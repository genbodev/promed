/**
* swEvnNotifyOrphanEditWindow - Направление на включение в регистр по орфанным заболеваниям
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2009-2010 Swan Ltd.
* @version      8.08.2012
*/

sw.Promed.swEvnNotifyOrphanEditWindow = Ext.extend(sw.Promed.BaseForm, 
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
	height: 240,
	doSave: function(options)
	{
		if ( this.formStatus == 'save' || this.action != 'add' ) {
			return false;
		}
		if ( !options || typeof options != 'object' ) {
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
						Ext.Msg.alert(lang['oshibka_#']+action.result.Error_Code, action.result.Error_Message);
					}
				}
			},
			success: function(result_form, action) 
			{
				win.formStatus = 'edit';
				loadMask.hide();
				if (action.result && action.result.EvnNotifyBase_id) {
					if (action.result.PersonRegister_id) {
						showSysMsg(lang['napravlenie_sozdano_i_patsient_vklyuchen_v_registr']);
					} else {
						showSysMsg(lang['napravlenie_sozdano']);
					}
					if (options.print) {
						win.action = 'view';
						win.setFieldsDisabled(true);
						win.EvnNotifyOrphan_id = action.result.EvnNotifyBase_id;
						win.printNotification(win.EvnNotifyOrphan_id);
					} else {
						win.hide();
					}
					win.callback(action.result);
				} else {
					showSysMsg(lang['nepravilnyiy_format_otveta_servera']);
				}
			}
		});
		
	},
	doPrint: function() {
		if (this.action == 'add') {
			this.doSave({print: true});
		} else {
			this.printNotification(this.EvnNotifyOrphan_id);
		}
	},
	printNotification: function(EvnNotifyOrphan_id) {
		if ( !EvnNotifyOrphan_id ) {
			return false;
		}

		printBirt({
			'Report_FileName': 'han_EvnNotifyOrphan.rptdesign',
			'Report_Params': '&paramEvnNotifyOrphan=' + EvnNotifyOrphan_id,
			'Report_Format': 'pdf'
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
		sw.Promed.swEvnNotifyOrphanEditWindow.superclass.show.apply(this, arguments);
		
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
		
		if (arguments[0].EvnNotifyOrphan_id) 
			this.EvnNotifyOrphan_id = arguments[0].EvnNotifyOrphan_id;
		else 
			this.EvnNotifyOrphan_id = null;
			
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
			if ( ( this.EvnNotifyOrphan_id ) && ( this.EvnNotifyOrphan_id > 0 ) )
				this.action = "view";
			else 
				this.action = "add";
		}
		
		base_form.setValues(arguments[0].formParams);
		
		var loadMask = new Ext.LoadMask(this.getEl(),{msg: LOAD_WAIT});
		loadMask.show();
		
		var lpu_combo = base_form.findField('Lpu_oid');

		if (this.action != 'add') {
			Ext.Ajax.request({
				failure:function (response, options) {
					loadMask.hide();
					current_window.hide();
					sw.swMsg.alert(lang['oshibka'], lang['ne_udalos_poluchit_dannyie_s_servera']);
				},
				params:{
					EvnNotifyOrphan_id: this.EvnNotifyOrphan_id
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
					lpu_combo.getStore().load({
						callback: function () {
							if ( lpu_combo.getStore().getCount() > 0 ) {
								lpu_combo.setValue(lpu_combo.getValue());
							}
						}
					});
					loadMask.hide();
				}.createDelegate(this),
				url:'/?c=EvnNotifyOrphan&m=load'
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
			lpu_combo.getStore().load({
				callback: function () {
					if ( lpu_combo.getStore().getCount() > 0 ) {
						lpu_combo.setValue(getGlobalOptions().lpu_id);
					}
				}
			});
			base_form.findField('EvnNotifyOrphan_setDT').setValue(getGlobalOptions().date);
			loadMask.hide();			
		}
				
		switch (this.action) 
		{
			case 'add':
				this.setTitle(lang['napravlenie_na_vklyuchenie_v_registr_po_orfannyim_zabolevaniyam_dobavlenie']);
				this.setFieldsDisabled(false);
				break;
			case 'view':
				this.setTitle(lang['napravlenie_na_vklyuchenie_v_registr_po_orfannyim_zabolevaniyam_prosmotr']);
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
			autoHeight: true,
			labelAlign: 'right',
			labelWidth: 250,
			url:'/?c=EvnNotifyOrphan&m=save',
			items: 
			[{
				region: 'north',
				layout: 'form',
				xtype: 'panel',
				items: [{
					name: 'EvnNotifyOrphan_id',
					xtype: 'hidden'
				}, {
					name: 'EvnNotifyOrphan_pid',
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
					allowBlank: false,
					//enableKeyEvents: true,
					fieldLabel: lang['mo_v_kotoroy_patsientu_vpervyie_ustanovlen_diagnoz_orfannogo_zabolevaniya'],
					hiddenName: 'Lpu_oid',
					listWidth: 620,
					width: 350,
					xtype: 'swlpucombo'
				},{
					name: 'EvnNotifyOrphan_setDT',
					xtype: 'hidden'
				}, {
					changeDisabled: false,
					disabled: true,
					fieldLabel: lang['vrach_zapolnivshiy_napravlenie_na_vklyuchenie_v_registr'],
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
					this.doSave({print: false});
				}.createDelegate(this),
				iconCls: 'save16',
				text: BTN_FRMSAVE
			}, {
				handler: function() {
					this.doPrint();
				}.createDelegate(this),
				iconCls: 'print16',
				text: lang['pechat']
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
		sw.Promed.swEvnNotifyOrphanEditWindow.superclass.initComponent.apply(this, arguments);
	}
});