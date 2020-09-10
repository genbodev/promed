/**
 * Модуль подключения к системе транспортного мониторинга ТНЦ
 **/


var log = function() {
	for (var i=0; i<arguments.length; i++) {
		console.log(arguments[i]);
	}
	console.log();
};

var post = require('./httpPost.js');

/** Параметры тестовых сервисов

var username = "Специалист ИП Лебедева";
var password = "aY1RoZ2KEhzlgUmde3AWaA=="; //\u003d\u003d

var host = 'infor.trans-monitor.ru';
var http_port = 9393;
var ws_port = 9393;
*/


//Параметры боевой Уфы

/** old version password
var username = 'Svan';
var password = '6Afx/PgtEy+bsBjKZzihnw==';//'e807f1fcf82d132f9bb018ca6738a19f';//'1234567890';
 **/
/*var username = 'RMIAS SSMP Ufa CentrPod';
var password = '6Afx/PgtEy+bsBjKZzihnw==';//'e807f1fcf82d132f9bb018ca6738a19f';//'1234567890';*/ //заменяем для показа

var username = 'RMIAS Oktyabrskiy';
var password = 'NTo/hHg20g2WfXU6/ojMtw==';


//http://gostrans.bashkortostan.ru:47227/vms-ws/services
var host = 'gostrans.bashkortostan.ru';
var ws_port = 47227;
var http_port = 47227; 

// Параметры боевой Уфы */

var getListPath = '/vms-ws/rest/TransportUIWS/getList'; // Путь для получения списка ТС
var getLastCachedDataPath = '/vms-ws/rest/NDDataWS/getLastCachedData'; // Путь для получения последних полученных данных со списка устройств
var ws_path = '/vms-ws/socket'; //Пусть до сокета

var transport_list = [];
var lastCachedData = [];

var SubscribingResultMsgType = 'ru.infor.websocket.transport.SubscribingResult'; //Тип сообщения - результат подписки
var DataPackMsgType = 'ru.infor.websocket.transport.DataPack'; //Тип сообщения - данные с устройства

var getTransportInterval = null;

function getLastCachedDataFromCache() {
	return lastCachedData;
}
function getGetTransportInterval() {
	return getTransportInterval;
}
function startGetTransportInterval( callback ) {
	
	// Не будем давать возможность запускать интервал, если он не будет производить действий,
	// чтобы лишний раз не нагружать сторонний сервис
	if (typeof callback !== 'function') {
		return false;
	}
	
	if (getTransportInterval) {
		return;
	}
	
	
	getTransportInterval = setInterval(function(){
		
		getLastCachedDataFromService( callback );
		
	}, 10000);
	
}


/**
 * Метод получения списка данных от транспортных средсвах
 */

function getTransportList( callback ) {
	
	var data = [{
		"userName":username, "password":password
	}, {
		"loadNotDiscardedOnly":1,  "applyPrimaryGroupFilter":1,  "beginIndex":0, "count":2147483647,  "loadDeletedItems":0
	}];
	var data_json = JSON.stringify( data );
	
	var request_params = {
		host: host,
		port: http_port,
		path: getListPath,
		method: 'POST',
		headers: {
			'Accept':'*/*',
			'Accept-Charset':'UTF-8,*;q=0.5',
			'Accept-Encoding':'deflate',
			'Accept-Language':'ru-RU,ru;q=0.8,en-US;q=0.6,en;q=0.4'
		}
	};
	
	//C:\Program Files (x86)\nodejs 10.31>node.exe C:\WebServers\home\promed.ru\www\jscore\NodeJS\httpServer.js
	
	try {	
		post.request(
			request_params
			, data_json
			, false
			, function(data){
				try {
					var result = JSON.parse(data);
					if (!result || !result.objList || !result.objList.length ) {
						throw 'result object doesn\'t contains objList array';
					}
					transport_list = result.objList;
					if ( typeof callback === 'function' ) {
						callback( transport_list );
					}
				} catch(e) {
					log({process_getTransportList_result_error:e})
				}
			}
			, function(err){
				log({getTransportList_post_request_error:err});
			}
		);
	} catch(e) {
		log({process_getTransportList_result_error:e})
	}
}

function _associateLastDataWithTransportId( data , transport ) {
	
	if (!data || !data.length || !transport || !transport.length) {
		return [];
	}
	
	for (var i = 0; i < data.length; i++) {
		
		for (var k = 0; k < transport.length; k++) {
			if (data[i].deviceId == transport[k].deviceId) {
				data[i].transport_id = transport[k].id;
				data[i].transport_name = (transport[k].transportTypeDescription || '') + ' ' + (transport[k].garageNum);
				data[i].tramsport = transport[k];
				continue;
			}
		};
		
	};
	
	return data;
	
}

/*
 * Метод получения последних полученных геоданных для списка устройств
 **/
