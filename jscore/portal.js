var IS_DEBUG = true;

// функция логирования
function log(obj) {
	if (IS_DEBUG) {
		console.log(obj);
	}
}

function GetErrorMessage(e) {
	var err = e.message;
	if (!err) {
		err = e;
	} else if (e.number) {
		err += " (" + e.number + ")";
	}
	return err;
}

function checkTokenAuthMarsh(serial) {
	var req = getXmlHttp();
	
	document.getElementById('auth_submit').disabled = true; // дизаблим кнопку
	var msg = document.getElementById('login-message');
	msg.innerHTML = lang['avtorizatsiya_s_pomoschyu_marsh']; // +lang['indikatsiya']+ +lang['protsessa']+
	msg.style.color = "#990000";
	
	var region = "";
	if (document.getElementById('promed-region')) {
		region = document.getElementById('promed-region').value;
	}
	var dbtype = "";
	if (document.getElementById('promed-dbtype')) {
		dbtype = document.getElementById('promed-dbtype').value;
	}
	
	req.onreadystatechange = function() {
		if (req.readyState == 4) {
			try {
				answer = eval('(' + req.responseText + ')');
			} catch (e) {
				msg.innerHTML = lang['Nedostupen_server_avtorizacii_povtorite_popytku_pozzhe'];//"Недоступен сервер авторизации, повторите попытку позже.";
				document.getElementById('auth_submit').disabled = false;
			}

			if (answer) {
				if (!answer.success) {
					if (answer.blocked == 1) {
						msg.innerHTML = lang['Vasha_uchyotnaya_zapis_zablokirovana'];//"Ваша учётная запись заблокирована";
					} else {
						if (answer.Error_Msg !== undefined) {
							msg.innerHTML = answer.Error_Msg;
						} else {
							msg.innerHTML = lang['Oshibka_avtorizacii'];//"Ошибка авторизации!";
						}
					}
					msg.style.color = "#990000";
					document.getElementById('auth_submit').disabled = false;
				}
				else {
					location.replace('/?c=promed');
				}
			}
		}
	}
	var params = 'serial=' + encodeURIComponent(serial) + '&authType=marsh' + '&swUserRegion=' + encodeURIComponent(region) + '&swUserDBType=' + encodeURIComponent(dbtype);
	
	req.open('POST', '/?c=main&m=index&method=Logon', true);
	req.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
	req.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
	req.send(params);
}

function checkTokenAuth(data) {
	var req = getXmlHttp();
	
	document.getElementById('auth_submit').disabled = true; // дизаблим кнопку
	var msg = document.getElementById('login-message');
	msg.innerHTML = lang['avtorizatsiya_po_etsp']; // +lang['indikatsiya']+ +lang['protsessa']+
	msg.style.color = "#990000";
	
	var region = "";
	if (document.getElementById('promed-region')) {
		region = document.getElementById('promed-region').value;
	}
	var dbtype = "";
	if (document.getElementById('promed-dbtype')) {
		dbtype = document.getElementById('promed-dbtype').value;
	}
	
	req.onreadystatechange = function() {
		if (req.readyState == 4) {
			try {
				answer = eval('(' + req.responseText + ')');
			} catch (e) {
				msg.innerHTML = lang['Nedostupen_server_avtorizacii_povtorite_popytku_pozzhe'];//"Недоступен сервер авторизации, повторите попытку позже.";
				document.getElementById('auth_submit').disabled = false;
			}

			if (answer) {
				if (!answer.success) {
					if (answer.blocked == 1) {
						msg.innerHTML = lang['Vasha_uchyotnaya_zapis_zablokirovana'];//"Ваша учётная запись заблокирована";
					} else {
						if (answer.Error_Msg !== undefined) {
							msg.innerHTML = answer.Error_Msg;
						} else {
							msg.innerHTML = lang['Oshibka_avtorizacii'];//"Ошибка авторизации!";
						}
					}
					msg.style.color = "#990000";
					document.getElementById('auth_submit').disabled = false;
				}
				else {
					location.replace('/?c=promed');
				}
			}
		}
	}
	var params = 'message=' + encodeURIComponent(data.message) + '&signedMessage=' + encodeURIComponent(data.signedMessage) + '&authType=ecp' + '&swUserRegion=' + encodeURIComponent(region) + '&swUserDBType=' + encodeURIComponent(dbtype);
	
	req.open('POST', '/?c=main&m=index&method=Logon', true);
	req.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
	req.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
	req.send(params);
}

