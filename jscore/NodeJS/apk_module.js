/**
 * Модуль взаимодействия с мобильным приложением старшего бригады
 */


//	
// Инициализация системных модулей
//

var http = require("http");  // Модуль для создания http сервера и обработки http-запросов
var qs = require("querystring"); //Модуль для работы с параметрами запросов
var config_file = require('./httpServer.js').config;
console.log("config file apk_module = "+config_file);
//
// Инициализация прикладных модулей
//

var post = require('./httpPost.js'); // Реализация POST запросов
// var constants = require('./constants.js'); // Модуль хренения констант: хостов, портов подключения к сервисам, логины/пароли
var constants = require('./'+config_file); // Модуль хренения констант: хостов, портов подключения к сервисам, логины/пароли


var et_head_socket_list = []; //массив авторизованых сокетов бригад

var auth_group = 'autentificated'; //наименование авторизованной группы сокетов

var APK_IO = {}; //объект io одуля socket.io
this.apkIO = null; //объект io модуля socket.io, чтобы иметь доступ из других серверов. Для тестирования.
/**
 * Метод аутентификации сокета приложения
 */
var autentification = function( data , callback ) {

	if (typeof callback !== 'function') {
		callback = function(){};
	}

	var options = {
		host: constants.PROMED_API_HOST,
		port: constants.PROMED_API_PORT,
		path: constants.PROMED_AUTH_PATH+'?session-id='+data['session-id'],
		method: 'GET',
		headers: {
			'User-Agent':'NodeJS', // Без указания этого заголовка API не будет работать корректно
			'Session-Id':data['session-id']
		}
	};

	post.request(
		options,
		'',
		false,
		function(data) {

			try {
				var data = JSON.parse(data);

				if (data && data['errorMessage'] === 'OK') {
					callback(true);
				} else {
					callback(false, data);
				}
			} catch(e) {
				log({'APK_autentification_post_error':e});
			}
		},
		function(data) {
			callback(false, data);
		}
	);

}


/**
 *
 * Метод запуска сокет-сервера для приложения
 *
 **/
function startAPKSocketServer( SMP_IO ) {
	try {

		// SMP_IO - объект сокет сервера десктопных АРМов СМП. Инициализируется вне даннного модуля

		if (!SMP_IO || !SMP_IO.sockets) {
			throw {startAPKSocketServer_error: 'SMP_IO or SMP_IO.sockets is not defined'};
		}

		var APK_IO = require('socket.io').listen(constants.APK_SocketServerPort);
		this.apkIO = APK_IO;
		APK_IO.sockets.on('connection', function (socket) {

			//инициализируем объект сессии сокета
			socket.session = {};

			//Обработка события аутентификации
			socket.on('login', function(data) {

				// При аутентификации приложение должно отправлять идентификатор сессии, полученный
				// в API промеда и идентификатор бригады

				if (!data || !data['session-id'] || !data['EmergencyTeam_id']) {
					return false;
				}

				//data['session-id'] = 'MUMCpdFDTMIZrDVxrZJ2+77329l2dGKuUsRGjFwRCiOAK75P/0/vA7oDplZyY4LZ';

				autentification(  data,
					function( success , error_data ){
						if (!success) {
							log({apk_autentification_error: error_data});
							socket.emit('authentification_error',{apk_autentification_error: error_data})
							return false;
						}
						log({APK_joined: data['EmergencyTeam_id']});

						// При успешной аутентификации добавляем сокет в группу авторизованных и в массив авторизованых сокетов бригад

						socket.join(auth_group);

						socket.EmergencyTeam_id = data['EmergencyTeam_id'];

						et_head_socket_list[socket.id]=socket;
					}
				)


			});

			//Обработка события setStatus - смены статуса бригады, инициированного бригадой
			socket.on('setStatus', function(data){

				// Рассылаем данные всем всем авторизованным сокетам десктопных АРМов СМП, если...
				if ( (socket.rooms[auth_group] !== undefined)  // Сокет, инициирующий событие, находится в группе авторизованных
					&& data['EmergencyTeamStatus_Name'] // Передано наименование статуса
					&& (typeof data['EmergencyTeamStatus_id'] !== 'undefined') // Передан идентификатор статуса
					&& data['EmergencyTeam_id'])  {// Передан идентификатор бригадыя

					data = JSON.stringify([data]);

					SMP_IO.sockets.in('authentificatedUsers').emit('changeEmergencyTeamStatus', data, 'changeStatus');
				}

			});

			socket.on('disconnect', function () {
				if (et_head_socket_list[socket.id]) {
					delete et_head_socket_list[socket.id];
				}
			});
		});

		log();
		log("ApkSocketServer has started.");
		log();

	} catch(e) {
		log({startSmpServerError:e});
		// При непредвиденной ошибке при запуске сокет-сервера перезапускаем через 10 секунд
		setTimeout(function(){
			startAPKSocketServer(SMP_IO);
		}, 10000)

	}

}

/**
 *
 * Метод отправки статуса на планшет с нативным приложением
 * @param data object
 *
 **/
