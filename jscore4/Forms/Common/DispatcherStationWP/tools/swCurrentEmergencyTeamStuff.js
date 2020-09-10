/*
	Текущий наряд
*/

Ext.define('common.DispatcherStationWP.tools.swCurrentEmergencyTeamStuff', {
	alias: 'widget.swCurrentEmergencyTeamStuff',
	extend: 'sw.standartToolsWindow',
	title: 'Текущий наряд',
	width: 800,
	height: 400,
	onEsc: Ext.emptyFn,
	cls: 'swCurrentEmergencyTeamStuff',
	defaultFocus: 'gridpanel[refId=swEmergencyTeamShiftGP] tableview',

	initComponent: function() {
		var win = this,
			conf = win.initialConfig;
		
		win.wialonUnitsWithCoords = Ext.create('Ext.data.Store', {
			autoLoad: true,
			fields: [				
				{name: 'id', type:'int'},
				{name: 'nm', type:'string'},
				{name: 'pos'}
			],
			proxy: {
				limitParam: undefined,
				startParam: undefined,
				paramName: undefined,
				pageParam: undefined,
				type: 'ajax',
				url: '/?c=Wialon&m=getAllAvlUnitsWithCoords',
				reader: {
					type: 'json',
					successProperty: 'success',
					root: 'items'
				},
				actionMethods: {
					create : 'POST',
					read   : 'POST',
					update : 'POST',
					destroy: 'POST'
				}
			}
		});
		
		win.medPersonallist = Ext.create('Ext.data.Store', {
			autoLoad: true,
			fields: [
				{name: 'MedPersonal_id', type:'int'},
				{name: 'MedPersonal_Code', type:'int'},
				{name: 'MedPersonal_Fio', type:'string'},
				{name: 'LpuSection_id', type:'int'}
			],
			proxy: {
				limitParam: undefined,
				startParam: undefined,
				paramName: undefined,
				pageParam: undefined,
				type: 'ajax',
				url: '/?c=MedPersonal4E&m=loadMedPersonalCombo',
				reader: {
					type: 'json',
					successProperty: 'success',
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
		
		/*
		win.swEmergencyTeamShiftGridSM = Ext.create('Ext.selection.CheckboxModel',{
			listeners: {
				beforedeselect: function( model,record, index, eOpts ) {
					if (record.data.locked) {
						return false;
					}
				}
			}
		});
		*/
		
		win.swEmergencyTeamShiftGrid = Ext.create('Ext.grid.Panel', {
			flex: 1,
			stripeRows: true,
			//selModel:win.swEmergencyTeamShiftGridSM,
			refId: 'swEmergencyTeamShiftGP',
			viewConfig: {
				loadingText: 'Загрузка',
				markDirty: false
			},
			plugins: new Ext.grid.plugin.CellEditing({
				clicksToEdit: 1
			}),
			tbar: [
				{
					xtype: 'button',
					text: 'Новый',
					iconCls: 'add16',
					handler: function(){
						var EmergencyTeamTemplateSetDuty = Ext.create('common.DispatcherStationWP.tools.swEmergencyTeamTemplateSetDuty',
						{
							layout: {
								type: 'fit',
								align: 'stretch'
							},
							maximized: true,
							constrain: true,
							renderTo: Ext.getCmp('inPanel').body
						});
						EmergencyTeamTemplateSetDuty.show();
					}
				},
				{
					xtype: 'button',
					text: 'Изменить',
					iconCls: 'edit16',
					handler: function(){
						win.showEmergencyTeamStuff();
						
						/*
						Ext.create('common.DispatcherStationWP.tools.swEditEmergencyTeamTemplate', {
							action: 'edit',
							config: params,
							listeners : {
								'aftersave': function(wnd, EmergencyTeam_data){	
									var store = win.swEmergencyTeamShiftGrid.getStore();
									var selectionModel = win.swEmergencyTeamShiftGrid.getSelectionModel();
									
									store.reload({
										callback: function() {
											selectionModel.select(
												store.findRecord('EmergencyTeam_id', EmergencyTeam_data.EmergencyTeam_id)
											);
										}
									})
								}
							}
						}).show();
						*/
					}.bind(this)
				},
				{
					xtype: 'button',
					text: 'Шаблоны',
					iconCls: 'inbox16',
					handler: function(){
						var EmergencyTeamTemplateWindow = Ext.create('common.DispatcherStationWP.tools.swEmergencyTeamTemplateWindow', {
							layout: {
								type: 'fit',
								align: 'stretch'
							},
							maximized: true,
							constrain: true,
							renderTo: Ext.getCmp('inPanel').body
						});
						EmergencyTeamTemplateWindow.show();
					}
				},
				{
					xtype: 'button',
					text: 'Укладка',
					iconCls: 'drug-sklad16',
					handler: function(){
						var selectedTeam = win.swEmergencyTeamShiftGrid.getSelectionModel().getSelection()[0];
						if ( typeof selectedTeam  == 'undefined') {
							setTimeout(function(){
								Ext.Msg.alert('Ошибка','Бригада не выбрана');
							},1000);
							return false;
						}
						var	swEmergencyTeamDrugsPackWin = Ext.create('sw.tools.subtools.swEmergencyTeamDrugsPack', {
								EmergencyTeam_id: selectedTeam.get('EmergencyTeam_id')						
							});
						swEmergencyTeamDrugsPackWin.show();
					}
				},
				{
					xtype: 'button',
					text: 'История',
					iconCls: 'journal16',
					refId: 'HistoryButton',
					disabled: true,
					handler: function(){
						var selectedTeam = win.swEmergencyTeamShiftGrid.getSelectionModel().getSelection()[0];
						var EmergencyTeamID = selectedTeam.get('EmergencyTeam_id');
						var EmergencyTeamNUM = selectedTeam.get('EmergencyTeam_Num');
						var LpuBuildingNAME = selectedTeam.get('LpuBuilding_Name');
 						if(!EmergencyTeamID) return;
						Ext.create('sw.tools.swBrigadesHistory', {
							EmergencyTeam_id: EmergencyTeamID,
							EmergencyTeam_Num: EmergencyTeamNUM,
							LpuBuilding_Name: LpuBuildingNAME
						}).show();
					}
				},
				{
					xtype: 'button',
					text: 'Печать',
					iconCls: 'print16',
					handler: function(){
						Ext.ux.grid.Printer.print(win.swEmergencyTeamShiftGrid)
					}
				},
				
				{
					xtype: 'button',
					disabled: false,
					text: 'Обновить место',
					iconCls: 'refresh16',
					handler: function(){
						win.wialonUnitsWithCoords.reload();
						win.setPlaceFromWialonId(win.swEmergencyTeamShiftGrid.store);
					}
				}
			],
			listeners: {
				itemClick: function(cmp, record, item, index, e, eOpts ){
					//win.checkEmptyFioHeadShift();
				},
				cellkeydown: function(cmp, td, cellIndex, record, tr, rowIndex, e, eOpts){
					switch(e.getKey()){
						//case 27: {win.winPopupGrid.close(); break;}
						case 13: {
							win.showSubMenu( win.swEmergencyTeamShiftGrid.columns[8].el.getX(), cmp.getY()+e.target.offsetTop+e.target.clientHeight);
							break;
						}
					}
				},
				celldblclick: function( cmp, td, cellIndex, record, tr, rowIndex, e, eOpts ){

				}
			},
			store: new Ext.data.JsonStore({
				autoLoad: true,
				numLoad: 0,
				storeId: 'EmergencyTeamShiftStore',
				fields: [
					{name: 'EmergencyTeam_id', type: 'int'},
					{name: 'CMPTabletPC_id', type: 'int'},
					{name: 'EmergencyTeam_Num', type: 'string'},
					{name: 'EmergencyTeamSpec_Name', type: 'string'},
					{name: 'EmergencyTeam_CarModel', type: 'string'},
					{name: 'EmergencyTeam_PortRadioNum', type: 'int'},			
					{name: 'EmergencyTeam_GpsNum', type: 'string'},	
					{name: 'LpuBuilding_Nick', type: 'string'},	

					{name: 'EmergencyTeam_CarNum', type: 'string'},					
					{name: 'EmergencyTeam_CarBrand', type: 'string'},
					{name: 'EmergencyTeam_IsOnline', type: 'string'},
					{name: 'Lpu_id', type: 'int'},									
					{name: 'LpuBuilding_id', type: 'string'},
					{name: 'LpuBuilding_Name', type: 'string'},
					
					{name: 'EmergencyTeamStatus_id', type: 'int'},
					{name: 'EmergencyTeam_HeadShiftFIO', type: 'string'},
					{name: 'EmergencyTeam_HeadShift2FIO', type: 'string'},					
					{name: 'EmergencyTeam_DriverFIO', type: 'string'},
					{name: 'EmergencyTeam_Driver2FIO', type: 'string'},					
					{name: 'EmergencyTeam_Assistant1FIO', type: 'string'},
					{name: 'EmergencyTeam_Assistant2FIO', type: 'string'},
					{name: 'EmergencyTeam_DutyTime', type: 'string'},
					{name: 'GeoserviceTransport_id', type: 'int'},
					{name: 'GeoserviceTransport_name', type: 'string'},
					{name: 'EmergencyTeamDuty_id', type: 'int'},
					{name: 'EmergencyTeamDuty_DTStart',
						convert: function(v, record){
							var e = Ext.Date.parse(v, "Y-m-d H:i:s")
							return Ext.Date.format(e, 'H:i')
						}
					},
					{name: 'EmergencyTeamDuty_DTFinish',
						convert: function(v, record){
							var e = Ext.Date.parse(v, "Y-m-d H:i:s")
							return Ext.Date.format(e, 'H:i')
						}
					},
					{name: 'medPersonCount', type: 'int'},
					{name: 'HeadTeamAndCount',
						convert: function(v, record){
							var headFio = record.get('EmergencyTeam_HeadShiftFIO'),
								res = record.get('medPersonCount');
							return headFio+' + '+res
						}
					},
					{name: 'CalculateAddress', type: 'string'},
					{name: 'locked', type: 'boolean', defaultValue: true},

					{name: 'EmergencyTeam_HeadShift', type: 'int', convert: null},
					{name: 'EmergencyTeam_HeadShift2', type: 'int', convert: null},					
					{name: 'EmergencyTeam_Driver', type: 'int', convert: null},
					{name: 'EmergencyTeam_Driver2', type: 'int', convert: null},					
					{name: 'EmergencyTeam_Assistant1', type: 'int', convert: null},
					{name: 'EmergencyTeam_Assistant2', type: 'int', convert: null},
					{name: 'EmergencyTeamSpec_id', type: 'int'},					
					{name: 'EmergencyTeamDuty_ChangeComm', type: 'string'},
					
					{name: 'EmergencyTeam_Head1StartTime', type: 'string'},
					{name: 'EmergencyTeam_Head1FinishTime', type: 'string'},
					{name: 'EmergencyTeam_Head2StartTime', type: 'string'},
					{name: 'EmergencyTeam_Head2FinishTime', type: 'string'},
					{name: 'EmergencyTeam_Assistant1StartTime', type: 'string'},
					{name: 'EmergencyTeam_Assistant1FinishTime', type: 'string'},
					{name: 'EmergencyTeam_Assistant2StartTime', type: 'string'},
					{name: 'EmergencyTeam_Assistant2FinishTime', type: 'string'},
					{name: 'EmergencyTeam_Driver1StartTime', type: 'string'},
					{name: 'EmergencyTeam_Driver1FinishTime', type: 'string'},
					{name: 'EmergencyTeam_Driver2StartTime', type: 'string'},
					{name: 'EmergencyTeam_Driver2FinishTime', type: 'string'},
				],
				proxy: {
					limitParam: undefined,
					startParam: undefined,
					paramName: undefined,
					pageParam: undefined,
					type: 'ajax',
					url: '/?c=EmergencyTeam4E&m=loadEmergencyTeamShiftList',
					reader: {
						type: 'json',
						successProperty: 'success',
						root: 'data'
					},
					actionMethods: {
						create : 'POST',
						read   : 'POST',
						update : 'POST',
						destroy: 'POST'
					},
					extraParams:{
						dateFactFinish: Ext.Date.format(new Date(), 'd.m.Y H:i:s'),
						dateFactStart: Ext.Date.format(new Date(), 'd.m.Y H:i:s'),
						// запросим список нарядов тех подразделений СМП, которые были выбраны  пользователем при входе в АРМ в форме «Выбор подстанций для управления»
						// если этот параметр не отправлять, то загрузится только текущий
						loadSelectSmp: true 
					}
				},
				listeners: {
					load: function(store, records, successful, eOpts ) {
						if(successful) {
							win.swEmergencyTeamShiftGrid.getSelectionModel().select(0);
						}
						if(records.length>0){
							win.down('button[refId=HistoryButton]').enable();
						}
						win.setPlaceFromWialonId(store);
					}
				}
			}),
			columns: [
				{ dataIndex: 'EmergencyTeam_id', text: 'ID', key: true, hidden: true, hideable: false },
				{ dataIndex: 'CMPTabletPC_id', hidden: true, hideable: false },
				{ dataIndex: 'LpuBuilding_Name', text: 'Подразделение СМП',  flex: 1, hideable: false },
				{ dataIndex: 'EmergencyTeam_Num', text: 'Номер бригады', width: 100, hideable: false },
				{ dataIndex: 'EmergencyTeamSpec_Name', text: 'Профиль',  flex: 1, hideable: false },
				{ dataIndex: 'GeoserviceTransport_name', text: 'GPS/ГЛОНАСС', width: 100, hideable: false },
				//{ dataIndex: 'LpuBuilding_Nick', text: 'П/С', width: 60, hideable: false },
				{ dataIndex: 'EmergencyTeamDuty_DTStart', text: 'Смена с', width: 80, hideable: false },
				{ dataIndex: 'EmergencyTeamDuty_DTFinish', text: 'Смена по', width: 80, hideable: false },
				{ dataIndex: 'HeadTeamAndCount', text: 'Состав', flex: 1, hideable: false },
				
				//{ dataIndex: 'CalculateAddress', text: 'Адрес Гугл', flex: 1, hideable: false  },
				{ dataIndex: 'CalculateAddressWialon', text: 'Адрес', flex: 1, hideable: false  },				
				{
					xtype:'actioncolumn',
					width:20,
					items: [{
						iconCls: 'grid-cell-moreinfo-btn',
						handler: function(grid, rowIndex, colIndex, o, event, record) {
							win.showSubMenu(event.getX(),event.getY(), record);
						}
					}]
				}
				
			]
		});
		
		win.currentGridContainer = Ext.create('Ext.container.Container', {
			layout: 'fit',
			hidden: false,
			items: win.swEmergencyTeamShiftGrid
		});
		
		
		win.printButton = Ext.create('Ext.button.Button', {
			refId: 'printEmergencyTeamDutyTimeGridButton',
			text: 'Печать',
			iconCls: 'print16',
			hidden: true,
			disabled: true,
			handler: function(){
				Ext.ux.grid.Printer.print(win.swEmergencyTeamTemplateGrid);
			}
		});

		
		//отправляем сборку
		win.configComponents = {
			//top: win.topTbar,
			center: [win.currentGridContainer],
			//subBottomItems: [win.nextButton,win.saveButton],
			leftButtons: win.printButton
		}
		
		win.callParent(arguments);
	},
	
	showEmergencyTeamStuff: function(){
		var win = this,
			parentSelRec = win.swEmergencyTeamShiftGrid.getSelectionModel().getSelection()[0];

		Ext.create('sw.tools.subtools.swEmergencyTeamPopupEditWindow', {
			sel: parentSelRec.get('EmergencyTeam_Num')+' '+parentSelRec.get('EmergencyTeamSpec_Name'),
			//startTime: parentSelRec.get('EmergencyTeamDuty_DTStart'),
			startTime: Ext.Date.parse( (Ext.Date.format(new Date(), "Y-m-d") + ' ' + parentSelRec.get('EmergencyTeamDuty_DTStart')), 'Y-m-d H:i' ),
			//finishTime: parentSelRec.get('EmergencyTeamDuty_DTFinish'),
			finishTime: Ext.Date.parse( (Ext.Date.format(new Date(), "Y-m-d") + ' ' + parentSelRec.get('EmergencyTeamDuty_DTFinish')), 'Y-m-d H:i' ),
			dataPickerValue: new Date(),
			EmergencyTeam_id: parentSelRec.get('EmergencyTeam_id'),
			EmergencyTeam_Num: parentSelRec.get('EmergencyTeam_Num'),
			EmergencyTeam_CarNum: parentSelRec.get('EmergencyTeam_CarNum'),
			EmergencyTeam_CarBrand: parentSelRec.get('EmergencyTeam_CarBrand'),
			EmergencyTeam_CarModel: parentSelRec.get('EmergencyTeam_CarModel'),
			EmergencyTeam_PortRadioNum: parentSelRec.get('EmergencyTeam_PortRadioNum'),
			EmergencyTeam_GpsNum: parentSelRec.get('EmergencyTeam_GpsNum'),
			LpuBuilding_id: parentSelRec.get('LpuBuilding_id'),
			CMPTabletPC_id: parentSelRec.get('CMPTabletPC_id'),
			EmergencyTeamSpec_id: parentSelRec.get('EmergencyTeamSpec_id'),
			EmergencyTeamDuty_ChangeComm: parentSelRec.get('EmergencyTeamDuty_ChangeComm'),
			
			EmergencyTeam_HeadShift: parentSelRec.get('EmergencyTeam_HeadShift'),
			EmergencyTeam_HeadShift2: parentSelRec.get('EmergencyTeam_HeadShift2'),
			EmergencyTeam_Assistant1: parentSelRec.get('EmergencyTeam_Assistant1'),
			EmergencyTeam_Assistant2: parentSelRec.get('EmergencyTeam_Assistant2'),
			EmergencyTeam_Driver: parentSelRec.get('EmergencyTeam_Driver'),
			EmergencyTeam_Driver2: parentSelRec.get('EmergencyTeam_Driver2'),			
			EmergencyTeamDuty_id: parentSelRec.get('EmergencyTeamDuty_id'),
			EmergencyTeam_Head1StartTime: parentSelRec.get('EmergencyTeam_Head1StartTime'),
			EmergencyTeam_Head1FinishTime: parentSelRec.get('EmergencyTeam_Head1FinishTime'),
			EmergencyTeam_Head2StartTime: parentSelRec.get('EmergencyTeam_Head2StartTime'),
			EmergencyTeam_Head2FinishTime: parentSelRec.get('EmergencyTeam_Head2FinishTime'),
			EmergencyTeam_Assistant1StartTime: parentSelRec.get('EmergencyTeam_Assistant1StartTime'),
			EmergencyTeam_Assistant1FinishTime: parentSelRec.get('EmergencyTeam_Assistant1FinishTime'),
			EmergencyTeam_Assistant2StartTime: parentSelRec.get('EmergencyTeam_Assistant2StartTime'),
			EmergencyTeam_Assistant2FinishTime: parentSelRec.get('EmergencyTeam_Assistant2FinishTime'),
			EmergencyTeam_Driver1StartTime: parentSelRec.get('EmergencyTeam_Driver1StartTime'),
			EmergencyTeam_Driver1FinishTime: parentSelRec.get('EmergencyTeam_Driver1FinishTime'),
			EmergencyTeam_Driver2StartTime: parentSelRec.get('EmergencyTeam_Driver2StartTime'),
			EmergencyTeam_Driver2FinishTime: parentSelRec.get('EmergencyTeam_Driver2FinishTime'),
			
			callback:function(r){
				win.swEmergencyTeamShiftGrid.store.reload();
			}
		}).show();
	},
	
	showSubMenu: function(x,y,r){
		var win = this,
			recTeam = r?r:win.swEmergencyTeamShiftGrid.getSelectionModel().getSelection()[0],
			storeTeamStatus = Ext.data.StoreManager.lookup('common.DispatcherDirectWP.store.EmergencyTeamStatusStore'),
			subMenu = Ext.create('Ext.menu.Menu', {
			plain: true,
			renderTo: Ext.getBody(),
			//constrainTo: (x&&y)? null : win.swEmergencyTeamShiftGrid.getView().getSelectedNodes()[0].querySelector('.grid-cell-moreinfo-btn'),
			items: [
				{
					text: 'Статус',
					hideOnClick: false,
					handler: function(i){
						i.cancelDeferHide();
						i.doExpandMenu();
					},
					menu: {
						xtype: 'menu',
						itemId: 'EmergencyStatusDynamicSubMenu',
						closeAction: 'hide',
						listeners: {
							show: function(sm){
								var toSelectItem = sm.down('menuitem[value='+recTeam.get('EmergencyTeamStatus_id')+']');
									if (toSelectItem){
										toSelectItem.setIconCls('ok16');
									}
							},
							click: function(m,i)
							{
								var status = i.value,
									team = recTeam.get('EmergencyTeam_id'),
									armType = sw.Promed.MedStaffFactByUser.last.ARMType;
									
								if (status && team)
								{
									Ext.Ajax.request({
										url: '/?c=EmergencyTeam4E&m=setEmergencyTeamStatus',
										callback: function(opt, success, response) {
											if (success){
												response_obj = Ext.decode(response.responseText),
												data = {'EmergencyTeam_id':team};
												if (win.socket){
													win.socket.emit('changeEmergencyTeamStatus', data, 'changeStatus', function(data){});
												}												
												win.swEmergencyTeamShiftGrid.getStore().reload();
											}
										}.bind(this),
										params: {
											'EmergencyTeamStatus_id': status,
											'EmergencyTeam_id':	team,
											'ARMType': armType
										}
									})
								}
							}
						}
					}
				},
				{
					text: 'Состав',
					handler: function(i){
						win.showEmergencyTeamStuff();
						/*var ewin = Ext.create('sw.tools.subtools.swEmergencyTeamOperEnvAddEdit', {
							action: 'edit',
							EmergencyTeam_id: recTeam.get('EmergencyTeam_id'),
							height: 260
						});
						
						ewin.on('render', function(){
							ewin.down('fieldset[refId=commonInfo]').hide();
						})
						
						ewin.show();*/
					}
				},{
					text: 'Смена машины',
					disabled: true,
					handler: function(i){
						
					}
				},{
					text: 'Место',
					disabled: true,
					handler: function(i){
						
					}
				},{
					text: 'История',
					disabled: true,
					handler: function(i){
						
					}
				}
				]
			}),
			subMenuEmergencyStatuses = subMenu.down('menu[itemId=EmergencyStatusDynamicSubMenu]');
			
		storeTeamStatus.each(function(rec){
			subMenuEmergencyStatuses.add({
				text: rec.get('EmergencyTeamStatus_Name'),
				value: rec.get('EmergencyTeamStatus_id')
			})
		});
			
		subMenu.showAt(x,y);
	},
	
	setPlaceFromWialonId: function(s){
		var win = this,
			coordsArray = [];
			//coordsArrayG = [],
			//service = new google.maps.DistanceMatrixService();			
		s.each(function(rec){
			if (rec.get('GeoserviceTransport_id') != null && rec.get('GeoserviceTransport_id') != 0) {
				var wiaId = rec.get('GeoserviceTransport_id'),
					wiaRec = win.wialonUnitsWithCoords.findRecord('id', wiaId);
				if (wiaRec) {
					var carPos = wiaRec.get('pos');
					//coordsArrayG.push(new google.maps.LatLng(parseFloat(carPos.y), parseFloat(carPos.x)));
					coordsArray.push({y:carPos.y, x:carPos.x});
				}
			}
		})
		if(coordsArray.length>0){
			Ext.Ajax.request({
				url: '/?c=Wialon&m=geocodeCoords',
				params: {
					coords: Ext.JSON.encode(coordsArray)
				},
				callback: function(opt, success, response) {					
					if (response.status == 200 && response.statusText == 'OK'){
						var res = Ext.JSON.decode(response.responseText);
						
						for(var i in res.locations){
							s.getAt(i).set('CalculateAddressWialon', res.locations[i]);
						}
					}
				}.bind(this)
			});			
			/*
			гугл адреса - !пока не удалять!
			вдруг концепция изменится
			service.getDistanceMatrix(
			{
				origins: [coordsArrayG[0]],
				destinations: coordsArrayG,
				travelMode: google.maps.TravelMode.DRIVING,
				avoidHighways: false,
				avoidTolls: false
			}, function(response, status){
				if(response){
					for(var i in response.destinationAddresses){
						s.getAt(i).set('CalculateAddress', response.destinationAddresses[i]);
					}
				}
			});
			*/
		}
	}
})