function getXmlHttp() {
	var xmlhttp;
	try {
		xmlhttp = new ActiveXObject("Msxml2.XMLHTTP");
	} 
	catch (e) {
		try {
			xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
		} 
		catch (E) {
			xmlhttp = false;
		}
	}
	if (!xmlhttp && typeof XMLHttpRequest != 'undefined') {
		xmlhttp = new XMLHttpRequest();
	}
	return xmlhttp;
}

function appendOptionLast(value, text, elSel)
{
	var elOptNew = document.createElement('option');
	elOptNew.text = text;
	elOptNew.value = value;

	try {
		elSel.add(elOptNew, null); // standards compliant; doesn't work in IE
	}
	catch(ex) {
		elSel.add(elOptNew); // IE only
	}
}

function getReadersAndInitSelect()
{
	var readers = getSocCardReadersArray();
	if ( readers && (!readers.ErrorMessage || String(readers.ErrorMessage) == '') )
	{
		for ( var i = 0; i < readers.readersArray.length; i++ )
		{
			appendOptionLast(readers.readersArray[i], readers.readersArray[i], document.getElementById('promed-cardrider'));
		}
	}
}

function loginWithMarsh()
{
	var response = {
		success: false, 
		ErrorCode: 1, 
		ErrorMessage: lang['proizoshla_oshibka']
	};

	// проверяем наличие плагина
	if ( document.marsh )
	{
		// проверяем доступность методов
		if ( typeof document.marsh.Run == 'unknown' || document.marsh.Run ) // IE, это один большой костыль и УГ
		{
			// вызываем методы
			try 
			{
				document.marsh.Run();
				var serial = document.marsh.Get();
				if ( serial )
				{
					// получили серийник, логинимся
					checkTokenAuthMarsh(serial);
				}
				else
				{
					response.ErrorCode = 5;
					response.ErrorMessage = lang['ne_udalos_poluchit_seriynyiy_nomer_marsha'];
				}
			}
			catch (e)
			{
				response.ErrorCode = 4;
				response.ErrorMessage = lang['proizoshla_oshibka_chteniya_seriynogo_nomera_marsha'];
			}
		}
		else
		{
			response.ErrorCode = 3;
			response.ErrorMessage = lang['net_dostupa_k_metodam_plagina_poprobuyte_obnovit_stranitsu_v_kraynem_sluchae_vozmojno_priydetsya_obnovit_java-plugin_v_brauzere'];
		}	
	}
	else
	{
		response.ErrorCode = 2;
		response.ErrorMessage = lang['ne_nayden_plagin_dlya_chteniya_dannyih_kartridera'];
	}
	return response;
}

function getSocCardReadersArray()
{
	var response = {
		success: false, 
		ErrorCode: 1, 
		ErrorMessage: lang['proizoshla_oshibka_opredeleniya_spiska']
	};
	var readers_array = new Array();
	// проверяем наличие плагина
	if ( document.apl )
	{
		// проверяем доступность методов
		if ( typeof document.apl.getReaders == 'unknown' || document.apl.getReaders ) // IE, это один большой костыль и УГ
		{
			// вызываем методы
			try 
			{
				var readers = document.apl.getReaders();
				if ( readers )
				{													
					//var reader = String(readers).substr(1, String(readers).length - 2);
					readers_array = String(readers).split(", ");
					if ( readers_array.length > 0 )
					{
						readers_array[0] = String(readers_array[0]).substr(1, String(readers_array[0]).length - 1);
						readers_array[readers_array.length - 1] = String(readers_array[readers_array.length - 1]).substr(0, String(readers_array[readers_array.length - 1]).length - 1);
						response.success = true;
						response.readersArray = readers_array;
						response.ErrorCode = null;
						response.ErrorMessage = null;
					}							
					else
					{
						response.ErrorCode = 5;
						response.ErrorMessage = lang['ne_udalos_poluchit_spisok_ustroystv_chteniya_kart'];
					}
				}
				else
				{
					response.ErrorCode = 5;
					response.ErrorMessage = lang['ne_udalos_poluchit_spisok_ustroystv_chteniya_kart'];
				}
			}
			catch (e)
			{
				response.ErrorCode = 4;
				response.ErrorMessage = lang['proizoshla_oshibka_chteniya_kartyi'];
			}
		}
		else
		{
			response.ErrorCode = 3;
			response.ErrorMessage = lang['net_dostupa_k_metodam_plagina_poprobuyte_obnovit_stranitsu_v_kraynem_sluchae_vozmojno_priydetsya_obnovit_java-plugin_v_brauzere'];
		}	
	}
	else
	{
		response.ErrorCode = 2;
		response.ErrorMessage = lang['ne_nayden_plagin_dlya_chteniya_dannyih_kartridera'];
	}
	return response;	
}

