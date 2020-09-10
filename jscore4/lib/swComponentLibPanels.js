//Ext.ns('Ext.ux');

/**
 * @var Object ymaps
 * @var Object OpenLayers
 */

/**
 * Класс sw.BaseMapPanel
 * Базовый класс карт. Все карты должны наследоваться от него.
 * @todo Подумать как реализовать абстрактные классы
  */
Ext.define('sw.BaseMapPanel',{

	extend: 'Ext.panel.Panel',
	
	map: null,
	
	type: null,
	
	_ambulanceMarkeUrl: "/img/icons/ambulance32.png",
	
	loadScript: function( src, callback ) {
		var script = document.createElement('script');
		var appendTo = document.getElementsByTagName('head')[0];
		// Callback
		if ( script.readyState && !script.onload ) {
			// IE, Opera
			script.onreadystatechange = function(){
				if ( script.readyState == "loaded" || script.readyState == "complete" ) {
					script.onreadystatechange = null;
					if ( typeof callback == 'function' ) {
						callback();
					}
				}
			}
		} else if ( typeof callback == 'function' ) {
			// Rest
			script.onload = callback;
		}
		script.src = src;
		appendTo.appendChild( script );
	},
	
	/**
	 * @abstract Загружает карту
	 */
	loadMap: function( callback ){
		if ( isDebug() )
			log('sw.BaseMapPanel.loadMap abstract function');
	},

	/**
	 * Возвращает объект карты
	 */ 
	getMap: function(){
		return this.map;
	},
	
	/**
	 * Устанавливает тип (Тип чего? Везде этот метод был но нигде не использовался)
	 */
	setType: function( type ){
		this.type = type;
	},
	
	/**
	 * Возвращает тип (Тип чего? Везде этот метод был но нигде не использовался)
	 */
	getType: function(){
		return this.type;
	},
	
	getAmbulanceMarkerUrl: function(marker){

		var url = this._ambulanceMarkeUrl;

		if(marker.statusCode){
			switch (parseInt(marker.statusCode)){
				case 1:
				{
					url = '/img/icons/mapMarkers/ambulance-markers/asset-39.png';
					break;
	}
				case 2:
				{
					url = '/img/icons/mapMarkers/ambulance-markers/asset-40.png';
					break;
				}
				case 3:
				{
					url = '/img/icons/mapMarkers/ambulance-markers/asset-41.png';
					break;
				}
				case 4:
				{
					url = '/img/icons/mapMarkers/ambulance-markers/asset-52.png';
					break;
				}
				case 7:
				case 10:
				case 37:
				case 38:
				case 41:
				case 43:
				{
					url = '/img/icons/mapMarkers/ambulance-markers/asset-48.png';
					break;
				}
				case 8:
				case 44:
				case 45:
				{
					url = '/img/icons/mapMarkers/ambulance-markers/asset-44.png';
					break;
				}
				case 9:
				case 14:
				{
					url = '/img/icons/mapMarkers/ambulance-markers/asset-43.png';
					break;
				}
				case 11:
				{
					url = '/img/icons/mapMarkers/ambulance-markers/asset-45.png';
					break;
				}
				case 5:
				case 13:
				case 47:
				{
					url = '/img/icons/mapMarkers/ambulance-markers/asset-37.png';
					break;
				}
				case 17:
				{
					url = '/img/icons/mapMarkers/ambulance-markers/asset-42.png';
					break;
				}
				case 19:
				{
					url = '/img/icons/mapMarkers/ambulance-markers/asset-49.png';
					break;
				}			
				case 36:
				{
					url = '/img/icons/mapMarkers/ambulance-markers/asset-51.png';
					break;
				}
				case 48:
				{
					url = '/img/icons/mapMarkers/ambulance-markers/asset-38.png';
					break;
				}
				case 50:
				{
					url = '/img/icons/mapMarkers/ambulance-markers/asset-47.png';
					break;
				}
				default : {
					url = this._ambulanceMarkeUrl;
					break;
				}
			}
		}

		return url;
	}
});

Ext.define('sw.GMapPanel',{
	extend: 'sw.BaseMapPanel',
	//id: 'swGoogleMapPanel',
	alias: 'widget.swgooglemappanel',
	src: 'http://maps.google.com/maps/api/js?libraries=geometry&sensor=false',		
	layout: 'fit',
	infoWindow:{},
	googleMarkers: [],
	currentMarker: null,
	isRendered: false,
	mapOptions: {},
	directionsDisplay: null,
	directionsService: null,
	loadMap: function( callback ){
		Ext.Loader.loadScriptFile('https://www.google.com/jsapi',function(){
			google.load("maps", "3", {
				// other_params:"sensor=false&language=ru",
				other_params: "language=ru&key="+getGlobalOptions().google_api_key,
				callback : function(){
					var mainPanel = this.up('panel[refId=swMapPanel]'),
						mainConfig = mainPanel.initialConfig;
					this.mapOptions = this.mapOptions || {};
					
					function loadMapWithOpts(coords, pan){
						Ext.applyIf(pan.mapOptions, {
								center: new google.maps.LatLng(coords[0], coords[1]),//Россия, Пермь
								zoom: 12,
								disableDoubleClickZoom: true,
								scaleControl: true,
								mapTypeId: google.maps.MapTypeId.ROADMAP // HYBRID, ROADMAP, SATELLITE, TERRAIN
							});

							pan.directionsService = new google.maps.DirectionsService();
							pan.directionsDisplay = new google.maps.DirectionsRenderer({
								suppressMarkers:true
							});
							pan.googleMarkers = new Array();
							
							//бывают случаи когда функционал гугла нужен без отображения карты
							//а дом элемент нужен для инициализации
							(pan.rendered)?(pan.map = new google.maps.Map(pan.body.dom, pan.mapOptions)):(pan.map = new google.maps.Map(document.body.appendChild(document.createElement('div')), pan.mapOptions));
							pan.directionsDisplay.setMap(pan.getMap());
							pan.isRendered = true;
							
							if ( mainPanel.showTraffic ) {
								pan.map.trafficLayer = new google.maps.TrafficLayer();
								pan.map.trafficLayer.setMap(pan.map);
							}
							if ( typeof callback == 'function' ){
								callback();
							}

							if (mainConfig.addMarkByClick) {
								google.maps.event.addListener(pan.map, 'dblclick', pan.doClick.bind(pan));
							}
					};
					
					if(mainPanel.currentPosition==null){
						this.geocode(getGlobalOptions().region.name, function(coords){
							loadMapWithOpts(coords, this);
						}.bind(this));
					}
					else{
						loadMapWithOpts(mainPanel.currentPosition, this);
					}

				}.bind(this)
			});
		}.bind(this),Ext.emptyFn,null,false);
	},
	
	getDirectionsDisplay: function(){
		
		return this.directionsDisplay || (this.directionsDisplay = new google.maps.DirectionsRenderer({
			suppressMarkers: true
		}));
	},
	
	getDirectionsService : function(){
		
		return this.directionsService || (this.directionsService = new google.maps.DirectionsService());
	},
	
	doClick: function (event) {
		var mainPanel = this.up('panel[refId=swMapPanel]'),
			mainConfig = mainPanel.initialConfig;
		/*
		var marker = [{
			point:[event.latLng.lat(),event.latLng.lng()],
			baloonContent:'Возможное местоположение',
			imageHref: '/img/googlemap/firstaid.png',
			imageSize: [30,35],
			imageOffset: [-16,-37],
			additionalInfo: {type:'placementCursor'},
			center: true
		}]

		mainPanel.setMarkers(marker);*/
		mainPanel.fireEvent('mapClick', {point:[event.latLng.lat(),event.latLng.lng()], pixel: [event.pixel.x, event.pixel.y]});
		/*
		if (this.currentMarker) {
			this.currentMarker.setMap(null);
		}
		
		this.addMarker({point:[event.latLng.lat(),event.latLng.lng()], imageHref: '/img/googlemap/firstaid.png', title:'Новый адрес'});
		*/
		if (typeof this.clickCallback == 'function') {
			this.clickCallback(event);
		}
	},
	
	setInfoWindow: function(infWin) {
		this.infoWindow = infWin;
	},
	getInfoWindow: function() {
		return this.infoWindow;	
	},
	getCenter : function(){    
		return this.getMap().getCenter();  
	},
	getCenterLatLng : function(){
		var ll = this.getCenter();
		return {lat: ll.lat(), lng: ll.lng()};
	},
//	addMarkers: function(markers) {
//		if (Ext.isArray(markers)){
//			for (var i = 0; i < markers.length; i++) {
//				var mkr_point = new google.maps.LatLng(markers[i].lat,markers[i].lng);
//				var iwo = (markers[i].infoWindowOptions) ? markers[i].infoWindowOptions : null;
//				var icon = markers[i].icon ? markers[i].icon : this.iconVan;
//				this.googleMarkers.push(this.addMarker(mkr_point,markers[i].marker,false,markers[i].setCenter, markers[i].listeners, iwo, icon ));
//			}
//		}	
//	},
	addMarker:  function(marker) {
		var mainPanel = this.up('panel[refId=swMapPanel]'),
			mainConfig = mainPanel.initialConfig;
		//function(point, marker, clear, center, listeners, infoWindowOptions,iconDirection){
		if ( (!marker.point&&!Ext.isArray(marker.point)&&!marker.point.length!=2)&&(!marker.address) || typeof google=='undefined') {			
			return false;
		}
		
		var mkr_point = new google.maps.LatLng(marker.point[0]/*.lat*/,marker.point[1]/*.lng*/);
		if (marker.center === true) {
			if(this.getMap())this.getMap().setCenter(mkr_point);
		}
		
		var mark = new google.maps.Marker({
			map: this.getMap(),
			position: mkr_point,
			title: marker.title,
			icon: marker.imageHref
		});
			
		var infoWindow = null;
		if (marker.baloonContent != null) {
//			marker.baloonContent = marker.baloonContent.content + 
//				'<br/><button onClick=\'Ext.Msg.alert(\"Внимание\", \"Функционал в разработке.\");\'> Подробная информация о бригаде </button>'
			infoWindow = new google.maps.InfoWindow({content: marker.baloonContent});
			google.maps.event.addListener(mark, 'click', Ext.bind(this.showBaloon,this, [mark,infoWindow]));
		}

		if (typeof marker.listeners === 'object'){
			for (evt in marker.listeners) {
				if (marker.listeners.hasOwnProperty(key)) {
					google.maps.event.addListener(mark, evt, marker.listeners[evt]);
				}
			}
		}
		
		
		if (marker.additionalInfo) {
			for (key in marker.additionalInfo) {
				if (marker.additionalInfo.hasOwnProperty(key)) {
					mark[key] = marker.additionalInfo[key];	
				}
			}
		}
		
		this.googleMarkers.push(mark);
		this.currentMarker = mark;
		mainPanel.fireEvent('onAddMarker', mark);

		return mark;
	},
	removeMarker: function(marker) {
		for (var i=0;i<this.googleMarkers.length;i++) {	
			if (this.googleMarkers[i] == marker) {
				this.googleMarkers[i].setMap(null);
				delete this.googleMarkers[i];
				this.googleMarkers.splice(i,1);
				return;
			}
		}
	},
	showBaloon: function(mark,infoWindow) {
		if (typeof infoWindow != 'undefined') {
			infoWindow.open(this.getMap(),mark);
			this.setInfoWindow(infoWindow);
		} else {
			if (this.getInfoWindow()){
				this.getInfoWindow().close();
			}
		}
	},
	findMarkerBy: function(key,value) {
		for (var i=0;i<this.googleMarkers.length;i++) {
			if (this.googleMarkers[i][key] == value) {
				return this.googleMarkers[i];
			}
		}
	},
	setRoute:function(start,end){
		
	},
//	onMarkClick: function(mark,infoWindow) {
//		if (!mark||typeof mark == 'undefined') return false;
//		var directDisp = this.directionsDisplay;
//		var start = mark.getPosition();
//		if (!this.currentMarker||typeof this.currentMarker == 'undefined') return false;
//		var end = this.currentMarker.getPosition();
//		
//		var statuspanel = this.ownerCt.findById('gmap_status_panel'),
//		statusfield = statuspanel.find('name', 'gmap_status_field')[0];
//		var map = this.map;
//		var request = {
//			origin:start,
//			destination:end,
//			travelMode: google.maps.TravelMode.DRIVING
//		};
//		
//		if (typeof this.infoWindow != 'undefined'){
//			this.infoWindow.close();
//		}
//		
//		this.directionsService.route(request, function(result, status) {
//			if (status == google.maps.DirectionsStatus.OK) {
//			directDisp.setDirections(result);
//			statusfield.getEl().update('<div style="margin-left: 5px; width:500px; height: 16px;">Расстояние до места: '+
//				result.routes[0].legs[0].distance.text+'; Ожидаемое время прибытия:'+ result.routes[0].legs[0].duration.text + '</div>');
//			statuspanel.setVisible(true);
//			//log(result); routes[0].legs[0].distance.text; routes[0].legs[0].duration.text
//			}
//			if (typeof infoWindow != 'undefined') {
//				infoWindow.open(map,mark);
//			}
//		});
//		this.infoWindow = infoWindow;
//	},
	geocode: function (addr, callback) {
		if(typeof google=='object'){
			if(typeof google.maps.Geocoder == 'function'){
				this.geocoder = new google.maps.Geocoder();
				this.geocoder.geocode({'address': addr}, function(results, status) {
					if (status !== google.maps.GeocoderStatus.OK) {
						//Ext.MessageBox.alert('Ошибка', 'Невозможно определить точку');
						//здесь надо повесить событие
						callback({'error':'Невозможно определить точку'});
					}else{
						var place = results[0],
						point = place.geometry.location;
						if (typeof callback == 'function') {
							callback([point.lat(),point.lng()]);
						}
					}
				}.bind(this));
			}
		}
	},
	getAddressFromLatLng: function(coords, callback){
		this.geocoder = new google.maps.Geocoder();

		var crds = new google.maps.LatLng(coords[0], coords[1])
		this.geocoder.geocode({'location': crds}, function(results, status) {
			if (status !== google.maps.GeocoderStatus.OK) {
				Ext.MessageBox.alert('Ошибка', 'Невозможно определить адрес');
			}else{
				var place = results[0],
				point = place.geometry.location;
				if (typeof callback == 'function') {
					var resObj = results[0].address_components,
						address = {};
					
					for (var a in resObj){
						for (var n in resObj[a].types){
							
							switch(resObj[a].types[n]){
								case 'street_number' : {
									var streetNum = (resObj[a].long_name);
									address.streetNum = streetNum;

									if(streetNum.includes('корпус')) {
										var nums = streetNum.split('корпус')
										address.streetNumber = nums[0].trim();
										address.buildingNumber = nums[1].trim();
									} else {
										address.streetNumber = streetNum;
										address.buildingNumber = "";
									}
									break;
								}
								case 'establishment' : {
									address.establishmentName = (resObj[a].long_name);
									break;
								}
								case 'route' : {
									address.streetShortName = (resObj[a].short_name); 
									address.streetLongName = (resObj[a].long_name);
									break;}
								case 'locality' : {
									address.areaShortName = (resObj[a].short_name);
									address.areaLongName = (resObj[a].long_name)
									break;
								}
								case 'administrative_area_level_2' : {
									address.cityShortName = (resObj[a].short_name);
									address.cityLongName = (resObj[a].long_name)
									break;
								}
								case 'administrative_area_level_1' : {
									address.regionName = (resObj[a].long_name);
									break;
								}
								case 'country' : {
									address.countryName = (resObj[a].long_name);
									address.countryShortName = (resObj[a].short_name);
									break;
								}
								case 'postal_code' : {
									address.postalCode = (resObj[a].long_name);
									break;
								}
							}
						}
					}

					callback(address);
				}
			}
		}.bind(this));
	},
	initComponent: function() {
				
		var defPOVConfig = {
			heading: 34,
			pitch: 10,
			zoom: 1
		};

		Ext.applyIf(this,defPOVConfig);

		sw.GMapPanel.superclass.initComponent.call(this);  
		this.callParent(arguments);
	}
});
//
////////////////////////////////////////////////////////////////////////////////GOOGLE FOR SMP ////////////////////////////////////////////////////////////////////////////////
//
Ext.define('sw.Smp.GMapPanel',{
	extend: 'sw.GMapPanel',
	alias: 'widget.swsmpgooglemappanel',
	_ambulancePrefix: 'ambulance_', //Префикс для поиска и добавления маркеров транспортов в ambulanceMarkerList
	_ambulanceMarkeUrl: "/img/icons/ambulance32.png",
	initComponent: function() {
		
		//Определяем атрибуты-объекты в initComponent, поскольку при создании экземпляра карты, каждый экземпляр ссылается на один и тот же объект
		
		this.ambulanceMarkerList = {}; //"Ассоциативный" массив маркеров транспортов
		this.accidentMarker = null; // Маркер места вызова
		this.accidentMarkerList = {};
		this.callParent(arguments);
	},
	setRouteFromAmbulanceToAccident: function( id ) {
		var ambulanceMarker = this.ambulanceMarkerList[this._ambulancePrefix + id];
		
		if (!this.accidentMarker || !ambulanceMarker) {
			return false;
		}
		
		var mainPanel = this.up('panel[refId=swMapPanel]'),
			request = {
				origin: ambulanceMarker.car_marker.getPosition(),
				destination: this.accidentMarker.getPosition(),
				travelMode: google.maps.TravelMode.DRIVING
			};
			
		this.getDirectionsService().route(request,function(response,status){
			if ( status == google.maps.DirectionsStatus.OK ) {
				this.getDirectionsDisplay().setDirections(response);
				mainPanel.fireEvent('onSetRoadTrack', {timeDuration:response.routes[0].legs[0].duration.text});
			}
		}.bind(this));
		
	},
	setMapViewToCenter: function( id ) {
		try
		{
			var ambulanceMarker = this.ambulanceMarkerList[this._ambulancePrefix + id],
				zoom = 16,
				position = ambulanceMarker.car_marker.getPosition();
			this.getMap().setCenter(position);
			this.getMap().setZoom(zoom);
		}
		catch(e)
		{
			Ext.Msg.alert('Ошибка','Положение бригады еще не получено');
		}
	},
	getAllAmbulancesArrivalTimeToAccident: function( idList, card, callback ) {
		
		if (
			! (idList instanceof Array) || 
			! idList.length || 
			! Ext.isFunction( callback )  ||
			! (this.accidentMarkerList[card])
			)
		{
			callback( false );
			return false;
		}

		var destinations = [ this.accidentMarkerList[card].mapMarker.getPosition()],
			map = this,
			origins = (function(){
				
				var ambulancePositionList = [],
					ambulanceMarker;
					
				for (var i = 0; i < idList.length; i++) {
					ambulanceMarker = map.ambulanceMarkerList[map._ambulancePrefix + idList[i]];
					if (!ambulanceMarker || !ambulanceMarker.car_marker || !Ext.isFunction(ambulanceMarker.car_marker.getPosition)) {
						// на нет и суда нет. обнулим тогда это значение в idList, а то после могут быть несоответствия 
						// далее нулевые значения будут исключены в idListGoogle
						idList[i] = 0;
						continue;
					}
					ambulancePositionList.push( ambulanceMarker.car_marker.getPosition() );
				};
				
				return ambulancePositionList;
				
			})(),
			service = new google.maps.DistanceMatrixService();
		
		service.getDistanceMatrix(
			{
				origins: origins,
				destinations: destinations,
				travelMode: google.maps.TravelMode.DRIVING,
				avoidHighways: false,
				avoidTolls: false
			}, 
			function(response, status){
				var idListGoogle = [];
				for(var i=0; i < idList.length; i++){
					if( idList[i] ) idListGoogle.push( idList[i] );
				}
				if ( status !== 'OK' || 
					! response || 
					! (response.rows instanceof Array) || 
					response.rows.length != idListGoogle.length
				) {
					if(!getRegionNick().inlist(['ufa', 'krym', 'kz']))
						log('Ошибка', 'Во время получения времени доезда произошла ошибка #4' );
					log(arguments);
					return;
				}
				
				var result = [],
					rows = response.rows;
				
				for (var i = 0; i < rows.length; i++) {
					try {
						result.push({
							id: idListGoogle[i],
							durationText: rows[i].elements[0].duration.text,
							durationValue: rows[i].elements[0].duration.value,
							distanceText: rows[i].elements[0].distance.text,
							distanceValue: rows[i].elements[0].distance.value
						})
					} catch (e) {
						if(!getRegionNick().inlist(['ufa', 'krym', 'kz']))
							log('Ошибка', 'Во время получения времени доезда произошла ошибка #5' );
						log({e:e});
						return;
					}
				};
				
				callback(result);
				
			}
		);
	},
	_getAddressForGeocodeByCmpCallCardRecord: function( record , callback){
		
		if (!(record instanceof Ext.data.Model) || 
			!record.get('Adress_Name') || 
			typeof callback !== 'function') 
		{
			return false;
		}
		
		callback(record.get('Adress_Name'));
	},
	addAmbulanceMarker: function( marker ) {
		// Проверка входных данных
		if (!marker || !marker.id || !marker.point || !marker.point[0] || !marker.point[1]) {
			return;
		}
	
		// Устанавливаем маркер автомобиля
		marker.title = marker.title || '';
		
		var car_marker = new google.maps.Marker({
			position: new google.maps.LatLng( marker.point[0], marker.point[1] ),
			map: this.getMap(),
			title: marker.title,
			icon: {
				url: this.getAmbulanceMarkerUrl(marker),
				//url: this._ambulanceMarkeUrl,
				anchor: new google.maps.Point(17, 20),
				rotation: Math.round(marker.direction) || null
			}
		});
		
		// Устанавливаем маркер направления
		
		
		// var car_direction_marker = (direction) ? new google.maps.Marker({
		// 			position: new google.maps.LatLng(marker.point[0], marker.point[1]),
		// 			map: this.getMap(),
		// 			icon:{
		// 				path: google.maps.SymbolPath.FORWARD_CLOSED_ARROW, 
		// 				scale:4,
		// 				rotation: direction,
		// 				fillColor: 'green',
		// 				fillOpacity: 0.8,
		// 				strokeWeight: 1,
		// 				strokeColor: '#e1e1e1',
		// 				anchor: new google.maps.Point(0, 10)
		// 			}
		// 		}) : null;
		
		// Добавляем данные маркера в массив маркеров
		//this.markers.push(marker);
		
		// Добавляем маркеры google в массив маркеров google
		this.googleMarkers.push(car_marker);
		// this.googleMarkers.push(car_direction_marker);
		
		// Добавляем данные маркера в массив маркеров автомобилей СМП
		// 
		// добавляем с префиксом "ambulance_" для последующего быстрого
		// поиска по идентификатору автомобиля
		
		this.ambulanceMarkerList[this._ambulancePrefix + marker.id] = {
			data: marker,
			car_marker: car_marker
			// ,car_direction_marker: car_direction_marker
		}
		
	},
	updateAmbulanceMarker: function( marker ) {
		
		if (!marker || !marker.id || !marker.point || !marker.point[0] || !marker.point[1] 	) {
			return;
		}
		
		var ambulanceMarker = this.ambulanceMarkerList[this._ambulancePrefix+marker.id];
		
		if ( !ambulanceMarker || !ambulanceMarker.car_marker ) {
			return;
		}
		
		marker.title = marker.title || '';
		
		//Обновляем позицию и наименование
		
		ambulanceMarker.car_marker.setPosition(new google.maps.LatLng( marker.point[0], marker.point[1] ));
		ambulanceMarker.car_marker.setTitle(marker.title);
		
		ambulanceMarker.car_marker.setIcon(this.getAmbulanceMarkerUrl(marker));
		
		if (!ambulanceMarker.data) {
			ambulanceMarker.data = marker;
		} else {
			ambulanceMarker.data.point = marker.point;
			ambulanceMarker.data.title = marker.title;
		}
		
		// @TODO: Уточнить у ТНЦ, что значит если direction отсутствует. Он пропал совсем или просто не изменился ?
		// если пропал совсем, то надо маркер направления удалять
		
		//Шаблон параметров иконки зелёной стрелки
		// var green_arrow_icon_template = {
		// 	path: google.maps.SymbolPath.FORWARD_CLOSED_ARROW, 
		// 	scale:4,
		// 	rotation: Math.round(ambulanceMarker.data.direction) || null,
		// 	fillColor: 'green',
		// 	fillOpacity: 0.8,
		// 	strokeWeight: 1,
		// 	strokeColor: '#e1e1e1',
		// 	anchor: new google.maps.Point(0, 10)
		// };
				
		// if (  !ambulanceMarker.car_direction_marker ) {
		// 	if (marker.direction) {
		// 		// Добавляем маркер направления, если его не было, но данные направления появились
		// 		ambulanceMarker.car_direction_marker = new google.maps.Marker({
		// 			position: new google.maps.LatLng(marker.point[0], marker.point[1]),
		// 			map: this.getMap(),
		// 			icon:green_arrow_icon_template
		// 		});
		// 		this.googleMarkers.push(ambulanceMarker.car_direction_marker);
				
		// 	} else {
		// 		// Если не было маркера направления и данных по направлению не появилось - не делаем ничего
		// 	}
		// } else {
		// 	//Если маркер направления есть, обновляем его позицию
		// 	ambulanceMarker.car_direction_marker.setPosition(new google.maps.LatLng( marker.point[0], marker.point[1] ));
		// 	if (marker.direction) {
		// 		// Если маркер направления есть и данные направления появились, обновляем иконку с новым направлением и цветом (с красной на зелёную)
		// 		green_arrow_icon_template.direction = marker.direction;
		// 		ambulanceMarker.car_direction_marker.setIcon(green_arrow_icon_template);
		// 	} else {
		// 		// Если маркер направления есть, но данных по направлению не пришло, обновляем цвет иконки (с красной на зелёную)
		// 		ambulanceMarker.car_direction_marker.setIcon(green_arrow_icon_template);
		// 	}	
		// }
		
	},
	deleteOldAmbulanceMarkers: function(markerList){
		var map = this;
		for(var markerIndex in map.ambulanceMarkerList){
			var exists = false;
			markerList.forEach(function(item){
				if(map.ambulanceMarkerList[markerIndex]['data']['id'] == item['id']){
					exists = true;
				}
			})
			if(!exists){
				console.log('mark del', map.ambulanceMarkerList[markerIndex]['data'])
				map.deleteAmbulanceMarker(map.ambulanceMarkerList[markerIndex]['data'])

			}
		}
	},
	deleteAmbulanceMarker: function( marker ) {
		
		var ambulanceMarker = this.ambulanceMarkerList[this._ambulancePrefix+marker.id];
		
		// Проверка входных данных
		if (!marker || !marker.id || !ambulanceMarker) {
			return;
		}
		
		this.removeMarker(ambulanceMarker.car_marker);
		// this.removeMarker(ambulanceMarker.car_direction_marker);
		
		delete this.ambulanceMarkerList[this._ambulancePrefix+marker.id];
		
	},
	
	setMapViewToCenterByCoords: function( coords ){
		var mapPanel = this,
			map = mapPanel.getMap();
			
		if(!map) return false;

		try
		{
			var latLng = new google.maps.LatLng( coords[0], coords[1] );
			this.getMap().setCenter( latLng );
			this.getMap().setZoom(18);
			//this.getMap().setCenter(coords, 18);
		}
		catch(e)
		{
			Ext.Msg.alert('Координаты вызова не установлены');
		}
	},
	
	//Удаляет с карты проложенные маршруты
	clearRoutes: function() {
		
		this.getDirectionsDisplay().setDirections({routes: []});
		
	},
	setAmbulanceMarker: function( marker ) {

		// Проверка входных данных
		if (!marker || !marker.id || !marker.point || !marker.point[0] || !marker.point[1]) {
			return;
		}
		
		//
		//Проверяем наличие маркера в массиве ( отображенной бригады СМП на карте)
		//Если маркер с идентификатором установлен, значит необходимо его
		//переместить согласно полученным данным
		//
		
		
		if (!this.ambulanceMarkerList[this._ambulancePrefix+marker.id]) {
			return this.addAmbulanceMarker(marker);
		} else {
			return this.updateAmbulanceMarker(marker);
		}
		
	},
	setVisibleAmbulanceMarker: function( id , visible ) {
		
		var ambulanceMarker = this.ambulanceMarkerList[this._ambulancePrefix+id];
		
		if (!ambulanceMarker) {
			return false;
		}
		
		if (ambulanceMarker.car_marker) {
			ambulanceMarker.car_marker.setVisible(visible);
		}
		if (ambulanceMarker.car_direction_marker) {
			ambulanceMarker.car_direction_marker.setVisible(visible);
		}
		ambulanceMarker.data.visible = false;
		
	},
	setAccidentMarker: function( data ) {
		
		if (data === null) {
			return this._deleteAccidentMarker();
		}
		
		if (!data && !data.point || !data.point[0] || !data.point[1]) {
			return false;
		}
		
		return ( (this.accidentMarker !== null) ? this._updateAccidentMarker(data) : this._addAccidentMarker(data) );
		
	},
	/*
	* @private
	*/
	_addAccidentMarker: function( data ) {
		
		// Проверка входных данных
		if (!data  || !data.point || !data.point[0] || !data.point[1]) {
			return false;
		}
		
		this.accidentMarker = this.addMarker( data );
		
		return true;
		
	},
	/*
	* @private
	*/
	_updateAccidentMarker: function( data ) {
		// Обновляем маркер вызова только по полям title и point
		
		if (!data  || !data.point || !data.point[0] || !data.point[1]) {
			return false;
		}
		var latLng = new google.maps.LatLng( data.point[0], data.point[1] );
		this.accidentMarker.setPosition( latLng );
		this.getMap().setCenter( latLng );
		this.accidentMarker.setTitle(data.title || '');
		
	},
	/*
	* @private
	*/
	_deleteAccidentMarker: function( ) {
		this.removeMarker( this.accidentMarker );
		this.accidentMarker = null;
	}
});


