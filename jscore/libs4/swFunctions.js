// если нэймспейса ещё нет, объявим.
if (typeof sw4 == 'undefined') {
	var sw4 = {};
}

/**
 * Инициализация глобальных сторе
 */
sw4.initGlobalStores = function() {
	// Создаем нужные stores
	sw4.swLpuFilialGlobalStore = Ext6.create ('Ext6.data.Store', {
		table: 'LpuFilial',
		autoLoad: false,
		proxy: {
			type: 'ajax',
			url: '/?c=Common&m=loadLpuFilialList',
			reader: {
				type: 'json'
			}
		},
		fields: [
			{name: 'LpuFilial_Code', mapping: 'LpuFilial_Code'},
			{name: 'LpuFilial_id', mapping: 'LpuFilial_id'},
			{name: 'LpuFilial_Name', mapping: 'LpuFilial_Name'},
			{name: 'LpuFilial_begDate', mapping: 'LpuFilial_begDate'},
			{name: 'LpuFilial_endDate', mapping: 'LpuFilial_endDate'},
			{name: 'Lpu_id', mapping: 'Lpu_id'},
			{name: 'Lpu_Name', mapping: 'Lpu_Name'},
			{name: 'sortID', mapping: 'sortID'}
		]
	});

	sw4.swLpuBuildingGlobalStore = Ext6.create ('Ext6.data.Store', {
		table: 'LpuBuilding',
		autoLoad: false,
		proxy: {
			type: 'ajax',
			url: '/?c=Common&m=loadLpuBuildingList',
			reader: {
				type: 'json'
			}
		},
		fields: [
			{name: 'LpuBuilding_Code', mapping: 'LpuBuilding_Code'},
			{name: 'LpuBuilding_id', mapping: 'LpuBuilding_id'},
			{name: 'LpuBuilding_Name', mapping: 'LpuBuilding_Name'},
			{name: 'Lpu_id', mapping: 'Lpu_id'},
			{name: 'Lpu_Name', mapping: 'Lpu_Name'},
			{name: 'LpuFilial_id', mapping: 'LpuFilial_id'},
			{name: 'LpuFilial_Name', mapping: 'LpuFilial_Name'},
			{name: 'sortID', mapping: 'sortID'}
		]
	});

	sw4.swLpuSectionGlobalStore = Ext6.create ('Ext6.data.Store', {
		table: 'LpuSection',
		autoLoad: false,
		proxy: {
			type: 'ajax',
			url: C_LPUSECTION_LIST,
			reader: {
				type: 'json'
			}
		},
		fields: [
			{name: 'LpuSection_Code', mapping: 'LpuSection_Code'},
			{name: 'LpuSection_id', mapping: 'LpuSection_id'},
			{name: 'LpuSection_pid', mapping: 'LpuSection_pid'},
			{name: 'LpuSectionAge_id', mapping: 'LpuSectionAge_id'},
			{name: 'LpuSection_Class', mapping: 'LpuSection_Class'},
			{name: 'LpuSection_Name', mapping: 'LpuSection_Name'},
			{name: 'LpuSectionProfile_id', mapping: 'LpuSectionProfile_id'},
			{name: 'LpuSectionProfile_Code', mapping: 'LpuSectionProfile_Code'},
			{name: 'LpuSectionProfile_Name', mapping: 'LpuSectionProfile_Name'},
			{name: 'LpuSectionProfile_SysNick', mapping: 'LpuSectionProfile_SysNick'},
			{name: 'Lpu_id', mapping: 'Lpu_id'},
			{name: 'Lpu_Name', mapping: 'Lpu_Name'},
			{name: 'LpuBuilding_id', mapping: 'LpuBuilding_id'},
			{name: 'LpuUnit_id', mapping: 'LpuUnit_id'},
			{name: 'LpuUnitSet_id', mapping: 'LpuUnitSet_id'},
			{name: 'LpuUnitSet_Code', mapping: 'LpuUnitSet_Code'},
			{name: 'LpuUnitType_id', mapping: 'LpuUnitType_id'},
			{name: 'LpuUnitType_Code', mapping: 'LpuUnitType_Code'},
			{name: 'LpuUnitType_SysNick', mapping: 'LpuUnitType_SysNick'},
			{name: 'LpuSection_disDate', mapping: 'LpuSection_disDate'},
			{name: 'LpuSection_setDate', mapping: 'LpuSection_setDate'},
			{name: 'LpuSection_IsHTMedicalCare', mapping: 'LpuSection_IsHTMedicalCare'},
			{name: 'LpuSectionServiceList', mapping: 'LpuSectionServiceList', type: 'string'},
			{name: 'sortID', mapping: 'sortID'}
		],
		url: C_LPUSECTION_LIST
	});

	sw4.swLpuSectionWardGlobalStore = Ext6.create ('Ext6.data.Store', {
		autoLoad: false,
		proxy: {
			type: 'ajax',
			url: C_LPUSECTIONWARD_LIST,
			reader: {
				type: 'json'
			}
		},
		fields: [
			{name: 'LpuSectionWard_id', mapping: 'LpuSectionWard_id'},
			{name: 'LpuSection_id', mapping: 'LpuSection_id'},
			{name: 'Lpu_id', mapping: 'Lpu_id'},
			{name: 'Lpu_Name', mapping: 'Lpu_Name'},
			{name: 'Sex_id', mapping: 'Sex_id'},
			{name: 'LpuSectionWard_Name', mapping: 'LpuSectionWard_Name'},
			{name: 'LpuSectionWard_disDate', mapping: 'LpuSectionWard_disDate'},
			{name: 'LpuSectionWard_setDate', mapping: 'LpuSectionWard_setDate'},
			{name: 'sortID', mapping: 'sortID'}
		]
	});

	sw4.swFederalKladrGlobalStore = Ext6.create ('Ext6.data.Store', {
		autoLoad: false,
		fields: [
			{name: 'KLAdr_Code', mapping: 'KLAdr_Code'},
			{name: 'KLArea_id', mapping: 'KLArea_id'}
		],
		url: C_KLADR_LIST
	});

	sw4.swLpuUnitGlobalStore = Ext6.create ('Ext6.data.Store', {
		autoLoad: false,
		proxy: {
			type: 'ajax',
			url: '/?c=Common&m=loadLpuUnitList',
			reader: {
				type: 'json'
			}
		},
		fields: [
			{name: 'LpuUnit_id', mapping: 'LpuUnit_id'},
			{name: 'Lpu_id', mapping: 'Lpu_id'},
			{name: 'Lpu_Name', mapping: 'Lpu_Name'},
			{name: 'LpuUnitType_id', mapping: 'LpuUnitType_id'},
			{name: 'LpuBuilding_id', mapping: 'LpuBuilding_id'},
			{name: 'LpuUnit_Code', mapping: 'LpuUnit_Code'},
			{name: 'LpuUnit_Name', mapping: 'LpuUnit_Name'},
			{name: 'LpuUnit_IsEnabled', mapping: 'LpuUnit_IsEnabled'},
			{name: 'sortID', mapping: 'sortID'}
		]
	});

	sw4.swMedServiceGlobalStore = Ext6.create ('Ext6.data.Store', {
		autoLoad: false,
		proxy: {
			type: 'ajax',
			url: C_MEDSERVICE_LIST,
			reader: {
				type: 'json'
			}
		},
		fields: [
			{name: 'MedService_id', mapping: 'MedService_id'},
			{name: 'Lpu_id', mapping: 'Lpu_id'},
			{name: 'Lpu_Name', mapping: 'Lpu_Name'},
			{name: 'LpuBuilding_id', mapping: 'LpuBuilding_id'},
			{name: 'LpuSection_id', mapping: 'LpuSection_id'},
			{name: 'LpuUnit_id', mapping: 'LpuUnit_id'},
			{name: 'LpuUnitType_id', mapping: 'LpuUnitType_id'},
			{name: 'MedService_begDT', mapping: 'MedService_begDT'},
			{name: 'MedService_endDT', mapping: 'MedService_endDT'},
			{name: 'MedService_Name', mapping: 'MedService_Name'},
			{name: 'MedService_Nick', mapping: 'MedService_Nick'},
			{name: 'MedServiceType_id', mapping: 'MedServiceType_id'},
			{name: 'sortID', mapping: 'sortID'}
		]
	});

	sw4.swResultDeseaseLeaveTypeGlobalStore = Ext6.create ('Ext6.data.Store', {
		autoLoad: false,
		proxy: {
			type: 'ajax',
			url: '/?c=Common&m=loadResultDeseaseLeaveTypeList',
			reader: {
				type: 'json'
			}
		},
		fields: [
			{name: 'ResultDeseaseLeaveType_id', mapping: 'ResultDeseaseLeaveType_id'},
			{name: 'ResultDeseaseType_id', mapping: 'ResultDeseaseType_id'},
			{name: 'LeaveType_id', mapping: 'LeaveType_id'}
		]
	});

	sw4.swMedStaffFactGlobalStore = Ext6.create ('Ext6.data.Store', {
		autoLoad: false,
		proxy: {
			type: 'ajax',
			url: C_MEDPERSONAL_LIST,
			reader: {
				type: 'json'
			}
		},
		fields: [
			{name: 'MedPersonal_DloCode', mapping: 'MedPersonal_DloCode'},
			{name: 'MedPersonal_Fio', mapping: 'MedPersonal_Fio'},
			{name: 'MedPersonal_Fin', mapping: 'MedPersonal_Fin'},
			{name: 'MedPersonal_SurName', convert: function(v, rec) {
					var result = '';
					if (rec.get('MedPersonal_Fio')) {
						var fio = rec.get('MedPersonal_Fio').split(' ');
						result = (fio[0]) ? Ext6.util.Format.capitalize(fio[0].toLowerCase()) : '';
					}
					
					return result;
				}},
			{name: 'MedPersonal_FirName', convert: function(v, rec) {
					var result = '';
					if (rec.get('MedPersonal_Fio')) {
						var fio = rec.get('MedPersonal_Fio').split(' ');
						result = (fio[1]) ? Ext6.util.Format.capitalize(fio[1].toLowerCase()) : '';
					}

					return result;
				}},
			{name: 'MedPersonal_SecName',  convert: function(v, rec) {
					var result = '';
					if (rec.get('MedPersonal_Fio')) {
						var fio = rec.get('MedPersonal_Fio').split(' ');
						result = (fio[2]) ? Ext6.util.Format.capitalize(fio[2].toLowerCase()) : '';
					}

					return result;
				}},
			{name: 'MedPersonal_id', mapping: 'MedPersonal_id'},
			{name: 'MedPersonal_TabCode', mapping: 'MedPersonal_TabCode'},
			{name: 'MedStaffFact_id', mapping: 'MedStaffFact_id'},
			{name: 'Lpu_id', mapping: 'Lpu_id'},
			{name: 'Lpu_Name', mapping: 'Lpu_Name'},
			{name: 'LpuBuilding_id', mapping: 'LpuBuilding_id'},
			{name: 'LpuUnit_id', mapping: 'LpuUnit_id'},
			{name: 'LpuUnitSet_id', mapping: 'LpuUnitSet_id'},
			{name: 'LpuSection_id', mapping: 'LpuSection_id'},
			{name: 'LpuSection_pid', mapping: 'LpuSection_pid'},
			{name: 'LpuSectionAge_id', mapping: 'LpuSectionAge_id'},
			{name: 'LpuSection_Name', mapping: 'LpuSection_Name'},
			{name: 'LpuSectionProfile_Code', mapping: 'LpuSectionProfile_Code'},
			{name: 'LpuSectionProfile_Name', mapping: 'LpuSectionProfile_Name'},
			{name: 'LpuSectionProfile_SysNick', mapping: 'LpuSectionProfile_SysNick'},
			{name: 'LpuUnitType_Code', mapping: 'LpuUnitType_Code'},
			{name: 'LpuUnitType_SysNick', mapping: 'LpuUnitType_SysNick'},
			{name: 'LpuSection_disDate', mapping: 'LpuSection_disDate'},
			{name: 'LpuSection_setDate', mapping: 'LpuSection_setDate'},
			{name: 'WorkData_begDate', mapping: 'WorkData_begDate'},
			{name: 'WorkData_endDate', mapping: 'WorkData_endDate'},
			{name: 'WorkData_dloBegDate', mapping: 'WorkData_dloBegDate'},
			{name: 'WorkData_dloEndDate', mapping: 'WorkData_dloEndDate'},
			{name: 'PostKind_id', mapping: 'PostKind_id'},
			{name: 'PostMed_Code', mapping: 'PostMed_Code'},
			{name: 'PostMed_Name', mapping: 'PostMed_Name'},
			{name: 'frmpEntry_id', mapping: 'frmpEntry_id'},
			{name: 'MedSpecOms_id', mapping: 'MedSpecOms_id'},
			{name: 'MedSpecOms_Code', mapping: 'MedSpecOms_Code'},
			{name: 'FedMedSpec_id', mapping: 'FedMedSpec_id'},
			{name: 'FedMedSpec_Code', mapping: 'FedMedSpec_Code'},
			{name: 'FedMedSpecParent_Code', mapping: 'FedMedSpecParent_Code'},
			{name: 'MedStaffFactCache_IsDisableInDoc', mapping: 'MedStaffFactCache_IsDisableInDoc'},
			{ name: 'MedStaffFact_Stavka', mapping: 'MedStaffFact_Stavka', type: 'string'},
			{name: 'LpuRegion_List', mapping: 'LpuRegion_List'},
			{name: 'LpuRegion_MainList', mapping: 'LpuRegion_MainList'},
			{name: 'LpuRegion_DatesList', mapping: 'LpuRegion_DatesList'},
			{name: 'MedStaffFactCache_IsHomeVisit', mapping: 'MedStaffFactCache_IsHomeVisit'},
			{name: 'sortID', mapping: 'sortID'}
		],
		url: C_MEDPERSONAL_LIST
	});

	sw4.globalStores = {
		'LpuBuilding': sw4.swLpuBuildingGlobalStore,
		'LpuSection': sw4.swLpuSectionGlobalStore,
		'LpuSectionWard': sw4.swLpuSectionWardGlobalStore,
		'FederalKladr': sw4.swFederalKladrGlobalStore,
		'LpuUnit': sw4.swLpuUnitGlobalStore,
		'MedService': sw4.swMedServiceGlobalStore,
		'MedStaffFact': sw4.swMedStaffFactGlobalStore,
		'ResultDeseaseLeaveType': sw4.swResultDeseaseLeaveTypeGlobalStore
	};
}

