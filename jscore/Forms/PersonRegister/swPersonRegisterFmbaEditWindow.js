/**
 * swPersonRegisterFmbaEditWindow - Форма просмотра/редактирования записи регистра по ВЗН (7 нозологиям)
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      PersonRegister
 * @access       public
 * @copyright    Copyright (c) 2009-2016 Swan Ltd.
 * @author       Alexander Kurakin (original Alexander Permyakov)
 * @version      04.2016
 * @prefix       MHW
 */

sw.Promed.swPersonRegisterFmbaEditWindow = Ext.extend(sw.Promed.BaseForm, 
{
	winTitle: lang['zapis_registra_fmba'],
	PersonRegisterType_SysNick: 'fmba',
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
	listeners: {
		'hide': function(win) {
			win.onHide(win.isChange);
		},
		'beforeShow': function(win) {
			if (false == sw.Promed.personRegister.isAllow(win.PersonRegisterType_SysNick)) {
				return false;
			}
			if (!(String(getGlobalOptions().groups).indexOf('FmbaRegistry', 0) >= 0)) {
				sw.swMsg.alert('Сообщение', 'Форма "'+ win.winTitle +'" доступна только для пользователей с указанной группой "Регистр ФМБА"');
				return false;
			}
			return true;
		}
	},
	createInputArea: function(name, el_data) {
		if (this.allowSpecificEdit == false) {
			return false;
		}
        var record = this.rightPanel.viewFormDataStore.getById('PersonRegisterFmba' +'_'+ el_data.object_id),
            params = null;
		if (record && record.data) {
            params = record.data;
        } else {
			return false;
		}
		var me = this;
		var cmp, ct, elinputid, eloutputid, config;
		var empty_value = '<span style="color: #666;">Не указано</span>';
		var onChange = function(conf){
			if(conf.elOutput) {
				if (false && conf.field_name == 'Diag_id' && !conf.disableMsg) {
					sw.swMsg.show(
					{
						buttons: Ext.Msg.YESNO,
						fn: function( buttonId ) 
						{
							if ( buttonId == 'yes' ) 
							{
								conf.disableMsg = true;
								onChange(conf);
							}
							else
							{
								conf.elOutput.setDisplayed('inline');
								conf.elInputWrap.setDisplayed('none');
								conf.elInput.destroy();
								me.input_cmp_list[conf.elOutputId] = false;
							}
						},
						msg: lang['izmenen_diagnoz_zapisi_registra_po_vzn_izmenit_diagnoz_i_sozdat_napravlenie_na_vnesenie_izmeneniy_v_registr'],
						title: lang['vopros']
					});
					return false;
				}
				log(conf,'ssssss')
                me.requestSaveWithShowInfoMsg('/?c=PersonRegister&m=updateField',
                    {
                        Mode: 'personregister_viewform'
                        ,PersonRegister_id: params.PersonRegister_id
                        ,PersonRegisterType_SysNick: me.PersonRegisterType_SysNick
                        ,field_name: conf.field_name
                        ,field_value: conf.value
                    },
                    function(result) {
                        if ( result.success ) {
                            me.isChange = true;
                            conf.elOutput.setDisplayed('inline');
                            conf.elOutput.update(conf.outputValue);
                            if (conf.type == 'id') conf.elOutput.setAttribute('dataid',conf.value);
                            conf.elInputWrap.setDisplayed('none');
                            conf.elInput.destroy();
                            me.input_cmp_list[conf.elOutputId] = false;
                        }
                        if (typeof conf.requestCallback == 'function') {
                            result.field_name = conf.field_name;
                            result.field_value = conf.value || null;
                            result.field_raw_value = conf.outputValue;
                            result.field_rec = conf.rec || null;
                            conf.requestCallback(result);
                        }
                    }, me);
			}
		};
		
		var onCancel = function(conf){
			conf.elOutput.setDisplayed('inline');
			conf.elInputWrap.setDisplayed('none');
			conf.elInput.destroy();
			me.input_cmp_list[conf.elOutputId] = false;
		};
		
		if(!me.input_cmp_list) me.input_cmp_list = {};
		
		var getBaseConfig = function(options){
			return {
				hideLabel: true
				,renderTo: options.elInputId
				,listeners:
				{
					blur: function(f) {
						// лечим TypeError: combo.getStore(...) is null
                        if (f.disableBlurAction) {
                            return false;
                        }
						options.elInput = f;
						onCancel(options);
					},
					render: function(f) {
						
						log('sdfsdfsd',f)
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
						// лечим TypeError: combo.getStore(...) is null
                        if (f.disableBlurAction) {
                            return false;
                        }
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
						onChange(options);
					}
				}
			};
		};
		
		eloutputid = 'PersonRegisterFmba_'+ el_data.object_id +'_input'+name;
		elinputid = 'PersonRegisterFmba_'+ el_data.object_id +'_inputarea'+name;
		eloutput = Ext.get(eloutputid);
		ct = Ext.get(elinputid);
		
		switch(name){
			case 'Diag':
				if (ct && !me.input_cmp_list[eloutputid]) {
					ct.setDisplayed('block');
					eloutput.setDisplayed('none');
					config = getBaseConfig({
						name: name
						,type: 'id'
						,field_name: 'Diag_id'
						,elOutputId: eloutputid
						,elInputId: elinputid
						,elOutput: eloutput
						,outputValue: null
						,codeField: 'Diag_Code'
						,elInputWrap: ct
						,elInput: null
                        ,getFieldRawValue: function(f, n, rec) {
                            if (rec) {
                                return rec.get('Diag_Code') + ' ' + rec.get('Diag_Name');
                            }
                            return empty_value;
                        }
                        ,requestCallback: function(result) {
                            if ( result.success ) {
                                if (result.field_rec) {
									log(result,'sdfsdfds');
                                    record.set('Diag_id', result.field_value);
                                    record.set('Diag_Name', result.field_raw_value);
                                } else {
                                    record.set('Diag_id', null);
                                    record.set('Diag_Name', null);
                                }
                                record.commit(true);
                                me.rightPanel.viewFormDataStore.commitChanges();
                            }
                        }
					});
					config.width = 350;
					config.listWidth = 500;
					config.hiddenName = 'Diag_id';
					//config.PersonRegisterType_SysNick = me.PersonRegisterType_SysNick;
                    config.MorbusType_SysNick = 'vzn';
					var listeners = Ext.apply({}, config.listeners);
					delete config.listeners;
					cmp = new sw.Promed.SwDiagCombo(config);
					cmp.additQueryFilter ="(isVZN = 1 and Diag_Code not like 'E75.5')";
					cmp.additClauseFilter = '(record["MorbusType_List"].search(new RegExp("vzn", "i"))>=0)';
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
					me.input_cmp_list[eloutputid] = cmp;
				}
				break;
			case 'Code':
				if(ct && !me.input_cmp_list[eloutputid]) {
					ct.setDisplayed('block');
					eloutput.setDisplayed('none');
					config = getBaseConfig({
						name: name
						,type: 'int'
						,field_name: name
						,elOutputId: eloutputid
						,elInputId: elinputid
						,elOutput: eloutput
						,outputValue: params['PersonRegister_Code'] || empty_value
						,elInputWrap: ct
						,elInput: null
                        ,requestCallback: function(result) {
                            if ( result.success ) {
                                record.set('PersonRegister_Code', result.field_value);
                                record.commit(true);
                                me.rightPanel.viewFormDataStore.commitChanges();
                            }
                        }
					});
					config.width = 100;
					config.maskRe = new RegExp("^[0-9]*$");
					config.allowDecimals = false;
					config.allowNegative = false;
					config.maxLength = 13;
					config.autoCreate = {tag: "input", size:14, maxLength: config.maxLength, autocomplete: "off"};

					cmp = new Ext.form.NumberField(config);
					cmp.setValue(params['PersonRegister_Code'] || null);
					cmp.focus(true, 500);
					me.input_cmp_list[eloutputid] = cmp;
				}
				break;
		}
	},
	/**
	 * Открывает соответсвующую акшену форму 
	 *
     * @param {String} open_form Название открываемой формы, такое же как название объекта формы
     * @param {String} id Наименование идентификатора таблицы, передаваемого в форму
     * @param {Object} oparams
     * @param {String} mode
     * @param {String} title
     * @param {Function} callback
     * @returns {boolean}
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
		sw.Promed.swPersonRegisterFmbaEditWindow.superclass.show.apply(this, arguments);
		
		//log(arguments[0]);
		if ( !arguments[0] || !arguments[0].Person_id || !arguments[0].PersonRegister_id /*|| !arguments[0].MorbusType_SysNick*/ ) {
			sw.swMsg.alert(lang['soobschenie'], lang['nevernyie_parametryi'], function() { this.hide(); }.createDelegate(this) );
			return false;
		}
		
		this.Person_id = arguments[0].Person_id;
		this.PersonRegister_id = arguments[0].PersonRegister_id;
		this.MorbusType_SysNick = arguments[0].MorbusType_SysNick || null;
		this.userMedStaffFact = arguments[0].userMedStaffFact || sw.Promed.MedStaffFactByUser.last;
		this.onHide = arguments[0].onHide || Ext.emptyFn;
		this.isChange = false;
		this.allowSpecificEdit = arguments[0].allowSpecificEdit || false;
		this.setTitle(this.winTitle + ': ' + (this.allowSpecificEdit ? lang['_redaktirovanie'] : lang['_prosmotr']));
		this.viewObject = {
			id: 'PersonRegisterFmba_'+ this.PersonRegister_id,
			attributes: {
				accessType: (this.allowSpecificEdit)?'edit':'view',
				text: 'test',
				object: 'PersonRegisterFmba',
				object_id: 'PersonRegister_id',
				object_value: this.PersonRegister_id			
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

        Ext.apply(this, sw.Promed.ViewPanelMsgMixin);
        this.viewPanel = this.rightPanel;
		
		var win = this;
		this.rightPanel.configActions = {
			PersonRegisterNolos: {
				print: {
					actionType: 'view',
					sectionCode: 'PersonRegisterFmba',
					handler: function(e, c, d) {
						win.rightPanel.printHtml(d.section_id);
					}
				},
				editPhoto: {
					actionType: 'edit',
					sectionCode: 'PersonRegisterFmba',
					handler: function(e, c, d) {
						if (win.allowSpecificEdit) {
							var params = {
								action: 'loadimage',
								saveUrl: '/?c=PMMediaData&m=uploadPersonPhoto',
								enableFileDescription: false,
								saveParams: {Person_id: win.Person_id},
								callback: function(data){
									if (data && data.person_thumbs_src)
									{
										document[('photo_person_'+ win.Person_id)].src=data.person_thumbs_src +'?'+ Math.random();
									}
								}
							};
							getWnd('swFileUploadWindow').show(params);
						}
					}
				},
				editPers: {
					actionType: 'view',
					sectionCode: 'PersonRegisterFmba',
					handler: function(e, c, d) {
						var data = win.rightPanel.getObjectData('PersonRegisterFmba',d.object_id),
							action = (win.allowSpecificEdit)?'edit':'view';
						var params = {
							callback: function(data){
								if (data && data.Person_id && win.allowSpecificEdit) {
									win.loadNodeViewForm();
								}
							}
						};
						win.openForm('swPersonEditWindow','XXX_id',params, action,lang['redaktirovanie_personalnyih_dannyih_patsienta']);
					}
				},
				printMedCard: {
					actionType: 'view',
					sectionCode: 'PersonRegisterFmba', 
					handler: function(e, c, d) {
						var data = win.rightPanel.getObjectData('PersonRegisterFmba',d.object_id);
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
					actionType: 'view',
					sectionCode: 'PersonRegisterFmba',
					handler: function(e, c, d) {
						var action = (win.allowSpecificEdit)?'edit':'view',
							params = {
								callback: Ext.emptyFn, // почему-то в форме swPersonCardHistoryWindow вызывается только при нажатии на кн. "помощь"
								onHide: function(data){
									if (win.allowSpecificEdit) {
										win.loadNodeViewForm();
									}
								}
							};
						win.openForm('swPersonCardHistoryWindow','XXX_id',params, action,lang['istoriya_prikrepleniya']);
					}
				},
				inputDiag: {
					actionType: 'edit',
					sectionCode: 'PersonRegisterFmba',
					dblClick: false,
					handler: function(e, c, d) {
						win.createInputArea('Diag', d);
					}
				},
				inputCode: {
					actionType: 'edit',
					sectionCode: 'PersonRegisterFmba',
					dblClick: false,
					handler: function(e, c, d) {
						win.createInputArea('Code', d);
					}
				}
			},
			PersonPrivilegeRegAll: {
				toggleDisplay: {
					actionType: 'view',
					sectionCode: 'PersonPrivilegeRegAllList',
					handler: function(e, c, d) {
						var id = 'PersonPrivilegeRegAllTable_'+ d.object_id;
						win.rightPanel.toggleDisplay(id,Ext.get(id).isDisplayed());
					}
				}
			},
			PersonPrivilegeFedAll: {
				toggleDisplay: {
					actionType: 'view',
					sectionCode: 'PersonPrivilegeFedAllList',
					handler: function(e, c, d) {
						var id = 'PersonPrivilegeFedAllTable_'+ d.object_id;
						win.rightPanel.toggleDisplay(id,Ext.get(id).isDisplayed());
					}
				}
			},
			PersonDrug: {
				toggleDisplay: {
					actionType: 'view',
					sectionCode: 'PersonDrugList',
					handler: function(e, c, d) {
						var id = 'PersonDrugTable_'+ d.object_id;
						win.rightPanel.toggleDisplay(id,Ext.get(id).isDisplayed());
					}
				}
			},
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
		sw.Promed.swPersonRegisterFmbaEditWindow.superclass.initComponent.apply(this, arguments);
	}
});