//
////////////////////////////////////////////////////////////////////////////////YANDEX ////////////////////////////////////////////////////////////////////////////////
//
Ext.define('sw.YandexMapPanel',{
	extend: 'sw.BaseMapPanel',
	//id: 'swYandexMapPanel',	
	alias: 'widget.swyandexmappanel',
	isRendered: false,
	yandexMarkers: [],
	showTraffic: true,
	currentMarker: null,
	src: 'https://api-maps.yandex.ru/2.1/?load=package.standard&lang=ru_RU',
	doClick: function (event) {
		var mainPanel = this.up('panel[refId=swMapPanel]'),
			mainConfig = mainPanel.initialConfig;

		//mainPanel.fireEvent('mapClick', {point: event.get('coords'), pagepixel: event._Hb.position}); //old version yaMap 1.x
		mainPanel.fireEvent('mapClick', {point: event.get('coords'), pagepixel: event.get('position')});
		/*
		if (this.currentMarker) {
			this.removeMarker(this.currentMarker);
		}
		
		this.addMarker({point: event.get('coords'), pagepixel: event.get('position')});
		*/
		if (typeof this.clickCallback == 'function') {
			this.clickCallback(event);
		}

	},
	
	geocode: function (addr,callback) {
		if( (typeof ymaps === "undefined") || !Ext.isFunction(ymaps.geocode))
			callback();
		ymaps.geocode(addr, {results: 1}).then(function (res) {
			if (typeof callback == 'function') {
				callback(res.geoObjects.get(0).geometry.getCoordinates())
			}
		}, function (err) {
			//если не работает яндекс - возвращаем пустой callback
			if (typeof callback == 'function') {
				callback()
			}
			// Если геокодирование не удалось, сообщаем об ошибке.
			//Ext.MessageBox.alert('Ошибка', 'Невозможно определить точку </br>'+err.message);
		}).catch(function(e) {
			if (typeof callback == 'function') {
				callback({'error':'Невозможно определить точку'})
			}
		})
	},
	
	getAddressFromLatLng: function(coords, callback){
		ymaps.geocode(coords).then(function(res){
				if (typeof callback == 'function') {
					var resObj = res.geoObjects.get(0).properties.get('metaDataProperty').GeocoderMetaData.AddressDetails.Country,
						address = {};	
						
					function checkProp(o){
						if (typeof o == 'object'){
							for (var key in o) {
								if (o.hasOwnProperty(key)) {
									
									switch(key){
										case 'PremiseNumber' : {
											var streetNum = (o[key]);
											address.streetNum = streetNum;

											if(streetNum.includes('к')) {
												var nums = streetNum.split('к')
												address.streetNumber = nums[0].trim();
												address.buildingNumber = nums[1].trim();
											} else {
												address.streetNumber = streetNum;
												address.buildingNumber = "";
											}
											break;
										}
										case 'PremiseName' : {
											address.establishmentName = (o[key]);
											break;
										}
										case 'ThoroughfareName' : {
											address.streetLongName = (o[key]);
											break;
										}
										case 'LocalityName' : {
											address.areaShortName = (o[key]);											
											break;
										}
//										case 'LocalityName' : {
//											address.cityShortName = (o[key]);
//											break;
//										}
										case 'AdministrativeAreaName' : {
											address.regionName = (o[key]);
											break;
										}
										case 'CountryName' : {
											address.countryName = (o[key]);											
											break;
										}
										case 'CountryNameCode': {
											address.countryShortName = (o[key]);
											break;
										}
//										case 'postal_code' : {
//											address.postalCode = (resObj[a].long_name);
//											break;
//										}									
									}
									
									arguments.callee(o[key]);									
								}
							}
						}
					}
					
					checkProp(resObj)

					callback(address)
				}
			}, function(err){
				//Ext.MessageBox.alert('Ошибка', 'Невозможно определить точку </br>'+err.message);
			});
	},
	findMarkerBy: function(key,value) {
		for (var i=0;i<this.yandexMarkers.length;i++) {
			if (this.yandexMarkers[i][key] == value) {
				return this.yandexMarkers[i];
			}
		}
	},
	removeMarker: function(marker) {
		for (var i=0;i<this.yandexMarkers.length;i++) {	
			this.getMap().geoObjects.remove(marker);
			if (this.yandexMarkers[i] == marker) {
				delete this.yandexMarkers[i];
				this.yandexMarkers.splice(i,1);
				this.getMap().geoObjects.remove(marker);
				return;
			}
		}		
	},
	searchControler:function(addr, callback){
		var searchControl = new ymaps.control.SearchControl({
			options: {
				provider: 'yandex#search'
			}
		});

		searchControl.search(addr).then(function(a){
			if (typeof callback == 'function') {
				callback(a.responseMetaData.SearchResponse.Point.coordinates)
			}
		})
	},
	loadMap: function( callback ) {	
		var src = this.src + '&apikey=' + getGlobalOptions().yandex_api_key;
		this.loadScript(src,function(){
			ymaps.ready(function(){
				var mainPanel = this.up('panel[refId=swMapPanel]');	
				var mainConfig = mainPanel.initialConfig;
				
				function loadMapWithOpts(coords, pan){
					
					pan.map = new ymaps.Map(pan.getLayout().getElementTarget().id, {
						center:[coords[0], coords[1]],
						zoom:15,
						controls: ['zoomControl']	
					},
					{
						suppressMapOpenBlock: true
					}
					
					);

					pan.map.behaviors.enable('scrollZoom');
					pan.map.behaviors.disable('dblClickZoom');
					
					if ( typeof callback == 'function' ) {
						callback();
					};
					
					pan.isRendered = true;
					
					pan.fireEvent('mapIsReady');
					
					if ( mainPanel.showTraffic ) {
						pan.map.actualProvider = new ymaps.traffic.provider.Actual({}, { infoLayerShown: true });						
						// И затем добавим его на карту.
						pan.map.actualProvider.setMap(pan.map);
					}
					
					if ( mainConfig.addMarkByClick ) {
						pan.map.events.add('dblclick',function(e){
							pan.doClick(e)
						}.bind(pan));
					}
				};
				
				if(mainPanel.currentPosition==null){
					this.geocode(getGlobalOptions().region.name, function(coords){
						
						loadMapWithOpts(coords, this);
					}.bind(this));
				}
				else{
					loadMapWithOpts(mainPanel.currentPosition, this);
				}
				
			}.bind(this));
		}.bind(this));
	},
	/**
	 *
	 * @param marker.point Array
	 * @param marker.baloonContent String
	 * @param marker.title String
	 * @param marker.imageHref String
	 * @param marker.imageSize String
	 * @param marker.imageOffset String
	 * @param marker.additionalInfo Object
	 * @param marker.listeners Object
	 * @returns {*}
	 */
	addMarker: function (marker) {
		if ((!marker.point&&!Ext.isArray(marker.point)&&!marker.point.length!=2)&&(!marker.address)) {
			return false;
		}
		var yandexMarkerInitObj = {attrs: {}};
		yandexMarkerInitObj['point']=[marker.point[0],marker.point[1]];
		
		if (marker.baloonContent != null) {
			yandexMarkerInitObj['attrs']['balloonContent'] = marker.baloonContent;
			yandexMarkerInitObj['attrs']['iconContent'] = '';
			yandexMarkerInitObj['attrs']['hintContent'] = "";
		}

		if (marker.title) {
			yandexMarkerInitObj['attrs']['hintContent'] = marker.title;
		}

		if (marker.id) {
			yandexMarkerInitObj['attrs']['id'] = marker.id;
		}

		if (marker.imageHref && marker.imageSize && marker.imageOffset) {
			yandexMarkerInitObj['opts'] = {
				iconLayout: 'default#image',
				iconImageHref:marker.imageHref,
				iconImageSize: marker.imageSize,//[32,37],
				iconImageOffset: marker.imageOffset//[-16,-37]
			};
		} else {
			yandexMarkerInitObj['opts'] = {
				preset: 'twirl#redStretchyIcon'
			};
		}

		if (marker.center === true) {
			this.getMap().setCenter([marker.point[0],marker.point[1]]);
		}
		if( (ymaps && ymaps.Placemark) === undefined)
			return false;

		var yandexMarker = new ymaps.Placemark(yandexMarkerInitObj.point,yandexMarkerInitObj.attrs,yandexMarkerInitObj.opts);
		if (marker.additionalInfo) {
			for (key in marker.additionalInfo) {
				if (marker.additionalInfo.hasOwnProperty(key)) {
					yandexMarker[key] = marker.additionalInfo[key];
				}
			}
		}
		this.yandexMarkers.push(yandexMarker);

		if (typeof marker.listeners === 'object'){
			for (evt in marker.listeners) {
				yandexMarker.events.add(evt, marker.listeners[evt], yandexMarker)
			}
		}

		this.getMap().geoObjects.add(yandexMarker);

		this.currentMarker = yandexMarker;

		return this.currentMarker

	},
	initComponent: function() {
		
		this.addEvents({
			mapIsReady: true,
		});
		
		this.callParent(arguments);
	}
});


//
////////////////////////////////////////////////////////////////////////////////YANDEX FOR SMP////////////////////////////////////////////////////////////////////////////////
//

Ext.define('sw.Smp.YandexMapPanel',{
	extend: 'sw.YandexMapPanel',
	alias: 'widget.swsmpyandexmappanel',
	_ambulancePrefix: 'ambulance_', //Префикс для поиска и добавления маркеров транспортов в ambulanceMarkerList
	_ambulanceMarkeUrl: "/img/icons/ambulance32.png",
	_ambulanceRoute: null,
	initComponent: function() {
		
		//Определяем атрибуты-объекты в initComponent, поскольку при создании экземпляра карты, каждый экземпляр ссылается на один и тот же объект
		
		this.ambulanceMarkerList = {}; //"Ассоциативный" массив маркеров транспортов
		this.accidentMarker = null; // Маркер места вызова
		this.accidentMarkerList = {};
		this.callParent(arguments);
	},
	_getAddressForGeocodeByCmpCallCardRecord: function( record , callback ){
		
		if (!(record instanceof Ext.data.Model) || 
			!record.get('Adress_Name') || 
			typeof callback !== 'function') 
		{
			return false;
		}
		
		callback(record.get('Adress_Name'));
	},
	addAmbulanceMarker: function( marker ) {
		// Проверка входных данных
		if (!marker || !marker.id || !marker.point || !marker.point[0] || !marker.point[1]) {
			return;
		}
	
		// Устанавливаем маркер автомобиля
		
		marker.title = marker.title || '';
		
		var car_marker = new ymaps.Placemark(
			marker.point,
			{
				hintContent:marker.baloon || marker.title
			},
			{
				iconLayout: 'default#image',
				//iconImageHref: this._ambulanceMarkeUrl,
				iconImageHref: this.getAmbulanceMarkerUrl(marker),
				iconImageSize: [37, 26]
			}
		);

		// Добавляем маркеры yandex в массив маркеров yandex
		this.yandexMarkers.push(car_marker);

		this.getMap().geoObjects.add(car_marker);
				
		// Добавляем данные маркера в массив маркеров автомобилей СМП
		// 
		// добавляем с префиксом "ambulance_" для последующего быстрого
		// поиска по идентификатору автомобиля
		
		this.ambulanceMarkerList[this._ambulancePrefix + marker.id] = {
			data: marker,
			car_marker: car_marker
		}
	},
	setLpuBuildingMarker: function(data) {
		var me = this;
		if(!data) return false;
		var lpuBuildingBalloonLayout = ymaps.templateLayoutFactory.createClass( // Создание балуна
			'<div class="mapBalloon">' +
			'{{properties.LpuBuilding_Name}}<br />' +
			'<span>Свободно бригад/Занято бригад </span>{{properties.TeamsStatusFree_Count}}/{{properties.TeamsStatusDuty_Count}}<br />' +
			'<span>Приняты/Ожидают принятия вызовы </span>{{properties.CallsAccepted}}/{{properties.CallsNoAccepted}}<br />' +
			'<span>Врачи/Фельдшеры на смене </span>{{properties.Team_HeadShiftCount}}/{{properties.Team_AssistantCount}}<br />' +
			'</div>'
		);
		var placeMark = new ymaps.Placemark(
			[data.LpuBuilding_Latitude, data.LpuBuilding_Longitude], // coordinates
			{//properties
				LpuBuilding_Name: data.LpuBuilding_Name,
				TeamsStatusFree_Count: data.TeamsStatusFree_Count,
				TeamsStatusDuty_Count: data.TeamsStatusDuty_Count,
				Team_HeadShiftCount: data.Team_HeadShiftCount,
				Team_AssistantCount: data.Team_AssistantCount,
				CallsAccepted: data.CallsAccepted,
				CallsNoAccepted: data.CallsNoAccepted,
				markType: 'lpuBuilding'
			},{
				iconLayout: 'default#image',
				iconImageHref: '/img/icons/mapMarkers/emergencyLpuBuilding.png',
				balloonContentLayout: lpuBuildingBalloonLayout,
				// Запретим замену обычного балуна на балун-панель.
				// Если не указывать эту опцию, на картах маленького размера откроется балун-панель.
				balloonPanelMaxMapArea: 0,
				balloonMinWidth: 273,
				balloonMaxHeight: 110,
				hideIconOnBalloonOpen:false,
				iconImageSize: [21, 21],
			}
		);

		this.getMap().geoObjects.add(placeMark);
	},
	updateAmbulanceMarker: function( marker ) {
		
		if (!marker || !marker.id || !marker.point || !marker.point[0] || !marker.point[1] 	) {
			return;
		}
		
		var ambulanceMarker = this.ambulanceMarkerList[this._ambulancePrefix+marker.id];
		
		if ( !ambulanceMarker || !ambulanceMarker.car_marker ) {
			return;
		}
				
		//Обновляем позицию и наименование
		marker.title = marker.title || '';
		
		ambulanceMarker.car_marker.geometry.setCoordinates(marker.point);
		
		ambulanceMarker.car_marker.options.set('iconImageHref', this.getAmbulanceMarkerUrl(marker));
		ambulanceMarker.car_marker.properties.set('hintContent', marker.title);
		/*
		ambulanceMarker.car_marker.setPosition(new google.maps.LatLng( marker.point[0], marker.point[1] ));
		ambulanceMarker.car_marker.setTitle(marker.title);
		
		var direction = Math.round(marker.direction) || null;
		
		if (direction) {
			ambulanceMarker.car_marker.setIcon({
				url: this._ambulanceMarkeUrl,
				anchor: new google.maps.Point(17, 20),
				rotation: direction
			});
		}*/
	},
	deleteAmbulanceMarker: function( marker ) {	
		
	},
	setAmbulanceMarker: function( marker ) {
		if (!marker || !marker.id || !marker.point || !marker.point[0] || !marker.point[1]) {
			return;
		}
		
		//
		//Проверяем наличие маркера в массиве ( отображенной бригады СМП на карте)
		//Если маркер с идентификатором установлен, значит необходимо его
		//переместить согласно полученным данным
		//
		
		if (!this.ambulanceMarkerList[this._ambulancePrefix+marker.id]) {
			return this.addAmbulanceMarker(marker);
		} else {
			return this.updateAmbulanceMarker(marker);
		}
	},
	setVisibleAmbulanceMarker: function( id , visible ) {	
		
	},
	
	setAccidentMarker: function( data ) {
		if (data === null) {
			return this._deleteAccidentMarker();
		}
		
		if (!data && !data.point || !data.point[0] || !data.point[1]) {
			return false;
		}
		return ( (this.accidentMarker !== null) ? this._updateAccidentMarker(data) : this._addAccidentMarker(data) );
		
	},
	/*
	* @private
	*/
	_addAccidentMarker: function( data ) {
		// Проверка входных данных
		if (!data  || !data.point || !data.point[0] || !data.point[1]) {
			return false;
		}

		this.accidentMarker = this.addMarker( data );
		return this.accidentMarker;
		
	},
	/*
	* @private
	*/
	_updateAccidentMarker: function( data ) {
		// Обновляем маркер вызова только по полям title и point
		
		if (!data  || !data.point || !data.point[0] || !data.point[1]) {
			return false;
		}
		
		// Сменить тайтл и местоположение		
		this.getMap().setCenter(data.point);
		this.accidentMarker.geometry.setCoordinates(data.point);
		
		return this.accidentMarker;
		//this.accidentMarker.title = marker.title || '';			
	},
	/*
	* @private
	*/
	_deleteAccidentMarker: function( ) {
		this.removeMarker( this.accidentMarker );
		this.accidentMarker = null;
	},
	calculateMultiTrackTimeDestination: function(coordLocationsFrom, coordsLocationTo, callback){
		
	},
	getAllAmbulancesArrivalTimeToAccident: function( idList, card, callback ) {
		if (
			! (idList instanceof Array) || 
			! idList.length || 
			! Ext.isFunction( callback )  ||
			! (this.accidentMarkerList[card])
			)
		{
			callback( false );
			return false;
		}
		

		var map = this,
			activeCarsYandexMarkers = function(){
				var ambulanceYandexMarkerList = [],
					ambulanceMarker;
				for (var i = 0; i < idList.length; i++) {
					ambulanceMarker = map.ambulanceMarkerList[map._ambulancePrefix + idList[i]];
					if (!ambulanceMarker || !ambulanceMarker.car_marker || !Ext.isFunction(ambulanceMarker.car_marker.geometry.getCoordinates)) {
						continue;
					}
					ambulanceMarker.car_marker.ambulance_id = idList[i];
					ambulanceYandexMarkerList.push( ambulanceMarker.car_marker/*.geometry.getCoordinates()*/ );
				};
				
				return ambulanceYandexMarkerList;
				
			},
			carsGeoResultArray = ymaps.geoQuery(activeCarsYandexMarkers()),
			sortedambulanceYandexMarkerListByDistance = carsGeoResultArray.sortByDistance(map.accidentMarkerList[card].mapMarker.geometry),
			result = [],
			countMarkers = sortedambulanceYandexMarkerListByDistance.getLength(),
			collectRequest = function(marker, route){
				result.push({
					id: marker.ambulance_id,
					durationText: route.getHumanTime(),
					durationValue: route.getTime(),
					distanceText: route.getHumanLength(),
					distanceValue: route.getLength()
				});
				if(result.length == countMarkers){
					callback(result);
				}
			};

		if (!sortedambulanceYandexMarkerListByDistance.getLength()) {
			callback(false);
			return false;
		}

		sortedambulanceYandexMarkerListByDistance.each(function(marker, index){
			if(index==countMarkers) {
				callback(false);
				return false;
			}
			ymaps.route([
				map.accidentMarkerList[card].mapMarker.geometry.getCoordinates(),
				marker.geometry.getCoordinates()
			]).then(function (route) {
				collectRequest(marker, route);
			});
		});
		
	},
	setRouteFromAmbulanceToAccident: function( id ) {

		var ambulanceMarker = this.ambulanceMarkerList[this._ambulancePrefix + id],
			mapPanel = this,
			mainPanel = this.up('panel[refId=swMapPanel]');
		
		if (!mapPanel.accidentMarker || !ambulanceMarker) {
			return false;
		}
		
		ymaps.route([
			ambulanceMarker.car_marker.geometry.getCoordinates(),
			mapPanel.accidentMarker.geometry.getCoordinates()
		],{
			mapStateAutoApply: true
		}).then(function (route) {
			if(mapPanel._ambulanceRoute){
				mapPanel.getMap().geoObjects.remove(mapPanel._ambulanceRoute);
			}
			route.getWayPoints().removeAll();			
			mapPanel._ambulanceRoute = route;
			mapPanel.getMap().geoObjects.add(route);
			mainPanel.fireEvent('onSetRoadTrack', {timeDuration:route.getHumanTime()});
		})		
	},
	setMapViewToCenter: function( id ) {
		try
		{
			var ambulanceMarker = this.ambulanceMarkerList[this._ambulancePrefix + id],
				zoom = 16,
				position = ambulanceMarker.car_marker.geometry.getCoordinates();

			this.getMap().setCenter(position, zoom);
		}
		catch(e)
		{
			Ext.Msg.alert('Ошибка','Положение бригады еще не получено');
		}
	},
	setMapViewToCenterByCoords: function( coords ){
		var mapPanel = this,
			map = mapPanel.getMap();
			
		if(!map) return false;
		
		if(!map.isRendered){
			mapPanel.on('mapIsReady', function(){
			})
		}
		try
		{
			this.getMap().setCenter(coords, 18);
		}
		catch(e)
		{
			Ext.Msg.alert('Координаты вызова не установлены');
		}
	}
})

