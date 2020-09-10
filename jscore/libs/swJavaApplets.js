/**
 * swJavaApplets. Бибилиотка для работы с нашими java-апплетами
 * @package      Libs
 * @access       public
 * @copyright    Copyright © 2009 Swan Ltd.
 * @version      март 2011
 * @author       Марков Андрей
 */

// Инициализация апплетов
Ext.namespace('sw.java', 'sw.Applets');

sw.Applets.AuthApplet = {
	checkPlugin: function() {
		if ( promedCardAPI && promedCardAPI.currProMedPlug && promedCardAPI.currProMedPlug.getBrowserPlugin() && promedCardAPI.currProMedPlug.getBrowserPlugin().valid )
		{
			return true;
		}

		return false;
	},
	signDocumentRaw: function(readerName, cardType, pin, text, count) {
		if (typeof count != "number") {
			count = 1;
		}
		var signature = promedCardAPI.currProMedPlug.signDocumentRaw(readerName, cardType, pin, text);
		switch(signature.errorMessage) {
			case 'CKR_PIN_LEN_RANGE':
				signature.errorMessage = 'Указанный пин-код содержит неправильное количество символов';
				break;
			case 'CKR_PIN_EXPIRED':
			case 'CKR_PIN_LOCKED':
			case 'CKR_PIN_INCORRECT':
			case 'CKR_PIN_INVALID':
				signature.errorMessage = 'Неправильный пин-код';
				break;
			case 'CKR_ARGUMENTS_BAD':
				// в геликон сказали, что это периодическая ошибка для etoken (мол etoken виноват), поэтому попробуем ещё раз
				if (count <= 2) {
					count++;
					return sw.Applets.AuthApplet(readerName, cardType, pin, text, count);
				}
		}

		return signature;
	},
	signText: function(options) {
		if (!sw.Applets.AuthApplet.checkPlugin()) {
			var links = '<br><a href="/plugins/AuthApplet.msi">AuthApplet.msi</a>';
			if (navigator && navigator.appVersion && navigator.appVersion.indexOf("Linux") != -1) {
				links = '<br><a href="/plugins/promed-cardapi_i386.deb">promed-cardapi_i386.deb</a>' +
					'<br><a href="/plugins/promed-cardapi_amd64.deb">promed-cardapi_amd64.deb</a>';
			}
			sw.swMsg.alert('Ошибка', 'Не установлен плагин для подписания документов с помощью ЭЦП.<br>Плагин можно скачать по ссылке: ' + links);
			return false;
		}

		if (!options.text) {
			sw.swMsg.alert('Ошибка', 'Не задан текст для функции подписания.');
			return false;
		}

		if (!options.Cert_Thumbprint) {
			// не передан сертификат, подписываем первым попавшимся или выдаём ошибку?
		}

		if (!options.callback || typeof options.callback != 'function') {
			// зачем подписывать, если нет каллбэка?
			sw.swMsg.alert('Ошибка', 'Не задан callback для функции подписания.');
			return false;
		}

		// 1. ищем какие нибудь вставленные токены
		var tokenFound = false;
		var readers = promedCardAPI.currProMedPlug.getReaders();
		readers.forEach(function(reader) {
			if (tokenFound) {
				return false;
			}
			if (reader.cardPresence) { // нашли карту, пытаемся определить тип ридера
				// 2.1. определяем тип токена
				var cardType = reader.getCardTypeByATR();
				var readerName = reader.readerName;
				if (cardType) {
					var useThisToken = true;
					if (options.Cert_Thumbprint) {
						// если передан сертификат, то ищем его на токене и используем этот токен, только если
						useThisToken = false;

						// 2.2. получаем сертификаты с токена
						var certs = promedCardAPI.currProMedPlug.getCertList(readerName, cardType, "getCertList"); // пин код для получения сертификатов не нужен, передаём getCertList вместо пин кода
						certs.forEach(function(cert) {
							// 3. сравинваем сертификат с выбранным пользователем
							if (cert.hash.toLowerCase() == options.Cert_Thumbprint.toLowerCase()) {
								useThisToken = true;
							}
						});
					}

					if (useThisToken) {
						tokenFound = true;

						// 4. запрашиваем пин код
						getWnd('swPINCodeWindow').show({
							callback: function(data) {
								if (data && data.pin) {
									// 5. подписываем
									var hash = promedCardAPI.currProMedPlug.getGost341194Hash(options.text);
									var signature = sw.Applets.AuthApplet.signDocumentRaw(readerName, cardType, data.pin, hash);
									if (signature.errorMessage == "OK") {
										// всё супер, вызываем каллбэк
										options.callback(signature.documentSigned);
									} else {
										switch(signature.errorMessage) {
											case 'CKR_PIN_LEN_RANGE':
												signature.errorMessage = 'Указанный пин-код содержит неправильное количество символов';
												break;
											case 'CKR_PIN_EXPIRED':
											case 'CKR_PIN_LOCKED':
											case 'CKR_PIN_INCORRECT':
											case 'CKR_PIN_INVALID':
												signature.errorMessage = 'Неправильный пин-код';
												break;
											case 'CKR_ARGUMENTS_BAD':
												// в геликон сказали, что это периодическая ошибка для etoken (мол etoken виноват), возможно надо попробовать ещё раз
												break;
										}
										if (typeof options.error == 'function') {
											options.error();
										}
										sw.swMsg.alert('Ошибка', 'Ошибка подписи: ' + signature.errorMessage);
										return false;
									}
								} else {
									// не ввели пин код
								}
							},
							onHide: function() {
								// закрыли ввод пин кода
								if (typeof options.error == 'function') {
									options.error();
								}
							}
						});
					}
				}
			}
		});

		if (!tokenFound) {
			if (typeof options.error == 'function') {
				options.error();
			}
			sw.swMsg.alert('Ошибка', 'Токен не найден.');
			return false;
		}
	}
}

