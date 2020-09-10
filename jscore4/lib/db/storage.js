/**
* Функции для работы с локальными справочниками 
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://promedweb.ru/
*
*
* @access       public
* @author       Markoff A.A. <markov@swan.perm.ru>
* @version      ноябрь 2011
* @comment      
*
*/


/** Создание таблицы-объекта для хранения настроек на клиенте
 *
 */
Ext.db.Storage = function(config){
	Ext.apply(this, config);
	Ext.db.Storage.superclass.constructor.call(this);
	this.addEvents({
		create : true
	});
};
Ext.extend(Ext.db.Storage, Ext.util.Observable, {
	map: {
		evnstick_print_topmargin: { name: 'evnstick_print_topmargin', group: 'evnstick' },
		evnstick_print_leftmargin: { name: 'evnstick_print_leftmargin', group: 'evnstick' }
	},
	name: 'Storage',
	fields: [
		{ name: 'Storage_Name', mapping: 'Storage_Name', type: 'string' },
		{ name: 'Storage_Value', mapping: 'Storage_Value', string: 'string' }
	],
	key: 'Storage_Name',
	/** Проверка существования таблицы
	 *
	 */
	exist: function() {
		if (Ext.isGears) {
			return false; // TODO: Тут наверное надо будет сделать проверку 
		}
		if (Ext.isIndexedDb) {
			return (Ext.idb.IDBDriver.checkStoreExist(this.name));
		}
		if (Ext.isWebSqlDB) {
			return false; // TODO: Тут наверное надо будет сделать проверку 
		}
	},
	/** Создание таблицы-объекта для хранения настроек
	 *
	 */
	create: function (config) {
		if (Ext.isGears) {
			var table = new Ext.sql.SQLiteStore({
				autoLoad: true,
				dbFile: 'Promed.db',
				fields: this.fields,
				keyName: this.key,
				tableName: this.name
			});
			/*
			 var r = new Array(new Ext.data.Record(records)); //{Storage_Name: field_name, Storage_Value: field_value}
			 if (table && records) {
			 	table.add(r);
			 }
			 */
			if (config && typeof config.success=='function') {
				config.success(table);
			}
		}
		var t = this;
		if (Ext.isIndexedDb) {
			if (!t.exist()) {
				if (Ext.idb.IDBDriver.setVersionExists()) {
					Ext.idb.IDBDriver.setVersion(getGlobalOptions().localDBVersion - 0.0001, {
						success: function() {
							var store = Ext.idb.IDBDriver.createStore(t.name, (config && config.key)?config.key:null);
						},
						after: function() {
							Ext.idb.IDBDriver.setVersion(getGlobalOptions().localDBVersion, {
								after: function() {
									if (config && typeof config.success=='function') {
										config.success(store, null);
									}
								}
							});
						},
						failure: function(e) {
							if (config && typeof config.failure=='function') {
								config.failure(e);
							}
						}
					});
					// если Firefox 10
				} else {
					// новые хранилища создаются только в VERSION_CHANGE режиме бд => нужно открыть базу указав более высокую версию.
					Ext.idb.IDBDriver.reopenVersioned(parseFloat(getGlobalOptions().localDBVersion) + 0.001, {
						upgrade: function() {
							var store = Ext.idb.IDBDriver.createStore(t.name, (config && config.key)?config.key:null);
							if (config && typeof config.success=='function') {
								config.success(store, null);
							}
						},
						success: function() {
							// do nothing..
						},
						failure: function(e) {
							if (config && typeof config.failure=='function') {
								config.failure(e);
							}
						}
					});
				}
			}
		}
		if (Ext.isWebSqlDB) {
			Ext.wsdb.WSDBDriver.createStore(this,  config);
		}
		if (Ext.isRemoteDB) {
			// Не требует создания
		}
	},
	/** Удаление таблицы-объекта для хранения настроек
	 *
	 */
	drop: function (store, config) {
		Ext.idb.IDBDriver.deleteStore(store);
	},
	/** Получение значения по ключу 
	 *	Для remoteDB используется любая доступная технология хранения данных, если ничего не доступно - сохраняет в remote БД
	 */
	get: function (name, config) {
		if (Ext.isGears) {
			var sresult = sw_select_from_local_db('Promed.db', "select Storage_Value as Storage_Value from "+this.name+" where Storage_Name='"+name+"'");
			if (sresult.length>0) {
				var r = [{Storage_Name: name, Storage_Value: sresult[0]['Storage_Value']}];
			} else {
				var r = null;
			}
			if (config && typeof config.success=='function') {
				config.success(r);
			}
			/* else {
				if (config && typeof config.failure=='function') {
					config.failure(null, null);
				}
			}*/
		} else {
			if (Ext.isIndexedDb) {
				var r = Ext.idb.IDBDriver.clause({clause: [{name: 'Storage_Name', type: '=', value: name}], limit:1});
				Ext.idb.IDBDriver.load(this.name, r, config);
			} else {
				if (Ext.isWebSqlDB) {
					Ext.wsdb.WSDBDriver.load(this.name, config, " where Storage_Name='"+name+"'");
				} else {
					if (Ext.isRemoteDB) {
						// Если данные в псевдоудаленном хранилище сохранены, то они придут через getGlobalOptions
						//log('isRemoteDB get: ', [{'Storage_Value':Ext.globalOptions[Ext.db.localStorage.map[name].group][name], 'Storage_Name':name}]);
						return [{'Storage_Value':Ext.globalOptions[Ext.db.localStorage.map[name].group][name], 'Storage_Name':name}];
					}
				}
			}
		}

	}, 
	/** Сохранение значения по ключу
	 *	Для remoteDB используется любая доступная технология хранения данных, если ничего не доступно - сохраняет в remote БД
	 *
	 */
	set: function (records, config) {
		if (Ext.isGears) {
			var sresult = sw_select_from_local_db('Promed.db', "select Storage_Value as Storage_Value from "+this.name+" where Storage_Name='"+records['Storage_Name']+"'");
			//log("select Storage_Value as Storage_Value from "+this.name+" where Storage_Name='"+name+"'");
			if (sresult.length>0 && records['Storage_Name']) {
				var sresult = sw_exec_query_local_db('Promed.db', "update "+this.name+" set Storage_Value = "+records['Storage_Value']+" where Storage_Name='"+records['Storage_Name']+"'");
			} else if (records['Storage_Name']) {
				var sresult = sw_exec_query_local_db('Promed.db', "insert into "+this.name+"(Storage_Name, Storage_Value) values ('"+records['Storage_Name']+"', '"+records['Storage_Value']+"')");
				//log( "insert into "+this.name+"(Storage_Name, Storage_Value) values ('"+records['Storage_Name']+"', '"+records['Storage_Value']+"'");
			}
			//log(sresult);
			if (sresult.length>0) {
				var r = [{Storage_Name:  records['Storage_Name'], Storage_Value: sresult[0]['Storage_Value']}];
			} else {
				var r = null;
			}
			if (sresult) {
				if (config && typeof config.success=='function') {
					config.success(r);
				}
			} else {
				if (config && typeof config.failure=='function') {
					config.failure(r);
				}
			}
		} else {
			if (Ext.isIndexedDb) {
				Ext.idb.IDBDriver.add(this.name, records, config);
			} else {
				if (Ext.isWebSqlDB) {
					Ext.wsdb.WSDBDriver.save(this.name, records, config, "Storage_Name='"+records['Storage_Name']+"'");
				} else {
					if (Ext.isRemoteDB) {
						// Подменяем данные на изменившиеся
						Ext.globalOptions[Ext.db.localStorage.map[records['Storage_Name']].group][records['Storage_Name']] = records['Storage_Value'];
						// Сохранение данных в БД на вебсервер
						records['where'] =  "where Storage_Name='"+records['Storage_Name']+"'";
						Ext.rdb.RDBDriver.save(this.name, records, config);
						//log('isRemoteDB set: ', records, config);
					}
				}
			}
		}
	}
});
/** Локальные настройки 
 *
 */
