/**
* swMorbusOnkoWindow - окно простого заболевания.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      MorbusOnko
* @access       public
* @copyright    Copyright (c) 2009-2013 Swan Ltd.
* @version      06.2013
* @prefix       MOVW
*/

sw.Promed.swMorbusOnkoWindow = Ext.extend(sw.Promed.BaseForm, 
{
	width : 400,
	height : 400,
	modal: true,
	resizable: false,
	autoHeight: false,
	closeAction :'hide',
	id: 'MorbusOnkoWindow',
	autoScroll: true,
	border : false,
	plain : false,
	action: null,
	maximized: true,
	title: langs('Запись регистра'),
	listeners: {
		'hide': function(win) {
			var conf;
			for(var field_name in win.changed_fields) {
				conf = win.changed_fields[field_name];
				if(!conf.remove){
					conf.elOutput.setDisplayed('inline');
					conf.elOutput.update(conf.outputValue);
					if(conf.type == 'id') conf.elOutput.setAttribute('dataid',conf.value);
					conf.elInputWrap.setDisplayed('none');
					conf.elInput.destroy();
					win.input_cmp_list[conf.elOutputId] = false;
				}
			}
			win.onHide(win.isChange);
		},
		'beforeShow': function(win) {
			/*if(!getWnd('swWorkPlaceMZSpecWindow').isVisible())
			{
				if (String(getGlobalOptions().groups).indexOf('OnkoRegistry', 0) < 0)
				{
					sw.swMsg.alert('Сообщение', 'Форма "'+ win.title +'" доступна только для пользователей, с указанной группой «Регистр по онкологии»»');
					return false;
				}
			}*/
		}
	},
	checkAccessEdit: function(disable_msg) {
		return true;
		/*
		if (String(getGlobalOptions().groups).indexOf('OnkoRegistry', 0) >= 0)
		{
			return true;
		}
		else
		{
			if(!disable_msg) sw.swMsg.alert(langs('Сообщение'), langs('Функция редактирования доступна только для пользователей, с указанной группой "Регистр по онкологии"'));
			return false;
		}
		*/
	},
	/**
	 * Открывает соответсвующую акшену форму 
	 * 
	 * @param {string} open_form Название открываемой формы, такое же как название объекта формы
     * @param {string} id Наименование идентификатора таблицы, передаваемого в форму
     * @param {object} oparams
     * @param {string} mode
     * @param {string} title
     * @param {function} callback
     */
	openForm: function (open_form, id, oparams, mode, title, callback)
	{
		// Проверка
		if (getWnd(open_form).isVisible())
		{
			sw.swMsg.alert(langs('Сообщение'), langs('Форма ')+ ((title)?title:open_form) +langs(' в данный момент открыта.'));
			return false;
		}
		else
		{
			if (!mode)
				mode = 'edit';
			if(mode == 'edit' && this.checkAccessEdit() == false) {
				return false;
			}
			var params = {
				action: mode,
				Person_id: this.Person_id,
				/*
				PersonEvn_id: this.PersonEvn_id,
				Server_id: this.Server_id,
				Person_Firname: this.findById('PEMK_PersonInfoFrame').getFieldValue('Person_Firname'),
				Person_Surname: this.findById('PEMK_PersonInfoFrame').getFieldValue('Person_Surname'),
				Person_Secname: this.findById('PEMK_PersonInfoFrame').getFieldValue('Person_Secname'),
				Person_Birthday: this.findById('PEMK_PersonInfoFrame').getFieldValue('Person_Birthday'),
				onHide: function() { if(callback){callback();} else {this.reloadTree();} }.createDelegate(this),
				*/
				UserMedStaffFact_id: this.userMedStaffFact.MedStaffFact_id,
				UserLpuSection_id: this.userMedStaffFact.LpuSection_id,
				userMedStaffFact: this.userMedStaffFact,
				from: this.id,
				ARMType: this.userMedStaffFact.ARMType
			};
			params = Ext.apply(params || {}, oparams || {});
			/*
			if (IS_DEBUG)
			{
				console.debug('openForm | Форма %s с параметрами: %o', open_form, params);
			}
			*/
			 if(open_form == 'swPersonCardHistoryWindow')
                params.action = (this.editType=='onlyRegister')?'view':'edit';
            if(open_form == 'swPersonEditWindow')
                params.readOnly = (this.editType=='onlyRegister')?true:false;
			getWnd(open_form).show(params);
		}
	},
	deleteEvent: function(event, data) {
		if(this.allowSpecificEdit == false) {
			return false;
		}
		if(this.checkAccessEdit() == false) {
			return false;
		}
		if ( !event.inlist(['MorbusOnkoBasePersonState','MorbusOnkoBasePS','MorbusOnkoSpecTreat','MorbusOnkoRefusal','MorbusOnkoRadTer','MorbusOnkoHirTer','MorbusOnkoChemTer','MorbusOnkoGormTer','MorbusOnkoNonSpecTer','MorbusOnkoSopDiag','MorbusOnkoDrug','OnkoConsult','MorbusOnkoLink']) )
		{
			return false;
		}

		if ( event.inlist(['MorbusOnkoBasePersonState','MorbusOnkoBasePS','MorbusOnkoSpecTreat','MorbusOnkoRefusal','MorbusOnkoRadTer','MorbusOnkoHirTer','MorbusOnkoChemTer','MorbusOnkoGormTer','MorbusOnkoNonSpecTer','MorbusOnkoSopDiag','MorbusOnkoDrug','OnkoConsult','MorbusOnkoLink']) )
		{
			data.object_id = data.object_id.split('_')[1];
		}

		var formParams = this.rightPanel.getObjectData(data.object,data.object_id);

		var error = '';
		var question = '';
		var params = {};
		var url = '';
		var onSuccess;

		switch ( event ) {	
			case 'MorbusOnkoBasePersonState':
			case 'MorbusOnkoBasePS':
			case 'MorbusOnkoSpecTreat':
			case 'MorbusOnkoLink':
			case 'MorbusOnkoRefusal':
			case 'MorbusOnkoRadTer':
			case 'MorbusOnkoHirTer':
			case 'MorbusOnkoChemTer':
			case 'MorbusOnkoDrug':
			case 'MorbusOnkoGormTer':
			case 'MorbusOnkoNonSpecTer':
			case 'MorbusOnkoSopDiag':
				//case 'OnkoConsult':
				error = langs('При удалении возникли ошибки');
				question = langs('Удалить');
				onSuccess = function () {
					var reload_params = {
						section_code: data.object,
						object_key: data.object + '_id',
						object_value: data.object_id,
						parent_object_key: 'Morbus_id',
						parent_object_value: formParams.Morbus_id,
						param_name: 'MorbusOnko_pid',
						param_value: formParams.MorbusOnko_pid || null,
						MorbusOnkoVizitPLDop_id: formParams.MorbusOnkoVizitPLDop_id || null,
						MorbusOnkoLeave_id: formParams.MorbusOnkoLeave_id || null,
						MorbusOnkoDiagPLStom_id: formParams.MorbusOnkoDiagPLStom_id || null,
						Person_id: formParams.Person_id || 0,
						section_id: data.object + 'List_' + formParams.MorbusOnko_pid + '_' + formParams.Morbus_id
					};
					this.rightPanel.reloadViewForm(reload_params);
				}.createDelegate(this);
				url = '/?c=Utils&m=ObjectRecordDelete';
				params['obj_isEvn'] = 'false';
				switch (data.object) {
					case 'MorbusOnkoBasePersonState':
						params['MorbusOnkoBasePersonState_id'] = data.object_id;
						url = '/?c=MorbusOnkoBasePersonState&m=destroy';
						break;
					case 'MorbusOnkoSopDiag':
						url = '/?c=MorbusOnkoSpecifics&m=deleteMorbusOnkoSopDiag';
						params['object'] = 'MorbusOnkoSopDiag';
						break;
					case 'MorbusOnkoDrug':
						url = '/?c=MorbusOnkoDrug&m=destroy';
						params['MorbusOnkoDrug_id'] = data.object_id;
						break;
					case 'MorbusOnkoBasePS': params['object'] = 'MorbusOnkoBasePS'; break;
					case 'MorbusOnkoSpecTreat': params['object'] = 'MorbusOnkoSpecTreat'; break;
					case 'MorbusOnkoLink': params['object'] = 'MorbusOnkoLink'; break;
					case 'MorbusOnkoRefusal': params['object'] = 'MorbusOnkoRefusal'; break;
					case 'MorbusOnkoRadTer': params['object'] = 'EvnUslugaOnkoBeam'; params['obj_isEvn'] = 'true'; break;
					case 'MorbusOnkoHirTer': params['object'] = 'EvnUslugaOnkoSurg'; params['obj_isEvn'] = 'true'; break;
					case 'MorbusOnkoChemTer': params['object'] = 'EvnUslugaOnkoChem'; params['obj_isEvn'] = 'true'; break;
					case 'MorbusOnkoGormTer': params['object'] = 'EvnUslugaOnkoGormun'; params['obj_isEvn'] = 'true'; break;
					case 'MorbusOnkoNonSpecTer': params['object'] = 'EvnUslugaOnkoNonSpec'; params['obj_isEvn'] = 'true'; break;
					case 'OnkoConsult': params['object'] = 'OnkoConsult'; break;
				}
				params['id'] = data.object_id;
				break;
			case 'OnkoConsult':
				error = langs('При удалении возникли ошибки');
				question = langs('Удалить');
				onSuccess = function () {
					var reload_params = {
						section_code: data.object,
						object_key: data.object + '_id',
						object_value: data.object_id,
						parent_object_key: 'Morbus_id',
						parent_object_value: formParams.Morbus_id,
						param_name: 'MorbusOnko_pid',
						param_value: formParams.MorbusOnko_pid || null,
						MorbusOnkoVizitPLDop_id: formParams.MorbusOnkoVizitPLDop_id || null,
						MorbusOnkoLeave_id: formParams.MorbusOnkoLeave_id || null,
						MorbusOnkoDiagPLStom_id: formParams.MorbusOnkoDiagPLStom_id || null,
						Person_id: formParams.Person_id || 0,
						section_id: data.object + 'List_' + formParams.MorbusOnko_pid + '_' + formParams.Morbus_id
					};
					this.rightPanel.reloadViewForm(reload_params);
				}.createDelegate(this);
				url = '/?c=OnkoConsult&m=delete';
				params['obj_isEvn'] = 'false';
				params['object'] = 'OnkoConsult';
				params['OnkoConsult_id'] = data.object_id;
				break;
		}

		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId, text, obj) {
				if ( buttonId == 'yes' ) {
					var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Удаление записи..."});
					loadMask.show();
					Ext.Ajax.request({
						failure: function(response, options) {
							loadMask.hide();
							sw.swMsg.alert(langs('Ошибка'), error);
						},
						params: params,
						success: function(response, options) {
							//console.log("response=",response);
							loadMask.hide();
							var response_obj = Ext.util.JSON.decode(response.responseText);
							if ( response_obj.success == false ) {
								sw.swMsg.alert(langs('Ошибка'), response_obj.Error_Msg ? response_obj.Error_Msg : error);
							} else {
								onSuccess({});
							}
						}.createDelegate(this),
						url: url
					});
				}
			}.createDelegate(this),
			icon: Ext.MessageBox.QUESTION,
			msg: question,
			title: langs('Вопрос')
		});

        return true;
        
	},
	submitMorbusOnkoHtmlForm: function(btn_name, el_data) {
		var form = this;
		if(this.allowSpecificEdit == false) {
			return false;
		}
		var save_tb1 = Ext.get('MorbusOnko_'+el_data.object_id+'_toolbarDiag');
		var params = this.rightPanel.getObjectData('MorbusOnko',el_data.object_id.split('_')[1]);
		var do_check_palliat = false;
		if(!params) {
			return false;
		}
		for(var field_name in this.changed_fields) {
			params[field_name] = this.changed_fields[field_name].value || '';
            if ('OnkoTreatment_id' == field_name) {
                params['OnkoTreatment_Code'] = this.changed_fields[field_name].elInput.getFieldValue('OnkoTreatment_Code');
            }
            if ('MorbusOnkoBase_deadDT' == field_name && !params[field_name]) {
                params['Diag_did'] = '';
                params['AutopsyPerformType_id'] = '';
                params['TumorAutopsyResultType_id'] = '';
            }
			if ('HistologicReasonType_id' == field_name && !params[field_name] ) {
				params['MorbusOnko_histDT'] = '';
			}
            if ('AutopsyPerformType_id' == field_name) {
                if (params[field_name] == 3) {
                    params['TumorAutopsyResultType_id'] = 8;
                } else {
                    params['TumorAutopsyResultType_id'] = '';
                }
            }
            if(field_name.indexOf('OnkoDiagConfType') === 0){
				if(!this.changed_fields[field_name].remove){
					var el = document.getElementById(this.changed_fields[field_name].elOutputId);
					if(el){
						el.setAttribute('dataid', this.changed_fields[field_name].value);
					}
				}
				var id = this.changed_fields[field_name].elOutputId;
				ids = id.split('_');
				var val_str = '';
				var count = 0;
				var n = '';
				for(var i=1;i<8;i++){
					if(i>1){
						n = i;
					}
					var el = document.getElementById('MorbusOnko_'+ids[1]+'_'+ids[2]+'_inputOnkoDiagConfType'+n);
					if(el){
						var val = el.getAttribute('dataid');
						if(val){
							count = i;
							val_str += val;
							val_str += ',';
						}
					}
				}
				val_str = val_str.substr(0, (val_str.length-1));
				params['OnkoDiagConfTypes'] = val_str;
			}
		}

		if (
			getRegionNick() == 'msk' &&
			!!params.TumorStage_id &&
			params.TumorStage_id.inlist([13, 14, 15, 16])
		) {
			do_check_palliat = true;
		}

		if ( !params.MorbusOnko_histDT && params.HistologicReasonType_id ) {
			sw.swMsg.alert('Ошибка', 'Поле "Дата регистрации отказа / противопоказания" обязательно для заполнения');
			return false;
		}

		if ( !Ext.isEmpty(params.Diag_id) ) {
			var fieldsList = {
				'OnkoTreatment': 'Повод обращения',
				'OnkoM': 'Стадия опухолевого процесса по системе TNM (M)',
				'OnkoN': 'Стадия опухолевого процесса по системе TNM (N)',
				'OnkoT': 'Стадия опухолевого процесса по системе TNM (T)',
				'TumorStage': 'Стадия опухолевого процесса'
			}, allowBlank, linkStore, withDiagAndSpr, withoutDiag, withoutSpr,
			filterDate = Date.parseDate(!Ext.isEmpty(params['Evn_disDate']) ? params['Evn_disDate'] : getGlobalOptions().date, 'd.m.Y');

			if(getRegionNick() == 'perm'){
				if ( Ext.isEmpty(params['MorbusOnko_setDiagDT']) ) {
					sw.swMsg.alert('Ошибка', 'Поле Дата установления диагноза обязательно для заполнения');
					return false;
				}
			}

			for ( var field in fieldsList ) {
				if ( !Ext.isEmpty(params[field + (field.inlist(['OnkoM','OnkoN','OnkoT','TumorStage']) ? '_fid' : '_id')]) ) {
					continue;
				}

				withDiagAndSpr = new Array();
				withoutDiag = new Array();
				withoutSpr = new Array();

				linkStore = this[field + 'LinkStore'];

				if (linkStore) {
					linkStore.each(function(rec) {
						// добавить фильтр по params['filterDate']
						if (
							(Ext.isEmpty(rec.get(field + 'Link_begDate')) || rec.get(field + 'Link_begDate') <= filterDate)
							&& (Ext.isEmpty(rec.get(field + 'Link_endDate')) || rec.get(field + 'Link_endDate') >= filterDate)
						) {
							if (!Ext.isEmpty(rec.get('Diag_id')) && rec.get('Diag_id') == params['Diag_id']) {
								if (!Ext.isEmpty(rec.get(field + '_fid'))) {
									withDiagAndSpr.push(rec.get(field + 'Link_id'));
								} else {
									withoutSpr.push(rec.get('Diag_id'));
								}
							} else if (Ext.isEmpty(rec.get('Diag_id')) && !Ext.isEmpty(rec.get(field + '_fid'))) {
								withoutDiag.push(rec.get(field + 'Link_id'));
							}
						}
					});

					if ( field == 'TumorStage' ) {
						allowBlank = !(getRegionNick() != 'kz' && !Ext.isEmpty(params.OnkoTreatment_id) && params.OnkoTreatment_Code != 5 && params.OnkoTreatment_Code != 6);
					}
					else {
						allowBlank = !(getRegionNick() != 'kz' && (withDiagAndSpr.length > 0 || (withoutDiag.length > 0 && withoutSpr.length == 0)) && params.Person_Age >= 18 && params.OnkoTreatment_Code === 0);
					}
				} else {
					allowBlank = getRegionNick() == 'kz';
				}

				if ( allowBlank === false ) {
					sw.swMsg.alert('Ошибка', 'Поле ' + fieldsList[field] + ' обязательно для заполнения');
					return false;
				}
						
				if ( field.inlist(['OnkoM','OnkoN','OnkoT','TumorStage']) && Ext.isEmpty(params[field + '_id']) ) {
					sw.swMsg.alert('Ошибка', 'Поле ' + fieldsList[field] + ' обязательно для заполнения');
					return false;
				}
			}
		}
		
		var docheckEvnNotify = false;
		if (getRegionNick().inlist(['perm', 'msk']) && this.changed_fields['OnkoStatusYearEndType_id']) {
			var OnkoStatusYearEndType_idOld = this.changed_fields['OnkoStatusYearEndType_id'].elOutput.getAttribute('dataid');
			var OnkoStatusYearEndType_id = this.changed_fields['OnkoStatusYearEndType_id'].elInput.getValue();
			if (
				!!OnkoStatusYearEndType_idOld && OnkoStatusYearEndType_idOld.inlist([1,6,7]) &&
				!!OnkoStatusYearEndType_id && OnkoStatusYearEndType_id.inlist([2,3,4,5])
			) {
				docheckEvnNotify = true;
			}
		}

		if (getRegionNick() == 'perm' && !(params['TumorStage_id'] && params['TumorStage_id'] >= 9 && params['TumorStage_id'] <= 16)) {
            params['OnkoLateDiagCause_id'] = '';
        }

		if (do_check_palliat) {
			checkPalliatRegistry({
				Person_id: this.Person_id,
				Diag_id: params.Diag_id
			});
		}

		params['EvnDiagPLStomSop_id'] = this.EvnDiagPLStomSop_id || null;
		params['EvnDiagPLSop_id'] = this.EvnDiagPLSop_id || null;
		params['Evn_pid'] = this.EvnDiagPLStom_id || this.EvnVizitPL_id || this.EvnVizitDispDop_id || this.EvnSection_id || null;
		params['Mode'] = 'personregister_viewform';
		var url = '/?c=MorbusOnkoSpecifics&m=saveMorbusSpecific';
		form.loadMask = form.getLoadMask(LOAD_WAIT);
		form.loadMask.show();
		Ext.Ajax.request({
			url: url,
			params: params,
			callback: function(options, success, response) {
				form.loadMask.hide();
				var result = Ext.util.JSON.decode(response.responseText);
				if (result.success)
				{
					save_tb1.setDisplayed('none');
					var conf;
					for(var field_name in form.changed_fields) {
						conf = form.changed_fields[field_name];
						if(!conf.remove){
							conf.elOutput.setDisplayed('inline');
							conf.elOutput.update(conf.outputValue);
							if(conf.type == 'id') conf.elOutput.setAttribute('dataid',conf.value);
							conf.elInputWrap.setDisplayed('none');
							conf.elInput.destroy();
							form.input_cmp_list[conf.elOutputId] = false;
						}
					}
					if (getRegionNick() == 'perm' && !params['OnkoLateDiagCause_id']) {
			            var id = conf.elOutputId;
						ids = id.split('_');
						var el = document.getElementById('MorbusOnko_'+ids[1]+'_'+ids[2]+'_inputOnkoLateDiagCause');
						if(el){
							el.setAttribute('dataid', '');
							el.innerHTML = '<span class="empty">Не указано</span>';
						}
			        }
					form.changed_fields = {};
					form.isChange = true;
					
					if (docheckEvnNotify) {
						Ext.Ajax.request({
							url: '/?c=MorbusOnkoSpecifics&m=checkRegister',
							params: {
								Person_id: form.Person_id,
								Diag_id: params.Diag_id
							},
							success: function(response) {
								var data = JSON.parse(response.responseText);
								if (data.canInclude != 2) {
									return false;
								}
								
								checkEvnNotify({
									Evn_id:  params.MorbusOnko_pid
									,EvnDiagPLSop_id: params.EvnDiagPLSop_id || null
									,MorbusType_SysNick: 'onko'
									,callback: function(success) {
										var notifyForm = Ext.query('[id*="MorbusOnkoEvnNotify"]')[0];
										var MorbusOnko_pid = notifyForm.id.split('_')[1];
										var MorbusOnkoEvnNotify_id = notifyForm.id.split('_')[2];
										var params = {
											section_code: 'MorbusOnkoEvnNotify',
											section_id: notifyForm.id,
											object_key: 'MorbusOnkoEvnNotify_id',
											object_value: MorbusOnkoEvnNotify_id,
											parent_object_key: 'Morbus_id',
											parent_object_value: MorbusOnkoEvnNotify_id,
											param_name: 'MorbusOnko_pid',
											param_value: MorbusOnko_pid,
										};
										form.rightPanel.reloadViewForm(params);
									}
								});
							}
						});
					}
				}
			}
		});
	},
    checkDiagAttribPanel: function(name, el_data) {
		var vals = [];
		var n = '';
		for(var i=1;i<8;i++){
			if(i>1){
				n = i;
			}
			var el = document.getElementById('MorbusOnko_'+el_data.object_id+'_inputOnkoDiagConfType'+n);
			if(el){
				var val = el.getAttribute('dataid');
				if(val) vals.push(val);
			}
		}
		var diagAttribPanel = Ext.get('MorbusOnko_'+el_data.object_id+'_diagAttribPanel');
		if ((1).inlist(vals) || ((2).inlist(vals) && getRegionNick() == 'perm')) {
			diagAttribPanel.setDisplayed('block');
		} else {
			diagAttribPanel.setDisplayed('none');
		}
	},
	createMorbusOnkoHtmlForm: function(name, el_data) {
		if(this.allowSpecificEdit == false) {
			return false;
		}
		var morbus_id = el_data.object_id.split('_')[1];
		var params = this.rightPanel.getObjectData('MorbusOnko',morbus_id);
		if(typeof params != 'object') {
			return false;
		}
		var form = this;
		var cmp, ct, elinputid, eloutputid, config;
        var empty_value = '<span class="empty">Не указано</span>';

		if(!this.input_cmp_list) this.input_cmp_list = {};

		eloutputid = 'MorbusOnko_'+ el_data.object_id +'_input'+name;
		elinputid = 'MorbusOnko_'+ el_data.object_id +'_inputarea'+name;
		eloutput = Ext.get(eloutputid);
		ct = Ext.get(elinputid);
		
		var checkDate = Date.parseDate(!Ext.isEmpty(params.Evn_disDate) ? params.Evn_disDate : getGlobalOptions().date, 'd.m.Y');
		var OnkoTreatment_Code = form.changed_fields['OnkoTreatment_id'] ? form.changed_fields['OnkoTreatment_id'].elInput.getFieldValue('OnkoTreatment_Code') : params.OnkoTreatment_Code;

		var setValueTo = function (name, option) {
            if (!name) {
                return false;
            }
            if (!option) {
                option = {};
            }
            var field_name = name +'_id';
            if (option.field_name) {
                field_name = option.field_name;
            }
            if (!option.type) {
                option.type = 'id';
            }
            if (!option.value) {
                option.value = null;
            }
            if (!option.outputValue) {
                option.outputValue = empty_value;
            }
            params[field_name] = option.value;
            var record = form.rightPanel.viewFormDataStore.getById('MorbusOnko_'+ morbus_id);
            var conf_changed_field = form.changed_fields[field_name];
            var elinputid = 'MorbusOnko_'+ el_data.object_id +'_input'+ name;
            if (record) {
                record.set(field_name, option.value);
                if (option.type == 'id' && record.get(field_name+'_Name')) {
                    record.set(field_name+'_Name', option.outputValue);
                }
                record.commit(true);
                form.rightPanel.viewFormDataStore.commitChanges();
            }
            if (conf_changed_field) {
                conf_changed_field.value = option.value;
                conf_changed_field.outputValue = option.outputValue;
            }
            if (form.input_cmp_list[elinputid]) {
                form.input_cmp_list[elinputid].setValue(option.value);
            } else {
                var el = Ext.get(elinputid);
                if (el) {
                    if (option.type == 'id') {
                        el.set({data_id: (option.value||'')});
                        el.set({dataid: (option.value||'')});
                    }
                    el.update(option.outputValue);
                }
            }
            return true;
        };

		var onChange = function(conf){
			var save_tb1 = Ext.get('MorbusOnko_'+el_data.object_id+'_toolbarDiag');
			save_tb1.setDisplayed('block');
			if(!this.changed_fields) this.changed_fields = {};
			if(conf.name == 'EndDiag'){
				conf.field_name = 'Diag_id';
			}
			this.changed_fields[conf.field_name] = conf;

			var id = conf.elOutputId;
			ids = id.split('_');

            if (conf.name == 'MorbusOnkoBaseDeadDT' && !conf.value) {
                //в зависимых полях автоматически должно очистится значение
                setValueTo('DiagDead', {field_name: 'Diag_did'});
                setValueTo('AutopsyPerformType');
                setValueTo('TumorAutopsyResultType');
            }
			if ('HistologicReasonType' == conf.name) {
				if ( !conf.value ) {
					setValueTo('MorbusOnkoHistDT', {value: null, outputValue: empty_value, field_name: 'MorbusOnko_histDT'});
					Ext.get('MorbusOnko_' + ids[1] + '_contMorbusOnkoHistDT').setDisplayed('none');
				}
				else {
					Ext.get('MorbusOnko_' + ids[1] + '_contMorbusOnkoHistDT').setDisplayed('block');
				}
			}
            if (conf.name == 'AutopsyPerformType') {
                if (conf.value == 3) {
                    //в поле  «Результат аутопсии применительно к данной опухоли» автоматически должно заполняться значение «неизвестно»,
                    setValueTo('TumorAutopsyResultType', {value: 8, outputValue: langs('(неизвестно)')});
                } else {
                    //в поле «Результат аутопсии применительно к данной опухоли» автоматически должно очистится значение
                    setValueTo('TumorAutopsyResultType');
                }
            }
            if(conf.name == 'MorbusOnko_setDiagDT'){
				setValueTo('OnkoDiag', {value: null, outputValue: empty_value, field_name: 'OnkoDiag_mid'});
			}
			var isMorpho = false;
            if(conf.name.indexOf('OnkoDiagConfType') === 0){
            	if(!conf.remove){
            		var conf_val = conf.value;
            	} else {
            		var conf_val = null;
            	}

				if ( conf.value == 1 ) {
					isMorpho = true;
				}

            	//обновляем атрибут в dom-элементе
				var el = document.getElementById(conf.elOutputId);
				if(el){
					el.setAttribute('dataid', conf_val);
				}

				var val_str = '';
				var count = 0;
				var n = '';
				var arr = [];

				// собираем все значения Методов подтверждения на форме
				for(var i=1;i<8;i++){
					if(i>1){
						n = i;
					}
					var el = document.getElementById('MorbusOnko_'+ids[1]+'_'+ids[2]+'_inputOnkoDiagConfType'+n);
					if(el){
						var val = el.getAttribute('dataid');
						if(val){
							arr.push(val);
						}
					}
				}

				// для каждого комбика если он существует обновляем Store, 
				// исключая значения присутствующие на форме кроме значения комбика если оно задано
				for(var i=1;i<8;i++){
					if(i>1){
						n = i;
					}
					var local_arr = arr;
					var el = document.getElementById('MorbusOnko_'+ids[1]+'_'+ids[2]+'_inputareaOnkoDiagConfType'+n);
					var cmp = Ext.get('MorbusOnko_'+ids[1]+'_'+ids[2]+'_inputareaOnkoDiagConfType'+n);
					if(el && cmp && cmp.store){
						var val = el.getAttribute('dataid');
						var str = "";
						if(val && local_arr.length>0 && local_arr.indexOf(val) != -1){
							var index = arr.indexOf(val);
							local_arr.splice(index, 1);
							str = local_arr.join(',');
						}
						cmp.getStore().load({
							params: {where:" where OnkoDiagConfType_id not in ("+str+")"}
						});
					}
				}
			
				//form.checkDiagAttribPanel(name, el_data);

			}

			// собираем все значения Методов подтверждения на форме
			for ( var i = 1; i < 8; i++ ) {
				var el = document.getElementById('MorbusOnko_' + ids[1] + '_' + ids[2] + '_inputOnkoDiagConfType' + (i > 1 ? i : ''));

				if ( el ) {
					var val = el.getAttribute('dataid');

					if ( val && val == 1 ) {
						isMorpho = true;
					}
				}
			}

			var HistologicReasonType_id;

			if ( conf.name == 'HistologicReasonType' ) {
				HistologicReasonType_id = conf.value;
			}
			else if ( this.changed_fields['HistologicReasonType_id'] ) {
				HistologicReasonType_id = this.changed_fields['HistologicReasonType_id'].elInput.getValue();
			}
			else {
				HistologicReasonType_id = params.HistologicReasonType_id;
			}

			if ( getRegionNick() != 'kz' ) {
				Ext.get('MorbusOnko_' + ids[1] + '_histDT_allowBlank').setDisplayed(getRegionNick() != 'kz' && HistologicReasonType_id ? 'inline' : 'none');
			}
		}.createDelegate(this);
		
		var onCancel = function(conf){
			if(!this.changed_fields) this.changed_fields = {};
			if(!this.changed_fields[conf.field_name]) {
				conf.elOutput.setDisplayed('inline');
				conf.elInputWrap.setDisplayed('none');
				conf.elInput.destroy();
				this.input_cmp_list[conf.elOutputId] = false;
			}
		}.createDelegate(this);
		
		var getBaseConfig = function(options){
			var selectFn = function () {return true;};

			if ( options.type == 'id' ) {
				selectFn = function(f, r, i) {
					var n = null;

					if ( typeof r == 'object' && !Ext.isEmpty(r.get(f.valueField)) ) {
						n = r.get(f.valueField);
					}

					f.fireEvent('change', f, n);
				}
			}

			return {
				hideLabel: true
				,renderTo: options.elInputId
				,listeners:
				{
					blur: function(f) {
						options.elInput = f;
						onCancel(options);
					},
					render: function(f) {
						if(options.type == 'id') {
							//if(!f.getStore() || f.getStore().getCount()==0) log('not store: ' + options.field_name);
							var dataid = options.elOutput.getAttribute('dataid');
							if(!Ext.isEmpty(dataid)) {
								f.setValue(parseInt(dataid));
							}
						} else {
							f.setValue(params[options.field_name]);
						}
					},
					change: function(f,n,o) {
						if(options.type == 'date') {
							options.outputValue = (n)?n.format('d.m.Y'):empty_value;
							options.value = (n)?n.format('d.m.Y'):null;
						}
						if(options.type.inlist(['string','int'])) {
							options.outputValue = (n)?n:empty_value;
							options.value = n || null;
						}
						if(options.type == 'id') {
							var rec = (n)?f.getStore().getById(n):false;
							if(rec) {
								options.value = n;
								if (options.field_name.inlist(['OnkoT_fid', 'OnkoN_fid', 'OnkoM_fid', 'TumorStage_fid'])) {
									options.outputValue = '<span style="color: #c00;">' + rec.get(options.codeField) + '.</span> ' + rec.get(f.displayField);
								} else if(options.codeField) {
									options.outputValue = rec.get(options.codeField) + '. ' + rec.get(f.displayField);
								} else {
									options.outputValue = rec.get(f.displayField);
								}
							} else {
								options.value = 0;
								options.outputValue = empty_value;
							}
						}
						options.elInput = f;
						onChange(options);
					},
					select: selectFn
				}
			};
		};
		
		var
			field,
			fieldsList = new Array(),
			linkStore,
			linkStoreWithDiagAndSpr = {
				'OnkoM': new Array(),
				'OnkoN': new Array(),
				'OnkoT': new Array(),
				'TumorStage': new Array()
			},
			linkStoreWithoutDiag = {
				'OnkoM': new Array(),
				'OnkoN': new Array(),
				'OnkoT': new Array(),
				'TumorStage': new Array()
			},
			linkStoreWithoutSpr = {
				'OnkoM': new Array(),
				'OnkoN': new Array(),
				'OnkoT': new Array(),
				'TumorStage': new Array()
			},
			record = form.rightPanel.viewFormDataStore.getById('MorbusOnko_'+ morbus_id);

		if ( name.inlist([ 'OnkoMF', 'OnkoNF', 'OnkoTF', 'TumorStageF' ]) ) {
			fieldsList.push(name.slice(0, -1));
		}
		else if ( name == 'OnkoTreatment' ) {
			fieldsList = [ 'OnkoM', 'OnkoN', 'OnkoT' ];
		}

		if ( fieldsList.length > 0 && !Ext.isEmpty(params['Diag_id']) ) {
			var filterDate = Date.parseDate(!Ext.isEmpty(params['Evn_disDate']) ? params['Evn_disDate'] : getGlobalOptions().date, 'd.m.Y');

			for ( var i in fieldsList ) {
				field = fieldsList[i];

				if ( typeof field != 'string' ) {
					continue;
				}

				linkStore = form[field + 'LinkStore'];

				if ( !linkStore ) {
					continue;
				}

				linkStore.each(function(rec) {
					// добавить фильтр по params['Evn_disDate']
					if (
						(Ext.isEmpty(rec.get(field + 'Link_begDate')) || rec.get(field + 'Link_begDate') <= filterDate)
						&& (Ext.isEmpty(rec.get(field + 'Link_endDate')) || rec.get(field + 'Link_endDate') >= filterDate)
					) {
						if ( !Ext.isEmpty(rec.get('Diag_id')) && rec.get('Diag_id') == params['Diag_id'] ) {
							if ( !Ext.isEmpty(rec.get(field + '_fid')) ) {
								linkStoreWithDiagAndSpr[field].push(rec.get(field + 'Link_id'));
							}
							else {
								linkStoreWithoutSpr[field].push(rec.get('Diag_id'));
							}
						}
						else if ( Ext.isEmpty(rec.get('Diag_id')) && !Ext.isEmpty(rec.get(field + '_fid')) ) {
							linkStoreWithoutDiag[field].push(rec.get(field + 'Link_id'));
						}
					}
				});
			}
		}

		switch (name){
       		case 'firstSignDT'://дата появления первых признаков заболевания
			case 'firstVizitDT'://дата первого обращения
			case 'setDiagDT'://Дата установления диагноза
            case 'MorbusBaseSetDT'://дата взятия на учет в ОД
            case 'MorbusBaseDisDT'://дата снятия с учета в ОД
            case 'MorbusOnkoBaseDeadDT'://дата смерти
            case 'MorbusOnkoHistDT'://дата регистрации отказа / противопоказания
				if(ct && !this.input_cmp_list[eloutputid]) {
                    var field_name;
                    switch (name) {
                        case 'MorbusBaseSetDT'://дата
                            field_name = 'MorbusBase_setDT';
                            break;
                        case 'MorbusBaseDisDT'://дата
                            field_name = 'MorbusBase_disDT';
                            break;
                        case 'MorbusOnkoBaseDeadDT'://дата
                            field_name = 'MorbusOnkoBase_deadDT';
                            break;
                        case 'MorbusOnkoHistDT'://дата
                            field_name = 'MorbusOnko_histDT';
                            break;
                        default: field_name = 'MorbusOnko_'+ name;
                            break;
                    }
					ct.setDisplayed('block');
					eloutput.setDisplayed('none');
					config = getBaseConfig({
						name: name
						,type: 'date'
						,field_name: field_name
						,elOutputId: eloutputid
						,elInputId: elinputid
						,elOutput: eloutput
						,outputValue: null
						,elInputWrap: ct
						,elInput: null
					});
					config.maxValue = getGlobalOptions().date;
					config.width = 90;
					cmp = new sw.Promed.SwDateField(config);
					cmp.focus(true, 500);
					this.input_cmp_list[eloutputid] = cmp;
				}
				break;
			case 'NumHisto'://номер гистологического исследования
				if(ct && !this.input_cmp_list[eloutputid]) {
					ct.setDisplayed('block');
					eloutput.setDisplayed('none');
					config = getBaseConfig({
						 name: name
						,type: 'string'
						,field_name: 'MorbusOnko_'+ name
						,elOutputId: eloutputid
						,elInputId: elinputid
						,elOutput: eloutput
						,outputValue: empty_value
						,elInputWrap: ct
						,elInput: null
					});
					config.width = 100;
					config.maxLength = 20;
					//config.maskRe = new RegExp("^[0-9]*$");
					//config.allowDecimals = false;
					//config.allowNegative = false;
					cmp = new Ext.form.TextField(config);
					cmp.focus(true, 500);
					this.input_cmp_list[eloutputid] = cmp;
				}
                break;
            case 'IsMainTumor':
			case 'IsTumorDepoUnknown':
			case 'IsTumorDepoLympha':
			case 'IsTumorDepoBones':
			case 'IsTumorDepoLiver':
			case 'IsTumorDepoLungs':
			case 'IsTumorDepoBrain':
			case 'IsTumorDepoSkin':
			case 'IsTumorDepoKidney':
			case 'IsTumorDepoOvary':
			case 'IsTumorDepoPerito':
			case 'IsTumorDepoMarrow':
			case 'IsTumorDepoOther':
			case 'IsTumorDepoMulti':
				if(ct && !this.input_cmp_list[eloutputid]) {
					ct.setDisplayed('block');
					eloutput.setDisplayed('none');
					config = getBaseConfig({
						name: name
						,type: 'id'
						,field_name: 'MorbusOnko_'+ name
						,elOutputId: eloutputid
						,elInputId: elinputid
						,elOutput: eloutput
						,outputValue: empty_value
						,elInputWrap: ct
						,elInput: null
					});
					config.width = 70;
                    config.hideEmptyRow = ('IsMainTumor' == name);
					config.comboSubject = 'YesNo';
					cmp = new sw.Promed.SwCommonSprLikeCombo(config);
					cmp.focus(true, 500);
					this.input_cmp_list[eloutputid] = cmp;
				}
				break;
			case 'OnkoTreatment':
				if(ct && !this.input_cmp_list[eloutputid]) {
					ct.setDisplayed('block');
					eloutput.setDisplayed('none');
					config = getBaseConfig({
						name: name
						,type: 'id'
						,field_name: name + '_id'
						,elOutputId: eloutputid
						,elInputId: elinputid
						,elOutput: eloutput
						,outputValue: empty_value
						,elInputWrap: ct
						,elInput: null
					});
					config.width = 200;
					config.comboSubject = name;
					config.allowBlank = (getRegionNick() == 'kz');
					config.lastQuery = '';
					config.moreFields = [
						{name: 'OnkoTreatment_begDate', type: 'date', dateFormat: 'd.m.Y' },
						{name: 'OnkoTreatment_endDate', type: 'date', dateFormat: 'd.m.Y' }
					];
					config.onLoadStore = function() {
						cmp.getStore().clearFilter();
						// Фильтация на дату окончания случая
						cmp.getStore().filterBy(function(rec) {
							return (
								(Ext.isEmpty(rec.get('OnkoTreatment_begDate')) || rec.get('OnkoTreatment_begDate') <= checkDate)
								&& (Ext.isEmpty(rec.get('OnkoTreatment_endDate')) || rec.get('OnkoTreatment_endDate') >= checkDate)
							);
						});
						cmp.baseFilterFn = function(rec) {
							return (
								(Ext.isEmpty(rec.get('OnkoTreatment_begDate')) || rec.get('OnkoTreatment_begDate') <= checkDate)
								&& (Ext.isEmpty(rec.get('OnkoTreatment_endDate')) || rec.get('OnkoTreatment_endDate') >= checkDate)
							);
						};
					};
					cmp = new sw.Promed.SwCommonSprLikeCombo(config);
					cmp.focus(true, 500);

					cmp.on('change', function(f,n,o) {
						var code, index = f.getStore().findBy(function(rec) {
							return (rec.get('OnkoTreatment_id') == n);
						});

						if ( index >= 0 ) {
							code = f.getStore().getAt(index).get('OnkoTreatment_Code');
						}

						if ( getRegionNick() != 'kz' ) {
							// Добавляем или убираем *
							if ( (linkStoreWithDiagAndSpr.OnkoT.length > 0 || (linkStoreWithoutDiag.OnkoT.length > 0 && linkStoreWithoutSpr.OnkoT.length == 0)) && code === 0 && params.Person_Age >= 18 ) {
								Ext.get('MorbusOnko_OnkoT_allowBlank').setDisplayed('inline');

								if ( form.changed_fields['OnkoT_fid'] ) {
									form.changed_fields['OnkoT_fid'].elInput.setAllowBlank(false);
								}
							}
							else {
								Ext.get('MorbusOnko_OnkoT_allowBlank').setDisplayed('none');

								if ( form.changed_fields['OnkoT_fid'] ) {
									form.changed_fields['OnkoT_fid'].elInput.setAllowBlank(true);
								}
							}

							if ( (linkStoreWithDiagAndSpr.OnkoN.length > 0 || (linkStoreWithoutDiag.OnkoN.length > 0 && linkStoreWithoutSpr.OnkoN.length == 0)) && code === 0 && params.Person_Age >= 18 ) {
								Ext.get('MorbusOnko_OnkoN_allowBlank').setDisplayed('inline');

								if ( form.changed_fields['OnkoN_fid'] ) {
									form.changed_fields['OnkoN_fid'].elInput.setAllowBlank(false);
								}
							}
							else {
								Ext.get('MorbusOnko_OnkoN_allowBlank').setDisplayed('none');

								if ( form.changed_fields['OnkoN_fid'] ) {
									form.changed_fields['OnkoN_fid'].elInput.setAllowBlank(true);
								}
							}

							if ( (linkStoreWithDiagAndSpr.OnkoM.length > 0 || (linkStoreWithoutDiag.OnkoM.length > 0 && linkStoreWithoutSpr.OnkoM.length == 0)) && code === 0 && params.Person_Age >= 18 ) {
								Ext.get('MorbusOnko_OnkoM_allowBlank').setDisplayed('inline');

								if ( form.changed_fields['OnkoM_fid'] ) {
									form.changed_fields['OnkoM_fid'].elInput.setAllowBlank(false);
								}
							}
							else {
								Ext.get('MorbusOnko_OnkoM_allowBlank').setDisplayed('none');

								if ( form.changed_fields['OnkoM_fid'] ) {
									form.changed_fields['OnkoM_fid'].elInput.setAllowBlank(true);
								}
							}

							if ( !Ext.isEmpty(n) && code != 5 && code != 6 ) {
								Ext.get('MorbusOnko_TumorStage_allowBlank').setDisplayed('inline');

								if ( form.changed_fields['TumorStage_fid'] ) {
									form.changed_fields['TumorStage_fid'].elInput.setAllowBlank(false);
								}
							}
							else {
								Ext.get('MorbusOnko_TumorStage_allowBlank').setDisplayed('none');

								if ( form.changed_fields['TumorStage_fid'] ) {
									form.changed_fields['TumorStage_fid'].elInput.setAllowBlank(true);
								}
							}
						}
					});

					this.input_cmp_list[eloutputid] = cmp;
				}
				break;
			case 'TumorStage':
				if(ct && !this.input_cmp_list[eloutputid]) {
					ct.setDisplayed('block');
					eloutput.setDisplayed('none');
					config = getBaseConfig({
						name: name
						,type: 'id'
						,field_name: name + '_id'
						,elOutputId: eloutputid
						,elInputId: elinputid
						,elOutput: eloutput
						,outputValue: empty_value
						,elInputWrap: ct
						,elInput: null
					});
					config.width = 200;
					config.allowBlank = (getRegionNick() == 'kz');
					config.enableKeyEvents = true;
					config.loadParams = getRegionNumber().inlist([101]) ? {mode: 1} : {mode: 2}; // только region_id=null, kz - свои
					config.lastQuery = '';
					config.onLoadStore = function() {
						cmp.getStore().clearFilter();
					};
					cmp = new sw.Promed.SwTumorStageNewCombo(config);
					cmp.focus(true, 500);
					cmp.on('change', function(f,n,o) {
						if (getRegionNick() == 'msk' && n && n.inlist([13, 14, 15, 16])) {
							setValueTo('OnkoStatusYearEndType', {value: 5, outputValue: 'клиническая группа IV'});
						}
					});
					this.input_cmp_list[eloutputid] = cmp;
				}
				break;
			case 'TumorStageF':
				if(ct && !this.input_cmp_list[eloutputid]) {
					var fname = name.substr(0, 10);
					ct.setDisplayed('block');
					eloutput.setDisplayed('none');
					config = getBaseConfig({
						name: fname
						,type: 'id'
						,field_name: fname + '_fid'
						,elOutputId: eloutputid
						,elInputId: elinputid
						,elOutput: eloutput
						,outputValue: empty_value
						,codeField: fname+'Link_CodeStage'
						,elInputWrap: ct
						,elInput: null
					});
					config.width = 200;
					config.allowBlank = (getRegionNick() == 'kz' || OnkoTreatment_Code == 5 || OnkoTreatment_Code == 6);
					config.enableKeyEvents = true;
					config.loadParams = getRegionNumber().inlist([101]) ? {mode: 1} : {mode: 2}; // только region_id=null, kz - свои
					config.lastQuery = '';
					config.valueField = fname+'_id';
					config.displayField = fname+'_Name';
					config.tpl = new Ext.XTemplate(
						'<tpl for="."><div class="x-combo-list-item">',
						'<table style="border: 0;">',
						'<td style="width: 35px;"><font color="red">{'+fname+'Link_CodeStage}&nbsp;</font></td>',
						'<td><span style="font-weight: bold;">{'+fname+'_Name}</span></td>',
						'</tr></table>',
						'</div></tpl>'
					);
					config.store = new Ext.db.AdapterStore({
						dbFile: 'Promed.db',
						tableName: 'fed_' + fname,
						key: fname + 'Link_id',
						autoLoad: true,
						listeners: {
							'load': function(store) {
								cmp.setValue(cmp.getValue());
								config.onLoadStore(store);
							}
						},
						fields: [
							{ name: fname+'_id', mapping: fname+'_id' },
							{ name: fname+'_Name', mapping: fname+'_Name' },
							{ name: fname+'_did', mapping: fname+'_did' },
							{ name: fname+'Link_id', mapping: fname+'Link_id' },
							{ name: fname+'Link_CodeStage', mapping: fname+'Link_CodeStage' }
						],
						sortInfo: {
							field: fname+'_id'
						},
						getById: function(id) { 
							var index = cmp.getStore().findBy(function(rec) {
								if (rec.get(fname+'_id') == id) {
									return true;
								} else {
									return false;
								}
							});
							if (index >= 0) {
								return cmp.getStore().getAt(index);
							}
							return false;
						}
					});
					config.onLoadStore = function() {
						cmp.getStore().clearFilter();
						if ( getRegionNick() != 'kz' ) {
							cmp.getStore().filterBy(function(rec) {
								if ( linkStoreWithDiagAndSpr[fname].length > 0 ) {
									return rec.get(fname + 'Link_id').inlist(linkStoreWithDiagAndSpr[fname]);
								}
								else if ( linkStoreWithoutSpr[fname].length > 0 || linkStoreWithoutDiag[fname].length > 0 ) {
									return rec.get(fname + 'Link_id').inlist(linkStoreWithoutDiag[fname]);
								}
								else {
									return false;
								}
							});
							cmp.baseFilterFn = function(rec) {
								if ( linkStoreWithDiagAndSpr[fname].length > 0 ) {
									return rec.get(fname + 'Link_id').inlist(linkStoreWithDiagAndSpr[fname]);
								}
								else if ( linkStoreWithoutSpr[fname].length > 0 || linkStoreWithoutDiag[fname].length > 0 ) {
									return rec.get(fname + 'Link_id').inlist(linkStoreWithoutDiag[fname]);
								}
								else {
									return false;
								}
							};
						}
					};
					cmp = new sw.Promed.SwBaseLocalCombo(config);
					cmp.on('change', function(f,n,o) {
						var TumorStage_id, code, name;
						var outputValue;
						if ( !Ext.isEmpty(n) ) {
							TumorStage_id = cmp.getFieldValue('TumorStage_did');
							code = cmp.getFieldValue('TumorStageLink_CodeStage');
							name = cmp.getFieldValue('TumorStage_Name');
						}
						var index = form.TumorStageStore.findBy(function(rec) {
							return (rec.get('TumorStage_id') == TumorStage_id);
						});
						outputValue = (TumorStage_id && form.TumorStageStore.getAt(index)) ? form.TumorStageStore.getAt(index).get('TumorStage_Name') : empty_value;
						setValueTo('TumorStage', {value: TumorStage_id, outputValue: outputValue});
						if (code) cmp.setRawValue(code + '. ' + name);
						if (getRegionNick() == 'msk' && TumorStage_id && TumorStage_id.inlist([13, 14, 15, 16])) {
							setValueTo('OnkoStatusYearEndType', {value: 5, outputValue: 'клиническая группа IV'});
						}
					});
					cmp.focus(true, 500);
					this.input_cmp_list[eloutputid] = cmp;
				}
				break;
            case 'HistologicReasonType':
				if(ct && !this.input_cmp_list[eloutputid]) {
					ct.setDisplayed('block');
					eloutput.setDisplayed('none');
					config = getBaseConfig({
						name: name
						,type: 'id'
						,field_name: name + '_id'
						,elOutputId: eloutputid
						,elInputId: elinputid
						,elOutput: eloutput
						,outputValue: empty_value
						,elInputWrap: ct
						,elInput: null
					});
					config.lastQuery = '';
					config.width = 200;
					config.comboSubject = name;
					config.moreFields = [
						{name: 'HistologicReasonType_begDT', type: 'date', dateFormat: 'd.m.Y' },
						{name: 'HistologicReasonType_endDT', type: 'date', dateFormat: 'd.m.Y' }
					];
					config.onLoadStore = function() {
						cmp.getStore().clearFilter();

						cmp.getStore().filterBy(function(rec) {
							return (
								(!rec.get('HistologicReasonType_begDT')  || rec.get('HistologicReasonType_begDT') <= checkDate)
								&& (!rec.get('DeseaseType_endDT') || rec.get('DeseaseType_endDT') >= checkDate)
							);
						});
						cmp.baseFilterFn = function(rec) {
							return (
								(!rec.get('HistologicReasonType_begDT')  || rec.get('HistologicReasonType_begDT') <= checkDate)
								&& (!rec.get('HistologicReasonType_endDT') || rec.get('HistologicReasonType_endDT') >= checkDate)
							);
						};
					};
					cmp = new sw.Promed.SwCommonSprLikeCombo(config);
					cmp.focus(true, 500);
					this.input_cmp_list[eloutputid] = cmp;
				}
				break;
			case 'TumorPrimaryMultipleType':
			case 'OnkoLesionSide':
			case 'OnkoRegType':
			case 'OnkoRegOutType':
			case 'TumorCircumIdentType':
			case 'OnkoLateDiagCause':
            case 'AutopsyPerformType':
            case 'TumorAutopsyResultType':
            case 'OnkoPostType':
            case 'OnkoStatusYearEndType':
            case 'OnkoVariance':
            case 'OnkoRiskGroup':
            case 'OnkoResistance':
            case 'OnkoStatusBegType':
				if(ct && !this.input_cmp_list[eloutputid]) {
                    if ( name.inlist(['AutopsyPerformType','TumorAutopsyResultType'])
                        && (Ext.isEmpty(this.changed_fields['MorbusOnkoBase_deadDT'])
                        || Ext.isEmpty(this.changed_fields['MorbusOnkoBase_deadDT'].value))
                        && Ext.isEmpty(params['MorbusOnkoBase_deadDT'])
                        && getRegionNick() != 'kz'
                        ) {
                        break;
                    }
                    if ( name == 'TumorAutopsyResultType'
                        && (Ext.isEmpty(this.changed_fields['AutopsyPerformType_id'])
                        || !this.changed_fields['AutopsyPerformType_id'].value.toString().inlist(['2','3']))
                        && !(params['AutopsyPerformType_id'] && params['AutopsyPerformType_id'].toString().inlist(['2','3']))
                        ) {
                        break;
                    }
                    if ( name == 'OnkoLateDiagCause'
                    	&& getRegionNick() == 'perm'
                        && (
                        	Ext.isEmpty(this.changed_fields['TumorStage_id'])
                        	|| 
                        	!(this.changed_fields['TumorStage_id'] >= 9 && this.changed_fields['TumorStage_id'] <= 16)
                        	)
                        && !(params['TumorStage_id'] && params['TumorStage_id'] >= 9 && params['TumorStage_id'] <= 16)
                        ) {
                        break;
                    }
					ct.setDisplayed('block');
					eloutput.setDisplayed('none');
					config = getBaseConfig({
						name: name
						,type: 'id'
						,field_name: name + '_id'
						,elOutputId: eloutputid
						,elInputId: elinputid
						,elOutput: eloutput
						,outputValue: empty_value
						,elInputWrap: ct
						,elInput: null
					});
					config.width = 200;
					config.comboSubject = name;
					cmp = new sw.Promed.SwCommonSprLikeCombo(config);
					cmp.focus(true, 500);
					this.input_cmp_list[eloutputid] = cmp;
				}
				break;
			case 'OnkoInvalidType':
				ct.setDisplayed('block');
				eloutput.setDisplayed('none');
				config = getBaseConfig({
					name: name
					,type: 'id'
					,field_name: name +'_id'
					,elOutputId: eloutputid
					,elInputId: elinputid
					,elOutput: eloutput
					,outputValue: empty_value
					,elInputWrap: ct
					,elInput: null

				});
				config.width = 200;
				config.comboSubject = name;
				cmp = new sw.Promed.SwCommonSprLikeCombo(config);
				cmp.focus(true, 500);
				this.input_cmp_list[eloutputid] = cmp;
				break;
			case 'OnkoT':
			case 'OnkoN':
			case 'OnkoM':
				if(ct && !this.input_cmp_list[eloutputid]) {
					ct.setDisplayed('block');
					eloutput.setDisplayed('none');
					config = getBaseConfig({
						name: name
						,type: 'id'
						,field_name: name + '_id'
						,elOutputId: eloutputid
						,elInputId: elinputid
						,elOutput: eloutput
						,outputValue: empty_value
						,elInputWrap: ct
						,elInput: null
					});
					config.allowBlank = !(getRegionNick() != 'kz');
					config.width = 65;
					config.comboSubject = name;
					config.lastQuery = '';
					config.onLoadStore = function() {
						cmp.getStore().clearFilter();
					};
					cmp = new sw.Promed.SwCommonSprLikeCombo(config);
					cmp.focus(true, 500);
					this.input_cmp_list[eloutputid] = cmp;
				}
				break;
			case 'OnkoTF':
			case 'OnkoNF':
			case 'OnkoMF':
				if(ct && !this.input_cmp_list[eloutputid]) {
					var fname = name.substr(0, 5);
					ct.setDisplayed('block');
					eloutput.setDisplayed('none');
					config = getBaseConfig({
						name: fname
						,type: 'id'
						,field_name: fname + '_fid'
						,elOutputId: eloutputid
						,elInputId: elinputid
						,elOutput: eloutput
						,outputValue: empty_value
						,codeField: fname+'Link_CodeStage'
						,elInputWrap: ct
						,elInput: null
					});
					config.allowBlank = !(getRegionNick() != 'kz' && (linkStoreWithDiagAndSpr[fname].length > 0 || (linkStoreWithoutDiag[fname].length > 0 && linkStoreWithoutSpr[fname].length == 0)) && OnkoTreatment_Code === 0 && params.Person_Age >= 18);
					config.width = 100;
					config.lastQuery = '';
					config.valueField = fname+'_id';
					config.displayField = fname+'_Name';
					config.tpl = new Ext.XTemplate(
						'<tpl for="."><div class="x-combo-list-item">',
						'<table style="border: 0;">',
						'<td style="width: 35px;"><font color="red">{'+fname+'Link_CodeStage}&nbsp;</font></td>',
						'<td><span style="font-weight: bold;">{'+fname+'_Name}</span></td>',
						'</tr></table>',
						'</div></tpl>'
					);
					config.store = new Ext.db.AdapterStore({
						dbFile: 'Promed.db',
						tableName: 'fed_' + fname,
						key: fname + 'Link_id',
						autoLoad: true,
						listeners: {
							'load': function(store) {
								cmp.setValue(cmp.getValue());
								config.onLoadStore(store);
							}
						},
						fields: [
							{ name: fname+'_id', mapping: fname+'_id' },
							{ name: fname+'_Name', mapping: fname+'_Name' },
							{ name: fname+'_did', mapping: fname+'_did' },
							{ name: fname+'Link_id', mapping: fname+'Link_id' },
							{ name: fname+'Link_CodeStage', mapping: fname+'Link_CodeStage' }
						],
						sortInfo: {
							field: fname+'_id'
						},
						getById: function(id) { 
							var index = cmp.getStore().findBy(function(rec) {
								if (rec.get(fname+'_id') == id) {
									return true;
								} else {
									return false;
								}
							});
							if (index >= 0) {
								return cmp.getStore().getAt(index);
							}
							return false;
						}
					});
					config.onLoadStore = function() {
						cmp.getStore().clearFilter();

						if ( getRegionNick() != 'kz' ) {
							cmp.getStore().filterBy(function(rec) {
								if ( linkStoreWithDiagAndSpr[fname].length > 0 ) {
									return rec.get(fname + 'Link_id').inlist(linkStoreWithDiagAndSpr[fname]);
								}
								else if ( linkStoreWithoutSpr[fname].length > 0 || linkStoreWithoutDiag[fname].length > 0 ) {
									return rec.get(fname + 'Link_id').inlist(linkStoreWithoutDiag[fname]);
								}
								else {
									return false;
								}
							});
							cmp.baseFilterFn = function(rec) {
								if ( linkStoreWithDiagAndSpr[fname].length > 0 ) {
									return rec.get(fname + 'Link_id').inlist(linkStoreWithDiagAndSpr[fname]);
								}
								else if ( linkStoreWithoutSpr[fname].length > 0 || linkStoreWithoutDiag[fname].length > 0 ) {
									return rec.get(fname + 'Link_id').inlist(linkStoreWithoutDiag[fname]);
								}
								else {
									return false;
								}
							};
						}
					};
					cmp = new sw.Promed.SwBaseLocalCombo(config);
					cmp.on('change', function(f,n,o) {
						var val, code, name;
						var outputValue;
						if ( !Ext.isEmpty(n) ) {
							val = cmp.getFieldValue(fname + '_did');
							code = cmp.getFieldValue(fname + 'Link_CodeStage');
							name = cmp.getFieldValue(fname + '_Name');
						}
						var index = form[fname + 'Store'].findBy(function(rec) {
							return (rec.get(fname + '_id') == val);
						});
						outputValue = (val && form[fname + 'Store'].getAt(index)) ? form[fname + 'Store'].getAt(index).get(fname + '_Name') : empty_value;
						setValueTo(fname, {value: val, outputValue: outputValue});
						if (code) cmp.setRawValue(code + '. ' + name);
					});
					cmp.focus(true, 500);
					this.input_cmp_list[eloutputid] = cmp;
				}
				break;
			case 'OnkoDiag'://морфологический тип опухоли. (Гистология опухоли)
				if(ct && !this.input_cmp_list[eloutputid]) {
					ct.setDisplayed('block');
					eloutput.setDisplayed('none');
					config = getBaseConfig({
						name: name
						,type: 'id'
						,field_name: 'OnkoDiag_mid'
						,elOutputId: eloutputid
						,elInputId: elinputid
						,elOutput: eloutput
						,outputValue: empty_value
						,codeField: 'OnkoDiag_Code'
						,elInputWrap: ct
						,elInput: null
					});
					config.lastQuery = '';
					config.width = 350;
					config.listWidth = 500;
					config.hiddenName = 'OnkoDiag_mid';
					cmp = new sw.Promed.SwOnkoDiagCombo(config);
					var dataid = eloutput.getAttribute('dataid');
					var MorbusOnko_setDiagDT = getValidDT(params.MorbusOnko_setDiagDT, '');
					cmp.getStore().load({
						params: {
							where: 'where '+(dataid ? ('OnkoDiag_id = '+ dataid +' or '):'')+'OnkoDiag_Code like \'%/%\''
							,clause: {where: (dataid ? ('record["OnkoDiag_id"] == "'+ dataid +'" || '):'')+'record["OnkoDiag_pid"] > "0"'}
						},
						callback: function(){
							if ( typeof MorbusOnko_setDiagDT == 'object' ) {
								cmp.getStore().filterBy(function(rec) {
									return (
										(Ext.isEmpty(rec.get('OnkoDiag_begDate')) || rec.get('OnkoDiag_begDate') <= MorbusOnko_setDiagDT)
										&& (Ext.isEmpty(rec.get('OnkoDiag_endDate')) || rec.get('OnkoDiag_endDate') >= MorbusOnko_setDiagDT)
									);
								});

								cmp.baseFilterFn = function(rec) {
									return (
										(Ext.isEmpty(rec.get('OnkoDiag_begDate')) || rec.get('OnkoDiag_begDate') <= MorbusOnko_setDiagDT)
										&& (Ext.isEmpty(rec.get('OnkoDiag_endDate')) || rec.get('OnkoDiag_endDate') >= MorbusOnko_setDiagDT)
									);
								}
							}
							if(cmp.getStore().getCount() > 0 && dataid && dataid > 0) {
								cmp.setValue(dataid);
							}
						},
						scope: cmp
					});
					cmp.focus(true, 500);
					this.input_cmp_list[eloutputid] = cmp;
				}
				break;
			case 'Lpu'://в какое медицинское учреждение
				if(ct && !this.input_cmp_list[eloutputid]) {
					ct.setDisplayed('block');
					eloutput.setDisplayed('none');
					config = getBaseConfig({
						name: name
						,type: 'id'
						,field_name: 'Lpu_foid'
						,elOutputId: eloutputid
						,elInputId: elinputid
						,elOutput: eloutput
						,outputValue: null
						,elInputWrap: ct
						,elInput: null
					});
					config.width = 200;
					cmp = new sw.Promed.SwLpuLocalCombo(config);
					var dataid = eloutput.getAttribute('dataid');
					cmp.getStore().load({
						callback: function(){
							if(this.getStore().getCount() > 0) {
								this.setValue(dataid);
							}
						},
						scope: cmp
					});
					cmp.focus(true, 500);
					this.input_cmp_list[eloutputid] = cmp;
				}
				break;
            case 'DiagDead'://
                if(ct && !this.input_cmp_list[eloutputid]) {
                    if ( (Ext.isEmpty(this.changed_fields['MorbusOnkoBase_deadDT'])
                        || Ext.isEmpty(this.changed_fields['MorbusOnkoBase_deadDT'].value))
                        && Ext.isEmpty(params['MorbusOnkoBase_deadDT'])
                    ) {
                        break;
                    }
                    ct.setDisplayed('block');
                    eloutput.setDisplayed('none');
                    var config_data = {
                        name: name
                        ,type: 'id'
                        ,field_name: 'Diag_did'
                        ,elOutputId: eloutputid
                        ,elInputId: elinputid
                        ,elOutput: eloutput
                        ,outputValue: empty_value
                        ,codeField: 'Diag_Code'
                        ,elInputWrap: ct
                        ,elInput: null
                    };
                    config = getBaseConfig(config_data);
                    config.width = 350;
                    config.listWidth = 600;
                    config.hiddenName = name;
                    //var change = Ext.apply(config.listeners.change);
                    //delete config.listeners.change;
                    cmp = new sw.Promed.SwDiagCombo(config);
                    var dataid = eloutput.getAttribute('dataid');
                    if (dataid && dataid > 0) {
                        cmp.getStore().load({
                            params: {where: 'where Diag_id = '+dataid},
                            callback: function(){
                                if(this.getStore().getCount() > 0 && dataid && dataid > 0) {
                                    this.setValue(dataid);
                                    this.getStore().each(function(record) {
                                        if (record.get('Diag_id') == dataid) {
                                            cmp.fireEvent('select', cmp, record, 0);
                                        }
                                    });
                                }
                                this.focus(true, 100);
                            },
                            scope: cmp
                        });
                    } else {
                        cmp.focus(true, 100);
                    }
                    this.input_cmp_list[eloutputid] = cmp;
                }
                break;
            case 'EndDiag'://
                if(ct && !this.input_cmp_list[eloutputid]) {
                    ct.setDisplayed('block');
                    eloutput.setDisplayed('none');

                    var filterDate = null;
					cmp = new sw.Promed.SwDiagCombo({
						checkAccessRights: true
						,renderTo: elinputid
						,width: 360
						,filterDate: filterDate
					});
					cmp.on('blur', function(f) {
						var options = {
							name: name
							,type: 'id'
							,field_name: field_name
							,elOutputId: eloutputid
							,elInputId: elinputid
							,elOutput: eloutput
							,outputValue: empty_value
							,elInputWrap: ct
							,elInput: null
						};
						options.elInput = f;
						if(!Ext.get('DiagSearchTreeWindow') || !Ext.get('DiagSearchTreeWindow').isVisible()){
							onCancel(options);
						}
					});
					cmp.on('render', function(f) {
						var dataid = options.elOutput.getAttribute('dataid');
						if(options.type == 'id') {
							//if(!f.getStore() || f.getStore().getCount()==0) log('not store: ' + options.field_name);
							if(!Ext.isEmpty(dataid) && parseInt(dataid) != 0) {
								f.setValue(parseInt(dataid));
							} else {
								f.setValue(null);
							}
						} else {
							f.setValue(dataid);
						}
					});
					cmp.on('change', function(f,n,o) {
						if(!n){
							return false;
						}
						var options = {
							name: name
							,type: 'id'
							,field_name: field_name
							,elOutputId: eloutputid
							,elInputId: elinputid
							,elOutput: eloutput
							,outputValue: empty_value
							,elInputWrap: ct
							,elInput: null
						};
						var rec = (n)?f.getStore().getById(n):false;
						if(rec) {
							options.value = n;
							options.outputValue = rec.get(f.displayField);
						} else {
							options.value = 0;
							options.outputValue = empty_value;
						}

						options.elInput = f;
						if(!Ext.get('DiagSearchTreeWindow') || !Ext.get('DiagSearchTreeWindow').isVisible()){
							onChange(options);
						}
					});
                    var dataid = eloutput.getAttribute('dataid');
                    if (dataid && dataid > 0) {
                        cmp.getStore().load({
                            params: {where: 'where Diag_id = '+dataid},
                            callback: function(){
                                if(this.getStore().getCount() > 0 && dataid && dataid > 0) {
                                    this.setValue(dataid);
                                    this.getStore().each(function(record) {
                                        if (record.get('Diag_id') == dataid) {
                                        	var diag_code = record.get('Diag_Code').substr(0, 3);
                                        	cmp.baseFilterFn = function(rec){
                                        		if(typeof rec.get == 'function'){
                                        			return (rec.get('Diag_Code').substr(0, 3) == diag_code);
                                        		} else {
                                        			return (rec.attributes.Diag_Code.substr(0, 3) == diag_code);
                                        		}
                                        	};
                                            cmp.fireEvent('select', cmp, record, 0);
                                        }
                                    });
                                }
                                this.focus(true, 100);
                            },
                            scope: cmp
                        });
                    } else {
                        cmp.focus(true, 100);
                    }
                    this.input_cmp_list[eloutputid] = cmp;
                }
                break;
            case 'NumCard'://
                if(ct && !this.input_cmp_list[eloutputid]) {
                    ct.setDisplayed('block');
                    eloutput.setDisplayed('none');
                    config = getBaseConfig({
                        name: name
                        ,type: 'string'
                        ,field_name: 'MorbusOnkoBase_NumCard'
                        ,elOutputId: eloutputid
                        ,elInputId: elinputid
                        ,elOutput: eloutput
                        ,outputValue: empty_value
                        ,elInputWrap: ct
                        ,elInput: null
                    });
					config.maskRe = new RegExp(".*");
                    config.maxLength = 10; // В БД поле varchar(10)
                    config.width = 100;
                    cmp = new Ext.form.TextField(config);
                    cmp.focus(true, 500);
                    this.input_cmp_list[eloutputid] = cmp;
                }
                break;
		}
	},
	openMorbusOnkoSpecificForm: function(options) {

		var win = this;
		if(!options.action || !options.object || !options.eldata) {
			return false;
		}
		if(this.allowSpecificEdit == false && !options.action.inlist(['view', 'print'])) {
			return false;
		}
		
		var win_name,
			object_id,
			data,
			mhdata,
			evndata,
			evnsysnick = null, //(this.data.Code.inlist(['EvnPL','EvnVizitPL']))?'EvnVizitPL':'EvnSection',
			params = {formParams: {}};
			
		/*
		log('openMorbusOnkoSpecificForm');
		log(options);
		*/

		if(options.action.inlist(['add', 'select'])) {
			object_id = (options.eldata.object_id.split('_').length > 1)?options.eldata.object_id.split('_')[1]:options.eldata.object_id;
			mhdata = options.mhdata || this.rightPanel.getObjectData('MorbusOnko',object_id);
			if(!mhdata) {
				return false;
			}
		} else {
			object_id = (options.eldata.object_id.split('_').length > 1)?options.eldata.object_id.split('_')[1]:options.eldata.object_id;
			data = this.rightPanel.getObjectData(options.object,object_id);
			if(!data) {
				return false;
			}
			mhdata = options.mhdata || this.rightPanel.getObjectData('MorbusOnko',data.Morbus_id);
			if(!mhdata) {
				return false;
			}
		}

		params.callback = function() {
			var reload_params = {
				section_code: options.object,
				object_key: options.object +'_id',
				object_value: object_id,
				parent_object_key: 'Morbus_id',
				parent_object_value: mhdata.Morbus_id,
				param_name: 'MorbusOnko_pid',
				param_value: mhdata.MorbusOnko_pid,
				MorbusOnkoVizitPLDop_id: mhdata.MorbusOnkoVizitPLDop_id,
				MorbusOnkoLeave_id: mhdata.MorbusOnkoLeave_id,
				MorbusOnkoDiagPLStom_id: mhdata.MorbusOnkoDiagPLStom_id,
				section_id: options.object +'List_'+ mhdata.MorbusOnko_pid +'_'+ mhdata.Morbus_id
			};
			if ( mhdata.Person_id == mhdata.MorbusOnko_pid ) {
				reload_params.Person_id = mhdata.Person_id;
			}
			
			if (options.object.inlist(['MorbusOnkoChemTer','MorbusOnkoGormTer'])) {
				reload_params.callback = function() {
					win.rightPanel.reloadViewForm({
						MorbusOnkoLeave_id: mhdata.MorbusOnkoLeave_id,
						MorbusOnkoVizitPLDop_id: mhdata.MorbusOnkoVizitPLDop_id,
						MorbusOnkoDiagPLStom_id: mhdata.MorbusOnkoDiagPLStom_id,
						object_key: "MorbusOnkoDrug_id",
						object_value: object_id,
						param_name: "MorbusOnko_pid",
						param_value: mhdata.MorbusOnko_pid,
						parent_object_key: "Morbus_id",
						parent_object_value: mhdata.Morbus_id,
						section_code: "MorbusOnkoDrug",
						section_id: 'MorbusOnkoDrugList_'+mhdata.MorbusOnko_pid+'_'+mhdata.Morbus_id
					});
				};
			}
			this.rightPanel.reloadViewForm(reload_params);
		}.createDelegate(this);

        params.formParams = {
            Morbus_id: mhdata.Morbus_id,
            MorbusOnko_id: mhdata.MorbusOnko_id,
            MorbusOnkoBase_id: mhdata.MorbusOnkoBase_id,
            Person_id: this.Person_id,
            PersonEvn_id: this.PersonEvn_id,
            Server_id: this.Server_id,
            Lpu_id: getGlobalOptions().lpu_id,
            Lpu_uid: getGlobalOptions().lpu_id,
			Evn_id:( mhdata.Person_id !== mhdata.MorbusOnko_pid ) ? mhdata.MorbusOnko_pid : ''
        };
        params.action = options.action;
		params.MorbusOnkoVizitPLDop_id = mhdata.MorbusOnkoVizitPLDop_id;
		params.MorbusOnkoLeave_id = mhdata.MorbusOnkoLeave_id;
		params.MorbusOnkoDiagPLStom_id = mhdata.MorbusOnkoDiagPLStom_id;
		params.formParams.MorbusOnkoVizitPLDop_id = mhdata.MorbusOnkoVizitPLDop_id;
		params.formParams.MorbusOnkoLeave_id = mhdata.MorbusOnkoLeave_id;
		params.formParams.MorbusOnkoDiagPLStom_id = mhdata.MorbusOnkoDiagPLStom_id;
		switch(options.object) {
			case 'MorbusOnkoBasePersonState':
				win_name = 'swMorbusOnkoBasePersonStateWindow';
                params.formParams.Evn_id = null;
                if(options.action == 'add') {
                    params.formParams.MorbusOnkoBasePersonState_id = null;
                } else {
                    params.formParams.MorbusOnkoBasePersonState_id = object_id;
                }
				break;
			case 'MorbusOnkoBasePS':
				win_name = 'swMorbusOnkoBasePSWindow';
                params.formParams.Evn_id = null;
				if(options.action == 'add') {
                    params.formParams.MorbusOnkoBasePS_id = null;
				} else {
					params.formParams.MorbusOnkoBasePS_id = object_id;
				}
				break;
			case 'MorbusOnkoSopDiag':
				win_name = 'swMorbusOnkoSopDiagWindow';
				params.formParams.Evn_id = null;
				if (options.action == 'add') {
					params.formParams.MorbusOnkoBaseDiagLink_id = null;
				} else {
					params.formParams.MorbusOnkoBaseDiagLink_id = object_id;
				}
				break;
			case 'OnkoConsult':
				win_name = 'swOnkoConsultEditWindow';
				params.MorbusOnko_id = mhdata.MorbusOnko_id;
				params.MorbusOnkoVizitPLDop_id = mhdata.MorbusOnkoVizitPLDop_id;
				params.MorbusOnkoLeave_id = mhdata.MorbusOnkoLeave_id;
				params.MorbusOnkoDiagPLStom_id = mhdata.MorbusOnkoDiagPLStom_id;
				params.EvnSection_id = win.EvnSection_id;
				params.EvnVizitPL_id = win.EvnVizitPL_id;
				params.EvnVizitDispDop_id = win.EvnVizitDispDop_id;
				if (options.action == 'add') {
					params.OnkoConsult_id = null;
				} else {
					params.OnkoConsult_id = object_id;
				}
				break;
			case 'MorbusOnkoPersonDisp':
				win_name = 'swPersonDispEditWindow';
				params.formParams.Evn_id = null;
				if (options.action == 'add') {
					params.formParams.PersonDisp_id = 0;
					var el = document.getElementById('MorbusOnko_'+mhdata.MorbusOnko_pid+'_'+mhdata.Morbus_id+'_inputMorbusBaseSetDT');
					if(el){
						var dateVal = el.innerHTML;
						if(!(dateVal.indexOf('Не указано') >= 0)){
							dateAr = dateVal.split('.');
							if(dateAr.length == 3){
								params.formParams.PersonDisp_begDate = new Date(dateAr[2],parseInt(dateAr[1])-1,dateAr[0]);
							}
						}
					}
					var el = document.getElementById('MorbusOnko_'+mhdata.MorbusOnko_pid+'_'+mhdata.Morbus_id+'_inputsetDiagDT');
					if(el){
						var dateVal = el.innerHTML;
						if(!(dateVal.indexOf('Не указано') >= 0)){
							dateAr = dateVal.split('.');
							if(dateAr.length == 3){
								params.formParams.PersonDisp_DiagDate = new Date(dateAr[2],parseInt(dateAr[1])-1,dateAr[0]);
							}
						}
					}
					var el = document.getElementById('MorbusOnko_'+mhdata.MorbusOnko_pid+'_'+mhdata.Morbus_id+'_DiagValue');
					if(el){
						var diagVal = el.getAttribute("dataid");
						if(!Ext.isEmpty(diagVal)){
							params.formParams.Diag_id = diagVal;
						}
					}
					if(this.ARMType == 'common'){
						params.UserMedStaffFact_id = this.userMedStaffFact.MedStaffFact_id;
						params.UserLpuSection_id = this.userMedStaffFact.LpuSection_id;
					}
				} else if (options.action == 'select') {
					win_name = 'swPersonDispSelectWindow';
				} else {
					params.formParams.PersonDisp_id = object_id;
				}
				params.formParams.PersonRegister_id = this.PersonRegister_id;
				params.callback = function(data) {
					var reload_params = {
						section_code: options.object,
						object_key: 'PersonRegister_id',
						object_value: this.PersonRegister_id,
						parent_object_key: 'Morbus_id',
						parent_object_value: mhdata.Morbus_id,
						param_name: 'MorbusOnko_pid',
						param_value: mhdata.MorbusOnko_pid,
						section_id: options.object +'List_'+ mhdata.MorbusOnko_pid +'_'+ mhdata.Morbus_id
					};
					this.rightPanel.reloadViewForm(reload_params);
				}.createDelegate(this);
				break;
			case 'MorbusOnkoDrug':
				win_name = 'swMorbusOnkoDrugWindow';
				if (options.action == 'add') {
					params.formParams.MorbusOnkoDrug_id = null;
				} else {
					params.formParams.MorbusOnkoDrug_id = object_id;
				}

				params.callback = function(data) {
					var MorbusOnkoDrug_id = 0;
					if(this.rightPanel.viewFormDataStore ) {
						var viewFormDataStore = this.rightPanel.viewFormDataStore.data.items;
						for (var index in viewFormDataStore) {
							if(viewFormDataStore[index].object_key == 'MorbusOnkoDrug_id') {
								MorbusOnkoDrug_id = viewFormDataStore[index].object_value;
							}
						}
					};
					var reload_params = {
						section_code: 'MorbusOnkoDrug',
						object_key: 'MorbusOnkoDrug_id',
						object_value: MorbusOnkoDrug_id,
						parent_object_key: 'Morbus_id',
						parent_object_value: mhdata.Morbus_id,
						param_name: 'MorbusOnko_pid',
						param_value: mhdata.MorbusOnko_pid,
						section_id: 'MorbusOnkoDrugList_'+ mhdata.MorbusOnko_pid +'_'+ mhdata.Morbus_id,
						MorbusOnkoLeave_id: mhdata.MorbusOnkoLeave_id,
						MorbusOnkoVizitPLDop_id: mhdata.MorbusOnkoVizitPLDop_id
					};

					this.rightPanel.reloadViewForm(reload_params);
				}.createDelegate(this);
				break;
			case 'MorbusOnkoSpecTreat':
				win_name = 'swMorbusOnkoSpecTreatWindow';
				params.formParams.Evn_id = null;
				params.formParams.MorbusOnkoVizitPLDop_id = mhdata.MorbusOnkoVizitPLDop_id;
				params.formParams.MorbusOnkoLeave_id = mhdata.MorbusOnkoLeave_id;
				params.formParams.MorbusOnkoDiagPLStom_id = mhdata.MorbusOnkoDiagPLStom_id;
				if (options.action == 'add') {
					params.formParams.MorbusOnkoSpecTreat_id = null;
				} else {
					params.formParams.MorbusOnkoSpecTreat_id = object_id;
				}
				break;
			case 'MorbusOnkoLink':
				win_name = 'swMorbusOnkoLinkDiagnosticsWindow';
				var el = document.getElementById('MorbusOnko_'+mhdata.MorbusOnko_pid+'_'+mhdata.Morbus_id+'_DiagValue');
				if(el){
					var diagVal = el.getAttribute("dataid");
					if(!Ext.isEmpty(diagVal)){
						params.formParams.Diag_id = diagVal;
					}
				}
				params.formParams.MorbusOnkoVizitPLDop_id = mhdata.MorbusOnkoVizitPLDop_id;
				params.formParams.MorbusOnkoLeave_id = mhdata.MorbusOnkoLeave_id;
				params.formParams.MorbusOnkoDiagPLStom_id = mhdata.MorbusOnkoDiagPLStom_id;
				params.formParams.Evn_disDate = mhdata.Evn_disDate ? mhdata.Evn_disDate : getGlobalOptions().date;
				params.formParams.HistologicReasonType_id = win.changed_fields['HistologicReasonType_id'] ? win.changed_fields['HistologicReasonType_id'].elInput.getValue() : mhdata.HistologicReasonType_id;
				if (options.action == 'add') {
					params.formParams.MorbusOnkoLink_id = null;
				} else {
					params.formParams.MorbusOnkoLink_id = object_id;
				}
				break;
			case 'MorbusOnkoRefusal':
				win_name = 'swMorbusOnkoRefusalWindow';
				params.formParams.Evn_id = null;
				params.formParams.MorbusOnkoVizitPLDop_id = mhdata.MorbusOnkoVizitPLDop_id;
				params.formParams.MorbusOnkoLeave_id = mhdata.MorbusOnkoLeave_id;
				if (options.action == 'add') {
					params.isRefusal = options.isRefusal;
					params.formParams.MorbusOnkoRefusal_id = null;
				} else {
					params.formParams.MorbusOnkoRefusal_id = object_id;
				}
				break;
			case 'MorbusOnkoChemTer':
				win_name = 'swEvnUslugaOnkoChemEditWindow';
                params.formParams.EvnUslugaOnkoChem_pid = this.MorbusOnko_pid;
				if(options.action == 'add') {
                    params.EvnUslugaOnkoChem_id = null;
                    params.formParams.EvnUslugaOnkoChem_id = null;
				} else {
                    params.EvnUslugaOnkoChem_id = object_id;
                    params.formParams.EvnUslugaOnkoChem_id = object_id;
				}

				params.onSaveDrug = function(data) {
					var DrugTherapyScheme_id = 0;
					if(this.rightPanel.viewFormDataStore ) {
						var viewFormDataStore = this.rightPanel.viewFormDataStore.data.items;
						for (var index in viewFormDataStore) {
							if(viewFormDataStore[index].object_key == 'DrugTherapyScheme_id') {
								DrugTherapyScheme_id = viewFormDataStore[index].object_value;
							}
						}
					};
					var reload_params = {
						section_code: 'DrugTherapyScheme',
						object_key: 'DrugTherapyScheme_id',
						object_value: DrugTherapyScheme_id,
						parent_object_key: 'Morbus_id',
						parent_object_value: mhdata.Morbus_id,
						param_name: 'MorbusOnko_pid',
						param_value: mhdata.MorbusOnko_pid,
						section_id: 'DrugTherapySchemeList_'+ mhdata.MorbusOnko_pid +'_'+ mhdata.Morbus_id
					};

					this.rightPanel.reloadViewForm(reload_params);
				}.createDelegate(this);
				break;
			case 'MorbusOnkoGormTer':
				win_name = 'swEvnUslugaOnkoGormunEditWindow';
                params.formParams.EvnUslugaOnkoGormun_pid = this.MorbusOnko_pid;
                if(options.action == 'add') {
                    params.EvnUslugaOnkoGormun_id = null;
                    params.formParams.EvnUslugaOnkoGormun_id = null;
                } else {
                    params.EvnUslugaOnkoGormun_id = object_id;
                    params.formParams.EvnUslugaOnkoGormun_id = object_id;
                }

                params.onSaveDrug = function(data) {
					var DrugTherapyScheme_id = 0;
					if(this.rightPanel.viewFormDataStore ) {
						var viewFormDataStore = this.rightPanel.viewFormDataStore.data.items;
						for (var index in viewFormDataStore) {
							if(viewFormDataStore[index].object_key == 'DrugTherapyScheme_id') {
								DrugTherapyScheme_id = viewFormDataStore[index].object_value;
							}
						}
					};
					var reload_params = {
						section_code: 'DrugTherapyScheme',
						object_key: 'DrugTherapyScheme_id',
						object_value: DrugTherapyScheme_id,
						parent_object_key: 'Morbus_id',
						parent_object_value: mhdata.Morbus_id,
						param_name: 'MorbusOnko_pid',
						param_value: mhdata.MorbusOnko_pid,
						section_id: 'DrugTherapySchemeList_'+ mhdata.MorbusOnko_pid +'_'+ mhdata.Morbus_id
					};

					this.rightPanel.reloadViewForm(reload_params);
				}.createDelegate(this);
				break;
			case 'MorbusOnkoNonSpecTer':
				win_name = 'swEvnUslugaOnkoNonSpecEditWindow';
                params.formParams.EvnUslugaOnkoNonSpec_pid = this.MorbusOnko_pid;
                if(options.action == 'add') {
                    params.EvnUslugaOnkoNonSpec_id = null;
                    params.formParams.EvnUslugaOnkoNonSpec_id = null;
                } else {
                    params.EvnUslugaOnkoNonSpec_id = object_id;
                    params.formParams.EvnUslugaOnkoNonSpec_id = object_id;
                }
				break;
			case 'MorbusOnkoRadTer':
				win_name = 'swEvnUslugaOnkoBeamEditWindow';
                params.formParams.EvnUslugaOnkoBeam_pid = this.MorbusOnko_pid;
                if(options.action == 'add') {
                    params.EvnUslugaOnkoBeam_id = null;
                    params.formParams.EvnUslugaOnkoBeam_id = null;
                } else {
                    params.EvnUslugaOnkoBeam_id = object_id;
                    params.formParams.EvnUslugaOnkoBeam_id = object_id;
                }
				break;
			case 'MorbusOnkoHirTer':
				win_name = 'swEvnUslugaOnkoSurgEditWindow';
                params.formParams.EvnUslugaOnkoSurg_pid = this.MorbusOnko_pid;
                if(options.action == 'add') {
                    params.EvnUslugaOnkoSurg_id = null;
                    params.formParams.EvnUslugaOnkoSurg_id = null;

                    params.EvnSection_id = win.EvnSection_id;
                } else {
                    params.EvnUslugaOnkoSurg_id = object_id;
                    params.formParams.EvnUslugaOnkoSurg_id = object_id;
                }
				break;
			default:
				return false;
		}
		getWnd(win_name).show(params);
	},
	loadNodeViewForm: function() 
	{
		var form = this;
		if(this.MorbusOnko_pid) {
			this.viewObject.attributes.object_id = 'MorbusOnko_pid';
			this.viewObject.attributes.object_value = this.MorbusOnko_pid;
		}
		if (this.PersonRegister_id) {
			this.rightPanel.loadNodeViewForm(this.viewObject,{
				param_name: 'PersonRegister_id',
				param_value: this.PersonRegister_id
			});
		} else if(this.Morbus_id) {
			this.rightPanel.loadNodeViewForm(this.viewObject,{
				param_name: 'Morbus_id',
				param_value: this.Morbus_id,
				EvnDiagPLStomSop_id: this.EvnDiagPLStomSop_id,
				EvnDiagPLSop_id: this.EvnDiagPLSop_id,
				callback: function() {
					Ext.Ajax.request({
						url: '/?c=EvnOnkoNotify&m=getEvnOnkoNotifyList',
						params: {
							Person_id: form.Person_id
						},
						success: function(response) {
							var data = JSON.parse(response.responseText);
							 //form.getObjectData('EvnVizitPL', form.EvnVizitPL).Diag_Code;
							var addNotifyButton = Ext.select('#MorbusOnkoWindow [id*="addEvnOnkoNotifyContainer"]');
							var newNotifyDiagCodeArr = Ext.select('#MorbusOnkoWindow [id*="_DiagValue"]').item(0).dom.innerText.split('.', 2);
							
							if (typeof(newNotifyDiagCodeArr[1]) != 'number' || !newNotifyDiagCodeArr[1]) { 
								newNotifyDiagCodeArr[1] = 0;
							}

							var exist = false;
							for(var key in data) {
								if(data[key].Diag_Code) {
									var oldNotifyDiagCodeArr = data[key].Diag_Code.split('.');
									if(!oldNotifyDiagCodeArr[1]) { oldNotifyDiagCodeArr[1] = 0 }
									if(	
										newNotifyDiagCodeArr[0] == oldNotifyDiagCodeArr[0]
										&& newNotifyDiagCodeArr[1] == oldNotifyDiagCodeArr[1]
									) {
										exist = true;
									}
								}
							}
							if(!exist) {
								addNotifyButton.show();
							} else {
								addNotifyButton.hide();
							}
						}
					});
				}
			});
		} else if(this.EvnDiagPLStom_id) { // создание специфики
			this.rightPanel.loadNodeViewForm(this.viewObject,{
				param_name: 'EvnDiagPLStom_id',
				param_value: this.EvnDiagPLStom_id,
				//parent_object_key: 'MorbusOnko_pid',
				//parent_object_value: this.MorbusOnko_pid,
				EvnDiagPLStomSop_id: this.EvnDiagPLStomSop_id,
				EvnDiagPLSop_id: this.EvnDiagPLSop_id
			});
		} else if(this.EvnVizitPL_id) { // создание специфики
			this.rightPanel.loadNodeViewForm(this.viewObject,{
				param_name: 'EvnVizitPL_id',
				param_value: this.EvnVizitPL_id,
				EvnDiagPLSop_id: this.EvnDiagPLSop_id
			});
		} else if(this.EvnVizitDispDop_id) { // создание специфики
			this.rightPanel.loadNodeViewForm(this.viewObject,{
				param_name: 'EvnVizitDispDop_id',
				param_value: this.EvnVizitDispDop_id,
				EvnDiagPLSop_id: this.EvnDiagPLSop_id
			});
		} else if(this.EvnSection_id) { // создание специфики
			this.rightPanel.loadNodeViewForm(this.viewObject,{
				param_name: 'EvnSection_id',
				param_value: this.EvnSection_id,
				EvnDiagPLSop_id: this.EvnDiagPLSop_id
			});
		}
	},

	show: function() 
	{
		sw.Promed.swMorbusOnkoWindow.superclass.show.apply(this, arguments);
		
		if ( !arguments[0] || !arguments[0].Person_id || !arguments[0].PersonEvn_id || typeof arguments[0].Server_id == 'undefined' ) {
			sw.swMsg.alert(langs('Сообщение'), langs('Неверные параметры'), function() { this.hide(); }.createDelegate(this) );
			return false;
		}
		this.editType = 'all';
        if(arguments[0] && arguments[0].editType){
            this.editType = arguments[0].editType;
        }
		this.Person_id = arguments[0].Person_id;
		this.PersonEvn_id = arguments[0].PersonEvn_id;
		this.Server_id = arguments[0].Server_id;
		this.EvnDiagPLStom_id = arguments[0].EvnDiagPLStom_id;
		this.PersonRegister_id = arguments[0].PersonRegister_id || null;
		this.EvnOnkoNotifyNeglected_id = arguments[0].EvnOnkoNotifyNeglected_id;
		this.MorbusOnkoLeave_id = arguments[0].MorbusOnkoLeave_id;
		this.MorbusOnkoVizitPLDop_id = arguments[0].MorbusOnkoVizitPLDop_id;
		this.MorbusOnkoDiagPLStom_id = arguments[0].MorbusOnkoDiagPLStom_id;
		this.EvnVizitPL_id = arguments[0].EvnVizitPL_id;
		this.EvnVizitDispDop_id = arguments[0].EvnVizitDispDop_id;
		this.EvnSection_id = arguments[0].EvnSection_id;
		this.Morbus_id = arguments[0].Morbus_id;
		this.MorbusOnko_pid = arguments[0].MorbusOnko_pid;

		// различные сопутствующие
		this.EvnDiagPLStomSop_id = arguments[0].EvnDiagPLStomSop_id || null;
		this.EvnDiagPLSop_id = arguments[0].EvnDiagPLSop_id || null;
		
		this.userMedStaffFact = arguments[0].userMedStaffFact || sw.Promed.MedStaffFactByUser.last;
		this.ARMType = arguments[0].ARMType || null;
		this.onHide = arguments[0].onHide || Ext.emptyFn;
		this.isChange = false;
		this.allowSpecificEdit = arguments[0].allowSpecificEdit || false;
		if(arguments[0] && arguments[0].action == 'view')
			this.allowSpecificEdit = false;

		if (this.MorbusOnko_pid) {
			this.setTitle('Специфика');
		}

		this.viewObject = {
			id: 'PersonMorbusOnko_'+ this.Person_id,
			attributes: {
				accessType: (this.allowSpecificEdit == true)?'edit':'view',
				text: 'test',
				object: 'PersonMorbusOnko',
				object_id: 'Person_id',
				object_value: this.Person_id			
			}
		};

		if ( this.OnkoMLinkStore.getCount() == 0 ) {
			this.OnkoMLinkStore.load();
		}

		if ( this.OnkoNLinkStore.getCount() == 0 ) {
			this.OnkoNLinkStore.load();
		}

		if ( this.OnkoTLinkStore.getCount() == 0 ) {
			this.OnkoTLinkStore.load();
		}

		if ( this.TumorStageLinkStore.getCount() == 0 ) {
			this.TumorStageLinkStore.load();
		}

		if ( this.TumorStageStore.getCount() == 0 ) {
			this.TumorStageStore.load();
		}

		if ( this.OnkoTStore.getCount() == 0 ) {
			this.OnkoTStore.load();
		}

		if ( this.OnkoNStore.getCount() == 0 ) {
			this.OnkoNStore.load();
		}

		if ( this.OnkoMStore.getCount() == 0 ) {
			this.OnkoMStore.load();
		}

		/*if ( this.OnkoTNMDiagStore.getCount() == 0 ) {
			this.OnkoTNMDiagStore.load();
		}*/

		this.loadNodeViewForm();
        this.changed_fields = {};
	},
	OnkoMLinkStore: new Ext.db.AdapterStore({
		autoLoad: false,
		dbFile: 'Promed.db',
		fields: [
			{ name: 'OnkoMLink_id', type: 'int', mapping: 'OnkoMLink_id' },
			{ name: 'OnkoM_id', type: 'int', mapping: 'OnkoM_id' },
			{ name: 'Diag_id', type: 'int', mapping: 'Diag_id' },
			{ name: 'OnkoM_fid', type: 'int', mapping: 'OnkoM_fid' },
			{ name: 'OnkoMLink_begDate', type: 'date', mapping: 'OnkoMLink_begDate', dateFormat: 'd.m.Y' },
			{ name: 'OnkoMLink_endDate', type: 'date', mapping: 'OnkoMLink_endDate', dateFormat: 'd.m.Y' }
		],
		key: 'OnkoMLink_id',
		tableName: 'OnkoMLink'
	}),
	OnkoNLinkStore: new Ext.db.AdapterStore({
		autoLoad: false,
		dbFile: 'Promed.db',
		fields: [
			{ name: 'OnkoNLink_id', type: 'int', mapping: 'OnkoNLink_id' },
			{ name: 'OnkoN_id', type: 'int', mapping: 'OnkoN_id' },
			{ name: 'Diag_id', type: 'int', mapping: 'Diag_id' },
			{ name: 'OnkoN_fid', type: 'int', mapping: 'OnkoN_fid' },
			{ name: 'OnkoNLink_begDate', type: 'date', mapping: 'OnkoNLink_begDate', dateFormat: 'd.m.Y' },
			{ name: 'OnkoNLink_endDate', type: 'date', mapping: 'OnkoNLink_endDate', dateFormat: 'd.m.Y' }
		],
		key: 'OnkoNLink_id',
		tableName: 'OnkoNLink'
	}),
	OnkoTLinkStore: new Ext.db.AdapterStore({
		autoLoad: false,
		dbFile: 'Promed.db',
		fields: [
			{ name: 'OnkoTLink_id', type: 'int', mapping: 'OnkoTLink_id' },
			{ name: 'OnkoT_id', type: 'int', mapping: 'OnkoT_id' },
			{ name: 'Diag_id', type: 'int', mapping: 'Diag_id' },
			{ name: 'OnkoT_fid', type: 'int', mapping: 'OnkoT_fid' },
			{ name: 'OnkoTLink_begDate', type: 'date', mapping: 'OnkoTLink_begDate', dateFormat: 'd.m.Y' },
			{ name: 'OnkoTLink_endDate', type: 'date', mapping: 'OnkoTLink_endDate', dateFormat: 'd.m.Y' }
		],
		key: 'OnkoTLink_id',
		tableName: 'OnkoTLink'
	}),
	TumorStageLinkStore: new Ext.db.AdapterStore({
		autoLoad: false,
		dbFile: 'Promed.db',
		fields: [
			{ name: 'TumorStageLink_id', type: 'int', mapping: 'TumorStageLink_id' },
			{ name: 'TumorStage_id', type: 'int', mapping: 'TumorStage_id' },
			{ name: 'Diag_id', type: 'int', mapping: 'Diag_id' },
			{ name: 'TumorStage_fid', type: 'int', mapping: 'TumorStage_fid' },
			{ name: 'TumorStageLink_begDate', type: 'date', mapping: 'TumorStageLink_begDate', dateFormat: 'd.m.Y' },
			{ name: 'TumorStageLink_endDate', type: 'date', mapping: 'TumorStageLink_endDate', dateFormat: 'd.m.Y' }
		],
		key: 'TumorStageLink_id',
		tableName: 'TumorStageLink'
	}),
	TumorStageStore: new Ext.db.AdapterStore({
		autoLoad: false,
		dbFile: 'Promed.db',
		fields: [
			{ name: 'TumorStage_id', type: 'int', mapping: 'TumorStage_id' },
			{ name: 'TumorStage_Code', type: 'int', mapping: 'TumorStage_Code' },
			{ name: 'TumorStage_Name', type: 'string', mapping: 'TumorStage_Name' }
		],
		key: 'TumorStage_id',
		params: { object: 'TumorStage' },
		tableName: 'TumorStage'
	}),
	OnkoTStore: new Ext.db.AdapterStore({
		autoLoad: false,
		dbFile: 'Promed.db',
		fields: [
			{ name: 'OnkoT_id', type: 'int', mapping: 'OnkoT_id' },
			{ name: 'OnkoT_Code', type: 'int', mapping: 'OnkoT_Code' },
			{ name: 'OnkoT_Name', type: 'string', mapping: 'OnkoT_Name' }
		],
		key: 'OnkoT_id',
		params: { object: 'OnkoT' },
		tableName: 'OnkoT'
	}),
	OnkoNStore: new Ext.db.AdapterStore({
		autoLoad: false,
		dbFile: 'Promed.db',
		fields: [
			{ name: 'OnkoN_id', type: 'int', mapping: 'OnkoN_id' },
			{ name: 'OnkoN_Code', type: 'int', mapping: 'OnkoN_Code' },
			{ name: 'OnkoN_Name', type: 'string', mapping: 'OnkoN_Name' }
		],
		key: 'OnkoN_id',
		params: { object: 'OnkoN' },
		tableName: 'OnkoN'
	}),
	OnkoMStore: new Ext.db.AdapterStore({
		autoLoad: false,
		dbFile: 'Promed.db',
		fields: [
			{ name: 'OnkoM_id', type: 'int', mapping: 'OnkoM_id' },
			{ name: 'OnkoM_Code', type: 'int', mapping: 'OnkoM_Code' },
			{ name: 'OnkoM_Name', type: 'string', mapping: 'OnkoM_Name' }
		],
		key: 'OnkoM_id',
		params: { object: 'OnkoM' },
		tableName: 'OnkoM'
	}),
	printHtml: function(prms)
	{
		Ext.Ajax.request({
			url: '/?c=Template&m=getEvnForm',
			params: prms,
			callback: function(options, success, response) {
				if ( success ) {
					var result = Ext.util.JSON.decode(response.responseText);
					if(result.success && result.html){
						var id_salt = Math.random();
						var win_id = 'printEvent' + Math.floor(id_salt*10000);
						var win = window.open('', win_id);
						win.document.write('<html><head><title>Печатная форма</title><link href="/css/emk.css?'+ id_salt +'" rel="stylesheet" type="text/css" /></head><body id="rightEmkPanelPrint">'+ result.html +'</body></html>');
						var i, el;
						// нужно показать скрытые области для печати
						var printonly_list = Ext.query("div[class=printonly]",win.document);
						for(i=0; i < printonly_list.length; i++)
						{
							el = new Ext.Element(printonly_list[i]);
							el.setStyle({display: 'block'});
						}
						// нужно скрыть элементы управления
						var tb_list = Ext.query("*[class*=section-toolbar]",win.document);
						tb_list = tb_list.concat(Ext.query("*[class*=sectionlist-toolbar]",win.document));
						tb_list = tb_list.concat(Ext.query("*[class*=item-toolbar]",win.document));
						//tb_list = tb_list.concat(Ext.query("*[class=section-button]",win.document));
						//log(tb_list);
						for(i=0; i < tb_list.length; i++)
						{
							el = new Ext.Element(tb_list[i]);
							el.setStyle({display: 'none'});
						}
						win.document.close();
					} else {
						Ext.Msg.alert(langs('Сообщение'), 'Ошибка при получении формы для печати');
 						return false;
					}
				}
			}
		});
	},
	initComponent: function() 
	{

		this.rightPanel = new Ext.Panel(
		{
			animCollapse: false,
			autoScroll: true,
			bodyStyle: 'background-color: #e3e3e3',
			floatable: false,			
			minSize: 400,
			region: 'center',
			id: 'rightEmkPanel',
			split: true,
			layoutConfig:
			{
				titleCollapse: true,
				animate: true,
				activeOnTop: false,
				style: 'border 0px'
			},
			items: 
			[{
				html: ''
			}]
		});
		
		Ext.apply(this.rightPanel,sw.Promed.viewHtmlForm);
		this.rightPanel.ownerWindow = this;
		var win = this;
		this.rightPanel.configActions = {
			PersonMorbusOnko: {
				print: {
					actionType: 'view',
					sectionCode: 'PersonMorbusOnko',
					handler: function(e, c, d) {
						var	data = {
								EvnPL_id: d.object_id,
								win: win,
								section: d.section_id,
								EvnOnkoNotifyNeglected_id: win.EvnOnkoNotifyNeglected_id,
								MorbusOnkoLeave_id: win.MorbusOnkoLeave_id,
								MorbusOnkoVizitPLDop_id: win.MorbusOnkoVizitPLDop_id,
								Morbus_id: win.Morbus_id
							},
							btnEl = Ext.get(d.object +'_'+d.object_id+'_print');
						data.callbackCostPrint = function() {
							win.reloadViewForm({
								section_code: d.object
								,section_id: d.object + '_' + d.object_id
								,object_key: d.object +'_id'
								,object_value: d.object_id
							});
						};
						sw.Promed.PersonMorbusOnkoHelper.Report.showPrintMenu(win.Person_id, data, btnEl);
					}
				},
				editPhoto: {
					actionType: 'edit',
					sectionCode: 'person_data',
					handler: function(e, c, d) {
						var params = {
							action: 'loadimage',
							//saveUrl: '/?c=PersonMediaData&m=uploadPersonPhoto',
							saveUrl: '/?c=pmMediaData&m=uploadPersonPhoto',
							enableFileDescription: false,
							saveParams: {Person_id: d.object_id},
							callback: function(data){
								if (data && data.person_thumbs_src)
								{
									document[('photo_person_'+ d.object_id)].src=data.person_thumbs_src +'?'+ Math.random();
								}
							}
						};
						getWnd('swFileUploadWindow').show(params);
					}
				},
				editPers: {
					actionType: 'edit',
					dblClick: true,
					sectionCode: 'person_data',
					handler: function(e, c, d) {
						var params = {
							callback: function(data){
								if (data && data.Person_id)
								{
									/*
									нужно обновить секцию person_data, но пока этого не сделать
									var reload_params = {
										section_code: d.object,
										view_section: 'subsection',
										object_key: 'Person_id',
										object_value: data.Person_id,
										parent_object_key: 'Person_id',
										parent_object_value: d.object_id,
										section_id: d.section_id
									};
									form.reloadViewForm(reload_params);
									*/
									// пока будем перезагружать всю сигн.информацию
									win.loadNodeViewForm();
								}
							}
						};
						win.openForm('swPersonEditWindow','XXX_id',params,'edit',langs('Редактирование персональных данных пациента'));
					}
				},
				printMedCard: {
					actionType: 'view',
					sectionCode: 'person_data', 
					handler: function(e, c, d) {
						var data = win.rightPanel.getObjectData('PersonMorbusOnko',d.object_id);
						if (getRegionNick() =='ufa'){
							printMedCard4Ufa(data.PersonCard_id);
							return;
						}
						if(getRegionNick().inlist([ 'buryatiya', 'astra', 'perm', 'ekb', 'pskov', 'krym', 'khak', 'kaluga' ])){
							var PersonCard = 0;
							if(!Ext.isEmpty(data.PersonCard_id)){
								var PersonCard = data.PersonCard_id;
							}
							printBirt({
		                        'Report_FileName': 'pan_PersonCard_f025u.rptdesign',
		                        'Report_Params': '&paramPerson=' + data.Person_id + '&paramPersonCard=' + PersonCard + '&paramLpu=' + getLpuIdForPrint(),
		                        'Report_Format': 'pdf'
		                    });
						} else {
							Ext.Ajax.request(
							{
								url : '/?c=PersonCard&m=printMedCard',
								params : 
								{
									PersonCard_id: data.PersonCard_id,
									Person_id: data.Person_id
								},
								callback: function(options, success, response)
								{
									if ( success ) {
								        var response_obj = Ext.util.JSON.decode(response.responseText);
										openNewWindow(response_obj.result);
									}
								}
							});
						}
					}.createDelegate(this)	
				},				
				editAttach: {
					actionType: 'edit',
					sectionCode: 'person_data',
					handler: function(e, c, d) {
						var params = {
							callback: Ext.emptyFn, // почему-то в форме swPersonCardHistoryWindow вызывается только при нажатии на кн. "помощь"
							onHide: function(data){
								// нужно обновить секцию person_data, пока будем перезагружать всю сигн.информацию
								win.loadNodeViewForm();
							}
						};
						win.openForm('swPersonCardHistoryWindow','XXX_id',params,'edit',langs('История прикрепления'));
					}
				}
			},
			MorbusOnko: {
				copyEvnUsluga: {
					actionType: 'edit',
					sectionCode: 'MorbusOnko',
					handler: function(e, c, d) {
						var Evn_id = d.object_id.split('_')[0];
						var Morbus_id = d.object_id.split('_')[1];
						getWnd('swEvnUslugaCopyWindow').show({
							Evn_id: Evn_id,
							Morbus_id: Morbus_id,
							callback: function() {
								var reload_params = {
									section_code: d.object,
									object_key: 'Morbus_id',
									object_value: Morbus_id,
									parent_object_key: 'MorbusOnko_pid',
									parent_object_value: Evn_id,
									section_id: d.object + '_' + d.object_id
								};
								if(!Ext.isEmpty(win.EvnDiagPLSop_id)) {
									reload_params.EvnDiagPLSop_id = win.EvnDiagPLSop_id;
								}
								win.rightPanel.reloadViewForm(reload_params);
							}
						});
					}
				},
				addConf: {
					actionType: 'edit',
					sectionCode: 'MorbusOnko',
					handler: function(e, c, d) {
						var count = 1;
						var exists = [];
						var not_exists = [];
						for(var i=2;i<8;i++){
							var el = document.getElementById('MorbusOnko_'+d.object_id+'_inputOnkoDiagConfType'+i);
							if(el){
								count++;
								exists.push(i);
							} else {
								not_exists.push(i);
							}
						}
						if(count == 6){
							return false;
						}
						if(not_exists.length>0){
							var index = not_exists[0];
							// получение максимального индекса элемента на странице (чтобы вставить после последнего элемента)
							var el_index = 0;
							if(exists.length > 0){
								for(var a=0;a<exists.length;a++){
									var sa = document.getElementById('MorbusOnko_'+d.object_id+'_removeOnkoDiagConfType'+exists[a]);
									if(sa){
										var sa_i = sa.getAttribute('el-index');
										sa_i = parseInt(sa_i);
										if(sa_i>el_index){
											el_index = sa_i;
										}
									}
								}
							}
							el_index++;
						} else {
							return false;
						}
						var el = document.getElementById('MorbusOnko_'+d.object_id+'_addConf');
						var div = document.createElement('div');
						div.className = "data-row-container";
						var html = '<div class="data-row">'+
						'Метод подтверждения диагноза:'+
						'<span id="MorbusOnko_'+d.object_id+'_inputOnkoDiagConfType'+index+'" class="value link" dataid="" onclick="Ext.getCmp(\'MorbusOnkoWindow\').createMorbusOnkoHtmlForm(\'OnkoDiagConfType'+index+'\', {object_id:\''+d.object_id+'\'})" style="display: inline;">'+
						'<span class="empty">Не указано</span>'+
						'</span>'+
						'</div>'+
						'<div id="MorbusOnko_'+d.object_id+'_inputareaOnkoDiagConfType'+index+'" class="input-area" style="display: none;"></div>'+
						'<a id="MorbusOnko_'+d.object_id+'_removeOnkoDiagConfType'+index+'" el-index="'+el_index+'" onclick="Ext.getCmp(\'MorbusOnkoWindow\').createMorbusOnkoHtmlForm(\'OnkoDiagConfType'+index+'\', {object_id:\''+d.object_id+'\',remove:1})" class="button icon icon-delete16" style="margin-left:5px" title="Удалить метод подтверждения"><span></span></a>';
						div.innerHTML = html;
						if(exists.length == 0){
							el.parentNode.insertBefore(div, el.nextSibling);
						} else {
							var ind = 2;
							var el_ind = 0;
							// находим элемент с максимальным индексом и вставляем после него
							for (var i = 0; i < exists.length ; i++) {
								var sel = document.getElementById('MorbusOnko_'+d.object_id+'_removeOnkoDiagConfType'+exists[i]);
								if(sel){
									var sel_i = sel.getAttribute('el-index');
									sel_i = parseInt(sel_i);
									if(sel_i>el_ind){
										el_ind = sel_i;
										ind = exists[i];
									}
								}
							}
							el2 = document.getElementById('MorbusOnko_'+d.object_id+'_removeOnkoDiagConfType'+ind);
							el2 = el2.parentNode;
							el2.parentNode.insertBefore(div, el2.nextSibling);
						}
					}
				},
				toggleDisplayDiag: {
					actionType: 'view',
					sectionCode: 'MorbusOnko',
					handler: function(e, c, d) {
						var id = 'MorbusOnkoDiag_'+ d.object_id;
						win.rightPanel.toggleDisplay(id,Ext.get(id).isDisplayed());
					}
				},
				toggleDisplayHisto: {
					actionType: 'view',
					sectionCode: 'MorbusOnko',
					handler: function(e, c, d) {
						var id = 'MorbusOnkoHisto_'+ d.object_id;
						win.rightPanel.toggleDisplay(id,Ext.get(id).isDisplayed());
					}
				},
				toggleDisplayMorfo: {
					actionType: 'view',
					sectionCode: 'MorbusOnko',
					handler: function(e, c, d) {
						var id = 'MorbusOnkoMorfo_'+ d.object_id;
						win.rightPanel.toggleDisplay(id,Ext.get(id).isDisplayed());
					}
				},
				toggleDisplayMeta: {
					actionType: 'view',
					sectionCode: 'MorbusOnko',
					handler: function(e, c, d) {
						var id = 'MorbusOnkoMeta_'+ d.object_id;
						win.rightPanel.toggleDisplay(id,Ext.get(id).isDisplayed());
					}
				},
				toggleDisplayConfirm: {
					actionType: 'view',
					sectionCode: 'MorbusOnko',
					handler: function(e, c, d) {
						var id = 'MorbusOnkoConfirm_'+ d.object_id;
						win.rightPanel.toggleDisplay(id,Ext.get(id).isDisplayed());
					}
				},
				toggleDisplayControl: {
					actionType: 'view',
					sectionCode: 'MorbusOnko',
					handler: function(e, c, d) {
						var id = 'MorbusOnkoControl_'+ d.object_id;
						win.rightPanel.toggleDisplay(id,Ext.get(id).isDisplayed());
					}
				},
				inputOnkoTreatment: {actionType: 'edit', sectionCode: 'MorbusOnko', handler: function(e, c, d) { win.createMorbusOnkoHtmlForm('OnkoTreatment', d);}},
				inputfirstSignDT: { actionType: 'edit', sectionCode: 'MorbusOnko', handler: function(e, c, d) { win.createMorbusOnkoHtmlForm('firstSignDT', d); }},
				inputfirstVizitDT: { actionType: 'edit', sectionCode: 'MorbusOnko', handler: function(e, c, d) { win.createMorbusOnkoHtmlForm('firstVizitDT', d); }},
				inputsetDiagDT: { actionType: 'edit', sectionCode: 'MorbusOnko', handler: function(e, c, d) { win.createMorbusOnkoHtmlForm('setDiagDT', d); }},
                inputNumHisto: { actionType: 'edit', sectionCode: 'MorbusOnko', handler: function(e, c, d) { win.createMorbusOnkoHtmlForm('NumHisto', d); }},
				inputIsMainTumor: { actionType: 'edit', sectionCode: 'MorbusOnko', handler: function(e, c, d) { win.createMorbusOnkoHtmlForm('IsMainTumor', d); }},
				inputIsTumorDepoUnknown: { actionType: 'edit', sectionCode: 'MorbusOnko', handler: function(e, c, d) { win.createMorbusOnkoHtmlForm('IsTumorDepoUnknown', d); }},
				inputIsTumorDepoLympha: { actionType: 'edit', sectionCode: 'MorbusOnko', handler: function(e, c, d) { win.createMorbusOnkoHtmlForm('IsTumorDepoLympha', d); }},
				inputIsTumorDepoBones: { actionType: 'edit', sectionCode: 'MorbusOnko', handler: function(e, c, d) { win.createMorbusOnkoHtmlForm('IsTumorDepoBones', d); }},
				inputIsTumorDepoLiver: { actionType: 'edit', sectionCode: 'MorbusOnko', handler: function(e, c, d) { win.createMorbusOnkoHtmlForm('IsTumorDepoLiver', d); }},
				inputIsTumorDepoLungs: { actionType: 'edit', sectionCode: 'MorbusOnko', handler: function(e, c, d) { win.createMorbusOnkoHtmlForm('IsTumorDepoLungs', d); }},
				inputIsTumorDepoBrain: { actionType: 'edit', sectionCode: 'MorbusOnko', handler: function(e, c, d) { win.createMorbusOnkoHtmlForm('IsTumorDepoBrain', d); }},
				inputIsTumorDepoSkin: { actionType: 'edit', sectionCode: 'MorbusOnko', handler: function(e, c, d) { win.createMorbusOnkoHtmlForm('IsTumorDepoSkin', d); }},
				inputIsTumorDepoKidney: { actionType: 'edit', sectionCode: 'MorbusOnko', handler: function(e, c, d) { win.createMorbusOnkoHtmlForm('IsTumorDepoKidney', d); }},
				inputIsTumorDepoOvary: { actionType: 'edit', sectionCode: 'MorbusOnko', handler: function(e, c, d) { win.createMorbusOnkoHtmlForm('IsTumorDepoOvary', d); }},
				inputIsTumorDepoPerito: { actionType: 'edit', sectionCode: 'MorbusOnko', handler: function(e, c, d) { win.createMorbusOnkoHtmlForm('IsTumorDepoPerito', d); }},
				inputIsTumorDepoMarrow: { actionType: 'edit', sectionCode: 'MorbusOnko', handler: function(e, c, d) { win.createMorbusOnkoHtmlForm('IsTumorDepoMarrow', d); }},
				inputIsTumorDepoOther: { actionType: 'edit', sectionCode: 'MorbusOnko', handler: function(e, c, d) { win.createMorbusOnkoHtmlForm('IsTumorDepoOther', d); }},
				inputIsTumorDepoMulti: { actionType: 'edit', sectionCode: 'MorbusOnko', handler: function(e, c, d) { win.createMorbusOnkoHtmlForm('IsTumorDepoMulti', d); }},
				inputTumorStage: { actionType: 'edit', sectionCode: 'MorbusOnko', handler: function(e, c, d) { win.createMorbusOnkoHtmlForm('TumorStage', d); }},
				inputTumorStageF: { actionType: 'edit', sectionCode: 'MorbusOnko', handler: function(e, c, d) { win.createMorbusOnkoHtmlForm('TumorStageF', d); }},
				inputTumorPrimaryMultipleType: { actionType: 'edit', sectionCode: 'MorbusOnko', handler: function(e, c, d) { win.createMorbusOnkoHtmlForm('TumorPrimaryMultipleType', d); }},
				inputOnkoLesionSide: { actionType: 'edit', sectionCode: 'MorbusOnko', handler: function(e, c, d) { win.createMorbusOnkoHtmlForm('OnkoLesionSide', d); }},
				inputOnkoT: { actionType: 'edit', sectionCode: 'MorbusOnko', handler: function(e, c, d) { win.createMorbusOnkoHtmlForm('OnkoT', d); }},
				inputOnkoN: { actionType: 'edit', sectionCode: 'MorbusOnko', handler: function(e, c, d) { win.createMorbusOnkoHtmlForm('OnkoN', d); }},
				inputOnkoM: { actionType: 'edit', sectionCode: 'MorbusOnko', handler: function(e, c, d) { win.createMorbusOnkoHtmlForm('OnkoM', d); }},
				inputOnkoTF: { actionType: 'edit', sectionCode: 'MorbusOnko', handler: function(e, c, d) { win.createMorbusOnkoHtmlForm('OnkoTF', d); }},
				inputOnkoNF: { actionType: 'edit', sectionCode: 'MorbusOnko', handler: function(e, c, d) { win.createMorbusOnkoHtmlForm('OnkoNF', d); }},
				inputOnkoMF: { actionType: 'edit', sectionCode: 'MorbusOnko', handler: function(e, c, d) { win.createMorbusOnkoHtmlForm('OnkoMF', d); }},
				inputLpu: { actionType: 'edit', sectionCode: 'MorbusOnko', handler: function(e, c, d) { win.createMorbusOnkoHtmlForm('Lpu', d); }},
				inputOnkoRegType: { actionType: 'edit', sectionCode: 'MorbusOnko', handler: function(e, c, d) { win.createMorbusOnkoHtmlForm('OnkoRegType', d); }},
				inputOnkoRegOutType: { actionType: 'edit', sectionCode: 'MorbusOnko', handler: function(e, c, d) { win.createMorbusOnkoHtmlForm('OnkoRegOutType', d); }},
				inputTumorCircumIdentType: { actionType: 'edit', sectionCode: 'MorbusOnko', handler: function(e, c, d) { win.createMorbusOnkoHtmlForm('TumorCircumIdentType', d); }},
				inputOnkoLateDiagCause: {actionType: 'edit', sectionCode: 'MorbusOnko', handler: function(e, c, d) {win.createMorbusOnkoHtmlForm('OnkoLateDiagCause', d);}},
                inputAutopsyPerformType: { actionType: 'edit', sectionCode: 'MorbusOnko', handler: function(e, c, d) { win.createMorbusOnkoHtmlForm('AutopsyPerformType', d); }},
                inputTumorAutopsyResultType: { actionType: 'edit', sectionCode: 'MorbusOnko', handler: function(e, c, d) { win.createMorbusOnkoHtmlForm('TumorAutopsyResultType', d); }},

                inputOnkoDiag: { actionType: 'edit', sectionCode: 'MorbusOnko', handler: function(e, c, d) { win.createMorbusOnkoHtmlForm('OnkoDiag', d); }},
                inputMorbusBaseSetDT: { actionType: 'edit', sectionCode: 'MorbusOnko', handler: function(e, c, d) { win.createMorbusOnkoHtmlForm('MorbusBaseSetDT', d); }},
                inputMorbusBaseDisDT: { actionType: 'edit', sectionCode: 'MorbusOnko', handler: function(e, c, d) { win.createMorbusOnkoHtmlForm('MorbusBaseDisDT', d); }},
                inputMorbusOnkoBaseDeadDT: { actionType: 'edit', sectionCode: 'MorbusOnko', handler: function(e, c, d) { win.createMorbusOnkoHtmlForm('MorbusOnkoBaseDeadDT', d); }},
                inputNumCard: { actionType: 'edit', sectionCode: 'MorbusOnko', handler: function(e, c, d) { win.createMorbusOnkoHtmlForm('NumCard', d); }},
                inputMorbusOnkoHistDT: {actionType: 'edit', sectionCode: 'MorbusOnko', handler: function(e, c, d) {d.remove = true;win.createMorbusOnkoHtmlForm('MorbusOnkoHistDT', d);}},
                inputOnkoPostType: { actionType: 'edit', sectionCode: 'MorbusOnko', handler: function(e, c, d) { win.createMorbusOnkoHtmlForm('OnkoPostType', d); }},
                inputOnkoStatusYearEndType: { actionType: 'edit', sectionCode: 'MorbusOnko', handler: function(e, c, d) { win.createMorbusOnkoHtmlForm('OnkoStatusYearEndType', d); }},
                inputOnkoInvalidType:{actionType: 'edit', sectionCode: 'MorbusOnko', handler: function(e, c, d) {win.createMorbusOnkoHtmlForm('OnkoInvalidType', d);}},
                inputDiagDead: { actionType: 'edit', sectionCode: 'MorbusOnko', handler: function(e, c, d) { win.createMorbusOnkoHtmlForm('DiagDead', d); }},
				saveDiag: { actionType: 'edit', sectionCode: 'MorbusOnko', handler: function(e, c, d) { win.submitMorbusOnkoHtmlForm('saveDiag',d); } },
				inputEndDiag: {actionType: 'edit', sectionCode: 'MorbusOnko', handler: function(e, c, d) {win.createMorbusOnkoHtmlForm('EndDiag', d);}},
                inputOnkoVariance: {actionType: 'edit', sectionCode: 'MorbusOnko', handler: function(e, c, d) {win.createMorbusOnkoHtmlForm('OnkoVariance', d);}},
                inputOnkoRiskGroup: {actionType: 'edit', sectionCode: 'MorbusOnko', handler: function(e, c, d) {win.createMorbusOnkoHtmlForm('OnkoRiskGroup', d);}},
                inputOnkoResistance: {actionType: 'edit', sectionCode: 'MorbusOnko', handler: function(e, c, d) {win.createMorbusOnkoHtmlForm('OnkoResistance', d);}},
                inputOnkoStatusBegType: {actionType: 'edit', sectionCode: 'MorbusOnko', handler: function(e, c, d) {win.createMorbusOnkoHtmlForm('OnkoStatusBegType', d);}},
                inputHistologicReasonType: {actionType: 'edit', sectionCode: 'MorbusOnko', handler: function(e, c, d) {win.createMorbusOnkoHtmlForm('HistologicReasonType', d);}}
			},
			MorbusOnkoBasePersonState: {
				edit: {
					actionType: 'edit',
					sectionCode: 'MorbusOnkoBasePersonState',
					dblClick: true,
					handler: function(e, c, d) {
						win.openMorbusOnkoSpecificForm({action: 'edit',object: 'MorbusOnkoBasePersonState', eldata: d});
					}
				},
				'delete': {
					actionType: 'edit',
					sectionCode: 'MorbusOnkoBasePersonState',
					handler: function(e, c, d) {
						win.deleteEvent('MorbusOnkoBasePersonState',d);
					}
				},
				add: {
					actionType: 'view',
					sectionCode: 'MorbusOnkoBasePersonStateList',
					handler: function(e, c, d) {
						win.openMorbusOnkoSpecificForm({action: 'add',object: 'MorbusOnkoBasePersonState', eldata: d});
					}
				},
				print: {
					actionType: 'view',
					sectionCode: 'MorbusOnkoBasePersonStateList',
					handler: function(e, c, d) {
						win.rightPanel.printHtml(d.section_id);
					}
				},
				toggleDisplay: {
					actionType: 'view',
					sectionCode: 'MorbusOnkoBasePersonStateList',
					handler: function(e, c, d) {
						var id = 'MorbusOnkoBasePersonStateTable_'+ d.object_id;
						win.rightPanel.toggleDisplay(id,Ext.get(id).isDisplayed());
					}
				}
			},
			MorbusOnkoBasePS: {
				edit: {
					actionType: 'edit',
					sectionCode: 'MorbusOnkoBasePS',
					dblClick: true,
					handler: function(e, c, d) {
						win.openMorbusOnkoSpecificForm({action: 'edit',object: 'MorbusOnkoBasePS', eldata: d});
					}
				},
				'delete': {
					actionType: 'edit',
					sectionCode: 'MorbusOnkoBasePS',
					handler: function(e, c, d) {
						win.deleteEvent('MorbusOnkoBasePS',d);
					}
				},
				add: {
					actionType: 'view',
					sectionCode: 'MorbusOnkoBasePSList',
					handler: function(e, c, d) {
						win.openMorbusOnkoSpecificForm({action: 'add',object: 'MorbusOnkoBasePS', eldata: d});
					}
				},
				print: {
					actionType: 'view',
					sectionCode: 'MorbusOnkoBasePSList',
					handler: function(e, c, d) {
						win.rightPanel.printHtml(d.section_id);
					}
				},
				toggleDisplay: {
					actionType: 'view',
					sectionCode: 'MorbusOnkoBasePSList',
					handler: function(e, c, d) {
						var id = 'MorbusOnkoBasePSTable_'+ d.object_id;
						win.rightPanel.toggleDisplay(id,Ext.get(id).isDisplayed());
					}
				}
			},
			MorbusOnkoEvnNotify: {
                toggleDisplay: {
					actionType: 'view',
					sectionCode: 'MorbusOnkoEvnNotifyList',
					handler: function(e, c, d) {
						var id = 'MorbusOnkoEvnNotifyTable_'+ d.object_id;
						win.rightPanel.toggleDisplay(id,Ext.get(id).isDisplayed());
					}
				},
				addEvnOnkoNotify: {
                    actionType: 'edit',
                    sectionCode: 'MorbusOnkoEvnNotifyList',
                    handler: function(e, c, d) {
                    	var Evn_id = d.object_id.split('_')[0];
                    	var Diag_id = Ext.select('#MorbusOnkoWindow [id*="_DiagValue"]').item(0).dom.attributes.dataid.value;

                    	checkEvnNotify({
                    		Evn_id: Evn_id,
                    		EvnDiagPLSop_id: win.EvnDiagPLSop_id || null,
                    		MorbusType_SysNick: 'onko',
                    		callback: function(success) {
                    			var notifyForm = Ext.query('[id*="MorbusOnkoEvnNotify"]')[0];
								var MorbusOnko_pid = notifyForm.id.split('_')[1];
								var MorbusOnkoEvnNotify_id = notifyForm.id.split('_')[2];
								var params = { 
									section_code: 'MorbusOnkoEvnNotify',
									section_id: notifyForm.id,
									object_key: 'MorbusOnkoEvnNotify_id', 
									object_value: MorbusOnkoEvnNotify_id,
									parent_object_key: 'Morbus_id', 
									parent_object_value: MorbusOnkoEvnNotify_id,
									param_name: 'MorbusOnko_pid',
									param_value: MorbusOnko_pid,
								};
								win.rightPanel.reloadViewForm(params);
                    		}
                    	});
                    }
                },
                print: {
                	actionType: 'view',
                	sectionCode: 'MorbusOnkoEvnNotifyList',
                	handler: function(e, c, d) {
                		var object_id, EvnOnkoNotify_id;
						object_id = d.object_id.split('_')[0];
						var EvnOnkoNotifys = Ext.query('[id*=MorbusOnkoEvnNotify_' + object_id + '] td');
						EvnOnkoNotify_id = (Ext.query('[id*=MorbusOnkoEvnNotify_' + object_id + ']')[0]).id.split('_')[2];
						EvnOnkoNotifys.forEach(function(el){
							if(!el.innerHTML)
								EvnOnkoNotify_id = Ext.get(el).parent().id.split('_')[2];
						});

						printBirt({
							'Report_FileName': 'OnkoNotify.rptdesign',
							'Report_Params': '&paramEvnOnkoNotify=' + EvnOnkoNotify_id,
							'Report_Format': 'pdf'
						});
                	}
                }
			},
			MorbusOnkoPersonDisp: {
                toggleDisplay: {
					actionType: 'view',
					sectionCode: 'MorbusOnkoPersonDispList',
					handler: function(e, c, d) {
						var id = 'MorbusOnkoPersonDispTable_'+ d.object_id;
						win.rightPanel.toggleDisplay(id,Ext.get(id).isDisplayed());
					}
				},
				add: {
					actionType: 'edit',
					sectionCode: 'MorbusOnkoPersonDispList',
					handler: function(e, c, d) {
						win.openMorbusOnkoSpecificForm({action: 'add',object: 'MorbusOnkoPersonDisp', eldata: d});
					}
				},
				'select': {
					actionType: 'edit',
					sectionCode: 'MorbusOnkoPersonDispList',
					handler: function(e, c, d) {
						win.openMorbusOnkoSpecificForm({action: 'select',object: 'MorbusOnkoPersonDisp', eldata: d});
					}
				},
				edit: {
					actionType: 'edit',
					sectionCode: 'MorbusOnkoPersonDisp',
					dblClick: true,
					handler: function(e, c, d) {
						win.openMorbusOnkoSpecificForm({action: 'edit',object: 'MorbusOnkoPersonDisp', eldata: d});
					}
				},
				print: {
					actionType: 'edit',
					sectionCode: 'MorbusOnkoPersonDisp',
					handler: function(e, c, d) {
						var disparr = d.object_id.split('_');
						var paramPersonDisp = disparr[1];
						printBirt({
							'Report_FileName': 'PersonDispCard.rptdesign',
							'Report_Params': '&paramPersonDisp=' + paramPersonDisp,
							'Report_Format': 'pdf'
						});
					}
				}
			},
			MorbusOnkoSopDiag: {
				edit: {
					actionType: 'edit',
					sectionCode: 'MorbusOnkoSopDiag',
					dblClick: true,
					handler: function(e, c, d) {
						win.openMorbusOnkoSpecificForm({action: 'edit',object: 'MorbusOnkoSopDiag', eldata: d});
					}
				},
				'delete': {
					actionType: 'edit',
					sectionCode: 'MorbusOnkoSopDiag',
					handler: function(e, c, d) {
						win.deleteEvent('MorbusOnkoSopDiag',d);
					}
				},
				add: {
					actionType: 'edit',
					sectionCode: 'MorbusOnkoSopDiagList',
					handler: function(e, c, d) {
						win.openMorbusOnkoSpecificForm({action: 'add',object: 'MorbusOnkoSopDiag', eldata: d});
					}
				},
				toggleDisplay: {
					actionType: 'view',
					sectionCode: 'MorbusOnkoSopDiagList',
					handler: function(e, c, d) {
						var id = 'MorbusOnkoSopDiagTable_'+ d.object_id;
						win.toggleDisplay(id,Ext.get(id).isDisplayed());
					}
				}
			},
			OnkoConsult: {
				edit: {
					actionType: 'edit',
					sectionCode: 'OnkoConsult',
					dblClick: true,
					handler: function(e, c, d) {
						win.openMorbusOnkoSpecificForm({action: 'edit',object: 'OnkoConsult', eldata: d});
					}
				},
				'delete': {
					actionType: 'edit',
					sectionCode: 'OnkoConsult',
					handler: function(e, c, d) {
						win.deleteEvent('OnkoConsult',d);
					}
				},
				add: {
					actionType: 'edit',
					sectionCode: 'OnkoConsultList',
					handler: function(e, c, d) {
						win.openMorbusOnkoSpecificForm({action: 'add',object: 'OnkoConsult', eldata: d});
					}
				},
				toggleDisplay: {
					actionType: 'view',
					sectionCode: 'OnkoConsultList',
					handler: function(e, c, d) {
						var id = 'OnkoConsultTable_'+ d.object_id;
						win.toggleDisplay(id,Ext.get(id).isDisplayed());
					}
				}
			},
			MorbusOnkoDrug: {
				add: {
					actionType: 'edit',
					sectionCode: 'MorbusOnkoDrugList',
					handler: function(e, c, d) {
						win.openMorbusOnkoSpecificForm({action: 'add', object: 'MorbusOnkoDrug', eldata: d});
					}
				},
				edit: {
					actionType: 'edit',
					sectionCode: 'MorbusOnkoDrug',
					dblClick: true,
					handler: function(e, c, d) {
						win.openMorbusOnkoSpecificForm({action: 'edit', object: 'MorbusOnkoDrug', eldata: d});
					}
				},
				view: {
					actionType: 'view',
					sectionCode: 'MorbusOnkoDrug',
					dblClick: true,
					handler: function(e, c, d) {
						win.openMorbusOnkoSpecificForm({action: 'view', object: 'MorbusOnkoDrug', eldata: d});
					}
				},
				'delete': {
					actionType: 'edit',
					sectionCode: 'MorbusOnkoDrug',
					handler: function(e, c, d) {
						win.deleteEvent('MorbusOnkoDrug', d);
					}
				},
				toggleDisplay: {
					actionType: 'view',
					sectionCode: 'MorbusOnkoDrugList',
					handler: function(e, c, d) {
						var id = 'MorbusOnkoDrugTable_'+ d.object_id;
						win.toggleDisplay(id,Ext.get(id).isDisplayed());
					}
				},
				print: {
					actionType: 'view',
					sectionCode: 'MorbusOnkoDrugList',
					handler: function(e, c, d) {
						win.rightPanel.printHtml(d.section_id);
					}
				}
			},
			MorbusOnkoSpecTreat: {
				view: {
					actionType: 'view',
					sectionCode: 'MorbusOnkoSpecTreat',
					handler: function(e, c, d) {
						win.openMorbusOnkoSpecificForm({action: 'view',object: 'MorbusOnkoSpecTreat', eldata: d});
					}
				},
				edit: {
					actionType: 'edit',
					sectionCode: 'MorbusOnkoSpecTreat',
					dblClick: true,
					handler: function(e, c, d) {
						win.openMorbusOnkoSpecificForm({action: 'edit',object: 'MorbusOnkoSpecTreat', eldata: d});
					}
				},
				'delete': {
					actionType: 'edit',
					sectionCode: 'MorbusOnkoSpecTreat',
					handler: function(e, c, d) {
						win.deleteEvent('MorbusOnkoSpecTreat',d);
					}
				},
				add: {
					actionType: 'edit',
					sectionCode: 'MorbusOnkoSpecTreatList',
					handler: function(e, c, d) {
						win.openMorbusOnkoSpecificForm({action: 'add',object: 'MorbusOnkoSpecTreat', eldata: d});
					}
				},
				toggleDisplay: {
					actionType: 'view',
					sectionCode: 'MorbusOnkoSpecTreatList',
					handler: function(e, c, d) {
						var id = 'MorbusOnkoSpecTreatTable_'+ d.object_id;
						win.rightPanel.toggleDisplay(id,Ext.get(id).isDisplayed());
					}
				}
			},
			MorbusOnkoLink: {
				edit: {
					actionType: 'edit',
					sectionCode: 'MorbusOnkoLink',
					dblClick: true,
					handler: function(e, c, d) {
						win.openMorbusOnkoSpecificForm({action: 'edit',object: 'MorbusOnkoLink', eldata: d});
					}
				},
				'delete': {
					actionType: 'edit',
					sectionCode: 'MorbusOnkoLink',
					handler: function(e, c, d) {
						win.deleteEvent('MorbusOnkoLink',d);
					}
				},
				add: {
					actionType: 'edit',
					sectionCode: 'MorbusOnkoLinkList',
					handler: function(e, c, d) {
						win.openMorbusOnkoSpecificForm({action: 'add',object: 'MorbusOnkoLink', eldata: d});
					}
				},
				toggleDisplay: {
					actionType: 'view',
					sectionCode: 'MorbusOnkoLinkList',
					handler: function(e, c, d) {
						var id = 'MorbusOnkoLinkTable_'+ d.object_id;
						win.rightPanel.toggleDisplay(id,Ext.get(id).isDisplayed());
					}
				}
			},
			MorbusOnkoRefusal: {
				edit: {
					actionType: 'edit',
					sectionCode: 'MorbusOnkoRefusal',
					dblClick: true,
					handler: function(e, c, d) {
						win.openMorbusOnkoSpecificForm({action: 'edit',object: 'MorbusOnkoRefusal', eldata: d});
					}
				},
				'delete': {
					actionType: 'edit',
					sectionCode: 'MorbusOnkoRefusal',
					handler: function(e, c, d) {
						win.deleteEvent('MorbusOnkoRefusal',d);
					}
				},
				add: {
					actionType: 'edit',
					sectionCode: 'MorbusOnkoRefusalList',
					handler: function(e, c, d) {
						var me = this;

						if (me.refusalMenu) {
							me.refusalMenu.destroy();
							me.refusalMenu = null;
						}

						me.d = d;

						me.refusalMenu = new Ext.menu.Menu();

						me.refusalMenu.add({
							text: 'Отказ от лечения',
							handler: function() {
								win.openMorbusOnkoSpecificForm({action: 'add', object: 'MorbusOnkoRefusal', eldata: d, isRefusal: true});
							}
						});
						me.refusalMenu.add({
							text: 'Противопоказание к лечению',
							handler: function() {
								win.openMorbusOnkoSpecificForm({action: 'add', object: 'MorbusOnkoRefusal', eldata: d, isRefusal: false});
							}
						});

						var btnEl = Ext.get(d.object + '_' + d.object_id + '_add');
						me.refusalMenu.show(btnEl);
					}
				},
				toggleDisplay: {
					actionType: 'view',
					sectionCode: 'MorbusOnkoRefusalList',
					handler: function(e, c, d) {
						var id = 'MorbusOnkoRefusalTable_'+ d.object_id;
						win.toggleDisplay(id,Ext.get(id).isDisplayed());
					}
				}
			},
			MorbusOnkoRadTer: {
				view: {
					actionType: 'view',
					sectionCode: 'MorbusOnkoRadTer',
					handler: function(e, c, d) {
						win.openMorbusOnkoSpecificForm({action: 'view',object: 'MorbusOnkoRadTer', eldata: d});
					}
				},
				edit: {
					actionType: 'edit',
					sectionCode: 'MorbusOnkoRadTer',
					dblClick: true,
					handler: function(e, c, d) {
						win.openMorbusOnkoSpecificForm({action: 'edit',object: 'MorbusOnkoRadTer', eldata: d});
					}
				},
				'delete': {
					actionType: 'edit',
					sectionCode: 'MorbusOnkoRadTer',
					handler: function(e, c, d) {
						win.deleteEvent('MorbusOnkoRadTer',d);
					}
				},
				add: {
					actionType: 'edit',
					sectionCode: 'MorbusOnkoRadTerList',
					handler: function(e, c, d) {
						win.openMorbusOnkoSpecificForm({action: 'add',object: 'MorbusOnkoRadTer', eldata: d});
					}
				},
				print: {
					actionType: 'view',
					sectionCode: 'MorbusOnkoRadTerList',
					handler: function(e, c, d) {
						win.rightPanel.printHtml(d.section_id);
					}
				},
				toggleDisplay: {
					actionType: 'view',
					sectionCode: 'MorbusOnkoRadTerList',
					handler: function(e, c, d) {
						var id = 'MorbusOnkoRadTerTable_'+ d.object_id;
						win.rightPanel.toggleDisplay(id,Ext.get(id).isDisplayed());
					}
				}
			},
			MorbusOnkoHirTer: {
				view: {
					actionType: 'view',
					sectionCode: 'MorbusOnkoHirTer',
					handler: function(e, c, d) {
						win.openMorbusOnkoSpecificForm({action: 'view',object: 'MorbusOnkoHirTer', eldata: d});
					}
				},
				edit: {
					actionType: 'edit',
					sectionCode: 'MorbusOnkoHirTer',
					dblClick: true,
					handler: function(e, c, d) {
						win.openMorbusOnkoSpecificForm({action: 'edit',object: 'MorbusOnkoHirTer', eldata: d});
					}
				},
				'delete': {
					actionType: 'edit',
					sectionCode: 'MorbusOnkoHirTer',
					handler: function(e, c, d) {
						win.deleteEvent('MorbusOnkoHirTer',d);
					}
				},
				add: {
					actionType: 'edit',
					sectionCode: 'MorbusOnkoHirTerList',
					handler: function(e, c, d) {
						win.openMorbusOnkoSpecificForm({action: 'add',object: 'MorbusOnkoHirTer', eldata: d});
					}
				},
				print: {
					actionType: 'view',
					sectionCode: 'MorbusOnkoHirTerList',
					handler: function(e, c, d) {
						win.rightPanel.printHtml(d.section_id);
					}
				},
				toggleDisplay: {
					actionType: 'view',
					sectionCode: 'MorbusOnkoHirTerList',
					handler: function(e, c, d) {
						var id = 'MorbusOnkoHirTerTable_'+ d.object_id;
						win.rightPanel.toggleDisplay(id,Ext.get(id).isDisplayed());
					}
				}
			},
			MorbusOnkoChemTer: {
				view: {
					actionType: 'view',
					sectionCode: 'MorbusOnkoChemTer',
					handler: function(e, c, d) {
						win.openMorbusOnkoSpecificForm({action: 'view',object: 'MorbusOnkoChemTer', eldata: d});
					}
				},
				edit: {
					actionType: 'edit',
					sectionCode: 'MorbusOnkoChemTer',
					dblClick: true,
					handler: function(e, c, d) {
						win.openMorbusOnkoSpecificForm({action: 'edit',object: 'MorbusOnkoChemTer', eldata: d});
					}
				},
				'delete': {
					actionType: 'edit',
					sectionCode: 'MorbusOnkoChemTer',
					handler: function(e, c, d) {
						win.deleteEvent('MorbusOnkoChemTer',d);
					}
				},
				add: {
					actionType: 'edit',
					sectionCode: 'MorbusOnkoChemTerList',
					handler: function(e, c, d) {
						win.openMorbusOnkoSpecificForm({action: 'add',object: 'MorbusOnkoChemTer', eldata: d});
					}
				},
				print: {
					actionType: 'view',
					sectionCode: 'MorbusOnkoChemTerList',
					handler: function(e, c, d) {
						win.rightPanel.printHtml(d.section_id);
					}
				},
				toggleDisplay: {
					actionType: 'view',
					sectionCode: 'MorbusOnkoChemTerList',
					handler: function(e, c, d) {
						var id = 'MorbusOnkoChemTerTable_'+ d.object_id;
						win.rightPanel.toggleDisplay(id,Ext.get(id).isDisplayed());
					}
				}
			},
			MorbusOnkoGormTer: {
				view: {
					actionType: 'view',
					sectionCode: 'MorbusOnkoGormTer',
					handler: function(e, c, d) {
						win.openMorbusOnkoSpecificForm({action: 'view',object: 'MorbusOnkoGormTer', eldata: d});
					}
				},
				edit: {
					actionType: 'edit',
					sectionCode: 'MorbusOnkoGormTer',
					dblClick: true,
					handler: function(e, c, d) {
						win.openMorbusOnkoSpecificForm({action: 'edit',object: 'MorbusOnkoGormTer', eldata: d});
					}
				},
				'delete': {
					actionType: 'edit',
					sectionCode: 'MorbusOnkoGormTer',
					handler: function(e, c, d) {
						win.deleteEvent('MorbusOnkoGormTer',d);
					}
				},
				add: {
					actionType: 'edit',
					sectionCode: 'MorbusOnkoGormTerList',
					handler: function(e, c, d) {
						win.openMorbusOnkoSpecificForm({action: 'add',object: 'MorbusOnkoGormTer', eldata: d});
					}
				},
				print: {
					actionType: 'view',
					sectionCode: 'MorbusOnkoGormTerList',
					handler: function(e, c, d) {
						win.rightPanel.printHtml(d.section_id);
					}
				},
				toggleDisplay: {
					actionType: 'view',
					sectionCode: 'MorbusOnkoGormTerList',
					handler: function(e, c, d) {
						var id = 'MorbusOnkoGormTerTable_'+ d.object_id;
						win.rightPanel.toggleDisplay(id,Ext.get(id).isDisplayed());
					}
				}
			},
			MorbusOnkoNonSpecTer: {
				view: {
					actionType: 'view',
					sectionCode: 'MorbusOnkoNonSpecTer',
					handler: function(e, c, d) {
						win.openMorbusOnkoSpecificForm({action: 'view',object: 'MorbusOnkoNonSpecTer', eldata: d});
					}
				},
				edit: {
					actionType: 'edit',
					sectionCode: 'MorbusOnkoNonSpecTer',
					dblClick: true,
					handler: function(e, c, d) {
						win.openMorbusOnkoSpecificForm({action: 'edit',object: 'MorbusOnkoNonSpecTer', eldata: d});
					}
				},
				'delete': {
					actionType: 'edit',
					sectionCode: 'MorbusOnkoNonSpecTer',
					handler: function(e, c, d) {
						win.deleteEvent('MorbusOnkoNonSpecTer',d);
					}
				},
				add: {
					actionType: 'edit',
					sectionCode: 'MorbusOnkoNonSpecTerList',
					handler: function(e, c, d) {
						win.openMorbusOnkoSpecificForm({action: 'add',object: 'MorbusOnkoNonSpecTer', eldata: d});
					}
				},
				print: {
					actionType: 'view',
					sectionCode: 'MorbusOnkoNonSpecTerList',
					handler: function(e, c, d) {
						win.rightPanel.printHtml(d.section_id);
					}
				},
				toggleDisplay: {
					actionType: 'view',
					sectionCode: 'MorbusOnkoNonSpecTerList',
					handler: function(e, c, d) {
						var id = 'MorbusOnkoNonSpecTerTable_'+ d.object_id;
						win.rightPanel.toggleDisplay(id,Ext.get(id).isDisplayed());
					}
				}
			}
		};
		
		Ext.apply(this, 
		{
			region: 'center',
			layout: 'border',
			buttons: 
			[{
				text: '-'
			},
			HelpButton(this),
			{
				handler: function() 
				{
					this.ownerCt.hide();
				},
				iconCls: 'cancel16',
				text: BTN_FRMCLOSE
			}],
			items: {
				autoScroll: true,
				bodyBorder: false,
				frame: false,
				xtype: 'form',
				region: 'center',
				layout: 'border',
				border: false,
				items: [this.rightPanel]
			}
		});
		sw.Promed.swMorbusOnkoWindow.superclass.initComponent.apply(this, arguments);
	}
});