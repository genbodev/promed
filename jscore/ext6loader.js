/**
 * Загрузчик сервернх настроек, справочников и еще разных настроек
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Init
 * @access       public
 * @copyright    Copyright (c) 2009-2011 Swan Ltd.
 * @author       Markoff Andrew <markov@swan.perm.ru>
 *               Pshenicyn Ivan aka IVP (ipshon@rambler.ru)
 *               Bykov Stas aka Savage (savage1981@gmail.com)
 * @version      18.06.2009
 */

 // Алгоритм такой 
 // Подгружаем с сервера все настройки + версию базы данных 
 // Проверяем изменилась ли версия 
 // Если версия изменилась, то получаем список таблиц которые поменялись 
 // Обновляем все эти таблицы 
 
 
var progressstep = 0;
var loadsuccess = true;
var localVer = null;

function loadPromed(load_callback) {
	var loadName = '';
	// функция загрузки настроек
	Ext6.loadOptions = function(firstLoad) {
		controlStoreRequest = Ext6.Ajax.request({
			url: C_OPTIONS_LOAD,
			success: function(result){
				Ext6.globalOptions = Ext6.util.JSON.decode(result.responseText);
				// Если при получении глобальных настроек вернулась ошибка, то выводим еще и выходим из Промеда
				if  (Ext6.globalOptions && (Ext6.globalOptions.success!=undefined) && Ext6.globalOptions.success === false) {
					Ext6.Msg.alert('Ошибка при загрузке настроек', 'При загрузке настроек с сервера произошла ошибка.<br/><b>'+((Ext6.globalOptions.Error_Msg)?Ext6.globalOptions.Error_Msg:'')+'</b><br/>Выйдите из системы и зайдите снова.',
					function () {
						window.onbeforeunload = null;
						window.location = C_LOGOUT;
					});
					
					return true;
				}
				
				// Если первая загрузка, то загружаем справочники
				if (firstLoad) {
					// Определяем, какое хранилище используется для хранения "локальных" данных
					setDBDriver();

					// версия с сервера
					var serverVer = getGlobalOptions().localDBVersion;
					localVer = serverVer;
					log('Версия локальной БД с сервера: '+serverVer);

					if (Ext6.isRemoteDB) {
						loadName = 'remotedb6|comboboxes';
						// подгружаем модуль RemoteDB
						log('Для хранения локальных справочников используется хранилище вебсервера');
						getWnd(loadName).load({callback: function() {
							var RDDBDriver = Ext6.db.remoteDBDriver.getInstance();
							// для модуля хранилища на стороне вебсервера версия справочников не существенна,
							// т.к. обновлять справочники на стороне клиента не надо (в данном случае их тут нет).
							RDDBDriver.open();
							load_callback();
						}});
					} else {
						log('Загрузка локального хранилища не выполнена!');
						load_callback();
					}
				} else {
					// Подменяем данные с локального хранилища при перечитывании глобальных настроек 
					Ext6.setLocalOptions();
				}
			},
			failure: function(result){
				Ext6.Msg.alert('Ошибка при загрузке настроек', 'При загрузке настроек с сервера произошла ошибка. Выйдите из системы и зайдите снова.');
			},
			method: 'GET',
			timeout: 120000
		});
	}
	// изменение состояния прогресс-бара
	// сообщение о загрузке настроек не показываем, поскольку:
	// определение для монгоДБ берется из настроек, а справочники по монгоДБ не загружаются
	// и получается мигание этого окна с загрузкой настроек... которое в принципе никуда не упирается
	// load_progress.updateProgress(load_progress.value + progressstep, 'Загрузка настроек...');
	// загрузка настроек
	Ext6.loadOptions(true);
}