function returnCardError(message) {
	var msg = document.getElementById('card-login-message');
	msg.innerHTML = message;
	msg.style.color = "#990000";
	document.getElementById('card_auth_submit').disabled = false;
}

function checkPOSTcardauthprocess(message) {
	var req = getXmlHttp();

	var tokentype = document.getElementById('promed-tokentype').value;

	// проверяем pin
	var pin = document.getElementById('promed-pincode').value;
	var login = document.getElementById('promed-login').value;

	var region = "";
	if (document.getElementById('promed-region')) {
		region = document.getElementById('promed-region').value;
	}
	var dbtype = "";
	if (document.getElementById('promed-dbtype')) {
		dbtype = document.getElementById('promed-dbtype').value;
	}

	if (login.length == 0 && tokentype != 'nca1' && tokentype != 'nca2') { // для NCALayer логин не обязателен
		returnCardError(lang['Ne_vvedeno_imya_polzovatelya']);
		return false;
	} else if (pin.length == 0 && tokentype != 'cc' && tokentype != 'vn') {
		if (region == 'kz') {
			returnCardError(langs('Не введен пароль!'));
		} else {
			returnCardError(langs('Не введен ПИН-код!'));
		}
		return false;
	}

	if (tokentype && tokentype.indexOf('vn') >= 0) {
		// авторизация с помощью VipNet PKI Client (Web Unit)
		var client = new LssClient(jQuery);
		var options = {
			base64Data: btoa(message),
			description: 'Данные для авторизации',
			documentName: 'auth',
			fileExtension: 'txt',
			isAttached: false
		};

		client.sign(options).done(function(response) {
			log(response);

			if (response.IsSuccessful) {
				// получили подпись, осуществляем вход
				var encmessage = response.SignedData;
				var params = 'login=' + encodeURIComponent(login) + '&encmessage=' + encodeURIComponent(encmessage) + '&authType=ecp&tokenType=' + tokentype + '&swUserRegion=' + encodeURIComponent(region) + '&swUserDBType=' + encodeURIComponent(dbtype);
				req.onreadystatechange = function () {
					if (req.readyState == 4) {
						try {
							answer = eval('(' + req.responseText + ')');
						} catch (e) {
							returnCardError(langs('Недоступен сервер авторизации, повторите попытку позже.'));
							return false;
						}
						if (!answer.success) {
							if (answer.Error_Msg) {
								returnCardError(langs('Ошибка авторизации: ' + answer.Error_Msg));
							} else {
								returnCardError(langs('Ошибка авторизации!'));
							}
							return false;
						} else {
							location.replace('/?c=promed');
						}
					}
				};

				req.open('POST', '/?c=main&m=index&method=Logon', true);
				req.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
				req.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

				req.send(params);
			} else {
				returnCardError(response.ErrorMessage);
				return false;
			}
		})
		.fail(function(error) {
			returnCardError('Ошибка при вызове метода подписания, убедитесь, что установлен ViPNet PKI Client (Web Unit).');
			log(error);
			return false;
		});
	} else if (tokentype && tokentype.indexOf('cc') >= 0) {
		// авторизация с помощью КриптоПро Browser Plugin
		var e = document.getElementById('CertListBox');
		var selectedCertID = e.selectedIndex;
		if (selectedCertID == -1) {
			returnCardError(langs('Не выбран сертификат!'));
			return false;
		}
		Common_RawSign('CertListBox', btoa(message), function(data) {
			if (data.success) {
				// получили подпись, осуществляем вход
				var encmessage = data.signature;
				var params = 'login=' + encodeURIComponent(login) + '&encmessage=' + encodeURIComponent(encmessage) + '&authType=ecp&tokenType=' + tokentype + '&swUserRegion=' + encodeURIComponent(region) + '&swUserDBType=' + encodeURIComponent(dbtype);
				req.onreadystatechange = function () {
					if (req.readyState == 4) {
						try {
							answer = eval('(' + req.responseText + ')');
						} catch (e) {
							returnCardError(langs('Недоступен сервер авторизации, повторите попытку позже.'));
							return false;
						}
						if (!answer.success) {
							if (answer.Error_Msg) {
								returnCardError(langs('Ошибка авторизации: ' + answer.Error_Msg));
							} else {
								returnCardError(langs('Ошибка авторизации!'));
							}
							return false;
						} else {
							location.replace('/?c=promed');
						}
					}
				};

				req.open('POST', '/?c=main&m=index&method=Logon', true);
				req.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
				req.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

				req.send(params);
			} else {
				returnCardError(data.error);
				return false;
			}
		});
	} else if (tokentype && tokentype.indexOf('nca') >= 0) {
		// авторизация с помощью NCALayer
		ncaLayer.connect(function() {
			// подключились, можно подписывать

			// ищем ключ
			var storageAlias = "PKCS12";
			switch(tokentype) {
				case 'nca1':
					storageAlias = "PKCS12";
					break;
				case 'nca2':
					storageAlias = "AKKaztokenStore";
					break;
			}
			var storagePath = null;
			ncaLayer.browseKeyStore(storageAlias, "P12", "", function(rw) {
				if (rw.getErrorCode() === "NONE") {
					storagePath = rw.getResult();
					if (storagePath !== null && storagePath !== "") {
						// получили путь к токену, можно искать ключ
						var keyType = "AUTH";
						ncaLayer.getKeys(storageAlias, storagePath, pin, keyType, function(result) {
							if (result['errorCode'] === "NONE") {
								var list = result['result'];
								var slotListArr = list.split("\n");
								var alias = null;
								for (var i = 0; i < slotListArr.length; i++) {
									if (slotListArr[i] === null || slotListArr[i] === "") {
										continue;
									}
									alias = slotListArr[i].split("|")[3];
								}

								if (alias != null) {
									// получили ключ, можно достать ИИН
									ncaLayer.getRdnByOid(storageAlias, storagePath, alias, pin, "2.5.4.5", 0, function (result) {
										if (result && result.result && result.result.version) {
											return; // пропускаем, если это сообщение о версии
										}
										if (result['errorCode'] === "NONE") {
											// получилось достать ИИН
											var iin = result['result'];
											var params = 'login=' + encodeURIComponent(login) + '&iin=' + encodeURIComponent(iin) + '&authType=ecp&tokenType=' + tokentype + '&swUserRegion=' + encodeURIComponent(region) + '&swUserDBType=' + encodeURIComponent(dbtype);
											req.onreadystatechange = function () {
												if (req.readyState == 4) {
													try {
														answer = eval('(' + req.responseText + ')');
													} catch (e) {
														returnCardError(lang['Nedostupen_server_avtorizacii_povtorite_popytku_pozzhe']);
														return false;
													}
													if (!answer.success) {
														if (answer.Error_Msg) {
															returnCardError(langs('Ошибка авторизации: ' + answer.Error_Msg));
														} else {
															returnCardError(langs('Ошибка авторизации!'));
														}
														return false;
													} else {
														location.replace('/?c=promed');
													}
												}
											}

											req.open('POST', '/?c=main&m=index&method=Logon', true);
											req.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
											req.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

											req.send(params);
										}
										else {
											if (result['errorCode'] === "WRONG_PASSWORD" && result['result'] > -1) {
												returnCardError("Неправильный пароль! Количество оставшихся попыток: " + result['result']);
												return false;
											} else if (result['errorCode'] === "WRONG_PASSWORD") {
												returnCardError("Неправильный пароль!");
												return false;
											} else {
												ncaLayer.log(result);
												returnCardError("Ошибка подписи с помощью NCALayer: " + result['errorCode']);
												return false;
											}
										}
									});
								} else {
									returnCardError("На токене не найдено ни одного ключа");
									return false;
								}
							}
							else {
								if (result['errorCode'] === "WRONG_PASSWORD" && result['result'] > -1) {
									returnCardError("Неправильный пароль! Количество оставшихся попыток: " + result['result']);
									return false;
								} else if (result['errorCode'] === "WRONG_PASSWORD") {
									returnCardError("Неправильный пароль!");
									return false;
								} else {
									returnCardError("Ошибка поиска ключей с помощью NCALayer: " + result['errorCode']);
									return false;
								}
							}
						});
					}
				}
			});
		});
		// browseKeyStore(storageAlias, "P12", storagePath, "chooseStoragePathBack");
	} else if (tokentype && tokentype.indexOf('aa') >= 0) {
		// авторизация с помощью AuthApi
		var tokenType = 'eToken';
		authApi.setTomEE(false);
		switch(tokentype) {
			case 'aa1':
				// eToken ГОСТ
				tokenType = 'eToken';
				break;
			case 'aa2':
				// jaCarta
				tokenType = 'jaCarta';
				break;
			case 'aa3':
				// ruToken
				tokenType = 'ruToken';
				break;
			case 'aa4':
				// CSP
				tokenType = 'CSP';
				break;
			case 'aae1':
				// jaCarta
				authApi.setTomEE(true);
				tokenType = 'jaCarta';
				break;
			case 'aae2':
				// ruToken
				authApi.setTomEE(true);
				tokenType = 'ruToken';
				break;
		}

		authApi.signString({
			tokenType: tokenType,
			pin: pin,
			message: message,
			callback: function(result) {
				if (result && result.signature && result.signature.length > 0) {
					// подпись успешна, отправляем на сервер
					var encmessage = result.signature;
					var params = 'login=' + encodeURIComponent(login) + '&encmessage=' + encodeURIComponent(encmessage) + '&authType=ecp&tokenType=' + tokentype + '&swUserRegion=' + encodeURIComponent(region) + '&swUserDBType=' + encodeURIComponent(dbtype);
					req.onreadystatechange = function () {
						if (req.readyState == 4) {
							try {
								answer = eval('(' + req.responseText + ')');
							} catch (e) {
								returnCardError(lang['Nedostupen_server_avtorizacii_povtorite_popytku_pozzhe']);
								return false;
							}
							if (!answer.success) {
								if (answer.Error_Msg) {
									returnCardError(langs('Ошибка авторизации: ' + answer.Error_Msg));
								} else {
									returnCardError(langs('Ошибка авторизации!'));
								}
								return false;
							} else {
								location.replace('/?c=promed');
							}
						}
					}

					req.open('POST', '/?c=main&m=index&method=Logon', true);
					req.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
					req.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

					req.send(params);
				}
				else {
					var errorMessage = 'Неверный ответ сервиса';
					if (result && result.errorMessage) {
						errorMessage = result.errorMessage;
					}
					returnCardError("Ошибка подписи с помощью AuthApi: " + errorMessage);
					return false;
				}
			}
		});
	} else {
		// авторизация с помощью AuthApplet
		tokentype = parseInt(tokentype);
		if (promedCardAPI != undefined && promedCardAPI.currProMedPlug != undefined && promedCardAPI.currProMedPlug.getBrowserPlugin() && promedCardAPI.currProMedPlug.getBrowserPlugin().valid) {
			if (tokentype == 12 || tokentype == 13) { // для PKCS#12 своя логика
				var hash = message;
				log("Base64-coded data hash: " + hash);
			} else {
				log("Base64-coded data to sign: " + btoa(message));
				var hash = promedCardAPI.currProMedPlug.getGost341194Hash(btoa(message));
				log("Base64-coded data hash: " + hash);
			}

			try {
				var resp = promedCardAPI.currProMedPlug.signDocumentRaw("", tokentype, pin, hash);
			} catch (e) {
				var resp = {
					errorMessage: lang['oshibka_avtorizatsii_po_etsp']
				};

				if (e.errorMessage) {
					resp.errorMessage = e.errorMessage;
				}
			}

			if (resp && resp.errorMessage == 'OK') {
				var encmessage = resp.documentSigned;
				var params = 'login=' + encodeURIComponent(login) + '&encmessage=' + encodeURIComponent(encmessage) + '&authType=ecp&tokenType=' + tokentype + '&swUserRegion=' + encodeURIComponent(region) + '&swUserDBType=' + encodeURIComponent(dbtype);
			} else {
				switch (resp.errorMessage) {
					case 'CKR_PIN_LEN_RANGE':
						resp.errorMessage = lang['ukazannyiy_pin-kod_soderjit_nepravilnoe_kolichestvo_simvolov'];
						break;
					case 'CKR_PIN_EXPIRED':
					case 'CKR_PIN_LOCKED':
					case 'CKR_PIN_INCORRECT':
					case 'CKR_PIN_INVALID':
						resp.errorMessage = lang['nepravilnyiy_pin-kod'];
						break;
				}

				returnCardError(resp.errorMessage);
				return false;
			}


			req.onreadystatechange = function () {
				if (req.readyState == 4) {
					try {
						answer = eval('(' + req.responseText + ')');
					} catch (e) {
						returnCardError(lang['Nedostupen_server_avtorizacii_povtorite_popytku_pozzhe']);
						return false;
					}
					if (!answer.success) {
						if (answer.Error_Msg) {
							returnCardError(langs('Ошибка авторизации: ' + answer.Error_Msg));
						} else {
							returnCardError(langs('Ошибка авторизации!'));
						}
						return false;
					} else {
						location.replace('/?c=promed');
					}
				}
			}

			req.open('POST', '/?c=main&m=index&method=Logon', true);
			req.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
			req.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

			req.send(params);
		} else {
			var links =
				'<br><a href="/plugins/AuthApplet.msi">AuthApplet.msi</a>';
			if (navigator && navigator.appVersion && navigator.appVersion.indexOf("Linux") != -1) {
				links =
					'<br><a href="/plugins/promed-cardapi_i386.deb">promed-cardapi_i386.deb</a>' +
					'<br><a href="/plugins/promed-cardapi_amd64.deb">promed-cardapi_amd64.deb</a>';
			}			returnCardError(lang['Ne_ustanovlen_plagin_dlya_raboty_s_EHCP_Plagin_mozhno_skachat_po_ssylke'] + links);
			return false;
		}
	}
}

