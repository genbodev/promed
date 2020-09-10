/**
* swMorbusTubAdviceOperWindow - Оперативное лечение.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2009-2010 Swan Ltd.
* @version      28.12.2012
*/

sw.Promed.swMorbusTubAdviceOperWindow = Ext.extend(sw.Promed.BaseForm, 
{
	action: null,
	winTitle: lang['operativnoe_lechenie'],
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
		
		var params = new Object();
		var data = new Object();

		switch ( this.formMode ) {
			case 'local':
				data.BaseData = {
					'MorbusTubAdviceOper_id': base_form.findField('MorbusTubAdviceOper_id').getValue(),
					'MorbusTubAdviceOper_setDT': base_form.findField('MorbusTubAdviceOper_setDT').getValue(),
					'UslugaComplex_id': base_form.findField('UslugaComplex_id').getValue(),
					'UslugaComplex_Name': base_form.findField('UslugaComplex_id').getRawValue()
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
							if ( action.result.MorbusTubAdviceOper_id > 0 ) {
								base_form.findField('MorbusTubAdviceOper_id').setValue(action.result.MorbusTubAdviceOper_id);

								data.BaseData = {
									'MorbusTubAdviceOper_id': base_form.findField('MorbusTubAdviceOper_id').getValue(),
									'MorbusTubAdviceOper_setDT': base_form.findField('MorbusTubAdviceOper_setDT').getValue(),
									'UslugaComplex_id': base_form.findField('UslugaComplex_id').getValue(),
									'UslugaComplex_Name': base_form.findField('UslugaComplex_id').getRawValue()
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
		sw.Promed.swMorbusTubAdviceOperWindow.superclass.show.apply(this, arguments);
		
		var that = this;
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
		
		this.center();

		var base_form = this.FormPanel.getForm();
		base_form.reset();
		var usluga_combo = base_form.findField('UslugaComplex_id');
		usluga_combo.setAllowedUslugaComplexAttributeList([ 'oper' ]);
		usluga_combo.getStore().baseParams.UslugaComplex_Date = null;

		this.action = null;
		this.formMode = 'remote';
		this.formStatus = 'edit';
		this.onHide = Ext.emptyFn;
		
		this.MorbusTubAdviceOper_id = arguments[0].MorbusTubAdviceOper_id || null;
		this.callback = arguments[0].callback || Ext.emptyFn;
		
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
			if ( ( this.MorbusTubAdviceOper_id ) && ( this.MorbusTubAdviceOper_id > 0 ) )
				this.action = "edit";
			else 
				this.action = "add";
		}
		
		base_form.setValues(arguments[0].formParams);
		
		this.getLoadMask().show();
		switch (this.action) 
		{
			case 'add':
				this.setTitle(this.winTitle +lang['_dobavlenie']);
				this.setFieldsDisabled(false);
				break;
			case 'edit':
				this.setTitle(this.winTitle +lang['_redaktirovanie']);
				this.setFieldsDisabled(false);
				break;
			case 'view':
				this.setTitle(this.winTitle +lang['_prosmotr']);
				this.setFieldsDisabled(true);
				break;
		}
		if(this.action != 'add' && this.formMode == 'remote') {
			Ext.Ajax.request({
				failure:function () {
					sw.swMsg.alert(lang['oshibka'], lang['ne_udalos_poluchit_dannyie_s_servera']);
					that.getLoadMask().hide();
				},
				params:{
					MorbusTubAdviceOper_id: that.MorbusTubAdviceOper_id
				},
				success: function (response) {
					that.getLoadMask().hide();
					var result = Ext.util.JSON.decode(response.responseText);
					if (!result[0]) { return false; }
					base_form.setValues(result[0]);
					base_form.findField('MorbusTubAdviceOper_setDT').focus(true,200);
					var usluga_complex_id = usluga_combo.getValue();
					if ( usluga_complex_id ) {
						usluga_combo.getStore().load({
							callback: function() {
								if ( usluga_combo.getStore().getCount() > 0 ) {
									usluga_combo.setValue(usluga_complex_id);
								}
								else {
									usluga_combo.clearValue();
								}
							}.createDelegate(this),
							params: {
								UslugaComplex_id: usluga_complex_id
							}
						});
					}
				},
				url:'/?c=MorbusTub&m=loadMorbusTubAdviceOper'
			});				
		} else {
			this.getLoadMask().hide();
			base_form.findField('MorbusTubAdviceOper_setDT').focus(true,200);
		}
		
	},	
	initComponent: function() 
	{
		
		this.FormPanel = new Ext.form.FormPanel(
		{	
			autoScroll: true,
			frame: true,
			region: 'north',
			bodyStyle: 'padding: 5px',
			autoHeight: false,
			labelAlign: 'right',
			labelWidth: 200,
			items: 
			[{
				name: 'MorbusTubAdviceOper_id',
				xtype: 'hidden'
			}, {
				name: 'MorbusTubAdvice_id',
				xtype: 'hidden'
			}, {
				fieldLabel: lang['data_operatsii'],
				name: 'MorbusTubAdviceOper_setDT',
				allowBlank: false,
				xtype: 'swdatefield',
				plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
				listeners: {
					'change': function(field, newValue, oldValue) {
						var usluga_combo = this.FormPanel.getForm().findField('UslugaComplex_id');
						// Устанавливаем фильтр по дате для услуг
						usluga_combo.clearValue();
						usluga_combo.lastQuery = 'This query sample that is not will never appear';
						usluga_combo.getStore().baseParams.UslugaComplex_Date = (typeof newValue == 'object' ? Ext.util.Format.date(newValue, 'd.m.Y') : newValue);
						usluga_combo.getStore().removeAll();
					}.createDelegate(this)
				}
			}, {
				fieldLabel: lang['tip_operatsii'],
				hiddenName: 'UslugaComplex_id',
				to: 'MorbusTubAdviceOper',
				allowBlank: false,
				anchor:'100%',
				listWidth: 600,
				xtype: 'swuslugacomplexnewcombo'
			}],
			reader: new Ext.data.JsonReader(
			{
				success: Ext.emptyFn
			}, 
			[
				{name: 'MorbusTubAdviceOper_id'},
				{name: 'MorbusTubAdvice_id'},
				{name: 'MorbusTubAdviceOper_setDT'},
				{name: 'UslugaComplex_id'}
			]),
			url: '/?c=MorbusTub&m=saveMorbusTubAdviceOper'
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
		sw.Promed.swMorbusTubAdviceOperWindow.superclass.initComponent.apply(this, arguments);
	}
});