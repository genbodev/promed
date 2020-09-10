Array.prototype.indexOf = function (el) {
	var i, len;
	for (i = 0, len = this.length; i < len; i++) {
		if (this[i] == el) return i;
	}
	return -1;
};
String.prototype.replaceChar = function (num, chr) {
	return this.slice(0, num - 1) + chr + this.slice(num);
};

promedCardAPI = {

	tableUtfRecoder: {
		"\\xD0\\xB0": "а",
		"\\xD0\\x90": "А",
		"\\xD0\\xB1": "б",
		"\\xD0\\x91": "Б",
		"\\xD0\\xB2": "в",
		"\\xD0\\x92": "В",
		"\\xD0\\xB3": "г",
		"\\xD0\\x93": "Г",
		"\\xD0\\xB4": "д",
		"\\xD0\\x94": "Д",
		"\\xD0\\xB5": "е",
		"\\xD0\\x95": "Е",
		"\\xD1\\x91": "ё",
		"\\xD0\\x81": "Ё",
		"\\xD0\\xB6": "ж",
		"\\xD0\\x96": "Ж",
		"\\xD0\\xB7": "з",
		"\\xD0\\x97": "З",
		"\\xD0\\xB8": "и",
		"\\xD0\\x98": "И",
		"\\xD0\\xB9": "й",
		"\\xD0\\x99": "Й",
		"\\xD0\\xBA": "к",
		"\\xD0\\x9A": "К",
		"\\xD0\\xBB": "л",
		"\\xD0\\x9B": "Л",
		"\\xD0\\xBC": "м",
		"\\xD0\\x9C": "М",
		"\\xD0\\xBD": "н",
		"\\xD0\\x9D": "Н",
		"\\xD0\\xBE": "о",
		"\\xD0\\x9E": "О",
		"\\xD0\\xBF": "п",
		"\\xD0\\x9F": "П",
		"\\xD1\\x80": "р",
		"\\xD0\\xA0": "Р",
		"\\xD1\\x81": "с",
		"\\xD0\\xA1": "С",
		"\\xD1\\x82": "т",
		"\\xD0\\xA2": "Т",
		"\\xD1\\x83": "у",
		"\\xD0\\xA3": "У",
		"\\xD1\\x84": "ф",
		"\\xD0\\xA4": "Ф",
		"\\xD1\\x85": "х",
		"\\xD0\\xA5": "Х",
		"\\xD1\\x86": "ц",
		"\\xD0\\xA6": "Ц",
		"\\xD1\\x87": "ч",
		"\\xD0\\xA7": "Ч",
		"\\xD1\\x88": "ш",
		"\\xD0\\xA8": "Ш",
		"\\xD1\\x89": "щ",
		"\\xD0\\xA9": "Щ",
		"\\xD1\\x8A": "ъ",
		"\\xD0\\xAA": "Ъ",
		"\\xD1\\x8B": "ы",
		"\\xD0\\xAB": "Ы",
		"\\xD1\\x8C": "ь",
		"\\xD0\\xAC": "Ь",
		"\\xD1\\x8D": "э",
		"\\xD0\\xAD": "Э",
		"\\xD1\\x8E": "ю",
		"\\xD0\\xAE": "Ю",
		"\\xD1\\x8F": "я",
		"\\xD0\\xAF": "Я"
	},

	parseCertUrl: "http://192.168.36.62:8080/sign_service/api/parse_cert",

	MALE: 1,
	FEMALE: 2,
	CARD_NOT_EXIST: -1,
	TIME_LOG: true,
	DEVELOP_LOG: (IS_DEBUG == 1),

	PRO_MED_TRY_LOAD_COUNT: 5,
	PRO_MED_TRY_LOAD_DELAY: 500,

	consoleDebug: function (str) {
		if (console == undefined) return;
		if (console.debug) {
			console.debug(str);
		} else if (console.warn) {
			console.warn(str)
		}
	},
	ajaxRequest: function (url, params, callbackOk, callbackFail) {
		var ajObj = {
			url: url,
			async: false,
			dataType: "jsonp",
			success: function (data, textStatus, jqXHR) {
				if (promedCardAPI.DEVELOP_LOG) {
					console.debug(url + " ajax ok")
					console.debug(arguments)
				}
				if (callbackOk != undefined) {
					callbackOk(data)
				}
			},
			error: function (jqXHR, textStatus, errorThrown) {
				if (promedCardAPI.DEVELOP_LOG) {
					console.debug(url + " ajax fail")
					console.debug(arguments)
				}
				if (callbackFail != undefined) {
					callbackFail(textStatus, errorThrown)
				}
			}
		}
		if (params != undefined) {
			ajObj.data = params;
		}
		$.ajax(ajObj)
	},
	showSplashPluginNeedLoad: function () {
		var bodyEl = document.getElementsByTagName("body")[0];
		if (bodyEl == undefined) return;
		var divEl = document.createElement('div');
		divEl.setAttribute("style",
			"width:600px;height:400px;border:2px solid red;position:absolute;"
			+ "z-order:999999;left:" + Math.round((bodyEl.clientWidth - 600) / 2)
			+ ";top:" + Math.round((bodyEl.clientHeight - 400) / 2))
		divEl.innerHTML = 'Не установлен плагин ProMed AuthApplet.<br/>'
			+ 'Пожалуйста, скачайте и установите плагин ProMed AuthApplet по следующей ссылке:<br/>'
			+ '<a href="AuthApplet.msi">ProMed AuthApplet для Windows</a><br/><br/>'
			+ '<a href="promed-cardapi_i386.deb">ProMed AuthApplet для Debian/Ubuntu 32bit</a><br/><br/>'
			+ '<a href="promed-cardapi_amd64.deb">ProMed AuthApplet для Debian/Ubuntu 64bit</a><br/><br/>'
			+ 'После этого закройте браузер и откройте заново страницу';
		bodyEl.appendChild(divEl)
	},
	checkProMedPlugin: function () {
		if (promedCardAPI.currProMedPlug != undefined) {
			return;
		}
		this.currProMedPlug = new promedCardAPI.CardApi()
		var checker = function () {
			if (promedCardAPI.currProMedPlug.getBrowserPlugin()
				&& promedCardAPI.currProMedPlug.getBrowserPlugin().valid) {
				if (promedCardAPI.DEVELOP_LOG) {
					console.debug("ProMed plugin checked: OK!")
				}
			} else {
				promedCardAPI.currProMedPlug.tryCounter = promedCardAPI.currProMedPlug.tryCounter
					|| 0;
				if (promedCardAPI.DEVELOP_LOG) {
					console.debug("ProMed plugin checked: false, try "
						+ promedCardAPI.currProMedPlug.tryCounter)
				}
				if (promedCardAPI.currProMedPlug.tryCounter < promedCardAPI.PRO_MED_TRY_LOAD_COUNT) {
					setTimeout(checker, promedCardAPI.PRO_MED_TRY_LOAD_DELAY)
					promedCardAPI.currProMedPlug.tryCounter++
				} else {
					// promedCardAPI.showSplashPluginNeedLoad()
				}
			}
		}
		checker()
	},
	CardTypes: {
		UnknownCard: 0,
		UEC_Card: 1,
		EPolice_Card: 2,
		Bashkir_Card: 3,
		MARSH_DCC: 4,
		EToken_Key: 5,
		RuToken_Key: 6,
		EPolice_Security_Card: 7,
		Lissi_PSMIS_SW_Key: 8,
		ETokenPro_Key: 9,
		jaCarta_Key: 10,
		Ktoken: 11,
		PKC12Gost: 12,
		PKC12RSA: 13
	},
	CardBridgeNames: ["", "UEC_Bridge", "EP_Bridge", "", "", "EToken_Bridge", "", "", "LISPSMIS_Bridge", "ETokenPro_Bridge", "jaCarta_Bridge", "Ktoken_Bridge", "PKC12Gost_Bridge", "PKC12RSA_Bridge"],
	ATR_Cards: {
		"3b:6f:00:ff:00:56:72:75:54:6f:6b:6e:73:30:20:00:00:90:00": 6,
		"3b:d5:18:00:81:31:fe:7d:80:73:c8:21:10:f4": ETOKEN_PRO_ENABLED?9:5,
		"3b:f7:13:00:00:81:31:fe:45:46:4f:4d:53:4f:4d:53:a9": 2,
		"3b:7c:13:00:00:80:64:11:65:01:90:73:00:00:00:81:07": 1,
		"3b:f5:13:00:00:81:31:fe:45:41:43:41:52:44:b8": 7,
		"LISSI_SW_VIRTUAL_TOKEN": 8,
		// "3b:d5:18:00:81:31:fe:7d:80:73:c8:21:10:f4": 9,
		"3b:dc:18:ff:81:91:fe:1f:c3:80:73:c8:21:13:66:01:06:11:59:00:01:28": 10,
		"3b:d9:11:00:81:31:fe:8d:00:00:46:4f:4d:53:31:2e:31:32": 2
	},
	pluginIDCounter: 0,

	ReturnCodes: {
		SurName: 0,
		FirName: 1,
		SecName: 2,
		BirthDay: 3,
		BirthPlace: 4,
		UAddress: 5,
		PAddress: 6,
		Phone: 7,
		Email: 8,
		Gender: 9,
		SNILS: 10,
		PoliceNum: 11,
		OGRN: 12,
		OKATO: 13,
		PoliceBegDate: 14,
		PoliceEndDate: 15,
		CardBegDate: 16,
		CardEndDate: 17
	},
	actions: {
		getStatus: "readCardStatus",
		getPerson: "readCardInf",
		getCert: "readTokenCert",
		signDocumentRaw: "signDocumentRaw",
		getCertList: "getCertList"
	},
	CardTypes_Arr: [],
	Errors: {
		UnknownReader: langs('Ne_najdeno_ustrojstvo_EHCP'),//"Не найдено устройство ЭЦП",
		UnknownCardType: langs('Neizvestnyj_tip_ustrojstva_EHCP'),//"Неизвестный тип устройства ЭЦП",
		UnsupportedCardType: langs('Tip_karty_ne_podderzhivaetsya'),//"Тип карты не поддерживается",
		CardNotExist: langs('Karta_otsutstvuet_v_kart_ridere'),//"Карта отсутствует в карт-ридере",
		NeedPin: langs('Ne_ukazan_pin_kod'),//"Не указан пин-код",
		PluginNotExist: langs('Ne_ustanovlen_plagin_AuthApplet'),//"Не установлен плагин AuthApplet",
		UnsupportdSignOpertaion: langs('Operaciya_podpisi_EHCP_ne_podderzhivaetsya_dannym_vidom_smart_karty'),//"Операция подписи ЭЦП не поддерживается данным видом смарт-карты",
		UnsupportdCertListOpertaion: langs('Operaciya_spiska_hehshej_sertifikatov_ne_podderzhivaetsya_dannym_vidom_smart_karty')//"Операция списка хэшей сертификатов не поддерживается данным видом смарт-карты"
	},
	ErrorCodesHash: {},
	extend: function (Child, Parent) {
		var F = function () {
		}
		F.prototype = Parent.prototype
		Child.prototype = new F()
		Child.prototype.constructor = Child
		Child.superclass = Parent.prototype
	},
	Bentchmark: function () {
		this.beginTime = new Date();
	},
	CommonResponse: function (res) {
		if (res == undefined) {
			this.errorCode = undefined;
			this.errorMessage = undefined;
		} else {
			this.errorCode = res.Result;
			this.errorMessage = res.Error;
		}
	},
	CardTypeResponse: function (response) {
		promedCardAPI.CardTypeResponse.superclass.constructor.call(this, response)
		if (response == undefined) {
			this.cardType = undefined;
			this.readerName = undefined;
		}
	},
	CardData: function (response) {
		promedCardAPI.CardData.superclass.constructor.call(this, response)
		if (response == undefined) {
			this.cardType = undefined;
			this.cardId = undefined;
			this.cardBegDate = undefined;
			this.cardEndDate = undefined;
			this.surname = undefined;
			this.firname = undefined;
			this.secname = undefined;
			this.birthday = undefined;
			this.birthPlace = undefined;
			this.uAddress = undefined;
			this.pAddress = undefined;
			this.phone = undefined;
			this.email = undefined;
			this.sex = undefined;
			this.snils = undefined;
			this.polisNum = undefined;
			this.polisBegDate = undefined;
			this.polisEndDate = undefined;
			this.smoName = undefined;
			this.smoOgrn = undefined;
			this.smoOkato = undefined;
		} else {
			this.cardType = response.CardType;
			this.cardId = response.PAN;
			this.cardBegDate = promedCardAPI.stampToDate(response.CardBegDate);
			this.cardEndDate = promedCardAPI.stampToDate(response.CardEndDate);
			this.surname = response.SurName;
			this.firname = response.FirName;
			this.secname = response.SecName;
			this.birthday = promedCardAPI.stampToDate(response.BirthDay);
			this.birthPlace = response.BirthPlace;
			this.uAddress = response.UAddress;
			this.pAddress = response.PAddress;
			this.phone = response.Phone;
			this.email = response.Email;
			this.sex = undefined;
			if (response.Gender == "М") {
				this.sex = 1
			} else if (response.Gender == "Ж") {
				this.sex = 2
			}
			this.snils = response.SNILS;
			this.polisNum = response.PoliceNum;
			this.polisBegDate = promedCardAPI.stampToDate(response.PoliceBegDate);
			this.polisEndDate = promedCardAPI.stampToDate(response.PoliceEndDate);
			this.smoName = response.SmoName;
			this.smoOgrn = response.SmoOGRN;
			this.smoOkato = response.SMO_OKATO;
		}
	},
	SignResponse: function (response) {
		promedCardAPI.SignResponse.superclass.constructor.call(this, response)
		this.documentSigned = response.SignedData;
	},
	Exception: function (text) {
		if (!promedCardAPI.ErrorCodesHash.filled) {
			var i = 1;
			for (t in promedCardAPI.Errors) {
				if (typeof(promedCardAPI.Errors[t]) == "string") {
					promedCardAPI.ErrorCodesHash[promedCardAPI.Errors[t]] = i
				}
				i++;
			}
			promedCardAPI.ErrorCodesHash.filled = true
		}
		this.errorMessage = text;
		this.errorCode = promedCardAPI.ErrorCodesHash[text]
	},
	checkCardType: function (cardType) {
		if (cardType != undefined
			&& promedCardAPI.CardTypes_Arr.indexOf(cardType) == -1) {
			throw new promedCardAPI.Exception(promedCardAPI.Errors.UnknownCardType)
		}
	},
	checkCardPin: function (cardPin) {
		if (promedCardAPI.strNotDefined(cardPin)) {
			throw new promedCardAPI.Exception(promedCardAPI.Errors.NeedPin)
		}
	},
	parseAppletResp: function (str, delim, fromCertParse) {
		delim = delim || '\n';
		var arr = str.split(delim)
		var resp = {};
		for (var i = 0; i < arr.length; i++) {
			var s = arr[i];
			var ind = s.indexOf('=');
			if (s != '' && ind > -1) {
				var key = s.substr(0, ind);
				var val = s.substr(ind + 1);
				if (fromCertParse && val.indexOf("/") > -0) {
					var tempar = val.split("/");
					resp[key] = tempar[0];
					for (var j = 1; j < tempar.length; j++) {
						var s2 = tempar[j];
						var ind2 = s2.indexOf('=');
						if (s2 != '' && ind2 > -1) {
							var key2 = s2.substr(0, ind2)
							var val2 = s2.substr(ind2 + 1)
							if (key2 == "1.2.643.100.3") {
								resp["SNILS"] = val2
							} else if (key2 == "1.2.643.100.1") {
								resp["OGRN"] = val2
							} else if (key2 == "1.2.643.3.131.1.1") {
								resp["INN"] = val2
							} else if (key2 == "emailAddress") {
								resp["E"] = val2
							} else if (key2 == "title") {
								resp["JOB"] = val2
							}
						}
					}
				} else {
					resp[key.trim()] = val;
				}
			}
		}
		return resp;
	},
	isCardNotExist: function (res) {
		return res != undefined && res.Error == 'Карта отсутствует в карт-ридере';
	},
	strToInt: function (str) {
		var type = typeof(str);
		if (type == "number") return str;
		if (type == "string") return parseInt(str)
		return undefined;
	},
	stampToDate: function (stamp) {
		stamp = promedCardAPI.strToInt(stamp)
		if (stamp == undefined || isNaN(stamp)) return undefined;
		return new Date(stamp * 1000)
	},
	getParamStr: function (action, reader, pin, dopParam) {
		var res = " " + action + ' "' + reader + '" ';
		if (!promedCardAPI.strNotDefined(pin)) res = res + pin;
		if (!promedCardAPI.strNotDefined(dopParam)) res = res + " " + dopParam;
		return res;
	},
	strNotDefined: function (str) {
		return str == undefined || str == '';
	},
	composeReturnValues: function (arr) {
		var result = "00000000000000000000000000000000";
		if (typeof(arr) == "number") {
			result[arr] = "1"
		} else if (arr instanceof Array) {
			for (var i = 0; i < arr.length; i++) {
				result = result.replaceChar(arr[i] + 1, "1")
			}
		}
		return result
	},
	strMayBeUndef: function (str) {
		if (str == undefined)
			return "";
		else
			return str;
	},
	dateToStr: function (date) {
		if (date === undefined) return "";
		var day = date.getDate()
		var month = date.getMonth() + 1
		var year = date.getFullYear()
		if (month < 10) month = '0' + month;
		if (day < 10) day = '0' + day;
		return day + '.' + month + '.' + year;
	},
	returnFromCardDataByCode: function (CardData, code) {
		switch (code) {
			case promedCardAPI.ReturnCodes.SurName:
				return promedCardAPI.CardData.surname;
			case promedCardAPI.ReturnCodes.FirName:
				return promedCardAPI.CardData.firname;
			case promedCardAPI.ReturnCodes.SecName:
				return promedCardAPI.CardData.secname;
			case promedCardAPI.ReturnCodes.BirthDay:
				return promedCardAPI.CardData.birthday;
			case promedCardAPI.ReturnCodes.BirthPlace:
				return promedCardAPI.CardData.birthPlace;
			case promedCardAPI.ReturnCodes.UAddress:
				return promedCardAPI.CardData.uAddress;
			case promedCardAPI.ReturnCodes.PAddress:
				return promedCardAPI.CardData.pAddress;
			case promedCardAPI.ReturnCodes.Phone:
				return promedCardAPI.CardData.phone;
			case promedCardAPI.ReturnCodes.Email:
				return promedCardAPI.CardData.email;
			case promedCardAPI.ReturnCodes.Gender:
				return promedCardAPI.CardData.sex;
			case promedCardAPI.ReturnCodes.SNILS:
				return promedCardAPI.CardData.snils;
			case promedCardAPI.ReturnCodes.PoliceNum:
				return promedCardAPI.CardData.polisNum;
			case promedCardAPI.ReturnCodes.OGRN:
				return promedCardAPI.CardData.smoOgrn;
			case promedCardAPI.ReturnCodes.OKATO:
				return promedCardAPI.CardData.smoOkato;
			case promedCardAPI.ReturnCodes.PoliceBegDate:
				return promedCardAPI.CardData.polisBegDate;
			case promedCardAPI.ReturnCodes.PoliceEndDate:
				return promedCardAPI.CardData.polisEndDate;
		}
	},
	isDefinedInCardData: function (CardData, codes) {
		var isDefinedParam = function (number) {
			return !promedCardAPI.strNotDefined(returnFrompromedCardAPI
				.CardDataByCode(CardData, number))
		}
		if (CardData == undefined || !(CardData instanceof promedCardAPI.CardData))
			return false;
		if (typeof(codes) == "number") {
			return isDefinedParam(codes)
		} else if (codes instanceof Array) {
			for (var i = 0; i < codes.length; i++) {
				if (isDefinedParam(codes[i])) return true;
			}
			return false;
		}
	},
	returnResponse: function (readerName, cardType) {
		var resp = new promedCardAPI.CardTypeResponse();
		resp.cardType = cardType;
		resp.readerName = readerName;
		resp.errorMessage = "OK";
		resp.errorCode = 0;
		return resp;
	},
	getVarParamObj: function () {
		return {
			value: undefined,
			set: function (val) {
				this.value = val
			},
			get: function () {
				return this.value
			}
		}
	},
	getCardType: function(atr) {
		if (
			ETOKEN_PRO_ENABLED && promedCardAPI.ATR_Cards[atr] === 5
		) {
			return 9;
		}
		return promedCardAPI.ATR_Cards[atr];
	},
	getCardTypeByATR: function () {
		if (this == undefined || promedCardAPI.strNotDefined(this.atr)) {
			return undefined;
		}
		return promedCardAPI.getCardType(this.atr);
	},
	isSupportedSign: function (cardType) {
		return (
			cardType == promedCardAPI.CardTypes.PKC12RSA
			|| cardType == promedCardAPI.CardTypes.PKC12Gost
			|| cardType == promedCardAPI.CardTypes.Ktoken
			|| cardType == promedCardAPI.CardTypes.jaCarta_Key
			|| cardType == promedCardAPI.CardTypes.ETokenPro_Key
			|| cardType == promedCardAPI.CardTypes.EToken_Key
			|| cardType == promedCardAPI.CardTypes.RuToken_Key
			|| cardType == promedCardAPI.CardTypes.Lissi_PSMIS_SW_Key
		)
	},
	isSupportedCertList: function (cardType) {
		return (
			cardType == promedCardAPI.CardTypes.PKC12RSA
			|| cardType == promedCardAPI.CardTypes.PKC12Gost
			|| cardType == promedCardAPI.CardTypes.Ktoken
			|| cardType == promedCardAPI.CardTypes.jaCarta_Key
			|| cardType == promedCardAPI.CardTypes.ETokenPro_Key
			|| cardType == promedCardAPI.CardTypes.EToken_Key
			|| cardType == promedCardAPI.CardTypes.Lissi_PSMIS_SW_Key
		)
	},
	CardApi: function () {
		this.constructPlugin = function () {
			this.checkBrowserPlugin(true);
			if (this.browserPlugin) return;
			var bodyEl = document.getElementsByTagName("body")[0];
			if (bodyEl == undefined) return;
			var id = promedCardAPI.browserPlugId = "ProMed_AuthApplet_Obj_"
				+ promedCardAPI.pluginIDCounter;
			promedCardAPI.pluginIDCounter++;

			// проверка наличия плагина
			pluginExists = false;
			if (navigator && navigator.plugins) {
				for (var i = 0; i < navigator.plugins.length; i++) {
					var plugin = navigator.plugins[i];
					if (plugin && plugin['application/x-authapplet']) {
						pluginExists = true;
					}
				}
			}
			if (!pluginExists) {
				return true;
			}

			var plugObj = document.createElement('object');
			plugObj.setAttribute("type", "application/x-authapplet");
			plugObj.setAttribute("id", id);
			plugObj.setAttribute("style", "width:0px;height:0px")
			bodyEl.appendChild(plugObj);
		}
		this.getPlugBridgeAvaliable = function (bridgeName) {
			var test;
			if (promedCardAPI.TIME_LOG) test = new promedCardAPI.Bentchmark();
			if (promedCardAPI.DEVELOP_LOG) {
				console.debug('getPlugBridgeAvaliable ' + bridgeName)
			}
			this.checkBrowserPlugin();
			var res = this.browserPlugin.getBridgeAvaliable(bridgeName)
			if (promedCardAPI.TIME_LOG) test.test()
			if (promedCardAPI.DEVELOP_LOG) promedCardAPI.consoleDebug(res);
			return res;
		}
		this.getBridge_Out = function (params, parse, name) {
			var test;
			if (promedCardAPI.TIME_LOG) test = new promedCardAPI.Bentchmark();
			this.checkBrowserPlugin();
			var res = this.browserPlugin[name](params)
			if (promedCardAPI.TIME_LOG) test.test()
			if (promedCardAPI.DEVELOP_LOG) promedCardAPI.consoleDebug(res);
			if (parse)
				return promedCardAPI.parseAppletResp(res)
			else
				return res;
		}
		this.getEP_Bridge_Out = function (params, parse) {
			return this.getBridge_Out(params, parse, "getEP_Bridge_Output")
		}
		this.getPSMIS_Bridge_Out = function (params, parse) {
			return this.getBridge_Out(params, parse, "getPSMIS_Bridge_Output")
		}
		this.getEToken_Bridge_Out = function (params, parse) {
			return this.getBridge_Out(params, parse, "getEToken_Bridge_Output")
		}
		this.getETokenPro_Bridge_Out = function (params, parse) {
			return this.getBridge_Out(params, parse, "getETokenPro_Bridge_Output")
		}
		this.getJaCarta_Bridge_Out = function (params, parse) {
			return this.getBridge_Out(params, parse, "getJaCarta_Bridge_Output")
		}
		this.getKtoken_Bridge_Output = function (params, parse) {
			return this.getBridge_Out(params, parse, "getKtoken_Bridge_Output")
		}
		this.getPKC12Gost_Bridge_Output = function (params, parse) {
			return this.getBridge_Out(params, parse, "getPKC12Gost_Bridge_Output")
		}
		this.getPKC12RSA_Bridge_Output = function (params, parse) {
			return this.getBridge_Out(params, parse, "getPKC12RSA_Bridge_Output")
		}
		this.getUEC_Bridge_Out = function (params, parse) {
			return this.getBridge_Out(params, parse, "getUEC_Bridge_Output")
		}

		this.getReadersLister_Out = function () {
			var test;
			if (promedCardAPI.TIME_LOG) test = new promedCardAPI.Bentchmark();
			this.checkBrowserPlugin();
			var res = eval(this.browserPlugin.getReadersLister_Output())
			if (promedCardAPI.TIME_LOG) test.test()
			if (promedCardAPI.DEVELOP_LOG) promedCardAPI.consoleDebug(res);
			return res;
		}
		this.beginLongFunc = function () {
			this.flagLongFunc = this.flagLongFunc || 0;
			if (this.flagLongFunc == 0) {
				delete this.cachedReadersList;
				this.getReaders();
			}
			this.flagLongFunc++;
		}
		this.endLongFunc = function () {
			this.flagLongFunc--;
			if (this.flagLongFunc == 0) {
				delete this.cachedReadersList;
			}
		}
		this.getBrowserPlugin = function () {
			if (promedCardAPI.browserPlugId)
				return document.getElementById(promedCardAPI.browserPlugId);
		}
		this.checkBrowserPlugin = function (notThrow) {
			this.browserPlugin = this.browserPlugin || this.getBrowserPlugin()
			if (this.browserPlugin == undefined && !notThrow) {
				throw new promedCardAPI.Exception(promedCardAPI.Errors.PluginNotExist)
			}
		}
		this.UECInitialize = function (SectorsIni, TerminalIni) {
			this.SectorsIni = SectorsIni;
			this.TerminalIni = TerminalIni;
		}
		this.getReaders = function () {
			if (this.flagLongFunc && this.cachedReadersList) {
				return this.cachedReadersList;
			}
			var response = this.getReadersLister_Out();
			for (var i = 0; i < response.length; i++) {
				response[i].getCardTypeByATR = promedCardAPI.getCardTypeByATR;
			}
			this.cachedReadersList = response;
			return response;
		}
		this.getReaders_str = function (readers) {
			var res = [];
			for (var i = 0; i < readers.length; i++) {
				res.push(readers[i].readerName)
			}
			return res;
		}
		this.checkReaderName = function (readerName, obj) {
			if (promedCardAPI.strNotDefined(readerName)) //return;
				throw new promedCardAPI.Exception(promedCardAPI.Errors.UnknownReader)
			var readers = this.getReaders();
			var arr = this.getReaders_str(readers);
			var index = arr.indexOf(readerName);
			if (index == -1) {
				throw new promedCardAPI.Exception(promedCardAPI.Errors.UnknownReader)
			}
			if (obj != undefined) {
				obj.set(readers[index].cardPresence)
				obj.atr = readers[index].atr
				obj.getCardTypeByATR = promedCardAPI.getCardTypeByATR
			}
			return arr;
		}
		this.getSupportCards = function () {
			var resp = [];
			resp.push(promedCardAPI.CardTypes.UEC_Card);
			resp.push(promedCardAPI.CardTypes.EPolice_Card);
			resp.push(promedCardAPI.CardTypes.EToken_Key);
			resp.push(promedCardAPI.CardTypes.Lissi_PSMIS_SW_Key);
			resp.push(promedCardAPI.CardTypes.ETokenPro_Key);
			resp.push(promedCardAPI.CardTypes.jaCarta_Key);
			resp.push(promedCardAPI.CardTypes.Ktoken);
			resp.push(promedCardAPI.CardTypes.PKC12Gost);
			resp.push(promedCardAPI.CardTypes.PKC12RSA);
			return resp;
		}
		this.isSupported = function (code) {
			this.supportedCash = this.supportedCash || {};
			if (this.supportedCash[code] == undefined) {
				this.supportedCash[code] = (this.getSupportCards().indexOf(code) > -1)
					&& (this.getPlugBridgeAvaliable(promedCardAPI.CardBridgeNames[code]) == true);
			}
			return this.supportedCash[code]
		}
		this.getCardType = function (readerName) {
			try {
				var res;
				try {
					this.beginLongFunc()
					var obj = promedCardAPI.getVarParamObj();
					this.checkReaderName(readerName, obj);
					if (!obj.get()) {
						throw new promedCardAPI.Exception(promedCardAPI.Errors.CardNotExist)
					}
					if (obj.getCardTypeByATR()
						&& this.isSupported(obj.getCardTypeByATR())) {
						return promedCardAPI.returnResponse(readerName, obj
							.getCardTypeByATR())
					}
					if (this.isSupported(promedCardAPI.CardTypes.EPolice_Card)) {
						res = this.getEP_Bridge_Out(promedCardAPI.getParamStr(
							promedCardAPI.actions.getStatus, readerName), true);
						if (res.Error == 'OK') {
							return promedCardAPI.returnResponse(readerName,
								promedCardAPI.CardTypes.EPolice_Card)
						}
					}
					if (this.isSupported(promedCardAPI.CardTypes.UEC_Card)) {
						res = this.getUEC_Bridge_Out(promedCardAPI.getParamStr(
							promedCardAPI.actions.getStatus, readerName), true)
						if (res.Error == 'OK') {
							return promedCardAPI.returnResponse(readerName,
								promedCardAPI.CardTypes.UEC_Card)
						}
					}
				} finally {
					this.endLongFunc()
				}
			} catch (e) {
				if (e instanceof promedCardAPI.Exception) {
					var resp = new promedCardAPI.CardTypeResponse();
					resp.errorMessage = e.errorMessage;
					resp.errorCode = e.errorCode || 1;
					return resp;
				}
			}
			var resp = new promedCardAPI.CardTypeResponse();
			resp.readerName = "";
			resp.errorMessage = promedCardAPI.Errors.UnknownCardType
			resp.errorCode = 1;
			return resp;

		}
		this.checkSupportedCardType = function (cardType) {
			promedCardAPI.checkCardType(cardType)
			if (!this.isSupported(cardType)) {
				throw new promedCardAPI.Exception(promedCardAPI.Errors.UnsupportedCardType)
			}
		}
		this.getCardStatus = function (readerName, cardType) {
			try {
				this.beginLongFunc()
				var obj = promedCardAPI.getVarParamObj();
				this.checkReaderName(readerName, obj);
				if (!obj.get()) {
					throw new promedCardAPI.Exception(promedCardAPI.Errors.CardNotExist)
				}
				if (promedCardAPI.strNotDefined(cardType)
					&& !promedCardAPI.strNotDefined(readerName)) {
					return this.getCardType(readerName);
				} else {
					this.checkSupportedCardType(cardType)
					var readers = this.getReaders();
					for (var i = 0; i < readers.length; i++) {
						if (readers[i].readerName == readerName) {
							if (readers[i].cardPresence == 0) {
								var res = new promedCardAPI.CardTypeResponse()
								res.cardType = cardType;
								res.readerName = readerName;
								res.errorMessage = promedCardAPI.Errors.CardNotExist;
								res.errorCode = 1;
								return res;
							}
							if (readers[i].getCardTypeByATR() == cardType) {
								var res = new promedCardAPI.CardTypeResponse()
								res.cardType = cardType;
								res.readerName = readerName;
								res.errorMessage = "OK";
								res.errorCode = 0;
								return res;
							} else {
								var res = new promedCardAPI.CardTypeResponse()
								res.cardType = promedCardAPI.CardTypes.UnknownCard;
								res.readerName = readerName;
								res.errorMessage = promedCardAPI.Errors.UnknownCardType;
								res.errorCode = 2;
								return res;
							}
						}
					}
					var res = new promedCardAPI.CardTypeResponse()
					res.cardType = promedCardAPI.CardTypes.UnknownCard;
					res.readerName = "";
					res.errorMessage = promedCardAPI.Errors.UnknownReader;
					res.errorCode = 2;
					return res;
				}
			} finally {
				this.endLongFunc()
			}
		}
		this.findCard = function (cardType) {
			try {
				try {
					this.beginLongFunc()
					this.checkSupportedCardType(cardType);
					var readers = this.getReaders()
					for (var i = 0; i < readers.length; i++) {
						if (readers[i].atr
							&& promedCardAPI.getCardType(readers[i].atr) == cardType) {
							return promedCardAPI.returnResponse(readers[i].readerName,
								cardType)
						}
					}
					for (var i = 0; i < readers.length; i++) {
						if (readers[i].cardPresence) {
							if (this.getCardStatus(readers[i].readerName, cardType).errorMessage == "OK")
								return promedCardAPI.returnResponse(readers[i].readerName,
									cardType)
						}
					}
					var resp = new promedCardAPI.CardTypeResponse();
					resp.cardType = cardType;
					resp.readerName = "";
					resp.errorMessage = promedCardAPI.Errors.UnknownReader;
					resp.errorCode = 1;
					return resp;
				} finally {
					this.endLongFunc()
				}
			} catch (e) {
				if (e instanceof promedCardAPI.Exception) {
					var resp = new promedCardAPI.CardTypeResponse();
					resp.readerName = "";
					resp.errorMessage = e.errorMessage;
					resp.errorCode = e.errorCode || 1;
					return resp;
				}
			}
		}
		this.checkReaderAndCard = function (obj) {
			if (promedCardAPI.strNotDefined(obj.readerName)
				&& !promedCardAPI.strNotDefined(obj.cardType)) {
				obj.readerName = this.findCard(obj.cardType).readerName
			} else if (promedCardAPI.strNotDefined(obj.cardType)
				&& !promedCardAPI.strNotDefined(obj.readerName)) {
				obj.cardType = this.getCardType(obj.readerName).cardType
			}
			this.checkReaderName(obj.readerName);
			this.checkSupportedCardType(obj.cardType);
		}
		this.parseCertResponse = function (certResponse, cardtype, callback) {
			var cutDate = function (str) {
				var res = str.trim().substr(11).replace(/GMT.*$/, "");
				while (res.indexOf("  ") > -1) {
					res = res.replace(/  /g, " ");
				}
				return res.trim();
			}
			var decodeUtfStr = function (str) {
				var res = "";
				var i = 0;
				while (i < str.length) {
					if (str[i] == "\\" && str[i + 1] == "x") {
						var tempstr = str.substr(i, 8);
						i = i + 8;
						res += promedCardAPI.tableUtfRecoder[tempstr];
					} else {
						res += str[i];
						i++;
					}
				}
				return res;
			}
			var arr = certResponse.split('\n');
			var resp = new promedCardAPI.CardData(promedCardAPI
				.parseAppletResp(arr[0] + '\n' + arr[1]));
			resp.cardType = cardtype;

			if (arr[2] == "RawCert64Coded") {
				promedCardAPI.ajaxRequest(promedCardAPI.parseCertUrl, {
					cert: arr[3]
				}, function (data) {
					var arr = data.dump;
					for (var i = 0; i < arr.length; i++) {
						arr[i] = decodeUtfStr(arr[i]);
					}
					for (var i = 0; i < arr.length; i++) {
						var str = arr[i].trim();
						if (str == "Serial Number:") {
							resp.cardId = arr[i + 1].trim().toUpperCase().replace(/:/g, " ")
						} else if (str == "Validity") {
							var date_before_str = cutDate(arr[i + 1]);
							var date_after_str = cutDate(arr[i + 2]);
							var date_before = getDateFromFormat(date_before_str,
								"MMM d HH:mm:ss yyyy")
							var date_after = getDateFromFormat(date_after_str,
								"MMM d HH:mm:ss yyyy")
							if (date_before != 0) {
								resp.cardBegDate = new Date(date_before)
							}
							if (date_after != 0) {
								resp.cardEndDate = new Date(date_after)
							}
						} else if (str.indexOf('Subject:') > -1) {
							str = str.substr(str.indexOf('Subject:') + 8).trim();
							var obj_sub = promedCardAPI.parseAppletResp(str, ",", true);
							if (!promedCardAPI.strNotDefined(obj_sub.CN)) {
								resp.firname = obj_sub.CN;
							}
							if (!promedCardAPI.strNotDefined(obj_sub.E)) {
								resp.email = obj_sub.E;
							}
							if (!promedCardAPI.strNotDefined(obj_sub.SN)) {
								resp.surname = obj_sub.SN;
							}
							if (!promedCardAPI.strNotDefined(obj_sub.SNILS)) {
								resp.snils = obj_sub.SNILS;
							}
						}
					}
					callback(resp);
				})
			} else {

			}

			return resp;

		}
		this.getOutResponse = function (action, readerName, cardPin, cardType,
										parse, dopParam, callback) {

			if (dopParam != undefined && callback == undefined
				&& typeof(dopParam) == "function") {
				callback = dopParam;
				dopParam = undefined;
			}
			if (cardType == promedCardAPI.CardTypes.UEC_Card) {
				var res = this.getUEC_Bridge_Out(promedCardAPI.getParamStr(action,
					readerName, cardPin, dopParam), parse);
				if (callback == undefined)
					return res;
				else
					callback(res)
			}
			if (cardType == promedCardAPI.CardTypes.EPolice_Card) {
				var res = this.getEP_Bridge_Out(promedCardAPI.getParamStr(action,
					readerName, cardPin, dopParam), parse)
				if (callback == undefined)
					return res;
				else
					callback(res)
			}
			if (cardType == promedCardAPI.CardTypes.EToken_Key) {
				var isGetPerson = action == promedCardAPI.actions.getPerson;
				var result = this.getEToken_Bridge_Out(promedCardAPI.getParamStr(
					isGetPerson ? promedCardAPI.actions.getCert : action, readerName,
					cardPin, dopParam), isGetPerson ? false : parse);
				if (isGetPerson) {
					this.parseCertResponse(result, promedCardAPI.CardTypes.EToken_Key,
						callback)
				} else if (callback == undefined)
					return result;
				else
					callback(result)
			}
			if (cardType == promedCardAPI.CardTypes.ETokenPro_Key) {
				var isGetPerson = action == promedCardAPI.actions.getPerson;
				var result = this.getETokenPro_Bridge_Out(promedCardAPI.getParamStr(
					isGetPerson ? promedCardAPI.actions.getCert : action, readerName,
					cardPin, dopParam), isGetPerson ? false : parse);
				if (isGetPerson) {
					this.parseCertResponse(result, promedCardAPI.CardTypes.ETokenPro_Key,
						callback)
				} else if (callback == undefined)
					return result;
				else
					callback(result)
			}
			if (cardType == promedCardAPI.CardTypes.jaCarta_Key) {
				var isGetPerson = action == promedCardAPI.actions.getPerson;
				var result = this.getJaCarta_Bridge_Out(promedCardAPI.getParamStr(
					isGetPerson ? promedCardAPI.actions.getCert : action, readerName,
					cardPin, dopParam), isGetPerson ? false : parse);
				if (isGetPerson) {
					this.parseCertResponse(result, promedCardAPI.CardTypes.jaCarta_Key,
						callback)
				} else if (callback == undefined)
					return result;
				else
					callback(result)
			}
			if (cardType == promedCardAPI.CardTypes.Ktoken) {
				var isGetPerson = action == promedCardAPI.actions.getPerson;
				var result = this.getKtoken_Bridge_Output(promedCardAPI.getParamStr(isGetPerson
						? promedCardAPI.actions.getCert : action, readerName, cardPin, dopParam),
					isGetPerson ? false : parse);
				if (isGetPerson) {
					this.parseCertResponse(result, promedCardAPI.CardTypes.Ktoken, callback)
				} else if (callback == undefined)
					return result;
				else
					callback(result)
			}
			if (cardType == promedCardAPI.CardTypes.PKC12Gost) {
				var isGetPerson = action == promedCardAPI.actions.getPerson;
				var result = this.getPKC12Gost_Bridge_Output(promedCardAPI.getParamStr(isGetPerson
						? promedCardAPI.actions.getCert : action, readerName, cardPin, dopParam),
					isGetPerson ? false : parse);
				if (isGetPerson) {
					this.parseCertResponse(result, promedCardAPI.CardTypes.PKC12Gost, callback)
				} else if (callback == undefined)
					return result;
				else
					callback(result)
			}
			if (cardType == promedCardAPI.CardTypes.PKC12RSA) {
				var isGetPerson = action == promedCardAPI.actions.getPerson;
				var result = this.getPKC12RSA_Bridge_Output(promedCardAPI.getParamStr(isGetPerson
						? promedCardAPI.actions.getCert : action, readerName, cardPin, dopParam),
					isGetPerson ? false : parse);
				if (isGetPerson) {
					this.parseCertResponse(result, promedCardAPI.CardTypes.PKC12RSA, callback)
				} else if (callback == undefined)
					return result;
				else
					callback(result)
			}
			if (cardType == promedCardAPI.CardTypes.Lissi_PSMIS_SW_Key) {
				var isGetPerson = action == promedCardAPI.actions.getPerson;
				var result = this.getPSMIS_Bridge_Out(promedCardAPI.getParamStr(
					isGetPerson ? promedCardAPI.actions.getCert : action, readerName,
					cardPin, dopParam), isGetPerson ? false : parse);
				if (isGetPerson) {
					this.parseCertResponse(result,
						promedCardAPI.CardTypes.Lissi_PSMIS_SW_Key, callback)
				} else if (callback == undefined)
					return result;
				else
					callback(result)
			}
		}
		this.doFindAndRun = function (readerName, cardType, cardPin, callback) {
			if (cardType != undefined) {
				this.checkSupportedCardType(cardType);
			}

			// if no need a reader
			if (cardType == promedCardAPI.CardTypes.PKC12Gost || cardType == promedCardAPI.CardTypes.PKC12RSA) {
				return callback.call(this, readerName, cardType, cardPin);
			}

			try {
				this.beginLongFunc()
				promedCardAPI.checkCardPin(cardPin);
				var obj = {
					readerName: readerName,
					cardType: cardType
				};
				this.checkReaderAndCard(obj);
				readerName = obj.readerName;
				cardType = obj.cardType;
				return callback.call(this, readerName, cardType, cardPin)
			} finally {
				this.endLongFunc()
			}
		}
		this.getCardData = function (readerName, cardType, cardPin, callback) {
			return this.doFindAndRun(readerName, cardType, cardPin, function (readerName, cardType, cardPin) {
				if (callback == undefined) {
					var result = this.getOutResponse(promedCardAPI.actions.getPerson,
						readerName, cardPin, cardType, true)
					if (!(result instanceof promedCardAPI.CardData))
						return new promedCardAPI.CardData(result);
					else
						return result;
				} else {
					this.getOutResponse(promedCardAPI.actions.getPerson, readerName,
						cardPin, cardType, true, function (result) {
							if (!(result instanceof promedCardAPI.CardData))
								callback(new promedCardAPI.CardData(result))
							else
								callback(result);
						})
				}
			})
		}
		this.getFIO = function (readerName, cardType, cardPin, callback) {
			return this.doFindAndRun(readerName, cardType, cardPin, function (readerName, cardType, cardPin) {
				var obj;
				var codes = [promedCardAPI.ReturnCodes.SurName,
					promedCardAPI.ReturnCodes.FirName, promedCardAPI.ReturnCodes.SecName,
					promedCardAPI.ReturnCodes.BirthDay];
				if (callback == undefined) {
					obj = new promedCardAPI.CardData(this.getOutResponse(
						promedCardAPI.actions.getPerson, readerName, cardPin, cardType,
						true, promedCardAPI.composeReturnValues(codes)));
					if (!isDefinedInpromedCardAPI.CardData(obj, codes)) return "";
					var result = promedCardAPI.strMayBeUndef(obj.surname) + ' '
						+ promedCardAPI.strMayBeUndef(obj.firname) + ' '
						+ promedCardAPI.strMayBeUndef(obj.secname) + ' '
						+ promedCardAPI.dateToStr(obj.birthday);
					return result.trim()
				} else {
					this.getOutResponse(promedCardAPI.actions.getPerson, readerName,
						cardPin, cardType, true, promedCardAPI.composeReturnValues(codes),
						function (res) {
							obj = (res instanceof promedCardAPI.CardData) ? res
								: new promedCardAPI.CardData(res);
							if (!isDefinedInpromedCardAPI.CardData(obj, codes)) callback("");
							var result = promedCardAPI.strMayBeUndef(obj.surname) + ' '
								+ promedCardAPI.strMayBeUndef(obj.firname) + ' '
								+ promedCardAPI.strMayBeUndef(obj.secname) + ' '
								+ promedCardAPI.dateToStr(obj.birthday);
							callback(result.trim())
						})
				}
			})
		}
		this.getOneCodeValue = function (readerName, cardType, cardPin, codeValue,
										 callback) {
			return this.doFindAndRun(readerName, cardType, cardPin, function (readerName, cardType, cardPin) {
				var obj;
				var codes = [codeValue];
				if (callback == undefined) {
					obj = new promedCardAPI.CardData(this.getOutResponse(
						promedCardAPI.actions.getPerson, readerName, cardPin, cardType,
						true, promedCardAPI.composeReturnValues(codes)));
					if (!isDefinedInpromedCardAPI.CardData(obj, codes)) return "";
					return promedCardAPI.strMayBeUndef(returnFrompromedCardAPI
						.CardDataByCode(obj, codeValue)).trim();
				} else {
					this.getOutResponse(promedCardAPI.actions.getPerson, readerName,
						cardPin, cardType, true, promedCardAPI.composeReturnValues(codes),
						function (result) {
							obj = (result instanceof promedCardAPI.CardData) ? res
								: new promedCardAPI.CardData(res);
							if (!isDefinedInpromedCardAPI.CardData(obj, codes)) callback("");
							callback(promedCardAPI.strMayBeUndef(returnFrompromedCardAPI
								.CardDataByCode(obj, codeValue)).trim());
						})
				}
			})
		}
		this.getGost341194Hash = function (data) {
			this.checkBrowserPlugin()
			return this.getBrowserPlugin().GostHash(data)
		}
		this.signDocumentRaw = function (readerName, cardType, cardPin, documentHash) {
			if (cardType === 9) {
				ETOKEN_PRO_ENABLED = true; // включаем, если хотят подписывать с eToken Pro
			} else if (cardType === 5) {
				ETOKEN_PRO_ENABLED = false; // вывключаем, если хотят подписывать с eToken ГОСТ
			}
			return this.doFindAndRun(readerName, cardType, cardPin, function (readerName, cardType, cardPin) {
				promedCardAPI.checkCardPin(cardPin)
				if (!promedCardAPI.isSupportedSign(cardType)) {
					var result = new promedCardAPI.SignResponse();
					result.errorMessage = promedCardAPI.Errors.UnsupportdSignOpertaion;
					result.errorCode = 1;
					return result;
				}
				return new promedCardAPI.SignResponse(this.getOutResponse(
					promedCardAPI.actions.signDocumentRaw, readerName, cardPin, cardType,
					true, documentHash));
			})
		}
		this.getCertList = function (readerName, cardType, cardPin) {
			return this.doFindAndRun(readerName, cardType, cardPin, function (readerName, cardType, cardPin) {
				if (!promedCardAPI.isSupportedCertList(cardType)) {
					var result = new promedCardAPI.CommonResponse();
					result.errorMessage = promedCardAPI.Errors.UnsupportdCertListOpertaion;
					result.errorCode = 1;
					return result;
				}
				return eval(this.getOutResponse(promedCardAPI.actions.getCertList,
					readerName, cardPin, cardType, false));
			})
		}
		this.getEmail = function (readerName, cardType, cardPin) {
			return this.getOneCodeValue(readerName, cardType, cardPin,
				promedCardAPI.ReturnCodes.Email)
		}
		this.getSnils = function (readerName, cardType, cardPin) {
			return this.getOneCodeValue(readerName, cardType, cardPin,
				promedCardAPI.ReturnCodes.SNILS)
		}
		this.getPhone = function (readerName, cardType, cardPin) {
			return this.getOneCodeValue(readerName, cardType, cardPin,
				promedCardAPI.ReturnCodes.Phone)
		}
		this.constructPlugin();
	}
}
for (t in promedCardAPI.CardTypes) {
	if (typeof(promedCardAPI.CardTypes[t]) == "number") {
		promedCardAPI.CardTypes_Arr.push(promedCardAPI.CardTypes[t])
	}
}
promedCardAPI.Bentchmark.prototype = {
	test: function () {
		if (promedCardAPI.DEVELOP_LOG) {
			promedCardAPI.consoleDebug(((new Date()).getTime() - this.beginTime
					.getTime())
				+ ' ms');
		}
	}
}
promedCardAPI.extend(promedCardAPI.CardTypeResponse,
	promedCardAPI.CommonResponse)
promedCardAPI.extend(promedCardAPI.CardData, promedCardAPI.CommonResponse)
promedCardAPI.extend(promedCardAPI.SignResponse, promedCardAPI.CommonResponse)
if (console == undefined
	|| (console.debug == undefined && console.warn == undefined)) {
	promedCardAPI.TIME_LOG = promedCardAPI.DEVELOP_LOG = false;
}
