/**
* swEvnOnkoNotifyNeglectedEditWindow - Протокол запущенной формы онкозаболевания
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
 * @package      MorbusOnko
 * @author       Пермяков Александр
 * @version      06.2013
* @access       public
* @copyright    Copyright (c) 2009-2010 Swan Ltd.
*/

sw.Promed.swEvnOnkoNotifyNeglectedEditWindow = Ext.extend(sw.Promed.BaseForm, 
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
	width: 650,
	height: 350,
	doSave: function() 
	{
		if ( this.formStatus == 'save' ) {
			return false;
		}
		
		var win = this;
		this.formStatus = 'save';
		
		var form = this.FormPanel;
		var base_form = form.getForm();
		var params = {};

		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					this.formStatus = 'edit';
					form.getFirstInvalidEl().focus(false);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение..."});
		loadMask.show();

        var field = base_form.findField('EvnOnkoNotifyNeglected_setNotifyDT');
        if ( field.disabled ) {
            params.EvnOnkoNotifyNeglected_setNotifyDT = field.getRawValue();
        }
        field = base_form.findField('EvnOnkoNotifyNeglected_setDT');
        if ( field.disabled ) {
            params.EvnOnkoNotifyNeglected_setDT = field.getRawValue();
        }
        field = base_form.findField('OnkoLateDiagCause_id');
        if ( field.disabled ) {
            params.OnkoLateDiagCause_id = field.getValue();
        }
		
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
				win.formStatus = 'edit';
				loadMask.hide();
				var data = {};
				if (typeof action.result == 'object')
				{
					data = action.result;
					if (action.result.success)
					{
						win.callback(data);
						win.hide();
						showSysMsg(lang['protokol_sohranen']);
					}
				}
			}
		});
		
	},
	setFieldsDisabled: function(d) 
	{
		var form = this;
		var base_form = this.findById('FormPanel').getForm();
		
		base_form.items.each(function(f) 
		{
			if (f && (f.xtype!='hidden') && (f.xtype!='fieldset')  && (f.changeDisabled!==false))
			{
				f.setDisabled(d);
			}
		});
		form.buttons[0].setDisabled(d);
	},
	show: function() 
	{
		sw.Promed.swEvnOnkoNotifyNeglectedEditWindow.superclass.show.apply(this, arguments);
		
		var current_window = this;
		if (!arguments[0]) 
		{
			sw.swMsg.show(
			{
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.ERROR,
				msg: lang['oshibka_otkryitiya_formyi_ne_ukazanyi_nujnyie_vhodnyie_parametryi'],
				title: lang['oshibka'],
				fn: function() {
					this.hide();
				}
			});
            return false;
		}
		this.focus();
		this.findById('FormPanel').getForm().reset();
		
		this.center();

		var base_form = this.FormPanel.getForm();
		base_form.reset();

		this.action = null;
		this.callback = Ext.emptyFn;
		this.formMode = 'remote';
		this.formStatus = 'edit';
		this.onHide = Ext.emptyFn;
		
		if (arguments[0].EvnOnkoNotifyNeglected_id) 
			this.EvnOnkoNotifyNeglected_id = arguments[0].EvnOnkoNotifyNeglected_id;
		else 
			this.EvnOnkoNotifyNeglected_id = null;
			
		if (arguments[0].callback) 
		{
			this.callback = arguments[0].callback;
		}	
		if ( arguments[0].formMode && typeof arguments[0].formMode == 'string' && arguments[0].formMode.inlist([ 'local', 'remote' ]) ) 
		{
			this.formMode = arguments[0].formMode;
		}
		if (arguments[0].owner) 
		{
			this.owner = arguments[0].owner;
		}
		if (arguments[0].action) 
		{
			this.action = arguments[0].action;
		}
		else 
		{
			if ( ( this.EvnOnkoNotifyNeglected_id ) && ( this.EvnOnkoNotifyNeglected_id > 0 ) )
				this.action = "edit";
			else 
				this.action = "add";
		}
		
		base_form.setValues(arguments[0].formParams);
        this.setFieldsDisabled(false);
		
		var loadMask = new Ext.LoadMask(this.getEl(),{msg: LOAD_WAIT});
		loadMask.show();

		if (this.action != 'add') {
			Ext.Ajax.request({
				failure:function (response, options) {
					loadMask.hide();
					current_window.hide();
					sw.swMsg.alert(lang['oshibka'], lang['ne_udalos_poluchit_dannyie_s_servera']);
				},
				params:{
					EvnOnkoNotifyNeglected_id: this.EvnOnkoNotifyNeglected_id
				},
				success:function (response, options) {
					var result = Ext.util.JSON.decode(response.responseText);
					base_form.setValues(result[0]);
					loadMask.hide();
                    this.InformationPanel.load({
                        Person_id: result[0].Person_id
                    });
                    base_form.findField('EvnOnkoNotifyNeglected_setNotifyDT').setDisabled(result[0].EvnOnkoNotifyNeglected_setNotifyDT && this.action == 'edit');
                    base_form.findField('EvnOnkoNotifyNeglected_setDT').setDisabled(result[0].EvnOnkoNotifyNeglected_setDT && this.action == 'edit');
                    base_form.findField('OnkoLateDiagCause_id').setDisabled(result[0].OnkoLateDiagCause_id && this.action == 'edit');
				}.createDelegate(this),
				url:'/?c=EvnOnkoNotifyNeglected&m=load'
			});			
		} else {
			this.InformationPanel.load({
				Person_id: arguments[0].formParams.Person_id
			});
			base_form.findField('EvnOnkoNotifyNeglected_setNotifyDT').setValue(getGlobalOptions().date);
			loadMask.hide();			
		}
		switch (this.action) 
		{
			case 'add':
				this.setTitle(lang['protokol_dobavlenie']);
				break;
			case 'edit':
				this.setTitle(lang['protokol_redaktirovanie']);
				break;
		}
		
	},	
	initComponent: function() 
	{
		
		this.InformationPanel = new sw.Promed.PersonInformationPanelShort({
			region: 'north'
		});
		this.FormPanel = new Ext.form.FormPanel(
		{	
			frame: true,
			layout: 'form',
			region: 'center',
			id: 'FormPanel',
			bodyStyle: 'padding: 5px',
			autoHeight: false,
			labelAlign: 'right',
			labelWidth: 250,
			url:'/?c=EvnOnkoNotifyNeglected&m=save',
			items: 
			[{
				region: 'north',
				layout: 'form',
				xtype: 'panel',
				items: [{
					name: 'EvnOnkoNotifyNeglected_id',
					xtype: 'hidden'
				}, {
					name: 'EvnOnkoNotify_id',
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
					allowBlank: true,
					fieldLabel: lang['naimenovanie_uchrejdeniya_gde_provedena_konferentsiya'],
					hiddenName: 'Lpu_cid',
					listWidth: 620,
					width: 350,
					xtype: 'swlpucombo'
				}, {
					allowBlank: true,
					fieldLabel: lang['dannyie_klinicheskogo_razbora_nastoyaschego_sluchaya'],
					name: 'EvnOnkoNotifyNeglected_ClinicalData',
					autoCreate: {tag: "textarea", size: 256, maxLength: "256", autocomplete: "off"},
					xtype: 'textarea',
					width: 350,
					height: 40
				}, {
					allowBlank: true,
					fieldLabel: lang['organizatsionnyie_vyivodyi'],
					name: 'EvnOnkoNotifyNeglected_OrgDescr',
					autoCreate: {tag: "textarea", size: 256, maxLength: "256", autocomplete: "off"},
					xtype: 'textarea',
					width: 350,
					height: 40
				}, {
					allowBlank: true,
					fieldLabel: lang['data_konferentsii'],
					name: 'EvnOnkoNotifyNeglected_setConfDT',
					xtype: 'swdatefield',
					plugins: [new Ext.ux.InputTextMask('99.99.9999', false)]
                }, {
                    allowBlank: false,
                    fieldLabel: lang['data_zapolneniya_protokola'],
                    name: 'EvnOnkoNotifyNeglected_setNotifyDT',
                    xtype: 'swdatefield',
                    plugins: [new Ext.ux.InputTextMask('99.99.9999', false)]
                }, {
                    allowBlank: false,
                    fieldLabel: lang['data_ustanovleniya_zapuschennosti_raka'],
                    name: 'EvnOnkoNotifyNeglected_setDT',
                    xtype: 'swdatefield',
                    plugins: [new Ext.ux.InputTextMask('99.99.9999', false)]
                }, {
                    allowBlank: false,
                    fieldLabel: lang['prichina_pozdnego_ustanovleniya_diagnoza'],
                    xtype: 'swcommonsprlikecombo',
                    comboSubject: 'OnkoLateDiagCause'
				}]
			}],
            reader: new Ext.data.JsonReader({
                success: Ext.emptyFn
            }, [
                {name: 'EvnOnkoNotifyNeglected_id'},
                {name: 'EvnOnkoNotify_id'},
                {name: 'Morbus_id'},
                {name: 'Server_id'},
                {name: 'Person_id'},
                {name: 'PersonEvn_id'},
                {name: 'EvnOnkoNotifyNeglected_ClinicalData'},
                {name: 'EvnOnkoNotifyNeglected_OrgDescr'},
                {name: 'Lpu_cid'},
                {name: 'Lpu_sid'},
                {name: 'Lpu_id'},
                {name: 'OnkoLateDiagCause_id'},
                {name: 'EvnOnkoNotifyNeglected_setConfDT'},
                {name: 'EvnOnkoNotifyNeglected_setNotifyDT'},
                {name: 'EvnOnkoNotifyNeglected_setDT'}
            ])
		});
		Ext.apply(this, 
		{	
			buttons: 
			[{
				handler: function() {
					this.doSave();
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
					this.ownerCt.hide();
				},
				iconCls: 'cancel16',
				text: BTN_FRMCANCEL
			}],
			items: [this.InformationPanel, this.FormPanel]
		});
		sw.Promed.swEvnOnkoNotifyNeglectedEditWindow.superclass.initComponent.apply(this, arguments);
	}
});