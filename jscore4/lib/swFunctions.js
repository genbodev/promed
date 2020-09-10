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
			sw.swMsg.alert('Ошибка', 'При проверке человека возникли ошибки!');
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
			sw.swMsg.alert('Ошибка', 'При проверке человека произошла ошибка запроса к БД!');
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
			sw.swMsg.alert('Ошибка', 'При поиске человека возникли ошибки!');
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
				sw.swMsg.alert('Ошибка', 'По заданным параметрам ни одного человека не найдено!');
				return;
			}
			if(typeof option.failure == 'function') {
				option.failure(response, options);
				return;
			}
			sw.swMsg.alert('Ошибка', 'При поиске человека произошла ошибка запроса к БД!');
		}
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
		sw.swMsg.alert('Ошибка', 'Не задано имя отчёта для формирования.');
		return false;
	}
	if (Ext.isEmpty(options.Report_Params)) {
		options.Report_Params = '';
	}
	if (Ext.isEmpty(options.Report_Format)) {
		options.Report_Format = '';
	}

	// 1. напрямую
	// window.open(((getGlobalOptions().birtpath)?getGlobalOptions().birtpath:'')+'/run?__report=report/'+options.Report_FileName + options.Report_Params + '&__format=' + options.Report_Format, '_blank');
	// 2. через прокси
	window.open('/?c=ReportRun&m=RunByFileName&Report_FileName='+options.Report_FileName+'&Report_Params='+encodeURIComponent(options.Report_Params)+'&Report_Format='+options.Report_Format, '_blank');
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
	sw.Promed.debug_wnd = Ext.create('Ext.Window',{
		autoScroll: true,
		width: 700,
		height: 500,
		maximizable: true,
		closeAction: 'close',
		collapsible: true,
		title: 'Отладочная информация',
		items: [
		Ext.create('Ext.Panel',{
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
	var response = {success: false, ErrorCode: 1, ErrorMessage: 'Произошла ошибка определения списка.'};
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
						response.ErrorMessage = 'Не удалось получить список устройств чтения карт.';
					}
				}
				else
				{
					response.ErrorCode = 5;
					response.ErrorMessage = 'Не удалось получить список устройств чтения карт.';
				}
			}
			catch (e)
			{
				response.ErrorCode = 4;
				response.ErrorMessage = 'Произошла ошибка чтения карты.';
			}
		}
		else
		{
			response.ErrorCode = 3;
			response.ErrorMessage = 'Нет доступа к методам плагина. Попробуйте обновить страницу. В крайнем случае возможно прийдется обновить Java-plugin в браузере.';
		}
	}
	else
	{
		response.ErrorCode = 2;
		response.ErrorMessage = 'Не найден плагин для чтения данных картридера.';
	}
	return response;
}

// получение идентификатора с картридера
function getSocCardNumFromReader()
{
	var response = {success: false, ErrorCode: 1, ErrorMessage: 'Произошла ошибка определения идентификатора.'};
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
						response.ErrorMessage = 'Карта не вставлена.';
					}
				}
				else
				{
					response.ErrorCode = 5;
					response.ErrorMessage = 'Не удалось получить список устройств чтения карт.';
				}
			}
			catch (e)
			{
				response.ErrorCode = 4;
				response.ErrorMessage = 'Произошла ошибка чтения карты.';
			}
		}
		else
		{
			response.ErrorCode = 3;
			response.ErrorMessage = 'Нет доступа к методам плагина. Попробуйте обновить страницу. В крайнем случае возможно прийдется обновить Java-plugin в браузере.';
		}
	}
	else
	{
		response.ErrorCode = 2;
		response.ErrorMessage = 'Не найден плагин для чтения данных картридера.';
	}
	return response;
}
/**
 * Создание списка карт-ридеров доступных в системе
 */
function initCardReaders() {
	log('Инициализируем список ридеров');
	sw.Applets.readers = [];
	var readers = getSocCardReadersArray();
	if (readers && (!readers.ErrorMessage || String(readers.ErrorMessage) == '')) {
		// todo: в список ридеров с данным плагином еще попадает test из апплета, который там видимо прибит гвоздями, можно его исключать
		sw.Applets.readers = readers.readersArray;
	} else {
		log('Картридер не определен, используется ридер по умолчанию');
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
					result['grid'][n].hideable = false;
				} else {
					result['grid'][n].hidden = true;
					result['grid'][n].hideable = false;
				}
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
						result['grid'][n].renderer = sw.Promed.Format.checkColumn;
						break;
					case 'checkcolumn':
						result['store'][n].type = 'string';
						result['grid'][n].renderer = sw.Promed.Format.checkColumn;
						break;
					case 'checkcolumnedit':
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
					case 'datetimesec':
						result['store'][n].dateFormat = 'd.m.Y H:i:s';
						result['store'][n].type = 'date';
						result['grid'][n].renderer = Ext.util.Format.dateRenderer('d.m.Y H:i:s');
						break;
					case 'rownumberer':
						result['grid'][n] = Ext.create('Ext.grid.RowNumberer');
						break;
					default:
						result['store'][n].type = arr[i]['type'];
						break;
				}
			}
			else if (arr[i]['renderer'])
			{
				result['grid'][n].renderer = arr[i]['renderer'];
			}
			else
			{
				result['store'][n].type = 'string';
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

			// Редактор
			if (arr[i]['editor']!=undefined)
			{
				result['grid'][n].editor = arr[i]['editor'];
				result['editing'] = true;
			}

			// Заголовки колонок грида
			if (arr[i]['header']!=undefined)
			{
				result['grid'][n].header = arr[i]['header'];
			}
			else
			{
				result['grid'][n].header = 'Не определено!';
			}

			// Преобразуем checkcolumnedit в CheckColumnEdit )
			if (arr[i]['type']=="checkcolumnedit") {
				var checkcolumn = new Ext.grid.CheckColumnEdit(result['grid'][n]);
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

	while (record || result == 0)
	{
		result = Math.floor(Math.random() * 1000000);
		record = store.getById(result);
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
	if (section != undefined) {
		section = section.replace(/ /g, '_');
		//https://redmine.swan.perm.ru/issues/13024
		//при вызове помощи из Армов в section кидалась длиннющая строка с тегами ссылки и проч. Поэтому в таких случаях нужно обрубать ее и оставлять только нужное.
		if(section.indexOf('href')>0) {
			section = section.substr(section.indexOf('>')+1,section.indexOf('_/')-section.indexOf('>')-1);
		}
		if (getGlobalOptions().region) {
            window.open(getGlobalOptions().wikipath + section);
		}
        else { // вдруг регион не определен
			window.open('/wiki/main/wiki/' + section);
		}
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
			}.bind(self)
		};
		if (tbidx != undefined)
		{
			btnCfg.tabIndex = tbidx;
		}
		return Ext.create('Ext.Button',(btnCfg));
	}
}

