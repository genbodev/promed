/**
 * swFunctions. Общие функции проекта PromedWeb
 * @package      Libs
 * @access       public
 * @copyright    Copyright © 2009 Swan Ltd.
 * @version      10.04.2009
 */

 /*
	TODO: Night: Все смешалось в доме Облонских (с).
	1. Нужно будет разнести функции на прикладные и системные.
	1.1. Кроме того функции прикладные можно разнести по namespaces.
	2. Задокументировать функции согласно принятым стандартам )
	*/

/*
 * Функция добавлеяет events в listeners указанному гриду
 * фильтр грида swFilterGridPlugin.js
 */
function _addFilterToGrid(grid,columnsFilter,configParams){
	//Подключение фильтра к гриду
		grid.addListener('render',
			function(g){
				_setGridFilter(g, columnsFilter, configParams);
			}
		);
		//Контроль изменения текста заголовка сторонними скриптами
		grid.getGrid().getColumnModel().grid = grid.id;
		grid.getGrid()
			.getColumnModel()
			.addListener('headerchange',
			function(g, columnIndex)
			{
				_controlHeaders(g,columnIndex);
			}
		);
}

/**
 * Функция наблюдения за текстом заголовков грида, т.к. setFilter() рисует в заголовка кнопки для открытия окна фильтров и есть сторонние скрипты
 * которые воздействуют на текст заголовков - данная функция на listeners контролирует м в случае необходимости вставляет кнопку открытия окна фильтра
 * фильтр грида swFilterGridPlugin.js
 */
function _controlHeaders(g,columnIndex){

	var dataIndex = g.getDataIndex(columnIndex);

	if(!Ext.getCmp(g.grid).FilterSettingConfig){

		return;
	}

	if(dataIndex.inlist(columnsFilter)){
		//Код заголовка колонки в которой используется кнопка фильтра
		var template = '<img title="Фильтр" src="/img/filter.png" style="border:0px; cursor:pointer;'+
			'display:inline-block; align:left; margin-right:3px; margin-bottom:-4px" '+
			'onclick=\'swShowWnd("swFilterGridPlugin", {params_init : |jsonconfig|})\'/><|tag|>|headertext|</|tag|>';
		//Ищем наличие кнопки фильтра в header
		var regexp = (/filter\.png/).test(g.getColumnHeader(columnIndex))
		//Не найдена кнопка фильтра
		if(!regexp){
			//ПРоверяем есть ли сохранённый фильтр для данной колонки по dataIndex
			var newheader = ((g.FilterSettings) && (g.FilterSettings[dataIndex]))
				? template.replace('|headertext|', g.getColumnHeader(columnIndex)).replace('|tag|','b')
				: template.replace('|headertext|', g.getColumnHeader(columnIndex)).replace('|tag|','span');

			newheader = newheader.replace('|jsonconfig|', Ext.getCmp(g.grid).FilterSettingConfig[dataIndex]);
			g.setColumnHeader(columnIndex, newheader);
		}
	}
}
/**
 * Функция автоматически добавляет кнопки фильтров для определённого грида и определённых колонок этого грида
 * grid - объект рида, в шапку которого необходимо внедрить фильтр
 * columnsFilter - массив с dataIndex колонок, которые должны использовать фильтр
 * фильтр грида swFilterGridPlugin.js
 */
function _setGridFilter(grid, columnsFilter, configParams){
	/*
	 var registry = form.AccountGrid.getGrid().getSelectionModel().getSelected();
	 if(getGlobalRegistryData.Registry_id)
	 getGlobalRegistryData.Registry_id = registry.get('Registry_id');
	 */
	//Получили модель грида
	var cm = grid.getColumnModel();
	//Получили шапку грида
	var headers = cm.config;
	//Задача - модифицировать шапку грида, при этом исключить скрытые заголовки и заголовки по признаку dataIndex
	//Массив для используемых заголовков по dataIndex
	var TurnFilterHeaders = columnsFilter;

	grid.FilterSettingConfig = (grid.FilterSettingsConfig) ? grid.FilterSettingsConfig : [];

	//Смотрим заголовки всех колонок и отбираем нужное
	for(key in headers){
		//Удостоверимся что мы имеем дело с объектом, а не с функцией
		if(typeof(headers[key]) == 'object'){
			//Нам нужены только заголовки

			if(headers[key].dataIndex){

				for(k in TurnFilterHeaders){
					if(headers[key].dataIndex == TurnFilterHeaders[k]){
						headers[key].width = headers[key].width + 25;
						var params = new Object;

						//Сформировал набор параметров для окна фильтрации
						params.title = headers[key].header;
						params.type = 'unicFilter';
						params.cell = headers[key].dataIndex;
						params.specific = false;
						params.value = null;

						config = (!configParams) ? {} : configParams;

						//2 след. параметра у нас в авторитете - если не назначены ранее и есть возможность назначить - назначаем
						/*if(!config.Registry_id){
							Registry_id = (getGlobalRegistryData.Registry_id) ? getGlobalRegistryData.Registry_id : null;
						}
						if(!config.RegistryType_id){
							RegistryType_id = (getGlobalRegistryData.RegistryType_id) ? getGlobalRegistryData.RegistryType_id : null;
						}*/

						config = {
							gridID     : grid.id,
							url        : (configParams.url) ? config.url : null,
							paramCell  :params,
							Registry_id : null,
							RegistryType_id : null
						};

						config =  (typeof(config) == 'object') ? Ext.util.JSON.encode(config) : config;

						grid.FilterSettingConfig[headers[key].dataIndex] = config;

						headers[key].header = '<img title="Фильтр" src="/img/filter.png" style="border:0px; cursor:pointer;'+
							'display:inline-block; align:left; margin-right:3px; margin-bottom:-4px" '+
							'onclick=\'swShowWnd("swFilterGridPlugin", '+
							'{params_init : '+grid.FilterSettingConfig[headers[key].dataIndex]+'})\'/><span>' + headers[key].header + '</span>';

					}
				}
			}
		}

	}

	grid.render();
}
/**
 * Проверяет мертв ли человек по наличию у него неотмененного DeathSvid
 *
 * option.Person_id - человек
 * option.failure - функция, которая выполняется при failure запроса
 * option.onIsDead - функция, которая выполняется, когда человек мертв (принимает в параметрах DeathSvid)
 * option.onIsLiving - функция, которая выполняется, когда человек жив
 * @param {Object} option
 * @return {void}
 */
function checkPersonDead(option)
{
	if (!option || !option.Person_id || typeof option.onIsLiving != 'function' || typeof option.onIsDead != 'function')
		return;
	Ext.Ajax.request({
		url: '/?c=MedSvid&m=getDeathSvidByAttr',
		params: {Person_id: option.Person_id, DeathSvid_IsBad: 1},
		failure: function(response, options)
		{
			if(typeof option.failure == 'function') {
				option.failure(response, options);
				return;
			}
			sw.swMsg.alert(langs('Ошибка'), langs('При проверке человека возникли ошибки!'));
		},
		success: function(response, options)
		{
			if ( response && typeof response.responseText == 'string' && response.responseText.length > 0 )
			{
				var result  = Ext.util.JSON.decode(response.responseText);
				if ( result.length > 0)
				{
					option.onIsDead(result);
					return;
				}
				option.onIsLiving(response, options);
				return;
			}
			if(typeof option.failure == 'function') {
				option.failure(response, options);
				return;
			}
			sw.swMsg.alert(langs('Ошибка'), langs('При проверке человека произошла ошибка запроса к БД!'));
		}
	});
}

/**
 * Ищет людей без использования формы поиска
 *
 * option.params - параметры, могут быть такие же как в форме поиска человека (swPersonSearchWindow)
 * option.url - можно указать другой url
 * option.failure - метод, который выполняется при failure запроса
 * option.onFound - метод, который выполняется, когда люди найдены
 * option.onNotFound - метод, который выполняется, когда люди не найдены
 * @param {Object} option
 * @return {void}
 */
function personSearchRequest(option)
{
	if (!option || !option.params)
		return;
	Ext.Ajax.request({
		url: option.url || '/?c=Person&m=getPersonSearchGrid',
		params: option.params,
		failure: function(response, options)
		{
			if(typeof option.failure == 'function') {
				option.failure(response, options);
				return;
			}
			sw.swMsg.alert(langs('Ошибка'), langs('При поиске человека возникли ошибки!'));
		},
		success: function(response, options)
		{
			if ( response && typeof response.responseText == 'string' && response.responseText.length > 0 )
			{
				var result  = Ext.util.JSON.decode(response.responseText);
				if ( result.data && result.totalCount > 0)
				{
					if(typeof option.onFound == 'function') {
						option.onFound(result.data,result.totalCount);
						return;
					}
				}
				if(typeof option.onNotFound == 'function') {
					option.onNotFound(response, options);
					return;
				}
				sw.swMsg.alert(langs('Ошибка'), langs('По заданным параметрам ни одного человека не найдено!'));
				return;
			}
			if(typeof option.failure == 'function') {
				option.failure(response, options);
				return;
			}
			sw.swMsg.alert(langs('Ошибка'), langs('При поиске человека произошла ошибка запроса к БД!'));
		}
	});
}

/**
 * Функция открытия окон на основе собранных тех. параметров
 */
function openWindowsByTechInfo() {
	log('Пытаемся открыть последние открытые окна');
	if (getGlobalOptions().se_techinfo && getGlobalOptions().se_techinfo.windows) {
		// здесь по идее окна надо отсортировать по возрастанию lastZIndex.
		var sortable = [];
		for(var k in getGlobalOptions().se_techinfo.windows) {
			if (typeof getGlobalOptions().se_techinfo.windows[k] == 'object') {
				sortable.push([getGlobalOptions().se_techinfo.windows[k], getGlobalOptions().se_techinfo.windows[k].lastZIndex]);
			}
		}
		sortable.sort(function(a, b) {return a[1] - b[1]});

		for(var k in sortable) {
			if (typeof sortable[k][0] == 'object') {
				var win = sortable[k][0];
				getWnd(win.objectClass).show(win.lastArguments);
			}
		}
	}
}

/**
 * Функция сбора тех. параметров для возможности открытия промеда с теми же окнами, что на момент вызова данной функции
 */
function getPromedTechInfo() {
	var techInfo = {};
	// 1. Получаем информацию о всех открытых окнах
	techInfo.windows = [];
	techInfo.currentWindow = "";
	var maxZIndex = 0;
	Ext.WindowMgr.each(function(win) {
		if (!win.hidden && win.lastZIndex > maxZIndex) {
			techInfo.currentWindow = win.objectClass;
			maxZIndex = win.lastZIndex;
		}
		techInfo.windows.push({
			'objectClass': win.objectClass,
			'lastArguments': win.lastArguments,
			'hidden': win.hidden,
			'lastZIndex': win.lastZIndex
		});
	});

	// 2. Получаем информацию из Ext.globalOptions
	techInfo.extglobaloptions = Ext.globalOptions

	log('Техническая информация с клиента собрана', techInfo);
	return techInfo;
}

/**
 * Получение даты актуальности репликации
 */
function getReplicationInfo(db, callback) {
	Ext.Ajax.request({
		callback: function(options, success, response) {
			var text = 'Актуальность данных: (неизвестно)';
			
			if (success) {
				var result = Ext.util.JSON.decode(response.responseText);
				text = 'Актуальность: ' + (!Ext.isEmpty(result.actualDT) ? result.actualDT : '(неизвестно)');

				if (!Ext.isEmpty(result.syncDT)) {
					text = text + ' / Время синхронизации: ' + result.syncDT;
				}
			}
			
			if (typeof callback == 'function') {
				callback(text);
			}
		},
		params: {
			db: db
		},
		url: '/?c=Utils&m=getReplicationInfo'
	});
}

/**
 * Печать через Birt
 */
function printBirt(options) {
	if ( typeof options != 'object' ) {
		options = new Object();
	}
	if (Ext.isEmpty(options.Report_FileName)) {
		sw.swMsg.alert(langs('Ошибка'), langs('Не задано имя отчета для формирования.'));
		return false;
	}
	if (Ext.isEmpty(options.Report_Params)) {
		options.Report_Params = '';
	}
	if (Ext.isEmpty(options.Report_Format)) {
		options.Report_Format = '';
	}

	if (usePostgre()) {
		var match = options.Report_FileName.match(/^(.+)\.rptdesign$/);

		if (match && match[1].slice(-3) != '_pg') {
			options.Report_FileName = match[1]+'_pg.rptdesign';
		}
	}

	// 1. напрямую
	// window.open(((getGlobalOptions().birtpath)?getGlobalOptions().birtpath:'')+'/run?__report=report/'+options.Report_FileName + options.Report_Params + '&__format=' + options.Report_Format, '_blank');
	// 2. через прокси
	if(Array.isArray(options.Report_FileName)) {
		for (i = 0; i < options.Report_FileName.length; i++) {
			window.open('/?c=ReportRun&m=RunByFileName&Report_FileName=' + options.Report_FileName[i] + '&Report_Params=' + encodeURIComponent(options.Report_Params) + '&Report_Format=' + options.Report_Format, '_blank');
		}
	}else {
		window.open('/?c=ReportRun&m=RunByFileName&Report_FileName=' + options.Report_FileName + '&Report_Params=' + encodeURIComponent(options.Report_Params) + '&Report_Format=' + options.Report_Format, '_blank');
	}
}

/**
 * Функция для отображения списка в виде таблицы в отдельной вкладке браузера (код и стили позаимстовованы из ext.ux.gridprint)
 * Формат входящих данных:
 * 	- title: заголовок окна;
 * 	- addNumberColumn: флаг включающий нумерацию строк;
 * 	- header_data: описание колнок (формат: dataIndex: текстовый идентификатор поля данных, header: наименование колонки);
 * 	- row_data: данные для печати в формате массива обьектов.
 */
function printDataOnNewTab(data) {
    var page_title = data.title;
    var columns = data.header_data;
    var row_data = data.row_data;

    //автонумерация строк
    if (!Ext.isEmpty(data.addNumberColumn)) {
        columns.unshift({dataIndex: 'PrintNum', header: langs('№ п/п')});

        for (var i = 0; i < row_data.length; i++) {
            row_data[i].PrintNum = i+1;
        }
    }

    var headings = Ext.ux.GridPrinter.headerTpl.apply(columns);
    var body = Ext.ux.GridPrinter.bodyTpl.apply(columns);

    var html = new Ext.XTemplate(
        '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">',
        '<html>',
        '<head>',
        '<meta content="text/html; charset=UTF-8" http-equiv="Content-Type" />',
        '<link href="' + Ext.ux.GridPrinter.stylesheetPath + '" rel="stylesheet" type="text/css" media="screen,print" />',
        '<title>' + page_title + '</title>',
        '</head>',
        '<body>',
        '<table>',
        headings,
        '<tpl for=".">',
        body,
        '</tpl>',
        '</table>',
        '</body>',
        '</html>'
    ).apply(row_data);

    // для того, чтобы вывод шел не в уже созданое окно
    var id_salt = Math.random();
    var win_id = 'printgrid' + Math.floor(id_salt*10000);
    // собственно открываем окно и пишем в него
    var win = window.open('', win_id);
    win.document.write(html);
    win.document.close();
}

/*
* функция проверяет корректность единого номера по алгоритму
* На входе 16ти значный единый номер полиса
* на выходе - логическое значение
*/
function checkEdNumSignature(ednum) {
	var reg = /^\d{16}$/;
		if ( !reg.test(ednum) )
			return false;
	var key = ednum.charAt(ednum.length - 1);
	var str_chet = '';
	var str_nechet = '';
	for ( var i = 14; i >= 0; i-- )
		if ( Number(ednum.charAt(i))%2 == 0 )
			str_chet = String(str_chet).concat(String(ednum.charAt(i)));
		else
			str_nechet = String(str_nechet).concat(String(ednum.charAt(i)));
	var str_number = String(str_chet).concat(String(Number(str_nechet) * 2));
	var summ = 0;
	for ( var i = 0; i < str_number.length; i++ )
		summ += Number(str_number.charAt(i));
	var number_key = summ%10 == 0 ? 0 : 10 - summ%10;
	if ( number_key == key )
		return true;
	else
		return false;
}

/*
* функция проверяет корректность единого номера полиса по алгоритму
* На входе 16ти значный единый номер полиса
* на выходе - логическое значение
*/
function checkEdNumFedSignature(ednum) {
	var reg = /^\d{16}$/;
		if ( !reg.test(ednum) )
			return false;
	var key = ednum.charAt(ednum.length - 1);
	var str_chet = '';
	var str_nechet = '';
	for ( var i = 14; i >= 0; i-- )
		if ( i%2 == 0 )
			str_nechet = String(str_nechet).concat(String(ednum.charAt(i)));
		else
			str_chet = String(str_chet).concat(String(ednum.charAt(i)));
	var str_number = String(str_chet).concat(String(Number(str_nechet) * 2));
	var summ = 0;
	for ( var i = 0; i < str_number.length; i++ )
		summ += Number(str_number.charAt(i));
	var number_key = summ%10 == 0 ? 0 : 10 - summ%10;
	if ( number_key == key )
		return true;
	else
		return false;
}
/**
*  Выводит дамп переданного объекта в отладочном окне.
*  Входящие данные:
*  ссылка на объект
*  На выходе:
*  пусто
*/
function swalert(obj)
{
	if ( sw.Promed.debug_wnd )
		sw.Promed.debug_wnd.close();
	sw.Promed.debug_wnd = new Ext.Window({
		autoScroll: true,
		width: 700,
		height: 500,
		maximizable: true,
		closeAction: 'close',
		collapsible: true,
		title: langs('Отладочная информация'),
		items: [
		new Ext.Panel({
			id: 'promed_debug',
			region: 'center'
		})
		]
	});
	sw.Promed.debug_wnd.show();
	var ppTable = prettyPrint(obj);
	debug = document.getElementById('promed_debug');
	debug.innerHTML = '';
	debug.appendChild(ppTable);
}

// получение идентификатора с картридера
function getSocCardReadersArray()
{
	var response = {success: false, ErrorCode: 1, ErrorMessage: langs('Произошла ошибка определения списка.')};
	var readers_array = new Array();
	// проверяем наличие плагина
	if ( document.apl )
	{
		// проверяем доступность методов
		if ( typeof document.apl.getReaders == 'unknown' || document.apl.getReaders ) // IE, это один большой костыль и УГ
		{
			// вызываем методы
			try
			{
				var readers = document.apl.getReaders();
				if ( readers )
				{
					//var reader = String(readers).substr(1, String(readers).length - 2);
					var readers_array = String(readers).split(", ");
					if ( readers_array.length > 0 )
					{
						readers_array[0] = String(readers_array[0]).substr(1, String(readers_array[0]).length - 1);
						readers_array[readers_array.length - 1] = String(readers_array[readers_array.length - 1]).substr(0, String(readers_array[readers_array.length - 1]).length - 1);
						response.success = true;
						response.readersArray = readers_array;
						response.ErrorCode = null;
						response.ErrorMessage = null;
					}
					else
					{
						response.ErrorCode = 5;
						response.ErrorMessage = langs('Не удалось получить список устройств чтения карт.');
					}
				}
				else
				{
					response.ErrorCode = 5;
					response.ErrorMessage = langs('Не удалось получить список устройств чтения карт.');
				}
			}
			catch (e)
			{
				response.ErrorCode = 4;
				response.ErrorMessage = langs('Произошла ошибка чтения карты.');
			}
		}
		else
		{
			response.ErrorCode = 3;
			response.ErrorMessage = langs('Нет доступа к методам плагина. Попробуйте обновить страницу. В крайнем случае возможно прийдется обновить Java-plugin в браузере.');
		}
	}
	else
	{
		response.ErrorCode = 2;
		response.ErrorMessage = langs('Не найден плагин для чтения данных картридера.');
	}
	return response;
}

// получение идентификатора с картридера
function getSocCardNumFromReader()
{
	var response = {success: false, ErrorCode: 1, ErrorMessage: langs('Произошла ошибка определения идентификатора.')};
	// проверяем наличие плагина
	if ( document.apl )
	{
		// проверяем доступность методов
		if ( typeof document.apl.getReaders == 'unknown' || document.apl.getReaders ) // IE, это один большой костыль и УГ
		{
			// вызываем методы
			try
			{
				var readers = getSocCardReadersArray();
				if ( readers && readers.success == true && readers['readersArray'] && readers.readersArray.length > 0 )
				{
					var reader = readers.readersArray[0];
					var soccard_id = document.apl.getID(reader);
					if ( soccard_id.length >= 25 )
					{
						response.success = true;
						response.SocCard_id = soccard_id;
						response.ErrorCode = null;
						response.ErrorMessage = null;
					}
					else
					{
						response.ErrorCode = 5;
						response.ErrorMessage = langs('Карта не вставлена.');
					}
				}
				else
				{
					response.ErrorCode = 5;
					response.ErrorMessage = langs('Не удалось получить список устройств чтения карт.');
				}
			}
			catch (e)
			{
				response.ErrorCode = 4;
				response.ErrorMessage = langs('Произошла ошибка чтения карты.');
			}
		}
		else
		{
			response.ErrorCode = 3;
			response.ErrorMessage = langs('Нет доступа к методам плагина. Попробуйте обновить страницу. В крайнем случае возможно прийдется обновить Java-plugin в браузере.');
		}
	}
	else
	{
		response.ErrorCode = 2;
		response.ErrorMessage = langs('Не найден плагин для чтения данных картридера.');
	}
	return response;
}
/**
 * Создание списка карт-ридеров доступных в системе
 */
function initCardReaders() {
	log(langs('Инициализируем список ридеров'));
	sw.Applets.readers = [];
	var readers = getSocCardReadersArray();
	if (readers && (!readers.ErrorMessage || String(readers.ErrorMessage) == '')) {
		// todo: в список ридеров с данным плагином еще попадает test из апплета, который там видимо прибит гвоздями, можно его исключать
		sw.Applets.readers = readers.readersArray;
	} else {
		log(langs('Картридер не определен, используется ридер по умолчанию'));
		sw.Applets.readers.push('Gemplus Usb Smart Card Reader 0');
	}
	return sw.Applets.readers;
}

/**
 * Создание данных Store, Grid, BaseParams
 *
 * @param {Object[]} arr Массив данных полей
 * @param {String} object Название объекта
 * @return {Object[]} Массив с параметрами объектов Store, Grid, BaseParams
 */
function swGetJSONData(arr, object)
{
	var result = new Array();
	// Формируем пять разных объектов
	result['store'] = new Array();
	result['grid'] = new Array();
	result['base'] = new Object();
	result['emply'] = new Object();
	result['params'] = new Array();

	result['editing'] = false;
	result['grouping'] = false;
	result['sortInfo'] = {};
	result['groupField'] = null;
	result['plugins'] = [];
	var n = -1;
	var p = -1;
	this.key_id = null;
	this.autoexpand = false;
	for (i=0; i < arr.length; i++)
	{
		// Бежим по всем элементам, но добавляем только те, у которых указан параметр name
		if ((arr[i]) && (arr[i]['name']))
		{
			n++;
			result['grid'][n] = new Object();
			result['store'][n] = new Object();

			result['store'][n].name = arr[i]['name'];
			result['emply'][arr[i]['name']] = null;


			if (arr[i]['key']) // если key = true
			{
				this.key_id = arr[i]['name'];
				//result['grid'] = result['grid'] + "id: '"+key_id+"', ";
				/*
				result['grid'][n].hidden =(arr[i]['hidden']!=undefined)?arr[i]['hidden']:true;
				result['grid'][n].hideable = false;
				*/
				if (arr[i]['hidden']!=undefined) {
					result['grid'][n].hidden = arr[i]['hidden'];
				} else {
					result['grid'][n].hidden = true;
				}
				result['grid'][n].hideable = (arr[i]['hideable']!=undefined)?arr[i]['hideable']:false;

			}
			else
			{
				if (arr[i]['hidden']!=undefined)
				{
					result['grid'][n].hidden = arr[i]['hidden'];
					if (arr[i].hidden ==true)
						result['grid'][n].hideable = false;
				}
				else
				{
					result['grid'][n].hidden  = false;
				}
				if (arr[i]['hideable']!=undefined)
				{
					result['grid'][n].hideable = arr[i]['hideable'];
				}
			}
			if (arr[i]['isparams'] || (i==0))
			{
				p++;
				result['params'][p] = new Object();
				result['params'][p].name = arr[i]['name'];
			}
			if(arr[i]['filter']!=undefined){
				result['grid'][n].filter = arr[i]['filter'];
			}
			if(arr[i]['filterStyle']!=undefined){
				result['grid'][n].filterStyle = arr[i]['filterStyle'];
			}
			if (arr[i]['value']!=undefined)
			{
				result['base'][arr[i]['name']] = arr[i]['value'];
			}
			else
			{
				result['base'][arr[i]['name']] = '';
			}

			if (arr[i]['sortable']!=undefined)
			{
				result['grid'][n].sortable = arr[i]['sortable'];
			}
			else
			{
				result['grid'][n].sortable = true;
			}
			result['grid'][n].dataIndex = arr[i]['name'];

			// init sort field
			if (arr[i]['sort']!=undefined)
			{
				if (arr[i]['sort'])
				{
					result['sortInfo'].field = arr[i]['name'];
					if (arr[i]['direction']!=undefined)
					{
						result['sortInfo'].direction = arr[i]['direction'];
					}
				}
			}
			// group store
			if (arr[i]['group']!=undefined)
			{
				if (arr[i]['group'])
				{
					result['groupField'] = arr[i]['name'];
					result['grouping'] = true;
				}
			}

			// autoexpand ?
			if (arr[i]['id']!=undefined)
			{
				result['grid'][n].id = arr[i]['id'];
				if (arr[i]['id'] == 'autoexpand')
				{
					this.autoexpand = true;
					if (arr[i]['autoExpandMin']!=undefined)
					{
						this.autoExpandMin = arr[i]['autoExpandMin'];
					}
				}
			}
			else
			{ // при указании autoexpand: true
				if (arr[i]['autoexpand']!=undefined)
				{
					if (arr[i]['autoexpand'])
					{
						result['grid'][n].id = 'autoexpand';
						this.autoexpand = arr[i]['autoexpand'];
						if (arr[i]['autoExpandMin']!=undefined)
						{
							this.autoExpandMin = arr[i]['autoExpandMin'];
						}
					}
				}
			}

			if (arr[i]['align']!=undefined)
			{
				result['grid'][n].align = arr[i]['align'];
			}

			if (arr[i]['headerAlign']!=undefined)
			{
				result['grid'][n].headerAlign = arr[i]['headerAlign'];
			}

			if (arr[i]['qtip']!=undefined)
			{
				result['grid'][n].qtip = arr[i]['qtip'];
			}

			// Тип и даты
			if (arr[i]['type']!=undefined)
			{
				switch (arr[i]['type']) {
					case 'date':
						var dateFormat = 'd.m.Y';
						result['store'][n].dateFormat = dateFormat;
						result['grid'][n].renderer = Ext.util.Format.dateRenderer(dateFormat);
						result['store'][n].type = arr[i]['type'];
						break;
					case 'checkbox':
						result['store'][n].type = 'string';
						result['grid'][n].renderer = sw.Promed.Format.checkColumn.createDelegate(result['grid'][n]);
						break;
					case 'checkcolumn':
						result['store'][n].type = 'string';
						result['grid'][n].renderer = sw.Promed.Format.checkColumn.createDelegate(result['grid'][n]);
						break;
					case 'checkcolumnedit':
						//result['grid'][n].renderer = sw.Promed.Format.checkColumnEdit;
						break;
					case 'multiselectcolumnedit':
						//result['grid'][n].renderer = sw.Promed.Format.checkColumnEdit;
						break;
					case 'money':
						result['store'][n].type = 'float';
						result['grid'][n].renderer = sw.Promed.Format.rurMoney;
						break;
					case 'time':
						result['store'][n].dateFormat = 'H:i';
						result['store'][n].type = 'date';
						result['grid'][n].renderer = Ext.util.Format.dateRenderer('H:i');
						break;
					case 'datetime':
						result['store'][n].dateFormat = 'd.m.Y H:i';
						result['store'][n].type = 'date';
						result['grid'][n].renderer = Ext.util.Format.dateRenderer('d.m.Y H:i');
						break;
					case 'timedate':
						result['store'][n].dateFormat = 'H:i d.m.Y';
						result['store'][n].type = 'date';
						result['grid'][n].renderer = Ext.util.Format.dateRenderer('H:i d.m.Y');
						break;
					case 'datetimesec':
						result['store'][n].dateFormat = 'd.m.Y H:i:s';
						result['store'][n].type = 'date';
						result['grid'][n].renderer = Ext.util.Format.dateRenderer('d.m.Y H:i:s');
						break;
					case 'rownumberer':
						result['grid'][n] = new Ext.grid.RowNumberer();
						break;
					default:
						result['store'][n].type = arr[i]['type'];
						break;
				}
			}
			else
			{
				result['store'][n].type = 'string';
			}

			if (arr[i]['renderer'])
			{
				result['grid'][n].renderer = arr[i]['renderer'];
			}

			if (arr[i]['mapping']!=undefined)
			{
				result['store'][n].mapping = arr[i]['mapping'];
			}
			else
			{
				result['store'][n].mapping = arr[i]['name'];
			}

			// Размер колонки грида
			if (arr[i]['width']!=undefined)
			{
				result['grid'][n].width = arr[i]['width'];
			}

			// Тип сортировки
			if (arr[i]['sortType']!=undefined)
			{
				result['store'][n].sortType = arr[i]['sortType'];
			}

			if (arr[i]['fixed']!=undefined)
			{
				result['grid'][n].fixed = arr[i]['fixed'];
			}

			// Скрыть колнку из печати
			if (arr[i]['hiddenPrint']!=undefined)
			{
				result['grid'][n].hiddenPrint = arr[i]['hiddenPrint'];
			}

			// Редактор
			if (arr[i]['editor']!=undefined)
			{
				result['grid'][n].editor = arr[i]['editor'];
				result['editing'] = true;
			}

			// CSS
			if (arr[i]['css']!=undefined)
			{
				result['grid'][n].css = arr[i]['css'];
			}

			// Заголовки колонок грида
			if (arr[i]['header']!=undefined)
			{
				result['grid'][n].header = arr[i]['header'];
			}
			else
			{
				result['grid'][n].header = langs('Не определено!');
			}

			// Преобразуем checkcolumnedit в CheckColumnEdit )
			if (arr[i]['type']=="checkcolumnedit") {
				var checkcolumn = new Ext.grid.CheckColumnEdit(result['grid'][n]);
				result['plugins'].push(checkcolumn);
				result['grid'][n] = checkcolumn;
				result['editing'] = true;
			}

			// Преобразуем multiselectcolumnedit в MultiSelectColumnEdit )
			if (arr[i]['type']=="multiselectcolumnedit") {
				var checkcolumn = new Ext.grid.MultiSelectColumnEdit(result['grid'][n]);
				result['plugins'].push(checkcolumn);
				result['grid'][n] = checkcolumn;
				result['editing'] = true;
			}

		// ID - не отображаем
		/*
			if (i==0)
				result['grid'] = "";
			*/
		}

	}
	if ((!this.key_id) && (arr[0]['name']!=undefined))
	{
		this.key_id = arr[0]['name'];
		result['grid'][0].hidden = true;
		result['grid'][0].hideable = false;
	}

	// Передаем объект
	result['base'].object = object;
	// Также передаем первую запись
	result['key_id'] = this.key_id;

	result['autoexpand'] = this.autoexpand;
	if (this.autoExpandMin)
	{
		result['autoExpandMin'] = this.autoExpandMin;
	}
	return result;
}


/**
*  Получение временного id для store
*  Входящие данные: store - хранилище
*  На выходе: число
*/
function swGenTempId(store)
{
	var record;
	var result = 0;

	var collection = store.data?store.data:store;
	if (!(collection instanceof Ext.util.MixedCollection)) {
		return result;
	}

	while (record || result == 0)
	{
		result = Math.floor(Math.random() * 1000000);
		record = collection.key(result);
	}

	return result;
}
/**
 *  Открытие новой вкладки (окна) браузера
 *  Входящие данные:
 *       id   : наименование объекта
 *       html : выводимые данные
 *  На выходе: число
 */
function openNewWindow(html)
{
	/*
	var id_salt = Math.random();
	var win_id = 'win_' + Math.floor(id_salt*100000);
	*/
	var win = window.open('#', '_blank');
	win.document.write(html);
	if(typeof win.stop == 'function')
	{
		win.stop();
	}
}

/**
* Аналог функции in
* проверка вхождения в массив значений
* Пример использования: if ( field.getValue().inlist(['1','2','7','8']) )
*
*/
function inlist(looking_for, list)
{
	for(i in list)
	{
		if(looking_for == list[i])
		{
			return true;
		}
	}
	return false;
}

String.prototype.inlist=function(list)
{
	for(i in list)
	{
		if(this==list[i])
		{
			return true;
		}
	}
	return false;
};
Number.prototype.inlist=String.prototype.inlist;

/**
 * Возвращает значение первого параметра объекта
 */
function getFirstValue(o) {
	if (typeof o == 'object') {
		for (var i in o) {
			return o[i];
			break;
		}
	}
	return null;
}

/**
 * Возвращает наименование первого параметра объекта
 */
function getFirstKey(o) {
	if (typeof o == 'object') {
		for (var i in o) {
			return i;
			break;
		}
	}
	return null;
}

function count( mixed_var, mod )
{ // Count elements in an array, or properties in an object
	var key, cnt = 0;
	mode = 0;
	if( mod == 'RECURSIVE' ) mode = 1;
	for (key in mixed_var)
	{
		cnt++;
		if( mode && mixed_var[key] && (mixed_var[key].constructor === Array || mixed_var[key].constructor === Object) )
		{
			cnt += count(mixed_var[key], 1)-1;
		}
	}
	return cnt;
}



/**
 * Показ раздела справки соответсвующей входному параметру
 * @param {String} section Раздел справки.
 * @return {Boolean}
 */
function ShowHelp(section)
{
	if (Ext.isEmpty(section)) {
		return false;
	}

	var
		useConfluence = !Ext.isEmpty(getGlobalOptions().confluencepath),
		helpPath = (useConfluence ? getGlobalOptions().confluencepath : getGlobalOptions().wikipath),
		spaceSymbol = (useConfluence ? '+' : '_');

	section = section.replace(/ /g, spaceSymbol);

	//https://redmine.swan.perm.ru/issues/13024
	//при вызове помощи из Армов в section кидалась длиннющая строка с тегами ссылки и проч. Поэтому в таких случаях нужно обрубать ее и оставлять только нужное.
	if (section.indexOf(spaceSymbol + '&nbsp;&nbsp;') > 0) {
		section = section.substr(0, section.indexOf(spaceSymbol + '&nbsp;&nbsp;') + 1);
	}

	if (section.indexOf('href') > 0) {
		section = section.substr(section.indexOf('>') + 1,section.indexOf(spaceSymbol + '/')-section.indexOf('>') - 1);
	}

	//https://redmine.swan.perm.ru/issues/97315
	//убираем после слеша все
	if (section.indexOf(spaceSymbol + '/') > 0) {
		section = section.substr(0, section.indexOf(spaceSymbol + '/'));
	}

	if (getGlobalOptions().region) {
		window.open(helpPath + section);
	}
	else { // вдруг регион не определен
		window.open('/wiki/main/wiki/' + section);
	}
	return true;
}


/**
 * Возвращает кнопку контекстной помощи, по названию раздела справки
 * @param {Ext.Window} self ссылка на окно, к которому делается кнопка
 * @param {int} tbidx табиндекс для кнопки
 * @return {}
 */
function HelpButton(self, tbidx)
{
	if (self != undefined)
	{
		var btnCfg = {
			text: BTN_FRMHELP,
			iconCls: 'help16',
			handler: function(button, event) {
				ShowHelp(self.title);
			}.createDelegate(self)
		};
		if (tbidx != undefined)
		{
			btnCfg.tabIndex = tbidx;
		}
		return new Ext.Button(btnCfg);
	}
}

/**
*  Дизейблит итемы объекта и всех внутренних объектов
*/
function disableItems(Object) {
	var _this = this;
	if (Object.items) {
		Object.items.each(function(el){
			_this.disableItems(el);
		});
	} else {
		Object.setDisabled(true);
	}
}
/**
*  Получение записей из Store
*  Входящие данные: store - хранилище данных
*                   options - параметры получения
*  На выходе: массив записей
*/
function getStoreRecords(store, options)
{
	var fields = new Array();
	var fields_types = new Array();//параллельный массив с типами полей, для избежания гадания типа поля на кофейной гуще (т.е. по названию поля)
	var result = new Array();

	if (store == undefined || store == null)
	{
		return result;
	}

	// Опции по умолчанию
	if (options == undefined || options == null)
	{
		var options = new Object();
		options.convertDateFields = false;
		options.dateFormat = 'd.m.Y';
		options.exceptionFields = new Array();
		options.exceptionRecordIds = new Array();
	}
	else
	{
		if (!options.convertDateFields) options.convertDateFields = false;
		if (!options.dateFormat) options.dateFormat = 'd.m.Y';
		if (!options.exceptionFields) options.exceptionFields = new Array();
		if (!options.exceptionRecordIds) options.exceptionRecordIds = new Array();
	}

	if (options.clearFilter) {
		store.clearFilter();
	}

	store.fields.eachKey(function(key, item) {
		var i;
		var add = true;

		for (i = 0; i < options.exceptionFields.length; i++)
		{
			if (key.toLowerCase().indexOf(options.exceptionFields[i].toLowerCase()) != -1)
			{
				add = false;
				break;
			}
		}

		if (add === true)
		{
			fields.push(key);
			fields_types.push(item.type);
		}
	});

	store.each(function(record) {
		var i;
		var temp_record = new Object();

		if (record.id.inlist(options.exceptionRecordIds)) {
			return true;
		}

		for (i = 0; i < fields.length; i++)
		{
			var type = fields_types[i];
			if ((options.convertDateFields === true) && (fields_types[i].toLowerCase()=='date'))
			{
				temp_record[fields[i]] = Ext.util.Format.date(record.data[fields[i]], options.dateFormat);
			}
			else
			{
				temp_record[fields[i]] = record.data[fields[i]];
			}
		}

		result.push(temp_record);
	});

	if (options.clearFilter) {
		store.filterBy(function(rec) {
			return !(Number(rec.get('RecordStatus_Code')) == 3);
		});
	}

	return result;
}

/**
 * Меняет русские буквы на английские расположенные на той же клавише (для диагнозов)
 *
 * @params {String} str Входящая строка
 * @return {String}
 * @author Lich
 */
function LetterChange(str)
{
	var str_new = "";
	for (var i=0;i<str.length;i++)
	{
		switch (str.charAt(i)) {
			case 'й':str_new = str_new + 'Q';break;
			case 'Й':str_new = str_new + 'Q';break;
			case 'ц':str_new = str_new + 'W';break;
			case 'Ц':str_new = str_new + 'W';break;
			case 'у':str_new = str_new + 'E';break;
			case 'У':str_new = str_new + 'E';break;
			case 'к':str_new = str_new + 'R';break;
			case 'К':str_new = str_new + 'R';break;
			case 'е':str_new = str_new + 'T';break;
			case 'Е':str_new = str_new + 'T';break;
			case 'н':str_new = str_new + 'Y';break;
			case 'Н':str_new = str_new + 'Y';break;
			case 'г':str_new = str_new + 'U';break;
			case 'Г':str_new = str_new + 'U';break;
			case 'ш':str_new = str_new + 'I';break;
			case 'Ш':str_new = str_new + 'I';break;
			case 'щ':str_new = str_new + 'O';break;
			case 'Щ':str_new = str_new + 'O';break;
			case 'з':str_new = str_new + 'P';break;
			case 'З':str_new = str_new + 'P';break;
			case 'ф':str_new = str_new + 'A';break;
			case 'Ф':str_new = str_new + 'A';break;
			case 'ы':str_new = str_new + 'S';break;
			case 'Ы':str_new = str_new + 'S';break;
			case 'в':str_new = str_new + 'D';break;
			case 'В':str_new = str_new + 'D';break;
			case 'а':str_new = str_new + 'F';break;
			case 'А':str_new = str_new + 'F';break;
			case 'п':str_new = str_new + 'G';break;
			case 'П':str_new = str_new + 'G';break;
			case 'р':str_new = str_new + 'H';break;
			case 'Р':str_new = str_new + 'H';break;
			case 'о':str_new = str_new + 'J';break;
			case 'О':str_new = str_new + 'J';break;
			case 'л':str_new = str_new + 'K';break;
			case 'Л':str_new = str_new + 'K';break;
			case 'д':str_new = str_new + 'L';break;
			case 'Д':str_new = str_new + 'L';break;
			case 'я':str_new = str_new + 'Z';break;
			case 'Я':str_new = str_new + 'Z';break;
			case 'ч':str_new = str_new + 'X';break;
			case 'Ч':str_new = str_new + 'X';break;
			case 'с':str_new = str_new + 'C';break;
			case 'С':str_new = str_new + 'C';break;
			case 'м':str_new = str_new + 'V';break;
			case 'М':str_new = str_new + 'V';break;
			case 'и':str_new = str_new + 'B';break;
			case 'И':str_new = str_new + 'B';break;
			case 'т':str_new = str_new + 'N';break;
			case 'Т':str_new = str_new + 'N';break;
			case 'ь':str_new = str_new + 'M';break;
			case 'Ь':str_new = str_new + 'M';break;
			case ',':str_new = str_new + '.';break;
			case 'ю':str_new = str_new + '.';break;
			case 'Ю':str_new = str_new + '.';break;
			case 'б':str_new = str_new + '.';break;
			case 'Б':str_new = str_new + '.';break;
			default:str_new = str_new +str.charAt(i);
		}
	}
	return str_new;
}

/**parser->parse
 * Вызывается для добавления записи из грида в окно объединения
 *
 * @param selRec {Record} Выбранная запись в гриде.
 * @param RecordType_Code {String} Идентификатор объединяемой таблицы, по сути ее название.
 * @param RecordType_Name {String} Название объединяемой таблицы для пользователя.
 * @param callback {Function} Функция, вызывающаяся по завершении объединения.
 * @return {Boolean}
 * @author Lich
 */
function AddRecordToUnion(
	selRec, // выбранная запись в гриде
	RecordType_Code, // идентификатор объединяемой таблицы, по сути ее название
	RecordType_Name,// название объединяемой таблицы для пользователя
	callback //функция, вызывающаяся по завершении объединения
	) {
	var unionWindow = getWnd('swRecordUnionWindow');
	var params = {
		successFn: callback,
		selRec: selRec,
		RecordType_Code: RecordType_Code,
		RecordType_Name: RecordType_Name
	};
	if (!unionWindow.isVisible()) {
		params.clearGrid = true;
	}
	unionWindow.show(params);

	return true;
}


/**
 * Вызывается для добавления записи по человеку из грида в окно Объединения человека
 *
 * @param selRec {Record} Выбранная запись в гриде.
 * @param callback {Function} Функция, вызывающаяся по завершении объединения.
 * @return {Boolean}
 * @author Lich
 */
function AddPersonToUnion(
	selRec, // выбранная запись в гриде
	callback //функция, вызывающаяся по завершении объединения
) {
	var unionWindow = getWnd('swPersonUnionWindow');
	var params = {
		successFn: callback,
		selRec: selRec
	};
	if (!unionWindow.isVisible()) {
		params.clearGrid = true;
	}
	unionWindow.show(params);

	return true;
}


/**
 * Берет из формы все параметры, включая disabled поля.
 *
 * @param {Ext.form.FormPanel} form Форма, с которой берем поля
 * @return {Array} Ассоциативный массив параметров
 * @author Lich
 */
function getAllFormFieldValues(
	form // Форма
	)
{
	var params = form.getForm().getValues();
	var arr = form.find('disabled', true);
	for (i = 0; i < arr.length; i++)
	{
		if ( arr[i].hiddenName && arr[i].xtype!='button') {
			params[arr[i].hiddenName] = arr[i].getValue();
		}
		else if ( arr[i].name && arr[i].xtype!='button') {
			params[arr[i].name] = arr[i].getValue();
		}
	}
	for (key in params) {
		if (params[key] == 'undefined') {
			delete params[key];
		}
	}
	return params;
}

/**
 * Проверяет ответ сервера и если он содержит ошибку - выдает ее
 *
 * @param {String} response Ответ сервера.
 * @return {Boolean}
 * @author Lich
 */
function handleResponseError(response)
{
	if ( response.responseText.length > 0 )
	{
		var resp_obj = Ext.util.JSON.decode(response.responseText);
		if (resp_obj.success == false)
		{
			$errStr = ((resp_obj.Error_Code)?'<b>'+resp_obj.Error_Code+'.</b>':'')+resp_obj.Error_Msg;
			sw.swMsg.alert(
				langs('Ошибка'),
				$errStr,
				function () {
					return false;
				}
				);
		}
	}
	return true;
}

/**
 * Выводит заданное окно.Проверяет, что оно еще не открыто.
 *
 * @param {String} window_name Название окна.
 * @param {Array} params Параметры передающиеся окну
 * @return {Boolean}
 * @author Lich
 */
function ShowWindow(window_name, params)
{
	if (getWnd(window_name).isVisible())
	{
		sw.swMsg.show({
			buttons: Ext.Msg.OK,
			fn: Ext.emptyFn,
			icon: Ext.Msg.WARNING,
			msg: langs('Окно уже открыто'),
			title: ERR_WND_TIT
		});
		return false;
	}

	getWnd(window_name).show(params);
	return false;
}


/**
* Добавляет пустую строку в grid
* @author Savage
*/
function LoadEmptyRow(grid, root)
{
	var data = new Object();
	var load_data = new Object();

	grid.getStore().fields.eachKey(function(key, item) {
		data[key] = null;
	});

	grid.getStore().removeAll();

	if (root)
	{
		load_data[root] = [ data ];
		grid.getStore().loadData(load_data);
	}
	else
	{
		grid.getStore().loadData([ data ]);
	}

	return true;
}

/**
 * Возвращает окно с заданным именем.
 *
 * Фабричный метод. Если окно есть в глобальной области видимости, то просто
 * возвращает его. Иначе создает и возвращает.
 *
 * @param {String} window_name Название окна.
 * @param {Function} callback1 Функция вызывающаяся если окно еще не было создано.
 * @param {Function} callback2 Функция вызывающаяся если окно уже было создано.
 * @return {Object}
 */

function swGetWindow(window_name, callback1, callback2)
{

	var result = null;
	var result_was_null = false;

	if (window[window_name] == null)
	{
		if (sw.Promed[window_name] != undefined)
		{
			result_was_null = true;
			window[window_name] = new sw.Promed[window_name]();
		}
	}

	result = window[window_name];

	if (result_was_null == true)
	{
		main_center_panel.add(result);
		main_center_panel.doLayout();

		if (callback1 != undefined && callback1 != null)
		{
			callback1(result);
		}
	}
	else
	{
		if (callback2 != undefined && callback2 != null)
		{
			callback2(result);
		}
	}

	return result;
}

function swDestroyWin(win) {
	win.destroy();
	window[win.objectName] = null;
}

function minimizeWin(win){
	if (win.noTaskBarButton) {
		return;
	}
	if (!win.modal) {
		win.getEl().set({ style: 'display:none;' });
		if (typeof win.syncShadow == 'function') {
			win.syncShadow();
		}
		win.minimized = true;
		win.toBack();
		markInactive(win);
	}
}

function markActive(win){
	if (win.noTaskBarButton) {
		return;
	}
	if (!win.taskButton || win.taskButton == null) {
		// если нет кнопки на таксбаре то добавляем её
		win.taskButton = taskbar.addTaskButton(win);
	}
	if (taskbar.buttonsCount() > 0 && main_taskbar_panel.hidden) {
		main_taskbar_panel.show();
		main_center_panel.setHeight(main_center_panel.getEl().getHeight() - main_taskbar_panel.el.getHeight());
	}
	taskbar.setActiveButton(win.taskButton);
	Ext6.select('.active-win').removeCls('active-win');
	Ext6.fly(win.taskButton.el).addCls('active-win');
	win.getEl().set({ style: 'display:block;' });
	win.minimized = false;
}

function markOnActivate(win){
	if (win.noTaskBarButton) {
		return;
	}
	if (!win.minimized) {
		taskbar.setActiveButton(win.taskButton);
		Ext6.select('.active-win').removeCls('active-win');
		Ext6.fly(win.taskButton.el).addCls('active-win');
		win.getEl().set({ style: 'display:block;' });
	}
}

function markInactive(win){
	if (win && win.taskButton) {
		Ext6.fly(win.taskButton.el).removeCls('active-win');
	}
}

function removeWin(win){
	if (win.noTaskBarButton) {
		return;
	}
	taskbar.removeTaskButton(win.taskButton);
	if (taskbar.buttonsCount() == 0 && !main_taskbar_panel.hidden) {
		main_center_panel.setHeight(main_center_panel.getEl().getHeight() + 30);
		main_taskbar_panel.hide();
	}
	win.taskButton = null;

	// swDestroyWin(win); // дестроить окно, а не скрыть.. т.к. открываются новые экземпляры окон. для Промеда дестроить не нужно видимо пока.
}

/**
 * Возвращает окно с заданным именем. Вернет Null, если код формы не загружен.
 *
 * Используется в методе swShowWnd. Если окно есть в глобальной области видимости, то просто
 * возвращает его. Иначе создает и возвращает.
 *
 * @param {String|Object} wnd Название окна или объект.
 * @param {Object} config Конфиг настроек.
 * @return {Object}
 */

function swGetWnd(wnd, config)
{
	var result = null;
	var result_was_null = false;
	if (typeof wnd !== 'object') {
		wnd = {objectName: wnd, objectClass: wnd};
	}

	if (window[wnd.objectName] == null)
	{
		if (sw.Promed[wnd.objectClass] != undefined)
		{
			// создаем объект
			result_was_null = true;
			// если в конфиге пришел класс окна, то используем его
			// в конфиге можно передать и параметры
			var params = {};
			if (config && config.params_init && typeof config.params_init == 'object') {
				params = config.params_init;
			}
			if (getWndRoles()[wnd.objectClass]) {
				params.roles = getWndRoles()[wnd.objectClass];
			} else {
				params.roles = {
					'add': true,
					'delete': true,
					'edit': true,
					'export': true,
					'import': true,
					'view': true
				};
			}
			window[wnd.objectName] = new sw.Promed[wnd.objectClass](params);
			// и устанавливаем objectName, чтобы далеко его не искать
			window[wnd.objectName].objectName = wnd.objectName;
			window[wnd.objectName].objectClass = wnd.objectClass;
		}
	}

	result = window[wnd.objectName];

	if ( !Ext.isEmpty(result) && typeof result == 'object' && result.allowDuplicateOpening == true && result.isVisible() ) {
		result_was_null = true;

		var params = {};
		if (config && config.params_init && typeof config.params_init == 'object') {
			params = config.params_init;
		}
		if (getWndRoles()[wnd.objectClass]) {
			params.roles = getWndRoles()[wnd.objectClass];
		}
		else {
			params.roles = {
				'add': true,
				'delete': true,
				'edit': true,
				'export': true,
				'import': true,
				'view': true
			};
		}

		var dt = new Date();

		params.id = wnd.objectName + dt.format('YmdHis');
		params.isWindowCopy = true;

		result = new sw.Promed[wnd.objectClass](params);

		result.objectName = wnd.objectName;
		result.objectClass = wnd.objectClass;
	}

	if (result_was_null == true)
	{
		// Добавляем окно в панель
		main_center_panel.add(result);
		main_center_panel.doLayout();

		// действия только если есть таксбар
		if (!Ext.isEmpty(taskbar) && typeof taskbar == 'object') {
			result.on({
				'activate': {
					fn: markOnActivate
				},
				'beforeshow': {
					fn: markActive
				},
				'minimize': {
					fn: minimizeWin
				},
				'hide': {
					fn: removeWin
				}
			});
		}
	}
	if (config && config.callback && typeof config.callback == 'function')
	{
		config.callback(result, result_was_null); // Функция выполняющаяся при получения окна
	}
	return result;
}

/**
 * Открывает окно с заданным именем.
 *
 * Если окно есть в глобальной области видимости, то просто открывает его
 * Иначе создает и открывает.
 *
 * @param {String} window_name Название окна объекта.
 * @param {Object} config Конфиг настроек вида {params_init: {}, callback: function (result, isCreateWnd), params: {}}
 * @return {Object}
 */

function swShowWnd(wnd, config)
{
	log({'Метод':'swShowWnd', 'wnd': wnd, 'config': config});
	function show()
	{
		if (checkWindowInExtJS6(wnd.objectClass)) {
			var win = Ext6.create(WINDOWS_ALIAS[wnd.objectClass], {
				objectClass: wnd.objectClass
			});
			if (!Ext6.isEmpty(taskbar) && typeof taskbar == 'object') {
				win.on({
					'activate': {
						fn: markOnActivate
					},
					'beforeshow': {
						fn: markActive
					},
					'minimize': {
						fn: minimizeWin
					},
					'hide': {
						fn: removeWin
					},
					'destroy': {
						fn: removeWin
					}
				});

				markActive(win);
			}
			win.objectName = wnd.objectName;
			win.objectClass = wnd.objectClass;
			sw.Promed.mask.hide();
			win.show(config.params ? config.params : {});
			return true;
		}

		var win = swGetWnd(wnd, config);
		if (win && (win.isVisible() && config && (config.checkVisible == undefined || config.checkVisible===true)))
		{
			sw.swMsg.show(
			{
				buttons: Ext.Msg.OK,
				fn: function()
				{
					win.toFront();
				},
				icon: Ext.Msg.WARNING,
				title: langs('Сообщение'),
				msg: 'Окно "'+win.title+'" уже открыто.'
			});
			return false;
		}
		sw.Promed.mask.hide();
		if (win.showCount!=undefined && win.showCount == 0) { // Если первый запуск
			win.showCount++;
			if (isDebug()){
				log('Первый запуск формы ',win.id,' (',win,') с параметрами ', params);
			}
			win.loadDataLists(params); // то загружаем справочники
			return false;
		} else {
			win.show(params);
		}

	}
	// window[wnd.objectName] - само окно
	// sw.Promed[objectClass] - класс
	// sw.codeInfo.forms[objectName] - наименование и настройки
	if (typeof wnd !== 'object') {
		wnd = {objectName: wnd, objectClass: wnd};
	}

	// выводим окно ожидания (и заодно)
	// Маска поверх всех окон
	sw.Promed.mask.show();

	var result = null;
	var result_was_null = false;

	var params = (config && config.params && typeof config.params == 'object')?config.params:{};
	var curSwShowWndCount;

	try
	{
		if (checkWindowInExtJS6(wnd.objectClass)) {
			Ext6.require(WINDOWS_ALIAS[wnd.objectClass], function() {
				sw4.addToPerfLog({
					window: wnd.objectClass,
					type: 'afterfetch'
				});
				show();
			});
		} else {
			// Если класс окна не обнаружен, то загружаем и выполняем
			if (!sw.Promed[wnd.objectClass]) {

				sw.codeInfo.lastObjectName = wnd.objectName;
				sw.codeInfo.lastObjectClass = wnd.objectClass;
				if (sw.Promed.Actions.loadLastObjectCode) {
					sw.Promed.Actions.loadLastObjectCode.setHidden(false);
					sw.Promed.Actions.loadLastObjectCode.setText(langs('Обновить ') + wnd.objectName + ' ...');
				}
				// Отправляем запрос на получение измененного JS-файла
				loadJsCode(wnd, function() {
					// callback на чтение js-файла
					show();
				});
			}
			else {
				// Иначе - просто выполняем
				show();
			}
		}
	}
	catch(e)
	{
		if (IS_DEBUG==2)
			throw e;
		else {
			showFatalError(e, function() {show();});
		}
	}
	finally
	{
		//

	}
}

/**
 * Проверяет, что окно есть в списке окон ExtJS 6
 * @param wnd
 * @returns {boolean}
 */
function checkWindowInExtJS6(wnd) {
	if (
		WINDOWS_ALIAS.hasOwnProperty(wnd)
		&& (getRegionNick() == 'perm' || getRegionNick() == 'krym' || !wnd.inlist(['swSelectEvnStatusCauseWindow'])) // некоторые формы на других регионах пока нужны в старом виде
	) {
		return true;
	}
}

/**
 * Установка статуса событию
 * @param params
 */
function setEvnStatus(params) {
	Ext.Ajax.request({
		url: '/?c=Evn&m=updateEvnStatus',
		method: 'POST',
		callback: function(options, success, response) {
			if (typeof params.callback == 'function') {
				params.callback();
			}
		},
		params: {
			Evn_id: params.Evn_id,
			EvnClass_SysNick: params.EvnClass_SysNick,
			EvnStatus_SysNick: params.EvnStatus_SysNick,
			EvnStatusHistory_Cause: params.EvnStatusHistory_Cause
		}
	});
}

/**
 * Возвращает объект для открытия окна.
 *
 * @param {String|Object} wnd Класс окна объекта, по умолчанию оно же название окна.
 * @param {Function} callback Функция вызываемая при инициализации окна (замена старого callback1 и callback2).
 * @return {Object}
 */

function getWnd(wnd, config)
{
	if (checkWindowInExtJS6(wnd)) {
		// если уже есть выводим как есть
		var checkwin = Ext6.ComponentQuery.query('window[$className=' + WINDOWS_ALIAS[wnd] + ']')
		if (wnd == 'swPersonEmkWindowExt6') {
			// проверяем есть ли скрытое окно, если есть то возвращаем его
			for (var k in checkwin) {
				if (checkwin[k].hidden) {
					return checkwin[k];
				}
			}

			// иначе создаём новое, но не более трёх всего
			if (checkwin.length >= 3) {
				sw.swMsg.alert(langs('Ошибка'), 'Превышен лимит одновременно открытых ЭМК');
				return {
					show: function() {
						// заглушка
					},
					isVisible: function() {
						// заглушка
						return false;
					},
					hide: function() {
						// заглушка
					}
				};
			}
		} else if (checkwin[0]) {
			return checkwin[0];
		}

		sw4.addToPerfLog({
			window: wnd,
			type: 'beforefetch'
		});
	}

	if (typeof wnd !== 'object') {
		wnd = {objectName: wnd, objectClass: wnd};
	}

	/*if (getWndRoles()[wnd.objectClass] && !Ext.isEmpty(getWndRoles()[wnd.objectClass].view) && getWndRoles()[wnd.objectClass].view == false) {
		if(wnd.objectClass != 'swWorkPlaceMZSpecWindow')
			sw.swMsg.alert(langs('Ошибка'),langs('У вас нет разрешения для открытия данной формы.'));
		return {
			show: function() {
				// заглушка
			},
			isVisible: function () {
				// заглушка
				return false;
			},
			hide: function() {
				// заглушка
			}
		};
	}*/

	if (getGlobalOptions().blockFormList && wnd.objectClass.inlist(getGlobalOptions().blockFormList)) {
		var error = 'Функционал временно недоступен в связи с техническим сбоем. Приносим свои извинения за предоставленные неудобства.';
		if (getGlobalOptions().blockFormTime) {
			error += ' Приблизительное время устранения проблемы ' + getGlobalOptions().blockFormTime + ' минут.';
		}
		sw.swMsg.alert(langs('Ошибка'), error);
		return {
			show: function() {
				// заглушка
			},
			isVisible: function () {
				// заглушка
				return false;
			},
			hide: function() {
				// заглушка
			}
		};
	}

	var win = swGetWnd(wnd, {params_init: (config && config.params)?config.params:null});
	// TODO: а пока здесь тоже можно включить проверку на уже открытую форму, и не открывать ее, а активировать.
	if (win)
	{
		if (config && config.params && win.showCount!=undefined && win.showCount == 0) { // Если первый запуск
			win.showCount++;
			if (isDebug()){
				log('Первый запуск формы ',win.id,' (',win,') с параметрами ', config.params);
			}
			win.loadDataLists(config.params); // то загружаем справочники
		}
		return win;
	}
	else
	{
		return {
			wnd: wnd,
			callback: (config && config.callback)?config.callback:null,
			params_init: (config && config.params)?config.params:null,
			show: function (params, checkVisible)
			{
				var config = {};
				if (!checkVisible)
				{
					config.checkVisible = true;
				}
				else
				{
					config.checkVisible = checkVisible;
				}
				config.callback = this.callback;
				config.params_init = this.params_init;
				config.params = params;
				swShowWnd(this.wnd, config);
			},
			load: function (config)
			{
				if (checkWindowInExtJS6(this.wnd.objectClass)) {
					Ext6.require(WINDOWS_ALIAS[this.wnd.objectClass], function() {
						sw4.addToPerfLog({
							window: wnd,
							type: 'afterfetch'
						});
						if (config && typeof config.callback == "function") {
							config.callback(success);
						}
					});
				} else {
					loadJsCode(this.wnd, function(success) {
						if (config && typeof config.callback == "function") {
							config.callback(success);
						}
					});
				}
			},
			isVisible: function ()
			{
				var win = swGetWnd(wnd);
				if (win)
					return win.isVisible();
				else
					return false;
			},
			hide: function ()
			{
				if (swGetWnd(wnd)) {
					swGetWnd(wnd).hide();
				} else {
					log('Попытка скрыть окно, которое не создано', wnd);
				}
			}
		};
	}
}

/**
 * Возвращает объект для открытия окна. Окно создаётся новым экземпляром.
 *
 * @param {String|Object} wnd Класс окна объекта, по умолчанию оно же название окна.
 * @param {Function} callback Функция вызываемая при инициализации окна (замена старого callback1 и callback2).
 * @return {Object}
 */

function getNewWnd(wnd, config) {
	if (typeof sw.windowCounter != 'object') {
		sw.windowCounter = {};
	}

	// шаманим с параметрами, приводя их к виду "({objectName:'swEvnSectionEditWindow2', objectClass:'swEvnSectionEditWindow'},{params:{id:'EvnSectionEditWindow2'}})"
	if (typeof wnd !== 'object') {
		wnd = {objectName: wnd, objectClass: wnd};
	}

	if (typeof config !== 'object') {
		config = new Object();
	}

	if (typeof config.params !== 'object') {
		config.params = new Object();
	}

	if (!Ext.isEmpty(sw.windowCounter[wnd.objectClass])) {
		sw.windowCounter[wnd.objectClass]++;
	} else {
		sw.windowCounter[wnd.objectClass] = 0;
	}

	wnd.objectName = wnd.objectClass + sw.windowCounter[wnd.objectClass];
	config.params.id = wnd.objectName;

	return getWnd(wnd, config);
}

function showFatalError(e, cb)
{
	var err = '';
	var btn = Ext.Msg.YESNO;
	sw.Promed.mask.hide();
	var text = e;
	var stack = '';
	if (e.message) {
		text = e.message;
		if (e.fileName) {
			text += '<br>file: ' + e.fileName;
		}
		if (e.lineNumber) {
			text += '<br>line: ' + e.lineNumber;
		}
		if (e.columnNumber) {
			text += '<br>column: ' + e.columnNumber;
		}
		if (e.stack) {
			stack = '<br><br>stack:<br>' + e.stack;
		}
	}
	var link = text + stack;
	if (IS_DEBUG)
	{
		err = '<br/>Повторить попытку обновления функционала объекта <a href="#" title="При успешной попытке нужно будет повторить открытие формы">?</a> <br/>';
		sw.codeInfo.lastErr = e;
		link = '<a href="#" onClick="javascript: throw sw.codeInfo.lastErr;">'+text+'</a>' + stack;
	}
	else
	{
		err = '<br/>Пожалуйста, сообщите об ошибке разработчикам.';
		btn = Ext.Msg.OK;
	}
	sw.swMsg.show({
		buttons: btn,
		fn: function(buttonId, text, obj)
		{

			if ('yes' == buttonId)
			{
				if (sw.codeInfo) {
					loadJsCode({objectName: sw.codeInfo.lastObjectName, objectClass: sw.codeInfo.lastObjectClass}, cb);
				};
			}
		},
		icon: Ext.Msg.ERROR,
		msg: 'При инициализации объекта "'+sw.codeInfo.lastObjectName+'"<br/>(класс "'+sw.codeInfo.lastObjectClass+'") произошла ошибка:<br/><b>'+link+'</b>'+err,
		title: langs('Ошибка') + ' ' + e.name
	});
}

/**
 * Возвращает окно с заданным именем.
 *
 * Фабричный метод. Если окно есть в глобальной области видимости, то просто
 * возвращает его. Иначе создает и возвращает.
 *
 * @param {String} window_name Название окна.
 * @param {Function} callback1 Функция вызывающаяся если окно еще не было создано.
 * @param {Function} callback2 Функция вызывающаяся если окно уже было создано.
 * @return {Object}
 */
 // TODO: Функция не нужна. Надо проверить и удалить
function swGetWindowWithParams(window_name, params, callback1, callback2)
{

	var result = null;
	var result_was_null = false;

	if (window[window_name] == null)
	{
		if (sw.Promed[window_name] != undefined)
		{
			result_was_null = true;
			if (params)
				window[window_name] = new sw.Promed[window_name](params);
			else
				window[window_name] = new sw.Promed[window_name]();
		}
	}

	result = window[window_name];
	if (result_was_null == true)
	{
		main_center_panel.add(result);
		main_center_panel.doLayout();

		if (callback1 != undefined && callback1 != null)
		{
			callback1(result);
		}
	}
	else
	{
		if (callback2 != undefined && callback2 != null)
		{
			callback2(result);
		}
	}

	return result;
}

function initGlobalStores() {
	// Создаем нужные stores
	swLpuFilialGlobalStore = new Ext.data.Store({
		table: 'LpuFilial',
		autoLoad: false,
		reader: new Ext.data.JsonReader({
			id: 'LpuFilial_id'
		}, [
			{name: 'LpuFilial_Code', mapping: 'LpuFilial_Code'},
			{name: 'LpuFilial_id', mapping: 'LpuFilial_id'},
			{name: 'LpuFilial_Name', mapping: 'LpuFilial_Name'},
			{name: 'LpuFilial_begDate', mapping: 'LpuFilial_begDate'},
			{name: 'LpuFilial_endDate', mapping: 'LpuFilial_endDate'},
			{name: 'Lpu_id', mapping: 'Lpu_id'},
			{name: 'Lpu_Name', mapping: 'Lpu_Name'},
			{name: 'sortID', mapping: 'sortID'}
		]),
		url: '/?c=Common&m=loadLpuFilialList'
	});

	swLpuBuildingGlobalStore = new Ext.data.Store({
		table: 'LpuBuilding',
		autoLoad: false,
		reader: new Ext.data.JsonReader({
			id: 'LpuBuilding_id'
		}, [
			{name: 'LpuBuilding_Code', mapping: 'LpuBuilding_Code'},
			{name: 'LpuBuilding_id', mapping: 'LpuBuilding_id'},
			{name: 'LpuBuilding_Name', mapping: 'LpuBuilding_Name'},
			{name: 'Lpu_id', mapping: 'Lpu_id'},
			{name: 'Lpu_Name', mapping: 'Lpu_Name'},
			{name: 'LpuFilial_id', mapping: 'LpuFilial_id'},
			{name: 'LpuFilial_Name', mapping: 'LpuFilial_Name'},
			{name: 'sortID', mapping: 'sortID'},
			{name: 'LpuBuilding_begDate', mapping: 'LpuBuilding_begDate'},
			{name: 'LpuBuilding_endDate', mapping: 'LpuBuilding_endDate'}
		]),
		url: '/?c=Common&m=loadLpuBuildingList'
	});

	swLpuSectionGlobalStore = new Ext.data.Store({
		autoLoad: false,
		table: 'LpuSection',
		reader: new Ext.data.JsonReader({
			id: 'LpuSection_id'
		}, [
			{name: 'LpuSection_Code', mapping: 'LpuSection_Code'},
			{name: 'LpuSectionCode_id', mapping: 'LpuSectionCode_id'},
			{name: 'LpuSection_id', mapping: 'LpuSection_id'},
			{name: 'LpuSection_pid', mapping: 'LpuSection_pid'},
			{name: 'LpuSectionAge_id', mapping: 'LpuSectionAge_id'},
			{name: 'LpuSection_Class', mapping: 'LpuSection_Class'},
			{name: 'LpuSection_Name', mapping: 'LpuSection_Name'},
			{name: 'LpuSectionProfile_id', mapping: 'LpuSectionProfile_id'},
			{name: 'LpuSectionProfile_Code', mapping: 'LpuSectionProfile_Code'},
			{name: 'LpuSectionProfile_Name', mapping: 'LpuSectionProfile_Name'},
			{name: 'LpuSectionProfile_SysNick', mapping: 'LpuSectionProfile_SysNick'},
			{name: 'LpuSectionBedProfile_id', mapping: 'LpuSectionBedProfile_id'},
			{name: 'LpuSectionBedProfile_Code', mapping: 'LpuSectionBedProfile_Code'},
			{name: 'LpuSectionBedProfile_Name', mapping: 'LpuSectionBedProfile_Name'},
			{name: 'Lpu_id', mapping: 'Lpu_id'},
			{name: 'Lpu_Name', mapping: 'Lpu_Name'},
			{name: 'LpuBuilding_id', mapping: 'LpuBuilding_id'},
			{name: 'LpuUnit_id', mapping: 'LpuUnit_id'},
			{name: 'LpuUnitSet_id', mapping: 'LpuUnitSet_id'},
			{name: 'LpuUnitSet_Code', mapping: 'LpuUnitSet_Code'},
			{name: 'LpuUnitType_id', mapping: 'LpuUnitType_id'},
			{name: 'LpuUnitType_Code', mapping: 'LpuUnitType_Code'},
			{name: 'LpuUnitType_SysNick', mapping: 'LpuUnitType_SysNick'},
			{name: 'LpuSection_disDate', mapping: 'LpuSection_disDate'},
			{name: 'LpuSection_setDate', mapping: 'LpuSection_setDate'},
			{name: 'LpuSection_IsHTMedicalCare', mapping: 'LpuSection_IsHTMedicalCare'},
			{name: 'LpuSectionServiceList', mapping: 'LpuSectionServiceList', type: 'string'},
			{name: 'LpuSectionLpuSectionProfileList', mapping: 'LpuSectionLpuSectionProfileList', type: 'string'},
			{name: 'MedicalCareKind_id', mapping: 'MedicalCareKind_id'},
			{name: 'MedicalCareKind_Code', mapping: 'MedicalCareKind_Code'},
			{name: 'listType', mapping: 'listType', type: 'string'},
			{name: 'sortID', mapping: 'sortID'},
			{name: 'LpuPeriodDLO_Code', mapping: 'LpuPeriodDLO_Code'},
			{name: 'LpuPeriodDLO_begDate', mapping: 'LpuPeriodDLO_begDate'},
			{name: 'LpuPeriodDLO_endDate', mapping: 'LpuPeriodDLO_endDate'}
		]),
		url: C_LPUSECTION_LIST
	});

	swLpuSectionWardGlobalStore = new Ext.data.Store({
		autoLoad: false,
		reader: new Ext.data.JsonReader({
			id: 'LpuSectionWard_id'
		}, [
			{name: 'LpuSectionWard_id', mapping: 'LpuSectionWard_id'},
			{name: 'LpuSection_id', mapping: 'LpuSection_id'},
			{name: 'Lpu_id', mapping: 'Lpu_id'},
			{name: 'Lpu_Name', mapping: 'Lpu_Name'},
			{name: 'Sex_id', mapping: 'Sex_id'},
			{name: 'LpuSectionWard_Name', mapping: 'LpuSectionWard_Name'},
			{name: 'LpuSectionWard_disDate', mapping: 'LpuSectionWard_disDate'},
			{name: 'LpuSectionWard_setDate', mapping: 'LpuSectionWard_setDate'},
			{name: 'sortID', mapping: 'sortID'}
		]),
		url: C_LPUSECTIONWARD_LIST
	});

	swFederalKladrGlobalStore = new Ext.data.Store({
		autoLoad: false,
		reader: new Ext.data.JsonReader({
			id: 'KLArea_id'
		}, [
			{name: 'KLAdr_Code', mapping: 'KLAdr_Code'},
			{name: 'KLArea_id', mapping: 'KLArea_id'}
		]),
		url: C_KLADR_LIST
	});

	swLpuUnitGlobalStore = new Ext.data.Store({
		autoLoad: false,
		reader: new Ext.data.JsonReader({
			id: 'LpuUnit_id'
		}, [
			{name: 'LpuUnit_id', mapping: 'LpuUnit_id'},
			{name: 'Lpu_id', mapping: 'Lpu_id'},
			{name: 'Lpu_Name', mapping: 'Lpu_Name'},
			{name: 'LpuUnitType_id', mapping: 'LpuUnitType_id'},
			{name: 'LpuBuilding_id', mapping: 'LpuBuilding_id'},
			{name: 'LpuUnit_Code', mapping: 'LpuUnit_Code'},
			{name: 'LpuUnit_Name', mapping: 'LpuUnit_Name'},
			{name: 'LpuUnit_IsEnabled', mapping: 'LpuUnit_IsEnabled'},
			{name: 'sortID', mapping: 'sortID'}
		]),
		url: '/?c=Common&m=loadLpuUnitList'
	});

	swMedServiceGlobalStore = new Ext.data.Store({
		autoLoad: false,
		reader: new Ext.data.JsonReader({
			id: 'MedService_id'
		}, [
			{name: 'MedService_id', mapping: 'MedService_id'},
			{name: 'Lpu_id', mapping: 'Lpu_id'},
			{name: 'Lpu_Name', mapping: 'Lpu_Name'},
			{name: 'LpuBuilding_id', mapping: 'LpuBuilding_id'},
			{name: 'LpuSection_id', mapping: 'LpuSection_id'},
			{name: 'LpuUnit_id', mapping: 'LpuUnit_id'},
			{name: 'LpuUnitType_id', mapping: 'LpuUnitType_id'},
			{name: 'MedService_begDT', mapping: 'MedService_begDT'},
			{name: 'MedService_endDT', mapping: 'MedService_endDT'},
			{name: 'MedService_Name', mapping: 'MedService_Name'},
			{name: 'MedService_Nick', mapping: 'MedService_Nick'},
			{name: 'MedServiceType_id', mapping: 'MedServiceType_id'},
			{name: 'MedServiceType_SysNick', mapping: 'MedServiceType_SysNick'},
			{name: 'sortID', mapping: 'sortID'}
		]),
		url: C_MEDSERVICE_LIST
	});

	swResultDeseaseLeaveTypeGlobalStore = new Ext.data.Store({
		autoLoad: false,
		reader: new Ext.data.JsonReader({
			id: 'ResultDeseaseLeaveType_id'
		}, [
			{name: 'ResultDeseaseLeaveType_id', mapping: 'ResultDeseaseLeaveType_id'},
			{name: 'ResultDeseaseType_id', mapping: 'ResultDeseaseType_id'},
			{name: 'LeaveType_id', mapping: 'LeaveType_id'}
		]),
		url: '/?c=Common&m=loadResultDeseaseLeaveTypeList'
	});

	swTreatmentClassServiceTypeGlobalStore = new Ext.data.Store({
		autoLoad: false,
		reader: new Ext.data.JsonReader({
			id: 'TreatmentClassServiceType_id'
		}, [
			{name: 'TreatmentClassServiceType_id', mapping: 'TreatmentClassServiceType_id'},
			{name: 'TreatmentClass_id', mapping: 'TreatmentClass_id'},
			{name: 'ServiceType_id', mapping: 'ServiceType_id'}
		]),
		url: '/?c=Common&m=loadTreatmentClassServiceTypeList'
	});

	swTreatmentClassVizitTypeGlobalStore = new Ext.data.Store({
		autoLoad: false,
		reader: new Ext.data.JsonReader({
			id: 'TreatmentClassVizitType_id'
		}, [
			{name: 'TreatmentClassVizitType_id', mapping: 'TreatmentClassVizitType_id'},
			{name: 'TreatmentClass_id', mapping: 'TreatmentClass_id'},
			{name: 'VizitType_id', mapping: 'VizitType_id'}
		]),
		url: '/?c=Common&m=loadTreatmentClassVizitTypeList'
	});

	swMedicalCareKindLpuSectionProfileGlobalStore = new Ext.data.Store({
		autoLoad: false,
		reader: new Ext.data.JsonReader({
			id: 'MedicalCareKindLpuSectionProfile_id'
		}, [
			{name: 'MedicalCareKindLpuSectionProfile_id', mapping: 'MedicalCareKindLpuSectionProfile_id'},
			{name: 'MedicalCareKind_id', mapping: 'MedicalCareKind_id'},
			{name: 'LpuSectionProfile_id', mapping: 'LpuSectionProfile_id'}
		]),
		url: '/?c=Common&m=loadMedicalCareKindLpuSectionProfileList'
	});

	swMedSpecLinkGlobalStore = new Ext.data.Store({
		autoLoad: false,
		reader: new Ext.data.JsonReader({
			id: 'MedSpecLink_id'
		}, [
			{name: 'MedSpecLink_id', mapping: 'MedSpecLink_id'},
			{name: 'MedSpec_id', mapping: 'MedSpec_id'},
			{name: 'MedicalCareKind_id', mapping: 'MedicalCareKind_id'},
			{name: 'MedicalCareType_id', mapping: 'MedicalCareType_id'}
		]),
		url: '/?c=Common&m=loadMedSpecLinkList'
	});

	swMedStaffFactGlobalStore = new Ext.data.Store({
		autoLoad: false,
		reader: new Ext.data.JsonReader({
			id: 'MedStaffFactKey_id'
		}, [
			{name: 'MedStaffFactKey_id', mapping: 'MedStaffFactKey_id'},
			{name: 'MedPersonal_DloCode', mapping: 'MedPersonal_DloCode'},
			{name: 'MedPersonal_Fio', mapping: 'MedPersonal_Fio'},
			{name: 'MedPersonal_Fin', mapping: 'MedPersonal_Fin'},
			{name: 'MedPersonal_id', mapping: 'MedPersonal_id'},
			{name: 'Person_id', mapping: 'Person_id'},
			{name: 'Person_Snils', mapping: 'Person_Snils'},
			{name: 'MedPersonal_TabCode', mapping: 'MedPersonal_TabCode'},
			{name: 'MedStaffFact_id', mapping: 'MedStaffFact_id'},
			{name: 'Lpu_id', mapping: 'Lpu_id'},
			{name: 'Lpu_Name', mapping: 'Lpu_Name'},
			{name: 'LpuBuilding_id', mapping: 'LpuBuilding_id'},
			{name: 'LpuBuildingType_id', mapping: 'LpuBuildingType_id'},
			{name: 'LpuUnit_id', mapping: 'LpuUnit_id'},
			{name: 'LpuUnitSet_id', mapping: 'LpuUnitSet_id'},
			{name: 'LpuSection_id', mapping: 'LpuSection_id'},
			{name: 'LpuSection_pid', mapping: 'LpuSection_pid'},
			{name: 'LpuSectionAge_id', mapping: 'LpuSectionAge_id'},
			{name: 'LpuSection_Name', mapping: 'LpuSection_Name'},
			{name: 'LpuSectionProfile_Code', mapping: 'LpuSectionProfile_Code'},
			{name: 'LpuSectionProfile_SysNick', mapping: 'LpuSectionProfile_SysNick'},
			{name: 'LpuSectionProfile_Name', mapping: 'LpuSectionProfile_Name'},
			{name: 'LpuUnitType_Code', mapping: 'LpuUnitType_Code'},
			{name: 'LpuUnitType_SysNick', mapping: 'LpuUnitType_SysNick'},
			{name: 'LpuSection_disDate', mapping: 'LpuSection_disDate'},
			{name: 'LpuSection_setDate', mapping: 'LpuSection_setDate'},
			{name: 'WorkData_begDate', mapping: 'WorkData_begDate'},
			{name: 'WorkData_endDate', mapping: 'WorkData_endDate'},
			{name: 'WorkData_dloBegDate', mapping: 'WorkData_dloBegDate'},
			{name: 'WorkData_dloEndDate', mapping: 'WorkData_dloEndDate'},
			{name: 'PostKind_id', mapping: 'PostKind_id'},
			{name: 'PostMed_id', mapping: 'PostMed_id'},
			{name: 'PostMed_Code', mapping: 'PostMed_Code'},
			{name: 'PostMed_Name', mapping: 'PostMed_Name'},
			{name: 'frmpEntry_id', mapping: 'frmpEntry_id'},
			{name: 'SortVal', mapping: 'SortVal'},
			{name: 'MedSpecOms_id', mapping: 'MedSpecOms_id'},
			{name: 'MedSpecOms_Code', mapping: 'MedSpecOms_Code'},
			{name: 'FedMedSpec_id', mapping: 'FedMedSpec_id'},
			{name: 'FedMedSpec_Code', mapping: 'FedMedSpec_Code'},
			{name: 'FedMedSpecParent_Code', mapping: 'FedMedSpecParent_Code'},
			{name: 'Post_IsPrimaryHealthCare', mapping: 'Post_IsPrimaryHealthCare', type: 'int'},
			{name: 'MedStaffFactCache_IsDisableInDoc', mapping: 'MedStaffFactCache_IsDisableInDoc'},
			{name: 'MedStaffFact_Stavka', mapping: 'MedStaffFact_Stavka', type: 'string'},
			{name: 'LpuRegion_List', mapping: 'LpuRegion_List'},
			{name: 'LpuRegion_MainList', mapping: 'LpuRegion_MainList'},
			{name: 'LpuRegion_DatesList', mapping: 'LpuRegion_DatesList'},
			{name: 'MedStaffFactCache_IsHomeVisit', mapping: 'MedStaffFactCache_IsHomeVisit'},
			{name: 'LpuSectionProfile_msfid', mapping: 'LpuSectionProfile_msfid'},
			{name: 'sortID', mapping: 'sortID'},
			{name: 'MedPost_pid', mapping: 'MedPost_pid'},
			{name: 'LpuPeriodDLO_Code', mapping: 'LpuPeriodDLO_Code'},
			{name: 'LpuPeriodDLO_begDate', mapping: 'LpuPeriodDLO_begDate'},
			{name: 'LpuPeriodDLO_endDate', mapping: 'LpuPeriodDLO_endDate'},
			{name: 'MedPersonalPost_Code', mapping: 'MedPersonalPost_Code'}
		]),
		url: C_MEDPERSONAL_LIST
	});

	swLpuSectionProfileMedSpecOms = new Ext.data.Store({
		autoLoad: false,
		reader: new Ext.data.JsonReader({
			id: 'LpuSectionProfileMedSpecOms_id'
		}, [
			{name: 'Server_id', mapping: 'Server_id'},
			{name: 'LpuSectionProfileMedSpecOms_id', mapping: 'LpuSectionProfileMedSpecOms_id'},
			{name: 'LpuSectionProfile_id', mapping: 'LpuSectionProfile_id'},
			{name: 'MedSpecOms_id', mapping: 'MedSpecOms_id'},
			{name: 'LpuSectionProfileMedSpecOms_begDate', mapping: 'LpuSectionProfileMedSpecOms_begDate'},
			{name: 'LpuSectionProfileMedSpecOms_endDate', mapping: 'LpuSectionProfileMedSpecOms_endDate'},
			{name: 'Lpu_id', mapping: 'Lpu_id'}
		]),
		url: '/?c=Common&m=loadLpuSectionProfileMedSpecOms'

	});

	swUslugaComplexMedSpec = new Ext.data.Store({
		autoLoad: false,
		reader: new Ext.data.JsonReader({
			id: 'UslugaComplexMedSpec_id'
		}, [
			{name: 'UslugaComplexMedSpec_id', mapping: 'UslugaComplexMedSpec_id'},
			{name: 'UslugaComplex_id', mapping: 'UslugaComplex_id'},
			{name: 'MedSpecOms_id', mapping: 'MedSpecOms_id'},
			{name: 'DispClass_id', mapping: 'DispClass_id'},
			{name: 'UslugaComplexMedSpec_begDate', mapping: 'UslugaComplexMedSpec_begDate'},
			{name: 'UslugaComplexMedSpec_endDate', mapping: 'UslugaComplexMedSpec_endDate'}
		]),
		url: '/?c=Common&m=loadUslugaComplexMedSpec'
	});

	swLpuDispContractStore = new Ext.data.Store({
		autoLoad: false,
		reader: new Ext.data.JsonReader({
			id: 'LpuDispContract_id'
		}, [
			{name: 'LpuDispContract_id', mapping: 'LpuDispContract_id'},
			{name: 'Lpu_oid', mapping: 'Lpu_oid'},
			{name: 'LpuDispContract_setDate', mapping: 'LpuDispContract_setDate'},
			{name: 'LpuDispContract_disDate', mapping: 'LpuDispContract_disDate'},
			{name: 'LpuSection_id', mapping: 'LpuSection_id'},
			{name: 'LpuSectionProfile_id', mapping: 'LpuSectionProfile_id'},
			{name: 'LpuSectionProfile_Code', mapping: 'LpuSectionProfile_Code'}
		])
	});

	sw.Promed.globalStores = {
		'LpuFilial': swLpuFilialGlobalStore,
		'LpuBuilding': swLpuBuildingGlobalStore,
		'LpuSection': swLpuSectionGlobalStore,
		'LpuSectionWard': swLpuSectionWardGlobalStore,
		'FederalKladr': swFederalKladrGlobalStore,
		'LpuUnit': swLpuUnitGlobalStore,
		'MedService': swMedServiceGlobalStore,
		'MedStaffFact': swMedStaffFactGlobalStore,
		'ResultDeseaseLeaveType':swResultDeseaseLeaveTypeGlobalStore,
		'TreatmentClassServiceType':swTreatmentClassServiceTypeGlobalStore,
		'TreatmentClassVizitType':swTreatmentClassVizitTypeGlobalStore,
		'MedicalCareKindLpuSectionProfile':swMedicalCareKindLpuSectionProfileGlobalStore,
		'MedSpecLink':swMedSpecLinkGlobalStore
	};

	if (getRegionNick() === 'pskov')
	{
		sw.Promed.globalStores['LpuSectionProfileMedSpecOms'] = swLpuSectionProfileMedSpecOms;
		sw.Promed.globalStores['UslugaComplexMedSpec'] = swUslugaComplexMedSpec ;
	}

	if (getRegionNick() == 'ekb')
	{
		sw.Promed.globalStores['LpuDispContract'] = swLpuDispContractStore;
	}
}

/**
 * Загрузка глобальных списков
 */
function loadGlobalStores(config) {
	initGlobalStores();
	var stores = sw.Promed.globalStores;
	var o = [];
	// Проверяем наличие у компонентов Store
		// Пробегаемся по компонентам и собираем запросы
		for(i in stores) {
			o.push(i);
		}
		// Отправляем запрос на получение данных по всем сторе, получаем обратно ответ
		Ext.Ajax.request({
			url: '/?c=Common&m=loadGlobalStores',
			params: {'stores': Ext.util.JSON.encode(o)},
			failure: function(response, options) {
				if (config && config.callback && typeof config.callback == 'function') {
					config.callback(response, false);
					return;
				}
			},
			success: function(response, options) {
				var result = null;
				if (response && typeof response.responseText == 'string' && response.responseText.length > 0) {
					result = Ext.util.JSON.decode(response.responseText);
				}
				if (result) {
					// Разбираем ответ по сторе
					for(i in stores) {
						if (!Ext.isEmpty(result[i])){
							stores[i].loadData(result[i], false);
						} else {
							log(langs('Store для ') + i +langs(' не загружена'));
						}
					}
				}

				// прогрузим глобальные сторе 4-го экста
				sw4.loadGlobalStores(result);

				// Формируем в менеджере сторе информацию о загруженных данных
				if (config && config.callback && typeof config.callback == 'function') {
					config.callback(result, true);
				}
			}
		});
}


/**
 * Загрузка списка филиалов в область глобальной видимости
 */
function loadLpuFilialGlobalStore() {
	swLpuFilialGlobalStore = new Ext.data.Store({
		autoLoad: true,
		reader: new Ext.data.JsonReader({
			id: 'LpuFilial_id'
		}, [
			{name: 'LpuFilial_Code', mapping: 'LpuFilial_Code'},
			{name: 'LpuFilial_id', mapping: 'LpuFilial_id'},
			{name: 'LpuFilial_Name', mapping: 'LpuFilial_Name'},
			{name: 'LpuFilial_begDate', mapping: 'LpuFilial_begDate'},
			{name: 'LpuFilial_endDate', mapping: 'LpuFilial_endDate'},
			{name: 'Lpu_id', mapping: 'Lpu_id'},
			{name: 'Lpu_Name', mapping: 'Lpu_Name'},
			{name: 'sortID', mapping: 'sortID'}
		]),
		url: '/?c=Common&m=loadLpuFilialList'
	});
}

/**
 * Загрузка списка подразделений в область глобальной видимости
 */
function loadLpuBuildingGlobalStore() {
	swLpuBuildingGlobalStore = new Ext.data.Store({
		autoLoad: true,
		reader: new Ext.data.JsonReader({
			id: 'LpuBuilding_id'
		}, [
			{name: 'LpuBuilding_Code', mapping: 'LpuBuilding_Code'},
			{name: 'LpuBuilding_id', mapping: 'LpuBuilding_id'},
			{name: 'LpuBuilding_Name', mapping: 'LpuBuilding_Name'},
			{name: 'Lpu_id', mapping: 'Lpu_id'},
			{name: 'Lpu_Name', mapping: 'Lpu_Name'},
			{name: 'LpuFilial_id', mapping: 'LpuFilial_id'},
			{name: 'LpuFilial_Name', mapping: 'LpuFilial_Name'},
			{name: 'sortID', mapping: 'sortID'},
			{name: 'LpuBuilding_begDate', mapping: 'LpuBuilding_begDate'},
			{name: 'LpuBuilding_endDate', mapping: 'LpuBuilding_endDate'}
		]),
		url: '/?c=Common&m=loadLpuBuildingList'
	});
}
function doLoadFederalKladrGlobalStore(iteration){
	if ( !iteration )
		var iteration = 1;
	swFederalKladrGlobalStore.load({
		callback: function(r, o, success) {
			if ( success === false ) {
				if ( iteration >= 3 ) {
					//setPromedInfo('Справочник палат не загружен или пуст', 'lpusectionward-info');
					//sw.swMsg.alert('Ошибка', 'Невозможно загрузить глобальный справочник палат!');
				}
				else {
					doLoadFederalKladrGlobalStore(iteration + 1);
				}
			}
		}
	});
}

function doLoadLpuSectionGlobalStore(iteration) {
	if ( !iteration )
		var iteration = 1;
	swLpuSectionGlobalStore.load({
		callback: function(r, o, success) {
			if ( success === false || !r || r.length == 0 )
			{
				if ( iteration >= 3 )
					setPromedInfo(langs('Справочник отделений не загружен или пуст'), 'lpusection-info');
					//sw.swMsg.alert('Ошибка','Невозможно загрузить глобальный справочник отделений!');
				else
					doLoadLpuSectionGlobalStore(iteration + 1);
			}else {
				setPromedInfo('', 'lpusection-info');
			}
		}
	});
}

function doLoadLpuSectionWardGlobalStore(iteration) {
	if ( !iteration )
		var iteration = 1;
	swLpuSectionWardGlobalStore.load({
		callback: function(r, o, success) {
			if ( success === false ) {
				if ( iteration >= 3 ) {
					//setPromedInfo('Справочник палат не загружен или пуст', 'lpusectionward-info');
					//sw.swMsg.alert('Ошибка', 'Невозможно загрузить глобальный справочник палат!');
				}
				else {
					doLoadLpuSectionWardGlobalStore(iteration + 1);
				}
			}
		}
	});
}
/**
 * Загрузка списка городов федерального значения в область глобальной видимости
 */
function loadFederalKladrGlobalStore(){
	swFederalKladrGlobalStore = new Ext.data.Store({
		autoLoad: false,
		reader: new Ext.data.JsonReader({
			id: 'KLArea_id'
		}, [
			{name: 'KLAdr_Code', mapping: 'KLAdr_Code'},
			{name: 'KLArea_id', mapping: 'KLArea_id'}
		]),
		url: C_KLADR_LIST
	});
	doLoadFederalKladrGlobalStore();
}
/**
 * Загрузка списка отделений в область глобальной видимости
 */
function loadLpuSectionGlobalStore() {
	swLpuSectionGlobalStore = new Ext.data.Store({
		autoLoad: false,
		reader: new Ext.data.JsonReader({
			id: 'LpuSection_id'
		}, [
			{name: 'LpuSection_Code', mapping: 'LpuSection_Code'},
			{name: 'LpuSectionCode_id', mapping: 'LpuSectionCode_id'},
			{name: 'LpuSection_id', mapping: 'LpuSection_id'},
			{name: 'LpuSection_pid', mapping: 'LpuSection_pid'},
			{name: 'LpuSectionAge_id', mapping: 'LpuSectionAge_id'},
			{name: 'LpuSection_Class', mapping: 'LpuSection_Class'},
			{name: 'LpuSection_Name', mapping: 'LpuSection_Name'},
			{name: 'LpuSectionProfile_id', mapping: 'LpuSectionProfile_id'},
			{name: 'LpuSectionProfile_Code', mapping: 'LpuSectionProfile_Code'},
			{name: 'LpuSectionProfile_Name', mapping: 'LpuSectionProfile_Name'},
			{name: 'LpuSectionProfile_SysNick', mapping: 'LpuSectionProfile_SysNick'},
			{name: 'Lpu_id', mapping: 'Lpu_id'},
			{name: 'LpuBuilding_id', mapping: 'LpuBuilding_id'},
			{name: 'LpuUnit_id', mapping: 'LpuUnit_id'},
			{name: 'LpuUnitSet_id', mapping: 'LpuUnitSet_id'},
			{name: 'LpuUnitSet_Code', mapping: 'LpuUnitSet_Code'},
			{name: 'LpuUnitType_id', mapping: 'LpuUnitType_id'},
			{name: 'LpuUnitType_Code', mapping: 'LpuUnitType_Code'},
			{name: 'LpuUnitType_SysNick', mapping: 'LpuUnitType_SysNick'},
			{name: 'LpuSection_disDate', mapping: 'LpuSection_disDate'},
			{name: 'LpuSection_setDate', mapping: 'LpuSection_setDate'},
			{name: 'LpuSection_IsHTMedicalCare', mapping: 'LpuSection_IsHTMedicalCare'},
			{name: 'LpuSectionServiceList', mapping: 'LpuSectionServiceList', type: 'string'},
			{name: 'MedicalCareKind_id', mapping: 'MedicalCareKind_id'},
			{name: 'MedicalCareKind_Code', mapping: 'MedicalCareKind_Code'},
			{name: 'LpuPeriodDLO_Code', mapping: 'LpuPeriodDLO_Code'},
			{name: 'LpuPeriodDLO_begDate', mapping: 'LpuPeriodDLO_begDate'},
			{name: 'LpuPeriodDLO_endDate', mapping: 'LpuPeriodDLO_endDate'}
		]),
		url: C_LPUSECTION_LIST
	});
	doLoadLpuSectionGlobalStore();
}


/**
 * Загрузка списка палат в область глобальной видимости
 */
function loadLpuSectionWardGlobalStore() {
	swLpuSectionWardGlobalStore = new Ext.data.Store({
		autoLoad: false,
		reader: new Ext.data.JsonReader({
			id: 'LpuSectionWard_id'
		}, [
			{name: 'LpuSectionWard_id', mapping: 'LpuSectionWard_id'},
			{name: 'LpuSection_id', mapping: 'LpuSection_id'},
			{name: 'Sex_id', mapping: 'Sex_id'},
			{name: 'LpuSectionWard_Name', mapping: 'LpuSectionWard_Name'},
			{name: 'LpuSectionWard_disDate', mapping: 'LpuSectionWard_disDate'},
			{name: 'LpuSectionWard_setDate', mapping: 'LpuSectionWard_setDate'}
		]),
		url: C_LPUSECTIONWARD_LIST
	});

	doLoadLpuSectionWardGlobalStore();
}


/**
 * Загрузка списка подразделений в область глобальной видимости
 */
function loadLpuUnitGlobalStore() {
	swLpuUnitGlobalStore = new Ext.data.Store({
		autoLoad: true,
		reader: new Ext.data.JsonReader({
			id: 'LpuUnit_id'
		}, [
			{name: 'LpuUnit_id', mapping: 'LpuUnit_id'},
			{name: 'LpuUnitType_id', mapping: 'LpuUnitType_id'},
			{name: 'LpuBuilding_id', mapping: 'LpuBuilding_id'},
			{name: 'LpuUnit_Code', mapping: 'LpuUnit_Code'},
			{name: 'LpuUnit_Name', mapping: 'LpuUnit_Name'},
			{name: 'LpuUnit_IsEnabled', mapping: 'LpuUnit_IsEnabled'}
		]),
		url: '/?c=Common&m=loadLpuUnitList'
	});
}

function doLoadMedStaffFactGlobalStore(iteration) {
	if ( !iteration )
		var iteration = 1;
	swMedStaffFactGlobalStore.load({
		params: {ignoreDisableInDocParam: 1, mode: 'all'},
		callback: function(r, o, success) {
			if ( success === false || !r || r.length == 0 )
			{
				if ( iteration >= 3 ) {
					setPromedInfo(langs('Справочник врачей не загружен или пуст'), 'medpersonal-info');
					//sw.swMsg.alert('Ошибка','Невозможно загрузить глобальный справочник врачей!');
				}
				else
					doLoadMedStaffFactGlobalStore(iteration + 1);
			} else {
				setPromedInfo('', 'medpersonal-info');
			}
		}
	});
}
function doLoadResultDeseaseLeaveTypeGlobalStore(iteration) {
	if ( !iteration )
		var iteration = 1;
	swResultDeseaseLeaveTypeGlobalStore.load({
		callback: function(r, o, success) {
			if ( success === false || !r || r.length == 0 )
			{
				if ( iteration >= 3 ) {
					setPromedInfo(langs('Справочник не загружен или пуст'), '');
					//sw.swMsg.alert('Ошибка','Невозможно загрузить глобальный справочник врачей!');
				}
				else
					doLoadResultDeseaseLeaveTypeGlobalStore(iteration + 1);
			} else {
				setPromedInfo('', 'o');
			}
		}
	});
}

/**
 * Загрузка списка врачей в область глобальной видимости
 */
function loadMedStaffFactGlobalStore() {
	swMedStaffFactGlobalStore = new Ext.data.Store({
		autoLoad: false,
		reader: new Ext.data.JsonReader({
			id: 'MedStaffFact_id'
		}, [
			{name: 'MedPersonal_DloCode', mapping: 'MedPersonal_DloCode'},
			{name: 'MedPersonal_Fio', mapping: 'MedPersonal_Fio'},
			{name: 'MedPersonal_Fin', mapping: 'MedPersonal_Fin'},
			{name: 'MedPersonal_id', mapping: 'MedPersonal_id'},
			{name: 'Person_id', mapping: 'Person_id'},
			{name: 'MedPersonal_TabCode', mapping: 'MedPersonal_TabCode'},
			{name: 'MedStaffFact_id', mapping: 'MedStaffFact_id'},
			{name: 'Lpu_id', mapping: 'Lpu_id'},
			{name: 'LpuBuilding_id', mapping: 'LpuBuilding_id'},
			{name: 'LpuBuildingType_id', mapping: 'LpuBuildingType_id'},
			{name: 'LpuUnit_id', mapping: 'LpuUnit_id'},
			{name: 'LpuUnitSet_id', mapping: 'LpuUnitSet_id'},
			{name: 'LpuSection_id', mapping: 'LpuSection_id'},
			{name: 'LpuSection_pid', mapping: 'LpuSection_pid'},
			{name: 'LpuSectionAge_id', mapping: 'LpuSectionAge_id'},
			{name: 'LpuSection_Name', mapping: 'LpuSection_Name'},
			{name: 'LpuSectionProfile_Code', mapping: 'LpuSectionProfile_Code'},
			{name: 'LpuSectionProfile_SysNick', mapping: 'LpuSectionProfile_SysNick'},
			{name: 'LpuUnitType_Code', mapping: 'LpuUnitType_Code'},
			{name: 'LpuUnitType_SysNick', mapping: 'LpuUnitType_SysNick'},
			{name: 'LpuSection_disDate', mapping: 'LpuSection_disDate'},
			{name: 'LpuSection_setDate', mapping: 'LpuSection_setDate'},
			{name: 'WorkData_begDate', mapping: 'WorkData_begDate'},
			{name: 'WorkData_endDate', mapping: 'WorkData_endDate'},
			{name: 'WorkData_dloBegDate', mapping: 'WorkData_dloBegDate'},
			{name: 'WorkData_dloEndDate', mapping: 'WorkData_dloEndDate'},
			{name: 'PostKind_id', mapping: 'PostKind_id'},
			{name: 'PostMed_id', mapping: 'PostMed_id'},
			{name: 'PostMed_Code', mapping: 'PostMed_Code'},
			{name: 'PostMed_Name', mapping: 'PostMed_Name'},
			{name: 'frmpEntry_id', mapping: 'frmpEntry_id'},
			{name: 'MedSpecOms_id', mapping: 'MedSpecOms_id'},
			{name: 'MedStaffFactCache_IsDisableInDoc', mapping: 'MedStaffFactCache_IsDisableInDoc'}
		]),
		url: C_MEDPERSONAL_LIST
	});
	doLoadMedStaffFactGlobalStore();
}


function doLoadMedServiceGlobalStore(iteration) {
	if ( !iteration )
		var iteration = 1;
	swMedServiceGlobalStore.load({
		callback: function(r, o, success) {
			if ( success === false || !r || r.length == 0 )
			{
/*
				if ( iteration >= 3 )
					sw.swMsg.alert(langs('Ошибка'),langs('Невозможно загрузить глобальный справочник служб!'));
				else
					doLoadMedServiceGlobalStore(iteration + 1);
*/
			}
		}
	});
}

/**
 * Изменение текста на фоновой подложке ПромедВеб
 * (запись вспомогательной информации, логирование и отображение событий для пользователя)
 * @param text string Текст для записи.
 * @param id string Id элемента.
 * @param add boolean Признак добавления текста. Eсли false, то текст заменяется. По умолчанию true.
 * @param effect boolean Использовать эффект при добавлении текста. По умолчанию true.
 */
function setPromedInfo(text, id, effect) {

	effect = (effect!=undefined)?effect:true; // по умолчанию применяем эффект
	// Выбираем элемент (для добавления выбираем родителя, для изменения в функцию приходит id того элемента, который хотим изменить
	if (Ext.get(id)) { // если элемент с таким id уже существует
		var add = false; // то мы можем только апдейтить его
		var elem = Ext.get(id);
	} else {
		var add = true; // если элемента не существует, то мы его добавляем
		var elem = Ext.get('promed-info'); // иначе возьмем основной div для обновления
	}
	if (!elem) { // Если элемент найти не удалось, то сделать ничего нельзя
		return false;
	}
	if (add) { // при добавлении создаем новый див с указанным Id
		var ell = document.createElement('div');
		ell.innerHTML = text;
		ell.id = (id)?id:'promed-info';
		elem.dom.appendChild(ell);
		elem = Ext.get(ell);
	} else { // при изменении апдейтим имеющийся
		elem.update(text);
	}
	//console.warn(id,text);
	if (effect) { // применяем эффект
		if(!elem.isVisible()){
			elem.slideIn('t', {
				duration: .2,
				easing: 'easeIn',
				callback: function(){
					elem.fadeIn({endOpacity: .80, duration: 2});
					//elem.highlight('9999ff');
				}
			});
		} else {
			elem.fadeIn({endOpacity: .80, duration: 2});
			//el.highlight('9999ff');
		}
	}
}
/**
 * Загрузка списка служб в область глобальной видимости
 */
function loadMedServiceGlobalStore() {
	swMedServiceGlobalStore = new Ext.data.Store({
		autoLoad: false,
		reader: new Ext.data.JsonReader({
			id: 'MedService_id'
		}, [
			{name: 'MedService_id', mapping: 'MedService_id'},
			{name: 'Lpu_id', mapping: 'Lpu_id'},
			{name: 'LpuBuilding_id', mapping: 'LpuBuilding_id'},
			{name: 'LpuSection_id', mapping: 'LpuSection_id'},
			{name: 'LpuUnit_id', mapping: 'LpuUnit_id'},
			{name: 'LpuUnitType_id', mapping: 'LpuUnitType_id'},
			{name: 'MedService_begDT', mapping: 'MedService_begDT'},
			{name: 'MedService_endDT', mapping: 'MedService_endDT'},
			{name: 'MedService_Name', mapping: 'MedService_Name'},
			{name: 'MedService_Nick', mapping: 'MedService_Nick'},
			{name: 'MedServiceType_id', mapping: 'MedServiceType_id'}
		]),
		url: C_MEDSERVICE_LIST
	});

	doLoadMedServiceGlobalStore();
}


/**
 * Перемещает курсор в гриде на страницу вниз
 * @param {Ext.grid.GridPanel} grid
 * @param {String} key_field
 */
function GridPageDown(grid) {
	var records_count = grid.getStore().getCount();
	var selected_record = grid.getSelectionModel().getSelected();

	if ( records_count > 0 && selected_record ) {
		var index = grid.getStore().indexOf(selected_record);

		if ( index + 10 <= records_count - 1 ) {
			index = index + 10;
		}
		else {
			index = records_count - 1;
		}

		grid.getView().focusRow(index);
		grid.getSelectionModel().selectRow(index);
	}
}


/**
 * Перемещает курсор в гриде на страницу вверх
 * @param {Ext.grid.GridPanel} grid
 * @param {String} key_field
 */
 function GridPageUp(grid) {
	var records_count = grid.getStore().getCount();
	var selected_record = grid.getSelectionModel().getSelected();

	if ( records_count > 0 && selected_record ) {
		var index = grid.getStore().indexOf(selected_record);

		if ( index - 10 >= 0 ) {
			index = index - 10;
		}
		else {
			index = 0;
		}

		grid.getView().focusRow(index);
		grid.getSelectionModel().selectRow(index);
	}
}


/**
 * Перемещает курсор в гриде на первую запись
 * @param {Ext.grid.GridPanel} grid
 */
function GridHome(grid) {
	if (grid.getStore().getCount() > 0)
	{
		grid.getView().focusRow(0);
		grid.getSelectionModel().selectFirstRow();
	}
}


/**
 * Перемещает курсор в гриде на последнюю запись
 * @param {Ext.grid.GridPanel} grid
 */
function GridEnd(grid) {
	if (grid.getStore().getCount() > 0)
	{
		grid.getView().focusRow(grid.getStore().getCount() - 1);
		grid.getSelectionModel().selectLastRow();
	}
}

/**
 * Перемещает курсор в гриде на запись где значение поля field = value
 * @param {Ext.grid.GridPanel} grid
 * @param {string} field - наименование поля, по которому выбираем запись
 * @param {string} value - значение поля, по которому выбираем запись
 * @param {integer} shift - смещение ряда, на котором фокусируемся, от выбранного. Для центрирования выбора
 * Пример использования: GridAtRecord(this.getGrid(), 'LpuUnit_id', form.params.LpuUnit_id);
 */
function GridAtRecord(grid, field, value, shift) {
	grid.getStore().each(function(record)
	{
		if (record.get(field)==value)
		{
			var index = grid.getStore().indexOf(record);
			if (!shift || index + shift < 0 || index + shift > grid.getStore().getCount() - 1) {
				shift = 0;
			}
			grid.getView().focusRow(index + shift);
			grid.getSelectionModel().selectRow(index);
			return false;
		}
	});
	return null;
}
/**
 * Проверка, является ли текущий пользователь пользователем группы, или входит ли хотя бы в одну и групп
 */
function isUserGroup(group) {
	// 2013-09-09, savage: немного модифицировал метод, т.к. indexOf не всегда выдает нужный результат
	var
		groupsToExplore = (typeof group == 'string' ? [ group ] : group),
		i,
		result = false,
		userGroups = (typeof getGlobalOptions().groups == 'string' ? getGlobalOptions().groups.split('|') : []);

	if ( Ext.isArray(userGroups) ) {
		// Приводим к нижнему регистру все группы пользователя
		for ( i = 0; i < userGroups.length; i++) {
			userGroups[i] = userGroups[i].toLowerCase();
		}

		if ( Ext.isArray(groupsToExplore) ) {
			for ( i = 0; i < groupsToExplore.length; i++) {
				if ( groupsToExplore[i].toLowerCase().inlist(userGroups) )  {
					result = true;
				}
			}
		}
	}

	return result;
}

/**
 * Проверка, является ли текущий пользователь LpuCadrAdmin
 */
function isLpuCadrAdmin() {
	return getGlobalOptions().groups && getGlobalOptions().groups.toString().indexOf('LpuCadrAdmin') != -1;
}

/**
 * Проверка, является ли текущий пользователь SMOUser или TFOMSUser
 */
function isSmoTfomsUser() {
	return getGlobalOptions().groups && (getGlobalOptions().groups.toString().indexOf('SMOUser') != -1 || getGlobalOptions().groups.toString().indexOf('TFOMSUser') != -1);
}

/**
 * Проверка, является ли текущий пользователь OperLLOUser
 */
function isOperLLOUser() {
	return getGlobalOptions().groups && (getGlobalOptions().groups.toString().indexOf('OperLLO') != -1);
}

/**
 * Проверка, является ли текущий пользователь суперадминистратором
 */
function isSuperAdmin() {
	return getGlobalOptions().groups && getGlobalOptions().groups.toString().indexOf('SuperAdmin') != -1 && getGlobalOptions().superadmin;
}

/**
 * Проверка, является ли текущий пользователь пользователем реестров (полный доступ)
 */
function isRegistryUser() {
	return getGlobalOptions().groups && getGlobalOptions().groups.toString().indexOf('RegistryUser') != -1;
}

/**
 * Проверка, является ли текущий пользователь администратором организации
 */
function isOrgAdmin() {
	return getGlobalOptions().groups && (getGlobalOptions().groups.toString().indexOf('OrgAdmin') != -1 || getGlobalOptions().groups.toString().indexOf('LpuAdmin') != -1);
}

/**
 * Проверка, является ли текущий пользователь пользователем организации
 */
function isOrgUser() {
	return getGlobalOptions().groups && getGlobalOptions().groups.toString().indexOf('OrgUser') != -1;
}

/**
 * Проверка, является ли текущий пользователь администратором центра записи
 */
function isCallCenterAdmin() {
	return getGlobalOptions().groups && getGlobalOptions().groups.toString().indexOf('CallCenterAdmin') != -1;
}

/**
 * Проверка, является ли текущий пользователь оператором центра записи
 */
function isCallCenterOperator() {
	return getGlobalOptions().groups && getGlobalOptions().groups.toString().indexOf('OperatorCallCenter') != -1;
}

/**
 * Проверка, является ли текущий пользователь администратором регистратуры ЛПУ
 */
function isRegAdmin() {
	return getGlobalOptions().groups && getGlobalOptions().groups.toString().indexOf('RegAdmin') != -1;
}

/**
 * Проверка, является ли текущий пользователь администратором МО.
 */
function isLpuAdmin(lpu_id) {
	return isOrgAdmin() && getGlobalOptions().orgtype && getGlobalOptions().orgtype == 'lpu' && (Ext.isEmpty(lpu_id) || lpu_id == getGlobalOptions().lpu_id); // return getGlobalOptions().groups && getGlobalOptions().groups.toString().indexOf('LpuAdmin') != -1;
}

/**
 * Проверка, имеет ли текущий пользователь доступ к бланкам ЛВН
 */
function isEvnStickBlankAccess() {
	return getGlobalOptions().evnstickblank_access && getGlobalOptions().evnstickblank_access == 1;
}

/**
 * Проверка, имеет ли текущий пользователь доступ к регистру беременных
 */
function isPregnancyRegisterAccess() {
	return isUserGroup(['OperPregnRegistry','RegOperPregnRegistry']);
}

/**
 * Проверка, имеет ли текущий пользователь группу прав МПЦ
 */
function isInterdistrictPerCenter() {
	return isUserGroup('InterdistrictPerCenter');
}

/**
 * Проверка, имеет ли текущий пользователь доступ к работе с медсвидетельствами (о смерти)
 */
function isMedSvidAccess() {
	return (isSuperAdmin() || isUserGroup('MedSvidDeath') /*|| (getGlobalOptions().medsvidgrant_add && getGlobalOptions().medsvidgrant_add == 1)*/);
}

/**
 * Проверка, является ли текущий пользователь DrivingCommission
 */
function isDrivingCommission() {
	if (getRegionNick() == 'kz') return false;
	if (getRegionNick() != 'perm') return true;
	return isUserGroup(['DrivingCommissionReg','DrivingCommissionOphth','DrivingCommissionPsych','DrivingCommissionPsychNark','DrivingCommissionTherap']);
}

/**
 * Проверка, имеет ли текущий пользователь доступ к регистру ФМБА
 */
function isFmbaUser() {
	return getGlobalOptions().groups && getGlobalOptions().groups.toString().indexOf('FmbaRegistry') != -1;
}

/**
 * Проверка, имеет ли текущий пользователь доступ к регистру по больным туберкулезом
 */
function isTubRegistryUser() {
	return getGlobalOptions().groups && getGlobalOptions().groups.toString().indexOf('TubRegistry') != -1;
}

/**
 * Проверка наличия АРМа
 */
function haveArmType(ARMType) {
	return (sw.Promed.MedStaffFactByUser && sw.Promed.MedStaffFactByUser.store && sw.Promed.MedStaffFactByUser.store.findBy(function(rec) { return (rec.get('ARMType') == ARMType); }) >= 0);
}

/**
 * Проверка, является ли текущий пользователь врачом поликлиники
 */
function isPolkaVrach() {
	return haveArmType('common') || haveArmType('stom') || haveArmType('stom6');
}

/**
 * Проверка, является ли текущий пользователь регистратором поликлиники
 */
function isPolkaRegistrator() {
	return /*isEvnStickBlankAccess() ||*/ haveArmType('regpol') || haveArmType('regpol6');
}

/**
 * Проверка, является ли текущий пользователь оператором
 */
function isOperator() {
	return Ext.isEmpty(getGlobalOptions().medpersonal_id);
}

function isRegLvn() {
    return haveArmType('lvn');
}

/**
 * Проверка, является ли текущий пользователь врачём приёмного отделения
 */
function isStacReceptionVrach() {
	return haveArmType('stacpriem');
}

/**
 * Проверка, является ли текущий пользователь врачом в стационаре
 */
function isStacVrach() {
	return haveArmType('stac');
}

/**
 * Проверка, является ли текущий пользователь врачём приёмного отделения
 */
function isMedStatUser() {
	return getGlobalOptions().isMedStatUser;
}

/**
 * Проверка, является ли текущий пользователь патологоанатомом
 */
function isPathoMorphoUser() {
	return getGlobalOptions().isPathoMorphoUser;
}

/**
 * Проверка, имеет ли пользователь доступ к просмотру кадровой инфы.
 */
function isCadrUserView() {
	return getGlobalOptions().groups && (getGlobalOptions().groups.toString().indexOf('LpuCadrView') != -1 || getGlobalOptions().groups.toString().indexOf('LpuTariffSpec') != -1 || getGlobalOptions().groups.toString().indexOf('RosZdrNadzorView') != -1 || getGlobalOptions().groups.toString().indexOf('SuperAdmin') != -1 || isLpuAdmin());
}

/**
 * Проверка, входит ли пользователь только в группы просмотра кадровой инфы.
 */
function onlyCadrUserView() {
	// TODO: Проверку на количество надо делать конечно более углубленную - сейчас предполагается что Кадровики и РосЗдравНадзор не могут одновременно быть указаны у одного пользователя
	//
	return getGlobalOptions().groups && (getGlobalOptions().groups.split('|').length==1) && (getGlobalOptions().groups.toString().indexOf('LpuTariffSpec') != -1 || getGlobalOptions().groups.toString().indexOf('LpuCadrView') != -1 || getGlobalOptions().groups.toString().indexOf('RosZdrNadzorView') != -1);
	//return true;
}

/**
 * Проверка, является ли текущий пользователь MedPersView.
 */
function isMedPersView() {
	return getGlobalOptions().groups && getGlobalOptions().groups.toString().indexOf('MedPersView') != -1;
}
/**
 * Проверка, входит ли пользователь в группу  LpuTariffSpec (Специалиста по тарификации)
 */
function isLpuTariffSpec() {
	return getGlobalOptions().groups && getGlobalOptions().groups.toString().indexOf('LpuTariffSpec') != -1;
}
/**
 * Проверка, входит ли пользователь в группу  ExportAttachedPopulation (Экспорт прикрепленного населения)
 */
function isExpPop() {
	return getGlobalOptions().groups && getGlobalOptions().groups.toString().indexOf('ExportAttachedPopulation') != -1;
}

/**
 * Проверка, входит ли пользователь в группу  StorageCard (Сотрудник картохранилища)
 */
function isStorageCardUser() {
	return getGlobalOptions().groups && getGlobalOptions().groups.toString().indexOf('StorageCard') != -1;
}

/**
 * Деперсонализация данных пациента в АРМ МСЭ
 */
function isMseDepers() {
	return getGlobalOptions().use_depersonalized_expertise && getWnd('swMseWorkPlaceWindow').isVisible();
}

/**
 * Проверка, имеет ли текущий пользователь доступ к работе с обращениями.
 */
function isAccessTreatment() {
	var access = false;
	if (getGlobalOptions().superadmin)
		access = true;
	if (getGlobalOptions().isMinZdrav)
		access = true;
	return access;
}

function usePostgre() {
	return !!getGlobalOptions().usePostgre;
}

/**
 * Возвращает опции отображения
 */
function getAppearanceOptions() {
	return Ext.globalOptions.appearance;
}


/**
 * Возвращает опции отображения
 */
function getNoticeOptions() {
	return Ext.globalOptions.notice;
}

/**
 * Возвращает опции лаборатории
 */
function getLisOptions() {
	return Ext.globalOptions.lis;
}

/**
 * Возвращает суперглобальные опции
 */
function getGlobalOptions() {
	if (Ext.globalOptions)
		return Ext.globalOptions.globals;
}

/**
 * Возвращает настройки стационара
 */
function getStacOptions() {
	return Ext.globalOptions.stac;
}

/**
 * Возвращает настройки поликлиники
 */
function getPolkaOptions() {
	return Ext.globalOptions.polka;
}

/**
 * Возвращает настройки назначений
 */
function getPrescriptionOptions() {
    return Ext.globalOptions.prescription;
}

/**
 * Возвращает настройки печати
 */
function getPrintOptions() {
	return Ext.globalOptions.print;
}

/**
 * Возвращает настройки эл. очереди
 */
function getElectronicQueueOptions() {
	return (Ext.globalOptions.electronicqueue) ? Ext.globalOptions.electronicqueue : {};
}


/**
 * Возвращает настройки вызовов на дом
 */
function getHomeVizitOptions() {
	return Ext.globalOptions.homevizit;
}

/**
 * Возвращает настройки специфики
 */
function getSpecificsOptions() {
	return Ext.globalOptions.specifics;
}


/**
 * Возвращает настройки ЛВН
 */
function getEvnStickOptions() {
	return Ext.globalOptions.evnstick;
}


/**
 * Возвращает прочие настройки
 */
function getOthersOptions() {
	return Ext.globalOptions.others;
}


/**
 * Возвращает настройки услуг
 */
function getUslugaOptions() {
	return Ext.globalOptions.usluga;
}

/**
 * Возвращает настройки учета медикаментов
 */
function getDrugControlOptions() {
	return Ext.globalOptions.drugcontrol;
}

/**
 * Возвращает АРМ по умолчанию
 */
function getDefaultARM() {
	return Ext.globalOptions.defaultARM;
}

/**
 * Возвращает признак использования remote хранилища вебсервера для хранения справочников
 */
function isRemoteDB() {
	if (getGlobalOptions().useRemoteDB) { // если MongoDB установлено на сервере и используется
		/*if (getGlobalOptions().client && getGlobalOptions().client == 'mobile') { // Если клиент - телефон или планшет
			Ext.globalOptions.others.enable_localdb = false;
		}
		if (Ext.isSafari || Ext.isOpera || Ext.isLinux) { // Если браузер Сафари или Опера, или из под Линукса (подразумеваем что Линукс = марш)
			Ext.globalOptions.others.enable_localdb = false;
		}*/
		// всегда используем МонгоДБ
		Ext.globalOptions.others.enable_localdb = false;
	} else {
		Ext.globalOptions.others.enable_localdb = true;
		if (!Ext.globalOptions.others.enable_localdb) {
			Ext.Msg.alert(langs('Ошибка'), langs('В настройках пользователя отключено использование локальных справочников ,<br/>')+
				'но на сервере не настроена БД для серверных "локальных" справочников!<br/>'+
				langs('Пожалуйста, сообщение о проблеме в технический отдел.'));
		}
		if (isSuperAdmin())
			setPromedInfo(langs('На сервере не настроена БД для "локальных" справочников <br/>или отсутствует библиотека MongoDB'), 'remote-info');
	}
	return !Ext.globalOptions.others.enable_localdb;
}

/**
 * Получение активного окна
 */
function getActiveWin() {
	return Ext.WindowMgr.getActive();
}

/**
 *
 */

/**
 * Закрывает все окна
 * TODO: Скорее всего их надо не просто скрывать, а дестроить
 */
function closeWindowsAll() {
	Ext.WindowMgr.each(function(wnd){
		if ( wnd.isVisible() ) {
			wnd.hide();
		}
	});
}
/**
 * Устанавливает текущую МО
 */
function setCurrentLpu(data) {
	closeWindowsAll();

	getCountNewDemand();

	// обновляем настройки (getGlobalOptions() и перезагружаем локальные сторе)
	Ext.Ajax.request({
		url: C_OPTIONS_LOAD,
		success: function(result){
			Ext.globalOptions = Ext.util.JSON.decode(result.responseText);
			// Если при получении глобальных настроек вернулась ошибка, то выводим еще и выходим из Промеда
			if  (Ext.globalOptions && (Ext.globalOptions.success!=undefined) && Ext.globalOptions.success === false) {
				Ext.Msg.alert('Ошибка при загрузке настроек', 'При загрузке настроек с сервера произошла ошибка.<br/><b>'+((Ext.globalOptions.Error_Msg)?Ext.globalOptions.Error_Msg:'')+'</b><br/>Выйдите из системы и зайдите снова.',
					function () {
						window.onbeforeunload = null;
						window.location = C_LOGOUT;
					});

				return true;
			}
			log(result,'sdfsd')
			// Подменяем данные с локального хранилища при перечитывании глобальных настроек
			Ext.setLocalOptions();

			user_menu.items.items[0].setText('<b>Информация о пользователе</b><br/>'+'Имя : '+UserName+'<br/>'+'E-mail : '+UserEmail+'<br/>'+'Описание : '+UserDescr+'<br/>'+'МО : '+getGlobalOptions().lpu_nick);
			if ( window['swLpuStructureViewForm'] ) {
				swLpuStructureViewForm.close();
				window['swLpuStructureViewForm'] = null;
			}

			loadGlobalStores({
				callback: function () {
					// Открытие АРМа по умолчанию
					if(Ext.globalOptions.others && Ext.globalOptions.others.notifyAboutLastAuth == true) {
						Ext.Ajax.request({
							url: '/?c=user&m=getLastAuth',
							success: function (response) {
								var lastAuth = response.responseText;
								sw.swMsg.show({
									buttons: Ext.Msg.OK,
									fn: function(buttonId, text, obj) {
										if (Ext.get('do_not_show').getValue() == 'on'){
											disableNotifyLastAuth()
										}
										sw.Promed.MedStaffFactByUser.openDefaultWorkPlace();
									}.createDelegate(this),
									icon: Ext.MessageBox.QUESTION,
									msg: 'Последний успешный вход в систему был выполнен: '+lastAuth+'. <br/><br/><input type="checkbox" id="do_not_show" /> Больше не показывать это сообщение',
									title: 'Успешный вход в систему'
								});
							}
						});
					} else {
						sw.Promed.MedStaffFactByUser.openDefaultWorkPlace();
					}
				}
			});
		},
		failure: function(result){
			Ext.Msg.alert(langs('Ошибка при загрузке настроек'), langs('При загрузке настроек с сервера произошла ошибка. Выйдите из системы и зайдите снова.'));
		},
		method: 'GET',
		timeout: 120000
	});
}

function disableNotifyLastAuth(){
	var data = {
		node: 'others',
		notifyAboutLastAuth : false
	};
	Ext.Ajax.request({
		url: C_OPTIONS_SAVE_FORM,
		params: data,
	});
}

function changeCurrentLpu(data){
	Ext.Ajax.request({
		url: C_OPTIONS_LOAD,
		success: function(result){
			Ext.globalOptions = Ext.util.JSON.decode(result.responseText);
			// Если при получении глобальных настроек вернулась ошибка, то выводим еще и выходим из Промеда
			if  (Ext.globalOptions && (Ext.globalOptions.success!=undefined) && Ext.globalOptions.success === false) {
				Ext.Msg.alert('Ошибка при загрузке настроек', 'При загрузке настроек с сервера произошла ошибка.<br/><b>'+((Ext.globalOptions.Error_Msg)?Ext.globalOptions.Error_Msg:'')+'</b><br/>Выйдите из системы и зайдите снова.',
					function () {
						window.onbeforeunload = null;
						window.location = C_LOGOUT;
					});

				return true;
			}
			// Подменяем данные с локального хранилища при перечитывании глобальных настроек
			Ext.setLocalOptions();

			user_menu.items.items[0].setText('<b>Информация о пользователе</b><br/>'+'Имя : '+UserName+'<br/>'+'E-mail : '+UserEmail+'<br/>'+'Описание : '+UserDescr+'<br/>'+'МО : '+getGlobalOptions().lpu_nick);
			/*if ( window['swLpuStructureViewForm'] ) {
				swLpuStructureViewForm.close();
				window['swLpuStructureViewForm'] = null;
			}*/

			loadGlobalStores({
				callback: function () {
					// Открытие АРМа по умолчанию
					//sw.Promed.MedStaffFactByUser.openDefaultWorkPlace();
				}
			});
		},
		failure: function(result){
			Ext.Msg.alert(langs('Ошибка при загрузке настроек'), langs('При загрузке настроек с сервера произошла ошибка. Выйдите из системы и зайдите снова.'));
		},
		method: 'GET',
		timeout: 120000
	});
}

/**
 * Очистка комбобоксов во фрейме адреса
 *
 * @param {Integer} level Уровень, который мы очищаем
 * @param {Object} combos Массив с комобобоксами
}
 */
function clearAddressCombo(
	level,
	combos
	) {
	var country_combo = combos['Country'];
	var region_combo = combos['Region'];
	var subregion_combo = combos['SubRegion'];
	var city_combo = combos['City'];
	var town_combo = combos['Town'];
	var street_combo = combos['Street'];

	var klarea_pid = 0;

	switch (level)
	{
		case 0:
			country_combo.clearValue();
			region_combo.clearValue();
			subregion_combo.clearValue();
			city_combo.clearValue();
			town_combo.clearValue();
			if (street_combo) street_combo.clearValue();

			region_combo.getStore().removeAll();
			subregion_combo.getStore().removeAll();
			city_combo.getStore().removeAll();
			town_combo.getStore().removeAll();
			if (street_combo) street_combo.getStore().removeAll();
		break;

		case 1:
			region_combo.clearValue();
			subregion_combo.clearValue();
			city_combo.clearValue();
			town_combo.clearValue();
			if (street_combo) street_combo.clearValue();

			subregion_combo.getStore().removeAll();
			city_combo.getStore().removeAll();
			town_combo.getStore().removeAll();
			if (street_combo) street_combo.getStore().removeAll();
		break;

		case 2:
			subregion_combo.clearValue();
			city_combo.clearValue();
			town_combo.clearValue();
			if (street_combo) street_combo.clearValue();

			city_combo.getStore().removeAll();
			town_combo.getStore().removeAll();
			if (street_combo) street_combo.getStore().removeAll();

			if (region_combo.getValue() != null)
			{
				klarea_pid = region_combo.getValue();
			}

			loadAddressCombo(level, combos, 0, klarea_pid, true);
		break;

		case 3:
			city_combo.clearValue();
			town_combo.clearValue();
			if (street_combo) street_combo.clearValue();

			town_combo.getStore().removeAll();
			if (street_combo) street_combo.getStore().removeAll();

			if (subregion_combo.getValue() != null)
			{
				klarea_pid = subregion_combo.getValue();
			}
			else if (region_combo.getValue() != null)
			{
				klarea_pid = region_combo.getValue();
			}

			loadAddressCombo(level, combos, 0, klarea_pid, true);
		break;

		case 4:
			town_combo.clearValue();
			if (street_combo) street_combo.clearValue();

			if (street_combo) street_combo.getStore().removeAll();

			if (city_combo.getValue() != null)
			{
				klarea_pid = city_combo.getValue();
			}
			else if (subregion_combo.getValue() != null)
			{
				klarea_pid = subregion_combo.getValue();
			}
			else if (region_combo.getValue() != null)
			{
				klarea_pid = region_combo.getValue();
			}

			loadAddressCombo(level, combos, 0, klarea_pid, true);
		break;
	}
}


/**
 * Загрузка комбобокса в фрейме адреса
 *
 * @param {Integer} level Уровень, который мы загружаем
 * @param {Object} combos Массив с комобобоксами
 * @param {Integer} country_id Идентификатор выбранной страны
 * @param {Integer} value Значение выбранного комбобокса
 * @param {Boolean} recursion Загружаем рекурсивно?
 */
function loadAddressCombo(level, combos, country_id, value, recursion) {
	var target_combo = null;

	switch (level)
	{
		case 0:
			target_combo = combos['Region'];
		break;

		case 1:
			target_combo = combos['SubRegion'];
		break;

		case 2:
			target_combo = combos['City'];
		break;

		case 3:
			target_combo = combos['Town'];
		break;

		case 4:
			target_combo = combos['Street'];
		break;

		default:
			return false;
		break;
	}
	if (target_combo == null)
		return;
	target_combo.clearValue();
	target_combo.getStore().removeAll();
	target_combo.getStore().load({
		params: {
			country_id: country_id,
			level: level + 1,
			value: value
		},
		callback: function(store, records, options) {
			if (level >= 0 && level <= 3 && recursion == true)
			{
				loadAddressCombo(level + 1, combos, country_id, value, recursion);
			}
		}
	});
}


/**
 * Запрос к серверу на загрузку комбобокса
 *
 * @param {Ext.form.ComboBox} combo Комбобокс
 * @param {String} name Название справочника, для вывода сообщения об ошибке
 * @param {String} callback Функция, выполняющейся при окончании загрузки
 */
function loadComboOnce(combo, name, callback) {
	if (combo.getStore().getCount() == 0) {
		combo.getStore().load({
			callback: function(records, options, success) {
				if (callback && typeof callback=='function')
					callback(success);
				if (!success)
				{
					Ext.Msg.alert(langs('Ошибка'), langs('Ошибка при загрузке справочника: ')+name);
					return false;
				}
			}
		});
	}
}


/**
 * Переводит фокус на заданный грид
 *
 * @param {Ext.grid.GridPanel} grid Грид на который переходим
 */
function TabToGrid(grid) {
	if ( grid.getStore().getCount() > 0 ) {
		var selected_record = grid.getSelectionModel().getSelected();
		if (selected_record != -1) {
			var index = grid.getStore().indexOf(selected_record);
		}
		else {
			var index = 0;
		}
		grid.getView().focusRow(index);
		grid.getSelectionModel().selectRow(index);
		return true;
	}
	else {
		return false;
	}
}


/**
 * Проставляет значение комбобокса с проверкой, что такое значение существует в Store
 * Если значения не существует, то очищаем поле
 *
 * @param {Ext.form.ComboBox} combo Комбобокс
 * @param {String} value Значение, которое пытаемся проставить
 */
function SetComboValueWithCheck(combo, value, fireEvent) {
	var record = combo.getStore().getById(value);

	if ( !record )
	{
		combo.clearValue();
		if (fireEvent)
			combo.fireEvent('change', combo, '');
	}
	else
	{
		combo.setValue(value);
		if (fireEvent)
			combo.fireEvent('change', combo, value);
	}
}

/**
 * Возвращает последний компонент на заданной форме являющийся полем ввода
 * служит для поиска последнего элемента на вкладке для того чтобы сфокусироваться на нем
 *
 * @param {Ext.form.BasicForm} form
 * @return {Ext.form.Field} Последний компонент на форме являющийся полем ввода
 */
function getLastFieldOnForm(form) {
	if (form.items.length == 0)
		return null;
	var el = form;
	while (el.items != undefined && el.items.length > 0)
	{
		var el1 = null;
		el.items.each(function(f) {
			el1 = f;
		});
		el = el1;
	}
	return el;
}


/**
 * Обновляет данные в строке грида по локальным данным, чтобы не тянуть их с сервера
 * или добавляет новую запись в грид, если стоит признак add
 * В случае возврата параметра по людям обновляет их данные во всех строках грида
 *
 * @param {Ext.grid.GridPanel} grid Грид, данные которого обновляем
 * @param {Object} data Массив с данными строки грида
 * @param {Boolean} add Признак добавления данных
 */
function setGridRecord(grid, data, add) {
	if (add == undefined || !add) {
		var record = grid.getStore().getById(data[grid.getStore().reader.meta.id]);
		if (record) {
			// Night: 25.11.2009. Чуть доработал. Cейчас цикл бежит не входному массиву, а по полям строки грида
			// и если поле строки грида имеет формат date, а входящее значение не date, то значение конвертится.
			// То есть достаточно с формы с которой передаем данные взять getForm().getValues() и отдать в data.
			record.fields.each(function(f) {
				if (data[f.name])
				{
					if ((f.type=='date') && (!Ext.isDate(data[f.name]))) {
						record.set(f.name, Date.parseDate(data[f.name], f.dateFormat));
					}
					else {
						record.set(f.name, data[f.name]);
					}
				}
			});
			//record.set(field, data[field]);
			record.commit();
		}
	}
	else {
		grid.getStore().loadData([ data ], true);
	}
	grid.getStore().each(function(record) {
		if (record.get('Person_id') != undefined &&
			record.get('Server_id') !=undefined &&
			record.get('Person_id') == data.Person_id &&
			record.get('Server_id') == data.Server_id
		) {
			record.set('Person_Birthday', data.Person_Birthday);
			record.set('Person_Firname', data.Person_Firname);
			record.set('Person_Secname', data.Person_Secname);
			record.set('Person_Surname', data.Person_Surname);

			record.commit();
		}
	});
}


/**
 * Получает с сервера текущее значение даты (d.m.Y) и времени (H:i)
 *
 * @params {Object} params Опции:
 *     callback {Function} Функция, вызываемая после загрузки данных
 *     addMaxDateDays (int) Количество дней, добавляемых к максимальной дате (по умолчанию 0)
 *     addMinDateDays (int) Количество дней, добавляемых к минимальной дате (по умолчанию 0)
 *     dateField (object) поле формы "Дата" (обязательный параметр)
 *     timeField (object) поле формы "Время"
 *     setDate (boolean) установить полученное значение даты в поле params.dateField
 *     setTimeMaxValue (boolean) установить полученное значение время в качестве максимального для поля params.timeField
 *     setTimeMinValue (boolean) установить полученное значение время в качестве минимального для поля params.timeField
 *     setDateMaxValue (boolean) установить полученное значение даты в качестве максимального для поля params.dateField
 *     setDateMinValue (boolean) установить полученное значение даты в качестве минимального для поля params.dateField
 *     setTime (boolean) установить полученное значение времени в поле params.timeField
 *     loadMask (boolean) отображать маску во время запроса к серверу
 *     windowId (string) идентификатор окна, на которое накладывается маска (обязательный параметр)
 */
function setCurrentDateTime(params) {
	if ( !params || typeof params != 'object' || !params.dateField || typeof params.dateField != 'object' || !params.windowId ) {
		return false;
	}

	if ( params.loadMask ) {
		var loadMask = new Ext.LoadMask(Ext.get(params.windowId), {msg: "Получение текущих даты и времени"});
		loadMask.show();
	}

	if ( !params.addMaxDateDays ) {
		params.addMaxDateDays = 0;
	}

	if ( !params.addMinDateDays ) {
		params.addMinDateDays = 0;
	}

	Ext.Ajax.request({
		callback: function(options, success, response) {
			if ( params.loadMask ) {
				loadMask.hide();
			}

			if ( success ) {
				var response_obj = Ext.util.JSON.decode(response.responseText);

				if (response_obj.Date) {
					Ext.globalOptions.globals.date = response_obj.Date; // обновим текущую дату в getGlobalOptions().date
				}

				var date;
				if (params.dateField.format == 'd.m.Y H:i') {
					date = Date.parseDate(response_obj.Date + ' ' + response_obj.Time, params.dateField.format);
				} else {
					date = Date.parseDate(response_obj.Date, 'd.m.Y');
				}

				var time = Date.parseDate(response_obj.Time,'H:i');

				if ( params.setTime && params.timeField && typeof params.timeField == 'object' && params.timeField.setRawValue ) {
					params.timeField.setRawValue(Ext.util.Format.date(time,'H:i'));
					if (params.timeField.validate) {
						params.timeField.validate(); // чтобы поле не было зелёным
					}
				}

				if ( params.setDate && !params.dateField.getValue() ) {
					params.dateField.setValue(date);
				}

				if ( params.setDateMaxValue ) {
					params.dateField.setMaxValue(date.add(Date.DAY, params.addMaxDateDays));
					if ( params.setTimeMaxValue ) {
						params.timeField.setMaxValue(time.add('H:i', 0),params.dateField.getValue());
					}
					// params.dateField.setMaxValue(date);
				}

				if ( params.setDateMinValue ) {
					params.dateField.setMinValue(date.add(Date.DAY, params.addMinDateDays));

					// params.dateField.setMinValue(date);
				}



				if ( params.callback && typeof params.callback == 'function' ) {
					params.callback(date);
				}
			}
			else {
				sw.swMsg.alert(langs('Ошибка'), langs('Ошибка при определении текущей даты и времени'), function() {params.dateField.focus(true);} );

				if ( params.callback && typeof params.callback == 'function' ) {
					params.callback();
				}
			}
		},
		url: C_GET_CURDATETIME
	});
}

/**
 * Получает с сервера текущее значение даты (d.m.Y) и времени (H:i) (сокращенный вариант)
 * просто получает время и выполняет callback
 *
 * @params {Object} params Опции:
 *     callback {Function} Функция, вызываемая после загрузки данных, обязательное поле
 * Возвращает объект параметры:
 *     success
 *     date
 *     time
 */
function getCurrentDateTime(params) {
	Ext.Ajax.request({
		callback: function(options, success, response) {
			var result = {};
			result.success = success;
			result.date = null;
			result.time = null;
			if (success)
			{
				var response_obj = Ext.util.JSON.decode(response.responseText);
				result.date = response_obj.Date;
				result.time = response_obj.Time;
			}
			if ( params.callback && typeof params.callback == 'function' ) {
				params.callback(result);
			}
		},
		url: C_GET_CURDATETIME
	});
}

/**
 * Проверка, является ли текущий пользователь пользователем аптеки
 */
function isFarmacyUser() {
	return getGlobalOptions().groups && (getGlobalOptions().groups.toString().indexOf('FarmacyAdmin') != -1 || getGlobalOptions().groups.toString().indexOf('FarmacyUser') != -1);
}


function getCurArm() {
	return  sw.Promed.MedStaffFactByUser.last ? sw.Promed.MedStaffFactByUser.last.ARMType : '';
}

/**
 * Печать бланка талона амбулаторного пациента
 */
function printEvnPLBlank(options) {
	if ( typeof options != 'object' ) {
		return false;
	}

	if ( typeof options.type != 'string' || (options.type != 'EvnPL' && options.type != 'EvnPLStom') ) {
		return false;
	}

    if (!getRegionNick().inlist(['kz', 'ufa'])) {
    	if( getRegionNick() == 'kaluga' && 'DirMaster' == options.formType) {
    		if (getPolkaOptions().print_single_list) {
				// печать ТАП на 1 листе
				printBirt({
					'Report_FileName': 'f025-1u_TimeTableGraf_A5.rptdesign',
					'Report_Params': '&prmFntPnt=1&prmBckPnt=1&pramRazr=0&paramTimeTableGraf=' + options.TimetableGraf_id + '&paramLpu=' + getGlobalOptions().lpu_id,
					'Report_Format': 'pdf'
				});
			} else if (getPolkaOptions().print_format && parseInt(getPolkaOptions().print_format) == 2) {
				// A5
				if (getPolkaOptions().print_two_side && parseInt(getPolkaOptions().print_two_side) == 2) {
					// двусторонняя Да
					printBirt({
						'Report_FileName': 'f025-1u_TimeTableGraf_A5.rptdesign',
						'Report_Params': '&prmFntPnt=1&prmBckPnt=1&pramRazr=1&paramTimeTableGraf=' + options.TimetableGraf_id + '&paramLpu=' + getGlobalOptions().lpu_id,
						'Report_Format': 'pdf'
					});
				} else {
					// двусторонняя Нет
					printBirt({
						'Report_FileName': 'f025-1u_TimeTableGraf_A5.rptdesign',
						'Report_Params': '&prmFntPnt=1&prmBckPnt=0&pramRazr=0&paramTimeTableGraf=' + options.TimetableGraf_id + '&paramLpu=' + getGlobalOptions().lpu_id,
						'Report_Format': 'pdf'
					});
					printBirt({
						'Report_FileName': 'f025-1u_TimeTableGraf_A5.rptdesign',
						'Report_Params': '&prmFntPnt=0&prmBckPnt=1&pramRazr=0&paramTimeTableGraf=' + options.TimetableGraf_id + '&paramLpu=' + getGlobalOptions().lpu_id,
						'Report_Format': 'pdf'
					});
				}
			} else {
				// A4
				if (getPolkaOptions().print_two_side && parseInt(getPolkaOptions().print_two_side) == 2) {
					// двусторонняя Да
					printBirt({
						'Report_FileName': 'f025-1u_TimeTableGraf_all.rptdesign',
						'Report_Params': '&prmFntPnt=1&prmBckPnt=1&paramTimeTableGraf=' + options.TimetableGraf_id + '&paramLpu=' + getGlobalOptions().lpu_id,
						'Report_Format': 'pdf'
					});
				} else {
					// двусторонняя Нет
					printBirt({
						'Report_FileName': 'f025-1u_TimeTableGraf_all.rptdesign',
						'Report_Params': '&prmFntPnt=1&prmBckPnt=0&paramTimeTableGraf=' + options.TimetableGraf_id + '&paramLpu=' + getGlobalOptions().lpu_id,
						'Report_Format': 'pdf'
					});
					printBirt({
						'Report_FileName': 'f025-1u_TimeTableGraf_all.rptdesign',
						'Report_Params': '&prmFntPnt=0&prmBckPnt=1&paramTimeTableGraf=' + options.TimetableGraf_id + '&paramLpu=' + getGlobalOptions().lpu_id,
						'Report_Format': 'pdf'
					});
				}
			}
    	} else {
			if (getPolkaOptions().print_single_list) {
				// печать ТАП на 1 листе
				printBirt({
					'Report_FileName': 'f025-1u_blank_A5.rptdesign',
					'Report_Params': '&prmFntPnt=1&prmBckPnt=1&pramRazr=0&paramPerson=' + options.personId + '&paramLpu=' + getGlobalOptions().lpu_id,
					'Report_Format': 'pdf'
				});
			} else if (getPolkaOptions().print_format && parseInt(getPolkaOptions().print_format) == 2) {
				// A5
				if (getPolkaOptions().print_two_side && parseInt(getPolkaOptions().print_two_side) == 2) {
					// двусторонняя Да
					printBirt({
						'Report_FileName': 'f025-1u_blank_A5.rptdesign',
						'Report_Params': '&prmFntPnt=1&prmBckPnt=1&pramRazr=1&paramPerson=' + options.personId + '&paramLpu=' + getGlobalOptions().lpu_id,
						'Report_Format': 'pdf'
					});
				} else {
					// двусторонняя Нет
					printBirt({
						'Report_FileName': 'f025-1u_blank_A5.rptdesign',
						'Report_Params': '&prmFntPnt=1&prmBckPnt=0&pramRazr=0&paramPerson=' + options.personId + '&paramLpu=' + getGlobalOptions().lpu_id,
						'Report_Format': 'pdf'
					});
					printBirt({
						'Report_FileName': 'f025-1u_blank_A5.rptdesign',
						'Report_Params': '&prmFntPnt=0&prmBckPnt=1&pramRazr=0&paramPerson=' + options.personId + '&paramLpu=' + getGlobalOptions().lpu_id,
						'Report_Format': 'pdf'
					});
				}
			} else {
				// A4
				if (getPolkaOptions().print_two_side && parseInt(getPolkaOptions().print_two_side) == 2) {
					// двусторонняя Да
					printBirt({
						'Report_FileName': 'f025-1u_blank_all.rptdesign',
						'Report_Params': '&prmFntPnt=1&prmBckPnt=1&paramPerson=' + options.personId + '&paramLpu=' + getGlobalOptions().lpu_id,
						'Report_Format': 'pdf'
					});
				} else {
					// двусторонняя Нет
					printBirt({
						'Report_FileName': 'f025-1u_blank_all.rptdesign',
						'Report_Params': '&prmFntPnt=1&prmBckPnt=0&paramPerson=' + options.personId + '&paramLpu=' + getGlobalOptions().lpu_id,
						'Report_Format': 'pdf'
					});
					printBirt({
						'Report_FileName': 'f025-1u_blank_all.rptdesign',
						'Report_Params': '&prmFntPnt=0&prmBckPnt=1&paramPerson=' + options.personId + '&paramLpu=' + getGlobalOptions().lpu_id,
						'Report_Format': 'pdf'
					});
				}
			}
		}
    } else {
    	if (getRegionNick() == 'ufa' && !options.before2015) {
    		var url = '&paramServiceType=' + (options.serviceTypeId ? options.serviceTypeId : 0)
				+ '&paramMedPersonal=' + (options.medPersonalId ? options.medPersonalId : 0)
				+ '&paramPerson=' + (options.personId ? options.personId : 0)
				+ '&paramLpuSectionProfile=' + (options.lpuSectionProfileId ? options.lpuSectionProfileId : 0)
				+ '&paramLpu=' + getGlobalOptions().lpu_id
				+ '&paramPayType=' + (options.payTypeId ? options.payTypeId : 0);

    		if (getPolkaOptions().print_single_list) {
				// печать ТАП на 1 листе
				printBirt({
					'Report_FileName': 'f025-1u_blank_A5.rptdesign',
					'Report_Params': '&prmFntPnt=1&prmBckPnt=1&pramRazr=0' + url,
					'Report_Format': 'pdf'
				});
			} else if (getPolkaOptions().print_format && parseInt(getPolkaOptions().print_format) == 2) {
				// A5
				if (getPolkaOptions().print_two_side && parseInt(getPolkaOptions().print_two_side) == 2) {
					// двусторонняя Да
					printBirt({
						'Report_FileName': 'f025-1u_blank_A5.rptdesign',
						'Report_Params': '&prmFntPnt=1&prmBckPnt=1&pramRazr=1' + url,
						'Report_Format': 'pdf'
					});
				} else {
					// двусторонняя Нет
					printBirt({
						'Report_FileName': 'f025-1u_blank_A5.rptdesign',
						'Report_Params': '&prmFntPnt=1&prmBckPnt=0&pramRazr=0' + url,
						'Report_Format': 'pdf'
					});
					printBirt({
						'Report_FileName': 'f025-1u_blank_A5.rptdesign',
						'Report_Params': '&prmFntPnt=0&prmBckPnt=1&pramRazr=0' + url,
						'Report_Format': 'pdf'
					});
				}
			} else {
				// A4
				if (getPolkaOptions().print_two_side && parseInt(getPolkaOptions().print_two_side) == 2) {
					// двусторонняя Да
					printBirt({
						'Report_FileName': 'f025-1u_blank.rptdesign',
						'Report_Params': '&prmFntPnt=1&prmBckPnt=1' + url,
						'Report_Format': 'pdf'
					});
				} else {
					// двусторонняя Нет
					printBirt({
						'Report_FileName': 'f025-1u_blank.rptdesign',
						'Report_Params': '&prmFntPnt=1&prmBckPnt=0' + url,
						'Report_Format': 'pdf'
					});
					printBirt({
						'Report_FileName': 'f025-1u_blank.rptdesign',
						'Report_Params': '&prmFntPnt=0&prmBckPnt=1' + url,
						'Report_Format': 'pdf'
					});
				}
			}
    		return true;
    	}
        var url = "";

        switch ( options.type ) {
            case 'EvnPL':
                url = '/?c=EvnPL&m=printEvnPLBlank';
            break;

            case 'EvnPLStom':
                url = '/?c=EvnPLStom&m=printEvnPLStomBlank';
            break;

            default:
                return false;
            break;
        }

        if ( options.lpuSectionProfileId ) {
            url = url + '&LpuSectionProfile_id=' + options.lpuSectionProfileId;
        }

        if ( options.medPersonalId ) {
            url = url + '&MedPersonal_id=' + options.medPersonalId;
        }

        if ( options.payTypeId ) {
            url = url + '&PayType_id=' + options.payTypeId;
        }

        if ( options.personId ) {
            url = url + '&Person_id=' + options.personId;
        }

        if ( options.serviceTypeId ) {
            url = url + '&ServiceType_id=' + options.serviceTypeId;
        }

        if ( options.TimetableGraf_id ) {
            url = url + '&TimetableGraf_id=' + options.TimetableGraf_id;
        }

        window.open(url, '_blank');

        return true;
    }
}

/**
 * Глобальный лоадмаск
 */
function getGlobalLoadMask(message) {
	if (message) {
		delete(sw.Promed.globalLoadMask);
	}
	if (!sw.Promed.globalLoadMask) {
		sw.Promed.globalLoadMask = new Ext.LoadMask(Ext.getBody(), { msg: message });
	}
	return sw.Promed.globalLoadMask;
}

/**
 * Печать талона амбулаторного пациента
 */
function printEvnPL(options) {
	if ( typeof options != 'object' ) {
		return false;
	}

	if ( typeof options.type != 'string' || (options.type != 'EvnPL' && options.type != 'EvnPLStom') ) {
		return false;
	}

	var url = '';

	if (Ext.isEmpty(options.EvnPL_id)) {
		if (isDebug()){
			log('Не передан идентификатор EvnPL_id');
		}
		return false;
	}

	if (getRegionNick().inlist([ 'kz' ]) && options.type == 'EvnPL') {
		// для Казахстана своя логика, вне зависимости от года
		url = '/?c=EvnPL&m=printEvnPL&EvnPL_id=' + options.EvnPL_id;
		window.open(url, '_blank');
	} else {
		// для остальных зависит от года, поэтому определяем год по ТАП
		var EvnPL_id = options.EvnPL_id;
		getGlobalLoadMask('Получение данных ТАП...').show(); // не понятно к чему привязывать loadMask, поэтому глобальный лоад маск.
		Ext.Ajax.request({
			url: '/?c=' + options.type + '&m=getEvnPLDate',
			params: {
				EvnPL_id: EvnPL_id
			},
			callback: function(opt, success, response) {
				getGlobalLoadMask().hide();

				if (success && response && response.responseText) {
					var result = Ext.util.JSON.decode(response.responseText);

					var epl_date = new Date(2015, 0, 1);
					if (result && result.EvnPL_Date) {
						epl_date = getValidDT(result.EvnPL_Date, '');
					}

					var xdate = new Date(2016, 0, 1); // для ТАП и для стомат. ТАП Перми с 1 января 2016
					if (getRegionNick() != 'perm' && options.type == 'EvnPLStom') {
						xdate = sw.Promed.EvnPL.getEvnPLStomNewBegDate(); // для стомат ТАП зависит от региона
					}

					if (epl_date >= xdate) {
						// новая печать
						if (getPolkaOptions().print_single_list) {
							// печать ТАП на 1 листе
							printBirt({
								'Report_FileName': 'f025-1u_all_A5.rptdesign',
								'Report_Params': '&prmFntPnt=1&prmBckPnt=1&pramRazr=0&s=' + EvnPL_id,
								'Report_Format': 'pdf'
							});
						} else if (getPolkaOptions().print_format && parseInt(getPolkaOptions().print_format) == 2) {
							// A5
							if (getPolkaOptions().print_two_side && parseInt(getPolkaOptions().print_two_side) == 2) {
								// двусторонняя Да
								printBirt({
									'Report_FileName': 'f025-1u_all_A5.rptdesign',
									'Report_Params': '&prmFntPnt=1&prmBckPnt=1&pramRazr=1&s=' + EvnPL_id,
									'Report_Format': 'pdf'
								});
							} else {
								// двусторонняя Нет
								printBirt({
									'Report_FileName': 'f025-1u_all_A5.rptdesign',
									'Report_Params': '&prmFntPnt=1&prmBckPnt=0&pramRazr=0&s=' + EvnPL_id,
									'Report_Format': 'pdf'
								});
								printBirt({
									'Report_FileName': 'f025-1u_all_A5.rptdesign',
									'Report_Params': '&prmFntPnt=0&prmBckPnt=1&pramRazr=0&s=' + EvnPL_id,
									'Report_Format': 'pdf'
								});
							}
						} else {
							// A4
							if (getPolkaOptions().print_two_side && parseInt(getPolkaOptions().print_two_side) == 2) {
								// двусторонняя Да
								printBirt({
									'Report_FileName': 'f025-1u_all.rptdesign',
									//'Report_Params': '&prmFntPnt=1&prmBckPnt=1&paramEvnPL=' + EvnPL_id,
									'Report_Params': '&prmFntPnt=1&prmBckPnt=1&s=' + EvnPL_id,
									'Report_Format': 'pdf'
								});
							} else {
								// двусторонняя Нет
								printBirt({
									'Report_FileName': 'f025-1u_all.rptdesign',
									//'Report_Params': '&prmFntPnt=1&prmBckPnt=0&paramEvnPL=' + EvnPL_id,
									'Report_Params': '&prmFntPnt=1&prmBckPnt=0&s=' + EvnPL_id,
									'Report_Format': 'pdf'
								});
								printBirt({
									'Report_FileName': 'f025-1u_all.rptdesign',
									//'Report_Params': '&prmFntPnt=0&prmBckPnt=1&paramEvnPL=' + EvnPL_id,
									'Report_Params': '&prmFntPnt=0&prmBckPnt=1&s=' + EvnPL_id,
									'Report_Format': 'pdf'
								});
							}
						}
					} else {
						// старая печать
						if (options.type == 'EvnPLStom') {
							url = '/?c=EvnPLStom&m=printEvnPLStom&EvnPLStom_id=' + options.EvnPL_id;
							window.open(url, '_blank');
						} else {
							if (getRegionNick() == 'penza') {
								printBirt({
									'Report_FileName': 'EvnPLPrint.rptdesign',
									'Report_Params': '&paramEvnPL=' + EvnPL_id,
									'Report_Format': 'pdf'
								});
							} else {
								url = '/?c=EvnPL&m=printEvnPL&EvnPL_id=' + EvnPL_id;
								window.open(url, '_blank');
							}
						}
					}
				}
			}
		});
	}
}

/**
 * Печать карты 003/у
 */
function printEvnPS003(options) {
	if ( typeof options != 'object' ) {
		return false;
	}

	if (Ext.isEmpty(options.EvnPS_id)) {
		if (isDebug()){
			log('Не передан идентификатор EvnPS_id');
		}
		return false;
	}

	var template = 'han_EvnPS.rptdesign';
	if (getStacOptions().evnps_print_format && getStacOptions().evnps_print_format == 2) {
		template = 'han_EvnPS_A3.rptdesign';
	}
	if(isEncrypHIVRegion()) {
		printBirt({
			'Report_FileName': template,
			'Report_Params': '&paramEvnPS=' + options.EvnPS_id + '&paramLpu=' + getGlobalOptions().lpu_id,
			'Report_Format': 'pdf'
		});
	} else {
		printBirt({
			'Report_FileName': template,
			'Report_Params': '&paramEvnPS=' + options.EvnPS_id,
			'Report_Format': 'pdf'
		});
	}
}

/**
 * Печать КВС
 */
function printEvnPS(options) {
	if ( typeof options != 'object' ) {
		return false;
	}

	if (Ext.isEmpty(options.EvnPS_id)) {
		if (isDebug()){
			log('Не передан идентификатор EvnPS_id');
		}
		return false;
	}


	var printParams = {
		'Report_Params': '&paramEvnPS=' + options.EvnPS_id,
		'Report_Format': 'pdf'
	};

	//создаём параметры если они не переданы
	['Parent_Code', 'KVS_Type', 'EvnSection_id', 'LpuUnitType_SysNick'].forEach(function(el){
		if (Ext.isEmpty(options[el])){
			options[el] = '';
		}
	});

	switch(getRegionNick()){
		case 'ekb':
		case 'penza':
		case 'vologda':
		case 'adygeya':
			printParams.Report_FileName = 'f066u02_all.rptdesign';
			printBirt(printParams);
			/*printParams.Report_FileName = 'f066u02.rptdesign';
			printBirt(printParams);
			printParams.Report_FileName = 'f066u02_oborot.rptdesign';
			printBirt(printParams);*/
			/*printBirt({
				'Report_FileName': 'f066u02.rptdesign',
				'Report_Params': '&paramEvnPS=' + options.EvnPS_id,
				'Report_Format': 'pdf'
			});
			printBirt({
				'Report_FileName': 'f066u02_oborot.rptdesign',
				'Report_Params': '&paramEvnPS=' + options.EvnPS_id,
				'Report_Format': 'pdf'
			});*/
			break;
		case 'kz':
			if ( Ext.isEmpty(options.LpuUnitType_SysNick) || options.LpuUnitType_SysNick == 'stac' ) {
				printParams.Report_FileName = 'han_EvnPS_f066u.rptdesign';
				printBirt(printParams);
				/*printBirt({
					'Report_FileName': 'han_EvnPS_f066u.rptdesign',
					'Report_Params': '&paramEvnPS=' + options.EvnPS_id,
					'Report_Format': 'pdf'
				});*/
			} else {
				printParams.Report_FileName = 'han_EvnPS_f066_4u2.rptdesign';
				printBirt(printParams);
				/*printBirt({
					'Report_FileName': 'han_EvnPS_f066_4u2.rptdesign',
					'Report_Params': '&paramEvnPS=' + options.EvnPS_id,
					'Report_Format': 'pdf'
				});*/
			}
			break;
		default:
			window.open('/?c=EvnPS&m=printEvnPS&EvnPS_id=' + options.EvnPS_id + '&Parent_Code=' + options.Parent_Code + '&KVS_Type=' + options.KVS_Type + '&EvnSection_id=' + options.EvnSection_id, '_blank');
			break;
	}
}

function printControlCardZno(Evn_id, EvnDiag_id)
{
	if (isMseDepers()) {
		return false;
	}
	if (Evn_id)
	{
		printBirt({
			'Report_FileName': 'CheckList_MedCareOnkoPatients.rptdesign',
			'Report_Params': '&Evn_id=' + Evn_id + (EvnDiag_id ? '&EvnDiag_id=' + EvnDiag_id : ''),
			'Report_Format': 'pdf'
		});
	}
}

function printControlCardOnko(Evn_id, EvnDiag_id)
{
	if (isMseDepers()) {
		return false;
	}
	if (Evn_id)
	{
		printBirt({
			'Report_FileName': 'WritingOut_MedCareOnkoPatients.rptdesign',
			'Report_Params': '&Evn_id=' + Evn_id + (EvnDiag_id ? '&EvnDiag_id=' + EvnDiag_id : ''),
			'Report_Format': 'pdf'
		});
	}
}

function printRankinScale(EvnPS_id)
{
	if (EvnPS_id)
	{
		printBirt({
			'Report_FileName': 'print_rankinscale.rptdesign',
			'Report_Params': '&paramEvnPS=' + EvnPS_id,
			'Report_Format': 'pdf'
		});
	}
}

/**
 * Установить фильтр на swLpuFilialGlobalStore
 * options - параметры фильтрации
 */
function setLpuFilialGlobalStoreFilter(options) {
	if ( typeof swLpuFilialGlobalStore != 'object' ) {
		return false;
	}

	swLpuFilialGlobalStore.clearFilter();

	if ( typeof options != 'object' ) {
		options = new Object();
	}

	swLpuFilialGlobalStore.filterBy(function(record) {
		// Фильтруем по датам
		if ( options.onDate ) {
			var
				cur_date = Date.parseDate(options.onDate, 'd.m.Y'),
				beg_date = Date.parseDate(record.get('LpuFilial_begDate'), 'd.m.Y'),
				end_date = Date.parseDate(record.get('LpuFilial_endDate'), 'd.m.Y');

			if ( !Ext.isEmpty(cur_date) ) {
				if ( (!Ext.isEmpty(beg_date) && beg_date >= cur_date) || (!Ext.isEmpty(end_date) && end_date <= cur_date) ) {
					return false;
				}
			}
		}
		else {
			if ( !Ext.isEmpty(options.dateFrom) ) {
				var
					dateFrom = Date.parseDate(options.dateFrom, 'd.m.Y'),
					end_date = Date.parseDate(record.get('LpuFilial_endDate'), 'd.m.Y');

				if ( !Ext.isEmpty(dateFrom) && !Ext.isEmpty(end_date) && end_date < dateFrom ) {
					return false;
				}
			}

			if ( !Ext.isEmpty(options.dateTo) ) {
				var
					dateTo = Date.parseDate(options.dateTo, 'd.m.Y'),
					beg_date = Date.parseDate(record.get('LpuFilial_begDate'), 'd.m.Y');

				if ( !Ext.isEmpty(dateTo) && !Ext.isEmpty(beg_date) && beg_date > dateTo ) {
					return false;
				}
			}
		}

		// Фильтруем по МО
		if ( options.Lpu_id ) {
			if ( record.get('Lpu_id') != options.Lpu_id ) {
				return false;
			}
		}

		// Фильтруем по ids
		if ( options.ids && Ext.isArray(options.ids) ) {
			if ( !options.ids.in_array(record.get('LpuFilial_id')) ) {
				return false;
			}
		}

		// Фильтруем по exceptIds
		if ( options.exceptIds && Ext.isArray(options.exceptIds) ) {
			if ( options.exceptIds.in_array(record.get('LpuFilial_id')) ) {
				return false;
			}
		}

		// Фильтруем по id
		if ( options.id ) {
			if ( record.get('LpuFilial_id') != options.id ) {
				return false;
			}
		}

		return true;
	});

	return true;
}


/**
 * Установить фильтр на swLpuSectionGlobalStore
 * options - параметры фильтрации
 */
function setLpuSectionGlobalStoreFilter(options,external_store) {
	if(typeof external_store != 'object' && typeof swLpuSectionGlobalStore != 'object' ){
		return false;
	}
	if ( typeof options != 'object' ) {
		options = new Object();
	}
	
	var filterFunction = function(record) {
		//TODO: (LpuSectionProfile_id == 75) надо переделать на (LpuSectionProfile_SysNick == 'priem')
		var filter_flag = true;

		if ( typeof options.exactIdList == 'object' && record.get('LpuSection_id').inlist(options.exactIdList) ) {
			return true;
		}
		// Фильтруем по датам
		if ( options.onDate ) {
			var cur_date = Date.parseDate(options.onDate, 'd.m.Y');
			var dis_date = Date.parseDate(record.get('LpuSection_disDate'), 'd.m.Y');
			var set_date = Date.parseDate(record.get('LpuSection_setDate'), 'd.m.Y');

			if ( !Ext.isEmpty(cur_date) ) {
				if ( (!Ext.isEmpty(set_date) && set_date > cur_date) || (!Ext.isEmpty(dis_date) && dis_date < cur_date) ) {
					filter_flag = false;
				}

				if (options.ldcFilterDate && swLpuDispContractStore) {
					var isActual = false;
					swLpuDispContractStore.findBy(function(rec) {
						if (rec.get('Lpu_oid') == record.get('Lpu_id') && (rec.get('LpuSection_id') == record.get('LpuSection_id') || rec.get('LpuSectionProfile_id') == record.get('LpuSectionProfile_id'))) {
							var set_date = Date.parseDate(rec.get('LpuDispContract_setDate'), 'd.m.Y');
							var dis_date = Date.parseDate(rec.get('LpuDispContract_disDate'), 'd.m.Y');
							if ( (Ext.isEmpty(set_date) || set_date <= cur_date) && (Ext.isEmpty(dis_date) || dis_date >= cur_date) ) {
								isActual = true;
							}
						}
					});
					if (!isActual) filter_flag = false;
				}
			}
		}
		else {
			if ( !Ext.isEmpty(options.dateFrom) ) {
				var dateFrom = Date.parseDate(options.dateFrom, 'd.m.Y');
				var dis_date = Date.parseDate(record.get('LpuSection_disDate'), 'd.m.Y');

				if ( !Ext.isEmpty(dateFrom) && !Ext.isEmpty(dis_date) && dis_date < dateFrom ) {
					filter_flag = false;
				}
			}

			if ( !Ext.isEmpty(options.dateTo) ) {
				var dateTo = Date.parseDate(options.dateTo, 'd.m.Y');
				var set_date = Date.parseDate(record.get('LpuSection_setDate'), 'd.m.Y');

				if ( !Ext.isEmpty(dateTo) && !Ext.isEmpty(set_date) && set_date > dateTo ) {
					filter_flag = false;
				}
			}
		}

		// Убираем детские отделения
		if ( options.WithoutChildLpuSectionAge ) {
			if (record.get('LpuSectionAge_id') == 2) {
				filter_flag = false;
			}
		}

		// Фильтруем отделения ЛЛО
		if ( options.isDlo ) {
			if ( Ext.isEmpty(record.get('LpuSectionProfile_SysNick')) || record.get('LpuSectionProfile_SysNick') == 'priem' || (!record.get('LpuUnitType_SysNick').inlist([ 'polka', 'fap', 'ccenter' ])) || (getRegionNick() == 'msk' && Ext.isEmpty(record.get('LpuPeriodDLO_Code'))) ) {
				filter_flag = false;
			}
		}

		// Фильтруем поликлинические отделения
		if ( options.isPolka ) {
			if ( Ext.isEmpty(record.get('LpuSectionProfile_SysNick')) || !record.get('LpuUnitType_SysNick').inlist([ 'polka', 'ccenter', 'traumcenter', 'fap']) ) {
				if( record.get('LpuSectionProfile_SysNick') != 'priem' && record.get('LpuUnitType_SysNick') != 'stac' ){ // добавил приемные отделения стационара #185842
					filter_flag = false;
				}
			}
			// Убираем стоматологические отделения
			else {
				switch ( getRegionNick() ) {
					case 'ufa':
						if ( !options.isDisp && record.get('LpuSectionProfile_Code').toString().inlist([ '526', '562', '626', '662', '527', '528', '529', '530', '559', '560', '561', '627', '628', '629', '630', '659', '660', '661', '827', '828', '829', '830', '859', '860', '861' ]) ) {
							filter_flag = false;
						}
					break;

					case 'perm':
						if ( !options.isDisp && record.get('LpuSectionProfile_Code').toString().inlist([ '1800', '1810', '1820', '1830', '1840', '1850', '85', '89', '90', '86', '63', '87', '88', '171', '1803', '1801', '1811', '1802', '1812', '1860', '7181', '7182' ]) ) {
							filter_flag = false;
						}
					break;
				}
			}
		}

		// Фильтруем не поликлинические отделения
		if ( options.isNotPolka ) {
			// Добавлены возможность выбора отделений ФАП (LpuUnitType_id = 12) - #6241 (c) Night
			if ( record.get('LpuUnitType_SysNick').inlist([ 'polka', 'ccenter', 'traumcenter', 'fap' ]) ) {
				filter_flag = false;
			}
		}

		// Фильтруем стоматологические отделения
		if ( options.isStom ) {
			if ( Ext.isEmpty(record.get('LpuSectionProfile_SysNick')) || record.get('LpuSectionProfile_SysNick') == 'priem' || !record.get('LpuUnitType_SysNick').inlist([ 'polka', 'ccenter', 'traumcenter', 'fap' ]) ) {
				filter_flag = false;
			}
			else {
				switch ( getRegionNick() ) {
					case 'ufa':
						// Добавлены дополнительные коды профилей
						// https://redmine.swan.perm.ru/issues/14502
						if ( !record.get('LpuSectionProfile_Code').toString().inlist([ '526', '562', '626', '662', '527', '528', '529', '530', '559', '560', '561', '627', '628', '629', '630', '659', '660', '661', '827', '828', '829', '830', '859', '860', '861' ]) ) {
							filter_flag = false;
						}
					break;

					case 'perm':
						if ( !record.get('LpuSectionProfile_Code').toString().inlist([ '1800', '1810', '1820', '1830', '1840', '1850', '85', '89', '90', '86', '63', '87', '88', '171', '1803', '1801', '1811', '1802', '1812', '1860', '7181', '7182' ]) ) {
							filter_flag = false;
						}
						/*if (
							record.get('LpuSectionProfile_Code').toString().length < 2
							|| (record.get('LpuSectionProfile_Code').toString().substr(0, 2) != '18' && !record.get('LpuSectionProfile_Code').toString().inlist([ '7181', '7182' ]))
						) {
							filter_flag = false;
						}*/
					break;
				}
			}
		}

		/* Фильтруем поликлинические отделения, но для детей сирот еще нужны стоматологические отделения,
		*  поэтому убрал отсеивание по условию (record.get('LpuSectionProfile_Code') >= 1800 && record.get('LpuSectionProfile_Code') <= 1870)
		*/
		if ( options.isPolkaAndStom ) {
			if ( Ext.isEmpty(record.get('LpuSectionProfile_SysNick')) || record.get('LpuSectionProfile_SysNick') == 'priem' || !record.get('LpuUnitType_SysNick').toString().inlist([ 'polka', 'ccenter', 'traumcenter', 'fap' ]) ) {
				filter_flag = false;
			}
		}

		/**
		 * Полка, стоматка или другое
		 */
		if ( options.isPolkaAndStomAndOther ) {
			if ( Ext.isEmpty(record.get('LpuSectionProfile_SysNick')) || record.get('LpuSectionProfile_SysNick') == 'priem' || !record.get('LpuUnitType_SysNick').toString().inlist([ 'polka', 'ccenter', 'traumcenter', 'fap', 'other' ]) ) {
				filter_flag = false;
			}
		}

		/**
		 *	Фильтруем отделения для заполнения протоколов патоморфогистологических исследований
		 *	Поликлиника, стационар, параклиника, ФАП, энисинг элс?
		 */
		if ( options.isHisto ) {
			if (
				Ext.isEmpty(record.get('LpuSectionProfile_SysNick'))
				|| record.get('LpuSectionProfile_SysNick') == 'priem'
				|| !(record.get('LpuUnitType_SysNick').toString().inlist([ 'stac', 'polka', 'dstac', 'hstac', 'pstac', 'parka', 'ccenter', 'traumcenter', 'smp', 'patan' ]))
			) {
				filter_flag = false;
			}
		}

		// Фильтруем приемные отделения поликлиники
		if ( options.isPolkaReception ) {
			if ( Ext.isEmpty(record.get('LpuSectionProfile_SysNick')) || record.get('LpuSectionProfile_SysNick') != 'priem' || record.get('LpuUnitType_SysNick') != 'polka' ) {
				filter_flag = false;
			}
		}

		// Фильтруем приемные отделения стационара
		if ( options.isStacReception ) {
			if ( Ext.isEmpty(record.get('LpuSectionProfile_SysNick')) || Ext.isEmpty(record.get('LpuUnitType_SysNick'))
				|| !record.get('LpuUnitType_SysNick').toString().inlist([ 'stac', 'dstac', 'hstac', 'pstac', 'priem' ])
			) {
				filter_flag = false;
			}
			else if ( getRegionNick() == 'astra' ) {
				if (
					(record.get('LpuUnitType_SysNick') != 'priem' && record.get('LpuSectionProfile_SysNick').toString() != 'priem')
					|| (record.get('LpuUnitType_SysNick') == 'priem' && record.get('LpuSectionProfile_SysNick').toString() != 'priem' && record.get('LpuSectionProfile_Code') != '303')
				) {
					filter_flag = false;
				}
			}
			else if ( getRegionNick().inlist([ 'ekb', 'kareliya', 'krym', 'penza', 'pskov' ]) ) {
				if ( record.get('LpuSectionProfile_SysNick').toString() != 'priem' && record.get('LpuSectionProfile_Code') != '160' ) {
					filter_flag = false;
				}
			}
			else {
				if ( record.get('LpuSectionProfile_SysNick').toString() != 'priem' ) {
					filter_flag = false;
				}
			}
		}

		// Фильтруем приемные отделения параклиники
		if ( options.isParkaReception ) {
			if ( Ext.isEmpty(record.get('LpuSectionProfile_SysNick')) || record.get('LpuSectionProfile_SysNick') != 'priem' || record.get('LpuUnitType_SysNick') != 'parka' ) {
				filter_flag = false;
			}
		}

		// Фильтруем отделения параклиники
		if ( options.isParka ) {
			if ( record.get('LpuUnitType_SysNick') != 'parka' ) {
				filter_flag = false;
			}
		}

		// Фильтруем по коду профиля отделения
		if ( options.lpuSectionProfileCode ) {
			if ( record.get('LpuSectionProfile_Code').toString().length < options.lpuSectionProfileCode.toString().length || record.get('LpuSectionProfile_Code').toString().substr(0, options.lpuSectionProfileCode.toString().length) != options.lpuSectionProfileCode.toString() ) {
				filter_flag = false;
			}
		}

		// Фильтруем по списку кодов профилей
		if ( options.arrayLpuSectionProfile ) {
			if ( !(record.get('LpuSectionProfile_Code').inlist(options.arrayLpuSectionProfile)) ) {
				filter_flag = false;
			}
		}
		// Фильтруем по списку кодов профилей (выбираем те, которые НЕ входят в указанный список)
		if ( options.arrayLpuSectionProfileNot ) {
			if ( (record.get('LpuSectionProfile_Code').inlist(options.arrayLpuSectionProfileNot)) ) {
				filter_flag = false;
			}
		}

		// Фильтруем по списку кодов типов подразделений
		if ( options.arrayLpuUnitType ) {
			if ( !(record.get('LpuUnitType_Code').inlist(options.arrayLpuUnitType)) ) {
				filter_flag = false;
			}
		}

		// Фильтруем по списку идентификаторов типов подразделений
		if ( options.arrayLpuUnitTypeId ) {
			if ( !(record.get('LpuUnitType_id').inlist(options.arrayLpuUnitTypeId)) ) {
				filter_flag = false;
			}
		}

		// Фильтруем отделения стационара
		if ( options.isStac ) {
			if ( !(record.get('LpuUnitType_SysNick').inlist([ 'stac', 'dstac', 'hstac', 'pstac', 'priem' ])) ) {
				filter_flag = false;
			}
		}

		if( options.isOnlyStac){
			if ( !(record.get('LpuUnitType_SysNick').inlist([ 'stac'])) ) {
				filter_flag = false;
			}
		}

		if( options.isOnlyPolka){
			if ( !(record.get('LpuUnitType_SysNick').inlist([ 'polka'])) ) {
				filter_flag = false;
			}
		}

		// Фильтруем отделения, кроме стационара
		if ( options.isNotStac ) {
			if ( record.get('LpuUnitType_SysNick').inlist([ 'stac', 'dstac', 'hstac', 'pstac', 'priem' ]) ) {
				filter_flag = false;
			}
		}

		// Фильтруем отделения стационара и поликлиники
		if ( options.isStacAndPolka ) {
			if ( !(record.get('LpuUnitType_SysNick').inlist([ 'polka', 'stac', 'dstac', 'hstac', 'pstac', 'ccenter', 'traumcenter', 'fap', 'priem' ])) ) {
				filter_flag = false;
			}
			// Если поликлиника, то убираем стоматологические отделения
			else if ( record.get('LpuUnitType_SysNick').inlist([ 'polka', 'ccenter', 'traumcenter', 'fap' ]) ) {
				if ( record.get('LpuSectionProfile_SysNick') == 'priem' ) {
					filter_flag = false;
				}
				else {
					switch ( getRegionNick() ) {
						case 'ufa':
							if ( record.get('LpuSectionProfile_Code').toString().inlist([ '526', '562', '626', '662', '527', '528', '529', '530', '559', '560', '561', '627', '628', '629', '630', '659', '660', '661', '827', '828', '829', '830', '859', '860', '861' ]) ) {
								filter_flag = false;
							}
						break;

						case 'perm':
							if ( record.get('LpuSectionProfile_Code').toString().inlist([ '1800', '1810', '1820', '1830', '1840', '1850', '85', '89', '90', '86', '63', '87', '88', '171', '1803', '1801', '1811', '1802', '1812', '1860', '7181', '7182' ]) ) {
								filter_flag = false;
							}
						break;
					}
				}
			}
		}

		// Фильтруем по МО
		if ( options.Lpu_id ) {
			if ( record.get('Lpu_id') != options.Lpu_id ) {
				filter_flag = false;
			}
		}

		// Фильтруем по организации
		if ( options.Org_id ) {
			if ( record.get('Org_id') != options.Org_id ) {
				filter_flag = false;
			}
		}

		// Фильтруем по сторонним специалистам
		if ( options.isAliens ) {
			if ( record.get('Lpu_id') == getGlobalOptions().lpu_id ) {
				filter_flag = false;
			}
		}
		else {
			if ( !record.get('Lpu_id').inlist(typeof getGlobalOptions().linkedLpuIdList == 'object' ? getGlobalOptions().linkedLpuIdList : []) ) {
				filter_flag = false;
			}
		}

		// Фильтруем отделения по ids
		if ( options.ids && Ext.isArray(options.ids) ) {
			if ( !options.ids.in_array(record.get('LpuSection_id')) ) {
				filter_flag = false;
			}
		}

		// Фильтруем отделения по exceptIds
		if ( options.exceptIds && Ext.isArray(options.exceptIds) ) {
			if ( options.exceptIds.in_array(record.get('LpuSection_id')) ) {
				filter_flag = false;
			}
		}

		// Фильтруем отделения по id
		if ( options.id ) {
			if ( record.get('LpuSection_id') != options.id ) {
				filter_flag = false;
			}
		}

		// Фильтруем отделения по pid
		if ( options.pid ) {
			if ( record.get('LpuSection_pid') != options.pid ) {
				filter_flag = false;
			}
		}

		// Фильтрация подотделений:
		if ( typeof options.allowLowLevel == 'string' ) {
			// загружаем только подотделения
			if ( options.allowLowLevel == 'only' && !record.get('LpuSection_pid') ) {
				filter_flag = false;
			}
			else {
				// загружаем весь список
			}
		}
		// загружаем только отделения
		else if ( record.get('LpuSection_pid') ) {
			filter_flag = false;
		}

		// Фильтруем по подразделению
		if ( !Ext.isEmpty(options.LpuBuilding_id) ) {
			if ( record.get('LpuBuilding_id') != options.LpuBuilding_id ) {
				filter_flag = false;
			}
		}

		// Фильтруем по коду подразделения ТФОМС
		if ( !Ext.isEmpty(options.LpuUnitSet_id) ) {
			if ( record.get('LpuUnitSet_id') != options.LpuUnitSet_id ) {
				filter_flag = false;
			}
		}

		// Фильтруем по группе отделений
		if ( !Ext.isEmpty(options.LpuUnit_id) ) {
			if ( record.get('LpuUnit_id') != options.LpuUnit_id ) {
				filter_flag = false;
			}
		}

        if ( !Ext.isEmpty(options.LpuSection_id) ) {
            if ( record.get('LpuSection_id') != options.LpuSection_id ) {
                filter_flag = false;
            }
        }

		if ( filter_flag ) {
			return true;
		}
		else {
			return false;
		}
	};

	if ( typeof external_store == 'object' ) {
		external_store.clearFilter();
		external_store.filterBy(filterFunction);
	}
	else if ( typeof swLpuSectionGlobalStore == 'object' ) {
		swLpuSectionGlobalStore.clearFilter();
		swLpuSectionGlobalStore.filterBy(filterFunction);
	}

	return true;
}


/**
 * Установить фильтр на swLpuSectionWardGlobalStore
 * options - параметры фильтрации
 */
function setLpuSectionWardGlobalStoreFilter(options) {
	swLpuSectionWardGlobalStore.clearFilter();

	if ( typeof options != 'object' ) {
		options = new Object();
	}

	swLpuSectionWardGlobalStore.filterBy(function(record) {
		var filter_flag = true;

		// Фильтруем палаты по отделению
		if ( options.LpuSection_id && options.LpuSection_id > 0 ) {
			if ( options.LpuSection_id != record.get('LpuSection_id') ) {
				filter_flag = false;
			}
		}

		// Фильтруем по датам
		if ( options.onDate ) {
			var cur_date = Date.parseDate(options.onDate, 'd.m.Y');
			var dis_date = Date.parseDate(record.get('LpuSectionWard_disDate'), 'd.m.Y');
			var set_date = Date.parseDate(record.get('LpuSectionWard_setDate'), 'd.m.Y');
			//log(cur_date);
			//log(dis_date);
			//log(set_date);
			if ( cur_date ) {
				if ( (set_date && set_date > cur_date) || (dis_date && dis_date < cur_date) ) {
					filter_flag = false;
				}
			}
		}

		// Фильтруем палаты по ids
		if ( options.ids && Ext.isArray(options.ids) ) {
			if ( !options.ids.in_array(record.get('LpuSectionWard_id')) ) {
				filter_flag = false;
			}
		}

		// Фильтруем палаты по id
		if ( options.id ) {
			if ( record.get('LpuSectionWard_id') != options.id ) {
				filter_flag = false;
			}
		}

		if ( filter_flag ) {
			return true;
		}
		else {
			return false;
		}
	});

	return true;
}

/**
 * Вывод информации о количестве новых заявок на прикрепелние
 */
function getCountNewDemand() {
	//Временно закомменчено до лучших времен
	/*Ext.Ajax.request({
		failure: function(response, options) {
			Ext.Msg.alert(langs('Ошибка'), langs('Произошла ошибка при проверке заявок на прикрепление.'));
		},
		success: function(response, options) {
			var response_obj = Ext.util.JSON.decode(response.responseText);
			if (response_obj['cnt'] > 0) Ext.Msg.alert(langs('Новые заявки'), langs('Доступно новых заявлений на прикрепление: ') + response_obj['cnt']);
		},
		url: '/?c=Demand&m=getCountNewDemand'
	});*/
}

/**
 * Установить фильтр на swMedStaffFactGlobalStore
 * options - параметры фильтрации
 */
function setMedStaffFactGlobalStoreFilter(options,external_store,returnfilterFn) {
	if(typeof external_store != 'object' && typeof swMedStaffFactGlobalStore != 'object' ){
		return false;
	}
	if ( typeof options != 'object' ) {
		options = new Object();
	}

	// По-умолчанию отсеиваем
	if ( options.disableInDoc == undefined ) {
		options.disableInDoc = true;
	}

	// Массив для фильтрации мест работы врача в одном отделении
	var distinctMPList = new Array();

	// Вынес массив с кодами врачебных специальностей, ибо для Казахстана свой набор кодов
	// @task https://redmine.swan.perm.ru/issues/78975
	var
		doctorCodes = new Array(),
		doctorFedAkCodes = new Array();

	if ( getRegionNick() == 'kz' ) {
		if ( IS_DEBUG == 1 ) {
			doctorCodes = [ 2, 3, 6, 48, 168, 262, 263, 264, 287, 10008, 10209, 10214, 10227, 10228, 10229 ];
			doctorFedAkCodes = [ 2, 3, 4, 6, 48, 111, 112, 113, 116, 117, 120, 168, 262, 263, 264, 287, 10008, 10209, 10214, 10227, 10228, 10229, 4510209, 4510217 ];
		}
		else {
			doctorCodes = [ 2, 3, 5, 6, 171, 172, 173, 178, 10008, 10209, 10214, 10227, 10228, 10229 ];
			doctorFedAkCodes = [ 2, 3, 5, 6, 104, 105, 109, 111, 171, 172, 173, 178, 356, 362, 364, 10008, 10209, 10214, 10227, 10228, 10229, 4510209, 4510217 ];
		}
	}
	else {
		doctorCodes = [ 6, 48, 168, 216, 262, 263, 264, 287, 10002, 10236, 10240 ];
		doctorFedAkCodes = [ 4, 6, 48, 111, 112, 116, 117, 120, 168, 216, 262, 263, 264, 287, 10002, 10236, 10240, 4510209, 4510217 ];
	}

	var midMedPersonalCodes = [ 115 ];

	var filterFunction = function(record) {
		if ( options.disableInDoc ) {
			if ( record.get('MedStaffFactCache_IsDisableInDoc') == 2 ) {
				return false;
			}
		}

		if ( typeof options.exactIdList == 'object' && record.get('MedStaffFact_id').inlist(options.exactIdList) ) {
			return true;
		}

		// Фильтруем по датам
		if ( !Ext.isEmpty(options.onDate) ) {
			var onDate = Date.parseDate(options.onDate, 'd.m.Y');
			var ls_dis_date = Date.parseDate(record.get('LpuSection_disDate'), 'd.m.Y');
			var ls_set_date = Date.parseDate(record.get('LpuSection_setDate'), 'd.m.Y');
			var mp_beg_date = Date.parseDate(record.get('WorkData_begDate'), 'd.m.Y');
			var mp_end_date = Date.parseDate(record.get('WorkData_endDate'), 'd.m.Y');

			if ( !Ext.isEmpty(onDate) ) {
				if ( (!Ext.isEmpty(mp_beg_date) && mp_beg_date > onDate) || (!Ext.isEmpty(mp_end_date) && mp_end_date < onDate) ||
					(!Ext.isEmpty(ls_set_date) && ls_set_date > onDate) || (!Ext.isEmpty(ls_dis_date) && ls_dis_date < onDate) /*||
					(ls_set_date > mp_beg_date) || (ls_dis_date < mp_end_date)*/ )
				{
					return false;
				}

				if (options.ldcFilterDate && swLpuDispContractStore) {
					var isActual = false;
					swLpuDispContractStore.findBy(function(rec) {
						if (rec.get('Lpu_oid') == record.get('Lpu_id') && (rec.get('LpuSection_id') == record.get('LpuSection_id') || rec.get('LpuSectionProfile_Code') == record.get('LpuSectionProfile_Code'))) {
							var set_date = Date.parseDate(rec.get('LpuDispContract_setDate'), 'd.m.Y');
							var dis_date = Date.parseDate(rec.get('LpuDispContract_disDate'), 'd.m.Y');
							if ( (Ext.isEmpty(set_date) || set_date <= onDate) && (Ext.isEmpty(dis_date) || dis_date >= onDate) ) {
								isActual = true;
							}
						}
					});
					if (!isActual) return false;
				}
			}
		}
		else {
			if ( !Ext.isEmpty(options.dateFrom) ) {
				var dateFrom = Date.parseDate(options.dateFrom, 'd.m.Y');
				var mp_end_date = Date.parseDate(record.get('WorkData_endDate'), 'd.m.Y');

				if ( !Ext.isEmpty(dateFrom) && !Ext.isEmpty(mp_end_date) && mp_end_date < dateFrom ) {
					return false;
				}
			}

			if ( !Ext.isEmpty(options.dateTo) ) {
				var dateTo = Date.parseDate(options.dateTo, 'd.m.Y');
				var mp_beg_date = Date.parseDate(record.get('WorkData_begDate'), 'd.m.Y');

				if ( !Ext.isEmpty(dateTo) && !Ext.isEmpty(mp_beg_date) && mp_beg_date > dateTo ) {
					return false;
				}
			}
		}

		// Фильтруем по датам окончания работы
		if ( !Ext.isEmpty(options.onEndDate) ) {
			var onEndDate = Date.parseDate(options.onEndDate, 'd.m.Y');
			var mp_end_date = Date.parseDate(record.get('WorkData_endDate'), 'd.m.Y');

			if ( !Ext.isEmpty(onEndDate) ) {
				if ( !Ext.isEmpty(mp_end_date) && mp_end_date < onEndDate ) {
					return false;
				}
			}
		}

		// Для случаев, когда нужна фильтрация только по дате и признаку MedStaffFactCache_IsDisableInDoc
		if ( options.all == true ) {
			return true;
		}

		// Если withoutLpuSection = true, то в эту ветку не зайдет
		// По-умолчанию должен заходить
		if ( !options.withoutLpuSection ) {
			if ( Ext.isEmpty(record.get('LpuSection_id')) ) {
				return false;
			}
		}

		// Убираем врачей детских отделений
		if ( options.WithoutChildLpuSectionAge ) {
			if (record.get('LpuSectionAge_id') == 2) {
				return false;
			}
		}

		// Фильтруем врачей ЛЛО
		//options.fromRecept
		if ( options.isDlo ) {
			var add_if = (record.get('MedPersonal_DloCode').toString().length == 0);
			if(getRegionNick() == 'ufa' && !options.fromRecept)
			{
				add_if = (record.get('MedPersonal_TabCode').toString().length == 0);
			}
			if (
				add_if
				|| record.get('LpuSectionProfile_SysNick') == 'priem'
				|| !record.get('LpuUnitType_SysNick').toString().inlist([ 'polka', 'fap', 'ccenter' ])
			) {
				return false;
			}

			if ( !Ext.isEmpty(options.onDate) && !getRegionNick().inlist([ 'saratov'/*, 'ufa' */]) ) { //Исключил Уфу в рамках задачи https://redmine.swan.perm.ru/issues/98008
				var dloBegDate = Date.parseDate(record.get('WorkData_dloBegDate'), 'd.m.Y');
				var dloEndDate = Date.parseDate(record.get('WorkData_dloEndDate'), 'd.m.Y');
				var dloOnDate = Date.parseDate(options.onDate, 'd.m.Y');

				if ( !Ext.isEmpty(dloOnDate) ) {
					if (
                        typeof dloBegDate != 'object'
                        || dloBegDate > dloOnDate
                        || (dloEndDate && dloEndDate < dloOnDate)
					) {
						return false;
					}
				}
			}

			if ( !Ext.isEmpty(options.onDate) && getRegionNick() == 'msk' && Ext.isEmpty(record.get('LpuPeriodDLO_Code')) ) {
				return false;
			}
		}

		if ( options.isDoctorOrMidMedPersonal ) {
			if (
				record.get('PostKind_id') != 1 && !Number(record.get('PostMed_Code')).inlist(doctorCodes)
				&& record.get('PostKind_id') != 6 && !Number(record.get('PostMed_Code')).inlist(midMedPersonalCodes)
			) {
				return false;
			}
		}

		// Только врачи
		if ( options.isDoctor
			&& (
				!record.get('LpuUnitType_SysNick').toString().inlist([ 'polka', 'ccenter', 'traumcenter', 'fap' ]) // если не поликлиника
				//|| (Ext.globalOptions.polka.enable_is_doctor_filter && Number(Ext.globalOptions.polka.enable_is_doctor_filter) == 1) // или для поликлиники - только при включенной настройке
			)
		) {
			// Добавил в список врачей заведующих (PostMed_Code = 6)
			// https://redmine.swan.perm.ru/issues/28754

			// Ещё фельдшер 117

			// добавил дополнительно по задаче #45504:
			/*
			 Заведующий	6
			 Заместитель главного врача по поликлинике	216
			 Заведующий отделением	262
			 Заведующий отделом	263
			 Заведующий центром	264
			 Начальник отдела	287
			 Врач-педиатр городской (районный)	48
			 Руководитель отдела	10236
			 Руководитель отделения	10240
			 Главный врач (начальник) медицинской организации 	10002
			 */

			if ( record.get('PostKind_id') != 1 && (Ext.isEmpty(record.get('PostMed_Code')) || !record.get('PostMed_Code').inlist(doctorCodes)) ) {
				return false;
			}
		}

		if ( typeof options.EvnClass_SysNick == 'string' && options.EvnClass_SysNick.inlist([ 'EvnSection', 'EvnVizit' ]) ) {
			switch ( options.EvnClass_SysNick ) {
				/*case 'EvnSection':
					if ( !Ext.isEmpty(Ext.globalOptions.medpers.allowed_medpersonal_es) && Ext.globalOptions.medpers.allowed_medpersonal_es != 'full_list' ) {
						if (
							Ext.globalOptions.medpers.allowed_medpersonal_es == 'doct_only'
							&& record.get('PostKind_id') != 1
							&& (Ext.isEmpty(record.get('PostMed_Code')) || !record.get('PostMed_Code').inlist(doctorCodes))
						) {
							return false;
						}
						else if (
							Ext.globalOptions.medpers.allowed_medpersonal_es == 'doct_feld_ak'
							&& record.get('PostKind_id') != 1
							&& (Ext.isEmpty(record.get('PostMed_Code')) || !record.get('PostMed_Code').inlist(doctorFedAkCodes))
						) {
							return false;
						}
						else if (
							Ext.globalOptions.medpers.allowed_medpersonal_es == 'all'
							&& record.get('PostKind_id') != 1
							&& record.get('PostKind_id') != 6
							&& (Ext.isEmpty(record.get('PostMed_Code')) || !record.get('PostMed_Code').inlist(doctorFedAkCodes))
						) {
							return false;
						}
					}
				break;

				case 'EvnVizit':
					if ( !Ext.isEmpty(Ext.globalOptions.medpers.allowed_medpersonal_ev) && Ext.globalOptions.medpers.allowed_medpersonal_ev != 'full_list' ) {
						if (
							Ext.globalOptions.medpers.allowed_medpersonal_ev == 'doct_only'
							&& record.get('PostKind_id') != 1
							&& (Ext.isEmpty(record.get('PostMed_Code')) || !record.get('PostMed_Code').inlist(doctorCodes))
						) {
							return false;
						}
						else if (
							Ext.globalOptions.medpers.allowed_medpersonal_ev == 'doct_feld_ak'
							&& record.get('PostKind_id') != 1
							&& (Ext.isEmpty(record.get('PostMed_Code')) || !record.get('PostMed_Code').inlist(doctorFedAkCodes))
						) {
							return false;
						}
						else if (
							Ext.globalOptions.medpers.allowed_medpersonal_ev == 'all'
							&& record.get('PostKind_id') != 1
							&& record.get('PostKind_id') != 6
							&& (Ext.isEmpty(record.get('PostMed_Code')) || !record.get('PostMed_Code').inlist(doctorFedAkCodes))
						) {
							return false;
						}
					}
				break;*/
				case 'EvnSection':
					if ( !Ext.isEmpty(Ext.globalOptions.medpers.allowed_medpersonal_es) ) {
						var postKinds = Ext.globalOptions.medpers.allowed_medpersonal_es.split(',');
						if (
							!record.get('PostKind_id').inlist(postKinds)
						) {
							return false;
						}
					}
				break;

				case 'EvnVizit':
					if ( !Ext.isEmpty(Ext.globalOptions.medpers.allowed_medpersonal_ev) && Ext.globalOptions.medpers.allowed_medpersonal_ev ) {
						var postKinds = Ext.globalOptions.medpers.allowed_medpersonal_ev.split(',');
						if (
							!record.get('PostKind_id').inlist(postKinds)
						) {
							return false;
						}
					}
				break;
			}
		}

		// Только определнный список специальностей
		// https://redmine.swan.perm.ru/issues/40561
		// Замена PostMed_Code на frmpEntry_id
		// https://redmine.swan.perm.ru/issues/42785
		// Доработал условие
		// https://redmine.swan.perm.ru/issues/44444
		if ( options.isPriemMedPers ) {
			if ( record.get('PostKind_id') != 1 && (Ext.isEmpty(record.get('frmpEntry_id')) || !record.get('frmpEntry_id').inlist([ 111, 112, 114, 117, 120, 124, 10002, 10003, 10004, 10005, 10006, 10128, 10170, 10171, 10173, 10235, 10236, 10237, 10238, 10240 ])) ) {
				return false;
			}
		}

		// Средний мед. персонал и зубные врачи
		if ( options.isMidMedPersonal ) {
			if ( record.get('PostKind_id') != 6 && (Ext.isEmpty(record.get('PostMed_Code')) || !record.get('PostMed_Code').inlist(midMedPersonalCodes)) ) {
				return false;
			}
		}

		// Только средний мед. персонал
		if ( options.isMidMedPersonalOnly ) {
			if ( record.get('PostKind_id') != 6 ) {
				return false;
			}
		}

		// Фильтруем врачей поликлинических отделений
		if ( options.isPolka ) {
			if ( record.get('LpuSectionProfile_SysNick') == 'priem' || !record.get('LpuUnitType_SysNick').toString().inlist([ 'polka', 'ccenter', 'traumcenter', 'fap' ]) ) {
				if( record.get('LpuSectionProfile_SysNick') != 'priem' && record.get('LpuUnitType_SysNick') != 'stac' ){ // добавил врачей приемного отделения стационара #185842
					return false;
				}
			}
			else {
				switch ( getRegionNick() ) {
					case 'ufa':
						if ( !options.isDisp && record.get('LpuSectionProfile_Code').toString().inlist([ '526', '562', '626', '662', '527', '528', '529', '530', '559', '560', '561', '627', '628', '629', '630', '659', '660', '661', '827', '828', '829', '830', '859', '860', '861' ]) ) {
							return false;
						}
					break;

					case 'perm':
						if ( !options.isDisp && record.get('LpuSectionProfile_Code').toString().inlist([ '1800', '1810', '1820', '1830', '1840', '1850', '85', '89', '90', '86', '63', '87', '88', '171', '1803', '1801', '1811', '1802', '1812', '1860', '7181', '7182' ]) ) {
							return false;
						}
					break;
				}
			}
		}

		// Фильтруем врачей не поликлинических отделений
		if ( options.isNotPolka ) {
			if ( record.get('LpuUnitType_SysNick').toString().inlist([ 'polka', 'ccenter', 'traumcenter', 'fap' ]) ) {
				return false;
			}
		}

		// Фильтруем врачей приемных отделений поликлиники
		if ( options.isPolkaReception ) {
			if ( record.get('LpuSectionProfile_SysNick') != 'priem' || record.get('LpuUnitType_SysNick') != 'polka' ) {
				return false;
			}
		}

		// Фильтруем врачей стоматологических отделений
		if ( options.isStom ) {
			if ( record.get('LpuSectionProfile_SysNick') == 'priem' || !record.get('LpuUnitType_SysNick').toString().inlist([ 'polka', 'ccenter', 'traumcenter', 'fap' ]) ) {
				return false;
			}
			else {
				switch ( getRegionNick() ) {
					case 'ufa':
						// Добавлены дополнительные коды профилей
						// https://redmine.swan.perm.ru/issues/14502
						if ( !record.get('LpuSectionProfile_Code').toString().inlist([ '526', '562', '626', '662', '527', '528', '529', '530', '559', '560', '561', '627', '628', '629', '630', '659', '660', '661', '827', '828', '829', '830', '859', '860', '861' ]) ) {
							return false;
						}
					break;

					case 'perm':
						if ( !record.get('LpuSectionProfile_Code').toString().inlist([ '1800', '1810', '1820', '1830', '1840', '1850', '85', '89', '90', '86', '63', '87', '88', '171', '1803', '1801', '1811', '1802', '1812', '1860', '7181', '7182' ]) ) {
							return false;
						}
						/*if (
							record.get('LpuSectionProfile_Code').toString().length < 2
							|| (record.get('LpuSectionProfile_Code').toString().substr(0, 2) != '18' && !record.get('LpuSectionProfile_Code').toString().inlist([ '7181', '7182' ]))
						) {
							return false;
						}*/
					break;
				}
			}
		}

		if ( options.isPolkaAndStom ) {
			if ( Ext.isEmpty(record.get('LpuSectionProfile_SysNick')) || record.get('LpuSectionProfile_SysNick') == 'priem' || !record.get('LpuUnitType_SysNick').toString().inlist([ 'polka', 'ccenter', 'traumcenter', 'fap' ]) ) {
				return false;
			}
		}

		if ( options.isPolkaAndStomAndOther ) {
			if ( Ext.isEmpty(record.get('LpuSectionProfile_SysNick')) || record.get('LpuSectionProfile_SysNick') == 'priem' || !record.get('LpuUnitType_SysNick').toString().inlist([ 'polka', 'ccenter', 'traumcenter', 'fap', 'other' ]) ) {
				return false;
			}
		}

		// Фильтруем врачей приемных отделений стационара
		if ( options.isStacReception ) {
			if ( Ext.isEmpty(record.get('LpuSectionProfile_SysNick')) || Ext.isEmpty(record.get('LpuUnitType_SysNick'))
				|| !record.get('LpuUnitType_SysNick').toString().inlist([ 'stac', 'dstac', 'hstac', 'pstac', 'priem' ])
			) {
				return false;
			}
			else if ( getRegionNick() == 'astra' ) {
				if (
					(record.get('LpuUnitType_SysNick') != 'priem' && record.get('LpuSectionProfile_SysNick').toString() != 'priem')
					|| (record.get('LpuUnitType_SysNick') == 'priem' && record.get('LpuSectionProfile_SysNick').toString() != 'priem' && record.get('LpuSectionProfile_Code') != '303')
				) {
					return false;
				}
			}
			else if ( getRegionNick().inlist([ 'ekb', 'kareliya', 'krym', 'penza', 'pskov' ]) ) {
				if ( record.get('LpuSectionProfile_SysNick').toString() != 'priem' && record.get('LpuSectionProfile_Code') != '160' ) {
					return false;
				}
			}
			else {
				if ( record.get('LpuSectionProfile_SysNick').toString() != 'priem' ) {
					return false;
				}
			}
		}

		// Фильтруем врачей приемных отделений параклиники
		if ( options.isParkaReception ) {
			if ( record.get('LpuSectionProfile_SysNick') != 'priem' || record.get('LpuUnitType_SysNick') != 'parka' ) {
				return false;
			}
		}

		// Фильтруем врачей параклиники
		if ( options.isParka ) {
			if ( record.get('LpuUnitType_SysNick') != 'parka' ) {
				return false;
			}
		}

		/**
		 *	Фильтруем врачей для заполнения протоколов патоморфогистологических исследований
		 *	Поликлиника, стационар, параклиника, ФАП, энисинг элс?
		 */
		if ( options.isHisto ) {
			if (
				Ext.isEmpty(record.get('LpuSectionProfile_SysNick'))
				|| record.get('LpuSectionProfile_SysNick') == 'priem'
				|| !(record.get('LpuUnitType_SysNick').toString().inlist([ 'stac', 'polka', 'dstac', 'hstac', 'pstac', 'parka', 'ccenter', 'traumcenter', 'smp', 'patan' ]))
			) {
				return false;
			}
		}

		// Фильтруем по коду профиля отделения
		if ( options.lpuSectionProfileCode ) {
			if ( record.get('LpuSectionProfile_Code').toString().length < options.lpuSectionProfileCode.toString().length || record.get('LpuSectionProfile_Code').toString().substr(0, options.lpuSectionProfileCode.toString().length) != options.lpuSectionProfileCode.toString() ) {
				return false;
			}
		}

		// Фильтруем по списку кодов профилей отделений
		if ( options.arrayLpuSectionProfile ) {
			if ( !(record.get('LpuSectionProfile_Code').inlist(options.arrayLpuSectionProfile)) ) {
				return false;
			}
		}

		// Фильтруем по списку кодов типов подразделений
		if ( options.arrayLpuUnitType ) {
			if ( !(record.get('LpuUnitType_Code').inlist(options.arrayLpuUnitType)) ) {
				return false;
			}
		}

		// Фильтруем врачей, работающих в стационаре
		if ( options.isStac ) {
			if ( !(record.get('LpuUnitType_SysNick').inlist([ 'stac', 'dstac', 'hstac', 'pstac', 'priem' ])) ) {
				return false;
			}
		}

		if( options.isOnlyStac){
			if ( !(record.get('LpuUnitType_SysNick').inlist([ 'stac'])) ) {
				return false;
			}
		}

		if( options.isOnlyPolka){
			if ( !(record.get('LpuUnitType_SysNick').inlist([ 'polka'])) ) {
				return false;
			}
		}

		// Фильтруем отделения, кроме стационара
		if ( options.isNotStac ) {
			if ( record.get('LpuUnitType_SysNick').inlist([ 'stac', 'dstac', 'hstac', 'pstac', 'priem' ]) ) {
				return false;
			}
		}

		// Фильтруем по специальности
		if ( options.MedSpecOms_id ) {
			if ( record.get('MedSpecOms_id') != options.MedSpecOms_id ) {
				return false;
			}
		}

		// Фильтруем по специальности
		if ( options.MedSpecOmsList && options.MedSpecOmsList.length > 0 ) {
			if ( !record.get('MedSpecOms_id') || !record.get('MedSpecOms_id').inlist(options.MedSpecOmsList.split(',')) ) {
				return false;
			}
		}

		// Фильтруем врачей, работающих в стационаре или в поликлинике
		if ( options.isStacAndPolka ) {
			if ( !(record.get('LpuUnitType_SysNick').inlist([ 'polka', 'stac', 'dstac', 'hstac', 'pstac', 'parka', 'ccenter', 'traumcenter', 'fap', 'priem' ])) ) {
				return false;
			}
			// Если поликлиника, то убираем приемные и стоматологические отделения
			else if ( record.get('LpuUnitType_SysNick').inlist([ 'polka', 'ccenter', 'traumcenter', 'fap' ]) ) {
				if ( record.get('LpuSectionProfile_SysNick') == 'priem' ) {
					return false;
				}
				else {
					switch ( getRegionNick() ) {
						case 'ufa':
							if ( record.get('LpuSectionProfile_Code').toString().inlist([ '526', '562', '626', '662', '527', '528', '529', '530', '559', '560', '561', '627', '628', '629', '630', '659', '660', '661', '827', '828', '829', '830', '859', '860', '861' ]) ) {
								return false;
							}
						break;

						case 'perm':
							if ( record.get('LpuSectionProfile_Code').toString().inlist([ '1800', '1810', '1820', '1830', '1840', '1850', '85', '89', '90', '86', '63', '87', '88', '171', '1803', '1801', '1811', '1802', '1812', '1860', '7181', '7182' ]) ) {
								return false;
							}
						break;
					}
				}
			}
		}

		// Фильтруем места работы по LpuSection_id (переданному значению)
		if ( options.LpuSection_id ) {
			if ( record.get('LpuSection_id') != options.LpuSection_id ) {
				return false;
			}
		}

		// Фильтрация врачей подотделений:
		if ( typeof options.allowLowLevel == 'string' ) {
			// загружаем только подотделения
			if ( options.allowLowLevel == 'only' && !record.get('LpuSection_pid') ) {
				return false;
			}
			else {
				// загружаем весь список
			}
		}
		// загружаем только врачей отделений
		else if ( record.get('LpuSection_pid') ) {
			return false;
		}

		// Фильтруем по сторонним специалистам
		if ( options.isAliens ) {
			if ( record.get('Lpu_id') == getGlobalOptions().lpu_id ) {
				return false;
			}
		}
		// Фильтруем места работы по Lpu_id (переданному значению)
		if ( options.Lpu_id ) {
			if ( record.get('Lpu_id') != options.Lpu_id ) {
				return false;
			}
		}
		else {
			if ( !record.get('Lpu_id').inlist(typeof getGlobalOptions().linkedLpuIdList == 'object' ? getGlobalOptions().linkedLpuIdList : []) ) {
				return false;
			}
		}

		// Фильтруем места работы по ids
		if ( options.ids && Ext.isArray(options.ids) ) {
			if ( !options.ids.in_array(record.get('MedStaffFact_id')) ) {
				return false;
			}
		}

		// Фильтруем места работы по id
		if ( options.id && !options.ids ) {
			if ( record.get('MedStaffFact_id') != options.id ) {
				return false;
			}
		}

		// Фильтруем места работы по Person_id
		if ( options.Person_id ) {
			if ( record.get('Person_id') != options.Person_id ) {
				return false;
			}
		}

		// Фильтруем места работы по Номенклатуре должностей MedPost_pid
		if ( options.MedPost_pid ) {
			if ( options.MedPost_pid != record.get('MedPost_pid')  ) {
				return false;
			}
		}

		// Фильтруем места работы по списку MedPersonal_id
		if ( options.medPersonalIdList && Ext.isArray(options.medPersonalIdList) ) {
			if ( !options.medPersonalIdList.in_array(record.get('MedPersonal_id')) ) {
				return false;
			}
		}

		// Фильтруем по подразделению
		if ( !Ext.isEmpty(options.LpuBuilding_id) && !getGlobalOptions().region.nick.inlist(['kareliya'])){
			if ( record.get('LpuBuilding_id') != options.LpuBuilding_id ) {
				return false;
			}
		}

		// Фильтруем по типу подразделения
		if ( !Ext.isEmpty(options.LpuBuildingType_id) ) {
			if ( record.get('LpuBuildingType_id') != options.LpuBuildingType_id ) {
				return false;
			}
		}

		// Фильтруем по коду подразделения ТФОМС
		if ( !Ext.isEmpty(options.LpuUnitSet_id) ) {
			if ( record.get('LpuUnitSet_id') != options.LpuUnitSet_id ) {
				return false;
			}
		}

		// Фильтруем по группе отделений
		if ( !Ext.isEmpty(options.LpuUnit_id) ) {
			if ( record.get('LpuUnit_id') != options.LpuUnit_id ) {
				return false;
			}
		}

		// Фильтруем по типу участка
		if ( !Ext.isEmpty(options.LpuRegionType_SysNick) ) {
			switch(getRegionNick()){
				case 'perm':
					switch(options.LpuRegionType_SysNick){
						case 'ter':
							if (!(record.get('PostMed_Code') && record.get('PostMed_Code').inlist(['73','74','76','117','111']))){
								return false;
							}
							break;
						case 'ped':
							if (!(record.get('PostMed_Code') && record.get('PostMed_Code').inlist(['46','47','117','111']))){
								return false;
							}
							break;
						case 'stom':
							if (!(record.get('PostMed_Code') && record.get('PostMed_Code').inlist(['191','192','194']))){
								return false;
							}
							break;
						case 'gin':
							if (!(record.get('PostMed_Code') && record.get('PostMed_Code').inlist(['12','13']))){
								return false;
							}
							break;
						case 'vop':
							if (!(record.get('PostMed_Code') && record.get('PostMed_Code').inlist(['40']))){
								return false;
							}
							break;
					}
					break;
				case 'khak':
					switch(options.LpuRegionType_SysNick){
						case 'ter':
							if (!(record.get('PostMed_Code') && record.get('PostMed_Code').inlist(['74','117']))){
								return false;
							}
							break;
						case 'ped':
							if (!(record.get('PostMed_Code') && record.get('PostMed_Code').inlist(['47','117']))){
								return false;
							}
							break;
						case 'stom':
							if (!(record.get('PostMed_Code') && record.get('PostMed_Code').inlist(['191','192','194','115']))){
								return false;
							}
							break;
						case 'gin':
							if (!(record.get('PostMed_Code') && record.get('PostMed_Code').inlist(['12']))){
								return false;
							}
							break;
						case 'vop':
							if (!(record.get('PostMed_Code') && record.get('PostMed_Code').inlist(['40', '111', '117', '120', '4510209']))){
								return false;
							}
							break;
						case 'feld':
							if (!(record.get('PostMed_Code') && record.get('PostMed_Code').inlist(['111','117','120','4510209']))){
								return false;
							}
							break;
					}
					break;
				case 'buryatiya':
					switch(options.LpuRegionType_SysNick){
						case 'ter':
							if (!(record.get('PostMed_Code') && record.get('PostMed_Code').inlist(['117','111','76','74','73']))){
								return false;
							}
							break;
						case 'ped':
							if (!(record.get('PostMed_Code') && record.get('PostMed_Code').inlist(['46','47','117']))){
								return false;
							}
							break;
						case 'stom':
							if (!(record.get('PostMed_Code') && record.get('PostMed_Code').inlist(['191','192','194','115']))){
								return false;
							}
							break;
						case 'gin':
							if (!(record.get('PostMed_Code') && record.get('PostMed_Code').inlist(['12','13','111','117','120','4510209']))){
								return false;
							}
							break;
						case 'vop':
							if (!(record.get('PostMed_Code') && record.get('PostMed_Code').inlist(['117','111','40']))){
								return false;
							}
							break;
						case 'feld':
							if (!(record.get('PostMed_Code') && record.get('PostMed_Code').inlist(['111','117','120','4510209']))){
								return false;
							}
							break;
					}
					break;
				case 'pskov':
					switch(options.LpuRegionType_SysNick){
						case 'ter':
							if (!(record.get('PostMed_Code') && record.get('PostMed_Code').inlist(['73','74','117']))){
								return false;
							}
							break;
						case 'ped':
							if (!(record.get('PostMed_Code') && record.get('PostMed_Code').inlist(['46','47']))){
								return false;
							}
							break;
						case 'vop':
							if (!(record.get('PostMed_Code') && record.get('PostMed_Code').inlist(['40']))){
								return false;
							}
							break;
						case 'feld':
							if (!(record.get('PostMed_Code') && record.get('PostMed_Code').inlist(['117']))){
								return false;
							}
							break;
					}
					break;
			}
		}

		// Поменяли логику фильтрации дублей мест работы. По-умолчанию без фильтрации.
		// https://redmine.swan.perm.ru/issues/67873
		if ( options.disableDuplicateMSF == true ) {
			if ( Ext.isEmpty(distinctMPList[record.get('MedPersonal_id')]) ) {
				distinctMPList[record.get('MedPersonal_id')] = new Array();
			}

			if ( !Ext.isEmpty(record.get('LpuSection_id')) ) {
				if ( !record.get('LpuSection_id').toString().inlist(distinctMPList[record.get('MedPersonal_id')]) ) {
					distinctMPList[record.get('MedPersonal_id')].push(record.get('LpuSection_id').toString());
				}
				else {
					return false;
				}
			}
		}

		// Фильтруем по типу участка для вызовов на дом
		if ( !Ext.isEmpty(options.LpuRegionType_HomeVisit) ) {
			// Данные на рабочем Кз в persis.Post отличаются
			if(getRegionNick() == 'kz' && !IS_DEBUG){
				var commonList = ['38','44','45','70','71','73','109','104'];
				var stomList = ['163','164','166','167'];
				var allList = commonList.concat(stomList);;
			} else if(getRegionNick() == 'kareliya'){
				var commonList = ['40','46','47','73','74','76','117','111'];
				var middleMPList = ['116']; //Старшая медицинская сестра (акушерка, фельдшер, операционная медицинская сестра, зубной техник)
				commonList = commonList.concat(middleMPList);
				var stomList = ['191','192','194','195'];
				var allList = commonList.concat(stomList);
			} else {
				var commonList = ['40','46','47','73','74','76','117','111'];
				var stomList = ['191','192','194','195'];
				var allList = commonList.concat(stomList);
			}

			if(getRegionNick() == 'ekb' && record.get('MedStaffFact_Stavka') == '0'){
				return false;
			}

			switch(options.LpuRegionType_HomeVisit){
				case 'terpedvop':
					// фильтр для узких специалистов - только те у кого есть флаг Прием на дому
					if (
						record.get('PostMed_Code')
						&& !record.get('PostMed_Code').inlist(commonList) // все кроме учатковых (и равных им из commonList) врачей
						&& (
							!record.get('MedStaffFactCache_IsHomeVisit') // не значения флага Прием на дому
							|| !(record.get('MedStaffFactCache_IsHomeVisit') && record.get('MedStaffFactCache_IsHomeVisit') == 2) // флаг Прием на дому отрицателен
						)
					) {
						return false;
					}
					// Фильтрация для узких специалистов
					if(!record.get('PostMed_Code').inlist(commonList) && options.HomeVisitDate){
						if(typeof options.HomeVisitDate == 'object'){
							var HomeVisitDate = options.HomeVisitDate;
						} else {
							var HomeVisitDate = Date.parseDate(options.HomeVisitDate, 'd.m.Y');
						}
						var ls_dis_date = Date.parseDate(record.get('LpuSection_disDate'), 'd.m.Y');
						var ls_set_date = Date.parseDate(record.get('LpuSection_setDate'), 'd.m.Y');
						var mp_beg_date = Date.parseDate(record.get('WorkData_begDate'), 'd.m.Y');
						var mp_end_date = Date.parseDate(record.get('WorkData_endDate'), 'd.m.Y');

						if ( !Ext.isEmpty(HomeVisitDate) ) {
							if ( (!Ext.isEmpty(mp_beg_date) && mp_beg_date > HomeVisitDate) || (!Ext.isEmpty(mp_end_date) && mp_end_date < HomeVisitDate) ||
								(!Ext.isEmpty(ls_set_date) && ls_set_date > HomeVisitDate) || (!Ext.isEmpty(ls_dis_date) && ls_dis_date < HomeVisitDate) /*||
								(ls_set_date > mp_beg_date) || (ls_dis_date < mp_end_date)*/ )
							{
								return false;
							}
						}
					}
					// Для Карелии фильтрация по дате работы для общих врачей
					if(getRegionNick().inlist(['kareliya','ekb']) && record.get('PostMed_Code').inlist(commonList) && options.HomeVisitDate){
						if(typeof options.HomeVisitDate == 'object'){
							var HomeVisitDate = options.HomeVisitDate;
						} else {
							var HomeVisitDate = Date.parseDate(options.HomeVisitDate, 'd.m.Y');
						}
						var ls_dis_date = Date.parseDate(record.get('LpuSection_disDate'), 'd.m.Y');
						var ls_set_date = Date.parseDate(record.get('LpuSection_setDate'), 'd.m.Y');
						var mp_beg_date = Date.parseDate(record.get('WorkData_begDate'), 'd.m.Y');
						var mp_end_date = Date.parseDate(record.get('WorkData_endDate'), 'd.m.Y');

						if ( !Ext.isEmpty(HomeVisitDate) ) {
							if ( (!Ext.isEmpty(mp_beg_date) && mp_beg_date > HomeVisitDate) || (!Ext.isEmpty(mp_end_date) && mp_end_date < HomeVisitDate) ||
								(!Ext.isEmpty(ls_set_date) && ls_set_date > HomeVisitDate) || (!Ext.isEmpty(ls_dis_date) && ls_dis_date < HomeVisitDate) /*||
								(ls_set_date > mp_beg_date) || (ls_dis_date < mp_end_date)*/ )
							{
								return false;
							}
						}
					}
					// Для Карелии фильтрация по дате работы на участке для общих врачей
					if(getRegionNick().inlist(['kareliya']) && record.get('PostMed_Code').inlist(commonList) && options.HomeVisitDate && record.get('LpuRegion_DatesList')){
						if(typeof options.HomeVisitDate == 'object'){
							var HomeVisitDate = options.HomeVisitDate;
						} else {
							var HomeVisitDate = Date.parseDate(options.HomeVisitDate, 'd.m.Y');
						}
						var datesList = record.get('LpuRegion_DatesList');
						if(datesList.length > 0){
							datesList = datesList.split(',');
							if(datesList.length > 0){
								var lpuregion_flag = 0;
								for(var i = 0;i<datesList.length;i++){
									var item = datesList[i].split(':');
									var beg_date = Date.parseDate(item[1], 'd.m.Y');
									var end_date = Date.parseDate(item[2], 'd.m.Y');
									if ( !Ext.isEmpty(HomeVisitDate) ) {
										if ( (!Ext.isEmpty(beg_date) && beg_date > HomeVisitDate) || (!Ext.isEmpty(end_date) && end_date < HomeVisitDate) )
										{
											lpuregion_flag = 1; // Врач не работает на участке
										} else {
											lpuregion_flag = 2; // Врач работает на участке
										}
									}
									if(lpuregion_flag == 2){
										break;
									}
								}
								if(lpuregion_flag !== 2){
									return false;
								}
							}
						}
					}
					// только узких специалистов
					if(options.HomeVisit_onlySpecs){
						if(record.get('PostMed_Code').inlist(commonList)){
							return false;
						}
					}
					// Только записанные на участки - только для участковых врачей
					if ( options.withLpuRegionOnly ) {
						if ( record.get('PostMed_Code').inlist(commonList) && !record.get('LpuRegion_List') ) {
							return false;
						}
					}
					break;
				case 'stom':
					if (!(record.get('PostMed_Code') && record.get('PostMed_Code').inlist(stomList))){
						return false;
					}
					// Только записанные на участки - для стоматологов на стом участках
					if ( options.withLpuRegionOnly ) {
						if ( !record.get('LpuRegion_List') ) {
							return false;
						}
					}
					break;
				case 'all':
					if (
						(record.get('PostMed_Code')
						&& !record.get('PostMed_Code').inlist(allList)
						&& ( !record.get('MedStaffFactCache_IsHomeVisit') || !((record.get('MedStaffFactCache_IsHomeVisit')) && record.get('MedStaffFactCache_IsHomeVisit') == 2) ))
						){
						return false;
					}
					// Фильтрация для узких специалистов
					if(!record.get('PostMed_Code').inlist(allList) && options.HomeVisitDate){
						if(typeof options.HomeVisitDate == 'object'){
							var HomeVisitDate = options.HomeVisitDate;
						} else {
							var HomeVisitDate = Date.parseDate(options.HomeVisitDate, 'd.m.Y');
						}
						var ls_dis_date = Date.parseDate(record.get('LpuSection_disDate'), 'd.m.Y');
						var ls_set_date = Date.parseDate(record.get('LpuSection_setDate'), 'd.m.Y');
						var mp_beg_date = Date.parseDate(record.get('WorkData_begDate'), 'd.m.Y');
						var mp_end_date = Date.parseDate(record.get('WorkData_endDate'), 'd.m.Y');

						if ( !Ext.isEmpty(HomeVisitDate) ) {
							if ( (!Ext.isEmpty(mp_beg_date) && mp_beg_date > HomeVisitDate) || (!Ext.isEmpty(mp_end_date) && mp_end_date < HomeVisitDate) ||
								(!Ext.isEmpty(ls_set_date) && ls_set_date > HomeVisitDate) || (!Ext.isEmpty(ls_dis_date) && ls_dis_date < HomeVisitDate) /*||
								(ls_set_date > mp_beg_date) || (ls_dis_date < mp_end_date)*/ )
							{
								return false;
							}
						}
					}
					// Для Карелии фильтрация по дате работы для всех
					if(getRegionNick().inlist(['kareliya','ekb']) && record.get('PostMed_Code').inlist(allList) && options.HomeVisitDate){
						if(typeof options.HomeVisitDate == 'object'){
							var HomeVisitDate = options.HomeVisitDate;
						} else {
							var HomeVisitDate = Date.parseDate(options.HomeVisitDate, 'd.m.Y');
						}
						var ls_dis_date = Date.parseDate(record.get('LpuSection_disDate'), 'd.m.Y');
						var ls_set_date = Date.parseDate(record.get('LpuSection_setDate'), 'd.m.Y');
						var mp_beg_date = Date.parseDate(record.get('WorkData_begDate'), 'd.m.Y');
						var mp_end_date = Date.parseDate(record.get('WorkData_endDate'), 'd.m.Y');

						if ( !Ext.isEmpty(HomeVisitDate) ) {
							if ( (!Ext.isEmpty(mp_beg_date) && mp_beg_date > HomeVisitDate) || (!Ext.isEmpty(mp_end_date) && mp_end_date < HomeVisitDate) ||
								(!Ext.isEmpty(ls_set_date) && ls_set_date > HomeVisitDate) || (!Ext.isEmpty(ls_dis_date) && ls_dis_date < HomeVisitDate) /*||
								(ls_set_date > mp_beg_date) || (ls_dis_date < mp_end_date)*/ )
							{
								return false;
							}
						}
					}
					// Для Карелии фильтрация по дате работы на участке для общих врачей
					if(getRegionNick().inlist(['kareliya']) && record.get('PostMed_Code').inlist(commonList) && options.HomeVisitDate && record.get('LpuRegion_DatesList')){
						if(typeof options.HomeVisitDate == 'object'){
							var HomeVisitDate = options.HomeVisitDate;
						} else {
							var HomeVisitDate = Date.parseDate(options.HomeVisitDate, 'd.m.Y');
						}
						var datesList = record.get('LpuRegion_DatesList');
						if(datesList.length > 0){
							datesList = datesList.split(',');
							if(datesList.length > 0){
								for(var i = 0;i<datesList.length;i++){
									var item = datesList[i].split(':');
									var beg_date = Date.parseDate(item[1], 'd.m.Y');
									var end_date = Date.parseDate(item[2], 'd.m.Y');
									if ( !Ext.isEmpty(HomeVisitDate) ) {
										if ( (!Ext.isEmpty(beg_date) && beg_date > HomeVisitDate) || (!Ext.isEmpty(end_date) && end_date < HomeVisitDate) )
										{
											return false;
										}
									}
								}
							}
						}
					}
					// Только записанные на участки - только для участковых врачей
					if ( options.withLpuRegionOnly ) {
						if ( record.get('PostMed_Code').inlist(commonList) && !record.get('LpuRegion_List') ) {
							return false;
						}
					}
					break;
			}
		}


		if ( ! Ext.isEmpty(options.UslugaComplex_MedSpecOms) )
		{
			// если нет специальности, то не идем дальше, в swUslugaComplexMedSpec более 7к записей
			if ( Ext.isEmpty(record.get('MedSpecOms_id')) )
			{
				return false;
			}

			var didDate = (typeof options.UslugaComplex_MedSpecOms.didDate == 'object' ? options.UslugaComplex_MedSpecOms.didDate : getValidDT(options.UslugaComplex_MedSpecOms.didDate,'')),
				UslugaComplex_id = options.UslugaComplex_MedSpecOms.UslugaComplex_id;

			var findMatchUslugaComplex_MedSpecOms = swUslugaComplexMedSpec.findBy(function (rec, id) {

				if ( rec.get('UslugaComplex_id') == UslugaComplex_id && record.get('MedSpecOms_id') == rec.get('MedSpecOms_id'))
				{
					var UslugaComplexMedSpec_begDate = getValidDT(Ext.util.Format.date(rec.get('UslugaComplexMedSpec_begDate'), 'd.m.Y'),''),
						UslugaComplexMedSpec_endDate = getValidDT(Ext.util.Format.date(rec.get('UslugaComplexMedSpec_endDate'), 'd.m.Y'),'');

					if ( didDate >= UslugaComplexMedSpec_begDate && ( didDate <= UslugaComplexMedSpec_endDate || Ext.isEmpty(UslugaComplexMedSpec_endDate) ) )
					{
						return true;
					}
				}
			});

			if (findMatchUslugaComplex_MedSpecOms == -1)
			{
				return false;
			}
		}

		return true;
	};

	if ( typeof external_store == 'object' ) {
		external_store.clearFilter();
		external_store.filterBy(filterFunction);
	}
	else if ( typeof swMedStaffFactGlobalStore == 'object' ) {
		swMedStaffFactGlobalStore.clearFilter();
		swMedStaffFactGlobalStore.filterBy(filterFunction);
	}
	if(returnfilterFn)
		return filterFunction;

	return true;
}

/**
 * Установить фильтр на swMedServiceGlobalStore
 * options - параметры фильтрации
 */
function setMedServiceGlobalStoreFilter(options) {
	swMedServiceGlobalStore.clearFilter();

	if ( typeof options != 'object' ) {
		options = new Object();
	}

	swMedServiceGlobalStore.filterBy(function(record) {
		// Фильтруем по датам
		if ( options.onDate ) {
			var onDate = Date.parseDate(options.onDate, 'd.m.Y');
			var ms_beg_date = Date.parseDate(record.get('MedService_begDT'), 'd.m.Y');
			var ms_end_date = Date.parseDate(record.get('MedService_endDT'), 'd.m.Y');

			if ( onDate ) {
				if ( (ms_beg_date && ms_beg_date > onDate) || (ms_end_date && ms_end_date < onDate ) ) {
					return false;
				}
			}
		}
		else {
			if ( options.dateFrom ) {
				var dateFrom = Date.parseDate(options.dateFrom, 'd.m.Y');
				var ms_end_date = Date.parseDate(record.get('MedService_endDT'), 'd.m.Y');

				if ( dateFrom && ms_end_date && ms_end_date < dateFrom ) {
					return false;
				}
			}

			if ( options.dateTo ) {
				var dateTo = Date.parseDate(options.dateTo, 'd.m.Y');
				var ms_beg_date = Date.parseDate(record.get('MedService_begDT'), 'd.m.Y');

				if ( dateTo && ms_beg_date && ms_beg_date > dateTo ) {
					return false;
				}
			}
		}

		// Фильтруем службы по МО
		if ( options.Lpu_id ) {
			if ( record.get('Lpu_id') != options.Lpu_id ) {
				return false;
			}
		}

		// Фильтруем службы по группе отделений
		if ( options.LpuBuilding_id ) {
			if ( record.get('LpuBuilding_id') != options.LpuBuilding_id ) {
				return false;
			}
		}

		// Фильтруем службы по подразделению
		if ( options.LpuUnit_id ) {
			if ( record.get('LpuUnit_id') != options.LpuUnit_id ) {
				return false;
			}
		}

		// Фильтруем службы по отделению
		if ( options.LpuUnitType_id ) {
			if ( record.get('LpuUnitType_id') != options.LpuUnitType_id ) {
				return false;
			}
		}

		// Фильтруем службы по типу
		if ( options.MedServiceType_id ) {
			if ( record.get('MedServiceType_id') != options.MedServiceType_id ) {
				return false;
			}
		}

		return true;
	});

	return true;
}

/**
* Преобразует date (строка, формат 'd.m.Y') и time (строка, формат 'H:i') в объект Date
*/
function getValidDT(date, time) {
	if ( typeof date != 'string' || typeof time != 'string' ) {
		return null;
	}

	if ( date != Ext.util.Format.date(Date.parseDate(date, 'd.m.Y'), 'd.m.Y') ) {
		return null;
	}

	if ( time.length > 0 && time != Ext.util.Format.date(Date.parseDate(time, 'H:i'), 'H:i') ) {
		return null;
	}

	return Date.parseDate(date + ' ' + (time.toString().length == 0 ? '00:00:00' : time + ':00'), 'd.m.Y H:i:s');
}

/**
* Проверяет комбобокс с датой, не позволяя выставить значение даты меньше даты смерти если таковая имеется. Предпологается использование на событие "change"
* возвращает true - если изменение даты было заблокированно, и значение осталось прежним
*/
function blockedDateAfterPersonDeath(sourcetype, source, combo, newvalue, oldvalue) {
	return checkDateAfterPersonDeath(sourcetype, source, combo, newvalue, oldvalue, 'blocked');
};

function clearDateAfterPersonDeath(sourcetype, source, combo) { //очищает комбобокс если дата больше даты смерти
	return checkDateAfterPersonDeath(sourcetype, source, combo, combo.getValue(), null, 'clear');
};

function checkDateAfterPersonDeath(sourcetype, source, combo, newvalue, oldvalue, mode) {
	var deathdate = '';
	switch (sourcetype) {
		case 'personpanelid': //идентификатор PersonInformationPanel
			source = Ext.getCmp(source);
		case 'personpanel': //ссылка на PersonInformationPanel
			//пробуем извлечь дату
			if (source)
				deathdate = source.getFieldValue('Person_deadDT');
			break;
		case 'datestr':
			deathdate = Date.parseDate(source, 'd.m.Y');
			break;
		case 'dateobj':
			deathdate = source;
			break;
	}
	//alert('check ' + deathdate);
	if (deathdate != '' && deathdate < newvalue) {
		if (mode != 'clear') Ext.Msg.alert(langs('Ошибка'), langs('Дата не может быть больше даты смерти человека.'));
		combo.setValue(oldvalue);
		if (mode == 'clear') combo.startValue = null;
		return true;
	}
	return false;
}

function getMedPersonalListFromGlobal() {
	var result = new Array();
	var med_personal_array = new Array();

	// Сперва тянем из своей МО, ибо чужой табельный номер вызывает боль и истерику у пользователей
	// @task https://redmine.swan.perm.ru/issues/70630
	swMedStaffFactGlobalStore.each(function(record) {
		var add = true;

		if ( !record.get('MedPersonal_id').toString().inlist(med_personal_array) && record.get('Lpu_id') == getGlobalOptions().lpu_id ) {
			med_personal_array.push(record.get('MedPersonal_id'));

			result.push({
				'MedPersonal_Fio': record.get('MedPersonal_Fio'),
				'MedPersonal_id': record.get('MedPersonal_id'),
				'MedPersonal_TabCode': record.get('MedPersonal_TabCode'),
				'MedPersonal_Code': record.get('MedPersonal_TabCode')
			});
		}
	});

	// Затем всех остальных
	swMedStaffFactGlobalStore.each(function(record) {
		var add = true;

		if ( !record.get('MedPersonal_id').toString().inlist(med_personal_array) ) {
			med_personal_array.push(record.get('MedPersonal_id'));

			result.push({
				'MedPersonal_Fio': record.get('MedPersonal_Fio'),
				'MedPersonal_id': record.get('MedPersonal_id'),
				'MedPersonal_TabCode': record.get('MedPersonal_TabCode'),
				'MedPersonal_Code': record.get('MedPersonal_TabCode')
			});
		}
	});

	return result;
}


/**
 * Текущее выбранное место в АРМе врача в поликлинике
 */
function ARMIsPolka() {
	if (getGlobalOptions().CurLpuUnitType_id == undefined)
		return false;
	return getGlobalOptions().CurLpuUnitType_id.toString().inlist(['2', '5', '10', '11', '12']);
}


/**
 * Текущее выбранное место в АРМе врача в стационаре
 */
function ARMIsStac() {
	if (getGlobalOptions().CurLpuUnitType_id == undefined)
		return false;
	return getGlobalOptions().CurLpuUnitType_id.toString().inlist(['1', '6', '7', '9']);
}


/**
 * Текущее выбранное место в АРМе врача в параклинике
 */
function ARMIsParka() {
	if (getGlobalOptions().CurLpuUnitType_id == undefined)
		return false;
	return getGlobalOptions().CurLpuUnitType_id.toString().inlist(['3']);
}


function globalEval(src) {
	if (window.execScript) // eval in global scope for IE
		window.execScript(src);
	else // other browsers
		// TODO: на инструкции eval.call(null, src); Хром взрывается
		if (Ext.isChrome)
			eval(src);
		else
			eval.call(null, src);
}

/**
 * Получает JS-файл (окно формы) и обновляет функциональную часть окна
 */
function loadJsCode(wnd, callback)
{
	log({'Метод':'loadJsCode', 'wnd': wnd, 'callback': callback});
	// Отправляем запрос на получение измененного JS-файла
	Ext.Ajax.request({
		url: '/?c=promed&m=getJSFile&wnd=' + wnd.objectClass + '&_r=' + getRegionNick(), // _r - чтобы не кэшировались файлы при смене региона
		//url: '/?c=promed&m=getJSFile&wnd='+wnd.objectClass,
		disableCaching: false,
		//params: {wnd:wnd.objectClass},
		callback: function(options, success, response) {
			var roles = {};

			if (success) {
				// Читаем и пересоздаем (добавляем в DOM)
				if (response.responseText) {
					var result  = {success: false};
					try {
						var result  = Ext.util.JSON.decode(response.responseText);
						if ( result.success ) {
							var responseText = result.data;
						}
					} catch(e) {
						var responseText = response.responseText;
						result.success = true;
					}
					if ( result.success ) {
						try {
							globalEval(responseText);
							if (typeof callback == "function") {
								callback(success);
							}
						} catch(e) {
							if (IS_DEBUG==2)
								throw e;
							else {
								showFatalError(e, callback);
							}
						}
					} else {
						sw.Promed.mask.hide();
					}
				}
				//window[objectName] =  new sw.Promed[objectName]();
			}
		}
	});

	// Зачистка объекта (исключаем из DOM)
	if (window[wnd.objectName])
	{
		window[wnd.objectName] = null;
		delete sw.Promed[wnd.objectName];
	}
}

/** Функция возвращает true, при включененном режиме отладки и наличии консоли
 *
 */
function isDebug() {
	return ((typeof console==="object" ) && (typeof console.log==="function" ) && IS_DEBUG);
}

/** Вывод в лог с условиями (включененный режим отладки и наличие консоли)
 *  @obj - объект или текст.
 */
function log(obj) {
	if (isDebug()) {
		console.log.apply(console, arguments);
	}
}

/** Вывод в лог с условиями (включененный режим отладки и наличие консоли)
 *  @obj - объект или текст.
 */
function warn() {
	if (isDebug())
		console.warn.apply(console, arguments);
}

/** Вычисление возраста человека на дату
 *  @birth_date - дата рождения
 *  @date - дата, на которую вычисляется возраст
 */
function swGetPersonAge(birth_date, date) {
	// др может прийти в виде текста
	if (typeof birth_date == 'string' && !Ext.isEmpty(birth_date)) {
		birth_date = Date.parseDate(birth_date, 'd.m.Y');
	}

	if ( !birth_date || typeof birth_date != 'object' ) {
		return -1;
	}

	if ( !date || typeof date != 'object' ) {
		date = getValidDT(date, '') || getValidDT(getGlobalOptions().date, '');
	}

	if ( birth_date > date )  {
		return -1;
	}

	// Тру-метод
	// var person_age = (birth_date.getMonthsBetween(date) - (birth_date.getMonthsBetween(date) % 12)) / 12;

	// Олдскул-быдло-метод
	var person_age = date.getFullYear() - birth_date.getFullYear();

	if ( date.getMonth() < birth_date.getMonth() ) {
		person_age = person_age - 1;
	}
	else if ( date.getMonth() == birth_date.getMonth() && date.getDate() < birth_date.getDate() ) {
		person_age = person_age - 1;
	}

	return person_age;
}

/** Вычисление возраста человека на дату (месяцы)
 *  @birth_date - дата рождения
 *  @date - дата, на которую вычисляется возраст
 */
function swGetPersonAgeMonth(birth_date, date) {

	if (typeof birth_date == 'string' && !Ext.isEmpty(birth_date)) {
		birth_date = Date.parseDate(birth_date, 'd.m.Y');
	}

	if ( !birth_date || typeof birth_date != 'object' ) {
		return -1;
	}

	if ( !date || typeof date != 'object' ) {
		date = getValidDT(getGlobalOptions().date, '') || new Date();
	}

	if ( birth_date > date )  {
		return -1;
	}

	// Тру-метод
	// var person_age_month = Math.floor(birth_date.getMonthsBetween(date)) % 12;

	// @task https://redmine.swan.perm.ru/issues/93470
	// Используем олдскул-быдло-метод, ибо getMonthsBetween возвращает неверные данные
	var
		bdy = birth_date.getFullYear(),
		bdm = birth_date.getMonth(),
		bdd = birth_date.getDate(),
		dy = date.getFullYear(),
		dm = date.getMonth(),
		dd = date.getDate();

	var
		diffY = dy - bdy,
		diffM = dm - bdm,
		diffD = dd - bdd;

	var person_age_month = (diffY * 12 + diffM + (diffD < 0 ? -1 : 0)) % 12;

	return person_age_month;
}

/** Вычисление возраста человека на дату (дни от рождения)
 *  @birth_date - дата рождения
 *  @date - дата, на которую вычисляется возраст
 */
function swGetPersonAgeDay(birth_date, date) {

	if (typeof birth_date == 'string' && !Ext.isEmpty(birth_date)) {
		birth_date = Date.parseDate(birth_date, 'd.m.Y');
	}

	if ( !birth_date || typeof birth_date != 'object' ) {
		return -1;
	}

	if ( !date || typeof date != 'object' ) {
		date = getValidDT(getGlobalOptions().date, '') || new Date();
	}

	if ( birth_date > date )  {
		return -1;
	}

	// Тру-метод
	var person_age_day = (date - birth_date)/(1000*60*60*24);
	return person_age_day;
}

/** Изменение количества непрочитанных сообщений в панели сообщений
 *
 */
function changeCountMessages(count) {
	if (getNoticeOptions().is_infopanel_message) {
		main_messages_panel.setVisible((count>0));
		main_messages_panel.unreadCount = count;
		var tc = count.toString(), l = tc.length;

		var ok = ((tc.substring(l-1,1)=='1')?'ие':((tc.substring(l-1,1).inlist(['2','3','4']))?'ия': 'ий'));
		var okch = ((tc.substring(l-1,1)=='1')?'ое':'ых');
		main_messages_tpl.overwrite(main_messages_panel.body, {count: count, okch: okch, ok: ok});
	} else {
		main_messages_panel.setVisible(false);
	}
}
/** Изменение количества непрочитанных сообщений от админа ЦОД в панели сообщений
 *
 */
function changeCountAdminMessages(count) {
	if (getNoticeOptions().is_extra_message) {
		main_messages_panel.setVisible((count>0));
		main_messages_panel.unreadCount = count;
		var tc = count.toString(), l = tc.length;

		var ok = ((tc.substring(l-1,1)=='1')?'ие':((tc.substring(l-1,1).inlist(['2','3','4']))?'ия': 'ий'));
		var okch = ((tc.substring(l-1,1)=='1')?'ое':'ых');
		main_messages_tpl.overwrite(main_messages_panel.body, {count: count, okch: okch, ok: ok});
	} else {
		main_messages_panel.setVisible(false);
	}
}
/**
 * Выводит сообщение системы
 */
function showSysMsg(msg, title, type, options) {
	type = (type)?type:'info';
	title = (title)?title:langs('Сообщение');
	if (title!='') {
		title = '<b>'+title+'</b><br/>';
	}
	var animateTarget='';
	if(options&&options.animateTarget){
		animateTarget = Ext.getCmp(options.animateTarget).getEl();
	}
	var text = '<div class="popup-msg icon '+type+'"><div class="icon"></div><div style="max-height: 600px; overflow: auto;" class="text"><!--i>Система:</i><br/-->'+title+msg+'</div></div>';
	var NewNoticeWindow = new Ext.ux.window.MessageWindow({
		title: '',
		autoHeight: true,
		help: false,
		closable: (options && options.closable)?options.closable:false,
		constrainHeader: false,
		frame: false,
		header: false,
		pinOnClick: false,
        pinState: (options && options.pinState) ? options.pinState : 'unpin',
		constrain: false,
		shadow: false,
		bodyStyle: (options && options.bodyStyle)?options.bodyStyle:'text-align:left;padding:20px;background:transparent',
		buttonAlign: 'left',
		//headerAsText: false,
		hideBorders: true,
		cls: 'customSysMsg',
		animateTarget: (!Ext.isEmpty(Ext.getCmp('main-messages-panel')))?Ext.getCmp('main-messages-panel').getEl():animateTarget,
		//style: 'opacity: 0;',
		tools:[],
		hideFx: {
			delay: (options && options.delay)?options.delay:5000,
			mode: 'custom',
			callback: function(cb,scope,args,delay) {
				this.proxy.setOpacity(.5);
				this.proxy.show();
				var tb = this.getBox(false);
				this.proxy.setBox(tb);
				this.el.hide();
				var b = this.animateTarget.getBox();
				b.callback = this.afterHide;
				b.scope = this;
				b.duration = .25;
				b.easing = 'easeNone';
				b.block = true;
				b.opacity = 0;
				this.proxy.shift(b);
			}
		},
		/*show: function() {
			this.toFront();
		},*/
		html: '',
		listeners: {
			hide: function(win) {
				//showSysMsg('!!!');
			}
		},
		width: 330
	});
	NewNoticeWindow.html = text;
	NewNoticeWindow.show();
	NewNoticeWindow.toFront();
	if(options &&options.isReturn){
		return NewNoticeWindow;
	}
}

function showPopupWarningMsg(msg) {
	if (getNoticeOptions().is_popup_warning) {
		var msg_list = Ext.isArray(msg)?msg:[msg];
		var delay = !Ext.isEmpty(getNoticeOptions().popup_delay)?getNoticeOptions().popup_delay:8;
		for (var i=0; i<msg_list.length; i++) {
			showSysMsg('', msg_list[i], 'warning', {closable: true, delay: delay*1000, bodyStyle: 'text-align:left; margin-left:7px; padding: 0px 0px 20px 20px;background:transparent'});
		}
	}
}

function showPopupInfoMsg(msg) {
	if (getNoticeOptions().is_popup_info) {
		var msg_list = Ext.isArray(msg)?msg:[msg];
		var delay = !Ext.isEmpty(getNoticeOptions().popup_delay)?getNoticeOptions().popup_delay:8;
		for (var i=0; i<msg_list.length; i++) {
			showSysMsg('', msg_list[i], 'info', {closable: true, delay: delay*1000, bodyStyle: 'text-align:left; margin-left:7px; padding: 0px 0px 20px 20px;background:transparent'});
		}
	}
}

/** Вывод всплывающего сообщения из кеша
 *
 */
function showNotice() {
	// берем первую запись из стека
	if (sw.notices.length>0) {
		var NewNoticeWindow = new Ext.ux.window.MessageWindow({
			//title: '',
			autoHeight: true,
			help: false,
			closable: false,
			constrainHeader: false,
			frame: false,
			header: false,
			pinOnClick: false,
			constrain: false,
			//plain: true,
			shadow: false,
			/*cls: '',
			baseCls: '',
			bodyCls: '',
			*/
			bodyStyle: 'text-align:left;padding:20px;background:transparent',
			buttonAlign: 'left',
			//headerAsText: false,
			hideBorders: true,
			animateTarget: (!Ext.isEmpty(Ext.getCmp('main-messages-panel')))?Ext.getCmp('main-messages-panel').getEl():'',
			//style: 'opacity: 0;',
			tools:[],
			hideFx:
			{
				delay: 3000,
				mode: 'custom',
				callback: function(cb,scope,args,delay) {
					this.proxy.setOpacity(.5);
					this.proxy.show();
					var tb = this.getBox(false);
					this.proxy.setBox(tb);
					this.el.hide();
					var b = this.animateTarget.getBox();
					b.callback = this.afterHide;
					b.scope = this;
					b.duration = .25;
					b.easing = 'easeNone';
					b.block = true;
					b.opacity = 0;
					this.proxy.shift(b);
				}
			},
			html: '',
			listeners: {
				hide: function(win)
				{
					//win.setPosition(Ext.getBody().getWidth()-win.width-20, 200);
					changeCountMessages(main_messages_panel.unreadCount+1);
					showNotice();
				}
			},
			width: 330
		});
		var notice = sw.notices[0];
		//NewNoticeWindow.setTitle(notice.PMUser_Name+':');
		NewNoticeWindow.html = notice.msg;
		NewNoticeWindow.show();
		sw.notices.splice(0,1);

	}

}

/**
 * Функция в ссылке "результат" всплывающего сообщения
 * открывает соответствующие окна результатов выполнения услуг
 */
function openNoticeResult(evnClassNick, Evn_id, Person_id) {
	switch(evnClassNick) {
		case 'EvnUslugaTelemed': //alert('EvnUslugaTelemed');
			getWnd('swEvnUslugaTelemedEditWindow').show({action: 'view', formParams: { Person_id: Person_id, EvnUslugaTelemed_id: Evn_id}});
			break;
		case 'EvnUslugaParPolka': //alert('EvnUslugaParPolka');
			getWnd('swEvnUslugaParEditWindow').show({ action: 'view', EvnUslugaPar_id:Evn_id, Person_id:Person_id });
			break;
	}
}

/** Вывод всплывающего сообщения из кеша
 *
 */
function showAdminNotice() {
	// берем первую запись из стека
	if (sw.adminnotices.length>0) {
		var NewNoticeWindow = new Ext.ux.window.MessageWindow({
			//title: 'Cообщение от Администратора системы',
			autoHeight: true,
			help: false,
			closable: false,
			constrainHeader: false,
			frame: false,
			header: false,
			pinOnClick: false,
			constrain: false,
			//plain: true,
			shadow: false,
			/*cls: '',
			baseCls: '',
			bodyCls: '',
			*/
			bodyStyle: 'text-align:left;padding:20px;background:transparent',
			buttonAlign: 'left',
			//headerAsText: false,
			hideBorders: true,
			animateTarget: (!Ext.isEmpty(Ext.getCmp('main-messages-panel')))?Ext.getCmp('main-messages-panel').getEl():'',
			style: 'color: red;',
			tools:[],
			hideFx:
			{
				delay: 10000,
				mode: 'custom',
				callback: function(cb,scope,args,delay) {
					this.proxy.setOpacity(.5);
					this.proxy.show();
					var tb = this.getBox(false);
					this.proxy.setBox(tb);
					this.el.hide();
					var b = this.animateTarget.getBox();
					b.callback = this.afterHide;
					b.scope = this;
					b.duration = .25;
					b.easing = 'easeNone';
					b.block = true;
					b.opacity = 0;
					this.proxy.shift(b);
				}
			},
			html: '',
			listeners: {
				hide: function(win)
				{
					//win.setPosition(Ext.getBody().getWidth()-win.width-20, 200);
					changeCountAdminMessages(main_messages_panel.unreadCount+1);
					showAdminNotice();
				}
			},
			width: 330
		});
		var notice = sw.adminnotices[0];
		var title = document.title.replace(new RegExp("РИАМС",'g'),'');
		var newmsg = notice.msg.replace(new RegExp("Промед",'g'),title);
		NewNoticeWindow.html = newmsg;
		NewNoticeWindow.show();
		sw.adminnotices.splice(0,1);

	}

}
/** Проверяем наличие новых(непрочитанных) сообщений
 *
 *
 */
function getNewMessages() {
	Ext.Ajax.request({
		url: '/?c=Messages&m=getNewMessages',
		success: function(resp, opts)
		{
			var obj = Ext.util.JSON.decode(resp.responseText);
			if (obj)
			{
				var reclen = (obj.data && obj.data.length)?obj.data.length:0;
				if(getNoticeOptions().is_infopanel_message) {
					if (obj.totalCount && sw.notices.length==0) {
						// вызов функции изменения отображения количества новых сообщений - пришедшие сообщения
						 //если без пушей - то показываем сразу сколько сообщений
						var ct = getNoticeOptions().is_popup_message ? obj.totalCount - reclen : obj.totalCount;
						changeCountMessages(ct);
					}
				}
				if (reclen > 0 && getNoticeOptions().is_popup_message)
				{
					// Заполняем стек сообщений
					for (i = 0; i < reclen; i++)
					{
						sw.notices.push(obj.data[i]);
					}
					showNotice();
				}
				else {
					return false;
				}
			}
		}
	});
}

/** Проверяем наличие непрочитанных сообщений от Админа ЦОД
 *
 *
 */
function getAdminMessages() {
	return; // временно отключено, т.к. тяжелые запросы тормозят уфу.

	Ext.Ajax.request({
		url: '/?c=Messages&m=getAdminMessages',
		success: function(resp, opts)
		{
			var obj = Ext.util.JSON.decode(resp.responseText);
			if (obj)
			{
				//var reclen = (obj.data && obj.data.length)?obj.data.length:0;
				var reclen = (obj.data && obj.data.length )?obj.data.length:0;
				if (sw.firstadminnotice == 1){
					sw.firstadminnotice = 0;
				} else {
					if(obj.newmes == false){
						return false;
					}
				}
				if (obj.totalCount && sw.adminnotices.length==0) {
					// вызов функции изменения отображения количества новых сообщений - пришедшие сообщения
					var ct = obj.totalCount - reclen;
					changeCountAdminMessages(ct);
				}
				if (reclen > 0)
				{
					// Заполняем стек сообщений
					for (i = 0; i < reclen; i++)
					{
						sw.adminnotices.push(obj.data[i]);
					}
					showAdminNotice();
				}
				else {
					return false;
				}
			}
		}
	});
}
/** Функция для запускалки
*
*/
function taskRun()
{
	if(!sw.Promed.GlobalVariables.statusInactivity) {
		getNewMessages();
	}
}
/** Функция для запускалки
*
*/
function extraTaskRun()
{
	if(getNoticeOptions().is_extra_message && !sw.Promed.GlobalVariables.statusInactivity) {
		getAdminMessages();
	}
}

/** Функция возвращает примитивный тип аргумента в нижнем регистре (!)
*	Пример:
*		alert(getPrimType(0))					// 'number'
*		alert(getPrimType('lalala'))			// 'string'
*		alert(getPrimType([1,2,3]))				// 'array'
*		alert(getPrimType({}))					// 'object'
*		alert(getPrimType(function a() {}))		// 'function'
*		alert(getPrimType(null))				// 'null'		в javascript typeof null === 'object'
*		alert(getPrimType(undefined))			// 'undefined'
*		alert(getPrimType(new Date()))			// 'date'
*		alert(getPrimType(new Image()))			// 'htmlimageelement'
*
*/
function getPrimType( v ) {
	if(typeof v === 'object') {
		return Object.prototype.toString.call(v).slice(8, -1).toLowerCase();
	} else {
		return typeof v;
	}
}

/** Функция определяет метод хранения данных справочников
 *
 */
function setDBDriver() {
	// Если используется remoteDB, то одна любая другая технология хранения тоже может быть использована
	Ext.isRemoteDB = isRemoteDB();
	if (Ext.isRemoteDB) {
		Ext.isIndexedDb = false;
		Ext.isWebSqlDB = false;
		Ext.isGears = false;
	}
	// Выбираем определенный драйвер, на случай если браузер поддерживает несколько технологий
	if (Ext.isGears) {
		Ext.isIndexedDb = false;
		Ext.isWebSqlDB = false;
	}
	if (Ext.isIndexedDb) {
		Ext.isWebSqlDB = false;
	}
}

function getListMenu(panel) {

}

/**
 * Проверяет наличие извещения, если его нет, то создает
 */
function checkEvnNotify(config) {
	if (!config || !config.Evn_id || !config.MorbusType_SysNick) {
		return false;
	}
	var callback = Ext.emptyFn;
	if (typeof config.callback == 'function') {
		callback = config.callback;
	}
	if (sw.isExt6Menu) sw.Promed.mask.show();
	Ext.Ajax.request({
		url: '/?c=Common&m=checkEvnNotify',
		params: {
			Evn_id: config.Evn_id,
			EvnDiagPLSop_id: config.EvnDiagPLSop_id || null,
			MorbusType_SysNick: config.MorbusType_SysNick
		},
		callback: function(options, success, response) {
			if (sw.isExt6Menu) sw.Promed.mask.hide();
			var result = Ext.util.JSON.decode(response.responseText);
			if (result.success) {
				_processingResponseCheckEvnNotify(result, callback, 'checkEvnNotify');
			}
		}
	});
	return true;
}

/**
 * Проверяет наличие извещения, если его нет, то создает
 */
function _processingResponseCheckEvnNotify(result, callback, mode) {
	switch(result.EvnClass_SysNick) {
		case 'EvnVizitPLStom':
		case 'EvnDiagPLStom':
		case 'EvnVizitPL':
		case 'EvnPS'://для приемн.отд-я в стационаре
		case 'EvnSection':
			if (!result.MorbusType_List) {
				result.MorbusType_List = {};
			}
			var morbus_type;
			for (morbus_type in result.MorbusType_List) {
				switch (true) {
					case ('pregnancy' == morbus_type):
						if (result.MorbusType_List[morbus_type].PersonRegister_id) {
							checkEvnNotifyHIVPreg({
								EvnNotifyHIVPreg_pid: result.Evn_id
								,Server_id: result.Server_id
								,Person_id: result.Person_id
								,PersonEvn_id: result.PersonEvn_id
								,MedPersonal_id: getGlobalOptions().medpersonal_id
								,Diag_id: result.Diag_id
								,EvnNotifyHIVPreg_setDT: getGlobalOptions().date
								,Morbus_id: result.MorbusType_List[morbus_type].Morbus_id || null
								,EvnNotifyHIVPreg_id: result.MorbusType_List[morbus_type].EvnNotifyHIVPreg_id || null
								,PersonRegister_id: result.MorbusType_List[morbus_type].PersonRegister_id || null
								,mode: mode
							});
						}
						break;
					case ('onko' == morbus_type):
						checkEvnOnkoNotify({
							EvnOnkoNotify_pid: result.Evn_id
							,Server_id: result.Server_id
							,Person_id: result.Person_id
							,PersonEvn_id: result.PersonEvn_id
							,MedPersonal_id: getGlobalOptions().medpersonal_id
							,Diag_id: result.Diag_id
							,Diag_Code: result.Diag_Code
							,EvnOnkoNotify_setDT: getGlobalOptions().date
							,Morbus_id: result.MorbusType_List[morbus_type].Morbus_id || null
							,EvnDiagPLSop_id: result.MorbusType_List[morbus_type].EvnDiagPLSop_id || null
							,EvnOnkoNotify_id: result.MorbusType_List[morbus_type].EvnNotifyBase_id || null
							,EvnOnkoNotifyNeglected_id: result.MorbusType_List[morbus_type].EvnOnkoNotifyNeglected_id || null
							,PersonRegister_id: result.MorbusType_List[morbus_type].PersonRegister_id || null
							,TumorStage_id: result.MorbusType_List[morbus_type].TumorStage_id || null
							,Alert_Msg: result.MorbusType_List[morbus_type].Alert_Msg || null
							,callback: callback
							,mode: mode
						});
						break;
					case ('nephro' == morbus_type):
						checkEvnNotifyNephro({
							EvnNotifyNephro_pid: result.Evn_id
							,Server_id: result.Server_id
							,Person_id: result.Person_id
							,PersonEvn_id: result.PersonEvn_id
							,MedPersonal_id: getGlobalOptions().medpersonal_id
							,Diag_id: result.Diag_id
							,Diag_Code: result.Diag_Code
							,EvnNotifyNephro_setDate: getGlobalOptions().date
							,Morbus_id: result.MorbusType_List[morbus_type].Morbus_id || null
							,EvnNotifyNephro_id: result.MorbusType_List[morbus_type].EvnNotifyBase_id || null
							,PersonRegister_id: result.MorbusType_List[morbus_type].PersonRegister_id || null
							,PersonRegisterOutCause_id: result.MorbusType_List[morbus_type].PersonRegisterOutCause_id || null
							,callback: callback
							,mode: mode
						});
						break;
					case ('prof' == morbus_type):
						checkEvnNotifyProf({
							EvnNotifyProf_pid: result.Evn_id
							,Server_id: result.Server_id
							,Person_id: result.Person_id
							,PersonEvn_id: result.PersonEvn_id
							,MedPersonal_id: getGlobalOptions().medpersonal_id
							,Diag_id: result.Diag_id
							,EvnNotifyProf_setDate: getGlobalOptions().date
							,Morbus_id: result.Morbus_id || null
							,EvnNotifyProf_id: result.EvnNotifyBase_id
							,PersonRegister_id: (result.PersonRegister_id)?result.PersonRegister_id:null
							,Diag_Code: result.Diag_Code
							,callback: callback
							,mode: mode
						});
						break;
					case ('narc' == morbus_type): // Наркология
					case ('crazy' == morbus_type): // Психиатрия
					case ('hepa' == morbus_type): //Создание/проверка наличия извещения и записи регистра с логикой автоматического включения в регистр при создании извещения о вирусном гепатите
					case ('orphan' == morbus_type): // орфанные
					case ('tub' == morbus_type): // туберкулез, почти логика такая же как для гепатита
					case ('vener' == morbus_type): // венеро, логика почти такая же как для гепатита
					case ('hiv' == morbus_type && getRegionNick() != 'kaluga'): // вич, логика почти такая же как для гепатита
						checkEvnNotifyBaseWithAutoIncludeInPersonRegister(result, mode, morbus_type, callback);
						break;
				}
			}
			break;
	}
}

/**
* Проверка существования записи в регистре по суицидам и необходимости внесения
* Стандартный функционал регистров не подходит, т.к. тут нет извещений
*/
function checkSuicideRegistry (config) {
	var scope = this;
	if (!config || !config.Evn_id || !config.EvnClass_SysNick) {
		return false;
	}

	Ext.Ajax.request({
		url: '/?c=Common&m=checkSuicideRegistry',
		params: {
			Evn_id: config.Evn_id,
			EvnClass_SysNick: config.EvnClass_SysNick
		},
		callback: function(options, success, response) {
			var result = Ext.util.JSON.decode(response.responseText);
			log(result);
			if (result && result.length > 0 && result[0].Diag_id) {
				sw.swMsg.show({
					buttons: Ext.Msg.YESNO,
					scope: scope,
					fn: function(buttonId) {
						if ( buttonId == 'yes' ) {
							if (getWnd('swPersonRegisterSuicideEditWindow').isVisible()) {
								getWnd('swPersonRegisterSuicideEditWindow').hide();
							}
							var params = result[0];
							params.action = 'add';
							getWnd('swPersonRegisterSuicideEditWindow').show(params);
						}
					},
					icon: Ext.Msg.QUESTION,
					msg: 'Включить пациента в регистр лиц, совершивших суицидальные попытки?',
					title: langs('Вопрос')
				});
			}
		}
	});
}

/**
 * Проверка существования записи в регистре по паллиативной помощи
 */
function checkPalliatRegistry(config) {
	if (!config || !config.Person_id) {
		return false;
	}

	Ext.Ajax.request({
		url: '/?c=MorbusPalliat&m=checkCanInclude',
		params: {
			Person_id: config.Person_id
		},
		callback: function(options, success, response) {
			var result = Ext.util.JSON.decode(response.responseText);
			if (result && result.length) {
				sw.swMsg.confirm(
					langs('Вопрос'),
					'Стадия опухолевого процесса пациента принадлежит к клинической группе «IV». Включить пациента в регистр паллиативных онкологических пациентов?',
					function(btn) {
						if ( btn == 'yes' ) {
							sw.Promed.personRegister.add({
								PersonRegisterType_SysNick: 'palliat'
								,MorbusType_SysNick: 'palliat'
								,Diag_id: config.Diag_id
								,registryType: 'palliat'
								,callback: Ext.emptyFn
								,person: {
									Person_id: result[0].Person_id
									,PersonEvn_id: result[0].PersonEvn_id
									,Server_id: result[0].Server_id
									,Person_Firname: result[0].Person_Firname
									,Person_Secname: result[0].Person_Secname
									,Person_Surname: result[0].Person_Surname
									,Person_Birthday: getValidDT(result[0].Person_Birthday, '')
								}
							});
						}
					}
				);
			}
		}
	});
}

/** Функция подписывает документ (любой документ, не обязательно Evn)
 *
 */
function signedDocument(config) {
	var msg = langs('Подписать данный документ?');
	var scope = this;
	var params = {id:null, type:'Evn'};
	var button;
	var allowQuestion = true;
	var callback = Ext.emptyFn;
	if (typeof config.callback == 'function') {
		callback = config.callback;
	}
	if (config) {
		// Опеределяем передали ли id документа
		if (config.Evn_id) {
			params.id = config.Evn_id;
			params.type = 'Evn';
		} else {
			if (config.id) {
				params.id = config.id;
				params.type = 'Doc';
			}
		}
		// Проверяем, может быть передали свой вопрос
		if (config.msg) {
			msg = config.msg;
		}
		// Проверяем, в рамках какого объекта планируется выполниться, если не определено, то в рамках this )
		if (config.scope) {
			scope = config.scope;
		}
		// Проверяем, передана ли кнопка по которой выполняется вызов
		if (config.button) {
			button = config.button;
		}
		// Проверим какой признак передается с формы
		if (config.Evn_IsSigned && config.Evn_IsSigned == 2 && (!config.msg)) {
			msg = langs('Отменить подпись документа?');
		}
		// Проверим можно ли выводить вопрос (по умолчанию можно)
		if (typeof config.allowQuestion != 'undefined') {
			allowQuestion = config.allowQuestion;
		}

	}

	var requestSignedDocument = function(params, button, config) {
		if (button) {
			button.disable();
		}
		Ext.Ajax.request({
			url: '/?c=Common&m=signedDocument',
			params: params,
			callback: function(options, success, response) {
				if (button) {
					button.enable();
				}
				var result = Ext.util.JSON.decode(response.responseText);
				/*if (result.success) {
					_processingResponseCheckEvnNotify(result, callback, 'signedDocument');
				}*/
				callback(result.success);
			}
		});
	};

	if (params.id) {
		if(allowQuestion) {
			// Вопрос
			sw.swMsg.show({
				buttons: Ext.Msg.YESNO,
				scope : scope,
				fn: function(buttonId) {
					if ( buttonId == 'yes' ) {
						requestSignedDocument(params, button, config);
					}
				},
				icon: Ext.Msg.QUESTION,
				msg: msg,
				title: langs('Вопрос')
			});
		} else {
			requestSignedDocument(params, button, config);
		}
	}
}

function saveEvnReceptIsPrinted(config) {
	var button;
	var callback = Ext.emptyFn;
	var reprinting_disabled = (getRegionNick() == 'msk'); //для Москвы повторная печать запрещена

	if (typeof config.callback == 'function') {
		callback = config.callback;
	}
	if (config.reprinting_disabled != undefined) {
        reprinting_disabled = config.reprinting_disabled;
	}

	// Проверяем, передана ли кнопка по которой выполняется вызов
	if (config.button) {
		button = config.button;
	}
	if (config.Evn_id) {
		if (button) {
			button.disable();
		}
		Ext.Ajax.request({
			url: '/?c=EvnRecept&m=saveEvnReceptIsPrinted',
			params: {
				EvnRecept_id: config.Evn_id
			},
			callback: function(options, success, response) {
				if (button) {
					button.enable();
				}
				var result = Ext.util.JSON.decode(response.responseText);

				if (reprinting_disabled && result.reprinting) {
                    sw.swMsg.alert(langs('Сообщение'), langs('Повторная печать рецепта недоступна. Если рецепт был испорчен, удалите его и добавьте новый.'));
				} else {
                    callback(result.success);
				}
			}
		});
	}
}

/**
 * Класс для синхронизации асинхронных действий
 */
sw.Promed.Doings = function() {
	return {
		items: {},
		fnList: {},
		doLater: function(name, fn){
			this.fnList[name] = (typeof fn == 'function')?fn:Ext.emptyFn;
			if (!this.hasDoings()) {
				for(name in this.fnList) {
					var fn = this.fnList[name];
					delete this.fnList[name];
					fn();
				}
			}
		},
		start: function(name) {
			this.items[name] = true;
		},
		finish: function(name) {
			this.items[name] = false;
			if (!this.hasDoings()) {
				for(name in this.fnList) {
					var fn = this.fnList[name];
					delete this.fnList[name];
					fn();
				}
			}
		},
		hasDoings: function() {
			var count = 0;
			for (var name in this.items) {
				if (this.items[name]) {
					count++;
				}
			}
			return (count > 0);
		}
	}
};

/** Набор методов исходов в госпитализации, в т.ч. отказов в госпитализации
 *
 */
sw.Promed.Leave = {
	store: null,
	/** Создание списка исходов в госпитализации
	 *
	 * option.id - id списка исходов
	 * option.ownerWindow
	 * option.getParams - функция, которая должна вернуть парамсы
	 * option.callbackEditWindow
	 * option.onHideEditWindow
	 * @param {Object} option
	 * @return {Object}
	 */
	getMenu: function(option) {
		//log(option);
		//var combo = new sw.Promed.SwLeaveTypeCombo({id:'LeaveTypeForMenu'});
		if(!this.store) {
			this.store = new Ext.db.AdapterStore({
				autoLoad: false,
				dbFile: 'Promed.db',
				fields: [
					{name: 'LeaveType_Name', mapping: 'LeaveType_Name'},
					{name: 'LeaveType_Code', mapping: 'LeaveType_Code'},
					{name: 'LeaveType_id', mapping: 'LeaveType_id'},
					{name: 'LeaveType_begDate', mapping: 'LeaveType_begDate', type: 'date', dateFormat: 'd.m.Y'},
					{name: 'LeaveType_endDate', mapping: 'LeaveType_endDate', type: 'date', dateFormat: 'd.m.Y'}
				],
				key: 'LeaveType_id',
				sortInfo: {field: 'LeaveType_Code'},
				tableName: (getRegionNick().inlist([ 'buryatiya', 'ekb', 'penza', 'pskov' ]) ? 'LeaveTypeFed' : 'LeaveType')
			});
		}
		var menu = new Ext.menu.Menu({id: option.id || 'menuLeaveType'});
		var conf = {
			callback: function(){
				if ( typeof option.filterLeaveTypeList == 'function' ) {
					option.filterLeaveTypeList(this.store);
				}
				// Получаем список исходов в госпитализации
				this.store.each(function(record) {
					menu.add({text: record.get('LeaveType_Code')+'.'+record.get('LeaveType_Name'), LeaveType_Code: record.get('LeaveType_Code'), LeaveType_id:record.get('LeaveType_id'), iconCls: 'leave16', handler: function() {
						option.LeaveType_id = this.LeaveType_id;
						option.LeaveType_Code = this.LeaveType_Code;
						option.ignoreCheckBeforeLeave = false;
						sw.Promed.Leave.select(option);
					}});
				});
				if(typeof option.onCreate == 'function') option.onCreate(menu);
			}.createDelegate(this)
		};

		if(this.store.getCount() > 0) {
			conf.callback();
		} else {
			this.store.load(conf);
		}
		return menu;
	},
	/** Открывает окно редактирования выбранного типа исхода
	 *
	 * option.LeaveType_id -
	 * option.LeaveType_Code -
	 * option.ownerWindow
	 * option.getParams - функция, которая должна вернуть парамсы
	 * option.callbackEditWindow
	 * option.onHideEditWindow
	 * @param {Object} option
	 * @return {Bool}
	 */
	select: function(option) {
		var me = this, data = option.getParams();

		if ( !Ext.isEmpty(option.LeaveType_Code) && !option.LeaveType_Code.toString().inlist([ '5', '104', '204' ]) && data.isEvnSectionLast == false ) { //data.EvnOtherSection_id > 0 &&
			sw.swMsg.alert(langs('Сообщение'), langs('Это не последнее движение, допустимый исход - перевод в другое отделение или перевод на другой профиль коек!'));
			return false;
		}

		if (!option.ignoreCheckBeforeLeave) {
			sw.Promed.EvnSection.checkBeforeLeave(
				option.ownerWindow,
				function(success){
					if (success) {
						option.ignoreCheckBeforeLeave = true;
						me.select(option);
					}
				},
				data.EvnPS_id,
				data.EvnSection_id,
				undefined,
				undefined,
				undefined,
				undefined,
				undefined,
				undefined,
				data.childPS
			);
			return true;
		}

		//BOB - 14.06.2018
		//console.log('BOB_option.ownerWindow.ARMType=',option.ownerWindow.ARMType); //BOB - 14.01.2019
		//if ((option.ownerWindow.ARMType)&&(option.ownerWindow.ARMType == "stac")){
		if (!option.ignoreCheckBeforeLeaveByReanimat) {
			sw.Promed.EvnSection.checkBeforeLeaveByReanimat(
				option.ownerWindow,
				function(success){
					if (success) {
						option.ignoreCheckBeforeLeaveByReanimat = true;
						me.select(option);
					}
				},
				data.EvnPS_id,
				data.EvnSection_id,
				data.Person_id,
				option.LeaveType_id
			);
			return true;
		}
		//}
		//BOB - 14.06.2018

		if (!option.ignoreCheckKardioPrivilegeConsent) {
			sw.Promed.EvnSection.checkKardioPrivilegeConsent(
				option.ownerWindow,
				function(success){
					if (success) {
						option.ignoreCheckKardioPrivilegeConsent = true;
						me.select(option);
					}
				},
				data.EvnPS_id,
				data.Person_id,
				option.LeaveType_id
			);
			return true;
		}

		var action = 'add';
		var params = new Object();

		params.EvnSection_id = data.EvnSection_id;
		params.LeaveType_id = option.LeaveType_id;
		params.Person_id = data.Person_id;
		params.PersonEvn_id = data.PersonEvn_id;
		params.Server_id = data.Server_id;

		if ( data.EvnLeave_setDate ) {
			params.EvnSection_disDate = data.EvnLeave_setDate;
		}

		if ( data.EvnLeave_UKL ) {
			params.EvnLeave_UKL = data.EvnLeave_UKL;
		}

		if ( data.LeaveCause_id ) {
			params.LeaveCause_id = data.LeaveCause_id;
		}

		if ( data.ResultDesease_id ) {
			params.ResultDesease_id = data.ResultDesease_id;
		}

		if ( data.EvnPS_id ) {
			params.EvnPS_id = data.EvnPS_id;
		}

		if ( getWnd('swEmkEvnSectionEditWindow').isVisible() ) {
			sw.swMsg.alert(langs('Сообщение'), langs('Окно редактирования исхода госпитализации пациента уже открыто'));
			return false;
		}

		getWnd('swEmkEvnSectionEditWindow').show({
			action: action,
			callback: function(ndata) {
				if ( typeof option.callbackEditWindow == 'function' ) {
					option.callbackEditWindow(ndata);
				}
			}.createDelegate(this),
			formParams: params,
			onHide: function() {
				if ( typeof option.onHideEditWindow == 'function' ) {
					option.onHideEditWindow();
				}
			}.createDelegate(this),
			Person_id: data.Person_id,
			ARMType_id: data.ARMType_id,
			EvnPS_id: data.EvnPS_id,
			Person_Birthday: data.Person_Birthday,
			Person_Firname: data.Person_Firname,
			Person_Secname: data.Person_Secname,
			Person_Surname: data.Person_Surname
		});
		return true;
	},
	openEditWindow: function(option) {
		if ( getWnd('swEmkEvnSectionEditWindow').isVisible() ) {
			sw.swMsg.alert(langs('Сообщение'), langs('Окно редактирования исхода госпитализации пациента уже открыто'));
			return false;
		}

		var data = option.getParams();

		getWnd('swEmkEvnSectionEditWindow').show({
			action: 'edit',
			callback: function(ndata) {
				if ( typeof option.callbackEditWindow == 'function' ) {
					option.callbackEditWindow(ndata);
				}
			}.createDelegate(this),
			formParams: {
				EvnSection_id: data.EvnSection_id
			},
			onHide: function() {
				if ( typeof option.onHideEditWindow == 'function' ) {
					option.onHideEditWindow();
				}
			}.createDelegate(this),
			Person_id: data.Person_id,
			ARMType_id: data.ARMType_id,
			EvnPS_id: data.EvnPS_id,
			Person_Birthday: data.Person_Birthday,
			Person_Firname: data.Person_Firname,
			Person_Secname: data.Person_Secname,
			Person_Surname: data.Person_Surname
		});
	},
	doSign: function(options) {
		var loadMask = new Ext.LoadMask(options.ownerWindow.getEl(), {msg: "Подписание исхода из отделения..."});
		loadMask.show();

		Ext.Ajax.request({
			failure: function(response, opt) {
				loadMask.hide();
				sw.swMsg.alert(langs('Ошибка'), langs('Ошибка при подписании исхода из отделения'));
			},
			params: options,
			success: function(response, opt) {
				loadMask.hide();
				if (response.responseText) {
					var answer = Ext.util.JSON.decode(response.responseText);
					if (answer.success) {
						if(typeof options.callback == 'function')
							options.callback();
					} else if (answer.Error_Code) {
						Ext.Msg.alert(langs('Ошибка #')+answer.Error_Code, answer.Error_Message);
					}
				}
				else {
					Ext.Msg.alert(langs('Ошибка'), langs('Ошибка при подписании исхода из отделения! Отсутствует ответ сервера.'));
				}
			},
			url: '/?c=EvnPS&m=signLeaveEvent'
		});
	},
	sign: function(options) {
		// @options.ownerWindow object
		// @options.callback function()
		// @options.LeaveEvent_pid int
		// @options.LeaveEvent_rid int
		// @options.LeaveEvent_id int
		// @options.LeaveType_id int
		// @options.parentClass string
		if ( typeof options != 'object' || options.length == 0 ) {
			return false;
		}

		Ext.Msg.show({
			title: langs('Внимание'),
			msg: langs('После подписания исхода невозможно будет редактировать данные, связанные с данным движением, в том числе эпикриз соответствующий исходу из отделения. Продолжить?'),
			buttons: Ext.Msg.YESNO,
			fn: function(btn) {
				if (btn === 'yes') {
					sw.Promed.Leave.doSign(options);
				} else {
					return false;
				}
			},
			icon: Ext.MessageBox.QUESTION
		});
	},
	/** Удаление исхода госпитализации в профильное отделение */
	deleteLeave: function(options) {
		if ( !options || !options.ownerWindow || !options.EvnSection_id ) {
			return false;
		}

		var loadMask = new Ext.LoadMask(options.ownerWindow.getEl(), { msg: "Удаление исхода госпитализации в профильное отделение..." });
		loadMask.show();
		Ext.Ajax.request({
			failure: function(response, opt) {
				loadMask.hide();
				sw.swMsg.alert(langs('Ошибка'), langs('Ошибка при удалении исхода госпитализации в профильное отделение'));
			},
			params: {EvnSection_id: options.EvnSection_id},
			success: function(response, opt) {
				loadMask.hide();
				if (response.responseText) {
					var answer = Ext.util.JSON.decode(response.responseText);
					if (answer.success) {
						if (typeof options.callback == 'function')
							options.callback();
					}
				} else {
					Ext.Msg.alert(langs('Ошибка'), langs('Ошибка при удалении исхода госпитализации в профильное отделение! Отсутствует ответ сервера.'));
				}
			},
			url: '/?c=EvnSection&m=deleteLeave'
		});
		return true;
	}
};

sw.Promed.LpuSectionWard = {
	/** Асинхронное создание списка палат, в которые можно перевести
	 *
	 * option.LpuSection_id - Список палат только по данному отделению
	 * option.date - Список палат только на эту дату
	 * option.id - id списка палат
	 * option.getParams - функция, которая должна вернуть парамсы для проставления палат (LpuSection_id,EvnSection_id,LpuSectionWardCur_id,Person_id,Sex_id,ignore_sex), ignore_sex - boolean флаг, чтобы игнорировать пол человека
	 * option.onSuccess - функция, которая выполняется при успешном проставлении палаты
	 * option.callback - функция, которая выполняется при создании меню
	 * @param {Object} option
	 * @return boolean
	 */
	createListLpuSectionWard: function(option) {
		if(typeof option.getParams != 'function' || typeof option.callback != 'function' || typeof option.onSuccess != 'function' || !option.LpuSection_id)
			return false;
		// Получаем список палат по данному отделению
		Ext.Ajax.request({
			url: '/?c=EvnSection&m=getLpuSectionWardList',
			params: {LpuSection_id: option.LpuSection_id, date: option.date},
			failure: function(response, options) {
				Ext.Msg.alert(langs('Ошибка'), langs('При получении списка палат произошла ошибка!'));
			},
			success: function(response, action)
			{
				if (response.responseText) {
					var result = Ext.util.JSON.decode(response.responseText);
					var menu = new Ext.menu.Menu({id: (option.id || 'menuLpuSectionWard')+option.LpuSection_id});
					var ico = 'tree-rooms-none16';
					menu.add({text: langs('Без палаты'), LpuSection_id: option.LpuSection_id, Sex_id: null, LpuSectionWard_id: '0', iconCls: ico, handler: function() {
						sw.Promed.LpuSectionWard.setWard({
							LpuSectionWard_id: '0',
							onSuccess: option.onSuccess,
							params: option.getParams()
						});
					}});
					if (result) {
						for(var i = 0;i < result.length;i++) {
							if(result[i]['Sex_id'] == 2)
								ico = 'tree-rooms-female16';
							else if(result[i]['Sex_id'] == 1)
								ico = 'tree-rooms-male16';
							else
								ico = 'tree-rooms-common16';
							if (getRegionNick() != 'kz') {
								menu.add({text: result[i]['LpuSectionWard_Name'], LpuSection_id: result[i]['LpuSection_id'], Sex_id: result[i]['Sex_id'], LpuSectionWard_id:result[i]['LpuSectionWard_id'], iconCls: ico, handler: function() {
									sw.Promed.LpuSectionWard.setWard({
										LpuSectionWard_id: this.LpuSectionWard_id,
										onSuccess: option.onSuccess,
										params: option.getParams()
									});
								}});
							} else {
								var submenu = new Ext.menu.Menu({id: 'menuLpuSectionWardBed' + result[i]['LpuSectionWard_id']});
								for(var j = 0; j < result[i]['beds'].length; j++) {
									submenu.add({text: result[i]['beds'][j]['BedProfileRuFull'], LpuSectionWard_id: result[i]['beds'][j]['GetBed_id'], handler: function() {
										sw.Promed.LpuSectionWard.setWard({
											LpuSectionWard_id: this.LpuSectionWard_id,
											onSuccess: option.onSuccess,
											params: option.getParams()
										});
									}});
								}
								menu.add({text: result[i]['LpuSectionWard_Name'], LpuSection_id: result[i]['LpuSection_id'], Sex_id: result[i]['Sex_id'], LpuSectionWard_id:result[i]['LpuSectionWard_id'], iconCls: ico, menu: submenu });
							}
						}
						option.callback(menu);
					}
				}
				else {
					Ext.Msg.alert(langs('Ошибка'), langs('При получении списка палат произошла ошибка! Отсутствует ответ сервера.'));
				}
			}
		});
		return true;
	},
	/** Создание списка палат из swLpuSectionWardGlobalStore, в которые можно перевести
	 *
	 * option.LpuSection_id - Список палат только по данному отделению
	 * option.id - id списка палат
	 * option.getParams - функция, которая должна вернуть парамсы для проставления палат (LpuSection_id,EvnSection_id,LpuSectionWardCur_id,Person_id,Sex_id,ignore_sex), ignore_sex - boolean флаг, чтобы игнорировать пол человека
	 * option.onSuccess - функция, которая выполняется при успешном проставлении палаты
	 * @param {Object} option
	 * @return {Object}
	 */
	getMenu: function(option) {
		if(typeof option.getParams != 'function' || typeof option.onSuccess != 'function' || !option.LpuSection_id)
			return false;
		var ico = '';
		var menu = new Ext.menu.Menu({id: (option.id || 'menuLpuSectionWard')+option.LpuSection_id});
		var params = option.getParams();
		// Получаем список палат по данному отделению
		setLpuSectionWardGlobalStoreFilter({
			LpuSection_id: params.LpuSection_id,
			onDate: params.date
		});
		swLpuSectionWardGlobalStore.each(function(record) {
			if (record.get('LpuSection_id')==option.LpuSection_id) {
				if(record.get('Sex_id') == 2)
					ico = 'tree-rooms-female16';
				else if(record.get('Sex_id') == 1)
					ico = 'tree-rooms-male16';
				else
					ico = 'tree-rooms-common16';
				menu.add({text: record.get('LpuSectionWard_Name'), LpuSection_id: record.get('LpuSection_id'), LpuSectionWard_id:record.get('LpuSectionWard_id'), iconCls: ico, handler: function() {
					sw.Promed.LpuSectionWard.setWard({
						LpuSectionWard_id: this.LpuSectionWard_id,
						onSuccess: option.onSuccess,
						params: params
					});
				}});
			}
		});
		return menu;
	},
	/** Запрос на перевод в палату
	 *
	 * option.LpuSectionWard_id - выбранная палата
	 * option.params - параметры
	 * option.onSuccess - функция, которая выполняется при успешном проставлении палаты
	 * @param {Object} option
	 * @return {void}
	 */
	setWard: function(option) {
		if (option.params.LpuSectionWardCur_id!=option.LpuSectionWard_id) { // Если палата, в которой находится пациент, отличается от палаты, в которую выполняется перевод
			var params = {};
			//палата записывается или в EvnSection или в EvnPS
			params.EvnPS_id = (option.params.EvnPS_id && !option.params.EvnSection_id)?option.params.EvnPS_id:null;
			params.EvnSection_id = (!option.params.EvnPS_id && option.params.EvnSection_id)?option.params.EvnSection_id:null;
			params.LpuSection_id = option.params.LpuSection_id;
			params.Sex_id = option.params.Sex_id;
			params.Person_id = option.params.Person_id;
			params.LpuSectionWard_id = option.LpuSectionWard_id;
			params.ignore_sex = option.params.ignore_sex ? 1 : 0 ;
			params.LpuSectionWardCur_id = option.params.LpuSectionWardCur_id;
			Ext.Ajax.request({
				url: '/?c=EvnSection&m=setEvnSectionWard',
				params: params,
				failure: function(response, options) {
					Ext.Msg.alert(langs('Ошибка'), langs('При переводе в палату произошла ошибка!'));
				},
				success: function(response, action)
				{
					if (response.responseText) {
						var answer = Ext.util.JSON.decode(response.responseText);
						if (answer.success) {
							if(typeof option.onSuccess == 'function')
								option.onSuccess(params);
						} else if (answer.Error_Code) {
							Ext.Msg.alert(langs('Ошибка #')+answer.Error_Code, answer.Error_Message);
						} else if (answer.Alert_Msg) {
							sw.swMsg.show(
							{
								icon: Ext.MessageBox.QUESTION,
								msg: answer.Alert_Msg + '<br /> Продолжить?',
								title: langs('Вопрос'),
								buttons: Ext.Msg.YESNO,
								fn: function(buttonId, text, obj)
								{
									if ('yes' == buttonId)
									{
										option.params.ignore_sex = true;
										sw.Promed.LpuSectionWard.setWard(option);
									}
								}
							});
						}

					}
					else {
						Ext.Msg.alert(langs('Ошибка'), langs('При переводе в палату произошла ошибка! Отсутствует ответ сервера.'));
					}
				}
			});
		}
	},
	/**
	 * Загружаем список палат, в котором должны быть: указанная палата и остальные палаты профильного
	 * отделения, соответствующие полу пациента (включая общие палаты),
	 * в которых есть свободные места
	 *
	 * @param {Object} option
	 * {Object} option.win
	 * {Object} option.lpuSectionWardCombo
	 * {int} option.LpuSection_id
	 * {int} option.Sex_id
	 * {string} option.date
	 * @return {void}
	 */
	filterWardBySex: function(option, mode) {
		option.date = option.date || getGlobalOptions().date;
		var lpusectionward_id = option.lpuSectionWardCombo.getValue(),
			params = {
				mode: 'combo',
				date: option.date,
				LpuSection_id: option.LpuSection_id,
				Sex_id: option.Sex_id
			};
		if( mode ) {
			params.mode = mode;
		}
		if( lpusectionward_id > 0) {
			params.mode = 'freelyprofil';
			params.LpuSectionWard_id = lpusectionward_id;
		}
		if( option.LpuSectionBedProfile_id > 0) {
			params.LpuSectionBedProfile_id = option.LpuSectionBedProfile_id;
		}
		var store = option.lpuSectionWardCombo.getStore();
		store.load({
			params: params,
			callback: function() {
				var index = option.lpuSectionWardCombo.getStore().findBy(function (record, id) {
					return (record.get('LpuSectionWard_id') == lpusectionward_id);
				});
				if (index < 0) {
					lpusectionward_id = null;
				} else {
					option.win.oldLpuSectionWard_id = lpusectionward_id;
				}
				option.lpuSectionWardCombo.setValue(lpusectionward_id);

				if (option.callback && typeof option.callback == 'function') {
					option.callback();
				}
			}
		});
	}
};
sw.Promed.LpuSectionBedProfileFilters = {

	filterBedProfileByWard: function(option, mode) {
		option.date = option.date || getGlobalOptions().date;
		var lpusectionbedprofilelink_id = option.lpuSectionBedProfileCombo.getValue(),
			params = {
				date: option.date,
				LpuSection_id: option.LpuSection_id,
				Is_Child: option.Is_Child
			};

		if( option.LpuSectionWard_id > 0) {
			params.LpuSectionWard_id = option.LpuSectionWard_id;
		}
		var store = option.lpuSectionBedProfileCombo.getStore();
		store.load({
			params: params,
			callback: function() {
				var index = option.lpuSectionBedProfileCombo.getStore().findBy(function (record, id) {
					return (record.get('LpuSectionBedProfile_id') == lpusectionbedprofilelink_id);
				});
				if (index < 0) {
					lpusectionbedprofilelink_id = null;
				} else {
					option.win.oldLpuSectionBedProfileLink_id = lpusectionbedprofilelink_id;
				}
				option.lpuSectionBedProfileCombo.setValue(this.data.items.length === 1 ? this.data.items[0].id : '');

				if (option.callback && typeof option.callback == 'function') {
					option.callback();
				}
			}
		});
	}
};

sw.Promed.MedPersonal = {
	/** Создание списка врачей указанного отделения, которых можно прописать в движение
	 *
	 * option.LpuSection_id - Список врачей только по данному отделению
	 * option.id - id списка врачей
	 * option.getParams - метод, который должен вернуть парамсы для проставления врача (LpuSection_id,EvnSection_id,MedPersonalCur_id,Person_id)
	 * option.onSuccess - функция, которая выполняется при успешном проставлении врача
	 * @param {Object} option
	 * @return {Object}
	 */
	getMenu: function(option) {
		if(typeof option.getParams != 'function' || typeof option.onSuccess != 'function' || !option.LpuSection_id)
			return false;
		var menu = new Ext.menu.Menu({id: (option.id || 'menuMedPersonal')+option.LpuSection_id});
		// Получаем список врачей по данному отделению
		setMedStaffFactGlobalStoreFilter({
			allowDuplacateMSF: true,
			EvnClass_SysNick: 'EvnSection',
			onDate: getGlobalOptions().date,
			isStac: true,
			//isDoctor: true,
			LpuSection_id: option.LpuSection_id
		});
		swMedStaffFactGlobalStore.each(function(record) {
			menu.add({text: record.get('MedPersonal_Fio')+' '+record.get('PostMed_Name'), LpuSection_id: record.get('LpuSection_id'), MedPersonal_id:record.get('MedPersonal_id'), MedStaffFact_id:record.get('MedStaffFact_id'), iconCls: 'staff16', handler: function() {
				sw.Promed.MedPersonal.setMedPersonal({
					MedPersonal_id: this.MedPersonal_id,
					MedStaffFact_id: this.MedStaffFact_id,
					onSuccess: option.onSuccess,
					params: option.getParams()
				});
			}});
		});
		return menu;
	},
	/** Создание списка врачей по идентификаторам
	 *  
	 * option.LpuSection_id - Список врачей только по данному отделению
	 * option.id - id списка врачей
	 * option.getParams - метод, который должен вернуть парамсы для проставления врача (LpuSection_id,EvnSection_id,MedPersonalCur_id,Person_id)
	 * option.ids - список идентификаторов
	 * @param {Object} option 
	 * @return {Object}
	 */
	getMedPersonalListById: function(option) {
		if(typeof option.getParams != 'function' || !option.LpuSection_id)
			return false;
		var menu = new Ext.menu.Menu({id: (option.id || 'menuMedPersonal')+option.LpuSection_id});
		// Получаем список врачей по данному отделению
		option.ids.forEach(function(record) {
			menu.add({
				text: option.personals[record],
				iconCls: 'staff16', 
				handler: null
			});
			
		});
		return menu;
	},
	/** Запрос на изменение врача в движении
	 *
	 * option.MedPersonal_id - врач
	 * option.params - параметры
	 * option.onSuccess - функция, которая выполняется при успешном проставлении врача
	 * @param {Object} option
	 * @return {void}
	 */
	setMedPersonal: function(option) {
		if (option.params.MedPersonalCur_id!=option.MedPersonal_id) { // Если врач, который указан в движении, отличается от врача, которого надо записать в движение
			var params = {};
			params.LpuSection_id = option.params.LpuSection_id;
			params.EvnSection_id = option.params.EvnSection_id;
			params.EvnSection_pid = option.params.EvnSection_pid||null;
			params.Person_id = option.params.Person_id;
			params.PersonEvn_id = option.params.PersonEvn_id;
			params.Server_id = option.params.Server_id;
			params.MedPersonal_id = option.MedPersonal_id;
			params.MedStaffFact_id = option.MedStaffFact_id;
			Ext.Ajax.request({
				url: '/?c=EvnSection&m=setEvnSectionMedPersonal',
				params: params,
				failure: function(response, options) {
					Ext.Msg.alert(langs('Ошибка'), langs('При изменении врача произошла ошибка!'));
				},
				success: function(response, action)
				{
					if (response.responseText) {
						var answer = Ext.util.JSON.decode(response.responseText);
						if (answer.success) {
							if(typeof option.onSuccess == 'function')
								option.onSuccess();
						} else if (answer.Error_Code) {
							Ext.Msg.alert(langs('Ошибка #')+answer.Error_Code, answer.Error_Message);
						}
					}
					else {
						Ext.Msg.alert(langs('Ошибка'), langs('При изменении врача произошла ошибка! Отсутствует ответ сервера.'));
					}
				}
			});
		}
	}
};


// TODO: Решить с АРМType - по идее, он вообще теперь извне не должен приходить, а определяться АРМом по умолчанию
// TODO: _check надо убрать, получение переделать
sw.Promed.MedStaffFactByUser = {
	ARMType: null,
	onSelect: null,
	store: null,
	last: null,
	current: null,
	/** Инициализация, проверка наличия, загрузка в store списка рабочих мест пользователя текущей МО
	 *
	 * option._ini_callback
	 * @access private
	 * @param {Object} option
	 * @return void
	 */
	_ini: function(option) {
		log({'_ini': option});
		this.onSelect = option.onSelect || function(data) {console.log({userMedStaffFact: data});if (data.ARMForm) getWnd(data.ARMForm).show({ARMType: data.ARMType, userMedStaffFact: data});};
		this.ARMType = option.ARMType || 'common';
		this.last = null;
		if(!this.store) {
			this.store = new Ext.data.Store({
				proxy: (Ext.version == '2.3.0' ? null : {
					type: 'ajax',
					actionMethods: {create: "POST", read: "POST", update: "POST", destroy: "POST"},
					url: '/?c=User&m=getMSFList',
					reader: {
						type: 'json',
						rootProperty: 'data'
					}
				}),
				reader: new Ext.data.JsonReader(
				{
					id: 'id'
				},
				[
					{name: 'id', mapping: 'id'},
					{name: 'MedStaffFact_id', mapping: 'MedStaffFact_id'},
					{name: 'LpuSection_id', mapping: 'LpuSection_id'},
					{name: 'MedPersonal_id', mapping: 'MedPersonal_id'},
					{name: 'LpuSection_Name', type: 'string', mapping: 'LpuSection_Name'},
					{name: 'LpuSection_Nick', type: 'string', mapping: 'LpuSection_Nick'},
					{name: 'LpuSectionProfile_id', type: 'string', mapping: 'LpuSectionProfile_id'},
					{name: 'LpuSectionProfile_Code', type: 'string', mapping: 'LpuSectionProfile_Code'},
					{name: 'LpuSectionProfile_Name', type: 'string', mapping: 'LpuSectionProfile_Name'},
					{name: 'PostMed_Name', type: 'string', mapping: 'PostMed_Name'},
					{name: 'PostMed_Code', type: 'string', mapping: 'PostMed_Code'},
					{name: 'PostMed_id', mapping: 'PostMed_id'},
					{name: 'LpuBuilding_id', mapping: 'LpuBuilding_id'},
					{name: 'LpuBuilding_Name', type: 'string', mapping: 'LpuBuilding_Name'},
					{name: 'LpuUnit_id', mapping: 'LpuUnit_id'},
					{name: 'LpuUnit_Name', type: 'string', mapping: 'LpuUnit_Name'},
					{name: 'Timetable_isExists', type: 'string', mapping: 'Timetable_isExists'},
					{name: 'LpuUnitType_SysNick', type: 'string', mapping: 'LpuUnitType_SysNick'},
					{name: 'LpuUnitType_id', mapping: 'LpuUnitType_id'},
					{name: 'MedService_id', mapping: 'MedService_id'},
					{name: 'MedService_Nick', type: 'string', mapping: 'MedService_Nick'},
					{name: 'MedService_Name', type: 'string', mapping: 'MedService_Name'},
					{name: 'MedServiceType_SysNick', type: 'string', mapping: 'MedServiceType_SysNick'},
					{name: 'MedService_IsExternal', type: 'int', mapping: 'MedService_IsExternal'},
					{name: 'MedService_IsLocalCMP', type: 'int', mapping: 'MedService_IsLocalCMP'},
					{name: 'MedService_LocalCMPPath', type: 'string', mapping: 'MedService_LocalCMPPath'},
					{name: 'Name', type: 'string', mapping: 'Name'},
					{name: 'Lpu_Nick', type: 'string', mapping: 'Lpu_Nick'},
					{name: 'Org_Nick', type: 'string', mapping: 'Org_Nick'},
					{name: 'ARMNameLpu', mapping: 'ARMNameLpu'},
					{name: 'Lpu_id', type: 'int', mapping: 'Lpu_id'},
					{name: 'Org_id', type: 'int', mapping: 'Org_id'},
					{name: 'ARMType', mapping: 'ARMType'},
					{name: 'ARMName', mapping: 'ARMName'},
					{name: 'ARMForm', mapping: 'ARMForm'},
					{name: 'ARMType_id', mapping: 'ARMType_id'},
					{name: 'MedicalCareKind_id', mapping: 'MedicalCareKind_id'},
					{name: 'PostKind_id', mapping: 'PostKind_id'},
					{name: 'MedStaffFactLink_id', mapping: 'MedStaffFactLink_id'},
					{name: 'MedStaffFactLink_begDT', mapping: 'MedStaffFactLink_begDT'},
					{name: 'MedStaffFactLink_endDT', mapping: 'MedStaffFactLink_endDT'},
					{name: 'Post_Name', type: 'string', mapping: 'Post_Name'},
					{name: 'MedPersonal_FIO', mapping: 'MedPersonal_FIO'},
					{name: 'UslugaComplex_Name', mapping: 'UslugaComplex_Name'},
					{name: 'MedStaffFactCache_IsDisableInDoc', mapping: 'MedStaffFactCache_IsDisableInDoc'},
					{name: 'ElectronicService_id', mapping: 'ElectronicService_id'},
					{name: 'ElectronicService_Num', mapping: 'ElectronicService_Num'},
					{name: 'ElectronicService_Name', mapping: 'ElectronicService_Name'},
					{name: 'ElectronicQueueInfo_id', type: 'int', mapping: 'ElectronicQueueInfo_id'},
					{name: 'ElectronicService_isShownET', type: 'int', mapping: 'ElectronicService_isShownET'},
					{name: 'ElectronicTreatment_ids', type: 'string', mapping: 'ElectronicTreatment_ids'},
					{name: 'ElectronicQueueInfo_CallTimeSec', type: 'int', mapping: 'ElectronicQueueInfo_CallTimeSec'},
					{name: 'ElectronicQueueInfo_PersCallDelTimeMin', type: 'int', mapping: 'ElectronicQueueInfo_PersCallDelTimeMin'},
					{name: 'ElectronicQueueInfo_CallCount', type: 'int', mapping: 'ElectronicQueueInfo_CallCount'},
					{name: 'ElectronicScoreboard_id', type: 'int', mapping: 'ElectronicScoreboard_id'},
					{name: 'ElectronicScoreboard_IPaddress', type: 'string', mapping: 'ElectronicScoreboard_IPaddress'},
					{name: 'ElectronicScoreboard_Port', type: 'int', mapping: 'ElectronicScoreboard_Port'},
					{name: 'UslugaComplexMedService_id', type: 'int', mapping: 'UslugaComplexMedService_id'},
					{name: 'UslugaComplex_id', type: 'int', mapping: 'UslugaComplex_id'},
					{name: 'Storage_id', type: 'int', mapping: 'Storage_id'},
					{name: 'Storage_pid', type: 'int', mapping: 'Storage_pid'},
					{name: 'client', mapping: 'client'}
				]),
				url: '/?c=User&m=getMSFList'
			});
		}


		var store = this.store;
		store.clearFilter();

		//if( /*store.getCount()==0*/ true ) {
		var that = this;
		if( store.getCount()==0 || !getDefaultARM() || (getDefaultARM() && getGlobalOptions().lpu_id != getDefaultARM().Lpu_id) || (store.Lpu_id && store.Lpu_id != getGlobalOptions().lpu_id) ) { // Если ноль записей или поменяли МО то загружаем данные
			//if(getGlobalOptions().medstafffact.length > 0)
			//console.warn('Читаем сторе...');
            store.Lpu_id = getGlobalOptions().lpu_id;
			store.load({
				callback: function() {
					store.sort('ARMName', 'ASC'); // добавил сортировку для удобства.
					option._ini_callback();
					if (!Ext.isEmpty(HIDE_MENU_ON_ARMS) && HIDE_MENU_ON_ARMS == 1 && !isSuperAdmin() && !isLpuAdmin() && !isLpuCadrAdmin() && typeof main_card_toolbar != 'undefined')
					{
						if (sw.Promed.MedStaffFactByUser.store.getCount() > 0) {
							main_card_toolbar.getLayout().setActiveItem(1);
						} else {
							main_card_toolbar.getLayout().setActiveItem(0);
						}
						main_card_toolbar.setVisible(true);
						main_center_panel.setHeight(main_frame.getEl().getHeight());
					}
					if(getGlobalOptions().ArmMenuTitle==1)
						that.linkMenu = sw.Promed.MedStaffFactByUser.createListWorkPlaces(option);
				}
			});
		} else {
			option._ini_callback();
		}
	},
	/** Проверка соответствия рабочего места АРМу
	 *
	 * @access private
	 * @param {object} data
	 * @return boolean
	 */
	_check: function(data) {
		/*
		if ( !data.LpuUnitType_SysNick )
			return false;
		if ( this.ARMType == 'common' && data.LpuUnitType_SysNick.inlist(['polka','ccenter','traumcenter']) )
			return true;
		if ( this.ARMType == 'par' && data.LpuUnitType_SysNick == 'parka' )
			return true;
		if ( this.ARMType == 'stac' && !data.PostMed_Code.inlist(['126','116']) && data.LpuUnitType_SysNick.inlist(['stac','hstac','pstac','dstac']) )
			return true;
		if ( this.ARMType == 'stacnurse' && data.PostMed_Code.inlist(['126','116']) && data.LpuUnitType_SysNick.inlist(['stac','hstac','pstac','dstac']))
			return true;
		if ( this.ARMType.inlist(['prescr','mse','vk']) && data.LpuUnitType_SysNick.inlist(['polka','ccenter','traumcenter','stac','hstac','pstac','dstac']) )
			return true;
		return false;
		*/
		return true;//(this.ARMType == data.ARMType);
	},
	/**
	 * Функция вывода меню выбора на заголовке формы
	 */
	_showMenu: function(id)
	{
		if (this.linkMenu) {
			if (sw.isExt6Menu) {
				this.linkMenu.showBy(id);
			} else {
				this.linkMenu.show(Ext.fly(id));
			}
		}
	},
	/**
	 * Функция создания меню, наименования и ссылки
	 * 1. создает linkTitle для изпользования в заголовке окна АРМа
	 * 2. создает сам список меню
	 * 3. создает this.current, который содержит информацию о выбранном (установленном) АРМе
	 * Входящие параметры:
	 * o {object} объект окна АРМа, из которого функция вызвана
	 * option {object} данные пришедшие в форму извне (в частном случае userMedStaffFact)
	 * @access public
	 * @return {void}
	 *
	 */
	createMenu: function(o, option) {
		log({'createMenu':option});
		//if (!this.linkMenu)
		this.linkMenu = sw.Promed.MedStaffFactByUser.createListWorkPlaces(option);
		if(getGlobalOptions().ArmMenuTitle==2){
			o.linkTitle = ' <a id="header_link_'+o.id+'" href="#" onClick="sw.Promed.MedStaffFactByUser._showMenu(&quot;'+'header_link_'+o.id+'&quot;);">';
			if((this.current && this.current.title))
				o.linkTitle += this.current.title+'</a>';
			else{
				o.linkTitle += langs('Рабочее место врача');
				o.linkTitle += '</a>';
			}

		}
		else{
			if((this.current && this.current.title))
				o.linkTitle = this.current.title;
			else
				o.linkTitle = langs('Рабочее место врача');
		}
	},
	/**
	 * Входящие параметры:
	 * o {object} объект окна АРМа, из которого функция вызвана
	 * option {object} данные пришедшие в форму извне (в частном случае userMedStaffFact)
	 * @access public
	 * @return {void}
	 *
	 */
	setMenuTitle: function(o, option) {
		log({'setMenuTitle':option});
		if (!option) {
			// ошибка, не указаны входные параметры: без них заголовок правильно не отобразится

		} else {
			option.MedPersonal_FIO = (option && option.MedPersonal_FIO)?option.MedPersonal_FIO:getGlobalOptions().CurMedPersonal_FIO;
			option.MedPersonal_id = (option && option.MedPersonal_id)?option.MedPersonal_id:getGlobalOptions().CurMedPersonal_id;
			option.LpuSection_id = (option && option.LpuSection_id)?option.LpuSection_id:getGlobalOptions().CurLpuSection_id;
		}
		this.createMenu(o, option);
		var textMedPersonal = ((option.MedPersonal_FIO)?' (' + option.MedPersonal_FIO + ')':langs(' ( нет информации о враче! )'));
		if(isUserGroup('Communic'))
			textMedPersonal = '';
		if (option.ElectronicService_Name && option.UslugaComplex_Name) {
			textMedPersonal += ' ' + option.UslugaComplex_Name + ' / ' + option.ElectronicService_Name;
		}

		var changeWorkplaceMenu = Ext.getCmp('change_workplace_menu');
		if (changeWorkplaceMenu) {
			changeWorkplaceMenu.setText(o.linkTitle); // заголовок АРМ в кнопке переключения АРМ
			// заголовок АРМ убираем
			o.setTitle('Журнал');
			if (o.header) {
				o.header.hide();
			}
			o.header = false;
		} else {
			var changeWorkplaceMenu = Ext6.getCmp('change_workplace_menu');
			if (changeWorkplaceMenu) {
				changeWorkplaceMenu.setText(o.linkTitle); // заголовок АРМ в кнопке переключения АРМ
				// заголовок АРМ убираем
				o.setTitle('Журнал');
				if (o.header) {
					o.header.hide();
				}
				o.header = false;
			} else {
				o.setTitle(o.linkTitle + textMedPersonal);
			}
		}
		window.MedPersonal_FIO = option.MedPersonal_FIO;
	},
	/**
	 * Функция фильтрации сторе по правилу: если должность и место работы у нескольких записей совпадают, показывать только те ИЗ СОВПАДАЮЩИХ, у которых есть расписание
	 *
	 * option.allArms - boolean, признак фильтрации в пределах арма или во всех армах
	 * @access public
	 * @param {object} data
	 * @return {void}
	 */
	setFilter: function(option) {
		this.store.clearFilter();
		this.store.filterBy(function(record) {
			//return this._check(record.data);
			return true;
		}.createDelegate(this));
		if ( this.store.getCount() > 1 && getRegionNick() != 'kareliya'  && getRegionNick() != 'astra' && getRegionNick() != 'ufa') {
			this.store.each(function(record){
				//если должность и место работы у нескольких записей совпадают, показывать только те ИЗ СОВПАДАЮЩИХ, у которых есть расписание
				var rl = this.store.queryBy(function(rec,id){
					if( record.id != id && record.get('LpuSection_id') == rec.get('LpuSection_id') && record.get('PostMed_id') == rec.get('PostMed_id') && record.get('ARMType') == rec.get('ARMType') && Ext.isEmpty(record.get('MedStaffFactLink_id'))&& Ext.isEmpty(rec.get('MedStaffFactLink_id'))) {
						var key = 0;
						if(record.get('Timetable_isExists') == 'false')
							key = record.get('MedStaffFact_id');
						if(rec.get('Timetable_isExists') == 'false')
							key = rec.get('MedStaffFact_id');
						if(key > 0) {
							this.store.filterBy(function(r) {
								return (
									(
										key != r.get('MedStaffFact_id')
										|| r.get('LpuSection_id') != record.get('LpuSection_id')
										|| r.get('PostMed_id') != record.get('PostMed_id')
										|| r.get('ARMType') != record.get('ARMType')
									)
									&& (this._check(r.data) || option.allArms)
								);
							}.createDelegate(this));
						}
						return true;
					}
					return false;
				},this);
			},this);
		}
	},

	/**
	 * Функция выбора рабочего места для определенного АРМа
	 * option.selectFirst - boolean, признак, чтобы автоматом выбрать первое место работы из нескольких
	 * option.ARMType - тип АРМа
	 * option.onSelect - функция, которая выполняется при выборе рабочего места
	 * @access public
	 * @param {Object} option
	 * @return {void}
	 */
	selectARM: function(option) {
		log({'selectARM':option});
		/*if ( getGlobalOptions().medstafffact.length == 0 ) {
			sw.swMsg.alert(langs('Внимание'),langs('К сожалению у врача нет ни одного места работы.'));
			return false;
		}*/
		option._ini_callback = function(){
			// если должность и место работы у нескольких записей совпадают, показывать только те ИЗ СОВПАДАЮЩИХ, у которых есть расписание
			this.setFilter({allArms:false});
			//console.log(option.ARMType+'@@@@');

			//Если есть группа специалист МЗ по мед. оборудованию - то возвращаем пустое рабочее место
			if (getGlobalOptions().groups && getGlobalOptions().groups.toString().indexOf('OuzSpecMPC') != -1 ) return true;

			// Если АРМ по умолчанию определен и есть доступ к этому АРМу у врача
			if (getDefaultARM()) {
				var defaultArm = this._checkDefaultArm();
				if (defaultArm) {
					//if( !Ext.isEmpty(getDefaultARM().ARMForm) &&  getDefaultARM().ARMForm == 'swCmpCallCardCloseStreamWindow' && getRegionNick() == 'ufa')
					//	defaultArm.ARMForm = 'swCmpCallCardCloseStreamWindow';
					this.openWorkPlace({data: defaultArm});
					return true;
				}
			}

			// Если АРМ по умолчанию не определен или к нему нет доступа
			if ( this.store.getCount()==0 ) {
				if (option.ARMType=='smpvr' || option.ARMType=='smpreg') {
					sw.swMsg.alert(langs('Внимание'),langs('Нет доступа к АРМу: <br/>у пользователя есть привязка к врачу, необходимо убрать'));
				} else {
					//sw.swMsg.alert('Внимание','К сожалению, у врача нет мест работы выбранного типа.');
					sw.swMsg.alert(langs('Внимание'),langs('Нет доступа к АРМу: <br/>Пользователь не имеет необходимых мест работы или не связан с врачом.'));
				}
				return false;
			} else if ( this.store.getCount()==1 || option.selectFirst ) { // TODO: Тут надо проверить, что сделать
				var r = this.store.getAt(0).data;
				this.openWorkPlace({data: r});

				return true;
			} else {
				getWnd('swSelectWorkPlaceWindow'+(sw.isExt6Menu ? 'Ext6':'')).show();
			}
		}.createDelegate(this);
		this._ini(option);
	},
	/**
	 * Функция проверки АРМа по умолчанию, проверяет наличие установленного АРМа по умолчанию в списке АРМов и возвращает его данные.
	 * @access public
	 * @param {Object} option
	 * @return {void}
	 */
	_checkDefaultArm: function () {
		log('_checkDefaultArm');
		var r = false;
		// log('getDefaultARM',getDefaultARM());
		// ищем арм по умолчанию среди списка доступных армов и возвращаем его данные, если нашли
		if (getGlobalOptions().org_id == getDefaultARM().Org_id || (!Ext.isEmpty(getGlobalOptions().lpu_id) && !Ext.isEmpty(getDefaultARM().Lpu_id) && getGlobalOptions().lpu_id == getDefaultARM().Lpu_id) || !getGlobalOptions().org_id) { // Открываем АРМ по умолчанию только если АРМ указан для выбранной организации или организация не выбрана
			if (sw.openLLOFromEMIASData) {
				this.store.each(function(record) {
					if (
						record.get('ARMType') == 'polkallo'
					) {
						r = record.data;
					}
				});
			} else {
				this.store.each(function(record) {
					if (
						record.get('MedService_id') == getDefaultARM().MedService_id &&
						(record.get('MedPersonal_id') == 0 || record.get('MedPersonal_id') == getDefaultARM().MedPersonal_id) &&
						record.get('MedStaffFact_id') == getDefaultARM().MedStaffFact_id &&
						(record.get('Org_id') == getDefaultARM().Org_id || ((!Ext.isEmpty(record.get('Lpu_id')) && !Ext.isEmpty(getDefaultARM().Lpu_id)) && record.get('Lpu_id') == getDefaultARM().Lpu_id)) &&
						record.get('LpuSection_id') == getDefaultARM().LpuSection_id &&
						record.get('ARMType') == getDefaultARM().ARMType
					) {
						r = record.data;
					}
				});
			}
		}
		return r;
	},
	/**
	 * Функция установки рабочего места (АРМа)
	 * option.MedStaffFact_id - АРМ рабочего места врача
	 * option.MedPersonal_id - совместно с LpuSection_id, альтернатива MedStaffFact_id
	 * option.LpuSection_id - совместно с MedPersonal_id, альтернатива MedStaffFact_id
	 * option.MedService_id - АРМ службы
	 * option.onSelect - функция, которая выполняется при выборе рабочего места
	 * @access public
	 * @param {Object} option
	 * @return {void}
	 */
	setARM: function(option) {
		log({'setARM':option});

		if(typeof(main_toolbar) != "undefined" && main_toolbar !== null){
			var t = main_toolbar;
			Ext6.getCmp('_main_toolbar_menu_button').setVisible(option.ARMType != 'lpuuser6');
		}
		if(typeof(main_menu_panel) != "undefined" && main_menu_panel !== null){
			var t = main_menu_panel.items.items;
			for(var i = 1; i < t.length; i++) {
				var hidemenu = ["_menu_lpu", "_menu_dlo", "_menu_polka", "_menu_stac", "_menu_parka", "_stomatka", "_menu_farmacy", "_menu_documents", "_menu_service", "_menu_reports", "_menu_windows", "_menu_help"];
				if(hidemenu.includes(t[i].id)) {
					t[i].setVisible(option.ARMType != 'lpuuser');
				}
			}
		}
		
		option._ini_callback = function(){
			var cbWorkShift,
				cbWsVisible,
				v;

			$.ajax({
				url: C_USER_SETCURARM,
				type: 'POST',
				data: {MedStaffFact_id: option.MedStaffFact_id, LpuSection_id: option.LpuSection_id, MedService_id: option.MedService_id, Lpu_id: option.Lpu_id, ARMType: option.ARMType},
				dataType: 'json',
				async: false,
				cache: false
			}).done(function(data){
				if ( !data[0] ) {
					return;
				}

				data = data[0];
				data.PostMed_Code = (data.PostMed_Code)?data.PostMed_Code.toString():null;
				this.ARMType = data.ARMType;
				if(this._check(data)){
					/*
					* @To-Do Надо будет убрать запись в GlobalOptions т.к. всегда параметры текущего МР можно получить из sw.Promed.MedStaffFactByUser.last
					*/
					getGlobalOptions().CurMedStaffFact_id = data.MedStaffFact_id;
					getGlobalOptions().CurMedPersonal_id = data.MedPersonal_id;
					getGlobalOptions().CurLpuSection_id = data.LpuSection_id;
					getGlobalOptions().CurLpuSectionProfile_id = data.LpuSectionProfile_id;
					getGlobalOptions().CurLpuUnitType_id = data.LpuUnitType_id;
					getGlobalOptions().CurPostMed_id= data.PostMed_id;
					getGlobalOptions().CurLpuSection_Name = data.LpuSection_Name;
					getGlobalOptions().CurMedPersonal_FIO = data.MedPersonal_FIO;
					getGlobalOptions().CurMedService_id = data.MedService_id;
					getGlobalOptions().CurMedService_Nick = data.MedService_Nick;
					getGlobalOptions().CurMedService_Name = data.MedService_Name;
					getGlobalOptions().CurMedServiceType_SysNick = data.MedServiceType_SysNick;
					getGlobalOptions().CurMedService_IsExternal = data.MedService_IsExternal;
					getGlobalOptions().CurLpuSectionAttributes = data.LpuSectionAttributes;

					// Если при установке места работы произошла смена МО, то учитываем все изменения на клиенте
					if (data.lpu && data.lpu.Lpu_id && (data.lpu.Lpu_id!=getGlobalOptions().lpu_id) && (!isSuperAdmin() && data.ARMType!='superadmin')) {
						setCurrentLpu(data.lpu);
					}
					delete(data.success);

					// #175117
					// Настраиваем флаг "Я на смене" в главном меню:
					// 1. Отображаем флаг, только если в текущем отделении предусмотрен учет смен.
					// 2. Если флаг отображен:
					// 2.1. Устанавливаем или снимаем его в зависимости от того, находится ли
					//      текущий пользователь на смене в данный момент.
					// 2.2. Планируем, чтобы через 1 час запустилась функция, скрывающая флаг, если
					//      смена пользователя уже завершилась.
					if (main_menu_panel && (v = main_menu_panel.items) && (v = v.map) &&
						(cbWorkShift = v.cbWorkShift))
					{
						cbWorkShift.setVisible(cbWsVisible = isWorkShift());

						if (cbWsVisible)
						{
							doIfOnWorkShift(getGlobalOptions().pmuser_id,
											_setCbWorkShift,
											this);

							setTimeout(refreshCbWorkShift, 60 * 60 * 1000);
						}
					}

					this.onSelect(data);
					this.last = data;
					// При смене АРМ-а на Ext6 необходимо предупреждать пользователей,
					// что всё очень плохо и им придется потерпеть
					// 182436 отключили на время
					//if(sw.isExt6Menu)
						//checkShownMsgArms(data.ARMType,data.ARMName);
				} else {
					Ext.Msg.alert(langs('Ошибка'), langs('У врача нет мест работы выбранного типа.'));
				}
			}.createDelegate(this));

//			Ext.Ajax.request({
//				url: C_USER_SETCURARM,
//				params: {MedStaffFact_id: option.MedStaffFact_id, LpuSection_id: option.LpuSection_id, MedService_id: option.MedService_id, Lpu_id: option.Lpu_id, ARMType: option.ARMType},
//				callback: function(o, s, r){
//					if (s && r.responseText != '')
//					{
//						var data = Ext.util.JSON.decode(r.responseText);
//						if ( data[0])
//						{
//							data = data[0];
//							data.PostMed_Code = (data.PostMed_Code)?data.PostMed_Code.toString():null;
//							this.ARMType = data.ARMType;
//							if(this._check(data))
//							{
//								/*
//								* @To-Do Надо будет убрать запись в GlobalOptions т.к. всегда параметры текущего МР можно получить из sw.Promed.MedStaffFactByUser.last
//								*/
//								getGlobalOptions().CurMedStaffFact_id = data.MedStaffFact_id;
//								getGlobalOptions().CurMedPersonal_id = data.MedPersonal_id;
//								getGlobalOptions().CurLpuSection_id = data.LpuSection_id;
//								getGlobalOptions().CurLpuSectionProfile_id = data.LpuSectionProfile_id;
//								getGlobalOptions().CurLpuUnitType_id = data.LpuUnitType_id;
//								getGlobalOptions().CurPostMed_id= data.PostMed_id;
//								getGlobalOptions().CurLpuSection_Name = data.LpuSection_Name;
//								getGlobalOptions().CurMedPersonal_FIO = data.MedPersonal_FIO;
//								getGlobalOptions().CurMedService_id = data.MedService_id;
//								getGlobalOptions().CurMedService_Nick = data.MedService_Nick;
//								getGlobalOptions().CurMedService_Name = data.MedService_Name;
//								getGlobalOptions().CurMedServiceType_SysNick = data.MedServiceType_SysNick;
//
//								// Если при установке места работы произошла смена МО, то учитываем все изменения на клиенте
//								if (data.lpu && data.lpu.Lpu_id && (data.lpu.Lpu_id!=getGlobalOptions().lpu_id) && (!isSuperAdmin() && data.ARMType!='superadmin')) {
//									setCurrentLpu(data.lpu);
//								}
//								delete(data.success);
//								this.onSelect(data);
//								this.last = data;
//								//warn('last', this.last);
//							}
//							else
//							{
//								Ext.Msg.alert('Ошибка', 'У врача нет мест работы выбранного типа.');
//							}
//						}
//					}
//				}.createDelegate(this)
//			});
		}.createDelegate(this);
		this._ini(option);
	},
	/**
	 * Функция установки рабочего места для определенного АРМа (не используется)
	 * option.MedStaffFact_id
	 * option.onSelect - функция, которая выполняется при выборе рабочего места
	 * @access public
	 * @param {Object} option
	 * @return {void}
	 */
	setMedStaffFact: function(option) {
		log({'setMedStaffFact':option});
		option._ini_callback = function(){
			Ext.Ajax.request({
				url: C_USER_SETCURMSF,
				params: {MedStaffFact_id: option.MedStaffFact_id},
				callback: function(o, s, r){
					if (s && r.responseText != '')
					{
						var data = Ext.util.JSON.decode(r.responseText);
						if ( data.success )
						{
							data.PostMed_Code = data.PostMed_Code.toString();
							this.ARMType = option.ARMType;
							data.ARMForm = option.ARMForm;
							data.ARMType_id = option.ARMType_id;
							if(this._check(data))
							{
								/*
								* @To-Do Надо будет убрать запись в GlobalOptions т.к. всегда параметры текущего МР можно получить из sw.Promed.MedStaffFactByUser.last
								*/
								getGlobalOptions().CurMedStaffFact_id = data.MedStaffFact_id;
								getGlobalOptions().CurMedPersonal_id = data.MedPersonal_id;
								getGlobalOptions().CurLpuSection_id = data.LpuSection_id;
								getGlobalOptions().CurLpuSectionProfile_id = data.LpuSectionProfile_id;
								getGlobalOptions().CurLpuUnitType_id = data.LpuUnitType_id;
								getGlobalOptions().CurPostMed_id= data.PostMed_id;
								getGlobalOptions().CurLpuSection_Name = data.LpuSection_Name;
								getGlobalOptions().CurMedPersonal_FIO = data.MedPersonal_FIO;
								/*
								getGlobalOptions().CurMedService_id = data.MedService_id;
								getGlobalOptions().CurMedService_Nick = data.MedService_Nick;
								getGlobalOptions().MedService_Name = data.MedService_Name;
								getGlobalOptions().CurMedServiceType_SysNick = data.MedServiceType_SysNick;
								*/
								delete(data.success);
								this.onSelect(data);
								this.last = data;
							}
							else
							{
								Ext.Msg.alert(langs('Ошибка'), langs('У врача нет мест работы выбранного типа.'));
							}
						}
					}
				}.createDelegate(this)
			});
		}.createDelegate(this);
		this._ini(option);
	},

	/**
	 * Открытие АРМа по умолчанию
	 * @access public
	 * @param {Object} option
	 * @return {void}
	 */
	openDefaultWorkPlace: function(options) {
		log({'openDefaultWorkPlace':getDefaultARM()});

		var me = this;
		if (!options) {
			options = {};
		}

		if (!options.ignoreWarning && isUserGroup('LpuUser') && typeof LpuUserWarning === 'string') {
			options.ignoreWarning = true;
			sw.swMsg.show({
				title: '<font color="red">Внимание</font>',
				msg: LpuUserWarning,
				width: 450,
				buttons: Ext.MessageBox.OK,
				fn: function(buttonId) {
					me.openDefaultWorkPlace(options);
				}
			});
			return;
		}

		if (window.opener && typeof window.opener.postMessage == 'function') {
			window.opener.postMessage({status: 'MessageListenerReady'}, "*");
		}

		if (getGlobalOptions().se_techinfo) {
			this._ini({
				_ini_callback: function() {
					openWindowsByTechInfo();
				}
			});

			return true;
		}

		if (!getGlobalOptions().setARM_id || (getGlobalOptions().setARM_id == 'default')) {

			var wnd = getGlobalOptions().getwnd;

			if (!Ext.isEmpty(wnd)) {
				if (!Ext.isEmpty(getGlobalOptions().cccid) && getGlobalOptions().cccid > 0) {
					var params = new Object(),
						formParams = new Object();
					params.action = 'add';
					if (!Ext.isEmpty(getGlobalOptions().act) && getGlobalOptions().act != '') {
						params.action = getGlobalOptions().act;
					}
					params.callback = function() {
						Ext.Msg.alert(langs('Сообщение'), langs('Выполнено'));
					}
					formParams.CmpCallCard_id = getGlobalOptions().cccid;
					formParams.ARMType = 'smpadmin';
					params.formParams = formParams;
					getWnd(wnd).show(params);
				} else {
					switch(wnd){
						case 'swReportEndUserWindow':{
							getWnd('reports').load({
								callback: function(success)
								{
									sw.codeInfo.loadEngineReports = success;
									var curARM = getGlobalOptions().curARMType;
									isSmpArm  = ((curARM == 'smpdispatchstation') || (curARM == 'smpheaddoctor')) ? true : false;
									getWnd('swReportEndUserWindow').show({
										ARMType: isSmpArm ? '' : 'nmp'
									});
								}
							});
							break;
						}
						case 'swCmpCallCardNewCloseCardWindow': {
							getWnd(wnd).show({
								action: 'stream',
								formParams: {
									ARMType: 'smpadmin'
								}
							});
							break;
						}
					}


				}
			} else if (getDefaultARM() && getDefaultARM().ARMType) {
				this.selectARM({
					ARMType: getDefaultARM().ARMType
				});
			} else {
				this._ini({
					_ini_callback: function() {
						// do nothing
						if (sw.Promed.MedStaffFactByUser.store.getCount()>0) {
							getWnd('swSelectWorkPlaceWindow'+(sw.isExt6Menu ? 'Ext6':'')).show();
						}
					}
				});
			}
		} else {
			var _this = this;
			this._ini({
				_ini_callback: function() {
					var record;
					record = this.store.getById(getGlobalOptions().setARM_id);
					var defaultArm = _this._checkDefaultArm();
					if(record || defaultArm) {
						this.openWorkPlace(
							(record) ? record : (sw.isExt6Menu ? { data : defaultArm } : getDefaultARM() ) ); //получается что если идет на getDefaultARM, то без обертки {} никогда не выполнится. На всякий случай поправил только для ext6.
					} else {//никакой арм не открылся - нужно построить список армов
						sw.Promed.MedStaffFactByUser.linkMenu = sw.Promed.MedStaffFactByUser.createListWorkPlaces({MedService_id:null,ARMType:null,MedStaffFact_id:null,LpuSection_id:null,ARMType_id:null});
						getWnd('swSelectWorkPlaceWindow'+(sw.isExt6Menu ? 'Ext6':'')).show();
					}
				}.bind(this)
			});
		}
	},
	/**
	 * Открытие формы соответствующего выбранного АРМа
	 * Входные параметры: выбранная строка АРМа/места работы
	 * @access public
	 * @param {Object} option
	 * @return {void}
	 */
	openWorkPlace: function(option) {
		if (sw.isExt6Menu) {
			// сначала надо закрыть все сущетсвующие окна
			Ext.WindowMgr.each(function(wnd) {
				if (wnd.isVisible()) {
					wnd.hide();
				}
			});
		}
		// TODO: Надо думать над тем, что может быть ФИО брать уже из этого же списка, а не при setMedStaffFact (но это лирика пока... )
		log({'openWorkPlace':option});
		var globalOptions = getGlobalOptions();
		if (option.data) {

			//для вологды открываем новый СМП
			if (getRegionNick().inlist(['vologda']) && getGlobalOptions().newSmpServer) {
				var newSmpArms = ['smpdispatchcall', 'smpdispatchstation', 'smpheaddoctor'];

				if (option.data.ARMType.inlist(newSmpArms)) {
					window.location = getGlobalOptions().newSmpServer + '?c=Main&m=login&login=' + getGlobalOptions().login + '&authFromPromed=2&currentArm=' + option.data.ARMType;
					return;
				}
			}

			if (option.data.client && option.data.client != globalOptions.client) {
				window.onbeforeunload = null;
				window.location='/?c=promed&m=loadArm&ARM_id='+option.data.id;
				return;
			}

			if (option.data.MedStaffFact_id && option.data.MedStaffFact_id>0) { // медстаффакт
				option.data.onSelect = function(data) {
					if (option.data.ARMForm)
						getWnd(option.data.ARMForm).show({ARMType: option.data.ARMType, userMedStaffFact: data});
					else
						getWnd('swSelectWorkPlaceWindow'+(sw.isExt6Menu ? 'Ext6':'')).show();
				};
				this.setARM(option.data);
			} else { // службы
				//this.onSelect = option.onSelect || function(data) { getWnd('swMPWorkPlaceWindow').show({ARMType: data.ARMType, userMedStaffFact: data }); };
				option.data.onSelect = function(data) {
					if (option.data.ARMForm)
						getWnd(option.data.ARMForm).show(option.data);
					else
						getWnd('swSelectWorkPlaceWindow'+(sw.isExt6Menu ? 'Ext6':'')).show();
				};
				//getWnd(option.data.ARMForm).show(option.data);
				this.setARM(option.data);
			}
		}
		// Здесь мы должны установить место работы и открыть форму
	},
	/**
	 * Создание компонента списка всех разрешенных мест работы
	 * Входные параметры: место работы или служба
	 * option.MedStaffFact_id
	 * option.ARMType - тип АРМа
	 * option.onSelect - функция, которая выполняется при выборе рабочего места
	 * @access public
	 * @param {Object} option
	 * @return {Ext.menu.Menu}
	 */
	createListWorkPlaces: function(option) {
		if ( typeof option != 'object' ) {
			option = new Object();
		}
		// Функция должна получать текущее место работы и службу
		// и визуально строить компонент выбора, в котором текущее место работы - выбрано
		var store = this.store;
		// Выбранные АРМ, место работы, службу определяем по полученным данным
		var o = this;
		var menuArr = new Array();
		if (sw.isExt6Menu) {
			var menu = Ext6.create('Ext6.menu.Menu', {
				cls: 'work-place-menu',
				layout: {
					type: 'vbox',
					align: 'stretchmax',
					overflowHandler: null
				}
			});
		} else {
			var menu = new Ext.menu.Menu();
		}
		this.setFilter({allArms:true});
		// Далее получаем список отделений в этом подразделении и из списка получаем меню
		var
			linkedMSFCount = 0,
			mainMSFCount = 0;

		store.each(function(record) {
			if (record.get('ARMType') && Ext.isEmpty(record.get('MedStaffFactLink_id')) ) { // Только АРМы, на всякий случай (и только основные места работы)
				mainMSFCount++;

				if (record.get('MedService_id')>0) { // Если служба, то берем название службы
					text = record.get('ARMName')+' / '+record.get('Org_Nick')+' / '+record.get('MedService_Name')+((record.get('PostMed_Name'))?' / '+record.get('PostMed_Name'):'');
					if ( (option.MedService_id == record.get('MedService_id')) && (option.ARMType == record.get('ARMType')) ) {
						o.current = record.data;
						o.current.title = text;
						text = '<b>'+text+'</b>';
					}
				} else {
					if (record.get('MedStaffFact_id')>0) { // иначе берем отделение
						text = record.get('ARMName')+' / '+record.get('Org_Nick')+((record.get('LpuSection_Nick'))?' / '+record.get('LpuSection_Nick'):'')+((record.get('PostMed_Name'))?' / '+record.get('PostMed_Name'):'');

						if (option.MedStaffFact_id == record.get('MedStaffFact_id') && option.LpuSection_id == record.get('LpuSection_id') && option.ARMType_id == record.get('ARMType_id') && option.client == record.get('client')) {
							o.current = record.data;
							o.current.title = text;
							text = '<b>'+text+'</b>';
						}
					} else { // а если не врач, то все остальное
						text = record.get('ARMName')+((Ext.isEmpty(record.get('Org_Nick')) || record.get('Org_Nick') == 'false') ? ' ' : (' / '+ record.get('Org_Nick'))) +((record.get('PostMed_Name'))?' / '+record.get('PostMed_Name'):'');
						// если не указан ни врач, ни служба, то АРМ такого типа может быть только один для одного пользователя
						if (option.ARMType == record.get('ARMType')) {
							o.current = record.data;
							o.current.title = text;
							text = '<b>'+text+'</b>';
						}
					}
				}
				menuArr.push({
					text: text,
					data: record.data,
					iconCls: (sw.isExt6Menu ? '' : 'workplace-mp16'),
					handler: function() {
						sw.Promed.MedStaffFactByUser.openWorkPlace(record);
					}
				});
			}
		});

		// Только АРМы и только связанные места работы
		store.each(function(record) {
			if ( record.get('ARMType') && !Ext.isEmpty(record.get('MedStaffFactLink_id')) ) {
				linkedMSFCount++;
				if (record.get('MedService_id')>0) { // Если служба, то берем название службы
					text = record.get('ARMName')+' / '+record.get('Org_Nick')+' / '+record.get('MedService_Name')+((record.get('PostMed_Name'))?' / '+record.get('PostMed_Name'):'');
					if ( (option.MedService_id == record.get('MedService_id')) && (option.ARMType == record.get('ARMType')) ) {
						o.current = record.data;
						o.current.title = text;
					}
				} else {
					if (record.get('MedStaffFact_id')>0) { // иначе берем отделение
						text = record.get('ARMName')+' / '+record.get('Org_Nick')+((record.get('LpuSection_Nick'))?' / '+record.get('LpuSection_Nick'):'')
							+ ' / ' + record.get('MedPersonal_FIO') + ' / ' + record.get('MedStaffFactLink_begDT') + ' - ' + record.get('MedStaffFactLink_endDT')+((record.get('PostMed_Name'))?' / '+record.get('PostMed_Name'):'');

						if (option.MedStaffFact_id == record.get('MedStaffFact_id')) {
							o.current = record.data;
							o.current.title = text;
						}
					} else { // а если не врач, то все остальное
						text = record.get('ARMName')+' / '+record.get('Org_Nick')+((record.get('PostMed_Name'))?' / '+record.get('PostMed_Name'):'');
						// если не указан ни врач, ни служба, то АРМ такого типа может быть только один для одного пользователя
						if (option.ARMType == record.get('ARMType')) {
							o.current = record.data;
							o.current.title = text;
						}
					}
				}

				if ( mainMSFCount > 0 && linkedMSFCount == 1 ) {
					menuArr.push({text:'-'});
				}

				menuArr.push({
					text: text,
					data: record.data,
					iconCls: (sw.isExt6Menu ? '' : 'workplace-mp16'),
					handler: function() {
						sw.Promed.MedStaffFactByUser.openWorkPlace(record);
					}
				});
			}
		});

		menuArr.push({text:'-'});
		menuArr.push({text: langs('Выбор места работы по умолчанию...'), data: {}, iconCls: 'settings16', handler: function() {getWnd('swSelectWorkPlaceWindow'+(sw.isExt6Menu ? 'Ext6':'')).show();}});

		for (key in menuArr) {
			if (key!='remove' && key!='in_array') {
				if (menuArr[key].text == '-') {
					menu.add('-');
				} else if (typeof menuArr[key] == 'object') {
					menu.add(menuArr[key]);
				}
			}
		}


		return menu;
	},
	/**
	 * Пытаемся определить MedStaffFact_id врача в отделении, где создана служба
	 * @access public
	 * @param {Object} filters
	 * @param {int} MedPersonal_id
	 * @param {Function} callback
	 * @param {Object} scope
	 * @return {void}
	 */
	loadMedStaffFactId: function(filters, MedPersonal_id, callback, scope) {
		setMedStaffFactGlobalStoreFilter(filters);
		var records = [];
		swMedStaffFactGlobalStore.each(function(record) {
			if ( record.get('MedPersonal_id') == MedPersonal_id ) {
				records.push(record);
			}
		});
		if (!scope) {
			scope = window;
		}
		if (typeof callback != 'function') {
			callback = function(id){
				log(id);
			};
		}
		if (records.length > 0) {
			callback.call(scope, records[0].get('MedStaffFact_id'));
		} else {
			callback.call(scope, null);
		}
	}
};


/**
 * sw.Promed.GlobalVariables хранилище для работы с глобальными переменными
 */
sw.Promed.GlobalVariables = {

	statusInactivity : true,

	set : function(name, value) {
		this[name] = value
	},

	get : function(name) {

		if(this[name] == undefined) {
			sw.swMsg.alert(langs('Внимание'),"переменная " + name + " не определена");
		}

		return this[name];
	}
}
/** Набор методов назначений
 *
 */
sw.Promed.EvnPrescr = {
	//parentEvnClass_SysNick: null,
	prescriptionTypeStore: null,
	_createUnExecEvnPrescrParams: function(ep_data, ownerWindow) {
		if ( !ep_data) {
			return false;
		}
		var conf = {
			ownerWindow: ownerWindow
			,EvnPrescrDay_id: ep_data.EvnPrescr_id
			,PrescriptionType_id: ep_data.PrescriptionType_id
			,EvnDirection_id: ep_data.EvnDirection_id
			,EvnPrescr_IsExec: ep_data.EvnPrescr_IsExec
			,EvnPrescr_IsHasEvn: ep_data.EvnPrescr_IsHasEvn
			//,PrescriptionStatusType_id: ep_data.PrescriptionStatusType_id
		};
		return conf;
	},
	_execEvnPrescr: function(conf, ep_data, dObject, coords) {
		conf.btnId = dObject +'_'+ ep_data.EvnPrescr_id +'_exec';
		conf.coords = coords;
		conf.Diag_id = ep_data.Diag_id;
		conf.Person_Birthday = ep_data.Person_Birthday;
		conf.Person_Surname = ep_data.Person_Surname;
		conf.Person_Firname = ep_data.Person_Firname;
		conf.Person_Secname = ep_data.Person_Secname;
		sw.Promed.EvnPrescr.exec(conf);
	},
	execEvnPrescr: function(parentClass, el_data, coords, data, ownerWindow) {
		var conf = this._createExecEvnPrescrParams(data, ownerWindow);
		conf.parentEvnClass_SysNick = parentClass;
		conf.onExecSuccess = function(cnfg){
			data.callback();
			if (cnfg && cnfg.mode && 'withUseDrug' == cnfg.mode) {
				//рефреш раздела Использование медикаментов при списании по назначению
				if (data.refreshEvnDrugSectionCallback) {
					data.refreshEvnDrugSectionCallback();
				}
			}
		};
		conf.onExecCancel = function(){
			//
		};
		this._execEvnPrescr(conf, data, el_data.object, coords);
		return true;
	},
	_createExecEvnPrescrParams: function(ep_data, ownerWindow) {
		if ( !ep_data) {
			return false;
		}
		var conf = {
			ownerWindow: ownerWindow
			,allowChangeTime: (ep_data.PrescriptionType_id == 10)
			,EvnPrescr_setDate: ep_data.EvnPrescr_setDate
			,Person_id: ep_data.Person_id
			,PersonEvn_id: ep_data.PersonEvn_id
			,Server_id: ep_data.Server_id
		};
		conf.EvnPrescr_id = ep_data.EvnPrescr_id;
		conf.PrescriptionType_id = ep_data.PrescriptionType_id;
		conf.EvnPrescr_IsExec = ep_data.EvnPrescr_IsExec;
		//conf.PrescriptionStatusType_id = ep_data.PrescriptionStatusType_id;
		//for 10
		conf.ObservTimeType_id = ep_data.ObservTimeType_id || null;
		//for 6,7,11,12
		conf.EvnPrescr_rid  = ep_data.EvnPrescr_rid;
		conf.EvnPrescr_pid = ep_data.EvnPrescr_pid;
		conf.UslugaId_List = ep_data.UslugaId_List || null;
		conf.TableUsluga_id = ep_data.TableUsluga_id || null;
		conf.PrescriptionType_Code = ep_data.PrescriptionType_Code;
		conf.EvnDirection_id = ep_data.EvnDirection_id || null;
		conf.PayType_id = ep_data.PayType_id;
		if (ownerWindow.EvnPrescr_id && ep_data.PrescriptionType_id == 13 && ep_data.EvnPrescr_id == ownerWindow.EvnPrescr_id) {
			conf.mode = 'execUsluga';
			conf.EvnDirection_id = null;
			conf.userMedStaffFact = ownerWindow.userMedStaffFact;
		}
		return conf;
	},
	unExecEvnPrescr: function(parentClass, el_data, data, ownerWindow) {
		var conf = this._createUnExecEvnPrescrParams(data, ownerWindow);
		conf.onSuccess = function() {
			data.callback();
		};
		sw.Promed.EvnPrescr.unExec(conf);
		return true;
	},
	_openEvnPrescrEditWindow: function(data, evnsysnick, ownerWindow, callbackEditWindow) {
		sw.Promed.EvnPrescr.openEditWindow({
			action: 'edit'
			,PrescriptionType_id: data.PrescriptionType_id
			,PrescriptionType_Code: data.PrescriptionType_Code
			,parentEvnClass_SysNick: evnsysnick
			,userMedStaffFact: ownerWindow.userMedStaffFact
			,data: {
				Diag_id: data.Diag_id || null,
				Evn_pid: data.EvnPrescr_pid
				,EvnPrescr_id: data.EvnPrescr_id
				,Person_id: data.Person_id
				,PersonEvn_id: data.PersonEvn_id
				,Server_id: data.Server_id
				,Person_Firname: data.Person_Firname
				,Person_Surname: data.Person_Surname
				,Person_Secname: data.Person_Secname
				,Person_Birthday: data.Person_Birthday
			}
			,callbackEditWindow: callbackEditWindow
		});
	},
	openPrintDoc: function(url)
	{
		window.open(url, '_blank');
	},
	getDirActionMenu: function(conf) {
		log('getDirActionMenu', conf);

		var data = conf.data,
			evdata = conf.evdata,
			dirdata = conf.dirdata,
			evnsysnick = conf.evnsysnick,
			ownerWindow = conf.ownerWindow,
			d = conf.d,
			EvnClass_SysNick = conf.EvnClass_SysNick,
			that = this;

		if (data == false || evdata == false) {
			return false;
		}

		if (ownerWindow.readOnly) {
			evdata.accessType = 'view';
		}

		var DirActions = {};
		var allowActions = (evdata.accessType && data.EvnDirection_id && data.EvnDirection_id > 0);

		var actions = [{
			name: 'action_printdir',
			text: langs('Печать направления'),
			hidden: (data.isEvnCourse || data.PrescriptionType_id == 6),
			disabled: ( !allowActions || !data.EvnDirection_id),
			handler: function () {
				if (getRegionNick() == 'kz') {
					printBirt({
						'Report_FileName': 'rec_EvnDirection_Usl.rptdesign',
						'Report_Params': '&paramEvnDirection=' + data.EvnDirection_id,
						'Report_Format': 'pdf'
					});
				} else {
					sw.Promed.Direction.print({
						EvnDirection_id: data.EvnDirection_id
					});
				}
				return true;
			}.createDelegate(this)
		}, 	
		// 192492	
		 {
			name: 'print_direction_200u',
			hidden: (getRegionNick() != 'vologda' || data.isEvnCourse || data.PrescriptionType_id != 11),
			text: 'Печать «Форма 200/у»',
			disabled: false,
			handler: function() {
				that.printForm('printEvnDirectionF200u.rptdesign', data.EvnDirection_id);
			}
		},
		{
			name: 'print_direction_201u',
			tag: 'AnalysisHematological',
			hidden: (getRegionNick() != 'vologda' || data.isEvnCourse || data.PrescriptionType_id != 11),
			text: 'Печать «Форма 201/у»',
			disabled: false,
			handler: function() {
				that.printForm('printEvnDirectionF201u.rptdesign', data.EvnDirection_id);
			}
		},
		{
			name: 'print_direction_202u',
			tag: 'AnalysisBlood2',
			hidden: (getRegionNick() != 'vologda' || data.isEvnCourse || data.PrescriptionType_id != 11),
			text: 'Печать «Форма 202/у»',
			disabled: false,
			handler: function() {
				that.printForm('printEvnDirectionF202u.rptdesign', data.EvnDirection_id);
			}
		},
		{
			name: 'print_direction_210u',
			tag: 'AnalysisUrine',
			hidden: (getRegionNick() != 'vologda' || data.isEvnCourse || data.PrescriptionType_id != 11),
			text: 'Печать «Форма 210/у»',
			disabled: false,
			handler: function() {
				that.printForm('printEvnDirectionF210u.rptdesign', data.EvnDirection_id);
			}
		},
		{
			name: 'print_direction_212u',
			tag: 'AnalysisUrine2',
			hidden: (getRegionNick() != 'vologda'  || data.isEvnCourse || data.PrescriptionType_id != 11),
			text: 'Печать «Форма 212/у»',
			disabled: false,
			handler: function() {
				that.printForm('printEvnDirectionF212u.rptdesign', data.EvnDirection_id);
			}
		},
		{
			name: 'print_direction_213u',
			tag: 'GlucosuricProfile',
			hidden: (getRegionNick() != 'vologda' || data.isEvnCourse || data.PrescriptionType_id != 11),
			text: 'Печать «Форма 213/у»',
			disabled: false,
			handler: function() {
				that.printForm('printEvnDirectionF213u.rptdesign', data.EvnDirection_id);
			}
		},
		{
			name: 'print_direction_215u',
			tag: 'AnalysisUrine3',
			hidden: (getRegionNick() != 'vologda' || data.isEvnCourse || data.PrescriptionType_id != 11),
			text: 'Печать «Форма 215/у»',
			disabled: false,
			handler: function() {
				that.printForm('printEvnDirectionF215u.rptdesign', data.EvnDirection_id);
			}
		},
		{
			name: 'print_direction_224u',
			tag: 'AnalysisBlood',
			hidden: (getRegionNick() != 'vologda' || data.isEvnCourse || data.PrescriptionType_id != 11),
			text: 'Печать «Форма 224/у»',
			disabled: false,
			handler: function() {
				that.printForm('printEvnDirectionF224u.rptdesign', data.EvnDirection_id);
			}
		},
		{
			name: 'print_direction_225u',
			tag: 'AnalysisBlood3',
			hidden: (getRegionNick() != 'vologda' || data.isEvnCourse || data.PrescriptionType_id != 11),
			text: 'Печать «Форма 225/у»',
			disabled: false,
			handler: function() {
				that.printForm('printEvnDirectionF225u.rptdesign', data.EvnDirection_id);
			}
		},
		{
			name: 'print_direction_452u06',
			tag: 'Chemical',
			hidden: (getRegionNick() != 'vologda' || data.isEvnCourse || data.PrescriptionType_id != 11),
			text: 'Печать «Форма 452/у-06»',
			disabled: false,
			handler: function() {
				that.printForm('printEvnDirectionF452u06.rptdesign', data.EvnDirection_id);
			}
		},
		// --- 192492
		{
			name: 'action_deletedir',
			text: langs('Отменить направление'),
			hidden: (data.isEvnCourse || data.PrescriptionType_id == 6),
			disabled: ( !allowActions || evdata.accessType == 'view' || !data.EvnDirection_id || data.EvnPrescr_IsExec == 2),
			handler: function () {
				if (data.EvnPrescr_id && data.EvnPrescr_id > 0) {
					sw.Promed.EvnPrescr.cancel({
						ownerWindow: ownerWindow,
						getParams: function () {
							return {
								parentEvnClass_SysNick: data.parent_object
								,PrescriptionType_id: data.PrescriptionType_id
								,EvnPrescr_id: data.EvnPrescr_id
							};
						},
						callback: function () {
							data.callback();
						}
					});
					return true;
				}

				if (!dirdata) return false;
				var TimetableGraf_ident = dirdata.timetable +'_'+ dirdata.timetable_id;
				key_parts = TimetableGraf_ident.split('_');

				return sw.Promed.Direction.cancel({
					cancelType: 'cancel',
					ownerWindow: ownerWindow,
					EvnDirection_id: dirdata.EvnDirection_id,
					DirType_Code: dirdata.DirType_Code,
					TimetableGraf_id: ('TimetableGraf' == key_parts[0]) ? key_parts[1] : null,// === ('TimetableGraf' == dirdata.timetable) ? dirdata.timetable_id : null,
					TimetableMedService_id: ('TimetableMedService' == key_parts[0]) ? key_parts[1] : null,// === ('TimetableMedService' == dirdata.timetable) ? dirdata.timetable_id : null,
					TimetableResource_id: ('TimetableResource' == key_parts[0]) ? key_parts[1] : null,// === ('TimetableResource' == dirdata.timetable) ? dirdata.timetable_id : null,
					TimetableStac_id: ('TimetableStac' == key_parts[0]) ? key_parts[1] : null,// === ('TimetableStac' == dirdata.timetable) ? dirdata.timetable_id : null,
					EvnQueue_id: ('EvnQueue' == key_parts[0]) ? key_parts[1] : null,// === ('EvnQueue' == dirdata.timetable) ? dirdata.timetable_id : null,
					allowRedirect: false,
					userMedStaffFact: ownerWindow.userMedStaffFact,
					personData: {
						Person_id: data.Person_id,
						Server_id: data.Server_id,
						PersonEvn_id: data.PersonEvn_id,
						Person_Firname: data.Person_Firname,
						Person_Secname: data.Person_Secname,
						Person_Surname: data.Person_Surname,
						Person_Birthday: data.Person_Birthday
					},
					callback: function() {
						data.callback();
					}
				});
			}.createDelegate(this)
		}];
		
		
		// 192492
		var actionNameList = [];
		if (getRegionNick() != 'vologda') {
			actionNameList = ['action_printdir','action_deletedir'];
		} else {
			
			actionNameList = actions.map(function(oAction) {
				return oAction.name;
			});
		}
		// -- 192492
		

		for (var i=0; i<actions.length; i++) {
			if (actions[i]['name'].inlist(actionNameList)) {
				DirActions[actions[i]['name']] = new Ext.Action( {
					id: 'id_'+actions[i]['name'],
					text: actions[i]['text'],
					disabled: actions[i]['disabled'] || false,
					hidden: actions[i]['hidden'] || false,
					tooltip: actions[i]['tooltip'],
					iconCls : actions[i]['iconCls'] || 'x-btn-text',
					icon: actions[i]['icon'] || null,
					menu: actions[i]['menu'] || null,
					scope: this,
					handler: actions[i]['handler'],
					tag: !! actions[i]['tag'] ? actions[i]['tag'] : null 
				});
			}
		}

		var DirListActionMenu = new Ext.menu.Menu();
		for (var key in DirActions) {
			if (DirActions.hasOwnProperty(key)) {
				DirListActionMenu.add(DirActions[key]);
			}
		}
		
		if (getRegionNick() == 'vologda') {
			that.setPrintItemVisible(DirListActionMenu, data);
			DirListActionMenu.shadow = false;
		}

		return DirListActionMenu;
	},
// 192492
	printForm: function(sReportName, EvnDirection_id) {
		if (!! EvnDirection_id) {
			printBirt({ // https://redmine.swan.perm.ru/issues/54910
				'Report_FileName': sReportName,
				'Report_Params': '&paramEvnDirection=' + EvnDirection_id,
				'Report_Format': 'pdf'
			});
		}
	},
	setPrintItemVisible: function(oMenu, data) {
		
		var oDeferred = $.Deferred();

		var aMenuItems = oMenu.items.items.map(function(oItem) {
			return oItem;
		});		
	
		if (!!data && !!data.UslugaComplex_id) {
			Ext.Ajax.request({
				async: false,
				url: '/?c=UslugaComplex&m=getUslugaComplexAttributes',
				params: {
					'UslugaComplex_id': data.UslugaComplex_id
				},
				callback: function(opt, success, response) {
					var aAttributes = Ext.util.JSON.decode(response.responseText);
					aMenuItems.forEach(function(oItem){
						if (!!oItem.initialConfig && !! oItem.initialConfig.tag  && oItem.initialConfig.tag != '') {
							var oAttr = aAttributes.find(function(oAttr) {
								return oAttr.UslugaComplexAttributeType_SysNick == oItem.initialConfig.tag;
							});
	
							if (!! oAttr) {
								oItem.setVisible(getRegionNick() == 'vologda' && !data.isEvnCourse && data.PrescriptionType_id == 11);
							} else {
								oItem.setVisible(false);
							}
						}
					});
					oDeferred.resolve();
				}
			});
		} else {
			aMenuItems.forEach(function(oItem) {
				if (!!oItem.initialConfig && !!oItem.initialConfig.tag  && oItem.initialConfig.tag != '') {
					oItem.setVisible(false);
				}
			});
			oDeferred.resolve();
		}
		
		return oDeferred;
	},
// --- 192492
	
	getPrescrActionMenu: function(conf) {
		log('getPrescrActionMenu', conf);

		var data = conf.data,
			evdata = conf.evdata,
			evnsysnick = conf.evnsysnick,
			ownerWindow = conf.ownerWindow,
			d = conf.d,
			EvnClass_SysNick = conf.EvnClass_SysNick;

		if (data == false || evdata == false) {
			return false;
		}

		if (ownerWindow.readOnly) {
			evdata.accessType = 'view';
		}

		data.Person_id = evdata.Person_id;
		data.PersonEvn_id = data.PersonEvn_id || evdata.PersonEvn_id;
		data.Server_id = data.Server_id || evdata.Server_id;

		var PrescrActions = {};
		var allowActions = (evdata.accessType && data.EvnPrescr_id && data.EvnPrescr_id > 0);
		var allowActionEdit = (allowActions && evdata.accessType != 'view' && data.EvnPrescr_IsExec != 2 && data.PrescriptionStatusType_id == 1);
		var allowActionExec = (allowActions && evdata.accessType != 'view' && sw.Promed.EvnPrescr.isExecutable(this._createExecEvnPrescrParams(data, ownerWindow)));
		var allowActionUnExec = (allowActions && evdata.accessType != 'view' && sw.Promed.EvnPrescr.isUnExecutable(this._createUnExecEvnPrescrParams(data, ownerWindow)));

		if (ownerWindow.EvnPrescr_id && data.PrescriptionType_id == 13) {
			allowActionExec = (allowActions && data.EvnPrescr_id == ownerWindow.EvnPrescr_id && data.EvnPrescr_IsExec != 2);
		}

		var actions = [{
			name: 'action_exec',
			text: langs('Выполнить'),
			tooltip: langs('Выполнить'),
			disabled: !allowActionExec,
			hidden: (!data.EvnPrescr_id || data.PrescriptionType_id == 1 || data.PrescriptionType_id == 2),
			handler: function (item, evn) {
                data.Diag_id = evdata.Diag_id;
                data.PayType_id = evdata.PayType_id;
				this.execEvnPrescr(evnsysnick, d, evn.getXY(), data, ownerWindow);
			}.createDelegate(this)
		}, {
			name: 'action_unexec',
			text: langs('Отменить выполнение'),
			tooltip: langs('Отменить выполнение'),
			disabled: !allowActionUnExec,
			hidden: (!data.EvnPrescr_id || data.PrescriptionType_id == 1 || data.PrescriptionType_id == 10 || data.PrescriptionType_id == 2),
			handler: function () {
				this.unExecEvnPrescr(evnsysnick, d, data, ownerWindow);
			}.createDelegate(this)
		}, {
			name: 'action_edit',
			text: langs('Редактировать'),
			tooltip: langs('Редактировать назначение'),
			hidden: (data.isEvnCourse),
			disabled: ( !allowActionEdit ),
			handler: function () {
				this._openEvnPrescrEditWindow(data, evnsysnick, ownerWindow, function(fdata){
					data.callback();
				});
			}.createDelegate(this)
		}, {
			name: 'action_editEvnCourse',
			text: langs('Редактировать'),
			tooltip: langs('Редактировать курс'),
			hidden: (!data.isEvnCourse || data.isEvnCourse != 1 || !data.EvnCourse_id ),
			disabled: ( evdata.accessType == 'view' || !data.PrescriptionType_id.toString().inlist(['5', '6'])),
			handler: function () {
                var par = conf;
                par.PrescriptionType_Code = conf.data.PrescriptionType_Code;
                par.action = 'edit';
                if (conf.ownerWindow && conf.ownerWindow.userMedStaffFact) {
                    par.UserLpuSection_id = !Ext.isEmpty(conf.ownerWindow.userMedStaffFact.LpuSection_id) ? conf.ownerWindow.userMedStaffFact.LpuSection_id : null;
                    par.UserLpuUnitType_id = !Ext.isEmpty(conf.ownerWindow.userMedStaffFact.LpuUnitType_id) ? conf.ownerWindow.userMedStaffFact.LpuUnitType_id : null;
				}
                par.data.Diag_id = (conf.evdata && conf.evdata.Diag_id) || '';
                par.parentWin = conf.ownerWindow;
                par.parentEvnClass_SysNick = conf.evnsysnick;
                if(par.PrescriptionType_Code == 5){
                	var me = conf.ownerWindow;
					var section_code = conf.EvnClass_SysNick;
		            var ep_data = conf.data;
	                par.callbackEditWindow = function(changedType){
						if (!Ext.isArray(changedType)) {
							// если не с формы назначений, то обновляем тот тип который редактировали
							//определяем наличие ф-ции
							if (typeof (me.reloadEvnPrescrList) === "function")
							{
							    me.reloadEvnPrescrList(section_code, ep_data.PrescriptionType_id, ep_data.EvnPrescr_pid);
							}
						} else {
							var reloadPrescrParent = false;
							for (var i = 0; i < changedType.length; i++) {
								var type_id = changedType[i];

								me.reloadEvnPrescrList(section_code, type_id, ep_data.EvnPrescr_pid);
								if (Number(type_id).inlist([6, 7, 11, 12, 13])) {
									reloadPrescrParent = true;
								}
							}
							if (reloadPrescrParent) {
								me.loadTreeNode({id: evnsysnick + '_' + ep_data.EvnPrescr_pid});
							}
						}
					};
                }
				this.openEvnCourseEditWindow(par);
			}.createDelegate(this)
		}, {
			name: 'action_remove',
			text: 'Отменить',//Отменить курс
			disabled: ( !data || evdata.accessType == 'view' || !data.EvnCourse_id
				|| (evnsysnick.inlist(['EvnPS', 'EvnSection']) && data.PrescriptionType_id != 5 && allowActions)
				|| (data.EvnPrescr_IsExec && data.EvnPrescr_IsExec == 2)
			),
			handler: function () {
				sw.Promed.EvnPrescr.cancelEvnCourse({
					ownerWindow: ownerWindow
					, getParams: function () {
						return {
							parentEvnClass_SysNick: EvnClass_SysNick
							, PrescriptionType_id: data.PrescriptionType_id
							, EvnCourse_id: data.EvnCourse_id
						};
					}
					, callback: function () {
						data.callback();
						if (typeof data.refreshEvnReceptGeneralSectionCallback == 'function') {
							data.refreshEvnReceptGeneralSectionCallback();
						}
					}.createDelegate(this)
				});
			}.createDelegate(this)
		}, {
			name: 'action_delete',
			text: langs('Отменить назначение'),
			hidden: (data.isEvnCourse),
			disabled: ( !allowActions || evdata.accessType == 'view' || data.EvnPrescr_IsExec == 2),
			handler: function () {
				sw.Promed.EvnPrescr.cancel({
					ownerWindow: ownerWindow
					, getParams: function () {
						return {
							parentEvnClass_SysNick: evnsysnick
							, PrescriptionType_id: data.PrescriptionType_id
							, EvnPrescr_id: data.EvnPrescr_id
						};
					}
					, callback: function () {
						data.callback({
							reloadTreeNode: true
						});
					}.createDelegate(this)
				});
			}.createDelegate(this)
		}];

		var actionNameList = ['action_exec', 'action_unexec', 'action_edit', 'action_editEvnCourse',
			'action_direct', 'action_remove', 'action_delete'];

		for (var i = 0; i < actions.length; i++) {
			if (actions[i]['name'].inlist(actionNameList)) {
				PrescrActions[actions[i]['name']] = new Ext.Action({
					id: 'id_' + actions[i]['name'],
					text: actions[i]['text'],
					disabled: actions[i]['disabled'] || false,
					hidden: actions[i]['hidden'] || false,
					tooltip: actions[i]['tooltip'],
					iconCls: actions[i]['iconCls'] || 'x-btn-text',
					icon: actions[i]['icon'] || null,
					menu: actions[i]['menu'] || null,
					scope: this,
					handler: actions[i]['handler']
				});
			}
		}

		var PrescrListActionMenu = new Ext.menu.Menu();
		for (var key in PrescrActions) {
			if (PrescrActions.hasOwnProperty(key)) {
				PrescrListActionMenu.add(PrescrActions[key]);
			}
		}

		return PrescrListActionMenu;
	},
	signedEvn: function(EvnClass_SysNick, object_id, callback, ownerWindow) {
		var sign_callback = function(success) {
			callback();
		};

						// обычная подпись
						signedDocument({
							Evn_id: object_id
							,callback: sign_callback
							,allowQuestion: false
						});
	},
	/** Создание списка типов назначений
	 *
	 * option.id - id списка типов назначений
	 * option.getParams - функция, которая должна вернуть парамсы
	 * option.onCreate
	 * option.callbackEditWindow
	 * option.onHideEditWindow
	 * option.parentEvnClass_SysNick
	 * option.userMedStaffFact
	 * @param {Object} option
	 * @return {Object}
	 */
	createPrescriptionTypeMenu: function(option) {
		if(!this.prescriptionTypeStore) {
			this.prescriptionTypeStore = new Ext.db.AdapterStore({
				autoLoad: false,
				dbFile: 'Promed.db',
				fields: [
					{name: 'PrescriptionType_Name', type: 'string'},
					{name: 'PrescriptionType_Code', type: 'int'},
					{name: 'PrescriptionType_id', type: 'int'}
				],
				key: 'PrescriptionType_id',
				sortInfo: {field: 'PrescriptionType_Code'},
				tableName: 'PrescriptionType'
			});
		}

		var conf = {
			callback: function(){
				var menu = new Ext.menu.Menu({id: option.id || 'menuPrescriptionType'});
				var exceptionTypes = option.exceptionTypes || [];
				var cfg;
				var defaultHandler = function() {
					option.PrescriptionType_id = this.PrescriptionType_id;
					option.PrescriptionType_Code = this.PrescriptionType_Code;
					option.action = 'add';
					option.data = option.getParams();
					if (option.PrescriptionType_Code == 5) {
						sw.Promed.EvnPrescr.openEvnCourseEditWindow(option);
					} else {
						sw.Promed.EvnPrescr.openEditWindow(option);
					}
				};
				this.prescriptionTypeStore.clearFilter();
				this.prescriptionTypeStore.filterBy(function(record, id) {
					var allowCodeList = [];
					switch (option.parentEvnClass_SysNick) {
						case 'EvnSection':
							allowCodeList = ['1','2','5','6','7','10','11','12','13']; // 3, 13 вместо 4
							break;
						case 'EvnVizitPLStom': //те же, что и в полке
						case 'EvnVizitPL':
							allowCodeList = ['1','2','5','6','7','11','12','13']; // 4
							break;
						case 'EvnPLDispDriver':
							allowCodeList = ['11','12','13'];
							break;
					}
					return record.get('PrescriptionType_Code').toString().inlist(allowCodeList);
				}, this);
				this.prescriptionTypeStore.each(function(record) {
					if(!record.get('PrescriptionType_Code').toString().inlist(exceptionTypes)) {
						cfg = {text: record.get('PrescriptionType_Name'), iconCls: ''};
						if (false && IS_DEBUG != 1 && 11 == parseInt(record.get('PrescriptionType_Code'))) {
							cfg.menu = new Ext.menu.Menu({id: 'menuEvnPrescrLabDiad'});
							cfg.menu.add({
								text: langs('В "свое" МО с направлением'),
								iconCls: '',
								handler: function() {
									option.data = option.getParams();
									getWnd('swEvnPrescrLabDiagListEditWindow').show({
										onHide: option.onHideEditWindow,
										callback: option.callbackEditWindow,
										parentEvnClass_SysNick: option.parentEvnClass_SysNick,
										userMedStaffFact: option.userMedStaffFact,
										Diag_id: option.data.Diag_id,
										formParams: {
											EvnPrescrLabDiag_pid: option.data.Evn_pid
											,EvnPrescrLabDiag_setDate: option.data.begDate
											,Person_id: option.data.Person_id || null
											,PersonEvn_id: option.data.PersonEvn_id || null
											,Server_id: option.data.Server_id || null
										}
									});
								}
							});
							cfg.menu.add({
								text: langs('Только назначение'),
								iconCls: '',
								PrescriptionType_Code: record.get('PrescriptionType_Code'),
								PrescriptionType_id: record.get('PrescriptionType_id'),
								handler: defaultHandler
							});
						} else {
							cfg.PrescriptionType_Code = record.get('PrescriptionType_Code');
							cfg.PrescriptionType_id = record.get('PrescriptionType_id');
							cfg.handler = defaultHandler;
						}

						menu.add(cfg);
					}
				});
				option.onCreate(menu);
			}.createDelegate(this)
		};

		if(this.prescriptionTypeStore.getCount() > 0) {
			conf.callback();
		} else {
			this.prescriptionTypeStore.load(conf);
		}

	},
	/** Открывает окно добавления назначения с копированием графика
	 *
	 * option.data - парамсы (Evn_pid,EvnPrescr_id)
	 * option.PrescriptionType_Code -
	 * option.parentEvnClass_SysNick
	 * option.callbackEditWindow
	 * @param {Object} option
	 * @return {void}
	 */
	grafcopy: function(option) {
		if(!option || !option.PrescriptionType_Code || !option.PrescriptionType_Code.toString().inlist(['5','6']) )
			return false;
		option.action = 'addwithgrafcopy';
		this.openEditWindow(option);
	},
	/** Открытие формы со списком служб, которые оказывают услугу из назначения, для выбора.
	 * После выбора службы открывается расписание либо службы, либо услуги, если в службе заведено расписание на конкретную услугу.
	 *
	 * option парамсы (Evn_pid,EvnPrescr_id,uslugaList,Person_id, PersonEvn_id, Server_id, Diag_id ...)
	 * option.callback
	 * option.onHide
	 * @param {Object} option
	 * @return {bool}
	 */
	direct: function(option) {
		if(!option || !option.PrescriptionType_Code || !option.PrescriptionType_Code.toString().inlist(['4','6','7','11','12','13']) )
		{
			return false;
		}
		var personData = {};
		personData.Person_id = option.Person_id;
		personData.PersonEvn_id = option.PersonEvn_id;
		personData.Server_id = option.Server_id;
		personData.Person_Firname = option.Person_Firname;
		personData.Person_Surname = option.Person_Surname;
		personData.Person_Secname = option.Person_Secname;
		personData.Person_Birthday = option.Person_Birthday;

		var evnPrescrData = {};
		evnPrescrData.EvnPrescr_id = option.EvnPrescr_id;
		evnPrescrData.PrescriptionType_Code = option.PrescriptionType_Code;
		evnPrescrData.EvnPrescr_IsCito = option.EvnPrescr_IsCito || 1;
		evnPrescrData.UslugaComplex_2011id = option.UslugaComplex_2011id || null;

		var directionData = {};
		directionData.EvnDirection_pid = option.Evn_pid;
		directionData.Diag_id = option.Diag_id;
		directionData.MedPersonal_id = option.userMedStaffFact.MedPersonal_id;

		getWnd('swUslugaComplexMedServiceListWindow').show({
			mode: option.mode || null,
			userMedStaffFact: option.userMedStaffFact,
			personData: personData,
			evnPrescrData: evnPrescrData,
			directionData: directionData,
			onDirection: function(ndata) {
				if ( typeof option.callback == 'function' ) {
					option.callback(ndata);
				}
			}
		});
		return true;
	},
	/**
	 * Отмена назначений из курса
	 * невыполненные назначения в курсе отменяются
	 *
	 * conf.ownerWindow -
	 * conf.getParams - функция, которая должна вернуть парамсы (PrescriptionType_id,EvnPrescr_id,parentEvnClass_SysNick,EvnPrescr_setDate,EvnPrescr_rangeDate)
	 * conf.callback
	 * @param {Object} conf
	 * @return {bool}
	 */
	cancelEvnCourse: function(conf) {
		var data = conf.getParams();
		if(!data.PrescriptionType_id || !data.EvnCourse_id) {
			return false;
		}
		var input_data = {
			PrescriptionType_id: data.PrescriptionType_id
			,EvnCourse_id: data.EvnCourse_id
			,parentEvnClass_SysNick: data.parentEvnClass_SysNick || null
		};
		function cancelEvnCourse(input_data) {
			var loadMask = new Ext.LoadMask(conf.ownerWindow.getEl(), {msg: "Отмена назначений из курса..."});
			loadMask.show();
			Ext.Ajax.request({
				showErrors: false,
				params: input_data,
				callback: function(opt, success, response) {
					loadMask.hide();
					if (response && response.responseText) {
						var answer = Ext.util.JSON.decode(response.responseText);
						if (answer.success) {
							if (typeof conf.callback == 'function') conf.callback();
						} else if (answer.Error_Msg) {
							if (answer.TimetableMedService_id) {
								sw.Promed.Direction.cancel({
									cancelType: 'cancel',
									ownerWindow: conf.ownerWindow,
									TimetableMedService_id: answer.TimetableMedService_id,
									callback: function(cfg) {
										cancelEvnCourse(input_data);
									}
								});
							} else if (answer.EvnQueue_id) {
								sw.Promed.Direction.cancel({
									cancelType: 'cancel',
									ownerWindow: conf.ownerWindow,
									EvnQueue_id: answer.EvnQueue_id,
									callback: function(cfg) {
										cancelEvnCourse(input_data);
									}
								});
							} else if (answer.TimetableResource_id) {
								sw.Promed.Direction.cancel({
									cancelType: 'cancel',
									ownerWindow: conf.ownerWindow,
									TimetableResource_id: answer.TimetableResource_id,
									callback: function(cfg) {
										cancelEvnCourse(input_data);
									}
								});
							} else {
								Ext.Msg.alert(langs('Ошибка'), answer.Error_Msg);
							}
						}
					} else {
						Ext.Msg.alert(langs('Ошибка'), langs('Ошибка при отмене назначений из курса! Отсутствует ответ сервера.'));
					}
				},
				url: '/?c=EvnPrescr&m=cancelEvnCourse'
			});
		}
		cancelEvnCourse(input_data);
		return true;
	},
	/** Отмена назначения
	 *
	 * conf.ownerWindow -
	 * conf.getParams - функция, которая должна вернуть парамсы (PrescriptionType_id,EvnPrescr_id,parentEvnClass_SysNick,EvnPrescr_setDate,EvnPrescr_rangeDate)
	 * conf.callback
	 * @param {Object} conf
	 * @return {bool}
	 */
	cancel: function(conf) {
		var data = conf.getParams();
		if(!data.PrescriptionType_id || !data.EvnPrescr_id) {
			return false;
		}
		var input_data = {
			PrescriptionType_id: data.PrescriptionType_id
			,EvnPrescr_id: data.EvnPrescr_id
			,parentEvnClass_SysNick: data.parentEvnClass_SysNick || null
			,EvnPrescr_setDate: data.EvnPrescr_setDate || null
			,DirType_id: data.DirType_id || null
			,EvnPrescr_IsExec: data.EvnPrescr_IsExec || null
			,EvnStatus_id: data.EvnStatus_id || null
			,UslugaComplex_id: data.UslugaComplex_id || null
			,couple: data.couple || null
		};
		function cancelEvnPrescr(input_data) {
			conf.ownerWindow.getLoadMask('Отмена назначения...').show();
			Ext.Ajax.request({
				showErrors: false,
				params: input_data,
				callback: function(opt, success, response) {
					conf.ownerWindow.getLoadMask().hide();
					log(response, (response && response.responseText));
					if (response && response.responseText) {
						var answer = Ext.util.JSON.decode(response.responseText);
						log(answer);
						if (answer.success) {
							if (typeof conf.callback == 'function') conf.callback();
						} else if (answer.Error_Msg) {
							if (answer.TimetableMedService_id) {
								sw.Promed.Direction.cancel({
									cancelType: 'cancel',
									ownerWindow: conf.ownerWindow,
									TimetableMedService_id: answer.TimetableMedService_id,
									DirType_id: data.DirType_id || null,
									callback: function(cfg) {
										cancelEvnPrescr(input_data);
									}
								});
							} else if (answer.EvnQueue_id) {
								sw.Promed.Direction.cancel({
									cancelType: 'cancel',
									ownerWindow: conf.ownerWindow,
									EvnQueue_id: answer.EvnQueue_id,
									callback: function(cfg) {
										cancelEvnPrescr(input_data);
									}
								});
							} else if (answer.EvnDirection_id && Number(answer.EvnStatus_id).inlist([10,16,17])) {
								sw.Promed.Direction.cancel({
									cancelType: 'cancel',
									ownerWindow: conf.ownerWindow,
									EvnDirection_id: answer.EvnDirection_id,
									DirType_id: answer.DirType_id || null,
									callback: function(cfg) {
										cancelEvnPrescr(input_data);
									}
								});
							} else if (answer.TimetableResource_id) {
								sw.Promed.Direction.cancel({
									cancelType: 'cancel',
									ownerWindow: conf.ownerWindow,
									TimetableResource_id: answer.TimetableResource_id,
									callback: function(cfg) {
										cancelEvnPrescr(input_data);
									}
								});
							} else {
								Ext.Msg.alert(langs('Ошибка'), answer.Error_Msg);
							}
						}
					} else {
						Ext.Msg.alert(langs('Ошибка'), langs('Ошибка при отмене назначения! Отсутствует ответ сервера.'));
					}
				},
				url: '/?c=EvnPrescr&m=cancelEvnPrescr'
			});
		}

		if (conf.withoutQuestion) {
			cancelEvnPrescr(input_data);
		} else {
			Ext.Msg.show({
				title: langs('Внимание'),
				msg: 'Отменить назначение?',
				buttons: Ext.Msg.YESNO,
				fn: function(btn) {
					if (btn === 'yes') {
						cancelEvnPrescr(input_data);
					} else {
						return false;
					}
				},
				icon: Ext.MessageBox.QUESTION
			});
		}
	},
	/** Подписание назначения
	 *
	 * conf.ownerWindow -
	 * conf.is_all - подписание всех назначений
	 * conf.getParams - функция, которая должна вернуть парамсы (parentEvnClass_SysNick,PrescriptionType_id,Evn_pid,EvnPrescr_id,EvnPrescr_setDate,EvnPrescr_rangeDate)
	 * conf.callback
	 * @param {Object} conf
	 * @return {void}
	 */
	sign: function(conf) {
		var data = conf.getParams();
		if(!data.PrescriptionType_id && !conf.is_all) {
			return false;
		}
		var input_data = {
			PrescriptionType_id: data.PrescriptionType_id
			,parentEvnClass_SysNick: data.parentEvnClass_SysNick || null
			,EvnPrescr_setDate: data.EvnPrescr_setDate || null
			,EvnPrescr_rangeDate: data.EvnPrescr_rangeDate || null
		};
		if (conf.unsign) {
			input_data.unsign = true;
		}
		var url = '/?c=EvnPrescr&m=signEvnPrescr';
		if(conf.is_all) {
			input_data.PrescriptionType_id = null;
			input_data.EvnPrescr_id = null;
			input_data.EvnPrescr_setDate = null;
			input_data.EvnPrescr_rangeDate = null;
			input_data.EvnPrescr_pid = data.Evn_pid;
			url = '/?c=EvnPrescr&m=signEvnPrescrAll';
		} else if(data.PrescriptionType_id.toString().inlist(['4','5','6','7','11','12','13'])) {
			if(!data.Evn_pid) {
				return false;
			}
			if(!data.EvnPrescr_id) {
				return false;
			}
			input_data.EvnPrescr_id = data.EvnPrescr_id;
			input_data.EvnPrescr_pid = data.Evn_pid;
		} else {
			if(!data.EvnPrescr_id) {
				return false;
			}
			//input_data.EvnPrescr_id = null;
			input_data.EvnPrescr_pid = data.EvnPrescr_id;
		}
		/*Ext.Msg.show({
			title: langs('Внимание'),
			msg: langs('После подписания невозможно будет редактировать данные назначения. Продолжить?'),
			buttons: Ext.Msg.YESNO,
			fn: function(btn) {
				if (btn === 'yes') {*/
					var loadMask = new Ext.LoadMask(conf.ownerWindow.getEl(), {msg: "Подписание назначения..."});
					loadMask.show();

					Ext.Ajax.request({
						failure: function(response, opt) {
							loadMask.hide();
							sw.swMsg.alert(langs('Ошибка'), langs('Ошибка при подписании назначения'));
						},
						params: input_data,
						success: function(response, opt) {
							loadMask.hide();
							if (response.responseText) {
								var answer = Ext.util.JSON.decode(response.responseText);
								if (answer.success) {
									if(typeof conf.callback == 'function')
										conf.callback();
								} else if (answer.Error_Message) {
									Ext.Msg.alert(langs('Ошибка'), answer.Error_Message);
								}
							}
							else {
								Ext.Msg.alert(langs('Ошибка'), langs('Ошибка при подписании назначения! Отсутствует ответ сервера.'));
							}
						},
						url: url
					});
				/*} else {
					return false;
				}
			},
			icon: Ext.MessageBox.QUESTION
		});*/
	},
	/** Создание назначения по шаблону из списка CureStandartList
	 *
	 * conf.parentEvnClass_SysNick -
	 * conf.Evn_rid -
	 * conf.Evn_pid -
	 * conf.PersonEvn_id -
	 * conf.Server_id -
	 * conf.ownerWindow -
	 * conf.callback
	 * @param {Object} conf
	 * @return {void}
	 */
	lastLoadCureStandartPid: null,
	lastSelectCureStandartId: null,
	storeCureStandart: new Ext.data.Store({
		reader: new Ext.data.JsonReader(
		{
			id: 'CureStandart_id'
		},
		[
			{name: 'CureStandart_id', mapping: 'CureStandart_id'},
			{name: 'Row_Num', type: 'string', mapping: 'Row_Num'},
			{name: 'html', type: 'string', mapping: 'html'}
		]),
		url: '/?c=EvnPrescr&m=loadCureStandartListForRestyledGrid'
	}),
	openCureStandartSelectWindow: function(conf) {
		var me = this,
			loadMask = new Ext.LoadMask(conf.ownerWindow.getEl(), {msg: "Запрос списка " + getMESAlias() + "..."});
		loadMask.show();
		Ext.Ajax.request({
			failure: function(response, opt) {
				loadMask.hide();
				sw.swMsg.alert(langs('Ошибка'), langs('Ошибка при загрузке списка ') + getMESAlias());
			},
			params: {
				EvnPrescr_pid: conf.Evn_pid
			},
			success: function(response, opt) {
				loadMask.hide();
				if (response.responseText) {
					var answer = Ext.util.JSON.decode(response.responseText);

					if ( !Ext.isEmpty(answer.Error_Msg) )
					{
						return false;
					}

					if (!Ext.isArray(answer)) {
						Ext.Msg.alert(langs('Ошибка'), langs('Неправильный формат ответа сервера'));
						return false;
					}
					if (answer.length > 0 && answer[0].Error_Message) {
						Ext.Msg.alert(langs('Ошибка'), answer[0].Error_Message);
						return false;
					}
					me.storeCureStandart.removeAll();
					me.storeCureStandart.loadData(answer);
					if ( me.storeCureStandart.getCount()==0 ) {
						sw.swMsg.alert(langs('Сообщение'), getMESAlias() + langs(' не найден'));
						return false;
					} else if ( me.storeCureStandart.getCount()==1) {
						conf.CureStandart_id = this.storeCureStandart.getAt(0).get('CureStandart_id');
						this.selectCureStandart(conf);
						return true;
					} else {
						getWnd('swCureStandartSelectWindow').show({onSelectParams: conf});
					}
				}
				else {
					Ext.Msg.alert('Ошибка', 'Ошибка при запросе списка ' + getMESAlias() + '! Отсутствует ответ сервера.');
				}
			}.createDelegate(this),
			url: '/?c=EvnPrescr&m=loadCureStandartListForRestyledGrid'
		});
	},
	showCureStandart: function(evnId, ownerWindow) {
		this.openCureStandartSelectWindow({
			isForPrint: true,
			Evn_pid: evnId,
			ownerWindow: ownerWindow
		});
	},
	addwithtemplate: function(conf) {
		conf.isForPrint = false;
		this.openCureStandartSelectWindow(conf);
	},
	/** Создание назначения по заданному шаблону CureStandart_id
	 *
	 * conf.CureStandart_id -
	 * conf.parentEvnClass_SysNick -
	 * conf.Evn_rid -
	 * conf.Evn_pid -
	 * conf.PersonEvn_id -
	 * conf.Server_id -
	 * conf.ownerWindow -
	 * conf.callback
	 * @param {Object} conf
	 * @return {void}
	 */
	selectCureStandart: function(conf) {
		if (conf.isForPrint) {
			window.open('/?c=EvnPrescr&m=printCureStandart&CureStandart_id='+conf.CureStandart_id, '_blank');
		} else {
			this.lastSelectCureStandartId = conf.CureStandart_id;
			var params = new Object();
			params.callback = function(ndata) {
				if ( typeof conf.callback == 'function' ) {
					conf.callback(ndata);
				}
			}.createDelegate(this);
			params.CureStandart_id = conf.CureStandart_id;
			params.parentEvnClass_SysNick = conf.parentEvnClass_SysNick;
			params.Evn_pid = conf.Evn_pid;
			params.Evn_rid = conf.Evn_rid;
			params.PersonEvn_id = conf.PersonEvn_id;
			params.Server_id = conf.Server_id;
			params.action = 'edit';
			getWnd('swCureStandartTemplateWindow').show(params);
		}
	},
	execRequest: function(conf) {
		var loadMask = new Ext.LoadMask(conf.ownerWindow.getEl(), {msg: "Выполнение запроса к серверу..."});
		loadMask.show();
		var params = {
			EvnPrescr_id: conf.EvnPrescr_id,
			Timetable_id: conf.Timetable_id || null,
			PrescriptionType_id: conf.PrescriptionType_id
		};
		if(sw.Promed.MedStaffFactByUser.last) {
			params.LpuSection_id = sw.Promed.MedStaffFactByUser.last.LpuSection_id || null;
			params.MedPersonal_id = sw.Promed.MedStaffFactByUser.last.MedPersonal_id || null;
			params.MedService_id = sw.Promed.MedStaffFactByUser.last.MedService_id || null;
		}
		Ext.Ajax.request({
			failure: function(response, options) {
				loadMask.hide();
				sw.swMsg.alert(langs('Ошибка'), (response.status ? response.status.toString() + ' ' + response.statusText : langs('Ошибка при выполнении запроса к серверу')));
			},
			params: params,
			success: function(response, options) {
				loadMask.hide();

				var response_obj = Ext.util.JSON.decode(response.responseText);

				if ( response_obj.success == false ) {
					sw.swMsg.alert(langs('Ошибка'), response_obj.Error_Msg ? response_obj.Error_Msg : langs('Ошибка при выполнении запроса к серверу'));
				}
				else {
					conf.onExecSuccess(conf);
				}
			},
			url: '/?c=EvnPrescr&m=execEvnPrescr'
		});
	},
	_execUsluga: function(conf) {
		if ( !this.isExecutable(conf) ) {
			conf.onExecCancel();
			return false;
		}
		var code = null, win, uslugaClass;
		var uslugaList;
		if(typeof conf.UslugaId_List == 'string' && conf.UslugaId_List.length > 0) {
			uslugaList = conf.UslugaId_List.split(',');
		} else {
			return false;
		}
		var thas = this;
		//открывать форму добавленпия услуги в зависимости от типа назначения
		//и автоматически подставлять совпадающую по эталонным полям услугу,
		//на комбо услуг накладывать дополнительный фильтр по атрибуту услуги соответственно типу назначения
		switch ( Number(conf.PrescriptionType_Code) ) {
			case 7: // опер.
				//открываем форму добавления опер. услуги
				win = getWnd('swEvnUslugaOperEditWindow');
				if ( win.isVisible() ) {
					win.hide();
				}
				code = 1;
				uslugaClass = 'EvnUslugaOper';
				break;
			case 6: // ман. и проц.
			case 13: //конс.прием
			case 11: //лаб.
			case 12: //ФД
				//открываем форму добавления общей услуги
				win = getWnd('swEvnUslugaEditWindow');
				if ( win.isVisible() ) {
					win.hide();
				}
				code = 6;
				uslugaClass = 'EvnUslugaCommon';
				break;
			default:
				conf.onExecCancel();
				sw.swMsg.alert(langs('Сообщение'), langs('Для данного типа назначений не предусмотрено выполнение с оказанием услуги!'));
				break;
		}
		if (code>0) {
			Ext.Ajax.request({
				url: '/?c=EvnPrescr&m=loadEvnUslugaData',
				params: {
					Evn_id: conf.EvnPrescr_pid,
					UslugaComplex_id: uslugaList[0]
				},
				success: function(r, o) {
					var obj = Ext.util.JSON.decode(r.responseText)[0];
					if ( obj.success == false ) {
						sw.swMsg.alert(langs('Ошибка'), obj.Error_Msg ? obj.Error_Msg : langs('Ошибка при выполнении запроса к серверу'));
						return false;
					}

					var params = {
						action: 'add',
						EvnClass_SysNick: uslugaClass,
						parentClass: (obj.EvnClass_SysNick=='EvnSection')?'EvnSection':'EvnVizit',//'EvnPrescr',
						Person_id: conf.Person_id,
						onHide: function() {
							conf.onExecCancel();
						},
						callback: function() {
							thas.execRequest(conf);
						}
					};

					params.formParams = {};
					params.formParams.EvnUsluga_id = 0;
					params.formParams.EvnUsluga_pid = conf.EvnPrescr_pid;
					if (conf.PrescriptionType_Code == 7) {
						params.formParams.EvnUslugaOper_pid = conf.EvnPrescr_pid;
					}
					params.formParams.PersonEvn_id = conf.PersonEvn_id;
					params.formParams.Person_id = conf.Person_id;
					params.formParams.Server_id = conf.Server_id;
					params.formParams.MedStaffFact_id = conf.userMedStaffFact.MedStaffFact_id||null;
					params.formParams.LpuSection_uid = conf.userMedStaffFact.LpuSection_id||null;
					params.formParams.MedPersonal_id = conf.userMedStaffFact.MedPersonal_id||null;

					params.formParams.EvnPrescr_id = conf.EvnPrescr_id;
					params.PrescriptionType_Code = conf.PrescriptionType_Code;
					if (conf.PrescriptionType_Code == 6) {
						params.formParams.EvnPrescrProc_id = conf.EvnPrescr_id;
					}
					params.formParams[uslugaClass +'_setDate'] = conf.EvnPrescr_setDate;
					params.formParams.UslugaComplex_id = uslugaList[0];
					params.formParams.Usluga_id = obj.Usluga_id;
					if (getRegionNick()=='kz') params.formParams.PayType_id = conf.PayType_id;

					// данные для ParentEvnCombo uslugaClass
					params.parentEvnComboData = [{
						Evn_id: conf.EvnPrescr_pid
						,Evn_Name: obj.Evn_setDate + ' / ' + obj.LpuSection_Name + ' / ' + obj.MedPersonal_FIO
						,Evn_setDate: obj.Evn_setDate
						,Evn_setTime: obj.Evn_setTime
						,MedStaffFact_id: obj.MedStaffFact_id
						,LpuSection_id: obj.LpuSection_id
						,MedPersonal_id: obj.MedPersonal_id
					}];

					sw.Promed.UslugaClass.onSelectCode(
						code,
						params,
						conf.EvnPrescr_rid,
						null
					);
					return true;
				}
			});
		}
		return true;
	},
	unExec: function(conf) {
		if ( typeof conf.onSuccess != 'function' ) {
			conf.onSuccess = Ext.emptyFn;
		}
		if ( typeof conf.onCancel != 'function' ) {
			conf.onCancel = Ext.emptyFn;
		}
		if ( !this.isUnExecutable(conf)) {
			conf.onCancel();
			return false;
		}

		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId) {
				if ( buttonId == 'yes' ) {
					var loadMask = new Ext.LoadMask(conf.ownerWindow.getEl(), {msg: "Выполнение запроса к серверу..."});
					loadMask.show();
					Ext.Ajax.request({
						failure: function(response, options) {
							loadMask.hide();
							sw.swMsg.alert(langs('Ошибка'), (response.status ? response.status.toString() + ' ' + response.statusText : langs('Ошибка при выполнении запроса к серверу')));
						},
						params: {
							EvnPrescr_id: conf.EvnPrescrDay_id,
							PrescriptionType_id: conf.PrescriptionType_id
						},
						success: function(response, options) {
							loadMask.hide();
							var response_obj = Ext.util.JSON.decode(response.responseText);
							if ( response_obj.success == false ) {
								sw.swMsg.alert(langs('Ошибка'), response_obj.Error_Msg ? response_obj.Error_Msg : langs('Ошибка при выполнении запроса к серверу'));
							} else {
								conf.onSuccess();
							}
						},
						url: '/?c=EvnPrescr&m=rollbackEvnPrescrExecution'
					});
				} else {
					conf.onCancel();
				}
			},
			icon: Ext.MessageBox.QUESTION,
			msg: langs('Отменить факт выполнения назначения?'),
			title: langs('Вопрос')
		});
		return true;
	},
	/**
	 * Проверяем возможность выполнения
	 * Тут должна быть аналогичная логика проверки,
	 * как и в EvnPrescr_Model::execEvnPrescr
	 * @param data
	 * @return {Boolean}
	 */
	isExecutable: function(data) {
		if ( !( data && data.EvnPrescr_id && data.EvnPrescr_id>0
			&& data.PrescriptionType_Code && data.PrescriptionType_Code>0 ) ) {
			return false;
		}
		if ( data.EvnPrescr_IsExec && data.EvnPrescr_IsExec == 2 ) {
			return false;
		}
		/*if ( data.PrescriptionStatusType_id && data.PrescriptionStatusType_id == 3 ) {
			//нельзя выполнить отмененные
			return false;
		}
		if ( (!data.PrescriptionStatusType_id || data.PrescriptionStatusType_id != 2) ) {
			//выполнить можно только подписанные
			return false;
		}*/
		var isExecutable = true;
		switch (Number(data.PrescriptionType_Code)) {
			case 10:
				isExecutable = true;
				break;
			case 5:
				isExecutable = true;
				break;
			case 6: case 7: case 11: case 12: case 13:
				if ( data.EvnDirection_id && data.EvnDirection_id > 0 ) {
					//действие должно быть недоступно для назначений, по которым созданы направления.
					isExecutable = false;
				}
				break;
			default:
				isExecutable = false;
				break;
		}
		return isExecutable;
	},
	/**
	 * проверяем возможность отмены выполнения
	 * Тут должна быть аналогичная логика проверки,
	 * как и в EvnPrescr_Model::rollbackEvnPrescrExecution
	 * @param data
	 * @return {Boolean}
	 */
	isUnExecutable: function(data) {
		if ( !data
			|| !data.EvnPrescrDay_id
			|| !data.PrescriptionType_id
			//|| data.PrescriptionStatusType_id == 2
		) {
			return false;
		}
		if ( data.EvnPrescr_IsExec && data.EvnPrescr_IsExec != 2 ) {
			return false;
		}
		var isUnExecutable = true;
		switch (Number(data.PrescriptionType_id)) {
			case 5:
				if ( data.EvnPrescr_IsHasEvn && data.EvnPrescr_IsHasEvn == 2 ) {
					isUnExecutable = false;
				}
				break;
			case 10: break;
			case 6: case 7: case 11: case 12: case 13:
				if ( data.EvnPrescr_IsHasEvn && data.EvnPrescr_IsHasEvn == 2 ) {
					isUnExecutable = false;
				}
				if ( data.EvnDirection_id && data.EvnDirection_id > 0 ) {
					//действие должно быть недоступно для назначений, по которым созданы направления.
					isUnExecutable = false;
				}
				break;
			default:
				isUnExecutable = false;
				break;
		}
		return isUnExecutable;
	},
	exec: function(conf) {
		if ( !this.isExecutable(conf) ) {
			conf.onExecCancel();
			return false;
		}
		if ( typeof conf.onExecSuccess != 'function' ) {
			conf.onExecSuccess = Ext.emptyFn;
		}
		if ( typeof conf.onExecCancel != 'function' ) {
			conf.onExecCancel = Ext.emptyFn;
		}
		if (!conf.userMedStaffFact) {
			conf.userMedStaffFact = sw.Promed.MedStaffFactByUser.current;
		}

		var thas = this;
		switch ( Number(conf.PrescriptionType_Code) ) {
			case 1:
			case 2:
				thas.execRequest(conf);
				break;
			case 6: // ман. и проц.
			case 7: // опер.
			case 11: //лаб.
			case 12: //ФД
			case 13: //конс.прием
				if (conf.mode && conf.mode == 'execUsluga') {
					thas._execUsluga(conf);
					break;
				}
				if (conf.mode && conf.mode == 'simple') {
					thas.execRequest(conf);
					break;
				}
				// для назначений услуг выпадает список: Выполнить, Выполнить с оказанием услуги.
				 if (!this.uslugaExecMenu) {
					this.uslugaExecMenu = new Ext.menu.Menu({id: 'menuExecEvnPrescrUsluga'});
					this.uslugaExecMenu.cnfg = conf;
					this.uslugaExecMenu.add({
						text: langs('Выполнить'),
						iconCls: '',
						handler: function() {
							thas.execRequest(thas.uslugaExecMenu.cnfg);
						}
					});
					this.uslugaExecMenu.add({
						text: langs('Выполнить с оказанием услуги'),
						iconCls: '',
						handler: function() {
							thas._execUsluga(thas.uslugaExecMenu.cnfg);
						}
					});
				}
				this.uslugaExecMenu.items.itemAt(1).setDisabled(!this.isExecutable(conf));
				this.uslugaExecMenu.cnfg = conf;

				if (conf.btnId) {
					if (Ext.get(conf.btnId) != null) {
						this.uslugaExecMenu.show(Ext.get(conf.btnId)); //,'tr'
					} else {
						if (conf.coords) {
							this.uslugaExecMenu.showAt([conf.coords[0], conf.coords[1]]);
						}
					}

				}
				break;
			case 5:
				if (conf.mode && conf.mode == 'simple') {
					thas.execRequest(conf);
					break;
				}
				var loadEvnPrescrTreatDrugList = function(conf, callback) {
					var loadMask = new Ext.LoadMask(conf.ownerWindow.getEl(), {msg: "Загрузка списка медикаментов в назначении..."});
					loadMask.show();
					Ext.Ajax.request({
						failure: function(response, options) {
							loadMask.hide();
							sw.swMsg.alert(langs('Ошибка'), (response.status ? response.status.toString() + ' ' + response.statusText : langs('Ошибка при выполнении запроса к серверу')));
						}.createDelegate(this),
						params: {
							EvnPrescrTreat_id: conf.EvnPrescr_id
						},
						success: function(response, options) {
							loadMask.hide();
							var response_obj = Ext.util.JSON.decode(response.responseText);
							callback(conf, response_obj);
						},
						url: '/?c=EvnPrescr&m=loadEvnDrugGrid'
					});
				};
				var execWithUseDrug = function(conf, response_obj){
					// Если в исполняемом назначении один медикамент, сразу вызывать форму списания
					var params = {};
					var winName = '';
					if (response_obj.length == 1 && !response_obj[0].EvnDrug_id) {
						// сразу вызывать форму списания
						winName = getEvnDrugEditWindowName();
						params.userMedStaffFact = !Ext.isEmpty(conf.userMedStaffFact) ? conf.userMedStaffFact : null;
						params.openMode = 'prescription';
						params.Person_id = response_obj[0].Person_id;
						params.action = 'add';
						params.formParams = response_obj[0];
						var EvnPrescrTreatDrug_FactCount = response_obj[0].EvnPrescrTreatDrug_FactCount||0;
						//Количество невыполненных приемов
						params.formParams.PrescrFactCountDiff = response_obj[0].EvnPrescrTreat_PrescrCount - EvnPrescrTreatDrug_FactCount;
						if (!conf.parentEvnClass_SysNick || conf.parentEvnClass_SysNick.inlist(['EvnPS','EvnSection'])) {
							params.formParams.EvnPrescrTreat_Fact = 1;
						} else {
							params.formParams.EvnPrescrTreat_Fact = response_obj[0].EvnPrescrTreat_PrescrCount;
						}
						params.parentEvnClass_SysNick = conf.parentEvnClass_SysNick;
						params.parentEvnComboData = [];
						params.parentEvnComboData.push({
							Evn_id: response_obj[0].EvnDrug_pid
							,Evn_Name: response_obj[0].Evn_setDate + ' / ' + response_obj[0].LpuSection_Name + ' / ' + response_obj[0].MedPersonal_FIO
							,Evn_setDate: Date.parseDate(response_obj[0].Evn_setDate, 'd.m.Y')
							,MedStaffFact_id: response_obj[0].MedStaffFact_id
							,LpuSection_id: response_obj[0].LpuSection_id
							,MedPersonal_id: response_obj[0].MedPersonal_id
						});
						params.callback = function(){
							conf.mode = 'withUseDrug';
							conf.onExecSuccess(conf);
						};
					} else {
						// отображать список медикаментов
						winName = 'swEvnPrescrDrugStreamWindow';
						params = {
							parentEvnClass_SysNick: conf.parentEvnClass_SysNick,
							DrugGridData: response_obj,
							callback: function() {
								conf.mode = 'withUseDrug';
								conf.onExecSuccess(conf);
							}
						};
					}
					var win = getWnd(winName);
					if (win.isVisible()) {
						win.hide();
					}
					win.show(params);
				};
				// Загрузка списка медикаментов в назначении
				loadEvnPrescrTreatDrugList(conf, function(conf, evnPrescrTreatDrugList) {
					if (false == Ext.isArray(evnPrescrTreatDrugList) || 0 == evnPrescrTreatDrugList.length) {
						log('evnPrescrTreatDrugList is empty');
						return false;
					}
					var isAllowExecWithUseDrug = true, i = 0, me = this;
					while (evnPrescrTreatDrugList.length > i) {
						if (!evnPrescrTreatDrugList[i].Drug_id) {
							// если в отделении на остатках нет одного из назначенных медикаментов
							isAllowExecWithUseDrug = false;
							break;
						}
						i++;
					}
					if (conf.mode && conf.mode == 'withUseDrug' && isAllowExecWithUseDrug) {
						execWithUseDrug(conf, evnPrescrTreatDrugList);
						return true;
					}
					if (conf.mode && conf.mode == 'withUseDrug' && !isAllowExecWithUseDrug) {
						sw.swMsg.alert(langs('Сообщение'), langs('Нельзя выполнить с использованием медикаментов, т.к. в отделении на остатках нет одного из назначенных медикаментов!'));
						return false;
					}
					// для назначений медикаментов выпадает список: Выполнить, Выполнить с использованием медикаментов
					me.treatExecMenu = new Ext.menu.Menu({id: 'menuExecEvnPrescrTreat'});
					me.treatExecMenu.cnfg = conf;
					me.treatExecMenu.add({
						text: langs('Выполнить'),
						iconCls: '',
						handler: function() {
							thas.execRequest(me.treatExecMenu.cnfg);
						}
					});
					me.treatExecMenu.add({
						text: langs('Выполнить с использованием медикаментов'),
						iconCls: '',
						disabled: !isAllowExecWithUseDrug,
						handler: function() {
							execWithUseDrug(me.treatExecMenu.cnfg, evnPrescrTreatDrugList);
						}
					});
					if (conf.btnId) {
						//console.log(Ext.get(conf.btnId));
						if (Ext.get(conf.btnId) != null) {
							me.treatExecMenu.show(Ext.get(conf.btnId)); //,'tr'
						} else if (conf.coords) {
							me.treatExecMenu.showAt(conf.coords); //,'tr'
						}
					}
				});
				break;
			case 10:
				// открываем форму заполнения параметров наблюдения
				// При этом назначение считается исполненным, если заполнены все параметры наблюдения.
				var win = getWnd('swEvnObservPrescrEditWindow');
				if ( win.isVisible() ) {
					win.hide();
				}
				win.show({
					disableChangeTime: !conf.allowChangeTime,
					onHide: function() {
						conf.onExecCancel();
					},
					callback: function() {
						conf.onExecSuccess(conf);
					},
					formParams: {
						EvnObserv_pid: conf.EvnPrescr_id
						,ObservTimeType_id: conf.ObservTimeType_id
						,EvnObserv_setDate: conf.EvnPrescr_setDate
						,Person_id: conf.Person_id
						,Person_Birthday: Ext.util.Format.date(conf.Person_Birthday, 'd.m.Y')
						,PersonEvn_id: conf.PersonEvn_id
						,Server_id: conf.Server_id
					}
				});
				break;
			default:
				conf.onExecCancel();
				sw.swMsg.alert(langs('Сообщение'), langs('Для данного типа назначений выполнение не предусмотрено!'));
				break;
		}
		return true;
	},
	/**
	 * Открывает расписание для выбора бирки
	 * @param option
	 */
	openTimetable: function(option) {
		if (typeof option.MedService == 'object') {
			var win = getWnd('swTTMSScheduleRecordWindow');
			if (win.isVisible()) {
				win.hide();
			}
			win.show({
				disableRecord: true,
				MedService_id: option.MedService.MedService_id,
				MedServiceType_id: option.MedService.MedServiceType_id,
				MedService_Nick: option.MedService.MedService_Nick,
				MedService_Name: option.MedService.MedService_Name,
				MedServiceType_SysNick: option.MedService.MedServiceType_SysNick,
				Lpu_did: option.MedService.Lpu_id,
				date: option.MedService.date,
				callback: option.callback,
				UslugaComplexMedService_id:option.MedService.UslugaComplexMedService_id,
				userClearTimeMS: option.userClearTimeMS || Ext.emptyFn
			});
		} else if (typeof option.Resource == 'object') {
			var win = getWnd('swTTRScheduleRecordWindow');
			if (win.isVisible()) {
				win.hide();
			}
			win.show({
				disableRecord: true,
				MedService_id: option.Resource.MedService_id,
				MedServiceType_id: option.Resource.MedServiceType_id,
				MedService_Nick: option.Resource.MedService_Nick,
				MedService_Name: option.Resource.MedService_Name,
				MedServiceType_SysNick: option.Resource.MedServiceType_SysNick,
				UslugaComplexMedService_id: option.Resource.UslugaComplexMedService_id,
				Resource_id: option.Resource.Resource_id,
				Resource_Name: option.Resource.Resource_Name,
				Lpu_did: option.Resource.Lpu_id,
				date: option.Resource.date,
				callback: option.callback,
				userClearTimeR: option.userClearTimeR || Ext.emptyFn
			});
		}
	},
	getPrescrTypeSysNick: function(PrescriptionType_Code) {
		var prescr_code = '';
		switch(PrescriptionType_Code.toString()) {
			case '1': prescr_code = 'EvnPrescrRegime'; break;
			case '2': prescr_code = 'EvnPrescrDiet'; break;
			case '3': prescr_code = 'EvnPrescrDiag'; break;
			case '4': prescr_code = 'EvnPrescrCons'; break;
			case '5': prescr_code = 'EvnPrescrTreat'; break;
			case '6': prescr_code = 'EvnPrescrProc'; break;
			case '7': prescr_code = 'EvnPrescrOperBlock'; break;
			case '10': prescr_code = 'EvnPrescrObserv'; break;
			case '11': prescr_code = 'EvnPrescrLabDiag'; break;
			case '12': prescr_code = 'EvnPrescrFuncDiag'; break;
			case '13': prescr_code = 'EvnPrescrConsUsluga'; break;
		}
		return prescr_code;
	},
	openEvnCourseEditWindow: function(option) {
		var win_name,
			formParams;
		var params = {};
		params.callback = function(ndata) {
			if ( typeof option.callbackEditWindow == 'function' ) {
				option.callbackEditWindow(ndata);
			}
		}.createDelegate(this);
		params.onHide = function(ndata) {
			if ( typeof option.onHideEditWindow == 'function' ) {
				option.onHideEditWindow(ndata);
			}
		}.createDelegate(this);
		params.parentEvnClass_SysNick = option.parentEvnClass_SysNick;
		params.action = option.action || 'add';
		//log(option);
		option.userMedStaffFact = option.userMedStaffFact || {};
        var Morbus_id = !Ext.isEmpty(option.data.Morbus_id) ? option.data.Morbus_id : null;
		switch(option.PrescriptionType_Code.toString()) {
            case '5':
				win_name = 'swEvnCourseTreatEditWindow';
				formParams = {
					EvnCourseTreat_pid: option.data.Evn_pid
					,EvnCourseTreat_id: option.data.EvnCourse_id || null
					,EvnCourseTreat_setDate: Date.parseDate((option.data.begDate || getGlobalOptions().date), 'd.m.Y')//option.data.begDate
					,PersonEvn_id: option.data.PersonEvn_id || null
					,Person_id: option.data.Person_id || null
					,LpuSection_id:  option.data.LpuSection_id || null
					,Server_id: option.data.Server_id || null
					,MedPersonal_id: option.data.MedPersonal_id || option.userMedStaffFact.MedPersonal_id || null
					,LpuSection_id: option.data.LpuSection_id || option.userMedStaffFact.LpuSection_id || null
                    ,Morbus_id: Morbus_id//!Ext.isEmpty(option.data.Morbus_id) ? option.data.Morbus_id : null//option.data.Morbus_id || null
                    ,action: params.action
				};
				params.UserLpuSection_id = !Ext.isEmpty(option.UserLpuSection_id) ? option.UserLpuSection_id : null;
				params.UserLpuUnitType_id = !Ext.isEmpty(option.UserLpuUnitType_id) ? option.UserLpuUnitType_id : null;
				params.userMedStaffFact = option.userMedStaffFact;
				params.parentWin = !Ext.isEmpty(option.parentWin) ? option.parentWin : null;
				params.Diag_id = !Ext.isEmpty(option.data.Diag_id) ? option.data.Diag_id : null;
				if(option.action == 'edit'){
					if ( typeof option.callbackEditWindow == 'function' ) {
						params.callback = option.callbackEditWindow;
					}
				}
				break;
			case '6':
				win_name = 'swPolkaEvnPrescrProcEditWindow';
				formParams = {
					EvnCourseProc_pid: option.data.Evn_pid
					,EvnCourseProc_id: option.data.EvnCourse_id || null
					,EvnCourseProc_setDate: Date.parseDate((option.data.begDate || getGlobalOptions().date), 'd.m.Y')//option.data.begDate
					,PersonEvn_id: option.data.PersonEvn_id || null
					,Server_id: option.data.Server_id || null
					,MedPersonal_id: option.data.MedPersonal_id || option.userMedStaffFact.MedPersonal_id || null
					,LpuSection_id: option.data.LpuSection_id || option.userMedStaffFact.LpuSection_id || null
					,Morbus_id: Morbus_id//option.data.Morbus_id || null
				};
				break;
		}
		if(!win_name || !formParams) {
			log('Undefined parameters for selected PrescriptionType_Code');
			return false;
		}

		params.formParams = formParams;
		getWnd(win_name).show(params);
		return true;
	},
	openEditWindow: function(option) {
		var win_name,
			formParams;
		var params = {};
		params.callback = function(ndata) {
			if ( typeof option.callbackEditWindow == 'function' ) {
				option.callbackEditWindow(ndata);
			}
		}.createDelegate(this);
		params.onHide = function(ndata) {
			if ( typeof option.onHideEditWindow == 'function' ) {
				option.onHideEditWindow(ndata);
			}
		}.createDelegate(this);
		params.parentEvnClass_SysNick = option.parentEvnClass_SysNick;
		params.action = option.action || 'add';
		params.changedType = option.changedType;
		//log(option);
		switch(option.PrescriptionType_Code.toString()) {
			case '1':
			case '2':
				if(params.action == 'add') {
					formParams = {
						EvnPrescr_pid: option.data.Evn_pid
						,EvnPrescr_id : option.EvnPrescr_id
						,Person_id: option.data.Person_id
						,PersonEvn_id: option.data.PersonEvn_id
						,Server_id: option.data.Server_id
						,EvnPrescr_setDate: option.data.begDate
					};
					switch ( option.PrescriptionType_Code.toString() ) {
						case '1':win_name = 'swPolkaEvnPrescrRegimeEditWindow';
							formParams.PrescriptionRegimeType_id = option.Prescr_type;
							break;
						case '2':win_name = 'swPolkaEvnPrescrDietEditWindow';
							formParams.PrescriptionDietType_id = option.Prescr_type;
							break;
					}
					params.begDate = option.data.begDate;
					params.callback = function(ndata) {
						this.hide();
						if ( typeof option.callbackEditWindow == 'function' ) {
							option.callbackEditWindow(ndata);
						}
					};
				} else {
					switch ( option.PrescriptionType_Code.toString() ) {
						case '1':win_name = 'swPolkaEvnPrescrRegimeEditWindow';break;
						case '2':win_name = 'swPolkaEvnPrescrDietEditWindow';break;
						case '10':win_name = 'swPolkaEvnPrescrObservEditWindow';break;
					}
					formParams = option.data;
				}
				break;
			case '10':
				//if(option.parentEvnClass_SysNick == 'EvnSection' ) {
					if(params.action == 'add') {
						switch ( option.PrescriptionType_Code.toString() ) {
							case '1':win_name = 'swPolkaEvnPrescrRegimeEditWindow';break;
							case '2':win_name = 'swPolkaEvnPrescrDietEditWindow';break;
							case '10':win_name = 'swPolkaEvnPrescrObservEditWindow';break;
						}
						formParams = {
							EvnPrescr_pid: option.data.Evn_pid
							,Person_id: option.data.Person_id
							,PersonEvn_id: option.data.PersonEvn_id
							,Server_id: option.data.Server_id
							,EvnPrescr_setDate: option.data.begDate
							,newEvnPrescr_id: option.newEvnPrescr_id||(-1)
						};
						params.begDate = option.data.begDate;
						params.callback = function(ndata) {
							this.hide();
							if ( typeof option.callbackEditWindow == 'function' ) {
								option.callbackEditWindow(ndata);
							}
						};
					} else {
						switch ( option.PrescriptionType_Code.toString() ) {
							case '1':win_name = 'swPolkaEvnPrescrRegimeEditWindow';break;
							case '2':win_name = 'swPolkaEvnPrescrDietEditWindow';break;
							case '10':win_name = 'swPolkaEvnPrescrObservEditWindow';break;
						}
						formParams = option.data;
					}
					//'5','6','7','11','12' как в поликлинике
				//}
				break;
			case '4':
				win_name = 'swPolkaEvnPrescrConsEditWindow';
				formParams = {
					EvnPrescrCons_id: option.data.EvnPrescr_id || null
					,EvnPrescrCons_pid: option.data.Evn_pid
					,EvnPrescrCons_setDate: Date.parseDate((option.data.begDate || getGlobalOptions().date), 'd.m.Y')//option.data.begDate
					,PersonEvn_id: option.data.PersonEvn_id || null
					,Server_id: option.data.Server_id || null
				};
				break;
			case '5':
				win_name = 'swEvnPrescrTreatEditWindow';
				params.LpuSection_id = option.userMedStaffFact.LpuSection_id || null;
				formParams = {
					EvnPrescrTreat_pid: option.data.Evn_pid
					,EvnPrescrTreat_id: option.data.EvnPrescr_id || null
					,EvnPrescrTreat_setDate: Date.parseDate((option.data.begDate || getGlobalOptions().date), 'd.m.Y')//option.data.begDate
					,PersonEvn_id: option.data.PersonEvn_id || null
					,Server_id: option.data.Server_id || null
					,EvnCourse_id: option.data.EvnCourse_id || null
				};
				break;
			/*case '6':
				win_name = 'swPolkaEvnPrescrProcEditWindow';
				formParams = {
					EvnPrescrProc_pid: option.data.Evn_pid
					,EvnPrescrProc_id: option.data.EvnPrescr_id || null
					,EvnPrescrProc_setDate: Date.parseDate((option.data.begDate || getGlobalOptions().date), 'd.m.Y')//option.data.begDate
					,PersonEvn_id: option.data.PersonEvn_id || null
					,Server_id: option.data.Server_id || null
				};
				break;*/
			case '7':
				win_name = 'swPolkaEvnPrescrOperEditWindow';
				formParams = {
					EvnPrescrOper_pid: option.data.Evn_pid
					,EvnPrescrOper_id: option.data.EvnPrescr_id || null
					,EvnPrescrOper_setDate: Date.parseDate((option.data.begDate || getGlobalOptions().date), 'd.m.Y')//option.data.begDate
					,PersonEvn_id: option.data.PersonEvn_id || null
					,Server_id: option.data.Server_id || null
				};
				break;
			case '11':
				win_name = 'swPolkaEvnPrescrLabDiagEditWindow';
				formParams = {
					EvnPrescrLabDiag_pid: option.data.Evn_pid
					,EvnPrescrLabDiag_id: option.data.EvnPrescr_id || null
					,EvnPrescrLabDiag_setDate: Date.parseDate((option.data.begDate || getGlobalOptions().date), 'd.m.Y')//option.data.begDate
					,PersonEvn_id: option.data.PersonEvn_id || null
					,Server_id: option.data.Server_id || null
				};
				break;
			case '12':
				win_name = 'swPolkaEvnPrescrFunDiagEditWindow';
				formParams = {
					EvnPrescrFuncDiag_pid: option.data.Evn_pid
					,EvnPrescrFuncDiag_id: option.data.EvnPrescr_id || null
					,EvnPrescrFuncDiag_setDate: Date.parseDate((option.data.begDate || getGlobalOptions().date), 'd.m.Y')//option.data.begDate
					,PersonEvn_id: option.data.PersonEvn_id || null
					,Server_id: option.data.Server_id || null
				};
				break;
			case '13':
				win_name = 'swPolkaEvnPrescrConsUslugaEditWindow';
				formParams = {
					EvnPrescrConsUsluga_pid: option.data.Evn_pid
					,EvnPrescrConsUsluga_id: option.data.EvnPrescr_id || null
					,EvnPrescrConsUsluga_setDate: Date.parseDate((option.data.begDate || getGlobalOptions().date), 'd.m.Y')//option.data.begDate
					,PersonEvn_id: option.data.PersonEvn_id || null
					,Server_id: option.data.Server_id || null
				};
				break;
		}
		//BOB - 22.04.2019
		if (option.parentWindow_id)
			params.parentWindow_id = option.parentWindow_id;
		//BOB - 22.04.2019

		if (/*IS_DEBUG == 1 &&*/ params.action != 'add' && option.PrescriptionType_Code.toString().inlist(['6','7','11','12','13','14'])) {
			win_name = 'swEvnPrescrUslugaEditWindow';
			formParams = {
				EvnPrescr_pid: option.data.Evn_pid
				,EvnPrescr_id: option.data.EvnPrescr_id || null
				,EvnPrescr_setDate: Date.parseDate((option.data.begDate || getGlobalOptions().date), 'd.m.Y')//option.data.begDate
				,PersonEvn_id: option.data.PersonEvn_id || null
				,Server_id: option.data.Server_id || null
				,EvnCourse_id: option.data.EvnCourse_id || null
			};
			params.PrescriptionType_id = option.PrescriptionType_id;
			params.PrescriptionType_Code = option.PrescriptionType_Code;
			params.userMedStaffFact = option.userMedStaffFact;
			params.Person_id = option.data.Person_id;
			params.Person_Firname = option.data.Person_Firname;
			params.Person_Surname = option.data.Person_Surname || null;
			params.Person_Secname = option.data.Person_Secname || null;
			params.Person_Birthday = option.data.Person_Birthday || null;
			params.Diag_id = option.data.Diag_id || null;
		}
		if (/*IS_DEBUG == 1 &&*/ params.action == 'add' && option.PrescriptionType_Code.toString().inlist(['6','7','11','12','13','14'])) {
			win_name = 'swEvnPrescrUslugaInputWindow';
			formParams = {
				Evn_pid: option.data.Evn_pid
				,DopDispInfoConsent_id: option.data.DopDispInfoConsent_id
				,SurveyTypeLink_lid: option.data.SurveyTypeLink_lid
				,EvnPLDisp_id: option.data.EvnPLDisp_id
				,parentEvnClass_SysNick: option.parentEvnClass_SysNick
				,PrescriptionType_id: option.PrescriptionType_id
				,PersonEvn_id: option.data.PersonEvn_id
				,Person_id: option.data.Person_id
				,Server_id: option.data.Server_id
				,EvnPrescr_setDate: option.data.begDate
				,Diag_id: option.data.Diag_id
				,TreatmentClass_id: option.data.TreatmentClass_id
				,PayTypeKAZ_id: option.data.PayTypeKAZ_id
			};

			params.PrescriptionType_Code = option.PrescriptionType_Code;
			params.userMedStaffFact = option.userMedStaffFact;
			params.Person_Firname = option.data.Person_Firname;
			params.Person_Surname = option.data.Person_Surname || null;
			params.Person_Secname = option.data.Person_Secname || null;
			params.Person_Age = option.data.Person_Age || null;
			params.Diag_Code = option.data.Diag_Code || null;
			params.Diag_Name = option.data.Diag_Name || null;
			params.IsCito = option.data.IsCito || null;
			params.isPaidVisit = option.isPaidVisit || null;
			params.electronicQueueData = option.data.electronicQueueData;

			if(option.PrescriptionType_Code.toString().inlist(['11','12','13'])){
				formParams.EvnPrescr_setDate = Date.parseDate(getGlobalOptions().date , 'd.m.Y');
			}
		}
		if(!win_name || !formParams) {
			log('Undefined parameters for selected PrescriptionType_Code');
			return false;
		}
		params.formParams = formParams;
		getWnd(win_name).show(params);
		return true;
	}
};
/**
 * Функция отправляет данные на сервер и получает данные обратно
 */
function loadStores(components, callback, owner) {
	var o = {};
	if (Ext.isRemoteDB) {
		var needLoad = false;
		// Проверяем наличие у компонентов Store
		// Пробегаемся по компонентам и собираем запросы
		for (var i in components) {
			if (components[i].getStore) {
				var s = components[i].getStore();
				if (s.tableName) {
					var p = s.params || ((components[i].loadParams && components[i].loadParams.params) ? components[i].loadParams.params : null); // Стандартные параметры или параметры для чтения
					if (!sw.localStorage.load(s, p)) {
						o[s.tableName] = {'url': s.url, 'baseparams': s.baseParams, 'params': p};
						needLoad = true;
					}
				}
			}
		}

		if (needLoad) {
			// Отправляем весь запрос, получаем обратно ответ
			Ext.Ajax.request({
				url: '/?c=MongoDBWork&m=getDataAll',
				params: {'data': Ext.util.JSON.encode(o)},
				failure: function(response, options) {
					if (callback && typeof callback == 'function') {
						callback(response, false, owner);
						return;
					}
				},
				success: function(response, options) {
					var result = null;
					if (response && typeof response.responseText == 'string' && response.responseText.length > 0) {
						result = Ext.util.JSON.decode(response.responseText);
					}
					if (result) {
						// Разбираем ответ по сторе
						for (var i in components) {
							if (components[i].getStore) {
								var s = components[i].getStore();
								if (s.tableName && s.getCount() == 0) {
									var tableName = s.tableName;
									if (result[tableName]) {
										var p = s.params || ((components[i].loadParams && components[i].loadParams.params) ? components[i].loadParams.params : null);
										sw.localStorage.save(s, p, result[tableName]);
										s.loadData(result[tableName], false);
									}
								}
							}
						}
					}
					// Формируем в менеджере сторе информацию о загруженных данных
					if (callback && typeof callback == 'function') {
						callback(result, true, owner);
					}
				}
			});
		} else {
			if (callback && typeof callback == 'function') {
				callback({}, true, owner);
			}
		}
	} else { // для локальных хранилищ просто пробегаемся по компонентам
		if (components.length < 1) {
			if (callback && typeof callback == 'function') {
				callback({}, true, owner);
			}
		} else {
			var component = components.shift();
			if (component.getStore && (component.getStore().getCount()==0)) {
				var s = component.getStore(); /* 'name': s.tableName, */
				var p = s.params || ((component.loadParams && component.loadParams.params)?component.loadParams.params: null); // Стандартные параметры или параметры для чтения
				component.getStore().load({params: p, callback:function (r,o,s) {
					if (component.getStore().getCount() == 0) {
						warn('Пустой справочник ',r);
					}
					loadStores(components, callback, owner);
				}});
			} else {
				loadStores(components, callback, owner);
			}
		}
	}
};

/** Функция отправляет данные на сервер и получает данные обратно
 *
 */
function saveLog(log, callback) {
	var o = {};
	// Отправляем весь запрос, получаем обратно ответ
	Ext.Ajax.request({
		url: '/?c=Utils&m=saveLog',
		params: {'log': log},
		failure: function(response, options) {
			if (callback && typeof callback == 'function') {
				callback(response, false);
				return;
			}
		},
		success: function(response, options) {
			var result = null;
			if (response && typeof response.responseText == 'string' && response.responseText.length > 0) {
				result = Ext.util.JSON.decode(response.responseText);
			}
			if (result) {
				// Формируем в менеджере сторе информацию о загруженных данных
				if (callback && typeof callback == 'function') {
					callback(result, true);
				}
			} else {
				if (callback && typeof callback == 'function') {
					callback(result, false);
				}
			}
		}
	});
};

function swSetMaxMenuHeight(m, maxHeight) {
	m.el.setHeight('auto');
	m.el.applyStyles('overflow:visible;');

	if (m.el.getHeight() > maxHeight) {
		m.el.setHeight(maxHeight);
		m.el.applyStyles('overflow:auto;');
	}
};

function getRegionNick() {
	if ( getGlobalOptions().region && getGlobalOptions().region.nick ) {
		return getGlobalOptions().region.nick;
	}

	return '';
}

function getPayTypeSysNickOms()
{
	var PayType_SysNick = 'oms';
	switch ( getRegionNick() ) {
		case 'by': PayType_SysNick = 'besus'; break;
		case 'kz': PayType_SysNick = 'Resp'; break;
	}
	return PayType_SysNick;
}

function getCurrencyType() {

	switch ( getRegionNick() ) {
		case 'kz': {
			return 'тенге';
		}
		default: {
			return 'руб.'
		}
	}
}
/**
 * Проверяет что строка является числом
 */
function filterFloat(value) {
	if(/^(\-|\+)?([0-9]+(\.[0-9]+)?|Infinity)$/
	  .test(value))
	  return value;
  return '';
}

function getRegionNumber() {
	if ( getGlobalOptions().region && getGlobalOptions().region.number ) {
		return getGlobalOptions().region.number;
	}

	return '';
}

function getLabController() {
	var labcontroller = 'AsMlo';
	return labcontroller;
}

function getWndRoles() {
	if ( getGlobalOptions().wndroles ) {
		return getGlobalOptions().wndroles;
	}

	return {};
}

function getMESAlias() {
	var result = 'МЭС';
	return result;
}

function isRegisterAutoInclude(register_type) {
	return (getGlobalOptions()['register_'+register_type+'_auto_include'] == 1);
}

sw.Promed.viewHtmlForm = {
	ownerWindow: null,
	Tree: null,
	actionListDblClickBySection:{},
	actionListClickBySection:{},
	actionNameList_View:[],
	actionNameList_Add:[],
	actionNameList_Edit:[],
	actionNameList_Del:[],
	emptyValue: '<span style="color: #666;">Не указано</span>',
	Field: function(cfg) {
		log(cfg);
		var me = this,
			sectionName = cfg.sectionName,
			name = cfg.name,
			objectId = cfg.objectId,
			wrapEl = null,
			outputEl = null,
			callback = cfg.callback||null,
			inputEl = null,
			value = null,
			rawValue = sw.Promed.viewHtmlForm.emptyValue,
			disabled = cfg.disabled || false,
			store = cfg.store || null,
			storeIsLoad = false,
			valueField = cfg.valueField || null,
			codeField = cfg.codeField || null,
			displayField = cfg.displayField || null;
		me.getId = function(el){
			if (!el) {
				el = '';
			}
			return sectionName + '_' + objectId + '_' + el + name;
		};

		function init(){
			wrapEl = Ext.get(me.getId('wrap'));
			outputEl = Ext.get(me.getId('input'));
			if (!wrapEl || !outputEl) {
				log('undefined wrap or output element');
				return false;
			}
			if (!disabled) {
				inputEl = Ext.get(me.getId('inputarea'));
				if (!inputEl) {
					log('undefined input element');
					return false;
				}
			}
			if (store && (!valueField || !displayField)) {
				log('undefined value or display field');
				return false;
			}
			return true;
		}

		me.setContainerVisible = function(isVisible){
			if (me.rendered && wrapEl) {
				if (isVisible) wrapEl.setDisplayed('block');
				else wrapEl.setDisplayed('none');
			}
		};
		me.getStore = function(){
			return store;
		};
		me.hideContainer = function(){
			me.setContainerVisible(false);
		};
		me.showContainer = function(){
			me.setContainerVisible(true);
		};
		me.setAllowBlank = function(isAllow){};
		me.setValue = function(newValue){
			if (store) {
				var idx = store.findBy(function(rec) {
					return (rec.get(valueField) == newValue);
				});
				var record = store.getAt(idx);
				if (record) {
					value = record.get(valueField);
					if (codeField) {
						rawValue = record.get(codeField) + '&nbsp;' + record.get(displayField);
					} else {
						rawValue = record.get(displayField);
					}
				} else {
					value = null;
					rawValue = sw.Promed.viewHtmlForm.emptyValue;
				}
			} else {
				value = newValue;
				if (value !== null) {
					rawValue = value;
				} else {
					rawValue = sw.Promed.viewHtmlForm.emptyValue;
				}
			}
			if (me.rendered && outputEl) {
				outputEl.update(rawValue);
				if(callback!=null){
					callback(value);
				}
			}
		};
		me.clearValue = function(){
			me.setValue(null);
		};
		me.getFieldValue = function(fieldName){
			if (store && value > 0) {
				var idx = store.findBy(function(rec) {
					return (rec.get(valueField) == value);
				});
				var record = store.getAt(idx);
				if (record) {
					return record.get(fieldName);
				} else {
					return null;
				}
			}
			return value;
		};
		me.setFieldValue = function(fieldName, fieldValue){
			if (store) {
				if (store.getCount() == 0 && false == storeIsLoad) {
					store.load({
						callback: function() {
							me.setFieldValue(fieldName, fieldValue);
						}
					});
					return true;
				}
				var idx = store.findBy(function(rec) {
					return (rec.get(fieldName) == fieldValue);
				});
				var record = store.getAt(idx);
				if (record) {
					me.setValue(record.get(valueField));
				} else {
					me.clearValue();
				}
			} else {
				me.setValue(fieldValue);
			}
			return true;
		};
		me.getRawValue = function(){
			return rawValue;
		};
		me.getValue = function(){
			return value;
		};
		me.rendered = init();
		return me;
	},
	/**
	* Создает массивы экшенов для элементов управления формы просмотра в соответствии со списком map и configActions
	*/
	createActionListForTpl:function (map)
	{
		if (typeof(map) != 'object')
		{
			return false;
		}
		var object_code, obj, action_name, action_obj;
		for (object_code in map)
		{
			if (typeof(this.configActions[object_code]) != 'object' || typeof(map[object_code]) != 'object')
			{
				continue;
			}
			obj = this.configActions[object_code];
			for (action_name in obj)
			{
				if (typeof(obj[action_name]) != 'object' || !obj[action_name].sectionCode || !obj[action_name].handler || (typeof obj[action_name].handler != 'function'))
				{
					continue;
				}
				action_obj = obj[action_name];
				if (action_obj.actionType)
				{
					switch(action_obj.actionType)
					{
						case 'view':this.actionNameList_View.push(action_name);break;
						case 'add':this.actionNameList_Add.push(action_name);break;
						case 'edit':this.actionNameList_Edit.push(action_name);break;
						case 'del':this.actionNameList_Del.push(action_name);break;
					}
				}
				if (action_obj.dblClick)
				{
					this.actionListDblClickBySection[action_obj.sectionCode] = action_obj.handler;
				}
				if(typeof(this.actionListClickBySection[action_obj.sectionCode]) != 'object')
				{
					this.actionListClickBySection[action_obj.sectionCode] = {};
				}
				this.actionListClickBySection[action_obj.sectionCode][action_name] = action_obj.handler;
			}
			if (map[object_code] && map[object_code].item && Ext.isArray(map[object_code].item))
			{
				item_arr = map[object_code].item;
				for(i=0; i < item_arr.length; i++)
				{
					if (item_arr[i].children)
					{
						this.createActionListForTpl(item_arr[i].children);
					}
				}
			}
		}
		return true;
	},
	/**
	* Устанавливает обработчики на элементы управления секции формы просмотра с идентификатором code +'_'+ id
	*/
	addHandlerForObject: function (code, id, is_readonly)
	{
		// id секции должны быть в формате: EvnVizitPL_data_21374
		var section_id = code +'_'+ id,
			s = Ext.get(section_id);
		if (!s)
		{
			log('Section '+ section_id +' NOT found');
			return false;
		}
		//log('Section '+ section_id +' found');
		var section_action = this.actionListClickBySection[code];
		if (section_action)
		{
			var el;
			var params = {object:code, object_id:id, section_id: section_id};
			for(action in section_action)
			{
				// id элементов управления должны быть в формате: EvnVizitPL_protocol_21374_edit
				el = Ext.get(section_id +'_'+ action);
				//log(section_id +'_'+ action);
				if (el)
				{
					// content_id = идентификатор содержимого секции, которое нужно обновлять после редактирования
					if(is_readonly)
					{
						if (action.inlist(this.actionNameList_View))
						{
							el.on('click', section_action[action],s,params);
						}
						else
						{
							el.hide();
						}
					}
					else
					{
						if (action.inlist(this.actionNameList_Edit))
						{
							params.content_id = section_id +'_content';
						}
						el.on('click', section_action[action],s,params);
					}
				}
				/*
				else
				{
					log('action By Element '+ section_id +'_'+ action +' not found');
				}
				*/
			}
			if(this.actionListDblClickBySection[code] && (!is_readonly))
			{
				s.on('dblclick', this.actionListDblClickBySection[code],s,params);
			}
			return true;
		}
		else
		{
			//log('action By Section '+ section_id +' not found');
			// log(this.actionListClickBySection);
			return false;
		}
	},
	/*
	* Навешивает обработчики на элементы управления формы просмотра в соответствии со списком map
	*/
	addHandlerInTpl: function (map, pid, readonly)
	{
		//log(map);
		//log(pid);
		if (typeof(map) != 'object')
		{
			return false;
		}
		var o='', b,i,j, code, id, id2, ss_arr, ro_arr = [], ro_id, item_arr, parent_id, node, data,
			is_readonly;
		for (o in map)
		{
			if (typeof(map[o]) != 'object')
			{
				continue;
			}
			is_readonly = false;
			if(readonly)
			{
				is_readonly = true;
			}
			ss_arr = null;
			if (Ext.isArray(map[o].subsection))
			{
				ss_arr = map[o].subsection;
			}
			ro_arr = [];
			if (Ext.isArray(map[o].related_objects))
			{
				ro_arr = map[o].related_objects;
			}
			if (map[o] && map[o].parent_value)
			{
				//log('For section-parent_value: '+ o +'_'+ map[o].parent_value);
				this.addHandlerForObject(o,map[o].parent_value,is_readonly);
			}
			if (map[o] && map[o].item && Ext.isArray(map[o].item))
			{
				item_arr = map[o].item; //parent_value
				code = o;
				for(i=0; i < item_arr.length; i++)
				{
					id = item_arr[i][map[o].object_key];

					if (id)
					{
						//получаем тип доступа из ноды
						//node = this.Tree.getNodeById(o +'_'+ id);
						//if(node && node.attributes.accessType && node.attributes.accessType == 'view')
						//получаем тип доступа из map
						data = this.getObjectData(o,id);
						if(data && data.accessType && data.accessType == 'view')
						{
							is_readonly = true;
						}
						else
						{
							is_readonly = false;
						}

						if(item_arr[i].EvnXml_id && !is_readonly) {
							this.XmlDataProcessing({
								EvnXml_id: item_arr[i].EvnXml_id,
								XmlType_id: item_arr[i].XmlType_id,
								xml_data: item_arr[i].xml_data
							});
						}

						id2 = id;
						if(map[o].first_key && data[map[o].first_key]) {
							id2 = data[map[o].first_key] +'_'+ id;
						}

						this.addHandlerForObject(code,id2,is_readonly);
						if (Ext.isArray(ss_arr))
						{
							for(j=0; j < ss_arr.length; j++)
							{
								//log('For subsection: '+ ss_arr[j] +'_'+ id);
								this.addHandlerForObject(ss_arr[j].code,id,is_readonly);
								if (ss_arr[j].code == 'EvnVizitPL_protocol' && item_arr[i].emptyxmltemplate)
								{
									b = Ext.get(ss_arr[j].code +'_'+ id +'_edit');
									b.setStyle({display: 'none'});
									b = Ext.get(ss_arr[j].code +'_'+ id +'_del');
									b.setStyle({display: 'none'});
									b = Ext.get(ss_arr[j].code +'_'+ id +'_print');
									b.setStyle({display: 'none'});
								}
							}
						}
					}

					for(j=0; j < ro_arr.length; j++)
					{
						ro_id = item_arr[i].data[ro_arr[j].field_code] +'_'+ item_arr[i].data[ro_arr[j].field_key];
						this.addHandlerForObject(ro_arr[j].field_code,ro_id,is_readonly);
					}

					if (item_arr[i].children && id)
					{
						this.addHandlerInTpl(item_arr[i].children, id, is_readonly);
					}
				}
			}
			if (map[o] && typeof(map[o].list) == 'string' && pid)
			{
				id2 = pid;
				if(map[o].first_key && map[o].parent_object) {
					data = this.getObjectData(map[o].parent_object,pid);
					if(data) {
						id2 = data[map[o].first_key] +'_'+ pid;
					}
				}
				//log('For section-list: '+ map[o].list +'_'+ id2);
				this.addHandlerForObject(map[o].list,id2,is_readonly);
			}
		}
		return true;
	},
	/**
	* При загрузке в панель просмотра создаются экземпляры компонента, в котором отображается редактируемый текст
	*/
	XmlDataProcessing: function (options)
	{
		if(!options || typeof(options.xml_data) != 'object' || !options.XmlType_id || !options.XmlType_id.inlist([2,3]))
			return false;

		var isNewRedactor = (getGlobalOptions().medpersonal_id == '41'); //getGlobalOptions().pmuser_id == '470379905'

		if(isNewRedactor) {
			var editor = new nicEditor({
				iconsPath: '/nicedit/nicEditorIcons.gif'
				// full buttonList : ['save','bold','italic','underline','left','center','right','justify','ol','ul','fontSize','fontFamily','fontFormat','indent','outdent','image','upload','link','unlink','forecolor','bgcolor'],
				,buttonList: ['code','bold','italic','underline','left','center','right','fontSize','fontFamily','fontFormat','indent','outdent','forecolor','bgcolor','swclear','swdelete']
			});

			editor.addEvent('focus', function() {
				var instance = editor.selectedInstance;
				if(instance) {
					instance.lastValue = instance.getContent();
					var t = Ext.get('toolbar_'+ instance.field.name +'_'+ instance.field.EvnXml_id);
					if(t) {
						t.setVisibilityMode(Ext.Element.DISPLAY);
						t.setVisible(true);
					}
				}
			});
			editor.addEvent('blur', function() {
				var instance = editor.selectedInstance;
				if(instance && instance.lastValue && instance.lastValue != instance.getContent()) {
					instance.saveValue();
				}
				if(instance) {
					var t = Ext.get('toolbar_'+ instance.field.name +'_'+ instance.field.EvnXml_id);
					if(t) {
						t.setVisibilityMode(Ext.Element.DISPLAY);
						t.setVisible(false);
					}
				}
			});
			/*
			editor.addEvent('key', function() {
				log('key');
				log(arguments);
			});
			*/

			sw.Promed.swNicEdit = function(config){
				if(!config || !config.EvnXml_id || !config.name || !config.renderToElement ) {
					return false;
				}
				config.value = config.value || '';

				this.config = config;

				var field_id = 'field_'+ config.name +'_'+ config.EvnXml_id;
				var toolbar_id = 'toolbar_'+ config.name +'_'+ config.EvnXml_id;
				this.config.renderToElement.update('<div id="'+ toolbar_id +'" style="width: 700px; display: none;"></div><textarea style="width: 700px; height: 30px;" id="'+ field_id +'" name="'+ config.name +'">'+ config.value +'</textarea>',false,function(){
					editor.addInstance(field_id);
					editor.setPanel(toolbar_id);
					var instance = editor.instanceById(field_id);
					if(instance) {
						instance.field = {
							name: config.name,
							id: field_id,
							EvnXml_id: config.EvnXml_id
						};
					}
				});
				return editor.instanceById(field_id);
			};
		} else {
			this.clearEvnXmlNode = function(data) {
				if(data.field) {
					data.field.setValue('');
					data.field.saveValue();
				} else {
					var el = Ext.get('data_'+ data.name +'_'+ data.EvnXml_id);
					if(el) {
						el.update('');
					}
				}
			}.createDelegate(this);
			this.deleteEvnXmlNode = function(data) {
				var f = Ext.getCmp('field_'+ data.name +'_'+ data.EvnXml_id);
				if(f) {
					f.destroy();
				}
				var el = Ext.get('block_'+ data.name +'_'+ data.EvnXml_id);
				if(el) {
					el.update('');
				}
				//this.getLoadMask().show();
				Ext.Ajax.request({
					url: '/?c=Template&m=deleteEvnXmlNode',
					callback: function(opt, success, response) {
						//this.getLoadMask().hide();
						if (success && response.responseText != '') {
							var response_obj = Ext.util.JSON.decode(response.responseText);
							if(response_obj.success) {
								//this.loadNodeViewForm(this.Tree.getNodeById(this.node.id));
							}
						}
					}.createDelegate(this),
					params: data
				});
			}.createDelegate(this);
		}

		var b,f,capt,el,c,h,s;
		for(o in options.xml_data) {
			if(typeof options.xml_data[o] != 'string')
				continue;
			b = Ext.get('data_'+ o +'_'+ options.EvnXml_id);
			//log(b);
			if(b) {
				if(isNewRedactor) {
					f = new sw.Promed.swNicEdit({
						EvnXml_id: options.EvnXml_id
						,value: options.xml_data[o]
						,name: o
						,renderToElement: b
					});
				} else {
					b.update('');
					f = new sw.Promed.swTextFieldEmk({
						EvnXml_id: options.EvnXml_id,
						value: options.xml_data[o],
						id: 'field_'+ o +'_'+ options.EvnXml_id,
						name: o,
						renderTo: 'data_'+ o +'_'+ options.EvnXml_id
					});
					capt = Ext.get('caption_'+ o +'_'+ options.EvnXml_id);
					if(capt){
						capt.addClass('caption');
						//capt.child('span').setStyle({'float': 'left', 'margin-right': '1em', 'height': '32px'});
						el = capt.insertHtml('afterEnd'
						,'<div id="toolbar_'+o +'_'+ options.EvnXml_id+'" style="display: none;" class="toolbar">'
						+'<a id="EvnXml_'+o +'_'+ options.EvnXml_id+'_delete" class="button icon icon-fielddelete16" title="Удалить раздел"><span> </span></a>'
						+'<a id="EvnXml_'+o +'_'+ options.EvnXml_id+'_clear" class="button icon icon-fieldclear16" title="Очистить раздел"><span> </span></a>'
						+'</div>'
						, true);

						el = Ext.get('block_'+ o +'_'+ options.EvnXml_id);
						if(el) {
							el.on('mouseout',function(event){if (isMouseLeaveOrEnter(event,this)) {Ext.get('toolbar_'+this.id.split('_')[1]+'_'+ options.EvnXml_id).setStyle('display','none');}});
							el.on('mouseover',function(event){if (isMouseLeaveOrEnter(event,this)) {Ext.get('toolbar_'+this.id.split('_')[1]+'_'+ options.EvnXml_id).setStyle('display','block');}});
						}

						el = Ext.get('EvnXml_'+o +'_'+ options.EvnXml_id+'_delete');
						if(el) {
							el.on('click', function(e,s,data){this.deleteEvnXmlNode(data);}.createDelegate(this),capt,{EvnXml_id:options.EvnXml_id, name: o});
						}

						el = Ext.get('EvnXml_'+o +'_'+ options.EvnXml_id+'_clear');
						if(el) {
							el.on('click', function(e,s,data){this.clearEvnXmlNode(data);}.createDelegate(this),capt,{field:f, EvnXml_id:options.EvnXml_id, name: o});
						}
					}
				}
			}
		}
	},
	map: null,
	/**
	* Store данных событий, отображаемых в панели просмотра
	* атрибуты записей:
	* object_code string
	* object_key string
	* object_value int
	* parent_object_code string
	* parent_object_key string
	* parent_object_value int
	* subsection array
	* list string
	* id string Имеет формат: object_code +'_'+ object_value
	* data
	*/
	viewFormDataStore: new Ext.data.SimpleStore(
	{
		autoLoad: true,
		fields:[],
		updateFromMap: function(map, parent)
		{
			this.completeFromMap(map, parent, true);
		},
		completeFromMap: function(map, parent, remove_existing)
		{
			//log(map);
			if (typeof(map) != 'object') return false;
			var object = {},list,subsection, record, item_arr, i,index;
			for (object.code in map)
			{
				//log(object.code);
				if (typeof(map[object.code]) == 'object' && map[object.code].item && Ext.isArray(map[object.code].item))
				{
					item_arr = map[object.code].item;
					object.key = map[object.code].object_key;
					list = map[object.code].list || null;
					subsection = map[object.code].subsection || null;
					//log(item_arr.length);
					for(i=0; i < item_arr.length; i++)
					{
						//log(item_arr[i].data);
						//item_arr[i].data._is_first = (i==0);
						//item_arr[i].data._is_last = (i==(item_arr.length-1));
						item_arr[i].data._item_count = item_arr.length;
						item_arr[i].data._item_index = i;
						record = new Ext.data.Record(item_arr[i].data);
						object.value = item_arr[i].data[object.key];
						if(record)
						{
							record.object_code = object.code;
							record.object_key = object.key;
							record.object_value = object.value;
							record.parent_object_code = (parent && parent.code) || null;
							record.parent_object_key = (parent && parent.key) || null;
							record.parent_object_value = (parent && parent.value) || null;
							record.subsection = subsection;
							record.list = list;
							record.id = object.code +'_'+ object.value;
							//log(record.id);
							if (remove_existing)
							{
								index = this.indexOfId(record.id);
								if(index)
								{
									this.removeAt(index);
								}
							}
							this.add(record);
						}
						if(item_arr[i].xml_data) {
							record = new Ext.data.Record(item_arr[i].xml_data);
							if(record)
							{
								record.object_code = 'EvnXml';
								record.object_key = 'EvnXml_id';
								record.object_value = item_arr[i].EvnXml_id;
								record.parent_object_code = object.code;
								record.parent_object_key = object.key;
								record.parent_object_value = object.value;
								record.XmlType_id = item_arr[i].XmlType_id;
								record.id = 'EvnXml_'+ object.value;
								if (remove_existing)
								{
									index = this.indexOfId(record.id);
									if(index)
									{
										this.removeAt(index);
									}
								}
								this.add(record);
							}
						}
						if (item_arr[i].children)
						{
							this.completeFromMap(item_arr[i].children, object,remove_existing);
						}
					}
				}
			}
		},
		data : []
	}),
	getItemObjectFromMap: function(data,object,object_id, map)
	{
		var o = '', i, id, sid = object +'_'+ object_id, item_arr, result;
		if (!map)
			map = this.map;
		if (typeof(map) != 'object')
		{
			return false;
		}
		for (o in map)
		{
			if (typeof(map[o]) != 'object')
			{
				continue;
			}
			if (map[o] && map[o].item && Ext.isArray(map[o].item))
			{
				item_arr = map[o].item;
				for(i=0; i < item_arr.length; i++)
				{
					id = o +'_'+ item_arr[i][o +'_id'];
					//log(id +'='+ sid);
					if (id == sid && typeof(item_arr[i][data]) == 'object')
					{
						return item_arr[i][data];
					}
					if (item_arr[i].children)
					{
						result = this.getItemObjectFromMap(data,object,object_id,item_arr[i].children);
						if (result)
						{
							return result;
						}
					}
				}
			}
		}
		return false;
	},
	getObjectData: function(object,object_id)
	{
		//return this.getItemObjectFromMap('data',object,object_id);
		var record = this.viewFormDataStore.getById(object +'_'+ object_id);
		if (record && record.data)
		{
			return record.data;
		}
		log('In viewFormDataStore not found record with id: '+ object +'_'+ object_id);
		log(this.viewFormDataStore);
		return false;
	},
	getObjectDataWithFindBy: function(search)
	{
		var index = this.viewFormDataStore.findBy(search);
		if (index == -1) {
			return false;
		}
		return this.viewFormDataStore.getAt(index).data;
	},
	getObjectChildren: function(object,object_id)
	{
		return this.getItemObjectFromMap('children',object,object_id);
	},
	/**
	*
	*/
	clearNodeViewForm: function ()
	{
		this.collapse();
		this.node = null;
		this.data = null;
		this.setTitle(' ');
		this.actionListDblClickBySection = {};
		this.actionListClickBySection = {};
		this.actionNameList_View = [];
		this.actionNameList_Add = [];
		this.actionNameList_Edit = [];
		this.actionNameList_Del = [];
		this.map = null;
		this.input_cmp_list = null;
		this.viewFormDataStore.removeAll();
		var tpl = new Ext.XTemplate(' ');
		tpl.overwrite(this.body, {});
	},
	/**
	 * Загружает форму отображения документа объекта
	 *
	 * @param {node} Нода дерева
	 */
	loadNodeViewForm: function (node, params)
	{
		if(!params) {
			params = {};
		}
		var form = this.ownerWindow;
		this.clearNodeViewForm();
		if(form.id == 'PersonEmkForm')
		{
			if (!this.isCorrectNode(node))
			{
				sw.swMsg.alert(langs('Ошибка'), langs('Ошибка загрузки формы просмотра. <br/>Не передана нода или нода имеет неправильные параметры.'));
				log(node);
				return false;
			}
			if (this.isForbiddenCode(node.attributes.object))
			{
				sw.swMsg.alert(langs('Сообщение'), langs('Для данного объекта отсутствует шаблон отображения'));
				return false;
			}

			if ((typeof form.Person_id == 'undefined') || (typeof form.PersonEvn_id == 'undefined') || (typeof form.Server_id == 'undefined') || (!form.Person_Surname) || (!form.Person_Birthday))
			{
				sw.swMsg.alert(langs('Ошибка'), langs('Ошибка загрузки формы просмотра. <br/>Не указаны параметры человека, необходимые для правильной работы формы просмотра.'));
				return false;
			}
		}
		var data_node = this.getDataNode(node);
		if (!data_node)
		{
			sw.swMsg.alert(langs('Ошибка'), langs('Ошибка загрузки формы просмотра. <br/>Не удалось получить параметры ноды.'));
			return false;
		}
		this.node = node;
		this.data = data_node;
		this.setTitle(this.data.Name);
		var parent_attr = (this.node.parentNode)?this.node.parentNode.attributes:false;
		this.loadNodeViewSection({
			Code: this.data.Code,
			object_key: this.data.key,
			object_value: this.data.id,
			param_name: params.param_name || null,
			param_value: params.param_value || null,
			parent_object_key: params.parent_object_key || null,
			parent_object_value: params.parent_object_value || null,
			EvnDiagPLStomSop_id: params.EvnDiagPLStomSop_id || null,
			EvnDiagPLSop_id: params.EvnDiagPLSop_id || null,
			MorbusOnkoVizitPLDop_id: params.MorbusOnkoVizitPLDop_id || null,
			MorbusOnkoLeave_id: params.MorbusOnkoLeave_id || null,
			MorbusOnkoDiagPLStom_id: params.MorbusOnkoDiagPLStom_id || null,
			accessType: this.data.accessType || params.accessType || null,
			Person_id: params.Person_id || 0,
			msg: langs('Пожалуйста, подождите, идет загрузка формы просмотра...'),
			onSuccess: function(form, html, map){
				//log(map);
				var tpl = new Ext.XTemplate(html);
				tpl.overwrite(this.body, {});
				this.createActionListForTpl(map);
				var parent = {};
				this.viewFormDataStore.completeFromMap(map,parent);
				this.map = map;
				var access_type = 'edit';
				var data = this.getObjectData(this.data.Code,this.data.id);
				if(data && data.accessType)
					access_type = data.accessType;
				if (this.data.key == 'Person_id')
				{
					this.addHandlerInTpl(map,this.data.id, (access_type == 'view'));
					parent.code = 'Person';
					parent.key = 'Person_id';
					parent.value = this.data.id;
				}
				else
				{
					this.addHandlerInTpl(map, null, (access_type == 'view'));
				}
				this.hidePrintOnly(this.data.Code +'_'+ this.data.id);
				this.addAutoLinksHandlers();
				var node_list = Ext.query("*[class*=allowed_hide_after_loading]",Ext.getDom(this.data.Code +'_'+ this.data.id));
				var i, el;
				for(i=0; i < node_list.length; i++)
				{
					el = new Ext.Element(node_list[i]);
					el.setStyle({display: 'none'});
				}

				this.expand();

				if(form.id == 'PersonEmkForm')
				{
					if (!form.Tree.getSelectionModel().isSelected(node))
					{
						form.Tree.getSelectionModel().select(node);
					}
					form.savePosition();
					form.saveFormViewState(node);
				}

				if(typeof(params.callback) == 'function') {
					params.callback();
				}
			}.createDelegate(this),
			onError: function(form){
				this.clearNodeViewForm();
				sw.swMsg.alert(langs('Ошибка'), langs('Ошибка загрузки формы просмотра. <br/>Возможно, документ был удален либо указан идентификатор несуществующего документа.'));
				if(form.id == 'PersonEmkForm')
				{
					if (!form.Tree.getSelectionModel().isSelected(node))
					{
						form.Tree.getSelectionModel().select(node);
					}
					form.savePosition();
				}
			}.createDelegate(this)
		},parent_attr);
	},
	loadNodeViewSection: function(option_obj,parentnode_attr)
	{
		var params = {
			user_MedStaffFact_id: (!Ext.isEmpty(this.ownerWindow.userMedStaffFact) && typeof this.ownerWindow.userMedStaffFact == 'object' ? this.ownerWindow.userMedStaffFact.MedStaffFact_id : null),
			scroll_value: null,
			object: option_obj.Code,
			object_id: option_obj.object_key,
			object_value: option_obj.object_value,
			EvnDiagPLStomSop_id: option_obj.EvnDiagPLStomSop_id,
			EvnDiagPLSop_id: option_obj.EvnDiagPLSop_id,
			MorbusOnkoVizitPLDop_id: option_obj.MorbusOnkoVizitPLDop_id,
			MorbusOnkoLeave_id: option_obj.MorbusOnkoLeave_id,
			MorbusOnkoDiagPLStom_id: option_obj.MorbusOnkoDiagPLStom_id
		};
		if(option_obj.Person_id ) {
			params.Person_id = option_obj.Person_id;
		}
		if(option_obj.is_reload_one_section)
		{
			params.is_reload_one_section = 1;
		}
		if(option_obj.parent_object_key)
		{
			params.parent_object_id = option_obj.parent_object_key;
		}
		if(option_obj.parent_object_value)
		{
			params.parent_object_value = option_obj.parent_object_value;
		}
		if(option_obj.param_name)
		{
			params.param_name = option_obj.param_name;
		}
		if(option_obj.param_value)
		{
			params.param_value = option_obj.param_value;
		}
		if(option_obj.accessType)
		{
			params.accessType = option_obj.accessType;
		}
		if(this.searchNodeObj) {
			params.scroll_value = params.object +'_'+ option_obj.object_value;
			this.searchNodeObj = null;
		}
		// Специфические параметры в зависимости от ноды
		switch ( params.object )
		{
			case 'PersonMorbusHepatitis':
			case 'Anthropometry':
				params.view_section = 'main';
			case 'AllergHistory':
			case 'PersonMedHistory':
			case 'BloodData':
			case 'ExpertHistory':
			case 'DiagList':
			case 'PersonSvidInfo':
			case 'PersonDispInfo':
			case 'SurgicalList':
				params.parent_object_value = option_obj.object_value;
				params.parent_object_id = 'Person_id';
				params.Person_id = option_obj.object_value;
			break;
			case 'EvnVizitPL':
				params.object_value = parentnode_attr.object_value;
				params.object = 'EvnPL';
				params.object_id = 'EvnPL_id';
				params.scroll_value = 'EvnVizitPL_head_' + option_obj.object_value;
			break;
			case 'EvnPL':
				params.scroll_value = 'EvnPL_' + option_obj.object_value;
			break;
			case 'EvnUslugaPar':
				params.view_section = 'main';
			break;
			case 'EvnRecept':
				params.object = 'EvnReceptView';
			break;
			case 'EvnReceptList':
				params.object = 'EvnRecept';
			break;
			case 'EvnReceptKardio':
				params.object = 'EvnReceptView';
			break;
			case 'EvnReceptKardioList':
				params.object = 'EvnReceptKardio';
			break;
			case 'FreeDocument':
				params.object = 'FreeDocumentView';
			break;
			case 'MorbusNephroLab':
			case 'MorbusNephroDisp':
			case 'MorbusNephroDrug':
			case 'NephroCommission':
			case 'NephroAccess':
			case 'NephroDocument':
			case 'NephroBloodCreatinine':
				params.isOnlyLast = option_obj.isOnlyLast || 0;
				break;
		}
		var form = this.ownerWindow;
		form.loadMask = form.getLoadMask(option_obj.msg || LOAD_WAIT);
		form.loadMask.show();
		Ext.Ajax.request({
			url: '/?c=Template&m=getEvnForm',
			callback: function(opt, success, response) {
				form.loadMask.hide();
				if (success && response.responseText != '')
				{
					var response_obj = Ext.util.JSON.decode(response.responseText);
					if ( response_obj.success )
					{
						if ( response_obj['html'] && response_obj['map'])
						{
							option_obj.onSuccess(form,response_obj.html,response_obj.map);
							if (params.scroll_value && Ext.fly(params.scroll_value)){
								document.getElementById(params.scroll_value).scrollIntoView();
								document.getElementById('main-center-panel').scrollIntoView();
							}
						}
						else
						{
							if (typeof option_obj.onError == 'function')
							{
								option_obj.onError(form);
							}
						}
					}
				}
			},
			params: params
		});
	},
	reloadSection: function(params)
	{
		this.loadNodeViewSection({
			is_reload_one_section: 1,
			Code: params.section_code,
			object_key: params.object_key,
			object_value: params.object_value,
			parent_object_key: params.parent_object_key || null,
			parent_object_value: params.parent_object_value || null,
			EvnDiagPLStomSop_id: params.EvnDiagPLStomSop_id || null,
			EvnDiagPLSop_id: params.EvnDiagPLSop_id || null,
			MorbusOnkoVizitPLDop_id: params.MorbusOnkoVizitPLDop_id || null,
			MorbusOnkoLeave_id: params.MorbusOnkoLeave_id || null,
			MorbusOnkoDiagPLStom_id: params.MorbusOnkoDiagPLStom_id || null,
			param_name: params.param_name || null,
			param_value: params.param_value || null,
			accessType: params.accessType || null,
			isOnlyLast: params.isOnlyLast || null,
			Person_id: params.Person_id || 0,
			msg: 'Пожалуйста, подождите, идет загрузка ...',//секции '+ params.section_id +'
			onSuccess: function(form, html, map){
				this.viewFormDataStore.updateFromMap(map, parent);
				if(params.allowCreateAction) {
					this.createActionListForTpl(map);
				}
				this.updateSection(params.section_id, params.section_code, params.object_value, html, map, params.parent_object_value);
				var parent = {
					code: null,
					key: params.parent_object_key || null,
					value: params.parent_object_value || null
				};
				if (typeof params.callback == 'function') {
					params.callback();
				}
			}.createDelegate(this),
			onError: function(form){
				//sw.swMsg.alert('Ошибка', 'Ошибка загрузки секции. <br/>Не найдена форма отображения.');
			}
		},false);
	},
	reloadViewForm: function(params)
	{
		if (this.node.id != params.section_id)
		{
			this.reloadSection(params);
		}
		else if (this.Tree)
		{
			this.loadNodeViewForm(this.Tree.getNodeById(this.node.id));
		}
	},
	/**
	* Нода, для которой, загружена форма просмотра,
	*/
	node: null,
	/**
	* Кэш параметров ноды, для которой, загружена форма просмотра,
	*/
	data: null,
	/**
	* Проверяет наличие параметров ноды перед загрузкой в форму просмотра
	*/
	isCorrectNode: function (node)
	{
		return (node && node.parentNode && node.getOwnerTree() && node.attributes && node.attributes.object && node.attributes.object_id && node.attributes.object_value);
	},
	isForbiddenCode: function (code)
	{
		return !code.inlist(this.codeListForLoad);
	},
	getDataNode: function (node)
	{
		var key = node.attributes.object_id;
		if (key.toLowerCase() == 'person_id')
		{
			key = 'Person_id';
		}
		return {
			accessType: node.attributes.accessType || '',
			Name: node.attributes.node_name || node.attributes.text,
			Code: node.attributes.object,
			key: key,
			id: node.attributes.object_value,
			level: (node.getDepth)?node.getDepth():null
		};
	},
	toggleDisplay: function(id, hide) {
		var el = Ext.get(id);
		if (el)
		{
			if(el.isDisplayed())
			{
				if (hide == true) el.setStyle({display: 'none'});
			}
			else
			{
				if (hide == false) el.setStyle({display: 'block'});
			}
		}
	},
	openPrintDoc: function(url)
	{
		window.open(url, '_blank');
	},
	printHtml: function(id)
	{
		var s = Ext.get(id);
		if (!s)
		{
			Ext.Msg.alert(langs('Сообщение'), langs('Секция ')+ id +langs(' не найдена.'));
			return false;
		}
		var id_salt = Math.random();
		var win_id = 'printEvent' + Math.floor(id_salt*10000);
		var win = window.open('', win_id);
		win.document.write('<html><head><title>Печатная форма</title><link href="/css/emk.css?'+ id_salt +'" rel="stylesheet" type="text/css" /></head><body id="rightEmkPanelPrint">'+ s.dom.innerHTML +'</body></html>');
		var i, el;
		// нужно показать скрытые области для печати
		var printonly_list = Ext.query("div[class=printonly]",win.document);
		for(i=0; i < printonly_list.length; i++)
		{
			el = new Ext.Element(printonly_list[i]);
			el.setStyle({display: 'block'});
		}
		// нужно скрыть элементы управления
		var tb_list = Ext.query("*[class*=section-toolbar]",win.document);
		tb_list = tb_list.concat(Ext.query("*[class*=sectionlist-toolbar]",win.document));
		tb_list = tb_list.concat(Ext.query("*[class*=item-toolbar]",win.document));
		//tb_list = tb_list.concat(Ext.query("*[class=section-button]",win.document));
		//log(tb_list);
		for(i=0; i < tb_list.length; i++)
		{
			el = new Ext.Element(tb_list[i]);
			el.setStyle({display: 'none'});
		}
		win.document.close();
		//win.print();
	},
	// добавляет обработчики клика на автоматически добавленные гиперссылки
	addAutoLinksHandlers: function() {
		var node_list;
		try{
			node_list = Ext.query("*[class*=showAllergHistory]",Ext.getDom(this.data.Code +'_'+ this.data.id));
			var i, el;
			for(i=0; i < node_list.length; i++)
			{
				el = new Ext.Element(node_list[i]);
				if(!el.hasClass('clickable'))
				{
					el.on('click', this.showAllergHistory,this);
					el.addClass('clickable');
				}
			}
		} catch (e) {
			//node_list = [];
		}
	},
	hidePrintOnly: function (rootnode_id)
	{
		//log('rootnode_id: '+ rootnode_id);
		var rootnode = Ext.getDom(rootnode_id);
		var node_list = Ext.query("div[class*=printonly]",rootnode);
		//log(node_list);
		var i, el;
		for(i=0; i < node_list.length; i++)
		{
			el = new Ext.Element(node_list[i]);
			//log(el);
			el.setStyle({display: 'none'});
		}
	},
	onLoadSection: function(section_id, section_code, id, map, pid) {
		this.hidePrintOnly(section_code +'_'+ id);
		this.addAutoLinksHandlers();
		var node_list = Ext.query("*[class*=allowed_hide_after_loading]",Ext.getDom(this.data.Code +'_'+ this.data.id));
		var i, el;
		for(i=0; i < node_list.length; i++)
		{
			el = new Ext.Element(node_list[i]);
			el.setStyle({display: 'none'});
		}

		if (section_id == (section_code +'_'+ id))
		{
			this.addHandlerForObject(section_code, id, false);
		}
		if (map && pid)
		{
			this.addHandlerInTpl(map, pid, false);
		}
	},
	updateSection: function(section_id, section_code, id, html, map, pid) {
		var el = Ext.get(section_id);
		if (el)
		{
			el.update(html, false);
			this.onLoadSection(section_id, section_code, id, map, pid);
		}
	},
	codeListForLoad: [],
	configActions: {},
	addConfigActions: function(config){
		Ext.apply(this.configActions,config);
	}
};
/**
 * Примесь к объекту класса потомка sw.Promed.BaseForm для отображения сообщения,
 * всплывающего в панели просмотра окна
 * @type {Object}
 * @example Примешивание осуществляется в initComponent так:
 * Ext.apply(this, sw.Promed.ViewPanelMsgMixin);
 * this.viewPanel = this.rightPanel;// Ссылка на панель просмотра
 * Перед загрузкой панели просмотра должен сбрасываться счетчик запросов
 * this.cntSaveRequests = 0;
 */
sw.Promed.ViewPanelMsgMixin = {
	viewPanel: null,
	_viewPanelInfoMsg: null,
	_viewPanelWarningMsg: null,
	cntSaveRequests: 0,
	/**
	 * Показывает алерт вверху панели просмотра
	 * Ссылки в тексте могут сразу вызывать соответствующие контролы или переводить фокус в нужное поле.
	 * @param {Object} option
	 * option.msg Текст подсказки required
	 * ption.links Список конфигов для ссылок
	 * @param {String} field Имя поля, ассоциированного с текстом ссылки (опционально)
	 * @param {String} link Текст ссылки (опционально)
	 * @param {Object} params Параметры для создания компонента редактирования поля (опционально)
	 * @return {Boolean}
	 */
	showWarningMsg: function (option, field, link, params)
	{
		var form = this;
		if (!option) {
			option = {};
		}
		if (!option.links) {
			option.links = {};
		}
		if (field && link && params) {
			option.links[field] = {
				link: link,
				onclick: function(e, node, msg) {
					form.createInputArea(field, params.object, params);
					msg.hide();
				}
			};
		}
		if (field && !option.msg) {
			option.msg = 'Поле '+ field +' обязательно для заполнения!'
		}
		if (!option.msg) {
			return false;
		}
		if (form._viewPanelWarningMsg) {
			form._viewPanelWarningMsg.destroy();
		}
		if (form._viewPanelInfoMsg) {
			form._viewPanelInfoMsg.destroy();
		}
		option.viewPanel = form.viewPanel;
		option.useCase = 'warning';
		option.hideDelay = 15000;
		option.onDestroy = function() {
			if (form._viewPanelWarningMsg) {
				delete form._viewPanelWarningMsg;
			}
		};
		form._viewPanelWarningMsg = new sw.Promed.ViewPanelMsg(option);
		form._viewPanelWarningMsg.show();
		return true;
	},
	/**
	 * Показывает информационную панель вверху панели просмотра ЭМК.
	 * Ссылки в тексте могут сразу вызывать соответствующие контролы или переводить фокус в нужное поле.
	 * @param {Object} option
	 * @param {Boolean} isLoading
	 * @return {Boolean}
	 */
	showInfoMsg: function (option, isLoading)
	{
		var form = this;
		if (!option) {
			option = {};
		}
		if (!option.msg) {
			if (isLoading) {
				option.useCase = 'loading';
				option.msg = langs('Сохранение изменений...');
				option.hideDelay = 0;
			} else {
				option.useCase = 'info';
				option.msg = langs('Все изменения сохранены.');
				option.hideDelay = 1000;
			}
		}
		if (form._viewPanelWarningMsg) {
			form._viewPanelWarningMsg.destroy();
		}
		if (form._viewPanelInfoMsg) {
			form._viewPanelInfoMsg.destroy();
		}
		option.viewPanel = form.viewPanel;
		option.onDestroy = function() {
			if (form._viewPanelInfoMsg) {
				delete form._viewPanelInfoMsg;
			}
		};
		form._viewPanelInfoMsg = new sw.Promed.ViewPanelMsg(option);
		form._viewPanelInfoMsg.show();
		return true;
	},
	/**
	 * Выполняет запрос сохранения данных, введенных в интерактивном документе
	 * @param {String} url
	 * @param {Object} params
	 * @param {Function} callback
	 * @param {sw.Promed.BaseForm} scope
	 */
	requestSaveWithShowInfoMsg: function(url, params, callback, scope, yesnofn, nofn) {
		var me = this;
		if (!scope) {
			scope = me;
		}
		me.cntSaveRequests++;
		if (!me._viewPanelInfoMsg || me._viewPanelInfoMsg.getUseCase() != 'loading') {
			me.showInfoMsg({}, true);
		}
		Ext.Ajax.request({
			showErrors: false,
			url: url,
			params: params,
			callback: function(options, success, response) {
				me.cntSaveRequests--;
				var response_obj = Ext.util.JSON.decode(response.responseText);
				if (0 == me.cntSaveRequests
					&& response_obj.success == true
					&& (!me._viewPanelInfoMsg || me._viewPanelInfoMsg.getUseCase() != 'info')
					) {
					me.showInfoMsg({}, false);
				}
				if (response_obj.success == false && response_obj.Error_Msg == 'YesNo') {
					me.showInfoMsg({}, false);
					sw.swMsg.show({
						buttons: Ext.Msg.YESNO,
						fn: function(buttonId, text, obj) {
							if ( buttonId == 'yes' ) {
								if (typeof yesnofn == 'function') {
									var options = Ext.util.JSON.decode(params.options);
									yesnofn(response_obj.Error_Code, options);
									params.options = Ext.util.JSON.encode(options);
								}
								me.requestSaveWithShowInfoMsg(url, params, callback, scope, yesnofn, nofn);
							}
							else if ( buttonId == 'no' && typeof nofn == 'function' ) {
								var options = Ext.util.JSON.decode(params.options);
								nofn(response_obj.Error_Code, options);
								params.options = Ext.util.JSON.encode(options);

								if (options.cancelSetParameter) return true;
								
								me.requestSaveWithShowInfoMsg(url, params, callback, scope, yesnofn, nofn);
							}
						}.createDelegate(this),
						icon: Ext.MessageBox.QUESTION,
						msg: response_obj.Alert_Msg,
						title: langs(' Продолжить сохранение?')
					});
				} else if (response_obj.success == false) {
					me.showInfoMsg({
						msg: response_obj.Error_Msg || langs('При сохранении произошла ошибка'),
						useCase: 'warning'
					}, false);
				}
				if (!Ext.isEmpty(response_obj.Warning_Msg)) {
					showPopupWarningMsg(response_obj.Warning_Msg);
				}
				if (!Ext.isEmpty(response_obj.Info_Msg)) {
					showPopupInfoMsg(response_obj.Info_Msg);
				}
				callback.call(scope, response_obj);
			}
		});
	}
};
/**
 * Класс сообщения, всплывающего в панели просмотра
 * @param {Object} config
 * @return {sw.Promed.ViewPanelMsg}
 * @constructor
 */
sw.Promed.ViewPanelMsg = function(config) {
	var me = this,
		viewPanel = config.viewPanel,
		msg = config.msg,
		useCase = config.useCase || 'warning',
		hideDelay = config.hideDelay || 0, // мс до автоматического скрытия
		links = config.links || {},
		onDestroy = config.onDestroy || Ext.emptyFn,
		id = viewPanel.getId() + 'ViewPanelMsg',
		rendered = false,
		timeout_id = null,
		parentEl = viewPanel.body,
		wrapNode = document.createElement('DIV'),
		iconNode = document.createElement('DIV'),
		mainNode = document.createElement('DIV'),
		btnNode = document.createElement('DIV'),
		icon,
		bgcolor,
		width,
		position;

	me.initialConfig = config;

	switch (useCase) {
		case 'info':
			icon = '/img/icons/info16.png';
			bgcolor = 'silver';
			width = 300;
			position = ' top: ' + parentEl.getXY()[1] + 'px; right: 15px;';
			break;
		case 'loading':
			icon = '/img/icons/UploaderIcons/loading.gif';
			bgcolor = 'silver';
			width = 300;
			position = ' top: ' + parentEl.getXY()[1] + 'px; right: 15px;';
			break;
		default: // warning
			icon = '/img/icons/warning16.png';
			bgcolor = 'yellow';
			width = (parentEl.dom.clientWidth - 30);
			position = ' top: ' + parentEl.getXY()[1] + 'px;'
				+ ' left: ' + parentEl.getXY()[0] + 'px;';
			break;
	}

	wrapNode.setAttribute('id', id);
	//wrapNode.className = 'x-window-bwrap x-window-body popup-msg icon info';
	wrapNode.setAttribute('style', 'position: fixed;'
		+ position
		+ ' width: ' + width + 'px;'
		+ ' padding: 3px; margin: 10px;'
		+ ' border: 1px solid #666; border-radius: 5px;'
		+ ' background-color: ' + bgcolor + ';'
		+ ' overflow: hidden; z-index: 1;');
	//iconNode.className = 'icon';
	iconNode.setAttribute('style', 'background-image: url("' + icon + '");'
		+ ' width: 16px; height: 16px; float: left;'
		+ ' margin-top: 7px; margin-left: 10px; margin-right: 10px;'
	);
	//mainNode.className = 'text';
	mainNode.setAttribute('style', 'margin-top: 7px; margin-bottom: 7px; margin-right: 20px;' +
		' text-align: left; font-size: 12px; font-family: Arial,sans-serif;');
	btnNode.setAttribute('style', 'position: absolute; top: 5px; right: 5px;'
		+ ' background-image: url("/img/icons/close16.png");'
		+ ' width: 16px; height: 16px; cursor: pointer;');

	var el, closeBtnEl, key, link, linkId;

	me.getId = function() {
		return id;
	};
	me.getEl = function() {
		return el;
	};
	me.getCloseBtnEl = function() {
		return closeBtnEl;
	};
	me.getParentEl = function() {
		return parentEl;
	};
	me.getUseCase = function() {
		return useCase;
	};
	me.getMsg = function() {
		return msg;
	};
	me.isRendered = function() {
		return rendered;
	};
	me.show = function() {
		if (!me.isRendered()) {
			me.render();
		}
		if (hideDelay > 0) {
			timeout_id = window.setTimeout(function(){
				if (timeout_id) {
					me.hide();
				}
			}, hideDelay);
		}
	};
	for(key in links) {
		if (!links[key]['link']) {
			continue;
		}
		linkId = me.getId() + key;
		link = '<span class="link" id="' + linkId + '"' +
			' style="color: blue">' +
			links[key]['link'] +
			'</span>';
		msg = msg.replace(key, link);
	}
	me.render = function() {
		el = new Ext.Element(wrapNode);
		closeBtnEl = new Ext.Element(btnNode)
		var textEl = new Ext.Element(mainNode);
		textEl.update(msg);
		me.getEl().appendChild(new Ext.Element(iconNode));
		me.getEl().appendChild(textEl);
		me.getEl().appendChild(closeBtnEl);
		me.getParentEl().appendChild(me.getEl());
		me.getCloseBtnEl().on('click', function() {
			me.hide();
		});
		for(key in links) {
			linkId = me.getId() + key;
			link = Ext.get(linkId);
			if (link) {
				link.on('click', function(e, node) {
					if (typeof(links[key]['onclick']) == 'function') {
						links[key]['onclick'](e, node, me);
					}
				});
			}
		}
		rendered = true;
	};
	me.destroy = function() {
		if (me.isRendered()) {
			me.getEl().remove();
			rendered = false;
		}
		if (typeof(onDestroy)  == 'function') {
			onDestroy();
		}
	};
	me.hide = function() {
		me.destroy();
	};
	return me;
};

/** Проверка, создано ли экстренное извещение об инфекционном заболевании, отравлении (ф. №058/У)
 *  @conf - параметры
 */
function requestEvnInfectNotify(conf) {
	if (!conf || !conf.EvnInfectNotify_pid)
	{
		return false;
	}
	var callback = Ext.emptyFn;
	if (conf.callback && typeof conf.callback == 'function') {
		callback = conf.callback;
	}
	Ext.Ajax.request(
	{
		url: '/?c=EvnInfectNotify&m=isIsset',
		params: {EvnInfectNotify_pid: conf.EvnInfectNotify_pid},
		callback: function(options, success, response)
		{
			if (success)
			{
				if ( response.responseText.length > 0 )
				{
					var result = Ext.util.JSON.decode(response.responseText);
					var cb = function(){
						showSysMsg(langs('Извещение создано'));
						callback();
					}
					if (!result.success)
					{
						sw.swMsg.show(
						{
							buttons: Ext.Msg.YESNO,
							fn: function( buttonId )
							{
								if ( buttonId == 'yes' ) {
									getWnd('swEvnInfectNotifyEditWindow').show({formParams: conf,callback:cb});
								} else {
									callback();
								}
							},
							msg: langs('Создать "Экстренное извещение об инфекционном заболевании, отравлении" (ф. №058/У)?'),
							title: langs('Вопрос')
						});
					} else {
						callback();
					}
				}
			}
		}
	});
}


/** Создание извещение на онкобольного, проверка запущенной формы злокачественного новообразования
 *  @conf - параметры
 */
function checkEvnOnkoNotify(conf) {
	if (!conf || !conf.EvnOnkoNotify_pid)
	{
		return false;
	}
	var checkEvnOnkoNotifyNeglected = function(conf) {
		//Проверка запущенной формы злокачественного новообразования
		if (Ext.isEmpty(conf.EvnOnkoNotifyNeglected_id) && conf.Diag_Code && conf.TumorStage_id)
		{
			var diag_code_check = Array('C00', 'C01', 'C02', 'C04', 'C06', 'C07', 'C08', 'C09', 'C20', 'C21', 'C44', 'C51', 'C60', 'C50', 'C52', 'C53', 'C73', 'C62');
			var diag_code = conf.Diag_Code.substr(0, 3);
			if (
					(
						(conf.TumorStage_id >= 13 && conf.TumorStage_id <= 16) ||
						((conf.TumorStage_id >= 9 && conf.TumorStage_id <= 12) && ( diag_code.inlist(diag_code_check)) || conf.Diag_Code == 'C63.2')
					) || (
						getRegionNick() == 'kz' &&
						((conf.TumorStage_id >= 32 && conf.TumorStage_id <= 35) ||
						((conf.TumorStage_id >= 28 && conf.TumorStage_id <= 31) && ( diag_code.inlist(diag_code_check)) || conf.Diag_Code == 'C63.2'))
					)
				)
			{
				sw.swMsg.show(
				{
					buttons: Ext.Msg.YESNO,
					fn: function( buttonId )
					{
						if ( buttonId == 'yes' ) {
							if( !Ext.isEmpty(conf.Alert_Msg) ) {
								sw.swMsg.alert(langs('Сообщение'), conf.Alert_Msg);
								if (conf.mode == 'signedDocument') {
									//отменять подписание учетного документа, из которого создается Извещение.
									signedDocument({
										Evn_id: conf.EvnOnkoNotify_pid
										,callback: conf.callback
										,allowQuestion: false
									});
								}
							} else {
								getWnd('swEvnOnkoNotifyNeglectedEditWindow').show({formParams: conf});
							}
						}
					},
					msg: langs('У пациента выявлена запущенная форма злокачественного новообразования. Создать Протокол?'),
					title: langs('Вопрос')
				});
			}
		}
	};
	var createEvnOnkoNotify = function(conf) {
		var params = {
			EvnOnkoNotify_id: null
			,formParams: {
				EvnOnkoNotify_id: null
				,EvnOnkoNotify_pid: conf.EvnOnkoNotify_pid
				,EvnDiagPLSop_id: conf.EvnDiagPLSop_id || null
				,Morbus_id: conf.Morbus_id || null
				,Server_id: conf.Server_id
				,PersonEvn_id: conf.PersonEvn_id
				,Person_id: conf.Person_id
				,Diag_id: conf.Diag_id
				,Lpu_sid: null
				,EvnOnkoNotify_setDT: conf.EvnOnkoNotify_setDT || getGlobalOptions().date
				,MedPersonal_id: conf.MedPersonal_id
			}
			,callback: function (data) {
				conf.EvnOnkoNotify_id = data.EvnOnkoNotify_id;
				checkEvnOnkoNotifyNeglected(conf);

				if(typeof conf.callback == 'function') {
					conf.callback();
				}
			}
		};
		// тут особый случай, когда должны быть заполнены обязательные поля специфики
		var notMorbusMsg = langs('Для выписки Извещения по онкологии необходимо заполнить в специфике поля: "Дата установления диагноза", "Метод подтверждения диагноза", "Стадия опухолевого процесса", "Стадия опухолевого процесса по системе TNM"');
		if (conf.mode != 'signedDocument') {
			if ( !Ext.isEmpty(conf.Alert_Msg) ) {
				sw.swMsg.alert(langs('Сообщение'), conf.Alert_Msg);
			} else if (false && !conf.Morbus_id) { //Проверка всех полей уже есть на стороне сервера(ложное срабатывание в стоматологии)
				sw.swMsg.alert(langs('Сообщение'), notMorbusMsg);
			} else {
				getWnd('swEvnOnkoNotifyEditWindow'+(sw.isExt6Menu ? 'Ext6':'')).show(params);
			}
			return true;
		}
		// conf.mode == 'signedDocument'
		sw.swMsg.show(
		{
			buttons: Ext.Msg.YESNO,
			fn: function( buttonId )
			{
				if ( buttonId == 'yes' ) {
					if( !Ext.isEmpty(conf.Alert_Msg) ) {
						sw.swMsg.alert(langs('Сообщение'), conf.Alert_Msg);
						//отменять подписание учетного документа, из которого создается Извещение.
						signedDocument({
							Evn_id: conf.EvnOnkoNotify_pid
							,callback: conf.callback
							,allowQuestion: false
						});
					} else {
						if (!conf.Morbus_id) {
							sw.swMsg.alert(langs('Сообщение'), notMorbusMsg);
							//отменять подписание учетного документа, из которого создается Извещение.
							signedDocument({
								Evn_id: conf.EvnOnkoNotify_pid
								,callback: conf.callback
								,allowQuestion: false
							});
						} else {
							getWnd('swEvnOnkoNotifyEditWindow'+(sw.isExt6Menu ? 'Ext6':'')).show(params);
						}
					}
				}
			},
			msg: langs('Создать Извещение о больном с впервые в жизни установленным диагнозом злокачественного новообразования?'),
			title: langs('Вопрос')
		});
		return true;
	};
	createEvnOnkoNotify(conf);

	if ( !Ext.isEmpty(conf.EvnOnkoNotify_id) )
	{
		checkEvnOnkoNotifyNeglected(conf);
	}
}

function checkEvnNotifyNephro(conf) {
	if (!conf || (!conf.EvnNotifyNephro_pid && !conf.fromDispCard)) {
		return false;
	}
	var createNotify = function(conf) {
		var params = {
			EvnNotifyNephro_id: null
			,formParams: {
				EvnNotifyNephro_id: null
				,EvnNotifyNephro_pid: conf.EvnNotifyNephro_pid
				,Morbus_id: conf.Morbus_id
				,Server_id: conf.Server_id
				,PersonEvn_id: conf.PersonEvn_id
				,Person_id: conf.Person_id
				,Diag_Name: conf.Diag_Name
				,EvnNotifyNephro_setDate: conf.EvnNotifyNephro_setDate || getGlobalOptions().date
				,MedPersonal_id: conf.MedPersonal_id
				,fromDispCard: conf.fromDispCard
			}
			,callback: function (data) {
				conf.EvnNotifyNephro_id = data.EvnNotifyNephro_id;
				if (typeof conf.callback == 'function') {
					conf.callback(data.EvnNotifyNephro_id);
				}
			}
		};
		var msg = langs('Создать Извещение по нефрологии');
		if (false && conf.mode != 'signedDocument') {
			if( !Ext.isEmpty(conf.Alert_Msg) ) {
				sw.swMsg.alert(langs('Сообщение'), conf.Alert_Msg);
			} else {
				getWnd('swEvnNotifyNephroEditWindow').show(params);
			}
			return true;
		}
		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function( buttonId )
			{
				if ( buttonId == 'yes' ) {
					if( !Ext.isEmpty(conf.Alert_Msg) ) {
						sw.swMsg.alert(langs('Сообщение'), conf.Alert_Msg);
						if (conf.mode == 'signedDocument') {
							//отменять подписание учетного документа, из которого создается Извещение.
							signedDocument({
								Evn_id: conf.EvnNotifyNephro_pid
								,callback: conf.callback
								,allowQuestion: false
							});
						}
					} else {
						if (!conf.Morbus_id) {
							// тут особый случай, когда при добавлении извещения в форму загружаются данные специфики, т.е. заболевание уже должно быть создано
							// или ничего не загружать в форму, когда нет заболевания?
							sw.swMsg.alert(langs('Сообщение'), langs('Перед созданием извещения нужно заполнить специфику заболевания'));
							if (conf.mode == 'signedDocument') {
								//отменять подписание учетного документа, из которого создается Извещение.
								signedDocument({
									Evn_id: conf.EvnNotifyNephro_pid
									,callback: conf.callback
									,allowQuestion: false
								});
							}
						} else {
							getWnd('swEvnNotifyNephroEditWindow').show(params);
						}
					}
				}
			},
			msg: msg,
			title: langs('Вопрос')
		});
		return true;
	};
	if ( Ext.isEmpty(conf.EvnNotifyNephro_id) && Ext.isEmpty(conf.PersonRegister_id)) {
		return createNotify(conf);
	}
	return false;
}

function checkEvnNotifyProf(conf) {
	if (!conf || !conf.EvnNotifyProf_pid)
	{
		return false;
	}
	var createNotify = function(conf) {
		var params = {
			EvnNotifyProf_id: null
			,formParams: {
				EvnNotifyProf_id: null
				,EvnNotifyProf_pid: conf.EvnNotifyProf_pid || null
				,Morbus_id: conf.Morbus_id || null
				,Server_id: conf.Server_id
				,PersonEvn_id: conf.PersonEvn_id
				,Person_id: conf.Person_id
				,Diag_id: conf.Diag_id
				,Diag_Name: conf.Diag_Name
				,EvnNotifyProf_setDate: conf.EvnNotifyProf_setDate || getGlobalOptions().date
				,MedPersonal_id: conf.MedPersonal_id
			}
			,callback: function (data) {
				conf.EvnNotifyProf_id = data.EvnNotifyProf_id;
				if (typeof conf.callback == 'function') {
					conf.callback(data.EvnNotifyProf_id);
				}
			}
		};
		var msg = langs('Создать Извещение по профзаболеванию?');
		if (false && conf.mode != 'signedDocument') {
			if( !Ext.isEmpty(conf.Alert_Msg) ) {
				sw.swMsg.alert(langs('Сообщение'), conf.Alert_Msg);
			} else {
				getWnd('swEvnNotifyProfEditWindow').show(params);
			}
			return true;
		}
		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function( buttonId )
			{
				if ( buttonId == 'yes' ) {
					if( !Ext.isEmpty(conf.Alert_Msg) ) {
						sw.swMsg.alert(langs('Сообщение'), conf.Alert_Msg);
					} else {
						getWnd('swEvnNotifyProfEditWindow').show(params);
					}
				}
			},
			msg: msg,
			title: langs('Вопрос')
		});
		return true;
	};
	if ( Ext.isEmpty(conf.EvnNotifyProf_id) && Ext.isEmpty(conf.PersonRegister_id)) {
		return createNotify(conf);
	}
	return false;
}
/** Создание/проверка наличия извещения и записи регистра с логикой автоматического включения в регистр при создании извещения
 *  @conf - параметры
 */
function checkEvnNotifyBaseWithAutoIncludeInPersonRegister(conf, mode, morbus_type, callback) {
	if (!conf || !conf.Evn_id || !morbus_type || !morbus_type.toString().inlist(['crazy','hepa','orphan','tub','vener','hiv','narc'])) {
		return false;
	}
	conf.MedPersonal_id = getGlobalOptions().medpersonal_id;
	conf.EvnNotifyBase_setDT = getGlobalOptions().date;

	if (mode != 'signedDocument') {
		conf.disableQuestion = true;
	}

	var createNotify = function(conf) {
		var params = {
			formParams: {
				Morbus_id: conf.MorbusType_List[morbus_type].Morbus_id || null
				,Server_id: conf.Server_id
				,PersonEvn_id: conf.PersonEvn_id
				,Person_id: conf.Person_id
				,Diag_Name: conf.Diag_Name
				,Diag_id: conf.Diag_id
				,MedPersonal_id: conf.MedPersonal_id
			}
			,callback: function (data) {
				// автоматическое включение в регистр производится в той же транзакции, что и создание извещения
				var success = (data && data.EvnNotifyBase_id && data.EvnNotifyBase_id > 0);
				if (typeof callback == 'function') {
					callback(success);
				}
			}
		};
		switch (true) {
			case ('narc' == morbus_type): //Создание извещения о больном с диагнозом наркомании
				params.formParams.EvnNotifyNarco_id = null;
				params.formParams.EvnNotifyNarco_pid = conf.Evn_id;
				params.formParams.EvnNotifyNarco_setDT = conf.EvnNotifyBase_setDT;
				if (conf.disableQuestion) {
					getWnd('swEvnNotifyNarcoEditWindow').show(params);
				} else {
					sw.swMsg.show({
						buttons: Ext.Msg.YESNO,
						fn: function( buttonId ) {
							if ( buttonId == 'yes' ) {
								if( !Ext.isEmpty(conf.Alert_Msg) ) {
									sw.swMsg.alert(langs('Сообщение'), conf.Alert_Msg);
								} else {
									getWnd('swEvnNotifyNarcoEditWindow').show(params);
								}
							}
						},
						msg: langs('Создать Извещение о больном с впервые в жизни установленным диагнозом наркомании?'),
						title: langs('Вопрос')
					});
				}
				break;

			case ('crazy' == morbus_type): //Создание извещения о больном с диагнозом психиатрии (наркомании)
				params.formParams.EvnNotifyCrazy_id = null;
				params.formParams.EvnNotifyCrazy_pid = conf.Evn_id;
				params.formParams.EvnNotifyCrazy_setDT = conf.EvnNotifyBase_setDT;
				if (conf.disableQuestion) {
					getWnd('swEvnNotifyCrazyEditWindow').show(params);
				} else {
					sw.swMsg.show({
						buttons: Ext.Msg.YESNO,
						fn: function( buttonId ) {
							if ( buttonId == 'yes' ) {
								if( !Ext.isEmpty(conf.Alert_Msg) ) {
									sw.swMsg.alert(langs('Сообщение'), conf.Alert_Msg);
								} else {
									getWnd('swEvnNotifyCrazyEditWindow').show(params);
								}
							}
						},
						msg: langs('Создать Извещение о больном психическим заболеванием?'),
						title: langs('Вопрос')
					});
				}
				break;

			case ('hepa' == morbus_type): //Создание извещения о больном вирусным гепатитом
				params.formParams.EvnNotifyHepatitis_id = null;
				params.formParams.EvnNotifyHepatitis_pid = conf.Evn_id;
				params.formParams.EvnNotifyHepatitis_setDT = conf.EvnNotifyBase_setDT;
				Ext.Ajax.request({
					failure:function (response, options) {
						sw.swMsg.alert(langs('Ошибка'), langs('Не удалось получить данные с сервера'));
					},
					params: params.formParams,
					success:function (response, options) {
						var result  = Ext.util.JSON.decode(response.responseText);
						if (result && result.EvnNotifyBase_id) {
							if (result.PersonRegister_id) {
								showSysMsg(langs('Создано извещение о больном вирусным гепатитом и пациент включен в регистр'));
							} else {
								showSysMsg(langs('Создано извещение о больном вирусным гепатитом'));
							}
							params.callback(result);
						}
					},
					url:'/?c=EvnNotifyHepatitis&m=save'
				});
				break;
			case ('orphan' == morbus_type): //Создание извещения об орфанном заболевании
				params.formParams.EvnNotifyOrphan_id = null;
				params.formParams.EvnNotifyOrphan_pid = conf.Evn_id;
				params.formParams.EvnNotifyOrphan_setDT = conf.EvnNotifyBase_setDT;
				if (conf.disableQuestion) {
					getWnd('swEvnNotifyOrphanEditWindow').show(params);
				} else {
					sw.swMsg.show({
						buttons: Ext.Msg.YESNO,
						fn: function( buttonId ) {
							if ( buttonId == 'yes' ) {
								if( !Ext.isEmpty(conf.Alert_Msg) ) {
									sw.swMsg.alert(langs('Сообщение'), conf.Alert_Msg);
								} else {
									getWnd('swEvnNotifyOrphanEditWindow').show(params);
								}
							}
						},
						msg: langs('Создать Направление на включение в регистр по орфанным заболеваниям? '),
						title: langs('Вопрос')
					});
				}
				break;
			case ('tub' == morbus_type): //Создание извещения о больном туберкулезом
				params.EvnNotifyTub_id = null;
				params.formParams.EvnNotifyTub_id = null;
				params.formParams.EvnNotifyTub_pid = conf.Evn_id;
				params.formParams.EvnNotifyTub_setDT = conf.EvnNotifyBase_setDT;
				params.formParams.EvnNotifyTub_IsFirstDiag = conf.EvnNotifyTub_IsFirstDiag || 2;
				if (conf.disableQuestion) {
					getWnd('swEvnNotifyTubEditWindow').show(params);
				} else {
					sw.swMsg.show({
						buttons: Ext.Msg.YESNO,
						fn: function( buttonId )
						{
							if ( buttonId == 'yes' ) {
								getWnd('swEvnNotifyTubEditWindow').show(params);
							}
						},
						msg: langs('Создать Извещение о больном туберкулезом?'),
						title: langs('Вопрос')
					});
				}
				break;
			case ('vener' == morbus_type): //Создание извещения с типом "Венерология".
				params.EvnNotifyVener_id = null;
				params.formParams.EvnNotifyVener_id = null;
				params.formParams.EvnNotifyVener_pid = conf.Evn_id;
				params.formParams.EvnNotifyVener_setDT = conf.EvnNotifyBase_setDT;
				if (conf.disableQuestion) {
					getWnd('swEvnNotifyVenerEditWindow').show(params);
				} else {
					sw.swMsg.show({
						buttons: Ext.Msg.YESNO,
						fn: function( buttonId )
						{
							if ( buttonId == 'yes' ) {
								getWnd('swEvnNotifyVenerEditWindow').show(params);
							}
						},
						msg: langs('Создать Извещение о больном венерическим заболеванием?'),
						title: langs('Вопрос')
					});
				}
				break;
			case ('hiv' == morbus_type): //Создание извещения о ВИЧ-инфицированном
				params.EvnNotifyHIV_id = null;
				params.formParams.EvnNotifyHIV_id = null;
				params.formParams.EvnNotifyHIV_pid = conf.Evn_id;
				params.formParams.EvnNotifyHIV_setDT = conf.EvnNotifyBase_setDT;
				function createEvnNotifyHIV(params) {
					Ext.Ajax.request({
						failure:function (response, options) {
							sw.swMsg.alert(langs('Ошибка'), langs('Не удалось получить данные с сервера'));
						},
						params: {Person_id: params.formParams.Person_id},
						success:function (response, options) {
							var result  = Ext.util.JSON.decode(response.responseText);
							if(result && result.success) {
								params.formParams.HIVContingentType_pid = result.HIVContingentType_id;
								getWnd('swEvnNotifyHIVEditWindow').show(params);
							}
						},
						url:'/?c=MorbusHIV&m=definePatriality'
					});
				}
				if (conf.disableQuestion) {
					createEvnNotifyHIV(params);
				} else {
					sw.swMsg.show({
						buttons: Ext.Msg.YESNO,
						fn: function( buttonId )
						{
							if ( buttonId == 'yes' ) {
								createEvnNotifyHIV(params);
							}
						},
						msg: langs('Создать ОПЕРАТИВНОЕ ДОНЕСЕНИЕ о лице, в крови которого при исследовании в реакции иммуноблота выявлены антитела к ВИЧ (форма N 266/У-88)?'),
						title: langs('Вопрос')
					});
				}
				break;
		}
	};

	var returnToTheRegistry = function(result) {
		var buttons = {
			yes: (parseInt(result.MorbusType_List[morbus_type].PersonRegisterOutCause_id) == 3) ? langs('Новое') : langs('Да'),
			no: (parseInt(result.MorbusType_List[morbus_type].PersonRegisterOutCause_id) == 3) ? langs('Предыдущее') : langs('Нет')
		};
		if (parseInt(result.MorbusType_List[morbus_type].PersonRegisterOutCause_id) == 3) {
			buttons.cancel = langs('Отмена');
		}
		sw.swMsg.show(
		{
			buttons: buttons,
			fn: function( buttonId )
			{
				var mode;
				if ( buttonId == 'yes' && result.Yes_Mode)
				{
					mode = result.Yes_Mode
				}
				else if ( buttonId == 'no' && result.No_Mode)
				{
					mode = result.No_Mode
				}
				if(mode)
				{
					if ( mode.inlist(['homecoming','relapse']) )
					{
						// Вернуть пациента в регистр, удалить дату закрытия заболевания
						sw.Promed.personRegister.back({
							PersonRegister_id: result.MorbusType_List[morbus_type].PersonRegister_id
							,EvnNotifyBase_id: result.MorbusType_List[morbus_type].EvnNotifyBase_id
							,Diag_id: result.Diag_id
							,Morbus_id: result.MorbusType_List[morbus_type].Morbus_id
							,ownerWindow: Ext.getCmp('PersonEmkForm')
							,callback: function() {
								showSysMsg(langs('Пациент возвращен в регистр'));
							}
						});
					}
					else
					{
						// Новое заболевание: создать Извещение/Запись регистра
						conf.MorbusType_List[morbus_type].Morbus_id = null;
						conf.Mode = mode;
						conf.disableQuestion = true;
						conf.EvnNotifyTub_IsFirstDiag = 1;
						createNotify(conf);
					}
				}
			},
			msg: result.Alert_Msg,
			title: langs('Вопрос')
		});
	};

	if ( Ext.isEmpty(conf.MorbusType_List[morbus_type].EvnNotifyBase_id) && Ext.isEmpty(conf.MorbusType_List[morbus_type].PersonRegister_id) )
	{
		//Если нет извещения/записи регистра, то создавать новое извещение и новую запись регистра.
		createNotify(conf);
		return true;
	}
	else
	{
		if ( !Ext.isEmpty(conf.MorbusType_List[morbus_type].EvnNotifyBase_id) && Ext.isEmpty(conf.MorbusType_List[morbus_type].PersonRegisterOutCause_id))
		{
			//Если уже есть извещение и запись регистра с открытым заболеванием, то новое извещение не создавать.
			return true;
		}
		if ( 1 == conf.MorbusType_List[morbus_type].PersonRegisterOutCause_id )
		{
			sw.swMsg.alert(langs('Сообщение'), langs('Пациент был исключен из регистра по причине "Смерть", повторное включение в регистр невозможно.'));
			return true;
		}
		if ( 2 == conf.MorbusType_List[morbus_type].PersonRegisterOutCause_id )
		{
			conf.Alert_Msg = langs('Пациент был исключен из регистра по причине "Выехал". Вернуть пациента в регистр?');
			conf.Yes_Mode = 'homecoming'; // Извещение/Запись регистра не создавать, восстановить запись в регистре, удалить дату закрытия заболевания
			conf.No_Mode = false; // Извещение/Запись регистра не создавать
			returnToTheRegistry(conf);
			return true;
		}
		if ( 3 == conf.MorbusType_List[morbus_type].PersonRegisterOutCause_id )
		{
			conf.Alert_Msg = langs('Пациент был исключен из регистра по причине "Выздоровление". У пациента новое заболевание?');
			conf.Yes_Mode = 'new'; // При нажатии "Новое" создать Извещение/Запись регистра
			conf.No_Mode = 'relapse'; // При нажатии "Предыдущее" Извещение/Запись регистра не создавать, восстановить запись в регистре, удалить дату закрытия заболевания
			returnToTheRegistry(conf);
			return true;
		}
	}
	return false;
}

/** Добавляет всплывающую подсказку на ховер элемента
 *
 **/
function addToolTip(field, msg) {
		field.el.dom.qtip = msg;
		field.el.dom.qclass = 'x-form-invalid-tip';
		if(Ext.QuickTips){
			Ext.QuickTips.enable();
		}
}

function isEncrypHIVRegion() {
	return getRegionNick().inlist(['astra','kaluga']);
}

/**
 * Определение доступа к регистру ВИЧ-инфицированных
 */
function allowHIVRegistry() {
	var allow = isUserGroup('HIVRegistry');
	if (isEncrypHIVRegion()) {
		allow = (isUserGroup('HIVRegistry') && getGlobalOptions().lpu_is_secret && haveArmType('common'));
	}
	return allow;
}

/** Создание/проверка Извещение о случае завершения беременности у ВИЧ-инфицированной женщины
 *  @conf - параметры
 */
function checkEvnNotifyHIVPreg(conf) {
	//log(conf);
	if (Ext.isEmpty(conf) || Ext.isEmpty(conf.EvnNotifyHIVPreg_pid) || Ext.isEmpty(conf.PersonRegister_id))
	{
		return false;
	}
	var createNotify = function(conf) {
		var params = {
			EvnNotifyHIVPreg_id: null
			,formParams: {
				EvnNotifyHIVPreg_id: null
				,EvnNotifyHIVPreg_pid: conf.EvnNotifyHIVPreg_pid
				,Morbus_id: conf.Morbus_id
				,Server_id: conf.Server_id
				,PersonEvn_id: conf.PersonEvn_id
				,Person_id: conf.Person_id
				//,Diag_Name: conf.Diag_Name
				,Diag_id: conf.Diag_id
				,EvnNotifyHIVPreg_setDT: conf.EvnNotifyHIVPreg_setDT || getGlobalOptions().date
				,MedPersonal_id: conf.MedPersonal_id
			}
			,callback: function (data) {
				conf.EvnNotifyHIVPreg_id = data.EvnNotifyHIVPreg_id;
			}
		};
		if (conf.mode != 'signedDocument') {
			if( !Ext.isEmpty(conf.Alert_Msg) ) {
				sw.swMsg.alert(langs('Сообщение'), conf.Alert_Msg);
			} else {
				getWnd('swEvnNotifyHIVPregEditWindow').show(params);
			}
			return true;
		}
		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function( buttonId )
			{
				if ( buttonId == 'yes' ) {
					if( !Ext.isEmpty(conf.Alert_Msg) ) {
						sw.swMsg.alert(langs('Сообщение'), conf.Alert_Msg);
					} else {
						getWnd('swEvnNotifyHIVPregEditWindow').show(params);
					}
				}
			},
			msg: langs('Создать Извещение о случае завершения беременности у ВИЧ-инфицированной женщины (форма N 313/у)?'),
			title: langs('Вопрос')
		});
		return true;
	};
	return createNotify(conf);
}

/**
 * Проверяет наличие направления на МСЭ у пациента со статусами «Новое» и «Отказ ВК»
 *
 * option.Person_id - человек
 * option.Lpu_id - МО
 * option.callback - функция
 * @param {Object} option
 * @return {void}
 */
function checkEvnPrescrMseExists(option)
{
	if (!option || !option.Person_id || typeof option.callback != 'function')
		return;
	Ext.Ajax.request({
		url: '/?c=Mse&m=checkEvnPrescrMseExists',
		params: {Person_id: option.Person_id},
		failure: function(response, options) {
			if(typeof option.failure == 'function') {
				option.failure(response, options);
				return;
			}
			sw.swMsg.alert(langs('Ошибка'), langs('При проверке человека возникли ошибки!'));
		},
		success: function(response, options) {
			if ( response && typeof response.responseText == 'string' && response.responseText.length > 0 ) {
				var result  = Ext.util.JSON.decode(response.responseText);
				if ( result.length > 0) {
					sw.swMsg.show({
						buttons: Ext.Msg.YESNO,
						fn: function( buttonId ) {
							if ( buttonId == 'yes' ) {
								option.callback();
							}
						},
						msg: 'Для пациента уже существует направление на МСЭ со статусом «Новое» или «Отказ ВК». Желаете создать новое направление на МСЭ?',
						title: langs('Вопрос')
					});
					return;
				}
				option.callback();
				return;
			}
			if(typeof option.failure == 'function') {
				option.failure(response, options);
				return;
			}
			sw.swMsg.alert(langs('Ошибка'), langs('При проверке человека произошла ошибка запроса к БД!'));
		}
	});
}

/**
 * Создаёт новое направление на МСЭ для пациента
 *
 * option.personData - человек
 * option.callback - функция
 * @param {Object} option
 * @return {void}
 */
function createEvnPrescrMse(option)
{
	if (!option || !option.personData || !option.userMedStaffFact || typeof option.callback != 'function')
		return;
	sw.Promed.Direction.queuePerson({
		person: {
			Person_Surname: option.personData.Person_Surname,
			Person_Firname: option.personData.Person_Firname,
			Person_Secname: option.personData.Person_Secname,
			Person_Birthday: option.personData.Person_Birthday,
			Person_id: option.personData.Person_id,
			PersonEvn_id: option.personData.PersonEvn_id,
			Server_id: option.personData.Server_id
		},
		direction: {
			DirType_id: 9
			,MedService_id: null
			,MedServiceType_id: null
			,MedService_Nick: null
			,MedService_Name: null
			,MedServiceType_SysNick: 'mse'
			,Lpu_did: null
			,LpuUnit_did: null
			,LpuUnitType_SysNick: 'mse'
			,LpuSection_uid: null
			,LpuSection_Name: null
			,LpuSectionProfile_id: null
			,Diag_id: null
			,EvnDirection_pid: option.directionData.EvnDirection_pid || null
			,EvnVK_id: null
			,EvnQueue_id: null
			,QueueFailCause_id: null
			,UslugaComplex_id: null
			,PrehospDirect_id: 1
			,EvnVK_setDT: null
			,EvnPrescr_id: null
			,PrescriptionType_Code: null
			,MedStaffFact_id: option.userMedStaffFact.MedStaffFact_id || getGlobalOptions().CurMedStaffFact_id
			,MedPersonal_id: option.userMedStaffFact.MedPersonal_id || getGlobalOptions().CurMedPersonal_id
			,Lpu_id: option.userMedStaffFact.Lpu_id || getGlobalOptions().lpu_id
			,LpuSection_id: option.userMedStaffFact.LpuSection_id || getGlobalOptions().CurLpuSection_id
			,withCreateDirection: false
			//передаем фейковые данные, т.к. они обязательные при сохранении очереди. Но при постановке в очередь на службу уровня ЛПУ этих данных просто нет
			,LpuSection_did: option.userMedStaffFact.LpuSection_id || getGlobalOptions().CurLpuSection_id //куда направлен
			,LpuUnit_did: option.userMedStaffFact.LpuUnit_id || null //куда направлен
		},
		order: {}, // если при записи сделан заказ, то передаем его данные
		callback: option.callback,
		onHide: null,
		needDirection: null,
		fromEmk: null,
		mode: 'nosave',
		loadMask: true,
		windowId: 'TTMSScheduleRecordWindow'
	});
}

/**
 * Создаёт новое направление на ВК для пациента
 *
 * option.personData - человек
 * option.callback - функция
 * @param {Object} option
 * @return {void}
 */
function createEvnPrescrVK(option)
{
	if (!option || !option.personData || !option.userMedStaffFact || typeof option.callback != 'function')
		return;
	sw.Promed.Direction.queuePerson({
		person: {
			Person_Surname: option.personData.Person_Surname,
			Person_Firname: option.personData.Person_Firname,
			Person_Secname: option.personData.Person_Secname,
			Person_Birthday: option.personData.Person_Birthday,
			Person_id: option.personData.Person_id,
			PersonEvn_id: option.personData.PersonEvn_id,
			Server_id: option.personData.Server_id
		},
		direction: {
			DirType_id: 9
			,MedService_id: null
			,MedServiceType_id: null
			,MedService_Nick: null
			,MedService_Name: null
			,MedServiceType_SysNick: 'vk'
			,Lpu_did: getGlobalOptions().lpu_id
			,LpuUnit_did: null
			,LpuUnitType_SysNick: 'vk'
			,LpuSection_uid: null
			,LpuSection_Name: null
			,LpuSectionProfile_id: null
			,Diag_id: null
			,EvnDirection_pid: option.directionData.EvnDirection_pid || null
			,EvnVK_id: null
			,EvnQueue_id: null
			,QueueFailCause_id: null
			,UslugaComplex_id: null
			,PrehospDirect_id: 1
			,EvnVK_setDT: null
			,EvnPrescr_id: null
			,PrescriptionType_Code: null
			,MedStaffFact_id: option.userMedStaffFact.MedStaffFact_id || getGlobalOptions().CurMedStaffFact_id
			,MedPersonal_id: option.userMedStaffFact.MedPersonal_id || getGlobalOptions().CurMedPersonal_id
			,Lpu_id: option.userMedStaffFact.Lpu_id || getGlobalOptions().lpu_id
			,LpuSection_id: option.userMedStaffFact.LpuSection_id || getGlobalOptions().CurLpuSection_id
			,withCreateDirection: false
			//передаем фейковые данные, т.к. они обязательные при сохранении очереди. Но при постановке в очередь на службу уровня ЛПУ этих данных просто нет
			,LpuSection_did: option.userMedStaffFact.LpuSection_id || getGlobalOptions().CurLpuSection_id //куда направлен
			,LpuUnit_did: option.userMedStaffFact.LpuUnit_id || null //куда направлен
		},
		order: {}, // если при записи сделан заказ, то передаем его данные
		callback: option.callback,
		onHide: null,
		needDirection: null,
		fromEmk: null,
		mode: 'nosave',
		loadMask: true,
		windowId: option.windowId,
		win: option.win
	});
}

/** Набор методов для PersonRegister
 *
 */
sw.Promed.personRegister = {
	PersonRegisterFailIncludeCauseStore: null,
	/**
	 * Доступна ли работа с регистром указанного типа.
	 * Аналог метода библиотеки swPersonRegister::isAllow
	 * @param {String} type
	 * @return {Boolean}
	 */
	isAllow: function(type) {
		var res,
			regionNick = getRegionNick();
		switch (type) {
			case 'orphan': // по орфанным заболеваниям
			case 'nolos': // ВЗН (7 нозологий)
			case 'prof': // по профзаболеваниям
			case 'palliat': // по паллиативной помощи
			case 'geriatrics': // по гериатрии
				//res = (regionNick != 'saratov'); // для всех регионов, кроме Саратова
				res = true; // для всех регионов
				break;
			case 'fmba': // ФМБА
				res = (regionNick == 'saratov'); // для Саратова
				//res = true; // для всех регионов
				break;
			default:
				res = false;
				break;
		}
		return res;
	},
	/**
	 * Есть ли у пользователя промеда группа для работы регистром по орфанным заболеваниям
	 * @return {Boolean}
	 */
	isOrphanRegistryOperator: function() {
		return (String(getGlobalOptions().groups).indexOf('Orphan', 0) >= 0);
	},
	/**
	 * Есть ли у пользователя промеда группа для экспорта в федеральный регистр по орфанным заболеваниям
	 * @return {Boolean}
	 */
	isOrphanExportOperator: function() {
		return (String(getGlobalOptions().groups).indexOf('Orphan', 0) >= 0
		);
	},
	/**
	 * @param {String} win_id
	 * @param {sw.Promed.BaseForm} arm
	 * @returns {Object}
	 */
	getOrphanBtnConfig: function(win_id, arm, withoutIcon) {
		var self = this,
			isDisabled = (false == self.isOrphanRegistryOperator()),
			isHidden = (false == self.isAllow('orphan'));
		var cfg = {
			tooltip: langs('Регистр по орфанным заболеваниям'),
			text: langs('Регистр по орфанным заболеваниям'),
			iconCls: withoutIcon ? '' : 'doc-reg16',
			disabled: isDisabled,
			hidden: isHidden,
			handler: function() {
				var win = arm || Ext.getCmp(win_id);
				if (win) {
					if ( getWnd('swPersonRegisterOrphanListWindow').isVisible() ) {
						getWnd('swPersonRegisterOrphanListWindow').hide();
					}
					var ARMType = '';
					if (win_id == 'swWorkPlaceSpecMEKLLOWindow')
						ARMType = 'spesexpertllo';
					if (win_id == 'swWorkPlaceAdminLLOWindow')
						ARMType = 'adminllo';
					getWnd('swPersonRegisterOrphanListWindow').show({userMedStaffFact: win.userMedStaffFact, ARMType: ARMType});
				}
			}
		};
		return cfg;
	},
	/**
	 * @param {String} win_id
	 * @param {sw.Promed.BaseForm} arm
	 * @returns {Object}
	 */
	getEvnNotifyOrphanBtnConfig: function(win_id, arm, withoutIcon) {
		var self = this,
			isDisabled = (false),
			isHidden = (false == self.isAllow('orphan'));
		var cfg = {
			tooltip: langs('Журнал Извещений/Направлений об орфанных заболеваниях'),
			text: langs('Журнал Извещений/Направлений об орфанных заболеваниях'),
			iconCls: withoutIcon ? '' : 'journal16',
			disabled: isDisabled,
			hidden: isHidden,
			handler: function() {
				var win = arm || Ext.getCmp(win_id);
				if (win) {
					if ( getWnd('swEvnNotifyRegisterOrphanListWindow').isVisible() ) {
						getWnd('swEvnNotifyRegisterOrphanListWindow').hide();
					}
					getWnd('swEvnNotifyRegisterOrphanListWindow').show({userMedStaffFact: win.userMedStaffFact});
				}
			}
		};
		return cfg;
	},
	/**
	 * @param {String} win_id
	 * @param {sw.Promed.BaseForm} arm
	 * @returns {Object}
	 */
	getEvnNotifyPalliatBtnConfig: function(win_id, arm, withoutIcon) {
		var self = this,
			isDisabled = (false == self.isPalliatRegistryOperator()),
			isHidden = (false == self.isAllow('palliat'));
		var cfg = {
			tooltip: langs('Журнал Извещений по паллиативной помощи'),
			text: langs('Журнал Извещений по паллиативной помощи'),
			iconCls: withoutIcon ? '' : 'journal16',
			disabled: isDisabled,
			hidden: isHidden,
			handler: function() {
				var win = arm || Ext.getCmp(win_id);
				if (win) {
					if ( getWnd('swEvnNotifyRegisterPalliatListWindow').isVisible() ) {
						getWnd('swEvnNotifyRegisterPalliatListWindow').hide();
					}
					getWnd('swEvnNotifyRegisterPalliatListWindow').show({userMedStaffFact: win.userMedStaffFact});
				}
			}
		};
		return cfg;
	},
	/**
	 * Есть ли у пользователя промеда группа для работы регистром по ВЗН (7-нозологиям)
	 * @return {Boolean}
	 */
	isVznRegistryOperator: function() {
		return (String(getGlobalOptions().groups).indexOf('VznRegistry', 0) >= 0);
	},
	/**
	 * Есть ли у пользователя промеда группа для экспорта в федеральный регистр по ВЗН
	 * @return {Boolean}
	 */
	isVznExportOperator: function() {
		return (String(getGlobalOptions().groups).indexOf('VznRegistry', 0) >= 0
			/*|| String(getGlobalOptions().groups).indexOf('OuzUser', 0) >= 0
			//|| String(getGlobalOptions().groups).indexOf('OrgUser', 0) >= 0
			|| String(getGlobalOptions().groups).indexOf('OuzAdmin', 0) >= 0
			|| String(getGlobalOptions().groups).indexOf('OrgAdmin', 0) >= 0*/
		);
	},
	/**
	 * @param {String} win_id
	 * @param {sw.Promed.BaseForm} arm
	 * @returns {Object}
	 */
	getVznBtnConfig: function(win_id, arm, withoutIcon) {
		var self = this,
			isDisabled = (false == self.isVznExportOperator()),
			isHidden = (false == self.isAllow('nolos'));
		var cfg = {
			tooltip: langs('Регистр по ВЗН'),
			text: langs('Регистр по ВЗН'),
			iconCls: withoutIcon ? '' : 'doc-reg16',
			disabled: isDisabled,
			hidden: isHidden,
			handler: function() {
				var win = arm || Ext.getCmp(win_id);
				if (win) {
					if ( getWnd('swPersonRegisterNolosListWindow').isVisible() ) {
						getWnd('swPersonRegisterNolosListWindow').hide();
					}
					getWnd('swPersonRegisterNolosListWindow').show({userMedStaffFact: win.userMedStaffFact});
				}
			}/*,
			menu: new Ext.menu.Menu({
				items: [{
					tooltip: langs('Регистр по ВЗН'),
					text: langs('Регистр по ВЗН'),
					iconCls : 'doc-reg16',
					disabled: (false == self.isVznRegistryOperator()),
					handler: function() {
						if ( getWnd('swPersonRegisterNolosListWindow').isVisible() ) {
							getWnd('swPersonRegisterNolosListWindow').hide();
						}
						getWnd('swPersonRegisterNolosListWindow').show({userMedStaffFact: win.userMedStaffFact});
					}
				}, {
					text: langs('Выгрузка 05-ФР'),
					tooltip: langs('Выгрузка 05-ФР'),
					iconCls : 'doc-reg16',
					handler: function() {
						getWnd('swPersonRegisterNolosExportWindow').show({ExportMod: '05-FR'});
					}
				}, {
					text: langs('Выгрузка 06-ФР'),
					tooltip: langs('Сведения о выписанных и отпущенных лекарственных препаратах по ВЗН'),
					iconCls : 'doc-reg16',
					handler: function() {
						getWnd('swPersonRegisterNolosExportWindow').show({ExportMod: '06-FR'});
					}
				}]
			})*/
		};
		return cfg;
	},
	/**
	 * @param {String} win_id
	 * @param {sw.Promed.BaseForm} arm
	 * @returns {Object}
	 */
	getEvnNotifyVznBtnConfig: function(win_id, arm, withoutIcon) {
		var self = this,
			isDisabled = (false),
			isHidden = (false == self.isAllow('nolos'));
		var cfg = {
			tooltip: langs('Журнал Извещений/Направлений по ВЗН'),
			text: langs('Журнал Извещений/Направлений по ВЗН'),
			iconCls: withoutIcon ? '' : 'journal16',
			disabled: isDisabled,
			hidden: isHidden,
			handler: function() {
				var win = arm || Ext.getCmp(win_id);
				if (win) {
					if ( getWnd('swEvnNotifyRegisterNolosListWindow').isVisible() ) {
						getWnd('swEvnNotifyRegisterNolosListWindow').hide();
					}
					getWnd('swEvnNotifyRegisterNolosListWindow').show({userMedStaffFact: win.userMedStaffFact});
				}
			}
		};
		return cfg;
	},
	/**
	 * контроль на наличие в системе объекта «Направление на включение в регистр»
	 * @param {Object} option
	 * option.PersonRegisterType_SysNick
	 * option.Person_id
	 * option.Diag_id
	 * option.callback
	 * @return {Boolean}
	 */
	checkEvnNotifyRegisterInclude: function(option) {
		if (!option.Person_id || !option.PersonRegisterType_SysNick || typeof option.callback != 'function') {
			return false;
		}
		Ext.Ajax.request({
			url: '/?c=PersonRegister&m=checkExistsEvnNotifyRegisterInclude',
			params: {
				Person_id: option.Person_id,
				PersonRegisterType_SysNick: option.PersonRegisterType_SysNick,
				Diag_id: option.Diag_id || null
			},
			callback: function(options, success, response) {
				var result = Ext.util.JSON.decode(response.responseText);
				if (result.success) {
					option.callback(result.isDisabledCreate || false);
				} else {
					option.callback(true);
				}
			}
		});
		return true;
	},
	/**
	 * Есть ли у пользователя промеда группа для работы регистром по суицидам
	 * @return {Boolean}
	 */
	isSuicideRegistryOperator: function() {
		return (String(getGlobalOptions().groups).indexOf('SuicideRegistry', 0) >= 0);
	},
	/**
	 * Есть ли у пользователя промеда группа для работы регистром по паллиативной помощи
	 * @return {Boolean}
	 */
	isPalliatRegistryOperator: function() {
		return (String(getGlobalOptions().groups).indexOf('RegistryPalliatCare', 0) >= 0);
	},
	/**
	 * Добавить в регистр
	 * @param {Object} conf
	 * conf.person
	 * conf.PersonRegisterType_SysNick
	 * conf.MorbusType_SysNick
	 * conf.Morbus_id
	 * conf.EvnNotifyBase_id
	 * conf.Diag_id
	 * conf.callback
	 * @return {Boolean}
	 */
	create: function(conf) {
		if (typeof conf != 'object'
			|| Ext.isEmpty(conf.PersonRegisterType_SysNick)
		) {
			sw.swMsg.alert(langs('Ошибка'), langs('Отсутствуют обязательные параметры'));
			return false;
		}
		var me = this;
		if (!me.isAllow(conf.PersonRegisterType_SysNick)) {
			sw.swMsg.alert(langs('Ошибка'), langs('Работа с регистром недоступна'));
			return false;
		}
		if ( !conf.PersonRegisterType_SysNick.inlist(['orphan','prof']) ) {
			sw.swMsg.alert(langs('Ошибка'), langs('Нельзя добавить в регистр указанного типа'));
			return false;
		}
		if ('orphan' == conf.PersonRegisterType_SysNick && !me.isOrphanRegistryOperator()) {
			sw.swMsg.alert(langs('Ошибка'), langs('Нет прав для добавления в регистр'));
			return false;
		}

		if( typeof conf.person != 'object' || Ext.isEmpty(conf.person)) {
			if ( getWnd('swPersonSearchWindow').isVisible() ) {
				getWnd('swPersonSearchWindow').hide();
			}
			getWnd('swPersonSearchWindow').show({
				onSelect: function(person_data) {
					getWnd('swPersonSearchWindow').hide();
					conf.person = person_data;
					me.create(conf);
				},
				searchMode: conf.searchMode || 'all'
			});
			return false;
		}

		if (getWnd('swPersonRegisterCreateWindow').isVisible()) {
			getWnd('swPersonRegisterCreateWindow').hide();
		}

		getWnd('swPersonRegisterCreateWindow').show({
			PersonRegister_id: null
			,PersonRegisterType_SysNick: conf.PersonRegisterType_SysNick
			,MorbusType_SysNick: conf.MorbusType_SysNick || null
			,Person_id: conf.person.Person_id
			,callback: conf.callback || Ext.emptyFn
			,Morbus_id: conf.Morbus_id || null
			,Diag_id: conf.Diag_id || null
		});
		return true;
	},
	/** Исключить из регистра
	 *
	 * @param {Object} conf
	 * conf.PersonRegister_id
	 * conf.Person_id
	 * conf.Diag_Name
	 * conf.PersonRegister_setDate
	 * conf.callback
	 * conf.MorbusType_SysNick
	 * conf.PersonRegisterType_SysNick
	 * conf.Diag_id
	 * @return {Boolean}
	 */
	doExcept: function(conf) {
		if (!conf ||
			Ext.isEmpty(conf.PersonRegister_id) ||
			Ext.isEmpty(conf.Person_id) ||
			false == Ext.isDate(conf.PersonRegister_setDate) ||
			Ext.isEmpty(conf.Diag_Name) ||
			Ext.isEmpty(conf.PersonRegisterType_SysNick)
		) {
			sw.swMsg.alert(langs('Ошибка'), langs('Отсутствуют обязательные параметры'));
			return false;
		}

		if (getWnd('swPersonRegisterExceptWindow').isVisible()) {
			getWnd('swPersonRegisterExceptWindow').hide();
		}

		getWnd('swPersonRegisterExceptWindow').show({
			PersonRegister_id: conf.PersonRegister_id
			,Person_id: conf.Person_id
			,Diag_Name: conf.Diag_Name
			,PersonRegister_setDate: conf.PersonRegister_setDate
			,Lpu_did: getGlobalOptions().lpu_id
			,MedPersonal_did: getGlobalOptions().medpersonal_id
			,PersonRegister_disDate: getGlobalOptions().date
			,PersonRegisterType_SysNick: conf.PersonRegisterType_SysNick
			,MorbusType_SysNick: conf.MorbusType_SysNick || null
			,callback: conf.callback || Ext.emptyFn
			,Diag_id: conf.Diag_id
		});
		return true;
	},
	/** Включить в регистр по Извещению
	 *
	 * @param {Object} conf
	 * conf.EvnNotifyBase_id -
	 * conf.Diag_id -
	 * conf.Person_id -
	 * conf.PersonRegisterType_SysNick -
	 * conf.MorbusType_SysNick -
	 * conf.Morbus_id -
	 * conf.ownerWindow -
	 * conf.callback
	 * conf.disableQuestion
	 * conf.question
	 * @return {void}
	 */
	doInclude: function(conf) {
		if (
			Ext.isEmpty(conf) ||
			typeof conf.ownerWindow != 'object' ||
			Ext.isEmpty(conf.EvnNotifyBase_id) ||
			Ext.isEmpty(conf.Person_id) ||
			Ext.isEmpty(conf.PersonRegisterType_SysNick)
		) {
			sw.swMsg.alert(langs('Ошибка'), langs('Отсутствуют обязательные параметры'));
		}
		if (!conf.question) {
			conf.question = langs('Включить данные по выбранному Извещению в регистр?');
		}
		var includeRequest = function(conf) {
			var loadMask = new Ext.LoadMask(conf.ownerWindow.getEl(), {msg: "Подождите, идет сохранение..."});
			var params = {
				PersonRegister_id: null
				,PersonRegisterType_SysNick: conf.PersonRegisterType_SysNick
				,EvnNotifyBase_id: conf.EvnNotifyBase_id
				,Person_id: conf.Person_id
				,Diag_id: conf.Diag_id || null
				,MorbusType_SysNick: conf.MorbusType_SysNick || null
				,Morbus_id: conf.Morbus_id || null
				,Lpu_did:conf.Lpu_did
				,PersonRegister_setDate: getGlobalOptions().date
				,Lpu_iid: getGlobalOptions().lpu_id || conf.Lpu_did
				,MedPersonal_iid: getGlobalOptions().medpersonal_id || conf.MedPersonal_id
				,Mode: conf.Mode || null
			};
			loadMask.show();
			Ext.Ajax.request({
				failure:function (response, options) {
					loadMask.hide();
					sw.swMsg.alert(langs('Ошибка'), langs('Не удалось получить данные с сервера'));
				},
				params: params,
				success:function (response, options) {
					loadMask.hide();
					if ( response && typeof response.responseText == 'string' && response.responseText.length > 0 ) {
						var result  = Ext.util.JSON.decode(response.responseText);
						if ( result.success ) {
							if ( typeof conf.callback == 'function' ) {
								conf.callback(result);
							}
							return true;
						}
						if (result.Alert_Msg && result.Error_Msg == 'YesNo') {
							var buttons = {
								yes: (parseInt(result.PersonRegisterOutCause_Code) == 3) ? langs('Новое') : langs('Да'),
								no: (parseInt(result.PersonRegisterOutCause_Code) == 3) ? langs('Предыдущее') : langs('Нет')
							};
							if (parseInt(result.PersonRegisterOutCause_Code) == 3) {
								buttons.cancel = langs('Отмена');
							}
							sw.swMsg.show(
							{
								buttons: buttons,
								fn: function( buttonId )
								{
									var mode;
									if ( buttonId == 'yes' && result.Yes_Mode) {
										mode = result.Yes_Mode
									} else if ( buttonId == 'no' && result.No_Mode) {
										mode = result.No_Mode
									}
									if (mode) {
										if ( mode.inlist(['homecoming','relapse']) ) {
											// Вернуть пациента в регистр
											sw.Promed.personRegister.back({
												PersonRegister_id: result.PersonRegister_oid
												,PersonRegisterType_SysNick: conf.PersonRegisterType_SysNick
												,EvnNotifyBase_id: conf.EvnNotifyBase_id
												,Diag_id: conf.Diag_id || null
												,Morbus_id: conf.Morbus_id || null
												,ownerWindow: conf.ownerWindow
												,callback: conf.callback
											});
										} else {
											conf.Mode = mode;
											includeRequest(conf);
										}
									}
								},
								msg: result.Alert_Msg,
								title: langs('Вопрос')
							});
							return true;
						}
						if (!result.Error_Msg) {
							sw.swMsg.alert(langs('Ошибка'), langs('При сохранении произошли ошибки [Тип ошибки: 1]'));
						}
						return false;
					}
					return false;
				},
				url:'/?c=PersonRegister&m=doInclude'
			});
		};

		if (conf.disableQuestion) {
			includeRequest(conf);
		} else {
			sw.swMsg.show(
			{
				buttons: Ext.Msg.YESNO,
				fn: function( buttonId )
				{
					if ( buttonId == 'yes' )
					{
						includeRequest(conf);
					}
				},
				msg: conf.question,
				title: langs('Вопрос')
			});
		}
	},
	/** Создание списка причин не включения в регистр
	 *
	 * @param {Object} option
	 * option.id - id списка
	 * option.ownerWindow -
	 * option.getParams -
	 * option.onCreate
	 * option.callback
	 * @return {void}
	 */
	createPersonRegisterFailIncludeCauseMenu: function(option) {
		if(
			typeof option != 'object' ||
			typeof option.getParams != 'function' ||
			typeof option.onCreate != 'function'
		) {
			sw.swMsg.alert(langs('Ошибка'), langs('Создание списка причин не включения в регистр. Отсутствуют обязательные параметры'));
		}
		if(!this.PersonRegisterFailIncludeCauseStore) {
			this.PersonRegisterFailIncludeCauseStore = new Ext.db.AdapterStore({
				autoLoad: false,
				dbFile: 'Promed.db',
				fields: [
					{name: 'PersonRegisterFailIncludeCause_Name', type: 'string'},
					{name: 'PersonRegisterFailIncludeCause_SysNick', type: 'string'},
					{name: 'PersonRegisterFailIncludeCause_Code', type: 'int'},
					{name: 'PersonRegisterFailIncludeCause_id', type: 'int'}
				],
				key: 'PersonRegisterFailIncludeCause_id',
				sortInfo: {field: 'PersonRegisterFailIncludeCause_Code'},
				tableName: 'PersonRegisterFailIncludeCause'
			});
		}

		this.PersonRegisterFailIncludeCauseStore.removeAll();
		var conf = {
			callback: function(){
				var menu = new Ext.menu.Menu({id: option.id || 'menuPersonRegisterFailIncludeCause'});
				this.PersonRegisterFailIncludeCauseStore.each(function(record) {
					menu.add({text: record.get('PersonRegisterFailIncludeCause_Name'), PersonRegisterFailIncludeCause_Code: record.get('PersonRegisterFailIncludeCause_Code'), PersonRegisterFailIncludeCause_id:record.get('PersonRegisterFailIncludeCause_id'), iconCls: '', handler: function() {
						var params = option.getParams();
						if (typeof params == 'object') {
							if (params.RegisterType && params.RegisterType.inlist(['onko','palliat'])) {
								option.PersonRegisterFailIncludeCause_id = this.PersonRegisterFailIncludeCause_id;
								option.EvnNotifyBase_id = params.EvnNotifyBase_id;
								option.PersonRegisterType_SysNick = params.PersonRegisterType_SysNick || null;
								option.MedPersonal_id = params.MedPersonal_id;
								option.Lpu_id = params.Lpu_id;
								var formParams = {};
								formParams.option = option;
								formParams.action = 'add';
								formParams.callback = function(data){
									sw.Promed.personRegister.notInclude(data);
								}
								getWnd('swEvnOnkoNotifyNotIncludeCommentWindow').show(formParams);
							} else {
								option.PersonRegisterFailIncludeCause_id = this.PersonRegisterFailIncludeCause_id;
								option.EvnNotifyBase_id = params.EvnNotifyBase_id;
								option.PersonRegisterType_SysNick = params.PersonRegisterType_SysNick || null;
								option.MedPersonal_id = params.MedPersonal_id;
								option.Lpu_id = params.Lpu_id;
								sw.Promed.personRegister.notInclude(option);
							}
						}
					}});
				});
				option.onCreate(menu);
			}.createDelegate(this)
		};
		this.PersonRegisterFailIncludeCauseStore.load(conf);

	},
	/** Не включать в регистр. Сохранение причины не включения в регистр
	 *
	 * @param {Object} conf
	 * conf.EvnNotifyBase_id -
	 * conf.PersonRegisterFailIncludeCause_id -
	 * conf.ownerWindow -
	 * conf.callback
	 * @return {void}
	 */
	notInclude: function(conf) {
		if(
			typeof conf != 'object' ||
			typeof conf.ownerWindow != 'object' ||
			Ext.isEmpty(conf.PersonRegisterFailIncludeCause_id) ||
			Ext.isEmpty(conf.EvnNotifyBase_id)
		) {
			sw.swMsg.alert(langs('Ошибка'), langs('Не включать в регистр. Отсутствуют обязательные параметры'));
		}
		var loadMask = new Ext.LoadMask(conf.ownerWindow.getEl(), {msg: "Подождите, идет сохранение..."});
		var params = {
			EvnNotifyBase_id: conf.EvnNotifyBase_id
			,PersonRegisterType_SysNick: conf.PersonRegisterType_SysNick
			,PersonRegisterFailIncludeCause_id: conf.PersonRegisterFailIncludeCause_id
			,Lpu_niid: getGlobalOptions().lpu_id || conf.Lpu_id
			,MedPersonal_niid: getGlobalOptions().medpersonal_id || conf.MedPersonal_id
			,EvnOnkoNotify_Comment: conf.EvnOnkoNotify_Comment || null
		};
		loadMask.show();
		Ext.Ajax.request({
			failure:function (response, options) {
				loadMask.hide();
				sw.swMsg.alert(langs('Ошибка'), langs('Не удалось получить данные с сервера'));
			},
			params: params,
			success:function (response, options) {
				loadMask.hide();
				if(typeof conf.callback == 'function') {
					conf.callback();
				}
			},
			url:'/?c=PersonRegister&m=notinclude'
		});
	},
	/** Включить в регистр по Извещению
	 *
	 * @param {Object} conf
	 * conf.EvnNotifyBase_id -
	 * conf.Diag_id -
	 * conf.Person_id -
	 * conf.MorbusType_SysNick -
	 * conf.Morbus_id -
	 * conf.ownerWindow -
	 * conf.callback
	 * conf.disableQuestion
	 * conf.question
	 * @return {void}
	 */
	include: function(conf) {
		if(
			typeof conf != 'object' ||
			typeof conf.ownerWindow != 'object' ||
			Ext.isEmpty(conf.EvnNotifyBase_id) ||
			Ext.isEmpty(conf.Diag_id) ||
			Ext.isEmpty(conf.Person_id) ||
			Ext.isEmpty(conf.MorbusType_SysNick)
		) {
			sw.swMsg.alert(langs('Ошибка'), langs('Отсутствуют обязательные параметры'));
		}
		if(!conf.question) {
			conf.question = langs('Включить данные по выбранному Извещению в регистр?');
		}
		var includeRequest = function(conf) {
			var loadMask = new Ext.LoadMask(conf.ownerWindow.getEl(), {msg: "Подождите, идет сохранение..."});
			var params = {
				PersonRegister_id: null
				,EvnNotifyBase_id: conf.EvnNotifyBase_id
				,Person_id: conf.Person_id
				,Diag_id: conf.Diag_id
				,PersonRegisterType_SysNick: conf.PersonRegisterType_SysNick
				,MorbusType_SysNick: conf.MorbusType_SysNick
				,Morbus_id: conf.Morbus_id || null
				,PersonRegister_setDate: getGlobalOptions().date
				,Lpu_iid: getGlobalOptions().lpu_id
				,MedPersonal_iid: getGlobalOptions().medpersonal_id
				,Mode: conf.Mode || null
			};
			loadMask.show();
			Ext.Ajax.request({
				failure:function (response, options) {
					loadMask.hide();
					sw.swMsg.alert(langs('Ошибка'), langs('Не удалось получить данные с сервера'));
				},
				params: params,
				success:function (response, options) {
					loadMask.hide();
					if ( response && typeof response.responseText == 'string' && response.responseText.length > 0 )
					{
						var result  = Ext.util.JSON.decode(response.responseText);
						if (result.Alert_Msg)
						{
							var buttons = {
								yes: (parseInt(result.PersonRegisterOutCause_id) == 3) ? langs('Новое') : langs('Да'),
								no: (parseInt(result.PersonRegisterOutCause_id) == 3) ? langs('Предыдущее') : langs('Нет')
							};
							if (parseInt(result.PersonRegisterOutCause_id) == 3) {
								buttons.cancel = langs('Отмена');
							}
							sw.swMsg.show(
							{
								buttons: buttons,
								fn: function( buttonId )
								{
									var mode;
									if ( buttonId == 'yes' && result.Yes_Mode)
									{
										mode = result.Yes_Mode
									}
									else if ( buttonId == 'no' && result.No_Mode)
									{
										mode = result.No_Mode
									}
									if(mode)
									{
										if ( mode.inlist(['homecoming','relapse']) )
										{
											// Вернуть пациента в регистр, удалить дату закрытия заболевания
											sw.Promed.personRegister.back({
												PersonRegister_id: result.PersonRegister_id
												,EvnNotifyBase_id: conf.EvnNotifyBase_id
												,Diag_id: conf.Diag_id
												,Morbus_id: conf.Morbus_id
												,ownerWindow: conf.ownerWindow
												,callback: conf.callback
											});
										}
										else
										{
											conf.Mode = mode;
											includeRequest(conf);
										}
									}
								},
								msg: result.Alert_Msg,
								title: langs('Вопрос')
							});
						}
						else if ( result.success && typeof conf.callback == 'function' )
						{
							conf.callback(result);
							return true;
						}
					}
					return false;
				},
				url:'/?c=PersonRegister&m=save'
			});
		};

		if(conf.disableQuestion)
		{
			includeRequest(conf);
		}
		else
		{
			sw.swMsg.show(
			{
				buttons: Ext.Msg.YESNO,
				fn: function( buttonId )
				{
					if ( buttonId == 'yes' )
					{
						includeRequest(conf);
					}
				},
				msg: conf.question,
				title: langs('Вопрос')
			});
		}
	},
	/** Добавить в регистр
	 *
	 * @param {Object} conf
	 * conf.person
	 * conf.MorbusType_SysNick -
	 * conf.Morbus_id -
	 * conf.EvnNotifyBase_id -
	 * conf.Diag_id -
	 * conf.callback
	 * @return {Boolean}
	 */
	add: function(conf) {
		if(
			typeof conf != 'object' ||
			Ext.isEmpty(conf.MorbusType_SysNick)
		) {
			sw.swMsg.alert(langs('Ошибка'), langs('Отсутствуют обязательные параметры'));
			return false;
		}

		if( typeof conf.person != 'object' || Ext.isEmpty(conf.person)) {
			if ( getWnd('swPersonSearchWindow').isVisible() ) {
				getWnd('swPersonSearchWindow').hide();
			}
			getWnd('swPersonSearchWindow').show({
				viewOnly: (!Ext.isEmpty(conf.viewOnly))?conf.viewOnly:false,
				onSelect: function(person_data) {
					getWnd('swPersonSearchWindow').hide();
					conf.person = person_data;
					sw.Promed.personRegister.add(conf);
				},
				searchMode: conf.searchMode || 'all'
			});
			return false;
		}

		if ( 'hepa' == conf.MorbusType_SysNick
			&& String(getGlobalOptions().groups).indexOf('HepatitisRegistry', 0) < 0
			&& (!conf.person.CmpLpu_id || conf.person.CmpLpu_id != getGlobalOptions().lpu_id)
		) {
			sw.swMsg.alert(langs('Сообщение'), langs('Добавление в регистр возможно только для пациентов, прикрепленных к текущей МО'));
			return false;
		}

		if ( 'geriatrics' == conf.MorbusType_SysNick
			&& !isSuperAdmin() && !isUserGroup('GeriatryRegistryFullAccess')
			&& (!conf.person.CmpLpu_id || conf.person.CmpLpu_id != getGlobalOptions().lpu_id)
		) {
			sw.swMsg.alert(langs('Сообщение'), langs('Добавление в регистр возможно только для пациентов, прикрепленных к текущей МО'));
			return false;
		}
		if ( 'geriatrics' == conf.MorbusType_SysNick && swGetPersonAge(conf.person.Person_Birthday) <= 60 ) {
			sw.swMsg.alert(langs('Сообщение'), langs('Добавление в регистр возможно только для пациентов не моложе 60 лет'));
			return false;
		}
        //перенёс на строки 10891 10892 10893
		//if (getWnd('swPersonRegisterIncludeWindow').isVisible()) {
		//	getWnd('swPersonRegisterIncludeWindow').hide();
		//}
        if(conf.MorbusType_id && conf.MorbusType_id.inlist([84,88,89,50,19,110,111,112,113]) && (!getRegionNick().inlist(['kz']))){
            var wnd = 'swPersonRegisterIncludeWindowUfa';
        }
        else{
            var wnd = 'swPersonRegisterIncludeWindow';
        }

		if (getWnd(wnd).isVisible()) {
			getWnd(wnd).hide();
		}

		getWnd(wnd).show({
			PersonRegister_id: null
			,MorbusType_SysNick: conf.MorbusType_SysNick
			,PersonRegisterType_SysNick: conf.PersonRegisterType_SysNick
			,registryType: conf.registryType
			,Person_id: conf.person.Person_id
			,Lpu_iid: getGlobalOptions().lpu_id
			,MedPersonal_iid: getGlobalOptions().medpersonal_id
			,PersonRegister_setDate: getGlobalOptions().date
			,callback: conf.callback || Ext.emptyFn
			,Morbus_id: conf.Morbus_id || null
            ,MorbusType_id: conf.MorbusType_id || null
			,EvnNotifyBase_id: conf.EvnNotifyBase_id || null
			,Diag_id: conf.Diag_id || null
			,PersonEvn_id: conf.person.PersonEvn_id
			,Server_id: conf.person.Server_id
			,Person_Firname: conf.person.Person_Firname
			,Person_Secname: conf.person.Person_Secname
			,Person_Surname: conf.person.Person_Surname
			,Person_Birthday: new Date(conf.person.Person_Birthday).dateFormat('d.m.Y')
		});
		return true;
	},
	/** Исключить из регистра
	 *
	 * @param {Object} conf
	 * conf.PersonRegister_id
	 * conf.Person_id
	 * conf.Diag_Name
	 * conf.PersonRegister_setDate
	 * conf.callback
	 * conf.MorbusType_SysNick
	 * @return {Boolean}
	 */
	out: function(conf) {
		if(
			typeof conf != 'object' ||
			Ext.isEmpty(conf.PersonRegister_id) ||
			Ext.isEmpty(conf.Person_id) ||
			Ext.isEmpty(conf.PersonRegister_setDate) ||
			Ext.isEmpty(conf.Diag_Name) ||
			Ext.isEmpty(conf.MorbusType_SysNick)
		) {
			sw.swMsg.alert(langs('Ошибка'), langs('Отсутствуют обязательные параметры'));
			return false;
		}

		if (getWnd('swPersonRegisterOutWindow').isVisible()) {
			getWnd('swPersonRegisterOutWindow').hide();
		}

		getWnd('swPersonRegisterOutWindow').show({
			PersonRegister_id: conf.PersonRegister_id
			,Person_id: conf.Person_id
			,Diag_Name: conf.Diag_Name
			,PersonRegister_setDate: conf.PersonRegister_setDate
			,Lpu_did: getGlobalOptions().lpu_id
			,MedPersonal_did: getGlobalOptions().medpersonal_id
			,PersonRegister_disDate: getGlobalOptions().date
			,MorbusType_SysNick: conf.MorbusType_SysNick
			,callback: conf.callback || Ext.emptyFn
		});
		return true;
	},
	/** Вернуть в регистр (после исключения из регистра по причине "выехал" или "выздоровление")
	 *
	 * @param {Object} conf
	 * conf.PersonRegister_id
	 * conf.callback
	 * conf.ownerWindow
	 * @return {void}
	 */
	back: function(conf) {
		if(
			typeof conf != 'object' ||
			typeof conf.ownerWindow != 'object' ||
			Ext.isEmpty(conf.PersonRegister_id)
		) {
			sw.swMsg.alert(langs('Ошибка'), langs('Отсутствуют обязательные параметры'));
		}

		var loadMask = new Ext.LoadMask(conf.ownerWindow.getEl(), {msg: "Подождите, идет сохранение..."});
		var params = {
			PersonRegister_id: conf.PersonRegister_id
			,PersonRegisterType_SysNick: conf.PersonRegisterType_SysNick || null
			,PersonRegister_setDate:conf.PersonRegister_setDate || null
			,EvnNotifyBase_id: conf.EvnNotifyBase_id || null
			,Diag_id: conf.Diag_id || null
			,Morbus_id: conf.Morbus_id || null
		};
		loadMask.show();
		Ext.Ajax.request({
			failure:function (response, options) {
				loadMask.hide();
				showSysMsg(langs('Не удалось получить данные с сервера'));
			},
			params: params,
			success:function (response, options) {
				loadMask.hide();
				if ( response && typeof response.responseText == 'string' && response.responseText.length > 0 )
				{
					var result  = Ext.util.JSON.decode(response.responseText);
					if ( result.success && typeof conf.callback == 'function' )
					{
						conf.callback(result);
						return true;
					}
				}
				return false;
			},
			url:'/?c=PersonRegister&m=back'
		});
		return true;
	}
}


/**
 * Набор методов для PersonPrivilege
 */
sw.Promed.PersonPrivilege = {
	checkExists: function(conf) {
		var win = conf.win;
		var addPrivilege = function(pdata) {
			var params = {
				action: 'add',
				Person_id: pdata.Person_id,
				PersonEvn_id: pdata.PersonEvn_id,
				Server_id: pdata.Server_id,
				callback: Ext.emptyFn,
				onHide: function() {
					conf.callback();
				}
			};
			getWnd('swPrivilegeEditWindow').show(params);
		};

		if (win) win.getLoadMask('Проверка информации о льготах').show();
		if (conf && conf.Person_id) {
			Ext.Ajax.request({
				callback:function (options, success, response) {
					if (win) win.getLoadMask().hide();
					if (success) {
						var response_obj = Ext.util.JSON.decode(response.responseText);
						if (response_obj.check == false) {
							sw.swMsg.show({
								buttons: {
									yes: {
										text: langs('Продолжить'),
										iconCls: 'ok16'
									},
									cancel: {
										text: langs('Отменить'),
										iconCls: 'close16'
									}
								},
								fn: function(buttonId, text, obj) {
									if ('yes' == buttonId) {
										addPrivilege(conf)
									}
									if ('cancel' == buttonId && typeof conf.callback == 'function') {
										conf.callback();
									}
								},
								icon: Ext.Msg.WARNING,
								msg: 'Для передачи данных в сервис «Бюро госпитализации» уточните наличие льготы у пациента. Если пациент имеет льготу, то добавьте информацию в систему.',
								title: 'Нет информации о льготах'
							});
						} else {
							if (typeof conf.callback == 'function') {
								conf.callback();
							}
						}
					}
				},
				params:{
					Person_id: conf.Person_id,
					EvnDirection_id: conf.EvnDirection_id || null
				},
				url:'/?c=Privilege&m=checkPersonPrivilegeExists'
			});
			return true;
		}
	}
}

/**
 * вычисляет контрольную сумму для EAN13
 */
function Ean13CalculateChecksum(code) {
	var odd = true;
	var result = 0;
	var keys1 = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9];
	var c = code.length;
	var multiplier;
	for (var i = c; i > 0; i--) {
		if (odd === true) {
			multiplier = 3;
			odd = false;
		} else {
			multiplier = 1;
			odd = true;
		}

		if (keys1.indexOf(parseInt(code[i - 1])) == -1) {
			return false;
		}

		result += keys1[parseInt(code[i - 1])] * multiplier;
	}
	result = (10 - result % 10) % 10;
	return result;
}

/**
 * Проверяет штрих-код на соответствие EAN13, возвращает массив с ошибками. Если ошибок нет - возвращает пустой массив.
 */
function Ean13CheckBarcode(barcode) {
	var error = [];
	if (barcode.length != 13) {
		error.push(langs('Длина штрих-кода должна быть равна 13 символам. (введено ') + barcode.length + ')');
	}
	if (error.length == 0) {
		var checksum = Ean13CalculateChecksum(barcode.substr(0, 12));
		if (typeof(checksum) == 'boolean') {
			error.push(langs('Ошибка расчета контрольной суммы'));
		}
		if (checksum != parseInt(barcode.substr(12, 1))) {
			error.push('Последняя, контрольная цифра должна быть ' + checksum + '. (введено ' + barcode.substr(12, 1) + ')');
		}
	}
	return error;
}

/**
 * Проверка, является ли указанный код комплексной услуги кодом профилактического посещения
 */
function isProphylaxisVizitOnly(uslugacomplex_code) {
	return new RegExp("(805|811|872|890|891|892|816|817|907|908)$").test(uslugacomplex_code);
}
/**
 * Проверка, является ли указанный код комплексной услуги кодом однократного посещения по заболеванию
 */
function isMorbusOneVizitCode(uslugacomplex_code) {
	return new RegExp("(871)$").test(uslugacomplex_code);
}
/**
 * Проверка, является ли указанный код комплексной услуги кодом однократного посещения по неотложке
 */
function isUrgentVizitCode(uslugacomplex_code) {
	return new RegExp("(824|825)$").test(uslugacomplex_code);
}
/**
 * Проверка, является ли указанный код комплексной услуги кодом многократного посещения по заболеванию
 */
function isMorbusMultyVizitCode(uslugacomplex_code) {
	return new RegExp("(836|865|866|888|889)$").test(uslugacomplex_code);
}
/**
 * Проверка, является ли указанный код комплексной услуги кодом многократного посещения по заболеванию
 */
function isMorbusGroup88VizitCode(uslugacomplex_code) {
	return new RegExp("(888|889)$").test(uslugacomplex_code);
}
/**
 * Проверка, является ли указанный код комплексной услуги кодом посещения по заболеванию
 */
function isMorbusVizitOnly(uslugacomplex_code) {
	return (isMorbusMultyVizitCode(uslugacomplex_code) || isMorbusOneVizitCode(uslugacomplex_code));
}
/**
 * Проверка, является ли указанный код комплексной услуги кодом однократного посещения
 */
function isOneVizitCode(uslugacomplex_code) {
	return (isProphylaxisVizitOnly(uslugacomplex_code) || isMorbusOneVizitCode(uslugacomplex_code) || isUrgentVizitCode(uslugacomplex_code));
}

/**
 * Компонент для профиля коек (для Самары)
 */
sw.Promed.LpuSectionBedProfile = {
	/** Асинхронное создание меню со списком профилей коек для указанного отделения
	 *
	 * option.LpuSection_id - Список только по данному отделению
	 * option.id - id меню
	 * option.getParams - функция, которая должна вернуть парамсы для записи профиля койки (LpuSectionBedProfileCur_id, LpuSection_id, EvnSection_id, PersonEvn_id, Server_id)
	 * option.onSuccess - функция, которая выполняется при успешной записи профиля койки
	 * option.callback - функция, которая выполняется при создании меню
	 * @param {Object} option
	 * @return boolean
	 */
	createListLpuSectionBedProfileMenu: function(option) {
		if (
			typeof option.getParams != 'function' ||
			typeof option.callback != 'function' ||
			typeof option.onSuccess != 'function' ||
			!option.LpuSection_id
		) {
			return false;
		}
		var params = {LpuSection_id: option.LpuSection_id, date: option.date};
		var optionParams = option.getParams();
		if(optionParams.EvnSection_id) params.EvnSection_id = optionParams.EvnSection_id;
		Ext.Ajax.request({
			url: '/?c=EvnSection&m=getLpuSectionBedProfilesByLpuSection',
			params: params,
			failure: function(response, options) {
				Ext.Msg.alert(langs('Ошибка'), langs('При получении списка профилей!'));
			},
			success: function(response, action)
			{
				if (response.responseText) {
					var result = Ext.util.JSON.decode(response.responseText);
					var menu = new Ext.menu.Menu({id: (option.id || 'menuLpuSectionBedProfile')+option.LpuSection_id});
					var ico = 'tree-rooms-none16';
					if (result) {
						for(var i = 0;i < result.length;i++) {
							menu.add({text: '<font color="red">'+result[i]['LpuSectionBedProfile_Code']+'</font>&nbsp;'+result[i]['LpuSectionBedProfile_Name']+' (V020:&nbsp;'+result[i]['LpuSectionBedProfile_fedCode']+'&nbsp;'+result[i]['LpuSectionBedProfile_fedName']+')', LpuSectionBedProfile_id:result[i]['LpuSectionBedProfile_id'], LpuSectionBedProfileLink_id:result[i]['LpuSectionBedProfileLink_id'], iconCls: ico, handler: function() {
									sw.Promed.LpuSectionBedProfile.setLpuSectionBedProfile({
										LpuSectionBedProfile_id: this.LpuSectionBedProfile_id,
										LpuSectionBedProfileLink_id: this.LpuSectionBedProfileLink_id,
										onSuccess: option.onSuccess,
										params: option.getParams()
									});
								}});
						}
					}
					option.callback(menu);
				}
				else {
					Ext.Msg.alert(langs('Ошибка'), langs('При получении списка профилей произошла ошибка! Отсутствует ответ сервера.'));
				}
			}
		});
		return true;
	},
	/** Запрос списка профилей коек	по профилям отделения (по основному и дополнительным) через стыковочную таблицу «Профиль отделения – Профиль койки».
	 *
	 *  Профиля коек, отсутствующие в таблице стыковки «Профиль отделения – Профиль койки» также доступны для выбора
	 * option.callback - функция, которая выполняется после получения списка
	 * @param {Object} option
	 * @return boolean
	 */
	getLpuSectionBedProfileLink: function(options) {
		if (options && options.params && options.params.LpuSection_id) {
			var option = options.params;
			var params = {};
			params.LpuSection_id = option.LpuSection_id;
			if(option.LpuSectionProfile_id )params.LpuSectionProfile_id = option.LpuSectionProfile_id;
			if(option.begDate ) params.begDate = option.begDate;
			if(option.endDate ) params.endDate = option.endDate;
			if(option.validityLpuSection) params.validityLpuSection = option.validityLpuSection;
			Ext.Ajax.request({
				callback:function (opt, success, response) {
					if (success) {
						var response_obj = Ext.util.JSON.decode(response.responseText);
						if (typeof options.callback == 'function') {
							options.callback(response_obj);
						}
					}
					else {
						sw.swMsg.alert(langs('Ошибка'), langs('При загрузке профилей коек отделения произошли ошибки'));
					}
				},
				params: params,
				url:'/?c=EvnSection&m=getLpuSectionBedProfileLink'
			});
			return true;
		}
		return false;
	},
	/** Запрос списка профилей коек для указанного отделения
	 *
	 * option.LpuSection_id - Список только по данному отделению
	 * option.callback - функция, которая выполняется после получения списка
	 * @param {Object} option
	 * @return boolean
	 */
	getLpuSectionBedProfilesLinkByLpuSection: function(options) {
		if (options && options.params && options.params.LpuSection_id) {
			var option = options.params,
				params = {},
				input;
			if(typeof option.LpuSection_id == 'object'){
				if(Ext.type(option.LpuSection_id) == 'nodelist' && option.LpuSection_id.length > 0)
					input = option.LpuSection_id[0];
				else
					input = params.LpuSection_id;
				params.LpuSection_id = Ext.isEmpty(input.value)?'':input.value;
			}
			else
				params.LpuSection_id = option.LpuSection_id;
			if(option.LpuSectionProfile_id )params.LpuSectionProfile_id = option.LpuSectionProfile_id;
			if(option.begDate ) params.begDate = option.begDate;
			if(option.endDate ) params.endDate = option.endDate;
			Ext.Ajax.request({
				callback:function (opt, success, response) {
					if (success) {
						var response_obj = Ext.util.JSON.decode(response.responseText);
						if (typeof options.callback == 'function') {
							options.callback(response_obj);
						}
					}
					else {
						sw.swMsg.alert(langs('Ошибка'), langs('При загрузке профилей коек отделения произошли ошибки'));
					}
				},
				params: params,
				url:'/?c=EvnSection&m=getLpuSectionBedProfilesLinkByLpuSection'
			});
			return true;
		}
		return false;
	},
	/** Запрос списка профилей коек для указанного отделения
	 *
	 * option.LpuSection_id - Список только по данному отделению
	 * option.callback - функция, которая выполняется после получения списка
	 * @param {Object} option
	 * @return boolean
	 */
	getLpuSectionBedProfilesByLpuSection: function(option) {
		if (option && option.LpuSection_id) {
			Ext.Ajax.request({
				callback:function (options, success, response) {
					if (success) {
						var response_obj = Ext.util.JSON.decode(response.responseText);
						if (typeof option.callback == 'function') {
							option.callback(response_obj);
						}
					}
					else {
						sw.swMsg.alert(langs('Ошибка'), langs('При загрузке профилей коек отделения произошли ошибки'));
					}
				},
				params:{
					LpuSection_id: option.LpuSection_id
				},
				url:'/?c=EvnSection&m=getLpuSectionBedProfilesByLpuSection'
			});
			return true;
		}
		return false;
	},
	/** Запрос списка профилей коек для указанного профиля
	 *
	 * option.LpuSectionProfile_id - Список только по данному профилю
	 * option.callback - функция, которая выполняется после получения списка
	 * @param {Object} option
	 * @return boolean
	 */
	getLpuSectionBedProfilesByLpuSectionProfile: function(option) {
		if (option && option.LpuSectionProfile_id) {
			var params = {
				LpuSectionProfile_id: option.LpuSectionProfile_id
			};
			if (option.LpuSectionBedProfile_IsChild) {
				params.LpuSectionBedProfile_IsChild = option.LpuSectionBedProfile_IsChild;
			}
			Ext.Ajax.request({
				callback: function (options, success, response) {
					if (success) {
						var response_obj = Ext.util.JSON.decode(response.responseText);
						if (typeof option.callback == 'function') {
							option.callback(response_obj);
						}
					}
					else {
						sw.swMsg.alert(langs('Ошибка'), langs('При загрузке профилей коек отделения произошли ошибки'));
					}
				},
				params: params,
				url: '/?c=EvnSection&m=getLpuSectionBedProfilesByLpuSectionProfile'
			});
			return true;
		}
		return false;
	},
	/** Запись профиля койки
	 *
	 * option.LpuSectionBedProfile_id - выбранный профиль койки
	 * option.params - параметры (LpuSectionBedProfileCur_id,LpuSection_id,EvnSection_id,PersonEvn_id,Server_id)
	 * option.onSuccess - функция, которая выполняется при успешной записи профиля койки
	 * @param {Object} option
	 * @return {void}
	 */
	setLpuSectionBedProfile: function(option) {
		if (option.LpuSectionBedProfileCur_id!=option.LpuSectionBedProfile_id) { // Если профиль койки, в которой находится пациент, отличается от выбранного профиля койки
			var params = {};
			params.LpuSectionBedProfile_id = option.LpuSectionBedProfile_id;
			params.LpuSectionBedProfileLink_fedid = option.LpuSectionBedProfileLink_id;
			params.LpuSectionBedProfileCur_id = option.LpuSectionBedProfile_id;
			params.EvnSection_id = option.params.EvnSection_id;
			params.LpuSection_id = option.params.LpuSection_id;
			params.PersonEvn_id = option.params.PersonEvn_id;
			params.Server_id = option.params.Server_id;
			Ext.Ajax.request({
				url: '/?c=EvnSection&m=setLpuSectionBedProfile',
				params: params,
				failure: function(response, options) {
					Ext.Msg.alert(langs('Ошибка'), langs('При записи профиля койки произошла ошибка!'));
				},
				success: function(response, action)
				{
					if (response.responseText) {
						var answer = Ext.util.JSON.decode(response.responseText);
						if (answer.success) {
							if(typeof option.onSuccess == 'function')
								option.onSuccess(params);
						} else if (answer.Error_Message) {
							Ext.Msg.alert(langs('Ошибка'), answer.Error_Message);
						}
					}
					else {
						Ext.Msg.alert(langs('Ошибка'), langs('При записи профиля койки произошла ошибка! Отсутствует ответ сервера.'));
					}
				}
			});
		}
	}
};
sw.Promed.LpuSectionProfile = {
	/** Асинхронное создание меню со списком профилей для указанного отделения
	 *
	 * option.LpuSection_id - Список только по данному отделению
	 * option.id - id меню
	 * option.getParams - функция, которая должна вернуть парамсы для записи профиля  (LpuSectionProfileCur_id, LpuSection_id, EvnSection_id, PersonEvn_id, Server_id)
	 * option.onSuccess - функция, которая выполняется при успешной записи профиля койки
	 * option.callback - функция, которая выполняется при создании меню
	 * @param {Object} option
	 * @return boolean
	 */
	createListLpuSectionProfileMenu: function(option) {
		if (
			typeof option.getParams != 'function' ||
			typeof option.callback != 'function' ||
			typeof option.onSuccess != 'function' ||
			!option.LpuSection_id
		) {
			return false;
		}
		Ext.Ajax.request({
			url: '/?c=Common&m=loadLpuSectionProfileDopList',//getLpuSectionProfilesByLpuSection',
			params: {LpuSection_id: option.LpuSection_id, date: option.date},
			failure: function(response, options) {
				Ext.Msg.alert(langs('Ошибка'), langs('При получении списка профилей!'));
			},
			success: function(response, action)
			{
				if (response.responseText) {
					var result = Ext.util.JSON.decode(response.responseText);
					var menu = new Ext.menu.Menu({id: (option.id || 'menuLpuSectionProfile')+option.LpuSection_id});
					var ico = 'tree-rooms-none16';
					if (result) {
						for(var i = 0;i < result.length;i++) {
							menu.add({text: result[i]['LpuSectionProfile_Code']+' - '+result[i]['LpuSectionProfile_Name'], LpuSectionProfile_id:result[i]['LpuSectionProfile_id'], iconCls: ico, handler: function() {
									sw.Promed.LpuSectionProfile.setLpuSectionProfile({
										LpuSectionProfile_id: this.LpuSectionProfile_id,
										onSuccess: option.onSuccess,
										params: option.getParams()
									});
								}});
						}
					}
					option.callback(menu);
				}
				else {
					Ext.Msg.alert(langs('Ошибка'), langs('При получении списка профилей произошла ошибка! Отсутствует ответ сервера.'));
				}
			}
		});
		return true;
	},

	/** Запись профиля
	 *
	 * option.LpuSectionProfile_id - выбранный профиль
	 * option.params - параметры (LpuSectionProfileCur_id,LpuSection_id,EvnSection_id,PersonEvn_id,Server_id)
	 * option.onSuccess - функция, которая выполняется при успешной записи профиля койки
	 * @param {Object} option
	 * @return {void}
	 */
	setLpuSectionProfile: function(option) {
		if (option.LpuSectionProfileCur_id!=option.LpuSectionProfile_id) { // Если профиль койки, в которой находится пациент, отличается от выбранного профиля койки
			var params = {};
			params.EvnSection_id = option.params.EvnSection_id;
			params.LpuSection_id = option.params.LpuSection_id;
			params.PersonEvn_id = option.params.PersonEvn_id;
			params.Server_id = option.params.Server_id;
			params.LpuSectionProfile_id = option.LpuSectionProfile_id;
			params.LpuSectionProfileCur_id = option.LpuSectionProfile_id;
			Ext.Ajax.request({
				url: '/?c=EvnSection&m=setLpuSectionProfile',
				params: params,
				failure: function(response, options) {
					Ext.Msg.alert(langs('Ошибка'), langs('При записи профиля произошла ошибка!'));
				},
				success: function(response, action)
				{
					if (response.responseText) {
						var answer = Ext.util.JSON.decode(response.responseText);
						if (answer.success) {
							if(typeof option.onSuccess == 'function')
								option.onSuccess(params);
						} else if (answer.Error_Message) {
							Ext.Msg.alert(langs('Ошибка'), answer.Error_Message);
						}
					}
					else {
						Ext.Msg.alert(langs('Ошибка'), langs('При записи профиля произошла ошибка! Отсутствует ответ сервера.'));
					}
				}
			});
		}
	}
};
/**
 * Класс хранилища локального справочника общего вида
 * @param {object} config
 * string config.tableName Имя таблицы локального справочника
 * string config.typeCode 'int' or 'string'
 * boolean config.allowSysNick
 * boolean config.autoLoad
 * function config.onLoadStore
 * string config.orderBy
 * string config.prefix
 * object config.loadParams
 * @return {sw.Promed.LocalStorage}
 */
sw.Promed.LocalStorage = function (config) {
	if (!config || !config.tableName) {
		return false;
	}
	var thas = this;
	this.tableName = config.tableName;
	this.typeCode = config.typeCode || 'string';
	this.allowSysNick = config.allowSysNick || false;
	this.autoLoad = config.autoLoad || false;
	this.onLoadStore = (typeof config.onLoadStore == 'function') ? config.onLoadStore : function(){};
	this.orderBy = config.orderBy || 'Code';
	this.prefix = config.prefix || '';
	this.loadParams = config.loadParams || null;

	this.fields = [
		{name: this.tableName + '_id', mapping: this.tableName + '_id'},
		{name: this.tableName + '_Code', mapping: this.tableName + '_Code', type: this.typeCode},
		{name: this.tableName + '_Name', mapping: this.tableName + '_Name'}
	];
	if (this.allowSysNick) {
		this.fields.push({name: this.tableName + '_SysNick', mapping: this.tableName + '_SysNick'});
	}
	this._store = new Ext.db.AdapterStore({
		autoLoad: this.autoLoad,
		dbFile: 'Promed.db',
		fields: this.fields,
		key: this.tableName + '_id',
		listeners: {
			'load': function(store) {
				thas.onLoadStore();
			}
		},
		sortInfo: {
			field: this.tableName + '_' + this.orderBy
		},
		tableName: this.prefix + this.tableName
	});

	/**
	 * @see {Ext.data.Store.getCount}
	 * @return {int}
	 */
	this.getCount = function(){
		return thas._store.getCount();
	};

	/**
	 * @return {Ext.db.AdapterStore}
	 */
	this.getStore = function(){
		return thas._store;
	};

	/**
	 * @see {Ext.data.Store.removeAll}
	 * @return {sw.Promed.LocalStorage}
	 */
	this.removeAll = function(){
		thas.loadParams = null;
		thas._store.removeAll();
		return thas;
	};

	/**
	 * @see {Ext.data.Store.load}
	 * @return {sw.Promed.LocalStorage}
	 */
	this.load = function(loadParams){
		if (typeof loadParams == 'object') {
			thas.loadParams = loadParams;
		}
		thas._store.removeAll();
		thas._store.load(thas.loadParams);
		return thas;
	};

	/**
	 * Возвращает первую запись
	 * @return mixed {Ext.data.Record} или false
	 */
	this.getFirstRecord = function(){
		if (thas._store.getCount() > 0) {
			return thas._store.getAt(0);
		} else {
			return false;
		}
	};

	/**
	 * Возвращает значение ключа (идентификатор) первой записи
	 * @return mixed Идентификатор или false
	 */
	this.getValueOfKeyByFirstRecord = function(){
		var r = thas.getFirstRecord();
		if (r) {
			return r.get(thas._store.key);
		} else {
			return false;
		}
	};

	return this;
};

/**
 * Верификация документа
 */
function signDocumentVerification(options) {
		options.ownerWindow.getLoadMask(langs('Идет верификация документа...')).show();
		Ext.Ajax.request({
			url: '/?c=Signatures&m=verifySign',
			params: {
				Doc_Type: options.Doc_Type,
				Doc_id: options.Doc_id
			},
			callback: function (opt, success, response) {
				options.ownerWindow.getLoadMask().hide();
				var result = Ext.util.JSON.decode(response.responseText);
				if (result.success) {
					if (!Ext.isEmpty(result.valid)) {
						if (result.valid == 2) {
							sw.swMsg.alert(langs('Сообщение'), langs('Документ подписан и не изменялся с момента подписания'));
						} else if (result.valid == 1) {
							sw.swMsg.alert(langs('Сообщение'), langs('Документ не актуален'));
						}
					} else {
						sw.swMsg.alert(langs('Сообщение'), langs('Документ не подписан'));
					}

				options.callback(result);
				}
			}
		});
						}

/**
 * Обёртка для функции подписания документа с помощью ЭЦП
 * По умолчанию используется сервис подписания, AuthApplet или AuthApi, хранение подписей и подписанных документов в монго.
 * При options.storage = signatures используется AuthApplet/AuthApi/КриптоПро, хранение подписей в бд в таблице Signatures.
 */
function signDocEcp(options) {
	if (!options.Doc_Type || !options.Doc_id) {
		sw.swMsg.alert(langs('Ошибка'), langs('Не указаны необходимые параметры для подписания'));
		return false;
	}

		var callback = options.callback;
		var win = options.win;
		var params = {
			Doc_Type: options.Doc_Type,
			Doc_id: options.Doc_id
		};

		var doc_signtype = getOthersOptions().doc_signtype;

		// выбираем сертификат
		getWnd('swCertSelectWindow').show({
			signType: doc_signtype,
			callback: function (cert) {
				params.SignedToken = cert.Cert_Base64;

				if (win) {
					win.getLoadMask('Получение данных для подписи').show();
				}
				Ext.Ajax.request({
					url: '/?c=Signatures&m=getDocHash',
					params: params,
					callback: function (options, success, response) {
						if (win) {
							win.getLoadMask().hide();
						}
						if (success) {
							var result = Ext.util.JSON.decode(response.responseText);
							if (result.Base64ToSign) {
								if (doc_signtype && doc_signtype.inlist(['authapi', 'authapitomee'])) {
									// хотим подписать с помощью AuthApi
									sw.Applets.AuthApi.signText({
										win: win,
										text: result.Base64ToSign,
										Cert_Thumbprint: cert.Cert_Thumbprint,
										callback: function (sSignedData) {
											params.signType = doc_signtype;
											params.Hash = result.Hash;
											params.SignedData = sSignedData;
											if (win) {
												win.getLoadMask('Подписание').show();
											}
											Ext.Ajax.request({
												url: '/?c=Signatures&m=signDoc',
												params: params,
												callback: function (options, success, response) {
													if (win) {
														win.getLoadMask().hide();
													}
													if (success) {
														var result = Ext.util.JSON.decode(response.responseText);
														if (result.success) {
															callback({
															success: true,
															Signatures_id: result.Signatures_id
															});
														}
													}
												}
											});
										}
									});
							} else if (doc_signtype && doc_signtype == 'authapplet') {
									// хотим подписать с помощью AuthApplet
									sw.Applets.AuthApplet.signText({
										text: result.Base64ToSign,
										Cert_Thumbprint: cert.Cert_Thumbprint,
										callback: function (sSignedData) {
										params.signType = doc_signtype;
											params.Hash = result.Hash;
											params.SignedData = sSignedData;
											if (win) {
												win.getLoadMask('Подписание').show();
											}
											Ext.Ajax.request({
												url: '/?c=Signatures&m=signDoc',
												params: params,
												callback: function (options, success, response) {
													if (win) {
														win.getLoadMask().hide();
													}
													if (success) {
														var result = Ext.util.JSON.decode(response.responseText);
														if (result.success) {
															callback({
															success: true,
															Signatures_id: result.Signatures_id
															});
														}
													}
												}
											});
										}
									});
							} else {
								// подпись с помощью КриптоПро
								sw.Applets.CryptoPro.signRawText({
									text: result.Base64ToSign,
									Cert_Thumbprint: cert.Cert_Thumbprint,
									callback: function(sSignedData) {
										params.signType = 'cryptopro';
										params.Hash = result.Hash;
										params.SignedData = sSignedData;
										if (win) {
											win.getLoadMask('Подписание').show();
										}
										Ext.Ajax.request({
											url: '/?c=Signatures&m=signDoc',
											params: params,
											callback: function (options, success, response) {
												if (win) {
													win.getLoadMask().hide();
												}
												if (success) {
													var result = Ext.util.JSON.decode(response.responseText);
													if (result.success) {
														callback({
															success: true,
															Signatures_id: result.Signatures_id
														});
													}
												}
											}
										});
									},
									error: function() {

									}
								});
							}
						}
					}
				}
			});
		}
	});
}

function twoDecimalsRenderer(v) {
	if ( Ext.isEmpty(v) ) {
		return v;
	}

	v = Math.round((v - 0) * 100) / 100;
	v = v == Math.floor(v) ? v + ".00" : v * 10 == Math.floor(v * 10) ? v + "0" : v;
	v = String(v);
	var ps = v.split(".");
	var whole = ps[0];
	var sub = ps[1] ? "." + ps[1] : ".00";
	var r = /(\d+)(\d{3})/;
	while (r.test(whole)) {
		whole = whole.replace(r, "$1,$2");
	}
	v = whole + sub;
	if (v.charAt(0) == "-") {
		return v.substr(1);
	}
	return v;
}

function snilsRenderer(v) {
	if ( Ext.isEmpty(v) ) {
		return v;
	}
	var inp = v.toString().substr(0, 11);
	var regexp = /^(\d{3})(\d{3})(\d{3})(\d{2})$/;
	if ( !regexp.test(inp) ) {
		return '';
	}
	return inp.replace(regexp, '$1-$2-$3-$4');
}

/**
 * Класс льготного рецепта
 * @param {object} config
 * int config.EvnRecept_id
 * @return {sw.Promed.EvnRecept}
 */
sw.Promed.EvnRecept = function (config) {
	this.EvnRecept_id = config.EvnRecept_id || null;

	var thas = this;
	this._options = null;

	/**
	 * Возвращает настройки рецептов
	 * @return {object} Настройки рецептов
	 */
	this.getOptions = function(){
		if (!thas._options) {
			thas._options = Ext.globalOptions.recepts;
		}
		return thas._options;
	};

	/**
	 * Печатает рецепт
	 * @param {int} evn_recept_id
	 * @return {sw.Promed.EvnRecept}
	 */
	this.print = function(evn_recept_id){
		if (!evn_recept_id) {
			evn_recept_id = thas.EvnRecept_id;
		}

		// Получим тип рецепта https://redmine.swan.perm.ru/issues/31345
		var params = {};
		params.EvnRecept_id = evn_recept_id;
		var that = thas;
        var region_nick = getRegionNick();
		saveEvnReceptIsPrinted({
			allowQuestion: false
			,callback: function(success) {
				if ( success == true ) {
					Ext.Ajax.request({
						url: '/?c=EvnRecept&m=getReceptForm',
						params: params,
						callback: function(options, success, response) {
							if (success) {
								var result = Ext.util.JSON.decode(response.responseText);
                                var ReceptForm_id = 9;
								var ReceptForm_Code = '148';
                                ReceptForm_id = result.ReceptForm_id*1;
								ReceptForm_Code = result.ReceptForm_Code;
								var evn_recept_set_date = result.EvnRecept_setDate;
								if (thas.getOptions().print_extension == 3) {
									if(ReceptForm_Code != '1-МИ')
										window.open(C_EVNREC_PRINT_DS, '_blank');
									window.open(C_EVNREC_PRINT + '&EvnRecept_id=' + evn_recept_id, '_blank');
								} else {
									Ext.Ajax.request({
										url: '/?c=EvnRecept&m=getPrintType',
										callback: function(options, success, response) {
											if (success) {
												var result = Ext.util.JSON.decode(response.responseText);
												var PrintType = '';
												switch(result.PrintType) {
													case '1':
														PrintType = 2;
														break;
													case '2':
														PrintType = 3;
														break;
													case '3':
														PrintType = '';
														break;
												}

                                                switch (ReceptForm_id) {
                                                    case 2: //1-МИ
                                                        if(result.CopiesCount == 1){
                                                            printBirt({
                                                                'Report_FileName': 'EvnReceptPrint4_1MI.rptdesign',
                                                                'Report_Params': '&paramEvnRecept=' + evn_recept_id,
                                                                'Report_Format': 'pdf'
                                                            });
                                                        } else {
                                                            if(PrintType == '') {
                                                                printBirt({
                                                                    'Report_FileName': 'EvnReceptPrint1_1MI.rptdesign',
                                                                    'Report_Params': '&paramEvnRecept=' + evn_recept_id,
                                                                    'Report_Format': 'pdf'
                                                                });
                                                            } else {
                                                                printBirt({
                                                                    'Report_FileName': 'EvnReceptPrint' + PrintType + '_1MI.rptdesign',
                                                                    'Report_Params': '&paramEvnRecept=' + evn_recept_id,
                                                                    'Report_Format': 'pdf'
                                                                });
                                                            }
                                                        }
                                                        break;
                                                    case 9: //148-1/у-04(л)
                                                        if (region_nick == 'msk') {
                                                            printBirt({
                                                                'Report_FileName': 'EvnReceptPrint_148_1u04_2InA4_2020.rptdesign',
                                                                'Report_Params': '&paramEvnRecept=' + evn_recept_id,
                                                                'Report_Format': 'pdf'
                                                            });
                                                        } else {
                                                            //игнорируем настройки и печатаем сразу обе стороны
                                                            printBirt({
                                                                'Report_FileName': 'EvnReceptPrint_148_1u04_2InA4_2019.rptdesign',
                                                                'Report_Params': '&paramEvnRecept=' + evn_recept_id,
                                                                'Report_Format': 'pdf'
                                                            });
                                                        }
                                                        printBirt({
                                                            'Report_FileName': 'EvnReceptPrint_148_1u04_2InA4_2019Oborot.rptdesign',
                                                            'Report_Params': '&paramEvnRecept=' + evn_recept_id,
                                                            'Report_Format': 'pdf'
                                                        });
                                                        break;
                                                    case 10: //148-1/у-04 (к)
                                                        //игнорируем настройки и печатаем сразу обе стороны
                                                        printBirt({
                                                            'Report_FileName': 'EvnReceptPrint_148_1u04k_2InA4_2019.rptdesign',
                                                            'Report_Params': '&paramEvnRecept=' + evn_recept_id,
                                                            'Report_Format': 'pdf'
                                                        });
                                                        printBirt({
                                                            'Report_FileName': 'EvnReceptPrint_148_1u04_2InA4_2019Oborot.rptdesign',
                                                            'Report_Params': '&paramEvnRecept=' + evn_recept_id,
                                                            'Report_Format': 'pdf'
                                                        });
                                                        break;
                                                    case 1: //148-1/у-04(л), 148-1/у-06(л)
                                                        if (region_nick == 'msk') {
                                                            printBirt({
                                                                'Report_FileName': 'EvnReceptPrint_148_1u04_4InA4_2019.rptdesign',
                                                                'Report_Params': '&paramEvnRecept=' + evn_recept_id,
                                                                'Report_Format': 'pdf'
                                                            });
                                                            break; //в пределах условия для того, чтобы в других регионах выполнение проваливалось в дефолтную секцию
                                                        }
                                                    default:
                                                        var ReportName = 'EvnReceptPrint' + PrintType;
                                                        var ReportNameOb = 'EvnReceptPrintOb' + PrintType;
                                                        /*if(result.CopiesCount == 1)
                                                        {
                                                            ReportName = 'EvnReceptPrint2_2015';
                                                            ReportNameOb = 'EvnReceptPrintOb2_2015';
                                                        }*/
                                                        if(result.CopiesCount == 1) {
                                                            if(evn_recept_set_date >= '2016-07-30') {
                                                                ReportName = 'EvnReceptPrint4_2016_new';
                                                            } else if(evn_recept_set_date >= '2016-01-01') {
                                                                ReportName = 'EvnReceptPrint4_2016';
                                                            } else {
                                                                ReportName = 'EvnReceptPrint2_2015';
                                                            }
                                                            ReportNameOb = 'EvnReceptPrintOb2_2015';
                                                        } else {
                                                            if (evn_recept_set_date >= '2016-07-30') {
                                                                ReportName = ReportName + '_2016_new';
															} else if(evn_recept_set_date >= '2016-01-01') {
                                                                ReportName = ReportName + '_2016';
															}
                                                        }
                                                        if (thas.getOptions().print_extension == 1) {
                                                            printBirt({
                                                                'Report_FileName': ReportNameOb+'.rptdesign',
                                                                'Report_Params': '&paramEvnRecept=' + evn_recept_id + '&paramProMedPort=' + result.server_port + '&paramProMedProto=' + result.server_http,
                                                                'Report_Format': 'pdf'
                                                            });
                                                        }
                                                        if(result.server_port != null) {
                                                            printBirt({
                                                                'Report_FileName': ReportName + '.rptdesign',
                                                                'Report_Params': '&paramEvnRecept=' + evn_recept_id + '&paramProMedPort=' + result.server_port + '&paramProMedProto=' + result.server_http,
                                                                'Report_Format': 'pdf'
                                                            });
                                                        } else {
                                                            printBirt({
                                                                'Report_FileName': ReportName + '.rptdesign',
                                                                'Report_Params': '&paramEvnRecept=' + evn_recept_id + '&paramProMedProto=' + result.server_http,
                                                                'Report_Format': 'pdf'
                                                            });
                                                        }
                                                        break;
                                                }
											}
										}
									});
								}
								return thas;
							}
						}
					});
				}
				else {
					sw.swMsg.alert('Ошибка', 'Ошибка при сохранении признака распечатывания рецепта');
				}
			}.createDelegate(that)
			,Evn_id: evn_recept_id
		});

	};

	return this;
};

/*
 * Глобальный объект для работы с Dicom просмотровщиком
 **/
sw.Promed.DicomViewer = {
	//options, передаваемые при генерации вьюхи
	opts: {},
	//Префикс для идентификатора обертки просмотровщика
	_wraperIdPrefix: 'EvnUslugaparFunctRequest_wraper_',
	//Идентификатор обертки просмотровщика
	wraperID:'',
	//Объект прогрессбара
	progressbar: {},
	//Флаг указывающий на загрузку изображений для видеопетли
	isLoading: false,
	//Флаг остановки загрузки изображений для видеопетли
	stopLoading: false,
	//Функция, выполняемая после остановки загрузки изображений для видеопетли
	stopLoadingCallback: Ext.emptyFn,
	//Глобальный интервал для setInterval
	interval:null,
	//jQuery Объект для изображения
	img: null,
	//Номер текущего отображаемого изображения в видеопетле
	current_frame_num: null,
	//Путь до изображения без значения параметра frameNumber
	srcWithoutFrameNumber: null,

	/*
	 * Обработчик клика на миниизображение в сайдбаре просмотровщика Dicom
	 * @opts - объект:
	 *		@id - идентификатор исследования
	 *		@sidebar_postfix - постфикс класса сайдбара
	 *		@src - путь до изображения
	 *		@imgObj - ссылка на объект кликнутого изображения в сайдабре
	 */

	sidebarClick: function(opts) {

		var cb = function(opts) {
			//Останавливаем проигрывание видеопетли
			this.pauseClick();

			if ( (typeof opts != 'object') || (!opts['id']) || (!opts['src']) || (typeof opts['imgObj'] != 'object')) {
				this.opts = opts;
				return false;
			}

			opts['sidebar_postfix'] = (!!opts['sidebar_postfix'])?opts['sidebar_postfix']:'',

			this.opts = opts;

			this.wraperID = '#'+this._wraperIdPrefix+opts['id'];

			$(this.wraperID+' .sidebar1'+opts['sidebar_postfix']+' .active').removeClass('active');
			$(opts['imgObj']).parent('a').addClass('active');
			$(this.wraperID+' #contentImg_'+opts['id']).attr('src',opts['src']);

			if (opts['numberOfFrames']>1) {
				//Отображаем блоки с информацией о видеопетле
				$(this.wraperID+' .loopcontainer:first').addClass('bottom_frame_title');
				$(this.wraperID+' .default-size-link').addClass('bottom_frame_title');

				$('.loopcontainer').show();
				$(this.wraperID+' .load_loop_info').show();
				$(this.wraperID+' .loop_buttons').hide();
				$('.loop_item_count').html(opts['numberOfFrames']);
				$('#progressbar').html('');

				this.load();
			} else {
				$('.loopcontainer').hide();
			}

			setTimeout(function(){
				$(this.wraperID+' .default-size-link a').attr('href',opts['src']);
				if (($(this.wraperID+' .content').width() < document.getElementById('contentImg_'+opts['id']).naturalWidth)||
					($(this.wraperID+' .content').height() < document.getElementById('contentImg_'+opts['id']).naturalHeight)) {
					$(this.wraperID+' .default-size-link').show();
				} else {
					$(this.wraperID+' .default-size-link').hide();
				}
			}.bind(this),150);
		}.bind(this);

		if (this.isLoading) {
			this.stopLoading = true;
			this.stopLoadingCallback = function(){
				cb(opts);
			}
		} else {
			cb(opts);
		}

	},
	/*
	 * Функция-обрботчик клика на play-кнопке
	 */
	playClick: function(){
		this.pauseClick();
		//Защита от бесконечного прокручивания
		setTimeout(function(){clearInterval(this.interval);}.bind(this),300000);
		this.interval = setInterval(function(){
			this.current_frame_num = (this.current_frame_num%this.opts['numberOfFrames'] + 1);
			this.img.attr('src',(this.srcWithoutFrameNumber+this.current_frame_num));
			$(this.wraperID+' .loopcontainer #current_loop_item_number').html(this.current_frame_num);
		}.bind(this),1000);
	},

	/*
	 * Функция-обрботчик клика на pause-кнопке
	 */
	pauseClick:function() {
		this.interval = clearInterval(this.interval);
	},

	/*
	 * Функция для загрузки изображений видеопетли
	 */
	load: function() {
		var opts=this.opts,
			progressbar = $(this.wraperID+' #progressbar').progressbar(),
			//Объект неотображаемого изображенения для загрузки изображений видеопетли в кэш
			cacheImg = $('<img/>'),
			//Обхект отображения текущего номера изображения загружаемой видеопетли
			curFrameNumTextField = $(this.wraperID+' .loopcontainer #loop_item_count_loaded');

		//Инициализация
		this.srcWithoutFrameNumber = opts['src'].replace(/&frameNumber=\d/,'&frameNumber=');
		this.img = $(this.wraperID+' #contentImg_'+opts['id']);
		this.current_frame_num=0;
		$(this.wraperID+' .loopcontainer #current_loop_item_number').html(1);
		progressbar.progress(0);
		this.isLoading = true;

		(function (data){
			//Проверка события остановки загрузки видеопетли
			if (this.stopLoading) {
				this.isLoading = false;
				this.stopLoading = false;
				this.stopLoadingCallback();
				this.stopLoadingCallback = Ext.emptyFn();
				return;
			}


			if (data['curFrameNum'] > opts['numberOfFrames']) {
				//Если загрузка завершена
				this.isLoading = false;

				$(this.wraperID+' .load_loop_info').hide();
				$(this.wraperID+' .loop_buttons').show();

				return;
			} else {
				//Если загрузка не завершена,
				//вызываем рекурсивно эту же функцию после загрузки очередного изображения
				var callback = arguments.callee;

				cacheImg.bindImageLoad(function(){
					//Устанавливаем процентное значения загрузки в прогрессбар
					progressbar.progress((data['curFrameNum']/opts['numberOfFrames']*100).toFixed(2));
					curFrameNumTextField.html(data['curFrameNum']);
					//setTimeout нужен, чтобы передать управление другим функциям, например, для метода progress прогрессбара
					setTimeout(function(){callback.call(this,{curFrameNum:data['curFrameNum']+1});}.bind(this), 1);
				}.bind(this)).attr('src',(this.srcWithoutFrameNumber+data['curFrameNum'].toString()))
			}
		}).call(this,{curFrameNum:1});
	},
	/*
	 *  Функция-обработчик клика на изображение в хэдере просмотровщика
	 *	@opts - объект:
	 *		@id - идентификатор исследования
	 *		@seriesNum - постфикс класса сайдбара
	 *		@study_uid - путь до изображения
	 *		@LpuEquipmentPacs_id - ссылка на объект кликнутого изображения в сайдабре
	 *		[@EMK] - флаг отображения в ЕМК (необязательный)
	 */
	headerClick: function(opts) {

		if ( (typeof opts != 'object') || (!opts['id']) || (!opts['seriesNum']) || (!opts['study_uid']) || /*(!opts['EMK'])||*/(!opts['LpuEquipmentPacs_id']) || (typeof opts['imgObj'] != 'object')) {
				return false;
		}

		opts['EMK'] = (!opts['EMK'])?0:opts['EMK'];

		this.wraperID = '#'+this._wraperIdPrefix+opts['id'];

		if ($(opts['imgObj']).hasClass('active')) return;
		$(this.wraperID+' .header .active').removeClass('active');
		$(opts['imgObj']).parent('a').addClass('active');
		$('#EvnUslugaparFunctRequest_content_'+opts['id']).html('');
		$.ajax({url:'/?c=Dicom&m=getSeriesView', method: 'POST',data: {'seriesNum':opts['seriesNum'],'study_uid':opts['study_uid'],'LpuEquipmentPacs_id':opts['LpuEquipmentPacs_id'],'EMK':opts['EMK']},
			success: function(responseText,result,response) {
				var resp,errTxt;
				$(this.wraperID+' .slider').click();
				if (response.status !=200) {
					sw.swMsg.alert(langs('Ошибка'), langs('Запрос завершился провалом'), Ext.emptyFn );
				}
				try {
					resp = JSON.parse(responseText);
					if (!resp.success||!resp.html) {
						errTxt = (!resp.Error_Msg)?langs('При запросе к серверу возникла ошибка'):resp.Error_Msg;
						sw.swMsg.alert(langs('Ошибка'), errTxt, Ext.emptyFn );
						return
					}
					$('#EvnUslugaparFunctRequest_content_'+opts['id']).html(resp.html);
				}
				catch(exc) {
					sw.swMsg.alert(langs('Ошибка'), langs('Ошибка полученных данных'), Ext.emptyFn );
				}
			}.createDelegate(this)
		});
	}
}

/**
*  Secure Hash Algorithm (SHA1)
**/
function SHA1(msg) {

	function rotate_left(n,s) {
		var t4 = ( n<<s ) | (n>>>(32-s));
		return t4;
	};

	function lsb_hex(val) {
		var str="";
		var i;
		var vh;
		var vl;

		for( i=0; i<=6; i+=2 ) {
			vh = (val>>>(i*4+4))&0x0f;
			vl = (val>>>(i*4))&0x0f;
			str += vh.toString(16) + vl.toString(16);
		}
		return str;
	};

	function cvt_hex(val) {
		var str="";
		var i;
		var v;

		for( i=7; i>=0; i-- ) {
			v = (val>>>(i*4))&0x0f;
			str += v.toString(16);
		}
		return str;
	};


	function Utf8Encode(string) {
		string = string.replace(/\r\n/g,"\n");
		var utftext = "";

		for (var n = 0; n < string.length; n++) {

			var c = string.charCodeAt(n);

			if (c < 128) {
				utftext += String.fromCharCode(c);
			}
			else if((c > 127) && (c < 2048)) {
				utftext += String.fromCharCode((c >> 6) | 192);
				utftext += String.fromCharCode((c & 63) | 128);
			}
			else {
				utftext += String.fromCharCode((c >> 12) | 224);
				utftext += String.fromCharCode(((c >> 6) & 63) | 128);
				utftext += String.fromCharCode((c & 63) | 128);
			}

		}

		return utftext;
	};

	var blockstart;
	var i, j;
	var W = new Array(80);
	var H0 = 0x67452301;
	var H1 = 0xEFCDAB89;
	var H2 = 0x98BADCFE;
	var H3 = 0x10325476;
	var H4 = 0xC3D2E1F0;
	var A, B, C, D, E;
	var temp;

	msg = Utf8Encode(msg);

	var msg_len = msg.length;

	var word_array = new Array();
	for( i=0; i<msg_len-3; i+=4 ) {
		j = msg.charCodeAt(i)<<24 | msg.charCodeAt(i+1)<<16 |
		msg.charCodeAt(i+2)<<8 | msg.charCodeAt(i+3);
		word_array.push( j );
	}

	switch( msg_len % 4 ) {
		case 0:
			i = 0x080000000;
		break;
		case 1:
			i = msg.charCodeAt(msg_len-1)<<24 | 0x0800000;
		break;

		case 2:
			i = msg.charCodeAt(msg_len-2)<<24 | msg.charCodeAt(msg_len-1)<<16 | 0x08000;
		break;

		case 3:
			i = msg.charCodeAt(msg_len-3)<<24 | msg.charCodeAt(msg_len-2)<<16 | msg.charCodeAt(msg_len-1)<<8    | 0x80;
		break;
	}

	word_array.push( i );

	while( (word_array.length % 16) != 14 ) word_array.push( 0 );

	word_array.push( msg_len>>>29 );
	word_array.push( (msg_len<<3)&0x0ffffffff );


	for ( blockstart=0; blockstart<word_array.length; blockstart+=16 ) {

		for( i=0; i<16; i++ ) W[i] = word_array[blockstart+i];
		for( i=16; i<=79; i++ ) W[i] = rotate_left(W[i-3] ^ W[i-8] ^ W[i-14] ^ W[i-16], 1);

		A = H0;
		B = H1;
		C = H2;
		D = H3;
		E = H4;

		for( i= 0; i<=19; i++ ) {
			temp = (rotate_left(A,5) + ((B&C) | (~B&D)) + E + W[i] + 0x5A827999) & 0x0ffffffff;
			E = D;
			D = C;
			C = rotate_left(B,30);
			B = A;
			A = temp;
		}

		for( i=20; i<=39; i++ ) {
			temp = (rotate_left(A,5) + (B ^ C ^ D) + E + W[i] + 0x6ED9EBA1) & 0x0ffffffff;
			E = D;
			D = C;
			C = rotate_left(B,30);
			B = A;
			A = temp;
		}

		for( i=40; i<=59; i++ ) {
			temp = (rotate_left(A,5) + ((B&C) | (B&D) | (C&D)) + E + W[i] + 0x8F1BBCDC) & 0x0ffffffff;
			E = D;
			D = C;
			C = rotate_left(B,30);
			B = A;
			A = temp;
		}

		for( i=60; i<=79; i++ ) {
			temp = (rotate_left(A,5) + (B ^ C ^ D) + E + W[i] + 0xCA62C1D6) & 0x0ffffffff;
			E = D;
			D = C;
			C = rotate_left(B,30);
			B = A;
			A = temp;
		}

		H0 = (H0 + A) & 0x0ffffffff;
		H1 = (H1 + B) & 0x0ffffffff;
		H2 = (H2 + C) & 0x0ffffffff;
		H3 = (H3 + D) & 0x0ffffffff;
		H4 = (H4 + E) & 0x0ffffffff;

	}

	var temp = cvt_hex(H0) + cvt_hex(H1) + cvt_hex(H2) + cvt_hex(H3) + cvt_hex(H4);

	return temp.toLowerCase();

}

function daysBetween(first, second) {
	// Copy date parts of the timestamps, discarding the time parts.
	var one = new Date(first.getFullYear(), first.getMonth(), first.getDate());
	var two = new Date(second.getFullYear(), second.getMonth(), second.getDate());

	// Do the math.
	var millisecondsPerDay = 1000 * 60 * 60 * 24;
	var millisBetween = two.getTime() - one.getTime();
	var days = millisBetween / millisecondsPerDay;

	// Round down.
	return Math.floor(days) + Math.ceil(days - Math.floor(days));
}

function userHasWorkPlaceAtLpuSection(LpuSection_id) {
	if ( Ext.isEmpty(getGlobalOptions().medpersonal_id) || Ext.isEmpty(LpuSection_id) ) {
		return false;
	}

	var filter = {
		 onDate: getGlobalOptions().date
		,LpuSection_id: LpuSection_id
		,medPersonalIdList: [ getGlobalOptions().medpersonal_id ]
	}

	setMedStaffFactGlobalStoreFilter(filter);

	return (swMedStaffFactGlobalStore.getCount() > 0);
}

function userIsDoctor(date) {
	if ( Ext.isEmpty(getGlobalOptions().lpu_id) || Ext.isEmpty(getGlobalOptions().medpersonal_id) ) {
		return false;
	}

	var onDate = Date.parseDate(date || getGlobalOptions().date, 'd.m.Y');

	swMedStaffFactGlobalStore.clearFilter();

	swMedStaffFactGlobalStore.filterBy(function(record) {
		var
			ls_dis_date = Date.parseDate(record.get('LpuSection_disDate'), 'd.m.Y'),
			ls_set_date = Date.parseDate(record.get('LpuSection_setDate'), 'd.m.Y'),
			mp_beg_date = Date.parseDate(record.get('WorkData_begDate'), 'd.m.Y'),
			mp_end_date = Date.parseDate(record.get('WorkData_endDate'), 'd.m.Y');

		return (
			record.get('Lpu_id') == getGlobalOptions().lpu_id
			&& record.get('MedPersonal_id') == getGlobalOptions().medpersonal_id
			&& (
				record.get('PostKind_id') == 1
				|| ((getRegionNick() != 'kz' || IS_DEBUG == 1) && !Ext.isEmpty(record.get('frmpEntry_id')) && record.get('frmpEntry_id').inlist([ 6, 48, 111, 112, 114, 117, 120, 124, 216, 262, 263, 264, 287, 10002, 10003, 10004, 10005, 10006, 10128, 10170, 10171, 10173, 10235, 10236, 10237, 10238, 10240 ]))
				|| (getRegionNick() == 'kz' && IS_DEBUG != 1 && !Ext.isEmpty(record.get('PostMed_Code')) && record.get('PostMed_Code').inlist([ 2, 3, 5, 6, 104, 105, 109, 171, 172, 173, 178, 10008, 10209, 10214, 10227, 10228, 10229 ]))
			)
			&& (Ext.isEmpty(mp_beg_date) || mp_beg_date <= onDate)
			&& (Ext.isEmpty(mp_end_date) || mp_end_date > onDate)
			&& (Ext.isEmpty(ls_set_date) || ls_set_date <= onDate)
			&& (Ext.isEmpty(ls_dis_date) || ls_dis_date > onDate)
		)
	});

	return (swMedStaffFactGlobalStore.getCount() > 0);
}

/**
 * Clone Function
 * @param {Object/Array} o Object or array to clone
 * @return {Object/Array} Deep clone of an object or an array
 * @author Ing. Jozef Sakáloš
 */
function swCloneObject(o) {
	if(!o || 'object' !== typeof o) {
		return o;
	}
	if('function' === typeof o.clone) {
		return o.clone();
	}
	var c = '[object Array]' === Object.prototype.toString.call(o) ? [] : {};
	var p, v;
	for(p in o) {
		if(o.hasOwnProperty(p)) {
			v = o[p];
			if(v && 'object' === typeof v) {
				c[p] = swCloneObject(v);
			}
			else {
				c[p] = v;
			}
		}
	}
	return c;
}; // eo function clone

function calculationIBSTypeId(diag, is_save_ischemia, is_rise_ts) {
	var IBSType_id = 2;
	if (diag.indexOf('I20.0') >= 0) {
		IBSType_id = 1;
	} else if (diag.indexOf('I21.0') >= 0 || diag.indexOf('I21.1') >= 0 || diag.indexOf('I21.2') >= 0 || diag.indexOf('I21.3') >= 0) {
		if (2 == is_save_ischemia && 2 == is_rise_ts) {
			IBSType_id = 1;
		}
	} else if (diag.indexOf('I21.4') >= 0 || diag.indexOf('I21.9') >= 0 || diag.indexOf('I22') >= 0 || diag.indexOf('I23') >= 0 || diag.indexOf('I24') >= 0) {
		if (2 == is_save_ischemia) {
			IBSType_id = 1;
		}
	}
	return IBSType_id;
}

/**
 * Альтернативный JSON, не вызвывающий too mach recursion
 */
Ext.util.JSONalt = new (function(){
	var useHasOwn = !!{}.hasOwnProperty;

	// crashes Safari in some instances
	//var validRE = /^("(\\.|[^"\\\n\r])*?"|[,:{}\[\]0-9.\-+Eaeflnr-u \n\r\t])+?$/;

	var pad = function(n) {
		return n < 10 ? "0" + n : n;
	};

	var m = {
		"\b": '\\b',
		"\t": '\\t',
		"\n": '\\n',
		"\f": '\\f',
		"\r": '\\r',
		'"' : '\\"',
		"\\": '\\\\'
	};

	var encodeString = function(s){
		if (/["\\\x00-\x1f]/.test(s)) {
			return '"' + s.replace(/([\x00-\x1f\\"])/g, function(a, b) {
					var c = m[b];
					if(c){
						return c;
					}
					c = b.charCodeAt();
					return "\\u00" +
						Math.floor(c / 16).toString(16) +
						(c % 16).toString(16);
				}) + '"';
		}
		return '"' + s + '"';
	};

	var encodeArray = function(o, startRecursion, maxRecursion){
		var a = ["["], b, i, l = o.length, v;
		for (i = 0; i < l; i += 1) {
			v = o[i];
			switch (typeof v) {
				case "undefined":
				case "function":
				case "unknown":
					break;
				default:
					if (b) {
						a.push(',');
					}
					a.push(v === null ? "null" : Ext.util.JSONalt.encode(v, startRecursion, maxRecursion));
					b = true;
			}
		}
		a.push("]");
		return a.join("");
	};

	this.encodeDate = function(o){
		return '"' + o.getFullYear() + "-" +
			pad(o.getMonth() + 1) + "-" +
			pad(o.getDate()) + "T" +
			pad(o.getHours()) + ":" +
			pad(o.getMinutes()) + ":" +
			pad(o.getSeconds()) + '"';
	};


	this.encode = function(o, startRecursion, maxRecursion){
		if (startRecursion > maxRecursion) {
			return "null";
		}
		startRecursion++;

		if(typeof o == "undefined" || o === null){
			return "null";
		}else if(Ext.isArray(o)){
			return encodeArray(o, startRecursion, maxRecursion);
		}else if(Ext.isDate(o)){
			return Ext.util.JSONalt.encodeDate(o);
		}else if(typeof o == "string"){
			return encodeString(o);
		}else if(typeof o == "number"){
			return isFinite(o) ? String(o) : "null";
		}else if(typeof o == "boolean"){
			return String(o);
		}else {
			var a = ["{"], b, i, v;
			for (i in o) {
				if(!useHasOwn || o.hasOwnProperty(i)) {
					v = o[i];
					switch (typeof v) {
						case "undefined":
						case "function":
						case "unknown":
							break;
						default:
							if(b){
								a.push(',');
							}
							a.push(this.encode(i, startRecursion, maxRecursion), ":",
								v === null ? "null" : this.encode(v, startRecursion, maxRecursion));
							b = true;
					}
				}
			}
			a.push("}");
			return a.join("");
		}
	};


	this.decode = function(json){
		return eval("(" + json + ')');
	};
})();

/**
 * Список идентификаторов МО, которым доступно копирование КВС
 * @task https://redmine.swan.perm.ru/issues/60329
 */
function getLpuListForEvnPSCopy() {
	return [ '150184', '13002494', '13002495' ];
}


/**
 * Создаёт объект для запроса
 */
function getXmlHttp(){
	try {
		return new ActiveXObject("Msxml2.XMLHTTP");
	} catch (e) {
		try {
			return new ActiveXObject("Microsoft.XMLHTTP");
		} catch (ee) {
		}
	}
	if (typeof XMLHttpRequest!='undefined') {
		return new XMLHttpRequest();
	}
}

/**
 * Проверяет возможность редактирования документа
 */
function syncCheckEvnInRegistry(scope, param_str) {

	if (scope.action !== 'view') {
		switch (getRegionNick()){
			case 'buryatiya':
				var xmlhttp = getXmlHttp();
				xmlhttp.open("POST", '/?c=Registry&m=checkEvnInRegistry&_dc=' + Math.random(), false);
				xmlhttp.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
				xmlhttp.onreadystatechange = function() {
					if (xmlhttp.readyState == 4) {
						if (!Ext.isEmpty(xmlhttp.responseText)) {
							var response_obj = Ext.util.JSON.decode(xmlhttp.responseText);
							if (response_obj.success == false) {
								sw.swMsg.alert(langs('Ошибка'), response_obj.Error_Msg ? response_obj.Error_Msg : langs('Ошибка при загрузке данных формы'));

								if (!Ext.isEmpty(scope)){
									scope.action = 'view';
								}
							} else if (Ext.isEmpty(response_obj.success) || response_obj.success != true){
								sw.swMsg.alert(langs('Ошибка'), response_obj.Error_Msg ? response_obj.Error_Msg : langs('Ошибка при проверке возможности редактирования документа.'));

								if (!Ext.isEmpty(scope)){
									scope.action = 'view';
								}
							}
						}
					}
				};
				xmlhttp.send(param_str);
				break;
			default:
				break;
		}
	}
}

/**
 * Печать мед карты для Уфы
 */
function printMedCard4Ufa(PersonCard_id, PersonAmbulatCard_id){
	if ( (Ext.isEmpty(PersonCard_id) || PersonCard_id <= 0) && (Ext.isEmpty(PersonAmbulatCard_id)) || PersonAmbulatCard_id <= 0 ) {
		return false;
	}
	//var add_params = Ext.isEmpty(PersonAmbulatCard_id) ? '' : '&paramPersonAmbulatCard=' + PersonAmbulatCard_id;
	var add_params =  Ext.isEmpty(PersonAmbulatCard_id) ? '&paramPersonCard=' + PersonCard_id: '&paramPersonAmbulatCard=' + PersonAmbulatCard_id;
	printBirt({
		'Report_FileName': 'f025u_oborot.rptdesign',
		'Report_Format': 'pdf',
		'Report_Params': '&paramLpu=' + getGlobalOptions().lpu_id + add_params
	});
	printBirt({
		'Report_FileName': 'f025u.rptdesign',
		'Report_Format': 'pdf',
		'Report_Params': '&paramLpu=' + getGlobalOptions().lpu_id + add_params
	});
}

/**
 * Открытие формы просмотра направления
 */
function openEvnDirectionEditWindow(EvnDirection_id, Person_id) {
	getWnd('swEvnDirectionEditWindow').show({
		action: 'view',
		formParams: {},
		EvnDirection_id: EvnDirection_id,
		Person_id: Person_id
	});
}

function getEvnDrugEditWindowName() {
	var name = '';
	switch(getDrugControlOptions().drugcontrol_module) {
		case 2:
		case '2':
			name = 'swNewEvnDrugEditWindow';
			break;
		default:
			name = 'swEvnDrugEditWindow';
			break;
	}
	return name;
}

/**
 * Получение наименования страны текущего региона Промед в нужном падеже и форме
 * @param  {string} 	pad  	Падеж
 * @param  {boolean} 	full 	Полная или краткая форма (реализовано для Казахстана)
 * @return {string}      		Наименование страны
 */
function getCountryName(pad, full) {
	if ( Ext.isEmpty(pad) ) {
		pad = 'im';
	}
	if ( Ext.isEmpty(full) ) {
		full = false;
	}

	var result = '';

	if (full && getRegionNick() == 'kz') {
		switch ( pad ) {
			case 'rod': result = 'Республики'; break;
			case 'dat': result = 'Республике'; break;
			case 'vin': result = 'Республику'; break;
			case 'tvor': result = 'Республикой'; break;
			case 'predl': result = 'Республике'; break;
			default: result = 'Республика'; break;
		}
		result+= ' Казахстан';
	} else {
		switch ( pad ) {
			case 'rod':
				switch ( getRegionNick() ) {
					case 'by': result = 'Беларуси'; break;
					case 'kz': result = 'Казахстана'; break;
					default: result = 'России'; break;
				}
			break;

			case 'dat':
				switch ( getRegionNick() ) {
					case 'by': result = 'Беларуси'; break;
					case 'kz': result = 'Казахстану'; break;
					default: result = 'России'; break;
				}
			break;

			case 'vin':
				switch ( getRegionNick() ) {
					case 'by': result = 'Беларусь'; break;
					case 'kz': result = 'Казахстан'; break;
					default: result = 'Россию'; break;
				}
			break;

			case 'tvor':
				switch ( getRegionNick() ) {
					case 'by': result = 'Беларусью'; break;
					case 'kz': result = 'Казахстаном'; break;
					default: result = 'Россией'; break;
				}
			break;

			case 'predl':
				switch ( getRegionNick() ) {
					case 'by': result = 'Беларуси'; break;
					case 'kz': result = 'Казахстане'; break;
					default: result = 'России'; break;
				}
			break;

			default:
				switch ( getRegionNick() ) {
					case 'by': result = 'Беларусь'; break;
					case 'kz': result = 'Казахстан'; break;
					default: result = 'Россия'; break;
				}
			break;
		}
	}

	return result;
}

/**
 * Функция генерирует название реестра лекарственных стредств для разных регионов
 */
function getRLSTitle() {
	if(getRegionNick() == 'kz') {
		$title = langs('Реестр лекарственных средств');
		$title+= ' ' + getCountryName('rod', true);
	} else {
		//$title = langs('Регистр лекарственных средств');
		$title = langs('Справочник медикаментов');
	}
	//$title+= ' ' + getCountryName('rod', true);
	return $title;
}

/**
 * Функция для определения, принадлежит ли запись подчиненного комбобокса к обслуживаемым отделениям
 */
function checkSlaveRecordForLpuSectionService(masterRecord, slaveRecord) {
	if ( typeof masterRecord != 'object' || typeof slaveRecord != 'object' || Ext.isEmpty(masterRecord.get('LpuSectionServiceList')) ) {
		return false;
	}

	var LpuSectionServiceList = masterRecord.get('LpuSectionServiceList').split(',');

	return (typeof LpuSectionServiceList == 'object' && !Ext.isEmpty(slaveRecord.get('LpuSection_id')) && slaveRecord.get('LpuSection_id').inlist(LpuSectionServiceList));
}

/**
 * Функция для проверки принадлежит ли значение текущему сторе
 */
function checkValueInStore(base_form, field, key, value) {
	base_form.findField(field).clearValue();
	base_form.findField(field).getStore().findBy(function(rec) {
		if ( rec.get(key) == value ) {
			base_form.findField(field).setValue(rec.get(base_form.findField(field).valueField));
			return true;
		}
		return false;
	});
}

/**
 * Функция проверяет есть ли у текущего пользовтеля права на редактирование ЛВН, если нет - переводит грид в режим "только чтение"
 */
function checkEvnStickEditable(grid_id, scope) {
	if (!isPolkaRegistrator() && !isMedStatUser() && !userIsDoctor() && !isOperator() && !isRegLvn() && !isPolkaVrach() && !haveArmType('vk')) {
		if (!Ext.isEmpty(grid_id)){
			scope.findById(grid_id).getTopToolbar().items.items[0].disable();
			scope.findById(grid_id).getTopToolbar().items.items[1].disable();
			scope.findById(grid_id).getTopToolbar().items.items[3].disable();
		}
		scope.evnStickAction = 'view';
	}
}

/**
 * Функция проверяет есть ли у текущего пользовтеля права на редактирование выбранного протокола, возвращает true/false
 *
 */
function checkPathoMorphEditable(rec, patho_param = 'MedPersonal_id') {
	return (
		//оператор имеет доступ для создания и редактирования всех протоколов в рамках своей МО
		(isSuperAdmin() && parseInt(rec.get('Lpu_id')) === parseInt(getGlobalOptions().lpu_id))
		||

		//патологоанатом меет доступ для редактирования своих или ничьих протоколов
		(isPathoMorphoUser() && rec.get('Lpu_id') === parseInt(getGlobalOptions().lpu_id) && (Ext.isEmpty(rec.get(patho_param)) || parseInt(getGlobalOptions().medpersonal_id) === parseInt(rec.get(patho_param))))
	);
}

/**
 * Функция возвращает признак того, что у МО есть правопреемник
 */
function lpuIsTransit(grid_id, scope) {
	return (getGlobalOptions().lpuIsTransit === 1);
}

/**
 * Склоняет слово по числам
 *
 * на входе
 * $case1 - ед. число,
 * $case2 - мн. число для 2, 3, 4 или оканчивающихся на 2, 3, 4
 * $case3 - мн. число для 5-20 (включительно), и всех что кончаются на любые кроме 2, 3, 4
 * $anInteger - число
 * пример:
 *   '1 '.ru_word_case('день', 'дня', 'дней', 1) // output: 1 день
 *   '2 '.ru_word_case('день', 'дня', 'дней', 2) // output: 2 дня
 *   '11 '.ru_word_case('день', 'дня', 'дней', 11) // output: 11 дней
 *   '21 '.ru_word_case('день', 'дня', 'дней', 21) // output: 21 день

 * @param $case1
 * @param $case2
 * @param $case3
 * @param $anInteger
 * @return mixed
 */
function ru_word_case(case1, case2, case3, anInteger){
	var result = case3;
	if ((anInteger < 5)||(20 < anInteger)) {
		days = anInteger.toString();
		lastSymbol =  days[days.length-1];
		switch (lastSymbol) {
			case '1':
				result = case1;
				break;
			case '2':
			case '3':
			case '4':
				result = case2;
				break;
			default:
				break;
		}
	}
	return result;
}

/**
 * Выборка необходимых идентификаторов пользователя из локального списка мест работы для текущего пользователя и дополнительного MedPersonal_id
 * @param {string}
 * @return {Array}
 */
function getUserMedStaffFactData(field, MedPersonal_id) {
	var
		medPersonalList = new Array(),
		result = new Array();

	if ( !Ext.isEmpty(MedPersonal_id) ) {
		medPersonalList.push(MedPersonal_id);
	}

	if ( !Ext.isEmpty(getGlobalOptions().medpersonal_id) ) {
		medPersonalList.push(getGlobalOptions().medpersonal_id);
	}

	if ( medPersonalList.length == 0 || swMedStaffFactGlobalStore == null ) {
		return result;
	}

	if ( Ext.isEmpty(field) || typeof field != 'string' ) {
		field = 'MedStaffFact_id';
	}

	swMedStaffFactGlobalStore.clearFilter();

	swMedStaffFactGlobalStore.each(function(rec) {
		if ( rec.get('MedPersonal_id').inlist(medPersonalList) && !Ext.isEmpty(rec.get(field)) ) {
			result.push(rec.get(field));
		}
	});

	return result;
}

/**
 * Экранирует специальные символы для использования строки в HTML
 * @param {string}
 * @return {string}
 */
function escapeHtml(unsafe) {
	return unsafe
		.replace(/&/g, "&amp;")
		.replace(/</g, "&lt;")
		.replace(/>/g, "&gt;")
		.replace(/"/g, "&quot;")
		.replace(/'/g, "&#039;");
}

/**
 * Генерирует строку сообщения из полученного ответа. Используется на формах редактирвоания КВС при проверке пересечения КВС.
 * @param {object}
 * @return {string}
 */
function getMsgForCheckDoubles (response_obj){
	var msg = response_obj.Alert_Msg,
		headMsg = langs('Информация')+'&nbsp;'+langs('о')+'&nbsp;'+langs('пересечениях');
	if (response_obj.Error_Code === 113){
		var addMsg = '',
			inner = langs('Пересечение внутри ЛПУ: <br/>'),
			outer = langs('Пересечение с другими ЛПУ: <br/>');

		response_obj.data.forEach(function(rec){
			switch (rec.intersect_type) {
				case 'inner':
					inner += '- ' + (!Ext.isEmpty(rec.EvnPS_NumCard)?rec.EvnPS_NumCard:'') + ' ' + (!Ext.isEmpty(rec.LpuSectionProfile_Name)?rec.LpuSectionProfile_Name:'') + '<br/>';
					break;
				case 'outer':
					outer += '- ' + (!Ext.isEmpty(rec.EvnPS_NumCard)?rec.EvnPS_NumCard:'') + ' ' + (!Ext.isEmpty(rec.Lpu_Nick)?rec.Lpu_Nick:'') + '<br/>';
					break;
			}
		});
		addMsg += inner + '<br/>' + outer;
		addMsg = escapeHtml(addMsg);
		msg += '<br/> <a onclick="Ext.Msg.alert(\' ' + headMsg +  ' \',\' ' + addMsg +  ' \');" href=\'#\' >Подробнее</a>'
	}

	return msg;
}

function isValidDate(d){
	if ( Object.prototype.toString.call(d) !== "[object Date]" ) {
		return false;
	}
	return !isNaN(d.getTime());
}

Date.prototype.isValid = function(){
	return isValidDate(this);
}

function swTranslite(str) {
	var arr = {
		'а': 'a',
		'б': 'b',
		'в': 'v',
		'г': 'g',
		'д': 'd',
		'е': 'e',
		'ж': 'g',
		'з': 'z',
		'и': 'i',
		'й': 'y',
		'к': 'k',
		'л': 'l',
		'м': 'm',
		'н': 'n',
		'о': 'o',
		'п': 'p',
		'р': 'r',
		'с': 's',
		'т': 't',
		'у': 'u',
		'ф': 'f',
		'ы': 'i',
		'э': 'e',
		'А': 'A',
		'Б': 'B',
		'В': 'V',
		'Г': 'G',
		'Д': 'D',
		'Е': 'E',
		'Ж': 'G',
		'З': 'Z',
		'И': 'I',
		'Й': 'Y',
		'К': 'K',
		'Л': 'L',
		'М': 'M',
		'Н': 'N',
		'О': 'O',
		'П': 'P',
		'Р': 'R',
		'С': 'S',
		'Т': 'T',
		'У': 'U',
		'Ф': 'F',
		'Ы': 'I',
		'Э': 'E',
		'ё': 'yo',
		'х': 'h',
		'ц': 'ts',
		'ч': 'ch',
		'ш': 'sh',
		'щ': 'shch',
		'ъ': '',
		'ь': '',
		'ю': 'yu',
		'я': 'ya',
		'Ё': 'YO',
		'Х': 'H',
		'Ц': 'TS',
		'Ч': 'CH',
		'Ш': 'SH',
		'Щ': 'SHCH',
		'Ъ': '',
		'Ь': '',
		'Ю': 'YU',
		'Я': 'YA',
		' ': '_'
	};
	var replacer = function (a) {
		if (arr[a] != undefined) {
			return arr[a]
		}
		return a;
	};
	return str.replace(/[А-Яа-я ]/g, replacer)
}

/**
 * Кодирует base64
 */
function swBase64Encode(data) {
	var b64 = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=";
	var o1, o2, o3, h1, h2, h3, h4, bits, i = 0, enc = '';

	do { // pack three octets into four hexets
		o1 = data.charCodeAt(i++);
		o2 = data.charCodeAt(i++);
		o3 = data.charCodeAt(i++);

		bits = o1 << 16 | o2 << 8 | o3;

		h1 = bits >> 18 & 0x3f;
		h2 = bits >> 12 & 0x3f;
		h3 = bits >> 6 & 0x3f;
		h4 = bits & 0x3f;

		// use hexets to index into b64, and append result to encoded string
		enc += b64.charAt(h1) + b64.charAt(h2) + b64.charAt(h3) + b64.charAt(h4);
	} while (i < data.length);

	switch (data.length % 3) {
		case 1:
			enc = enc.slice(0, -2) + '==';
			break;
		case 2:
			enc = enc.slice(0, -1) + '=';
			break;
	}

	return enc;
}

/**
 * Декодирует base64
 */
function swBase64Decode( data ) {
	var b64 = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=";
	var o1, o2, o3, h1, h2, h3, h4, bits, i=0, enc='';

	do {  // unpack four hexets into three octets using index points in b64
		h1 = b64.indexOf(data.charAt(i++));
		h2 = b64.indexOf(data.charAt(i++));
		h3 = b64.indexOf(data.charAt(i++));
		h4 = b64.indexOf(data.charAt(i++));

		bits = h1<<18 | h2<<12 | h3<<6 | h4;

		o1 = bits>>16 & 0xff;
		o2 = bits>>8 & 0xff;
		o3 = bits & 0xff;

		if (h3 == 64)	  enc += String.fromCharCode(o1);
		else if (h4 == 64) enc += String.fromCharCode(o1, o2);
		else			   enc += String.fromCharCode(o1, o2, o3);
	} while (i < data.length);

	return enc;
}

function utf8 (utftext) {
	var string = "";
	var i = 0;
	var c = c1 = c2 = 0;

	while ( i < utftext.length ) {

		c = utftext.charCodeAt(i);

		if (c < 128) {
			string += String.fromCharCode(c);
			i++;
		}
		else if((c > 191) && (c < 224)) {
			c2 = utftext.charCodeAt(i+1);
			string += String.fromCharCode(((c & 31) << 6) | (c2 & 63));
			i += 2;
		}
		else {
			c2 = utftext.charCodeAt(i+1);
			c3 = utftext.charCodeAt(i+2);
			string += String.fromCharCode(((c & 15) << 12) | ((c2 & 63) << 6) | (c3 & 63));
			i += 3;
		}

	}

	return string;
}

/**
 * Получение строки из бинарной строки
 */
function swGetCharStrFromBinary(string) {
	var temp = '';
	var result = '';
	for(i=0;i<string.length;i+=8) {
		temp = parseInt(string.substring(i,i+8), 2);
		if (temp > 0) {
			// windows-1251 -> utf-8
			if (temp >= 192 && temp <= 255) {
				result += String.fromCharCode(temp + 848);
			} else {
				switch(temp) {
					case 168:
						temp = 177 + 848; // ё
					case 184:
						temp = 257 + 848; // Ё
						break;
				}
				result += String.fromCharCode(temp);
			}
		}
	}

	return result;
}

function checkPassword(pass, withoutMessage, form) {
	var lowerSymb = new RegExp('[a-zа-я]');
	var upperSymb = new RegExp('[A-ZА-Я]');
	var numbeSymb = new RegExp('[0-9]');
	var specSymb = new RegExp('[^A-Z^А-Я^a-z^а-я^0-9]');

	var minPassLen = 6;
	if (getGlobalOptions().password_minlength) {
		minPassLen = getGlobalOptions().password_minlength;
	}

	var containsLowers = lowerSymb.test(pass);
	var containsUppers = true;
	var needUppers = false;
	if (getGlobalOptions().password_hasuppercase) {
		needUppers = true;
		containsUppers = upperSymb.test(pass);
	}
	var containsNumbers = true;
	var needNumbers = false;
	if (getGlobalOptions().password_hasnumber) {
		needNumbers = true;
		containsNumbers = numbeSymb.test(pass);
	}
	var containsSpec = true;
	var needSpec = false;
	if (getGlobalOptions().password_hasspec) {
		needSpec = true;
		containsSpec = specSymb.test(pass);
	}
	var containsMinLen = (pass.length >= minPassLen);
	if (!(
			containsLowers&&
			containsUppers&&
			containsNumbers&&
			containsSpec&&
			containsMinLen
		)){

		if (!withoutMessage) {
			sw.swMsg.alert(langs('Безопасность пароля'),
				langs('Пароль не удовлетворяет рекомендуемым требованиям безопасности: <br>') +
				langs('пароль должен состоять минимум из ') + (containsMinLen ? '' : '<b>') + minPassLen + (containsMinLen ? '' : '</b>') + ' ' + langs('символов') + ', ' +
				'среди которых должна присутствовать минимум ' + (containsLowers ? '' : '<b>') + 'одна строчная буква' + (containsLowers ? '' : '</b>') +
				(needUppers ? (', ' + (containsUppers ? '' : '<b>') + 'одна прописная буква' + (containsUppers ? '' : '</b>')) : '') +
				(needNumbers ? (', ' + (containsNumbers ? '' : '<b>') + 'одна цифра' + (containsNumbers ? '' : '</b>')) : '') +
				(needSpec ? (', ' + (containsSpec ? '' : '<b>') + 'один спец. символ' + (containsSpec ? '' : '</b>')) : '')
				, function () {
					if ( !Ext.isEmpty(form) && typeof form.findField == 'function' ) {
						form.findField("pass").focus(true, 100)
					}
				});
		}
		return false;
	}

	return true;
}

function warnNeedChangePassword(params) {
	// За несколько дней до истечения срока действия пароля при входе в систему пользователю предлагают сменить пароль.
	// После аутентификации отображается сообщение (с возможность отмены действия, кнопка «Отмена») – «Срок действия пароля подходит концу. Сменить пароль сейчас?»  (кнопки ОК и Отменить).
	if ( !Ext.isEmpty(getGlobalOptions().password_expirationperiod) && !Ext.isEmpty(getGlobalOptions().password_daystowarn) ) {
		var days = parseInt(getGlobalOptions().password_expirationperiod);
		var warnDays = parseInt(getGlobalOptions().password_daystowarn);
		var secs = days*24*60*60;
		var warnSecs = warnDays*24*60*60;

		var current_time = Math.floor(new Date().getTime() / 1000);

		var password_time = Math.floor(new Date(2016, 2, 1).getTime() / 1000);
		if ( !Ext.isEmpty(getGlobalOptions().password_date) && parseInt(getGlobalOptions().password_date) > 0 ) {
			password_time = parseInt(getGlobalOptions().password_date);
		}

		if (password_time + secs - current_time - warnSecs <= 0) {
			// предупреждение
			/*sw.swMsg.show({
				title: langs('Внимание'),
				msg: langs('Срок действия пароля подходит концу. Сменить пароль сейчас?'),
				buttons: Ext.Msg.YESNO,
				fn: function (buttonId) {
					if (buttonId == 'yes') {
						getWnd('swUserPasswordChangeWindow').show({
							onHide: params.callback
						});
					} else {
						if (params && params.callback) {
							params.callback();
						}
					}
				}
			});*/
			var exp_days = Math.ceil((warnSecs, password_time + secs - current_time) / 24 / 60 / 60);

			if ( exp_days < 0 ) {
				exp_days = 0;
			}

			var exp_days_caption = 'день';

			if (
				(exp_days.toString().length > 1 && exp_days.toString().substr(exp_days.toString().length - 2, 2).inlist([ '11', '12', '13', '14', '15', '16', '17', '18', '19' ]))
				|| exp_days.toString().substr(exp_days.toString().length - 1, 1).inlist([ '0', '5', '6', '7', '8', '9' ])
			) {
				exp_days_caption = 'дней';
			}
			else if ( exp_days.toString().substr(exp_days.toString().length - 1, 1).inlist([ '2', '3', '4' ]) ) {
				exp_days_caption = 'дня';
			}

			sw.swMsg.alert(langs('Внимание'), 'До истечения срока действия пароля осталось' + ' ' + exp_days + ' ' + exp_days_caption + '. Пароль можно сменить в профиле пользователя.', function() { params.callback(); });
		} else {
			params.callback();
		}
	} else {
		params.callback();
	}
}

/**
 * Объект взаимодействия с сокетом NodeJS
 * @TODO Необходимо доработать. Как минимум занести функцию connectNode
 */
sw.Promed.socket = {
	//Интервал (setInterval) повторного соединения
	reconnectInterval: null,
	//Задержка интервала повторного соединения [мс]
	reconnectIntervalDelay: 6000,  /*6 сек*/
	//Количество выполнения интервала повторного соединения
	_reconnectIntervalCount: 0,

	/**
	 * @abstract Метод соединения с сокетом NodeJS
	*/
	connect: function() { },
	startReconnectInterval: function() {

		//Если интервал переподключения уже запущен, не будем запускать его ещё раз
		if (this.reconnectInterval) {
			return;
		}

		if (isDebug()) log('Запуск инетрвала');

		//Реинициализация переменной-счетчика количества выполненных интервалов
		this._reconnectIntervalCount = 0;

		//Интервал 0-1 минута, 10-11 минут, каждую 30 минуту
		//Пытаемся подключиться в течение минуты

		this.reconnectInterval = setInterval( function() {

			var count = sw.Promed.socket._reconnectIntervalCount;

			if(  count<11 || count>100 && count<111 || count%300<10 )
			{
				sw.Promed.socket.connect();
			}

			//Увеличиваем счетчик количества выполненных интервалов
			sw.Promed.socket._reconnectIntervalCount++;

		} ,this.reconnectIntervalDelay /*6 сек*/);

	},
	stopReconncetInterval: function() {

		clearInterval(this.reconnectInterval);
		this.reconnectInterval = null;
	},

	// Метод получения сокета
	// @TODO сделать как замену connectNode
	getSocket: function( ) {

	}

}
/*
 *
 * @param {type} cmp
 * @returns {undefined}запускаем node.js
 * cmp - окно
 */
function connectNode(cmp){
	var opts = getGlobalOptions();

	if (!opts || !opts.smp || !opts.smp.NodeJSSocketConnectHost) {
		log('No socket conection host')
		return false;
	}

	cmp.socket = io(opts.smp.NodeJSSocketConnectHost);
	cmp.io = io;
	sw.Promed.socket.connect = function(){
		cmp.socket.connect();
	};

	cmp.socket.on('connect', function () {

		sw.Promed.socket.stopReconncetInterval();

		cmp.socket.on('authentification', function (callback) {
			callback(document.cookie, opts.pmuser_id, navigator.userAgent, opts.region.nick);
		});
		cmp.socket.on('logout', function(){
			location.replace(location.origin+'/?c=main&m=Logout');
		});
	});

	cmp.socket.on('connect_error',function(err){
		//Логика не ясна, поэтому пока скроем
		//cmp.socket.disconnect();
		log('Ошибка соединения с NodeJS');
		sw.Promed.socket.startReconnectInterval();
	});

	cmp.socket.on('disconnect', function () {
		log('Разрыв соединения с NodeJS');
		sw.Promed.socket.startReconnectInterval();
	});
}

function getCurrencyName(){
	if(getRegionNick() == 'kz'){
		return 'тенге'
	}
	return 'руб.'
}

/**
 * Метод проверки, что для документа необходима подпись
 */
function checkNeedSignature(params) {
	if (getGlobalOptions().hasEMDCertificate && getRegionNick() != 'kz') {
		Ext.Ajax.request({
			callback: function(options, success, response) {
				var result = Ext.util.JSON.decode(response.responseText);

				if (result.needSignature) {
						sw.swMsg.show({
							title: langs('Вопрос'),
							msg: langs('Протокол подлежит регистрации в РЭМД ЕГИСЗ. Если редактирование данных по оказанию услуги закончено, подпишите протокол электронной подписью. Подписать?'),
							icon: Ext.MessageBox.QUESTION,
							buttons: Ext.Msg.YESNO,
							fn: function(buttonId) {
								if (buttonId == 'yes') {
									getWnd('swEMDSignWindow').show({
										EMDRegistry_ObjectName: params.EMDRegistry_ObjectName,
										EMDRegistry_ObjectID: params.EMDRegistry_ObjectID,
										callback: function(data) {
											if (data.success && typeof params.callback == 'function') {
												params.callback();
											}
										}
									});
								}
							}
						});
				}
			},
			params: {
				EMDRegistry_ObjectName: params.EMDRegistry_ObjectName,
				EMDRegistry_ObjectID: params.EMDRegistry_ObjectID
			},
			url: '/?c=EMD&m=checkNeedSignature'
		});
	}
}

/**
 * Открытие случаев
 */
function openEditForm(cfg) {
	if (!cfg || !cfg.Evn_id) {
		sw.swMsg.alert('Сообщение', 'Не передан идентификатор случая');
		return false;
	}

	// получаем EvnClass
	Ext.Ajax.request({
		url: '/?c=Evn&m=getEvnData',
		params: {
			Evn_id: cfg.Evn_id,
			mode: 'registry'
		},
		callback: function(options, success, response) {
			if (response && response.responseText) {
				var response_obj = Ext.util.JSON.decode(response.responseText);
				if (response_obj && response_obj[0] && response_obj[0].EvnClass_id) {
					var config = getEditFormForEvnClass({
						EvnClass_id: response_obj[0].EvnClass_id,
						DispClass_id: response_obj[0].DispClass_id
					});

					open_form = config.open_form;
					key = config.key;

					var params = {
						action: 'edit',
						Person_id: response_obj[0].Person_id,
						Server_id: response_obj[0].Server_id,
						PersonEvn_id: response_obj[0].PersonEvn_id,
						usePersonEvn: true
					};
					params = Ext.apply(params || {});

					if (id) {
						params[key] = cfg.Evn_id;
					}

					if (open_form.inlist([ 'swCmpCallCardEditWindow', 'swCmpCallCardNewCloseCardWindow', 'swEvnUslugaTelemedEditWindow' ])) { // карты вызова, телемедицинские услуги
						params.formParams = Ext.apply(params);
					}

					getWnd(open_form).show(params);
				}
			}
			wnd.form.findField('Period_range').setValue(val1+' - '+val2);
		}
	});
}

/**
 * Получение формы редактирования для EvnClass'а
 */
function getEditFormForEvnClass(params) {
	var config = new Object();

	// по умолчанию полка.
	config.open_form = 'swEvnPLEditWindow';
	config.key = 'EvnPL_id';

	if (!params) {
		return config;
	}

	var EvnClass_id = parseInt(params.EvnClass_id);
	var DispClass_id = parseInt(params.DispClass_id);

	switch (EvnClass_id) {
		case 6:
		case 13:
			config.open_form = 'swEvnPLStomEditWindow';
			config.key = 'EvnPLStom_id';
			break;
		case 30:
		case 32:
			config.open_form = 'swEvnPSEditWindow';
			config.key = 'EvnPS_id';
			break;
		case 35:
			config.open_form = 'EvnPLWOWEditWindow';
			config.key = 'EvnPLWOW_id';
			break;
		case 47:
			config.open_form = 'swEvnUslugaParEditWindow';
			config.key = 'EvnUslugaPar_id';
			break;
		case 8:
			config.open_form = 'swEvnPLDispDopEditWindow';
			config.key = 'EvnPLDispDop_id';
			break;
		case 9:
			config.key = 'EvnPLDispOrp_id';

			switch (DispClass_id) {
				case 3:
				case 7:
					config.open_form = 'swEvnPLDispOrp13EditWindow';
					break;

				case 4:
				case 8:
					config.open_form = 'swEvnPLDispOrp13SecEditWindow';
					break;
			}
			break;
		case 101:
			config.open_form = 'swEvnPLDispDop13EditWindow';
			config.key = 'EvnPLDispDop13_id';
			break;
		case 103:
			config.open_form = 'swEvnPLDispProfEditWindow';
			config.key = 'EvnPLDispProf_id';
			break;
		case 104:
			config.key = 'EvnPLDispTeenInspection_id';

			switch (DispClass_id) {
				case 6:
					config.open_form = 'swEvnPLDispTeenInspectionEditWindow';
					break;

				case 9:
					config.open_form = 'swEvnPLDispTeenInspectionPredEditWindow';
					break;

				case 10:
					config.open_form = 'swEvnPLDispTeenInspectionProfEditWindow';
					break;

				case 11:
					config.open_form = 'swEvnPLDispTeenInspectionPredSecEditWindow';
					break;

				case 12:
					config.open_form = 'swEvnPLDispTeenInspectionProfSecEditWindow';
					break;
			}
			break;

		case 160:
			config.open_form = 'swEvnUslugaTelemedEditWindow';
			config.key = 'EvnUslugaTelemed_id';
			break;
	}

	return config;
}

sw.Promed.CostPrint = {
	config: {
		ByDay: null,
		callback: Ext.emptyFn,
		Evn_id: null,
		Person_id: null,
		type: null
	},
	print: function(config) {
		if ( typeof config != 'object' ) {
			return false;
		}
		else if ( Ext.isEmpty(config.Evn_id) ) {
			return false;
		}

		this.config.ByDay = config.ByDay || null;
		this.config.callback = typeof config.callback == 'function' ? config.callback : Ext.emptyFn;
		this.config.Evn_id = config.Evn_id || null;
		this.config.Person_id = config.Person_id || null;
		this.config.type = config.type || null;

		if ( getRegionNick() == 'kaluga' ) {
			this._getCostPrintData();
		}
		else {
			getWnd('swCostPrintWindow').show(this.config);
		}
	},
	_getCostPrintData: function() {
		Ext.Ajax.request({
			callback: function(options, success, response) {
				var result = Ext.util.JSON.decode(response.responseText);

				if ( !Ext.isEmpty(result.Error_Msg) ) {
					sw.swMsg.alert(langs('Сообщение'), result.Error_Msg);
				}
				else {
					if ( result.CostPrint_setDT ) {
						this.config.CostPrint_setDT = result.CostPrint_setDT;
					}

					if ( result.Evn_setDate ) {
						this.config.Evn_setDate = result.Evn_setDate;
					}

					if ( result.Cost_Year ) {
						this.config.Cost_Year = result.Cost_Year;
					}

					this._saveCostPrint();
				}
			}.createDelegate(this),
			params: {
				Evn_id: this.config.Evn_id
			},
			url: '/?c=CostPrint&m=getCostPrintData'
		});
	},
	_saveCostPrint: function() {
		Ext.Ajax.request({
			callback: function(options, success, response) {
				var result = Ext.util.JSON.decode(response.responseText);

				var
					format = 'pdf',
					params = '',
					pattern = '';

				if ( getPrintOptions().cost_print_extension == 2 ) {
					format = 'xls';
				}
				else if ( getPrintOptions().cost_print_extension == 3 ) {
					format = 'html';
				}

				switch ( this.config.type ) {
					case 'EvnPL':
					case 'EvnPLStom':
						pattern = 'pan_Spravka_PL.rptdesign';
						params = '&paramEvnPL=' + this.config.Evn_id;
					break;

					case 'EvnPS':
						pattern = 'hosp_Spravka_KSG.rptdesign';
						params = '&paramEvnPS=' + this.config.Evn_id;
					break;
				}

				if ( !Ext.isEmpty(pattern) ) {
					printBirt({
						'Report_FileName': pattern,
						'Report_Params': params,
						'Report_Format': format
					});
				}
				else {
					sw.swMsg.alert(langs('Сообщение'), langs('Не найден шаблон для печати'));
					log(langs('Не найден шаблон для печати'), this.config.type, getRegionNick());
				}
			}.createDelegate(this),
			params: {
				CmpCallCard_id: this.config.CmpCallCard_id || null,
				CostPrint_begDate: this.config.CostPrint_begDate || null,
				CostPrint_endDate: this.config.CostPrint_endDate || null,
				CostPrint_IsNoPrint: this.config.CostPrint_IsNoPrint || null,
				CostPrint_setDT: this.config.CostPrint_setDT || null,
				CostPrint_setDate: this.config.CostPrint_setDate || null,
				Evn_id: this.config.Evn_id || null,
				Evn_setDate: this.config.Evn_setDate || null,
				Person_id: this.config.Person_id || null,
				Person_IsPred: this.config.Person_IsPred || null,
				Person_pid: this.config.Person_pid || null
			},
			url: '/?c=CostPrint&m=saveCostPrint'
		});
	}
}

window.addEventListener("message", onMessage);

function onMessage(e) {
	var msg = e.data;
	if (typeof msg == "string") {
		if (msg.indexOf("access_token=") >= 0) {
			var token = msg.substring(13);
			if (token.length > 0) {
				if (getWnd('swMedServiceEditWindow') && getWnd('swMedServiceEditWindow').formPanel) {
					getWnd('swMedServiceEditWindow').formPanel.getForm().findField('MedService_WialonToken').setValue(token);
				}
				if (getWnd('swIframeWindow').isVisible()) {
					getWnd('swIframeWindow').hide();
				}
			}
		}
	}
}

function getWialonTestLogin() {
	//сделал для астрахани, для тестирования подключения к wialon из php.
	var login='',
		password='',
		token='';
	var arg = arguments;
	if(arg.length >= 2){
		login=arg[0],
		password=arg[1],
		token= (arg[2]) ? arg[2] : '';
	}else if(arg.length == 1){
		token=arg[0];
	}
	Ext.Ajax.request({
		callback: function(options, success, response) {
			var result = Ext.util.JSON.decode(response.responseText);
			if(result.data.fp){
				sw.swMsg.alert(langs('Ошибка'), result.data.fp);
			}else if(result.data.error){
				console.warn('WIALON ERROR. code: '+result.data.error);
			}else if(result.data.host || result.data.ssid){
				console.warn('WIALON connection present');
			}else{
				console.warn('WIALON ERROR');
			}
		},
		params: {
			login: login,
			password: password,
			token: token,
			loginTest: getRegionNick()
		},
		url: '/?c=Wialon&m=loginTest'
	});
}

function lostConnectionAlert() {
	Ext.Msg.alert('Внимание', 'Связь с основным сервером отсутствует, функционал временно недоступен');
}

function checkConnection(callback) {
	if (sw.Promed.MedStaffFactByUser.last && sw.Promed.MedStaffFactByUser.last.MedService_IsLocalCMP && parseInt(sw.Promed.MedStaffFactByUser.last.MedService_IsLocalCMP) == 2) {
		Ext.Ajax.request({
			url: '/?c=ping',
			isPingRequest: true,
			params: {},
			callback: function(options, success, response) {
				if (success) {
					var result = Ext.util.JSON.decode(response.responseText);
					if (result && result.pong) {
						// есть свзяь
						log('есть связь с основным сервером');
						// если связи не было и она появилась, значит надо проверить ещё и очередь ActiveMQ (ушли ли все записи на основной сервер), затем переключиться на основной серверв todo
						if (sw.lostConnection) {
							Ext.Ajax.request({
								url: '/?c=Common&m=checkActiveMQIsEmpty',
								params: {},
								callback: function(options, success, response) {
									var result = Ext.JSON.decode(response.responseText);
									if (result && result.empty) {
										sw.lostConnection = false;
										// убираем предупреждение
										var divWarn = Ext.query('.noConnectionWarning');
										if (divWarn && divWarn[0]) {
											divWarn[0].style.display = "none";
										}
									}
								}
							});
						}
					} else {
						// нет связи
						log('нет связи с основным сервером');
						// переключаемся на локальный сервер
						sw.lostConnection = true;
						// вешаем в правом верхнему углу красное сообщение об отсутствии связи и предупреждении что запись идет локально
						var divWarn = Ext.query('.noConnectionWarning');
						if (divWarn && divWarn[0]) {
							divWarn[0].style.display = "block";
						}
					}
				} else {
					// нет связи
					log('нет связи с основным сервером');
					// переключаемся на локальный сервер
					sw.lostConnection = true;
					// вешаем в правом верхнему углу красное сообщение об отсутствии связи и предупреждении что запись идет локально
					var divWarn = Ext.query('.noConnectionWarning');
					if (divWarn && divWarn[0]) {
						divWarn[0].style.display = "block";
					}
				}

				if (callback && typeof callback == 'function') {
					callback();
				}
			}
		});
	} else {
		// do nothing
		if (callback && typeof callback == 'function') {
			callback();
		}
	}
}

/**
 * Печать маршрутного листа для РВФД
 */
//Ufa, gaf #116422, для ГАУЗ РВФД
function printRVFDRouteList(person_data) {
	window.open('/?c=EvnDirection&m=printRVFDRouteList&Person_id=' +person_data.Person_id);
}

/**
 * Функция для перевода
 */
function langs(name) {
	if (lang[name]) {
		return lang[name];
	} else {
		return name;
	}
}

sw.Promed.ScanCodeService = function(options) {
	var _this = this;

	var getDrugDataInProcess = false;
	var scanCheckTimer = null;

	var interval = options.interval || 1000;
	var onGetDrugPackData = options.onGetDrugPackData || Ext.emptyFn();

	var initScanner = function() {
		getDrugDataInProcess = false;

		$.ajax({
			url: 'https://localhost:8443/ScanCodeService/scancode/readCode',
			dataType: 'jsonp',
			jsonpCallback: 'callback',
			success: function (data) {}
		});
	};

	var getDrugPackData = function() {
		if (!getDrugDataInProcess) {
			$.ajax({
				url: 'https://localhost:8443/ScanCodeService/rest/scancode/readCode/status',
				dataType: 'jsonp',
				jsonpCallback: 'callback',
				success: function(data) {
					if (data.status) {
						$.ajax({
							url: 'https://localhost:8443/ScanCodeService/rest/scancode/readCode/drugPackObject',
							dataType: 'jsonp',
							type: 'GET',
							jsonpCallback: 'callback',
							success: function (drugPackObject) {
								log(['DrugPackObject', drugPackObject]);
								if (typeof (barcodeScannerLogging) === "function") {
									barcodeScannerLogging();
								}
								onGetDrugPackData(drugPackObject);
								initScanner();
							},
							error: function() {
								initScanner();
							}
						});
					} else {
						initScanner();
					}
				},
				error: function(data, textStatus) {
					initScanner();
				}
			});
		}
	};

	return {
		start: function() {
			initScanner();
			scanCheckTimer = setInterval(getDrugPackData, interval);
		},
		stop: function() {
			clearInterval(scanCheckTimer);
		}
	};
};

sw.Promed.ScanningBarcodeService = {
	running_in_shape: false,
	form: '',
	interval: 1000,
	Process: false,
	scanCheckTimer: false,
	url: 'https://localhost:8443/ScanCodeService/rest/scancode/readCode',
	init: function(options){
		var _this = this;
		var Process = false;
		var scanCheckTimer = null;
		var running_in_shape = false;

		var interval = options.interval || _this.interval;
		var fn = (options.callback && typeof options.callback == 'function') ? options.callback : Ext.emptyFn();

		var url = this.url;
		var running_in_shape = function() { return {form: _this.form, scanning: _this.running_in_shape}};
		var initScanner = function() {
			Process = false;

			$.ajax({
				url: url,
				dataType: 'jsonp',
				jsonpCallback: 'callback',
				success: function (data) {}
			});
		};
		var getPackData = function() {
			if (!Process) {
				$.ajax({
					url: url + '/status',
					dataType: 'jsonp',
					jsonpCallback: 'callback',
					success: function(data) {
						$.ajax({
							url: url + '/code',
							dataType: 'jsonp',
							jsonpCallback: 'callback',
							success: function (data) {
								if(data.code){
									log(['dataCode', data.code]);
									if (typeof (barcodeScannerLogging) === "function") {
										barcodeScannerLogging();
									}
									fn(data.code);
									initScanner();
								}
							},
							error: function() {
								initScanner();
							}
						});
					},
					error: function(data, textStatus) {
						initScanner();
					}
				});
			}
		};
		return {
			start: function(data) {
				initScanner();
				_this.running_in_shape = true;
				_this.form = data.form || '';
				scanCheckTimer = setInterval(getPackData, interval);
			},
			stop: function(data) {
				_this.running_in_shape = false;
				_this.form = '';
				clearInterval(scanCheckTimer);
			},
			running_in_shape: running_in_shape
		};
	}
}

function barcodeScannerLogging(data){
	/* Логирвание работы сканера в БД (дата/время, пользователь)*/
	var dt = data || false;
	var params = {
		pmUser_id: getGlobalOptions().pmuser_id,
		MedPersonal_id: getGlobalOptions().medpersonal_id
	}
	if(dt && dt.polisNum) {
		params.polisNum = dt.polisNum;
	}else if(dt && dt.Polis_Num){
		params.polisNum = dt.Polis_Num;
	}

	Ext.Ajax.request({
		url: '/?c=SystemMonitor&m=barcodeScannerLogging',
		params: params,
		success: function(response) {
			var scan = Ext.util.JSON.decode(response.responseText);
			if(scan.success && scan.ScannerHistory_id){
				log('record barcodeScannerLogging');
			}else{
				log('error record barcodeScannerLogging');
			}
		},
		failure: function() {
			log('error record barcodeScannerLogging');
		}
	});
}

function checkPersonPhoneVerification(options) {
	var lastMedStaffFact = sw.Promed.MedStaffFactByUser.last || {};

	var Person_id = options.Person_id;
	var MedStaffFact_id = options.MedStaffFact_id || lastMedStaffFact.MedStaffFact_id || null;
	var callback = options.callback || Ext.emptyFn;
	var force = options.force || false;

	if (!force && !getGlobalOptions().request_person_phone_verification) {
		callback();
		return;
	}

	Ext.Ajax.request({
		url: '/?c=Person&m=checkPersonPhoneStatus',
		params: {
			Person_id: Person_id,
			MedStaffFact_id: MedStaffFact_id
		},
		success: function(response) {
			var answer = Ext.util.JSON.decode(response.responseText);

			if (answer.success && !answer.isVerified && !answer.wasVerificationToday) {
				getWnd('swPersonPhoneVerificationWindow').show({
					formParams: {
						Person_id: Person_id,
						MedStaffFact_id: MedStaffFact_id,
						PersonPhone_Phone: answer.PersonPhone_Phone
					},
					onHide: callback
				});
			} else {
				callback();
			}
		},
		failure: function() {
			callback();
		}
	});
};


function getLpuIdForPrint(){
	var lpu_id = getGlobalOptions().lpu_id;
	if(Ext.isEmpty(lpu_id)){
		lpu_id = '-1';
	}

	return lpu_id;
}

function dateIsBetween(onDate, startDate, endDate)
{
	var arr = [onDate, startDate];

	Ext.each(arr, function (el) {
		if ( Ext.isEmpty(el) || ! Ext.isDate(el))
		{
			return false;
		}
	});

	if ( ! Ext.isEmpty(endDate) && ! Ext.isDate(endDate) )
	{
		return false;
	}

	if (onDate < startDate || ( ! Ext.isEmpty(endDate) && onDate > endDate ))
	{
		return false;
	}

	return true;

}

function checkLpuSectionProfile_MedSpecOms_Exists(MedSpecOms_id, LpuSectionProfile_id, LSPMSOonDate)
{
	var arrForCheck = [MedSpecOms_id, LpuSectionProfile_id, LSPMSOonDate];

	Ext.each(arrForCheck, function(el) {
		if ( Ext.isEmpty(el) )
		{
			return false;
		}
	});


	var findMatchLpuSectionProfile_MedSpecOms  = swLpuSectionProfileMedSpecOms.findBy(function (rec, id) {
		if (rec.get('MedSpecOms_id') == MedSpecOms_id && rec.get('LpuSectionProfile_id') == LpuSectionProfile_id)
		{

			var LSPMSO_begDate = Ext.util.Format.date(rec.get('LpuSectionProfileMedSpecOms_begDate'), 'd.m.Y'),
				LSPMSO_endDate = Ext.util.Format.date(rec.get('LpuSectionProfileMedSpecOms_endDate'), 'd.m.Y');

			if (dateIsBetween(LSPMSOonDate, LSPMSO_begDate, LSPMSO_endDate))
			{
				return true;
			}
		}
	});

	if (findMatchLpuSectionProfile_MedSpecOms == -1)
	{
		return false;
	}

	return true;
}

/**
 * Получение информации о пользователе
 */
function getPromedUserInfo()
{
	var result = new Array();
	result.lpu_nick = String(getGlobalOptions().lpu_nick);
	result.lpu_id = String(getGlobalOptions().lpu_id);
	result.sessionId = getCookie('PHPSESSID');
	result.groups = getGlobalOptions().groups;
	return result;
}

/**
 * Получение даты, с которой действуют новые правила ДВН
 */
function getNewDVNDate() {
	var dateX = null;
	switch (getRegionNick()) {
		case 'ufa':
		case 'adygeya':
			dateX = new Date(2019,6,1);
			break;

		case 'kareliya':
		case 'krasnoyarsk':
		case 'perm':
			dateX = new Date(2019,5,1);
			break;

		default:
			dateX = new Date(2019,4,6);
			break;
	}

	return dateX;
}

/**
 * Получение куки с заданным именем
 */
function getCookie(name) {
	var cookie = " " + document.cookie;
	var search = " " + name + "=";
	var setStr = null;
	var offset = 0;
	var end = 0;
	if (cookie.length > 0) {
		offset = cookie.indexOf(search);
		if (offset != -1) {
			offset += search.length;
			end = cookie.indexOf(";", offset)
			if (end == -1) {
				end = cookie.length;
			}
			setStr = unescape(cookie.substring(offset, end));
		}
	}
	return(setStr);
}

function vacLpuContr() {
	var result = false;
	// if (getGlobalOptions().lpu_id == 35)
	// if (sw.Promed.vac.utils.lpuAccess(getGlobalOptions().lpu_id) || getGlobalOptions().region.nick != 'ufa')
	result = true;

	return result;
}

/**
 * Получение zIndex, чтобы менюшки были поверх окон
 */
function getActiveZIndex() {
	var win = getActiveWin();
	if (win) {
		var el = win.getEl();
		if (el && typeof el.getZIndex == 'function') {
			return el.getZIndex();
		}
	}

	return 0;
}

/**
 * Признак "Врач ДЛО на текущую дату"
 */
function isDLOUser(onDate) {
	if ( Ext.isEmpty(getGlobalOptions().medpersonal_id) ) {
		return false;
	}

	if ( Ext.isEmpty(onDate) ) {
		onDate = getGlobalOptions().date;
	}

	setMedStaffFactGlobalStoreFilter({
		MedPersonal_id: getGlobalOptions().medpersonal_id,
		isDlo: true,
		onDate: onDate
	});

	return (swMedStaffFactGlobalStore.getCount() > 0);
}

/******* isWorkShift **********************************************************
 * #175117
 * Предусмотрена ли фиксация начала и окончания смены в отделении.
 * Если отделение не указано, берется из глобальных опций.
 * Возвращает true, если отделение относится к типу:
 *  1. Круглосуточный стационар (1)
 *  2. Дневной стационар при стационаре (6)
 *  3. Дневной стационар при поликлинике (9)
 *  4. Приемные (17)
 * В остальных случаях возвращает false.
 *
 * LpuUnitType_id - идентификатор типа отделения
 ******************************************************************************/
function isWorkShift(LpuUnitType_id)
{
	if (!(LpuUnitType_id =
			LpuUnitType_id ||
			getGlobalOptions().CurLpuUnitType_id))
		return false;

	return LpuUnitType_id.toString().inlist(['1', '6', '9', '17']);
}

/******* doIfOnWorkShift ******************************************************
 * #175117
 * Если в журнале учета рабочего времени сотрудников есть смены указанного
 * пользователя, в которые входят текущие дата и время, запускает указанную
 * функцию, передавая ей текущие дату и время и найденные смены в качестве
 * параметров (если журнал заполнен корректно, такая смена должна быть одна).
 * pmUser_id - идентификатор пользователя;
 * onFindWorkShift - функция, вызываемая по результатам поиска рабочей смены,
 *  параметры функции:
 *   pmUser_id - идентификатор пользователя
 *   dt - дата и время
 *   records - массив записей, найденных в журнале учета рабочего времени;
 * scope - объект, в контексте которого выполняется функция
 ******************************************************************************/
function doIfOnWorkShift(pmUserId, onFindWorkShift, scope)
{
	getCurrentDateTime({ callback: _gcdtCallback });

	/******* _gcdtCallback ****************************************************
	* Если есть рабочая смена пользователя pmUserId, в которую попадают дата и
	* время, указанные в result, вызывает функцию onFindWorkShift.
	*/
	function _gcdtCallback(result)
	{
		var curDT =
				Date.parse(result.date.replace(/(\d+).(\d+).(\d+)/, '$3/$2/$1') +
							' ' + result.time);

		// Ищем смену в журнале учета рабочего времени сотрудников:
		Ext.Ajax.request(
			{
				url: '/?c=TimeJournal&m=loadTimeJournal',

				params:
				{
					pmUser_tid: pmUserId,
					currentDateTime: curDT
				},

				callback:
					function(options, success, response)
					{
						var record;

						if (!success)
						{
							sw.swMsg.alert(langs('oshibka'),
											langs('Ошибка чтения журнала учета рабочего времени сотрудников'));
							return;
						}

						onFindWorkShift.call(
							scope,
							pmUserId,
							curDT,
							Ext.util.JSON.decode(response.responseText));
					}
			});
	}
}

/******* _setCbWorkShift ******************************************************
 * #175117
 * Устанавливает состояние флага "Я на смене" в соответствии с тем, переданы
 * ли в параметре records смены врача.
 ******************************************************************************/
function _setCbWorkShift(pmUserId, dt, records)
{
	var v,
		cbWorkShift;

	if (!cbWorkShift)
	if (!((v = main_menu_panel) && (v = v.items) && (v = v.map) &&
			(cbWorkShift = v.cbWorkShift)))
		return;

	cbWorkShift.setValue(records && (records.length > 0));
};

/******* refreshCbWorkShift ***************************************************
 * #175117
 * Актуализирует видимость и состояние флага "Я на смене" в соответствии с
 * данными по текущему врачу в журнале учета рабочего времени сотрудников
 * (TimeJournal).
 * 1. Если на текущий момент все смены врача завершены, флаг скрывается.
 * 2. В противном случае флаг остается видимым, но обновляется его состояние в
 *    зависимости от того, находится ли врач на смене, и планируется запуск
 *    очередной актуализации флага через 1 час.
 ******************************************************************************/
function refreshCbWorkShift()
{
	var v,
		cbWorkShift,
		pmUserId = getGlobalOptions().pmuser_id;

	if (!pmUserId ||
		!((v = main_menu_panel) && (v = v.items) && (v = v.map) &&
			(cbWorkShift = v.cbWorkShift)))
		return;

	// 1. Запрашиваем текущие дату и время:
	getCurrentDateTime(
		{
			callback:
				function(result)
				{
					var curDT = Date.parse(result.date.replace(/(\d+).(\d+).(\d+)/,
															   '$3/$2/$1') +
											' ' + result.time);

					// 2. Запрашиваем смены, не завершенные до текущего момента:
					Ext.Ajax.request(
						{
							url: '/?c=TimeJournal&m=loadTimeJournal',

							params:
							{
								pmUser_tid: pmUserId,
								minEndDT: curDT
							},

							callback:
								function(options, success, response)
								{
									var records;

									if (!success)
									{
										sw.swMsg.alert(langs('oshibka'),
													   langs('Ошибка чтения журнала учета рабочего времени сотрудников'));
										return;
									}

									records = Ext.util.JSON.decode(response.responseText);

									if (records && records.length > 0)
									// 3. Есть незавершенные смены - флаг оставляем видимым:
									{
										// 3.1. Ищем текущую смену, все действия - в callback-функции:
										Ext.Ajax.request(
											{
												url: '/?c=TimeJournal&m=loadTimeJournal',

												params:
												{
													pmUser_tid: pmUserId,
													currentDateTime: curDT
												},

												callback:
													function(options, success, response)
													{
														var records;

														if (!success)
														{
															sw.swMsg.alert(langs('oshibka'),
																		   langs('Ошибка чтения журнала учета рабочего времени сотрудников'));

															return;
														}

														records = Ext.util.JSON.decode(response.responseText);

														// 3.2. Актуализируем состояние флага в зависимости
														// от того, найдена ли смена:
														cbWorkShift.setValue(records && (records.length > 0));
													}
											});

										// 3.3. Планируем очередную актуализацию флага через 1 час:
										setTimeout(hideCbWorkShift, 60 * 60 * 1000);
									}
									else
										// 4. Незавершенных смен нет - скрываем флаг:
										cbWorkShift.setVisible(false);
								}
						});
				}
		});
}

/**
 * JSON ENCODER
 * @param $obj
 * @returns string
 */
function jsonEncode($obj) {
	return Ext.util.JSON.encode($obj);
}

/**
 * JSON DECODER
 * @param jsonString
 * @returns string
 */
function jsonDecode(jsonString) {
	var result;
	try {
		result = Ext.util.JSON.decode(jsonString);
	} catch(e) {
		warn(e);
		warn('JSON STRING: \r\n' + jsonString);
	}
	return result;
}

/**
 * Общее выполнение запросов с маской и обработкой ошибок
 */
function ajaxRequest(opt) {
	var mask = false;

	if(opt.maskEl) {
		mask = new Ext.LoadMask(opt.maskEl, { msg:opt.maskText || '' });
		mask.show();
	}

	var params = {};
	params.url = opt.url;
	params.params = opt.params;

	params.callback = function(options, success, response) {
		if(mask) {
			mask.hide();
		}
		var resp_obj = jsonDecode(response.responseText);

		if ( !resp_obj || !success || resp_obj.Error_Code || resp_obj.Error_Msg ) {
			if (typeof opt.onError === 'function') {
				opt.onError(resp_obj);
			}
			return;
		}

		if (typeof opt.onSuccess === 'function') {
			opt.onSuccess(resp_obj);
		}
	};
	Ext.Ajax.request(params);
}

/**
 * ---
 */
function pneumoAlert(callback) {
	Ext.Msg.show({
		buttons: Ext.Msg.OK,
		//icon: Ext.Msg.WARNING,
		fn: function() {
			if (!!callback && typeof callback == 'function') {
				callback();
			}
		},
		msg: "<div style=\"font-size: 1.4em; text-align: center\">ЕСЛИ<br> поступил пациент (СМП, самостоятельно, или иными каналами с извещением о поступлении) с диагнозом:<br>" +
			"ВНЕБОЛЬНИЧНАЯ ПНЕВМОНИЯ<br>" +
			"<br>" +
			"Медицинскому персоналу приемного отделения:<br>" +
			"<br>" +
			"НЕМЕДЛЕННО перенаправить пациента(ку) в<br>" +
			"ГБУЗ МО \"Мытищинская ГБ\"<br>" +
			"<br>" +
			"желательно - тем же транспортом, не допуская возможной контаминации!<br>" +
			"<br>" +
			"ЕСЛИ<br>" +
			"диагноз устанавливается в приемном или профильном отделении <br>" +
			"НЕМЕДЛЕННО обеспечить персонал СИЗ и организовать транспортировку в<br>" +
			"ГБУЗ МО \"Мытищинская ГБ\"<br>" +
			"<br>" +
			"оказав, при необходимости неотложную медицинскую помощь в СИЗ!</div>",
		title: "<div style=\"font-size: 1.4em;\">ВНИМАНИЕ!</div>",
		minWidth: 600
	});
}

/**
 * Диагнозы пневмонии
 */
function isPneumoDiag(Diag_Code) {
	
	if (!Diag_Code) return false;
	
	var p_diags = [
		'B05.2', 'J10.0', 'J11.0', 'J12', 'J12.0', 'J12.1', 'J12.2', 'J12.3', 'J12.8', 'J12.9', 'J13', 'J14', 'J15', 'J15.0', 'J15.1', 'J15.2', 
		'J15.3', 'J15.4', 'J15.5', 'J15.6', 'J15.7', 'J15.9', 'J16', 'J16.0', 'J16.8', 'J17', 'J17.0', 'J17.1', 'J17.2', 'J17.3', 'J17.8', 'J18', 
		'J18.1', 'J18.2', 'J18.8', 'J18.9', 'P23.0', 'P23.1', 'P23.2', 'P23.3', 'P23.4', 'P23.5', 'P23.6', 'P23.8', 'P23.9', 'U04'
	];
	
	if (Diag_Code.inlist(p_diags)) return true;
	
	return false;
}

/******* isWorkShift **********************************************************
 * #175117
 * Предусмотрена ли фиксация начала и окончания смены в отделении.
 * Если отделение не указано, берется из глобальных опций.
 * Возвращает true, если отделение относится к типу:
 *  1. Круглосуточный стационар (1)
 *  2. Дневной стационар при стационаре (6)
 *  3. Дневной стационар при поликлинике (9)
 *  4. Приемные (17)
 * В остальных случаях возвращает false.
 *
 * LpuUnitType_id - идентификатор типа отделения
 ******************************************************************************/
function isWorkShift(LpuUnitType_id)
{
	if (!(LpuUnitType_id =
			LpuUnitType_id ||
			getGlobalOptions().CurLpuUnitType_id))
		return false;

	return LpuUnitType_id.toString().inlist(['1', '6', '9', '17']);
}

/******* doIfOnWorkShift ******************************************************
 * #175117
 * Если в журнале учета рабочего времени сотрудников есть смены указанного
 * пользователя, в которые входят текущие дата и время, запускает указанную
 * функцию, передавая ей текущие дату и время и найденные смены в качестве
 * параметров (если журнал заполнен корректно, такая смена должна быть одна).
 * pmUser_id - идентификатор пользователя;
 * onFindWorkShift - функция, вызываемая по результатам поиска рабочей смены,
 *  параметры функции:
 *   pmUser_id - идентификатор пользователя
 *   dt - дата и время
 *   records - массив записей, найденных в журнале учета рабочего времени;
 * scope - объект, в контексте которого выполняется функция
 ******************************************************************************/
function doIfOnWorkShift(pmUserId, onFindWorkShift, scope)
{
	getCurrentDateTime({ callback: _gcdtCallback });

	/******* _gcdtCallback ****************************************************
	* Если есть рабочая смена пользователя pmUserId, в которую попадают дата и
	* время, указанные в result, вызывает функцию onFindWorkShift.
	*/
	function _gcdtCallback(result)
	{
		var curDT =
				Date.parse(result.date.replace(/(\d+).(\d+).(\d+)/, '$3/$2/$1') +
							' ' + result.time);

		// Ищем смену в журнале учета рабочего времени сотрудников:
		Ext.Ajax.request(
			{
				url: '/?c=TimeJournal&m=loadTimeJournal',

				params:
				{
					pmUser_tid: pmUserId,
					currentDateTime: curDT
				},

				callback:
					function(options, success, response)
					{
						var record;

						if (!success)
						{
							sw.swMsg.alert(langs('oshibka'),
											langs('Ошибка чтения журнала учета рабочего времени сотрудников'));
							return;
						}

						onFindWorkShift.call(
							scope,
							pmUserId,
							curDT,
							Ext.util.JSON.decode(response.responseText));
					}
			});
	}
}

/******* _setCbWorkShift ******************************************************
 * #175117
 * Устанавливает состояние флага "Я на смене" в соответствии с тем, переданы
 * ли в параметре records смены врача.
 ******************************************************************************/
function _setCbWorkShift(pmUserId, dt, records)
{
	var v,
		cbWorkShift;

	if (!cbWorkShift)
	if (!((v = main_menu_panel) && (v = v.items) && (v = v.map) &&
			(cbWorkShift = v.cbWorkShift)))
		return;

	cbWorkShift.setValue(records && (records.length > 0));
};

/******* refreshCbWorkShift ***************************************************
 * #175117
 * Актуализирует видимость и состояние флага "Я на смене" в соответствии с
 * данными по текущему врачу в журнале учета рабочего времени сотрудников
 * (TimeJournal).
 * 1. Если на текущий момент все смены врача завершены, флаг скрывается.
 * 2. В противном случае флаг остается видимым, но обновляется его состояние в
 *    зависимости от того, находится ли врач на смене, и планируется запуск
 *    очередной актуализации флага через 1 час.
 ******************************************************************************/
function refreshCbWorkShift()
{
	var v,
		cbWorkShift,
		pmUserId = getGlobalOptions().pmuser_id;

	if (!pmUserId ||
		!((v = main_menu_panel) && (v = v.items) && (v = v.map) &&
			(cbWorkShift = v.cbWorkShift)))
		return;

	// 1. Запрашиваем текущие дату и время:
	getCurrentDateTime(
		{
			callback:
				function(result)
				{
					var curDT = Date.parse(result.date.replace(/(\d+).(\d+).(\d+)/,
															   '$3/$2/$1') +
											' ' + result.time);

					// 2. Запрашиваем смены, не завершенные до текущего момента:
					Ext.Ajax.request(
						{
							url: '/?c=TimeJournal&m=loadTimeJournal',

							params:
							{
								pmUser_tid: pmUserId,
								minEndDT: curDT
							},

							callback:
								function(options, success, response)
								{
									var records;

									if (!success)
									{
										sw.swMsg.alert(langs('oshibka'),
													   langs('Ошибка чтения журнала учета рабочего времени сотрудников'));
										return;
									}

									records = Ext.util.JSON.decode(response.responseText);

									if (records && records.length > 0)
									// 3. Есть незавершенные смены - флаг оставляем видимым:
									{
										// 3.1. Ищем текущую смену, все действия - в callback-функции:
										Ext.Ajax.request(
											{
												url: '/?c=TimeJournal&m=loadTimeJournal',

												params:
												{
													pmUser_tid: pmUserId,
													currentDateTime: curDT
												},

												callback:
													function(options, success, response)
													{
														var records;

														if (!success)
														{
															sw.swMsg.alert(langs('oshibka'),
																		   langs('Ошибка чтения журнала учета рабочего времени сотрудников'));

															return;
														}

														records = Ext.util.JSON.decode(response.responseText);

														// 3.2. Актуализируем состояние флага в зависимости
														// от того, найдена ли смена:
														cbWorkShift.setValue(records && (records.length > 0));
													}
											});

										// 3.3. Планируем очередную актуализацию флага через 1 час:
										setTimeout(hideCbWorkShift, 60 * 60 * 1000);
									}
									else
										// 4. Незавершенных смен нет - скрываем флаг:
										cbWorkShift.setVisible(false);
								}
						});
				}
		});
}

/**
 * yl:5588 Действующие лекарственные наначения для пациента
 */
function checkPersonPrescrTreat(Person_id,panel,callback){
	panel.mask('Подождите, проводится проверка количества назначенных препаратов');
	Ext.Ajax.request({
		url: '/?c=EvnPrescr&m=checkPersonPrescrTreat',
		params: {
			Person_id: Person_id
		},
		callback: function (opt, success, response) {
			panel.unmask();
			if (success && response && response.responseText && (result = Ext.util.JSON.decode(response.responseText))) {
				if (!Ext.isEmpty(result.totalCount) && result.totalCount>=5) {
					panel.blockHide = false;
					panel.addListener("beforehide",function(cmp,eOpts){return cmp.blockHide;});//чтобы меню не исчезло
					Ext6.MessageBox.show({
						icon: Ext6.Msg.WARNING,
						title: "Внимание!",
						msg: "Пациенту назначено более пяти препаратов.<br>Возможно появление побочного эффекта.",
						buttons: Ext6.Msg.YESNO,
						buttonText: {
							no : "Продолжить",
							yes : "Отмена"
						},
						fn: function(buttonId) {
							panel.blockHide = true;//нормальная работа
							if (buttonId=="yes") {//Отмена
								panel.hide();
							}else if(callback && typeof callback == "function"){
								callback();
							}
						}
					});
				}else if(callback && typeof callback == "function"){
					callback();
				}
			}
		}
	});
}
//yl:5588 проверка препаратов в пакете на количество, аллергии и пересечения 
function checkPacketPrescrTreat(panel,mode,cbFn,resp,stage){
	var me = this;

	if(!resp || !resp.hasOwnProperty("totalCount") || !resp.hasOwnProperty("allergic") || !resp.hasOwnProperty("reaction")){
		Ext6.Msg.alert(langs("Ошибка"), langs("Ошибка проверки пакетных назначений"));
		return;
	};

	if(!stage){//первое сообщение на количество
		if(resp.totalCount>=5){
			Ext6.MessageBox.show({
				icon: Ext6.Msg.WARNING,
				title: "Внимание!",
				msg: "Пациенту назначено более пяти препаратов.<br>Возможно появление побочного эффекта.",
				buttons: Ext6.Msg.YESNO,
				buttonText: {
					no : "Продолжить",
					yes : "Отмена"
				},
				fn: function(buttonId) {
					if (buttonId=="no") {//Продолжить
						checkPacketPrescrTreat(panel,mode,cbFn,resp,2);
					}else if(cbFn && typeof cbFn == "function"){//в полном режиме пакетов
						panel.unmask();
						cbFn();//включить кнопку
					}
				}
			});
		}else{
			checkPacketPrescrTreat(panel,mode,cbFn,resp,2);
		};

	}else{//второе на аллергии и пересечения вместе
		var msg="";
		for(var i=0;i<resp.allergic.length;i++){
			msg+="У пациента выявлена аллергическая реакция на препарат \""+resp.allergic[i].Drug_Name+"\"!"+"<br><br>";
		};
		for(var i=0;i<resp.reaction.length;i++){
			msg+="При использовании препарата '" + resp.reaction[i].DrugComplexMnn_RusName2 + "' в комплексе с препаратом '" + resp.reaction[i].DrugComplexMnn_RusName + "' (ТАП №" + resp.reaction[i].EvnPL_NumCard + " от " + resp.reaction[i].EvnPrescrTreat_setDT + ") возможны побочные эффекты"+"<br><br>";
		};
		if(msg!=""){
			Ext6.MessageBox.show({
				icon: Ext6.Msg.WARNING,
				title: "Внимание!",
				msg: msg,
				buttons: Ext6.Msg.YESNO,
				buttonText: {
					no : "Продолжить",
					yes : "Отмена"
				},
				fn: function(buttonId) {
					if (buttonId=="no") {//Продолжить
						panel.doSave(mode,cbFn);
					}else if(cbFn && typeof cbFn == "function"){//в полном режиме пакетов
						panel.unmask();
						cbFn();//включить кнопку
					}
				}
			});
		}else{
			panel.doSave(mode,cbFn);
		};
	};
}

function checkUslugaAttribute (code, attributeList) {
	var flag = false;
	for (var i = 0; i < attributeList.length; i++) {
		if (attributeList[i].UslugaComplexAttributeType_Code == code) {
			flag = true;
			break;
		}
	}
	return flag;
}

function formatPhone(phone, format='+7($1)-$2-$3-$4') {
	phone = String(phone);
	var regexp = /(\d{3})(\d{3})(\d{2})(\d{2})( \(БД\))?$/;
	if (regexp.test(phone)) {
		phone = phone.replace(regexp,format);
	}
	return phone;
}