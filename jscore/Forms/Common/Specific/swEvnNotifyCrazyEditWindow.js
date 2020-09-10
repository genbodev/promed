/**
* swEvnNotifyCrazyEditWindow - Извещение об психиатрии и наркологии
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

sw.Promed.swEvnNotifyCrazyEditWindow = Ext.extend(sw.Promed.BaseForm, 
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
	height: 220,
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
		sw.Promed.swEvnNotifyCrazyEditWindow.superclass.show.apply(this, arguments);
		
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
		
		if (arguments[0].EvnNotifyCrazy_id) 
			this.EvnNotifyCrazy_id = arguments[0].EvnNotifyCrazy_id;
		else 
			this.EvnNotifyCrazy_id = null;
			
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
			if ( ( this.EvnNotifyCrazy_id ) && ( this.EvnNotifyCrazy_id > 0 ) )
				this.action = "view";
			else 
				this.action = "add";
		}
		this.type = 'crazy';
		if (arguments[0].type)
		{
			this.type = arguments[0].type;
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
					EvnNotifyCrazy_id: this.EvnNotifyCrazy_id
				},
				success:function (response, options) {
					var result = Ext.util.JSON.decode(response.responseText);
					base_form.setValues(result[0]);
					this.InformationPanel.load({
						Person_id: base_form.findField('Person_id').getValue()
					});
					base_form.findField('MedPersonal_id').getStore().load({
						callback: function()
						{
							base_form.findField('MedPersonal_id').setValue(base_form.findField('MedPersonal_id').getValue());
							base_form.findField('MedPersonal_id').fireEvent('change', base_form.findField('MedPersonal_id'), base_form.findField('MedPersonal_id').getValue());
						}.createDelegate(this)
					});
					loadMask.hide();
				}.createDelegate(this),
				url:'/?c=EvnNotifyCrazy&m=load'
			});			
		} else {
			this.InformationPanel.load({
				Person_id: base_form.findField('Person_id').getValue()
			});
			base_form.findField('MedPersonal_id').getStore().load({
				callback: function()
				{
					base_form.findField('MedPersonal_id').setValue(base_form.findField('MedPersonal_id').getValue());
					base_form.findField('MedPersonal_id').fireEvent('change', base_form.findField('MedPersonal_id'), base_form.findField('MedPersonal_id').getValue());
				}
			});

			base_form.findField('EvnNotifyCrazy_setDT').setValue(getGlobalOptions().date);
			loadMask.hide();			
		}
		var title = lang['izveschenie_o']+((this.type=='crazy')?lang['psihiatricheskom_zabolevanii']:lang['narkologicheskom_zabolevanii']);
		switch (this.action) 
		{
			case 'add':
				this.setTitle(title+lang['_dobavlenie']);
				this.setFieldsDisabled(false);
				break;
			case 'view':
				this.setTitle(title+lang['_prosmotr']);
				this.setFieldsDisabled(true);
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
			url:'/?c=EvnNotifyCrazy&m=save',
			items: 
			[{
				region: 'north',
				layout: 'form',
				xtype: 'panel',
				items: [{
					name: 'EvnNotifyCrazy_id',
					xtype: 'hidden'
				}, {
					name: 'EvnNotifyCrazy_pid',
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
					changeDisabled: false,
					disabled: true,
					fieldLabel: lang['diagnoz'],
					hiddenName: 'Diag_Name',
					listWidth: 620,
					width: 350,
					xtype: 'swdiagcombo'
				}, {
					name: 'EvnNotifyCrazy_setDT',
					xtype: 'hidden'
				}, {
					changeDisabled: false,
					disabled: true,
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
		sw.Promed.swEvnNotifyCrazyEditWindow.superclass.initComponent.apply(this, arguments);
	}
});
