/**
* swEvnPrescrTreatDrugEditWindow - окно добавления/редактирования медикамента при назначении лекарственного лечения.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Prescription
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Stas Bykov aka Savage (savage@swan.perm.ru)
* @version      0.001-03.11.2011
* @comment      Префикс для id компонентов EPRTRDEF (EvnPrescrTreatDrugEditForm)
*/
/*NO PARSE JSON*/

sw.Promed.swEvnPrescrTreatDrugEditWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swEvnPrescrTreatDrugEditWindow',
	objectSrc: '/jscore/Forms/Prescription/swEvnPrescrTreatDrugEditWindow.js',

	action: null,
	autoHeight: true,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	collapsible: false,
	doSave: function() {
		var base_form = this.FormPanel.getForm();

		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					this.FormPanel.getFirstInvalidEl().focus(true);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		var evnPrescrTreatDrugData = new Object();

		var drug_prep_fas_id = base_form.findField('DrugPrepFas_id').getValue();
		var drug_name = '';

		var index = base_form.findField('DrugPrepFas_id').getStore().findBy(function(rec) {
			if ( rec.get('DrugPrepFas_id') == drug_prep_fas_id ) {
				return true;
			}
			else {
				return false;
			}
		});
		var record = base_form.findField('DrugPrepFas_id').getStore().getAt(index);

		if ( record ) {
			drug_name = record.get('DrugPrep_Name');
		}

		evnPrescrTreatDrugData.Drug_id = base_form.findField('Drug_id').getValue();
		evnPrescrTreatDrugData.Drug_Name = drug_name;
		evnPrescrTreatDrugData.DrugPrepFas_id = drug_prep_fas_id;
		evnPrescrTreatDrugData.EvnPrescrTreatDrug_id = base_form.findField('EvnPrescrTreatDrug_id').getValue();
		evnPrescrTreatDrugData.EvnPrescrTreatDrug_Kolvo = base_form.findField('EvnPrescrTreatDrug_Kolvo').getValue();
		evnPrescrTreatDrugData.EvnPrescrTreatDrug_Kolvo_Show = base_form.findField('EvnPrescrTreatDrug_Kolvo_Show').getValue();
		evnPrescrTreatDrugData.EvnPrescrTreatDrug_KolvoEd = base_form.findField('EvnPrescrTreatDrug_KolvoEd').getValue();

		this.callback({ evnPrescrTreatDrugData: evnPrescrTreatDrugData});
		this.hide();
	},
	draggable: true,
	// height: 550,
	id: 'EvnPrescrTreatDrugEditWindow',
	initComponent: function() {
		this.FormPanel = new Ext.form.FormPanel({
			autoHeight: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			border: false,
			frame: false,
			id: 'EvnPrescrTreatDrugEditForm',
			labelAlign: 'right',
			labelWidth: 150,
			reader: new Ext.data.JsonReader({
				success: Ext.amptyFn
			},  [
				{ name: 'Drug_id' },
				{ name: 'DrugPrepFas_id' },
				{ name: 'EvnPrescrTreatDrug_id' },
				{ name: 'EvnPrescrTreatDrug_Kolvo' },
				{ name: 'EvnPrescrTreatDrug_Kolvo_Show' },
				{ name: 'EvnPrescrTreatDrug_KolvoEd' }
			]),
			region: 'center',
			url: '/?c=EvnPrescr&m=saveEvnPrescrTreatDrug',

			items: [{
				name: 'EvnPrescrTreatDrug_id', // Идентификатор назначенного медикамента
				value: -1,
				xtype: 'hidden'
			}, { // Медикамент
				allowBlank: false,
				hiddenName: 'DrugPrepFas_id',
				width: 400,
				xtype: 'swdrugprepcombo'
			}, { // Упаковка
				hiddenName: 'Drug_id',
				listeners: {
					'change': function(combo, newValue, oldValue) {
						var base_form = this.FormPanel.getForm();
						var record = combo.getStore().getById(newValue);

						base_form.findField('Drug_Fas').setRawValue('');
						base_form.findField('DrugForm_Name').setRawValue('');
						base_form.findField('DrugUnit_Name').setRawValue('');

						if ( !record ) {
							// base_form.findField('EvnPrescrTreatDrug_Kolvo').fireEvent('change', base_form.findField('EvnPrescrTreatDrug_Kolvo'), base_form.findField('EvnPrescrTreatDrug_Kolvo').getValue());
							return false;
						}

						base_form.findField('Drug_Fas').setRawValue(record.get('Drug_Fas') ? record.get('Drug_Fas') : 1);
						base_form.findField('DrugForm_Name').setRawValue(record.get('DrugForm_Name'));
						base_form.findField('DrugUnit_Name').setRawValue(record.get('DrugUnit_Name'));

						return true;
					}.createDelegate(this)
				},
				// tabIndex: TABINDEX_EDEW + 5,
				width: 400,
				xtype: 'swdrugpackcombo'
			}, {
				border: false,
				layout: 'column',

				items: [{
					border: false,
					layout: 'form',
					items: [{
						disabled: true,
						fieldLabel: lang['ed_ucheta'],
						name: 'DrugUnit_Name',
						// tabIndex: TABINDEX_EDEW + 6,
						width: 70,
						xtype: 'textfield'
					}]
				}, {
					border: false,
					labelWidth: 100,
					layout: 'form',
					items: [{
						disabled: true,
						fieldLabel: lang['lek_forma'],
						name: 'DrugForm_Name',
						// tabIndex: TABINDEX_EDEW + 7,
						width: 70,
						xtype: 'textfield'
					}]
				}, {
					border: false,
					labelWidth: 100,
					layout: 'form',
					items: [{
						disabled: true,
						fieldLabel: lang['kol-vo_v_upak'],
						name: 'Drug_Fas',
						// tabIndex: TABINDEX_EDEW + 8,
						width: 70,
						xtype: 'numberfield'
					}]
				}]
			}, {
				border: false,
				layout: 'column',

				items: [{
					border: false,
					layout: 'form',

					items: [{
						allowBlank: false,
						allowDecimals: true,
						allowNegative: false,
						decimalPrecision: 4,
						fieldLabel: lang['kolichestvo_ed_uch'],
						listeners: {
							'change': function(field, newValue, oldValue) {
								var base_form = this.FormPanel.getForm();

								// Расчет суммы - цена берется из медикамента
								if ( newValue.toString().length == 0 ) {
									base_form.findField('EvnPrescrTreatDrug_KolvoEd').setValue('');
								}
								else {
									var fas = base_form.findField('Drug_Fas').getValue() > 0 ? base_form.findField('Drug_Fas').getValue() : 1;
									var kolvo_ed = (fas * newValue).toFixed(2);
									
									base_form.findField('EvnPrescrTreatDrug_KolvoEd').setValue(kolvo_ed);
									base_form.findField('EvnPrescrTreatDrug_Kolvo').setValue((kolvo_ed / fas).toFixed(6));
									base_form.findField('EvnPrescrTreatDrug_Kolvo_Show').setValue(newValue.toFixed(4));
								}
							}.createDelegate(this)
						},
						minValue: 0.0001,
						name: 'EvnPrescrTreatDrug_Kolvo_Show',
						// tabIndex: TABINDEX_EDEW + 12,
						width: 100,
						xtype: 'numberfield'
					}]
				}, {
					border: false,
					hidden: true,
					labelWidth: 50,
					layout: 'form',

					items: [{
						allowBlank: false,
						allowDecimals: true,
						allowNegative: false,
						decimalPrecision: 6,
						fieldLabel: 'Real',
						listeners: {
							'change': function(field, newValue, oldValue) {
								//
							}.createDelegate(this)
						},
						minValue: 0.000001,
						name: 'EvnPrescrTreatDrug_Kolvo',
						// tabIndex: TABINDEX_EDEW + 12,
						width: 50,							
						xtype: 'numberfield'
					}]
				}, {
					border: false,
					labelWidth: 130,
					layout: 'form',

					items: [{
						// allowBlank: false,
						allowDecimals: true,
						allowNegative: false,
						decimalPrecision: 2,
						// disabled: true,
						fieldLabel: lang['kol-vo_ed_doz'],
						listeners: {
							'change': function(field, newValue, oldValue) {
								var base_form = this.FormPanel.getForm();

								if ( newValue.toString().length == 0 ) {
									base_form.findField('EvnPrescrTreatDrug_Kolvo').setValue('');
									base_form.findField('EvnPrescrTreatDrug_Kolvo_Show').setValue('');
								}
								else {
									var kolvo = newValue / base_form.findField('Drug_Fas').getValue();
									var kolvo_show = kolvo.toFixed(4);

									base_form.findField('EvnPrescrTreatDrug_Kolvo').setValue(kolvo.toFixed(6));
									base_form.findField('EvnPrescrTreatDrug_Kolvo_Show').setValue(kolvo_show);
								}
							}.createDelegate(this)
						},
						name: 'EvnPrescrTreatDrug_KolvoEd',
						// tabIndex: TABINDEX_EDEW + 13,
						width: 100,
						xtype: 'numberfield'
					}]
				}]
			}]
		});

		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.doSave();
				}.createDelegate(this),
				iconCls: 'save16',
				onShiftTabAction: function () {
					// var base_form = this.FormPanel.getForm();
				}.createDelegate(this),
				onTabAction: function () {
					// this.buttons[1].focus();
				}.createDelegate(this),
				// tabIndex: TABINDEX_EPRDTEF + 34,
				text: BTN_FRMSAVE
			}, {
				text: '-'
			},
			HelpButton(this, -1),
			{
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'cancel16',
				onShiftTabAction: function () {
					// this.buttons[1].focus();
				}.createDelegate(this),
				onTabAction: function () {
					// var base_form = this.FormPanel.getForm();
				}.createDelegate(this),
				// tabIndex: TABINDEX_EPRDTEF + 36,
				text: BTN_FRMCANCEL
			}],
			items: [
				this.FormPanel
			],
			layout: 'form'
		});

		sw.Promed.swEvnPrescrTreatDrugEditWindow.superclass.initComponent.apply(this, arguments);
	},
	keys: [{
		alt: true,
		fn: function(inp, e) {
			var current_window = Ext.getCmp('EvnPrescrTreatDrugEditWindow');

			switch ( e.getKey() ) {
				case Ext.EventObject.C:
					current_window.doSave();
				break;

				case Ext.EventObject.J:
					current_window.hide();
				break;
			}
		},
		key: [
			Ext.EventObject.C,
			Ext.EventObject.J
		],
		scope: this,
		stopEvent: false
	}],
	layout: 'form',
	listeners: {
		'beforehide': function(win) {
			//
		},
		'hide': function(win) {
			win.onHide();
		}
	},
	maximizable: false,
	maximized: false,
	modal: true,
	onHide: Ext.emptyFn,
	plain: true,
	resizable: false,
	show: function() {
		sw.Promed.swEvnPrescrTreatDrugEditWindow.superclass.show.apply(this, arguments);

		this.center();

		var base_form = this.FormPanel.getForm();
		base_form.reset();

		base_form.findField('Drug_id').getStore().removeAll();
		base_form.findField('DrugPrepFas_id').getStore().removeAll();

		this.action = null;
		this.callback = Ext.emptyFn;
		this.onHide = Ext.emptyFn;
		
		if ( !arguments[0] || !arguments[0].formParams ) {
			sw.swMsg.alert(lang['soobschenie'], lang['nevernyie_parametryi'], function() { this.hide(); }.createDelegate(this) );
			return false;
		}

		base_form.setValues(arguments[0].formParams);

		if ( arguments[0].action && typeof arguments[0].action == 'string' ) {
			this.action = arguments[0].action;
		}

		if ( arguments[0].callback && typeof arguments[0].callback == 'function' ) {
			this.callback = arguments[0].callback;
		}

		if ( arguments[0].onHide && typeof arguments[0].onHide == 'function' ) {
			this.onHide = arguments[0].onHide;
		}

		switch ( this.action ) {
			case 'add':
				this.setTitle(lang['medikament_dobavlenie']);
			break;

			case 'edit':
				this.setTitle(lang['medikament_redaktirovanie']);

				var drug_id = base_form.findField('Drug_id').getValue();
				var drug_prep_fas_id = base_form.findField('DrugPrepFas_id').getValue();

				base_form.findField('Drug_id').getStore().baseParams = new Object();
				base_form.findField('DrugPrepFas_id').getStore().baseParams = new Object();

				base_form.findField('DrugPrepFas_id').getStore().load({
					callback: function() {
						if ( base_form.findField('DrugPrepFas_id').getStore().getCount() > 0 ) {
							base_form.findField('DrugPrepFas_id').setValue(drug_prep_fas_id);
						}
					},
					params: {
						DrugPrepFas_id: drug_prep_fas_id
					}
				});


				base_form.findField('Drug_id').getStore().load({
					callback: function() {
						if ( base_form.findField('Drug_id').getStore().getCount() > 0 ) {
							base_form.findField('Drug_id').setValue(drug_id);
							base_form.findField('Drug_id').fireEvent('change', base_form.findField('Drug_id'), drug_id);
						}
					},
					params: {
						DrugPrepFas_id: drug_prep_fas_id
					}
				});
			break;

			default:
				this.hide();
			break;
		}

		base_form.clearInvalid();

		base_form.findField('DrugPrepFas_id').focus(true, 250);
	},
	width: 600
});