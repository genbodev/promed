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
// Функция для загрузки справочников
function getTableList(spr_list, spr_structure, spr_records, load_mask, wnd, load_callback)
{
	if (spr_list.length == 0) {
		//load_mask.updateProgress(1, 'Загрузка завершена');
		wnd.close();
		load_callback();
		if (Ext.isGears) {
			var localVerGears = new Ext.sql.Version();
			localVerGears.change(getGlobalOptions().localDBVersion);
		} else {
			if (Ext.isIndexedDb) {
				if (Ext.idb.IDBDriver.setVersionExists()) {
				// зачем это делать в Firefox 10.. там версия только при открытии меняется.
					Ext.idb.IDBDriver.setVersion((loadsuccess)?getGlobalOptions().localDBVersion:localVer, {
						after: function() {
							// TODO: Продумать что здесь будет.. вполне возможно что калбэк должен быть здесь 	
						},
						success: function() {
							// TODO: Продумать что здесь будет.. вполне возможно что калбэк должен быть здесь 	
						},
						failure: function(e) {
							// TODO: Продумать что здесь будет.. вполне возможно что калбэк должен быть здесь 
						}
					});
				}
			} else if (Ext.isWebSqlDB) {
				Ext.wsdb.WSDBDriver.setVersion((loadsuccess)?getGlobalOptions().localDBVersion:localVer, {
					success: function() {
						// TODO: Продумать что здесь будет.. вполне возможно что калбэк должен быть здесь 	
					},
					failure: function(e) {
						// TODO: Продумать что здесь будет.. вполне возможно что калбэк должен быть здесь 
					}
				});
			}
		}
		return false;
	}
	var obj          = spr_list.shift();
	var stringfields = spr_structure[obj];
	var arr         = stringfields;
	var key_id      = "";
	var currobject  = obj;
	load_mask.updateProgress(load_mask.value + progressstep, 'Загрузка справочника: ' + obj);
	// формирование параметров 
	var params = {};
	params['object'] = currobject;
	if (stringfields) {
		for (i = 0; i < arr.length; i++) {
			if (arr[i]['name'] != undefined) {
				if (i == 0) {
					key_id = arr[i]['name'];
				}
				params[arr[i]['name']] = '';
			}
		}
	}
	else {
		// Ошибка - справочник не описан в loader_init
		log('Справочник '+obj+' не загружен, поскольку неизвестна его структура (не описан в loader-init).');
		loadsuccess = false;
		getTableList(spr_list, spr_structure, spr_structure, load_mask, wnd, load_callback);
		return false;
	}
	
	var remoteTableStore = new Ext.data.JsonStore(
	{
		autoLoad: false,
		baseParams: params,
		currobject: currobject,
		fields: stringfields,
		fieldsParam: stringfields,
		key: key_id,
		key_id: key_id,
		url: C_GETOBJECTLIST
	});
	// TODO: В дальнейшем надо сделать все же загрузку через Ajax-запрос и переделать полностью создание и запись нового справочника в Gears
	/*
	Ext.Ajax.request({
		url: C_GETOBJECTLIST,
		params: params,
		key_id: key_id,
		callback: function(options, success, response) {
			if (success == true) {
				var records = Ext.util.JSON.decode(response.responseText);
				if (Ext.isGears) {
	*/
	remoteTableStore.load({
		callback: function(records, options, success) {
			if (success == true) {
				if (Ext.isGears) {
					// Сохранение в Gears-хранилище
					var data = new Ext.sql.SQLiteStore({
						dbFile: 'Promed.db',
						fields: this.fieldsParam,
						key: this.key_id,
						tableName: this.currobject
					});
					data.add(records);
					/*
					for (var i = 0; i < records.length; i++) {
						var table_name = records[i].SyncTable_Name;
						// удаляем таблицу из Gears-хранилища
						sw_exec_query_local_db('Promed.db', "drop table if exists " + table_name);
						spr_list.push(table_name);
					}
					*/
					getTableList(spr_list, spr_structure, records, load_mask, wnd, load_callback);
					
				} else if (Ext.isIndexedDb) {
					if (records && records.length>0 ) {
						// TODO: при ошибке версию надо менять обратно 
						Ext.idb.IDBDriver.add(currobject, records[0].store.reader.jsonData, {
							success: function(r) {
								//log(spr_list.length);
								//if (spr_list.length == 0) {
								//	wnd.close();
								//	load_callback();
									/**/
									//window.location.reload();
								//}
								//else {
								getTableList(spr_list, spr_structure, spr_records, load_mask, wnd, load_callback);
								//}
							}, 
							failure: function(e) {
								// TODO: Сообщение поправить для тех времен когда загрузка будет фоном 
								alert('При загрузке справочника '+currobject+' произошла ошибка!\n\r Пожалуйста, сообщите о данной ошибке разработчикам.');
								loadsuccess = false;
								getTableList(spr_list, spr_structure, spr_records, load_mask, wnd, load_callback);
							}
						});
					}
					else // Если записей НОЛЬ 
						getTableList(spr_list, spr_structure, spr_records, load_mask, wnd, load_callback);
				} else if (Ext.isWebSqlDB) {
					// Сохранение в WebSql-хранилище
					// Глюк: Если превышение размера локальной базы выпадает на создание таблицы - то после вопроса транзакция может не завершиться (Safari)
					var o = {name: currobject, key: this.key_id, fields: this.fieldsParam};
					Ext.wsdb.WSDBDriver.createStore(o, {
						success: function(r) {
							//console.warn(currobject);
						}, 
						failure: function(e) {
							// TODO: Сообщение поправить для тех времен когда загрузка будет фоном 
							console.warn('Таблица '+currobject+' не создана!');
						}
					});
					// Пустые записи не грузим, хотя по идее и создавать не надо
					if (records && records.length>0 ) {
						Ext.wsdb.WSDBDriver.add(currobject, records[0].store.reader.jsonData, {
							success: function(tx,r) {
								//log(spr_list.length);
								getTableList(spr_list, spr_structure, spr_records, load_mask, wnd, load_callback);
							}, 
							failure: function(tx,e) {
								// TODO: Сообщение поправить для тех времен когда загрузка будет фоном 
								alert('При загрузке справочника '+currobject+' произошла ошибка!\n\r Пожалуйста, сообщите о данной ошибке разработчикам.');
								log(e);
								loadsuccess = false;
								getTableList(spr_list, spr_structure, spr_records, load_mask, wnd, load_callback);
							}
						});
					}
					else // Если записей НОЛЬ 
						getTableList(spr_list, spr_structure, spr_records, load_mask, wnd, load_callback);
				}
			}
		},
		timeout: 600000
	});
}

