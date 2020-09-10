/**
* swMorbusHIVWindow - Запись регистра с типом «ВИЧ»: Просмотр/Редактирование
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Morbus
* @access       public
* @copyright    Copyright (c) 2009-2010 Swan Ltd.
* @author       Alexander Permyakov
* @version      2012/12
*/

sw.Promed.swMorbusHIVWindow = Ext.extend(sw.Promed.BaseForm, 
{
	modal: true,
	resizable: false,
	autoHeight: false,
	closeAction :'hide',
	autoScroll: true,
	border : false,
	plain : false,
	action: null,
	maximized: true,
	title: lang['zapis_registra_s_tipom_vich'],
	listeners: {
		'hide': function(win) {
			win.onHide(win.isChange);
		},
		'beforeShow': function(win) {
			if(!getWnd('swWorkPlaceMZSpecWindow').isVisible() && !isUserGroup('MIACSuperAdmin'))
			{
				if (String(getGlobalOptions().groups).indexOf('HIV', 0) < 0)
				{
					sw.swMsg.alert('Сообщение', 'Форма "'+ win.title +'" доступна только для пользователей, с указанной группой «Регистр по «ВИЧ»»');
					return false;
				}
			}
		}
	},
	checkAccessEdit: function(disable_msg) {
		return true;
	},
	/**
	 * Открывает соответсвующую акшену форму 
	 * 
	 * @param {string} open_form Название открываемой формы, такое же как название объекта формы
     * @param {string} id Наименование идентификатора таблицы, передаваемого в форму
     * @param {object} oparams
     * @param {string} mode
     * @param {string} title
     */
	openForm: function (open_form, id, oparams, mode, title)
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
		if ( !event.inlist(['MorbusHIVChem','MorbusHIVVac','MorbusHIVSecDiag']) )
		{
			return false;
		}

		data.object_id = data.object_id.split('_')[1];
		var formParams = this.rightPanel.getObjectData(data.object,data.object_id);

		var error = '';
		var question = '';
		var params = new Object();
		var url = '';
		var onSuccess;

		switch ( event ) {	
			case 'MorbusHIVChem':
			case 'MorbusHIVVac':
			case 'MorbusHIVSecDiag':
				error = lang['pri_udalenii_voznikli_oshibki'];
				question = lang['udalit'];
				onSuccess = function(){
					var reload_params = {
						section_code: data.object,
						object_key: data.object +'_id',
						object_value: data.object_id,
						parent_object_key: 'Morbus_id',
						parent_object_value: formParams.Morbus_id,
						param_name: 'MorbusHIV_pid',
						param_value: formParams.MorbusHIV_pid || null,
						section_id: data.object +'List_'+ formParams.MorbusHIV_pid +'_'+ formParams.Morbus_id
					};
					this.rightPanel.reloadViewForm(reload_params);
				}.createDelegate(this);
				url = '/?c=Utils&m=ObjectRecordDelete';
				params['obj_isEvn'] = 'false';
				params['object'] = event;// == data.object
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
								onSuccess();
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
	submitMorbusHIVHtmlForm: function(btn_name, el_data) {
		//log(['submitMorbusHIVHtmlForm',arguments]);
		if(this.allowSpecificEdit == false) {
			return false;
		}
		var save_tb1 = Ext.get('MorbusHIV_'+el_data.object_id+'_save');
		var save_tb2 = Ext.get('MorbusHIV_'+el_data.object_id+'_saveLab');
		var params = this.rightPanel.getObjectData('MorbusHIV',el_data.object_id.split('_')[1]);
		if(!params) {
			return false;
		}
		for(var field_name in this.changed_fields) {
			params[field_name] = this.changed_fields[field_name].value || '0';
		}
		//log(['this.changed_fields',this.changed_fields]);
		params['Evn_pid'] = null;
		params['Mode'] = 'personregister_viewform';
		var url = '/?c=MorbusHIV&m=saveMorbusSpecific';
		var form = this;
		/*
		log(params);
		//if(options.type == 'listid') log(['render',options.field_name,f,params[options.field_name]]);
		*/

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
                    form.buttons[0].el_data = null;
                    form.buttons[0].setDisabled(true);
					var conf;
					for(var field_name in form.changed_fields) {
						conf = form.changed_fields[field_name];
						conf.elOutput.setDisplayed('inline');
						conf.elOutput.update(conf.outputValue);
						if(conf.type == 'id') conf.elOutput.setAttribute('dataid',conf.value);
						if(conf.type == 'listid') conf.elOutput.setAttribute('dataidlist',conf.value);
						conf.elInputWrap.setDisplayed('none');
						if(field_name != 'HIVContingentType_id_list')
							conf.elInput.destroy();
						form.input_cmp_list[conf.elOutputId] = false;
					}
					form.changed_fields = {};
					form.isChange = true;
				}
			}
		});
	},
	createMorbusHIVLabHtmlForm: function(name, el_data) {
		this.createMorbusHIVHtmlForm(name, el_data, true);
	},
	createMorbusHIVHtmlForm: function(name, el_data, is_lab) {
		if(this.allowSpecificEdit == false) {
			return false;
		}
		is_lab = is_lab || false;
		var morbus_id = el_data.object_id.split('_')[1];
		var params = this.rightPanel.getObjectData('MorbusHIV',morbus_id);
		if(typeof params != 'object') {
			return false;
		}
		var form = this;
		var cmp, ct, elinputid, eloutputid, config;
		var empty_value = '<span style="color: #666;">Не указано</span>';
		var onChange = function(conf){
			var save_btn = Ext.get('MorbusHIV_'+el_data.object_id+'_save');
			var save_btn2 = Ext.get('MorbusHIV_'+el_data.object_id+'_saveLab');
			if(conf.is_lab) {
				save_btn2.setDisplayed('block');
			} else {
				save_btn.setDisplayed('block');
			}
            this.buttons[0].el_data = el_data;
            this.buttons[0].setDisabled(false);
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
				,baseParams: options.baseParams
				,field_name: options.field_name
				,listeners:
				{
					blur: function(f) {
                        if (f.disableBlurAction) {
                            return false;
                        }
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
							//if(options.type == 'listid') log(['render',options.field_name,f,params[options.field_name]]);
							f.setValue(params[options.field_name]);
						}
					},
					change: function(f,n,o) {

						if(options.event_type == 'selectConType'){

							var section_id = 'MorbusHIV', name = 'HIVContingentTypeIdList';
							eloutputid = section_id +'_'+ el_data.object_id +'_input'+name;
							elinputid = section_id +'_'+ el_data.object_id +'_inputarea'+name;
							eloutput = Ext.get(eloutputid);
							ct = Ext.get(elinputid);
							ct.setDisplayed('block');
							eloutput.setDisplayed('none');
							ct.update('');
							config = getBaseConfig({
								name: name
								,url: '/?c=MorbusHIV&m=getHIVContingentType'
								,type: 'listid'
								,field_name: 'HIVContingentType_id_list'
								,fieldValue: 'HIVContingentType_id'
								,boxLabel: 'HIVContingentType_Name'
								,elOutputId: eloutputid
								,elInputId: elinputid
								,elOutput: eloutput
								,outputValue: empty_value
								,elInputWrap: ct
								,baseParams: {
									Nationality: n.toString(),
									MorbusHIV_id: params.MorbusHIV_id,
									End_value: 0
								}
								,elInput: null
								,method: 'post'
								,reader: new Ext.data.JsonReader(
									{
										totalProperty: 'totalCount',
										root: 'data',
										fields: [{name: 'HIVContingentType_id'}, {name: 'HIVContingentType_Name'}, {name: 'checked'}]
									})
								,items:[{boxLabel:'Loading'},{boxLabel:'Loading'}]
								,columns: 1
								,vertical: true
								,cbRenderer:function(){}
								,cbHandler:function(){}
							});
							cmp = new sw.Promed.swHIVContingentTypeCheckboxGroup(config);
							cmp.focus(false, 500);
							form.input_cmp_list[eloutputid] = cmp;
							var new_conf = {   // Как только сменили тип гражданства - стираем тип контингента, так как коды контингентов не сходятся
								elOutput: eloutput,
								//elInput: ct,
								elInputWrap: ct,
								field_name: 'HIVContingentType_id_list',
								outputValue: empty_value,
								value: null,
								type: 'listid'
							};
							onChange(new_conf);
						}
						if(options.type == 'listid') {
							var v = f.getValue();
							//log(['change',v]);
							if(Ext.isEmpty(v))
							{
								options.outputValue = empty_value;
								options.value = null;
							}
							else
							{
								options.value = v;
								options.outputValue = f.getRawValue();
							}
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
							var rec = (n)?f.getStore().getById(n):false;
							if(rec) {
								options.value = n;
								if(options.codeField) {
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
					}
				}
			};
		};
		
		var section_id = 'MorbusHIV';
		eloutputid = section_id +'_'+ el_data.object_id +'_input'+name;
		elinputid = section_id +'_'+ el_data.object_id +'_inputarea'+name;
		eloutput = Ext.get(eloutputid);
		ct = Ext.get(elinputid);
		
		switch(name){
			case 'OutendDT'://Дата снятия с диспансерного наблюдения
				if(ct && !this.input_cmp_list[eloutputid]) {
					ct.setDisplayed('block');
					eloutput.setDisplayed('none');
					config = getBaseConfig({
						name: name
						,type: 'date'
						,field_name: 'MorbusHIVOut_endDT'
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
			case 'DiagDT'://Дата установления диагноза ВИЧ-инфекции		
			case 'BlotDT'://Дата постановки реакции иммуноблота
			case 'IFADT'://Дата ИФА
			case 'PCRDT'://Дата ПЦР
			case 'ConfirmDate': //Дата подтверждения диагноза
				if(ct && !this.input_cmp_list[eloutputid]) {
                    var fieldNames = {
                        DiagDT: 'MorbusHIV_DiagDT',
                        BlotDT: 'MorbusHIVLab_BlotDT',
                        IFADT: 'MorbusHIVLab_IFADT',
                        PCRDT: 'MorbusHIVLab_PCRDT',
						ConfirmDate: 'MorbusHIV_confirmDate'
                    };
					ct.setDisplayed('block');
					eloutput.setDisplayed('none');
					config = getBaseConfig({
						name: name
						,is_lab: is_lab
						,type: 'date'
						,field_name: fieldNames[name]
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
			case 'CountCD4'://CD4 Т-лимфоциты Количество (мм), Целое число, 2 разряда
				if(ct && !this.input_cmp_list[eloutputid]) {
					ct.setDisplayed('block');
					eloutput.setDisplayed('none');
					config = getBaseConfig({
						name: name
						,type: 'int'
						,field_name: 'MorbusHIV_'+ name
						,elOutputId: eloutputid
						,elInputId: elinputid
						,elOutput: eloutput
						,outputValue: empty_value
						,elInputWrap: ct
						,elInput: null
					});
					config.width = 100;
					config.plugins = [new Ext.ux.InputTextMask('99', false)];
					config.allowDecimals = false;
					config.allowNegative = false;
					cmp = new Ext.form.NumberField(config);
					cmp.focus(true, 500);
					this.input_cmp_list[eloutputid] = cmp;
				}
				break;
			case 'PartCD4'://CD4 Т-лимфоциты % содержания. Вещественное число, 2,2
				if(ct && !this.input_cmp_list[eloutputid]) {
					ct.setDisplayed('block');
					eloutput.setDisplayed('none');
					config = getBaseConfig({
						name: name
						,type: 'int'//float
						,field_name: 'MorbusHIV_'+ name
						,elOutputId: eloutputid
						,elInputId: elinputid
						,elOutput: eloutput
						,outputValue: empty_value
						,elInputWrap: ct
						,elInput: null
					});
					config.width = 100;
					config.plugins = [new Ext.ux.InputTextMask('99.99', false)];
					//config.maxLength = 5;
					config.allowDecimals = true;
					config.allowNegative = false;
					cmp = new Ext.form.NumberField(config);
					cmp.focus(true, 500);
					this.input_cmp_list[eloutputid] = cmp;
				}
				break;
            case 'NumImmun'://№ иммуноблота (целое число, 5 знаков)
                if(ct && !this.input_cmp_list[eloutputid]) {
                    ct.setDisplayed('block');
                    eloutput.setDisplayed('none');
                    config = getBaseConfig({
                        name: name
                        ,type: 'int'
                        ,field_name: 'MorbusHIV_'+ name
                        ,elOutputId: eloutputid
                        ,elInputId: elinputid
                        ,elOutput: eloutput
                        ,outputValue: empty_value
                        ,elInputWrap: ct
                        ,elInput: null
                    });
                    config.width = 60;
                    config.plugins = [new Ext.ux.InputTextMask('99999', false)];
                    //config.maxLength = 5;
                    config.allowDecimals = false;
                    config.allowNegative = false;
                    cmp = new Ext.form.NumberField(config);
                    cmp.focus(true, 500);
                    this.input_cmp_list[eloutputid] = cmp;
                }
                break;
            case 'EpidemCode'://Эпидемиологический код
                if(ct && !this.input_cmp_list[eloutputid]) {
                    ct.setDisplayed('block');
                    eloutput.setDisplayed('none');
                    config = getBaseConfig({
                        name: name
                        ,type: 'string'
                        ,field_name: 'MorbusHIV_'+ name
                        ,elOutputId: eloutputid
                        ,elInputId: elinputid
                        ,elOutput: eloutput
                        ,outputValue: empty_value
                        ,elInputWrap: ct
                        ,elInput: null
                    });
                    config.width = 300;
                    config.maxLength = 100;
                    cmp = new Ext.form.TextField(config);
                    cmp.focus(true, 500);
                    this.input_cmp_list[eloutputid] = cmp;
                }
                break;
			case 'HIVContingentTypeP'://Гражданство
				if(ct && !this.input_cmp_list[eloutputid]) {
					ct.setDisplayed('block');
					eloutput.setDisplayed('none');
					config = getBaseConfig({
						name: name
						,type: 'id'
						,event_type: 'selectConType'
						,field_name: 'HIVContingentTypeP_id'
						,elOutputId: eloutputid
						,elInputId: elinputid
						,elOutput: eloutput
						,outputValue: empty_value
						,elInputWrap: ct
						,elInput: null
					});
					config.comboData = [
						[100,'Граждане СССР'],
						[200,'Иностранные граждане']
					];
					config.comboFields = [
						{name: 'HIVContingentType_id', type:'int'},
						{name: 'HIVContingentType_Name', type:'string'}
					];
					config.width = 300;
					config.hiddenName = 'HIVContingentTypeP_id';
					config.valueField = 'HIVContingentType_id';
					config.displayField = 'HIVContingentType_Name';
					cmp = new sw.Promed.swStoreInConfigCombo(config);
					cmp.focus(true, 500);
					this.input_cmp_list[eloutputid] = cmp;
				}
				break;
			case 'HIVContingentTypeIdList'://Код контингента
				if(ct && !this.input_cmp_list[eloutputid]) {
					ct.setDisplayed('block');
					eloutput.setDisplayed('none');
					config = getBaseConfig({
						name: name
						,url: '/?c=MorbusHIV&m=getHIVContingentType'
						,type: 'listid'
						,fieldName: 'HIVContingentType_id_list'
						,field_name: 'HIVContingentType_id_list'
						,elOutputId: eloutputid
						,elInputId: elinputid
						,elOutput: eloutput
						,outputValue: empty_value
						,elInputWrap: ct
						,elInput: null
						,method: 'post'
						,baseParams: {
							MorbusHIV_id: params.MorbusHIV_id,
							Nationality: params.HIVContingentTypeP_id,
							End_value: 0
						}
						,reader: new Ext.data.JsonReader(
							{
								totalProperty: 'totalCount',
								root: 'data',
								fields: [{name: 'HIVContingentType_id'}, {name: 'HIVContingentType_Name'}, {name: 'is_checked'}]
							})
						,items:[{boxLabel:'Loading'},{boxLabel:'Loading'}]
						,columns: 1
						,vertical: true
						,cbRenderer:function(){}
						,cbHandler:function(){}
					});
					cmp = new sw.Promed.swHIVContingentTypeCheckboxGroup(config);
					cmp.focus(false, 500);
					this.input_cmp_list[eloutputid] = cmp;
				}
				break;
			case 'HIVPregPathTransType':
			case 'HIVPregInfectStudyType':
			case 'HIVDispOutCauseType':
			case 'HIVInfectType':
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
					config.width = 350;
					config.listWidth = 450;
					config.comboSubject = name;
					config.typeCode = 'int';
					config.autoLoad = true;					
					cmp = new sw.Promed.SwCommonSprCombo(config);
					cmp.focus(true, 500);
					this.input_cmp_list[eloutputid] = cmp;
				}
				break;
			case 'LabAssessmentResultI':
				if(ct && !this.input_cmp_list[eloutputid]) {
					ct.setDisplayed('block');
					eloutput.setDisplayed('none');
					log([
						eloutputid,
						elinputid,
						eloutput
					]);
					config = getBaseConfig({
						name: name
						,type: 'id'
						,field_name: 'LabAssessmentResult_iid'
						,elOutputId: eloutputid
						,elInputId: elinputid
						,elOutput: eloutput
						,outputValue: empty_value
						,elInputWrap: ct
						,elInput: null
					});
					config.width = 350;
					config.listWidth = 450;
					config.comboSubject = 'LabAssessmentResult';
					config.hiddenName = 'LabAssessmentResult_iid';
					config.typeCode = 'int';
					config.autoLoad = true;
					cmp = new sw.Promed.SwCommonSprCombo(config);
					cmp.focus(true, 500);
					this.input_cmp_list[eloutputid] = cmp;
				}
				break;
			case 'LabAssessmentResultC':
				if(ct && !this.input_cmp_list[eloutputid]) {
					ct.setDisplayed('block');
					eloutput.setDisplayed('none');
					config = getBaseConfig({
						name: name
						,type: 'id'
						,field_name: 'LabAssessmentResult_cid'
						,elOutputId: eloutputid
						,elInputId: elinputid
						,elOutput: eloutput
						,outputValue: empty_value
						,elInputWrap: ct
						,elInput: null
					});
					config.width = 350;
					config.listWidth = 450;
					config.comboSubject = 'LabAssessmentResult';
					config.hiddenName = 'LabAssessmentResult_cid';
					config.typeCode = 'int';
					config.autoLoad = true;
					cmp = new sw.Promed.SwCommonSprCombo(config);
					cmp.focus(true, 500);
					this.input_cmp_list[eloutputid] = cmp;
				}
				break;
			case 'DiagD'://Причина смерти
				if(ct && !this.input_cmp_list[eloutputid]) {
					ct.setDisplayed('block');
					eloutput.setDisplayed('none');
					config = getBaseConfig({
						name: name
						,type: 'id'
						,field_name: 'DiagD_id'
						,elOutputId: eloutputid
						,elInputId: elinputid
						,elOutput: eloutput
						,outputValue: empty_value
						,codeField: 'Diag_Code'
						,elInputWrap: ct
						,elInput: null
					});
					config.width = 350;
					config.listWidth = 500;
					config.hiddenName = 'DiagD_id';
					cmp = new sw.Promed.SwDiagCombo(config);
					var dataid = eloutput.getAttribute('dataid');
					cmp.getStore().load({
						params: {where: 'where Diag_id = '+ dataid},
						callback: function(){
							if(this.getStore().getCount() > 0 && dataid && dataid > 0) {
								this.setValue(dataid);
							}
						},
						scope: cmp
					});
					cmp.focus(true, 500);
					this.input_cmp_list[eloutputid] = cmp;
				}
				break;
			case 'Lpuifa'://Учреждение, первично выявившее положительный результат в ИФА
				if(ct && !this.input_cmp_list[eloutputid]) {
					ct.setDisplayed('block');
					eloutput.setDisplayed('none');
					config = getBaseConfig({
						name: name
						,is_lab: true
						,type: 'id'
						,field_name: 'Lpuifa_id'
						,elOutputId: eloutputid
						,elInputId: elinputid
						,elOutput: eloutput
						,outputValue: null
						,elInputWrap: ct
						,elInput: null
					});
					config.width = 200;
					config.hiddenName = 'Lpuifa_id';
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
			case 'TestSystem'://Тип тест-системы. Текстовое поле, 64 MorbusHIVLab_TestSystem
			case 'BlotNum'://N серии. Текстовое поле, 64 MorbusHIVLab_BlotNum
			case 'PCRResult'://Результат ПЦР. Текст, 30 MorbusHIVLab_PCRResult
			case 'IFAResult'://Результат ИФА. Текстовое поле, 30 MorbusHIVLab_IFAResult
			case 'BlotResult'://Выявленные белки и гликопротеиды. Текстовое поле, 100 MorbusHIVLab_BlotResult
				if(ct && !this.input_cmp_list[eloutputid]) {
					ct.setDisplayed('block');
					eloutput.setDisplayed('none');
					config = getBaseConfig({
						name: name
						,is_lab: true
						,type: 'string'
						,field_name: section_id +'Lab_'+ name
						,elOutputId: eloutputid
						,elInputId: elinputid
						,elOutput: eloutput
						,outputValue: null
						,elInputWrap: ct
						,elInput: null
					});
					if(name == 'BlotResult')
						config.maxLength = 100;
					else if (name == 'PCRResult' || name == 'IFAResult')
						config.maxLength = 30;
					else
						config.maxLength = 64;
					cmp = new Ext.form.TextField(config);
					cmp.focus(true, 500);
					this.input_cmp_list[eloutputid] = cmp;
				}
				break;
		}
	},
	openMorbusHIVSpecificForm: function(options) {
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
		log('openMorbusHIVSpecificForm');
		log(options);
		*/

		if(options.action == 'add') {
			object_id = (options.eldata.object_id.split('_').length > 1)?options.eldata.object_id.split('_')[1]:options.eldata.object_id;
			mhdata = options.mhdata || this.rightPanel.getObjectData('MorbusHIV',object_id);
			if(!mhdata) {
				return false;
			}
		} else {
			object_id = (options.eldata.object_id.split('_').length > 1)?options.eldata.object_id.split('_')[1]:options.eldata.object_id;
			data = this.rightPanel.getObjectData(options.object,object_id);
			if(!data) {
				return false;
			}
			mhdata = options.mhdata || this.rightPanel.getObjectData('MorbusHIV',data.Morbus_id);
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
				param_name: 'MorbusHIV_pid',
				param_value: mhdata.MorbusHIV_pid,
				section_id: options.object +'List_'+ mhdata.MorbusHIV_pid +'_'+ mhdata.Morbus_id
			};
			this.rightPanel.reloadViewForm(reload_params);
		}.createDelegate(this);
		
		switch(options.object) {
			case 'MorbusHIVChem':
				win_name = 'swMorbusHIVChemEditWindow';
				if(options.action == 'add') {
					params.action = 'add';
					params.MorbusHIVChem_id = null;
					params.formParams = {MorbusHIV_id: mhdata.MorbusHIV_id, Person_id: this.Person_id, Evn_id: null};
				}
				if(options.action == 'edit') {
					params.action = 'edit';
					params.MorbusHIVChem_id = object_id;
					params.formParams = {MorbusHIV_id: mhdata.MorbusHIV_id, Person_id: this.Person_id};
				}
				break;
			case 'MorbusHIVVac':
				win_name = 'swMorbusHIVVacEditWindow';
				if(options.action == 'add') {
					params.action = 'add';
					params.MorbusHIVVac_id = null;
					params.formParams = {MorbusHIV_id: mhdata.MorbusHIV_id, Person_id: this.Person_id, Evn_id: null};
				}
				if(options.action == 'edit') {
					params.action = 'edit';
					params.MorbusHIVVac_id = object_id;
					params.formParams = {MorbusHIV_id: mhdata.MorbusHIV_id, Person_id: this.Person_id};
				}
				break;
			case 'MorbusHIVSecDiag':
				win_name = 'swMorbusHIVSecDiagEditWindow';
				if(options.action == 'add') {
					params.action = 'add';
					params.MorbusHIVSecDiag_id = null;
					params.formParams = {MorbusHIV_id: mhdata.MorbusHIV_id, Person_id: this.Person_id, Evn_id: null};
				}
				if(options.action == 'edit') {
					params.action = 'edit';
					params.MorbusHIVSecDiag_id = object_id;
					params.formParams = {MorbusHIV_id: mhdata.MorbusHIV_id, Person_id: this.Person_id};
				}
				break;
			default:
				return false;
		}
		getWnd(win_name).show(params);
	},
	loadNodeViewForm: function() 
	{
		this.rightPanel.loadNodeViewForm(this.viewObject,{
			param_name: 'PersonRegister_id',
			param_value: this.PersonRegister_id
		});
	},

	show: function() 
	{
		sw.Promed.swMorbusHIVWindow.superclass.show.apply(this, arguments);
		
		if ( !arguments[0] || !arguments[0].Person_id || !arguments[0].PersonEvn_id || typeof arguments[0].Server_id == 'undefined' || !arguments[0].PersonRegister_id ) {
			sw.swMsg.alert(lang['soobschenie'], lang['nevernyie_parametryi'], function() { this.hide(); }.createDelegate(this) );
			return false;
		}
		
		this.editType = 'all';
        if(arguments[0] && arguments[0].editType){
            this.editType = arguments[0].editType;
        }
		this.Person_id = arguments[0].Person_id;
		this.PersonEvn_id = arguments[0].PersonEvn_id;
		this.Server_id = arguments[0].Server_id;
		this.PersonRegister_id = arguments[0].PersonRegister_id;
		this.userMedStaffFact = arguments[0].userMedStaffFact || sw.Promed.MedStaffFactByUser.last;
		this.onHide = arguments[0].onHide || Ext.emptyFn;
        this.isChange = false;
        this.changed_fields = {};
        this.input_cmp_list = {};
		this.allowSpecificEdit = arguments[0].allowSpecificEdit || false;

		if(arguments[0] && arguments[0].action == 'view')
			this.allowSpecificEdit = false;

		var title = lang['zapis_registra_s_tipom_vich'];
		if (this.allowSpecificEdit) {
			title += ': '+lang['redaktirovanie'];
		} else {
			title += ': '+lang['prosmotr'];
		}
		this.setTitle(title);

		
		this.viewObject = {
			id: 'PersonMorbusHIV_'+ this.Person_id,
			attributes: {
				accessType: (this.allowSpecificEdit == true)?'edit':'view',
				text: 'test',
				object: 'PersonMorbusHIV',
				object_id: 'Person_id',
				object_value: this.Person_id			
			}
		};
		this.loadNodeViewForm();
        this.buttons[0].el_data = null;
        this.buttons[0].setVisible(this.allowSpecificEdit == true);
        this.buttons[0].setDisabled(true);
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
			PersonMorbusHIV: {
				print: {
					actionType: 'view',
					sectionCode: 'PersonMorbusHIV',
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
							//saveUrl: '/?c=PersonMediaData&m=uploadPersonPhoto',
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
						win.openForm('swPersonEditWindow','XXX_id',params,'edit',lang['redaktirovanie_personalnyih_dannyih_patsienta']);
					}
				},
				printMedCard: {
					actionType: 'view',
					sectionCode: 'person_data', 
					handler: function(e, c, d) {
						var data = win.rightPanel.getObjectData('PersonMorbusHIV',d.object_id);
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
									PersonCard_id: data.PersonCard_id
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
						win.openForm('swPersonCardHistoryWindow','XXX_id',params,'edit',lang['istoriya_prikrepleniya']);
					}
				}
			},
			MorbusHIV: {
				toggleDisplayMorbusHIVLab: {
					actionType: 'view',
					sectionCode: 'MorbusHIV',
					handler: function(e, c, d) {
						var id = 'MorbusHIVLabData_'+ d.object_id;
						win.rightPanel.toggleDisplay(id,Ext.get(id).isDisplayed());
					}
				},
				inputDiagDT: { actionType: 'edit', sectionCode: 'MorbusHIV', handler: function(e, c, d) { win.createMorbusHIVHtmlForm('DiagDT', d); }},
				inputOutendDT: { actionType: 'edit', sectionCode: 'MorbusHIV', handler: function(e, c, d) { win.createMorbusHIVHtmlForm('OutendDT', d); }},
				inputCountCD4: { actionType: 'edit', sectionCode: 'MorbusHIV', handler: function(e, c, d) { win.createMorbusHIVHtmlForm('CountCD4', d); }},
                inputPartCD4: { actionType: 'edit', sectionCode: 'MorbusHIV', handler: function(e, c, d) { win.createMorbusHIVHtmlForm('PartCD4', d); }},
                inputNumImmun: { actionType: 'edit', sectionCode: 'MorbusHIV', handler: function(e, c, d) { win.createMorbusHIVHtmlForm('NumImmun', d); }},
                inputConfirmDate: { actionType: 'edit', sectionCode: 'MorbusHIV', handler: function(e, c, d) { win.createMorbusHIVHtmlForm('ConfirmDate', d); }},
                inputEpidemCode: { actionType: 'edit', sectionCode: 'MorbusHIV', handler: function(e, c, d) { win.createMorbusHIVHtmlForm('EpidemCode', d); }},
				inputHIVPregPathTransType: { actionType: 'edit', sectionCode: 'MorbusHIV', handler: function(e, c, d) { win.createMorbusHIVHtmlForm('HIVPregPathTransType', d); }},
				inputHIVPregInfectStudyType: { actionType: 'edit', sectionCode: 'MorbusHIV', handler: function(e, c, d) { win.createMorbusHIVHtmlForm('HIVPregInfectStudyType', d); }},
				inputHIVDispOutCauseType: { actionType: 'edit', sectionCode: 'MorbusHIV', handler: function(e, c, d) { win.createMorbusHIVHtmlForm('HIVDispOutCauseType', d); }},
				inputHIVInfectType: { actionType: 'edit', sectionCode: 'MorbusHIV', handler: function(e, c, d) { win.createMorbusHIVHtmlForm('HIVInfectType', d); }},
				inputLabAssessmentResultI: { actionType: 'edit', sectionCode: 'MorbusHIV', handler: function(e, c, d) { win.createMorbusHIVHtmlForm('LabAssessmentResultI', d); }},
				inputLabAssessmentResultC: { actionType: 'edit', sectionCode: 'MorbusHIV', handler: function(e, c, d) { win.createMorbusHIVHtmlForm('LabAssessmentResultC', d); }},
				inputDiagD: { actionType: 'edit', sectionCode: 'MorbusHIV', handler: function(e, c, d) { win.createMorbusHIVHtmlForm('DiagD', d); }},
				inputHIVContingentTypeP: { actionType: 'edit', sectionCode: 'MorbusHIV', handler: function(e, c, d) { win.createMorbusHIVHtmlForm('HIVContingentTypeP', d); }},
				inputHIVContingentTypeIdList: { actionType: 'edit', sectionCode: 'MorbusHIV', handler: function(e, c, d) { win.createMorbusHIVHtmlForm('HIVContingentTypeIdList', d); }},
							
				inputBlotDT: { actionType: 'edit', sectionCode: 'MorbusHIV', handler: function(e, c, d) { win.createMorbusHIVLabHtmlForm('BlotDT', d); }},
				inputTestSystem: { actionType: 'edit', sectionCode: 'MorbusHIV', handler: function(e, c, d) { win.createMorbusHIVLabHtmlForm('TestSystem', d); }},
				inputBlotNum: { actionType: 'edit', sectionCode: 'MorbusHIV', handler: function(e, c, d) { win.createMorbusHIVLabHtmlForm('BlotNum', d); }},
				inputBlotResult: { actionType: 'edit', sectionCode: 'MorbusHIV', handler: function(e, c, d) { win.createMorbusHIVLabHtmlForm('BlotResult', d); }},
				inputIFADT: { actionType: 'edit', sectionCode: 'MorbusHIV', handler: function(e, c, d) { win.createMorbusHIVLabHtmlForm('IFADT', d); }},
				inputLpuifa: { actionType: 'edit', sectionCode: 'MorbusHIV', handler: function(e, c, d) { win.createMorbusHIVLabHtmlForm('Lpuifa', d); }},
				inputIFAResult: { actionType: 'edit', sectionCode: 'MorbusHIV', handler: function(e, c, d) { win.createMorbusHIVLabHtmlForm('IFAResult', d); }},
				inputPCRDT: { actionType: 'edit', sectionCode: 'MorbusHIV', handler: function(e, c, d) { win.createMorbusHIVLabHtmlForm('PCRDT', d); }},
				inputPCRResult: { actionType: 'edit', sectionCode: 'MorbusHIV', handler: function(e, c, d) { win.createMorbusHIVLabHtmlForm('PCRResult', d); }},
				
				save: { actionType: 'edit', sectionCode: 'MorbusHIV', handler: function(e, c, d) { win.submitMorbusHIVHtmlForm('save',d); } },
				saveLab: { actionType: 'edit', sectionCode: 'MorbusHIV', handler: function(e, c, d) { win.submitMorbusHIVHtmlForm('saveLab',d); } }
			},
			MorbusHIVChem: {
				edit: {
					actionType: 'edit',
					sectionCode: 'MorbusHIVChem',
					dblClick: true,
					handler: function(e, c, d) {
						win.openMorbusHIVSpecificForm({action: 'edit',object: 'MorbusHIVChem', eldata: d});
					}
				},
				'delete': {
					actionType: 'edit',
					sectionCode: 'MorbusHIVChem',
					handler: function(e, c, d) {
						win.deleteEvent('MorbusHIVChem',d);
					}
				},
				add: {
					actionType: 'view',
					sectionCode: 'MorbusHIVChemList',
					handler: function(e, c, d) {
						win.openMorbusHIVSpecificForm({action: 'add',object: 'MorbusHIVChem', eldata: d});
					}
				},
				print: {
					actionType: 'view',
					sectionCode: 'MorbusHIVChemList',
					handler: function(e, c, d) {
						win.rightPanel.printHtml(d.section_id);
					}
				},
				toggleDisplay: {
					actionType: 'view',
					sectionCode: 'MorbusHIVChemList',
					handler: function(e, c, d) {
						var id = 'MorbusHIVChemTable_'+ d.object_id;
						win.rightPanel.toggleDisplay(id,Ext.get(id).isDisplayed());
					}
				}
			},
			MorbusHIVVac: {
				edit: {
					actionType: 'edit',
					sectionCode: 'MorbusHIVVac',
					dblClick: true,
					handler: function(e, c, d) {
						win.openMorbusHIVSpecificForm({action: 'edit',object: 'MorbusHIVVac', eldata: d});
					}
				},
				'delete': {
					actionType: 'edit',
					sectionCode: 'MorbusHIVVac',
					handler: function(e, c, d) {
						win.deleteEvent('MorbusHIVVac',d);
					}
				},
				add: {
					actionType: 'view',
					sectionCode: 'MorbusHIVVacList',
					handler: function(e, c, d) {
						win.openMorbusHIVSpecificForm({action: 'add',object: 'MorbusHIVVac', eldata: d});
					}
				},
				print: {
					actionType: 'view',
					sectionCode: 'MorbusHIVVacList',
					handler: function(e, c, d) {
						win.rightPanel.printHtml(d.section_id);
					}
				},
				toggleDisplay: {
					actionType: 'view',
					sectionCode: 'MorbusHIVVacList',
					handler: function(e, c, d) {
						var id = 'MorbusHIVVacTable_'+ d.object_id;
						win.rightPanel.toggleDisplay(id,Ext.get(id).isDisplayed());
					}
				}
			},
			MorbusHIVSecDiag: {
				edit: {
					actionType: 'edit',
					sectionCode: 'MorbusHIVSecDiag',
					dblClick: true,
					handler: function(e, c, d) {
						win.openMorbusHIVSpecificForm({action: 'edit',object: 'MorbusHIVSecDiag', eldata: d});
					}
				},
				'delete': {
					actionType: 'edit',
					sectionCode: 'MorbusHIVSecDiag',
					handler: function(e, c, d) {
						win.deleteEvent('MorbusHIVSecDiag',d);
					}
				},
				add: {
					actionType: 'view',
					sectionCode: 'MorbusHIVSecDiagList',
					handler: function(e, c, d) {
						win.openMorbusHIVSpecificForm({action: 'add',object: 'MorbusHIVSecDiag', eldata: d});
					}
				},
				print: {
					actionType: 'view',
					sectionCode: 'MorbusHIVSecDiagList',
					handler: function(e, c, d) {
						win.rightPanel.printHtml(d.section_id);
					}
				},
				toggleDisplay: {
					actionType: 'view',
					sectionCode: 'MorbusHIVSecDiagList',
					handler: function(e, c, d) {
						var id = 'MorbusHIVSecDiagTable_'+ d.object_id;
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
                handler: function()
                {
                    if (this.el_data) {
                        win.submitMorbusHIVHtmlForm('save', this.el_data);
                    }
                },
                el_data: null,
                iconCls: 'save16',
                tooltip: BTN_FRMSAVE_TIP,
                text: BTN_FRMSAVE
            }, {
				text: '-'
			},
			HelpButton(this),
			{
				handler: function() 
				{
                    win.hide();
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
		sw.Promed.swMorbusHIVWindow.superclass.initComponent.apply(this, arguments);
	}
});