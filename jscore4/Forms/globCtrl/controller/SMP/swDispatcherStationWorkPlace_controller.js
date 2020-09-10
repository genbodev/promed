/*
 * Контроллер АРМа диспетчера направлений
 */

Ext.define('SMP.swDispatcherStationWorkPlace_controller', {
	extend: 'SMP.swSMPDefaultController_controller',
	models: [
		'common.DispatcherStationWP.model.CmpCallCard',
		'common.DispatcherStationWP.model.EmergencyTeam',
		'common.DispatcherStationWP.model.PersonMod',
		'common.DispatcherStationWP.model.WialonMod'
	],
	stores: [
		'common.DispatcherStationWP.store.CmpCallsStore',
		'common.DispatcherStationWP.store.EmergencyTeamStore',
		'common.DispatcherStationWP.store.Person',
		// 'stores.smp.GeoserviceTransportStore',
		'stores.smp.GeoserviceTransportGroupStore',
		//'common.DispatcherStationWP.store.EmergencyTeamProposalLogic'
	],
	requires: [
        'sw.tools.swSmpCallCardCheckDuplicateWindow',
		'sw.tools.swSmpCallCardCheckLastDayClosedWindow',
		'sw.tools.swAddHomeVisit',
		'sw.CmpCallsList'
    ],
	
	refs: [{
			ref: 'mapPanelRef', // суфикс ref для того чтобы можно было отличить ссылку от метода класса (не надо копировать этот камент)
			selector: 'swDispatcherStationWorkPlace swsmpmappanel'
		},{
			ref: 'emergencyTeamViewRef',
			selector: 'swDispatcherStationWorkPlace #swDispatcherStationWorkPlace_teamGrid'
		},{
			ref: 'CmpCallCardViewRef',
			selector: 'swDispatcherStationWorkPlace #swDispatcherStationWorkPlace_callsGrid'
		},
		{
			ref: 'emergecyWindowRef',
			selector: 'swDispatcherStationWorkPlace'
		}
	],
	timeControlRefresh_flag: false,
	timeControlRefresh_ms: 100000,
	sortCalls: 'urgency',
	onlineEt: [],
	withoutChangeStatus: true,
	init: function() {

		var cntr = this;

		cntr.control({
			'swDispatcherStationWorkPlace': {
				beforerender: function(cmp){
					var region = getGlobalOptions().region.nick;
					/*
					var CmpCallsListDW = Ext.getCmp('callsListDW')
					if(CmpCallsListDW && CmpCallsListDW.down('CmpCallsList'))
						CmpCallsListDW.down('CmpCallsList').close();

					var CmpCallsListHD = Ext.getCmp('callsListHD')
					if(CmpCallsListHD && CmpCallsListHD.down('CmpCallsList'))
						CmpCallsListHD.down('CmpCallsList').close();
					*/
					//прикручиваем ноджс
					connectNode(cmp);
					//запустим таймер на обновление данных, который будет срабатывать (если NodeJS включен timeControlRefresh_flag=true) каждые timeControlRefresh_ms
					this.timeControlRefresh(cmp);
				},
				show: function(cmp){
					var region = getGlobalOptions().region.nick,
						EmergencyTeamNotNotify = [],
						EmergencyTeamsCallCards = null,
						callRecordsBeforeLoad = null;
						newRecordsAfterLoad = [];

					if(['ufa', 'krym', 'perm', 'kz', 'ekb', 'astra', 'komi', 'buryatiya', 'khak', 'kareliya'].indexOf(region) != -1) this.showSettingsWin();

					this.checkHavingLpuBuilding();

					var cntr = this,
						dt = new Date(Date.now()),
						firstDayMonth = Ext.Date.format(Ext.Date.getFirstDateOfMonth(dt), 'd.m.Y'),
						lastDayMonth = Ext.Date.format(Ext.Date.getLastDateOfMonth(dt), 'd.m.Y'),
						win = cntr.getEmergecyWindowRef(),
						teamsView = win.TeamsView,
						callsView = win.CallsView,
						panelCalls = callsView.up('panel'),
						panelTeams = teamsView.up('panel'),
						spanElCallsLoadingTool = panelCalls.down('tool[itemId=loadtool]').el.child('span'),
						spanElTeamsLoadingTool = panelTeams.down('tool[itemId=loadtool]').el.child('span'),
						storeCalls = Ext.data.StoreManager.lookup('common.DispatcherStationWP.store.CmpCallsStore'),
						shortCallsGrid = win.down('grid[refId=callsCardsShortGrid]'),
						storeTeams = Ext.data.StoreManager.lookup('common.DispatcherStationWP.store.EmergencyTeamStore'),
						callDetailForm = win.down('form[refId=CallDetailPanel]').getForm(),
						cardId = callDetailForm.findField('CmpCallCard_id'),
						modeViewArray = ['table','closed','cancel'], // различные режимы табличного вида
						groupingFeature = shortCallsGrid.getView().features[0],
						shortViewPanelWrapper = win.down('panel[refId=shortViewPanelWrapper]'),
						datePickerRange = shortViewPanelWrapper.down('datePickerRange'),
						mainToolbar = Ext.getCmp('Mainviewport_Toolbar'),
						settingsBtn = mainToolbar.down('button[refId=settingsBtn]'),
						focusedCmp;

					cntr.countOfLoadings = 0;

					settingsBtn.show();

					//Улучшение #109547 СМП. АРМ ДП. Необходимо изменить сортировку столбцов по умолчанию в разделе "Вызовы".
					if (getRegionNick().inlist(['ufa'])) {
						var columnsShortCallsGrid = shortCallsGrid.columns,
							ufaColumnsShortCallsGrid = [];
						//сперва собираем столбцы которые не используются в меню столбцов
						columnsShortCallsGrid.forEach(function (el, index, arr){
							if(el.hideable == false) {
								ufaColumnsShortCallsGrid.push(el.initialConfig);
							}	
						});
						//порядок столбцов для уфы
						var arrNoHiddenСolumns = [
							'is112',
							'CmpIllegalAct_byPerson',
							'CmpCallCard_Numv',
							'CmpCallCard_Ngod',
							'CmpCallCard_prmDateFormat',
							'Adress_Name',
							'CmpCallCard_IsExtraText',
							'CmpReason_Name',
							'DuplicateAndActiveCall_Count',
							'Person_Birthday',
							'Person_FIO',
							'EmergencyTeam_Num',
							'CmpCallType_Name',
							'CmpCallCardEventType_Name',
							'EventWaitDuration',
							'LpuBuilding_Code',
							'EmergencyTeamSpec_Code',
							'countcallsOnTeam',
							'EmergencyTeamDelayType_Name',
							'CmpCallCardAcceptor_Code',
							'CmpCallCard_PlanDT',
							'CmpCallCard_FactDT',
							'isLate',
							'CmpPPDResult_Name',
							'CmpCallCard_IsQuarantineText'
						];

						for(var i = 0; i < arrNoHiddenСolumns.length; i++){
							ufaColumnsShortCallsGrid.push(shortCallsGrid.down('gridcolumn[itemId=' + arrNoHiddenСolumns[i] + ']').initialConfig);
						}
						shortCallsGrid.reconfigure(shortCallsGrid.store, ufaColumnsShortCallsGrid);
					}
					
					if (getGlobalOptions().IsLocalSMP) {
						// АРМ доступен только для просмотра
						// Все кнопки/меню не доступны
						var topToolbar = win.down('toolbar[refId=DPTopToolbar]');
						// topToolbar.down('button[refId=createNewCall]').disable();
						topToolbar.down('button[refId=menuEmergencyTeam]').disable();
						topToolbar.down('button[refId=menuService]').disable();
						topToolbar.down('button[refId=buttonDlo]').disable();
						topToolbar.down('button[refId=EmergencyTeamsDutyMarks]').disable();
						topToolbar.down('button[refId=addStreamCard]').disable();
						topToolbar.down('button[refId=audioCalls]').disable();
						topToolbar.down('button[refId=wialon_btn]').disable();
						topToolbar.down('button[refId=glonass_btn]').disable();
						topToolbar.down('button[refId=vid1_btn]').hide();
						topToolbar.down('button[refId=vid2_btn]').hide();
						topToolbar.down('combo[refId=sortCalls]').hide();

						shortViewPanelWrapper.down('button[refId=saveShortCardBtn]').disable();

						Ext.getCmp('Mainviewport_Toolbar').down('button[refId=settingsBtn]').disable();

						// Блок Бригады скрыт
						teamsView.ownerCt.hide();
					}

					//if(cntr.isNmpArm){
					//	shortViewPanelWrapper.down('button[refId=saveShortCardBtn]').disable();
					//}

					storeCalls.on('beforeload', function(store, operation){

						if (cntr.reloadTimeout) {
							clearTimeout(cntr.reloadTimeout);

							cntr.reloadTimeout = setTimeout(function() {
								cntr.reloadStores();
							}, 20000);
						} else {
							cntr.reloadTimeout = setTimeout(function() {
								cntr.reloadStores();
							}, 20000);
						}

						if(typeof cntr.modeView !== 'undefined' && cntr.modeView == 'table')
							//operation.params.modeView = cntr.modeView;
							store.getProxy().setExtraParam('modeView',cntr.modeView);
							store.getProxy().setExtraParam('mode',cntr.modeView);							

						if(spanElCallsLoadingTool.hasCls('hiddentool'))spanElCallsLoadingTool.removeCls('hiddentool');
						store.getProxy().setExtraParam('callRecords',Ext.JSON.encode({all:callRecordsBeforeLoad,new:newRecordsAfterLoad}));
						focusedCmp = Ext.ComponentQuery.query('field[hasFocus=true]')[0];
						if(groupingFeature.groupCache)
							groupingFeature.stateOfGropups = groupingFeature.groupCache;
					});

					if(cntr.isNmpArm){
						cntr.sortCalls = 'nmp';

					}else{
						cntr.sortCalls = getRegionNick().inlist(['ufa']) ? 'time' : 'urgency';
					};

					cntr.sortCmpCalls();


					storeCalls.on('load', function( st, records, successful){
						groupingFeature.collapse(4, true);
						groupingFeature.collapse(5, true);
						groupingFeature.collapse(6, true);
						groupingFeature.collapse(7, true);

						if(cntr.countOfLoadings == 0){
							switch(cntr.modeView){
								case 'table':
									//storeCalls.add({CmpGroupTable_id: 2},{CmpGroupTable_id: 3},{CmpGroupTable_id: 4},{CmpGroupTable_id: 5});
									//storeCalls.group('CmpGroupTable_id','ASC');
									break;
								case 'closed':
									groupingFeature.expand(5, true);
									break;
								case 'cancel':
									groupingFeature.expand(6, true);
									break;
							}
						}
						
						if(!successful){st.removeAll()};
						if(!spanElCallsLoadingTool.hasCls('hiddentool'))spanElCallsLoadingTool.addCls('hiddentool');
						if(( st.find('CmpCallCard_id', callDetailForm.findField('CmpCallCard_id').getValue() ) == -1 ) &&
							( st.find('CmpCallCard_id', callDetailForm.findField('CmpCallCardDubl_id').getValue() ) == -1 ) )
						{
							callDetailForm.reset();
						}
						
						var groups = [],
							counts = [],
							removeIndexByGroupFiltered = [],
							removeIndexByGroup = [];
						st.each(function(rec,index) {
							if(typeof rec.raw.CmpCallCard_id == 'undefined'
								&& typeof rec.raw.CmpGroupTable_id !== 'undefined'
								&& typeof rec.raw.countCardByGroup !== 'undefined')
							{
								counts[rec.raw.CmpGroupTable_id] = rec.raw.countCardByGroup;
								removeIndexByGroup[rec.raw.CmpGroupTable_id] = index;
							}
							else
							{
								//rec.data.Person_Birthday = cntr.person_age(new Date(rec.raw.CmpCallCard_prmDate), rec.raw.Person_Birthday,rec.raw.Person_Age);

								if(Ext.Array.contains(groups,typeof rec.raw.CmpGroupTable_id))
									groups.push(rec.raw.CmpGroupTable_id);

								//проверка отложенных вызовов
								if((rec.raw.CmpCallCardStatusType_id == 19) && (rec.raw.isTimeDefferedCall == 2)){

									var rightContainerEl,
										defNoticeWindow = Ext.getCmp('defNotice');
									if(cntr.modeView && cntr.modeView != 'default'){
										rightContainerEl= win.down('form[refId=CallDetailPanel]').el;
									}else{
										rightContainerEl = panelTeams.el;
									}
									if(defNoticeWindow){
										defNoticeWindow.destroy();
									}

									if(cntr.isNmpArm){
										//"автоматический" вывод отложенного вызова
										cntr.setDefferedCallToTransmitted(rec,callsView);
									}
									else{
										var alertBox = Ext.create('Ext.window.Window', {
											title: 'Сообщение',
											height: 120,
											width: 300,
											id: 'defNotice',
											constrain: true,
											header: false,
											constrainTo: rightContainerEl,
											layout: {
												type: 'hbox',
												align: 'middle'
											},
											bodyBorder: false,
											items: [
												{
													xtype: 'label',
													flex: 1,
													html: "Внимание! Наступило время обслуживания отложенного вызова на " + rec.raw.Person_FIO + ". Перевести вызов в работу?"
												}
											],
											fbar: [
												{
													xtype: 'button',
													text: 'Да',
													handler: function () {
														cntr.setDefferedCallToTransmitted(rec,callsView);
														alertBox.close();
													}
												}, {
													xtype: 'button',
													text: 'Нет',
													handler: function () {
														cntr.showDefferedCallWindow(rec,callsView)
														alertBox.close();
													}
												}
											]
										});
										alertBox.showAt([rightContainerEl.getWidth()/2+rightContainerEl.getLocalX()-150, rightContainerEl.getHeight()-40]);
									}
								}

								//Проверка вызовов на решении ДП
								if(rec.get('CmpGroup_id') == 0) {
									switch (true) {
										case (!cntr.isNmpArm && rec.get('hasEventDeny') && rec.get('CmpCallCardStatusType_id') == 21):
										{
											cntr.showAcceptDenyCallMessage(rec, storeCalls, callsView);
											break;
										}
										case (!cntr.isNmpArm && rec.get('CmpCallCardStatusType_id') == 21 && rec.get('CmpCallType_Code').inlist([1, 2]) && !rec.get('hasEventDeny')):
										{
											cntr.showEditLpuBuildingMessage(rec, storeCalls, callsView);
											break;
										}
										case !Ext.isEmpty(rec.get('CmpPPDResult_id')):
										{
											cntr.showUnresultSolutionMsg(rec, storeCalls, callsView);
											break;
										}
										default :
										{
											cntr.showDPSolutionMsg(rec, storeCalls, callsView);
										}
									}
								}
							}
						});
						if(!records){
							callRecordsBeforeLoad = [];
						}
						else if(records.length == 0){
							callRecordsBeforeLoad = [];
						}
						else if(!callRecordsBeforeLoad){
							callRecordsBeforeLoad = [];
							st.each(function(rec,index){callRecordsBeforeLoad.push(rec.raw.CmpCallCard_id)})
						}
						newRecordsAfterLoad = [];
						cntr.getMapPanelRef().removeOldMarkers(st);
						cntr.setLpuBuildingsOnMap();

						st.each(function(rec,index) {
							if(typeof rec !== 'undefined'){
								if(typeof rec.raw.CmpCallCard_id !== 'undefined')
								{
									rec.data.countCardByGroup = counts[rec.raw.CmpGroupTable_id];
									rec.raw.countCardByGroup = counts[rec.raw.CmpGroupTable_id];
									if(callRecordsBeforeLoad ){
										if(!rec.raw.CmpCallCard_id.inlist(callRecordsBeforeLoad) ){
											//звуковой сигнал только при активной настройке и статусе вызова "Передано", "Передано из 112"
											if(rec.raw.IsSignalBeg == '2' && rec.raw.CmpCallCardStatusType_id.inlist([1,20])){
												Ext.select('#DispatchStantionWP_newCmpCallCardAudio').first().dom.play();
											}
											callRecordsBeforeLoad.push(rec.raw.CmpCallCard_id);
										}
									}
									if(rec.raw.isNewCall){
										newRecordsAfterLoad.push(rec.raw.CmpCallCard_id)
									}

									if(cntr.getMapPanelRef().isVisible())
										cntr.getMapPanelRef().setCmpCallCardMarkerInList(rec,function(point){});
								}
								else
								{
									if(counts[rec.raw.CmpGroupTable_id] != 0)
									{
										//removeIndexByGroupFiltered[rec.raw.CmpGroupTable_id] = index;
										//st.removeAt(index);
										//st.remove(rec);
									}
								}


							}


						});
						//st.remove(removeIndexByGroupFiltered);

						if(typeof cntr.modeView !== 'undefined' && Ext.Array.contains(modeViewArray, cntr.modeView))
						{
							for(var groupIndex in groupingFeature.stateOfGropups){
								if(groupingFeature.stateOfGropups[groupIndex].isCollapsed){									
									groupingFeature.collapse(groupIndex, false);
								}
								else{
									//если и так раскрыта, то зачем ее раскрывать опять
									if(!groupingFeature.isExpanded(groupIndex))
										groupingFeature.expand(groupIndex, false);
								}
							}
						}
						else{
							st.each(function(rec,index) {
								if(typeof rec !== 'undefined' && typeof rec.raw.CmpCallCard_id == 'undefined')
									st.remove(rec);
							});
						}

						//Уфа хочет свою сортировку после перезагрузки стора
						if(!getRegionNick().inlist(['ufa'])){
							cntr.sortCmpCalls();
						}

						if(focusedCmp && Ext.WindowManager.getActive() == cmp){
							focusedCmp.focus();
						}
					});
					
					storeTeams.on('beforeload', function(store, operation){
						if(spanElTeamsLoadingTool.hasCls('hiddentool'))spanElTeamsLoadingTool.removeCls('hiddentool');
					});
					storeTeams.on('load', function( st, records, successful){
						var newETCallCards = {};
						var alertAutoStartDuty = [];
						var alertAutoFinishDuty = [];
						var firstload = (EmergencyTeamsCallCards == null);

						if(firstload) EmergencyTeamsCallCards = {};

						if(records){

							records.forEach(function(rec){
								//Наступило время дежурства бригады (alertToStartVigil - ид дежурства)
								if(rec.get('alertToStartVigil'))
								{
									var txt = 'Наступило время дежурства бригады '+rec.get('EmergencyTeam_Num')+ '. Перевести бригаду в статус «Дежурство»?',
										buttons = [
											{
												xtype: 'button',
												text: 'Да',
												handler: function () {													
													var params = {
														'EmergencyTeamStatus_Code': 50, //«Дежурство»
														'EmergencyTeam_id':	rec.get('EmergencyTeam_id'),
														'ARMType': (sw.Promed.MedStaffFactByUser.last != null) ? sw.Promed.MedStaffFactByUser.last.ARMType : sw.Promed.MedStaffFactByUser.current.ARMType
													};
													cntr.setEmergencyTeamStatus(params, teamsView, callsView);
													
													var params = {
														CmpEmTeamDuty_id: rec.get('alertToStartVigil'),
														CmpEmTeamDuty_FactBegDT: Ext.Date.format(new Date(), 'm.d.Y H:i')
													};
													cntr.setVigiltimes(params, function(){
														Ext.ComponentQuery.query('redNotifyWindow')[0].close();
													});
												}
											},
											{
												xtype: 'tbfill'
											},
											{
												xtype: 'button',
												text: 'Нет, отменить дежурство',
												handler: function () {
													var params = {
														CmpEmTeamDuty_id: rec.get('alertToStartVigil')														
													};
													
													cntr.deleteVigil(params, function(){
														Ext.ComponentQuery.query('redNotifyWindow')[0].close();
													});
												}
											},
											{
												xtype: 'button',
												text: 'Нет, отложить дежурство',
												handler: function () {
													Ext.ComponentQuery.query('redNotifyWindow')[0].close();
												}
											}
										];
										
									cntr.showNotify( txt, buttons, 100 );
								}
								
								//Наступило время дежурства бригады (alertToStartVigil - ид дежурства)
								if(rec.get('alertToEndVigil'))
								{
									var txt = 'Время дежурства бригады '+rec.get('EmergencyTeam_id')+ '. истекло. Перевести бригаду в статус «Свободна»?»',
										buttons = [
											{
												xtype: 'button',
												text: 'Да',
												handler: function () {													
													var params = {
														'EmergencyTeamStatus_Code': 13, //«Свободна»
														'EmergencyTeam_id':	rec.get('EmergencyTeam_id'),
														'ARMType': (sw.Promed.MedStaffFactByUser.last != null) ? sw.Promed.MedStaffFactByUser.last.ARMType : sw.Promed.MedStaffFactByUser.current.ARMType
													};
													cntr.setEmergencyTeamStatus(params, teamsView, callsView);
													
													var params = {
														CmpEmTeamDuty_id: rec.get('alertToEndVigil'),
														CmpEmTeamDuty_FactEndDT: Ext.Date.format(new Date(), 'm.d.Y H:i')
													};
													cntr.setVigiltimes(params, function(){
														Ext.ComponentQuery.query('redNotifyWindow')[0].close();
													});
												}
											},
											{
												xtype: 'tbfill'
											},
											{
												xtype: 'button',
												text: 'Нет',
												handler: function () {
													Ext.ComponentQuery.query('redNotifyWindow')[0].close();
												}
											}
										];
										
									cntr.showNotify( txt, buttons, 100 );
								}
								
								//@todo функционал нужно оптимизировать - проводить проверку на сервере
								//статус Обед и нет отказа в уведомлении об окончании Обеда
								if(rec.get('EmergencyTeamStatus_Code').inlist([9,40]) && EmergencyTeamNotNotify.indexOf(rec.get('EmergencyTeam_id')) == -1) {
									
									Ext.Ajax.request({
										url: '/?c=EmergencyTeam4E&m=checkLunchTimeOut',
										params: {
											EmergencyTeam_id: rec.get('EmergencyTeam_id')
										},
										callback: function (opt, success, response) {
											if (success) {
												var response_obj = Ext.JSON.decode(response.responseText);
												if(response_obj[0].LunchTimeOut == 2){

													//var breakType = params.EmergencyTeamStatus_Code == '9' ? '«Обед»' : '«Ужин»';

													var msg = getRegionNick().inlist(['ufa']) ? "Время приёма пищи " : "Время обеда ";

													Ext.MessageBox.confirm('Сообщение',
														msg + 'бригады №' + rec.get('EmergencyTeam_Num') + ' закончилось. Перевести бригаду в статус «Свободна»?', function (btn) {
															if (btn === 'yes') {
																var params = {
																	'EmergencyTeamStatus_Code': 13, //Свободна
																	'EmergencyTeam_id':	rec.get('EmergencyTeam_id'),
																	'ARMType': (sw.Promed.MedStaffFactByUser.last != null) ? sw.Promed.MedStaffFactByUser.last.ARMType : sw.Promed.MedStaffFactByUser.current.ARMType
																};
																cntr.setEmergencyTeamStatus(params, teamsView, callsView);
															}else{
																EmergencyTeamNotNotify.push(rec.get('EmergencyTeam_id'));
															}
														}.bind(this))
												}
											}
										}
									});

								}

								//Сохраняем список бригад со статусами для звукового оповещения
								var ETCallCard = rec.get('CmpCallCard_id'),
									ET = rec.get('EmergencyTeam_id');

								if((EmergencyTeamsCallCards[ET] && EmergencyTeamsCallCards[ET].CmpCallCard_id > 0) && (ETCallCard == 0)){
									//Включаем звуковое оповещение, если позволяют настройки
									if(rec.get('IsSignalEnd') == '2'){
										Ext.select('#DispatchStantionWP_newCmpCallCardAudio').first().dom.play();
									}
								}

								//Автоматическая печать КТ для подчиненных подстанций
								if((getGlobalOptions().SmpUnitType_Code == 2) && (getGlobalOptions().SmpUnitParam_IsKTPrint == 2) && (EmergencyTeamsCallCards[ET] != undefined)
									&& (ETCallCard > 0) && (EmergencyTeamsCallCards[ET].CmpCallCard_id != ETCallCard)){

									var rightContainerEl;
									if(cntr.modeView && cntr.modeView != 'default'){
										rightContainerEl= win.down('form[refId=CallDetailPanel]').el;
									}else{
										rightContainerEl = panelTeams.el;
									}

									var AutoPrintKT = Ext.create('Ext.window.Window', {
										title: 'Сообщение',
										height: 120,
										width: 300,
										constrain: true,
										header: false,
										constrainTo: rightContainerEl,
										layout: {
											type: 'hbox',
											align: 'middle'
										},
										bodyBorder: false,
										items: [
											{
												xtype: 'label',
												flex: 1,
												html: 'Бригада №' + rec.get("EmergencyTeam_Num") + ' назначена на вызов №' + rec.get("CmpCallCard_Numv") + '.' + '</br>Распечатать контрольный талон?'
											}
										],
										fbar: [
											{
												xtype: 'button',
												text: 'Да',
												handler: function () {
													if (getRegionNick().inlist(['ufa', 'krym'])) {
														var location = '/?c=CmpCallCard&m=printCmpCallCardHeader&CmpCallCard_id=' + ETCallCard;
														var win = window.open(location);
													} else {
														cntr.printControlBill({
															EmergencyTeam_id: ET,
															CmpCallCard_id: ETCallCard
														});
													}
													AutoPrintKT.close();
												}
											}, {
												xtype: 'button',
												text: 'Нет',
												handler: function () {
													AutoPrintKT.close();
												}
											}
										]
									});
									AutoPrintKT.showAt([rightContainerEl.getWidth()/2+rightContainerEl.getLocalX()-150, rightContainerEl.getHeight()-40]);

								}
								
								newETCallCards[ET] = {
									CmpCallCard_id: ETCallCard, 
									EmergencyTeam_Num: rec.get('EmergencyTeam_Num'),
									EmergencyTeamStatus_Code: rec.get('EmergencyTeamStatus_Code')
								};
							})

						}

						if(successful){
							var newTeam = [];
							//Сравним старый список бригад с новым для оповещения о выходе\закрытии смены
							for(var team_id in newETCallCards){
								if(!EmergencyTeamsCallCards[team_id]){
									alertAutoStartDuty.push(newETCallCards[team_id].EmergencyTeam_Num);
								
									if(!newETCallCards[team_id].EmergencyTeamStatus_Code){
										newTeam.push(team_id);
									}
								}else{
									delete EmergencyTeamsCallCards[team_id]
								}
							}
							if(!firstload && alertAutoStartDuty.length > 0){
								newTeam.forEach(function(id) {
									var params = {
										'EmergencyTeamStatus_Code': 13, //«Свободна»
										'EmergencyTeam_id':	id,
										'ARMType': (sw.Promed.MedStaffFactByUser.last != null) ? sw.Promed.MedStaffFactByUser.last.ARMType : sw.Promed.MedStaffFactByUser.current.ARMType
									};
									cntr.setEmergencyTeamStatus(params, teamsView, callsView);
								});
								cntr.showYellowMsg("Бригада " + alertAutoStartDuty.join(', ') + " выведена на смену.", 3000);
							}

							if(!firstload && Object.keys(EmergencyTeamsCallCards).length > 0){
								for(var team_id in EmergencyTeamsCallCards){
									alertAutoFinishDuty.push(EmergencyTeamsCallCards[team_id].EmergencyTeam_Num)
								}

								if(alertAutoFinishDuty.length > 0){
									cntr.showYellowMsg("Смена бригады " + alertAutoFinishDuty.join(', ') + " закрыта.", 3000);
								}
							}

							EmergencyTeamsCallCards = newETCallCards;
						}
						else{
							st.removeAll();
						}

						if(!spanElTeamsLoadingTool.hasCls('hiddentool'))spanElTeamsLoadingTool.addCls('hiddentool');
					});
					

					storeCalls.load({
						params: {
							begDate: Ext.Date.format(Ext.Date.add(new Date(), Ext.Date.DAY, -1), 'd.m.Y'),
							endDate: Ext.Date.format(new Date(), 'd.m.Y')
						},
						callback: function(records,b,c){
							cntr.setTitleServedTap();
							/*
							if(groupingFeature.groupCache){
								groupingFeature.collapse(4, true);
								groupingFeature.collapse(5, true);
								groupingFeature.collapse(6, true);
								
								switch(cntr.modeView){
									case 'table':
										//storeCalls.add({CmpGroupTable_id: 2},{CmpGroupTable_id: 3},{CmpGroupTable_id: 4},{CmpGroupTable_id: 5});
										//storeCalls.group('CmpGroupTable_id','ASC');
										break;
									case 'closed':
										groupingFeature.expand(4, true);
										break;
									case 'cancel':
										groupingFeature.expand(5, true);
										break;
								}
							}
							*/
						}
					});	
					

					/*if (getGlobalOptions().region.nick == 'ufa') {						
						storeTeams.on('load', function( store, records, successful, eOpts) {
							store.filterBy(function(rec){
								return false;
							});
						});
					}*/
					//storeTeams.load();
					
					//Поскольку на Уфе объектов на данный момент около 180 и групп порядка 45
					//По умолчанию все группы скрываем, соответственно должны быть скрыты все маркеры
					/*
					if (getGlobalOptions().region.nick == 'ufa' || getGlobalOptions().region.nick == 'krym') {
						
						this.getStore('stores.smp.GeoserviceTransportGroupStore').on('load', function( store, records, successful, eOpts) {
							if(!records) return;
							for (var i = 0; i < records.length; i++){
								records[i].set('visible',false);
							};
							storeTeams.load();
							//Прячем все отображаемые маркеры, если на момент обновления стора групп они отобразились
							this.filterEmergencyTeamListWithMarkersBySelectedGroupList();
						}.bind(this));
					}
					else{
						storeTeams.load();		
					}*/
					
					storeTeams.load();

					this.getStore('stores.smp.GeoserviceTransportGroupStore').load();
					this.regionalCrutches();
				},
				render: function(cmp){
					var storeCalls = Ext.data.StoreManager.lookup('common.DispatcherStationWP.store.CmpCallsStore'),
						storeTeams = Ext.data.StoreManager.lookup('common.DispatcherStationWP.store.EmergencyTeamStore'),
						geoservice_store = this.getGeoserviceTransportStore(),
						shortCallsGrid = cmp.down('grid[refId=callsCardsShortGrid]'),
						teamsShortGrid = cmp.down('grid[refId=teamsShortGrid]'),
						groupingFeature = shortCallsGrid.getView().features[0],
						cntr = this;

					cntr.isNmpArm = cmp.isNmpArm;

					Ext.Ajax.request({
						url: '/?c=CmpCallCard4E&m=getOperDepartamentOptions',
						callback: function (opt, success, response) {
							if(success && response.responseText){
								var res = Ext.JSON.decode(response.responseText);
								cntr.operDepartamentOptions = res;
								if(res.SmpUnitParam_IsShowCallCount == 'false'){
									this.columns.find(function(a,b,c){
										if(a.dataIndex == 'countcallsOnTeam'){
											a.hide();
										};
									});
								};
							};
						}.bind(shortCallsGrid)
					});

					cmp.geoservice_store = geoservice_store;
					
					// @TODO: Сделать единую загрузку для всех регионов
					// @TODO: Соответственно переделать карты виалон под единый интерфейс всех классов карт
					
					cmp.geoservice_store.on( 'load' , function( store , records, successful, eOpts) {

						if(!records || (records.length==0)) return;
						var marker = {};
						var markerList = [];

						for (var i = 0; i < records.length; i++) {
							
							var teamIndex = storeTeams.findExact('GeoserviceTransport_id', records[i].data.GeoserviceTransport_id);
							
							if (!records[i].data || (teamIndex == -1) ) {
								continue; 
							}
							
							var teamRec = storeTeams.getAt(teamIndex);

							marker = {
								id: records[i].data.GeoserviceTransport_id,
								//title: records[i].data.GeoserviceTransport_name,
								title: teamRec.get('EmergencyTeam_Num')+' '+teamRec.get('EmergencyTeamStatus_Name'),
								point: [ records[i].data.lat , records[i].data.lng ],
								direction: records[i].data.direction,
								statusCode: teamRec.get('EmergencyTeamStatus_Code')
							}

							markerList.push(marker);
						};
						
						//log({markerList:markerList});
						this.getMapPanelRef().deleteOldAmbulanceMarkers(markerList)
						for (i = 0; i < markerList.length; i++) {
							this.getMapPanelRef().setAmbulanceMarker(markerList[i]);
						};

						this.filterEmergencyTeamListWithMarkersBySelectedGroupList();
						
						
					},  this);
					
					storeTeams.on('load', function( store, records, successful, eOpts) {
						cntr.filterEmergencyTeamListWithMarkersBySelectedGroupList();

						teamsShortGrid.getPlugin().applyFilters(true);
						storeTeams.each(function(record){
							cntr.onlineEt.forEach(function(item) {
								if (item == record.get('EmergencyTeam_id')) {
									//	$('#onlinestatus'+item).show();
									var teamInStore = storeTeams.findRecord('EmergencyTeam_id', item);
									if (teamInStore) {
										teamInStore.set('isOnline', '1');
									}
								}
							});											
						});

						cmp.geoservice_store.getExtraParamsFn = function () {
							return {
								'filtertransport_ids': JSON.stringify(
									(storeTeams.count()) ? storeTeams.collect('GeoserviceTransport_id') : [0]
								)
							};
						};
						cmp.geoservice_store.runAutoRefresh();

					});
					
					// @TODO: почему затирается гриб бригад.
					//
					

					Ext.ComponentQuery.query('button[refId=buttonChooseArm]')[0].on(
						'afterSelectArm', function(){
							cntr.reloadStores();							
					});

					//Обновление истории управление подстанциями
					Ext.Ajax.request({
						url: '/?c=CmpCallCard4E&m=updateSmpUnitHistoryData'
					});

					var timeRefreshArm = cntr.isNmpArm ? 120000 : 20000; //Для нмп армов время обновления 2 минуты, для смп = 20 сек
					//проверка на подключенный ноджс
					//если нет то вручную апдейт при условии что окно активно

					//интервал только для нмп арма
					if (cntr.isNmpArm) {
						setInterval(function(){
							var activeWin = Ext.WindowManager.getActive();

							if (cmp==activeWin){
								if (cmp.socket){
									if (!cmp.socket.connected){
										//Не обновляем НМП арм если применены фильтры
										if(!(cntr.isNmpArm && storeCalls.isFiltered())) {
											this.reloadStores();
										}
										this.timeControlRefresh_flag = false;
									}
									else{
										this.timeControlRefresh_flag = true;
									}
								}
								else{
									//Не обновляем НМП арм если применены фильтры
									if(!(cntr.isNmpArm && storeCalls.isFiltered())){
										this.reloadStores();
									}



									this.timeControlRefresh_flag = false;
								}
							}
						}.bind(this),timeRefreshArm);
					}
					
					
					if (cmp.socket){
						//листенер изменения онлайн-статуса бригады
						cmp.socket.on('changeEmergencyTeamOnline', function(data) {
							var	onlineTeams = JSON.parse(data);
							cntr.onlineEt = [];
							//$('.et-online').hide();
							onlineTeams.forEach(function(item) {
								//$('#onlinestatus'+item).show();
								var teamInStore = storeTeams.findRecord('EmergencyTeam_id', item);
								if (teamInStore) {
									cntr.onlineEt.push(item);
									teamInStore.set('isOnline', '1');
								}
							});
							
						});
						//листенер выхода бригады на смену
						cmp.socket.on('setEmergencyTeamDutyTime', function(data) {
							storeTeams.reload();
							log('NODE listen setEmergencyTeamDutyTime');
						});
						//листенер на изменение бригады
						cmp.socket.on('changeEmergencyTeamStatus', function (data, action) {
							var	win = this.getEmergecyWindowRef(),
								teamsView = win.TeamsView,
								emergencyTeam = JSON.parse(data),
								teamInStore = storeTeams.findRecord('EmergencyTeam_id', emergencyTeam[0].EmergencyTeam_id),
								storeStatuses = Ext.data.StoreManager.lookup('common.DispatcherDirectWP.store.EmergencyTeamStatusStore'),
								rec = storeStatuses.findRecord('EmergencyTeamStatus_id', emergencyTeam[0].EmergencyTeamStatus_id);
								
							if(teamInStore){
								switch(action){
									case 'changeStatus': {
										/*
										var statusCode = rec.get('EmergencyTeamStatus_Code');

										switch(statusCode){
											case 4 : 
											{
												//конец обслуживания
												storeCalls.reload();
												break;
											}
										};
										*/		
										storeTeams.sort();
										storeTeams.reload();
										teamsView.refresh();
										// перезагрузим вызовы в любом случае.
										storeCalls.reload();
									break;};
									case 'deleteTimeInterval': {
										var storeDutyId = teamInStore.data.EmergencyTeamDuty_id,
											deleteRecDutyId = emergencyTeam[0].EmergencyTeamDuty_id;
										
										if (parseInt(storeDutyId) == deleteRecDutyId){
											storeTeams.remove(teamInStore)
											storeTeams.sort();
											teamsView.refresh();
										}
										
									break;};
									case 'changeTimeInterval':{
										teamInStore.set('EmergencyTeamDuty_DTStart', emergencyTeam[0].EmergencyTeamDuty_DTStart);
										teamInStore.set('EmergencyTeamDuty_DTFinish', emergencyTeam[0].EmergencyTeamDuty_DTFinish);
										storeTeams.sort();
										teamsView.refresh();
									break;}; 
								}
							}
						}.bind(this));	
						
						cmp.socket.on('addTimeEmergencyTeams', function(data){
							var win = this.getEmergecyWindowRef(),
								teamsView = win.TeamsView;
							
								storeTeams.add(data);					
							
							teamsView.refresh();
						}.bind(this));
						// Обновление списка бригад по таймеру
						cmp.socket.on('updateEmergencyTeamOperEnvForSmpUnit',function(EmergencyTeamsList,action) {
							storeTeams.loadData(EmergencyTeamsList);
						});
						//листенер на изменение статуса карты
						cmp.socket.on('changeCmpCallCard', function (data, action) {
							log('node listen changeCmpCallCard', action)
							var	win = this.getEmergecyWindowRef(),
								callsView = win.CallsView,
								cmpCard = JSON.parse(data),
								cardInStore = storeCalls.findRecord('CmpCallCard_id', cmpCard.CmpCallCard_id);

								switch(action){
									case 'addCall': {
										//листенер на добавление карты
										//storeCalls.add(cmpCard);
										//callsView.refresh();
										cntr.reloadStores();
									break;};
									case 'boostCall': {
										//ускорение карты
										if(cardInStore){
											cardInStore.set('CmpCallCard_BoostTime', cmpCard.CmpCallCard_BoostTime)
										}
										callsView.refresh();
									break;};
									case 'feelBadlyCall': {
										//добавление повторной причины + смена срочности
										if(cardInStore){
											cardInStore.set('CmpSecondReason_id', cmpCard.CmpSecondReason_id);
											cardInStore.set('CmpSecondReason_Name', cmpCard.CmpSecondReason_Name);
											cardInStore.set('CmpCallCard_Urgency', cmpCard.CmpCallCard_Urgency);
										}
										//callsView.refresh();
									break;};
									case 'closeCard': {
										//листенер на закрытие карты
										cntr.reloadStores();
									break;};
								}
							
						}.bind(this));
					}
				},
				afterrender: function(cmp){
					this.setHotKeys(cmp.CallsView);
				}
			},
			'swDispatcherStationWorkPlace swsmpmappanel': {
				afterrender: function( self, eOpts ) {	
					self.currentCallPoint = {};				
				},
				
				afterMapRender: function( self ) {					
					if (typeof self.setMarkers == 'function'){
						//var marks = self.loadBrigadeMarkers();
						//self.setMarkers(marks);
					}
				},
				detectLocation: function(){
					var win = this.getEmergecyWindowRef(),
						callsView = win.CallsView;

					//callsView.focus();
					callsView.getSelectionModel().select(callsView.store.getAt(0));
				},
				onSetRoadTrack: function (time){
					var	win = this.getEmergecyWindowRef(),
						teamsView = win.TeamsView,
						callsView = win.CallsView,						
						selectedTeamRec = teamsView.getSelectionModel().getSelection()[0],
						selectedCallRec = callsView.getSelectionModel().getSelection()[0],
						storeTeams = Ext.data.StoreManager.lookup('common.DispatcherStationWP.store.EmergencyTeamStore'),
						st_id = 0;
						
					if (selectedTeamRec) {
						storeTeams.each(function(record){
							if (selectedTeamRec.get('EmergencyTeam_id') == record.get('EmergencyTeam_id')) {
								st_id = record.get('EmergencyTeamStatus_id');
							}						
						});

						//@todo подумать - нужна ли эта проверка
						if ( st_id == '59') {
							setTimeout(function(){
								Ext.Msg.alert('Ошибка','Бригада в статусе ожидания принятия.');
							},1000);
							return false;
						}

					}
				}
			},
			
			'swDispatcherStationWorkPlace #swDispatcherStationWorkPlace_teamGrid': {
				
				selectionchange: function( self, selected, eOpts ) {
					this.getMapPanelRef().clearRoutes();
				},
				
				itemclick: function( obj, record, item, index, e, eOpts ){
					var elCls = e.target.getAttribute('class'),
						win = this.getEmergecyWindowRef(),
						teamsView = win.TeamsView,
						callsView = win.CallsView,
						storeCalls = Ext.data.StoreManager.lookup('common.DispatcherStationWP.store.CmpCallsStore'),
						//callId = teamsView.getSelectionModel().getSelection()[0].raw.CmpCallCard_id,
						callId = record.get('CmpCallCard_id');

					if(callId){
						var index_selCall = storeCalls.find('CmpCallCard_id',callId),
							record_Call = storeCalls.getAt(index_selCall);
						callsView.getSelectionModel().select(record_Call,false,true);
					}else{
						callsView.getSelectionModel().deselectAll();
					}

					if (elCls){
						if (elCls.indexOf("cell-cmpcc-moreinfo") != -1){
							this.showEmergencyTeamSubMenu(e.getX()-100, e.getY(), callsView, teamsView);
						}
						else{
							this.setCenterEmergencyTeamOnMap( record, false, null, callsView, teamsView );
						}
					}
					else{
						this.setCenterEmergencyTeamOnMap( record, false, null, callsView, teamsView);
					}
					this.toggleFocusClassOnView('EmergencyTeam');
				},
				
				containerclick: function(cmp, e){
					var el = e.target,
					 	elCls = e.target.getAttribute('class'),
						win = this.getEmergecyWindowRef(),
						teamsView = win.TeamsView,
						otherTeams = Ext.query('.other-team-wrapper')[0];

					if(elCls && elCls.indexOf('other-teams-btn') != -1){

						otherTeams.hidden = !otherTeams.hidden;
						teamsView.hideOtherTeams = otherTeams.hidden;
						if(otherTeams.hidden){
							el.innerText = 'Показать бригады других подстанций';
						}else{
							el.innerText = 'Скрыть бригады других подстанций';
						}
					}

					if(elCls && elCls.indexOf('x-grid-group-title') != -1){
						var lpubuilding_id = el.dataset.id,
						wrapper = Ext.query('.group-wrapper-' + lpubuilding_id)[0],
						header = Ext.query('.group-header-' + lpubuilding_id)[0];

						header.classList.toggle('x-grid-group-hd-collapsed')
						wrapper.hidden = !wrapper.hidden;
						teamsView.collapsedGroups[lpubuilding_id] = wrapper.hidden;
					}
					this.toggleFocusClassOnView('EmergencyTeam');
				}
			},
			
			'swDispatcherStationWorkPlace grid[refId=emergencyTeamGroupFilterGrid] checkcolumn': {
				checkchange: function(cmp, rowIndex, checked){
					this.filterEmergencyTeamListWithMarkersBySelectedGroupList();
				},
				headerclick: function( ct, column, e, t, eOpts ){
					
					var el = Ext.fly(column.getId()).select('div.customCheckAll span').first(),
						store = ct.view.store;
				
					el.toggleCls('checkedall');

					store.each(function(record){
						record.set('visible', el.hasCls('checkedall'));
					});
					
					this.filterEmergencyTeamListWithMarkersBySelectedGroupList();
				}
			},
			
			'swDispatcherStationWorkPlace button[refId=EmergencyTeamsDutyMarks]': {
				click: function(){
					if (sw.lostConnection) {
						lostConnectionAlert();
						return false;
					}

					var win = this.getEmergecyWindowRef();

					if (sw.lostConnection) {
						lostConnectionAlert();
						return false;
					}
					
					Ext.create('sw.tools.swSmpEmergencyTeamSetDutyTimeWindow',{
						listeners:{
							setDutyTimeToEmergencyTeams: function(recs){					
								win.TeamsView.getStore().reload();
								if (win.socket) {
									win.socket.emit('setEmergencyTeamDutyTime', recs, function(data){
										log('NODE emit SetEmergencyTeamDutyTime');
									});
								}
							}
						}
					}).show();
				}				
			},
			
			'swDispatcherStationWorkPlace button[refId=hr-splitterTableView]': {
				click: function(cmp){

					var	cntr = this,
						win = cntr.getEmergecyWindowRef(),
						buttonEl = cmp.getEl(),
						callDetailForm = win.down('form[refId=CallDetailPanel]');
					
					buttonEl.toggleCls('right-splitter');
					
					if (buttonEl.hasCls('right-splitter')){
						cmp.setIconCls('left-splitter');
						callDetailForm.hide();						
					}
					else{
						cmp.setIconCls('right-splitter');
						callDetailForm.show();
						
					}
				}
			},
				
			'swDispatcherStationWorkPlace button[refId=hr-splitter]': {
				click: function(cmp){
					var mainWin = cmp.up('window'),
						teamsWin = mainWin.TeamsView.up('panel'),
						callsWin = mainWin.CallsView.up('panel'),
						buttonEl = cmp.getEl();
					
					buttonEl.toggleCls('right-splitter');
					
					if (buttonEl.hasCls('right-splitter')){
						cmp.setIconCls('right-splitter');
						Ext.apply(callsWin, {flex: 1});
						Ext.apply(teamsWin, {flex: 3});
						teamsWin.removeCls('short-view');
						callsWin.addCls('short-view');						
					}
					else{
						cmp.setIconCls('left-splitter');
						Ext.apply(callsWin, {flex: 3});
						Ext.apply(teamsWin, {flex: 1});
						callsWin.removeCls('short-view');
						teamsWin.addCls('short-view');
					}
					callsWin.up('container').doLayout();
				},
				focus: function(){
					var win = this.getEmergecyWindowRef(),
						callsView = win.CallsView,
						teamsView = win.TeamsView;
						
					callsView.focus();
					callsView.getEl().toggleCls('focused-panel');
				}
			},
			'swDispatcherStationWorkPlace button[refId=vr-splitter]': {
				click: function(cmp){
					var mainWin = cmp.up('window'),
						teamsWin = mainWin.TeamsView.up('panel'),
						callsWin = mainWin.CallsView.up('panel'),
						buttonEl = cmp.getEl(),
						mapWin = mainWin.mapPanel;
					
					buttonEl.toggleCls('bottom-splitter');
					if (buttonEl.hasCls('bottom-splitter')){
						cmp.setIconCls('bottom-splitter');
						mapWin.setHeight(300);
						callsWin.addCls('mini-view');
					}
					else{
						cmp.setIconCls('top-splitter');
						mapWin.setHeight(0);
						callsWin.removeCls('mini-view');
					}
					if (mapWin.getCurrentMapType() == 'google'||'wialon')
					{
						if(typeof google != 'undefined')google.maps.event.trigger(mapWin.getPanelByType(mapWin.getCurrentMapType()).map,'resize');
					}
					if (mapWin.getCurrentMapType() == 'here')
					{
						var hereMap = mapWin.getPanelByType(mapWin.getCurrentMapType());
						hereMap.map?hereMap.map.getViewPort().resize():false;
					}
					if (mapWin.getCurrentMapType() == 'yandex'){
						if(typeof ymaps != 'undefined')
							mapWin.getPanelByType(mapWin.getCurrentMapType()).map.container.fitToViewport();
					}
					mapWin.up('container').doLayout();
				}
			},
			'swDispatcherStationWorkPlace button[refId=refresh]': {
				click: function(cmp){					
					var map_panel = this.getMapPanelRef().getPanelByType( this.getMapPanelRef().getCurrentMapType() );

					map_panel.refreshItems();
				}
			},
			'swDispatcherStationWorkPlace menuitem[refId=editEmergencyTeamAuto]': {
				click: function(cmp){
					if (sw.lostConnection) {
						lostConnectionAlert();
						return false;
					}

					//шаблоны
					var EmergencyTeamTemplateWindow = Ext.create('common.DispatcherStationWP.tools.swEmergencyTeamAutoWindow', {
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
			'swDispatcherStationWorkPlace menuitem[refId=editEmergencyTeamTemplate]': {
				click: function(cmp){
					if (sw.lostConnection) {
						lostConnectionAlert();
						return false;
					}

					//шаблоны
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
			'swDispatcherStationWorkPlace menuitem[refId=setEmergencyTeamDutyTime]': {
				click: function(cmp){
					if (sw.lostConnection) {
						lostConnectionAlert();
						return false;
					}

					//Формирование наряда
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
			'swDispatcherStationWorkPlace menuitem[refId=currentEmergencyTeamStuff]': {
				click: function(cmp){
					if (sw.lostConnection) {
						lostConnectionAlert();
						return false;
					}

					//текущий наряд
					var CurrentEmergencyTeamStuff = Ext.create('common.DispatcherStationWP.tools.swCurrentEmergencyTeamStuff',
					{
						layout: {
							type: 'fit',
							align: 'stretch'
						},
						maximized: true,
						constrain: true,
						renderTo: Ext.getCmp('inPanel').body
					});
					CurrentEmergencyTeamStuff.show();
				}
			},
			'swDispatcherStationWorkPlace menuitem[refId=saveEmergencyTeamsIsComingWindow]': {
				click: function(cmp){
					//Отметка о выходе на смену
					var EmergencyTeamsIsComingWindow = Ext.create('common.DispatcherStationWP.tools.swSaveEmergencyTeamsIsComingWindow',
					{
						layout: {
							type: 'fit',
							align: 'stretch'
						},
						maximized: true,
						constrain: true,
						renderTo: Ext.getCmp('inPanel').body
					});
					EmergencyTeamsIsComingWindow.show();
				}
			},
			'swDispatcherStationWorkPlace menuitem[refId=saveEmergencyTeamsIsCloseWindow]': {
				click: function(cmp){
					//Отметка о выходе на смену
					var EmergencyTeamsIsCloseWindow = Ext.create('common.DispatcherStationWP.tools.swSaveEmergencyTeamsIsCloseWindow',
					{
						layout: {
							type: 'fit',
							align: 'stretch'
						},
						maximized: true,
						constrain: true,
						renderTo: Ext.getCmp('inPanel').body
					});
					EmergencyTeamsIsCloseWindow.show();
				}
			},
			
			'swDispatcherStationWorkPlace toolbar[refId=DPTopToolbar]': {
				render: function(cmp){

				}
			},
			
			'swDispatcherStationWorkPlace menuitem[refId=farmacyRegisterWindow]': {
				click: function(cmp){
					if (sw.lostConnection) {
						lostConnectionAlert();
						return false;
					}

					Ext.create('sw.tools.swSmpFarmacyRegisterWindow').show();
				}
			},			
			'swDispatcherStationWorkPlace menuitem[refId=dispatchOperEnvWindow]': {
				click: function(cmp){
					if (sw.lostConnection) {
						lostConnectionAlert();
						return false;
					}

					Ext.create('sw.tools.swDispatchOperEnvWindow').show();
				}
			},
			'swDispatcherStationWorkPlace menuitem[refId=smpWaybillsViewWindow]': {
				click: function(cmp){
					if (sw.lostConnection) {
						lostConnectionAlert();
						return false;
					}

					Ext.create('sw.tools.swWaybillsWindow').show();
				}
			},
			'swDispatcherStationWorkPlace menuitem[refId=aktivSmp]': {
				click: function(cmp){
					if (sw.lostConnection) {
						lostConnectionAlert();
						return false;
					}

					Ext.create('sw.tools.swAktivJournal').show();
				}
			},
			'swDispatcherStationWorkPlace menuitem[refId=unformalizedAddressDirectoryEditWindow]': {
				click: function(cmp){
					if (sw.lostConnection) {
						lostConnectionAlert();
						return false;
					}

					Ext.create('sw.tools.swUnformalizedAddressDirectoryEditWindow').show();
				}
			},
			'swDispatcherStationWorkPlace menuitem[refId=tabletComputersWindow]': {
				click: function(cmp){
					if (sw.lostConnection) {
						lostConnectionAlert();
						return false;
					}

					Ext.create('sw.tools.swTabletComputersWindow').show();
				}
			},
			'swDispatcherStationWorkPlace menuitem[refId=statisticReports]': {
				click: function(cmp){
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
			'swDispatcherStationWorkPlace menuitem[refId=DecigionTreeEditWindow]': {
				click: function(cmp){
					if (sw.lostConnection) {
						lostConnectionAlert();
						return false;
					}

					//Ext.create('common.HeadDutyWP.tools.swDecigionTreeEditWindow').show();
					Ext.create('common.DispatcherStationWP.tools.swDecigionTreeEditWindow').show();
				}
			},

			'#swDispatcherStationWorkPlace_callsWin #swDispatcherStationWorkPlace_callsGrid': {

				selectionchange: function( self, selected, eOpts ) {
					var cntr = this,
						mapPanel = cntr.getMapPanelRef(),
						win = this.getEmergecyWindowRef(),
						callsView = win.CallsView,
						teamsView = win.TeamsView;

					//если карта готова
					if( mapPanel.getPanelByType(mapPanel.curMapType).isRendered)
					mapPanel.clearRoutes();


					cntr.getStore('common.DispatcherStationWP.store.EmergencyTeamStore').each(function(record) {
						record.set('EmergencyTeamDistance', null);
						record.set('EmergencyTeamDuration', null );
						record.set('EmergencyTeamDistanceText', null );
						record.set('EmergencyTeamDurationText', null );
					})
					/*
					if (selected.length == 1 && selected[0].data.CmpCallCardStatusType_id != 4) {
						cntr.offerEmergencyTeamToCmpCallCard( callsView, teamsView );
					}
					if (selected.length == 0) {
						cntr.getMapPanelRef().setAccidentMarker(null);
					}
					*/
				},

				itemcontextmenu: function( grid, record, item, index, event, eOpts ){
					var win = this.getEmergecyWindowRef(),
						callsView = win.CallsView,
						teamsView = win.TeamsView;

					event.preventDefault();
					event.stopPropagation();
					grid.getSelectionModel().select(record, false, true);

					this.showCmpCallCardSubMenu(event.getX(), event.getY(), callsView, teamsView, false);
				},

				itemclick : function(self, store_record, html_element, node_index, event){
					var mainWin = self.up('window'),
						mapPanel = mainWin.mapPanel,
						elCls = event.target.getAttribute('class'),
						coords = [],
						cntr = this,
						win = this.getEmergecyWindowRef(),
						callsView = win.CallsView,
						teamsView = win.TeamsView,
						storeCalls = Ext.data.StoreManager.lookup('common.DispatcherStationWP.store.CmpCallsStore'),
						storeTeams = Ext.data.StoreManager.lookup('common.DispatcherStationWP.store.EmergencyTeamStore'),
						teamId = callsView.getSelectionModel().getSelection()[0].raw.EmergencyTeam_id,
						teamRecord = storeTeams.findBy(function(rec,id){return (id == teamId)}),
						callId = store_record.getId();

					var callIndex = newRecordsAfterLoad.indexOf(callId.toString());
					if(callIndex != -1){
						Ext.select('.x-item-selected').first().removeCls('new-call');
						newRecordsAfterLoad.splice(callIndex, 1)
					}

					if(teamRecord != -1){
						teamsView.getSelectionModel().select(teamRecord,false,true)
					}else{
						teamsView.getSelectionModel().deselectAll()
					}

					if(store_record.get('CmpGroup_id') == 0){
						switch(true){
							case (!cntr.isNmpArm && store_record.get('hasEventDeny') && store_record.get('CmpCallCardStatusType_id') == 21 ):
							{
								cntr.showAcceptDenyCallMessage(store_record, storeCalls, callsView);
								break;
							}
							case (!cntr.isNmpArm && store_record.get('CmpCallCardStatusType_id') == 21 && store_record.get('CmpCallType_Code').inlist([1,2]) && !store_record.get('hasEventDeny')): {
								cntr.showEditLpuBuildingMessage(store_record, storeCalls, callsView);
								break;
							}
							case !Ext.isEmpty(store_record.get('CmpPPDResult_id')): {
								cntr.showUnresultSolutionMsg(store_record, storeCalls, callsView);
								break;
							}
							default : {
								cntr.showDPSolutionMsg(store_record, storeCalls, callsView);
							}
						}
					}

					if (elCls) {
						switch (true) {
							case (elCls.indexOf('select-rid') != -1):{
								var ridRec = storeCalls.findRecord('CmpCallCard_id', event.target.dataset.rid)

								callsView.getSelectionModel().select(ridRec);
								return;
								break;
							}
							case (elCls.indexOf('cell-cmpcc-moreinfo') != -1):{
								this.showCmpCallCardSubMenu(event.getX() - 100, event.getY(), callsView, teamsView, false);
								break;
							}
							case (elCls.indexOf('cell-cmpcc-accept') != -1):{

								var loadMask = new Ext.LoadMask(Ext.getBody(), {msg: "Принятие карты вызова..."});
								loadMask.show();
								Ext.Ajax.request({
									url: '/?c=CmpCallCard4E&m=acceptCmpCallCardByDispatchStation',
									params: {
										CmpCallCard_id: store_record.get('CmpCallCard_id'),
//									CmpCallCardStatusType_id: 2,
										armtype: 'smpdispatcherstation'
									},
									callback: function (opt, success, response) {
										loadMask.hide();
										if (success) {
											var response_obj = Ext.JSON.decode(response.responseText);
											var hide_message = function () {
												Ext.defer(function () {
													Ext.MessageBox.hide();
												}, 1500);
											}

											if (response_obj.success) {
												var dt = new Date(Date.now());
												Ext.data.StoreManager.lookup('common.DispatcherStationWP.store.EmergencyTeamStore').reload();
												Ext.MessageBox.confirm('Сообщение',
													'Бригада №' + store_record.get('EmergencyTeam_Num') + ' назначена на вызов №' + store_record.get('CmpCallCard_Numv') + '.' + '</br>Распечатать карту вызова?', function (btn) {
														if (btn === 'yes') {
															if (getRegionNick().inlist(['ufa', 'krym'])) {
																var location = '/?c=CmpCallCard&m=printCmpCallCardHeader&CmpCallCard_id=' + store_record.get('CmpCallCard_id');
																var win = window.open(location);
															} else {
																cntr.printControlBill({
																	CmpCallCard_id: store_record.get('CmpCallCard_id'),
																	EmergencyTeam_id: store_record.get('EmergencyTeam_id')
																})
																cntr.setCmpCallCardTransToETType({
																	CmpCallCard_id: store_record.get('CmpCallCard_id'),
																	CmpCallCard_Kakp: 1
																});
															}
														} else {
															cntr.setCmpCallCardTransToETType({
																CmpCallCard_id: store_record.get('CmpCallCard_id'),
																CmpCallCard_Kakp: 0
															})
														}
														Ext.Msg.alert('', 'Карта вызова успешно принята');
														hide_message();
													})

												storeCalls.load({
													params: {
														begDate: '01.06.2013',
														endDate: Ext.Date.format(Ext.Date.getLastDateOfMonth(dt), 'd.m.Y')
													}
												});
												storeTeams.load();
												callsView.separatorIsSet = false;
												callsView.refresh();
											} else {
												var error_msg = (response_obj.Error_Msg) ? response_obj.Error_Msg : 'Ошибка принятия карты вызова';
												Ext.Msg.alert('Ошибка', error_msg);
												hide_message();
											}
										}
									}
								});
								break;
							}
							case (elCls.indexOf('cell-cmpcc-reject') != -1):{

								var loadMask = new Ext.LoadMask(Ext.getBody(), {msg: "Отмена вызова..."});
								loadMask.show();
								Ext.Ajax.request({
									url: '/?c=CmpCallCard4E&m=declineCmpCallCardByDispatchStation',
									params: {
										CmpCallCard_id: store_record.get('CmpCallCard_id')
//									CmpCallCardStatusType_id: 8,
									},
									callback: function (opt, success, response) {
										loadMask.hide();
										if (success) {
											var response_obj = Ext.JSON.decode(response.responseText);
											var hide_message = function () {
												Ext.defer(function () {
													Ext.MessageBox.hide();
												}, 1500);
											}

											if (response_obj.success) {
												Ext.Msg.alert('', 'Карта вызова успешно отменена');
												hide_message();
												var dt = new Date(Date.now());
												storeCalls.load({
													params: {
														begDate: '01.06.2013',
														endDate: Ext.Date.format(Ext.Date.getLastDateOfMonth(dt), 'd.m.Y')
													}
												});
												storeTeams.load();
												callsView.separatorIsSet = false;
												callsView.refresh();
											} else {
												var error_msg = (response_obj.Error_Msg) ? response_obj.Error_Msg : 'Ошибка отмены карты вызова';
												Ext.Msg.alert('Ошибка', error_msg);
												hide_message();
											}
										}
									}
								});
								break;
							}
							case (elCls.indexOf('cell-cmpcc-close') != -1):{

								if (getRegionNick().inlist(['ufa', 'krym', 'perm', 'ekb', 'astra', 'kz', 'komi'])) {
									var action = store_record.get('CmpCloseCard_id') > 0 ? 'edit' : 'add';
									cntr.showCmpCloseCardFromExt2(false, store_record.get('CmpCallCard_id'), action);
								} else {
									var win = Ext.create('sw.tools.swCmpCloseCardShortWindow').show({
										CmpCallCard_id: store_record.get('CmpCallCard_id'),
										action: 'edit',
										callback: function (data) {
											if (!data || !data['CmpCallCard_id']) {
												Ext.Msg.alert('Ошибка', 'Ошибка передачи данных из формы закрытия вызова');
											}
											Ext.MessageBox.confirm('Сообщение',
												'Распечатать контрольный талон?', function (btn) {
													if (btn === 'yes') {
														if (getRegionNick().inlist(['ufa', 'krym'])) {
															var location = '/?c=CmpCallCard&m=printCmpCallCardHeader&CmpCallCard_id=' + data['CmpCallCard_id'];
															var win = window.open(location);
														} else {
															this.printCloseTalonBill({CmpCallCard_id: data['CmpCallCard_id']});
														}
													}
												}.bind(this))

											storeCalls.reload();
											callsView.separatorIsSet = false;
											callsView.refresh();
										}.bind(this)
									});
								}
								break;
							}
							case (elCls.indexOf('cell-cmpcc-showclosed') != -1):{
								if (getRegionNick().inlist(['ufa', 'krym', 'perm', 'ekb', 'komi'])) {
									var action = store_record.get('CmpCloseCard_id') > 0 ? 'edit' : 'add';
									cntr.showCmpCloseCardFromExt2(false, store_record.get('CmpCallCard_id'), action);
								} else {
									var win = Ext.create('sw.tools.swCmpCloseCardShortWindow').show({
										CmpCallCard_id: store_record.get('CmpCallCard_id'),
										action: 'show',
										callback: function (data) {

										}.bind(this)
									});
								}
								break;
							}
							case (elCls.indexOf('cell-cmpcc-showprint') != -1):{

								if (getRegionNick().inlist(['ufa', 'krym'])) {
									var location = '/?c=CmpCallCard&m=printCmpCloseCard110&CmpCallCard_id=' + store_record.get('CmpCallCard_id');
									var win = window.open(location);
								} else {
									Ext.Ajax.request({
										url: '/?c=CmpCallCard4E&m=printCmpCallCardCloseTicket',
										params: {
											CmpCallCard_id: store_record.get('CmpCallCard_id')
										},
										callback: function (opt, success, response) {
											if (success) {
												Ext.MessageBox.confirm('Сообщение', 'Распечатать форму 110у?', function (btn) {
													if (btn === 'yes') {
														var id_salt = Math.random();
														var win_id = 'print_110u' + Math.floor(id_salt * 10000);
														var win = window.open('/?c=CmpCallCard&m=printCmpCloseCard110&CmpCallCard_id=' + store_record.get('CmpCallCard_id'), win_id);
													}
												}.bind(this));
												var win = window.open();
												win.document.write(response.responseText);
												win.document.close();
											}
										}
									})
								}
								break;
							}
							case (elCls.indexOf('calls-text') != -1):{
								if (store_record && store_record.get("CmpCallCardStatusType_id") != 4) {
									cntr.offerEmergencyTeamToCmpCallCard( callsView, teamsView );
								}
								if (!store_record) {
									cntr.getMapPanelRef().setAccidentMarker(null);
								}
								break;
							}
							default: {
								if (store_record && store_record.get("CmpCallCardStatusType_id") != 4) {
									cntr.offerEmergencyTeamToCmpCallCard( callsView, teamsView );
								}
								if (!store_record) {
									cntr.getMapPanelRef().setAccidentMarker(null);
								}
								break;
							}
						}

					}

					//self.fireEvent('containerclick');
				}.bind(this),
				render: function(c){
					setInterval(function(){
							c.separatorIsSet = false;
							//c.refresh();
						}.bind(this)
					, 20000);
				},
				viewready: function(cmp){
					cmp.getEl().toggleCls('focused-panel');
					cmp.focus();
					cmp.getSelectionModel().select(cmp.store.getAt(0));
				},
				containerclick: function(cmp){
					var win = this.getEmergecyWindowRef(),
						callsView = win.CallsView,
						teamsView = win.TeamsView;

					if (!callsView.getEl().hasCls('focused-panel')){
						callsView.getEl().toggleCls('focused-panel');
					}
					if (teamsView.getEl().hasCls('focused-panel')){
						teamsView.getEl().toggleCls('focused-panel');
					}
				},
				beforerefresh: function(view, opts) {
					view.separatorIsSet = false;
				}
			},
			'#swDispatcherStationWorkPlace_callsWin #swDispatcherStationWorkPlace_callsGrid[store]': {
				load: function( st, records, successful){
				}
			},
			'swDispatcherStationWorkPlace button[refId=createNewCall]': {
				click: function(){
					cntr.showWorkPlaceSMPDispatcherCallWindow(true);
				}
			},
			'swDispatcherStationWorkPlace button[refId=addStreamCard]': {
				click: function(){
					if (sw.lostConnection) {
						lostConnectionAlert();
						return false;
					}

					var cntr = this;
					cntr.showCmpCloseCardFromExt2(true);
				}
			},
			'swDispatcherStationWorkPlace button[refId=audioCalls]': {
				click: function(){
					if (sw.lostConnection) {
						lostConnectionAlert();
						return false;
					}

					Ext.create('sw.tools.swSearchAudioCallsWindow',{
						listeners:{							
						}
					}).show();
				}	
			},
			'swDispatcherStationWorkPlace combo[refId=sortCalls]': {
				select: function(cmp, recs){
					var cntr = this;
					cntr.sortCalls = recs[0].data.mode;
					cntr.sortCmpCalls();
				}
			},
			'swDispatcherStationWorkPlace button[refId=searchAndFocusTeamButton]': {
				click: function(cmp, recs){
					cntr.searchAndFocusTeamButton();
				}
			},
			'swDispatcherStationWorkPlace fieldset[refId=teamsFilterPanel] field': {
				keypress: function(c, e, o){
					if ( e.getKey() == Ext.EventObject.ENTER )
					{
						cntr.searchAndFocusTeamButton();
					}
				}
			},
			'swDispatcherStationWorkPlace button[refId=searchAndFocusCallButton]': {
				click: function(cmp, recs){
					cntr.searchAndFocusCallButton();
				}
			},
			'swDispatcherStationWorkPlace fieldset[refId=callsFilterPanel] field': {
				keypress: function(c, e, o){
					if ( e.getKey() == Ext.EventObject.ENTER )
					{
						cntr.searchAndFocusCallButton();
					}
					if ( e.getKey() == Ext.EventObject.TAB )
					{
						c.nextNode().focus();
					}
				},
			},
			'swDispatcherStationWorkPlace button[refId=vid1_btn]': {
				click: function(){
					var cntr = this;
					var storeTeams =  Ext.data.StoreManager.lookup('common.DispatcherStationWP.store.EmergencyTeamStore');
					var storeCalls =  Ext.data.StoreManager.lookup('common.DispatcherStationWP.store.CmpCallsStore');
					cntr.lockViewBtns(true);
					storeTeams.clearGrouping();
					storeTeams.sort();
					storeCalls.clearFilter();
					cntr.displayShortView(false, function() {
						cntr.lockViewBtns(false);
					});
				}
			},
			'swDispatcherStationWorkPlace button[refId=vid2_btn]': {
				click: function(){
					var cntr = this,
						storeTeams =  Ext.data.StoreManager.lookup('common.DispatcherStationWP.store.EmergencyTeamStore'),
						win = cntr.getEmergecyWindowRef(),
						shortCallsGrid = win.down('grid[refId=callsCardsShortGrid]');

					cntr.lockViewBtns(true);
					storeTeams.group('LpuBuilding_id');
					shortCallsGrid.getPlugin().applyFilters();
					cntr.displayShortView(true, function() {
						cntr.lockViewBtns(false);
					});
				}
			},
			'swDispatcherStationWorkPlace grid[refId=callsCardsShortGrid]': {
				groupclick: function (view, node, group, e, eOpts) {
					var cntr = this,
						win = cntr.getEmergecyWindowRef(),
						shortCallsGrid = win.down('grid[refId=callsCardsShortGrid]'),
						shortViewPanelWrapper = win.down('panel[refId=shortViewPanelWrapper]'),
						datePickerRange = shortViewPanelWrapper.down('datePickerRange'),
						groupingFeature = shortCallsGrid.getView().features[0],
						mode = '';
					
					if(groupingFeature.groupCache)						
						groupingFeature.stateOfGropups = groupingFeature.groupCache;
					
					var storeCalls = Ext.data.StoreManager.lookup('common.DispatcherStationWP.store.CmpCallsStore');
					switch (group) {
						case '5':
							if (cntr.modeView == 'closed') {
								cntr.modeView = 'table';
								return false;
							}

							//storeCalls.removeAll();
							mode = 'closed';
							break;
						case '6':
							if (cntr.modeView == 'cancel') {
								cntr.modeView = 'table';
								return false;
							}
							//storeCalls.removeAll();
							mode = 'cancel';
							break;
					}
					if (mode != '') {
						cntr.modeView = mode;
						storeCalls.reload({
							params: {
								mode: mode,
								begDate: Ext.Date.format(datePickerRange.dateFrom, 'd.m.Y'),
								endDate: Ext.Date.format(datePickerRange.dateTo, 'd.m.Y')
								//begDate: Ext.Date.format(Ext.Date.add(dt, Ext.Date.DAY, -1), 'd.m.Y'),
								//endDate: Ext.Date.format(dt, 'd.m.Y')
							}
						});

					}
				},
				itemcontextmenu: function(grid, record, item, index, event, eOpts){
					var cntr = this,
						win = this.getEmergecyWindowRef(),
						teamsView = win.down('grid[refId=teamsShortGrid]'),
						callsView = win.down('grid[refId=callsCardsShortGrid]');

					event.preventDefault();
					event.stopPropagation();

					cntr.showCmpCallCardSubMenu(event.getX(), event.getY(), callsView, teamsView, true);
				},
				itemclick: function(grid, record, item, index, e){
					var cntr = this,
						win = cntr.getEmergecyWindowRef(),
						teamsView = win.down('grid[refId=teamsShortGrid]'),
						callsView = win.down('grid[refId=callsCardsShortGrid]'),
						storeCalls = Ext.data.StoreManager.lookup('common.DispatcherStationWP.store.CmpCallsStore'),
						storeTeams = Ext.data.StoreManager.lookup('common.DispatcherStationWP.store.EmergencyTeamStore'),
						teamId = callsView.getSelectionModel().getSelection()[0].data.EmergencyTeam_id,
						teamRecord = storeTeams.findBy(function(rec,id){return (id == teamId)}),
						callId = record.getId();

					var callIndex = newRecordsAfterLoad.indexOf(callId.toString());
					if(callIndex != -1){
						var children = Ext.select('.x-grid-row-selected.x-grid-row-focused > .x-grid-td');
						children.each(function(rec){
							rec.dom.style.backgroundColor = 'white';
						})
						newRecordsAfterLoad.splice(callIndex, 1)
					}

					if(teamRecord != -1){
						teamsView.getSelectionModel().select(teamRecord,false,true)
					}else{
						teamsView.getSelectionModel().deselectAll()
					}

					if(record.get('CmpGroup_id') == 0){
						switch(true){
							case (!cntr.isNmpArm && record.get('hasEventDeny') && record.get('CmpCallCardStatusType_id') == 21 ): {
								cntr.showAcceptDenyCallMessage(record, storeCalls, callsView);
								break;
							}
							case (!cntr.isNmpArm && record.get('CmpCallCardStatusType_id') == 21 && record.get('CmpCallType_Code').inlist([1,2]) && !record.get('hasEventDeny')): {
								cntr.showEditLpuBuildingMessage(record, storeCalls, callsView);
								break;
							}
							case !Ext.isEmpty(record.get('CmpPPDResult_id')): {
								cntr.showUnresultSolutionMsg(record, storeCalls, callsView);
								break;
							}
							default : {
								cntr.showDPSolutionMsg(record, storeCalls, callsView);
							}
						}
					}

					if(teamsView.getSelectionModel().getSelection()[0]
						&& !(callsView.getSelectionModel().getSelection()[0].data.CmpCallCardStatusType_id.inlist([4,5,6]))
					){
						cntr.offerEmergencyTeamToCmpCallCard( callsView, teamsView );
					}

					cntr.loadShortCardPanel(record.get('CmpCallCard_id'));

					var rightContainerEl= win.down('form[refId=CallDetailPanel]').el,
						notice112Window = Ext.getCmp('notice112');

					if(notice112Window){
						notice112Window.destroy();
					}
					if(record.get('CmpCallCardStatusType_id') == 20){
						var alertBox = Ext.create('Ext.window.Window', {
							title: 'Сообщение',
							height: 70,
							width: 280,
							id: 'notice112',
							constrain: true,
							header: false,
							constrainTo: rightContainerEl,
							layout: {
								type: 'hbox',
								align: 'middle'
							},
							bodyBorder: false,
							items: [
								{
									xtype: 'label',
									flex: 1,
									html: "Вызов передан из Системы 112. Для передачи вызова в работу заполните обязательные поля и сохраните изменения"
								}
							]
						});
						alertBox.showAt([rightContainerEl.getWidth()/2+rightContainerEl.getLocalX()-150, rightContainerEl.getHeight()-20]);
					}

				},
				containerclick: function(cmp, e){
					var el = e.target,
						elCls = e.target.getAttribute('class'),
						win = this.getEmergecyWindowRef(),
						callsView = win.down('grid[refId=callsCardsShortGrid]');

					if(elCls.indexOf('multi-group-title') != -1){
						var lpubuilding_id = el.dataset.id,
							header = Ext.query('.multi-group-header-' + lpubuilding_id)[0];

						header.classList.toggle('x-grid-group-hd-collapsed');
						callsView.collapsedGroups[lpubuilding_id] = 'x-grid-group-hd-collapsed'.inlist(header.classList);

						var children = Ext.select('.multi-group-record-' + lpubuilding_id);
						children.each(function(rec){
							rec.dom.style.display = 'none';
						})
					}

				}
			},
			'swDispatcherStationWorkPlace grid[refId=teamsShortGrid]': {
				itemcontextmenu: function(grid, record, item, index, event, eOpts){
					var cntr = this,
						win = this.getEmergecyWindowRef(),
						teamsView = win.down('grid[refId=teamsShortGrid]'),
						callsView = win.down('grid[refId=callsCardsShortGrid]');
					cntr.showEmergencyTeamSubMenu(event.getX(), event.getY(), callsView, teamsView);
					event.preventDefault();
					event.stopPropagation();
				},
				itemclick: function(){
					var cntr = this,
						win = this.getEmergecyWindowRef(),
						teamsView = win.down('grid[refId=teamsShortGrid]'),
						callsView = win.down('grid[refId=callsCardsShortGrid]'),
						storeCalls = Ext.data.StoreManager.lookup('common.DispatcherStationWP.store.CmpCallsStore'),
						callId = teamsView.getSelectionModel().getSelection()[0].raw.CmpCallCard_id;

					if(callId){
						var callRecord = storeCalls.findBy(function(rec,id){
							return (id == callId);
						})
						callsView.getSelectionModel().select(callRecord,false,true)
					}else{
						callsView.getSelectionModel().deselectAll()
					}
				}
			},

			'swDispatcherStationWorkPlace form[refId=CallDetailPanel] combobox[name=secondStreetCombo]': {
				keyup: function(c, e, o){
					cntr.checkCrossRoadsFields(true, e);
				},
				blur: function(){
					cntr.checkCrossRoadsFields();
				}
			},

			'swDispatcherStationWorkPlace form[refId=CallDetailPanel] textfield[name=CmpCallCard_Dom]': {
				keyup: function(c, e, o){
					cntr.checkCrossRoadsFields(true, e);
				}
			},

			'swDispatcherStationWorkPlace form[refId=CallDetailPanel] combobox[name=ageUnit_id]':{
				select: function(cmp){
					cntr.setPersonAgeFields(cmp);
				}
			},
			'swDispatcherStationWorkPlace form[refId=CallDetailPanel] field[name=Person_Age]':{
				blur: function(cmp){
					cntr.setPersonAgeFields(cmp);
				}
			},


			'swDispatcherStationWorkPlace form[refId=CallDetailPanel] button[name=showAudioCallRecordWindow]': {
				click: function(){
					var cntr = this,
						win = cntr.getEmergecyWindowRef(),
						callDetailForm = win.down('form[refId=CallDetailPanel]').getForm();
						
					var cmpCallRecord_id = callDetailForm.findField('CmpCallRecord_id').getValue();
																	
					var swCmpCallRecordListenerWindow = Ext.create('common.tools.swCmpCallRecordListenerWindow',{
						record_id : cmpCallRecord_id
					}).show();
				}
			},
			
			'swDispatcherStationWorkPlace form[refId=CallDetailPanel] button[refId=saveShortCardBtn]': {
				click: function(){
					var cntr = this,
						win = cntr.getEmergecyWindowRef(),
						callsView = win.down('grid[refId=callsCardsShortGrid]'),
						callDetailForm = win.down('form[refId=CallDetailPanel]').getForm(),
						params = {};
					var CmpCallCardID = callDetailForm.findField('CmpCallCard_id').getValue();
					var CmpCallCardStatusType_id = callDetailForm.findField('CmpCallCardStatusType_id').getValue();
					if( !CmpCallCardID ){
						// если отсутствет ид карты, то и продолжать нет смысла
						return;
					}
					params.CmpCallCard_id = CmpCallCardID;
					params.CmpCallType_id = callDetailForm.findField('CmpCallType_id').getValue();
					params.withoutChangeStatus = cntr.withoutChangeStatus;
					if(!callDetailForm.isValid()){
						Ext.Msg.alert('Предупреждение', 'Не все поля формы заполнены. Незаполненные поля выделены');
						return;
					}
					//проверки только для вызовов в статусе "Передано из 112"
					if(CmpCallCardStatusType_id == 20){

						params.CmpCallCardStatusType_id = 1; //Статус "передано"
						var dublicate_params = {};

						dublicate_params.CmpCallCard_id = CmpCallCardID;
						dublicate_params.CmpCallCard_prmDate = Ext.Date.format(callDetailForm.findField('CmpCallCard_prmDate').getValue(), 'Y-m-d');
						dublicate_params.CmpCallCard_prmTime = Ext.Date.format(callDetailForm.findField('CmpCallCard_prmTime').getValue(), 'H:i:s');
						dublicate_params.Person_Surname = callDetailForm.findField('Person_SurName').getValue();
						dublicate_params.Person_Firname = callDetailForm.findField('Person_FirName').getValue();
						dublicate_params.Person_Secname = callDetailForm.findField('Person_SecName').getValue();
						dublicate_params.Sex_id = callDetailForm.findField('Sex_id').getValue();
						dublicate_params.Person_id = callDetailForm.findField('Person_id').getValue();
						dublicate_params.Person_Birthday = callDetailForm.findField('Person_Birthday').getValue();
						dublicate_params.Person_Age = callDetailForm.findField('Person_AgeInt').getValue();

						dublicate_params.CmpCallCard_Dom = callDetailForm.findField('CmpCallCard_Dom').getValue();
						dublicate_params.CmpCallCard_Kvar = callDetailForm.findField('CmpCallCard_Kvar').getValue();
						dublicate_params.CmpCallCard_Podz = callDetailForm.findField('CmpCallCard_Podz').getValue();
						dublicate_params.CmpCallCard_Etaj = callDetailForm.findField('CmpCallCard_Etaj').getValue();
						dublicate_params.withoutChangeStatus = cntr.withoutChangeStatus;

						// Данные выбранного города/наспункта
						var streetsCombo = callDetailForm.findField('dStreetsCombo');
						var cityCombo = callDetailForm.findField('dCityCombo');
						var rec = streetsCombo.findRecord('StreetAndUnformalizedAddressDirectory_id', streetsCombo.getValue());
						if (rec){
							// filterParams.StreetAndUnformalizedAddressDirectory_id = rec.get('StreetAndUnformalizedAddressDirectory_id');
							dublicate_params.KLStreet_id = rec.get('KLStreet_id');
							dublicate_params.UnformalizedAddressDirectory_id = rec.get('UnformalizedAddressDirectory_id');
						}

						var town = cityCombo.getValue();

						if (typeof cityCombo.store.proxy.reader.jsonData !== 'undefined' && town){
							var city = cityCombo.findRecord('Town_id', town);
							city = city.data;

							if(city.KLAreaLevel_id==4){
								dublicate_params.KLTown_id = city.Town_id;

								//если региона нет тогда нас пункт не относится к городу
								if(city.Region_id){
									dublicate_params.KLSubRgn_id = city.Area_pid;
								} else{
									dublicate_params.KLCity_id = city.Area_pid;
								}
							} else{
								dublicate_params.KLCity_id = city.Town_id;
								//если город верхнего уровня, то район сохранять не надо
								if(city.KLAreaStat_id!=0)
								{dublicate_params.KLSubRgn_id = city.Area_pid;}
							}

							dublicate_params.KLAreaStat_idEdit = city.KLAreaStat_id;
							dublicate_params.KLRgn_id = city.Region_id;
						}

						var loadMask = new Ext.LoadMask(Ext.getBody(),{msg:"Поиск дублирующих вызовов..."});
						loadMask.show();

						Ext.Ajax.request({
							url: '/?c=CmpCallCard4E&m=checkDuplicateCmpCallCardByAddress',
							params: dublicate_params,
							callback: function(opt, success, response){
								if ( success ) {
									var response_obj = Ext.JSON.decode(response.responseText);
									if(response_obj.Error_Msg){
										Ext.Msg.alert('Ошибка',response_obj.Error_Msg);
										return;
									}
									if(response_obj.data.length > 0){
										cntr.showDublicateWindow(response_obj.data,params)
									}else{
										Ext.Ajax.request({
											url: '/?c=CmpCallCard4E&m=checkDuplicateCmpCallCardByFIO',
											params: dublicate_params,
											callback: function (opt, success, response) {
												if ( success ) {
													var response_obj = Ext.JSON.decode(response.responseText);
													if(response_obj.Error_Msg){
														Ext.Msg.alert('Ошибка',response_obj.Error_Msg);
														return;
													}
													if(response_obj.data && response_obj.data.length > 0){

														cntr.showDublicateWindow(response_obj.data,params)

													}else{

														Ext.Ajax.request({
															url: '/?c=CmpCallCard4E&m=checkLastDayClosedCallsByAddressAndPersonId',
															params: dublicate_params,
															success: function(response){
																var data = Ext.JSON.decode(response.responseText);
																if(data.Error_Msg){
																	Ext.Msg.alert('Ошибка',data.Error_Msg);
																	return;
																}
																if (data.length > 0) {

																	var cmpLastDayClosedWindow = Ext.ComponentQuery.query('window[refId=SmpCallCardCheckLastDayClosedWindow]')[0];
																	if(!cmpLastDayClosedWindow.isVisible()){
																		cmpLastDayClosedWindow.show({closedCards: data});
																		if( !cmpLastDayClosedWindow.hasListener('selectLastDayClosedCall') ) {
																			cmpLastDayClosedWindow.on('selectLastDayClosedCall', function (success, pp, rec) {

																				var Record = callDetailForm.findField('CmpCallType_id').getStore().findRecord('CmpCallType_Code', 2) // повторный
																				if (Record)
																					params.CmpCallType_id = Record.get('CmpCallType_id');

																				cntr.saveShortCard(params);
																				cmpLastDayClosedWindow.close();
																			});
																		}
																		cmpLastDayClosedWindow.on('close', function (success, pp, rec) {
																			cntr.saveShortCard(params);
																		});


																	}

																}else{
																	cntr.saveShortCard(params);
																}
															}
														});

													}
												}

											}
										});
									}

								}
							}
						});


					}
					else{
						cntr.saveShortCard(params);
					}

				}
			},
			'swDispatcherStationWorkPlace form[refId=CallDetailPanel] combobox[name=EmergencyTeam_id]': {
				select: function(c, r){
					var cntr = this,
						win = this.getEmergecyWindowRef(),
						teamsView = win.down('grid[refId=teamsShortGrid]'),						
						callsView = win.down('grid[refId=callsCardsShortGrid]');
						team = teamsView.store.findRecord('EmergencyTeam_id', r[0].data.EmergencyTeam_id);
					
					teamsView.getSelectionModel().select(team);
					
					cntr.setCenterEmergencyTeamOnMap(team, true, null, callsView, teamsView, function(success){
						if(!success)c.clearValue( );
					});
				}
			},
			'swDispatcherStationWorkPlace form[refId=CallDetailPanel] combobox[name=LpuBuilding_id]': {
				select: function(c, r){
					var cntr = this,
						win = cntr.getEmergecyWindowRef(),
						shortViewPanelWrapper = win.down('panel[refId=shortViewPanelWrapper]'),
						callDetailForm = shortViewPanelWrapper.down('form[refId=CallDetailPanel]').getForm(),
						Lpu_ppdid = callDetailForm.findField('Lpu_ppdid'),
						MedService_id = callDetailForm.findField('selectNmpCombo'),
						CmpCallCard_IsPoli = callDetailForm.findField('CmpCallCard_IsPoli');
						
					MedService_id.clearValue();
					Lpu_ppdid.clearValue();
					CmpCallCard_IsPoli.setValue(false);
				},
				select: function(){
					this.setEnabledCallCardFields();
				}
			},
			'swDispatcherStationWorkPlace form[refId=CallDetailPanel] combobox[name=CmpCallCard_IsExtra]': {
				select: function(c, r){
					this.setEnabledCallCardFields();
				}
			},
			'swDispatcherStationWorkPlace form[refId=CallDetailPanel] combobox[name=Lpu_ppdid]': {
				select: function(c, val){
					var cntr = this,
						win = cntr.getEmergecyWindowRef(),
						shortViewPanelWrapper = win.down('panel[refId=shortViewPanelWrapper]'),
						callDetailForm = shortViewPanelWrapper.down('form[refId=CallDetailPanel]').getForm(),
						NmpServiceStore = callDetailForm.findField('selectNmpCombo').getStore(),
						LpuBuilding_id = callDetailForm.findField('LpuBuilding_id'),
						Lpu_smpid = callDetailForm.findField('Lpu_smpid'),
						CmpCallCard_IsPassSSMP = callDetailForm.findField('CmpCallCard_IsPassSSMP'),
						recIndex = null;

					if(val){
						recIndex = c.getStore().findBy(function(r){return r.get('Lpu_id') == val});
					}
					
					LpuBuilding_id.clearValue();
					Lpu_smpid.clearValue();
					CmpCallCard_IsPassSSMP.setValue(false);

					NmpServiceStore.getProxy().extraParams = {
						'Lpu_ppdid': (recIndex != -1) ? val : null,
						'isClose': 1
					}
					NmpServiceStore.reload();
				},
				select: function(){
					this.setEnabledCallCardFields();
				}
			},
			'swDispatcherStationWorkPlace form[refId=CallDetailPanel] checkbox[name=CmpCallCard_IsPoli]': {
				change: function(cmp, val){
					var cntr = this,
						win = cntr.getEmergecyWindowRef(),
						shortViewPanelWrapper = win.down('panel[refId=shortViewPanelWrapper]'),
						callDetailForm = shortViewPanelWrapper.down('form[refId=CallDetailPanel]').getForm(),
						NmpStore = callDetailForm.findField('Lpu_ppdid').getStore();

					if(val){
						callDetailForm.findField('CmpCallCard_IsPassSSMP').setValue(false);
						callDetailForm.findField('Lpu_smpid').clearValue();
						callDetailForm.findField('LpuBuilding_id').clearValue();
					}
					NmpStore.proxy.extraParams = val ? null : {'Object': 'LpuWithMedServ','MedServiceType_id': 18};
					NmpStore.reload();
				},
				focus: function(){
					this.setEnabledCallCardFields();
				},
				blur: function(){
					this.setEnabledCallCardFields();
				}
			},
			'swDispatcherStationWorkPlace form[refId=CallDetailPanel] checkbox[name=CmpCallCard_IsPassSSMP]': {
				change: function(cmp, val){
					var cntr = this,
						win = cntr.getEmergecyWindowRef(),
						shortViewPanelWrapper = win.down('panel[refId=shortViewPanelWrapper]'),
						callDetailForm = shortViewPanelWrapper.down('form[refId=CallDetailPanel]').getForm();

					if(val){
						callDetailForm.findField('CmpCallCard_IsPoli').setValue(false);
						callDetailForm.findField('Lpu_ppdid').clearValue();
						callDetailForm.findField('selectNmpCombo').clearValue();
					}
				},
				focus: function(){
					this.setEnabledCallCardFields();
				},
				blur: function(){
					this.setEnabledCallCardFields();
				}
			}
		});
	},
	person_age: function(date, Person_Birthday, Person_Age){
		var person_age = 'Возраст не определен';
		if ( Person_Birthday){

			if( Person_Age > 0)
				person_age = Person_Age + ' л.';
			else{
				var dateOfBirth = Ext.Date.parse( Person_Birthday, 'd.m.Y'),
					daysAge = Math.floor(Math.abs(dateOfBirth-date)/(1000*60*60*24)),
					mounthAge = Math.floor(Math.abs(date.getMonthsBetween(dateOfBirth)));

				if(mounthAge)
					person_age = mounthAge+' м.';
				else
					person_age = daysAge+ ' д.';
			}
		}
		else
			if( Person_Age > 0)
				person_age = Person_Age + ' л.';

		return person_age;
	},
	displayShortView: function(displayShortView, cb){
		var cntr = this,
			win = cntr.getEmergecyWindowRef(),
			teamsWin = win.TeamsView.up('panel'),
			callsWin = win.CallsView.up('panel'),
			dt = new Date(Date.now()),
			storeCalls = Ext.data.StoreManager.lookup('common.DispatcherStationWP.store.CmpCallsStore'),
			shortViewPanelWrapper = win.down('panel[refId=shortViewPanelWrapper]'),
			hrSplitterContainer = win.down('container[refId=hrSplitterContainer]'),
			hrSplitterContainerTableView = win.down('container[refId=hrSplitterContainerTableView]'),
            callDetailPanel = shortViewPanelWrapper.down('form[refId=CallDetailPanel]'),
			callDetailForm = callDetailPanel.getForm(),
			dateTimeFieldsetBlock = shortViewPanelWrapper.down('fieldset[refId=dateTimeFieldsetBlock]'),
			callDetailFormFields = callDetailForm.getFields().items,
			datePickerRange = shortViewPanelWrapper.down('datePickerRange'),
			defNoticeWindow = Ext.getCmp('defNotice'),
			cmpCallCard_id = callDetailForm.findField('CmpCallCard_id').getValue();

		storeCalls.removeAll();
		if(defNoticeWindow){
			defNoticeWindow.destroy();
		}

		if(displayShortView){ // входим в табличную часть
			cntr.modeView = 'table';
			storeCalls.removeAll();
			storeCalls.reload({
				params: {
					mode: cntr.modeView,
					begDate: Ext.Date.format(datePickerRange.dateFrom, 'd.m.Y'),
					endDate: Ext.Date.format(datePickerRange.dateTo, 'd.m.Y')
				},
				callback: function() {
					if (cmpCallCard_id == "") callDetailForm.findField('CmpCallPlaceType_id').setValue(0);
					cntr.setTitleServedTap();
					if(cb)cb();
				}
			});
			storeCalls.clearGrouping();
			storeCalls.group('CmpGroupTable_id','ASC');
			shortViewPanelWrapper.show();
			hrSplitterContainer.hide();
			hrSplitterContainerTableView.show();
			teamsWin.hide();
			callsWin.hide();
			callDetailPanel.doLayout();
			//setDisableFields(true);

			if (cmpCallCard_id == ""){
				for (i = 0; i < callDetailFormFields.length; i++){ //при смене вида формы поля в уменьшенной карте вызова блокируются
					callDetailFormFields[i].setDisabled(true);
				}
				callDetailForm.findField('secondStreetCombo').setVisible(false);
			}
		}
		else{			 // выходим из табличной части
			cntr.modeView = 'default';
			storeCalls.clearGrouping();
			storeCalls.removeAll();
			storeCalls.reload({
				params: {
					begDate: Ext.Date.format(Ext.Date.add(dt, Ext.Date.DAY, -1), 'd.m.Y'),
					endDate: Ext.Date.format(dt, 'd.m.Y')
				},
				callback: function() {
					cntr.setTitleServedTap();
					if(cb)cb();
				}
			});
			shortViewPanelWrapper.hide();
			hrSplitterContainer.show();
			hrSplitterContainerTableView.hide();
			if (!getGlobalOptions().IsLocalSMP) {
				teamsWin.show();
			}
			callsWin.show();
		}
	},
	
	showNotify: function(contentText, buttons, height){
		var cntr = this,
			armWindowEl = cntr.getEmergecyWindowRef().getEl(),
			alertBox = null,
			notify = Ext.ComponentQuery.query('redNotifyWindow')[0];

		if(notify){notify.close()}

		alertBox = Ext.create('sw.redNotifyWindow', {
			refId: 'redNotifyWindow',
			width: 450,
			height: height,
			contentText: contentText,
			bbar: buttons
		});

		alertBox.showAt([armWindowEl.getWidth()-500, armWindowEl.getHeight()-100]);
	},
	showWorkPlaceSMPDispatcherCallWindow: function(newCall, typeEditCard, isNmpArm, callsView, rec){
		var cntr=this;

		if (getWnd('swWorkPlaceSMPDispatcherCallWindow').isVisible()){
			getWnd('swWorkPlaceSMPDispatcherCallWindow').close();
		}

		if(!newCall){
			var storeCalls = Ext.data.StoreManager.lookup('common.DispatcherStationWP.store.CmpCallsStore'),
				recCardInSelection = callsView.getSelectionModel().getSelection()[0],
				recCard = (recCardInSelection)?callsView.getStore().findRecord('CmpCallCard_id', recCardInSelection.get('CmpCallCard_id')):false,
				data=(rec)?rec:recCard.raw,
				params ={
					'CmpCallPlaceType_id' : data.CmpCallPlaceType_id,
					'Town_id' : data.Town_id,
					'StreetAndUnformalizedAddressDirectory_id' : data.StreetAndUnformalizedAddressDirectory_id,
					'CmpCallCard_Dom' : data.CmpCallCard_Dom,
					'CmpCallCard_Korp' : data.CmpCallCard_Korp,
					'CmpCallCard_Kvar' : data.CmpCallCard_Kvar,
					'CmpCallCard_Podz' : data.CmpCallCard_Podz,
					'CmpCallCard_Etaj' : data.CmpCallCard_Etaj,
					'CmpCallCard_Kodp' : data.CmpCallCard_Kodp,
					'CmpCallCard_Comm' : data.CmpCallCard_Comm,
					'KLRgn_id': data.KLRgn_id,
					'KLSubRgn_id': data.KLSubRgn_id,
					'KLCity_id':data.KLCity_id,
					'KLTown_id': data.Town_id,
					'KLStreet_id': data.KLStreet_id,
					'dCityCombo':data.Town_id,
					'CmpCallCard_UlicSecond': data.CmpCallCard_UlicSecond,
					'CmpCallCard_CallLtd' : data.UnAdress_lat,
					'CmpCallCard_CallLng' : data.UnAdress_lng
				};
			switch(typeEditCard) {
				case 'oneWayCallCard':
					Object.assign(params, {
						'CmpCallCard_DayNumberRid': data.CmpCallCard_Numv, //связь с первичным обращением
						'CmpCallCard_rid' : data.CmpCallCard_id,
						'EmergencyTeam_id' : data.EmergencyTeam_id,
						'LpuBuilding_id': !(isNmpArm) ? data.LpuBuilding_id : null,
						'CmpCallCard_IsPoli': !(isNmpArm) ? data.CmpCallCard_IsPoli : null,
						'CmpCallCard_IsPassSSMP': !(isNmpArm) ? data.CmpCallCard_IsPassSSMP : null,
						'Lpu_smpid': !(isNmpArm) ? data.Lpu_smpid : null,
						'Lpu_ppdid': data.Lpu_ppdid,
						'MedService_id': data.MedService_id,
						'CmpCallCard_IsExtra': data.CmpCallCard_IsExtra,
						'region_id' : getGlobalOptions().region.number,
						'CmpCallType_Code' : 4, //4 - тип обращения попутный
						'CmpCallerType_id': 10, // 10 - тип вызывающего врач
						'CmpCallCardStatusType_id': null, // статус карты (принят на обслуживание бригадой) унес в модель тк не проставляется статус по канонам
						'typeEditCard' : 'oneWayCallCard'
					});
					break;
				case 'copyCallCard':
					Object.assign(params, {
						'CmpCallCard_sid' : data.CmpCallCard_sid ? data.CmpCallCard_sid : data.CmpCallCard_id,
						'CmpCallCard_Telf' : data.CmpCallCard_Telf,
						'CmpCallerType_id' : data.CmpCallerType_id,
						'CmpReason_id' : data.CmpReason_id,
						'CmpReason_Name': data.CmpReason_Name,
						'CmpCallCard_IsExtra': data.CmpCallCard_IsExtra,
						'LpuBuilding_id': !(isNmpArm) ? data.LpuBuilding_id : null,
						'CmpCallCard_IsPoli': !(isNmpArm) ? data.CmpCallCard_IsPoli : null,
						'CmpCallCard_IsPassSSMP': !(isNmpArm) ? data.CmpCallCard_IsPassSSMP : null,
						'Lpu_smpid': !(isNmpArm) ? data.Lpu_smpid : null,
						'Lpu_ppdid': data.Lpu_ppdid,
						'MedService_id': data.MedService_id,
						'typeEditCard' : 'copyCallCard',
						'CmpCallType_Code': 1
					});
					break;
				case 'forSpecialEmergencyTeam':
					Object.assign(params, {
						'CmpCallCard_DayNumberRid': data.CmpCallCard_Numv, //связь с первичным обращением
						'CmpCallCard_rid' : data.CmpCallCard_id,
						'LpuBuilding_id': data.LpuBuilding_id,
						'CmpCallCard_IsPoli': data.CmpCallCard_IsPoli,
						'Person_Age' : data.Person_Age,
						'Person_Birthday': Ext.Date.format(new Date(data.Person_Birthday), 'd.m.Y'),
						'Person_Firname' : data.Person_Firname,
						'Person_Secname' : data.Person_Secname,
						'Person_Surname' : data.Person_Surname,
						'Person_id' : data.Person_id,
						'Sex_id' : data.Sex_id,
						'CmpCallCard_DateTper': data.CmpCallCard_DateTper,
						'CmpCallCard_DateVyez': data.CmpCallCard_DateVyez,
						'CmpCallCard_DatePrzd': data.CmpCallCard_DatePrzd,
						'CmpCallCard_DateTgsp': data.CmpCallCard_DateTgsp,
						'CmpCallCard_DateTsta': data.CmpCallCard_DateTsta,
						'CmpCallCard_DateTisp': data.CmpCallCard_DateTisp,
						'CmpCallCard_DateTvzv': data.CmpCallCard_DateTvzv,
						'CmpCallCard_HospitalizedTime': data.CmpCallCard_HospitalizedTime,
						'region_id' : getGlobalOptions().region.number,
						'CmpCallType_Code' : getRegionNick().inlist(['perm']) ? 1 : 9, //1 - тип обращения Первичный, 9 - тип обращения для спец бригады СМП
						'typeEditCard' : 'forSpecialEmergencyTeam',
					});
					if(getRegionNick().inlist(['perm']) && data.EmergencyTeam_Num){
						params.CmpCallCard_Ktov = 'Бригада № ' + data.EmergencyTeam_Num;
					}else{
						params.CmpCallerType_id = 10 // 10 - тип вызывающего врач
					}
					break;
			}
			getWnd('swWorkPlaceSMPDispatcherCallWindow').show({
				showByDP: true,
				params: params,
				onSaveByDp: function(card_id){
					getWnd('swWorkPlaceSMPDispatcherCallWindow').hide();
						cntr.reloadStores(function(){
							//выделить запись
							var addedRec = storeCalls.findRecord('CmpCallCard_id', card_id);
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
					cntr.reloadStores();
				}
			});
		}
	},

    setVigiltimes:function(params, callbackFn){
        Ext.Ajax.request({
            url: '/?c=EmergencyTeam4E&m=editEmergencyTeamVigilTimes',
            params: params,
            callback: function(opt, success, response) {
            if (success){
                var callbackParams =  Ext.decode(response.responseText);

					if (callbackParams[0].Error_Msg != null) {
						Ext.Msg.alert('Ошибка', callbackParams[0].Error_Msg);
					}

					if(callbackFn)callbackFn(callbackParams);
				}
			}
		});
	},

	deleteVigil: function(params, callbackFn){
		Ext.Ajax.request({
			url: '/?c=EmergencyTeam4E&m=deleteEmergencyTeamVigil',
			params: params,
			callback: function(opt, success, response) {
				if (success){
					var callbackParams =  Ext.decode(response.responseText);

					if (callbackParams[0].Error_Msg != null) {
						Ext.Msg.alert('Ошибка', callbackParams[0].Error_Msg);
					}
					if(callbackFn)callbackFn(callbackParams);
				}
			}
		});
	},

	loadShortCardPanel: function(CmpCallCard_id, callback){
		var cntr = this,
			win = cntr.getEmergecyWindowRef(),
			shortViewPanelWrapper = win.down('panel[refId=shortViewPanelWrapper]'),
			callDetailForm = shortViewPanelWrapper.down('form[refId=CallDetailPanel]').getForm(),
			cityCombo = callDetailForm.findField('dCityCombo'),
			streetsCombo = callDetailForm.findField('dStreetsCombo'),
			secondStreetCombo = callDetailForm.findField('secondStreetCombo'),
			cmpCallCard_Dom = callDetailForm.findField('CmpCallCard_Dom'),
			saveShortCardBtn = shortViewPanelWrapper.down('button[refId=saveShortCardBtn]'),
			showAudioCallRecordWindowBtn = shortViewPanelWrapper.down('button[name=showAudioCallRecordWindow]');

		saveShortCardBtn.show();
		saveShortCardBtn.disable();

		Ext.Ajax.request({
			url: '/?c=CmpCallCard4E&m=loadCmpCallCardEditForm',
			params: {CmpCallCard_id: CmpCallCard_id},
			callback: function(opt, success, response) {
				if (!success){
					return false;
				}
				var response_obj = Ext.JSON.decode(response.responseText)[0];

				if(!response_obj) return;

				//чтобы не плодит поля - пустышки
				cntr.editedCardData = response_obj;

				//@todo тут все очень плохо, нужно рефакторить

				cityCombo.store.getProxy().extraParams = {
					KLRgn_id: response_obj.KLRgn_id,
					KLSubRgn_id: response_obj.KLSubRgn_id,
					KLCity_id: response_obj.KLCity_id,
					KLTown_id: response_obj.KLTown_id,
					region_id : getGlobalOptions().region.number
				};

				var displaySecondStreet = response_obj.CmpCallCard_UlicSecond ? true : false;

				secondStreetCombo.setVisible(displaySecondStreet);
				cmpCallCard_Dom.setVisible(!displaySecondStreet);


				callDetailForm.reset();
				cityCombo.reset();
				cityCombo.store.removeAll();
				cityCombo.store.load({
					callback: function(rec, operation, success){
						if ( this.getCount() != 1 || !rec) {
							return;
						}
						cityCombo.setValue(rec[0].get('Town_id'));

						streetsCombo.bigStore.getProxy().extraParams = {
							town_id: rec[0].get('Town_id'),
							Lpu_id: sw.Promed.MedStaffFactByUser.current.Lpu_id
						};

						streetsCombo.bigStore.load({
							callback: function(records, operation, success) {
								var rec = streetsCombo.bigStore.findRecord('StreetAndUnformalizedAddressDirectory_id', response_obj.StreetAndUnformalizedAddressDirectory_id);
								if (rec){
									streetsCombo.store.removeAll();
									streetsCombo.store.add(rec);
									streetsCombo.setValue(rec.get('StreetAndUnformalizedAddressDirectory_id'));
								}
								else{
									streetsCombo.setRawValue(response_obj.CmpCallCard_Ulic);
								}

								secondStreetCombo.bigStore.loadData(records || []);

								var secondrec = secondStreetCombo.bigStore.findRecord('KLStreet_id', response_obj.CmpCallCard_UlicSecond);
								if (secondrec){
									secondStreetCombo.store.removeAll();
									secondStreetCombo.store.add(secondrec);
									secondStreetCombo.setValue(secondrec.get('KLStreet_id'));

									secondStreetCombo.setVisible(displaySecondStreet);
									cmpCallCard_Dom.setVisible(!displaySecondStreet);
								}
								saveShortCardBtn.enable();

							}
						});
					}
				});

				showAudioCallRecordWindowBtn.setVisible(response_obj.CmpCallRecord_id)

				response_obj.CmpCallCard_prmDate = Ext.Date.parse(response_obj.CmpCallCard_prmDate, "Y-m-d H:i:s");
				response_obj.CmpCallPlaceType_id = response_obj.CmpCallPlaceType_id ? parseInt(response_obj.CmpCallPlaceType_id) : null;
				response_obj.Sex_id = response_obj.Sex_id ? parseInt(response_obj.Sex_id) : null;
				response_obj.CmpCallType_id = response_obj.CmpCallType_id ? parseInt(response_obj.CmpCallType_id) : null;
				response_obj.CmpReason_id = response_obj.CmpReason_id ? parseInt(response_obj.CmpReason_id) : null;
				response_obj.CmpCallerType_id = response_obj.CmpCallerType_id ? parseInt(response_obj.CmpCallerType_id) : response_obj.CmpCallCard_Ktov;
				response_obj.LpuBuilding_id = response_obj.LpuBuilding_id ? parseInt(response_obj.LpuBuilding_id) : null;
				response_obj.Person_Age = response_obj.Person_Age ? parseInt(response_obj.Person_Age) : null;
				response_obj.EmergencyTeam_id = response_obj.EmergencyTeam_id ? parseInt(response_obj.EmergencyTeam_id) : null;
				response_obj.DPMedPersonal_id = response_obj.DPMedPersonal_id ? parseInt(response_obj.DPMedPersonal_id) : null;
				response_obj.Lpu_hid = response_obj.Lpu_hid ? parseInt(response_obj.Lpu_hid) : null;

				var CmpCallCard_TransTime = Ext.Date.parse(response_obj.CmpCallCard_DateTper, 'Y-m-d H:i:s'),
					CmpCallCard_GoTime = Ext.Date.parse(response_obj.CmpCallCard_DateVyez, 'Y-m-d H:i:s'),
					CmpCallCard_ArriveTime = Ext.Date.parse(response_obj.CmpCallCard_DatePrzd, 'Y-m-d H:i:s'),
					CmpCallCard_TransportTime = Ext.Date.parse(response_obj.CmpCallCard_DateTgsp, 'Y-m-d H:i:s'),
					CmpCallCard_EndTime = Ext.Date.parse(response_obj.CmpCallCard_DateTisp, 'Y-m-d H:i:s'),
					CmpCallCard_Hospitalized = Ext.Date.parse(response_obj.CmpCallCard_HospitalizedTime, 'Y-m-d H:i:s');


				if(CmpCallCard_TransTime){
					response_obj.CmpCallCard_DateTper =  CmpCallCard_TransTime;
					callDetailForm.findField('CmpCallCard_DateTperTime').setValue(Ext.Date.format(CmpCallCard_TransTime, 'H:i'));
					//callDetailForm.findField('CmpCallCard_DateTper').setReadOnly(true);
					//callDetailForm.findField('CmpCallCard_DateTperTime').setReadOnly(true);
				}

				if(CmpCallCard_GoTime){
					response_obj.CmpCallCard_DateVyez =  CmpCallCard_GoTime;
					callDetailForm.findField('CmpCallCard_DateVyezTime').setValue(Ext.Date.format(CmpCallCard_GoTime, 'H:i'));
					//callDetailForm.findField('CmpCallCard_DateVyez').setReadOnly(true);
					//callDetailForm.findField('CmpCallCard_DateVyezTime').setReadOnly(true);
				}

				if(CmpCallCard_ArriveTime){
					response_obj.CmpCallCard_DatePrzd =  CmpCallCard_ArriveTime;
					callDetailForm.findField('CmpCallCard_DatePrzdTime').setValue(Ext.Date.format(CmpCallCard_ArriveTime, 'H:i'));
					//callDetailForm.findField('CmpCallCard_DatePrzd').setReadOnly(true);
					//callDetailForm.findField('CmpCallCard_DatePrzdTime').setReadOnly(true);
				}

				/*if(CmpCallCard_TransportTime){
					response_obj.CmpCallCard_DateTgsp =  CmpCallCard_TransportTime;
					callDetailForm.findField('CmpCallCard_DateTgspTime').setValue(Ext.Date.format(CmpCallCard_TransportTime, 'H:i'));
					//callDetailForm.findField('CmpCallCard_DateTgsp').setReadOnly(true);
					//callDetailForm.findField('CmpCallCard_DateTgspTime').setReadOnly(true);
				}*/

				if(CmpCallCard_EndTime){
					response_obj.CmpCallCard_DateTisp =  CmpCallCard_EndTime;
					callDetailForm.findField('CmpCallCard_TispTime').setValue(Ext.Date.format(CmpCallCard_EndTime, 'H:i'));
					//callDetailForm.findField('CmpCallCard_DateTisp').setReadOnly(true);
					//callDetailForm.findField('CmpCallCard_TispTime').setReadOnly(true);
				}

				if(CmpCallCard_Hospitalized){
					response_obj.CmpCallCard_HospitalizedTime =  CmpCallCard_Hospitalized;
					callDetailForm.findField('CmpCallCard_HospitalizedTimeTime').setValue(Ext.Date.format(CmpCallCard_Hospitalized, 'H:i'));
					//callDetailForm.findField('CmpCallCard_HospitalizedTime').setReadOnly(true);
					//callDetailForm.findField('CmpCallCard_HospitalizedTimeTime').setReadOnly(true);
				}


				if(response_obj.CmpCallCard_IsExtra){
					response_obj.CmpCallCard_IsExtra = parseInt(response_obj.CmpCallCard_IsExtra);
				}
				if(response_obj.CmpCallCard_IsPoli){
					response_obj.CmpCallCard_IsPoli = (response_obj.CmpCallCard_IsPoli == 2);
				}
				if(response_obj.CmpCallCard_IsPassSSMP){
					response_obj.CmpCallCard_IsPassSSMP = (response_obj.CmpCallCard_IsPassSSMP == 2);
				}


				if(response_obj.EmergencyTeam_id){
					callDetailForm.findField('EmergencyTeam_id').setValue(response_obj.EmergencyTeam_id);
					callDetailForm.findField('EmergencyTeam_Num').setValue(response_obj.EmergencyTeam_Num);
					response_obj.EmergencyTeam_id =  parseInt(response_obj.EmergencyTeam_id);
					// callDetailForm.findField('EmergencyTeam_id').setReadOnly(true);
				}

				//ageUnit_id
				callDetailForm.setValues(response_obj);

				if (!callDetailForm.getAllFields().CmpCallerType_id.getValue() && response_obj.CmpCallerType_id) {
					callDetailForm.getAllFields().CmpCallerType_id.setRawValue(response_obj.CmpCallerType_id)
				}
				
				if(response_obj.Person_Birthday){
					cntr.setPersonAgeFields('Person_Birthday');
				}

				//personBirthdayYearAgeField.setValue(cntr.person_age(response_obj.CmpCallCard_prmDate, response_obj.Person_Birthday, response_obj.Person_Age));

				if(response_obj.MedService_id){
					var recordIndex = callDetailForm.findField('selectNmpCombo').getStore().findBy(function(rec,id){
						return rec.get('MedService_id') == response_obj.MedService_id;
					}),
						record = callDetailForm.findField('selectNmpCombo').getStore().getAt(recordIndex);
					callDetailForm.findField('selectNmpCombo').select(record);
				}
				if(response_obj.Lpu_smpid){
					var recordIndex = callDetailForm.findField('Lpu_smpid').getStore().findBy(function(rec,id){
						return rec.get('Lpu_id') == response_obj.Lpu_smpid;
					}),
						record = callDetailForm.findField('Lpu_smpid').getStore().getAt(recordIndex);
					callDetailForm.findField('Lpu_smpid').select(record);
				}
				if(response_obj.Lpu_ppdid){
					var recordIndex = callDetailForm.findField('Lpu_ppdid').getStore().findBy(function(rec,id){
						return rec.get('Lpu_id') == response_obj.Lpu_ppdid;
					}),
						record = callDetailForm.findField('Lpu_ppdid').getStore().getAt(recordIndex);
					callDetailForm.findField('Lpu_ppdid').select(record);
				}
				cntr.setEnabledCallCardFields(response_obj);

				if(callback) callback();
				return true;

			}.bind(this)
		});
	},

	//региональные костыли
	regionalCrutches: function(){
		var cntr = this,
			region = getGlobalOptions().region.nick,
			win = this.getEmergecyWindowRef(),
			topToolbar = win.down('toolbar[refId=DPTopToolbar]'),
			teamsView = win.TeamsView,
			panelTeams = teamsView.up('panel'),
			teamsGroupFilterSettingsTool = panelTeams.down('tool[refId=showTeamsFilterPanelBtn]');

		switch(region){
			case 'ufa' : {
				teamsGroupFilterSettingsTool.hide();

				//settingsBtn.on('click', function(){cntr.showSettingsWin()});
				break;
			}
			case 'krym' : {
				teamsGroupFilterSettingsTool.hide();

				//settingsBtn.on('click', function(){cntr.showSettingsWin()});

				//topToolbar.down('button[refId=vid1_btn]').hide();
				//topToolbar.down('button[refId=vid2_btn]').hide();
				break;
			}
			case 'pskov' : {
				topToolbar.down('button[refId=wialon_btn]').show();
				topToolbar.down('button[refId=glonass_btn]').show();
				break;
			}
			case 'kz' : {
				teamsGroupFilterSettingsTool.hide();

				//settingsBtn.on('click', function(){cntr.showSettingsWin()});
				break;
			}
		}
	},

	showSettingsWin: function(){
		if (getGlobalOptions().IsLocalSMP) {
			return;
		}

		openSelectSMPStationsToControlWindow();
	},

	setHotKeys: function(cmp){
		var win = this.getEmergecyWindowRef(),
			callsView = win.CallsView,
			teamsView = win.TeamsView,
			topToolbar = win.down('toolbar[refId=DPTopToolbar]'),
			me = this;

		var pressedkeyg = new Ext.util.KeyMap({
			target: cmp.el,
			binding: [
				{
					key: [Ext.EventObject.ENTER],
					fn: function(){
						var recCard = callsView.getSelectionModel().getSelection()[0],
							recTeam = teamsView.getSelectionModel().getSelection()[0];

						if (callsView.hasCls('focused-panel')&&recCard){
							this.showCmpCallCardSubMenu(null, null, callsView, teamsView, false);
						}
						if (teamsView.hasCls('focused-panel')&&recTeam){
							this.showEmergencyTeamSubMenu(null, null, callsView, teamsView);
						}
					}.bind(this)
				},
				{
					key: [Ext.EventObject.TAB],
					fn: function(c, e){
						//tab
						if (callsView.hasCls('focused-panel')||teamsView.hasCls('focused-panel')){
							//e.preventDefault();
							if (callsView.hasCls('focused-panel')&&!teamsView.hasCls('focused-panel')){
								teamsView.focus();
							}
							if (!callsView.hasCls('focused-panel')&&teamsView.hasCls('focused-panel')){
								callsView.focus(null, 100);
							}
							/*if (!callsView.hasCls('focused-panel')&&!teamsView.hasCls('focused-panel')){
								callsView.getEl().toggleCls('focused-panel');
							}
							else{*/
								callsView.getEl().toggleCls('focused-panel');
								teamsView.getEl().toggleCls('focused-panel');
							//}
						}

						//после кнопок переносим фокус на панель вызовов
						if (e.getTarget().id == topToolbar.items.last().btnEl.id){
							e.preventDefault();
							callsView.focus();
							callsView.getEl().toggleCls('focused-panel');
						}
					}
				},
				{
					key: [Ext.EventObject.ESC],
					fn: function(c, e){
						if(me.getMapPanelRef().getHeight()>0){me.toggleExpandMapPanel();}
						if (callsView.hasCls('focused-panel')||teamsView.hasCls('focused-panel')){
							callsView.hasCls('focused-panel')?callsView.removeCls('focused-panel'):teamsView.removeCls('focused-panel');
							//Ext.getCmp('Mainviewport_Toolbar').items.getAt(0).focus();
							win.down('toolbar[refId=DPTopToolbar]').items.getAt(0).focus();
						}

						e.preventDefault();
					}
				},
				{
					key: [Ext.EventObject.F4],
					fn: function(c, evt){
						me.toggleExpandMapPanel();
					}
				}
			]
		});
	},
	
	toggleExpandMapPanel: function(){
		var h = this.getEmergecyWindowRef().body.getHeight()-35,
			mapWin = this.getMapPanelRef(),
			vrButton = Ext.ComponentQuery.query('swDispatcherStationWorkPlace button[refId=vr-splitter]')[0];

		if((h) == mapWin.getHeight())
		{
			mapWin.setHeight(0);			
			vrButton.setIconCls('top-splitter');
			vrButton.getEl().removeCls('bottom-splitter');			
		} 
		else{mapWin.setHeight(h);}
		
		if (mapWin.getCurrentMapType() == 'google'||'wialon')
		{
			if(typeof google != 'undefined')google.maps.event.trigger(mapWin.getPanelByType(mapWin.getCurrentMapType()).map,'resize');
		}
		if (mapWin.getCurrentMapType() == 'here')
		{
			var hereMap = mapWin.getPanelByType(mapWin.getCurrentMapType());
			hereMap.map?hereMap.map.getViewPort().resize():false;
		}
		if (mapWin.getCurrentMapType() == 'yandex'){
			if(typeof ymaps != 'undefined')
				mapWin.getPanelByType(mapWin.getCurrentMapType()).map.container.fitToViewport();
		}
		mapWin.up('container').doLayout();
	},
	
	printCloseTalonBill: function(data){
		if (!data['CmpCallCard_id']) {
			return false;
		}
		
		Ext.Ajax.request({
			url: '/?c=CmpCallCard4E&m=printCmpCallCardCloseTicket',
			params: {
				CmpCallCard_id: data.CmpCallCard_id
			},
			callback: function(opt, success, response){
				if (success){
					Ext.MessageBox.confirm('Сообщение', 'Распечатать форму 110у?',function(btn){
						if( btn === 'yes' ){							
							var id_salt = Math.random();
							var win_id = 'print_110u' + Math.floor(id_salt * 10000);
							var win = window.open('/?c=CmpCallCard&m=printCmpCloseCard110&CmpCallCard_id=' + data.CmpCallCard_id, win_id);							
						}
					}.bind(this));
					var win = window.open();
					win.document.write(response.responseText);
					win.document.close();
				}
			}
		})	
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
	
	rejectCallCard: function(rec) {
		var cntr = this,
			data = rec.data,
			win = this.getEmergecyWindowRef();
		

				var params = {
					CmpCallCardStatusType_id: (data.SmpUnitParam_IsDenyCallAnswerDisp == 2) ? 21 : 18,
					CmpCallCard_id:	data.CmpCallCard_id
				};

				Ext.Ajax.request({
					url: '/?c=CmpCallCard4E&m=setStatusCmpCallCard',
					params: params,
					success: function(response){
						var obj = Ext.decode(response.responseText);
						if ( obj.success ) {
							Ext.Ajax.request({
								url: '/?c=CmpCallCard&m=setCmpCallCardEvent',
								callback: function(opt, success, response) {
									cntr.reloadStores();
								}.bind(this),
								params: {								
									'CmpCallCard_id': data.CmpCallCard_id,
									'CmpCallCardEventType_Code': 34 //Отклонен
								}
							});
						}
					},
					failure: function(response, opts){
						Ext.Msg.alert('Ошибка','Во время отказа произошла ошибка, обратитесь к администратору');
					}
				});

	},

	showCmpCallCardSubMenu: function(x,y, callsView, teamsView, shortView){
		if (getGlobalOptions().IsLocalSMP) {
			// АРМ доступен только для просмотра
			// Все кнопки/меню не доступны
			return false;
		}
		
		var cntr = this,
			mapPanel = cntr.getMapPanelRef(),
			win = this.getEmergecyWindowRef(),
			//callsView = win.CallsView,
			//teamsView = win.TeamsView,
			storeTeams = Ext.data.StoreManager.lookup('common.DispatcherStationWP.store.EmergencyTeamStore'),
			storeCalls = Ext.data.StoreManager.lookup('common.DispatcherStationWP.store.CmpCallsStore'),
			recCardInSelection = callsView.getSelectionModel().getSelection()[0],
			//recTeamInSelection = teamsView.getSelectionModel().getSelection()[0],
			//оказывается, что getSelection хранит старые данные
			recCard = recCardInSelection ? callsView.getStore().findRecord('CmpCallCard_id', recCardInSelection.get('CmpCallCard_id')) : null,
			//recTeam = recTeamInSelection ? teamsView.getStore().findRecord('EmergencyTeam_id', recTeamInSelection.get('EmergencyTeam_id')) : null,
			recTeam = storeTeams.findRecord('EmergencyTeam_id', recCardInSelection.get('EmergencyTeam_id')),
			recCardGroupId = parseInt(recCard.get('CmpGroupTable_id'));

		var subMenu = Ext.create('Ext.menu.Menu', {
				//width: 100,
				//showSeparator: false,
				plain: true,
				renderTo: Ext.getBody(),
				constrainTo: (x&&y)? null : callsView.getSelectedNodes()[0].querySelector('.cell-cmpcc-moreinfo'),
				items: [
					/*{
						text: 'Информация',
						hidden: shortView,
						handler: function(){
							this.showCmpCallCardFromGrid(recCard);
							subMenu.close();
						}.bind(this)
					},*/
					{
						text: 'Карточка вызова 112',
						hidden: !(recCard.get('CmpCallCardStatusType_id') == 20),
						handler: function(){
							this.showCmpCallCard112(recCard);
							subMenu.close();
						}.bind(this)
					},
					{
						text: 'Печать карты вызова НМП',
						hidden: (!cntr.isNmpArm || recCard.get('CmpCallCard_IsExtra').inlist([1])),
						handler: function () {
							printBirt({
								'Report_FileName': 'CmpCallCardPrintedForm.rptdesign',
								'Report_Params': '&paramCmpCallCard=' + recCard.get('CmpCallCard_id'),
								'Report_Format': 'pdf'
							});

						}
					},
					{
						text: 'Печать КТ',
						hidden: !(recCard.get('EmergencyTeam_id') && recCard.get('CmpCallCard_id')) || cntr.isNmpArm,
						handler: function(){
							if (getRegionNick().inlist(['ufa','krym'])) {
								var location = '/?c=CmpCallCard&m=printCmpCallCardHeader&CmpCallCard_id=' + recCard.get('CmpCallCard_id');
								var win = window.open(location);
							} else {
								this.printControlBill({
									EmergencyTeam_id: recCard.get('EmergencyTeam_id'),
									CmpCallCard_id: recCard.get('CmpCallCard_id')
								});
							}
							subMenu.close();
						}.bind(this)
					},
					{
						xtype: 'menuseparator',
						hidden: !(recCard.get('EmergencyTeam_id') && recCard.get('CmpCallCard_id')) || cntr.isNmpArm
					},
					{
						text: 'В дублирующие',
						hidden: cntr.isNmpArm,
						disabled: !( (recCard.get('CmpCallType_Code') != 14) && (recCardGroupId==1)), //Активно для поступивших первичных вызовов (в группе 1)
						handler: function(){
							var checkBy = 'DP_doDouble',
								filterCmpCalls = [];

							this.selectFirstCardWin = Ext.create('sw.tools.swSelectFirstSmpCallCard', {
								params: {
									checkBy: checkBy,
									filterCmpCalls: filterCmpCalls,
									CmpCallCard_id: recCard.raw.CmpCallCard_id,
									filterByName: recCard.raw.Person_Firname,
									filterByFamily: recCard.raw.Person_Surname,
									filterBySecName: recCard.raw.Person_Secname,
									KLCity_id: (recCard.raw.KLCity_id)?recCard.raw.KLCity_id : 0,
									KLRegion_id: (recCard.raw.KLRgn_id)?recCard.raw.KLRgn_id : 0,
									KLStreet_id: (recCard.raw.KLStreet_id)?recCard.raw.KLStreet_id : 0,
									KLSubRGN_id: (recCard.raw.KLSubRgn_id)?recCard.raw.KLSubRgn_id : 0,
									KLTown_id: (recCard.raw.KLTown_id)?recCard.raw.KLTown_id : 0,
									domField: recCard.raw.CmpCallCard_Dom,
									korpField: recCard.raw.CmpCallCard_Korp,
									kvarField: recCard.raw.CmpCallCard_Kvar,
									Adress_Name: recCard.raw.Adress_Name
								},
								showByDp: true,
								listeners:{
									doubleCall: function(rec){

										var params = {
											CmpCallCard_id:	recCard.get('CmpCallCard_id'),
											CmpCallCardStatusType_Code: 9, //дубль
											CmpCallType_Code: 14, //Дублирующий
											CmpCallCard_rid: rec.get('CmpCallCard_id'),
											armtype: 'smpdispatcherstation'
										};

										Ext.Ajax.request({
											url: '/?c=CmpCallCard4E&m=setStatusCmpCallCard',
											params: params,
											success: function(response, opts){
												var obj = Ext.decode(response.responseText);
												if ( obj.success ) {
													var cmpCallCardParam = {
														CmpCallCard_id: opts.params.CmpCallCard_id,
														EmergencyTeam_id: rec.get('EmergencyTeam_id'),
														Comment: opts.params.CmpCallCardStatus_Comment
													};
													if(cmpCallCardParam.EmergencyTeam_id){
														win.socket.emit('registrationFailure', cmpCallCardParam, function(data){
															log('NodeJS emit registrationFailure');
														});
														// win.socket.on('registrationFailure', function(data){
														// 	log('NodeJS ON registrationFailure');
														// });
													}

													cntr.reloadStores();

												}
											}
										});

									}.bind(this)
								}
							}).show();
							subMenu.close();
						}.bind(this)
					},
					{xtype: 'menuseparator', hidden: cntr.isNmpArm},
					{
						text: 'Создать попутный',
						disabled: !Ext.Array.contains([3], recCardGroupId) || (recCard.get('CmpCallType_Code') == 4), //доступна для вызовов в статусе «Принято» с Типом вызова отличающимся от «Попутный».
						handler: function(){
							cntr.showWorkPlaceSMPDispatcherCallWindow(false,'oneWayCallCard', cntr.isNmpArm, callsView);

							/*getWnd('swWorkPlaceSMPDispatcherCallWindow').show({
								showByDP: true,
								onSaveByDp: function(card_id){
									getWnd('swWorkPlaceSMPDispatcherCallWindow').hide();

										возможно этот участок кода понадобится, но он сбрасывет статус у бриады + записывает в историю статусов
										Ext.Ajax.request({
											url: '/?c=CmpCallCard4E&m=setEmergencyTeamWithoutSending',
											params: {
											EmergencyTeam_id: params.EmergencyTeam_id,
											CmpCallCard_id: card_id
											}
										});

									cntr.reloadStores(function(){
										//выделить запись
										var addedRec = storeCalls.findRecord('CmpCallCard_id', card_id);
										if(addedRec){
											callsView.getSelectionModel().select(addedRec);
										}
									});
								},
								params: params
							});*/

							subMenu.close();
						}.bind(this)
					},
					{
						text: 'Создать для спецбригады',
						hidden: cntr.isNmpArm,
						disabled: !Ext.Array.contains([3], recCardGroupId), //Активно для вызовов из группы 3
						handler: function(){
							cntr.showWorkPlaceSMPDispatcherCallWindow(false,'forSpecialEmergencyTeam', cntr.isNmpArm, callsView);
							//win.show();
							subMenu.close();
							/*getWnd('swWorkPlaceSMPDispatcherCallWindow').show({
								showByDP: true,
								onSaveByDp: function(card_id){
									getWnd('swWorkPlaceSMPDispatcherCallWindow').hide();

									cntr.reloadStores(function(){
										//выделить запись
										var addedRec = storeCalls.findRecord('CmpCallCard_id', card_id);
										if(addedRec){
											callsView.getSelectionModel().select(addedRec);
										}
									});
								},
								params: params
							});*/
						}.bind(this)
					},
					{
						text: 'Копировать',
						handler: function(){
							cntr.showWorkPlaceSMPDispatcherCallWindow(false,'copyCallCard', cntr.isNmpArm, callsView);

							/*getWnd('swWorkPlaceSMPDispatcherCallWindow').show({
								onSaveByDp: function(card_id){
									getWnd('swWorkPlaceSMPDispatcherCallWindow').hide();

									cntr.reloadStores(function(){
										//выделить запись
										var addedRec = storeCalls.findRecord('CmpCallCard_id', card_id);
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
						text: 'Передать на подстанцию',
						hideOnClick: false,
						hidden: !(recCard.get('IsSendCall') == '2' && recCard.get('IsNoTrans') != '2' && recCard.get('CmpCallCardStatusType_id').inlist([1,3])),
						handler: function(i){
							i.cancelDeferHide();
							i.doExpandMenu();
						},
						menu: {
							xtype: 'menu',
							itemId: 'LpuBuildingDynamicSubMenu',
							showSeparator: false,
							listeners: {
								beforeshow: function(c){
									Ext.Ajax.request({
										url: '/?c=CmpCallCard4E&m=loadSmpUnitsNestedALL',
										callback: function(opt, success, response){
											if (success){
												var obj = Ext.decode(response.responseText);

												for(var i in obj){
													c.add({text: obj[i].LpuBuilding_Name, value: obj[i].LpuBuilding_id});
												}
											}
										}
									});
								},
								beforehide:function(c){
									c.removeAll();
								},
								click: function(m,i)
								{
									Ext.MessageBox.confirm('Сообщение',
										'Передать вызов на подстанцию ' + i.text + '?', function (btn) {
											if (btn === 'yes') {
												var LpuBuilding_id = i.value,
													Card_id = recCard.get('CmpCallCard_id');

												Ext.Ajax.request({
													url: '/?c=CmpCallCard4E&m=sendCmpCallCardToLpuBuilding',
													params: {
														'LpuBuilding_id': LpuBuilding_id,
														'CmpCallCard_id': Card_id
													},
													callback: function(opt, success, response){
														cntr.reloadStores();
													}
												});
											}
										}.bind(this));


								}
							}
						}
					},
					{xtype: 'menuseparator', hidden: cntr.isNmpArm},
					{
						text: 'На бригаду',
						hideOnClick: false,
						hidden: recCard.get('CmpCallCard_IsExtra').inlist([4]),
						itemId: 'TeamsToCallDynamicOpenSubMenu',
						disabled: (
							( !cntr.isNmpArm && (getGlobalOptions().SmpUnitType_Code == 2 || !Ext.Array.contains([1], recCardGroupId) || (!teamsView.store.getCount())) ) ||
							//я так понял, что для нмп арма у нас другие условия, решил расширить условие
							( cntr.isNmpArm &&
								(
									getGlobalOptions().SmpUnitType_Code == 2 ||
									(!Ext.Array.contains([1], recCardGroupId) || (!teamsView.store.getCount())) ||
									!recCard.get('CmpCallType_Code').inlist([1,19]) ||
									!recCard.get('CmpCallCard_IsExtra').inlist([2,3])
								)
							)
						), //Активно для вызовов из группы 1, подчиненные подстанции не могут назначать бригады
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

									mapPanel.getAllAmbulancesArrivalTimeToAccident( [team.get('GeoserviceTransport_id')] , recCard.get('CmpCallCard_id'), function( data ) {
										cntr.setCenterEmergencyTeamOnMap(team, true, data? data[0]['durationText']: null, callsView, teamsView );
									});
								}
							}
						}		
					},
					{
						text: 'Отклонить бригаду',
						hidden: !(recCard.get('CmpCallCardStatusType_id') == 2 && recTeam && recCard.get('EmergencyTeamStatus_Code').inlist([1,8,36,48])) || recCard.get('CmpCallCard_IsExtra').inlist([4]),
						disabled: false,
						handler: function(){
							if(cntr.isNmpArm){
								Ext.create('sw.tools.swSelectNmpReasonWindow', {
									CmpCallCard_id:	recCard.get('CmpCallCard_id'),
									operation: 'resetTeam',
									listeners: {
										saveResult: function(win, store){
											cntr.reloadStores();
										}
									}
								}).show();
							}else{
								cntr.resetEmergencyTeamFromCall(callsView, teamsView);
							}

						}
					},
					{
						text: 'Отложить',
						hidden: (recCardGroupId != 1) || (cntr.isNmpArm && recCard.get('CmpCallCard_IsExtra').inlist([1,4])),
						handler: function(menuBtn){
							cntr.showDefferedCallWindow(recCard, callsView, menuBtn.getX(), menuBtn.getY())
						}
					},
					{
						text: 'Вывести из отложенных',
						hidden: (recCardGroupId != 7),
						handler: function(){
							cntr.setDefferedCallToTransmitted(recCard,callsView)
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
						text: 'Отклонить вызов',
						hidden: cntr.isNmpArm || !getRegionNick().inlist(['perm']) || (recCard.get('SmpUnitParam_IsDenyCallAnswerDisp') == 1 && recCard.get('LpuBuilding_IsDenyCallAnswerDoc') == 1) || (recCardGroupId != 1) || (recCard.get('SmpUnitType_Code') != 5),
						handler: function(){
							cntr.rejectCallCard(recCard);
						}
					},
					{xtype: 'menuseparator'},
					{
						text: 'Печать шапки для 110у',
						hidden: (!getRegionNick().inlist(['ufa', 'krym'])),
						//disabled: !Ext.Array.contains([2, 3], recCardGroupId), //Активно для вызовов из группы 3, 4
						handler: function(){							
							var location = '/?c=CmpCallCard&m=printCmpCallCardHeader&CmpCallCard_id='+recCard.get('CmpCallCard_id');
							var win = window.open(location);
						}					
					},
					{
						text: 'Вызов исполнен',
						disabled: !((recCardGroupId == 3) || (cntr.isNmpArm && recCard.get('CmpCallCard_IsExtra').inlist([1,3,4]) && recCardGroupId == 1)), //Активно для вызовов из группы 3 или для вызовов на пол-ку, на дом и экстренных в нмп
						handler: function(){

							if(cntr.isNmpArm){
								switch(recCard.get('CmpCallCard_IsExtra')){
									case 1:

										//Статус вызова меняется на «Обслужено»
										Ext.Ajax.request({
											url: '/?c=CmpCallCard4E&m=setStatusCmpCallCard',
											params: {
												CmpCallCardStatusType_Code: 4, //Обслужено
												CmpCallCard_id: recCard.get('CmpCallCard_id'),
												armtype: 'smpdispatcherstation'
											},
											success: function (response, opts) {
												var obj = Ext.decode(response.responseText);
												if (obj.success) {
													cntr.reloadStores();
												}
											}
										});
										//Результат обслуживания НМП заполняется значением «Вызов передан СМП»
										Ext.Ajax.request({
											url: '/?c=CmpCallCard&m=setResult',
											params: {
												CmpPPDResult_Code: 22, //Вызов передан СМП
												CmpCallCard_id: recCard.get('CmpCallCard_id')
											}
										});

										break;
									default:
										Ext.create('sw.tools.swSelectNmpReasonWindow', {
											CmpCallCard_id:	recCard.get('CmpCallCard_id'),
											operation: 'ppdResult',
											listeners: {
												saveResult: function(){
													callsView.getStore().reload();
													teamsView.getStore().reload();
												}
											}
										}).show();
								}

							}else{
								cntr.setCardStatus(recCard.get('CmpCallCard_id'), recCard.get('EmergencyTeam_id'), 4, false, teamsView, callsView);
							}

							subMenu.close();
						}.bind(this)
					},
					{xtype: 'menuseparator', hidden: ((!getRegionNick().inlist(['ufa', 'krym'])) || recCard.get('CmpGroup_id') != '3')},
					{
						text: 'Закрыть карту вызова',
						hidden: (recCard.get('CmpGroup_id') != '3' || cntr.isNmpArm),
						disabled: !Ext.Array.contains([3], recCard.get('CmpGroup_id')) || recCard.get('CmpGroupTable_id').inlist([7]), //Активно для вызовов из группы 3 и не отложенные
						//disabled: ((getGlobalOptions().region.nick != 'ufa' && getGlobalOptions().region.nick != 'krym') || recCard.get('CmpGroup_id') != '3'),
						handler: function(){
							var action = recCard.get('CmpCloseCard_id') > 0 ? 'edit' : 'add';
							cntr.showCmpCloseCardFromExt2(false, recCard.get('CmpCallCard_id'), action);
						}
					},
                    {
						text: 'Прослушать аудиозапись',
						hidden: !(recCard.get('CmpCallRecord_id')),
						handler: function(){
							subMenu.close();

							Ext.create('common.tools.swCmpCallRecordListenerWindow',{
								record_id : recCard.get('CmpCallRecord_id')
							}).show();
						}.bind(this)
                    },
					{
						text: 'История вызова',
						hidden: recCard.get('CmpCallCard_IsExtra').inlist([4]),
						handler: function(){
							subMenu.close();
							var callCardHistoryWindow = Ext.create('sw.tools.swCmpCallCardHistory',{
								card_id: recCard.get('CmpCallCard_id')
							});
							callCardHistoryWindow.show();
						}.bind(this)
					},
					{
						text: 'Передать в поликлинику',
						hidden: ((recCardGroupId != 1) || recCard.get('CmpCallCard_IsExtra') != 3 || recCard.get('Person_IsUnknown') == 2 ),
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
						text: 'Талон первичного вызова',
						hidden: !(getRegionNick() == 'kareliya') || !recCard.data.CmpCallCard_rid || !(recCard.data.CmpCallType_Code != 2),
						handler: function () {
							cntr.showWndFromExt2('swCmpCallCardNewShortEditWindow', recCard.data.CmpCallCard_id)
						}
					},
					{
						text: 'Карта первичного вызова',
						hidden: !(getRegionNick() == 'kareliya') || !recCard.data.CmpCloseCard_rid || !(recCard.data.CmpCallType_Code != 2),
						handler: function () {
							cntr.showWndFromExt2('swCmpCallCardNewCloseCardWindow', recCard.data.CmpCallCard_rid);
						}
					}
				]
			}),
			subMenuEmergencyTeamsToCall = subMenu.down('menu[itemId=TeamsToCallDynamicSubMenu]');

		/*
		 @todo наработка по сортировке подменю "На бригаду" (возможно не нужна будет, тестирование задачи #129395 покажет) - после тестирования удалить
		 var storeTeams = Ext.data.StoreManager.lookup('common.DispatcherStationWP.store.EmergencyTeamStore');
		 var arr = [];
		 storeTeams.each(function(rec){

		 arr.push({
		 text: rec.get('EmergencyTeam_Num')+' '+rec.get('EmergencyTeamStatus_Name') + ' (' + rec.get('Lpu_Nick') + ')',
		 value: rec.get('EmergencyTeam_id'),
		 EmergencyTeamStatus_Code: rec.get('EmergencyTeamStatus_Code'),
		 EmergencyTeam_Num: rec.get('EmergencyTeam_Num')
		 })
		 //console.log(rec.get('EmergencyTeam_Num'))
		 });

		 arr.sort(function(a,b,c){

		 if(a.EmergencyTeamStatus_Code!=b.EmergencyTeamStatus_Code){
		 return a.EmergencyTeamStatus_Code>b.EmergencyTeamStatus_Code? -1: 1
		 }
		 else{
		 return a.EmergencyTeam_Num > b.EmergencyTeam_Num? -1: 1
		 }
		 })
		 arr
		*/
		teamsView.store.each(function(rec){
			if( rec.get('EmergencyTeamDuty_isNotFact')==1 || rec.get('WorkAccess') == 'false') {
				// Бригады, плановая смена которых закончилась, но не закончилась фактическая смена будут НЕдоступны для назначения на вызов #94154
				return;
			};
			subMenuEmergencyTeamsToCall.add({
				text: rec.get('EmergencyTeam_Num')+' '+rec.get('EmergencyTeamStatus_Name') + ' (' + rec.get('Lpu_Nick') + ')',
				value: rec.get('EmergencyTeam_id')
			});
		});
		if( subMenuEmergencyTeamsToCall.items.length == 0) subMenu.down('[itemId=TeamsToCallDynamicOpenSubMenu]').disable();
			
		subMenu.showAt(x,y);
	},

	showEmergencyTeamSubMenu: function(x,y, callsGrid, teamsGrid){
		var cntr = this,
			win = this.getEmergecyWindowRef(),
			callsView = callsGrid,
			teamsView = teamsGrid,
			//callsView = win.CallsView,
			//teamsView = win.TeamsView,
			recCard = callsView.getSelectionModel().getSelection()[0],
			recTeam = teamsView.getSelectionModel().getSelection()[0],
			storeTeamStatus = Ext.data.StoreManager.lookup('common.DispatcherDirectWP.store.EmergencyTeamStatusStore'),
			storeTeams = Ext.data.StoreManager.lookup('common.DispatcherStationWP.store.EmergencyTeamStore'),
			storeCalls = Ext.data.StoreManager.lookup('common.DispatcherStationWP.store.CmpCallsStore'),
			// В табличном виде EXT ведет себя некультурно, выдает старые значения, хотя стор давно обновлен
			index_selTeam = storeTeams.find('EmergencyTeam_id',recTeam.get('EmergencyTeam_id')),
			record_Team = storeTeams.getAt(index_selTeam),
			index_selCall = storeCalls.find('CmpCallCard_id',record_Team.get('CmpCallCard_id')),
			record_Call = storeCalls.getAt(index_selCall),
			getToDepartureMenuItem = function(){
				if(getGlobalOptions().SmpUnitType_Code == 2) return; //Подчиненные подстанции не могут назначать бригаду на вызов
				var isNotFact = ( record_Team.getData().EmergencyTeamDuty_isNotFact != 1 ) ? false : true;
				
				return Ext.create('Ext.menu.Item', {
					text: 'На вызов', 
					disabled: isNotFact || (!cntr.isNmpArm && !record_Team.get('EmergencyTeamStatus_Code').inlist([13,21])),
					hidden: recTeam.get('WorkAccess') == 'false',
					menu: {
						xtype: 'menu',
						itemId: 'CmpCallsToEmergencyDynamicSubMenu',
						closeAction: 'destroy',
						listeners:{
							click: function(m,i)
							{
								var CmpCallCard_id = i.value;
								
								callsView.getSelectionModel().select(callsView.store.findRecord('CmpCallCard_id', CmpCallCard_id));	
									
								cntr.setEmergencyTeamToCall(callsView, teamsView);
							}		
						}			
					}
				});
			},
			subMenu = Ext.create('Ext.menu.Menu', {
				showSeparator: false,
				renderTo: Ext.getBody(),
				constrainTo: (x&&y)? null : teamsView.getSelectedNodes()[0],
				listeners:{
					hide: function(t,e){
						win.defaultFocus = 'teamGrid';
					}
				},
				items: [
					{
						text: 'Статус',
						hideOnClick: false,
						hidden: recTeam.get('WorkAccess') == 'false',
						handler: function(i){
							i.cancelDeferHide();
							i.doExpandMenu();
						},
						menu: {
							xtype: 'menu',
							itemId: 'EmergencyStatusDynamicSubMenu',
							closeAction: 'destroy',
							listeners: {
								show: function(sm){
									var toSelectItem = sm.down('menuitem[value='+record_Team.get('EmergencyTeamStatus_id')+']');
									if (toSelectItem){
										toSelectItem.setIconCls('ok16');
									}
								},
								click: function(m,i)
								{
									var status = i.value,
										code = i.code,
										// recTeam странным образом содержит старые записи несмотря на то, что стор обновлен 
										// team = recTeam.get('EmergencyTeam_id'),
										// call = recTeam.get('CmpCallCard_id'),
										team = record_Team.get('EmergencyTeam_id'),
										team_num = record_Team.get('EmergencyTeam_Num'),
										call_ngod = record_Team.get('CmpCallCard_Ngod'),
										call = record_Team.get('CmpCallCard_id'),
										armType = (sw.Promed.MedStaffFactByUser.last != null) ? sw.Promed.MedStaffFactByUser.last.ARMType : sw.Promed.MedStaffFactByUser.current.ARMType;

									switch (code){
										case 53:
										case 3 : {
											if(call){
												//Начало госпитализации
												var swHospitalizedWindow = Ext.create('common.DispatcherStationWP.tools.swHospitalizedWindow',{
													listeners: {
														selectAction: function(data){ //Diag_id,lpu_id,LpuSection,Code
															recCard = callsView.getSelectionModel().getSelection()[0];
															var person = null;
															if(Ext.isEmpty(record_Call)) {
																if (!(Ext.isEmpty(recCard)))
																	person = recCard.get('Person_id');
															}
															else
																person = record_Call.get('Person_id');

															swHospitalizedWindow.close();
															if(call){
																var hospParams = {
																	Lpu_hid: data.Lpu_id,
																	LpuSection_id: data.LpuSection_id,
																	Code: data.Code,
																	CmpCallCard_id: call,
																	EmergencyTeam_id: team,
																	EmergencyData_BrigadeNum: team_num,
																	EmergencyData_CallNum: call_ngod,
																	EvnDirection_IsAuto: 2,
																	Person_id: person,
																	Diag_id: data.Diag_id,
																	cmpcommonstate_id: data.cmpcommonstate_id
																}

																var setLpuHospitalized = function() {
																	Ext.Ajax.request({
																		url: '/?c=CmpCallCard4E&m=setLpuHospitalized',
																		params: hospParams,
																		callback: function(opt, success, response){
																			var callbackParams =  Ext.decode(response.responseText);
																			if ( callbackParams.success == false ) {
																				var error_msg = (callbackParams.Error_Msg)?callbackParams.Error_Msg:'Человек не идентифицирован.</br>Автоматическое резервирование койки невозможно';
																				var armWindowEl = win.getEl(),
																					alertBox = Ext.create('Ext.window.Window', {
																						title: 'Резервирование невозможно',
																						height: 80,
																						width: 350,
																						layout: 'fit',
																						constrain: true,
																						cls: 'waitingForAnswerHeadDoctorWindow',
																						header: false,
																						constrainTo: armWindowEl,
																						layout: {
																							type: 'vbox',
																							align: 'bottom'
																						},
																						items: [
																							{
																								xtype: 'label',
																								flex: 1,
																								html: "<a>"+error_msg+"</a>"
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
																				alertBox.showAt([(armWindowEl.getWidth()-400), armWindowEl.getHeight()-100]);
																				setTimeout(function(){alertBox.close()},3000);
																			}
																			else {
																				var params = {
																					EmergencyTeam_id: team,
																					CmpCallCard_id: call,
																					Lpu_hid: data.Lpu_id
																				};
																				win.socket.emit('setEmergencyTeamStatusBeginHospitalized', params, function(data){
																					var emergency_id = ( data ) ? data : 0;
																					console.log('NODE emit setEmergencyTeamStatusBeginHospitalized : '+emergency_id);
																				});
																			}
																		}
																	});
																}

																if(getRegionNick() == 'ufa' && ( data.diagType == 'OKS' || data.ScaleLams_Value || data.PrehospTraumaScale_Value )) {
																	var scaleParams = Object.assign(hospParams, data);
																	cntr.saveHospForm( scaleParams, setLpuHospitalized );
																} else {
																	setLpuHospitalized();
																}

															};
															
															var params = {
																'EmergencyTeamStatus_id': status,
																'EmergencyTeamStatus_Code': code,
																'EmergencyTeam_id':	team,
																'ARMType': armType,
																'CmpCallCard_id': call
															};
															
															cntr.setEmergencyTeamStatus(params, teamsView, callsView);
															
														},
														changeEtStatusOperTable: function() {
															swHospitalizedWindow.close();

															var params = {
																'EmergencyTeamStatus_id': status,
																'EmergencyTeamStatus_Code': code,
																'EmergencyTeam_id':	team,
																'ARMType': armType,
																'CmpCallCard_id': call
															};

															cntr.setEmergencyTeamStatus(params, teamsView, callsView);
														}

													}
												});
												var windowParams = {};
												windowParams.params = {};
												windowParams.teamsView = win.TeamsView;
												if(record_Call && record_Call.data) {
													windowParams.params = record_Call.data;
												}
												windowParams.teamStore = Ext.data.StoreManager.lookup('common.DispatcherStationWP.store.EmergencyTeamStore');
												windowParams.params.formType = (code == 53) ? 'traum' : 'hosp';
												swHospitalizedWindow.show(windowParams);
											}
											break;
										}
										case 4: {
											//Конец обслуживания
											if (status && team)
											{

												if(cntr.isNmpArm){
													if(recCard){
														Ext.create('sw.tools.swSelectNmpReasonWindow', {
															CmpCallCard_id:	recCard.get('CmpCallCard_id'),
															operation: 'ppdResult',
															listeners: {
																saveResult: function(){
																	callsView.getStore().reload();
																	teamsView.getStore().reload();
																}
															}
														}).show();
													}else{
														cntr.setEmergencyTeamStatus(
															{
																'EmergencyTeamStatus_Code': code,
																'EmergencyTeam_id':	team,
																'ARMType': (sw.Promed.MedStaffFactByUser.last != null) ? sw.Promed.MedStaffFactByUser.last.ARMType : sw.Promed.MedStaffFactByUser.current.ARMType
															},
															teamsView,
															callsView
														);
													}

												}else{
													if(call)
													{
														cntr.setCardStatus(call, team, code, false, teamsView, callsView);
													}
													else{
														cntr.setEmergencyTeamStatus(
															{
																'EmergencyTeamStatus_Code': 4,
																'EmergencyTeam_id':	team,
																'ARMType': (sw.Promed.MedStaffFactByUser.last != null) ? sw.Promed.MedStaffFactByUser.last.ARMType : sw.Promed.MedStaffFactByUser.current.ARMType
															},
															teamsView,
															callsView
														);
													}
												}
											}
											break;
										}
										case 13 : {
											if (status && team)
											{
												var params = {
													'EmergencyTeamStatus_id': status,
													'EmergencyTeamStatus_Code': code,
													'EmergencyTeam_id':	team,
													'ARMType': armType,
													'CmpCallCard_id':  null
												};

												cntr.setEmergencyTeamStatus(params, teamsView, callsView);

											}
											break;
										}
										case 50 : {
											//статус Дежурство
											if(call){
												Ext.Msg.alert('Ошибка', 'Бригада назначена на вызов № '+recCard.get('CmpCallCard_Numv')+', '+recCard.get('Person_FIO')+'. Смена статуса бригады на «Дежурство» невозможна. Отклоните бригаду с вызова и повторите действие');
												return;
											}
											else{
												var params = {
													action: 'add',
													EmergencyTeam_id: team,
													EmergencyTeamDuty_DTStart: Ext.Date.format(new Date(), 'Y-m-d H:i:s'),
													CmpEmTeamDuty_FactBegDT: Ext.Date.format(new Date(), 'Y-m-d H:i:s')
												};
												
												var emergencyTeamVigilWindow = Ext.create('sw.tools.subtools.swEmergencyTeamVigilWindow', params);
												
												emergencyTeamVigilWindow.show();
												
												emergencyTeamVigilWindow.on('saveVigil', function(){
													emergencyTeamVigilWindow.close();
													
													var statusparams = {
														'EmergencyTeamStatus_id': status,
														'EmergencyTeamStatus_Code': code,
														'EmergencyTeam_id':	team,
														'ARMType': armType,
														'CmpCallCard_id': call
													};
													cntr.setEmergencyTeamStatus(statusparams, teamsView, callsView);
													
												})
											}
											break;
										}
										default: {
											if (status && team)
											{
												var params = {
													'EmergencyTeamStatus_id': status,
													'EmergencyTeamStatus_Code': code,
													'EmergencyTeam_id':	team,
													'ARMType': armType,
													'CmpCallCard_id': call
												};
												cntr.setEmergencyTeamStatus(params, teamsView, callsView);
												
											}
										}
									}
								}
							}
						}
					},{
						text: 'Информация',
						handler: function(){
							cntr.showEmergencyTeamInfoFromGrid(recTeam);
							//@to_do - вынужденная мера, тк хэндлер срабатывает 2 раза
							//будет время -  разобраться
							subMenu.close();
						}
					},
					/*
	//				{
	//					text: 'Состав',
	//					handler: function(){
	//						cntr.showEmergencyTeamEditStuffInfoFromGrid(recTeam);
	//						subMenu.close();
	//					}
	//				},
					{
						text: 'Замена авто',
						disabled: true	
					},*/
					{
						text: 'Информация о КТ',
						hidden: recTeam.get('WorkAccess') == 'false',
						handler: function(){
							var cardId = recTeam.get('CmpCallCard_id') ||record_Team.get('CmpCallCard_id'),
								cardRec = cardId?callsView.getStore().getById(cardId):null;
							
							if(cardRec)cntr.showCmpCallCardFromGrid(cardRec);
							subMenu.close();
						},
						disabled: !recTeam.get('CmpCallCard_id') && !record_Team.get('CmpCallCard_id')
					},
					{
						text: 'Укладка',
						hidden: (recTeam.get('WorkAccess') == 'false') || cntr.isNmpArm,
						handler: function(i){
							cntr.showEmergencyTeamDrugsPack(recTeam);					
							subMenu.close();						
						}					
					},
					/*{
						text: 'На вызов',
						handler: function(i){		
							cntr.showWindowCallsToTeam();
							subMenu.close();
						},
						menu: {
							xtype: 'menu',
							itemId: 'CmpCallsToEmergencyDynamicSubMenu',
							closeAction: 'destroy',
						}
					},*/
					getToDepartureMenuItem(),
					{
						text: 'Создать для спецбригады',
						disabled: true,
						hidden: (recTeam.get('WorkAccess') == 'false') || cntr.isNmpArm,
						itemId: 'createCallForSpecBrig',
						handler: function(i){
							i.cancelDeferHide();
							i.doExpandMenu();
						},
						menu: {
							xtype: 'menu',
							itemId: 'createCallForSpecBrigSubMenu',
							closeAction: 'destroy',							
							listeners:{
								click: function(m,i)
								{
									var card_id = i.value,
										rec = storeCalls.findRecord('CmpCallCard_id', card_id);
									
									//cntr.createCallForSpecBrigFunction(rec.raw);
									cntr.showWorkPlaceSMPDispatcherCallWindow(false,'forSpecialEmergencyTeam','',callsView,rec.raw);

									/*getWnd('swWorkPlaceSMPDispatcherCallWindow').show({
										showByDP: true,
										onSaveByDp: function(card_id){
											getWnd('swWorkPlaceSMPDispatcherCallWindow').hide();

											cntr.reloadStores(function(){
												//выделить запись
												var addedRec = storeCalls.findRecord('CmpCallCard_id', card_id);
												if(addedRec){
													callsView.getSelectionModel().select(addedRec);
												}
											});
										},
										params: params
									});*/
								}		
							}			
						}
						//disabled: (!recTeam.get('CmpCallCard_id') && !recTeam.get('GeoserviceTransport_id'))
					},
					{
						text: 'Показать на карте',
						handler: function(){
							if (sw.lostConnection) {
								lostConnectionAlert();
								return false;
							}

							cntr.setCenterEmergencyTeamOnMapContextMenu(recTeam);
							subMenu.close();
						},
						disabled: (!recTeam.get('CmpCallCard_id') && !recTeam.get('GeoserviceTransport_id'))
					},
					{
						text: 'Изменить состав наряда',
						hidden: !getRegionNick().inlist(['kareliya']),
						disabled: !recTeam.get('EmergencyTeamStatus_Code').inlist(['13', '21', '47', '5']),
						handler: function () {
							if (sw.lostConnection) {
								lostConnectionAlert();
								return false;
							}

							cntr.changeDuty(recTeam.data.EmergencyTeam_id);
						}
					}
				]
			}),
			waitingForAcceptStatusMenu = Ext.create('Ext.menu.Menu', {
				itemId: 'WaitingForAcceptStatusMenu',
				closeAction: 'destroy',
				listeners: {
					click: function(m,i)
					{
						var CmpCallCard_id = i.value;

						callsView.getSelectionModel().select(callsView.store.findRecord('CmpCallCard_id', CmpCallCard_id));

						cntr.setEmergencyTeamToCall(callsView, teamsView);
					}
				}
			}),
			cmpCallsToEmergencyDynamicSubMenu = subMenu.down('menu[itemId=CmpCallsToEmergencyDynamicSubMenu]'),
			createCallForSpecBrigSubMenu = subMenu.down('menu[itemId=createCallForSpecBrigSubMenu]'),
			createCallForSpecBrigSubMenuItems = [],
			createCallForSpecBrig = subMenu.down('menuitem[itemId=createCallForSpecBrig]'),
			subMenuEmergencyStatuses = subMenu.down('menu[itemId=EmergencyStatusDynamicSubMenu]');

		storeCalls.each(function(rec){
			if (rec.get('CmpCallCardStatusType_id') == 1) {
				var item = {
					datePrm : Ext.Date.format( new Date( rec.get('CmpCallCard_prmDate') ), 'd-m-Y H:i:s' ),
					numV : '№:' + rec.get('CmpCallCard_Numv'),
					urgency : rec.get('CmpCallCard_CalculatedUrgency') ? ('СР:'+rec.get('CmpCallCard_CalculatedUrgency') ):'',
					//pacientFio : rec.get('Person_FIO'),
					//pacientAge : rec.get('Person_Age'),
					//typeCall : rec.get('CmpCallType_Name'),
					reason : rec.get('CmpReason_Name'),
					address : rec.get('Adress_Name')
				},
				txt = '';
				
				for(var i in item){
					txt += ' '+item[i];
				};

				//Содержит подменю со списком вызовов в статусе «Передано» 
				//(переданные вызовы, на которые еще не назначена бригада) и Типом вызова «Первичный» или «Повторный». 
				if(
					(!cntr.isNmpArm && rec.get('CmpCallType_Code').inlist([1,2]) && !rec.get('EmergencyTeam_id')) ||
					//я так понял, что для нмп арма у нас другие условия, решил расширить условие
					(cntr.isNmpArm && rec.get('CmpCallType_Code').inlist([1,19]) && !rec.get('EmergencyTeam_id') && rec.get('CmpCallCard_IsExtra').inlist([2,3]))
				)
				{
					var callItem = {
						text: txt,
						value: rec.get('CmpCallCard_id')
					}
					if(waitingForAcceptStatusMenu)
						waitingForAcceptStatusMenu.add(callItem);

					if(cmpCallsToEmergencyDynamicSubMenu)
						cmpCallsToEmergencyDynamicSubMenu.add(callItem);
				};
				
			};
			//Содержит подменю со списком вызовов в статусе «Принято» 
			if (rec.get('CmpCallCardStatusType_id') == 2) {
				if(createCallForSpecBrigSubMenu && rec.get('EmergencyTeam_id') == record_Team.get('EmergencyTeam_id')){
					
					var item = {
						datePrm : Ext.Date.format( new Date( rec.get('CmpCallCard_prmDate') ), 'd-m-Y H:i:s' ),
						numV : '№:' + rec.get('CmpCallCard_Numv'),
						urgency : rec.get('CmpCallCard_Urgency') ? ('СР:'+rec.get('CmpCallCard_Urgency') ):'',
						//pacientFio : rec.get('Person_FIO'),
						//pacientAge : rec.get('Person_Age'),
						//typeCall : rec.get('CmpCallType_Name'),
						reason : rec.get('CmpReason_Name'),
						address : rec.get('Adress_Name')
					},
					txt = '';
					
					for(var i in item){
						txt += ' '+item[i];
					};
					createCallForSpecBrig.setDisabled(false);
					createCallForSpecBrigSubMenuItems.push({
						text: txt,
						value: rec.get('CmpCallCard_id'),
						date: rec.get('CmpCallCard_prmDate')	
					});
					/*createCallForSpecBrigSubMenu.add({
						text: txt,
						value: rec.get('CmpCallCard_id')					
					});
					*/
				}
			}
		});
		
		/* сортировка по дате элементов меню создать для спецбригады*/
		if(createCallForSpecBrigSubMenuItems){
			function compare(a,b) {
			if(new Date(a.date) < new Date(b.date))
				return -1;
			if(new Date(a.date) > new Date(b.date))
				return 1;
				return 0;
			}

			var sortedArray = createCallForSpecBrigSubMenuItems.sort(compare);
			createCallForSpecBrigSubMenu.add(sortedArray);
		}
		/*
		switch(getRegionNick()){
			case 'perm' : {
				break;
			}
			case 'ufa' : {
				break;
			}
			default: {
			}
		}
*/
		if(getRegionNick().inlist(['perm'])) {
			storeTeamStatus.reload({
				params: {
					EmergencyTeamStatus_pid: record_Team.get('EmergencyTeamStatus_id')
				},
				callback: function (records) {

					if (!records)return;

					records.forEach(function (rec) {
						if (rec) {
							if(cntr.isNmpArm && rec.get('EmergencyTeamStatus_Code').inlist(['3', '7', '8', '9', '10', '20', '21', '36', '49', '50'])){

							}
							else{
								subMenuEmergencyStatuses.insert(
									0,
									{
										text: rec.get('EmergencyTeamStatus_Name'),
										code: rec.get('EmergencyTeamStatus_Code'),
										value: rec.get('EmergencyTeamStatus_id')
									}
								);
							}
						}
					});

				}
			});
		}else{
			storeTeamStatus.each(function(rec){

				var menu = null;

				switch (rec.get("EmergencyTeamStatus_Code")){

					case 1:{
						//Выехал на вызов
						break;
					}
					case 2:{
						//Приезд на вызов
						break;
					}
					case 3:{
						//Начало госпитализации
						break;
					}
					case 4:{
						//Конец обслуживания
						break;
					}
					case 17:{
						//Прибытие в МО
						break;
					}
					case 36:{
						//Ожидание принятия
						menu =  waitingForAcceptStatusMenu;
						break;
					}
					case 48:{
						//Принял вызов
						break;
					}

				}
				if(getRegionNick().inlist(['ufa']) && cntr.isNmpArm && rec.get('EmergencyTeamStatus_Code').inlist(['3', '7', '8', '9', '10', '11', '17', '20', '21', '37', '38', '40', '43', '44', '45', '49', '50'])){

				}else{
					if (rec.get('EmergencyTeamStatus_id') != 59) {
						subMenuEmergencyStatuses.add({
							text: rec.get('EmergencyTeamStatus_Name'),
							code: rec.get('EmergencyTeamStatus_Code'),
							value: rec.get('EmergencyTeamStatus_id'),
							menu: menu
						})
					};
				}
			});
		};

		subMenuEmergencyStatuses.add({
			text: 'История статусов за текущую смену',
			hideOnClick: false,
			handler: function(i){
				i.cancelDeferHide();
				i.doExpandMenu();
			},
			menu: {
				xtype: 'menu',
				itemId: 'EmergencyStatusHistoryDynamicSubMenu',
				showSeparator: false,
				listeners: {
					beforeshow: function(c){
						Ext.Ajax.request({
							url: '/?c=EmergencyTeam4E&m=loadEmergencyTeamStatusesHistory',
							params: {
								EmergencyTeam_id: recTeam.get('EmergencyTeam_id')
							},
							callback: function(opt, success, response){
								if (success){
									var obj = Ext.decode(response.responseText);
									
									for(var i in obj){
										var hist = obj[i],
											tdate = Ext.Date.parse(hist.EmergencyTeamStatusHistory_insDT, 'Y-m-d H:i:s'),
											fdate = Ext.Date.format(tdate, 'H:i:s'),
											t = fdate+' '+hist.EmergencyTeamStatus_Name;
										
										c.add({text: t});
									}
								}
							}
						});
					},
					beforehide:function(c){
						c.removeAll();
					}
				}
			}
		});
		
		subMenu.showAt(x,y);
	},
	
	showEmergencyTeamEditStuffInfoFromGrid: function(record){
		var win = Ext.create('sw.tools.subtools.swEmergencyTeamOperEnvAddEdit', {
			action: 'edit',
			EmergencyTeam_id: record.get('EmergencyTeam_id'),
			height: 200
		});
		
		win.on('render', function(){
			win.down('fieldset[refId=commonInfo]').hide();
		})
		
		win.show();
	},
	
	/*
	showWindowCallsToTeam: function(){
		
		this.selectCmpCallCardNotification = Ext.create('sw.lib.Toaster',{
			position: 't',
			paddingY: 200,
			modal: false,
			//cls: 'ux-notification-light',
			// slideInAnimation: 'bounceOut',
			closable: false,
			autoCloseDelay: 3000,
			html: 'Пожалуйста, выберите вызов для назначения'
		}).show();
		
		this.getCmpCallCardViewRef().getSelectionModel().deselectAll();
		
		this.toggleFocusClassOnView('CmpCallCard');

		
		return;
	},
	*/
	
	showCmpCallCardFromGrid: function(store_record){
		var arrayCards = Ext.ComponentQuery.query('swsmpmappanel callCardWindow'),
			storeTeams = Ext.data.StoreManager.lookup('common.DispatcherStationWP.store.EmergencyTeamStore'),
			cntr = this;

		if (arrayCards.length < 1){
			var callcard = Ext.create('sw.tools.swShortCmpCallCard',{
				view: 'view', 
				card_id: store_record.get('CmpCallCard_id'),
				listeners: {
					close: function(){
						cntr.getCmpCallCardViewRef().getStore().reload();
					}
				}
			});
			callcard.show();
		}else{
			arrayCards[0].fireEvent('show', {
				view: 'view',
				card_id: store_record.get('CmpCallCard_id')
			});
		}
	},
	
	showEmergencyTeamInfoFromGrid: function(record){
		var win = Ext.create('sw.tools.subtools.swEmergencyTeamOperEnvAddEdit', {
			action: 'view',
			EmergencyTeam_id: record.get('EmergencyTeam_id')
		});
		win.show();
	},
	
	showEmergencyTeamDrugsPack: function(record){
		var win = Ext.create('sw.tools.subtools.swEmergencyTeamDrugsPack', {
			EmergencyTeam_id: record.get('EmergencyTeam_id')						
		});
		win.show();
	},
	
	sortEmergencyTeamStore: function(callback){
		
		var EmergencyTeamStore = this.getStore('common.DispatcherStationWP.store.EmergencyTeamStore'),
			selectedCmpCallCardRecord = this.getCmpCallCardViewRef().getSelectionModel().getSelection()[0];
		
		if (!selectedCmpCallCardRecord) {
			return false;
		}
		
		var CmpCallCardData = selectedCmpCallCardRecord.getData();
		
		if ( ( CmpCallCardData['CmpCallCard_CalculatedUrgency'] || 0 ) <= 2){
			//Вызовы с высокой срочностью сортируются без учета логики предложения.
			//Просто выбираем самую быструю|свободную бригду
			EmergencyTeamStore.considerProposalLogic = false; 
			//Формируем новый тип сортировки 
			EmergencyTeamStore.sort(EmergencyTeamStore.getCurrentSorterConfig());
			callback();
		}
		else{
			Ext.Ajax.request({
				url: '/?c=CmpCallCard4E&m=getEmergencyTeamPriorityFromReason',
				params: {
					CmpReason_id: CmpCallCardData['CmpReason_id'],
					Person_Age: CmpCallCardData['Person_Age'],
					CmpCallPlaceType_id: CmpCallCardData['CmpCallPlaceType_id']
				},
				callback: function(opt, success, response){
					if (success){
						//получаем список профилей и приоритетов
						//и расставляем приоритеты
						var obj = Ext.decode(response.responseText);
						EmergencyTeamStore.each(function(rec){
							rec.set('EmergencyTeamProposalLogicPriority', null);
							var spec = rec.get('EmergencyTeamSpec_id')
							for( var key in obj ){
								if (obj.hasOwnProperty(key)) {
									if (spec==obj[key].EmergencyTeamSpec_id){
										rec.set('EmergencyTeamProposalLogicPriority', obj[key].ProfilePriority);
									}
								}
							}
						})
						//Вызовы со срочностью 3 и больше сортируются с учетом логики предложения.
						EmergencyTeamStore.considerProposalLogic = true; 
						//Формируем новый тип сортировки 
						EmergencyTeamStore.sort(EmergencyTeamStore.getCurrentSorterConfig());
						
						callback();
					}
				}
			})	
		}		
	},

	setLpuBuildingsOnMap: function() {
		var me = this;


		Ext.Ajax.request({
			url: '?c=CmpCallCard4E&m=getControlLpuBuildingsInfo',
			callback: function (options, success, response) {
				if(success) {
					var res = Ext.JSON.decode(response.responseText),
						map = me.getMapPanelRef();

					for(var i in res) {
						map.setLpuBuildingMarker(res[i]);
					}
					return true;
				}
			}
		})
	},

	sortCmpCalls: function(){
		var cntr = this,
			storeCalls = Ext.data.StoreManager.lookup('common.DispatcherStationWP.store.CmpCallsStore'),
			sorters = [];

		if(cntr.modeView && cntr.modeView.inlist(['table', 'closed', 'cancel']) ){
			sorters.push({
				property : 'LpuBuilding_id',
				direction: 'ASC'
			})
		}

		switch(cntr.sortCalls){
			case 'nmp' : {
				sorters.push(
					{
						direction: 'DESC',
						property: 'CmpPPDResult_id'
					},
					{
						direction: 'ASC',
						property: 'CmpGroup_id'
					},
					{
						direction: 'ASC',
						property: 'CmpCallCard_IsExtra'
					},
					{
						direction: 'ASC',
						property: 'CmpCallType_Code'
					},
					{
						direction: 'ASC',
						property: 'CmpCallCard_prmDate'
					}
				);
				break;
			}
			case 'time' : {
				sorters.push(
					{
						property : 'CmpGroup_id',
						direction: 'ASC'
					},
					{
						property : 'CmpCallCard_prmDate',
						direction: 'ASC'
					},
					{
						property : 'TransmittedOrAccepted',
						direction: 'ASC'
					}
				);
				break;
			}
			case 'urgency' : {
				sorters.push(
					{
						property : 'CmpGroup_id',
						direction: 'ASC'
					},
					{
						property : 'CmpCallCardStatusType_id',
						direction: 'DESC'
					},
					{
						property : 'CmpCallCard_Urgency',
						direction: 'ASC'
					},
					{
						property : 'TransmittedOrAccepted',
						direction: 'ASC'
					}
				);
				break;
			}
		}

		storeCalls.sort(sorters);
	},
	
	reloadStores: function(callback){
		var cntr = this,
			reload = true,
			callStore = cntr.getStore('common.DispatcherStationWP.store.CmpCallsStore'),
			win = this.getEmergecyWindowRef();

		this.countOfLoadings++;
		var loadedStores = 0,
			allstoresLoad = function(callsstore){
				loadedStores++;
				if(loadedStores == 2 && callback){
					callback();
				};

				if(callsstore){
					cntr.setTitleServedTap();
				}

			}.bind(cntr);

		if(cntr.modeView == 'table') {
			var shortViewPanelWrapper = win.down('panel[refId=shortViewPanelWrapper]'),
			datePickerRange = shortViewPanelWrapper.down('datePickerRange'),
			now = new Date();
			if(datePickerRange.dateTo.getDate() != now.getDate() && now.getHours() == 0 && now.getMinutes() < 2 ){
				datePickerRange.dateTo = now;
				datePickerRange.maxValue = now;
				datePickerRange.shadowSetValue(false,now);
				reload = false;
				callStore.reload({
					params: {
						mode: cntr.modeView,
						begDate: Ext.Date.format(datePickerRange.dateFrom, 'd.m.Y'),
						endDate: Ext.Date.format(now, 'd.m.Y')
					},
					callback: function(){
						allstoresLoad(callStore);
					}
				});
			}
		}

		if(reload)
			callStore.reload({ callback: function(){
				allstoresLoad(callStore);
				if (!Ext.ComponentQuery.query('[name=CmpCallCard_id]')[0].getValue()) {
					Ext.ComponentQuery.query('[name=CmpCallPlaceType_id]')[0].setValue(0);
				}
			}});

		this.getStore('common.DispatcherStationWP.store.EmergencyTeamStore').reload({ callback: function(){allstoresLoad()} });

		//только для оперотдела и при активной настройке "Сообщать диспетчерам оперативного отдела о подстанциях, не взятых под управление"
		if(getGlobalOptions().SmpUnitType_Code == 4 && cntr.operDepartamentOptions && cntr.operDepartamentOptions.SmpUnitParam_IsDispNoControl == 'true'){
			cntr.checkLpuBuildingWithoutSmpUnitHistory()
		}
	},
	
	//@todo доделать
	getCallProfile: function(reason,age,callplace_value){
		
		if( !reason || !age || !callplace_value ) return false;
		
		Ext.Ajax.request({
			url:'/?c=CmpCallCard4E&m=getCallUrgencyAndProfile',
			autoAbort : true,
			callback: function(opt, success, response){
				if ( success )
				{
					var response_obj = Ext.JSON.decode(response.responseText);

					if (response_obj.Error_Msg)
					{
						Ext.Msg.alert('Ошибка', response_obj.Error_Msg);
					}
					else
					{
						/*
						flagOrArrayCodesIsNMP = (
							("ufa" == getGlobalOptions().region.nick || getGlobalOptions().region.nick == 'krym') 
							&& "nmp" == type_service_reason
							&& !(f.isDisabled() || f.isDisabled() || f.isDisabled())
							&&	(person_id != '0' || getGlobalOptions().region.nick == 'ufa')
						);
						this.setEnabledCombo(flagOrArrayCodesIsNMP,type_service_reason);
						urgency_label.setText('СР: '+(response_obj.CmpUrgencyAndProfileStandart_Urgency||'?'));
						profile_label.setText('ПР: '+ (response_obj.EmergencyTeamSpec_Code||'?'));
						*/
					}
				}

			}.bind(this),
			params:{
				CmpReason_id:reason,
				Person_Age:age,
				CmpCallPlaceType_id:callplace_value
			}
		});
	},
	/*
	nodeReloadStores: function(){
		var	win = this.getEmergecyWindowRef(),
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
		});
	},
	*/
	// Перемещается к выбранной бригаде и отображает ее по центру карты
	setCenterEmergencyTeamOnMap: function(record, setEmergencyTeamToCall, durationText, callsView, teamsView, callback){
		//var EmergencyTeamDuty_isNotFact = record.get('EmergencyTeamDuty_isNotFact');
		//if(EmergencyTeamDuty_isNotFact == 1) return false; //Бригады, плановая смена которых закончилась, но не закончилась фактическая НЕдоступны для назначения на вызов
		// ID 59 - ожидание принятия
		var cntr = this,
			storeCalls = Ext.data.StoreManager.lookup('common.DispatcherStationWP.store.CmpCallsStore'),
			recCardInSelection = callsView.getSelectionModel().getSelection()[0],
			//оказывается, что getSelection хранит старые данные
			callRec = recCardInSelection ? callsView.getStore().findRecord('CmpCallCard_id', recCardInSelection.get('CmpCallCard_id')) : null;

		if ( record.get('CmpCallCard_id') > 0 && !cntr.isNmpArm && !getRegionNick().inlist([ 'kareliya' , 'ufa']) ) {
			/*
			setTimeout(function(){
				Ext.Msg.alert('Ошибка','Бригада уже назначена на вызов');
			},1000);
			*/
			if(callback && typeof callback == 'function'){callback(false);}
			return false;
		}

		//только свободные бригады и свободные вызовы
		//if ( (!callRec || typeof callRec == 'undefined') || (!record.get('EmergencyTeamStatus_Code').inlist(['13','47']) && callRec.get('EmergencyTeam_id')) ) {
		if ( !callRec || typeof callRec == 'undefined' || callRec.get('EmergencyTeam_id') ) {
			return false;
		}

		if (getGlobalOptions().SmpUnitType_Code != 2 && setEmergencyTeamToCall){
			Ext.MessageBox.confirm('Назначение бригады на вызов', (durationText&&('Время доезда: '+durationText)||'') + ' Назначить выбранную бригаду на вызов?',function(btn){
				if( btn === 'yes' ){
					if(callback && typeof callback == 'function'){callback(true);}
					this.setEmergencyTeamToCall(callsView, teamsView);
				} else {
					if(callback && typeof callback == 'function'){callback(false);}
					callsView.getSelectionModel().deselectAll();
					teamsView.getSelectionModel().deselectAll();
				}
			},this);
		}

		var map_panel = this.getMapPanelRef();

		if(record.get('GeoserviceTransport_id')) { map_panel.setRouteFromAmbulanceToAccident( record.get('GeoserviceTransport_id') ); }


	},
	setCenterEmergencyTeamOnMapContextMenu: function(record){
		if(record.get('GeoserviceTransport_id'))
		{
			var buttonEl = Ext.ComponentQuery.query('swDispatcherStationWorkPlace button[refId=vr-splitter]')[0].el,
				mapWin = this.getMapPanelRef(),
				teamsWin = this.getEmergencyTeamViewRef(),
				callsWin = this.getCmpCallCardViewRef();

			buttonEl.addCls('bottom-splitter');
			if (buttonEl.hasCls('bottom-splitter')){
				Ext.ComponentQuery.query('swDispatcherStationWorkPlace button[refId=vr-splitter]')[0].setIconCls('bottom-splitter');
				mapWin.setHeight(300);
				callsWin.addCls('mini-view');
			}
			if (mapWin.getCurrentMapType() == 'google'||'wialon')
			{
				if(typeof google != 'undefined')google.maps.event.trigger(mapWin.getPanelByType(mapWin.getCurrentMapType()).map,'resize');
			}
			if (mapWin.getCurrentMapType() == 'here')
			{
				var hereMap = mapWin.getPanelByType(mapWin.getCurrentMapType());
				hereMap.map?hereMap.map.getViewPort().resize():false;
			}
			if (mapWin.getCurrentMapType() == 'yandex'){
				if(typeof ymaps != 'undefined')
					mapWin.getPanelByType(mapWin.getCurrentMapType()).map.container.fitToViewport();
			}
			mapWin.up('container').doLayout();

			mapWin.setMapViewToCenter( record.get('GeoserviceTransport_id'));
		}

	},
	//
	setCmpCallCardTransToETType: function(data) {
		if (!data['CmpCallCard_id'] || !data['CmpCallCard_Kakp']) {
			return false;
		}
		Ext.Ajax.request({
			params: data,
			url:'/?c=CmpCallCard4E&m=setCmpCallCardTransType',
			callback: function(opt, success, response) {
				if (success){
					var response_obj = Ext.JSON.decode(response.responseText);
					if (response_obj.success) {
						
					} else {
						Ext.Msg.alert('Ошибка',(response_obj.Error_Msg != '')?response_obj.Error_Msg:'При сохранении типа передачи талона вызова бригаде произошла ошибка');
					}
				} else {
					Ext.Msg.alert('Ошибка','При сохранении типа передачи талона вызова бригаде произошла ошибка');
				}
			}
		})
	},
	setEmergencyTeamToCall: function(callsView, teamsView){
		var	cntr = this,
			win = this.getEmergecyWindowRef(),
			//teamsView = this.getEmergencyTeamViewRef(),
			//callsView = this.getCmpCallCardViewRef(),
			selectedTeamRec = teamsView.getSelectionModel().getSelection()[0],
			selectedCallRec = callsView.getSelectionModel().getSelection()[0],
			selectedTeamId = selectedTeamRec.get('EmergencyTeam_id'),
			selectedCallId = selectedCallRec.get('CmpCallCard_id'),
			selectedTeamNum = selectedTeamRec.get('EmergencyTeam_Num'),			
			selectedCallNum = selectedCallRec.get('CmpCallCard_Numv'),
			storeCalls = Ext.data.StoreManager.lookup('common.DispatcherStationWP.store.CmpCallsStore'),
			storeTeams = Ext.data.StoreManager.lookup('common.DispatcherStationWP.store.EmergencyTeamStore');

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
							if(cntr.isNmpArm){
								Ext.MessageBox.confirm('Сообщение',
									'Бригада №' + selectedTeamNum + ' назначена на вызов №' + selectedCallNum + '.' + '</br>Распечатать карту вызова НМП', function (btn) {
										if (btn === 'yes') {
											printBirt({
												'Report_FileName': 'CmpCallCardPrintedForm.rptdesign',
												'Report_Params': '&paramCmpCallCard=' + selectedCallId,
												'Report_Format': 'pdf'
											});
										}
									}.bind(this));
							}else{
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
							}


							storeTeams.reload();
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

					storeCalls.reload();
					//@todo - дубль? : callsView.getStore().reload();
					
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
					var err_str = 'Во время назначения бригады произошла непредвиденная ошибка. Перезагрузите страницу и попробуйте выполнить действие заново. Если ошибка повторится, обратитесь к администратору.';
					if(obj.Error_Msg)
						err_str = obj.Error_Msg;
					Ext.Msg.alert('Ошибка',err_str);
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

		if(params.EmergencyTeamStatus_Code.inlist(['9', '40']) && params.CmpCallCard_id > 0) {

			callsView.getStore().findBy(function(record,id){
				if(id == params.CmpCallCard_id){
					numv = record.get('CmpCallCard_Numv');
					personFIO = record.get('Person_FIO');
				}
			});

			var breakType = params.EmergencyTeamStatus_Code == '9' ? '«Обед»' : '«Ужин»';

			Ext.MessageBox.show({title:'Сообщение',msg:'«Бригада назначена на вызов № ' + numv +', ' + personFIO +'. Смена статуса бригады на '+
				breakType +
			' невозможна»',buttons:Ext.MessageBox.OK});
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

	saveHospForm: function(params,callback) {
		var loadMask = new Ext.LoadMask( Ext.getBody(), { msg:"Сохранение данных..." });

		loadMask.show();
		Ext.Ajax.request({
			url: '/?c=CmpCallCard4E&m=saveScales',
			params: params,
			success: function (response, options) {
				loadMask.hide();
				var response_obj = Ext.JSON.decode(response.responseText);
				if(!Ext.isEmpty(response_obj.Error_Msg)) {
					Ext.Msg.alert('Ошибка', response_obj.Error_Msg);
					callback();
				} else {
					callback();
				}
			},
			failure: function(response, options) {
				loadMask.hide();
				if(!Ext.isEmpty(response_obj.Error_Msg)) {
					Ext.Msg.alert('Ошибка', response_obj.Error_Msg);
				}
			}
		})
	},

	resetEmergencyTeamFromCall: function(callsView, teamsView){
		var	selectedCallRec = callsView.getSelectionModel().getSelection()[0],
			selectedCallId = selectedCallRec.get('CmpCallCard_id'),
			selectedCallRid = selectedCallRec.get('CmpCallCard_rid'),
			EmergencyTeam_id = callsView.getStore().getById(selectedCallId).get('EmergencyTeam_id'),
			loadMask = new Ext.LoadMask(Ext.getBody(),{msg:"Сохранение данных..."}),
			win = this.getEmergecyWindowRef();
		loadMask.show();
		if(!EmergencyTeam_id){
			loadMask.hide();
			return false;
		}

		Ext.Ajax.request({
			url: '/?c=EmergencyTeam4E&m=cancelEmergencyTeamFromCall',
			callback: function(opt, success, response) {
				var obj = Ext.decode(response.responseText);
				if ( obj.success ) {
					Ext.Msg.alert('Сообщение', 'Бригада отклонена');
					callsView.getStore().reload();
					teamsView.getStore().reload();
					callsView.getSelectionModel().deselectAll();
					teamsView.getSelectionModel().deselectAll();
					if (win.socket) {
						win.socket.emit('cancelCmpCard', {
							CmpCallCard_id: selectedCallId,
							CmpCallCard_rid: selectedCallRid,
							EmergencyTeam_id: EmergencyTeam_id
						}, function(data){
							log('NODE emit cancelCmpCard : apk='+data);
						});
					}

				} else {
					Ext.Msg.alert('Ошибка','Во время отклонения бригады произошла непредвиденная ошибка. Перезагрузите страницу и попробуйте выполнить действие заново. Если ошибка повторится, обратитесь к администратору.');
				}
			}.bind(this),
			success: function(response, opts){
				loadMask.hide();
			},
			params: {
				'CmpCallCard_id': selectedCallId,				
				'CmpCallCard_rid': selectedCallRid,
				'EmergencyTeam_id': EmergencyTeam_id,
				'ARMType': (sw.Promed.MedStaffFactByUser.last != null) ? sw.Promed.MedStaffFactByUser.last.ARMType : sw.Promed.MedStaffFactByUser.current.ARMType
			}
		});
		
	},
	
	//Метод фильтрации списка нарядов и их маркеров по выбранным группам транспортных средств
	filterEmergencyTeamListWithMarkersBySelectedGroupList: function() {
		var emergencyTeamStore = this.getStore('common.DispatcherStationWP.store.EmergencyTeamStore'),
			geoserviceTransportStore = this.getGeoserviceTransportStore(),
			mapPanel = this.getMapPanelRef(),
			visibleGroupList = [],
			visibleGeoserviceTransportList = [],
			hiddenGeoserviceTransportList = [];
		
		if( geoserviceTransportStore.data.items.length == 0 ) {
			// стор не всегда бывает загружен
			geoserviceTransportStore.load();
		}
		//console.log('geoserviceTransportStore', geoserviceTransportStore.count());
		//if(geoserviceTransportStore.count() == 0) return false;

		//Получаем список групп, которые необходимо отображать
		this.getStore('stores.smp.GeoserviceTransportGroupStore').each(function(record){
			if(record.get('visible') === true){
				visibleGroupList.push(  record.get('id')+'' );
			}
		});
		
		//Получаем список отображаемого транспорта по списку отображаемыхх групп
		//оставлю пока лежать тут
		/*geoserviceTransportStore.each(function(record){
			// Флаг нахождения транспорта в одной из отображаемых групп
			var hasVisibleGroup = false,
				//Преобразовываем все к строкам
				transportGroupList = Ext.Array.map(record.get('groups'),function(item,idx,allItems){
					return item+'';
				});
			//Если транспорт состоит хотя бы в одной из отображаемых групп, будем считать его
			//отображаемым (на карте и в списке нарядов)	
			for (var i = 0; i < visibleGroupList.length && !hasVisibleGroup; i++) {
				hasVisibleGroup = Ext.Array.contains(transportGroupList, visibleGroupList[i]+'' );
			};
			
			visibleGeoserviceTransportList.push({
				id: record.get('GeoserviceTransport_id'),
				visible: hasVisibleGroup
			});
			
			if (!hasVisibleGroup) {
				hiddenGeoserviceTransportList.push(record.get('GeoserviceTransport_id'));
			}
		});*/
		
		emergencyTeamStore.each(function(record){
			
			//не тужимся: нет id - нет разговора
			if(!record.get('GeoserviceTransport_id') || !record.get('groups') ) return;
			if(visibleGroupList.length == 0) return;
			
			var groupsCar = [];
			if(typeof(record.get('groups')) == 'string'){
				groupsCar = record.get('groups').split(',');
			}
			else{groupsCar = record.get('groups');}
			
			// Флаг нахождения транспорта в одной из отображаемых групп
			var hasVisibleGroup = false,
				//Преобразовываем все к строкам
				transportGroupList = Ext.Array.map(groupsCar,function(item,idx,allItems){
					return item+'';
				});

			//Если транспорт состоит хотя бы в одной из отображаемых групп, будем считать его
			//отображаемым (на карте и в списке нарядов)	
			for (var i = 0; i < visibleGroupList.length && !hasVisibleGroup; i++) {
				hasVisibleGroup = Ext.Array.contains(transportGroupList, visibleGroupList[i]+'' );
			};
			
			visibleGeoserviceTransportList.push({
				id: record.get('GeoserviceTransport_id'),
				visible: hasVisibleGroup
			});
			
			if (!hasVisibleGroup) {
				hiddenGeoserviceTransportList.push(record.get('GeoserviceTransport_id'));
			}
		});
		
		// Два массива hiddenGeoserviceTransportList и visibleGeoserviceTransportList используются потому, что 
		// в случае с отображением маркеров необходимо указать параметр отображения для каждого конкретного маркера транспортного средства
		// но не удобно и непрактично использовать visibleGeoserviceTransportList с объектами вместо идентификаторов для фильтрации стора бригад
		
		// Скрываем/отображаем маркеры траспортных средств на карте
		for (var i = 0; i < visibleGeoserviceTransportList.length; i++) {
			mapPanel.setVisibleAmbulanceMarker( visibleGeoserviceTransportList[i].id, visibleGeoserviceTransportList[i].visible );
		};	

		if (getRegionNick().inlist(['ufa', 'krym'])) {
			emergencyTeamStore.filterBy(function(record){
				return !Ext.Array.contains(hiddenGeoserviceTransportList, record.get('GeoserviceTransport_id') )
			})
		} else {
			if(count(geoserviceTransportStore.data.items) > 0) {				
				emergencyTeamStore.filterBy(function(record){
					return !Ext.Array.contains(hiddenGeoserviceTransportList, record.get('GeoserviceTransport_id') )
				})
			}
		}

		
	},
	/**
	* @public Метод предложения бригады
	* @param CmpCallCard_record Ext.data.Model - record из стора талонов вызова
	*/
	offerEmergencyTeamToCmpCallCard: function( callsView, teamsView ) {

		var cntr = this,
			mapPanel = this.getMapPanelRef(),
			cmpCallCard_record = callsView.getSelectionModel().getSelection()[0],
			emergencyTeamRecord = teamsView.getSelectionModel().getSelection()[0],
			emergencyTeamStore = this.getStore('common.DispatcherStationWP.store.EmergencyTeamStore'),
			emergencyTeamData = Ext.Array.pluck( emergencyTeamStore.getRange(), 'data' ),
			geoserviceTransportIdList = Ext.Array.pluck( emergencyTeamData , 'GeoserviceTransport_id' ),
			loadMask = new Ext.LoadMask(Ext.getBody(),{msg:"Просчет времени доезда..."});

			//mapPanel.setCmpCallCardMarker( cmpCallCard_record , function(){

			if ( emergencyTeamRecord ) {
                teamsView.getSelectionModel().select(emergencyTeamRecord);
				if (!Ext.Array.contains([1, 2], cmpCallCard_record.get('CmpGroup_id')*1) ) {
					Ext.Msg.alert('Ошибка', 'Указанный вызов не может быть выбран для данной бригады, поскольку уже выполнен другой бригадой')
					callsView.getSelectionModel().deselectAll();
					teamsView.getSelectionModel().deselectAll();
					return;
				}

				if(emergencyTeamRecord.get('GeoserviceTransport_id'))
				{
					loadMask.show();
					try {
						loadMask.hide();
						//@todo yandex не работает getAllAmbulancesArrivalTimeToAccident
						mapPanel.getAllAmbulancesArrivalTimeToAccident( [emergencyTeamRecord.get('GeoserviceTransport_id')] , function( data ) {

							cntr.setCenterEmergencyTeamOnMap(emergencyTeamRecord, true, data[0]['durationText'], callsView, teamsView );
						});
					} catch (e) {
						log({offerEmergencyTeamToCmpCallCard_getAllAmbulancesArrivalTimeToAccident_e:e})
						loadMask.hide();
					}
				}
				else{
					loadMask.hide();
					cntr.setCenterEmergencyTeamOnMap(emergencyTeamRecord, true, null, callsView, teamsView );
				}
				
			}
				
			
			//if ( getGlobalOptions().region.number != 60 ) {
			//	return;
			//}
			
			mapPanel.getAllAmbulancesArrivalTimeToAccident( geoserviceTransportIdList, cmpCallCard_record.get('CmpCallCard_id'), function( data ) {
				if (! (data instanceof Array)) {
					//Ext.Msg.alert('Ошибка', 'Во время получения времени доезда произошла ошибка' );
					return;
				}

				var emergencyTeamRecord;
				try {
					for (var i = 0; i < data.length; i++) {
						emergencyTeamRecord = emergencyTeamStore.findRecord('GeoserviceTransport_id',data[i]['id']);
						emergencyTeamRecord.set('EmergencyTeamDistance', data[i]['distanceValue']);
						emergencyTeamRecord.set('EmergencyTeamDuration', data[i]['durationValue']);
						emergencyTeamRecord.set('EmergencyTeamDistanceText', data[i]['distanceText']);
						emergencyTeamRecord.set('EmergencyTeamDurationText', data[i]['durationText']);
					};
					emergencyTeamStore.sort();
				} catch (e) {
					//Ext.Msg.alert('Ошибка', 'Во время получения времени доезда произошла ошибка' );
					return;
				}
				
				//cntr.sortEmergencyTeamStore( function(){
				//	var firstRec = emergencyTeamStore.first();
				//	teamsView.getSelectionModel().select( firstRec );
				//	mapPanel.setRouteFromAmbulanceToAccident( firstRec.get('GeoserviceTransport_id') );
				//}.bind(this));
			});
		//});

		if(cmpCallCard_record.get('UnAdress_lat') && cmpCallCard_record.get('UnAdress_lng')){
			mapPanel.setMapViewToCenterByCoords([cmpCallCard_record.get('UnAdress_lat'), cmpCallCard_record.get('UnAdress_lng')]);
		}

	},
	toggleFocusClassOnView: function(view) {
		
		if (!Ext.Array.contains(['CmpCallCard', 'EmergencyTeam'], view)) {
			return false;
		}
		
		var focusedView = (view == 'CmpCallCard') ? this.getCmpCallCardViewRef() : this.getEmergencyTeamViewRef(),
			unfocusedView = (view == 'CmpCallCard') ? this.getEmergencyTeamViewRef() : this.getCmpCallCardViewRef();
		
		if (!focusedView.getEl().hasCls('focused-panel')) {
			focusedView.getEl().toggleCls('focused-panel');
		}
		if (unfocusedView.getEl().hasCls('focused-panel')) {
			unfocusedView.getEl().toggleCls('focused-panel');
		}	
	},
	
	showCmpCloseCardFromExt2: function(stream, cmp_id, act){

		var cntr = this,
			path = '',
			winTitle = 'Поточный ввод 110у',
			action = ((act != undefined) && act.inlist(['add','edit'])) ? act :'add';
			
		if(stream){
			path = "/?c=promed&getwnd=swCmpCallCardNewCloseCardWindow&showTop=1";
			winTitle = 'Поточный ввод 110у';
		}
		else{
			path = "/?c=promed&getwnd=swCmpCallCardNewCloseCardWindow&act=" + action + "&showTop=1&cccid="+cmp_id;
			winTitle = (action == 'add') ? 'Карта вызова: Закрытие' : 'Карта вызова: Редактирование';
		}

		if(!Ext.ComponentQuery.query('[refId=CmpCloseCard]')[0] || !Ext.ComponentQuery.query('[refId=CmpCloseCard]')[0].isVisible()) {
			new Ext.Window({
				id: 'myFFFrame'+Ext.id(),
				refId: 'CmpCloseCard',
				title: winTitle,
				header: false,
				extend: 'sw.standartToolsWindow',
				toFrontOnShow: true,
				//width : '100%',
				//modal: true,
				style: {
					'z-index': 90000
				},
				//height: '90%',
				//layout : 'fit',
				layout: {
					type: 'fit',
					align: 'stretch'
				},
				maximized: true,
				constrain: true,
				renderTo: Ext.getCmp('inPanel').body,
				items : [{
					xtype : "component",
					id: 'component'+Ext.id(),
					autoEl : {
						tag : "iframe",
						src : path
					}
				}],
				listeners:{
					'close': function(){
						cntr.reloadStores();
					}
				}
			}).show();
		} else {
			Ext.Msg.alert('Ошибка', 'Карта вызова уже открыта')
		}



	},
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
					Ext.ComponentQuery.query('grid[refId=callsCardsShortGrid]')[0].getStore().reload();
				}
			}
		});
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
					callsView.getStore().reload();
				}
			});
			/*
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
								Lpu_id: cntr.isNmpArm ? recCard.get('Lpu_ppdid') : null,
								CmpCallCard_prmDT: new Date()
							},
							callback: function (opt, success, response) {
								if (!success) {
									return false;
								}
							}
						});

						callsView.getStore().reload();
					} else {
						Ext.Msg.alert('Не удалось вывести вызов из отложенных')
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
									value: recCall.get('CmpCallCard_defCom'),
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

								if(form.findField('CmpCallCard_storDate').getValue().getFullYear() > new Date().getFullYear()+1) {
									Ext.Msg.alert('Ошибка', 'Нельзя откладывать вызов более, чем на 1 год')

									return false;
								}

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
												callsView.getStore().reload();

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
	timeControlRefresh: function(cmp){
		setInterval(function(){
				var activeWin = Ext.WindowManager.getActive();

				if( !this.timeControlRefresh_flag ) return;

				if (cmp==activeWin) {
					// обновим инфомацию если нод включен
					this.reloadStores();
				}
			}.bind(this)
		, this.timeControlRefresh_ms);
	},
	resetTimeOnClear: function(cb,newVal){
		var cntr = this,
			win = cntr.getEmergecyWindowRef(),
			callDetailForm = win.down('form[refId=CallDetailPanel]').getForm();

		if(!Ext.isDate(newVal)){
			//очищаем поле с временем
			callDetailForm.findField(cb.getName()+'Time').reset();
		}
	},
	showCmpCallCard112: function(rec){

		if(!rec || !rec.get('CmpCallCard_id'))
			return;
		var callcard112 = Ext.create('sw.tools.swCmpCallCard112',{
			view: 'view',
			card_id: rec.get('CmpCallCard_id')
		});
		callcard112.show();

	},
	saveShortCard: function(params){
		var cntr = this,
			win = cntr.getEmergecyWindowRef(),
			callDetailForm = win.down('form[refId=CallDetailPanel]').getForm(),
			allFields = callDetailForm.getAllFields(),
			allValues = callDetailForm.getAllValues(),
			cityCombo = allFields.dCityCombo,
			shortViewPanelWrapper = win.down('panel[refId=shortViewPanelWrapper]'),
			saveShortCardBtn = shortViewPanelWrapper.down('button[refId=saveShortCardBtn]'),
			secondStreet = allFields.secondStreetCombo;

		params = Object.assign(cntr.editedCardData,params, allValues);

		params.CmpCallCardStatusType_id = (params.CmpCallCardStatusType_id != 20) ? params.CmpCallCardStatusType_id : 1;
		params.CmpCallCard_prmDT = Ext.Date.format(Ext.Date.parse(params.CmpCallCard_prmDate, 'Y-m-d H:i:s'), 'Y-m-d') + ' ' + Ext.Date.format(Ext.Date.parse(params.CmpCallCard_prmTime, 'Y-m-d H:i:s'), 'H:i:s');
		params.CmpCallCard_Tper = params.CmpCallCard_DateTper? Ext.Date.format(Ext.Date.parse(params.CmpCallCard_DateTper, 'Y-m-d H:i:s'), 'Y-m-d') + ' ' + Ext.Date.format(Ext.Date.parse(params.CmpCallCard_DateTperTime, 'Y-m-d H:i:s'), 'H:i:s'): null;
		params.CmpCallCard_Vyez = params.CmpCallCard_DateVyez? Ext.Date.format(Ext.Date.parse(params.CmpCallCard_DateVyez, 'Y-m-d H:i:s'), 'Y-m-d') + ' ' + Ext.Date.format(Ext.Date.parse(params.CmpCallCard_DateVyezTime, 'Y-m-d H:i:s'), 'H:i:s'): null;
		params.CmpCallCard_Przd = params.CmpCallCard_DatePrzd? Ext.Date.format(Ext.Date.parse(params.CmpCallCard_DatePrzd, 'Y-m-d H:i:s'), 'Y-m-d') + ' ' + Ext.Date.format(Ext.Date.parse(params.CmpCallCard_DatePrzdTime, 'Y-m-d H:i:s'), 'H:i:s'): null;
		params.CmpCallCard_Tgsp = params.CmpCallCard_DateTgsp? Ext.Date.format(Ext.Date.parse(params.CmpCallCard_DateTgsp, 'Y-m-d H:i:s'), 'Y-m-d') + ' ' + Ext.Date.format(Ext.Date.parse(params.CmpCallCard_DateTgspTime, 'Y-m-d H:i:s'), 'H:i:s'): null;
		params.CmpCallCard_Tisp = params.CmpCallCard_DateTisp? Ext.Date.format(Ext.Date.parse(params.CmpCallCard_DateTisp, 'Y-m-d H:i:s'), 'Y-m-d') + ' ' + Ext.Date.format(Ext.Date.parse(params.CmpCallCard_TispTime, 'Y-m-d H:i:s'), 'H:i:s'): null;
		params.CmpCallCard_HospitalizedTime = params.CmpCallCard_HospitalizedTime? Ext.Date.format(Ext.Date.parse(params.CmpCallCard_HospitalizedTime, 'Y-m-d H:i:s'), 'Y-m-d') + ' ' + Ext.Date.format(Ext.Date.parse(params.CmpCallCard_HospitalizedTimeTime, 'Y-m-d H:i:s'), 'H:i:s'): null;
		params.Person_PolisNum = allValues.Polis_Num;
	//	params.withoutChangeStatus = cntr.withoutChangeStatus;
		params.CmpCallCard_UlicSecond = secondStreet.getSelectedRecord()? secondStreet.getSelectedRecord().get('KLStreet_id'): null;

		if (!allFields.CmpCallerType_id.getSelectedRecord()){
			params.CmpCallCard_Ktov = params.CmpCallerType_id;
			params.CmpCallerType_id = 0;
		}

		//params.Person_Age = callDetailForm.findField('Person_AgeInt').getValue();

		if (allValues.dCityCombo){
			var city = cityCombo.getSelectedRecord().data;

			if(city.KLAreaLevel_id==4){
				params.KLTown_id = city.Town_id;

				//если региона нет тогда нас пункт не относится к городу
				if(city.Region_id){
					params.KLSubRgn_id = city.Area_pid;
				} else{
					params.KLCity_id = city.Area_pid;
				}
			} else{
				params.KLCity_id = city.Town_id;
				//если город верхнего уровня, то район сохранять не надо
				if(city.KLAreaStat_id!=0)
				{params.KLSubRgn_id = city.Area_pid;}
			}

			params.KLAreaStat_idEdit = city.KLAreaStat_id;
			params.KLRgn_id = city.Region_id;
		}

		if(
			(params.CmpCallCard_IsExtra == 2) &&
			!(params.CmpCallType_Code.inlist([6,15,16,17])? true : false) &&
			(params.CmpCallCard_IsPassSSMP == false) &&
			!params.LpuBuilding_id &&
			!params.Lpu_ppdid
		){
			Ext.Msg.alert('Ошибка', 'Если вызов неотложный, то хотя бы одно из полей «МО передачи (НМП)» или «Подразделение СМП» должно быть заполнено');
			return false;
		}

		for(var p in params) {
			if (params[p] == ' ') params[p] = null;
		}

		var loadMask = new Ext.LoadMask(Ext.getBody(),{msg:"Сохранение информации о вызове..."});
		loadMask.show();
		saveShortCardBtn.disable();
		//cntr.editedCardData

		Ext.Ajax.request({
			// url: '/?c=CmpCallCard4E&m=saveCmpCallCardTimes',
			//url: '/?c=CmpCallCard4E&m=saveShortCmpCallCard',
			url: '/?c=CmpCallCard4E&m=saveCmpCallCard',
			params: params,
			callback: function(opt, success, response) {
				loadMask.hide();
				saveShortCardBtn.enable();
				if (!success) {
					return false;
				}

				var EmergencyTeam_id = callDetailForm.findField('EmergencyTeam_id').getValue(),
					callsView = win.down('grid[refId=callsCardsShortGrid]');

				callsView.getStore().reload();
				//условие для уфы из #107019
				if (win.socket && EmergencyTeam_id && !getRegionNick().inlist(['ufa'])) {
					if(!EmergencyTeam_id || EmergencyTeam_id=="NaN") {
						log('Attention! Missing parameter EmergencyTeam_id. In NodeJS not sent.');
						return;
					}
					win.socket.emit('changeCmpCallCard', params.CmpCallCard_id, 'saveCall', function(data){
						console.log('NODE emit saveCall : apk='+data);
					});
					// win.socket.on('changeCmpCallCard', function(data, type){
					// 	console.log('NODE ON changeCmpCallCard type='+type);
					// });
					/*
					 // если всё хорошо, то удалить из нода
					 params['EmergencyTeam_id'] = EmergencyTeam_id;
					 params['streetValue'] = streetName;
					 win.socket.emit('updatedInformationOnCall', params, function(data){
					 log('NODE emit UpdatedInformationOnCall');
					 });
					 win.socket.on('updatedInformationOnCall', function(data, status){
					 log('NODE ON. событие "updatedInformationOnCall" из NodeJS');
					 });
					 */
				}
			},
			failure: function() {
				loadMask.hide();
				sw.swMsg.alert('Ошибка', 'Ошибка при сохранении информации о вызове');
			}
		});
	},

	showDublicateWindow: function(cards,params){
		var cntr = this,
			win = cntr.getEmergecyWindowRef(),
			callDetailForm = win.down('form[refId=CallDetailPanel]').getForm(),
			cmpDublicateWindow = Ext.ComponentQuery.query('window[refId=SmpCallCardCheckDuplicateWindow]')[0];

		if( cmpDublicateWindow.isVisible() ) return;
		cmpDublicateWindow.show({doubleCmpCard: cards});

		if( !cmpDublicateWindow.hasListener('selectDuplicateCall') )
		{
			cmpDublicateWindow.on('selectDuplicateCall', function(success,pp,rec) {
				if( cmpDublicateWindow.isVisible() ){

					/*
					 Тип текущего вызова меняется на «Дублирующий»; CmpCallType_id code 14
					 */
					var DoubleRecord = callDetailForm.findField('CmpCallType_id').getStore().findRecord('CmpCallType_Code', 14) // дублирующий
					if(DoubleRecord)
						params.CmpCallType_id = DoubleRecord.get('CmpCallType_id');
					params.CallType = 'double';
					params.CmpCallCard_rid = pp.CmpCallCard_rid;

					cntr.saveShortCard(params);
					cmpDublicateWindow.close();
				}
			});
		}

		cmpDublicateWindow.callback = cntr.saveShortCard.bind(cntr,params);

	},

	//Расчет возраста на основе даты рождения
	//Устанавливает связку 4 полей: приема вызова, даты рождения, возраста b ед. возраста
	//editedField - редактируемое поле - от него зависит, в какую сторону будут заноситься изменения
	setPersonAgeFields: function(editedField) {
		var cntr = this,
			win = cntr.getEmergecyWindowRef(),
			shortViewPanelWrapper = win.down('panel[refId=shortViewPanelWrapper]'),
			base_form = shortViewPanelWrapper.down('form[refId=CallDetailPanel]').getForm(),
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
						calcBirthday = new Date(date.setDate(date.getDate() - inp));
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

    showYellowMsg: function(msg, delay, callback){
        var div = document.createElement('div');

        div.style.width='300px';
        div.style.height='70px';
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
            if(callback)callback();
        }, delay);
    },

	setEnabledCallCardFields: function(data){
		var cntr = this,
			win = cntr.getEmergecyWindowRef(),
			callsView = win.down('grid[refId=callsCardsShortGrid]'),
			recCardInSelection = callsView.getSelectionModel().getSelection()[0],
			recCard = recCardInSelection ? callsView.getStore().findRecord('CmpCallCard_id', recCardInSelection.get('CmpCallCard_id')) : null,
			shortViewPanelWrapper = win.down('panel[refId=shortViewPanelWrapper]'),
			callDetailForm = shortViewPanelWrapper.down('form[refId=CallDetailPanel]').getForm(),
			CmpCallCard_IsExtra = callDetailForm.findField('CmpCallCard_IsExtra').getValue(),
			secondStreetCombo = callDetailForm.findField('secondStreetCombo'),
			LpuBuilding_id = callDetailForm.findField('LpuBuilding_id').getValue(),
			Lpu_ppdid = callDetailForm.findField('Lpu_ppdid').getValue(),
			isPoli = callDetailForm.findField('CmpCallCard_IsPoli').getValue(),
			CmpCallCard_IsPassSSMP = callDetailForm.findField('CmpCallCard_IsPassSSMP').getValue(),
			CmpCallTypeField = callDetailForm.findField('CmpCallType_id'),
			CmpCallTypeRec = CmpCallTypeField.findRecordByValue(CmpCallTypeField.getValue()),
			callTypeWithoutLpu = (CmpCallTypeRec && CmpCallTypeRec.get(CmpCallTypeField.codeField).inlist([6,15,16,17])), //Консультативное, Консультативный, Справка, Абонент отключился
			eabledNMPFields = ['CmpCallCard_DateTper', 'CmpCallCard_DateTperTime', 'CmpCallCard_DateVyez', 'CmpCallCard_DateVyezTime', 'CmpCallCard_DatePrzd', 'CmpCallCard_DatePrzdTime',
				'CmpCallCard_DateTisp', 'CmpCallCard_TispTime', 'CmpCallCard_Comm'];

		if(!recCard)
			return false;

		var is112 = (recCard.get('CmpCallCardStatusType_id') == 20),
			isClosedAndDisabled = ( recCard.get('CmpCallCardStatusType_id') == 6) && (getRegionNick().inlist(['perm']) ),
			isDeny = (recCard.get('CmpCallCardStatusType_id') == 21 && (CmpCallTypeRec && CmpCallTypeRec.get(CmpCallTypeField.codeField).inlist([1,2])) ),
			callDetailFields = callDetailForm.getFields().items;

		callDetailFields.forEach(function(field){
			var fieldName = field.getName();

			if(cntr.isNmpArm){

				field.setReadOnly(!fieldName.inlist(eabledNMPFields));
				field.setDisabled(!fieldName.inlist(eabledNMPFields));
				return;
			}

			switch(fieldName){

				 //поля постоянно неактивные
				 case 'CmpCallCard_prmDate':
				 case 'CmpCallCard_prmTime':
				 case 'CmpCallCard_Ngod':
				 case 'CmpCallCard_Numv':
				 case 'CmpCallCard_Urgency':
				 case 'EmergencyTeamSpec_Code':
				 case 'EmergencyTeam_Num':
				 case 'EmergencyTeam_HeadDocName':
				 case 'DPMedPersonal_id':

					 field.setReadOnly(true);
					 field.setDisabled(true);

				 break;

				case 'CmpCallCard_Korp':
				case 'CmpCallCard_Kvar':
				case 'CmpCallCard_Podz':
				case 'CmpCallCard_Etaj':
				case 'CmpCallCard_Kodp':{

					var firstStreetval = data?data.CmpCallCard_UlicSecond:secondStreetCombo.getValue();
					var secondStreetval = data?data.CmpCallCard_UlicSecond:secondStreetCombo.getValue();

					field.setDisabled(!Ext.isEmpty(secondStreetval) || isClosedAndDisabled || !getRegionNick().inlist(['perm']));
					field.setReadOnly(!Ext.isEmpty(secondStreetval) || isClosedAndDisabled || !getRegionNick().inlist(['perm']));
					/*
					if( isClosedAndDisabled  || getRegionNick().inlist(['ufa']) ){
						field.setReadOnly(true);
						field.setDisabled(true);
					}
					else{
						if(
							request.CmpCallCard_UlicSecond
						){
							field.setDisabled(true);
							field.setReadOnly(true);
						}
						else{
							field.setDisabled(false);
							field.setReadOnly(false);
						}
					}
					*/
					break;
				}

				case 'LpuBuilding_id':

					var smpBuildingVisible = (is112 && ((CmpCallCard_IsExtra == 1) || ((CmpCallCard_IsExtra == 2) && !Lpu_ppdid && !isPoli ))) || isDeny;
					field.setReadOnly(!smpBuildingVisible);
					field.setDisabled(!smpBuildingVisible);

				
					var smpBuildingAllowBlank = (is112 && (CmpCallCard_IsExtra == 1) && !CmpCallCard_IsPassSSMP && !callTypeWithoutLpu);
					//field.allowBlank = (!smpBuildingAllowBlank);
					
					break;
				case 'CmpCallCard_IsPoli':
					
					var isPoliVisible = (is112 && (CmpCallCard_IsExtra == 2));
					field.setReadOnly(!isPoliVisible);
					field.setDisabled(!isPoliVisible);
					
					break;
				case 'Lpu_ppdid':
					
					var nmpBuildingVisible = (is112 && (CmpCallCard_IsExtra == 2)/* && !LpuBuilding_id*/ && !CmpCallCard_IsPassSSMP );
					field.setReadOnly(!nmpBuildingVisible);
					field.setDisabled(!nmpBuildingVisible);
					//field.allowBlank = !nmpBuildingVisible;
					
					break;
				case 'selectNmpCombo':
					
					var nmpServiceVisible = (is112 && CmpCallCard_IsExtra == 2 && !LpuBuilding_id && !CmpCallCard_IsPassSSMP);
					field.setReadOnly(!nmpServiceVisible);
					field.setDisabled(!nmpServiceVisible);
					
					break;
				case 'CmpCallCard_IsPassSSMP':
					
					var isPassSSMPVisible = (is112 && getGlobalOptions().smp_allow_transfer_of_calls_to_another_MO == 1);
					field.setVisible(isPassSSMPVisible);
					field.setReadOnly(!isPassSSMPVisible);
					field.setDisabled(!isPassSSMPVisible);
					
					break;
				case 'Lpu_smpid':					
					var Lpu_smpidVisible = (is112 && CmpCallCard_IsPassSSMP);
					//field.setVisible(Lpu_smpidVisible);
					//field.setReadOnly(!Lpu_smpidVisible);
					field.setDisabled(!Lpu_smpidVisible);
					field.allowBlank = !Lpu_smpidVisible;					
					break;
				case 'CmpReason_id':

					if( getRegionNick().inlist(['perm']) ){
						field.setReadOnly(isClosedAndDisabled);
						field.setDisabled(isClosedAndDisabled);
						field.allowBlank = isClosedAndDisabled;
					}
					else{
						field.setReadOnly(!is112);
						field.setDisabled(!is112);
						field.allowBlank = !is112;
					}

					break;
				case 'CmpCallCard_Comm': {
					var unactive = ( isClosedAndDisabled && getRegionNick().inlist(['perm']) );
					field.setReadOnly(unactive);
					field.setDisabled(unactive);

					break;
				}
				case 'CmpCallCard_DateTper':
				case 'CmpCallCard_DateTperTime':
				case 'CmpCallCard_DateVyez':
				case 'CmpCallCard_DateVyezTime':
				case 'CmpCallCard_DatePrzd':
				case 'CmpCallCard_DatePrzdTime':
				case 'CmpCallCard_DateTgsp':
				case 'CmpCallCard_DateTgspTime':
				case 'CmpCallCard_DateTisp':
				case 'CmpCallCard_TispTime':
				case 'Lpu_hid':
				case 'CmpCallCard_HospitalizedTime':
				case 'CmpCallCard_HospitalizedTimeTime':
					if( getRegionNick().inlist(['perm']) ){
						field.setReadOnly(!isClosedAndDisabled);
						field.setDisabled(!isClosedAndDisabled);
					}
					else{
						field.setReadOnly(false);
						field.setDisabled(false);
					}
					break;
				default:
					//дилемма в чем если Пермь и закрыта то неактивна а пермь не проверяет 112
					//а остальные регионы только по 112 проверяют
					//сделаю всетку так будет понятней
					if( getRegionNick().inlist(['perm']) ){
						field.setReadOnly(isClosedAndDisabled);
						field.setDisabled(isClosedAndDisabled);
					}
					else{
						field.setReadOnly(!is112);
						field.setDisabled(!is112);
					}
					break;

			}
			field.validate();

		});
	},


	// функция отображения и фокусов полей для перекрестков
	// changeFocus - ставить фокус или нет
	checkCrossRoadsFields: function(changeFocus, e) {

		if(e && (e.getCharCode() == e.SHIFT)){return false;}

		var cntr = this,
			win = cntr.getEmergecyWindowRef(),
			baseForm = win.down('form[refId=CallDetailPanel]').getForm(),
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

		CmpCallCard_Korp.setDisabled(crossRoadsMode);
		CmpCallCard_Kvar.setDisabled(crossRoadsMode);
		CmpCallCard_Podz.setDisabled(crossRoadsMode);

		CmpCallCard_Etaj.setDisabled(crossRoadsMode);
		CmpCallCard_Kodp.setDisabled(crossRoadsMode);

		if(changeFocus){
			if(crossRoadsMode){
				secondStreetCombo.focus();
				cmpCallCard_Dom.reset();
				CmpCallCard_Korp.reset();
				CmpCallCard_Kvar.reset();
				CmpCallCard_Podz.reset();
				CmpCallCard_Etaj.reset();
				CmpCallCard_Kodp.reset();
			}
			else{
				cmpCallCard_Dom.focus();
				cmpCallCard_Dom.reset();
				if(secondStreetCombo.getPicker() && secondStreetCombo.getPicker().isVisible()){
					secondStreetCombo.collapse();
				}
			}
		}
	},

	showDPSolutionMsg: function(rec, storeCalls, callsView){
		var cntr = this,
			win = cntr.getEmergecyWindowRef(),
			rid = rec.get('CmpCallCard_rid'),
			storeTeams = Ext.data.StoreManager.lookup('common.DispatcherStationWP.store.EmergencyTeamStore'),
			teamId = rec.raw.EmergencyTeam_id,
			teamRecord = storeTeams.findBy(function(rec,id){return (id == teamId)}),
			txt = 'Необходимо согласовать отмену вызова <a href="javascript:void(0)" id="selectRid"> №' + rec.get("ridNum") + '</a><br/>' +
				rec.get("Person_FIO") + ',<br/>' +
				'Адрес: '+rec.get("Adress_Name")+'.<br/>' +
				'Причина отмены: '+rec.get("CmpCallCard_Comm"),
			buttons = [
				{
					xtype: 'button',
					text: 'Согласовать',
					margin: '0 15',
					handler: function () {

						/*
						Старый кусок, сейчас это не сервере
						//отменяющий вызов
						var params = {
							CmpCallCardStatusType_id: 6, //Статус отменяющего вызова меняется с «Решение старшего врача» на «Закрыто»
							CmpCallCard_id:	rec.get('CmpCallCard_id'),
							CmpCallCardStatus_Comment: 'Согласовано',
							armtype: 'smpdispatchstation'
						};

						Ext.Ajax.request({
							url: '/?c=CmpCallCard4E&m=setStatusCmpCallCard',
							params: params,
							success: function(response, opts){
								//первичный вызов
								var statusForFirstCard = '';
								//Если на первичный (отменяемый) вызов выехала бригада (то есть бригада на вызов назначена и находится в любом статусе, кроме «Ожидание принятия») то статус Обслужено
								if( rec.get('ridEmergencyTeam_id') && rec.get('ridEmergencyTeamStatus_Code') != '36'){
									statusForFirstCard = 4;
								}
								else{
									//иначе Статус первичного (отменяемого) вызова меняется на «Отказ»
									statusForFirstCard = 5;
								}

								var params = {
									CmpCallCardStatusType_id: statusForFirstCard, //Статус отменяемого вызова меняется в зависимости от статуса бригады
									CmpCallCard_id:	rec.get('CmpCallCard_rid'),
									CmpCallCardStatus_Comment: 'Согласовано',
									armtype: 'smpdispatchstation',
									typeSetStatusCCC: 'cancelmode'
								};


								Ext.Ajax.request({
									url: '/?c=CmpCallCard4E&m=setStatusCmpCallCard',
									params: params,
									callback: function (opt, success, response) {
										if (success) {
											Ext.ComponentQuery.query('redNotifyWindow')[0].close();
											cntr.reloadStores();
										}
										//Для обслуженного вызова меняем статусы бригады (для отмененного смена статусов бригад уже есть в модели)
										if(statusForFirstCard == 4){
											Ext.Ajax.request({
												url: '/?c=EmergencyTeam4E&m=setEmergencyTeamStatus',
												callback: function (opt, success, response) {
													Ext.Ajax.request({
														url: '/?c=EmergencyTeam4E&m=setEmergencyTeamStatus',
														params: {
															EmergencyTeamStatus_Code: (teamRecord && teamRecord.get('countcallsOnTeam') > 1) ? 36 : 13,
															EmergencyTeam_id: (rec.get('ridEmergencyTeam_id') || rec.get('ridEmergencyTeam_id')),
															ARMType: 'smpdispatchstation'
														}
													})
												},
												params: {
													EmergencyTeamStatus_Code: 4, //Конец обслуживания
													EmergencyTeam_id: (rec.get('ridEmergencyTeam_id') || rec.get('ridEmergencyTeam_id')),
													ARMType: 'smpdispatchstation'
												}
											});
										}else if(cntr.isNmpArm){
											//Для отмененного ставим результат обслуживания "Отказ от осмотра"
											Ext.Ajax.request({
												url: '/?c=CmpCallCard&m=setResult',
												params: {
													CmpPPDResult_Code: 8, //Отказ от осмотра
													CmpCallCard_id: rec.get('CmpCallCard_rid')
												}
											});
										}


									}
								});


							}
						});

						*/

						Ext.Ajax.request({
							url: '/?c=CmpCallCard4E&m=setStatusCmpCallCardByHD',
							params: {
								CmpCallCard_id:	rec.get('CmpCallCard_id'),
								callType: 'cancel',
								action: 'accept'
							},
							success: function (response, opts) {
								Ext.ComponentQuery.query('redNotifyWindow')[0].close();
								cntr.reloadStores();

								// оповещенеие NodeJS
								if(win.socket && win.socket.connected && rec.get('ridEmergencyTeam_id')){
									// ridEmergencyTeam_id - назаначенная бригада на первичный вызов
									var cmpCallCardParam = {
										CmpCallCard_id: rec.get('CmpCallCard_rid'),
										EmergencyTeam_id: rec.get('ridEmergencyTeam_id'),
										Comment: callDetailForm.findField('CmpCallCard_Comm').getValue()
									}
									win.socket.emit('registrationFailure', cmpCallCardParam, function(data){
										log('node emit registrationFailure : apk='+data);
									});
								}
							}
						});

					}
				},
				{
					xtype: 'button',
					text: 'Отклонить',
					handler: function () {

						Ext.Ajax.request({
							url: '/?c=CmpCallCard4E&m=setStatusCmpCallCardByHD',
							params: {
								CmpCallCard_id:	rec.get('CmpCallCard_id'),
								callType: 'cancel',
								action: 'discard'
							},
							success: function (response, opts) {
								Ext.ComponentQuery.query('redNotifyWindow')[0].close();
								cntr.reloadStores();
							}
						});

					}
				}
			];

		cntr.showNotify( txt, buttons, 120 );
		var b = Ext.get(Ext.DomQuery.select('#selectRid'));
		b.addListener('click', function(){
			var ridRec = storeCalls.findRecord('CmpCallCard_id', rid);
			callsView.getSelectionModel().deselectAll()
			if(ridRec)callsView.getSelectionModel().select(ridRec);
		});
	},

	showUnresultSolutionMsg: function(rec, storeCalls, callsView){
		var cntr = this,
			win = cntr.getEmergecyWindowRef(),
			rid = rec.get('CmpCallCard_rid'),
			txt = 'Необходимо согласовать завершение обслуживания вызова  <a href="javascript:void(0)" id="selectRid"> №' + rec.get("CmpCallCard_Numv") + '</a><br/>' +
				rec.get("Person_FIO") + ',<br/>' +
				'Адрес: '+rec.get("Adress_Name")+'.<br/>' +
				'Результат обслуживания: '+rec.get("CmpPPDResult_Name"),
			buttons = [
				{
					xtype: 'button',
					text: 'Согласовать',
					margin: '0 15',
					handler: function () {

						Ext.ComponentQuery.query('redNotifyWindow')[0].close();

						Ext.Ajax.request({
							url: '/?c=CmpCallCard4E&m=setStatusCmpCallCard',
							params: {
								CmpCallCardStatusType_Code: 4, //Обслужено
								CmpCallCard_id: rec.get('CmpCallCard_id'),
								armtype: 'smpdispatcherstation'
							},
							success: function (response, opts) {
								var obj = Ext.decode(response.responseText);
								if (obj.success) {
									cntr.reloadStores();
								}
							}
						});
					}
				},
				{
					xtype: 'button',
					text: 'Отклонить',
					handler: function () {

						Ext.ComponentQuery.query('redNotifyWindow')[0].close();

						Ext.Ajax.request({
							url: '/?c=CmpCallCard4E&m=setStatusCmpCallCard',
							params: {
								CmpCallCardStatusType_Code: 4, //Обслужено
								CmpCallCard_id: rec.get('CmpCallCard_id'),
								armtype: 'smpdispatcherstation'
							},
							success: function (response, opts) {
								var obj = Ext.decode(response.responseText);
								if (obj.success) {
									Ext.Ajax.request({
										url: '/?c=CmpCallCard4E&m=copyCmpCallCard',
										params: {
											CmpCallCard_id: rec.get('CmpCallCard_id')
										},
										success: function (response, opts) {
											var obj = Ext.decode(response.responseText);
											if (obj.success) {
												cntr.reloadStores();
											}
										}
									});
								}
							}
						});

						//copyCmpCallCard
					}
				}
			];

		cntr.showNotify( txt, buttons, 120 );
		var b = Ext.get(Ext.DomQuery.select('#selectRid'));
		b.addListener('click', function(){
			var ridRec = storeCalls.findRecord('CmpCallCard_id', rid);
			callsView.getSelectionModel().deselectAll()
		});
	},

	//события Нода для НМП и мобильного приложения
	eventNPM_NodeAPK: function(event, data, callback){
		var ARMType = (sw.Promed.MedStaffFactByUser.last != null) ? sw.Promed.MedStaffFactByUser.last.ARMType : sw.Promed.MedStaffFactByUser.current.ARMType;
		//if(ARMType != 'dispnmp') return false;
		var cntr = this;
		var params = data;
		var win = cntr.getEmergecyWindowRef();
		if (win.socket) {
			win.socket.emit(event, params, function(data){
				log('NODE emit '+event+' : apk='+data);
				if( typeof callback == 'function' ) callback(data);
			});
		}
	},

	setCardStatus: function(card, team, status, clearCard, teamsView, callsView){

		var cntr = this,
			statusCallCardParams = {
			//CmpCallCardStatusType_id: 4, //Обслужено
			CmpCallCardStatusType_Code: status, //Обслужено
			CmpCallCard_id:	card,
			armtype: 'smpdispatcherstation'
		};

		Ext.Ajax.request({
			url: '/?c=CmpCallCard4E&m=setStatusCmpCallCard',
			params: statusCallCardParams,
			success: function(response, opts){
				var obj = Ext.decode(response.responseText);
				if ( obj.success ) {

					var teamStatusParams = {
						'EmergencyTeamStatus_Code': 4, //Конец обслуживания
						'CmpCallCard_id': card,
						'EmergencyTeam_id':	team,
						'ARMType': (sw.Promed.MedStaffFactByUser.last != null) ? sw.Promed.MedStaffFactByUser.last.ARMType : sw.Promed.MedStaffFactByUser.current.ARMType
					};

					cntr.setEmergencyTeamStatus(teamStatusParams, teamsView, callsView,  function(){});

					cntr.eventNPM_NodeAPK('eventCallCompleted',
						{
							'CmpCallCard_id': card,
							'EmergencyTeam_id':	team
						}
					);
				}
			}
		});
	},

	setTitleServedTap: function(){
		var cntr = this,
			callStore = cntr.getStore('common.DispatcherStationWP.store.CmpCallsStore'),
			win = this.getEmergecyWindowRef(),
			servedCallsListTab = win.down('container[refId=servedCallsListTab]'),
			originiTitle = servedCallsListTab.initialConfig.title;

		var rec = callStore.findRecord( 'CmpGroupTable_id', 4);

		if(rec && !servedCallsListTab.tab.active){
			if(rec.get('countCardByGroup') > 0){
				if(!servedCallsListTab.tab.hasCls('attentionale')){
					servedCallsListTab.tab.addCls('attentionale');
				}
				servedCallsListTab.tab.setText( servedCallsListTab.initialConfig.title + " (" + rec.get('countCardByGroup') + ")")
			}
			else {
				if (servedCallsListTab.tab.hasCls('attentionale')) {
					servedCallsListTab.tab.removeCls('attentionale');
				}
				servedCallsListTab.tab.setText( servedCallsListTab.initialConfig.title);
			}
		}
	},

	showEditLpuBuildingMessage: function(rec, storeCalls, callsView){
		var cntr = this,
			win = cntr.getEmergecyWindowRef(),
			callsCardsShortGrid = win.down('panel[refId=callsCardsShortGrid]'),
			callDetailForm = win.down('form[refId=CallDetailPanel]').getForm(),
			txt = 'Необходимо подтвердить или изменить подстанцию для вызова  <a href="javascript:void(0)" id="selectRid"> №' + rec.get("CmpCallCard_Numv") + '</a><br/>' +
				rec.get("Person_FIO") + ',<br/>' +
				'Адрес: '+rec.get("Adress_Name")+'.<br/>',
			buttons = [
				{
					xtype: 'button',
					text: 'Подтвердить',
					margin: '0 15',
					handler: function () {

						Ext.ComponentQuery.query('redNotifyWindow')[0].close();

						Ext.Ajax.request({
							url: '/?c=CmpCallCard4E&m=setStatusCmpCallCard',
							params: {
								CmpCallCardStatusType_Code: 1, //Передано
								CmpCallCard_id: rec.get('CmpCallCard_id'),
								armtype: 'smpdispatcherstation'
							},
							success: function (response, opts) {
								var obj = Ext.decode(response.responseText);
								if (obj.success) {
									cntr.reloadStores();
								}
							}
						});
					}
				},
				{
					xtype: 'button',
					text: 'Изменить',
					handler: function () {

						Ext.ComponentQuery.query('redNotifyWindow')[0].close();

						cntr.showSelectLpuBuildingWindow(rec, callsView);

					}
				}
			];

		cntr.showNotify( txt, buttons, 120 );
		var b = Ext.get(Ext.DomQuery.select('#selectRid'));
		b.addListener('click', function(){
			callsView.getSelectionModel().select(rec);
		});
	},

	showAcceptDenyCallMessage: function(rec, storeCalls, callsView){
		var cntr = this,
			win = cntr.getEmergecyWindowRef(),
			callsCardsShortGrid = win.down('panel[refId=callsCardsShortGrid]'),
			callDetailForm = win.down('form[refId=CallDetailPanel]').getForm(),
			txt = 'Необходимо разрешить отклонение вызова № <a href="javascript:void(0)" id="selectRid"> №' + rec.get("CmpCallCard_Numv") + '</a> и указать другую подстанцию<br/>' +
				rec.get("Person_FIO") + ',<br/>' +
				'Адрес: '+rec.get("Adress_Name")+'.<br/>',
			buttons = [
				{
					xtype: 'button',
					text: 'Разрешить',
					margin: '0 15',
					handler: function () {

						Ext.ComponentQuery.query('redNotifyWindow')[0].close();
						cntr.showSelectLpuBuildingWindow(rec, callsView);

					}
				},
				{
					xtype: 'button',
					text: 'Запретить',
					handler: function () {

						Ext.ComponentQuery.query('redNotifyWindow')[0].close();

						Ext.Ajax.request({
							url: '/?c=CmpCallCard4E&m=setStatusCmpCallCard',
							params: {
								CmpCallCardStatusType_Code: 1, //Передано
								CmpCallCard_id: rec.get('CmpCallCard_id'),
								armtype: 'smpdispatcherstation'
							},
							success: function (response, opts) {
								var obj = Ext.decode(response.responseText);
								if (obj.success) {
									cntr.reloadStores();
								}
							}
						});



					}
				}
			];

		cntr.showNotify( txt, buttons, 120 );
		var b = Ext.get(Ext.DomQuery.select('#selectRid'));
		b.addListener('click', function(){
			callsView.getSelectionModel().select(rec);
		});
	},
	checkLpuBuildingWithoutSmpUnitHistory: function(){
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
						cntr.showNotify('Подстанции ' + lbs.join(',') + 'не взяты под управление диспетчером' )
					}


				}
			}
		})
	},
	showSelectLpuBuildingWindow: function(recCall, callsView, x, y){
		if (recCall) {
			var cntr = this,
				selectLpuBuildingWindow = Ext.create('Ext.window.Window', {
					title: 'Выбор подразделения СМП',
					height: 120,
					width: 400,
					layout: 'hbox',
					modal: true,
					items: {
						xtype: 'BaseForm',
						height: 120,
						width: 400,
						items: {
							border: false,
							padding: '10 10 10 10',
							xtype: 'container',
							layout: 'column',
							floatable: false,
							height: 160,
							items: [
								{
									xtype: 'smpUnitsNestedCombo',
									name: 'LpuBuilding_id',
									width: 280,
									labelWidth: 120,
									autoFilter: true,
									margin: '0 0 0 10',
									value: recCall.data.LpuBuilding_id,
									displayTpl: '<tpl for="."> {LpuBuilding_Code}. {LpuBuilding_Name} </tpl>',
									tpl: '<tpl for="."><div class="enlarged-font x-boundlist-item">'+
									'<font color="red">{LpuBuilding_Code}</font> {LpuBuilding_Name}'+
									'</div></tpl>'
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

								var form = selectLpuBuildingWindow.down('form').getForm(),
									lpuBuilding_id = form.findField('LpuBuilding_id').getValue();

								if(lpuBuilding_id){
									Ext.Ajax.request({
										url: '/?c=CmpCallCard4E&m=sendCmpCallCardToLpuBuilding',
										params: {
											'LpuBuilding_id': lpuBuilding_id,
											'CmpCallCard_id': recCall.data.CmpCallCard_id
										},
										callback: function(opt, success, response){

											Ext.Ajax.request({
												url: '/?c=CmpCallCard4E&m=setStatusCmpCallCard',
												params: {
													CmpCallCardStatusType_Code: 1, //Передано
													CmpCallCard_id: recCall.data.CmpCallCard_id,
													armtype: 'smpdispatcherstation'
												},
												success: function (response, opts) {
													var obj = Ext.decode(response.responseText);
													if (obj.success) {
														cntr.reloadStores();
													}
												}
											});
										}
									});
								}
								selectLpuBuildingWindow.close();

							}
						},
						{xtype: 'tbfill'},
						{
							xtype: 'button',
							iconCls: 'cancel16',
							text: 'Закрыть',
							handler: function () {
								selectLpuBuildingWindow.close();
							}
						}
					]
				});

			selectLpuBuildingWindow.showAt(x, y);
		}
	},

	searchAndFocusTeamButton: function(){
		var cntr = this,
			storeTeams = Ext.data.StoreManager.lookup('common.DispatcherStationWP.store.EmergencyTeamStore'),
			win = cntr.getEmergecyWindowRef(),
			filterForm = Ext.getCmp(win.id+'_mainPanel'),
			filterTeamNum = filterForm.getForm().findField('FilterTeamNum');

		var rec = storeTeams.find('EmergencyTeam_Num', filterTeamNum.getValue(), false, false);

		if(rec != -1){
			cntr.getEmergencyTeamViewRef().getSelectionModel().select(storeTeams.getAt(rec));
		}else{
			cntr.getEmergencyTeamViewRef().getSelectionModel().deselectAll();
		}

	},
	searchAndFocusCallButton: function(){
		var cntr = this,
			storeCalls = Ext.data.StoreManager.lookup('common.DispatcherStationWP.store.CmpCallsStore'),
			win = cntr.getEmergecyWindowRef(),
			filterForm = Ext.getCmp(win.id+'_mainPanel'),
			values = filterForm.getValues();

		var cardIndex = storeCalls.findBy(function(rec,id){

			if(
				( !values.FilterCityCombo || rec.get('KLCity_id') == values.FilterCityCombo ||  rec.get('KLTown_id') == values.FilterCityCombo ) &&
				( !values.FilterStreetsCombo || rec.get('StreetAndUnformalizedAddressDirectory_id') == values.FilterStreetsCombo ) &&
				( !values.FilterCmpCallCard_Kvar || rec.get('CmpCallCard_Kvar') == values.FilterCmpCallCard_Kvar ) &&
				( !values.FilterCmpCallCard_Korp || rec.get('CmpCallCard_Korp') == values.FilterCmpCallCard_Korp ) &&
				( !values.FilterCmpCallCard_Dom || rec.get('CmpCallCard_Dom') == values.FilterCmpCallCard_Dom ) &&
				( !values.FilterCmpCallCard_Numv || rec.get('CmpCallCard_Numv') == values.FilterCmpCallCard_Numv ) &&
				( !values.FilterCmpCallCard_Ngod || rec.get('CmpCallCard_Ngod') == values.FilterCmpCallCard_Ngod )
			){
				return true;
			}
			else{
				return false;
			}

		});

		if(cardIndex != -1){
			cntr.getCmpCallCardViewRef().getSelectionModel().select(storeCalls.getAt(cardIndex));
			//cntr.getCmpCallCardViewRef().focusNode(cardRecord);
		}else{
			cntr.getCmpCallCardViewRef().getSelectionModel().deselectAll();
		}
	},

	changeDuty: function (teamId) {
		var cntr = this;

		Ext.Ajax.request({
			url: '/?c=EmergencyTeam4E&m=loadEmergencyTeam',
			params: {
				EmergencyTeam_id: teamId
			},
			callback: function (opts, success, response) {
				var res = Ext.JSON.decode(response.responseText)[0];

				if (!res) return false

				res.EmergencyTeamDuty_DFinish = new Date(res.EmergencyTeamDuty_DTFinish);
				res.EmergencyTeamDuty_DStart = new Date(res.EmergencyTeamDuty_DTStart);
				res.EmergencyTeamDuty_DTFinish = new Date(res.EmergencyTeamDuty_DTFinish);
				res.EmergencyTeamDuty_DTStart = new Date(res.EmergencyTeamDuty_DTStart);
				res.mode = 'split';
				res.callback = function() {
					cntr.reloadStores()
				};
				Ext.create('sw.tools.subtools.swEmergencyTeamPopupEditWindow', res).show()
			}
		});
	},

	showWndFromExt2: function(wnd, card_id){

		if(Ext.isEmpty(wnd) || Ext.isEmpty(card_id)){
			return;
		}
		var me = this,
			title = (wnd == 'swCmpCallCardNewCloseCardWindow') ? 'Карта вызова' : 'Талон вызова',
			action = 'view';

		new Ext.Window({
			title: title,
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
					src : "/?c=promed&getwnd=" + wnd + "&act=" + action + "&showTop=1&cccid="+card_id
				}
			}]
		}).show();
	},

	//лочим кнопки вида, пока не загрузится стор
	lockViewBtns: function (lock) {
		var cntr = this,
			win = cntr.getEmergecyWindowRef(),
			vid1_btn = win.down('[refId=vid1_btn]'),
			vid2_btn = win.down('[refId=vid2_btn]');

		vid1_btn.setDisabled(lock);
		vid2_btn.setDisabled(lock);
	}
});
