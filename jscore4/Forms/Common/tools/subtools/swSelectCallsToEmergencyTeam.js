/*
	Выбор вызова для бригады
*/


Ext.define('sw.tools.subtools.swSelectCallsToEmergencyTeam', {
	alias: 'widget.swSelectCallsToEmergencyTeam',
	extend: 'sw.standartToolsWindow',
	width: 900,
	height: 220,
	title: 'Выбор вызова для бригады',
	defaultFocus: 'grid[refId=callsGrid]',
	
	preLoadWindow: function(parentCallsStore, selectedTeamRec, wialonMap){
		var collectCardsIds = parentCallsStore.collect('CmpCallCard_id'),
			win = this,
			storeCallsForEmergencyTeam = win.callsGrid.store,
			wialonUnits = wialonMap.units;
		
		Ext.Ajax.request({
			url: '/?c=CmpCallCard4E&m=getCallsPriorityFromReason',
			params: {
				CmpCardsArray: collectCardsIds.toString(),
				EmergencyTeamSpec_id: selectedTeamRec.get('EmergencyTeamSpec_id')
			},
			callback: function(opt, success, response){
				if (success){
					//получаем список карт и приоритетов
					//и расставляем приоритеты
					//больше профиль - срочнее вызов
					var obj = Ext.decode(response.responseText),
						callsLocation = [];

					for(var i in obj){
						var recFromParentStore = parentCallsStore.findRecord('CmpCallCard_id', obj[i].CmpCallCard_id);
						
						recFromParentStore.data.ProfilePriority = obj[i].ProfilePriority;
						storeCallsForEmergencyTeam.add(recFromParentStore.data);
						callsLocation.push(recFromParentStore.get('Adress_Name'));
					}
					//если записей нет ввиду отсутствия правила набираем вызовы из родительского колодца

					if (obj.length == 0){
						for(var c=0; c<5; c++){
							var recFromParentStore = parentCallsStore.getAt(c);
							
							recFromParentStore.data.ProfilePriority = 0;
							storeCallsForEmergencyTeam.add(recFromParentStore.data);
							callsLocation.push(recFromParentStore.get('Adress_Name'));
						}
					}
					
					storeCallsForEmergencyTeam.sort('CalcReasonRange', 'ASC');
					storeCallsForEmergencyTeam.sort('ProfilePriority', 'DESC');
					win.callsGrid.getSelectionModel().select(0);
						//получаем координаты машинки бригады по
						var coordsSelectedTeam = wialonMap.getWialonUnitLatLng(selectedTeamRec.get('GeoserviceTransport_id'));

						if(typeof google != 'undefined'){
							if(typeof google.maps.LatLng != 'undefined')
							{
								var carLocation = new google.maps.LatLng(coordsSelectedTeam[0], coordsSelectedTeam[1]);

								//вычисляем время прибытия до мест вызовов
								//и записываем значения в колодец
								wialonMap.calculateMultiTrackTimeDestination([carLocation], callsLocation, function(roadsInfo){
									if(!roadsInfo){return false}
									var arrRoadIndfoRows = roadsInfo.rows[0].elements;
								
									for (i in arrRoadIndfoRows){
										var element = arrRoadIndfoRows[i],
											rec = storeCallsForEmergencyTeam.getAt(i);

										rec.set('DistanceToEmergencyTeam', element.distance.value);
										rec.set('DurationToEmergencyTeam', element.duration.value);
										rec.set('DistanceTextToEmergencyTeam', element.distance.text);
										rec.set('DurationTextToEmergencyTeam', element.duration.text);
									}									
									storeCallsForEmergencyTeam.sort('DurationToEmergencyTeam', 'ASC');
									storeCallsForEmergencyTeam.sort('CalcReasonRange', 'ASC');
									storeCallsForEmergencyTeam.sort('ProfilePriority', 'DESC');									
									win.callsGrid.getSelectionModel().select(0);
									if (obj.length == 0){
										//Ext.Msg.alert('Внимание','Нет доступных правил приоритета для выбранной бригады.', function(){	
										//}.bind(this));
									}
									else{
									}
									
								}.bind(this));
							}
						}
						else{
							//Ext.Msg.alert('Внимание','Сервис гугл не загружен. Повторите попытку позже.')
						}
					
				}
			}.bind(this)
		})
	
	},
	
	initComponent: function() {
		var win = this,
			conf = win.initialConfig;
		
		win.addEvents({
			selectCallToEmergencyTeam: true
		});
		// win.on('render', function(cmp){
			// var pressedkey = new Ext.util.KeyMap({
				// target: win.getEl(),
				// binding: [
					// {
						// key: [13],
						// fn: function(){
							//win.saveBtn.handler();
						// }
					// }
				// ]
			// })
		// });
		
		win.callsGrid = Ext.create('Ext.grid.Panel', {
			border: false,
			refId:'callsGrid',
			viewConfig: {
				loadingText: 'Загрузка',
				markDirty: false
			},
			columns: [
				{text: '№/день',  dataIndex: 'CmpCallCard_Numv', width: 50},
				{text: 'CmpCallCard_id',  dataIndex: 'CmpCallCard_id', hidden: true},
				{text: 'Person_FIO',  dataIndex: 'Person_FIO', flex: 1},
				{text: 'Срочность',  dataIndex: 'CmpCallCard_CalculatedUrgency', width: 80},
				{text: 'ProfilePriority', dataIndex: 'ProfilePriority', hidden: true},
				{text: 'Повод', dataIndex: 'CmpReason_Name', flex: 1},
				{text: 'Адрес',  dataIndex: 'Adress_Name', flex: 1},
				
				{text: 'DistanceTextToEmergencyTeam', dataIndex: 'DistanceTextToEmergencyTeam', hidden: true},
				{text: 'DistanceToEmergencyTeam', dataIndex: 'DistanceToEmergencyTeam', hidden: true},
				{text: 'Время прибытия',  dataIndex: 'DurationTextToEmergencyTeam', width: 100},
				{text: 'DurationToEmergencyTeam',  dataIndex: 'DurationToEmergencyTeam', hidden: true},
				{text: 'Время ожидания', dataIndex: 'EmergencyTeamCalcStatus_id', width: 100}
			],
			store: Ext.create('Ext.data.Store', {
				fields: [
					{ name: 'CmpCallCard_id', type: 'int'},
					{ name: 'Sex_id', type: 'int' },	
					{ name: 'CmpCallCard_prmDate', type: 'string' },
					{ name: 'CmpCallCard_Numv', type: 'string' },
					{ name: 'CmpCallCard_Ngod', type: 'string' },
					{ name: 'Person_FIO', type: 'string' },
					{ name: 'CmpReason_Name',type: 'string' },
					{ name: 'Adress_Name', type: 'string' },
					{ name: 'CmpCallCard_CalculatedUrgency', type: 'string' },
					{ name: 'CmpSecondReason_id', type: 'int' },
					{ name: 'CmpSecondReason_Name', type: 'string' },
					{ name: 'ProfilePriority', type: 'int'},
					
					{ name: 'DistanceToEmergencyTeam', type: 'int'},
					{ name: 'DurationToEmergencyTeam', type: 'int'},
					{ name: 'DistanceTextToEmergencyTeam', type: 'string'},
					{ name: 'DurationTextToEmergencyTeam', type: 'string'},
					{
						name: 'EmergencyTeamCalcStatus_id',
							convert: function(v, record){
								var datePrm = record.get('CmpCallCard_prmDate'),
									delta = new Date() - new Date(Date.parse(datePrm)),
									appendix = delta % 3600000,
									hours = chkzero(Math.floor(delta / 3600000)),
									min = chkzero(Math.floor(appendix / 60000)),
									sec = chkzero(Math.floor((appendix % 100000 / 1000) % 60)),
									roundSec = sec>30?'30':'00',
									result = '';

								function chkzero(num) {
									var str = num.toString();
									if (str.length == 1)
										return '0' + str
									else
										return str
								}

								if (hours > 24) {
									return ''
								}
								else {
									if (hours > 0) {
										result = hours + ':' + min;//+':'+sec;
									}
									else {
										result = min + ':' + roundSec;
									}
								}
								return result
						}
					}
					//CmpUrgencyAndProfileStandartRefSpecPriority_ProfilePriority
				],
				listeners: {
					load: function(){
						
					}
				}
			}),
			listeners: {
				cellkeydown: function( cmp, td, cellIndex, record, tr, rowIndex, e, eOpts ){
					if(e.getKey()==13){
						win.saveBtn.handler();
					}
				}
			}
		})
		
		
					
		win.preLoadWindow(conf.parentCallsStore, conf.selectedTeamRec, conf.wialonMap);		
		
		
		win.centerContent = Ext.create('Ext.container.Container', {
			layout: {
				type: 'vbox',
				align: 'stretch'
			},
			items: [win.callsGrid]
		})
		
		win.saveBtn = Ext.create('Ext.button.Button', {
			text: 'Выбрать',
			iconCls: 'ok16',
			refId: 'saveButton',
			disabled: false,
			handler: function(){
				var selectedCallId = win.callsGrid.getSelectionModel().getSelection()[0].get('CmpCallCard_id');
				win.fireEvent('selectCallToEmergencyTeam', selectedCallId);
			}.bind(this)
		});
		
		
		//отправляем сборку
		win.configComponents = {
			//top: win.topTbar,
			center: win.centerContent,
			leftButtons: win.saveBtn
		};
		
		win.callParent(arguments);
	}
})