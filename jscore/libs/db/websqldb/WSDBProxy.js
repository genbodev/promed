/*
 * Ext JS Library 0.30
 * Copyright(c) 2006-2009, Ext JS, LLC.
 * licensing@extjs.com
 * 
 * http://extjs.com/license
 */

/**
 * @class Ext.wsdb.Proxy
 * @extends Ext.data.DataProxy
 * An implementation of {@link Ext.data.DataProxy} that reads from a SQLLite
 * database.
 *
 * @constructor
 * @param {Object} conn an {@link Ext.wsdb.Connection} object
 * @param {String} table The name of the database table
 * @param {String} keyName The primary key of the table
 * @param {Ext.data.Store} store The datastore to bind to
 * @param {Boolean} readonly By default all changes to the store will be persisted
 * to the database. Set this to true to override to make the store readonly.
 */
Ext.wsdb.Proxy = function(conn, table, keyName, store, readonly){
	Ext.wsdb.Proxy.superclass.constructor.call(this);
	this.conn = conn;
	this.table = table; //this.conn.getTable(table, keyName);
	this.keyName = keyName;
	this.store = store;

	if (readonly !== true) {
		this.store.on('add', this.onAdd, this);
		this.store.on('update', this.onUpdate, this);
		this.store.on('remove', this.onRemove, this);
	}
};

Ext.wsdb.Proxy.DATE_FORMAT = 'Y-m-d H:i:s';

Ext.extend(Ext.wsdb.Proxy, Ext.data.DataProxy, {
	load : function(params, reader, callback, scope, arg){
		if(!this.conn.isOpen()){ // assume that the connection is in the process of opening
			this.conn.on('open', function(){
				this.load(params, reader, callback, scope, arg);
			}, this, {single:true});
			return;
		};
		if(this.fireEvent("beforeload", this, params, reader, callback, scope, arg) !== false) {
			var clause = params.where || '';
			var args = params.args || [];
			var group = params.groupBy;
			var sort = params.sort;
			var dir = params.dir;

			if(group || sort){
				clause += ' ORDER BY ';
				if(group && group != sort){
					clause += group + ' ASC, ';
				}
				clause += sort + ' ' + (dir || 'ASC');
			}

			//var rs = this.table.selectBy(clause, args);
			var t = this.table;
			this.conn.load(this.table, {
				success:function(tx, rs) {
					var r=[];
					// TODO: Возможно для Оперы так будет быстрее, Safari не содержит массив в rows
					// if (Ext.isOpera) {
					// 	r = rs.rows;
					// }
					
					for (var i = 0; i < rs.rows.length; i++) {
						r.push(rs.rows.item(i));
					} 
					this.onLoad({callback:callback, scope:scope, arg:arg, reader: reader}, r);
				}.createDelegate(this),
				failure:function(tx, e) {
					//swalert(e);
					// TODO: Надо здесь все же сообщение об ошибке
					//throw 'Not found table '+name+' in local database!';
				}.createDelegate(this)
			}, clause, arg
			);
		} else {
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

	processData : function(o) {
		var fs = this.store.fields;
		var r = {};
		for(var key in o){
			var f = fs.key(key), v = o[key];
		if(f){
			if(f.type == 'date'){
				r[key] = v ? v.format(Ext.wsdb.Proxy.DATE_FORMAT,10) : '';
			}else if(f.type == 'boolean'){
				r[key] = v ? 1 : 0;
			}else{
				r[key] = v;
			}
		}
	}
	return r;
	},
	
	onUpdate : function(ds, record) {
		var changes = record.getChanges();
		var kn = this.keyName;
		this.conn.update(this.table, this.processData(changes), {}, kn + ' = ?', [record.data[kn]]);
		record.commit(true);
	},
	
	onAdd : function(ds, records, index) {
		for(var i = 0, len = records.length; i < len; i++) {
			this.conn.add(this.table, this.processData(records[i].data));
		}
	},

	onRemove : function(ds, record, index) {
		var kn = this.keyName;
		//this.conn.remove(this.table, kn + ' = ?', [record.data[kn]]);
		this.conn.remove(this.table, {kn:record.data[kn]});
	}
});