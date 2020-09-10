/**
* swDrugTorgPositionEditWindow - окно редактирования торговой позиции.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Farmacy
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Stas Bykov aka Savage (savage@swan.perm.ru)
* @version      0.001-29.12.2009
* @comment      Префикс для id компонентов DTPEF (DrugTorgPositionEditForm)
*
*
* @input data: action - действие (add, edit, view)
*/

sw.Promed.swDrugTorgPositionEditWindow = Ext.extend(sw.Promed.BaseForm, {
	action: null,
	autoHeight: true,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	collapsible: false,
	doSave: function() {
		var data = new Object();
		var record;

		var form = this.findById('DrugTorgPositionEditForm');
		var base_form = form.getForm();

		if ( !base_form.isValid() ) {
			Ext.MessageBox.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					form.getFirstInvalidEl().focus(true);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		var post = getAllFormFieldValues(form);

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT_SAVE });
		loadMask.show();

		base_form.submit({
			failure: function(result_form, action) {
				loadMask.hide();

				if ( action.result ) {
					if ( action.result.Error_Msg ) {
						sw.swMsg.alert(lang['oshibka'], action.result.Error_Msg);
					}
					else {
						sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_3]']);
					}
				}
			},
			params: post,
			success: function(result_form, action) {
				loadMask.hide();

				if ( action.result && action.result.DocumentUcStr_id > 0 ) {
					base_form.findField('DocumentUcStr_id').setValue(action.result.DocumentUcStr_id);

					var drug_id = base_form.findField('Drug_id').getValue();
					var drug_name = '';

					record = base_form.findField('Drug_id').getStore().getById(drug_id);
					if ( record ) {
						drug_name = record.get('Drug_Name');
					}

					data.documentUcStrData = [{
						'DocumentUcStr_id': base_form.findField('DocumentUcStr_id').getValue(),
						'EvnRecept_id': base_form.findField('EvnRecept_id').getValue(),
						'Drug_id': drug_id,
						'Drug_Name': drug_name,
						'DocumentUcStr_RashCount': base_form.findField('DocumentUcStr_RashCount').getValue()
					}];

					this.callback(data);
					this.hide();
				}
				else {
					sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_2]']);
				}
			}.createDelegate(this)
		});
	},
	draggable: true,
	DrugMnn_id: null,
	DrugTorg_id: null,
	enableEdit: Ext.emptyFn,
	EvnRecept_IsMnn_Code: null,
	id: 'DrugTorgPositionEditWindow',
	initComponent: function() {
		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.doSave();
				}.createDelegate(this),
				iconCls: 'save16',
				// tabIndex: TABINDEX_EREF + 20,
				text: BTN_FRMSAVE,
				tooltip: lang['sohranit_vvedennyie_dannyie']
			}, {
				text: '-'
			},
			HelpButton(this/*, TABINDEX_EREF + 23*/),
			{
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'cancel16',
				onShiftTabAction: function () {
					this.buttons[this.buttons.length - 2].focus();
				}.createDelegate(this),
				onTabAction: function () {
					//
				}.createDelegate(this),
				// tabIndex: TABINDEX_EREF + 24,
				text: BTN_FRMCANCEL,
				tooltip: lang['zakryit_okno']
			}],
			items: [ new Ext.form.FormPanel({
				autoScroll: true,
				bodyStyle: 'padding: 0.5em;',
				border: false,
				frame: false,
				id: 'DrugTorgPositionEditForm',
				items: [{
					name: 'DocumentUcStr_id',
					value: 0,
					xtype: 'hidden'
				}, {
					name: 'EvnRecept_id',
					value: 0,
					xtype: 'hidden'
				}, {
					allowBlank: false,
					displayField: 'Drug_Name',
					enableKeyEvents: true,
					fieldLabel: lang['medikament'],
					forceSelection: true,
					hiddenName: 'Drug_id',
					listWidth: 800,
					listeners: {
						'beforeselect': function() {
							// this.findById('EREF_DrugCombo').lastQuery = '';
							return true;
						}.createDelegate(this),
						'change': function(combo, newValue, oldValue) {
							var base_form = this.findById('DrugTorgPositionEditForm').getForm();

							var document_uc_str_combo = base_form.findField('DocumentUcStr_id');

							document_uc_str_combo.clearValue();
							document_uc_str_combo.getStore().removeAll();
							document_uc_str_combo.lastQuery = '';

							if ( newValue > 0 ) {
								document_uc_str_combo.getStore().load({
									params: {
										Drug_id: newValue
									}
								});
							}
							else {
								document_uc_str_combo.fireEvent('change', document_uc_str_combo, null, 1);
							}

							return true;
						}.createDelegate(this)
					},
					loadingText: lang['idet_poisk'],
					minChars: 1,
					minLength: 1,
					minLengthText: lang['pole_doljno_byit_zapolneno'],
					mode: 'local',
					resizable: true,
					selectOnFocus: true,
					store: new Ext.data.Store({
						autoLoad: false,
						reader: new Ext.data.JsonReader({
							id: 'Drug_id'
						}, [
							{ name: 'Drug_id', mapping: 'Drug_id' },
							{ name: 'Drug_Name', mapping: 'Drug_Name' }
						]),
						url: '/?c=Farmacy&m=loadDrugList'
					}),
					tpl: new Ext.XTemplate(
						'<tpl for="."><div class="x-combo-list-item">',
						'<h3>{Drug_Name}&nbsp;</h3>',
						'</div></tpl>'
					),
					triggerAction: 'all',
					valueField: 'Drug_id',
					width: 500,
					xtype: 'combo'
				}, {
					border: false,
					layout: 'column',
					items: [{
						border: false,
						layout: 'form',
						items: [{
							disabled: true,
							fieldLabel: lang['ed_ucheta'],
							name: 'Drug_EdUch',
							// tabIndex: TABINDEX_EREF + 3,
							width: 70,
							xtype: 'textfield'
						}]
					}, {
						border: false,
						labelWidth: 100,
						layout: 'form',
						items: [{
							disabled: true,
							fieldLabel: lang['ed_dozirovki'],
							name: 'Drug_EdDos',
							// tabIndex: TABINDEX_EREF + 3,
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
							name: 'Drug_KolvoUpak',
							// tabIndex: TABINDEX_EREF + 3,
							width: 70,
							xtype: 'textfield'
						}]
					}]
				}, {
					allowBlank: false,
					displayField: 'DocumentUcStr_Name',
					enableKeyEvents: true,
					fieldLabel: lang['partiya'],
					forceSelection: true,
					hiddenName: 'DocumentUcStr_oid',
					listWidth: 800,
					listeners: {
						'beforeselect': function() {
							// this.findById('EREF_DrugCombo').lastQuery = '';
							return true;
						}.createDelegate(this),
						'change': function(combo, newValue, oldValue) {
							var base_form = this.findById('DrugTorgPositionEditForm').getForm();

							base_form.findField('DocumentUcStr_Count').setValue('');
							base_form.findField('DocumentUcStr_EdCount').setValue('');
							base_form.findField('DocumentUcStr_Price').setValue('');

							base_form.findField('DocumentUcStr_RashCount').fireEvent('change', base_form.findField('DocumentUcStr_RashCount'), '', 1);

							var record = combo.getStore().getById(newValue);

							if ( record ) {
								base_form.findField('DocumentUcStr_Count').setValue(record.get('DocumentUcStr_Count'));
								base_form.findField('DocumentUcStr_EdCount').setValue(record.get('DocumentUcStr_EdCount'));
								base_form.findField('DocumentUcStr_Price').setValue(record.get('DocumentUcStr_Price'));

								base_form.findField('DocumentUcStr_RashCount').fireEvent('change', base_form.findField('DocumentUcStr_RashCount'), base_form.findField('DocumentUcStr_RashCount').getValue(), 0);
							}

							return true;
						}.createDelegate(this)
					},
					loadingText: lang['idet_poisk'],
					minChars: 1,
					minLength: 1,
					minLengthText: lang['pole_doljno_byit_zapolneno'],
					mode: 'local',
					resizable: true,
					selectOnFocus: true,
					store: new Ext.data.Store({
						autoLoad: false,
						reader: new Ext.data.JsonReader({
							id: 'DocumentUcStr_id'
						}, [
							{ name: 'DocumentUcStr_id', mapping: 'DocumentUcStr_id' },
							{ name: 'DocumentUcStr_Name', mapping: 'DocumentUcStr_Name' },
							{ name: 'DrugFinance_id', mapping: 'DrugFinance_id' },
							{ name: 'DocumentUcStr_Count', mapping: 'DocumentUcStr_Count' },
							{ name: 'DocumentUcStr_EdCount', mapping: 'DocumentUcStr_EdCount' },
							{ name: 'DocumentUcStr_Ser', mapping: 'DocumentUcStr_Ser' },
							{ name: 'DrugFinance_Name', mapping: 'DrugFinance_Name' },
							{ name: 'DocumentUcStr_godDate', mapping: 'DocumentUcStr_godDate' },
							{ name: 'DocumentUcStr_Price', mapping: 'DocumentUcStr_Price' }
						]),
						url: '/?c=Farmacy&m=loadDocumentUcStrList'
					}),
					tpl: new Ext.XTemplate(
						'<table cellpadding="0" cellspacing="0" style="width: 100%;"><tr style="font-family: tahoma; font-size: 10pt; font-weight: bold;">',
						'<td style="padding: 2px; width: 15%;">Срок годности</td>',
						'<td style="padding: 2px; width: 15%;">Цена</td>',
						'<td style="padding: 2px; width: 15%;">Остаток</td>',
						'<td style="padding: 2px; width: 40%;">Источник финансирования</td>',
						'<td style="padding: 2px; width: 15%;">Серия</td></tr>',
						'<tpl for="."><tr class="x-combo-list-item">',
						'<td style="padding: 2px;">{DocumentUcStr_godDate}&nbsp;</td>',
						'<td style="padding: 2px;">{DocumentUcStr_Price}&nbsp;</td>',
						'<td style="padding: 2px;">{DocumentUcStr_Count}&nbsp;</td>',
						'<td style="padding: 2px;">{DrugFinance_Name}&nbsp;</td>',
						'<td style="padding: 2px;">{DocumentUcStr_Ser}&nbsp;</td>',
						'</tr></tpl>',
						'</table>'
					),
					triggerAction: 'all',
					valueField: 'DocumentUcStr_id',
					width: 500,
					xtype: 'combo'
				}, {
					border: false,
					layout: 'column',
					items: [{
						border: false,
						layout: 'form',
						items: [{
							disabled: true,
							fieldLabel: lang['ostatok_ed_uch'],
							name: 'DocumentUcStr_Count',
							// tabIndex: TABINDEX_EREF + 3,
							width: 100,
							xtype: 'numberfield'
						}]
					}, {
						border: false,
						labelWidth: 150,
						layout: 'form',
						items: [{
							disabled: true,
							fieldLabel: lang['ostatok_ed_doz'],
							name: 'DocumentUcStr_EdCount',
							// tabIndex: TABINDEX_EREF + 3,
							width: 100,
							xtype: 'numberfield'
						}]
					}]
				}, {
					allowBlank: false,
					disabled: true,
					fieldLabel: lang['tsena'],
					name: 'DocumentUcStr_Price',
					// tabIndex: TABINDEX_EREF + 3,
					width: 100,
					xtype: 'textfield'
				}, {
					border: false,
					layout: 'column',
					items: [{
						border: false,
						layout: 'form',
						items: [{
							allowBlank: false,
							allowDecimals: false,
							allowNegative: false,
							fieldLabel: lang['kolichestvo_ed_uch'],
							listeners: {
								'change': function(field, newValue, oldValue) {
									var base_form = this.findById('DrugTorgPositionEditForm').getForm();

									var max_value = base_form.findField('DocumentUcStr_Count').getValue();
									var price = base_form.findField('DocumentUcStr_Price').getValue();

									if ( newValue > max_value ) {
										newValue = max_value;
										field.setValue(max_value);
									}

									if ( newValue.toString().length == 0 ) {
										base_form.findField('DocumentUcStr_Sum').setValue('');
									}
									else {
										base_form.findField('DocumentUcStr_Sum').setValue(price * newValue);
									}
								}.createDelegate(this)
							},
							minValue: 0,
							name: 'DocumentUcStr_RashCount',
							width: 100,
							xtype: 'numberfield'
						}]
					}, {
						border: false,
						labelWidth: 150,
						layout: 'form',
						items: [{
							// allowBlank: false,
							allowDecimals: false,
							allowNegative: false,
							disabled: true,
							fieldLabel: lang['kolichestvo_ed_doz'],
							name: 'DocumentUcStr_RashEdCount',
							width: 100,
							xtype: 'numberfield'
						}]
					}]
				}, {
					allowBlank: false,
					disabled: true,
					fieldLabel: lang['summa'],
					name: 'DocumentUcStr_Sum',
					// tabIndex: TABINDEX_EREF + 3,
					width: 100,
					xtype: 'textfield'
				}],
				labelAlign: 'right',
				labelWidth: 130,
				reader: new Ext.data.JsonReader({
					success: Ext.emptyFn
				}, [
					{ name: 'DocumentUcStr_id' },
					{ name: 'DocumentUcStr_oid' },
					{ name: 'Drug_id' },
					{ name: 'Drug_Kolvo' },
					{ name: 'EvnRecept_id' }
				]),
				region: 'center',
				trackResetOnLoad: true,
				url: '/?c=Farmacy&m=saveDrugTorgPosition'
			})]
		});
		sw.Promed.swDrugTorgPositionEditWindow.superclass.initComponent.apply(this, arguments);
	},
	keys: [{
		alt: true,
		fn: function(inp, e) {
			var current_window = Ext.getCmp('DrugTorgPositionEditWindow');

			e.stopEvent();

			if ( e.browserEvent.stopPropagation ) {
				e.browserEvent.stopPropagation();
			}
			else {
				e.browserEvent.cancelBubble = true;
			}

			if ( e.browserEvent.preventDefault ) {
				e.browserEvent.preventDefault();
			}
			else {
				e.browserEvent.returnValue = false;
			}

			e.returnValue = false;

			if ( Ext.isIE ) {
				e.browserEvent.keyCode = 0;
				e.browserEvent.which = 0;
			}

			switch (e.getKey()) {
				case Ext.EventObject.J:
					current_window.hide();
				break;

				case Ext.EventObject.C:
					current_window.doSave();
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
		'hide': function() {
			this.onHide();
		}
	},
	maximizable: false,
	modal: true,
	onHide: Ext.emptyFn,
	plain: true,
	ReceptFinance_Code: null,
	resizable: false,
	show: function() {
		sw.Promed.swDrugTorgPositionEditWindow.superclass.show.apply(this, arguments);

		if ( !arguments[0] ) {
			sw.swMsg.alert(lang['oshibka'], lang['otsutstvuyut_neobhodimyie_parametryi'], function() { this.hide(); }.createDelegate(this) );
			return false;
		}

		var base_form = this.findById('DrugTorgPositionEditForm').getForm();

		this.action = null;
		this.callback = Ext.emptyFn;
		this.DrugMnn_id = null;
		this.DrugTorg_id = null;
		this.EvnRecept_IsMnn_Code = null;
		this.onHide = Ext.emptyFn;
		this.ReceptFinance_Code = null;

		this.center();
		base_form.reset();

		if ( arguments[0].action ) {
			this.action = arguments[0].action;
		}

		if ( arguments[0].callback ) {
			this.callback = arguments[0].callback;
		}

		if ( arguments[0].DrugMnn_id ) {
			this.DrugMnn_id = arguments[0].DrugMnn_id;
		}

		if ( arguments[0].DrugTorg_id ) {
			this.DrugTorg_id = arguments[0].DrugTorg_id;
		}

		if ( arguments[0].EvnRecept_IsMnn_Code ) {
			this.EvnRecept_IsMnn_Code = arguments[0].EvnRecept_IsMnn_Code;
		}

		if ( arguments[0].onHide ) {
			this.onHide = arguments[0].onHide;
		}

		if ( arguments[0].ReceptFinance_Code ) {
			this.ReceptFinance_Code = arguments[0].ReceptFinance_Code;
		}
/*
		// В baseParams списка медикаментов записать DrugMnn_id, DrugTorg_id, EvnRecept_IsMnn_Code, ReceptFinance_Code
		base_form.findField('Drug_id').getStore().baseParams = {
			DrugMnn_id: this.DrugMnn_id,
			DrugTorg_id: this.DrugTorg_id,
			EvnRecept_IsMnn_Code: this.EvnRecept_IsMnn_Code,
			ReceptFinance_Code: this.ReceptFinance_Code
		}
*/
		var loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT });
		loadMask.show();

		switch ( this.action ) {
			case 'add':
				this.enableEdit(true);
				this.setTitle(lang['torgovaya_pozitsiya_dobavlenie']);

				loadMask.hide();

				loadMask = new Ext.LoadMask(this.getEl(), { msg: lang['zagruzka_spiska_medikamentov'] });
				loadMask.show();

				base_form.findField('Drug_id').getStore().load({
					callback: function() {
						loadMask.hide();
					},
					params: {
						DrugMnn_id: this.DrugMnn_id,
						DrugTorg_id: this.DrugTorg_id,
						EvnRecept_IsMnn_Code: this.EvnRecept_IsMnn_Code,
						ReceptFinance_Code: this.ReceptFinance_Code
					}
				})

				loadMask.hide();

				base_form.clearInvalid();

				base_form.findField('Drug_id').focus(true, 250);
			break;

			case 'edit':
				this.enableEdit(true);
				this.setTitle(lang['torgovaya_pozitsiya_redaktirovanie']);

				loadMask.hide();

				base_form.clearInvalid();

				base_form.findField('Drug_id').focus(true, 250);
			break;

			case 'view':
				this.enableEdit(false);
				this.setTitle(lang['torgovaya_pozitsiya_prosmotr']);

				loadMask.hide();

				base_form.clearInvalid();

				this.buttons[this.buttons.length - 1].focus();
			break;
		}
	},
	title: lang['torgovaya_pozitsiya'],
	width: 700
});
