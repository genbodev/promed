/**
 * swHeadMedSpecRegisterWindow - Регистр главных внештатных врачей-специалистов при МЗ
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * @access       public
 * @copyright    Copyright (c) 2016 Swan Ltd.
 * @author       Alexander Kurakin
 * @version      05.2016
 * @comment      Префикс для id компонентов MSMRW
 */
sw.Promed.swHeadMedSpecRegisterWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	title: 'Регистр главных внештатных врачей-специалистов при МЗ',
	buttonAlign: 'left',
	closable: true,
	closeAction: 'hide',
	collapsible: true,
	getButtonSearch: function() {
		return Ext.getCmp('HMSRW_SearchButton');
	},
	doReset: function() {
		var wnd = this;
		var form = wnd.FilterPanel.getForm();
		form.reset();
		this.SearchFrame.ViewActions.action_view.setDisabled(true);
		this.SearchFrame.ViewActions.action_delete.setDisabled(true);
		this.SearchFrame.ViewActions.action_refresh.setDisabled(true);
		this.SearchFrame.getGrid().getStore().removeAll();
	},
	doSearch: function(params) {
		
		if (typeof params != 'object') {
			params = {};
		}
		var wnd = this;
		var base_form = wnd.FilterPanel.getForm();
		
		var grid = this.SearchFrame.getGrid();

		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					//
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}


		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет поиск..."});
		loadMask.show();

		var post = getAllFormFieldValues(wnd.FilterPanel);

		post.limit = 100;
		post.start = 0;
		
		//log(post);

		if ( base_form.isValid() ) {
			this.SearchFrame.ViewActions.action_refresh.setDisabled(false);
			grid.getStore().removeAll();
			grid.getStore().load({
				callback: function(records, options, success) {
					loadMask.hide();
				},
				params: post
			});
		}
		
	},
	height: 550,
	openWindow: function(action) {
		if (!action || !action.toString().inlist(['add','view','edit'])) {
			return false;
		}

		var grid = this.SearchFrame.getGrid();
		if (!grid.getSelectionModel().getSelected() && action!='add') {
			return false;
		}
		var selected_record = grid.getSelectionModel().getSelected();

		var params = {};
		params.action = action;
		params.callback = function(data) {
			grid.getStore().reload();
		};
		params.onHide = function() {
			grid.getView().focusRow(grid.getStore().indexOf(selected_record));
		};

		switch(action) {
			case 'add':

				if ( getWnd('swPersonSearchWindow').isVisible() ) {
					getWnd('swPersonSearchWindow').hide();
				}
				getWnd('swPersonSearchWindow').show({
					onSelect: function(person_data) {
						getWnd('swPersonSearchWindow').hide();
						params.person = person_data;
						getWnd('swHeadMedSpecEditWindow').show(params);
					},
					searchMode: 'hms'
				});
				return false;

				break;
            case 'edit':
			case 'view':
				if (getWnd('swHeadMedSpecEditWindow').isVisible()) {
					getWnd('swHeadMedSpecEditWindow').hide();
				}
				if ( Ext.isEmpty(selected_record.get('HeadMedSpec_id')) ) {
					sw.swMsg.alert(lang['soobschenie'], lang['oshibka_vyibora_zapisi']);
					return false;
				}
				params.onHide = function(isChange) {
					if (isChange) {
						grid.getStore().reload();
					} else {
						grid.getView().focusRow(grid.getStore().indexOf(selected_record));
					}
				};

				params.PersonData = selected_record.data.Person_Fio + ' ' + selected_record.data.Person_BirthDay;
				params.HeadMedSpec_id = selected_record.data.HeadMedSpec_id;
				params.HeadMedSpecType_id = selected_record.data.HeadMedSpecType_id;
				params.HeadMedSpec_begDT = selected_record.data.HeadMedSpec_begDT;
				params.HeadMedSpec_endDT = selected_record.data.HeadMedSpec_endDT;
				params.MedWorker_id = selected_record.data.MedWorker_id;
				getWnd('swHeadMedSpecEditWindow').show(params);
				break;
		}
	},
	initComponent: function() {
		var wnd = this;

		this.onKeyDown = function (inp, e) {
			if (e.getKey() == Ext.EventObject.ENTER) {
				e.stopEvent();
				this.doSearch();
			}
		}.createDelegate(this);

		this.FilterCommonPanel = new sw.Promed.Panel({
			layout: 'form',
			autoScroll: true,
			bodyBorder: false,
			labelAlign: 'right',
			labelWidth: 140,
			border: false,
			frame: true,
			items: [{
						layout: 'form',
						labelWidth: 100,
						items: [{
							layout: 'form',
							items:
							[{
								xtype:'swdatefield',
								format:'d.m.Y',
								plugins:[ new Ext.ux.InputTextMask('99.99.9999', false) ],
								name: 'Search_Day',
								fieldLabel: 'На дату',
								width: 100,
								listeners: {
									'keydown': wnd.onKeyDown
								}
							}]
						}, {
							layout: 'form',
							items:
								[{
									xtype: 'textfieldpmw',
									width: 200,
									name: 'Person_SurName',
									fieldLabel: lang['familiya'],
									listeners: {
										'keydown': wnd.onKeyDown
									}
								}]
						},
						{
							layout: 'form',
							items: [{
								xtype: 'textfieldpmw',
								width: 200,
								name: 'Person_FirName',
								fieldLabel: lang['imya'],
								listeners: {
									'keydown': wnd.onKeyDown
								}
							}]
						},
						{
							layout: 'form',
							items: [{
								xtype: 'textfieldpmw',
								width: 200,
								name: 'Person_SecName',
								fieldLabel: lang['otchestvo'],
								listeners: {
									'keydown': wnd.onKeyDown
								}
							}]
						}, {
							layout: 'form',
							items:
							[{
								xtype: 'textfieldpmw',
								width: 200,
								name: 'HeadMedSpecType_Name',
								fieldLabel: 'Специальность',
								listeners: {
									'keydown': wnd.onKeyDown
								}
							}]
						}]
					}]
		});

		this.FilterButtonsPanel = new sw.Promed.Panel({
			autoScroll: true,
			bodyBorder: false,
			border: false,
			frame: true,
			items: [{
				layout: 'column',
				items: [{
					layout:'form',
					items: [{
						style: "padding-left: 10px",
						xtype: 'button',
						text: 'Поиск',
						iconCls: 'search16',
						id: 'HMSRW_SearchButton',
						minWidth: 100,
						handler: function() {
							wnd.doSearch();
						}
					}]
				}, {
					layout:'form',
					items: [{
						style: "padding-left: 10px",
						xtype: 'button',
						text: 'Сброс',
						iconCls: 'reset16',
						minWidth: 100,
						handler: function() {
							wnd.doReset();
						}
					}]
				}]
			}]
		});

		this.FilterPanel = getBaseFiltersFrame({
			region: 'north',
			defaults: {bodyStyle:'background:#DFE8F6;width:100%;'},
			ownerWindow: wnd,
			id: 'MSMRW_RegistrySearchFilters',
			toolBar: this.WindowToolbar,
			items: [
				this.FilterCommonPanel,
				this.FilterButtonsPanel
			]
		});

		this.SearchFrame = new sw.Promed.ViewFrame({
			actions: [
				{name: 'action_add', handler: function() { wnd.openWindow('add'); }},
                {name: 'action_edit', handler: function() { wnd.openWindow('edit'); }},
                {name: 'action_view', handler: function() { wnd.openWindow('view'); }},
				{name: 'action_delete', handler: this.deleteHeadMedSpec.createDelegate(this)},
				{name: 'action_refresh'},
				{name: 'action_print', menuConfig: null,
                    handler: function()
                    {
						wnd.SearchFrame.printRecords();
                    }
                }
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			autoLoadData: false,
			dataUrl: '/?c=HeadMedSpec&m=loadHeadMedSpecList',
			id: 'MSMRW_RegistrySearchGrid',
			pageSize: 100,
			paging: true,
			region: 'center',
			root: 'data',
			stringfields: [
				{name: 'HeadMedSpec_id', type: 'int', header: 'ID', key: true},
				{name: 'Person_id', type: 'int', hidden: true},
				{name: 'PersonEvn_id', type: 'int', hidden: true},
				{name: 'Server_id', type: 'int', hidden: true},
				{name: 'MedWorker_id', type: 'int', hidden: true},
				{name: 'HeadMedSpecType_id', type: 'int', hidden: true},
				{name: 'Person_BirthDay', type: 'string', hidden: true},
				{name: 'HeadMedSpecType_Name', type: 'string', header: 'Специальность', width: 400},
				{name: 'Person_Fio', type: 'string', header: 'ФИО', width: 150, id: 'autoexpand'},
				{name: 'HeadMedSpec_begDT', type: 'date', format: 'd.m.Y', header: lang['data_vklyucheniya_v_registr'], width: 150},
				{name: 'HeadMedSpec_endDT', type: 'date', format: 'd.m.Y', header: lang['data_isklyucheniya_iz_registra'], width: 170}
			],
			toolbar: true,
			totalProperty: 'totalCount', 
			onBeforeLoadData: function() {
                wnd.getButtonSearch().disable();
			},
			onLoadData: function() {
                wnd.getButtonSearch().enable();
			},
			onRowSelect: function(sm,index,record) {
				this.getAction('action_view').setDisabled( Ext.isEmpty(record.get('HeadMedSpec_id')) );
				this.getAction('action_add').setDisabled(false);
				if(wnd.viewOnly == true)
				{
					this.getAction('action_add').setDisabled(true);
					this.getAction('action_delete').setDisabled(true);
                	this.getAction('action_edit').setDisabled(true);
				}
				else
				{
					this.getAction('action_delete').setDisabled( Ext.isEmpty(record.get('HeadMedSpec_id')) );
                	this.getAction('action_edit').setDisabled( Ext.isEmpty(record.get('HeadMedSpec_id')) );
				}
            },
            onEnter: function() {
                var record = this.getGrid().getSelectionModel().getSelected();
                if (record && record.get('HeadMedSpec_id')) {
                    this.getAction('action_edit').execute();
                }
            },
            onDblClick: function() {
                this.onEnter();
			}
		});

		Ext.apply(this, {
			buttons: [{
				text: '-'
			},
			HelpButton(this, -1),
			{
				handler: function() {
                    wnd.hide();
				},
				iconCls: 'cancel16',
				onShiftTabAction: function() {
                    wnd.buttons[wnd.buttons.length - 2].focus();
				},
				onTabAction: function() {
                    
				},
				text: BTN_FRMCLOSE
			}],
			layout: 'border',
			items:
				[
					wnd.FilterPanel,
				{
					border: false,
					region: 'center',
					layout: 'border',
					items:[{
						border: false,
						region: 'center',
						layout: 'fit',
						items: [this.SearchFrame]
					}]
				}
				]
		});

		sw.Promed.swHeadMedSpecRegisterWindow.superclass.initComponent.apply(this, arguments);
		
	},
	layout: 'border',
	listeners: {
		'hide': function(win) {
			win.doReset();
		},
		'restore': function(win) {
			win.findById('MSMRW_RegistrySearchFilters').doLayout();
		}
	},
	maximizable: true,
	minHeight: 550,
	minWidth: 800,
	modal: false,
	plain: true,
	resizable: false,
	show: function() {
		sw.Promed.swHeadMedSpecRegisterWindow.superclass.show.apply(this, arguments);
		var wnd = this;
		var base_form = wnd.FilterPanel.getForm();

		this.restore();
		this.center();
		this.maximize();
		this.doReset();
		
		this.doLayout();
		var today = new Date();
		base_form.findField('Search_Day').setValue(today);
		this.viewOnly = false;
		if(arguments[0] && arguments[0].viewOnly)
		{
			this.viewOnly = arguments[0].viewOnly;
		}
		this.SearchFrame.getAction('action_add').setDisabled(this.viewOnly);
		this.doSearch();
	},
	deleteHeadMedSpec: function() {
		var grid = this.SearchFrame.getGrid();
		if ( !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('HeadMedSpec_id') || !grid.getSelectionModel().getSelected().get('Person_id') )
		{
			Ext.Msg.alert(lang['oshibka'], lang['ne_vyibrana_zapis']);
			return false;
		}
		var record = grid.getSelectionModel().getSelected(),
			wnd = this;
		wnd.getLoadMask('Проверка записи').show();
		Ext.Ajax.request({
			url: '/?c=HeadMedSpec&m=checkHeadMedSpec',
			params: {
				Person_id: grid.getSelectionModel().getSelected().get('Person_id')
			},
			callback: function(options, success, response) {
				wnd.getLoadMask().hide();
				if (success) {	
					var obj = Ext.util.JSON.decode(response.responseText);
					if(obj[0].cnt == 0){
						Ext.Msg.show({
							title: lang['vopros'],
							msg: 'Выбранная запись из регистра будет удалена. Если Вы желаете исключить врача из регистра, сохранив информацию о том, что он был главным внештатным специалистом, то отредактируйте данные по врачу, указав дату исключения. Удалить запись?',
							buttons: Ext.Msg.YESNO,
							fn: function(btn) {
								if (btn === 'yes') {
									wnd.getLoadMask(lang['udalenie']).show();
									Ext.Ajax.request({
										url: '/?c=HeadMedSpec&m=deleteHeadMedSpec',
										params: {
											HeadMedSpec_id: grid.getSelectionModel().getSelected().get('HeadMedSpec_id')
										},
										callback: function(options, success, response) {
											wnd.getLoadMask().hide();
											if (success) {	
												var obj = Ext.util.JSON.decode(response.responseText);
												if( obj.success )
													grid.getStore().remove(record);
											} else {
												sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_udalenii_zapisi_registra']);
											}
										}
									});
								}
							},
							icon: Ext.MessageBox.QUESTION
						});
					} else {
						sw.swMsg.alert(lang['oshibka'], 'Удалить врача из регистра нельзя, т.к. есть заявки на лекарственные средства, созданные выбранным врачом. Для исключения врача из регистра отредактируйте данные, указав дату исключения.');
					}
				} else {
					sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_udalenii_zapisi_registra']);
				}
			}
		});
	}
});
