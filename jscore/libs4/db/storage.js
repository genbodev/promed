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
Ext6.db.Storage = function(config){
	Ext6.apply(this, config);
	Ext6.db.Storage.superclass.constructor.call(this);
};
Ext6.extend(Ext6.db.Storage, Ext6.util.Observable, {
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
	},
	/** Создание таблицы-объекта для хранения настроек
	 *
	 */
	create: function (config) {
		if (Ext6.isRemoteDB) {
			// Не требует создания
		}
	},
	/** Удаление таблицы-объекта для хранения настроек
	 *
	 */
	drop: function (store, config) {
		Ext6.idb.IDBDriver.deleteStore(store);
	},
	/** Получение значения по ключу 
	 *	Для remoteDB используется любая доступная технология хранения данных, если ничего не доступно - сохраняет в remote БД
	 */
	get: function (name, config) {
		return [{'Storage_Value':Ext6.globalOptions[Ext6.db.localStorage.map[name].group][name], 'Storage_Name':name}];
	},
	/** Сохранение значения по ключу
	 *	Для remoteDB используется любая доступная технология хранения данных, если ничего не доступно - сохраняет в remote БД
	 *
	 */
	set: function (records, config) {
		// Подменяем данные на изменившиеся
		Ext6.globalOptions[Ext6.db.localStorage.map[records['Storage_Name']].group][records['Storage_Name']] = records['Storage_Value'];
		// Сохранение данных в БД на вебсервер
		records['where'] =  "where Storage_Name='"+records['Storage_Name']+"'";
		Ext6.rdb.RDBDriver.save(this.name, records, config);
	}
});
/** Локальные настройки 
 *
 */
Ext6.setLocalOptions = function() {
	Ext6.db.localStorage = new Ext6.db.Storage();
	Ext6.db.localStorage.create();
	for (var key in Ext6.db.localStorage.map) {
	//for (i = 0; i < Ext6.db.localStorage.map.length; i++)
		Ext6.db.localStorage.get(Ext6.db.localStorage.map[key].name, {
			success: function(tx, r) {
				var result = null;
				var name = '';
				if (Ext6.isRemoteDB) {
					if (Ext6.isArray(tx)) {
						if (tx.length>0) {
							result = tx[0]['Storage_Value'];
							name = tx[0]['Storage_Name'];
						}
					}
				}
				if (result) {
					Ext6.globalOptions[Ext6.db.localStorage.map[name].group][name] = result;
				} else {
					// Нет данных, и поэтому скорее всего (вероятность 99%) это первый запуск под этим браузером с использованием настроек.
					// Тут по задаче #6457 надо сохранять глобальные переменные в локальное хранилище... сохраняем 
					// Ext6.db.localStorage.set({Storage_Name: name, Storage_Value: Ext6.globalOptions[Ext6.db.localStorage.map[name].group][name]});
					if (Ext6.globalOptions[Ext6.db.localStorage.map['evnstick_print_topmargin'].group]['evnstick_print_topmargin']) {
						Ext6.db.localStorage.set({Storage_Name: 'evnstick_print_topmargin', Storage_Value: Ext6.globalOptions[Ext6.db.localStorage.map['evnstick_print_topmargin'].group]['evnstick_print_topmargin']});
					}
					if (Ext6.globalOptions[Ext6.db.localStorage.map['evnstick_print_leftmargin'].group]['evnstick_print_leftmargin']) {
						Ext6.db.localStorage.set({Storage_Name: 'evnstick_print_leftmargin', Storage_Value: Ext6.globalOptions[Ext6.db.localStorage.map['evnstick_print_leftmargin'].group]['evnstick_print_leftmargin']});
					}
				}
			}, 
			failure: function(tx, e) {
				// TODO: Возможно надо что-то сообщать
			}
		});
	}
}