function auth1keydown(event)
{
	if (event.keyCode == 13) {
		document.getElementById('auth_submit').disabled = true;
		checkPOSTauth();
	}
}

function auth2keydown(event, message)
{
	if (event.keyCode == 13) {
		document.getElementById('card_auth_submit').disabled = true;
		checkPOSTcardauth(message);
	}
}

function checkPOSTcardauth(message) {
	var msg = document.getElementById('card-login-message');
	msg.innerHTML = lang['avtorizatsiya'];
	msg.style.color = "#990000";
	// с таймаутом, чтобы отобразилось сообщенние "Авторизация", а то браузер подвисает от плагина
	setTimeout( function(){ checkPOSTcardauthprocess(message); }, 200);
}

function checkPOSTauth() {

	if ( document.getElementById('auth_submit').disabled == "disabled" ) {
		return false;
	}

	document.getElementById('auth_submit').disabled = "disabled";

	var req = getXmlHttp();
	var s_ok = 1;
	
	var login = document.getElementById('promed-login').value;
	var psw = document.getElementById('promed-password').value;
	var pswn1 = document.getElementById('promed-new-password').value;
	var pswn2 = document.getElementById('promed-new-password-two').value;
	var region = "";
	if (document.getElementById('promed-region')) {
		region = document.getElementById('promed-region').value;
	}
	var dbtype = "";
	if (document.getElementById('promed-dbtype')) {
		dbtype = document.getElementById('promed-dbtype').value;
	}
	
	var msg = document.getElementById('login-message');
	msg.innerHTML = lang['avtorizatsiya'];
	msg.style.color = "#990000";
	
	if ((login == '') || (psw == '')) {
		s_ok = 0;
		msg.innerHTML = lang['Ne_zapolneny_neobhodimye_polya'];//"Не заполнены необходимые поля!";
		msg.style.color = "#990000";
	}

	if (pswn1 != pswn2) {
		s_ok = 0;
		msg.innerHTML = lang['Znacheniya_v_polyah_Novyj_parol_i_Povtorite_parol_ne_sovpadayut'];//'Значения в полях "Новый пароль" и "Повторите пароль" не совпадают';
		msg.style.color = "#990000";
	}
	
	if (s_ok != 1) {
		document.getElementById('auth_submit').disabled = "";
	}
	if (s_ok == 1) {
		req.onreadystatechange = function() {
			if (req.readyState == 4) {
				try {
					answer = eval('(' + req.responseText + ')');
				} catch (e) {
					msg.innerHTML = lang['Nedostupen_server_avtorizacii_povtorite_popytku_pozzhe'];//"Недоступен сервер авторизации, повторите попытку позже.";
					document.getElementById('auth_submit').disabled = "";
				}

				if (answer) {
					if (!answer.success) {
						document.getElementById('changepassword').style.display = 'none';
						if (answer.blocked == 1) {
							msg.innerHTML = lang['Vasha_uchyotnaya_zapis_zablokirovana'];//"Ваша учётная запись заблокирована";
						} else {
							if (answer.Error_Msg !== undefined) {
								if (answer.Error_Code && (answer.Error_Code == '11' || answer.Error_Code == '12')) {
									document.getElementById('changepassword').style.display = 'block';
								}
								msg.innerHTML = answer.Error_Msg;
							} else {
								msg.innerHTML = lang['Oshibka_avtorizacii'];//"Ошибка авторизации!";
							}
						}
						msg.style.color = "#990000";
						document.getElementById('auth_submit').disabled = false;
					}
					else {
						location.replace('/?c=promed');
					}
				}
			}
		}
		var params = 'login=' + encodeURIComponent(login) + '&psw=' + encodeURIComponent(psw) + '&swUserRegion=' + encodeURIComponent(region) + '&swUserDBType=' + encodeURIComponent(dbtype);
		if (pswn1.length > 0) {
			if (checkPassword(pswn1, false)) {
				params += '&newpsw=' + encodeURIComponent(pswn1);
			} else {
				document.getElementById('auth_submit').disabled = false;
				return;
			}
		}
		
		req.open('POST', '/?c=main&m=index&method=Logon&login=' + encodeURIComponent(login), true);
		req.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
		req.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
		req.send(params);
	}
}

