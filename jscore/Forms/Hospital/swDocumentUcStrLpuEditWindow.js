/**
* swDocumentUcStrLpuEditWindow - окно редактирования строки учетного документа в аптеке (приход-расход).
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
* @comment      Префикс для id компонентов DUSEF (DocumentUcStrLpuEditForm)
*
*
* @input data: action - действие (add, edit, view)
*              documentUcStrMode - статус (expenditure, income)
*/

sw.Promed.swDocumentUcStrLpuEditWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: true,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	documentUcStrMode: null,
	doSave: function() {
		if ( this.action != 'add' && this.action != 'edit' ) {
			return false;
		}
		var base_form = this.findById('DocumentUcStrLpuEditForm').getForm();
		var form = this;
		if (!base_form.isValid()) 
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
		
		
		
		
		var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Подождите, идет сохранение..." });
		loadMask.show();
		
		if (!base_form.findField('DrugLabResult_Name').getValue())
			base_form.findField('DrugLabResult_Name').setValue(base_form.findField('DrugLabResult_Name').getRawValue());
		
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
			}.createDelegate(this),
			params: {
				'DocumentUcStr_RashEdCount': base_form.findField('DocumentUcStr_RashEdCount').getValue(),
				'DocumentUcStr_SumR': base_form.findField('DocumentUcStr_SumR').getValue()
			},
			success: function(result_form, action) {
				loadMask.hide();

				this.callback(this.owner,action.result.DocumentUcStr_id);
			}.createDelegate(this)
		});
	},
	draggable: true,
	enableEdit: function(enable) {
		var base_form = this.findById('DocumentUcStrLpuEditForm').getForm();

		if ( enable ) {
			base_form.findField('Drug_id').enable();
			base_form.findField('DocumentUcStr_RashCount').enable();

			switch ( this.documentUcStrMode ) {
				case 'expenditure':
					base_form.findField('DocumentUcStr_godnDate').disable();
					base_form.findField('DocumentUcStr_oid').enable();
					base_form.findField('DocumentUcStr_PriceR').disable();
					base_form.findField('DocumentUcStr_Ser').disable();
					base_form.findField('DocumentUcStr_SertNum').disable();
					base_form.findField('DrugLabResult_Name').disable();

				break;

				case 'income':
					base_form.findField('DocumentUcStr_godnDate').enable();
					base_form.findField('DocumentUcStr_oid').disable();
					base_form.findField('DocumentUcStr_PriceR').enable();
					base_form.findField('DocumentUcStr_Ser').enable();
					base_form.findField('DocumentUcStr_SertNum').enable();
					base_form.findField('DrugLabResult_Name').enable();
				break;

				default:
					sw.swMsg.alert(lang['oshibka'], lang['nevernyiy_parametr_rejim_otkryitiya_formyi'], function() { this.hide(); }.createDelegate(this) );
				break;
			}
		}
		else {
			base_form.findField('Drug_id').disable();
			base_form.findField('DocumentUcStr_godnDate').disable();
			//base_form.findField('DocumentUcStr_IsLab').disable();
			base_form.findField('DocumentUcStr_oid').disable();
			base_form.findField('DocumentUcStr_PriceR').disable();
			base_form.findField('DocumentUcStr_RashCount').disable();
			base_form.findField('DocumentUcStr_SertNum').disable();
			base_form.findField('DocumentUcStr_Ser').disable();
			base_form.findField('DrugLabResult_Name').disable();
		}
	},
	clearValues: function(enable) {
		var base_form = this.findById('DocumentUcStrLpuEditForm').getForm();
		base_form.findField('Drug_id').setValue(null);
		base_form.findField('DocumentUcStr_godnDate').setValue(null);
		base_form.findField('DocumentUcStr_id').setValue(null);
		base_form.findField('DocumentUcStr_oid').setValue(null);
		base_form.findField('DocumentUcStr_PriceR').setValue(null);
		base_form.findField('DocumentUcStr_Ser').setValue(null);
		base_form.findField('DocumentUcStr_SertNum').setValue(null);
		base_form.findField('DocumentUcStr_Count').setValue(null);
		base_form.findField('DocumentUcStr_EdCount').setValue(null);
		base_form.findField('DocumentUcStr_RashCount').setValue(null);
		base_form.findField('DocumentUcStr_SumR').setValue(null);
		base_form.findField('DrugLabResult_Name').setValue(null);
	},
	
	firstTabIndex: 15300,
	id: 'DocumentUcStrLpuEditWindow',
	initComponent: function() {
		Ext.apply(this, {
			buttons: [{
				handler: function() {
					if ( this.action != 'view' ) {
						this.doSave();
					}
				}.createDelegate(this),
				iconCls: 'save16',
				tabIndex: TABINDEX_DUSEF + 21,
				text: BTN_FRMSAVE,
				tooltip: lang['sohranit_vvedennyie_dannyie']
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
						this.findById('DocumentUcStrLpuEditForm').getForm().findField('Drug_id').focus(true);
					}
				}.createDelegate(this),
				tabIndex: TABINDEX_DUSEF + 22,
				text: BTN_FRMCANCEL,
				tooltip: lang['zakryit_okno']
			}],
			items: [ new Ext.form.FormPanel({
				autoScroll: true,
				bodyStyle: 'padding: 0.5em;',
				border: false,
				frame: true,
				id: 'DocumentUcStrLpuEditForm',
				items: [{
					name: 'DocumentUcStr_id',
					value: null,
					xtype: 'hidden'
				}, {
					name: 'DocumentUc_id',
					value: null,
					xtype: 'hidden'
				},
				{
					name: 'DocumentUcStr_PriceRN',
					value: 0,
					xtype: 'hidden'
				},
				
				{
					allowBlank: false,
					displayField: 'Drug_Name',
					enableKeyEvents: true,
					fieldLabel: lang['medikament'],
					forceSelection: true,
					hiddenName: 'Drug_id',
					//listWidth: 800,
					listeners: {
						'change': function(combo, newValue, oldValue) {
							var base_form = this.findById('DocumentUcStrLpuEditForm').getForm();
							var record = combo.getStore().getById(newValue);

							base_form.findField('Drug_Fas').setRawValue('');
							base_form.findField('DrugForm_Name').setRawValue('');
							base_form.findField('DrugUnit_Name').setRawValue('');

							if ( !record ) {
								base_form.findField('DocumentUcStr_RashCount').fireEvent('change', base_form.findField('DocumentUcStr_RashCount'), base_form.findField('DocumentUcStr_RashCount').getValue());
								return false;
							}

							base_form.findField('Drug_Fas').setRawValue(record.get('Drug_Fas') ? record.get('Drug_Fas') : 1);
							base_form.findField('DrugForm_Name').setRawValue(record.get('DrugForm_Name'));
							base_form.findField('DrugUnit_Name').setRawValue(record.get('DrugUnit_Name'));

							base_form.findField('DocumentUcStr_RashCount').fireEvent('change', base_form.findField('DocumentUcStr_RashCount'), base_form.findField('DocumentUcStr_RashCount').getValue());

							if ( this.documentUcStrMode == 'expenditure' ) {
								var document_uc_str_combo = base_form.findField('DocumentUcStr_oid');

								document_uc_str_combo.clearValue();
								document_uc_str_combo.getStore().removeAll();
								document_uc_str_combo.lastQuery = '';

								if ( newValue > 0 ) {
									document_uc_str_combo.getStore().load({
										params: {
											Drug_id: newValue,
											DrugMnn_id: record.get('DrugMnn_id'),
											mode: 'default'
										}
									});
								}
								else {
									document_uc_str_combo.fireEvent('change', document_uc_str_combo, null, 1);
								}
							}

							return true;
						}.createDelegate(this)
					},
					loadingText: lang['idet_poisk'],
					minChars: 1,
					minLength: 1,
					minLengthText: lang['pole_doljno_byit_zapolneno'],
					mode: 'remote',
					resizable: true,
					selectOnFocus: true,
					store: new Ext.data.Store({
						autoLoad: false,
						reader: new Ext.data.JsonReader({
							id: 'Drug_id',
							sortInfo: {
								field: 'Drug_Name'
							}
						}, [
							{ name: 'Drug_Fas', mapping: 'Drug_Fas' },
							{ name: 'Drug_id', mapping: 'Drug_id' },
							{ name: 'Drug_Name', mapping: 'Drug_Name' },
							{ name: 'DrugMnn_id', mapping: 'DrugMnn_id' },
							{ name: 'DrugForm_Name', mapping: 'DrugForm_Name' },
							{ name: 'DrugUnit_Name', mapping: 'DrugUnit_Name' }
						]),
						url: '/?c=Farmacy&m=loadDrugList'
					}),
					tabIndex: TABINDEX_DUSEF + 1,
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
							name: 'DrugUnit_Name',
							tabIndex: TABINDEX_DUSEF + 2,
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
							name: 'DrugForm_Name',
							tabIndex: TABINDEX_DUSEF + 3,
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
							tabIndex: TABINDEX_DUSEF + 4,
							width: 70,
							xtype: 'numberfield'
						}]
					}]
				}, {
					allowBlank: true,
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
						'change': function(combo, newValue, oldValue) 
						{
							var base_form = this.findById('DocumentUcStrLpuEditForm').getForm();
							var record = combo.getStore().getById(newValue);
							base_form.findField('DocumentUcStr_Count').setValue('');
							base_form.findField('DocumentUcStr_EdCount').setValue('');
							base_form.findField('DocumentUcStr_godnDate').setValue('');
							base_form.findField('DocumentUcStr_RashCount').fireEvent('change', base_form.findField('DocumentUcStr_RashCount'), '', 1);
							
							if ( record ) {
								base_form.findField('DocumentUcStr_PriceR').setValue(record.get('DocumentUcStr_Price'));
								base_form.findField('DocumentUcStr_Count').setValue(record.get('DocumentUcStr_Count'));
								base_form.findField('DocumentUcStr_EdCount').setValue(record.get('DocumentUcStr_EdCount'));
								base_form.findField('DocumentUcStr_godnDate').setValue(record.get('DocumentUcStr_godnDate'));
								
								base_form.findField('DocumentUcStr_NZU').setValue(record.get('DocumentUcStr_NZU'));

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
							{ name: 'DocumentUcStr_godnDate', mapping: 'DocumentUcStr_godnDate' },
							{ name: 'DocumentUcStr_Price', mapping: 'DocumentUcStr_Price' },
							{ name: 'PrepSeries_IsDefect', mapping: 'PrepSeries_IsDefect' }
						]),
						url: '/?c=Farmacy&m=loadDocumentUcStrList'
					}),
					tabIndex: TABINDEX_DUSEF + 5,
					tpl: new Ext.XTemplate(
						'<table cellpadding="0" cellspacing="0" style="width: 100%;"><tr style="font-family: tahoma; font-size: 10pt; font-weight: bold;">',
						'<td style="padding: 2px; width: 15%;">Срок годности</td>',
						'<td style="padding: 2px; width: 15%;">Цена</td>',
						'<td style="padding: 2px; width: 15%;">Остаток</td>',
						'<td style="padding: 2px; width: 40%;">Источник финансирования</td>',
						'<td style="padding: 2px; width: 15%;">Серия</td></tr>',
						'<tpl for="."><tr class="x-combo-list-item" style="{[values.PrepSeries_IsDefect==2?"color: red;":""]}">',
						'<td style="padding: 2px;">test text {DocumentUcStr_godnDate}&nbsp;</td>',
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
							tabIndex: TABINDEX_DUSEF + 6,
							width: 100,
							xtype: 'numberfield'
						}]
					}, {
						border: false,
						labelWidth: 130,
						layout: 'form',
						items: [{
							disabled: true,
							fieldLabel: lang['ostatok_ed_doz'],
							name: 'DocumentUcStr_EdCount',
							tabIndex: TABINDEX_DUSEF + 7,
							width: 100,
							xtype: 'numberfield'
						}]
					}]
				}, /*{
					fieldLabel: lang['otdel'],
					allowBlank: false,
					hiddenName: 'DrugFinance_id',
					tabIndex: TABINDEX_DUSEF + 8,
					width: 300,
					xtype: 'swdrugfinancecombo'
				},*/ {
					border: false,
					layout: 'column',
					items: [{
						border: false,
						layout: 'form',
						items: [{
							allowBlank: false,
							allowDecimals: true,
							allowNegative: false,
							decimalPrecision: 3, 
							fieldLabel: lang['kolichestvo_ed_uch'],
							listeners: {
								'change': function(field, newValue, oldValue) 
								{
									var base_form = this.findById('DocumentUcStrLpuEditForm').getForm();
									var price = base_form.findField('DocumentUcStr_PriceR').getValue();
									if ( this.documentUcStrMode == 'expenditure' ) 
									{
										var max_value = base_form.findField('DocumentUcStr_Count').getValue();
										if ( newValue > max_value ) 
										{
											newValue = max_value;
											field.setValue(max_value);
										}
									}
									
									if (base_form.findField('Drug_Fas').getValue()>0)
									{
										base_form.findField('DocumentUcStr_RashEdCount').setValue(base_form.findField('Drug_Fas').getValue()*newValue);
									}
									if ( price.toString().length > 0 && newValue.toString().length > 0 )
									{
										base_form.findField('DocumentUcStr_SumR').setValue(Number(price * newValue).toFixed(2));
									}
									else 
									{
										base_form.findField('DocumentUcStr_SumR').setValue('');
									}
								}.createDelegate(this)
							},
							minValue: 0,
							name: 'DocumentUcStr_RashCount',
							tabIndex: TABINDEX_DUSEF + 8,
							width: 100,
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
							disabled: true,
							decimalPrecision: 3,
							fieldLabel: lang['kol-vo_ed_doz'],
							name: 'DocumentUcStr_RashEdCount',
							tabIndex: TABINDEX_DUSEF + 9,
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
							disabled: true,
							fieldLabel: lang['tsena'],
							listeners: {
								'change': function(field, newValue, oldValue) {
									var base_form = this.findById('DocumentUcStrLpuEditForm').getForm();
									var count = base_form.findField('DocumentUcStr_RashCount').getValue();
									base_form.findField('DocumentUcStr_SumR').setValue(count * newValue);
								}.createDelegate(this)
							},
							name: 'DocumentUcStr_PriceR',
							tabIndex: TABINDEX_DUSEF + 13,
							width: 100,
							xtype: 'numberfield'
						}]
					}, {
						border: false,
						labelWidth: 130,
						layout: 'form',
						items: [{
							allowBlank: false,
							allowDecimals: true,
							allowNegative: false,
							disabled: true,
							fieldLabel: lang['summa'],
							name: 'DocumentUcStr_SumR',
							tabIndex: TABINDEX_DUSEF + 14,
							width: 100,
							xtype: 'numberfield'
						}]
					}]
				}, {
					fieldLabel: lang['srok_godnosti'],
					format: 'd.m.Y',
					name: 'DocumentUcStr_godnDate',
					plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
					tabIndex: TABINDEX_DUSEF + 15,
					width: 100,
					xtype: 'swdatefield'
				}, 
				{
					border: false,
					layout: 'column',
					items: [{
						border: false,
						layout: 'form',
						labelWidth: 130,
						items: [{
							fieldLabel: lang['seriya'],
							allowBlank: false,
							name: 'DocumentUcStr_Ser',
							width: 100,
							tabIndex: TABINDEX_DUSEF + 16,
							xtype: 'textfield'
						}]
					}]
				},
				{
					fieldLabel: lang['№_sertifikata'],
					name: 'DocumentUcStr_SertNum',
					width: 100,
					tabIndex: TABINDEX_DUSEF + 17,
					xtype: 'textfield'
				},
				{
					width: 450,
					fieldLabel: lang['rez_lab_issl'],
					name: 'DrugLabResult_Name',
					tabIndex: TABINDEX_DUSEF + 19,
					xtype: 'swdruglabresultcombo',
					allowBlank: true,
					forceSelection: false
				}],
				labelAlign: 'right',
				labelWidth: 130,
				reader: new Ext.data.JsonReader({
					success: Ext.emptyFn
				}, [
					{ name: 'DocumentUcStr_id' },
					{ name: 'Drug_id' },
					{ name: 'DocumentUcStr_Nds' },
					{ name: 'DocumentUcStr_PriceR' },
					{ name: 'DrugNds_id' },
					{ name: 'DocumentUcStr_Count' },
					{ name: 'DocumentUcStr_EdCount' },
					{ name: 'DocumentUcStr_Ser' },
					{ name: 'DocumentUcStr_SertNum' },
					//{ name: 'DocumentUcStr_IsLab' },
					{ name: 'DocumentUcStr_RashCount' },
					{ name: 'DocumentUcStr_SumR' },
					{ name: 'DocumentUcStr_godnDate' },
					{ name: 'DocumentUcStr_NZU' },
					{ name: 'DrugLabResult_Name' }
					
				]),
				region: 'center',
				trackResetOnLoad: true,
				url: '/?c=Farmacy&m=saveDocumentUcStr'
			})]
		});
		sw.Promed.swDocumentUcStrLpuEditWindow.superclass.initComponent.apply(this, arguments);
	},
	keys: [{
		alt: true,
		fn: function(inp, e) {
			var current_window = Ext.getCmp('DocumentUcStrLpuEditWindow');

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
	listeners: 
	{
		beforeshow: function()
		{
			// Никого не жалко, никого!!!
		},
		hide: function() 
		{
			this.onHide();
		}
	},
	modal: true,
	onHide: Ext.emptyFn,
	plain: true,
	resizable: false,
	show: function() {
		sw.Promed.swDocumentUcStrLpuEditWindow.superclass.show.apply(this, arguments);

		var base_form = this.findById('DocumentUcStrLpuEditForm').getForm();
		this.findById('DocumentUcStrLpuEditForm').getForm().reset();
		var form = this;
		
		this.action = null;
		this.callback = Ext.emptyFn;
		this.documentUcStrMode = 'income';
		this.onHide = Ext.emptyFn;
		this.center();
		
		if ( !arguments[0] ) {
			sw.swMsg.alert(lang['oshibka'], lang['otsutstvuyut_neobhodimyie_parametryi'], function() { this.hide(); }.createDelegate(this) );
			return false;
		}
		//
		this.clearValues();
		base_form.setValues(arguments[0]);
		base_form.isFirst = 1;
		
		base_form.findField('DocumentUcStr_oid').getStore().removeAll();
		base_form.findField('Drug_id').getStore().removeAll();

		if ( arguments[0].action ) {
			this.action = arguments[0].action;
		}

		if ( arguments[0].callback ) {
			this.callback = arguments[0].callback;
		}
		
		if ( arguments[0].owner ) {
			this.owner = arguments[0].owner;
		}
		
		if ( arguments[0].Contragent_id ) {
			this.Contragent_id = arguments[0].Contragent_id;
		}
		
		if ( arguments[0].mode ) {
			this.documentUcStrMode = arguments[0].mode;
		}
		//log(arguments[0]);
		
		if ( arguments[0].onHide ) {
			this.onHide = arguments[0].onHide;
		}

		base_form.findField('Drug_id').getStore().baseParams = {
			mode: this.documentUcStrMode
		}
		
		var loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT });
		loadMask.show();
		base_form.findField('DocumentUcStr_PriceRN').setValue(0);
		
		if (this.action!='add') {
			this.findById('DocumentUcStrLpuEditForm').getForm().load(
			{
				params: 
				{
					DocumentUcStr_id: base_form.findField('DocumentUcStr_id').getValue()
				},
				failure: function() 
				{
					sw.swMsg.show(
					{
						buttons: Ext.Msg.OK,
						fn: function() 
						{
							form.hide();
						},
						icon: Ext.Msg.ERROR,
						msg: lang['oshibka_zaprosa_k_serveru_poprobuyte_povtorit_operatsiyu'],
						title: lang['oshibka']
					});
				},
				success: function() 
				{
					// Что надо сделать при чтении
					base_form.findField('Drug_id').getStore().load({
						params: {
							mode: form.documentUcStrMode,
							Contragent_id: form.Contragent_id,
							Drug_id: base_form.findField('Drug_id').getValue()
						},
						callback: function () {
							base_form.findField('Drug_id').setValue(base_form.findField('Drug_id').getValue());
							base_form.findField('Drug_id').fireEvent('change', base_form.findField('Drug_id'), base_form.findField('Drug_id').getValue());
							if (this.action!='view') 
							{
								base_form.findField('Drug_id').focus(true, 250);
							}
						}
					});
					Contragent_id
					base_form.findField('DrugLabResult_Name').getStore().removeAll();
					base_form.findField('DrugLabResult_Name').getStore().load(
					{
						callback: function() 
						{
							base_form.findField('DrugLabResult_Name').setValue(base_form.findField('DrugLabResult_Name').getValue());
						}
					});
					base_form.findField('DocumentUcStr_PriceRN').setValue(base_form.findField('DocumentUcStr_PriceRN').getValue());
				},
				url: '/?c=Farmacy&m=loadDocumentUcStrView'
			});
		}
		else 
		{
			base_form.findField('Drug_id').focus(true, 250);
			base_form.findField('DrugLabResult_Name').getStore().removeAll();
			base_form.findField('DrugLabResult_Name').getStore().load();
		}
		
		switch ( this.action ) {
			case 'add':
				this.enableEdit(true);
				this.setTitle(lang['stroka_dokumenta_dobavlenie']);

				loadMask.hide();
				base_form.clearInvalid();

			break;

			case 'edit':
				this.enableEdit(true);
				this.setTitle(lang['stroka_dokumenta_redaktirovanie']);

				loadMask.hide();
				base_form.clearInvalid();

			break;

			case 'view':
				this.enableEdit(false);
				this.setTitle(lang['stroka_dokumenta_prosmotr']);

				loadMask.hide();
				base_form.clearInvalid();

				this.buttons[this.buttons.length - 1].focus();
			break;
		}
	},
	split: true,
	width: 700
});