function loadTables(wnd, localVer, load_progress, load_callback)
{
	load_progress.updateProgress(load_progress.value + 0.01, 'Проверка справочников...');
	var spr_list = [];
	Ext.Ajax.request({
		url: '/?c=SprLoader&m=getSyncTables',
		params: {
			version: localVer,                                                // тут передается текущая версия локальной базы
			module:  (getGlobalOptions().OrgFarmacy_id>0)?'farmacy':'promed'  // тут передается модуль для получения определенных таблиц 
		},
		callback: function(options, success, response) {
		
			var records = Ext.JSON.decode(response.responseText);
			
			if (success) {
				progressstep = Number(1/records.length);
				// Меняем версию на пришедшую с сервера 
				if (Ext.isGears) {
					for (var i = 0; i < records.length; i++) {
						var table_name = records[i].SyncTable_Name;
						// удаляем таблицу из Gears-хранилища
						sw_exec_query_local_db('Promed.db', "drop table if exists " + table_name);
						spr_list.push(table_name);
					}
					getTableList(spr_list, spr_structure, records, load_progress, wnd, load_callback);
				} else if (Ext.isIndexedDb) {
					// для того чтобы версия менялась правильно, если при загрузке произошли ошибки
					// если не Firefox 10
					if (Ext.idb.IDBDriver.setVersionExists()) {
						Ext.idb.IDBDriver.setVersion(getGlobalOptions().localDBVersion - 0.0001, {
						//Ext.idb.IDBDriver.setVersion(localVer + 0.0001, {
							success: function() {
								for (var i = 0; i < records.length; i++) {
									var table_name = records[i].SyncTable_Name;
									//var table_key = records[i].SyncTable_Key;
									Ext.idb.IDBDriver.deleteStore(table_name);
									/*Тест Diag*/
									/*
									if (table_name=='Diag') {
										var keys = {Diag_id:'Diag_id', Diag_Code:'Diag_Code'};
										var store = Ext.idb.IDBDriver.createStore(table_name, keys);
									} else 
									*/
									var store = Ext.idb.IDBDriver.createStore(table_name/*, table_key*/);
									//log('store: ', store);
									spr_list.push(table_name);
								}
							},
							after: function() {
								getTableList(spr_list, spr_structure, records, load_progress, wnd, load_callback);
							},
							failure: function(e) {
								// TODO: Продумать сообщение об ошибке 
								alert('Error');
								// getTableList(spr_list, spr_structure, records, load_progress, wnd, load_callback);
							}
						});
					// если Firefox 10
					} else {
						Ext.idb.IDBDriver.reopenVersioned(getGlobalOptions().localDBVersion, {
							upgrade: function() { // открылось в VERSION_CHANGE
								// под локальные настройки.
								if (!Ext.idb.IDBDriver.checkStoreExist('Storage')) {
									Ext.idb.IDBDriver.createStore('Storage');
								}
								
								for (var i = 0; i < records.length; i++) {
									var table_name = records[i].SyncTable_Name;
									Ext.idb.IDBDriver.deleteStore(table_name);
									var store = Ext.idb.IDBDriver.createStore(table_name/*, table_key*/);
									spr_list.push(table_name);
								}
							},
							success: function() { // открылось не в VERSION_CHANGE..
								// do nothing..
								getTableList(spr_list, spr_structure, records, load_progress, wnd, load_callback);
							},
							failure: function(e) {
								// TODO: Продумать сообщение об ошибке 
								alert('Error');
								// getTableList(spr_list, spr_structure, records, load_progress, wnd, load_callback);
							}
						});
					}
				} else if (Ext.isWebSqlDB) {
					// для того чтобы версия менялась правильно, если при загрузке произошли ошибки
					Ext.wsdb.WSDBDriver.setVersion(getGlobalOptions().localDBVersion - 0.0001, {
					//Ext.wsdb.WSDBDriver.setVersion(localVer + 0.0001, {
						success: function() {
							for (var i = 0; i < records.length; i++) {
								var table_name = records[i].SyncTable_Name;
								//var table_key = records[i].SyncTable_Key;
								Ext.wsdb.WSDBDriver.deleteStore(table_name);
								spr_list.push(table_name);
							}
							getTableList(spr_list, spr_structure, records, load_progress, wnd, load_callback);
						},
						failure: function(e) {
							// TODO: Продумать сообщение об ошибке 
							alert('Error');
							// getTableList(spr_list, spr_structure, records, load_progress, wnd, load_callback);
						}
					});
				}
			}
		},
		timeout: 600000
	});
}

