/*
 * Ext JS Library 0.30
 * Copyright(c) 2006-2009, Ext JS, LLC.
 * licensing@extjs.com
 *
 * http://extjs.com/license
 */

/**
 * @class Ext6.rdb.Store
 * @extends Ext6.data.JsonStore
 * Convenience class which assists in setting up Store's.
 * This class will create the necessary table if it does not exist.
 * This class requires that all fields stored in the database will also be kept
 * in the Ext6.data.Store.
 */
Ext6.rdb.Store = Ext6.extend(Ext6.data.JsonStore, {
	/**
	 * @cfg {String} key This is the primary key for the table and the id for the Ext6.data.Record.
	 */
	/**
	 * @cfg {Array} fields Array of fields to be used. Both name and type must be specified for every field.
	 */
	/**
	 * @cfg {String} tableName  Name of the database table
	 */
	constructor: function(config) {
		config = config || {};
		config.reader = new Ext6.data.JsonReader({
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
		
		var conn = Ext6.db.remoteDBDriver.getInstance();
		// Подразумеваем что на момент обращения и создания Store для объектов коннекшен с базой уже установлен
		/*if (!conn.isOpen()) {
			log('Удаленная база данных не доступна!');
		}*/
		// Вместо проверки на установленный коннекшен (при инициализации store это совсем не обязательно) будем проверять наличие autoLoad: true
		if (config.autoLoad == true && isDebug()) {
			console.warn('Справочник '+config.tableName+' имеет атрибут autoLoad: true');
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
			if (isDebug()) console.warn('Справочник думает, что надо брать данные из БД (remote):',config.tableName, config);
			config.mode = 'remote'; // Если урл для получения данных определен, то это явно справочник, который всегда грузится с сервера
		}
		Ext6.rdb.Store.superclass.constructor.call(this, config);
		this.proxy = config.proxy || (!config.data ? new Ext6.data.HttpProxy({url: config.url}) : undefined);
		//this.proxy = new Ext6.rdb.Proxy(conn, config.tableName, config.key, this, false);
	}
});
