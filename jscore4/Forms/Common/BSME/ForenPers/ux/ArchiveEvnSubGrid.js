Ext.define('common.BSME.ForenPers.ux.ArchiveEvnSubGrid',{
	extend: 'common.BSME.DefaultWP.ux.ArchiveGrid',
	
	
	columns: [
		{ text: '№ п/п',  dataIndex: 'EvnForensic_Num', width: 50},
		{ text: 'ФИО свидетельствуемого', dataIndex: 'Person_Fio', flex: 1 },
		{ text: 'Пол', dataIndex: 'Sex_Name', width: 35},
		{ text: 'Дата  рождения', dataIndex: 'Person_BirthDay', width: 85 },
		{ text: 'Инициатор экспертизы', dataIndex: 'Iniciator', flex: 1},
		{ text: 'Дата проведения экспертизы', dataIndex: 'EvnForensicSub_ExpertiseDT', width: 120,  type: 'date', editor: {xtype: 'swdatefield'}, renderer: Ext.util.Format.dateRenderer('d.m.Y'),tdCls: 'Editable'},
		{ text: 'Время происшествия', dataIndex: 'EvnForensicSub_AccidentDT', width: 120 },
		{ text: 'Оценка вреда здоровью/ определение половых состояний/ определение возраста, рубцов', dataIndex: 'ForensicSubReportWorking_Text' },
		{ text: 'Результаты экспертизы', dataIndex: 'EvnForensicSub_Result', editor: { xtype: 'textfield'}, tdCls: 'Editable' },
		{ text: 'Фамилия судебно-медицинского эксперта', dataIndex: 'Expert_Fin', width: 150 },
		{ text: 'Фамилия, инициалы, должность, № документа удостоверяющего личность лица, получившего «Заключение эксперта» («Акт»), его подпись, дата (или номер и дата почтовой квитанции)', dataIndex: 'EvnForensicSub_Receiver', flex: 1 ,editor: { xtype: 'textfield'},tdCls: 'Editable' },
	],
	
	
	extraParams: {
		
	},
	initComponent: function(){
		var me = this;
		
		me.printButton = {
			xtype: 'splitbutton',
			text: 'Печать',
			iconCls: 'print16',
			menu: {
				xtype: 'menu',
				items: [
					{
						xtype: 'menuitem',
						itemId: 'print_request',
						text: 'Печать журнала',
						handler: function () {							
							var pattern = '',
								dateFrom = me.datePickerRange.isVisible()?(Ext.Date.format(me.datePickerRange.dateFrom, 'd.m.Y')+' 00:00:00'):'01.01.1900 00:00:00',
								dateTo = me.datePickerRange.isVisible()?(Ext.Date.format(me.datePickerRange.dateTo, 'd.m.Y')+' 23:59:59'):(Ext.Date.format(new Date, 'd.m.Y')+' 23:59:59'),
								wnd = this.up('window'),
								params,
								ForensicSubType_id,
								selection;
		
								if (!wnd) {
									return false;
								}
								
								selection = wnd.JournalTreePanel.getSelectionModel().getSelection()[0];
								
								if (!selection || !selection.data || !selection.data.loadStoreParams || !selection.data.loadStoreParams.params || !selection.data.loadStoreParams.params.ForensicSubType_id) {
									return false;
								}
								
								ForensicSubType_id = selection.data.loadStoreParams.params.ForensicSubType_id;
								
								
								params = '&paramMedService='+(getGlobalOptions().CurMedService_id || '')+'&paramBegDate='+dateFrom+'&paramEndDate='+dateTo;
							
							switch (parseInt(ForensicSubType_id)){
								case 1:{pattern = 'EvnForensicSub1.rptdesign'; break;}
								case 2:{pattern = 'EvnForensicSub2.rptdesign'; break;}
								case 3:{pattern = 'EvnForensicSub3.rptdesign'; break;}
								case 4:{pattern = 'EvnForensicSub4.rptdesign'; break;}
								default: 
									Ext.Msg.alert('Ошибка','Получен неверный тип журнала.');
									break;
							}
							printBirt({
								'Report_FileName': pattern,
								'Report_Params': params,
								'Report_Format': 'xls'
							});
						}
					},
					{
						xtype: 'menuitem',
						itemId: 'print_act',
						text: 'Печать заключения',						
						handler: function () {
							if (!me.archiveGrid.getSelectionModel().hasSelection()) {
								return false;
							}
							
							Ext.Ajax.request({
								url: '/?c=EvnXml&m=doPrint',
								params: {
									EvnXml_id: me.archiveGrid.getSelectionModel().getSelection()[0].data.EvnXml_id
								},
								callback: function(opt, success, response){
									if ( !success ) {
										Ext.Msg.alert('Ошибка','Во время загрузки печатной формы произошла непредвиденная ошибка.');
										return;
									}
									var win = window.open();
									win.document.write(response.responseText);
								}
							});
						}
					}
				],
				listeners: {
					show: function(с){
						
						с.down('menuitem[itemId=print_act]').setDisabled( !me.archiveGrid.getSelectionModel().hasSelection() )
						
					}
				}
			},
			listeners: {
				click: function(){
					this.showMenu();
				}				
			}
		};

		me.exportButton = {
//			xtype: 'button',
//			text: 'Экспорт в DBF',
//			handler: function() {
//				Ext.create('common.BSME.ForenPers.SecretaryWP.tools.swExportJournalWindow',{
//					MedService_id: getGlobalOptions().CurMedService_id || null,
//					JournalType: me.extraParams.JournalType,
//					dateFrom: me.datePickerRange.isVisible()?Ext.Date.format(me.datePickerRange.dateFrom, 'd.m.Y'):null,
//					dateTo: me.datePickerRange.isVisible()?Ext.Date.format(me.datePickerRange.dateTo, 'd.m.Y'):null
//				});
//			}
		};
		
		me.store = Ext.create('sw.ExtendedStore',{
			autoLoad: false,
			pageSize: 20,
			storeId: this.id+'RequestListDataviewStore',
			idProperty: 'EvnForensicSub_id',
			fields: [
				{name: 'EvnForensicSub_id', type: 'int'},
				{name: 'EvnForensic_Num', type: 'string'},
				{name: 'Person_Fio', type: 'string'},
				{name: 'Sex_Name', type: 'string'},
				{name: 'Person_BirthDay', type: 'string'},
				{name: 'Iniciator', type: 'string'},
				{name: 'EvnForensicSub_ExpertiseDT', type: 'string'},
				{name: 'EvnForensicSub_AccidentDT', type: 'string'},
				{name: 'ForensicSubReportWorking_Text', type: 'string'},
				{name: 'EvnForensicSub_Result', type: 'string'},
				{name: 'Expert_Fin', type: 'string'},
				{name: 'EvnForensicSub_Receiver', type: 'string'},
				{name: 'EvnXml_id', type: 'int'},
			],
			proxy: {
				type: 'ajax',
				url: '/?c=BSME&m=getEvnForensicSubArchive',
				extraParams: me.extraParams,
				reader: {
					type: 'json',
					successProperty: 'success',
					totalProperty: 'totalCount',
					root: 'data'
				},
				actionMethods: {
					create : 'POST',
					read   : 'POST',
					update : 'POST',
					destroy: 'POST'
				}
			},
			listeners: {
				update: function(store, record, operation, modifiedFieldNames, eOpts) {
					
					if (operation === 'edit') {
						
						var params = {
							EvnForensicSub_id: record.get('EvnForensicSub_id')
						};
						
						Ext.Object.each(modifiedFieldNames, function(key, value, obj) {
							params[value] = record.get(value);
						});
						
						var url;
						switch (modifiedFieldNames[0]) {
							case 'EvnForensicSub_Result':
								url = '/?c=BSME&m=setEvnForensicSubResult';
								break;
							case 'EvnForensicSub_Receiver':
								url = '/?c=BSME&m=setEvnForensicSubReceiver';
								break;
							case 'EvnForensicSub_ExpertiseDT':
								url = '/?c=BSME&m=setEvnForensicSubExpertiseDT';
								params['EvnForensicSub_ExpertiseDT'] = Ext.util.Format.date(params['EvnForensicSub_ExpertiseDT'],'d.m.Y');
								break;
							default:
								return false;
								break;
						}
						
						
						Ext.Ajax.request({
							params: params,
							url: url,
							callback: function(params,success,result) {
								
								var resp = Ext.JSON.decode(result.responseText, true);

								if ((result.status == 200) && resp && resp.success) {
									record.commit();
								} else {
									record.reject();
									Ext.Msg.alert('Ошибка', 'При сохранении возникла ошибка. Попробуйте перезагрузить страницу или обратитесь к администратору');
								}
							}
						})
					}
				}
			}
		});
		
		me.callParent(arguments);
	}
})