/**
 * Загрузка глобальных списков
 */
sw4.loadGlobalStores = function(result) {
	sw4.initGlobalStores();
	var stores = sw4.globalStores;
	for(i in stores) {
		if (!Ext6.isEmpty(result[i])){
			stores[i].loadData(result[i], false);
		}
	}
}

/**
 *  Получение записей из Store
 *  Входящие данные: store - хранилище данных
 *                   options - параметры получения
 *  На выходе: массив записей
 */
sw4.getStoreRecords = function(store, options) {
	var fields = new Array();
	var fields_types = new Array();//параллельный массив с типами полей, для избежания гадания типа поля на кофейной гуще (т.е. по названию поля)
	var result = new Array();

	if (store == undefined || store == null)
	{
		return result;
	}

	// Опции по умолчанию
	if (options == undefined || options == null)
	{
		var options = new Object();
		options.convertDateFields = false;
		options.dateFormat = 'd.m.Y';
		options.exceptionFields = new Array();
	}
	else
	{
		if (!options.convertDateFields) options.convertDateFields = false;
		if (!options.dateFormat) options.dateFormat = 'd.m.Y';
		if (!options.exceptionFields) options.exceptionFields = new Array();
	}

	store.getProxy().getModel().getFields().forEach(function(item) {
		var i;
		var key = item.name;
		var add = true;

		for (i = 0; i < options.exceptionFields.length; i++)
		{
			if (key.toLowerCase().indexOf(options.exceptionFields[i].toLowerCase()) != -1)
			{
				add = false;
				break;
			}
		}

		if (add === true)
		{
			fields.push(key);
			fields_types.push(item.type);
		}
	});

	store.each(function(record) {
		var i;
		var temp_record = new Object();

		for (i = 0; i < fields.length; i++)
		{
			var type = fields_types[i];
			if ((options.convertDateFields === true) && (fields_types[i].toLowerCase()=='date'))
			{
				temp_record[fields[i]] = Ext6.util.Format.date(record.data[fields[i]], options.dateFormat);
			}
			else
			{
				temp_record[fields[i]] = record.data[fields[i]];
			}
		}

		result.push(temp_record);
	});
	return result;
}

