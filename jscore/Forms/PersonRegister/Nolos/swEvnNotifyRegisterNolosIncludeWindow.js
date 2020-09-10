/**
 * swEvnNotifyRegisterNolosIncludeWindow - Направление на включение в регистр: Добавление
 * 
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * @package      PersonRegister
 * @access       public
 * @copyright    Copyright (c) 2009-2014 Swan Ltd.
 * @author       Alexander Permyakov
 * @version      02.2015
 */
sw.Promed.swEvnNotifyRegisterNolosIncludeWindow = Ext.extend(sw.Promed.BaseForm, 
{
	title: lang['napravlenie_na_vklyuchenie_v_registr_dobavlenie'],
	PersonRegisterType_SysNick: 'nolos',
	formMode: 'remote',
	width: 700,
	height: 600,
	//autoHeight: true,
	buttonAlign: 'left',
	closable: true,
	closeAction: 'hide',
	draggable: true,
	formStatus: 'edit',
	layout: 'border',
	action:'add',
	modal: true,
	callback: Ext.emptyFn,
	onHide: Ext.emptyFn,
	doSave: function()
	{
		if ( this.formStatus == 'save' ) {
			return false;
		}
		var me = this;
		this.formStatus = 'save';
		var base_form = me.FormPanel.getForm();
		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
                    me.formStatus = 'edit';
					me.FormPanel.getFirstInvalidEl().focus(false);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
		var params = {
			PersonRegisterType_SysNick: me.PersonRegisterType_SysNick
		};
        var diagField = base_form.findField('Diag_id'),
            moField = base_form.findField('Lpu_did'),
            mpField = base_form.findField('MedPersonal_id');
        if (diagField.disabled) {
            params.Diag_id = diagField.getValue();
        }
        if (moField.disabled) {
            params.Lpu_did = moField.getValue();
        }
        if (mpField.disabled) {
            params.MedPersonal_id = mpField.getValue();
        }
        me.getLoadMask().show(lang['pojaluysta_podojdite_idet_sozdanie_napravleniya']);
		base_form.submit({
			params: params,
			failure: function(result_form, action) 
			{
				me.formStatus = 'edit';
				me.getLoadMask().hide();
				if (action.result) {
					if (action.result.Error_Code) {
						Ext.Msg.alert(lang['oshibka_#']+action.result.Error_Code, action.result.Error_Message);
					}
				}
			},
			success: function(result_form, action) 
			{
				showSysMsg(lang['napravlenie_sozdano']);
				me.formStatus = 'edit';
				me.getLoadMask().hide();
				var data = {};
				if (action.result) {
					data = action.result;
				}
				me.callback(data);
                me.hide();
			}
		});
        return true;
	},
	setFieldsDisabled: function(d) 
	{
		var base_form = this.FormPanel.getForm();		
		base_form.items.each(function(f) 
		{
			if (f && (f.xtype!='hidden') && (f.xtype!='fieldset')  && (f.changeDisabled!==false))
			{
				f.setDisabled(d);
			}
		});
		this.buttons[0].setDisabled(d);
	},
	show: function() 
	{
		sw.Promed.swEvnNotifyRegisterNolosIncludeWindow.superclass.show.apply(this, arguments);		
		var me = this;
		if(arguments[0]&&arguments[0].action){
			this.action=arguments[0].action
		}else{
			this.action='add';
		}
		if (!arguments[0] || !arguments[0].formParams || ((this.action=='add'&&!arguments[0].formParams.Person_id)&&(this.action=='edit'&&!arguments[0].formParams.EvnNotifyRegister_id))) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.ERROR,
				msg: lang['oshibka_otkryitiya_formyi_ne_ukazanyi_nujnyie_vhodnyie_parametryi'],
				title: lang['oshibka'],
				fn: function() {
                    me.hide();
				}
			});
            return false;
		}
		this.focus();
		this.center();
		this.formStatus = 'edit';
        this.formParams = arguments[0].formParams;
        this.callback = arguments[0].callback || Ext.emptyFn;
        this.onHide = arguments[0].onHide || Ext.emptyFn;
		this._processingInputFormParams();
		this._resetForm();
		this._loadForm();
		this.buttons[0].show();		
        return true;
    },
	_processingInputFormParams: function() 
	{
		if (!this.formParams.MedPersonal_id) {
			this.formParams.MedPersonal_id = getGlobalOptions().medpersonal_id;
		}
		if (!this.formParams.Lpu_did) {
			this.formParams.Lpu_did = getGlobalOptions().lpu_id;
		}		
    },
	_resetForm: function() 
	{
		this.FormPanel.getForm().reset();
		this.formMode = 'remote';
		this.FormPanel.getForm().setValues(this.formParams);
    },
	_loadForm: function() 
	{
		var me = this,
            base_form = this.FormPanel.getForm(),
            diagField = base_form.findField('Diag_id'),
            evnVKField = base_form.findField('EvnVK_id'),
            moField = base_form.findField('Lpu_did'),
            mpField = base_form.findField('MedPersonal_id'),
            isRegisterOperator = sw.Promed.personRegister.isVznRegistryOperator();
		me.setFieldsDisabled(false);		
        me.getLoadMask().show(lang['pojaluysta_podojdite_idet_zagruzka_formyi']);
		switch(me.action){
			case 'add':
				me.InformationPanel.load({
				Person_id: base_form.findField('Person_id').getValue(),
				callback: function(dataList) {
					if (!dataList || !dataList[0] || !dataList[0].data || !dataList[0].data.PersonEvn_id) {
						me.getLoadMask().hide();
						me.hide();
						return false;
					}
					me.getLoadMask().hide();
					base_form.findField('PersonEvn_id').setValue(dataList[0].data.PersonEvn_id);
					base_form.findField('Server_id').setValue(dataList[0].data.Server_id);
					if (diagField.getValue()) {
						diagField.getStore().load({
							params: {
								where: ' where Diag_id = ' + diagField.getValue()
							},
							callback: function()
							{
								diagField.setValue(diagField.getValue());
								diagField.setDisabled(true);
								diagField.fireEvent('change', diagField, diagField.getValue());
							}
						});
					} else {
						diagField.setDisabled(false);
						diagField.additQueryFilter = "(Diag_Code not like 'E75.5')";
					}
					evnVKField.setValue(null);
					evnVKField.getStore().removeAll();
					evnVKField.getStore().baseParams = {
						Person_id: base_form.findField('Person_id').getValue()
					};
					evnVKField.getStore().load({});
					moField.setDisabled(false == isRegisterOperator);
					mpField.setDisabled(false == isRegisterOperator);
					moField.getStore().load({
						params: {
							Lpu_id: isRegisterOperator ? null : moField.getValue()
						},
						callback: function()
						{
							moField.setValue(moField.getValue());
							moField.fireEvent('change', moField, moField.getValue());
						}
					});
					return false;
				}
			});
				break;
			case 'edit':
				base_form.load({
					failure: function() {
						me.getLoadMask().hide();
						sw.swMsg.alert(lang['oshibka'], lang['ne_udalos_zagruzit_dannyie_s_servera']);
					}.createDelegate(this),
					url:'/?c=PersonRegister&m=loadEvnNotifyRegisterInclude',
					params:me.formParams,
					success: function(fm,rec,d) {
							var response_obj = Ext.util.JSON.decode(rec.response.responseText);
							me.InformationPanel.load({
								Person_id: base_form.findField('Person_id').getValue(),
								callback: function(dataList) {
									if (!dataList || !dataList[0] || !dataList[0].data || !dataList[0].data.PersonEvn_id) {
										me.getLoadMask().hide();
										me.hide();
										return false;
									}
									me.getLoadMask().hide();
									if (diagField.getValue()) {
										diagField.getStore().load({
											params: {
												where: ' where Diag_id = ' + diagField.getValue()
											},
											callback: function()
											{
												diagField.setValue(diagField.getValue());
												diagField.setDisabled(true);
												diagField.fireEvent('change', diagField, diagField.getValue());
											}
										});
									} else {
										diagField.setDisabled(false);
										diagField.additQueryFilter = "(Diag_Code not like 'E75.5')";
									}
									evnVKField.getStore().baseParams = {
										Person_id: base_form.findField('Person_id').getValue()
									};
									evnVKField.getStore().load({
										callback:function(){
											evnVKField.setValue(evnVKField.getValue());
										}
									});
									
									moField.setDisabled(true);
									mpField.setDisabled(true);
									moField.getStore().load({
										params: {
											Lpu_id:  moField.getValue()
										},
										callback: function()
										{
											moField.setValue(moField.getValue());
											moField.fireEvent('change', moField, moField.getValue());
										}
									});
									return false;
								}
							});
					}
				});
				break;
		}
		
	},
	_createFormPanel: function() 
	{
		var me = this;
		return new Ext.form.FormPanel({
			frame: true,
			layout: 'form',
			region: 'center',
			id: 'FormPanel',
			bodyStyle: 'padding: 5px',
			autoHeight: false,
			labelAlign: 'right',
			labelWidth: 200,
			autoScroll:true,
			reader: new Ext.data.JsonReader({
				success: Ext.emptyFn
			}, [
			{
				name: 'Person_id'
			},

			{
				name: 'PersonEvn_id'
			},

			{
				name: 'EvnVK_id'
			},

			{
				name: 'EvnNotifyRegister_pid'
			},
			{
				name: 'EvnNotifyRegister_Comment'
			},
			{
				name: 'Lpu_did'
			},
			{
				name: 'Diag_id'
			},
			{
				name:'Server_id'
			},
			{
				name:'MedPersonal_id'
			}
			]),
			url:'/?c=PersonRegister&m=createEvnNotifyRegisterInclude',
			items: 
			[{
				region: 'north',
				layout: 'form',
				xtype: 'panel',
				items: [{
					name: 'Server_id',
					xtype: 'hidden'
				}, {
					name: 'PersonEvn_id',
					xtype: 'hidden'
				}, {
					name: 'Person_id',
					xtype: 'hidden'
				},{
					name: 'EvnNotifyRegister_id',
					xtype: 'hidden'
				}, 
					{
					name: 'EvnNotifyRegister_pid',
					xtype: 'hidden'
				}, {
                    hiddenName: 'Diag_id',
                    fieldLabel: lang['diagnoz'],
                    allowBlank: false,
                    xtype: 'swdiagcombo',
                    anchor:'100%',
					PersonRegisterType_SysNick: me.PersonRegisterType_SysNick
                }, {
                    fieldLabel: lang['protokol_vk'],
                    trigger1Class: null,
                    hiddenName: 'EvnVK_id',
                    allowBlank: false,
                    anchor:'100%',
					xtype: 'swevnvknoloscombo'
				}, {
                    fieldLabel: lang['obosnovanie_napravleniya'],
                    name: 'EvnNotifyRegister_Comment',
                    anchor:'100%',
					height: 65,
					//width: 585,
                    maxLength: 1024,
                    maxLengthText: lang['maksimalnaya_dlina_etogo_polya_1024_simvolov'],
                    xtype: 'textarea'
				}, {/*
					fieldLabel: lang['data_zapolneniya'],
					name: 'EvnNotifyRegister_setDate',
					xtype: 'swdatefield',
                    allowBlank: false,
					plugins: [new Ext.ux.InputTextMask('99.99.9999', false)]
                }, {*/
                    fieldLabel: lang['mo_zapolneniya_napravleniya'],
					hiddenName: 'Lpu_did',// Может не совпадать с МО пользователя
					width: 350,
					xtype: 'swlpucombo',
                    allowBlank: false,
                    anchor: false,
                    listeners: {
                        'change': function(combo, newVal) {
                            var base_form = me.FormPanel.getForm(),
                                mpField = base_form.findField('MedPersonal_id'),
                                isRegisterOperator = sw.Promed.personRegister.isVznRegistryOperator();
                            mpField.getStore().baseParams = {
                                Lpu_id: newVal
                            };
                            mpField.getStore().load({
                                params: {
                                    MedPersonal_id: isRegisterOperator ? null : mpField.getValue()
                                },
                                callback: function()
                                {
                                    if (mpField.getStore().getById(mpField.getValue())) {
                                        mpField.setValue(mpField.getValue());
                                    } else {
                                        mpField.setValue(null);
                                    }
                                }
                            });
                        }
                    }
				}, {
					fieldLabel: lang['vrach_zapolnivshiy_napravlenie'],
					hiddenName: 'MedPersonal_id',
					listWidth: 750,
					width: 350,
					xtype: 'swmedpersonalcombo',
                    allowBlank: false,
					anchor: false
				}]
			}]
		});
    },
	_cfgButtons: function() 
	{
		var me = this;
		return [{
			handler: function() {
				me.doSave();
			}.createDelegate(this),
			iconCls: 'save16',
			text: BTN_FRMSAVE
		},
		{
			text: '-'
		},
		HelpButton(this),
		{
			handler: function() 
			{
				me.hide();
			},
			iconCls: 'cancel16',
			text: BTN_FRMCANCEL
		}];
    },
	initComponent: function() 
	{
		this.InformationPanel = new sw.Promed.PersonInformationPanelShort({
			region: 'north'
		});
		this.FormPanel = this._createFormPanel();		
		Ext.apply(this, 
		{	
			buttons: this._cfgButtons(),
			items: [this.InformationPanel, this.FormPanel]
		});
		sw.Promed.swEvnNotifyRegisterNolosIncludeWindow.superclass.initComponent.apply(this, arguments);
	}
});