/**
*  Получение записей из Store
*  Входящие данные: store - хранилище данных
*                   options - параметры получения
*  На выходе: массив записей
//*/
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
	}
	else
	{
		if (!options.convertDateFields) options.convertDateFields = false;
		if (!options.dateFormat) options.dateFormat = 'd.m.Y';
		if (!options.exceptionFields) options.exceptionFields = new Array();
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
			case 'й': str_new = str_new + 'Q'; break;
			case 'Й': str_new = str_new + 'Q'; break;
			case 'ц': str_new = str_new + 'W'; break;
			case 'Ц': str_new = str_new + 'W'; break;
			case 'у': str_new = str_new + 'E'; break;
			case 'У': str_new = str_new + 'E'; break;
			case 'к': str_new = str_new + 'R'; break;
			case 'К': str_new = str_new + 'R'; break;
			case 'е': str_new = str_new + 'T'; break;
			case 'Е': str_new = str_new + 'T'; break;
			case 'н': str_new = str_new + 'Y'; break;
			case 'Н': str_new = str_new + 'Y'; break;
			case 'г': str_new = str_new + 'U'; break;
			case 'Г': str_new = str_new + 'U'; break;
			case 'ш': str_new = str_new + 'I'; break;
			case 'Ш': str_new = str_new + 'I'; break;
			case 'щ': str_new = str_new + 'O'; break;
			case 'Щ': str_new = str_new + 'O'; break;
			case 'з': str_new = str_new + 'P'; break;
			case 'З': str_new = str_new + 'P'; break;
			case 'ф': str_new = str_new + 'A'; break;
			case 'Ф': str_new = str_new + 'A'; break;
			case 'ы': str_new = str_new + 'S'; break;
			case 'Ы': str_new = str_new + 'S'; break;
			case 'в': str_new = str_new + 'D'; break;
			case 'В': str_new = str_new + 'D'; break;
			case 'а': str_new = str_new + 'F'; break;
			case 'А': str_new = str_new + 'F'; break;
			case 'п': str_new = str_new + 'G'; break;
			case 'П': str_new = str_new + 'G'; break;
			case 'р': str_new = str_new + 'H'; break;
			case 'Р': str_new = str_new + 'H'; break;
			case 'о': str_new = str_new + 'J'; break;
			case 'О': str_new = str_new + 'J'; break;
			case 'л': str_new = str_new + 'K'; break;
			case 'Л': str_new = str_new + 'K'; break;
			case 'д': str_new = str_new + 'L'; break;
			case 'Д': str_new = str_new + 'L'; break;
			case 'я': str_new = str_new + 'Z'; break;
			case 'Я': str_new = str_new + 'Z'; break;
			case 'ч': str_new = str_new + 'X'; break;
			case 'Ч': str_new = str_new + 'X'; break;
			case 'с': str_new = str_new + 'C'; break;
			case 'С': str_new = str_new + 'C'; break;
			case 'м': str_new = str_new + 'V'; break;
			case 'М': str_new = str_new + 'V'; break;
			case 'и': str_new = str_new + 'B'; break;
			case 'И': str_new = str_new + 'B'; break;
			case 'т': str_new = str_new + 'N'; break;
			case 'Т': str_new = str_new + 'N'; break;
			case 'ь': str_new = str_new + 'M'; break;
			case 'Ь': str_new = str_new + 'M'; break;
			case ',': str_new = str_new + '.'; break;
			case 'ю': str_new = str_new + '.'; break;
			case 'Ю': str_new = str_new + '.'; break;
			case 'б': str_new = str_new + '.'; break;
			case 'Б': str_new = str_new + '.'; break;
			default: str_new = str_new +str.charAt(i);
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
	// TODO: Единственное место, которое я не знаю как исправить на getWnd
	var unionWindow = swGetWindow('swRecordUnionWindow');
	if (callback != undefined) {
		unionWindow.successFn = callback;
	}
	var unionGrid = Ext.getCmp('RUW_RecordGrid');
	var unionInfo = Ext.getCmp('RUW_InfoPanel');
	if (unionWindow.RecordType_Code != null && unionWindow.RecordType_Code != RecordType_Code) {
		unionWindow.toFront();
		sw.swMsg.alert('Внимание','Вы уже начали выбирать записи другого типа: <b>'+unionWindow.RecordType_Name+ '</b>!');
		return false;
	}

	if (unionWindow.hidden) {
		unionWindow.show();
	}
	else {
		unionWindow.toFront();
	}

	unionWindow.RecordType_Code = RecordType_Code;
	unionWindow.RecordType_Name = RecordType_Name;
	unionInfo.items.items[0].getStore().removeAll();
	unionInfo.items.items[0].getStore().loadData([{
		RecordType_Name: unionWindow.RecordType_Name
	}]);

	var recStore = Ext.getCmp('RUW_RecordGrid').getStore();
	var IsMainRec = 0;
	if (recStore.getCount() == 0)
		IsMainRec = 1;
	// Добавляем эти данные в грид для объединения в окне Объединения
	if ( RecordType_Code == 'MedStaffFact' ) {
		recStore.loadData(
			[{
				Record_id: selRec.data['MedStaffFact_id'],
				Record_Name: selRec.data['MedPersonal_FIO'],
				Record_Code: selRec.data['MedPersonal_TabCode'],
				IsMainRec: IsMainRec
			}], true );
	} else
	if ( RecordType_Code == 'MedPersonal' ) {
		recStore.loadData(
			[{
				Record_id: selRec.data['MedPersonal_id'],
				Record_Name: selRec.data['Person_SurName'] + ' ' + selRec.data['Person_FirName'] + ' ' + selRec.data['Person_SecName'],
				Record_Code: selRec.data['MedPersonal_Code'],
				IsMainRec: IsMainRec
			}], true );
	} else {
		recStore.loadData(
			[{
				Record_id: selRec.data[RecordType_Code+ '_id'],
				Record_Name: selRec.data[RecordType_Code+ '_Name'],
				Record_Code: selRec.data[RecordType_Code+ '_Code'],
				IsMainRec: IsMainRec
			}], true );
	}
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
	var unionWindow = swGetWindow('swPersonUnionWindow');
	if (callback != undefined) {
		unionWindow.successFn = callback;
	}
	var unionGrid = Ext.getCmp('PUW_RecordGrid');

	if (unionWindow.hidden) {
		unionWindow.show();
	}
	else {
		unionWindow.toFront();
	}

	var recStore = Ext.getCmp('PUW_RecordGrid').getStore();
	var IsMainRec = 0;
	if (recStore.getCount() == 0)
		IsMainRec = 1;
	// Добавляем эти данные в грид для объединения в окне Объединения
	recStore.loadData(
		[{
			Person_id: selRec.data['Person_id'],
			Person_Surname: selRec.data['PersonSurName_SurName'],
			Person_Firname: selRec.data['PersonFirName_FirName'],
			Person_Secname: selRec.data['PersonSecName_SecName'],
			Person_Birthdate: selRec.data['PersonBirthDay_BirthDay'],
			Server_id: selRec.data['Server_id'],
			IsMainRec: IsMainRec
		}], true );
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
		if ( arr[i].hiddenName ) {
			params[arr[i].hiddenName] = arr[i].getValue();
		}
		else if ( arr[i].name ) {
			params[arr[i].name] = arr[i].getValue();
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
				'Ошибка',
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
			msg: 'Окно уже открыто',
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

//function swGetWindow(window_name, callback1, callback2)
//{
//
//	var result = null;
//	var result_was_null = false;
//
//	if (window[window_name] == null)
//	{
//		if (sw.Promed[window_name] != undefined)
//		{
//			result_was_null = true;
//			window[window_name] = new sw.Promed[window_name]();
//		}
//	}
//
//	result = window[window_name];
//
//	if (result_was_null == true)
//	{
//		main_center_panel.add(result);
//		main_center_panel.doLayout();
//
//		if (callback1 != undefined && callback1 != null)
//		{
//			callback1(result);
//		}
//	}
//	else
//	{
//		if (callback2 != undefined && callback2 != null)
//		{
//			callback2(result);
//		}
//	}
//
//	return result;
//}


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
			if (sw.roles[wnd.objectClass]) {
				params.roles = sw.roles[wnd.objectClass];
			} else {
				// если права не пришли (это формы которые грузятся при старте промеда, а не через getJSFile), то им разрешено всё. // TODO надо как то получать и права на них тоже.
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

	globalPanel = Ext.ComponentQuery.query('gloPanel')
	//console.log('result', result)

//	if (result_was_null == true)
//	{
//		// Добавляем окно в панель
//		main_center_panel.add(result);
//		main_center_panel.doLayout();
//		console.log('MCP - ', main_center_panel)
//	}
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
				title: 'Сообщение',
				msg: 'Окно "'+win.title+'" уже открыто.'
			});
			return false;
		}
		sw.Promed.mask.hide();

		if (win) {
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
	//console.log(wnd)
	sw.Promed.mask.show();

	var result = null;
	var result_was_null = false;

	var params = (config && config.params && typeof config.params == 'object')?config.params:{};
	try
	{
		// Если класс окна не обнаружен, то загружаем и выполняем
		if (!sw.Promed[wnd.objectClass])
		{

			sw.codeInfo.lastObjectName = wnd.objectName;
			sw.codeInfo.lastObjectClass = wnd.objectClass;
//			if (sw.Promed.Actions.loadLastObjectCode)
//			{
//				sw.Promed.Actions.loadLastObjectCode.setHidden(false);
//				sw.Promed.Actions.loadLastObjectCode.setText('Обновить '+wnd.objectName+' ...');
//			}
			// Отправляем запрос на получение измененного JS-файла
			loadJsCode(wnd,
			function() // callback на чтение js-файла
			{
				show();
			});
		}
		else
		{
			// Иначе - просто выполняем
			show();
		}
	}
	catch(e)
	{
		if (IS_DEBUG==2)
			throw e;
		else {
			showFatalError(e, function() { show(); });
		}
	}
	finally
	{
//

	}
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
	if (WINDOWS_ALIAS.hasOwnProperty(wnd)) {
		var checkwin = Ext.ComponentQuery.query('window[refId='+this.ARMType+']')
		if (checkwin[0])
		{
			var windowsListToolbar = Ext.getCmp('windowsListToolbar'),
				windowsListToolbarButton = windowsListToolbar.down('button[refId="'+checkwin[0].id+'"]');

			if(windowsListToolbarButton){
				windowsListToolbarButton.toggle(true);

				windowsListToolbarButton.armId = config.id;
			}

			return checkwin[0];
		}
		else {
			return Ext.create(WINDOWS_ALIAS[wnd],{params_init: (config )?config:null});
		}
	}
//	else {
//		sw.swMsg.show(
//			{
//				buttons: Ext.Msg.OK,
//				fn: function()
//				{
//					win.toFront();
//				},
//				icon: Ext.Msg.WARNING,
//				title: 'Сообщение',
//				msg: 'Окно "'+wnd+'" не найдено.'
//			})
//		return false;
//	}
	// TODO: на данный момент - это временно. в дальнейшем надо будет оставить только ветку else

	if (typeof wnd !== 'object') {
		wnd = {objectName: wnd, objectClass: wnd};
	}

	var win = swGetWnd(wnd, {params_init: (config && config.params)?config.params:null});
	// TODO: а пока здесь тоже можно включить проверку на уже открытую форму, и не открывать ее, а активировать.
	if (win)
	{
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
				loadJsCode(this.wnd,
				function(success)
				{
					if (config && typeof config.callback == "function")
						config.callback(success);
				});
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
				swGetWnd(wnd).hide();
			}
		};
	}
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
	Ext.Msg.show({
		buttons: Ext.Msg.YESNO,
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
		title: 'Ошибка '+e.name
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
//function swGetWindowWithParams(window_name, params, callback1, callback2)
//{
//	var result = null;
//	var result_was_null = false;
//
//	if (window[window_name] == null)
//	{
//		if (sw.Promed[window_name] != undefined)
//		{
//			result_was_null = true;
//			if (params)
//				window[window_name] = new sw.Promed[window_name](params);
//			else
//				window[window_name] = new sw.Promed[window_name]();
//		}
//	}
//
//	result = window[window_name];
//	if (result_was_null == true)
//	{
//		main_center_panel.add(result);
//		main_center_panel.doLayout();
//
//		if (callback1 != undefined && callback1 != null)
//		{
//			callback1(result);
//		}
//	}
//	else
//	{
//		if (callback2 != undefined && callback2 != null)
//		{
//			callback2(result);
//		}
//	}
//
//	return result;
//}



/**
 * Загрузка списка подразделений в область глобальной видимости
 */
function loadLpuBuildingGlobalStore() {
	swLpuBuildingGlobalStore = Ext.create('Ext.data.Store',{
		autoLoad: true,
		reader: Ext.create('Ext.data.JsonReader',{
			id: 'LpuBuilding_id'
		}, [
			{ name: 'LpuBuilding_Code', mapping: 'LpuBuilding_Code' },
			{ name: 'LpuBuilding_id', mapping: 'LpuBuilding_id' },
			{ name: 'LpuBuilding_Name', mapping: 'LpuBuilding_Name' }
		]),
		proxy: {
			type: 'ajax',
			url: '/?c=Common&m=loadLpuBuildingList',
			reader: {
				type: 'json'
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
					setPromedInfo('Справочник отделений не загружен или пуст', 'lpusection-info');
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
 * Загрузка списка отделений в область глобальной видимости
 */
function loadLpuSectionGlobalStore() {
	swLpuSectionGlobalStore = Ext.create('Ext.data.Store',{
		autoLoad: false,
		reader: Ext.create('Ext.data.JsonReader',{
			id: 'LpuSection_id'
		}, [
			{ name: 'LpuSection_Code', mapping: 'LpuSection_Code' },
			{ name: 'LpuSection_id', mapping: 'LpuSection_id' },
			{ name: 'LpuSection_pid', mapping: 'LpuSection_pid' },
			{ name: 'LpuSection_Class', mapping: 'LpuSection_Class' },
			{ name: 'LpuSection_Name', mapping: 'LpuSection_Name' },
			{ name: 'LpuSectionProfile_id', mapping: 'LpuSectionProfile_id' },
			{ name: 'LpuSectionProfile_Code', mapping: 'LpuSectionProfile_Code' },
			{ name: 'LpuSectionProfile_Name', mapping: 'LpuSectionProfile_Name' },
			{ name: 'LpuSectionProfile_SysNick', mapping: 'LpuSectionProfile_SysNick' },
			{ name: 'Lpu_id', mapping: 'Lpu_id' },
			{ name: 'LpuBuilding_id', mapping: 'LpuBuilding_id' },
			{ name: 'LpuUnit_id', mapping: 'LpuUnit_id' },
			{ name: 'LpuUnitSet_Code', mapping: 'LpuUnitSet_Code' },
			{ name: 'LpuUnitType_id', mapping: 'LpuUnitType_id' },
			{ name: 'LpuUnitType_Code', mapping: 'LpuUnitType_Code' },
			{ name: 'LpuUnitType_SysNick', mapping: 'LpuUnitType_SysNick' },
			{ name: 'LpuSection_disDate', mapping: 'LpuSection_disDate' },
			{ name: 'LpuSection_setDate', mapping: 'LpuSection_setDate' }
		]),
		proxy: {
			type: 'ajax',
			url: C_LPUSECTION_LIST,
			reader: {
				type: 'json'
			}
		}
	});
	doLoadLpuSectionGlobalStore();
}


/**
 * Загрузка списка палат в область глобальной видимости
 */
function loadLpuSectionWardGlobalStore() {
	swLpuSectionWardGlobalStore = Ext.create('Ext.data.Store',{
		autoLoad: false,
		reader: Ext.create('Ext.data.JsonReader',{
			id: 'LpuSectionWard_id'
		}, [
			{ name: 'LpuSectionWard_id', mapping: 'LpuSectionWard_id' },
			{ name: 'LpuSection_id', mapping: 'LpuSection_id' },
			{ name: 'Sex_id', mapping: 'Sex_id' },
			{ name: 'LpuSectionWard_Name', mapping: 'LpuSectionWard_Name' },
			{ name: 'LpuSectionWard_disDate', mapping: 'LpuSectionWard_disDate' },
			{ name: 'LpuSectionWard_setDate', mapping: 'LpuSectionWard_setDate' }
		]),
		proxy: {
			type: 'ajax',
			url: C_LPUSECTIONWARD_LIST,
			reader: {
				type: 'json'
			}
		}
	});

	doLoadLpuSectionWardGlobalStore();
}


/**
 * Загрузка списка подразделений в область глобальной видимости
 */
function loadLpuUnitGlobalStore() {
	swLpuUnitGlobalStore = Ext.create('Ext.data.Store',{
		autoLoad: true,
		reader: Ext.create('Ext.data.JsonReader',{
			id: 'LpuUnit_id'
		}, [
			{ name: 'LpuUnit_id', mapping: 'LpuUnit_id' },
			{ name: 'LpuUnitType_id', mapping: 'LpuUnitType_id' },
			{ name: 'LpuBuilding_id', mapping: 'LpuBuilding_id' },
			{ name: 'LpuUnit_Code', mapping: 'LpuUnit_Code' },
			{ name: 'LpuUnit_Name', mapping: 'LpuUnit_Name' },
			{ name: 'LpuUnit_IsEnabled', mapping: 'LpuUnit_IsEnabled' }
		]),
		proxy: {
			type: 'ajax',
			url: '/?c=Common&m=loadLpuUnitList',
			reader: {
				type: 'json'
			}
		}
	});
}

function doLoadMedStaffFactGlobalStore(iteration) {
	if ( !iteration )
		var iteration = 1;

	swMedStaffFactGlobalStore.load({
		callback: function(r, o, success) {
			if ( success === false || !r || r.length == 0 )
			{
				if ( iteration >= 3 ) {
					setPromedInfo('Справочник врачей не загружен или пуст', 'medpersonal-info');
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

///**
// * Загрузка списка врачей в область глобальной видимости
// */
function loadMedStaffFactGlobalStore() {
	swMedStaffFactGlobalStore = Ext.create('Ext.data.Store',{
		autoLoad: false,
		reader: Ext.create('Ext.data.JsonReader',{
			id: 'MedStaffFact_id'
		}, [
			{ name: 'MedPersonal_DloCode', mapping: 'MedPersonal_DloCode' },
			{ name: 'MedPersonal_Fio', mapping: 'MedPersonal_Fio' },
			{ name: 'MedPersonal_id', mapping: 'MedPersonal_id' },
			{ name: 'MedPersonal_TabCode', mapping: 'MedPersonal_TabCode' },
			{ name: 'MedStaffFact_id', mapping: 'MedStaffFact_id' },
			{ name: 'Lpu_id', mapping: 'Lpu_id' },
			{ name: 'LpuBuilding_id', mapping: 'LpuBuilding_id' },
			{ name: 'LpuUnit_id', mapping: 'LpuUnit_id' },
			{ name: 'LpuSection_id', mapping: 'LpuSection_id' },
			{ name: 'LpuSection_Name', mapping: 'LpuSection_Name' },
			{ name: 'LpuSectionProfile_Code', mapping: 'LpuSectionProfile_Code' },
			{ name: 'LpuSectionProfile_SysNick', mapping: 'LpuSectionProfile_SysNick' },
			{ name: 'LpuUnitType_Code', mapping: 'LpuUnitType_Code' },
			{ name: 'LpuUnitType_SysNick', mapping: 'LpuUnitType_SysNick' },
			{ name: 'LpuSection_disDate', mapping: 'LpuSection_disDate' },
			{ name: 'LpuSection_setDate', mapping: 'LpuSection_setDate' },
			{ name: 'WorkData_begDate', mapping: 'WorkData_begDate' },
			{ name: 'WorkData_endDate', mapping: 'WorkData_endDate' },
			{ name: 'WorkData_dloBegDate', mapping: 'WorkData_dloBegDate' },
			{ name: 'WorkData_dloEndDate', mapping: 'WorkData_dloEndDate' },
			{ name: 'PostKind_id', mapping: 'PostKind_id' },
			{ name: 'PostMed_Code', mapping: 'PostMed_Code' }
		]),
		proxy: {
			type: 'ajax',
			url: C_MEDPERSONAL_LIST,
			reader: {
				type: 'json'
			}
		}
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
					sw.swMsg.alert('Ошибка','Невозможно загрузить глобальный справочник служб!');
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
					elem.fadeIn({ endOpacity: .80, duration: 2});
					//elem.highlight('9999ff');
				}
			});
		} else {
			elem.fadeIn({ endOpacity: .80, duration: 2});
			//el.highlight('9999ff');
		}
	}
}
/**
 * Загрузка списка служб в область глобальной видимости
 */
function loadMedServiceGlobalStore() {
	swMedServiceGlobalStore = Ext.create('Ext.data.Store',{
		autoLoad: false,
		reader: Ext.create('Ext.data.JsonReader',{
			id: 'MedService_id'
		}, [
			{ name: 'MedService_id', mapping: 'MedService_id' },
			{ name: 'Lpu_id', mapping: 'Lpu_id' },
			{ name: 'LpuBuilding_id', mapping: 'LpuBuilding_id' },
			{ name: 'LpuSection_id', mapping: 'LpuSection_id' },
			{ name: 'LpuUnit_id', mapping: 'LpuUnit_id' },
			{ name: 'LpuUnitType_id', mapping: 'LpuUnitType_id' },
			{ name: 'MedService_begDT', mapping: 'MedService_begDT' },
			{ name: 'MedService_endDT', mapping: 'MedService_endDT' },
			{ name: 'MedService_Name', mapping: 'MedService_Name' },
			{ name: 'MedService_Nick', mapping: 'MedService_Nick' },
			{ name: 'MedServiceType_id', mapping: 'MedServiceType_id' }
		]),
		proxy: {
			type: 'ajax',
			url: C_MEDSERVICE_LIST,
			reader: {
				type: 'json'
			}
		}
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
	var result = false;
	if (typeof group == 'string') {
		return (getGlobalOptions().groups.toString().toLowerCase().indexOf(group) != -1);
	}
	if (Ext.isArray(group)) {
		for (i=0; i < group.length; i++) {
			if (getGlobalOptions().groups.toString().toLowerCase().indexOf(group[i]) != -1) {
				result = true;
			}
		}
	}
	return result;
}

/**
 * Проверка, является ли текущий пользователь суперадминистратором
 */
function isSuperAdmin() {
	return getGlobalOptions().groups && getGlobalOptions().groups.toString().indexOf('SuperAdmin') != -1 && getGlobalOptions().superadmin;
}
//
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
 * Проверка, является ли текущий пользователь администратором ЛПУ.
 */
function isLpuAdmin() {
	return isOrgAdmin() && getGlobalOptions().orgtype && getGlobalOptions().orgtype == 'lpu'; // return getGlobalOptions().groups && getGlobalOptions().groups.toString().indexOf('LpuAdmin') != -1;
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


/**
 * Возвращает опции отображения
 */
function getAppearanceOptions() {
	return Ext.globalOptions.appearance;
}

/**
 * Возвращает суперглобальные опции
 */
function getGlobalOptions() {
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
 * Возвращает настройки специфики
 */
function getSpecificsOptions() {
	return Ext.globalOptions.specifics;
}


/**
 * Возвращает настройки услуг
 */
function getUslugaOptions() {
	return Ext.globalOptions.usluga;
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
			Ext.Msg.alert('Ошибка', 'В настройках пользователя отключено использование локальных справочников ,<br/>'+
				'но на сервере не настроена БД для серверных "локальных" справочников!<br/>'+
				'Пожалуйста, сообщение о проблеме в технический отдел.');
		}
		if (isSuperAdmin())
			setPromedInfo('На сервере не настроена БД для "локальных" справочников <br/>или отсутствует библиотека MongoDB', 'remote-info');
	}
	return !Ext.globalOptions.others.enable_localdb;
}

/**
 * Закрывает все окна
 * TODO: Скорее всего их надо не просто скрывать, а дестроить
 */
function closeWindowsAll() {
	//console.log('closeAll')
	w = Ext.ComponentQuery.query('window')
	w.forEach(function(element, index, array){
		if (!element.isHidden())
		{
			//element.hide(element)
			//element.close()
		}
	})
	//Ext.WindowManager.each(function(wnd){
//	viewPort = Ext.ComponentQuery.query('viewport')
//	viewPort.destroy()
//		wnd = Ext.getCmp('mainForm')
//		console.log('!!!', wnd)
//		if ( wnd.isVisible() ) {
//			wnd.hide();
//		}
	//});
}
/**
 * Устанавливает текущее ЛПУ
 */
function setCurrentLpu(data) {

	closeWindowsAll();
	loadLpuBuildingGlobalStore();
	loadLpuSectionGlobalStore();
	loadLpuSectionWardGlobalStore();
	loadLpuUnitGlobalStore();
	loadMedStaffFactGlobalStore();
	getCountNewDemand();
	getGlobalOptions().lpu_nick = data.Lpu_Nick;
	if ( data.Lpu_IsDMS == 2 )
		getGlobalOptions().lpu_is_dms = true;
	else
		getGlobalOptions().lpu_is_dms = false;
	getGlobalOptions().lpu_name = data.Lpu_Name;
	getGlobalOptions().lpu_id = data.Lpu_id;
	getGlobalOptions().lpu_sysnick = data.Lpu_SysNick;
	getGlobalOptions().lpu_email = data.Lpu_Email;
	getGlobalOptions().lpu_level_id = data.LpuLevel_id;
	getGlobalOptions().lpu_level_code = data.LpuLevel_Code;
	getGlobalOptions().pmuser_id = data.pmuser_id;
	getGlobalOptions().medstafffact = data.medstafffact;
	//Обновляем данные пользователя в меню
	user_menu.items.items[0].setText('<b>Информация о пользователе</b><br/>'+'Имя : '+UserName+'<br/>'+'E-mail : '+UserEmail+'<br/>'+'Описание : '+UserDescr+'<br/>'+'ЛПУ : '+getGlobalOptions().lpu_nick);
	if ( window['swLpuStructureViewForm'] ) {
		swLpuStructureViewForm.close();
		window['swLpuStructureViewForm'] = null;
	}
	// Открытие АРМа по умолчанию
	 sw.Promed.MedStaffFactByUser.openDefaultWorkPlace();
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
					Ext.Msg.alert('Ошибка', 'Ошибка при загрузке справочника: '+name);
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
		var loadMask = new Ext.LoadMask(Ext.get(params.windowId), { msg: "Получение текущих даты и времени" });
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
				var response_obj = Ext.JSON.decode(response.responseText);

				var date;
                if (params.dateField.format == 'd.m.Y H:i') {
                    date = Date.parseDate(response_obj.Date + ' ' + response_obj.Time, params.dateField.format);
                } else {
                    date = Date.parseDate(response_obj.Date, 'd.m.Y');
                }

				var time = response_obj.Time;

				if ( params.setTime && params.timeField && typeof params.timeField == 'object' && params.timeField.setRawValue ) {
					params.timeField.setRawValue(time);
				}

				if ( params.setDate && !params.dateField.getValue() ) {
					params.dateField.setValue(date);
				}

				if ( params.setDateMaxValue ) {
					params.dateField.setMaxValue(date.add(Date.DAY, params.addMaxDateDays));
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
				sw.swMsg.alert('Ошибка', 'Ошибка при определении текущей даты и времени', function() { params.dateField.focus(true); } );

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
				var response_obj = Ext.JSON.decode(response.responseText);
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


/**
 * Установить фильтр на swLpuSectionGlobalStore
 * options - параметры фильтрации
 */
function setLpuSectionGlobalStoreFilter(options) {
	swLpuSectionGlobalStore.clearFilter();

	if ( typeof options != 'object' ) {
		options = new Object();
	}

	if ( !options.regionCode ) {
		options.regionCode = 59;
	}

	swLpuSectionGlobalStore.filterBy(function(record) {
		//TODO: (LpuSectionProfile_id == 75) надо переделать на (LpuSectionProfile_SysNick == 'priem')
		var filter_flag = true;
		// Фильтруем по датам
		if ( options.onDate ) {
			var cur_date = Date.parseDate(options.onDate, 'd.m.Y');
			var dis_date = Date.parseDate(record.get('LpuSection_disDate'), 'd.m.Y');
			var set_date = Date.parseDate(record.get('LpuSection_setDate'), 'd.m.Y');

			if ( !Ext.isEmpty(cur_date) ) {
				if ( (!Ext.isEmpty(set_date) && set_date > cur_date) || (!Ext.isEmpty(dis_date) && dis_date < cur_date) ) {
					filter_flag = false;
				}
			}
		}

		// Фильтруем отделения ЛЛО
		if ( options.isDlo ) {
			if ( Ext.isEmpty(record.get('LpuSectionProfile_SysNick')) || record.get('LpuSectionProfile_SysNick') == 'priem' || (!record.get('LpuUnitType_SysNick').inlist([ 'polka', 'fap' ])) ) {
				filter_flag = false;
			}
		}

		// Фильтруем поликлинические отделения
		if ( options.isPolka ) {
			if ( Ext.isEmpty(record.get('LpuSectionProfile_SysNick')) || record.get('LpuSectionProfile_SysNick') == 'priem' || !record.get('LpuUnitType_SysNick').inlist([ 'polka', 'ccenter', 'traumcenter', 'fap' ]) ) {
				filter_flag = false;
			}
			// Убираем стоматологические отделения
			else {
				switch ( options.regionCode.toString() ) {
					case '2':
						// УФА
						if ( record.get('LpuSectionProfile_Code').toString().inlist([ '526', '562', '626', '662', '527', '528', '529', '530', '559', '560', '561', '627', '628', '629', '630', '659', '660', '661', '827', '828', '829', '830', '859', '860', '861' ]) ) {
							filter_flag = false;
						}
					break;

					case '59':
						// ПЕРМЬ
						if (
							record.get('LpuSectionProfile_Code').toString().length >= 2
							&& (record.get('LpuSectionProfile_Code').toString().substr(0, 2) == '18' || record.get('LpuSectionProfile_Code').toString().inlist([ '7181', '7182' ]))
						) {
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
				switch ( options.regionCode.toString() ) {
					case '2':
						// Добавлены дополнительные коды профилей
						// https://redmine.swan.perm.ru/issues/14502
						// УФА
						if ( !record.get('LpuSectionProfile_Code').toString().inlist([ '526', '562', '626', '662', '527', '528', '529', '530', '559', '560', '561', '627', '628', '629', '630', '659', '660', '661', '827', '828', '829', '830', '859', '860', '861' ]) ) {
							filter_flag = false;
						}
					break;

					case '59':
						// ПЕРМЬ
						if (
							record.get('LpuSectionProfile_Code').toString().length < 2
							|| (record.get('LpuSectionProfile_Code').toString().substr(0, 2) != '18' && !record.get('LpuSectionProfile_Code').toString().inlist([ '7181', '7182' ]))
						) {
							filter_flag = false;
						}
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
		 *	Фильтруем отделения для заполнения протоколов патоморфогистологических исследований
		 *	Поликлиника, стационар, параклиника, ФАП, энисинг элс?
		 */
		if ( options.isHisto ) {
			if (
				Ext.isEmpty(record.get('LpuSectionProfile_SysNick')) || record.get('LpuSectionProfile_SysNick') == 'priem'
				|| !(record.get('LpuUnitType_SysNick').toString().inlist([ 'stac', 'polka', 'dstac', 'hstac', 'pstac', 'parka', 'ccenter', 'traumcenter', 'smp' ]))
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
			if ( ((Ext.isEmpty(record.get('LpuSectionProfile_SysNick')) || record.get('LpuSectionProfile_SysNick').toString() != 'priem') && !(getGlobalOptions().region && getGlobalOptions().region.nick == 'kareliya')) || Ext.isEmpty(record.get('LpuUnitType_SysNick')) || !record.get('LpuUnitType_SysNick').toString().inlist([ 'stac', 'dstac', 'hstac', 'pstac' ]) ) {
				filter_flag = false;
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

		// Фильтруем отделения стационара
		if ( options.isStac ) {
			if ( !(record.get('LpuUnitType_SysNick').inlist([ 'stac', 'dstac', 'hstac', 'pstac' ])) ) {
				filter_flag = false;
			}
		}

		// Фильтруем отделения стационара и поликлиники
		if ( options.isStacAndPolka ) {
			if ( !(record.get('LpuUnitType_SysNick').inlist([ 'polka', 'stac', 'dstac', 'hstac', 'pstac', 'ccenter', 'traumcenter', 'fap' ])) ) {
				filter_flag = false;
			}
			// Если поликлиника, то убираем стоматологические отделения
			else if ( record.get('LpuUnitType_SysNick').inlist([ 'polka', 'ccenter', 'traumcenter', 'fap' ]) ) {
				if ( record.get('LpuSectionProfile_SysNick') == 'priem' ) {
					filter_flag = false;
				}
				else {
					switch ( options.regionCode.toString() ) {
						case '2':
							// УФА
							if ( record.get('LpuSectionProfile_Code').toString().inlist([ '526', '562', '626', '662', '527', '528', '529', '530', '559', '560', '561', '627', '628', '629', '630', '659', '660', '661', '827', '828', '829', '830', '859', '860', '861' ]) ) {
								filter_flag = false;
							}
						break;

						case '59':
							// ПЕРМЬ
							if (
								record.get('LpuSectionProfile_Code').toString().length >= 2
								&& (record.get('LpuSectionProfile_Code').toString().substr(0, 2) == '18' || record.get('LpuSectionProfile_Code').toString().inlist([ '7181', '7182' ]))
							) {
								filter_flag = false;
							}
						break;
					}
				}
			}
		}

		// Фильтруем по сторонним специалистам
		if ( options.isAliens ) {
			if ( record.get('Lpu_id') == getGlobalOptions().lpu_id ) {
				filter_flag = false;
			}
		}
		else {
			if ( record.get('Lpu_id') != getGlobalOptions().lpu_id ) {
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

		// Фильтруем по группе отделений
		if ( !Ext.isEmpty(options.LpuUnit_id) ) {
			if ( record.get('LpuUnit_id') != options.LpuUnit_id ) {
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
 * Установить фильтр на swLpuSectionWardGlobalStore
 * options - параметры фильтрации
 */
function setLpuSectionWardGlobalStoreFilter(options) {
	swLpuSectionWardGlobalStore.clearFilter();

	if ( typeof options != 'object' ) {
		options = new Object();
	}

	/*if ( !options.regionCode ) {
		options.regionCode = 59;
	}*/

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
			Ext.Msg.alert('Ошибка', 'Произошла ошибка при проверке заявок на прикрепление.');
		},
		success: function(response, options) {
			var response_obj = Ext.util.JSON.decode(response.responseText);
			if (response_obj['cnt'] > 0) Ext.Msg.alert('Новые заявки', 'Доступно новых заявлений на прикрепление: ' + response_obj['cnt']);
		},
		url: '/?c=Demand&m=getCountNewDemand'
	});*/
}

/**
 * Установить фильтр на swMedStaffFactGlobalStore
 * options - параметры фильтрации
 */
function setMedStaffFactGlobalStoreFilter(options) {
	swMedStaffFactGlobalStore.clearFilter();
	if ( typeof options != 'object' ) {
		options = new Object();
	}

	if ( !options.regionCode ) {
		options.regionCode = (getGlobalOptions().region && getGlobalOptions().region.number)?getGlobalOptions().region.number:59;
	}

	// Массив для фильтрации мест работы врача в одном отделении
	var distinctMPList = new Array();

	swMedStaffFactGlobalStore.filterBy(function(record) {
		var filter_flag = true;

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
					filter_flag = false;
				}
			}
		}
		else {
			if ( !Ext.isEmpty(options.dateFrom) ) {
				var dateFrom = Date.parseDate(options.dateFrom, 'd.m.Y');
				var mp_end_date = Date.parseDate(record.get('WorkData_endDate'), 'd.m.Y');

				if ( !Ext.isEmpty(dateFrom) && !Ext.isEmpty(mp_end_date) && mp_end_date < dateFrom ) {
					filter_flag = false;
				}
			}

			if ( !Ext.isEmpty(options.dateTo) ) {
				var dateTo = Date.parseDate(options.dateTo, 'd.m.Y');
				var mp_beg_date = Date.parseDate(record.get('WorkData_begDate'), 'd.m.Y');

				if ( !Ext.isEmpty(dateTo) && !Ext.isEmpty(mp_beg_date) && mp_beg_date > dateTo ) {
					filter_flag = false;
				}
			}
		}

		// Фильтруем по датам окончания работы
		if ( !Ext.isEmpty(options.onEndDate) ) {
			var onEndDate = Date.parseDate(options.onEndDate, 'd.m.Y');
			var mp_end_date = Date.parseDate(record.get('WorkData_endDate'), 'd.m.Y');

			if ( !Ext.isEmpty(onEndDate) ) {
				if ( !Ext.isEmpty(mp_end_date) && mp_end_date < onEndDate ) {
					filter_flag = false;
				}
			}
		}

		// Фильтруем врачей ЛЛО
		if ( options.isDlo ) {
			if (
				(
					(record.get('MedPersonal_DloCode').toString().length == 0 && options.regionCode.toString() != '2') ||
					(record.get('MedPersonal_TabCode').toString().length == 0 && options.regionCode.toString() == '2')
				)
				|| record.get('LpuSectionProfile_SysNick') == 'priem'
				|| !record.get('LpuUnitType_SysNick').toString().inlist([ 'polka', 'fap' ])
			) {
				filter_flag = false;
			}

			if ( !Ext.isEmpty(options.onDate) && options.regionCode.toString() != '2' && options.regionCode.toString() != '64' ) {
				var dloBegDate = Date.parseDate(record.get('WorkData_dloBegDate'), 'd.m.Y');
				var dloEndDate = Date.parseDate(record.get('WorkData_dloEndDate'), 'd.m.Y');
				var dloOnDate = Date.parseDate(options.onDate, 'd.m.Y');

				if ( !Ext.isEmpty(dloOnDate) ) {
					if (
						typeof dloBegDate != 'object'
						|| dloBegDate > dloOnDate
						|| (dloEndDate && dloEndDate < dloOnDate)
					) {
						filter_flag = false;
					}
				}
			}
		}

		// Только врачи
		if ( options.isDoctor && Ext.globalOptions.polka.enable_is_doctor_filter && Number(Ext.globalOptions.polka.enable_is_doctor_filter) === 1) {
			if ( record.get('PostKind_id') != 1 ) {
				filter_flag = false;
			}
		}

		// Фильтруем врачей поликлинических отделений
		if ( options.isPolka ) {
			if ( record.get('LpuSectionProfile_SysNick') == 'priem' || !record.get('LpuUnitType_SysNick').toString().inlist([ 'polka', 'ccenter', 'traumcenter', 'fap' ]) ) {
				filter_flag = false;
			}
			else {
				switch ( options.regionCode.toString() ) {
					case '2':
						// УФА
						if ( record.get('LpuSectionProfile_Code').toString().inlist([ '526', '562', '626', '662', '527', '528', '529', '530', '559', '560', '561', '627', '628', '629', '630', '659', '660', '661', '827', '828', '829', '830', '859', '860', '861' ]) ) {
							filter_flag = false;
						}
					break;

					case '59':
						// ПЕРМЬ
						if (
							record.get('LpuSectionProfile_Code').toString().length >= 2
							&& (record.get('LpuSectionProfile_Code').toString().substr(0, 2) == '18' || record.get('LpuSectionProfile_Code').toString().inlist([ '7181', '7182' ]))
						) {
							filter_flag = false;
						}
					break;
				}
			}
		}

		// Фильтруем врачей не поликлинических отделений
		if ( options.isNotPolka ) {
			if ( record.get('LpuUnitType_SysNick').toString().inlist([ 'polka', 'ccenter', 'traumcenter', 'fap' ]) ) {
				filter_flag = false;
			}
		}

		// Фильтруем врачей приемных отделений поликлиники
		if ( options.isPolkaReception ) {
			if ( record.get('LpuSectionProfile_SysNick') != 'priem' || record.get('LpuUnitType_SysNick') != 'polka' ) {
				filter_flag = false;
			}
		}

		// Фильтруем врачей стоматологических отделений
		if ( options.isStom ) {
			if ( record.get('LpuSectionProfile_SysNick') == 'priem' || !record.get('LpuUnitType_SysNick').toString().inlist([ 'polka', 'ccenter', 'traumcenter', 'fap' ]) ) {
				filter_flag = false;
			}
			else {
				switch ( options.regionCode.toString() ) {
					case '2':
						// Добавлены дополнительные коды профилей
						// https://redmine.swan.perm.ru/issues/14502
						// УФА
						if ( !record.get('LpuSectionProfile_Code').toString().inlist([ '526', '562', '626', '662', '527', '528', '529', '530', '559', '560', '561', '627', '628', '629', '630', '659', '660', '661', '827', '828', '829', '830', '859', '860', '861' ]) ) {
							filter_flag = false;
						}
					break;

					case '59':
						// ПЕРМЬ
						if (
							record.get('LpuSectionProfile_Code').toString().length < 2
							|| (record.get('LpuSectionProfile_Code').toString().substr(0, 2) != '18' && !record.get('LpuSectionProfile_Code').toString().inlist([ '7181', '7182' ]))
						) {
							filter_flag = false;
						}
					break;
				}
			}
		}

		// Фильтруем врачей приемных отделений стационара
		if ( options.isStacReception ) {
			if ( (record.get('LpuSectionProfile_SysNick') != 'priem' && !(getGlobalOptions().region && getGlobalOptions().region.nick == 'kareliya')) || record.get('LpuUnitType_SysNick') != 'stac' ) {
				filter_flag = false;
			}
		}

		// Фильтруем врачей приемных отделений параклиники
		if ( options.isParkaReception ) {
			if ( record.get('LpuSectionProfile_SysNick') != 'priem' || record.get('LpuUnitType_SysNick') != 'parka' ) {
				filter_flag = false;
			}
		}

		/**
		 *	Фильтруем врачей для заполнения протоколов патоморфогистологических исследований
		 *	Поликлиника, стационар, параклиника, ФАП, энисинг элс?
		 */
		if ( options.isHisto ) {
			if (
				record.get('LpuSectionProfile_SysNick') == 'priem'
				|| !(record.get('LpuUnitType_SysNick').inlist([ 'polka', 'stac', 'dstac', 'hstac', 'pstac', 'parka', 'ccenter', 'traumcenter', 'fap' ]))
			) {
				filter_flag = false;
			}
		}

		// Фильтруем по коду профиля отделения
		if ( options.lpuSectionProfileCode ) {
			if ( record.get('LpuSectionProfile_Code').toString().length < options.lpuSectionProfileCode.toString().length || record.get('LpuSectionProfile_Code').toString().substr(0, options.lpuSectionProfileCode.toString().length) != options.lpuSectionProfileCode.toString() ) {
				filter_flag = false;
			}
		}

		// Фильтруем по списку кодов профилей отделений
		if ( options.arrayLpuSectionProfile ) {
			if ( !(record.get('LpuSectionProfile_Code').inlist(options.arrayLpuSectionProfile)) ) {
				filter_flag = false;
			}
		}

		// Фильтруем по списку кодов типов подразделений
		if ( options.arrayLpuUnitType ) {
			if ( !(record.get('LpuUnitType_Code').inlist(options.arrayLpuUnitType)) ) {
				filter_flag = false;
			}
		}

		// Фильтруем врачей, работающих в стационаре
		if ( options.isStac ) {
			if ( !(record.get('LpuUnitType_SysNick').inlist([ 'stac', 'dstac', 'hstac', 'pstac' ])) ) {
				filter_flag = false;
			}
		}

		// Фильтруем врачей, работающих в стационаре или в поликлинике
		if ( options.isStacAndPolka ) {
			if ( !(record.get('LpuUnitType_SysNick').inlist([ 'polka', 'stac', 'dstac', 'hstac', 'pstac', 'parka', 'ccenter', 'traumcenter', 'fap' ])) ) {
				filter_flag = false;
			}
			// Если поликлиника, то убираем приемные и стоматологические отделения
			else if ( record.get('LpuUnitType_SysNick').inlist([ 'polka', 'ccenter', 'traumcenter', 'fap' ]) ) {
				if ( record.get('LpuSectionProfile_SysNick') == 'priem' ) {
					filter_flag = false;
				}
				else {
					switch ( options.regionCode.toString() ) {
						case '2':
							// УФА
							if ( record.get('LpuSectionProfile_Code').toString().inlist([ '526', '562', '626', '662', '527', '528', '529', '530', '559', '560', '561', '627', '628', '629', '630', '659', '660', '661', '827', '828', '829', '830', '859', '860', '861' ]) ) {
								filter_flag = false;
							}
						break;

						case '59':
							// ПЕРМЬ
							if (
								record.get('LpuSectionProfile_Code').toString().length >= 2
								&& (record.get('LpuSectionProfile_Code').toString().substr(0, 2) == '18' || record.get('LpuSectionProfile_Code').toString().inlist([ '7181', '7182' ]))
							) {
								filter_flag = false;
							}
						break;
					}
				}
			}
		}

		// Фильтруем места работы по LpuSection_id (переданному значению)
		if ( options.LpuSection_id ) {
			if ( record.get('LpuSection_id') != options.LpuSection_id ) {
				filter_flag = false;
			}
		}

		// Фильтруем по сторонним специалистам
		if ( options.isAliens ) {
			if ( record.get('Lpu_id') == getGlobalOptions().lpu_id ) {
				filter_flag = false;
			}
		}
		else if ( record.get('Lpu_id') != getGlobalOptions().lpu_id ) {
			filter_flag = false;
		}

		// Фильтруем места работы по ids
		if ( options.ids && Ext.isArray(options.ids) ) {
			if ( !options.ids.in_array(record.get('MedStaffFact_id')) ) {
				filter_flag = false;
			}
		}

		// Фильтруем места работы по id
		if ( options.id && !options.ids ) {
			if ( record.get('MedStaffFact_id') != options.id ) {
				filter_flag = false;
			}
		}

		// Фильтруем места работы по списку MedPersonal_id
		if ( options.medPersonalIdList && Ext.isArray(options.medPersonalIdList) ) {
			if ( !options.medPersonalIdList.in_array(record.get('MedPersonal_id')) ) {
				filter_flag = false;
			}
		}

		// Фильтруем по подразделению
		if ( !Ext.isEmpty(options.LpuBuilding_id) ) {
			if ( record.get('LpuBuilding_id') != options.LpuBuilding_id ) {
				filter_flag = false;
			}
		}

		// Фильтруем по группе отделений
		if ( !Ext.isEmpty(options.LpuUnit_id) ) {
			if ( record.get('LpuUnit_id') != options.LpuUnit_id ) {
				filter_flag = false;
			}
		}

		if ( filter_flag == true ) {
			if ( Ext.isEmpty(distinctMPList[record.get('MedPersonal_id')]) ) {
				distinctMPList[record.get('MedPersonal_id')] = new Array();
			}

			if ( !record.get('LpuSection_id').toString().inlist(distinctMPList[record.get('MedPersonal_id')]) ) {
				distinctMPList[record.get('MedPersonal_id')].push(record.get('LpuSection_id').toString());
			}
			else {
				filter_flag = false;
			}
		}

		return filter_flag;
	});

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
		var filter_flag = true;

		// Фильтруем по датам
		if ( options.onDate ) {
			var onDate = Date.parseDate(options.onDate, 'd.m.Y');
			var ms_beg_date = Date.parseDate(record.get('MedService_begDT'), 'd.m.Y');
			var ms_end_date = Date.parseDate(record.get('MedService_endDT'), 'd.m.Y');

			if ( onDate ) {
				if ( (ms_beg_date && ms_beg_date > onDate) || (ms_end_date && ms_end_date < onDate ) ) {
					filter_flag = false;
				}
			}
		}
		else {
			if ( options.dateFrom ) {
				var dateFrom = Date.parseDate(options.dateFrom, 'd.m.Y');
				var ms_end_date = Date.parseDate(record.get('MedService_endDT'), 'd.m.Y');

				if ( dateFrom && ms_end_date && ms_end_date < dateFrom ) {
					filter_flag = false;
				}
			}

			if ( options.dateTo ) {
				var dateTo = Date.parseDate(options.dateTo, 'd.m.Y');
				var ms_beg_date = Date.parseDate(record.get('MedService_begDT'), 'd.m.Y');

				if ( dateTo && ms_beg_date && ms_beg_date > dateTo ) {
					filter_flag = false;
				}
			}
		}

		// Фильтруем службы по ЛПУ
		if ( options.Lpu_id ) {
			if ( record.get('Lpu_id') != options.Lpu_id ) {
				filter_flag = false;
			}
		}

		// Фильтруем службы по группе отделений
		if ( options.LpuBuilding_id ) {
			if ( record.get('LpuBuilding_id') != options.LpuBuilding_id ) {
				filter_flag = false;
			}
		}

		// Фильтруем службы по подразделению
		if ( options.LpuUnit_id ) {
			if ( record.get('LpuUnit_id') != options.LpuUnit_id ) {
				filter_flag = false;
			}
		}

		// Фильтруем службы по отделению
		if ( options.LpuUnitType_id ) {
			if ( record.get('LpuUnitType_id') != options.LpuUnitType_id ) {
				filter_flag = false;
			}
		}

		// Фильтруем службы по типу
		if ( options.MedServiceType_id ) {
			if ( record.get('MedServiceType_id') != options.MedServiceType_id ) {
				filter_flag = false;
			}
		}

		return filter_flag;
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
		if (mode != 'clear') Ext.Msg.alert('Ошибка', 'Дата не может быть больше даты смерти человека.');
		combo.setValue(oldvalue);
		if (mode == 'clear') combo.startValue = null;
		return true;
	}
	return false;
}

function getMedPersonalListFromGlobal() {
	var result = new Array();
	var med_personal_array = new Array();

	swMedStaffFactGlobalStore.each(function(record) {
		var add = true;

		if ( !record.get('MedPersonal_id').toString().inlist(med_personal_array) ) {
			med_personal_array.push(record.get('MedPersonal_id'));

			result.push({
				'MedPersonal_Fio': record.get('MedPersonal_Fio'),
				'MedPersonal_id': record.get('MedPersonal_id'),
				'MedPersonal_TabCode': record.get('MedPersonal_TabCode')
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
		url: '/?c=promed&m=getJSFile',
		params: {wnd:wnd.objectClass},
		callback: function(options, success, response) {
			if (success) {
				// Читаем и пересоздаем (добавляем в DOM)
				if (response.responseText) {
					var result  = {success: false};
					try {
						var result  = Ext.JSON.decode(response.responseText);
						if ( result.success ) {
							var responseText = result.data;
							if (result.roles) {
								sw.roles[wnd.objectClass] = result.roles;
							}
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
		if (arguments.length == 1) {
			console.log(obj);
		} else {
			console.log(arguments);
		}
	}
}

/** Вывод в лог с условиями (включененный режим отладки и наличие консоли)
 *  @obj - объект или текст.
 */
function warn() {
	if (isDebug())
		console.warn(arguments);
}

Date.prototype.lastday = function() {
	var d = new Date(this.getFullYear(), this.getMonth() + 1, 0);
	return d.getDate();
};

Date.prototype.getMonthsBetween = function(d) {
	var sDate, eDate;
	var d1 = this.getFullYear() * 12 + this.getMonth();
	var d2 = d.getFullYear() * 12 + d.getMonth();
	var sign;
	var months = 0;

	if (this == d) {
		months = 0;
	} else if (d1 == d2) { //тот же год и месяц
		months = (d.getDate() - this.getDate()) / this.lastday();
	} else {
		if (d1 <  d2) {
			sDate = this;
			eDate = d;
			sign = 1;
		} else {
			sDate = d;
			eDate = this;
			sign = -1;
		}

		var sAdj = sDate.lastday() - sDate.getDate();
		var eAdj = eDate.getDate();
		var adj = (sAdj + eAdj) / sDate.lastday() - 1;
		months = Math.abs(d2 - d1) + adj;
		months = (months * sign)
	}
	return months;
};

/** Вычисление возраста человека на дату
 *  @birth_date - дата рождения
 *  @date - дата, на которую вычисляется возраст
 */
function swGetPersonAge(birth_date, date) {
	if ( !birth_date || typeof birth_date != 'object' ) {
		return -1;
	}

	if ( !date || typeof date != 'object' ) {
		date = new Date();
	}

	if ( birth_date > date )  {
		return -1;
	}

	// Тру-метод
	var person_age = (birth_date.getMonthsBetween(date) - (birth_date.getMonthsBetween(date) % 12)) / 12;
/*
	// Олдскул-быдло-метод
	var person_age = date.getFullYear() - birth_date.getFullYear();

	if ( date.getMonth() > birth_date.getMonth() ) {
		person_age = person_age - 1;
	}
	else if ( date.getMonth() == birth_date.getMonth() && date.getDate() > birth_date.getDate() ) {
		person_age = person_age - 1;
	}
*/
	return person_age;
}

/** Изменение количества непрочитанных сообщений в панели сообщений
 *
 */
//function changeCountMessages(count) {
//	if (getAppearanceOptions().is_popup_message) {
//		main_messages_panel.setVisible((count>0));
//		main_messages_panel.unreadCount = count;
//		var tc = count.toString(), l = tc.length;
//
//		var ok = ((tc.substring(l-1,1)=='1')?'ие':((tc.substring(l-1,1).inlist(['2','3','4']))?'ия': 'ий'));
//		var okch = ((tc.substring(l-1,1)=='1')?'ое':'ых');
//		main_messages_tpl.overwrite(main_messages_panel.body, {count: count, okch: okch, ok: ok});
//	} else {
//		main_messages_panel.setVisible(false);
//	}
//}
/**
 * Выводит сообщение системы
 */
//function showSysMsg(msg, title, type) {
//	type = (type)?type:'info';
//	title = (title)?title:'Сообщение';
//	if (title!='') {
//		title = '<b>'+title+'</b><br/>';
//	}
//	var text = '<div class="popup-msg icon '+type+'"><div class="icon"></div><div class="text"><!--i>Система:</i><br/-->'+title+msg+'</div></div>'
//	var NewNoticeWindow = new Ext.ux.window.MessageWindow({
//		title: '',
//		autoHeight: true,
//		help: false,
//		closable: false,
//		constrainHeader: false,
//		frame: false,
//		header: false,
//		pinOnClick: false,
//		constrain: false,
//		shadow: false,
//		bodyStyle: 'text-align:left;padding:20px;background:transparent',
//		buttonAlign: 'left',
//		//headerAsText: false,
//		hideBorders: true,
//		animateTarget: Ext.getCmp('main-messages-panel').getEl(),
//		//style: 'opacity: 0;',
//		tools:[],
//		hideFx: {
//			delay: 5000,
//			mode: 'custom',
//			callback: function(cb,scope,args,delay) {
//				this.proxy.setOpacity(.5);
//				this.proxy.show();
//				var tb = this.getBox(false);
//				this.proxy.setBox(tb);
//				this.el.hide();
//				var b = this.animateTarget.getBox();
//				b.callback = this.afterHide;
//				b.scope = this;
//				b.duration = .25;
//				b.easing = 'easeNone';
//				b.block = true;
//				b.opacity = 0;
//				this.proxy.shift(b);
//			}
//		},
//		/*show: function() {
//			this.toFront();
//		},*/
//		html: '',
//		listeners: {
//			hide: function(win) {
//				//showSysMsg('!!!');
//			}
//		},
//		width: 330
//	});
//	NewNoticeWindow.html = text;
//	NewNoticeWindow.show();
//	NewNoticeWindow.toFront();
//}

/** Вывод всплывающего сообщения из кеша
 *
 */
//function showNotice() {
//	// берем первую запись из стека
//	if (sw.notices.length>0) {
//		var NewNoticeWindow = Ext.create('Ext.ux.window.MessageWindow',{
//			//title: '',
//			autoHeight: true,
//			help: false,
//			closable: false,
//			constrainHeader: false,
//			frame: false,
//			header: false,
//			pinOnClick: false,
//			constrain: false,
//			//plain: true,
//			shadow: false,
//			/*cls: '',
//			baseCls: '',
//			bodyCls: '',
//			*/
//			bodyStyle: 'text-align:left;padding:20px;background:transparent',
//			buttonAlign: 'left',
//			//headerAsText: false,
//			hideBorders: true,
//			animateTarget: Ext.getCmp('main-messages-panel').getEl(),
//			//style: 'opacity: 0;',
//			tools:[],
//			hideFx:
//			{
//				delay: 3000,
//				mode: 'custom',
//				callback: function(cb,scope,args,delay) {
//					this.proxy.setOpacity(.5);
//					this.proxy.show();
//					var tb = this.getBox(false);
//					this.proxy.setBox(tb);
//					this.el.hide();
//					var b = this.animateTarget.getBox();
//					b.callback = this.afterHide;
//					b.scope = this;
//					b.duration = .25;
//					b.easing = 'easeNone';
//					b.block = true;
//					b.opacity = 0;
//					this.proxy.shift(b);
//				}
//			},
//			html: '',
//			listeners: {
//				hide: function(win)
//				{
//					//win.setPosition(Ext.getBody().getWidth()-win.width-20, 200);
//					changeCountMessages(main_messages_panel.unreadCount+1);
//					showNotice();
//				}
//			},
//			width: 330
//		});
//		var notice = sw.notices[0];
//		//NewNoticeWindow.setTitle(notice.PMUser_Name+':');
//		NewNoticeWindow.html = notice.msg;
//		NewNoticeWindow.show();
//		sw.notices.splice(0,1);
//
//	}
//
//}

/** Проверяем наличие новых(непрочитанных) сообщений
 *
 *
 */
//function getNewMessages() {
//	Ext.Ajax.request({
//		url: '/?c=Messages&m=getNewMessages',
//		success: function(resp, opts)
//		{
//			var obj = Ext.util.JSON.decode(resp.responseText);
//			if (obj)
//			{
//				var reclen = (obj.data && obj.data.length)?obj.data.length:0;
//				if (obj.totalCount && sw.notices.length==0) {
//					// вызов функции изменения отображения количества новых сообщений - пришедшие сообщения
//					var ct = obj.totalCount - reclen;
//					changeCountMessages(ct);
//				}
//				if (reclen > 0)
//				{
//					// Заполняем стек сообщений
//					for (i = 0; i < reclen; i++)
//					{
//						sw.notices.push(obj.data[i]);
//					}
//					showNotice();
//				}
//				else {
//					return false;
//				}
//			}
//		}
//	});
//}

/** Функция для запускалки
*
*/
//function taskRun()
//{
//	getNewMessages();
//}

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
//function getPrimType( v ) {
//	if(typeof v === 'object') {
//		return Object.prototype.toString.call(v).slice(8, -1).toLowerCase();
//    } else {
//		return typeof v;
//	}
//}

/** Функция определяет метод хранения данных справочников
 *
 */
function setDBDriver() {
	// Если используется remoteDB, то одна любая другая технология хранения тоже может быть использована
	Ext.isRemoteDB = isRemoteDB();
	/*if (isRemoteDB()) {
		Ext.isIndexedDb = false;
		Ext.isWebSqlDB = false;
		Ext.isGears = false;
	} else {
	*/
	// Выбираем определенный драйвер, на случай если браузер поддерживает несколько технологий
	if (Ext.isGears) {
		Ext.isIndexedDb = false;
		Ext.isWebSqlDB = false;
	}
	if (Ext.isIndexedDb) {
		Ext.isWebSqlDB = false;
	}
}
//
//function getListMenu(panel) {
//
//}

/** Функция подписывает документ (любой документ, не обязательно Evn)
 *
 */

function signedDocument(config) {
	var msg = 'Подписать данный документ?';
	var scope = this;
	var params = {id:null, type:'Evn'};
	var button;
	var allowQuestion = true;
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
			msg = 'Отменить подпись документа?';
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
				if (result.success) {
					switch(result.EvnClass_SysNick) {
						case 'EvnVizitPL':
						case 'EvnSection':
							switch(parseInt(result.MorbusType_id)) {
								case 2:
									if(result.PersonRegister_id) {
										checkEvnNotifyHIVPreg({
											EvnNotifyHIVPreg_pid: result.Evn_id
											,Server_id: result.Server_id
											,Person_id: result.Person_id
											,PersonEvn_id: result.PersonEvn_id
											,MedPersonal_id: getGlobalOptions().medpersonal_id
											,Diag_id: result.Diag_id
											,EvnNotifyHIVPreg_setDT: getGlobalOptions().date
											,Morbus_id: result.Morbus_id
											,MorbusType_id: result.MorbusType_id
											,EvnNotifyHIVPreg_id: result.EvnNotifyHIVPreg_id || null
											,PersonRegister_id: result.PersonRegister_id
											//,Diag_Code: result.Diag_Code
											//,Alert_Msg: result.Alert_Msg
										});
									}
									break;
								case 3:
									checkEvnOnkoNotify({
										EvnOnkoNotify_pid: result.Evn_id
										,Server_id: result.Server_id
										,Person_id: result.Person_id
										,PersonEvn_id: result.PersonEvn_id
										,MedPersonal_id: getGlobalOptions().medpersonal_id
										,Diag_id: result.Diag_id
										,EvnOnkoNotify_setDT: getGlobalOptions().date
										,Morbus_id: result.Morbus_id
										,MorbusType_id: result.MorbusType_id
										,EvnOnkoNotify_id: result.EvnOnkoNotify_id
										,EvnOnkoNotifyNeglected_id: (result.EvnOnkoNotifyNeglected_id)?result.EvnOnkoNotifyNeglected_id:null
										,PersonRegister_id: (result.PersonRegister_id)?result.PersonRegister_id:null
										,Diag_Code: result.Diag_Code
										,TumorStage_id: result.TumorStage_id
										,Alert_Msg: result.Alert_Msg
									});
									break;
								case 4:
									checkEvnNotifyCrazy({
										EvnNotifyCrazy_pid: result.Evn_id
										,Server_id: result.Server_id
										,Person_id: result.Person_id
										,PersonEvn_id: result.PersonEvn_id
										,MedPersonal_id: getGlobalOptions().medpersonal_id
										,Diag_Code: result.Diag_Code
										,Diag_Name: result.Diag_Name
										,EvnNotifyCrazy_setDT: getGlobalOptions().date
										,Morbus_id: result.Morbus_id
										,MorbusType_id: result.MorbusType_id
										,EvnNotifyCrazy_id: result.EvnNotifyCrazy_id
										,PersonRegister_id: result.PersonRegister_id
									});
									break;
								case 6:
									checkEvnNotifyOrphan({
										EvnNotifyOrphan_pid: result.Evn_id
										,Server_id: result.Server_id
										,Person_id: result.Person_id
										,PersonEvn_id: result.PersonEvn_id
										,MedPersonal_id: getGlobalOptions().medpersonal_id
										,Diag_Name: result.Diag_Name
										,EvnNotifyOrphan_setDT: getGlobalOptions().date
										,Morbus_id: result.Morbus_id
										,MorbusType_id: result.MorbusType_id
										,EvnNotifyOrphan_id: result.EvnNotifyOrphan_id
										,PersonRegister_id: result.PersonRegister_id
									});
									break;
								case 5: //Создание/проверка наличия извещения и записи регистра с логикой автоматического включения в регистр при создании извещения о вирусном гепатите
								case 7: // туберкулез, почти логика такая же как для гепатита
								case 8: // венеро, логика почти такая же как для гепатита
								case 9: // вич, логика почти такая же как для гепатита
									checkEvnNotifyBaseWithAutoIncludeInPersonRegister(result);
									break;
							}
						break;
					}
				}

				// в случае если в конфиге передан Callback (что будет в большинстве случаев), то мы его выполняем
				if (config.callback) {
					config.callback(result.success);
				}
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
				title: 'Вопрос'
			});
		} else {
			requestSignedDocument(params, button, config);
		}
	}
}

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
			this.store = Ext.create('Ext.db.AdapterStore',{
				autoLoad: false,
				dbFile: 'Promed.db',
				fields: [
					{name: 'LeaveType_Name', mapping: 'LeaveType_Name'},
					{name: 'LeaveType_Code', mapping: 'LeaveType_Code'},
					{name: 'LeaveType_id', mapping: 'LeaveType_id'}
				],
				key: 'LeaveType_id',
				sortInfo: {field: 'LeaveType_Code'},
				tableName: 'LeaveType'
			});
		}
		var menu = Ext.create('Ext.menu.Menu',{id: option.id || 'menuLeaveType'});
		var conf = {
			callback: function(){
				// Получаем список исходов в госпитализации
				this.store.each(function(record) {
					menu.add({text: record.get('LeaveType_Code')+'.'+record.get('LeaveType_Name'), LeaveType_Code: record.get('LeaveType_Code'), LeaveType_id:record.get('LeaveType_id'), iconCls: 'leave16', handler: function() {
						option.LeaveType_id = this.LeaveType_id;
						option.LeaveType_Code = this.LeaveType_Code;
						sw.Promed.Leave.select(option);
					}});
				});
				if(typeof option.onCreate == 'function') option.onCreate(menu);
			}.bind(this)
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
	 * @return {void}
	 */
	select: function(option) {
		var data = option.getParams();

		if ( parseInt(option.LeaveType_Code) != 5 &&  data.isEvnSectionLast == false ) { //data.EvnOtherSection_id > 0 &&
			sw.swMsg.alert('Сообщение', 'Это не последнее движение, возможен единственный исход - перевод в другое отделение!');
			return false;
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

		if ( getWnd('swEmkEvnSectionEditWindow').isVisible() ) {
			sw.swMsg.alert('Сообщение', 'Окно редактирования исхода госпитализации пациента уже открыто');
			return false;
		}

		getWnd('swEmkEvnSectionEditWindow').show({
			action: action,
			callback: function(ndata) {
				if ( typeof option.callbackEditWindow == 'function' ) {
					option.callbackEditWindow(ndata);
				}
			}.bind(this),
			formParams: params,
			onHide: function() {
				if ( typeof option.onHideEditWindow == 'function' ) {
					option.onHideEditWindow();
				}
			}.bind(this),
			Person_id: data.Person_id,
			Person_Birthday: data.Person_Birthday,
			Person_Firname: data.Person_Firname,
			Person_Secname: data.Person_Secname,
			Person_Surname: data.Person_Surname
		});
	},
	openEditWindow: function(option) {
		if ( getWnd('swEmkEvnSectionEditWindow').isVisible() ) {
			sw.swMsg.alert('Сообщение', 'Окно редактирования исхода госпитализации пациента уже открыто');
			return false;
		}

		var data = option.getParams();

		getWnd('swEmkEvnSectionEditWindow').show({
			action: 'edit',
			callback: function(ndata) {
				if ( typeof option.callbackEditWindow == 'function' ) {
					option.callbackEditWindow(ndata);
				}
			}.bind(this),
			formParams: {
				EvnSection_id: data.EvnSection_id
			},
			onHide: function() {
				if ( typeof option.onHideEditWindow == 'function' ) {
					option.onHideEditWindow();
				}
			}.bind(this),
			Person_id: data.Person_id,
			Person_Birthday: data.Person_Birthday,
			Person_Firname: data.Person_Firname,
			Person_Secname: data.Person_Secname,
			Person_Surname: data.Person_Surname
		});
	},
	doSign: function(options) {
		var loadMask = Ext.create('Ext.LoadMask',options.ownerWindow.getEl(), { msg: "Подписание исхода из отделения..." });
		loadMask.show();

		Ext.Ajax.request({
			failure: function(response, opt) {
				loadMask.hide();
				sw.swMsg.alert('Ошибка', 'Ошибка при подписании исхода из отделения');
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
						Ext.Msg.alert('Ошибка #'+answer.Error_Code, answer.Error_Message);
					}
				}
				else {
					Ext.Msg.alert('Ошибка', 'Ошибка при подписании исхода из отделения! Отсутствует ответ сервера.');
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
			title: 'Внимание!',
			msg: 'После подписания исхода невозможно будет редактировать данные, связанные с данным движением, в том числе эпикриз соответствующий исходу из отделения. Продолжить?',
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
				sw.swMsg.alert('Ошибка', 'Ошибка при удалении исхода госпитализации в профильное отделение');
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
					Ext.Msg.alert('Ошибка', 'Ошибка при удалении исхода госпитализации в профильное отделение! Отсутствует ответ сервера.');
				}
			},
			url: '/?c=EvnSection&m=deleteLeave'
		});
		return true;
	}
};
//
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
				Ext.Msg.alert('Ошибка', 'При получении списка палат произошла ошибка!');
			},
			success: function(response, action)
			{
				if (response.responseText) {
					var result = Ext.util.JSON.decode(response.responseText);
					var menu = Ext.create('Ext.menu.Menu',{id: (option.id || 'menuLpuSectionWard')+option.LpuSection_id});
					var ico = 'tree-rooms-none16';
					menu.add({text: 'Без палаты', LpuSection_id: option.LpuSection_id, Sex_id: null, LpuSectionWard_id: '0', iconCls: ico, handler: function() {
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
							menu.add({text: result[i]['LpuSectionWard_Name'], LpuSection_id: result[i]['LpuSection_id'], Sex_id: result[i]['Sex_id'], LpuSectionWard_id:result[i]['LpuSectionWard_id'], iconCls: ico, handler: function() {
								sw.Promed.LpuSectionWard.setWard({
									LpuSectionWard_id: this.LpuSectionWard_id,
									onSuccess: option.onSuccess,
									params: option.getParams()
								});
							}});
						}
						option.callback(menu);
					}
				}
				else {
					Ext.Msg.alert('Ошибка', 'При получении списка палат произошла ошибка! Отсутствует ответ сервера.');
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
		var menu = Ext.create('Ext.menu.Menu',{id: (option.id || 'menuLpuSectionWard')+option.LpuSection_id});
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
			Ext.Ajax.request({
				url: '/?c=EvnSection&m=setEvnSectionWard',
				params: params,
				failure: function(response, options) {
					Ext.Msg.alert('Ошибка', 'При переводе в палату произошла ошибка!');
				},
				success: function(response, action)
				{
					if (response.responseText) {
						var answer = Ext.util.JSON.decode(response.responseText);
						if (answer.success) {
							if(typeof option.onSuccess == 'function')
								option.onSuccess(params);
						} else if (answer.Error_Code) {
							Ext.Msg.alert('Ошибка #'+answer.Error_Code, answer.Error_Message);
						} else if (answer.Alert_Msg) {
							sw.swMsg.show(
							{
								icon: Ext.MessageBox.QUESTION,
								msg: answer.Alert_Msg + '<br /> Продолжить?',
								title: 'Вопрос',
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
						Ext.Msg.alert('Ошибка', 'При переводе в палату произошла ошибка! Отсутствует ответ сервера.');
					}
				}
			});
		}
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
		var menu = Ext.create('Ext.menu.Menu',{id: (option.id || 'menuMedPersonal')+option.LpuSection_id});
		// Получаем список врачей по данному отделению
		setMedStaffFactGlobalStoreFilter({
			isStac: true,
			LpuSection_id: option.LpuSection_id
		});
		swMedStaffFactGlobalStore.each(function(record) {
			menu.add({text: record.get('MedPersonal_Fio'), LpuSection_id: record.get('LpuSection_id'), MedPersonal_id:record.get('MedPersonal_id'), iconCls: 'staff16', handler: function() {
				sw.Promed.MedPersonal.setMedPersonal({
					MedPersonal_id: this.MedPersonal_id,
					onSuccess: option.onSuccess,
					params: option.getParams()
				});
			}});
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
			params.MedPersonal_id = option.MedPersonal_id;
			Ext.Ajax.request({
				url: '/?c=EvnSection&m=setEvnSectionMedPersonal',
				params: params,
				failure: function(response, options) {
					Ext.Msg.alert('Ошибка', 'При изменении врача произошла ошибка!');
				},
				success: function(response, action)
				{
					if (response.responseText) {
						var answer = Ext.util.JSON.decode(response.responseText);
						if (answer.success) {
							if(typeof option.onSuccess == 'function')
								option.onSuccess();
						} else if (answer.Error_Code) {
							Ext.Msg.alert('Ошибка #'+answer.Error_Code, answer.Error_Message);
						}
					}
					else {
						Ext.Msg.alert('Ошибка', 'При изменении врача произошла ошибка! Отсутствует ответ сервера.');
					}
				}
			});
		}
	}
};

isUndefined = function(param) {
	return (typeof param === 'undefined');
}
// TODO: Решить с АРМType - по идее, он вообще теперь извне не должен приходить, а определяться АРМом по умолчанию
// TODO: _check надо убрать, получение переделать
sw.Promed.MedStaffFactByUser = {
	ARMType: null,
	onSelect: null,
	store: null,
	last: null,
	current: null,
	button: null,
	currentTheme: 'classic',
	getArmButton: function() {
		if (!(this.button = this.button||Ext.ComponentQuery.query('armButton[refId=buttonChooseArm]')[0]||null)) {
			this.button = Ext.create('sw.armButton');
		}
		return this.button;
	},
	/**
	 * EXT4 addition functions BEGIN
	 */
	initiate: function(option) {
		var parentObject = this;
		this._ini({
			_ini_callback: function(){

				var selectArmButton = Ext.create('Ext.SplitButton',{
					alias: 'widget.armButton',
					text: 'Рабочее место врача',
					refId : 'buttonChooseArm',
					initComponent: function() {
						var me = this;

						me.addEvents({
							afterSelectArm: true
						});

						me.menu = parentObject._createMenu(me);
						me.on('click', function(){
							me.showMenu();
						})
					}
				});

				parentObject.button = selectArmButton;

				if (option&&(typeof option.callback == 'function')) {
					option.callback(selectArmButton);
				}

			}
		});
	},
	_createMenu: function(armBtn){
		// Функция должна получать текущее место работы и службу
		// и визуально строить компонент выбора, в котором текущее место работы - выбрано
		var store = this.store,
		// Выбранные АРМ, место работы, службу определяем по полученным данным
			menuArr = new Array(),
			mainMSFMenuArr = new Array(),
			linkedMSFMenuArr = new Array(),
			menu = Ext.create('Ext.menu.Menu',{
				id:'ListStacSectionMenu',
				overflowY: 'auto',
				layout: {
					type: 'vbox',
					align: 'stretchmax',
//					overflowHandler: 'Scroller'
				},
				listeners:{
					show: function(){
						menu.items.each(function(item){
							if(!item.data) return;
							if(item.data.id==sw.Promed.MedStaffFactByUser.current.id){
								item.addCls('x-menu-item-active');
								menu.scrollBy(item.getOffsetsTo(menu.getEl()), true);
							}
							else{
								item.removeCls('x-menu-item-active');
							}
						})
					}
				}
            }),
			store = this.store,
		// Далее получаем список отделений в этом подразделении и из списка получаем меню
			linkedMSFCount = 0,
			mainMSFCount = 0,
			text = '',
			record;

		this.setFilter({allArms:true});
		for (var i=0; i<store.getCount(); i++) {
			var record = store.getAt(i);
			if (record.get('ARMType') && Ext.isEmpty(record.get('MedStaffFactLink_id')) ) { // Только АРМы, на всякий случай (и только основные места работы)
				mainMSFCount++;
				if (record.get('MedService_id')>0) { // Если служба, то берем название службы
					text = record.get('ARMName')+' / '+record.get('Lpu_Nick')+' / '+record.get('MedService_Name');
				} else {
					if (record.get('MedStaffFact_id')>0) { // иначе берем отделение
						text = record.get('ARMName')+' / '+record.get('Lpu_Nick')+((record.get('LpuSection_Nick'))?' / '+record.get('LpuSection_Nick'):'');
					} else { // а если не врач, то все остальное
						text = record.get('ARMName')+' / '+record.get('Lpu_Nick');
						// если не указан ни врач, ни служба, то АРМ такого типа может быть только один для одного пользователя
					}
				}

				mainMSFMenuArr.push({text: text, data: record.data, iconCls: 'workplace-mp16',disabled:false,
					handler: function() {
						sw.Promed.MedStaffFactByUser.openWorkPlace(this);
						if(this.data.client=='ext4')armBtn.fireEvent('afterSelectArm');
					}
				});
			}

		// Только АРМы и только связанные места работы
			if ( record.get('ARMType') && !Ext.isEmpty(record.get('MedStaffFactLink_id')) ) {
				linkedMSFCount++;
				if (record.get('MedService_id')>0) { // Если служба, то берем название службы
					text = record.get('ARMName')+' / '+record.get('Lpu_Nick')+' / '+record.get('MedService_Name');
				} else {
					if (record.get('MedStaffFact_id')>0) { // иначе берем отделение
						text = record.get('ARMName')+' / '+record.get('Lpu_Nick')+((record.get('LpuSection_Nick'))?' / '+record.get('LpuSection_Nick'):'')
							+ ' / ' + record.get('MedPersonal_FIO') + ' / ' + record.get('MedStaffFactLink_begDT') + ' - ' + record.get('MedStaffFactLink_endDT');
					} else { // а если не врач, то все остальное
						text = record.get('ARMName')+' / '+record.get('Lpu_Nick');
					}
				}

				if ( mainMSFCount > 0 && linkedMSFCount == 1 ) {
					menuArr.push({text:'-'});
				}

				linkedMSFMenuArr.push({text: text, data: record.data, iconCls: 'workplace-mp16', handler: function() {
						sw.Promed.MedStaffFactByUser.openWorkPlace(this);
					}});
			}
		};

		menuArr = mainMSFMenuArr.concat(linkedMSFMenuArr);
		menuArr.push({text:'-'});
		menuArr.push({text: 'Выбор места работы по умолчанию...', data: {}, iconCls: 'settings16', handler: function() {
			Ext.create('common.tools.swSelectWorkPlaceWindow').show();
		}});

		for (var i=0; i<menuArr.length; i++) {
			menu.add((menuArr[i].text=='-')?('-'):(menuArr[i]));
		}
		return menu;
	},
	/**
	 * Получаем текст кнопки
	 */
	_getButtonTextByParams: function(option,callback) {
		var data = option.data || option;
		this.button.menu.items.findBy(function(item, selector){
			if (item && item.data
				&& (isUndefined(data['ARMType']) || item.data.ARMType == data['ARMType'])
				&& (isUndefined(data['Lpu_id']) || item.data.Lpu_id == data['Lpu_id'])
				&& (isUndefined(data['MedStaffFact_id'])||item.data.MedStaffFact_id == data['MedStaffFact_id'])
				&& (isUndefined(data['LpuSection_id'])|| item.data.LpuSection_id == data['LpuSection_id'])
				&& (isUndefined(data['MedService_id'])|| item.data.MedService_id == data['MedService_id'])
			) {
				if (typeof callback == 'function') {
					callback(item.text,item.data);
				}
			}
		},this);

	},

	/**
	 * EXT4 addition functions END
	 */

	/** Инициализация, проверка наличия, загрузка в store списка рабочих мест пользователя текущего ЛПУ
	 *
	 * option._ini_callback
	 * @access private
	 * @param {Object} option
	 * @return void
	 */
	_ini: function(option) {
		log({'_ini': option});

		this.onSelect = option.onSelect || function(data)
		{log({userMedStaffFact: data});
			if (data.ARMForm) {
				getWnd(data.ARMForm, data).show({ARMType: data.ARMType, userMedStaffFact: data});
			}
		};
		this.ARMType = option.ARMType || 'common';
		this.last = null;

		if(!this.store) {
			this.store = Ext.create('Ext.data.Store', {
				proxy: {
					type: 'ajax',
					url : '/?c=User&m=getMSFList',
					reader: {
						type: 'json'
					}
				},
				fields: [
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
					{name: 'SmpUnitType_Code', mapping: 'SmpUnitType_Code'},
					{name: 'SmpUnitParam_IsKTPrint', mapping: 'SmpUnitParam_IsKTPrint'},
					{name: 'MedicalCareKind_id', mapping: 'MedicalCareKind_id'},
					{name: 'PostMedType_id', mapping: 'PostMedType_id'},
					{name: 'PostKind_id', mapping: 'PostKind_id'},
					{name: 'MedStaffFactLink_id', mapping: 'MedStaffFactLink_id'},
					{name: 'MedStaffFactLink_begDT', mapping: 'MedStaffFactLink_begDT'},
					{name: 'MedStaffFactLink_endDT', mapping: 'MedStaffFactLink_endDT'},
					{name: 'Post_Name', type: 'string', mapping: 'Post_Name'},
					{name: 'MedPersonal_FIO', mapping: 'MedPersonal_FIO'},
					{name: 'MedStaffFactCache_IsDisableInDoc', mapping: 'MedStaffFactCache_IsDisableInDoc'},
					{name: 'client', mapping: 'client'}
				],
				autoload: false
			});
		}

		var store = this.store;
		store.clearFilter();

		if( /*store.getCount()==0*/ true ) {
		//if( store.getCount()==0 /*true*/ ) {
			//if(getGlobalOptions().medstafffact.length > 0)
			//console.warn('Читаем сторе...');
			store.load({
				callback: function() {
					store.sort('ARMName', 'ASC'); // добавил сортировку для удобства.
					option._ini_callback();
					if (!Ext.isEmpty(HIDE_MENU_ON_ARMS) && HIDE_MENU_ON_ARMS == 1 && !isSuperAdmin() && !isLpuAdmin() && !isLpuCadrAdmin() && !Ext.isEmpty(main_card_toolbar))
					{
						//Пока неясно, что делать с main_card_toolbar

//						if (sw.Promed.MedStaffFactByUser.store.getCount() > 0) {
//							main_card_toolbar.getLayout().setActiveItem(1);
//						} else {
//							main_card_toolbar.getLayout().setActiveItem(0);
//						}
//						main_card_toolbar.setVisible(true);
//						main_center_panel.setHeight(main_frame.getEl().getHeight());
					}
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
	 * Не требуется за ненадобностью
	 */
	_showMenu: function(id)
	{
		if (this.linkMenu) {
			this.linkMenu.show(Ext.fly(id));
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
//		o.linkTitle = ' <a id="header_link_'+o.id+'" href="#" onClick="sw.Promed.MedStaffFactByUser._showMenu(&quot;'+'header_link_'+o.id+'&quot;);">'+((this.current && this.current.title)?this.current.title:'Рабочее место врача')+'</a>';
	},
	/**
	 * Входящие параметры:
	 * o {object} объект окна АРМа, из которого функция вызвана
	 * option {object} данные пришедшие в форму извне (в частном случае userMedStaffFact)
	 * @access public
	 * @return {void}
	 *
	 */
	setMenuTitle: function(option) {
		var button = this.button;

		if (!option.text) {
			this._getButtonTextByParams(option,function(text,data){
				button.setText(text);
				this.current = data;
				this.current.title = text;
			}.bind(this));
		} else {
			this.current = option.data;
			this.current.title = option.text;
			button.setText(option.text);
		}
		button.current = option.data;
//		if (win) {
//			win.setTitle(option.text||(this.current&&this.current.title)||text+' ('+((option.data&&option.data.MedPersonal_FIO)||(this.current&&this.current.MedPersonal_FIO)||'')+')');
//		}

//		if (!option) {
//			// ошибка, не указаны входные параметры: без них заголовок правильно не отобразится
//
//		} else {
//			option.MedPersonal_FIO = (option && option.MedPersonal_FIO)?option.MedPersonal_FIO:getGlobalOptions().CurMedPersonal_FIO;
//			option.MedPersonal_id = (option && option.MedPersonal_id)?option.MedPersonal_id:getGlobalOptions().CurMedPersonal_id;
//			option.LpuSection_id = (option && option.LpuSection_id)?option.LpuSection_id:getGlobalOptions().CurLpuSection_id;
//		}
//		this.createMenu(o, option);
//		o.setTitle(o.linkTitle + ((option.MedPersonal_FIO)?' (' + option.MedPersonal_FIO + ')':' ( нет информации о враче! )'));
//		window.MedPersonal_FIO = option.MedPersonal_FIO;
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
		if ( this.store.getCount() > 1 ) {
			this.store.each(function(record){
				//если должность и место работы у нескольких записей совпадают, показывать только те ИЗ СОВПАДАЮЩИХ, у которых есть расписание
				var rl = this.store.queryBy(function(rec,id){
					if( record.id != id && record.get('LpuSection_id') == rec.get('LpuSection_id') && record.get('PostMed_id') == rec.get('PostMed_id')) {
						var key = 0;
						if(record.get('Timetable_isExists') == 'false')
							key = record.get('MedStaffFact_id');
						if(rec.get('Timetable_isExists') == 'false')
							key = rec.get('MedStaffFact_id');
						if(key > 0) {
							this.store.filterBy(function(r) {
								return (key != r.get('MedStaffFact_id') && (true || option.allArms));
							});
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
			sw.swMsg.alert('Внимание','К сожалению у врача нет ни одного места работы.');
			return false;
		}*/
//		option._ini_callback = function(){
			// если должность и место работы у нескольких записей совпадают, показывать только те ИЗ СОВПАДАЮЩИХ, у которых есть расписание
			this.setFilter({allArms:false});
			//console.log(option.ARMType+'@@@@');

			// Если АРМ по умолчанию определен и есть доступ к этому АРМу у врача
			if (getDefaultARM()) {
				var defaultArm = this._checkDefaultArm();
				if (defaultArm) {
					option.MedStaffFact_id = defaultArm.MedStaffFact_id;
					option.MedPersonal_id = defaultArm.MedPersonal_id;
					option.LpuSection_id = defaultArm.LpuSection_id;
					option.MedService_id = defaultArm.MedService_id;
					option.MedServiceType_SysNick = defaultArm.MedServiceType_SysNick;
					option.ARMType = defaultArm.ARMType;
					option.ARMForm = defaultArm.ARMForm;
					option.Org_id = defaultArm.Org_id;
					this.openWorkPlace({data: option});

					return true;
				}
			}

			// Если АРМ по умолчанию не определен или к нему нет доступа
			if ( this.store.getCount()==0 ) {
				if (option.ARMType=='smpvr' || option.ARMType=='smpreg') {
					Ext.Msg.alert('Внимание','Нет доступа к АРМу: <br/>у пользователя есть привязка к врачу, необходимо убрать');
				} else {
					//sw.swMsg.alert('Внимание','К сожалению, у врача нет мест работы выбранного типа.');
					Ext.Msg.alert('Внимание','Нет доступа к АРМу: <br/>Пользователь не имеет необходимых мест работы или не связан с врачом.');
				}
				return false;
			} else if ( this.store.getCount()==1 || option.selectFirst ) { // TODO: Тут надо проверить, что сделать
				option.MedService_id = this.store.getAt(0).get('MedService_id');
				if (this.store.getAt(0).get('MedService_id')>0) {
					// Служба
					option.MedService_id = this.store.getAt(0).get('MedService_id');
				} else {
					// Место работы
					option.MedStaffFact_id = this.store.getAt(0).get('MedStaffFact_id');
					option.MedPersonal_id = this.store.getAt(0).get('MedPersonal_id');
					option.LpuSection_id = this.store.getAt(0).get('LpuSection_id');
				}
				option.ARMType = this.store.getAt(0).get('ARMType');
				option.ARMForm = this.store.getAt(0).get('ARMForm');
				this.openWorkPlace({data: option});
				return true;
			} else {
				Ext.create('common.tools.swSelectWorkPlaceWindow').show();
			}
//		}.bind(this);
//		this._ini(option);
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
		// log(getDefaultARM());
		// ищем арм по умолчанию среди списка доступных армов и возвращаем его данные, если нашли
		if (getGlobalOptions().org_id == getDefaultARM().Org_id || (!Ext.isEmpty(getGlobalOptions().lpu_id) && !Ext.isEmpty(getDefaultARM().Lpu_id) && getGlobalOptions().lpu_id == getDefaultARM().Lpu_id) || !getGlobalOptions().org_id) { // Открываем АРМ по умолчанию только если АРМ указан для выбранной организации или организация не выбрана
			this.store.each(function(record) {
				// log(record);
				if (
					record.get('MedService_id') == getDefaultARM().MedService_id &&
					(record.get('MedPersonal_id') == 0 || record.get('MedPersonal_id') == getDefaultARM().MedPersonal_id) &&
					record.get('MedStaffFact_id') == getDefaultARM().MedStaffFact_id &&
					(record.get('Org_id') == getDefaultARM().Org_id || (!Ext.isEmpty(record.get('Lpu_id')) && !Ext.isEmpty(getDefaultARM().Lpu_id)) && record.get('Lpu_id') == getDefaultARM().Lpu_id) &&
					record.get('LpuSection_id') == getDefaultARM().LpuSection_id &&
					record.get('ARMType') == getDefaultARM().ARMType
				) {
					r = record.data;
				}
			});
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

		var parentObj = this;

//		$.ajax({
//			url: C_USER_SETCURARM,
//			type: 'POST',
//			data: {MedStaffFact_id: option.MedStaffFact_id, LpuSection_id: option.LpuSection_id, MedService_id: option.MedService_id, Lpu_id: option.Lpu_id, ARMType: option.ARMType},
//			dataType: 'json',
//			async: false,
//			cache: false
//		}).done(function(data){
//			if ( !data[0] ) {
//				return;
//			}
//
//			data = data[0];
//			data.PostMed_Code = (data.PostMed_Code)?data.PostMed_Code.toString():null;
//			parentObj.ARMType = data.ARMType;
//
//			/*
//			* @To-Do Надо будет убрать запись в GlobalOptions т.к. всегда параметры текущего МР можно получить из sw.Promed.MedStaffFactByUser.last
//			*/
//			getGlobalOptions().CurMedStaffFact_id = data.MedStaffFact_id;
//			getGlobalOptions().CurMedPersonal_id = data.MedPersonal_id;
//			getGlobalOptions().CurLpuSection_id = data.LpuSection_id;
//			getGlobalOptions().CurLpuSectionProfile_id = data.LpuSectionProfile_id;
//			getGlobalOptions().CurLpuUnitType_id = data.LpuUnitType_id;
//			getGlobalOptions().CurPostMed_id= data.PostMed_id;
//			getGlobalOptions().CurLpuSection_Name = data.LpuSection_Name;
//			getGlobalOptions().CurMedPersonal_FIO = data.MedPersonal_FIO;
//			getGlobalOptions().CurMedService_id = data.MedService_id;
//			getGlobalOptions().CurMedService_Nick = data.MedService_Nick;
//			getGlobalOptions().CurMedService_Name = data.MedService_Name;
//			getGlobalOptions().CurMedServiceType_SysNick = data.MedServiceType_SysNick;
//
//			// Если при установке места работы произошла смена ЛПУ, то учитываем все изменения на клиенте
//			if (data.lpu && data.lpu.Lpu_id && (data.lpu.Lpu_id!=getGlobalOptions().lpu_id) && (!isSuperAdmin() && data.ARMType!='superadmin')) {
//				setCurrentLpu(data.lpu);
//			}
//			delete(data.success);
//			parentObj.onSelect(data);
//			parentObj.last = data;
//		});

		if (getGlobalOptions().region.nick.inlist(['vologda']) && getGlobalOptions().newSmpServer) {
			//переход на СМП 2 для вологды
			var groups = getGlobalOptions().groups.split('|'),
				newSmpArms = ['smpdispatchcall', 'smpdispatchstation', 'smpheaddoctor'],
				smpGroups = ['SMPCallDispath', 'smpdispatchstation', 'smpheaddoctor'],
				onlySMP = true;

			for (var i in groups) {
				if (!groups[i].inlist(smpGroups)){
					onlySMP = false;
				}
			}

			if (this.current.ARMType.inlist(newSmpArms) && onlySMP) {
				window.location = getGlobalOptions().newSmpServer + '?c=Main&m=login&login=' + getGlobalOptions().login + '&authFromPromed=2&currentArm=' + this.current.ARMType;
				return;
			} else if (this.current.ARMType.inlist(newSmpArms)) {

				var armSelected = false;

				sw.Promed.MedStaffFactByUser.getArmButton().menu.items.items.forEach(function (rec) {
					if (!rec.data.ARMType.inlist(newSmpArms) && !armSelected) {
						sw.Promed.MedStaffFactByUser.selectARM(rec.data);
						armSelected = true;
					}
				});
				return;
			}
		}

		Ext.Ajax.request({
			url: C_USER_SETCURARM,
			params: {MedStaffFact_id: option.MedStaffFact_id, LpuSection_id: option.LpuSection_id, MedService_id: option.MedService_id, Lpu_id: option.Lpu_id, ARMType: option.ARMType},
			method: 'POST',
			async: false,
			callback: function(o, s, r){
				if (s && r.responseText != '')
				{
					var data = Ext.JSON.decode(r.responseText);

					if ( data[0])
					{
						data = data[0];
						data.PostMed_Code = (data.PostMed_Code)?data.PostMed_Code.toString():null;
						this.ARMType = data.ARMType;

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
						getGlobalOptions().SmpUnitType_Code = data.SmpUnitType_Code;
						getGlobalOptions().SmpUnitParam_IsKTPrint = data.SmpUnitParam_IsKTPrint;
						getGlobalOptions().CurMedServiceType_SysNick = data.MedServiceType_SysNick;
						getGlobalOptions().CurMedService_IsExternal = data.MedService_IsExternal;

						// Если при установке места работы произошла смена ЛПУ, то учитываем все изменения на клиенте
						if (data.lpu && data.lpu.Lpu_id && (data.lpu.Lpu_id!=getGlobalOptions().lpu_id) && (!isSuperAdmin() && data.ARMType!='superadmin')) {
							setCurrentLpu(data.lpu);
						}
						delete(data.success);
						parentObj.onSelect(data);
						parentObj.last = data;
						//warn('last', this.last);
					}
				}
			}
		});
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

		var parentObj = this;

		option._ini_callback = function(){
			Ext.Ajax.request({
				url: C_USER_SETCURMSF,
				params: {MedStaffFact_id: option.MedStaffFact_id},
				callback: function(o, s, r){
					if (s && r.responseText != '')
					{
						var data = Ext.JSON.decode(r.responseText);
						if ( data.success )
						{
							data.PostMed_Code = data.PostMed_Code.toString();
							parentObj.ARMType = option.ARMType;
							data.ARMForm = option.ARMForm;
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
							parentObj.onSelect(data);
							parentObj.last = data;
						}
					}
				}
			});
		}
		this._ini(option);
	},

	/**
	 * Открытие АРМа по умолчанию
	 * @access public
	 * @param {Object} option
	 * @return {void}
	 */
	openDefaultWorkPlace: function() {
		log({'openDefaultWorkPlace':getDefaultARM()});
		if (!getGlobalOptions().setARM_id || (getGlobalOptions().setARM_id == 'default')) {
			if (getDefaultARM() && getDefaultARM().ARMType ) {
				this.selectARM({
					ARMType: getDefaultARM().ARMType
				});
			} else if (getGlobalOptions().IsLocalSMP) {
				this.selectARM({
					selectFirst: true
				});
			}
		} else {
			var record;
			record = this.store.findRecord('id',getGlobalOptions().setARM_id);
			this.openWorkPlace( (record) ? {data:record.data} : getDefaultARM() );
//			this._ini({
//				_ini_callback: function() {
//					var record;
//					record = this.store.findRecord('id',getGlobalOptions().setARM_id);
//					this.openWorkPlace(
//						(record) ? record : getDefaultARM() );
//				}.bind(this)
//			});
		}
	},
	_setThemeByArmType: function(ARMType) {
		var ARMThemes = {
			'forenbiodprtwithmolgenlabbsmesecretary':'neptune',
			'forenbiodprtwithmolgenlabbsmehead':'neptune',
			'forenbiodprtwithmolgenlabbsmeexpert':'neptune',
			'forenbiodprtwithmolgenlabbsmedprthead':'neptune',
			//АРМы службы "Судебно-химическое отделение"
			'forenchemdprtbsmesecretary':'neptune',
			'forenchemdprtbsmehead':'neptune',
			'forenchemdprtbsmeexpert':'neptune',
			'forenchemdprtbsmedprthead':'neptune',
			//АРМы службы "Медико-криминалистическое отделение"
			'medforendprtbsmesecretary':'neptune',
			'medforendprtbsmehead':'neptune',
			'medforendprtbsmeexpert':'neptune',
			'medforendprtbsmedprthead':'neptune',
			//АРМы службы "Судебно-гистологическое отделение"
			'forenhistdprtbsmesecretary':'neptune',
			'forenhistdprtbsmehead':'neptune',
			'forenhistdprtbsmeexpert':'neptune',
			'forenhistdprtbsmedprthead':'neptune',
			//АРМы службы "Отдел организационно-методический"
			'organmethdprtbsmesecretary':'neptune',
			'organmethdprtbsmehead':'neptune',
			'organmethdprtbsmeexpert':'neptune',
			'organmethdprtbsmedprthead':'neptune',
			//АРМы службы "Отдел судебно-медицинской экспертизы трупов с судебно-гистологическим отделением"
			'forenmedcorpsexpdprtbsmesecretary':'neptune',
			'forenmedcorpsexpdprtbsmehead':'neptune',
			'forenmedcorpsexpdprtbsmeexpert':'neptune',
			'forenmedcorpsexpdprtbsmedprthead':'neptune',
			//АРМы службы "Отдел судебно-медицинской экспертизы потерпевших, обвиняемых и других лиц"
			'forenmedexppersdprtbsmesecretary':'neptune',
			'forenmedexppersdprtbsmehead':'neptune',
			'forenmedexppersdprtbsmeexpert':'neptune',
			'forenmedexppersdprtbsmedprthead':'neptune',
			//АРМы службы "Отдел комиссионных и комплексных экспертиз"
			'commcomplexpbsmesecretary':'neptune',
			'commcomplexpbsmehead':'neptune',
			'commcomplexpbsmeexpert':'neptune',
			'commcomplexpbsmedprthead':'neptune',
			//АРМы службы "Районное отделение БСМЭ"
			'forenareadprtbsmesecretary':'neptune',
			'forenareadprtbsmehead':'neptune',
			'forenareadprtbsmeexpert':'neptune',
			'forenareadprtbsmedprthead':'neptune',
			// АРМ Лаборанта БСМЭ
			forenmedexppersdprtbsmeexpertassistant: 'neptune'
		};

		//законсервировано, тк перенес в promed.php
		// if (this.currentTheme != ARMThemes[ARMType]) {

			// if (ARMThemes[ARMType] && ARMThemes[ARMType] == 'neptune') {
				// Ext.util.CSS.swapStyleSheet("theme","/extjs4/resources/css/ext-all-neptune.css");

				// this.currentTheme = ARMThemes[ARMType];
				//Почему-то если сразу прописать после swapStyleSheet, то doLayout не срабатывает, а в swapStyleSheet callback-а нет
				// setTimeout(function(){
					// Ext.getCmp('Mainviewport_Toolbar').doLayout();
				// },500)
			// } else /*option.data.theme == 'default'*/ {
				// Ext.util.CSS.swapStyleSheet("theme","/extjs4/resources/css/ext-all.css");
				// this.currentTheme = 'classic';
				//Почему-то если сразу прописать после swapStyleSheet, то doLayout не срабатывает, а в swapStyleSheet callback-а нет
				// setTimeout(function(){
					// Ext.getCmp('Mainviewport_Toolbar').doLayout();
				// },500)
			// }
		// }
	},
	/**
	 * Открытие формы соответствующего выбранного АРМа
	 * Входные параметры: выбранная строка АРМа/места работы
	 * @access public
	 * @param {Object} option
	 * @return {void}
	 */
	openWorkPlace: function(option) {
		// TODO: Надо думать над тем, что может быть ФИО брать уже из этого же списка, а не при setMedStaffFact (но это лирика пока... )
		log({'openWorkPlace':option});
		var globalOptions = getGlobalOptions(), win;
		this.setMenuTitle(option);
		if (option.data) {

			this._setThemeByArmType(option.data.ARMType);

			if (option.data.client && option.data.client != globalOptions.client) {
				window.onbeforeunload = null;
				window.location='/?c=promed&m=loadArm&ARM_id='+option.data.id;
				return;
			}
			//this.setMenuTitle(this,option);
			if (option.data.MedStaffFact_id && option.data.MedStaffFact_id>0) { // медстаффакт
				option.data.onSelect = function(data) {
					if (option.data.ARMForm) {
						win = getWnd(option.data.ARMForm);
						win.show({ARMType: option.data.ARMType, userMedStaffFact: data});

					}
					else
						Ext.create('common.tools.swSelectWorkPlaceWindow').show();
				};
				this.setARM(option.data);
			} else { // службы
				//this.onSelect = option.onSelect || function(data) { getWnd('swMPWorkPlaceWindow').show({ARMType: data.ARMType, userMedStaffFact: data }); };
				option.data.onSelect = function(data) {
					if (option.data.ARMForm)  {
						win = getWnd(option.data.ARMForm);
						win.show(option.data);

					}
					else
						Ext.create('common.tools.swSelectWorkPlaceWindow').show();
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
	 * option.callback - функция, выполняющаяся после создания меню
	 * @access public
	 * @param {Object} option
	 * @return {void}
	 */
	createListWorkPlacesMenu: function(option) {
		// Функция должна получать текущее место работы и службу
		// и визуально строить компонент выбора, в котором текущее место работы - выбрано
		var store = this.store;
		// Выбранные АРМ, место работы, службу определяем по полученным данным
		var o = this;
		var menuArr = new Array();
		var mainMSFMenuArr = new Array();
		var linkedMSFMenuArr = new Array();
		var menu = Ext.create('Ext.menu.Menu',{id:'ListStacSectionMenu'});
		var creatingMenu = function() {
			var store = this.store;
			this.setFilter({allArms:true});
			// Далее получаем список отделений в этом подразделении и из списка получаем меню
			var
			linkedMSFCount = 0,
			mainMSFCount = 0,
			disabled = false,
			text = '',
			record;
			if (option.setARM) {
				record = this.store.findRecord('id',getGlobalOptions().setARM_id);
				option = (record&&record.data)?record.data:option;
			}
			for (var i=0; i<store.getCount(); i++) {
				var record = store.getAt(i);
				disabled = false;
				if (record.get('ARMType') && Ext.isEmpty(record.get('MedStaffFactLink_id')) ) { // Только АРМы, на всякий случай (и только основные места работы)
					mainMSFCount++;

					if (record.get('MedService_id')>0) { // Если служба, то берем название службы
						text = record.get('ARMName')+' / '+record.get('Lpu_Nick')+' / '+record.get('MedService_Name');
						if ( (option.MedService_id == record.get('MedService_id')) && (option.ARMType == record.get('ARMType')) ) {
							o.current = record.data;
							o.current.title = text;
							disabled = true;
							//text = '<b>'+text+'</b>';
						}
					} else {
						if (record.get('MedStaffFact_id')>0) { // иначе берем отделение
							text = record.get('ARMName')+' / '+record.get('Lpu_Nick')+((record.get('LpuSection_Nick'))?' / '+record.get('LpuSection_Nick'):'');

							if (option.MedStaffFact_id == record.get('MedStaffFact_id') && option.LpuSection_id == record.get('LpuSection_id')) {
								o.current = record.data;
								o.current.title = text;
								disabled = true;
								//text = '<b>'+text+'</b>';
							}
						} else { // а если не врач, то все остальное
							text = record.get('ARMName')+' / '+record.get('Lpu_Nick');
							// если не указан ни врач, ни служба, то АРМ такого типа может быть только один для одного пользователя
							if (option.ARMType == record.get('ARMType')) {
								o.current = record.data;
								o.current.title = text;
								disabled = true;
								//text = '<b>'+text+'</b>';
							}
						}
					}
					/*
					 *
					 *ARMType: value.ARMType,ARMName: value.ARMName,Lpu_id: value.Lpu_id,MedPersonal_id: value.MedPersonal_id,MedService_id: value.MedService_id,wtitle: wintitle.slice(2),text: value.ARMName + ' / '+value.Lpu_Nick + ' / '+value.MedService_Name,icon: '/img/icons/workplace-mp16.png',disabled: false
					 **/
					mainMSFMenuArr.push({text: text, data: record.data, iconCls: 'workplace-mp16',disabled:disabled, handler: function() {
							sw.Promed.MedStaffFactByUser.openWorkPlace(this);
						}});
				}

			// Только АРМы и только связанные места работы
				if ( record.get('ARMType') && !Ext.isEmpty(record.get('MedStaffFactLink_id')) ) {
					linkedMSFCount++;
					if (record.get('MedService_id')>0) { // Если служба, то берем название службы
						text = record.get('ARMName')+' / '+record.get('Lpu_Nick')+' / '+record.get('MedService_Name');
						if ( (option.MedService_id == record.get('MedService_id')) && (option.ARMType == record.get('ARMType')) ) {
							o.current = record.data;
							o.current.title = text;
						}
					} else {
						if (record.get('MedStaffFact_id')>0) { // иначе берем отделение
							text = record.get('ARMName')+' / '+record.get('Lpu_Nick')+((record.get('LpuSection_Nick'))?' / '+record.get('LpuSection_Nick'):'')
								+ ' / ' + record.get('MedPersonal_FIO') + ' / ' + record.get('MedStaffFactLink_begDT') + ' - ' + record.get('MedStaffFactLink_endDT');

							if (option.MedStaffFact_id == record.get('MedStaffFact_id')) {
								o.current = record.data;
								o.current.title = text;
							}
						} else { // а если не врач, то все остальное
							text = record.get('ARMName')+' / '+record.get('Lpu_Nick');
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

					linkedMSFMenuArr.push({text: text, data: record.data, iconCls: 'workplace-mp16', handler: function() {
							sw.Promed.MedStaffFactByUser.openWorkPlace(this);
						}});
				}
			};

			menuArr = mainMSFMenuArr.concat(linkedMSFMenuArr);
			menuArr.push({text:'-'});
			menuArr.push({text: 'Выбор места работы по умолчанию...', data: {}, iconCls: 'settings16', handler: function() {Ext.create('common.tools.swSelectWorkPlaceWindow').show();}});

			for (var i=0; i<menuArr.length; i++) {
				menu.add((menuArr[i].text=='-')?('-'):(menuArr[i]));
			}


			if (typeof option.callback == 'function') {
				option.callback(menu);
			}
		}.bind(this);

//		if (this.store.getCount()>0) {
//			creatingMenu();
//		} else {
			option._ini_callback = creatingMenu;
			this._ini(option);
//		}
	}
};

/** Набор методов назначений
 *
 */
sw.Promed.EvnPrescr = {
	//parentEvnClass_SysNick: null,
	prescriptionTypeStore: null,
	/** Создание списка типов назначений
	 *
	 * option.id - id списка типов назначений
	 * option.getParams - функция, которая должна вернуть парамсы
	 * option.onCreate
	 * option.callbackEditWindow
	 * option.onHideEditWindow
	 * option.parentEvnClass_SysNick
	 * @param {Object} option
	 * @return {Object}
	 */
	createPrescriptionTypeMenu: function(option) {
		if(!this.prescriptionTypeStore) {
			this.prescriptionTypeStore = Ext.create('Ext.db.AdapterStore',{
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
				var menu = Ext.create('Ext.menu.Menu',{id: option.id || 'menuPrescriptionType'});
				var exceptionTypes = option.exceptionTypes || [];
				this.prescriptionTypeStore.clearFilter();
				this.prescriptionTypeStore.filterBy(function(record, id) {
					if(option.parentEvnClass_SysNick == 'EvnVizitPL') {
						return record.get('PrescriptionType_Code').toString().inlist(['1','2','5','6','7','11','12']); // 4
					} else {
						return record.get('PrescriptionType_Code').toString().inlist(['1','2','4','5','6','7','10','11','12']); // 3
					}
				}, this);
				this.prescriptionTypeStore.each(function(record) {
					if(!record.get('PrescriptionType_Code').toString().inlist(exceptionTypes)) {
						menu.add({text: record.get('PrescriptionType_Name'), PrescriptionType_Code: record.get('PrescriptionType_Code'), PrescriptionType_id:record.get('PrescriptionType_id'), iconCls: '', handler: function() {
							option.PrescriptionType_id = this.PrescriptionType_id;
							option.PrescriptionType_Code = this.PrescriptionType_Code;
							option.action = 'add';
							option.data = option.getParams();
							sw.Promed.EvnPrescr.openEditWindow(option);
						}});
					}
				});
				option.onCreate(menu);
			}.bind(this)
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
	 * @return {void}
	 */
	direct: function(option) {
		if(!option || !option.PrescriptionType_Code || !option.PrescriptionType_Code.toString().inlist(['4','6','7','11','12']) )
			return false;
		var params = new Object();
		params.callback = function(ndata) {
			if ( typeof option.callback == 'function' ) {
				option.callback(ndata);
			}
		}.bind(this);
		params.onHide = function() {
			if ( typeof option.onHide == 'function' ) {
				option.onHide();
			}
		}.bind(this);
		params.parentEvnClass_SysNick = option.parentEvnClass_SysNick;
		params.EvnDirection_rid = option.Evn_rid;
		params.EvnDirection_pid = option.Evn_pid;
		params.Diag_id = option.Diag_id;
		params.Person_id = option.Person_id;
		params.PersonEvn_id = option.PersonEvn_id;
		params.Server_id = option.Server_id;
		params.Person_Firname = option.Person_Firname;
		params.Person_Surname = option.Person_Surname;
		params.Person_Secname = option.Person_Secname;
		params.Person_Birthday = option.Person_Birthday;
		params.EvnPrescr_id = option.EvnPrescr_id;
		params.PrescriptionType_Code = option.PrescriptionType_Code;
		if(option.PrescriptionType_Code == '4') {
			params.LpuSectionProfile_id = option.LpuSectionProfile_id;
		} else {
			params.uslugaList = option.uslugaList;
			//getWnd('swUslugaMedServiceRecordWindow').show(params);
			if(option.uslugaList.length == 1) {
				params.UslugaComplex_id = option.uslugaList[0];
			}
			params.LpuUnitType_SysNick = 'parka';
			params.LpuUnitType_id = 3;
		}
		params.Lpu_id = getGlobalOptions().lpu_id;
		params.formMode = 'evn_prescr';
		params.fromEmk = option.fromEmk || null;
		params.userMedStaffFact = option.userMedStaffFact;
		params.onEvnDirectionSave = option.callback;

		getWnd('swMPRecordWindow').show(params);

		/*
		Ext.Ajax.request({
			failure: function(response, opt) {
				sw.swMsg.alert('Ошибка', 'Ошибка');
			},
			params: {
				uslugaList: option.uslugaList.toString(),
				start: 0,
				limit: 100
			},
			success: function(response, opt) {
				if (response.responseText) {
					var answer = Ext.util.JSON.decode(response.responseText);
					if(answer.data && answer.totalCount && answer.totalCount > 0) {
						params.MedService_id = answer.data[0].MedService_id;
						params.MedServiceType_SysNick = answer.data[0].MedServiceType_SysNick;
						params.Lpu_id = answer.data[0].Lpu_id;
						params.LpuUnit_id = answer.data[0].LpuUnit_id;
						params.LpuUnitType_id = answer.data[0].LpuUnitType_id;
						params.LpuUnitType_SysNick = answer.data[0].LpuUnitType_SysNick;
						params.LpuSection_id = answer.data[0].LpuSection_id;
						params.LpuSectionProfile_id = answer.data[0].LpuSectionProfile_id;
						params.LpuBuilding_id = answer.data[0].LpuBuilding_id;
						params.MedService_Nick = answer.data[0].MedService_Nick;
						params.UslugaComplex_Name = answer.data[0].UslugaComplex_Name;
						params.UslugaComplex_id = answer.data[0].UslugaComplex_id;
						getWnd('swMPRecordWindow').show(params);
					}
				}
				else {
					Ext.Msg.alert('Ошибка', 'Ошибка! Отсутствует ответ сервера.');
				}
			},
			url: '/?c=TimetableGraf&m=getListMedServiceByUsluga'
		});*/
	},
	/** Отмена назначения
	 *
	 * conf.ownerWindow -
	 * conf.getParams - функция, которая должна вернуть парамсы (PrescriptionType_id,EvnPrescr_id,parentEvnClass_SysNick,EvnPrescr_setDate,EvnPrescr_rangeDate)
	 * conf.callback
	 * @param {Object} conf
	 * @return {void}
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
			,EvnPrescr_rangeDate: data.EvnPrescr_rangeDate || null
		};

		Ext.Msg.show({
			title: 'Внимание!',
			msg: 'Отменить назначение?',
			buttons: Ext.Msg.YESNO,
			fn: function(btn) {
				if (btn === 'yes') {
					var loadMask = new Ext.LoadMask(conf.ownerWindow.getEl(), { msg: "Отмена назначения..." });
					loadMask.show();

					Ext.Ajax.request({
						failure: function(response, opt) {
							loadMask.hide();
							sw.swMsg.alert('Ошибка', 'Ошибка при отмене назначения');
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
									Ext.Msg.alert('Ошибка', answer.Error_Message);
								}
							}
							else {
								Ext.Msg.alert('Ошибка', 'Ошибка при отмене назначения! Отсутствует ответ сервера.');
							}
						},
						url: '/?c=EvnPrescr&m=cancelEvnPrescr'
					});
				} else {
					return false;
				}
			},
			icon: Ext.MessageBox.QUESTION
		});
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
		} else if(data.PrescriptionType_id.toString().inlist(['4','5','6','7','11','12'])) {
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
			title: 'Внимание!',
			msg: 'После подписания невозможно будет редактировать данные назначения. Продолжить?',
			buttons: Ext.Msg.YESNO,
			fn: function(btn) {
				if (btn === 'yes') {*/
					var loadMask = new Ext.LoadMask(conf.ownerWindow.getEl(), { msg: "Подписание назначения..." });
					loadMask.show();

					Ext.Ajax.request({
						failure: function(response, opt) {
							loadMask.hide();
							sw.swMsg.alert('Ошибка', 'Ошибка при подписании назначения');
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
									Ext.Msg.alert('Ошибка', answer.Error_Message);
								}
							}
							else {
								Ext.Msg.alert('Ошибка', 'Ошибка при подписании назначения! Отсутствует ответ сервера.');
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
	storeCureStandart: Ext.create('Ext.data.Store',{
		reader: Ext.create('Ext.data.JsonReader',
		{
			id: 'CureStandart_id'
		},
		[
			{name: 'CureStandart_id', mapping: 'CureStandart_id'},
			{name: 'CureStandartConditionsType_Name', type: 'string', mapping: 'CureStandartConditionsType_Name'},
			{name: 'CureStandartAgeGroupType_Name', type: 'string', mapping: 'CureStandartAgeGroupType_Name'},
			{name: 'Diag_Code', type: 'string', mapping: 'Diag_Code'},
			{name: 'Diag_Name', type: 'string', mapping: 'Diag_Name'},
			{name: 'CureStandartStageType_Name', type: 'string', mapping: 'CureStandartStageType_Name'},
			{name: 'CureStandartPhaseType_Name', type: 'string', mapping: 'CureStandartPhaseType_Name'},
			{name: 'CureStandartComplicationType_Name', type: 'string', mapping: 'CureStandartComplicationType_Name'},
			{name: 'CureStandartTreatment_Duration', type: 'string', mapping: 'CureStandartTreatment_Duration'}
		]),
		proxy: {
			type: 'ajax',
			url: '/?c=EvnPrescr&m=loadCureStandartList',
			reader: {
				type: 'json'
			}
		}
	}),
	addwithtemplate: function(conf) {
		var input_data = {
			parentEvnClass_SysNick: conf.parentEvnClass_SysNick || null
			,EvnPrescr_pid: conf.Evn_pid
			,Diag_id: conf.Diag_id || null
			,CureStandartAgeGroupType_id: conf.CureStandartAgeGroupType_id || null
			,CureStandartConditionsType_id: conf.CureStandartConditionsType_id || null
		};

		var loadMask = new Ext.LoadMask(conf.ownerWindow.getEl(), { msg: "Запрос списка " + getMESAlias() + "..." });
		loadMask.show();

		Ext.Ajax.request({
			failure: function(response, opt) {
				loadMask.hide();
				sw.swMsg.alert('Ошибка', 'Ошибка при загрузке списка ' + getMESAlias());
			},
			params: input_data,
			success: function(response, opt) {
				loadMask.hide();
				if (response.responseText) {
					var answer = Ext.util.JSON.decode(response.responseText);
					if (!Ext.isArray(answer)) {
						Ext.Msg.alert('Ошибка', 'Неправильный формат ответа сервера!');
						return false;
					}
					if (answer[0].Error_Message) {
						Ext.Msg.alert('Ошибка', answer[0].Error_Message);
						return false;
					}
					this.storeCureStandart.removeAll();
					this.storeCureStandart.loadData(answer);
					if ( this.storeCureStandart.getCount()==0 ) {
						sw.swMsg.alert('Сообщение', getMESAlias() + ' не найден!');
						return false;
					} else if ( this.storeCureStandart.getCount()==1) {
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
			}.bind(this),
			url: '/?c=EvnPrescr&m=loadCureStandartList'
		});
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
		this.lastSelectCureStandartId = conf.CureStandart_id;
		var params = new Object();
		params.callback = function(ndata) {
			if ( typeof conf.callback == 'function' ) {
				conf.callback(ndata);
			}
		}.bind(this);
		params.CureStandart_id = conf.CureStandart_id;
		params.parentEvnClass_SysNick = conf.parentEvnClass_SysNick;
		params.Evn_pid = conf.Evn_pid;
		params.Evn_rid = conf.Evn_rid;
		params.PersonEvn_id = conf.PersonEvn_id;
		params.Server_id = conf.Server_id;
		params.action = 'edit';
		getWnd('swCureStandartTemplateWindow').show(params);
	},
	execRequest: function(conf) {
		var loadMask = new Ext.LoadMask(conf.ownerWindow.getEl(), { msg: "Выполнение запроса к серверу..." });
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
				sw.swMsg.alert('Ошибка', (response.status ? response.status.toString() + ' ' + response.statusText : 'Ошибка при выполнении запроса к серверу'));
			},
			params: params,
			success: function(response, options) {
				loadMask.hide();

				var response_obj = Ext.util.JSON.decode(response.responseText);

				if ( response_obj.success == false ) {
					sw.swMsg.alert('Ошибка', response_obj.Error_Msg ? response_obj.Error_Msg : 'Ошибка при выполнении запроса к серверу');
				}
				else {
					conf.onExecSuccess();
				}
			},
			url: '/?c=EvnPrescr&m=execEvnPrescr'
		});
	},
	exec: function(conf) {
		if ( !conf.EvnPrescr_id || conf.EvnPrescr_IsExec == 2 || conf.PrescriptionStatusType_id != 2 ) {
			return false;
		}
		if ( typeof conf.onExecSuccess != 'function' ) {
			conf.onExecSuccess = Ext.emptyFn;
		}
		if ( typeof conf.onExecCancel != 'function' ) {
			conf.onExecCancel = Ext.emptyFn;
		}

		var dlg_msg = 'Выполнить назначение?';
		var dlg_btn = Ext.Msg.YESNO;
		if(Number(conf.PrescriptionType_Code) == 5) {
			dlg_msg = 'Списать медикаменты с остатков отделения?';
			dlg_btn = Ext.Msg.YESNOCANCEL;
		}
		sw.swMsg.show({
			msg: dlg_msg,
			buttons: dlg_btn,
			fn: function(buttonId, text, obj) {
				switch ( Number(conf.PrescriptionType_Code) ) {
					case 6:
					case 7:
					case 11:
					case 12: //направление и создание заказа на комплексную услугу
						if ( buttonId == 'yes' ) {
							var uslugaList;
							if(typeof conf.UslugaId_List == 'string' && conf.UslugaId_List.length > 0) {
								uslugaList = conf.UslugaId_List.split(',');
							} else {
								return false;
							}
							if( conf.EvnDirection_id > 0 ) {
								this.direct({
									//fromEmk: true
									parentEvnClass_SysNick: 'EvnSection'
									,EvnPrescr_id: conf.EvnPrescr_id
									,Evn_rid: conf.EvnPrescr_rid
									,Evn_pid: conf.EvnPrescr_pid
									,Diag_id: conf.Diag_id || null
									,uslugaList: uslugaList
									,PrescriptionType_Code: conf.PrescriptionType_Code
									,Person_Firname: conf.Person_Firname
									,Person_Surname: conf.Person_Surname
									,Person_Secname: conf.Person_Secname
									,Person_Birthday: conf.Person_Birthday
									,Person_id: conf.Person_id
									,PersonEvn_id: conf.PersonEvn_id
									,Server_id: conf.Server_id
									,userMedStaffFact: sw.Promed.MedStaffFactByUser.last
									,callback: function(fdata){
										this.execRequest(conf);
									}.bind(this)
								});
							} else {
								//this.execRequest(conf);
								if( conf.PrescriptionType_Code == 6 ) {
									Ext.Ajax.request({
										url: '/?c=EvnPrescr&m=loadEvnUslugaData',
										params: { parentEvnClass_SysNick: 'EvnSection', Evn_id: conf.EvnPrescr_pid, UslugaComplex_id: uslugaList[0] },
										success: function(r, o) {
											var obj = Ext.util.JSON.decode(r.responseText)[0];
											if ( obj.success == false ) {
												sw.swMsg.alert('Ошибка', obj.Error_Msg ? obj.Error_Msg : 'Ошибка при выполнении запроса к серверу');
												return false;
											}

											if( obj.Usluga_id == null ) {
												sw.swMsg.alert('Ошибка', 'Отсутствует общая услуга');
												return false;
											}

											getWnd('swEvnUslugaCommonEditWindow').show({
												action: 'add',
												parentClass: 'EvnPrescr',
												Person_id: conf.Person_id,
												callback: function() {
													this.execRequest(conf);
												}.bind(this),
												formParams: {
													EvnPrescrProc_id: conf.EvnPrescr_id
													,EvnUslugaCommon_setDate: conf.EvnPrescr_setDate
													,Person_id: conf.Person_id
													,PersonEvn_id: conf.PersonEvn_id
													,Server_id: conf.Server_id
													,Usluga_id: obj.Usluga_id
													,MedStaffFact_id: sw.Promed.MedStaffFactByUser.current.MedStaffFact_id
												},
												parentEvnComboData: [{
													Evn_id: conf.EvnPrescr_pid
													,Evn_Name: obj.Evn_setDate + ' / ' + obj.LpuSection_Name + ' / ' + obj.MedPersonal_FIO
													,Evn_setDate: obj.Evn_setDate
													,LpuSection_id: obj.LpuSection_id || sw.Promed.MedStaffFactByUser.current.LpuSection_id
												}]
											});
										}.bind(this)
									});
								}
							}
						}
						else {
							conf.onExecCancel();
						}
						break;
					case 10:
						if ( buttonId == 'yes' ) {
							if ( getWnd('swEvnObservEditWindow').isVisible() ) {
								sw.swMsg.alert('Ошибка', 'Окно уже открыто');
								return false;
							}

							getWnd('swEvnObservEditWindow').show({
								callback: function() {
									conf.onExecSuccess();
								},
								formParams: {
									 EvnObserv_pid: conf.EvnPrescr_id
									,ObservTimeType_id: conf.ObservTimeType_id
									,EvnObserv_setDate: conf.EvnPrescr_setDate
									,Person_id: conf.Person_id
									,PersonEvn_id: conf.PersonEvn_id
									,Server_id: conf.Server_id
								}
							});
						}
						else {
							conf.onExecCancel();
						}
						break;
					case 5:
						if ( buttonId == 'yes' ) {
							// Выполнить назначение со списанием медикаментов
		/*
							sw.swMsg.alert('Сообщение', 'Функционал выполнения назначения со списанием остатков в разработке', function() {
								conf.onExecCancel();
							});
		*/
							if ( getWnd('swEvnPrescrDrugStreamWindow').isVisible() ) {
								sw.swMsg.alert('Ошибка', 'Окно поточного списания медикаментов уже открыто');
								return false;
							}

							getWnd('swEvnPrescrDrugStreamWindow').show({
								callback: function() {
									conf.onExecSuccess();
								},
								formParams: {
									EvnPrescrTreat_id: conf.EvnPrescr_id,
									EvnPrescrTreatTimetable_id: conf.Timetable_id || null,
									Person_id: conf.Person_id
								}
							});
						}
						else if ( buttonId == 'no' ) {
							// Выполнить назначение без списания медикаментов
							this.execRequest(conf);
						}
						else {
							conf.onExecCancel();
						}
						break;
					default:
						if ( buttonId == 'yes' ) {
							this.execRequest(conf);
						}
						else {
							conf.onExecCancel();
						}
						break;
				}

			}.bind(this),
			icon: Ext.MessageBox.QUESTION,
			title: 'Вопрос'
		});
	},
	openEditWindow: function(option) {
		var win_name,
			formParams;
		var params = new Object();
		params.callback = function(ndata) {
			if ( typeof option.callbackEditWindow == 'function' ) {
				option.callbackEditWindow(ndata);
			}
		}.bind(this);
		params.onHide = function(ndata) {
			if ( typeof option.onHideEditWindow == 'function' ) {
				option.onHideEditWindow(ndata);
			}
		}.bind(this);
		params.parentEvnClass_SysNick = option.parentEvnClass_SysNick;
		params.action = option.action || 'add';
		//log(option);
		switch(option.PrescriptionType_Code.toString()) {
			case '1':
			case '2':
			//case '3':
			case '10':
				//if(option.parentEvnClass_SysNick == 'EvnSection' ) {
					if(params.action == 'add') {
						switch ( option.PrescriptionType_Code.toString() ) {
							case '1': win_name = 'swPolkaEvnPrescrRegimeEditWindow'; break;
							case '2': win_name = 'swPolkaEvnPrescrDietEditWindow'; break;
							case '10': win_name = 'swPolkaEvnPrescrObservEditWindow'; break;
						}
						formParams = {
							EvnPrescr_pid: option.data.Evn_pid
							,Person_id: option.data.Person_id
							,PersonEvn_id: option.data.PersonEvn_id
							,Server_id: option.data.Server_id
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
							case '1': win_name = 'swPolkaEvnPrescrRegimeEditWindow'; break;
							case '2': win_name = 'swPolkaEvnPrescrDietEditWindow'; break;
							case '10': win_name = 'swPolkaEvnPrescrObservEditWindow'; break;
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
					,EvnPrescrCons_setDate: Date.parseDate((getGlobalOptions().date), 'd.m.Y')//option.data.begDate
					,PersonEvn_id: option.data.PersonEvn_id || null
					,Server_id: option.data.Server_id || null
				};
				break;
			case '5':
				win_name = 'swPolkaEvnPrescrTreatEditWindow';
				formParams = {
					EvnPrescrTreat_pid: option.data.Evn_pid
					,EvnPrescrTreat_id: option.data.EvnPrescr_id || null
					,EvnPrescrTreat_setDate: Date.parseDate((getGlobalOptions().date), 'd.m.Y')//option.data.begDate
					,PersonEvn_id: option.data.PersonEvn_id || null
					,Server_id: option.data.Server_id || null
				};
				break;
			case '6':
				win_name = 'swPolkaEvnPrescrProcEditWindow';
				formParams = {
					EvnPrescrProc_pid: option.data.Evn_pid
					,EvnPrescrProc_id: option.data.EvnPrescr_id || null
					,EvnPrescrProc_setDate: Date.parseDate((getGlobalOptions().date), 'd.m.Y')//option.data.begDate
					,PersonEvn_id: option.data.PersonEvn_id || null
					,Server_id: option.data.Server_id || null
				};
				break;
			case '7':
				win_name = 'swPolkaEvnPrescrOperEditWindow';
				formParams = {
					EvnPrescrOper_pid: option.data.Evn_pid
					,EvnPrescrOper_id: option.data.EvnPrescr_id || null
					,EvnPrescrOper_setDate: Date.parseDate((getGlobalOptions().date), 'd.m.Y')//option.data.begDate
					,PersonEvn_id: option.data.PersonEvn_id || null
					,Server_id: option.data.Server_id || null
				};
				break;
			case '11':
				win_name = 'swPolkaEvnPrescrLabDiagEditWindow';
				formParams = {
					EvnPrescrLabDiag_pid: option.data.Evn_pid
					,EvnPrescrLabDiag_id: option.data.EvnPrescr_id || null
					,EvnPrescrLabDiag_setDate: Date.parseDate((getGlobalOptions().date), 'd.m.Y')//option.data.begDate
					,PersonEvn_id: option.data.PersonEvn_id || null
					,Server_id: option.data.Server_id || null
				};
				break;
			case '12':
				win_name = 'swPolkaEvnPrescrFunDiagEditWindow';
				formParams = {
					EvnPrescrFuncDiag_pid: option.data.Evn_pid
					,EvnPrescrFuncDiag_id: option.data.EvnPrescr_id || null
					,EvnPrescrFuncDiag_setDate: Date.parseDate((getGlobalOptions().date), 'd.m.Y')//option.data.begDate
					,PersonEvn_id: option.data.PersonEvn_id || null
					,Server_id: option.data.Server_id || null
				};
				break;
		}
		if(!win_name || !formParams) {
			log('Undefined parameters for selected PrescriptionType_Code');
			return false;
		}
		params.formParams = formParams;
		getWnd(win_name).show(params);
	}
};
/** Функция отправляет данные на сервер и получает данные обратно
 *
 */
function loadStores(components, callback, owner) {
	var o = {};
	if (Ext.isRemoteDB) {
		// Проверяем наличие у компонентов Store
		// Пробегаемся по компонентам и собираем запросы
		for(i in components) {
			if (components[i].getStore) {
				var s = components[i].getStore(); /* 'name': s.tableName, */
				var p = s.params || ((components[i].loadParams && components[i].loadParams.params)?components[i].loadParams.params: null); // Стандартные параметры или параметры для чтения
				o[s.tableName] = {'url': s.url,'baseparams':s.baseParams,'params':p};
				//console.log(components[i].id, ':',); //
			}
		}
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
					for(i in components) {
						if (components[i].getStore && components[i].getStore().tableName && result[components[i].getStore().tableName]) {
							if (components[i].getStore().getCount()==0) { // Если хранилище пустое, то заполняем его пришедшими данными
								components[i].getStore().loadData(result[components[i].getStore().tableName], false);
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

function loadLocalMongoCombos(combos) {
	if (!Ext.isArray(combos)) {
		return false;
	}


	var countMongoCombo = 0,
		res = {};
		
	for (var i=0; i<combos.length; i++) {
		var combo = combos[i];
		Ext.data.Store.prototype.clearFilter.call(combo.store);
		//не надо грузить localComboMongo store множество раз
		//console.log('store count - ', o.store.count())
		if(!combo.store.count())
		{
			countMongoCombo++;
			//собираем поля
			var ffields = {},
				cfields = combo.store.getProxy().getReader().getFields();
	
			Ext.Object.each(cfields, function(key, value, myself)
			{
				if (value.type.type != 'auto')
				{
					var nn = value.name
					ffields[nn] = ""
				}
			})
			ffields.object = combo.tableName;

			//собираем urls
			var curl =combo.store.url,								
			//собираем таблицы
				ctable =combo.tableName;
			
			//собираем в параметры
			res[ctable] = {
				url: combo.store.url,
				params : (combo.params || null),
				baseparams : ffields							
			}
		}
	}

	if (countMongoCombo){
		Ext.Ajax.request({
		url: '/?c=MongoDBWork&m=getDataAll',
		callback: function(opt, success, response) {
			if (success){
				var response_obj = Ext.JSON.decode(response.responseText);
				//заполняем комбики монго
				for (var i=0, combo = combos[i]; i<combos.length; i++) {
					combo.store.loadData(response_obj[combo.tableName], true);
					combo.store.commitChanges();
					combo.store.fireEvent('load');
				}
			}
		}.bind(this),
		params: {'data': Ext.JSON.encode(				
			res
		)}

		})
	}
	
}

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


function getMESAlias() {
	var result = 'МЭС';

	if ( getGlobalOptions().region ) {
		switch ( getGlobalOptions().region.nick ) {
			case 'samara':
				result = 'КСГ';
			break;
		}
	}

	return result;
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
			}.bind(this);
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
					}.bind(this),
					params: data
				});
			}.bind(this);
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
							el.on('mouseout',function(event){ if (isMouseLeaveOrEnter(event,this)) { Ext.get('toolbar_'+this.id.split('_')[1]+'_'+ options.EvnXml_id).setStyle('display','none');} });
							el.on('mouseover',function(event){ if (isMouseLeaveOrEnter(event,this)) { Ext.get('toolbar_'+this.id.split('_')[1]+'_'+ options.EvnXml_id).setStyle('display','block');} });
						}

						el = Ext.get('EvnXml_'+o +'_'+ options.EvnXml_id+'_delete');
						if(el) {
							el.on('click', function(e,s,data){this.deleteEvnXmlNode(data);}.bind(this),capt,{EvnXml_id:options.EvnXml_id, name: o});
						}

						el = Ext.get('EvnXml_'+o +'_'+ options.EvnXml_id+'_clear');
						if(el) {
							el.on('click', function(e,s,data){this.clearEvnXmlNode(data);}.bind(this),capt,{field:f, EvnXml_id:options.EvnXml_id, name: o});
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
	viewFormDataStore: Ext.create('Ext.data.SimpleStore',
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
							record = Ext.create('Ext.data.Record',item_arr[i].xml_data);
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
				sw.swMsg.alert('Ошибка', 'Ошибка загрузки формы просмотра. <br/>Не передана нода или нода имеет неправильные параметры.');
				log(node);
				return false;
			}
			if (this.isForbiddenCode(node.attributes.object))
			{
				sw.swMsg.alert('Сообщение', 'Для данного объекта отсутствует шаблон отображения');
				return false;
			}

			if ((typeof form.Person_id == 'undefined') || (typeof form.PersonEvn_id == 'undefined') || (typeof form.Server_id == 'undefined') || (!form.Person_Surname) || (!form.Person_Birthday))
			{
				sw.swMsg.alert('Ошибка', 'Ошибка загрузки формы просмотра. <br/>Не указаны параметры человека, необходимые для правильной работы формы просмотра.');
				return false;
			}
		}
		var data_node = this.getDataNode(node);
		if (!data_node)
		{
			sw.swMsg.alert('Ошибка', 'Ошибка загрузки формы просмотра. <br/>Не удалось получить параметры ноды.');
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
			msg: 'Пожалуйста, подождите, идет загрузка формы просмотра...',
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
			}.bind(this),
			onError: function(form){
				this.clearNodeViewForm();
				sw.swMsg.alert('Ошибка', 'Ошибка загрузки формы просмотра. <br/>Не найдена форма отображения события.');
				if(form.id == 'PersonEmkForm')
				{
					if (!form.Tree.getSelectionModel().isSelected(node))
					{
						form.Tree.getSelectionModel().select(node);
					}
					form.savePosition();
				}
			}.bind(this)
		},parent_attr);
	},
	loadNodeViewSection: function(option_obj,parentnode_attr)
	{
		var params = {
			user_MedStaffFact_id: this.ownerWindow.userMedStaffFact.MedStaffFact_id,
			scroll_value: null,
			object: option_obj.Code,
			object_id: option_obj.object_key,
			object_value: option_obj.object_value
		};
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
			case 'FreeDocument':
				params.object = 'FreeDocumentView';
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
			param_name: params.param_name || null,
			param_value: params.param_value || null,
			accessType: params.accessType || null,
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
				if (params.callback == 'function') {
					params.callback();
				}
			}.bind(this),
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
			Ext.Msg.alert('Сообщение', 'Секция '+ id +' не найдена.');
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

/** Проверка, создано ли экстренное извещение об инфекционном заболевании, отравлении (ф. №058/У)
 *  @conf - параметры
 */
function requestEvnInfectNotify(conf) {

	return false;//отключаем функционал

	if (!conf || !conf.EvnInfectNotify_pid)
	{
		return false;
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
					if (!result.success)
					{
						sw.swMsg.show(
						{
							buttons: Ext.Msg.YESNO,
							fn: function( buttonId )
							{
								if ( buttonId == 'yes' )
									getWnd('swEvnInfectNotifyEditWindow').show({formParams: conf});
							},
							msg: 'Создать «Экстренное извещение об инфекционном заболевании, отравлении» (ф. №058/У)?',
							title: 'Вопрос'
						});
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
	if (!conf || !conf.EvnOnkoNotify_pid || !conf.Morbus_id)
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
					(conf.TumorStage_id >= 13 && conf.TumorStage_id <= 16) ||
					((conf.TumorStage_id >= 9 && conf.TumorStage_id <= 12) && ( diag_code.inlist(diag_code_check)) || conf.Diag_Code == 'C63.2')
				)
			{
				sw.swMsg.show(
				{
					buttons: Ext.Msg.YESNO,
					fn: function( buttonId )
					{
						if ( buttonId == 'yes' ) {
							if( !Ext.isEmpty(conf.Alert_Msg) ) {
								sw.swMsg.alert('Сообщение', conf.Alert_Msg);
							} else {
								getWnd('swEvnOnkoNotifyNeglectedEditWindow').show({formParams: conf});
							}
						}
					},
					msg: 'У пациента выявлена запущенная форма злокачественного новообразования. Создать Протокол?',
					title: 'Вопрос'
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
				,Morbus_id: conf.Morbus_id
				,MorbusType_id: conf.MorbusType_id
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
			}
		};
		sw.swMsg.show(
		{
			buttons: Ext.Msg.YESNO,
			fn: function( buttonId )
			{
				if ( buttonId == 'yes' ) {
					if( !Ext.isEmpty(conf.Alert_Msg) ) {
						sw.swMsg.alert('Сообщение', conf.Alert_Msg);
					} else {
						getWnd('swEvnOnkoNotifyEditWindow').show(params);
					}
				}
			},
			msg: 'Создать Извещение о больном с впервые в жизни установленным диагнозом злокачественного новообразования?',
			title: 'Вопрос'
		});
	};
	if( Ext.isEmpty(conf.EvnOnkoNotify_id) && Ext.isEmpty(conf.PersonRegister_id) )
	{
		createEvnOnkoNotify(conf);
	}
	if ( !Ext.isEmpty(conf.EvnOnkoNotify_id) )
	{
		checkEvnOnkoNotifyNeglected(conf);
	}
}

/** Создание/проверка Извещение об орфанном заболевании
 *  @conf - параметры
 */
function checkEvnNotifyOrphan(conf) {
	log(conf);
	if (!conf || !conf.EvnNotifyOrphan_pid)
	{
		return false;
	}
	var createNotify = function(conf) {
		var params = {
			EvnNotifyOrphan_id: null
			,formParams: {
				EvnNotifyOrphan_id: null
				,EvnNotifyOrphan_pid: conf.EvnNotifyOrphan_pid
				,Morbus_id: conf.Morbus_id
				,MorbusType_id: conf.MorbusType_id
				,Server_id: conf.Server_id
				,PersonEvn_id: conf.PersonEvn_id
				,Person_id: conf.Person_id
				,Diag_Name: conf.Diag_Name
				,Lpu_oid: null
				,EvnNotifyOrphan_setDT: conf.EvnNotifyOrphan_setDT || getGlobalOptions().date
				,MedPersonal_id: conf.MedPersonal_id
			}
			,callback: function (data) {
				conf.EvnNotifyOrphan_id = data.EvnNotifyOrphan_id;
			}
		};
		sw.swMsg.show(
		{
			buttons: Ext.Msg.YESNO,
			fn: function( buttonId )
			{
				if ( buttonId == 'yes' ) {
					if( !Ext.isEmpty(conf.Alert_Msg) ) {
						sw.swMsg.alert('Сообщение', conf.Alert_Msg);
					} else {
						getWnd('swEvnNotifyOrphanEditWindow').show(params);
					}
				}
			},
			msg: 'Создать Направление на включение в регистр по орфанным заболеваниям? ',
			title: 'Вопрос'
		});
	};
	if( Ext.isEmpty(conf.EvnNotifyOrphan_id) && Ext.isEmpty(conf.PersonRegister_id))
	{
		createNotify(conf);
	}
}

/** Создание/проверка Извещение об психиатрии
 *  @conf - параметры
 */
function checkEvnNotifyCrazy(conf) {
	log(conf);
	if (!conf || !conf.EvnNotifyCrazy_pid)
	{
		return false;
	}
	var createNotify = function(conf) {
		var params = {
			EvnNotifyCrazy_id: null
			,formParams: {
				EvnNotifyCrazy_id: null
				,EvnNotifyCrazy_pid: conf.EvnNotifyCrazy_pid
				,Morbus_id: conf.Morbus_id
				,MorbusType_id: conf.MorbusType_id
				,Server_id: conf.Server_id
				,PersonEvn_id: conf.PersonEvn_id
				,Person_id: conf.Person_id
				,Diag_Name: conf.Diag_Name
				,EvnNotifyCrazy_setDT: conf.EvnNotifyCrazy_setDT || getGlobalOptions().date
				,MedPersonal_id: conf.MedPersonal_id
			}
			,callback: function (data) {
				conf.EvnNotifyCrazy_id = data.EvnNotifyCrazy_id;
			}
		};
		var msg = 'Создать Извещение о больном психическим заболеванием?'

		if (conf.Diag_Code.substr(0, 2) == 'F1') {
			var msg = 'Создать Извещение о больном наркологическим заболеванием?';
			params.type = 'narko';
		}
		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function( buttonId )
			{
				if ( buttonId == 'yes' ) {
					if( !Ext.isEmpty(conf.Alert_Msg) ) {
						sw.swMsg.alert('Сообщение', conf.Alert_Msg);
					} else {
						getWnd('swEvnNotifyCrazyEditWindow').show(params);
					}
				}
			},
			msg: msg,
			title: 'Вопрос'
		});
	};
	if( Ext.isEmpty(conf.EvnNotifyCrazy_id) && Ext.isEmpty(conf.PersonRegister_id))
	{
		createNotify(conf);
	}
}

/** Создание/проверка наличия извещения и записи регистра с логикой автоматического включения в регистр при создании извещения
 *  @conf - параметры
 */
function checkEvnNotifyBaseWithAutoIncludeInPersonRegister(conf) {
	if (!conf || !conf.Evn_id || !conf.MorbusType_id || !conf.MorbusType_id.toString().inlist(['5','7','8','9'])) {
		return false;
	}
	conf.MedPersonal_id = getGlobalOptions().medpersonal_id;
	conf.EvnNotifyBase_setDT = getGlobalOptions().date;
	conf.allowAutoIncludeInPersonRegister = true;

	var createNotify = function(conf) {
		var params = {
			formParams: {
				Morbus_id: conf.Morbus_id
				,MorbusType_id: conf.MorbusType_id
				,Server_id: conf.Server_id
				,PersonEvn_id: conf.PersonEvn_id
				,Person_id: conf.Person_id
				,Diag_Name: conf.Diag_Name
				,Diag_id: conf.Diag_id
				,MedPersonal_id: conf.MedPersonal_id
			}
			,callback: function (data) {
				if (conf.allowAutoIncludeInPersonRegister) {
					sw.Promed.personRegister.include({
						Morbus_id: data.Morbus_id
						,MorbusType_id: data.MorbusType_id
						,EvnNotifyBase_id: data.EvnNotifyBase_id
						,Person_id: conf.Person_id
						,Diag_id: conf.Diag_id
						,ownerWindow: Ext.getCmp('PersonEmkForm')
						,disableQuestion: true
						,Mode: conf.Mode || null
						,callback: function() {
							showSysMsg('Пациент включен в регистр');
						}
					});
				}
			}
		};
		switch (parseInt(conf.MorbusType_id)) {
			case 5: //Создание извещения о больном вирусным гепатитом
				params.formParams.EvnNotifyHepatitis_id = null;
				params.formParams.EvnNotifyHepatitis_pid = conf.Evn_id;
				params.formParams.EvnNotifyHepatitis_setDT = conf.EvnNotifyBase_setDT;
				Ext.Ajax.request({
					failure:function (response, options) {
						sw.swMsg.alert('Ошибка', 'Не удалось получить данные с сервера');
					},
					params: params.formParams,
					success:function (response, options) {
						var result  = Ext.util.JSON.decode(response.responseText);
						if(result && result.Morbus_id && result.MorbusType_id && result.EvnNotifyBase_id) {
							showSysMsg('Создано извещение о больном вирусным гепатитом');
							params.callback(result);
						}
					},
					url:'/?c=EvnNotifyHepatitis&m=save'
				});
				break;
			case 7: //Создание извещения о больном туберкулезом
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
						msg: 'Создать Извещение о больном туберкулезом?',
						title: 'Вопрос'
					});
				}
				break;
			case 8: //Создание извещения с типом "Венерология".
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
						msg: 'Создать Извещение о больном венерическим заболеванием?',
						title: 'Вопрос'
					});
				}
				break;
			case 9: //Создание извещения о ВИЧ-инфицированном
				params.EvnNotifyHIV_id = null;
				params.formParams.EvnNotifyHIV_id = null;
				params.formParams.EvnNotifyHIV_pid = conf.Evn_id;
				params.formParams.EvnNotifyHIV_setDT = conf.EvnNotifyBase_setDT;
				function createEvnNotifyHIV(params) {
					Ext.Ajax.request({
						failure:function (response, options) {
							sw.swMsg.alert('Ошибка', 'Не удалось получить данные с сервера');
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
						msg: 'Создать ОПЕРАТИВНОЕ ДОНЕСЕНИЕ о лице, в крови которого при исследовании в реакции иммуноблота выявлены антитела к ВИЧ (форма N 266/У-88)?',
						title: 'Вопрос'
					});
				}
				break;
		}
	};

	var returnToTheRegistry = function(result) {
		var buttons = {
			yes: (parseInt(result.PersonRegisterOutCause_id) == 3) ? 'Новое' : 'Да',
			no: (parseInt(result.PersonRegisterOutCause_id) == 3) ? 'Предыдущее' : 'Нет'
		};
		if (parseInt(result.PersonRegisterOutCause_id) == 3) {
			buttons.cancel = 'Отмена';
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
							,EvnNotifyBase_id: result.EvnNotifyBase_id
							,Diag_id: result.Diag_id
							,Morbus_id: result.Morbus_id
							,ownerWindow: Ext.getCmp('PersonEmkForm')
							,callback: function() {
								showSysMsg('Пациент возвращен в регистр');
							}
						});
					}
					else
					{
						// Новое заболевание: создать Извещение/Запись регистра
						conf.Morbus_id = null;
						conf.Mode = mode;
						conf.disableQuestion = true;
						conf.EvnNotifyTub_IsFirstDiag = 1;
						createNotify(conf);
					}
				}
			},
			msg: result.Alert_Msg,
			title: 'Вопрос'
		});
	};

				/*
				else if (1 == conf.disableAutoInclude) {

				}
function checkEvnNotifyVener(conf) {
	if (!conf || !conf.EvnNotifyVener_pid)
	{
		return false;
	}
	var createNotify = function(conf) {
	};
}
				*/

	if (8 == parseInt(conf.MorbusType_id)) {
		// для венерологии дополнительная логика
		if (!conf.disableAutoInclude) {
			return false;
		}
		if (2 == conf.disableAutoInclude) {
			/*
			Для диагнозов В35.0-В35.9 и В86 запись регистра с типом "Венерология" не создавать.
			Включение в регистр производит оператор, т.е. в БД может быть извещение без записи регистра
			*/
			conf.allowAutoIncludeInPersonRegister = false;
			if (
				(!Ext.isEmpty(conf.EvnNotifyVener_id) || !Ext.isEmpty(conf.PersonRegister_id)) &&
				Ext.isEmpty(conf.PersonRegisterOutCause_id)
			) {
				//Если уже есть извещение или запись регистра с открытым заболеванием, то новое извещение не создавать.
				return true;
			}
			if ( !Ext.isEmpty(conf.PersonRegister_id) && 1 == conf.PersonRegisterOutCause_id )
			{
				sw.swMsg.alert('Сообщение', 'Пациент был исключен из регистра по причине "Смерть", повторное создание извещения невозможно.');
				return true;
			}
			if(
				Ext.isEmpty(conf.EvnNotifyVener_id) ||
				(!Ext.isEmpty(conf.PersonRegister_id) && !Ext.isEmpty(conf.PersonRegisterOutCause_id))
			) {
				//Если нет извещения или есть запись регистра с закрытым заболеванием, то создавать новое извещение.
				createNotify(conf);
				return true;
			}
			sw.swMsg.alert('Сообщение', 'Непредвиденная ситуация, создание извещения невозможно.');
			return true;
		}
		//для остальных венерозаболеваний логика проверки такая же как для гепатита, ВИЧ, туберкулеза
		conf.allowAutoIncludeInPersonRegister = true;
	}

	if ( Ext.isEmpty(conf.PersonRegister_id) )
	{
		//Если нет извещения/записи регистра, то создавать новое извещение и новую запись регистра.
		createNotify(conf);
		return true;
	}
	else
	{
		if ( !Ext.isEmpty(conf.EvnNotifyBase_id) && Ext.isEmpty(conf.PersonRegisterOutCause_id))
		{
			//Если уже есть извещение и запись регистра с открытым заболеванием, то новое извещение не создавать.
			return true;
		}
		if ( 1 == conf.PersonRegisterOutCause_id )
		{
			sw.swMsg.alert('Сообщение', 'Пациент был исключен из регистра по причине "Смерть", повторное включение в регистр невозможно.');
			return true;
		}
		if ( 2 == conf.PersonRegisterOutCause_id )
		{
			conf.Alert_Msg = 'Пациент был исключен из регистра по причине "Выехал". Вернуть пациента в регистр?';
			conf.Yes_Mode = 'homecoming'; // Извещение/Запись регистра не создавать, восстановить запись в регистре, удалить дату закрытия заболевания
			conf.No_Mode = false; // Извещение/Запись регистра не создавать
			returnToTheRegistry(conf);
			return true;
		}
		if ( 3 == conf.PersonRegisterOutCause_id )
		{
			conf.Alert_Msg = 'Пациент был исключен из регистра по причине "Выздоровление". У пациента новое заболевание?';
			conf.Yes_Mode = 'new'; // При нажатии "Новое" создать Извещение/Запись регистра
			conf.No_Mode = 'relapse'; // При нажатии "Предыдущее" Извещение/Запись регистра не создавать, восстановить запись в регистре, удалить дату закрытия заболевания
			returnToTheRegistry(conf);
			return true;
		}
	}
	return false;
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
				,MorbusType_id: conf.MorbusType_id
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
		sw.swMsg.show(
			{
				buttons: Ext.Msg.YESNO,
				fn: function( buttonId )
				{
					if ( buttonId == 'yes' ) {
						if( !Ext.isEmpty(conf.Alert_Msg) ) {
							sw.swMsg.alert('Сообщение', conf.Alert_Msg);
						} else {
							getWnd('swEvnNotifyHIVPregEditWindow').show(params);
						}
					}
				},
				msg: 'Создать Извещение о случае завершения беременности у ВИЧ-инфицированной женщины (форма N  313/у)?',
				title: 'Вопрос'
			});
	};
	if( true /* Ext.isEmpty(conf.EvnNotifyHIVPreg_id)*/ )
	{
		createNotify(conf);
	}
}

/** Набор методов для PersonRegister
 *
 */
sw.Promed.personRegister = {
	PersonRegisterFailIncludeCauseStore: null,
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
			sw.swMsg.alert('Ошибка', 'Создание списка причин не включения в регистр. Отсутствуют обязательные параметры');
		}
		if(!this.PersonRegisterFailIncludeCauseStore) {
			this.PersonRegisterFailIncludeCauseStore = Ext.create('Ext.db.AdapterStore',{
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
				var menu = Ext.create('Ext.menu.Menu',{id: option.id || 'menuPersonRegisterFailIncludeCause'});
				this.PersonRegisterFailIncludeCauseStore.each(function(record) {
					menu.add({text: record.get('PersonRegisterFailIncludeCause_Name'), PersonRegisterFailIncludeCause_Code: record.get('PersonRegisterFailIncludeCause_Code'), PersonRegisterFailIncludeCause_id:record.get('PersonRegisterFailIncludeCause_id'), iconCls: '', handler: function() {
						var params = option.getParams();
						if (typeof params == 'object') {
							option.PersonRegisterFailIncludeCause_id = this.PersonRegisterFailIncludeCause_id;
							option.EvnNotifyBase_id = params.EvnNotifyBase_id;
							sw.Promed.personRegister.notInclude(option);
						}
					}});
				});
				option.onCreate(menu);
			}.bind(this)
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
			sw.swMsg.alert('Ошибка', 'Не включать в регистр. Отсутствуют обязательные параметры');
		}
		var loadMask = new Ext.LoadMask(conf.ownerWindow.getEl(), {msg: "Подождите, идет сохранение..."});
		var params = {
			EvnNotifyBase_id: conf.EvnNotifyBase_id
			,PersonRegisterFailIncludeCause_id: conf.PersonRegisterFailIncludeCause_id
			,Lpu_niid: getGlobalOptions().lpu_id
			,MedPersonal_niid: getGlobalOptions().medpersonal_id
		};
		loadMask.show();
		Ext.Ajax.request({
			failure:function (response, options) {
				loadMask.hide();
				sw.swMsg.alert('Ошибка', 'Не удалось получить данные с сервера');
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
	 * conf.MorbusType_id -
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
			Ext.isEmpty(conf.Person_id)
		) {
			sw.swMsg.alert('Ошибка', 'Отсутствуют обязательные параметры');
		}
		if(!conf.question) {
			conf.question = 'Включить данные по выбранному Извещению в регистр?';
		}
		var includeRequest = function(conf) {
			var loadMask = new Ext.LoadMask(conf.ownerWindow.getEl(), {msg: "Подождите, идет сохранение..."});
			var params = {
				PersonRegister_id: null
				,EvnNotifyBase_id: conf.EvnNotifyBase_id
				,Person_id: conf.Person_id
				,Diag_id: conf.Diag_id
				,MorbusType_id: conf.MorbusType_id || null
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
					sw.swMsg.alert('Ошибка', 'Не удалось получить данные с сервера');
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
								yes: (parseInt(result.PersonRegisterOutCause_id) == 3) ? 'Новое' : 'Да',
								no: (parseInt(result.PersonRegisterOutCause_id) == 3) ? 'Предыдущее' : 'Нет'
							};
							if (parseInt(result.PersonRegisterOutCause_id) == 3) {
								buttons.cancel = 'Отмена';
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
								title: 'Вопрос'
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
				title: 'Вопрос'
			});
		}
	},
	/** Добавить в регистр
	 *
	 * @param {Object} conf
	 * conf.person
	 * conf.MorbusType_id -
	 * conf.Morbus_id -
	 * conf.EvnNotifyBase_id -
	 * conf.Diag_id -
	 * conf.callback
	 * @return {void}
	 */
	add: function(conf) {
		if(
			typeof conf != 'object' ||
			Ext.isEmpty(conf.MorbusType_id)
		) {
			sw.swMsg.alert('Ошибка', 'Отсутствуют обязательные параметры');
		}

		if( typeof conf.person != 'object' || Ext.isEmpty(conf.person)) {
			if ( getWnd('swPersonSearchWindow').isVisible() ) {
				getWnd('swPersonSearchWindow').hide();
			}
			getWnd('swPersonSearchWindow').show({
				onSelect: function(person_data) {
					getWnd('swPersonSearchWindow').hide();
					conf.person = person_data;
					sw.Promed.personRegister.add(conf);
				},
				searchMode: conf.searchMode || 'all'
			});
			return false;
		}

		if (getWnd('swPersonRegisterIncludeWindow').isVisible()) {
			getWnd('swPersonRegisterIncludeWindow').hide();
		}

		getWnd('swPersonRegisterIncludeWindow').show({
			PersonRegister_id: null
			,MorbusType_id: conf.MorbusType_id
			,Person_id: conf.person.Person_id
			,Lpu_iid: getGlobalOptions().lpu_id
			,MedPersonal_iid: getGlobalOptions().medpersonal_id
			,PersonRegister_setDate: getGlobalOptions().date
			,callback: conf.callback || Ext.emptyFn
			,Morbus_id: conf.Morbus_id || null
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
	 * @return {void}
	 */
	out: function(conf) {
		if(
			typeof conf != 'object' ||
			Ext.isEmpty(conf.PersonRegister_id) ||
			Ext.isEmpty(conf.Person_id) ||
			Ext.isEmpty(conf.PersonRegister_setDate) ||
			Ext.isEmpty(conf.Diag_Name)
		) {
			sw.swMsg.alert('Ошибка', 'Отсутствуют обязательные параметры');
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
			,MorbusType_id: conf.MorbusType_id || null
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
			sw.swMsg.alert('Ошибка', 'Отсутствуют обязательные параметры');
		}

		var loadMask = new Ext.LoadMask(conf.ownerWindow.getEl(), {msg: "Подождите, идет сохранение..."});
		var params = {
			PersonRegister_id: conf.PersonRegister_id
			,EvnNotifyBase_id: conf.EvnNotifyBase_id || null
			,Diag_id: conf.Diag_id || null
			,Morbus_id: conf.Morbus_id || null
		};
		loadMask.show();
		Ext.Ajax.request({
			failure:function (response, options) {
				loadMask.hide();
				showSysMsg('Не удалось получить данные с сервера');
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
		error.push('Длина штрих-кода должна быть равна 13 символам. (введено ' + barcode.length + ')');
	}
	if (error.length == 0) {
		var checksum = Ean13CalculateChecksum(barcode.substr(0, 12));
		if (typeof(checksum) == 'boolean') {
			error.push('Ошибка расчета контрольной суммы');
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
	return new RegExp("(805|811|835)$").test(uslugacomplex_code);
}
/**
 * Проверка, является ли указанный код комплексной услуги кодом посещения по заболеванию
 */
function isMorbusVizitOnly(uslugacomplex_code) {
	return new RegExp("(865|866|871|836)$").test(uslugacomplex_code);
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
		sw.Promed.LpuSectionBedProfile.getLpuSectionBedProfilesByLpuSection({
			LpuSection_id: option.LpuSection_id,
			callback: function(result) {
				var menu = Ext.create('Ext.menu.Menu',{id: (option.id || 'menuLpuSectionBedProfile')+option.LpuSection_id});
				/* на случай если надо будет сделать удаление профиля коек
				menu.add({text: 'Без профиля койки ', LpuSectionBedProfile_id: '0', handler: function() {
					sw.Promed.LpuSectionBedProfile.setLpuSectionBedProfile({
						LpuSectionBedProfile_id: '0',
						onSuccess: option.onSuccess,
						params: option.getParams()
					});
				}});
				*/
				if (result) {
					for(var i = 0;i < result.length;i++) {
						menu.add({text: result[i]['LpuSectionBedProfile_Name'], LpuSectionBedProfile_id: result[i]['LpuSectionBedProfile_id'], handler: function() {
							sw.Promed.LpuSectionBedProfile.setLpuSectionBedProfile({
								LpuSectionBedProfile_id: this.LpuSectionBedProfile_id,
								onSuccess: option.onSuccess,
								params: option.getParams()
							});
						}});
					}
					option.callback(menu);
				}
			}
		});
		return true;
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
						sw.swMsg.alert('Ошибка', 'При загрузке профилей коек отделения произошли ошибки');
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
	/** Запись профиля койки
	 *
	 * option.LpuSectionBedProfile_id - выбранный профиль койки
	 * option.params - параметры (LpuSectionBedProfileCur_id,LpuSection_id,EvnSection_id,PersonEvn_id,Server_id)
	 * option.onSuccess - функция, которая выполняется при успешной записи профиля койки
	 * @param {Object} option
	 * @return {void}
	 */
	setLpuSectionBedProfile: function(option) {
		if (option.params.LpuSectionBedProfileCur_id!=option.LpuSectionBedProfile_id) { // Если профиль койки, в которой находится пациент, отличается от выбранного профиля койки
			var params = {};
			params.EvnSection_id = option.params.EvnSection_id;
			params.LpuSection_id = option.params.LpuSection_id;
			params.PersonEvn_id = option.params.PersonEvn_id;
			params.Server_id = option.params.Server_id;
			params.LpuSectionBedProfile_id = option.LpuSectionBedProfile_id;
			params.LpuSectionBedProfileLink_fedid = option.LpuSectionBedProfileLink_id;
			Ext.Ajax.request({
				url: '/?c=EvnSection&m=setLpuSectionBedProfile',
				params: params,
				failure: function(response, options) {
					Ext.Msg.alert('Ошибка', 'При записи профиля койки произошла ошибка!');
				},
				success: function(response, action)
				{
					if (response.responseText) {
						var answer = Ext.util.JSON.decode(response.responseText);
						if (answer.success) {
							if(typeof option.onSuccess == 'function')
								option.onSuccess(params);
						} else if (answer.Error_Message) {
							Ext.Msg.alert('Ошибка', answer.Error_Message);
						}
					}
					else {
						Ext.Msg.alert('Ошибка', 'При записи профиля койки произошла ошибка! Отсутствует ответ сервера.');
					}
				}
			});
		}
	}
};

/**
 * Класс хранилища локального справочника общего вида
 * @param string config.tableName Имя таблицы локального справочника
 * @param string config.typeCode 'int' or 'string'
 * @param boolean config.allowSysNick
 * @param boolean config.autoLoad
 * @param function config.onLoadStore
 * @param string config.orderBy
 * @param string config.prefix
 * @param {object} config.loadParams
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
    this._store = Ext.create('Ext.db.AdapterStore',{
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
     * @return {sw.Promed.LocalStorage}
     */
    this.getCount = function(){
        return thas._store.getCount();
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

	if (!opts.NODE_ENABLED) {
		log('NODEJS DISABLED MANUALLY');
		return false;
	}
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
		cmp.socket.on('logout', function(){z
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

function connectPortalProxy(/* callback */){

	var opts = getGlobalOptions();

	if (!opts || !opts.nodePortalConnectionHost ) {
		log('No socket connection host');
		return false;
	}

	var socket = io(opts.nodePortalConnectionHost);

	socket.on('connect', function () {
		console.log('connect');
		socket.on('message', function (message) {
			/*callback(message);*/
			console.log({message: message});
		});
	});
}

function portalProxyTest() {
	Ext.Ajax.request({
		// params: {
		// 	addresses: Ext.encode(dirty_records)
		// },
		url: '/?c=CmpCallCard4E&m=portalProxyTest',
		callback: function(options, success, response) {
			if(success) {
				console.log('success');
			}else{
				Ext.Msg.alert('Ошибка');
			}
		},
		failure: function(){
			Ext.Msg.alert('Ошибка','failure');
		}
	});
}

sw.Promed.XmlTemplateCatDefault = {
    /**
     * Получение идентификатора папки по умолчанию c массивом его папок верхнего уровня
     * @param {Object} params
     * @param {Function} callback
     * @param {sw.Promed.BaseForm} scope
     * @return {Boolean}
     */
    loadId: function(params, callback, scope) {
		log(params);
        if ((!params.MedStaffFact_id && !params.MedPersonal_id && !params.MedService_id && !params.LpuSection_id)|| !params.EvnClass_id || !params.XmlType_id) {
            callback.call(scope, false, 'Недостаточно параметров для получения папки по умолчанию', []);
            return false;
        }
        if (!params.LpuSection_id) {
            params.LpuSection_id = getGlobalOptions().CurLpuSection_id || null;
        }
        // шлем запрос
		var loadMask =  new Ext.LoadMask(scope, {msg:"Подождите, идет получение папки по умолчанию..."});
        loadMask.show();
        Ext.Ajax.request({
            url: '/?c=XmlTemplateCatDefault&m=getPath',
            callback: function(o, s, response)
            {
                loadMask.hide();
                if ( s ) {
                    var result = Ext.JSON.decode(response.responseText);
                    if (result.length > 0) {
                        callback.call(scope, true, result[0].XmlTemplateCat_id, result);
                    } else {
                        callback.call(scope, true, null, []);
                    }
                } else {
                    callback.call(scope, false, 'Ошибка при выполнении запроса получения папки по умолчанию.', []);
                }
            },
            params: params
        });
        return true;
    },
    /**
     * Сохранение папки по умолчанию
     * @param {Object} params
     * @param {Function} callback
     * @param {sw.Promed.BaseForm} scope
     * @return {Boolean}
     */
    save: function(params, callback, scope) {
        if (!params.XmlTemplateCat_id || (!params.MedStaffFact_id && !params.MedPersonal_id && !params.MedService_id && !params.LpuSection_id) || !params.EvnClass_id || !params.XmlType_id) {
            callback.call(scope, false, 'Недостаточно параметров для сохранения папки по умолчанию');
            return false;
        }
		var loadMask =  new Ext.LoadMask(scope, {msg:"Подождите, идет сохранение папки по умолчанию..."});
        loadMask.show();
        Ext.Ajax.request({
            url: '/?c=XmlTemplateCatDefault&m=save',
            callback: function(options, success, response)
            {
                loadMask.hide();
                if ( success ) {
                    var result = Ext.JSON.decode(response.responseText);
                    if (result['success']) {
                        callback.call(scope, true, result['XmlTemplateCatDefault_id']);
                    } else {
                        callback.call(scope, false, result['Error_Msg']);
                    }
                } else {
                    callback.call(scope, false, 'Ошибка при выполнении запроса сохранения папки по умолчанию.');
                }
            },
            params: params
        });
        return true;
    },
    /**
     * Создание папки по умолчанию
     * @param {Object} params
     * @param {Function} callback
     * @param {sw.Promed.BaseForm} scope
     * @return {Boolean}
     */
    create: function(params, callback, scope) {
        if ((!params.MedStaffFact_id && !params.MedPersonal_id && !params.MedService_id && !params.LpuSection_id) || !params.EvnClass_id || !params.XmlType_id) {
            callback.call(scope, false, 'Недостаточно параметров для создания папки по умолчанию', []);
            return false;
        }
		var loadMask =  new Ext.LoadMask(scope, {msg:"Подождите, идет создание папки по умолчанию..."});
        var self = this;
        loadMask.show();
        Ext.Ajax.request({
            url: '/?c=XmlTemplateCat&m=createDefault',
            callback: function(options, success, response)
            {
                loadMask.hide();
                if ( success ) {
                    var result = Ext.JSON.decode(response.responseText);
                    if (result['success']) {
                        callback.call(scope, true, result['XmlTemplateCat_id'], [result]);
                    } else {
                        callback.call(scope, false, result['Error_Msg'], []);
                    }
                } else {
                    callback.call(scope, false, 'Ошибка при выполнении запроса создания папки по умолчанию.', []);
                }
            },
            params: params
        });
        return true;
    }
};


function getRegionNick() {
	if ( getGlobalOptions().region && getGlobalOptions().region.nick ) {
		return getGlobalOptions().region.nick;
	}

	return '';
}

function getCurrencyName(){
	if(getRegionNick() == 'kz'){
		return 'тенге'
	}
	return 'руб.'
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
					var result = Ext.JSON.decode(response.responseText);
					if (result && result.pong) {
						// есть свзяь
						log('есть связь с основным сервером');
						// если связи не было и она появилась, значит надо проверить ещё и очередь ActiveMQ (ушли ли все записи на основной сервер), затем переключиться на основной сервер
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

function openSelectSMPStationsToControlWindow() {
	Ext.create('sw.selectStationsToControlWindow', {
		autoShow: true
	});
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

//функция для отмены загрузки стора
function stopLoadingStore(store){

	if (store.loading ) {
		var requests = Ext.Ajax.requests;
		for (var id in requests)
			if (requests[id].options.proxy && requests[id].options.proxy.url == store.proxy.url) {
				Ext.Ajax.abort(requests[id]);
			}
	}
}