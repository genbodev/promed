
var log = function() {

	for (var i=0; i<arguments.length; i++) {
        logger.info(arguments[i]);
	}
	//console.log();
};

var config_file = configFile(process.argv);
var logger = null;

exports.config = config_file;

function configFile(args){
	var nameFile = 'constants.js';
	if(args.length > 2){
		nameFile = process.argv[2];
	}
	return nameFile;
}

function nodePortalProxy() {

	//
	// Инициализация системных модулей
	//

    var http = require("http"); // Модуль для создания http сервера и обработки http-запросов
    var https = require("https"); // Модуль для создания http сервера и обработки http-запросов
	var qs = require("querystring"); //Модуль для работы с параметрами запросов
    var log4js = require('log4js');

	//
	// Инициализация прикладных модулей
	//
	//console.log("config file httpServer = "+config_file);
	var constants = require('./'+config_file); // Модуль хренения констант: хостов, портов подключения к сервисам, логины/пароли

	//
	// Инициализация переменных
	//

	var sockets = []; // массив сокетов, ключ массива - идентификатор пункта обслуживания

    var NODE_SERVER = null;
	var SOCKET_SERVER = null;

    // настроим логирование
    log4js.configure({
        appenders: {
            console: { type: 'console'},
            out: {
            	type: 'dateFile',
				filename: constants.portal_proxy_log,
                layout: {
                    type: 'pattern',
                    pattern: '[%d] [%p] %m%n'
                },
                compress: true
			}
        },
        categories: {
            default: { appenders: [ 'out', 'console' ], level:'debug'}
        }
    });

    logger = log4js.getLogger('node-portal');

	function socketEmit(socket, data) {

		try {

			log('send message for client: ' + socket.clientData.MedPersonal_FIO + ', ElectronicService_id: ' + data.ElectronicService_id);

			socket.emit('message', data);
			return true;

		}  catch(e) {

            logger.error(e);
			socket.emit('error', {msg: e});
			return false;
		}
	}

	function roomEmit(room, data) {

		try {

			log('send message for group, ' + room.name + ':' + room.id);
			SOCKET_SERVER.sockets.in(room.name+room.id).emit('message', data);
			return true;

		}  catch(e) {

            logger.error(e);
			SOCKET_SERVER.sockets.in(room.name+room.id).emit('error', {msg: e});
			return false;
		}
	}

	// отправка сообщений группам в зависимости от полученных данных
	// @param data object данные запроса
	function emitManager(data, emitType) {

		var room = {},
			result = false;

		switch (emitType) {

			case 'service' : {

				room.name = 'ElectronicService';

				if (data.ElectronicService_id) {

					room.id = data.ElectronicService_id;
					result = roomEmit(room, data);

					// если есть параметр откуда перенаправлен талон
					if (data.fromElectronicService_id && data.fromElectronicService_id != data.ElectronicService_id) {
						room.id = data.fromElectronicService_id;
						result = roomEmit(room, data);
					}

				} else {
					log(data.message + ': ' + room.name + ' is null');
				}

				break;
			}

			case 'queue' : {

				room.name = 'ElectronicQueue';
				if (data.ElectronicQueueInfo_id) {

					room.id = data.ElectronicQueueInfo_id;
					result = roomEmit(room,data);

					if (data.sourceElectronicQueueInfo_id && data.sourceElectronicQueueInfo_id != data.ElectronicQueueInfo_id) {
						room.id = data.sourceElectronicQueueInfo_id;
						result = roomEmit(room,data);
					}

				} else {
					log(data.message + ': ' + room.name + ' is null');
				}

				break;
			}

			case 'socket' : {

				if (data.ElectronicService_id) {

					var changedSocket = sockets[data.ElectronicService_id];
					result = socketEmit(changedSocket, data);

				} else {
					log(data.message + ': ElectronicService_id is null');
				}

				break;
			}

			case 'ierarchy' : {

				if (data.ElectronicTreatment_id) {

					room.id = data.ElectronicTreatment_id;
					room.name = 'ElectronicTreatment';

					result = roomEmit(room, data);

				} else if (data.ElectronicService_id) {

					room.id = data.ElectronicService_id;
					room.name = 'ElectronicService';

					result = roomEmit(room, data);

					// если есть параметр замещаемого врача(ПО)
					if (data.msfReplaceElectronicService_id) {
						room.id = data.msfReplaceElectronicService_id;
						result = roomEmit(room, data);
					}

				} else if (data.ElectronicQueueInfo_id) {

					room.id = data.ElectronicQueueInfo_id;
					room.name = 'ElectronicQueue';

					result = roomEmit(room,data);

                    // если есть параметр замещаемого врача(ЭО)
                    if (data.msfReplaceElectronicQueueInfo_id) {
                        room.id = data.msfReplaceElectronicQueueInfo_id;
                        result = roomEmit(room, data);
                    }

				} else {
					log(data.message + ': no queue and no service');
				}

				break;
			}

            case 'selective' : {

                if (data.ElectronicTreatment_id) {
                    room.id = data.ElectronicTreatment_id;
                    room.name = 'ElectronicTreatment';

                    result = roomEmit(room, data);
                }

                if (data.ElectronicService_id) {
                    room.id = data.ElectronicService_id;
                    room.name = 'ElectronicService';

                    result = roomEmit(room, data);
                }

                // если есть параметр замещаемого врача(ПО)
                if (data.msfReplaceElectronicService_id) {
                    room.id = data.msfReplaceElectronicService_id;
                    room.name = 'ElectronicService';

                    result = roomEmit(room, data);
                }

                if (data.ElectronicQueueInfo_id) {
                    room.id = data.ElectronicQueueInfo_id;
                    room.name = 'ElectronicQueue';

                    result = roomEmit(room,data);
                }

                // если есть параметр замещаемого врача(ЭО)
                if (data.msfReplaceElectronicQueueInfo_id) {
                    room.id = data.msfReplaceElectronicQueueInfo_id;
                    room.name = 'ElectronicQueue';

                    result = roomEmit(room, data);
                }
			}
		}

		return result;
	}

	// отправка сообщений группам в зависимости от полученных данных
	// @param data object данные запроса
	function tvEmit(data) {

		var room = {},
			lb_broadcast_result = false,
			ls_broadcast_result = false;

		if (data.LpuBuilding_id) {

			room.name = 'TvBroadcastToLpuBuilding';
			room.id = data.LpuBuilding_id;

			lb_broadcast_result = roomEmit(room, data);
		}

		if (data.LpuSection_id) {

			room.name = 'TvBroadcastToLpuSection';
			room.id = data.LpuSection_id;

			ls_broadcast_result = roomEmit(room, data);
		}

        if (data.ElectronicScoreboard_id) {

            room.name = 'TvBroadcast';
            room.id = data.ElectronicScoreboard_id;

            tv_broadcast_result = roomEmit(room, data);
        }

		return lb_broadcast_result && ls_broadcast_result && tv_broadcast_result;
	}

	// Роутинг запросов к http-серверу NodeJS
	// @param data object данные запроса
	// @param response object объект ответа для http-запроса
	function route(data,response){

		var success = false;
		var securedData = {};
		var rooms = SOCKET_SERVER.sockets.adapter.rooms;

		// если версия NODE.JS > 4, необходимо такое преобразование
		// иначе ошибка hasOwnProperty() not a function при выполнении emit()
		Object.keys(data).forEach(function(obj){
			securedData[obj] = data[obj];
		});

		switch (data.message) {

			// сообщения для работы ЭО
			// case 'electronicTalonStatusHasChanged' : {
			//
			// 	success = emitManager(securedData, 'service');
			// 	break;
			// }

			case 'electronicTalonRedirected' : {
				success = emitManager(securedData, 'selective');
				break;
			}

            case 'electronicTalonStatusHasChanged' :
			case 'electronicTalonIsBusy' :
			case 'electronicTalonIsFreeForCall' :
			case 'electronicQueueDisabled' : {

				success = emitManager(securedData, 'queue');
				break;
			}

			case 'electronicTalonCreated' : {
				success = emitManager(securedData, 'ierarchy');
				break;
			}

            // сообщения для обновления данных на телевизоре с ЭО
            case 'RefreshScoreboardBrowserPage':{
                success = tvEmit(securedData);
                break;
            }


			// сообщения для обновления данных на телевизоре с расписанием
			case 'ScoreboardTimetableChangeRoom' :
			case 'ScoreboardTimetableRemoveDoctor' :
			case 'ScoreboardTimetableAppendDoctor' :
			case 'ScoreboardTimetableChangeWorktime' : {
				success = tvEmit(securedData);
				break;
			}
		}

		responseData= JSON.stringify({success: success});
		response.write(responseData);
		response.end();
	};

	// функция обработки запроса
	function onRequest(request, response) {
        var postData = "";

        request.setEncoding("utf8");

        // Обработчик ошибки запроса
        request.addListener('error',function(error){
            logger.error({startHttpServer_http_request_error:error})
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
                logger.error('Parse Error');
                var data = {
                    success: false,
                    Err_Msg: 'Parse Error'
                };
                response.write(qs.stringify(data));
                response.end();
            }
            logger.info('request params:', requestParams);
            route(requestParams, response);
        });
    }

	/**
	*
	* Метод запуска http сервера для обработки запросов из промеда
	* Метод создаёт http-сервер и обрабатывает параметры запроса,
	* в случае успешной обработки передаёт данные методу "route"
	* который определяет, что нужно сделать с запросом и какой вернуть результат
	*/
	function startHttpServer() {

        var fs = require('fs'),
        	options = {},
			log_msg = "NODE SERVER START ERROR!";

        try {

            options.key = fs.readFileSync(constants.certKey);
            options.cert = fs.readFileSync(constants.cert);

            // для того чтобы работало межсайтовое взаимодействие CORS нужно создать HTTPS сервер
            // (например между кврачу и промедом, если нод-портал сидит на другом веб-сервере)
            NODE_SERVER = https.createServer(options, function(request, response) { onRequest(request, response); }).listen(constants.httpServerPort);
            log_msg = 'NODE SERVER (HTTPS) HAS STARTED ON PORT: '

		} catch(e) {

            log('NODE SERVER START ERROR ON HTTPS!', e);
            log('STARTING NODE SERVER ON HTTP...');

        	// если возникла ошибка при чтении файлов-сертификата создаем обычный сервер
            NODE_SERVER = http.createServer(function(request, response) { onRequest(request, response); }).listen(constants.httpServerPort);
            log_msg = 'NODE SERVER (HTTP) HAS STARTED ON PORT: '
		}

		log(log_msg + constants.httpServerPort);

		// запускаем сокет сервер
		if (NODE_SERVER) startSocketServer();
	}

	/**
	* Метод запуска сокет-сервера
	* Метод запускает сокет сервер с указанным в константах портом.
	* В результате работы перечисленных в методе и подключаемых модулях обработчиков
	* событий происходит взаимодействие между сервером промед и мобильным (Web) армом
	*/

	function startSocketServer() {

		try {

			var io = require('socket.io').listen(NODE_SERVER);
			SOCKET_SERVER = io;

			io.sockets.on('connection', function (socket) {

				var clientData = socket.request._query;

				// подбираем данные о подключенном клиенте
				socket.clientData = clientData;

				// подключенный клиент и его параметры
                logger.info('socket connected:', socket.request._query);

				// если ТВ то добавляем в группу в зависимости от типа и присланных парметров
				if (clientData.tvType)  {

					// добавляем в группу по идентификатору ТВ
                    if (clientData.ElectronicScoreboard_id) {
                        socket.join('TvBroadcast' + clientData.ElectronicScoreboard_id);
                        log('client join to ElectronicScoreboard group: ' + clientData.ElectronicScoreboard_id);
					}

					// добавляем ТВ в группу рассылки по отделению
					if (clientData.LpuSection_id) {

                        var LpuSection_id = parseInt(clientData.LpuSection_id);

                        if (LpuSection_id) {
                            socket.join('TvBroadcastToLpuSection' + clientData.LpuSection_id);
                            log('client join to LpuSection group: ' + clientData.LpuSection_id);
						}
					}

                    // добавляем ТВ в группу рассылки по подразделению (только если не указано отделение)
					if (clientData.LpuBuilding_id) {

                        var LpuBuilding_id = parseInt(clientData.LpuBuilding_id);

                        if (LpuBuilding_id && (LpuSection_id === undefined || LpuSection_id == 0)) {
                            socket.join('TvBroadcastToLpuBuilding' + clientData.LpuBuilding_id);
                            log('client join to LpuBuilding group: ' + clientData.LpuBuilding_id);
                        }
					}

                    // если передан список очередей, добавляем ТВ как слушатель в группу
                    if (clientData.queueList) {

                        var queues = clientData.queueList.split(',');

                        if (queues) {
                            queues.forEach(function (eq) {

                                if (eq.length > 0) {
                                    socket.join('ElectronicQueue' + eq.trim());
                                    log('client join to ElectronicQueue group: ' + eq.trim());
                                }
                            })
                        }
                    }
				}

				// если переданы связанные поводы, добавляем сокет в группу с каждым поводом
				if (clientData.ElectronicTreatment_ids) {

					var treatments = clientData.ElectronicTreatment_ids.split(',');

					if (treatments) {
						treatments.forEach(function (thr) {

							if (thr.length > 0) {
								socket.join('ElectronicTreatment' + thr.trim());
							}
						})
					}
				}

				// добавляем его в группу связанную с его ЭО
				if (clientData.ElectronicQueueInfo_id)
					socket.join('ElectronicQueue'+clientData.ElectronicQueueInfo_id);

				// добавляем его в группу связанную с его пунктом обслуживания
				if (clientData.ElectronicService_id)
					socket.join('ElectronicService'+clientData.ElectronicService_id);

				// добавляем этот сокет в массив по ключу пункта обслуживания
				//sockets[clientData.ElectronicService_id] = socket;
			});

            log('SOCKET SERVER HAS STARTED');

		} catch(e) {
            logger.error({socketServerRerror:e});
			// startSocketServer();
		}
	}

    //Запуск HTTPS сервера
    startHttpServer();
	exports.startHttpServer = startHttpServer;
}

//Обрабатываем непредивденные исключения
process.on('uncaughtException',function(e){
    console.log({arguments:arguments});

	//После непредвиденного исключения необходимо перезапустить приложение
	// setTimeout(nodePortalProxy,5000)
});

nodePortalProxy();
