/**
* swWorkListWindow - Окно Рабочие списки
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan-it.ru/elektronnoe_zdravoohranenie/riams_promed
*
*
* @access       public
* @author       yan yudin (yudin.yan@gmail.com)
* @version      01.2020
*
*/

sw.Promed.swWorkListWindow = Ext.extend(sw.Promed.BaseForm, {
    id: 'swWorkListWindow',
    title: langs('Рабочие списки'),
    border: true,
    graggable: true,
    modal: true,
    width: 1000,
	height: 800,
	doSearch: function(baseParams) {
		var directionsPanel = this.TabPanel.findById('directions').getStore();
		var workListPanel = this.TabPanel.findById('workList_tab').getStore();

		directionsPanel.baseParams.Data = Ext.util.JSON.encode(baseParams);
		workListPanel.baseParams.Data = Ext.util.JSON.encode(baseParams);
		directionsPanel.load();
		workListPanel.load();
    },
    show: function() {
        this.TabPanel.setActiveTab(1);
		this.TabPanel.setActiveTab(0);
        sw.Promed.swWorkListWindow.superclass.show.apply(this, arguments);
    },
    saveDefaultSettings: function() {
        var wnd = this;
        var paramsSetting = new Object();
		paramsSetting.Data = [];
		wnd.getLoadMask('Сохранение').show();

        wnd.GridSettings.getStore().each(function(rec){
            var data = new Object();
            data.UslugaComplexMedService_id = rec.get('UslugaComplexMedService_id');
            data.MedProductCard_id = rec.get('MedProductCard_id');
			paramsSetting.Data.push(data);
		});
		
        Ext.Ajax.request({
            url: '/?c=WorkList&m=addMedProductUslugaComplex',
            params: {Data: Ext.util.JSON.encode(paramsSetting)},
            callback: function(options, success, response) {
                wnd.getLoadMask().hide();
                var result = Ext.util.JSON.decode(response.responseText)[0];
                if(success) {
                    if(result) {
                        if(result.Error_Message) sw.swMsg.alert(langs('Ошибка'), result.Error_Message);
                        else showSysMsg('Настройки по умолчанию сохранены успешно', 'Настройки сохранены');
                    }
                } else {
                    sw.swMsg.alert(langs('Ошибка'), langs('Ошибка при сохранении'));
                }
            }
        });
	},
	useOperationToRequestWL: function(operationData){
		var wnd = this;
		var paramsQueue = new Object();
		paramsQueue.Data = [];
		paramsQueue.Data.push(operationData.requestData.json);
		
		wnd.getLoadMask(operationData.textLoadMask).show();
		
		Ext.Ajax.request({
			url: operationData.procedure,
			params: {Data: Ext.util.JSON.encode(paramsQueue)},
			callback: function(options, success, response) {
				var result = Ext.util.JSON.decode(response.responseText)[0];
				wnd.getLoadMask().hide();
                if(success) {
                    if(result) {
                        if(result.Error_Message) sw.swMsg.alert(langs('Ошибка'), result.Error_Message);
						else showSysMsg(result.message, 'Рабочие списки');
                    }
                } else {
                    sw.swMsg.alert(langs('Ошибка'), langs('Ошибка при добавлении'));
                }
			}
		});
		setTimeout(() => wnd.TabPanel.findById('workList_tab').getStore().reload(), 1000);
	},
    initComponent: function() {
        var wnd = this;
		var dataFuncWindow = Ext.getCmp('swWorkPlaceFuncDiagWindow');
		this.MedService_id = dataFuncWindow.MedService_id;
		var params = {
			MedService_id: this.MedService_id,
			MedProductCard_IsWorkList: "2",
			Lpu_id: dataFuncWindow.Lpu_id
		};
		var selectRowDir = '';
		var selectRowWL = '';
		var rowDataSetting = '';

        wnd.loadMedProductCard = new Ext.data.JsonStore({
            autoLoad: true,
			url: '/?c=WorkList&m=getMedProductCardIsWL',
			baseParams: {
				MedService_id: this.MedService_id,
				MedProductCard_IsWorkList: 2,
				MedProductCard_isCombo: true
			},
            fields: [
                { name: 'MedProductCard_id', type: 'int' },
                { name: 'MedProductClass_Name', type: 'string' }
			],
			listeners: {
				load: function(store, record, options) {
					var value = {
						MedProductCard_id: record[0].data.MedProductCard_id,
						MedService_id: Number(params.MedService_id),
						startDate: Ext.util.Format.date(new Date(), 'Y-m-d'),
						endDate: Ext.util.Format.date(new Date(), 'Y-m-d')
					};
					var comboFilter = wnd.findById('filterPanel');
					comboFilter.setValue(value.MedProductCard_id);
					wnd.doSearch(value);
				}
			}
		});
		
        wnd.GridSettings = new Ext.grid.EditorGridPanel({
            id: 'gridPanelSettings',
            loadMask: true,
			border: true,
			clicksToEdit: 1,
            store: new Ext.data.JsonStore({
                autoLoad: true,
                url: '/?c=WorkList&m=getUsluaList',
                baseParams: {MedService_id: params.MedService_id},
                fields: [
                    { name: 'UslugaComplexMedService_id', type: 'int' },
                    { name: 'UslugaCategory_Name', type: 'string' },
                    { name: 'UslugaComplex_Code', type: 'string' },
                    { name: 'UslugaComplex_Name', type: 'string' },
					{ name: 'MedProductCard_id', type: 'int' },
					{ name: 'MedProductClass_Name', tupe: 'string' }
                ]
            }),
            cm: new Ext.grid.ColumnModel([
                {
                    dataIndex: 'UslugaComplexMedService_id',  
                    hidden: true
                },
                {
                    dataIndex: 'UslugaCategory_Name',  
                    header: 'Категория',
                    width: 100
                }, 
                {
                    dataIndex: 'UslugaComplex_Code', 
                    header: 'Код',
                    width: 150
                }, 
                {
                    dataIndex: 'UslugaComplex_Name', 
                    header: 'Наименование',
                    width: 400
                },
                {
                    dataIndex: 'MedProductClass_Name',  
                    header: 'Медицинское изделие',
                    width: 300,
                    editor: new sw.Promed.SwCommonSprCombo({
                        width: 300,
						displayField: 'MedProductClass_Name',
						valueField: 'MedProductCard_id',
						store: wnd.loadMedProductCard,
						listeners: {
							select: function( combo, record, index) {
								this.setValue(record.data.MedProductClass_Name);
                            	rowDataSetting.MedProductCard_id = record.data.MedProductCard_id;
							}
						}
                    })
				},
				{
					dataIndex: 'MedProductCard_id',
					hidden: true
				}
			]),
			sm: new Ext.grid.RowSelectionModel({
				singleSelect: true,
				listeners: {
					rowselect: function(row, index, r) {
						rowDataSetting = r.data;
					}
				}
			}),
            stripeRows: true
		});

		wnd.MedProductCombo = {
			xtype: 'swbaselocalcombo',
			fieldLabel: 'Медицинское изделие',
			valueField: 'MedProductCard_id',
			displayField: 'MedProductClass_Name',
			id: 'filterPanel',
			width: 250,
			store: wnd.loadMedProductCard,
			listeners: {
				select: function(combo, record, index) {
					var date = wnd.dateMenu.value? wnd.dateMenu.value.split(' - ') : [Ext.util.Format.date(new Date(), 'd.m.Y'), Ext.util.Format.date(new Date(), 'd.m.Y')];
					var baseParams = {
						MedService_id: params.MedService_id,
						MedProductCard_id: record.data.MedProductCard_id,
						startDate: date[0],
						endDate: date[1]
					};
					wnd.doSearch(baseParams);
				}
			}
		};

		wnd.dateMenu = new Ext.form.DateRangeField({
			width: 150,
			value: Ext.util.Format.date(new Date(), 'd.m.Y') + ' - ' + Ext.util.Format.date(new Date(), 'd.m.Y'),
			id: this.id + '_periodField',
			plugins: 
			[
				new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)
			],
			listeners: {
				select: function(this_dateField, date){
					var date = date.split(' - ');
					var value = {
						startDate: date[0],
						endDate: date[1],
						MedService_id: Number(params.MedService_id),
						MedProductCard_id: wnd.findById('filterPanel').getValue()
					};
					wnd.doSearch(value);
				}
			}
		});

        wnd.TabPanel = new Ext.TabPanel({
			title: 'Рабочие списки',
			border: false,
            region: 'center',
            items: [
                {
                    title: 'Рабочие списки',
                    id: 'workList',
                    items: [
                        new sw.Promed.Panel ({
							autoWidth: true,
							border: false,
							region: 'north',
							items: [{
								layout: 'form',
								labelAlign: 'right',
								labelWidth: 145,
								items: [
									wnd.MedProductCombo
								]
							}]
						}),
						new sw.Promed.Panel ({
							title: 'Направления',
							border: false,
							region: 'center',
							items: [
								new Ext.grid.EditorGridPanel({
									loadMask: true,
									height: 300,
									style: 'padding-bottom: 10px;',
									border: false,
									stripeRows: true,
									id: 'directions',
									store: new Ext.data.JsonStore({
										autoLoad: false,
										url: '/?c=WorkList&m=getDirections',
										fields: [
											{ name: 'Person_FIO', type: 'string'},
											{ name: 'Person_BirthDay', type: 'string'},
											{ name: 'EvnDirection_Num', type: 'string'},
											{ name: 'UslugaComplex_Name', type: 'string'},
											{ name: 'EvnDirection_setDT', type: 'string'},
											{ name: 'Time_begTime', type: 'string'}
										],
									}),
									cm: new Ext.grid.ColumnModel([
										{ dataIndex: 'Person_FIO', type: 'string', header: 'ФИО пациента', sortable: true, width: 250},
										{ dataIndex: 'Person_BirthDay', type: 'string', header: 'Дата рождения', sortable: true, width: 130},
										{ dataIndex: 'EvnDirection_Num', type: 'string', header: 'Номер напрваления', sortable: true, width: 120},
										{ dataIndex: 'UslugaComplex_Name', type: 'string', header: 'Услуга', sortable: true, width: 250},
										{ dataIndex: 'EvnDirection_setDT', type: 'string', header: 'Дата направления', sortable: true, width: 150},
										{ dataIndex: 'Time_begTime', type: 'string', header: 'Запись', sortable: true, width: 50}
									]),
									sm: new Ext.grid.RowSelectionModel({
										singleSelect: true,
										listeners: {
											rowselect: function(sm, row, rec) {
												selectRowDir = rec;
											}
										  }
									})
								})
							],
							tbar: new Ext.Toolbar({
								items: [
									{ 
										text: 'Обновить',
										handler: function() {
											wnd.TabPanel.findById('directions').getStore().reload();
											selectRowDir = '';
										}
									},
									{ 
										text: 'Добавить в РС',
										handler: function() {
											if(!selectRowDir) {
												return Ext.Msg.alert('Ошибка', 'Не выбрана ни одна запись из списка напаравлений');
											}
											var operationData = {
												textLoadMask: 'Добавление в очередь РС',
												procedure: '/?c=WorkList&m=addRecordToDB',
												requestData: selectRowDir
											};
											selectRowDir = '';
											wnd.useOperationToRequestWL(operationData);
										}
									}
								]
							})
						}),
                        new sw.Promed.Panel ({
							autoWidth: true,
							border: false,
							title: 'Рабочий список',
                            region: 'south',
                            items: [
                                new Ext.grid.EditorGridPanel({
                                    loadMask: true,
									height: 300,
									style: 'padding-bottom: 20px;',
                                    border: false,
									id: 'workList_tab',
                                    store: new Ext.data.JsonStore({
                                        autoLoad: false,
                                        url: '/?c=WorkList&m=getWorkList',
                                        fields: [
											{ name: 'Person_FIO', type: 'string'},
											{ name: 'Person_id', type: 'string'},
                                            { name: 'Person_BirthDay', type: 'string'},
                                            { name: 'EvnDirection_Num', type: 'string'},
                                            { name: 'UslugaComplex_Name', type: 'string'},
                                            { name: 'Usluga_setDate', type: 'string'},
											{ name: 'Time_begTime', type: 'string'},
											{ name: 'WorkListStatus_Name', type: 'string'},
											{ name: 'WorkListStatus_Code', type: 'string'},
											{ name: 'UslugaComplex_Code', type: 'string'},
											{ name: 'MedProductCard_id', type: 'string'},
											{ name: 'LpuSection_Name', type: 'string'},
											{ name: 'EvnDirection_id', type: 'string'},
											{ name: 'WorkListQueue_id', type: 'string'},
											{ name: 'EvnUslugaPar_id', type: 'string'},
											{ name: 'LpuEquipmentPacs_id', type: 'string'}
										],
									}),
									cm: new Ext.grid.ColumnModel([
										{ dataIndex: 'Person_FIO', type: 'string', header: 'ФИО пациента', sortable: true, width: 200},
										{ dataIndex: 'Person_BirthDay', type: 'date', header: 'Дата рождения', sortable: true, width: 100},
										{ dataIndex: 'EvnDirection_Num', type: 'string', header: 'Номер напрваления', sortable: true, width: 120},
										{ dataIndex: 'UslugaComplex_Name', type: 'string', header: 'Услуга', sortable: true, width: 250},
										{ dataIndex: 'Usluga_setDate', type: 'date', header: 'Дата направления', sortable: true, width: 100},
										{ dataIndex: 'Time_begTime', type: 'string', header: 'Запись', sortable: true, width: 50},
										{ dataIndex: 'WorkListStatus_Name', type: 'string', header: 'Статус', sortable: true, width: 140},
										{ dataIndex: 'WorkListStatus_Code', hidden: true},
										{ dataIndex: 'UslugaComplex_Code', hidden: true},
										{ dataIndex: 'MedProductCard_id', hidden: true},
										{ dataIndex: 'Person_id', hidden: true},
										{ dataIndex: 'LpuSection_Name', hidden: true},
										{ dataIndex: 'EvnDirection_id', hidden: true},
										{ dataIndex: 'WorkListQueue_id', hidden: true},
										{ dataIndex: 'EvnUslugaPar_id', hidden: true},
										{ dataIndex: 'LpuEquipmentPacs_id', hidden: true}
									]),
									sm: new Ext.grid.RowSelectionModel({
										singleSelect: true,
										listeners: {
											rowselect: function(sm, row, rec) {
												selectRowWL = rec;
												Ext.getCmp('WLW_medProduct').setValue(rec.data.MedProductCard_id);
											}
										  }
									})
                                })
                            ],
                            tbar: new Ext.Toolbar({
                                items: [
                                    { 
                                        text: 'Обновить',
                                        handler: function() {
											wnd.TabPanel.findById('workList_tab').getStore().reload();
											selectRowWL = '';
                                        }
                                    },
                                    { 
                                        text: 'Удалить из РС',
                                        handler: function() {
											var operationData = {
												textLoadMask: 'Удаление из очереди РС',
												procedure: '/?c=WorkList&m=cancelRecordToDB',
												requestData: selectRowWL
											};
											if(Ext.isEmpty(operationData.requestData)) {
												sw.swMsg.show({
													buttons: Ext.Msg.OK,
													icon: Ext.Msg.WARNING,
													msg: 'Не выбрана запись для удаления',
													title: langs('Удаление записи')
												});
												return;
											}
											sw.swMsg.show({
												buttons: Ext.Msg.YESNO,
												fn: function(buttonId, text, obj) {
													if ( buttonId == 'yes' ) {
														selectRowWL = '';
														wnd.useOperationToRequestWL(operationData);
													}
												},
												icon: Ext.MessageBox.QUESTION,
												msg: langs('Убрать заявку из Рабочего списка?'),
												title: langs('Вопрос')
											});
											
                                        }
                                    },
                                    { 
                                        xtype: 'swbaselocalcombo',
                                        valueField: 'MedProductCard_id',
										displayField: 'MedProductClass_Name',
										id: 'WLW_medProduct',
                                        width: 250,
										store: wnd.loadMedProductCard,
										listeners: {
											select: function(combo, record, index) {
												var valueMedProductId = this.getValue();

												if(Ext.isEmpty(valueMedProductId)) {
													sw.swMsg.show({
														buttons: Ext.Msg.OK,
														icon: Ext.Msg.WARNING,
														msg: 'Не выбрано медицинское изделие',
														title: langs('Внесение изменений')
													});
													return;
												}
												
												if(selectRowWL.data.MedProductCard_id != valueMedProductId) {
													selectRowWL.json.MedProductCard_id = valueMedProductId;
													var operationData = {
														textLoadMask: 'Внесение изменений в очереди РС',
														procedure: '/?c=WorkList&m=updRecordToDB',
														requestData: selectRowWL
													};
													selectRowWL = '';
													wnd.useOperationToRequestWL(operationData);
												}
											}
										}
                                    },
									wnd.dateMenu
                                ]
                            })
                        })
                    ]
                },
                {
                    title: 'Настройки по умолчанию',
                    id: 'defaultSettiпgs',
                    items: [
                        wnd.GridSettings
                    ]
                }
            ],
            listeners: {
				tabchange: function(tab, panel) {
                   switch(panel.title){
                       case 'Настройки по умолчанию':
                           wnd.buttons[2].setVisible(true);
                           break;
                        case 'Рабочие списки':
                           wnd.buttons[2].setVisible(false);
                           break;
                   }
				}.createDelegate(this)
			},
            activeTab: 0
        });

        Ext.apply(wnd, {
            items: [
                wnd.TabPanel
            ],
            buttons: [{
                hidden: true,
            }, '-',{
                iconCls: 'save16',
                text: 'Сохранить',
                handler: function() {
                    wnd.saveDefaultSettings();
                }
            },HelpButton(wnd), {
            iconCls: 'close16',
            onTabElement: 'rifOk',
            text: BTN_FRMCLOSE,
            handler: function() {
                wnd.hide();
            } 
            }]
		});

		// this.addListener('beforehide', function(p) {
		// 	clearInterval(servicePeriod);
		// });

        sw.Promed.swWorkListWindow.superclass.initComponent.apply(wnd, arguments);
    }
});