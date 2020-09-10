/**
 * Журнал загрузок
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @autor		Dmitriy Vlasenko
 * @copyright    Copyright (c) 2015 Swan Ltd.
 * @version      12.2015
 */
sw.Promed.swReceptUploadLogWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swReceptUploadLogWindow',
	maximized: true,
	title: 'Журнал загрузок',
	show: function() {
		sw.Promed.swReceptUploadLogWindow.superclass.show.apply(this, arguments);

		var b_f = this.FilterPanel.getForm();

		if (arguments[0]) {
			var Date = '';
			if (arguments[0].begDate) {
				Date = Date + arguments[0].begDate;
			}

			Date = Date + ' - ';

			if (arguments[0].endDate) {
				Date = Date + arguments[0].endDate;
			}

			b_f.findField('ReceptUploadLog_setDT_Range').setValue(Date);
		}

		if( !this.GridPanel.getAction('send_act_about_import') ) {
			this.GridPanel.addActions({
				text: 'Сформировать акт по загрузке',
				name: 'send_act_about_import',
				handler: function() {
					this.execAction('sendActAboutImport', 'Выполняется формирование акта...');
				},
				scope: this
			});
		}

		if( !this.GridPanel.getAction('import_and_exp') ) {
			this.GridPanel.addActions({
				text: 'Провести импорт и проверку данных',
				name: 'import_and_exp',
				handler: function() {
					this.execAction('importAndExpertise', 'Выполняется импорт и проверка данных...');
				},
				scope: this
			});
		}

		b_f.findField('Contragent_id').getStore().baseParams.mode = 'punktotp';
		if( !b_f.findField('Contragent_id').getStore().getCount() ) {
			b_f.findField('Contragent_id').getStore().load();
		}

		this.doSearch();
	},
	doSearch: function(mode) {
		var params = this.FilterPanel.getForm().getValues();
		params.limit = 50;
		params.start = 0;
		this.GridPanel.removeAll();
		this.GridPanel.loadData({globalFilters: params});
	},

	showRegistryReceptListWindow: function() {
		var grid = this.GridPanel;
		var record = grid.getGrid().getSelectionModel().getSelected();
		if( !record ) return false;

		getWnd('swRegistryReceptListWindow').show({
			ReceptUploadLog_id: record.get('ReceptUploadLog_id')
		});
	},

	showImportReceptUploadWindow: function(action) {
		var grid = this.GridPanel;
		var record = grid.getGrid().getSelectionModel().getSelected();

		if( action != 'add' ) {
			if( !record ) {
				return false;
			}

			if (!Ext.isEmpty(record.get('RegistryLLO_id'))) {
				return true;
			}

			if (record.get('ReceptUploadType_id') == 2 || record.get('ReceptUploadType_id') == 3) {
				this.showRegistryReceptListWindow();
				return true;
			}
		}

		getWnd('swImportReceptUploadWindow').show({
			action: action,
			record: record || null,
			callback: function(upload_result) {
				grid.ViewActions.action_refresh.execute();
				if(upload_result && upload_result.farmacy_import_msg && upload_result.farmacy_import_msg != '') {
					sw.swMsg.alert('Результат импорта', upload_result.farmacy_import_msg);
				}
			}
		});
	},

	deleteReceptUploadLog: function() {
		var record = this.GridPanel.getGrid().getSelectionModel().getSelected();
		if( !record ) return false;

		Ext.Msg.show({
			title: 'Внимание!',
			scope: this,
			msg: 'Вы действительно хотите удалить выбранную запись?',
			buttons: Ext.Msg.YESNO,
			fn: function(btn) {
				if (btn === 'yes') {
					this.getLoadMask('Удаление записи...').show();
					Ext.Ajax.request({
						scope: this,
						url: '/?c=ReceptUpload&m=deleteReceptUploadLog',
						params: { ReceptUploadLog_id: record.get('ReceptUploadLog_id') },
						callback: function(o, s, r) {
							this.getLoadMask().hide();
							this.GridPanel.ViewActions.action_refresh.execute();
						}
					});
				}
			},
			icon: Ext.MessageBox.QUESTION
		});
	},

	execAction: function(action, msg, cb, scope) {
		if( !action ) return false;

		var wnd = this,
			record = wnd.GridPanel.getGrid().getSelectionModel().getSelected();
		if( !record ) return false;

		var url = '/?c=ReceptUpload&m=' + action;
		var params = new Object();

		params = Ext.apply(params, record.data);

		if (!Ext.isEmpty(record.get('RegistryLLO_id')) && action == 'importAndExpertise') {
			url = '/?c=RegistryLLO&m=setRegistryStatus';
			params.RegistryStatus_Code = 2; //2 - К оплате
		}

		wnd.getLoadMask(msg || '').show();
		Ext.Ajax.request({
			url: url,
			params: params,
			scope: scope || this,
			callback: function(o, s, r) {
				wnd.getLoadMask().hide();
				if( s ) {
					wnd.GridPanel.ViewActions.action_refresh.execute();
					if( cb && Ext.isFunction(cb) ) {
						cb.apply(this, arguments);
					}
				}
			}
		});
	},
	buttonAlign : "right",
	buttons:
	[
		{
			text: BTN_FRMHELP,
			iconCls: 'help16',
			handler: function(button, event)
			{
				ShowHelp(this.ownerCt.title);
			}
		},
		{
			text      : BTN_FRMCLOSE,
			tabIndex  : -1,
			tooltip   : 'Закрыть',
			iconCls   : 'cancel16',
			handler   : function()
			{
				this.ownerCt.hide();
			}
		}
	],
	initComponent: function() {
		var form = this;

		this.onKeyDown = function (inp, e) {
			if (e.getKey() == Ext.EventObject.ENTER) {
				e.stopEvent();
				this.doSearch();
			}
		}.createDelegate(this);
		this.FilterPanel = getBaseFiltersFrame({
			ownerWindow: this,
			defaults: {
				frame: true,
				collapsed: false
			},
			items: [{
				layout: 'column',
				items: [{
					layout: 'form',
					labelWidth: 100,
					items: [{
						xtype: 'daterangefield',
						name: 'ReceptUploadLog_setDT_Range',
						listWidth: 300,
						width: 200,
						fieldLabel: 'Период'
					}]
				}, {
					layout: 'form',
					items: [{
						fieldLabel: 'Тип данных',
						value: 2,
						xtype: 'swcommonsprcombo',
						comboSubject: 'ReceptUploadType'
					}]
				}]
			}, {
				layout: 'column',
				items: [{
					layout: 'form',
					labelWidth: 100,
					items: [{
						xtype: 'swcontragentcombo',
						hiddenName:'Contragent_id',
						listWidth: 300,
						fieldLabel: 'Пункт отпуска'
					}]
				}, {
					layout: 'form',
					style: 'margin-left: 10px;',
					items: [{
						xtype: 'button',
						handler: this.doSearch,
						scope: this,
						iconCls: 'search16',
						text: BTN_FRMSEARCH
					}]
				}, {
					layout: 'form',
					style: 'margin-left: 10px;',
					items: [{
						xtype: 'button',
						iconCls: 'reset16',
						handler: function() {
							this.FilterPanel.getForm().reset();
							this.doSearch();
						},
						scope: this,
						text: BTN_FRMRESET
					}]
				}]
			}]
		});
		this.GridPanel = new sw.Promed.ViewFrame({
			id: this.id + '_Grid',
			region: 'center',
			autoExpandColumn: 'autoexpand',
			actions: [
				{ name:'action_add', /*text: 'Импорт', icon: 'img/icons/petition-report16.png',*/ handler: this.showImportReceptUploadWindow.createDelegate(this, ['add']) },
				{ name:'action_edit', disabled: true /*text: 'Случаи', icon: 'img/icons/doc-uch16.png'*/ },
				{ name:'action_view', handler: this.showImportReceptUploadWindow.createDelegate(this, ['view']) },
				{ name:'action_delete', handler: this.deleteReceptUploadLog.createDelegate(this) },
				{ name:'action_refresh' },
				{ name:'action_print' }
			],
			autoLoadData: false,
			paging: true,
			pageSize: 50,
			stringfields: [
				// Поля для отображение в гриде
				{ name: 'ReceptUploadLog_id', type: 'int', header: '№', width: 60, hidden: false, key: true },
				{ name: 'ReceptUploadLog_setDT', header: 'Дата загрузки' },
				{ name: 'Contragent_id', hidden: true },
				{ name: 'ReceptUploadType_id', hidden: true },
				{ name: 'ReceptUploadStatus_id', hidden: true },
				{ name: 'ReceptUploadStatus_Code', hidden: true },
				{ name: 'Contragent_Name', header: 'Пункт отпуска', id: 'autoexpand' },
				{ name: 'ReceptUploadType_Name', header: 'Тип данных', width: 120 },
				{ name: 'file_name', header: 'Имя загруженного файла', width: 180 },
				{ name: 'file_size', header: 'Размер' },
				{ name: 'ReceptUploadStatus_Name', header: 'Статус данных', width: 200 },
				{ name: 'ReceptUploadLog_Act', header: 'Ссылка на акт', width: 120, renderer: function(v, p, r) {
					return !Ext.isEmpty(v) && +r.get('isHisRecord') ? '<a href="'+v+'" target="_blank">скачать</a>' : '';
				} },
				{ name: 'ReceptUploadLog_InFail', header: 'Ссылка на файлы', width: 120, renderer: function(v, p, r) {
					return !Ext.isEmpty(v) && +r.get('isHisRecord') ? '<a href="'+v+'" target="_blank">скачать</a>' : '';
				} },
				{ name: 'isHisRecord', hidden: true },
				{ name: 'RegistryLLO_id', hidden: true }
			],
			editformclassname: '',
			dataUrl: '/?c=ReceptUpload&m=loadReceptUploadLogList',
			root: 'data',
			totalProperty: 'totalCount',
			//title: 'Журнал рабочего места',
			onRowSelect: function(sm, index, record) {
				this.getAction('action_delete').setDisabled( !+record.get('isHisRecord') || !record.get('ReceptUploadStatus_Code').inlist([1]) || Ext.isEmpty(record.get('RegistryLLO_id')) );
				this.getAction('import_and_exp').setDisabled( !+record.get('isHisRecord') && (Ext.isEmpty(record.get('RegistryLLO_id')) || record.get('ReceptUploadStatus_Code') == 4) );
				this.getAction('send_act_about_import').setDisabled( !+record.get('isHisRecord') );
			},
			onLoadData: function() {
				var view = this.getGrid().getView(),
					store = this.getGrid().getStore(),
					rows = view.getRows();
				Ext.each(rows, function(row, idx) {
					var record = store.getAt(idx);
					if( !+record.get('isHisRecord') && !Ext.isEmpty(record.get('ReceptUploadLog_id')) && Ext.isEmpty(record.get('RegistryLLO_id')) ) {
						new Ext.ToolTip({
							html: 'Данные были загружены на другой веб-сервер и не могут быть изменены или удалены!',
							target: Ext.get(row).id
						});
					}
				});
			}
		});

		this.GridPanel.getGrid().view = new Ext.grid.GridView({
			getRowClass: function (row, index) {
				var cls = '';
				if ( !+row.get('isHisRecord') && Ext.isEmpty(row.get('RegistryLLO_id')) )
					cls = cls+'x-grid-rowgray ';
				return cls;
			}.createDelegate(this)
		});

		this.GridPanel.getGrid().getView().on('refresh', function(v) {
			//log('refresh');
		});

		this.CenterPanel = new sw.Promed.Panel({
			region: 'center',
			border: false,
			layout: 'border',
			items: [this.GridPanel]
		});

		Ext.apply(this,	{
			layout: 'border',
			items: [
				this.FilterPanel
				,this.CenterPanel
			]
		});

		sw.Promed.swReceptUploadLogWindow.superclass.initComponent.apply(this, arguments);
	}
});