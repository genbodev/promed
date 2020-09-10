/*
 * Контроллер АРМа диспетчера направлений
 */

Ext.define('SMP.swDispatcherDirectWorkPlace_controller', {
	extend: 'SMP.swSMPDefaultController_controller',
	models: [
		'common.DispatcherDirectWP.model.CmpCallCard',
		'common.DispatcherDirectWP.model.EmergencyTeam',
		'common.DispatcherDirectWP.model.EmergencyTeamStatus'
	],
	stores: [
		'common.DispatcherDirectWP.store.CmpCallsStore',
		'common.DispatcherDirectWP.store.EmergencyTeamStore',
		'common.DispatcherDirectWP.store.EmergencyTeamStatusStore',
		// 'stores.smp.GeoserviceTransportStore',
		'stores.smp.GeoserviceTransportGroupStore',
		//'common.DispatcherDirectWP.store.EmergencyTeamProposalLogic'
	],
	refs: [{
			ref: 'mapPanelRef', // суфикс ref для того чтобы можно было отличить ссылку от метода класса (не надо копировать этот камент)
			selector: 'swDispatcherDirectWorkPlace swsmpmappanel'
		},{
			ref: 'emergencyTeamViewRef',
			selector: 'swDispatcherDirectWorkPlace #teamGrid'
		},{
			ref: 'CmpCallCardViewRef',
			selector: 'swDispatcherDirectWorkPlace #callsGrid'
		},{
			ref: 'emergecyWindowRef',
			selector: 'swDispatcherDirectWorkPlace'
		}
	],
	init: function() {
		var controller = this;
		this.control({
			'swDispatcherDirectWorkPlace': {
				beforerender: function(cmp){
					//прикручиваем ноджс
						connectNode(cmp);
				},
				
				render: function(cmp){
					var dt = new Date(Date.now()),
						firstDayMonth = Ext.Date.format(Ext.Date.getFirstDateOfMonth(dt), 'd.m.Y'),
						lastDayMonth = Ext.Date.format(Ext.Date.getLastDateOfMonth(dt), 'd.m.Y'),
						storeCalls = Ext.data.StoreManager.lookup('common.DispatcherDirectWP.store.CmpCallsStore'),
						storeTeams = Ext.data.StoreManager.lookup('common.DispatcherDirectWP.store.EmergencyTeamStore'),
						geoservice_store = this.getGeoserviceTransportStore(),
						cntr = this;
					
					cmp.geoservice_store = geoservice_store;
					
					
					// @TODO: Сделать единую загрузку для всех регионов
					// @TODO: Соответственно переделать карты виалон под единый интерфейс всех классов карт
					
					cmp.geoservice_store.on( 'load' , function( store , records, successful, eOpts) {
						var marker = {};
						var markerList = [];
						if(!records){return;}
						for (var i = 0; i < records.length; i++) {
							
							if (!records[i].data) { 
								continue; 
							}
							
							marker = {
								id: records[i].data.GeoserviceTransport_id,
								title: records[i].data.GeoserviceTransport_name,
								point: [ records[i].data.lat , records[i].data.lng ],
								direction: records[i].data.direction
							}
							
							markerList.push(marker);
						};
						//log({markerList:markerList});
						for (i = 0; i < markerList.length; i++) {
							// @TODO: Доработать удаление маркеров, данных по которым не пришло (по id)
							this.getMapPanelRef().setAmbulanceMarker(markerList[i]);
						};
						
					},  this);
					
					// @TODO: почему затирается гриб бригад.
					//
					cmp.geoservice_store.runAutoRefresh();
					
					storeCalls.load({
						params: {
							begDate: '01.06.2013',
							endDate: lastDayMonth,
							CmpGroup_id: 1
						}
					});
					
					//проверка на подключенный ноджс
					//если нет то вручную апдейт
					setInterval(function(){
						var activeWin = Ext.WindowManager.getActive();

						if (cmp==activeWin){
							if (!cmp.socket || !cmp.socket.connected){
								this.reloadStores();								
							}
						}
					}.bind(this),15000);
					
					Ext.ComponentQuery.query('button[refId=buttonChooseArm]')[0].on(
						'afterSelectArm', function(){
							cntr.reloadStores();
					});

					if (cmp.socket && cmp.socket.connected){
						//листенер на изменение бригады
						cmp.socket.on('changeEmergencyTeamStatus', function (data, action) {
							var	win = this.getEmergecyWindowRef(),
								teamsView = win.TeamsView,
								emergencyTeam = JSON.parse(data),
								teamInStore = storeTeams.findRecord('EmergencyTeam_id', emergencyTeam[0].EmergencyTeam_id);

							if(teamInStore){
								switch(action){
									case 'changeStatus': {
										teamInStore.set('EmergencyTeamStatus_id', emergencyTeam[0].EmergencyTeamStatus_id);
										teamInStore.set('EmergencyTeamStatus_Name', emergencyTeam[0].EmergencyTeamStatus_Name);			
										storeTeams.sort();
										teamsView.refresh();
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

						cmp.socket.on('setEmergencyTeamToCall', function (data) {
						}.bind(this));	

						cmp.socket.on('addTimeEmergencyTeams', function(data){
							var win = this.getEmergecyWindowRef(),
								teamsView = win.TeamsView;

								storeTeams.add(data)						

							teamsView.refresh();
						}.bind(this))

						// Обновление списка бригад по таймеру
						/*
						cmp.socket.on('updateEmergencyTeamOperEnvForSmpUnit',function(EmergencyTeamsList,action){
							storeTeams.loadData(EmergencyTeamsList);							
						});
						*/

						//листенер на изменение карты
						cmp.socket.on('changeCmpCallCard', function (data, action) {
							log('node listen changeCmpCallCard', data, action)
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
										this.sortCmpCalls();
										//callsView.refresh();
									break;};
								}
						}.bind(this));
					}
				},
				afterrender: function(){
					this.setHotKeys(this.getEmergecyWindowRef());
				},
				show: function(wnd) {
					var sortingRadiogroup = wnd.EmergencyTeamSortingTypeRadioGroup,
						cntr = this,
						win = cntr.getEmergecyWindowRef(),
						teamsView = win.TeamsView,
						callsView = win.CallsView,
						storeCalls = Ext.data.StoreManager.lookup('common.DispatcherDirectWP.store.CmpCallsStore'),
						storeTeams = Ext.data.StoreManager.lookup('common.DispatcherDirectWP.store.EmergencyTeamStore'),
						panelCalls = callsView.up('panel'),
						panelTeams = teamsView.up('panel'),
						spanElCallsLoadingTool = panelCalls.down('tool[itemId=loadtool]').el.child('span'),
						spanElTeamsLoadingTool = panelTeams.down('tool[itemId=loadtool]').el.child('span');
						sortingRadiogroup.fireEvent('change', sortingRadiogroup, sortingRadiogroup.getValue() );
					
					this.checkHavingLpuBuilding();
					
					storeCalls.on('beforeload', function(store, operation){
						if(spanElCallsLoadingTool.hasCls('hiddentool'))spanElCallsLoadingTool.removeCls('hiddentool');
						/*
						cntr.stopLoadingStore(storeCalls);
						storeCalls.lastOperation = operation;
						*/
					});
					storeCalls.on('load', function(){
						if(!spanElCallsLoadingTool.hasCls('hiddentool'))spanElCallsLoadingTool.addCls('hiddentool');
					});
					
					storeTeams.on('beforeload', function(store, operation){
						if(spanElTeamsLoadingTool.hasCls('hiddentool'))spanElTeamsLoadingTool.removeCls('hiddentool');
					});
					storeTeams.on('load', function(){
						if(!spanElTeamsLoadingTool.hasCls('hiddentool'))spanElTeamsLoadingTool.addCls('hiddentool');
					});
					
					//Поскольку на Уфе объектов на данный момент около 180 и групп порядка 45
					//По умолчанию все группы скрываем, соответственно должны быть скрыты все маркеры
					
					
					if (getRegionNick().inlist(['ufa', 'krym'])) {
						
						this.getStore('stores.smp.GeoserviceTransportGroupStore').on('load', function( store, records, successful, eOpts) {
							for (var i = 0; i < records.length; i++) {
								records[i].set('visible',false);
							};
							
							//Прячем все отображаемые маркеры, если на момент обновления стора групп они отобразились
							this.filterEmergencyTeamListWithMarkersBySelectedGroupList();
							
							//В случае, если маркеры ещё не отобразились , необходимо повесить обработчик на следующее
							// и только на следующее обновление стора транспортных средств
							
							var geoservice_store_load_listener = function() {
								this.filterEmergencyTeamListWithMarkersBySelectedGroupList();
								this.getGeoserviceTransportStore().removeListener('load',geoservice_store_load_listener);
							}.bind(this)
							
							this.getGeoserviceTransportStore().addListener('load',geoservice_store_load_listener);
							
						}.bind(this));
					}
					
					this.getStore('stores.smp.GeoserviceTransportGroupStore').load();
				}
			},
			
			'swDispatcherDirectWorkPlace swsmpmappanel': {
				afterrender: function( self, eOpts ) {	
					self.currentCallPoint = {};
				},
				
				afterMapRender: function( self ) {
					if (typeof self.setMarkers == 'function'){

					}
				},
				detectLocation: function(){

				},
				onSetRoadTrack: function (time){
					var	win = this.getEmergecyWindowRef(),
						teamsView = win.TeamsView,
						callsView = win.CallsView,
						selectedTeamRec = teamsView.getSelectionModel().getSelection()[0],
						selectedCallRec = callsView.getSelectionModel().getSelection()[0];
				
					// this.loadMask.hide();
					// Ext.MessageBox.confirm('Назначение бригады на вызов', 
					// 'Время маршрута ~'+time.timeDuration+' Назначить выбранную бригаду на вызов?',function(btn){
						// if( btn === 'yes' ){
							// this.setEmergencyTeamToCall();
						// }
					// },this);
				}
			},
			
			'swDispatcherDirectWorkPlace #teamGrid': {
				selectionchange: function( self, selected, eOpts ) {
					this.getMapPanelRef().clearRoutes();
				},
				itemclick: function( obj, record, item, index, e, eOpts ){
					var mainWin = obj.up('window'),
						mapPanel = mainWin.mapPanel,
						elCls = e.target.getAttribute('class');						
						
					if (elCls){
						if (elCls.indexOf("cell-cmpcc-moreinfo") != -1){
							this.showEmergencyTeamSubMenu(e.getX()-100, e.getY(), record)
						}
						else{
							this.setCenterEmergencyTeamOnMap(record);
						}
					}
					else{
						this.setCenterEmergencyTeamOnMap(record);
					}
					//obj.fireEvent('containerclick');
					this.toggleFocusClassOnView('EmergencyTeam');
				},
				containerclick: function(cmp){
					this.toggleFocusClassOnView('EmergencyTeam');
				}
			},
			
			//панель фильтров бригад
			'swDispatcherDirectWorkPlace grid[refId=emergencyTeamGroupFilterGrid] checkcolumn': {
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
			
			'swDispatcherDirectWorkPlace button[refId=hr-splitter]': {
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
			'swDispatcherDirectWorkPlace button[refId=vr-splitter]': {
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
						if(typeof google != 'undefined')
							if(typeof google.maps.event != 'undefined')
								google.maps.event.trigger(mapWin.getPanelByType(mapWin.getCurrentMapType()).map,'resize');
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
			'swDispatcherDirectWorkPlace button[refId=refresh]': {
				click: function(cmp){
					var map_panel = this.getMapPanelRef().getPanelByType( this.getMapPanelRef().getCurrentMapType() );
					map_panel.refreshItems();
				}
			},

			'#callsWin #callsGrid': {
				selectionchange: function( self, selected, eOpts ) {
					
					this.getMapPanelRef().clearRoutes();
					
					this.getStore('common.DispatcherDirectWP.store.EmergencyTeamStore').each(function(record) {
						record.set('EmergencyTeamDistance', null);
						record.set('EmergencyTeamDuration', null );
						record.set('EmergencyTeamDistanceText', null );
						record.set('EmergencyTeamDurationText', null );
					})
					
					if (selected.length == 1) {
						this.offerEmergencyTeamToCmpCallCard( selected[0] );
					} 
					if (selected.length == 0) {
						this.getMapPanelRef().setAccidentMarker(null);
					}
				},
				itemclick : function(self, CmpCallCard_record, html_element, node_index, e){
					var elCls = e.target.getAttribute('class');					
					
					if (elCls && (elCls.indexOf("cell-cmpcc-moreinfo") != -1) ){
						
						this.showCmpCallCardSubMenu(e.getX()-100, e.getY());
						
					}
					this.toggleFocusClassOnView('CmpCallCard');
				}.bind(this),
				render: function(self){
					var storeTeams =  Ext.data.StoreManager.lookup('common.DispatcherDirectWP.store.EmergencyTeamStore');
					
					setInterval(function(){
						this.sortCmpCalls();
						storeTeams.filter();
					}.bind(this), 60000);
				},				
				viewready: function(cmp){
					cmp.getEl().toggleCls('focused-panel');
					this.sortCmpCalls();
				},
				containerclick: function(cmp){
					this.toggleFocusClassOnView('CmpCallCard');
				}
			}, 
			'swDispatcherDirectWorkPlace radiogroup[name=EmergencyTeamSortingTypeRadioGroup]' : {
				change: function(radiogroup, newValue, oldValue, options) {
					
					var value = newValue[radiogroup.name];
					
					var EmergencyTeamStore = Ext.data.StoreManager.lookup('common.DispatcherDirectWP.store.EmergencyTeamStore');
					
					switch (value) {
						case '1':
							EmergencyTeamStore.setSortType('duration');
							EmergencyTeamStore.sort(EmergencyTeamStore.getCurrentSorterConfig());
							break;
						case '2': 
							EmergencyTeamStore.setSortType('freetime');
							EmergencyTeamStore.sort(EmergencyTeamStore.getCurrentSorterConfig());
							break;
						default: 
							break;
					}
					
				}
			}
			
		});
	},
	
	setHotKeys: function(cmp){
		var win = this.getEmergecyWindowRef(),
			callsView = win.CallsView,
			teamsView = win.TeamsView,
			mapPanel = this.getMapPanelRef(),
			me = this;
			
		var pressedkeyg = new Ext.util.KeyMap({
			target: cmp.el,
			binding: [
				{
					key: [Ext.EventObject.ENTER],
					fn: function(){
						//console.log('enter');
						var CmpCallCard_record = callsView.getSelectionModel().getSelection()[0],
							recTeam = teamsView.getSelectionModel().getSelection()[0];
						
						if (callsView.hasCls('focused-panel') && CmpCallCard_record){
							
							this.offerEmergencyTeamToCmpCallCard( CmpCallCard_record );
							
							this.toggleFocusClassOnView('CmpCallCard');
							//this.showCmpCallCardSubMenu();							
						}
						if (teamsView.hasCls('focused-panel')&&recTeam){
							
							this.setCenterEmergencyTeamOnMap(recTeam);
							this.toggleFocusClassOnView('EmergencyTeam');
							
//							this.showEmergencyTeamSubMenu();
						}
						
					}.bind(this)
				},
				{
					key: [Ext.EventObject.TAB],
					fn: function(c, e){
						//console.log('tab');
						if (callsView.hasCls('focused-panel')){
							teamsView.focus();
						}
						else{
							callsView.focus();
						}
						if (!callsView.hasCls('focused-panel')&&!teamsView.hasCls('focused-panel')){
							callsView.getEl().toggleCls('focused-panel');
						}
						else{
							callsView.getEl().toggleCls('focused-panel');
							teamsView.getEl().toggleCls('focused-panel');
						}
						e.preventDefault();
					}
				},
				{
					key: [Ext.EventObject.ESC],
					fn: function(c, e){
						if(me.getMapPanelRef().getHeight()>0){me.toggleExpandMapPanel();}
						if (callsView.hasCls('focused-panel')||teamsView.hasCls('focused-panel')){
							callsView.hasCls('focused-panel')?callsView.removeCls('focused-panel'):teamsView.removeCls('focused-panel');
							Ext.getCmp('Mainviewport_Toolbar').items.getAt(0).focus();
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
	
	showCmpCallCardFromGrid: function(store_record, cardId){
		var arrayCards = Ext.ComponentQuery.query('swsmpmappanel callCardWindow'),
			storeTeams = Ext.data.StoreManager.lookup('common.DispatcherDirectWP.store.EmergencyTeamStore'),
			win = this.getEmergecyWindowRef(),
			callsView = win.CallsView,
			teamsView = win.TeamsView;

		if (arrayCards.length < 1){
			var callcard = Ext.create('sw.tools.swShortCmpCallCard',{
				view: 'view', 
				card_id: store_record?store_record.get('CmpCallCard_id'):cardId,
				listeners: {
					close: function(){
						callsView.focus();
					}
				}
			});
			callcard.show();
		}else{
			arrayCards[0].fireEvent('show', {
				view: 'view',
				card_id: store_record?store_record.get('CmpCallCard_id'):cardId
			});
		}
	},
	
	showEmergencyTeamInfoFromGrid: function(record){
		var win = Ext.create('sw.tools.subtools.swEmergencyTeamOperEnvAddEdit', {
			action: 'viev',
			EmergencyTeam_id: record.get('EmergencyTeam_id')
		});
		win.show();
	},
	
	showEmergencyTeamEditStuffInfoFromGrid: function(record){
		var win = Ext.create('sw.tools.subtools.swEmergencyTeamOperEnvAddEdit', {
			action: 'edit',
			EmergencyTeam_id: record.get('EmergencyTeam_id'),
			height: 260
		});
		
		win.on('render', function(){
			win.down('fieldset[refId=commonInfo]').hide();
		})
		
		win.show();
	},
	
	// Снятие текущего вызова с бригады
	
	cancelCmpCallCardFromEmergencyTeam: function( CmpCallCard_id ){
		
		CmpCallCard_id = parseInt(CmpCallCard_id);
		
		if (isNaN(CmpCallCard_id)) {
			return false;
		}
		
		var win = this.getEmergecyWindowRef(),
			controller = this;
		
		this.loadMask = new Ext.LoadMask(win,{msg:"Отмена вызова..."});
		this.loadMask.show();
		
		
		Ext.Ajax.request({
			url: '/?c=EmergencyTeam4E&m=cancelEmergencyTeamFromCall',
			callback: function(opt, success, response) {
				if (success){
					//Ext.data.StoreManager.lookup('common.DispatcherDirectWP.store.EmergencyTeamStore').reload();
					Ext.Ajax.request({
						url: '/?c=CmpCallCard4E&m=cancelCmpCallCardFromEmergencyTeam',
						params: {
							CmpCallCard_id: CmpCallCard_id
						},
						callback: function(opt, success, response) {
							controller.loadMask.hide();
							if (success){
								var response_obj = Ext.decode(response.responseText);
								if ( !response_obj.success ) { 
									Ext.Msg.alert('Ошибка',response_obj.Error_Msg || 'При выполнении запроса снятия вызова с бригады произошла ошибка');
								}

								controller.reloadStores()

							} else {
								Ext.Msg.alert('Ошибка','Ошибка выполнения запроса снятия вызова с бригады.');
							}
						}

					});
				}
			}.bind(this),
			params: {								
				'CmpCallCard_id':	CmpCallCard_id,
				'ARMType': sw.Promed.MedStaffFactByUser.last.ARMType
			}
		})
		
		
		
	},
	
	showCmpCallCardSubMenu: function(x,y){
		var cntr = this,
			win = this.getEmergecyWindowRef(),
			callsView = win.CallsView,
			teamsView = win.TeamsView,
			CmpCallCardRecord = callsView.getSelectionModel().getSelection()[0],
			EmergencyTeamRecord = teamsView.getSelectionModel().getSelection()[0],		
			subMenu = Ext.create('Ext.menu.Menu', {
				//width: 100,
				//showSeparator: false,
				plain: true,
				renderTo: Ext.getBody(),
				constrainTo: (x&&y)? null : callsView.getSelectedNodes()[0].querySelector('.cell-cmpcc-moreinfo'),
				items: [
					{
						text: 'Информация',
						handler: function(){						
							this.showCmpCallCardFromGrid(CmpCallCardRecord);
							subMenu.close();
						}.bind(this)
					},{
						text: 'На бригаду',
						hideOnClick: false,
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
									cntr.setCenterEmergencyTeamOnMap(team);
								}
							}
						}		
					},
					{
						text: 'Отклонить бригаду',
						hidden: (CmpCallCardRecord.get('EmergencyTeam_id')>0)?false:true,
						handler: function(i){
							cntr.cancelCmpCallCardFromEmergencyTeam( CmpCallCardRecord.get('CmpCallCard_id') );
						}
					}
				]
			}),
			subMenuEmergencyTeamsToCall = subMenu.down('menu[itemId=TeamsToCallDynamicSubMenu]');	
		
		teamsView.store.each(function(rec){
			subMenuEmergencyTeamsToCall.add({
				text: rec.get('EmergencyTeam_Num')+' '+rec.get('EmergencyTeamStatus_Name'),
				value: rec.get('EmergencyTeam_id')
			})
		})
			
		subMenu.showAt(x,y);
	},
	
	showEmergencyTeamSubMenu: function(x,y, record){
		var cntr = this,
			win = this.getEmergecyWindowRef(),
			callsView = win.CallsView,
			teamsView = win.TeamsView,
			recCard = callsView.getSelectionModel().getSelection()[0],
			recTeam = teamsView.getSelectionModel().getSelection()[0],
			storeTeamStatus = Ext.data.StoreManager.lookup('common.DispatcherDirectWP.store.EmergencyTeamStatusStore'),			
			subMenu = Ext.create('Ext.menu.Menu', {
				//width: 120,
				showSeparator: false,
				renderTo: Ext.getBody(),
				constrainTo: (x&&y)? null : teamsView.getSelectedNodes()[0],
				listeners:{
					hide: function(t,e){
						win.defaultFocus = 'teamGrid';
					}
				},
				items: [{
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
												win.socket.emit('changeEmergencyTeamStatus', data, 'changeStatus', function(data){
													
												});
												teamsView.getStore().reload();
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
				},{
					text: 'Информация',
					handler: function(){
						cntr.showEmergencyTeamInfoFromGrid(recTeam);
						//@to_do - вынужденная мера, тк хэндлер срабатывает 2 раза
						//будет время -  разобраться
						subMenu.close();
					}
				},
				/*{
					text: 'Состав',
					handler: function(){
						cntr.showEmergencyTeamEditStuffInfoFromGrid(recTeam);
						subMenu.close();
					}
				},
				{
					text: 'Замена авто',
					disabled: true					
				},*/
				{
					text: 'Информация о КТ',
					handler: function(){
						var cardId = recTeam.get('CmpCallCard_id');
						
						if(cardId)cntr.showCmpCallCardFromGrid(null, cardId);
						subMenu.close();
					},
					disabled: !recTeam.get('CmpCallCard_id')
				},
				{
					text: 'На вызов',
					handler: function(i){					
						cntr.showWindowCallsToTeam();
						subMenu.close();
					}
				},
				{
					text: 'Отменить вызов',
					disabled: !(record && record.get('CmpCallCard_id')),
					handler: function(i){					
						cntr.cancelCmpCallCardFromEmergencyTeam( record.get('CmpCallCard_id') );
						subMenu.close();
					}
				}
				]
			}),
			subMenuEmergencyStatuses = subMenu.down('menu[itemId=EmergencyStatusDynamicSubMenu]');
		
		storeTeamStatus.each(function(rec){
			if (rec.get('EmergencyTeamStatus_id') != 59) {
				subMenuEmergencyStatuses.add({
					text: rec.get('EmergencyTeamStatus_Name'),
					value: rec.get('EmergencyTeamStatus_id')
				})
			}
		});
		
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
										
										c.add({
											text: t
										});
									}
								}
							}
						});
					}
				}
			}
		})
		//	console.log(subMenu.down('menu[itemId=EmergencyStatusDynamicSubMenu]'));
		
		subMenu.showAt(x,y);
	},
	
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
		
		this.sortCmpCalls();
		
		return;
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
	
	printControlBill: function(teamData, callData){
		Ext.Ajax.request({
			url: '/?c=CmpCallCard4E&m=printControlTicket',
			params: {
				teamId: teamData.data.EmergencyTeam_id,
				callId: callData.data.CmpCallCard_id
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
	
	sortEmergencyTeamStore: function(callback){
		
		var EmergencyTeamStore = this.getStore('common.DispatcherDirectWP.store.EmergencyTeamStore'),
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
	
	sortCmpCalls: function(){
		console.log('sorting..');
		var storeCalls = Ext.data.StoreManager.lookup('common.DispatcherStationWP.store.CmpCallsStore');
		if ( Ext.getCmp('tbar_sorting') && Ext.getCmp('tbar_sorting').getValue().tbar_sorting == '1' ) {
			console.log('by time..');
			storeCalls.sort([{
				property: 'CmpCallCard_prmDate',
				direction: 'ASC',
				sorterFn: function(v1,v2){
					var date1 = new Date(Date.parse(v1.get('CmpCallCard_prmDate'))),
						date2 = new Date(Date.parse(v2.get('CmpCallCard_prmDate')));
					if ( date1 < date2 ) {
						return -1;
					} else if ( date1 == date2 ) {
						return 0;
					} else {
						return 1;
					}
				}
			}]);
		} else {
			for( var key in storeCalls.data.items){
				var rec = storeCalls.getAt(key),
					val = rec.data,
					delta = new Date() - new Date(Date.parse(val.CmpCallCard_prmDate)),
					mins = Math.floor(delta/60000),
					updateTimeMinutes = 15,
					result = Math.floor(mins/updateTimeMinutes),
					urgencyVal = val.CmpCallCard_Urgency - result;

				if (urgencyVal > 0){
					rec.set('CmpCallCard_CalculatedUrgency', urgencyVal);
				} else {
					rec.set('CmpCallCard_CalculatedUrgency', 1);
				}
				if (key==storeCalls.count()-1){
					storeCalls.sort('CmpCallCard_CalculatedUrgency', 'ASC');
					//grid.separatorIsSet = false;
					storeCalls.sort('CmpGroup_id', 'ASC');
				}
			}
		}
	},
	
	reloadStores: function(){
		this.getStore('common.DispatcherDirectWP.store.CmpCallsStore').reload();
		this.getStore('common.DispatcherDirectWP.store.EmergencyTeamStore').reload();
	},
	
	// Перемещается к выбранной бригаде и отображает ее по центру карты
	setCenterEmergencyTeamOnMap: function(record, durationText){
		
		// ID 59 - ожидание принятия
		if (record.get('EmergencyTeamStatus_id') == '59') {
			setTimeout(function(){
				Ext.Msg.alert('Ошибка','Бригада в статусе ожидания принятия.');
			},1000);
			return false;
		}

		var map_panel = this.getMapPanelRef();
		
		if (record.get('GeoserviceTransport_id')){ map_panel.setRouteFromAmbulanceToAccident( record.get('GeoserviceTransport_id') );}
		
		Ext.MessageBox.confirm('Назначение бригады на вызов', (durationText&&('Время доезда: '+durationText)||'') + 'Назначить выбранную бригаду на вызов?',function(btn){
			if( btn === 'yes' ){
				this.setEmergencyTeamToCall();
			} else {
				this.getEmergencyTeamViewRef().getSelectionModel().deselectAll();
				this.getCmpCallCardViewRef().getSelectionModel().deselectAll();
			}
		},this);		
	},
	
	setEmergencyTeamToCall: function(callback){
		var	win = this.getEmergecyWindowRef(),
			teamsView = this.getEmergencyTeamViewRef(),
			callsView = this.getCmpCallCardViewRef(),
			selectedTeamRec = teamsView.getSelectionModel().getSelection()[0],
			selectedCallRec = callsView.getSelectionModel().getSelection()[0],
			selectedTeamId = selectedTeamRec.get('EmergencyTeam_id'),
			selectedCallId = selectedCallRec.get('CmpCallCard_id'),
			selectedTeamNum = selectedTeamRec.get('EmergencyTeam_Num'),
			selectedCallNum = selectedCallRec.get('CmpCallCard_Numv'),
			storeCalls = Ext.data.StoreManager.lookup('common.DispatcherDirectWP.store.CmpCallsStore');
		
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
			callback: function(){
				loadMask.hide();
			},
			success: function(response, opts){
				var obj = Ext.decode(response.responseText);
				if ( obj.success ) {
					var socketData = {EmergencyTeam_id: selectedTeamId, CmpCallCard_id: selectedCallId};
						win.socket.emit('setEmergencyTeamToCall', socketData, function(data){
							console.log('NODE emit setEmergencyTeamToCall');
						});
						
					Ext.Ajax.request({
						url: '/?c=EmergencyTeam4E&m=setEmergencyTeamStatus',
						callback: function(opt, success, response) {
							if (success){
								response_obj = Ext.decode(response.responseText),
								data = {'EmergencyTeam_id':selectedTeamId};
								win.socket.emit('changeEmergencyTeamStatus', data, 'changeStatus', function(data){
									console.log('NODE emit changeStatus');
								});
								teamsView.refresh();
								teamsView.getStore().reload();
							}
						}.bind(this),
						
						// ID 59 - ожидание принятия
						params: {
							'EmergencyTeamStatus_id': 59,
							'EmergencyTeam_id':	selectedTeamId,
							'ARMType': sw.Promed.MedStaffFactByUser.last.ARMType
						}
					})
						
					Ext.MessageBox.confirm('Сообщение', 
						'Бригада №'+selectedTeamNum+' назначена на вызов №'+ selectedCallNum+'.'+'</br>Распечатать контрольный талон?',function(btn){
							if( btn === 'yes' ){
								this.printControlBill(selectedTeamRec,selectedCallRec);							
							}
						}.bind(this))							
					storeCalls.reload();
					callsView.refresh();
				} else {
					Ext.Msg.alert('Ошибка','Во время назначения бригады произошла непредвиденная ошибка. Перезагрузите страницу и попробуйте выполнить действие заново. Если ошибка повторится, обратитесь к администратору.');
				}
			}.bind(this),
			failure: function(response, opts){
				Ext.MessageBox.show({title:'Ошибка',msg:'Во время выполнения запроса произошла непредвиденная ошибка.',buttons:Ext.MessageBox.OK});
			}
		});
	},
	
	resetEmergencyTeamFromCall: function(){
		var	win = this.getEmergecyWindowRef(),
			callsView = win.CallsView,
			selectedCallRec = callsView.getSelectionModel().getSelection()[0],
			selectedCallId = selectedCallRec.get('CmpCallCard_id');
			
		var loadMask = new Ext.LoadMask(Ext.getBody(),{msg:"Сохранение данных..."});
			loadMask.show();
		
		Ext.Ajax.request({
			url: '/?c=CmpCallCard4E&m=setEmergencyTeamWithoutSending',
			params: {
				EmergencyTeam_id: 0,
				CmpCallCard_id: selectedCallId
			},
			callback: function(){
				loadMask.hide();
			},
			success: function(response, opts){
				var obj = Ext.decode(response.responseText);
				if ( obj.success ) {					
					Ext.MessageBox.confirm('Сообщение', 'Бригада отклонена');
					callsView.refresh();
				} else {
					Ext.Msg.alert('Ошибка','Во время отклонения бригады произошла непредвиденная ошибка. Перезагрузите страницу и попробуйте выполнить действие заново. Если ошибка повторится, обратитесь к администратору.');
				}
			}.bind(this),
			failure: function(response, opts){
				Ext.MessageBox.show({title:'Ошибка',msg:'Во время выполнения запроса произошла непредвиденная ошибка.',buttons:Ext.MessageBox.OK});
			}
		});
	},
	
	//Метод фильтрации списка нарядов и их маркеров по выбранным группам транспортных средств
	filterEmergencyTeamListWithMarkersBySelectedGroupList: function() {
		
		var emergencyTeamStore = this.getStore('common.DispatcherDirectWP.store.EmergencyTeamStore'),
			geoserviceTransportStore = this.getGeoserviceTransportStore(),
			mapPanel = this.getMapPanelRef(),
			visibleGroupList = [],
			visibleGeoserviceTransportList = [],
			hiddenGeoserviceTransportList = [];
		
		//Получаем список групп, которые необходимо отображать
		this.getStore('stores.smp.GeoserviceTransportGroupStore').each(function(record){
			if(record.get('visible') === true){
				visibleGroupList.push(  record.get('id')+'' );
			}
		});
		
		//Получаем список отображаемого транспорта по списку отображаемыхх групп
		geoserviceTransportStore.each(function(record){
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
		});
		
		// Два массива hiddenGeoserviceTransportList и visibleGeoserviceTransportList используются потому, что 
		// в случае с отображением маркеров необходимо указать параметр отображения для каждого конкретного маркера транспортного средства
		// но не удобно и непрактично использовать visibleGeoserviceTransportList с объектами вместо идентификаторов для фильтрации стора бригад
		
		// Скрываем/отображаем маркеры траспортных средств на карте
		for (var i = 0; i < visibleGeoserviceTransportList.length; i++) {
			mapPanel.setVisibleAmbulanceMarker( visibleGeoserviceTransportList[i].id, visibleGeoserviceTransportList[i].visible );
		};	
		
		// Оставляем в сторе только те бригады, чьи транспортные средства не скрыты 
		emergencyTeamStore.filterBy(function(record){
			return !Ext.Array.contains(hiddenGeoserviceTransportList, record.get('GeoserviceTransport_id') )					
		})
		
	},
	/**
	* @public Метод предложения бригады 
	* @param CmpCallCard_record Ext.data.Model - record из стора талонов вызова
	*/
	offerEmergencyTeamToCmpCallCard: function( CmpCallCard_record ) {
		
		if ( ! (CmpCallCard_record instanceof Ext.data.Model) ) {
			return;
		}
		
		var emergencyTeamRecord = this.getEmergencyTeamViewRef().getSelectionModel().getSelection()[0];
		
		var cntr = this,
			mapPanel = this.getMapPanelRef(),
			emergencyTeamView = this.getEmergencyTeamViewRef(),
			emergencyTeamStore = this.getStore('common.DispatcherDirectWP.store.EmergencyTeamStore'),
			emergencyTeamData = Ext.Array.pluck( emergencyTeamStore.getRange(), 'data' ),
			geoserviceTransportIdList = Ext.Array.pluck( emergencyTeamData , 'GeoserviceTransport_id' ),		
			loadMask = new Ext.LoadMask(Ext.getBody(),{msg:"Просчет времени доезда..."});
			
		
			try {
				mapPanel.setCmpCallCardMarker( CmpCallCard_record , function(){

				if ( emergencyTeamRecord ) {
					if(emergencyTeamRecord.get('GeoserviceTransport_id')){
					loadMask.show();
						mapPanel.getAllAmbulancesArrivalTimeToAccident( [emergencyTeamRecord.get('GeoserviceTransport_id')] , function( data ) {
							loadMask.hide();
							if (! (data instanceof Array ) || !data.length) {
								Ext.Msg.alert('Ошибка', 'Во время получения времени доезда произошла ошибка #1' );
								return;
							}

							cntr.setCenterEmergencyTeamOnMap(emergencyTeamRecord, data[0]['durationText'] );

						});
					}
					else{
						cntr.setCenterEmergencyTeamOnMap(emergencyTeamRecord);
					}
					return;

				}
				
				Ext.Array.remove(geoserviceTransportIdList,0);
				
				// Не получаем время доезда, если бригад нет. Иначе при клике на любой вызов постоянно выводится ошибка #2.
				if (geoserviceTransportIdList.length) {
					
					mapPanel.getAllAmbulancesArrivalTimeToAccident( geoserviceTransportIdList , function( data ) {
						loadMask.hide();
						if (! (data instanceof Array)) {
							Ext.Msg.alert('Ошибка', 'Во время получения времени доезда произошла ошибка #2' );
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
						} catch (e) {
							Ext.Msg.alert('Ошибка', 'Во время получения времени доезда произошла ошибка #3' );
							return;
						}

						cntr.sortEmergencyTeamStore( function(){
							var firstRec = emergencyTeamStore.first();
							emergencyTeamView.getSelectionModel().select( firstRec );
							mapPanel.setRouteFromAmbulanceToAccident( firstRec.get('GeoserviceTransport_id') );
						}.bind(this));
						
					});
				} else {
					loadMask.hide();
				}
				
			});
		} catch (e) {
			log({offerEmergencyTeamToCmpCallCard_getAllAmbulancesArrivalTimeToAccident_e:e})
			loadMask.hide();
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
	}

});