/**
 * swMorbusNephroWindow - Форма просмотра записи регистра с типом «Нефрология»
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * @package      Nephro
 * @access       public
 * @copyright    Copyright (c) 2009-2014 Swan Ltd.
 * @author       Alexander Permyakov
 * @version      11.2014
 */
sw.Promed.swMorbusNephroWindow = Ext.extend(sw.Promed.BaseForm, 
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
    winTitle: lang['zapis_registra_po_nefrologii'],
	listeners: {
		'hide': function(win) {
			win.onHide(win.isChange);
			win.input_cmp_list = null;
		},
		'beforeShow': function(win) {
            if(!getWnd('swWorkPlaceMZSpecWindow').isVisible() && !isUserGroup('MIACSuperAdmin'))
            {
    			if (String(getGlobalOptions().groups).indexOf('NephroRegistry', 0) < 0)
    			{
    				sw.swMsg.alert('Сообщение', 'Форма "'+ win.winTitle +'" доступна только для пользователей, с указанной группой «Регистр по нефрологии»');
    				return false;
    			}
            }
		}
	},
	createMorbusNephroHtmlForm: function(name, el_data) {
		if(this.allowSpecificEdit == false) {
			return false;
		}
		var morbus_id = el_data.object_id.split('_')[1];
		var params = this.rightPanel.getObjectData('MorbusNephro',morbus_id);
		if (typeof params != 'object') {
			return false;
		}
		var form = this;
		var cmp, ct, elinputid, eloutputid, config;
		var empty_value = '<span style="color: #666;">Не указано</span>';
        var onChange = function(conf){
            var save_tb1 = Ext.get('MorbusNephro_'+el_data.object_id+'_toolbarMorbusNephro');
            switch(conf.name){
                case 'DialysisType':
                case 'NephroResultType':
                case 'PersonWeight':
                case 'PersonHeight':
                case 'MorbusNephro_begDate':
                case 'MorbusNephro_firstDate':
                case 'MorbusNephro_dialDate':
                case 'NephroDiagConfType':
                case 'NephroDiagConfTypeC':
                case 'NephroCRIType':
                case 'MorbusNephro_transDate':
                case 'KidneyTransplantType':
                case 'MorbusNephro_deadDT':
                case 'DispGroupType':
                case 'MorbusNephro_Treatment':
                case 'MorbusNephro_CRIDinamic':
                case 'MorbusNephro_IsHyperten':
                case 'Lpu':
                case 'MorbusNephro_DistanceToDialysisCenter':
                case 'MorbusNephro_MonitoringBegDate':
                case 'MorbusNephro_MonitoringEndDate':
                case 'MorbusNephro_dialEndDate':
                case 'MorbusNephro_transRejectDate':
                case 'NephroPersonStatus':
                    save_tb1.setDisplayed('block');
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
						options.elInput = f;
						onCancel(options);
					},
					render: function(f) {
						/*if(options.type == 'id') {
							//if(!f.getStore() || f.getStore().getCount()==0) log('not store: ' + options.field_name);
							debugger;
							var dataid = options.elOutput.getAttribute('dataid');
							if(!Ext.isEmpty(dataid)) {
								f.setValue(parseInt(dataid));
							}
						} else */{
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

						if(getRegionNick() == 'ufa'){ //#135648
							if(options.field_name == 'NephroResultType_id') {
								rec = this.store.getById(n);

								ResultType_Code = rec ? rec.get('NephroResultType_Code') : 0;

								var id = options.elInputId.split('_');

								var routineMonitoring = Ext.get('MorbusNephro_'+ id[1] + '_' + id[2] +'_inputMorbusNephro_RoutineMonitoring');
								routineMonitoring.setDisplayed(ResultType_Code == '1' ? 'block':'none');

								isDialysis = ResultType_Code == '2' || ResultType_Code == '3' ? 'block':'none';

								var nephroAccess = Ext.get('MorbusNephro_' + id[1] + '_' + id[2] + '_NephroAccess');
								nephroAccess.setDisplayed(isDialysis);

								nephroPersonStatus = Ext.get('MorbusNephro_' + id[1] + '_' + id[2] + '_NephroPersonStatus');
								nephroPersonStatus.setDisplayed(isDialysis)
							}
						}

						options.elInput = f;
						if (n!=o)
							onChange(options);
					}
				}
			};
		};

		eloutputid = 'MorbusNephro_'+ el_data.object_id +'_input'+name;
		elinputid = 'MorbusNephro_'+ el_data.object_id +'_inputarea'+name;
		eloutput = Ext.get(eloutputid);
		ct = Ext.get(elinputid);


        switch(name){
            // даты
            case 'MorbusNephro_begDate':
            case 'MorbusNephro_firstDate':
            case 'MorbusNephro_dialDate':
            case 'MorbusNephro_dialEndDate':
            case 'MorbusNephro_transDate':
            case 'MorbusNephro_transRejectDate':
            case 'MorbusNephro_deadDT':
            case 'MorbusNephro_MonitoringBegDate':
            case 'MorbusNephro_MonitoringEndDate':
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
            case 'MorbusNephro_DistanceToDialysisCenter':
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
                    config.maxValue = 9999;
                    config.maxLength = 4;
                    config.maxLengthText = langs('Максимальная длина этого поля 4 символа');
                    config.allowDecimals = true;
                    config.allowNegative = false;
                    cmp = new Ext.form.NumberField(config);
                    cmp.focus(true, 200);
                    this.input_cmp_list[eloutputid] = cmp;
                }
                break;
            case 'PersonHeight':
            case 'PersonWeight':
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
                    //config.maskRe = new RegExp("^[0-9]*$");
                    //config.regex = new RegExp("^[0-9]{0,3}$");
                    config.maxValue = 999;
                    config.maxLength = 3;
                    config.maxLengthText = lang['maksimalnaya_dlina_etogo_polya_3_simvola'];
                    config.allowDecimals = false;
                    config.allowNegative = false;
                    cmp = new Ext.form.NumberField(config);
                    cmp.focus(true, 200);
                    this.input_cmp_list[eloutputid] = cmp;
                }
                break;
            case 'MorbusNephro_Treatment':
            case 'MorbusNephro_CRIDinamic':
                if(ct && !this.input_cmp_list[eloutputid]) {
                    ct.setDisplayed('block');
                    eloutput.setDisplayed('none');
                    config = getBaseConfig({
                        name: name
                        ,type: 'string'
                        ,field_name: name
                        ,elOutputId: eloutputid
                        ,elInputId: elinputid
                        ,elOutput: eloutput
                        ,outputValue: empty_value
                        ,elInputWrap: ct
                        ,elInput: null
                    });
                    config.hideLabel = true;
                    config.width = 200;
                    config.maxLength = 100;
                    if ('MorbusNephro_CRIDinamic' == name) {
                        config.maxLength = 64;
                    }
                    cmp = new Ext.form.TextField(config);
                    cmp.focus(true, 500);
                    this.input_cmp_list[eloutputid] = cmp;
                }
                break;
            case 'MorbusNephro_IsHyperten':
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
            case 'NephroDiagConfType':
            case 'NephroDiagConfTypeC':
            case 'NephroCRIType':
            case 'KidneyTransplantType':
            case 'DialysisType':
            case 'DispGroupType':
            case 'NephroResultType':
            case 'NephroPersonStatus':
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
                    config.listWidth = 500;
                    config.comboSubject = name;
                    if ('NephroDiagConfTypeC' == name) {
                        config.comboSubject = 'NephroDiagConfType';
                    }
                    config.typeCode = (name == 'NephroCRIType') ? 'string' : 'int';
                    config.autoLoad = true;
                    cmp = new sw.Promed.SwCommonSprCombo(config);
                    this.input_cmp_list[eloutputid] = cmp;
                    cmp.focus(true, 500);
                }
                break;
            case 'Lpu':
                if(ct && !this.input_cmp_list[eloutputid]) {
                    ct.setDisplayed('block');
                    eloutput.setDisplayed('none');
                    config = getBaseConfig({
                        name: name
                        ,type: 'id'
                        ,field_name: 'DialysisCenter_id'
                        ,elOutputId: eloutputid
                        ,elInputId: elinputid
                        ,elOutput: eloutput
                        ,outputValue: empty_value
                        ,elInputWrap: ct
                        ,elInput: null
                    });
                    config.width = 250;
                    config.listWidth = 500;
                    config.comboSubject = name;
                    config.autoLoad = true;
                    cmp = new sw.Promed.SwLpuOpenedCombo(config);
                    this.input_cmp_list[eloutputid] = cmp;
                    cmp.focus(true, 500);
                }
                break;
        }
	},
	openMorbusNephroSpecificForm: function(options) {
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
			params = {formParams: {}},
            pData = {};

		if(options.action == 'add' || options.action == 'selectIsLast') {
			object_id = (options.eldata.object_id.split('_').length > 1)?options.eldata.object_id.split('_')[1]:options.eldata.object_id;
			mhdata = options.mhdata || this.rightPanel.getObjectData('MorbusNephro',object_id);
			if(!mhdata) {
				return false;
			}
            pData = this.rightPanel.getObjectData('PersonMorbusNephro', this.Person_id);
            if (!pData) {
                return false;
            }
		} else {
			object_id = (options.eldata.object_id.split('_').length > 1)?options.eldata.object_id.split('_')[1]:options.eldata.object_id;
			data = this.rightPanel.getObjectData(options.object,object_id);
			if (!data || data.accessType == 'view') {
				return false;
			}
            if ('EvnDiagNephro' == options.object && data.isMainRec == 1) {
                options.action = 'view';
            }
			mhdata = options.mhdata || this.rightPanel.getObjectData('MorbusNephro',data.MorbusNephro_id);

			if(!mhdata) {
				return false;
			}
		}
		if (options.action == 'selectIsLast') {
			var btn = Ext.get(options.object + 'List_' + options.eldata.object_id + '_selectIsLast'),
				isOnlyLast = btn.hasClass('viewAll') ? 1 : 0;
			this.rightPanel.reloadViewForm({
				section_code: options.object,
				object_key: options.object +'_id',
				object_value: (object_id<0)?data.MorbusNephro_id:object_id,
				parent_object_key: 'MorbusNephro_id',
				parent_object_value: mhdata.MorbusNephro_id,
				param_name: 'MorbusNephro_pid',
				param_value: mhdata.MorbusNephro_pid,
				section_id: options.object +'List_'+ mhdata.MorbusNephro_pid +'_'+ mhdata.MorbusNephro_id,
				isOnlyLast: isOnlyLast,
				callback: function() {
                    log(isOnlyLast);
					var btn = Ext.get(options.object + 'List_' + options.eldata.object_id + '_selectIsLast');
					if (1 == isOnlyLast) {
						btn.removeClass('viewAll');
						btn.update(lang['otobrajat_vse']);
					}
				}
			});
			return true;
		}

        params.callback = function() {
            var reload_params = {
                section_code: options.object,
                object_key: options.object +'_id',
                object_value: (object_id<0)?data.MorbusNephro_id:object_id,
                parent_object_key: 'MorbusNephro_id',
                parent_object_value: mhdata.MorbusNephro_id,
                param_name: 'MorbusNephro_pid',
                param_value: mhdata.MorbusNephro_pid,
                section_id: options.object +'List_'+ mhdata.MorbusNephro_pid +'_'+ mhdata.MorbusNephro_id
            };
			this.rightPanel.reloadViewForm(reload_params);
        }.createDelegate(this);

        switch(options.object) {
            case 'EvnDiagNephro':
            case 'MorbusNephroLab':
            case 'MorbusNephroDisp':
            case 'MorbusNephroDialysis':
            case 'MorbusNephroDrug':
            case 'NephroCommission':
            case 'NephroAccess':
                win_name = 'sw'+options.object+'Window';
                params.action = options.action;
                params[options.object+'_id'] = (params.action=='edit' || params.action=='editout' || params.action=='view')?object_id:null;
                params.formParams = {
                    MorbusNephro_id: mhdata.MorbusNephro_id,
                    Morbus_id: mhdata.Morbus_id,
                    MorbusBase_id: mhdata.MorbusBase_id,
                    Person_id: this.Person_id,
                    PersonEvn_id: pData.PersonEvn_id || null,
                    Server_id: pData.Server_id || null,
                    Evn_id: null
                };
                if(getRegionNick() == 'ufa' && options.object == 'MorbusNephroDisp'){ //#135648
                    personData = this.rightPanel.getObjectData('PersonMorbusNephro', this.Person_id);
                    params.formParams.Person_Age = personData.Person_Age;
                    params.formParams.Person_Sex = personData.Sex_Code;
                    params.onHide = function(respParams) {
                        var wnd = swMorbusNephroWindow;
                        wnd.getLoadMask('Проверка поля "Стадия ХБП').show();
                        wnd.setCRIType(respParams, options.eldata.object_id);
                    }.createDelegate(this);
                }
                break;
            case 'NephroDocument':
                win_name = 'swFileUploadWindow';
                params.enableFileDescription = true;
                params.saveUrl = "/?c=MorbusNephro&m=uploadFile";
                params.saveParams = {
                    MorbusNephro_id: mhdata.MorbusNephro_id,
                    Morbus_id: mhdata.Morbus_id,
                    MorbusBase_id: mhdata.MorbusBase_id,
                    Person_id: this.Person_id,
                    PersonEvn_id: pData.PersonEvn_id || null,
                    Server_id: pData.Server_id || null,
                    Evn_id: null
                }
                break;
            default:
                return false;
        }
        getWnd(win_name).show(params);
		return true;
	},
	/**
	 * Сохраняет данные по специфике
	 * @param btn_name
	 * @param el_data
	 * @return {Boolean}
	 */
	submitMorbusNephroHtmlForm: function(btn_name, el_data) {
		if(this.allowSpecificEdit == false) {
			return false;
		}

		var save_tb1 = Ext.get('MorbusNephro_'+el_data.object_id+'_toolbarMorbusNephro');

		var params = this.rightPanel.getObjectData('MorbusNephro',el_data.object_id.split('_')[1]);
		if(!params) {
			return false;
		}
		for(var field_name in this.changed_fields) {
			params[field_name] = this.changed_fields[field_name].value || '';
		}
        if (params['PersonHeight']) {
            params['PersonHeight_Height'] = params['PersonHeight'];
            delete params['PersonHeight'];
        }
        if (params['PersonWeight']) {
            params['PersonWeight_Weight'] = params['PersonWeight'];
            delete params['PersonWeight'];
        }
        if (params['NephroDiagConfTypeC_id']) {
            params['NephroDiagConfType_cid'] = params['NephroDiagConfTypeC_id'];
            delete params['NephroDiagConfTypeC_id'];
        }
		if(getRegionNick()=='ufa') {
			if(params['DialysisCenter_id'] == null) {
				params['DialysisCenter_id'] = params['Lpu_id'];
			}
			if(this.input_cmp_list) {
				NephroResultType = this.input_cmp_list['MorbusNephro_'+el_data.object_id+'_inputNephroResultType'];
				if(NephroResultType) {
					rec = NephroResultType.getStore().getById( NephroResultType.getValue() );

					NephroResultType_Code = rec ? rec.get('NephroResultType_Code') : null;

					if(NephroResultType_Code != 1) {
						delete params['MorbusNephro_MonitoringBegDate'];
						delete params['MorbusNephro_MonitoringEndDate']
					}
					params['NephroResultType_Code'] = NephroResultType_Code;
				}
			}
		}
        params['PersonHeight_id'] = params.PersonHeight_id || null;
        params['PersonWeight_id'] = params.PersonWeight_id || null;
		params['Evn_pid'] = this.EvnDiagPLStom_id || this.EvnVizitPL_id || this.EvnSection_id || null;
		if (this.EvnVizitPL_id) {
			params['Mode'] = 'evnvizitpl_viewform';
		} else {
		params['Mode'] = 'personregister_viewform';
		}
		var url = '/?c=MorbusNephro&m=doSaveMorbusNephro';
		var form = this;
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
						conf.elOutput.setDisplayed('inline');
						conf.elOutput.update(conf.outputValue);
						if(conf.type == 'id') conf.elOutput.setAttribute('dataid',conf.value);
						conf.elInputWrap.setDisplayed('none');
						conf.elInput.destroy();
						form.input_cmp_list[conf.elOutputId] = false;
					}
					form.changed_fields = {};
					form.isChange = true;
				}
				if(getRegionNick()=='ufa') {
					if(form.input_cmp_list) {
						NephroCRIType = form.input_cmp_list['MorbusNephro_'+el_data.object_id+'_inputNephroCRIType'];
						if(!Ext.isEmpty(NephroCRIType))
							form.updMorbusNephroDisp(el_data.object_id);
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
		if ( !event.inlist(['MorbusNephroDiagSop','MorbusNephroConditChem','MorbusNephroPrescr','EvnDirectionTub','NephroDocument','NephroAccess','NephroCommission', 'MorbusNephroDrug', 'MorbusNephroDialysis']) )
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
			case 'MorbusNephroDiagSop':
			case 'MorbusNephroConditChem':
			case 'MorbusNephroPrescr':
			case 'EvnDirectionTub':
			case 'NephroAccess':
			case 'MorbusNephroDrug':
			case 'MorbusNephroDialysis':
			case 'NephroCommission':
			case 'NephroDocument':
				error = lang['pri_udalenii_voznikli_oshibki'];
				question = lang['udalit'];
				onSuccess = function(){
					var reload_params = {
						section_code: data.object,
						object_key: data.object +'_id',
						object_value: data.object_id,
						parent_object_key: 'MorbusNephro_id',
						parent_object_value: formParams.MorbusNephro_id,
						accessType: (this.allowSpecificEdit == true)?1:0,
						param_name: 'MorbusNephro_pid',
						param_value: formParams.MorbusNephro_pid || null,
						section_id: data.object +'List_'+ formParams.MorbusNephro_pid +'_'+ formParams.MorbusNephro_id
					};
					this.rightPanel.reloadViewForm(reload_params);
				}.createDelegate(this);
				url = '/?c=Utils&m=ObjectRecordDelete';
				params['object'] = data.object;
				params['obj_isEvn'] = (event == 'EvnDirectionTub')?'true':'false';
				params['id'] = data.object_id;
				if(['NephroAccess', 'NephroCommission', 'NephroDocument'].indexOf(event) >= 0) {
					params['scheme'] = 'r2';
				}
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
		if (this.MorbusNephro_pid) {
			this.viewObject.attributes.object_id = 'MorbusNephro_pid';
			this.viewObject.attributes.object_value = this.MorbusNephro_pid;
		}
		if (this.PersonRegister_id) {
		this.rightPanel.loadNodeViewForm(this.viewObject,{
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
	show: function() 
	{
		sw.Promed.swMorbusNephroWindow.superclass.show.apply(this, arguments);
		
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
		this.EvnVizitPL_id = arguments[0].EvnVizitPL_id;
		this.EvnSection_id = arguments[0].EvnSection_id;
		this.Morbus_id = arguments[0].Morbus_id || null;
		this.MorbusNephro_pid = arguments[0].MorbusNephro_pid;
		this.onHide = arguments[0].onHide || Ext.emptyFn;
		this.isChange = false;
		this.allowSpecificEdit = arguments[0].allowSpecificEdit || false;
        if(arguments[0] && arguments[0].action == 'view')
            this.allowSpecificEdit = false;

		if (this.MorbusNephro_pid) {
			this.setTitle('Специфика');
		} else {
        if (this.allowSpecificEdit && arguments[0].action!='view') {
            this.setTitle(this.winTitle + lang['_redaktirovanie']);
        } else {
            this.setTitle(this.winTitle + lang['_prosmotr']);
        }
		}

        this.viewObject = {
			id: 'PersonMorbusNephro_'+ this.Person_id,
			attributes: {
				accessType: (this.allowSpecificEdit == true)?'edit':'view',
				text: 'test',
				object: 'PersonMorbusNephro',
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
			PersonMorbusNephro: {
				print: {
					actionType: 'view',
					sectionCode: 'PersonMorbusNephro',
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
						var data = win.rightPanel.getObjectData('PersonMorbusNephro',d.object_id);

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
								// нужно обновить секцию person_data, пока будем перезагружать все
								win.loadNodeViewForm();
							}
						};
						win.openForm('swPersonCardHistoryWindow','XXX_id',params,'edit',lang['istoriya_prikrepleniya']);
					}
				}
			},
            MorbusNephro: {
                saveMorbusNephro: {
                    actionType: 'edit', sectionCode: 'MorbusNephro', handler: function(e, c, d) {
                        win.submitMorbusNephroHtmlForm('saveMorbusNephro',d);
                    }
                },
                inputPersonWeight: {
                    actionType: 'edit',
                    sectionCode: 'MorbusNephro',
                    handler: function(e, c, d) {
                        win.createMorbusNephroHtmlForm('PersonWeight', d);
                    }
                },
                inputMorbusNephro_begDate: {
                    actionType: 'edit',
                    sectionCode: 'MorbusNephro',
                    handler: function(e, c, d) {
                        win.createMorbusNephroHtmlForm('MorbusNephro_begDate', d);
                    }
                },
                inputMorbusNephro_firstDate: {
                    actionType: 'edit',
                    sectionCode: 'MorbusNephro',
                    handler: function(e, c, d) {
                        win.createMorbusNephroHtmlForm('MorbusNephro_firstDate', d);
                    }
                },
                inputMorbusNephro_dialDate: {
                    actionType: 'edit',
                    sectionCode: 'MorbusNephro',
                    handler: function(e, c, d) {
                        win.createMorbusNephroHtmlForm('MorbusNephro_dialDate', d);
                    }
                },
                inputMorbusNephro_transDate: {
                    actionType: 'edit',
                    sectionCode: 'MorbusNephro',
                    handler: function(e, c, d) {
                        win.createMorbusNephroHtmlForm('MorbusNephro_transDate', d);
                    }
                },
                inputMorbusNephro_deadDT: {
                    actionType: 'edit',
                    sectionCode: 'MorbusNephro',
                    handler: function(e, c, d) {
                        win.createMorbusNephroHtmlForm('MorbusNephro_deadDT', d);
                    }
                },
                inputNephroDiagConfType: {
                    actionType: 'edit',
                    sectionCode: 'MorbusNephro',
                    handler: function(e, c, d) {
                        win.createMorbusNephroHtmlForm('NephroDiagConfType', d);
                    }
                },
                inputNephroCRIType: {
                    actionType: 'edit',
                    sectionCode: 'MorbusNephro',
                    handler: function(e, c, d) {
                        win.createMorbusNephroHtmlForm('NephroCRIType', d);
                    }
                },
                inputKidneyTransplantType: {
                    actionType: 'edit',
                    sectionCode: 'MorbusNephro',
                    handler: function(e, c, d) {
                        win.createMorbusNephroHtmlForm('KidneyTransplantType', d);
                    }
                },
                inputDialysisType: {
                    actionType: 'edit',
                    sectionCode: 'MorbusNephro',
                    handler: function(e, c, d) {
                        win.createMorbusNephroHtmlForm('DialysisType', d);
                    }
                },
                inputDispGroupType: {
                    actionType: 'edit',
                    sectionCode: 'MorbusNephro',
                    handler: function(e, c, d) {
                        win.createMorbusNephroHtmlForm('DispGroupType', d);
                    }
                },
                inputNephroResultType: {
                    actionType: 'edit',
                    sectionCode: 'MorbusNephro',
                    handler: function(e, c, d) {
                        win.createMorbusNephroHtmlForm('NephroResultType', d);
                    }
                },
                inputPersonHeight: {
                    actionType: 'edit',
                    sectionCode: 'MorbusNephro',
                    handler: function(e, c, d) {
                        win.createMorbusNephroHtmlForm('PersonHeight', d);
                    }
                },
                inputMorbusNephro_Treatment: {
                    actionType: 'edit',
                    sectionCode: 'MorbusNephro',
                    handler: function(e, c, d) {
                        win.createMorbusNephroHtmlForm('MorbusNephro_Treatment', d);
                    }
                },
                inputMorbusNephro_CRIDinamic: {
                    actionType: 'edit',
                    sectionCode: 'MorbusNephro',
                    handler: function(e, c, d) {
                        win.createMorbusNephroHtmlForm('MorbusNephro_CRIDinamic', d);
                    }
                },
                inputNephroDiagConfTypeC: {
                    actionType: 'edit',
                    sectionCode: 'MorbusNephro',
                    handler: function(e, c, d) {
                        win.createMorbusNephroHtmlForm('NephroDiagConfTypeC', d);
                    }
                },
                inputMorbusNephro_IsHyperten: {
                    actionType: 'edit',
                    sectionCode: 'MorbusNephro',
                    handler: function(e, c, d) {
                        win.createMorbusNephroHtmlForm('MorbusNephro_IsHyperten', d);
                    }
                },
                inputLpu: {
                    actionType: 'edit',
                    sectionCode: 'MorbusNephro',
                    handler: function(e, c, d) {
                        win.createMorbusNephroHtmlForm('Lpu', d);
                    }
                },
                inputAttachLpu: {
                    actionType: 'edit',
                    sectionCode: 'MorbusNephro',
                    handler: function(e, c, d) {
                        formParams = win.rightPanel.getObjectData('MorbusNephro',d.object_id.split('_')[1]);

                        //элементы
                        btn = Ext.get(c.id);
                        spanLpu = Ext.get(d.section_id + '_AttachLpu');
                        saveBtn = Ext.get(d.section_id + '_toolbarMorbusNephro');

                        //открепление?
                        isAttached = btn.dom.textContent == "Открепить";

                        //если прикреплен то открепляем
                        formParams['DialysisCenter_id'] = isAttached ? '' : getGlobalOptions().lpu_id; 

                        saveBtn.setDisplayed('block');
                        lpu_nick = isAttached ? '<font color="#666">Не указано</font>' : '<b>' + getGlobalOptions().lpu_nick + '</b>';

                        btnName = isAttached ? 'Прикрепить' : 'Открепить';

                        //обновим элементы
                        btn.update(btnName);
                        spanLpu.update(lpu_nick);
                    }
                },
                inputMorbusNephro_DistanceToDialysisCenter: {
                    actionType: 'edit',
                    sectionCode: 'MorbusNephro',
                    handler: function(e, c, d) {
                        win.createMorbusNephroHtmlForm('MorbusNephro_DistanceToDialysisCenter', d);
                    }
                },
                inputMorbusNephro_MonitoringBegDate:{
                    actionType: 'edit',
                    sectionCode: 'MorbusNephro',
                    handler: function(e, c, d) {
                        win.createMorbusNephroHtmlForm('MorbusNephro_MonitoringBegDate', d);
                    }
                },
                inputMorbusNephro_MonitoringEndDate:{
                    actionType: 'edit',
                    sectionCode: 'MorbusNephro',
                    handler: function(e, c, d) {
                        win.createMorbusNephroHtmlForm('MorbusNephro_MonitoringEndDate', d);
                    }
                },
                inputMorbusNephro_dialEndDate: { //#143422
                    actionType: 'edit',
                    sectionCode: 'MorbusNephro',
                    handler: function(e, c, d) {
                        win.createMorbusNephroHtmlForm('MorbusNephro_dialEndDate', d);
                    }
                },
                inputMorbusNephro_transRejectDate: { //#143422
                    actionType: 'edit',
                    sectionCode: 'MorbusNephro',
                    handler: function(e, c, d) {
                        win.createMorbusNephroHtmlForm('MorbusNephro_transRejectDate', d);
                    }
                },
                inputNephroPersonStatus: { //#143422
                    actionType: 'edit',
                    sectionCode: 'MorbusNephro',
                    handler: function(e,c,d) {
                        win.createMorbusNephroHtmlForm('NephroPersonStatus', d);
                    }
                }
            },
            EvnDiagNephro: {
                edit: {
                    actionType: 'edit',
                    sectionCode: 'EvnDiagNephro',
                    dblClick: true,
                    handler: function(e, c, d) {
                        win.openMorbusNephroSpecificForm({action: 'edit',object: 'EvnDiagNephro', eldata: d});
                    }
                },
                'delete': {
                    actionType: 'edit',
                    sectionCode: 'EvnDiagNephro',
                    handler: function(e, c, d) {
                        win.deleteEvent('EvnDiagNephro',d);
                    }
                },
                add: {
                    actionType: 'edit',
                    sectionCode: 'EvnDiagNephroList',
                    handler: function(e, c, d) {
                        win.openMorbusNephroSpecificForm({action: 'add',object: 'EvnDiagNephro', eldata: d});
                    }
                },
                print: {
                    actionType: 'view',
                    sectionCode: 'EvnDiagNephroList',
                    handler: function(e, c, d) {
                        win.rightPanel.printHtml(d.section_id);
                    }
                },
                toggleDisplay: {
                    actionType: 'view',
                    sectionCode: 'EvnDiagNephroList',
                    handler: function(e, c, d) {
                        var id = 'EvnDiagNephroTable_'+ d.object_id;
                        win.rightPanel.toggleDisplay(id,Ext.get(id).isDisplayed());
                    }
                }
            },
            MorbusNephroLab: {
                edit: {
                    actionType: 'edit',
                    sectionCode: 'MorbusNephroLab',
                    dblClick: true,
                    handler: function(e, c, d) {
                        win.openMorbusNephroSpecificForm({action: 'edit',object: 'MorbusNephroLab', eldata: d});
                    }
                },
                'delete': {
                    actionType: 'edit',
                    sectionCode: 'MorbusNephroLab',
                    handler: function(e, c, d) {
                        win.deleteEvent('MorbusNephroLab',d);
                    }
                },
                add: {
                    actionType: 'edit',
                    sectionCode: 'MorbusNephroLabList',
                    handler: function(e, c, d) {
                        win.openMorbusNephroSpecificForm({action: 'add',object: 'MorbusNephroLab', eldata: d});
                    }
                },
				selectIsLast: {
					actionType: 'view',
					sectionCode: 'MorbusNephroLabList',
					handler: function(e, c, d) {
						win.openMorbusNephroSpecificForm({action: 'selectIsLast',object: 'MorbusNephroLab', eldata: d});
					}
				},
                print: {
                    actionType: 'view',
                    sectionCode: 'MorbusNephroLabList',
                    handler: function(e, c, d) {
                        win.rightPanel.printHtml(d.section_id);
                    }
                },
                toggleDisplay: {
                    actionType: 'view',
                    sectionCode: 'MorbusNephroLabList',
                    handler: function(e, c, d) {
                        var id = 'MorbusNephroLabTable_'+ d.object_id;
                        win.rightPanel.toggleDisplay(id,Ext.get(id).isDisplayed());
                    }
                }
            },
            MorbusNephroDisp: {
                edit: {
                    actionType: 'edit',
                    sectionCode: 'MorbusNephroDisp',
                    dblClick: true,
                    handler: function(e, c, d) {
                        win.openMorbusNephroSpecificForm({action: 'edit',object: 'MorbusNephroDisp', eldata: d});
                    }
                },
                'delete': {
                    actionType: 'edit',
                    sectionCode: 'MorbusNephroDisp',
                    handler: function(e, c, d) {
                        win.deleteEvent('MorbusNephroDisp',d);
                    }
                },
                add: {
                    actionType: 'edit',
                    sectionCode: 'MorbusNephroDispList',
                    handler: function(e, c, d) {
                        win.openMorbusNephroSpecificForm({action: 'add',object: 'MorbusNephroDisp', eldata: d});
                    }
                },
				selectIsLast: {
					actionType: 'view',
					sectionCode: 'MorbusNephroDispList',
					handler: function(e, c, d) {
						win.openMorbusNephroSpecificForm({action: 'selectIsLast',object: 'MorbusNephroDisp', eldata: d});
					}
				},
                print: {
                    actionType: 'view',
                    sectionCode: 'MorbusNephroDispList',
                    handler: function(e, c, d) {
                        win.rightPanel.printHtml(d.section_id);
                    }
                },
                toggleDisplay: {
                    actionType: 'view',
                    sectionCode: 'MorbusNephroDispList',
                    handler: function(e, c, d) {
                        var id = 'MorbusNephroDispTable_'+ d.object_id;
                        win.rightPanel.toggleDisplay(id,Ext.get(id).isDisplayed());
                    }
                }
			},
			MorbusNephroDialysis: {
                edit: {
                    actionType: 'edit',
                    sectionCode: 'MorbusNephroDialysis',
                    dblClick: true,
                    handler: function(e, c, d) {
                        win.openMorbusNephroSpecificForm({action: 'edit',object: 'MorbusNephroDialysis', eldata: d});
                    }
                },
				editout: {
                    actionType: 'edit',
                    sectionCode: 'MorbusNephroDialysis',
                    dblClick: true,
                    handler: function(e, c, d) {
                        win.openMorbusNephroSpecificForm({action: 'editout',object: 'MorbusNephroDialysis', eldata: d});
                    }
                },
                'delete': {
                    actionType: 'edit',
                    sectionCode: 'MorbusNephroDialysis',
                    handler: function(e, c, d) {
                        win.deleteEvent('MorbusNephroDialysis',d);
                    }
                },
                add: {
                    actionType: 'edit',
                    sectionCode: 'MorbusNephroDialysisList',
                    handler: function(e, c, d) {
                        win.openMorbusNephroSpecificForm({action: 'add',object: 'MorbusNephroDialysis', eldata: d});
                    }
                },
                print: {
                    actionType: 'view',
                    sectionCode: 'MorbusNephroDialysisList',
                    handler: function(e, c, d) {
                        win.rightPanel.printHtml(d.section_id);
                    }
                },
                toggleDisplay: {
                    actionType: 'view',
                    sectionCode: 'MorbusNephroDialysisList',
                    handler: function(e, c, d) {
                        var id = 'MorbusNephroDialysisTable_'+ d.object_id;
                        win.rightPanel.toggleDisplay(id,Ext.get(id).isDisplayed());
                    }
                }
			},
			MorbusNephroDrug: {
				view: {
					actionType: 'view',
					dblClick: true,
					sectionCode: 'MorbusNephroDrug',
					handler: function(e, c, d) {
						win.openMorbusNephroSpecificForm({action: 'view',object: 'MorbusNephroDrug', eldata: d});
					}
				},
				add: {
					actionType: 'edit',
					sectionCode: 'MorbusNephroDrugList',
					handler: function(e, c, d) {
						win.openMorbusNephroSpecificForm({action: 'add',object: 'MorbusNephroDrug', eldata: d});
					}
				},
				edit: {
					actionType: 'edit',
					sectionCode: 'MorbusNephroDrug',
					handler: function(e, c, d) {
						win.openMorbusNephroSpecificForm({action: 'edit',object: 'MorbusNephroDrug', eldata: d});
					}
				},
				delete: {
					actionType: 'edit',
					sectionCode: 'MorbusNephroDrug',
					handler: function(e, c, d) {
						win.deleteEvent('MorbusNephroDrug',d);
					}
				},
				selectIsLast: {
					actionType: 'view',
					sectionCode: 'MorbusNephroDrugList',
					handler: function(e, c, d) {
						win.openMorbusNephroSpecificForm({action: 'selectIsLast',object: 'MorbusNephroDrug', eldata: d});
					}
				},
				print: {
					actionType: 'view',
					sectionCode: 'MorbusNephroDrugList',
					handler: function(e, c, d) {
						win.rightPanel.printHtml(d.section_id);
					}
				},
				toggleDruglay: {
					actionType: 'view',
					sectionCode: 'MorbusNephroDrugList',
					handler: function(e, c, d) {
						var id = 'MorbusNephroDrugTable_'+ d.object_id;
						win.rightPanel.toggleDisplay(id,Ext.get(id).isDisplayed());
					}
				}
			},
			NephroCommission: {
				edit: {
					actionType: 'edit',
					sectionCode: 'NephroCommission',
					dblClick: true,
					handler: function(e, c, d) {
						win.openMorbusNephroSpecificForm({action: 'edit',object: 'NephroCommission', eldata: d});
					}
				},
				delete: {
					actionType: 'edit',
					sectionCode: 'NephroCommission',
					handler: function(e, c, d) {
						win.deleteEvent('NephroCommission',d);
					}
				},
				add: {
					actionType: 'edit',
					sectionCode: 'NephroCommissionList',
					handler: function(e, c, d) {
						win.openMorbusNephroSpecificForm({action: 'add',object: 'NephroCommission', eldata: d});
					}
				},
				toggleDisplay: {
					actionType: 'view',
					sectionCode: 'NephroCommissionList',
					handler: function(e, c, d) {
						var id = 'NephroCommissionTable_'+ d.object_id;
						win.rightPanel.toggleDisplay(id,Ext.get(id).isDisplayed());
					}
				},
				selectIsLast: {
					actionType: 'view',
					sectionCode: 'NephroCommissionList',
					handler: function(e, c, d) {
						win.openMorbusNephroSpecificForm({action: 'selectIsLast',object: 'NephroCommission', eldata: d});
					}
				}
			},
			NephroAccess: {
				edit: {
					actionType: 'edit',
					sectionCode: 'NephroAccess',
					dblClick: true,
					handler: function(e, c, d) {
						var mhdata = new Object();
						mhdata.NephroAccess_id = d.object_id.split('_')[1];
						win.openMorbusNephroSpecificForm({action: 'edit',object: 'NephroAccess', eldata: d});
					}
				},
				delete: {
					actionType: 'edit',
					sectionCode: 'NephroAccess',
					handler: function(e, c, d) {
						win.deleteEvent('NephroAccess',d);
					}
				},
				add: {
					actionType: 'edit',
					sectionCode: 'NephroAccessList',
					handler: function(e, c, d) {
						win.openMorbusNephroSpecificForm({action: 'add',object: 'NephroAccess', eldata: d});
					}
				},
				toggleDisplay: {
					actionType: 'view',
					sectionCode: 'NephroAccessList',
					handler: function(e, c, d) {
						var id = 'NephroAccessTable_'+ d.object_id;
						win.rightPanel.toggleDisplay(id,Ext.get(id).isDisplayed());
					}
				},
				selectIsLast: {
					actionType: 'view',
					sectionCode: 'NephroAccessList',
					handler: function(e, c, d) {
						win.openMorbusNephroSpecificForm({action: 'selectIsLast',object: 'NephroAccess', eldata: d});
					}
				}
			},
			NephroDocument: {
				add: {
					accessType: 'edit',
					sectionCode: 'NephroDocumentList',
					handler: function(e, c, d) {
						win.openMorbusNephroSpecificForm({action: 'add', object: 'NephroDocument', eldata:d});
					}
				},
				delete: {
					sectionCode: 'NephroDocument',
					handler: function(e, c, d) {
						win.deleteEvent('NephroDocument',d);
					}
				},
				download: {
					sectionCode: 'NephroDocument',
					handler: function(e, c, d) {
						var NephroDocument_id = d.object_id.split('_')[1];
						window.open('/?c=NephroDocument&m=getDocument&NephroDocument_id='+NephroDocument_id);
					}
				},
				selectIsLast: {
					actionType: 'view',
					sectionCode: 'NephroDocumentList',
					handler: function(e, c, d) {
						win.openMorbusNephroSpecificForm({action: 'selectIsLast',object: 'NephroDocument', eldata: d});
					}
				},
				toggleDisplay: {
					actionType: 'view',
					sectionCode: 'NephroDocumentList',
					handler: function(e, c, d) {
						var id = 'NephroDocumentTable_'+ d.object_id;
						win.rightPanel.toggleDisplay(id,Ext.get(id).isDisplayed());
					}
				}
			},
			NephroBloodCreatinine: {
				selectIsLast: {
					actionType: 'view',
					sectionCode: 'NephroBloodCreatinineList',
					handler: function(e, c, d) {
						win.openMorbusNephroSpecificForm({action: 'selectIsLast',object: 'NephroBloodCreatinine', eldata: d});
					}
				},
				toggleDisplay: {
					actionType: 'view',
					sectionCode: 'NephroBloodCreatinineList',
					handler: function(e, c, d) {
						var id = 'NephroBloodCreatinineTable_'+ d.object_id;
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
		sw.Promed.swMorbusNephroWindow.superclass.initComponent.apply(this, arguments);
	},

	/** #135648
	 * Устанавливает автоматически высчитанное значение CRIType_id в поле "Стадия ХБП"
	 * если Показатель "Креатинин крови" последний в списке "Динамическое наблюдение"
	 * вызывается при закрытии окна "Динамическое наблюдение"
	 * @param array params {
	 *    @param int CRIType_id
	 *    @param int MorbusNephro_id
	 *    @param date MorbusNephroRate_rateDT
	 * }
	 */
	setCRIType: function(params) {
		if(!params.CRIType_id) { this.getLoadMask().hide(); return;}

		var wnd = swMorbusNephroWindow;
		Ext.Ajax.request({
			url: '/?c=MorbusNephro&m=getLastRate',
			params: { MorbusNephro_id: params.MorbusNephro_id },
			callback: function(options, success, response) {
				wnd.getLoadMask().hide();
				if(success) {
					var result = Ext.util.JSON.decode(response.responseText);

					if(!result.MorbusNephroRate_rateDT) return;

					lastRateDate = Date.parse(result.MorbusNephroRate_rateDT).format('Y-m-d');
					currRateDate = new Date(params.MorbusNephroRate_rateDT).format('Y-m-d');

					if(lastRateDate > currRateDate) return;


					CRIType_inputId = 'MorbusNephro_' + wnd.Person_id + '_' + params.MorbusNephro_id + "_inputNephroCRIType";
					wnd.createMorbusNephroHtmlForm('NephroCRIType', { object_id: wnd.Person_id + '_' + params.MorbusNephro_id  });

					CRITypeCombo = wnd.input_cmp_list[CRIType_inputId];
					CRITypeCombo.getStore().on('load', function() {
						CRITypeCombo.setValue(params.CRIType_id);
						CRITypeCombo.fireEvent('change',CRITypeCombo, params.CRIType_id);
						CRITypeCombo.collapse();
					});
					CRITypeCombo.getStore().load();
					CRITypeCombo.focus();
				} else {
					sw.swMsg.alert('Ошибка сервера','Установите в поле "Стадия ХБП" значение полученное при расчете СКФ');
				}
			}
		});
	},

	updMorbusNephroDisp: function(object_id) {
		if(!object_id) return;
		objectIdArray = object_id.split('_');
		var btn = Ext.get('MorbusNephroDispList_' + object_id + '_selectIsLast'),
		isOnlyLast = btn.hasClass('viewAll') ? 0 : 1;

		swMorbusNephroWindow.rightPanel.reloadViewForm({
			section_code: 'MorbusNephroDisp',
			object_key: 'MorbusNephroDisp_id',
			object_value: objectIdArray[1],
			parent_object_key: 'MorbusNephro_id',
			parent_object_value: objectIdArray[1],
			param_name: 'MorbusNephro_pid',
			param_value: objectIdArray[0],
			section_id: 'MorbusNephroDispList_'+object_id,
			isOnlyLast: 0
		})
	}
});
