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
Ext.rdb.Store = Ext.extend(Ext.data.Store, {
//Ext.define('Ext.rdb.Store',{
//	extend:'Ext.data.Store',
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
		
		console.log(config);
		
		config = config || {};
		//ExtJS 2.3 version
//		config.reader = new Ext.data.JsonReader({
//			id: config.key,
//			fields: config.fields
//		});
		//ExtJS 4.2.2 version
		config.idProperty = config.key;
		config.fields = config.fields;
		if (!config.tableName) {
			// Определяем tableName по другим косвенным признакам
			log(this);
		}
		if (!config.tableName) {
			console.warn('Источник данных справочника не определен:', this);
		}
		/*
		 * 
		 * this.store = Ext.create('Ext.data.Store',{
			autoLoad: this.autoLoad,
			idProperty: 'EvnClass_id',
			fields: [
				{name: 'EvnClass_id', mapping: 'EvnClass_id'},
				{name: 'EvnClass_Name', mapping: 'EvnClass_Name'},
				{name: 'EvnClass_SysNick', mapping: 'EvnClass_SysNick'}
			],
			proxy: {
				url: '/?c=XmlTemplate&m=loadEvnClassList',
				type: 'ajax'
			}
		});
		 * 
		 * 
		 */
		// получить поля из сторе 
		var params = {};
		var fields = spr_structure[config.tableName] || config.fields; // определяем список полей по структуре, если структура не определена - берем из конфига 
		for (key in fields) {
			if (fields[key].name) {
				params[fields[key].name] = '';
			}
		}
		params['object'] = config.tableName;
		config.extraParams = params;
		console.log(params);
		
		var conn = Ext.db.remoteDBDriver.getInstance();
		// Подразумеваем что на момент обращения и создания Store для объектов коннекшен с базой уже установлен
		if (!conn.isOpen()) {
			//log('Удаленная база данных не доступна!');
		}
		// Определяем локальность хранилища
		
		if (!config.url) {
			config.proxy = {
				url: '/?c=MongoDBWork&m=getData',
				type: 'ajax'
			};
			config.url = '/?c=MongoDBWork&m=getData';
			config.mode = 'local';  // Если урла нет, то это "локальный" справочник
			// Но для некоторых локальных справочников правила не действуют... то есть загружаться они будут не по автоматически при запросе, а по вводу в поле
			// или по кокретному лоаду
			if (config.tableName.inlist(getDataWait())) {
				config.mode = 'wait';  // Так помечаем те, которые ждут ввода
			}
			
		} else {
			if (isDebug()) console.warn('Справочник думает, надо брать данные из БД (remote):',config.tableName, config);
			config.mode = 'remote'; // Если урл для получения данных определен, то это явно справочник, который всегда грузится с сервера
		}
//		this.callParent(config)
		Ext.rdb.Store.superclass.constructor.call(this, config);
		//this.proxy = config.proxy || (!config.data ? new Ext.data.HttpProxy({url: config.url}) : undefined);
		//this.proxy = new Ext.rdb.Proxy(conn, config.tableName, config.key, this, false);
	}
});
