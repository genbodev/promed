/**
 * @var Object ymaps
 */
Ext.define('common.InteractiveMapSMP.lib.swInteractiveMapPanel', {
	extend: 'sw.Smp.MapPanel',
	alias: 'widget.swinteractivesmpmappanel',
	xtype: 'swsmpinteractivemappanel',
	refId: 'swMapPanel',
	curMapType: 'yandex',
	defMapType: 'yandex',
	typeList: ['yandex'],
	/**
	 *
	 * @param EmergencyTeamList Array
	 * @param EmergencyTeamList[] Object
	 * @param EmergencyTeamList[].EmergencyTeam_id int
	 * @param EmergencyTeamList[].GeoserviceTransport_id int
	 * @param EmergencyTeamList[].point int
	 */
	setMarkersByEmergencyTeamList: function (EmergencyTeamList) {
		var map = this.getCurrentMap(),
			markerIds = [],
			ambulanceMarker;

		map.EmergencyTeamList = EmergencyTeamList;

		for (var i = 0; i<EmergencyTeamList.length; i++) {
			markerIds.push(EmergencyTeamList[i].EmergencyTeam_id);
			map.setEmergencyTeamMarkerOnMap(EmergencyTeamList[i]);
		}
		for (var key in map.ambulanceMarkerList) {
			ambulanceMarker = map.ambulanceMarkerList[key];
			if (markerIds.indexOf(ambulanceMarker.car_marker.EmergencyTeam.EmergencyTeam_id) === -1) {
				map.deleteEmergencyTeamMarker(ambulanceMarker)
			}
		}
	},
	/**
	 *
	 * @param records Array
	 * @param records[] CmpCallCard
	 */
	setMarkersByCmpCallCardList: function ( records ) {
		var map = this.getCurrentMap(),
			markerIds = [],
			accidentMarker;

		for (var i = 0; i<records.length; i++) {
			markerIds.push(records[i].get('CmpCallCard_id'));
			map.setCmpCallCardMarkerOnMap(records[i]);
		}
		for (var key in map.accidentMarkerList) {
			accidentMarker = map.accidentMarkerList[key];

			if (markerIds.indexOf(accidentMarker.accidentMarker.CmpCallCard.CmpCallCard_id) === -1) {
				map.deleteCmpCallCardMarker(accidentMarker)
			}
		}
		/**
		 * Приходится перезапускать фильтр предлагаемых вызовов вручную, т.к. если весить на фильтрацию на datachange,
		 * То событие datachange начинает выполняться рекурсивно
		 */
		map.ProposedCmpCallCardStore.filter();
	},
	/**
	 * @returns {String} Карта отображаемая у пользователя по умолчанию
	 */
	getDefaultUserMap: function(){
		return this.curMapType
	},
	items: [
		{
			xtype: 'swsmpinteractiveyandexmappanel',
			title: 'Yandex Maps',
			type: 'yandex',
			// hidden: this.getDefaultUserMap()=='yandex' ? false : true,
			flex: 1,
			listeners: {
				'setEmergencyTeamOnCmpCallCard': function (EmergencyTeam_id, CmpCallCard_id) {
					this.up('swinteractivesmpmappanel').fireEvent('setEmergencyTeamOnCmpCallCard', EmergencyTeam_id, CmpCallCard_id)
				}
			}
		}
	],
	initComponent: function () {
		this.addEvents('setEmergencyTeamOnCmpCallCard');
		this.callParent(arguments);
	}
});
/**
 *
 */
