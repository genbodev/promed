/*
 * Ext JS Library 0.30
 * Copyright(c) 2006-2009, Ext JS, LLC.
 * licensing@extjs.com
 *
 * http://extjs.com/license
 */

/**
 * @class Ext.idb.IDBStore
 * @extends Ext.data.Store
 * Convenience class which assists in setting up IDBStore's.
 * This class will create the necessary table if it does not exist.
 * This class requires that all fields stored in the database will also be kept
 * in the Ext.data.Store.
 */
Ext.idb.IDBStore = Ext.extend(Ext.data.Store, {
	/**
	 * @cfg {String} key This is the primary key for the table and the id for the Ext.data.Record.
	 */
	/**
	 * @cfg {Array} fields Array of fields to be used. Both name and type must be specified for every field.
	 */
	/**
	 * @cfg {String} dbFile Filename to create/open
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
		var conn = Ext.db.indexedDBDriver.getInstance();

		// Условие ниже не очень правильно, вообще коннекшен должен уже быть создан и если это не так, надо выдавать ошибку....
		// Либо в success open делать вызов конструктора далее? - это вообще дурдом 

		if (!conn.isOpen()) {
			//conn.open('Promed');
			console.log('Локальная база IndexedDB не доступна!');
		}
		
//		if (Ext.db.indexedDBDriver.getInstance()) {
//			console.log('Локальная база IndexedDB уже доступна!');
//		};
			
		// Определяем локальность хранилища
		if (!config.url) {
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

		Ext.idb.IDBStore.superclass.constructor.call(this, config);
		
		this.proxy = new Ext.idb.Proxy(conn, config.tableName, config.key, this, false);
	}
	/*
	,
	
	load: function() {
		// Здесь может быть обработка тех фильтров что приходит 
	}
	*/
});