sw.Applets.CryptoPro = {
	initCryptoPro: function() {
		if (Ext.isEmpty(document.getElementById('cadesplugin'))) {
			// проверка наличия плагина
			pluginExists = false;
			if (navigator && navigator.plugins) {
				for (var i = 0; i < navigator.plugins.length; i++) {
					var plugin = navigator.plugins[i];
					if (plugin && plugin['application/x-cades']) {
						pluginExists = true;
					}
				}
			}
			if (!pluginExists) {
				return true;
			}

			Ext.getBody().createChild({
				'tag': 'object',
				'class': 'hiddenObject',
				'type': 'application/x-cades',
				'width': 0,
				'height': 0,
				'id': 'cadesplugin'
			});
		}
	},
	decimalToHexString: function(number) {
		if (number < 0) {
			number = 0xFFFFFFFF + number + 1;
		}

		return number.toString(16).toUpperCase();
	},
	GetErrorMessage: function(e) {
		var err = e.message;
		if (!err) {
			err = e;
		} else if (e.number) {
			err += " (0x" + sw.Applets.CryptoPro.decimalToHexString(e.number) + ")";
		}
		return err;
	},
	objCreator: function(name, callback) {
		try{
			switch (navigator.appName) {
				case 'Microsoft Internet Explorer':
					return new ActiveXObject(name);
				default:
					var userAgent = navigator.userAgent;
					if (userAgent.match(/Trident\/./i)) { // IE10, 11
						return new ActiveXObject(name);
					}
					if (userAgent.match(/ipod/i) || userAgent.match(/ipad/i) || userAgent.match(/iphone/i)) {
						return call_ru_cryptopro_npcades_10_native_bridge("CreateObject", [name]);
					}
					var cadesobject = document.getElementById('cadesplugin');
					if (cadesobject && typeof cadesobject.CreateObject == 'function') {
						return cadesobject.CreateObject(name);
					}
					return false;
			}
		} catch (err) {
			log(err);
		}
	},
	verifySignedXML: function(options) {
		if (!options) {
			options = {};
		}

		var canAsync = !!cadesplugin.CreateObjectAsync;
		if (canAsync) {
			if (sw.Applets.CryptoProAsync) {
				return sw.Applets.CryptoProAsync.verifySignedXMLAsync(options);
			} else {
				sw.swMsg.alert('Ошибка', 'Подписание не доступно, обновите браузер.');
				return false;
			}
		}

		try {
			var oSignedXML = sw.Applets.CryptoPro.objCreator("CAdESCOM.SignedXML");
		} catch (err) {
			log('Failed to create CAdESCOM.SignedXML: ', err);
			sw.swMsg.alert('Ошибка', 'Нет доступа к верификации. Убедитесь, что плагин КриптоПро установлен и имеет доступ к подписанию документов.');
			return false;
		}

		try {
			var verify = oSignedXML.Verify(options.xml);
		} catch (err) {
			log('Ошибка проверки подписи:', err);
			options.callback(false);
			return false;
		}

		options.callback(true);
		return true;
	},
	signText: function(options) {
		if (!options) {
			options = {};
		}

		var canAsync = !!cadesplugin.CreateObjectAsync;
		if (canAsync) {
			if (sw.Applets.CryptoProAsync) {
				return sw.Applets.CryptoProAsync.signTextAsync(options);
			} else {
				sw.swMsg.alert('Ошибка', 'Подписание не доступно, обновите браузер.');
				return false;
			}
		}

		try {
			var oStore = sw.Applets.CryptoPro.objCreator("CAPICOM.store");
			oStore.Open();
		} catch (err) {
			log('Failed to create CAPICOM.store: ', err);
			sw.swMsg.alert(langs('Ошибка'), langs('Нет доступа к сертификатам. Убедитесь, что плагин КриптоПро установлен и имеет доступ к хранилищу сертификатов.'));
			return false;
		}

		var CAPICOM_CERTIFICATE_FIND_SHA1_HASH = 0; // Поиск по SHA1-хэшу
		var CAPICOM_CERTIFICATE_INCLUDE_WHOLE_CHAIN = 1; // Saves the complete certificate chain.

		var oCerts = oStore.Certificates.Find(CAPICOM_CERTIFICATE_FIND_SHA1_HASH, options.Cert_Thumbprint);

		if (oCerts.Count == 0) {
			sw.swMsg.alert(langs('Ошибка'), langs('Сертификат не найден.'));
			return false;
		}

		var oCert = oCerts.Item(1);
		try {
			var oSigner = sw.Applets.CryptoPro.objCreator("CAdESCOM.CPSigner");
		} catch (err) {
			log('Failed to create CAdESCOM.CPSigner: ', err);
			sw.swMsg.alert(langs('Ошибка'), langs('Нет доступа к подписанию. Убедитесь, что плагин КриптоПро установлен и имеет доступ к подписанию документов.'));
			return false;
		}

		if (oSigner) {
			oSigner.Certificate = oCert;
		}
		else {
			log("Failed to create CPSigner");
			sw.swMsg.alert(langs('Ошибка'), langs('Нет доступа к подписанию. Убедитесь, что плагин КриптоПро установлен и имеет доступ к подписанию документов.'));
			return false;
		}

		var oSignedData = sw.Applets.CryptoPro.objCreator("CAdESCOM.CadesSignedData");
		var CADESCOM_CADES_BES = 1; // простая подпись
		var CADESCOM_BASE64_TO_BINARY = 2; // Данные будут перекодированы из Base64 в бинарный массив.
		var CAPICOM_ENCODE_BASE64 = 0; // Data is saved as a base64-encoded string.

		oSignedData.ContentEncoding = CADESCOM_BASE64_TO_BINARY;
		oSignedData.Content = options.text;
		oSigner.Options = CAPICOM_CERTIFICATE_INCLUDE_WHOLE_CHAIN;
		try {
			var sSignedData = oSignedData.SignCades(oSigner, CADESCOM_CADES_BES, true, CAPICOM_ENCODE_BASE64);
		}
		catch (e) {
			log("Не удалось создать подпись из-за ошибки: " + sw.Applets.CryptoPro.GetErrorMessage(e));
			sw.swMsg.alert('Ошибка', "Не удалось создать подпись из-за ошибки: " + sw.Applets.CryptoPro.GetErrorMessage(e));
			return false;
		}

		if (sSignedData) {
			if (options.callback) {
				options.callback(sSignedData);
			}
		}
	},
	signRawText: function(options) {
		if (!options) {
			options = {};
		}

		var canAsync = !!cadesplugin.CreateObjectAsync;
		if (canAsync) {
			if (sw.Applets.CryptoProAsync) {
				return sw.Applets.CryptoProAsync.signRawTextAsync(options);
			} else {
				if (typeof options.error == 'function') {
					options.error();
				}
				sw.swMsg.alert('Ошибка', 'Подписание не доступно, обновите браузер.');
				return false;
			}
		}

		try {
			var oStore = sw.Applets.CryptoPro.objCreator("CAPICOM.store");
			oStore.Open();
		} catch (err) {
			if (typeof options.error == 'function') {
				options.error();
			}
			log('Failed to create CAPICOM.store: ', err);
			sw.swMsg.alert(langs('Ошибка'), langs('Нет доступа к сертификатам. Убедитесь, что плагин КриптоПро установлен и имеет доступ к хранилищу сертификатов.'));
			return false;
		}

		var CAPICOM_CERTIFICATE_FIND_SHA1_HASH = 0; // Поиск по SHA1-хэшу
		var CAPICOM_CERTIFICATE_INCLUDE_WHOLE_CHAIN = 1; // Saves the complete certificate chain.

		var oCerts = oStore.Certificates.Find(CAPICOM_CERTIFICATE_FIND_SHA1_HASH, options.Cert_Thumbprint);

		if (oCerts.Count == 0) {
			if (typeof options.error == 'function') {
				options.error();
			}
			sw.swMsg.alert(langs('Ошибка'), langs('Сертификат не найден.'));
			return false;
		}

		var oCert = oCerts.Item(1);

		var pubKey = oCert.PublicKey();
		var algo = pubKey.Algorithm;
		var algoOid = algo.Value;
		if (algoOid == "1.2.643.7.1.1.1.1") {   // алгоритм подписи ГОСТ Р 34.10-2012 с ключом 256 бит
			digestMethod = cadesplugin.CADESCOM_HASH_ALGORITHM_CP_GOST_3411_2012_256;
		}
		else if (algoOid == "1.2.643.7.1.1.1.2") {   // алгоритм подписи ГОСТ Р 34.10-2012 с ключом 512 бит
			digestMethod = cadesplugin.CADESCOM_HASH_ALGORITHM_CP_GOST_3411_2012_512;
		}
		else if (algoOid == "1.2.643.2.2.19") {  // алгоритм ГОСТ Р 34.10-2001
			digestMethod = cadesplugin.CADESCOM_HASH_ALGORITHM_CP_GOST_3411;
		}
		else {
			if (typeof options.error == 'function') {
				options.error();
			}
			sw.swMsg.alert('Ошибка', 'Поддерживается подпись только сертификатами с алгоритмами ГОСТ Р 34.10-2012, ГОСТ Р 34.10-2001');
			return false;
		}

		try {
			var oRawSignature = sw.Applets.CryptoPro.objCreator("CAdESCOM.RawSignature");
		} catch (err) {
			if (typeof options.error == 'function') {
				options.error();
			}
			log('Failed to create CAdESCOM.RawSignature: ', err);
			sw.swMsg.alert(langs('Ошибка'), langs('Нет доступа к подписанию. Убедитесь, что плагин КриптоПро установлен и имеет доступ к подписанию документов.'));
			return false;
		}

		try {
			var oHashedData = cadesplugin.CreateObject("CAdESCOM.HashedData");
		} catch (err) {
			if (typeof options.error == 'function') {
				options.error();
			}
			log('Failed to create CAdESCOM.HashedData: ', err);
			sw.swMsg.alert(langs('Ошибка'), langs('Нет доступа к хэшированию. Убедитесь, что плагин КриптоПро установлен и имеет доступ к подписанию документов.'));
			return false;
		}

		var CADESCOM_HASH_ALGORITHM_CP_GOST_3411 = 100;
		var CADESCOM_BASE64_TO_BINARY = 1;
		oHashedData.Algorithm = digestMethod;
		oHashedData.DataEncoding = CADESCOM_BASE64_TO_BINARY;
		oHashedData.Hash(options.text);

		try {
			var sSignedData = oRawSignature.SignHash(oHashedData, oCert);
		}
		catch (e) {
			if (typeof options.error == 'function') {
				options.error();
			}
			log("Не удалось создать подпись из-за ошибки: " + sw.Applets.CryptoPro.GetErrorMessage(e));
			sw.swMsg.alert('Ошибка', "Не удалось создать подпись из-за ошибки: " + sw.Applets.CryptoPro.GetErrorMessage(e));
			return false;
		}

		if (sSignedData) {
			if (options.callback) {
				options.callback(sSignedData);
			}
		}
	},
	signXML: function(options) {
		if (!options) {
			options = {};
		}

		var canAsync = !!cadesplugin.CreateObjectAsync;
		if (canAsync) {
			if (sw.Applets.CryptoProAsync) {
				return sw.Applets.CryptoProAsync.signXMLAsync(options);
			} else {
				sw.swMsg.alert('Ошибка', 'Подписание не доступно, обновите браузер.');
				return false;
			}
		}

		try {
			var oStore = sw.Applets.CryptoPro.objCreator("CAPICOM.store");
			oStore.Open();
		} catch (err) {
			log('Failed to create CAPICOM.store: ', err);
			sw.swMsg.alert('Ошибка', 'Нет доступа к сертификатам. Убедитесь, что плагин КриптоПро установлен и имеет доступ к хранилищу сертификатов.');
			return false;
		}

		var CAPICOM_CERTIFICATE_FIND_SHA1_HASH = 0; // Поиск по SHA1-хэшу
		var CADESCOM_XML_SIGNATURE_TYPE_TEMPLATE = 2;

		var oCerts = oStore.Certificates.Find(CAPICOM_CERTIFICATE_FIND_SHA1_HASH, options.Cert_Thumbprint);

		if (oCerts.Count == 0) {
			sw.swMsg.alert('Ошибка', 'Сертификат не найден.');
			return false;
		}

		var oCert = oCerts.Item(1);
		try {
			var oSigner = sw.Applets.CryptoPro.objCreator("CAdESCOM.CPSigner");
		} catch (err) {
			log('Failed to create CAdESCOM.CPSigner: ', err);
			sw.swMsg.alert('Ошибка', 'Нет доступа к подписанию. Убедитесь, что плагин КриптоПро установлен и имеет доступ к подписанию документов.');
			return false;
		}

		try {
			var oSignedXML = sw.Applets.CryptoPro.objCreator("CAdESCOM.SignedXML");
		} catch (err) {
			log('Failed to create CAdESCOM.SignedXML: ', err);
			sw.swMsg.alert('Ошибка', 'Нет доступа к подписанию. Убедитесь, что плагин КриптоПро установлен и имеет доступ к подписанию документов.');
			return false;
		}

		var pubKey = oCert.PublicKey();
		var algo = pubKey.Algorithm;
		var algoOid = algo.Value;
		if (algoOid == "1.2.643.7.1.1.1.1") {   // алгоритм подписи ГОСТ Р 34.10-2012 с ключом 256 бит
			signMethod = "urn:ietf:params:xml:ns:cpxmlsec:algorithms:gostr34102012-gostr34112012-256";
			digestMethod = "urn:ietf:params:xml:ns:cpxmlsec:algorithms:gostr34112012-256";
		}
		else if (algoOid == "1.2.643.7.1.1.1.2") {   // алгоритм подписи ГОСТ Р 34.10-2012 с ключом 512 бит
			signMethod = "urn:ietf:params:xml:ns:cpxmlsec:algorithms:gostr34102012-gostr34112012-512";
			digestMethod = "urn:ietf:params:xml:ns:cpxmlsec:algorithms:gostr34112012-512";
		}
		else if (algoOid == "1.2.643.2.2.19") {  // алгоритм ГОСТ Р 34.10-2001
			signMethod = "urn:ietf:params:xml:ns:cpxmlsec:algorithms:gostr34102001-gostr3411";
			digestMethod = "urn:ietf:params:xml:ns:cpxmlsec:algorithms:gostr3411";
		}
		else {
			sw.swMsg.alert('Ошибка', "Поддерживается подпись только сертификатами с алгоритмами ГОСТ Р 34.10-2012, ГОСТ Р 34.10-2001");
			return false;
		}

		if (oSigner) {
			oSigner.Certificate = oCert;
		}
		else {
			log("Failed to create CPSigner");
			sw.swMsg.alert('Ошибка', 'Нет доступа к подписанию. Убедитесь, что плагин КриптоПро установлен и имеет доступ к подписанию документов.');
			return false;
		}

		oSignedXML.Content = options.xml;
		oSignedXML.SignatureType = CADESCOM_XML_SIGNATURE_TYPE_TEMPLATE;
		oSignedXML.SignatureMethod = signMethod;
		oSignedXML.DigestMethod = digestMethod;

		try {
			var sSignedData = oSignedXML.Sign(oSigner);
		}
		catch (e) {
			log("Не удалось создать подпись из-за ошибки: " + sw.Applets.CryptoPro.GetErrorMessage(e));
			sw.swMsg.alert('Ошибка', "Не удалось создать подпись из-за ошибки: " + sw.Applets.CryptoPro.GetErrorMessage(e));
			return false;
		}

		if (sSignedData) {
			if (options.callback) {
				options.callback(sSignedData);
			}
		}
	},
	getCertList: function(options) {
		if (!options) {
			options = {};
		}

		var canAsync = !!cadesplugin.CreateObjectAsync;
		if (canAsync) {
			if (sw.Applets.CryptoProAsync) {
				return sw.Applets.CryptoProAsync.getCertListAsync(options);
			} else {
				sw.swMsg.alert('Ошибка', 'Подписание не доступно, обновите браузер.');
				return false;
			}
		}

		var oStore = sw.Applets.CryptoPro.objCreator("CAPICOM.store");
		if (!oStore) {
			log("Ошибка получения сертификатов, oStore undefined");
			sw.swMsg.alert(langs('Ошибка'), langs('Ошибка получения сертификатов. Убедитесь, что плагин КриптоПро установлен и имеет доступ к хранилищу сертификатов.'));
			return false;
		}

		try {
			oStore.Open();
		}
		catch (e) {
			log("Ошибка при открытии хранилища: " + sw.Applets.CryptoPro.GetErrorMessage(e));
			sw.swMsg.alert(langs('Ошибка'), langs('Нет доступа к сертификатам. Убедитесь, что плагин КриптоПро установлен и имеет доступ к хранилищу сертификатов.'));
			return false;
		}

		if (!oStore.Certificates) {
			log("Ошибка получения сертификатов, oStore.Certificates undefined");
			sw.swMsg.alert(langs('Ошибка'), langs('Ошибка получения сертификатов. Убедитесь, что плагин КриптоПро установлен и имеет доступ к хранилищу сертификатов.'));
			return false;
		}

		var certCnt = oStore.Certificates.Count;
		var records = [];
		var record = {};
		var cert;
		var CAPICOM_CERT_INFO_SUBJECT_SIMPLE_NAME = 0;
		var CAPICOM_CERT_INFO_ISSUER_SIMPLE_NAME = 1;
		var CAPICOM_ENCODE_BASE64 = 0;

		for (var i = 1; i <= certCnt; i++) {
			try {
				cert = oStore.Certificates.Item(i);
			}
			catch (ex) {
				sw.swMsg.alert('Ошибка', "Ошибка при перечислении сертификатов: " + sw.Applets.CryptoPro.GetErrorMessage(ex));
				log("Ошибка при перечислении сертификатов: " + sw.Applets.CryptoPro.GetErrorMessage(ex));
				return false;
			}

			record = {
				Cert_id: i,
				Cert_Base64: '',
				Cert_SubjectName: '',
				Cert_IssuerName: '',
				Cert_ValidFromDate: '',
				Cert_ValidToDate: '',
				Cert_Thumbprint: ''
			};

			try {
				record.Cert_Base64 = cert.Export(CAPICOM_ENCODE_BASE64);
			}
			catch (e) {
				log("Ошибка при экспорте сертификата: " + sw.Applets.CryptoPro.GetErrorMessage(e));
			}
			try {
				var SubjectName = cert.SubjectName;

				var subjectNameAttributes = {};
				SubjectName.split(', ').forEach(function(atribute_str) {
					var attribute = atribute_str.split('=');
					subjectNameAttributes[attribute[0]] = attribute[1];
				});
				
				record.Cert_SubjectName = subjectNameAttributes["CN"];
				
				if (subjectNameAttributes["SN"] && subjectNameAttributes["G"]) {
					record.Cert_SubjectName = subjectNameAttributes["SN"] + " " + subjectNameAttributes["G"];
				}
			}
			catch (e) {
				log("Ошибка при получении SubjectName: " + sw.Applets.CryptoPro.GetErrorMessage(e));
			}
			try {
				record.Cert_IssuerName = cert.GetInfo(CAPICOM_CERT_INFO_ISSUER_SIMPLE_NAME);
			}
			catch (e) {
				log("Ошибка при получении свойства CAPICOM_CERT_INFO_ISSUER_SIMPLE_NAME: " + sw.Applets.CryptoPro.GetErrorMessage(e));
			}
			try {
				record.Cert_ValidFromDate = cert.ValidFromDate;
			}
			catch (e) {
				log("Ошибка при получении свойства ValidFromDate: " + sw.Applets.CryptoPro.GetErrorMessage(e));
			}
			try {
				record.Cert_ValidToDate = cert.ValidToDate;
			}
			catch (e) {
				log("Ошибка при получении свойства ValidToDate: " + sw.Applets.CryptoPro.GetErrorMessage(e));
			}
			try {
				record.Cert_Thumbprint = cert.Thumbprint;
			}
			catch (e) {
				log("Ошибка при получении свойства Thumbprint: " + sw.Applets.CryptoPro.GetErrorMessage(e));
			}

			if (options.allowedCertList) {
				if (record.Cert_Thumbprint && record.Cert_Thumbprint.toLowerCase().inlist(options.allowedCertList)) {
					records.push(record);
				}
			}
		}

		oStore.Close();

		if (options.callback) {
			options.callback(records);
		}
	}
}