Ext.setLocalOptions = function() {
	Ext.db.localStorage = new Ext.db.Storage();
	Ext.db.localStorage.create();
	for (var key in Ext.db.localStorage.map) {
	//for (i = 0; i < Ext.db.localStorage.map.length; i++) 
		Ext.db.localStorage.get(Ext.db.localStorage.map[key].name, {
			success: function(tx, r) {
				var result = null;
				var name = '';
				if (Ext.isGears) {
					if (Ext.isArray(tx)) {
						if (tx.length>0) {
							result = tx[0]['Storage_Value'];
							name = tx[0]['Storage_Name'];
						}
					}
				}
				if (Ext.isIndexedDb) {
					if (Ext.isArray(tx)) {
						if (tx.length>0) {
							result = tx[0]['Storage_Value'];
							name = tx[0]['Storage_Name'];
						}
					}
				}
				if (Ext.isWebSqlDB) {
					for (var i = 0; i < r.rows.length; i++) {
						result = r.rows.item(i)['Storage_Value'];
						name = r.rows.item(i)['Storage_Name'];
					}
				}
				if (Ext.isRemoteDB) {
					if (Ext.isArray(tx)) {
						if (tx.length>0) {
							result = tx[0]['Storage_Value'];
							name = tx[0]['Storage_Name'];
						}
					}
				}
				if (result) {
					Ext.globalOptions[Ext.db.localStorage.map[name].group][name] = result;
				} else {
					// Нет данных, и поэтому скорее всего (вероятность 99%) это первый запуск под этим браузером с использованием настроек.
					// Тут по задаче #6457 надо сохранять глобальные переменные в локальное хранилище... сохраняем 
					// Ext.db.localStorage.set({Storage_Name: name, Storage_Value: Ext.globalOptions[Ext.db.localStorage.map[name].group][name]});
					if (Ext.globalOptions[Ext.db.localStorage.map['evnstick_print_topmargin'].group]['evnstick_print_topmargin']) {
						Ext.db.localStorage.set({Storage_Name: 'evnstick_print_topmargin', Storage_Value: Ext.globalOptions[Ext.db.localStorage.map['evnstick_print_topmargin'].group]['evnstick_print_topmargin']});
					}
					if (Ext.globalOptions[Ext.db.localStorage.map['evnstick_print_leftmargin'].group]['evnstick_print_leftmargin']) {
						Ext.db.localStorage.set({Storage_Name: 'evnstick_print_leftmargin', Storage_Value: Ext.globalOptions[Ext.db.localStorage.map['evnstick_print_leftmargin'].group]['evnstick_print_leftmargin']});
					}
				}
			}, 
			failure: function(tx, e) {
				// TODO: Возможно надо что-то сообщать
			}
		});
	}
}