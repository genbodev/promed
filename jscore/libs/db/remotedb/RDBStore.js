/*
 * Ext JS Library 0.30
 * Copyright(c) 2006-2009, Ext JS, LLC.
 * licensing@extjs.com
 *
 * http://extjs.com/license
 */

/**
 * @class Ext.rdb.Store
 * @extends Ext.data.JsonStore
 * Convenience class which assists in setting up Store's.
 * This class will create the necessary table if it does not exist.
 * This class requires that all fields stored in the database will also be kept
 * in the Ext.data.Store.
 */
Ext.rdb.Store = Ext.extend(Ext.data.JsonStore, {
	/**
	 * @cfg {String} key This is the primary key for the table and the id for the Ext.data.Record.
	 */
	/**
	 * @cfg {Array} fields Array of fields to be used. Both name and type must be specified for every field.
	 */
	/**
	 * @cfg {String} tableName  Name of the database table
	 */
	constructor: function(config) {
		config = config || {};
		config.reader = new Ext.data.JsonReader({
			id: config.key,
			fields: config.fields
		});
		if (!config.tableName) {
			// Определяем tableName по другим косвенным признакам
			log(this);
		}
		if (!config.tableName) {
			console.warn('Источник данных справочника не определен:', this);
		}

		// получить поля из сторе 
		var params = {};
		var fields = spr_structure[config.tableName] || config.fields; // определяем список полей по структуре, если структура не определена - берем из конфига 
		for (key in fields) {
			if (fields[key].name) {
				params[fields[key].name] = '';
			}
		}
		params['object'] = config.tableName;
		config.baseParams = params;
		//log(params);

		var conn = Ext.db.remoteDBDriver.getInstance();
		// Вместо проверки на установленный коннекшен (при инициализации store это совсем не обязательно) будем проверять наличие autoLoad: true
		if (config.autoLoad == true && isDebug()) {
			console.warn('Справочник ' + config.tableName + ' имеет атрибут autoLoad: true');
		}
		// Определяем локальность хранилища
		if (!config.url) {
			config.url = '/?c=MongoDBWork&m=getData';
			config.mode = 'local';  // Если урла нет, то это "локальный" справочник
			// Но для некоторых локальных справочников правила не действуют... то есть загружаться они будут не по автоматически при запросе, а по вводу в поле
			// или по кокретному лоаду
			if (config.tableName.inlist(getDataWait())) {
				config.mode = 'wait';  // Так помечаем те, которые ждут ввода
			}

		} else {
			if (isDebug()) console.warn('Справочник думает, что надо брать данные из БД (remote):', config.tableName, config);
			config.mode = 'remote'; // Если урл для получения данных определен, то это явно справочник, который всегда грузится с сервера
		}

		if (config.url) {
			config.proxy = new Ext.rdb.Proxy({url: config.url});
		}

		Ext.rdb.Store.superclass.constructor.call(this, config);
	},
	load: function(options) {
		var s = this;
		options = options || {};
		if (this.fireEvent("beforeload", this, options) !== false) {
			this.storeOptions(options);
			var p = Ext.apply(options.params || {}, this.baseParams);
			if (this.sortInfo && this.remoteSort) {
				var pn = this.paramNames;
				p[pn["sort"]] = this.sortInfo.field;
				p[pn["dir"]] = this.sortInfo.direction;
			}
			if (!sw.localStorage.load(s, p, options)) {
				this.proxy.load(p, this.reader, function(o, options, success, response) {
					if (response && response.responseText) {
						try {
							var result = Ext.util.JSON.decode(response.responseText);
							sw.localStorage.save(s, p, result);
						} catch(e) {
							// не удалось
						}
					}
					this.loadRecords(o, options, success);
				}, this, options);
			}
			return true;
		} else {
			return false;
		}
	}
});
