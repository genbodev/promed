/**
* swEvnInfectNotifyEditWindow - Извещение форма №058/У
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2009-2010 Swan Ltd.
* @version      3.08.2012
*/

sw.Promed.swEvnInfectNotifyEditWindow = Ext.extend(sw.Promed.BaseForm, 
{
	action: null,
	autoHeight: true,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	draggable: true,
	formMode: 'remote',
	formStatus: 'edit',
	modal: true,
	width: 650,
	doSave: function() 
	{
		if ( this.formStatus == 'save' ) {
			return false;
		}
		
		var win = this;
		this.formStatus = 'save';
		
		var form = this.FormPanel;
		var base_form = form.getForm();
		var params = new Object();

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
		
		params.EvnInfectNotify_IsLabDiag = base_form.findField('EvnInfectNotify_IsLabDiag').getValue();
		params.MedPersonal_id = base_form.findField('MedPersonal_id').getValue();
		
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
				win.callback();
				win.hide();
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
		sw.Promed.swEvnInfectNotifyEditWindow.superclass.show.apply(this, arguments);
		
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
		
		if (arguments[0].EvnInfectNotify_id) 
			this.EvnInfectNotify_id = arguments[0].EvnInfectNotify_id;
		else 
			this.EvnInfectNotify_id = null;
			
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
			if ( ( this.EvnInfectNotify_id ) && ( this.EvnInfectNotify_id > 0 ) )
				this.action = "view";
			else 
				this.action = "add";
		}
		
		base_form.setValues(arguments[0].formParams);
		
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
					EvnInfectNotify_id: this.EvnInfectNotify_id
				},
				success:function (response, options) {
					var result = Ext.util.JSON.decode(response.responseText);
					base_form.setValues(result[0]);
					base_form.findField('MedPersonal_id').getStore().load({
						callback: function()
						{
							base_form.findField('MedPersonal_id').setValue(base_form.findField('MedPersonal_id').getValue());
							base_form.findField('MedPersonal_id').fireEvent('change', base_form.findField('MedPersonal_id'), base_form.findField('MedPersonal_id').getValue());
						}
					});
					loadMask.hide();
				}.createDelegate(this),
				url:'/?c=EvnInfectNotify&m=load'
			});			
		} else {
			base_form.findField('MedPersonal_id').getStore().load({
				callback: function()
				{
					base_form.findField('MedPersonal_id').setValue(base_form.findField('MedPersonal_id').getValue());
					base_form.findField('MedPersonal_id').fireEvent('change', base_form.findField('MedPersonal_id'), base_form.findField('MedPersonal_id').getValue());
				}
			});
			base_form.findField('EvnInfectNotify_DiseaseDate').setValue(getGlobalOptions().date);
			loadMask.hide();			
		}
				
		switch (this.action) 
		{
			case 'add':
				this.setTitle(lang['izveschenie_forma_№058_u_dobavlenie']);
				this.setFieldsDisabled(false);
				break;
			case 'view':
				this.setTitle(lang['izveschenie_forma_№058_u_prosmotr']);
				this.setFieldsDisabled(true);
				break;
		}
		
	},	
	initComponent: function() 
	{
		
		this.FormPanel = new Ext.form.FormPanel(
		{	
			autoScroll: true,
			frame: true,
			region: 'north',
			id: 'FormPanel',
			bodyStyle: 'padding: 5px',
			autoHeight: false,
			labelAlign: 'right',
			labelWidth: 250,
			url:'/?c=EvnInfectNotify&m=save',
			items: 
			[{
				region: 'north',
				layout: 'form',
				xtype: 'panel',
				id: 'EIN_FormPanel',
				items: [{
					name: 'EvnInfectNotify_id',
					xtype: 'hidden'
				}, {
					name: 'EvnInfectNotify_pid',
					xtype: 'hidden'
				}, {
					name: 'Server_id',
					xtype: 'hidden'
				}, {
					name: 'PersonEvn_id',
					xtype: 'hidden'
				}, {
					changeDisabled: false,
					disabled: true,
					fieldLabel: lang['diagnoz'],
					hiddenName: 'Diag_Name',
					listWidth: 620,
					//valueField: 'Diag_Code',
					width: 350,
					xtype: 'swdiagcombo'
				}, {
					allowBlank: false,
					width: 95,
					fieldLabel: lang['podtverjden_laboratorno'],
					hiddenName: 'EvnInfectNotify_IsLabDiag',
					xtype: 'swyesnocombo',
					value: 2
				}, {
					allowBlank: false,
					fieldLabel: lang['data_zabolevaniya'],
					name: 'EvnInfectNotify_DiseaseDate',
					xtype: 'swdatefield',
					plugins: [new Ext.ux.InputTextMask('99.99.9999', false)]
				}, {
					allowBlank: false,
					fieldLabel: lang['data_pervichnogo_obrascheniya_vyiyavleniya'],
					name: 'EvnInfectNotify_FirstTreatDate',
					xtype: 'swdatefield',
					plugins: [new Ext.ux.InputTextMask('99.99.9999', false)]
				}, {
					allowBlank: false,
					fieldLabel: lang['data_ustanovleniya_diagnoza'],
					name: 'EvnInfectNotify_SetDiagDate',
					xtype: 'swdatefield',
					plugins: [new Ext.ux.InputTextMask('99.99.9999', false)]
				}, {
					fieldLabel: lang['data_poslednego_posescheniya_detskogo_uchrejdeniya_shkolyi'],
					name: 'EvnInfectNotify_NextVizitDate',
					xtype: 'swdatefield',
					plugins: [new Ext.ux.InputTextMask('99.99.9999', false)]
				}, {
					fieldLabel: lang['mesto_gospitalizatsii'],
					hiddenName: 'Lpu_id',
					xtype: 'swlpucombo',
					width: 350
				}, {
					fieldLabel: lang['gde_proizoshlo_otravlenie_chem'],
					name: 'EvnInfectNotify_PoisonDescr',
					autoCreate: {tag: "textarea", size:64, maxLength: "64", autocomplete: "off"},
					xtype: 'textarea',
					width: 350,
					height: 40
				}, {
					fieldLabel: lang['provedennyie_pervichnyie_protivoepidemicheskie_meropriyatiya_i_dopolnitelnyie_svedeniya'],
					name: 'EvnInfectNotify_FirstMeasures',
					autoCreate: {tag: "textarea", size:64, maxLength: "64", autocomplete: "off"},
					xtype: 'textarea',
					width: 350,
					height: 50
				}, { 
					layout: 'column',
					border: false,								
					autoHeight: true,
					items: [{
						layout: 'form',
						border: false,
						items: [{
							fieldLabel: lang['data_i_chas_pervichnoy_signalizatsii_v_ses'],
							format: 'd.m.Y',									
							name: 'EvnInfectNotify_FirstSESDT_Date',
							plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
							width: 100,
							value: '',
							xtype: 'swdatefield'						
						}]
					}, {
						layout: 'form',
						border: false,
						labelWidth: 1,
						items: [{
							labelSeparator: '',
							format: 'H:i',
							name: 'EvnInfectNotify_FirstSESDT_Time',
							plugins: [ new Ext.ux.InputTextMask('99:99', true) ],
							tabIndex: TABINDEX_EREF + 12,
							validateOnBlur: false,
							width: 60,
							value: '',
							listeners: {
								'keydown': function (inp, e) {
									if ( e.getKey() == Ext.EventObject.F4 ) {
										e.stopEvent();
										inp.onTriggerClick();
									}
								}
							},
							xtype: 'swtimefield'
						}]
					}]
				}, {
					changeDisabled: false,
					disabled: true,
					fieldLabel: lang['familiya_soobschivshego'],
					hiddenName: 'MedPersonal_id',
					listWidth: 750,
					width: 350,
					xtype: 'swmedpersonalcombo',
					anchor: false
				}, {
					fieldLabel: lang['kto_prinyal_soobschenie'],
					name: 'EvnInfectNotify_ReceiverMessage',
					autoCreate: {tag: "input", size:64, maxLength: "64", autocomplete: "off"},
					xtype: 'textfield',
					width: 350
				}]
			}],
			reader: new Ext.data.JsonReader(
			{
				success: Ext.emptyFn
			}, 
			[
				{name: 'EvnInfectNotify_id'},
				{name: 'MorbusHepatitis_id'},
				{name: 'HepatitisQueueType_id'},
				{name: 'EvnInfectNotifyEdit_Num'},
				{name: 'EvnInfectNotifyEdit_IsCure'}
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
			items: [this.FormPanel]
		});
		sw.Promed.swEvnInfectNotifyEditWindow.superclass.initComponent.apply(this, arguments);
	}
});