/**
 * Создает и открывает окно загрузки с прогресс-баром
 */
function initSplash() {
	
	var project_name='ПроМед';
	var project_copyright='&copy; 2009-2012, ООО &laquo;СВАН&raquo;';
	var project_logo='/img/promed-web-logo.png';
	if (!Ext.isRemoteDB) { // для МонгоДБ не показываем окно загрузки настроек и справочников
		var wnd = new Ext.Window({
			id: 'splash',
			width:435,
			height:220,
			closable: false,
			draggable: false,
			resizable: false,
			modal: true,
			border: false,
			bodyBorder: false,
			html:
				'<table width="100%" border="0" cellspacing="0" cellpadding="10">'+
					'<tr>'+
					'<td width="120" height="120"><img style="margin-left:10px" src="'+project_logo+'" width="100" height="100" /></td>'+
					'<td align="left" valign="middle"><font style="font-family:Verdana, Geneva, sans-serif; font-size:36px">'+project_name+'</font></td>'+
					'</tr>'+
					'<tr>'+
					'<td height="30">&nbsp;</td>'+
					'<td align="left" valign="top">ver. '+PromedVer+' (Ревизия : '+Revision+' от '+PromedVerDate+')</td>'+
					'</tr>'+
					'<tr>'+
					'<td height="30">&nbsp;</td>'+
					'<td align="left" valign="top">'+project_copyright+'</td>'+
					'</tr>'+
					'</table>'+
					'<div id="splash-progress-bar"></div>'
		});
		wnd.show();
		var load_progress = new Ext.ProgressBar({
			anchor: '95%',
			applyTo: 'splash-progress-bar',
			value: 0.03
		});
		wnd.add(load_progress);
		load_progress.updateProgress(load_progress.value + progressstep, 'Загрузка настроек...');
		wnd.load_progress = load_progress;
		return wnd;
	} else {
		return false;
	}
}
function loadPromed(load_callback) {

	// функция загрузки настроек
	Ext.loadOptions = function(firstLoad) {
		controlStoreRequest = Ext.Ajax.request({
			url: C_OPTIONS_LOAD,
			async: false,
			success: function(result){
				Ext.globalOptions = Ext.JSON.decode(result.responseText);
				// Если при получении глобальных настроек вернулась ошибка, то выводим еще и выходим из Промеда
				if  (Ext.globalOptions && (Ext.globalOptions.success!=undefined) && Ext.globalOptions.success === false) {
					Ext.Msg.alert('Ошибка при загрузке настроек', 'При загрузке настроек с сервера произошла ошибка.<br/><b>'+((Ext.globalOptions.Error_Msg)?Ext.globalOptions.Error_Msg:'')+'</b><br/>Выйдите из системы и зайдите снова.',
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

					if (Ext.isRemoteDB) {
						// подгружаем модуль RemoteDB
						log('Для хранения локальных справочников используется хранилище вебсервера');
						getWnd('remotedb4').load({callback: function() {
							var RDDBDriver = Ext.db.remoteDBDriver.getInstance();
							// для модуля хранилища на стороне вебсервера версия справочников не существенна,
							// т.к. обновлять справочники на стороне клиента не надо (в данном случае их тут нет).
							RDDBDriver.open();
							// здесь справочники дальше открывать не надо
							// Загружаем еще какой нибудь драйвер для использования, если доступно
							if (Ext.isIndexedDb) {
								getWnd('indexeddb4').load({callback: function() {
									var IDBDriver = Ext.db.indexedDBDriver.getInstance();
									// Открываем без изменения версии
									log('IndexedDb доступен');
									IDBDriver.open('Promed',null, {
										failure: function(e, change_version) {
											log('Хранилище IndexedDb не открыто!');
											// ну нет так нет
											Ext.db.indexedDB = false;
											Ext.isIndexedDb = false;
											load_callback();
										},
										success: function(e, change_version) {
											log('Хранилище IndexedDb открыто.');
											load_callback();
										}
									});
								}});
							} else {
								if (Ext.isWebSqlDB) {
									getWnd('websqldb4').load({callback: function() {
										var WSDBDriver = Ext.db.webSqlDBDriver.getInstance();
										WSDBDriver.open("Promed",'', {
											failure: function(e, change_version) {
												// TODO: Продумать ситуацию с обработкой ошибки
												log('Хранилище WebSql не открыто!');
												load_callback();
											},
											success: function(e, change_version) {
												log('Хранилище WebSql открыто.');
												load_callback();
											}
										});
									}});
								} else {
									if (Ext.isGears) {
										getWnd('gears4').load({callback: function() {
											var localVerGears = new Ext.sql.Version();
											load_callback();
										}});
									} else {
										load_callback();
									}
								}
							}
							//wnd.close(); // для МонгоДБ не показываем окно загрузки настроек и справочников
						}});
					} else if (Ext.isGears) { 
						// подгружаем модуль Gears
						log('Для хранения локальных справочников используется Google Gears');
						getWnd('gears4').load({callback: function() {
							var localVerGears = new Ext.sql.Version();
							localVer = localVerGears.get();
							localVer = (localVer)?localVer:0;
							log('Версия локальной БД клиента: '+localVer);
							//log(serverVer, localVer);
							if (serverVer>parseFloat(localVer)) {
								//log(localVerGears.get());
								//localVerGears.change(0.01);
								// инициализируем окно прогресса
								var wnd = initSplash();
								loadTables(wnd, localVer, wnd.load_progress, load_callback);
							} else {
								load_callback();
							}
						}});
					} else if (Ext.isIndexedDb) {
						// подгружаем модуль IndexedDb
						log('Для хранения локальных справочников используется IndexedDb');
						getWnd('indexeddb4').load({callback: function() {
							var IDBDriver = Ext.db.indexedDBDriver.getInstance();
							// Открываем без изменения версии 
							log('Драйвер IndexedDb инициализирован');
							IDBDriver.open('Promed',null, {
								failure: function(e, change_version) {
									// TODO: Продумать ситуацию с обработкой ошибки  
									alert('Ошибка работы с локальным хранилищем!');
								},
								success: function(e, change_version) {
									log('Хранилище IndexedDb открыто');
									// получаем текущую версию
									localVer = IDBDriver.getVersion();
									localVer = (localVer && localVer!='undefined')?localVer:0;
									//log('change_version ',change_version);
									//log(serverVer, localVer);
									// если версия изменилась то загружаем измененные справочники
									if (!Ext.idb.IDBDriver.setVersionExists()) {
										localVer = localVer / 1000;
									}
									log('Версия локальной БД клиента: '+localVer);
									if (serverVer>parseFloat(localVer)) {
										// инициализируем окно прогресса
										var wnd = initSplash();
										loadTables(wnd, localVer, wnd.load_progress, load_callback);
									} else {
										load_callback();
									}
								}
							});
						}});
					} else if (Ext.isWebSqlDB) {
						// подгружаем модуль WebSqlDB
						log('Для хранения локальных справочников используется Web Sql DB');
						getWnd('websqldb4').load({callback: function() {
							var WSDBDriver = Ext.db.webSqlDBDriver.getInstance();
							// Открываем без изменения версии 
							WSDBDriver.open("Promed",'', {
								failure: function(e, change_version) {
									// TODO: Продумать ситуацию с обработкой ошибки  
									log(e);
									alert('Ошибка работы с локальным хранилищем!');
								},
								success: function(e, change_version) {
									// получаем текущую версию 
									localVer = WSDBDriver.getVersion();
									localVer = (localVer && localVer!='undefined')?localVer:'0';
									log('Версия локальной БД клиента: '+localVer);
									//log('change_version ',change_version);
									//log(serverVer, localVer);
									// если версия изменилась то загружаем измененные справочники
									if (serverVer>parseFloat(localVer)) {
										// инициализируем окно прогресса
										var wnd = initSplash();
										loadTables(wnd, localVer, wnd.load_progress, load_callback);
									} else {
										load_callback();
									}
								}
							});
						}});
					} else {
						log('Загрузка локального хранилища не выполнена!');
						load_callback();
					}
				} else {
					// Подменяем данные с локального хранилища при перечитывании глобальных настроек 
					Ext.setLocalOptions();
				}
			},
			failure: function(result){
				Ext.Msg.alert('Ошибка при загрузке настроек', 'При загрузке настроек с сервера произошла ошибка. Выйдите из системы и зайдите снова.');
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
	Ext.loadOptions(true);
}