/**
* swMorbusHepatitisDiagWindow - Диагноз.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2009-2010 Swan Ltd.
* @version      24.05.2012
*/

sw.Promed.swMorbusHepatitisDiagWindow = Ext.extend(sw.Promed.BaseForm, 
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
	doSave: function() 
	{
		if ( this.formStatus == 'save' ) {
			return false;
		}

		var win = this;
		this.formStatus = 'save';
		
		var form = this.FormPanel;
		var base_form = form.getForm();

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
		
		base_form.submit({
			failure: function(result_form, action) 
			{
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
				loadMask.hide();
				win.callback();
				win.hide();
			}
		});
		
	},
	setFieldsDisabled: function(d) 
	{
		var form = this;
		this.FormPanel.items.each(function(f) 
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
		sw.Promed.swMorbusHepatitisDiagWindow.superclass.show.apply(this, arguments);
		
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
		
		if (arguments[0].MorbusHepatitisDiag_id) 
			this.MorbusHepatitisDiag_id = arguments[0].MorbusHepatitisDiag_id;
		else 
			this.MorbusHepatitisDiag_id = null;
			
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
			if ( ( this.MorbusHepatitisDiag_id ) && ( this.MorbusHepatitisDiag_id > 0 ) )
				this.action = "edit";
			else 
				this.action = "add";
		}
		
		base_form.setValues(arguments[0].formParams);
		
		var loadMask = new Ext.LoadMask(this.getEl(),{msg: LOAD_WAIT});
		loadMask.show();
		
		switch (this.action) 
		{
			case 'add':
				this.setTitle(lang['diagnoz_dobavlenie']);
				this.setFieldsDisabled(false);
				break;
			case 'edit':
				this.setTitle(lang['diagnoz_redaktirovanie']);
				this.setFieldsDisabled(false);
				break;
			case 'view':
				this.setTitle(lang['diagnoz_prosmotr']);
				this.setFieldsDisabled(true);
				break;
		}
		
		var medpersonal_id = base_form.findField('MedPersonal_id').getValue();

		if (this.action != 'add') {
			Ext.Ajax.request({
				failure:function (response, options) {
					sw.swMsg.alert(lang['oshibka'], lang['ne_udalos_poluchit_dannyie_s_servera']);
				},
				params:{
					MorbusHepatitisDiag_id: this.MorbusHepatitisDiag_id
				},
				success:function (response, options) {
					var result = Ext.util.JSON.decode(response.responseText);
					//log(result[0]);
					base_form.setValues(result[0]);
					medpersonal_id = base_form.findField('MedPersonal_id').getValue();
					base_form.findField('MedPersonal_id').getStore().load({
						params: { Lpu_id: getGlobalOptions().lpu_id },
						callback: function () {
							if ( medpersonal_id > 0 ) {
								base_form.findField('MedPersonal_id').setValue(medpersonal_id);
							} else if ((getGlobalOptions().medpersonal_id > 0) && (!isSuperAdmin())) {
								base_form.findField('MedPersonal_id').setValue(getGlobalOptions().medpersonal_id);
							}
						}.createDelegate(this)
					});
					loadMask.hide();
				}.createDelegate(this),
				url:'/?c=MorbusHepatitisDiag&m=load'
			});			
		} else {
			base_form.findField('MedPersonal_id').getStore().load({
				params: { Lpu_id: getGlobalOptions().lpu_id },
				callback: function () {
					if ( medpersonal_id > 0 ) {
						base_form.findField('MedPersonal_id').setValue(medpersonal_id);
					} else if ((getGlobalOptions().medpersonal_id > 0) && (!isSuperAdmin())) {
						base_form.findField('MedPersonal_id').setValue(getGlobalOptions().medpersonal_id);
					}
				}.createDelegate(this)
			});
			loadMask.hide();
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
			labelWidth: 200,
			url:'/?c=MorbusHepatitisDiag&m=save',
			items: 
			[{
				name: 'MorbusHepatitisDiag_id',
				xtype: 'hidden'
			}, {
				name: 'MorbusHepatitis_id',
				xtype: 'hidden'
			}, {
				name: 'EvnSection_id',
				xtype: 'hidden'
			}, {
				fieldLabel: lang['data'],
				name: 'MorbusHepatitisDiag_setDT',
				allowBlank: false,
				xtype: 'swdatefield',
				plugins: [new Ext.ux.InputTextMask('99.99.9999', false)]
			}, {
				// hiddenName: '', 
				xtype: 'swlpucombo',
				width: 450,
				listeners: {
					'change': function(field, newValue, oldValue) {
						var base_form = this.FormPanel.getForm();
						base_form.findField('MedPersonal_id').clearValue();
						base_form.findField('MedPersonal_id').getStore().load({
							params: { Lpu_id: newValue }
						});
					}.createDelegate(this)
				},
				value: getGlobalOptions().lpu_id
			}, {
				allowBlank: false,
				fieldLabel: lang['vrach'],
				hiddenName: 'MedPersonal_id',
				listWidth: 750,
				width: 450,
				xtype: 'swmedpersonalcombo',
				anchor: false
			}, {
				allowBlank: false,
				name: 'HepatitisDiagType_id',
                comboSubject: 'HepatitisDiagType',
                fieldLabel: lang['diagnoz'],
				xtype: 'swcommonsprcombo',
				width: 450
			}, {
				fieldLabel: lang['data_podtverjdeniya'],
				name: 'MorbusHepatitisDiag_ConfirmDT',
				allowBlank: true,
				xtype: 'swdatefield',
				plugins: [new Ext.ux.InputTextMask('99.99.9999', false)]
			},  {
				name: 'HepatitisDiagActiveType_id',
                comboSubject: 'HepatitisDiagActiveType',
                fieldLabel: lang['aktivnost'],
				xtype: 'swcommonsprcombo',
				width: 450
			}, {
				name: 'HepatitisFibrosisType_id',
                comboSubject: 'HepatitisFibrosisType',
                fieldLabel: lang['fibroz'],
				xtype: 'swcommonsprcombo',
				width: 450
			}],
			reader: new Ext.data.JsonReader(
			{
				success: Ext.emptyFn
			}, 
			[
				{name: 'MorbusHepatitisDiag_id'},
				{name: 'MorbusHepatitisDiag_setDT'},
				{name: 'MedPersonal_id'},
				{name: 'HepatitisDiagType_id'},
				{name: 'HepatitisDiagActiveType_id'},
				{name: 'HepatitisFibrosisType_id'}
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
		sw.Promed.swMorbusHepatitisDiagWindow.superclass.initComponent.apply(this, arguments);
	}
});