sw4.ruWordCase = function(case1, case2, case3, anInteger) {
	var result = case3;
	if ((anInteger < 5)||(20 < anInteger)) {
		var days = anInteger.toString();
		var lastSymbol =  days[days.length-1];
		switch (lastSymbol) {
			case '1':
				result = case1;
				break;
			case '2':
			case '3':
			case '4':
				result = case2;
				break;
			default:
				break;
		}
	}
	return result;
}

sw4.getRussianDayOfWeek = function(day) {
	var days = ['вс', 'пн', 'вт', 'ср', 'чт', 'пт', 'сб'];
	if (days[day]) {
		return days[day];
	} else {
		return '';
	}
}

/**
 * Сохранение в лог информации, о загрузке окон
 * @param object
 */
sw4.addToPerfLog = function(object) {
	if (typeof sw4.perfLog == 'undefined') {
		sw4.perfLog = [];
	}
	if (typeof sw4.perfStat == 'undefined') {
		sw4.perfStat = new Object();
	}

	object.time = Date.now().valueOf();
	if (object.window && !object.type.inlist(['request'])) {
		if (!object.type.inlist(['beforefetch', 'beforerender']) && sw4.perfStat[object.window]) {
			object.self = object.time - sw4.perfStat[object.window];
		}
		sw4.perfStat[object.window] = object.time;
	}
	sw4.perfLog.push(object);

	if (sw4.perfLog.length > 100) {
		// отправляем на сервер
		Ext6.Ajax.request({
			url: '/?c=PerfLog&m=savePerfLog',
			params: {
				perfLog: Ext6.JSON.encode(sw4.perfLog)
			},
			failure: function (response, options) {
				// not ok
			},
			success: function (response, options) {
				// ok
			}
		});

		// уже отправили на сервер, пусть наполняется заново
		sw4.perfLog = [];
	}
}

