/* 
Редактирование справочника неформализованных адресов СМП
*/


Ext.define('sw.tools.swUnformalizedAddressDirectoryEditWindow', {
	alias: 'widget.swUnformalizedAddressDirectoryEditWindow',
	extend: 'Ext.window.Window',
	// title: 'Редактирование справочника объектов СМП',
	title: 'Объекты СМП',
	width: 1000,
	height: 800,
	maximizable: true,
	modal: true,
	layout: {
        //align: 'stretch',
        type: 'fit'
    },
	doReset: function () {
		var _form = this.down('form').getForm();
		_form.reset();
	},
	doSearch: function () {

		var unformalizedAdressGrid = Ext.ComponentQuery.query('[refId=unformalizedAdressGrid]')[0];		
		var _form = this.down('form').getForm();

		unformalizedAdressGrid.store.reload({
			params: _form.getValues() 						
		});
	},	
	initComponent: function() {
		var win = this;

		var smpUnits = Ext.create('sw.SmpUnitsSelectedUser',{
			hideLabel: true,
			displayTpl: '<tpl for=".">{LpuBuilding_fullName}</tpl>'
		});

		
		smpUnits.getStore().proxy.extraParams.loadSelectSmp = 0;
		smpUnits.getStore().reload();
		
		var lpuAllLocalCombo = Ext.create('sw.lpuAllLocalCombo', {
			bigFont: false,
			autoFilter: false,
			hideLabel: true,
			refId: 'lpuAllLocalCombo'
		});

		lpuAllLocalCombo.getStore().load();

		var storeUN = new Ext.data.JsonStore({
				autoLoad: true,
				autoSync: false,
				numLoad: 0,
				pageSize:100,
				storeId: 'unformalizedAdressStore',
				fields: [
					{name: 'UnformalizedAddressDirectory_id', type: 'int'},
					{name: 'KLRgn_id', type: 'int'},
					{name: 'KLSubRgn_id', type: 'int'},
					{name: 'KLCity_id', type: 'int'},
					{name: 'KLTown_id', type: 'int'},
					{name: 'KLStreet_id', type: 'int'},
					{name: 'KLAreaStat_id', type: 'int'},
					{name: 'UnformalizedAddressDirectory_Name', type: 'string'},					
					{name: 'UnformalizedAddressDirectory_lat', type: 'string'},
					{name: 'UnformalizedAddressDirectory_lng', type: 'string'},
					{name: 'UnformalizedAddressDirectory_Dom', type: 'string'},
					{name: 'UnformalizedAddressDirectory_Corpus', type: 'string'},
					{name: 'UnformalizedAddressDirectory_Address', type: 'string'},
					{name: 'UnformalizedAddressDirectoryType_Name', type: 'int'},
					{name: 'UnformalizedAddressType_id', type: 'int'},
					{name: 'UnformalizedAddressType_Name', type: 'string'},
					{name: 'LpuBuilding_id', type: 'int'},
					{name: 'LpuBuilding_Name', type: 'string'},
					{name: 'Lpu_id', type: 'int'},
					{name: 'Lpu_aid', type: 'int', useNull: true},
					{name: 'Org_Nick', type: 'string'}
				],
				sorters: [
					{
						property : 'UnformalizedAddressDirectory_Name',
						direction: 'ASC'
					}
				],
				proxy: {
					// limitParam: undefined,
					// startParam: undefined,
					// paramName: undefined,
					// pageParam: undefined,
					type: 'ajax',
					url: '/?c=CmpCallCard4E&m=loadUnformalizedAddressDirectory',
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
				}
			});

		var unformalizedAdressGrid = Ext.create('Ext.grid.Panel', {
			title: 'Cправочник объектов',
			height: 730,
			flex: 1,
			dock: 'bottom',
			autoScroll: true,
			refId: 'unformalizedAdressGrid',
			viewConfig: {
				loadingText: 'Загрузка',
				preserveScrollOnRefresh: true
			},
			listeners: {
				itemClick: function(){
					this.down('button[itemId=editUnFormalizedAdressButton]').setDisabled(false);
					this.down('button[itemId=deleteUnFormalizedAdressButton]').setDisabled(false);
				},
				render: function(){
					var editBtn = this.down('button[itemId=editUnFormalizedAdressButton]'),
						delBtn = this.down('button[itemId=deleteUnFormalizedAdressButton]');
					this.getSelectionModel().on('selectionchange', function(){
						editBtn.toggle(false);
						delBtn.toggle(false);
					})
				}
			},
			store: storeUN,
			bbar: Ext.create('Ext.PagingToolbar', {
				store: storeUN,
				displayInfo: true,
				beforePageText: 'Страница',
				afterPageText: 'из {0}',
				displayMsg: 'показано {0} - {1} из {2}'
			}),
			//requires: [
			//	'Ext.ux.GridHeaderFilters'
			//],
			//plugins: [
			//	Ext.create('Ext.ux.GridHeaderFilters',
			//		{
			//			enableTooltip: false,
			//			reloadOnChange: true
			//		}
			//	)],
			columns: [
				{ dataIndex: 'UnformalizedAddressDirectory_id', text: 'ID', hidden: true, hideable: false },
				{ dataIndex: 'UnformalizedAddressDirectory_Name', text: 'Название', flex: 1, filter:{
					xtype: 'transFieldDelbut',
					translate: false,
					fieldLabel: null,
					triggerClear: true,
					name: 'unformAdressName',
					displayField: 'UnformalizedAddressDirectory_Name',
					valueField: 'UnformalizedAddressDirectory_id',
					storeName: 'unformalizedAdressStore',
					autocompleteField: false
				} },
				{ dataIndex: 'UnformalizedAddressType_Name', text: 'Тип объекта', width: 200, filter:
					Ext.create('sw.TypeOfUnformalizedAddress',{
						valueField: 'UnformalizedAddressType_Name',
						hideLabel: true
					})
				},
				{ dataIndex: 'Org_Nick', text: 'МО', width: 130, filter: lpuAllLocalCombo},
				{ dataIndex: 'UnformalizedAddressDirectory_lat', text: 'Широта', width: 130, filter:  {xtype: 'transFieldDelbut', translate: false} },
				{ dataIndex: 'UnformalizedAddressDirectory_lng', text: 'Долгота', width: 130, filter:  {xtype: 'transFieldDelbut', translate: false} },
				{ dataIndex: 'UnformalizedAddressDirectory_Address', text: 'Адрес', flex: 1, filter:
					{
						xtype: 'transFieldDelbut',
						fieldLabel: null,
						name: 'Address_AddressText'
					}
				},
				// { dataIndex: 'UnformalizedAddressDirectory_Dom', text: 'Номер дома', width: 90 },
				{ dataIndex: 'LpuBuilding_Name', text: 'Подразделение СМП', flex: 1,
					filter: smpUnits
				},
				{ dataIndex: 'KLAreaStat_id', text: 'KLAreaStat_id', flex: 1, hidden: true, hideable: false  },
				{ dataIndex: 'KLCity_id', text: 'KLCity_id', flex: 1, hidden: true, hideable: false },
				{ dataIndex: 'KLTown_id', text: 'KLTown_id', flex: 1, hidden: true, hideable: false  },
				{ dataIndex: 'KLRgn_id', text: 'KLRgn_id', flex: 1, hidden: true, hideable: false  },
				{ dataIndex: 'KLStreet_id', text: 'KLStreet_id', flex: 1, hidden: true, hideable: false  },
				{ dataIndex: 'KLSubRgn_id', text: 'KLSubRgn_id', flex: 1, hidden: true, hideable: false  }
				
			],
			dockedItems: [
				{
					xtype: 'toolbar',
					dock: 'top',
					items: [
						{
							xtype: 'button',
							itemId: 'addUnFormalizedAdressButton',
							disabled: false,
							text: 'Добавить',
							iconCls: 'save16',
							handler: function () {
								win.showUnformalizedAddressPopupAddEditWindow(null, unformalizedAdressGrid.store);
							}
						},
						{
							xtype: 'button',
							itemId: 'editUnFormalizedAdressButton',
							disabled: true,
							text: 'Изменить',
							iconCls: 'edit16',
							handler: function () {
								var selectedRecord = unformalizedAdressGrid.getSelectionModel().getSelection()[0];
								if (selectedRecord && selectedRecord.data) {
									win.showUnformalizedAddressPopupAddEditWindow(selectedRecord.data)
								}

							}
						},
						{
							xtype: 'button',
							itemId: 'deleteUnFormalizedAdressButton',
							disabled: true,
							text: 'Удалить',
							iconCls: 'delete16',
							handler: function () {
								if (unformalizedAdressGrid.getSelectionModel().getSelection().length == 0) return;
								Ext.Msg.show({
									title: 'Подтверждение',
									msg: 'Удалить неформализованный адрес?',
									buttons: Ext.Msg.YESNO,
									icon: Ext.Msg.QUESTION,
									fn: function (btn) {
										if (btn == 'yes') {
											var myMask = new Ext.LoadMask(win, {msg: "Удаление объекта..."});
											myMask.show();

											var selectedRec = unformalizedAdressGrid.getSelectionModel().getSelection()[0];
											unformalizedAdressGrid.store.remove(selectedRec);

											Ext.Ajax.request({
												url: '/?c=CmpCallCard4E&m=deleteUnformalizedAddress',
												params: {UnformalizedAddressDirectory_id: selectedRec.get('UnformalizedAddressDirectory_id')},
												callback: function (opt, success, response) {
													myMask.hide();
													if (success) {
														var obj = Ext.decode(response.responseText);
														if (obj.success) {
															unformalizedAdressGrid.store.reload();
														} else {
															Ext.Msg.show({
																title: 'Ошибка',
																msg: 'Произошла ошибка при удалинии неформализованного адреса',
																buttons: Ext.Msg.OK,
																icon: Ext.MessageBox.WARNING
															});
															// log('Ошибка удалиния неформализованного адреса: '+obj.Error_Msg);
														}
													}
												}
											})
										}
									}
								})
							}
						},
						{
							xtype: 'button',
							itemId: 'refreshUnFormalizedAdressButton',
							disabled: false,
							text: 'Обновить',
							iconCls: 'refresh16',
							handler: function () {
								unformalizedAdressGrid.store.reload();
								unformalizedAdressGrid.down('button[itemId=editUnFormalizedAdressButton]').setDisabled(true);
								unformalizedAdressGrid.down('button[itemId=deleteUnFormalizedAdressButton]').setDisabled(true);
							}
						},
						{
							xtype: 'button',
							itemId: 'printEmergencyTeamDutyTimeGridButton',
							text: 'Печать',
							iconCls: 'print16',
							handler: function () {
								Ext.ux.grid.Printer.print(unformalizedAdressGrid)
							}
						}
					]
				},
				{
					xtype: 'form',
					//dock: 'top',
					layout: 'form',
					style: 'margin: 0px 0px 0px 0px;', 
					title: null,
					autoWidth: true,
					labelWidth: 165,
					labelAlign: 'right',
					items: [
						{
							layout: 'column',
							labelAlign: 'right',
							border: false,
							items: [
									{
										border: false,
										labelAlign: 'left',
										items: [{								
													xtype: 'textfield',
													name: 'UnformalizedAddressDirectory_Name',
													width: 500,
													labelSeparator : ':',
													fieldLabel: 'Название',
													labelWidth: 150,
													labelAlign: 'right',
												},
												{
													xtype: 'swTypeOfUnformalizedAddress',
													name: 'UnformalizedAddressDirectoryType_Name',
													fieldLabel: 'Тип объекта',
													labelWidth: 150,
													width: 500,
													labelAlign: 'right',
												},						
												{
													xtype: 'lpuAllLocalCombo',
													fieldLabel: 'МО',
													labelAlign: 'left',
													name: 'Lpu_aid',
													bigFont: false,
													autoFilter: false,
													labelAlign: 'right',
													labelWidth: 150,
													width: 500,													
												},						
												{
													xtype: 'SmpUnitsSelectedUser',
													name: 'LpuBuilding_Name',
													fieldLabel: 'Подразделение СМП',
													labelAlign: 'right',
													labelWidth: 150,
													width: 500,
												}
										]
									},{										
										border: false,
										items: [{								
													xtype: 'textfield',
													name: 'UnformalizedAddressDirectory_lat',
													labelSeparator : ':',
													fieldLabel: 'Ширина',
													labelAlign: 'right',
													labelWidth: 100,
													width: 400,
												},
												{
													xtype: 'textfield',
													displayField: 'UnformalizedAddressType_Name',
													valueField: 'UnformalizedAddressDirectory_lng',
													name: 'UnformalizedAddressDirectory_lng',
													fieldLabel: 'Долгота',
													labelAlign: 'right',
													labelWidth: 100,
													width: 400,
												},						
												{
													xtype: 'textfield',
													name: 'UnformalizedAddressDirectory_Address',
													labelSeparator : ':',
													fieldLabel: 'Адрес',
													labelAlign: 'right',
													labelWidth: 100,
													width: 400,
												}
										]
									}				
							]							
						}						
					]
				}				
			]
		})
		
		var cityCombo = Ext.create('sw.dCityCombo', {
			flex: 1,
			listeners: {
				change: function (c, newValue, oldValue, eOpts){
					if (newValue){
						if (newValue.toString().length > 0){
							c.store.getProxy().extraParams = {
							'city_default' : null,
							'region_id' : getGlobalOptions().region.number
							}
						}
					};
				},
				select: function(cmp, recs){
					var cityRec = recs[0];
					
					//var streetCombo = cmp.up('BaseForm').getForm().findField('dStreetsCombo');
					//streetCombo.bigStore.getProxy().extraParams = {
					//	'town_id' : cityRec.get('Town_id'),
					//	'Lpu_id' : sw.Promed.MedStaffFactByUser.current.Lpu_id
					//}
					//streetCombo.bigStore.load();
				}
			}
		});

		cityCombo.store.getProxy().extraParams = {
			'region_id' : getGlobalOptions().region.number,
			'region_name' : getGlobalOptions().region.name,
			'city_default' : getGlobalOptions().region.number,
			'Lpu_id': getGlobalOptions().lpu_id
		};

		var streetsCombo = Ext.create('sw.streetsSpeedCombo', {
			flex: 1,
			name: 'dStreetsCombo',
			fieldLabel: 'Улица / Объект',
			labelAlign: 'right',
			allowBlank: true,
			enableKeyEvents : true,					
			listeners: {
				change: function (c, newValue, oldValue, eOpts)
				{
					if (newValue){
						if (newValue.toString().length > 0) {
							if (cityCombo.getValue())
							{
								c.store.getProxy().extraParams = {
								'town_id' : cityCombo.getValue(),
								'Lpu_id' : sw.Promed.MedStaffFactByUser.current.Lpu_id
							}
							}
						}
						else{
							c.reset()
						}					
					}
					else{c.reset()}
				},
				keypress: function(c, e, o){
				//	var baseForm = this.up('BaseForm'),
				//		searchBtn = baseForm.down('button[itemId=searchUnformAdress]')
				//	if ( (e.getKey() == 13))
				//	{
				//		searchBtn.fireHandler()
				//}
				}
			}
		});
		
		var houseNum = Ext.create('Ext.form.field.Text',{											
			width: 150,
			labelWidth: 50,
			plugins: [new Ux.Translit(true, true)],
			fieldLabel: 'Дом',
			labelAlign: 'right',
			name: 'CmpCallCard_Dom',
			enableKeyEvents : true,
			listeners: {
				keypress: function(c, e, o){
				//	var baseForm = this.up('BaseForm'),
				//		searchBtn = baseForm.down('button[itemId=searchUnformAdress]')
				//	if ( (e.getKey() == 13))
				//	{
				//		searchBtn.fireHandler()
				//}
				}
			}
		})

		
		Ext.applyIf(win, {
			items: [
				{
					xtype: 'container',
					items: [
						unformalizedAdressGrid,
					]
				}

			],
			dockedItems: [
				{
					xtype: 'container',
					dock: 'bottom',

					layout: {
						align: 'right',
						type: 'vbox',
						padding: 3
					},
					items: [
						{
							xtype: 'container',
							height: 30,
							layout: {
								align: 'middle',
								pack: 'center',
								type: 'hbox'
							},
							items: [
								{
									handler: function () {
										win.doSearch();
									},
									iconCls: 'search16',
									text: 'Поиск',
									xtype: 'button',
								}, {
									handler: function () {
										win.doReset();
									},
									margin: '0 5',
									iconCls: 'resetsearch16',
									text: 'Сброс',
									xtype: 'button',									
								},
								{
									style: 'margin: 0px 0px 0px 700px;',
									xtype: 'button',
									//id: 'helpEmergencyTeamDutyTimeGrid',
									text: 'Помощь',
									iconCls   : 'help16',
									handler   : function()
									{
										ShowHelp(win.title);
									}
								},
								{
									xtype: 'button',
									iconCls: 'cancel16',
									text: 'Закрыть',
									margin: '0 5',
									handler: function(){
										win.close()
									}
								}
							]}
					]
				},
			]
		});
		
		win.callParent();
	},
	showUnformalizedAddressPopupAddEditWindow: function(rec, parentStore){

		var popupAddEditWindow = Ext.create('sw.tools.swUnformalizedAddressPopupAddEditWindow', {record: rec, parentStore: parentStore}).show();
		popupAddEditWindow.on('saveUnformalizedAdress', function(UnformalizedAddressDirectory_id){
			var unformalizedAdressGrid = Ext.ComponentQuery.query('[refId=unformalizedAdressGrid]')[0];
			unformalizedAdressGrid.store.reload({
				callback: function(){
					var rec = unformalizedAdressGrid.store.findRecord('UnformalizedAddressDirectory_id', UnformalizedAddressDirectory_id);
					if(rec)
						unformalizedAdressGrid.getSelectionModel().select(rec);
				}
			});
		});

	}
})

