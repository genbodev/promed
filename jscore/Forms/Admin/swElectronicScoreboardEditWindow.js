/**
 * swElectronicScoreboardEditWindow - электронное табло форма добавления\редактирования\просмотра
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package     Admin
 * @access      public
 * @copyright	Copyright (c) 2017 Swan Ltd.
 * @author       Sysolin Maksim
 * @version      08.2017
 */
/*NO PARSE JSON*/
sw.Promed.swElectronicScoreboardEditWindow = Ext.extend(sw.Promed.BaseForm,
    {
        maximizable: false,
        maximized: true,
        height: 600,
        width: 900,
        id: 'swElectronicScoreboardEditWindow',
        title: 'Электронное табло',
        layout: 'border',
        resizable: true,
        formAction: null,
        isLED: null,
        listeners: {

            'hide': function(wnd) {
                wnd.hide();
            }
        },
        onGridRowSelect: function(grid) {

            var wnd = this;

            if (wnd.formAction && wnd.formAction != 'view' && grid.readOnly !== true) {
                grid.ViewActions.action_delete.setDisabled(false);
            }
        },
        clearGridFilter: function(grid) { //очищаем фильтры (необходимо делать всегда перед редактированием store)

            grid.getGrid().getStore().clearFilter();
        },
        setGridFilter: function(grid) { //скрывает удаленные записи
            grid.getGrid().getStore().filterBy(function(record){
                return (record.get('state') != 'delete');
            });
        },
        deleteLpuOfficeAssign: function(){

            var wnd = this,
                grid = wnd.LpuBuildingOfficeScoreboardGrid.getGrid(),
                record = grid.getSelectionModel().getSelected();

            if (record) {
                sw.swMsg.show({
                    icon: Ext.MessageBox.QUESTION,
                    msg: langs('Вы хотите удалить запись?'),
                    title: langs('Подтверждение'),
                    buttons: Ext.Msg.YESNO,
                    fn: function(buttonId, text, obj) {

                        if ('yes' == buttonId) {
                            Ext.Ajax.request({
                                url: '/?c=LpuBuildingOffice&m=deleteLpuBuildingOfficeScoreboard',
                                params: {
                                    LpuBuildingOfficeScoreboard_id: record.get('LpuBuildingOfficeScoreboard_id')
                                },
                                success: function (resp) {
									wnd.LpuBuildingOfficeScoreboardGrid.refreshRecords(null,0)
                                },
                                error: function (elem, resp) {
                                    if (!resp.result.success) {
                                        Ext.Msg.alert(langs('Ошибка'), langs('Ошибка запроса к серверу. Попробуйте повторить операцию.'));
                                    }
                                }
                            });
                        } else {
                            return false;
                        }
                    }
                });
            }
        },
        deleteGridRecord: function(){

            var wnd = this,
                view_frame = this.ElectronicScoreboardQueueLinkGrid,
                grid = view_frame.getGrid(),
                selected_record = grid.getSelectionModel().getSelected();

            sw.swMsg.show({
                icon: Ext.MessageBox.QUESTION,
                msg: langs('Вы хотите удалить запись?'),
                title: langs('Подтверждение'),
                buttons: Ext.Msg.YESNO,
                fn: function(buttonId, text, obj) {

                    if ('yes' == buttonId) {
                        if (selected_record.get('state') == 'add') {
                            grid.getStore().remove(selected_record);
                        } else {
                            selected_record.set('state', 'delete');
                            selected_record.commit();
                            wnd.setGridFilter(view_frame);
                            wnd.doSave({refreshGrid: true})
                        }
                    } else {
                        if (grid.getStore().getCount()>0) {
                            grid.getView().focusRow(0);
                        }
                    }
                }
            });
        },
        openLpuBuildingOfficeAssignWindow: function(action) {

            var wnd = this,
                grid = this.LpuBuildingOfficeScoreboardGrid.getGrid(),
                form = this.formEditPanel.getForm();

            var params = {};


            params.Lpu_id = form.findField('Lpu_id').getValue();
            params.LpuBuilding_id = form.findField('LpuBuilding_id').getValue();
            params.ElectronicScoreboard_id = form.findField('ElectronicScoreboard_id').getValue();

            params.onSave = function() {
				wnd.LpuBuildingOfficeScoreboardGrid.refreshRecords(null,0)
			};

            if (action === 'edit') {
				var record = grid.getSelectionModel().getSelected();
				params.LpuBuildingOfficeScoreboard_id = record.get('LpuBuildingOfficeScoreboard_id');
			}

            if (!params.Lpu_id) {
                log('Не указан идентификатор МО');
                return false;
            }

            if (!params.ElectronicScoreboard_id) {
                log('Не указан идентификатор табло');
                return false;
            }

            params.action = action;
            getWnd('swLpuBuildingOfficeAssign').show(params);
        },
        openElectronicScoreboardQueueLinkEditWindow: function(action) {
            
            var wnd = this,
                grid = this.ElectronicScoreboardQueueLinkGrid.getGrid(),
                form = this.formEditPanel.getForm();

            var params = new Object();

            params.Lpu_id = form.findField('Lpu_id').getValue();
			params.LpuBuilding_id = form.findField('LpuBuilding_id').getValue();

            if (!params.Lpu_id) {
                log('Не указан lpu_id');
                return false;
            }

            params.action = action;

            var view_frame = wnd.ElectronicScoreboardQueueLinkGrid,
                store = view_frame.getGrid().getStore(),
                existedQueueIdList = {},
                existedServicesIdList = [];

            params.showElectronicServiceCombo = wnd.isLED;

            if (store && store.getCount() > 0) {

                store.each(function(rec) {

                    if (!existedQueueIdList[rec.get('ElectronicQueueInfo_id')])
                        existedQueueIdList[rec.get('ElectronicQueueInfo_id')] = [];

                    var electronicService_id = rec.get('ElectronicService_id');
                    if (electronicService_id) existedQueueIdList[rec.get('ElectronicQueueInfo_id')].push(electronicService_id);
                })
            }

            params.existedQueueIdList = existedQueueIdList;
            params.existedServicesIdList = existedServicesIdList;

            if (action == 'add') {

                params.onSave = function(data) {

                    var record_count = store.getCount();
                    if ( record_count == 1 && !store.getAt(0).get('ElectronicScoreboardQueueLink_id') ) {
                        view_frame.removeAll({addEmptyRecord: false});
                    }
                    
                    var index = store.findBy(function(rec) {
                    	return rec.get('ElectronicQueueInfo_Code') == data.ElectronicQueueInfo_Code;
                    });

                    if (index != -1) {
                    	sw.swMsg.show({
							buttons: Ext.Msg.OK,
							icon: Ext.Msg.WARNING,
							title: ERR_WND_TIT,
							msg: 'На электронное табло нельзя назначить две очереди с одним и тем же кодом',
							fn: function( buttonId ) {
								if (buttonId == 'ok') {
									// do nothing
								}
							}							 
						});
						
						return false;
                    }

                    var record = new Ext.data.Record.create(view_frame.jsonData['store']);
                    wnd.clearGridFilter(view_frame);

                    data.ElectronicScoreboardQueueLink_id = Math.floor(Math.random()*10000); //генерируем временный идентификатор
                    data.state = 'add';

                    store.insert(record_count, new record(data));
                    wnd.setGridFilter(view_frame);
                };

                getWnd('swElectronicScoreboardQueueLinkEditWindow').show(params);
            }
        },
        doSave: function(options) {

            if (typeof options != 'object') { options = new Object() }

            var wnd = this,
                form = this.formEditPanel.getForm(),
                loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение..."}),
                grid = wnd.ElectronicScoreboardQueueLinkGrid;

            if (!form.isValid()) {

                sw.swMsg.show( {

                    buttons: Ext.Msg.OK,
                    fn: function() {
                        wnd.formEditPanel.getFirstInvalidEl().focus(false);
                    },
                    icon: Ext.Msg.WARNING,
                    msg: ERR_INVFIELDS_MSG,
                    title: ERR_INVFIELDS_TIT
                });
                return false;
            }

            var params = {};

            if (form.findField('Lpu_id').disabled) {
                params.Lpu_id = form.findField('Lpu_id').getValue();
            }

			if ( wnd.findById(wnd.id + '_IsShownTimetableCheckbox').getValue() == true ) {
				form.findField('ElectronicScoreboard_IsShownTimetable').setValue(2);

				if ( grid.getGrid().getStore().getCount() > 0 ) {
					grid.getGrid().getStore().each(function(rec) {
						if ( rec.get('state') == 'add' ) {
							grid.getGrid().getStore().remove(rec);
						}
						else {
							rec.set('state', 'delete');
							rec.commit();
						}
					});
				}
			}
			else {
				form.findField('ElectronicScoreboard_IsShownTimetable').setValue(1);
			}

            params.queueData = wnd.ElectronicScoreboardQueueLinkGrid.getJSONChangedData();

			params.ElectronicScoreboard_IsCalled = form.findField('ElectronicScoreboard_IsCalledCheckbox').getValue() ? 2:1;
			//params.ElectronicScoreboard_IsShownForEachDoctor = form.findField('ElectronicScoreboard_IsShownForEachDoctor').getValue() ? 2:1;

            loadMask.show();

            form.submit({

                failure: function(result_form, action) {
                    loadMask.hide();
                },
				params: params,
                success: function(result_form, action) {

                    loadMask.hide();

                    if (action.result) {
						if (action.result.ElectronicScoreboard_id && grid.readOnly !== true) {
							grid.getGrid().getStore().baseParams = {
								ElectronicScoreboard_id: action.result.ElectronicScoreboard_id,
								start: 0,
								limit: 100
							};
							grid.loadData();
						}
						if ((typeof options.callback === 'function') && (typeof options === 'object')) {
							options.callback();
						}
					}
					else {
						sw.swMsg.alert(langs('Ошибка'), 'При сохранении возникли ошибки');
					}

                }.createDelegate(this)
            });
        },
        initComponent: function()
        {
            var wnd = this;

            this.formEditPanel = new Ext.FormPanel({
                region: 'north',
                labelAlign: 'right',
                layout: 'form',
                autoHeight: true,
                labelWidth: 100,
                frame: true,
                border: false,
				items: [{
					border: false,
					layout: 'column',
					labelWidth: 140,
					anchor: '10',
					items: [{
						layout: 'form',
						columnWidth: .50,
						border: false,
						items: [
							{
								xtype: 'fieldset',
								autoHeight: true,
								collapsible: false,
								title: 'Основные настройки',
								style: 'margin-top: 5px; margin-right: 10px; height: 340px;',
								labelWidth: 230,
								items: [
									{
										name: 'ElectronicScoreboard_id',
										xtype: 'hidden'
									},
									new sw.Promed.SwLpuSearchCombo({
										fieldLabel: 'МО',
										hiddenName: 'Lpu_id',
										allowBlank: false,
										listWidth: 400,
										width: 350,
										listeners: {
											'change': function (combo, newValue, oldValue) {
												if ( !newValue || newValue == oldValue ) {
													return false;
												}

												var
													base_form = wnd.formEditPanel.getForm(),
													buildingCombo = base_form.findField('LpuBuilding_id'),
													sectionCombo = base_form.findField('LpuSection_id'),
													index,
													LpuBuilding_id = buildingCombo.getValue(),
													LpuSection_id = sectionCombo.getValue();

												buildingCombo.clearValue();
												sectionCombo.clearValue();

												if ( newValue == getGlobalOptions().lpu_id ) {
													swLpuBuildingGlobalStore.clearFilter();
													swLpuBuildingGlobalStore.filterBy(function(rec) {
														return (rec.get('Lpu_id') == newValue);
													});
													base_form.findField('LpuBuilding_id').getStore().loadData(getStoreRecords(swLpuBuildingGlobalStore));

													if ( !Ext.isEmpty(LpuBuilding_id) ) {
														index = buildingCombo.getStore().findBy(function(rec) {
															return (rec.get('LpuBuilding_id') == LpuBuilding_id);
														});
														if ( index >= 0 ) {
															buildingCombo.setValue(LpuBuilding_id);
														}
													}

													setLpuSectionGlobalStoreFilter({ Lpu_id: newValue });
													base_form.findField('LpuSection_id').getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));

													if ( !Ext.isEmpty(LpuSection_id) ) {
														index = sectionCombo.getStore().findBy(function(rec) {
															return (rec.get('LpuSection_id') == LpuSection_id);
														});
														if ( index >= 0 ) {
															sectionCombo.setValue(LpuSection_id);
														}
													}
												}
												else {
													buildingCombo.getStore().baseParams.Lpu_id = newValue;
													buildingCombo.getStore().load({
														callback: function() {
															if ( !Ext.isEmpty(LpuBuilding_id) ) {
																index = buildingCombo.getStore().findBy(function(rec) {
																	return (rec.get('LpuBuilding_id') == LpuBuilding_id);
																});
																if ( index >= 0 ) {
																	buildingCombo.setValue(LpuBuilding_id);
																}
															}
														},
														params: {
															mode: 'combo'
														}
													});

													sectionCombo.getStore().baseParams.Lpu_id = newValue;
													sectionCombo.getStore().load({
														callback: function() {
															if ( !Ext.isEmpty(LpuSection_id) ) {
																index = sectionCombo.getStore().findBy(function(rec) {
																	return (rec.get('LpuSection_id') == LpuSection_id);
																});
																if ( index >= 0 ) {
																	sectionCombo.setValue(LpuSection_id);
																}
															}
														},
														params: {
															mode: 'combo'
														}
													});
												}
											}
										}
									}), {
										allowBlank: false,
										fieldLabel: 'Подразделение',
										hiddenName: 'LpuBuilding_id',
										id: this.id + '_LpuBuildingCombo',
										linkedElements: [
											this.id + '_LpuSectionCombo'
										],
										listWidth: 600,
										width: 350,
										xtype: 'swlpubuildingglobalcombo'
									}, {
										fieldLabel: 'Отделение',
										hiddenName: 'LpuSection_id',
										id: this.id + '_LpuSectionCombo',
										listWidth: 600,
										parentElementId: this.id + '_LpuBuildingCombo',
										width: 350,
										xtype: 'swlpusectionglobalcombo'
									}, {
										name: 'ElectronicScoreboard_Code',
										fieldLabel: 'Код',
										xtype: 'textfield',
										allowBlank: false,
										width: 150
									}, {
										name: 'ElectronicScoreboard_Name',
										fieldLabel: 'Наименование',
										xtype: 'textfield',
										allowBlank: false,
										width: 350
									}, {
										name: 'ElectronicScoreboard_IsLED',
										xtype: 'hidden'
									}, {
										fieldLabel: 'Тип',
										xtype: 'radiogroup',
										width: 350,
										columns: 1,
										name: 'ElectronicScoreboard_IsLED_rg',
										id: 'ElectronicScoreboard_IsLED_rg',
										items: [
											{
												name: 'ElectronicScoreboard_IsLED',
												boxLabel: 'Телевизор',
												inputValue: 1
											},
											{
												name: 'ElectronicScoreboard_IsLED',
												boxLabel: 'Светодиодное табло',
												inputValue: 2
											}
										],
										listeners: {
											'change': function(radioGroup, radioBtn) {
												if (radioBtn) {

													if (radioBtn.inputValue == 1) {

														var store = wnd.ElectronicScoreboardQueueLinkGrid.getGrid().getStore();

														if (store && store.getCount() > 0) {

															var existedServicesIdList = [];

															store.each(function(rec) {
																var electronicService_id = rec.get('ElectronicService_id');
																if (electronicService_id) existedServicesIdList.push(electronicService_id);
															})

															// мы не можем переключить тип табло на ТВ
															// до тех пор пока есть связанные пункты обслуживания
															if (existedServicesIdList && existedServicesIdList.length > 0) {

																sw.swMsg.show({

																	msg: 'Тип "Телевизор" недоступен, пока есть связанные пункты обслуживания',
																	title: ERR_INVFIELDS_TIT,
																	icon: Ext.Msg.WARNING,
																	buttons: Ext.Msg.OK,
																	fn: function() {

																		var radioGroup = wnd.formEditPanel.getForm().findField('ElectronicScoreboard_IsLED_rg').items;

																		radioGroup.each(function(btn){
																			if (btn.inputValue == 2) btn.setValue(2);
																		});
																	}
																});

																return false;
															}
														}
													}

													wnd.toggleFormLedFields(radioBtn.inputValue);
												}
											}
										}
									}, {
										name: 'ElectronicScoreboard_begDate',
										fieldLabel: 'Дата начала',
										xtype: 'swdatefield',
										allowBlank: false,
										plugins: [ new Ext.ux.InputTextMask('99.99.9999', false)],
										width: 100
									}, {
										name: 'ElectronicScoreboard_endDate',
										fieldLabel: 'Дата окончания',
										xtype: 'swdatefield',
										allowBlank: true,
										plugins: [ new Ext.ux.InputTextMask('99.99.9999', false)],
										width: 100
									}
								]
							}

						]
					},
						{
							layout: 'form',
							columnWidth: .50,
							border: false,
							items: [
								{
									xtype: 'fieldset',
									autoHeight: true,
									collapsible: false,
									title: 'Опции',
									style: 'margin-top: 5px; margin-right: 10px; height: 340px;',
									labelWidth: 230,
									items: [
										{
											fieldLabel: 'Отображение расписания',
											id: this.id + '_IsShownTimetableCheckbox',
											listeners: {
												'check': function(field, checked) {
													var base_form = wnd.formEditPanel.getForm();

													if (checked) {

														base_form.findField('ElectronicScoreboard_RefreshInSeconds').enable();
														wnd.ElectronicScoreboardQueueLinkGrid.setReadOnly(true);
														wnd.LpuBuildingOfficeScoreboardGrid.setReadOnly(true);

														wnd.tabPanel.hideTabStripItem('tab_roomlink');
														wnd.tabPanel.setActiveTab(0);

													} else {

														base_form.findField('ElectronicScoreboard_RefreshInSeconds').setValue(null);
														base_form.findField('ElectronicScoreboard_RefreshInSeconds').disable();

														wnd.tabPanel.unhideTabStripItem('tab_roomlink');

														if (wnd.action !== 'view') {
															wnd.ElectronicScoreboardQueueLinkGrid.setReadOnly(false);
															wnd.LpuBuildingOfficeScoreboardGrid.setReadOnly(false);
														}
													}
												}
											},
											xtype: 'checkbox'
										},  {
											name: 'ElectronicScoreboard_IsShownTimetable',
											xtype: 'hidden'
										},
										{
											xtype: 'fieldset',
											autoHeight: true,
											collapsible: false,
											title: 'Настройки табло электронной очереди',
											style: 'margin-top: 15px; margin-right: 10px; height: 80px;',
											labelWidth: 220,
											items: [
												{
													fieldLabel: 'Отображать текстовый статус талона электронной очереди',
													id: 'ElectronicScoreboard_IsCalledCheckbox',
													xtype: 'checkbox'
												},
												{
													fieldLabel: 'Индивидуальное табло (свой экран для каждого пункта обслуживания)',
													id: 'ElectronicScoreboard_IsShownForEachDoctor',
													xtype: 'checkbox',
													listeners: {
														'check': function(field, checked) {
															
															if (checked) {
																wnd.LpuBuildingOfficeScoreboardGrid.setReadOnly(true);
																wnd.tabPanel.hideTabStripItem('tab_roomlink');
																wnd.tabPanel.setActiveTab(0);

															} else {
																timetableCheckboxChecked = wnd.findById(wnd.id + '_IsShownTimetableCheckbox').getValue();

																log(timetableCheckboxChecked,'timetableCheckboxChecked');
																
																if (!timetableCheckboxChecked) {
																	wnd.tabPanel.unhideTabStripItem('tab_roomlink');
																	if (wnd.action !== 'view') {
																		wnd.LpuBuildingOfficeScoreboardGrid.setReadOnly(false);
																	}	
																}
															}
														}
													},
												}
											]
										},
										{
											xtype: 'fieldset',
											autoHeight: true,
											collapsible: false,
											title: 'Настройки светодиодного табло',
											style: 'margin-top: 15px; margin-right: 10px; height: 60px;',
											labelWidth: 220,
											items: [
												{
													name: 'ElectronicScoreboard_IPaddress',
													fieldLabel: 'IP-Адрес',
													xtype: 'textfield',
													plugins: [ new Ext.ux.InputTextMask('999.999.999.999', true)],
													allowBlank: false,
													width: 150
												},
												{
													name: 'ElectronicScoreboard_Port',
													fieldLabel: 'Порт',
													xtype: 'textfield',
													allowBlank: true,
													width: 150,
													maskRe: /[0-9]/,
													autoCreate: {tag: "input", type: "text", size: "5", maxLength: "5", autocomplete: "off"},
												}
											]
										},
										{
											xtype: 'fieldset',
											autoHeight: true,
											collapsible: false,
											title: 'Настройки табло расписания',
											style: 'margin-top: 15px; margin-right: 10px; height: 60px;',
											labelWidth: 220,
											items: [
												{
													allowDecimals: false,
													allowNegative: false,
													fieldLabel: 'Интервал смены информации на экране (сек.)',
													maxValue: 300,
													name: 'ElectronicScoreboard_RefreshInSeconds',
													width: 150,
													xtype: 'numberfield'
												}
											]
										},
									]
								}]
						}
					]
				}],
                reader: new Ext.data.JsonReader({}, [
                    { name: 'ElectronicScoreboard_id' },
                    { name: 'Lpu_id' },
                    { name: 'LpuBuilding_id' },
                    { name: 'LpuSection_id' },
                    { name: 'ElectronicScoreboard_Code' },
                    { name: 'ElectronicScoreboard_Name' },
                    { name: 'ElectronicScoreboard_begDate' },
                    { name: 'ElectronicScoreboard_endDate' },
                    { name: 'ElectronicScoreboard_IsLED' },
                    { name: 'ElectronicScoreboard_IsShownTimetable' },
					{ name: 'ElectronicScoreboard_IsCalledCheckbox' },
					{ name: 'ElectronicScoreboard_IsShownForEachDoctor' },
                    { name: 'ElectronicScoreboard_IPaddress' },
                    { name: 'ElectronicScoreboard_Port' },
                    { name: 'ElectronicScoreboard_RefreshInSeconds' }
                ]),
                url: '/?c=ElectronicScoreboard&m=save'
            });

            this.ElectronicScoreboardQueueLinkGrid = new sw.Promed.ViewFrame({
                id: 'ElectronicScoreboardQueueLinkGrid',
                object: 'ElectronicScoreboardQueueLink',
                dataUrl: '/?c=ElectronicScoreboard&m=loadElectronicScoreboardQueues',
                autoLoadData: false,
                paging: true,
                totalProperty: 'totalCount',
                region: 'center',
                toolbar: true,
                useEmptyRecord: false,
                stringfields: [
                    {name: 'ElectronicScoreboardQueueLink_id', type: 'int', header: 'ID', key: true, hidden: true},
                    {name: 'ElectronicQueueInfo_Code', header: 'Код ЭО', width: 100},
                    {name: 'ElectronicService_Name', header: 'Пункт обслуживания', width: 300},
                    {name: 'ElectronicQueueInfo_Name', header: 'Электронная очередь', width: 150, id: 'autoexpand'},
                    {name: 'ElectronicQueueInfo_id', header: 'Идентификатор очереди', hidden: true},
                    {name: 'ElectronicService_id', header: 'Идентификатор ПО', hidden: true},
                    {name: 'LpuBuilding_Name', header: 'Подразделение', width: 200, },
                    {name: 'LpuSection_Name', header: 'Отделение', width: 200,},
                    {name: 'MedService_Name', header: 'Служба', width: 200}
                ],
                actions: [
                    {name:'action_add', handler: function() { wnd.openElectronicScoreboardQueueLinkEditWindow('add'); }},
                    {name:'action_edit',  disabled: true, hidden: true, handler: function() { wnd.openElectronicScoreboardQueueLinkEditWindow('edit'); }},
                    {name:'action_view',  disabled: true, hidden: true, handler: function() { wnd.openElectronicScoreboardQueueLinkEditWindow('view'); }},
                    {name:'action_delete', handler: function() { wnd.deleteGridRecord() }},
                    {name:'action_print', disabled: true, hidden: true},
                    {name:'action_refresh', hidden: true}
                ],
                onRowSelect: function(sm, rowIdx, record) {
                    wnd.onGridRowSelect(this);
                },
                getChangedData: function(){ //возвращает новые и измненные показатели
                    var data = new Array();
                    wnd.clearGridFilter(this);
                    this.getGrid().getStore().each(function(record) {
                        if (record.data.state == 'add' || record.data.state == 'edit' ||  record.data.state == 'delete') {
                            data.push(record.data);
                        }
                    });
                    wnd.setGridFilter(this);
                    return data;
                },
                getJSONChangedData: function(){ //возвращает новые и измненные записи в виде закодированной JSON строки
                    var dataObj = this.getChangedData();
                    return dataObj.length > 0 ? Ext.util.JSON.encode(dataObj) : "";
                }
            });

			this.LpuBuildingOfficeScoreboardGrid = new sw.Promed.ViewFrame({
				id: 'LpuBuildingOfficeScoreboardGrid',
				object: 'LpuBuildingOfficeScoreboardGrid',
				dataUrl: '/?c=LpuBuildingOffice&m=loadLpuBuildingOfficeScoreboard',
				autoLoadData: false,
				paging: true,
				totalProperty: 'totalCount',
				region: 'center',
				toolbar: true,
				useEmptyRecord: false,
				stringfields: [
					{name: 'LpuBuildingOfficeScoreboard_id', type: 'int', header: 'ID', key: true, hidden: true},
					{name: 'LpuBuildingOffice_id', type: 'int', hidden: true},
					{name: 'ElectronicScoreboard_id', type: 'int', hidden: true},
					{name: 'LpuBuildingOffice_Number', header: 'Номер кабинета', width: 100},
					{name: 'LpuBuildingOffice_Name', header: 'Наименование кабинета', width: 300},
					{name: 'LpuBuildingOffice_Comment', header: 'Комментарий', width: 150, id: 'autoexpand'},
					{name: 'LpuBuildingOfficeScoreboard_begDT', header: 'Дата начала', width: 100},
					{name: 'LpuBuildingOfficeScoreboard_endDT', header: 'Дата окончания', width: 100}
				],
				actions: [
					{name:'action_add', handler: function() { wnd.openLpuBuildingOfficeAssignWindow('add'); }},
					{name:'action_edit',  handler: function() { wnd.openLpuBuildingOfficeAssignWindow('edit'); }},
					{name:'action_view',  disabled: true, hidden: true, handler: function() { wnd.openLpuBuildingOfficeAssignWindow('view'); }},
					{name:'action_delete', handler: function() { wnd.deleteLpuOfficeAssign() }},
					{name:'action_print', disabled: true, hidden: true},
					{name:'action_refresh'}
				],
				onRowSelect: function(sm, rowIdx, record) {
					wnd.onGridRowSelect(this);
				},
				getChangedData: function(){ //возвращает новые и измненные показатели
					var data = new Array();
					wnd.clearGridFilter(this);
					this.getGrid().getStore().each(function(record) {
						if (record.data.state == 'add' || record.data.state == 'edit' ||  record.data.state == 'delete') {
							data.push(record.data);
						}
					});
					wnd.setGridFilter(this);
					return data;
				},
				getJSONChangedData: function(){ //возвращает новые и измненные записи в виде закодированной JSON строки
					var dataObj = this.getChangedData();
					return dataObj.length > 0 ? Ext.util.JSON.encode(dataObj) : "";
				}
			});

			this.tabPanel = new Ext.TabPanel({
				activeTab: 0,
				id: wnd.id + 'TabPanel',
				layoutOnTabChange: true,
				region: 'center',
				height: 530,
				border: false,
				items:
					[{
						title: "Список ЭО",
						layout: 'border',
						id: "tab_eqlink",
						items: [
							this.ElectronicScoreboardQueueLinkGrid
						]
					},
						{
							title: "Cписок кабинетов",
							layout: 'border',
							id: 'tab_roomlink',
							items: [
								this.LpuBuildingOfficeScoreboardGrid
							]
						}],
				listeners:
					{
						tabchange: function(panel, tab)
						{
							if (tab.id === 'tab_roomlink' && wnd.ElectronicScoreboard_id) {

								var grid_rooms = wnd.LpuBuildingOfficeScoreboardGrid;

								grid_rooms.getGrid().getStore().baseParams = {
									ElectronicScoreboard_id: wnd.ElectronicScoreboard_id,
									start: 0,
									limit: 100
								};
								grid_rooms.loadData();
							}
						}
					}
			});

			this.formPanel = new Ext.Panel({
				region: 'center',
				labelAlign: 'right',
				layout: 'border',
				labelWidth: 50,
				border: false,
				items: [
					this.formEditPanel,
					this.tabPanel
				]
			});

            Ext.apply(this, {
                items: [
                    wnd.formPanel
                ],
                buttons: [{
                    text: BTN_FRMSAVE,
                    iconCls: 'save16',
                    handler: function() {
                        wnd.doSave({
							callback: function() {
								wnd.hide();
							}
						});

                    }
                }, {
                    text: '-'
                },
                    HelpButton(this, TABINDEX_RRLW + 13),
                    {
                        iconCls: 'close16',
                        tabIndex: TABINDEX_RRLW + 14,
                        handler: function() {
                            wnd.hide();
                        },
                        text: BTN_FRMCLOSE
                    }]
            });

            sw.Promed.swElectronicScoreboardEditWindow.superclass.initComponent.apply(this, arguments);
        },
        toggleFormLedFields: function(value) {

            var wnd = this,
                form = this.formEditPanel.getForm(),
                ipAddrField = form.findField('ElectronicScoreboard_IPaddress'),
                portField = form.findField('ElectronicScoreboard_Port'),
				timetableCheckbox = wnd.findById(this.id + '_IsShownTimetableCheckbox');

            wnd.isLED = (value == 2) ? 1 : 0;

            switch (value) {

                case 1: // телевизор

                    ipAddrField.setAllowBlank(true);
                    portField.setAllowBlank(true);
                    timetableCheckbox.enable();
                    break;

                case 2:  // табло электронное
                    ipAddrField.setAllowBlank(false);
                    portField.setAllowBlank(false);
                    timetableCheckbox.disable();
                    timetableCheckbox.setValue(false);
                    break;
            }

			timetableCheckbox.fireEvent('check', timetableCheckbox, timetableCheckbox.getValue());
        },
        setFieldsDisabled: function(d) {
            var form = this.formEditPanel.getForm();
            form.items.each(function(f)
            {
                if (f && (f.xtype!='hidden') && (f.xtype!='fieldset')  && (f.changeDisabled!==false))
                {
                    f.setDisabled(d);
                }
            });
            this.buttons[0].show();
            this.buttons[0].setDisabled(d);
            this.ElectronicScoreboardQueueLinkGrid.setReadOnly(d);
        },
        show: function() {

            sw.Promed.swElectronicScoreboardEditWindow.superclass.show.apply(this, arguments);

            var wnd = this,
                form = this.formEditPanel.getForm(),
                grid_eq = this.ElectronicScoreboardQueueLinkGrid,
				grid_rooms = this.LpuBuildingOfficeScoreboardGrid;

            wnd.formAction = null;

            form.reset();
			grid_eq.getGrid().getStore().baseParams = {};
			grid_eq.getGrid().getStore().removeAll();

			grid_rooms.getGrid().getStore().baseParams = {};
			grid_rooms.getGrid().getStore().removeAll();

            if (arguments[0]['action']) {
                this.action = arguments[0]['action'];
                wnd.formAction = this.action;
            }

            if (arguments[0]['callback']) {
                this.returnFunc = arguments[0]['callback'];
            }

            if (arguments[0]['ElectronicScoreboard_id']) {
                this.ElectronicScoreboard_id = arguments[0]['ElectronicScoreboard_id'];
            } else {
                this.ElectronicScoreboard_id = null;
            }

            this.setFieldsDisabled(this.action == 'view');

            if (isLpuAdmin() && !isSuperAdmin()) {
                form.findField('Lpu_id').disable();
            }

			form.findField('LpuBuilding_id').getStore().removeAll();
			form.findField('LpuSection_id').getStore().removeAll();

            switch (this.action){

                case 'add':
                    this.setTitle('Электронное табло: Добавление');

                    if (isLpuAdmin() && !isSuperAdmin()) {
                        form.findField('Lpu_id').setValue(getGlobalOptions().lpu_id);
                        form.findField('Lpu_id').fireEvent('change', form.findField('Lpu_id'), form.findField('Lpu_id').getValue());
                        form.findField('ElectronicScoreboard_Code').focus(true, 100);
                    } else {
                        form.findField('Lpu_id').focus(true, 100);
                    }

					wnd.toggleFormLedFields();

                    break;

                case 'edit':
                    this.setTitle('Электронное табло: Редактирование');
                    //form.findField('Lpu_id').setDisabled(true);
                    break;

                case 'view':
                    this.setTitle('Электронное табло: Просмотр');
                    break;
            }

            if (this.action != 'add') {

                var loadMask = new Ext.LoadMask(this.getEl(), {
                        msg: "Подождите, идет загрузка..."
                    });

                loadMask.show();

                form.load({
                    url: '/?c=ElectronicScoreboard&m=load',
                    params: {
                        ElectronicScoreboard_id: wnd.ElectronicScoreboard_id
                    },
                    success: function (elem, resp) {

                        loadMask.hide();

                        var scoreboardType = form.findField('ElectronicScoreboard_IsLED').getValue();
                        var radioGroup = form.findField('ElectronicScoreboard_IsLED_rg').items;

						wnd.findById(wnd.id + '_IsShownTimetableCheckbox').setValue(form.findField('ElectronicScoreboard_IsShownTimetable').getValue() == 2);

                        radioGroup.each(function(radioBtn){
                            if (radioBtn.inputValue == scoreboardType) {
								radioBtn.setValue(true);
								form.findField('ElectronicScoreboard_IsLED_rg').fireEvent('change', form.findField('ElectronicScoreboard_IsLED_rg'), radioBtn);
								wnd.toggleFormLedFields(scoreboardType);
							}
                        });

                        form.findField('Lpu_id').fireEvent('change', form.findField('Lpu_id'), form.findField('Lpu_id').getValue());

						if ( form.findField('ElectronicScoreboard_IsShownTimetable').getValue() == 1 ) {
							grid_eq.getGrid().getStore().baseParams = {
								ElectronicScoreboard_id: wnd.ElectronicScoreboard_id,
								start: 0,
								limit: 100
							};
							grid_eq.loadData();

							grid_rooms.getGrid().getStore().baseParams = {
								ElectronicScoreboard_id: wnd.ElectronicScoreboard_id,
								start: 0,
								limit: 100
							};
							grid_rooms.loadData();
						}
                    },
                    failure: function (elem, resp) {

                        loadMask.hide();

                        if (!resp.result.success) {
                            Ext.Msg.alert(langs('Ошибка'), langs('Ошибка запроса к серверу. Попробуйте повторить операцию.'));
                            this.hide();
                        }
                    },
                    scope: this
                });
            }
        }
    });