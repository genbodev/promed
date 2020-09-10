
Ext.define('stores.smp.GeoserviceTransportStore', {
    extend: 'Ext.data.Store',
	autoLoad: false,
	stripeRows: true,
	storeId: 'GeoserviceTransportStore',
	fields: [
		{name: 'GeoserviceTransport_id', type: 'int'},
		{name: 'GeoserviceTransport_name', type: 'string'},	
		{name: 'lat', type: 'string'},
		{name: 'lng', type: 'string'},
		{name: 'direction', type: 'string'},
		{name: 'groups', type: 'auto'}	
	],
	proxy: {
		type: 'ajax',
		url: '/?c=GeoserviceTransport&m=getGeoserviceTransportListWithCoords',
		reader: {
			type: 'json',
			successProperty: 'success',
			root: 'data'
		},
		limitParam: undefined,
		startParam: undefined,
		paramName: undefined,
		pageParam: undefined
	},
	//Тип геосервиса. Возможные значения: [wialon , tnc]
	_type: '', 
	// Объект интервала автообновления через Ajax
	_ajaxAutoRefreshInterval: null, 
	// Объект интервала автообновления через сокет NodeJS
	_socketAutoRefreshInterval: null,
	// Задержка интервала автообновления [ мс ]
	_autoRefreshDelay: 30000,
	// Объект интервала автообновления через сокет NodeJS для Wialon
	_wialonSocketAutoRefreshInterval: null,
	// Флаг отображающий текущее состояние автозагрузки (запущена / не запущена)
	_autoRefresh: false,
	/**
	 * Получение типа геосервиса
	 */
	/*
	defineGeoserviceType : function(callback) {
		var opts = getGlobalOptions();
		
		if (!opts || !opts.region || !opts.region.number) {
			callback( this.defaultType );
			return;
		}
		
		// В перспективе будет запрос на получение типа геосервиса,
		// чтобы определялся в одном месте для всего промеда
		
		switch (opts.region.number) {
			case 2:
				this._type = 'TNC';
				break;
			default:
				this._type = 'Wialon';
				break;
		}
		if (typeof callback == 'function') {
			callback( this._type );
		}
		
		return this._type;
		
	},
	*/
	/**
	 * @public Метод запуска автоматического обновления данных
	 */
	runAutoRefresh: function() {
		
		// Если автозагрузка запущена, запускаем событие загрузки стора
		if (this._autoRefresh) {
			this.fireEvent('load', this, this.getRange() , true)
			return;
		}
		
		// Устанавливаем флаг автозагрузки
		this._autoRefresh = true;
		
		//log('runAutoRefresh');
		var store = this;
		
		//Определяем тип геосервиса
		/*this.defineGeoserviceType(function( ){
			//Устанавливаем тип сервиса как параметр для ajax-запроса к серверу
			this.getProxy().setExtraParam('geoservice_type',this._type);
			*/
			//Подключаемся к NodeJS, получаем сокет
			connectNode(this);
			
			var socket = this.socket;
			if (socket && getRegionNick() != 'ufa') {
				
				
				// При соединении (первичном или после разрыва соединения)
				socket.on('connect', function(){
					log('connect socket');
					//до аутентификации нет смысла стартовать обновление через socket т.к. нод проигнорирует событие
					// перенес по событию authentificated
					/*
					//Останавливаем автообновление через ajax
					this._stopAjaxAutoRefresh();
					//Запускаем автообновление через сокет NodeJS
					this._startSocketAutoRefresh();
					*/
				}.bind(this));

				socket.on('disconnect',function(){
					//Обновляем хранилище через ajax
					this.load();
					//Запускаем автообновление через сокет ajax
					this._startAjaxAutoRefresh();
					//Останавливаем автообновление через сокет NodeJS
					this._stopSocketAutoRefresh();
				}.bind(this))

				//После подключения к NodeJS происходит аутентификация. 
				//Это занимает какое-то время. 
				//Поэтому получать данные при подключении к NodeJS будем только 
				//после события аутентификации.

				store.socket.on('authentificated',function(){
					this._doSocketRefresh();

					this._startSocketAutoRefresh();
				}.bind(this))

				if (!this.socket.connected) {

					//Если сокет не подключен, загружаем данные через ajax
					//и запускаем таймер авторефреша через ajax

					//store.load();
					store._startAjaxAutoRefresh();
				} else {

					//Если сокет не подключен, загружаем данные через сокет NodeJS
					//и запускаем таймер авторефреша через сокет NodeJS

					this._doSocketRefresh();
					this._startSocketAutoRefresh();
				}
			} else {
				this.load();
				store._startAjaxAutoRefresh();
				// log({Error: 'не получен объект сокета' });
			}
			
		/*}.bind(this));*/
		
	},
	/**
	 * @public Метод остановки автоматического обновления данных
	 */
	stopAutoRefresh: function() {
		// Сбрасываем флаг автозагрузки
		this._autoRefresh = false;
		// log('stopAutoRefresh');
		this._stopSocketAutoRefresh();
		this._stopAjaxAutoRefresh();
	},
	/**
	 * @public метод получения дополнительных параметров запроса
	 */
	getExtraParamsFn: Ext.emptyFn,
	/**
	 * @private Метод запуска автоматической загрузки данных Ajax-ом
	 */
	_startAjaxAutoRefresh: function() {
		var store = this;
		this._ajaxAutoRefreshInterval = setInterval(function(){
			//для тнц устанавливаем ограничение на армы
			var activeWin = Ext.WindowManager.getActive();
			var extra = store.getExtraParamsFn();
			if (extra) {
				for (var key in extra) {
					if (extra.hasOwnProperty(key)) {
						store.getProxy().setExtraParam( key, extra[key] );
					}
				}
			}

			store.reload();
		},this._autoRefreshDelay);
	},
	/**
	 * @private Метод остановки автоматической загрузки данных Ajax-ом
	 */
	_stopAjaxAutoRefresh: function() {
		// log('_stopAjaxAutoRefresh');
		clearInterval(this._ajaxAutoRefreshInterval);
	},
	/**
	 * @private Метод запуска автоматической загрузки данных через сокет NodeJS 
	 */
	_startSocketAutoRefresh: function() {
		var store = this;
		//console.log('hasListeners?', store.socket.hasListeners('changeGeoserviceTransportList'));
		if(!store.socket.hasListeners('changeGeoserviceTransportList')){
			//запускаем интервал на ноде (получение трекеров)
			store.socket.emit('setUpdateGeoserviceTransportList', false, function (data) {
				if(data.statement) log(data.statement);
			});
			//слушаем нод (получение трекеров)
			store.socket.on('changeGeoserviceTransportList', function (data,p) {
				//при ответе от Нода останавливаем автообновление через ajax
				store._stopAjaxAutoRefresh();
				
				var data = Ext.JSON.decode(data,true);
				if (!data || data.length == 0) {log({ERROR: data});	return false; };
				log('NODE TransportList: '+data.length);
				store.loadData(data);
				store.fireEvent('load', store, store.getRange() , true);
			} );
		}
		
		// log('_startSocketAutoRefresh');
		/*switch (this._type ) {
			case 'Wialon':
				this._startWialonSocketAutoRefresh();
				break;
			case 'TNC':
				this._startTNCSocketAutoRefresh();
				break;
			default:
				// Ничего
				break;
		}*/
	},
	/**
	 * @private Метод остановки автоматической загрузки данных через сокет NodeJS 
	 */
	_stopSocketAutoRefresh: function() {
		// log('_stopSocketAutoRefresh');
		/*switch (this._type ) {
			case 'Wialon':
				this._stopWialonSocketAutoRefresh();
				break;
			case 'TNC':
				this._stopTNCSocketAutoRefresh();
				break;
			default:
				// Ничего
				break;
		}*/
		var store = this;
		store.socket.removeListener('changeGeoserviceTransportList');
	},
	/**
	 * @private Метод обновления данных через сокет NodeJS
	 */
	_doSocketRefresh: function() {
		// log('_doSocketRefresh');
		/*switch (this._type ) {
			case 'Wialon':
				this._doWialonSocketRefresh();
				break;
			case 'TNC':
				this._doTNCSocketRefresh();
				break;
			default:
				// Ничего
				break;
		}*/
	},
	/**
	 * @private Метод обновления данных Wialon через сокет NodeJS
	 */
	_doWialonSocketRefresh: function() {
		//log('_doWialonSocketRefresh');
		
		this.socket.emit('getAllWialonCars', function(data){

			data = Ext.JSON.decode(data,true);

			if (!data || !data.items || !data.items.length) {
				log({ERROR_startWialonSocketAutoRefresh_data: data});
				return false;
			}

			var result = [];
			var items = data.items;
			for (var i=0 ; i<items.length ; i++) {
				result.push({
					'GeoserviceTransport_name' : items[i]['nm'] || '',
					'GeoserviceTransport_id' : items[i]['id'] || null,
					'lat' : items[i]['pos'] && items[i]['pos']['y'] || '',
					'lng' : items[i]['pos'] && items[i]['pos']['x'] || '',
					'direction' : items[i]['pos'] && items[i]['pos']['c'] || '',
					'groups' : items[i]['ugs'] || []
				})
			}
			
			
			this.loadData(result);
			// log({result:result});
			this.fireEvent('load', this, this.getRange() , true)
			
		}.bind(this));
		
	},
	/**
	 * @private Метод обновления данных ТНЦ через сокет NodeJS
	 */
	_doTNCSocketRefresh: function() {
		// log('_doTNCSocketRefresh');
		
		
		this.socket.emit('getTNCTransport', function(data){
			//log({_doTNCSocketRefresh_data:data})
			this.loadData( data );
			this.fireEvent('load', this, this.getRange() , true)
		}.bind(this))
	},
	/**
	 * @private Метод запуска автоматической загрузки данных через сокет NodeJS
	 * для геосервиса Wialon
	 */
	_startWialonSocketAutoRefresh: function() {
		// log('_startWialonSocketAutoRefresh');
		
		//@TODO Прописать интервал на стороне сервера NodeJS для уменьшения количества запросов
		this._wialonSocketAutoRefreshInterval = setInterval(function(){
			this._doWialonSocketRefresh();
		}.bind(this),this._autoRefreshDelay)
	},
	/**
	 * @private Метод остановки автоматической загрузки данных через сокет NodeJS
	 * для геосервиса Wialon
	 */
	_stopWialonSocketAutoRefresh: function() {
		// log('_stopWialonSocketAutoRefresh');
		clearInterval(this._wialonSocketAutoRefreshInterval);
	},
	/**
	 * @private Метод запуска автоматической загрузки данных через сокет NodeJS
	 * для геосервиса ТНЦ
	 */
	_startTNCSocketAutoRefresh: function() {
		// log('_startTNCSocketAutoRefresh');
		
		if (this.socket.hasListeners('TNCTransportUpdata')) {
			return;
		}
		this._doTNCSocketRefresh();
		this.socket.on('TNCTransportUpdata',function(data){
			this.loadData( data );
			
			this.fireEvent('load', this, this.getRange() , true)
		}.bind(this));
		
	},
	/**
	 * @private Метод остановки автоматической загрузки данных через сокет NodeJS
	 * для геосервиса ТНЦ
	 */
	_stopTNCSocketAutoRefresh: function() {
		this.socket.removeListener('TNCTransportUpdata')
	}

});
