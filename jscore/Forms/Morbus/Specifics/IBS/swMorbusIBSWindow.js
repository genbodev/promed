/**
 * swMorbusIBSWindow - Форма просмотра/редактирования записи регистра с типом «ИБС»
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * @package      IBS
 * @access       public
 * @copyright    Copyright (c) 2009-2014 Swan Ltd.
 * @author       Alexander Permyakov
 * @version      12.2014
 */
sw.Promed.swMorbusIBSWindow = Ext.extend(sw.Promed.BaseForm, 
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
	winTitle: lang['zapis_registra_ibs'],
	listeners: {
		'hide': function(win) {
			win.onHide(win.isChange);
		},
		'beforeShow': function(win) {
			if (String(getGlobalOptions().groups).indexOf('IBSRegistry', 0) < 0 && !getWnd('swWorkPlaceMZSpecWindow').isVisible())
			{
				sw.swMsg.alert('Сообщение', 'Форма "'+ win.title +'" доступна только для пользователей, с указанной группой «Оператор регистра ИБС»');
				return false;
			}
		}
	},
	createMorbusIBSHtmlForm: function(name, el_data) {
		if(this.allowSpecificEdit == false) {
			return false;
		}
		var morbus_id = el_data.object_id.split('_')[1];
        var morbus_rec = this.rightPanel.viewFormDataStore.getById('MorbusIBS_'+ morbus_id);
		if (!morbus_rec || !morbus_rec.data) {
			return false;
		}
        var params = morbus_rec.data;
		var form = this;
		var cmp, ct, elinputid, eloutputid, config, eloutput;
		var empty_value = '<span style="color: #666;">Не указано</span>';
        var onChange = function(conf){
            var save_tb1 = Ext.get('MorbusIBS_'+el_data.object_id+'_toolbarMorbusIBS');
            switch(conf.name){
				case 'UslugaComplex':
				case 'Diag':
				case 'MorbusIBS_FuncClass':
				case 'MorbusIBS_IsStenocardia':
				case 'MorbusIBS_IsEchocardiography':
				case 'MorbusIBS_IsHalterMonitor':
				case 'MorbusIBS_IsMyocardInfarct':
				case 'MorbusIBS_IsMyocardIschemia':
				case 'MorbusIBS_IsFirstStenocardia':
				case 'MorbusIBS_IsNoStableStenocardia':
				case 'MorbusIBS_IsRiseTS':
				case 'MorbusIBS_IsSaveIschemia':
				case 'MorbusIBS_IsBackStenocardia':
				case 'MorbusIBS_IsShunting':
				case 'MorbusIBS_IsStenting':
				case 'MorbusIBS_IsKGIndication':
				case 'MorbusIBS_IsKGConsent':
				case 'MorbusIBS_IsKGFinished':
				case 'IBSType':
				case 'IBSStressTest':
                    save_tb1.setDisplayed('block');
                    break;
            }

            var IBSType_id = params['IBSType_id'],
                el = Ext.get('MorbusIBS_'+el_data.object_id+'_outputIBSType');
            if ('Diag' == conf.name && conf.value) {
                morbus_rec.set('Diag_id', conf.value);
                morbus_rec.set('Diag_Name', conf.outputValue);
                IBSType_id = calculationIBSTypeId(conf.outputValue, params['MorbusIBS_IsSaveIschemia'], params['MorbusIBS_IsRiseTS']);
            }
            if ('MorbusIBS_IsSaveIschemia' == conf.name && conf.value) {
                morbus_rec.set('MorbusIBS_IsSaveIschemia', conf.value);
                IBSType_id = calculationIBSTypeId(params['Diag_Name'], conf.value, params['MorbusIBS_IsRiseTS']);
            }
            if ('MorbusIBS_IsRiseTS' == conf.name && conf.value) {
                morbus_rec.set('MorbusIBS_IsRiseTS', conf.value);
                IBSType_id = calculationIBSTypeId(params['Diag_Name'], params['MorbusIBS_IsSaveIschemia'], conf.value);
            }
            if (el && IBSType_id != params['IBSType_id']) {
                morbus_rec.set('IBSType_id', IBSType_id);
                el.update(2==IBSType_id?lang['podozrenie']:lang['podtverjdeno']);
            }
            log(el, IBSType_id, params['IBSType_id'] );
            morbus_rec.commit(true);
            form.rightPanel.viewFormDataStore.commitChanges();

            if(!form.changed_fields) form.changed_fields = {};
            form.changed_fields[conf.field_name] = conf;
        };

        var onCancel = function(conf){
            if(!form.changed_fields) form.changed_fields = {};
            if(!form.changed_fields[conf.field_name]) {
                conf.elOutput.setDisplayed('inline');
                conf.elInputWrap.setDisplayed('none');
                conf.elInput.destroy();
                form.input_cmp_list[conf.elOutputId] = false;
            }
        };
        
        if(!form.input_cmp_list) form.input_cmp_list = {};

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
						if(options.type == 'id' && options.name == 'Diag') {
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
                        if (typeof options.getFieldRawValue != 'function') {
                            options.getFieldRawValue = function(f, n, rec) {
                                var value = n || empty_value;
                                if (options.type == 'date') {
                                    value = (n)?n.format('d.m.Y'):empty_value;
                                }
                                if (options.type == 'id') {
                                    value = (rec)?rec.get(f.displayField):empty_value;
                                }
                                return value;
                            };
                        }
                        options.outputValue = options.getFieldRawValue(f, n, null);
                        options.value = n || null;
                        if (options.type == 'date') {
                            options.outputValue = options.getFieldRawValue(f, n, null);
                            options.value = (n)?n.format('d.m.Y'):null;
                        }
                        if (options.type == 'id') {
                            options.rec = (n)?f.getStore().getById(n):null;
                            options.outputValue = options.getFieldRawValue(f, n, options.rec);
                        }
                        options.elInput = f;
                        if (n!=o)
                            onChange(options);
					}
				}
			};
		};

		eloutputid = 'MorbusIBS_'+ el_data.object_id +'_input'+name;
		elinputid = 'MorbusIBS_'+ el_data.object_id +'_inputarea'+name;
		eloutput = Ext.get(eloutputid);
		ct = Ext.get(elinputid);


        switch(name){
            case 'UslugaComplex':
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
                    config.editable = false;
                    config.width = 400;
                    config.listWidth = 600;
                    config.to = 'MorbusIBS';
                    cmp = new sw.Promed.SwUslugaComplexNewCombo(config);
                    cmp.clearBaseParams();
                    cmp.setUslugaComplexDate(getGlobalOptions().date);
                    cmp.setUslugaCategoryList(['gost2011']);
                    cmp.setUslugaComplex2011Id('200897');
                    cmp.getStore().baseParams.query = 'A06.10.006';
                    cmp.getStore().removeAll();
                    cmp.getStore().load({callback: function(){
                        cmp.setValue(cmp.getValue());
                        cmp.focus(true, 500);
                    }, params: {
                        UslugaComplex_id: cmp.getValue()
                    }});
                    this.input_cmp_list[eloutputid] = cmp;
                }
                break;
            case 'Diag':
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
                        ,codeField: name + '_Code'
                        ,getFieldRawValue: function(f, n, rec) {
                            if (rec) {
                                return rec.get('Diag_Code') + ' ' + rec.get('Diag_Name');
                            }
                            return empty_value;
                        }
                    });
                    config.width = 400;
                    config.MorbusType_SysNick = 'ibs';
                    var listeners = Ext.apply({}, config.listeners);
                    delete config.listeners;
                    //config.additQueryFilter = "(Diag_Code like '%I20.' or Diag_Code like '%I21.' or Diag_Code like '%I22.' or Diag_Code like '%I23.' or Diag_Code like '%I24.' or Diag_Code like '%I25.')";
                    //config.additClauseFilter = '(record["Diag_Code"].search(new RegExp("^I2[0-5]", "i"))>=0)';
                    cmp = new sw.Promed.SwDiagCombo(config);
                    cmp.on('blur', listeners.blur);
                    cmp.on('render', listeners.render);
                    cmp.onChange = listeners.change;
                    cmp.getStore().filterBy(function () {
                        return false;
                    });
                    var dataid = params['Diag_id'] || null;
                    if (dataid && dataid > 0) {
                        cmp.getStore().load({
                            params: {where: 'where Diag_id = '+ dataid, clause: {where: 'record["Diag_id"] == "'+ dataid +'"' }},
                            callback: function(){
                                if (this.getStore().getCount() > 0) {
                                    this.setValue(dataid);
                                }
                            },
                            scope: cmp
                        });
                    }
                    cmp.focus(true, 500);
                    
                    this.input_cmp_list[eloutputid] = cmp;
                }
                break;
            case 'MorbusIBS_FuncClass':
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
                    config.minValue = 1;
                    config.maxValue = 4;
                    config.maxLength = 1;
                    config.maxLengthText = lang['maksimalnaya_dlina_etogo_polya_3_simvola'];
                    config.allowDecimals = false;
                    config.allowNegative = false;
                    cmp = new Ext.form.NumberField(config);
                    cmp.focus(true, 200);
                    this.input_cmp_list[eloutputid] = cmp;
                }
                break;
            case 'MorbusIBS_IsStenocardia':
            case 'MorbusIBS_IsEchocardiography':
            case 'MorbusIBS_IsHalterMonitor':
            case 'MorbusIBS_IsMyocardInfarct':
            case 'MorbusIBS_IsMyocardIschemia':
            case 'MorbusIBS_IsFirstStenocardia':
            case 'MorbusIBS_IsNoStableStenocardia':
            case 'MorbusIBS_IsRiseTS':
            case 'MorbusIBS_IsSaveIschemia':
            case 'MorbusIBS_IsBackStenocardia':
            case 'MorbusIBS_IsShunting':
            case 'MorbusIBS_IsStenting':
            case 'MorbusIBS_IsKGIndication':
            case 'MorbusIBS_IsKGConsent':
            case 'MorbusIBS_IsKGFinished':
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
            case 'IBSType':
            case 'IBSStressTest'://MorbusIBS_StressTest
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
                    if ('IBSStressTest' == name) {
                        config.comboSubject = 'TubHistolResultType';
                    }
                    config.typeCode = 'int';
                    config.autoLoad = true;
                    cmp = new sw.Promed.SwCommonSprCombo(config);
                    this.input_cmp_list[eloutputid] = cmp;
                    cmp.focus(true, 500);
                }
                break;
        }
	},
	/**
	 * Сохраняет данные по специфике
	 * @param btn_name
	 * @param el_data
	 * @return {Boolean}
	 */
	submitMorbusIBSHtmlForm: function(btn_name, el_data) {
		if(this.allowSpecificEdit == false) {
			return false;
		}

		var save_tb1 = Ext.get('MorbusIBS_'+el_data.object_id+'_toolbarMorbusIBS');

		var params = this.rightPanel.getObjectData('MorbusIBS',el_data.object_id.split('_')[1]);
		if(!params) {
			return false;
		}
		for(var field_name in this.changed_fields) {
			params[field_name] = this.changed_fields[field_name].value || '';
		}
        if (params['IBSStressTest_id']) {
            params['MorbusIBS_StressTest'] = params['IBSStressTest_id'];
            delete params['IBSStressTest_id'];
        }
        //params['Evn_pid'] = params.MorbusIBS_pid;
		params['Evn_pid'] = null;
		params['Mode'] = 'personregister_viewform';
		var url = '/?c=MorbusIBS&m=doSaveMorbusIBS';
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
        var wnd = this;
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
                params.action = (wnd.editType=='onlyRegister')?'view':'edit';
            if(open_form == 'swPersonEditWindow')
                params.readOnly = (wnd.editType=='onlyRegister')?true:false;
			getWnd(open_form).show(params);
		}
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
		sw.Promed.swMorbusIBSWindow.superclass.show.apply(this, arguments);

		//log(arguments[0]);
		if ( !arguments[0] || !arguments[0].Person_id || !arguments[0].PersonRegister_id ) {
			sw.swMsg.alert(lang['soobschenie'], lang['nevernyie_parametryi'], function() { this.hide(); }.createDelegate(this) );
			return false;
		}
        
        this.editType = 'all';
        if(arguments[0] && arguments[0].editType){
            this.editType = arguments[0].editType;
        }

		this.Person_id = arguments[0].Person_id;
		this.PersonRegister_id = arguments[0].PersonRegister_id;
		this.userMedStaffFact = arguments[0].userMedStaffFact || sw.Promed.MedStaffFactByUser.last;
		this.onHide = arguments[0].onHide || Ext.emptyFn;
		this.isChange = false;
		this.allowSpecificEdit = arguments[0].allowSpecificEdit || false;

        if(arguments[0] && arguments[0].action == 'view')
        {
            this.allowSpecificEdit = false;
        }

        if (this.allowSpecificEdit) {
            this.setTitle(this.winTitle + lang['_redaktirovanie']);
        } else {
            this.setTitle(this.winTitle + lang['_prosmotr']);
        }
		this.viewObject = {
			id: 'PersonMorbusIBS_'+ this.Person_id,
			attributes: {
				accessType: (this.allowSpecificEdit == true)?'edit':'view',
				text: 'test',
				object: 'PersonMorbusIBS',
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
			PersonMorbusIBS: {
				print: {
					actionType: 'view',
					sectionCode: 'PersonMorbusIBS',
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
						var data = win.rightPanel.getObjectData('PersonMorbusIBS',d.object_id);

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
            MorbusIBS: {
                saveMorbusIBS: {
                    actionType: 'edit', sectionCode: 'MorbusIBS', handler: function(e, c, d) {
                        win.submitMorbusIBSHtmlForm('saveMorbusIBS',d);
                    }
                },
                inputDiag: {
                    actionType: 'edit',
                    sectionCode: 'MorbusIBS',
                    handler: function(e, c, d) {
                        win.createMorbusIBSHtmlForm('Diag', d);
                    }
                },
                inputIBSStressTest: {
                    actionType: 'edit',
                    sectionCode: 'MorbusIBS',
                    handler: function(e, c, d) {
                        win.createMorbusIBSHtmlForm('IBSStressTest', d);
                    }
                },
                inputMorbusIBS_FuncClass: {
                    actionType: 'edit',
                    sectionCode: 'MorbusIBS',
                    handler: function(e, c, d) {
                        win.createMorbusIBSHtmlForm('MorbusIBS_FuncClass', d);
                    }
                },
                inputMorbusIBS_IsStenocardia: {
                    actionType: 'edit',
                    sectionCode: 'MorbusIBS',
                    handler: function(e, c, d) {
                        win.createMorbusIBSHtmlForm('MorbusIBS_IsStenocardia', d);
                    }
                },
                inputMorbusIBS_IsEchocardiography: {
                    actionType: 'edit',
                    sectionCode: 'MorbusIBS',
                    handler: function(e, c, d) {
                        win.createMorbusIBSHtmlForm('MorbusIBS_IsEchocardiography', d);
                    }
                },
                inputMorbusIBS_IsHalterMonitor: {
                    actionType: 'edit',
                    sectionCode: 'MorbusIBS',
                    handler: function(e, c, d) {
                        win.createMorbusIBSHtmlForm('MorbusIBS_IsHalterMonitor', d);
                    }
                },
                inputMorbusIBS_IsMyocardInfarct: {
                    actionType: 'edit',
                    sectionCode: 'MorbusIBS',
                    handler: function(e, c, d) {
                        win.createMorbusIBSHtmlForm('MorbusIBS_IsMyocardInfarct', d);
                    }
                },
                inputMorbusIBS_IsMyocardIschemia: {
                    actionType: 'edit',
                    sectionCode: 'MorbusIBS',
                    handler: function(e, c, d) {
                        win.createMorbusIBSHtmlForm('MorbusIBS_IsMyocardIschemia', d);
                    }
                },
                inputMorbusIBS_IsFirstStenocardia: {
                    actionType: 'edit',
                    sectionCode: 'MorbusIBS',
                    handler: function(e, c, d) {
                        win.createMorbusIBSHtmlForm('MorbusIBS_IsFirstStenocardia', d);
                    }
                },
                inputMorbusIBS_IsNoStableStenocardia: {
                    actionType: 'edit',
                    sectionCode: 'MorbusIBS',
                    handler: function(e, c, d) {
                        win.createMorbusIBSHtmlForm('MorbusIBS_IsNoStableStenocardia', d);
                    }
                },
                inputMorbusIBS_IsRiseTS: {
                    actionType: 'edit',
                    sectionCode: 'MorbusIBS',
                    handler: function(e, c, d) {
                        win.createMorbusIBSHtmlForm('MorbusIBS_IsRiseTS', d);
                    }
                },
                inputMorbusIBS_IsSaveIschemia: {
                    actionType: 'edit',
                    sectionCode: 'MorbusIBS',
                    handler: function(e, c, d) {
                        win.createMorbusIBSHtmlForm('MorbusIBS_IsSaveIschemia', d);
                    }
                },
                inputMorbusIBS_IsBackStenocardia: {
                    actionType: 'edit',
                    sectionCode: 'MorbusIBS',
                    handler: function(e, c, d) {
                        win.createMorbusIBSHtmlForm('MorbusIBS_IsBackStenocardia', d);
                    }
                },
                inputMorbusIBS_IsShunting: {
                    actionType: 'edit',
                    sectionCode: 'MorbusIBS',
                    handler: function(e, c, d) {
                        win.createMorbusIBSHtmlForm('MorbusIBS_IsShunting', d);
                    }
                },
                inputMorbusIBS_IsStenting: {
                    actionType: 'edit',
                    sectionCode: 'MorbusIBS',
                    handler: function(e, c, d) {
                        win.createMorbusIBSHtmlForm('MorbusIBS_IsStenting', d);
                    }
                },
                inputMorbusIBS_IsKGIndication: {
                    actionType: 'edit',
                    sectionCode: 'MorbusIBS',
                    handler: function(e, c, d) {
                        win.createMorbusIBSHtmlForm('MorbusIBS_IsKGIndication', d);
                    }
                },
                inputUslugaComplex: {
                    actionType: 'edit',
                    sectionCode: 'MorbusIBS',
                    handler: function(e, c, d) {
                        win.createMorbusIBSHtmlForm('UslugaComplex', d);
                    }
                },
                inputMorbusIBS_IsKGConsent: {
                    actionType: 'edit',
                    sectionCode: 'MorbusIBS',
                    handler: function(e, c, d) {
                        win.createMorbusIBSHtmlForm('MorbusIBS_IsKGConsent', d);
                    }
                },
                inputMorbusIBS_IsKGFinished: {
                    actionType: 'edit',
                    sectionCode: 'MorbusIBS',
                    handler: function(e, c, d) {
                        win.createMorbusIBSHtmlForm('MorbusIBS_IsKGFinished', d);
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
		sw.Promed.swMorbusIBSWindow.superclass.initComponent.apply(this, arguments);
	}
});
