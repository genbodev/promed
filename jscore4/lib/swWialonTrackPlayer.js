/**
 * Об]trn для инициализации проигрывателя треков виалон
 * @type {{initTrackPlayer: sw.Promed.WialonTrackPlayer.initTrackPlayer}}
 */
sw.Promed.WialonTrackPlayer = {

//Object {startTime: 1458568091, endTime: 1482947720, wialonId: "405"}
    initTrackPlayer: function (wialonId, timeFrom, timeTo) {

        // wialonId = 49;
        // timeFrom = 1499080200;
        // timeTo = 1499080919;
        // sw.Promed.WialonTrackPlayer.initTrackPlayer(180, 1482944320, 1482947720);

		var Mask = new Ext.LoadMask(Ext.getBody(), {msg: "Идет построение трека..."});

        var callbacks = {}; // system variables
        var timeoutId;
        var layer_temp = {
            "layerName":"",
            "itemId":0,
            "timeFrom":0,
            "timeTo":0,
            "tripDetector":1,
            "trackColor":"",
            "trackWidth":4,
            "arrows":false,
            "points":true,
            "pointColor":"",
            "annotations":false
        };

        var time_prev, last_graph_click=0;

        /* maps variables */
        var map,
            eventsLayer,
            markersLayer,
            stopIcon,
            unitinfobox;

// time variables: start of interval, end of interval
        var from = timeFrom, to = timeTo;
        var state = "STOP"; // state of player ('PLAY' or 'STOP')
        var previousPoint = null;
        var busy = false;
        var paused = false;

        var plot;

        var DataStorage = {};
        var messagesUtils;
// var sensorsUtils;

        var speed = 100;                // !!!! animation time miliseconds
        var msgSize = 500;              // !!!! message load at once
        var messagesLoadSpeed = 5000;   // !!!! load ?msgSize+1? every ?messagesLoadSpeed? miliseconds
        var minRange = 600;             // !!!! min zoom range in sec
        var timezone = 0;               // !!!! timezone offset in seconds
        var skipBeetweenTrips = false;
        var idTimeout = 0;

        var tz = 0, dst = 0;

        var unitId = null;

        var LANG = "";
        var regionWialonUrl = '';
		var url;
        /** * * * * * * * * * * * * * * *
         * WialonSDK and Leaflet init *
         * * * * * * * * * * * * * * * * */

        function exec_callback(id) { /// Execute callback
            if (!callbacks[id])
                return;
            callbacks[id].call();
        }

        window.exec_callback = exec_callback;

        function wrap_callback(callback) { /// Wrap callback
            var id = (new Date()).getTime();
            callbacks[id] = callback;
            return id;
        }

        function ie() { /// IE check
            return (navigator.appVersion.indexOf("MSIE 6") != -1 || navigator.appVersion.indexOf("MSIE 7") != -1 || navigator.appVersion.indexOf("MSIE 8") != -1);
        }

        function get_html_var(name) { /// Fetch varable from 'GET' request
            if (!name) {
                return null;
            }
            var pairs = document.location.search.substr(1).split("&");
            for (var i = 0; i < pairs.length; i++) {
                var pair = pairs[i].split("=");
                if (decodeURIComponent(pair[0]) === name) {
                    return decodeURIComponent(pair[1]);
                }
            }
            return null;
        }

        function load_script(src, callback) { /// Load script
            var script = document.createElement("script");
            script.setAttribute("type","text/javascript");
            script.setAttribute("charset","UTF-8");
            script.setAttribute("src", src);
            if (callback && typeof callback == "function") {
                if(ie())
                    script.onreadystatechange = function () {
                        if (this.readyState == 'complete' || this.readyState == 'loaded')
                            callback();
                    };
                else
                    script.setAttribute("onLoad", "exec_callback(" + wrap_callback(callback) + ")");
            }
            document.getElementsByTagName("head")[0].appendChild(script);
        }

        function init() { // We are ready now
            url = getGlobalOptions().wialon_local.wialon_url + "wsdk/script/wialon.js" ;
            load_script(url, init_sdk);
        }

        function init_sdk() { /// Init SDK
			Mask.show();
            var wialon_local = getGlobalOptions().wialon_local;

            if (!wialon_local.user || !wialon_local.token) {
				Mask.hide();
                alert('Не заданы параметры авторизации Wialon Local. Пожалуйста, обратитесь к администратору.');
                return;
            }

			var sess = wialon.core.Session.getInstance();
            sess.initSession(regionWialonUrl);
			sess.loginToken(wialon_local.token,  wialon_local.user, login);
            sess.addListenerOnce('serverUpdated',function () {
                addDataStorage( function(tf){
					if( !tf ) logout();
					Mask.hide();
				});
            });
        }

		// Logout
		function logout() {
			sw.Promed.WialonTrackPlayer.logout();
		}

        function login(code) { /// Login result
			if (code) {
				var str = $.localise.tr("Login error, restart the application");
				switch(code){
					case 1:
						str = 'Недействительная сессия';
						break;
					case 2:
						str = 'Неверное имя сервиса';
						break;
					case 3:
						str = 'Неверный результат';
						break;
					case 7:
						str = 'Доступ запрещен';
						break;
					case 5:
						str = 'Ошибка выполнения запроса';
						break;
					case 9:
						str = 'Сервер авторизации недоступен, пожалуйста попробуйте повторить запрос позже';
						break;
					case 6:
						str = 'Неизвестная ошибка';
						break;
				}
				Mask.hide();
				logout();
				alert(str);
				return false;
			}
//            if (code) {
//				alert($.localise.tr("Login error, restart the application")); return;
//			}

            var sess = wialon.core.Session.getInstance();

            tz = wialon.util.DateTime.getTimezoneOffset();
            dst = wialon.util.DateTime.getDSTOffset(sess.getServerTime());

            // preload user dateTime format
            getUserDateTimeFormat();

            var flags = wialon.item.Item.dataFlag.base | wialon.item.Unit.dataFlag.sensors | wialon.item.Item.dataFlag.customProps;

            sess.loadLibrary("itemIcon");
            sess.loadLibrary("unitTripDetector");
            sess.loadLibrary("unitSensors");

            sess.updateDataFlags(
                [{type: "type", data: "avl_unit", flags: flags, mode: 0}],
                function (code) {
                    if (code)
                        return;
                    var un = sess.getItems("avl_unit");
                    wialon.util.Helper.filterItems(un, wialon.item.Item.accessFlag.execReports);
                }
            );

            initMap();
        }

        /* initialize map */
        function initMap() {
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

            L.RightSidePopup = L.Popup.extend({
                _updatePosition: function () {
                    if (!this._map) { return; }

                    var pos = this._map.latLngToLayerPoint(this._latlng),
                        animated = this._animated,
                        offset = L.point(this.options.offset);

                    if (animated) {
                        L.DomUtil.setPosition(this._container, pos);
                    }

                    this._containerBottom = -offset.y - (animated ? 0 : pos.y);
                    this._containerLeft = offset.x + (animated ? 0 : pos.x);

                    // bottom position the popup in case the height of the popup changes (images loading etc)
                    this._container.style.bottom = this._containerBottom + 'px';
                    this._container.style.left = this._containerLeft + 'px';
                }
            });

            var sess = wialon.core.Session.getInstance();

            var gis_url = sess.getBaseGisUrl();
            var user_id = sess.getCurrUser().getId();

            var gurtam = L.tileLayer.webGis(gis_url, {attribution: "Gurtam Maps", minZoom: 4, userId: user_id});

            var render = new L.TileLayer.WebGisRender(sess.getBaseUrl() + "/adfurl" + new Date().getTime(), {
                sessionId: sess.getId(),
                minZoom: 4
            });

            var osm = L.tileLayer('https://{s}.tile.osm.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://osm.org/copyright">OpenStreetMap</a> contributors',
                minZoom: 4
            });

            map = L.map("map", {
                center: [53.505,28.49],
                keyboard: false,
                zoom: 6,
                layers: [render, gurtam]
            });
            render.bringToFront();
            render.setZIndex(100);

            var layers = {
                "Gurtam Maps": gurtam,
                "OpenStreetMap": osm
            };

            L.control.layers(layers).addTo(map);


            // common icons
            stopIcon = L.icon({
                iconUrl: "img/wialonTrackPlayer/stop.png",
                iconSize: [21, 20],
                iconAnchor: [0, 0]
            });

            // unit hover info box
            unitinfobox = new L.RightSidePopup({
                className: "unitinfoBox",
                closeButton: false,
                autoPan: false,
                offset: [25, 60],
                minWidth: 140
            });

            // markers layers
            eventsLayer = L.layerGroup().addTo(map);
            markersLayer = L.featureGroup().addTo(map);

            markersLayer
                .on("mouseover", function(evt) {
                    var marker = evt.layer;
                    if ("parentMarker" in marker.options) {
                        marker = marker.options.parentMarker;
                    }
                    unitHover(marker, true);
                })
                .on("mouseout", function(evt) {
                    var marker = evt.layer;
                    if ("parentMarker" in marker.options) {
                        marker = marker.options.parentMarker;
                    }
                    unitHover(marker, false);
                });
        }

        /** * * * * * * * * * *
         * track Player logic  *
         * * * * * * * * * * * */

        function freeze() {
            clearTimeout(timeoutId);
        }

        function setTimeRange() {
            pause();

            if(to<=from){
                alert($.localise.tr("Wrong datetime format"));
                return false;
            }

            var val = $("#slider").slider("option", "value");
            val = from;

            $("#slider").slider("option",{
                min: from,
                max: to,
                value: val
            });
            $("#info_block").slider("option",{
                min: from,
                max: to,
                value: val
            });
            $("#range").slider("option",{
                values: [from,to],
                min: from,
                max: to
            });

            $(".photo").remove();
            $("#photos").css({width:"100%",left:"0%"});

            drawTimeline();

            for(var i in DataStorage){
                clearTimeout(DataStorage[i].getMessagesId);
                updateDataStorage(i);
            }

			return true;
        }

        function changeSliderRange(evt, ui, keepRatio, changeValue) {
            /*
             * keepRatio - keep same position on selected range (e.g while Drag), not Zoom when true
             * changeValue - if keepRatio==true { change global current time or not }, set false on DblClick
             */
            var o = $("#range").slider("option","values");
            var which = null;
            if(ui.handle){
                if(ui.handle.nextSibling == null) which = 0;
                else which = 1;
            }

            /*== design block */
            var h = $("#range").children(".ui-slider-handle");
            if( ui.values[0]==from ){ h[0].style.opacity = 0.1; h[0].style.width = "2px"; }
            else { h[0].style.opacity = 1; h[0].style.width = "1px"; }
            if( ui.values[1]==to ){ h[1].style.opacity = 0.1; h[1].style.width = "2px"; }
            else { h[1].style.opacity = 1; h[1].style.width = "1px"; }
            /* design block ==*/

            var val = $( "#slider").slider("option", "value");

            if(keepRatio){
                val =  ui.values[0] + (ui.values[1]-ui.values[0])*(val-o[0])/(o[1]-o[0]);
            } else if( changeValue ){
                if(val < ui.values[ 0 ]) val = ui.values[ 0 ];
                if(val > ui.values[ 1 ]) val = ui.values[ 1 ];
            }

            var p = (to-from)/(ui.values[1]-ui.values[0])*100;
            $("#photos").width( p+"%" ).css("left", "-"+p*(ui.values[0]-from)/(to-from)+"%" );

            if(plot && !keepRatio){
                var axes = plot.getAxes();
                var scale = ((axes.xaxis.options.max-axes.xaxis.options.min)/((ui.values[1]-ui.values[0])*1000));
                p = plot.getXAxes()[0].p2c(which!==null? ui.values[which]*1000 : (from+(from-to)/2)*1000);
                plot.zoom({amount:scale, center:{left:p, top:0}});
            }

            $("#slider").slider("option", {"value":val});
            $("#info_block").slider("option", {"min":ui.values[0], "max":ui.values[1]});
        }

        function addDataStorage( callback ) {
            pause();
            if( !wialonId ){ alert($.localise.tr("Select unit")); return;}
            if( DataStorage[wialonId] ){
                alert($.localise.tr("There is a track for this unit already"));
				callback(false);
				return false;
            }

            var sess = wialon.core.Session.getInstance();

            var unit = sess.getItem( wialonId );
            // var color = '679a01';
            var color = '1826B0';

            if (!unit) {
                alert('Нет данных о транспорте с указанным идентификатором! Пожалуйста, проверьте наличие идентификатора виалон у бригады');
				callback(false);
                return false;
            }

            var render = sess.getRenderer();
            var obj = layer_temp; // get template and set values
            obj.layerName = unit.getName();
            obj.itemId = unit.getId();

            obj.timeFrom = from;
            obj.timeTo = to;
            if( !setTimeRange() ) { callback(false); return; }

            obj.trackColor = "c0"+ color;
            obj.pointColor = "c0"+ color;

            render.createMessagesLayer( obj, function(code, layer){
                if (code || !layer) {
                    alert($.localise.tr(code==1001?"No messages for the selected interval":"Unable to build a track"));
					callback(false);
                    return false;
                }

                unit.getTrips(from, to, layer.getName(), qx.lang.Function.bind(function(id, code, trips){
                    if(code){ alert($.localise.tr("Unable to load trips")); callback(false); return; }
                    if(DataStorage[id]){
                        DataStorage[id].trips = trips;
                        updateTimeline();
                    }
                }, this, layer.getUnitId()));

                initTrack(layer, obj.trackColor);
                initMessages(layer.getUnitId());
                activateUI(true);
				callback(true);
            });
        }

        function updateDataStorage(id) {
            DataStorage[id].photos = false;
            DataStorage[id].version++;
            for(var i=0; i<DataStorage[id].eventsMarkers.length; i++)
                if(DataStorage[id].eventsMarkers[i])
                    eventsLayer.removeLayer(DataStorage[id].eventsMarkers[i]);
            DataStorage[id].eventsMarkers = [];

            var sess = wialon.core.Session.getInstance();
            var render = sess.getRenderer();
            if(DataStorage[id].layer && render)
                render.removeLayer(DataStorage[id].layer, function(code){
                    updateMessageLayer(id, render);
                });
            else
                updateMessageLayer(id, render);
        }

        function updateMessageLayer(id, render) {
            var unit = DataStorage[id].unit;

            var obj = layer_temp; // get template and set values
            obj.layerName = unit.getName();
            obj.itemId = unit.getId();
            obj.timeFrom = from;
            obj.timeTo = to;
            obj.trackColor = DataStorage[id].color;
            obj.pointColor = DataStorage[id].color;

            render.createMessagesLayer( obj, function(code, layer){
                var checkbox = $("#unit_track_"+unit.getId()+" .check_wrapper input");
                if(code){
                    if(code!=1001) alert($.localise.tr("Unable to build a track"));
                    DataStorage[id].layer = null;
                    $("#info_"+id).html(DataStorage[id].unit.getName());
                    checkbox.click().attr("disabled",true);
                    return;
                }
                initTrack(layer, obj.trackColor);
                if( checkbox.attr("disabled") )
                    checkbox.attr("disabled",false).click();
                unit.getTrips(from, to, layer.getName(), function(code, trips){
                    if(code){ alert($.localise.tr("Unable to load trips")); return; }
                    DataStorage[layer.getUnitId()].trips = trips;
                    updateTimeline();
                });
                initMessages(layer.getUnitId());
            });
        }

        function deleteDataStorage(val) {
            pause();

            var sess = wialon.core.Session.getInstance();
            var rend = sess.getRenderer();
            if( !rend ){ alert($.localise.tr("Session error, restart the application")); }

            if( DataStorage[val] ){
                clearTimeout(DataStorage[val].getMessagesId);

                if(DataStorage[val].layer)
                    rend.removeLayer(DataStorage[val].layer, function(){
                        refreshMap();
                        updateTimeline();

                        var c = 0;
                        for(var i in DataStorage) c++;
                        if (c === 0) {
                            activateUI(false);
                        }
                    });

                markersLayer.removeLayer(DataStorage[val].marker);
                markersLayer.removeLayer(DataStorage[val].stopMarker);
                for(var i=0; i<DataStorage[val].eventsMarkers.length; i++)
                    if(DataStorage[val].eventsMarkers[i])
                        eventsLayer.removeLayer(DataStorage[val].eventsMarkers[i]);

                DataStorage[val] = null;
                delete DataStorage[val];

                refreshMap();

                $("#photos .ph_"+val).remove();
            } else {
                alert($.localise.tr("Nothing to delete, no tracks for the given unit"));
            }
        }

        var getUserDateTimeFormat = (function(){
            var format = false;

            var getAdaptedDateFormat = function(wialonDateFormat){
                var s = wialonDateFormat.replace(/(%\w)|_|%%/g, function(str){
                    switch (str) {
                        case "%Y": return 'yy';
                        case "%y": return 'y';
                        // month
                        case "%B": return 'MM';   // MM - month name long
                        case "%b": return 'M';    // M - month name short
                        case "%m": return 'mm';   // mm - month of year (two digit)
                        case "%l": return 'm';    // m - month of year (no leading zero)
                        // day
                        case "%A": return 'DD';   // DD - day name long
                        case "%a": return 'D';    // D - day name short
                        case "%E": return 'dd';   // dd - (two digit)
                        case "%e": return 'd';    // d - (no leading zero)
                        // for time format:
                        case "%H": return 'HH';   // 24h
                        case "%I": return 'hh';   // 12h
                        case "%p": return 'TT';   // AM/PM
                        case "%M": return 'mm';
                        default: return '';
                    }
                });

                return s;
            };

            /**
             * getUserDateTimeFormat for current user
             * @param  {boolean} timeOnly get timeOnly format (without date)
             * @return {string}          format to use in wialon.util.DateTime.formatDate or wialon.util.DateTime.formatDate
             */
            var getUserDateTimeFormat = function getUserDateTimeFormat(callback){
                if (!format) {
                    wialon.core.Session.getInstance().getCurrUser().getLocale(function(code, locale){
                        var t;

                        !locale && (locale = {});
                        locale.def = '%Y-%m-%E_%H:%M:%S';
                        !locale.fd && (locale.fd = locale.def);
                        typeof locale.wd === 'undefined' && (locale.wd = 1);

                        t = wialon.util.DateTime.convertFormat(locale.fd, true);
                        t = t.split('_');

                        format = {
                            fd: locale.fd,
                            firstDay: locale.wd,
                            date: t[0],
                            time: t[1],
                            dateTime: t[0] + '_' + t[1],
                            dateTimeToPrint: t[0] + ' ' + t[1]
                        };

                        // Set settings for datepicker
                        var tr_month =[
                            $.localise.tr("January"),$.localise.tr("February"),$.localise.tr("March"),$.localise.tr("April"),
                            $.localise.tr("May"),$.localise.tr("June"),$.localise.tr("July"),$.localise.tr("August"),
                            $.localise.tr("September"),$.localise.tr("October"),$.localise.tr("November"),$.localise.tr("December")
                        ];
                        var tr_month_short =[$.localise.tr("Jan"), $.localise.tr("Feb"), $.localise.tr("Mar"), $.localise.tr("Apr"),
                            $.localise.tr("May"), $.localise.tr("Jun"), $.localise.tr("Jul"), $.localise.tr("Aug"),
                            $.localise.tr("Sep"), $.localise.tr("Oct"), $.localise.tr("Nov"), $.localise.tr("Dec")];

                        var tr_days =[
                            $.localise.tr("Sunday"),$.localise.tr("Monday"),$.localise.tr("Tuesday"),$.localise.tr("Wednesday"),
                            $.localise.tr("Thursday"),$.localise.tr("Friday"),$.localise.tr("Saturday")
                        ];

                        var tr_days_short =[
                            $.localise.tr("Sun"),$.localise.tr("Mon"),$.localise.tr("Tue"),$.localise.tr("Wed"),
                            $.localise.tr("Thu"),$.localise.tr("Fri"),$.localise.tr("Sat")
                        ];

                        var tr_days_min =[
                            $.localise.tr("Su"),$.localise.tr("Mo"),$.localise.tr("Tu"),$.localise.tr("We"),
                            $.localise.tr("Th"),$.localise.tr("Fr"),$.localise.tr("Sa")
                        ];

                        // Update locale days & month names
                        wialon.util.DateTime.setLocale(
                            tr_days,
                            tr_month,
                            tr_days_short,
                            tr_month_short
                        );

                        if (LANG !== 'en') {
                            $.datepicker.regional[LANG] = {
                                monthNames: tr_month,
                                monthNamesShort: tr_month_short,
                                dayNames: tr_days,
                                dayNamesShort: tr_days_short,
                                dayNamesMin: tr_days_min,
                                isRTL: false,
                                showMonthAfterYear: false,
                                yearSuffix: ''};

                            $.datepicker.setDefaults($.datepicker.regional[LANG]);
                        }

                        var settings = {
                            showSecond: true,
                            dateFormat: getAdaptedDateFormat( locale.fd.split('_')[0] ),
                            timeFormat: format.time,
                            // maxDate: maxDate,
                            firstDay: format.firstDay,
                            showButtonPanel:false,
                            prevText: $.localise.tr("Prev"),
                            nextText: $.localise.tr("Next"),
                            monthNames: tr_month,
                            dayNames: tr_days,
                            dayNamesShort: tr_days_short,
                            dayNamesMin: tr_days_min,
                            timeText: $.localise.tr("Time"),
                            hourText: $.localise.tr("Hours"),
                            minuteText: $.localise.tr("Minutes"),
                            secondText: $.localise.tr("Seconds"),
                            controlType: "select"
                        };

// 				// Test time format
// 				var tDate = new Date();
// 				tDate.setHours(0);
// 				tDate.setMinutes(0);
// 				tDate.setSeconds(0);
// 				tDate.setMilliseconds(0);
// 				var tMs = (tDate.getTime() / 1000 - 7 * 24 * 60 * 60) * 1000; // a week ago
// 				tDate = new Date(tMs);
// 				var $testInput = $('<input>');
// 				$testInput.datetimepicker(settings);
// 				$testInput.datetimepicker('setDate', tDate);
// 				if ( $testInput.datetimepicker('getDate').getTime() !== tMs ) {
// 					settings.dateFormat = getAdaptedDateFormat( locale.def.split('_')[0] );
// 					settings.timeFormat = 'HH:mm:ss';
// 				}

                        if (typeof callback == 'function')
                            callback();
                    });
                }

                return format;
            };

            return getUserDateTimeFormat;
        }());

        function tickFormat(v, axis) {
            var wialonTimeFormat = getUserDateTimeFormat().fd.split('_')[1].replace('H', 'h');
            var timeUnitSize = {
                "second": 1000,
                "minute": 60 * 1000,
                "hour": 60 * 60 * 1000,
                "day": 24 * 60 * 60 * 1000,
                "month": 30 * 24 * 60 * 60 * 1000,
                "quarter": 3 * 30 * 24 * 60 * 60 * 1000,
                "year": 365.2425 * 24 * 60 * 60 * 1000
            };
            var opts = axis.options;
            var d = new Date(v);
            if(opts.timeOffset)
                d = new Date(d.setSeconds(axis.options.timeOffset));
            var t = axis.tickSize[0] * timeUnitSize[axis.tickSize[1]];
            var span = axis.max - axis.min;
            var suffix = (opts.twelveHourClock) ? " %p" : "";

            var fmt = "";
            if (t < timeUnitSize.minute)
                fmt = wialonTimeFormat + suffix;
            else if (t < timeUnitSize.day) {
                if (span < 2 * timeUnitSize.day)
                    fmt = wialonTimeFormat + suffix;
                else
                    fmt = "%b %d " + wialonTimeFormat + suffix;
            }
            else if (t < timeUnitSize.month)
                fmt = "%b %d";
            else if (t < timeUnitSize.year) {
                if (span < timeUnitSize.year)
                    fmt = "%b";
                else
                    fmt = "%b %y";
            }
            else
                fmt = "%y";

            return $.plot.formatDate(d, fmt, opts.monthNames);
        }

        function drawTimeline() {
            var count = getDataStorageCount();
            var photo = needPhoto();

            if(count){
                $("#graph").height(14+(photo?76:0)+count*18);
                $("#info_block").show();
            } else {
                $("#graph").height(1);
                $("#info_block").hide();
            }

            var t_from = (from + timezone)*1000;
            var t_to = (to + timezone)*1000;

            plot = $.plot("#graph", [], {
                series: {shadowSize: 0},
                xaxis: {
                    mode: "time", show:true, min:t_from, max:t_to, color:"#FFF", tickColor:"#3e454d", timeOffset:-get_local_timezone() + tz + dst,
                    tickFormatter: tickFormat,
                    monthNames: [$.localise.tr("Jan"), $.localise.tr("Feb"), $.localise.tr("Mar"), $.localise.tr("Apr"),
                        $.localise.tr("May"), $.localise.tr("Jun"), $.localise.tr("Jul"), $.localise.tr("Aug"),
                        $.localise.tr("Sep"), $.localise.tr("Oct"), $.localise.tr("Nov"), $.localise.tr("Dec")],
                    panRange: [t_from, t_to]
                },
                yaxis: {
                    show:true, reserveSpace:false, autoscaleMargin:0, panRange:false, zoomRange:false, ticks:[0], labelWidth:0, labelHeight:0, tickColor:"#3e454d", min:0
                },
                pan: { interactive:true },
                grid: {borderWidth:0, labelMargin:0, axisMargin:0, minBorderMargin:0, hoverable:true, clickable: true, autoHighlight:false, mouseActiveRadius:20},
                legend: {show:false}
            });

            var canvas = plot.getCanvas();
            canvas.setAttribute("width", 10);
            canvas.setAttribute("height", 10);

            $("#graph").bind("plothover", function (event, pos, item) {
                showHoverInfo(item);
            });

            $("#graph").bind("plotclick", function (event, pos, item) {
                if (event.timeStamp-last_graph_click > 10) {
                    last_graph_click = event.timeStamp;
                    if (item) {
                        var marker = DataStorage[item.series.label].eventsMarkers[item.dataIndex];
                        if (marker){
                            marker.openPopup();
                            map.panTo(marker.getLatLng());
                        }
                    }
                }
            });

            $("#graph").bind("plotpan", function (event, plot, delta) {
                pause();
                var axes = plot.getAxes();
                var min = Math.ceil(axes.xaxis.min/1000), max =Math.floor(axes.xaxis.max/1000);
                changeSliderRange(null, {values:[min, max]}, true, false);
                $("#range").slider("option","values",[min,max]);
            });

            $(window).resize();
        }

        function updateTimeline() {
            var count = getDataStorageCount();
            var photo = needPhoto();

            if(count){
                $("#graph").height((photo?89:14)+count*18);
                $("#info_block").show();
            } else {
                $("#graph").height(1);
                $("#info_block").hide();
                $(window).resize();
                return;
            }
            plot.resize();

            var index = count;
            var data = [];
            for(var id in DataStorage){
                if(!DataStorage[id].enable) continue;
                index--;

                var c = DataStorage[id].color;
                var d = [];
                var msg = [];
                var i;

                var l = DataStorage[id].trips.length;
                for(i=0; i< l; i++){
                    d.push( [DataStorage[id].trips[i].from.t*1000, 10+18*index, 8, 0, 0] );
                    d.push( [DataStorage[id].trips[i].to.t*1000, 0, 0, 0, 0] );
                }

                l = DataStorage[id].events.length;
                for(i=0; i< l; i++){
                    msg.push( [DataStorage[id].events[i].t*1000, 10+18*index] );
                }
                if(d.length)
                    data.push({
                        data:d,
                        fillArea:[{color:"#"+c.substr(2), representation:"symmetric", opacity:1}],
                        lines: { show: false, steps: true}
                    });
                if(msg.length)
                    data.push({
                        label:id,
                        data:msg,
                        points: {show:true, fillColor:"#00FFFF", radius: 3, lineWidth: 2},
                        color:"#FFFFFF"
                    });
            }
            plot.getOptions().yaxes[0].max = 18*count+(photo?78:2);
            plot.setData(data);
            plot.setupGrid();

            $(window).resize();
        }

        function showHoverInfo(item) {
            if (item) {
                if (previousPoint != item.dataIndex) {
                    previousPoint = item.dataIndex;

                    if($("#graph_hover"))$("#graph_hover").remove();
                    var msg_text = messagesUtils.getMessageText(DataStorage[item.series.label].events[item.dataIndex]);
                    $('<div id="graph_hover"><div class="text">'+msg_text+'</div><div class="tick"></div></div>')
                        .css({ display: "none" })
                        .appendTo("body").fadeIn(100);

                    $("#graph_hover").offset({top: item.pageY-18, left: item.pageX-310}).show();
                }
            } else {
                $("#graph_hover").remove();
                previousPoint = null;
            }
        }

        function initTrack(layer, color) {
            if(!layer) return;

            unitId = layer.getUnitId();
            var sess = wialon.core.Session.getInstance();
            var unit =sess.getItem( unitId );
            var unitMetrics = unit.getMeasureUnits();
            var n = unit.getName();
            var first = layer.getFirstPoint();
            var last = layer.getLastPoint();

            var eventIcon = L.icon({
                iconUrl: "img/wialonTrackPlayer/marker-green.png",
                iconSize: [17, 21],
                iconAnchor: [0, 11]
            });
            var markerIcon = L.icon({
                iconUrl: unit.getIconUrl(32),
                iconSize: [32, 32]
            });

            var lonlat = L.latLng(first.lat, first.lon);

            var marker = L.marker(lonlat, {icon: markerIcon, unitId: unitId});
            markersLayer.addLayer(marker);

            var stopMarker = L.marker(lonlat, {
                icon: stopIcon,
                parentMarker: marker
            });
            markersLayer.addLayer(stopMarker);

            var rotate = parseInt(unit.getCustomProperty("img_rot", "0"), 10) ? true: false;

            DataStorage[unitId] = {
                "unit": unit,
                "layer": layer,
                "marker": marker,
                "stopMarker": stopMarker,
                "eventIcon": eventIcon,
                "first": first,
                "last": last,
                "msgIntervals": [],
                "messages": [],
                "trips": [],
                "events": [],
                "eventsMarkers": [],
                "color": color,
                "enable": true,
                "rotate": rotate,
                "follow": false,
                "photos": false,
                "showPhoto": true,
                "commons": {},
                "sensors": {},
                "hover": false,
                "version": 0
            };

            DataStorage[unitId].commons = { speed:{show:true, name:$.localise.tr("Speed"), value:0, metrics: $.localise.tr(unitMetrics ? "mph" : "kph")} };
            var sens = unit.getSensors();
            for(var j in sens) {
                DataStorage[unitId].sensors[sens[j].id] = {
                    show: false,
                    name: sens[j].n,
                    value: "-"
                };
            }
            // }

            /* common part for add and update */
            /* head info - unitName, mileage and first-last points */
            var mileage = layer.getMileage();
            if (typeof mileage != 'undefined') {
                mileage /= 1000;
                unitMetrics && (mileage *= 0.621);
            } else mileage = "0";


            $("#info_main").html(
                n+" /"+wialon.util.String.sprintf("%.2f&nbsp;%s ",mileage, $.localise.tr("km"))+"/<br/>"+
                "<span>"+ wialon.util.DateTime.formatTime(first.time, 2, getUserDateTimeFormat().dateTimeToPrint) +" - "+ wialon.util.DateTime.formatTime(last.time, 0, getUserDateTimeFormat().dateTimeToPrint) +"</span>"
            );



            loadEvents(unitId);

            fitBoundsToMap();
        }

        function unitHover(marker, show) {
            var unitId = marker.options.unitId;
            if (show) {
                var content = getUnitHoverContent(unitId, $("#slider").slider("option", "value"), null);

                unitinfobox
                    .setLatLng(marker.getLatLng())
                    .setContent(content);

                if (!DataStorage[unitId].hover) {
                    map.openPopup(unitinfobox);
                    $(unitinfobox._container).css("border-color", "#" + DataStorage[unitId].color.substr(2));
                    DataStorage[unitId].hover = true;
                }

            } else {
                DataStorage[unitId].hover = false;
                map.closePopup(unitinfobox);
            }
        }

        function getUnitHoverContent(id, time, content) {
            if (content === null) {
                content = "";
                if(DataStorage[id].commons.speed.show)
                    content += "<div class='row'>"+DataStorage[id].commons.speed.name+"<span>"+DataStorage[id].commons.speed.value+" "+DataStorage[id].commons.speed.metrics+"</span></div>";
                for(var s in DataStorage[id].sensors)
                    if(DataStorage[id].sensors[s].show)
                        content += "<div class='row'>"+DataStorage[id].sensors[s].name+"<span>"+DataStorage[id].sensors[s].value+"</span></div>";
            }

            var datetime = wialon.util.DateTime.formatTime(time, 0, getUserDateTimeFormat().dateTime).split('_');

            content = "<div class='text'><div class='header'>"+
                "<div class='row'>"+DataStorage[id].unit.getName()+"</div>"+
                "<div class='row'><span style='margin:0px; margin-right:5px;'>"+datetime[0]+"</span>"+datetime[1]+"</div></div>"+
                content+"</div>";
            return content;
        }

        /** Load registered events
         */
        function loadEvents(unitId) {
            if(busy){
                setTimeout(function(){ loadEvents(unitId); },1000);
                return;
            }
            if(!DataStorage[unitId]) return;
            if(DataStorage[unitId].events.length) return;
            busy=true;
            var ml = wialon.core.Session.getInstance().getMessagesLoader();
            ml.unload(function(){
                ml.loadInterval(unitId, from, to, 1536, 65280, 1,
                    function(code, data){
                        if(code){
                            busy = false;
                            alert($.localise.tr("Unable to load events"));
                            return;
                        }
                        if(!DataStorage[unitId]) return;

                        var count = data.count;

                        if(count>1000){
                            alert(wialon.util.String.sprintf($.localise.tr("%d events have been found for %s. First 1000 of them will be shown.\nTo see other events, change time interval.") ,count, DataStorage[unitId].unit.getName()));
                            count=1000;
                        }
                        ml.getMessages(0, count, function(code, data){
                            busy = false;
                            if(code){ alert($.localise.tr("Unable to load events")); return; }
                            if(DataStorage[unitId]){
                                DataStorage[unitId].events = data;
                                showEventsOnMap(unitId);
                                updateTimeline();
                            }
                        });
                    }
                );
            });
        }

        /** Show registered events markers on map
         */
        function showEventsOnMap(id) {
            if(!DataStorage[id]) return;
            var len = DataStorage[id].events.length,
                content = "",
                marker,
                evt;
            for(var i = 0; i < len; i++) {
                evt = DataStorage[id].events[i];
                if (evt.x === 0 && evt.y === 0) {
                    DataStorage[id].eventsMarkers.push(null);
                    continue;
                }

                content = messagesUtils.getMessageText(DataStorage[id].events[i]);
                content = '<div ><div class="text">'+content+'</div><div class="tick"></div></div>';

                marker = L.marker(L.latLng(evt.y, evt.x), {icon: DataStorage[id].eventIcon}).bindPopup(content, {
                    className: "infoBox",
                    maxWidth: 315,
                    minWidth: 315,
                    offset: L.point(-170, 20)
                });

                DataStorage[id].eventsMarkers.push(marker);
                eventsLayer.addLayer(marker);
            }
        }

        function showUnitOnMap(evt) {
            var obj = evt.target;
            if(state == 'PLAY') // do not change map center in FOLLOW mode while PLAY
                for(var i in DataStorage)
                    if( DataStorage[i].follow && DataStorage[i].enable ) return;

            //var unitId = $(obj).parent().data("unitid");
            if(DataStorage[unitId].enable)
                map.panTo(DataStorage[unitId].marker.getLatLng());
        }

        function followUnit(evt) {
            evt.preventDefault();
            // var unitId = $(this).parent().parent().data("unitid");
            var follow = !DataStorage[unitId].follow;


            for(var i in DataStorage)
                if(DataStorage[i].follow && unitId!=i){
                    DataStorage[i].follow = false;
                    $("#toggle_follow_"+i).children("img").attr("src","img/wialonTrackPlayer/watch-on-map-dis.png");
                }

            DataStorage[unitId].follow = follow;
            $(this).children("img").attr("src",follow?"img/wialonTrackPlayer/watch-on-map.png":"img/wialonTrackPlayer/watch-on-map-dis.png");
            if( state=="STOP" )
                followUnits();
        }

        function storeMessages(id, j, code, data) {
            if(code || !DataStorage[id]){
                return;
            }
            DataStorage[id].msgIntervals[j] = data[0].t;
        }

        function initMessages(id) { // get messages for item
            var intCount =  Math.ceil(DataStorage[id].layer.getMessagesCount()/msgSize); // number of messages intervals
            DataStorage[id].msgIntervals[0] = DataStorage[id].first.time;
            DataStorage[id].msgIntervals[intCount] = DataStorage[id].last.time;

            wialon.core.Remote.getInstance().startBatch();
            for(var i=1; i<=intCount-1; i++){
                if(!DataStorage[id]) break;
                DataStorage[id].layer.getMessages(0, i*msgSize, i*msgSize, qx.lang.Function.bind(storeMessages, this, id, i));
            }
            wialon.core.Remote.getInstance().finishBatch( function(code){ if(code){ alert("Init messages error"); return;} });
            loadMessages(id, 0);
            if (intCount > 1) {
                loadMessages(id, intCount - 1);
            }
        }

        function loadMessages(id, l) {
            if (!DataStorage[id]) {
                return;
            }
            // check if already loaded
            if( DataStorage[id] && DataStorage[id].messages[l] && DataStorage[id].messages[l].length !==0){
                return;
            } else if( DataStorage[id].layer && l < DataStorage[id].msgIntervals.length-1 ){ // there are nonloaded intervals
                var to_msg = msgSize*(l+1)>DataStorage[id].layer.getMessagesCount() ? DataStorage[id].layer.getMessagesCount() : msgSize*(l+1);

                DataStorage[id].messages[l] = []; // indicate that LoadMessages already run
                DataStorage[id].layer.getMessages(0, msgSize*l, to_msg , qx.lang.Function.bind(function(l, version, code, data){
                    if(DataStorage[id] && DataStorage[id].version!=version)
                        return;

                    // First message speed
                    if (!l && DataStorage[id].first.time === data[0].t) {
                        DataStorage[id].first.speed = data[0].pos.s;
                    }

                    // Last message speed
                    if ( to_msg == DataStorage[id].layer.getMessagesCount() && DataStorage[id].last.time === data[data.length-1].t ) {
                        DataStorage[id].last.speed = data[data.length-1].pos.s;
                    }

                    if(code || !DataStorage[id] || !DataStorage[id].messages[l] || DataStorage[id].messages[l].length) return;
                    DataStorage[id].messages[l] = data; // save messages

                    for(var i=0; i< data.length; i++){
                        if(data[i].p.image){
                            var image = document.createElement('img');
                            image.className = "photo";
                            image.src = DataStorage[id].layer.getMessageImageUrl(0, msgSize*l+i, true);
                            image.style.marginLeft = (data[i].t-from)/(to-from)*100+"%";
                            image.onload = qx.lang.Function.bind(imageLoaded, image, id, i);

                            if(!DataStorage[id].photos){
                                DataStorage[id].photos = true;
                                $("#toggle_photo_"+id).css("display","block");
                                $("#photos").css("display",(needPhoto()?"block":"none"));
                                updateTimeline();
                            }
                        }
                    }

                    DataStorage[id].getMessagesId = setTimeout(function(){loadMessages(id, l+1);}, messagesLoadSpeed);
                    if(!l) activateUI( true ); //activate
                }, this, l, DataStorage[id].version));
            }
        }

        function imageLoaded(id, i) {
            var w = this.width, h = this.height;
            this.width = w * 70/h;
            this.height = 70;
            this.style.border = "1px solid #"+ DataStorage[id].color.substr(2);
            $(this).addClass("ph_"+id);
            $("#photos").append(this);
        }

        function activateUI(enable) {
            $("#control").css("display", (enable?"block":"none"));
            $("#slider").slider("option", "disabled", !enable);
            $("#range").slider("option", "disabled", !enable);
            $("#tostart_btn").attr("disabled", !enable);
            $("#stepleft_btn").attr("disabled", !enable);
            $("#play_btn").attr("disabled", !enable);
            $("#toend_btn").attr("disabled", !enable);
            $("#stepright_btn").attr("disabled", !enable);
            $("#settings_btn").attr("disabled", !enable);
            if(!enable){
                $("#step_wrapper").hide();
                $("#settings_dialog").hide();
            }
            $(window).resize();
        }

        function fitBoundsToMap() {

            var llb = L.latLngBounds([]);
            for(var i in DataStorage){
                if(DataStorage[i].follow){ refreshMap(); return; }
                if(!DataStorage[i].layer) continue;
                var b = DataStorage[i].layer.getBounds();

                llb.extend(L.latLng(b[0], b[1]));
                llb.extend(L.latLng(b[2], b[3]));
            }

            refreshMap();

            map.fitBounds(llb);
        }

        function refreshMap() { // redraw render layer
            if(map){
                map.eachLayer(function (l) {
                    if (l instanceof L.TileLayer.WebGisRender) {
                        l.setUrl(wialon.core.Session.getInstance().getBaseUrl() + "/adfurl" + new Date().getTime());
                    }
                });
            }
        }

        function fastSlow(faster) {
            var value = $("#step").slider("value");
            if (faster) {
                value++;
            } else {
                value--;
            }
            $("#step").slider("value", value);
        }

        function playPause() {
            if(state=="STOP"){
                $("#play_btn").css("background","url('img/wialonTrackPlayer/pause.png') no-repeat");
                state = "PLAY";
                slide();
            } else {
                pause();
            }
        }

        function pause() {
            if(state == "PLAY"){
                freeze();
                state = "STOP";
                $("#play_btn").css("background","url('img/wialonTrackPlayer/play.png') no-repeat");
            }
        }

        function stepTime(right) {
            pause();
            var time = $("#slider").slider("option", "value");
            var step = $("#step").slider("option", "value");
            slideToTime(right ? time + step : time - step);
        }

        function slideToTime(time, play) {
            var range = $("#range").slider("option","values");

            if(time < range[0]){ time = range[0]; pause(); }
            if(time > range[1]){ time = range[1]; pause(); }

            $("#slider").slider("option","value",time);

            if(play){
                freeze();
                // wait 3*speed after skip
                setTimeout( function(){ state = "PLAY"; slide(); }, 3*speed);
            }
        }

        function showTime(time) {
            if(typeof(wialon)=="undefined") return;
            var str = (wialon.util.DateTime.formatTime(time, 0, getUserDateTimeFormat().dateTime)).split('_');
            $("#t_curr .d").html(str[0]);
            $("#t_curr .t").html(str[1]);

        }

        function slide() {
            var range = $("#range").slider("option","values");
            var slide_start = new Date();
            var time = $("#slider").slider("option","value");
            var step = $("#step").slider("option","value");

            time_prev = time;
            time += step;

            slideToTime(time);

            if( state=="PLAY" && (time+step <= range[1]) ){
                var slide_end = new Date();
                if(slide_end - slide_start < speed)
                    idTimeout = setTimeout(slide, speed - (slide_end - slide_start));
                else slide();
            } else {
                pause();
            }
        }

        function moveCar(time) {
            var LatLngList = [],
                isAnyMove = false,
                nextTripTime = null;

            for(var i in DataStorage){
                if(!DataStorage[i].enable) continue;

                var pos = findPos(time, i);
                if( pos.ok ){
                    var hoverContent = "";
                    DataStorage[i].lastPos = pos;

                    showStopInfo( pos, i );
                    var latlng = L.latLng(pos.y, pos.x);

                    DataStorage[i].marker.setLatLng(latlng);

                    /* follow */
                    if(DataStorage[i].follow) {
                        LatLngList.push(latlng);
                    }

                    /* speed */
                    var sp = pos.speed || 0;
                    DataStorage[i].unit.getMeasureUnits() && (sp *= 0.621);

                    if(DataStorage[i].commons.speed.show)
                        hoverContent += "<div class='row'>"+$.localise.tr("Speed")+"<span>"+sp.toFixed(2)+" "+DataStorage[i].commons.speed.metrics+"</span></div>";

                    DataStorage[i].commons.speed.value = Math.round(sp);
                    $("#unit_speed_main").html( DataStorage[i].commons.speed.value );

                    /* hover */
                    if(DataStorage[i].hover){
                        var content = getUnitHoverContent(i, time, hoverContent);
                        unitinfobox
                            .setLatLng(latlng)
                            .setContent(content);
                    }

                    if(pos.trip){
                        isAnyMove = isAnyMove || pos.trip.move;
                        if( pos.trip.tripIndex!==undefined && pos.trip.tripIndex+1<DataStorage[i].trips.length){
                            var trip_time = DataStorage[i].trips[pos.trip.tripIndex+1].from.t;
                            if( trip_time<nextTripTime || nextTripTime===null )
                                nextTripTime = trip_time;
                        }
                    }
                } else {
                    isAnyMove = true;
                    if(DataStorage[i].follow) LatLngList.push(DataStorage[i].marker.getLatLng());
                }
            }
            if(!isAnyMove && nextTripTime!==null && skipBeetweenTrips && state=="PLAY"){
                freeze();
                state="STOP";
                slideToTime(nextTripTime, true);
                return;
            }

            followUnits(LatLngList);
        }

        function followUnits(LatLngList) {
            if(!LatLngList){
                LatLngList = [];
                for(var i in DataStorage){
                    if(!DataStorage[i].enable) continue;
                    if(DataStorage[i].follow) LatLngList.push(DataStorage[i].marker.getLatLng());
                }
            }
            if(LatLngList.length == 1){
                map.setView(LatLngList[0], undefined, {animate: false});
            }
        }

        /* DO NOT TOUGHT IF DONT KNOW WHAT YOU ARE DOING */
        function findPos(time, i) {
            var obj = { "ok":true, "move":true, "time":time, "speed":0 };
            var first = DataStorage[i].first;
            var last = DataStorage[i].last;

            if (time === first.time) {
                obj.speed = first.speed || 0;
            }
            else if (time === last.time) {
                obj.speed = last.speed || 0;
            }

            if( time <= first.time ){// before first
                obj.x = first.lon;
                obj.y = first.lat;
                obj.move = false;
                obj.trip = {move:false, tripIndex:-1};
            } else if (time >= last.time ){// after last
                obj.x = last.lon;
                obj.y = last.lat;
                obj.move = false;
            } else {

                var msgInt;
                // check previous
                var lp = DataStorage[i].lastPos;
                // if stil in same interval
                if( lp && DataStorage[i].msgIntervals[lp.msgInt]<=time && DataStorage[i].msgIntervals[lp.msgInt+1]>time){
                    msgInt = lp.msgInt;
                } else { // else - have to find interval
                    msgInt = findMsgInterval(DataStorage[i].msgIntervals, time);
                    if(!DataStorage[i].messages[msgInt]){ // messages not loaded
                        clearTimeout(DataStorage[i].getMessagesId); // stop load
                        freeze();
                        loadMessages(i, msgInt);
                        obj.ok = false;
                        return obj;
                    }
                }
                // if messages not loaded yet
                if(!DataStorage[i].messages[msgInt] || !DataStorage[i].messages[msgInt].length){
                    obj.ok = false;
                    return obj;
                }

                obj.msgInt = msgInt; // save interval index for next time

                var index; // index of message in message[] array
                // check previous
                if(lp && lp.ind>0 && lp.ind < DataStorage[i].messages[msgInt].length &&
                    DataStorage[i].messages[msgInt][lp.ind].t<time && DataStorage[i].messages[msgInt][lp.ind+1].t>=time)
                {   // if still between same points (messages)
                    index = lp.ind;
                } else { // else - need to find //OPTIMIZATION - check next message
                    index = findRightPlace( DataStorage[i].messages[msgInt], 0, DataStorage[i].messages[msgInt].length-1, time);
                }

                if(index == -1) { obj.ok = false; return obj; }

                if(!DataStorage[i].trips || ! DataStorage[i].trips.length) {
                    obj.ok = false;
                    return obj;
                }
                var trip = isMove( DataStorage[i].trips, 0, DataStorage[i].trips.length-1, time);

                // get "begin" and "end" of line where unit in cur time
                var m1 = DataStorage[i].messages[msgInt][index], m2 = DataStorage[i].messages[msgInt][index+1];

                // save message index for next time
                obj.ind = index;
                obj.message = m1;
                obj.trip = trip;

                var move = trip.move;
                if(!move && trip.pos){ // not move (not in trip) and position was found in trip info
                    obj.x = trip.pos.to.p.x;
                    obj.y = trip.pos.to.p.y;
                    obj.move = false;
                } else {  // msgInt - index of interval of messages where unit in time

                    var t = index, tt = msgInt;
                    while( !m1.pos ){
                        if(t>=0) m1 = DataStorage[i].messages[tt][t--];
                        else if(tt>0) {
                            tt--;
                            if(DataStorage[i].messages[tt] && DataStorage[i].messages[tt].length)
                                t=DataStorage[i].messages[tt].length-1;
                            else break;
                        } else break;
                    }

                    t = index+1, tt = msgInt;
                    while( !m2.pos ){
                        if(t<DataStorage[i].messages[tt].length) m2 = DataStorage[i].messages[tt][t++];
                        else if(tt<DataStorage[i].messages.length-2) {
                            tt++;
                            if(DataStorage[i].messages[tt] && DataStorage[i].messages[tt].length)
                                t=0;
                            else break;
                        } else break;
                    }

                    if( !m1.pos || !m2.pos ){ // if position unknown - leave...
                        obj.ok = false;
                    } else if(move){ // if unit moves - calculate point on line
                        var ko = (time-m1.t)/(m2.t-m1.t);
                        obj.x = m1.pos.x + (m2.pos.x-m1.pos.x)*ko;
                        obj.y = m1.pos.y + (m2.pos.y-m1.pos.y)*ko;
                        obj.speed = m1.pos.s + (m2.pos.s-m1.pos.s)*ko;

                        if(Math.abs(m2.pos.y-m1.pos.y)<0.00001 && Math.abs(m2.pos.x-m1.pos.x)<0.00001)
                            obj.course = m1.pos.c;
                        else{
                            var p1 = getMeters(obj.x, obj.y);
                            var p2 = getMeters(m2.pos.x, m2.pos.y);
                            var angle = Math.atan2(p2.x-p1.x, p2.y-p1.y);
                            var degrees = (angle*180/Math.PI);

                            obj.course = degrees;
                        }
                    } else { // unit doesnt move
                        obj.x = m1.pos.x;
                        obj.y = m1.pos.y;
                        obj.move = false;
                    }
                }
            }
            return obj;
        }

        function showStopInfo(pos, id) { // show stop marker when unit doesnt move
            var latlng = L.latLng(pos.y, pos.x);
            if (pos.move) {
                markersLayer.removeLayer(DataStorage[id].stopMarker);
            } else {
                DataStorage[id].stopMarker.setLatLng(latlng).addTo(markersLayer);
            }
        }

        function getDataStorageCount() { // get count of shown tracks
            var count = 0;
            for(var i in DataStorage) if(DataStorage[i].enable) count++;
            return count;
        }

        function needPhoto() {
            for(var i in DataStorage){
                if(DataStorage[i].enable && DataStorage[i].photos && DataStorage[i].showPhoto)
                    return true;
            }
            return false;
        }

        /* binary search */
        function findRightPlace(arr, imin, imax, time) {
            if( imax-imin==1){
                if(arr[imin].t<time && arr[imax].t>=time) return imin;
                else return -1;
            }
            var imid = parseInt((imax+imin)/2, 10);
            if(arr[imid].t>=time)
                return findRightPlace( arr, imin, imid, time);
            else if (arr[imid].t<=time)
                return findRightPlace( arr, imid, imax, time);
            else return -1;
        }

        function isMove(arr, imin, imax, time) {
            var obj = {"move":false};
            if( imin>imax){
                if(imax >-1)
                    obj.pos = arr[imax];
                obj.tripIndex = imax;
                return obj;
            }
            var imid = parseInt((imax+imin)/2, 10);
            if(arr[imid].from.t>time)
                return isMove( arr, imin, imid-1, time);
            else if (arr[imid].to.t<time)
                return isMove( arr, imid+1, imax, time);
            else if(arr[imid].from.t<=time && arr[imid].to.t>=time){
                obj.tripIndex = imid;
                obj.move = true;
                return obj;
            }else{
                return obj;
            }
        }

        function findMsgInterval(arr, time) {
            var res = -1;
            for(var i=0; i<arr.length-1; i++)
                if(arr[i]<=time && arr[i+1]>time){
                    res = i; break;
                }
            return res;
        }

        $(window).resize(function(){
            var h = $(window).height();
            var w = $(window).width();
            if(h<200) h = 200;
            if(w<400) w = 400;

            if(plot){
                plot.resize();
                plot.draw();
            }
            // DOM elements
            var $map = $("#map");
            // var $menu = $("#menu");
            var $control = $("#control");
            var $playline_block = $("#playline_block");
            var $track_data_block = $("#track_data_block");

            var add_h = 5;
            if($control.css("display") == "block") {
                $control.height( $track_data_block.height() + $playline_block.height() + $("#info_block").height());
            } else {
                $control.height(0);
                add_h = 2;
            }

            $(".timeline_wrapper").width($control.width() - $(".b1", $playline_block).width() - $(".b2", $playline_block).width() - 45 );

            $map.css("bottom", $control.height() + add_h);
        });

        function ltranslate(){
            $("#tr_skip_trips").html($.localise.tr("Skip intervals between trips"));

            $("#step_val").attr("title", $.localise.tr("Playback speed"));

            Messages.prototype.translation = {
                bytes_counter_is: $.localise.tr("GPRS traffic counter value: %d KB."),
                bytes_counter_reset: $.localise.tr("GPRS traffic counter reset. %d KB consumed."),
                engine_hours_counter_is: $.localise.tr("Engine hours counter value is %d h."),
                engine_hours_counter_reset: $.localise.tr("Engine hours counter value was changed from %d h to %d h."),
                mileage_counter_is: $.localise.tr("Mileage counter value is %d km."),
                mileage_counter_reset: $.localise.tr("Mileage counter value was changed from %d km to %d km."),
                rt_aborted: $.localise.tr("Route '%s': round aborted."),
                rt_arrived: $.localise.tr("Route '%s': arrival at point '%s'."),
                rt_departured: $.localise.tr("Route '%s': departure from point '%s'."),
                rt_finished: $.localise.tr("Route '%s': round finished."),
                rt_in_time: $.localise.tr("Route '%s': unit returned to schedule."),
                rt_skipped: $.localise.tr("Route '%s': point '%s' skipped."),
                rt_started: $.localise.tr("Route '%s': round by schedule '%s' started."),
                rt_too_early: $.localise.tr("Route '%s': unit is ahead of schedule."),
                rt_too_late: $.localise.tr("Route '%s': unit is late."),
                zone_in: $.localise.tr("Route %s: entered %s"),
                zone_out: $.localise.tr("Route %s: left %s"),
                route_event: $.localise.tr("Route"),
                event: $.localise.tr("Event"),
                maintenance: $.localise.tr("Filling"),
                reg_filling: $.localise.tr("Maintenance"),
                violation: $.localise.tr("Violation")
            };

            messagesUtils = new Messages();
        }

        $(document).ready(function(){
            $(window).resize();

            LANG = "ru";
            localise();//('lang/', {language: LANG});
            ltranslate();

            var playbackSpeed = null;

            if (!playbackSpeed || playbackSpeed<1)
                playbackSpeed = 1;
            else if (playbackSpeed>9)
                playbackSpeed = 9;

            init();

            $("#slider").slider({
                range: "min",
                step: 1,
                disabled:true,
                change: function(evt, ui){
                    $("#info_block").slider("option", "value", ui.value);
                },
                slide: function(evt, ui){
                    var values = $("#range").slider("option","values");
                    if(ui.value < values[ 0 ]){
                        $(this).slider("option", {value: values[0]});
                        return false;
                    }
                    if(ui.value > values[ 1 ]){
                        $(this).slider("option", {value: values[1]});
                        return false;
                    }

                    $("#info_block").slider("option",{value:ui.value});
                    return true;
                },
                start: function(evt, ui){
                    if(state=="PLAY"){
                        freeze();
                        state = "STOP";
                        paused = true;
                    }
                },
                stop: function(){
                    if (paused) {
                        paused = false;
                        state = "PLAY";
                        slide();
                    }
                }
            });
            $("#info_block").slider({
                step: 1,
                disabled:true,
                change:function(evt, ui){
                    showTime(ui.value);
                    moveCar(ui.value);
                }
            });

            $("#range").slider({
                range: true,
                step: 1,
                disabled:true,
                slide: function(evt,ui){
                    if(ui.values[1]-ui.values[0] < minRange){
                        return false;
                    }
                    changeSliderRange(evt, ui, false, true);
                    return true;
                },
                start: function(){ $("body").css("cursor","move"); },
                stop: function(){ $("body").css("cursor","auto"); }
            });

            $("#range .ui-slider-handle").dblclick(function(){
                var opt = $("#range").slider("option","values");
                changeSliderRange(null, {handle:true, values:[opt[0], to]}, false, false);
                changeSliderRange(null, {handle:{nextSibling:true}, values:[from, to]}, false, false);
                $("#range").slider("option","values",[from, to]);
            });

            $("#play_btn").click(playPause);

            $("#tostart_btn").click( function(){ slideToTime(from); });
            $("#toend_btn").click( function(){ slideToTime(to); });

            $("#stepleft_btn").click(stepTime);
            $("#stepright_btn").click(function () {
                stepTime(1);
            });

            var $settingsDialog = $("#settings_dialog");
            $("#settings_btn").click(function(evt){
                if ($settingsDialog.css("display") != 'block') {
                    $settingsDialog.show();
                    evt.stopPropagation();
                }
            });

            var $stepWrapper = $("#step_wrapper");
            $("#step_val").click(function(evt){
                if($stepWrapper.css("display") != "block"){
                    $stepWrapper.show().focus();
                    evt.stopPropagation();
                }
            });

            $("#step").slider({
                orientation:"vertical",
                min:1,
                max:9,
                step:1,
                value: playbackSpeed,
                slide: function(evt, obj){
                    $("#step_val").html(obj.value/speed*1000+"x");
                },
                change: function(evt, obj){
                    $("#step_val").html(obj.value/speed*1000+"x");
                }
            });
            $("#step_val").html(playbackSpeed/speed*1000+"x");

            $('#toggle_follow_main').click(followUnit);
            $('#unit_track_main').click(showUnitOnMap);

            $("#skip_trips_ch").on("change", function () {
                skipBeetweenTrips = this.checked;
            });

            var $dialogs = $(".dialog");
            $("body")
                .on("click", function (evt) {
                    var $elem = $(evt.target);
                    while ($elem.length && (!$elem.hasClass("dialog") && $elem.attr("tagName") != "BODY")) {
                        $elem = $elem.parent();
                    }
                    if (!$elem.hasClass("dialog")) {
                        $dialogs.each(function (i, elem) {
                            if ($(elem).css("display") == "block") {
                                $(elem).hide();
                            }
                        });
                    }
                })
                .on("keydown", function (evt) {
                    evt = evt || event;
                    switch (evt.keyCode) {
                        case 32: playPause(); evt.preventDefault(); break; // space btn
                        case 37: stepTime(0); evt.preventDefault(); break; //left
                        case 38: fastSlow(1); evt.preventDefault(); break; // up
                        case 39: stepTime(1); evt.preventDefault(); break; //right
                        case 40: fastSlow(0); evt.preventDefault(); break; // down
                    }
                });

            $(".ui-slider-handle").removeAttr("href");
        });

        /* functions for work with time */
        function get_local_timezone() {
            var rightNow = new Date();
            var jan1 = new Date(rightNow.getFullYear(), 0, 1, 0, 0, 0, 0);  // jan 1st
            var june1 = new Date(rightNow.getFullYear(), 6, 1, 0, 0, 0, 0); // june 1st
            var temp = jan1.toGMTString();
            var jan2 = new Date(temp.substring(0, temp.lastIndexOf(" ")-1));
            temp = june1.toGMTString();
            var june2 = new Date(temp.substring(0, temp.lastIndexOf(" ")-1));
            var std_time_offset = ((jan1 - jan2) / (1000 * 60 * 60));
            var daylight_time_offset = ((june1 - june2) / (1000 * 60 * 60));
            var dst;
            if (std_time_offset == daylight_time_offset) {
                dst = "0"; // daylight savings time is NOT observed
            } else {
                // positive is southern, negative is northern hemisphere
                var hemisphere = std_time_offset - daylight_time_offset;
                if (hemisphere >= 0)
                    std_time_offset = daylight_time_offset;
                dst = "1"; // daylight savings time is observed
            }

            return parseInt(std_time_offset*3600,10);
        }

        /* reproject lon/lat to meters */
        function getMeters(x, y){
            var Rad = y * Math.PI/180;
            var FSin = Math.sin(Rad);
            return {
                x: x * 0.017453292519943 * 6378137,
                y: 6378137 / 2.0 * Math.log((1.0 + FSin) / (1.0 - FSin))
            };
        }

        function localise() {
            TRANSLATIONS = {
                "Track player": "Проигрыватель треков",
                "Monitoring info": "Информация для мониторинга",
                "Interval": "Интервал",
                "Today": "Сегодня",
                "Yesterday": "Вчера",
                "Current week": "Текущая неделя",
                "Current month": "Текущий месяц",
                "7 days": "7 дней",
                "30 days": "30 дней",
                "Custom interval": "Выбрать вручную",
                "Unit": "Объект",
                "Change interval": "Изменить интервал",
                "Show track": "Построить трек",
                "Tracks": "Треки",
                "Playback speed": "Скорость воспроизведения",
                "Rotate icon": "Вращать иконку",
                "Double-click on necessary sensors below to track them.": "Для отслеживания необходимых значений датчиков выберите их двойным щелчком из списков ниже.",
                "General information": "Общая информация",
                "Hide settings": "Скрыть настройки",
                "Sensors": "Датчики",
                "Skip intervals between trips": "Пропускать интервалы между поездками",
                "Prev": "Назад",
                "Next": "Вперед",
                "Login error, restart the application": "Ошибка логина, перезапустите приложение",
                "Wrong datetime format": "Неверный формат даты",
                "Invalid time interval": "Неверный интервал времени",
                "Select unit": "Выберите объект",
                "There is a track for this unit already": "Для выбранного объекта трек уже построен",
                "No messages for the selected interval": "Для выбранного интервала сообщений не найдено",
                "Unable to build a track": "Не удалось построить трек",
                "Unable to load trips": "Не удалось загрузить поездки",
                "Session error, restart the application": "Ошибка сессии. Перезапустите приложение",
                "Init messages error": "Ошибка при инициализации сообщений",
                "Nothing to delete, no tracks for the given unit": "Нечего удалять, трек для данного объекта не построен",
                "Hide/show track": "Скрыть/показать трек",
                "Focus on unit": "Центрировать карту на объекте",
                "Delete track": "Удалить трек",
                "Settings": "Настройки",
                "Follow the unit on map": "Следовать за объектом",
                "Show/hide pictures": "Показать/скрыть фотографии",
                "Show/hide parameters": "Скрыть/показать параметры",
                "Speed": "Скорость",
                "mph": "ми/ч",
                "kph": "км/ч",
                "miles": "миль",
                "km": "км",
                "Go to the first message": "Перейти к первому сообщению",
                "Beginning": "Начало",
                "Go to the last message": "Перейти к последнему сообщению",
                "End": "Конец",
                "Unable to load events": "Не удалось загрузить события",
                "%d events have been found for %s. First 1000 of them will be shown.\nTo see other events, change time interval.": "%d событий было обнаружено для объекта %s. Будут показаны первые 1000 событий.\nДля отображения других событий измените интервал времени.",
                "January": "Январь",
                "February": "Февраль",
                "March": "Март",
                "April": "Апрель",
                "May": "Май",
                "June": "Июнь",
                "July": "Июль",
                "August": "Август",
                "September": "Сентябрь",
                "October": "Октябрь",
                "November": "Ноябрь",
                "December": "Декабрь",
                "Jan": "Янв",
                "Feb": "Фев",
                "Mar": "Мар",
                "Apr": "Апр",
                "Jun": "Июн",
                "Jul": "Июл",
                "Aug": "Авг",
                "Sep": "Сен",
                "Oct": "Окт",
                "Nov": "Ноя",
                "Dec": "Дек",
                "Sunday": "воскресенье",
                "Monday": "понедельник",
                "Tuesday": "вторник",
                "Wednesday": "среда",
                "Thursday": "четверг",
                "Friday": "пятница",
                "Saturday": "суббота",
                "Sun": "вск",
                "Mon": "пнд",
                "Tue": "втр",
                "Wed": "срд",
                "Thu": "чтв",
                "Fri": "птн",
                "Sat": "сбт",
                "Su": "Вс",
                "Mo": "Пн",
                "Tu": "Вт",
                "We": "Ср",
                "Th": "Чт",
                "Fr": "Пт",
                "Sa": "Сб",
                "Time": "Время",
                "Hours": "Часы",
                "Minutes": "Минуты",
                "Seconds": "Секунды",
                "Absolute engine hours": "Абсолютные моточасы",
                "Absolute fuel consumption sensor": "Датчик абсолютного расхода топлива",
                "Accelerometer": "Акселерометр",
                "Counter sensor": "Счетчик",
                "Custom sensor": "Произвольный датчик",
                "State sensor": "Датчик состояния",
                "Custom digital sensor": "Произвольный цифровой датчик",
                "Driver binding": "Привязка водителя",
                "Engine efficiency sensor": "Датчик полезной работы двигателя",
                "Engine ignition sensor": "Датчик зажигания",
                "Engine revs sensor": "Датчик оборотов двигателя",
                "Equipment binding": "Привязка оборудования",
                "Fuel level sensor": "Датчик уровня топлива",
                "Impulse fuel consumption sensor": "Импульсный датчик расхода топлива",
                "Fuel level impulse sensor": "Импульсный датчик уровня топлива",
                "Instant fuel consumption sensor": "Датчик мгновенного расхода топлива",
                "litres": "литров",
                "m": "м",
                "Mileage sensor": "Датчик пробега",
                "Relative odometer": "Относительный одометр",
                "On/Off": "Вкл/Выкл",
                "Relative engine hours": "Относительные моточасы",
                "rpm": "об/мин",
                "Enter sensor name, type, description or parameter.": "Введите имя датчика либо его тип, описание или параметр",
                "Temperature coefficient": "Коэффициент температуры",
                "Temperature sensor": "Датчик температуры",
                "Trailer binding": "Привязка прицепа",
                "V": "В",
                "Voltage sensor": "Датчик напряжения",
                "on": "Включено",
                "off": "Выключено",
                "GPRS traffic counter value: %d KB.": "Текущее значение счетчика трафика: %d Кб.",
                "GPRS traffic counter reset. %d KB consumed.": "Сброс счетчика GPRS-трафика. %d Кб потрачено.",
                "Engine hours counter value is %d h.": "Значение счетчика моточасов - %d ч.",
                "Engine hours counter value was changed from %d h to %d h.": "Значение счетчика моточасов изменено с %d ч на %d ч.",
                "Mileage counter value is %d km.": "Значение счетчика пробега - %d км.",
                "Mileage counter value was changed from %d km to %d km.": "Значение счетчика пробега изменено с %d км на %d км.",
                "Route '%s': round aborted.": "Маршрут '%s': рейс прерван.",
                "Route '%s': arrival at point '%s'.": "Маршрут '%s': прибытие в точку '%s'.",
                "Route '%s': departure from point '%s'.": "Маршрут '%s': отправление из точки '%s'.",
                "Route '%s': round finished.": "Маршрут '%s': рейс завершен.",
                "Route '%s': unit returned to schedule.": "Маршрут '%s': объект вернулся в расписание.",
                "Route '%s': point '%s' skipped.": "Маршрут '%s': точка '%s' пропущена.",
                "Route '%s': round by schedule '%s' started.": "Маршрут '%s': рейс по расписанию '%s' начат.",
                "Route '%s': unit is ahead of schedule.": "Маршрут '%s': объект движется с опережением.",
                "Route '%s': unit is late.": "Маршрут '%s': объект опаздывает.",
                "Route %s: entered %s": "Маршрут %s: вход в %s",
                "Route %s: left %s": "Маршрут %s: выход из %s",
                "Route": "Маршрут",
                "Event": "Событие",
                "Filling": "Заправка",
                "Maintenance": "Техобслуживание",
                "Violation": "Нарушение"
            };
        }

    },
	logout: function(){
			var user = wialon.core.Session.getInstance().getCurrUser(); //  user
			if (!user) return;
			wialon.core.Session.getInstance().logout( // Если пользователь существует - выход из системы
				function (code) { // logout callback
					if (code) console.log('WIALON. Ошибка выхода из системы: '+wialon.core.Errors.getErrorText(code)); // Ошибка выхода из системы, ошибка печати
					else console.log("WIALON. Завершение, выход из системы"); // Завершение, выход из системы
				}
			);
	}
}