/**
 * Возвращает кнопку контекстной помощи, по названию раздела справки
 * @param {Ext6.Window} self ссылка на окно, к которому делается кнопка
 * @param {int} tbidx табиндекс для кнопки
 * @return {}
 */
sw4.getHelpButton = function(self, tbidx) {
	if (self != undefined)
	{
		var btnCfg = {
			text: BTN_FRMHELP,
			handler: function(button, event) {
				ShowHelp(self.title);
			}.createDelegate(self)
		};
		if (!Ext6.isEmpty(tbidx)) {
			btnCfg.tabIndex = tbidx;
		}

		return new Ext6.Button(btnCfg);
	}
}

/**
 * Загрузка справочников
 */
function loadDataLists(panel, components, callback) {
	if (components.length > 0) {
		panel.mask(LOADING_MSG);
		var needLoad = false;
		var o = {};
		for(var i in components) {
			if (components[i].getStore && components[i].getStore() && components[i].getStore().getCount() == 0 && components[i].getStore().mode == 'local') {
				var s = components[i].getStore();
				if (s.tableName) {
					var p = s.params || ((components[i].loadParams && components[i].loadParams.params) ? components[i].loadParams.params : null); // Стандартные параметры или параметры для чтения
					o[s.tableName] = {'url': s.url, 'baseparams': s.baseParams, 'params': p};
					needLoad = true;
				}
			}
		}

		if (needLoad) {
			Ext6.Ajax.request({
				url: '/?c=MongoDBWork&m=getDataAll',
				params: {'data': Ext6.JSON.encode(o)},
				failure: function (response, options) {
					panel.unmask();

					if (callback && typeof callback == 'function') {
						callback();
					}
				},
				success: function (response, options) {
					panel.unmask();
					var result = null;
					if (response && typeof response.responseText == 'string' && response.responseText.length > 0) {
						result = Ext6.JSON.decode(response.responseText);
					}
					if (result) {
						// Разбираем ответ по сторе
						for (var i in components) {
							if (components[i].getStore && components[i].getStore() && components[i].getStore().tableName && result[components[i].getStore().tableName]) {
								if (components[i].getStore().getCount() == 0) { // Если хранилище пустое, то заполняем его пришедшими данными
									components[i].getStore().loadData(result[components[i].getStore().tableName], false);

									// добавляем пустую строку
									if (typeof components[i].insertAdditionalRecords == 'function') {
										components[i].insertAdditionalRecords();
									}
								}
							}
						}
					}

					if (callback && typeof callback == 'function') {
						callback();
					}
				}
			});
		} else {
			panel.unmask();

			if (callback && typeof callback == 'function') {
				callback();
			}
		}
	} else {
		if (callback && typeof callback == 'function') {
			callback();
		}
	}
}

sw4.showInfoMsg = function(config) {
	var infoMsg = {},
		hideDelay = (config.hideDelay === null)? null : config.hideDelay || 6000, // мс до автоматического скрытия
		wrapNode = document.createElement('DIV'),
		iconNode = document.createElement('DIV'),
		mainNode = document.createElement('DIV'),
		btnNode = document.createElement('DIV'),
		bottom = config.bottom ? config.bottom : 10;

	if (!config.panel) {
		infoMsg.parentEl = Ext6.getBody();
	} else {
		infoMsg.parentEl = config.panel.body;
	}

	switch (config.type) {
		case 'warning':
			icon = '/img/icons/emk/panelicons/VarningAlertIcon.png';
			bgcolor = '#FFEA80';
			position = ' bottom: '+bottom+'px; right: 10px;';
			border = 'border: 1px solid #E6D167;';
			break;
		case 'info':
			icon = '/img/icons/emk/panelicons/sgnalAlertIcon.png';
			bgcolor = '#BBDEFB';
			position = ' bottom: '+bottom+'px; right: 10px;';
			border = 'border: 1px solid #90CAF9;';
			break;
		case 'error':
			icon = '/img/icons/emk/panelicons/errorAlertMsg.png';
			bgcolor = '#FFCDD2';
			position = ' bottom: '+bottom+'px; right: 10px;';
			border = 'border: 1px solid #EF9A9A; ';
			break;
		case 'success':
			icon = '/img/icons/emk/panelicons/sucsessAlertIcon.png';
			bgcolor = '#C8E6C9';
			position = ' bottom: '+bottom+'px; right: 10px;';
			border = 'border: 1px solid #81C784;';
			break;
		case 'loading':
			icon = '/img/icons/2017/preloader.gif';
			bgcolor = '#BBDEFB';
			position = ' bottom: '+bottom+'px; right: 10px;';
			border = 'border: 1px solid #90CAF9;';
			break;
		default:
			// do nothing
			return null;
	}
	wrapNode.setAttribute('style', 'position: fixed;'
		+ position
		+ border
		+ ' padding: 3px; margin: 10px;'
		+ ' border-radius: 5px;'
		+ ' background-color: ' + bgcolor + ';'
		+ ' min-width: 320px; max-width: 500px;'
		+ ' overflow: hidden; z-index: 100000;');
	wrapNode.classList.add('alert-msg-window');
	iconNode.setAttribute('style', 'background: url("' + icon + '") no-repeat center;'
		+ 'background-size: 16px;'
		+ 'opacity: 0.6;'
		+ ' width: 16px; height: 16px; float: left;'
		+ ' margin-top: 7px; margin-left: 10px; margin-right: 10px;'
	);
	mainNode.setAttribute('style', 'margin-top: 7px; margin-bottom: 7px; margin-right: 40px; margin-left: 40px;' +
		' text-align: left; font-size: 12px; font-family: Arial,sans-serif;');
	btnNode.setAttribute('style', 'position: absolute; top: 10px; right: 16px;'
		+ ' background-image: url("/img/icons/emk/panelicons/closeAlertMsg.png");'
		+ 'opacity: 0.6;'
		+ ' width: 16px; height: 16px; cursor: pointer;');
	mainNode.setAttribute('class','info-msg-popup');
	
	infoMsg.rendered = false;
	infoMsg.getEl = function() {
		return infoMsg.el;
	};
	infoMsg.getCloseBtnEl = function() {
		return infoMsg.closeBtnEl;
	};
	infoMsg.getParentEl = function() {
		return infoMsg.parentEl;
	};
	infoMsg.isRendered = function() {
		return infoMsg.rendered;
	};
	infoMsg.show = function() {
		if (!infoMsg.isRendered()) {
			infoMsg.render();
		}
		if (hideDelay > 0) {
			infoMsg.timeout_id = window.setTimeout(function(){
				if (infoMsg.timeout_id) {
					infoMsg.getEl().animate({
						duration: 500,
						to: {
							opacity: 0
						},
						listeners: {
							afteranimate: function() {
								infoMsg.hide();
							},
							scope: this
						}
					});
				}
			}, hideDelay);
		}
	};
	infoMsg.render = function() {
		infoMsg.el = new Ext6.Element(wrapNode);
		infoMsg.closeBtnEl = new Ext6.Element(btnNode);
		infoMsg.textEl = new Ext6.Element(mainNode);
		infoMsg.textEl.update(config.text);
		if (config.type == 'loading') {
			var loadingIconNode = document.createElement('DIV');
			iconLoadig = '<div class="vertical-centered-box">'+
				'<div class="content-loader-wrap">'+
				'<div class="loader-circle"></div>'+
				'<div class="loader-line-mask one">'+
				'<div class="loader-line"></div>'+
				'</div>'+
				'<div class="loader-line-mask two">'+
				'<div class="loader-line"></div>'+
				'</div>'+
				'<div class="loader-line-mask three">'+
				'</div>'+
				'<div class="loader-line-mask four">'+
				'</div>'+
				'</div>'+
				'</div>';
			loadingIconNode.setAttribute('style', 'background-size: 16px;'
				+ 'opacity: 0.6;'
				+ ' width: 16px; height: 16px; float: left;'
				+ ' margin-top: 7px; margin-left: 10px; margin-right: 10px;');
			loadingIconNode.innerHTML = iconLoadig;
			infoMsg.getEl().appendChild(new Ext6.Element(loadingIconNode));
		}else {
			infoMsg.getEl().appendChild(new Ext6.Element(iconNode));
		}
		infoMsg.getEl().appendChild(infoMsg.textEl);
		infoMsg.getEl().appendChild(infoMsg.closeBtnEl);
		infoMsg.getParentEl().appendChild(infoMsg.getEl());
		infoMsg.getCloseBtnEl().on('click', function() {
			infoMsg.hide();
		});
		infoMsg.rendered = true;
	};
	infoMsg.destroy = function() {
		if (infoMsg.isRendered()) {
			infoMsg.getEl().remove();
			infoMsg.rendered = false;
		}
	};
	infoMsg.hide = function() {
		window.clearTimeout(infoMsg.timeout_id);
		infoMsg.timeout_id = null;
		infoMsg.destroy();
		if(config.next) config.next();
	};

	infoMsg.show();

	return infoMsg;
};

