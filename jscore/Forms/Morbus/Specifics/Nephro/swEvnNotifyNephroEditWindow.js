/**
 * swEvnNotifyNephroEditWindow - Извещение по нефрологии
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
sw.Promed.swEvnNotifyNephroEditWindow = Ext.extend(sw.Promed.BaseForm, 
{
	action: null,
	//autoHeight: true,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	draggable: true,
	formMode: 'remote',
	formStatus: 'edit',
	layout: 'border',
	modal: true,
	width: 700,
	height: 600,
	doSave: function()
	{
		if ( this.formStatus == 'save' || this.action != 'add' ) {
			return false;
		}
		
		var win = this;
		this.formStatus = 'save';
		
		var form = this.FormPanel;
		var base_form = form.getForm();
		var params = {};
        if(this.fromDispCard){
            params.fromDispCard = 1;
        }
        params.Diag_id = base_form.findField('Diag_id').getValue();

		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
                    win.formStatus = 'edit';
					form.getFirstInvalidEl().focus(false);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение..."});
		loadMask.show();
		
		//params.MedPersonal_id = base_form.findField('MedPersonal_id').getValue();
		//params.EvnNotifyNephro_setDate = Ext.util.Format.date(base_form.findField('EvnNotifyNephro_setDate').getValue(), 'd.m.Y');
		//params.Diag_Name = base_form.findField('Diag_Name').getValue();
		
		base_form.submit({
			params: params,
			failure: function(result_form, action) 
			{
				win.formStatus = 'edit';
				loadMask.hide();
				if (action.result) 
				{
					if (action.result.Error_Code)
					{
						Ext.Msg.alert(lang['oshibka_#']+action.result.Error_Code, action.result.Error_Message);
					}
				}
			},
			success: function(result_form, action) 
			{
				
				showSysMsg(lang['izveschenie_sozdano']);
				win.formStatus = 'edit';
				loadMask.hide();
				var data = {};
				if (typeof action.result == 'object') {
					data = action.result;
				}
				win.callback(data);
                win.hide();
			}
		});
        return true;
	},
	setFieldsDisabled: function(d) 
	{
		var form = this;
		var base_form = this.findById('FormPanel').getForm();
		
		base_form.items.each(function(f) 
		{
			if (f && (f.xtype!='hidden') && (f.xtype!='fieldset')  && (f.changeDisabled!==false))
			{
                if(f.hiddenName && f.hiddenName == 'Diag_id'){
                    f.setDisabled(true);
                } else {
                    f.setDisabled(d);
                }
			}
		});
		form.buttons[0].setDisabled(d);
	},
	show: function() 
	{
		sw.Promed.swEvnNotifyNephroEditWindow.superclass.show.apply(this, arguments);
		
		var me = this;
		if (!arguments[0] || !arguments[0].formParams) {
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

		var base_form = this.FormPanel.getForm();
		base_form.reset();

		this.formMode = 'remote';
		this.formStatus = 'edit';

        this.EvnNotifyNephro_id = arguments[0].EvnNotifyNephro_id || null;
        this.callback = arguments[0].callback || Ext.emptyFn;
        this.onHide = arguments[0].onHide || Ext.emptyFn;

        var url, params = {};
		if (this.EvnNotifyNephro_id) {
			this.action = 'view';
            this.setTitle(lang['izveschenie_po_nefrologii_prosmotr']);
            this.setFieldsDisabled(true);
            url = '/?c=MorbusNephro&m=doLoadEditFormEvnNotifyNephro';
            params.EvnNotifyNephro_id = this.EvnNotifyNephro_id;
		} else {
            this.action = 'add';
            this.setTitle(lang['izveschenie_po_nefrologii_dobavlenie']);
            this.setFieldsDisabled(false);
            if (!arguments[0].formParams.EvnNotifyNephro_setDate) {
                arguments[0].formParams.EvnNotifyNephro_setDate = getGlobalOptions().date;
            }
            if (!arguments[0].formParams.EvnNotifyNephro_diagDate) {
                arguments[0].formParams.EvnNotifyNephro_diagDate = getGlobalOptions().date;
            }
            if (!arguments[0].formParams.MedPersonal_id) {
                arguments[0].formParams.MedPersonal_id = getGlobalOptions().medpersonal_id;
            }
            if (!arguments[0].formParams.MedPersonal_hid) {
                arguments[0].formParams.MedPersonal_hid = getGlobalOptions().medpersonal_id;
            }
            url = '/?c=MorbusNephro&m=doLoadEditFormMorbusNephro';
            params.Morbus_id = arguments[0].formParams.Morbus_id;
            this.fromDispCard = arguments[0].formParams.fromDispCard;
		}
		
		base_form.setValues(arguments[0].formParams);

		var loadMask = new Ext.LoadMask(this.getEl(),{msg: LOAD_WAIT});
		loadMask.show();

        Ext.Ajax.request({
            failure:function () {
                loadMask.hide();
                me.hide();
                sw.swMsg.alert(lang['oshibka'], lang['ne_udalos_poluchit_dannyie_s_servera']);
            },
            params: params,
            success:function (response) {
                var result = Ext.util.JSON.decode(response.responseText);
                if ('add' == me.action) {
                    base_form.findField('Person_id').setValue(result[0].Person_id);
                    base_form.findField('PersonHeight_id').setValue(result[0].PersonHeight_id || null);
                    base_form.findField('PersonWeight_id').setValue(result[0].PersonWeight_id || null);
                    base_form.findField('PersonHeight_Height').setValue(result[0].PersonHeight_Height || null);
                    base_form.findField('PersonWeight_Weight').setValue(result[0].PersonWeight_Weight || null);
                    if (!base_form.findField('Diag_id').getValue()) {
                        base_form.findField('Diag_id').setValue(result[0].Diag_id || null);
                    }
                    base_form.findField('NephroDiagConfType_id').setValue(result[0].NephroDiagConfType_id || null);
                    base_form.findField('NephroCRIType_id').setValue(result[0].NephroCRIType_id || null);
                    base_form.findField('EvnNotifyNephro_IsHyperten').setValue(result[0].MorbusNephro_IsHyperten || null);
                    base_form.findField('EvnNotifyNephro_Treatment').setValue(result[0].MorbusNephro_Treatment || null);
                    base_form.findField('EvnNotifyNephro_firstDate').setValue(result[0].MorbusNephro_firstDate || null);
                } else {
                    base_form.setValues(result[0]);
                }
                me.InformationPanel.load({
                    Person_id: base_form.findField('Person_id').getValue()
                });
                if (base_form.findField('Diag_id').getValue()) {
                    base_form.findField('Diag_id').getStore().load({
                        params: {
                            where: ' where Diag_id = ' + base_form.findField('Diag_id').getValue()
                        },
                        callback: function()
                        {
                            base_form.findField('Diag_id').setValue(base_form.findField('Diag_id').getValue());
                            base_form.findField('Diag_id').fireEvent('change', base_form.findField('Diag_id'), base_form.findField('Diag_id').getValue());
                        }
                    });
                }
                base_form.findField('MedPersonal_id').getStore().load({
                    callback: function()
                    {
                        base_form.findField('MedPersonal_id').setValue(base_form.findField('MedPersonal_id').getValue());
                        base_form.findField('MedPersonal_id').fireEvent('change', base_form.findField('MedPersonal_id'), base_form.findField('MedPersonal_id').getValue());
                    }
                });
                base_form.findField('MedPersonal_hid').getStore().load({
                    callback: function()
                    {
                        base_form.findField('MedPersonal_hid').setValue(base_form.findField('MedPersonal_hid').getValue());
                        base_form.findField('MedPersonal_hid').fireEvent('change', base_form.findField('MedPersonal_hid'), base_form.findField('MedPersonal_hid').getValue());
                    }
                });
                loadMask.hide();
            },
            url: url
        });
        return true;
    },
	initComponent: function() 
	{
		var me = this;
		this.InformationPanel = new sw.Promed.PersonInformationPanelShort({
			region: 'north'
		});
		this.FormPanel = new Ext.form.FormPanel({
			frame: true,
			layout: 'form',
			region: 'center',
			id: 'FormPanel',
			bodyStyle: 'padding: 5px',
			autoHeight: false,
			labelAlign: 'right',
			labelWidth: 200,
			autoScroll:true,
			url:'/?c=MorbusNephro&m=doSaveEvnNotifyNephro',
			items: 
			[{
				region: 'north',
				layout: 'form',
				xtype: 'panel',
				items: [{
					name: 'EvnNotifyNephro_id',
					xtype: 'hidden'
				}, {
					name: 'EvnNotifyNephro_pid',
					xtype: 'hidden'
				}, {
					name: 'Morbus_id',
					xtype: 'hidden'
				}, {
					name: 'Server_id',
					xtype: 'hidden'
				}, {
					name: 'PersonEvn_id',
					xtype: 'hidden'
				}, {
					name: 'Person_id',
					xtype: 'hidden'
				}, {
                    name: 'PersonHeight_id',
                    xtype: 'hidden'
                }, {
                    name: 'PersonWeight_id',
                    xtype: 'hidden'
                }, {
                    hiddenName: 'Diag_id',
                    fieldLabel: lang['diagnoz'],
                    xtype: 'swdiagcombo',
                    anchor:'100%',
                    MorbusType_SysNick: 'nephro',
                    allowBlank: false
                }, {
                    fieldLabel: lang['data_ustanovleniya'],
                    name: 'EvnNotifyNephro_diagDate',
                    allowBlank: false,
                    xtype: 'swdatefield',
                    plugins: [new Ext.ux.InputTextMask('99.99.9999', false)]
                }, {
                    fieldLabel: lang['data_zabolevaniya_do_ustanovleniya_diagnoza'],
                    name: 'EvnNotifyNephro_firstDate',
                    allowBlank: false,
                    xtype: 'swdatefield',
                    plugins: [new Ext.ux.InputTextMask('99.99.9999', false)]
                }, {
                    fieldLabel: lang['sposob_ustanovleniya_diagnoza'],
                    anchor:'100%',
                    hiddenName: 'NephroDiagConfType_id',
                    xtype: 'swcommonsprcombo',
                    allowBlank: false,
                    sortField:'NephroDiagConfType_Code',
                    comboSubject: 'NephroDiagConfType'
                }, {
                    fieldLabel: 'Стадия ХБП',
                    anchor:'100%',
                    hiddenName: 'NephroCRIType_id',
                    xtype: 'swcommonsprcombo',
                    allowBlank: false,
                    sortField:'NephroCRIType_Code',
                    comboSubject: 'NephroCRIType'
                }, {
                    fieldLabel: lang['arterialnaya_gipertenziya'],
                    width: 70,
                    hiddenName: 'EvnNotifyNephro_IsHyperten',
                    xtype: 'swyesnocombo',
                    allowBlank: false
                }, {
                    fieldLabel: lang['rost_v_sm'],
                    name: 'PersonHeight_Height',
                    width: 100,
                    xtype: 'numberfield',
                    allowNegative: false,
                    allowDecimals: false,
                    decimalPrecision: 0,
                    regex:new RegExp('(^[0-9]{0,3})$'),
                    maxValue: 999,
                    maxLength: 3,
                    maxLengthText: lang['maksimalnaya_dlina_etogo_polya_3_simvola']
                }, {
                    fieldLabel: lang['ves_v_kg'],
                    name: 'PersonWeight_Weight',
                    width: 100,
                    xtype: 'numberfield',
                    allowNegative: false,
                    allowDecimals: false,
                    decimalPrecision: 0,
                    regex:new RegExp('(^[0-9]{0,3})$'),
                    maxValue:999,
                    maxLength: 3,
                    maxLengthText: lang['maksimalnaya_dlina_etogo_polya_3_simvola']
                }, {
                    fieldLabel: lang['naznachennoe_lechenie_dieta_preparatyi'],
                    name: 'EvnNotifyNephro_Treatment',
                    anchor:'100%',
                    maxLength: 100,
                    maxLengthText: lang['maksimalnaya_dlina_etogo_polya_100_simvolov'],
                    xtype: 'textfield'
                }, {
                    xtype: 'fieldset',
                    autoHeight: true,
                    title: lang['poslednie_laboratornyie_dannyie'],
                    style: 'padding: 0; padding-left: 10px',
                    items: [{
                        fieldLabel: lang['kreatinin_krovi'],
                        name: 'EvnNotifyNephro_Kreatinin',
                        allowBlank: false,
                        anchor:'100%',
                        xtype: 'textfield'
                    }, {
                        fieldLabel: lang['gemoglobin'],
                        name: 'EvnNotifyNephro_Haemoglobin',
                        anchor:'100%',
                        xtype: 'textfield'
                    }, {
                        fieldLabel: lang['belok_mochi'],
                        name: 'EvnNotifyNephro_Protein',
                        allowBlank: false,
                        anchor:'100%',
                        xtype: 'textfield'
                    }, {
                        fieldLabel: lang['udelnyiy_ves'],
                        name: 'EvnNotifyNephro_SpecWeight',
                        anchor:'100%',
                        xtype: 'textfield'
                    }, {
                        fieldLabel: lang['tsilindryi'],
                        name: 'EvnNotifyNephro_Cast',
                        anchor:'100%',
                        xtype: 'textfield'
                    }, {
                        fieldLabel: lang['leykotsityi'],
                        name: 'EvnNotifyNephro_Leysk',
                        anchor:'100%',
                        xtype: 'textfield'
                    }, {
                        fieldLabel: lang['eritrotsityi'],
                        name: 'EvnNotifyNephro_Erythrocyt',
                        allowBlank: false,
                        anchor:'100%',
                        xtype: 'textfield'
                    }, {
                        fieldLabel: lang['soli'],
                        name: 'EvnNotifyNephro_Salt',
                        anchor:'100%',
                        xtype: 'textfield'
                    }, {
                        fieldLabel: 'Мочевина',
                        name: 'EvnNotifyNephro_Urea',
                        allowBlank: false,
                        anchor:'100%',
                        xtype: 'textfield'
                    }, {
                        fieldLabel: 'Клубочковая фильтрация',
                        name: 'EvnNotifyNephro_GFiltration',
                        allowBlank: false,
                        anchor:'100%',
                        xtype: 'textfield'
                    }]
                }, {
					fieldLabel: lang['data_zapolneniya'],
					name: 'EvnNotifyNephro_setDate',
					allowBlank: false,
					xtype: 'swdatefield',
					plugins: [new Ext.ux.InputTextMask('99.99.9999', false)]
				}, {
					fieldLabel: lang['lechaschiy_vrach'],
					hiddenName: 'MedPersonal_id',
					listWidth: 750,
					width: 350,
					xtype: 'swmedpersonalcombo',
                    allowBlank: false,
					anchor: false
                }, {
                    fieldLabel: lang['zaveduyuschiy_otdeleniem'],
                    hiddenName: 'MedPersonal_hid',
                    listWidth: 750,
                    width: 350,
                    xtype: 'swmedpersonalcombo',
                    allowBlank: false,
                    anchor: false
				}]
			}]
		});
		Ext.apply(this, 
		{	
			buttons: 
			[{
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
			}],
			items: [this.InformationPanel, this.FormPanel]
		});
		sw.Promed.swEvnNotifyNephroEditWindow.superclass.initComponent.apply(this, arguments);
	}
});
