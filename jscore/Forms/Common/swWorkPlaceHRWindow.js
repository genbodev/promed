/**
* АРМ кадровика/кадровика-администратора
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @autor		
* @copyright    Copyright (c) 2012 Swan Ltd.
* @version      апрель.2012
*/
sw.Promed.swWorkPlaceHRWindow = Ext.extend(sw.Promed.swWorkPlaceWindow,
{
	id: 'swWorkPlaceHRWindow',
	validateSearchForm: function() {
		var form = this;
		var base_form = form.FilterPanel.getForm();
		var msg = ERR_INVFIELDS_MSG;

		if (!base_form.isValid()) {
			if (!base_form.findField('MedStaffFact_date_range').validate() || !base_form.findField('MedStaffFact_disDate_range').validate()) {
				msg = lang['trebuetsya_ukazat_period'];
			}
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function()
				{
					form.FilterPanel.getFirstInvalidEl().focus(false);
				},
				icon: Ext.Msg.WARNING,
				msg: msg,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
		return true;
	},
	show: function()
	{
		sw.Promed.swWorkPlaceHRWindow.superclass.show.apply(this, arguments);

		// Свои функции при открытии

		var base_form = this.FilterPanel.getForm();
		
		this.GridPanel.setParam('start', 0, true);
		this.GridPanel.setParam('limit', 100, true);

		base_form.findField('LpuStructure_id').getStore().load({params:{Lpu_id: getGlobalOptions().lpu_id}});
		
		with(this.LeftPanel.actions) {
			action_LpuStructure.setHidden( arguments[0].ARMType !== 'lpucadradmin' );
			action_LpuPass.setHidden( arguments[0].ARMType !== 'lpucadradmin' );
		}

		var currDate = getGlobalOptions().date;

		base_form.findField('MedStaffFact_date_range').setMaxValue(currDate);
		base_form.findField('MedStaffFact_disDate_range').setMaxValue(currDate);
	},
    addCloseFilterMenu: function(gridCmp){
        var form = this;
        var grid = gridCmp;

        if ( !grid.getAction('action_isclosefilter_'+grid.id) ) {
            var menuIsCloseFilter = new Ext.menu.Menu({
                items: [
                    new Ext.Action({
                        text: lang['vse'],
                        handler: function() {
                            if (grid.gFilters) {
                                grid.gFilters.isClose = null;
                            }
                            grid.getAction('action_isclosefilter_'+grid.id).setText(lang['pokazyivat_vse']);
                            grid.getGrid().getStore().baseParams.isClose = null;
                            grid.getGrid().getStore().reload();
                        }
                    }),
                    new Ext.Action({
                        text: lang['otkryityie'],
                        handler: function() {
                            if (grid.gFilters) {
                                grid.gFilters.isClose = 1;
                            }
                            grid.getAction('action_isclosefilter_'+grid.id).setText(lang['pokazyivat_otkryityie']);
                            grid.getGrid().getStore().baseParams.isClose = 1;
                            grid.getGrid().getStore().reload();
                        }
                    }),
                    new Ext.Action({
                        text: lang['zakryityie'],
                        handler: function() {
                            if (grid.gFilters) {
                                grid.gFilters.isClose = 2;
                            }
                            grid.getAction('action_isclosefilter_'+grid.id).setText(lang['pokazyivat_zakryityie']);
                            grid.getGrid().getStore().baseParams.isClose = 2;
                            grid.getGrid().getStore().reload();
                        }
                    })
                ]
            });

            grid.addActions({
                isClose: 1,
                name: 'action_isclosefilter_'+grid.id,
                text: lang['pokazyivat_otkryityie'],
                menu: menuIsCloseFilter
            });
            grid.addActions({
                name:'action_card',
                text:lang['kartochka'],
                handler: function()
                {
                    if(form.GridPanel.ViewGridPanel.getSelectionModel().getSelected()){
                        var MedPersonal_id = form.GridPanel.ViewGridPanel.getSelectionModel().getSelected().get('MedPersonal_id');
                       // alert(MedPersonal_id);
                        window.gwtBridge.runMedWorkerEditor(getPromedUserInfo(), String(MedPersonal_id), function(result) {

                        });
                    }
                }.createDelegate(this)
            });
            grid.getGrid().getStore().baseParams.isClose = 1;
            grid.getGrid().getStore().reload();
        }

        return true;
    },
    doSearch: function() {
        var base_form = this.FilterPanel.getForm();
        var gridPanel = Ext.getCmp('WorkPlaceHRGridPanel');

        var params = base_form.getValues();
        if (gridPanel.getGrid().getStore().baseParams.isClose) {
            params.isClose = gridPanel.getGrid().getStore().baseParams.isClose;
        }
        gridPanel.getGrid().getStore().baseParams = params;

        gridPanel.getGrid().getStore().load();
    },
	buttonPanelActions: {
		action_MedStaffFactTT: {
			nn: 'action_MedStaffFactTT',
			tooltip: lang['shtatnoe_raspisanie'],
			text: lang['shtatnoe_raspisanie'],
			iconCls : 'staff32',
			disabled: false, 
			handler: function() {
				getWnd('swMedStaffFactTTViewForm').show();
			}
		},
		action_LpuStructure: {
			nn: 'action_LpuStructure',
			tooltip: lang['struktura_mo'],
			text: lang['struktura_mo'],
			iconCls: 'structure32',
			handler: function() {
				getWnd('swLpuStructureViewForm').show();
			}
		},
		action_LpuPass: {
			nn: 'action_LpuPass',
			tooltip: lang['pasport_mo'],
			text: lang['pasport_mo'],
			iconCls: 'lpu-passport32',
			handler: function() {
				getWnd('swLpuPassportEditWindow').show({
					action: 'edit',
					Lpu_id: getGlobalOptions().lpu_id
				});
			}
		},
        action_Import: {
            text: lang['obnovlenie_registrov'],
            tooltip: lang['obnovlenie_registrov'],
            iconCls: 'database-export32',
            hidden: true,
            handler: function(){
                getWnd('swImportWindow').show();
            }
		},
		action_staff_actions: {
				nn: 'action_staff_actions',
				disabled: isMedPersView(),
				text:lang['deystviya'],
				menuAlign: 'tr',
				iconCls : 'database-export32',
				tooltip: lang['deystviya'],
				hidden: (getGlobalOptions().region.nick=='kareliya'),
				menu: [{
					name: 'download_med_staff',
					text: langs('Импорт данных ФРМР'),
					//hidden: (getGlobalOptions().region.nick!='kareliya'),,
					handler: function()
					{
						getWnd('swXmlImportWindow').show({Fl:1,RegisterList_Name:'MedPersonal',RegisterList_id:4});
					}.createDelegate(this)
				}]
		},
		action_Documents:
		{
			nn: 'action_Documents',
			tooltip: langs('Инструментарий'),
			text: langs('Инструментарий'),
			iconCls : 'document32',
			disabled: false,
			hidden: ((getGlobalOptions().region.nick!='kareliya') || (getGlobalOptions().lpu_isLab == 2)),
			menuAlign: 'tr',
			menu: new Ext.menu.Menu({
				items: [
					{
						name: langs('Выгрузка регистра медработников для ФРМР новый'),
						text: langs('Выгрузка регистра медработников для ФРМР новый'),
						hidden: false,
						handler: function()
						{
							log(getGlobalOptions());
							getWnd('swExportMedPersonalToXMLFRMPWindow').show();
						}.createDelegate(this)
					}, {
						name: langs('Выгрузка штатного расписания для ФРМР новый'),
						text: langs('Выгрузка штатного расписания для ФРМР новый'),
						hidden: false,
						handler: function()
						{
							getWnd('swExportMedPersonalToXMLFRMPStaffWindow').show();
						}.createDelegate(this)
					}
				]
			})
		},
		action_PersonSearch: {
				text: 'Человек: Поиск',
				tooltip: 'Человек: Поиск',
				iconCls: 'patient-search32',
				handler: function()
				{
					getWnd('swPersonSearchWindow').show({
						onSelect: function(person_data) {
							if (!person_data.afterAdd) {
								getWnd('swPersonEditWindow').show({
									onHide: function () {
										if ( person_data.onHide && typeof person_data.onHide == 'function' ) {
											person_data.onHide();
										}
									},
									callback: function(callback_data) {
										if ( typeof callback_data != 'object' ) {
											return false;
										}

										var grid = getWnd('swPersonSearchWindow').PersonSearchViewFrame.getGrid();

										if ( typeof grid != 'object' ) {
											return false;
										}

										grid.getStore().each(function(record) {
											if ( record.get('Person_id') == callback_data.Person_id ) {
												record.set('Server_id', callback_data.Server_id);
												record.set('PersonEvn_id', callback_data.PersonEvn_id);
												record.set('PersonSurName_SurName', callback_data.PersonData.Person_SurName);
												record.set('PersonFirName_FirName', callback_data.PersonData.Person_FirName);
												record.set('PersonSecName_SecName', callback_data.PersonData.Person_SecName);
												record.set('PersonBirthDay_BirthDay', callback_data.PersonData.Person_BirthDay);
												record.commit();
											}
										});

										grid.getView().focusRow(0);
									},
									Person_id: person_data.Person_id,
									Server_id: person_data.Server_id
								});
							}
						},
						searchMode: 'all'
					});
				}
			}
	},
	initComponent: function() {
		var form = this;

		this.FilterPanel = new sw.Promed.BaseWorkPlaceFilterPanel({
			owner: form,
			keys: [{
				key: Ext.EventObject.ENTER,
				fn: function(e) {
					if (this.validateSearchForm()) {
						form.doSearch();
						this.GridPanel.setParam('start', 0);
					}
				}.createDelegate(this),
				stopEvent: true
			}, {
				ctrl: true,
				fn: function(inp, e) {
					form.doReset();
				},
				key: 188,
				scope: this,
				stopEvent: true
			}],
			filter: {
				title: lang['filtr'],
				layout: 'form',
				items: [{
					name: 'LpuBuilding_id',
					xtype: 'hidden'
				}, {
					name: 'LpuUnit_id',
					xtype: 'hidden'
				}, {
					name: 'LpuSection_id',
					xtype: 'hidden'
				}, {
					layout: 'column',
					items: [{
						width: 330,
						layout: 'form',
						labelWidth: 80,
						items:
							[{
								xtype: 'textfieldpmw',
								width: 240,
								name: 'Search_Fio',
								fieldLabel: lang['fio']
							}]
						}, {
						width: 250,
						labelWidth: 120,
						layout: 'form',
						items:
							[{
								xtype: 'swdatefield',
								format: 'd.m.Y',
								plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
								name: 'Search_BirthDay',
								fieldLabel: lang['data_rojdeniya']
							}]
						}, {
						layout: 'form',
						width: 210,
						labelWidth: 70,
						items:
							[{
								fieldLabel: lang['snils'],
								hiddenName: 'Person_Snils',
								//width: 120,
								xtype: 'swsnilsfield'
							}]
						}, {
						layout: 'form',
						width: 340,
						labelWidth: 115,
						items:
							[{
								fieldLabel: lang['doljnost'],
								hiddenName: 'PostMed_id',
								width: 210,
								xtype: 'swpostmedlocalcombo'
							}]
						}]
				}, {
					layout: 'column',
					items: [{
						layout: 'form',
						width: 390,
						labelWidth: 80,
						items: [{
							fieldLabel: lang['uroven_lpu'],
							hiddenName: 'LpuStructure_id',
							width: 300,
							xtype: 'swlpustructureelementcombo',
							listeners: {
								'select': function(combo,record,index) {
									var base_form = form.FilterPanel.getForm();

									var object = record.get('LpuStructure_Nick');
									var val = record.get('LpuStructureElement_id');

									if (!object.inlist(['LpuBuilding','LpuUnit','LpuSection'])) {
										return;
									}

									base_form.findField('LpuBuilding_id').setValue(null);
									base_form.findField('LpuUnit_id').setValue(null);
									base_form.findField('LpuSection_id').setValue(null);

									base_form.findField(object+'_id').setValue(val);
								}
							}
						}]
					}, {
						layout: 'form',
						width: 400,
						labelWidth: 140,
						items: [{
							fieldLabel: lang['tip_podrazdeleniya'],
							hiddenName: 'LpuUnitType_id',
							lastQuery: '',
							width: 250,
							xtype: 'swlpuunittypecombo'
						}]
					}, {
						layout: 'form',
						width: 360,
						labelWidth: 160,
						items: [{
							hiddenName: 'WorkType_id',
							valueField: 'WorkType_id',
							displayField: 'WorkType_Name',
							fieldLabel: lang['tip_zanyatiya_doljnosti'],
							store: new Ext.data.SimpleStore({
								autoLoad: true,
								data: [
									[ 1, 1, lang['osnovnoe_mesto_rabotyi'] ],
									[ 2, 2, lang['sovmestitelstvo'] ],
									[ 3, 3, lang['sovmeschenie'] ]
								],
								fields: [
									{ name: 'WorkType_id', type: 'int'},
									{ name: 'WorkType_Code', type: 'int'},
									{ name: 'WorkType_Name', type: 'string'}
								],
								key: 'WorkType_id',
								sortInfo: { field: 'WorkType_Code' }
							}),
							editable: false,
							xtype: 'swbaselocalcombo'
						}]
					}]
				}, {
					layout: 'column',
					items: [{
						xtype: 'fieldset',
						autoHeight: true,
						title: lang['sotrudniki_lpu'],
						style: "padding: 5px 0;",
						items: [{
							layout: 'column',
							width: 440,
							items: [{
								layout: 'form',
								labelWidth: 10,
								items: [{
									labelSeparator: '',
									name: 'medStaffFactDateRange',
									xtype: 'checkbox',
									listeners: {
										'check': function(checkbox, checked) {
											this.FilterPanel.getForm().findField('MedStaffFact_date_range').setAllowBlank(!checked);
										}.createDelegate(this)
									}
								}]
							}, {
								layout: 'form',
								labelWidth: 135,
								items: [{
									fieldLabel: lang['rabotayuschie_na_datu'],
									xtype: 'daterangefield',
									plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false) ],
									width: 180,
									name: 'MedStaffFact_date_range'
								}]
							}]
						}, {
							layout: 'column',
							width: 440,
							items: [{
								layout: 'form',
								labelWidth: 10,
								items: [{
									labelSeparator: '',
									name: 'medStaffFactEndDateRange',
									xtype: 'checkbox',
									listeners: {
										'check': function(checkbox, checked) {
											this.FilterPanel.getForm().findField('MedStaffFact_disDate_range').setAllowBlank(!checked);
										}.createDelegate(this)
									}
								}]
							}, {
								layout: 'form',
								labelWidth: 135,
								items: [{
									fieldLabel: lang['uvolennyie_v_period'],
									xtype: 'daterangefield',
									plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false) ],
									width: 180,
									name: 'MedStaffFact_disDate_range'
								}]
							}]
						}]
					}, {
						layout: 'form',
						items: [{
							style: "padding-left: 30px; padding-top: 10px;",
							xtype: 'button',
							id: form.id + 'BtnSearch',
							text: lang['nayti'],
							iconCls: 'search16',
							handler: function() {
								if (this.validateSearchForm()) {
									form.doSearch();
									this.GridPanel.setParam('start', 0);
								}
							}.createDelegate(form)
						}, {
							style: "padding-left: 30px; padding-top: 10px;",
							xtype: 'button',
							id: form.id + 'BtnClear',
							text: lang['sbros'],
							iconCls: 'reset16',
							handler: function() {
								form.doReset();
								if (this.validateSearchForm()) {
									form.doSearch();
									this.GridPanel.setParam('start', 0);
								}
							}.createDelegate(form)
						}]
					}]
				}]
			}
		});

		var StaffPanel = new sw.Promed.ViewFrame(
		{
			title:lang['mesto_rabotyi_sotrudnika'],
			id: 'WorkPlaceHRGridPanel',
			object: 'MedStaffFact',
			editformclassname: 'swMedStaffFactEditWindow',
			dataUrl: '/?c=MedPersonal&m=getMedPersonalGridPaged',
			height:303,
			pageSize: 100,
			paging: true,
			toolbar: true,
			root: 'data',
			totalProperty: 'totalCount',
			autoLoadData: false,
			allowedPersonKeys: (getGlobalOptions().region.nick == 'kareliya')?(['F10']):(null),
			remoteSort: true,
			stringfields:
			[
				{name: 'MedStaffFact_id', type: 'int', header: 'ID', key: true},
				{name: 'MedPersonal_id', type: 'int', hidden: true, isparams: true},
				{name: 'MedPersonal_TabCode', type: 'string', header: lang['tab_№'], width: 50},
				{id: 'autoexpand', name: 'MedPersonal_FIO',  type: 'string', header: lang['fio_vracha']},
				{name: 'LpuSection_Name',  type: 'string', header: lang['strukturnyiy_element_lpu'], width: 200},
				{name: 'PostMed_Name',  type: 'string', header: lang['doljnost'], width: 150},
				{name: 'MedStaffFact_Stavka',  type: 'float', header: lang['stavka'], width: 55},
				{name: 'MedStaffFact_setDate',  type: 'date', header: lang['nachalo'], width: 75},
				{name: 'MedStaffFact_disDate',  type: 'date', header: lang['okonchanie'], width: 75}
			],
			actions:
			[
				{name:'action_add', handler: function()
					{
						var lpuStruct = new Array();
						lpuStruct.Lpu_id = getGlobalOptions().lpu_id;
						lpuStruct.LpuBuilding_id = null;
						lpuStruct.LpuUnit_id = null;
						lpuStruct.LpuSection_id = null;
						lpuStruct.description = '';
						window.gwtBridge.runWorkPlaceEditor(getPromedUserInfo(), null, lpuStruct, function(result) {
							if ( Number(result) > 0 )
								form.GridPanel.ViewGridPanel.getStore().reload();
						});
					}
				},
				{name:'action_edit', handler: function()
					{
						if ( form.GridPanel.ViewGridPanel.getSelectionModel().getSelected() )
						{
							var row = form.GridPanel.ViewGridPanel.getSelectionModel().getSelected();
							var lpuStruct = new Array();
							lpuStruct.Lpu_id = getGlobalOptions().lpu_id;
							lpuStruct.LpuBuilding_id = null;
							lpuStruct.LpuUnit_id = null;
							lpuStruct.LpuSection_id = null;
							lpuStruct.description = '';
							var MedStaffFact_id = row.data.MedStaffFact_id;
							window.gwtBridge.runWorkPlaceEditor(getPromedUserInfo(), String(MedStaffFact_id), lpuStruct, function(result) {
								if ( Number(result) > 0 )
									form.GridPanel.ViewGridPanel.getStore().reload();
							});				
						}
					}
				},
				{name:'action_view', disabled: true, hidden: true, handler: function() {}},
				{name:'action_delete', disabled: isMedPersView(), 
					handler: function() {
						if ( form.GridPanel.ViewGridPanel.getSelectionModel().getSelected() )
						{
							sw.swMsg.show({
								icon: Ext.MessageBox.QUESTION,
								msg: lang['vyi_hotite_udalit_zapis'],
								title: lang['podtverjdenie'],
								buttons: Ext.Msg.YESNO,
								fn: function(buttonId, text, obj)
								{
									if ('yes' == buttonId)
									{
										var row = form.GridPanel.ViewGridPanel.getSelectionModel().getSelected();
										var staff_id = row.get('MedStaffFact_id');
										window.gwtBridge.deleteWorkPlace(getPromedUserInfo(), String(staff_id), function(result) {
											form.GridPanel.ViewGridPanel.getStore().reload();
										}.createDelegate(this));
									}
								}.createDelegate(this)
							});
						}
					}.createDelegate(this)
				},
				{name:'action_refresh'},
				{name:'action_print', hidden: isMedPersView()}
			]
		});
        StaffPanel.ViewToolbar.on('render', function(vt){return this.addCloseFilterMenu(StaffPanel);}.createDelegate(this));
        this.GridPanel = StaffPanel;
		sw.Promed.swWorkPlaceHRWindow.superclass.initComponent.apply(this, arguments);
		this.WindowToolbar.hide();
		
	}
});