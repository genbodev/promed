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
			this.response.ErrorMessage = 'Не найден апплет для чтения данных анализатора.';
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
				this.response.ErrorMessage = 'Ошибка при инициализации порта анализатора.';
			}
		}
		else 
		{
			this.response.ErrorCode = 2;
			this.response.ErrorMessage = 'Апплет недоступен или метод апплета не обнаружен.';
		}
		return this.response;
	},
	connect : function()
	{
		this.response = {};
		if (!this.applet)
		{
			this.response.ErrorCode = 1;
			this.response.ErrorMessage = 'Не найден апплет для чтения данных анализатора.';
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
				this.response.ErrorMessage = 'Ошибка при соединении с анализатором.';
			}
		}
		else 
		{
			this.response.ErrorCode = 2;
			this.response.ErrorMessage = 'Апплет недоступен или метод апплета не обнаружен.';
		}
		return this.response;
	},
	getResult : function()
	{
		this.response = {};
		if (!this.applet)
		{
			this.response.ErrorCode = 1;
			this.response.ErrorMessage = 'Не найден апплет для чтения данных анализатора.';
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
				this.response.ErrorMessage = 'Ошибка получения данных от анализатора.';
			}
		}
		else 
		{
			this.response.ErrorCode = 2;
			this.response.ErrorMessage = 'Апплет недоступен или метод апплета не обнаружен.';
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
				setPromedInfo('Отсутствует java машина. Работа с картами будет недоступна.<br/>Для установки java машины зайдите на сайт <a href=http://java.com/ru>http://java.com/ru</a>', 'javamashine-info');
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
			sw.swMsg.alert('Ошибка', 'Апплет не инициализирован. Повторите попытку.', function(){});
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

// Получение данных с анализатора
sw.Applets.uec = {
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
		this.initUec();
		if (Ext.globalOptions.others.enable_uecreader) {
			if(this.uecIntervalObj) {
				clearInterval(this.uecIntervalObj);
			}
			log('Включаем считывание с УЭК');
			if (options && options.callback) {
				this.setCallback(options.callback);
			}
			var uecInterval = (options && options.interval)?options.interval:null || Ext.globalOptions.others.uecreader_interval;
			this.uecIntervalObj = setInterval(this.getUecData.bind(this), uecInterval);
			this.readerActive = true;
		}
	},
	stopUecReader: function() {
		log('Выключаем считывание с УЭК');
		this.readerActive = false;
		if(this.uecIntervalObj) {
			clearInterval(this.uecIntervalObj);
		}
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
				response.success = true;
				response.ErrorCode = null;
				response.ErrorMessage = null;
			} catch (err) {
				response.ErrorCode = 3;
				response.ErrorMessage = this.getErrorMessage(err);
			}
		}
		else
		{
			response.ErrorCode = 2;
			response.ErrorMessage = 'Не найден плагин КриптоПро.';
		}
		return response;
	},
	/**
	 * Чтение карты
	 */
	getUecData: function (options, params) {
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
			getWnd('swPINCodeWindow').show({
				params: params,
				callback: function(data) {
					if (data && data.pin) {
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
								this.getPerson(response, options);
							}
							var oPersonalCardholderData = oCard.PersonalCardholderData(true);
						} catch (err) {
							var p = {msg: this.getErrorMessage(err)};
							this.getUecData(options, p);
							log(err);
						}
					} else {
						this.startUecReader();
					}
				}.bind(this),
				onHide: function() {
					this.startUecReader();
				}.bind(this)
			});
		}
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
				Ext.Msg.alert('Внимание', 'Считывание УЭК произведено, достаньте карту.<br>Для продолжения считывания нажмите "ОК".', function() {
					this.startUecReader();
				}.bind(this));
				if (options && options.callback) {
					options.callback(data, response_obj[0]);
				} else {
					if (this.callback) {
						this.callback(data, response_obj[0]);
					}
				}
			}.bind(this),
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
			// проверяем наличие плагина cadesplugin и если его нет то не подключаем его.
			var enabledPlugin = false;
			
			// своя логика для IE
			if (navigator.appName == "Microsoft Internet Explorer") {
				try {
					this.uecObject = new ActiveXObject("CAdESCOM.UECard");
				}
				catch (e) {
					setPromedInfo('Отсутствует плагин КриптоПро. Работа с картами УЭК будет недоступна.', 'cryptoproplugin-info');
				}
				return true;
			}
			
			var userAgent = navigator.userAgent;
			if (userAgent.match(/ipod/i) || userAgent.match(/ipad/i) || userAgent.match(/iphone/i)) {
				enabledPlugin = true;
			} else {
				var mimetype = navigator.mimeTypes["application/x-cades"];
				if (mimetype && mimetype.enabledPlugin) {
					enabledPlugin = true;
				}
			}
			
			if (enabledPlugin) {		
				var uecapplet = Ext.getBody().createChild({
					'tag': 'object',
					'class': 'hiddenObject',
					'type': 'application/x-cades',
					'width': 0,
					'height': 0,
					'id': 'cadesplugin'
				});
					
				var cadesobject = document.getElementById("cadesplugin");
				this.uecObject = cadesobject.CreateObject("CAdESCOM.UECard");
			} else {
				setPromedInfo('Отсутствует плагин КриптоПро. Работа с картами УЭК будет недоступна.', 'cryptoproplugin-info');
			}
		}

		return true;
	}
}
/***************************/
sw.Applets.bdz = {
	applet: document.bdzapplet,
	readerActive: false,
	flag:null,
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
		name = (name)?name:'bdzapplet';
		this.applet = document[name];
	},
	startBdzReader: function(options) {
		if (Ext.globalOptions.others.enable_bdzreader) {
			if(this.bdzIntervalObj) {
				clearInterval(this.bdzIntervalObj);
			}
			log('Включаем считывание штрих-кода с полиса единого образца');
			if (options && options.callback) {
				this.setCallback(options.callback);
			}
			var bdzInterval = (options && options.interval)?options.interval:null || Ext.globalOptions.others.uecreader_interval;
			this.bdzIntervalObj = setInterval(this.getBdzData.bind(this), bdzInterval);
			this.readerActive = true;
		}
	},
	stopBdzReader: function() {
		log('Выключаем считывание с штрих-кода с полиса единого образца');
		this.readerActive = false;
		if(this.bdzIntervalObj) {
			clearInterval(this.bdzIntervalObj);
		}
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
		var response = {success: false, ErrorCode: 1, ErrorMessage: 'Ошибка работы с УЭК.'};
		// проверяем наличие плагина
		if ( this.applet )
		{
			// проверка на наличие карт ридера
			try{
				var test = this.applet.BDZCardWelcomeText; // пробуем получить приветсвие :)
				// далее необходим ввод пин-кода
				response.success = true;
				response.ErrorCode = null;
				response.ErrorMessage = null;
			} catch (err) {
				response.ErrorCode = 3;
				response.ErrorMessage = this.getErrorMessage(err);
			}
		}
		else
		{
			response.ErrorCode = 2;
			response.ErrorMessage = 'Ошибка, картридер не подключен.';
			
		}
		
		return response;
	},
	/**
	 * Чтение карты
	 */
	getBdzData: function (options, params) {
		this.bdzData = this.getBdzStatus();
		log(this.bdzData);
		if (this.bdzData.success)
		{
			var oCard = document.BDZSmartCardApplet.readPolicy();
			
			if(oCard.polisNum!=null){
			if(this.flag!=oCard.polisNum){
			
				try {
					this.stopBdzReader();
					this.flag=oCard.polisNum;
					
					var response = {};
					response.bdzNum = '';
					response.surName = oCard.surName;
					response.firName = oCard.firName;
					response.secName = oCard.secName;
					response.birthDay = oCard.birthDay;
					response.polisNum = oCard.polisNum;
					response.success = true;
					response.ErrorCode = null;
					response.ErrorMessage = null;
					if (!response.ErrorCode) {
						this.getPerson(response, options);
					}
				} catch (err) {
					var p = {msg: this.getErrorMessage(err)};
					this.getBdzData(options, p);
					log(err);
				}
			} 	
		}else{this.flag=null;}}
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
					// todo: функция которая должна выполняться this.callback
					if (options && options.callback) {
						options.callback(data, response_obj[0]);
					} else
						if (this.callback) {
							this.callback(data, response_obj[0]);
						}
				}else{sw.swMsg.alert('Ошибка', 'Данного человека нет БД', function(){});}
				this.startBdzReader(); // стартовать опрос лучше всего в каллбэке
			}.bind(this),
			url: '?c=Person&m=getPersonByUecData'
		});
	},
	/** Инициализация апплета, если еще не инициализирован и получение данных с анализатора
	 * В качестве параметра передается объект, в который будет включен апплет и выполнена инициализация апплета
	 */
	initBdz: function () {
		name = (name)?name:'BDZSmartCardApplet';
		if (navigator.javaEnabled()) {
			if (!document[name]) {
				// Аплет получения данных с анализатора
				var bdzapplet = Ext.getBody().createChild({
					name: name,
					tag: 'applet',
					archive:'applets/swan-smartcard-bdz.jar',
					code:'ru/swan/smartcard/bdz/EPolicyPersonApplet',
					width: 0,
					height: 0,
					id: 'java_Applets_'+name,
					style:'width:1px,height:1px'
				});
				this.init(name);
			}
			return true;
		} else {
			setPromedInfo('Отсутствует java машина. Работа со штрих-кодами полисов единого образца будет недоступна.<br/>Для установки java машины зайдите на сайт <a href=http://java.com/ru>http://java.com/ru</a>', 'javamashine-info');
		}
		return false;
	}
}
/***************************/
// Получение данных с анализатора
sw.Applets.uecOld = { // не стал косячить код, переименовал в uecOld.
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
		if (Ext.globalOptions.others.enable_uecreader) {
			if(this.uecIntervalObj) {
				clearInterval(this.uecIntervalObj);
			}
			log('Включаем считывание с УЭК');
			if (options && options.callback) {
				this.setCallback(options.callback);
			}
			var uecInterval = (options && options.interval)?options.interval:null || Ext.globalOptions.others.uecreader_interval;
			this.uecIntervalObj = setInterval(this.getUecData.bind(this), uecInterval);
			this.readerActive = true;
		}
	},
	stopUecReader: function() {
		log('Выключаем считывание с УЭК');
		this.readerActive = false;
		if(this.uecIntervalObj) {
			clearInterval(this.uecIntervalObj);
		}
	},
	/**
	 * Чтение карты и проверка статуса
	 */
	getUecStatus: function () {
		var response = {success: false, ErrorCode: 1, ErrorMessage: 'Ошибка работы с УЭК.'};
		// проверяем наличие плагина
		if ( this.applet )
		{
			// проверяем доступность методов
			if ( typeof document.uecapplet.getUecStatus == 'unknown' || document.uecapplet.getUecStatus ) // IE, это один большой костыль и УГ
			{
				// вызываем методы
				try
				{
					var uecStatus = document.uecapplet.getUecStatus((sw.Applets.readers[0])?sw.Applets.readers[0]:'');
					switch(uecStatus) {
						case 0:
							response.ErrorCode = 5;
							response.ErrorMessage = 'Отсутсвует картридер.';
							break;
						case 1:
							response.ErrorCode = 6;
							response.ErrorMessage = 'Карта не вставлена в картридер.';
							break;
						case 2:
							// далее необходим ввод пин-кода
							response.success = true;
							response.ErrorCode = null;
							response.ErrorMessage = null;
							break;
						default:
							response.ErrorCode = 7;
							response.ErrorMessage = 'Неизвестный код статуса картридера.';
							break;
					}
				}
				catch (e)
				{
					response.ErrorCode = 4;
					response.ErrorMessage = 'Произошла ошибка чтения карты.';
					log({'select reader':sw.Applets.readers[0],'all readers':sw.Applets.readers, 'error': e});
				}
			}
			else
			{
				response.ErrorCode = 3;
				response.ErrorMessage = 'Нет доступа к методам плагина. Попробуйте обновить страницу. В крайнем случае возможно прийдется обновить Java-plugin в браузере.';
			}
		}
		else
		{
			response.ErrorCode = 2;
			response.ErrorMessage = 'Не найден плагин для чтения данных УЭК.';
		}
		return response;
	},

	/**
	 * Чтение карты и проверка статуса
	 */
	getUecData: function (options, params) {
		// проверяем наличие плагина
		if ( this.applet )
		{
			this.uecData = this.getUecStatus();
			log(this.uecData);
			if (this.uecData.success)
			{
				// остановим чтение карты
				this.stopUecReader();
				// необходим ввод пин-кода
				getWnd('swPINCodeWindow').show({
					params: params,
					callback: function(data) {
						if (data && data.pin) 
						{
							var person = this.applet.readUec((sw.Applets.readers[0])?sw.Applets.readers[0]:'', data.pin);
							// todo: Если ошибка - открывать ввод ПИН кода по новой или запускать опрос картридера
							if (person.errorCode > 0) {
								// если произошла ошибка - снова открываем форму ввода PIN-кода и сообщаем об ошибке
								var p = {msg: person.errorMessage};
								this.getUecData(options, p);
								log(person.errorMessage);
							} else {
								var response = {};
								response.uecNum = person.uecNum;
								response.surName = person.surName;
								response.firName = person.firName;
								response.SecName = person.SecName;
								response.birthDay = person.birthDay;
								response.polisNum = person.polisNum;
								response.success = true;
								response.ErrorCode = null;
								response.ErrorMessage = null;
								if (!response.ErrorCode) {
									this.getPerson(response, options);
								}
							}
						} else {
							this.startUecReader();
						}
					}.bind(this),
					onHide: function() {
						this.startUecReader();
					}.bind(this)
				});
			}
		}
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
				Person_SecName: data.SecName,
				Person_BirthDay: data.birthDay,
				Polis_Num: data.polisNum
			},
			success: function(response, options) {
				var response_obj = Ext.util.JSON.decode(response.responseText);
				if (response_obj[0] && response_obj[0].Person_id) {
					log('Нашли персона в БД: '+response_obj[0].Person_id);
					// todo: функция которая должна выполняться this.callback
					if (options && options.callback) {
						options.callback(data, response_obj[0]);
					} else
						if (this.callback) {
							this.callback(data, response_obj[0]);
						}
				}
				//this.startUecReader(); // стартовать опрос лучше всего в каллбэке
			}.bind(this),
			url: '?c=Person&m=getPersonByUecData'
		});
	},

	/** Инициализация апплета, если еще не инициализирован и получение данных с анализатора
	 * В качестве параметра передается объект, в который будет включен апплет и выполнена инициализация апплета
	 */
	initUec: function (o, name) {
		name = (name)?name:'uecapplet';
		if (navigator.javaEnabled()) {
			if (!document[name]) {
				// Аплет получения данных с анализатора
				var uecapplet = Ext.getBody().createChild({
					name: name,
					tag: 'applet',
					archive:'applets/swan-smartcard-uec.jar',
					code:'ru/swan/smartcard/uec/UecApplet',
					width: 0,
					height: 0,
					id: 'java_Applets_'+name,
					style:'width:1px,height:1px'
				});
				this.init(name);
			}
			//loadMask.hide();
			return true;
		} else {
			setPromedInfo('Отсутствует java машина. Работа с картами будет недоступна.<br/>Для установки java машины зайдите на сайт <a href=http://java.com/ru>http://java.com/ru</a>', 'javamashine-info');
		}
		return false;
	}
}

