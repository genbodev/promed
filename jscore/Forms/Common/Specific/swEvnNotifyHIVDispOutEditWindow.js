/**
* swEvnNotifyHIVDispOutEditWindow - Донесение о снятии с диспансерного наблюдения ребенка, рожденного ВИЧ-инфицированной матерью (форма N  310/у)
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2009-2010 Swan Ltd.
* @author       Alexander Permyakov 
* @version      2012/12
*/

sw.Promed.swEvnNotifyHIVDispOutEditWindow = Ext.extend(sw.Promed.BaseForm, 
{
	action: null,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	formMode: 'remote',
	formStatus: 'edit',
	layout: 'border',
	modal: true,
	maximized : true,
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
		
		//params.MedPersonal_id = base_form.findField('MedPersonal_id').getValue();

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
				
				showSysMsg(lang['izveschenie_sozdano']);
				win.formStatus = 'edit';
				loadMask.hide();
				var data = {};
				if (typeof action.result == 'object') {
					data = action.result;
					win.callback(data);
					win.hide();
				}
			}
		});
		
	},
	setFieldsDisabled: function(d) 
	{
		var form = this;
		var base_form = this.FormPanel.getForm();
		
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
		sw.Promed.swEvnNotifyHIVDispOutEditWindow.superclass.show.apply(this, arguments);
		
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
		this.FormPanel.getForm().reset();
		
		this.center();

		var base_form = this.FormPanel.getForm();
		base_form.reset();

		this.formStatus = 'edit';
		this.EvnNotifyHIVDispOut_id = arguments[0].EvnNotifyHIVDispOut_id || null;
		this.action = (( this.EvnNotifyHIVDispOut_id ) && ( this.EvnNotifyHIVDispOut_id > 0 ))?'view':'add';
		this.formMode = (arguments[0].formMode && typeof arguments[0].formMode == 'string' && arguments[0].formMode.inlist([ 'local', 'remote' ]))?arguments[0].formMode:'remote';
		this.owner = arguments[0].owner || null;
		this.callback = arguments[0].callback || Ext.emptyFn;
		this.onHide = arguments[0].onHide || Ext.emptyFn;
		
		base_form.setValues(arguments[0].formParams);
		
		var loadMask = new Ext.LoadMask(this.getEl(),{msg: LOAD_WAIT});
		loadMask.show();
		
		var lpu_combo = base_form.findField('Lpu_rid');
		var diag_combo = base_form.findField('Diag_id');

		if (this.action != 'add') {
			switch (this.action) 
			{
				case 'view':
					this.setTitle(lang['donesenie_o_snyatii_s_dispansernogo_nablyudeniya_rebenka_rojdennogo_vich-infitsirovannoy_materyu_forma_n_310_u_prosmotr']);
					this.setFieldsDisabled(true);
					break;
			}
			Ext.Ajax.request({
				failure:function (response, options) {
					loadMask.hide();
					current_window.hide();
					sw.swMsg.alert(lang['oshibka'], lang['ne_udalos_poluchit_dannyie_s_servera']);
				},
				params:{
					EvnNotifyHIVDispOut_id: this.EvnNotifyHIVDispOut_id
				},
				success:function (response, options) {
					var result = Ext.util.JSON.decode(response.responseText);
					base_form.setValues(result[0]);
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
					diag_combo.getStore().load({
						params: {where: 'where Diag_id = '+ diag_combo.getValue()},
						callback: function () {
							if ( diag_combo.getStore().getCount() > 0 ) {
								diag_combo.setValue(diag_combo.getValue());
							}
						}
					});
					loadMask.hide();
				}.createDelegate(this),
				url:'/?c=EvnNotifyHIVDispOut&m=load'
			});			
		} else {
			this.setTitle(lang['donesenie_o_snyatii_s_dispansernogo_nablyudeniya_rebenka_rojdennogo_vich-infitsirovannoy_materyu_forma_n_310_u_dobavlenie']);
			this.setFieldsDisabled(false);
			base_form.findField('MedPersonal_id').getStore().load({
				callback: function()
				{
					base_form.findField('MedPersonal_id').setValue(base_form.findField('MedPersonal_id').getValue());
					base_form.findField('MedPersonal_id').fireEvent('change', base_form.findField('MedPersonal_id'), base_form.findField('MedPersonal_id').getValue());
				}
			});
			base_form.findField('EvnNotifyHIVDispOut_setDT').setValue(getGlobalOptions().date);
			loadMask.hide();			
		}		
	},	
	initComponent: function() 
	{
		
		this.FormPanel = new Ext.form.FormPanel({
			frame: true,
			layout: 'form',
			region: 'center',
			bodyStyle: 'padding: 5px',
			autoHeight: false,
			labelAlign: 'right',
			labelWidth: 220,
			autoScroll:true,
			url:'/?c=EvnNotifyHIVDispOut&m=save',
			items: 
			[{
				region: 'center',
				layout: 'form',
				xtype: 'panel',
				items: [{
					name: 'EvnNotifyHIVDispOut_id',
					xtype: 'hidden'
				}, {
					name: 'EvnNotifyHIVDispOut_pid',
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
					name: 'Person_mid',
					xtype: 'hidden'
				}, {
					width: 300,
					fieldLabel: lang['mat'],
					allowBlank: false,
					xtype: 'swpersoncomboex',
					onSelectPerson: function(data) {
						var base_form = this.FormPanel.getForm();
						base_form.findField('Person_mid').setValue(data.Person_id);
					}.createDelegate(this), 
					hiddenName: 'mother_fio'
				}, {
					width: 300,
					fieldLabel: lang['rebenok'],
					allowBlank: false,
					xtype: 'swpersoncomboex',
					onSelectPerson: function(data) {
						var base_form = this.FormPanel.getForm();
						base_form.findField('Server_id').setValue(data.Server_id);
						base_form.findField('Person_id').setValue(data.Person_id);
						base_form.findField('PersonEvn_id').setValue(data.PersonEvn_id);
						Ext.Ajax.request({
							failure:function (response, options) {
								sw.swMsg.alert(lang['oshibka'], lang['ne_udalos_poluchit_dannyie_s_servera']);
							},
							params: {Person_id: data.Person_id},
							success:function (response, options) {
								var result  = Ext.util.JSON.decode(response.responseText);
								if(result && result[0]) {
									base_form.findField('Lpu_rid').setValue(result[0].Lpu_id);
								}
							},
							url:'/?c=MorbusHIV&m=defineBirthSvidLpu'
						});
					}.createDelegate(this), 
					hiddenName: 'baby_fio'
				}, {
					fieldLabel: lang['otkaznoy_rebenok'],
					xtype: 'swyesnocombo',
					width: 80,
					hiddenName: 'EvnNotifyHIVDispOut_IsRefuse'
				}, {
					fieldLabel: lang['rebenok'],
					width: 300,
					comboSubject: 'HIVChildType',
					hiddenName: 'HIVChildType_id',
					autoLoad: true,
					xtype: 'swcommonsprcombo'
				}, {
					fieldLabel: 'ЛПУ рождения',//По умолчанию ЛПУ, указанное в свидетельстве о рождении.
					width: 300,
					autoLoad: false,
					allowBlank: true,
					hiddenName: 'Lpu_rid',
					xtype: 'swlpulocalcombo'
				}, {
					fieldLabel: lang['data_snyatiya_s_dispansernogo_nablyudeniya'],
					name: 'EvnNotifyHIVDispOut_endDT',
					xtype: 'swdatefield',
					plugins: [new Ext.ux.InputTextMask('99.99.9999', false)]
				}, {
					fieldLabel: lang['prichina_snyatiya_s_dispansernogo_nablyudeniya'],
					width: 300,
					comboSubject: 'HIVDispOutCauseType',
					hiddenName: 'HIVDispOutCauseType_id',
					autoLoad: true,
					xtype: 'swcommonsprcombo'
				}, {
					fieldLabel: lang['prichina_smerti'],
					width: 300,
					hiddenName: 'Diag_id',
					xtype: 'swdiagcombo'
				}, {
					name: 'MorbusHIVLab_id',
					xtype: 'hidden'
				}, {
					autoHeight: true,
					title: lang['laboratornaya_diagnostika_vich-infektsii'],
					xtype: 'fieldset',
					items: [{
						autoHeight: true,
						title: lang['immunnyiy_blotting'],
						xtype: 'fieldset',
						labelWidth: 80,
						items: [{
							name: 'MorbusHIVLab_TestSystem',
							xtype: 'hidden'
							/*
							fieldLabel: lang['tip_test-sistemyi'],
							name: 'MorbusHIVLab_TestSystem',
							allowBlank:true,
							width: 300,
							maxLength: 64,
							xtype: 'textfield'
							*/
						}, {
							name: 'MorbusHIVLab_BlotNum',
							xtype: 'hidden'
							/*
							fieldLabel: lang['№_serii'],
							name: 'MorbusHIVLab_BlotNum',
							allowBlank:true,
							width: 300,
							maxLength: 64,
							xtype: 'textfield'
							*/
						}, {
							border: false,
							layout: 'column',
							items: [{
								border: false,
								layout: 'form',
								items: [{
									fieldLabel: lang['data'],
									name: 'MorbusHIVLab_BlotDT',
									width: 85,
									xtype: 'swdatefield',
									plugins: [new Ext.ux.InputTextMask('99.99.9999', false)]
								}]
							}, {
								border: false,
								layout: 'form',
								items: [{
									fieldLabel: lang['rezultat'],
									name: 'MorbusHIVLab_BlotResult',
									allowBlank:true,
									width: 200,
									maxLength: 100,
									xtype: 'textfield'
								}]
							}]
						}]
					}, {
						autoHeight: true,
						title: lang['immunofermentnyiy_analiz'],
						xtype: 'fieldset',
						labelWidth: 80,
						items: [{
							name: 'Lpuifa_id',
							xtype: 'hidden'
							/*
							fieldLabel: lang['uchrejdenie_pervichno_vyiyavivshee_polojitelnyiy_rezultat_v_ifa'],
							allowBlank: true,
							width: 300,
							autoLoad: false,
							hiddenName: 'Lpuifa_id',
							xtype: 'swlpulocalcombo'
							*/
						}, {
							border: false,
							layout: 'column',
							items: [{
								border: false,
								layout: 'form',
								items: [{
									fieldLabel: lang['data'],
									name: 'MorbusHIVLab_IFADT',
									width: 85,
									xtype: 'swdatefield',
									plugins: [new Ext.ux.InputTextMask('99.99.9999', false)]
								}]
							}, {
								border: false,
								layout: 'form',
								items: [{
									fieldLabel: lang['rezultat'],
									name: 'MorbusHIVLab_IFAResult',
									width: 200,
									maxLength: 30,
									xtype: 'textfield'
								}]
							}]
						}]
					}, {
						autoHeight: true,
						title: lang['polimeraznaya_tsepnaya_reaktsiya'],
						xtype: 'fieldset',
						labelWidth: 80,
						items: [{
							border: false,
							layout: 'column',
							items: [{
								border: false,
								layout: 'form',
								items: [{
									fieldLabel: lang['data'],
									name: 'MorbusHIVLab_PCRDT',
									width: 85,
									xtype: 'swdatefield',
									plugins: [new Ext.ux.InputTextMask('99.99.9999', false)]
								}]
							}, {
								border: false,
								layout: 'form',
								items: [{
									fieldLabel: lang['rezultat'],
									name: 'MorbusHIVLab_PCRResult',
									width: 200,
									maxLength: 30,
									xtype: 'textfield'
								}]
							}]
						}]
					}]
				}, {
					fieldLabel: lang['data_zapolneniya_izvescheniya'],
					name: 'EvnNotifyHIVDispOut_setDT',
					allowBlank: false,
					xtype: 'swdatefield',
					plugins: [new Ext.ux.InputTextMask('99.99.9999', false)]
				}, {
					fieldLabel: lang['vrach_zapolnivshiy_izveschenie'],
					hiddenName: 'MedPersonal_id',
					allowBlank: false,
					listWidth: 450,
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
				text: BTN_FRMCLOSE
			}],
			items: [this.FormPanel]
		});
		sw.Promed.swEvnNotifyHIVDispOutEditWindow.superclass.initComponent.apply(this, arguments);
	}
});