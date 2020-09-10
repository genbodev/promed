/**
* swEvnDirectionCytologicViewWindows - Журнал направлений на цитологическое диагностическое исследование
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* Использует: Направление на цитологическое диагностическое исследование (swEvnDirectionCytologicEditWindow)
*/

sw.Promed.swEvnDirectionCytologicViewWindows = Ext.extend(sw.Promed.BaseForm, {
	border: false,
	buttonAlign: 'left',
	closeAction: 'hide',
	curentMedStaffFactByUser: sw.Promed.MedStaffFactByUser.current,
	deleteEvnDirectionCytologic: function() {
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
		var id = selected_record.get('EvnDirectionCytologic_id');

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
							EvnDirectionCytologic_id: id
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
						url: '/?c=EvnDirectionCytologic&m=deleteEvnDirectionCytologic'
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
	id: 'EvnDirectionCytologicViewWindow',
	initComponent: function() {
		var win = this;
		this.datePeriodToolbar = new sw.Promed.datePeriodToolbar({
			curDate: getGlobalOptions().date,
			mode: 'day',
			onSelectPeriod: function(begDate,endDate,allowLoad)
			{
				this.findById('EDCVW_begDate').setValue(begDate.format('d.m.Y'));
				this.findById('EDCVW_endDate').setValue(endDate.format('d.m.Y'));
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
			id: 'EvnDirectionCytologicFilterForm',
			region: 'north',

			items: [{
						name: 'begDate',
						id: 'EDCVW_begDate',
						xtype: 'hidden'
					},
					{
						name: 'endDate',
						id: 'EDCVW_endDate',
						xtype: 'hidden'

					},{
				comboSubject: 'YesNo',
				fieldLabel: langs('Срочность'),
				hiddenName: 'EvnDirectionCytologic_IsCito',
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
								name: 'EvnDirectionCytologic_Ser',
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
								name: 'EvnDirectionCytologic_Num',
								width: 100,
								xtype: 'textfield'
							}
						]
					},{
						bodyStyle: 'padding-right: 10px;',
						border: false,
						labelWidth: 200,
						layout: 'form',
						items: [
							{
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
							}
						]
					}
				]
					
			
			}, /*{
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
			},*/{
					
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
				{ name: 'action_add', handler: function() { this.openEvnDirectionCytologicEditWindow('add'); }.createDelegate(this) },
				{ name: 'action_edit', handler: function() { this.openEvnDirectionCytologicEditWindow('edit'); }.createDelegate(this) },
				{ name: 'action_view', handler: function() { this.openEvnDirectionCytologicEditWindow('view'); }.createDelegate(this) },
				{ name: 'action_delete', handler: function() { this.deleteEvnDirectionCytologic(); }.createDelegate(this) },
				{ name: 'action_print',
					menuConfig: {
						printObject: { text: langs('Печать направления'), handler: function() { this.printEvnDirectionCytologic(); }.createDelegate(this) }
					}
				}
			],
			autoLoadData: false,
			signObject: 'EvnDirectionCytologic',
			dataUrl: '/?c=EvnDirectionCytologic&m=loadEvnDirectionCytologicGrid',
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
				var flag = (record && record.get('EvnDirectionCytologic_id')) ? false : true;

				if(!flag && record.get('DirFailType_id')) flag = true;
				this.SearchGrid.ViewActions.action_add.setDisabled(false);
				this.SearchGrid.ViewActions.action_edit.setDisabled(flag);
				this.SearchGrid.ViewActions.action_delete.setDisabled(flag);
				this.SearchGrid.ViewActions.action_reject.setDisabled(flag);
				
				/*
				if(win.viewOnly == true)
				{
					this.SearchGrid.ViewActions.action_add.setDisabled(true);
					this.SearchGrid.ViewActions.action_edit.setDisabled(true);
					this.SearchGrid.ViewActions.action_delete.setDisabled(true);
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
				}
				*/
			}.createDelegate(this),
			paging: true,
			region: 'center',
			root: 'data',
			stringfields: [
				{ name: 'EvnDirectionCytologic_id', type: 'int', header: 'ID', key: true },
				{ name: 'accessType', type: 'string', hidden: true },
				{ name: 'Person_id', type: 'int', hidden: true },
				{ name: 'PersonEvn_id', type: 'int', hidden: true },
				{ name: 'EvnStatus_id', type: 'int', hidden: true },
				{ name: 'DirFailType_id', type: 'int', hidden: true },
				{ name: 'Server_id', type: 'int', hidden: true },
				{ name: 'EvnDirectionCytologic_HasProto', type: 'checkbox', header: langs('Протокол'), width: 70 },
				{ name: 'EvnDirectionCytologic_Ser', type: 'string', header: langs('Серия направления'), width: 120 },
				{ name: 'EvnDirectionCytologic_Num', type: 'string', header: langs('Номер направления'), width: 120 },
				{ name: 'EvnDirectionCytologic_setDate', type: 'date', format: 'd.m.Y', header: langs('Дата направления') },
				{ name: 'LpuSection_Name', type: 'string', header: langs('Отделение'), width: 250},
				{ name: 'MedPersonal_Fio', header: langs('Врач'), width: 250},
				{ name: 'EvnDirectionCytologic_NumCard', type: 'string', header: langs('№ карты'), width: 80 },
				{ name: 'Person_Surname', type: 'string', header: langs('Фамилия'), width: 100 },
				{ name: 'Person_Firname', type: 'string', header: langs('Имя'), width: 100 },
				{ name: 'Person_Secname', type: 'string', header: langs('Отчество'), width: 100 },
				{ name: 'Person_Birthday', type: 'date', format: 'd.m.Y', header: langs('Дата рождения') },
				{ name: 'Lpu_Name', type: 'string', header: langs('МО направления'), width: 250 },
				{ name: 'EvnDirectionCytologic_IsCito', type: 'string', header: langs('Срочность'), width: 70 }
			],
			title: 'Направления на цитологическое исследование: Список',
			totalProperty: 'totalCount'
		});
		
		this.SearchGrid.ViewGridPanel.view = new Ext.grid.GridView({
			getRowClass: function (row, index) {
				var cls = 'x-grid-panel';
				if ( row.get('DirFailType_id') ) {
					//cls = cls + 'x-grid-rowgray';
					cls = 'x-grid-rowgray';
				}
				// else {
				// 	cls = 'x-grid-panel'; 
				// }
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

		sw.Promed.swEvnDirectionCytologicViewWindows.superclass.initComponent.apply(this, arguments);
	},
	keys: [{
		fn: function(inp, e) {
			var win = Ext.getCmp('EvnDirectionCytologicViewWindow');

			switch ( e.getKey() ) {
				case Ext.EventObject.INSERT:
					win.openEvnDirectionCytologicEditWindow('add');
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
			this.SearchGrid.gFilters = null;
		}
		else {
			var params = base_form.getValues();

			params.limit = 100;
			params.start = 0;

			if(!params.begDate) params.begDate = Ext.util.Format.date(this.datePeriodToolbar.dateMenu.getValue1(), 'd.m.Y');
			if(!params.endDate) params.endDate = Ext.util.Format.date(this.datePeriodToolbar.dateMenu.getValue2(), 'd.m.Y');
		
			this.SearchGrid.loadData({
				globalFilters: params
			});
		}
	},
	maximizable: true,
	maximized: true,
	modal: false,
	plain: true,
	openEvnDirectionCytologicEditWindow: function(action) {
		var win = this;
		if ( action != 'add' && action != 'edit' && action != 'view' ) {
			return false;
		}

		if ( action == 'add' && getWnd('swPersonSearchWindow').isVisible() ) {
			sw.swMsg.alert(langs('Сообщение'), langs('Окно поиска человека уже открыто'));
			return false;
		}

		if ( getWnd('swEvnDirectionCytologicEditWindow').isVisible() ) {
			sw.swMsg.alert(langs('Сообщение'), langs('Окно редактирования направление на цитологическое исследование уже открыто'));
			return false;
		}

		var grid = this.SearchGrid.getGrid();
		var params = new Object();

		params.action = action;
		params.callback = function(data) {
			if(getWnd('swPersonSearchWindow').isVisible()){
				//закрыть форму
				getWnd('swPersonSearchWindow').hide();
			}
			if ( !data || !data.evnDirectionCytologicData ) {
				return false;
			}

			// Обновить запись в grid
			var record = grid.getStore().getById(data.evnDirectionCytologicData.EvnDirectionCytologic_id);

			if ( record ) {
				var grid_fields = new Array();

				grid.getStore().fields.eachKey(function(key, item) {
					grid_fields.push(key);
				});

				for ( i = 0; i < grid_fields.length; i++ ) {
					if(grid_fields[i] in data.evnDirectionCytologicData) record.set(grid_fields[i], data.evnDirectionCytologicData[grid_fields[i]]);
				}

				record.commit();
			}
			else {
				if ( grid.getStore().getCount() == 1 && !grid.getStore().getAt(0).get('EvnDirectionCytologic_id') ) {
					grid.getStore().removeAll();
				}

				grid.getStore().loadData({ data: [ data.evnDirectionCytologicData ], totalCount: 1 }, true);
			}
		}
		params.formParams = new Object();
		params.curentMedStaffFactByUser = (win && win.curentMedStaffFactByUser) ? win.curentMedStaffFactByUser : sw.Promed.MedStaffFactByUser.current;

		if ( action == 'add' ) {
			getWnd('swPersonSearchWindow').show({
				onClose: Ext.emptyFn,
				onSelect: function(person_data) {
					params.onHide = function() {
						getWnd('swPersonSearchWindow').findById('person_search_form').getForm().findField('PersonSurName_SurName').focus(true, 250);
					};
					params.formParams.Person_id =  person_data.Person_id;
					params.formParams.PersonEvn_id = person_data.PersonEvn_id;
					params.formParams.Server_id = person_data.Server_id;

					getWnd('swEvnDirectionCytologicEditWindow').show(params);
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

			var evn_direction_сytologic_id = selected_record.get('EvnDirectionCytologic_id');
			var person_id = selected_record.get('Person_id');
			var server_id = selected_record.get('Server_id');

			if ( evn_direction_сytologic_id > 0 && person_id > 0 && server_id >= 0 ) {
				params.formParams.EvnDirectionCytologic_id = evn_direction_сytologic_id;
				params.formParams.Person_id = person_id;
				params.formParams.Server_id = server_id;				
				params.onHide = function() {
					grid.getView().focusRow(grid.getStore().indexOf(selected_record));
				};

				getWnd('swEvnDirectionCytologicEditWindow').show(params);
			}
		}
	},	
	printEvnDirectionCytologic: function() {
		var grid = this.SearchGrid.getGrid();
		if ( !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('EvnDirectionCytologic_id') ) {
			return false;
		}

		var report_file_name = 'f203u02_Directioncytologic.rptdesign';
		//alert('печать в разработке'); return false;
		printBirt({
			'Report_FileName': report_file_name,
			'Report_Params': '&paramEvnDirectioncytologic=' + grid.getSelectionModel().getSelected().get('EvnDirectionCytologic_id'),
			'Report_Format': 'pdf'
		});
	},
	openSelectEvnStatusCauseWindow: function(){
		var win = this;
		var selected_record = this.SearchGrid.getGrid().getSelectionModel().getSelected();

		getWnd('swSelectEvnStatusCauseWindow').show({
			Evn_id: selected_record.get('EvnDirectionCytologic_id'),
			EvnClass_id: 27, //выписка направлений
			callback(values) {
				win.cancelEvnDirectionCytologic(values);
			}
		});
	},
	cancelEvnDirectionCytologic: function(values){
		if(!values) return false;
		var selected_record = this.SearchGrid.getGrid().getSelectionModel().getSelected();
		if(selected_record && selected_record.get('EvnDirectionCytologic_id') && selected_record.get('EvnDirectionCytologic_id')>0){
			var params = {
				EvnDirectionCytologic_id: selected_record.get('EvnDirectionCytologic_id'),
				EvnStatusCause_id: values.EvnStatusCause_id,
				EvnStatusHistory_Cause: values.EvnStatusHistory_Cause
			}
			var grid = this.SearchGrid.getGrid();
			this.getLoadMask().show();
			Ext.Ajax.request({
				params: params,
				failure: function(response, options) {
					this.getLoadMask().hide();
					sw.swMsg.alert(langs('Ошибка'), langs('Ошибка при отмене направления') );
				}.createDelegate(this),
				success: function(response, options) {
					this.getLoadMask().hide();
					var response_obj = Ext.util.JSON.decode(response.responseText);					
					var row = grid.getStore().getById(selected_record.id);
					if(row){
						row.set('DirFailType_id', 11);
						grid.getSelectionModel().clearSelections();
					}
					//grid.getStore().remove(selected_record);
				}.createDelegate(this),
				url: '/?c=EvnDirectionCytologic&m=cancelEvnDirectionCytologic'
			});
		}
	},
	resizable: false,	
	show: function() {
		var win = this;
		sw.Promed.swEvnDirectionCytologicViewWindows.superclass.show.apply(this, arguments);

		this.curentMedStaffFactByUser = (arguments[0].curentMedStaffFactByUser) ? arguments[0].curentMedStaffFactByUser : sw.Promed.MedStaffFactByUser.current;
		this.getLoadMask().show();
		this.datePeriodToolbar.onShow(true);
		
		if ( !this.SearchGrid.getAction('action_reject') ) {
			this.SearchGrid.addActions({
				disabled: true,
				hidden: false,
				handler: function() {
					this.openSelectEvnStatusCauseWindow();
				}.createDelegate(this),
				name: 'action_reject',
				text: langs('Отклонить')
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
		
		this.SearchGrid.setActionDisabled('action_add', this.viewOnly);
		this.loadGridWithFilter(true);
		this.getLoadMask().hide();
	},
	title: langs('Журнал направлений на цитологическое диагностическое исследование'),
	iconCls: 'cytologica16',
	width: 800
});