//
//////////////////////////////////////////////////////////////////////////////// OPENSTREETMAP ////////////////////////////////////////////////////////////////////////////////
//
Ext.define('sw.OSMMapPanel',{
	extend: 'sw.BaseMapPanel',
	//id: 'swOSMMapPanel',	
	alias: 'widget.swosmmappanel',
	isRendered: false,
	markers: {},	//layer
	OSMMarkers: [], //array
	currentMarker: null,
	src: 'http://www.openlayers.org/api/OpenLayers.js',
	geocode: function( addr, callback ){
		var params = {
			street: addr['House']+' '+addr['Street'],
			//country: 'РОССИЯ',
			format:'json',
			limit:1
		};
		if (addr['City']) {
			params['city']=addr['City'];
		} else {
			params['state']=addr['Rgn'];
		}
		
		Ext.Ajax.request({
			url: 'https://nominatim.openstreetmap.org/search.php',
			params:params,
			method: 'GET',
			success: function(response, opts) {
				var resp = JSON.parse(response.responseText);
				if (Ext.isArray(resp)&&(resp.length>0)&&(resp[0].lon)&&(resp[0].lat)) {
					if ( typeof callback == 'function' ) {
						callback([resp[0].lat,resp[0].lon]);
					}
				} else {
					Ext.MessageBox.alert('Ошибка', 'Невозможно определить точку');
				}
			},
			failure: function(response, opts) {
				Ext.MessageBox.alert('Ошибка', 'При получении адреса для OSM карт возникли ошибки. Обратитесь к администратору');
			}
		});
	},
	
	getAddressFromLatLng: function(coords, callback){
		var params = {
			lat: coords[0],
			lon: coords[1],
			format:'json',
			limit:1
		};
		Ext.Ajax.request({
			url: 'https://nominatim.openstreetmap.org/reverse',
			params:params,
			method: 'GET',
			success: function(response, opts) {
				var resp = JSON.parse(response.responseText);
				if (resp) {
					var address = {};

					function checkProp(o){
						if (typeof o == 'object'){
							for (var key in o) {
								if (o.hasOwnProperty(key)) {
									
									switch(key){
										case 'house_number' : {
											address.streetNum = (o[key]); 
											break;
										}
//										case 'PremiseName' : {
//											address.establishmentName = (o[key]);
//											break;
//										}
										case 'road' : {
											address.streetLongName = (o[key]);
											break;
										}
										case 'city' : {
											address.areaShortName = (o[key]);											
											break;
										}
//										case 'LocalityName' : {
//											address.cityShortName = (o[key]);
//											break;
//										}
										case 'state' : {
											address.regionName = (o[key]);
											break;
										}
										case 'country' : {
											address.countryName = (o[key]);
											break;
										}
										case 'country_code': {
											address.countryShortName = (o[key]);
											break;	
										}
										case 'postcode' : {
											address.postalCode = (o[key]);
											break;
										}									
									}
									
									arguments.callee(o[key]);									
								}
							}
						}
					}
					
					checkProp(resp);
					
					if ( typeof callback == 'function' ) {
						callback(address);
					}
				} else {
					Ext.MessageBox.alert('Ошибка', 'Невозможно определить точку');
				}
			},
			failure: function(response, opts) {
				Ext.MessageBox.alert('Ошибка', 'При получении адреса для OSM карт возникли ошибки. Обратитесь к администратору');
			}
		});
	},
	findMarkerBy: function(key,value) {
		for (var i=0;i<this.OSMMarkers.length;i++) {
			if (this.OSMMarkers[i][key] == value) {
				return this.OSMMarkers[i];
			}
		}
	},
	removeMarker: function(marker) {
		for (var i=0;i<this.OSMMarkers.length;i++) {			
			if (this.OSMMarkers[i] == marker) {
				delete this.OSMMarkers[i];
				this.OSMMarkers.splice(i,1);
				this.markers.removeMarker(marker); 
				return;
			}
		}	
	},
	
	doClick: function (event) {
		
		var mainPanel = this.up('panel[refId=swMapPanel]'),
			mainConfig = mainPanel.initialConfig;
			
		if (this.currentMarker) {
			this.removeMarker(this.currentMarker);
		}
		
		var lonlat = this.map.getLonLatFromViewPortPx(event.xy)
        
		lonlat.transform(
		  new OpenLayers.Projection("EPSG:900913"), 
		  new OpenLayers.Projection("EPSG:4326")
		);
//		this.currentMarker = this.addMarker({
//			point:[lonlat.lat, lonlat.lon], 
//			imageHref: '/img/googlemap/firstaid.png', 
//			title:'Новый адрес',
//			imageOffset: [-16, -37],
//			imageSize: [32, 37]
//		});

		mainPanel.fireEvent('mapClick', {point:[lonlat.lat, lonlat.lon], pagepixel: [event.clientX, event.clientY]});
	},
	
	loadMap: function( callback ){
		this.loadScript(this.src,function(){
			
				var mainPanel = this.up('panel[refId=swMapPanel]');
				
				this.map = new OpenLayers.Map(this.getLayout().getElementTarget().id);
				var mapnik = new OpenLayers.Layer.OSM();
				this.getMap().addLayer(mapnik);
				
				var lonlat = new OpenLayers.LonLat(mainPanel.currentPosition[1],mainPanel.currentPosition[0]).transform(
					new OpenLayers.Projection("EPSG:4326"), // transform from WGS 1984
					new OpenLayers.Projection("EPSG:900913")
				)

				var zoom = 15;

				this.markers = new OpenLayers.Layer.Markers( "Markers" );
				this.map.addLayer(this.markers);

				OpenLayers.Popup.FramedCloud.prototype.autoSize = false;
				this.getMap().setCenter(lonlat, zoom);
				this.isRendered = true;
				
				if ( typeof callback == 'function' ) {
					callback();
				}
				
				var mainPanel = this.up('panel[refId=swMapPanel]'),
					mainConfig = mainPanel.initialConfig;
				
				OpenLayers.Control.Click = OpenLayers.Class(OpenLayers.Control, {
					defaultHandlerOptions: {
						'single': false,
						'double': true,
						'pixelTolerance': 0,
						'stopSingle': false,
						'stopDouble': true
					},

					initialize: function(options) {
						this.handlerOptions = OpenLayers.Util.extend(
							{}, this.defaultHandlerOptions
						);
						OpenLayers.Control.prototype.initialize.apply(
							this, arguments
						); 
						this.handler = new OpenLayers.Handler.Click(
							this, {
								'dblclick': this.trigger
							}, this.handlerOptions
						);
					}
				});

				if (mainConfig.addMarkByClick) {
					var click = new OpenLayers.Control.Click({
						trigger: function(e) {
							this.doClick(e);
						}.bind(this)
					});
					this.map.addControl(click);
					click.activate();
				}

		}.bind(this))
	},
	popups: [],
	addMarker: function (marker) {
		if ((!marker.point&&!Ext.isArray(marker.point)&&!marker.point.length!=2)&&(!marker.address)) {
			return false;
		}
		var OsmInitObj = {
			lonlat: new OpenLayers.LonLat(marker.point[1],marker.point[0]).transform(
				new OpenLayers.Projection("EPSG:4326"), // transform from WGS 1984
				new OpenLayers.Projection("EPSG:900913")
			)
		};

		var feature = new OpenLayers.Feature(this.markers, OsmInitObj.lonlat);
				
		if (marker.imageHref && marker.imageSize && marker.imageOffset) {
			
			OsmInitObj.size = new OpenLayers.Size(marker.imageSize[0],marker.imageSize[1]);
			OsmInitObj.offset = new OpenLayers.Pixel(-(OsmInitObj.size.w/2), -OsmInitObj.size.h);
			OsmInitObj.icon = new OpenLayers.Icon(marker.imageHref, OsmInitObj.size, OsmInitObj.offset);	
			feature.data.icon  = OsmInitObj.icon;
		} else {
			OsmInitObj.icon = false;
		}

		if (marker.center === true) {
			this.getMap().setCenter(OsmInitObj.lonlat);
		}
		
		//@TODO обработать остальные события
		
		
		if (marker.baloonContent != null) {
			feature.data.popupContentHTML = marker.baloonContent;
			
			feature.data.overflow = "auto";
			AutoSizeFramedCloud = OpenLayers.Class(OpenLayers.Popup.FramedCloud, {
				'autoSize': true
			});
			feature.popupClass = AutoSizeFramedCloud;
			feature.closeBox = false;
			OsmInitObj.osmmark = feature.createMarker();
			
			var parentObject = this;
			var markerClick = function (evt) {
				if (this.popup == null) {
					Ext.Array.each(parentObject.curPopup, function(popup, idx, those) {
						popup.hide();
					});
					this.popup = this.createPopup(this.closeBox);
					parentObject.getMap().addPopup(this.popup);
					this.popup.show();
					parentObject.popups.push = this.popup;
				} else {
					if (this.popup.visible()) {
						this.popup.hide();
					} else {
						Ext.Array.each(parentObject.curPopup, function(popup, idx, those) {
							popup.hide();
						});
						this.popup.show();
					}
				}
				parentObject.curPopup = this.popup;
				OpenLayers.Event.stop(evt);
			};
			OsmInitObj.osmmark.events.register("mousedown", feature, markerClick);
			
		} else {
			OsmInitObj.osmmark = feature.createMarker();
		}
		
		if (marker.additionalInfo) {
			for (key in marker.additionalInfo) {
				if (marker.additionalInfo.hasOwnProperty(key)) {
					OsmInitObj.osmmark[key] = marker.additionalInfo[key];	
				}
			}
		}
		this.OSMMarkers.push(OsmInitObj.osmmark);
		this.markers.addMarker(OsmInitObj.osmmark);
		this.currentMarker = OsmInitObj.osmmark;
		return this.currentMarker
	},
	initComponent: function() {
		this.callParent(arguments);
	}
});


//
////////////////////////////////////////////////////////////////////////////////OPENSTREETMAP FOR SMP////////////////////////////////////////////////////////////////////////////////
//

Ext.define('sw.Smp.OSMMapPanel',{
	extend: 'sw.OSMMapPanel',
	alias: 'widget.swsmposmmappanel',
	_ambulancePrefix: 'ambulance_', //Префикс для поиска и добавления маркеров транспортов в ambulanceMarkerList
	_ambulanceMarkeUrl: "/img/icons/ambulance32.png",
	initComponent: function() {
		
		//Определяем атрибуты-объекты в initComponent, поскольку при создании экземпляра карты, каждый экземпляр ссылается на один и тот же объект
		
		this.ambulanceMarkerList = {}; //"Ассоциативный" массив маркеров транспортов
		this.accidentMarker = null, // Маркер места вызова
		this.accidentMarkerList = {};
		this.callParent(arguments);
	},
	_getAddressForGeocodeByCmpCallCardRecord: function( record , callback){
		
		if (!(record instanceof Ext.data.Model) || 
			!record.get('CmpCallCard_id') || 
			typeof callback !== 'function') 
		{
			return false;
		}
		
		Ext.Ajax.request({
			url: '?c=CmpCallCard&m=getAddressForOsmGeocode',
			params: {
				CmpCallCard_id: record.get('CmpCallCard_id')
			},
			method: 'POST',
			success: function(response, opts) {
				
				var res = Ext.JSON.decode( response.responseText , true);
				if (res === null || !res[0]) {
					Ext.MessageBox.alert('Ошибка', 'При получении адреса для OSM карт возникли ошибки. Обратитесь к администратору');
					log( response.responseText );
					return;
				}
				callback({
					Rgn: res[0]['Rgn_Name'] || '',
					City: res[0]['City_Name'] || '',
					Street: res[0]['Street_Name'] || '',
					House: res[0]['House_Name'] || ''
				})
				
			},
			failure: function(response, opts) {
				Ext.MessageBox.alert('Ошибка', 'При получении адреса для OSM карт возникли ошибки. Обратитесь к администратору');
			}
		});
	},
	addAmbulanceMarker: function( marker ) {
		
	},
	updateAmbulanceMarker: function( marker ) {
		
	},
	deleteAmbulanceMarker: function( marker ) {	
		
	},
	setAmbulanceMarker: function( marker ) {

	},
	setVisibleAmbulanceMarker: function( id , visible ) {
	},
	setAccidentMarker: function( data ) {
		
		if (data === null) {
			return this._deleteAccidentMarker();
		}
		
		if (!data && !data.point || !data.point[0] || !data.point[1]) {
			return false;
		}
		
		return ( (this.accidentMarker !== null) ? this._updateAccidentMarker(data) : this._addAccidentMarker(data) );
		
	},
	/*
	* @private
	*/
	_addAccidentMarker: function( data ) {
		
		// Проверка входных данных
		if (!data  || !data.point || !data.point[0] || !data.point[1]) {
			return false;
		}
		
		this.accidentMarker = this.addMarker( data );
		return true;
		
	},
	/*
	* @private
	*/
	_updateAccidentMarker: function( data ) {
		// Обновляем маркер вызова только по полям title и point
		
		if (!data  || !data.point || !data.point[0] || !data.point[1]) {
			return false;
		}
		
		// Сменить тайтл и местоположение
		
		// this.accidentMarker.car_marker.setPosition(new google.maps.LatLng( data.point[0], data.point[1] ));
		// this.accidentMarker.car_marker.setTitle(data.title || '');
		
	},
	/*
	* @private
	*/
	_deleteAccidentMarker: function( ) {
		this.removeMarker( this.accidentMarker );
		this.accidentMarker = null;
	}
})

//
////////////////////////////////////////////////////////////////////////////////WIALON ////////////////////////////////////////////////////////////////////////////////
//
Ext.define('sw.WialonMapPanel',{
	//id: 'swWialonMapPanel',
	extend: 'sw.BaseMapPanel',	
	alias: 'widget.swwialonmappanel',	
	isRendered: false,	
	markers: {},	
	animateMarkers: false,	
	currentMarker: null,	
	uid: null,	
	src: 'http://maps.google.com/maps/api/js?libraries=geometry&sensor=false&language=ru',
	layout: 'fit',
	refreshTime: 20000,
	/**
	 * @private
	 */
	geocoder: null,	
	directionsDisplay: null,	
	directionsService: null,	
	/**
	 * @var Размер иконки объекта
	 */
	iconMaxBorder: 32,	
	/**
	 * @var Объекты на карте
	 */
	units: [],	
	/**
	 * @var Кеш IDs объектов
	 */
	cacheUnitsByEmergencyTeamId: {},	
	/**
	 * @var Маркер места вызова
	 */
	markerEmergencyCall: null,
	
	getXmlHttp: function(){
		var xmlhttp;
		try {
			xmlhttp = new ActiveXObject("Msxml2.XMLHTTP");
		} catch (e) {
			try {
				xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
			} catch (E) {
				xmlhttp = false;
			}
		}
		if (!xmlhttp && typeof XMLHttpRequest!='undefined') {
			xmlhttp = new XMLHttpRequest();
		}
		return xmlhttp;
	},
	
	request: function(options, callback){
		var params = Ext.apply({
			method: 'POST',
			url: null,
			async: false,
			type: 'html',
			success: null,
			failure: null
		},options);
		
		Ext.Ajax.request({
			url: params.url,
			method: params.method,
			success: function(response, opts) {
				//log('response', Ext.JSON.decode(response.responseText, true));
				if ( (response.status==200) )
				{
					var res = Ext.JSON.decode(response.responseText, true);
					callback(res);
				}
			},
			failure: function(response, opts) {
				//Ext.MessageBox.alert('Ошибка', 'Обратитесь к администратору');
			}
		});

	},
	
	getDirectionsDisplay: function(){
		if(typeof google == 'undefined') return;
		return this.directionsDisplay || (this.directionsDisplay = new google.maps.DirectionsRenderer({
			suppressMarkers: true
		}));
		
	},
	
	getDirectionsService : function(){
		if ( this.directionsService === null ) {
			this.directionsService = new google.maps.DirectionsService();
		}
		return this.directionsService;
	},
	
	initGurtamMaps: function(uid){
		function GurtamMapsType() {}; // Gurtam map type variable
				GurtamMapsType.prototype.tileSize = new google.maps.Size(256, 256);
		GurtamMapsType.prototype.maxZoom = 17;
		GurtamMapsType.prototype.name = "Gurtam";
		GurtamMapsType.prototype.alt = "Gurtam Maps";
		GurtamMapsType.prototype.getTile = function(coord, zoom, ownerDocument) {
			var url = "http://render.mapsviewer.com/hst-api.wialon.com/gis_render/"+coord.x + "_" + coord.y + "_" + (this.maxZoom-zoom) + "/" + uid + "/tile.png";
			//var url = "http://render.mapsviewer.com/hst-api.wialon.com/avl_render/"+coord.x + "_" + coord.y + "_" + (this.maxZoom-zoom) + "/" + Math.floor(Math.random() * (-100000)) + ".png";
			var img = ownerDocument.createElement("IMG");
			img.src = url;
			img.style.width = this.tileSize.width + "px";
			img.style.height = this.tileSize.height + "px";
			img.style.border = "0px";
			return img;
		};
		
		var mapType = (isDebug()) ? google.maps.MapTypeId.ROADMAP : "gurtammaps"; //Пока не отображается Gurtam
		
		var mapOptions = {
			mapTypeId: mapType,
			mapTypeControlOptions: {
				mapTypeIds: [google.maps.MapTypeId.ROADMAP, google.maps.MapTypeId.SATELLITE, "gurtammaps"],
				style: google.maps.MapTypeControlStyle.DROPDOWN_MENU
			}
		}
		
		this.getMap().setOptions( mapOptions );
		var gurtam = new GurtamMapsType(); // create new instance of GurtamMap
		this.map.mapTypes.set("gurtammaps", gurtam);		

		var trafficLayer = new google.maps.TrafficLayer();
			trafficLayer.setMap(this.getMap());
		this.getDirectionsService();
		this.getDirectionsDisplay().setMap(this.getMap());
		
		this.fireEvent('afterInit', this, this.getMap());
	},
	
	
	getAddressFromLatLng: function(coords, callback){
		this.geocoder = new google.maps.Geocoder();

		var crds = new google.maps.LatLng(coords[0], coords[1])
		this.geocoder.geocode({'location': crds}, function(results, status) {
			if (status !== google.maps.GeocoderStatus.OK) {
				Ext.MessageBox.alert('Ошибка', 'Невозможно определить адрес');
			}else{
				var place = results[0],
				point = place.geometry.location;
				if (typeof callback == 'function') {
					var resObj = results[0].address_components,
						address = {};
					
					for (var a in resObj){
						for (var n in resObj[a].types){
							
							switch(resObj[a].types[n]){
								case 'street_number' : {
									address.streetNum = (resObj[a].long_name); 
									break;
								}
								case 'establishment' : {
									address.establishmentName = (resObj[a].long_name);
									break;
								}
								case 'route' : {
									address.streetShortName = (resObj[a].short_name); 
									address.streetLongName = (resObj[a].long_name);
									break;}
								case 'locality' : {
									address.areaShortName = (resObj[a].short_name);
									address.areaLongName = (resObj[a].long_name)
									break;
								}
								case 'administrative_area_level_2' : {
									address.cityShortName = (resObj[a].short_name);
									address.cityLongName = (resObj[a].long_name)
									break;
								}
								case 'administrative_area_level_1' : {
									address.regionName = (resObj[a].long_name);
									break;
								}
								case 'country' : {
									address.countryName = (resObj[a].long_name);
									address.countryShortName = (resObj[a].short_name);
									break;
								}
								case 'postal_code' : {
									address.postalCode = (resObj[a].long_name);
									break;
								}									
							}
						}
					}

					callback(address);
				}
			}
		}.bind(this));
	},
	
	loadMap: function(callback){		
		this.mapPanel.loadMap(function(){
			this.isRendered = true;
			this.geocoder = new google.maps.Geocoder();
			this.map = this.mapPanel.getMap();
			var own = this;
			//var url='http://kit-api.wialon.com/wialon/ajax.html?svc=core/login&params={user:kitdemo,password:kitdemo}';
			Ext.Ajax.request({
				url: '/?c=Wialon&m=getMapUid',
				callback: function(opt, success, response) {
					// @TODO: Возвращает "{error: 8} ?"
					log({'response.responseText':response.responseText});
					if (success && response.responseText != '') {
						own.initGurtamMaps(response.responseText);
					}
				}
			});			

			// @todo Сохранить список бригад с ключом = идентификатор
			var win = this.up('window');			
//			if (win.showCar !== false) {
//				this.getAllAvlUnitsWithCoords(function(result){
//					this.units = result.items;
//					this.fillMapWithUnits();
//				}.bind(this));			
//			}
			//this.units = result.items;
			
			if (this.animateMarkers){
				Ext.Loader.loadScript({
					url: 'jscore4/lib/jquery/markerAnimate.js',
					scope: this
				})
			}
				
			if ( typeof callback == 'function' ) {
				callback();
			}
		}.bind(this));
	},
	
	initComponent: function(){
		
		//@todo Автоподгонка по высоте
		
		this.mapPanel = new sw.GMapPanel({
			id: this.id + 'googlemap',
			hidden: false,
			flex: 1
		});
				
		Ext.apply(this,{
			items: [this.mapPanel]
		});
		
		this.callParent(arguments);
		
		
		//sw.Promed.WialonPanel.superclass.initComponent.apply(this,arguments);
	},
	
	geocode: function( address, callback ){
		this.mapPanel.geocode( address, callback );
	},
	
	findMarkerBy: function( key, value ) {
		return this.mapPanel.findMarkerBy( key, value );
	},
//	findMarkerBy: function(key,value) {
//		for( var i in this.units  ) {
//			unit = this.units[i];
//			console.log
//			if (unit[key] == value) {
//				return unit;
//			}
//		}
//		for (var i=0;i<this.googleMarkers.length;i++) {
//			if (this.googleMarkers[i][key] == value) {
//				return this.googleMarkers[i];
//			}
//		}
//	},
	
	removeMarker: function( marker ){
		return this.mapPanel.removeMarker( marker );
	},

//	removeMarker: function(marker) {
//		if (marker){
//		marker.setMap(null);
//		}
//	},
	
	addMarker: function( marker ){
		return this.mapPanel.addMarker( marker );
	}
});