// Получение данных с анализатора 
sw.Applets.MedLab = {
	applet: document.javaMedLab,
	response: {},
	init: function(name)
	{
		name = (name)?name:'javaMedLab';
		this.applet = document[name];
	},
	setPortName : function(port)
	{
		this.response = {};
		if (!this.applet)
		{
			this.response.ErrorCode = 1;
			this.response.ErrorMessage = langs('Не найден апплет для чтения данных анализатора.');
			return this.response;
		}
		
		if ( typeof this.applet.setPortName == 'unknown' || this.applet.setPortName )
		{
			try 
			{
				port = port || 'COM3';
				this.response.json = this.applet.setPortName(port);
				this.response.success = true;
			}
			catch (e)
			{
				this.response.ErrorCode = 11;
				this.response.ErrorMessage = langs('Ошибка при инициализации порта анализатора.');
			}
		}
		else 
		{
			this.response.ErrorCode = 2;
			this.response.ErrorMessage = langs('Апплет недоступен или метод апплета не обнаружен.');
		}
		return this.response;
	},
	connect : function()
	{
		this.response = {};
		if (!this.applet)
		{
			this.response.ErrorCode = 1;
			this.response.ErrorMessage = langs('Не найден апплет для чтения данных анализатора.');
			return this.response;
		}
		
		if ( typeof this.applet.connect == 'unknown' || this.applet.connect )
		{
			try 
			{
				this.applet.connect();
				this.response.success = true;
			}
			catch (e)
			{
				this.response.ErrorCode = 12;
				this.response.ErrorMessage = langs('Ошибка при соединении с анализатором.');
			}
		}
		else 
		{
			this.response.ErrorCode = 2;
			this.response.ErrorMessage = langs('Апплет недоступен или метод апплета не обнаружен.');
		}
		return this.response;
	},
	getResult : function()
	{
		this.response = {};
		if (!this.applet)
		{
			this.response.ErrorCode = 1;
			this.response.ErrorMessage = langs('Не найден апплет для чтения данных анализатора.');
			return this.response;
		}
		
		if ( typeof this.applet.getResult == 'unknown' || this.applet.getResult )
		{
			try 
			{
				this.response.json = this.applet.getResult();
				this.response.success = true;
			}
			catch (e)
			{
				this.response.ErrorCode = 13;
				this.response.ErrorMessage = langs('Ошибка получения данных от анализатора.');
			}
		}
		else 
		{
			this.response.ErrorCode = 2;
			this.response.ErrorMessage = langs('Апплет недоступен или метод апплета не обнаружен.');
		}
		return this.response;
	},
	 
	/** Инициализация апплета, если еще не инициализирован и получение данных с анализатора 
	 * В качестве параметра передается объект, в который будет включен апплет и выполнена инициализация апплета
	 */
	initAnalizer : function (o, name)
	{
		//var loadMask = new Ext.LoadMask(o.getEl(), { msg: 'Инициализация анализатора...' });
		//loadMask.show();
		name = (name)?name:'javaMedLab';
		if (o && typeof o == "object")
		{
			if (navigator.javaEnabled())
			{	
				if (!o.applet)
				{
					// Аплет получения данных с анализатора 
					o.applet = o.getEl().createChild({
						name: name,
						tag: 'applet',
						archive:'applets/swMedLab.jar',
						code:'swan/MedLab/swMedLabApplet.class',
						width: 0,
						height: 0,
						id: 'java_Applets_'+name,
						style:'width:1px,height:1px'
					});
					sw.Applets.MedLab.init(name);
				}
				//loadMask.hide();
				return true;
			} else {
				setPromedInfo(langs('Отсутствует java машина. Работа с картами будет недоступна.<br/>Для установки java машины зайдите на сайт <a href=http://java.com/ru>http://java.com/ru</a>'), 'javamashine-info');
			}
		}
		//loadMask.hide();
		return false;
	},
	isComplete : function() 
	{
		return (this.applet.isComplete)?this.applet.isComplete():false;
	},
	getAnalizerData : function(o)
	{
		var result = null;
		//var loadMask = new Ext.LoadMask(o.getEl(), { msg: 'Получение данных с анализатора...' });
		//loadMask.show();
		if (this.applet.isComplete)
		{
			if (this.applet.isComplete()===true) 
			{
				var result = this.applet.getResult();
				this.applet.fillResPack();
			}
		} 
		else 
		{
			sw.swMsg.alert(langs('Ошибка'), langs('Апплет не инициализирован. Повторите попытку.'), function(){});
		}
		//loadMask.hide();
		return result;
	},
	parseAnalizer : function(data) 
	{
		var result = {};
		if (data)
		{
			log('Исходные данные:');
			log(data);
			data = this.decode(data);
			// здесь должны быть правила определения разных анализаторов
			var analizer = (data.header)?data.header:'';
			log('Полученные данные:');
			log(data);
			
			switch (analizer) 
			{
				case 'ACT18!!!8246-EN-2.00013151': // название анализатора 
					result['WBC'] = data['WBC'];
					result['LY%'] = data['LY%'];
					result['MO%'] = data['MO%'];
					result['GR%'] = data['GR%'];
					result['LY#'] = data['LY#'];
					result['MO#'] = data['MO#'];
					result['GR#'] = data['GR#'];
					result['RBC'] = data['RBC'];
					result['Hgb'] = data['Hgb'];
					result['Hct'] = data['Hct'];
					result['MCV'] = data['MCV'];
					result['MCH'] = data['MCH'];
					result['MCHC'] = data['MCHC'];
					result['RDW'] = data['RDW'];
					result['Plt'] = data['Plt'];
					result['MPV'] = data['MPV'];
					result['Pct'] = data['Pct'];
					result['PDW'] = data['PDW'];
					break;
			}
		}
		return result;
	},
	/** Функция проставляет полученные с анализатора данные в шаблоне 
	 * 
	 *
	 */
	setAnalizerData : function(editor, data) 
	{
		if (editor) {
			elements = editor.document.getElementsByTag('div');
			
			for( var i=0;i<elements.count();i++)
			{
				if (elements.getItem(i).getAttribute('_cke_real_class') == 'data')
				{
					//log(elements.getItem(i).getAttribute('_cke_real_name'));
					if (data[elements.getItem(i).getAttribute('_cke_real_name')])
					{
						elements.getItem(i).setText(data[elements.getItem(i).getAttribute('_cke_real_name')]);
						//log('data=%i',data[elements.getItem(i).getAttribute('_cke_real_name')]);
					}
				}
			}
		}
	},
	/** Функция декодирования данных, полученных с анализатора в hash-table формате
	 * 
	 *
	 */
	decode : function(data)
	{
		var result={};
		if (data)
		{
			data = data.replace(/{/g,'');
			data = data.replace(/}/g,'');
			data = data.replace(/\s+/g, '');
			
			var arr = data.split(',');
			for ( var i = 0; i < arr.length; i++)
			{
				var item = arr[i].split('=');
				result[item[0].replace(/\s+$/g, '')] = item[1].replace(/\s+/g, '');
			}
		}
		return result;
	}
}

