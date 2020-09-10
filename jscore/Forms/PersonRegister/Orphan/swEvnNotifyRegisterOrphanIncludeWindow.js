/**
 * swEvnNotifyRegisterOrphanIncludeWindow - Направление на включение в регистр: Добавление
 * 
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * @package      PersonRegister
 * @access       public
 * @copyright    Copyright (c) 2009-2015 Swan Ltd.
 * @author       Alexander Permyakov
 * @version      03.2015
 */
sw.Promed.swEvnNotifyRegisterOrphanIncludeWindow = Ext.extend(sw.Promed.BaseForm, 
{
	title: lang['napravlenie_na_vklyuchenie_v_registr_dobavlenie'],
	PersonRegisterType_SysNick: 'orphan',
	MorbusType_SysNick: 'orphan',
	formMode: 'remote',
	width: 700,
	height: 270,
	//autoHeight: true,
	buttonAlign: 'left',
	closable: true,
	closeAction: 'hide',
	draggable: true,
	formStatus: 'edit',
	layout: 'border',
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
            mpField = base_form.findField('MedPersonal_id');
        if (diagField.disabled) {
            params.Diag_id = diagField.getValue();
        }
        if (mpField.disabled) {
            params.MedPersonal_id = mpField.getValue();
        }
		if (base_form.findField('Lpu_did').disabled) {
			params.Lpu_did = base_form.findField('Lpu_did').getValue();
		}
        me.getLoadMask().show(langs('Пожалуйста, подождите, идет создание направления...'));
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
		this.buttons[0].show();
		this.buttons[0].setDisabled(d);
	},
	isMzSpecialist: function()
	{
		return (haveArmType('minzdravdlo') || haveArmType('spec_mz') || haveArmType('mzchieffreelancer'));
	},
	show: function() 
	{
		sw.Promed.swEvnNotifyRegisterOrphanIncludeWindow.superclass.show.apply(this, arguments);		
		var me = this;
		if (!arguments[0] || !arguments[0].formParams || !arguments[0].formParams.Person_id) {
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
        return true;
    },
	_processingInputFormParams: function() 
	{
		if (!this.formParams.MedPersonal_id) {
			this.formParams.MedPersonal_id = getGlobalOptions().medpersonal_id;
		}
		if (!this.formParams.Lpu_oid) {
			this.formParams.Lpu_oid = getGlobalOptions().lpu_id;
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
            moField = base_form.findField('Lpu_oid'),
            mpField = base_form.findField('MedPersonal_id');
		me.setFieldsDisabled(false);		
        me.getLoadMask().show(lang['pojaluysta_podojdite_idet_zagruzka_formyi']);
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
                }
                if (!moField.getValue()) {
                    moField.setValue(getGlobalOptions().lpu_id);
                }
                base_form.findField('Lpu_did').setValue(getGlobalOptions().lpu_id);
                moField.setDisabled(false);
                moField.getStore().load({
                    params: {
                        Lpu_id: null
                    },
                    callback: function()
                    {
                        moField.setValue(moField.getValue());
                    }
                });
                mpField.setDisabled(true);
                mpField.getStore().load({
                    params: {
                        MedPersonal_id: mpField.getValue()
                    },
                    callback: function()
                    {
                        mpField.setValue(mpField.getValue());
                    }
                });
                if(me.isMzSpecialist()){
                	base_form.findField('Lpu_did').enable();
                	base_form.findField('Lpu_did').fireEvent('change',base_form.findField('Lpu_did'),base_form.findField('Lpu_did').getValue());
                	mpField.setDisabled(false);
                } else {
                	base_form.findField('Lpu_did').disable();
                }
                return false;
			}
		});
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
			labelWidth: 220,
			autoScroll:true,
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
				}, {
                    name: 'EvnNotifyRegister_pid',
                    xtype: 'hidden'
                }, {
                    hiddenName: 'Diag_id',
                    fieldLabel: lang['diagnoz'],
                    allowBlank: false,
                    xtype: 'swdiagcombo',
                    anchor:'100%',
					MorbusType_SysNick: me.MorbusType_SysNick,
					PersonRegisterType_SysNick: me.PersonRegisterType_SysNick
                }, {
                    fieldLabel: lang['mo_v_kotoroy_vpervyie_ustanovlen_diagnoz_orfannogo_zabolevaniya'],
					hiddenName: 'Lpu_oid',// Может не совпадать с МО пользователя
					width: 350,
					style: 'margin-top: 8px',
					triggerConfig:
					{
						tag: "img", src: Ext.BLANK_IMAGE_URL, cls: "x-form-trigger x-form-arrow-trigger", style: "top: 8px"
					},
					xtype: 'swlpucombo',
                    allowBlank: false
				}, {
					fieldLabel: 'МО врача, заполнившего направление',
                    hiddenName: 'Lpu_did',
                    xtype: 'swlpucombo',
                    width: 350,
					style: 'margin-top: 8px',
					triggerConfig:
					{
						tag: "img", src: Ext.BLANK_IMAGE_URL, cls: "x-form-trigger x-form-arrow-trigger", style: "top: 8px"
					},
					allowBlank: false,
					listeners: {
                        'change': function(combo, newVal) {
                        	if (me.isMzSpecialist()){
                        		var base_form = me.FormPanel.getForm(),
	                                mpField = base_form.findField('MedPersonal_id'),
									MedPersonal_id = mpField.getValue();

								mpField.clearValue();

								mpField.getStore().baseParams = {
	                                Lpu_id: newVal
	                            };

	                            var curDate = new Date();
	                            var onDate = Ext.util.Format.date(curDate, 'Y-m-d');

	                            mpField.getStore().baseParams.onDate = onDate;
	                            mpField.getStore().load({
	                                callback: function()
	                                {
										if ( !Ext.isEmpty(MedPersonal_id) ) {
											var index = mpField.getStore().findBy(function(rec) {
												return (rec.get('MedPersonal_id') == MedPersonal_id);
											});
											if ( index >= 0 ) {
												mpField.setValue(MedPersonal_id);
											}
										}
	                                }
	                            });
                        	}
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
		sw.Promed.swEvnNotifyRegisterOrphanIncludeWindow.superclass.initComponent.apply(this, arguments);
	}
});
