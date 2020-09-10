/**
* Необходимые действия при загрузке (Общие для любых режимов открытия)
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Init
* @access       public
* @copyright    Copyright (c) 2015 Swan Ltd.
*
*/

Ext6.Loader.setConfig({
	enabled: true,
	disableCaching: false,
	paths: {
		'sw.libs' : '/?c=promed&m=getJSFile&type=extjs6&wnd=libs4',
		'sw.frames': '/?c=promed&m=getJSFile&type=extjs6&wnd=Frames4',
		'common': '/?c=promed&m=getJSFile&type=extjs6&wnd=Forms4/Common',
		'emd': '/?c=promed&m=getJSFile&type=extjs6&wnd=Forms4/EMD',
		'base': '/?c=promed&m=getJSFile&type=extjs6&wnd=Forms4/Base',
		'usluga' : '/?c=promed&m=getJSFile&type=extjs6&wnd=Forms4/Common/Usluga',
		'videoChat' : '/?c=promed&m=getJSFile&type=extjs6&wnd=Forms4/VideoChat',
		// СМП
		'smp': '/?c=promed&m=getJSFile&type=extjs6&wnd=smp',
		// UX
		'ux': '/?c=promed&m=getJSFile&type=extjs6&wnd=ux'
	}
});

Ext6.onReady(function(){
	Ext6.override(Ext6.form.field.Date, {
		format: 'd.m.Y'
	});
	Ext6.override('Ext6.ux.form.DateTimeField', {
		timeFormat: 'H:i'
	});
});

// общий менеджер окон для 2 и 6 экста, базируется на 6-ом эксте.
sw.WindowMgr = Ext6.WindowMgr;
sw.WindowMgr.setBase(9000);
// переопределяем менеджен окон из второго ExtJS, т.к. много где есть его использование.
Ext.WindowMgr = sw.WindowMgr;


/**
 * Глобальное приложение
 */
Ext6.application({
	name: 'swExt4',
	appFolder: 'jscore'
});

/**
 * Увеличиваем таймаут для всех Ajax-запросов
 */
Ext6.Ajax.setTimeout(600000);

// Раз в 5 минут грузим настройки с сервера (которые могут меняться, на данный момент это только права доступа к формам)
setInterval(function () {
	Ext.Ajax.request({
		url: '/?c=Options&m=getNewOptions',
		params: {},
		callback: function (options, success, response) {
			if (success) {
				var result = Ext.util.JSON.decode(response.responseText);
				if (result && result.globals && result.globals.blockFormList) {
					Ext.globalOptions.globals.blockFormList = result.globals.blockFormList;
				}
			}
		}
	});
}, 300000); // каждые 5 минут

sw.lostConnection = false;
// Раз в несколько секунд опрашиваем связь с сервером, а если её нет переключаемся на локальный веб.
setInterval(function () {
	checkConnection();
}, 10000); // 10 секунд

// Менеджер локальных данных для локальных справочников
sw.localStorage = {
	storage: {}, // хранилище
	/**
	 * Хэширование строки параметров
	 */
	hash: function(s) {
		var hash = 0, i, chr;
		if (s.length === 0) return hash;
		for (i = 0; i < s.length; i++) {
			chr   = s.charCodeAt(i);
			hash  = ((hash << 5) - hash) + chr;
			hash |= 0; // Convert to 32bit integer
		}
		return hash;
	},
	/**
	 * Загрузка в store локальных данных
	 */
	load: function(s, params, options) {
		options = options || {};
		var hash = 0;
		if (params && params.where) {
			hash = this.hash(params.where);
		}
		if (this.storage[s.tableName] && this.storage[s.tableName][hash]) {
			var r = s.reader.readRecords(this.storage[s.tableName][hash]);
			s.loadRecords(r, options, true);
			return true;
		} else {
			return false;
		}
	},
	/**
	 * Сохранение локальных данных
	 */
	save: function(s, params, result) {
		var hash = 0;
		if (params && params.where) {
			hash = this.hash(params.where);
		}

		if (this.storage[s.tableName]) {
			// храним только последний набор и набор без параметров, поэтому очищаем все остальные
			for (var hash_key in this.storage[s.tableName]) {
				if (hash_key != 0 && typeof this.storage[s.tableName][hash_key] == 'object') {
					delete this.storage[s.tableName][hash_key];
				}
			}
		} else {
			this.storage[s.tableName] = {};
		}

		this.storage[s.tableName][hash] = result;
	}
};

window.onerror = function (errorMsg, url, lineNumber, column, errorObj) {
	// собираем инфу и сохраняем в БД.
	var techInfo = getPromedTechInfo();

	var ignore = false;
	if (errorMsg && errorMsg.indexOf("'dom'") > -1) {
		ignore = true;
	}

	if (getRegionNick() == 'vologda' && !ignore) {
		Ext.Ajax.request({
			url: '/?c=Common&m=saveSystemError',
			params: {
				techInfo: Ext.util.JSONalt.encode(techInfo, 0, 6),
				error: errorMsg,
				window: techInfo.currentWindow,
				url: null,
				params: Ext.util.JSON.encode({url: url, lineNumber: lineNumber, column: column, errorObj: errorObj})
			},
			callback: function (opt, success, response) {
				// ok
			}
		});
	}
}

sw.messageListener = function(event) {
	if (event.data && event.data.action && event.data.action == 'openEditWindow') {
		openEditForm({
			Evn_id: event.data.Evn_id
		});
	}
};

if (window.addEventListener) {
	window.addEventListener("message", sw.messageListener);
} else {
	// IE8
	window.attachEvent("onmessage", sw.messageListener);
}