function checkPasswordFields() {
	var pswn1 = document.getElementById('promed-new-password').value;
	var pswn2 = document.getElementById('promed-new-password-two').value;

	// если пароль верный, то зелёная галочка
	if (checkPassword(pswn1, true)) {
		document.getElementById('promed-new-password').classList.add('textFieldVerified');
		// если повторите пароль совпадает с паролем, то зелёная галочка
		if (pswn2.length > 0 && pswn2 == pswn1) {
			document.getElementById('promed-new-password-two').classList.add('textFieldVerified');
		} else {
			document.getElementById('promed-new-password-two').classList.remove('textFieldVerified');
		}
	} else {
		document.getElementById('promed-new-password').classList.remove('textFieldVerified');
		document.getElementById('promed-new-password-two').classList.remove('textFieldVerified');
	}
}

function checkCaps(e) {
	var key = e.keyCode ? e.keyCode : e.which;
	var msg = document.getElementById('login-message');
	
	if ((key >= 65 && key <= 90) || (key >= 1040 && key <= 1071) || key==1025){
		msg.innerHTML = "Внимание: заглавные буквы";
		msg.style.color = "#990000";
	} else {
		msg.innerHTML = "";
		msg.style.color = "#666666";
	}
}

function checkPassword(pass, withoutMessage) {
	var msg = document.getElementById('login-message');
	var lowerSymb = new RegExp('[a-zа-я]');
	var upperSymb = new RegExp('[A-ZА-Я]');
	var numbeSymb = new RegExp('[0-9]');
	var specSymb = new RegExp('[^A-Z^А-Я^a-z^а-я^0-9]');

	var minPassLen = 6;
	if (password_minlength) {
		minPassLen = password_minlength;
	}

	var containsLowers = lowerSymb.test(pass);
	var containsUppers = true;
	var needUppers = false;
	if (password_hasuppercase) {
		needUppers = true;
		containsUppers = upperSymb.test(pass);
	}
	var containsNumbers = true;
	var needNumbers = false;
	if (password_hasnumber) {
		needNumbers = true;
		containsNumbers = numbeSymb.test(pass);
	}
	var containsSpec = true;
	var needSpec = false;
	if (password_hasspec) {
		needSpec = true;
		containsSpec = specSymb.test(pass);
	}
	var containsMinLen = (pass.length >= minPassLen);
	if (!(
			containsLowers&&
			containsUppers&&
			containsNumbers&&
			containsSpec&&
			containsMinLen
		)){

		if (!withoutMessage) {
			msg.innerHTML = "<br> Пароль не удовлетворяет рекомендуемым требованиям безопасности: <br>" +
				"пароль должен состоять минимум из " + (containsMinLen ? '' : '<b>') + minPassLen + (containsMinLen ? '' : '</b>') + ' символов, ' +
				'среди которых должна присутствовать минимум ' + (containsLowers ? '' : '<b>') + 'одна строчная буква' + (containsLowers ? '' : '</b>') +
				(needUppers ? (', ' + (containsUppers ? '' : '<b>') + 'одна прописная буква' + (containsUppers ? '' : '</b>')) : '') +
				(needNumbers ? (', ' + (containsNumbers ? '' : '<b>') + 'одна цифра' + (containsNumbers ? '' : '</b>')) : '') +
				(needSpec ? (', ' + (containsSpec ? '' : '<b>') + 'один спец. символ' + (containsSpec ? '' : '</b>')) : '');
			msg.style.color = "#990000";
		}
		return false;
	}

	return true;
}

