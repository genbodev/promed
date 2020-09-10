/**
* swMorbusHepatitisLabConfirmWindow - Лабораторные подтверждения.
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

sw.Promed.swMorbusHepatitisLabConfirmWindow = Ext.extend(sw.Promed.BaseForm, 
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
		
		var hepatitis_lab_confirm_type_id = base_form.findField('HepatitisLabConfirmType_id').getValue();
		var hepatitis_lab_confirm_type_name = '';
		
		var index;
		var params = new Object();
		
		index = base_form.findField('HepatitisLabConfirmType_id').getStore().findBy(function(rec) {
			if ( rec.get('HepatitisLabConfirmType_id') == hepatitis_lab_confirm_type_id ) {
				return true;
			}
			else {
				return false;
			}
		});		

		if ( index >= 0 ) {
			hepatitis_lab_confirm_type_name = base_form.findField('HepatitisLabConfirmType_id').getStore().getAt(index).get('HepatitisLabConfirmType_Name');
		}
		
		var data = new Object();

		switch ( this.formMode ) {
			case 'local':
				data.BaseData = {
					'MorbusHepatitisLabConfirm_id': base_form.findField('MorbusHepatitisLabConfirm_id').getValue(),
					'MorbusHepatitisLabConfirm_setDT': base_form.findField('MorbusHepatitisLabConfirm_setDT').getValue(),
					'HepatitisLabConfirmType_id': hepatitis_lab_confirm_type_id,
					'HepatitisLabConfirmType_Name': hepatitis_lab_confirm_type_name,
					'MorbusHepatitisLabConfirm_Result': base_form.findField('MorbusHepatitisLabConfirm_Result').getValue()					
				};

				this.callback(data);

				this.formStatus = 'edit';
				loadMask.hide();

				this.hide();
			break;

			case 'remote':
				base_form.submit({
					failure: function(result_form, action) {
						this.formStatus = 'edit';
						loadMask.hide();

						if ( action.result ) {
							if ( action.result.Error_Msg ) {
								sw.swMsg.alert(lang['oshibka'], action.result.Error_Msg);
							}
							else {
								sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_1]']);
							}
						}
					}.createDelegate(this),
					params: params,
					success: function(result_form, action) {
						this.formStatus = 'edit';
						loadMask.hide();

						if ( action.result ) {
							if ( action.result.MorbusHepatitisLabConfirm_id > 0 ) {
								base_form.findField('MorbusHepatitisLabConfirm_id').setValue(action.result.MorbusHepatitisLabConfirm_id);

								data.BaseData = {
									'MorbusHepatitisLabConfirm_id': base_form.findField('MorbusHepatitisLabConfirm_id').getValue(),
									'MorbusHepatitisLabConfirm_setDT': base_form.findField('MorbusHepatitisLabConfirm_setDT').getValue(),
									'HepatitisLabConfirmType_id': hepatitis_lab_confirm_type_id,
									'HepatitisLabConfirmType_Name': hepatitis_lab_confirm_type_name,
									'MorbusHepatitisLabConfirm_Result': base_form.findField('MorbusHepatitisLabConfirm_Result').getValue()
								};

								this.callback(data);
								this.hide();
							}
							else {
								if ( action.result.Error_Msg ) {
									sw.swMsg.alert(lang['oshibka'], action.result.Error_Msg);
								}
								else {
									sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_3]']);
								}
							}
						}
						else {
							sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_2]']);
						}
					}.createDelegate(this)
				});
			break;

			default:
				loadMask.hide();
			break;
			
		}		
		
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
		sw.Promed.swMorbusHepatitisLabConfirmWindow.superclass.show.apply(this, arguments);
		
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
		
		if (arguments[0].MorbusHepatitisLabConfirm_id) 
			this.MorbusHepatitisLabConfirm_id = arguments[0].MorbusHepatitisLabConfirm_id;
		else 
			this.MorbusHepatitisLabConfirm_id = null;
			
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
			if ( ( this.MorbusHepatitisLabConfirm_id ) && ( this.MorbusHepatitisLabConfirm_id > 0 ) )
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
				this.setTitle(lang['laboratornyie_podtverjdeniya_dobavlenie']);
				this.setFieldsDisabled(false);
				break;
			case 'edit':
				this.setTitle(lang['laboratornyie_podtverjdeniya_redaktirovanie']);
				this.setFieldsDisabled(false);
				break;
			case 'view':
				this.setTitle(lang['laboratornyie_podtverjdeniya_prosmotr']);
				this.setFieldsDisabled(true);
				break;
		}
		
		loadMask.hide();
		
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
			items: 
			[{
				name: 'MorbusHepatitisLabConfirm_id',
				xtype: 'hidden'
			}, {
				fieldLabel: lang['data_issledovaniya'],
				name: 'MorbusHepatitisLabConfirm_setDT',
				allowBlank: false,
				xtype: 'swdatefield',
				plugins: [new Ext.ux.InputTextMask('99.99.9999', false)]
			}, {
				name: 'HepatitisLabConfirmType_id',
                comboSubject: 'HepatitisLabConfirmType',
                fieldLabel: lang['tip_laboratornogo_podtverjdeniya'],
				xtype: 'swcommonsprcombo',
				width: 450
			}, {
				name: 'MorbusHepatitisLabConfirm_Result',
                fieldLabel: lang['rezultat_issledovaniya'],
				xtype: 'textfield',
				width: 450
			}],
			reader: new Ext.data.JsonReader(
			{
				success: Ext.emptyFn
			}, 
			[
				{name: 'MorbusHepatitisLabConfirm_id'},
				{name: 'MorbusHepatitisLabConfirm_setDT'},
				{name: 'HepatitisLabConfirmType_id'},
				{name: 'MorbusHepatitisLabConfirm_Result'}
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
		sw.Promed.swMorbusHepatitisLabConfirmWindow.superclass.initComponent.apply(this, arguments);
	}
});