// Чтение УЭК
sw.Applets.uec = {
	checkPlugin: function() {
		// проверка наличия плагина
		this.initUec(); // инициализируем сначала

		if ( this.uecObject )
		{
			return true;
		}

		return false;
	},
	applet: document.uecapplet,
	readerActive: false,
	response: {},
	callback: null,
	/**
	 * Инициализация функции, которая будет выполняться при успешном нахождении человека
 	 * @param func выполняемая функция
	 */
	setCallback: function(func) {
		if (func && typeof func == 'function') {
			this.callback = func;
		}
	},
	/**
	 * Сброс выполняемой функции
	 */
	clearCallback: function() {
		this.callback = null;
	},
	init: function(name) {
		name = (name)?name:'uecapplet';
		this.applet = document[name];
	},
	startUecReader: function(options) {
		/*this.initUec();
		if (Ext.globalOptions.others.enable_uecreader) {
			if(this.uecIntervalObj) {
				clearInterval(this.uecIntervalObj);
			}
			log('Включаем считывание с УЭК');
			if (options && options.callback) {
				this.setCallback(options.callback);
			}
			var uecInterval = (options && options.interval)?options.interval:null || Ext.globalOptions.others.uecreader_interval;
			this.uecIntervalObj = setInterval(this.getUecData.createDelegate(this), uecInterval);
			this.readerActive = true;
		}*/
	},
	stopUecReader: function() {
		/*log('Выключаем считывание с УЭК');
		this.readerActive = false;
		if(this.uecIntervalObj) {
			clearInterval(this.uecIntervalObj);
		}*/
	},
	getErrorMessage: function(e) {
		var err = e.message;
		if (!err) {
			err = e;
		} else if (e.number) {
			err += " (" + e.number + ")";
		}
		return err;
	},
	/**
	 * Чтение карты и проверка статуса
	 */
	getUecStatus: function () {
		var response = {success: false, ErrorCode: 1, ErrorMessage: 'Ошибка работы с УЭК.'};
		// проверяем наличие плагина
		if ( this.uecObject )
		{
			// проверка на наличие карт ридера
			try{
				var test = this.uecObject.UECardWelcomeText; // пробуем получить приветсвие :)
				// далее необходим ввод пин-кода

				if (test != undefined) {
					response.success = true;
					response.ErrorCode = null;
					response.ErrorMessage = null;
				} else {
					response.ErrorCode = 3;
					response.ErrorMessage = langs('Не найдена карта');
				}
			} catch (err) {
				response.ErrorCode = 3;
				response.ErrorMessage = this.getErrorMessage(err);
			}
		}
		else
		{
			response.ErrorCode = 2;
			response.ErrorMessage = langs('Не найден плагин КриптоПро.');
		}
		return response;
	},
	/**
	 * Чтение карты
	 */
	getUecData: function (options, params) {
		if (options && options.callback) {
			this.setCallback(options.callback);
		}

		var successRead = false;

		var oCard = this.uecObject;
		
		// убрать этот дебаг код
		/*var response = {};
		response.uecNum = '';
		response.surName = 'ИВАНОВ';
		response.firName = 'ИВАН';
		response.secName = 'ИВАНОВИЧ';
		response.birthDay = '11.12.1967';
		response.polisNum = '10645326';
		response.success = true;
		response.ErrorCode = null;
		response.ErrorMessage = null;
		this.stopUecReader();
		this.getPerson(response, options);
		return false;*/
		// END убрать этот дебаг код
		
		this.uecData = this.getUecStatus();
		log(this.uecData);
		if (this.uecData.success)
		{
			// остановим чтение карты
			this.stopUecReader();
			// необходим ввод пин-кода
			successRead = true;
			getWnd('swPINCodeWindow').show({
				params: params,
				callback: function(data) {
					if (data && data.pin) {
						var successRead = false;

						try {
							oCard.SetPin1(data.pin);
							var oCardholderData = oCard.CardholderData;
							var response = {};
							response.uecNum = '';
							response.surName = oCardholderData.CardholderLastName;
							response.firName = oCardholderData.CardholderFirstName;
							response.secName = oCardholderData.CardholderSecondName;
							response.birthDay = '';
							if (oCardholderData.DateOfBirth.length == 8) {
								response.birthDay = oCardholderData.DateOfBirth.substring(6) + '.' + oCardholderData.DateOfBirth.substring(4,6) + '.' + oCardholderData.DateOfBirth.substring(0,4);
							}
							response.polisNum = oCardholderData.OMSNumber;
							response.success = true;
							response.ErrorCode = null;
							response.ErrorMessage = null;
							if (!response.ErrorCode) {
								successRead = true;
								this.getPerson(response, options);
							}
						} catch (err) {
							var p = {msg: this.getErrorMessage(err)};
							this.getUecData(options, p);
							log(err);
						}

						if (!successRead && options.onErrorRead) {
							options.onErrorRead();
						}
					} else {
						this.startUecReader();
					}
				}.createDelegate(this),
				onHide: function() {
					this.startUecReader();
				}.createDelegate(this)
			});
		}

		return successRead;
	},
	/**
	 * Идентификация пользователя
	 */
	getPerson: function(data, options) {
		Ext.Ajax.request({
			failure: function(response, options) {
			},
			params: {
				Person_SurName: data.surName,
				Person_FirName: data.firName,
				Person_SecName: data.secName,
				Person_BirthDay: data.birthDay,
				Polis_Num: data.polisNum
			},
			success: function(response, options) {
				var response_obj = Ext.util.JSON.decode(response.responseText);
				if (response_obj[0] && response_obj[0].Person_id) {
					log('Нашли персона в БД: '+response_obj[0].Person_id);
				} else {
					response_obj[0] = null;
				}
				// даже если ничего не нашли возвращаем данные с карты в калбек
				// если использовать тут sw.swMsg.alert, то сообщение может закрыться другим сообщением вызыванным с формы через sw.swMsg.alert.
				/*Ext.Msg.alert('Внимание', 'Считывание УЭК произведено, достаньте карту.<br>Для продолжения считывания нажмите "ОК".', function() {
					this.startUecReader();
				}.createDelegate(this));*/
				if (options && options.callback) {
					options.callback(data, response_obj[0]);
				} else {
					if (this.callback) {
						this.callback(data, response_obj[0]);
					}
				}
			}.createDelegate(this),
			url: '?c=Person&m=getPersonByUecData'
		});
	},
	/** Инициализация апплета, если еще не инициализирован и получение данных с анализатора
	 * В качестве параметра передается объект, в который будет включен апплет и выполнена инициализация апплета
	 */
	initUec: function () {
		this.uecObject = null; // при инициализации всегда пересоздаем объект
		if (!this.uecObject) 
		{
			this.uecObject = sw.Applets.CryptoPro.objCreator("CAdESCOM.UECard");
			if (!this.uecObject) {
				setPromedInfo(langs('Отсутствует КриптоПро УЭК CSP. Работа с картами УЭК будет недоступна.'), 'cryptoproplugin-info');
			}
		}

		return true;
	}
}