/**
 * Заглушка
 */
function inDevelopmentAlert() {
	Ext6.Msg.alert(langs('В разработке'), langs('Функционал в разработке'));
}

/**
 * Вопрос об удалении записи
 */
function checkDeleteRecord(options, recordText) {
	if (!recordText) {
		recordText = 'запись';
	}
	Ext6.Msg.show({
		title: 'Вопрос',
		msg: 'Вы действительно хотите удалить ' + recordText + '?',
		buttons: Ext6.Msg.YESNO,
		icon: Ext6.Msg.QUESTION,
		fn: function(btn) {
			if (btn === 'yes') {
				options.callback();
			}
		}
	});
}
/**
*  Получение временного id для store
*  Входящие данные: store - хранилище
*  На выходе: число
*/
sw4.GenTempId = function (store)
{
	var record;
	var result = 0;

	var collection = store.data?store.data:store;
	if (!(collection instanceof Ext6.util.MixedCollection)) {
		return result;
	}

	while (record || result == 0)
	{
		result = Math.floor(Math.random() * 1000000);
		record = collection.key(result);
	}

	return result;
}
/**
*  Проверяет видимость формы, не создавая ее
*/
function isVisibleWnd(wnd) {		
	//~ var winRef = Ext6.ComponentQuery.query('window[refId='+wnd+']')[0];
	//~ var winAlias = Ext6.ComponentQuery.query('window[alias='+wnd+']')[0];
	if (WINDOWS_ALIAS.hasOwnProperty(wnd)) {
		var win = Ext6.ComponentQuery.query('window[$className='+WINDOWS_ALIAS[wnd]+']')[0];
		if( win && win.isVisible() ) return true;
	}
	return false;
}

/** Вычисление возраста человека на дату
 *  @birth_date - дата рождения
 *  @date - дата, на которую вычисляется возраст
 */
sw4.GetPersonAge = function (birth_date, date) {
	
	if ( !birth_date || typeof birth_date != 'object' ) 
		return -1;
	if ( !date || typeof date != 'object' ) {
		date = getValidDT(getGlobalOptions().date, '') || new Date();
	}
	if ( birth_date > date )  {
		return -1;
	}
	var person_age = (birth_date.getMonthsBetween(date) - (birth_date.getMonthsBetween(date) % 12)) / 12;
	return person_age;
}

function getXmlTemplateSharedUnreadCount() {
	var button = Ext6.getCmp('_shared_templates');
	if (!button) return;

	Ext6.Ajax.request({
		url: '/?c=XmlTemplate6E&m=getXmlTemplateSharedUnreadCount',
		callback: function(options, success, response) {
			var responseObj = Ext6.decode(response.responseText);

			if (responseObj.success) {
				if (responseObj.count == 0) {
					button.hide();
					button.setText('');
				} else {
					button.show();
					button.setText('<span class="notice-widget-button">'+(responseObj.count>99?99:responseObj.count)+'</span>');
				}
			}
		}
	});
}

/**
*  Функция для запускалки
*/
function taskRunExt6()
{
	if(!sw.Promed.GlobalVariables.statusInactivity) {
		getNewMessagesExt6();
	}
}

/**
 * Проверяем наличие новых(непрочитанных) сообщений
 */
