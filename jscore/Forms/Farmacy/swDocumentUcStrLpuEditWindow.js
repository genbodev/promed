/**
* swDocumentUcStrLpuEditWindow - окно редактирования строки учетного документа в ЛПУ (приход-расход).
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
/*NO PARSE JSON*/
sw.Promed.swDocumentUcStrLpuEditWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: true,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	documentUcStrMode: null,
	codeRefresh: true,
	objectName: 'swDocumentUcStrLpuEditWindow',
	objectSrc: '/jscore/Forms/Farmacy/swDocumentUcStrLpuEditWindow.js',
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
					form.findById('DocumentUcStrLpuEditForm').getFirstInvalidEl().focus(true);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
		
		
		
		
		var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Подождите, идет сохранение..." });
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
			}.createDelegate(this),
			params: {
				DocumentUcStr_EdCount: base_form.findField('DocumentUcStr_RashEdCount').getValue(),
				DocumentUcStr_Count: base_form.findField('DocumentUcStr_RashCount').getValue(),
				DocumentUcStr_SumR: base_form.findField('DocumentUcStr_SumR').getValue(),
				DocumentUcStr_PriceR: base_form.findField('DocumentUcStr_PriceR').getValue(),
				DrugFinance_id: base_form.findField('DrugFinance_id').getValue(),
				DocumentUcStr_godnDate: (base_form.findField('DocumentUcStr_godnDate').getValue())?base_form.findField('DocumentUcStr_godnDate').getValue().dateFormat('d.m.Y'):'',
				DocumentUcStr_Ser: base_form.findField('DocumentUcStr_Ser').getValue(),
				DocumentUcStr_CertNum: base_form.findField('DocumentUcStr_CertNum').getValue(),
				DocumentUcStr_IsLab: base_form.findField('DocumentUcStr_IsLab').getValue()
			},
			success: function(result_form, action) {
				loadMask.hide();

				this.callback(this.owner,action.result.DocumentUcStr_id);
			}.createDelegate(this)
		});
	},
	draggable: true,
	enableEdit: function(enable) {
		var form = this;
		var base_form = this.findById('DocumentUcStrLpuEditForm').getForm();

		if ( enable ) {
			base_form.findField('Drug_id').enable();
			base_form.findField('DrugPrepFas_id').enable();
			base_form.findField('DocumentUcStr_RashCount').enable();

			switch ( this.documentUcStrMode ) {
				case 'expenditure':
					base_form.findField('DocumentUcStr_oid').enable(); // партия
					base_form.findField('DocumentUcStr_PriceR').disable(); // цена 
					base_form.findField('DocumentUcStr_godnDate').disable(); // срок годности
					//base_form.findField('DocumentUcStr_godnDate').setAllowBlank(false);
					base_form.findField('DrugFinance_id').disable(); // источник финансирования 
					base_form.findField('DrugFinance_id').setAllowBlank(true);
					base_form.findField('DocumentUcStr_Ser').disable(); // серия 
					base_form.findField('DocumentUcStr_CertNum').disable(); // номер серт
					base_form.findField('DocumentUcStr_IsLab').disable(); // результат иссл.
					base_form.findField('DocumentUcStr_RashEdCount').disable(); // кол-во
				break;

				case 'income': // от аптеки или организации 
					base_form.findField('DocumentUcStr_oid').disable();
					base_form.findField('DocumentUcStr_PriceR').enable();
					base_form.findField('DocumentUcStr_godnDate').enable();
					//base_form.findField('DocumentUcStr_godnDate').setAllowBlank(false);
					base_form.findField('DrugFinance_id').disable(); // источник финансирования
					base_form.findField('DrugFinance_id').setAllowBlank(false);
					base_form.findField('DocumentUcStr_Ser').enable();
					base_form.findField('DocumentUcStr_CertNum').enable();
					base_form.findField('DocumentUcStr_IsLab').enable();
					base_form.findField('DocumentUcStr_RashEdCount').enable(); 
					base_form.findField('DrugPrepFas_id').enable(); 
				break;

				default:
					sw.swMsg.alert(lang['oshibka'], lang['nevernyiy_parametr_rejim_otkryitiya_formyi'], function() { this.hide(); }.createDelegate(this) );
				break;
			}
			form.buttons[0].enable();
		}
		else {
			base_form.findField('Drug_id').disable();
			base_form.findField('DocumentUcStr_godnDate').disable();
			base_form.findField('DocumentUcStr_IsLab').disable();
			base_form.findField('DocumentUcStr_oid').disable();
			base_form.findField('DocumentUcStr_PriceR').disable();
			base_form.findField('DocumentUcStr_RashCount').disable();
			base_form.findField('DocumentUcStr_CertNum').disable();
			base_form.findField('DocumentUcStr_Ser').disable();
			base_form.findField('DocumentUcStr_RashEdCount').disable();
			base_form.findField('DrugFinance_id').disable();
			base_form.findField('DrugPrepFas_id').disable();
			form.buttons[0].disable();
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
		base_form.findField('DocumentUcStr_CertNum').setValue(null);
		base_form.findField('DocumentUcStr_Count').setValue(null);
		base_form.findField('DocumentUcStr_EdCount').setValue(null);
		base_form.findField('DocumentUcStr_RashCount').setValue(null);
		base_form.findField('DocumentUcStr_SumR').setValue(null);
		base_form.findField('DocumentUcStr_IsLab').setValue(null);
		base_form.findField('DrugFinance_id').setValue(null);
		base_form.findField('WhsDocumentCostItemType_id').setValue(null);
		base_form.findField('DrugPrepFas_id').setValue(null);
		
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
					name: 'DrugDeleted',
					value: null,
					xtype: 'hidden'
				},
				{
					name: 'DocumentUcStr_PriceRN',
					value: 0,
					xtype: 'hidden'
				},
				{ // Первый комбобокс (медикамент)
					hiddenName: 'DrugPrepFas_id',
					anchor: null,
					width: 500,
					/*
					listeners: 
					{
						'change': function(combo, newValue, oldValue) {
							//
						}.createDelegate(this)
					},/*/
					tabIndex: TABINDEX_DUSEF + 1,
					xtype: 'swdrugprepcombo'
				},
				{ // второй комбобокс (упаковка)
					hiddenName: 'Drug_id',
					anchor: null,
					width:500,
					listeners: 
					{
						'change': function(combo, newValue, oldValue) {
							var base_form = this.findById('DocumentUcStrLpuEditForm').getForm();
							var fw = this;
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
								
								var usc = document_uc_str_combo.getValue();
								document_uc_str_combo.clearValue();
								document_uc_str_combo.getStore().removeAll();
								document_uc_str_combo.lastQuery = '';
								if ( newValue > 0 ) {
									document_uc_str_combo.getStore().load({
										params: 
										{
											Drug_id: newValue,
											Contragent_id: fw.Contragent_id,
											DrugMnn_id: record.get('DrugMnn_id'),
											DocumentUc_id: base_form.findField('DocumentUc_id').getValue(),
											DocumentUcStr_id: base_form.findField('DocumentUcStr_id').getValue(),
											DrugFinance_id: base_form.findField('DrugFinance_id').getValue(),
											WhsDocumentCostItemType_id: base_form.findField('WhsDocumentCostItemType_id').getValue(),
											mode: 'default'
										},
										callback: function()
										{
											if (usc>0)
											{
												document_uc_str_combo.setValue(usc);
												document_uc_str_combo.fireEvent('change', document_uc_str_combo, document_uc_str_combo.getValue());
											}
										}
									});
								}
								else {
									document_uc_str_combo.fireEvent('change', document_uc_str_combo, null, 1);
								}
							}
							/*
							var DrugPrepFasCombo = base_form.findField('DrugPrepFas_id');
							//log((DrugPrepFasCombo.getValue()=='' && (newValue > 0)));
							if (DrugPrepFasCombo.getValue()=='' && (newValue > 0))
							{
								DrugPrepFasCombo.getStore().load(
								{
									params: {Drug_id: newValue},
									callback: function()
									{
										//DrugPrepFasCombo.hasFocus = true;
										if (DrugPrepFasCombo.getStore().getCount()>0)
										{
											DrugPrepFasCombo.setValue(DrugPrepFasCombo.getStore().getAt(0).get('DrugPrepFas_id'));
											//DrugPrepFasCombo.fireEvent('change', DrugPrepFasCombo, DrugPrepFasCombo.getValue());
											//DrugPrepFasCombo.collapse();
											//DrugPrepFasCombo.select(0);
										}
									}
								});
							}
							*/
							return true;
						}.createDelegate(this)
					},
					tabIndex: TABINDEX_DUSEF + 1,
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
							tabIndex: TABINDEX_DUSEF + 2,
							width: 110,
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
							tabIndex: TABINDEX_DUSEF + 3,
							width: 120,
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
							width: 60,
							xtype: 'numberfield'
						}]
					}]
				}, {
					allowBlank: false,
					displayField: 'DocumentUcStr_Name',
					enableKeyEvents: true,
					fieldLabel: lang['partiya'],
					forceSelection: true,
					hiddenName: 'DocumentUcStr_oid',
					listWidth: 700,
					listeners: {
						'beforeselect': function() {
							// this.findById('EREF_DrugCombo').lastQuery = '';
							return true;
						}.createDelegate(this),
						'change': function(combo, newValue, oldValue) 
						{
							var base_form = this.findById('DocumentUcStrLpuEditForm').getForm();
							var record = combo.getStore().getById(newValue);
							base_form.findField('DocumentUcStr_Count').setValue(null);
							base_form.findField('DocumentUcStr_EdCount').setValue(null);
							base_form.findField('DocumentUcStr_RashEdCount').setValue(null);
							base_form.findField('DocumentUcStr_RashEdCount').setDisabled(true);
							base_form.findField('DocumentUcStr_CertNum').setValue(null);
							base_form.findField('DocumentUcStr_IsLab').setValue(null);
							base_form.findField('DocumentUcStr_Ser').setValue(null);
							base_form.findField('DocumentUcStr_godnDate').setValue(null);
							base_form.findField('DrugFinance_id').setDisabled(true);
							base_form.findField('DocumentUcStr_RashCount').fireEvent('change', base_form.findField('DocumentUcStr_RashCount'), '', 1);
							if ( record ) {
								base_form.findField('DocumentUcStr_RashEdCount').setDisabled(base_form.findField('DocumentUcStr_EdCount').disabled);
								base_form.findField('DocumentUcStr_PriceR').setValue(record.get('DocumentUcStr_PriceR'));
								base_form.findField('DocumentUcStr_Count').setValue(record.get('DocumentUcStr_Count'));
								base_form.findField('DrugFinance_id').setValue(record.get('DrugFinance_id'));
								base_form.findField('DocumentUcStr_EdCount').setValue((record.get('DocumentUcStr_Count')*base_form.findField('Drug_Fas').getValue()).toFixed(4));
								base_form.findField('DocumentUcStr_CertNum').setValue(record.get('DocumentUcStr_CertNum'));
								base_form.findField('DocumentUcStr_IsLab').setValue(record.get('DocumentUcStr_IsLab'));
								base_form.findField('DocumentUcStr_godnDate').setValue(record.get('DocumentUcStr_godnDate'));
								base_form.findField('DocumentUcStr_Ser').setValue(record.get('DocumentUcStr_Ser'));
								base_form.findField('DocumentUcStr_RashCount').fireEvent('change', base_form.findField('DocumentUcStr_RashCount'), base_form.findField('DocumentUcStr_RashCount').getValue(), 0);
							}
							else
							{
								combo.setValue(null);
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
						reader: new Ext.data.JsonReader(
						{
							id: 'DocumentUcStr_id'
						}, [
							{ name: 'DocumentUcStr_id', mapping: 'DocumentUcStr_id' },
							{ name: 'DocumentUcStr_Name', mapping: 'DocumentUcStr_Name' },
							{ name: 'DrugFinance_id', mapping: 'DrugFinance_id' },
							{ name: 'DrugFinance_Name', mapping: 'DrugFinance_Name' },
							{ name: 'WhsDocumentCostItemType_id', mapping: 'WhsDocumentCostItemType_id' },
							{ name: 'WhsDocumentCostItemType_Name', mapping: 'WhsDocumentCostItemType_Name' },
							{ name: 'DocumentUcStr_Count', mapping: 'DocumentUcStr_Count' },
							{ name: 'DocumentUcStr_EdCount', mapping: 'DocumentUcStr_EdCount' },
							{ name: 'DocumentUcStr_CertNum', mapping: 'DocumentUcStr_CertNum' },
							{ name: 'DocumentUcStr_IsLab', mapping: 'DocumentUcStr_IsLab' },
							{ name: 'DocumentUcStr_Ser', mapping: 'DocumentUcStr_Ser' },
							{ name: 'DocumentUcStr_godnDate', mapping: 'DocumentUcStr_godnDate' },
							{ name: 'DocumentUcStr_PriceR', mapping: 'DocumentUcStr_PriceR' },
							{ name: 'PrepSeries_IsDefect', mapping: 'PrepSeries_IsDefect' }
						]),
						url: '/?c=Farmacy&m=loadDocumentUcStrList'
					}),
					tabIndex: TABINDEX_DUSEF + 5,
					tpl: new Ext.XTemplate(
						'<table cellpadding="0" cellspacing="0" style="width: 100%;"><tr style="font-family: tahoma; font-size: 8pt; font-weight: bold;">',
						'<td style="padding: 2px; width: 15%;">Срок годности</td>',
						'<td style="padding: 2px; width: 10%;">Цена</td>',
						'<td style="padding: 2px; width: 10%;">Остаток</td>',
						'<td style="padding: 2px; width: 30%;">Источник финансирования</td>',
						'<td style="padding: 2px; width: 25%;">Статья расхода</td>',
						'<td style="padding: 2px; width: 10%;">Серия</td></tr>',
						'<tpl for="."><tr class="x-combo-list-item" style="font-family: tahoma; font-size: 8pt; {[values.PrepSeries_IsDefect==2?"color: red;":""]}">',
						'<td style="padding: 2px;">{DocumentUcStr_godnDate}&nbsp;</td>',
						'<td style="padding: 2px;">{DocumentUcStr_PriceR}&nbsp;</td>',
						'<td style="padding: 2px;">{DocumentUcStr_Count}&nbsp;</td>',
						'<td style="padding: 2px;">{DrugFinance_Name}&nbsp;</td>',
						'<td style="padding: 2px;">{WhsDocumentCostItemType_Name}&nbsp;</td>',
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
							decimalPrecision: 4, 
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
				}, {
					fieldLabel: lang['istochnik_finans'],
					allowBlank: false,
					hiddenName: 'DrugFinance_id',
					tabIndex: TABINDEX_DUSEF + 8,
					width: 335,
					xtype: 'swdrugfinancecombo'
				}, {
					fieldLabel: lang['statya_rashoda'],
					allowBlank: false,
					hiddenName: 'WhsDocumentCostItemType_id',
					tabIndex: TABINDEX_DUSEF + 8,
					width: 335,
					xtype: 'swwhsdocumentcostitemtypecombo',
					disabled: true
				},
				{
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
								'change': function(field, newValue, oldValue) 
								{
									this.setKolvo(field, newValue, false);
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
						items: 
						[{
							allowBlank: false,
							allowDecimals: true,
							allowNegative: false,
							decimalPrecision: 4,
							disabled: true,
							fieldLabel: lang['kol-vo_ed_doz'],
							name: 'DocumentUcStr_RashEdCount',
							tabIndex: TABINDEX_DUSEF + 9,
							width: 100,
							xtype: 'numberfield',
							listeners: 
							{
								'change': function(field, newValue, oldValue) 
								{
									this.setKolvo(field, newValue, true);
								}.createDelegate(this)
							}
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
									base_form.findField('DocumentUcStr_SumR').setValue((count * newValue).toFixed(2));
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
							allowBlank: true,
							name: 'DocumentUcStr_Ser',
							width: 100,
							tabIndex: TABINDEX_DUSEF + 16,
							xtype: 'textfield'
						}]
					}]
				},
				{
					width: 100,
					fieldLabel: lang['№_sertifikata'],
					xtype: 'textfield',
					tabIndex: TABINDEX_DUSEF + 17,
					name: 'DocumentUcStr_CertNum',
					hiddenName: 'DocumentUcStr_CertNum',
					id: 'DUSEF_DocumentUcStr_CertNum',
					allowBlank: true
				}, 
				{
					fieldLabel: lang['rez_lab_issl'],
					name: 'DocumentUcStr_IsLab',
					hiddenName: 'DocumentUcStr_IsLab',
					tabIndex: TABINDEX_DUSEF + 19,
					xtype: 'swyesnocombo',
					allowBlank: true
				}],
				labelAlign: 'right',
				labelWidth: 130,
				reader: new Ext.data.JsonReader({
					success: Ext.emptyFn
				}, [
					{ name: 'DocumentUcStr_id' },
					{ name: 'Drug_id' },
					{ name: 'DrugPrepFas_id' },
					{ name: 'DrugFinance_id' },
					{ name: 'WhsDocumentCostItemType_id' },
					{ name: 'DocumentUcStr_PriceR' },
					{ name: 'DocumentUcStr_Count' },
					{ name: 'DocumentUcStr_EdCount' },
					{ name: 'DocumentUcStr_Ser' },
					{ name: 'DocumentUcStr_CertNum' },
					{ name: 'DocumentUcStr_IsLab' },
					//{ name: 'DocumentUcStr_RashCount' },
					{ name: 'DocumentUcStr_SumR' },
					{ name: 'DocumentUcStr_godnDate' },
					{ name: 'DocumentUcStr_oid' }
				]),
				region: 'center',
				//trackResetOnLoad: true,
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
	setKolvo: function(field, newValue, ed)
	{
		var base_form = this.findById('DocumentUcStrLpuEditForm').getForm();
		var price = base_form.findField('DocumentUcStr_PriceR').getValue();
		
		if ( this.documentUcStrMode == 'expenditure' ) 
		{
			var max_value = base_form.findField('DocumentUcStr_Count').getValue();
			if (ed==true)
			{
				max_value = base_form.findField('DocumentUcStr_EdCount').getValue();
			}
			
			if ( newValue > max_value ) 
			{
				newValue = max_value;
				field.setValue(max_value);
			}
		}
		
		if (base_form.findField('Drug_Fas').getValue()>0)
		{
			if (ed==true)
			{
				base_form.findField('DocumentUcStr_RashCount').setValue((newValue/base_form.findField('Drug_Fas').getValue()).toFixed(4));
			}
			else 
			{
				base_form.findField('DocumentUcStr_RashEdCount').setValue((base_form.findField('Drug_Fas').getValue()*newValue).toFixed(4));
			}
			/*
			log(base_form.findField('DocumentUcStr_RashCount').getValue());
			log(base_form.findField('Drug_Fas').getValue());
			log(base_form.findField('DocumentUcStr_RashEdCount').getValue());
			*/
		}
		
		if ( price.toString().length > 0 && newValue.toString().length > 0 )
		{
			base_form.findField('DocumentUcStr_SumR').setValue(Number(price * base_form.findField('DocumentUcStr_RashCount').getValue()).toFixed(4));
		}
		else 
		{
			base_form.findField('DocumentUcStr_SumR').setValue('');
			
		}
	},
	getLoadMask: function(MSG)
	{
		if (MSG) 
		{
			delete(this.loadMask);
		}
		if (!this.loadMask)
		{
			this.loadMask = new Ext.LoadMask(Ext.get(this.id), { msg: MSG });
		}
		return this.loadMask;
	},
	show: function() {
		sw.Promed.swDocumentUcStrLpuEditWindow.superclass.show.apply(this, arguments);

		var base_form = this.findById('DocumentUcStrLpuEditForm').getForm();
		var form = this;
		
		this.action = null;
		this.callback = Ext.emptyFn;
		this.documentUcStrMode = 'income';
		this.onHide = Ext.emptyFn;
		this.DrugFinance_id = null;
		this.WhsDocumentCostItemType_id = null;
		this.center();
		
		if ( !arguments[0] ) {
			sw.swMsg.alert(lang['oshibka'], lang['otsutstvuyut_neobhodimyie_parametryi'], function() { this.hide(); }.createDelegate(this) );
			return false;
		}
		//
		this.clearValues();
		base_form.reset();
		base_form.findField('DrugFinance_id').unsetFilter();
		base_form.findField('WhsDocumentCostItemType_id').unsetFilter();

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
		
		if ( arguments[0].DocumentUc_didDate ) {
			// 3. Добавить запрет ввода срока годности раньше даты поставки на форму "Строка документа: добавление". 
			// http://redmine/issues/2976
				base_form.findField('DocumentUcStr_godnDate').setMinValue(arguments[0].DocumentUc_didDate);
		}

        if (arguments[0].ContragentType_Code == 1 || arguments[0].DrugDocumentType_Code == 3) {
            base_form.findField('DocumentUcStr_godnDate').setAllowBlank(false);
            base_form.findField('DocumentUcStr_Ser').setAllowBlank(false);
        } else {
			base_form.findField('DocumentUcStr_godnDate').setAllowBlank(true);
			base_form.findField('DocumentUcStr_Ser').setAllowBlank(true);
		}

		if ( arguments[0].onHide ) {
			this.onHide = arguments[0].onHide;
		}

		if ( arguments[0].DrugFinance_id ) {
			this.DrugFinance_id = arguments[0].DrugFinance_id;
		}

		if ( arguments[0].WhsDocumentCostItemType_id ) {
			this.WhsDocumentCostItemType_id = arguments[0].WhsDocumentCostItemType_id;
		}

		if(this.Contragent_id){
			base_form.findField('Drug_id').getStore().baseParams = {
				Contragent_id: this.Contragent_id
			}
		}
		if(this.documentUcStrMode){
			base_form.findField('Drug_id').getStore().baseParams.mode= this.documentUcStrMode;
		}
		/*base_form.findField('Drug_id').getStore().baseParams = {
			mode: this.documentUcStrMode,
			Contragent_id: this.Contragent_id,
			DrugFinance_id: this.DrugFinance_id,
			WhsDocumentCostItemType_id: this.WhsDocumentCostItemType_id
		}*/

		base_form.findField('DrugPrepFas_id').getStore().baseParams = {
			mode: this.documentUcStrMode,
			Contragent_id: this.Contragent_id,
			DrugFinance_id: this.DrugFinance_id,
			WhsDocumentCostItemType_id: this.WhsDocumentCostItemType_id
		}
		
		this.getLoadMask().show();
		
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
					if(base_form.findField('DrugDeleted').getValue() == 1){
						this.action = 'view';
						this.enableEdit(false);
						sw.swMsg.alert('Сообщение', 'Медикамент, указанный в строке удален из справочника медикаментов - редактирование недоступно.' );
					}
					// Что надо сделать при чтении
					base_form.findField('DocumentUcStr_RashCount').setValue(base_form.findField('DocumentUcStr_Count').getValue());
					
					base_form.findField('DrugPrepFas_id').getStore().load({
						params: {
							DrugPrepFas_id: base_form.findField('DrugPrepFas_id').getValue()
						},
						callback: function () {
							base_form.findField('DrugPrepFas_id').setValue(base_form.findField('DrugPrepFas_id').getValue());
							base_form.findField('DrugPrepFas_id').fireEvent('change', base_form.findField('DrugPrepFas_id'), base_form.findField('DrugPrepFas_id').getValue());
							if (this.action!='view') 
							{
								base_form.findField('DrugPrepFas_id').focus(true, 500);
							}
						}
					});
					this.getLoadMask().hide();
					base_form.findField('DocumentUcStr_PriceRN').setValue(base_form.findField('DocumentUcStr_PriceRN').getValue());
				}.createDelegate(this),
				url: '/?c=Farmacy&m=loadDocumentUcStrView'
			});
		}
		else 
		{
			base_form.findField('DrugPrepFas_id').focus(true, 500);
		}
		
		switch ( this.action ) {
			case 'add':
				this.enableEdit(true);
				this.setTitle(lang['stroka_dokumenta_dobavlenie']);

				this.getLoadMask().hide();
				base_form.clearInvalid();

			break;

			case 'edit':
				this.enableEdit(true);
				this.setTitle(lang['stroka_dokumenta_redaktirovanie']);
				base_form.clearInvalid();

			break;

			case 'view':
				this.enableEdit(false);
				this.setTitle(lang['stroka_dokumenta_prosmotr']);
				base_form.clearInvalid();

				this.buttons[this.buttons.length - 1].focus();
			break;
		}
	},
	split: true,
	width: 700
});