function getLastCachedDataFromService( callback ) {
	
	if (typeof callback !== 'function') {
		return;
	}
	
	// Получаем список траспортных средств
	getTransportList( function( transport_data ) {
		
		//Получаем список идентификаторов из списка транспорта
		var device_list = [];
		
		for (var i = 0; i < transport_data.length; i++) {
			if (transport_data[i].deviceId) {
				device_list.push(transport_data[i].deviceId);
			}
		};
		
		// Получаем последние закэшированные данные по списку устройств
		getLastCachedDataRequest( device_list, function( cache_data ) {
			
			if (!cache_data || !cache_data.length) {
				return;
			}
			//Добавляем в информацию по устройствам соотвествующие идентификаторы транспорта
			var data = _associateLastDataWithTransportId( cache_data , transport_data );
			lastCachedData = data;
			callback(data);
			
		} )
		
		
	} );
	
}

/*
 * Запрос получения последних полученных геоданных для списка устройств
 **/
function getLastCachedDataRequest( deviceList , callback ) {
	var data = [{
		"userName":username,
		"password":password
	}, {
		"deviceIdList":deviceList, 
//		"applyPrimaryGroupFilter":1, 
//		"beginIndex":0,
//		"count":2147483647, 
//		"loadDeletedItems":0
	}];
	var data_json = JSON.stringify( data );
	
	var request_params = {
		host: host,
		port: http_port,
		path: getLastCachedDataPath,
		method: 'POST',
		headers: {
			'Accept':'*/*',
			'Accept-Charset':'UTF-8,*;q=0.5',
			'Accept-Encoding':'deflate',
			'Accept-Language':'ru-RU,ru;q=0.8,en-US;q=0.6,en;q=0.4'
		}
	};
	
	try {	
		post.request(
			request_params
			, data_json
			, false
			, function(data){
				try {
					var result = JSON.parse(data);
					if (typeof callback == 'function') {
						callback(result);
					}

				} catch(e) {
					log({process_getLastCachedData_result_error:e})
				}
			}
			, function(err){
				log({getLastCachedDataRequest_post_request_error:err});
			}
		);
	} catch(e) {
		log({process_getLastCachedData_result_error:e})
	}
	
	
}


exports.getTransportList = getTransportList;
// exports.subscribe = subscribe;
exports.getLastCachedDataFromService = getLastCachedDataFromService;
exports.getGetTransportInterval = getGetTransportInterval;
exports.startGetTransportInterval = startGetTransportInterval;
exports.getLastCachedDataFromCache = getLastCachedDataFromCache;

// Метод оформления подписки на получение данных // Пока принято решение не использовать
/**
 * @param device_list - список идентификаторов устройств транспортных средств
 */
// function subscribe(device_list, error, processMsg) {
	
// 	var WebSocket = require('ws'),
// 	ws = new WebSocket('ws://'+host+':'+ws_port+ws_path);
	
// 	log({host:'ws://'+host+':'+ws_port+ws_path});
	
// 	ws.on('open', function() {

// 		//Список идентификаторов устройств
		
// 		/**
// 		 *
// 		Тестовый список идентификаторов
		
// 		var deviceIdList = [
// 			48166159,
// 			48166151,
// 			48166136,
// 			48166128,
// 			48166115,
// 		];
// 		*/
// 		var deviceIdList = [];
		
// 		for (var i = 0; i < device_list.length; i++) {
			
// 			if (device_list[i]) {
// 				deviceIdList.push(device_list[i]);
// 			}
			
// 		};
		
// 		log({json:JSON.stringify(
// 			{
// 			 	"serviceName":"NDDataWS" 
// 				,"methodName":"sendList" 
// 				,"messageType":"ru.infor.ws.business.vms.websocket.objects.SubscribingOptions_SendListNDData" 
// 				,"context":{
// 					"userName":username,
// 					"password":password
// 				}
// 				,"deviceIdList":deviceIdList
// 			}
// 		)});
		
// 		ws.send(JSON.stringify(
// 			{
// 			 	"serviceName":"NDDataWS" 
// 				,"methodName":"sendList" 
// 				,"messageType":"ru.infor.ws.business.vms.websocket.objects.SubscribingOptions_SendListNDData" 
// 				,"context":{
// 					"userName":username,
// 					"password":password
// 				}
// 				,"deviceIdList":deviceIdList
// 			}
// 		))
// 	});
	
// 	var subscribe_success = null;
	
// 	ws.on('message', function(message) {
// 		try {
// 			var msg = JSON.parse(message);
			
// 			if (!msg) {
// 				return;
// 			}
			
// 			switch (msg.messageType) {
// 				case SubscribingResultMsgType:
					
// 					// Проверка подписки
// 					if (msg.status === -1) {
// 						subscribe_success = false;

// 						if (typeof error == 'function') {
// 							error(msg);
// 						} else {
// 							log({subscribe_error: msg});
// 						}

// 					} else if (msg.status === 0) {
// 						subscribe_success = true;
// 					}
					
// 					break;
// 				case DataPackMsgType:
// 						if(!msg.dataJson) {
// 							log({on_tnc_message_error:'No dataJson in msg'});
// 							return;
// 						}
						
// 						processMsg(msg.dataJson);
						
// 					break;
// 				default:
					
// 					break;
// 			}
			
// 			return;

// 		} catch (e) {
// 			log({on_tnc_message_error:e});
// 		}
// 	});
	
// }