//
////////////////////////////////////////////////////////////////////////////////WIALON FOR SMP////////////////////////////////////////////////////////////////////////////////
//

Ext.define('sw.Smp.WialonMapPanel',{
	extend: 'sw.WialonMapPanel',
	alias: 'widget.swsmpwialonmappanel',
	_ambulancePrefix: 'ambulance_', //Префикс для поиска и добавления маркеров транспортов в ambulanceMarkerList
	_ambulanceMarkeUrl: "/img/icons/ambulance32.png",
	markers: [],
	initComponent: function() {
		
		//Определяем атрибуты-объекты в initComponent, поскольку при создании экземпляра карты, каждый экземпляр ссылается на один и тот же объект
		
		this.ambulanceMarkerList = {}; //"Ассоциативный" массив маркеров транспортов
		this.accidentMarker = null, // Маркер места вызова
		this.accidentMarkerList = {};
		this.callParent(arguments);
	},
	setRouteFromAmbulanceToAccident: function( id ) {
		var ambulanceMarker = this.ambulanceMarkerList[this._ambulancePrefix + id];
		
		if (!this.accidentMarker || !ambulanceMarker) {
			return false;
		}
		//@TODO: Переделать на прямую ссылку
		var mainPanel = this.up('panel[refId=swMapPanel]'),
			request = {
				origin: ambulanceMarker.car_marker.getPosition(),
				destination: this.accidentMarker.getPosition(),
				travelMode: google.maps.TravelMode.DRIVING
			};
			
		this.getDirectionsService().route(request,function(response,status){
			if ( status == google.maps.DirectionsStatus.OK ) {
				this.getDirectionsDisplay().setDirections(response);
				mainPanel.fireEvent('onSetRoadTrack', {timeDuration:response.routes[0].legs[0].duration.text});
			}
		}.bind(this));
		
	},
	getAllAmbulancesArrivalTimeToAccident: function( idList, card, callback ) {
		
		if (
			! (idList instanceof Array) || 
			! idList.length || 
			! Ext.isFunction( callback )  ||
			! (this.accidentMarkerList[card])
			)
		{
			callback( false );
			return false;
		}

		var destinations = [ this.accidentMarkerList[card].mapMarker.getPosition()],
			map = this,
			origins = (function(){
				
				var ambulancePositionList = [],
					ambulanceMarker;
					
				for (var i = 0; i < idList.length; i++) {
					ambulanceMarker = map.ambulanceMarkerList[map._ambulancePrefix + idList[i]];
					if (!ambulanceMarker || !ambulanceMarker.car_marker || !Ext.isFunction(ambulanceMarker.car_marker.getPosition)) {
						continue;
					}
					ambulancePositionList.push( ambulanceMarker.car_marker.getPosition() );
				};
				
				return ambulancePositionList;
				
			})(),
			service = new google.maps.DistanceMatrixService();
		
		service.getDistanceMatrix(
			{
				origins: origins,
				destinations: destinations,
				travelMode: google.maps.TravelMode.DRIVING,
				avoidHighways: false,
				avoidTolls: false
			}, 
			function(response, status){
				var idListWialon = [];
				for(var i=0; i < idList.length; i++){
					if( idList[i] ) idListWialon.push( idList[i] );
				}
				if ( status !== 'OK' || 
					! response || 
					! (response.rows instanceof Array) || 
					response.rows.length != idListWialon.length
				) {
					if(!getRegionNick().inlist(['ufa', 'krym', 'kz']))
						log('Ошибка', 'Во время получения времени доезда произошла ошибка #6' );
					log(arguments);
					return;
				}
				
				var result = [],
					rows = response.rows;
				
				for (var i = 0; i < rows.length; i++) {
					try {
						result.push({
							id: idListWialon[i],
							durationText: rows[i].elements[0].duration.text,
							durationValue: rows[i].elements[0].duration.value,
							distanceText: rows[i].elements[0].distance.text,
							distanceValue: rows[i].elements[0].distance.value
						})
					} catch (e) {
						if(!getRegionNick().inlist(['ufa', 'krym', 'kz']))
							log('Ошибка', 'Во время получения времени доезда произошла ошибка #7' );
						log({e:e});
						return;
					}
				};
				
				callback(result);
				
			}
		);
		
		
	},
	_getAddressForGeocodeByCmpCallCardRecord: function( record , callback) {
		
		if (!(record instanceof Ext.data.Model) || 
			!record.get('Adress_Name') || 
			typeof callback !== 'function') 
		{
			return false;
		}
		
		callback(record.get('Adress_Name'));
	},
	addAmbulanceMarker: function( marker ) {
		// Проверка входных данных
		if (!marker || !marker.id || !marker.point || !marker.point[0] || !marker.point[1]) {
			return;
		}
	
		// Устанавливаем маркер автомобиля
		
		marker.title = marker.title || '';
		
		
		var car_marker = new google.maps.Marker({
			position: new google.maps.LatLng( marker.point[0], marker.point[1] ),
			map: this.map,
			title: marker.title,
			icon: {
				//url: this._ambulanceMarkeUrl,
				url: this.getAmbulanceMarkerUrl(marker),
				anchor: new google.maps.Point(17, 20)
			}
		});
		
		// Устанавливаем маркер направления
		
		
		var car_direction_marker = ( Math.round(marker.direction) || null) ? new google.maps.Marker({
					position: new google.maps.LatLng(marker.point[0], marker.point[1]),
					map: this.map,
					icon:{
						path: google.maps.SymbolPath.FORWARD_CLOSED_ARROW, 
						scale:4,
						rotation:  Math.round(marker.direction) || null,
						fillColor: 'green',
						fillOpacity: 0.8,
						strokeWeight: 1,
						strokeColor: '#e1e1e1',
						anchor: new google.maps.Point(0, 10)
					}
				}) : null;
		
				
		// Добавляем маркеры google в массив маркеров google
		this.markers.push(car_marker);
		this.markers.push(car_direction_marker);
		
		marker.visible = true;
		
		// Добавляем данные маркера в массив маркеров автомобилей СМП
		// 
		// добавляем с префиксом "ambulance_" для последующего быстрого
		// поиска по идентификатору автомобиля
		
		this.ambulanceMarkerList[this._ambulancePrefix + marker.id] = {
			data: marker,
			car_marker: car_marker
			,car_direction_marker: car_direction_marker
		}
		
		
		// @TODO: Как только класс карты виалон станет унифицированным - выпилить
		// Добавляем объект юнита для взаимодействия с предыдущим функционалом Виалона
		this.units.push({
			id: marker.id,
			pos: {
				x: marker.point[1],
				y: marker.point[0],
				c: Math.round(marker.direction) || null
			},
			nm: marker.title,
			marker: car_marker,
			arrow: car_direction_marker
		});
		
	},
	
	updateAmbulanceMarker: function( marker ) {
		
		if (!marker || !marker.id || !marker.point || !marker.point[0] || !marker.point[1] 	) {
			return;
		}
		var ambulanceMarker = this.ambulanceMarkerList[this._ambulancePrefix+marker.id];
		
		if ( !ambulanceMarker || !ambulanceMarker.car_marker ) {
			return;
		}
		
		marker.title = marker.title || '';
		
		//Обновляем позицию и наименование
		
		ambulanceMarker.car_marker.setPosition(new google.maps.LatLng( marker.point[0], marker.point[1] ));
		ambulanceMarker.car_marker.setTitle(marker.title);
		
		ambulanceMarker.car_marker.setIcon(this.getAmbulanceMarkerUrl(marker));
		
		var direction = Math.round(marker.direction) || null;
		
		if (!ambulanceMarker.data) {
			ambulanceMarker.data = marker;
		} else {
			ambulanceMarker.data.point = marker.point;
			ambulanceMarker.data.title = marker.title;
			ambulanceMarker.data.direction = marker.direction;
		}
		
		// @TODO: Уточнить у ТНЦ, что значит если direction отсутствует. Он пропал совсем или просто не изменился ?
		// если пропал совсем, то надо маркер направления удалять
		
		//Шаблон параметров иконки зелёной стрелки
		var green_arrow_icon_template = {
			path: google.maps.SymbolPath.FORWARD_CLOSED_ARROW, 
			scale:4,
			rotation: Math.round(ambulanceMarker.data.direction) || null,
			fillColor: 'green',
			fillOpacity: 0.8,
			strokeWeight: 1,
			strokeColor: '#e1e1e1',
			anchor: new google.maps.Point(0, 10)
		};
				
		if (  !ambulanceMarker.car_direction_marker ) {
			if (marker.direction) {
				// Добавляем маркер направления, если его не было, но данные направления появились
				ambulanceMarker.car_direction_marker = new google.maps.Marker({
					position: new google.maps.LatLng(marker.point[0], marker.point[1]),
					map: this.map,
					icon:green_arrow_icon_template
				});
				this.markers.push(ambulanceMarker.car_direction_marker);
				
			} else {
				// Если не было маркера направления и данных по направлению не появилось - не делаем ничего
			}
		} else {
			//Если маркер направления есть, обновляем его позицию
			ambulanceMarker.car_direction_marker.setPosition(new google.maps.LatLng( marker.point[0], marker.point[1] ));
			if (marker.direction) {
				// Если маркер направления есть и данные направления появились, обновляем иконку с новым направлением и цветом (с красной на зелёную)
				green_arrow_icon_template.direction = marker.direction;
				ambulanceMarker.car_direction_marker.setIcon(green_arrow_icon_template);
			} else {
				// Если маркер направления есть, но данных по направлению не пришло, обновляем цвет иконки (с красной на зелёную)
				ambulanceMarker.car_direction_marker.setIcon(green_arrow_icon_template);
			}	
		}
		
		// @TODO: Как только класс карты виалон станет унифицированным - выпилить
		// Обновляем объект юнита для взаимодействия с предыдущим функционалом Виалона
		var found = false;
		for (var i = 0; i < this.units.length && !found ; i++) {
			var marker = ambulanceMarker.data.id;
			
			found = (marker.id == this.units[i].id);
			if (found) {
				this.units[i] = {
					id: marker.id,
					pos: {
						x: marker.point[1],
						y: marker.point[0],
						c: Math.round(marker.direction) || null
					},
					nm: marker.title,
					marker: ambulanceMarker.car_marker,
					arrow: ambulanceMarker.car_direction_marker
				}
			}
			
		};
		
	},
	deleteAmbulanceMarker: function( marker ) {
		
		var ambulanceMarker = this.ambulanceMarkerList[this._ambulancePrefix+marker.id];
		
		// Проверка входных данных
		if (!marker || !marker.id || !ambulanceMarker) {
			return;
		}
		
		this.removeMarker(ambulanceMarker.car_marker);
		this.removeMarker(ambulanceMarker.car_direction_marker);
		
		delete this.ambulanceMarkerList[this._ambulancePrefix+marker.id];
		
	},
	setMapViewToCenter: function(id) {
		try
		{
			var ambulanceMarker = this.ambulanceMarkerList[this._ambulancePrefix + id],
				zoom = 16,
				position = ambulanceMarker.car_marker.getPosition();
			this.getMap().setCenter(position);
		}
		catch(e)
		{
			Ext.Msg.alert('Ошибка','Положение бригады еще не получено');
		}
	},
	setAmbulanceMarker: function( marker ) {

		// Проверка входных данных
		if (!marker || !marker.id || !marker.point || !marker.point[0] || !marker.point[1]) {
			return;
		}
		
		//
		//Проверяем наличие маркера в массиве ( отображенной бригады СМП на карте)
		//Если маркер с идентификатором установлен, значит необходимо его
		//переместить согласно полученным данным
		//
		
		if (!this.ambulanceMarkerList[this._ambulancePrefix+marker.id]) {
			return this.addAmbulanceMarker(marker);
		} else {
			return this.updateAmbulanceMarker(marker);
		}

	},
	setVisibleAmbulanceMarker: function( id , visible ) {
		
		var ambulanceMarker = this.ambulanceMarkerList[this._ambulancePrefix+id];
		
		if (!ambulanceMarker) {
			return false;
		}
		
		if (ambulanceMarker.car_marker) {
			ambulanceMarker.car_marker.setVisible(visible);
		}
		if (ambulanceMarker.car_direction_marker) {
			ambulanceMarker.car_direction_marker.setVisible(visible);
		}
		ambulanceMarker.data.visible = false;
		
	},
	setAccidentMarker: function( data ) {

		if (data === null) {
			return this._deleteAccidentMarker();
		}
		
		if (!data && !data.point || !data.point[0] || !data.point[1]) {
			return false;
		}
		
		return ( (this.accidentMarker !== null) ? this._updateAccidentMarker(data) : this._addAccidentMarker(data) );
		
	},
	//Удаляет с карты проложенные маршруты
	clearRoutes: function() {
		var dd = this.getDirectionsDisplay();
		if(dd)
			dd.setDirections({routes: []});
		
	},
	/*
	* @private
	*/
	_addAccidentMarker: function( data ) {
		
		// Проверка входных данных
		if (!data  || !data.point || !data.point[0] || !data.point[1]) {
			return false;
		}
		
		this.accidentMarker = this.addMarker( data );
		return true;
		
	},
	/*
	* @private
	*/
	_updateAccidentMarker: function( data ) {
		// Обновляем маркер вызова только по полям title и point
		
		if (!data  || !data.point || !data.point[0] || !data.point[1]) {
			return false;
		}
		
		var latLng = new google.maps.LatLng( data.point[0], data.point[1] );
		this.accidentMarker.setPosition(latLng);
		this.getMap().setCenter( latLng );
		this.accidentMarker.setTitle(data.title || '');
		
	},
	/*
	* @private
	*/
	_deleteAccidentMarker: function( ) {
		this.removeMarker( this.accidentMarker );
		this.accidentMarker = null;
	}
});


//
////////////////////////////////////////////////////////////////////////////////HERE////////////////////////////////////////////////////////////////////////////////
//

Ext.define('sw.HereMapPanel',{
	extend: 'sw.BaseMapPanel',
	alias: 'widget.swheremappanel',
	isRendered: false,
	hereMarkers: [],
	currentMarker: null,
	src: 'https://js.api.here.com/v3/3.0/mapsjs-core.js',
	serviceSrc: 'https://js.api.here.com/v3/3.0/mapsjs-service.js',
	uiSrc: 'https://js.api.here.com/v3/3.0/mapsjs-ui.js',
	uiCss: 'https://js.api.here.com/v3/3.0/mapsjs-ui.css',
	mapEventsSrc: 'https://js.api.here.com/v3/3.0/mapsjs-mapevents.js',
	app_id: 'LLXqZmwJieAdizB86O4B',
	app_code: 'htwIhneAHioJsJRdy5jcbw',
	herePlatform: null,
	map: null,
	doClick: function (event) {
		//console.log('do click');
		/*var mainPanel = this.up('panel[refId=swMapPanel]'),
			mainConfig = mainPanel.initialConfig;
		
		mainPanel.fireEvent('mapClick', {point: event.get('coordPosition'), pagepixel: event._Hb.position});
		
		if (this.currentMarker) {
			this.removeMarker(this.currentMarker);
		}
		if (typeof this.clickCallback == 'function') {
			this.clickCallback(event);
		}*/
	},
	
	geocode: function (addr,callback) {
		var mapPanel = this,
			geocoder = mapPanel.herePlatform.getGeocodingService(),
			geocodingParams = {searchText: addr},
			onResult = function(result) {
				if(!result.Response.View[0]) {
					Ext.MessageBox.alert('Ошибка', 'Невозможно определить точку </br>');
					return false;
				}
				
				var pos = result.Response.View[0].Result[0].Location.DisplayPosition;
				
				if (typeof callback == 'function') {
					callback([pos.Latitude, pos.Longitude])
				}
			},
			onError = function(error) {
				Ext.MessageBox.alert('Ошибка', 'Невозможно определить точку </br>'+error);
			};
		
		geocoder.geocode(geocodingParams, onResult, onError);
	},
	
	getAddressFromLatLng: function(coords, callback){
		//console.log('getAddressFromLatLng');
		/*ymaps.geocode(coords).then(function(res){
				if (typeof callback == 'function') {
					var resObj = res.geoObjects.get(0).properties.get('metaDataProperty').GeocoderMetaData.AddressDetails.Country,
						address = {};	
						
					function checkProp(o){
						if (typeof o == 'object'){
							for (var key in o) {
								if (o.hasOwnProperty(key)) {
									
									switch(key){
										case 'PremiseNumber' : {
											address.streetNum = (o[key]); 
											break;
										}
										case 'PremiseName' : {
											address.establishmentName = (o[key]);
											break;
										}
										case 'ThoroughfareName' : {
											address.streetLongName = (o[key]);
											break;
										}
										case 'LocalityName' : {
											address.areaShortName = (o[key]);											
											break;
										}
//										case 'LocalityName' : {
//											address.cityShortName = (o[key]);
//											break;
//										}
										case 'AdministrativeAreaName' : {
											address.regionName = (o[key]);
											break;
										}
										case 'CountryName' : {
											address.countryName = (o[key]);											
											break;
										}
										case 'CountryNameCode': {
											address.countryShortName = (o[key]);
											break;
										}
//										case 'postal_code' : {
//											address.postalCode = (resObj[a].long_name);
//											break;
//										}									
									}
									
									arguments.callee(o[key]);									
								}
							}
						}
					}
					
					checkProp(resObj)

					callback(address)
				}
			}, function(err){
				Ext.MessageBox.alert('Ошибка', 'Невозможно определить точку </br>'+err.message);
			});*/
	},
	findMarkerBy: function(key,value) {
		//console.log('findMarkerBy');
		/*for (var i=0;i<this.yandexMarkers.length;i++) {
			if (this.yandexMarkers[i][key] == value) {
				return this.yandexMarkers[i];
			}
		}*/
	},
	removeMarker: function(marker) {
		//console.log('removeMarker');
		/*for (var i=0;i<this.yandexMarkers.length;i++) {			
			if (this.yandexMarkers[i] == marker) {
				delete this.yandexMarkers[i];
				this.yandexMarkers.splice(i,1);
				this.getMap().geoObjects.remove(marker);
				return;
			}
		}*/		
	},
	loadMap: function( callback ) {
		var hereMapPanel = this,
			mainPanel = hereMapPanel.up('panel[refId=swMapPanel]');	
			mainConfig = mainPanel.initialConfig;
			
		hereMapPanel.loadScript(hereMapPanel.src,function(){
			hereMapPanel.loadScript(hereMapPanel.serviceSrc,function(){
				
				hereMapPanel.herePlatform = new H.service.Platform({
				  'app_id': hereMapPanel.app_id,
				  'app_code': hereMapPanel.app_code,
				  'lg': 'rus'
				});
				var defaultLayers = hereMapPanel.herePlatform.createDefaultLayers({lg: 'rus'});
				
				function loadMapWithOpts(coords){
					//не инициализировать карту по несколько раз
					if(!hereMapPanel.map)
					{
						hereMapPanel.map = new H.Map(
							hereMapPanel.getLayout().innerCt.dom,
							defaultLayers.normal.traffic,
							{
							  zoom: 12,
							  center: { lat: coords[0], lng: coords[1] },
							}
						);
						/*
						//сервис тайлов
						var mapTileService = hereMapPanel.herePlatform.getMapTileService({ type: 'base' }),
							parameters = { lg: 'rus'},
							tileLayer = mapTileService.createTileLayer(
							  'maptile',
							  'normal.day',
							  256,
							  'png8',
							  parameters
							);
							
						hereMapPanel.map.setBaseLayer(tileLayer);*/
						
						//сервис UI
						hereMapPanel.loadScript(hereMapPanel.uiSrc,function(){					
							var css = document.createElement("link");
							css.type = "text/css";
							css.rel="stylesheet"
							css.href = hereMapPanel.uiCss;
							document.body.appendChild(css);

							hereMapPanel.ui = H.ui.UI.createDefault(hereMapPanel.map, defaultLayers, 'ru-RU');
							hereMapPanel.ui.getControl('zoom').setAlignment('right-bottom');
							hereMapPanel.ui.getControl('mapsettings').setVisibility(false);
							
						});
						
						//сервис Событий
						hereMapPanel.loadScript(hereMapPanel.mapEventsSrc,function(){
							var mapEvents = new H.mapevents.MapEvents(hereMapPanel.map);
							var behavior = new H.mapevents.Behavior(mapEvents);
							hereMapPanel.map.addEventListener('pointerdown', function(e){	
								hereMapPanel.doClick(e);
							});
						});
						
						hereMapPanel.isRendered = true;
					}
					else{
						//если карта уже инициализирована - центруем ее по новым координатам
						hereMapPanel.map.setCenter({ lat: coords[0], lng: coords[1] }, true);
					}
				};

				if(mainPanel.currentPosition==null){
					hereMapPanel.geocode(getGlobalOptions().region.name, function(coords){
						loadMapWithOpts(coords);
					});
				}
				else{
					loadMapWithOpts(mainPanel.currentPosition);
				}
			})
		});
	},
	addMarker: function (marker) {		
		if ((!marker.point&&!Ext.isArray(marker.point)&&!marker.point.length!=2)&&(!marker.address)) {
			return false;
		}
		
		var hereMapPanel = this,
			icon = new H.map.Icon(marker.imageHref),
			accident_marker = new H.map.Marker({ lat: marker.point[0], lng: marker.point[1] }, { icon: icon });
		
		accident_marker.addEventListener ('pointerdown', function(){
			var geoPoint = accident_marker.getPosition();
			var bubble = new H.ui.InfoBubble({ lng: geoPoint.lng, lat: geoPoint.lat }, {
				content: marker.title||''
			});
			hereMapPanel.ui.addBubble(bubble);
		})
		
		hereMapPanel.getMap().addObject(accident_marker);
		
		hereMapPanel.map.setCenter({ lat: marker.point[0], lng: marker.point[1] }, true);
		
		return accident_marker;
		/*
		var yandexMarkerInitObj = {};
		yandexMarkerInitObj['point']=[marker.point[0],marker.point[1]];
		
		if (marker.baloonContent != null) {
			yandexMarkerInitObj['attrs'] = {
					iconContent: '',
					balloonContent: marker.baloonContent,
					hintContent: ''
				}
		}
		
		if (marker.imageHref && marker.imageSize && marker.imageOffset) {
			yandexMarkerInitObj['opts'] = {
				iconLayout: 'default#image',
				iconImageHref:marker.imageHref,
				iconImageSize: marker.imageSize,//[32,37],
				iconImageOffset: marker.imageOffset//[-16,-37]
			};
		} else {
			yandexMarkerInitObj['opts'] = {
				preset: 'twirl#redStretchyIcon'
			};
		}

		if (marker.center === true) {
			this.getMap().setCenter([marker.point[0],marker.point[1]]);
		}
		
		if (typeof marker.listeners === 'object'){
			yandexMarkerInitObj['events'] = [];
			for (evt in marker.listeners) {
				yandexMarkerInitObj['events'].push({
					eName: evt,
					eHandler: marker.listeners[evt]
				})
			}
		}
		
		var yandexMarker = new ymaps.Placemark(yandexMarkerInitObj.point,yandexMarkerInitObj.attrs,yandexMarkerInitObj.opts);
		if (marker.additionalInfo) {
			for (key in marker.additionalInfo) {
				if (marker.additionalInfo.hasOwnProperty(key)) {
					yandexMarker[key] = marker.additionalInfo[key];	
				}
			}
		}
		this.yandexMarkers.push(yandexMarker);
		
		this.getMap().geoObjects.add(yandexMarker);
		
		this.currentMarker = yandexMarker;
		return this.currentMarker*/

	},
	initComponent: function() {
		this.callParent(arguments);
	}
});