function getFirstElementByClass(node, className) {
	var list = node.getElementsByTagName('*');
	var length = list.length;
	
	for (var i = 0; i < length; i++) {
		if (hasClass(list[i], className)) 
			return list[i];
	}
	
	return false;
}

function toggleEntryOptions(event) {
	var node = event.target || event.srcElement;
	
	var el = getFirstElementByClass(node, 'entry_options');
	
	if (el) {
		if (el.style.display == 'block') 
			el.style.display = 'none';
		else el.style.display = 'block';
	}
}

function hasClass(elem, className) {
	return new RegExp("(^|\\s)" + className + "(\\s|$)").test(elem.className)
}

function loadCertList() {
	Common_FillCertList('CertListBox');
}

function checkTokenType() {
	var tokentype = document.getElementById('promed-tokentype').value;
	if (tokentype == 'vn') {
		// пин-код не нужен, список сертификатов тоже
		document.getElementById('pin-div').style.display = 'none';
		document.getElementById('cert-div').style.display = 'none';
	} else if (tokentype == 'cc') {
		// пин-код не нужен, зато нужен список сертификатов
		document.getElementById('pin-div').style.display = 'none';
		document.getElementById('cert-div').style.display = 'block';

		loadCertList();
	} else {
		// нужен пин-код, не нужен список сертификатов
		document.getElementById('pin-div').style.display = 'block';
		document.getElementById('cert-div').style.display = 'none';
	}
}

