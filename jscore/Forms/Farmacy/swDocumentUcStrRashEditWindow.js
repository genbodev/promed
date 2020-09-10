/**
* swDocumentUcStrRashEditWindow - окно редактирования/добавления диагноза (стоматология).
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Farmacy
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Stas Bykov aka Savage (savage@swan.perm.ru)
* @version      0.001-21.01.2010
* @comment      Префикс для id компонентов DUSREF (DocumentUcStrRashEditForm)
*/

sw.Promed.swDocumentUcStrRashEditWindow = Ext.extend(sw.Promed.BaseForm, {
	action: null,
	autoHeight: true,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	doSave: function() {
		var form = this.findById('DocumentUcStrRashEditForm');
		var base_form = form.getForm();

		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					if ( form.getFirstInvalidEl() ) {
						form.getFirstInvalidEl().focus(false);
					}
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		var response = new Object();

		var document_uc_str_name = '';
		var document_uc_str_oid = base_form.findField('DocumentUcStr_oid').getValue();

		var record = base_form.findField('DocumentUcStr_oid').getStore().getById(document_uc_str_oid);
		if ( record ) {
			document_uc_str_name = record.get('DocumentUcStr_Name');
		}

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT_SAVE });
		loadMask.show();

		response.DocumentUcStr_id = base_form.findField('DocumentUcStr_id').getValue();
		response.DocumentUcStr_oid = document_uc_str_oid;
		response.DocumentUcStr_Name = document_uc_str_name;
		response.DocumentUcStr_RashCount = base_form.findField('DocumentUcStr_RashCount').getValue();
		response.DocumentUcStr_Price = base_form.findField('DocumentUcStr_Price').getValue();
		response.DocumentUcStr_PriceR = base_form.findField('DocumentUcStr_PriceR').getValue();
		response.DocumentUcStr_Sum = base_form.findField('DocumentUcStr_Sum').getValue();
		response.DocumentUcStr_SumNdsR = base_form.findField('DocumentUcStr_SumNdsR').getValue();
		response.Drug_id = base_form.findField('Drug_id').getValue();

		loadMask.hide();

		this.callback({ documentUcStrData: response });
		this.hide();
	},
	draggable: true,
	enableEdit: function(enable) {
		var base_form = this.findById('DocumentUcStrRashEditForm').getForm();

		if ( enable ) {
			base_form.findField('DocumentUcStr_oid').enable();
			base_form.findField('DocumentUcStr_RashCount').enable();
			base_form.findField('DocumentUcStr_SumNdsR').enable();
			this.buttons[0].show();
		}
		else {
			base_form.findField('DocumentUcStr_oid').disable();
			base_form.findField('DocumentUcStr_RashCount').disable();
			base_form.findField('DocumentUcStr_SumNdsR').disable();
			this.buttons[0].hide();
		}
	},
	id: 'DocumentUcStrRashEditWindow',
	initComponent: function() {
		Ext.apply(this, {
			buttons: [{
				handler: function() {
					if ( this.action != 'view' ) {
						this.doSave();
					}
				}.createDelegate(this),
				iconCls: 'save16',
				tabIndex: TABINDEX_DUSREF + 9,
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
					if ( this.action != 'view' ) {
						this.buttons[0].focus();
					}
				}.createDelegate(this),
				onTabAction: function () {
					if ( this.action != 'view' ) {
						this.findById('DocumentUcStrRashEditForm').getForm().findField('DocumentUcStr_oid').focus(true, 100);
					}
				}.createDelegate(this),
				tabIndex: TABINDEX_DUSREF + 10,
				text: BTN_FRMCANCEL
			}],
			items: [ new Ext.form.FormPanel({
				bodyBorder: false,
				bodyStyle: 'padding: 5px 5px 0',
				border: false,
				buttonAlign: 'left',
				frame: false,
				id: 'DocumentUcStrRashEditForm',
				labelAlign: 'right',
				labelWidth: 150,
				items: [{
					name: 'DocumentUcStr_id',
					value: 0,
					xtype: 'hidden'
				}, {
					name: 'Drug_id',
					value: 0,
					xtype: 'hidden'
				}, {
					name: 'DrugMnn_id',
					value: 0,
					xtype: 'hidden'
				}, {
					name: 'DrugTorg_id',
					value: 0,
					xtype: 'hidden'
				}, {
					name: 'EvnRecept_otpDate',
					value: '',
					xtype: 'hidden'
				},/* {
					name: 'EvnRecept_Kolvo',
					value: 0,
					xtype: 'hidden'
				}, */{
					allowBlank: false,
					displayField: 'DocumentUcStr_Name',
					fieldLabel: lang['medikament'],
					forceSelection: true,
					hiddenName: 'DocumentUcStr_oid',
					listWidth: 600,
					listeners: {
						'change': function(combo, newValue, oldValue) {
							var base_form = this.findById('DocumentUcStrRashEditForm').getForm();

							base_form.findField('DocumentUcStr_Count').setValue('');
							// base_form.findField('DocumentUcStr_EdCount').setValue('');
							base_form.findField('DocumentUcStr_Price').setValue('');
							base_form.findField('DocumentUcStr_PriceR').setValue('');

							base_form.findField('DocumentUcStr_RashCount').maxValue = undefined;

							base_form.findField('DocumentUcStr_RashCount').fireEvent('change', base_form.findField('DocumentUcStr_RashCount'), '', 1);

							var record = combo.getStore().getById(newValue);

							if ( record ) {
								base_form.findField('DocumentUcStr_Count').setValue(record.get('DocumentUcStr_Count'));
								// base_form.findField('DocumentUcStr_EdCount').setValue(record.get('DocumentUcStr_EdCount'));
								base_form.findField('DocumentUcStr_Price').setValue(record.get('DocumentUcStr_Price'));
								base_form.findField('DocumentUcStr_PriceR').setValue(record.get('DocumentUcStr_PriceR'));

								base_form.findField('DocumentUcStr_RashCount').maxValue = record.get('DocumentUcStr_Count');
								base_form.findField('DocumentUcStr_RashCount').fireEvent('change', base_form.findField('DocumentUcStr_RashCount'), base_form.findField('DocumentUcStr_RashCount').getValue(), 0);
							}

							return true;
						}.createDelegate(this),
						'keydown': function(inp, e) {
							if ( e.getKey() == Ext.EventObject.TAB && e.shiftKey == true ) {
								e.stopEvent();
								this.buttons[this.buttons.length - 1].focus();
							}
						}.createDelegate(this)
					},
					resizable: true,
					selectOnFocus: true,
					store: new Ext.data.Store({
						autoLoad: false,
						reader: new Ext.data.JsonReader({
							id: 'DocumentUcStr_id'
						}, [
							{ name: 'DocumentUcStr_id', mapping: 'DocumentUcStr_id' },
							{ name: 'DocumentUcStr_Name', mapping: 'DocumentUcStr_Name' },
							{ name: 'DocumentUcStr_Count', mapping: 'DocumentUcStr_Count' },
							{ name: 'DrugFinance_Name', mapping: 'DrugFinance_Name' },
							{ name: 'DocumentUcStr_Price', mapping: 'DocumentUcStr_Price' },
							{ name: 'DocumentUcStr_PriceR', mapping: 'DocumentUcStr_PriceR' },
							{ name: 'DocumentUcStr_Ser', mapping: 'DocumentUcStr_Ser' }
						]),
						url: '/?c=Farmacy&m=loadDocumentUcStrList'
					}),
					tabIndex: TABINDEX_DUSREF + 1,
					tpl: new Ext.XTemplate(
						'<table cellpadding="0" cellspacing="0" style="width: 100%;"><tr style="font-family: tahoma; font-size: 10pt; font-weight: bold; text-align: center;">',
						'<td style="padding: 2px; width: 25%;">Медикамент</td>',
						'<td style="padding: 2px; width: 15%;">Цена (опт.)</td>',
						'<td style="padding: 2px; width: 15%;">Цена (розн.)</td>',
						'<td style="padding: 2px; width: 15%;">Остаток</td>',
						'<td style="padding: 2px; width: 15%;">Ист. финанс.</td>',
						'<td style="padding: 2px; width: 15%;">Серия</td>',
						'<tpl for="."><tr class="x-combo-list-item" style="white-space: normal; overflow: auto; text-overflow: clip;">',
						'<td style="padding: 2px;">{DocumentUcStr_Name}&nbsp;</td>',
						'<td style="padding: 2px; text-align: right;">{DocumentUcStr_Price}&nbsp;</td>',
						'<td style="padding: 2px; text-align: right;">{DocumentUcStr_PriceR}&nbsp;</td>',
						'<td style="padding: 2px; text-align: right;">{DocumentUcStr_Count}&nbsp;</td>',
						'<td style="padding: 2px;">{DrugFinance_Name}&nbsp;</td>',
						'<td style="padding: 2px;">{DocumentUcStr_Ser}&nbsp;</td>',
						'</tr></tpl>',
						'</table>'
					),
					valueField: 'DocumentUcStr_id',
					width: 430,
					xtype: 'swbaselocalcombo'
				}, {
					border: false,
					layout: 'column',
					items: [{
						border: false,
						layout: 'form',
						items: [{
							allowBlank: false,
							decimalPrecision: 3,
							disabled: true,
							fieldLabel: lang['ostatok_ed_uch'],
							name: 'DocumentUcStr_Count',
							tabIndex: TABINDEX_DUSREF + 2,
							width: 100,
							xtype: 'numberfield'
						}]
					}/*, {
						border: false,
						labelWidth: 150,
						layout: 'form',
						items: [{
							decimalPrecision: 3,
							disabled: true,
							fieldLabel: lang['ostatok_ed_doz'],
							name: 'DocumentUcStr_EdCount',
							tabIndex: TABINDEX_DUSREF + 9,
							width: 100,
							xtype: 'numberfield'
						}]
					}*/]
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
							decimalPrecision: 2,
							disabled: true,
							fieldLabel: lang['tsena_opt_bez_nds'],
							name: 'DocumentUcStr_Price',
							tabIndex: TABINDEX_DUSREF + 3,
							width: 100,
							xtype: 'numberfield'
						}]
					}, {
						border: false,
						labelWidth: 150,
						layout: 'form',
						items: [{
							allowBlank: false,
							allowDecimals: false,
							allowNegative: false,
							decimalPrecision: 2,
							disabled: true,
							fieldLabel: lang['tsena_rozn_s_nds'],
							name: 'DocumentUcStr_PriceR',
							tabIndex: TABINDEX_DUSREF + 4,
							width: 100,
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
							decimalPrecision: 2,
							disabled: true,
							fieldLabel: lang['kolichestvo_ed_uch'],
							listeners: {
								'change': function(field, newValue, oldValue) {
									var base_form = this.findById('DocumentUcStrRashEditForm').getForm();

									var price = base_form.findField('DocumentUcStr_Price').getValue();
									var price_r = base_form.findField('DocumentUcStr_PriceR').getValue();

									if ( price.toString().length > 0 && price_r.toString().length > 0 && newValue.toString().length > 0 ) {
										base_form.findField('DocumentUcStr_SumNdsR').maxValue = Number(price_r * newValue + 0.1).toFixed(2);
										base_form.findField('DocumentUcStr_SumNdsR').minValue = (price_r * newValue - 0.1 >= 0 ? Number(price_r * newValue - 0.1).toFixed(2) : 0);

										base_form.findField('DocumentUcStr_Sum').setValue(Number(price * newValue).toFixed(2));
										base_form.findField('DocumentUcStr_SumNdsR').setValue(Number(price_r * newValue).toFixed(2));
									}
									else {
										base_form.findField('DocumentUcStr_SumNdsR').maxValue = undefined;
										base_form.findField('DocumentUcStr_SumNdsR').minValue = undefined;

										base_form.findField('DocumentUcStr_Sum').setValue('');
										base_form.findField('DocumentUcStr_SumNdsR').setValue('');
									}
								}.createDelegate(this)
							},
							minValue: 0.01,
							name: 'DocumentUcStr_RashCount',
							tabIndex: TABINDEX_DUSREF + 5,
							width: 100,
							xtype: 'numberfield'
						}]
					}/*, {
						border: false,
						labelWidth: 150,
						layout: 'form',
						items: [{
							allowDecimals: false,
							allowNegative: false,
							decimalPrecision: 2,
							disabled: true,
							fieldLabel: lang['kolichestvo_ed_doz'],
							name: 'DocumentUcStr_RashEdCount',
							tabIndex: TABINDEX_DUSREF + 6,
							width: 100,
							xtype: 'numberfield'
						}]
					}*/]
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
							decimalPrecision: 2,
							disabled: true,
							fieldLabel: lang['summa_opt_bez_nds'],
							name: 'DocumentUcStr_Sum',
							tabIndex: TABINDEX_DUSREF + 7,
							width: 100,
							xtype: 'numberfield'
						}]
					}, {
						border: false,
						labelWidth: 150,
						layout: 'form',
						items: [{
							allowBlank: false,
							allowDecimals: false,
							allowNegative: false,
							decimalPrecision: 2,
							enableKeyEvents: true,
							fieldLabel: lang['summa_rozn_s_nds'],
							name: 'DocumentUcStr_SumNdsR',
							tabIndex: TABINDEX_DUSREF + 8,
							width: 100,
							xtype: 'numberfield'
						}]
					}]
				}],
				keys: [{
					alt: true,
					fn: function(inp, e) {
						switch ( e.getKey() ) {
							case Ext.EventObject.C:
								if ( this.action != 'view' ) {
									this.doSave();
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
				layout: 'form',
				reader: new Ext.data.JsonReader({
					success: function() { }
				}, [
					{ name: 'DocumentUcStr_id' }
				]),
				url: '/?c=Farmacy&m=loadDocumentUcStrRashEditForm'
			})]
		});
		sw.Promed.swDocumentUcStrRashEditWindow.superclass.initComponent.apply(this, arguments);
	},
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
	resizable: false,
	show: function() {
		sw.Promed.swDocumentUcStrRashEditWindow.superclass.show.apply(this, arguments);

		var base_form = this.findById('DocumentUcStrRashEditForm').getForm();

		this.center();
		base_form.reset();

		this.action = null;
		this.callback = Ext.emptyFn;
		this.onHide = Ext.emptyFn;

		base_form.findField('DocumentUcStr_oid').clearValue();
		base_form.findField('DocumentUcStr_oid').getStore().removeAll();

		base_form.findField('DocumentUcStr_SumNdsR').maxValue = undefined;
		base_form.findField('DocumentUcStr_SumNdsR').minValue = undefined;

		if ( !arguments[0] || !arguments[0].formParams ) {
			sw.swMsg.alert(lang['soobschenie'], lang['nevernyie_parametryi']);
			return false;
		}

		base_form.setValues(arguments[0].formParams);

		var recept_finance_Code = null;

		if ( arguments[0].action ) {
			this.action = arguments[0].action;
		}

		if ( arguments[0].callback ) {
			this.callback = arguments[0].callback;
		}

		if ( arguments[0].onHide ) {
			this.onHide = arguments[0].onHide;
		}

		if ( arguments[0].ReceptFinance_Code ) {
			recept_finance_Code = arguments[0].ReceptFinance_Code;
		}
		else {
			sw.swMsg.alert(lang['oshibka'], lang['ne_zadan_kod_tipa_finansirovaniya_retsepta'], function() { this.hide(); }.createDelegate(this) );
			return false;
		}

		var document_uc_str_combo = base_form.findField('DocumentUcStr_oid');

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT });
		loadMask.show();

		switch ( this.action ) {
			case 'add':
				this.setTitle(WND_FARM_DUSREFADD);
				this.enableEdit(true);

				document_uc_str_combo.getStore().load({
					callback: function() {
						loadMask.hide();
						document_uc_str_combo.focus(false, 250);
					}.createDelegate(this),
					params: {
						'Drug_id': base_form.findField('Drug_id').getValue(),
						'DrugMnn_id': base_form.findField('DrugMnn_id').getValue(),
						'DrugTorg_id': base_form.findField('DrugTorg_id').getValue(),
						'EvnRecept_otpDate': base_form.findField('EvnRecept_otpDate').getValue(),
						'mode': 'recept',
						'ReceptFinance_Code': recept_finance_Code
					}
				});
			break;

			case 'edit':
				this.setTitle(WND_FARM_DUSREFEDIT);
				this.enableEdit(true);

				document_uc_str_combo.getStore().load({
					callback: function() {
						loadMask.hide();
						document_uc_str_combo.setValue(document_uc_str_combo.getValue());
						document_uc_str_combo.fireEvent('change', document_uc_str_combo, document_uc_str_combo.getValue(), 0);
						document_uc_str_combo.focus(false, 250);
					}.createDelegate(this),
					params: {
						'Drug_id': base_form.findField('Drug_id').getValue(),
						'DrugMnn_id': base_form.findField('DrugMnn_id').getValue(),
						'DrugTorg_id': base_form.findField('DrugTorg_id').getValue(),
						'EvnRecept_otpDate': base_form.findField('EvnRecept_otpDate').getValue(),
						'mode': 'recept',
						'ReceptFinance_Code': recept_finance_Code
					}
				});
			break;

			case 'view':
				this.setTitle(WND_FARM_DUSREFVIEW);
				this.enableEdit(false);

				loadMask.hide();

				this.buttons[this.buttons.length - 1].focus();
			break;

			default:
				sw.swMsg.alert(lang['oshibka'], lang['neverno_zadan_rejim_otkryitiya_formyi'], function() { this.hide(); }.createDelegate(this) );
			break;
		}

		base_form.clearInvalid();
	},
	width: 650
});