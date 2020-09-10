/**
* swEvnReceptRlsProvideEditWindow - окно выбора серии и количества для обеспечения рецептов.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      	DLO
* @access       	public
* @copyright    	Copyright (c) 2013 Swan Ltd.
* @author       	Salakhov R.
* @version      	18.10.2013
*/

sw.Promed.swEvnReceptRlsProvideEditWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'EvnReceptRlsProvideEditWindow',
	layout: 'border',
	modal: true,
	plain: true,
	resizable: false,
	SubAccountTypeIsReserve: 1,	//по этому признаку зарезервированные медикаменты будут отфильтровываться в модели, тем самым исключается их попадание в поле 'Партия' (https://redmine.swan-it.ru/issues/168953)
	doSelect: function() {
		var wnd = this;

		if (!this.form.isValid()) {
			sw.swMsg.show(
				{
					buttons: Ext.Msg.OK,
					fn: function()
					{
						wnd.base_form.getFirstInvalidEl().focus(true);
					},
					icon: Ext.Msg.WARNING,
					msg: ERR_INVFIELDS_MSG,
					title: ERR_INVFIELDS_TIT
				});
			return false;
		}

		var params = new Object();
		params.Kolvo = this.form.findField('Kolvo').getValue();

		var dor_combo = this.form.findField('DrugOstatRegistry_id');
		var idx = dor_combo.getStore().findBy(function(rec) { return rec.get('DrugOstatRegistry_id') == dor_combo.getValue(); });
		if (idx >= 0) {
			var record = dor_combo.getStore().getAt(idx);

			if (record.get('PrepSeries_isDefect') == 1) {
				sw.swMsg.alert(lang['vnimanie'], lang['ceriya_zabrakovana_i_lp_ne_mojet_byit_otpuschen_po_retseptu']);
				return false;
			}

			if (record) {
				Ext.apply(params, record.data);
			}
		}

        //расчет цены и количества упаковок (для случаев когда для отоваривания используются остатки с ед. уч. отличными от упаковки)
        var gpc_coeff = !Ext.isEmpty(params.GoodsPackCount_Count) ? params.GoodsPackCount_Count*1 : 1; //количество ед измерения в упаковке (для упаковки равен 1)
        params.PackKolvo = params.Kolvo/gpc_coeff;
        params.DrugOstatRegistry_PackCost = params.DrugOstatRegistry_Cost*gpc_coeff;

		if (this.callback && typeof this.callback == 'function') {
			this.callback(params);
		}
		this.hide();
	},
	setInformationValues: function() {
		var dor_combo = this.form.findField('DrugOstatRegistry_id');
		var str_id = dor_combo.getValue();
		var idx = dor_combo.getStore().findBy(function(rec) { return rec.get('DrugOstatRegistry_id') == str_id; });

		this.form.setValues({
			PrepSeries_Ser: null,
			PrepSeries_GodnDate: null,
            DrugOstatRegistry_Cost: null,
            GoodsUnit_Nick: null
		});

		if (idx > -1) {
			var record = dor_combo.getStore().getAt(idx);
			this.form.setValues({
				PrepSeries_Ser: record.get('PrepSeries_Ser'),
				PrepSeries_GodnDate: record.get('PrepSeries_GodnDate'),
				DrugOstatRegistry_Cost: record.get('DrugOstatRegistry_Cost') > 0 ? (record.get('DrugOstatRegistry_Cost')*1).toFixed(2) : null,
                GoodsUnit_Nick: record.get('GoodsUnit_Nick')
			});
            //alert(dor_combo.getStore().getCount());
            if(dor_combo.getStore().getCount() == 1)
            {
                this.doSelect();
            }
		}
	},
	show: function() {
		sw.Promed.swEvnReceptRlsProvideEditWindow.superclass.show.apply(this, arguments);

		var wnd = this;

		this.EvnRecept_id = null;
		this.EvnReceptGeneral_id = null;
		this.DrugOstatRegistry_id = null;
		this.EvnRecept_Kolvo = 0;
		this.MedService_id = null;
		this.callback = Ext.emptyFn;
        this.Drug_ean = null;
        this.DrugRls_id = null;
        this.Drugnomen_Code = null;
		if (!arguments[0]) {
			this.hide();
			return false;
		}

		if (this.callback && typeof this.callback == 'function') {
			this.callback = arguments[0].callback;
		}

		this.form.reset();

		if (arguments[0].params) {
			if (arguments[0].params.EvnRecept_id && arguments[0].params.EvnRecept_id > 0) {
				this.EvnRecept_id = arguments[0].params.EvnRecept_id;
			}
			if (arguments[0].params.EvnReceptGeneral_id && arguments[0].params.EvnReceptGeneral_id > 0) {
				this.EvnReceptGeneral_id = arguments[0].params.EvnReceptGeneral_id;
			}
			if (arguments[0].params.DrugOstatRegistry_id && arguments[0].params.DrugOstatRegistry_id > 0) {
				this.DrugOstatRegistry_id = arguments[0].params.DrugOstatRegistry_id;
			}
			if (arguments[0].params.EvnRecept_Kolvo && arguments[0].params.EvnRecept_Kolvo > 0) {
				this.EvnRecept_Kolvo = arguments[0].params.EvnRecept_Kolvo*1;
			}
			if (arguments[0].params.MedService_id && arguments[0].params.MedService_id > 0) {
				this.MedService_id = arguments[0].params.MedService_id;
			}
            if (arguments[0].params.Drug_ean && arguments[0].params.Drug_ean > 0) {
                this.Drug_ean = arguments[0].params.Drug_ean;
            }
            if (arguments[0].params.DrugRls_id && arguments[0].params.DrugRls_id > 0) {
                this.DrugRls_id = arguments[0].params.DrugRls_id;
            }
            if (arguments[0].params.WhsDocumentCostItemType && arguments[0].params.WhsDocumentCostItemType > 0) {
                this.WhsDocumentCostItemType = arguments[0].params.WhsDocumentCostItemType;
            }
            if (arguments[0].params.DrugFinance_id && arguments[0].params.DrugFinance_id > 0) {
                this.DrugFinance_id = arguments[0].params.DrugFinance_id;
            }
            if (arguments[0].params.DrugComplexMnn_id && arguments[0].params.DrugComplexMnn_id > 0) {
                this.DrugComplexMnn_id = arguments[0].params.DrugComplexMnn_id;
            }
            if (arguments[0].params.Drugnomen_Code && arguments[0].params.Drugnomen_Code != '') {
                this.Drugnomen_Code = arguments[0].params.Drugnomen_Code;
            }
            this.Sin_check = arguments[0].params.Sin_check;
            // params.Sin_check
			this.form.setValues(arguments[0].params);
		}

		var dor_combo = this.form.findField('DrugOstatRegistry_id');
		var str_id = dor_combo.getValue();
        var that = this;
		dor_combo.getStore().load({
			params: {
                Drug_ean: wnd.Drug_ean,
                WhsDocumentCostItemType: wnd.WhsDocumentCostItemType,
                DrugFinance_id: wnd.DrugFinance_id,
				MedService_id: wnd.MedService_id,
                DrugComplexMnn_id: wnd.DrugComplexMnn_id,
                Sin_check: wnd.Sin_check,
                Drug_id: wnd.DrugRls_id,
                Drugnomen_Code: wnd.Drugnomen_Code,
                EvnRecept_id: wnd.EvnRecept_id,
				SubAccountTypeIsReserve: wnd.SubAccountTypeIsReserve
			},
			callback: function() {
				//wnd.form.findField('Kolvo').disable();
				if (str_id>0) {
					var idx = dor_combo.getStore().findBy(function(rec) { return rec.get('DrugOstatRegistry_id') == str_id; });
					dor_combo.setValue(idx > -1 ? str_id : null);
					dor_combo.fireEvent('change', dor_combo, dor_combo.getValue());
				} else {
					dor_combo.getStore().each(function(record){
						if (record.get('DrugOstatRegistry_id') > 0 && record.get('PrepSeries_isDefect') != 1) {
							//установка количества по умолчанию
                            var coeff = record.get('WhsDocumentSupplySpecDrug_Coeff')*1; //коэфицент пересчета количества для синонимов
                            var gpc_coeff = record.get('GoodsPackCount_Count')*1; //количество ед измерения в упаковке (для упаковки равен 1)
                            var need_kolvo = (coeff > 0 ? wnd.EvnRecept_Kolvo*coeff : wnd.EvnRecept_Kolvo)*gpc_coeff;
							var kolvo = record.get('DrugOstatRegistry_Kolvo') > need_kolvo ? need_kolvo : record.get('DrugOstatRegistry_Kolvo');

							wnd.form.findField('Kolvo').setValue(kolvo);
							//установка серии по умолчанию
							dor_combo.setValue(record.get('DrugOstatRegistry_id'));
							dor_combo.fireEvent('select', dor_combo, record, 0);
							return false;
						}
					});
				}
                if(dor_combo.getStore().getCount()==0)
                {
                    if(that.Sin_check == 1)
                        sw.swMsg.alert('Внимание!', 'В учетных данных склада нет товарных позиций с указанным кодом EAN или соответствующих выписанному ЛП. Попробуйте выбрать ЛП вручную.');
                    else
                        sw.swMsg.alert('Внимание!', 'В учетных данных склада нет товарных позиций с указанным кодом EAN. Попробуйте выбрать ЛП вручную.');
                that.hide();
                return false;
                }
				else if (dor_combo.getStore().getCount() > 1)
				{
					//wnd.form.findField('Kolvo').setValue('');
					//wnd.form.findField('Kolvo').enable();
				}
				//wnd.setInformationValues();
			}
		});
	},
	title: lang['vyibor_partii'],
	//width: 800,
	width: 500,
	height: 220,
	initComponent: function() {
		var wnd = this;

		var form = new Ext.form.FormPanel({
			autoScroll: true,
			bodyStyle: 'padding: 0.5em;',
			//autoHeight: true,
			border: false,
			frame: true,
			layount: 'form',
			items: [{
				xtype: 'combo',
				hiddenName: 'DrugOstatRegistry_id',
				fieldLabel: lang['partiya'],
				displayField: 'DrugShipment_Name',
				valueField: 'DrugOstatRegistry_id',
				enableKeyEvents: true,
				editable: false,
				forceSelection: true,
				triggerAction: 'all',
				allowBlank: false,
				anchor: '95%',
				listWidth: 800,
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
						id: 'DrugOstatRegistry_id'
					}, [
						{name: 'DrugOstatRegistry_id', mapping: 'DrugOstatRegistry_id'},
						{name: 'DocumentUcStr_id', mapping: 'DocumentUcStr_id'},
						{name: 'DrugOstatRegistry_Kolvo', mapping: 'DrugOstatRegistry_Kolvo'},
						{name: 'DrugOstatRegistry_Cost', mapping: 'DrugOstatRegistry_Cost'},
						{name: 'DocumentUcStr_Price', mapping: 'DocumentUcStr_Price'},
						{name: 'DrugNds_id', mapping: 'DrugNds_id'},
						{name: 'DocumentUcStr_IsNDS', mapping: 'DocumentUcStr_IsNDS'},
						{name: 'PrepSeries_id', mapping: 'PrepSeries_id'},
						{name: 'PrepSeries_Ser', mapping: 'PrepSeries_Ser'},
						{name: 'PrepSeries_GodnDate', mapping: 'PrepSeries_GodnDate'},
						{name: 'PrepSeries_isDefect', mapping: 'PrepSeries_isDefect'},
						{name: 'DrugNds_id', mapping: 'DrugNds_id'},
						{name: 'DrugNds_Code', mapping: 'DrugNds_Code'},
						{name: 'DrugFinance_Name', mapping: 'DrugFinance_Name'},
						{name: 'WhsDocumentCostItemType_Name', mapping: 'WhsDocumentCostItemType_Name'},
						{name: 'DrugRls_id', mapping: 'Drug_id'},
						{name: 'Drug_Name', mapping: 'Drug_Name'},
						{name: 'Drug_ShortName', mapping: 'Drug_ShortName'},
						{name: 'DrugShipment_Name', mapping: 'DrugShipment_Name'},
						//{name: 'Okei_NationSymbol', mapping: 'Okei_NationSymbol'},
                        {name: 'Finance_and_CostItem', mapping: 'Finance_and_CostItem'},
                        {name: 'WhsDocumentSupplySpecDrug_Coeff', mapping: 'WhsDocumentSupplySpecDrug_Coeff'},
                        {name: 'GoodsPackCount_Count', mapping: 'GoodsPackCount_Count'},
                        {name: 'GoodsUnit_id', mapping: 'GoodsUnit_id'},
                        {name: 'GoodsUnit_Nick', mapping: 'GoodsUnit_Nick'}
					]),
					url: '/?c=Farmacy&m=getDrugOstatForProvideFromBarcode'
				}),
				tpl: new Ext.XTemplate(
					'<table cellpadding="0" cellspacing="0" style="width: 100%;"><tr style="font-family: tahoma; font-size: 10pt; font-weight: bold;">',
                    '<td style="padding: 2px; width: 10%;">Серия</td>',
                    '<td style="padding: 2px; width: 10%;">Срок годности</td>',
                    '<td style="padding: 2px; width: 10%;">Цена</td>',
                    '<td style="padding: 2px; width: 10%;">Партия</td>',
					'<td style="padding: 2px; width: 10%;">Остаток</td>',
					'<td style="padding: 2px; width: 10%;">Ед.учета</td>',
                    '<td style="padding: 2px; width: 10%;">Ст.расхода</td>',
					'<td style="padding: 2px; width: 10%;">Ист.фин.</td>',
                    '<td style="padding: 2px; width: 20%;">Медикамент</td>',
                    //'<td style="padding: 2px; width: 10%;">Ставка НДС</td>',
					'</tr><tpl for="."><tr class="x-combo-list-item">',
					'<td style="padding: 2px;">{[(values.PrepSeries_isDefect == 1) ? "<font color=#ff0000>"+values.PrepSeries_Ser+"</font>" : values.PrepSeries_Ser]}&nbsp;</td>',
					'<td style="padding: 2px;">{PrepSeries_GodnDate}&nbsp;</td>',
					'<td style="padding: 2px;">{DrugOstatRegistry_Cost}&nbsp;</td>',
                    '<td style="padding: 2px;">{DrugShipment_Name}&nbsp;</td>',
					'<td style="padding: 2px; text-align: left;">{DrugOstatRegistry_Kolvo}&nbsp;</td>',
					'<td style="padding: 2px; text-align: left;">{GoodsUnit_Nick}&nbsp;</td>',
					'<td style="padding: 2px;">{WhsDocumentCostItemType_Name}&nbsp;</td>',
                    '<td style="padding: 2px;">{DrugFinance_Name}&nbsp;</td>',
                    '<td style="padding: 2px; width: 20%; text-overflow: clip;">{Drug_ShortName}&nbsp;</td>',
                    //'<td style="padding: 2px;">{DrugNds_Code}&nbsp;</td>',
					'</tr></tpl>',
					'</table>'
				),
				listeners: {
					'beforeselect': function() {
						return true;
					}.createDelegate(this),
					'select': function() {
						wnd.setInformationValues();
					}
				}
			}, {
				xtype: 'textfield',
				fieldLabel: lang['seriya'],
				name: 'PrepSeries_Ser',
				anchor: '95%',
				disabled: true
			}, {
				xtype: 'textfield',
				fieldLabel: lang['srok_godnosti'],
				name: 'PrepSeries_GodnDate',
				anchor: '95%',
				disabled: true
			}, {
				xtype: 'textfield',
				fieldLabel: lang['tsena'],
				name: 'DrugOstatRegistry_Cost',
				anchor: '95%',
				disabled: true
			}, {
				xtype: 'textfield',
				fieldLabel: langs('Ед.учета'),
				name: 'GoodsUnit_Nick',
				anchor: '95%',
				disabled: true
			}, {
				name: 'Kolvo',
				fieldLabel: lang['kolichestvo'],
				xtype: 'numberfield',
				allowNegative: false,
				anchor: '95%',
				allowBlank: false,
                disabled: false
			}]
		});

		Ext.apply(this, {
			layout: 'fit',
			buttons: [{
				handler: function() {
					this.doSelect();
				}.createDelegate(this),
				iconCls: 'save16',
				text: lang['sohranit']
			}, {
				text: '-'
			},
			HelpButton(this),
			{
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'cancel16',
				text: BTN_FRMCANCEL
			}],
			frame: true,
 			items: [form]
		});

		this.base_form = form;
		this.form = form.getForm();

		sw.Promed.swEvnReceptRlsProvideEditWindow.superclass.initComponent.apply(this, arguments);
	}
});