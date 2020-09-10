var constants = {
	
	//Хост и порт доступа от NodeJS к вебу Промеда (обычно 127.0.0.1:2080)
	promedHost : '127.0.0.1',
	promedPort : '80',

	httpServerPort : 9900,
	socketServerPort : 9999,

	PromedSocketServerPort : 9995,

	APK_SocketServerPort: 7777,
	PROMED_API_HOST : '46.146.246.89',
	PROMED_API_PORT : '8084',
	PROMED_AUTH_PATH : '/swan-api/rest-api/directory/SocStatus',
	//PROMED_AUTH_PATH : '/swan-api/rest-api/login/check',

	SMPSocketServerPort : 8888,
	SMPHttpServerPort : 8800

}

for (var key in constants) {
	
	exports[key] = constants[key];
	
};