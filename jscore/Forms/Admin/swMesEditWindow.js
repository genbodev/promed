/**
* swMesEditWindow - окно редактирования/добавления МЭС.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2009-2010 Swan Ltd.
* @author       Pshenicyn Ivan aka IVP (ipshon@rambler.ru)
* @version      16.02.2010
* @comment      Префикс для id компонентов MEW (MesEditWindow)
*/

sw.Promed.swMesEditWindow = Ext.extend(sw.Promed.BaseForm, {
	action: null,
	autoHeight: true,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	draggable: true,
	split: true,
	width: 600,
	layout: 'form',
	id: 'MesEditWindow',
	listeners: 
	{
		hide: function() 
		{
			this.onHide();
		}
	},
	modal: true,
	onHide: Ext.emptyFn,
	plain: true,
	resizable: false,
	doSave: function() 
	{
		var form = this.findById('MesEditForm');
		if ( !form.getForm().isValid() ) 
		{
			sw.swMsg.show(
			{
				buttons: Ext.Msg.OK,
				fn: function() 
				{
					form.getFirstInvalidEl().focus(true);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
		if ( this.action == 'add' && form.getForm().findField('Mes_begDT').getValue() < Date.parseDate(getGlobalOptions().date, 'd.m.Y') )
		{
			sw.swMsg.alert(lang['oshibka'], lang['data_nachala_deystviya'] + getMESAlias() + lang['ne_doljna_byit_ranshe_tekuschey_datyi'], function() {
				form.getForm().findField('Mes_begDT').focus();
			});
			return;
		}
		if ( (form.getForm().findField('Mes_endDT').getValue() != '') && (form.getForm().findField('Mes_endDT').getValue() < form.getForm().findField('Mes_begDT').getValue()) )
		{
			sw.swMsg.alert(lang['oshibka'], lang['data_okonchaniya_deystviya'] + getMESAlias() + lang['ne_doljna_byit_ranshe_datyi_nachala_deystviya'], function() {
				form.getForm().findField('Mes_endDT').focus();
			});
			return;
		}
		this.submit();
		return true;
	},
	submit: function() 
	{
		var form = this.findById('MesEditForm');
		var current_window = this;
		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение..."});
		var params =
		{
			action: current_window.action,
			Mes_Code: form.getForm().findField('Mes_Code').getValue()			
		};
		if ( this.action == 'edit' )
		{
			params.MesProf_id = form.getForm().findField('MesProf_id').getValue();
			params.MesAgeGroup_id = form.getForm().findField('MesAgeGroup_id').getValue();
			params.MesLevel_id = form.getForm().findField('MesLevel_id').getValue();
			params.Diag_id = form.getForm().findField('Diag_id').getValue();
			params.OmsLpuUnitType_id = form.getForm().findField('OmsLpuUnitType_id').getValue();
			//params.Mes_KoikoDni = form.getForm().findField('Mes_KoikoDni').getValue();
			params.Mes_begDT = ( form.getForm().findField('Mes_begDT').getValue().format ) ? form.getForm().findField('Mes_begDT').getValue().format('d.m.Y') : form.getForm().findField('Mes_begDT').getValue();
			params.Mes_endDT = ( form.getForm().findField('Mes_endDT').getValue().format ) ? form.getForm().findField('Mes_endDT').getValue().format('d.m.Y') : form.getForm().findField('Mes_endDT').getValue();
		}
		loadMask.show();
		form.getForm().submit(
		{
			params: params,
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
				if (action.result)
				{
					if (action.result.Mes_id)
					{
						current_window.hide();
						Ext.getCmp('MesEditWindow').callback({
							Mes_id: action.result.Mes_id,
							Mes_Code: form.getForm().findField('Mes_Code').getValue(),
							MesProf_CodeName: form.getForm().findField('MesProf_id').getRawValue(),
							MesAgeGroup_CodeName: form.getForm().findField('MesAgeGroup_id').getRawValue(),
							OmsLpuUnitType_CodeName: form.getForm().findField('OmsLpuUnitType_id').getRawValue(),
							MesLevel_CodeName: form.getForm().findField('MesLevel_id').getRawValue(),
							Mes_KoikoDni: form.getForm().findField('Mes_KoikoDni').getValue(),
							Diag_CodeName: form.getForm().findField('Diag_id').getRawValue(),
							Mes_begDT: form.getForm().findField('Mes_begDT').getValue(),
							Mes_endDT: form.getForm().findField('Mes_endDT').getValue()
						});
						
					}
					else
					{
						sw.swMsg.show(
						{
							buttons: Ext.Msg.OK,
							fn: function() 
							{
								form.hide();
							},
							icon: Ext.Msg.ERROR,
							msg: lang['pri_vyipolnenii_operatsii_sohraneniya_proizoshla_oshibka_pojaluysta_povtorite_popyitku_chut_pozje'],
							title: lang['oshibka']
						});
					}
				}
			}
		});
	},
	enableEdit: function(enable) 
	{
		var form = this;
		if (enable) 
		{
			var form = this.findById('MesEditForm');
			form.getForm().findField('MesProf_id').enable();
			form.getForm().findField('MesAgeGroup_id').enable();
			form.getForm().findField('MesLevel_id').enable();
			form.getForm().findField('Diag_id').enable();
			form.getForm().findField('OmsLpuUnitType_id').enable();
			form.getForm().findField('Mes_KoikoDni').enable();
			form.getForm().findField('Mes_begDT').enable();
			form.getForm().findField('Mes_endDT').enable();
			form.getForm().findField('Mes_DiagClinical').enable();
			form.getForm().findField('Mes_Consulting').enable();
			form.getForm().findField('Mes_DiagVolume').enable();
			form.getForm().findField('Mes_CureVolume').enable();
			form.getForm().findField('Mes_QualityMeasure').enable();
			form.getForm().findField('Mes_ResultClass').enable();
			form.getForm().findField('Mes_ComplRisk').enable();
			this.buttons[0].enable();
		}
		else 
		{
			var form = this.findById('MesEditForm');
			form.getForm().findField('MesProf_id').disable();
			form.getForm().findField('MesAgeGroup_id').disable();
			form.getForm().findField('MesLevel_id').disable();
			form.getForm().findField('Diag_id').disable();
			form.getForm().findField('OmsLpuUnitType_id').disable();
			form.getForm().findField('Mes_KoikoDni').disable();
			form.getForm().findField('Mes_begDT').disable();
			form.getForm().findField('Mes_endDT').disable();
			form.getForm().findField('Mes_DiagClinical').disable();
			form.getForm().findField('Mes_Consulting').disable();
			form.getForm().findField('Mes_DiagVolume').disable();
			form.getForm().findField('Mes_CureVolume').disable();
			form.getForm().findField('Mes_QualityMeasure').disable();
			form.getForm().findField('Mes_ResultClass').disable();
			form.getForm().findField('Mes_ComplRisk').disable();
			this.buttons[0].disable();			
		}
	},
	redisableBuffButtons: function()
	{
		// тупо конечно, но дисаблим или энаблим кнопки в зависимости от состояния буфера
		if ( this.memoBuffer['Mes_DiagClinical'] == '' )
			Ext.getCmp('Mes_DiagClinical_Buff_Button').disable();
		else
			Ext.getCmp('Mes_DiagClinical_Buff_Button').enable();
			
		if ( this.memoBuffer['Mes_DiagVolume'] == '' )
			Ext.getCmp('Mes_DiagVolume_Buff_Button').disable();
		else
			Ext.getCmp('Mes_DiagVolume_Buff_Button').enable();
			
		if ( this.memoBuffer['Mes_Consulting'] == '' )
			Ext.getCmp('Mes_Consulting_Buff_Button').disable();
		else
			Ext.getCmp('Mes_Consulting_Buff_Button').enable();
		
		if ( this.memoBuffer['Mes_CureVolume'] == '' )
			Ext.getCmp('Mes_CureVolume_Buff_Button').disable();
		else
			Ext.getCmp('Mes_CureVolume_Buff_Button').enable();
			
		if ( this.memoBuffer['Mes_QualityMeasure'] == '' )
			Ext.getCmp('Mes_QualityMeasure_Buff_Button').disable();
		else
			Ext.getCmp('Mes_QualityMeasure_Buff_Button').enable();
			
		if ( this.memoBuffer['Mes_ResultClass'] == '' )
			Ext.getCmp('Mes_ResultClass_Buff_Button').disable();
		else
			Ext.getCmp('Mes_ResultClass_Buff_Button').enable();
			
		if ( this.memoBuffer['Mes_ComplRisk'] == '' )
			Ext.getCmp('Mes_ComplRisk_Buff_Button').disable();
		else
			Ext.getCmp('Mes_ComplRisk_Buff_Button').enable();	
	},
	show: function() 
	{
		sw.Promed.swMesEditWindow.superclass.show.apply(this, arguments);
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
		this.findById('MesEditForm').getForm().reset();
		this.callback = Ext.emptyFn;
		this.onHide = Ext.emptyFn;
		if (arguments[0].Mes_id) 
			this.Mes_id = arguments[0].Mes_id;
		else 
			this.Mes_id = null;
			
		if (arguments[0].callback) 
		{
			this.callback = arguments[0].callback;
		}
		if (arguments[0].owner) 
		{
			this.owner = arguments[0].owner;
		}
		if (arguments[0].onHide) 
		{
			this.onHide = arguments[0].onHide;
		}
		if (arguments[0].action) 
		{
			this.action = arguments[0].action;
		}
		else 
		{
			if ( ( this.Mes_id ) && ( this.Mes_id > 0 ) )
				this.action = "edit";
			else 
				this.action = "add";
		}
		
		var form = this.findById('MesEditForm');
		form.getForm().setValues(arguments[0]);
		
		var loadMask = new Ext.LoadMask(this.getEl(),{msg: LOAD_WAIT});
		loadMask.show();
		switch (this.action) 
		{
			case 'add':
				this.setTitle(getMESAlias() + lang['_dobavlenie']);
				this.enableEdit(true);
				loadMask.hide();
				form.getForm().clearInvalid();
				break;
			case 'edit':
				this.setTitle(getMESAlias() + lang['_redaktirovanie']);
				this.enableEdit(true);
				// пока безусловно дисаблим при редактировании
				form.getForm().findField('MesProf_id').disable();
				form.getForm().findField('MesAgeGroup_id').disable();
				form.getForm().findField('MesLevel_id').disable();
				form.getForm().findField('Diag_id').disable();
				form.getForm().findField('OmsLpuUnitType_id').disable();
				form.getForm().findField('Mes_begDT').disable();
				// временно
				/*form.getForm().findField('Mes_endDT').disable();
				form.getForm().findField('Mes_KoikoDni').disable();*/
				break;
			case 'view':
				this.setTitle(getMESAlias() + lang['_prosmotr']);
				this.enableEdit(false);
				break;
		}
		
		this.redisableBuffButtons();
		
		if (this.action != 'add')
		{
			form.getForm().load(
			{
				params: 
				{
					Mes_id: current_window.Mes_id
				},
				failure: function() 
				{
					loadMask.hide();
					sw.swMsg.show(
					{
						buttons: Ext.Msg.OK,
						fn: function() 
						{
							current_window.hide();
						},
						icon: Ext.Msg.ERROR,
						msg: lang['oshibka_zaprosa_k_serveru_poprobuyte_povtorit_operatsiyu'],
						title: lang['oshibka']
					});
				},
				success: function() 
				{
					loadMask.hide();
					if ( form.getForm().findField('Mes_endDT').getValue() != '' )
						form.getForm().findField('Mes_endDT').disable();
					var diag_combo = form.getForm().findField('Diag_id');
					diag_combo.getStore().load({
						params: { where: "where Diag_id = " + diag_combo.getValue() },
						callback: function() {
							diag_combo.setValue(diag_combo.getValue());
							diag_combo.getStore().each(function(record) {
								if (record.data.Diag_id == diag_combo.getValue())
								{
									  diag_combo.fireEvent('select', diag_combo, record, 0);
								}
							});
						}
					});
					if (current_window.action=='edit')
					{
						form.getForm().findField('Mes_KoikoDni').focus(true, 100);
					}
					else 
						current_window.buttons[3].focus();
				},
				url: '/?c=Mes&m=loadMes'
			});
		}
		if ( this.action != 'view' )
			form.getForm().findField('MesProf_id').focus(true, 100);
		else
			this.buttons[3].focus();
	},
	
	initComponent: function() 
	{
		// Форма с полями 
		var current_window = this;
		
		this.mesEditForm = new Ext.form.FormPanel(
		{
			autoHeight: true,
			//bodyStyle: 'padding: 5px',
			border: false,
			buttonAlign: 'left',
			frame: false,
			id: 'MesEditForm',
			labelAlign: 'right',
			labelWidth: 200,
			items: 
			[{
				id: 'MEW_Mes_id',
				name: 'Mes_id',
				value: 0,
				xtype: 'hidden'
			}, 
			new Ext.Panel({
				height: Ext.isIE ? 265 : 250,
				bodyStyle: 'padding-top: 0.2em;',
				border: true,
				frame: true,
				style: 'margin-bottom: 0.1em;',
				items: [{
					allowBlank: false,
					anchor: '95%',
					disabled: true,
					xtype: 'textfield',
					fieldLabel: lang['kod'] + getMESAlias(),
					name: 'Mes_Code',
					width: 250
				}, {
					allowBlank: false,
					anchor: '95%',
					enableKeyEvents: true,
					listeners: {
						'keydown': function (inp, e) {
                            if (e.shiftKey == false && e.getKey() == Ext.EventObject.TAB)
                            {
 								e.stopEvent();
								var form = Ext.getCmp('MesEditForm');
								form.getForm().findField('MesAgeGroup_id').focus(true);
							}                            
						}
					},
					hiddenName: 'MesProf_id',
					width: 250,
					tabIndex: TABINDEX_MEW + 16,
					xtype: 'swmesprofcombo'
				}, {
					allowBlank: false,
					anchor: '95%',
					enableKeyEvents: true,
					listeners: {
						'keydown': function (inp, e) {
                            if (e.shiftKey == true && e.getKey() == Ext.EventObject.TAB)
                            {
 								e.stopEvent();
								var form = Ext.getCmp('MesEditForm');
								form.getForm().findField('MesProf_id').focus(true);
							}                            
						}
					},
					hiddenName: 'MesAgeGroup_id',
					width: 250,
					tabIndex: TABINDEX_MEW + 1,
					xtype: 'swmesagegroupcombo'
				}, {
					allowBlank: false,
					anchor: '95%',
					fieldLabel: lang['kategoriya_slojnosti'],
					hiddenName: 'MesLevel_id',
					width: 250,
					tabIndex: TABINDEX_MEW + 2,
					xtype: 'swmeslevelcombo'
				}, {
					allowBlank: false,
					anchor: '95%',
					fieldLabel: lang['diagnoz'],
					hiddenName: 'Diag_id',
					width: 250,
					tabIndex: TABINDEX_MEW + 3,
					xtype: 'swdiagcombo'
				}, {
					allowBlank: false,
					anchor: '95%',
					hiddenName: 'OmsLpuUnitType_id',
					width: 250,
					tabIndex: TABINDEX_MEW + 4,
					xtype: 'swomslpuunittypecombo'
				}, {
					xtype: 'numberfield',
					allowNegative: false,
					allowDecimals: false,
					anchor: '95%',
					maxLength: 3,
					autoCreate: {tag: "input", type: "text", size: "3", maxLength: "3", autocomplete: "off"},
					width: 250,
					fieldLabel: lang['normativnyiy_srok_lecheniya'],
					tabIndex: TABINDEX_MEW + 5,
					name: 'Mes_KoikoDni'
				}, {
					allowBlank: false,
					fieldLabel : lang['data_nachala_deystviya'] + getMESAlias(),
					xtype: 'swdatefield',
					format: 'd.m.Y',
					plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
					tabIndex: TABINDEX_MEW + 6,
					name: 'Mes_begDT'
				}, {
					fieldLabel : lang['data_okonchaniya_deystviya'] + getMESAlias(),
					xtype: 'swdatefield',
					format: 'd.m.Y',
					plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
					tabIndex: TABINDEX_MEW + 7,
					name: 'Mes_endDT'
				}],
				layout: 'form',
				title: lang['osnovnyie_parametryi']
			}), 
			new Ext.Panel({
				height: 250,
				labelWidth: 130,
				autoScroll: true,
				bodyStyle: 'padding-top: 0.2em;',
				border: true,
				frame: true,
				items: [{
					border: false,
					layout: 'column',					
					items:[{
						border: false,
						columnWidth: .85,
						layout: 'form',
						items: [{
							anchor: '99%',
							fieldLabel : lang['klinicheskiy_diagnoz'],
							name: 'Mes_DiagClinical',
							xtype: 'textarea',
							autoCreate: {tag: "textarea", autocomplete: "off"},
							tabIndex: TABINDEX_MEW + 8
						}]
					}, {
						border: false,
						columnWidth: .15,
						layout: 'form',
						items: [{
							handler: function() {
								var form = Ext.getCmp('MesEditForm');
								var current_window = Ext.getCmp('MesEditWindow');
								current_window.memoBuffer['Mes_DiagClinical'] = form.getForm().findField('Mes_DiagClinical').getValue();
								if ( form.getForm().findField('Mes_DiagClinical').getValue() == '' )
									Ext.getCmp('Mes_DiagClinical_Buff_Button').disable();
								else
									Ext.getCmp('Mes_DiagClinical_Buff_Button').enable();
							},
							text: lang['v_bufer'],
							minWidth: 75,
							width: 75,
							xtype: 'button'
						}, {
							handler: function() {
								var form = Ext.getCmp('MesEditForm');
								var current_window = Ext.getCmp('MesEditWindow');
								form.getForm().findField('Mes_DiagClinical').setValue(current_window.memoBuffer['Mes_DiagClinical']);
							},
							id: 'Mes_DiagClinical_Buff_Button',
							text: lang['iz_bufera'],
							minWidth: 70,
							width: 70,
							xtype: 'button'
						}]
					}]
				}, {
					border: false,
					layout: 'column',
					items:[{
						border: false,
						columnWidth: .85,
						layout: 'form',
						items: [{
							anchor: '99%',
							fieldLabel : lang['obyem_diagnostiki'],
							name: 'Mes_DiagVolume',
							xtype: 'textarea',
							tabIndex: TABINDEX_MEW + 9,
							autoCreate: {tag: "textarea", autocomplete: "off"}
						}]
					}, {
						border: false,
						columnWidth: .15,
						layout: 'form',
						items: [{
							handler: function() {
								var form = Ext.getCmp('MesEditForm');
								var current_window = Ext.getCmp('MesEditWindow');
								current_window.memoBuffer['Mes_DiagVolume'] = form.getForm().findField('Mes_DiagVolume').getValue();
								if ( form.getForm().findField('Mes_DiagVolume').getValue() == '' )
									Ext.getCmp('Mes_DiagVolume_Buff_Button').disable();
								else
									Ext.getCmp('Mes_DiagVolume_Buff_Button').enable();
							},
							text: lang['v_bufer'],
							minWidth: 75,
							width: 75,
							xtype: 'button'
						}, {
							handler: function() {
								var form = Ext.getCmp('MesEditForm');
								var current_window = Ext.getCmp('MesEditWindow');
								form.getForm().findField('Mes_DiagVolume').setValue(current_window.memoBuffer['Mes_DiagVolume']);
							},
							id: 'Mes_DiagVolume_Buff_Button',
							text: lang['iz_bufera'],
							minWidth: 75,
							width: 75,
							xtype: 'button'
						}]
					}]
				}, {
					border: false,
					layout: 'column',
					items:[{
						border: false,
						columnWidth: .85,
						layout: 'form',
						items: [{
							anchor: '99%',
							fieldLabel : lang['konsultatsii'],
							name: 'Mes_Consulting',
							xtype: 'textarea',
							tabIndex: TABINDEX_MEW + 10,
							autoCreate: {tag: "textarea", autocomplete: "off"}
						}]
					}, {
						border: false,
						columnWidth: .15,
						layout: 'form',
						items: [{
							handler: function() {
								var form = Ext.getCmp('MesEditForm');
								var current_window = Ext.getCmp('MesEditWindow');
								current_window.memoBuffer['Mes_Consulting'] = form.getForm().findField('Mes_Consulting').getValue();
								if ( form.getForm().findField('Mes_Consulting').getValue() == '' )
									Ext.getCmp('Mes_Consulting_Buff_Button').disable();
								else
									Ext.getCmp('Mes_Consulting_Buff_Button').enable();
							},
							text: lang['v_bufer'],
							minWidth: 75,
							width: 75,
							xtype: 'button'
						}, {
							handler: function() {
								var form = Ext.getCmp('MesEditForm');
								var current_window = Ext.getCmp('MesEditWindow');
								form.getForm().findField('Mes_Consulting').setValue(current_window.memoBuffer['Mes_Consulting']);
							},
							id: 'Mes_Consulting_Buff_Button',
							text: lang['iz_bufera'],
							minWidth: 75,
							width: 75,
							xtype: 'button'
						}]
					}]
				}, {
					border: false,
					layout: 'column',
					items:[{
						border: false,
						columnWidth: .85,
						layout: 'form',
						items: [{
							anchor: '99%',
							fieldLabel : lang['obyem_lecheniya'],
							name: 'Mes_CureVolume',
							tabIndex: TABINDEX_MEW + 11,
							xtype: 'textarea',
							autoCreate: {tag: "textarea", autocomplete: "off"}
						}]
					}, {
						border: false,
						columnWidth: .15,
						layout: 'form',
						items: [{
							handler: function() {
								var form = Ext.getCmp('MesEditForm');
								var current_window = Ext.getCmp('MesEditWindow');
								current_window.memoBuffer['Mes_CureVolume'] = form.getForm().findField('Mes_CureVolume').getValue();
								if ( form.getForm().findField('Mes_CureVolume').getValue() == '' )
									Ext.getCmp('Mes_CureVolume_Buff_Button').disable();
								else
									Ext.getCmp('Mes_CureVolume_Buff_Button').enable();
							},
							text: lang['v_bufer'],
							minWidth: 75,
							width: 75,
							xtype: 'button'
						}, {
							handler: function() {
								var form = Ext.getCmp('MesEditForm');
								var current_window = Ext.getCmp('MesEditWindow');
								form.getForm().findField('Mes_CureVolume').setValue(current_window.memoBuffer['Mes_CureVolume']);
							},
							id: 'Mes_CureVolume_Buff_Button',
							text: lang['iz_bufera'],
							minWidth: 75,
							width: 75,
							xtype: 'button'
						}]
					}]
				}, {
					border: false,
					layout: 'column',
					items:[{
						border: false,
						columnWidth: .85,
						layout: 'form',
						items: [{
							anchor: '99%',
							fieldLabel : lang['kriterii_kachestva'],
							name: 'Mes_QualityMeasure',
							xtype: 'textarea',
							tabIndex: TABINDEX_MEW + 11,
							autoCreate: {tag: "textarea", autocomplete: "off"}
						}]
					}, {
						border: false,
						columnWidth: .15,
						layout: 'form',
						items: [{
							handler: function() {
								var form = Ext.getCmp('MesEditForm');
								var current_window = Ext.getCmp('MesEditWindow');
								current_window.memoBuffer['Mes_QualityMeasure'] = form.getForm().findField('Mes_QualityMeasure').getValue();
								if ( form.getForm().findField('Mes_QualityMeasure').getValue() == '' )
									Ext.getCmp('Mes_QualityMeasure_Buff_Button').disable();
								else
									Ext.getCmp('Mes_QualityMeasure_Buff_Button').enable();
							},
							text: lang['v_bufer'],
							minWidth: 75,
							width: 75,
							xtype: 'button'
						}, {
							handler: function() {
								var form = Ext.getCmp('MesEditForm');
								var current_window = Ext.getCmp('MesEditWindow');
								form.getForm().findField('Mes_QualityMeasure').setValue(current_window.memoBuffer['Mes_QualityMeasure']);
							},
							id: 'Mes_QualityMeasure_Buff_Button',
							text: lang['iz_bufera'],
							minWidth: 75,
							width: 75,
							xtype: 'button'
						}]
					}]
				}, {
					border: false,
					layout: 'column',
					items:[{
						border: false,
						columnWidth: .85,
						layout: 'form',
						items: [{
							anchor: '99%',
							fieldLabel : lang['ishod_zabolevaniya'],
							name: 'Mes_ResultClass',
							xtype: 'textarea',
							tabIndex: TABINDEX_MEW + 12,
							autoCreate: {tag: "textarea", autocomplete: "off"}
						}]
					}, {
						border: false,
						columnWidth: .15,
						layout: 'form',
						items: [{
							handler: function() {
								var form = Ext.getCmp('MesEditForm');
								var current_window = Ext.getCmp('MesEditWindow');
								current_window.memoBuffer['Mes_ResultClass'] = form.getForm().findField('Mes_ResultClass').getValue();
								if ( form.getForm().findField('Mes_ResultClass').getValue() == '' )
									Ext.getCmp('Mes_ResultClass_Buff_Button').disable();
								else
									Ext.getCmp('Mes_ResultClass_Buff_Button').enable();
							},
							text: lang['v_bufer'],
							minWidth: 75,
							width: 75,
							xtype: 'button'
						}, {
							handler: function() {
								var form = Ext.getCmp('MesEditForm');
								var current_window = Ext.getCmp('MesEditWindow');
								form.getForm().findField('Mes_ResultClass').setValue(current_window.memoBuffer['Mes_ResultClass']);
							},
							id: 'Mes_ResultClass_Buff_Button',
							text: lang['iz_bufera'],
							minWidth: 75,
							width: 75,
							xtype: 'button'
						}]
					}]
				}, {
					border: false,
					layout: 'column',
					items:[{
						border: false,
						columnWidth: .85,
						layout: 'form',
						items: [{
							anchor: '99%',
							fieldLabel : lang['risk_oslojneniy'],
							name: 'Mes_ComplRisk',
							xtype: 'textarea',
							tabIndex: TABINDEX_MEW + 13,
							autoCreate: {tag: "textarea", autocomplete: "off"}
						}]
					}, {
						border: false,
						columnWidth: .15,
						layout: 'form',
						items: [{
							handler: function() {
								var form = Ext.getCmp('MesEditForm');
								var current_window = Ext.getCmp('MesEditWindow');
								current_window.memoBuffer['Mes_ComplRisk'] = form.getForm().findField('Mes_ComplRisk').getValue();
								if ( form.getForm().findField('Mes_ComplRisk').getValue() == '' )
									Ext.getCmp('Mes_ComplRisk_Buff_Button').disable();
								else
									Ext.getCmp('Mes_ComplRisk_Buff_Button').enable();
							},
							text: lang['v_bufer'],
							minWidth: 75,
							width: 75,
							xtype: 'button'
						}, {
							handler: function() {
								var form = Ext.getCmp('MesEditForm');
								var current_window = Ext.getCmp('MesEditWindow');
								form.getForm().findField('Mes_ComplRisk').setValue(current_window.memoBuffer['Mes_ComplRisk']);
							},
							id: 'Mes_ComplRisk_Buff_Button',
							text: lang['iz_bufera'],
							minWidth: 75,
							width: 75,
							xtype: 'button'
						}]
					}]
				}],
				layout: 'form',
				title: lang['dopolnitelnyie_parametryi'],
				buttons: 
				[{
					handler: function() 
					{
						var form = Ext.getCmp('MesEditForm');
						var current_window = Ext.getCmp('MesEditWindow');
						current_window.memoBuffer = {
							'Mes_DiagClinical': form.getForm().findField('Mes_DiagClinical').getValue(),
							'Mes_DiagVolume': form.getForm().findField('Mes_DiagVolume').getValue(),
							'Mes_Consulting': form.getForm().findField('Mes_Consulting').getValue(),
							'Mes_CureVolume': form.getForm().findField('Mes_CureVolume').getValue(),
							'Mes_QualityMeasure': form.getForm().findField('Mes_QualityMeasure').getValue(),
							'Mes_ResultClass': form.getForm().findField('Mes_ResultClass').getValue(),
							'Mes_ComplRisk': form.getForm().findField('Mes_ComplRisk').getValue()
						}
						
						current_window.redisableBuffButtons();
					},
					text: lang['v_bufer']
				},
				{
					handler: function() 
					{
						var form = Ext.getCmp('MesEditForm');
						var current_window = Ext.getCmp('MesEditWindow');
						if ( current_window.memoBuffer['Mes_DiagClinical'] != '' )
							form.getForm().findField('Mes_DiagClinical').setValue(current_window.memoBuffer['Mes_DiagClinical']);
						if ( current_window.memoBuffer['Mes_DiagVolume'] != '' )
							form.getForm().findField('Mes_DiagVolume').setValue(current_window.memoBuffer['Mes_DiagVolume']);
						if ( current_window.memoBuffer['Mes_Consulting'] != '' )
							form.getForm().findField('Mes_Consulting').setValue(current_window.memoBuffer['Mes_Consulting']);
						if ( current_window.memoBuffer['Mes_CureVolume'] != '' )
							form.getForm().findField('Mes_CureVolume').setValue(current_window.memoBuffer['Mes_CureVolume']);
						if ( current_window.memoBuffer['Mes_QualityMeasure'] != '' )
							form.getForm().findField('Mes_QualityMeasure').setValue(current_window.memoBuffer['Mes_QualityMeasure']);
						if ( current_window.memoBuffer['Mes_ResultClass'] != '' )
							form.getForm().findField('Mes_ResultClass').setValue(current_window.memoBuffer['Mes_ResultClass']);
						if ( current_window.memoBuffer['Mes_ComplRisk'] != '' )
							form.getForm().findField('Mes_ComplRisk').setValue(current_window.memoBuffer['Mes_ComplRisk']);
					},
					text: lang['iz_bufera']
				}]
			})],
			keys: 
			[{
				alt: true,
				fn: function(inp, e) 
				{
					switch (e.getKey()) 
					{
						case Ext.EventObject.C:
							if (this.action != 'view') 
							{
								this.doSave(false);
							}
							break;
						case Ext.EventObject.J:
							this.hide();
							break;
					}
				},
				key: [ Ext.EventObject.C, Ext.EventObject.J ],
				scope: this,
				stopEvent: true
			}],
			reader: new Ext.data.JsonReader(
			{
				success: function() 
				{ 
					//
				}
			}, 
			[
				{ name: 'Mes_id' },
				{ name: 'Mes_Code' },
				{ name: 'MesProf_id' },
				{ name: 'MesAgeGroup_id' },
				{ name: 'MesLevel_id' },
				{ name: 'Diag_id' },
				{ name: 'OmsLpuUnitType_id' },
				{ name: 'Mes_KoikoDni' },
				{ name: 'Mes_begDT' },
				{ name: 'Mes_endDT' },
				{ name: 'Mes_DiagClinical' },
				{ name: 'Mes_DiagVolume' },
				{ name: 'Mes_Consulting' },
				{ name: 'Mes_CureVolume' },
				{ name: 'Mes_QualityMeasure' },
				{ name: 'Mes_ResultClass' },
				{ name: 'Mes_ComplRisk' }
			]),
			url: '/?c=Mes&m=saveMes'
		});
		Ext.apply(this, 
		{
			buttons: 
			[{
				handler: function() 
				{
					this.ownerCt.doSave();
				},
				iconCls: 'save16',
				tabIndex: TABINDEX_MEW + 14,
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
				tabIndex: TABINDEX_MEW + 15,
				text: BTN_FRMCANCEL
			}],
			items: [this.mesEditForm]
		});
		sw.Promed.swMesEditWindow.superclass.initComponent.apply(this, arguments);
		this.memoBuffer = {
			'Mes_DiagClinical': '',
			'Mes_DiagVolume': '',
			'Mes_Consulting': '',
			'Mes_CureVolume': '',
			'Mes_QualityMeasure': '',
			'Mes_ResultClass': '',
			'Mes_ComplRisk': ''
		};
	}
	});