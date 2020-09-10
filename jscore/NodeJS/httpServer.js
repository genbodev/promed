
var log = function() {
	for (var i=0; i<arguments.length; i++) {
		console.log(arguments[i]);
	}
	console.log();
};

var config_file = configFile(process.argv);
exports.config = config_file;

function configFile(args){
	var nameFile = 'constants.js';
	if(args.length > 2){
		nameFile = process.argv[2];
	}
	return nameFile;
}

function promed() {
	
	//	
	// Инициализация системных модулей
	//
	
	var http = require("http"); // Модуль для создания http сервера и обработки http-запросов
	var qs = require("querystring"); //Модуль для работы с параметрами запросов
	var fs = require('fs'); 
	var url = require('url');
	
	//
	// Инициализация прикладных модулей
	//
	console.log("config file httpServer = "+config_file);
	var post = require('./httpPost.js'); // Реализация POST запросов
	//var constants = require('./constants.js'); // Модуль хренения констант: хостов, портов подключения к сервисам, логины/пароли
	var constants = require('./'+config_file); // Модуль хренения констант: хостов, портов подключения к сервисам, логины/пароли
	var apk = require('./apk_module.js'); // Модуль взаимодействия с приложением
	var tnc_module = require('./tnc_module.js'); // Модуль взаимодействия с геосервисом ТНЦ
	var eventsSmp4 = require('./eventsSmp4.js'); // Модуль хранения обработчиков событий для АРМов СМП написанных на ExtJS4
	
	//
	// Инициализация переменных
	//
	
	var sockets = []; // Массив сокетов АРМов старшего бригады СМП на планшете
	var smpSockets = []; // Массив сокетов десктопных АРМов СМП
	var apkSockets = []; // Массив сокетов нативного приложения

	var SMP_SOCKET_IO = null; // Инициализируем глобальный объект сокет-сервера для десктопных АРМов СМП, чтобы иметь доступ из других серверов
	var SOCKET_SERVER = null; // глобальный объект для тестирования
	var PROMED_SERVER = null; // глобальный объект для тестирования

	var promedSockets = {};
	
	
	// Шаблон данных запроса на получения дерева Evn-ов пациента
	var requestEvnTree = {
		ARMType: 'headBrigMobile',
		Diag_id: 0,
		LpuSection_id:	0,
		MedStaffFact_id: 0,
		Person_id: '',//Person_id
		level: 0,
		node: 'root',
		object:	'Person',
		object_id: '',//Person_id
		type: 0,
		user_MedStaffFact_id:''	
	}
	
	// Шаблон данных запроса на получения сигнальной информации о пациенте
	var requestSignalInformation = {
		Person_id:0,
		object: 'SignalInformationAll',
		object_id:'Person_id',
		object_value:0,//Person_id
		parent_object_id:'Person_id',
		parent_object_value:0,//Person_id
		user_MedStaffFact_id:0,
		view_section:'main',
		ARMType:'headBrigMobile'
	}
	
	// Шаблон данных запроса на получение данных конкретного Evn-а
	var requestEventData = {
		object:'',
		object_id:'',
		object_value:'',
		user_MedStaffFact_id:'',
		ARMType:'headBrigMobile'
	}
	
	// Интервал очистки заблокированных для редактирования талонов вызова
	var timeToClearLockList= false;

	// Кэширование состояния бригад СМП
	var EmergencyTeamOperEnvForSmpUnitCache = {
		last_update: null,
		timeout: 30000
	};

	// Метод получение идентификатора сессии из cookies-строки
	function getSessionId(cookies) {
		return /SESS\w*ID=([^;]+)/i.test(cookies) ? RegExp.$1 : false;
	}

	// Метод проверки строки JSON
	function isJSON(data){
		try{
			JSON.parse(data);
			return true;
		}
		catch (error){
			return false;
		}
	}
	
	// Роутинг запросов к http-серверу NodeJS
	// @param data object данные запроса
	// @param response object объект ответа для http-запроса
	function route(data,response){
		var success = false;
		switch (data.action)
		{
			// Экшн set указывает на создание и назначение на бригаду нового талона вызова
			case 'set' : {
				for (i=0; i<sockets.length; i++) {
					var tmp = sockets[i];

					if (tmp.session.EmergencyTeamId == data.EmergencyTeamId) {
						tmp.emit('NewCallCard',data);
						success = true;
					}
				}
				responseData= JSON.stringify({success: success});

				response.write(responseData);
				response.end();
				break;
			}
			// Экшн getOnlineUsersDNDV возвращает по запросу все pmUser_id подключенных десктопных пользователей
			case 'getOnlineUsersDNDV' : {
				//log(smpSockets);
				pmUserIds = [];
				for (var i in smpSockets) {
					if (smpSockets.hasOwnProperty(i)) {
					 pmUserIds.push(smpSockets[i].session.pmuser_id)
					}
				}
				if (pmUserIds.length > 0)
					{
						success = true;
					};
				
				responseData = JSON.stringify({success: success, data: pmUserIds});

				response.write(responseData);
				response.end();
				
				break;
			}
			case 'interrupt' : {
				var session_ids = JSON.parse(data.Session_ids);
				var emitUserID = data.emitUserID || 0; // пользователь отправивший команду на прерывание сессии
				var minutes = data.DelayMinutes || 0;
				var delay = minutes*60*1000;
				var message = "<b>Через "+minutes+" минут будет произведен выход из системы. Необходимо закончить работу и сохранить все изменения.</b><br>";
				var socketEmitUser = false;
				var usersSocket = []; 

				for (var i=0; i<session_ids.length; i++) {
					var session_id = session_ids[i];
					var socket = promedSockets[session_id];
					if (socket) usersSocket.push(socket);
				}

				if(emitUserID) {
					for(var key in promedSockets){
						if( promedSockets[key].id == emitUserID) {
							socketEmitUser = promedSockets[key];
							break;
						}
					}
				}

				if (data.Message) {message = message + "<br>" + data.Message;}

				var interruptEsErr = function(EsErr){
					// ответ серверу
					var success = EsErr || false;
					var responseData= JSON.stringify({success: success});
					response.write(responseData);
					response.end();
				}

				try{
					var logout = function() {
						var msg = true;
						try {
							usersSocket.forEach(function(socket){
								post.PostRequest({},socket.session.cookies, socket.session.userAgent, '/?c=User&m=logoutUser', function(response) {
									socket.emit('logout');
									socket.disconnect();
								},socket);
							});
						} catch(e) {
							log({'onPostLogoutError': e});
							msg = 'ERROR';
						}

						// отправим сообщение в консоль пользователя о выполнении команды прерывания сессии
						if(socketEmitUser) {
							socketEmitUser.emit('promedMessage', {title: 'Выполненение команды прерывания сессии ', message: msg, hidden: true});
						}
					};

					if ( usersSocket.length>0 ){
						if (delay > 0) {
							usersSocket.forEach(function(socket){
								socket.emit('promedMessage', {title: 'Оповещение', message: message});
							});
							setTimeout(logout, delay);
						} else {
							logout();
						}
						interruptEsErr(true);
					}else{
						// не найдены пользователи для прерывания сессии
						interruptEsErr(false);
					}
				}catch(e){
					log({'onPostLogoutError': e});
					interruptEsErr('ERROR');
				}
				break;
			}
			default:{
				return false;
				break;
			}		 
		}

	};

	function testServer(res){
		if(!res) return false;
		res.writeHead(200, {"Content-Type": "text/plain"});
		var objIO = {
			serverSMP_IO : {
				name : 'SMPserver',
				comment : 'взаимодействие между десктопными АРМами, нативным приложением и мобильным (Web) армом',
				io : ( SMP_SOCKET_IO ) ? SMP_SOCKET_IO : false,
				port : constants.SMPSocketServerPort,
				socketsArr: Object.keys(smpSockets).length
			},
			serverSOCKET_IO : {
				name : 'SOCKETserver',
				comment : 'взаимодействие между сервером промед и мобильным (Web) армом',
				io : ( SOCKET_SERVER ) ? SOCKET_SERVER : false,
				port : constants.socketServerPort,
				socketsArr: Object.keys(sockets).length
			},
			serverPROMED_IO : {
				name : 'PROMEDserver',
				comment : 'промед',
				io : ( PROMED_SERVER ) ? PROMED_SERVER : false,
				port : constants.PromedSocketServerPort,
				socketsArr: Object.keys(promedSockets).length
			},
			serverAPK_IO : {
				name : 'APKserver',
				comment : 'сокет сервер для работы с нативным приложением',
				io : ( apk.apkIO ) ? apk.apkIO : false,
				port : constants.APK_SocketServerPort,
				socketsArr: Object.keys(apkSockets).length
			}
		};
		for(var key in objIO){
			if( objIO[key].io && objIO[key].io.sockets ){
				objIO[key].sockets = objIO[key].io.sockets;
				objIO[key].connects = objIO[key].io.sockets.connected;
				objIO[key].connectLength = Object.keys( objIO[key].connects ).length;
			}else{
				objIO[key].sockets = false;
				objIO[key].connects = {};
				objIO[key].connectLength = 0;
				objIO[key].connectionKey = '';
			}
		}
		for(key in objIO){
			var obj = objIO[key];
			var existenceIO = ( obj.sockets ) ? 'io.sockets OK' : 'io.sockets undefined';
			res.write(obj.name+'. PORT: '+obj.port+'\n');
			res.write('sockets: '+existenceIO+'\n');
			res.write('connections: '+obj.connectLength+'\n');
			res.write('socketsArray: '+obj.socketsArr+'\n');
			res.write('.................................\n');
			res.write('\n');
		}
		res.end();
	}
	/**
	*
	* Метод запуска http сервера для обработки запросов из промеда
	* Метод создаёт http-сервер и обрабатывает параметры запроса,
	* в случае успешной обработки передаёт данные методу "route"
	* который определяет, что нужно сделать с запросом и какой вернуть результат
	*/
	function startHttpServer() {
		
		function onRequest(request, response) {
	    	var postData = "";
	    	
			request.setEncoding("utf8");
			
			// Обработчик ошибки запроса
			request.addListener('error',function(error){
				log({startHttpServer_http_request_error:error})
			});
			// Обработчик получения данных запроса
			request.addListener("data", function(postDataChunk) {
				postData += postDataChunk;
				//log("Received POST data chunk '"+	postDataChunk + "'.");
			});
			// Обработчик конца запроса
			request.addListener("end", function() {
				response.writeHead(200, {"Content-Type": "text/plain"});
				try {
					var requestParams = qs.parse(postData);
				} catch (e) {
					console.log('error httpServer');
					var data = {
						success: false,
						Err_Msg: 'Parse Error'
					}
					response.write(qs.stringify(data));
					response.end();
				}
				/*
				if (Object.keys(requestParams).length != 0) {
					log('POSTrequestParams', requestParams);
					route(requestParams,response);
				}
				*/
				log(requestParams);
				route(requestParams,response);
			});

			if(request.method=='GET') {
				var url_parts = url.parse(request.url,true);
				if(url_parts.path == '/nodejs'){
					testServer(response);
				}
			}
		}
		http.createServer(onRequest).listen(constants.httpServerPort);
		log("HTTPServer has started. :: "+constants.httpServerPort);
		log();
	}
	
	/**
	* Метод запуска сокет-сервера
	* Метод запускает сокет сервер с указанным в константах портом.
	* В результате работы перечисленных в методе и подключаемых модулях обработчиков
	* событий происходит взаимодействие между десктопными АРМами, нативным приложением и мобильным (Web) армом
	*/
	
	function startSmpServer() {
		try {
			var io = require('socket.io').listen(constants.SMPSocketServerPort);
			
			SMP_SOCKET_IO = io;
			
			io.sockets.on('connection', function (socket) {
				
				socket.session = {};
				
				// отследим сокет каждого кто зашел в промед из определенного региона.
				socket.on('region', function(data){
					var regionName = ['perm', 'ufa'];
					if(regionName.indexOf(data) < 0) return;
					// если нужный нам регион - запишем в файл время, идентификатор, колличество соединений
					var TheCurrentDate = new Date(); // текущая дата
					var clientsCount = socket.server.eio.clientsCount; // колличество соединений
					var str = TheCurrentDate+'. '+'ID: '+socket.id+'. clientsCount: '+clientsCount;
					fs.writeFile('compounds_socket.txt', str);
					console.log('SOCKET.IO REGION '+data+'. ID: '+socket.id+'. clientsCount: '+clientsCount);
				});
				/**
				* Сразу после того, как произошло соединение нового пользователя
				* с сокет-сервером необходимо его аутентифицировать:
				*
				* - В результате успешного процесса аутентификации сокет будет добавлен в группу
				* доверенных сокетов;
				*
				* - В случае провала аутентификации сокет будет отключен от сервера;
				* 
				*/
				
				// Отправляем запрос клиенту на получение данных для аутентификации
				//В результате должны быть получены: cookies-строка, pmuser_id, userAgent заголовок
				socket.emit('authentification', function(cookies, pmuser_id, userAgent, regionNick) {
					
					//Непосредственно аутентификация производится сервером промеда по cookies и userAgent
					post.PostRequest({},cookies, userAgent, '/?c=CmpCallCard&m=getPmUserInfo', function(data) {
						try {
							if(!isJSON(data)){
								log('authentification SYNTAX ERROR JSON: '+data);
								socket.emit('logout');
								socket.disconnect();
								return false;
							}
							var response = JSON.parse(data);
							//Если аутентификация на сервере промеда успешна, то в ответе должен вернуться 
							// pmuser_id аналогичный тому, что передал клиент и Lpu_id - идентификатор МО этого клиента
							if ((response[0])&&(response[0].pmuser_id)&&(response[0].pmuser_id == pmuser_id)&&(response[0].Lpu_id)) {
								
								//Добавляем клиентские данные в объект сессии
								socket.session.cookies = cookies;
								socket.session.pmuser_id = pmuser_id;
								socket.session.Lpu_id = response[0].Lpu_id;
								socket.session.CurMedService_id = response[0].CurMedService_id;
								socket.session.userAgent = userAgent;								
								socket.session.regionNick = regionNick;
								
								//Добавляем сокет в массив сокетов с ключом-идентификатором
								smpSockets[socket.id]=socket;
								
								//Добавяляем сокет в группы аутентифицированных пользователей,
								// группу соответствующего МО
								socket.join('authentificatedUsers');
								//Костыль. Надо думать, как сделать Lpu_id в подгруппой authentificatedUsers
								socket.join(response[0].Lpu_id);
								socket.join('MedService_'+response[0].CurMedService_id);
								//добавление в группу подстанций
								if(response[0].OperDepartament){
									socket.join('OperDepartament_'+response[0].OperDepartament);
									socket.session.OperDepartament = response[0].OperDepartament;
								}
								
								//Отправляем пользователю сообщение об успешной аутентификации
								socket.emit('authentificated');
							} else {
								// В случае ошибки аутентификации отправляем пользователю сообщение
								// об отключении (культурно) и отключаем его
								socket.emit('logout');
								socket.disconnect();
								return false;
							}
						} catch(e) {							
							// В случае непредвиденной ошибки аутентификации отправляем пользователю 
							// сообщение об отключении и отключаем его
							log({'onSmpAutentificationError': e});
							socket.emit('logout');
							socket.disconnect();
							return false;							
						}
						
					},socket);
				});
				
				// обработчик события lockCmpCallCard ( блокировка талона вызова )
				socket.on('lockCmpCallCard',function(CmpCallCardId,callback,onError) {
					try {
						// Проверяем наличие сокета в массиве проверенных сокетов смп
						if (!smpSockets[socket.id]) {
							return false;
						}
						// Отправляем запрос на блокировку талона вызова на сервер промед
						post.PostRequest({'CmpCallCard_id':CmpCallCardId},socket.session.cookies,socket.session.userAgent,'/?c=CmpCallCard&m=lockCmpCallCard', function(data){
							try {

								var response = JSON.parse(data);
								callback(response);
								if ((response)&&(response.success)){
									// Если ответ от сервера положительный, рассылаем всем доверенным сокетам событие, 
									// сообщающее о блокировке талона вызова с идентификатором CmpCallCardId
									io.sockets.in('authentificatedUsers').emit('lockCmpCallCard', {'CmpCallCard_id':CmpCallCardId});
								}
							} catch(e) {
								log({'onSmpLockCmpCallCard_PostRequest_Error': e});
								callback(false,{
										e:e,
										msg:'Во время блокировки талона вызова произошла ошибка. Обратитесь к администратору'
								});
							}
						});
					} catch(e) {
						log({'onSmpLockCmpCallCardError': e});
						callback(false,{
							e:e,
							msg:'Во время блокировки талона вызова произошла ошибка. Обратитесь к администратору'
						});
					}
				});

				/**
				*
				* Чтобы предотвратить бесконечную блокировку талона вызова, во время работы с талоном
				* клиентом с определённым интервалом инициализируются события keepLockCmpCallCard, говорящие серверу о том, что 
				* с талоном вызова с идентификатором CmpCallCardId все ещё производятся какие-то действия.
				*
				* Сервером промед постоянно проверяются все записи о блокировке талонов вызова. Если последнее изменение
				* в записи блокировки было больше минуты назад, значит событие keepLockCmpCallCard по какой-либо причине не было
				* инициировано клиентом, а значит необходимо талон вызова разблокировать, чтобы другие клиенты могли с ним работать
				*/
	
				// обработчик события keepLockCmpCallCard ( продолжение блокировки талона вызова )
				socket.on('keepLockCmpCallCard',function(CmpCallCardId,callback) {
					try {
						// Проверяем наличие сокета в массиве проверенных сокетов смп
						if (!smpSockets[socket.id]) {
							return false;
						}
						// Отправляем запрос на блокировку талона вызова на сервер промед (таким образом обновляя запись о блокировки)
						post.PostRequest({'CmpCallCard_id':CmpCallCardId},socket.session.cookies,socket.session.userAgent,'/?c=CmpCallCard&m=lockCmpCallCard', function(data){
							try {
								// Если ответ от сервера положительный, возвращаем результат инициатору сообщения 
								response = JSON.parse(data);
								callback(data);
							} catch (e) {
								log({'onKeepLockCmpCallCardError_PostReuqest': e});
								callback(false,{
									e:e,
									msg:'Во время продолженой блокировки талона вызова произошла ошибка. Обратитесь к администратору'
								});
							}
						});
					} catch(e) {
						log({'onKeepLockCmpCallCardError': e});
						callback(false,{
							e:e,
							msg:'Во время продолженой блокировки талона вызова произошла ошибка. Обратитесь к администратору'
						});
					}
				});

				/**
				* Инициализация событий для АРМов на ExtJS 4
				*/
				
				var params = [];
					params['socket'] = socket,
					params['smpSockets'] = smpSockets,
					params['post'] = post,
					params['io'] = io,
					params['apk'] = apk,
					params['tnc'] = tnc_module,
					evts4 = eventsSmp4.getSmpEventsObject(params);

				for (var key in evts4){
				  if (evts4.hasOwnProperty(key)) {
					socket.on(key, evts4[key]);
				  }
				}
				
				
				// обработчик события unlockCmpCallCard ( снятие блокировки талона вызова )
				socket.on('unlockCmpCallCard',function(CmpCallCardId,callback){
					try {
						// Проверяем наличие сокета в массиве проверенных сокетов смп
						if (!smpSockets[socket.id]) {
							return false;
						}
						// Отправляем запрос на получение данных о талоне вызова, в котором так же происходит разблокировка талона вызова, на сервер промед
						// @TODO: развести метод получения данных о талоне вызова и метод разблокировки талона
						post.PostRequest({'CmpCallCard_id':CmpCallCardId},socket.session.cookies,socket.session.userAgent,'/?c=CmpCallCard&m=getCmpCallCardSmpInfo', function(data){
							try {
								var response = JSON.parse(data);
								if (response){
									// Рассылаем всем сокетам из группы необходимого МО данные о разблокировке талона вызова
									io.sockets.in(response.Lpu_id).emit('unlockCmpCallCard', response);
									
									// С определённым интервалом очищаем устаревшие записи о заблокированных талонов вызовов
									if (timeToClearLockList) {
										post.PostRequest({},socket.session.cookies,socket.session.userAgent,'/?c=CmpCallCard&m=clearCmpCallCardList', function(data){
											log('Clearing CmpCallCardLockList result:');
											log(data);
										});
										timeToClearLockList = false;
									}
								} else {
									callback(response);
								}
							} catch(e) {
								log({'onUnlockCmpCallCardError_PostRequest': e});
								callback(false,{
									e:e,
									msg:'Во время разблокировки талона вызова произошла ошибка. Обратитесь к администратору'
								})
							}
						});
					} catch(e) {
						log({'onUnlockCmpCallCardError': e});
						callback(false,{
							e:e,
							msg:'Во время разблокировки талона вызова произошла ошибка. Обратитесь к администратору'
						})
					}
					
				});

				// обработчик события deleteCmpCallCard - удаление талона вызова
				socket.on('deleteCmpCallCard', function (CmpCallCardId,callback) {
					try {
						// Проверяем наличие сокета в массиве проверенных сокетов смп
						if (!smpSockets[socket.id]) {
							return false;
						}
						// Рассылаем всем сокетам в авторизованной группе сообщение об удалении талона вызова с идентификатором CmpCallCardId
						io.sockets.in('authentificatedUsers').emit('deleteCmpCallCard', {'CmpCallCard_id':CmpCallCardId});
		
						// В данном случае запрос к промеду не требуется, поскольку выполнен самим клиентом
						
					} catch(e) {
						log({'onDeleteCmpCallCardError': e});
						callback(false,{
							e:e,
							msg:'Во время удаления талона вызова произошла ошибка. Обратитесь к администратору'
						})
					}
					
				});
				
				// обработчик события addCmpCallCard - добавление нового талона вызова
				
				socket.on('addCmpCallCard', function (CmpCallCardId,onError) {
					try {
						// Проверяем наличие сокета в массиве проверенных сокетов смп
						if (!smpSockets[socket.id]) {
							return false;
						}
						// Получаем данные о новом талоне вызова
						post.PostRequest({'CmpCallCard_id':CmpCallCardId},socket.session.cookies,socket.session.userAgent,'/?c=CmpCallCard&m=getCmpCallCardSmpInfo', function(data){
							try {
								response = JSON.parse(data);
								
								// Рассылаем данные о новом талоне вызова всем клиентам МО, в котором талон был добавлен
								io.sockets.in(response.Lpu_id).emit('addCmpCallCard', response);
							} catch(e) {
								log({'onDeleteCmpCallCardError_PostRequset': e});
								if (typeof onError == 'function') {
									onError({
										e:e,
										msg:'Во время удаления талона вызова произошла ошибка. Обратитесь к администратору'
									});
								}
							}
						});
					} catch(e) {
						log({'onDeleteCmpCallCardError': e});
						if (typeof onError == 'function') {
							onError({
								e:e,
								msg:'Во время удаления талона вызова произошла ошибка. Обратитесь к администратору'
							});
						}
					}
				});
				
				//После отсоединения сокета, удаляем его из массива доверенных сокетов смп
				socket.on('disconnect', function () {
					delete smpSockets[socket.id];
				});
				
				/* интервал получения бригад с координатами */
				/*
				var updateEmergencyTeamOperEnvForSmpUnitInterval = setInterval(function(){
					var d = new Date;
					if ( EmergencyTeamOperEnvForSmpUnitCache.last_update !== null && ( d - EmergencyTeamOperEnvForSmpUnitCache.last_update ) < EmergencyTeamOperEnvForSmpUnitCache.timeout ) {
						return;
					}
					
					post.PostRequest({},socket.session.cookies,socket.session.userAgent,'/?c=EmergencyTeam4E&m=loadEmergencyTeamOperEnvForSmpUnit', function(data){
						try {
							var data = JSON.parse(data),
								byMedServiceId = {};
							EmergencyTeamOperEnvForSmpUnitCache.last_update = new Date();
							
							for( key in data ){
								var id = (data[key].MedService_id)?data[key].MedService_id.toString():null;
								if ( typeof byMedServiceId[id] == 'undefined' ) {
									byMedServiceId[id] = [];
								}
								byMedServiceId[id].push(data[key]);
							}
							
							for( id in byMedServiceId ){
								io.sockets.in('MedService_'+id).emit('updateEmergencyTeamOperEnvForSmpUnit', byMedServiceId[id]);
							}						
						} catch(e) {
							log({'onEmergencyTeamOperEnvForSmpUnitCachePostRequestError': e});
						}
					});
					
				},EmergencyTeamOperEnvForSmpUnitCache.timeout);
				*/
				
			});

			log("SmpSocketServer has started.");
			log();
			var clearLockListInterval = setInterval(function() {
					timeToClearLockList = true;
			}, 3600000)
			
		} catch(e) {
			log({startSmpServerError:e});
			startSmpServer();
		}
		
	}

	/**
	* Метод запуска сокет-сервера
	* Метод запускает сокет сервер с указанным в константах портом.
	* В результате работы перечисленных в методе и подключаемых модулях обработчиков
	* событий происходит взаимодействие между сервером промед и мобильным (Web) армом
	*/
	
	function startSocketServer() {	
		
		try {
			var io = require('socket.io').listen(constants.socketServerPort);
			SOCKET_SERVER = io;
			io.sockets.on('connection', function (socket) {

				sockets.push(socket);
				socket.session = {};
				
				// Мобильный АРМ старшего бригады был заменён нативным приложением. Поэтому дальнейшая поддержка кода неочевидна
				// @TODO: Если приложение будет использоваться, необходимо проверять аутентификацию проверять в каждом действии
				
				socket.on('authentification', function(cookies, userAgent, callback){
					try {
						socket.session.cookies = cookies;
						socket.session.userAgent = userAgent;
						post.PostRequest({},socket.session.cookies, socket.session.userAgent, '/?c=MobileBrig&m=getEmergencyTeamData', function(data) {
							response = JSON.parse(data);
							if ((response.EmergencyTeam_id)&&(response.MedStaffFact_id)) {
								socket.session.EmergencyTeamId = response.EmergencyTeam_id;
								socket.session.MedStaffFact_id = response.MedStaffFact_id;
								data = {
									'EmergencyTeam_id':socket.session.EmergencyTeamId,
									'isOnline': '2'
								};

								post.PostRequest(data,socket.session.cookies, socket.session.userAgent, '/?c=MobileBrig&m=setOnlineStatus', function(data) {
									//log(data);
									post.PostRequest('',socket.session.cookies, socket.session.userAgent, '/?c=MobileBrig&m=getDiagsControlNumber', function(controlNumber) {
										callback(controlNumber);
									},socket);
								},socket);

								//TODO: Постановка первичного статуса при соединении
							} else {
								socket.emit('logout');
								socket.disconnect();
								//TODO: Дисконнект
							}
						},socket)
					} catch(e) {
						log({'onAuthentification': e});
						callback(null,e);
					}
				});

				socket.on('setStatus', function(statusId, callback){
					post.PostRequest({'EmergencyTeamStatus_id':statusId, 'EmergencyTeam_id':socket.session.EmergencyTeamId},socket.session.cookies, socket.session.userAgent, '/?c=MobileBrig&m=setBrigStatus', callback,socket)
				});

				socket.on('getStacInfo',function(sectionCode,callback) {
					post.PostRequest({'LpuSectionProfile_Code':sectionCode},socket.session.cookies, socket.session.userAgent, '/?c=AmbulanceService&m=getStacList', callback,socket)
				})

				socket.on('getDiags',function(callback){
					post.PostRequest('',socket.session.cookies, socket.session.userAgent, '/?c=MobileBrig&m=getDiags', callback,socket);
				});

				socket.on('closeCallCard', function (data, callback) {
					post.PostRequest(data,socket.session.cookies, socket.session.userAgent, '/?c=MobileBrig&m=closeCallCard', callback,socket)
				});

				socket.on('bookEmergencyBed', function (data, callback) {
					post.PostRequest(data,socket.session.cookies, socket.session.userAgent, '/?c=AmbulanceService&m=bookEmergencyBed', callback,socket)
				});

				socket.on('callAccepted', function(PersonId, callback) {
					try {
						if ((PersonId == null)) {
							callback(null,null);
						} else {
							paramsRequestEvnTree = requestEvnTree;
							paramsRequestEvnTree.Person_id = PersonId;
							paramsRequestEvnTree.object_id = PersonId;
							paramsRequestEvnTree.user_MedStaffFact_id = socket.session.MedStaffFact_id;


							paramsRequestSignalInformation = requestSignalInformation;
							paramsRequestSignalInformation.Person_id = PersonId;
							paramsRequestSignalInformation.object_value = PersonId;
							paramsRequestSignalInformation.parent_object_value = PersonId;
							paramsRequestSignalInformation.user_MedStaffFact_id = socket.session.MedStaffFact_id;


							post.PostRequest(paramsRequestEvnTree,socket.session.cookies, socket.session.userAgent, '/?c=EMK&m=getPersonEmkData',function(personEmkData){
								post.PostRequest(paramsRequestSignalInformation,socket.session.cookies, socket.session.userAgent, '/?c=Template&m=getEvnForm',function(signalInformation){
									callback(personEmkData,signalInformation);
								},socket);
							},socket);	
						}
					} catch(e) {
						log({'onCallAccepted': e});
						callback(null,null,e)
					}
				});

				socket.on('loadEvn',function (objectName,id, callback) {
					try {
						paramsRequestEventData = requestEventData;
						paramsRequestEventData.object = objectName;
						paramsRequestEventData.object_id = objectName+'_id';
						paramsRequestEventData.object_value = id;
						paramsRequestEventData.user_MedStaffFact_id = socket.session.MedStaffFact_id;
						post.PostRequest(paramsRequestEventData,socket.session.cookies, socket.session.userAgent, '/?c=Template&m=getEvnForm', callback ,socket);
					} catch(e) {
						log({'onLoadEvnError': e});
						callback(null, e);
					}
				});

				socket.on('disconnect', function () {
					try {
						//io.sockets.emit('user disconnected');
						log(socket.session.EmergencyTeamId+'[EmergencyTeam_id] disconnected');
						data = {
							'EmergencyTeam_id':socket.session.EmergencyTeamId,
							'isOnline': '1'
						};
						for (i=0; i<sockets.length; i++) {
							var tmp = sockets[i];
							if (tmp.session.EmergencyTeamId == socket.session.EmergencyTeamId) {
								sockets.splice(i,1);
							}

						}
						post.PostRequest(data,socket.session.cookies, socket.session.userAgent, '/?c=MobileBrig&m=setOnlineStatus', function(data) {
							log(data);
						},socket)
					} catch(e) {
						log({'onDisconnectError': e});
					}
				});
			});
			log("SocketServer has started.");
			log();
		} catch(e) {
			log({socketServeRerror:e});
			startSocketServer();
		}
	}

	function startPromedServer() {
		try {
			var io = require('socket.io').listen(constants.PromedSocketServerPort);
			PROMED_SERVER = io;
			io.on('connection', function(socket) {
				log('promed connect');

				socket.emit('registration', function(cookies, pmuser_id, userAgent) {
					socket.session = {
						cookies: cookies,
						pmuser_id: pmuser_id,
						userAgent: userAgent
					};

					var session_id = getSessionId(cookies);
					if (session_id) {
						promedSockets[session_id] = socket;
						socket.join('promedUsers');
					} else {
						//error
					}

					log('session_id '+session_id);
				});

				socket.on('disconnect', function() {
					log('promed disconnect');
					if (typeof socket.session == 'undefined') {
						log('session is undefined');
					} else if (typeof socket.session.cookies == 'undefined') {
						log('cookies is undefined');
						log('session:');
						log(socket.session);
					} else {
						var session_id = getSessionId(socket.session.cookies);
						delete promedSockets[session_id];
					}
					socket.leave('promedUsers');
				});
			});
			log("PromedSocketServer has started.");
			log();
		} catch(e) {
			log({socketPromedServerError:e});
			startSocketServer();
		}
	}

	//Запуск сокет сервера для взаимодействия между дестопными АРМами
	startSocketServer();
	//Запуск HTTP сервера
	startHttpServer();
	//Запуск сокет сервера для мобильного АРМа
	startSmpServer();
	
	//??
	startPromedServer();
	
	//Запуск сокет сервера для работы с нативным приложением
	apk.startAPKSocketServer( SMP_SOCKET_IO );

	//exports.startHttpServer = startHttpServer;
	//exports.startSocketServer = startSocketServer;
}
/*
//Обрабатываем непредивденные исключения
process.on('uncaughtException',function(e){
	log({arguments:arguments});

	//После непредвиденного исключения необходимо перезапустить приложение
	setTimeout(promed,10000)
})
*/
promed();
