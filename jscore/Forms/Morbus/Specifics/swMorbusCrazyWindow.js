/**
* swMorbusCrazyWindow - Форма просмотра записи регистра с типом «Психиатрия»
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2009-2010 Swan Ltd.
* @version      24.05.2012
* @prefix       MHW
*/

sw.Promed.swMorbusCrazyWindow = Ext.extend(sw.Promed.BaseForm, 
{
	width : 400,
	height : 400,
	modal: true,
	resizable: false,
	autoHeight: false,
	closeAction :'hide',
	autoScroll: true,
	border : false,
	plain : false,
	action: null,
	maximized: true,
	title: lang['zapis_registra'],
	listeners: {
		'beforehide': function(win) {
			if(win.changed_fields && Object.keys(win.changed_fields).length>0 && !win.inSubmitOnHide && !win.ignoreHideCheck){
				sw.swMsg.show({
					buttons: {yes: "Сохранить", no: "Не сохранять"},
					fn: function ( buttonId ) {
						if ( buttonId == 'yes' )
						{
							win.submitOnHide();
							win.onHide(true);
							win.hide();
						} else {
							var conf;
							for(var field_name in win.changed_fields) {
								conf = win.changed_fields[field_name];
								conf.elOutput.setDisplayed('inline');
								conf.elOutput.update(conf.outputValue);
								if(conf.type == 'id') conf.elOutput.setAttribute('dataid',conf.value);
								conf.elInputWrap.setDisplayed('none');
								conf.elInput.destroy();
								win.input_cmp_list[conf.elOutputId] = false;
							}
							win.changed_fields = {};
							win.isChange = true;
							win.ignoreHideCheck = true;
							win.hide();
						}
					},
					msg: 'Имеются несохраненные данные. Просто закрыть форму или сперва сохранить данные?',
					title: 'Подтверждение'
				});
				return false;
			}
		},
		'hide': function(win) {
			win.onHide(win.isChange);
		},
		'beforeShow': function(win) {
			if(!getWnd('swWorkPlaceMZSpecWindow').isVisible() && !isUserGroup('MIACSuperAdmin'))
			{
				if (String(getGlobalOptions().groups).indexOf('Crazy', 0) < 0)
				{
					if(!isUserGroup("NarkoRegistry")&&!isUserGroup("NarkoMORegistry")){
						sw.swMsg.alert('Сообщение', 'Форма "'+ win.title +'" доступна только для пользователей, с указанной группой «Регистр по психиатрии»');
						return false;
					}	
				}
			}
		}
	},
	createMorbusHtmlForm: function(name, el_data) {
		if(this.allowSpecificEdit == false) {
			return false;
		}
		var morbus_id = el_data.object_id.split('_')[1];
		var params = this.rightPanel.getObjectData('MorbusCrazy',morbus_id);
		if(typeof params != 'object') {
			return false;
		}
		var form = this;
		var cmp, ct, elinputid, eloutputid, config;
		var empty_value = '<span style="color: #666;">Не указано</span>';
		var onChange = function(conf){

			var save_tb1 = Ext.get('MorbusCrazy_'+el_data.object_id+'_toolbarDiag');
			var save_tb2 = Ext.get('MorbusCrazy_'+el_data.object_id+'_toolbarMorbusCrazyDynamicsObserv');
			var save_tb3 = Ext.get('MorbusCrazy_'+el_data.object_id+'_toolbarMorbusCrazyPerson');
			var save_tb4 = Ext.get('MorbusCrazy_'+el_data.object_id+'_toolbarMorbusCrazyDrug');
			switch(name){
				case 'PersonRegister_setDate': case 'Morbus_setDT': case 'Diag_nid': case 'Diag_sid': case 'CrazyResultDeseaseType':
					save_tb1.setDisplayed('block');
					break;
				case 'CrazyCauseEndSurveyType':
				case 'Morbus_disDT':
				case 'MorbusCrazy_CardEndDT':
					save_tb2.setDisplayed('block');
					break;
				case 'MorbusCrazyBase_LTMDayCount': case 'MorbusCrazyBase_HolidayDayCount': case 'MorbusCrazyBase_HolidayCount': case 'MorbusCrazyPerson_IsWowInvalid':
				case 'MorbusCrazyPerson_IsWowMember': case 'CrazyEducationType': case 'CrazyPerson_CompleteClassCount': case 'MorbusCrazyPerson_IsEducation':
				case 'CrazySourceLivelihoodType': case 'CrazyResideType': case 'CrazyResideConditionsType': case 'MorbusCrazyBase_firstDT':
				case 'MorbusCrazyPerson_IsConvictionBeforePsych': case 'MorbusCrazyBase_DeathDT': case 'CrazyDeathCauseType':
					save_tb3.setDisplayed('block');
					break;
				case 'MorbusCrazyBase_IsUseAlienDevice': case 'MorbusCrazyBase_IsLivingConsumDrug':
					save_tb4.setDisplayed('block');
					break;

			}

			if(!this.changed_fields) this.changed_fields = {};
			this.changed_fields[conf.field_name] = conf;
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

		if(!this.input_cmp_list) this.input_cmp_list = {};

		var getBaseConfig = function(options){
			return {
				hideLabel: true
				,renderTo: options.elInputId
				,listeners:
				{
					blur: function(f) {
                        if (f.disableBlurAction) {
                            return false;
                        }
						options.elInput = f;
						onCancel(options);
                        return true;
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
                        if (f.disableBlurAction) {
                            return false;
                        }
						if(options.type == 'date') {
							options.outputValue = (n)?n.format('d.m.Y'):empty_value;
							options.value = (n)?n.format('d.m.Y'):null;
						}
						if(options.type.inlist(['string','int'])) {
							options.outputValue = (n)?n:empty_value;
							options.value = n || null;
						}
						if(options.type == 'id') {
							if(options.name == 'Diag_nid' || options.name == 'Diag_sid'){
								if(n){
									if(f.getStore().getCount()>1){
										if(!(parseInt(n) > 0)) {
											var newn = n.split(' ');
											var newnn = f.getStore().findBy(function(recrd){
												return (recrd.get('Diag_Code') == newn[0]);
											});
											var rec = f.getStore().getAt(newnn);
										} else {
											var rec = f.getStore().getById(n);
										}
									} else {
										var rec = f.getStore().getAt(0);
									}
								} else {
									var rec = false;
								}
							} else {
								var rec = (n)?f.getStore().getById(n):false;
							}
							if(rec) {
								if(options.name == 'Diag_nid' || options.name == 'Diag_sid'){
									options.value = rec.get('Diag_id');
								} else {
									options.value = n;
								}
								if(options.codeField) {
									options.outputValue = rec.get(options.codeField) + ' ' + rec.get(f.displayField);
								} else {
									options.outputValue = rec.get(f.displayField);
								}
							} else {
								options.value = 0;
								options.outputValue = empty_value;
							}
						}
						options.elInput = f;
						if (n!=o) {
                            onChange(options);
                        }
                        return true;
					}
				}
			};
		};

		eloutputid = 'MorbusCrazy_'+ el_data.object_id +'_input'+name;
		elinputid = 'MorbusCrazy_'+ el_data.object_id +'_inputarea'+name;
		eloutput = Ext.get(eloutputid);
		ct = Ext.get(elinputid);

		switch(name){
			// даты
			case 'PersonRegister_setDate':
			case 'Morbus_disDT':
			case 'MorbusCrazy_CardEndDT':
			case 'Morbus_setDT'://дата начала заболевания
			case 'MorbusCrazyBase_firstDT'://дата первого обращения
			case 'MorbusCrazyBase_DeathDT'://Дата установления диагноза
				if(ct && !this.input_cmp_list[eloutputid]) {
					ct.setDisplayed('block');
					eloutput.setDisplayed('none');
					config = getBaseConfig({
						name: name
						,type: 'date'
						,field_name: name
						,elOutputId: eloutputid
						,elInputId: elinputid
						,elOutput: eloutput
						,outputValue: null
						,elInputWrap: ct
						,elInput: null
					});
					config.width = 90;
					cmp = new sw.Promed.SwDateField(config);
					cmp.focus(true, 500);
					this.input_cmp_list[eloutputid] = cmp;
				}
				break;
			case 'MorbusCrazyBase_LTMDayCount':
			case 'MorbusCrazyBase_HolidayDayCount':
			case 'MorbusCrazyBase_HolidayCount':
			case 'MorbusCrazyPerson_CompleteClassCount':

				if(ct && !this.input_cmp_list[eloutputid]) {
					ct.setDisplayed('block');
					eloutput.setDisplayed('none');
					config = getBaseConfig({
						name: name
						,type: 'int'
						,field_name: name
						,elOutputId: eloutputid
						,elInputId: elinputid
						,elOutput: eloutput
						,outputValue: empty_value
						,elInputWrap: ct
						,elInput: null
					});
					config.width = 100;
					config.maskRe = new RegExp("^[0-9]*$");
					config.allowDecimals = false;
					config.allowNegative = false;
					config.maxValue = (name.inlist(['MorbusCrazyBase_LTMDayCount','MorbusCrazyBase_HolidayDayCount']))?999:99;
					config.maxLength = (name.inlist(['MorbusCrazyBase_LTMDayCount','MorbusCrazyBase_HolidayDayCount']))?'3':'2';
					config.autoCreate = {tag: "input", size:14, maxLength: config.maxLength, autocomplete: "off"};
					cmp = new Ext.form.NumberField(config);
					cmp.focus(true, 500);
					this.input_cmp_list[eloutputid] = cmp;
				}
				break;
			case 'MorbusCrazyPerson_IsWowInvalid':
			case 'MorbusCrazyPerson_IsWowMember':
			case 'MorbusCrazyPerson_IsEducation':
			case 'MorbusCrazyPerson_IsConvictionBeforePsych':
			case 'MorbusCrazyBase_IsUseAlienDevice':
			case 'MorbusCrazyBase_IsLivingConsumDrug':
				if(ct && !this.input_cmp_list[eloutputid]) {
					ct.setDisplayed('block');
					eloutput.setDisplayed('none');
					config = getBaseConfig({
						name: name
						,type: 'id'
						,field_name: name
						,elOutputId: eloutputid
						,elInputId: elinputid
						,elOutput: eloutput
						,outputValue: empty_value
						,elInputWrap: ct
						,elInput: null
					});
					config.width = 70;
					config.comboSubject = 'YesNo';
					config.typeCode = 'int';
					config.autoLoad = true;
					cmp = new sw.Promed.SwCommonSprCombo(config);
					cmp.focus(true, 500);
					this.input_cmp_list[eloutputid] = cmp;
				}
				break;
			case 'CrazyResultDeseaseType':
			case 'CrazyDeathCauseType':
			case 'CrazyEducationType':
			case 'CrazySourceLivelihoodType':
			case 'CrazyResideType':
			case 'CrazyResideConditionsType':
			case 'CrazyCauseEndSurveyType':
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
					config.width = 250;
					//config.listWidth = 500;
					config.comboSubject = name;
					config.typeCode = 'int';
					config.autoLoad = true;
					cmp = new sw.Promed.SwCommonSprCombo(config);
					cmp.focus(true, 500);
					this.input_cmp_list[eloutputid] = cmp;
				}
				break;
			case 'Diag_nid': //
			case 'Diag_sid': //
				if(ct && !this.input_cmp_list[eloutputid]) {
					ct.setDisplayed('block');
					eloutput.setDisplayed('none');
					var config_data = {
						name: name
						,type: 'id'
						,field_name: name
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
					var change = Ext.apply(config.listeners.change);
					//delete config.listeners.change;
					cmp = new sw.Promed.SwDiagCombo(config);

					/*cmp.addListener('blur', function(f) {
						//debugger;
						config_data.elInput = f;
						onCancel(config_data);
					});*/

					/*cmp.addListener('select', function(combo, record, index) {
						if ( record ) {
							combo.setRawValue(record.get('Diag_Code') + " " + record.get('Diag_Name'));
						}
					});
					cmp.addListener('change', function(combo, nv, ov) {
						debugger;

						if (/[^[0-9]/.test(nv)) {
							if ( combo.getRawValue() == '' ) {
								combo.setValue('');
							} else {
								var store = combo.getStore();
								var val = combo.getDiagCode(combo.getRawValue().toString().substr(0, combo.countSymbolsCode));
								var yes = false;
								combo.getStore().each(function(r){
									if ( r.get('Diag_Code') == val )
									{
										this.setValue(r.get(this.valueField));
										//combo.fireEvent('change', combo, r.get(this.valueField), '');
										yes = true;
										return true;
									}
								}.createDelegate(combo));
								if (!yes) {
									this.setValue(null);
								}
							}
						}
					});
					cmp.addListener('change',change);*/


					var dataid = params[name];
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
		}
	},
	openMorbusCrazySpecificForm: function(options) {
		if(this.allowSpecificEdit == false) {
			return false;
		}
		if(!options.action || !options.object || !options.eldata) {
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
		 log('openMorbusCrazySpecificForm');
		 log(options);
		 */

		if(options.action == 'add') {
			object_id = (options.eldata.object_id.split('_').length > 1)?options.eldata.object_id.split('_')[1]:options.eldata.object_id;
			mhdata = options.mhdata || this.rightPanel.getObjectData('MorbusCrazy',object_id);
			if(!mhdata) {
				return false;
			}
		} else {
			object_id = (options.eldata.object_id.split('_').length > 1)?options.eldata.object_id.split('_')[1]:options.eldata.object_id;
			data = this.rightPanel.getObjectData(options.object,object_id);
			if(!data) {
				return false;
			}
			mhdata = options.mhdata || this.rightPanel.getObjectData('MorbusCrazy',data.MorbusCrazy_id);

			if(!mhdata) {
				return false;
			}
		}
		params.callback = function() {
			var reload_params = {
				section_code: options.object,
				object_key: options.object +'_id',
				object_value: object_id,
				parent_object_key: 'MorbusCrazy_id',
				parent_object_value: mhdata.MorbusCrazy_id,
				param_name: 'MorbusCrazy_pid',
				param_value: mhdata.MorbusCrazy_pid,
				section_id: options.object +'List_'+ mhdata.MorbusCrazy_pid +'_'+ mhdata.MorbusCrazy_id
			};
			this.rightPanel.reloadViewForm(reload_params);
		}.createDelegate(this);


		switch(options.object) {
			case 'MorbusCrazyDiag':
			case 'MorbusCrazyDynamicsObserv':
			case 'MorbusCrazyVizitCheck':
			case 'MorbusCrazyDynamicsState':
			case 'MorbusCrazyBasePS':
			case 'MorbusCrazyForceTreat':
			case 'MorbusCrazyPersonSurveyHIV':
			case 'MorbusCrazyNdOsvid':
			case 'MorbusCrazyPersonStick':
			case 'MorbusCrazyPersonSuicidalAttempt':
			case 'MorbusCrazyPersonSocDangerAct':
			case 'MorbusCrazyBaseDrugStart':
			case 'MorbusCrazyDrug':
			case 'MorbusCrazyDrugVolume':
			case 'MorbusCrazyBBK':
			case 'MorbusCrazyPersonInvalid':
				win_name = 'sw'+options.object+'Window';
				params.action = options.action;
				params.type = this.type;
				params[options.object+'_id'] = (params.action=='edit')?object_id:null;
				params.formParams = {MorbusCrazy_id: mhdata.MorbusCrazy_id, MorbusCrazyBase_id: mhdata.MorbusCrazyBase_id, MorbusCrazyPerson_id: mhdata.MorbusCrazyPerson_id, Person_id: this.Person_id, Evn_id: null};
				break;
			default:
				return false;
		}
		getWnd(win_name).show(params);
	},
	/**
	 * Сохраняет данные по специфике
	 * @param btn_name
	 * @param el_data
	 * @return {Boolean}
	 */
	submitMorbusCrazyHtmlForm: function(btn_name, el_data) {
		if(this.allowSpecificEdit == false) {
			return false;
		}

		var save_tb1 = Ext.get('MorbusCrazy_'+el_data.object_id+'_toolbarDiag');
		var save_tb2 = Ext.get('MorbusCrazy_'+el_data.object_id+'_toolbarMorbusCrazyDynamicsObserv');
		var save_tb3 = Ext.get('MorbusCrazy_'+el_data.object_id+'_toolbarMorbusCrazyPerson');
		var save_tb4 = Ext.get('MorbusCrazy_'+el_data.object_id+'_toolbarMorbusCrazyDrug');

		var params = this.rightPanel.getObjectData('MorbusCrazy',el_data.object_id.split('_')[1]);
		if(!params) {
			return false;
		}
		for(var field_name in this.changed_fields) {
			params[field_name] = this.changed_fields[field_name].value || '';
		}
		params['Evn_pid'] = this.EvnDiagPLStom_id || this.EvnVizitPL_id || this.EvnSection_id || null;
		if (this.EvnVizitPL_id) {
			params['Mode'] = 'evnvizitpl_viewform';
		} else {
			params['Mode'] = 'personregister_viewform';
		}
		var url = '/?c=MorbusCrazy&m=saveMorbusCrazy';
		var form = this;

		if(btn_name=='saveMorbusCrazyDynamicsObserv'){
			if(getRegionNick() == 'ufa'){
				var url = '/?c=MorbusCrazy&m=setCauseEndSurveyType';
				params.MorbusCrazy_DeRegDT = params['Morbus_disDT'];
				params.CrazyCauseEndSurveyType_id = params['CrazyCauseEndSurveyType_id'];
				params.MorbusCrazy_CardEndDT = params['MorbusCrazy_CardEndDT'];
				if (!Ext.isEmpty(form.Morbus_id)) {
					params.Morbus_id = form.Morbus_id;
				}
			} else {
				var url = '/?c=PersonRegister&m=out';
				if(Ext.isEmpty(params['CrazyCauseEndSurveyType_id'])||(Ext.isEmpty(params['Morbus_disDT']))){
					sw.swMsg.alert(lang['oshibka'], lang['ne_vse_polya_zapolnenyi_dlya_isklyucheniya_iz_registra']);
					return false;
				}else{
					params.PersonRegister_disDate = params['Morbus_disDT'];
					params.PersonRegisterOutCause_id = params['CrazyCauseEndSurveyType_id'];
					params.PersonRegister_id = this.PersonRegister_id;
					params.MorbusType_SysNick = 'crazy';
					params.PersonRegister_setDate = this.PersonRegister_setDate
					params.MedPersonal_did = this.MedPersonal_did;
					params.Lpu_did = this.Lpu_did;
				}
			}
		}
		log(params,btn_name,el_data)
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
					save_tb2.setDisplayed('none');
					save_tb3.setDisplayed('none');
					save_tb4.setDisplayed('none');
					var conf;
					for(var field_name in form.changed_fields) {
						conf = form.changed_fields[field_name];
						conf.elOutput.setDisplayed('inline');
						conf.elOutput.update(conf.outputValue);
						if(conf.type == 'id') conf.elOutput.setAttribute('dataid',conf.value);
						conf.elInputWrap.setDisplayed('none');
						conf.elInput.destroy();
						form.input_cmp_list[conf.elOutputId] = false;
					}
					form.changed_fields = {};
					form.isChange = true;
					if(btn_name=='saveMorbusCrazyDynamicsObserv' && getRegionNick() != 'ufa'){
						form.hide();
					}
				}
			}
		});
	},
	/**
	 * Открывает соответсвующую акшену форму 
	 * 
	 * @param {open_form} Название открываемой формы, такое же как название объекта формы
	 * @param {id} Наименование идентификатора таблицы, передаваемого в форму
	 */
	openForm: function (open_form, id, oparams, mode, title, callback)
	{
		// Проверка
		if (getWnd(open_form).isVisible())
		{
			sw.swMsg.alert(lang['soobschenie'], lang['forma']+ ((title)?title:open_form) +lang['v_dannyiy_moment_otkryita']);
			return false;
		}
		else
		{
			if (!mode)
				mode = 'edit';
			var params = {
				action: mode,
				Person_id: this.Person_id,
				UserMedStaffFact_id: this.userMedStaffFact.MedStaffFact_id,
				UserLpuSection_id: this.userMedStaffFact.LpuSection_id,
				userMedStaffFact: this.userMedStaffFact,
				from: this.id,
				ARMType: this.userMedStaffFact.ARMType
			};
			params = Ext.apply(params || {}, oparams || {});
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
		if ( !event.inlist(['MorbusCrazyBasePS','MorbusCrazyForceTreat','MorbusCrazyPersonStick','MorbusCrazyPersonSuicidalAttempt','MorbusCrazyPersonSocDangerAct','MorbusCrazyBaseDrugStart','MorbusCrazyDrug','MorbusCrazyDynamicsState','MorbusCrazyVizitCheck','MorbusCrazyDynamicsObserv','MorbusCrazyDiag','MorbusCrazyPersonSurveyHIV','MorbusCrazyNdOsvid', 'MorbusCrazyDrugVolume', 'MorbusCrazyBBK']) )
		{
			return false;
		}

		if ( event.inlist(['MorbusCrazyBasePS','MorbusCrazyForceTreat','MorbusCrazyPersonStick','MorbusCrazyPersonSuicidalAttempt','MorbusCrazyPersonSocDangerAct','MorbusCrazyBaseDrugStart','MorbusCrazyDrug','MorbusCrazyDynamicsState','MorbusCrazyVizitCheck','MorbusCrazyDynamicsObserv','MorbusCrazyDiag','MorbusCrazyPersonSurveyHIV', 'MorbusCrazyNdOsvid', 'MorbusCrazyDrugVolume', 'MorbusCrazyBBK']) )
		{
			data.object_id = data.object_id.split('_')[1];
		}

		var formParams = this.rightPanel.getObjectData(data.object,data.object_id);

		var error = '';
		var question = '';
		var params = new Object();
		var url = '';
		var onSuccess;

		switch ( event ) {
			case 'MorbusCrazyForceTreat':
			case 'MorbusCrazyPersonStick':
			case 'MorbusCrazyPersonSuicidalAttempt':
			case 'MorbusCrazyPersonSocDangerAct':
			case 'MorbusCrazyBaseDrugStart':
			case 'MorbusCrazyDrug':
			case 'MorbusCrazyDrugVolume':
			case 'MorbusCrazyBBK':
			case 'MorbusCrazyBasePS':
			case 'MorbusCrazyDynamicsState':
			case 'MorbusCrazyVizitCheck':
			case 'MorbusCrazyDynamicsObserv':
			case 'MorbusCrazyDiag':
			case 'MorbusCrazyPersonSurveyHIV':
			case 'MorbusCrazyNdOsvid':
			case 'MorbusCrazyPersonInvalid':
				error = lang['pri_udalenii_voznikli_oshibki'];
				question = lang['udalit'];
				onSuccess = function(){
					var reload_params = {
						section_code: data.object,
						object_key: data.object +'_id',
						object_value: formParams.MorbusCrazy_id,
						parent_object_key: 'MorbusCrazy_id',
						parent_object_value: formParams.MorbusCrazy_id,
						accessType: (this.allowSpecificEdit == true)?1:0,
						param_name: 'MorbusCrazy_pid',
						param_value: formParams.MorbusCrazy_pid || null,
						section_id: data.object +'List_'+ formParams.MorbusCrazy_pid +'_'+ formParams.MorbusCrazy_id
					};
					this.rightPanel.reloadViewForm(reload_params);
				}.createDelegate(this);
				url = '/?c=Utils&m=ObjectRecordDelete';
				params['object'] = data.object;
				params['obj_isEvn'] = 'false';
				params['id'] = data.object_id;
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
							sw.swMsg.alert(lang['oshibka'], error);
						},
						params: params,
						success: function(response, options) {
							loadMask.hide();
							var response_obj = Ext.util.JSON.decode(response.responseText);
							if ( response_obj.success == false ) {
								sw.swMsg.alert(lang['oshibka'], response_obj.Error_Msg ? response_obj.Error_Msg : error);
							} else {
								onSuccess(response_obj);
							}
						}.createDelegate(this),
						url: url
					});
				}
			}.createDelegate(this),
			icon: Ext.MessageBox.QUESTION,
			msg: question,
			title: lang['vopros']
		});

		return true;

	},
	loadNodeViewForm: function() 
	{
		if (this.MorbusCrazy_pid) {
			this.viewObject.attributes.object_id = 'MorbusCrazy_pid';
			this.viewObject.attributes.object_value = this.MorbusCrazy_pid;
		}
		if (this.PersonRegister_id) {
			this.rightPanel.loadNodeViewForm(this.viewObject, {
				param_name: 'PersonRegister_id',
				param_value: this.PersonRegister_id
			});
		} else if (this.Morbus_id) {
			this.rightPanel.loadNodeViewForm(this.viewObject, {
				param_name: 'Morbus_id',
				param_value: this.Morbus_id
			});
		} else if (this.EvnDiagPLStom_id) { // создание специфики
			this.rightPanel.loadNodeViewForm(this.viewObject, {
				param_name: 'EvnDiagPLStom_id',
				param_value: this.EvnDiagPLStom_id
			});
		} else if (this.EvnVizitPL_id) { // создание специфики
			this.rightPanel.loadNodeViewForm(this.viewObject, {
				param_name: 'EvnVizitPL_id',
				param_value: this.EvnVizitPL_id
			});
		} else if (this.EvnSection_id) { // создание специфики
			this.rightPanel.loadNodeViewForm(this.viewObject, {
				param_name: 'EvnSection_id',
				param_value: this.EvnSection_id
			});
		}
	},
	submitOnHide: function() {
		this.inSubmitOnHide = true;
		var win = this;
		if(!win.changed_fields || win.changed_fields.length==0){
			return true;
		}
		var data = win.changed_fields;
		var observFields = {};
		var obj_id = false;
		for (item in data) {
			if(!obj_id){
				obj_id = data[item];
				obj_id = obj_id.elOutputId.split('_')[2];
			}
			if(item.inlist(['Morbus_disDT','MorbusCrazy_CardEndDT','CrazyCauseEndSurveyType'])){
				switch(item){
					case 'Morbus_disDT':
						observFields.Morbus_disDT = win.changed_fields.Morbus_disDT;
						delete win.changed_fields.Morbus_disDT;
						break;
					case 'MorbusCrazy_CardEndDT':
						observFields.MorbusCrazy_CardEndDT = win.changed_fields.MorbusCrazy_CardEndDT;
						delete win.changed_fields.MorbusCrazy_CardEndDT;
						break;
					case 'CrazyCauseEndSurveyType':
						observFields.CrazyCauseEndSurveyType = win.changed_fields.CrazyCauseEndSurveyType;
						delete win.changed_fields.CrazyCauseEndSurveyType;
						break;
				}
			}
		}
		if(Object.keys(win.changed_fields).length>0){
			this.submitMorbusCrazyHtmlForm(false,{object_id:this.Person_id+'_'+obj_id});
		}
		if(Object.keys(observFields).length>0){
			win.changed_fields = observFields;
			this.submitMorbusCrazyHtmlForm('saveMorbusCrazyDynamicsObserv',{object_id:this.Person_id+'_'+obj_id});
		}
	},
	
	show: function() 
	{
		sw.Promed.swMorbusCrazyWindow.superclass.show.apply(this, arguments);
		this.Person_id = null;
		this.PersonRegister_id = null;
		this.userMedStaffFact = null;
		this.onHide =null;
		this.isChange = false;
		this.allowSpecificEdit = null;
		this.viewObject = {}
		this.inSubmitOnHide = false;
		this.ignoreHideCheck = false;
		this.changed_fields = {};
		//log(arguments[0]);
		if ( !arguments[0] || !arguments[0].Person_id ) {
			sw.swMsg.alert(lang['soobschenie'], lang['nevernyie_parametryi'], function() { this.hide(); }.createDelegate(this) );
			return false;
		}
		this.editType = 'all';
        if(arguments[0] && arguments[0].editType){
            this.editType = arguments[0].editType;
        }
		this.Person_id = arguments[0].Person_id;
		this.EvnDiagPLStom_id = arguments[0].EvnDiagPLStom_id;
		this.PersonRegister_id = arguments[0].PersonRegister_id || null;
		this.userMedStaffFact = arguments[0].userMedStaffFact || sw.Promed.MedStaffFactByUser.last;
		this.PersonRegister_setDate = arguments[0].PersonRegister_setDate
		this.MedPersonal_did = arguments[0].MedPersonal_did
		this.Lpu_did = arguments[0].Lpu_did
		this.EvnVizitPL_id = arguments[0].EvnVizitPL_id;
		this.EvnSection_id = arguments[0].EvnSection_id;
		this.Morbus_id = arguments[0].Morbus_id || null;
		this.MorbusCrazy_pid = arguments[0].MorbusCrazy_pid;
		this.onHide = arguments[0].onHide || Ext.emptyFn;
		this.type = arguments[0].type || 'crazy';
		this.isChange = false;
		this.allowSpecificEdit = arguments[0].allowSpecificEdit || false;
		
		if(arguments[0] && arguments[0].action == 'view')
			this.allowSpecificEdit = false;

		if (this.MorbusCrazy_pid) {
			this.setTitle('Специфика');
		}

		this.viewObject = {
			id: 'PersonMorbusCrazy_'+ this.Person_id,
			attributes: {
				accessType: (this.allowSpecificEdit == true)?'edit':'view',
				text: 'test',
				object: 'PersonMorbusCrazy',
				object_id: 'Person_id',
				object_value: this.Person_id			
			}
		};
		this.loadNodeViewForm();
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
			PersonMorbusCrazy: {
				print: {
					actionType: 'view',
					sectionCode: 'PersonMorbusCrazy',
					handler: function(e, c, d) {
						win.rightPanel.printHtml(d.section_id);
					}
				},
				editPhoto: {
					actionType: 'edit',
					sectionCode: 'person_data',
					handler: function(e, c, d) {
						var params = {
							action: 'loadimage',
							saveUrl: '/?c=PMMediaData&m=uploadPersonPhoto',
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
									win.loadNodeViewForm();
								}
							}
						};
						win.openForm('swPersonEditWindow','XXX_id',params,'edit',lang['redaktirovanie_personalnyih_dannyih_patsienta']);
					}
				},
				printMedCard: {
					actionType: 'view',
					sectionCode: 'person_data', 
					handler: function(e, c, d) {
						var data = win.rightPanel.getObjectData('PersonMorbusCrazy',d.object_id);
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
								// нужно обновить секцию person_data, пока будем перезагружать все
								win.loadNodeViewForm();
							}
						};
						win.openForm('swPersonCardHistoryWindow','XXX_id',params,'edit',lang['istoriya_prikrepleniya']);
					}
				}
			},
			MorbusCrazy: {
				toggleDisplayDiag: {actionType: 'view',sectionCode: 'MorbusCrazy', handler: function(e, c, d) {
					var id = 'MorbusCrazyDiag_'+ d.object_id;
					win.rightPanel.toggleDisplay(id,Ext.get(id).isDisplayed()); }
				},
				toggleMorbusCrazyDynamicsObserv: {actionType: 'view',sectionCode: 'MorbusCrazy', handler: function(e, c, d) {
					var id = 'MorbusCrazyDynamicsObserv_'+ d.object_id;
					win.rightPanel.toggleDisplay(id,Ext.get(id).isDisplayed()); }
				},
				toggleMorbusCrazyBasePS: {actionType: 'view',sectionCode: 'MorbusCrazy', handler: function(e, c, d) {
					var id = 'MorbusCrazyBasePS_'+ d.object_id;
					win.rightPanel.toggleDisplay(id,Ext.get(id).isDisplayed()); }
				},
				toggleMorbusCrazyPerson: {actionType: 'view',sectionCode: 'MorbusCrazy', handler: function(e, c, d) {
					var id = 'MorbusCrazyPerson_'+ d.object_id;
					win.rightPanel.toggleDisplay(id,Ext.get(id).isDisplayed()); }
				},
				toggleMorbusCrazyDrug: {actionType: 'view',sectionCode: 'MorbusCrazy', handler: function(e, c, d) {
					var id = 'MorbusCrazyDrug_'+ d.object_id;
					win.rightPanel.toggleDisplay(id,Ext.get(id).isDisplayed()); }
				},
				inputMorbus_setDT: {
					actionType: 'edit',
					sectionCode: 'MorbusCrazy',
					dblClick: false,
					handler: function(e, c, d) {
						win.createMorbusHtmlForm('Morbus_setDT', d);
					}
				},
				inputPersonRegister_setDate: {
					actionType: 'edit',
					sectionCode: 'MorbusCrazy',
					dblClick: false,
					handler: function(e, c, d) {
						win.createMorbusHtmlForm('PersonRegister_setDate', d);
					}
				},
				inputMorbus_disDT: {
					actionType: 'edit',
					sectionCode: 'MorbusCrazy',
					dblClick: false,
					handler: function(e, c, d) {
						win.createMorbusHtmlForm('Morbus_disDT', d);
					}
				},
				inputMorbusCrazy_CardEndDT: {
					actionType: 'edit',
					sectionCode: 'MorbusCrazy',
					dblClick: false,
					handler: function(e, c, d) {
						win.createMorbusHtmlForm('MorbusCrazy_CardEndDT', d);
					}
				},
				inputDiag_nid: {
					actionType: 'edit',
					sectionCode: 'MorbusCrazy',
					dblClick: false,
					handler: function(e, c, d) {
						win.createMorbusHtmlForm('Diag_nid', d);
					}
				},
				inputDiag_sid: {
					actionType: 'edit',
					sectionCode: 'MorbusCrazy',
					dblClick: false,
					handler: function(e, c, d) {
						win.createMorbusHtmlForm('Diag_sid', d);
					}
				},
				inputCrazyResultDeseaseType: {
					actionType: 'edit',
					sectionCode: 'MorbusCrazy',
					dblClick: false,
					handler: function(e, c, d) {

						win.createMorbusHtmlForm('CrazyResultDeseaseType', d);
					}
				},
				inputCrazyDeathCauseType: {
					actionType: 'edit',
					sectionCode: 'MorbusCrazy',
					dblClick: false,
					handler: function(e, c, d) {

						win.createMorbusHtmlForm('CrazyDeathCauseType', d);
					}
				},
				inputMorbusCrazyBase_LTMDayCount: {
					actionType: 'edit',
					sectionCode: 'MorbusCrazy',
					dblClick: false,
					handler: function(e, c, d) {

						win.createMorbusHtmlForm('MorbusCrazyBase_LTMDayCount', d);
					}
				},
				inputMorbusCrazyBase_HolidayDayCount: {
					actionType: 'edit',
					sectionCode: 'MorbusCrazy',
					dblClick: false,
					handler: function(e, c, d) {

						win.createMorbusHtmlForm('MorbusCrazyBase_HolidayDayCount', d);
					}
				},
				inputMorbusCrazyBase_HolidayCount: {
					actionType: 'edit',
					sectionCode: 'MorbusCrazy',
					dblClick: false,
					handler: function(e, c, d) {
						win.createMorbusHtmlForm('MorbusCrazyBase_HolidayCount', d);
					}
				},
				inputMorbusCrazyPerson_IsWowInvalid: {
					actionType: 'edit',
					sectionCode: 'MorbusCrazy',
					dblClick: false,
					handler: function(e, c, d) {
						win.createMorbusHtmlForm('MorbusCrazyPerson_IsWowInvalid', d);
					}
				},
				inputMorbusCrazyPerson_IsWowMember: {
					actionType: 'edit',
					sectionCode: 'MorbusCrazy',
					dblClick: false,
					handler: function(e, c, d) {
						win.createMorbusHtmlForm('MorbusCrazyPerson_IsWowMember', d);
					}
				},
				inputCrazyEducationType: {
					actionType: 'edit',
					sectionCode: 'MorbusCrazy',
					dblClick: false,
					handler: function(e, c, d) {
						win.createMorbusHtmlForm('CrazyEducationType', d);
					}
				},
				inputMorbusCrazyPerson_CompleteClassCount: {
					actionType: 'edit',
					sectionCode: 'MorbusCrazy',
					dblClick: false,
					handler: function(e, c, d) {
						win.createMorbusHtmlForm('MorbusCrazyPerson_CompleteClassCount', d);
					}
				},
				inputMorbusCrazyPerson_IsEducation: {
					actionType: 'edit',
					sectionCode: 'MorbusCrazy',
					dblClick: false,
					handler: function(e, c, d) {
						win.createMorbusHtmlForm('MorbusCrazyPerson_IsEducation', d);
					}
				},
				inputCrazySourceLivelihoodType: {
					actionType: 'edit',
					sectionCode: 'MorbusCrazy',
					dblClick: false,
					handler: function(e, c, d) {
						win.createMorbusHtmlForm('CrazySourceLivelihoodType', d);
					}
				},
				inputCrazyResideType: {
					actionType: 'edit',
					sectionCode: 'MorbusCrazy',
					dblClick: false,
					handler: function(e, c, d) {
						win.createMorbusHtmlForm('CrazyResideType', d);
					}
				},
				inputCrazyResideConditionsType: {
					actionType: 'edit',
					sectionCode: 'MorbusCrazy',
					dblClick: false,
					handler: function(e, c, d) {
						win.createMorbusHtmlForm('CrazyResideConditionsType', d);
					}
				},
				inputMorbusCrazyBase_firstDT: {
					actionType: 'edit',
					sectionCode: 'MorbusCrazy',
					dblClick: false,
					handler: function(e, c, d) {
						win.createMorbusHtmlForm('MorbusCrazyBase_firstDT', d);
					}
				},
				inputMorbusCrazyPerson_IsConvictionBeforePsych: {
					actionType: 'edit',
					sectionCode: 'MorbusCrazy',
					dblClick: false,
					handler: function(e, c, d) {
						win.createMorbusHtmlForm('MorbusCrazyPerson_IsConvictionBeforePsych', d);
					}
				},
				inputMorbusCrazyBase_DeathDT: {
					actionType: 'edit',
					sectionCode: 'MorbusCrazy',
					dblClick: false,
					handler: function(e, c, d) {
						win.createMorbusHtmlForm('MorbusCrazyBase_DeathDT', d);
					}
				},
				inputCrazyCauseEndSurveyType: {
					actionType: 'edit',
					sectionCode: 'MorbusCrazy',
					dblClick: false,
					handler: function(e, c, d) {
						win.createMorbusHtmlForm('CrazyCauseEndSurveyType', d);
					}
				},
				inputMorbusCrazyBase_IsUseAlienDevice: {
					actionType: 'edit',
					sectionCode: 'MorbusCrazy',
					dblClick: false,
					handler: function(e, c, d) {
						win.createMorbusHtmlForm('MorbusCrazyBase_IsUseAlienDevice', d);
					}
				},
				inputMorbusCrazyBase_IsLivingConsumDrug: {
					actionType: 'edit',
					sectionCode: 'MorbusCrazy',
					dblClick: false,
					handler: function(e, c, d) {
						win.createMorbusHtmlForm('MorbusCrazyBase_IsLivingConsumDrug', d);
					}
				},
				saveDiag: { actionType: 'edit', sectionCode: 'MorbusCrazy', handler: function(e, c, d) { win.submitMorbusCrazyHtmlForm('saveDiag',d); } },
				saveMorbusCrazyDynamicsObserv: { actionType: 'edit', sectionCode: 'MorbusCrazy', handler: function(e, c, d) { win.submitMorbusCrazyHtmlForm('saveMorbusCrazyDynamicsObserv',d); } },
				saveMorbusCrazyPerson: { actionType: 'edit', sectionCode: 'MorbusCrazy', handler: function(e, c, d) { win.submitMorbusCrazyHtmlForm('saveMorbusCrazyPerson',d); } },
				saveMorbusCrazyDrug: { actionType: 'edit', sectionCode: 'MorbusCrazy', handler: function(e, c, d) { win.submitMorbusCrazyHtmlForm('saveMorbusCrazyDrug',d); } }
			},
			MorbusCrazyDiag: {
				edit: {
					actionType: 'edit',
					sectionCode: 'MorbusCrazyDiag',
					dblClick: true,
					handler: function(e, c, d) {
						win.openMorbusCrazySpecificForm({action: 'edit',object: 'MorbusCrazyDiag', eldata: d});
					}
				},
				'delete': {
					actionType: 'edit',
					sectionCode: 'MorbusCrazyDiag',
					handler: function(e, c, d) {
						win.deleteEvent('MorbusCrazyDiag',d);
					}
				},
				add: {
					actionType: 'edit',
					sectionCode: 'MorbusCrazyDiagList',
					handler: function(e, c, d) {
						win.openMorbusCrazySpecificForm({action: 'add',object: 'MorbusCrazyDiag', eldata: d});
					}
				},
				print: {
					actionType: 'view',
					sectionCode: 'MorbusCrazyDiagList',
					handler: function(e, c, d) {
						win.rightPanel.printHtml(d.section_id);
					}
				},
				toggleDisplay: {
					actionType: 'view',
					sectionCode: 'MorbusCrazyDiagList',
					handler: function(e, c, d) {
						var id = 'MorbusCrazyDiagTable_'+ d.object_id;
						win.rightPanel.toggleDisplay(id,Ext.get(id).isDisplayed());
					}
				}
			},
			MorbusCrazyDynamicsObserv: {
				edit: {
					actionType: 'edit',
					sectionCode: 'MorbusCrazyDynamicsObserv',
					dblClick: true,
					handler: function(e, c, d) {
						win.openMorbusCrazySpecificForm({action: 'edit',object: 'MorbusCrazyDynamicsObserv', eldata: d});
					}
				},
				'delete': {
					actionType: 'edit',
					sectionCode: 'MorbusCrazyDynamicsObserv',
					handler: function(e, c, d) {
						win.deleteEvent('MorbusCrazyDynamicsObserv',d);
					}
				},
				add: {
					actionType: 'edit',
					sectionCode: 'MorbusCrazyDynamicsObservList',
					handler: function(e, c, d) {
						win.openMorbusCrazySpecificForm({action: 'add',object: 'MorbusCrazyDynamicsObserv', eldata: d});
					}
				},
				print: {
					actionType: 'view',
					sectionCode: 'MorbusCrazyDynamicsObservList',
					handler: function(e, c, d) {
						win.rightPanel.printHtml(d.section_id);
					}
				},
				toggleDisplay: {
					actionType: 'view',
					sectionCode: 'MorbusCrazyDynamicsObservList',
					handler: function(e, c, d) {
						var id = 'MorbusCrazyDynamicsObservTable_'+ d.object_id;
						win.rightPanel.toggleDisplay(id,Ext.get(id).isDisplayed());
					}
				}
			},
			MorbusCrazyVizitCheck: {
				edit: {
					actionType: 'edit',
					sectionCode: 'MorbusCrazyVizitCheck',
					dblClick: true,
					handler: function(e, c, d) {
						win.openMorbusCrazySpecificForm({action: 'edit',object: 'MorbusCrazyVizitCheck', eldata: d});
					}
				},
				'delete': {
					actionType: 'edit',
					sectionCode: 'MorbusCrazyVizitCheck',
					handler: function(e, c, d) {
						win.deleteEvent('MorbusCrazyVizitCheck',d);
					}
				},
				add: {
					actionType: 'edit',
					sectionCode: 'MorbusCrazyVizitCheckList',
					handler: function(e, c, d) {
						win.openMorbusCrazySpecificForm({action: 'add',object: 'MorbusCrazyVizitCheck', eldata: d});
					}
				},
				print: {
					actionType: 'view',
					sectionCode: 'MorbusCrazyVizitCheckList',
					handler: function(e, c, d) {
						win.rightPanel.printHtml(d.section_id);
					}
				},
				toggleDisplay: {
					actionType: 'view',
					sectionCode: 'MorbusCrazyVizitCheckList',
					handler: function(e, c, d) {
						var id = 'MorbusCrazyVizitCheckTable_'+ d.object_id;
						win.rightPanel.toggleDisplay(id,Ext.get(id).isDisplayed());
					}
				}
			},
			MorbusCrazyDynamicsState: {
				edit: {
					actionType: 'edit',
					sectionCode: 'MorbusCrazyDynamicsState',
					dblClick: true,
					handler: function(e, c, d) {
						win.openMorbusCrazySpecificForm({action: 'edit',object: 'MorbusCrazyDynamicsState', eldata: d});
					}
				},
				'delete': {
					actionType: 'edit',
					sectionCode: 'MorbusCrazyDynamicsState',
					handler: function(e, c, d) {
						win.deleteEvent('MorbusCrazyDynamicsState',d);
					}
				},
				add: {
					actionType: 'edit',
					sectionCode: 'MorbusCrazyDynamicsStateList',
					handler: function(e, c, d) {
						win.openMorbusCrazySpecificForm({action: 'add',object: 'MorbusCrazyDynamicsState', eldata: d});
					}
				},
				print: {
					actionType: 'view',
					sectionCode: 'MorbusCrazyDynamicsStateList',
					handler: function(e, c, d) {
						win.rightPanel.printHtml(d.section_id);
					}
				},
				toggleDisplay: {
					actionType: 'view',
					sectionCode: 'MorbusCrazyDynamicsStateList',
					handler: function(e, c, d) {
						var id = 'MorbusCrazyDynamicsStateTable_'+ d.object_id;
						win.rightPanel.toggleDisplay(id,Ext.get(id).isDisplayed());
					}
				}
			},
			MorbusCrazyBasePS: {
				edit: {
					actionType: 'edit',
					sectionCode: 'MorbusCrazyBasePS',
					dblClick: true,
					handler: function(e, c, d) {
						win.openMorbusCrazySpecificForm({action: 'edit',object: 'MorbusCrazyBasePS', eldata: d});
					}
				},
				'delete': {
					actionType: 'edit',
					sectionCode: 'MorbusCrazyBasePS',
					handler: function(e, c, d) {
						win.deleteEvent('MorbusCrazyBasePS',d);
					}
				},
				add: {
					actionType: 'edit',
					sectionCode: 'MorbusCrazyBasePSList',
					handler: function(e, c, d) {
						win.openMorbusCrazySpecificForm({action: 'add',object: 'MorbusCrazyBasePS', eldata: d});
					}
				},
				print: {
					actionType: 'view',
					sectionCode: 'MorbusCrazyBasePSList',
					handler: function(e, c, d) {
						win.rightPanel.printHtml(d.section_id);
					}
				},
				toggleDisplay: {
					actionType: 'view',
					sectionCode: 'MorbusCrazyBasePSList',
					handler: function(e, c, d) {
						var id = 'MorbusCrazyBasePSTable_'+ d.object_id;
						win.rightPanel.toggleDisplay(id,Ext.get(id).isDisplayed());
					}
				}
			},
			MorbusCrazyForceTreat: {
				edit: {
					actionType: 'edit',
					sectionCode: 'MorbusCrazyForceTreat',
					dblClick: true,
					handler: function(e, c, d) {
						win.openMorbusCrazySpecificForm({action: 'edit',object: 'MorbusCrazyForceTreat', eldata: d});
					}
				},
				'delete': {
					actionType: 'edit',
					sectionCode: 'MorbusCrazyForceTreat',
					handler: function(e, c, d) {
						win.deleteEvent('MorbusCrazyForceTreat',d);
					}
				},
				add: {
					actionType: 'edit',
					sectionCode: 'MorbusCrazyForceTreatList',
					handler: function(e, c, d) {
						win.openMorbusCrazySpecificForm({action: 'add',object: 'MorbusCrazyForceTreat', eldata: d});
					}
				},
				print: {
					actionType: 'view',
					sectionCode: 'MorbusCrazyForceTreatList',
					handler: function(e, c, d) {
						win.rightPanel.printHtml(d.section_id);
					}
				},
				toggleDisplay: {
					actionType: 'view',
					sectionCode: 'MorbusCrazyForceTreatList',
					handler: function(e, c, d) {
						var id = 'MorbusCrazyForceTreatTable_'+ d.object_id;
						win.rightPanel.toggleDisplay(id,Ext.get(id).isDisplayed());
					}
				}
			},
			MorbusCrazyPersonStick: {
				edit: {
					actionType: 'edit',
					sectionCode: 'MorbusCrazyPersonStick',
					dblClick: true,
					handler: function(e, c, d) {
						win.openMorbusCrazySpecificForm({action: 'edit',object: 'MorbusCrazyPersonStick', eldata: d});
					}
				},
				'delete': {
					actionType: 'edit',
					sectionCode: 'MorbusCrazyPersonStick',
					handler: function(e, c, d) {
						win.deleteEvent('MorbusCrazyPersonStick',d);
					}
				},
				add: {
					actionType: 'edit',
					sectionCode: 'MorbusCrazyPersonStickList',
					handler: function(e, c, d) {
						win.openMorbusCrazySpecificForm({action: 'add',object: 'MorbusCrazyPersonStick', eldata: d});
					}
				},
				print: {
					actionType: 'view',
					sectionCode: 'MorbusCrazyPersonStickList',
					handler: function(e, c, d) {
						win.rightPanel.printHtml(d.section_id);
					}
				},
				toggleDisplay: {
					actionType: 'view',
					sectionCode: 'MorbusCrazyPersonStickList',
					handler: function(e, c, d) {
						var id = 'MorbusCrazyPersonStickTable_'+ d.object_id;
						win.rightPanel.toggleDisplay(id,Ext.get(id).isDisplayed());
					}
				}
			},
			MorbusCrazyPersonSuicidalAttempt: {
				edit: {
					actionType: 'edit',
					sectionCode: 'MorbusCrazyPersonSuicidalAttempt',
					dblClick: true,
					handler: function(e, c, d) {
						win.openMorbusCrazySpecificForm({action: 'edit',object: 'MorbusCrazyPersonSuicidalAttempt', eldata: d});
					}
				},
				'delete': {
					actionType: 'edit',
					sectionCode: 'MorbusCrazyPersonSuicidalAttempt',
					handler: function(e, c, d) {
						win.deleteEvent('MorbusCrazyPersonSuicidalAttempt',d);
					}
				},
				add: {
					actionType: 'edit',
					sectionCode: 'MorbusCrazyPersonSuicidalAttemptList',
					handler: function(e, c, d) {
						win.openMorbusCrazySpecificForm({action: 'add',object: 'MorbusCrazyPersonSuicidalAttempt', eldata: d});
					}
				},
				print: {
					actionType: 'view',
					sectionCode: 'MorbusCrazyPersonSuicidalAttemptList',
					handler: function(e, c, d) {
						win.rightPanel.printHtml(d.section_id);
					}
				},
				toggleDisplay: {
					actionType: 'view',
					sectionCode: 'MorbusCrazyPersonSuicidalAttemptList',
					handler: function(e, c, d) {
						var id = 'MorbusCrazyPersonSuicidalAttemptTable_'+ d.object_id;
						win.rightPanel.toggleDisplay(id,Ext.get(id).isDisplayed());
					}
				}
			},
			MorbusCrazyPersonSocDangerAct: {
				edit: {
					actionType: 'edit',
					sectionCode: 'MorbusCrazyPersonSocDangerAct',
					dblClick: true,
					handler: function(e, c, d) {
						win.openMorbusCrazySpecificForm({action: 'edit',object: 'MorbusCrazyPersonSocDangerAct', eldata: d});
					}
				},
				'delete': {
					actionType: 'edit',
					sectionCode: 'MorbusCrazyPersonSocDangerAct',
					handler: function(e, c, d) {
						win.deleteEvent('MorbusCrazyPersonSocDangerAct',d);
					}
				},
				add: {
					actionType: 'edit',
					sectionCode: 'MorbusCrazyPersonSocDangerActList',
					handler: function(e, c, d) {
						win.openMorbusCrazySpecificForm({action: 'add',object: 'MorbusCrazyPersonSocDangerAct', eldata: d});
					}
				},
				print: {
					actionType: 'view',
					sectionCode: 'MorbusCrazyPersonSocDangerActList',
					handler: function(e, c, d) {
						win.rightPanel.printHtml(d.section_id);
					}
				},
				toggleDisplay: {
					actionType: 'view',
					sectionCode: 'MorbusCrazyPersonSocDangerActList',
					handler: function(e, c, d) {
						var id = 'MorbusCrazyPersonSocDangerActTable_'+ d.object_id;
						win.rightPanel.toggleDisplay(id,Ext.get(id).isDisplayed());
					}
				}
			},
			MorbusCrazyBaseDrugStart: {
				edit: {
					actionType: 'edit',
					sectionCode: 'MorbusCrazyBaseDrugStart',
					dblClick: true,
					handler: function(e, c, d) {
						win.openMorbusCrazySpecificForm({action: 'edit',object: 'MorbusCrazyBaseDrugStart', eldata: d});
					}
				},
				'delete': {
					actionType: 'edit',
					sectionCode: 'MorbusCrazyBaseDrugStart',
					handler: function(e, c, d) {
						win.deleteEvent('MorbusCrazyBaseDrugStart',d);
					}
				},
				add: {
					actionType: 'edit',
					sectionCode: 'MorbusCrazyBaseDrugStartList',
					handler: function(e, c, d) {
						win.openMorbusCrazySpecificForm({action: 'add',object: 'MorbusCrazyBaseDrugStart', eldata: d});
					}
				},
				print: {
					actionType: 'view',
					sectionCode: 'MorbusCrazyBaseDrugStartList',
					handler: function(e, c, d) {
						win.rightPanel.printHtml(d.section_id);
					}
				},
				toggleDisplay: {
					actionType: 'view',
					sectionCode: 'MorbusCrazyBaseDrugStartList',
					handler: function(e, c, d) {
						var id = 'MorbusCrazyBaseDrugStartTable_'+ d.object_id;
						win.rightPanel.toggleDisplay(id,Ext.get(id).isDisplayed());
					}
				}
			},
			MorbusCrazyDrug: {
				edit: {
					actionType: 'edit',
					sectionCode: 'MorbusCrazyDrug',
					dblClick: true,
					handler: function(e, c, d) {
						win.openMorbusCrazySpecificForm({action: 'edit',object: 'MorbusCrazyDrug', eldata: d});
					}
				},
				'delete': {
					actionType: 'edit',
					sectionCode: 'MorbusCrazyDrug',
					handler: function(e, c, d) {
						win.deleteEvent('MorbusCrazyDrug',d);
					}
				},
				add: {
					actionType: 'edit',
					sectionCode: 'MorbusCrazyDrugList',
					handler: function(e, c, d) {
						win.openMorbusCrazySpecificForm({action: 'add',object: 'MorbusCrazyDrug', eldata: d});
					}
				},
				print: {
					actionType: 'view',
					sectionCode: 'MorbusCrazyDrugList',
					handler: function(e, c, d) {
						win.rightPanel.printHtml(d.section_id);
					}
				},
				toggleDisplay: {
					actionType: 'view',
					sectionCode: 'MorbusCrazyDrugList',
					handler: function(e, c, d) {
						var id = 'MorbusCrazyDrugTable_'+ d.object_id;
						win.rightPanel.toggleDisplay(id,Ext.get(id).isDisplayed());
					}
				}
			},
			MorbusCrazyDrugVolume: {
				edit: {
					actionType: 'edit',
					sectionCode: 'MorbusCrazyDrugVolume',
					dblClick: true,
					handler: function(e, c, d) {
						win.openMorbusCrazySpecificForm({action: 'edit',object: 'MorbusCrazyDrugVolume', eldata: d});
					}
				},
				'delete': {
					actionType: 'edit',
					sectionCode: 'MorbusCrazyDrugVolume',
					handler: function(e, c, d) {
						win.deleteEvent('MorbusCrazyDrugVolume',d);
					}
				},
				add: {
					actionType: 'edit',
					sectionCode: 'MorbusCrazyDrugVolumeList',
					handler: function(e, c, d) {
						win.openMorbusCrazySpecificForm({action: 'add',object: 'MorbusCrazyDrugVolume', eldata: d});
					}
				},
				print: {
					actionType: 'view',
					sectionCode: 'MorbusCrazyDrugVolumeList',
					handler: function(e, c, d) {
						win.rightPanel.printHtml(d.section_id);
					}
				},
				toggleDisplay: {
					actionType: 'view',
					sectionCode: 'MorbusCrazyDrugVolumeList',
					handler: function(e, c, d) {
						var id = 'MorbusCrazyDrugVolumeTable_'+ d.object_id;
						win.toggleDisplay(id,Ext.get(id).isDisplayed());
					}
				}
			},
			MorbusCrazyBBK: {
				edit: {
					actionType: 'edit',
					sectionCode: 'MorbusCrazyBBK',
					dblClick: true,
					handler: function(e, c, d) {
						win.openMorbusCrazySpecificForm({action: 'edit',object: 'MorbusCrazyBBK', eldata: d});
					}
				},
				'delete': {
					actionType: 'edit',
					sectionCode: 'MorbusCrazyBBK',
					handler: function(e, c, d) {
						win.deleteEvent('MorbusCrazyBBK',d);
					}
				},
				add: {
					actionType: 'edit',
					sectionCode: 'MorbusCrazyBBKList',
					handler: function(e, c, d) {
						win.openMorbusCrazySpecificForm({action: 'add',object: 'MorbusCrazyBBK', eldata: d});
					}
				},
				print: {
					actionType: 'view',
					sectionCode: 'MorbusCrazyBBKList',
					handler: function(e, c, d) {
						win.rightPanel.printHtml(d.section_id);
					}
				},
				toggleDisplay: {
					actionType: 'view',
					sectionCode: 'MorbusCrazyBBKList',
					handler: function(e, c, d) {
						var id = 'MorbusCrazyBBKTable_'+ d.object_id;
						win.rightPanel.toggleDisplay(id,Ext.get(id).isDisplayed());
					}
				}
			},
			MorbusCrazyPersonSurveyHIV: {
				edit: {
					actionType: 'edit',
					sectionCode: 'MorbusCrazyPersonSurveyHIV',
					dblClick: true,
					handler: function(e, c, d) {
						win.openMorbusCrazySpecificForm({action: 'edit',object: 'MorbusCrazyPersonSurveyHIV', eldata: d});
					}
				},
				'delete': {
					actionType: 'edit',
					sectionCode: 'MorbusCrazyPersonSurveyHIV',
					handler: function(e, c, d) {
						win.deleteEvent('MorbusCrazyPersonSurveyHIV',d);
					}
				},
				add: {
					actionType: 'edit',
					sectionCode: 'MorbusCrazyPersonSurveyHIVList',
					handler: function(e, c, d) {
						win.openMorbusCrazySpecificForm({action: 'add',object: 'MorbusCrazyPersonSurveyHIV', eldata: d});
					}
				},
				print: {
					actionType: 'view',
					sectionCode: 'MorbusCrazyPersonSurveyHIVList',
					handler: function(e, c, d) {
						win.rightPanel.printHtml(d.section_id);
					}
				},
				toggleDisplay: {
					actionType: 'view',
					sectionCode: 'MorbusCrazyPersonSurveyHIVList',
					handler: function(e, c, d) {
						var id = 'MorbusCrazyPersonSurveyHIVTable_'+ d.object_id;
						win.rightPanel.toggleDisplay(id,Ext.get(id).isDisplayed());
					}
				}
			},
			MorbusCrazyNdOsvid: {
				edit: {
					actionType: 'edit',
					sectionCode: 'MorbusCrazyNdOsvid',
					dblClick: true,
					handler: function(e, c, d) {
						win.openMorbusCrazySpecificForm({action: 'edit',object: 'MorbusCrazyNdOsvid', eldata: d});
					}
				},
				'delete': {
					actionType: 'edit',
					sectionCode: 'MorbusCrazyNdOsvid',
					handler: function(e, c, d) {
						win.deleteEvent('MorbusCrazyNdOsvid',d);
					}
				},
				add: {
					actionType: 'edit',
					sectionCode: 'MorbusCrazyNdOsvidList',
					handler: function(e, c, d) {
						win.openMorbusCrazySpecificForm({action: 'add',object: 'MorbusCrazyNdOsvid', eldata: d});
					}
				},
				print: {
					actionType: 'view',
					sectionCode: 'MorbusCrazyNdOsvidList',
					handler: function(e, c, d) {
						win.rightPanel.printHtml(d.section_id);
					}
				},
				toggleDisplay: {
					actionType: 'view',
					sectionCode: 'MorbusCrazyNdOsvidList',
					handler: function(e, c, d) {
						var id = 'MorbusCrazyNdOsvidTable_'+ d.object_id;
						win.rightPanel.toggleDisplay(id,Ext.get(id).isDisplayed());
					}
				}
			},
			MorbusCrazyPersonInvalid: {
				edit: {
					actionType: 'edit',
					sectionCode: 'MorbusCrazyPersonInvalid',
					dblClick: true,
					handler: function(e, c, d) {
						win.openMorbusCrazySpecificForm({action: 'edit',object: 'MorbusCrazyPersonInvalid', eldata: d});
					}
				},
				'delete': {
					actionType: 'edit',
					sectionCode: 'MorbusCrazyPersonInvalid',
					handler: function(e, c, d) {
						win.deleteEvent('MorbusCrazyPersonInvalid',d);
					}
				},
				add: {
					actionType: 'edit',
					sectionCode: 'MorbusCrazyPersonInvalidList',
					handler: function(e, c, d) {
						win.openMorbusCrazySpecificForm({action: 'add',object: 'MorbusCrazyPersonInvalid', eldata: d});
					}
				},
				print: {
					actionType: 'view',
					sectionCode: 'MorbusCrazyPersonInvalidList',
					handler: function(e, c, d) {
						win.rightPanel.printHtml(d.section_id);
					}
				},
				toggleDisplay: {
					actionType: 'view',
					sectionCode: 'MorbusCrazyPersonInvalidList',
					handler: function(e, c, d) {
						var id = 'MorbusCrazyPersonInvalidTable_'+ d.object_id;
						win.rightPanel.toggleDisplay(id,Ext.get(id).isDisplayed());
					}
				}
			},
			PersonPrivilege: {
				toggleDisplay: {
					actionType: 'view',
					sectionCode: 'PersonPrivilegeList',
					handler: function(e, c, d) {
						var id = 'PersonPrivilegeTable_'+ d.object_id;
						win.rightPanel.toggleDisplay(id,Ext.get(id).isDisplayed());
					}
				}
			},
			PersonPrivilegeFed: {
				toggleDisplay: {
					actionType: 'view',
					sectionCode: 'PersonPrivilegeFedList',
					handler: function(e, c, d) {
						var id = 'PersonPrivilegeFedTable_'+ d.object_id;
						win.rightPanel.toggleDisplay(id,Ext.get(id).isDisplayed());
					}
				}
			},
			/*DrugCrazy: {
				toggleDisplay: {
					actionType: 'view',
					sectionCode: 'DrugCrazyList',
					handler: function(e, c, d) {
						var id = 'DrugCrazyTable_'+ d.object_id;
						win.rightPanel.toggleDisplay(id,Ext.get(id).isDisplayed());
					}
				}
			},*/
			PersonRegisterExport: {
				toggleDisplay: {
					actionType: 'view',
					sectionCode: 'PersonRegisterExportList',
					handler: function(e, c, d) {
						var id = 'PersonRegisterExportTable_'+ d.object_id;
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
		sw.Promed.swMorbusCrazyWindow.superclass.initComponent.apply(this, arguments);
	}
});