//
////////////////////////////////////////////////////////////////////////////////HERE FOR SMP////////////////////////////////////////////////////////////////////////////////
//

Ext.define('sw.Smp.HereMapPanel',{
	extend: 'sw.HereMapPanel',
	alias: 'widget.swsmpheremappanel',
	_ambulancePrefix: 'ambulance_', //Префикс для поиска и добавления маркеров транспортов в ambulanceMarkerList
	_ambulanceMarkeUrl: "/img/icons/ambulance32.png",
	_ambulanceRoute: null,
	initComponent: function() {
		
		//Определяем атрибуты-объекты в initComponent, поскольку при создании экземпляра карты, каждый экземпляр ссылается на один и тот же объект
		
		this.ambulanceMarkerList = {}; //"Ассоциативный" массив маркеров транспортов
		this.accidentMarker = null, // Маркер места вызова
		
		this.callParent(arguments);
	},
	_getAddressForGeocodeByCmpCallCardRecord: function( record , callback ){
		if (!(record instanceof Ext.data.Model) || 
			!record.get('Adress_Name') || 
			typeof callback !== 'function') 
		{
			return false;
		}
		callback(record.get('Adress_Name'));
		
	},
	addAmbulanceMarker: function( marker ) {
		var hereMapPanel = this;
		// Проверка входных данных
		if (!marker || !marker.id || !marker.point || !marker.point[0] || !marker.point[1]) {
			return;
		}
	
		// Устанавливаем маркер автомобиля
		var icon = new H.map.Icon(hereMapPanel._ambulanceMarkeUrl),
			car_marker = new H.map.Marker({ lat: marker.point[0], lng: marker.point[1] }, { icon: icon });
		
		car_marker.addEventListener ('pointerdown', function(){
			var geoPoint = car_marker.getPosition();
			var bubble = new H.ui.InfoBubble({ lng: geoPoint.lng, lat: geoPoint.lat }, {
				content: marker.title||''
			});
			hereMapPanel.ui.addBubble(bubble);
		})
		hereMapPanel.getMap().addObject(car_marker);

		hereMapPanel.hereMarkers.push(car_marker);
		
		hereMapPanel.ambulanceMarkerList[hereMapPanel._ambulancePrefix + marker.id] = {
			data: marker,
			car_marker: car_marker
		}
	},
	updateAmbulanceMarker: function( marker ) {
		
		if (!marker || !marker.id || !marker.point || !marker.point[0] || !marker.point[1] 	) {
			return;
		}
		
		var ambulanceMarker = this.ambulanceMarkerList[this._ambulancePrefix+marker.id];
		
		if ( !ambulanceMarker || !ambulanceMarker.car_marker ) {
			return;
		}
				
		//Обновляем позицию и наименование
		marker.title = marker.title || '';
		
		ambulanceMarker.car_marker.setPosition({ lat: marker.point[0], lng: marker.point[1] });
		
	},
	deleteAmbulanceMarker: function( marker ) {	
		
	},
	setAmbulanceMarker: function( marker ) {

		if (!marker || !marker.id || !marker.point || !marker.point[0] || !marker.point[1]) {
			return;
		}
		
		//
		//Проверяем наличие маркера в массиве ( отображенной бригады СМП на карте)
		//Если маркер с идентификатором установлен, значит необходимо его
		//переместить согласно полученным данным
		//
		
		if (!this.ambulanceMarkerList[this._ambulancePrefix+marker.id]) {
			return this.addAmbulanceMarker(marker);
		} else {
			return this.updateAmbulanceMarker(marker);
		}
		
	},
	setVisibleAmbulanceMarker: function( id , visible ) {	
		
	},
	
	setAccidentMarker: function( data ) {
		if (data === null) {
			return this._deleteAccidentMarker();
		}
		
		if (!data && !data.point || !data.point[0] || !data.point[1]) {
			return false;
		}
		
		return ( (this.accidentMarker !== null) ? this._updateAccidentMarker(data) : this._addAccidentMarker(data) );
		
	},
	/*
	* @private
	*/
	_addAccidentMarker: function( data ) {		
		// Проверка входных данных
		if (!data  || !data.point || !data.point[0] || !data.point[1]) {
			return false;
		}
		
		this.accidentMarker = this.addMarker( data );
		return true;
		
	},
	/*
	* @private
	*/
	_updateAccidentMarker: function( data ) {		
		// Обновляем маркер вызова только по полям title и point
		
		if (!data  || !data.point || !data.point[0] || !data.point[1]) {
			return false;
		}	
		
		this.accidentMarker.setPosition({ lat: data.point[0], lng: data.point[1] });
		this.map.setCenter({ lat: data.point[0], lng: data.point[1] }, true);
	},
	/*
	* @private
	*/
	_deleteAccidentMarker: function( ) {
		/*
		this.removeMarker( this.accidentMarker );
		this.accidentMarker = null;
		*/
	},
	calculateMultiTrackTimeDestination: function(coordLocationsFrom, coordsLocationTo, callback){
		//console.log('calculateMultiTrackTimeDestination');
	},
	getAllAmbulancesArrivalTimeToAccident: function( idList, callback ) {
		//console.log('getAllAmbulancesArrivalTimeToAccident');
		/*
		if (
			! (idList instanceof Array) || 
			! idList.length || 
			! Ext.isFunction( callback )  ||
			! (this.accidentMarker)
			)
		{
			callback( false );
			return false;
		}
		

		var destinations = [ this.accidentMarker.geometry.getCoordinates() ],
			map = this,
			activeCarsYandexMarkers = function(){			
				var ambulanceYandexMarkerList = [],
					ambulanceMarker;
				for (var i = 0; i < idList.length; i++) {
					ambulanceMarker = map.ambulanceMarkerList[map._ambulancePrefix + idList[i]];
					if (!ambulanceMarker || !ambulanceMarker.car_marker || !Ext.isFunction(ambulanceMarker.car_marker.geometry.getCoordinates)) {
						continue;
					}
					ambulanceMarker.car_marker.ambulance_id = idList[i];
					ambulanceYandexMarkerList.push( ambulanceMarker.car_marker );
				};
				
				return ambulanceYandexMarkerList;
				
			},
			carsGeoResultArray = ymaps.geoQuery(activeCarsYandexMarkers()),
			sortedambulanceYandexMarkerListByDistance = carsGeoResultArray.sortByDistance(map.accidentMarker.geometry),
			result = [],
			countMarkers = sortedambulanceYandexMarkerListByDistance.getLength(),
			collectRequest = function(marker, route){
				result.push({
					id: marker.ambulance_id,
					durationText: route.getHumanTime(),
					durationValue: route.getTime(),
					distanceText: route.getHumanLength(),
					distanceValue: route.getLength()
				});
				if(result.length == countMarkers-1){
					callback(result);
				}
			};
		
		sortedambulanceYandexMarkerListByDistance.each(function(marker, index){
			if(index==4) return;
			ymaps.route([
					map.accidentMarker.geometry.getCoordinates(),
					marker.geometry.getCoordinates()
				],
				{}
			).then(function (route) {
				collectRequest(marker, route);				
			});
		});
		*/
	},
	setRouteFromAmbulanceToAccident: function( id ) {		
		var ambulanceMarker = this.ambulanceMarkerList[this._ambulancePrefix + id],
			hereMapPanel = this,
			mainPanel = this.up('panel[refId=swMapPanel]');

		if (!hereMapPanel.accidentMarker || !ambulanceMarker) {
			return false;
		}
		
		var carPos = ambulanceMarker.car_marker.getPosition(),
			callPos = hereMapPanel.accidentMarker.getPosition(),			
			routingParameters = {
				'mode': 'fastest;car',
				'waypoint0': 'geo!'+carPos.lat+','+carPos.lng,
				'waypoint1': 'geo!'+callPos.lat+','+callPos.lng,
				'representation': 'display'
			};
		
		var onResult = function(result) {
			var route,
				routeShape,
				strip;
				
			  if(result.response.route) {
				// Pick the first route from the response:
				route = result.response.route[0];
				// Pick the route's shape:
				routeShape = route.shape;

				// Create a strip to use as a point source for the route line
				strip = new H.geo.Strip();

				// Push all the points in the shape into the strip:
				routeShape.forEach(function(point) {
				  var parts = point.split(',');
				  strip.pushLatLngAlt(parts[0], parts[1]);
				});
				
				if(hereMapPanel.routeLine)hereMapPanel.getMap().removeObjects([hereMapPanel.routeLine]);
				
				hereMapPanel.routeLine = new H.map.Polyline(strip, {
				  style: { strokeColor: 'blue', lineWidth: 2 }
				});

				hereMapPanel.getMap().addObjects([hereMapPanel.routeLine]);

				hereMapPanel.getMap().setViewBounds(hereMapPanel.routeLine.getBounds());
			  }
			};


			var router = hereMapPanel.herePlatform.getRoutingService();

			router.calculateRoute(routingParameters, onResult,
				function(error) {
					alert(error.message);
				});
		
		/*
		ymaps.route([
			ambulanceMarker.car_marker.geometry.getCoordinates(),
			mapPanel.accidentMarker.geometry.getCoordinates()
		],{
			mapStateAutoApply: true
		}).then(function (route) {
			if(mapPanel._ambulanceRoute){
				mapPanel.getMap().geoObjects.remove(mapPanel._ambulanceRoute);
			}
			route.getWayPoints().removeAll();			
			mapPanel._ambulanceRoute = route;
			mapPanel.getMap().geoObjects.add(route);
			mainPanel.fireEvent('onSetRoadTrack', {timeDuration:route.getHumanTime()});
		})
		*/
	}
})


//
//////////////////////////////////////////////////////////////////////////////// MAIN MAP PANEL ////////////////////////////////////////////////////////////////////////////////
//
Ext.define('sw.MapPanel',{
	refId: 'swMapPanel',
	title: 'Карта',
	layout: {
		align: 'stretch',
		pack: 'center',
		type: 'hbox'	
	},
	thisLink: this,
	showCloseHelpButtons: true,
	extend: 'Ext.panel.Panel',
	
	/**
	 * @private
	 * При необходимости получайте значение через метод getDefaultUserMap()
	 */
	defMapType: 'wialon',
	
	/**
	 * @private
	 * При необходимости получайте значение через метод getDefaultTypeList()
	 */
	//typeList: ['google','yandex','osm','wialon', 'here'],
	typeList: ['google','yandex','wialon'], // карты OpenStreetMap и Here не работают (либо не доработаны)
	curMapType: '',
	showCar: true,
	showTraffic: true,
	alias: 'widget.swmappanel',
	markers: [],

	showDefaultMap: function() {
		this.showMapType(this.getDefaultUserMap());
	},
	getCurrentMap: function() {
		return this.getPanelByType(this.curMapType);
	},
	getCurrentMarker: function(key,value) {
		return this.getPanelByType(this.curMapType).currentMarker;
	},
	getPanelByType: function(type) {
		if (type.inlist(this.getDefaultTypeList())) {
			for (var i=0;i<this.items.length;i++) {
				if (this.items.getAt(i).type == type) {
					return this.items.getAt(i);
				}
			}
		} else {
			return false
		}
	},

	/**
	 * @returns {Array} список карт используемых в регионе
	 */
	getDefaultTypeList: function(){
		var maps = this.typeList;

		if (getRegionNick().inlist(['kz', 'krym','astra'])) {
			maps = ['yandex'];
		}
		
		return maps;
	},
	
	/**
	 * @returns {String} Карта отображаемая у пользователя по умолчанию
	 */
	getDefaultUserMap: function(){
		var default_map = this.defMapType,
			user_map = Ext.util.Cookies.get('defaultmap') || '';
	
		if(getRegionNick().inlist(['kz'])) {
			default_map = 'google';
		}
		if (getRegionNick().inlist(['ufa', 'krym', 'perm', 'astra'])) {
			default_map = 'yandex';
		}

		return user_map.inlist(this.getDefaultTypeList()) ? user_map : default_map;
	},
	
	/**
	 * Геокодирование 
	 * 
	 * @param {String} addr - адрес, string
	 * @param {Function} callback - ф-я, аргументом которой является массив [lat,lng] - широта и долгота
	 */
	geocode: function(addr, callback) {
		this.getPanelByType(this.getCurrentMapType()).geocode(addr, callback);
	},
	getRouteTime: function(pointA, pointB, callback) {
		var me = this;
		if(!pointA || !pointB) return;
		const route = new ymaps.multiRouter.MultiRoute({
			// Описание опорных точек мультимаршрута.
			referencePoints: [
				pointA,
				pointB
			],
			// Параметры маршрутизации.
			params: {
				// Ограничение на максимальное количество маршрутов, возвращаемое маршрутизатором.
				results: 1,
				avoidTrafficJams: true
			}
		}, {
			opacity: 0,
			// Позволяет скрыть иконки путевых точек маршрута.
			wayPointVisible:false,
		});

		me.getCurrentMap().map.geoObjects.add(route);

		route.model.events.add('requestsuccess', function() {
			// Получение ссылки на активный маршрут.
			var activeRoute = route.getActiveRoute();
			// Вывод информации о маршруте.
			//console.log("Длина: " + activeRoute.properties.get("distance").text);
			//console.log("Время прохождения: " + activeRoute.properties.get("duration").text);
			if(activeRoute.properties.get('duration')) {
				callback(activeRoute.properties.get('duration'))
			} else {
				callback(null);
			}
		});
	},
	getCrossRoadsCoords: function(addr, callback) {
		if(this.getCurrentMapType() === 'yandex'){
			this.getPanelByType('yandex').searchControler(addr, callback);
		}
	},
	getAddressFromLatLng: function(coords, callback) {
		this.getPanelByType(this.getCurrentMapType()).getAddressFromLatLng(coords, callback);
	},
	
	getCurrentMapType: function() {
		return this.curMapType||this.getDefaultUserMap();
	},
	setCurrentMapType: function(type) {
		this.curMapType = type;
	},
	//Показать карту определенного типа
	showMapType: function(type){
		if ( type == this.curMapType ) {
			return;
		}
		
		Ext.util.Cookies.set('defaultmap', type);
		
		var map = this.getPanelByType(type);
		
		if ( !map ) {
			return;
		}
		if (type != this.curMapType) {
			var map;
			if (map = this.getPanelByType(type)) {
				
				if(this.curMapType){
					this.getPanelByType(this.curMapType).hide();
				}
				
				map.show();
				if (!map.isRendered) {
					map.loadMap(function(){						
						this.fireEvent('afterMapRender',this);
					}.bind(this));
				} else {
					this.fireEvent('afterMapRender',this);
				}
				this.curMapType = type;
			}			
		}
		
		this.curMapType = type;
	},
	findMarkerBy: function(key,value) {
		return this.getPanelByType(this.curMapType).findMarkerBy(key,value);
	},
	//@markers - массив ОБЪЕКТОВ маркеров. Получение объекта маркера через ф-ю findMarkerBy
	removeMarkers: function(markers) {
		if ((this.getCurrentMapType() == this.getPanelByType(this.getCurrentMapType()).type) && (Ext.isArray(markers))) {
			for (var i=0;i<markers.length;i++) {
				this.getPanelByType(this.getCurrentMapType()).removeMarker(markers[i]);
			}
		}
	},
	//@markers - массив данных для создания маркеров на карте.
	setMarkers: function(markers) {
		if ((this.getCurrentMapType() == this.getPanelByType(this.getCurrentMapType()).type) && (Ext.isArray(markers))) {
			for (var i=0;i<markers.length;i++) {
				this.getPanelByType(this.getCurrentMapType()).addMarker(markers[i]);
			}
		}
	},
	/**
	 *
	 * @param callback
	 * @private
	 */
	_getCurrentPositionCoords: function(callback) {
		var emptyCoords = null;
		
		if (!navigator.geolocation || !navigator.geolocation.getCurrentPosition) {
			return callback(emptyCoords);
		}

		navigator.geolocation.getCurrentPosition(
			function (cuccessLocation) {
				callback([cuccessLocation.coords.latitude, cuccessLocation.coords.longitude]);
			},
			function (error) {
				callback(emptyCoords);
				log(error.code, error.message);
			},
			{
				maximumAge : 60000,
				timeout : 5000,
				enableHighAccuracy : false
			}
		)
	},
	afterRender: function() {
		var mapWrapperPanel = this,
			conf = mapWrapperPanel.initialConfig;

		this._getCurrentPositionCoords(function (coords){
			mapWrapperPanel.currentPosition = coords;
			mapWrapperPanel.fireEvent('detectLocation');
			mapWrapperPanel.getPanelByType(mapWrapperPanel.getDefaultUserMap()).loadMap(function(){
				mapWrapperPanel.fireEvent('afterMapRender',mapWrapperPanel);
			});
		});

		sw.MapPanel.superclass.afterRender.call(mapWrapperPanel);

		if(!this.curMapType){
			this.curMapType = mapWrapperPanel.getDefaultUserMap();
		}
		if (conf.toggledButtons){
			var btnRefs = {
				google: 'button[itemId=googleBtn]',
				yandex: 'button[itemId=yandexBtn]',
				osm: 'button[itemId=osmBtn]',
				wialon: 'button[itemId=wialonBtn]',
				here: 'button[itemId=hereBtn]'
			};
			mapWrapperPanel.down(btnRefs[mapWrapperPanel.curMapType]).toggle(true, false);
		}
	},

	initComponent: function() {
		var mapWrapperPanel = this,
			conf = mapWrapperPanel.initialConfig;
	
		this.addEvents({
			mapClick: true,
			afterMapRender: true,
			onSetRoadTrack: true,
			detectLocation: true
		});

		Ext.applyIf(this,{
					
			items: [{
				xtype: 'swgooglemappanel',
				title: (conf.toggledButtons) ? null : 'Google Maps',
				type: 'google',
				hidden: this.getDefaultUserMap()=='google' ? false : true,
				flex: 1
			},{
				xtype: 'swyandexmappanel',
				title: (conf.toggledButtons) ? null :'Yandex Maps',
				type: 'yandex',
				hidden: this.getDefaultUserMap()=='yandex' ? false : true,
				flex: 1
			},{
				xtype: 'swosmmappanel',
				title: (conf.toggledButtons)? null :'osm',
				type: 'osm',
				hidden: this.getDefaultUserMap()=='osm' ? false : true,
				flex:1
			},{
				xtype:  'swwialonmappanel',
				title: (conf.toggledButtons)? null : 'Wialon',
				type: 'wialon',				
				hidden: this.getDefaultUserMap()=='wialon' ? false : true,				
				flex:1
			},
			{
				xtype:  'swheremappanel',
				title: (conf.toggledButtons)? null : 'Here',
				type: 'here',				
				hidden: this.getDefaultUserMap()=='here' ? false : true,				
				flex:1
			}
			],
		
			bbar: [{
				text: 'Google',
				toggleGroup: 'gisMapsType',
				itemId: 'googleBtn',
				hidden: !'google'.inlist(this.getDefaultTypeList()),
				enableToggle: true,
				handler: function() {
					this.showMapType('google');
				}.bind(this)
			},{
				text: 'Yandex',
				toggleGroup: 'gisMapsType',
				itemId: 'yandexBtn',
				hidden: !'yandex'.inlist(this.getDefaultTypeList()),
				enableToggle: true,
				handler: function() {
					this.showMapType('yandex');
				}.bind(this)
			},{
				text: 'OpenStreetMap',
				toggleGroup: 'gisMapsType',
				itemId: 'osmBtn',
				// @todo Вынести региональность в getDefaultTypeList()
				hidden: (getGlobalOptions().region.nick == 'pskov')||(!'osm'.inlist(this.getDefaultTypeList())),
				enableToggle: true,
				handler: function() {
					this.showMapType('osm');
				}.bind(this)
			},{
				text: 'Wialon',
				toggleGroup: 'gisMapsType',
				itemId: 'wialonBtn',
				enableToggle: true,
				hidden: !'wialon'.inlist(this.getDefaultTypeList()),
				handler: function(){ 
					this.showMapType('wialon'); 
				}.bind(this)
			},{
				text: 'Here',
				toggleGroup: 'gisMapsType',
				itemId: 'hereBtn',
				hidden: !'here'.inlist(this.getDefaultTypeList()),
				enableToggle: true,
				handler: function() {
					this.showMapType('here');
				}.bind(this)
			},
			'->',
			{
				xtype: 'button',
				text: 'Помощь',
				margin: '0 5 0 0',
				iconCls: 'help16',
				handler: function(){
					ShowHelp(this.up('window').title);								
				},
				hidden: !mapWrapperPanel.showCloseHelpButtons
			},{
				xtype: 'button',
				iconCls: 'cancel16',
				text: 'Закрыть',
				handler: function(){
					this.up('window').close()
				},
				hidden: !mapWrapperPanel.showCloseHelpButtons
			}]
		})
		this.callParent(arguments);	
		
	}
});

