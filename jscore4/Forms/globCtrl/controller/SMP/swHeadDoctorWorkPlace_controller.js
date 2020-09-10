/*
 * Контроллер АРМа диспетчера вызовов
 */


Ext.define('SMP.swHeadDoctorWorkPlace_controller', {
    extend: 'SMP.swSMPDefaultController_controller', 
		
	models: [
		'common.HeadDoctorWP.model.CmpCallCard',
		'common.HeadDoctorWP.model.EmergencyTeam'		
    ],
	
    stores: [
		'common.HeadDoctorWP.store.CmpCallsStore',
		'common.HeadDoctorWP.store.EmergencyTeamStore',
		'common.HeadDoctorWP.store.MedPersonalStore',
		// 'stores.smp.GeoserviceTransportStore',
		'common.HeadDoctorWP.store.EmergencyTeamStatusHistoryStore',
		//'common.HeadDoctorWP.store.CmpCallCardStatusHistoryStore'
    ],
	requires: [
		'sw.CmpCallsList',
		'sw.CmpCallsUnderControlList'
	],
	
	refs: [
        {
            ref: 'callsGridHD',
            selector: 'swHeadDoctorWorkPlace grid[refId=callsGridHD]'
        },{
			ref: 'callDetailParentHD',
			selector: 'swHeadDoctorWorkPlace tabpanel[refId=callDetailParentHD]'
		},{
			ref: 'callDetailHD',
			selector: 'swHeadDoctorWorkPlace panel[refId=callDetailHD]'
		},{
			ref: 'callDetailFirstHD',
			selector: 'swHeadDoctorWorkPlace panel[refId=callDetailFirstHD]'
		},{
			ref: 'teamsGridHD',
			selector: 'swHeadDoctorWorkPlace grid[refId=teamsGridHD]'
		},{
			ref: 'teamDetailHD',
			selector: 'swHeadDoctorWorkPlace panel[refId=teamDetailHD]'
		},{
			ref: 'winHD', 
			selector: 'swHeadDoctorWorkPlace'
		},{
			ref: 'mapPanelHD',
			selector: 'swHeadDoctorWorkPlace swsmpmappanel'
		},
		{
			ref: 'leftSideContainerHD',
			selector: 'swHeadDoctorWorkPlace container[refs=leftSideContainerHD]'
		},
		{
			ref: 'rightSideContainerHD',
			selector: 'swHeadDoctorWorkPlace container[refs=rightSideContainerHD]'
		},
		{
			ref: 'ShowInMapBtnHD',
			selector: 'swHeadDoctorWorkPlace button[refId=showInMapBtnHD]'
		},
		{
			ref: 'callTrackTab',
			selector: 'swHeadDoctorWorkPlace panel[refId=callTrackTab]'
		}
	],
    mixins: {
        WialonTrackPlayerTabController: 'SMP.HeadDoctorWorkPlace.swWialonTrackPlayerTab_controller'
    },
	sortCalls: 'urgency',
	withoutChangeStatus: true,
    init: function() {
		
		var cntr = this;
		
		cntr.minTimersToCalls = {};
		cntr.maxTimersToCalls = {};
		
        this.control({
			'swHeadDoctorWorkPlace': {
				beforerender: function(cmp){
					/*
					var CmpCallsListDP = Ext.getCmp('callsListDP');
					if(CmpCallsListDP && CmpCallsListDP.down('CmpCallsList'))
						CmpCallsListDP.down('CmpCallsList').close();

					var CmpCallsListDW = Ext.getCmp('callsListDW');
					if(CmpCallsListDW && CmpCallsListDW.down('CmpCallsList'))
						CmpCallsListDW.down('CmpCallsList').close();
					*/
					//прикручиваем ноджс
					connectNode(cmp);
				},
				render: function(cmp){
					var me = cmp,
						globals = getGlobalOptions(),
                        baseForm = cntr.getCallDetailHD().getForm(),
						phoneField = baseForm.findField('CmpCallCard_Telf'),
                        callsPanel = cntr.getCallsGridHD(),
						storeCalls = Ext.data.StoreManager.lookup('common.HeadDoctorWP.store.CmpCallsStore'),
						teamsCalls = Ext.data.StoreManager.lookup('common.HeadDoctorWP.store.EmergencyTeamStore'),
						statusHistoryStore = Ext.data.StoreManager.lookup('cmpCallCardStatusHistoryStore'),
						geoservice_store = cntr.getGeoserviceTransportStore(),
						callsGridHD = cntr.getCallsGridHD(),
						teamsGridHD = cntr.getTeamsGridHD(),
						dt = new Date(Date.now()),
						firstDayMonth = Ext.Date.format(Ext.Date.getFirstDateOfMonth(dt), 'd.m.Y'),
						lastDayMonth = Ext.Date.format(Ext.Date.getLastDateOfMonth(dt), 'd.m.Y'),
						isOverCall = false;

					cntr.isNmpArm = cmp.isNmpArm;
					cntr.curArm = cmp.curArm;

                    cntr.baseForm = baseForm;
                    cntr.armWindow = cmp;

					Ext.Ajax.request({
                        url: '/?c=CmpCallCard4E&m=getOperDepartamentOptions',
						callback: function (opt, success, response) {
                            if(success && response.responseText){
                                cntr.setOperDeptOptions(Ext.decode(response.responseText));

								cntr.setVisibilityTools();
                            }
						}
					});
					Ext.ComponentQuery.query('button[refId=buttonChooseArm]')[0].on(
						'afterSelectArm', function(){ var w = Ext.ComponentQuery.query('window[cls=waitingForAnswerHeadDoctorWindow]'); if (w && w[0]) w[0].close(); }
					);

                    Ext.Ajax.request({
                        url: '/?c=CmpCallCard4E&m=getIsOverCallLpuBuildingData',
                        callback: function (opt, success, response) {
                            if(!Ext.isEmpty(response.responseText))
                                isOverCall = Ext.decode(response.responseText);
                        }
                    });

					storeCalls.load({
						scope: this,
						callback: function(records, operation, success) {

                            var grFeature = callsGridHD.getView().getFeature(cmp.id+'_GroupingGridHDFeature');

							//callsGridHD.getView().focus();
                            var data = [];
                            var bigGroups = callsPanel.bigStore.groups.items;
                           // var recs = callsPanel.bigStore.groups.items[0].records.concat(callsPanel.bigStore.groups.items[1].records);

                            bigGroups.forEach(function(value, index, arr) {
                                if((index+1).inlist([1,2])){
                                    data = data.concat(bigGroups[index].records);
                                }
                                else{
                                    data = data.concat(bigGroups[index].records[0]);
                                }
                            });

                            callsPanel.store.loadData(data);

							grFeature.collapse(3);
							grFeature.collapse(4);
							grFeature.collapse(5);
							grFeature.collapse(6);
							grFeature.collapse(7);

							cntr.sortCmpCalls('CmpCallCard_Urgency');

						}
					});
					//cntr.sortCmpCalls();
					teamsCalls.load();
					
					//обновление гридов по таймауту
					setInterval(function(){
						var activeWin = Ext.WindowManager.getActive();

						if (cmp==activeWin){
							if (cmp.socket){
								if (!cmp.socket.connected){
									this.reloadStores();
									connectNode(cmp);
								}
								else{
									//this.nodeReloadStores();
									//пока нет обновления по ноду
									this.reloadStores();
								}
							}
							else{
								this.reloadStores();
							}
							cntr.checkNeedActiveCall();
							if(cntr.getOperDeptOptions().SmpUnitParam_IsDocNoControl == 'true'){
								cntr.checkLpuBuildingWithoutSmpUnitHistory();
							}

						}
					}.bind(this),30000);
					
					//на обновление стора геосервиса 
					//обновляем адреса
					//обновляем маркеры бригад
					
					var addressForTransports = Ext.create('Ext.data.Store', {
						fields: ['GeoserviceTransport_id','geoAddress'],
						storeId: 'addressForTransportsStore',
						data: []
					 });
					 
					geoservice_store.on( 'load' , function( store , records, successful, eOpts) {
						if(!records) return;
						
						var marker = {},
							markerList = [],
							coordsArray = [],
							addressForTransportsStore = Ext.data.StoreManager.lookup('addressForTransportsStore');

						addressForTransportsStore.removeAll();
						cntr.teamsWithGeoId = [];
						
						for (var i = 0; i < records.length; i++) {
							if (!records[i].data) {
								continue; 
							}
							
							var teamInGrid = teamsCalls.findRecord('GeoserviceTransport_id', records[i].data.GeoserviceTransport_id),
								baloon = cntr.getBaloonContent(teamInGrid);

							if(teamInGrid/* && (typeof google === "object")&& (typeof google.maps === "object")*/)
							{
								coordsArray.push({lat:records[i].data.lat, lng: records[i].data.lng});
								//coordsArray.push( new google.maps.LatLng(records[i].data.lat, records[i].data.lng) );
								cntr.teamsWithGeoId.push(teamInGrid);
							
							
								marker = {
									id: records[i].data.GeoserviceTransport_id,
									//title: records[i].data.GeoserviceTransport_name,
									title: teamInGrid.get('EmergencyTeam_Num')+' '+teamInGrid.get('EmergencyTeamStatus_Name'),
									//title: records[i].data.GeoserviceTransport_name,
									point: [ records[i].data.lat , records[i].data.lng ],
									direction: records[i].data.direction,
									statusCode: teamInGrid.get('EmergencyTeamStatus_Code'),
									baloon: baloon? baloon: null
								}
								markerList.push(marker);
							}							
						};
						
						for (i = 0; i < markerList.length; i++) {
							// @TODO: Доработать удаление маркеров, данных по которым не пришло (по id)
							//if(cntr.getMapPanelHD().isVisible())
								cntr.getMapPanelHD().setAmbulanceMarker(markerList[i]);
						};

						//гугл адреса - !пока не удалять!
						//вдруг концепция изменится
						/*
						cntr.calcAddressesFromCoordsByGoogle(coordsArray, 
							function(response){
								for(var i in response.destinationAddresses){
									teamsCalls.findRecord('GeoserviceTransport_id', records[i].data.GeoserviceTransport_id);
									if(cntr.teamsWithGeoId[i])
									{
										cntr.teamsWithGeoId[i].set('CalculateAddress', response.destinationAddresses[i]);
										addressForTransportsStore.add(
											{ 
												GeoserviceTransport_id: cntr.teamsWithGeoId[i].get('GeoserviceTransport_id'),
												geoAddress: response.destinationAddresses[i] 
											}
										);
									}									
								}								
							}						
						)
						*/
					});
					
					teamsCalls.on('load', function(store , records, successful, eOpts){
						var tableView = teamsGridHD.getView();
						
						store.each(function(rec){
							if(rec.get('isOverTime')==2){
								//добавление класса row где время работы бригады закончено, но бригада находится на обслуживании вызова
								tableView.addRowCls(rec, 'isOverTime');
							}
						});

						geoservice_store.getExtraParamsFn = function () {
							return {
								'filtertransport_ids': JSON.stringify(
									(teamsCalls.count()) ? teamsCalls.collect('GeoserviceTransport_id') : [0]
								)
							};
						};
						geoservice_store.runAutoRefresh();
					});
					
					//обновляем маркеры вызовов
					
					storeCalls.on('load', function(store , records, successful, eOpts){
						
						//@todo ОПТИМИЗИРОВАТЬ КОД - жестокие фризы
						
						//пустые записи - пустые группы
						/*
						for(var c1=1; c1<8; c1++){
							if(!store.findRecord('CmpGroup_id', c1) && !(isOverCall && c1 == 2))
								store.add({CmpGroup_id: c1});
						}
						*/
						//здесь отложена функция
						//для четкого(до секунды) переключения вызова в 2 столбец
						//через for, что ниже получается длиннее или вообще не получается :(
						store.each(function(rec){
							var cardId = rec.get('CmpCallCard_id'),
								minTimeSmp = rec.get('timeToAlertByMinTimeSMP'),
								maxTimeSmp = rec.get('timeToAlertByMaxTimeSMP'),
								time = rec.get('EventWaitDuration');
							
							if (minTimeSmp>0 && !(cntr.minTimersToCalls[cardId])){
								cntr.minTimersToCalls[cardId] = setTimeout(function(){
									store.findRecord('CmpCallCard_id', cardId).set('CmpGroup_id', 2);
								}.bind(this), minTimeSmp*1000);
							}
							if (maxTimeSmp>0 && !(cntr.maxTimersToCalls[cardId])){							
								if(cntr.minTimersToCalls[cardId]){clearTimeout(cntr.minTimersToCalls[cardId]);}
								cntr.maxTimersToCalls[cardId] = setTimeout(function(){
									store.findRecord('CmpCallCard_id', cardId).set('CmpGroup_id', 2);
								}.bind(this), maxTimeSmp*1000);
							}
							if(cntr.getMapPanelHD().isVisible())
								cntr.getMapPanelHD().setCmpCallCardMarkerInList(rec,function(point){});

							if(!Ext.isEmpty(time)) {
								var newVal = {
									EventWaitDuration: 0,
									timeEventBreak: rec.raw.timeEventBreak
								};
								if(parseInt(time)){
									newVal.EventWaitDuration = parseInt(time);
								}
								rec.data.EventWaitDuration = cntr.getEventWaitDuration(newVal);
							}
							
						});
						/*
						var grFeature = cntr.getCallsGridHD().getView().getFeature(cntr.getWinHD().id+'_GroupingGridHDFeature'),
							sel = cntr.getCallsGridHD().getSelectionModel().getSelection()[0],
							leftContainer = cntr.getLeftSideContainerHD(),
							callsGridHD = cntr.getCallsGridHD();							
							
								grFeature.collapse(3);
								grFeature.collapse(4);
								grFeature.collapse(5);
								grFeature.collapse(6);
						*/
					});

					cntr.setLpuBuildingsOnMap();

					phoneField.onTriggerClick = function(){
						var input = phoneField.inputEl.dom;
						if(!(input.getAttribute('disabled') == 'disabled'
							&& !getRegionNick().inlist(['ufa', 'krym', 'kz'])))
						{
							var toPass = (input.getAttribute('type') == 'text'),
								val = toPass?'password':'text';
							if(val == 'text'){

								Ext.Ajax.request({
									url: '/?c=CmpCallCard&m=setCmpCallCardEvent',
									callback: function (opt, success, response) {
										statusHistoryStore.reload();
									},
									params: {
										CmpCallCard_id: baseForm.findField('CmpCallCard_id').getValue(),
										CmpCallCardEventType_Code: 21 // Событие "Просмотр номера телефона вызова"
									}
								})
							}
							phoneField.triggerEl.elements[0].dom.classList.toggle('x-form-eye-open-trigger');
							input.setAttribute('type',val);
						}
					}

					Ext.Ajax.request({
						url: '/?c=CmpCallCard&m=getIsCallControllFlag',
						callback: function (opt, success, response) {
							var responseObj = Ext.decode(response.responseText);
							if(responseObj.length > 0){
								responseObj = responseObj[0];
							}
							if(responseObj.SmpUnitParam_IsCallControll != 'true'){
								Ext.ComponentQuery.query('[refId=IsCallControllTab]')[0].tab.hide();
							}
						}
					});

					cntr.regionalCrutches();
				},
				show: function(cmp){
					cntr.showSettingsWin();
				},
				afterrender: function(cmp){
					cntr.setHotKeys();
					cntr.callsGridLoadMask = new Ext.LoadMask(cntr.getRightSideContainerHD().getEl(),{msg:"Загрузка..."});
					cntr.callsGridSaveMask = new Ext.LoadMask(cntr.getRightSideContainerHD().getEl(),{msg:"Сохранение изменений..."});
					cntr.teamsGridLoadMask = new Ext.LoadMask(cntr.getLeftSideContainerHD().getEl(),{msg:"Загрузка..."});
					
					cntr.getCallsGridHD().getView().focus();
				}
				
			},
			'swHeadDoctorWorkPlace grid[refId=callsGridHD]': {
				itemclick: function(grid, rec, eOpts ){
                    //поменял на itemclick тк вызов контекстного меню не должен открывать детальную информацию о вызове
                    var callsPanel = cntr.getCallsGridHD();

                    cntr.showDetailCmpCallCardPanel(rec);
                    //if(rec.get('UnAdress_lat') && rec.get('UnAdress_lng')){
                        callsPanel.down('button[refId=showCardIntoTheMap]').setDisabled(!Boolean(rec.get('UnAdress_lat') && rec.get('UnAdress_lng')));
                    //}
                    //для групп закрытых кнопка редактировать доступна
                    callsPanel.down('button[refId=closeCmpCloseCard]').setDisabled( !rec.get('CmpGroup_id').inlist([2,3,4,5]) );
                    callsPanel.down('button[refId=editCmpCloseCard]').setDisabled( !rec.get('CmpGroup_id').inlist([5,6]) || !rec.get('CmpCloseCard_id') > 0 );
                    callsPanel.down('button[refId=rejectCall]').setDisabled( !(rec.get('CmpCallCardStatusType_Code').inlist([1,12,10]) && !rec.get('CmpCallType_Code').inlist([14,17])) );
                    callsPanel.down('button[refId=backToWork]').setDisabled( !rec.get('CmpCallCardStatusType_Code').inlist([5]) );

				},			
				beforeselect: function(){
					if(cntr.getCallDetailHD().isLoading){
						return false;
					}
				},
				viewready: function(cmp){
					cntr.getLeftSideContainerHD().getEl().addCls('focused-panel');
					cmp.getView().focus(false, 1000);
					//console.log('viewready', cmp.getView(), plug);
					var grFeature = cntr.getCallsGridHD().getView().getFeature(cntr.getWinHD().id+'_GroupingGridHDFeature');
					if (grFeature) {
						grFeature.collapse(3);
						grFeature.collapse(4);
						grFeature.collapse(5);
						grFeature.collapse(6);
						grFeature.collapse(7);
					}
				},
				itemcontextmenu: function(grid, record, item, index, event, eOpts){
					var cntr = this,
						callsView = cntr.getCallsGridHD(),
						teamsView = cntr.getTeamsGridHD();
						//callsView = win.down('grid[refId=callsGridHD]');

                    event.stopEvent();
					event.preventDefault();
					event.stopPropagation();

					cntr.showCmpCallCardSubMenu(event.getX(), event.getY(), callsView, teamsView);
				},
				cellkeydown: function(cmp, td, cellIndex, rec, tr, rowIndex, e, eOpts){

					var callsPanel = cntr.getCallsGridHD();

					switch(e.getKey()){
						case 13: {
							cntr.showDetailCmpCallCardPanel(rec);
							//if(rec.get('UnAdress_lat') && rec.get('UnAdress_lng')){
							callsPanel.down('button[refId=showCardIntoTheMap]').setDisabled(!Boolean(rec.get('UnAdress_lat') && rec.get('UnAdress_lng')));
							//}
							//для групп закрытых кнопка редактировать доступна
							callsPanel.down('button[refId=closeCmpCloseCard]').setDisabled( !rec.get('CmpGroup_id').inlist([2,3,4]) );
							callsPanel.down('button[refId=editCmpCloseCard]').setDisabled( !rec.get('CmpGroup_id').inlist([5,6]) || !rec.get('CmpCloseCard_id') > 0 );
							callsPanel.down('button[refId=rejectCall]').setDisabled( !(rec.get('CmpCallCardStatusType_Code').inlist([1,12,10]) && !rec.get('CmpCallType_Code').inlist([14,17])) );
							callsPanel.down('button[refId=backToWork]').setDisabled( !rec.get('CmpCallCardStatusType_Code').inlist([5]) );

							break;
						}
					}
				},
			},
			'swHeadDoctorWorkPlace grid[refId=teamsGridHD]': {
				itemclick: function( grid, record, item, index, e, eOpts ){
					cntr.showDetailCmpEmergencyTeamPanel(record);
					cntr.checkHaveCmpEmergencyTeamOnMap(record);
				},
				selectionchange: function(grid, selection, eOpts ){					
					if(cntr.getTeamDetailHD().isVisible()){
						cntr.showDetailCmpEmergencyTeamPanel(selection[0]);
					}
				},
				cellkeydown: function(grid, td, cellIndex, record, tr, rowIndex, e, eOpts){
					if(Ext.EventObject.getKey(eOpts) == 13){
						cntr.showDetailCmpEmergencyTeamPanel(record);
					}
				},
				beforeselect: function(record, item){
					cntr.checkHaveCmpEmergencyTeamOnMap(item);
					if(cntr.getTeamDetailHD().isLoading){
						cntr.checkHaveCmpEmergencyTeamOnMap(record);
						return false;
					}
				}
			},
			'swHeadDoctorWorkPlace tabpanel[refId=callDetailParentHD] tab' :{
				click: function(tab){
					var callDetailPanel = cntr.getCallDetailHD().getForm(),
						card_id = callDetailPanel.findField('CmpCallCard_id').getValue(),
						card_rid = callDetailPanel.findField('CmpCallCard_rid').getValue();
					
					if(tab.card.id == cntr.getWinHD().id+'_callDetailFirstHDForm'){
						cntr.loadCmpCardDetail(card_rid, function(){}, true);
					}
				}
			},
			'swHeadDoctorWorkPlace tabpanel[refId=callDetailParentHD]' :{
				tabchange: function(tab, panel) {
					panel.doLayout();
				}
			},
			'swHeadDoctorWorkPlace panel[refId=callDetailHD]': {
				show: function(me){}
			},
			
			'swHeadDoctorWorkPlace panel[refId=callDetailHD] field':{
				change: function(){
					var callDetailPanel = cntr.getCallDetailHD(),
						callDetailForm = callDetailPanel.getForm();
					//проверка на валидацию формы, выставление активного статуса кнопке при успешной валидации
					callDetailPanel.down('button[refId=saveBtn]').setDisabled(!callDetailForm.isValid());
					callDetailPanel.down('button[refId=saveBtnAccept]').setDisabled(!callDetailForm.isValid());
					callDetailPanel.down('button[refId=cancelmodeAcceptBtn]').setDisabled(!callDetailForm.isValid());
					callDetailPanel.down('button[refId=cancelmodeDiscardBtn]').setDisabled(!callDetailForm.isValid());
					callDetailPanel.down('button[refId=doublemodeAcceptBtn]').setDisabled(!callDetailForm.isValid());
					callDetailPanel.down('button[refId=doublemodeDiscardBtn]').setDisabled(!callDetailForm.isValid());
					callDetailPanel.down('button[refId=spteammodeAcceptBtn]').setDisabled(!callDetailForm.isValid());
					callDetailPanel.down('button[refId=spteammodeDiscardBtn]').setDisabled(!callDetailForm.isValid());
					callDetailPanel.down('button[refId=denyAcceptBtn]').setDisabled(!callDetailForm.isValid());
					callDetailPanel.down('button[refId=denyDiscardBtn]').setDisabled(!callDetailForm.isValid());
					callDetailPanel.down('button[refId=hdObservmodeAcceptBtn]').setDisabled(!callDetailForm.isValid());
				}
			},

			'swHeadDoctorWorkPlace panel[refId=callDetailHD] combobox[name=ageUnit_id]':{
				select: function(cmp){
					cntr.setPersonAgeFields(cmp);
				}
			},
			'swHeadDoctorWorkPlace panel[refId=callDetailHD] field[name=Person_Age]':{
				blur: function(cmp){
					cntr.setPersonAgeFields(cmp);
				}
			},
			//
			
			//кнопки Если Тип вызова «Отмена вызова»
			'swHeadDoctorWorkPlace panel[refId=callDetailHD] button[refId=cancelmodeAcceptBtn]': {
				click: function(){
					var callDetailPanel = cntr.getCallDetailHD(),
						callDetailForm = callDetailPanel.getForm(),
						CallTypeFieldVal = callDetailForm.findField('CmpCallType_id').getValue(),
						CallCardFieldVal = callDetailForm.findField('CmpCallCard_id').getValue(),
						CallCardRidFieldVal = callDetailForm.findField('CmpCallCard_rid').getValue(),
						EmergencyTeamFieldVal = callDetailForm.findField('EmergencyTeam_id').getValue(),
						parentEmergencyTeamFieldVal = callDetailForm.findField('pcEmergencyTeam_id').getValue(),						
						parentEmergencyTeamStatusCodeFieldVal = callDetailForm.findField('pcEmergencyTeamStatus_Code').getValue(),						
						EmergencyTeamStatusRec = callDetailForm.findField('EmergencyTeamStatus_Code').getValue(),
						win = cntr.getWinHD();

					cntr.saveCmpCallCardDetail(function(success){

						if(!success) return;


						Ext.Ajax.request({
							url: '/?c=CmpCallCard4E&m=setStatusCmpCallCardByHD',
							params: {
								CmpCallCard_id:	CallCardFieldVal,
								callType: 'cancel',
								action: 'accept'
							},
							success: function(response, opts){
								Ext.ComponentQuery.query('window[cls=waitingForAnswerHeadDoctorWindow]')[0].close();
								cntr.setDefaultViewPanels();
								cntr.reloadStores();

								// оповещенеие NodeJS
								if(win.socket && win.socket.connected && parentEmergencyTeamFieldVal){
									// parentEmergencyTeamFieldVal - назаначенная бригада на первичный вызов
									var cmpCallCardParam = {
										CmpCallCard_id: CallCardRidFieldVal,
										EmergencyTeam_id: parentEmergencyTeamFieldVal,
										Comment: callDetailForm.findField('CmpCallCard_Comm').getValue()
									}
									win.socket.emit('registrationFailure', cmpCallCardParam, function(data){
										log('node emit registrationFailure : apk='+data);
									});
									// win.socket.on('registrationFailure', function (data) {
									// 	log('node on registrationFailure');
									// })
								}
							}
						});


					});
				}
				
			},
			'swHeadDoctorWorkPlace panel[refId=callDetailHD] button[refId=cancelmodeDiscardBtn]': {
				click: function(){
					var callDetailPanel = cntr.getCallDetailHD(),
						callDetailForm = callDetailPanel.getForm(),
						callTypeField = callDetailForm.findField('CmpCallType_id'),
						// CallTypeFieldVal = callDetailForm.findField('CmpCallType_id').getValue(),
						CallCardFieldVal = callDetailForm.findField('CmpCallCard_id').getValue(),
						CallCardRidFieldVal = callDetailForm.findField('CmpCallCard_rid').getValue();
					
					//отменяющий вызов
					//Тип обращения отменяющего вызова меняется с «Отмена вызова» на «Дублирующее»;
					//Статус отменяющего вызова меняется с «Решение старшего врача» на «Дубль»;
					//var CmpCallType_rec = callTypeField.getStore().findRecord('CmpCallType_Code',14).get('CmpCallType_id');

					cntr.saveCmpCallCardDetail(function(success){
						if(!success) return;

						cntr.callsGridSaveMask.show();

						Ext.Ajax.request({
							url: '/?c=CmpCallCard4E&m=setStatusCmpCallCardByHD',
							params: {
								CmpCallCard_id:	CallCardFieldVal,
								callType: 'cancel',
								action: 'discard'
							},
							success: function (response, opts) {
								Ext.ComponentQuery.query('window[cls=waitingForAnswerHeadDoctorWindow]')[0].close();
								cntr.setDefaultViewPanels();
								cntr.reloadStores();
                                cntr.callsGridSaveMask.hide();
							},
							failure: function(response, opts){
								cntr.callsGridSaveMask.hide();
								Ext.MessageBox.show({
									title:'Ошибка',
									msg:'При отклонении вызова произошла ошибка.',
									icon: Ext.MessageBox.ERROR,
									buttons:Ext.MessageBox.OK
								});
							}
						});

					});
				}
			},
			
			//
			
			//кнопки Если Тип вызова «Дублирующие»
			'swHeadDoctorWorkPlace panel[refId=callDetailHD] button[refId=doublemodeAcceptBtn]': {
				click: function(){
					
					var callDetailPanel = cntr.getCallDetailHD(),
						callDetailForm = callDetailPanel.getForm(),
						CallCardFieldVal = callDetailForm.findField('CmpCallCard_id').getValue(),	
						CmpCallCard_rid = callDetailForm.findField('CmpCallCard_rid').getValue(),
						CmpCallCard_IsDeterior = callDetailForm.findField('CmpCallCard_IsDeterior').getValue(),
						pcEmergencyTeam_id = callDetailForm.findField('pcEmergencyTeam_id').getValue(),
						cmpReason = callDetailForm.findField('CmpReason_id').getRawValue(),
						CmpCallCard_Numv = callDetailForm.findField('CmpCallCard_Numv').getValue(),
						Person_SurName = callDetailForm.findField('Person_SurName').getValue(),
						Person_FirName = callDetailForm.findField('Person_FirName').getValue(),
						Person_SecName = callDetailForm.findField('Person_SecName').getValue(),
						CmpCallCard_IsExtra = callDetailForm.findField('CmpCallCard_IsExtra').getValue(),
						win = this.getWinHD();
					cntr.saveCmpCallCardDetail(function(success){
						if(!success) return;

						cntr.callsGridSaveMask.show();
						Ext.Ajax.request({
							url: '/?c=CmpCallCard4E&m=setStatusCmpCallCardByHD',
							params: {
								CmpCallCard_id:	CallCardFieldVal,
								callType: 'double',
								action: 'accept',
								CmpCallCard_IsDeterior: CmpCallCard_IsDeterior
							},
							success: function(response, opts){

								Ext.Ajax.request({
									url: '/?c=CmpCallCard&m=setCmpCallCardEvent',
									params: {
										CmpCallCard_id: CmpCallCard_rid,
										CmpCallCardEventType_Code: 23,
										CmpCallCardEvent_Comment: 'Cогласовано'
									},
									success: function(response, opts){
										cntr.callsGridSaveMask.hide();
										Ext.ComponentQuery.query('window[cls=waitingForAnswerHeadDoctorWindow]')[0].close();
										cntr.setDefaultViewPanels();
										cntr.reloadStores();
									}
								});

								if(CmpCallCard_IsDeterior == 2 && pcEmergencyTeam_id && win.socket && win.socket.connected){
									// если ухудшение состояния (CmpCallCard_IsDeterior=2)
									// pcEmergencyTeam_id - назаначенная бригада на первичный вызов
									var paramsi = {
										CmpCallCard_id:	CmpCallCard_rid,
										EmergencyTeam_id: pcEmergencyTeam_id,
										CmpCallCard_IsDeterior: CmpCallCard_IsDeterior,
										cmpReason: cmpReason,
										CmpCallCard_Numv: CmpCallCard_Numv,
										FIO: Person_SurName + ' ' + Person_FirName + ' ' + Person_SecName,
										isExtra: CmpCallCard_IsExtra
									};
									win.socket.emit('isDeterior', paramsi, function(data){
										log('NODE emit isDeterior : apk='+data);
									});
									// win.socket.on('isDeterior', function (data) {
									// 	log('nodeon isDeterior');
									// })
								}
							},
							failure: function(response, opts){
								cntr.callsGridSaveMask.hide();
								Ext.MessageBox.show({
									title:'Ошибка',
									msg:'При объединении дублирующего вызова произошла ошибка.',
									icon: Ext.MessageBox.ERROR,
									buttons:Ext.MessageBox.OK
								});
							}
						});
					});
					
				}
			},
			
			'swHeadDoctorWorkPlace panel[refId=callDetailHD] button[refId=doublemodeDiscardBtn]': {
				click: function(){
					
					var callDetailPanel = cntr.getCallDetailHD(),
						callDetailForm = callDetailPanel.getForm(),
						CallCardFieldVal = callDetailForm.findField('CmpCallCard_id').getValue(),
						CallCardRidFieldVal = callDetailForm.findField('CmpCallCard_rid').getValue();

					cntr.saveCmpCallCardDetail(function(success){

						if(!success) return;

						Ext.Ajax.request({
							url: '/?c=CmpCallCard4E&m=setStatusCmpCallCardByHD',
							params: {
								CmpCallCard_id:	CallCardFieldVal,
								callType: 'double',
								action: 'discard'
							},
							success: function(response, opts){

								Ext.Ajax.request({
									url: '/?c=CmpCallCard&m=setCmpCallCardEvent',
									params: {
										CmpCallCard_id: CallCardRidFieldVal,
										CmpCallCardEventType_Code: 23,
										CmpCallCardEvent_Comment: 'Не Cогласовано'
									},
									success: function(response, opts){
										Ext.ComponentQuery.query('window[cls=waitingForAnswerHeadDoctorWindow]')[0].close();
										cntr.setDefaultViewPanels();
										cntr.reloadStores();
									}
								});
							}
						});
					});
				}
			},
			
			//
			
						
			//кнопки Если Тип вызова «Для спец бр смп»
			'swHeadDoctorWorkPlace panel[refId=callDetailHD] button[refId=spteammodeAcceptBtn]': {
				click: function(){
					
					var callDetailPanel = cntr.getCallDetailHD(),
						callDetailForm = callDetailPanel.getForm(),
						CallCardFieldVal = callDetailForm.findField('CmpCallCard_id').getValue();

					cntr.saveCmpCallCardDetail(function(success){

						if(!success) return;

						Ext.Ajax.request({
							url: '/?c=CmpCallCard4E&m=setStatusCmpCallCardByHD',
							params: {
								CmpCallCard_id:	CallCardFieldVal,
								callType: 'specteam',
								action: 'accept'
							},
							success: function(response, opts){
								Ext.ComponentQuery.query('window[cls=waitingForAnswerHeadDoctorWindow]')[0].close();
								cntr.setDefaultViewPanels();
								cntr.reloadStores();
							}
						});
					});
				}
			},
			
			'swHeadDoctorWorkPlace panel[refId=callDetailHD] button[refId=spteammodeDiscardBtn]': {
				click: function(){
					
					var callDetailPanel = cntr.getCallDetailHD(),
						callDetailForm = callDetailPanel.getForm(),
						CallCardFieldVal = callDetailForm.findField('CmpCallCard_id').getValue();
					cntr.saveCmpCallCardDetail(function(success){

						if(!success) return;

						Ext.Ajax.request({
							url: '/?c=CmpCallCard4E&m=setStatusCmpCallCardByHD',
							params: {
								CmpCallCard_id:	CallCardFieldVal,
								callType: 'specteam',
								action: 'discard'
							},
							success: function(response, opts){
								Ext.ComponentQuery.query('window[cls=waitingForAnswerHeadDoctorWindow]')[0].close();
								cntr.setDefaultViewPanels();
								cntr.reloadStores();
							}
						});
					});
				}
			},
			
			//Кнопки для вызовов требующих решения Старшего врача
			'swHeadDoctorWorkPlace panel[refId=callDetailHD] button[refId=hdObservmodeAcceptBtn]': {
				click: function(){
					
					var callDetailPanel = cntr.getCallDetailHD(),
						callDetailForm = callDetailPanel.getForm(),
						CallTypeFieldVal = callDetailForm.findField('CmpCallType_id').getValue(),
						CallCardFieldVal = callDetailForm.findField('CmpCallCard_id').getValue();
					cntr.saveCmpCallCardDetail(function(success){

						if(!success) return;

						//Статус вызова меняется с «Решение старшего врача» на «Передано»
						var params = {
							CmpCallCardStatusType_id: 1,
							CmpCallCard_id:	CallCardFieldVal,
							CmpCallType_id: CallTypeFieldVal,
							CmpCallCardStatus_Comment: 'Старший врач ознакомлен',
							armtype: 'smpheaddoctor'
						};

						Ext.Ajax.request({
							url: '/?c=CmpCallCard4E&m=setStatusCmpCallCard',
							params: params,
							success: function(response, opts){
								Ext.ComponentQuery.query('window[cls=waitingForAnswerHeadDoctorWindow]')[0].close();
								cntr.setDefaultViewPanels();
								cntr.reloadStores();
							}
						});
					});
					
				}
			},

			//Кнопки для отлоненных вызовов
			'swHeadDoctorWorkPlace panel[refId=callDetailHD] button[refId=denyAcceptBtn]': {
				click: function(){

					var callDetailPanel = cntr.getCallDetailHD(),
						callDetailForm = callDetailPanel.getForm(),
						CallCardFieldVal = callDetailForm.findField('CmpCallCard_id').getValue();

					callDetailPanel.down('button[refId=saveBtn]').setVisible(true);
					callDetailPanel.down('button[refId=denyAcceptBtn]').setVisible(false);
					callDetailPanel.down('button[refId=denyDiscardBtn]').setVisible(false);
					callDetailForm.findField('LpuBuilding_id').clearValue();
					callDetailForm.findField('LpuBuilding_id').enable();
					callDetailForm.findField('LpuBuilding_id').setReadOnly(false);
					callDetailForm.findField('CmpCallCardStatusType_id').setValue(1);
					cntr.withoutChangeStatus = false;
					Ext.ComponentQuery.query('window[cls=waitingForAnswerHeadDoctorWindow]')[0].close();

					cntr.showYellowMsg('Укажите подразделение СМП', 2000);

				}
			},
			'swHeadDoctorWorkPlace panel[refId=callDetailHD] button[refId=denyDiscardBtn]': {
				click: function(){

					var callDetailPanel = cntr.getCallDetailHD(),
						callDetailForm = callDetailPanel.getForm(),
						CallCardFieldVal = callDetailForm.findField('CmpCallCard_id').getValue();

					cntr.saveCmpCallCardDetail(function(success){

						if(!success) return;

						//Статус вызова меняется с «Решение старшего врача» на «Передано»
						var params = {
							CmpCallCardStatusType_id: 1,
							CmpCallCard_id:	CallCardFieldVal,
							armtype: 'smpheaddoctor'
						};

						Ext.Ajax.request({
							url: '/?c=CmpCallCard4E&m=setStatusCmpCallCard',
							params: params,
							success: function(response, opts){
								Ext.ComponentQuery.query('window[cls=waitingForAnswerHeadDoctorWindow]')[0].close();
								cntr.setDefaultViewPanels();
								cntr.reloadStores();
							}
						});
					});

				}
			},

			//
			
			'swHeadDoctorWorkPlace panel[refId=callDetailHD] button[refId=saveBtn]': {
				click: function(){
					cntr.saveCmpCallCardDetail(function(success){

						if(!success) return;
						// после сохранения срабатывает проверка на изменение полей
						// зададим существующие значения в originalValue для полей, которые могут редактироваться (их три всего)
						var formBase = cntr.getCallDetailHD().getForm();
						var fieldAllowedToChange = ['LpuBuilding_id', 'Lpu_ppdid', 'CmpReason_id', 'CmpCallCard_Comm'];
						fieldAllowedToChange.forEach(function(elem, key, fieldAllowedToChange){
							formBase.findField(elem).originalValue = formBase.findField(elem).getValue();
						});
					});

				}
			},					
			'swHeadDoctorWorkPlace panel[refId=callDetailHD] button[refId=saveBtnAccept]': {
				click: function(){
					
					var callDetailPanel = cntr.getCallDetailHD(),
						callDetailForm = callDetailPanel.getForm(),
						CallCardFieldVal = callDetailForm.findField('CmpCallCard_id').getValue();
					
					//Сохраняем изменения

					cntr.saveCmpCallCardDetail(function(success) {

						if(!success) return;

						Ext.Ajax.request({
							url: '/?c=CmpCallCard4E&m=setStatusCmpCallCardByHD',
							params: {
								CmpCallCard_id:	CallCardFieldVal,
								callType: 'hdobserve',
								action: 'accept'
							},
							success: function(response, opts){
								var waitingForAnswerWindow = Ext.ComponentQuery.query('window[cls=waitingForAnswerHeadDoctorWindow]')[0];
								if (waitingForAnswerWindow) {
									waitingForAnswerWindow.close();
								}
								cntr.setDefaultViewPanels();
								cntr.reloadStores();

							}
						});
					});

				}
			},		
			'swHeadDoctorWorkPlace panel[refId=callDetailHD] button[refId=cancelBtn]': {
				click: function(){
					var saveBtn = cntr.getCallDetailHD().down('button[refId=saveBtn]');
					
					//если изменения на форме - выводим предупреждение о потерях введенных данных
					if(cntr.getCallDetailHD().getForm().isDirty() && !saveBtn.disabled){
						Ext.Msg.show({
							title:'Отмена сохранения',
							msg: 'Все изменения будут потеряны. Закрыть форму?',
							buttons: Ext.Msg.YESNO,
							icon: Ext.Msg.QUESTION,
							fn: function(btn, text){
								if (btn == 'yes'){cntr.setDefaultViewPanels();}
								else return;
							}
						});
					}
					else cntr.setDefaultViewPanels();
				}
			},
			'swHeadDoctorWorkPlace panel[refId=teamDetailHD] button[refId=cancelBtn]': {
				click: function(){
					cntr.setDefaultViewPanels();
				}
			},
			'fieldset[refId=callDetailHDcmpCallCardEmergencyResult]': {
				beforeexpand: function(fieldset){
					var callBaseForm = cntr.getCallDetailHD();
					fieldset.topOffsetForm = callBaseForm.body.el.getScrollTop();
				},
				expand: function(fieldset){
					var callBaseForm = cntr.getCallDetailHD();

					cntr.setEmergencyResultFields();
					callBaseForm.body.el.setScrollTop(fieldset.topOffsetForm);
				}
			},
			'swHeadDoctorWorkPlace combobox[name=dCityCombo]': {
				render: function (c, o)
				{
					/*var streetCombo = cntr.getCallDetailHD().getForm().findField('dStreetsCombo');
					
					c.store.getProxy().extraParams = {
						'region_id' : getGlobalOptions().region.number,
						'region_name' : getGlobalOptions().region.name,
						'city_default' : getGlobalOptions().region.number
					}

					c.store.load({
						callback: function(rec, operation, success) {
						if (this.getCount() == 1)
							{
								c.setValue(rec[0].get('Town_id'));								
								
								streetCombo.bigStore.getProxy().extraParams = {
									'town_id' : rec[0].get('Town_id'),
									'Lpu_id' : sw.Promed.MedStaffFactByUser.current.Lpu_id
								}
								streetCombo.bigStore.load();
							}
						}
					})*/
				},
				change: function (c, newValue, oldValue, eOpts)
				{
					if (newValue){
						if (newValue.toString().length > 0){
							c.store.getProxy().extraParams = {
							'city_default' : null,
							'region_id' : getGlobalOptions().region.number
							}
						}
					}			
				},
				select: function(cmp, recs){					
					var cityRec = recs[0],
						streetCombo = cntr.getCallDetailHD().getForm().findField('dStreetsCombo'),
						secondStreetCombo = cntr.getCallDetailHD().getForm().findField('secondStreetCombo');

					streetCombo.bigStore.getProxy().extraParams = {
						'town_id' : cityRec.get('Town_id'),
						'Lpu_id' : sw.Promed.MedStaffFactByUser.current.Lpu_id
					}
					streetCombo.bigStore.load({
						callback: function(recs){
							secondStreetCombo.bigStore.loadData(recs);
						}
					});
				}
			},
			
			'swHeadDoctorWorkPlace panel[refId=callDetailHD] combobox[name=CmpReason_id]': {
				select: function(cmp, recs){
					cntr.getCmpCallCardUrgencyAndProfile();
					/*
					var callDetailPanel = cntr.getCallDetailHD(),
						callDetailForm = callDetailPanel.getForm(),
						lpuBuildingField = callDetailForm.findField('LpuBuilding_id');
					*/
					//Поле П/С обязательное для заполнения (подсвечивается зеленым), если выбран любой повод, кроме повода «Консультация по телефону»
					//cntr.setSmpNmpFields();
				}
			},
			
			'swHeadDoctorWorkPlace panel[refId=callDetailHD] combobox[name=CmpCallCard_IsExtra]': {
				//вид вызова
				select: function(cmp, recs){					
					cntr.setSmpNmpFields();
				}
			},
			'swHeadDoctorWorkPlace grid[refId=callsGridHD] combo[refId=sortCalls]': {
				select: function(cmp, recs){
					var cntr = this;
					cntr.sortCalls = recs[0].data.mode;

					cntr.sortCmpCalls(recs[0].data.field);
				}
			},
			'swHeadDoctorWorkPlace grid[refId=callsGridHD] button[refId=showCardIntoTheMap]': {
				click: function(){
					cntr.showMapPanel(false, true);
				}
			},
			'swHeadDoctorWorkPlace grid[refId=callsGridHD] button[refId=closeCmpCloseCard]': {
				click: function(){
					cntr.showCmpCloseCardFromExt2('add');
				}
			},
            'swHeadDoctorWorkPlace grid[refId=callsGridHD] button[refId=createNewCall]': {
                click: function(){
					cntr.showWorkPlaceSMPDispatcherCallWindow(true);
                }
            },
			'swHeadDoctorWorkPlace grid[refId=callsGridHD] button[refId=editCmpCloseCard]': {
				click: function(){
					cntr.showCmpCloseCardFromExt2('edit');
				}
			},
			'swHeadDoctorWorkPlace panel[refId=panelCallsGridHD] button[refId=backToWork]': {
                click: function(){
                    cntr.showBackToWorkMsg();
                }
            },
            'swHeadDoctorWorkPlace grid[refId=callsGridHD] button[refId=rejectCall]': {
				click: function(){
					cntr.showSelectReasonRejectSmpCallCard();
				}
			},
			'swHeadDoctorWorkPlace button[refId=mapBtnHD]':{
				click: function(){
					cntr.showMapPanel(false, false);
				}
			},
			'swHeadDoctorWorkPlace button[refId=statisticReports]':{
				click: function(){

					var wind = Ext.getCmp('myFFFrame');

					if(wind){
						wind.show();
						return;
					}

					new Ext.Window({
						id: "myFFFrame",
						title: 'Отчеты',
						header: false,
						extend: 'sw.standartToolsWindow',
						toFrontOnShow: true,
						style: {
							'z-index': 90000
						},
						layout: {
							type: 'fit',
							align: 'stretch'
						},
						maximized: true,
						constrain: true,
						renderTo: Ext.getCmp('inPanel').body,
						items : [{
							xtype : "component",
							autoEl : {
								tag : "iframe",
								src : "/?c=promed&getwnd=swReportEndUserWindow&showTop=1"
							}
						}]
					}).show();
				}
			},
			'swHeadDoctorWorkPlace button[refId=aktivSmp]':{
				click: function(){
					Ext.create('sw.tools.swAktivJournal').show();
				}
			},
			'swHeadDoctorWorkPlace button[refId=setEmergencyTeamDutyTime]':{
				click: function(){
					var EmergencyTeamTemplateSetDuty = Ext.create('common.DispatcherStationWP.tools.swEmergencyTeamTemplateSetDuty',
						{
							layout: {
								type: 'fit',
								align: 'stretch'
							},
							maximized: true,
							constrain: true,
							armType: 'smpheaddoctor',
							renderTo: Ext.getCmp('inPanel').body
						});
					EmergencyTeamTemplateSetDuty.show();
				}
			},
			'swHeadDoctorWorkPlace button[refId=closeMapWin]':{
				click: function(){
					cntr.setDefaultViewPanels();
				}
			},
			'swHeadDoctorWorkPlace button[refId=showInMapBtnHD]':{
				click: function(){
					cntr.showMapPanel(true, false);
				}
			},
			'swHeadDoctorWorkPlace button[refId=printEmergencyGridBtnHD]':{
				click: function(){
					Ext.ux.grid.Printer.print(cntr.getTeamsGridHD());
				}
			},
			'swHeadDoctorWorkPlace button[refId=showTrackBtn]': {
                click: function () {
                    cntr.playWialonTrack();
                }
            },
			'swHeadDoctorWorkPlace panel[refId=callDetailHD] checkbox[name=CmpCallCard_IsPoli]': {
				change: function(cmp, val, oldval, opts){

					var SmpField = cntr.getCallDetailHD().getForm().findField('LpuBuilding_id'),
						NmpField = cntr.getCallDetailHD().getForm().findField('Lpu_ppdid'),
						NmpStore = cntr.getCallDetailHD().getForm().findField('Lpu_ppdid').getStore();
					// флаг не установлен – список содержит МО региона, открытые на дату приёма вызова и имеющие открытые службы (на дату приёма вызова) с типом «Служба неотложной медицинской помощи»;
					// флаг установлен – список содержит МО региона, открытые на дату приёма вызова;
					if(val){
						cntr.baseForm.findField('CmpCallCard_IsPassSSMP').setValue(false);
						cntr.baseForm.findField('Lpu_smpid').clearValue();
						if(SmpField) SmpField.clearValue()
					} else {
						cntr.baseForm.findField('Lpu_ppdid').clearValue();
						cntr.baseForm.findField('MedService_id').clearValue();
					}

					NmpStore.proxy.extraParams = val ? null : {'Object': 'LpuWithMedServ','MedServiceType_id': 18};
					//if(!val){
					//	NmpField.clearValue()
					//}
					NmpStore.reload();
					cntr.setSmpNmpFields();

				}
			},
			'swHeadDoctorWorkPlace panel[refId=callDetailHD] combobox[name=Lpu_ppdid]': {
				select: function(cmp, recs){
					var NmpRec = recs[0],
						NmpService = cntr.getCallDetailHD().getForm().findField('MedService_id'),
						NmpServiceStore = cntr.getCallDetailHD().getForm().findField('MedService_id').getStore(),
						LpuBuilding_id = cntr.getCallDetailHD().getForm().findField('LpuBuilding_id'),
						CmpCallCard_IsPassSSMP = cntr.getCallDetailHD().getForm().findField('CmpCallCard_IsPassSSMP');

					//грузим службы с типом «Служба неотложной медицинской помощи», относящихся к МО, указанной в поле «МО передачи (НМП)».
					NmpServiceStore.proxy.extraParams = NmpRec.get('Lpu_id') ? {'Lpu_ppdid': NmpRec.get('Lpu_id')} : null;
					if(NmpRec.get('Lpu_id')){
						NmpService.clearValue();
					}
					NmpServiceStore.reload();

					LpuBuilding_id.clearValue();

					cntr.setSmpNmpFields();
					
					CmpCallCard_IsPassSSMP.setValue(false);
				},
				change: function(cmp, newV, oldV, opts){
                    //из-за 2 строк ниже поле подразделение очищается при загрузке, а должно быть заполнено
					//cntr.getCallDetailHD().getForm().findField('LpuBuilding_id').clearValue();
					//cntr.getCallDetailHD().getForm().findField('CmpCallCard_IsPassSSMP').setValue(false);
				}
			},
			'swHeadDoctorWorkPlace panel[refId=callDetailHD] combobox[name=LpuBuilding_id]': {
				select: function(cmp, recs){
					cntr.getCallDetailHD().getForm().findField('Lpu_ppdid').clearValue();
					cntr.getCallDetailHD().getForm().findField('MedService_id').clearValue();
					cntr.setSmpNmpFields();
				}
			},
			'swHeadDoctorWorkPlace panel[refId=callDetailHD] checkbox[name=CmpCallCard_IsPassSSMP]': {
				change: function(cmp, val,oldval){
					if(val){
						cntr.baseForm.findField('CmpCallCard_IsPoli').setValue(false);
						cntr.baseForm.findField('Lpu_ppdid').clearValue();
						cntr.baseForm.findField('MedService_id').clearValue();
					}
					cntr.setSmpNmpFields();
				}
			},
			'swHeadDoctorWorkPlace panel[refId=callDetailHD] combobox[name=secondStreetCombo]': {
				keyup: function(c, e, o){
					cntr.checkCrossRoadsFields(true, e);
				},
				blur: function(){
					cntr.checkCrossRoadsFields();
				}
			},
			'swHeadDoctorWorkPlace panel[refId=callDetailHD] textfield[name=CmpCallCard_Dom]': {
				keyup: function(c, e, o){
					cntr.checkCrossRoadsFields(true, e);
				}
			},
			'swHeadDoctorWorkPlace panel[refId=callDetailFirstHD] checkbox[name=CmpCallCard_IsPassSSMP]': {
				change: function(cmp, val){
					cntr.getCallDetailFirstHD().getForm().findField('Lpu_smpid').setVisible(val)
				}
			},
			'swHeadDoctorWorkPlace button[refId=hr-splitter]': {
				click: function(cmp){
					var leftContainer = cntr.getLeftSideContainerHD(),
						callDetailParentPanel = cntr.getCallDetailParentHD(),
						rightContainer = cntr.getRightSideContainerHD(),
						buttonEl = cmp.getEl();

					buttonEl.toggleCls('left-splitter');

					if (buttonEl.hasCls('left-splitter')){
						cmp.setIconCls('left-splitter');
						Ext.apply(leftContainer, {flex: 2});
						Ext.apply(rightContainer, {flex: 1});
						Ext.apply(callDetailParentPanel, {flex: 1});
					}
					else{
						cmp.setIconCls('right-splitter');
						Ext.apply(leftContainer, {flex: 1});
						Ext.apply(rightContainer, {flex: 1});
						Ext.apply(callDetailParentPanel, {flex: 1});
					}
					leftContainer.up('container').doLayout();
					callDetailParentPanel.up('container').doLayout();
					callDetailParentPanel.doLayout();
				}
			}
		})
    },
	
	//региональные костыли
	regionalCrutches: function(){
		var cntr = this,
			region = getGlobalOptions().region.nick,
			bf = cntr.getCallDetailHD().getForm(),
			prmDateField = bf.findField('CmpCallCard_prmDate'),
			numDayField = bf.findField('CmpCallCard_Numv'),
			prmTimeField = bf.findField('CmpCallCard_prmTime'),
			numYearField = bf.findField('CmpCallCard_Ngod'),
			mainToolbar = Ext.getCmp('Mainviewport_Toolbar'),
			settingsBtn = mainToolbar.down('button[refId=settingsBtn]');
			
		//пока не известно каким регионам показывать, а каким скрыть, но чую будут такой момент

		settingsBtn.show();

		//settingsBtn.on('click', function(){cntr.showSettingsWin()});

		if (Ext.Array.contains(['ufa','kz','krym'], region)) {
            numYearField && numYearField.hide();
        }
	},
	
	showSettingsWin: function(){
		openSelectSMPStationsToControlWindow();
	},
	
	toggleRightPanels: function(showDetail){
		var cntr = this,
			callDetailPanel = cntr.getCallDetailHD(),
			callDetailParentPanel = cntr.getCallDetailParentHD(),
			teamsPanel = cntr.getTeamsGridHD();
		
		callDetailPanel.setVisible(showDetail);
		callDetailParentPanel.setVisible(showDetail);
		teamsPanel.setVisible(!showDetail);
		if(showDetail){}
	},	
	
	toggleLeftPanels: function(showDetail){
		var cntr = this,
			teamDetailPanel = cntr.getTeamDetailHD(),
			callsPanel = cntr.getCallsGridHD();

		teamDetailPanel.setVisible(showDetail);
		callsPanel.setVisible(!showDetail);
		if(showDetail){}
	},
	
	setDefaultViewPanels: function(){
		var cntr = this,
			callsGrid = cntr.getCallsGridHD(),
			teamsGrid = cntr.getTeamsGridHD(),
			leftContainer = cntr.getLeftSideContainerHD(),
			callDetailParentPanel = cntr.getCallDetailParentHD(),
			rightContainer = cntr.getRightSideContainerHD(),
			waitingForAnswerHeadDoctorWindow = Ext.ComponentQuery.query('window[cls=waitingForAnswerHeadDoctorWindow]')[0];
			
		//cntr.getCallDetailHD().setVisible(false);
		callDetailParentPanel.setVisible(false);
		cntr.getTeamDetailHD().setVisible(false);
		cntr.getMapPanelHD().setVisible(false);
		teamsGrid.setVisible(true);
		callsGrid.setVisible(true);
		cntr.getLeftSideContainerHD().setVisible(true);
		cntr.getRightSideContainerHD().setVisible(true);
		
		if(waitingForAnswerHeadDoctorWindow && waitingForAnswerHeadDoctorWindow.isVisible())waitingForAnswerHeadDoctorWindow.close();
		
		if (leftContainer.hasCls('focused-panel')){	
			callsGrid.getView().focus();
		}
		if (rightContainer.hasCls('focused-panel')){
			teamsGrid.getView().focus();
		}
	},
	
	showCmpCloseCardFromExt2: function(mode){
		var cntr = this,
			callRec = cntr.getCallsGridHD().getSelectionModel().getSelection()[0],
			path = "/?c=promed&getwnd=swCmpCallCardNewCloseCardWindow&act=" + mode + "&showTop=1&cccid="+callRec.get('CmpCallCard_id');

		if(!Ext.ComponentQuery.query('[refId=CmpCloseCard]')[0] || !Ext.ComponentQuery.query('[refId=CmpCloseCard]')[0].isVisible()) {
			new Ext.Window({
				title: "",
				id: "myFFFrame",
				refId: 'CmpCloseCard',
				modal: true,
				toFrontOnShow: true,
				width: '100%',
				style: {
					'z-index': 90000
				},
				height: '90%',
				layout: 'fit',
				items: [{
					xtype: "component",
					autoEl: {
						tag: "iframe",
						src: path
					}
				}],
				listeners: {
					'close': function () {
						cntr.reloadStores();
					}
				}
			}).show();
		} else {
			Ext.Msg.alert('Ошибка', 'Карта вызова уже открыта')
		}
	},




	showSelectReasonRejectSmpCallCard: function(){
		var cntr = this,
			simplifyRejectSmpCallCardWindow = Ext.create('sw.tools.swSimplifyRejectSmpCallCardWindow'),
			recCardInSelection = cntr.getCallsGridHD().getSelectionModel().getSelection()[0];

		simplifyRejectSmpCallCardWindow.show({
			params: recCardInSelection.data,
			armtype:'smpheaddoctor'
		});

		simplifyRejectSmpCallCardWindow.on('saveRejectReason', function(data) {
			callRec = recCardInSelection ? cntr.getCallsGridHD().getStore().findRecord('CmpCallCard_id', recCardInSelection.get('CmpCallCard_id')) : null;
			if( !callRec.data ) return;
			var recCmp = callRec.data,
				CmpCallCard_id = recCmp.CmpCallCard_id,
				Person_FIO = recCmp.Person_FIO,
				mask = new Ext.LoadMask(simplifyRejectSmpCallCardWindow.getEl(),{msg:"Передача карты вызова в другое МО..."});

			if( !simplifyRejectSmpCallCardWindow.isVisible() ) return;
			var baseForm = cntr.baseForm;
			if ( !data.CmpRejectionReason_id && !recCmp.CmpCallCard_id ) {
				return false;
			}
			var saveRejectionReason = function() {
				Ext.Ajax.request({
					url: '/?c=CmpCallCard4E&m=setStatusCmpCallCard',
					params: {
						CmpCallCardStatusType_id:5, //Отказ
						CmpCallCard_id:	CmpCallCard_id,
						CmpRejectionReason_id: data.CmpRejectionReason_id,
						CmpRejectionReason_Name: data.CmpRejectionReason_Name,
						CmpCallCardStatus_Comment: data.CmpCallCardStatus_Comment||null
					},
					success: function(response, opts){
						var obj = Ext.decode(response.responseText);
						if ( obj.success ) {
							Ext.Msg.alert('Информация','Вызов на пациента '+Person_FIO+' успешно отменен', function(){
							});
							cntr.reloadStores();
						} else {
							Ext.Msg.alert('Ошибка','Во время создания талона отказа произошла ошибка, обратитесь к администратору');
						}
					},
					failure: function(response, opts){
						Ext.Msg.alert('Ошибка','Во время создания талона отказа произошла ошибка, обратитесь к администратору');
					}
				});
				simplifyRejectSmpCallCardWindow.close();
			}

			if(data.lpu_id) {
				mask.show();
				Ext.Ajax.request({
					url: '/?c=CmpCallCard4E&m=copyCmpCallCardToLpu',
					params: {
						CmpCallCard_id: CmpCallCard_id,
						Lpu_did: data.lpu_id,
						LpuBuilding_did: data.lpuBuilding_id,
						LpuDid_Nick: data.lpu_nick
					},
					success: function(response,opt) {
						mask.hide();
						var obj = Ext.decode(response.responseText);
						if ( obj[0] && obj[0]['CmpCallCard_id'] ) {
							saveRejectionReason();
						} else {
							Ext.Msg.alert('Ошибка','Во время передачи карты вызова в другое МО');
						}
					},
					failure: function(response, opt) {
						mask.hide();
						Ext.Msg.alert('Ошибка','Во время передачи карты вызова в другое МО');
					}
				})
			} else {
				saveRejectionReason();
			}
		});

	},

    showBackToWorkMsg: function(){

        var cntr = this;
        var recCard = cntr.getCallsGridHD().getSelectionModel().getSelection()[0];
        var operDeptOptions = cntr.getOperDeptOptions();

        Ext.Msg.show({
            title:'Вернуть в работу',
            msg: 'Вы действительно хотите вернуть вызов в работу?',
            buttons: Ext.Msg.YESNO,
            icon: Ext.Msg.QUESTION,
            fn: function(btn, text){
                if (btn == 'yes'){
                    var CmpReason_Code = recCard.get('CmpReason_Code'),
                        HeadDoctorObservReason = recCard.get('HeadDoctorObservReason'),
                        CmpGroup_id = recCard.get('CmpGroup_id'),
                        CmpCallType_Code = recCard.get('CmpCallType_Code'),
                        CmpCallCardStatusType_id = 1;


                    if(
                        CmpReason_Code == '999'
                        || ( operDeptOptions && operDeptOptions.LpuBuilding_IsCallReason == 'true' && HeadDoctorObservReason )
                        || ( operDeptOptions && operDeptOptions.LpuBuilding_IsCallSpecTeam == 'true' && CmpCallType_Code == 9 )
                    ){
                        //Если Повод вызова «Решение старшего врача», то статус вызова меняется на «Решение старшего врача»
                        //Если Повод вызова имеет признак «Требуется наблюдение старшим врачом»
                        //Если Тип вызова «Для спец. бр. СМП» ...<бла-бла-бла>...требующими решения старшего врача
                        CmpCallCardStatusType_id = 18;
                    }

                    Ext.Ajax.request({
                        url: '/?c=CmpCallCard4E&m=setStatusCmpCallCard',
                        params: {
                            CmpCallCardStatusType_id: 1, // Статус передано
                            CmpCallCard_id: recCard.get('CmpCallCard_id'),
                            CmpCallType_id: recCard.get('CmpCallType_id'),
                            armtype: 'smpdispatcherstation'
                        },
                        success: function(response, opts){
                            cntr.setDefaultViewPanels();
                            cntr.reloadStores();
                        }
                    });
                }
                else return;
            }
        });
    },

	showDetailCmpCallCardPanel: function(record){
		if(!record) return false;
		var cntr = this,
			callDetailPanel = cntr.getCallDetailHD(),
			callDetailForm = callDetailPanel.getForm(),
			cmpReasonField = callDetailForm.findField('CmpReason_id');

		if(record.get('CmpCallCard_id') && record.get('CmpCallCard_id')!=0)
		{
			cntr.callsGridLoadMask.show();
			cntr.loadCmpCardDetail(record.get('CmpCallCard_id'), function(success, request){
				cntr.toggleRightPanels(success);
				cntr.callsGridLoadMask.hide();
				cntr.setDisabledCmpCardDetailFields(request);

				var waitingForAnswerHeadDoctorWindow = Ext.ComponentQuery.query('window[cls=waitingForAnswerHeadDoctorWindow]')[0];
				if(waitingForAnswerHeadDoctorWindow && waitingForAnswerHeadDoctorWindow.isVisible())waitingForAnswerHeadDoctorWindow.close();

				var headDoctorDecision = (record.get('CmpGroup_id') == 1)?true:false;
				//Для вызовов в статусе «Решение старшего врача»

				//скрываем все функциональные кнопки для старшего врача, потом откроем нужные
				callDetailPanel.down('button[refId=cancelmodeAcceptBtn]').setVisible(false);
				callDetailPanel.down('button[refId=cancelmodeDiscardBtn]').setVisible(false);
				callDetailPanel.down('button[refId=doublemodeAcceptBtn]').setVisible(false);
				callDetailPanel.down('button[refId=doublemodeDiscardBtn]').setVisible(false);
				callDetailPanel.down('button[refId=spteammodeAcceptBtn]').setVisible(false);
				callDetailPanel.down('button[refId=spteammodeDiscardBtn]').setVisible(false);
				callDetailPanel.down('button[refId=denyAcceptBtn]').setVisible(false);
				callDetailPanel.down('button[refId=denyDiscardBtn]').setVisible(false);
				callDetailPanel.down('button[refId=hdObservmodeAcceptBtn]').setVisible(false);
				callDetailPanel.down('button[refId=saveBtnAccept]').setVisible(false);

				//возвращаем полю повод «Решение старшего врача» , потом скроем, если нужно будет
				cmpReasonField.getStore().findBy(function(rec){ if( rec.get('CmpReason_Code').inlist(['02?', '06?', '09?', '10?', '11?', '12?', '13?', '15?', '16?', '40?','999']) ){	rec.set('display', true); } });

				//Для вызовов НЕ в статусе «Решение старшего врача» кнопки сохранить и закрыть видны
				callDetailPanel.down('button[refId=saveBtn]').setVisible(!headDoctorDecision);

				//callDetailPanel.down('button[refId=cancelBtn]').setVisible(!headDoctorDecision);

				if(headDoctorDecision){
					cntr.showInWaitingForAnswerHeadDoctorModeView(record, request);
				}

				//отображение блока информации результат выезда
				var resultBlock = callDetailPanel.down('fieldset[refId=callDetailHDcmpCallCardEmergencyResult]');
				//if(record.get('CmpGroup_id') != 5){
				if(!record.get('CmpGroup_id').inlist([5,6])){
					resultBlock.hide();
				}else{
					resultBlock.show();
					resultBlock.collapse();
				}

			});
		}
	},
	
	setActiveFirstCallTab: function(CmpCallCard_rid){
		var cntr = this,
			callDetailParentPanel = cntr.getCallDetailParentHD();
			
		cntr.loadCmpCardDetail(CmpCallCard_rid, function(){}, true);
		callDetailParentPanel.setActiveTab(1);
	},

	showInWaitingForAnswerHeadDoctorModeView: function(rec, request){
		var cntr = this,
			rightContainerEl = cntr.getRightSideContainerHD().el,
			cmpCallCard_rid = rec.get('CmpCallCard_rid')||'',
            pcCmpCallCard_Numv = request ? request.pcCmpCallCard_Numv : '',
			callDetailPanel = cntr.getCallDetailHD(),
			callDetailForm = callDetailPanel.getForm(),
			callTypeField = callDetailForm.findField('CmpCallType_id'),
			cmpReasonField = callDetailForm.findField('CmpReason_id'),
			cmpCallTypeIsExtraCombo = callDetailForm.findField('CmpCallCard_IsExtra'),
			lpuNMPBuildingField = callDetailForm.findField('Lpu_ppdid'),
			lpuBuildingField = callDetailForm.findField('LpuBuilding_id'),
			logicRulesEmergencyTeamSpec_Code = callDetailForm.findField('LogicRulesEmergencyTeamSpec_Code');
			
		//Если Тип вызова «Отмена вызова»
		if(rec.get('CmpCallType_Code') == 17){
			
			//а другие кнопки не видны
			callDetailPanel.down('button[refId=cancelmodeAcceptBtn]').setVisible(true);
			callDetailPanel.down('button[refId=cancelmodeDiscardBtn]').setVisible(true);
			
			var alertBox = Ext.create('Ext.window.Window', {
				title: 'Hello',
				height: 50,
				width: 500,
				layout: 'fit',
				constrain: true,
				cls: 'waitingForAnswerHeadDoctorWindow',
				header: false,
				constrainTo: rightContainerEl,
				layout: {
					type: 'hbox',
					align: 'middle'
				},
				items: [
					{
						xtype: 'label',
						flex: 1,
						html: "Необходимо разрешить отмену вызова № <a href='#' style='color:black' onClick='globalApp.app.getController(\"SMP.swHeadDoctorWorkPlace_controller\").setActiveFirstCallTab("+cmpCallCard_rid+")'>"+pcCmpCallCard_Numv+"</a>"
					},
					{
						xtype: 'button',       
						text: 'Закрыть',
						renderTpl: [
							'<span id="{id}-btnWrap" class="{baseCls}-wrap closeBtn',
								'<tpl if="splitCls"> {splitCls}</tpl>',
								'{childElCls}" unselectable="on">',
								'<span id="{id}-btnEl" class="{baseCls}-button">',
									'X',
							'</span>'							
						],
						handler: function(){
							alertBox.close();
						}
					}
				]
			});
			
			alertBox.showAt([rightContainerEl.getWidth()/2+rightContainerEl.getLocalX()-250, rightContainerEl.getHeight()-20]);
			
			return;
		}
		
		//Если Тип вызова «Дублирующее»
		if(rec.get('CmpCallType_Code') == 14){
	
			callDetailPanel.down('button[refId=doublemodeAcceptBtn]').setVisible(true);
			callDetailPanel.down('button[refId=doublemodeDiscardBtn]').setVisible(true);
			
			var alertBox = Ext.create('Ext.window.Window', {
				title: 'Hello',
				height: 50,
				width: 500,
				layout: 'fit',
				constrain: true,
				cls: 'waitingForAnswerHeadDoctorWindow',
				header: false,
				constrainTo: rightContainerEl,
				layout: {
					type: 'hbox',
					align: 'middle'
				},
				items: [
					{
						xtype: 'label',
						flex: 1,
						html: "Данный вызов дублирует вызов № <a href='#' style='color:black' onClick='globalApp.app.getController(\"SMP.swHeadDoctorWorkPlace_controller\").setActiveFirstCallTab("+cmpCallCard_rid+")'>"+pcCmpCallCard_Numv+"</a>. Объединить?"
					},
					{
						xtype: 'button',       
						text: 'Закрыть',
						renderTpl: [
							'<span id="{id}-btnWrap" class="{baseCls}-wrap closeBtn',
								'<tpl if="splitCls"> {splitCls}</tpl>',
								'{childElCls}" unselectable="on">',
								'<span id="{id}-btnEl" class="{baseCls}-button">',
									'X',
							'</span>'							
						],
						handler: function(){
							alertBox.close();
						}
					}
				]
			});
			
			alertBox.showAt([rightContainerEl.getWidth()/2+rightContainerEl.getLocalX()-250, rightContainerEl.getHeight()-20]);
			return;
		}
		
		//Если Тип вызова «Для спец.бр.СМП»
		if(rec.get('CmpCallType_Code') == 9){

			callDetailForm.findField('LpuBuilding_id').clearValue();
			callDetailForm.findField('LpuBuilding_id').enable();
			callDetailForm.findField('LpuBuilding_id').setReadOnly(false);
			callDetailPanel.down('button[refId=spteammodeAcceptBtn]').setVisible(true);
			callDetailPanel.down('button[refId=spteammodeDiscardBtn]').setVisible(true);
			
			var alertBox = Ext.create('Ext.window.Window', {
				title: 'Hello',
				height: 80,
				width: 500,
				layout: 'fit',
				constrain: true,
				cls: 'waitingForAnswerHeadDoctorWindow',
				header: false,
				constrainTo: rightContainerEl,
				layout: {
					type: 'hbox',
					align: 'middle'
				},
				items: [
					{
						xtype: 'label',
						flex: 1,
						html: "<div>Требуется специализированная бригада с профилем "+logicRulesEmergencyTeamSpec_Code.getValue()+" на вызов, созданный в дополнение к первичному вызову № <a href='#' style='color:black' onClick='globalApp.app.getController(\"SMP.swHeadDoctorWorkPlace_controller\").setActiveFirstCallTab("+cmpCallCard_rid+")'>"+pcCmpCallCard_Numv+"</a>. Подтвердите?</div>"
					},
					{
						xtype: 'button',       
						text: 'Закрыть',
						renderTpl: [
							'<span id="{id}-btnWrap" class="{baseCls}-wrap closeBtn',
								'<tpl if="splitCls"> {splitCls}</tpl>',
								'{childElCls}" unselectable="on">',
								'<span id="{id}-btnEl" class="{baseCls}-button">',
									'X',
							'</span>'							
						],
						handler: function(){
							alertBox.close();
						}
					}
				]
			});
			
			alertBox.showAt([rightContainerEl.getWidth()/2+rightContainerEl.getLocalX()-250, rightContainerEl.getHeight()-60]);
			return;
		}
		
		//Если Повод вызова «Решение старшего врача»
		if( rec.get('CmpReason_Code').inlist(['02?', '06?', '09?', '10?', '11?', '12?', '13?', '15?', '16?', '40?','999']) ){
			
			//Поле «Повод» - содержит выпадающий список поводов, кроме поводов «Решение старшего врача». Не заполнено, обязательное для заполнения
			/*
			унес в функцию проверки активности полей
			cmpReasonField.enable();
			cmpReasonField.setReadOnly(false);			

			
			//Поле «Вид вызова» доступное для редактирования
			cmpCallTypeIsExtraCombo.enable();
			cmpCallTypeIsExtraCombo.setReadOnly(false);
			*/
			// кроме поводов «Решение старшего врача»
			cmpReasonField.getStore().each(function(rec){
				if( rec.get('CmpReason_Code').inlist(['02?', '06?', '09?', '10?', '11?', '12?', '13?', '15?', '16?', '40?','999']) ){	rec.set('display', false); }
			});
			/*
			унес в функцию проверки активности полей
			lpuBuildingField.setReadOnly(false);
			lpuBuildingField.setDisabled(false);
			*/
			//lpuNMPBuildingField.clearValue();
			//cntr.setSmpNmpFields();
			cmpReasonField.clearValue();
			//Форма содержит кнопку «Сохранить» - активна, если заполнены все обязательные поля. При нажатии на кнопку «Сохранить» в систему вносятся следующие изменения
			//callDetailPanel.down('button[refId=saveBtn]').setVisible(true);
			callDetailPanel.down('button[refId=saveBtnAccept]').setVisible(true);
			
			var alertBox = Ext.create('Ext.window.Window', {
				title: 'Hello',
				height: 50,
				width: 500,
				layout: 'fit',
				constrain: true,
				cls: 'waitingForAnswerHeadDoctorWindow',
				header: false,
				constrainTo: rightContainerEl,
				layout: {
					type: 'hbox',
					align: 'middle'
				},
				items: [
					{
						xtype: 'label',
						flex: 1,
						html: "Требуется уточнить повод вызова"
					},
					{
						xtype: 'button',       
						text: 'Закрыть',
						renderTpl: [
							'<span id="{id}-btnWrap" class="{baseCls}-wrap closeBtn',
								'<tpl if="splitCls"> {splitCls}</tpl>',
								'{childElCls}" unselectable="on">',
								'<span id="{id}-btnEl" class="{baseCls}-button">',
									'X',
							'</span>'							
						],
						handler: function(){
							alertBox.close();							
						}
					}
				]
			});
			alertBox.showAt([rightContainerEl.getWidth()/2+rightContainerEl.getLocalX()-250, rightContainerEl.getHeight()-20]);
			return;
		}
		
		//Вызовы с поводом, имеющим признак Требуется наблюдение старшим врачом
		if(rec.get('CmpGroup_id') == 1) {
			
			callDetailPanel.down('button[refId=hdObservmodeAcceptBtn]').setVisible(true);
			
			var alertBox = Ext.create('Ext.window.Window', {
				title: 'Hello',
				height: 50,
				width: 500,
				layout: 'fit',
				constrain: true,
				cls: 'waitingForAnswerHeadDoctorWindow',
				header: false,
				constrainTo: rightContainerEl,
				layout: {
					type: 'hbox',
					align: 'middle'
				},
				items: [
					{
						xtype: 'label',
						flex: 1,
						html: "Требуется ознакомиться с вызовом"
					},
					{
						xtype: 'button',       
						text: 'Закрыть',
						renderTpl: [
							'<span id="{id}-btnWrap" class="{baseCls}-wrap closeBtn',
								'<tpl if="splitCls"> {splitCls}</tpl>',
								'{childElCls}" unselectable="on">',
								'<span id="{id}-btnEl" class="{baseCls}-button">',
									'X',
							'</span>'							
						],
						handler: function(){
							alertBox.close();							
						}
					}
				]
			});
			alertBox.showAt([rightContainerEl.getWidth()/2+rightContainerEl.getLocalX()-250, rightContainerEl.getHeight()-20]);
			return;
		}

		if(rec.get('hasEventDeny')){
			callDetailPanel.down('button[refId=denyAcceptBtn]').setVisible(true);
			callDetailPanel.down('button[refId=denyDiscardBtn]').setVisible(true);

			var alertBox = Ext.create('Ext.window.Window', {
				title: 'Hello',
				height: 80,
				width: 500,
				constrain: true,
				cls: 'waitingForAnswerHeadDoctorWindow',
				header: false,
				constrainTo: rightContainerEl,
				layout: {
					type: 'hbox',
					align: 'middle'
				},
				items: [
					{
						xtype: 'label',
						flex: 1,
						html: "<div>Необходимо разрешить отклонение вызова и указать другу подстанцию</div>"
					},
					{
						xtype: 'button',
						text: 'Закрыть',
						renderTpl: [
							'<span id="{id}-btnWrap" class="{baseCls}-wrap closeBtn',
							'<tpl if="splitCls"> {splitCls}</tpl>',
							'{childElCls}" unselectable="on">',
							'<span id="{id}-btnEl" class="{baseCls}-button">',
							'X',
							'</span>'
						],
						handler: function(){
							alertBox.close();
						}
					}
				]
			});
			alertBox.showAt([rightContainerEl.getWidth()/2+rightContainerEl.getLocalX()-250, rightContainerEl.getHeight()-20]);
		}

		// Вызовы, для которых не удалось автоматически определить МО передачи (НМП)
		//•	в качестве МО передачи (НМП) вызова указана данная МО
		//•	НЕ заполнено поле «Служба НМП»
		//•	Вид вызова «Вызов врача на дом»

		if(
			lpuNMPBuildingField.getValue() == sw.Promed.MedStaffFactByUser.current.Lpu_id &&
			!callDetailForm.findField('MedService_id').getValue() &&
			cmpCallTypeIsExtraCombo.getValue().inlist([3])
		){
			var alertBox = Ext.create('Ext.window.Window', {
				title: 'Hello',
				height: 80,
				width: 500,
				constrain: true,
				cls: 'waitingForAnswerHeadDoctorWindow',
				header: false,
				constrainTo: rightContainerEl,
				layout: {
					type: 'hbox',
					align: 'middle'
				},
				items: [
					{
						xtype: 'label',
						flex: 1,
						html: "<div>Не удалось автоматически определить МО передачи (НМП) для вызова врача на дом</div>"
					},
					{
						xtype: 'button',
						text: 'Закрыть',
						renderTpl: [
							'<span id="{id}-btnWrap" class="{baseCls}-wrap closeBtn',
							'<tpl if="splitCls"> {splitCls}</tpl>',
							'{childElCls}" unselectable="on">',
							'<span id="{id}-btnEl" class="{baseCls}-button">',
							'X',
							'</span>'
						],
						handler: function(){
							alertBox.close();
						}
					}
				]
			});
			alertBox.showAt([rightContainerEl.getWidth()/2+rightContainerEl.getLocalX()-250, rightContainerEl.getHeight()-20]);
		}


		//console.warn('showInWaitingForAnswerHeadDoctorModeView', rec);
	},
	
	showDetailCmpEmergencyTeamPanel: function(record){
		var cntr = this;
		cntr.teamsGridLoadMask.show();
		cntr.loadEmergencyTeamDetail(record.get('EmergencyTeam_id'), function(success){
			cntr.toggleLeftPanels(success);
			cntr.teamsGridLoadMask.hide();
		});
//		cntr.setDisabledEmergencyTeamDetailFields(true);
	},
	checkHaveCmpEmergencyTeamOnMap: function(record){
		var cntr = this,
			teamsGridHD = cntr.getTeamsGridHD(),
			showInMapBtn = Ext.ComponentQuery.query('button[refId=showInMapBtnHD]',teamsGridHD)[0],
			teamEmergencyGeoId = record.get('GeoserviceTransport_id');
		if(teamEmergencyGeoId)
			showInMapBtn.setDisabled(false);
		else
			showInMapBtn.setDisabled(true);


	},
	
	showMapPanel: function(emergencyTeamToCenter, callToCenter){
		var cntr = this,
			mapPanel = cntr.getMapPanelHD(),
			emergTeamGeoId = cntr.getTeamDetailHD().getForm().findField('GeoserviceTransport_id').getValue(),
			callRec = cntr.getCallsGridHD().getSelectionModel().getSelection()[0];

		mapPanel.setVisible(true);
		//cntr.getCallDetailHD().setVisible(false); оказывается, это было лишним, но вдруг всплывёт что-нибудь?
		cntr.getTeamDetailHD().setVisible(false);
		cntr.getTeamsGridHD().setVisible(false);
		cntr.getCallsGridHD().setVisible(false);
		
		cntr.getLeftSideContainerHD().setVisible(false);
		cntr.getRightSideContainerHD().setVisible(false);
		
		if (mapPanel.getCurrentMapType() == 'google'||'wialon')
		{
			if(typeof google != 'undefined')google.maps.event.trigger(mapPanel.getPanelByType(mapPanel.getCurrentMapType()).map,'resize');
		}
		if (mapPanel.getCurrentMapType() == 'here')
		{
			var hereMap = mapPanel.getPanelByType(mapPanel.getCurrentMapType());
			hereMap.map?hereMap.map.getViewPort().resize():false;
		}
		if (mapPanel.getCurrentMapType() == 'yandex'){
			if( (typeof ymaps != 'undefined') && (mapPanel.getPanelByType(mapPanel.getCurrentMapType()).map) )
				mapPanel.getPanelByType(mapPanel.getCurrentMapType()).map.container.fitToViewport();
		}
		mapPanel.up('container').doLayout();

		if(emergencyTeamToCenter)
		{
			if(emergTeamGeoId)
			{
				if(mapPanel.getCurrentMapType() == 'google'||'yandex')
					mapPanel.setMapViewToCenter(emergTeamGeoId)
				else
					Ext.Msg.alert('Ошибка','Тип карты в разработке');
			}
			else
				Ext.Msg.alert('Ошибка','Бригада не найдена');
		}
		
		if(callToCenter && callRec)
		{
			
			if( mapPanel.getCurrentMapType() == 'google'||'yandex' )
				{
					if(callRec.get('UnAdress_lat') && callRec.get('UnAdress_lng')){
						console.warn(callRec.get('UnAdress_lat'), callRec.get('UnAdress_lng'));
						mapPanel.setMapViewToCenterByCoords([callRec.get('UnAdress_lat'), callRec.get('UnAdress_lng')]);
					}
					else
						Ext.Msg.alert('Ошибка','Координаты вызова не установлены');
				}
			else
				Ext.Msg.alert('Ошибка','Тип карты в разработке');
		}		


		mapPanel.focus();
	},

	playWialonTrack: function () {

		/**
		 * Получаем идентификатор автомобиля и время
		 */
		this._getDataForWialonTrack(
		    /**
			 * Открываем вкладку виалона
		     */
			this.initWialonTrackPlayerTab.bind(this)
		);


	},
	_getDataForWialonTrack: function (callback) {
		var cntr = this,
			callDetailForm = cntr.getCallDetailHD().getForm();

		var offSetTimeRegion = {
			perm: 300,
			krym: 180,
			buryatiya: 480,
		};

		callback = Ext.isFunction(callback) ? callback : Ext.emptyFn;

		Ext.Ajax.request({
			url:'/?c=EmergencyTeam4E&m=getCmpCallCardTrackPlayParams',
			// autoAbort : true,
			callback: function(opt, success, response){
				var errStr='Не удалось построить трек';
				if ( success ) {
					var response_obj = Ext.JSON.decode(response.responseText);
					if (response_obj.Error_Msg)
					{
						Ext.Msg.alert('Ошибка', response_obj.Error_Msg);
					} else {
						if( response_obj[0]['startTime'] == false){
							errStr = 'Вызов не принят бригадой';
							Ext.Msg.alert('Ошибка', errStr);
							return false;
						}
						if(response_obj[0]['startTime'] && response_obj[0]['wialonId']){
							var startTime = new Date(response_obj[0]['startTime']);
							var endTime = (response_obj[0]['endTime'] == false) ? new Date() : new Date(response_obj[0]['endTime']);		            		
							var offSetTime = Math.abs( startTime.getTimezoneOffset() );
							//смещение во времени (крым смещение 180)
							var tz = Math.abs( offSetTimeRegion[getRegionNick()] - offSetTime);

							response_obj[0]['endTime'] = Math.floor(endTime.getTime()/1000) + (tz*60);
							response_obj[0]['startTime'] = Math.floor(startTime.getTime()/1000) + (tz*60);
		
							if(response_obj[0]['startTime'] >= response_obj[0]['endTime']){
								errStr = 'Неверный интервал времени: '+startTime+' -- '+endTime;
								Ext.Msg.alert('Ошибка', errStr);
								return false;
							}
							// предотвратим построение трека при маленьком интервале времени
							var intervalTime = 3;
							var interval = Math.floor( (response_obj[0]['endTime']-response_obj[0]['startTime'])/60 );
							if( interval <= intervalTime){
								errStr = interval+' мин. - слишком маленький интервал для построения трека';
								Ext.Msg.alert('Ошибка', errStr);
								return false;
							}
							callback(response_obj[0]);
						}else{
							if(!response_obj[0]['endTime'] || !response_obj[0]['startTime']){
								errStr = 'не задан интервал времени';
							}else if(response_obj[0]['startTime'] >= response_obj[0]['endTime']){
								errStr = 'Неверный интервал времени: '+response_obj[0]['startTime']+' -- '+response_obj[0]['endTime'];
							}else if(!response_obj[0]['wialonId']){
								errStr = 'Отсутствует идентификатор виалон у бригады';
							}
							Ext.Msg.alert('Ошибка', errStr);
						}
						// callback(response_obj[0]);
					}
				}else{
					Ext.Msg.alert('Ошибка', errStr);
				}

			}.bind(this),
			params:{
				CmpCallCard_id:callDetailForm.findField('CmpCallCard_id').getValue(),
				EmergencyTeam_id:callDetailForm.findField('EmergencyTeam_id').getValue(),
			}
		});
	},
	initWialonTrackPlayerTab: function(data) {
		var str;
		if (!this._checkTrackPlayerData(data)) {
			str = 'Неверные данные для построения трека! Пожалуйста, попробуйте ещё раз или обратитесь к разработчикам.';
			if(!data.wialonId) str = 'нет привязки к Wialon';
			Ext.Msg.alert('Ошибка', str);
			return;
		}

		if( !data.endTime || !data.startTime || data.startTime >= data.endTime){
			str = 'startTime: '+ new Date(data.startTime*1000) +' -- endTime:'+ new Date(data.endTime*1000);
			Ext.Msg.alert('Ошибка. Неверный формат даты.', str);
			return;
		}

		var cntr = this,
			callTrackTab = cntr.getCallTrackTab();

        callTrackTab.setVisible(true);
        this.mixins['WialonTrackPlayerTabController'].initTrackPlayer(data);
	},
	_checkTrackPlayerData: function(data) {
		return !!data.startTime &&
                !!data.endTime &&
                !!data.wialonId;
	},
	calcAddressesFromCoordsByGoogle: function(coordsArray, callback){
		if((typeof google != "object") || coordsArray.length==0 || typeof google.maps != "object" || typeof google.maps.LatLng !='function') return false;
		
		var coordsGoo = [];
		for(var i in coordsArray){
			coordsGoo.push( new google.maps.LatLng(coordsArray[i].lat, coordsArray[i].lng) );
		}
		
		new google.maps.DistanceMatrixService().getDistanceMatrix(
			{
				origins: [coordsGoo[0]],
				destinations: coordsGoo,
				travelMode: google.maps.TravelMode.DRIVING,
				avoidHighways: false,
				avoidTolls: false
			},function(response, status){
				if(response){ callback(response);}
			}
		);	
	},
	
	setSmpNmpFields: function(){
		var cntr = this,
			callDetailPanel = cntr.getCallDetailHD(),
			callDetailForm = callDetailPanel.getForm(),
			lpuSMPBuildingField = callDetailForm.findField('LpuBuilding_id'),
			lpuNMPBuildingField = callDetailForm.findField('Lpu_ppdid'),
			cmpReasonField = callDetailForm.findField('CmpReason_id'),
			typeCall = callDetailForm.findField('CmpCallCard_IsExtra'),
			isPoli = callDetailForm.findField('CmpCallCard_IsPoli'),
			selectNmpCombo = callDetailForm.findField('MedService_id'),
			Lpu_smpidField = callDetailForm.findField('Lpu_smpid'),
			IsPassSSMPField = callDetailForm.findField('CmpCallCard_IsPassSSMP'),
			cmpReasonRecord = cmpReasonField.getStore().findRecord( 'CmpReason_id', cmpReasonField.getValue()),
			consult = (cmpReasonRecord && cmpReasonRecord.get('CmpReason_Code').inlist(['91К'])),
			CmpCallTypeField = callDetailForm.findField('CmpCallType_id'),
			CmpCallTypeRec = CmpCallTypeField.findRecordByValue(CmpCallTypeField.getValue()),
			callTypeWithoutLpu = (CmpCallTypeRec && CmpCallTypeRec.get(CmpCallTypeField.codeField).inlist([6,15,16,17])); //Консультативное, Консультативный, Справка, Абонент отключился

		//@todo всю проверку на активность и доступность полей перенести в отдельную функцию
		//lpuSMPBuildingField.clearValue();
		//lpuNMPBuildingField.clearValue();
		//Поле доступно только если вид вызова Неотложный"
		isPoli.setVisible((typeCall.getValue() == 2) && !consult)
		isPoli.setDisabled(!(typeCall.getValue() == 2) && !consult)
			//Поле доступное для редактирования, если:
			//		o	Вид вызова «Экстренный»;
			//		o	ИЛИ если одновременно выполняются следующие условия:
			//			•	Вид вызова «Неотложный»;
			//			•	НЕ установлен флаг «Вызов передан в поликлинику по телефону (рации)»;
			//			•	НЕ заполнено поле «МО передачи (НМП)».

		var smpBuildingVisible = ((typeCall.getValue() == 1  && !consult) || ((typeCall.getValue() == 2) && !lpuNMPBuildingField.getValue() && !isPoli.getValue() ));
			/*
			lpuSMPBuildingField.setDisabled(!smpBuildingVisible);
			lpuSMPBuildingField.setReadOnly(!smpBuildingVisible);
			*/
			//Поле обязательно для заполнения, если одновременно выполняются следующие условия:
			//	o	Вид вызова – «Экстренный»;
			//	o	Тип вызова любое значение, кроме «Консультативное», «Консультативный», «Справка», «Абонент отключился»;
			//	o	НЕ установлен флаг «Вызов передан в другую ССМП по телефону (рации)»;

		var smpBuildingAllowBlank = ((typeCall.getValue() == 1) && !IsPassSSMPField.getValue() && !consult);

			if(lpuSMPBuildingField){
				lpuSMPBuildingField.allowBlank = callTypeWithoutLpu  || !smpBuildingAllowBlank;
				lpuSMPBuildingField.validate();
			}


			//Поле доступно для редактирования и обязательно для заполнения, если одновременно выполняются следующие условия:
			//	o	вид вызова – «Неотложный»;
			//	o	не заполнено поле «Подстанция».
			//  o	Не установлен флаг «Вызов передан в другую ССМП по телефону (рации)».

        var nmpBuildingVisible = false;

			if (typeCall.getValue() != null){
				nmpBuildingVisible = ( typeCall.getValue().inlist([1,2,3]) /*&& !lpuSMPBuildingField.getValue()*/ && !consult && !IsPassSSMPField.getValue() || isPoli.getValue());
			}

			lpuNMPBuildingField.setDisabled(!nmpBuildingVisible);
			lpuNMPBuildingField.setReadOnly(!nmpBuildingVisible);
			//lpuNMPBuildingField.allowBlank = callTypeWithoutLpu  || !nmpBuildingVisible;
			lpuNMPBuildingField.validate();
			selectNmpCombo.setDisabled(!nmpBuildingVisible);
			selectNmpCombo.setReadOnly(!nmpBuildingVisible);

		if(!nmpBuildingVisible){
			lpuNMPBuildingField.clearValue();
			selectNmpCombo.clearValue();
		}

		//Видимое, доступное и обязательное для заполнения, если установлен флаг «Вызов передан в другую ССМП по телефону (рации)»;
		Lpu_smpidField.setVisible(IsPassSSMPField.getValue());
		Lpu_smpidField.setDisabled(!IsPassSSMPField.getValue());
		Lpu_smpidField.setReadOnly(!IsPassSSMPField.getValue());
		Lpu_smpidField.allowBlank = !IsPassSSMPField.getValue();
		Lpu_smpidField.validate();

		callDetailPanel.down('button[refId=saveBtn]').setDisabled(!callDetailForm.isValid());
		callDetailPanel.down('button[refId=saveBtnAccept]').setDisabled(!callDetailForm.isValid());

	},
	
	//загрузка значений в филдсет результат выезда
	setEmergencyResultFields: function(){
		var cntr = this,
			callBaseForm = cntr.getCallDetailHD(),
			lpuhidLabel = callBaseForm.down('label[refId=LpuHidLabel]'),
			resultLabel = callBaseForm.down('label[refId=resultLabel]'),
			diagLabel = callBaseForm.down('label[refId=diagLabel]'),
			udiagLabel = callBaseForm.down('label[refId=udiagLabel]'),
			sdiagLabel = callBaseForm.down('label[refId=sdiagLabel]'),
			uslugaGrid = callBaseForm.down('gridpanel[refId=callDetailHDcmpCallCardUslugaGrid]'),
			drugsGrid = callBaseForm.down('gridpanel[refId=callDetailHDcmpCallCardDrugsGrid]'),
			CmpCallCard_id = callBaseForm.getForm().findField('CmpCallCard_id').getValue();
		
		if(!CmpCallCard_id) return false;
		
		uslugaGrid.store.load({
			params:{
				'CmpCallCard_id':CmpCallCard_id
			}
		});
		
		drugsGrid.store.load({
			params:{
				'CmpCallCard_id':CmpCallCard_id
			}
		});
		
		diagLabel.setText('');
		udiagLabel.setText('');
		sdiagLabel.setText('');
		lpuhidLabel.setText('');
		resultLabel.setText('');
		
		Ext.Ajax.request({
			url: '/?c=CmpCallCard&m=getCmpCallDiagnosesFields',
			params: {CmpCallCard_id: CmpCallCard_id},
			callback: function(opt, success, response) {
				if (success){
					var response_obj = Ext.JSON.decode(response.responseText);
					diagLabel.setText(response_obj[0].d_name);
					udiagLabel.setText(response_obj[0].du_name);
					sdiagLabel.setText(response_obj[0].ds_name);
					lpuhidLabel.setText(response_obj[0].mh_name);
					resultLabel.setText(response_obj[0].cr_name);
					callBaseForm.body.el.setScrollTop(5000);
				}
			}
		});
	},
	
	reloadStores: function(callback){
		var storeCalls = Ext.data.StoreManager.lookup('common.HeadDoctorWP.store.CmpCallsStore'),
			teamsCalls = Ext.data.StoreManager.lookup('common.HeadDoctorWP.store.EmergencyTeamStore'),
			addressForTransportsStore = Ext.data.StoreManager.lookup('addressForTransportsStore'),
			cntr = this,
            callsPanel = cntr.getCallsGridHD(),
            grFeature = callsPanel.getView().getFeature(cntr.getWinHD().id+'_GroupingGridHDFeature'),
			callBaseForm = cntr.getCallDetailHD(),
			topOffsetForm = callBaseForm.body.el.getScrollTop();

		storeCalls.load({
            scope: this,
            callback: function(records, operation, success) {
                var bigGroups = callsPanel.bigStore.groups.items;
                var data = [];

                bigGroups.forEach(function(value, index, arr) {
                    if(grFeature.isExpanded(index+1)){
                        //data = Ext.Array.merge(data, bigGroups[index].records);
                        data = data.concat(bigGroups[index].records);
                    }else{
                        data = data.concat(bigGroups[index].records[0]);
                    }
                });

                callsPanel.store.loadData(data);

                if(callback)callback();
            }
        });
		
		teamsCalls.load({
			scope: this,
			callback: function(records, operation, success) {
				callBaseForm.body.el.setScrollTop(topOffsetForm);
			}
		});
	},
	
	//оставлю обновление по ноду на потом
	nodeReloadStores: function(){
		/*var	win = this.getEmergecyWindowRef(),
			callsStore = this.getStore('common.DispatcherStationWP.store.CmpCallsStore'),
			teamsStore = this.getStore('common.DispatcherStationWP.store.EmergencyTeamStore');
		
		win.socket.emit('getAllDispatchStationCmpCalls', function(data){
			var data = Ext.JSON.decode(data,true);
			if (!data) {
				log({ERROR: data});
				return false;
			}
			callsStore.loadData(data.data);
		});
		
		win.socket.emit('getAllDispatchStationCmpTeams', function(data){
			var data = Ext.JSON.decode(data,true);
			if (!data) {
				log({ERROR: data});
				return false;
			}
			teamsStore.loadData(data);
		});*/
	},
	
	//вынес дизаблирование полей CMPCallCardDetail в отдельную функцию
	//а вдруг надо будет редактировать и сохранять потом
	//так и оказалось
	setDisabledCmpCardDetailFields: function( request ){
		var cntr = this,
			callDetailPanel = cntr.getCallDetailHD(),
			callDetailForm = callDetailPanel.getForm(),
			fields = Ext.ComponentQuery.query('field',callDetailPanel),
			saveBtn = Ext.ComponentQuery.query('button[refId=saveBtn]',callDetailPanel)[0],
			searchResText = Ext.ComponentQuery.query('panel[refId=pacientSearchResText]',callDetailPanel)[0],
			region = getRegionNick(),
			cmpCallTypeField = callDetailForm.findField('CmpCallType_id'),
			cmpCallTypeRec = cmpCallTypeField.findRecordByValue(cmpCallTypeField.getValue()),
			headDocObserve = request.CmpReason_Code ? request.CmpReason_Code.inlist(['02?', '06?', '09?', '10?', '11?', '12?', '13?', '15?', '16?', '40?','999']) : false,
			isClosedAndDisabled = ( request.CmpCallCardStatusType_id == 6 ),
			cmpCallTypeIsExtraCombo = callDetailForm.findField('CmpCallCard_IsExtra'),
			isPoli = callDetailForm.findField('CmpCallCard_IsPoli'),
			Lpu_ppdid = callDetailForm.findField('Lpu_ppdid'),
			MedService_id = callDetailForm.findField('MedService_id'),
			CmpCallCard_IsExtra = callDetailForm.findField('CmpCallCard_IsExtra').getValue();

		for(var i in fields){
			//иначе поле просто ReadOnly
			var field = fields[i];

			switch(field.name){

				//нередактируемые поля
				case 'CmpCallCard_prmDate' :
				case 'CmpCallCard_Numv' :
				case 'CmpCallCard_prmTime' :
				case 'CmpCallCard_Ngod' :
				case 'CmpCallCard_Urgency' :
				case 'EmergencyTeam_Num' :
				case 'DPMedPersonal_id' :
				case 'EmergencyTeamSpec_id' :
				{
					field.setDisabled(true);
					break;
				}

				//поля зависимые от второй улицы
				case "CmpCallCard_Dom" :
				case "CmpCallCard_Korp" :
				case "CmpCallCard_Kvar" :
				case "CmpCallCard_Podz" :
				case "CmpCallCard_Etaj" :
				case "CmpCallCard_Kodp" :
				{
					if( isClosedAndDisabled && getRegionNick().inlist(['perm']) || !region.inlist(['perm']) || cntr.isNmpArm){
						field.setReadOnly(true);
						field.setDisabled(true);
					}
					else{
						if(
							request.CmpCallCard_UlicSecond
						){
							field.setDisabled(true);
						}
						else{
							field.setDisabled(false);
						}
					}

					break;
				}
				case "CmpCallCard_Telf" :
				{
					var InputEl = field.inputEl.dom;

					InputEl.setAttribute('type','password');
					field.triggerEl.elements[0].dom.classList.remove('x-form-eye-open-trigger');

					if(!getRegionNick().inlist(['ufa', 'krym', 'kz']))
						field.triggerEl.elements[0].dom.style.opacity = 1;

					InputEl.removeAttribute('disabled');
					InputEl.setAttribute('type','password');

					InputEl.removeAttribute('readOnly');
					if(isClosedAndDisabled || !getRegionNick().inlist(['perm'])){
						InputEl.setAttribute('readOnly', true);
					}

					field.setDisabled(false);
					InputEl.style.opacity = 1;
					InputEl.classList.toggle('x-form-eye-open-trigger')
					field.labelEl.dom.style.opacity = 1;

					break;
				}

				case 'CmpCallCard_IsExtra': {
					var active = (
						!region.inlist(['ufa']) && (
							request.CmpCallCardStatusType_id.inlist([1,3]) ||
							( request.CmpCallCardStatusType_id.inlist([18]) && headDocObserve )
						)
					);
					field.setReadOnly(!active);
					field.setDisabled(!active);
					break;
				}

				case 'CmpReason_id': {
					var unactive = ( isClosedAndDisabled && region.inlist(['perm']) )
						|| ( !region.inlist(['perm', 'astra']) && !headDocObserve ) ;

					field.setReadOnly(unactive);
					field.setDisabled(unactive);

					break;
				}
				case 'Lpu_ppdid': {

					var	active = false;

					if(cntr.isNmpArm){
						//для нмп
						//#130151 Поле доступно для редактирования, если выполняется одно из следующих условий:
						//o	Статус вызова «Передано» и Вид вызова «Неотложный», «Вызов врача на дом», «Обращение в поликлинику»;
						//o	Статус вызова «Решение старшего врача», Повод вызова «Решение старшего врача» и Вид вызова «Неотложный».
						//o	#146657 Статус вызова «Решение старшего врача», врача» и Вид вызова «Вызов врача на дом».

						active = (CmpCallCard_IsExtra.inlist([2,3,4]) && request.CmpCallCardStatusType_id.inlist([1])) ||
							(CmpCallCard_IsExtra.inlist([2]) && request.CmpCallCardStatusType_id.inlist([18]) && headDocObserve) ||
							(request.CmpCallCardStatusType_id.inlist([18]) && CmpCallCard_IsExtra.inlist([3]));

						if(active && request.Lpu_ppdid == sw.Promed.MedStaffFactByUser.current.Lpu_id){
							field.clearValue();
						}

					}else{
						//для смп

						//#130151 Поле доступно для редактирования, если выполняется одно из следующих условий:
						//Статус вызова «Передано» или «Возврат» и Вид вызова «Неотложный»;
						//Статус вызова «Решение старшего врача», Повод вызова «Решение старшего врача» (при смене пользователем Повода вызова поле «Вид вызова» не блокируется до сохранения изменений) и Вид вызова «Неотложный».

						active = (CmpCallCard_IsExtra == 2) &&
							(
								request.CmpCallCardStatusType_id.inlist([1,3]) ||
								( request.CmpCallCardStatusType_id.inlist([18]) && headDocObserve )
							);
					}

					field.allowBlank = !(active && Ext.isEmpty(request.LpuBuilding_id));
					field.setDisabled(!active);
					field.setReadOnly(!active);

					break;
				}
				//Вызов передан в поликлинику по телефону (рации)»
				//Вызов передан в другую ССМП по телефону (рации)
				case 'CmpCallCard_IsPoli': {
					var active = (
						( request.CmpCallCardStatusType_id.inlist([1,3]) && CmpCallCard_IsExtra == 2 ) ||
						( request.CmpCallCardStatusType_id.inlist([18]) && headDocObserve )
					);
					field.setReadOnly(!active);
					//field.setDisabled(!active);
					break;
				}
				case 'CmpCallCard_IsPassSSMP': {
					var active = (
						( request.CmpCallCardStatusType_id.inlist([1,3]) && CmpCallCard_IsExtra == 2 ) ||
						( request.CmpCallCardStatusType_id.inlist([18]) && headDocObserve )
					);
					field.setReadOnly(!active);
					//field.setDisabled(!active);
					break;
				}
				case 'MedService_id': {
					//Служба нмп
					var Lpu_ppdidVal = callDetailForm.findField('Lpu_ppdid').getValue(),
						active = false;

					if(cntr.isNmpArm){
						//для нмп
						//#130151 Поле доступно для редактирования, если заполнено поле «МО передачи (НМП)» И выполняется одно из следующих условий:
						//o	Статус вызова «Передано»
						//o	#130151 Статус вызова «Решение старшего врача», Повод вызова «Решение старшего врача», Вид вызова «Неотложный»
						//o	#146657 Статус вызова «Решение старшего врача»,  Вид вызова «Вызов врача на дом».

						active = !Ext.isEmpty(Lpu_ppdidVal) &&
							(
								request.CmpCallCardStatusType_id.inlist([1]) ||
								(CmpCallCard_IsExtra.inlist([2]) && request.CmpCallCardStatusType_id.inlist([18]) && headDocObserve) ||
								( request.CmpCallCardStatusType_id.inlist([18]) && CmpCallCard_IsExtra.inlist([3]))
							);

					}else{
						//для смп

						//#130151 Поле доступно для редактирования, если выполняется одно из следующих условий:
						//Статус вызова «Передано» или «Возврат» и #114296 заполнено поле «МО передачи (НМП)».
						//Статус вызова «Решение старшего врача», Повод вызова «Решение старшего врача» (при смене пользователем Повода вызова поле «Вид вызова» не блокируется до сохранения изменений) и заполнено поле «МО передачи (НМП)».

						active =
							(
								( request.CmpCallCardStatusType_id.inlist([1,3]) && !Ext.isEmpty(Lpu_ppdidVal) ) ||
								( request.CmpCallCardStatusType_id.inlist([18]) && headDocObserve )
							);
					}

					field.setDisabled(!active);
					field.setReadOnly(!active);

					break;
				}
				case 'LpuBuilding_id': {
					//	#130151 Поле доступно для редактирования, если выполняется одно из следующих условий:
					//	Статус вызова «Передано» или «Возврат».
					//	Статус вызова «Решение старшего врача» и Повод вызова «Решение старшего врача» (при смене пользователем Повода вызова поле «Вид вызова» не блокируется до сохранения изменений).
					//	Статус вызова «Решение старшего врача» и Тип вызова «Для спец. Бригады СМП».
					//	При редактировании поля «Подразделение СМП» автоматически очищаются значения полей «МО передачи (НМП)», «Служба НМП» (если поля заполнены) и снимается флаг «Вызов передан в поликлинику по телефону (рации)» (если флаг установлен).

					var unactive = !request.CmpCallCardStatusType_id.inlist([1, 3]) &&
							!(request.CmpCallCardStatusType_id.inlist([18]) && cmpCallTypeRec && cmpCallTypeRec.get(cmpCallTypeField.codeField) == 9) &&
							!(request.CmpCallCardStatusType_id.inlist([18]) && headDocObserve && cmpCallTypeIsExtraCombo.getValue() == 1) &&
							!(request.CmpCallCardStatusType_id.inlist([18]) && headDocObserve && cmpCallTypeIsExtraCombo.getValue() == 2 && !isPoli.getValue() && !Lpu_ppdid.getValue() && !MedService_id.getValue());

						field.setReadOnly(unactive);
						field.setDisabled(unactive);

					break;
				}
				case 'CmpCallCard_Comm': {
					var unactive = ( isClosedAndDisabled && region.inlist(['perm']) );
						field.setReadOnly(unactive);
						field.setDisabled(unactive);

					break;
				}
				case 'CmpCallType_id' : {
					if(region == 'ufa') { //#159552
						var unactive = request.CmpReason_Code != "999" || request.directedFromAnotherLpu == '0' || isClosedAndDisabled;
						field.setReadOnly(unactive);
						field.setDisabled(unactive);
						field.store.addFilter( function (rec) {
							return rec.get('CmpCallType_Code').inlist(['1','6','15','16'])
						})
						break;
					}
				}
				default:
				{
					var unactive = ( ( isClosedAndDisabled && region.inlist(['perm']) ) || !region.inlist(['perm']) || cntr.isNmpArm);
					field.setReadOnly(unactive);
					field.setDisabled(unactive);
					break;
				}
			}
		}

		if(searchResText) searchResText.el.setHTML('');

	},
	
	//вынес дизаблирование полей в отдельную функцию
	setDisabledEmergencyTeamDetailFields: function(makeDisable){		
		var cntr = this,
			teamDetailPanel = cntr.getTeamDetailHD(),
			fields = Ext.ComponentQuery.query('field',teamDetailPanel);

		for(i in fields){
			fields[i].setDisabled(makeDisable);
			Ext.EventManager.purgeElement(fields[i].getEl())
		}		
	},

	searchPerson: function(){
		var
			cntr = this,
			personDateAge, personAgeFrom, personAgeTo, currentYear,
			deltaDate = 5;
		if(
			cntr.getCallDetailHD().getForm().findField('Person_SurName').getValue() != '' &&
			cntr.getCallDetailHD().getForm().findField('Person_FirName').getValue() != '' &&
			cntr.getCallDetailHD().getForm().findField('Person_SecName').getValue() != '' &&
			cntr.getCallDetailHD().getForm().findField('Person_Age').getValue() > 0
		) {
			currentYear = Ext.Date.format(new Date,'Y');
			personDateAge = cntr.getCallDetailHD().getForm().findField('Person_Age').getValue();
			if (personDateAge >1000) personDateAge = currentYear - personDateAge;
			personAgeFrom = currentYear - personDateAge - 1;
			personAgeTo = currentYear - personDateAge;
		
			Ext.Ajax.request({
				url: '/?c=Person4E&m=getPersonSearchGrid',
				params: {
					PersonSurName_SurName: cntr.getCallDetailHD().getForm().findField('Person_SurName').getValue(),
					PersonFirName_FirName: cntr.getCallDetailHD().getForm().findField('Person_FirName').getValue(),
					PersonSecName_SecName: cntr.getCallDetailHD().getForm().findField('Person_SecName').getValue(),
					PersonBirthYearFrom: personAgeFrom ? personAgeFrom-deltaDate : null,
					PersonBirthYearTo: personAgeTo ? personAgeTo+deltaDate : null,
					Sex_id: cntr.getCallDetailHD().getForm().findField('Sex_id').getValue(),
					search_type: 'identification'
				},
				callback: function(opt, success, response) {

					log(response);
					var response_obj = Ext.JSON.decode(response.responseText);
					if (typeof response_obj !== 'undefined') {
						response_obj = response_obj.data[0];
						if (typeof response_obj !== 'undefined') {
							cntr.getCallDetailHD().getForm().findField('Person_SurName').setValue(response_obj.PersonSurName_SurName);
							cntr.getCallDetailHD().getForm().findField('Person_FirName').setValue(response_obj.PersonFirName_FirName);
							cntr.getCallDetailHD().getForm().findField('Person_SecName').setValue(response_obj.PersonSecName_SecName);
							cntr.getCallDetailHD().getForm().findField('Person_Age').setValue(response_obj.Person_Age);
							cntr.getCallDetailHD().getForm().findField('Sex_id').setValue(parseInt(response_obj.Sex_id));
							cntr.getCallDetailHD().getForm().findField('Polis_Num').setValue(response_obj.Polis_Num);
							cntr.getCallDetailHD().getForm().findField('Person_id').setValue(response_obj.Person_id);
                            cntr.showPersonSearchMessage('Пациент идентифицирован', 'uno', opt.Person_isOftenCaller);
						}
					}
				}.bind(this)
			});
		}
	},
	
	loadCmpCardDetail: function(card_id, callback, loadFirstCard){
		var cntr = this,
			callDetailPanel = cntr.getCallDetailHD(),
			callDetailFirstHD = cntr.getCallDetailFirstHD(),
			callCard = loadFirstCard?callDetailFirstHD:callDetailPanel,
			callDetailParentPanel = cntr.getCallDetailParentHD(),
			callDetailForm = callCard.getForm(),
			сmpCallerTypeCombo = callDetailForm.findField('CmpCallerType_id'),
			сmpCallCardKtovField = callDetailForm.findField('CmpCallCard_Ktov'),
			cityCombo = callDetailForm.findField('dCityCombo'),
			streetsCombo = callDetailForm.findField('dStreetsCombo'),
			cmpCallCard_Dom = callDetailForm.findField('CmpCallCard_Dom'),
			secondStreetCombo = callDetailForm.findField('secondStreetCombo'),
			callTypeCombo = callDetailForm.findField('CmpCallType_id'),
			fields = Ext.ComponentQuery.query('field',callCard),
			callTrackTab = cntr.getCallTrackTab(),
			isPoli = callDetailForm.findField('CmpCallCard_IsPoli'),
			CmpCallCard_IsPassSSMP = callDetailForm.findField('CmpCallCard_IsPassSSMP');

		if( callTrackTab.isVisible() ){
			var wialonTrackMapPanel = callTrackTab.down('panel[refId=wialonTrackMapPanel]');
			wialonTrackMapPanel.initMarkup();
			cntr.mixins['WialonTrackPlayerTabController'].logout();
		}
		callDetailPanel.isLoading = true;

		for(var i in fields){
            //if(fields[i].resetOriginalValue) fields[i].resetOriginalValue();
            //resetOriginalValue ведет себя неадекватно, иногда взрывается
            fields[i].originalValue = null;
            if(fields[i].reset) fields[i].reset();
		}
		callTypeCombo.getStore().clearFilter();

		//callDetailForm.findField('Lpu_ppdid').clearValue();

        Ext.Ajax.request({
			url: '/?c=CmpCallCard4E&m=loadCmpCallCardEditForm',
			params: {CmpCallCard_id: card_id},
			callback: function(opt, success, response) {
				callDetailPanel.isLoading = false;
				var track = callDetailPanel.down('button[refId=showTrackBtn]');
				
				var me = this,
					response_obj = Ext.JSON.decode(response.responseText)[0];
					
				if(!response_obj) return false;

				var f_callback = function(){
					if (!success){
						callback(false, null);
						return false;
					}
					else{
						callback(true, response_obj);
					}

					return true;
				};

				var displaySecondStreet = response_obj.CmpCallCard_UlicSecond ? true : false;

				secondStreetCombo.setVisible(displaySecondStreet);
				cmpCallCard_Dom.setVisible(!displaySecondStreet);

				cityCombo.store.getProxy().extraParams = {
					KLRgn_id: response_obj.KLRgn_id,
					KLSubRgn_id: response_obj.KLSubRgn_id,
					KLCity_id: response_obj.KLCity_id,
					KLTown_id: response_obj.KLTown_id,
					region_id : getGlobalOptions().region.number
				};	
				
				if(! (response_obj.EmergencyTeam_id && (response_obj.WialonID || response_obj.GeoserviceTransport_id)) ){
					// скроем кнопку просмотр трека если бригада не назначена или не использует геосервис Wialon
					track.hidden = true;
				}else if(getGlobalOptions().region.nick.inlist(['krym','buryatiya'])){
					track.hidden = false;
				}
				
				//if(response_obj.KLRgn_id|| response_obj.KLSubRgn_id||response_obj.KLCity_id||response_obj.KLTown_id){
					cityCombo.store.load({
						callback: function(rec, operation, success){
							if ( this.getCount() != 1 || !rec) {
								cityCombo.clearValue();
								streetsCombo.clearValue();
								f_callback();
								return;
							}
							cityCombo.setValue(rec[0].get('Town_id'));
							cityCombo.originalValue = rec[0].get('Town_id');

							streetsCombo.bigStore.getProxy().extraParams = {
								town_id: rec[0].get('Town_id'),
								Lpu_id: sw.Promed.MedStaffFactByUser.current.Lpu_id
							};

							streetsCombo.bigStore.load({
								callback: function(records, operation, success) {
									f_callback();
									var rec = streetsCombo.bigStore.findRecord('StreetAndUnformalizedAddressDirectory_id', response_obj.StreetAndUnformalizedAddressDirectory_id);
									if (rec){
										streetsCombo.store.removeAll();
										streetsCombo.store.add(rec);
										streetsCombo.setValue(rec.get('StreetAndUnformalizedAddressDirectory_id'));
										streetsCombo.originalValue = rec.get('StreetAndUnformalizedAddressDirectory_id');
									}
									else{
										streetsCombo.setRawValue(response_obj.CmpCallCard_Ulic);
									}

									secondStreetCombo.bigStore.loadData(records);

									var secondrec = secondStreetCombo.bigStore.findRecord('KLStreet_id', response_obj.CmpCallCard_UlicSecond);
									if (secondrec){
										secondStreetCombo.store.removeAll();
										secondStreetCombo.store.add(secondrec);
										secondStreetCombo.setValue(secondrec.get('KLStreet_id'));

										secondStreetCombo.setVisible(displaySecondStreet);
										cmpCallCard_Dom.setVisible(!displaySecondStreet);
									}
								}
							});
						}
					});
				//}
				response_obj.CmpCallCard_IsPassSSMP = (response_obj.CmpCallCard_IsPassSSMP == 2);
				response_obj.CmpCallCard_prmDate = Ext.Date.parse(response_obj.CmpCallCard_prmDate, "Y-m-d H:i:s");
				response_obj.CmpCallCard_IsPoli = (response_obj.CmpCallCard_IsPoli == 2);
				var DPMedPersonalCombo = callDetailForm.findField('DPMedPersonal_id');

				//DPMedPersonalCombo.getStore().proxy.extraParams = {Lpu_pid: response_obj.Lpu_id};

				DPMedPersonalCombo.getStore().load({
					params: {Lpu_pid: response_obj.Lpu_rid || response_obj.Lpu_id},
					callback: function(){
						DPMedPersonalCombo.setValue(+response_obj.DPMedPersonal_id);
					}
				});

				callDetailForm.setValues(response_obj);

				if(response_obj.Person_Birthday){
					cntr.setPersonAgeFields('Person_Birthday');
				}


				if(!response_obj.CmpCallerType_id){
					сmpCallerTypeCombo.setValue(response_obj.CmpCallCard_Ktov);
				}
				var NmpServiceStore = callDetailForm.findField('MedService_id').getStore();

				//грузим службы с типом «Служба неотложной медицинской помощи», относящихся к МО, указанной в поле «МО передачи (НМП)».
				NmpServiceStore.proxy.extraParams = response_obj.Lpu_ppdid ? {'Lpu_ppdid': response_obj.Lpu_ppdid} : null;
				NmpServiceStore.reload();
				
				var diagnosesPersonOnDispText = cntr.armWindow.down('panel[refId=diagnosesPersonOnDispText]');
					diagnosesPersonOnDispText.setVisible(false);
					
				if(response_obj.Person_id){
					cntr.getDiagnosesPersonOnDisp(response_obj.Person_id);
				}

				var hasSecondCard = (loadFirstCard || response_obj.CmpCallCard_rid)?true:false;
				
				callDetailParentPanel.getTabBar().items.getAt(1).setVisible( hasSecondCard );
				callDetailFirstHD.setVisible( hasSecondCard );

				if(!loadFirstCard){
					callDetailParentPanel.setActiveTab(0);
					cntr.getCmpCallCardStatusHistory(card_id, hasSecondCard, response_obj.CmpCallCard_rid);
				}
				cntr.setSmpNmpFields();
			}.bind(this)
		});
		
	},
	
	//функция проверки диагнозов человека на диспансерном учете
	getDiagnosesPersonOnDisp: function(personId){
		
		var diagnosesPersonOnDispText = this.armWindow.down('panel[refId=diagnosesPersonOnDispText]'),
			url = '/?c=Person&m=getDiagnosesPersonOnDisp';

		this.abortRequestByUrl(url);

		Ext.Ajax.request({
			url: url,
			params: {Person_id : personId},
			//autoAbort: true,
			callback: function(opt, success, response) {
				if (success){
					var res = Ext.JSON.decode(response.responseText),
						msg = 'Пациент состоит на диспансерном учете по диагнозам:',
						content = '';
					
					if(res.length){
						for(var i=0; i<res.length; i++){
							if(i==2){content += '<a href="#" id="expandDiagnosesPersonOnDispText">Еще</a> ' +
								'<div id="diagnosesPersonOnDispTextHiden" style="visibility: hidden ">'}
							content += "<div><strong>" +res[i].Diag_Code+'</strong> ' + res[i].Diag_Name + "</div>";
						}
						content +='</div>';
						diagnosesPersonOnDispText.setHeight(70);
						diagnosesPersonOnDispText.setVisible(true);

						diagnosesPersonOnDispText.el.setHTML(
							'<div class="clientDopInfo" style="margin: 10px 0 0 80px; width: 600px;">' +
								'<div style="height: 16px;'+
									'padding-left: 23px;  background-image: url("extjs4/resources/images/alert.png");' +
								'background-repeat: no-repeat">'+ msg+'</div>' +
								'<div style="padding-left: 23px;">'+content+'</div>' +
							'</div>'
						);
						var expandDiagnosesPersonOnDispText = Ext.get('expandDiagnosesPersonOnDispText');
						if(expandDiagnosesPersonOnDispText){
							expandDiagnosesPersonOnDispText.on('click', function(){
								var diagnosesPersonOnDispText_hiden =  Ext.get('diagnosesPersonOnDispTextHiden');
								diagnosesPersonOnDispText_hiden.setVisible(true);
								diagnosesPersonOnDispText.setHeight(60 + diagnosesPersonOnDispText_hiden.getHeight());
								this.destroy();
							})
						}

					}
					else{
						diagnosesPersonOnDispText.el.setHTML('');
						diagnosesPersonOnDispText.setVisible(false);
					}
						
				}
			}
		});
	},
	
	//Метод получения количества закрытых вызовов за смену указанной бригады
	getCountCateredCmpCallCards: function(team_id){
		Ext.Ajax.request({
			url: '/?c=CmpCallCard4E&m=getCountCateredCmpCallCards',
			params: {EmergencyTeam_id: team_id},
			callback: function(opt, success, response) {
				if (success){
					var response_obj = Ext.JSON.decode(response.responseText)[0];
					return (response_obj.countCateredCmpCallCards)>0?response_obj.countCateredCmpCallCards:'';
				}
				else return '';
			}
		})
	},
	
	loadEmergencyTeamDetail: function(team_id, callback){
		var cntr = this,
			teamDetailPanel = cntr.getTeamDetailHD(),
			teamDetailForm = cntr.getTeamDetailHD().getForm(),
			callBlock = cntr.getTeamDetailHD().down('fieldset[refId=teamDetailHDcallBlock]'),
			parseFromDateToTime = function(textFormatDate){
				var dateF = Ext.Date.parse(textFormatDate, "Y-m-d H:i:s");
				if(dateF){return(Ext.Date.format(dateF, 'H:i'));}
				else{
					return textFormatDate?textFormatDate:false;
				}
			};
			
		teamDetailPanel.isLoading = true;
	
		Ext.Ajax.request({
			url: '/?c=EmergencyTeam4E&m=loadEmergencyTeam',
			params: {EmergencyTeam_id: team_id},
			callback: function(opt, success, response) {
				teamDetailPanel.isLoading = false;
				if (success){
					var response_obj = Ext.JSON.decode(response.responseText)[0],
						teamStart = parseFromDateToTime(response_obj.EmergencyTeamDuty_DTStart),
						teamFinish = response_obj.EmergencyTeamDuty_DTFinish = parseFromDateToTime(response_obj.EmergencyTeamDuty_DTFinish);	

					response_obj.EmergencyTeamDuty_DTStart = teamStart;
					response_obj.EmergencyTeamDuty_DTFinish = teamFinish;					
					response_obj.EmergencyTeam_Head1StartTime = parseFromDateToTime(response_obj.EmergencyTeam_Head1StartTime)||teamStart;
					response_obj.EmergencyTeam_Head1FinishTime = parseFromDateToTime(response_obj.EmergencyTeam_Head1FinishTime)||teamFinish;
					response_obj.EmergencyTeam_Head2StartTime = parseFromDateToTime(response_obj.EmergencyTeam_Head2StartTime)||teamStart;
					response_obj.EmergencyTeam_Head2FinishTime = parseFromDateToTime(response_obj.EmergencyTeam_Head2FinishTime)||teamFinish;
					response_obj.EmergencyTeam_Assistant1StartTime = parseFromDateToTime(response_obj.EmergencyTeam_Assistant1StartTime)||teamStart;
					response_obj.EmergencyTeam_Assistant1FinishTime = parseFromDateToTime(response_obj.EmergencyTeam_Assistant1FinishTime)||teamFinish;
					response_obj.EmergencyTeam_Assistant2StartTime = parseFromDateToTime(response_obj.EmergencyTeam_Assistant2StartTime)||teamStart;
					response_obj.EmergencyTeam_Assistant2FinishTime = parseFromDateToTime(response_obj.EmergencyTeam_Assistant2FinishTime)||teamFinish;
					response_obj.EmergencyTeam_Driver1StartTime = parseFromDateToTime(response_obj.EmergencyTeam_Driver1StartTime)||teamStart;
					response_obj.EmergencyTeam_Driver1FinishTime = parseFromDateToTime(response_obj.EmergencyTeam_Driver1FinishTime)||teamFinish;
					response_obj.EmergencyTeam_Driver2StartTime = parseFromDateToTime(response_obj.EmergencyTeam_Driver2StartTime)||teamStart;
					response_obj.EmergencyTeam_Driver2FinishTime = parseFromDateToTime(response_obj.EmergencyTeam_Driver2FinishTime)||teamFinish;
					
					//прячем поле о вызове, если бригада не назначена
					if(response_obj.CmpCallCard_id){callBlock.setVisible(true);}
					else{callBlock.setVisible(false);}
					
					Ext.Ajax.request({
						url: '/?c=CmpCallCard4E&m=getCountCateredCmpCallCards',
						params: {EmergencyTeam_id: team_id},
						callback: function(opt, success, response) {
							var countCCC = 0;
							if (success){
								var countCCC = Ext.JSON.decode(response.responseText)[0];
								countCCC = (countCCC.countCateredCmpCallCards)>0?countCCC.countCateredCmpCallCards:'';
							}
							
							response_obj.CountCateredCmpCallCards = countCCC;
	
							teamDetailForm.setValues(response_obj);
							callback(true);
						}
					})
					
					
				}
			}
		});
		
		cntr.getEmergencyTeamStatusHistory(team_id);
		
	},
	
	getEmergencyTeamStatusHistory: function(team_id){
		var cntr = this,
			statusHistoryStore = Ext.data.StoreManager.lookup('common.HeadDoctorWP.store.EmergencyTeamStatusHistoryStore');
		
		statusHistoryStore.load({
			params: {EmergencyTeam_id: team_id}
		});
	},
	
	getCmpCallCardStatusHistory: function( call_id, hasSecondCard, secCardId ){
		var cntr = this,
			statusHistoryStore = Ext.data.StoreManager.lookup('cmpCallCardStatusHistoryStore'),
			statusHistoryFirstStore = Ext.data.StoreManager.lookup('cmpCallCardStatusHistoryFirstStore');
		
		statusHistoryStore.load({
			params: {CmpCallCard_id: call_id}
		});
		
		if(hasSecondCard && secCardId){
			statusHistoryFirstStore.load({ params: {CmpCallCard_id: secCardId}	});
		}
	},
	
	getCmpCallCardUrgencyAndProfile: function(callback){
		var cntr = this,
			callDetailForm = cntr.getCallDetailHD().getForm(),
			url = '/?c=CmpCallCard4E&m=getCallUrgencyAndProfile';

		if(!getRegionNick().inlist(['ufa', 'krym'])){
			return;
		}

		this.abortRequestByUrl(url);

		Ext.Ajax.request({
			url: url,
			//autoAbort : true,
			callback: function(opt, success, response){
				if ( success ) {
					var response_obj = Ext.JSON.decode(response.responseText),
						type_service_reason = response_obj.CmpCallCardAcceptor_SysNick,
						oldIsExtraValue = callDetailForm.findField('CmpCallCard_IsExtra').getValue();
					
					if (response_obj.Error_Msg) {
						callDetailForm.findField('CmpCallCard_Urgency').setValue('');
					}
					else {
						callDetailForm.findField('CmpCallCard_Urgency').setValue(response_obj.CmpUrgencyAndProfileStandart_Urgency);
						callDetailForm.findField('CmpCallCard_IsExtra').setValue(("nmp" == type_service_reason)?2:1);
						if (getRegionNick().inlist(['ufa']) && (oldIsExtraValue != callDetailForm.findField('CmpCallCard_IsExtra').getValue())) {
							if (callDetailForm.findField('CmpCallCard_IsExtra').getValue() == 2) {
								cntr.getNmpMedService();
							} else {
								cntr.changeLpuBuildingByFormAddress();
							}
						}

						cntr.setSmpNmpFields();
					}
				}
		
			}.bind(this),
			params:{
				CmpReason_id:callDetailForm.findField('CmpReason_id').getValue(),
				Person_Age:callDetailForm.findField('Person_Age').getValue(),
				CmpCallPlaceType_id:callDetailForm.findField('CmpCallPlaceType_id').getValue()
			}
		});
	},
	
	saveCmpCallCardDetail: function(callbackFn){
		var cntr = this,
			callDetailForm = cntr.getCallDetailHD().getForm(),
			allFields = callDetailForm.getAllFields(),
			allValues = callDetailForm.getAllValues(),
			streetsCombo = allFields.dStreetsCombo,
			secondStreetCombo = allFields.secondStreetCombo,
			cityCombo = allFields.dCityCombo,
			cmpReasonCombo = allFields.CmpReason_id,
			win = cntr.getWinHD(),
			CmpCallTypeField = allFields.CmpCallType_id,
			CmpCallTypeRec = CmpCallTypeField? CmpCallTypeField.getSelectedRecord(): false,
			callTypeWithoutLpu = (CmpCallTypeRec && CmpCallTypeRec.get(CmpCallTypeField.codeField).inlist([6,15,16,17])),
			streetRec = streetsCombo.getSelectedRecord()? streetsCombo.getSelectedRecord(): null,
			secStreetRec = secondStreetCombo.getSelectedRecord()? secondStreetCombo.getSelectedRecord(): null;

		if(cntr.isNmpArm){
			if(
				allValues.CmpCallCard_IsExtra.inlist([2,3,4]) &&
				(CmpCallTypeRec && !CmpCallTypeRec.get(CmpCallTypeField.codeField).inlist([6,15,16])) &&
				!allValues.Lpu_ppdid &&
				!allValues.MedService_id
			){
				Ext.Msg.alert('Ошибка', 'Для данного вида вызова поля «МО передачи (НМП)», «Служба НМП» должны быть заполнены');
				return false;
			}
		}
		else{
			if(
				(allValues.CmpCallCard_IsExtra == 2) &&
				!callTypeWithoutLpu &&
				allValues.CmpCallCard_IsPassSSMP == false &&
				!allValues.LpuBuilding_id &&
				!allValues.Lpu_ppdid
			){
				Ext.Msg.alert('Ошибка', 'Если вызов неотложный, то хотя бы одно из полей «МО передачи (НМП)» или «Подразделение СМП» должно быть заполнено');
				return false;
			}
		}

		if (cmpReasonCombo.getSelectedRecord()) {
			var cmpReasonCode = cmpReasonCombo.getSelectedRecord().get('CmpReason_Code');
		}
		
		allValues.ARMType = sw.Promed.MedStaffFactByUser.getArmButton().current.ARMType;

		//Статус вызова меняется следующим образом:
		//Если Повод вызова «Консультация по телефону», то статус вызова меняется на «Закрыто»;
		//Иначе статус вызова меняется на «Передано».

		//Создаем статус без изменений для проверки
		allValues.CmpCallCardStatusType_currentId = allValues.CmpCallCardStatusType_id;
		if(allValues.CmpCallCardStatusType_id == 18){
			allValues.CmpCallCardStatusType_id = (cmpReasonCode && cmpReasonCode == '91К')?6:1
		};

		if (streetRec){
			allValues.StreetAndUnformalizedAddressDirectory_id = streetRec.get('StreetAndUnformalizedAddressDirectory_id');
			allValues.KLStreet_id = streetRec.get('KLStreet_id');
			allValues.UnformalizedAddressDirectory_id = streetRec.get('UnformalizedAddressDirectory_id');
		}

		// Вторая улица
		if (secStreetRec){
			allValues.CmpCallCard_UlicSecond = secStreetRec.get('KLStreet_id');
		}
		
		if (cityCombo.getValue() && cityCombo.getSelectedRecord()){
			var city = cityCombo.getSelectedRecord().data;

			if(city.KLAreaLevel_id==4){
				allValues.KLTown_id = city.Town_id;

				//если региона нет тогда нас пункт не относится к городу
				if(city.Region_id){
					allValues.KLSubRgn_id = city.Area_pid;
				} else{
					allValues.KLCity_id = city.Area_pid;
				}
			} else{
				allValues.KLCity_id = city.Town_id;
				//если город верхнего уровня, то район сохранять не надо
				if(city.KLAreaStat_id!=0)
				{allValues.KLSubRgn_id = city.Area_pid;}
			}

			allValues.KLAreaStat_idEdit = city.KLAreaStat_id;
			allValues.KLRgn_id = city.Region_id;
		};

		if(allValues.CmpCallCard_Ktov){
			allValues.CmpCallerType_id = null;
		}

		allValues.CmpCallType_Code = (CmpCallTypeRec) ? CmpCallTypeRec.get('CmpCallType_Code') : null;
		allValues.CmpCallCard_Ngod = parseInt(allValues.CmpCallCard_Ngod);

		allValues.withoutChangeStatus = cntr.withoutChangeStatus;		
		
		Ext.Ajax.request({
			url: '/?c=CmpCallCard4E&m=saveCmpCallCard',
			params: allValues,
			callback: function(opt, success, response) {

				var response_obj = Ext.JSON.decode(response.responseText);

				if (!success){
					return;
				}

				if(callbackFn)callbackFn(response_obj.success);

				if(response_obj.success){
					if(!cntr.withoutChangeStatus){
						//Статус вызова меняется на «Передано»
						var status = {
							CmpCallCardStatusType_id: 1,
							CmpCallCard_id:	params.CmpCallCard_id,
							armtype: 'smpheaddoctor'
						};

						Ext.Ajax.request({
							url: '/?c=CmpCallCard4E&m=setStatusCmpCallCard',
							params: status,
							success: function(response, opts){
								cntr.withoutChangeStatus = true;
								cntr.setDefaultViewPanels();
								cntr.reloadStores();
							}
						});
					}
					Ext.Msg.alert('Сохранение', 'Карта вызова успешно сохранена', function() {
						//условие для уфы из #107019
						if (win.socket && win.socket.connected && allValues.EmergencyTeam_id && !getRegionNick().inlist(['ufa'])) {
							win.socket.emit('changeCmpCallCard', response_obj.CmpCallCard_id, 'addCall', function(data){
								console.log('NODE emit addCall : apk='+data);
							});
							// win.socket.on('changeCmpCallCard', function(data, type){
							// 	console.log('NODE ON changeCmpCallCard type='+type);
							// });
						}
					});
				}else{
					var error_msg = (response_obj.Error_Msg) ? response_obj.Error_Msg : 'Ошибка при сохранении карты вызова';
					Ext.Msg.alert('Ошибка', error_msg);
				}
			}
		});
	},

	//Расчет возраста на основе даты рождения
	//Устанавливает связку 4 полей: приема вызова, даты рождения, возраста b ед. возраста
	//editedField - редактируемое поле - от него зависит, в какую сторону будут заноситься изменения
	setPersonAgeFields: function(editedField) {

		var cntr = this,
			base_form = cntr.getCallDetailHD().getForm(),
			prmDate = base_form.findField('CmpCallCard_prmDate').getValue(),//дата вызова
			birthdayField = base_form.findField('Person_Birthday'),//дата рождения поле
			birthday = Ext.Date.parse(birthdayField.getValue(), "d.m.Y"),//дата рождения значение
			Person_Age_Inp = base_form.findField('Person_Age'),//скрытый возраст в годах
			Person_Age = base_form.findField('Person_AgeInt'),//возраст
			AgeUnit_id = base_form.findField('ageUnit_id'),//ед. возраста
			editedFieldName = (typeof editedField == 'string') ? editedField : editedField.getName(),
			date = new Date();

		switch (editedFieldName){
			case 'CmpCallCard_prmDate':
			case 'Person_Birthday': {
				if (Ext.isEmpty(birthday)) {
					Person_Age.setValue(null);
					Person_Age_Inp.setValue(null);
					AgeUnit_id.setValue('years');
				} else {
					var years = swGetPersonAge(birthday, prmDate);

					Person_Age.setValue(years);

					if (years > 0) {
						Person_Age_Inp.setValue(years);
						AgeUnit_id.setValue(1);
						Person_Age_Inp.maxValue = 120;
					} else {
						var days = Math.floor(Math.abs((prmDate - birthday)/(1000 * 3600 * 24)));
						var months = Math.floor(Math.abs(prmDate.getMonthsBetween(birthday)));

						if (months > 0) {
							Person_Age_Inp.setValue(months);
							AgeUnit_id.setValue(2);
							Person_Age_Inp.maxValue = 11;
						} else {
							Person_Age_Inp.setValue(days);
							AgeUnit_id.setValue(3);
							Person_Age_Inp.maxValue = 30;
						}
					}
				}
				break;
			}
			case 'Person_Age':
			case 'ageUnit_id': {

				var inp = Person_Age_Inp.getValue(),
					type = AgeUnit_id.getValue(),
					calcBirthday;

				switch (type){
					case 1: {
						if(!Ext.isEmpty(inp)){
							Person_Age.setValue( inp );
						}
						calcBirthday = new Date((date.getFullYear() - inp).toString());
						calcBirthday.setMonth(0);
						calcBirthday.setDate(1);
						Person_Age_Inp.maxValue = 120;
						break;
					}
					case 2: {
						//calcBirthday = new Date((date.getMonth() - inp).toString());
						calcBirthday = new Date(date.setMonth(date.getMonth() - inp));
						calcBirthday.setDate(1);
						Person_Age_Inp.maxValue = 11;
						Person_Age.setValue(0);
						break;
					}
					case 3: {
						calcBirthday = new Date(date.setDate(date.getDay() - inp));
						//calcBirthday = new Date((date.getDay() - inp).toString());
						Person_Age_Inp.maxValue = 30;
						Person_Age.setValue(0);
						break;
					}
				}
				Person_Age_Inp.validate();

				birthdayField.setValue(Ext.Date.format(calcBirthday, "d.m.Y"));

				break;
			}

			default: return;
		}
	},

    showPersonSearchMessage: function(msg, status, dopInfo){

        var baseForm = this.baseForm,
            wnd = this.armWindow,
            cntr = this,
            Lpu_ppdid = baseForm.findField('Lpu_ppdid'),
            pacientSearchResText = wnd.down('panel[refId=pacientSearchResText]'),
            src = null,
            dopPanel = '',
            parentWdth = 200,
            storePerson =  this.storePerson;

        pacientSearchResText.setVisible(true);

        switch (status){
            case 'load': {src = 'extjs4/resources/themes/images/default/grid/loading.gif';break}
            case 'noone': {src = 'extjs4/resources/themes/images/default/grid/drop-no.gif';break}
            case 'uno': {src = 'extjs4/resources/themes/images/default/grid/drop-yes.gif';break}
            case 'many': {src = 'extjs4/resources/themes/images/default/grid/columns.gif';break}

        }


        if (dopInfo == 2){
            dopPanel = '<div style="height: 16px; float: left;'+
                'padding-left: 23px; margin: 0 10px; background-image: url(extjs4/resources/themes/images/default/shared/warning.gif);' +
                'background-repeat: no-repeat">Часто обращающийся</div>'
            parentWdth = 350;
        }
        pacientSearchResText.el.setHTML('<div class="clientDopInfo" style="margin: 10px auto 0; width: '+ parentWdth +'px;">' +
            '<div style="height: 16px; float: left;'+
            'padding-left: 23px;  background-image: url('+src+');' +
            'background-repeat: no-repeat">'+ msg+'</div>' + dopPanel +
            '</div>')

        if ((status == 'noone') || (status == 'many'))
        {
            Lpu_ppdid.reset()
            Lpu_ppdid.disable(true)
            Ext.fly(Lpu_ppdid.getId()).select('.small-tip').setVisible(false, true)
        }

        if (status == 'many')
        {

            Ext.create('Ext.Button', {
                text: 'Выбрать',
                renderTo: pacientSearchResText.el,
                style: 'margin: -3px 6px;',
                handler: function() {
                    var pacientSearchRes = Ext.create('Ext.window.Window', {
                        alias: 'widget.pacientSearchRes',
                        height: 250,
                        modal: true,
                        width: 925,
                        layout: 'fit',
                        //renderTo: 'clientInfoFS'
                        items: {
                            xtype: 'grid',
                            //id: 'personsList',
                            border: false,
                            renderIcon: function(val) {
                                if (val != 'false'){
                                    if (val=='true'){val='on'}
                                    return '<div class="x-grid3-check-'+val+' x-grid3-cc-ext-gen2118"></div>'
                                }
                                //return <div class="x-grid3-check-col-on-non-border-gray x-grid3-cc-ext-gen2121">&nbsp;</div>
                            },
                            columns: [
                                {text: 'Фамилия',  dataIndex: 'PersonSurName_SurName', width: 90},
                                {text: 'Имя', dataIndex: 'PersonFirName_FirName', width: 80},
                                {text: 'Отчество', dataIndex: 'PersonSecName_SecName', width: 100},
                                {text: 'Дата рождения', dataIndex: 'PersonBirthDay_BirthDay', width: 90},

                                {text: 'Дата смерти', dataIndex: 'Person_deadDT', width: 90, renderer: function(value){return this.renderIcon(value);}},
                                {text: 'ЛПУ прикрепления', dataIndex: 'Lpu_Nick', width: 90},
                                {text: 'Прикр. ДМС', dataIndex: 'PersonCard_IsDms', width: 70, renderer: function(value){return this.renderIcon(value);}},
                                {text: 'БДЗ', dataIndex: 'Person_IsBDZ', width: 50, renderer: function(value){return this.renderIcon(value);}},
                                {text: 'Фед. льг', dataIndex: 'Person_IsFedLgot', width: 70, renderer: function(value){return this.renderIcon(value);}},
                                {text: 'Отказ', dataIndex: 'Person_IsRefuse', width: 50, renderer: function(value){return this.renderIcon(value);}},
                                {text: 'Рег. льг', dataIndex: 'Person_IsRegLgot', width: 60, renderer: function(value){return this.renderIcon(value);}},
                                {text: '7 ноз.', dataIndex: 'Person_Is7Noz', width: 50, renderer: function(value){return this.renderIcon(value);}},
                            ],
                            store: storePerson,
                            listeners: {
                                beforecellclick: function( grid, td, cellIndex, record, tr, rowIndex, e, eOpts )
                                {
                                    this.setPatient(record.getData());
                                    pacientSearchRes.close();
                                    cntr.showPersonSearchMessage('Пациент идентифицирован', 'uno', record.get('Person_isOftenCaller'));
                                    //this.searchPerson()
                                }.bind(this)
                            }
                        }
                    }).show()
                }.bind(this)
            })
        }
    },
	//настройка горячих клавиш
	setHotKeys: function(){
		var cntr = this,
			win = cntr.getWinHD(),
			leftContainer = cntr.getLeftSideContainerHD(),
			rightContainer = cntr.getRightSideContainerHD(),
			callsGrid = cntr.getCallsGridHD(),
			teamsGrid = cntr.getTeamsGridHD(),
			callDetailHD = cntr.getCallDetailHD(),
			teamDetailHD = cntr.getTeamDetailHD(),
			mapPanelHD = cntr.getMapPanelHD();
				
		var pressedkeyg = new Ext.util.KeyMap({
			target: win.el,
			binding: [
				/*{
					key: [Ext.EventObject.ENTER],
					fn: function(){

					}.bind(this)
				},*/
				{
					key: [Ext.EventObject.TAB],
					fn: function(c, e){
						if (rightContainer.hasCls('focused-panel') && callDetailHD.isVisible()) return false;
						e.preventDefault();
						if (leftContainer.hasCls('focused-panel')){	
							if(teamsGrid.isVisible()){teamsGrid.getView().focus();}
							if(callDetailHD.isVisible()){callDetailHD.getForm().findField('CmpCallCard_prmDate').focus();}
						}
						if (rightContainer.hasCls('focused-panel')){
							if(callsGrid.isVisible()){callsGrid.getView().focus();}
						}
						rightContainer.getEl().toggleCls('focused-panel');
						leftContainer.getEl().toggleCls('focused-panel');
					}
				},
				{
					key: [Ext.EventObject.ESC],
					fn: function(c, e){
						cntr.setDefaultViewPanels();
						e.preventDefault();
					}
				},
				{
					key: [Ext.EventObject.F4],
					fn: function(c, evt){
						cntr.showMapPanel();
						//me.toggleExpandMapPanel();
					}
				},
				/*{
					key: [Ext.EventObject.BACKSPACE],
					fn: function(c, e){
						
					}
				}*/
			]
		});
	},
	showCmpCallCardSubMenu: function(x,y, callsView, teamsView){
		var cntr = this,
			recCardInSelection = callsView.getSelectionModel().getSelection()[0],
			recCard = recCardInSelection ? callsView.getStore().findRecord('CmpCallCard_id', recCardInSelection.get('CmpCallCard_id')) : null;

        if(!recCard) return false;

        var recCardGroupId = parseInt(recCard.get('CmpCallCardStatusType_id')),
            operDeptOptions = cntr.getOperDeptOptions(),
			isNMP = (recCard.get('CmpCallCardAcceptor_Code')=='НМП');

		var subMenu = Ext.create('Ext.menu.Menu', {
            plain: true,
            renderTo: Ext.getBody(),
            constrainTo: (x&&y)? null : callsView.getSelectedNodes()[0].querySelector('.cell-cmpcc-moreinfo'),
            items: [
                {
                    text: 'На бригаду',
                    hideOnClick: false,
					hidden: getRegionNick().inlist(['ufa']),
                    itemId: 'TeamsToCallDynamicOpenSubMenu',
                    disabled: (
						!Ext.Array.contains([1,2], recCardGroupId) ||
						!teamsView.store.getCount() ||
						(isNMP && !cntr.isNmpArm) ||
						(
							cntr.isNmpArm &&(
								//!Ext.Array.contains([1,2], recCardGroupId) ||
								!recCard.get('CmpCallType_Code').inlist([1,19]) ||
								!recCard.get('CmpCallCard_IsExtra').inlist([2,3])
							)
						)
					),
                    handler: function(i){
                        i.cancelDeferHide();
                        i.doExpandMenu();
                    },
                    menu: {
                        xtype: 'menu',
                        showSeparator: false,
                        itemId: 'TeamsToCallDynamicSubMenu',
                        listeners: {
                            show: function(sm){

                            },
                            click: function(m,i)
                            {
                                var team_id = i.value,
                                    team = teamsView.store.findRecord('EmergencyTeam_id', team_id)

                                teamsView.getSelectionModel().select(team);
                                cntr.setCenterEmergencyTeamOnMap(team, true, null, callsView, teamsView);
                            }
                        }
                    }
                },
				{
					text: 'Копировать',
					hidden: !(cntr.getOperDeptOptions() && cntr.getOperDeptOptions().SmpUnitParam_IsCallSenDoc == 'true'),
					handler: function(){
						cntr.showWorkPlaceSMPDispatcherCallWindow(false,callsView);
						/*var params = {
							'Town_id' : recCard.raw.Town_id,
							'StreetAndUnformalizedAddressDirectory_id' : recCard.raw.StreetAndUnformalizedAddressDirectory_id,
							'CmpCallCard_Dom' : recCard.raw.CmpCallCard_Dom,
							'CmpCallCard_Korp' : recCard.raw.CmpCallCard_Korp,
							'CmpCallCard_Kvar' : recCard.raw.CmpCallCard_Kvar,
							'CmpCallCard_Podz' : recCard.raw.CmpCallCard_Podz,
							'CmpCallCard_Etaj' : recCard.raw.CmpCallCard_Etaj,
							'CmpCallCard_Kodp' : recCard.raw.CmpCallCard_Kodp,
							'CmpCallPlaceType_id' : recCard.raw.CmpCallPlaceType_id,
							'CmpCallCard_Telf' : recCard.raw.CmpCallCard_Telf,
							'CmpCallerType_id' : recCard.raw.CmpCallerType_id,
							'CmpCallCard_Comm' : recCard.raw.CmpCallCard_Comm,
							'CmpReason_id' : recCard.raw.CmpReason_id,
							'CmpReason_Name': recCard.raw.CmpReason_Name,
							'CmpCallCard_IsExtra': recCard.raw.CmpCallCard_IsExtra,
							'LpuBuilding_id': recCard.raw.LpuBuilding_id,
							'KLRgn_id': recCard.raw.KLRgn_id,
							'KLSubRgn_id': recCard.raw.KLSubRgn_id,
							'KLCity_id': recCard.raw.KLCity_id,
							'KLTown_id': recCard.raw.Town_id,
							'KLStreet_id': recCard.raw.KLStreet_id,
							'dCityCombo': recCard.raw.Town_id,
							'CmpCallCard_IsPoli': recCard.raw.CmpCallCard_IsPoli,
							'CmpCallType_Code': 1
						};

						getWnd('swWorkPlaceSMPDispatcherCallWindow').show({
							onSaveByDp: function(card_id){
								getWnd('swWorkPlaceSMPDispatcherCallWindow').hide();

								cntr.reloadStores(function(){
									//выделить запись
									var addedRec = callsView.getStore().findRecord('CmpCallCard_id', card_id);
									if(addedRec){
										callsView.getSelectionModel().select(addedRec);
									}
								});
							},
							showByDP: true,
							params: params
						});*/
					}.bind(this)
				},
                {
                    text: 'Отложить',
					//disabled: (	(recCardGroupId != 1) || !(isNMP && cntr.isNmpArm) ),
                    disabled: ((recCardGroupId != 1) || (!isNMP && cntr.isNmpArm)),
                    handler: function(menuBtn){
                        cntr.showDefferedCallWindow(recCard, callsView, menuBtn.getX(), menuBtn.getY())
                    }
                },
                {
                    text: 'Вывести из отложенных',
                    disabled: ((recCardGroupId != 19) || (!isNMP && cntr.isNmpArm)),
                    handler: function(){
                        cntr.setDefferedCallToTransmitted(recCard,callsView)
                    }
                },
                {
                    text: 'Оформить отказ',
                    //disabled: (!recCard.get('CmpCallCardStatusType_Code').inlist([1,12,10]) && recCard.get('CmpCallType_Code').inlist([4,17])),
                    disabled: ((!(recCard.get('CmpCallCardStatusType_Code').inlist([1,12,10]) && !recCard.get('CmpCallType_Code').inlist([14,17]))) || isNMP),
                    hidden: !getRegionNick().inlist(['ufa']),
                    handler: function(){
                        cntr.showSelectReasonRejectSmpCallCard();
                    }
                },
                {
                    text: 'Вернуть в работу',
                    disabled: ((!recCard.get('CmpCallCardStatusType_Code').inlist([5])) || isNMP),
                    hidden: !getRegionNick().inlist(['ufa']),
                    handler: function(){
                        cntr.showBackToWorkMsg();
                        /*
                        Ext.Msg.show({
                            title:'Вернуть в работу',
                            msg: 'Вы действительно хотите вернуть вызов в работу?',
                            buttons: Ext.Msg.YESNO,
                            icon: Ext.Msg.QUESTION,
                            fn: function(btn, text){
                                if (btn == 'yes'){
                                    var CmpReason_Code = recCard.get('CmpReason_Code'),
                                        HeadDoctorObservReason = recCard.get('HeadDoctorObservReason'),
                                        CmpGroup_id = recCard.get('CmpGroup_id'),
                                        CmpCallType_Code = recCard.get('CmpCallType_Code'),
                                        CmpCallCardStatusType_id = 1;


                                    if(
                                        CmpReason_Code == '999'
                                        || ( operDeptOptions && operDeptOptions.LpuBuilding_IsCallReason == 'true' && HeadDoctorObservReason )
                                        || ( operDeptOptions && operDeptOptions.LpuBuilding_IsCallSpecTeam == 'true' && CmpCallType_Code == 9 )
                                    ){
                                        //Если Повод вызова «Решение старшего врача», то статус вызова меняется на «Решение старшего врача»
                                        //Если Повод вызова имеет признак «Требуется наблюдение старшим врачом»
                                        //Если Тип вызова «Для спец. бр. СМП» ...<бла-бла-бла>...требующими решения старшего врача
                                        CmpCallCardStatusType_id = 18;
                                    }

                                    Ext.Ajax.request({
                                        url: '/?c=CmpCallCard4E&m=setStatusCmpCallCard',
                                        params: {
                                            CmpCallCardStatusType_id: 1, // Статус передано
                                            CmpCallCard_id: recCard.get('CmpCallCard_id'),
                                            CmpCallType_id: recCard.get('CmpCallType_id'),
                                            armtype: 'smpdispatcherstation'
                                        },
                                        success: function(response, opts){
                                            cntr.setDefaultViewPanels();
                                            cntr.reloadStores();
                                        }
                                    });
                                }
                                else return;
                            }
                        });
                        */
                    }
                },
				{
					text: 'Поставить на контроль',
					disabled: (recCard.get('CmpCallCard_isControlCall') == 2),
					hidden: (recCard.get('IsCallControll') != 2),
					handler: function(){
						cntr.setCallToControl(recCard, true);
					}
				},
				{
					text: 'Снять с контроля',
					disabled: !(recCard.get('CmpCallCard_isControlCall') == 2),
					hidden: (recCard.get('IsCallControll') != 2),
					handler: function(){
						cntr.setCallToControl(recCard, false);
					}
				},
				{
					text: 'Вызов исполнен',
					hidden: !cntr.isNmpArm,
					disabled: !(
						(recCard.get('CmpCallCard_IsExtra').inlist([3]) && recCard.get('CmpCallCardStatusType_id').inlist([1,2]))
						|| (recCard.get('CmpCallCard_IsExtra').inlist([2]) && recCard.get('CmpCallCardStatusType_id').inlist([2]))
					),
					handler: function(){
						Ext.create('sw.tools.swSelectNmpReasonWindow', {
							CmpCallCard_id:	recCard.get('CmpCallCard_id'),
							operation: 'ppdResult',
							listeners: {
								saveResult: function(win, rec){
									win.close();

									var status;
									if(
										rec.get("CmpPPDResult_Code").inlist([7, 8, 9, 10, 22])
									){
										status = 13;
									}else{
										status = 4;
									}

									Ext.Ajax.request({
										url: '/?c=CmpCallCard4E&m=setStatusCmpCallCard',
										params: {
											CmpCallCardStatusType_id: status, // Статус передано
											CmpCallCard_id: recCard.get('CmpCallCard_id'),
											CmpCallType_id: recCard.get('CmpCallType_id'),
											armtype: sw.Promed.MedStaffFactByUser.current.ARMType || sw.Promed.MedStaffFactByUser.last.ARMType
										},
										success: function(response, opts){
											cntr.setDefaultViewPanels();
											cntr.reloadStores();
										}
									});

								}
							}
						}).show();

						subMenu.close();
					}.bind(this)
				},
				{
					text: 'Передать в поликлинику',
					hidden: !cntr.isNmpArm,
					disabled: !(
						recCard.get('CmpCallCard_IsExtra').inlist([3])
						&& recCard.get('CmpCallCardStatusType_id').inlist([1, 18])
						&& recCard.get('Person_IsUnknown') != 2
					),
					handler: function(){

						subMenu.close();

						var callCardHistoryWindow = Ext.create('sw.tools.swAddHomeVisit',{
							params: {
								CmpCallCard_id: recCard.get('CmpCallCard_id'),
								CmpCallCard_prmDate: recCard.get('CmpCallCard_prmDate'),
								ComboValue_693: recCard.get('Lpu_ppdid'),
								Person_id: recCard.get('Person_id'),
								ComboValue_711: recCard.get('Adress_Name'),
								ComboValue_703: recCard.get('KLRegion_id'),
								ComboValue_705: recCard.get('KLCity_id'),
								KLTown_id: recCard.get('KLTown_id'),
								ComboValue_707: recCard.get('KLStreet_id'),
								Corpus: recCard.get('CmpCallCard_Korp'),
								House: recCard.get('CmpCallCard_Dom'),
								Flat: recCard.get('CmpCallCard_Kvar')
							},
							listeners:{
								addHomeVisit: function(){
									cntr.reloadStores();
								}
							}
						});
						callCardHistoryWindow.show();
					}.bind(this)
				},
				{
					text: 'История вызова',
					handler: function(){
						subMenu.close();

						var callCardHistoryWindow = Ext.create('sw.tools.swCmpCallCardHistory',{
							card_id: recCard.get('CmpCallCard_id')
						});
						callCardHistoryWindow.show();
					}.bind(this)
				},
                {
                    text: 'Прослушать аудиозапись',
                    disabled: !(recCard.get('CmpCallRecord_id')),
                    handler: function(){
                        subMenu.close();

                        Ext.create('common.tools.swCmpCallRecordListenerWindow',{
                            record_id : recCard.get('CmpCallRecord_id')
                        }).show();
                    }.bind(this)
                }
            ]
        }),
        subMenuEmergencyTeamsToCall = subMenu.down('menu[itemId=TeamsToCallDynamicSubMenu]');

        teamsView.store.each(function(rec){
            if( rec.get('EmergencyTeamDuty_isNotFact')==1 ) {
                // Бригады, плановая смена которых закончилась, но не закончилась фактическая смена будут НЕдоступны для назначения на вызов #94154
                return;
            }
            subMenuEmergencyTeamsToCall.add({
                text: rec.get('EmergencyTeam_Num')+' '+rec.get('EmergencyTeamStatus_Name'),
                value: rec.get('EmergencyTeam_id')
            });
        });
        if( subMenuEmergencyTeamsToCall.items.length == 0) subMenu.down('[itemId=TeamsToCallDynamicOpenSubMenu]').disable();

		subMenu.showAt(x,y);
	},

	showWorkPlaceSMPDispatcherCallWindow: function(newCall,callsView){
		if(!newCall){
			var cntr=this,
				recCardInSelection = callsView.getSelectionModel().getSelection()[0],
				recCard = (recCardInSelection)?callsView.getStore().findRecord('CmpCallCard_id', recCardInSelection.get('CmpCallCard_id')):false,
				data=recCard.raw,
				params = {
					'Town_id' : data.Town_id,
					'StreetAndUnformalizedAddressDirectory_id' : data.StreetAndUnformalizedAddressDirectory_id,
					'CmpCallCard_Dom' : data.CmpCallCard_Dom,
					'CmpCallCard_Korp' : data.CmpCallCard_Korp,
					'CmpCallCard_Kvar' : data.CmpCallCard_Kvar,
					'CmpCallCard_Podz' : data.CmpCallCard_Podz,
					'CmpCallCard_Etaj' : data.CmpCallCard_Etaj,
					'CmpCallCard_Kodp' : data.CmpCallCard_Kodp,
					'CmpCallPlaceType_id' : data.CmpCallPlaceType_id,
					'CmpCallCard_Telf' : data.CmpCallCard_Telf,
					'CmpCallerType_id' : data.CmpCallerType_id,
					'CmpCallCard_Comm' : data.CmpCallCard_Comm,
					'CmpReason_id' : data.CmpReason_id,
					'CmpReason_Name': data.CmpReason_Name,
					'CmpCallCard_IsExtra': data.CmpCallCard_IsExtra,
					'LpuBuilding_id': data.LpuBuilding_id,
					'KLRgn_id': data.KLRgn_id,
					'KLSubRgn_id': data.KLSubRgn_id,
					'KLCity_id': data.KLCity_id,
					'KLTown_id': data.Town_id,
					'KLStreet_id': data.KLStreet_id,
					'dCityCombo': data.Town_id,
					'CmpCallCard_IsPoli': data.CmpCallCard_IsPoli,
					'CmpCallType_Code': 1
				};
			getWnd('swWorkPlaceSMPDispatcherCallWindow').show({
				showByDP: true,
				params: params,
				onSaveByDp: function(card_id){
					getWnd('swWorkPlaceSMPDispatcherCallWindow').hide();
					cntr.reloadStores(function(){
						//выделить запись
						var addedRec = callsView.getStore().findRecord('CmpCallCard_id', card_id);
						if(addedRec){
							callsView.getSelectionModel().select(addedRec);
						}
					});
				}
			});
		}
		else{
			getWnd('swWorkPlaceSMPDispatcherCallWindow').show({
				showByDP: true,
				onSaveByDp: function(){
					getWnd('swWorkPlaceSMPDispatcherCallWindow').close();
				}
			});
		}
	},

    //назначение бригады
    setCenterEmergencyTeamOnMap: function(record, setEmergencyTeamToCall, durationText, callsView, teamsView, callback) {

        var storeCalls = Ext.data.StoreManager.lookup('common.DispatcherStationWP.store.CmpCallsStore'),
            recCardInSelection = callsView.getSelectionModel().getSelection()[0],
            //оказывается, что getSelection хранит старые данные
            callRec = recCardInSelection ? callsView.getStore().findRecord('CmpCallCard_id', recCardInSelection.get('CmpCallCard_id')) : null;

        if (record.get('EmergencyTeamStatus_Code') == '36') {
            setTimeout(function(){
                Ext.Msg.alert('Ошибка','Бригада в статусе ожидания принятия.');
            },1000);
            if(callback && typeof callback == 'function'){callback(false);}
            return false;
        }

        //только свободные бригады и свободные вызовы
        if ( !callRec || typeof callRec == 'undefined' || callRec.get('EmergencyTeam_id') ) {
            return false;
        }

        var map_panel = this.getMapPanelHD();

        if(record.get('GeoserviceTransport_id')) { map_panel.setRouteFromAmbulanceToAccident( record.get('GeoserviceTransport_id') ); }

        Ext.MessageBox.confirm('Назначение бригады на вызов', (durationText && ('Время доезда: ' + durationText) || '') + ' Назначить выбранную бригаду на вызов?', function (btn) {
            if (btn === 'yes') {
                if (callback && typeof callback == 'function') {
                    callback(true);
                }
                this.setEmergencyTeamToCall(callsView, teamsView);
            } else {
                if (callback && typeof callback == 'function') {
                    callback(false);
                }
                callsView.getSelectionModel().deselectAll();
                teamsView.getSelectionModel().deselectAll();
            }
        }, this);
    },

	// установка/снятие вызова с контроля
	setCallToControl: function(recCard, setControl){
		Ext.Ajax.request({
			url: '/?c=CmpCallCard4E&m=setCmpCallCardToControl',
			params: {
				CmpCallCard_id : recCard.get('CmpCallCard_id'),
				CmpCallCard_isControlCall : setControl ? 2 : 1
			},
			success: function (response) {
				var obj = Ext.decode(response.responseText);
				if (obj.success) {

				}
			}
		});
	},

    setEmergencyTeamToCall: function(callsView, teamsView){
        var	cntr = this,
            win = this.getWinHD(),
            selectedTeamRec = teamsView.getSelectionModel().getSelection()[0],
            selectedCallRec = callsView.getSelectionModel().getSelection()[0],
            selectedTeamId = selectedTeamRec.get('EmergencyTeam_id'),
            selectedCallId = selectedCallRec.get('CmpCallCard_id'),
            selectedTeamNum = selectedTeamRec.get('EmergencyTeam_Num'),
            selectedCallNum = selectedCallRec.get('CmpCallCard_Numv'),
            storeCalls = Ext.data.StoreManager.lookup('common.DispatcherStationWP.store.CmpCallsStore');

        if ( !selectedTeamId || !selectedCallId ) {
            setTimeout(function(){
                Ext.Msg.alert('Ошибка','Перед назначением, сначала выберите вызов, а затем бригаду.');
            },1000);
            return false;
        }

        var loadMask = new Ext.LoadMask(Ext.getBody(),{msg:"Сохранение данных..."});
        loadMask.show();

        Ext.Ajax.request({
            url: '/?c=CmpCallCard4E&m=setEmergencyTeamWithoutSending',
            params: {
                EmergencyTeam_id: selectedTeamId,
                CmpCallCard_id: selectedCallId
            },
            success: function(response, opts){
                loadMask.hide();
                var obj = Ext.decode(response.responseText);
                if (obj.success) {

                    var socketData = {EmergencyTeam_id: selectedTeamId, CmpCallCard_id: selectedCallId};
                    if (win.socket) {
                        win.socket.emit('setEmergencyTeamToCall', socketData, function (data) {
                            log('NODE emit setEmergencyTeamToCall : apk=' + data);
                        });

                        win.socket.emit('changeEmergencyTeamStatus', socketData, 'changeStatus', function (data) {
                            log('NODE emit changeStatus : apk=' + data);
                        });
                    }
                    Ext.Ajax.request({
                        url: '/?c=Messages&m=sendNotificationEmergencyTeam',
                        params: {
                            EmergencyTeam_id: selectedTeamRec.get('EmergencyTeam_id'),
                            EmergencyTeam_Num: selectedTeamRec.get('EmergencyTeam_Num'),
                            Person_FIO: selectedCallRec.get('Person_FIO'),
                            Urgency: selectedCallRec.get('CmpCallCard_Urgency'),
                            Numv: selectedCallRec.get('CmpCallCard_Numv'),
                            Adress_Name: selectedCallRec.get('Adress_Name'),
                            MedService_id: getGlobalOptions().CurMedService_id
                        },
                        success: function (response, opts) {

                            Ext.data.StoreManager.lookup('common.DispatcherStationWP.store.EmergencyTeamStore').reload();
                            Ext.MessageBox.confirm('Сообщение',
                                'Бригада №' + selectedTeamNum + ' назначена на вызов №' + selectedCallNum + '.' + '</br>Распечатать контрольный талон?', function (btn) {
                                    if (btn === 'yes') {
                                        if (getRegionNick().inlist(['ufa', 'krym'])) {
                                            var location = '/?c=CmpCallCard&m=printCmpCallCardHeader&CmpCallCard_id=' + selectedCallId;
                                            var win = window.open(location);
                                        } else {
                                            this.printControlBill({
                                                EmergencyTeam_id: selectedTeamId,
                                                CmpCallCard_id: selectedCallId
                                            });
                                        }
                                    }
                                }.bind(this));

                            storeCalls.reload();
                            callsView.separatorIsSet = false;
                            teamsView.getSelectionModel().select(selectedTeamRec);
                            callsView.getSelectionModel().deselectAll();
                        }.bind(this),
                        failure: function (response, opts) {
                            Ext.MessageBox.show({
                                title: 'Ошибка',
                                msg: 'Во время отправки СМС произошла непредвиденная ошибка.',
                                buttons: Ext.MessageBox.OK
                            });
                        }
                    });

                    //проверка по коду
                    var storeStatuses = Ext.data.StoreManager.lookup('common.DispatcherDirectWP.store.EmergencyTeamStatusStore'),
                        rec = storeStatuses.findRecord('EmergencyTeamStatus_Code', 36);

					cntr.reloadStores();

                    if(rec){
                        var params = {
                            // ID 59 - ожидание принятия
                            'EmergencyTeamStatus_id': rec.get('EmergencyTeamStatus_id'),
                            'EmergencyTeamStatus_Code': rec.get('EmergencyTeamStatus_Code'),
                            'EmergencyTeam_id': selectedTeamId,
                            'ARMType': (sw.Promed.MedStaffFactByUser.last != null) ? sw.Promed.MedStaffFactByUser.last.ARMType : sw.Promed.MedStaffFactByUser.current.ARMType,
                            'CmpCallCard_id': selectedCallId
                        };
                        cntr.setEmergencyTeamStatus(params, teamsView);
                    }

                } else {
                    Ext.Msg.alert('Ошибка','Во время назначения бригады произошла непредвиденная ошибка. Перезагрузите страницу и попробуйте выполнить действие заново. Если ошибка повторится, обратитесь к администратору.');
                    log('/?c=CmpCallCard&m=setEmergencyTeamWithoutSending query error.');
                    log( response );
                }
            }.bind(this),
            failure: function(response, opts){
                loadMask.hide();
                Ext.MessageBox.show({title:'Ошибка',msg:'Во время выполнения запроса произошла непредвиденная ошибка.',buttons:Ext.MessageBox.OK});
                log({response:response,opts:opts});
            }
        });
    },

    setEmergencyTeamStatus: function(data, teamsView, callsView, clb){
        var params = data,
            cntr = this,
            numv = 0,
            personFIO = '',
            CmpCallCardArr=[];
        if(params.EmergencyTeamStatus_Code == '9' && params.CmpCallCard_id > 0) {

            callsView.getStore().findBy(function(record,id){
                if(id == params.CmpCallCard_id){
                    numv = record.get('CmpCallCard_Numv');
                    personFIO = record.get('Person_FIO');
                }
            });

            Ext.MessageBox.show({title:'Сообщение',msg:'«Бригада назначена на вызов № ' + numv +', ' + personFIO +'. Смена статуса бригады на «Обед» невозможна»',buttons:Ext.MessageBox.OK});
            return;
        }

        if (params.EmergencyTeamStatus_Code.inlist('8', '23') && params.CmpCallCard_id > 0) {

            callsView.getStore().findBy(function (record, id) {
                if (id == params.CmpCallCard_id) {
                    numv = record.get('CmpCallCard_Numv');
                    personFIO = record.get('Person_FIO');
                }
                if(record.get('EmergencyTeam_id') == params.EmergencyTeam_id /*&& record.get('CmpGroup_id') !=3 */){
                    CmpCallCardArr.push(record.get('CmpCallCard_id'));
                }
            });

            Ext.MessageBox.confirm(
                'Сообщение',
                '«Бригада назначена на вызов № ' + numv + ', ' + personFIO + ' и будет отклонена. Продолжить?»',
                function (btn) {
                    if (btn === 'yes') {

                        Ext.Ajax.request({
                            url: '/?c=EmergencyTeam4E&m=setEmergencyTeamStatusRepair',
                            callback: function (opt, success, response) {
                                if (success) {
                                    var response_obj = Ext.decode(response.responseText),
                                        storeStatuses = Ext.data.StoreManager.lookup('common.DispatcherDirectWP.store.EmergencyTeamStatusStore'),
                                        rec = storeStatuses.findRecord('EmergencyTeamStatus_id', params.EmergencyTeamStatus_id),
                                        win = cntr.getEmergecyWindowRef(),
                                        storeCalls = Ext.data.StoreManager.lookup('common.DispatcherStationWP.store.CmpCallsStore');
                                    teamsView.getStore().reload();
                                    callsView.getStore().reload();

                                    //условие для устранения повторной загрузки при включенном ноде
                                    if (!win.socket || !win.socket.connected) {
                                        if (rec) {
                                            var statusCode = rec.get('EmergencyTeamStatus_Code');
                                            switch (statusCode) {
                                                case 4 :
                                                {
                                                    //конец обслуживания
                                                    storeCalls.reload();
                                                    break;
                                                }
                                            }
                                        }

                                    }
                                    ;
                                    teamsView.getStore().reload();
                                    callsView.getStore().reload();

                                    var data = {'EmergencyTeam_id': params.EmergencyTeam_id, 'CmpCallCardArr': CmpCallCardArr};
                                    if (win.socket) {
                                        win.socket.emit('changeEmergencyTeamStatus', data, 'changeStatus', function (data) {
                                            log('NODE emit changeStatus : apk='+data);
                                        });
                                    }

                                    if (clb) clb();


                                }
                            }.bind(this),
                            params: params
                        })

                    }
                },
                this
            );

        }else{
            Ext.Ajax.request({
                url: '/?c=EmergencyTeam4E&m=setEmergencyTeamStatus',
                callback: function(opt, success, response) {
                    if (success){

                        var response_obj = Ext.decode(response.responseText),
                            storeStatuses = Ext.data.StoreManager.lookup('common.DispatcherDirectWP.store.EmergencyTeamStatusStore'),
                            rec = storeStatuses.findRecord('EmergencyTeamStatus_id', params.EmergencyTeamStatus_id),
                            win = cntr.getEmergecyWindowRef(),
                            storeCalls = Ext.data.StoreManager.lookup('common.DispatcherStationWP.store.CmpCallsStore');

                        //условие для устранения повторной загрузки при включенном ноде
                        if (!win.socket || !win.socket.connected){
                            if(rec){
                                var statusCode = rec.get('EmergencyTeamStatus_Code');
                                switch(statusCode){
                                    case 4 :
                                    {
                                        //конец обслуживания
                                        storeCalls.reload();
                                        break;
                                    }
                                }
                            }

                        };
                        teamsView.getStore().reload();
                        storeCalls.reload();
                        var data = {'EmergencyTeam_id' : params.EmergencyTeam_id};
                        if (win.socket) {
                            win.socket.emit('changeEmergencyTeamStatus', data, 'changeStatus', function(data){
                                log('NODE emit changeStatus : apk='+data);
                            });
                        }

                        if(clb) clb();

                    }
                }.bind(this),
                params: data
            })
        }
    },

	setDefferedCallToTransmitted: function(recCard,callsView){
		var cntr = this;

		if(recCard){

			var params = {
				CmpCallCard_id: recCard.get('CmpCallCard_id'),
				CmpCallCardStatusType_currentId: recCard.get('CmpCallCardStatusType_id')
			};

			Ext.Ajax.request({
				url: '/?c=CmpCallCard4E&m=setDefferedCallToTransmitted',
				params: params,
				success: function (response) {
					cntr.reloadStores();
				}
			});

			/*
			var params = {
				CmpCallCardStatusType_id: 1, // Статус передано
				CmpCallCard_id: recCard.get('CmpCallCard_id'),
				CmpCallType_id: recCard.get('CmpCallType_id'),
				armtype: 'smpdispatcherstation'
            },
            win = this;


			Ext.Ajax.request({
				url: '/?c=CmpCallCard4E&m=setStatusCmpCallCard',
				params: params,
				success: function (response) {
					var obj = Ext.decode(response.responseText);
					if (obj.success) {
						//меняем время приема вызова
						Ext.Ajax.request({
							url: '/?c=CmpCallCard4E&m=saveCmpCallCardTimes',
							params: {
								CmpCallCard_id: recCard.get('CmpCallCard_id'),
								CmpCallCard_prmDT: new Date()
							},
							callback: function (opt, success, response) {
								if (!success) {
									return false;
								}
							}
						});

                        win.teamsGridLoadMask.show();
                        win.reloadStores(
                            function(){
                                win.teamsGridLoadMask.hide();
                            }
                        );
					} else {
						Ext.Msg.alert('Не удалось вывести вызов из отложенных');
					}
				}
			});
			*/
		}

	},
	showDefferedCallWindow: function(recCall, callsView, x, y){
		if (recCall) {
			var win = this,
				defCallWindow = Ext.create('Ext.window.Window', {
					title: 'Отложить до',
					height: 160,
					width: 250,
					layout: 'hbox',
					modal: true,
					items: {
						xtype: 'BaseForm',
						id: win.id + '_DefferedCallForm',
						height: 160,
						width: 250,
						items: {
							border: false,
							padding: '10 10 10 10',
							xtype: 'container',
							layout: 'column',
							floatable: false,
							height: 160,
							items: [
								{
									xtype: 'datefield',
									fieldLabel: 'Дата',
									format: 'd.m.Y',
									allowBlank: false,
									labelWidth: 75,
									plugins: [new Ux.InputTextMask('99.99.9999')],
									name: 'CmpCallCard_storDate',
									tabIndex: 1,
									value: new Date()
								},
								{
									height: 5, border: 0
								},
								{
									xtype: 'datefield',
									name: 'CmpCallCard_storTime',
									fieldLabel: 'Время',
									format: 'H:i:s',
									labelWidth: 75,
									hideTrigger: true,
									allowBlank: false,
									invalidText: 'Неправильный формат времени. Дата должна быть указана в формате ЧЧ:ММ:CC',
									plugins: [new Ux.InputTextMask('99:99:99')]
								},
								{
									height: 5, border: 0
								},
								{
									xtype: 'textfield',
									minHeight: 10,
									height: 20,
									labelWidth: 75,
									fieldLabel: 'Комментарий',
									enableKeyEvents: true,
									name: 'CmpCallCard_defCom'
								}
							]
						}
					},
					bbar: [
						{
							xtype: 'button',
							iconCls: 'save16',
							text: 'Сохранить',
							handler: function () {
								var form = Ext.getCmp(win.id + '_DefferedCallForm').getForm();

								if (form.isValid()) {
									var params = {
										CmpCallCardStatusType_id: 19, // Статус отложено
										CmpCallCard_id: recCall.get('CmpCallCard_id'),
										CmpCallType_id: recCall.get('CmpCallType_id'),
										armtype: 'smpdispatcherstation'
									};

									Ext.Ajax.request({
										url: '/?c=CmpCallCard4E&m=setStatusCmpCallCard',
										params: params,
										success: function (response) {
											var obj = Ext.decode(response.responseText);

											if (obj.success) {
												//обновляем талон вызова
												Ext.Ajax.request({
													url: '/?c=CmpCallCard4E&m=setDefferedCmpCallCardParams',
													params: {
														CmpCallCard_id: recCall.get('CmpCallCard_id'),
														CmpCallCard_storDate: Ext.Date.format(form.findField('CmpCallCard_storDate').getValue(), "Y-m-d"),
														CmpCallCard_storTime: Ext.Date.format(form.findField('CmpCallCard_storTime').getValue(), "H:i:s"),
														CmpCallCard_defCom: form.findField('CmpCallCard_defCom').getValue()
													},
													success: function (res) {
														var responseText = Ext.decode(res.responseText);
														if (responseText.success) {
															defCallWindow.close();
														} else {
															Ext.Msg.alert('Не удалось обновить данные вызова')
														}
													}
												});

                                                win.teamsGridLoadMask.show();
                                                win.reloadStores(
                                                    function(){
                                                        win.teamsGridLoadMask.hide();
                                                    }
                                                );

											} else {
												Ext.Msg.alert('Не удалось перевести вызов в отложенные')
											}
										}
									});
								}
							}
						},
						{xtype: 'tbfill'},
						{
							xtype: 'button',
							iconCls: 'cancel16',
							text: 'Закрыть',
							handler: function () {
								defCallWindow.close();
							}
						}
					],
				});

			defCallWindow.showAt(x, y);
		}
	},
	abortRequestByUrl: function(url){
		var requests = Ext.Ajax.requests;

		for (id in requests){
			if (requests.hasOwnProperty(id) && requests[id].options && requests[id].options.url == url) {
				Ext.Ajax.abort(requests[id]);
			}
		}

	},
	sortCmpCalls: function(field){
		var cntr = this,
			bigStoreCalls = Ext.data.StoreManager.lookup('common.HeadDoctorWP.store.CmpCallsStore'),
			callsPanel = cntr.getCallsGridHD();

		if(field){
			callsPanel.store.sorters.clear();
			callsPanel.store.sort(field, 'ASC');
		}else{
			if(cntr.isNmpArm){
				callsPanel.store.sort([
					{
						direction: 'ASC',
						property: 'CmpCallCard_IsExtra'
					},
					{
						direction: 'ASC',
						property: 'CmpCallType_id'
					},
					{
						direction: 'ASC',
						property: 'CmpCallCard_prmDate',
					}
				]);
			}
		}

        /*
            ломают группировку
		if(params && params == 'time'){

            storeCalls.sort([{
				sorterFn: function(v1,v2){
					var date1 = new Date(Date.parse(v1.get('CmpCallCard_prmDate'))),
						date2 = new Date(Date.parse(v2.get('CmpCallCard_prmDate'))),
						group1 = v1.get('CmpGroup_id'),
						group2 = v2.get('CmpGroup_id');

					if ( (group1 < group2)) {
						return -1;
					}
					else if((group1 == group2) && (date1 > date2)){
						return 1;
					}else if((group1 == group2) && (date1 < date2)){
						return -1;
					}
					else {
						return 1;
					}

				}
			}])

		}else{
			storeCalls.sort([{
				sorterFn: function(v1,v2){
					var urg1 = v1.get('CmpCallCard_Urgency'),
						urg2 = v2.get('CmpCallCard_Urgency'),
						group1 = v1.get('CmpGroup_id'),
						group2 = v2.get('CmpGroup_id');

					if ( (group1 < group2)) {
						return -1;
					}
					else if((group1 == group2) && (urg1 > urg2)){
						return 1;
					}else if((group1 == group2) && (urg1 < urg2)){
						return -1;
					}else if((group1 == group2) && (urg1 == urg2)){
						return 0;
					}
					else {
						return 1;
					}
				}
			}])
		}
		*/
	},
	changeLpuBuildingByFormAddress: function(allRegion){
		var me = this,
			callDetailForm = me.getCallDetailHD().getForm(),
			cityCombo = callDetailForm.findField('dCityCombo'),
			City_id, Town_id, Area_pid;

		var street_rec = callDetailForm.findField('dStreetsCombo').findRecord('StreetAndUnformalizedAddressDirectory_id', callDetailForm.findField('dStreetsCombo').getValue());
		if (!street_rec) {
			return;
		}

		var house = callDetailForm.findField('CmpCallCard_Dom').getValue();
		if (!house) {
			return;
		}
		if(this.saveAndContinue) return;

		// Данные выбранного города/наспункта
		if (typeof cityCombo.store.proxy.reader.jsonData !== 'undefined' && cityCombo.store.getAt(0)){
			var city = cityCombo.store.getAt(0).data;
			Area_pid = city.Area_pid;
			if(city.KLAreaLevel_id==4){
				Town_id = city.Town_id;
			} else{
				City_id = city.Town_id;
			}
		}


		Ext.Ajax.request({
			url: '/?c=TerritoryService&m=getLpuBuildingIdByAddress',
			params: {
				KLStreet_id: street_rec.get('KLStreet_id'),
				house: house,
				building: callDetailForm.findField('CmpCallCard_Korp').getValue(),
				city: ( City_id ) ? City_id : null,
				Area_pid: ( Area_pid ) ? Area_pid : null,
				town: ( Town_id ) ? Town_id : null,
				allRegion: allRegion ? allRegion : null
			},
			success: function(response){

				callDetailForm.findField('Lpu_ppdid').clearValue();
				callDetailForm.findField('MedService_id').clearValue();

				var data = Ext.JSON.decode(response.responseText);
				if (!data.length || !data[0] || !data[0].LpuBuilding_id) {
					if(allRegion || getGlobalOptions().smp_allow_transfer_of_calls_to_another_MO != 1){
						callDetailForm.findField('Lpu_ppdid').clearValue();
					}else{
						me.changeLpuBuildingByFormAddress(true)
					}
					return false;
				}
				callDetailForm.findField('LpuBuilding_id').setValue(parseInt(data[0].LpuBuilding_id));


			}
		});
	},
	getNmpMedService: function(){
		var cntr = this,
			baseForm = cntr.getCallDetailHD().getForm(),
		 	nmpCombo = baseForm.findField('MedService_id'),
			smpCombo = baseForm.findField('LpuBuilding_id'),
			lpuLocalCombo = baseForm.findField('Lpu_ppdid'),
		 	callTypeCombo = baseForm.findField('CmpCallType_id'),
		 	cityCombo = baseForm.findField('dCityCombo'),
		 	KLArea_id,
		 	streetCombo = baseForm.findField('dStreetsCombo'),
		 	streetRec = streetCombo.bigStore.findRecord('StreetAndUnformalizedAddressDirectory_id', streetCombo.getValue()),
			isExtra = baseForm.findField('CmpCallCard_IsExtra').getValue(),
		 	age = baseForm.findField('Person_Age').getValue();

		if (!baseForm.findField('Person_Age').getValue()) {
			age = 0;
		}

		// Данные выбранного города/наспункта
		if (typeof cityCombo.store.proxy.reader.jsonData !== 'undefined' && cityCombo.store.getAt(0)){
			var city = cityCombo.store.getAt(0).data;
			KLArea_id = city.Town_id;
		}

		var params = {
			KLStreet_id: streetRec?streetRec.get('KLStreet_id'):null,
			CmpCallCard_Dom: baseForm.findField('CmpCallCard_Dom').getValue(),
			CmpCallCard_Korp: baseForm.findField('CmpCallCard_Korp').getValue(),
			CmpCallCard_prmDate: baseForm.findField('CmpCallCard_prmDate').getRawValue(),
			CmpCallCard_prmTime: baseForm.findField('CmpCallCard_prmTime').getRawValue(),
			Person_Age: age,
			KLArea_id: ( KLArea_id ) ? KLArea_id : null,
		};

		var primaryCallType_Code = 1;

		var currentCallTypeRec = callTypeCombo.getStore().findRecord('CmpCallType_id',callTypeCombo.getValue());
		if(!currentCallTypeRec || currentCallTypeRec.data.CmpCallType_Code != primaryCallType_Code ){
			return false;
		}
		if (isExtra != 2 || Ext.isEmpty(params.KLStreet_id) || Ext.isEmpty(params.CmpCallCard_Dom) || Ext.isEmpty(params.CmpCallCard_prmDate) || Ext.isEmpty(params.CmpCallCard_prmTime) || Ext.isEmpty(params.Person_Age)) {
			return false;
		}

		Ext.Ajax.request({
			url: '/?c=CmpCallCard4E&m=getNmpMedService',
			params: params,
			success: function(response) {
				var obj = Ext.decode(response.responseText);

				if (!obj.success) {
					if (!Ext.isEmpty(obj.Error_Msg)) {
						Ext.Msg.alert('Ошибка', obj.Error_Msg);
					} else {
						Ext.Msg.alert('Ошибка', 'Ошибка при определении службы НМП');
					}
				} else {
					baseForm.findField('LpuBuilding_id').clearValue();
					if (!Ext.isEmpty(obj.Alert_Msg)) {

						cntr.showYellowMsg(obj.Alert_Msg, 3000);

					}
					if (!Ext.isEmpty(obj.MedService_id)) {
						smpCombo.setValue(null);
						nmpCombo.setValue(Number(obj.MedService_id));
					} else {
						nmpCombo.setValue(null);
						cntr.changeLpuBuildingByFormAddress();
					}

					if(obj.Lpu_id){
						lpuLocalCombo.getStore().clearFilter();
						lpuLocalCombo.setValue(parseInt(obj.Lpu_id));
					}

				}
			},
			failure: function() {
				sw.swMsg.alert('Ошибка', 'Ошибка при определении службы НМП');
			}
		});
	},
	showYellowMsg: function(msg, delay){
		var div = document.createElement('div');

		div.style.width='300px';
		div.style.height='65px';
		div.style.background='#edcd4b';
		div.style.border='solid 2px #efefb3';
		div.style.position='absolute';
		div.style.padding='10px';
		div.style.zIndex='99999';
		div.innerHTML = msg;
		div.style.right = 0;
		div.style.bottom = '50px';
		document.body.appendChild(div);

		setTimeout(function(){
			div.parentNode.removeChild(div);
		}, delay);
	},

    setOperDeptOptions: function(opts){
        this.operDeptOptions = opts;
    },

    getOperDeptOptions: function(opts){
        return this.operDeptOptions ? this.operDeptOptions : null;
    },

	//функция отображения кнопок на панели инструментов в зав-ти от настоек опер отдела
	setVisibilityTools: function(){
		var cntr = this,
			callsPanel = cntr.getCallsGridHD(),
			createNewCall = callsPanel.down('button[refId=createNewCall]');

		if(cntr.getOperDeptOptions()){
			createNewCall.setVisible(cntr.getOperDeptOptions().SmpUnitParam_IsCallSenDoc == 'true');
		}

	},
	setLpuBuildingsOnMap: function() {
		var me = this;

		Ext.Ajax.request({
			url: '?c=CmpCallCard4E&m=getControlLpuBuildingsInfo',
			callback: function (options, success, response) {
				if(success) {
					var res = Ext.JSON.decode(response.responseText),
						map = me.getMapPanelHD();

					for(var i in res) {
						map.setLpuBuildingMarker(res[i]);
					}
					return true;
				}
			}
		})
	},

	// функция отображения и фокусов полей для перекрестков
	// changeFocus - ставить фокус или нет
	checkCrossRoadsFields: function(changeFocus, e) {

		if(e && (e.getCharCode() == e.SHIFT)){return false;}

		var cntr = this,
			baseForm = cntr.getCallDetailHD().getForm(),
			cmpCallCard_Dom = baseForm.findField('CmpCallCard_Dom'),
			secondStreetCombo = baseForm.findField('secondStreetCombo'),
			CmpCallCard_Korp = baseForm.findField('CmpCallCard_Korp'),
			CmpCallCard_Kvar = baseForm.findField('CmpCallCard_Kvar'),
			CmpCallCard_Podz = baseForm.findField('CmpCallCard_Podz'),
			CmpCallCard_Etaj = baseForm.findField('CmpCallCard_Etaj'),
			CmpCallCard_Kodp = baseForm.findField('CmpCallCard_Kodp'),
			crossRoadsMode = ((cmpCallCard_Dom.getValue() == '/' && !secondStreetCombo.isVisible()) || (secondStreetCombo.getValue() && secondStreetCombo.isVisible()));

		//начали вводить улицу - слэш удалили
		if(secondStreetCombo.getValue()) cmpCallCard_Dom.reset();

		//проверка на существующий режим
		if((crossRoadsMode && secondStreetCombo.isVisible()) || (!crossRoadsMode && !secondStreetCombo.isVisible())) return;

		secondStreetCombo.setVisible(crossRoadsMode);
		cmpCallCard_Dom.setVisible(!crossRoadsMode);

		cmpCallCard_Dom.setDisabled(crossRoadsMode);
		CmpCallCard_Korp.setDisabled(crossRoadsMode);
		CmpCallCard_Kvar.setDisabled(crossRoadsMode);
		CmpCallCard_Podz.setDisabled(crossRoadsMode);

		CmpCallCard_Etaj.setDisabled(crossRoadsMode);
		CmpCallCard_Kodp.setDisabled(crossRoadsMode);

		if(changeFocus){
			if(crossRoadsMode){
				secondStreetCombo.focus();

				cmpCallCard_Dom.setValue(null);
				CmpCallCard_Korp.setValue(null);
				CmpCallCard_Kvar.setValue(null);
				CmpCallCard_Podz.setValue(null);
				CmpCallCard_Etaj.setValue(null);
				CmpCallCard_Kodp.setValue(null);
			}
			else{
				cmpCallCard_Dom.focus();
				if(secondStreetCombo.getPicker() && secondStreetCombo.getPicker().isVisible()){
					secondStreetCombo.collapse();
				}
			}
		}
	},

	printControlBill: function(data){
		if (!data['CmpCallCard_id'] || !data['EmergencyTeam_id']) {
			return false;
		}
		Ext.Ajax.request({
			url: '/?c=CmpCallCard4E&m=printControlTicket',
			params: {
				teamId: data['EmergencyTeam_id'],
				callId: data['CmpCallCard_id']
			},
			callback: function(opt, success, response){
				if (success){
					var win = window.open();
					win.document.write(response.responseText);
					win.document.close();
				}
			}
		})
	},
	checkNeedActiveCall: function(){
		var cntr = this;
		Ext.Ajax.request({
			url: '/?c=CmpCallCard4E&m=checkNeedActiveCall',
			success: function(response) {
				var obj = Ext.decode(response.responseText);
				if (obj && obj[0] && obj[0].success) {
					cntr.showYellowMsg('Превышено время ожидания назначения на бригаду. Перейдите на вкладку «Вызовы на контроле».', 3000);
				}
			}
		});
	},
	showNotify: function(contentText, buttons, height){
		var cntr = this,
			armWindowEl = cntr.getWinHD().getEl(),
			alertBox = Ext.create('sw.redNotifyWindow', {
				refId: 'redNotifyWindow',
				width: 450,
				height: height,
				contentText: contentText,
				bbar: buttons
			});

		alertBox.showAt([armWindowEl.getWidth()-500, armWindowEl.getHeight()-100]);
	},
	checkLpuBuildingWithoutSmpUnitHistory: function() {

		var cntr = this;
		Ext.Ajax.request({
			url: '/?c=CmpCallCard4E&m=checkLpuBuildingWithoutSmpUnitHistory',
			callback: function(opt, success, response){

				if(response && response.responseText){
					var res = Ext.JSON.decode(response.responseText),
						lbs = [];
					if(res.length > 0){
						res.forEach(function(item){
							lbs.push(item.LpuBuilding_Name);
						});
						cntr.showNotify('Подстанции ' + lbs.join(',') + ' не взяты под управление диспетчером' )
					}


				}
			}
		})
	},
	getEventWaitDuration: function(val){
		function addZero(time){
			if(time<10)
				time = '0' + time;
			return time;
		}
		function formatTime(time){
			var nTime;
			if(time<60)
				nTime = '00:' + addZero(time);
			else
				nTime = addZero(Math.floor((time/60))) + ':' + addZero(time%60);
			return nTime
		}
		return formatTime(val.EventWaitDuration);


	},

	getBaloonContent: function (team) {
    	if (!team || !getRegionNick().inlist(['buryatiya']))  return null;

    	var storeCalls = Ext.data.StoreManager.lookup('common.HeadDoctorWP.store.CmpCallsStore'),
			callId = team.get('CmpCallCard_id'),
			callRec = storeCalls.data.getAt(storeCalls.find('CmpCallCard_id', callId)),
			callData = callRec? callRec.data: null,
			content = '<div>';

		content += '<div>'+ team.get('EmergencyTeam_Num') +'</div>';
		content += '<div>'+ team.get('Person_Fin') +'</div>';
		content += '<div>'+ team.get('EmergencyTeamSpec_Code') +'</div>';
		content += '<div>'+ team.get('EmergencyTeamStatus_Name') +'</div>';

		callData? content += '<div>'+ callData.CmpReason_Code +'</div>': null;
		callData? content += '<div>'+ callData.Adress_Name +'</div>': null;
		callData? content += '<div>'+ callData.Person_FIO +'</div>': null;
		callData? content += '<div>'+ callData.personAgeText +'</div>': null;

		return content + '</div>';
	}
});

