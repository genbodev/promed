/*
 * Ext JS Library 2.3 IDBDriver 
 * 
 * @author Markoff A.A. <markov@swan.perm.ru>
 *
 * http://extjs.com/license
 */


Ext.db.indexedDBDriver = function(config){
	Ext.apply(this, config);
	Ext.db.indexedDBDriver.superclass.constructor.call(this);
	this.addEvents({
		open : true,
		close: true
	});
};

Ext.extend(Ext.db.indexedDBDriver, Ext.util.Observable, {
	openState : false,
	currentdb: null,
	/** Open database 
	 * Открывает базу данных 
	 */ 
	open : function(db, version, config, first_run) {
		if (Ext.isIndexedDb) {
			this.conn = null;
			first_run = (first_run!=undefined)?first_run:true;
			this.indexedDB = Ext.db.indexedDB;
			this.currentdb = db;
			request = this.indexedDB.open(db);
			request.onsuccess = function(e) {
				this.conn = e.target.result;
				this.openState = true;
				this.setVersion((version)?version:this.conn.version, config);
			}.createDelegate(this);
			request.onerror = function(e) {
				this.openState = false;
				log('Ошибка при открытии локального хранилища: '+e.target.errorCode);
				log(e.target);
				if (e.target.errorCode == 6 && first_run) {
					this.indexedDB.deleteDatabase(db);
					this.open(db, version, config, false);
				} else {
					// Закрыто! Крепко! Все!
					if ((typeof console==="object" ) && (typeof console.log==="function" ))
						console.warn('Error: '+e.target.errorCode, e.target);
					//alert('При работе с локальным хранилищем произошла ошибка.\n\r Для решения проблемы нужно удалить локальное хранилище вручную.')
					//throw e;
					if (config && typeof config.failure == 'function') {
						config.failure();
					}
				}
			}.createDelegate(this);
		}
		this.fireEvent('open', this);
	},
	
	close : function(){
		this.conn = null;
	},
	
	// protected/inherited method
	isOpen : function(){
		return this.openState;
	},
	getVersion: function() {
		return this.conn.version;
	},
	// проверка Firefox 10 ли?:)
	setVersionExists : function() {
		if (this.conn.setVersion) {
			return true;
		} else {
			return false;
		}
	},
	// переоткрытие бд в режиме VERSION_CHANGE 
	reopenVersioned : function(version, config) {
		this.version = parseInt((version)?version*1000:this.version);
		this.conn.close();
		request = this.indexedDB.open(this.currentdb, this.version);
		request.onsuccess = function(event) {  
			this.conn = event.target.result;
			config.success(null, true);
		}.createDelegate(this);
		request.onupgradeneeded = function(event) {  
			this.conn = event.target.result;
			config.upgrade(null, true);
		}.createDelegate(this);
		request.onerror = function(e) {
			this.openState = false;
			// Закрыто! Крепко! Все!
			throw e;
		};
	},
	setVersion : function (version, config) {
		this.version = (version)?version:this.version;
		if(this.version!= this.conn.version && this.openState) {
			if (this.setVersionExists()) {
				try {
					var requestVer = this.conn.setVersion(this.version);
					// onsuccess is the only place we can create Object Stores
					//config.success(null, false);
					requestVer.onerror = function(e) {
						// Ну может и не так грубо
						if (config && typeof config.failure == 'function') {
							config.failure(e);
						}
					}
					requestVer.onsuccess = function(e) {
						if (config && typeof config.success == 'function') {
							config.success(e, true);
						}
						var verTransaction = requestVer.result;
						verTransaction.oncomplete = function() {
							if (config && typeof config.after == 'function') {
								config.after(e, true);
							}
						}
					}
				}
				catch(e) {
					// при первом открытии в Chrome (когда хранилище еще не создано и indexedDB используется в качестве вспомогательной бд) при операции this.conn.setVersion(this.version); происходит ошибка
					// при повторном уже будет все норм
					warn('Ошибка при установке версии IndexedDB', e);
				}
			} else {
				// если метода setVersion нет, значит Firefox 10, применяем костыль..
				this.version = (version)?version*1000:this.version;
				if(this.version!= this.conn.version) {
					this.reopenVersioned(this.version/1000);
				}
			}
		}
		else {
			if (config && typeof config.success == 'function') {
				config.success(null, false);
			}
			if (config && typeof config.after == 'function') {
				config.after(null, false);
			}
		}
	},
	

	checkStoreExist: function(name){
		return this.conn.objectStoreNames.contains(name);
	},
	
	// обращение к таблице
	// TODO: Подумать над реализацией 
	
	getStore: function(name, mode){
		if(this.conn.objectStoreNames.contains(name)) {
			var modeTrans = (mode)?mode:'readonly';
			// заплатка для браузеров которые ещё поддерживают константы Ext.db.IDBTransaction['READ_WRITE'] = 1 и Ext.db.IDBTransaction['READ_ONLY'] = 0 
			// в новых версиях Chrome они значатся как deprecated. и вместо них рекомендуется использовать строки 'readonly' и 'readwrite'. 
			if (Ext.db.IDBTransaction && Ext.db.IDBTransaction['READ_WRITE']) {
				if (modeTrans == 'readwrite') {
					modeTrans = Ext.db.IDBTransaction['READ_WRITE'];
				} else {
					modeTrans = Ext.db.IDBTransaction['READ_ONLY'];
				}
			}
			var readTransaction = this.conn.transaction([name], modeTrans);
			return readTransaction.objectStore(name);
		} else {
			//log('Not found table '+name+' in local database!');
			return null;
		}
	},
	
	// получение списка таблиц в базе 
	getStores: function() {
		var result = [];
		for (var i=0; i<this.conn.objectStoreNames.length; i++){
			result.push(this.conn.objectStoreNames[i]);
		};
		return result;
	},
	
	deleteDatabase: function(db) {
		//this.indexedDB.deleteDatabase(db);
	},
	
	/** Создание таблицы 
	 * @storeName string Название таблицы
	 * @json object|string индекс или набор индексов
	 * возвращает созданный store
	 */
	createStore : function(storeName, json) {
		try {
			var store = this.conn.createObjectStore(storeName);
			if (json) {
				if (typeof json === 'object') {
					for (var key in json) {
						store.createIndex(key, json[key], { unique: false });
					}
				} else {
					if (typeof json === 'string') {
						if (json.length>0)
							store.createIndex(json, json, { unique: true });
					}
				}
			}
			return store;
		}
		catch(e) {
			//throw e;
		}
	},
	/** Удаление таблицы 
	 * @storeName string Название таблицы
	 * Ничего не возвращает, потому что не умеет. 
	 */
	deleteStore : function(storeName) {
		try {
			if(this.conn.objectStoreNames.contains(storeName)) {
				this.conn.deleteObjectStore(storeName);
			}
		}
		catch(e) {
			//throw e;
		}
	},
	/** Получение значения первого параметра в json-массиве
	 * @json object Массив в json-формете
	 * @keyName Наименование параметра, если надо получить значение именно для параметра с таким именем. Необязательный параметр.
	 * Возвращает значение параметра, если параметр существует, или null в противном случае. 
	 */
	getIndexVal: function(json, keyName) {
		// TODO: Еще например если поле содержит "_id" :) 
		for (var key in json) {
			if (keyName) {
				if (keyName==key) {
					return json[key];
				}
			} else {
				return json[key];
			}
		}
		return null;
	},
	/** Получение наименования первого параметра в json-массиве
	 * @json object Массив в json-формете
	 * Возвращает наименование первого параметра, если json-массив не пустой, или null в противном случае. 
	 */
	getIndexKey: function(json) {
		for (var key in json) {
			return key;
		}
		return null;
	},
	
	/* This is the main method. */
	exec : function(storeName, method, json, options) {
		switch(method) {
			case "create":
				this.createStore(storeName, json, options);
				break;
			case "load":
				if(json instanceof Array ) {
					this.query(storeName, options); // It's a collection
				} else {
					this.load(storeName, json, options); // It's a Model
				}
				break;
			case "update":
				this.add(storeName, json, options); // We may want to check that this is not a collection
				break;
			case "remove":
				this.remove(storeName, json, options); // We may want to check that this is not a collection
				break;
			default:
				// Hum what?
		}
	},
	/*
	execBy : function(sql, args){
	},
	*/
	// Writes the json to the storeName in db.
	// options are just success and error callbacks.
	// Добавление данных 
	add: function(storeName, json, options) {
		var store = this.getStore(storeName, 'readwrite');
		
		if ((typeof json == 'object') && (json.length>=0)) {
			if (json.length==0) {
				options.success(null); // Попытку вставить пустой массив будем игнорировать
				return true;
			}
			for ( var i = 0; i < json.length; i++) {
				var id = this.getIndexVal(json[i]);
				var request = store.put(json[i], id);
			}
		} else {
			var id = this.getIndexVal(json);
			var request = store.put(json, id);
		}
		//log(storeName, ':', count(json));
		request.onerror = function ( e ) {
			//log(storeName, ': error ', e);
			if (options && typeof options.failure=='function')
				options.failure(json, e);
		};
		request.onsuccess = function ( e ) {
			//log(storeName, ': success');
			if (options && typeof options.success=='function')
				options.success(e);
		};
	},
	// создание условия, чтобы не создавать его каждый раз
	clause: function(c) {
		var r = {};
		r['clause'] = (c['clause'])?getClause(c['clause']):'';
		r['limit'] = (c['limit'])?c['limit']:1;
		return r;
	},
	/*
	getClause: function(params) {
		var result = [];
		var r = '';
		if (params && params.length>0) {
			for (var i=0; i<params.length; i++) {
				result[i] = false;
				// Еще условие "и-или" надо обрабатывать, когда второе и третье условие берется 
				if (params[i].name && params[i].type) { // есть такое же поле
					switch ( params[i].type.toLowerCase() ) {
						case '=':
							result[i] = '(record["'+params[i].name+'"]=="'+params[i].value+'") ';
							break;
						case 'like':
							var v = params[i].value.replace(/'/ig, '');
							if (v && v.length>0) {
								if (v[0]!="%") {// первое не %
									v = '^'+v.replace(/%/ig, '');
								}
							}
							//if (data[params[i].name]==params[i].value) { // TODO: условие на вхождение надо 
							result[i] = '(record["'+params[i].name+'"].search(new RegExp("'+v+'", "i"))>=0) ';
							
							break;
						default:
							break;
					}
				}
				if (i>0 && params[i-1].next) {
					switch ( params[i-1].next.toLowerCase() ) {
						case 'or':
							r = (result[i-1]+' || '+result[i]);
							break;
						case 'and':
							r = (result[i-1]+' && '+result[i]);
							break;
						default:
							break;
					}
				}
			}
		}
		return r;
	},
	
	// Определение пересечения с данными 
	inDataList: function(data, params) {
		var result = [];
		var r = false;
		if (params && params.length>0) {
			for (var i=0; i<params.length; i++) {
				result[i] = false;
				// Еще условие "и-или" надо обрабатывать, когда второе и третье условие берется 
				if (params[i].name && params[i].type && data[params[i].name]) { // есть такое же поле
					switch ( params[i].type.toLowerCase() ) {
						case '=':
							if (data[params[i].name]==params[i].value) {
								result[i] = true;
							}
							break;
						case 'like':
							var v = params[i].value.replace(/'/ig, '');
							if (v && v.length>0) {
								if (v[0]!="%") {// первое не %
									v = '^'+v.replace(/%/ig, '');
								}
							}
							//if (data[params[i].name]==params[i].value) { // TODO: условие на вхождение надо 
							if (data[params[i].name].search(new RegExp(v, "i"))>=0) {
								result[i] = true;
							}
							break;
						default:
							break;
					}
				}
				if (i>0 && params[i-1].next) {
					switch ( params[i-1].next.toLowerCase() ) {
						case 'or':
							r = (result[i-1] || result[i]);
							break;
						case 'and':
							r = (result[i-1] && result[i]);
							break;
						default:
							break;
					}
				}
			}
		}
		return r;
	},
	*/
	funcClause: function(clause) {
		if (clause.length>0) {
			return 'function ifClause(record) {return ('+clause+');}';
		} else {
			return '';
		}
		
	},
	/*
	ifClause: function(data, clause) {
		var r = eval('('+clause+');');
		return r;
	},
	*/
	// Reads from storeName in db with json.id if it's there of with any json.xxxx as long as xxx is an index in storeName 
	// Чтение данных
	load: function(storeName, json, options) {
		var store = this.getStore(storeName);
		if (store) {
			/* // Скорость дичайше низкая, быстрее тупой перебор с проверкой
			if (storeName=='Diag') {
				var index = store.index("Diag_Code");
				var bounds = new Ext.db.IDBKeyRange.bound(
					'Y',
					'Z'
				);
				var request = index.openCursor( bounds );
			} else {
			*/
			var keyRange = Ext.db.IDBKeyRange.lowerBound(0); // Ext.db.IDBKeyRange.bound('Z','Z');
			var request = store.openCursor(keyRange); // сюда надо прикрутить лимит, то есть ищем с нуля до ста 
			
			//IDBCursorSync 
			/*
			var keyRange = Ext.db.IDBKeyRange.lowerBound(0); // Ext.db.IDBKeyRange.bound('Z','Z');
			var request = store.openCursor(keyRange); // сюда надо прикрутить лимит, то есть ищем с нуля до ста 
			*/
			//var drv = this;
			/* все варианты которые  могут быть 
			1. условия поля = значения 
			2. условие id 
			3. выбор из id (набор идешников)
			4. диапазон идешников
			4. лимит, как ограничитель количества 
			*/
			// в load кроме params может прийти уже подготовленный список.. хотя можно делать обработку на уровень выше и передавать сюда уже clause
			var result = [];
			var i=0;
			var limit = null;
			//var params = [];
			var clause = '';
			if (json) {
				limit  = (json.limit && json.limit>0)?json.limit:null; // 100 - типа по умолчанию 
				//params = (json.params && json.params.length>0)?json.params:[];
				clause = json.clause || '';
			}
			//console.timeStamp('Начало запроса: '+clause);
			//log(clause);
			//var clause  = this.getClause(params);
			eval(this.funcClause(clause));
			request.onsuccess = function(e) {
				// условия на выборку 
				if (e.target.result && (limit>i || !limit))
				{
					if (clause.length>0) {  // params.length>0 && 
						// устанавливаем параметры по которым будет проверять
						// развернуть условия если есть 
						//if (this.inDataList(e.target.result.value, params)) {
						//if (this.ifClause(e.target.result.value, clause)) {
						if (ifClause(e.target.result.value)) {
							result.push(e.target.result.value);
							i++;
						}
						//log(s);
					} else {
						result.push(e.target.result.value);
						i++;
					}
					e.target.result.continue();
				} else {
					// Абанамат!
					//console.timeStamp('Конец запроса');
					if (options && typeof options.success=='function')
						options.success(result, e);
				};
			}.createDelegate(this);
			request.onerror = function ( e ) {
				if (options && typeof options.failure=='function')
					options.failure(e);
			};
		} else {
			// Сторе не найдено
			if (options && typeof options.failure=='function') {
				options.failure('Store '+storeName+' not detected in IndexedDB!');
			}
		}
		/*
		if(json.id) {
			request = store.get(json.id);
		} else {
			// We need to find which index we have
			_.each(store.indexNames, function(key, index) {
				index = store.index(key);
				if(json[index.keyPath]) {
					request = index.get(json[index.keyPath]);
				}
			});
		}
		log(request);
		
		
		
		if(request) {
			request.onsuccess = function(event){
				if(event.target.result) {
					options.success(event.target.result);
				}
				else {
					options.error("Not Found");
				}
			};
			request.onerror = function() {
					options.error("Not Found"); // We couldn't find the record.
			}
		} else {
			
				
			options.error("Not Found"); // We couldn't even look for it, as we don't have enough data.
		}
		*/
	},

	// Удаление по ключу
	remove: function(storeName, json, keyName, options) {
		var store = this.getStore(storeName, 'readwrite');
		var id = this.getIndexVal(json, keyName);
		// TODO: вот тут даже когда уже удалена запись, FF4 все равно говорит что все хорошо (onsuccess), а Chrome работает нормально. Нужно будет проверить на более свежей версии FF
		var request = store.delete(id);
		request.onsuccess = function(e){
			if (options && typeof options.success=='function')
				options.success(e);
		};
		request.onerror = function(e){
			if (options && typeof options.failure=='function')
				options.failure(e);
		};
	},

	// Performs a query on storeName in db.
	// options may include :
	// - conditions : value of an index, or range for an index
	// - range : range for the primary key
	// - limit : max number of elements to be yielded
	// - offset : skipped items.
	// TODO : see if we could provide an options.stream where items would be yielded one by one. But that means we need to add that support into Backbone itself.
	// Самое важное - запрос 
	query: function(storeName, options) {
		/*
		var elements = [];
		var skipped = 0;
		var readCursor = null;
		var store = this.getStore(storeName);
		
		if(options.conditions) {
			// We have a condition, we need to use it for the cursor
			_.each(store.indexNames, function(key, index) {
				index = store.index(key);
				if(options.conditions[index.keyPath] instanceof Array) {
					var lower = options.conditions[index.keyPath][0] > options.conditions[index.keyPath][1] ? options.conditions[index.keyPath][1] : options.conditions[index.keyPath][0];
					var upper = options.conditions[index.keyPath][0] > options.conditions[index.keyPath][1] ? options.conditions[index.keyPath][0] : options.conditions[index.keyPath][1];
					var bounds = IDBKeyRange.bound(lower, upper);
					if(options.conditions[index.keyPath][0] > options.conditions[index.keyPath][1]) {
						// Looks like we want the DESC order
						readCursor = index.openCursor(bounds, 2);
					}
					else {
						// We want ASC order
						readCursor = index.openCursor(bounds, 0);
					}
				} else if(options.conditions[index.keyPath]) {
					readCursor = index.openCursor(IDBKeyRange.only(options.conditions[index.keyPath]));
				}
			});
		} else {
			// No conditions, use the index
			if(options.range) {
				var lower = options.range[0] > options.range[1] ? options.range[1] : options.range[0];
				var upper = options.range[0] > options.range[1] ? options.range[0] : options.range[1];
				var bounds = IDBKeyRange.bound(lower, upper);
				if(options.range[0] > options.range[1]) {
					readCursor = store.openCursor(bounds, 2);
				}
				else {
					readCursor = store.openCursor(bounds, 0);
				}
			} else {
				readCursor = store.openCursor();
			}
		}

		// Setup a handler for the cursor’s `success` event:
		readCursor.onsuccess = function ( e ) {
			var cursor = e.target.result;
			if( (cursor) && 
				(!options.limit || options.limit > elements.length)
				) {
				if(!options.offset || options.offset <= skipped ) {
					elements.push(e.target.result.value);
				} else {
					skipped ++;
				}
				cursor.continue();
			}
			else {
				options.success(elements);
			}
		};
		*/
	}
	/*
	queryBy : function(sql, args){
	},
	*/

	
});

Ext.db.indexedDBDriver.getInstance = function(db, config){
	if(Ext.isIndexedDb){ // indexedDb
		if (!Ext.idb.IDBDriver) {
			Ext.idb.IDBDriver = new Ext.db.indexedDBDriver(config);
		} 
		return Ext.idb.IDBDriver;
	} else { // gears
		return new Ext.db.GearsDriver(config);
	}
};