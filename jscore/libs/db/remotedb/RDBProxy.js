/*
 * Ext JS Library 0.30
 * Copyright(c) 2006-2009, Ext JS, LLC.
 * licensing@extjs.com
 * 
 * http://extjs.com/license
 */

/**
 * @class Ext.rdb.Proxy
 * @extends Ext.data.HttpProxy
 * An implementation of {@link Ext.data.DataProxy} that reads from a SQLLite
 * database.
 *
 * @constructor
 * @param {Object} conn an {@link Ext.rdb.Connection} object
 */
Ext.rdb.Proxy = function(conn) {
	Ext.rdb.Proxy.superclass.constructor.call(this);

	this.conn = conn;
	this.useAjax = !conn || !conn.events;
};

Ext.extend(Ext.rdb.Proxy, Ext.data.HttpProxy, {
	loadResponse : function(o, success, response){
		delete this.activeRequest;
		if(!success){
			this.fireEvent("loadexception", this, o, response);
			o.request.callback.call(o.request.scope, null, o.request.arg, false);
			return;
		}
		var result;
		try {
			result = o.reader.read(response);
		}catch(e){
			this.fireEvent("loadexception", this, o, response, e);
			o.request.callback.call(o.request.scope, null, o.request.arg, false);
			return;
		}
		this.fireEvent("load", this, o, o.request.arg);
		o.request.callback.call(o.request.scope, result, o.request.arg, true, response);
	}
});