// Работа с AuthApi aka ARAService (https://localhost:8088) - сервисом, сделанным Святославом Ильиных.
sw.Applets.AuthApi = {
	signText: function(options) {
		if (!options.text) {
			sw.swMsg.alert('Ошибка', 'Не задан текст для функции подписания.');
			return false;
		}

		if (!options.Cert_Thumbprint) {
			// не передан сертификат, подписываем первым попавшимся или выдаём ошибку?
		}

		if (!options.callback || typeof options.callback != 'function') {
			// зачем подписывать, если нет каллбэка?
			sw.swMsg.alert('Ошибка', 'Не задан callback для функции подписания.');
			return false;
		}

		if (getOthersOptions().doc_signtype && getOthersOptions().doc_signtype == 'authapitomee') {
			authApi.setTomEE(true);
		} else {
			authApi.setTomEE(false);
		}

		var win = options.win;

		getWnd('swPINCodeWindow').show({
			savePinObject: options.Cert_Thumbprint,
			callback: function(data) {
				if (data && data.pin) {
					// 1. ищем какие нибудь вставленные токены
					if (win) {
						win.getLoadMask('Поиск токена').show();
					}
					authApi.findToken({
						pin: data.pin,
						Cert_Thumbprint: options.Cert_Thumbprint,
						callback: function(response) {
							if (win) {
								win.getLoadMask().hide();
							}
							if (response.errorMessage) {
								if (typeof options.error == 'function') {
									options.error();
								}
								sw.swMsg.alert('Ошибка', 'Ошибка подписания с помощью ЭЦП: ' + response.errorMessage);
								return false;
							} else if (response.reader == null) {
								if (typeof options.error == 'function') {
									options.error();
								}
								sw.swMsg.alert('Ошибка', 'Ошибка подписания с помощью ЭЦП: Не найдено устройство ЭЦП');
								return false;
							}

							if (win) {
								win.getLoadMask('Подписание с помощью токена').show();
							}
							authApi.sign({
								cades: options.cades,
								tokenType: response.reader.tokenType,
								ReaderName: response.reader.ReaderName,
								keyId: response.reader.keyId,
								pin: data.pin,
								dataB64: options.text,
								callback: function(data) {
									if (win) {
										win.getLoadMask().hide();
									}

									if (data.errorMessage == "") {
										options.callback(data.signature);
									} else {
										if (typeof options.error == 'function') {
											options.error();
										}
										switch(data.errorMessage.replace(/ .*/g, '')) {
											case 'CKR_PIN_LEN_RANGE':
												data.errorMessage = 'Указанный пин-код содержит неправильное количество символов';
												if (sw.savedPinCode && sw.savedPinCode[options.Cert_Thumbprint]) {
													delete sw.savedPinCode[options.Cert_Thumbprint];
												}
												break;
											case 'CKR_PIN_EXPIRED':
											case 'CKR_PIN_LOCKED':
											case 'CKR_PIN_INCORRECT':
											case 'CKR_PIN_INVALID':
												data.errorMessage = 'Неправильный пин-код';
												if (sw.savedPinCode && sw.savedPinCode[options.Cert_Thumbprint]) {
													delete sw.savedPinCode[options.Cert_Thumbprint];
												}
												break;
										}
										sw.swMsg.alert('Ошибка', 'Ошибка подписи: ' + data.errorMessage);
										return false;
									}
								}
							});
						}
					});
				} else {
					// не ввели пин код
				}
			},
			onHide: function() {
				// закрыли ввод пин кода
				if (typeof options.error == 'function') {
					options.error();
				}
			}
		});
	},
	getEPoliceData: function(options) { // чтение данных с эл.полиса или с карты жителя
		var doc_readcardtype = getOthersOptions().doc_readcardtype;
		if (doc_readcardtype && doc_readcardtype.inlist(['authapi', 'authapitomee'])) {
			if (doc_readcardtype == 'authapitomee') {
				authApi.setTomEE(true);
			} else {
				authApi.setTomEE(false);
			}

			var me = this;
			// получаем список ридеров
			authApi.getPCSCList({
				callback: function (response) {
					// ищем ридер с правильным ATR
					for (var k in response.readers) {
						var reader = response.readers[k];
						if (reader.ATR && reader.ATR.inlist(authApi.ATRCodes.EPolice)) {
							// читаем с ридера данные карты
							authApi.readCardInf({
								ReaderName: reader.ReaderName,
								callback: function (data) {
									if (data && data.personResult && data.personResult.PoliceNum) {
										var response = {};
										response.bdzNum = '';
										response.surName = data.personResult.surname;
										response.firName = data.personResult.firname;
										response.secName = data.personResult.secname;
										response.birthDay = data.personResult.birthDay ? data.personResult.birthDay.substring(0, 10) : null;
										response.polisNum = data.personResult.PoliceNum;
										response.polisBegDate = data.personResult.cardBegDate ? data.personResult.cardBegDate.substring(0, 10) : null;
										response.polisEndDate = data.personResult.cardEndDate ? data.personResult.cardEndDate.substring(0, 10) : null;
										response.sex = data.personResult.gender;
										response.smoOgrn = data.polisResult.smoOGRN;
										response.smoOkato = data.polisResult.smoOKATO;
										response.snils = data.personResult.SNILS;
										response.success = true;
										response.ErrorCode = null;
										response.ErrorMessage = null;
										me.getPerson(response, options);
									} else if (data && data.errorMessage) {
										sw.swMsg.alert('Ошибка', data.errorMessage);
									}
								}
							});
							return;
						} else if (reader.ATR && reader.ATR.inlist(authApi.ATRCodes.TatCard)) {
							// читаем с ридера данные карты
							authApi.readTatCard({
								ReaderName: reader.ReaderName,
								callback: function (data) {
									if (data && data.personResult && data.personResult.PoliceNum) {
										var response = {};
										response.bdzNum = '';
										response.surName = data.personResult.surname;
										response.firName = data.personResult.firname;
										response.secName = data.personResult.secname;
										response.birthDay = data.personResult.birthDay ? data.personResult.birthDay.substring(0, 10) : null;
										response.polisNum = data.personResult.PoliceNum;
										response.polisBegDate = data.personResult.cardBegDate ? data.personResult.cardBegDate.substring(0, 10) : null;
										response.polisEndDate = data.personResult.cardEndDate ? data.personResult.cardEndDate.substring(0, 10) : null;
										response.sex = data.personResult.gender;
										response.smoOgrn = data.polisResult.smoOGRN;
										response.smoOkato = data.polisResult.smoOKATO;
										response.snils = data.personResult.SNILS;
										response.success = true;
										response.ErrorCode = null;
										response.ErrorMessage = null;
										me.getPerson(response, options);
									} else if (data && data.errorMessage) {
										sw.swMsg.alert('Ошибка', data.errorMessage);
									}
								}
							});
							return;
						}
					}

					if (options && typeof options.callback == 'function') {
						options.callback();
					}
				}
			});
		} else {
			if (sw.Applets.bdz.checkPlugin()) {
				var successRead = sw.Applets.bdz.getBdzData(options);
				if (!successRead) {
					if (options && typeof options.callback == 'function') {
						options.callback();
					}
				}
			} else if (options && typeof options.callback == 'function') {
				options.callback();
			}
		}
	},
	getPerson: function(data, options) {
		var me = this;
		if (typeof (barcodeScannerLogging) === "function") {
			barcodeScannerLogging(data);
		}
		Ext.Ajax.request({
			failure: function(response, opt) {
				if (options && typeof options.callback == 'function') {
					options.callback();
				}
			},
			params: {
				Person_SurName: data.surName,
				Person_FirName: data.firName,
				Person_SecName: data.secName,
				Person_BirthDay: data.birthDay,
				Polis_Num: data.polisNum
			},
			success: function(response, opt) {
				var response_obj = Ext.util.JSON.decode(response.responseText);
				if (response_obj[0] && response_obj[0].Person_id) {
					log('Нашли персона в БД: ' + response_obj[0].Person_id);
					if (options && typeof options.callback == 'function') {
						options.callback(data, response_obj[0]);
					}
				} else {
					sw.swMsg.show({
						buttons: Ext.Msg.YESNO,
						fn: function(buttonId) {
							this.formStatus = 'edit';
							if (buttonId == 'yes') {
								Ext.Ajax.request({
									failure: function(resp, options) {
									},
									params: {
										Org_OGRN: data.smoOgrn,
										Org_OKATO: data.smoOkato
									},
									success: function(resp, options) {
										var resp_obj = Ext.util.JSON.decode(resp.responseText);
										if (!resp_obj[0]) {
											resp_obj[0] = null;
										}
										var params = {};
										params.action = 'add';
										params.fields = {
											'Person_SurName': data.surName.toUpperCase(),
											'Person_FirName': data.firName.toUpperCase(),
											'Person_SecName': data.secName.toUpperCase(),
											'Person_BirthDay': data.birthDay,
											'Federal_Num': data.polisNum,
											'PersonSex_id': data.sex,
											'Polis_begDate': data.polisBegDate,
											'Polis_endDate': data.polisEndDate,
											'PolisType_id': 4,
											'Person_SNILS': data.snils
										};
										if (resp_obj[0] != null && resp_obj[0].OMSSprTerr_id) {
											params.fields.OMSSprTerr_id = resp_obj[0].OMSSprTerr_id;
											params.fields.OrgSMO_id = resp_obj[0].OrgSMO_id
										}

										params.callback = me.callback;
										params.onHide = function() {

										};
										getWnd('swPersonEditWindow').show(params);

									},
									url: '?c=Person&m=getOrgSMO'
								})
							}
						}.createDelegate(this),
						icon: Ext.Msg.QUESTION,
						msg: langs('Данного человека нет в БД. Добавить нового человека?'),
						title: 'Ошибка'
					});
					return false;
				}
			}.createDelegate(this),
			url: '?c=Person&m=getPersonByBdzData'
		});
	}
};

