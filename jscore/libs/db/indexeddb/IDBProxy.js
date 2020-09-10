/*
 * Ext JS Library 0.30
 * Copyright(c) 2006-2009, Ext JS, LLC.
 * licensing@extjs.com
 * 
 * http://extjs.com/license
 */

/**
 * @class Ext.idb.Proxy
 * @extends Ext.data.DataProxy
 * An implementation of {@link Ext.data.DataProxy} that reads from a SQLLite
 * database.
 *
 * @constructor
 * @param {Object} conn an {@link Ext.sql.Connection} object
 * @param {String} table The name of the database table
 * @param {String} keyName The primary key of the table
 * @param {Ext.data.Store} store The datastore to bind to
 * @param {Boolean} readonly By default all changes to the store will be persisted
 * to the database. Set this to true to override to make the store readonly.
 */
Ext.idb.Proxy = function(conn, table, keyName, store, readonly){
	Ext.idb.Proxy.superclass.constructor.call(this);
	this.conn = conn;
	this.table = table;
	// keyName;
	this.store = store;

	if (readonly !== true) {
		this.store.on('add', this.onAdd, this);
		this.store.on('update', this.onUpdate, this);
		this.store.on('remove', this.onRemove, this);
	}
};

Ext.idb.Proxy.DATE_FORMAT = 'Y-m-d H:i:s';

Ext.extend(Ext.idb.Proxy, Ext.data.DataProxy, {
	load : function(params, reader, callback, scope, arg){
		if(!this.conn.isOpen()){ // assume that the connection is in the process of opening
			this.conn.on('open', function(){
				this.load(params, reader, callback, scope, arg);
			}, this, {single:true});
			return;
		};
		if(this.fireEvent("beforeload", this, params, reader, callback, scope, arg) !== false) {
			var where = params.where || '';
			var args = params.args || [];
			var group = params.groupBy; // с групбай тоже непонятно что делать 
			var sort = params.sort; // не используем 
			var dir = params.dir; // не используем 
			var clause = params.clause || null;
			
			// Здесь конвертер
			// Фильтрация и сортировка которой сейчас нет
			// А сортировка то походу работает в самом сторе ExtJS ) так что похер ) или хз где , но оно сортируется 
			
			var r = {};
			//var clause = "where DiagLevel_id = 4 and Diag_Code like 'A%' limit 100";
			if (!clause) {
				if  (where.length>0) {
					var s = SqlToArray(where);
					r['clause'] = getClause(s['params']);
					if (this.table=='Diag') {
						r['limit'] = getSpecificLimit(this.table, s['params'], s['limit']);
					} else {
						r['limit'] = s['limit'];
					}
					//console.warn(this.table);
					//console.info('Result SQL: ', r);
				} else {
					r = {clause: '', limit: null};
				}
			} else {
				/*
				if (this.table=='Diag') {
					clause.limit = 1;
				}*/
				r = {clause: clause.where || '', limit: clause.limit || null};
				//console.info('Result IDB: ', r);
			}
			//this.conn.load();
			this.conn.load(this.table,r, {
				success:function(rs) {
					this.onLoad({callback:callback, scope:scope, arg:arg, reader: reader}, rs);
				}.createDelegate(this),
				failure:function(e) {throw e;}
			});
		
		//this.onLoad({callback:callback, scope:scope, arg:arg, reader: reader}, rs);
		}else{
			callback.call(scope||this, null, arg, false);
		}
	},

	onLoad : function(trans, rs, e, stmt){
			if(rs === false){
			this.fireEvent("loadexception", this, null, trans.arg, e);
					trans.callback.call(trans.scope||window, null, trans.arg, false);
					return;
		}
		
		var result = trans.reader.readRecords(rs);
		this.fireEvent("load", this, rs, trans.arg);
		trans.callback.call(trans.scope||window, result, trans.arg, true);
	},

	processData : function(o){
		var fs = this.store.fields;
		var r = {};
		for(var key in o){
			var f = fs.key(key), v = o[key];
		if(f){
			if(f.type == 'date'){
				r[key] = v ? v.format(Ext.idb.Proxy.DATE_FORMAT,10) : '';
			}else if(f.type == 'boolean'){
				r[key] = v ? 1 : 0;
			}else{
				r[key] = v;
			}
			}
		}
	return r;
	},

	onUpdate : function(ds, record){
		var changes = record.getChanges();
		//var kn = this.table.keyName;
		// Не понятно надо ли обрабатывать данные перед сохранением processData
		this.conn.add(this.table, this.processData(changes));
	},

	onAdd : function(ds, records, index){
		
		// Не понятно надо ли обрабатывать данные перед сохранением processData
		// this.conn.add(this.table, records);
		
		for(var i = 0, len = records.length; i < len; i++){
				this.conn.add(this.table, this.processData(records[i].data));
		}
	},

	onRemove : function(ds, record, index){
		this.conn.remove(this.table, record, this.keyName, {
			success: function(e) {},
			failure: function(e) {}
		});
	}
});