function getNewsMore() {
	var req = getXmlHttp();
	
	var start = document.getElementById('startNews').value;
	var num = document.getElementById('numNews').value;
	
	var msg = document.getElementById('getNews');
	msg.innerHTML = lang['zagruzka'];
	
	req.onreadystatechange = function() {
		if (req.readyState == 4) {
			answer = eval('(' + req.responseText + ')');
			
			//log(answer);
			
			if (answer.success) {
				var container = document.getElementById('newsContainer');
				container.innerHTML += answer.html;

				document.getElementById('startNews').value = parseInt(document.getElementById('startNews').value) + parseInt(document.getElementById('numNews').value);
			}
			
			if(answer.more){
				msg.innerHTML = '<a href="javascript:void(0)" onclick="getNewsMore();">ЕЩЕ '+answer.more+'</a>';
			} else {
				msg.innerHTML= '';
				document.getElementById('getNews').style.display = 'none';
			}
		}
	}
	
	var params = 'start=' + encodeURIComponent(start) + '&num=' + encodeURIComponent(num);
		
	req.open('POST', '/?c=portal&m=getNewsMore', true);
	req.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
		
	req.send(params);
}

function getSeminarsMore() {
    var req = getXmlHttp();

    var start = document.getElementById('startNews').value;
    var num = document.getElementById('numNews').value;

    var msg = document.getElementById('getNews');
    msg.innerHTML = lang['zagruzka'];

    req.onreadystatechange = function() {
        if (req.readyState == 4) {
            answer = eval('(' + req.responseText + ')');

            //log(answer);

            if (answer.success) {
                var container = document.getElementById('newsContainer');
                container.innerHTML += answer.html;

                document.getElementById('startNews').value = parseInt(document.getElementById('startNews').value) + parseInt(document.getElementById('numNews').value);
            }

            if(answer.end){
                msg.innerHTML= '';
            } else {
                msg.innerHTML = '<a href="javascript:void(0)" onclick="getNewsMore();">Показать еще</a>';
            }
        }
    }

    var params = 'start=' + encodeURIComponent(start) + '&num=' + encodeURIComponent(num);

    req.open('POST', '/?c=portal&m=getSeminarsMore', true);
    req.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

    req.send(params);
}

function ChangeSelectByValue(ddlID, value, change) {
	var ddl = document.getElementById(ddlID);
	for (var i = 0; i < ddl.options.length; i++) {
		if (ddl.options[i].value == value) {
			if (ddl.selectedIndex != i) {
				ddl.selectedIndex = i;
				if (change)
					ddl.onchange();
			}
			break;
		}
	}
}

/**
 * Функция для перевода
 */
function langs(name) {
	if (lang[name]) {
		return lang[name];
	} else {
		return name;
	}
}