/*
 * Ext JS Library 2.3 RDBDriver 
 * 
 * @author Markoff A.A. <markov@swan.perm.ru>
 *
 * http://extjs.com/license
 */


Ext6.db.remoteDBDriver = function(config){
	Ext6.apply(this, config);
	Ext6.db.remoteDBDriver.superclass.constructor.call(this);
};

Ext6.extend(Ext6.db.remoteDBDriver, Ext6.util.Observable, {
	openState : false,
	openDatabase: function() {
		// Пока заглушка, а вообще наверное надо сделать обращение к БД и проверку ее доступности
	},
	/** Open database 
	 */
	open : function() {
		this.openState = true;
		this.fireEvent('open', this);
	},
	
	close : function(){
		this.fireEvent('close', this);
	},
	
	// protected/inherited method
	isOpen : function(){
		return this.openState;
	},

	// todo: Эти два метода с версией не нужны, пока оставил на всякий случай 
	getVersion: function() {
		return 1;
	},
	setVersion : function (version, config) {
		return version;
	},

	/**
	 * Сохранение данных в псевдолокальном хранилище
	 * @param storeName - "таблица", объект данных в который будут внесены изменения
	 * @param json - набор значений для сохранения
	 * @param options
	 */
	save: function(storeName, json, options) {
		var p = json || {};
		p['object'] = storeName;
		Ext6.Ajax.request({
			url: '/?c=MongoDBWork&m=saveData',
			params: p,
			callback: function(options, success, response) {
				if (!success) {
					log('Ошибка при сохранении данных в "удаленном" хранилище!');
				}
				var r = Ext6.JSON.decode(response.responseText);
				if (!r || !r.success) {
					log('Ошибка при сохранении данных в "удаленном" хранилище!');
				}
			}
		});
	},

	// todo: Далее все остальные методы надо переписывать, если конечно они будут нужны

	// получение списка таблиц в базе 
	getStores: function(storeName, config) {
		var sql = ["SELECT name FROM sqlite_master WHERE type='table' ORDER BY name ", storeName].join('');
		this.exec(sql, config);
	},
	
	deleteDatabase: function(db) {
		//this.webSqlDB.deleteDatabase(db);
	},
	
	/** Создание таблицы 
	 * @o object|string индекс или набор индексов
	 * возвращает созданный store
	 */
	createStore : function(o, config) {
		var storeName = o.name;
		var keyName = (o.key)?o.key:null;
		var json = o.fields;
		if(!Ext6.isArray(json)){ // Ext fields collection
			json = json.items;
		}
		if(!Ext6.isArray(json)){
			// для создания таблицы должен быть передан массив объектов
			// TODO: здесь надо возвращать ошибку 
			return false;
		}
		var buf = [];
		for(var i = 0, len = json.length; i < len; i++){
			var f = json[i], s = f.name;
			if (i==0 && !keyName) {
				keyName = s;
			}
			switch(f.type) {
				case "int":
				case "bool":
				case "boolean":
					s += ' INTEGER';
					break;
				case "float":
					s += ' REAL';
					break;
				default:
					s += ' TEXT';
			}
			if(f.allowNull === false || f.name == keyName){
				s += ' NOT NULL';
			}
			if(f.name == keyName){
				s += ' PRIMARY KEY';
			}
			if(f.unique === true){
				s += ' UNIQUE';
			}
			buf[buf.length] = s;
		}
		var sql = ['CREATE TABLE IF NOT EXISTS ', storeName, ' (', buf.join(','), ')'].join('');
		this.exec(sql, config);
	},
	/** Удаление таблицы 
	 * @storeName string Название таблицы
	 * Ничего не возвращает, потому что не умеет. 
	 */
	deleteStore : function(storeName, config) {
		var sql = ['DROP TABLE IF EXISTS ', storeName].join('');
		this.exec(sql, config);
	},
	/**
	 * This is the main method. 
	 * 
	 */
	exec : function(sql, config, arg) {
		this.conn.transaction(function(tx) {
			//swalert(arg);
			// TODO: Сделать передачу агрументов 
			tx.executeSql(sql, [], function(tx, result) {
				if (config && typeof config.success=='function')
					config.success(tx, result);
			}, 
			function(tx, error) {
				if (config && typeof config.failure=='function')
					config.failure(tx, error);
			});
		});
	},


	// Writes the json to the storeName in db.
	// options are just success and error callbacks.
	// Добавление данных 
	// TODO: По идее для сохранения тоже можно заюзать параметры для сохранения (параметризированные запросы )
	add: function(storeName, json, options) {
		function getFields(json){
			var fields = [];
			for (var key in json) {
				fields.push(key);
			}
			return fields;
		}
		function getValues(json){
			var values = [];
			for (var key in json) {
				// TODO: json[key] должен быть строкой обязательно
				values.push("'"+json[key].replace(/'/g,"''")+"'"); // такое вот экранирование 
			}
			return values;
		}
		function addData(ct, sql_first, k, config){
			var sql = '';
			var row = [];
			for ( var i = k; i < json.length; i++) {
				row.push(['',getValues(json[i]),''].join(''));
				// TODO: Функция count в swFunctions - наверное нужно от нее избавиться
				if (count(row)>=300 || ((i+1) == json.length)) {
					break;
				}
			}
			sql = [sql, '', row.join(' UNION SELECT '), ''].join('');
			sql = [sql_first, sql].join('');
			ct.exec(sql, {
				success: function(tx,r) {
					if (i+1 < json.length) {
						addData(ct, sql_first, i+1, config);
					} else {
						if (config && typeof config.success=='function')
							config.success(tx, r);
					} 
				},
				failure: function(tx,e) {
					if (config && typeof config.failure=='function')
						config.failure(tx, e);
				}
			});
		}
		
		if ((typeof json == 'object') && (json.length>=0)) {
			if (json.length==0) {
				// Попытку вставить пустой массив будем игнорировать
				if (options && typeof options.success=='function')
					options.success(); 
				return true;
			}
			
			// блоками по нескольку записей 
			var sql_first = ['INSERT INTO ', storeName, ' (', getFields(json[0]).join(','), ') SELECT '].join('');
			addData(this, sql_first, 0, options);
		} else {
			var sql = ['INSERT INTO ', storeName, ' (', getFields(json).join(','), ') VALUES ( ', getValues(json).join(','), ')'].join('');
			//log(sql);
			this.exec(sql, options);
		}
	},

	// Reads from storeName in db with json.id if it's there of with any json.xxxx as long as xxx is an index in storeName 
	// Чтение данных
	// TODO: Сделать обработку json (fields:, where: order by: group by: )
	load: function(storeName, options, clause, args) {
		var sql = ['SELECT * FROM ', storeName].join('');
		if(clause){
			sql += ' ' + clause;
		}
		//log(sql);
		this.exec(sql, options, args);
	},

	update : function(storeName, json, options, clause, args) {
		var sql = "UPDATE " + storeName + " set ";
		var fs = [], args = [];
		for(var key in json){
			if(json.hasOwnProperty(key)){
				fs[fs.length] = key + ' = ?';
				//a[a.length] = o[key];
				args.push(o[key]);
			}
		}
		// это аргументы для where 
		for(var key in args){
			if(args.hasOwnProperty(key)){
				//a[a.length] = args[key];
				args.push(args[key]);
			}
		}
		sql = [sql, fs.join(','), ' WHERE ', clause].join('');
		this.exec(sql, args, options);
	},

	// Удаление записи
	// Примеры использования: remove('Table', [{Table_id:1}, {Table_id:2}])
	// TODO: Здесь по идее тоже можно переделать под параметризированные запросы 
	remove: function(storeName, json, options) {
		function getWheres(json) {
			var wheres = [];
			for (var key in json) {
				wheres.push([key,"='",json[key],"'"].join(''));
			}
			return wheres;
		}
		var sql = '';
		if ((typeof json == 'object') && (json.length>=0)) {
			var sql = ['DELETE FROM ', storeName].join('');
			if (json.length>0) {
				
				var row = [];
				for ( var i = 0; i < json.length; i++) {
					row.push(['(', getWheres(json[i]).join(' AND '), ')'].join(''));
				}
				sql = [sql, ' WHERE ', row.join(' OR '), ''].join('');
				//log(sql);
				this.exec(sql, options);
			}
		} else {
			var sql = ['DELETE FROM ', storeName, ' WHERE (', getWheres(json).join(' AND '), ')'].join('');
			//log(sql);
			this.exec(sql, options);
		}
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
	},
	queryBy : function(sql, args){
	}
});

Ext6.db.remoteDBDriver.getInstance = function(db, config){
	if(Ext6.isRemoteDB){ // remoteDB
		if (!Ext6.rdb.RDBDriver) {
			Ext6.rdb.RDBDriver = new Ext6.db.remoteDBDriver(config);
		} 
		return Ext6.rdb.RDBDriver;
	}
};