/**
 * swRegFunctions. Общие функции для части электронной регистратуры
 * @package      Libs
 * @access       public
 * @copyright    Copyright © 2009 Swan Ltd.
 * @version      22.05.2013
 */
 
 /**
 * Отправка запроса на освобождение записи на время
 */
function submitClearTime(param, success_callback, fail_callback) {
	var params = new Object({
		LpuUnitType_SysNick: param.type,
		DirFailType_id: param.DirFailType_id,
		EvnStatusCause_id: param.EvnStatusCause_id,
		EvnComment_Comment: param.EvnComment_Comment,
		Person_id: param.person_id || '',
		TimetableGrafRecList_id: param.TimetableGrafRecList_id || ''
	});

	if (param.cancelType) {
		params['cancelType'] = param.cancelType;
	}

	if (param.type == 'polka') {
		var url = C_TTG_CLEAR;
		params['TimetableGraf_id'] = param.id;
	}
	if (param.type == 'stac') {
		var url = C_TTS_CLEAR;
		params['TimetableStac_id'] = param.id;
	}
	
	if (param.type == 'medservice') {
		var url = C_TTMS_CLEAR;
		params['TimetableMedService_id'] = param.id;
	}
	if (param.type == 'medserviceorg') {
		var url = C_TTMSO_CLEAR;
		params['TimetableMedServiceOrg_id'] = param.id;
	}
	if (param.type == 'resource') {
		var url = C_TTR_CLEAR;
		params['TimetableResource_id'] = param.id;
	}
	
	Ext.Ajax.request({
		url: url,
		params: params,
		failure: function(response, options)
		{
			Ext.Msg.alert(langs('Ошибка'), langs('При выполнении операции освобождения <br/>времени приема произошла ошибка!'));
			if (fail_callback != undefined) {
				fail_callback(response);
			}
		},
		success: function(response, action)
		{
			if (success_callback != undefined) {
				success_callback(response);
			}
		}
	});
}

/**
 * Отправка запроса на удаление бирки в поликлинике/параклинике/стационаре
 */
function submitDeleteTime(params, success_callback, fail_callback, controller) {
	Ext.Ajax.request({
		url: controller,
		params: params,
		failure: function(response, options)
		{
			Ext.Msg.alert(langs('Ошибка'), langs('При выполнении операции удаления <br/>бирки произошла ошибка!'));
			if (fail_callback != undefined) {
				fail_callback(response);
			}
		},
		success: function(response, action)
		{
			if (success_callback != undefined) {
				success_callback(response);
			}
		}
	});
}

/**
 * Отправка запроса на очистку дня в поликлинике/параклинике/стационаре
 */
function submitClearDay(params, success_callback, fail_callback) {
	
	params['Day'] = params.day;
	params['LpuUnitType_SysNick'] = params.type;
		
	if (params.type == 'resource') {
		var url = C_TTR_CLEARDAY;
	} else if (params.type == 'stac') {
		var url = C_TTS_CLEARDAY;
	} else if (params.type == 'medservice') {
		var url = C_TTMS_CLEARDAY;
	} else if (params.type == 'medservicedlo') {
		var url = C_TTMSO_CLEARDAY;
	} else {
		var url = C_TTG_CLEARDAY;
	}
	
	Ext.Ajax.request({
		url: url,
		params: params,
		failure: function(response, options)
		{
			Ext.Msg.alert(langs('Ошибка'), langs('При выполнении операции удаления <br/>бирки произошла ошибка!'));
			if (fail_callback != undefined) {
				fail_callback(response);
			}
		},
		success: function(response, action)
		{
			if (success_callback != undefined) {
				success_callback(response);
			}
		}
	});
}

/**
 * Смена типа бирки
 */
function submitChangeTTType(params, success_callback, fail_callback, controller)
{
	Ext.Ajax.request({
		url: controller,
		params: params,
		failure: function(response, options)
		{
			Ext.Msg.alert(langs('Ошибка'), langs('При выполнении операции изменения типа<br/>бирки произошла ошибка!'));
			if (fail_callback != undefined) {
				fail_callback(response);
			}
		},
		success: function(response, action)
		{
			if (success_callback != undefined) {
				success_callback(response);
			}
		}
	});
}
