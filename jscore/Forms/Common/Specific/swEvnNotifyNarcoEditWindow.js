/**
* swEvnNotifyNarcoEditWindow - Извещение об наркологии
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2009-2010 Swan Ltd.
* @author       A.Markoff <markov@swan.perm.ru>
* @version      2012/10
*/

sw.Promed.swEvnNotifyNarcoEditWindow = Ext.extend(sw.Promed.BaseForm, 
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
	height: 520,
	id:'swEvnNotifyNarcoEditWindow',
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
		
		params.MedPersonal_id = base_form.findField('MedPersonal_id').getValue();
		params.EvnNotifyNarco_setDT= base_form.findField('EvnNotifyNarco_setDT').getValue().format('d.m.Y');
		params.Diag_id= base_form.findField('Diag_id').getValue();
		params.Lpu_id= base_form.findField('Lpu_id').getValue();
		
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
						Ext.Msg.alert(lang['oshibka_#']+action.result.Error_Code, action.result.Error_Msg);
					}
				}
			},
			success: function(result_form, action) 
			{
				win.formStatus = 'edit';
				loadMask.hide();
				win.hide();
				if (action.result && action.result.EvnNotifyBase_id) {
					if (action.result.PersonRegister_id) {
						showSysMsg(lang['izveschenie_sozdano_i_patsient_vklyuchen_v_registr']);
					} else {
						showSysMsg(lang['izveschenie_sozdano']);
					}
					win.callback(action.result);
				} else {
					showSysMsg(lang['nepravilnyiy_format_otveta_servera']);
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
		sw.Promed.swEvnNotifyNarcoEditWindow.superclass.show.apply(this, arguments);
		
		var current_window = this;
		if (!arguments[0]) {
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
		
		if (arguments[0].EvnNotifyNarco_id) 
			this.EvnNotifyNarco_id = arguments[0].EvnNotifyNarco_id;
		else 
			this.EvnNotifyNarco_id = null;
			
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
			if ( ( this.EvnNotifyNarco_id ) && ( this.EvnNotifyNarco_id > 0 ) )
				this.action = "view";
			else 
				this.action = "add";
		}
		this.type = 'narco';
		if (arguments[0].type)
		{
			this.type = arguments[0].type;
		}

		base_form.setValues(arguments[0].formParams);
		
		var title = 'Извещение по наркологии «Форма № 091/у»',
			params = null;
		switch (this.action) 
		{
			case 'add':
				this.setTitle(title+lang['_dobavlenie']);
				this.setFieldsDisabled(false);
				if (base_form.findField('EvnNotifyNarco_pid').getValue()) {
					params = {
						EvnNotifyNarco_pid: base_form.findField('EvnNotifyNarco_pid').getValue()
					};
				}
				break;
			case 'view':
				this.setTitle(title+lang['_prosmotr']);
				this.setFieldsDisabled(true);
				params = {
					EvnNotifyNarco_id: this.EvnNotifyNarco_id
				};
				break;
		}
		if (!params) {
			this.hide();
			return false;
		}
		
		var loadMask = new Ext.LoadMask(this.getEl(),{msg: LOAD_WAIT});
		loadMask.show();
		Ext.Ajax.request({
			failure:function (response, options) {
				loadMask.hide();
				current_window.hide();
				sw.swMsg.alert(lang['oshibka'], lang['ne_udalos_poluchit_dannyie_s_servera']);
			},
			params: params,
			success:function (response, options) {
				loadMask.hide();
				var result = Ext.util.JSON.decode(response.responseText);
				base_form.setValues(result[0]);
				if (this.action == 'add') {
					base_form.findField('EvnNotifyNarco_setDT').setValue(getGlobalOptions().date);
					base_form.findField('MedPersonal_id').setValue(getGlobalOptions().medpersonal_id);
					base_form.findField('Lpu_id').setValue(getGlobalOptions().lpu_id);
					base_form.findField('Lpu_id').disable();
				}
				this.InformationPanel.load({
					Person_id: base_form.findField('Person_id').getValue()
				});
				var medpersonal_id = base_form.findField('MedPersonal_id').getValue();
				base_form.findField('MedPersonal_id').setValue(null);
				base_form.findField('MedPersonal_id').getStore().load({
					callback: function()
					{
						base_form.findField('MedPersonal_id').getStore().each(function(record) {
							if ( record.get('MedPersonal_id') == medpersonal_id ) {
								base_form.findField('MedPersonal_id').setValue(medpersonal_id);
							}
						});
					}
				});
				var lpu_id = base_form.findField('Lpu_id').getValue();
				base_form.findField('Lpu_id').setValue(null);
				base_form.findField('Lpu_id').getStore().load({
					callback: function()
					{
						base_form.findField('Lpu_id').getStore().each(function(record) {
							if ( record.get('Lpu_id') == lpu_id ) {
								base_form.findField('Lpu_id').setValue(lpu_id);
							}
						});
					}
				});
				var diag_id = base_form.findField('Diag_id').getValue();
				var diag_sid = base_form.findField('Diag_sid').getValue();
				if(diag_id > 0 ){
					base_form.findField('Diag_sid').setValue(diag_id);
					base_form.findField('Diag_id').getStore().load({
						callback: function() {
							base_form.findField('Diag_id').getStore().each(function(record) {
								if ( record.get('Diag_id') == diag_id ) {
									base_form.findField('Diag_id').fireEvent('select', base_form.findField('Diag_id'), record, 0);
									base_form.findField('Diag_id').fireEvent('change', base_form.findField('Diag_id'), diag_id);
								}
							});
						},
						params: {where: "where Diag_id = " + diag_id}
					});

				}
				if(diag_sid > 0 ){
					base_form.findField('Diag_sid').getStore().load({
						callback: function() {
							base_form.findField('Diag_sid').getStore().each(function(record) {
								if ( record.get('Diag_id') == diag_sid ) {
									base_form.findField('Diag_sid').fireEvent('select', base_form.findField('Diag_sid'), record, 0);
									base_form.findField('Diag_sid').fireEvent('change', base_form.findField('Diag_sid'), diag_sid);
								}
							});
						},
						params: {where: "where Diag_id = " + diag_sid}
					});
				}
			}.createDelegate(this),
			url:'/?c=EvnNotifyNarco&m=load'
		});
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
			autoHeight: true,
			labelAlign: 'right',
			labelWidth: 300,
			url:'/?c=EvnNotifyNarco&m=save',
			items: 
			[{
				region: 'north',
				layout: 'form',
				xtype: 'panel',
				items: [{
					name: 'EvnNotifyNarco_id',
					xtype: 'hidden'
				}, {
					name: 'EvnNotifyNarco_pid',
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
				},{
					comboSubject: 'OnkoOccupationClass',
					fieldLabel: lang['sotsialno-professionalnaya_gruppa'],
					width:350,
					typeCode: 'int',
					editable: true,
					hiddenName: 'Post_id',
					xtype: 'swcommonsprcombo'
				},{
					xtype:'textfield',
					maxLength:64,
					width: 350,
					name:'EvnNotifyNarco_JobPlace',
					hiddenName:'EvnNotifyNarco_JobPlace',
					fieldLabel:lang['mesto_rabotyi']
				},{
					xtype:'swсitizentypecombo',
					hiddenName:'CitizenType',
					fieldLabel:lang['jitel']
				},{
					changeDisabled: false,
					disabled:true,
					fieldLabel: lang['diagnoz'],
					hiddenName: 'Diag_id',
					listWidth: 620,
					width: 350,
					xtype: 'swdiagcombo'
				},{
					fieldLabel: lang['soputstvuyuschiy_diagnoz'],
					hiddenName: 'Diag_sid',
					listWidth: 620,
					width: 350,
					xtype: 'swdiagcombo'
				},{
					xtype:'textfield',
					maxLength:64,
					width: 350,
					hiddenName:'EvnNotifyNarco_NarcoName',
					name:'EvnNotifyNarco_NarcoName',
					fieldLabel:lang['kakie_narkotiki_upotreblyaet']
				},{
					xtype:'swyearcombo',
					hiddenName:'EvnNotifyNarco_NarcoDate',
					fieldLabel:lang['s_kakogo_goda_voznikla_narkomaniya']
					
				},
				{
					xtype:'swnarcousetypecombo',
					hiddenName:'NarcoUseType_id',
					fieldLabel:lang['pri_kakih_obstoyatelstvah_privyik_k_narkotiku']
				},{
					xtype:'swnarcotreatinitiatecombo',
					hiddenName:'NarcoTreatInitiate_id',
					fieldLabel:lang['initsiator_lecheniya']
				},{
					xtype:'swnarcoreceivetypecombo',
					hiddenName:'NarcoReceiveType_id',
					fieldLabel:lang['sposob_polucheniya_narkotikov']
				},{
					changeDisabled: false,
					disabled:true,
					fieldLabel: lang['data_zapolneniya'],
					format: 'd.m.Y',
					hiddenName: 'EvnNotifyNarco_setDT',
					name: 'EvnNotifyNarco_setDT',
					plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
					width: 95,
					xtype: 'swdatefield'
				},{
					changeDisabled: false,
					disabled:true,
					xtype:'swlpucombo',
					name: "Lpu_id"
				},{
					changeDisabled: false,
					disabled:true,
					fieldLabel: lang['vrach_zapolnivshiy_izveschenie'],
					hiddenName: 'MedPersonal_id',
					listWidth: 750,
					width: 350,
					xtype: 'swmedpersonalcombo',
					anchor: false
				}]
			}]
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
		sw.Promed.swEvnNotifyNarcoEditWindow.superclass.initComponent.apply(this, arguments);
	}
});
