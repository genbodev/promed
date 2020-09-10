/**
* swEvnDirectionHistologicViewWindow - журнал направлений на патологогистологическое исследование
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Direction
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Stas Bykov aka Savage (savage1981@gmail.com)
* @version      28.08.2010
* @comment      Префикс для id компонентов EDHVW (EvnDirectionHistologicViewWindow)
*
*
* Использует: направление на патологогистологическое исследование (swEvnDirectionHistologicEditWindow)
*/

sw.Promed.swEvnDirectionHistologicViewWindow = Ext.extend(sw.Promed.BaseForm, {
	border: false,
	buttonAlign: 'left',
	closeAction: 'hide',
	deleteEvnDirectionHistologic: function() {
		var grid = this.SearchGrid;

		if ( !grid || !grid.getGrid() ) {
			sw.swMsg.alert(langs('Ошибка'), langs('При удалении направления возникли ошибки [Тип ошибки: 1]'));
			return false;
		}
		else if ( !grid.getGrid().getSelectionModel().getSelected() ) {
			sw.swMsg.alert(langs('Ошибка'), langs('Не выбрано направление из списка'));
			return false;
		}

		var selected_record = grid.getGrid().getSelectionModel().getSelected();
		var id = selected_record.get('EvnDirectionHistologic_id');

		if ( id == null || selected_record.get('accessType') != 'edit' ) {
			return false;
		}

		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId, text, obj) {
				if ( 'yes' == buttonId ) {
					Ext.Ajax.request({
						failure: function(response, options) {
							sw.swMsg.alert(langs('Ошибка'), langs('При удалении направления возникли ошибки [Тип ошибки: 2]'));
						},
						params: {
							EvnDirectionHistologic_id: id
						},
						success: function(response, options) {
							var response_obj = Ext.util.JSON.decode(response.responseText);

							if ( response_obj.success == false ) {
								sw.swMsg.alert(langs('Ошибка'), response_obj.Error_Msg ? response_obj.Error_Msg : langs('При удалении направления возникли ошибки [Тип ошибки: 3]'));
							}
							else {
								grid.getGrid().getStore().remove(selected_record);

								if ( grid.getGrid().getStore().getCount() == 0 ) {
									grid.addEmptyRecord(grid.getGrid().getStore());
								}
							}

							grid.focus();
						},
						url: '/?c=EvnDirectionHistologic&m=deleteEvnDirectionHistologic'
					});
				}
			},
			icon: Ext.MessageBox.QUESTION,
			msg: langs('Удалить направление?'),
			title: langs('Вопрос')
		});
	},
	getLoadMask: function() {
		if ( !this.loadMask ) {
			this.loadMask = new Ext.LoadMask(this.getEl(), { msg: langs('Подождите...') });
		}

		return this.loadMask;
	},
	height: 500,
	id: 'EvnDirectionHistologicViewWindow',
	initComponent: function() {
		var win = this;
		this.datePeriodToolbar = new sw.Promed.datePeriodToolbar({
			curDate: getGlobalOptions().date,
			mode: 'day',
			onSelectPeriod: function(begDate,endDate,allowLoad)
			{
				this.findById('EDHVW_begDate').setValue(begDate.format('d.m.Y'));
				this.findById('EDHVW_endDate').setValue(endDate.format('d.m.Y'));
				if(allowLoad)
					this.loadGridWithFilter();
			}.createDelegate(this)
		});
		this.datePeriodToolbar.dateMenu.addListener('blur', 
			function () {
				this.datePeriodToolbar.onSelectMode('range',false);
			}.createDelegate(this)
		);
			
		this.FilterPanel = new Ext.form.FormPanel({
			autoHeight: true,
			bodyStyle: 'padding: 5px',
			border: false,
			frame: true,
			labelAlign: 'right',
			labelWidth: 150,
			tbar: win.datePeriodToolbar,
			id: 'EvnDirectionHistologicFilterForm',
			region: 'north',

			items: [{
						name: 'begDate',
						id: 'EDHVW_begDate',
						xtype: 'hidden'
					},
					{
						name: 'endDate',
						id: 'EDHVW_endDate',
						xtype: 'hidden'

					},{
				comboSubject: 'YesNo',
				fieldLabel: langs('Срочность'),
				hiddenName: 'EvnDirectionHistologic_IsUrgent',
				width: 100,
				xtype: 'swcommonsprcombo'
			}, {
					
				layout: 'column',
				border: false,	
				items: [
					{
						border: false,
						layout: 'form',
						items: [
							{
								fieldLabel: langs('Серия направления'),
								name: 'EvnDirectionHistologic_Ser',
								width: 175,
								xtype: 'textfield'
							}
						]
					},{
						bodyStyle: 'padding-right: 5px;',
						border: false,
						layout: 'form',
						items: [
							{
								fieldLabel: langs('Номер направления'),
								name: 'EvnDirectionHistologic_Num',
								width: 100,
								xtype: 'textfield'
							}
						]
					}
				]
					
			
			}, {
				allowBlank: true,
				codeField: 'EvnType_Code',
				displayField: 'EvnType_Name',
				editable: false,
				fieldLabel: langs('Состояние направления'),
				hiddenName: 'EvnType_id',
				hideEmptyRow: true,
				listeners: {
					'blur': function(combo)  {
						if ( combo.value == '' )
							combo.setValue(1);
					}
				},
				store: new Ext.data.SimpleStore({
					autoLoad: true,
					data: [
						[ 1, 1, langs('Все') ],
						[ 2, 2, langs('Только действующие') ],
						[ 3, 3, langs('Только испорченные') ]
					],
					fields: [
						{ name: 'EvnType_id', type: 'int'},
						{ name: 'EvnType_Code', type: 'int'},
						{ name: 'EvnType_Name', type: 'string'}
					],
					key: 'EvnType_id',
					sortInfo: { field: 'EvnType_Code' }
				}),
				tpl: new Ext.XTemplate(
					'<tpl for="."><div class="x-combo-list-item">',
					'<font color="red">{EvnType_Code}</font>&nbsp;{EvnType_Name}',
					'</div></tpl>'
				),
				value: 1,
				valueField: 'EvnType_id',
				width: 175,
				xtype: 'swbaselocalcombo'
			},{
					
				layout: 'column',
				border: false,	
				items: [
					{
						border: false,
						layout: 'form',
						items: [
							{
								fieldLabel: langs('Фамилия'),
								listeners: {
									'keydown': function (f, e) {
										if ( e.getKey() == e.ENTER ) {
											this.loadGridWithFilter();
										}
									}.createDelegate(this)
								},
								maxLength: 30,
								name: 'Person_Surname',
								plugins: [ new Ext.ux.translit(true, true) ],
								width: 175,
								xtype: 'textfield'
							}
						]
					},{
						bodyStyle: 'padding-right: 5px;',
						border: false,
						layout: 'form',
						items: [
							{
								fieldLabel: langs('Имя'),
								listeners: {
									'keydown': function (f, e) {
										if ( e.getKey() == e.ENTER ) {
											this.loadGridWithFilter();
										}
									}.createDelegate(this)
								},
								maxLength: 30,
								name: 'Person_Firname',
								plugins: [ new Ext.ux.translit(true, true) ],
								width: 175,
								xtype: 'textfield'
							}
						]
					}
				]
					
			
			}],
		buttonAlign: 'left',
		buttons: [{
				handler: function() {
				win.loadGridWithFilter();
				},
				iconCls: 'search16',
				text: BTN_FRMSEARCH
			}, {
				handler: function() {
				win.loadGridWithFilter(true);
				},
				iconCls: 'resetsearch16',
				text: BTN_FRMRESET
			}]
		});
		
		this.SearchGrid = new sw.Promed.ViewFrame({
			actions: [
				{ name: 'action_add', handler: function() { this.openEvnDirectionHistologicEditWindow('add'); }.createDelegate(this) },
				{ name: 'action_edit', handler: function() { this.openEvnDirectionHistologicEditWindow('edit'); }.createDelegate(this) },
				{ name: 'action_view', handler: function() { this.openEvnDirectionHistologicEditWindow('view'); }.createDelegate(this) },
				{ name: 'action_delete', handler: function() { this.deleteEvnDirectionHistologic(); }.createDelegate(this) },
				{ name: 'action_print',
					menuConfig: {
						printObject: { text: langs('Печать направления'), handler: function() { this.printEvnDirectionHistologic(); }.createDelegate(this), hidden: getRegionNick() == 'kz' }
					}
				}
			],
			autoLoadData: false,
			dataUrl: '/?c=EvnDirectionHistologic&m=loadEvnDirectionHistologicGrid',
			height: 203,
			id: this.id + 'SearchGrid',
			onDblClick: function() {
				this.onEnter();
			},
			onEnter: function() {
				if ( !this.ViewActions.action_edit.isDisabled() ) {
					this.ViewActions.action_edit.execute();
				}
				else {
					this.ViewActions.action_view.execute();
				}
			},
			onLoadData: function() {
				//
			},
			onRowSelect: function(sm, index, record) {
				if(win.viewOnly == true)
				{
					this.SearchGrid.ViewActions.action_add.setDisabled(true);
					this.SearchGrid.ViewActions.action_edit.setDisabled(true);
					this.SearchGrid.ViewActions.action_delete.setDisabled(true);
					this.SearchGrid.ViewActions.action_proto_add.setDisabled(true);
					this.SearchGrid.ViewActions.action_isbad.setDisabled(true);
					this.SearchGrid.ViewActions.action_isnotbad.setDisabled(true);
				}
				else
				{
					switch ( record.get('accessType') ) {
						case 'edit':
							this.SearchGrid.ViewActions.action_edit.setDisabled(false);
							this.SearchGrid.ViewActions.action_delete.setDisabled(false);
						break;

						case 'view':
							this.SearchGrid.ViewActions.action_edit.setDisabled(true);
							this.SearchGrid.ViewActions.action_delete.setDisabled(true);
						break;
					}

					if ( record.get('EvnHistologicProto_id') || !record.get('EvnDirectionHistologic_id') || Number(record.get('EvnDirectionHistologic_IsBad')) == 1 ) {
						this.SearchGrid.ViewActions.action_proto_add.setDisabled(true);
					}
					else {
						this.SearchGrid.ViewActions.action_proto_add.setDisabled(false);
					}

					if ( record.get('EvnHistologicProto_id') ) {
						this.SearchGrid.ViewActions.action_proto_view.setDisabled(false);
					}
					else {
						this.SearchGrid.ViewActions.action_proto_view.setDisabled(true);
					}

					if ( Number(record.get('EvnDirectionHistologic_IsBad')) == 1 ) {
						this.SearchGrid.ViewActions.action_isbad.setDisabled(true);
						this.SearchGrid.ViewActions.action_isnotbad.setDisabled(false);
					}
					else {
						this.SearchGrid.ViewActions.action_isnotbad.setDisabled(true);

						if ( record.get('EvnDirectionHistologic_id') ) {
							this.SearchGrid.ViewActions.action_isbad.setDisabled(false);
						}
						else {
							this.SearchGrid.ViewActions.action_isbad.setDisabled(true);
						}
					}
				}
			}.createDelegate(this),
			paging: true,
			region: 'center',
			root: 'data',
			stringfields: [
				{ name: 'EvnDirectionHistologic_id', type: 'int', header: 'ID', key: true },
				{ name: 'EvnHistologicProto_id', type: 'int', hidden: true },
				{ name: 'EvnDirectionHistologic_LpuSectionName', type: 'string', hidden: true},
				{ name: 'EvnDirectionHistologic_MedPersonalFIO', type: 'string', hidden: true},				
				{ name: 'accessType', type: 'string', hidden: true },
				{ name: 'EvnDirectionHistologic_IsBad', type: 'int', hidden: true },
				{ name: 'Person_id', type: 'int', hidden: true },
				{ name: 'PersonEvn_id', type: 'int', hidden: true },
				{ name: 'Server_id', type: 'int', hidden: true },
				{ name: 'EvnDirectionHistologic_HasProto', type: 'checkbox', header: langs('Протокол'), width: 70 },
				{ name: 'EvnDirectionHistologic_Ser', type: 'string', header: langs('Серия направления'), width: 120 },
				{ name: 'EvnDirectionHistologic_Num', type: 'string', header: langs('Номер направления'), width: 120 },
				{ name: 'EvnDirectionHistologic_setDate', type: 'date', format: 'd.m.Y', header: langs('Дата направления') },
				{ name: 'LpuSection_Name', type: 'string', header: langs('Отделение'), width: 250,
					renderer: function(v, p, row) {
						if(v) {
							return v;
						} else {
							return row.get('EvnDirectionHistologic_LpuSectionName');
						}
					}
				},
				{ name: 'MedPersonal_Fio', header: langs('Лечащий врач'), width: 250, 
					renderer: function(v, p, row) {
						if(v) {
							return v;
						} else {
							return row.get('EvnDirectionHistologic_MedPersonalFIO');
						}
					}
				},
				{ name: 'EvnDirectionHistologic_NumCard', type: 'string', header: langs('№ карты'), width: 80 },
				{ name: 'Person_Surname', type: 'string', header: langs('Фамилия'), width: 80 },
				{ name: 'Person_Firname', type: 'string', header: langs('Имя'), width: 80 },
				{ name: 'Person_Secname', type: 'string', header: langs('Отчество'), width: 80 },
				{ name: 'Person_Birthday', type: 'date', format: 'd.m.Y', header: langs('Дата рождения') },
				{ name: 'Lpu_Name', type: 'string', header: langs('Пат.-анат. лаборатория'), width: 250 },
				{ name: 'EvnDirectionHistologic_IsUrgent', type: 'string', header: langs('Срочность'), width: 70 }
			],
			// title: 'Направления на патологогистологическое исследование: Список',
			totalProperty: 'totalCount'
		});

		this.SearchGrid.ViewGridPanel.view = new Ext.grid.GridView({
			getRowClass: function (row, index) {
				var cls = '';

				if ( row.get('EvnDirectionHistologic_IsBad') == 1 ) {
					cls = cls + 'x-grid-rowgray';
				}
				else {
					cls = 'x-grid-panel'; 
				}

				return cls;
			}
		});

		Ext.apply(this, {
			buttons: [{
				text: '-'
			},
			HelpButton(this),
			{
				handler: function()  {
					this.hide()
				}.createDelegate(this),
				iconCls: 'close16',
				text: BTN_FRMCLOSE
			}],
			defaults: {
				split: true
			},
			layout: 'border',
			items: [ this.FilterPanel,
			{
				border: false,
				layout: 'border',
				region: 'center',
				xtype: 'panel',

				items: [
					this.SearchGrid
				]
			}]
		});

		sw.Promed.swEvnDirectionHistologicViewWindow.superclass.initComponent.apply(this, arguments);
	},
	keys: [{
		fn: function(inp, e) {
			var win = Ext.getCmp('EvnDirectionHistologicViewWindow');

			switch ( e.getKey() ) {
				case Ext.EventObject.INSERT:
					win.openEvnDirectionHistologicEditWindow('add');
				break;
			}
		},
		key: [ Ext.EventObject.INSERT ],
		stopEvent: true
	}],
	layout: 'border',
	listeners: {
		'beforeshow': function() {
			//
		}
	},
	loadGridWithFilter: function(clear) {
		var base_form = this.FilterPanel.getForm();

		this.SearchGrid.removeAll();
		this.SearchGrid.setActionDisabled('action_proto_add', true);
		this.SearchGrid.setActionDisabled('action_proto_view', true);

		if ( clear ) {
			base_form.reset();
			this.SearchGrid.gFilters = null;
		}
		else {
			var params = base_form.getValues();

			params.limit = 100;
			params.start = 0;
		
			this.SearchGrid.loadData({
				globalFilters: params
			});
		}
	},
	maximizable: true,
	maximized: true,
	modal: false,
	plain: true,
	openEvnDirectionHistologicEditWindow: function(action) {
		if ( action != 'add' && action != 'edit' && action != 'view' ) {
			return false;
		}

		if ( action == 'add' && getWnd('swPersonSearchWindow').isVisible() ) {
			sw.swMsg.alert(langs('Сообщение'), langs('Окно поиска человека уже открыто'));
			return false;
		}

		if ( getWnd('swEvnDirectionHistologicEditWindow').isVisible() ) {
			sw.swMsg.alert(langs('Сообщение'), langs('Окно редактирования направление на патологогистологическое исследование уже открыто'));
			return false;
		}

		var grid = this.SearchGrid.getGrid();
		var params = new Object();

		params.action = action;
		params.callback = function(data) {
			if ( !data || !data.evnDirectionHistologicData ) {
				return false;
			}

			// Обновить запись в grid
			var record = grid.getStore().getById(data.evnDirectionHistologicData.EvnDirectionHistologic_id);

			if ( record ) {
				var grid_fields = new Array();

				grid.getStore().fields.eachKey(function(key, item) {
					grid_fields.push(key);
				});

				for ( i = 0; i < grid_fields.length; i++ ) {
					record.set(grid_fields[i], data.evnDirectionHistologicData[grid_fields[i]]);
				}

				record.commit();
			}
			else {
				if ( grid.getStore().getCount() == 1 && !grid.getStore().getAt(0).get('EvnDirectionHistologic_id') ) {
					grid.getStore().removeAll();
				}

				grid.getStore().loadData({ data: [ data.evnDirectionHistologicData ], totalCount: 1 }, true);
			}
		}
		params.formParams = new Object();

		if ( action == 'add' ) {
			getWnd('swPersonSearchWindow').show({
				onClose: Ext.emptyFn,
				onSelect: function(person_data) {
					params.onHide = function() {
						// TODO: getWnd
						getWnd('swPersonSearchWindow').findById('person_search_form').getForm().findField('PersonSurName_SurName').focus(true, 250);
					};
					params.formParams.Person_id =  person_data.Person_id;
					params.formParams.PersonEvn_id = person_data.PersonEvn_id;
					params.formParams.Server_id = person_data.Server_id;

					getWnd('swEvnDirectionHistologicEditWindow').show(params);
				},
				personFirname: this.FilterPanel.getForm().findField('Person_Firname').getValue(),
				//personSecname: this.FilterPanel.getForm().findField('Person_Secname').getValue(),
				personSurname: this.FilterPanel.getForm().findField('Person_Surname').getValue(),
				searchMode: 'all'
			});
		}
		else {
			if ( !grid.getSelectionModel().getSelected() ) {
				return false;
			}

			var selected_record = grid.getSelectionModel().getSelected();

			if ( selected_record.get('accessType') != 'edit' ) {
				params.action = 'view';
			}

			var evn_direction_histologic_id = selected_record.get('EvnDirectionHistologic_id');
			var person_id = selected_record.get('Person_id');
			var server_id = selected_record.get('Server_id');

			if ( evn_direction_histologic_id > 0 && person_id > 0 && server_id >= 0 ) {
				params.formParams.EvnDirectionHistologic_id = evn_direction_histologic_id;
				params.formParams.Person_id = person_id;
				params.formParams.Server_id = server_id;
				params.onHide = function() {
					grid.getView().focusRow(grid.getStore().indexOf(selected_record));
				};

				getWnd('swEvnDirectionHistologicEditWindow').show(params);
			}
		}
	},
	openEvnHistologicProtoEditWindow: function(action) {
		if ( !action || !action.inlist([ 'add', 'edit', 'view']) ) {
			return false;
		}

		if ( getWnd('swEvnHistologicProtoEditWindow').isVisible() ) {
			sw.swMsg.alert(langs('Сообщение'), langs('Окно редактирования протокола патологогистологического исследования уже открыто'));
			return false;
		}

		var formParams = new Object();
		var grid = this.SearchGrid.getGrid();
		var params = new Object();

		if ( !grid.getSelectionModel().getSelected() ) {
			return false;
		}

		var selected_record = grid.getSelectionModel().getSelected();

		formParams.Person_id = selected_record.get('Person_id');
		formParams.PersonEvn_id = selected_record.get('PersonEvn_id');
		formParams.Server_id = selected_record.get('Server_id');

		if ( action == 'add' ) {
			if ( !selected_record.get('EvnDirectionHistologic_id') ) {
				return false;
			}
			else if ( selected_record.get('EvnHistologicProto_id') ) {
				return false;
			}

			formParams.EvnDirectionHistologic_id = selected_record.get('EvnDirectionHistologic_id');
			formParams.EvnDirectionHistologic_SerNum = selected_record.get('EvnDirectionHistologic_Ser') + ' ' + selected_record.get('EvnDirectionHistologic_Num') + ', ' + Ext.util.Format.date(selected_record.get('EvnDirectionHistologic_setDate'), 'd.m.Y');
			formParams.EvnHistologicProto_id = 0;
		}
		else {
			formParams.EvnHistologicProto_id = selected_record.get('EvnHistologicProto_id');
		}

		params.action = action;
		params.callback = function(data) {
			if ( action == 'add' && typeof data == 'object' && typeof data.evnHistologicProtoData == 'object' && !Ext.isEmpty(data.evnHistologicProtoData.EvnHistologicProto_id) ) {
				selected_record.set('EvnDirectionHistologic_HasProto', 'true');
				selected_record.set('EvnHistologicProto_id', data.evnHistologicProtoData.EvnHistologicProto_id);
				selected_record.commit();

				this.SearchGrid.ViewActions.action_proto_add.setDisabled(true);
				this.SearchGrid.ViewActions.action_proto_view.setDisabled(false);
			}
		}.createDelegate(this);
		params.formParams = formParams;
		params.onHide = function() {
			grid.getView().focusRow(grid.getStore().indexOf(selected_record));
		};

		getWnd('swEvnHistologicProtoEditWindow').show(params);
	},
	printEvnDirectionHistologic: function() {
		var grid = this.SearchGrid.getGrid();

		if ( !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('EvnDirectionHistologic_id') ) {
			return false;
		}

		printBirt({
			'Report_FileName': 'f014u_DirectionHistologic.rptdesign',
			'Report_Params': '&paramEvnDirectionHistologic=' + grid.getSelectionModel().getSelected().get('EvnDirectionHistologic_id'),
			'Report_Format': 'pdf'
		});
	},
	resizable: false,
	setIsBad: function(flag, cause) {
		var grid = this.SearchGrid.getGrid();

		if ( !grid.getSelectionModel().getSelected() ) {
			return false;
		}

		var selected_record = grid.getSelectionModel().getSelected();

		var id = selected_record.get('EvnDirectionHistologic_id');

		if ( !id ) {
			return false;
		};

		if ( flag == true && Number(selected_record.get('EvnDirectionHistologic_IsBad')) == 1 ) {
			return false;
		}
		else if ( !flag && Number(selected_record.get('EvnDirectionHistologic_IsBad')) != 1 ) {
			return false;
		}
		var params = {
			EvnDirectionHistologic_id: id,
			EvnDirectionHistologic_IsBad: (flag == true ? 1 : 0)
		}
		if(cause) {
			params.EvnStatusCause_id = cause.EvnStatusCause_id;
			params.EvnStatusHistory_Cause = cause.EvnStatusHistory_Cause;
		}
		this.getLoadMask().show();

		Ext.Ajax.request({
			failure: function(response, options) {
				this.getLoadMask().hide();
				sw.swMsg.alert(langs('Ошибка'), langs('Ошибка при ') + (flag == true ? langs('аннулировании') : langs('отмене аннулирования')) + langs(' патологогистологического направления [Тип ошибки: 2]'));
			}.createDelegate(this),
			params: params,
			success: function(response, options) {
				this.getLoadMask().hide();

				var response_obj = Ext.util.JSON.decode(response.responseText);

				if ( response_obj.success == false ) {
					sw.swMsg.alert(langs('Ошибка'), response_obj.Error_Msg ? response_obj.Error_Msg : langs('Ошибка при ') + (flag == true ? langs('аннулировании') : langs('отмене аннулирования')) + langs(' патологогистологического направления [Тип ошибки: 3]'));
				}
				else {
					var base_form = this.FilterPanel.getForm();

					var type = base_form.findField('EvnType_id').getValue();

					if ( (type == 2 && flag == true) || (type == 3 && !flag) ) {
						grid.getStore().remove(selected_record);
					}
					else {
						selected_record.set('EvnDirectionHistologic_IsBad', (flag == true ? 1 : 0));
						selected_record.commit();

						this.SearchGrid.onRowSelect(
							grid.getSelectionModel(),
							grid.getStore().indexOf(selected_record),
							selected_record
						);
					}
				}
			}.createDelegate(this),
			url: '/?c=EvnDirectionHistologic&m=setEvnDirectionHistologicIsBad'
		});
	},
	show: function() {
		var win = this;
		sw.Promed.swEvnDirectionHistologicViewWindow.superclass.show.apply(this, arguments);
		this.getLoadMask().show();
		this.datePeriodToolbar.onShow(true);
		if ( !this.SearchGrid.getAction('action_isnotbad') ) {
			this.SearchGrid.addActions({
				disabled: true,
				hidden: true,
				handler: function() {
					this.setIsBad(false);
				}.createDelegate(this),
				name: 'action_isnotbad',
				text: langs('Отменить аннулирование')
			});
		}

		if ( !this.SearchGrid.getAction('action_isbad') ) {
			this.SearchGrid.addActions({
				disabled: true,
				handler: function() {
					var selected_record = this.SearchGrid.getGrid().getSelectionModel().getSelected();

					getWnd('swSelectEvnStatusCauseWindow').show({
						Evn_id: selected_record.get('EvnDirectionHistologic_id'),
						EvnClass_id: 27, //выписка направлений
						callback(values) {
							win.setIsBad(true, values);
						}
					});
				}.createDelegate(this),
				name: 'action_isbad',
				text: langs('Аннулировать')
			});
		}

		if ( !this.SearchGrid.getAction('action_proto_view') ) {
			this.SearchGrid.addActions({
				disabled: true,
				handler: function() {
					this.openEvnHistologicProtoEditWindow('edit');
				}.createDelegate(this),
				name: 'action_proto_view',
				text: langs('Открыть протокол')
			});
		}

		if ( !this.SearchGrid.getAction('action_proto_add') ) {
			this.SearchGrid.addActions({
				disabled: true,
				handler: function() {
					this.openEvnHistologicProtoEditWindow('add');
				}.createDelegate(this),
				name: 'action_proto_add',
				text: langs('Добавить протокол')
			});
		}

		this.center();
		this.maximize();
		this.viewOnly = false;
		if(arguments[0])
		{
			if(arguments[0].viewOnly)
				this.viewOnly = arguments[0].viewOnly;
		}
		//this.SearchGrid.ViewActions.action_add.setDisabled(this.viewOnly);
		this.SearchGrid.setActionDisabled('action_add', this.viewOnly);
		this.loadGridWithFilter(true);
		this.getLoadMask().hide();
	},
	title: langs('Журнал направлений на патологогистологическое исследование'), 
	width: 800
});