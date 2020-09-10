var constants = {

	//Хост и порт доступа от NodeJS к вебу Промеда (обычно 127.0.0.1:2080)
	promedHost : '127.0.0.1',
	promedPort : '80',

	httpServerPort : 7070,

	// для создания https сервера нужнен сертификат сервера и его ключ
	// можно указать путь до файла или положить внутри папки нода
    certKey : 'certs/server.key',
    // сам сертификат
    cert: 'certs/server.crt',

	// путь до лога
    portal_proxy_log: 'node_portal_proxy_info.log',
};

for (var key in constants) {
	exports[key] = constants[key];
};