/**
* swEvnDiagDopDispAndRecomendationEditWindow - окно "Состояние здоровья: Редактирование"
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright © 2009-2013 Swan Ltd.
* @author       
* @version      12.12.2013
* @comment      префикс EDAREW
*/
/*NO PARSE JSON*/

sw.Promed.swEvnDiagDopDispAndRecomendationEditWindow = Ext.extend(sw.Promed.BaseForm, {
	callback: Ext.emptyFn,
	layout: 'form',
	title: lang['sostoyanie_zdorovya_do_provedeniya_dispanserizatsii_profosmotra_redaktirovanie'],
	id: 'EvnDiagDopDispAndRecomendationEditWindow',
	width: 600,
	autoHeight: true,
	modal: true,
	formStatus: 'edit',
	doSave: function()  {
		var win = this;
		if ( win.formStatus == 'save' || win.action == 'view' ) {
			return false;
		}
		win.formStatus = 'save';
		var form = this.FormPanel;
		if (!form.getForm().isValid()) {
			sw.swMsg.show( {
				buttons: Ext.Msg.OK,
				fn: function() {
					win.formStatus = 'edit';
					form.getFirstInvalidEl().focus(false);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
		
		win.getLoadMask("Подождите, идет сохранение...").show();
		form.getForm().submit(
		{
			url: '/?c=EvnDiagDopDisp&m=saveEvnDiagDopDispAndRecomendation',
			failure: function(result_form, action) 
			{
				win.formStatus = 'edit';
				win.getLoadMask().hide();
			},
			success: function(result_form, action) 
			{
				win.formStatus = 'edit';
				win.getLoadMask().hide();
				if (action.result) 
				{
					if (action.result.EvnDiagDopDisp_id) 
					{
						win.hide();
						win.callback(win.owner, action.result.EvnDiagDopDisp_id);
					}
					else
						Ext.Msg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshla_oshibka']);
				}
				else
					Ext.Msg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshla_oshibka']);
			}
		});
	},
	callback: Ext.emptyFn,
	show: function() {
		sw.Promed.swEvnDiagDopDispAndRecomendationEditWindow.superclass.show.apply(this, arguments);
		
		this.formStatus = 'edit';
		var win = this;
		
		if (!arguments[0])
		{
			Ext.Msg.alert(lang['oshibka'], lang['otsutstvuyut_neobhodimyie_parametryi']);
			this.hide();
			return false;
		}
		
		this.EvnDiagDopDisp_id = arguments[0].EvnDiagDopDisp_id || null;
		
		if (arguments[0].callback) {
			this.callback = arguments[0].callback;
		}
		
		if (arguments[0].action) {
			this.action = arguments[0].action;
		}
		
		var base_form = win.FormPanel.getForm();
		base_form.reset();
		base_form.setValues(arguments[0]);

		base_form.findField('PlaceMedCareType1_id').getStore().filterBy(function(rec) {
			return (rec.get('PlaceMedCareType_Code') <= 4);
		});
		
		base_form.findField('PlaceMedCareType1_nid').getStore().filterBy(function(rec) {
			return (rec.get('PlaceMedCareType_Code') <= 4);
		});
		
		base_form.findField('PlaceMedCareType2_id').getStore().filterBy(function(rec) {
			return (rec.get('PlaceMedCareType_Code') <= 4);
		});
		
		base_form.findField('PlaceMedCareType2_nid').getStore().filterBy(function(rec) {
			return (rec.get('PlaceMedCareType_Code') <= 4);
		});
		
		switch (this.action)
		{
			case 'add':
				this.enableEdit(true);
				win.setTitle(lang['sostoyanie_zdorovya_do_provedeniya_dispanserizatsii_profosmotra_dobavlenie']);
			break;
			case 'edit':
			case 'view':
				if (this.action == 'edit') {
					this.enableEdit(true);
					win.setTitle(lang['sostoyanie_zdorovya_do_provedeniya_dispanserizatsii_profosmotra_redaktirovanie']);
				} else {
					this.enableEdit(false);
					win.setTitle(lang['sostoyanie_zdorovya_do_provedeniya_dispanserizatsii_profosmotra_prosmotr']);
				}
				
				win.getLoadMask("Подождите, идет загрузка...").show();
				base_form.load(
				{
					url: '/?c=EvnDiagDopDisp&m=loadEvnDiagDopDispAndRecomendationGrid',
					params: 
					{
						EvnDiagDopDisp_id: win.EvnDiagDopDisp_id,
						archiveRecord: win.archiveRecord
					},
					success: function() 
					{
						win.getLoadMask().hide();

						var diag_combo = base_form.findField('Diag_id');
						var diag_id = diag_combo.getValue();
						if (diag_id != '') {
							diag_combo.getStore().load({
								params: { where: "where Diag_id = " + diag_id },
								callback: function(data) {
									diag_combo.getStore().each(function(record) {
										if ( record.get('Diag_id') == diag_id ) {
											diag_combo.fireEvent('select', diag_combo, record, 0);
										}
									});
								}
							});
						}
						
						base_form.findField('ConditMedCareType1_nid').fireEvent('change', base_form.findField('ConditMedCareType1_nid'), base_form.findField('ConditMedCareType1_nid').getValue());
						base_form.findField('ConditMedCareType2_nid').fireEvent('change', base_form.findField('ConditMedCareType2_nid'), base_form.findField('ConditMedCareType2_nid').getValue());
						base_form.findField('ConditMedCareType3_nid').fireEvent('change', base_form.findField('ConditMedCareType3_nid'), base_form.findField('ConditMedCareType3_nid').getValue());
						base_form.findField('Diag_id').focus(true, 100);
					},
					failure: function() 
					{
						win.getLoadMask().hide();
						sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_zagruzke_dannyih'], function() { win.hide(); } );
					}
				});
			break;
		}
	},
	initComponent: function() 
	{
		this.FormPanel = new sw.Promed.FormPanel(
		{
			autoHeight: true,
			bodyStyle: 'padding:5px;',
			id: 'EvnDiagDopDispAndRecomendationEditFormPanel',
			layout: 'form',
			frame: true,
			autoWidth: false,
			region: 'center',
			labelWidth: 130,
			items:
			[{
				name: 'EvnDiagDopDisp_id',
				xtype: 'hidden'
			}, {
				name: 'EvnDiagDopDisp_pid',
				xtype: 'hidden'
			}, {
				name: 'PersonEvn_id',
				xtype: 'hidden'
			}, {
				name: 'Server_id',
				xtype: 'hidden'
			}, {
				allowBlank: false,
				hiddenName: 'Diag_id',
				width: 350,
				tabIndex: TABINDEX_EDAREW + 1,
				xtype: 'swdiagcombo'
			}, {
				fieldLabel: lang['diagnoz_ustanovlen_vpervyie'],
				allowBlank: false,
				value: 1,
				hiddenName: 'DeseaseDispType_id',
				tabIndex: TABINDEX_EDAREW + 2,
				xtype: 'swyesnocombo'
			}, {
				comboSubject: 'DispSurveilType',
				allowBlank: false,
				fieldLabel: lang['dispansernoe_nablyudenie'],
				hiddenName: 'DispSurveilType_id',
				lastQuery: '',
				width: 350,
				tabIndex: TABINDEX_EDAREW + 3,
				xtype: 'swcommonsprcombo'
			}, {
				autoHeight: true,
				style: 'padding: 0px;',
				title: lang['dopolnitelnyie_konsultatsii_i_issledovaniya'],
				width: 500,
				items: [
					{
						comboSubject: 'ConditMedCareType',
						fieldLabel: lang['naznacheno'],
						value: 1,
						hiddenName: 'ConditMedCareType1_nid',
						lastQuery: '',
						listeners: {
							'change': function(combo, newValue, oldValue) {
								var index = combo.getStore().findBy(function(rec) {
									return (rec.get(combo.valueField) == newValue);
								});

								combo.fireEvent('select', combo, combo.getStore().getAt(index));
							}.createDelegate(this),
							'select': function(combo, record, index) {
								var base_form = this.FormPanel.getForm();

								if ( typeof record == 'object' && record.get('ConditMedCareType_Code').toString().inlist([ '2', '3', '4' ]) ) {
									if ( this.action != 'view' ) {
										base_form.findField('PlaceMedCareType1_nid').setAllowBlank(false);
										base_form.findField('PlaceMedCareType1_nid').enable();
										base_form.findField('ConditMedCareType1_id').enable();
										base_form.findField('PlaceMedCareType1_id').enable();
									}
								}
								else {
									base_form.findField('PlaceMedCareType1_nid').setAllowBlank(true);
									base_form.findField('PlaceMedCareType1_nid').clearValue();
									base_form.findField('PlaceMedCareType1_nid').disable();
									base_form.findField('ConditMedCareType1_id').clearValue();
									base_form.findField('ConditMedCareType1_id').disable();
									base_form.findField('PlaceMedCareType1_id').clearValue();
									base_form.findField('PlaceMedCareType1_id').disable();
								}
							}.createDelegate(this)
						},
						width: 250,
						tabIndex: TABINDEX_EDAREW + 4,
						xtype: 'swcommonsprcombo'
					},
					{
						comboSubject: 'PlaceMedCareType',
						//loadParams: {params: {where: ' where PlaceMedCareType_Code <= 4'}},
						fieldLabel: lang['mesto_naznacheniya'],
						hiddenName: 'PlaceMedCareType1_nid',
						lastQuery: '',
						width: 250,
						tabIndex: TABINDEX_EDAREW + 5,
						xtype: 'swcommonsprcombo'
					},
					{
						comboSubject: 'ConditMedCareType',
						fieldLabel: lang['provedeno'],
						hiddenName: 'ConditMedCareType1_id',
						lastQuery: '',
						width: 250,
						tabIndex: TABINDEX_EDAREW + 6,
						xtype: 'swcommonsprcombo'
					},
					{
						comboSubject: 'PlaceMedCareType',
						//loadParams: {params: {where: ' where PlaceMedCareType_Code <= 4'}},
						fieldLabel: lang['mesto_provedeniya'],
						hiddenName: 'PlaceMedCareType1_id',
						lastQuery: '',
						width: 250,
						tabIndex: TABINDEX_EDAREW + 7,
						xtype: 'swcommonsprcombo'
					}
				],
				xtype: 'fieldset'
			}, {
				autoHeight: true,
				style: 'padding: 0px;',
				title: lang['lechenie'],
				width: 500,
				items: [
					{
						comboSubject: 'ConditMedCareType',
						fieldLabel: lang['naznacheno'],
						value: 1,
						hiddenName: 'ConditMedCareType2_nid',
						lastQuery: '',
						listeners: {
							'change': function(combo, newValue, oldValue) {
								var index = combo.getStore().findBy(function(rec) {
									return (rec.get(combo.valueField) == newValue);
								});

								combo.fireEvent('select', combo, combo.getStore().getAt(index));
							}.createDelegate(this),
							'select': function(combo, record, index) {
								var base_form = this.FormPanel.getForm();

								if ( typeof record == 'object' && record.get('ConditMedCareType_Code').toString().inlist([ '2', '3', '4' ]) ) {
									if ( this.action != 'view' ) {
										base_form.findField('PlaceMedCareType2_nid').setAllowBlank(false);
										base_form.findField('PlaceMedCareType2_nid').enable();
										base_form.findField('ConditMedCareType2_id').enable();
										if (Ext.isEmpty(base_form.findField('ConditMedCareType2_id').getValue())) {
											base_form.findField('ConditMedCareType2_id').setValue(1);
										}
										base_form.findField('PlaceMedCareType2_id').enable();
										if (Ext.isEmpty(base_form.findField('PlaceMedCareType2_id').getValue())) {
											base_form.findField('LackMedCareType2_id').enable();
										}
									}
								}
								else {
									base_form.findField('PlaceMedCareType2_nid').setAllowBlank(true);
									base_form.findField('PlaceMedCareType2_nid').clearValue();
									base_form.findField('PlaceMedCareType2_nid').disable();
									base_form.findField('ConditMedCareType2_id').clearValue();
									base_form.findField('ConditMedCareType2_id').disable();
									base_form.findField('PlaceMedCareType2_id').clearValue();
									base_form.findField('PlaceMedCareType2_id').disable();
									base_form.findField('LackMedCareType2_id').clearValue();
									base_form.findField('LackMedCareType2_id').disable();
								}
							}.createDelegate(this)
						},
						width: 250,
						tabIndex: TABINDEX_EDAREW + 8,
						xtype: 'swcommonsprcombo'
					},
					{
						comboSubject: 'PlaceMedCareType',
						//loadParams: {params: {where: ' where PlaceMedCareType_Code <= 4'}},
						fieldLabel: lang['mesto_naznacheniya'],
						hiddenName: 'PlaceMedCareType2_nid',
						lastQuery: '',
						width: 250,
						tabIndex: TABINDEX_EDAREW + 9,
						xtype: 'swcommonsprcombo'
					},
					{
						comboSubject: 'ConditMedCareType',
						fieldLabel: lang['provedeno'],
						hiddenName: 'ConditMedCareType2_id',
						lastQuery: '',
						width: 250,
						tabIndex: TABINDEX_EDAREW + 10,
						xtype: 'swcommonsprcombo'
					},
					{
						comboSubject: 'PlaceMedCareType',
						//loadParams: {params: {where: ' where PlaceMedCareType_Code <= 4'}},
						fieldLabel: lang['mesto_provedeniya'],
						hiddenName: 'PlaceMedCareType2_id',
						lastQuery: '',
						width: 250,
						tabIndex: TABINDEX_EDAREW + 11,
						listeners: {
							'change': function(combo, newValue, oldValue) {
								var index = combo.getStore().findBy(function(rec) {
									return (rec.get(combo.valueField) == newValue);
								});

								combo.fireEvent('select', combo, combo.getStore().getAt(index));
							}.createDelegate(this),
							'select': function(combo, record, index) {
								var base_form = this.FormPanel.getForm();

								if ( typeof record == 'object' && !Ext.isEmpty(record.get('PlaceMedCareType_Code')) ) {
									base_form.findField('LackMedCareType2_id').clearValue();
									base_form.findField('LackMedCareType2_id').disable();
								}
								else {
									if ( this.action != 'view' ) {
										base_form.findField('LackMedCareType2_id').enable();
									}
								}
							}.createDelegate(this)
						},
						xtype: 'swcommonsprcombo'
					},
					{
						comboSubject: 'LackMedCareType',
						fieldLabel: lang['prichina_nevyipolneniya_lecheniya'],
						hiddenName: 'LackMedCareType2_id',
						lastQuery: '',
						width: 250,
						tabIndex: TABINDEX_EDAREW + 12,
						xtype: 'swcommonsprcombo'
					}
				],
				xtype: 'fieldset'
			}, {
				autoHeight: true,
				style: 'padding: 0px;',
				title: lang['meditsinskaya_reabilitatsiya_sanatorno-kurortnoe_lechenie'],
				width: 500,
				items: [
					{
						comboSubject: 'ConditMedCareType',
						fieldLabel: lang['naznacheno'],
						value: 1,
						hiddenName: 'ConditMedCareType3_nid',
						lastQuery: '',
						listeners: {
							'change': function(combo, newValue, oldValue) {
								var index = combo.getStore().findBy(function(rec) {
									return (rec.get(combo.valueField) == newValue);
								});

								combo.fireEvent('select', combo, combo.getStore().getAt(index));
							}.createDelegate(this),
							'select': function(combo, record, index) {
								var base_form = this.FormPanel.getForm();

								if ( typeof record == 'object' && record.get('ConditMedCareType_Code').toString().inlist([ '2', '3', '4' ]) ) {
									if ( this.action != 'view' ) {
										base_form.findField('PlaceMedCareType3_nid').setAllowBlank(false);
										base_form.findField('PlaceMedCareType3_nid').enable();
										base_form.findField('ConditMedCareType3_id').enable();
										if (Ext.isEmpty(base_form.findField('ConditMedCareType3_id').getValue())) {
											base_form.findField('ConditMedCareType3_id').setValue(1);
										}
										base_form.findField('PlaceMedCareType3_id').enable();
										if (Ext.isEmpty(base_form.findField('PlaceMedCareType3_id').getValue())) {
											base_form.findField('LackMedCareType3_id').enable();
										}
									}
								}
								else {
									base_form.findField('PlaceMedCareType3_nid').setAllowBlank(true);
									base_form.findField('PlaceMedCareType3_nid').clearValue();
									base_form.findField('PlaceMedCareType3_nid').disable();
									base_form.findField('ConditMedCareType3_id').clearValue();
									base_form.findField('ConditMedCareType3_id').disable();
									base_form.findField('PlaceMedCareType3_id').clearValue();
									base_form.findField('PlaceMedCareType3_id').disable();
									base_form.findField('LackMedCareType3_id').clearValue();
									base_form.findField('LackMedCareType3_id').disable();
								}
							}.createDelegate(this)
						},
						width: 250,
						tabIndex: TABINDEX_EDAREW + 13,
						xtype: 'swcommonsprcombo'
					},
					{
						comboSubject: 'PlaceMedCareType',
						//loadParams: {params: {where: ' where PlaceMedCareType_Code <= 4'}},
						fieldLabel: lang['mesto_naznacheniya'],
						hiddenName: 'PlaceMedCareType3_nid',
						lastQuery: '',
						width: 250,
						tabIndex: TABINDEX_EDAREW + 14,
						xtype: 'swcommonsprcombo'
					},
					{
						comboSubject: 'ConditMedCareType',
						fieldLabel: lang['provedeno'],
						hiddenName: 'ConditMedCareType3_id',
						lastQuery: '',
						width: 250,
						tabIndex: TABINDEX_EDAREW + 15,
						xtype: 'swcommonsprcombo'
					},
					{
						comboSubject: 'PlaceMedCareType',
						//loadParams: {params: {where: ' where PlaceMedCareType_Code <= 4'}},
						fieldLabel: lang['mesto_provedeniya'],
						hiddenName: 'PlaceMedCareType3_id',
						lastQuery: '',
						width: 250,
						tabIndex: TABINDEX_EDAREW + 16,
						listeners: {
							'change': function(combo, newValue, oldValue) {
								var index = combo.getStore().findBy(function(rec) {
									return (rec.get(combo.valueField) == newValue);
								});

								combo.fireEvent('select', combo, combo.getStore().getAt(index));
							}.createDelegate(this),
							'select': function(combo, record, index) {
								var base_form = this.FormPanel.getForm();

								if ( typeof record == 'object' && !Ext.isEmpty(record.get('PlaceMedCareType_Code')) ) {
									base_form.findField('LackMedCareType3_id').clearValue();
									base_form.findField('LackMedCareType3_id').disable();
								}
								else {
									if ( this.action != 'view' ) {
										base_form.findField('LackMedCareType3_id').enable();
									}
								}
							}.createDelegate(this)
						},
						xtype: 'swcommonsprcombo'
					},
					{
						comboSubject: 'LackMedCareType',
						fieldLabel: lang['prichina_nevyipolneniya_lecheniya'],
						hiddenName: 'LackMedCareType3_id',
						lastQuery: '',
						width: 250,
						tabIndex: TABINDEX_EDAREW + 17,
						xtype: 'swcommonsprcombo'
					}
				],
				xtype: 'fieldset'
			}, {
				comboSubject: 'HTMRecomType',
				fieldLabel: lang['vmp'],
				allowBlank: false,
				value: 1,
				hiddenName: 'HTMRecomType_id',
				tabIndex: TABINDEX_EDAREW + 18,
				xtype: 'swcommonsprcombo'
			}],
			reader: new Ext.data.JsonReader(
			{
				success: function() 
				{ 
					// alert('success');
				}
			},
			[
				{ name: 'EvnDiagDopDisp_id' }, 
				{ name: 'EvnDiagDopDisp_pid' }, 
				{ name: 'PersonEvn_id' }, 
				{ name: 'Server_id' }, 
				{ name: 'Diag_id' }, 
				{ name: 'DeseaseDispType_id' }, 
				{ name: 'DispSurveilType_id' }, 
				{ name: 'ConditMedCareType1_nid' }, 
				{ name: 'PlaceMedCareType1_nid' }, 
				{ name: 'ConditMedCareType1_id' }, 
				{ name: 'PlaceMedCareType1_id' }, 
				{ name: 'ConditMedCareType2_nid' }, 
				{ name: 'PlaceMedCareType2_nid' }, 
				{ name: 'ConditMedCareType2_id' }, 
				{ name: 'PlaceMedCareType2_id' }, 
				{ name: 'LackMedCareType2_id' }, 
				{ name: 'ConditMedCareType3_nid' }, 
				{ name: 'PlaceMedCareType3_nid' }, 
				{ name: 'ConditMedCareType3_id' }, 
				{ name: 'PlaceMedCareType3_id' }, 
				{ name: 'LackMedCareType3_id' }, 
				{ name: 'HTMRecomType_id' }
			]
			),
			url: '/?c=EvnDiagDopDisp&m=saveEvnDiagDopDispAndRecomendation'
		});
		
		Ext.apply(this,
		{
			border: false,
			items: [this.FormPanel],
			buttons:
			[
				{
					text: BTN_FRMSAVE,
					tabIndex: TABINDEX_EDAREW + 91,
					iconCls: 'save16',
					handler: function() {
						this.doSave();
					}.createDelegate(this)
				},
				{
					text:'-'
				},
				HelpButton(this, TABINDEX_EDAREW + 92),
				{
					text: BTN_FRMCANCEL,
					tabIndex: TABINDEX_EDAREW + 93,
					iconCls: 'cancel16',
					handler: function()
					{
						this.hide();
					}.createDelegate(this)
				}
			]
		});
		
		sw.Promed.swEvnDiagDopDispAndRecomendationEditWindow.superclass.initComponent.apply(this, arguments);
	}
});