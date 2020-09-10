/**
* swEvnDrugPrescrEditWindow - редактирование медикамента, списываемого при выполнении назначения
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Prescription
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Stas Bykov aka Savage (savage@swan.perm.ru) (использована форма swEvnDrugEditWindow by Марков Андрей)
* @version      19.12.2011
* @comment      Префикс для id компонентов EDRPREF (EvnDrugPrescrEditWindow)
*
* @input data: action - действие (add, edit)
*/
/*NO PARSE JSON*/
sw.Promed.swEvnDrugPrescrEditWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swEvnDrugPrescrEditWindow',
	objectSrc: '/jscore/Forms/Hospital/swEvnDrugPrescrEditWindow.js',

	autoHeight: true,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	documentUcStrMode: 'expenditure',
	doSave: function() {
		var base_form = this.EditForm.getForm();

		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					this.EditForm.getFirstInvalidEl().focus(true);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Подождите, идет сохранение..." });
		loadMask.show();

		var data = new Object();

		var document_uc_str_id = base_form.findField('DocumentUcStr_oid').getValue();
		var document_uc_str_name = '';
		var drug_prep_fas_id = base_form.findField('DrugPrepFas_id').getValue();
		var drug_prep_fas_name = '';
		var mol_id = base_form.findField('Mol_id').getValue();
		var mol_name = '';

		var index = base_form.findField('DocumentUcStr_oid').getStore().findBy(function(rec) {
			if ( rec.get('DocumentUcStr_id') == document_uc_str_id ) {
				return true;
			}
			else {
				return false;
			}
		});
		var record = base_form.findField('DocumentUcStr_oid').getStore().getAt(index);

		if ( record ) {
			document_uc_str_name = record.get('DocumentUcStr_Name');
		}

		index = base_form.findField('DrugPrepFas_id').getStore().findBy(function(rec) {
			if ( rec.get('DrugPrepFas_id') == drug_prep_fas_id ) {
				return true;
			}
			else {
				return false;
			}
		});
		record = base_form.findField('DrugPrepFas_id').getStore().getAt(index);

		if ( record ) {
			drug_prep_fas_name = record.get('DrugPrep_Name');
		}

		index = base_form.findField('Mol_id').getStore().findBy(function(rec) {
			if ( rec.get('Mol_id') == mol_id ) {
				return true;
			}
			else {
				return false;
			}
		});
		record = base_form.findField('Mol_id').getStore().getAt(index);

		if ( record ) {
			mol_name = record.get('Person_Fio');
		}
		if(document_uc_str_id) {
			data.evnDrugData = {
				'DrugPrep_Name': drug_prep_fas_name,
				'DrugPrepFas_id': drug_prep_fas_id,
				'Drug_id': base_form.findField('Drug_id').getValue(),
				'EvnDrug_Kolvo': base_form.findField('EvnDrug_Kolvo').getValue(),
				'EvnDrug_KolvoEd': base_form.findField('EvnDrug_KolvoEd').getValue(),
				'DocumentUcStr_oid': document_uc_str_id,
				'Mol_id': mol_id,
				'EvnDrug_setDate': base_form.findField('EvnDrug_setDate').getValue(),
				'EvnDrug_setTime': base_form.findField('EvnDrug_setTime').getValue(),
				'Mol_Name': mol_name,
				'DocumentUcStr_Name': document_uc_str_name
			};
			this.callback(data);
		}
		loadMask.hide();

		this.hide();
	},
	draggable: true,
	id: 'EvnDrugPrescrEditWindow',
	initComponent: function() {
		this.EditForm = new Ext.form.FormPanel({
			autoScroll: true,
			bodyStyle: 'padding: 0.5em;',
			border: false,
			frame: true,
			id: 'EvnDrugPrescrEditForm',

			items: [{
				name: 'EvnDrug_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'EvnPrescrTreat_id',
				value: -1,
				xtype: 'hidden'
			}, {
				name: 'DrugComplexMnn_id',
				value: null,
				xtype: 'hidden'
			}, {
				name: 'Person_id',
				value: -1,
				xtype: 'hidden'
			}, {
				name: 'PersonEvn_id',
				value: -1,
				xtype: 'hidden'
			}, {
				name: 'Server_id',
				value: -1,
				xtype: 'hidden'
			}, {
				allowBlank: false,
				disabled: true,
				displayField: 'Evn_Name',
				editable: false,
				enableKeyEvents: true,
				fieldLabel: lang['otdelenie'],
				// tabIndex: TABINDEX_EDRPREF + 0,
				hiddenName: 'EvnDrug_pid',
				listWidth: 600,
				mode: 'local',
				store: new Ext.data.JsonStore({
					autoLoad: false,
					fields: [
						{ name: 'Evn_id', type: 'int' },
						{ name: 'MedStaffFact_id', type: 'int' },
						{ name: 'LpuSection_id', type: 'int' },
						{ name: 'MedPersonal_id', type: 'int' },
						{ name: 'Evn_Name', type: 'string' },
						{ name: 'Evn_setDate', type: 'date', format: 'd.m.Y' },
                        { name: 'Evn_disDate', type: 'date', format: 'd.m.Y' }
					],
					id: 'Evn_id'
				}),
				// tabIndex: TABINDEX_EUCOMEF + 1,
				tpl: new Ext.XTemplate(
					'<tpl for="."><div class="x-combo-list-item">',
					'{Evn_Name}&nbsp;',
					'</div></tpl>'
				),
				triggerAction: 'all',
				valueField: 'Evn_id',
				anchor: '98%',
				xtype: 'combo'
			}, {
				border: false,
				layout: 'column',
				items: [{
					border: false,
					layout: 'form',
					items: [{
						allowBlank: false,
						disabled: true,
						fieldLabel: lang['data'],
						format: 'd.m.Y',
						name: 'EvnDrug_setDate',
						plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
						selectOnFocus: true,
						// tabIndex: TABINDEX_EDRPREF + 1,
						width: 100,
						xtype: 'swdatefield'
					}]
				}, {
					border: false,
					labelWidth: 50,
					layout: 'form',
					items: [{
						fieldLabel: lang['vremya'],
						listeners: {
							'keydown': function (inp, e) {
								if ( e.getKey() == Ext.EventObject.F4 ) {
									e.stopEvent();
									inp.onTriggerClick();
								}
							}
						},
						name: 'EvnDrug_setTime',
						onTriggerClick: function() {
							var base_form = this.EditForm.getForm();
							var time_field = base_form.findField('EvnDrug_setTime');

							if ( time_field.disabled ) {
								return false;
							}

							setCurrentDateTime({
								dateField: base_form.findField('EvnDrug_setDate'),
								loadMask: true,
								setDate: true,
								setDateMaxValue: true,
								setDateMinValue: false,
								setTime: true,
								timeField: time_field,
								windowId: 'EvnDrugPrescrEditWindow'
							});
						}.createDelegate(this),
						plugins: [ new Ext.ux.InputTextMask('99:99', true) ],
						// tabIndex: TABINDEX_EDRPREF + 2,
						validateOnBlur: false,
						width: 60,
						xtype: 'swtimefield'
					}]
				}]
			}, {
				allowBlank: false,
				anchor: '98%',
				disabled: true,
				hiddenName: 'LpuSection_id',
				lastQuery: '',
				// listWidth: 650,
				linkedElements: [ ],
				// tabIndex: TABINDEX_EDRPREF + 3,
				xtype: 'swlpusectionglobalcombo'
			}, {
				allowBlank: false,
				anchor: '98%',
				hiddenName: 'Mol_id',
				lastQuery: '',
				// listWidth: 650,
				// tabIndex: TABINDEX_EDRPREF + 4,
				xtype: 'swmolcombo'
			}, {
				fieldLabel: lang['istochnik_finans'],
				anchor: '98%',
				disabled: true,
				name: 'DrugFinance_Name',
				xtype: 'textfield'
			}, {
				fieldLabel: lang['statya_rashoda'],
				anchor: '98%',
				disabled: true,
				name: 'WhsDocumentCostItemType_Name',
				xtype: 'textfield'
			}, {
				// Первый комбобокс (медикамент)
				anchor: '98%',
				disabled: true,
				hiddenName: 'DrugPrepFas_id',
				// tabIndex: TABINDEX_EDRPREF + 5,
				lastQuery: '',
				xtype: 'swdrugprepcombo'
			}, {
				// второй комбобокс (упаковка)
				anchor: '98%',
				hiddenName: 'Drug_id',
				listeners: {
					'change': function(combo, newValue, oldValue) {
						var base_form = this.EditForm.getForm();
						var record = combo.getStore().getById(newValue);

						base_form.findField('Drug_Fas').setRawValue('');
						base_form.findField('DrugForm_Name').setRawValue('');
						base_form.findField('DrugUnit_Name').setRawValue('');

						if ( !record ) {
							base_form.findField('EvnDrug_Kolvo').fireEvent('change', base_form.findField('EvnDrug_Kolvo'), base_form.findField('EvnDrug_Kolvo').getValue());
							return false;
						}

						base_form.findField('Drug_Fas').setRawValue(record.get('Drug_Fas') ? record.get('Drug_Fas') : 1);
						base_form.findField('DrugForm_Name').setRawValue(record.get('DrugForm_Name'));
						base_form.findField('DrugUnit_Name').setRawValue(record.get('DrugUnit_Name'));

						var document_uc_str_combo = base_form.findField('DocumentUcStr_oid');

						var document_uc_str_oid = document_uc_str_combo.getValue();
						var lpu_section_id = base_form.findField('LpuSection_id').getValue();

						document_uc_str_combo.clearValue();
						document_uc_str_combo.getStore().removeAll();
						document_uc_str_combo.lastQuery = '';

						if ( newValue > 0 ) {
							document_uc_str_combo.getStore().load({
								callback: function() {
									if ( document_uc_str_oid > 0 ) {
										document_uc_str_combo.setValue(document_uc_str_oid);
										base_form.findField('DocumentUcStr_oid').fireEvent('change', base_form.findField('DocumentUcStr_oid'), base_form.findField('DocumentUcStr_oid').getValue());
									}
								},
								params: {
									date: Ext.util.Format.date(base_form.findField('EvnDrug_setDate').getValue(),'d.m.Y'),
									Drug_id: newValue,
									LpuSection_id: lpu_section_id,
									mode: 'default'
								}
							});
						}
						else {
							document_uc_str_combo.fireEvent('change', document_uc_str_combo, null, 1);
						}
					}.createDelegate(this)
				},
				// tabIndex: TABINDEX_EDRPREF + 5,
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
						// tabIndex: TABINDEX_EDRPREF + 6,
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
						// tabIndex: TABINDEX_EDRPREF + 7,
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
						// tabIndex: TABINDEX_EDRPREF + 8,
						width: 70,
						xtype: 'numberfield'
					}]
				}]
			}, {
				allowBlank: false,
				anchor: '98%',
				displayField: 'DocumentUcStr_Name',
				enableKeyEvents: true,
				fieldLabel: lang['partiya'],
				forceSelection: true,
				hiddenName: 'DocumentUcStr_oid',
				listeners: {
					'beforeselect': function() {
						return true;
					}.createDelegate(this),
					'change': function(combo, newValue, oldValue) {
						var base_form = this.EditForm.getForm();

						// base_form.findField('EvnDrug_KolvoEd').setValue('');
						// base_form.findField('EvnDrug_Price').setValue('');

						var record = combo.getStore().getById(newValue);

						if ( record ) {
							base_form.findField('DocumentUcStr_Count').setValue(record.get('DocumentUcStr_Count'));
							base_form.findField('DocumentUcStr_EdCount').setValue(record.get('DocumentUcStr_EdCount'));
							// base_form.findField('EvnDrug_Price').setValue(record.get('EvnDrug_Price'));
							// base_form.findField('DocumentUc_id').setValue(record.get('DocumentUc_id'));
							// Расчет ед.
							base_form.findField('DocumentUcStr_EdCount').setValue((base_form.findField('Drug_Fas').getValue() * record.get('DocumentUcStr_Count')).toFixed(2));
						}
						else {
							combo.clearValue();
							base_form.findField('DocumentUcStr_Count').setValue(null);
							// base_form.findField('DocumentUc_id').setValue(null);
							base_form.findField('DocumentUcStr_EdCount').setValue(null);								
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
					reader: new Ext.data.JsonReader( {
						id: 'DocumentUcStr_id'
					}, [
						{ name: 'DocumentUcStr_id', mapping: 'DocumentUcStr_id' },
						// { name: 'DocumentUc_id', mapping: 'DocumentUc_id' },
						{ name: 'DocumentUcStr_Name', mapping: 'DocumentUcStr_Name' },
						{ name: 'DrugFinance_id', mapping: 'DrugFinance_id' },
						{ name: 'DocumentUcStr_Count', mapping: 'DocumentUcStr_Count' },
						{ name: 'DocumentUcStr_EdCount', mapping: 'DocumentUcStr_EdCount' },
						{ name: 'DocumentUcStr_Ser', mapping: 'DocumentUcStr_Ser' },
						{ name: 'DrugFinance_Name', mapping: 'DrugFinance_Name' },
						{ name: 'DocumentUcStr_godnDate', mapping: 'DocumentUcStr_godnDate' }
						// { name: 'EvnDrug_Price', mapping: 'EvnDrug_Price' }
					]),
					url: '/?c=Farmacy&m=loadDocumentUcStrList'
				}),
				// tabIndex: TABINDEX_EDRPREF + 9,
				tpl: new Ext.XTemplate(
					'<table cellpadding="0" cellspacing="0" style="width: 100%;"><tr style="font-family: tahoma; font-size: 8pt; font-weight: bold;">',
					'<td style="padding: 2px; width: 20%;">Срок годности</td>',
					'<td style="padding: 2px; width: 15%;">Цена</td>',
					'<td style="padding: 2px; width: 15%;">Остаток</td>',
					'<td style="padding: 2px; width: 35%;">Источник финансирования</td>',
					'<td style="padding: 2px; width: 15%;">Серия</td></tr>',
					'<tpl for="."><tr class="x-combo-list-item" style="font-family: tahoma; font-size: 8pt;">',
					'<td style="padding: 2px;">{DocumentUcStr_godnDate}&nbsp;</td>',
					// '<td style="padding: 2px;">{EvnDrug_Price}&nbsp;</td>',
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
						allowDecimals: true,
						allowNegative: false,
						decimalPrecision: 6,
						disabled: true,
						fieldLabel: lang['ostatok_ed_uch'],
						name: 'DocumentUcStr_Count',
						// tabIndex: TABINDEX_EDRPREF + 10,
						width: 100,
						xtype: 'numberfield'
					}]
				}, {
					border: false,
					labelWidth: 130,
					layout: 'form',
					items: [{
						allowDecimals: true,
						allowNegative: false,
						decimalPrecision: 2,
						disabled: true,
						fieldLabel: lang['ostatok_ed_doz'],
						name: 'DocumentUcStr_EdCount',
						// tabIndex: TABINDEX_EDRPREF + 11,
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
						decimalPrecision: 4,
						disabled: false,
						fieldLabel: lang['kolichestvo_ed_uch'],
						minValue: 0.0001,
						name: 'EvnDrug_Kolvo',
						// tabIndex: TABINDEX_EDRPREF + 12,
						width: 100,
						listeners: {
							'change': function(field, newValue, oldValue) {
								var bf = this.EditForm.getForm();
								bf.findField('DrugPrepFas_id').getStore().baseParams.Drug_Kolvo = newValue;
								bf.findField('Drug_id').getStore().baseParams.Drug_Kolvo = newValue;
							}.createDelegate(this)
						},
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
						disabled: false,
						decimalPrecision: 2,
						fieldLabel: lang['kol-vo_ed_doz'],
						name: 'EvnDrug_KolvoEd',
						// tabIndex: TABINDEX_EDRPREF + 13,
						width: 100,
						listeners: {
							'change': function(field, newValue, oldValue) {
								var bf = this.EditForm.getForm();
								var fas = bf.findField('Drug_Fas').getValue() > 0 ? bf.findField('Drug_Fas').getValue() : 1;
								var kolvo = 1;
								if(newValue > fas) {
									kolvo = Math.ceil(newValue/fas);//.toFixed(0);
								}
								bf.findField('EvnDrug_Kolvo').setValue(kolvo);
							}.createDelegate(this)
						},
						xtype: 'numberfield'
					}]
				}]
			}/*, {
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
						fieldLabel: lang['tsena'],
						name: 'EvnDrug_Price',
						// tabIndex: TABINDEX_EDRPREF + 14,
						width: 100,
						xtype: 'numberfield'
					}]
				}, {
					border: false,
					labelWidth: 130,
					layout: 'form',
					items: [{
						allowBlank: false,
						disabled: true,
						fieldLabel: lang['summa'],
						name: 'EvnDrug_Sum',
						// tabIndex: TABINDEX_EDRPREF + 15,
						width: 100,
						xtype: 'numberfield'
					}]
				}]
			}*/],
			labelAlign: 'right',
			labelWidth: 130,
			reader: new Ext.data.JsonReader({
				success: Ext.emptyFn
			}, [
				{ name: 'DocumentUcStr_oid' },
				{ name: 'DrugComplexMnn_id' },
				{ name: 'Drug_id' },
				{ name: 'DrugPrepFas_id' },
				{ name: 'EvnDrug_id' },
				{ name: 'EvnDrug_Kolvo' },
				{ name: 'EvnDrug_KolvoEd' },
				{ name: 'EvnDrug_pid' },
				// { name: 'EvnDrug_Price' },
				{ name: 'EvnDrug_setDate' },
				{ name: 'EvnDrug_setTime' },
				// { name: 'EvnDrug_Sum' },
				{ name: 'LpuSection_id' },
				{ name: 'Mol_id' },
				{ name: 'Person_id' },
				{ name: 'PersonEvn_id' },
				{ name: 'Server_id' },
				{ name: 'DrugFinance_Name' },
				{ name: 'WhsDocumentCostItemType_Name' }
			]),
			region: 'center',
			trackResetOnLoad: true,
			url: '/?c=EvnPrescr&m=save&m=saveEvnDrugPrescr'
		});

		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.doSave();
				}.createDelegate(this),
				iconCls: 'save16',
				// tabIndex: TABINDEX_EDRPREF + 21,
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
					this.buttons[0].focus();
				}.createDelegate(this),
				onTabAction: function () {
					this.EditForm.getForm().findField('Drug_id').focus(true);
				}.createDelegate(this),
				// tabIndex: TABINDEX_EDRPREF + 22,
				text: BTN_FRMCANCEL,
				tooltip: lang['zakryit_okno']
			}],
			items: [
				new sw.Promed.PersonInformationPanelShort( {
					id: 'EDRPREF_PersonInformationFrame'
				}),
				this.EditForm
			]
		});

		sw.Promed.swEvnDrugPrescrEditWindow.superclass.initComponent.apply(this, arguments);
	},
	keys: [{
		alt: true,
		fn: function(inp, e) {
			var current_window = Ext.getCmp('EvnDrugPrescrEditForm');

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

			switch ( e.getKey() ) {
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
		beforeshow: function() {
			//
		},
		hide: function() {
			this.onHide();
		}
	},
	modal: true,
	onHide: Ext.emptyFn,
	plain: true,
	resizable: false,
	setFilterMol: function(LpuSection_id) {
		// Устанавливаем фильтр и если по условиям фильтра найдена только одна запись - то устанавливаем эту запись
		var base_form = this.EditForm.getForm();
		var combo = base_form.findField('Mol_id');

		combo.getStore().clearFilter();
		combo.lastQuery = '';

		var Mol_id = null;
		var OldMol_id = combo.getValue();
		var Yes = false;

		if ( combo.getStore().getCount() > 0 ) {
			combo.getStore().filterBy(function(record) {
				if ( LpuSection_id == record.get('LpuSection_id') && LpuSection_id > 0 ) {
					Mol_id = record.get('Mol_id');

					if ( OldMol_id == Mol_id ) {
						Yes = true;
					}

					return true;
				}
				else {
					return false;
				}
			});

			if ( Yes ) {
				combo.setValue(OldMol_id);
			}
			else {
				if ( combo.getStore().getCount() == 1 ) {
					combo.setValue(Mol_id);
				}
				else {
					combo.clearValue();
				}
			}
		}
	},
	show: function() {
		sw.Promed.swEvnDrugPrescrEditWindow.superclass.show.apply(this, arguments);

		var base_form = this.EditForm.getForm();
		var formParams = new Object();

		base_form.reset();

		base_form.findField('DocumentUcStr_oid').getStore().removeAll();
		base_form.findField('Drug_id').getStore().removeAll();
		base_form.findField('DrugPrepFas_id').getStore().removeAll();
		base_form.findField('DrugPrepFas_id').setDisabled(true);
		base_form.findField('EvnDrug_pid').getStore().removeAll();

		this.callback = Ext.emptyFn;
		this.onHide = Ext.emptyFn;

		this.center();
		
		if ( !arguments[0] || !arguments[0].formParams )  {
			sw.swMsg.alert(lang['oshibka'], lang['otsutstvuyut_neobhodimyie_parametryi'], function() { this.hide(); }.createDelegate(this));
			return false;
		}

		formParams = arguments[0].formParams;

		if ( arguments[0].callback ) {
			this.callback = arguments[0].callback;
		}

		if ( arguments[0].onHide ) {
			this.onHide = arguments[0].onHide;
		}

		if ( arguments[0].parentEvnComboData ) {
			base_form.findField('EvnDrug_pid').getStore().loadData(arguments[0].parentEvnComboData);
		}

		this.findById('EDRPREF_PersonInformationFrame').load({
			 Person_id: (arguments[0].Person_id ? arguments[0].Person_id : '')
			,Person_Birthday: (arguments[0].Person_Birthday ? arguments[0].Person_Birthday : '')
			,Person_Firname: (arguments[0].Person_Firname ? arguments[0].Person_Firname : '')
			,Person_Secname: (arguments[0].Person_Secname ? arguments[0].Person_Secname : '')
			,Person_Surname: (arguments[0].Person_Surname ? arguments[0].Person_Surname : '')
		});
		
		var loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT });
		loadMask.show();
		
		base_form.setValues(formParams);
		
		var document_uc_str_oid = base_form.findField('DocumentUcStr_oid').getValue();
		var drug_id = base_form.findField('Drug_id').getValue();
		var drug_prep_fas_id = base_form.findField('DrugPrepFas_id').getValue();
		var evn_combo = base_form.findField('EvnDrug_pid');
		var evn_drug_pid = null;
		var index;
		var lpu_section_id = null;
		var record;
		var set_date = true;

		evn_combo.setValue(evn_combo.getStore().getAt(0).get('Evn_id'));

		lpu_section_id = evn_combo.getStore().getAt(0).get('LpuSection_id');

		base_form.findField('Mol_id').getStore().load({
			callback: function() {
				base_form.findField('Mol_id').setValue(base_form.findField('Mol_id').getValue());
				this.setFilterMol(lpu_section_id);
			}.createDelegate(this)
		});

		setLpuSectionGlobalStoreFilter({
			id: lpu_section_id
		});

		base_form.findField('LpuSection_id').getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));

		if ( base_form.findField('LpuSection_id').getStore().getCount() > 0 ) {
			base_form.findField('LpuSection_id').setValue(lpu_section_id);
		}

		base_form.findField('Drug_id').getStore().baseParams = {
			 date: Ext.util.Format.date(base_form.findField('EvnDrug_setDate').getValue(),'d.m.Y')
			,Drug_Kolvo: base_form.findField('EvnDrug_Kolvo').getValue()
			,DrugPrepFas_id: drug_prep_fas_id
			,mode: this.documentUcStrMode
			,LpuSection_id: lpu_section_id
		}

		base_form.findField('DrugPrepFas_id').getStore().baseParams = {
			 date: Ext.util.Format.date(base_form.findField('EvnDrug_setDate').getValue(),'d.m.Y')
			,Drug_Kolvo: base_form.findField('EvnDrug_Kolvo').getValue()
			,DrugPrepFas_id: drug_prep_fas_id
			,mode: this.documentUcStrMode
			,LpuSection_id: lpu_section_id
		}

		base_form.findField('DrugPrepFas_id').getStore().load({
			callback: function() {
				index = base_form.findField('DrugPrepFas_id').getStore().findBy(function(rec) {
					if ( rec.get('DrugPrepFas_id') == drug_prep_fas_id ) {
						return true;
					}
					else {
						return false;
					}
				});
				record = base_form.findField('DrugPrepFas_id').getStore().getAt(index);

				if ( record ) {
					base_form.findField('DrugPrepFas_id').setValue(drug_prep_fas_id);

					base_form.findField('Drug_id').getStore().load({
						callback: function() {
							index = base_form.findField('Drug_id').getStore().findBy(function(rec) {
								if ( rec.get('Drug_id') == drug_id ) {
									return true;
								}
								else {
									return false;
								}
							});
							record = base_form.findField('Drug_id').getStore().getAt(index);

							if ( record ) {
								base_form.findField('Drug_id').setValue(drug_id);
								base_form.findField('Drug_id').fireEvent('change', base_form.findField('Drug_id'), drug_id);

								loadMask.hide();
/*
								base_form.findField('DocumentUcStr_oid').getStore().load({
									callback: function() {
										index = base_form.findField('DocumentUcStr_oid').getStore().findBy(function(rec) {
											if ( rec.get('DocumentUcStr_id') == document_uc_str_oid ) {
												return true;
											}
											else {
												return false;
											}
										});
										record = base_form.findField('DocumentUcStr_oid').getStore().getAt(index);

										if ( record ) {
											base_form.findField('DocumentUcStr_oid').setValue(document_uc_str_oid);
										}
										else {
											base_form.findField('DocumentUcStr_oid').clearValue();
										}

										loadMask.hide();
									}.createDelegate(this)
								});
*/
							}
							else {
								base_form.findField('DocumentUcStr_oid').clearValue();
								base_form.findField('Drug_id').clearValue();

								loadMask.hide();
							}
						}.createDelegate(this)
					});
				}
				else {
					loadMask.hide();
					/*
					sw.swMsg.alert(lang['oshibka'], lang['medikament_otsutstvuet_na_ostatkah_otdeleniya'], function() { 
						this.hide();
					}.createDelegate(this));
					*/
					//возможность в рамках исполнения назначения списать любой другой медикамент с остатков отделения
					base_form.findField('Drug_id').clearValue();
					base_form.findField('DocumentUcStr_oid').clearValue();
					base_form.findField('DrugPrepFas_id').setDisabled(false);
					base_form.findField('DrugPrepFas_id').getStore().removeAll();
					base_form.findField('DrugPrepFas_id').clearValue();
					base_form.findField('DrugPrepFas_id').fireEvent('change', base_form.findField('DrugPrepFas_id'), null);
					base_form.findField('DrugPrepFas_id').focus(true, 250);
					base_form.clearInvalid();
					return true;
				}
			}.createDelegate(this)
		});

		base_form.clearInvalid();

		base_form.findField('EvnDrug_setTime').focus(true, 250);
	},
	split: true,
	title: lang['spisanie_medikamenta_redaktirovanie'],
	width: 700
});
