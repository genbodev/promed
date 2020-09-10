
/////////////////////////////////////////////////////////////////////////////WIALON(WEBGIS) //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * @TODO Необходимо определить момент для вызова this.map.invalidateSize(); - метода, адекватно отрисовывающего карту
 * в случае многократного использования в разных компонентах
 */
Ext.define('sw.WialonJSMapPanel',{
    extend: 'sw.BaseMapPanel',
    alias: 'widget.swwialonjsmappanel',
    // src: 'http://wialonweb.promedweb.ru/wsdk/script/wialon.js',
    src: 'https://hst-api.wialon.com/wsdk/script/wialon.js',
    _sess: null,
    isRendered: false,
    initComponent: function () {
        this.addEvents({
            mapIsReady: true
        });
        this.callParent(arguments);
    },
    listeners: {
        show: function () {
            // console.log({show_map: this.map});
            this.map && this.map.invalidateSize();
        }
    },
    loadMap: function(callback){
        this.loadScript(this.src, function () {

            // console.log(this);
            //
            // this.mapPanel = L.map(this.id).setView([53.9, 27.55], 10);
            // //
            // L.tileLayer('http://{s}.tile.osm.org/{z}/{x}/{y}.png', {
            //     attribution: '&copy; <a href="http://gurtam.com">Gurtam</a>'
            // }).addTo(map);

            var wialon_local = getGlobalOptions().wialon_local;
            if (!wialon_local.user || !wialon_local.token) {
                alert('Не заданы параметры авторизации Wialon Local. Пожалуйста, обратитесь к администратору.');
                return;
            }


            wialon.core.Session.getInstance().initSession('http://wialonweb.promedweb.ru');
            wialon.core.Session.getInstance().loginToken(wialon_local.token,  wialon_local.user, this._login.bind(this));


            wialon.core.Session.getInstance().addListenerOnce('serverUpdated',function () {
                //addDataStorage();
            });

        }.bind(this));
    },
    /**
     * @private
     */
    _loginErrors: {
        "defaultLoginError": "Неизвестная ошибка при логине WialonSDK",
        "1": "Недействительная сессия",
        "2": "Неверное имя сервиса",
        "3": "Неверный результат",
        "5": "Ошибка выполнения запроса",
        "6": "Неизвестная ошибка",
        "7": "Доступ запрещен",
        "9": "Сервер авторизации недоступен, пожалуйста попробуйте повторить запрос позже",
    },
    /**
     * @private
     */
    _login:  function(code) { /// Login result
        if (code) {
            var text = wialon.core.Errors.getErrorText(code);
            log({wialon_login_error_code: code, text: text,  wialon_login_error_str: this._loginErrors[code] || this._loginErrors["defaultLoginError"]});
            return;
        }

        /**
         * @DEBUG
         */
        // console.log(code);
        // wialon.util.Gis.searchByString("Минск,ул.Скрыганова,6А",0, 5, function(){console.log({args: arguments})});

        this._sess = wialon.core.Session.getInstance();

        this._sess.loadLibrary("itemIcon");

        this._initMap();
    },
    _initMap: function () {
        L.TileLayer.WebGisRender = L.TileLayer.WebGis.extend({
            initialize: function (url, options) {
                L.TileLayer.prototype.initialize.call(this, url, options);
                options.sessionId = options.sessionId || 0;
                options.nocache = options.nocache || false;
                this._url = url + '/avl_render/{x}_{y}_{z}/' + options.sessionId + '.png';
            },

            setUrl: function (url, noRedraw) {
                this._url = url + '/avl_render/{x}_{y}_{z}/' + this.options.sessionId + '.png';
                if (!noRedraw) {
                    this.redraw();
                }
                return this;
            }
        });

        var gis_url = this._sess.getBaseGisUrl();
        var user_id = this._sess.getCurrUser().getId();

        var gurtam = L.tileLayer.webGis(gis_url, {attribution: "Gurtam Maps", minZoom: 4, userId: user_id});

        var render = new L.TileLayer.WebGisRender(this._sess.getBaseUrl() + "/adfurl" + new Date().getTime(), {
            sessionId: this._sess.getId(),
            minZoom: 4
        });

        var osm = L.tileLayer('http://{s}.tile.osm.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://osm.org/copyright">OpenStreetMap</a> contributors',
            minZoom: 4
        });

        this.map = L.map(this.id, {
            center: [45.181009, 34.120023],
            keyboard: false,
            zoom: 10,
            layers: [render, gurtam],
            doubleClickZoom: false
        });
        render.bringToFront();
        render.setZIndex(100);

        L.control.layers({
            "Gurtam Maps": gurtam,
            "OpenStreetMap": osm
        }).addTo(this.map);



        // markers layers
        this.eventsLayer = L.layerGroup().addTo(this.map);
        this.markersLayer = L.featureGroup().addTo(this.map);

        this.isRendered = true;

        this.fireEvent('mapIsReady');

        var mainConfig = this.up('panel[refId=swMapPanel]').initialConfig;

        if ( mainConfig.addMarkByClick ) {
            this.map.addEventListener('dblclick',function(e){
                this.doClick(e)
            }.bind(this));
        }

        this.map.once('resize',function(){
            this.map.invalidateSize();
        }.bind(this));

        this.map.once('viewreset',function(){
            this.map.invalidateSize();
        }.bind(this));

    },
    doClick: function(event) {
        var mainPanel = this.up('panel[refId=swMapPanel]');

        mainPanel.fireEvent('mapClick', {
            point: [
                event.latlng.lat,
                event.latlng.lng
            ],
            pagepixel: [
                event.originalEvent.clientX,
                event.originalEvent.clientY
        ]});

        if (typeof this.clickCallback == 'function') {
            this.clickCallback(event);
        }

    },
    /**
     * Метод получения координат по адресу
     * @param addr {String}
     * @param callback {Function]
     */
    geocode: function (addr, callback) {
        // @todo По каким-то неясным пока причинам, получаем ошибку с кодом 1 - т.е. неверная сессия. Хотя на момент запроса уже залогинены
        // wialon.util.Gis.getLocations([{lat:45.181009, lng:34.120023}],0,1, function(){console.log(arguments)})


        /**
         * Попытка реализации геокодинга через openstreetmap источник
         * Неудачная, т.к. практически отсутствуют адреса Крыма
         */
        Ext.Ajax.request({
            url: 'http://nominatim.openstreetmap.org/search.php',
            method: 'GET',
            params: {
                format: 'json',
                'accept-language': 'ru',
                q: getGlobalOptions().region.name+' '+addr,
                // CmpCallCard_id: store_record.get('CmpCallCard_id'),
//									CmpCallCardStatusType_id: 2,
//                 armtype: 'smpdispatcherstation'
            },
            callback: function (opt, success, response) {
                // loadMask.hide();
                if (success) {
                    var response_obj = Ext.JSON.decode(response.responseText);
                    console.log({geocode_result: response_obj});
                }
            }
        });
    }
    // geocode: function (addr,callback) {
    //     if(typeof ymaps == 'undefined')return;
    //     ymaps.geocode(addr, {results: 1}).then(function (res) {
    //         if (typeof callback == 'function') {
    //             callback(res.geoObjects.get(0).geometry.getCoordinates())
    //         }
    //     }, function (err) {
    //         // Если геокодирование не удалось, сообщаем об ошибке.
    //         //Ext.MessageBox.alert('Ошибка', 'Невозможно определить точку </br>'+err.message);
    //     });
    // },
});

