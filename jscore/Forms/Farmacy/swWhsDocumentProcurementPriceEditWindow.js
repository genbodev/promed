/**
* swWhsDocumentProcurementPriceEditWindow - окно расчета закупочной цены
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Farmacy
* @access       public
* @copyright    Copyright (c) 2016 Swan Ltd.
* @author       Salakhov R.
* @version      03.2016
* @comment      
*/
sw.Promed.swWhsDocumentProcurementPriceEditWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: false,
	title: lang['raschet_zakupochnoi_ceny'],
	layout: 'border',
	id: 'WhsDocumentProcurementPriceEditWindow',
	modal: true,
	shim: false,
	width: 400,
	resizable: false,
	maximizable: true,
	maximized: true,
    calculateVariationCoefficient: function () {
        var k = 0;
        var price_array = new Array();
        var price_sum = 0;

        this.OfferGrid.getGrid().getStore().each(function(record) {
            price = record.get('CommercialOfferDrug_Price');
            if (record.get('state') != 'delete' && !Ext.isEmpty(price)) {
                price = price*1;
                price_array.push(price);
                price_sum += price;
            }
        });

        this.SupplyGrid.getGrid().getStore().each(function(record) {
            price = record.get('WhsDocumentSupplySpec_Price');
            if (record.get('state') != 'delete' && !Ext.isEmpty(price)) {
                price = price*1;
                price_array.push(price);
                price_sum += price;
            }
        });

        if (price_array.length > 1) { //необходимо минимум 2 записи
            var n = price_array.length;
            var avg = price_sum/n;
            var q = 0;
            var sum_quad = 0;

            for(var i = 0; i < n; i++) {
                sum_quad += Math.pow(price_array[i]-avg, 2);
            }

            q = Math.sqrt(sum_quad/(n - 1));

            k = (q/avg)*100;
        } else {
            sw.swMsg.alert(lang['oshibka'], lang['nedostatochno_zapisei_dlia_rascheta']);
        }

        var avg = price_sum/

        this.price_form.findField('VariationCoefficient').setValue(k != 0 ? k.toFixed(2) : null);
    },
    calculateTotalPrice: function () {
        var kolvo = this.price_form.findField('TotalKolvo').getValue()*1;
        var total_price = 0;
        var total_sum = 0;
        var cpt_field = this.drug_form.findField('CalculatPriceType_id');
        var cpt_code = '0';

        if (cpt_field.getValue() > 0) {
            cpt_code = cpt_field.getStore().getAt(cpt_field.getStore().findBy(function(rec) { return rec.get('CalculatPriceType_id') == cpt_field.getValue(); })).get('CalculatPriceType_Code');
            cpt_code = String(cpt_code);
        }

        //сбор данных о ценах
        var price = 0;
        var tariff_price = new Array();
        var offer_price = new Array();
        var supply_price = new Array();
        var tariff_sum = 0;
        var tariff_min = 0;
        var offer_sum = 0;
        var supply_sum = 0;

        this.TariffGrid.getGrid().getStore().each(function(record) {
            price = record.get('WhsDocumentProcurementPriceLink_PriceRub');
            if (record.get('state') != 'delete' && !Ext.isEmpty(price)) {
                price = price*1;
                tariff_price.push(price);
                tariff_sum += price;
                if (tariff_min == 0 || price < tariff_min) {
                    tariff_min = price;
                }
            }
        });

        this.OfferGrid.getGrid().getStore().each(function(record) {
            price = record.get('CommercialOfferDrug_Price');
            if (record.get('state') != 'delete' && !Ext.isEmpty(price)) {
                price = price*1;
                offer_price.push(price);
                offer_sum += price;
            }
        });

        this.SupplyGrid.getGrid().getStore().each(function(record) {
            price = record.get('WhsDocumentSupplySpec_Price');
            if (record.get('state') != 'delete' && !Ext.isEmpty(price)) {
                price = price*1;
                supply_price.push(price);
                supply_sum += price;
            }
        });

        //проверки и расчет
        switch(cpt_code) {
            case '1': //метод сопоставимых рыночных цен
                if (offer_price.length > 0 || supply_price.length > 0) {
                    total_price = (offer_sum + supply_sum)/(offer_price.length + supply_price.length);
                } else {
                    sw.swMsg.alert(lang['oshibka'], lang['net_zapisei_dlia_rascheta']);
                }
                break;
            case '2': //тарифный метод
                if (tariff_price.length <= 0) {
                    sw.swMsg.alert(lang['oshibka'], lang['raschet_ne_vozmozhen_otmette_tarify_dlia_rascheta_ceny']);
                }
                if (tariff_price.length == 1 || tariff_price.length == 3) {
                    total_price = tariff_sum/tariff_price.length;
                }
                if (tariff_price.length == 2) {
                    total_price = tariff_min;
                }
                if (tariff_price.length >= 4) {
                    sw.swMsg.alert(lang['oshibka'], lang['kolichestvo_zapisei_vybrannykh_dlia_rascheta_tarifa_prevyshaet_3_umenshite_kolichestvo_tarifov']);
                }
                break;
            default:
            sw.swMsg.alert(lang['oshibka'], lang['ne_vybran_metod_rascheta']);
            break;
        }

        this.price_form.findField('TotalPrice').setValue(total_price != 0 ? total_price.toFixed(2) : null);
        this.calculateTotalSum();
    },
    calculateTotalSum: function () {
        var kolvo = this.price_form.findField('TotalKolvo').getValue()*1;
        var total_price = this.price_form.findField('TotalPrice').getValue()*1;
        var gpc_count = this.price_form.findField('GoodsPackCount_Count').getValue()*1;
        var total_sum = kolvo*total_price;
        var gpc_price = 0;

        if (gpc_count > 0 && total_price > 0) {
            gpc_price = total_price/gpc_count;
        }

        this.price_form.findField('TotalSum').setValue(total_sum != 0 ? total_sum.toFixed(2) : null);
        this.price_form.findField('GoodsPackCount_Price').setValue(gpc_price != 0 ? gpc_price.toFixed(2) : null);
    },
    setDefaultCalculationDate: function () {
        var date_field = this.price_form.findField('CalculationDate');

        if (Ext.isEmpty(date_field.getValue())) {
            date_field.setValue((new Date).format('d.m.Y'));
        }
    },
    showTariffGrid: function(show) {
        if (show) {
            this.TariffGrid.ownerCt.show();
            this.TariffGrid.recalculateTariff();
            this.doLayout();
        } else {
            this.TariffGrid.ownerCt.hide();
            this.doLayout();
        }
    },
    showOfferGrid: function(show) {
        if (show) {
            this.OfferGrid.show();
            this.doLayout();
        } else {
            this.OfferGrid.hide();
            this.doLayout();
        }
    },
    setDefaultValues: function() {
        var cpt_combo = this.drug_form.findField('CalculatPriceType_id');

        this.drug_form.setValues(this.params);
        if (Ext.isEmpty(cpt_combo.getValue())) {
            var cpt_code = this.params.InJnvlp > 0 ? '2' : '1'; //1 - Метод сопоставимых рыночных цен; 2 - Тарифный метод.
            var cpt_id = cpt_combo.getStore().getAt(cpt_combo.getStore().findBy(function(rec) { return rec.get('CalculatPriceType_Code') == cpt_code; })).get('CalculatPriceType_id');
            cpt_combo.setValue(cpt_id);
            this.showTariffGrid(cpt_code == '2');
            this.showOfferGrid(cpt_code == '1');
        }

        this.tariff_form.findField('PriceType_SysNick').setValue('wholesale_nds'); // wholesale_nds - оптовая с НДС
        this.setPriceFormSettings();
    },
    setPriceFormSettings: function() {
        var show_uot_fields = Ext.isEmpty(this.WhsDocumentProcurementRequestSpec_id);
        var show_varoation_field = true;

        var cpt_combo = this.drug_form.findField('CalculatPriceType_id');
        var cpt_id = cpt_combo.getValue();
        var cpt_code = cpt_id > 0 ? cpt_combo.getStore().getAt(cpt_combo.getStore().findBy(function(rec) { return rec.get('CalculatPriceType_id') == cpt_id; })).get('CalculatPriceType_Code') : '0';

        cpt_code = String(cpt_code);

        if (show_uot_fields) {
            this.price_form.findField('GoodsPackCount_Price').ownerCt.hide();
        } else {
            this.price_form.findField('GoodsPackCount_Price').ownerCt.show();
        }

        if (cpt_code == '1') {
            this.price_form.findField('VariationCoefficient').ownerCt.ownerCt.show();
        } else {
            this.price_form.findField('VariationCoefficient').ownerCt.ownerCt.hide();
        }

        //автоподгон высоты панели
        var price_form_height = this.price_form.getEl().getHeight();
        var price_panel = this.findById('WhsDocumentProcurementPriceEditPriceForm').ownerCt;

        price_panel.setHeight(price_form_height+18);
        this.doLayout();
    },
    confirmAction: function (message, callback) {
        sw.swMsg.show({
            icon: Ext.MessageBox.QUESTION,
            msg: message,
            title: lang['podtverjdenie'],
            buttons: Ext.Msg.YESNO,
            fn: function(buttonId, text, obj) {
                if ('yes' == buttonId) {
                    callback();
                }
            }
        });
    },
	doSave:  function() {
		var wnd = this;
		if ( !this.price_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					wnd.findById('WhsDocumentProcurementPriceEditPriceForm').getFirstInvalidEl().focus(true);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

        var params = new Object();

        params.TariffJsonData = this.TariffGrid.getJSONChangedData();
        params.OfferJsonData = this.OfferGrid.getJSONChangedData();
        params.SupplyJsonData = this.SupplyGrid.getJSONChangedData();
        params.DrugRequestPurchaseSpec_id = this.DrugRequestPurchaseSpec_id;
        params.WhsDocumentProcurementRequestSpec_id = this.WhsDocumentProcurementRequestSpec_id;
        params.CalculatPriceType_id = this.drug_form.findField('CalculatPriceType_id').getValue();
        params.TotalPrice = this.price_form.findField('TotalPrice').getValue();
        params.CalculationDate = this.price_form.findField('CalculationDate').getValue();
        if (!Ext.isEmpty(params.CalculationDate)) {
            params.CalculationDate = params.CalculationDate.format('d.m.Y');
        }

        //swalert(params);
        //return false;

        //wnd.getLoadMask(lang['podojdite_idet_sohranenie']).show();
        Ext.Ajax.request({
            url: '/?c=MzDrugRequest&m=saveWhsDocumentProcurementPrice',
            params: params,
            failure: function() {
                //wnd.getLoadMask().hide();
            },
            success: function(response){
                var result = Ext.util.JSON.decode(response.responseText);
                //wnd.getLoadMask().hide();
                if (result && result.success) {
                    if (typeof wnd.callback == 'function' ) {
                        wnd.callback(wnd.owner);
                    }
                    wnd.hide();
                }
            }
        });

        return true;
	},
	show: function() {
        var wnd = this;
		sw.Promed.swWhsDocumentProcurementPriceEditWindow.superclass.show.apply(this, arguments);		
		this.action = '';
		this.callback = Ext.emptyFn;
		this.DrugRequestPurchaseSpec_id = null;
		this.WhsDocumentProcurementRequestSpec_id = null;
		this.params = new Object();

        if ( !arguments[0] || (Ext.isEmpty(arguments[0].DrugRequestPurchaseSpec_id) && Ext.isEmpty(arguments[0].WhsDocumentProcurementRequestSpec_id)) ) {
            sw.swMsg.alert(lang['oshibka'], lang['ne_ukazanyi_vhodnyie_dannyie'], function() { wnd.hide(); });
            return false;
        }
		if ( arguments[0].action ) {
			this.action = arguments[0].action;
		}
		if ( arguments[0].callback && typeof arguments[0].callback == 'function' ) {
			this.callback = arguments[0].callback;
		}
		if ( arguments[0].owner ) {
			this.owner = arguments[0].owner;
		}
		if ( arguments[0].DrugRequestPurchaseSpec_id ) {
			this.DrugRequestPurchaseSpec_id = arguments[0].DrugRequestPurchaseSpec_id;
		}
		if ( arguments[0].WhsDocumentProcurementRequestSpec_id ) {
			this.WhsDocumentProcurementRequestSpec_id = arguments[0].WhsDocumentProcurementRequestSpec_id;
		}
		if ( arguments[0].params ) {
			this.params = arguments[0].params;
		}

        this.drug_form.reset();
        this.tariff_form.reset();
        this.price_form.reset();
        this.setDefaultValues();

        this.TariffGrid.setParam('UserOrg_id', getGlobalOptions().org_id);
        this.TariffGrid.setParam('DrugRequestPurchaseSpec_id', this.DrugRequestPurchaseSpec_id);
        this.TariffGrid.setParam('DrugRequestPurchaseSpec_id', this.DrugRequestPurchaseSpec_id, false);
        this.TariffGrid.setParam('WhsDocumentProcurementRequestSpec_id', this.WhsDocumentProcurementRequestSpec_id);
        this.TariffGrid.setParam('WhsDocumentProcurementRequestSpec_id', this.WhsDocumentProcurementRequestSpec_id, false);
        this.TariffGrid.setParam('onSelect', function(data) {wnd.TariffGrid.addRecords(data);}, false);
        
        this.OfferGrid.setParam('UserOrg_id', getGlobalOptions().org_id);
        this.OfferGrid.setParam('DrugRequestPurchaseSpec_id', this.DrugRequestPurchaseSpec_id);
        this.OfferGrid.setParam('DrugRequestPurchaseSpec_id', this.DrugRequestPurchaseSpec_id, false);
        this.OfferGrid.setParam('WhsDocumentProcurementRequestSpec_id', this.WhsDocumentProcurementRequestSpec_id);
        this.OfferGrid.setParam('WhsDocumentProcurementRequestSpec_id', this.WhsDocumentProcurementRequestSpec_id, false);
        this.OfferGrid.setParam('onSelect', function(data) {wnd.OfferGrid.addRecords(data);}, false);
        
        this.SupplyGrid.setParam('UserOrg_id', getGlobalOptions().org_id);
        this.SupplyGrid.setParam('DrugRequestPurchaseSpec_id', this.DrugRequestPurchaseSpec_id);
        this.SupplyGrid.setParam('DrugRequestPurchaseSpec_id', this.DrugRequestPurchaseSpec_id, false);
        this.SupplyGrid.setParam('WhsDocumentProcurementRequestSpec_id', this.WhsDocumentProcurementRequestSpec_id);
        this.SupplyGrid.setParam('WhsDocumentProcurementRequestSpec_id', this.WhsDocumentProcurementRequestSpec_id, false);
        this.SupplyGrid.setParam('onSelect', function(data) {wnd.SupplyGrid.addRecords(data);}, false);
        
        this.TariffGrid.loadData();
        this.OfferGrid.loadData();
        this.SupplyGrid.loadData();

        if(!wnd.TariffGrid.getAction('action_wdppe_tariff_delete')) {
            wnd.TariffGrid.addActions({
                name:'action_wdppe_tariff_delete',
                text: lang['udalit'],
                menu: [{
                    name: 'action_tariff_delete',
                    text: lang['udalit'],
                    tooltip: lang['udalit'],
                    handler: function() {
                        wnd.confirmAction(lang['vyi_hotite_udalit_zapis'], function() {wnd.TariffGrid.deleteRecord();});
                    },
                    iconCls: 'delete16'
                }, {
                    name: 'action_tariff_delete_all',
                    text: lang['udalit_vse'],
                    tooltip: lang['udalit_vse'],
                    handler: function() {
                        wnd.confirmAction(lang['vy_hotite_udalit_vse_zapisi'], function() {wnd.TariffGrid.deleteAllRecords();});
                    },
                    iconCls: 'delete16'
                }],
                iconCls: 'delete16'
            }, 2);
        }

        if(!wnd.OfferGrid.getAction('action_wdppe_offer_delete')) {
            wnd.OfferGrid.addActions({
                name:'action_wdppe_offer_delete',
                text:lang['udalit'],
                menu: [{
                    name: 'action_offer_delete',
                    text: lang['udalit'],
                    tooltip: lang['udalit'],
                    handler: function() {
                        wnd.confirmAction(lang['vyi_hotite_udalit_zapis'], function() {wnd.OfferGrid.deleteRecord();});
                    },
                    iconCls: 'delete16'
                }, {
                    name: 'action_offer_delete_all',
                    text: lang['udalit_vse'],
                    tooltip: lang['udalit_vse'],
                    handler: function() {
                        wnd.confirmAction(lang['vy_hotite_udalit_vse_zapisi'], function() {wnd.OfferGrid.deleteAllRecords();});
                    },
                    iconCls: 'delete16'
                }],
                iconCls: 'delete16'
            }, 2);
        }

        if(!wnd.SupplyGrid.getAction('action_wdppe_supply_delete')) {
            wnd.SupplyGrid.addActions({
                name:'action_wdppe_supply_delete',
                text:lang['udalit'],
                menu: [{
                    name: 'action_supply_delete',
                    text: lang['udalit'],
                    tooltip: lang['udalit'],
                    handler: function() {
                        wnd.confirmAction(lang['vyi_hotite_udalit_zapis'], function() {wnd.SupplyGrid.deleteRecord();});
                    },
                    iconCls: 'delete16'
                }, {
                    name: 'action_supply_delete_all',
                    text: lang['udalit_vse'],
                    tooltip: lang['udalit_vse'],
                    handler: function() {
                        wnd.confirmAction(lang['vy_hotite_udalit_vse_zapisi'], function() {wnd.SupplyGrid.deleteAllRecords();});
                    },
                    iconCls: 'delete16'
                }],
                iconCls: 'delete16'
            }, 2);
        }

        var loadMask = new Ext.LoadMask(this.price_form.getEl(), {msg:'Загрузка...'});
        //loadMask.show();

        Ext.Ajax.request({
            params:{
                DrugRequestPurchaseSpec_id: wnd.DrugRequestPurchaseSpec_id,
                WhsDocumentProcurementRequestSpec_id: wnd.WhsDocumentProcurementRequestSpec_id
            },
            success: function (response) {
                var result = Ext.util.JSON.decode(response.responseText);
                if (result[0]) {
                    wnd.price_form.setValues(result[0]);
                    wnd.setDefaultCalculationDate();
                    wnd.calculateTotalSum();
                }
                //loadMask.hide();
            },
            failure:function () {
                sw.swMsg.alert(lang['oshibka'], lang['ne_udalos_poluchit_dannyie_s_servera']);
                //loadMask.hide();
            },
            url:'/?c=MzDrugRequest&m=loadWhsDocumentProcurementPrice'
        });
	},
	initComponent: function() {
		var wnd = this;		
		
		var drug_form = new Ext.Panel({
            region: 'north',
            autoScroll: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			height: 110,
			border: false,			
			frame: true,
			items: [{
				xtype: 'form',
				autoHeight: true,
				id: 'WhsDocumentProcurementPriceEditDrugForm',
				bodyStyle:'background:#DFE8F6;padding:0px;',
				border: false,
				labelWidth: 200,
				collapsible: true,
				url:'/?c=WhsDocumentProcurementPrice&m=save',
				items: [{
                    title: lang['medikament'],
                    xtype: 'fieldset',
                    autoHeight: true,
                    labelAlign: 'right',
                    labelWidth: 120,
                    items: [{
                        xtype: 'textfield',
                        fieldLabel: lang['naimenovanie'],
                        name: 'Drug_Name',
                        anchor: '100%',
                        disabled: true
                    }, {
                        layout: 'column',
                        items: [{
                            layout: 'form',
                            items: [{
                                xtype: 'checkbox',
                                fieldLabel: lang['jnvlp'],
                                name: 'InJnvlp',
                                disabled: true
                            }]
                        }, {
                            layout: 'form',
                            items: [{
                                xtype: 'swcommonsprcombo',
                                fieldLabel: lang['metod_rascheta'],
                                name: 'CalculatPriceType_id',
                                comboSubject: 'CalculatPriceType',
                                width: 250,
                                listeners: {
                                    select: function(combo, record) {
                                        var cpt_code = record.get('CalculatPriceType_Code');
                                        cpt_code = String(cpt_code);
                                        wnd.showTariffGrid(cpt_code == '2');
                                        wnd.showOfferGrid(cpt_code == '1');
                                        wnd.setPriceFormSettings();
                                    }
                                }
                            }]
                        }]
                    }]
                }]
			}]
		});

		var tariff_form = new Ext.Panel({
            region: 'north',
            autoScroll: true,
			bodyBorder: false,
			bodyStyle: 'padding: 0px',
			height: 33,
			border: false,
			frame: true,
			items: [{
				xtype: 'form',
				autoHeight: true,
				id: 'WhsDocumentProcurementPriceEditTariffForm',
				bodyStyle:'background:#DFE8F6;padding:0px;',
				border: false,
                labelAlign: 'right',
				labelWidth: 136,
				items: [{
                    xtype: 'combo',
                    fieldLabel: lang['vid_ceny'],
                    hiddenName: 'PriceType_SysNick',
                    allowBlank: false,
                    mode: 'local',
                    store: new Ext.data.SimpleStore({
                        key: 'PriceType_id',
                        fields:
                            [
                                {name: 'PriceType_id', type: 'int'},
                                {name: 'PriceType_SysNick', type: 'string'},
                                {name: 'PriceType_Name', type: 'string'}
                            ],
                        data: [
                            [1, 'base', 'Цена производителя'],
                            [2, 'wholesale_nds', lang['optovaya_s_nds']],
                            [3, 'wholesale', lang['optovaia_bez_nds']],
                            [4, 'retail_nds', lang['roznichnaya_s_nds']],
                            [5, 'retail', lang['roznichnaia_bez_nds']]
                        ]
                    }),
                    editable: false,
                    triggerAction: 'all',
                    displayField: 'PriceType_Name',
                    valueField: 'PriceType_SysNick',
                    tpl: '<tpl for="."><div class="x-combo-list-item">{PriceType_Name}</div></tpl>',
                    listeners: {
                        select: function() {
                            wnd.TariffGrid.recalculateTariff();
                        }
                    }
                }]
			}]
		});

		var price_form = new Ext.Panel({
            region: 'south',
            autoScroll: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			height: 182,
			border: false,
			frame: true,
			labelAlign: 'right',
			items: [{
				xtype: 'form',
				autoHeight: true,
				id: 'WhsDocumentProcurementPriceEditPriceForm',
				bodyStyle:'background:#DFE8F6;padding:0px;',
				border: false,
				labelWidth: 200,
				collapsible: true,
				url:'/?c=WhsDocumentProcurementPrice&m=save',
				items: [{
                    title: 'Цена',
					xtype: 'fieldset',
                    autoHeight: true,
                    labelAlign: 'right',
					items: [{
                        xtype: 'hidden',
                        fieldLabel: 'Количество',
                        name: 'TotalKolvo'
                    }, {
                        layout: 'column',
                        items: [{
                            layout: 'form',
                            items: [{
                                xtype: 'textfield',
                                fieldLabel: 'Коэффициент вариации (%)',
                                name: 'VariationCoefficient',
                                disabled: true
                            }]
                        }, {
                            layout: 'form',
                            items: [{
                                xtype: 'button',
                                text: 'Рассчитать',
                                iconCls: null,
                                style: 'margin-left: 3px;',
                                width: 80,
                                handler: function() {
                                    wnd.calculateVariationCoefficient();
                                }
                            }]
                        }]
                    }, {
                        layout: 'column',
                        items: [{
                            layout: 'form',
                            items: [{
                                xtype: 'textfield',
                                fieldLabel: 'Цена за уп.',
                                name: 'TotalPrice',
                                allowBlank: false,
                                listeners: {
                                    'change': function() {
                                        wnd.calculateTotalSum();
                                    }
                                }
                            }]
                        }, {
                            layout: 'form',
                            items: [{
                                xtype: 'button',
                                text: 'Рассчитать',
                                iconCls: null,
                                style: 'margin-left: 3px;',
                                width: 80,
                                handler: function() {
                                    wnd.calculateTotalPrice();
                                }
                            }]
                        }, {
                            layout: 'form',
                            items: [{
                                xtype: 'swdatefield',
                                fieldLabel: 'Дата расчета цены',
                                name: 'CalculationDate',
                                allowBlank: false
                            }]
                        }]
                    }, {
                        layout: 'form',
                        items: [{
                            layout: 'column',
                            items: [{
                                layout: 'form',
                                items: [{
                                    xtype: 'textfield',
                                    fieldLabel: 'Ед.измерения',
                                    name: 'Okei_Name',
                                    disabled: true
                                }]
                            }, {
                                layout: 'form',
                                labelWidth: 283,
                                items: [{
                                    xtype: 'textfield',
                                    fieldLabel: 'Кол-во ед.изм. в уп.',
                                    name: 'GoodsPackCount_Count',
                                    disabled: true
                                }]
                            }]
                        }, {
                            xtype: 'textfield',
                            fieldLabel: 'Цена за ед.измерения',
                            name: 'GoodsPackCount_Price',
                            disabled: true
                        }]
                    }, {
                        xtype: 'textfield',
                        fieldLabel: 'НМЦК',
                        name: 'TotalSum',
                        disabled: true
                    }]
                }]
			}]
		});

        this.TariffGrid = new sw.Promed.ViewFrame({
            region: 'center',
            actions: [
                {name: 'action_add'},
                {name: 'action_edit', hidden: true, disabled: true},
                {name: 'action_view', hidden: true, disabled: true},
                {name: 'action_delete', hidden: true, disabled: true},
                {name: 'action_refresh', hidden: true, disabled: true},
                {name: 'action_print'},
                {name: 'action_save', hidden: true, disabled: true}
            ],
            autoExpandColumn: 'autoexpand',
            autoExpandMin: 125,
            autoLoadData: false,
            border: true,
            dataUrl: '/?c=MzDrugRequest&m=loadWhsDocumentProcurementPriceLinkList',
            height: 180,
            object: 'WhsDocumentProcurementPriceLink',
            unique_object: 'Nomen',
            editformclassname: 'swWhsDocumentProcurementPriceLinkAddWindow',
            id: 'wdppeTariffGrid',
            paging: false,
            style: 'margin-bottom: 10px',
            stringfields: [
                { name: 'WhsDocumentProcurementPriceLink_id', type: 'int', header: 'ID', key: true },
                { name: 'state', hidden: true },
                { name: 'Nomen_id', hidden: true },
                { name: 'WhsDocumentProcurementPriceLink_PriceRub', type: 'money', editor: new Ext.form.NumberField(), header: 'Цена производителя' },
                { name: 'WhsDocumentProcurementPriceLink_PriceDate', type: 'date', editor: new Ext.form.DateField(), header: 'Дата регистрации цены' },
                { name: 'Tariff', type: 'money', header: 'Тариф' },
                { name: 'Wholesale', hidden: true },
                { name: 'Retail', hidden: true },
                { name: 'DrugPrepFas_Name', type: 'string', header: lang['naimenovanie'], id: 'autoexpand' }
            ],
            title: false,
            toolbar: true,
            editing: true,
            saveAtOnce: false,
            recalculateTariff: function(id) {
                var all_records = Ext.isEmpty(id);
                var nds = 1.1;
                var price_type = wnd.tariff_form.findField('PriceType_SysNick').getValue();

                this.getGrid().getStore().each(function(record) {
                    var nomen_id = record.get('Nomen_id');
                    if (nomen_id > 0 && (all_records || nomen_id == id)) {
                        var price = record.get('WhsDocumentProcurementPriceLink_PriceRub')*1;
                        var whs = record.get('Wholesale')/100;
                        var ret = record.get('Retail')/100;
                        var tariff = 0;

                        switch(price_type) {
                            case 'base':
                                tariff = price;
                                break;
                            case 'wholesale':
                                tariff = price+(Math.round(price*whs*100)/100);
                                break;
                            case 'wholesale_nds':
                                tariff = (price+(Math.round(price*whs*100)/100))*nds;
                                break;
                            case 'retail':
                                tariff = price+(Math.round(price*whs*100)/100)+(Math.round(price*ret*100)/100);
                                break;
                            case 'retail_nds':
                                tariff = (price+(Math.round(price*whs*100)/100)+(Math.round(price*ret*100)/100))*nds;
                                break;
                        }

                        record.set('Tariff', tariff.toFixed(2));
                        record.commit();
                    }
                });
            },
            onAfterEdit: function(o) {
                this.recalculateTariff(o.record.get('Nomen_id'));
                if (o.record.get('state') != 'add') {
                    o.record.set('state', 'edit');
                    o.record.commit();
                }
            },
            onLoadData: function() {
                this.recalculateTariff();
            },
            deleteRecord: function(){
                var view_frame = this;
                var object_field = view_frame.object+'_id';
                var selected_record = view_frame.getGrid().getSelectionModel().getSelected();

                if (selected_record && selected_record.get(object_field) > 0) {
                    if (selected_record.get('state') == 'add') {
                        view_frame.getGrid().getStore().remove(selected_record);
                    } else {
                        selected_record.set('state', 'delete');
                        selected_record.commit();
                        view_frame.setFilter();
                    }
                }
            },
            deleteAllRecords: function(){
                var view_frame = this;
                var object_field = view_frame.object+'_id';

                view_frame.getGrid().getStore().each(function(record) {
                    if (record.get('state') == 'add') {
                        view_frame.getGrid().getStore().remove(record);
                    } else if (record.get(object_field) > 0) {
                        record.set('state', 'delete');
                        record.commit();
                    }
                });
                view_frame.setFilter();
            },
            addRecords: function(data_arr){
                var view_frame = this;
                var store = view_frame.getGrid().getStore();
                var record_count = store.getCount();
                var record = new Ext.data.Record.create(view_frame.jsonData['store']);
                var object_field = view_frame.object+'_id';
                var unique_object_field = view_frame.unique_object+'_id';

                if ( record_count == 1 && !store.getAt(0).get(object_field) ) {
                    view_frame.removeAll({addEmptyRecord: false});
                    record_count = 0;
                }

                view_frame.clearFilter();
                for (var i = 0; i < data_arr.length; i++) {
                    var idx = store.findBy(function(rec) { return rec.get(unique_object_field) == data_arr[i][unique_object_field]; });
                    if (idx < 0 || store.getAt(idx).get('state') == 'delete') {
                        data_arr[i][object_field] = Math.floor(Math.random()*1000000); //генерируем временный идентификатор
                        data_arr[i].state = 'add';
                        store.insert(record_count, new record(data_arr[i]));
                    }
                }
                view_frame.setFilter();
                view_frame.recalculateTariff();
            },
            getChangedData: function(){ //возвращает новые и измненные данные
                var data = new Array();
                this.clearFilter();
                this.getGrid().getStore().each(function(record) {
                    if ((record.data.state == 'add' || record.data.state == 'edit' ||  record.data.state == 'delete')) {
                        data.push(record.data);
                    }
                });
                this.setFilter();
                return data;
            },
            getJSONChangedData: function(){ //возвращает новые и измененные записи в виде закодированной JSON строки
                var dataObj = this.getChangedData();
                return dataObj.length > 0 ? Ext.util.JSON.encode(dataObj) : "";
            },
            clearFilter: function() { //очищаем фильтры
                this.getGrid().getStore().clearFilter();
            },
            setFilter: function() { //скрывает удаленные записи
                this.getGrid().getStore().filterBy(function(record){
                    return (record.get('state') != 'delete');
                });
            }
        });

        this.OfferGrid = new sw.Promed.ViewFrame({
            region: 'north',
            actions: [
                {name: 'action_add'},
                {name: 'action_edit', hidden: true, disabled: true},
                {name: 'action_view', hidden: true, disabled: true},
                {name: 'action_delete', hidden: true, disabled: true},
                {name: 'action_refresh', hidden: true, disabled: true},
                {name: 'action_print'}
            ],
            autoExpandColumn: 'autoexpand',
            autoExpandMin: 125,
            autoLoadData: false,
            border: true,
            dataUrl: '/?c=MzDrugRequest&m=loadWhsDocumentCommercialOfferDrugList',
            height: 180,
            object: 'WhsDocumentCommercialOfferDrug',
            unique_object: 'CommercialOfferDrug',
            editformclassname: 'swWhsDocumentCommercialOfferDrugAddWindow',
            id: 'wdppeOfferGrid',
            paging: false,
            style: 'margin-bottom: 10px',
            stringfields: [
                { name: 'WhsDocumentCommercialOfferDrug_id', type: 'int', header: 'ID', key: true },
                { name: 'state', hidden: true },
                { name: 'CommercialOfferDrug_id', hidden: true },
                { name: 'CommercialOfferDrug_Price', type: 'money', header: 'Цена за уп.' },
                { name: 'CommercialOffer_begDT', type: 'date', header: 'Дата' },
                { name: 'Supplier_Name', type: 'string', header: 'Поставщик' },
                { name: 'DrugPrepFasCode_Code', type: 'string', header: 'Код' },
                { name: 'DrugPrepFas_Name', type: 'string', header: lang['naimenovanie'], id: 'autoexpand' }
            ],
            title: 'Коммерческие предложения',
            toolbar: true,
            deleteRecord: function(){
                var view_frame = this;
                var object_field = view_frame.object+'_id';
                var selected_record = view_frame.getGrid().getSelectionModel().getSelected();

                if (selected_record && selected_record.get(object_field) > 0) {
                    if (selected_record.get('state') == 'add') {
                        view_frame.getGrid().getStore().remove(selected_record);
                    } else {
                        selected_record.set('state', 'delete');
                        selected_record.commit();
                        view_frame.setFilter();
                    }
                }
            },
            deleteAllRecords: function(){
                var view_frame = this;
                var object_field = view_frame.object+'_id';

                view_frame.getGrid().getStore().each(function(record) {
                    if (record.get('state') == 'add') {
                        view_frame.getGrid().getStore().remove(record);
                    } else if (record.get(object_field) > 0) {
                        record.set('state', 'delete');
                        record.commit();
                    }
                });
                view_frame.setFilter();
            },
            addRecords: function(data_arr){
                var view_frame = this;
                var store = view_frame.getGrid().getStore();
                var record_count = store.getCount();
                var record = new Ext.data.Record.create(view_frame.jsonData['store']);
                var object_field = view_frame.object+'_id';
                var unique_object_field = view_frame.unique_object+'_id';

                if ( record_count == 1 && !store.getAt(0).get(object_field) ) {
                    view_frame.removeAll({addEmptyRecord: false});
                    record_count = 0;
                }

                view_frame.clearFilter();
                for (var i = 0; i < data_arr.length; i++) {
                    var idx = store.findBy(function(rec) { return rec.get(unique_object_field) == data_arr[i][unique_object_field]; });
                    if (idx < 0 || store.getAt(idx).get('state') == 'delete') {
                        data_arr[i][object_field] = Math.floor(Math.random()*1000000); //генерируем временный идентификатор
                        data_arr[i].state = 'add';
                        store.insert(record_count, new record(data_arr[i]));
                    }
                }
                view_frame.setFilter();
            },
            getChangedData: function(){ //возвращает новые и измненные данные
                var data = new Array();
                this.clearFilter();
                this.getGrid().getStore().each(function(record) {
                    if ((record.data.state == 'add' || record.data.state == 'edit' ||  record.data.state == 'delete')) {
                        data.push(record.data);
                    }
                });
                this.setFilter();
                return data;
            },
            getJSONChangedData: function(){ //возвращает новые и измененные записи в виде закодированной JSON строки
                var dataObj = this.getChangedData();
                return dataObj.length > 0 ? Ext.util.JSON.encode(dataObj) : "";
            },
            clearFilter: function() { //очищаем фильтры
                this.getGrid().getStore().clearFilter();
            },
            setFilter: function() { //скрывает удаленные записи
                this.getGrid().getStore().filterBy(function(record){
                    return (record.get('state') != 'delete');
                });
            }
        });

        this.SupplyGrid = new sw.Promed.ViewFrame({
            region: 'center',
            actions: [
                {name: 'action_add'},
                {name: 'action_edit', hidden: true, disabled: true},
                {name: 'action_view', hidden: true, disabled: true},
                {name: 'action_delete', hidden: true, disabled: true},
                {name: 'action_refresh', hidden: true, disabled: true},
                {name: 'action_print'}
            ],
            autoExpandColumn: 'autoexpand',
            autoExpandMin: 125,
            autoLoadData: false,
            border: true,
            dataUrl: '/?c=MzDrugRequest&m=loadWhsDocumentProcurementSupplySpecList',
            height: 180,
            object: 'WhsDocumentProcurementSupplySpec',
            unique_object: 'WhsDocumentSupplySpec',
            editformclassname: 'swWhsDocumentProcurementSupplySpecAddWindow',
            id: 'wdppeSupplyGrid',
            paging: false,
            style: 'margin-bottom: 10px',
            stringfields: [
                { name: 'WhsDocumentProcurementSupplySpec_id', type: 'int', header: 'ID', key: true },
                { name: 'state', hidden: true },
                { name: 'WhsDocumentSupplySpec_id', hidden: true },
                { name: 'WhsDocumentSupplySpec_Price', type: 'money', header: 'Цена за уп.' },
                { name: 'WhsDocumentUc_Date', type: 'date', header: 'Дата' },
                { name: 'WhsDocumentUc_Num', type: 'string', header: '№' },
                { name: 'Supplier_Name', type: 'string', header: 'Поставщик' },
                { name: 'DrugPrepFasCode_Code', type: 'string', header: 'Код' },
                { name: 'DrugPrepFas_Name', type: 'string', header: lang['naimenovanie'], id: 'autoexpand' }
            ],
            title: 'Контракты',
            toolbar: true,
            deleteRecord: function(){
                var view_frame = this;
                var object_field = view_frame.object+'_id';
                var selected_record = view_frame.getGrid().getSelectionModel().getSelected();

                if (selected_record && selected_record.get(object_field) > 0) {
                    if (selected_record.get('state') == 'add') {
                        view_frame.getGrid().getStore().remove(selected_record);
                    } else {
                        selected_record.set('state', 'delete');
                        selected_record.commit();
                        view_frame.setFilter();
                    }
                }
            },
            deleteAllRecords: function(){
                var view_frame = this;
                var object_field = view_frame.object+'_id';

                view_frame.getGrid().getStore().each(function(record) {
                    if (record.get('state') == 'add') {
                        view_frame.getGrid().getStore().remove(record);
                    } else if (record.get(object_field) > 0) {
                        record.set('state', 'delete');
                        record.commit();
                    }
                });
                view_frame.setFilter();
            },
            addRecords: function(data_arr){
                var view_frame = this;
                var store = view_frame.getGrid().getStore();
                var record_count = store.getCount();
                var record = new Ext.data.Record.create(view_frame.jsonData['store']);
                var object_field = view_frame.object+'_id';
                var unique_object_field = view_frame.unique_object+'_id';

                if ( record_count == 1 && !store.getAt(0).get(object_field) ) {
                    view_frame.removeAll({addEmptyRecord: false});
                    record_count = 0;
                }

                view_frame.clearFilter();
                for (var i = 0; i < data_arr.length; i++) {
                    var idx = store.findBy(function(rec) { return rec.get(unique_object_field) == data_arr[i][unique_object_field]; });
                    if (idx < 0 || store.getAt(idx).get('state') == 'delete') {
                        data_arr[i][object_field] = Math.floor(Math.random()*1000000); //генерируем временный идентификатор
                        data_arr[i].state = 'add';
                        store.insert(record_count, new record(data_arr[i]));
                    }
                }
                view_frame.setFilter();
            },
            getChangedData: function(){ //возвращает новые и измненные данные
                var data = new Array();
                this.clearFilter();
                this.getGrid().getStore().each(function(record) {
                    if ((record.data.state == 'add' || record.data.state == 'edit' ||  record.data.state == 'delete')) {
                        data.push(record.data);
                    }
                });
                this.setFilter();
                return data;
            },
            getJSONChangedData: function(){ //возвращает новые и измененные записи в виде закодированной JSON строки
                var dataObj = this.getChangedData();
                return dataObj.length > 0 ? Ext.util.JSON.encode(dataObj) : "";
            },
            clearFilter: function() { //очищаем фильтры
                this.getGrid().getStore().clearFilter();
            },
            setFilter: function() { //скрывает удаленные записи
                this.getGrid().getStore().filterBy(function(record){
                    return (record.get('state') != 'delete');
                });
            }
        });

		Ext.apply(this, {
			layout: 'border',
			buttons:
			[{
				handler: function() 
				{
					this.ownerCt.doSave();
				},
				iconCls: 'save16',
				text: BTN_FRMSAVE
			}, 
			{
				text: '-'
			},
			HelpButton(this, 0),
			{
				handler: function() 
				{
					this.ownerCt.hide();
				},
				iconCls: 'cancel16',
				text: BTN_FRMCANCEL
			}],
			items:[
                drug_form,
                {
                    region: 'center',
                    layout: 'border',
                    border: false,
                    items: [{
                        region: 'north',
                        layout: 'border',
                        title: 'Тарифы',
                        height: 200,
                        border: true,
                        items: [
                            tariff_form,
                            this.TariffGrid
                        ]
                    }, {
                        region: 'center',
                        layout: 'border',
                        border: false,
                        items: [
                            this.OfferGrid,
                            this.SupplyGrid
                        ]
                    }]
                },
                price_form
            ]
		});
		sw.Promed.swWhsDocumentProcurementPriceEditWindow.superclass.initComponent.apply(this, arguments);
		this.drug_form = this.findById('WhsDocumentProcurementPriceEditDrugForm').getForm();
		this.tariff_form = this.findById('WhsDocumentProcurementPriceEditTariffForm').getForm();
		this.price_form = this.findById('WhsDocumentProcurementPriceEditPriceForm').getForm();
	}
});