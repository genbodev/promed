/**
* swEvnCytologicProtoViewWindow - Журнал протоколов цитологических диагностических исследований
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* Использует: протокол исследования (swEvnCytologicProtoEditWindow)
*/

sw.Promed.swEvnCytologicProtoViewWindow = Ext.extend(sw.Promed.BaseForm, {
	border: false,
	buttonAlign: 'left',
	closeAction: 'hide',
	userMedStaffFact: {},
	deleteEvnCytologicProto: function() {
		var grid = this.SearchGrid;

		if ( !grid || !grid.getGrid() ) {
			sw.swMsg.alert(langs('Ошибка'), langs('При удалении протокола цитологического диагностического исследования возникли ошибки [Тип ошибки: 1]'));
			return false;
		}
		else if ( !grid.getGrid().getSelectionModel().getSelected() ) {
			sw.swMsg.alert(langs('Ошибка'), langs('Не выбран протокол цитологического диагностического исследования из списка'));
			return false;
		}

		var selected_record = grid.getGrid().getSelectionModel().getSelected();
		var id = selected_record.get('EvnCytologicProto_id');

		if ( !id || selected_record.get('accessType') != 'edit' ) {
			return false;
		}

		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId, text, obj) {
				if ( 'yes' == buttonId ) {
					Ext.Ajax.request({
						failure: function(response, options) {
							sw.swMsg.alert(langs('Ошибка'), langs('При удалении протокола цитологического диагностического исследования возникли ошибки [Тип ошибки: 2]'));
						},
						params: {
							EvnCytologicProto_id: id
						},
						success: function(response, options) {
							var response_obj = Ext.util.JSON.decode(response.responseText);

							if ( response_obj.success == false ) {
								sw.swMsg.alert(langs('Ошибка'), response_obj.Error_Msg ? response_obj.Error_Msg : langs('При удалении протокола цитологического диагностического исследования возникли ошибки [Тип ошибки: 3]'));
							}
							else {
								grid.getGrid().getStore().remove(selected_record);

								if ( grid.getGrid().getStore().getCount() == 0 ) {
									grid.addEmptyRecord(grid.getGrid().getStore());
								}
							}

							grid.focus();
						},
						url: '/?c=EvnCytologicProto&m=deleteEvnCytologicProto'
					});
				}
			},
			icon: Ext.MessageBox.QUESTION,
			msg: langs('Удалить протокол цитологического диагностического исследования?'),
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
	id: 'EvnCytologicProtocolsViewWindow',
	initComponent: function() {
		var win = this;
		this.FilterPanel = new Ext.form.FormPanel({
			autoHeight: true,
			bodyStyle: 'padding: 5px',
			border: false,
			frame: true,
			labelAlign: 'right',
			labelWidth: 180,
			id: 'swEvnCytologicProtocolsFilterForm',
			region: 'north',

			items: [{
				layout: 'column',
				border: false,	
				items: [{
					bodyStyle: 'padding-right: 5px;',
					border: false,
					layout: 'form',
					items: [{
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
						width: 220,
						xtype: 'textfield'
					}, {
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
						width: 220,
						xtype: 'textfield'
					}, {
						fieldLabel: langs('Отчество'),
						listeners: {
							'keydown': function (f, e) {
								if ( e.getKey() == e.ENTER ) {
									this.loadGridWithFilter();
								}
							}.createDelegate(this)
						},
						maxLength: 30,
						name: 'Person_Secname',
						plugins: [ new Ext.ux.translit(true, true) ],
						width: 220,
						xtype: 'textfield'
					},
					{
						border: false,
						layout: 'column',
						items: [
						{	layout: 'form',
							border: false,
							labelWidth: 180,
							items: [{
								xtype: 'numberfield',
								name: 'minAge',
								fieldLabel: langs('Возраст с'),
								value: '',
								maxValue: 99,
								minValue: 0,
								width:40,
								listeners: {
									'keydown': function (f, e) {alert("d");
										if ( e.getKey() == e.ENTER ) {
											this.loadGridWithFilter();
										}
									}.createDelegate(this)
								}
								}]},
						{
							
							layout: 'form',
							border: false,
							labelWidth: 30,
							items: [{
								xtype: 'numberfield',
								name: 'maxAge',
								fieldLabel: langs('по'),
								value: '',
								maxValue: 99,
								minValue: 0,
								width:40,
								listeners: {
									'keydown': function (f, e) {
										if ( e.getKey() == e.ENTER ) {
											this.loadGridWithFilter();
										}
									}.createDelegate(this)
								}
							}]}
							]
						},
						{
							allowBlank: false,
							name : "setDateRange",
							xtype : "daterangefield",
							width : 170,
							fieldLabel : "Дата исследования",
							plugins: [new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
							tabIndex : 304
						},
						{
						items : [{
							labelWidth : 180,
							items : [{
								allowBlank: true,
								displayField: 'Diag_Name',
								emptyText: langs('Введите код диагноза...'),
								fieldLabel: langs('Код диагноза с'),
								hiddenName: 'PT_Diag_Code_From',
								hideTrigger: false,
								id: 'PTS_DiagComboFrom',
								valueField: 'Diag_Code',
								width: 220,
								xtype: 'swdiagcombo',
								tabIndex : 311
							}],
							layout : "form",
							width : 410,
							border : false
						}, {
							labelWidth : 20,
							items : [{
								allowBlank: true,
								displayField: 'Diag_Name',
								emptyText: langs('Введите код диагноза...'),
								fieldLabel: langs('по'),
								hiddenName: 'PT_Diag_Code_To',
								hideTrigger: false,
								id: 'PTS_DiagComboTo',
								listeners: {
									'keydown': function (inp, e) {
										if (e.shiftKey == false && e.getKey() == Ext.EventObject.TAB) {
											if (EvnReceptSearchViewGrid.getStore().getCount() > 0) {
												e.stopEvent();
												TabToGrid(EvnReceptSearchViewGrid);
											}
										}
									}
								},
								valueField: 'Diag_Code',
								width: 220,
								xtype: 'swdiagcombo',
								tabIndex : 312
							}],
							layout : "form",
							width : 300,
							border : false
						}],
						layout : "column",
						autoHeight : true,
						border : false
					}]
				}, {
					bodyStyle: 'padding-right: 5px;',
					border: false,
					layout: 'form',
					items: [{
						disabled: false,
						handler: function () {
							this.loadGridWithFilter();
						}.createDelegate(this),
						minWidth: 125,
						text: langs('Установить фильтр'),
						topLevel: true,
						xtype: 'button'
					}, {
						disabled: false,
						handler: function () {
							this.loadGridWithFilter(true);
						}.createDelegate(this),
						minWidth: 125,
						text: langs('Снять фильтр'),
						topLevel: true,
						xtype: 'button'
					}]
				}]
			}, 
			/*
			{
				// КУДА ЭТО НЕ ПОНЯТНО ??????????????????????????????????????????
				allowBlank: true,
				codeField: 'EvnType_Code',
				displayField: 'EvnType_Name',
				editable: false,
				fieldLabel: langs('Состояние протокола'),
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
				width: 220,
				xtype: 'swbaselocalcombo'
			}
			*/
			]
		});
		
		this.SearchGrid = new sw.Promed.ViewFrame({
			actions: [
				{ name: 'action_add', disabled: !(isPathoMorphoUser() || isOperator()), handler: function() { this.openEvnCytologicProtoEditWindow('add'); }.createDelegate(this) },
				{ name: 'action_edit', handler: function() { this.openEvnCytologicProtoEditWindow('edit'); }.createDelegate(this) },
				{ name: 'action_view', handler: function() { this.openEvnCytologicProtoEditWindow('view'); }.createDelegate(this) },
				{ name: 'action_delete', handler: function() { this.deleteEvnCytologicProto(); }.createDelegate(this) },
				{ name: 'action_print',
					menuConfig: {
						printObjectDirection: { text: langs('Печать протокола'), handler: function() { this.printDirection(); }.createDelegate(this) }
					}
				}
			],
			autoLoadData: false,
			dataUrl: '/?c=EvnCytologicProto&m=loadEvnCytologicProtoGrid',
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
				// return false;
			},
			onRowSelect: function(sm, index, record) {
				// return false;
			}.createDelegate(this),
			paging: true,
			region: 'center',
			root: 'data',
			stringfields: [
				{ name: 'EvnCytologicProto_id', type: 'int', header: 'ID', key: true },
				{ name: 'accessType', type: 'string', hidden: true },
				{ name: 'EvnDirectionCytologic_id', type: 'int', hidden: true },
				{ name: 'Person_id', type: 'int', hidden: true },
				{ name: 'PersonEvn_id', type: 'int', hidden: true },
				{ name: 'Server_id', type: 'int', hidden: true },
				{ name: 'MedPersonal_id', type: 'int', hidden: true },
				{ name: 'Lpu_id', type: 'int', hidden: true },
				{ name: 'EvnCytologicProto_Ser', type: 'string', header: langs('Серия'), width: 120 },
				{ name: 'EvnCytologicProto_Num', type: 'string', header: langs('Номер'), width: 120 },
				{ name: 'EvnCytologicProto_SurveyDT', type: 'date', format: 'd.m.Y', header: langs('Дата исследования') },
				{ name: 'Lpu_Name', type: 'string', header: langs('Направившее ЛПУ'), width: 250 },
				{ name: 'LpuSection_Name', type: 'string', header: langs('Отделение'), width: 250 },
				{ name: 'EvnDirectionCytologic_NumCard', type: 'string', header: langs('№ карты'), width: 80 },
				{ name: 'Person_Surname', type: 'string', header: langs('Фамилия'), width: 150 },
				{ name: 'Person_Firname', type: 'string', header: langs('Имя'), width: 150 },
				{ name: 'Person_Secname', type: 'string', header: langs('Отчество'), width: 150 },
				{ name: 'Person_Birthday', type: 'date', format: 'd.m.Y', header: langs('Дата рождения') },
				{ name: 'Lab_MedPersonal_Fio', type: 'string', header: langs('Лаборант'), width: 250 }
			],
			title: langs('Журнал протоколов цитологических диагностических исследований: Список'),
			totalProperty: 'totalCount'
		});
		/*
		this.SearchGrid.ViewGridPanel.view = new Ext.grid.GridView({
			getRowClass: function (row, index) {
				debugger;
				var cls = '';

				if ( row.get('EvnCytologicProto_id') == 1 ) {
					cls = cls + 'x-grid-rowgray';
				}
				else {
					cls = 'x-grid-panel'; 
				}

				return cls;
			}
		});
		*/
		Ext.apply(this, {
			layout:'border',
			defaults: {
				split: true
			},
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

		sw.Promed.swEvnCytologicProtoViewWindow.superclass.initComponent.apply(this, arguments);
	},
	keys: [{
		fn: function(inp, e) {
			var win = Ext.getCmp('EvnCytologicProtocolsViewWindow');

			switch ( e.getKey() ) {
				case Ext.EventObject.INSERT:
					win.openEvnCytologicProtoEditWindow('add');
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

		if ( clear ) {
			base_form.reset();
			var date1 = getValidDT(getGlobalOptions().date, '').add(Date.MONTH, -1).format('d.m.Y');
			var date2 = getGlobalOptions().date;

			base_form.findField('setDateRange').setValue(date1 + ' - ' + date2);

			this.SearchGrid.getAction('action_refresh').setDisabled(true);
			this.SearchGrid.gFilters = null;
		}
		else {
			if ( !base_form.isValid() ) {
				sw.swMsg.show({
					buttons: Ext.Msg.OK,
					fn: function() {
						this.FilterPanel.getFirstInvalidEl().focus(true);
					}.createDelegate(this),
					icon: Ext.Msg.WARNING,
					msg: ERR_INVFIELDS_MSG,
					title: ERR_INVFIELDS_TIT
				});
				return false;
			}

			var setMax = Ext.util.Format.date(base_form.findField('setDateRange').getValue2(),'d.m.Y');
			var setMin = Ext.util.Format.date(base_form.findField('setDateRange').getValue1(),'d.m.Y');
			var params = base_form.getValues();
			
			params.setRangeStart = setMin;
			params.setRangeEnd = setMax;
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
	openEvnCytologicProtoEditWindow: function(action) {
		if ( action != 'add' && action != 'edit' && action != 'view' ) {
			return false;
		}
		var win = this;
		var formEvnCytologicProtoEditWindow = getWnd('swEvnCytologicProtoEditWindow');
		if(!formEvnCytologicProtoEditWindow) return false;

		if ( action == 'add' && getWnd('swPersonSearchWindow').isVisible() ) {
			sw.swMsg.alert(langs('Сообщение'), langs('Окно поиска человека уже открыто'));
			return false;
		}

		if ( formEvnCytologicProtoEditWindow.isVisible() ) {
			sw.swMsg.alert(langs('Сообщение'), langs('Окно редактирования протокола цитологического исследования уже открыто'));
			return false;
		}

		var params = new Object();
		var grid = this.SearchGrid.getGrid();

		params.action = action;
		params.callback = function(data) {
			if ( !data || !data.evnCytologicProtoData ) {
				return false;
			}
			if(action == 'add' && getWnd('swPersonSearchWindow').isVisible()){
				//закрываем форму поиска человека
				getWnd('swPersonSearchWindow').hide();
			}
			// Обновить запись в grid
			win.loadGridWithFilter();
			/*
			var record = grid.getStore().getById(data.evnCytologicProtoData.EvnCytologicProto_id);

			if ( record ) {
				var grid_fields = new Array();

				grid.getStore().fields.eachKey(function(key, item) {
					grid_fields.push(key);
				});

				for ( i = 0; i < grid_fields.length; i++ ) {
					if ( data.evnCytologicProtoData[grid_fields[i]] != undefined ) {
						record.set(grid_fields[i], data.evnCytologicProtoData[grid_fields[i]]);
					}
				}

				record.commit();
			}
			else {
				if ( grid.getStore().getCount() == 1 && !grid.getStore().getAt(0).get('EvnCytologicProto_id') ) {
					grid.getStore().removeAll();
				}

				grid.getStore().loadData({ data: [ data.evnCytologicProtoData ], totalCount: 1 }, true);
			}
			*/
		}
		params.formParams = new Object();

		if ( action == 'add' ) {
			if(win.userMedStaffFact.LpuSection_id) params.formParams.LpuSection_id = win.userMedStaffFact.LpuSection_id;
			getWnd('swPersonSearchWindow').show({
				onClose: Ext.emptyFn,
				onSelect: function(person_data) {
					params.onHide = function() {
						getWnd('swPersonSearchWindow').findById('person_search_form').getForm().findField('PersonSurName_SurName').focus(true, 250);
					};
					params.formParams.Person_id =  person_data.Person_id;
					params.formParams.PersonEvn_id = person_data.PersonEvn_id;
					params.formParams.Server_id = person_data.Server_id;

					formEvnCytologicProtoEditWindow.show(params);
				},
				personFirname: this.FilterPanel.getForm().findField('Person_Firname').getValue(),
				personSecname: this.FilterPanel.getForm().findField('Person_Secname').getValue(),
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

			var evn_cytologic_proto_id = selected_record.get('EvnCytologicProto_id');
			var person_id = selected_record.get('Person_id');
			var server_id = selected_record.get('Server_id');
			var person_evn_id = selected_record.get('PersonEvn_id');

			if ( evn_cytologic_proto_id > 0 && person_id > 0 && server_id >= 0 ) {
				params.formParams.EvnCytologicProto_id = evn_cytologic_proto_id;
				params.formParams.Person_id =  person_id;
				params.formParams.Server_id = server_id;
				params.formParams.PersonEvn_id = person_evn_id;
				params.onHide = function() {
					var index = grid.getStore().indexOf(selected_record);
					if(index >= 0) grid.getView().focusRow(grid.getStore().indexOf(selected_record));
				};

				formEvnCytologicProtoEditWindow.show(params);
			}
		}
	},
	printDirection: function() {
		//alert('печать в разработке'); return false;
		var grid = this.SearchGrid.getGrid();

		if ( !grid.getSelectionModel().getSelected() ) {
			return false;
		}

		var
			EvnDirectionCytologic_id = grid.getSelectionModel().getSelected().get('EvnDirectionCytologic_id'),
			EvnCytologicProto_id = grid.getSelectionModel().getSelected().get('EvnCytologicProto_id');

		if(!EvnCytologicProto_id) return false;

		var report_file_name = 'f203u02_CytologicProtocol.rptdesign';
		printBirt({
			'Report_FileName': report_file_name,
			'Report_Params': '&paramPrintPage=0&paramEvnCytologicProto=' + EvnCytologicProto_id,
			'Report_Format': 'pdf'
		});

		return true;
	},
	resizable: false,
	setIsBad: function(flag) {
		//
	},
	show: function() {
		sw.Promed.swEvnCytologicProtoViewWindow.superclass.show.apply(this, arguments);		
		this.getLoadMask().show();
		/*
		//ПРИГОДИТСЯ ВОЗМОЖНО
		if ( !this.SearchGrid.getAction('action_isbad') ) {
			this.SearchGrid.addActions({
				disabled: true,
				handler: function() {
					this.setCancel(true);
				}.createDelegate(this),
				name: 'action_isbad',
				text: langs('Восстановить')
			});
		}

		if ( !this.SearchGrid.getAction('action_isnotbad') ) {
			this.SearchGrid.addActions({
				disabled: true,
				handler: function() {
					this.setCancel(false);
				}.createDelegate(this),
				name: 'action_isnotbad',
				text: langs('Отменить')
			});
		}
		*/
		this.center();
		this.maximize();
		this.loadGridWithFilter(true);

		this.viewOnly = false;
		if(arguments[0])
		{
			if(arguments[0].viewOnly) this.viewOnly = arguments[0].viewOnly;
			this.userMedStaffFact = (arguments[0].curentMedStaffFactByUser) ? arguments[0].curentMedStaffFactByUser : sw.Promed.MedStaffFactByUser.current;
		}
		
		this.SearchGrid.setActionDisabled('action_add', this.viewOnly);

		this.getLoadMask().hide();
	},
	title: langs('Журнал протоколов цитологических диагностических исследований'),
	iconCls: 'cytologica16',
	width: 800
});