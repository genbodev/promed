function CertificateAdjuster()
{
}

CertificateAdjuster.prototype.extract = function(from, what)
{
	certName = "";

	var begin = from.indexOf(what);

	if(begin>=0)
	{
		var end = from.indexOf(', ', begin);
		certName = (end<0) ? from.substr(begin) : from.substr(begin, end - begin);
	}

	return certName;
}

CertificateAdjuster.prototype.Print2Digit = function(digit)
{
	return (digit<10) ? "0"+digit : digit;
}

CertificateAdjuster.prototype.GetCertDate = function(paramDate)
{
	var certDate = new Date(paramDate);
	return this.Print2Digit(certDate.getUTCDate())+"."+this.Print2Digit(certDate.getMonth()+1)+"."+certDate.getFullYear() + " " +
		this.Print2Digit(certDate.getUTCHours()) + ":" + this.Print2Digit(certDate.getUTCMinutes()) + ":" + this.Print2Digit(certDate.getUTCSeconds());
}

CertificateAdjuster.prototype.GetCertName = function(certSubjectName)
{
	return this.extract(certSubjectName, 'CN=');
}

CertificateAdjuster.prototype.GetIssuer = function(certIssuerName)
{
	return this.extract(certIssuerName, 'CN=');
}

CertificateAdjuster.prototype.GetCertInfoString = function(certSubjectName, certFromDate)
{
	return this.extract(certSubjectName,'CN=') + "; Выдан: " + this.GetCertDate(certFromDate);
}

function CheckForPlugIn_Async() {
	document.getElementById('PluginEnabledImg').setAttribute("src", "img/green_dot.png");
	document.getElementById('PlugInEnabledTxt').innerHTML = "Плагин загружен.";
	var CurrentPluginVersion;
	cadesplugin.async_spawn(function *() {
		var oAbout = yield cadesplugin.CreateObjectAsync("CAdESCOM.About");
		CurrentPluginVersion = yield oAbout.PluginVersion;
		document.getElementById('PlugInEnabledTxt').innerHTML += " Версия плагина: " + (yield CurrentPluginVersion.toString());
	}); //cadesplugin.async_spawn
}

function FillCertList_Async(lstId) {
	cadesplugin.async_spawn(function *() {
		var MyStoreExists = true;
		try {
			var oStore = yield cadesplugin.CreateObjectAsync("CAdESCOM.Store");
			if (!oStore) {
				alert("Create store failed");
				return;
			}

			yield oStore.Open();
		}
		catch (ex) {
			MyStoreExists = false;
		}

		var lst = document.getElementById(lstId);
		if(!lst)
		{
			return;
		}
		lst.boxId = lstId;

		var certCnt;
		var certs;
		if (MyStoreExists) {
			try {
				certs = yield oStore.Certificates;
				certCnt = yield certs.Count;
			}
			catch (ex) {
				alert("Ошибка при получении Certificates или Count: " + cadesplugin.getLastError(ex));
				return;
			}
			for (var i = 1; i <= certCnt; i++) {
				var cert;
				try {
					cert = yield certs.Item(i);
				}
				catch (ex) {
					alert("Ошибка при перечислении сертификатов: " + cadesplugin.getLastError(ex));
					return;
				}

				var oOpt = document.createElement("OPTION");
				var dateObj = new Date();
				try {
					var ValidFromDate = new Date((yield cert.ValidFromDate));
					oOpt.text = new CertificateAdjuster().GetCertInfoString(yield cert.SubjectName, ValidFromDate);
				}
				catch (ex) {
					alert("Ошибка при получении свойства SubjectName: " + cadesplugin.getLastError(ex));
				}
				try {
					//oOpt.value = yield cert.Thumbprint;
					oOpt.value = global_selectbox_counter
					global_selectbox_container.push(cert);
					global_isFromCont.push(false);
					global_selectbox_counter++;
				}
				catch (ex) {
					alert("Ошибка при получении свойства Thumbprint: " + cadesplugin.getLastError(ex));
				}

				lst.options.add(oOpt);
			}

			yield oStore.Close();
		}

		//В версии плагина 2.0.13292+ есть возможность получить сертификаты из
		//закрытых ключей и не установленных в хранилище
		try {
			yield oStore.Open(cadesplugin.CADESCOM_CONTAINER_STORE);
			certs = yield oStore.Certificates;
			certCnt = yield certs.Count;
			if(certCnt == 0)
				return;
			for (var i = 1; i <= certCnt; i++) {
				var cert = yield certs.Item(i);
				//Проверяем не добавляли ли мы такой сертификат уже?
				var found = false;
				for (var j = 0; j < global_selectbox_container.length; j++)
				{
					if ((yield global_selectbox_container[j].Thumbprint) === (yield cert.Thumbprint))
					{
						found = true;
						break;
					}
				}
				if(found)
					continue;
				var oOpt = document.createElement("OPTION");
				var ValidFromDate = new Date((yield cert.ValidFromDate));
				oOpt.text = new CertificateAdjuster().GetCertInfoString(yield cert.SubjectName, ValidFromDate);
				oOpt.value = global_selectbox_counter;
				global_selectbox_container.push(cert);
				global_isFromCont.push(true);
				global_selectbox_counter++;
				lst.options.add(oOpt);
			}
			yield oStore.Close();

		}
		catch (ex) {
		}
		if(global_selectbox_container.length == 0) {
			document.getElementById("boxdiv").style.display = '';
		}
	});//cadesplugin.async_spawn
}

function RawSign_Async(certListBoxId, data, callback) {
	cadesplugin.async_spawn(function*(arg) {
		var e = document.getElementById(arg[0]);
		var selectedCertID = e.selectedIndex;
		if (selectedCertID == -1) {
			alert("Select certificate");
			return;
		}

		var certificate = global_selectbox_container[selectedCertID];

		var Signature;
		try
		{
			var errormes = "";

			var pubKey = yield certificate.PublicKey();
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
				errormes = "Поддерживается подпись только сертификатами с алгоритмами ГОСТ Р 34.10-2012, ГОСТ Р 34.10-2001";
				throw errormes;
			}

			try {
				var oRawSignature = yield cadesplugin.CreateObjectAsync("CAdESCOM.RawSignature");
			} catch (err) {
				errormes = "Failed to create CAdESCOM.RawSignature: " + err.number;
				throw errormes;
			}

			var oHashedData = yield cadesplugin.CreateObjectAsync("CAdESCOM.HashedData");

			yield oHashedData.propset_Algorithm(digestMethod);
			yield oHashedData.propset_DataEncoding(cadesplugin.CADESCOM_BASE64_TO_BINARY);
			yield oHashedData.Hash(data);

			try {
				Signature = yield oRawSignature.SignHash(oHashedData, certificate);
			}
			catch (err) {
				errormes = "Не удалось создать подпись из-за ошибки: " + cadesplugin.getLastError(err);
				throw errormes;
			}

			callback({
				success: true,
				signature: Signature
			});
		}
		catch(err)
		{
			callback({
				success: false,
				error: err
			});
		}
	}, certListBoxId); //cadesplugin.async_spawn
}

function isIE() {
	var retVal = (("Microsoft Internet Explorer" == navigator.appName) || // IE < 11
		navigator.userAgent.match(/Trident\/./i)); // IE 11
	return retVal;
}

async_resolve();
