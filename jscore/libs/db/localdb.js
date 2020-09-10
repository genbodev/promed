/** Проверка и выбор драйвера для работы с локальными справочниками
 *
 */
Ext.namespace('Ext.db');
Ext.namespace('Ext.idb');
Ext.namespace('Ext.wsdb');
Ext.namespace('Ext.rdb');
Ext.namespace('Ext.sql');

// Функция проверки поддержки браузером WebSql
(function() {
	if (Ext.db.webSqlDB) {
		return;
	}
	Ext.isWebSqlDB = false;
	if (typeof window.openDatabase=='function') {
		Ext.isWebSqlDB = true;
	}
})();

// Функция проверки поддержки браузером indexedDB
(function() {
	if (Ext.db.indexedDB) {
		return;
	}
	Ext.isIndexedDb = false;
	/*
	if (Ext.isIE) {
		try {
			window.indexedDB = new ActiveXObject("SQLCE.Factory.4.0");
			window.indexedDBSync = new ActiveXObject("SQLCE.FactorySync.4.0");
		}
		catch(e) {
			log(e);
		}
	}
	*/
	Ext.db.indexedDB = window.indexedDB || // Use the standard DB API
			window.mozIndexedDB ||         // Or Firefox's early version of it
			window.webkitIndexedDB  ||     // Or Chrome's early version    
			window.msIndexedDB;            // Or IE>=10 version (?)
	if (Ext.db.indexedDB) {
		Ext.isIndexedDb = true;
	}
	// 
	Ext.db.IDBTransaction = window.IDBTransaction || window.webkitIDBTransaction; 
	Ext.db.IDBKeyRange = window.IDBKeyRange || window.webkitIDBKeyRange;
})();

// Функция проверки поддержки браузером Gears
(function() {
	// We are already defined. Hooray!
	if (window.google && google.gears) {
		Ext.isGears = true;
		return;
	}

	var factory = null;

	// Firefox
	if (typeof GearsFactory != 'undefined') {
		factory = new GearsFactory();
	} else {
		// IE
		try {
			factory = new ActiveXObject('Gears.Factory');
		} catch (e) {
			// Safari
			if (navigator.mimeTypes["application/x-googlegears"]) {
				factory = document.createElement("object");
				factory.style.display = "none";
				factory.width = 0;
				factory.height = 0;
				factory.type = "application/x-googlegears";
				document.documentElement.appendChild(factory);
			}
		}
	}

	// *Do not* define any objects if Gears is not installed. This mimics the
	// behavior of Gears defining the objects in the future.
	if (!factory) {
		return;
	}

	// Now set up the objects, being careful not to overwrite anything.
	if (!window.google) {
		window.google = {};
	}

	if (!google.gears) {
		google.gears = {factory: factory};
	}
	Ext.isGears = true;
})();

// Сообщение об ошибке Gears (появляется, если в браузере не установлен Gears и браузер не поддерживает IndexedDB)
if ((!window.google || !google.gears) && !Ext.isIndexedDb  && !Ext.isWebSqlDB) {
    //location.href = GEARS_WARNING;
}

// Выбираем определенный драйвер, на случай если браузер поддерживает несколько технологий 
if (Ext.isIndexedDb) {
	Ext.isWebSqlDB = false;
	Ext.isGears = false;
}

if (Ext.isGears) {
	Ext.isWebSqlDB = false;
}

// Функция возвращает массив справочников типа wait (загружаемые по вводимому запросу)
function getDataWait() {
	return ['Diag', 'Usluga'];
};

Ext.db.AdapterStore = function(config) {
	if (Ext.isRemoteDB) {
		return new Ext.rdb.Store(config);
	}
	if (Ext.isIndexedDb) {
		return new Ext.idb.IDBStore(config);
	}
	if (Ext.isWebSqlDB) {
		return new Ext.wsdb.Store(config);
	}
	if (Ext.isGears) {
		return new Ext.sql.SQLiteStore(config);
	}
}