//заначка для лучших времен
//		var tip = Ext.create('Ext.tip.ToolTip', {
//			autoHide : false,
//			renderTo: Ext.getBody(),
//			width: 300,
//			maxWidth: 500,
//			//html: 'Press this button to clear the form',
//			layout: {
//				type: 'vbox',
//				align: 'stretch',
//				pack: 'center'
//			},
//			listeners:{
//				show: function(){
//					var placeName = this.down('transFieldDelbut[name=unformAdressName]');
//					
//					placeName.setValue('');
//					placeName.focus();
//					placeName.selectedRecord = null;
//				},
//				hide: function(cmp){
//					var placeName = this.down('transFieldDelbut[name=unformAdressName]');
//					this.removeTemporaryMarkers();
//					
//					placeName.selectedRecord = null;
//				}
//			},
//			
//			removeTemporaryMarkers: function(){
//				
//				var placementCursorMarkers = null;
//					placementCursorMarkers = mapPanel.findMarkerBy('type', 'placementCursor');
//					
//					if (placementCursorMarkers){
//						mapPanel.removeMarkers([placementCursorMarkers])
//					}
//			},
//
//			items: [
//				{
//					xtype: 'transFieldDelbut',
//					fieldLabel: 'Введите название',
//					name: 'unformAdressName',
//					displayField: 'UnformalizedAddressDirectory_Name',
//					valueField: 'UnformalizedAddressDirectory_id',
//					store: 'unformalizedAdressStore',
//					labelWidth: 130,
//					listeners: {
//						change: function( cmp, newValue, oldValue, eOpts){
//							if (newValue.length > 16){
//								tip.setWidth(tip.getWidth()+12)
//							}
//							else{
//								tip.setWidth(300)
//							}
//
//							if(this.selectedRecord){
//								var rec = this.selectedRecord,
//									name = this.selectedRecord.get('UnformalizedAddressDirectory_Name'),
//									lat = this.selectedRecord.get('UnformalizedAddressDirectory_lat'),
//									lng = this.selectedRecord.get('UnformalizedAddressDirectory_lng'),
//									marker = mapPanel.findMarkerBy('type', 'placementCursor');
//									
//
//								if(!marker)
//								{
//									marker = [{
//										point:[lat,lng],
//										baloonContent:'Возможное местоположение',
//										imageHref: '/img/googlemap/firstaid-placement.png',
//										imageSize: [30,35],
//										imageOffset: [-16,-37],
//										additionalInfo: {type:'placementCursor'}
//									}]
//
//									mapPanel.setMarkers(marker);
//									//tip.getOutOfHere(marker);
//								}
//								else{
//									tip.removeTemporaryMarkers();
//								}
//							}
//							else{
//								tip.removeTemporaryMarkers();
//							}
//						},
//						addFullText: function(text){
//							if (this.getRawValue().length > 16){
//								tip.setWidth(12+text.length*12)
//							}
//						}
//					}
//				},
//				{
//					xtype: 'container',
//					height: 30,
//					layout: {
//						align: 'right',
//						pack: 'end',
//						type: 'hbox'
//					},
//					items: [
//						{
//							xtype: 'button',
//						//	id: 'saveEmergencyTeamDutyTimeGrid',
//							iconCls: 'save16',
//							text: 'Сохранить',
//							handler: function(){
//								//me.saveBrigTime(me)
//							}
//						},
//						{
//							xtype: 'button',
//						//	id: 'saveEmergencyTeamDutyTimeGrid',
//							iconCls: 'cancel16',
//							text: 'Отмена',
//							margin: '0 0 0 5',
//							handler: function(){
//								//me.saveBrigTime(me)
//							}
//						}
//					]
//				}
//			]
//		});