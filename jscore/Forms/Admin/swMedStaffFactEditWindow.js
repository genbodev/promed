/**
* swMedStaffFactEditWindow - окно просмотра и редактирования мед. персонала.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Pshenicyn Ivan aka IVP (ipshon@rambler.ru)
* @version      06.03.2009
*/
/*NO PARSE JSON*/
sw.Promed.swMedStaffFactEditWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swMedStaffFactEditWindow',
	objectSrc: '/jscore/Forms/Admin/swMedStaffFactEditWindow.js',

	layout      : 'fit',
	width       : 500,
	modal: true,
	resizable: false,
	draggable: false,
	autoHeight: true,
	closeAction : 'hide',
	plain       : true,
	id: 'med_staff_fact_edit_window',
	listeners: {
		'hide': function() {
			this.onWinClose();
		}
	},
	onWinClose: function() {},
	returnFunc: function(owner) {},
	enableEdit: function( enable ) {
		var form = this.findById('med_staff_fact_edit_form').getForm();
		if ( !enable )
		{
			form.findField('Lpu_idEdit').disable();
			form.findField('LpuUnit_idEdit').disable();
			form.findField('LpuSection_idEdit').disable();
			form.findField('PostMed_idEdit').disable();
			form.findField('MedStaffFact_StavkaEdit').disable();
			form.findField('MedSpec_idEdit').disable();
			//form.findField('MedStaffFact_setDateEdit').disable();
			//form.findField('MedStaffFact_disDateEdit').disable();
			form.findField('PostMedType_idEdit').disable();
			form.findField('PostMedClass_idEdit').disable();
			form.findField('PostMedCat_idEdit').disable();
			form.findField('MedPersonal_id').disable();
			form.findField('MedStaffFact_IsOMSEdit').disable();
			form.findField('MedStaffFact_IsSpecialistEdit').disable();
			this.buttons[0].disable();
		}
		else
		{
			form.findField('Lpu_idEdit').enable();
			form.findField('LpuUnit_idEdit').enable();
			form.findField('LpuSection_idEdit').enable();
			form.findField('PostMed_idEdit').enable();
			form.findField('MedStaffFact_StavkaEdit').enable();
			form.findField('MedSpec_idEdit').enable();
			//form.findField('MedStaffFact_setDateEdit').enable();
			//form.findField('MedStaffFact_disDateEdit').enable();
//			form.findField('MedPersonal_id').enable();
			form.findField('PostMedType_idEdit').enable();
			form.findField('PostMedClass_idEdit').enable();
			form.findField('PostMedCat_idEdit').enable();
			form.findField('MedStaffFact_IsOMSEdit').enable();
			form.findField('MedStaffFact_IsSpecialistEdit').enable();
			this.buttons[0].enable();
		}
	},
	submit: function() {
		var form = this.findById('med_staff_fact_edit_form').getForm();
		var window = this;
		var MedPersonal_id = this.fields.MedPersonal_id;
		var action = this.fields.action;
		var Lpu_id = form.findField('Lpu_idEdit').getValue();
		var post = {action: action, Lpu_idEdit: Lpu_id};
		if ( form.findField('LpuUnit_idEdit').disabled )
		{
			post.LpuUnit_idEdit = form.findField('LpuUnit_idEdit').getValue();
		}

		if ( form.findField('LpuSection_idEdit').disabled )
		{
			post.LpuSection_idEdit = form.findField('LpuSection_idEdit').getValue();
		}
/*		if ( form.findField('PostMed_idEdit').getValue() == null )
		{
			post.PostNew = form.findField('Post_idEdit').getRawValue();
		}
		else
		{
			if (form.findField('Post_idEdit').getStore().findBy(function(rec) { return rec.get('Post_Name') == form.findField('Post_idEdit').getRawValue(); }) >= 0 )
			{
				post.PostNew = '';
			}
			else
			{
				post.PostNew = form.findField('Post_idEdit').getRawValue();
				form.findField('Post_idEdit').setValue('');
			}
		}
*/
		if ( !form.isValid() )
		{
			Ext.Msg.alert(lang['oshibka_zapolneniya_formyi'], lang['proverte_pravilnost_zapolneniya_poley_formyi']);
			return;
		}
		var window = this;
		var MedPersonal_id = this.fields.MedPersonal_id;
		var action = this.fields.action;
		var Lpu_id = form.findField('Lpu_idEdit').getValue();
		var LpuUnit_id = form.findField('LpuUnit_idEdit').getValue();
		var LpuSection_id = form.findField('LpuSection_idEdit').getValue();
/*		if ( !form.findField('PostMed_idEdit').isValid() && post.PostNew == '' )
		{
			Ext.Msg.alert(lang['oshibka_zapolneniya_formyi'], lang['proverte_pravilnost_zapolneniya_poley_formyi']);
			return;
		}
*/
		if ( action == 'addinstructure' )
		{
			form.findField('MedPersonal_idEdit').setValue(form.findField('MedPersonal_id').getValue());
			MedPersonal_id = form.findField('MedPersonal_id').getValue();
		}
		var start_date = form.findField('MedStaffFact_setDateEdit').getValue();
		var end_date = form.findField('MedStaffFact_disDateEdit').getValue();
		if ( start_date != '' && end_date != '' )
		{			if ( start_date > end_date )
			{
				Ext.Msg.alert(lang['oshibka_zapolneniya_formyi'], lang['data_nachala_pozje_datyi_okonchaniya'], function() {					form.findField('MedStaffFact_setDateEdit').focus(true, 100);				});
				return false;
			}		}

		form.submit(
		{
			params: post,
			success: function(form, action) {
				window.hide();
				window.returnFunc(window.owner, MedPersonal_id);
			},
			failure: function (form, action)
			{
				Ext.Msg.alert("Ошибка", action.result.Error_Msg, function() {form.findField('LpuUnit_idEdit').focus(true, 100);});
			}
		});
	},
	title: lang['redaktirovanie_mesto_rabotyi_sotrudnika'],
	show: function() {
		sw.Promed.swMedStaffFactEditWindow.superclass.show.apply(this, arguments);

		// насильный вывод полей на табах
		this.findById('med_staff_fact_tab_panel').purgeListeners();
		this.findById('med_staff_fact_tab_panel').setActiveTab('third_tab');
		this.findById('med_staff_fact_tab_panel').setActiveTab('second_tab');
		this.findById('med_staff_fact_tab_panel').setActiveTab('first_tab');

		if ( arguments[0] )
		{
			if ( arguments[0].callback )
				this.returnFunc = arguments[0].callback;
			if ( arguments[0].owner )
				this.owner = arguments[0].owner;
			if ( arguments[0].fields )
				this.fields = arguments[0].fields;
			if ( arguments[0].onClose )
				this.onWinClose = arguments[0].onClose;
		}
		var form = this.findById('med_staff_fact_edit_form').getForm();
		form.reset();
		var wnd = this;
//		if ( form.findField('Lpu_idEdit').getStore().getCount() == 0 )
			form.findField('Lpu_idEdit').getStore().load();
//		if ( form.findField('Post_idEdit').getStore().getCount() == 0 )

			/*form.findField('PostMed_idEdit').getStore().removeAll();
			form.findField('PostMed_idEdit').getStore().load(
			{
				callback: function()
				{

				Ext.getCmp('med_staff_fact_tab_panel').addListener('tabchange', function(tab, panel)
				{
					var els=panel.findByType('textfield', false);
					if (els=='undefined')
						els=panel.findByType('combo', false);
					var el=els[0];
					if (el!='undefined' && el.focus)
					el.focus(true, 10);
				},this);
				}
			}
			);*/

		form.findField('MedPersonal_id').getStore().removeAll();

		form.findField('MedPersonal_id').disable();
		if (this.fields && this.fields.action)
			switch (this.fields.action)
			{
				case 'add':
					if ( wnd.fields.Lpu_id )
						form.findField('MedPersonal_id').getStore().load({params: {Lpu_id: wnd.fields.Lpu_id}});
					else
						form.findField('MedPersonal_id').getStore().load();

					this.setTitle(lang['dobavlenie_mesto_rabotyi_sotrudnika']);
					this.enableEdit(true);
					var window = this;
					var Mask = new Ext.LoadMask(Ext.get('med_staff_fact_edit_window'), {msg:"Пожалуйста, подождите, идет загрузка данных формы..."});
					Mask.show();
					form.findField('MedPersonal_idEdit').setValue(this.fields.MedPersonal_id);
					form.load({
						params:{
							action: 'getDataForAdd'
						},
						success: function(form) {
							var lpuId = form.findField('Lpu_idEdit').getValue();
//							form.findField('Lpu_idCombo').getStore().load({
//								callback: function() {
									Mask.hide();
									form.findField('LpuUnit_idEdit').focus(true, 100);
									if ( form.findField('MedPersonal_id').getStore().getCount() == 0 )
									{
									    if ( wnd.fields.Lpu_id )
											form.findField('MedPersonal_id').getStore().load({
												params: {
													Lpu_id: wnd.fields.Lpu_id
												},
												callback: function() {
													form.findField('MedPersonal_id').setValue(window.fields.MedPersonal_id);
												}
											});
										else
											form.findField('MedPersonal_id').getStore().load({
												callback: function() {
													form.findField('MedPersonal_id').setValue(window.fields.MedPersonal_id);
												}
											});
									}
									else
										form.findField('MedPersonal_id').setValue(window.fields.MedPersonal_id);
									form.findField('Lpu_idEdit').setValue(lpuId);
									form.findField('Lpu_idEdit').disable();
									form.findField('Lpu_idEdit').fireEvent('change', this, lpuId);
									form.clearInvalid();
//								}
//							});
						},
						failure: function() {
							Mask.hide();
							Ext.Msg.alert(lang['oshibka'], lang['ne_udalos_zagruzit_dannyie_s_servera'], function() {window.hide()});
						}
					});
				break;
				case 'addinstructure':

				if ( wnd.fields.Lpu_id )
						form.findField('MedPersonal_id').getStore().load({params: {Lpu_id: wnd.fields.Lpu_id}});
					else
						form.findField('MedPersonal_id').getStore().load();

					this.setTitle(lang['dobavlenie_mesto_rabotyi_sotrudnika']);
					this.enableEdit(true);
					var window = this;
					var Mask = new Ext.LoadMask(Ext.get('med_staff_fact_edit_window'), {msg:"Пожалуйста, подождите, идет загрузка данных формы..."});
					Mask.show();
					form.findField('MedPersonal_idEdit').setValue(this.fields.MedPersonal_id);
					form.findField('MedPersonal_id').enable();
					form.findField('MedPersonal_id').setValue(this.fields.MedPersonal_id);
					form.load({
						params:{
							action: 'getDataForAdd'
						},
						success: function(form)
						{
							var lpuId = window.fields.Lpu_id;
							var lpuunitId = window.fields.LpuUnit_id;
							var lpusectionId = window.fields.LpuSection_id;
							Mask.hide();
							form.findField('Lpu_idEdit').setValue(lpuId);
							form.findField('Lpu_idEdit').disable();
							form.findField('Lpu_idEdit').fireEvent('change', this, lpuId);
							form.findField('LpuUnit_idCombo').getStore().load(
							{
								params:
								{
									Lpu_id: lpuId,
									Object: 'LpuUnit',
									LpuUnit_id: '',
									LpuUnit_Name: ''
						},
								callback: function()
								{
									if (lpuunitId>0)
									{
										form.findField('LpuUnit_idEdit').setValue(lpuunitId);
										form.findField('LpuUnit_idEdit').disable();
										form.findField('LpuUnit_idEdit').fireEvent('change', this, lpuunitId);

										form.findField('LpuSection_idCombo').getStore().load(
										{
											params:
											{
												LpuUnit_id: lpuunitId,
												Object: 'LpuSection',
												LpuSection_id: '',
												LpuSection_Name: ''
									},
											callback: function()
											{
												if (lpusectionId>0)
												{
													form.findField('LpuSection_idEdit').setValue(lpusectionId);
													form.findField('LpuSection_idEdit').disable();
													form.findField('LpuSection_idEdit').fireEvent('change', this, lpusectionId);
													form.findField('MedPersonal_idEdit').focus(true, 100);
												}
												else
												{
													form.findField('LpuSection_idEdit').focus(true, 100);
												}
											}
										});
									}
									else
									{
										form.findField('LpuUnit_idEdit').focus(true, 100);
									}
								}
							});
							form.clearInvalid();

						},
						failure: function() {
							Mask.hide();
							Ext.Msg.alert(lang['oshibka'], lang['ne_udalos_zagruzit_dannyie_s_servera'], function() {window.hide()});
						}
					});
				break;
				case 'edit':
					if ( this.fields.readOnly )
					{
						this.setTitle(lang['prosmotr_mesto_rabotyi_sotrudnika']);
						this.enableEdit(false);
					}
					else
					{
						this.setTitle(lang['redaktirovanie_mesto_rabotyi_sotrudnika']);
						this.enableEdit(true);
					}
					var window = this;
					var Mask = new Ext.LoadMask(Ext.get('med_staff_fact_edit_window'), {msg:"Пожалуйста, подождите, идет загрузка данных формы..."});
					Mask.show();
					form.findField('MedPersonal_idEdit').setValue(this.fields.MedPersonal_id);
					form.findField('MedPersonal_id').setValue(this.fields.MedPersonal_id);
					form.findField('MedStaffFact_idEdit').setValue(this.fields.MedStaffFact_id);
					form.load({
						params:{
							action: 'getDataForEdit',
							MedStaffFact_id: this.fields.MedStaffFact_id
						},
						success: function(form) {
							form.clearInvalid();
							var lpuId = form.findField('Lpu_idEdit').getValue();
							var lpuUnitId = form.findField('LpuUnit_idEdit').getValue();
							var lpuSectionId = form.findField('LpuSection_idEdit').getValue();
							form.findField('Lpu_idEdit').getStore().load({
								callback: function() {
									if ( form.findField('MedPersonal_id').getStore().getCount() == 0 ) {
										if ( window.fields.Lpu_id )
											Lpu_id = window.fields.Lpu_id;
										else
											Lpu_id = 0;
										form.findField('MedPersonal_id').getStore().load({
											params: {
												Lpu_id: Lpu_id,
												MedPersonal_id: form.findField('MedPersonal_id').getValue()
											},
											callback: function() {
												form.findField('MedPersonal_id').setValue(window.fields.MedPersonal_id);
											}
										});
										/*
										else
											form.findField('MedPersonal_id').getStore().load({
												callback: function() {
													form.findField('MedPersonal_id').setValue(window.fields.MedPersonal_id);
												}
											});
										*/
									}
									else
										form.findField('MedPersonal_id').setValue(window.fields.MedPersonal_id);
									form.findField('Lpu_idEdit').setValue(lpuId);
									form.findField('Lpu_idEdit').disable();
									form.findField('LpuUnit_idCombo').getStore().load({
										params: {
											Lpu_id: lpuId,
											Object: 'LpuUnit',
											LpuUnit_id: '',
											LpuUnit_Name: ''
										},
										callback: function() {
											form.findField('LpuUnit_idEdit').setValue(lpuUnitId);
											form.findField('LpuSection_idCombo').getStore().load({
											params: {
													LpuUnit_id: lpuUnitId,
													Object: 'LpuSection',
													LpuSection_id: '',
													LpuSection_Name: ''
												},
												callback: function() {
													form.findField('LpuSection_idEdit').setValue(lpuSectionId);
													form.findField('LpuUnit_idEdit').focus(true, 500);
													Mask.hide();
												}
											});
										}
									});
								}
							});
						},
						failure: function() {
							Mask.hide();
							Ext.Msg.alert(lang['oshibka'], lang['ne_udalos_zagruzit_dannyie_s_servera'], function() {window.hide()});
						}
					});
				break;
			}
		//form.findField('LpuUnit_idEdit').focus(true, 100);
	},
	initComponent: function() {
		Ext.apply(this, {
			buttons: [{
				text: BTN_FRMSAVE,
				tabIndex:22,
				iconCls: 'save16',
				handler: function() {
					this.submit();
				}.createDelegate(this)
			}, {
				text: '-'
			},
			HelpButton(this),
			{
				text: BTN_FRMCANCEL,
				tabIndex: 23,
				iconCls: 'cancel16',
				handler: function() {
					this.hide();
				}.createDelegate(this)
			}],
			items: [
				new Ext.form.FormPanel({
					frame: true,
					autoHeight: true,
					labelAlign: 'right',
					id: 'med_staff_fact_edit_form',
					labelWidth: 125,
					buttonAlign: 'left',
					bodyStyle:'padding: 5px',
					url: C_MSF_EDIT,
					reader : new Ext.data.JsonReader({
							success: function() {alert('All Right!')}
						}, [
						{name: 'Lpu_idEdit'},
						{name: 'LpuSection_idEdit'},
						{name: 'LpuUnit_idEdit'},
						{name: 'MedSpec_idEdit'},
						{name: 'MedSpecOms_id'},
						{name: 'MedStaffFact_Contacts'},
						{name: 'MedStaffFact_Descr'},
						{name: 'MedStaffFact_disDateEdit'},
						{name: 'MedStaffFact_IsDirRec'},
						{name: 'MedStaffFact_IsOMSEdit'},
						{name: 'MedStaffFact_IsQueueOnFree'},
						{name: 'MedStaffFact_IsSpecialistEdit'},
						{name: 'MedStaffFact_PriemTime'},
						{name: 'MedStaffFact_setDateEdit'},
						{name: 'MedStaffFact_StavkaEdit'},
						{name: 'MedStatus_id'},
						{name: 'PostMed_idEdit'},
						{name: 'PostMedCat_idEdit'},
						{name: 'PostMedClass_idEdit'},
						{name: 'PostMedType_idEdit'},
						{name: 'RecType_id'}
					]),
					items: [{
						xtype: 'fieldset',
						autoHeight: true,
						title: lang['mesto_rabotyi'],
						style: 'padding: 0; margin-bottom: 5px',
						items: [
							{
								xtype: 'swlpulocalcombo',
								tabIndex: 1,
								id: 'Lpu_idCombo',
								hiddenName: 'Lpu_idEdit',
								width: 290,
								listWidth: 500,
								allowBlank: false,
								listeners: {
									'change': function(combo, lpuId) {
										this.ownerCt.ownerCt.getForm().findField('LpuUnit_idCombo').clearValue();
										this.ownerCt.ownerCt.getForm().findField('LpuSection_idCombo').clearValue();
										this.ownerCt.ownerCt.getForm().findField('LpuSection_idCombo').getStore().removeAll();
										this.ownerCt.ownerCt.getForm().findField('LpuUnit_idCombo').getStore().removeAll();
										this.ownerCt.ownerCt.getForm().findField('LpuUnit_idCombo').getStore().load({
											params: {
												Lpu_id: lpuId,
												Object: 'LpuUnit',
												LpuUnit_id: '',
												LpuUnit_Name: ''
											}
										});
									}
								}
							},
							{
								xtype: 'swlpuunitcombo',
								topLevel: true,
								tabIndex:2,
								id: 'LpuUnit_idCombo',
								hiddenName: 'LpuUnit_idEdit',
								width: 290,
								allowBlank: false,
								listWidth: 500,
								listeners: {
									'change': function(combo, lpuUnitId) {
										this.ownerCt.ownerCt.getForm().findField('LpuSection_idCombo').clearValue();
										this.ownerCt.ownerCt.getForm().findField('LpuSection_idCombo').getStore().removeAll();
										this.ownerCt.ownerCt.getForm().findField('LpuSection_idCombo').getStore().load({
											params: {
												LpuUnit_id: lpuUnitId,
												Object: 'LpuSection',
												LpuSection_id: '',
												LpuSection_Name: ''
											}
										});
									}
								}
							},
							{
								xtype: 'swlpusectioncombo',
								tabIndex:3,
								id: 'LpuSection_idCombo',
								allowBlank: false,
								hiddenName: 'LpuSection_idEdit',
								width: 290,
								listWidth: 500
							}
						]},
						{
							xtype: 'fieldset',
							autoHeight: true,
							title: lang['meditsinskiy_personal'],
							style: 'padding: 0; margin-bottom: 5px',
							items: [
								{
									xtype: 'hidden',
									name: 'MedPersonal_idEdit'
								},
								{
									xtype: 'hidden',
									name: 'MedStaffFact_idEdit'
								},
								{
									anchor: false,
									tabIndex:4,
									id: 'MedPersonalCombo',
									allowBlank: false,
									//listWidth: 250,
									width: 290,
									xtype: 'swmedpersonalallcombo',
									editable: true,
									codeField: 'MedPersonal_Code',
									loadingText: lang['idet_poisk'],
									minChars: 0,
//									minLength: 1,
									minLengthText: lang['pole_doljno_byit_zapolneno'],
									listeners:
									{
										select: function(combo,record,index)
										{
											var form = this.findById('med_staff_fact_edit_form').getForm();
											form.findField('MedStaffFact_setDateEdit').setValue(record.data.WorkData_begDate);
											form.findField('MedStaffFact_disDateEdit').setValue(record.data.WorkData_endDate);
										}.createDelegate(this)
									}
									/*
									displayField: 'MedPersonal_FIO',
									fieldLabel: lang['vrach'],
									hiddenName: 'MedPersonal_id',
									valueField: 'MedPersonal_id',
									listeners: {
										'beforeselect': function(combo, record, index) {
											record.data.MedPersonal_FIO = record.data.MedPersonal_Code + ". " + record.json.MedPersonal_FIO;
										}
									},
									mode: 'local',
									resizable: true,
									selectOnFocus: true,
									store: new Ext.data.Store({
										autoLoad: false,
										reader: new Ext.data.JsonReader({
											id: 'MedPersonal_id'
										}, [
											{ name: 'MedPersonal_FIO', mapping: 'MedPersonal_FIO' },
											{ name: 'MedPersonal_id', mapping: 'MedPersonal_id' },
											{ name: 'MedPersonal_Code', mapping: 'MedPersonal_Code' }
										]),
										url: C_MP_GRID
									}),
									tpl: new Ext.XTemplate(
										'<tpl for="."><div class="x-combo-list-item">',
										'<table style="border: 0;"><td style="width: 70px"><font color="red">{MedPersonal_Code}</font></td><td><h3>{MedPersonal_FIO}</h3></td></tr></table>',
										'</div></tpl>'
									),
									triggerAction: 'all',
									xtype: 'combo'

									*/
								}
							]
						},
						new Ext.TabPanel({
							id: 'med_staff_fact_tab_panel',
							activeTab: 0,
							height: 300,
							layoutOnTabChange: true,
							defaults:{bodyStyle:'padding:10px'},
							items: [
							{
								title: lang['1_opisanie'],
								autoHeight: true,
								layout:'form',
								id: 'first_tab',
								labelWidth: 115,
								items: [
									{
										xtype: 'swpostmedlocalcombo',
										tabIndex:5,
										hiddenName: 'PostMed_idEdit',
										allowBlank: true,
										editable: true,
										forceSelection: false,
										fieldLabel: lang['doljnost'],
										width: 290
									},
									{
										xtype: 'numberfield',
										tabIndex:6,
										name: 'MedStaffFact_StavkaEdit',
										maxValue: 3,
										minValue: 0,
										autoCreate: {tag: "input", size:4, maxLength: "4", autocomplete: "off"},
										allowBlank: false,
										fieldLabel: lang['stavka']
										/*,
										listeners:
										{
											change: function(field, newValue, oldValue)
											{
												if (field.getValue() != '')
												{
													if (newValue>3)
													{
														field.setValue(3);
													}
												}
											}
										}
										*/
									},
									{
										xtype: 'swmedspeccombo',
										tabIndex:7,
										hiddenName: 'MedSpec_idEdit',
										width: 290
									},
									{
										xtype: 'fieldset',
										autoHeight: true,
										title: lang['period_rabotyi'],
										style: 'padding: 0; margin-bottom: 5px',
										items: [
											{
												xtype: 'swdatefield',
												plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
												tabIndex:8,
												format: 'd.m.Y',
												fieldLabel: lang['nachalo'],
												allowBlank: false,
												disabled: true,
												name: 'MedStaffFact_setDateEdit'
											},
											{
												xtype: 'swdatefield',
												plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
												tabIndex:9,
												format: 'd.m.Y',
												allowBlank: true,
												disabled: true,
												fieldLabel: lang['okonchanie'],
												name: 'MedStaffFact_disDateEdit'
											}
										]
									}/*,
									{
										cls: 'x-btn-large',
										handler: function() {
											sw.swMsg.alert(lang['soobschenie'], lang['forma_dlya_redaktirovaniya_dopolnitelnyih_svedeniy']);
										},
										iconCls: 'idcard32',
										tooltip: lang['fed_registr_mp'],
										xtype: 'button'
									}*/
								]
							},
							{
								title: lang['2_klassifikatsiya'],
								autoHeight: true,
								layout:'form',
								id: 'second_tab',
								labelWidth: 115,
								items: [
									{
										xtype: 'swpostmedtypecombo',
										listWidth: 400,
										tabIndex:10,
										width: 290,
										hiddenName: 'PostMedType_idEdit'
									},
									{
										xtype: 'swpostmedclasscombo',
										tabIndex:11,
										width: 290,
										hiddenName: 'PostMedClass_idEdit'
									},
									{
										xtype: 'swpostmedcatcombo',
										tabIndex:12,
										width: 290,
										hiddenName: 'PostMedCat_idEdit'
									},
									{
										xtype: 'swyesnocombo',
										tabIndex:13,
										fieldLabel: lang['sertifikat'],
										width: 80,
										hiddenName: 'MedStaffFact_IsSpecialistEdit'
									},
									{
										xtype: 'swyesnocombo',
										tabIndex:14,
										fieldLabel: lang['rabotaet_v_oms'],
										width: 80,
										hiddenName: 'MedStaffFact_IsOMSEdit'
									},
									{
										xtype: 'swmedspecomscombo',
										fieldLabel: (getGlobalOptions().region && getGlobalOptions().region.nick == 'ufa')?lang['kod_pmsp']:lang['spetsialnost_s90'],
										tabIndex:14,
										width: 290,
										hiddenName: 'MedSpecOms_id'
									}
								]
							},
							{
								title: lang['3_atributyi_dlya_er'],
								autoHeight: true,
								layout:'form',
								id: 'third_tab',
								labelWidth: 115,
								items: [{
									xtype: 'swrectypecombo',
									tabIndex: 15,
									width: 290,
									hiddenName: 'RecType_id'
								}, {
									xtype: 'textfield',
									maskRe: /\d/,
									fieldLabel: lang['vremya_priema'],
									minLength: 0,
									autoCreate: {tag: "input", type: "text", size: "3", maxLength: "3", autocomplete: "off"},
									width: 50,
									name: 'MedStaffFact_PriemTime',
									tabIndex: 16
								}, {
									xtype: 'swmedstatuscombo',
									tabIndex: 17,
									width: 290,
									hiddenName: 'MedStatus_id'
								}, {
									xtype: 'checkbox',
									tabIndex: 18,
									labelSeparator: '',
									name: 'MedStaffFact_IsDirRec',
									boxLabel: lang['razreshat_zapis_k_vrachu_cherez_napravleniya']
								}, {
									xtype: 'checkbox',
									tabIndex: 19,
									labelSeparator: '',
									name: 'MedStaffFact_IsQueueOnFree',
									boxLabel: lang['pozvolyat_pomeschenie_v_ochered_pri_nalichii_svobodnyih_birok']
								}, {
									xtype: 'textarea',
									height: 45,
									width: 290,
									tabIndex: 20,
									fieldLabel: lang['primechanie_vracha'],
									name: 'MedStaffFact_Descr'
								}, {
									xtype: 'textarea',
									height: 45,
									width: 290,
									tabIndex: 21,
									fieldLabel: lang['kontaktnaya_informatsiya'],
									name: 'MedStaffFact_Contacts'
								}]
							}/*,
							{
								title: lang['3_uchastki']
							}*/
							]
						})
					]
				})
			],
			keys: [{
				key: "0123456789",
				ctrl: true,
				fn: function(e) {Ext.getCmp("med_staff_fact_tab_panel").setActiveTab(Ext.getCmp("med_staff_fact_tab_panel").items.items[ e - 49 ]);},
				stopEvent: true
			},
			{
		    	alt: true,
		        fn: function(inp, e) {
		        	Ext.getCmp('med_staff_fact_edit_window').buttons[0].handler();
		        },
		        key: [ Ext.EventObject.C ],
		        stopEvent: true
		    },{
		    	alt: true,
		        fn: function(inp, e) {
		        	Ext.getCmp('med_staff_fact_edit_window').buttons[1].handler();
		        },
		        key: [ Ext.EventObject.J ],
		        stopEvent: true
		    }]
		});
		sw.Promed.swMedStaffFactEditWindow.superclass.initComponent.apply(this, arguments);
	}
});