// Чтение штрих-кода
sw.Applets.BarcodeScaner = {
	applet: null,
	port: '',

	callback: Ext.emptyFn,
	init: function(name) {
		name = (name ? name : 'barcodeapplet');
		this.applet = document[name];
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

		if ( !this.applet ) {
			return false;
		}

		if ( typeof this.applet.isOpenPort != 'function' ) {
			return false;
		}

		if ( this.barcodeIntervalObj ) {
			clearInterval(this.barcodeIntervalObj);
		}

		log('Включаем считывание штрих-кода');

		if ( options && typeof options.callback == 'function' ) {
			this.setCallback(options.callback);
		}
/*
		else {
			this.clearCallback();
		}
*/
		var barcodeInterval = ((options && options.barcodeInterval ? options.barcodeInterval : null) || (Ext.globalOptions.others.barcodereader_interval));

		this.barcodeIntervalObj = setInterval(this.openBarcodeScanerPort.bind(this), barcodeInterval);
		this.port = (Ext.globalOptions.others.barcodereader_port || '');

		log('Порт: ' + (this.port || '(не указан)'));

		if ( this.applet.isOpenPort() == false ) {
			this.applet.ReadCode(this.port);
		}
	},

	/**
	 *	Запуск прослушивания порта
	 */
	openBarcodeScanerPort: function(options) {
		if ( !this.applet ) {
			return false;
		}

		log('Попытка чтения штрих-кода...');

		if ( this.applet.getReadCodeOK() == true ) {
			log('Удачно!');

			var codeObject = this.applet.getCodeObject();
			var data = new Object();

			data.Person_Surname = codeObject.SurName();
			data.Person_Firname = codeObject.FirName();
			data.Person_Secname = codeObject.SecName();
			data.Person_Birthday = codeObject.BirthDate();
			data.Sex_Code = codeObject.Sex();
			data.Polis_Num = codeObject.PolisNumber();
			data.Polis_endDate = codeObject.ExpireDate();

			if (typeof (barcodeScannerLogging) === "function") {
				barcodeScannerLogging(data);
			}

			this.getPerson(data);
			this.port = (Ext.globalOptions.others.barcodereader_port || '');
			this.applet.ReadCode(this.port);
		}
	},

	stopBarcodeScaner: function() {
		if ( !this.applet ) {
			return false;
		}
		
		if ( typeof this.applet.isOpenPort != 'function' ) {
			return false;
		}		

		if ( this.barcodeIntervalObj ) {
			clearInterval(this.barcodeIntervalObj);
		}

		if ( this.applet.isOpenPort() == true ) {
			this.applet.ClosePort();
		}

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
						alert('Пациент был найден по коду ОМС');
					}
					if(response_obj[0].resultType==2){
						alert('Пациент не найден по коду ОМС. Был произведен поиск по ФИО и дате рождения');
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
					alert('Человек не найден в БД. Будет открыта форма Человек: Добавление');
					getWnd('swPersonEditWindow').show({
						action: 'add',
						fields: {
							'Person_SurName': data.Person_Surname,
							'Person_FirName': data.Person_Firname,
							'Person_SecName': data.Person_Secname,
							'Person_BirthDay': data.Person_Birthday,
							'Polis_Num': data.Polis_Num
						}
					});
				}
			}.bind(this),
			url: '?c=Person&m=getPersonByBarcodeData'
		});
	},

	/**
	 *	Получение списка COM-портов
	 */
	getPortList: function() {
		if ( !this.applet ) {
			return false;
		}

		log('Получение списка COM-портов...');

		var portList = false;

		try {if (typeof this.applet.getPortList == 'function'){
			portList = this.applet.getPortList();
		}else{
		    log('Порты не подключены');
		    return false;
		}
		}
		catch (e)
		{
			log('Ошибка получения списка портов');
			log(e);
		}

		var result = new Array();

		if ( portList && portList.toString().length > 0 ) {
			var i;
			var portArray = portList.toString().split(",");
			var rec;

			for ( i = 0; i < portArray.length; i++ ) {
				rec = new Array(portArray[i], portArray[i]);
				result.push(rec);
			}
		}

		return result;
	},

	/**
	 *	Инициализация апплета, если еще не инициализирован
	 *	В качестве параметра передается объект, в который будет включен апплет и выполнена инициализация апплета
	 */
	initBarcodeScaner: function (o, name) {
		if ( navigator.javaEnabled() ) {
			name = (name ? name : 'barcodeapplet');

			if ( !document[name] ) {
				// Апплет для чтения штрих-кода
				var barcodeapplet = Ext.getBody().createChild({
					name: name,
					tag: 'applet',
					archive: 'applets/ScanCode.jar',
					code: 'ru.swan.applet.ReadCodeApplet',
					width: 0,
					height: 0,
					id: 'java_Applets_' + name,
					style: 'width: 1px, height: 1px'
				});

				this.init(name);
			}

			return true;
		}
		else {
			setPromedInfo('Отсутствует java машина. Работа с картами будет недоступна.<br/>Для установки java машины зайдите на сайт <a href=http://java.com/ru>http://java.com/ru</a>', 'javamashine-info');
		}

		return false;
	}
}