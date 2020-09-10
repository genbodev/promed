/*
 * Ext JS Library 0.30
 * Copyright(c) 2006-2009, Ext JS, LLC.
 * licensing@extjs.com
 *
 * http://extjs.com/license
 */

/**
 * @class Ext.sql.SQLiteStore
 * @extends Ext.data.Store
 * Convenience class which assists in setting up SQLiteStore's.
 * This class will create the necessary table if it does not exist.
 * This class requires that all fields stored in the database will also be kept
 * in the Ext.data.Store.
 */
Ext.sql.SQLiteStore = Ext.extend(Ext.data.Store, {
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
        var conn = Ext.sql.Connection.getInstance();
        conn.open(config.dbFile);
        // Create the database table if it does
        // not exist
        conn.createTable({
            name: config.tableName,
            key: config.key,
            fields: config.reader.recordType.prototype.fields
        });
		
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
		
        Ext.sql.SQLiteStore.superclass.constructor.call(this, config);
        this.proxy = new Ext.sql.Proxy(conn, config.tableName, config.key, this, false);
    }
});