Ext.define('sw.Smp.InteractiveYandexMapPanel',{
	extend: 'sw.Smp.YandexMapPanel',
	/**
	 * Префикс ключа вызова в объекте маркеров вызовов
	 * используем объект вместо массива
	 */
	_accidentPrefix: 'accident_',
	/**
	 * объект маркеров вызовов
	 * используем объект вместо массива
	 */
	accidentMarkerList: {},
	alias: 'widget.swsmpinteractiveyandexmappanel',
	/**
	 * Список идентификаторов статусов вызовов, с которыми вызовы предлагаются свободным бригадам
	 * @TODO Перевести на константы
	 * @TODO Перевести на коды вместо идентификаторов
	 */
	proposedCmpCallCardStatusIds: [
		1 // Передано
		,3 // Возврат
		,7 // Передано диспетчеру подстанции
		,8 // Возвращено диспетчером подстанции
		,20 // Передано из 112
		,21 // Решение диспетчера отправляющей части
	],
	/**
	 * Стор предлагаемых свободным бригадам вызовов
	 * @property {Ext.data.Store} ProposedCmpCallCardStore
	 */
	ProposedCmpCallCardStore: {},
	/**
	 * Грид с предлагаемыми свободным бригадам вызовами
	 * отрисовывается в балоне свлободной бригады
	 */
	CmpCallCardBalloonGrid: null,
	/**
	 * Префикс для id div-тега, который располагается в балоне свободной бригады, и в котором отрисовывается CmpCallCardBalloonGrid
	 */
	_balloonDivIdPrefix: 'AmbulanceBalloon_',
	/**
	 * @TODO Придумать как дёрнуть
	 */
	// src: 'https://enterprise.yandex.ru/2.1/?load=package.standard&lang=ru_RU&apikey=fc553d12-9e19-44c8-9be6-5e3787efc1f3',
	initComponent: function(){
		this.addEvents('setEmergencyTeamOnCmpCallCard');
		this.initProposedCmpCallCardStore();
		this.callParent(arguments);
	},
	/**
	 * Инициализация стора вызовов, предлагаемых свободным бригадам
	 */
	initProposedCmpCallCardStore: function () {
		var map = this;
		this.ProposedCmpCallCardStore = Ext.create('Ext.data.Store',{
			autoLoad: false,
			stripeRows: true,
			proxy: {type: 'memory'},
			model: 'common.InteractiveMapSMP.model.BalloonCmpCallCard',
			/**
			 * В балуне необходимо показывать только вызовы на которые ещё не назначена бригада
			 */
			filters: [
				function(item) {
					return map.proposedCmpCallCardStatusIds.indexOf(item.get('CmpCallCardStatusType_id')) !== -1;
				}
			],
			/**
			 * Сортируем по срочности
			 */
			sorters: [
				{
					sorterFn: function(rec1, rec2){
						var CmpCallCard_Urgency1 = rec1.get('CmpCallCard_Urgency') === '-' ? 99 : rec1.get('CmpCallCard_Urgency');
						var CmpCallCard_Urgency2 = rec2.get('CmpCallCard_Urgency') === '-' ? 99 : rec2.get('CmpCallCard_Urgency');

						if (CmpCallCard_Urgency1 === CmpCallCard_Urgency2) {
							return 0;
						}

						return CmpCallCard_Urgency1 < CmpCallCard_Urgency2 ? -1 : 1;
					}
				}
			]
		});
	},
	/**
	 * Воссоздаем грид каждый раз, когда он нужен в балуне, т.к. после обновления сторов при закрытом балуне
	 * по неопределённой причине слетает вся event-модель, грид отрисовывается, но перестает реагировать на любые действия пользователя
	 *
	 * @returns {common.InteractiveMapSMP.lib.swCmpCallCardBalloonGrid}
	 */
	getCmpCallCardBalloonGrid: function () {

		var map = this;

		if (this.CmpCallCardBalloonGrid) {
			this.CmpCallCardBalloonGrid.destroy()
		}

		this.CmpCallCardBalloonGrid = Ext.create('common.InteractiveMapSMP.lib.swCmpCallCardBalloonGrid',{
			store: this.ProposedCmpCallCardStore,
			height: 120,
			listeners: {
				onBuildRoute: function (rec) {
					map._buildRoute(this.currentAmbulancePlacemark.geometry.getCoordinates(), rec.get('point'));
				},
				onSetCmpCallCard: function (rec) {
					var grid = this;
					Ext.MessageBox.confirm('Назначение бригады на вызов',
						'Назначить бригаду №' + this.currentAmbulancePlacemark.EmergencyTeam.EmergencyTeam_Num +
						' на вызов №' + rec.get('CmpCallCard_Numv'),
						function (btn){
							if ( btn === 'yes' ){
								grid.currentAmbulancePlacemark.balloon.close();
								map._setEmergencyTeamOnCmpCallCard(
									grid.currentAmbulancePlacemark.EmergencyTeam.EmergencyTeam_id,
									rec.get('CmpCallCard_id')
								);
							}
						});
				}
			}
		});

		return this.CmpCallCardBalloonGrid;
	},
	/**
	 * Объект текущего отрисованного маршрута (ymaps.Route)
	 * хранится, чтобы удалять его из карты в событии закрытия балона
	 * @private
	 */
	_currentRoute: null,
	/**
	 * Метод отрисовки маршрута на карте между точкой вызова и местоположением бригады
	 * @param ambulancePoint {Array}
	 * @param accidentPoint {Array}
	 * @param callback {Function}
	 * @private
	 */
	_buildRoute: function(ambulancePoint, accidentPoint, callback) {
		var map = this;
		ymaps.route([
			ambulancePoint, accidentPoint
		],{
			avoidTrafficJams: true,
			mapStateAutoApply: true
		}).then(function (route) {

			if (map._currentRoute){
				map.getMap().geoObjects.remove(map._currentRoute);
			}
			route.getWayPoints().removeAll();
			map._currentRoute = route;
			map.getMap().geoObjects.add(route);
			Ext.isFunction(callback) && callback(route);
		})
	},
	/**
	 * Проброс события назначения бригады на вызов из грида предлагаемых вызовов в контроллер АРМа
	 * @param EmergencyTeam_id
	 * @param CmpCallCard_id
	 * @private
	 */
	_setEmergencyTeamOnCmpCallCard: function (EmergencyTeam_id, CmpCallCard_id) {
		this.fireEvent('setEmergencyTeamOnCmpCallCard', EmergencyTeam_id, CmpCallCard_id)
	},
	/**
	 * Установка маркера вызова на карту
	 * @param CmpCallCard {common.InteractiveMapSMP.model.CmpCallCard}
	 * @returns {*}
	 */
	setCmpCallCardMarkerOnMap: function( CmpCallCard ) {
		return (!this.accidentMarkerList[this._accidentPrefix+CmpCallCard.get('CmpCallCard_id')]) ?
			this.addCmpCallCardMarkerOnMap(CmpCallCard) :
			this.updateCmpCallCardMarkerOnMap(CmpCallCard);
	},
	/**
	 * Получение координат вызова
	 * @param CmpCallCard {common.InteractiveMapSMP.model.CmpCallCard}
	 * @param callback
	 * @returns {*}
	 * @private
	 */
	_getCmpCallCardMarkerPoint: function (CmpCallCard, callback) {
		var map = this,
			lat = CmpCallCard.get('UnAdress_lat'),
			lng = CmpCallCard.get('UnAdress_lng');

		if (lat && lng) {
			return callback([lat,  lng]);
		}

		this._getAddressForGeocodeByCmpCallCardRecord(CmpCallCard, function (address) {
			map.geocode( address , function( point ) {
				if ( !(point instanceof Array) || !point[0] || !point[1])
					return;
				callback( point, address );
			});
		}.bind(this));



	},
	/**
	 * Инициализация параметров для маркера вызова
	 * @param CmpCallCard {common.InteractiveMapSMP.model.CmpCallCard}
	 * @returns {{imageHref: string, imageSize: number[], imageOffset: *[], baloonContent: null, listeners: {balloonopen: listeners.balloonopen}, additionalInfo: {CmpCallCard, type: string}}}
	 * @private
	 */
	_initCmpCallCardMarkerData: function (CmpCallCard) {
		var map = this;

		return {
			imageHref: '/img/googlemap/firstaid.png',
			imageSize: [32, 37],
			imageOffset: [-16, -37],
			baloonContent: this._getAccidentBalloonContent(CmpCallCard),
			listeners: {
				balloonopen: function (e) {
					var placemark = this,
						EmergencyTeamMarker = (placemark.CmpCallCard.EmergencyTeam_id) ?
							map.ambulanceMarkerList[map._ambulancePrefix + this.CmpCallCard.EmergencyTeam_id] :
							null,
						CmpCallCardMarker = map.accidentMarkerList[map._accidentPrefix+placemark.CmpCallCard.CmpCallCard_id];

					if  (!EmergencyTeamMarker) {
						return true;
					}

					var destTimeTitle = "Время доезда: ",
						currentBalloon = CmpCallCardMarker.data.baloonContent,
						balloonContent = currentBalloon + destTimeTitle + "Загрузка...";

					placemark.properties.set("balloonContent", balloonContent);

					ymaps.route([
							placemark.geometry.getCoordinates(),
							EmergencyTeamMarker.car_marker.geometry.getCoordinates()
						],
						{
							avoidTrafficJams: true
						}
					).then(function (route) {
						balloonContent = currentBalloon + destTimeTitle + route.getHumanJamsTime();
						placemark.properties.set("balloonContent", balloonContent);
					});

				}

			},
			additionalInfo: {
				CmpCallCard: CmpCallCard.getData(),
				type: 'callPoint'
			}
		};
	},
	/**
	 * Динамическое получения данных для балуна вызова
	 * @param CmpCallCard {common.InteractiveMapSMP.model.CmpCallCard}
	 * @returns {string}
	 * @private
	 */
	_getAccidentBalloonContent: function (CmpCallCard) {
		var CmpCallCardData = CmpCallCard.getData();
		var data = {
			"Время вызова": Ext.Date.format(CmpCallCardData.CmpCallCard_prmDate, "H:i:s d.m.Y"),
			"ФИО": CmpCallCardData.Person_FIO || "",
			"Возраст": CmpCallCardData.personAgeText || "",
			"Повод": CmpCallCardData.CmpReason_Name || "",
			"Статус": CmpCallCardData.CmpCallCardEventType_Name || ""
		};

		var balloonContent = "";
		for (var key in data) {
			balloonContent += key + ": " + data[key] + "</br>";
		}

		return balloonContent;
	},
	/**
	 * Добавление маркера вызова на карту
	 * @param CmpCallCard {common.InteractiveMapSMP.model.CmpCallCard}
	 */
	addCmpCallCardMarkerOnMap: function( CmpCallCard ) {

		var data = this._initCmpCallCardMarkerData(CmpCallCard);

		var CmpCallCardMapInstance = this.ProposedCmpCallCardStore.add(CmpCallCard.getData())[0];

		this._getCmpCallCardMarkerPoint(CmpCallCard, function (point, address) {
			data.point = point;
			data.address = address;

			CmpCallCardMapInstance.set('point', point);

			this.accidentMarkerList[this._accidentPrefix+CmpCallCard.get('CmpCallCard_id')] = {
				data: data,
				accidentMarker: this.addMarker( data )
			}
		}.bind(this));
	},
	/**
	 * Обновление маркера вызова на карте
	 * @param CmpCallCard {common.InteractiveMapSMP.model.CmpCallCard}
	 * @returns {boolean}
	 */
	updateCmpCallCardMarkerOnMap: function( CmpCallCard ) {

		var CCCData = CmpCallCard.getData();
		var marker = this.accidentMarkerList[this._accidentPrefix+CCCData.CmpCallCard_id];

		if (!marker) {
			return false;
		}

		marker.accidentMarker.CmpCallCard = CCCData;
		this._updateCmpCallCardStoreModel(CCCData);
		marker.accidentMarker.properties.set("balloonContent", this._getAccidentBalloonContent(CmpCallCard));

		if (CCCData.UnAdress_lat && CCCData.UnAdress_lng) {
			this._updateCmpCallCardMarkerByUnformalizedAddressPoint([
				CCCData.UnAdress_lat, CCCData.UnAdress_lng
			]);
		} else {
			this._updateCmpCallCardMarkerByAddress(CmpCallCard, marker);
		}

	},
	/**
	 * Актуализация информации о вызове в сторе предлагаемых для назначения вызовов
	 * @param CmpCallCardData
	 * @private
	 */
	_updateCmpCallCardStoreModel: function(CmpCallCardData) {
		var CmpCallCardMapInstance = this.ProposedCmpCallCardStore.findRecord('CmpCallCard_id', CmpCallCardData.CmpCallCard_id);
		if (!CmpCallCardMapInstance) {
			return;
		}

		for (var key in CmpCallCardData) {
			if (CmpCallCardData.hasOwnProperty(key)) {
				CmpCallCardMapInstance.set(key, CmpCallCardData[key]);
			}
		}

	},
	/**
	 * Обновление местоположения маркера вызова по адресу вызова
	 * @param CmpCallCard
	 * @param marker
	 * @private
	 */
	_updateCmpCallCardMarkerByAddress: function (CmpCallCard, marker) {
		var map = this;
		this._getAddressForGeocodeByCmpCallCardRecord(CmpCallCard, function (address) {
			if (marker.data.address === address) {
				return;
			}

			marker.data.address = address;
			this.geocode(address, function (point) {
				if (Array.isArray(point))
					return;

				marker.data.point = point;
				marker.accidentMarker.geometry.setCoordinates(point);
				map._updateCmpCallCardStorePoint(marker.accidentMarker.CmpCallCard.CmpCallCard_id, point);
			})

		}.bind(this));
	},
	/**
	 * Обновление местоположения маркера вызова по точке из справочника неформализованных адресов
	 * @param UAPoint
	 * @param marker
	 * @private
	 */
	_updateCmpCallCardMarkerByUnformalizedAddressPoint: function(UAPoint, marker) {
		if ( marker.data.point[0] !== UAPoint[0] || marker.data.point[1] !== UAPoint[1] ) {
			marker.data.point = UAPoint;
			marker.data.address = undefined;
			marker.accidentMarker.geometry.setCoordinates(UAPoint);
			this._updateCmpCallCardStorePoint(marker.accidentMarker.CmpCallCard.CmpCallCard_id, UAPoint);
		}
	},
	/**
	 * Обновление координат вызова в сторе предлагаемых для назначения вызовов
	 * @param CmpCallCard_id
	 * @param point
	 * @private
	 */
	_updateCmpCallCardStorePoint: function (CmpCallCard_id, point) {

		var CmpCallCardMapInstance = this.ProposedCmpCallCardStore.findRecord('CmpCallCard_id', CmpCallCard_id);
		if (CmpCallCardMapInstance) {
			CmpCallCardMapInstance.set('point', point);
		}

	},
	/**
	 * Удаление маркера вызова с карты
	 * @param marker {Object}
	 * @param marker.accidentMarker {ymaps.Placemark}
	 */
	deleteCmpCallCardMarker: function(marker) {
		var CmpCallCard_id = marker.accidentMarker.CmpCallCard.CmpCallCard_id;
		delete this.accidentMarkerList[this._accidentPrefix + CmpCallCard_id];
		this.removeMarker( marker.accidentMarker );
		var model = this.ProposedCmpCallCardStore.findRecord('CmpCallCard_id', CmpCallCard_id);
		if (model) {
			this.ProposedCmpCallCardStore.remove(model);
		}
	},
	/**
	 * Установка маркера бригады на карту
	 * @param data Object дата EmergencyTeam
	 * @param data.EmergencyTeam_id int
	 * @param data.GeoserviceTransport_id int
	 * @param data.point int
	 * @returns {*}
	 */
	setEmergencyTeamMarkerOnMap: function (data) {
		return (!this.ambulanceMarkerList[this._ambulancePrefix+data.EmergencyTeam_id]) ?
			this.addEmergencyTeamMarker(data) :
			this.updateEmergencyTeamMarker(data);
	},
	/**
	 * Добавление маркера бригады на карту
	 * @param data
	 * @param data.EmergencyTeam_id int
	 * @param data.GeoserviceTransport_id int
	 * @param data.EmergencyTeam_Num int
	 * @param data.EmergencyTeamStatus_Name string
	 * @param data.EmergencyTeamStatus_Code int
	 * @param data.point int
	 * @returns {*}
	 */
	addEmergencyTeamMarker: function (data) {

		var map = this;

		var markerData = {
			imageHref: this.getAmbulanceMarkerUrl({statusCode: data.EmergencyTeamStatus_Code}),
			imageSize:  [37, 26],
			imageOffset:  [0, 0],
			point: data.point,
			baloonContent: this._getAmbulanceBalloonContent(data),
			additionalInfo: {
				EmergencyTeam: data,
				type: 'ambulance'
			},
			listeners: {
				balloonclose: function (e) {
					map.clearDeliveranceTime();
					map.clearRoutes();
				},
				balloonopen: function (e) {
					var placemark = this;

					emergencyTeamData = placemark.EmergencyTeam;
					placemark.properties.set("balloonContent", map._getAmbulanceBalloonContent(emergencyTeamData));

					var _buildRoute = function (ambulancePoint, accidentPoint) {
						map._buildRoute(ambulancePoint, accidentPoint, function (route) {
							placemark.properties.set( "balloonContent",
								placemark.properties.get("balloonContent") +
								"Время доезда: " + route.getHumanJamsTime()
							)
						})
					};

					if (placemark.EmergencyTeam.CmpCallCard_id) {
						placemark.balloon._captor.getBalloon().options.set('minHeight',0);
						placemark.balloon._captor.getBalloon().options.set('minWidth', 0);
						var ambulancePoint = placemark.geometry.getCoordinates(),
							accidentPoint;

						if (emergencyTeamData.EmergencyTeamStatus_Code === '3') {
							if (emergencyTeamData.LpuHid_PAddress) {
								map.geocode( emergencyTeamData.LpuHid_PAddress , function( point ) {
									if ( !(point instanceof Array) || !point[0] || !point[1])
										return;
									_buildRoute( ambulancePoint, point );
								});
							} else {}


						} else {
							var accidentPlacemark = map.accidentMarkerList[map._accidentPrefix + placemark.EmergencyTeam.CmpCallCard_id];
							accidentPoint = accidentPlacemark ? accidentPlacemark.accidentMarker.geometry.getCoordinates() : null;
							accidentPoint && _buildRoute(ambulancePoint, accidentPoint);
						}



					} else {
						// Предложение вызовов
						placemark.balloon._captor.getBalloon().options.set('minHeight', 170);
						placemark.balloon._captor.getBalloon().options.set('minWidth', 320);
						var cmpCallCardBalloonGrid = map.getCmpCallCardBalloonGrid();
						cmpCallCardBalloonGrid.currentAmbulancePlacemark = placemark;
						cmpCallCardBalloonGrid.render(map._balloonDivIdPrefix + emergencyTeamData.EmergencyTeam_id);
						map.getDeliveranceTime(placemark.geometry.getCoordinates());
					}

				}
			}
		};

		this.ambulanceMarkerList[this._ambulancePrefix + data.EmergencyTeam_id] = {
			car_marker: this.addMarker(markerData),
			data: markerData
		};
	},
	/**
	 * Удаление всех отрисованных(ого) маршрутов с карты
	 */
	clearRoutes: function () {
		if (this._currentRoute){
			this.getMap().geoObjects.remove(this._currentRoute);
		}
	},
	/**
	 * Очистка информации о времени доезда в сторе предлагаемых для назначения вызовов
	 */
	clearDeliveranceTime: function () {
		this.ProposedCmpCallCardStore.each(function (record) {
			record.set('DeliveranceTime', null);
		})
	},
	/**
	 * Получение времени доезда от точки до каждого вызова в сторе предлагаемых для назначения вызовов
	 * @param point
	 */
	getDeliveranceTime: function(point) {
		this.ProposedCmpCallCardStore.each(function (record) {

			ymaps.route([
					point,
					record.get('point')
				],
				{
					avoidTrafficJams: true
				}
			).then(function (route) {
				record.set('DeliveranceTime', route.getHumanJamsTime());
				record.commit();
			});
		});
	},
	/**
	 * Динамическое получение данных для балуна бригады
	 * @param EmergencyTeamData
	 * @returns {string}
	 * @private
	 */
	_getAmbulanceBalloonContent: function (EmergencyTeamData) {

		var data = {
			num: "№ " + (EmergencyTeamData.EmergencyTeam_Num || "-"),
			profile: EmergencyTeamData.EmergencyTeamSpec_Name || "без профиля",
			status: EmergencyTeamData.EmergencyTeamStatus_Name || "Без статуса",
			statusTime: (EmergencyTeamData.lastChangedStatusTime || "0") + " мин."
		};

		return [
			data.num + ' ' + data.profile,
			data.status + ': ' + data.statusTime,
			'<div id="'+this._balloonDivIdPrefix + EmergencyTeamData.EmergencyTeam_id+'"></div>'
		].join('</br>')

	},
	/**
	 * Обновление маркера бриагады на карте
	 * @param data
	 * @param data.EmergencyTeam_id int
	 * @param data.GeoserviceTransport_id int
	 * @param data.EmergencyTeam_Num int
	 * @param data.EmergencyTeamStatus_Name string
	 * @param data.EmergencyTeamStatus_Code int
	 * @param data.point Array[2]
	 * @returns {*}
	 */
	updateEmergencyTeamMarker: function (data) {
		var ambulanceMarker = this.ambulanceMarkerList[this._ambulancePrefix + data.EmergencyTeam_id];
		if (!ambulanceMarker)
			return;

		var markerData = ambulanceMarker.car_marker.EmergencyTeam;

		if (data.EmergencyTeamStatus_Code != markerData.EmergencyTeamStatus_Code) {
			ambulanceMarker.car_marker.options.set('iconImageHref', this.getAmbulanceMarkerUrl({statusCode: data.EmergencyTeamStatus_Code}));
		}

		if ((data.point[0] != markerData.point[0]) || (data.point[1] != markerData.point[1])) {
			ambulanceMarker.car_marker.geometry.setCoordinates(data.point);
		}

		ambulanceMarker.car_marker.EmergencyTeam = data;

	},
	/**
	 * Удаление маркера бригады с карты
	 * @param marker {Object}
	 * @param marker.car_marker {ymaps.Placemark}
	 */
	deleteEmergencyTeamMarker: function (marker) {
		delete this.ambulanceMarkerList[this._ambulancePrefix+marker.car_marker.EmergencyTeam.EmergencyTeam_id];
		this.removeMarker(marker.car_marker);
	}
});
