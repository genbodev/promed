authApi = {
	serviceUrl: 'https://localhost:8088/AraService/',
	setTomEE: function(isTomEE) {
		if (isTomEE) {
			this.serviceUrl = 'https://localhost:8443/ScanCodeService/scancode/';
		} else {
			this.serviceUrl = 'https://localhost:8088/AraService/';
		}
	},
	isDebug: (IS_DEBUG == 1),
	log: function() {
		if (this.isDebug) {
			console.log.apply(console, arguments);
		}
	},
	getReaderList: function(options) {
		var me = this;
		var readers = [];
		// получаем список ридеров из сервиса
		jQuery.jsonp({
			url: me.serviceUrl + 'EP_Bridge/getReaderList',
			dataType: 'jsonp',
			type: 'GET',
			callback: "devicecallback",
			success: function (data) {
				if (data && data.readers) {
					readers = data.readers;
				}

				if (options && typeof options.callback == 'function') {
					options.callback(readers);
				}
			},
			error: function () {
				if (options && typeof options.callback == 'function') {
					options.callback(readers);
				}
			}
		});
	},
	getPCSCList: function(options) {
		var me = this;
		var readers = [];
		// получаем список ридеров из сервиса
		jQuery.jsonp({
			url: me.serviceUrl + 'PCSC/List',
			dataType: 'jsonp',
			type: 'GET',
			callback: "devicecallback",
			success: function (data) {
				if (data && data.readers) {
					readers = data.readers;
				}

				if (options && typeof options.callback == 'function') {
					options.callback({
						readers: readers
					});
				}
			},
			error: function () {
				if (options && typeof options.callback == 'function') {
					options.callback({
						errorMessage: 'Не установлен плагин. Плагин можно скачать по ссылке: <a href="/plugins/AuthSetup86.msi">AuthSetup86.msi</a>.',
						readers: readers
					});
				}
			}
		});
	},
	getCertificates: function(options) {
		var me = this;
		var certificates = [];
		// получаем список ридеров из сервиса
		jQuery.jsonp({
			url: me.serviceUrl + me.Bridges[options.tokenType] + '/Certificates?slot=' + encodeURIComponent(options.ReaderName) + '&PIN=' + encodeURIComponent(options.pin),
			dataType: 'jsonp',
			type: 'GET',
			callback: "devicecallback",
			success: function (data) {
				if (data && data.certificates) {
					certificates = data.certificates;
				}

				// преобразуем keyId в key, чтобы было как в jaCarta.
				for (var k in certificates) {
					if (certificates[k].key_id) {
						certificates[k].key = certificates[k].key_id;
					}
				}

				if (options && typeof options.callback == 'function') {
					options.callback(certificates);
				}
			},
			error: function () {
				if (options && typeof options.callback == 'function') {
					options.callback(certificates);
				}
			}
		});
	},
	getCSPCertificates: function(options) {
		var me = this;
		var certificates = [];
		// получаем список ридеров из сервиса
		jQuery.jsonp({
			url: me.serviceUrl + me.Bridges[options.tokenType] + '/Certificates',
			dataType: 'jsonp',
			type: 'GET',
			callback: "devicecallback",
			success: function (data) {
				if (data && data.certificates) {
					for (var k in data.certificates) {
						if (data.certificates[k].key_id && data.certificates[k].key_id.indexOf('SCARD\\') >= 0) {
							// преобразуем key_id в keyId
							if (data.certificates[k].key_id) {
								data.certificates[k].keyId = data.certificates[k].key_id;
							}
							certificates.push(data.certificates[k]);
						}
					}
				}

				if (options && typeof options.callback == 'function') {
					options.callback(certificates);
				}
			},
			error: function () {
				if (options && typeof options.callback == 'function') {
					options.callback(certificates);
				}
			}
		});
	},
	ATRCodes: {
		EPolice: [
			"3BF71300008131FE45464F4D534F4D53A9",
			"3BD911008131FE8D0000464F4D53312E3132"
		],
		TatCard: [
			"3B6F00000031C068435350454D560300079000",
			"3B6F00000031C068435350454D5603000F9000"
		],
		jaCarta: [
			"3BDC18FF8191FE1FC38073C821136601061159000128"
		],
		eToken: [
			"3BD518008131FE7D8073C82110F4"
		],
		ruToken: [
			"3B8B015275746F6B656E20445320C1"
		]
	},
	Bridges: {
		jaCarta: "Jacarta",
		eToken: "Jacarta", // eToken ГОСТ работает так же, как и JaCarta
		ruToken: "Rutoken",
		CSP: "CSP"
	},
	readCardInf: function(options) {
		var me = this;
		jQuery.jsonp({
			url: me.serviceUrl + 'EP_Bridge/readCardInf?reader=' + options.ReaderName,
			dataType: 'jsonp',
			type: 'GET',
			callback: "devicecallback",
			success: function (data) {
				if (options && typeof options.callback == 'function') {
					options.callback(data);
				}
			},
			error: function () {
				if (options && typeof options.callback == 'function') {
					options.callback();
				}
			}
		});
	},
	readTatCard: function(options) {
		var me = this;
		jQuery.jsonp({
			url: me.serviceUrl + 'PCSC/readTatCard?reader=' + options.ReaderName,
			dataType: 'jsonp',
			type: 'GET',
			callback: "devicecallback",
			success: function (data) {
				if (options && typeof options.callback == 'function') {
					options.callback(data);
				}
			},
			error: function () {
				if (options && typeof options.callback == 'function') {
					options.callback();
				}
			}
		});
	},
	findTokenByCert: function(options) {
		var me = this;
		if (options.readers && options.readers.length > 0) {
			// берём из options.readers токен
			var reader = options.readers.shift();
			// ищем в нём сертификат
			if (reader.IsReady && reader.ATR) {
				var token = null;
				for (var j in me.ATRCodes) {
					if (
						jQuery.inArray(reader.ATR, me.ATRCodes[j]) >= 0
						&& (!options.tokenType || options.tokenType == j)
					) {
						token = reader;
						token.tokenType = j;
					}
				}
				if (token) {
					authApi.getCertificates({
						pin: options.pin,
						tokenType: token.tokenType,
						ReaderName: token.ReaderName,
						callback: function(certs) {
							for (var i = 0; i < certs.length; i++) {
								if (!options.Cert_Thumbprint || certs[i].hash.toLowerCase() == options.Cert_Thumbprint.toLowerCase()) {
									token.keyId = certs[i].key;
									options.callback({
										reader: token
									});
									return;
								}
							}

							// переходим к поиску сертификата в следующем токене
							me.findTokenByCert(options);
						}
					});
				} else {
					// переходим к поиску сертификата в следующем токене
					me.findTokenByCert(options);
				}
			} else {
				// переходим к поиску сертификата в следующем токене
				me.findTokenByCert(options);
			}
		} else if (options.tokenType && options.tokenType == 'CSP') {
			authApi.getCSPCertificates({
				tokenType: options.tokenType,
				callback: function(certs) {
					for (var i = 0; i < certs.length; i++) {
						if (!options.Cert_Thumbprint || certs[i].hash.toLowerCase() == options.Cert_Thumbprint.toLowerCase()) {
							options.callback({
								reader: certs[i]
							});
							return;
						}
					}

					options.callback({
						reader: null
					});
				}
			});
		} else {
			options.callback({
				reader: null
			});
		}
	},
	findToken: function(options) {
		var me = this;
		var token = null;

		if (options.tokenType && options.tokenType == 'CSP') {
			me.findTokenByCert(options);
		} else {
			me.getPCSCList({
				callback: function (response) {
					if (response.errorMessage) {
						options.callback({
							errorMessage: response.errorMessage,
							reader: null
						});
					} else {
						// нужен ещё сертификат
						options.readers = response.readers;
						me.findTokenByCert(options);
					}
				}
			});
		}
	},
	sign: function(options) {
		var me = this;

		var url = 'Sign'; // сырая подпись
		if (options.cades) {
			url = '/CADES/BC'; // подпись в формате CADES-BES
		}

		jQuery.ajax({
			url: me.serviceUrl + me.Bridges[options.tokenType] + url + '?slot=' + encodeURIComponent(options.ReaderName) + '&keyId=' + encodeURIComponent(options.keyId) + '&PIN=' + encodeURIComponent(options.pin),
			data: JSON.stringify({
				dataB64: options.dataB64
			}),
			type: 'POST',
			contentType: 'application/json',
			dataType: 'json',
			success: function(data) {
				if (options && typeof options.callback == 'function') {
					if (typeof data == 'object') {
						options.callback(data);
					} else {
						options.callback({
							errorMessage: 'Неверный ответ сервиса'
						});
					}
				}
			},
			error: function(jqXHR, textStatus, errorThrown) {
				// в старых версиях плагина POST не поддерживался, поэтому попробуем отправить как раньше (jsonp)
				jQuery.jsonp({
					url: me.serviceUrl + me.Bridges[options.tokenType] + '/Sign?slot=' + encodeURIComponent(options.ReaderName) + '&keyId=' + encodeURIComponent(options.keyId) + '&PIN=' + encodeURIComponent(options.pin) + '&dataB64=' + encodeURIComponent(options.dataB64),
					dataType: 'jsonp',
					type: 'GET',
					callback: "devicecallback",
					success: function (data) {
						if (options && typeof options.callback == 'function') {
							if (typeof data == 'object') {
								options.callback(data);
							} else {
								options.callback({
									errorMessage: 'Неверный ответ сервиса'
								});
							}
						}
					},
					error: function () {
						if (options && typeof options.callback == 'function') {
							options.callback({
								errorMessage: 'Сервис AuthApi не доступен'
							});
						}
					}
				});
			}
		});
	},
	signString: function(options) {
		var me = this;
		me.findToken({
			tokenType: options.tokenType,
			pin: options.pin,
			callback: function(response) {
				if (response.errorMessage) {
					if (options && typeof options.callback == 'function') {
						options.callback({
							errorMessage: response.errorMessage
						});
					}
					return false;
				} else if (response.reader == null) {
					if (options && typeof options.callback == 'function') {
						options.callback({
							errorMessage: 'Не найдено устройство ЭЦП'
						});
					}
					return false;
				}

				// подписываем
				me.sign({
					tokenType: options.tokenType,
					ReaderName: response.reader.ReaderName,
					keyId: response.reader.keyId,
					pin: options.pin,
					dataB64: btoa(options.message),
					callback: options.callback
				});
			}
		});
	}
};