/**
 * Модуль отправки http-сообщений
 */

var log = function() {
	for (var i=0; i<arguments.length; i++) {
		console.log(arguments[i]);
	}
	console.log();
};

var config_file = require('./httpServer.js').config;
console.log("config file httpPost = "+config_file);

//	
// Инициализация системных модулей
//

var http = require("http"); // Модуль для создания http сервера и обработки http-запросов
var querystring = require("querystring"); //Модуль для работы с параметрами запросов

//
// Инициализация прикладных модулей
//
	
//var constants = require('./constants.js'); // Модуль хренения констант: хостов, портов подключения к сервисам, логины/пароли
var constants = require('./'+config_file); // Модуль хренения констант: хостов, портов подключения к сервисам, логины/пароли

// Метод получения заголовков HTTP запроса по умолчанию
function getDefaultHeaders() {
	return {
		'Accept':'*/*',
		'Accept-Charset':'UTF-8,*;q=0.5',
		'Accept-Encoding':'deflate',
		'Accept-Language':'ru-RU,ru;q=0.8,en-US;q=0.6,en;q=0.4',
		'Content-Type':'application/x-www-form-urlencoded; charset=UTF-8',
		'X-Requested-With':'XMLHttpRequest'
	}
}

var default_http_method = 'POST'; // "GET"

/**
 *
 * Универсальный метод HTTP запроса
 *
 * @param options object параметры запроса :
 		options.path string url запроса
 		options.headers object объект заголовков запроса
 		options.method string метод запроса [POST,GET]
 * @param data object данные передаваемые в запросе
 * @param stringify_data boolean флаг преобразования данных в строку
 * @param onEnd function функция вызываемая в результате успешного выполнения запроса
 * @param onError function функция вызываемая в результате ошибки выполнения запроса
 *
 **/
function request(options, data, stringify_data, onEnd, onError) {
	
	try {
		// Проверка входных параметров
		if (!options.path) {
			throw {input_params_error: 'nopath'};
		}
		
		options.headers = options.headers || getDefaultHeaders();
		options.method = options.method || default_http_method;
		
		if (stringify_data) {
			data = querystring.stringify(data);
		}
		
		var request = http.request(options, function(res) {
			try {
				res.setEncoding('utf8');
				var acceptedData = '';
				res.on('data', function (chunk) {  
					try {
						acceptedData +=chunk;
					}catch (e) {
						log({'on_data_request_error':e,host:options.host,path:options.path});
					}
				});
				res.on('end', function() {
					try {
						if (typeof onEnd === 'function') {
							onEnd(acceptedData);
						}
					}catch (e) {
						log({'on_end_request_error':e,host:options.host,path:options.path});
					}
				})
			} catch(e) {
				log({'request_error':e,host:options.host,path:options.path});
			}
		});
		
		request.on('error',function(error){
			if (typeof onError === 'function') {
				onError(error);
			}
			log({http_request_error:arguments})
		});
		
		request.write(data);
		request.end();
		
	} catch(e) {
		console.log({postRequest_Error:e});
		if (typeof onError === 'function') {
			onError(e);
		}
	}
}



/**
 *
 * Метод отправки POST-запроса к промеду с инициализацией разрыва 
 * соединения передаваемого сокета при ошибке
 *
 * @param data object данные передаваемые на сервер промеда
 * @param cookie string строка c данными cookies клиента, инициирующего запрос
 * @param userAgent string строка-заголовок User-Agent клиента, инициирующего запрос
 * @param path string строка пути запроса к промеду (напр. : "\?c=Controller&m=Method")
 * @param callback function функция, вызываемая после выполнения запроса
 * @param socket object (socket) объект сокета клиента, инициирующего запрос
 *
 **/
function PostRequest(data, cookie, userAgent, path, callback, socket) {
	var post_options = {};
	try {
		var post_data = querystring.stringify(data);
		
		post_options = {
			host: constants.promedHost,
			port: constants.promedPort,
			path: path,
			method: 'POST',
			headers: {
				'Accept':'*/*',
				'Accept-Charset':'UTF-8,*;q=0.5',
				'Accept-Encoding':'deflate',
				'Accept-Language':'ru-RU,ru;q=0.8,en-US;q=0.6,en;q=0.4',
				'Connection':'close',
				'Content-Length': post_data.length,
				'Content-Type':'application/x-www-form-urlencoded; charset=UTF-8',
				'Cookie':(typeof cookie !== 'undefined')?cookie:'',
				//'Cookie':cookie,
				'User-Agent': (typeof userAgent !== 'undefined')?userAgent:'',
				//'User-Agent': userAgent,
				'X-Requested-With':'XMLHttpRequest'
			}
		};

		var post_req = http.request(post_options, function(res) {		
			try {
				res.setEncoding('utf8');
				var acceptedData = '';
				res.on('data', function (chunk) {  
					acceptedData +=chunk;  
					//console.log('Response: ' + chunk);
				});
				res.on('end', function() {
					if (acceptedData=='{Action: \'logout\'}') {
						//socket.emit('logout');
						//socket.disconnect();
						return false;
					}
					if (typeof callback == 'function') {
						callback(acceptedData);
					} else {
						console.log('Type of callback is not a function');
						console.log('Type of callback is '+typeof callback);
						console.log(callback);
						//socket.emit('logout');
						//socket.disconnect();
						return false;
					}
				})
			} catch(e) {
				log({PostRequest_error:e});
			}
		});
		post_req.on('error',function(){
			log({http_request_error:arguments})
		});
		// post the data
		post_req.write(post_data);
		//console.log(post_data);
		post_req.end();
	} catch(e) {
		log({onPostrequestError: e});
		log({'onPostrequestErrorOptions':post_options});
		e.data = data;
		e.path = path;
		callback(null,e)
	}
}

exports.PostRequest = PostRequest;
exports.request = request;