Ext.define('sw.Smp.WialonJSMapPanel',{
    extend: 'sw.WialonJSMapPanel',
    alias: 'widget.swsmpwialonjsmappanel',
    
    _getAddressForGeocodeByCmpCallCardRecord: function( record , callback) {

        if (!(record instanceof Ext.data.Model) ||
            !record.get('Adress_Name') ||
            typeof callback !== 'function')
        {
            return false;
        }

        callback(record.get('Adress_Name'));
    },
});

/**
 * @TODO
 *  В карте должны быть реализованы все методы описаного ниже компонента
*/
// //
// ////////////////////////////////////////////////////////////////////////////////YANDEX ////////////////////////////////////////////////////////////////////////////////
// //
// Ext.define('sw.YandexMapPanel',{
//     extend: 'sw.BaseMapPanel',
//     //id: 'swYandexMapPanel',
//     alias: 'widget.swyandexmappanel',
//     isRendered: false,
//     yandexMarkers: [],
//     showTraffic: true,
//     currentMarker: null,
//     src: 'https://api-maps.yandex.ru/2.1/?load=package.standard&lang=ru_RU',
//     doClick: function (event) {
//         var mainPanel = this.up('panel[refId=swMapPanel]'),
//             mainConfig = mainPanel.initialConfig;
//
//         //mainPanel.fireEvent('mapClick', {point: event.get('coords'), pagepixel: event._Hb.position}); //old version yaMap 1.x
//         mainPanel.fireEvent('mapClick', {point: event.get('coords'), pagepixel: event.get('position')});
//         /*
//          if (this.currentMarker) {
//          this.removeMarker(this.currentMarker);
//          }
//
//          this.addMarker({point: event.get('coords'), pagepixel: event.get('position')});
//          */
//         if (typeof this.clickCallback == 'function') {
//             this.clickCallback(event);
//         }
//
//     },
//
//     geocode: function (addr,callback) {
//         if(typeof ymaps == 'undefined')return;
//         ymaps.geocode(addr, {results: 1}).then(function (res) {
//             if (typeof callback == 'function') {
//                 callback(res.geoObjects.get(0).geometry.getCoordinates())
//             }
//         }, function (err) {
//             // Если геокодирование не удалось, сообщаем об ошибке.
//             //Ext.MessageBox.alert('Ошибка', 'Невозможно определить точку </br>'+err.message);
//         });
//     },
//
//     getAddressFromLatLng: function(coords, callback){
//         ymaps.geocode(coords).then(function(res){
//             if (typeof callback == 'function') {
//                 var resObj = res.geoObjects.get(0).properties.get('metaDataProperty').GeocoderMetaData.AddressDetails.Country,
//                     address = {};
//
//                 function checkProp(o){
//                     if (typeof o == 'object'){
//                         for (var key in o) {
//                             if (o.hasOwnProperty(key)) {
//
//                                 switch(key){
//                                     case 'PremiseNumber' : {
//                                         address.streetNum = (o[key]);
//                                         break;
//                                     }
//                                     case 'PremiseName' : {
//                                         address.establishmentName = (o[key]);
//                                         break;
//                                     }
//                                     case 'ThoroughfareName' : {
//                                         address.streetLongName = (o[key]);
//                                         break;
//                                     }
//                                     case 'LocalityName' : {
//                                         address.areaShortName = (o[key]);
//                                         break;
//                                     }
// //										case 'LocalityName' : {
// //											address.cityShortName = (o[key]);
// //											break;
// //										}
//                                     case 'AdministrativeAreaName' : {
//                                         address.regionName = (o[key]);
//                                         break;
//                                     }
//                                     case 'CountryName' : {
//                                         address.countryName = (o[key]);
//                                         break;
//                                     }
//                                     case 'CountryNameCode': {
//                                         address.countryShortName = (o[key]);
//                                         break;
//                                     }
// //										case 'postal_code' : {
// //											address.postalCode = (resObj[a].long_name);
// //											break;
// //										}
//                                 }
//
//                                 arguments.callee(o[key]);
//                             }
//                         }
//                     }
//                 }
//
//                 checkProp(resObj)
//
//                 callback(address)
//             }
//         }, function(err){
//             //Ext.MessageBox.alert('Ошибка', 'Невозможно определить точку </br>'+err.message);
//         });
//     },
//     findMarkerBy: function(key,value) {
//         for (var i=0;i<this.yandexMarkers.length;i++) {
//             if (this.yandexMarkers[i][key] == value) {
//                 return this.yandexMarkers[i];
//             }
//         }
//     },
//     removeMarker: function(marker) {
//         for (var i=0;i<this.yandexMarkers.length;i++) {
//             this.getMap().geoObjects.remove(marker);
//             if (this.yandexMarkers[i] == marker) {
//                 delete this.yandexMarkers[i];
//                 this.yandexMarkers.splice(i,1);
//                 this.getMap().geoObjects.remove(marker);
//                 return;
//             }
//         }
//     },
//     loadMap: function( callback ) {
//         this.loadScript(this.src,function(){
//             ymaps.ready(function(){
//                 var mainPanel = this.up('panel[refId=swMapPanel]');
//                 var mainConfig = mainPanel.initialConfig;
//
//                 function loadMapWithOpts(coords, pan){
//
//                     pan.map = new ymaps.Map(pan.getLayout().getElementTarget().id, {
//                             center:[coords[0], coords[1]],
//                             zoom:15,
//                             controls: ['zoomControl']
//                         },
//                         {
//                             suppressMapOpenBlock: true
//                         }
//
//                     );
//
//                     pan.map.behaviors.enable('scrollZoom');
//                     pan.map.behaviors.disable('dblClickZoom');
//
//                     if ( typeof callback == 'function' ) {
//                         callback();
//                     };
//
//                     pan.isRendered = true;
//
//                     pan.fireEvent('mapIsReady');
//
//                     if ( mainPanel.showTraffic ) {
//                         pan.map.actualProvider = new ymaps.traffic.provider.Actual({}, { infoLayerShown: true });
//                         // И затем добавим его на карту.
//                         pan.map.actualProvider.setMap(pan.map);
//                     }
//
//                     if ( mainConfig.addMarkByClick ) {
//                         pan.map.events.add('dblclick',function(e){
//                             pan.doClick(e)
//                         }.bind(pan));
//                     }
//                 };
//
//                 if(mainPanel.currentPosition==null){
//                     this.geocode(getGlobalOptions().region.name, function(coords){
//
//                         loadMapWithOpts(coords, this);
//                     }.bind(this));
//                 }
//                 else{
//                     loadMapWithOpts(mainPanel.currentPosition, this);
//                 }
//
//             }.bind(this));
//         }.bind(this));
//     },
//     addMarker: function (marker) {
//         if ((!marker.point&&!Ext.isArray(marker.point)&&!marker.point.length!=2)&&(!marker.address)) {
//             return false;
//         }
//         var yandexMarkerInitObj = {};
//         yandexMarkerInitObj['point']=[marker.point[0],marker.point[1]];
//
//         if (marker.baloonContent != null) {
//             yandexMarkerInitObj['attrs'] = {
//                 iconContent: '',
//                 balloonContent: marker.baloonContent,
//                 hintContent: ''
//             }
//         }
//
//         if (marker.imageHref && marker.imageSize && marker.imageOffset) {
//             yandexMarkerInitObj['opts'] = {
//                 iconLayout: 'default#image',
//                 iconImageHref:marker.imageHref,
//                 iconImageSize: marker.imageSize,//[32,37],
//                 iconImageOffset: marker.imageOffset//[-16,-37]
//             };
//         } else {
//             yandexMarkerInitObj['opts'] = {
//                 preset: 'twirl#redStretchyIcon'
//             };
//         }
//
//         if (marker.center === true) {
//             this.getMap().setCenter([marker.point[0],marker.point[1]]);
//         }
//         if (typeof marker.listeners === 'object'){
//             yandexMarkerInitObj['events'] = [];
//             for (evt in marker.listeners) {
//                 yandexMarkerInitObj['events'].push({
//                     eName: evt,
//                     eHandler: marker.listeners[evt]
//                 })
//             }
//         }
//         if(typeof ymaps.Placemark != 'undefined'){
//             var yandexMarker = new ymaps.Placemark(yandexMarkerInitObj.point,yandexMarkerInitObj.attrs,yandexMarkerInitObj.opts);
//             if (marker.additionalInfo) {
//                 for (key in marker.additionalInfo) {
//                     if (marker.additionalInfo.hasOwnProperty(key)) {
//                         yandexMarker[key] = marker.additionalInfo[key];
//                     }
//                 }
//             }
//             this.yandexMarkers.push(yandexMarker);
//
//             this.getMap().geoObjects.add(yandexMarker);
//
//             this.currentMarker = yandexMarker;
//
//             return this.currentMarker
//         }
//         else{
//             return false;
//         }
//
//     },
//     initComponent: function() {
//
//         this.addEvents({
//             mapIsReady: true,
//         });
//
//         this.callParent(arguments);
//     }
// });