function getNewMessagesExt6() {
	Ext6.Ajax.request({
		url: '/?c=Messages&m=getNewMessagesExt6',
		success: function(resp, opts)
		{
			var obj = Ext6.util.JSON.decode(resp.responseText);
			if (obj) 
			{
				var reclen = (obj.data && obj.data.length)?obj.data.length:0;
				
				if (obj.totalCount) {
					//~ if(sw.notices.length==0)
						main_notice_widget.EvnJournalList.setUnreadCount(obj.totalCount);
					//~ else
						//~ main_notice_widget.EvnJournalList.setUnreadCount(0);
				}
				
				if (reclen > 0 && getNoticeOptions().is_popup_message)
				{
					// Заполняем стек сообщений
					for (i = 0; i < reclen; i++)
					{
						sw.notices.push(obj.data[i]);
					}
					showNoticeExt6();
				}
				else {
					return false;
				}
			}
		}
	});
}

/**
 * Функция в ссылке "результат" всплывающего сообщения
 * открывает соответствующие окна результатов выполнения услуг
 */
function openNoticeResultExt6(evnClassNick, Evn_id, Person_id) {
	var obj = '', 
		obj_id = '';
	switch(evnClassNick) {
		case 'EvnUslugaTelemed':
			obj = 'EvnUslugaTelemed';
			obj_id = 'EvnUslugaTelemed_id';
			break;
		case 'EvnUslugaPar':
		case 'EvnUslugaParPolka':
			obj = 'EvnUslugaPar';
			obj_id = 'EvnUslugaPar_id';
			break;
	}
	
	if(obj_id) getWnd('uslugaResultWindow').show({
		Evn_id: Evn_id, 
		object: obj,
		object_id: obj_id
	});
}

/**
 *   Вывод всплывающего сообщения из кеша
 */
function showNoticeExt6() {
	// берем первую запись из стека
	if (sw.notices.length>0) {
		var notice = sw.notices[0];
		
		var evn = '';
		if(notice['Message_Subject'].search('Параклиническая услуга') >-1 ) evn='Параклиническая услуга (выполнено)';
		if(notice['Message_Subject'].search('Телемедицинская услуга') >-1 ) evn='Телемедицинская услуга (выполнено)';
		notice['EvnText'] = evn;
		if(evn) {
			tpl = new Ext6.XTemplate('<div>Пациент: <b>{Person_SurName} {Person_FirName} {Person_SecName}</b><div><div>{EvnText}:<br>',
				'<a href="javascript:openNoticeResultExt6(\'{EvnClass_SysNick}\',{Evn_id},0);">Результат</a></div>');
			tpl.apply(notice);
			sw4.showInfoMsg({
				type: 'info',//возможно, нужно добавить еще тип mail и подобрать значок
				text: tpl.apply(notice),
				next: showNoticeExt6,
				//~ hideDelay: null
			});
		}
		sw.notices.splice(0,1);
	}
}

/**
 * Возвращает форматированный вывод возраста
 * Точный вывод возрастов < 5 лет
 * Для старших выдает только число лет, без буквы (г./л.) /задается опцией useYearLetterForElder
 * Принимает либо запись из Store, либо иной объект, 
 * содержащий поля Person_BirthDay и Person_DeadDT в виде строки 
 * (по умолчанию в формате YYYY-MM-DD)
 */
function getAgeString(data) {
	var date1 = null,
		date2 = null;
	if(typeof data != 'object') return '';
	var DateFormat = 'Y-m-d';
	if('DateFormat' in data) {
		DateFormat = data.DateFormat;
	}
	if('get' in data) {
		date1 = Date.parseDate(data.get('Person_BirthDay'),DateFormat);
		date2 = Date.parseDate(data.get('Person_DeadDT'),DateFormat);
	} else if('Person_BirthDay' in data) {
		date1 = Date.parseDate(data.Person_BirthDay,DateFormat);
		date2 = Date.parseDate(data.Person_DeadDT,DateFormat);
	} else return '';
	if(!date2) {
		date2 = Date.now();
	}
	if(date1>date2) return '';
	
	var temp = new Ext6.XTemplate(' {letter}');
	if('Template' in data) {
		temp.set(data.Template);
	}
	var useYearLetterForElder=false;
	if('useYearLetterForElder' in data) {
		useYearLetterForElder=data.useYearLetterForElder;
	}
	
	var	y = date2.getYear()-date1.getYear(),
		m = date2.getMonth()-date1.getMonth(),
		d = date2.getDate()-date1.getDate();
	if(m<0) {
		m = 12+m;
		y--;
	}
	if(d<0) {
		d = date1.getDaysInMonth()+d;
		m--;
	}

	if(y==0) {
		if(m==0) return d+temp.apply({letter:'д'});
		else return m+temp.apply({letter:'м'})+(d>0 ? ' '+d+temp.apply({letter:'д'}) : '');
	} else {
		if(y<5)
			return y+temp.apply({letter:'г'})+(m>0 ? ' '+m+temp.apply({letter:'м'}) :
								(d>0 && y==1 ? ' '+d+temp.apply({letter:'д'}) : '')
							);
		else
			return y+(useYearLetterForElder ? 
				(y%10<5 && !(y>10 && y<15) ? temp.apply({letter:'г'}) : temp.apply({letter:'л'}))			
			:'');
	}
}

function getAgeStringY(data) {
	if(typeof data != 'object') return '';
	var data2 = Object.assign({useYearLetterForElder:true}, data);
	var s = getAgeString(data2);
	//~ var r = s.match(/(\d)$/);
	//~ if (r) s+=Number(s[1])<5 ? ' г.' : ' л.';
	return s;
}
/**
 * На выходе все первые буквы слов в строке в верхнем регистре, остальные в нижнем
 */
function toUpperCaseFirstLetter(str) {
	if(str && Ext6.isEmpty(str))
		return '';
	return str.split(/\s+/).map(function(word) {
		return word[0].toUpperCase() + word.substring(1).toLowerCase()
	}).join(' ')
}

/**
 * Формирует "Фамилия И.О." из "ФАМИЛИЯ ИО" или "ФАМИЛИЯ ИМЯ ОТЧЕСТВО"
 */