function APKSendStatus( data ) {

	// Обязательно должны быть переданы идентификаторы бригады и проставляемого статуса
	if (!data || !data['EmergencyTeam_id'] || !data['EmergencyTeamStatus_id']) {
		return false;
	}
	var result = false;
	if(data['EmergencyTeamStatus_Code'] == 8){
		// если статус "Ремонт". Задача 100937
		var CmpCallCardID = (data['CmpCallCardArr']) ? data['CmpCallCardArr'] : -1;
		result = _emitToEmergencyTeam(data['EmergencyTeam_id'], ['changeStatusRepairs', CmpCallCardID]);
	}else{
		result = _emitToEmergencyTeam(data['EmergencyTeam_id'], ['changeStatus',data['EmergencyTeamStatus_id']]);
	}
	return result;
}

/**
 * Отмена бригады
 * @param data = { EmergencyTeam_id, CmpCallCard_id }
 * @constructor
 */
function APKCancelCmpCallCard(data) {
	// Обязательно должны быть переданы идентификаторы бригады и талона вызова
	if (!data || !data['EmergencyTeam_id'] || !data['CmpCallCard_id']) {
		return false;
	}

	var result = _emitToEmergencyTeam(data['EmergencyTeam_id'], ['cancelCmpCard', data['CmpCallCard_id']]);
	return result;
}

/**
 * Событие изменения талона вызова
 * @param data = { EmergencyTeam_id, CmpCallCard_id }
 * @constructor
 */
function APKChangeCmpCallCard(data) {
	// Обязательно должны быть переданы идентификаторы бригады и талона вызова
	if (!data || !data['EmergencyTeam_id'] || !data['CmpCallCard_id']) {
		return false;
	}	
	// _emitToEmergencyTeam(data['EmergencyTeam_id'], ['changeCmpCard', data['CmpCallCard_id']]);
	var result = _emitToEmergencyTeam(data['EmergencyTeam_id'], ['changeCmpCard', data]);
	return result;
}

/**
 *
 * Метод оповещения о новом вызове
 * @param data object
 *
 **/
function APKSendCmpCallCard( data ) {

	// Обязательно должен быть передан идентификатор бригады
	if (!data || !data['EmergencyTeam_id']) {return false;}
	var result = _emitToEmergencyTeam(data['EmergencyTeam_id'], ['newCmpCard', data]);
	return result;
}

function APKEmergencyTeamStatusBeginHospitalized( data ) {

	// Обязательно должен быть передан идентификатор бригады
	if (!data || !data['EmergencyTeam_id']) {return false;}
	var result = _emitToEmergencyTeam(data['EmergencyTeam_id'], ['setEmergencyTeamStatusBeginHospitalized', data]);
	return result;
}

function APKregistrationFailure(data){
	if (!data || !data['EmergencyTeam_id']) {return false;}
	var result = _emitToEmergencyTeam(data['EmergencyTeam_id'], ['registrationFailure', data['CmpCallCard_id']]);
	return result;
}

/**
/* событие обновления информации карты вызова
 * data - измененные поля
*/
function APKupdatedInformationOnCall(data){
	if (!data || !data['EmergencyTeam_id']) {return false;}
	var result = _emitToEmergencyTeam(data['EmergencyTeam_id'], ['updatedInformationOnCall', data]);
	return result;
}

// ухудшение состояния
function APKisDeterior(data){
	if (!data || !data['EmergencyTeam_id'] || !data['CmpCallCard_id']) {return false;}
	var result = _emitToEmergencyTeam(data['EmergencyTeam_id'], ['isDeterior', data]);
	return result;
}

// событие НМП  "Вызов исполнен"
function APKEventCallCompleted(data){
	if (!data || !data['EmergencyTeam_id'] || !data['CmpCallCard_id']) {return false;}
	var result = _emitToEmergencyTeam(data['EmergencyTeam_id'], ['eventCallCompleted', data]);
	return result;
}

// возвращает массив id бригад
function APKemergencyTeamOnline(){
	var arrEmergencyTeam_id = [];
	for (var key in et_head_socket_list) {
		var socket = et_head_socket_list[key];
		arrEmergencyTeam_id.push(socket.EmergencyTeam_id);
	}
	return arrEmergencyTeam_id;
}

/**
 * Вызываем socket.emit с аргуметрами @param args для бригады с идентификатором @param EmergencyTeam_id
 * @param EmergencyTeam_id
 * @param args
 * @private
 */
function _emitToEmergencyTeam( EmergencyTeam_id, args ) {
	var socket = {};
	var flag = false;
	for (var key in et_head_socket_list) {
		socket = et_head_socket_list[key];
		// Если найден сокет с нужным идентификатором бригады , инициируем событие получения нового вызова с переданными данными
		if (socket && socket.EmergencyTeam_id == EmergencyTeam_id ) {
			socket.emit.apply(socket, args);
			flag = true;
		}
	}
	return flag;
}


var log = function() {
	for (var i=0; i<arguments.length; i++) {
		console.log(arguments[i]);
	}
	console.log();
};

exports.startAPKSocketServer = startAPKSocketServer;
exports.APKSendCmpCallCard = APKSendCmpCallCard;
exports.APKEmergencyTeamStatusBeginHospitalized = APKEmergencyTeamStatusBeginHospitalized;
exports.APKSendStatus = APKSendStatus;
exports.APKCancelCmpCallCard = APKCancelCmpCallCard;
exports.APKChangeCmpCallCard = APKChangeCmpCallCard;
exports.APKregistrationFailure = APKregistrationFailure;
exports.APKupdatedInformationOnCall = APKupdatedInformationOnCall;
exports.APKisDeterior = APKisDeterior;
exports.APKEventCallCompleted = APKEventCallCompleted;
exports.APKemergencyTeamOnline = APKemergencyTeamOnline;