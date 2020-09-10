Ext6.define('videoChat.lib.ContactManager', {
	requires: [
		'videoChat.store.Contact'
	],

	constructor: function(engine) {
		var me = this;

		me.engine = engine;
		me.storeList = [];
		me.globalStore = Ext6.create('videoChat.store.Contact');
	},

	loadRecord: function(id, callback) {
		var me = this;
		var params = {pmUser_oid: id};
		callback = callback || Ext6.emptyFn;

		var record = me.getRecord(id, true);

		if (record) {
			callback(record);
			return;
		}

		me.globalStore.fetch({
			params: params,
			callback: function(records) {
				me.globalStore.add(records);
				callback(records[0]);
			}
		});
	},

	getRecord: function(id, storeOnly) {
		var me = this;
		var record = me.globalStore.getById(id);

		if (storeOnly) {
			return record;
		}

		return new Promise(function(resolve, reject) {
			if (record) {
				resolve(record);
			} else {
				me.loadRecord(id, function(record) {
					resolve(record);
				});
			}
		});
	},

	createStore: function(config) {
		var me = this;
		var store = Ext6.create('videoChat.store.Contact', config);

		me.storeList.push(store);

		store.on('load', function(store, records) {
			store.managing = true;

			var observableRecords = me.getObservable(records);
			var updateFields = ['SurName', 'FirName', 'SecName', 'Avatar', 'LpuList'];

			observableRecords.forEach(function(record) {
				var globalRecord = me.getRecord(record.get('id'), true);
				if (globalRecord) {
					updateFields.forEach(function(field) {
						globalRecord.set(field, record.get(field));
					});

					store.remove(record);
					store.add(globalRecord);
				} else {
					me.globalStore.add(record);
				}
			});

			store.managing = false;

			me.observe();
		});

		return store;
	},

	getObservable: function(records) {
		var me = this;
		if (!records) {
			records = me.globalStore.data.items;
		}
		return records.filter(function(record) {
			return record.get('Status') != 'add';
		});
	},

	observe: function(records) {
		var me = this;

		if (!Ext6.isArray(records)) {
			records = me.getObservable();
		}

		var userList = records.map(function(record) {
			return record.get('id');
		});

		me.engine.addEvent('observeUsers', me.onObserve, me);
		me.engine.observeUsers(userList);
	},

	stopObserve: function() {
		var me = this;
		me.engine.removeEvent('observeUsers', me.onObserve);
		me.engine.clearObserveUsers();
	},

	onObserve: function(type, data) {
		var me = this;
		var records = me.getObservable();

		var onResponse = function(data) {
			var record = records.find(function(record) {
				return record.get('pmUser_id') == data.pmUser_id;
			});
			if (record) {
				record.set('Status', data.status);
				record.set('hasCamera', data.hasCamera);
				record.set('hasMicro', data.hasMicro);
			}
		};

		if (type == 'change' && Ext6.isObject(data)) {
			onResponse(data);
		}
		if (type == 'request' && Ext6.isArray(data)) {
			data.forEach(onResponse);
		}
	}
});