function CaseLettersPersonFio(str) {
	if(Ext6.isEmpty(str))
		return '';

	var words = str.split(/\s+/).reverse(),
		fio = '',
		word = '';

	if(words.length>0) {
		fio += toUpperCaseFirstLetter(words.pop());
		word = words.pop();
		if(words.length==0 && !Ext6.isEmpty(word) && word.length<3) {//значит остались инициалы в word
			words = word.split('');
		} else if(!Ext6.isEmpty(word)) {
			words.push(word);
			words.reverse();
		}
		if(!Ext6.isEmpty(words)) fio += ' '+words.map(function(x){return x.replace('.','').slice(0,1).toUpperCase()+'.'}).join('');
	}
	return fio;
}

/**
 * Проверка записи о показе сообщения пользователю
 *
 * @param {String} ARMType - Открытый АРМ
 * @param {String} ARMName - Название открытого АРМ-а
 * @return {void}
 */
function checkShownMsgArms(ARMType,ARMName) {
	if(!ARMType)
		ARMType = getGlobalOptions().curARMType;

	if(sw.Promed.MedStaffFactByUser && sw.Promed.MedStaffFactByUser.current){
		if(!ARMType && sw.Promed.MedStaffFactByUser.current.ARMType)
			ARMType = sw.Promed.MedStaffFactByUser.current.ARMType;
		if(!ARMName && ARMType){
			var s = sw.Promed.MedStaffFactByUser.store;
			var index = s.findBy( function(rec){ return (rec.get('ARMType') === ARMType);});
			if (index >= 0)
				var record = s.getAt(index);
			if(record) ARMName = record.get('ARMName');
		}
	}
	Ext6.Ajax.request({
		url: '/?c=User&m=checkShownMsgArms',
		params: {
			curARMType: ARMType
		},
		failure: function (response, options) {
			// а жаль
		},
		success: function (response, options) {
			var obj = Ext6.util.JSON.decode(response.responseText);
			if (obj && obj.success && obj.showMsg)
				Ext6.defer(function(){
					sw.swMsg.alert('', 'Новый интерфейс ЭМК '+(ARMName?('и '+ARMName):'')+' находится в стадии доработки. Некоторые функции могут быть недоступны.');
				},1000);

		}
	});
}

/**
 * Получение xtype для объекта
 */
function getXTypeForTestId(object, depth) {
	switch (object.xtype) {
		case 'tab':
			return 'tab';
		case 'tool':
			return 'tl';
		case 'panel':
			return 'pnl';
		case 'menuitem':
			return 'mi';
		case 'toolbar':
			return 'tbr';
		case 'button':
			return 'btn';
		case 'grid':
			return 'grd';
		case 'window':
			return 'win';
		case 'combobox':
			return 'cbx';
		case 'field':
			return 'fld';
		default:
			if (depth < 5 && object.superclass) {
				depth++;
				return getXTypeForTestId(object.superclass, depth);
			} else {
				return false;
			}
	}
}

function EvnPLDispRefuse(Person_id, DispClass_id, MedStaffFact_id, callback) {//отказ от диспансеризации (есть в журнале, в панели эмк, в блоке эмк)

	sw.swMsg.show({
		buttons: Ext6.Msg.YESCANCEL,
		buttonText: { yes: 'Подтвердить', cancel: 'Отмена' },
		msg: 'Подтвердить отказ от '+(DispClass_id==5 ? 'профосмотра' : 'диспансеризации')+'?',
		title: langs('Вопрос'),
		icon: Ext.MessageBox.QUESTION,
		fn: function(buttonId){
			if (buttonId === 'yes') {
				if(Person_id && DispClass_id && DispClass_id>0 && MedStaffFact_id) {
					Ext6.Ajax.request({
						url: '/?c=EvnPLDisp&m=Refuse',
						params: {
							Person_id: Person_id,
							DispClass_id: DispClass_id,
							MedStaffFact_id: MedStaffFact_id
						},
						failure: function (response, options) {
							Ext6.Msg.show({
								title: 'Ошибка',
								buttons: Ext6.Msg.OK,
								msg: 'Произошла ошибка при обращении к серверу',
								icon: Ext6.Msg.ERROR
							});
						},
						success: function (response, options) {
							var obj = Ext6.util.JSON.decode(response.responseText);
							if(DispClass_id!=5 && obj && obj.success && obj.data && obj.data.DispRefuse_id)
								printBirt({
									'Report_FileName': 'DispRefuse.rptdesign',
									'Report_Params': '&paramDispRefuse='+obj.data.DispRefuse_id+'&paramDispClass=' + DispClass_id,
									'Report_Format': 'pdf'
								});
							
							if (obj && obj.success && obj.data && typeof callback == 'function')
								callback(obj.data);
						}
					});
				}
			}
		}.createDelegate(this)
	});
}

