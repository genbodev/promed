/**
 * swJavaApplets. Бибилиотка для работы с нашими java-апплетами
 * @package      Libs
 * @access       public
 * @copyright    Copyright © 2009 Swan Ltd.
 * @version      март 2011
 * @author       Марков Андрей
 */

// Данный файл не работает в старых версиях браузеров.
// Инициализация апплетов
Ext.namespace('sw.java', 'sw.Applets');

sw.Applets.CryptoProAsync = {
	verifySignedXMLAsync: function(options) {
		cadesplugin.async_spawn (function*(arg) {
			try {
				var oSignedXML = yield cadesplugin.CreateObjectAsync("CAdESCOM.SignedXML");
			} catch (err) {
				log('Failed to create CAdESCOM.SignedXML: ', err);
				sw.swMsg.alert('Ошибка', 'Нет доступа к подписанию. Убедитесь, что плагин КриптоПро установлен и имеет доступ к подписанию документов.');
				return false;
			}

			try {
				var verify = yield oSignedXML.Verify(options.xml);
			} catch (err) {
				log('Ошибка проверки подписи:', err);
				options.callback(false);
				return false;
			}

			options.callback(true);
			return true;
		});
	},
	signTextAsync: function(options) {
		cadesplugin.async_spawn (function*(arg) {
			try {
				var oStore = yield cadesplugin.CreateObjectAsync("CAPICOM.store");
			} catch (e) {
				log("Ошибка при открытии хранилища: " + sw.Applets.CryptoPro.GetErrorMessage(e));
				sw.swMsg.alert(langs('Ошибка'), langs('Нет доступа к сертификатам. Убедитесь, что плагин КриптоПро установлен и имеет доступ к хранилищу сертификатов.'));
				return;
			}
			if (!oStore) {
				log("Ошибка получения сертификатов, oStore undefined");
				sw.swMsg.alert(langs('Ошибка'), langs('Ошибка получения сертификатов. Убедитесь, что плагин КриптоПро установлен и имеет доступ к хранилищу сертификатов.'));
				return false;
			}

			try {
				yield oStore.Open();
			}
			catch (e) {
				log("Ошибка при открытии хранилища: " + sw.Applets.CryptoPro.GetErrorMessage(e));
				sw.swMsg.alert(langs('Ошибка'), langs('Нет доступа к сертификатам. Убедитесь, что плагин КриптоПро установлен и имеет доступ к хранилищу сертификатов.'));
				return false;
			}

			var CAPICOM_CERTIFICATE_FIND_SHA1_HASH = 0; // Поиск по SHA1-хэшу
			var CAPICOM_CERTIFICATE_INCLUDE_WHOLE_CHAIN = 1; // Saves the complete certificate chain.

			var all_certs = yield oStore.Certificates;
			var oCerts = yield all_certs.Find(CAPICOM_CERTIFICATE_FIND_SHA1_HASH, options.Cert_Thumbprint);
			var k = yield oCerts.Count;
			if (k == 0) {
				sw.swMsg.alert('Ошибка', 'Сертификат не найден.');
				return false;
			}

			var oCert = yield oCerts.Item(1);
			try {
				var oSigner = yield cadesplugin.CreateObjectAsync("CAdESCOM.CPSigner");
			} catch (err) {
				log('Failed to create CAdESCOM.CPSigner: ', err);
				sw.swMsg.alert('Ошибка', 'Нет доступа к подписанию. Убедитесь, что плагин КриптоПро установлен и имеет доступ к подписанию документов.');
				return false;
			}

			try {
				var oSignedData = yield cadesplugin.CreateObjectAsync("CAdESCOM.CadesSignedData");
			} catch (err) {
				log('Failed to create CAdESCOM.SignedXML: ', err);
				sw.swMsg.alert('Ошибка', 'Нет доступа к подписанию. Убедитесь, что плагин КриптоПро установлен и имеет доступ к подписанию документов.');
				return false;
			}

			if (oSigner) {
				yield oSigner.propset_Certificate(oCert);
			}
			else {
				log("Failed to create CPSigner");
				sw.swMsg.alert('Ошибка', 'Нет доступа к подписанию. Убедитесь, что плагин КриптоПро установлен и имеет доступ к подписанию документов.');
				return false;
			}

			var CADESCOM_CADES_BES = 1; // простая подпись
			var CADESCOM_BASE64_TO_BINARY = 1; // Данные будут перекодированы из Base64 в бинарный массив.
			var CAPICOM_ENCODE_BASE64 = 0; // Data is saved as a base64-encoded string.

			yield oSignedData.propset_ContentEncoding(CADESCOM_BASE64_TO_BINARY);
			yield oSignedData.propset_Content(options.text);
			yield oSigner.propset_Options(CAPICOM_CERTIFICATE_INCLUDE_WHOLE_CHAIN);

			try {
				var sSignedData = yield oSignedData.SignCades(oSigner, CADESCOM_CADES_BES, true, CAPICOM_ENCODE_BASE64);
			}
			catch (e) {
				log("Не удалось создать подпись из-за ошибки: " + sw.Applets.CryptoPro.GetErrorMessage(e));
				sw.swMsg.alert('Ошибка', "Не удалось создать подпись из-за ошибки: " + sw.Applets.CryptoPro.GetErrorMessage(e));
				return false;
			}

			yield oStore.Close();

			if (sSignedData) {
				if (options.callback) {
					options.callback(sSignedData);
				}
			}
		});
	},
	signRawTextAsync: function(options) {
		cadesplugin.async_spawn (function*(arg) {
			try {
				var oStore = yield cadesplugin.CreateObjectAsync("CAPICOM.store");
			} catch (e) {
				if (typeof options.error == 'function') {
					options.error();
				}
				log("Ошибка при открытии хранилища: " + sw.Applets.CryptoPro.GetErrorMessage(e));
				sw.swMsg.alert(langs('Ошибка'), langs('Нет доступа к сертификатам. Убедитесь, что плагин КриптоПро установлен и имеет доступ к хранилищу сертификатов.'));
				return;
			}
			if (!oStore) {
				if (typeof options.error == 'function') {
					options.error();
				}
				log("Ошибка получения сертификатов, oStore undefined");
				sw.swMsg.alert(langs('Ошибка'), langs('Ошибка получения сертификатов. Убедитесь, что плагин КриптоПро установлен и имеет доступ к хранилищу сертификатов.'));
				return false;
			}

			try {
				yield oStore.Open();
			}
			catch (e) {
				if (typeof options.error == 'function') {
					options.error();
				}
				log("Ошибка при открытии хранилища: " + sw.Applets.CryptoPro.GetErrorMessage(e));
				sw.swMsg.alert(langs('Ошибка'), langs('Нет доступа к сертификатам. Убедитесь, что плагин КриптоПро установлен и имеет доступ к хранилищу сертификатов.'));
				return false;
			}

			var CAPICOM_CERTIFICATE_FIND_SHA1_HASH = 0; // Поиск по SHA1-хэшу
			var CAPICOM_CERTIFICATE_INCLUDE_WHOLE_CHAIN = 1; // Saves the complete certificate chain.

			var all_certs = yield oStore.Certificates;
			var oCerts = yield all_certs.Find(CAPICOM_CERTIFICATE_FIND_SHA1_HASH, options.Cert_Thumbprint);
			var k = yield oCerts.Count;
			if (k == 0) {
				if (typeof options.error == 'function') {
					options.error();
				}
				sw.swMsg.alert('Ошибка', 'Сертификат не найден.');
				return false;
			}

			var oCert = yield oCerts.Item(1);

			var pubKey = yield oCert.PublicKey();
			var algo = yield pubKey.Algorithm;
			var algoOid = yield algo.Value;
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
				var oRawSignature = yield cadesplugin.CreateObjectAsync("CAdESCOM.RawSignature");
			} catch (err) {
				if (typeof options.error == 'function') {
					options.error();
				}
				log('Failed to create CAdESCOM.RawSignature: ', err);
				sw.swMsg.alert('Ошибка', 'Нет доступа к подписанию. Убедитесь, что плагин КриптоПро установлен и имеет доступ к подписанию документов.');
				return false;
			}

			try {
				var oHashedData = yield cadesplugin.CreateObjectAsync("CAdESCOM.HashedData");
			} catch (err) {
				if (typeof options.error == 'function') {
					options.error();
				}
				log('Failed to create CAdESCOM.HashedData: ', err);
				sw.swMsg.alert('Ошибка', 'Нет доступа к хэшированию. Убедитесь, что плагин КриптоПро установлен и имеет доступ к подписанию документов.');
				return false;
			}

			yield oHashedData.propset_Algorithm(digestMethod);
			yield oHashedData.propset_DataEncoding(cadesplugin.CADESCOM_BASE64_TO_BINARY);
			yield oHashedData.Hash(options.text);

			try {
				var sSignedData = yield oRawSignature.SignHash(oHashedData, oCert);
			}
			catch (e) {
				if (typeof options.error == 'function') {
					options.error();
				}
				log("Не удалось создать подпись из-за ошибки: " + sw.Applets.CryptoPro.GetErrorMessage(e));
				sw.swMsg.alert('Ошибка', "Не удалось создать подпись из-за ошибки: " + sw.Applets.CryptoPro.GetErrorMessage(e));
				return false;
			}

			yield oStore.Close();

			if (sSignedData) {
				if (options.callback) {
					options.callback(sSignedData);
				}
			}
		});
	},
	signXMLAsync: function(options) {
		cadesplugin.async_spawn (function*(arg) {
			try {
				var oStore = yield cadesplugin.CreateObjectAsync("CAPICOM.store");
			} catch (e) {
				log("Ошибка при открытии хранилища: " + sw.Applets.CryptoPro.GetErrorMessage(e));
				sw.swMsg.alert(langs('Ошибка'), langs('Нет доступа к сертификатам. Убедитесь, что плагин КриптоПро установлен и имеет доступ к хранилищу сертификатов.'));
				return;
			}
			if (!oStore) {
				log("Ошибка получения сертификатов, oStore undefined");
				sw.swMsg.alert(langs('Ошибка'), langs('Ошибка получения сертификатов. Убедитесь, что плагин КриптоПро установлен и имеет доступ к хранилищу сертификатов.'));
				return false;
			}

			try {
				yield oStore.Open();
			}
			catch (e) {
				log("Ошибка при открытии хранилища: " + sw.Applets.CryptoPro.GetErrorMessage(e));
				sw.swMsg.alert(langs('Ошибка'), langs('Нет доступа к сертификатам. Убедитесь, что плагин КриптоПро установлен и имеет доступ к хранилищу сертификатов.'));
				return false;
			}

			var CAPICOM_CERTIFICATE_FIND_SHA1_HASH = 0; // Поиск по SHA1-хэшу
			var CADESCOM_XML_SIGNATURE_TYPE_TEMPLATE = 2;

			var all_certs = yield oStore.Certificates;
			var oCerts = yield all_certs.Find(CAPICOM_CERTIFICATE_FIND_SHA1_HASH, options.Cert_Thumbprint);
			var k = yield oCerts.Count;
			if (k == 0) {
				sw.swMsg.alert('Ошибка', 'Сертификат не найден.');
				return false;
			}

			var oCert = yield oCerts.Item(1);
			try {
				var oSigner = yield cadesplugin.CreateObjectAsync("CAdESCOM.CPSigner");
			} catch (err) {
				log('Failed to create CAdESCOM.CPSigner: ', err);
				sw.swMsg.alert('Ошибка', 'Нет доступа к подписанию. Убедитесь, что плагин КриптоПро установлен и имеет доступ к подписанию документов.');
				return false;
			}

			try {
				var oSignedXML = yield cadesplugin.CreateObjectAsync("CAdESCOM.SignedXML");
			} catch (err) {
				log('Failed to create CAdESCOM.SignedXML: ', err);
				sw.swMsg.alert('Ошибка', 'Нет доступа к подписанию. Убедитесь, что плагин КриптоПро установлен и имеет доступ к подписанию документов.');
				return false;
			}

			var pubKey = yield oCert.PublicKey();
			var algo = yield pubKey.Algorithm;
			var algoOid = yield algo.Value;
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
				yield oSigner.propset_Certificate(oCert);
			}
			else {
				log("Failed to create CPSigner");
				sw.swMsg.alert('Ошибка', 'Нет доступа к подписанию. Убедитесь, что плагин КриптоПро установлен и имеет доступ к подписанию документов.');
				return false;
			}

			yield oSignedXML.propset_Content(options.xml);
			yield oSignedXML.propset_SignatureType(CADESCOM_XML_SIGNATURE_TYPE_TEMPLATE);
			yield oSignedXML.propset_SignatureMethod(signMethod);
			yield oSignedXML.propset_DigestMethod(digestMethod);

			try {
				var sSignedData = yield oSignedXML.Sign(oSigner);
			}
			catch (e) {
				log("Не удалось создать подпись из-за ошибки: " + sw.Applets.CryptoPro.GetErrorMessage(e));
				sw.swMsg.alert('Ошибка', "Не удалось создать подпись из-за ошибки: " + sw.Applets.CryptoPro.GetErrorMessage(e));
				return false;
			}

			yield oStore.Close();

			if (sSignedData) {
				if (options.callback) {
					options.callback(sSignedData);
				}
			}
		});
	},
	getCertListAsync: function(options) {
		cadesplugin.async_spawn (function*(arg) {
			try {
				var oStore = yield cadesplugin.CreateObjectAsync("CAPICOM.store");
			} catch (e) {
				log("Ошибка при открытии хранилища: " + sw.Applets.CryptoPro.GetErrorMessage(e));
				sw.swMsg.alert(langs('Ошибка'), langs('Нет доступа к сертификатам. Убедитесь, что плагин КриптоПро установлен и имеет доступ к хранилищу сертификатов.'));
				return;
			}
			if (!oStore) {
				log("Ошибка получения сертификатов, oStore undefined");
				sw.swMsg.alert(langs('Ошибка'), langs('Ошибка получения сертификатов. Убедитесь, что плагин КриптоПро установлен и имеет доступ к хранилищу сертификатов.'));
				return false;
			}

			try {
				yield oStore.Open();
			}
			catch (e) {
				log("Ошибка при открытии хранилища: " + sw.Applets.CryptoPro.GetErrorMessage(e));
				sw.swMsg.alert(langs('Ошибка'), langs('Нет доступа к сертификатам. Убедитесь, что плагин КриптоПро установлен и имеет доступ к хранилищу сертификатов.'));
				return false;
			}

			var all_certs = yield oStore.Certificates;

			if (!all_certs) {
				log("Ошибка получения сертификатов, oStore.Certificates undefined");
				sw.swMsg.alert(langs('Ошибка'), langs('Ошибка получения сертификатов. Убедитесь, что плагин КриптоПро установлен и имеет доступ к хранилищу сертификатов.'));
				return false;
			}

			var certCnt = yield all_certs.Count;
			var records = [];
			var record = {};
			var cert;
			var CAPICOM_CERT_INFO_SUBJECT_SIMPLE_NAME = 0;
			var CAPICOM_CERT_INFO_ISSUER_SIMPLE_NAME = 1;
			var CAPICOM_ENCODE_BASE64 = 0;

			for (var i = 1; i <= certCnt; i++) {
				try {
					cert = yield all_certs.Item(i);
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
					record.Cert_Base64 = yield cert.Export(CAPICOM_ENCODE_BASE64);
				}
				catch (e) {
					log("Ошибка при экспорте сертификата: " + sw.Applets.CryptoPro.GetErrorMessage(e));
				}
				try {
					var SubjectName = yield cert.SubjectName;
						
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
					record.Cert_IssuerName = yield cert.GetInfo(CAPICOM_CERT_INFO_ISSUER_SIMPLE_NAME);
				}
				catch (e) {
					log("Ошибка при получении свойства CAPICOM_CERT_INFO_ISSUER_SIMPLE_NAME: " + sw.Applets.CryptoPro.GetErrorMessage(e));
				}
				try {
					record.Cert_ValidFromDate = yield cert.ValidFromDate;
				}
				catch (e) {
					log("Ошибка при получении свойства ValidFromDate: " + sw.Applets.CryptoPro.GetErrorMessage(e));
				}
				try {
					record.Cert_ValidToDate = yield cert.ValidToDate;
				}
				catch (e) {
					log("Ошибка при получении свойства ValidToDate: " + sw.Applets.CryptoPro.GetErrorMessage(e));
				}
				try {
					record.Cert_Thumbprint = yield cert.Thumbprint;
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

			yield oStore.Close();

			if (options.callback) {
				options.callback(records);
			}
		});
	}
}