// Работа с ViPNet PKI Client (Web Unit)
sw.Applets.ViPNetPKI = {
	signText: function(options) {
		if (!options.text) {
			sw.swMsg.alert('Ошибка', 'Не задан текст для функции подписания.');
			return false;
		}

		if (!options.Cert_Base64) {
			// не передан сертификат, подписываем первым попавшимся или выдаём ошибку?
		}

		if (!options.callback || typeof options.callback != 'function') {
			// зачем подписывать, если нет каллбэка?
			sw.swMsg.alert('Ошибка', 'Не задан callback для функции подписания.');
			return false;
		}

		var win = options.win;

		var client = new LssClient(jQuery).withBypass();
		var signOptions = {
			base64Data: options.text,
			description: 'Документ',
			documentName: 'doc',
			fileExtension: 'pdf',
			base64Certificate: options.Cert_Base64,
			isAttached: false
		};

		client.sign(signOptions).done(function(response) {
			log(response);

			if (response.IsSuccessful) {
				options.callback(response.SignedData);
			} else {
				sw.swMsg.alert('Ошибка', response.ErrorMessage);
				if (typeof options.error == 'function') {
					options.error();
				}
				return false;
			}
		})
		.fail(function(error) {
			sw.swMsg.alert('Ошибка', 'Ошибка при вызове метода подписания, убедитесь, что установлен ViPNet PKI Client (Web Unit).');
			log(error);
			if (typeof options.error == 'function') {
				options.error();
			}
			return false;
		});
	}
};