function createDirection(prescrParams, callback){
	log('_createDirection', prescrParams);

	var fdata = prescrParams.fdata;

	var direction = {
		LpuUnitType_SysNick: 'parka'
		,PrehospDirect_id: (getRegionNick() == 'kz')
			? (prescrParams.Lpu_id == getGlobalOptions().lpu_id) ? 15 : 16
			: (prescrParams.Lpu_id == getGlobalOptions().lpu_id) ? 1 : 2
		,PrescriptionType_Code: prescrParams.PrescriptionType_Code
		,EvnDirection_pid: fdata.Evn_id
		,Evn_id: fdata.Evn_id
		,EvnDirection_IsCito: (prescrParams.EvnPrescr_IsCito == '2')?2:1
		,DirType_id: sw.Promed.Direction.defineDirTypeByPrescrType(prescrParams.PrescriptionType_Code)
		,Diag_id: fdata.Diag_id || null
		,MedPersonal_id: fdata.userMedStaffFact.MedPersonal_id //ид медперсонала, который направляет
		,Lpu_id: fdata.userMedStaffFact.Lpu_id
		,Lpu_sid: fdata.userMedStaffFact.Lpu_id
		,LpuSection_id: fdata.userMedStaffFact.LpuSection_id
		,From_MedStaffFact_id: fdata.userMedStaffFact.MedStaffFact_id
		,UslugaComplex_id: prescrParams.UslugaComplex_id
		,LpuSection_Name: prescrParams.LpuSection_Name
		,LpuSection_did: prescrParams.LpuSection_id
		,LpuSection_uid: prescrParams.LpuSection_id
		,LpuSectionProfile_id: prescrParams.LpuSectionProfile_id
		,EvnPrescr_id: null
		,Resource_id: prescrParams.Resource_id
		,Resource_did: prescrParams.Resource_id
		,Resource_Name: prescrParams.Resource_Name
		//,MedService_id: prescrParams.MedService_id
		,MedService_id: prescrParams.MedService_id || (prescrParams.ttms_MedService_id?prescrParams.ttms_MedService_id:prescrParams.pzm_MedService_id)
		//,MedService_id: (prescrParams.MedService_id?prescrParams.MedService_id:(prescrParams.ttms_MedService_id?prescrParams.ttms_MedService_id:prescrParams.pzm_MedService_id))
		,MedService_did: prescrParams.MedService_id
		,MedService_Nick: prescrParams.MedService_Nick
		,MedServiceType_SysNick: prescrParams.MedServiceType_SysNick
		,Lpu_did: prescrParams.Lpu_id
		,LpuUnit_did: prescrParams.LpuUnit_id
		,time: (prescrParams.withResource?prescrParams.TimetableResource_begTime:prescrParams.TimetableMedService_begTime)||null
		,Server_id: fdata.Server_id
		,Person_id: fdata.Person_id
		,PersonEvn_id: fdata.PersonEvn_id
		,MedStaffFact_id: fdata.userMedStaffFact.MedStaffFact_id //ид медперсонала, который направляет
		,MedPersonal_did: null //ид медперсонала, куда направили
		,timetable: 'TimetablePar'
		,TimetableMedService_id: prescrParams.TimetableMedService_id
		,TimetableResource_id: prescrParams.TimetableResource_id
		,withResource: prescrParams.withResource
		,EvnQueue_id: null//
		,QueueFailCause_id: null//
		,EvnUsluga_id: null//Сохраненный заказ
		,EvnDirection_id: null
	};
	// параметры для формы выписки эл.направления
	var form_params = direction;
	form_params.Person_Surname = prescrParams.Person_Surname;
	form_params.Person_Firname = prescrParams.Person_Firname;
	form_params.Person_Secname = prescrParams.Person_Secname;
	form_params.Person_Birthday = prescrParams.Person_Birthday;
	var params = {
		action: 'add',
		mode: 'nosave',
		callback: function(data){
			if (data && data.evnDirectionData) {
				var o = data.evnDirectionData;
				//принимаем только то, что могло измениться
				direction.EvnDirection_Num = o.EvnDirection_Num;
				direction.DirType_id = o.DirType_id;
				direction.Diag_id = o.Diag_id;
				direction.LpuSectionProfile_id = o.LpuSectionProfile_id;
				direction.EvnDirection_Descr = o.EvnDirection_Descr;
				direction.EvnDirection_setDate = o.EvnDirection_setDate;
				direction.MedStaffFact_id = o.MedStaffFact_id;
				direction.MedPersonal_id = o.MedPersonal_id;
				direction.LpuSection_id = o.LpuSection_id;
				direction.MedStaffFact_zid = o.MedStaffFact_zid;
				direction.MedPersonal_zid = o.MedPersonal_zid;
				direction.Lpu_did = o.Lpu_did;
				direction.From_MedStaffFact_id = o.From_MedStaffFact_id;
				direction.EvnXml_id = o.EvnXml_id;
				direction.EvnDirection_desDT = o.EvnDirection_desDT;
				direction.EvnDirectionOper_IsAgree = o.EvnDirectionOper_IsAgree;
				direction.PayType_id = o.PayType_id;
				callback(direction);
			}
		},
		params: form_params
	};

	if (!prescrParams.MedService_id && !prescrParams.Lpu_id) {
		// будем сохранять только назначение
		direction.Lpu_did = null;
		callback(direction);
		return true;
	}
	if (!prescrParams.MedService_id && prescrParams.Lpu_id) {
		sw.Promed.Direction.openDirectionEditWindow(params);
		return true;
	}

	if (direction.PrescriptionType_Code == '7') {
		// для назначений в оперблок нужна форма направления,
		// т.к. нужна "отметка о согласии пациента/представителя пациента" и "предоперационный эпикриз"
		sw.Promed.Direction.openDirectionEditWindow(params);
	} else if (getGlobalOptions().lpu_id == prescrParams.Lpu_id) {
		//возвращаем параметры автоматического направления
		direction.EvnDirection_IsAuto = 2;
		direction.EvnDirection_setDate = getGlobalOptions().date;
		direction.EvnDirection_Num = '0';
		direction.MedPersonal_zid = '0';
		callback(direction);
	} else {
		// иначе создаем обычное направление, без дополнительных форм
		direction.EvnDirection_IsAuto = 1;
		direction.EvnDirection_setDate = getGlobalOptions().date;
		direction.EvnDirection_Num = '0';
		direction.MedPersonal_zid = '0';
		callback(direction);
	}
	return true;
}

function checkRecommendDose(options) {
	if (getRegionNick() == 'kz') {return false}
	
	var record = options.record;

	if (!Ext6.isEmpty(record.ActMatters_id) || !Ext6.isEmpty(record.RlsActmatters_id)) {

		var params = {
			Person_Age: options.Person_Age,
			Diag_id: options.Diag_id,
			ActMatter_id: record.ActMatters_id || record.RlsActmatters_id
		};

		Ext6.Ajax.request({
			params: params,
			url: '/?c=CureStandart&m=loadRecommendedDoseForDrug',
			callback: function(opt, scs, response) {

				var response_obj = Ext.util.JSON.decode(response.responseText);
				if (!Ext.isEmpty(response_obj)) {

					var DoseDay = response_obj.data.CureStandartTreatmentDrug_ODD;
					var DayUnit = response_obj.data.ODDDoseUnit_Name || '';
					var DoseKurs = response_obj.data.CureStandartTreatmentDrug_EKD;
					var KursUnit = response_obj.data.EKDDoseUnit_Name || '';

					if (DoseDay != '' || DoseKurs != '' && typeof params.callback == 'function') {
						options.callback({DoseDay:DoseDay, DayUnit:DayUnit, DoseKurs:DoseKurs, KursUnit:KursUnit});
					}
				}
			}
		});
	}
}