/**
* Панель карт для СМП 
*/
Ext.define('sw.Smp.MapPanel',{
	extend: 'sw.MapPanel',
	alias: 'widget.swsmpmappanel',
	
	//Удаляет с карты проложенные маршруты
	clearRoutes: function() {
		
		var map = this.getCurrentMap();
		
		if (typeof (map.clearRoutes) == 'function'  ) {
			map.clearRoutes.apply(map, arguments);
		}
		
	},
	
	setRouteFromAmbulanceToAccident: function( id ) {
		
		var map = this.getCurrentMap();
		
		if (typeof (map.setRouteFromAmbulanceToAccident) == 'function'  ) {
			map.setRouteFromAmbulanceToAccident.apply(map, arguments);
		}
	},
	setMapViewToCenter: function( id ) {

		var map = this.getCurrentMap();

		if (typeof (map.setMapViewToCenter) == 'function'  ) {
			map.setMapViewToCenter.apply(map, arguments);
		}
	},
	setMapViewToCenterByCoords: function( coords ) {

		var map = this.getCurrentMap();

		if (typeof (map.setMapViewToCenterByCoords) == 'function'  ) {
			map.setMapViewToCenterByCoords.apply(map, arguments);
		}
	},
	getAllAmbulancesArrivalTimeToAccident: function( idList, card_id, callback ) {
		
		var map = this.getCurrentMap();
		
		if (typeof (map.getAllAmbulancesArrivalTimeToAccident) == 'function'  ) {
			map.getAllAmbulancesArrivalTimeToAccident.apply(map, arguments);
		}
	},
	
	getAccidentMarkerUrl: function(markerData){
		var url = '/img/googlemap/firstaid.png';
		
		if(markerData && markerData.CmpCallCardStatusType_Code){
			switch (parseInt(markerData.CmpCallCardStatusType_Code)){
				case 0:
				case 1:
				case 2:
				{
					url = '/img/icons/mapMarkers/accident-markers/asset-50.png';
					break;
				}
				default: {
					url = '/img/googlemap/firstaid.png';
					break;
				}
			}
		};
		
		return url;
	},
	
	//@TODO: Подумать , может быть стоит объединить в одну функцию setCmpCallCardMarker и setAccidentMarker с т.з. ООП
	
	/**
	* @public Установка маркера талона вызова CmpCallCard на карту
	* @param record Ext.data.Model запись стора талонов вызова CmpCallCard 
	*/
	setCmpCallCardMarker: function( record , callback ) {

		if (!(record instanceof Ext.data.Model)) {
			return;
		}
		
		var point = [];
			
		// Если адрес вызова определён справочником неформализованных адресов,
		// значит координаты у него уже известны
		if (record.get('UnAdress_lat') && record.get('UnAdress_lng')) {
			point = [ record.get('UnAdress_lat'), record.get('UnAdress_lng') ];
			this.setAccidentMarker({
				point: point,
				title: record.get('Adress_Name') || ''
			});
			callback( point );
			return;
		}
		// Иначе получаем адрес необходимого формата из записи и получаем
		// координаты из методов геокодирования конкретного сервиса
		
		var map = this.getCurrentMap(),
			mapWrapperPanel = this;
			
		map._getAddressForGeocodeByCmpCallCardRecord( record , function(address){
			if (!address) {
				return;
			}
			map.geocode( address , function( point ) {
				
				if ( !(point instanceof Array) || !point[0] || !point[1]) {
					return;
				}
				mapWrapperPanel.setAccidentMarker({
					point: point,
					title: record.get('Adress_Name') || ''
				})
				callback( point );
			});
		});
	},
	setAccidentMarker: function( data ) {
		var map = this.getPanelByType(this.curMapType);
		
		if (typeof map.setAccidentMarker != 'function') {
			return false;
		}
		
		if ( data === null ) {
			return map.setAccidentMarker(data);
		}
		
		if (!data.point || !data.point[0] || !data.point[1]) {
			return false;
		}
		
		return map.setAccidentMarker( {
			point: data.point,
			center: true,
			title: data.title || '',
			imageHref: '/img/googlemap/firstaid.png',
			imageSize: [32, 37],
			imageOffset: [-16, -37],
			baloonContent: null,
			listeners: {},
			additionalInfo: {
				type: 'callPoint'
			}
		} )
		
	},
	setLpuBuildingMarker: function(data) {
		var map = this.getPanelByType(this.curMapType);

		if (typeof map.setLpuBuildingMarker != 'function' || data === null) {
			return false;
		}
		if(!map.getMap()){
			map.on('mapIsReady', function(){
				map.setLpuBuildingMarker( data)
			})
		} else {
			return map.setLpuBuildingMarker( data)
		}

	},
	/*
	* @public Установка маркеров талона вызова CmpCallCard на карту
	* 
	*/
	setCmpCallCardMarkerInList: function( record , callback ) {
		var point = [],
			map = this.getPanelByType(this.curMapType),
			mapWrapperPanel = this,
			cmpCardId = record.get('CmpCallCard_id'),
			lat = record.get('UnAdress_lat'),
			lng = record.get('UnAdress_lng'),
			addMarkerInList = function(cardId,pnt,rec){
				
				if (!map.isRendered) return false;
				
				var mark = map.accidentMarkerList[cardId];
				if(mark){
					if( (mark.point[0]!=lat )&& (mark.point[1]!=lng ) && mark.mapMarker ){
						map.removeMarker(mark.mapMarker);
					}
					else{return false;}
				}
				//тут как получается - если маркер есть и координаты не совпадают
				//то удаляем старый маркер и ставим новый
				//если маркер есть и координаты совпадают выходим и ничего не делаем
				//если марекра нет то добавляем
				map.accidentMarkerList[cardId] = {
					id: cardId,
					point: pnt,
					imageSize: [30,32],
					imageOffset: [-16,-37],
					imageHref: mapWrapperPanel.getAccidentMarkerUrl(record.getData()),
					//imageHref: '/img/googlemap/firstaid.png',
					title: rec.get('Adress_Name') || ''
				}
				map.accidentMarkerList[cardId].mapMarker = map.addMarker(map.accidentMarkerList[cardId]);
			}
			
		if (!(record instanceof Ext.data.Model)) {
			return;
		}

		if(!record.data.CmpCallCardStatusType_Code || record.data.CmpCallCardStatusType_Code.inlist([4,5,6,9,12])){
			if (!record.data.CmpCallCard_id) return
			if (map.accidentMarkerList[record.data.CmpCallCard_id]) {
				map.map.geoObjects.remove(map.accidentMarkerList[record.data.CmpCallCard_id].mapMarker)
			}
		}

		if(!record.data.CmpCallCardStatusType_Code || record.data.CmpCallCardStatusType_Code.inlist([4,5,6,9,12])) return
		// Если адрес вызова определён справочником неформализованных адресов,
		// значит координаты у него уже известны
		if (lat && lng) {
			point = [ lat, lng ];
			
			addMarkerInList(cmpCardId, point, record)

			callback( point );
			return;
		}
		// Иначе получаем адрес необходимого формата из записи и получаем
		// координаты из методов геокодирования конкретного сервиса		
		map._getAddressForGeocodeByCmpCallCardRecord( record , function(address){
			if (!address) return;
			
			map.geocode( address , function( point ) {				
				if ( !(point instanceof Array) || !point[0] || !point[1]) return;
				
				addMarkerInList(cmpCardId, point, record)

				callback( point );
			});
		});
	},
	removeOldMarkers: function(store) {
		var map = this.getPanelByType(this.curMapType);
	
		if (map.map) {
			Ext.Object.each(map.accidentMarkerList, function (id, item, object) {
				var remove = true;
				
				store.data.items.forEach(function (cmp, index, array) {
					if (cmp.data.CmpCallCard_id == item.id) {
						remove = false;
					}
				})

				if (remove) map.map.geoObjects.remove(item.mapMarker)
			})
		}
	},
	// Метод установки маркера бригады СМП (добавление / редактирование)
	setAmbulanceMarker: function( marker ) {
		var openedMapPanel = this.getPanelByType(this.curMapType);

		if(openedMapPanel.isRendered){
			if (typeof (openedMapPanel.setAmbulanceMarker) == 'function'  ) {
				openedMapPanel.setAmbulanceMarker(marker);
				return true;
			} else  {
				return false;
			}
		}
	},
	//Метод удаления маркеров бригады, которые отсутвуют в массиве markerList
	deleteOldAmbulanceMarkers: function(markerList){
		var openedMapPanel = this.getPanelByType(this.curMapType);

		if(openedMapPanel.isRendered){
			if (typeof (openedMapPanel.deleteOldAmbulanceMarkers) == 'function'  ) {
				openedMapPanel.deleteOldAmbulanceMarkers(markerList);
				return true;
			} else  {
				return false;
			}
		}
	},
	// Метод удаления маркера бригады СМП
	deleteAmbulanceMarker: function( marker ) {
		if (typeof (this.getPanelByType(this.curMapType).deleteAmbulanceMarker) == 'function'  ) {
			this.getPanelByType(this.curMapType).deleteAmbulanceMarker(marker);
		}
		return;
		
	}, 
	// Метод редактирования маркера бригады СМП
	updateAmbulanceMarker: function( marker ) {
		if (typeof (this.getPanelByType(this.curMapType).updateAmbulanceMarker) == 'function'  ) {
			this.getPanelByType(this.curMapType).updateAmbulanceMarker(marker);
		}
		return;
		
	}, 
	// Метод добавления маркера бригады СМП
	addAmbulanceMarker: function( marker ) {
		if (typeof (this.getPanelByType(this.curMapType).addAmbulanceMarker) == 'function'  ) {
			this.getPanelByType(this.curMapType).addAmbulanceMarker(marker);
		}
		return;
		
	}, 
	setVisibleAmbulanceMarker: function() {
		if (typeof (this.getPanelByType(this.curMapType).setVisibleAmbulanceMarker) == 'function'  ) {
			this.getPanelByType(this.curMapType).setVisibleAmbulanceMarker.apply(this.getPanelByType(this.curMapType), arguments);
		}
		return;
		
	},
	// Отображение маркеров на карте по списку
	setVisibleAmbulanceMarkersByIdList: function( transportlist ) {
		
		if (!(transportlist instanceof Array) ) {
			return false;
		}
		
		for (var i = 0; i < transportlist.length; i++) {
			
			if (!transportlist[i].id) {
				continue;
			}
			
			this.setVisibleAmbulanceMarker( transportlist[i].id, transportlist[i].visible )
			
		};
		
	},

	initComponent: function() {
		
		Ext.applyIf(this,{
			
			items: [
			{
				xtype: 'swsmpgooglemappanel',
				title: (this.initialConfig.toggledButtons) ? null : 'Google Maps',
				type: 'google',
				hidden: this.getDefaultUserMap()=='google' ? false : true,
				flex: 1
			},{
				xtype: 'swsmpyandexmappanel',
				title: (this.initialConfig.toggledButtons) ? null :'Yandex Maps',
				type: 'yandex',
				hidden: this.getDefaultUserMap()=='yandex' ? false : true,
				flex: 1
			},{
				xtype: 'swsmposmmappanel',
				title: (this.initialConfig.toggledButtons)? null :'osm',
				type: 'osm',
				hidden: this.getDefaultUserMap()=='osm' ? false : true,
				flex:1
			},{
				xtype:  !'wialon'.inlist(this.getDefaultTypeList()) ? 'panel' : 'swsmpwialonmappanel',
				title: (this.initialConfig.toggledButtons)? null : 'Wialon',
				type: 'wialon',				
				hidden: this.getDefaultUserMap()=='wialon' ? false : true,				
				flex:1
			},
			{
				xtype:  !'here'.inlist(this.getDefaultTypeList()) ? 'panel' : 'swsmpheremappanel',
				title: (this.initialConfig.toggledButtons)? null : 'Here',
				type: 'here',				
				hidden: this.getDefaultUserMap()=='here' ? false : true,				
				flex:1
			}
			]
		})
		
		this.callParent(arguments);	
	}
})