// Чтение электронного полиса
sw.Applets.bdz = {
	checkPlugin: function() {
		// проверка наличия плагина
		if ( promedCardAPI && promedCardAPI.currProMedPlug && promedCardAPI.currProMedPlug.getBrowserPlugin() && promedCardAPI.currProMedPlug.getBrowserPlugin().valid )
		{
			return true;
		}

		return false;
	},
	readerActive: false,
	response: {},
	callback: null,
	/**
	 * Инициализация функции, которая будет выполняться при успешном нахождении человека
 	 * @param func выполняемая функция
	 */
	setCallback: function(func) {
		if (func && typeof func == 'function') {
			this.callback = func;
		}
	},
	/**
	 * Сброс выполняемой функции
	 */
	clearCallback: function() {
		this.callback = null;
	},
	startBdzReader: function(options) {
		/*if (Ext.globalOptions.others.enable_bdzreader) {
			if(this.bdzIntervalObj) {
				clearInterval(this.bdzIntervalObj);
			}
			log('Включаем считывание электронного полиса');
			if (options && options.callback) {
				this.setCallback(options.callback);
			}
			var bdzInterval = (options && options.interval)?options.interval:null || Ext.globalOptions.others.uecreader_interval;
			this.bdzIntervalObj = setInterval(this.getBdzData.createDelegate(this), bdzInterval);
			this.readerActive = true;
		}*/
	},
	stopBdzReader: function() {
		/*log('Выключаем считывание электронного полиса');
		this.readerActive = false;
		if(this.bdzIntervalObj) {
			clearInterval(this.bdzIntervalObj);
		}*/
	},
	getErrorMessage: function(e) {
		var err = e.message;
		if (!err) {
			err = e;
		} else if (e.number) {
			err += " (" + e.number + ")";
		}
		return err;
	},
	/**
	 * Чтение карты и проверка статуса
	 */
	getBdzStatus: function () {
		var response = {success: false, ErrorCode: 1, ErrorMessage: langs('Ошибка работы с электронным полисом.')};
		// проверяем наличие плагина
		if ( promedCardAPI && promedCardAPI.currProMedPlug )
		{
			try{
				var result = promedCardAPI.currProMedPlug.findCard(2);
				if (result && result.errorMessage == "OK") {
					response.success = true;
					response.ErrorCode = null;
					response.ErrorMessage = null;
				} else {
					response.ErrorCode = result.errorCode;
					response.ErrorMessage = result.errorMessage;
				}
			} catch (err) {
				response.ErrorCode = 3;
				response.ErrorMessage = this.getErrorMessage(err);
			}
		}
		else
		{
			response.ErrorCode = 2;
			response.ErrorMessage = langs('Ошибка, остутствует плагин CardApi.');
		}

		return response;
	},
	/**
	 * Чтение карты
	 */
	getBdzData: function (options, params) {
		if (options && options.callback) {
			this.setCallback(options.callback);
		}

		var successRead = false;

		var bdzApplet = this;
		this.bdzData = this.getBdzStatus();
		log(this.bdzData);
		if (this.bdzData.success)
		{
			promedCardAPI.currProMedPlug.getCardData("", 2, "123", function(result) {
				if (result.polisNum != null) {
					try {
						bdzApplet.stopBdzReader();

						var response = {};
						response.bdzNum = '';
						response.surName = result.surname;
						response.firName = result.firname;
						response.secName = result.secname;
						response.birthDay = result.birthday.format('d.m.Y');
						response.polisNum = result.polisNum;
						response.polisBegDate = result.polisBegDate;
						response.polisEndDate = (result.polisEndDate < result.polisBegDate)?'':result.polisEndDate;
						response.sex = result.sex;
						response.smoName = result.smoName;
						response.smoOgrn = result.smoOgrn;
						response.smoOkato = result.smoOkato;
						response.snils = result.snils;
						response.success = true;
						response.ErrorCode = null;
						response.ErrorMessage = null;
						if (!response.ErrorCode) {
							successRead = true;
							bdzApplet.getPerson(response, options);
						}
					} catch (err) {
						log(err);
					}
				}
			});
		}

		return successRead;
	},
	/**
	 * Идентификация пользователя
	 */
	getPerson: function(data, options) {
        var _this = this;
        _this.flag = null;
        if (typeof (barcodeScannerLogging) === "function") {
			barcodeScannerLogging(data);
		}
		Ext.Ajax.request({
			failure: function(response, options) {
			},
			params: {
				Person_SurName: data.surName,
				Person_FirName: data.firName,
				Person_SecName: data.secName,
				Person_BirthDay: data.birthDay,
				Polis_Num: data.polisNum
			},
			success: function(response, options) {
				var response_obj = Ext.util.JSON.decode(response.responseText);
				if (response_obj[0] && response_obj[0].Person_id) {
					log('Нашли персона в БД: '+response_obj[0].Person_id);
					// todo: функция которая должна выполняться this.callback
					// если использовать тут sw.swMsg.alert, то сообщение может закрыться другим сообщением вызыванным с формы через sw.swMsg.alert.
					/*Ext.Msg.alert('Внимание', 'Считывание электронного полиса произведено, достаньте карту.<br>Для продолжения считывания нажмите "ОК".', function() {
						this.startBdzReader();
					}.createDelegate(this));*/
					if (options && options.callback) {
						options.callback(data, response_obj[0]);
					} else if (this.callback) {
                        this.callback(data, response_obj[0]);
                    }
				} else {
                    sw.swMsg.show({
                        buttons: Ext.Msg.YESNO,
                        fn: function(buttonId) {
                            this.formStatus = 'edit';
                            if ( buttonId == 'yes' ) {
								Ext.Ajax.request({
									failure: function(resp, options) {
									},
									params: {
										Org_OGRN:data.smoOgrn,
										Org_OKATO:data.smoOkato
									},
									success: function(resp, options) {
										var resp_obj = Ext.util.JSON.decode(resp.responseText);
										if(!resp_obj[0]){
											resp_obj[0] = null;
										}
										var params = {};
										params.action = 'add';
										params.fields = {
											'Person_SurName': data.surName.toUpperCase(),
											'Person_FirName': data.firName.toUpperCase(),
											'Person_SecName': data.secName.toUpperCase(),
											'Person_BirthDay': data.birthDay,
											'Federal_Num': data.polisNum,
											'PersonSex_id' : data.sex,
											'Polis_begDate' : data.polisBegDate,
											'Polis_endDate' : data.polisEndDate,
											//'OrgSMO_Name' : data.smoName;
											'PolisType_id':4,
											'Person_SNILS' : data.snils
										};
										if(resp_obj[0]!=null&&resp_obj[0].OMSSprTerr_id){
											params.fields.OMSSprTerr_id=resp_obj[0].OMSSprTerr_id;
											params.fields.OrgSMO_id=resp_obj[0].OrgSMO_id
										}
										
										params.callback = _this.callback;
										params.onHide = function() {
											_this.startBdzReader();
										};
										getWnd('swPersonEditWindow').show(params);
										
									},
									url: '?c=Person&m=getOrgSMO'
								})
                                
                            } else {
                                this.startBdzReader(); // стартовать опрос лучше всего в каллбэке
                            }
                        }.createDelegate(this),
                        icon: Ext.Msg.QUESTION,
                        msg: langs('Данного человека нет в БД. Добавить нового человека?'),
                        title: 'Ошибка'
                    });
                    return false;
                    //sw.swMsg.alert('Ошибка', 'Данного человека нет в БД', function(){});
                }
			}.createDelegate(this),
			url: '?c=Person&m=getPersonByBdzData'
		});
	},
	/** Инициализация апплета, если еще не инициализирован и получение данных с анализатора
	 * В качестве параметра передается объект, в который будет включен апплет и выполнена инициализация апплета
	 */
	initBdz: function () {
		return true;
	}
}

