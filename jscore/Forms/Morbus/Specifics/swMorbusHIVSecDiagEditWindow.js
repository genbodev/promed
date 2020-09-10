/**
* swMorbusHIVSecDiagEditWindow - Вторичные заболевания и оппортунистические инфекции.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Morbus
* @access       public
* @copyright    Copyright (c) 2009-2012 Swan Ltd.
* @version      2012/12
*/

sw.Promed.swMorbusHIVSecDiagEditWindow = Ext.extend(sw.Promed.BaseForm, 
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

		if(this.formMode == 'local')
		{
			var data = base_form.getValues();
			data.Diag_Name = base_form.findField('Diag_id').getRawValue();
			data.MorbusHIVSecDiag_setDT = (data.MorbusHIVSecDiag_setDT)?Date.parseDate(data.MorbusHIVSecDiag_setDT,'d.m.Y'):null;
			win.hide();
			win.callback(data);
			return true;
		}

		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение..."});
		loadMask.show();
		
		base_form.submit({
			failure: function(result_form, action) 
			{
				loadMask.hide();
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
		sw.Promed.swMorbusHIVSecDiagEditWindow.superclass.show.apply(this, arguments);
		
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
		this.findById('FormPanel').getForm().reset();
		
		this.center();

		var base_form = this.FormPanel.getForm();
		base_form.reset();

		this.formStatus = 'edit';		
		this.action = arguments[0].action || null;
		this.formMode = ( arguments[0].formMode && typeof arguments[0].formMode == 'string' && arguments[0].formMode.inlist([ 'local', 'remote' ]) )? arguments[0].formMode : 'remote';
		this.owner = arguments[0].owner || null;
		this.MorbusHIVSecDiag_id = arguments[0].MorbusHIVSecDiag_id || null;
		this.callback = arguments[0].callback || Ext.emptyFn;
		this.onHide = arguments[0].onHide || Ext.emptyFn;

		if (!this.action) 
		{
			if ( this.MorbusHIVSecDiag_id && this.MorbusHIVSecDiag_id > 0 )
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
				this.setTitle(lang['vtorichnyie_zabolevaniya_i_opportunisticheskie_infektsii_dobavlenie']);
				this.setFieldsDisabled(false);
				break;
			case 'edit':
				this.setTitle(lang['vtorichnyie_zabolevaniya_i_opportunisticheskie_infektsii_redaktirovanie']);
				this.setFieldsDisabled(false);
				break;
			case 'view':
				this.setTitle(lang['vtorichnyie_zabolevaniya_i_opportunisticheskie_infektsii_prosmotr']);
				this.setFieldsDisabled(true);
				break;
		}
		
		if (this.action != 'add' && this.formMode == 'local') {
			var combo = base_form.findField('Diag_id');
			var Diag_id = combo.getValue();
			if(Diag_id && Diag_id > 0)
			{
				combo.getStore().load({
					params: {where: 'where Diag_id = '+ Diag_id},
					callback: function() {
						this.setValue(Diag_id);
					}.createDelegate(combo)
				});
			}
		}

		if (this.action != 'add' && this.formMode != 'local') {
			Ext.Ajax.request({
				failure:function (response, options) {
					sw.swMsg.alert(lang['oshibka'], lang['ne_udalos_poluchit_dannyie_s_servera']);
				},
				params:{
					MorbusHIVSecDiag_id: this.MorbusHIVSecDiag_id
				},
				success:function (response, options) {
					var result = Ext.util.JSON.decode(response.responseText);
					if(result[0].accessType != 1)
					{
						this.action = 'view';
						this.setTitle(lang['vtorichnyie_zabolevaniya_i_opportunisticheskie_infektsii_prosmotr']);
						this.setFieldsDisabled(true);
					}
					base_form.setValues(result[0]);
					var combo = base_form.findField('Diag_id');
					var Diag_id = combo.getValue();
					combo.getStore().load({
						params: {where: 'where Diag_id = '+ Diag_id},
						callback: function() {
							this.setValue(Diag_id);
						}.createDelegate(combo)
					});
					loadMask.hide();
				}.createDelegate(this),
				url:'/?c=MorbusHIV&m=loadMorbusHIVSecDiag'
			});			
		} else {
			loadMask.hide();
			if(!arguments[0].formParams.MorbusHIVSecDiag_setDT) {
				 base_form.findField('MorbusHIVSecDiag_setDT').setValue(getGlobalOptions().date);
			}
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
			labelWidth: 120,
			url:'/?c=MorbusHIV&m=saveMorbusHIVSecDiag',
			items: 
			[{
				name: 'MorbusHIVSecDiag_id',
				xtype: 'hidden'
			}, {
				name: 'MorbusHIV_id',
				xtype: 'hidden'
			}, {
				name: 'EvnNotifyBase_id',
				xtype: 'hidden'
			}, {
				fieldLabel: lang['data'],
				name: 'MorbusHIVSecDiag_setDT',
				allowBlank: false,
				xtype: 'swdatefield',
				plugins: [new Ext.ux.InputTextMask('99.99.9999', false)]
			}, {
				hiddenName: 'Diag_id',
				fieldLabel: lang['zabolevanie'],
				xtype: 'swdiagcombo',
				width: 450,
				allowBlank: false
			}],
			reader: new Ext.data.JsonReader(
			{
				success: Ext.emptyFn
			}, 
			[
				{name: 'MorbusHIVSecDiag_id'},
				{name: 'MorbusHIV_id'},
				{name: 'EvnNotifyBase_id'},
				{name: 'MorbusHIVSecDiag_setDT'},
				{name: 'Diag_id'}
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
		sw.Promed.swMorbusHIVSecDiagEditWindow.superclass.initComponent.apply(this, arguments);
	}
});