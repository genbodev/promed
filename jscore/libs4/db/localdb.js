/** Проверка и выбор драйвера для работы с локальными справочниками
 *
 */
Ext6.namespace('Ext6.db');
Ext6.namespace('Ext6.idb');
Ext6.namespace('Ext6.wsdb');
Ext6.namespace('Ext6.rdb');
Ext6.namespace('Ext6.sql');

// Функция возвращает массив справочников типа wait (загружаемые по вводимому запросу)
function getDataWait() {
	return ['Diag', 'Usluga'];
};

Ext6.db.AdapterStore = function(config) {
	if (Ext6.isRemoteDB) {
		return new Ext6.rdb.Store(config);
	}
}