// Чтение штрих-кода
sw.Applets.BarcodeScaner = {
	url: 'https://localhost:8443/ScanCodeService/',
	port: '',
	ARMType: null,
	callback: Ext.emptyFn,
	init: function(name) {
		// ok
	},

	/**
	 *	Инициализация функции, которая будет выполняться при успешном нахождении человека
 	 *	@param func выполняемая функция
	 */
	setCallback: function(func) {
		if ( func && typeof func == 'function' ) {
			this.callback = func;
		}
	},

	/**
	 *	Сброс выполняемой функции
	 */
	clearCallback: function() {
		this.callback = Ext.emptyFn;
	},

	startBarcodeScaner: function(options) {
		if ( !Ext.globalOptions.others.enable_barcodereader ) {
			return false;
		}

		if ( this.barcodeIntervalObj ) {
			clearInterval(this.barcodeIntervalObj);
		}

		log('Включаем считывание штрих-кода');

		log('options');
		log(options);

        this.readObject = '';

        if(options && options.readObject)
        {
            this.readObject = options.readObject;
        }

		this.ARMType = (options && options.ARMType)?options.ARMType:null;

		if ( options && typeof options.callback == 'function' ) {
			this.setCallback(options.callback);
		}

		var barcodeInterval = ((options && options.barcodeInterval ? options.barcodeInterval : null) || (Ext.globalOptions.others.barcodereader_interval));

		this.barcodeIntervalObj = setInterval(this.openBarcodeScanerPort.createDelegate(this), barcodeInterval);
		this.port = (Ext.globalOptions.others.barcodereader_port || '');

		log('Порт: ' + (this.port || '(не указан)'));

		$.ajax({
			url: this.url + 'scancode/readCode?comPort='+this.port,
			dataType: 'jsonp',
			jsonpCallback: 'callback',
			success: function(data) {
				// ok
			}
		});
	},

	/**
	 *	Запуск прослушивания порта
	 */
	openBarcodeScanerPort: function(options) {
		//this.readObject = 'recept';
		log('Попытка чтения штрих-кода...');

		var me = this;
		$.ajax({
			url: this.url + 'scancode/readCode/status',
			dataType: 'jsonp',
			jsonpCallback: 'callback',
			success: function(data) {
				if (data.status) {
					log('Удачно!');

					switch (me.readObject) {
						case 'recept_code':
							var data = new Object();

							$.ajax({
								url: me.url + 'scancode/readCode/code',
								dataType: 'jsonp',
								jsonpCallback: 'callback',
								success: function(response) {
									if (response.code) {
										var code = response.code;

										// отбрасываем p в начале
										log('code', code);
										code = code.substring(1);
										// отбрасываем 2 символа в конце (перевод каретки)
										code = code.substring(0, code.length - 2);
										// пробелы меняем на плюсы
										code = code.replace(/\s/g, "+");
										// декодируем
										var string = swBase64Decode(code);
										// формируем бинарную строку
										var binary_string = '';
										var temp = '';
										for (i = 0; i < string.length; i++) {
											temp = string[i].charCodeAt(0);
											if (temp > 0xFF) {
												temp -= 0x350;
											}
											temp = temp.toString(2);
											while (temp.length < 8) temp = "0" + temp;
											binary_string += temp;
										}
										log('binary_string', binary_string);

										// достаём версию - это последние 19 бит
										var version = parseInt(binary_string.substring(binary_string.length - 19), 2);
										switch (version) {
											case 6:
												// 760 бит всего
												data.medpersonal_lpu_ogrn = parseInt(binary_string.substring(0, 50), 2).toString();
												data.medpersonal_code = swGetCharStrFromBinary(binary_string.substring(50, 106)).toString();
												data.lpu_ogrn = parseInt(binary_string.substring(106, 156), 2).toString();
												data.lpu_code = swGetCharStrFromBinary(binary_string.substring(156, 212)).toString();
												data.evn_recept_ser = swGetCharStrFromBinary(binary_string.substring(212, 324)).toString();
												data.evn_recept_num = parseInt(binary_string.substring(324, 388), 2).toString();
												data.diag_code = swGetCharStrFromBinary(binary_string.substring(388, 444)).toString();
												data.recept_finance_code = parseInt(binary_string.substring(444, 446), 2).toString();
												data.recept_discount_code = parseInt(binary_string.substring(446, 447), 2).toString();
												data.drug_is_mnn = parseInt(binary_string.substring(447, 448), 2).toString();
												data.drug_mnn_torg_code = parseInt(binary_string.substring(448, 492), 2).toString();
												data.person_snils = parseInt(binary_string.substring(492, 529), 2).toString();
												data.drug_dose = swGetCharStrFromBinary(binary_string.substring(529, 689)).toString();
												data.drug_dose_count = parseInt(binary_string.substring(689, 713), 2).toString();
												data.privilege_type_code = parseInt(binary_string.substring(713, 723), 2).toString();
												data.recept_valid_code = parseInt(binary_string.substring(723, 724), 2).toString();
												data.evn_recept_set_year = parseInt(binary_string.substring(724, 731), 2).toString();
												data.evn_recept_set_month = parseInt(binary_string.substring(731, 735), 2).toString();
												data.evn_recept_set_day = parseInt(binary_string.substring(735, 740), 2).toString();
												data.drug_is_kek = parseInt(binary_string.substring(740, 741), 2).toString();
												data.version = parseInt(binary_string.substring(741), 2).toString();

												// код срока действия: если 0 - то 2, если 1 - то 1.
												if (data.recept_valid_code == 0) {
													data.recept_valid_code = 2;
												}

												data.evn_recept_set_date = data.evn_recept_set_year + '-' + data.evn_recept_set_month + '-' + data.evn_recept_set_day;
												break;
											case 7:
												// 776 бит всего
												data.medpersonal_lpu_ogrn = parseInt(binary_string.substring(0, 50), 2).toString();
												data.medpersonal_code = swGetCharStrFromBinary(binary_string.substring(50, 106)).toString();
												data.lpu_ogrn = parseInt(binary_string.substring(106, 156), 2).toString();
												data.lpu_code = swGetCharStrFromBinary(binary_string.substring(156, 212)).toString();
												data.evn_recept_ser = swGetCharStrFromBinary(binary_string.substring(212, 324)).toString();
												data.evn_recept_num = parseInt(binary_string.substring(324, 388), 2).toString();
												data.diag_code = swGetCharStrFromBinary(binary_string.substring(388, 444)).toString();
												data.recept_finance_code = parseInt(binary_string.substring(444, 446), 2).toString();
												data.recept_discount_code = parseInt(binary_string.substring(446, 447), 2).toString();
												data.drug_is_mnn = parseInt(binary_string.substring(447, 448), 2).toString();
												data.drug_mnn_torg_code = parseInt(binary_string.substring(448, 492), 2).toString();
												data.person_snils = parseInt(binary_string.substring(492, 529), 2).toString();
												data.drug_dose = swGetCharStrFromBinary(binary_string.substring(529, 689)).toString();
												data.drug_dose_count = parseInt(binary_string.substring(689, 713), 2).toString();
												data.privilege_type_code = parseInt(binary_string.substring(713, 723), 2).toString();
												data.recept_valid_code = parseInt(binary_string.substring(723, 730), 2).toString();
												data.evn_recept_set_year = parseInt(binary_string.substring(730, 737), 2).toString();
												data.evn_recept_set_month = parseInt(binary_string.substring(737, 741), 2).toString();
												data.evn_recept_set_day = parseInt(binary_string.substring(741, 746), 2).toString();
												data.drug_is_kek = parseInt(binary_string.substring(746, 747), 2).toString();
												data.person_ident_type = parseInt(binary_string.substring(747, 751), 2).toString();
												data.whs_document_cost_item_type_code = parseInt(binary_string.substring(751, 757), 2).toString();
												data.version = parseInt(binary_string.substring(757), 2).toString();

												data.evn_recept_set_date = data.evn_recept_set_year + '-' + data.evn_recept_set_month + '-' + data.evn_recept_set_day;
												break;
											default:
												log('Неизвестная версия');
												return false;
										}

										log('распарсили штрих-код рецепта', data);
										me.callback(data);
									}

									if (typeof (barcodeScannerLogging) === "function") {
										barcodeScannerLogging(data);
									}
									me.port = (Ext.globalOptions.others.barcodereader_port || '');
									$.ajax({
										url: me.url + 'scancode/readCode?comPort='+me.port,
										dataType: 'jsonp',
										jsonpCallback: 'callback',
										success: function(data) {
											// ok
										}
									});
								}
							});

							break;
						case 'EAN':
							$.ajax({
								url: me.url + 'scancode/readCode/code',
								dataType: 'jsonp',
								jsonpCallback: 'callback',
								success: function(response) {
									if (response.code) {
										getWnd('swEvnReceptRlsProvideWindow').getDrugFromScanner(response.code);
									}
								}
							});
							break;
						default:
							$.ajax({
								url: me.url + 'scancode/readCode/polisObject',
								dataType: 'jsonp',
								jsonpCallback: 'callback',
								success: function(response) {
									if (response.polisNumber) {
										var data = new Object();

										data.Person_Surname = response.surName;
										data.Person_Firname = response.firName;
										data.Person_Secname = response.secName;
										data.Person_Birthday = response.birthDate;
										data.Sex_Code = response.sex;
										data.Polis_Num = response.polisNumber;
										data.Polis_endDate = response.expireDate;

										if (me.ARMType && me.ARMType.inlist(['common', 'stac', 'priem'])) {
											me.callback(data);
										} else {
											me.getPerson(data);
										}
									}

									if (typeof (barcodeScannerLogging) === "function") {
										barcodeScannerLogging(data);
									}
									me.port = (Ext.globalOptions.others.barcodereader_port || '');
									$.ajax({
										url: me.url + 'scancode/readCode?comPort='+me.port,
										dataType: 'jsonp',
										jsonpCallback: 'callback',
										success: function(data) {
											// ok
										}
									});
								}
							});

							break;
					}
				}
			}
		});
	},

	stopBarcodeScaner: function() {
		if ( this.barcodeIntervalObj ) {
			clearInterval(this.barcodeIntervalObj);
		}

		$.ajax({
			url: this.url + 'scancode/readCode/closePort',
			dataType: 'jsonp',
			jsonpCallback: 'callback',
			success: function(data) {
				// ok
			}
		});

		log('Чтение штрих-кода приостановлено');
	},

	/**
	 *	Идентификация пациента
	 */
	getPerson: function(data, options) {
		Ext.Ajax.request({
			failure: function(response, options) {
				//
			},
			params: data,
			success: function(response, options) {
				var response_obj = Ext.util.JSON.decode(response.responseText);

				if ( response_obj[0] && response_obj[0].Person_id ) {
					log('Нашли человека в БД: ' + response_obj[0].Person_id);
					if(response_obj[0].resultType==1){
						alert(langs('Пациент был найден по коду ОМС'));
					}
					if(response_obj[0].resultType==2){
						alert(langs('Пациент не найден по коду ОМС. Был произведен поиск по ФИО и дате рождения'));
					}
					// todo: функция которая должна выполняться this.callback
					if ( options && options.callback ) {
						options.callback(data, response_obj[0]);
					}
					else {
						if ( this.callback ) {
							this.callback(data, response_obj[0]);
						}
					}
				}
				else{	//В рамках задач http://redmine.swan.perm.ru/issues/22161 и http://redmine.swan.perm.ru/issues/22891
					alert(langs('Человек не найден в БД. Будет открыта форма Человек: Добавление'));
					getWnd('swPersonEditWindow').show({
						action: 'add',
						fields: {
							'Person_SurName': data.Person_Surname,
							'Person_FirName': data.Person_Firname,
							'Person_SecName': data.Person_Secname,
							'Person_BirthDay': data.Person_Birthday,
							'Federal_Num': data.Polis_Num,
							'PersonSex_id': (data.Sex_Code)?data.Sex_Code:null
						}
					});
				}
			}.createDelegate(this),
			url: '?c=Person&m=getPersonByBarcodeData'
		});
	},

	/**
	 *	Получение списка COM-портов
	 */
	getPortList: function(callback) {
		log('Получение списка COM-портов...');

		var portList = false;

		$.ajax({
			url:  this.url + 'scancode/portList',
			dataType: 'jsonp',
			jsonpCallback: 'callback',
			success: function(data) {
				var result = new Array();

				if (data.portList && data.portList.toString().length > 0) {
					var i;
					var portArray = data.portList.toString().split(",");
					var rec;

					for (i = 0; i < portArray.length; i++) {
						rec = new Array(portArray[i], portArray[i]);
						result.push(rec);
					}
				}

				callback(result);
			}
		});
	},

	/**
	 *	Инициализация апплета, если еще не инициализирован
	 *	В качестве параметра передается объект, в который будет включен апплет и выполнена инициализация апплета
	 */
	initBarcodeScaner: function (o, name) {
		// ok
		return true;
	}
}

/**
 * Общие функции для работы с ридерами
 */
sw.Applets.commonReader = {
	startReadersAdvanced: function(options) {
		if (options && options.uec) {
			sw.Applets.uec.startUecReader(options.uec);
		} else {
			sw.Applets.uec.startUecReader();
		}

		if (options && options.barcode) {
			sw.Applets.BarcodeScaner.startBarcodeScaner(options.barcode);
		} else {
			sw.Applets.BarcodeScaner.startBarcodeScaner();
		}

		if (options && options.bdz) {
			sw.Applets.bdz.startBdzReader(options.bdz);
		}
		else {
			sw.Applets.bdz.startBdzReader();
		}
	},
	startReaders: function(options) {
		if ( typeof options != 'object' ) {
			options = new Object();
		}
		sw.Applets.uec.startUecReader(options);
		sw.Applets.BarcodeScaner.startBarcodeScaner(options);
		sw.Applets.bdz.startBdzReader(options);
	},
	stopReaders: function() {
		sw.Applets.uec.stopUecReader();
		sw.Applets.bdz.stopBdzReader();
		sw.Applets.BarcodeScaner.stopBarcodeScaner();
	}
}