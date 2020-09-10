Ext.define('common.InteractiveMapSMP.swInteractiveMapPanelController', {
	extend: 'SMP.swSMPDefaultController_controller',
	models: [
		'common.InteractiveMapSMP.model.CmpCallCard',
		'common.InteractiveMapSMP.model.BalloonCmpCallCard',
		'common.InteractiveMapSMP.model.EmergencyTeam'
	],
	stores: [
		'common.InteractiveMapSMP.store.CmpCallCardStore',
		'common.InteractiveMapSMP.store.EmergencyTeamStore'
	],
	requires: [
		'common.InteractiveMapSMP.lib.swCmpCallCardBalloonGrid',
		'common.InteractiveMapSMP.lib.swInteractiveMapPanel'
	],

	refs: [{
			ref: 'filtersRef',
			selector: 'swInteractiveMapWorkPlace [refId=filterToolbar]'
		},{
			ref: 'mapPanelRef',
			selector: 'swInteractiveMapWorkPlace swinteractivesmpmappanel'
		},
		{
			ref: 'ArmWindowRef',
			selector: 'swInteractiveMapWorkPlace'
		}
	],
	/**
	 * Описываем события в Ext6 стиле
	 */
	controlConfig: {
		'swInteractiveMapWorkPlace': {
			render: 'initStores',
			show: 'onWorkPlaceShow'
		},
		'swInteractiveMapWorkPlace swinteractivesmpmappanel': {
			setEmergencyTeamOnCmpCallCard: 'setEmergencyTeamOnCmpCallCard'
		},
		'swInteractiveMapWorkPlace swEmergencyTeamStatuses': {
			change: 'onEmergencyTeamStatusFilterChange'
		},
		'swInteractiveMapWorkPlace swCmpCallTypeCombo': {
			change: 'onCmpCallTypeFilterChange'
		},
		'swInteractiveMapWorkPlace swCmpCallCardStatusTypeCombo': {
			change: 'onCmpCallCardStatusTypeComboChange'
		}
	},
	/**
	 * Выбор подстанций для отображения
	 */
	onWorkPlaceShow: function() {
		Ext.getCmp('Mainviewport_Toolbar').down('button[refId=settingsBtn]').show();
		openSelectSMPStationsToControlWindow();
		console.log(this.getMapPanelRef());
	},
	/**
	 * Обработчик фильтра по статусам бригады
	 * @param combo
	 * @param newValue
	 * @param oldValue
	 */
	onEmergencyTeamStatusFilterChange: function (combo, newValue, oldValue) {
		var emergencyTeamStore = this.getStore('common.InteractiveMapSMP.store.EmergencyTeamStore');
		emergencyTeamStore.getProxy().extraParams['EmergencyTeamStatus_id'] = newValue;
		emergencyTeamStore.reload();
	},
	/**
	 * Обработчик фильтра по типам вызова
	 * @param combo
	 * @param newValue
	 * @param oldValue
	 */
	onCmpCallTypeFilterChange: function (combo, newValue, oldValue) {
		var cmpCallCardStore = this.getStore('common.InteractiveMapSMP.store.CmpCallCardStore');
		cmpCallCardStore.getProxy().extraParams['CmpCallType_id'] = newValue;
		cmpCallCardStore.reload();
	},
	/**
	 * Обработчик фильтра по статусам вызова
	 * @param combo
	 * @param newValue
	 * @param oldValue
	 */
	onCmpCallCardStatusTypeComboChange: function (combo, newValue, oldValue) {
		var cmpCallCardStore = this.getStore('common.InteractiveMapSMP.store.CmpCallCardStore');
		cmpCallCardStore.getProxy().extraParams['CmpCallCardStatusType_id'] = newValue;
		cmpCallCardStore.reload();
	},
	/**
	 * Инициализация сторов АРМа
	 */
	initStores: function() {
		this.loadFiltersStores();
		this.loadMapStateStores();
	},
	/**
	 * Инициализация сторов объектов карты: бригад, их координат и карт вызова
	 */
	loadMapStateStores: function () {
		this.initGeoserviceStore();
		this.initCmpCallCardStore();
		this.initEmergencyTeamStore();
		this.setLpuBuildingsOnMap();
	},
	/**
	 * Инициализация стора GeoserviceTransportStore: запуск интервала автозагрузки, обработка загрузки
	 */
	initGeoserviceStore: function () {
		var geoserviceTransportStore = this.getGeoserviceTransportStore(),
			emergencyTeamStore = this.getStore('common.InteractiveMapSMP.store.EmergencyTeamStore');

		geoserviceTransportStore.getExtraParamsFn = function () {
			return {
				'filtertransport_ids': JSON.stringify(
					(emergencyTeamStore.count()) ? emergencyTeamStore.collect('GeoserviceTransport_id') : [0]
				)
			};
		};
		geoserviceTransportStore.on('load',this.onGeoserviceTransportStoreLoad.bind(this));
		geoserviceTransportStore.runAutoRefresh();
	},
	/**
	 * Код статуса "Ожидание вызова"
	 * @TODO Вынести в константы
	 */
	EmergencyTeam_AcceptWait_StatusCode: 36,
	/**
	 * Назначение бригады на вызов
	 * @param EmergencyTeam_id
	 * @param CmpCallCard_id
	 */
	setEmergencyTeamOnCmpCallCard: function (EmergencyTeam_id, CmpCallCard_id) {

		var loadMask = new Ext.LoadMask(Ext.getBody(),{msg:"Сохранение данных..."});

		loadMask.show();

		Ext.Ajax.request({
			url: '/?c=CmpCallCard4E&m=setEmergencyTeamWithoutSending',
			params: {
				EmergencyTeam_id: EmergencyTeam_id,
				CmpCallCard_id: CmpCallCard_id
			},
			success: function(response, opts){
				loadMask.hide();
				var obj = Ext.decode(response.responseText);
				if (obj.success) {

					this.setEmergencyTeamStatus(EmergencyTeam_id, this.EmergencyTeam_AcceptWait_StatusCode);
					this.getStore('common.InteractiveMapSMP.store.CmpCallCardStore').reload();

				} else {
					Ext.Msg.alert('Ошибка','Во время назначения бригады произошла непредвиденная ошибка. Перезагрузите страницу и попробуйте выполнить действие заново. Если ошибка повторится, обратитесь к администратору.');
					log('/?c=CmpCallCard&m=setEmergencyTeamWithoutSending query error.');
					log( response );
				}
			}.bind(this),
			failure: function(response, opts){
				loadMask.hide();
				Ext.MessageBox.show({
					title: 'Ошибка',
					msg: 'Во время выполнения запроса произошла непредвиденная ошибка.',
					buttons:Ext.MessageBox.OK
				});
				log({response:response,opts:opts});
			}
		});
	},
	/**
	 * Установка статуса бригаде
	 * @param EmergencyTeam_id int
	 * @param EmergencyTeamStatus_Code int
	 * @param cb
	 */
	setEmergencyTeamStatus: function (EmergencyTeam_id, EmergencyTeamStatus_Code, cb) {
		var cntrl = this;
		var socket = cntrl.getArmWindowRef().socket;
		Ext.Ajax.request({
			url: '/?c=EmergencyTeam4E&m=setEmergencyTeamStatus',
			params: {
				'EmergencyTeamStatus_Code': EmergencyTeamStatus_Code,
				'EmergencyTeam_id':	EmergencyTeam_id,
				'ARMType': sw.Promed.MedStaffFactByUser.last.ARMType
			},
			callback: function(opt, success, response) {
				response_obj = Ext.decode(response.responseText, true) || {};
				if (success && response_obj.success){
					socket && socket.emit('changeEmergencyTeamStatus',
						{'EmergencyTeam_id':EmergencyTeam_id}, 'changeStatus',
						function(data){ console.log('NODE emit changeStatus'); });

					this.getStore('common.InteractiveMapSMP.store.EmergencyTeamStore').reload();
				} else {
					Ext.Msg.alert('Ошибка','Во время установки статуса бригады бригады произошла непредвиденная ошибка. Перезагрузите страницу и попробуйте выполнить действие заново. Если ошибка повторится, обратитесь к администратору.');
					log('/?c=EmergencyTeam4E&m=setEmergencyTeamStatus query error.');
					log( response );
				}
			}.bind(this)
		})
	},
	/**
	 * Обработка события загрузки стора GeoserviceTransportStore
	 * @param store
	 * @param records
	 * @param successful
	 */
	onGeoserviceTransportStoreLoad: function ( store , records, successful ) {
		if (!this.getStore('common.InteractiveMapSMP.store.EmergencyTeamStore').isLoading()) {
			this.updateEmergencyTeamMarkers();
		}
	},
	/**
	 * Интервал обновления стора CmpCallCard
	 * @private int
	 */
	_cmpCallCardStoreRefreshIntervalId: null,
	/**
	 * Инициализация стора CmpCallCard: установка фильтра по дате, загрузка, запуск интервала автозагрузки, обработка загрузки
	 */
	initCmpCallCardStore: function () {
		var store = this.getStore('common.InteractiveMapSMP.store.CmpCallCardStore');
		store.on('load', this.onCmpCallCardStoreLoad.bind(this));
		store.load({
			params: {
				begDate: Ext.Date.format(Ext.Date.add(new Date(), Ext.Date.DAY, -1), 'd.m.Y'),
				endDate: Ext.Date.format(new Date(), 'd.m.Y')
			}
		});
		this._cmpCallCardStoreRefreshIntervalId = setInterval(function () {
			store.reload()
		}, 15000);
	},
	/**
	 * Обработчик события загрузки стора CmpCallCard
	 * @param store
	 * @param records
	 * @param successful
	 * @param eOpts
	 */
	onCmpCallCardStoreLoad: function (store , records, successful, eOpts) {
		if(!records) {
			return;
		}

		this.getMapPanelRef().setMarkersByCmpCallCardList(records);
	},
	/**
	 * Интервал обновления стора EmergencyTeamStore
	 * @private int
	 */
	_emergencyTeamStoreRefreshIntervalId: null,
	/**
	 * Инициализация стора EmergencyTeamStore: загрузка, запуск интервала автозагрузки, обработка загрузки
	 */
	initEmergencyTeamStore: function () {
		var store = this.getStore('common.InteractiveMapSMP.store.EmergencyTeamStore');
		store.on('load', this.onEmergencyTeamStoreLoad.bind(this));
		store.load();
		this._emergencyTeamStoreRefreshIntervalId = setInterval(function () {
			store.reload()
		}, 15000);
	},
	/**
	 * Обработчик события загрузки стора EmergencyTeamStore
	 * @param store
	 * @param records
	 * @param successful
	 * @param eOpts
	 */
	onEmergencyTeamStoreLoad: function (store , records, successful, eOpts) {

		if (!this.getGeoserviceTransportStore().isLoading())
			this.updateEmergencyTeamMarkers();

	},
	/**
	 * Инициализация обновления маркеров бригад на карте
	 */
	updateEmergencyTeamMarkers: function () {
		var geoserviceTransportStore = this.getGeoserviceTransportStore(),
			emergencyTeamStore = this.getStore('common.InteractiveMapSMP.store.EmergencyTeamStore'),
			emergencyTeamList = [];

		emergencyTeamStore.each(function (emergencyTeamRec) {
			geoserviceItemData = geoserviceTransportStore.findRecord('GeoserviceTransport_id',
				emergencyTeamRec.get('GeoserviceTransport_id'));

			if (!geoserviceItemData) {
				return;
			}

			var emergencyTeamData = emergencyTeamRec.getData();
			emergencyTeamData.point = [ geoserviceItemData.get('lat'), geoserviceItemData.get('lng') ];
			emergencyTeamList.push(emergencyTeamData);

		});


		this.getMapPanelRef().setMarkersByEmergencyTeamList(emergencyTeamList);

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
	/**
	 * Инициализация и загрузка сторов фильтров
	 */
	loadFiltersStores: function() {
		this.loadFiltersLocalCombos();
		this.loadFiltersMongoCombos();
	},
	/**
	 * Загрузка хранилищ (кроме монго) для комбобоксов фильтров
	 */
	loadFiltersLocalCombos: function(){
		var localCombos = this.getFiltersRef().query('[cls=localCombo]');
		for (var i = 0; i < localCombos.length; i++) {
			var localCombo = localCombos[i];
			localCombo.getStore().load();
		}
	},
	/**
	 * Загрузка монго хранилищ для комбобоксов фильтров
	 */
	loadFiltersMongoCombos:function(){
		loadLocalMongoCombos( this.getFiltersRef().query('[cls=localComboMongo]') );
	},
	/**
	 * Инициализация контроллера
	 */
	init: function() {
		this.control(this._getControls());
	},
	/**
	 * @private
	 * Преобразует конфиг из вида Ext6 в вид Ext4 для метода инициализации контроллера
	 */
	_getControls: function() {
		var controls = {};
		for (var selector in this.controlConfig){
			if (this.controlConfig.hasOwnProperty(selector)) {

				controls[selector] = {};
				for (var event in this.controlConfig[selector]) {
					if (this.controlConfig[selector].hasOwnProperty(event)) {
						controls[selector][event] = Ext.isFunction(this[this.controlConfig[selector][event]])? this[this.controlConfig[selector][event]] : Ext.emptyFn;
					}
				}

			}
		}
		return controls;
	}
});