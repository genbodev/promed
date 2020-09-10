Ext6.define('videoChat.lib.EventManager', {
	events: [],

	add: function(type, fn, scope) {
		var me = this;
		var event = me.events.find(function(event) {
			return event.type == type && event.fn == fn && event.scope == scope;
		});
		if (!event) {
			me.events.push({type: type, fn: fn, scope: scope});
		}
	},

	remove: function(type, fn, scope) {
		var me = this;
		me.events.forEach(function(event, index) {
			if (event.type == type &&
				(!fn || event.fn == fn) &&
				(!scope || event.scope == scope)
			) {
				me.events.splice(index, 1);
			}
		});
	},

	getFnList: function(type) {
		var me = this;
		return me.events.filter(function(event) {
			return event.type == type;
		}).map(function(event) {
			return event.fn;
		});
	},

	fire: function(type) {
		var me = this;
		var args = [].slice.call(arguments, 1);
		return me.events.filter(function(event) {
			return event.type == type;
		}).map(function(event) {
			event.fn.apply(event.scope, args);
		});
	}
});