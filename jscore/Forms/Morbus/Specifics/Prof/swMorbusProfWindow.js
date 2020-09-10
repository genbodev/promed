/**
 * swMorbusProfWindow - Форма просмотра записи регистра с типом «Профзаболевания»
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * @package      Prof
 * @access       public
 * @copyright    Copyright (c) 2009-2014 Swan Ltd.
 * @author       Dmitriy Vlasenko
 * @version      12.2014
 */
sw.Promed.swMorbusProfWindow = Ext.extend(sw.Promed.BaseForm, 
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
    winTitle: lang['zapis_registra_po_profzabolevaniyam'],
	listeners: {
		'hide': function(win) {
			win.onHide(win.isChange);
		},
		'beforeShow': function(win) {
			if(!getWnd('swWorkPlaceMZSpecWindow').isVisible() && !isUserGroup('MIACSuperAdmin'))
			if (String(getGlobalOptions().groups).indexOf('ProfRegistry', 0) < 0)
			{
				sw.swMsg.alert('Сообщение', 'Форма "'+ win.winTitle +'" доступна только для пользователей, с указанной группой «Регистр по профзаболеваниям»');
				return false;
			}
		}
	},
	createMorbusProfHtmlForm: function(name, el_data) {
		if(this.allowSpecificEdit == false) {
			return false;
		}
		var morbus_id = el_data.object_id.split('_')[1];
		var params = this.rightPanel.getObjectData('MorbusProf',morbus_id);
		if (typeof params != 'object') {
			return false;
		}
		var form = this;
		var cmp, ct, elinputid, eloutputid, config;
		var empty_value = '<span style="color: #666;">Не указано</span>';
        var onChange = function(conf){
            var save_tb1 = Ext.get('MorbusProf_'+el_data.object_id+'_toolbarMorbusProf');
            switch(conf.name){
				case 'MorbusProfDiag':
				case 'MorbusProf_Year':
				case 'MorbusProf_Month':
				case 'MorbusProf_Day':
				case 'MorbusProf_IsFit':
				case 'Org':
				case 'OnkoOccupationClass':
                    save_tb1.setDisplayed('block');
                    break;
            }

            if(!this.changed_fields) this.changed_fields = {};
            this.changed_fields[conf.field_name] = conf;
        }.createDelegate(this);

		var onCancel = function(conf){
			if(!this.changed_fields) this.changed_fields = {};
			if(!this.changed_fields[conf.field_name] && !conf.field_name.inlist(['Org'])) {
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
						options.elInput = f;
						if (n!=o)
							onChange(options);
					}
				}
			};
		};

		eloutputid = 'MorbusProf_'+ el_data.object_id +'_input'+name;
		elinputid = 'MorbusProf_'+ el_data.object_id +'_inputarea'+name;
		eloutput = Ext.get(eloutputid);
		ct = Ext.get(elinputid);


        switch(name){
			case 'MorbusProfDiag':
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
					config.width = 300;
					config.moreFields = [
						{ name: 'Diag_ids', mapping: 'Diag_ids' }
					];
					config.comboSubject = 'MorbusProfDiag';
					config.typeCode = 'string';
					config.autoLoad = true;
					config.onLoadStore = function() {
						// накладываем фильтр на заболевание
						var combo = this;
						combo.getStore().clearFilter();
						combo.lastQuery = '';
						if (!Ext.isEmpty(eloutput.getAttribute('filterbydiagid'))) {
							combo.getStore().filterBy(function(record) {
								return (!Ext.isEmpty(record.get('Diag_ids')) && record.get('Diag_ids').replace(/ /g,'').split(',').indexOf(eloutput.getAttribute('filterbydiagid')) > -1);
							});
						}
					}
					cmp = new sw.Promed.SwCommonSprCombo(config);
					cmp.focus(true, 500);
					this.input_cmp_list[eloutputid] = cmp;
				}
				break;
            case 'MorbusProf_Year':
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
					config.minValue = 0;
					config.maxLength = 3;
					config.maxLengthText = lang['maksimalnaya_dlina_etogo_polya_3_simvola'];
					config.allowDecimals = false;
					config.allowNegative = false;
					cmp = new Ext.form.NumberField(config);
					cmp.focus(true, 200);
					this.input_cmp_list[eloutputid] = cmp;
				}
				break;
            case 'MorbusProf_Month':
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
					config.minValue = 0;
					config.maxValue = 12;
					config.maxLength = 3;
					config.maxLengthText = lang['maksimalnaya_dlina_etogo_polya_3_simvola'];
					config.allowDecimals = false;
					config.allowNegative = false;
					cmp = new Ext.form.NumberField(config);
					cmp.focus(true, 200);
					this.input_cmp_list[eloutputid] = cmp;
				}
				break;
            case 'MorbusProf_Day':
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
                    config.minValue = 0;
                    config.maxValue = 30;
                    config.maxLength = 3;
                    config.maxLengthText = lang['maksimalnaya_dlina_etogo_polya_3_simvola'];
                    config.allowDecimals = false;
                    config.allowNegative = false;
                    cmp = new Ext.form.NumberField(config);
                    cmp.focus(true, 200);
                    this.input_cmp_list[eloutputid] = cmp;
                }
                break;
            case 'MorbusProf_IsFit':
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
                    config.width = 300;
                    config.comboSubject = 'MorbusProfIsFit';
                    config.typeCode = 'int';
                    config.autoLoad = true;
                    cmp = new sw.Promed.SwCommonSprCombo(config);
                    cmp.focus(true, 500);
                    this.input_cmp_list[eloutputid] = cmp;
                }
                break;
			case 'Org':
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
					config.width = 300;
					config.onTrigger1Click = function() {
						var combo = this;

						getWnd('swOrgSearchWindow').show({
							onSelect: function(orgData) {
								if ( orgData.Org_id > 0 ) {
									combo.getStore().load({
										params: {
											Object:'Org',
											Org_id: orgData.Org_id,
											Org_Name:''
										},
										callback: function() {
											combo.setValue(orgData.Org_id);
											combo.focus(true, 500);
											combo.fireEvent('change', combo, orgData.Org_id);
										}
									});
								}

								getWnd('swOrgSearchWindow').hide();
							},
							onClose: function() {combo.focus(true, 200)}
						});
					};
					cmp = new sw.Promed.SwOrgCombo(config);
					cmp.focus(true, 500);
					this.input_cmp_list[eloutputid] = cmp;
				}
				break;
			case 'OnkoOccupationClass':
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
					config.width = 300;
					config.comboSubject = 'OnkoOccupationClass';
					config.typeCode = 'int';
					config.autoLoad = true;
					cmp = new sw.Promed.SwCommonSprCombo(config);
					cmp.focus(true, 500);
					this.input_cmp_list[eloutputid] = cmp;
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
	submitMorbusProfHtmlForm: function(btn_name, el_data) {
		if(this.allowSpecificEdit == false) {
			return false;
		}

		var save_tb1 = Ext.get('MorbusProf_'+el_data.object_id+'_toolbarMorbusProf');

		var params = this.rightPanel.getObjectData('MorbusProf',el_data.object_id.split('_')[1]);
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
        if (params['ProfDiagConfTypeC_id']) {
            params['ProfDiagConfType_cid'] = params['ProfDiagConfTypeC_id'];
            delete params['ProfDiagConfTypeC_id'];
        }
        params['PersonHeight_id'] = params.PersonHeight_id || null;
        params['PersonWeight_id'] = params.PersonWeight_id || null;
		params['Evn_pid'] = this.EvnDiagPLStom_id || this.EvnVizitPL_id || this.EvnSection_id || null;
		if (this.EvnVizitPL_id) {
			params['Mode'] = 'evnvizitpl_viewform';
		} else {
		params['Mode'] = 'personregister_viewform';
		}
		var url = '/?c=MorbusProf&m=doSaveMorbusProf';
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
	loadNodeViewForm: function() 
	{
		if (this.MorbusProf_pid) {
			this.viewObject.attributes.object_id = 'MorbusProf_pid';
			this.viewObject.attributes.object_value = this.MorbusProf_pid;
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
		sw.Promed.swMorbusProfWindow.superclass.show.apply(this, arguments);
		
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
		this.MorbusProf_pid = arguments[0].MorbusProf_pid;
		this.onHide = arguments[0].onHide || Ext.emptyFn;
		this.isChange = false;
		this.allowSpecificEdit = arguments[0].allowSpecificEdit || false;

		if(arguments[0] && arguments[0].action == 'view')
			this.allowSpecificEdit = false;
		
		if (this.MorbusProf_pid) {
			this.setTitle('Специфика');
		} else {
        if (this.allowSpecificEdit) {
            this.setTitle(this.winTitle + lang['_redaktirovanie']);
        } else {
            this.setTitle(this.winTitle + lang['_prosmotr']);
        }
		}

		this.viewObject = {
			id: 'PersonMorbusProf_'+ this.Person_id,
			attributes: {
				accessType: (this.allowSpecificEdit == true)?'edit':'view',
				text: 'test',
				object: 'PersonMorbusProf',
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
			PersonMorbusProf: {
				print: {
					actionType: 'view',
					sectionCode: 'PersonMorbusProf',
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
						var data = win.rightPanel.getObjectData('PersonMorbusProf',d.object_id);

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
            MorbusProf: {
                saveMorbusProf: {
                    actionType: 'edit', sectionCode: 'MorbusProf', handler: function(e, c, d) {
                        win.submitMorbusProfHtmlForm('saveMorbusProf',d);
                    }
                },
                inputMorbusProfDiag: {
                    actionType: 'edit',
                    sectionCode: 'MorbusProf',
                    handler: function(e, c, d) {
                        win.createMorbusProfHtmlForm('MorbusProfDiag', d);
                    }
                },
                inputMorbusProf_Year: {
                    actionType: 'edit',
                    sectionCode: 'MorbusProf',
                    handler: function(e, c, d) {
                        win.createMorbusProfHtmlForm('MorbusProf_Year', d);
                    }
                },
                inputMorbusProf_Month: {
                    actionType: 'edit',
                    sectionCode: 'MorbusProf',
                    handler: function(e, c, d) {
                        win.createMorbusProfHtmlForm('MorbusProf_Month', d);
                    }
                },
                inputMorbusProf_Day: {
                    actionType: 'edit',
                    sectionCode: 'MorbusProf',
                    handler: function(e, c, d) {
                        win.createMorbusProfHtmlForm('MorbusProf_Day', d);
                    }
                },
                inputMorbusProf_IsFit: {
                    actionType: 'edit',
                    sectionCode: 'MorbusProf',
                    handler: function(e, c, d) {
                        win.createMorbusProfHtmlForm('MorbusProf_IsFit', d);
                    }
                },
                inputOrg: {
                    actionType: 'edit',
                    sectionCode: 'MorbusProf',
                    handler: function(e, c, d) {
                        win.createMorbusProfHtmlForm('Org', d);
                    }
                },
                inputOnkoOccupationClass: {
                    actionType: 'edit',
                    sectionCode: 'MorbusProf',
                    handler: function(e, c, d) {
                        win.createMorbusProfHtmlForm('OnkoOccupationClass', d);
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
		sw.Promed.swMorbusProfWindow.superclass.initComponent.apply(this, arguments);
	}
});