//а здесь хранится экспериментальная хр.. панель, которая 
//(по моим задумкам) должна быть суперуниверсальным инструментом для работы с картами
//и так, приступим...


		
Ext.define('sw.callCardsPanel', {
	alias: 'widget.callCardsPanel',
	extend: 'Ext.panel.Panel',
	layout: {
		type: 'border'
	},
	statics: {
		getGroupName: function(id, rows, count){
			var emptyGroup = rows[0].data.CmpCallCard_id,
				title = '',
				numrecords = 0;
				
			if (emptyGroup==0 && count==1){
				numrecords = ' (нет записей)'
			}
			else{count > 1 ? numrecords = ' ('+count+' записей)' : numrecords = ' ('+count+" запись)"}			
			switch(id){
				//case 1: {title = 'Принятые звонки '+numrecords;break;}
				case 2: {title = 'Принятые ДН'+numrecords;break;}
				case 3: {title = 'Переданные в СМП'+numrecords;break;}
				//case 4: {title = 'Обслужены в СМП'+numrecords;break;}
				//case 5: {title = 'Переданные в НМП'+numrecords;break;}
				//case 6: {title = 'Принятые НМП'+numrecords;break;}
				//case 7: {title = 'Обслужены НМП'+numrecords;break;}
				//case 8: {title = 'Отклонены НМП'+numrecords;break;}
				case 9: {title = 'Отказ'+numrecords;break;}
				case 10: {title = 'Закрытые'+numrecords;break;}
			}			
			return title
		}
	},
	
	initComponent: function() {
		var me = this
		
		var callsCardsGrid = Ext.create('Ext.grid.Panel', {
			//
			region: 'center',
			autoScroll: true,
			stripeRows: true,
			refId: 'callsCardsGrid',
			//xtype: 'callsCardsGrid',
			features: [{
				ftype: 'grouping',
				groupHeaderTpl: '{[sw.callCardsPanel.getGroupName(values.name, values.rows, values.rows.length)]}',
				hideGroupedHeader: false,
				startCollapsed: true
				//groupHeaderTpl: '{name} ({rows.length} Item{[values.rows.length > 1 ? "s" : ""]})'
			}],
			viewConfig: {
				loadingText: 'Загрузка'
			},


			filterResults: function(){
				
				this.store.clearFilter(false);
				var fam = me.down('transFieldDelbut[name=filterByFamily]').getValue();
				var nam = me.down('transFieldDelbut[name=filterByName]').getValue();
				var sam = me.down('transFieldDelbut[name=filterBySecName]').getValue();
				var db = me.down('datefield[name=filterByBirthDate]').getRawValue();
				this.store.filter('Person_Surname', fam);
				this.store.filter('Person_Firname', nam);
				this.store.filter('Person_Secname', sam);
				this.store.filter('Person_Birthday', db);
		//		this.store.filter('CmpCallCard_id', '0')
		//		this.store.filter({
		//			filterFn: function(item) {
		//				if(
		//					(
		//							(item.get("CmpCallCard_id") != 0 )
		//						&&	(item.get("Person_Surname") == fam )
		//						&& (item.get("Person_Firname") == nam) 
		//						&& (item.get("Person_Secname") == sam))
		//					)
		//					{
		//						return item
		//					}
		//			}
		//		})

			},

			listeners:{
				celldblclick: function(cmp, td, cellIndex, record, tr, rowIndex, e, eOpts){
					actionsToolbar.down('button[itemId=showCard]').fireHandler()
				}
			},


			store: new Ext.data.JsonStore({
				autoLoad: false,
				storeId: 'CmpCallsStoreInCCPanel',
				groupField: 'CmpGroupName_id',
				fields: [
					{name: 'CmpCallCard_id', type: 'int'},
					{name: 'Person_id', type: 'int'},
					{name: 'PersonEvn_id', type: 'int'},
					{name: 'Server_id', type: 'int'},
					{name: 'Person_Surname', type: 'srting'},
					{name: 'Person_Firname', type: 'srting'},
					{name: 'Person_Secname', type: 'srting'},
					{name: 'pmUser_insID', type: 'srting'},
					{name: 'CmpCallCard_prmDate', type: 'string'},
					{name: 'CmpCallCard_Numv', type: 'string'},
					{name: 'CmpCallCard_Ngod', type: 'string'},
					{name: 'CmpCallCard_isLocked', type: 'int'},
					{name: 'Person_FIO', type: 'string'},
					{name: 'Person_Birthday', type: 'string'},
					{name: 'CmpReason_Name', type: 'string'},
					{name: 'Adress_Name', type: 'string'},
					{name: 'CmpCallType_Name', type: 'string'},
					{name: 'CmpGroup_id',type: 'int'},					
					{name: 'CmpGroupName_id', type: 'int'},
					{name: 'CmpLpu_Name', type: 'string'},
					{name: 'CmpDiag_Name', type: 'string'},
					{name: 'StacDiag_Name', type: 'string'},
					{name: 'SendLpu_Nick', type: 'string'},
					{name: 'PPDUser_Name', type: 'string'},
					{name: 'ServeDT', type: 'string'},
					{name: 'PPDResult',	type: 'string'}	
				],
				proxy: {
					limitParam: undefined,
					startParam: undefined,
					paramName: undefined,
					pageParam: undefined,
					type: 'ajax',
					//url: '/?c=CmpCallCard4E&m=loadSMPCmpCallCardsList',
					url: '/?c=CmpCallCard4E&m=loadSMPDispatchStationWorkPlace',
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
				,
				listeners: {
					load: function(me, records, successful, eOpts){
						
					for(var j=1; j<11; j++) {
						if(!me.getGroups(j)){
							if (
								j == 2 ||
								j == 3 ||								
								j == 9 ||
								j == 10
							) me.add({CmpGroupName_id: j});
						}}
					}
				}

			}),
			columns: [
				{dataIndex: 'CmpCallCard_id', text: 'ID', key: true, hidden: true, hideable: false},
				{dataIndex: 'Person_id', hidden: true, hideable: false},
				{dataIndex: 'PersonEvn_id', hidden: true, hideable: false},
				{dataIndex: 'Server_id', hidden: true, hideable: false},
				{dataIndex: 'Person_Surname', hidden: true, hideable: false},
				{dataIndex: 'Person_Firname', hidden: true, hideable: false},
				{dataIndex: 'Person_Secname', hidden: true, hideable: false},
				{dataIndex: 'pmUser_insID', hidden: true, hideable: false},
				{dataIndex: 'CmpCallCard_isLocked', hidden: true, hideable: false},
				{dataIndex: 'CmpCallCard_prmDate', type: 'date', renderer: Ext.util.Format.dateRenderer('d.m.Y H:i'), text: 'Дата время', width: 110},
				{dataIndex: 'CmpCallCard_Numv', sortable: false, text: '№ вызова(за день)', width: 120},
				{dataIndex: 'CmpCallCard_Ngod', sortable: false, text: '№ вызова(за год)', width: 120},
				{dataIndex: 'Person_FIO', sortable: false, text: 'Пациент', width: 220},
				{dataIndex: 'Person_Birthday', sortable: false, text: 'Дата рождения', width: 100},
				{dataIndex: 'CmpCallType_Name', sortable: false, text: 'Тип вызова', width: 160},
				{dataIndex: 'CmpReason_Name',sortable: false,  text: 'Повод', width: 190},
				{dataIndex: 'Adress_Name',sortable: false,  text: 'Адрес', width: 350},
			//	{dataIndex: 'CmpLpu_Name', sortable: false, text: 'ЛПУ прикрепления', width: 120},
			//	{dataIndex: 'CmpDiag_Name',sortable: false,  text: 'Диагноз СМП', width: 100},
			//	{dataIndex: 'StacDiag_Name',sortable: false,  text: 'Диагноз стационара', width: 130},
			//	{dataIndex: 'SendLpu_Nick', sortable: false, text: 'ЛПУ передачи', width: 100},
			//	{dataIndex: 'PPDUser_Name', sortable: false, text: 'Принял', width: 200},
			//	{dataIndex: 'ServeDT', sortable: false, text: 'Обслужено', width: 150},
			//	{dataIndex: 'PPDResult', sortable: false, text: 'Результат обработки в НМП', width: 300, render: function(v, p, r) {
			//		return '<p title="'+v+'">'+v+'</p>';
			//	}},
				{dataIndex: 'CmpGroup_id', hidden: true, hideable: false},
				{dataIndex: 'CmpGroupName_id', hidden: true, hideable: false}
			]
		})
		//end grid
		
		me.on('render', function(){
			//grid actions
			var actionsButttons = me.initialConfig.actions
			actionsButttons.forEach(function (el, index, array){
				switch (el){
					case 'view' : {actionsToolbar.getComponent('showCard').show();break}
					case 'edit' : {actionsToolbar.getComponent('editCard').show();break}
					case 'add' : {actionsToolbar.getComponent('addCard').show();break}
					case 'delete' : {actionsToolbar.getComponent('deleteCard').show();break}
					case 'refresh' : {actionsToolbar.getComponent('refreshGrid').show();break}
					case 'print' : {actionsToolbar.getComponent('printListCards').show();break}					
					case 'confirm' : {actionsToolbar.getComponent('confirmCard').show();break}
					case 'discard' : {actionsToolbar.getComponent('discardCard').show();break}
					case 'served' : {actionsToolbar.getComponent('setServedCard').show();break}
					case 'closeCard' : {actionsToolbar.getComponent('closeCard').show();break}
					case 'print110' : {actionsToolbar.getComponent('print110').show();break}
					case 'abort' : {actionsToolbar.getComponent('abortCard').show();break}
					case 'passToNMP' : {actionsToolbar.getComponent('passCardToNMP').show();break}
				}
			})
			//callsCardsGrid.addDocked(actionsToolbar)
			
			//leftToolsPanel
			var toolsButtons = me.initialConfig.tools;
			toolsButtons.forEach(function (el, index, array){
				switch (el){					
					case 'farmacyRegisterWindow' : {leftToolsPanel.getComponent('buttonFarmacyRegisterWindow').show();break}						
					case 'smpEmergencyTeamOperEnvWindow' : {leftToolsPanel.getComponent('buttonSmpEmergencyTeamOperEnvWindow').show();break}
					case 'smpEmergencyTeamSetDutyWindow' : {leftToolsPanel.getComponent('buttonSmpEmergencyTeamSetDutyWindow').show();break}
					case 'dispatchOperEnvWindow' : {leftToolsPanel.getComponent('buttonDispatchOperEnvWindow').show();break}
					case 'smpWaybillsViewWindow' : {leftToolsPanel.getComponent('buttonSmpWaybillsViewWindow').show();break}
					case 'unformalizedAddressDirectoryEditWindow' : {leftToolsPanel.getComponent('buttonUnformalizedAddressDirectoryEditWindow').show();break}
					case 'reportEndUserWindow' : {leftToolsPanel.getComponent('buttonReportEndUserWindow').show();break}
					case 'DecigionTreeEditWindow' : {leftToolsPanel.getComponent('buttonDecigionTreeEditWindow').show();break}
					
				}
			})
		})
				
		var datesToolbar = Ext.create('Ext.toolbar.Toolbar', {
			region: 'north',
			//height: 29,
			items: [
				Ext.create('sw.datePrevDay'),
				Ext.create('sw.datePickerRange'),
				Ext.create('sw.dateNextDay'),
				Ext.create('sw.dateCurrentDay'),
				Ext.create('sw.dateCurrentWeek'),
				Ext.create('sw.dateCurrentMonth'),
				{ xtype: 'tbseparator' },
				{
					xtype: 'button',
					itemId: 'showCard',
					iconCls: 'view16',
					text: 'Просмотр',
					handler: function(){
						var cardid = callsCardsGrid.getView().getSelectionModel().selected.items[0].get('CmpCallCard_id')
						
						if (cardid)
						{
							Ext.create('sw.callCardWindow', 
							{
								view: 'view', 
								card_id: cardid
							}).show()
						}
					}
				},
				{
					xtype: 'button',
					itemId: 'refreshGrid',
					text: 'Обновить',
					iconCls: 'refresh16',				
					handler: function(){
						callsCardsGrid.store.reload()
					}
				},
				{
					xtype: 'button',
					itemId: 'printListCards',
					text: 'Печать списка',
					iconCls: 'print16',
					handler: function(){
						Ext.ux.grid.Printer.print(callsCardsGrid)
					}
				},
			]
		})
		
		var filterToolbar = Ext.create('Ext.toolbar.Toolbar',{
			region: 'north',
			items: [
			{
				xtype: 'fieldset',
				padding: '0 2 4 2',
				collapsible: true,
	//			collapsed: true,
				title: 'Фильтры',
				layout: 'hbox',
				items: [
					{
						xtype: 'transFieldDelbut',
						fieldLabel: 'Фамилия',
						name: 'filterByFamily',
						displayField: 'Person_Surname',
						storeName: 'CmpCallsStoreInCCPanel',
						enableKeyEvents : true,					
						listeners: {
							keypress: function(c, e, o){
							if ( (e.getKey() == 13))
							{
								callsCardsGrid.filterResults()
							}}
						}
					}
					,{
						xtype: 'transFieldDelbut',
						fieldLabel: 'Имя',
						name: 'filterByName',
						displayField: 'Person_Firname',
						storeName: 'CmpCallsStoreInCCPanel',
						enableKeyEvents : true,
						listeners: {
							keypress: function(c, e, o){
							if ( (e.getKey() == 13))
							{
								callsCardsGrid.filterResults()
							}}
						}
					}
					,{
						xtype: 'transFieldDelbut',
						fieldLabel: 'Отчество',
						name: 'filterBySecName',
						displayField: 'Person_Secname',
						storeName: 'CmpCallsStoreInCCPanel',
						enableKeyEvents : true,
						listeners: {
							keypress: function(c, e, o){
							if ( (e.getKey() == 13))
							{
								callsCardsGrid.filterResults()
							}}
						}
					},
					{
						xtype: 'datefield',
						width: 200,
						format: 'd.m.Y',
						labelAlign: 'right',
						labelWidth: 100,
						name: 'filterByBirthDate',
						enableKeyEvents : true,
						plugins: [new Ux.InputTextMask('99.99.9999')],
						fieldLabel: 'Дата рождения',
						//потому что гладиолус... выставляет ему св-во элемента тулбара с большей высотой
						listeners:{
							render: function(){this.triggerEl.setHeight(22)},
							keypress: function(c, e, o){
							if ( (e.getKey() == 13))
							{
								callsCardsGrid.filterResults()
							}}
						}
					},
					{
						xtype: 'button',
						text: 'Найти',
						iconCls: 'search16',
						margin: '0 0 0 10',
						handler: function() {
							callsCardsGrid.filterResults()
						}
					},
					{
						xtype: 'button',
						text: 'Сброс',
						iconCls: 'resetsearch16',
						margin: '0 0 0 10',
						handler: function() {
							callsCardsGrid.store.clearFilter(false);
						}
					}
				]
			}]
		})
		
		var leftToolsPanel = Ext.create('sw.ScrollableButtonPanel', {
			//require: (['widget.window', 'layout.border', 'Ext.data.Connection']),
			height: 60,
			width: 45,
			items: [{
				itemId: 'buttonFarmacyRegisterWindow',
				iconCls: 'dlo32 rt90', closable: false, width: 45, height: 42, tooltip: 'Учет лекарственных средств', hidden: true,
				handler: function() {
					Ext.create('sw.tools.swSmpFarmacyRegisterWindow').show();
				} 
			},{
				itemId: 'buttonSmpEmergencyTeamOperEnvWindow', 
				iconCls: 'emergency-list32 rt90', closable: false, width: 45, height: 42, tooltip: 'Оперативная обстановка по бригаде', hidden: true,
				handler: function() {
					Ext.create('sw.tools.swEmergencyTeamOperEnv').show();
				} 
			},{
				itemId: 'buttonSmpEmergencyTeamSetDutyWindow',
				iconCls: 'setduty32 rt90', closable: false, width: 45, height: 42, tooltip: 'Отметка о выходе бригад СМП', hidden: true,
				handler: function() {
					Ext.create('sw.tools.swSmpEmergencyTeamSetDutyTimeWindow').show();
				}
			},{
				itemId: 'buttonDispatchOperEnvWindow',  
				iconCls: 'users32 rt90', closable: false, width: 45, height: 42, tooltip: 'Оперативная обстановка по диспетчерам', hidden: true,
				handler: function() {
					Ext.create('sw.tools.swDispatchOperEnvWindow').show();
				}  
			},{
				itemId: 'buttonSmpWaybillsViewWindow', 
				iconCls: 'reports32 rt90', closable: false, width: 45, height: 42, tooltip: 'Учет путевых листов и ГСМ', hidden: true,
				handler: function() {
					Ext.create('sw.tools.swWaybillsWindow').show();
				}  
			},{
				itemId: 'buttonUnformalizedAddressDirectoryEditWindow',
				iconCls: 'reports32 rt90', closable: false, width: 45, height: 42, tooltip: 'Редактирование справочника неформ. адресов', hidden: true,
				handler: function() {
					Ext.create('sw.tools.swUnformalizedAddressDirectoryEditWindow').show();
				}  
			},{
				itemId: 'button3',
				iconCls: 'doubles32 rt90', closable: false, width: 45, height: 42, tooltip: 'Привязка бригад Wialon', hidden: true
			},{
				itemId: 'buttonReportEndUserWindow',
				iconCls: 'report32 rt90', closable: false, width: 45, height: 42, tooltip: 'Просмотр отчетов', hidden: true
			},{
				itemId: 'buttonDecigionTreeEditWindow',
				iconCls: 'structure32 rt90',
				closable: false, 
				width: 45, 
				height: 42, 
				tooltip: 'Редактирование дерева решений', 
				hidden: true,
				handler: function() {
					getWnd('swDecigionTreeEditWindow').show();
				}
			}]
		});
		
		var leftToolsPanelWrapper = Ext.create('Ext.panel.Panel', {
			region: 'west',
			width: 46,
			layout: 'fit',
			collapsible: true,
			collapseDirection: 'left',
			title: '',
			items: [
				leftToolsPanel
			]
		})
		
		var actionsToolbar = Ext.create('Ext.toolbar.Toolbar', {
			dock: 'top',
			layout: 'hbox',
			items: [
				{
					xtype: 'button',
					itemId: 'showCard',
					iconCls: 'view16',
					text: 'Просмотр',
					hidden: true,
					handler: function(){
						var cardid = callsCardsGrid.getView().getSelectionModel().selected.items[0].get('CmpCallCard_id')
						
						if (cardid)
						{
							Ext.create('sw.callCardWindow', 
							{
								view: 'view', 
								card_id: cardid
							}).show()
						}
					}
				},
				{
					xtype: 'button',
					itemId: 'editCard',
					text: 'Изменить',
					iconCls: 'edit16',
					hidden: true
				},
				{
					xtype: 'button',
					itemId: 'addCard',
					text: 'Добавить',
					iconCls: 'add16',
					hidden: true
				},
				{
					xtype: 'button',
					itemId: 'deleteCard',
					text: 'Удалить',
					iconCls: 'delete16',
					hidden: true
				},
				{
					xtype: 'button',
					itemId: 'refreshGrid',
					text: 'Обновить',
					iconCls: 'refresh16',
					hidden: true,
					handler: function(){
						callsCardsGrid.store.reload()
					}
				},
				{
					xtype: 'button',
					itemId: 'printListCards',
					text: 'Печать списка',
					iconCls: 'print16',
					hidden: true,
					handler: function(){
						Ext.ux.grid.Printer.print(callsCardsGrid)
					}
				},
				{
					xtype: 'button',
					itemId: 'confirmCard',
					text: 'Принять',
					//iconCls: 'edit16',
					hidden: true
				},
				{
					xtype: 'button',
					itemId: 'discardCard',
					text: 'Отклонить',
					//iconCls: 'add16',
					hidden: true
				},
				{
					xtype: 'button',
					itemId: 'setServedCard',
					text: 'Обслужено',
					//iconCls: 'delete16',
					hidden: true
				},
				{
					xtype: 'button',
					itemId: 'closeCard',
					text: 'Закрыть карту вызова',
					//iconCls: 'refresh16',
					hidden: true
				},
				{
					xtype: 'button',
					itemId: 'print110',
					text: 'Печать 110у',
					//iconCls: 'print16',
					hidden: true
				},
				{
					xtype: 'button',
					itemId: 'abortCard',
					text: 'Отказ',
					//iconCls: 'refresh16',
					hidden: true
				},
				{
					xtype: 'button',
					itemId: 'passCardToNMP',
					text: 'Передать в НМП',
					//iconCls: 'print16',
					hidden: true
				}
				
			]
		})
		//end actionsToolbar
		
		Ext.applyIf(me, {
			items: [
				datesToolbar,
			//	filterToolbar,
			//	leftToolsPanelWrapper,
				callsCardsGrid
			]
		})
		
		var dt = new Date(Date.now())
		callsCardsGrid.store.load({
		params:{
				//begDate: '10.12.2013',
				begDate: Ext.Date.format(dt, 'd.m.Y'),
				endDate: Ext.Date.format(dt, 'd.m.Y')
		}
		})
		

		me.callParent(arguments);
	}	
});

/*
Оставил здесь недоработанный кастомный компонент на основе Ext.view.View
еСТЬ группировка, сортировка, быстро грузится, но остались нерешенные проблемы
скролл неадекватно отрабатывает, особенно при ресайзе
непонятки с flex
масштабируемые колонки по ширине не сделаны
возможны проблемы при группировки - тк функционал протестирован не полностью
*/

Ext.define('sw.simpleGroupedView',{
    alias:'widget.simpleGroupedView',
    extend: 'Ext.view.View',
    itemSelector: '.thumb-wrap',
    componentCls: 'simpleGroupedView',
    columns: [],
    html: '',
    selectedItemCls: 'x-view-row-selected',
    overItemCls: 'x-view-row-hovered',
    collapsedGroups: [],
    enableFilters: false,
    layout: 'hbox',

    initComponent: function() {
        var cmp = this;

        cmp.addEvents({
            groupClick: true,
            headerClick: true,
            headerTriggerClick: true
        });

        cmp.on("boxReady", function(){
            debugger;
        })
        cmp.on("resize", function(cmp, width, height, oldWidth, oldHeight, eOpts){
            debugger;
        })

        cmp.on("render", function(){
            //простановка текстовых полей - фильтров
            if(cmp.enableFilters){

                cmp.columns.forEach(function(value, index, arr) {

                        var columnEl = cmp.getFilterColumnEl(index);

                    var fieldFilter = (value.filter && value.filter.filterBy) ?  value.filter.filterBy : value.dataIndex;

                    var defaultWidgetOpts = {
                            xtype: 'transFieldDelbut',
                            translate: false,
                            renderTo: columnEl,
                            hideLabel: true,
                        flex: 1,
                       // enableKeyEvents: true,
                        listeners: {
                            specialkey: function(field, e){
                                if (e.getKey() == e.ENTER) {

                                    var fieldVal = field.getValue() ? field.getValue().toString().toLowerCase() : '';

                                    if(field.getXType() == "swdatefield"){
                                        fieldVal = field.getRawValue();
                                    }

                                    cmp.getStore().removeFilter(fieldFilter, !fieldVal);

                                    if(!fieldVal) return;

                                    var d = Ext.create('Ext.util.Filter',
                                        {
                                            id: fieldFilter,
                                            filterFn: function(rec) {
                                                var recVal = rec.get(fieldFilter) ? rec.get(fieldFilter).toString().toLowerCase() : '';

                                                return (
                                                    !rec.get('CmpCallCard_id') ||
                                                    recVal.indexOf(fieldVal) != -1
                                                )
                                            }
                                        }
                                    );

                                    cmp.getStore().addFilter(d);
                                }
                            },
                            triggerClick: function(){
                                cmp.getStore().removeFilter(fieldFilter);
                            }
                        }
                    };

                    if (value.filter) {
                        //widgetOpts = Object.assign(defaultWidgetOpts, value.filter);
                    };

                    Ext.widget(defaultWidgetOpts);
                        });

            }
        });

        if(cmp.columns){

            var allWidth = 0, countFlex = 0, columnHtml = '';

            //@todo c flex подумать
            cmp.columns.forEach(function(value, index, arr){
                if(value.width){
                    allWidth += value.width;
                }
                else{
                    ++countFlex;
                }
            });

            cmp.flexWidth = 'calc((100% - ' + allWidth + 'px)/' + countFlex + ');';
            cmp.countFlex = countFlex;

            cmp.allWidth = allWidth;

            var colgroupTags = '';

            //вынужденная мера
            //шаблон для хедеров
            cmp.columns.forEach(function(value, index, arr){

               // var colWidth = value.width ? value.width+'px;' : cmp.flexWidth;
                var colWidth = value.width ? value.width+'px;' : '*';
                var multiLineCls = value.multiLine ? 'multiline' : '';

                cmp.columns[index].calcWidth = colWidth;

                var width = value.hidden ? 0 : colWidth;

                columnHtml += '<td class="x-column-header">' +
                    '<div class="x-column-header-inner column-count-' + index + '">' +
                    '<span class="x-column-header-text '+ multiLineCls +'">' + value.text + '</span>' +
                    '<div id="gridcolumn-1271-triggerEl" class="x-column-header-trigger" style=""></div>' +
                        '<div class="filter-wrapper"></div>' +
                    '</div>' +
                    '</td>';

                colgroupTags += '<colgroup align="center"><col class="x-grid-cell-gridcolumn-' + index + '" style="width:' + width + '"></colgroup>';
            });

            cmp.html = '<table class="x-grid-header-ct">' + colgroupTags + '<tr>' + columnHtml +'</tr></table>';
        };

        //запоминание позиции скролла
        if(cmp.preserveScrollOnRefresh){
            cmp.on('beforerefresh', function(a,b,c){
                var curEl = a.getEl().down('.content-wrapper');

                if(curEl){
                    var t = Ext.get(curEl);
                    cmp.oldScrollPos = curEl.getScroll();

                }
            });
            cmp.on('refresh', function(a,b,c){
                var curEl = a.getEl().down('.content-wrapper');

                if(curEl && cmp.oldScrollPos){
                    curEl.el.setScrollTop(cmp.oldScrollPos.top)
                }
            });
        };

        cmp.on('containerclick', function(cmp, event, eOpts){
            var currentEl = event.target;
            var elCls = currentEl.getAttribute('class');

            if (elCls) {
                //схлапываение группы
                if (currentEl.classList.contains('x-grid-group-title')) {
                    var parentEl = Ext.get(currentEl.parentElement);
                    var group = parentEl.next();
                    var groupClass = 'group-id-';
                    var groupId = elCls.substr(elCls.indexOf(groupClass) + groupClass.length,2);

                    cmp.groupClick(parentEl.hasCls('collapsed'), groupId, parentEl, group);

                    return;
                }

                //клик по хедеру
                if (
                    currentEl.classList.contains('x-column-header-inner') ||
                    currentEl.classList.contains('x-column-header-text')
                ) {
                    var currentEl = (currentEl.classList.contains('x-column-header-inner')) ? currentEl : currentEl.parentElement;
                    var elCls = currentEl.getAttribute('class');
                    var columnNum = 'column-count-';
                    var groupNum = elCls.substr(elCls.indexOf(columnNum) + columnNum.length,2);

                    cmp.headerClick(currentEl, groupNum);

                    return;
                }

                if (
                    currentEl.classList.contains('x-column-header-trigger')
                ) {
                    var parentEl = currentEl.parentElement;
                    var elCls = parentEl.getAttribute('class');
                    var columnNum = 'column-count-';
                    var groupNum = elCls.substr(elCls.indexOf(columnNum) + columnNum.length,2);

                    cmp.headerTriggerClick(currentEl, groupNum, event);

                    return;
                }
            }
        });

        cmp.tpl = new Ext.XTemplate(
            '{[ this.getContentWrapper() ]}',
                '<tpl for=".">',
                '{[ this.getOpenTag(values) ]}',
                '{[ this.getContentTag(values, xindex, xcount, this) ]}',
                '{[ this.getCloseTag(values, xindex, xcount) ]}',
                '</tpl>',
            '</div>',
            {
                getContentWrapper: function(){
                    //получение тега для контейнера групп
                    return '<div class="content-wrapper">';
                    //return '<div class="content-wrapper" style="height:'+(this.ownerCt.body.getHeight()-this.el.getHeight())+'px;">';
                }.bind(this),

                getOpenTag: function(val) {
                    //получение тега для группы
                    if( this.prevGroupNum != val.CmpGroup_id) {

                        if(!val.CmpGroup_id) return;

                        var htmlGroup = '';

                        if(!val.CmpCallCard_id){
                            //шаблон для хедера группы
                            var countRecsInGroup = this.store.groups.items[val.CmpGroup_id - 1].records.length - 1;
                            var groupname = '';

                            var collapsedGroup = (this.collapsedGroups && val.CmpGroup_id.inlist(this.collapsedGroups));
                            var collapsedGroupHeaderClass = collapsedGroup ? 'collapsed' : '';
                            var collapsedGroupClass = collapsedGroup ? 'hidden' : '';

                            switch (val.CmpGroup_id) {
                                case 1: groupname = 'Ожидание решения старшего врача'; break;
                                case 2: groupname = 'Внимание'; break;
                                case 3: groupname = 'В работе'; break;
                                case 4: groupname = 'Прочие'; break;
                                case 5: groupname = 'Закрытые'; break;
                                case 6: groupname = 'Отмененные'; break;
                                case 7: groupname = 'Отложенные'; break;
                            }

                            var pref = (val.CmpGroup_id > 1) ? '</table>' : '';

                            htmlGroup += pref +
                                '<div class="x-grid-group-hd x-grid-group-hd-collapsible ' + collapsedGroupHeaderClass + '">' +
                                '<div class="x-grid-group-title group-id-' + val.CmpGroup_id + '">' + groupname + ' (' + countRecsInGroup +')'+ '</div>' +
                                '<div class="thumb-wrap" style="display: none;"></div>'+
                                '</div>';
                        };

                        this.prevGroupNum = val.CmpGroup_id;

                        var colgroupTags = '';

                        this.columns.forEach(function(value, index, arr){
                           // var cellVal = value.format ? Ext.Date.format(new Date(val.CmpCallCard_prmDate), value.format) : val[value.dataIndex];
                            var width = value.hidden ? 0 : value.calcWidth;
                            colgroupTags += '<colgroup align="center"><col class="x-grid-cell-gridcolumn-' + index + '" style="width:' + width + '"></colgroup>';
                        })

                        htmlGroup += '<table class="group ' + collapsedGroupClass + '">' + colgroupTags;

                        return htmlGroup;

                    };
                    this.prevGroupNum = val.CmpGroup_id;
                }.bind(this),

                getContentTag: function(val, xindex, xcount, tmpl) {
                    if(!val.CmpGroup_id) return;
                    if(!val.CmpCallCard_id) return;

                    //шаблон для строки
                    var htmlRow = '<tr class="thumb-wrap x-grid-data-row">';

                    if(this.columns){
                        var flexWidth = 'calc((100% - ' + cmp.allWidth + 'px + 20px)/' + cmp.countFlex + ');';
                        this.columns.forEach(function(value, index, arr){
                            var cellVal = value.format ? Ext.Date.format(new Date(val.CmpCallCard_prmDate), value.format) : val[value.dataIndex];
                            var width = value.width ? value.width+'px;' : flexWidth;
                            htmlRow += '<td class="x-grid-cell x-grid-td x-unselectable">' +  cellVal + "</td>";
                        })
                    }
                    return htmlRow + '</tr>';
                }.bind(this),

                getCloseTag: function(val, xindex, xcount) {
                    //замыкающая функция
                    if(xindex == xcount){
                        return '</table>';
                    }
                }.bind(this),
                prevGroupNum: 0
            }
        );

        Ext.applyIf(cmp);
        cmp.callParent(arguments);
    },

    getHeaderInfo: function(index){
        return this.columns[index];
    },

    getFilterColumnEl: function(index){
        return this.el.down('.x-grid-header-ct .column-count-' + index + ' .filter-wrapper');
    },

    getColumnEl: function(index){
        return this.el.down('.x-grid-header-ct .column-count-' + index);
    },

    headerTriggerClick: function(el, num, event) {
        var cmp = this;

        var subMenu = Ext.create('Ext.menu.Menu', {
            plain: true,
            renderTo: Ext.getBody(),
            items: [
                {
                    text: 'Столбцы',
                    cls: "x-cols-icon",
                    menu: {
                        xtype: 'menu',
                        itemId: 'setVisibleColumns',
                        items: [

                        ]
                    }
                }
            ]
        });

        var subMenuEmergencyStatuses = subMenu.down('menu[itemId=setVisibleColumns]');

        cmp.columns.forEach(function(value, index, arr){
            subMenuEmergencyStatuses.add({
                xtype: 'menucheckitem',
                text:  value.text,
                value: index,
                checked: value.hidden ? false : true,
                listeners: {
                    checkchange: function( item, checked, eOpts ){
                        cmp.columns[item.value].hidden = !checked;

                        var els = Ext.query('.x-grid-cell-gridcolumn-'+ item.value);

                        els.forEach(function(a,b,c){
                            a.style.width = !checked ? '0' : cmp.columns[item.value].calcWidth.slice(0, -1);
                        });

                        var tables = Ext.query('.simpleGroupedView .x-grid-header-ct');

                        tables.forEach(function(a,b,c){
                            a.style.width = a.offsetWidth + 100 + 'px';
                        });

                    }
                }

            });
        });

        subMenu.showAt(event.xy[0],event.xy[1]);

        cmp.fireEvent('headerTriggerClick');
    },

    headerClick: function(el, num) {

        var cmp = this;
        var headerInfo = cmp.getHeaderInfo(+num);
        var headerEl = Ext.get(el);

        headerEl.toggleCls('asc');

        cmp.getStore().sort(headerInfo.dataIndex, headerEl.hasCls('asc') ? 'ASC' : 'DESC');
        cmp.fireEvent('headerClick', headerInfo);
    },

    groupClick: function(expand, groupId, parentEl, group){
        var cmp = this;

        Ext.get(group).toggleCls('hidden');
        Ext.get(parentEl).toggleCls('collapsed');

        //если группа сворачивается - добавляем ее в список свернутых
        if(parentEl.hasCls('collapsed')){
            if(!groupId.inlist(cmp.collapsedGroups)){
                cmp.collapsedGroups.push(+groupId);
            }
        }
        else{
            if(groupId.inlist(cmp.collapsedGroups)){
                delete cmp.collapsedGroups[cmp.collapsedGroups.indexOf(+groupId)]
            }
        }

        cmp.fireEvent('groupClick', {
            expand: expand,
            groupId: groupId,
            el: parentEl,
            groupEl: group
});
    }
});


	// определение параметров конфигурации панели


	Ext.define('sw.Promed.EvnXmlPanel',{
	extend: 'Ext.panel.Panel',
	alias:'widget.EvnXmlPanel',
	border: false,
	ownerWin: {},
	LpuSectionField: null,
	MedStaffFactField: null,
	options: {
		XmlType_id: null, // Фильтр: Тип документа, который может быть загружен в панель
		EvnClass_id: null // Фильтр: Категория документа, который может быть загружен в панель и категория шаблонов, которые могут быть выбраны для документа
	},
	viewOptions: {
		isWithSelectXmlTemplateBtn: true,
		isWithRestoreXmlTemplateBtn: true,
		isWithEvnXmlClearBtn: true,
		isWithPrintBtn: true
	},
	onClickPrintBtn: function(panel) {
		// По умолчанию используем стандартный обработчик
		panel.doPrint();
	},
	onAfterClearViewForm: function(panel) {
		//
	},
	onAfterLoadData: function(panel) {
		//
	},
	listeners: {
		expand: function(panel) {
			if ( panel.isLoaded === false ) {
				panel.isLoaded = true;
			}
			panel.doLayout();
		}.bind(this)
	},
	/**
	 * Метод, в котором должна быть выполнена проверка, что учетный документ сохранен
	 * Если не сохранен, то должно выполниться сохранение и установка базовых параметров в панели
	 * Если сохранен и есть базовые параметры, то должен быть выполнен указанный метод
	 *
	 * @param {sw.Promed.EvnXmlPanel} panel Экземпляр панели
	 * @param {String} method �?мя метода панели
	 * @param {*} params Параметры для метода (опционально)
	 * @return {Boolean}
	 */
	onBeforeCreate: function (panel, method, params) {
		if (!panel || !method || typeof panel[method] != 'function') {
			return false;
		}
		panel[method](params);
		return true;
	},
	// конец определения параметров конфигурации панели

	baseParams: {
		Evn_id: null,
		UslugaComplex_id: null,
		Server_id: null,
		userMedStaffFact: {}
	},

	// параметры документа
	_EvnXml_id: null,
	_XmlTemplate_id: null,
	_XmlTemplateType_id: null,
	_xml_data: null,
	_isReadOnly: false,
	/**
	 * Признак, что документ загружен
	 */
	_isLoaded: false,

	// методы для базовых параметров
	setBaseParams: function(obj) {
		this.baseParams.Evn_id = obj.Evn_id;
		this.baseParams.userMedStaffFact = obj.userMedStaffFact || null;
		this.baseParams.Server_id = obj.Server_id || null;
		this.baseParams.UslugaComplex_id = obj.UslugaComplex_id || null;
	},
	getUserMedStaffFact: function() {
		if (this.baseParams.userMedStaffFact) {
			return this.baseParams.userMedStaffFact;
		} else {
			return sw.Promed.MedStaffFactByUser.last || {};
		}
	},
	getUserMedPersonalId: function() {
		return this.getUserMedStaffFact().MedPersonal_id || null;
	},
	getUserMedServiceId: function() {
		return this.getUserMedStaffFact().MedService_id || null;
	},
	getUserLpuSectionId: function() {
		var umsf = this.getUserMedStaffFact();
		var lpusection_id = umsf.LpuSection_id || null;
		if (!lpusection_id && this.LpuSectionField) {
			lpusection_id = this.LpuSectionField.getValue() || null;
		}
		return lpusection_id;
	},
	getUserMedStaffFactId: function() {
		var umsf = this.getUserMedStaffFact();
		var medstafffact_id = umsf.MedStaffFact_id || null;
		if (!medstafffact_id && this.MedStaffFactField) {
			medstafffact_id = this.MedStaffFactField.getValue() || null;
		}
		return medstafffact_id;
	},
	getServerId: function() {
		return this.baseParams.Server_id;
	},
	getEvnId: function() {
		return this.baseParams.Evn_id;
	},
	getUslugaComplexId: function() {
		return this.baseParams.UslugaComplex_id;
	},
	// конец определения методов для базовых параметров

	getOption: function(name) {
		return this.options[name] || null;
	},
	getFilterEvnClassId: function() {
		return this.options.EvnClass_id || null;
	},

	// методы получения параметров загруженного документа
	getXmlTemplateId: function() {
		return this._XmlTemplate_id;
	},
	getXmlTypeId: function() {
		return this._XmlType_id;
	},
	getXmlTemplateTypeId: function() {
		return this._XmlTemplateType_id;
	},
	getEvnXmlId: function() {
		return this._EvnXml_id;
	},
	getIsFreeDocument: function()
	{
		return (sw.Promed.EvnXml.MULTIPLE_DOCUMENT_TYPE_ID == this.getXmlTypeId());
	},
	getIsVizitProtocol: function()
	{
		return (sw.Promed.EvnXml.EVN_VIZIT_PROTOCOL_TYPE_ID == this.getXmlTypeId());
	},
	getIsUslugaProtocol: function()
	{
		return (sw.Promed.EvnXml.EVN_USLUGA_PROTOCOL_TYPE_ID == this.getXmlTypeId());
	},
	// конец методов получения параметров загруженного документа

	/**
	 * Установлен ли режим "Только просмотр документа"
	 * @return {Boolean}
	 */
	getIsReadOnly: function()
	{
		return this._isReadOnly;
	},
	/**
	 * Устанавливает режим просмотра или редактирования документа
	 * Должно соответствовать режиму доступа к учетному документу
	 * Должно вызываться после загрузки документа
	 * @param {Boolean} is_read_only
	 */
	setReadOnly: function(is_read_only)
	{
		this._isReadOnly = is_read_only;
		this.getToolbarItem('btnXmlTemplateSelect').setVisible(!this._isReadOnly);
		this.getToolbarItem('btnXmlTemplateRestore').setVisible(!this._isReadOnly);
		this.getToolbarItem('btnEvnXmlClear').setVisible(!this._isReadOnly);
		//this.getTopToolbar().setVisible(visible); // добавил, т.к. зачем отображать тулбар, если все кнопки на нём скрыты.

},
getToolbarItem: function(item)
{
	var i = 0;
	var result = null;
	while (i<this.getTopToolbar().items.length && result==null)
	{
		if (this.getTopToolbar().items.item(i).name==item)
		{
			result = this.getTopToolbar().items.item(i);
		}
		i++;
	}
	return result;
},
onEvnSave: Ext.emptyFn,
/**
 * Обработчик загрузки документа в панель
 * @param {string} html Документ в виде строки HTML
 * @param {object} data Документ в виде объекта
 * @return {Boolean}
 * @access private
 */
_onLoadData: function(html, data) {
	this.getToolbarItem('btnXmlTemplateSelect').setDisabled(this.getIsReadOnly());
	this.getToolbarItem('btnXmlTemplateRestore').setDisabled(this.getIsReadOnly());
	this.getToolbarItem('btnEvnXmlClear').setDisabled(this.getIsReadOnly());
	this.getToolbarItem('btnEvnXmlPrint').setDisabled(false);

	this.baseParams.Evn_id = data.Evn_id;
	// data.Evn_pid
	// data.Evn_rid
	// data.EvnClass_id
	// data.EvnXml_Name
	this._EvnXml_id = data.EvnXml_id;
	this._xml_data = data.xml_data;
	this._XmlTemplate_id = data.XmlTemplate_id;
	this._XmlTemplateType_id = data.XmlTemplateType_id;
	this._XmlType_id = data.XmlType_id;

	var tpl = new Ext.XTemplate(html);
	tpl.overwrite(this.body, {});
	this.removeAll();

	// hidePrintOnly
	var node_list = Ext.query("div[class*=printonly]",this.body.dom);
	//log(node_list);
	var i, el;
	for(i=0; i < node_list.length; i++)
	{
		el = new Ext.Element(node_list[i]);
		//log(el);
		el.setStyle({display: 'none'});
	}
	// end hidePrintOnly

	if (this.getIsReadOnly() || !data.xml_data) {
		this.onAfterLoadData(this);
		return true;
	}

	// подсчитаем число разделов документа
	var cnt = 0, section_name;
	for(section_name in data.xml_data) {
		if(typeof data.xml_data[section_name] == 'string') cnt++;
	}
	this.cKEditor = null;
	if (section_name == 'UserTemplateData' && cnt == 1) {
		// нужно редактировать как документ с одним разделом
		var form_tpl = new Ext.XTemplate('');
		form_tpl.overwrite(this.body, {});
		this.cKEditor = Ext.form.CKEditor({
			name: section_name,
			hideLabel: true,
			height: "200",
			value: data.xml_data[section_name]
		});
		this.add({
			xtype: "fieldset",
			autoHeight: true,
			labelAlign: "top",
			region: "center",
			style: "border: 0;",
			items:[
				this.cKEditor
			]
		});
		this.doLayout();
		this.onAfterLoadData(this);
		this.onEvnSave = function() {
			// нужно сохранить возможные изменения
			// в единственном разделе Xml-документа
			// после сохранения учетного документа
			var field = this.cKEditor;
			if (field && field.getCKEditor()) {
				Ext.Ajax.request({
					url: '/?c=EvnXml&m=updateContent',
					callback: function(opt, success, response) {
						if (success && response.responseText != '') {
							var response_obj = Ext.util.JSON.decode(response.responseText);
							//
						}
					},
					params: {EvnXml_id: this.getEvnXmlId(), name: section_name, value: field.getCKEditor().getData(), isHTML: 1}
				});
			} else {
				sw.swMsg.alert('Ошибка', 'Xml-документ не сохранен. Обратитесь к разработчикам программы.');
			}
		}.bind(this);
		return true;
	}
	//this.useCkeditor = false;
	// отрисовка компонентов редактирования параметров с выбором значения
	var pv = new sw.Promed.ParameterValueCollection({
		dom: this.body.dom,
		EvnXml_id: this.getEvnXmlId()
	});
	//log(pv);
	pv.render();

	// отрисовка визуальных редакторов
	var editors = new sw.Promed.swNicEditors(this);
	editors.render(data);

	this.onAfterLoadData(this);
	return true;
},
/**
 * Загрузка документа в панель
 * Предварительно должны быть установлены базовые параметры
 * @param {object} options Параметры для загрузки документа
 */
doLoadData: function(options) {
	if (!options) {
		options = {};
	}
	if (options.EvnXml_id) {
		this._EvnXml_id = options.EvnXml_id;
	}
	if (options.Evn_id) {
		this.baseParams.Evn_id = options.Evn_id;
	}
	this._loadViewForm();
},
/**
 * Открытие печатной формы документа в новом окне
 */
doPrint: function() {
	window.open('/?c=EvnXml&m=doPrint&printHtml=1&EvnXml_id=' + this.getEvnXmlId(), '_blank');
},
/**
 * Обработчик выбора шаблона
 * или в окне поиска и просмотра шаблонов или из списка недавних шаблонов осмотра
 * @param {integer} XmlTemplate_id
 */
onSelectXmlTemplate: function(XmlTemplate_id) {
	this._createEmpty(XmlTemplate_id);
},
/**
 * Выбор шаблона в окне поиска и просмотра шаблонов
 */
doSelectXmlTemplate: function() {
//	getWnd('swPromedBaseForm').show();return;
	getWnd('swTemplSearchWindow').show({
		onSelect: function(params) {
			this.onSelectXmlTemplate(params.XmlTemplate_id);
		}.bind(this),
		LpuSection_id: this.getUserLpuSectionId(),
		MedService_id: this.getUserMedServiceId(),
		MedPersonal_id: this.getUserMedPersonalId(),
		MedStaffFact_id: this.getUserMedStaffFactId(),
		Evn_id: this.getEvnId(),
		UslugaComplex_id: this.getUslugaComplexId(),
		EvnClass_id: this.getOption('EvnClass_id'),
		XmlType_id: this.getOption('XmlType_id')
	});
},
/**
 * Создание документа
 *  из указанного шаблона
 *  или из шаблона по умолчанию
 *  или из базового шаблона этого типа документов
 * Также производится подсчет использования шаблона
 * @param {integer} xmltemplate_id �?дентификатор шаблона
 * @access private
 */
_createEmpty: function(xmltemplate_id) {
	this.ownerWin.getLoadMask().show();
	Ext.Ajax.request({
		url: '/?c=EvnXml&m=createEmpty',
		callback: function(opt, success, response) {
			this.ownerWin.getLoadMask().hide();
			if ( !success || Ext.isEmpty(response.responseText) ) {
				return false;
			}
			var response_obj = Ext.util.JSON.decode(response.responseText);
			if ( !response_obj.EvnXml_id ) {
				return false;
			}
			this._EvnXml_id = response_obj.EvnXml_id;
			this._loadViewForm();
		}.bind(this),
		params: {
			Evn_id: this.getEvnId(),
			XmlType_id: this.getOption('XmlType_id'),
			Server_id: this.getServerId(),
			MedStaffFact_id: this.getUserMedStaffFactId(),
			XmlTemplate_id: xmltemplate_id || null
		}
	});
},
/**
 * Создание копии документа и загрузка созданной копии в панель
 * Учетный документ должен быть создан
 * @param {integer} EvnXml_id �?дентификатор документа
 * @access private
 */
_copy: function(EvnXml_id) {
	var params = {};
	params.EvnXml_id = EvnXml_id;
	params.Evn_id = this.getEvnId();
	this.ownerWin.getLoadMask().show();
	Ext.Ajax.request({
		url: '/?c=EvnXml&m=copy',
		callback: function(opt, success, response) {
			this.ownerWin.getLoadMask().hide();
			if ( !success || Ext.isEmpty(response.responseText) ) {
				return false;
			}
			var response_obj = Ext.util.JSON.decode(response.responseText);
			if ( !response_obj.EvnXml_id ) {
				return false;
			}
			this._EvnXml_id = response_obj.EvnXml_id;
			this._loadViewForm();
		}.bind(this),
		params: params
	});
},
/**
 * Востановление шаблона документа
 */
doRestoreXmlTemplate: function() {
	this.ownerWin.getLoadMask().show();
	Ext.Ajax.request({
		url: '/?c=EvnXml&m=restore',
		callback: function(opt, success, response) {
			this.ownerWin.getLoadMask().hide();
			if ( !success || Ext.isEmpty(response.responseText) ) {
				return false;
			}
			var response_obj = Ext.util.JSON.decode(response.responseText);
			if ( !response_obj.EvnXml_id ) {
				return false;
			}
			this._EvnXml_id = response_obj.EvnXml_id;
			this._loadViewForm();
		}.bind(this),
		params: {
			EvnXml_id: this.getEvnXmlId()
		}
	});
},
/**
 * Очистка разделов документа
 * @return {Boolean}
 */
doClearEvnXml: function() {
	var f,xmldatanew={},flag=false;
	if(this._xml_data) {
		for(var k in this._xml_data) {
			f = (this.input_cmp_list && this.input_cmp_list['field_'+ k+'_'+ this.getEvnXmlId()]) || null;
			if(f) {
				f.setContent('&nbsp;-');
				xmldatanew[k] = '&nbsp;-';
				flag=true;
			}
		}
		if(flag == false)
			return false;
	} else {
		return false;
	}

	this.ownerWin.getLoadMask().show();
	Ext.Ajax.request({
		url: '/?c=EvnXml&m=updateContent',
		callback: function(opt, success, response) {
			this.ownerWin.getLoadMask().hide();
		}.bind(this),
		params: {
			XmlData: Ext.util.JSON.encode(xmldatanew),
			EvnXml_id: this.getEvnXmlId()
		}
	});
	return true;
},
/**
 * Сброс параметров при открытии формы с панелью
 * Должно выполняться вместе с очисткой базовой формы
 */
doReset: function() {
	this._clearViewForm();
	this.removeAll();
	this.baseParams.Evn_id = null;
	this.baseParams.UslugaComplex_id = null;
	this.baseParams.Server_id = null;
	this.baseParams.userMedStaffFact = {};
},
/**
 * Очистка панели от параметров документа
 * @access private
 */
_clearViewForm: function() {
	var tpl = new Ext.XTemplate('');
	tpl.overwrite(this.body, {});
	this._isLoaded = false;
	this._EvnXml_id = null;
	this._XmlTemplate_id = null;
	this._XmlTemplateType_id = null;
	this._XmlType_id = null;
	this._xml_data = null;
	this.getToolbarItem('btnXmlTemplateSelect').setDisabled(this.getIsReadOnly());
	this.getToolbarItem('btnXmlTemplateRestore').setDisabled(true);
	this.getToolbarItem('btnEvnXmlClear').setDisabled(true);
	this.getToolbarItem('btnEvnXmlPrint').setDisabled(true);
	this.onAfterClearViewForm(this);
},
/**
 * Загружает форму документа
 * @access private
 */
_loadViewForm: function () {
	this.ownerWin.getLoadMask().show();
	Ext.Ajax.request({
		url: '/?c=EvnXml&m=doLoadData',
		callback: function(options, success, response) {
			this.onEvnSave = Ext.emptyFn;
			this.ownerWin.getLoadMask().hide();
			if ( !success || Ext.isEmpty(response.responseText) ) {
				return false;
			}
			var response_obj = Ext.util.JSON.decode(response.responseText);
			if ( response_obj.Error_Msg ) {
				return false;
			}
			if ( !response_obj.html || !response_obj.data ) {
				this._onNotFound();
				return false;
			}
			this._isLoaded = true;
			this._onLoadData(response_obj.html, response_obj.data);
			return true;
		}.bind(this),
		params: {
			XmlType_id: this.getOption('XmlType_id'),
			Evn_id: this.getEvnId(),
			EvnXml_id: this.getEvnXmlId()
		}
	});
},
/**
 *
 */
_onNotFound: function() {
	if (this.getUslugaComplexId()) {
		// пробуем создать документ на основе шаблона по умолчанию
		this.ownerWin.getLoadMask().show();
		Ext.Ajax.request({
			url: '/?c=XmlTemplateDefault&m=getXmlTemplateIdByUsluga',
			callback: function(options, success, response) {
				this.ownerWin.getLoadMask().hide();
				if ( !success || Ext.isEmpty(response.responseText) ) {
					return false;
				}
				var response_obj = Ext.util.JSON.decode(response.responseText);
				if ( !Ext.isArray(response_obj) || response_obj.length == 0 ) {
					return false;
				}
				this._createEmpty(response_obj[0].XmlTemplate_id);
			}.bind(this),
			params: {
				UslugaComplex_id: this.getUslugaComplexId()
			}
		});
	}
},
/**
 * Копирование протокола осмотра из предыдущего посещения в этом же талоне
 * @param {integer} Evn_rid �?дентификатор талона
 */
loadLastEvnProtocolData: function(Evn_rid) {
	// пока отменяю этот метод
	if ( true || Ext.isEmpty(Evn_rid) || !this.getFilterEvnClassId().toString().inlist(['11','13']) ) {
		return;
	}
	this.ownerWin.getLoadMask().show();
	// Получаем EvnXml_id протокола последнего посещения в рамках указанного талона и создаем копию
	Ext.Ajax.request({
		url: '/?c=EvnXml&m=getLastEvnProtocolId',
		callback: function(options, success, response) {
			this.ownerWin.getLoadMask().hide();
			if ( !success || Ext.isEmpty(response.responseText) ) {
				return false;
			}
			var response_obj = Ext.util.JSON.decode(response.responseText);
			if ( !Ext.isArray(response_obj) || response_obj.length == 0 ) {
				return false;
			}
			// при добавлении это не получится, сначала надо сохранить посещение
			this._copy(response_obj[0].EvnXml_id);
		}.bind(this),
		params: {
			Evn_rid: Evn_rid,
			EvnClass_id: this.getFilterEvnClassId()
		}
	});
},
/**
 * �?нициализация
 */
initComponent: function() {
	this.tbar = Ext.create('Ext.Toolbar',{
		items: [{
			text:'Выбрать шаблон',
			tooltip:'Выбрать шаблон для документа',
			name:'btnXmlTemplateSelect',
			hidden: !this.viewOptions.isWithSelectXmlTemplateBtn,
			iconCls: 'search16',
			xtype: 'button',
			handler: function() {
				this.onBeforeCreate(this, 'doSelectXmlTemplate');
			}.bind(this)
		},
		{
			text:'Восстановить шаблон',
			tooltip:'Восстановить шаблон документа',
			name:'btnXmlTemplateRestore',
			hidden: !this.viewOptions.isWithRestoreXmlTemplateBtn,
			iconCls: 'template16',
			xtype: 'button',
			handler: function() {
				this.doRestoreXmlTemplate();
			}.bind(this)
		},
		{
			text:'Очистить',
			tooltip:'Очистить разделы документа',
			name:'btnEvnXmlClear',
			hidden: !this.viewOptions.isWithEvnXmlClearBtn,
			iconCls: 'clear16',
			handler: function() {
				this.doClearEvnXml();
			}.bind(this)
		},
		{
			text:'Печать',
			tooltip:'Печать документа',
			name:'btnEvnXmlPrint',
			hidden: !this.viewOptions.isWithPrintBtn,
			iconCls: 'print16',
			xtype: 'button',
			handler: function() {
				// Если определен обработчик, то используется он
				this.onClickPrintBtn(this);
			}.bind(this)
		}]
	});

	this.callParent(arguments);
}
});

sw.Promed.EvnXml = {
MULTIPLE_DOCUMENT_TYPE_ID: 2,
EVN_VIZIT_PROTOCOL_TYPE_ID: 3,
EVN_USLUGA_PROTOCOL_TYPE_ID: 4,
LAB_USLUGA_PROTOCOL_TYPE_ID: 7,
STAC_PROTOCOL_TYPE_ID: 8,
STAC_RECORD_TYPE_ID: 9,
STAC_EPIKRIZ_TYPE_ID: 10
//	,
//	loadXmlDataSectionStore: function(params, callback) {
//		var thas = this;
//		if (!params) {
//			params = {};
//		}
//		if (!callback) {
//			callback = Ext.emptyFn;
//		}
//		if (!this.XmlDataSectionStore) {
//			this.XmlDataSectionStore = new sw.Promed.LocalStorage({
//				tableName: 'XmlDataSection'
//				,typeCode: 'int'
//				,allowSysNick: true
//				,loadParams: {params: params}
//				,onLoadStore: function(){
//					callback(thas.XmlDataSectionStore);
//				}
//			});
//			return true;
//		}
//		callback(this.XmlDataSectionStore);
//		return true;
//	},
//	saveAsTemplate: function (EvnXml_id, cmp) {
//		var loadMask = new Ext.LoadMask(Ext.get(cmp.id), { msg: 'Пожалуйста, подождите, идет сохранение документа как шаблона...' });
//		loadMask.show();
//		Ext.Ajax.request({
//			url: '/?c=EvnXml&m=saveAsTemplate',
//			callback: function(opt, success, response) {
//				loadMask.hide();
//				if (success && response.responseText != '') {
//					var response_obj = Ext.util.JSON.decode(response.responseText);
//					if (response_obj.success) {
//						getWnd('swXmlTemplateSettingsEditWindow').show({
//							XmlTemplate_id: response_obj.XmlTemplate_id,
//							disabledChangeEvnClass: true,
//							disabledChangeXmlType: true,
//							callback: function() {
//								//
//							}
//						});
//					}
//				}
//			},
//			params: { EvnXml_id: EvnXml_id }
//		});
//	},
//	getNotViewXmlTypeIdList: function (evnclass_id) {
//		evnclass_id = evnclass_id+'';
//		var not_view_id_list = [];
//		if (false == evnclass_id.inlist(['30','32'])) {
//			not_view_id_list.push(''+this.STAC_PROTOCOL_TYPE_ID);
//			not_view_id_list.push(''+this.STAC_RECORD_TYPE_ID);
//			not_view_id_list.push(''+this.STAC_EPIKRIZ_TYPE_ID);
//		}
//		if (false == evnclass_id.inlist(['21','22','29','43','47'])) {
//			not_view_id_list.push(''+this.EVN_USLUGA_PROTOCOL_TYPE_ID);
//		}
//		if (false == evnclass_id.inlist(['22','47'])) {
//			not_view_id_list.push(''+this.LAB_USLUGA_PROTOCOL_TYPE_ID);
//		}
//		if (false == evnclass_id.inlist(['11'])) {
//			not_view_id_list.push(''+this.EVN_VIZIT_PROTOCOL_TYPE_ID);
//		}
//		return not_view_id_list;
//    }
};
