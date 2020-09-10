/**
* swWhsDocumentSupplySpecEditWindow - окно редактирования "Спецификация договора (контракта)"
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Farmacy
* @access       public
* @copyright    Copyright (c) 2012 Swan Ltd.
* @author       R. Salakhov
* @version      01.2013
* @comment      
*/
sw.Promed.swWhsDocumentSupplySpecEditWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: false,
	title: lang['spetsifikatsiya_kontrakta'],
	layout: 'border',
	id: 'WhsDocumentSupplySpecEditWindow',
	modal: true,
	shim: false,
	width: 400,
	resizable: false,
	maximizable: false,
	maximized: true,
	listeners: {
		hide: function() {
			this.onHide();
		}
	},
	setSumFromPrice: function(mode) {
		var form = this.form;
		var cnt = form.findField('WhsDocumentSupplySpec_KolvoUnit').getValue() > 0 ? form.findField('WhsDocumentSupplySpec_KolvoUnit').getValue()*1 : 0;
		var sum = 0;
		var sum_nds = 0;
		var nds_sum = 0;
		var nds = form.findField('WhsDocumentSupplySpec_NDS').getValue() > 0 ? form.findField('WhsDocumentSupplySpec_NDS').getValue()*1 : 0;
		var cost = form.findField('WhsDocumentSupplySpec_Price').getValue() > 0 ? form.findField('WhsDocumentSupplySpec_Price').getValue()*1 : 0;
		var cost_nds = 0;

		sum = (cnt * cost).toFixed(2);
		cost_nds = cost * ((100+nds)/100);
		sum_nds = (cost_nds*cnt).toFixed(2);
		nds_sum = (cost*nds/100).toFixed(2);

        cost_nds = cost_nds.toFixed(2);

		if (mode == 'only_sum') {
			form.findField('SumField').setValue(sum);
			form.findField('Nds_Sum').setValue(nds_sum);
		} else {
			form.findField('SumField').setValue(sum);
			form.findField('Nds_Sum').setValue(nds_sum);
			form.findField('WhsDocumentSupplySpec_SumNDS').setValue(sum_nds);
			form.findField('WhsDocumentSupplySpec_PriceNDS').setValue(cost_nds);
		}
	},
	setSumFromPriceNDS: function(mode) {
		var form = this.form;
		var cnt = form.findField('WhsDocumentSupplySpec_KolvoUnit').getValue() > 0 ? form.findField('WhsDocumentSupplySpec_KolvoUnit').getValue()*1 : 0;
		var sum = 0;
		var sum_nds = 0;
		var nds_sum = 0;
		var nds = form.findField('WhsDocumentSupplySpec_NDS').getValue() > 0 ? form.findField('WhsDocumentSupplySpec_NDS').getValue()*1 : 0;
		var cost_nds = form.findField('WhsDocumentSupplySpec_PriceNDS').getValue() > 0 ? form.findField('WhsDocumentSupplySpec_PriceNDS').getValue()*1 : 0;
		var cost = 0;

		sum_nds = (cost_nds*cnt).toFixed(2);
		cost = cost_nds/(1+nds/100);
		sum = (cost*cnt).toFixed(2);
		nds_sum = (cost*nds/100).toFixed(2);

        cost = cost.toFixed(2);

		if (mode == 'only_sum') {
			form.findField('SumField').setValue(sum);
			form.findField('Nds_Sum').setValue(nds_sum);
		} else {
			form.findField('SumField').setValue(sum);
			form.findField('Nds_Sum').setValue(nds_sum);
			form.findField('WhsDocumentSupplySpec_SumNDS').setValue(sum_nds);
			form.findField('WhsDocumentSupplySpec_Price').setValue(cost);
		}
	},
	setOstatKolvo: function() {
		var wnd = this;
		var spec_cnt_field = wnd.form.findField('WhsDocumentSupplySpec_Count');
		var uot_id = wnd.proc_spec_combo.getValue();
		var uot_kol = wnd.form.findField('WhsDocumentProcurementRequestSpec_Kolvo').getValue() > 0 ? wnd.form.findField('WhsDocumentProcurementRequestSpec_Kolvo').getValue()*1 : 0;
        var uot_data = wnd.proc_spec_combo.getSelectedRecordData();
		var grid_kol = 0;
		var ost_kol = null;
		var ost_goods_cnt = null;
		var ost_goods_id = null;

		if (uot_kol > 0) {
            if (wnd.owner) {
                wnd.owner.getGrid().getStore().each(function(record) {
                    if (record.get('WhsDocumentProcurementRequestSpec_id') == uot_id && record.get('WhsDocumentSupplySpec_id') != wnd.WhsDocumentSupplySpec_id) {
                        grid_kol += record.get('WhsDocumentSupplySpec_Count')*1;
                    }
                });
            }
            ost_kol = grid_kol < uot_kol ? uot_kol - grid_kol : 0;
		}

        if (ost_kol > 0 && uot_data && uot_data.WhsDocumentProcurementRequestSpec_Count > 0) {
            ost_goods_cnt = ost_kol * uot_data.WhsDocumentProcurementRequestSpec_Count;
            ost_goods_id = uot_data.GoodsUnit_id;
        }

		wnd.form.findField('Ostat_Kolvo').setValue(ost_kol);
		wnd.form.findField('Ostat_GoodsCount').setValue(ost_goods_cnt);
		wnd.form.findField('Ostat_GoodsUnit_id').setValue(ost_goods_id);

        var spec_cnt = spec_cnt_field.getValue();
        if (Ext.isEmpty(spec_cnt) || spec_cnt_field.default_value_enabled) {
            spec_cnt = ost_kol;
        }
        spec_cnt_field.default_value_enabled = true;
        wnd.setSpecCount(spec_cnt);
        wnd.setSpecCountKoef();
	},
    setSpecCount: function(spec_kol) {
		var wnd = this;
        var uot_data = wnd.proc_spec_combo.getSelectedRecordData();
		var spec_goods_cnt = null;
		var spec_goods_id = null;

        if (spec_kol > 0 && uot_data && uot_data.WhsDocumentProcurementRequestSpec_Count > 0) {
            spec_goods_cnt = spec_kol * uot_data.WhsDocumentProcurementRequestSpec_Count;
            spec_goods_id = uot_data.GoodsUnit_id;
        }

		wnd.form.findField('WhsDocumentSupplySpec_Count').setValue(spec_kol);
		wnd.form.findField('SpecCount_GoodsCount').setValue(spec_goods_cnt);
		wnd.form.findField('SpecCount_GoodsUnit_id').setValue(spec_goods_id);
	},
    setSpecCountKoef: function(koef) {
        var count_field = this.form.findField('WhsDocumentSupplySpec_Count');
        var kolvo_field = this.form.findField('WhsDocumentSupplySpec_KolvoUnit');
        var count = count_field.getValue();
        var kolvo = kolvo_field.getValue();

        if (Ext.isEmpty(this.WhsDocumentProcurementRequest_id)) { //если в контракте не указан лот, то прерываем выполнение функции
            return true;
        }

        if (koef <= 0) {
            koef = null;
        }

        if (Ext.isEmpty(koef) && count > 0 && kolvo > 0) {
            koef = count*1.0/kolvo;
            this.form.findField('SpecCount_Koef').setValue(koef);
        } else {
            if (Ext.isEmpty(koef)) {
                koef = 1;
                this.form.findField('SpecCount_Koef').setValue(koef);
            }

            if (count > 0) {
                kolvo = count*1.0/koef;
                kolvo_field.setValue(kolvo);
            } else if (kolvo > 0) {
                count = koef*kolvo;
                count_field.setValue(count);
            }
        }
	},
	setMaxSalePrice: function(drug_id) { //функция получения предельной цены медиамента
		var wnd = this;
		var jnvlp_panel = wnd.form.findField('MakerPrice').ownerCt.ownerCt;

		if (!drug_id) {
			drug_id = wnd.form.findField('Drug_id').getValue();
		}

		if (drug_id > 0) {
			Ext.Ajax.request({
				url: '/?c=WhsDocumentSupply&m=getMaxSalePrice',
				params: {
					Drug_id: drug_id,
					WhsDocumentUc_Date: wnd.WhsDocumentUc_Date
				},
				success: function(response){
					var result = Ext.util.JSON.decode(response.responseText);
					if (!Ext.isEmpty(result[0].MakerPrice) || !Ext.isEmpty(result[0].MaxRetailPriceNDS) || !Ext.isEmpty(result[0].MaxWholeSalePriceNDS)) {
						wnd.form.findField('IsJnvlp').setValue(lang['da']);
						wnd.form.findField('MakerPrice').setValue((result[0].MakerPrice + (!Ext.isEmpty(result[0].MakerPriceDate) ? ' (' +result[0].MakerPriceDate+')' : '')));
						wnd.form.findField('MaxRetailPriceNDS').setValue(result[0].MaxRetailPriceNDS);
						wnd.form.findField('MaxWholeSalePriceNDS').setValue(result[0].MaxWholeSalePriceNDS);
						jnvlp_panel.show();
					} else {
						wnd.form.findField('IsJnvlp').setValue(lang['net']);
						wnd.form.findField('MakerPrice').setValue(null);
						wnd.form.findField('MaxRetailPriceNDS').setValue(null);
						wnd.form.findField('MaxWholeSalePriceNDS').setValue(null);
						jnvlp_panel.hide();
					}
				}
			});
		} else {
			wnd.form.findField('IsJnvlp').setValue(lang['net']);
			wnd.form.findField('MakerPrice').setValue(null);
			wnd.form.findField('MaxRetailPriceNDS').setValue(null);
			wnd.form.findField('MaxWholeSalePriceNDS').setValue(null);
			jnvlp_panel.hide();
		}
	},
	setFieldLabel: function(field, label) {
		var el = field.el.dom.parentNode.parentNode;
		if(el.children[0].tagName.toLowerCase() === 'label') {
			el.children[0].innerHTML = label+':';
		} else if (el.parentNode.children[0].tagName.toLowerCase() === 'label') {
			el.parentNode.children[0].innerHTML = label+':';
		}
	},
	onHide: Ext.emptyFn,
	setDisabled: function(disable) {
		var wnd = this;

		var field_arr = [
			'WhsDocumentSupplySpec_id',
			'WhsDocumentSupplySpec_PosCode',
			'Drug_id',
			'WhsDocumentSupplySpec_KolvoForm',
			'WhsDocumentSupplySpec_ShelfLifePersent',
			'MustSupply_Kolvo',
			'WhsDocumentSupplySpec_KolvoUnit',
			'WhsDocumentSupplySpec_Price',
			'SumField',
			'WhsDocumentSupplySpec_PriceNDS',
			'DrugNomen_Code',
			'DrugPrepFasCode_Code',
			'contr_GoodsUnit_id',
			'DrugPrep_id',
			'Actmatters_id',
			'WhsDocumentSupplySpec_GoodsUnitQty',
			'WhsDocumentSupplySpec_SuppPrice'
		];

		for (var i in field_arr) if (wnd.form.findField(field_arr[i])) {
			if (disable) {
				wnd.form.findField(field_arr[i]).disable();
			} else {
				wnd.form.findField(field_arr[i]).enable();
			}
		}

		if (disable) {
			wnd.buttons[0].disable();
		} else {
			wnd.buttons[0].enable();
		}
	},
    setMode: function() { //переключение режима отображения полей в зависимости от того указан ли лот в шапке окнтракта
        var uot_exists = !Ext.isEmpty(this.WhsDocumentProcurementRequest_id);

        this.form.findField('WhsDocumentProcurementRequestSpec_id').setAllowBlank(!uot_exists);
        this.proc_spec_combo.getStore().baseParams.DrugComplexMnn_id = null;

        if (uot_exists) {
            this.form.findField('WhsDocumentProcurementRequestSpec_id').ownerCt.show();
            this.form.findField('WhsDocumentSupplySpec_Count').ownerCt.ownerCt.show();

            this.proc_spec_combo.getStore().baseParams.WhsDocumentProcurementRequest_id = this.WhsDocumentProcurementRequest_id;
            this.drug_combo.getStore().baseParams.WhsDocumentProcurementRequest_id = this.WhsDocumentProcurementRequest_id;
        } else {
            this.form.findField('WhsDocumentProcurementRequestSpec_id').ownerCt.hide();
            this.form.findField('WhsDocumentSupplySpec_Count').ownerCt.ownerCt.hide();

            this.proc_spec_combo.getStore().baseParams.WhsDocumentProcurementRequest_id = null;
            this.drug_combo.getStore().baseParams.WhsDocumentProcurementRequest_id = null;

            this.proc_spec_combo.getStore().load();
        }
    },
    setRegionSettings: function() { //отображение полей с учетом особенностей региона
        var org_type = getGlobalOptions().orgtype;
        var region_nick = getRegionNick();

        if (region_nick == 'krym' && org_type == 'reg_dlo') {
            this.form.findField('WhsDocumentSupplySpec_SuppPrice').showContainer();
        } else {
            this.form.findField('WhsDocumentSupplySpec_SuppPrice').hideContainer();
        }
    },
    setGoodsUnit: function(spec_data){
    	var wnd = this;
    	if (
    		!wnd.form.findField('contr_GoodsUnit_id').disabled 
    		&& !(wnd.form.findField('contr_GoodsUnit_id').getValue()>0) 
    		&& spec_data 
    		&& spec_data.DrugComplexMnn_id 
    		&& getGlobalOptions().org_id
    	) {
            var upak_data = wnd.form.findField('contr_GoodsUnit_id').getRecordDataByName('упаковка');

    		Ext.Ajax.request({
                url: '/?c=DrugNomen&m=getGoodsUnitData',
                params: {
                    DrugComplexMnn_id: spec_data.DrugComplexMnn_id,
                    Org_id: getGlobalOptions().org_id
                },
                success: function(response){
                    var result = Ext.util.JSON.decode(response.responseText);
                    if (result[0] && result[0].GoodsUnit_id) {
                        wnd.form.findField('contr_GoodsUnit_id').setValue(result[0].GoodsUnit_id);
                        wnd.form.findField('contr_GoodsUnit_id').fireEvent('change', wnd.form.findField('contr_GoodsUnit_id'), result[0].GoodsUnit_id);
                    } else if (upak_data && upak_data.GoodsUnit_id > 0) {
                        wnd.form.findField('contr_GoodsUnit_id').setValue(upak_data.GoodsUnit_id);
                        wnd.form.findField('contr_GoodsUnit_id').fireEvent('change', wnd.form.findField('contr_GoodsUnit_id'), upak_data.GoodsUnit_id);
					}
                }
            });
    	}
    },
    setGoodsUnitQty: function(){
    	var wnd = this;
    	var drug = wnd.drug_combo.getValue();
    	if(drug){
    		var index = wnd.drug_combo.getStore().findBy(function(rec){
    			return (rec.get('Drug_id')==drug);
    		});
    		if(index != -1){
    			var rec = wnd.drug_combo.getStore().getAt(index);
    		}
    		if(index != -1 && rec && rec.get('DrugComplexMnn_id')){
    			var gu = wnd.form.findField('contr_GoodsUnit_id').getValue();
    			var kolvo = wnd.form.findField('WhsDocumentSupplySpec_KolvoUnit').getValue();
    			var dcmnn = rec.get('DrugComplexMnn_id');

    			if(gu && kolvo) {
    				var gu_data = wnd.form.findField('contr_GoodsUnit_id').getSelectedRecordData();

    				if (gu_data && gu_data.GoodsUnit_Name == 'упаковка') {
                        wnd.form.findField('WhsDocumentSupplySpec_GoodsUnitQty').setValue(kolvo);
					} else {
                        Ext.Ajax.request({
                            url: '/?c=DrugNomen&m=loadGoodsPackCountList',
                            params: {
                                DrugComplexMnn_id: dcmnn,
                                GoodsUnit_id: gu
                            },
                            success: function(response){
                                var result = Ext.util.JSON.decode(response.responseText);
                                if (result[0] && result[0].GoodsPackCount_Count) {
                                    var qty = kolvo*result[0].GoodsPackCount_Count;
                                    wnd.form.findField('WhsDocumentSupplySpec_GoodsUnitQty').setValue(qty);
                                } else {
                                    sw.swMsg.alert('Предупреждение', 'В справочнике Количество товара в упаковке нет записей для выбранной единицы измерения и медикамента, добавьте новую запись',
                                        function(){
                                            getWnd('swGoodsUnitCountEditWindow').show({
                                                DrugComplexMnn_id: dcmnn,
                                                TRADENAMES_ID: null,
                                                GoodsUnit_id: gu,
                                                action: 'add',
                                                callback: function(data){
                                                    if(data && data.GoodsPackCount_Count){
                                                        var qty = kolvo*data.GoodsPackCount_Count;
                                                        wnd.form.findField('WhsDocumentSupplySpec_GoodsUnitQty').setValue(qty);
                                                    }
                                                }
                                            });
                                        }
                                    );
                                }
                            }
                        });
					}
    			}
    		}
    	}
    },
    doSave:  function() {
		var wnd = this;
		if ( !this.form.isValid() ) {
			sw.swMsg.show( {
				buttons: Ext.Msg.OK,
				fn: function() {
					wnd.findById('WhsDocumentSupplySpecEditForm').getFirstInvalidEl().focus(true);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		var params = this.form.getValues();

		params.WhsDocumentSupplySpec_KolvoMin = null;
		params.Drug_Name = null;
		params.DrugComplexMnn_RusName = null;
		params.WhsDocumentSupplySpec_NDS = this.form.findField('WhsDocumentSupplySpec_NDS').getValue();
		params.Okei_id = this.form.findField('Okei_id').getValue();
		params.WhsDocumentSupplySpec_SumNDS = this.form.findField('WhsDocumentSupplySpec_SumNDS').getValue();
		params.GoodsUnit_id = this.form.findField('contr_GoodsUnit_id').getValue();

		if (params.WhsDocumentSupplySpec_NDS <= 0) {
			params.WhsDocumentSupplySpec_NDS = 0;
		}

		Ext.Ajax.request({ //дописываем недостающие данные
			params:{
				Drug_id: params.Drug_id
			},
			success: function (response) {
				var result = Ext.util.JSON.decode(response.responseText);
				if (result[0]) {
					Ext.apply(params, result[0]);
					wnd.callback(params);
					wnd.hide();
				}
			},
			url:'/?c=WhsDocumentSupplySpec&m=getWhsDocumentSupplySpecContext'
		});
		return true;		
	},
	show: function() {
        var wnd = this;
		sw.Promed.swWhsDocumentSupplySpecEditWindow.superclass.show.apply(this, arguments);		
		this.owner = null;
		this.action = '';
		this.callback = Ext.emptyFn;
		this.WhsDocumentSupplySpec_id = null;
		this.WhsDocumentProcurementRequest_id = null;
		this.WhsDocumentUc_Date = null;
		this.showPriceDrugs = false;
		this.CommercialOfferDrug_PriceDetail = null;
        if ( !arguments[0] ) {
            sw.swMsg.alert(lang['oshibka'], lang['ne_ukazanyi_vhodnyie_dannyie'], function() { wnd.hide(); });
            return false;
        }
		if ( arguments[0].owner ) {
			this.owner = arguments[0].owner;
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
		if ( arguments[0].params ) {
			this.params = arguments[0].params;
			this.WhsDocumentSupplySpec_id = arguments[0].params.WhsDocumentSupplySpec_id;
			this.WhsDocumentProcurementRequest_id = arguments[0].params.WhsDocumentProcurementRequest_id;
			this.WhsDocumentUc_Date = arguments[0].params.WhsDocumentUc_Date ? arguments[0].params.WhsDocumentUc_Date.format("d.m.Y") : null;
			if(arguments[0].params.showPriceDrugs)
				this.showPriceDrugs = arguments[0].params.showPriceDrugs;
			if(arguments[0].params.CommercialOfferDrug_PriceDetail)
				this.CommercialOfferDrug_PriceDetail = arguments[0].params.CommercialOfferDrug_PriceDetail;
		}

		this.form.reset();
        this.form.findField('WhsDocumentSupplySpec_Count').default_value_enabled = false; //при открытии формы ставим запрет на значение по умолчанию, иначе сохреннео значение будет утеряно

        var loadMask = new Ext.LoadMask(this.form.getEl(), {msg:lang['zagruzka']});
        loadMask.show();

		wnd.setTitle(lang['spetsifikatsiya_kontrakta']);
		wnd.setFieldLabel(wnd.form.findField('Nds_Sum'), lang['v_t_ch_nds'] + ' (' + getCurrencyName() + ')');
        wnd.setMode();
        wnd.setRegionSettings();

        var proc_spec_id = wnd.proc_spec_combo.getValue();
        var drug_id = wnd.drug_combo.getValue();

        if(this.showPriceDrugs)
        	Ext.getCmp('Price_Drugs').show();
        else
        	Ext.getCmp('Price_Drugs').hide();

		switch (wnd.action) {
			case 'add':
				wnd.setTitle(wnd.title + lang['_dobavlenie']);
				wnd.form.findField('Okei_id').setValue(120); // 120 - Упаковка
				wnd.form.findField('WhsDocumentSupplySpec_ShelfLifePersent').setValue(getGlobalOptions().farmacy_remainig_exp_date_less_2_years); // Значение из настройки "Со сроком хранения до 2-х лет % от основного"
				if (arguments[0].params) {
					this.form.setValues(arguments[0].params);
					/*if (arguments[0].params.WhsDocumentSupplySpec_NDS) {
						wnd.setFieldLabel(wnd.form.findField('Nds_Sum'), 'в т.ч. НДС ('+arguments[0].params.WhsDocumentSupplySpec_NDS+'%)');
					}*/
                    if (!Ext.isEmpty(arguments[0].params.DrugNds_id)) {
                        wnd.form.findField('WhsDocumentSupplySpec_NDS').setValue(wnd.form.findField('DrugNds_id').getCode());
                    }
					if(arguments[0].params.Org_cid){
						wnd.proc_spec_combo.getStore().baseParams.Org_id = arguments[0].params.Org_cid;
					}
				}
                wnd.proc_spec_combo.loadData();
                wnd.actmatters_combo.loadData();
                wnd.drug_combo.loadData();
                wnd.drugprep_combo.loadData();

				wnd.setMaxSalePrice();
				wnd.setDisabled(false);
				loadMask.hide();
			break;
			case 'edit':
			case 'view':
				wnd.setTitle(wnd.title + (wnd.action == 'edit' ? lang['_redaktirovanie'] : lang['_prosmotr']));
                wnd.setMaxSalePrice();
				if (arguments[0].params) {
					this.form.setValues(arguments[0].params);

                    if (arguments[0].params.WhsDocumentProcurementRequestSpec_id) {
                        wnd.proc_spec_combo.setValueById(arguments[0].params.WhsDocumentProcurementRequestSpec_id);
                    } else {
                        wnd.proc_spec_combo.loadData();
                    }

                    if (arguments[0].params.Actmatters_id) {
                        wnd.actmatters_combo.setValueById(arguments[0].params.Actmatters_id);
                    } else {
                        wnd.proc_spec_combo.loadData();
                    }

                    if (arguments[0].params.Drug_id) {
                        wnd.drug_combo.setValueById(arguments[0].params.Drug_id);
                    } else {
                        wnd.drug_combo.loadData();
                    }

                    if (arguments[0].params.GoodsUnit_id) {
                        wnd.form.findField('contr_GoodsUnit_id').setValue(arguments[0].params.GoodsUnit_id);
                    }

					if (arguments[0].params.WhsDocumentSupplySpec_NDS != undefined) {
                        wnd.form.findField('DrugNds_id').setValueByCode(arguments[0].params.WhsDocumentSupplySpec_NDS);
					}
				}
				wnd.setSumFromPriceNDS('only_sum');
				wnd.setDisabled(wnd.action == 'view');
				// ну не дизаблится по другому, что теперь...
				wnd.form.findField('DrugNds_id').setDisabled(wnd.action == 'view');

				if(!Ext.isEmpty(wnd.CommercialOfferDrug_PriceDetail) && wnd.CommercialOfferDrug_PriceDetail > 0)
				{
					wnd.form.findField('CommercialOfferDrug_PriceDetail').setValue(wnd.CommercialOfferDrug_PriceDetail);
					wnd.form.findField('CommercialOfferDrug_PriceDetail').fireEvent('change',wnd.form.findField('CommercialOfferDrug_PriceDetail'),wnd.form.findField('CommercialOfferDrug_PriceDetail').getValue());
				}

				loadMask.hide();
			break;	
		}
	},
	initComponent: function() {
		var labelStyle = 'text-align: left; padding-left:5px; width: 36px;';
		var wnd = this;
		
		wnd.proc_spec_combo = new sw.Promed.SwBaseRemoteCombo ({
            fieldLabel: lang['medikament'],
            hiddenName: 'WhsDocumentProcurementRequestSpec_id',
            displayField: 'Drug_Name',
            valueField: 'WhsDocumentProcurementRequestSpec_id',
            allowBlank: false,
            editable: true,
            anchor: '95%',
            triggerAction: 'all',
            tpl: new Ext.XTemplate(
                '<tpl for="."><div class="x-combo-list-item">',
                '<table style="width:100%;border: 0;"><td style="width:80%;"><h3>{Drug_Name}</h3></td><td style="width:20%;"></td></tr></table>',
                '</div></tpl>'
            ),
            store: new Ext.data.SimpleStore({
                autoLoad: false,
                fields: [
                    { name: 'WhsDocumentProcurementRequestSpec_id', mapping: 'WhsDocumentProcurementRequestSpec_id' },
                    { name: 'Drug_Name', mapping: 'Drug_Name' },
                    { name: 'DrugComplexMnn_id', mapping: 'DrugComplexMnn_id' },
                    { name: 'Tradenames_id', mapping: 'Tradenames_id' },
                    { name: 'Tradenames_Name', mapping: 'Tradenames_Name' },
                    { name: 'Okei_id', mapping: 'Okei_id' },
                    { name: 'WhsDocumentProcurementRequestSpec_Kolvo', mapping: 'WhsDocumentProcurementRequestSpec_Kolvo' },
                    { name: 'GoodsUnit_id', mapping: 'GoodsUnit_id' },
                    { name: 'WhsDocumentProcurementRequestSpec_Count', mapping: 'WhsDocumentProcurementRequestSpec_Count' },
                    { name: 'Country_Name', mapping: 'Country_Name' }
                ],
                key: 'WhsDocumentProcurementRequestSpec_id',
                sortInfo: { field: 'Drug_Name' },
                url:'/?c=WhsDocumentSupply&m=loadWhsDocumentProcurementRequestSpecCombo'
            }),
            childrenList: ['Actmatters_id'],
            listeners: {
                'change': function(combo, newValue) {
                    combo.childrenList.forEach(function(field_name){
                        var f_combo = wnd.form.findField(field_name);
                        var record_data = combo.getSelectedRecordData();
                        if (!f_combo.disabled) {
                            f_combo.clearValue();
                            //f_combo.getStore().baseParams[combo.hiddenName] = !Ext.isEmpty(combo.getValue()) ? combo.getValue()  : null;
                            f_combo.getStore().baseParams['DrugComplexMnn_id'] = !Ext.isEmpty(record_data.DrugComplexMnn_id) ? record_data.DrugComplexMnn_id : null;
                            f_combo.loadData();
                        }
                    });
                    combo.setLinkedFieldValues();
                }
            },
            loadData: function() {
                var combo = this;
                combo.getStore().load({
                    callback: function(){
                        combo.setValue(null);
                    }
                });
            },
            clearValue: function() {
                var combo = this
                sw.Promed.SwBaseRemoteCombo.superclass.clearValue.apply(this, arguments);
                combo.childrenList.forEach(function(field_name){
                    var f_combo = wnd.form.findField(field_name);
                    if (!f_combo.disabled) {
                        f_combo.getStore().baseParams[combo.hiddenName] = null;
                        f_combo.clearValue();
                        f_combo.getStore().removeAll();
                    }
                });
                combo.setLinkedFieldValues();
            },
            setValueById: function(id) {
                var combo = this;
                combo.getStore().baseParams[combo.valueField] = id;
                combo.getStore().load({
                    callback: function(){
                        combo.setValue(id);
                        combo.getStore().baseParams[combo.valueField] = null;
                        combo.childrenList.forEach(function(field_name){
                            var f_combo = wnd.form.findField(field_name);
                            f_combo.getStore().baseParams[combo.valueField] = !Ext.isEmpty(id) ? id : null;
                        });
                        combo.setLinkedFieldValues();
                    }
                });
            },
            getSelectedRecordData: function() {
                var combo = this;
                var value = combo.getValue();
                var data = new Object();
                if (value > 0) {
                    var idx = this.getStore().findBy(function(record) {
                        return (record.get(combo.valueField) == value);
                    })
                    if (idx > -1) {
                        Ext.apply(data, this.getStore().getAt(idx).data);
                    }
                }
                return data;
            },
            setLinkedFieldValues: function() {
                var spec_data = this.getSelectedRecordData();
                var goods_count = null;

                spec_data.WhsDocumentProcurementRequestSpec_Kolvo = spec_data.WhsDocumentProcurementRequestSpec_Kolvo*1;
                spec_data.WhsDocumentProcurementRequestSpec_Count = spec_data.WhsDocumentProcurementRequestSpec_Count*1;

                if (spec_data.WhsDocumentProcurementRequestSpec_Kolvo > 0 && spec_data.WhsDocumentProcurementRequestSpec_Count > 0) {
                    goods_count = spec_data.WhsDocumentProcurementRequestSpec_Kolvo * spec_data.WhsDocumentProcurementRequestSpec_Count;
                }

                wnd.form.findField('Tradenames_Name').setValue(spec_data.Tradenames_Name);
                wnd.form.findField('WhsDocumentProcurementRequestSpec_Kolvo').setValue(spec_data.WhsDocumentProcurementRequestSpec_Kolvo);
                wnd.form.findField('WhsDocumentProcurementRequestSpec_GoodsUnit_id').setValue(spec_data.GoodsUnit_id);
                wnd.form.findField('WhsDocumentProcurementRequestSpec_GoodsCount').setValue(goods_count);
                wnd.setOstatKolvo();
                wnd.form.findField('Manufacturer_Country_Name').setValue(spec_data.Country_Name);
                if(spec_data.GoodsUnit_id){
                	wnd.form.findField('contr_GoodsUnit_id').setValue(spec_data.GoodsUnit_id);
                	wnd.form.findField('contr_GoodsUnit_id').fireEvent('change',wnd.form.findField('contr_GoodsUnit_id'),spec_data.GoodsUnit_id);
                	wnd.form.findField('contr_GoodsUnit_id').disable();
                } else {
                	wnd.setGoodsUnit(spec_data);
                	if(wnd.action != 'view'){
                		wnd.form.findField('contr_GoodsUnit_id').enable();
                	}
                }
            }
        });

        wnd.actmatters_combo = new sw.Promed.SwBaseRemoteCombo ({
            fieldLabel: lang['mnn'],
            hiddenName: 'Actmatters_id',
            displayField: 'Actmatters_Name',
            valueField: 'Actmatters_id',
            allowBlank: false,
            editable: true,
            anchor: '95%',
            triggerAction: 'all',
            tpl: new Ext.XTemplate(
                '<tpl for="."><div class="x-combo-list-item">',
                '<table style="width:100%;border: 0;"><td style="width:80%;"><h3>{Actmatters_Name}</h3></td><td style="width:20%;"></td></tr></table>',
                '</div></tpl>'
            ),
            store: new Ext.data.SimpleStore({
                autoLoad: false,
                fields: [
                    { name: 'Actmatters_id', mapping: 'Actmatters_id' },
                    { name: 'Actmatters_Name', mapping: 'Actmatters_Name' }
                ],
                key: 'Actmatters_id',
                sortInfo: { field: 'Actmatters_Name' },
                url:'/?c=WhsDocumentSupply&m=loadActmattersCombo'
            }),
            childrenList: ['Drug_id'],
            listeners: {
                'change': function(combo, newValue) {
                    combo.childrenList.forEach(function(field_name){
                        var f_combo = wnd.form.findField(field_name);
                        if (!f_combo.disabled) {
                            f_combo.clearValue();
                            f_combo.getStore().baseParams[combo.hiddenName] = !Ext.isEmpty(combo.getValue()) ? combo.getValue()  : null;
                            f_combo.loadData();
                        }
                    });
                }
            },
            loadData: function() {
                var combo = this;
                combo.getStore().load({
                    callback: function(){
                        combo.setValue(null);
                    }
                });
            },
            clearValue: function() {
                var combo = this
                sw.Promed.SwBaseRemoteCombo.superclass.clearValue.apply(this, arguments);
                combo.childrenList.forEach(function(field_name){
                    var f_combo = wnd.form.findField(field_name);
                    if (!f_combo.disabled) {
                        f_combo.getStore().baseParams[combo.hiddenName] = null;
                        f_combo.clearValue();
                        f_combo.getStore().removeAll();
                    }
                });
            },
            setValueById: function(id) {
                var combo = this;
                combo.getStore().baseParams[combo.valueField] = id;
                combo.getStore().load({
                    callback: function(){
                        combo.setValue(id);
                        combo.getStore().baseParams[combo.valueField] = null;
                        combo.childrenList.forEach(function(field_name){
                            var f_combo = wnd.form.findField(field_name);
                            f_combo.getStore().baseParams[combo.valueField] = !Ext.isEmpty(id) ? id : null;
                        });
                    }
                });
            },
            getSelectedRecordData: function() {
                var combo = this;
                var value = combo.getValue();
                var data = new Object();
                if (value > 0) {
                    var idx = this.getStore().findBy(function(record) {
                        return (record.get(combo.valueField) == value);
                    })
                    if (idx > -1) {
                        Ext.apply(data, this.getStore().getAt(idx).data);
                    }
                }
                return data;
            }
        });

        wnd.drug_combo = new sw.Promed.SwBaseRemoteCombo ({
            fieldLabel: lang['medikament'],//lang['torgovoe_naimenovanie'],
            hiddenName: 'Drug_id',
            displayField: 'Drug_Name',
            valueField: 'Drug_id',
            allowBlank: false,
            editable: true,
            anchor: '95%',
            triggerAction: 'all',
            tpl: new Ext.XTemplate(
                '<tpl for="."><div class="x-combo-list-item">',
                '<table style="width:100%;border: 0;"><td style="width:80%;"><h3>{Drug_Name}</h3></td><td style="width:20%;"></td></tr></table>',
                '</div></tpl>'
            ),
            store: new Ext.data.SimpleStore({
                autoLoad: false,
                fields: [
                    {name: 'Drug_id', mapping: 'Drug_id'},
                    {name: 'Drug_Name', mapping: 'Drug_Name'},
                    {name: 'DrugNomen_Code', mapping: 'DrugNomen_Code'},
					{name: 'DrugNomen_DrugNds_id', mapping: 'DrugNds_id'},
                    {name: 'DrugComplexMnn_RusName', mapping: 'DrugComplexMnn_RusName'},
                    {name: 'DrugComplexMnn_id', mapping: 'DrugComplexMnn_id'},
                    {name: 'Firm_Name', mapping: 'Firm_Name'},
                    {name: 'Country_Name', mapping: 'Country_Name'},
                    {name: 'Reg_Num', mapping: 'Reg_Num'},
                    {name: 'Reg_Firm', mapping: 'Reg_Firm'},
                    {name: 'Reg_Country', mapping: 'Reg_Country'},
                    {name: 'Reg_Period', mapping: 'Reg_Period'},
                    {name: 'Reg_ReRegDate', mapping: 'Reg_ReRegDate'}
                ],
                key: 'Drug_id',
                sortInfo: { field: 'Drug_Name' },
                url:'/?c=WhsDocumentSupply&m=loadDrugCombo'
            }),
            childrenList: [],
            listeners: {
                'change': function(combo, newValue) {
                    combo.childrenList.forEach(function(field_name){
                        var f_combo = wnd.form.findField(field_name);
                        if (!f_combo.disabled) {
                            f_combo.clearValue();
                            f_combo.getStore().baseParams[combo.hiddenName] = !Ext.isEmpty(combo.getValue()) ? combo.getValue()  : null;
                            f_combo.loadData();
                        }
                    });
                    combo.setLinkedFieldValues('change');
                }
            },
            loadData: function() {
                var combo = this;
                combo.getStore().load({
                    callback: function(){
                        combo.setValue(null);
                    }
                });
            },
            clearValue: function() {
                var combo = this
                sw.Promed.SwBaseRemoteCombo.superclass.clearValue.apply(this, arguments);
                combo.childrenList.forEach(function(field_name){
                    var f_combo = wnd.form.findField(field_name);
                    if (!f_combo.disabled) {
                        f_combo.getStore().baseParams[combo.hiddenName] = null;
                        f_combo.clearValue();
                    }
                });
                combo.setLinkedFieldValues('clear');
            },
            setValueById: function(id) {
                var combo = this;
                combo.getStore().baseParams[combo.valueField] = id;
                combo.getStore().load({
                    callback: function(){
                        combo.setValue(id);
                        combo.getStore().baseParams[combo.valueField] = null;
                        combo.childrenList.forEach(function(field_name){
                            var f_combo = wnd.form.findField(field_name);
                            f_combo.getStore().baseParams[combo.valueField] = !Ext.isEmpty(id) ? id : null;
                        });
                        combo.setLinkedFieldValues('set_by_id');
                    }
                });
            },
            getSelectedRecordData: function() {
                var combo = this;
                var value = combo.getValue();
                var data = new Object();
                if (value > 0) {
                    var idx = this.getStore().findBy(function(record) {
                        return (record.get(combo.valueField) == value);
                    })
                    if (idx > -1) {
                        Ext.apply(data, this.getStore().getAt(idx).data);
                    }
                }
                return data;
            },
            setLinkedFieldValues: function(event_name) {
                var drug_data = this.getSelectedRecordData(),
					ndsCombo = wnd.form.findField('DrugNds_id');

                wnd.form.findField('DrugNomen_Code').setValue(drug_data.DrugNomen_Code);

                if (event_name != 'set_by_id') {
                	var wdssNds = wnd.form.findField('WhsDocumentSupplySpec_NDS').getValue();
                    if (drug_data.DrugNomen_DrugNds_id) {
                        ndsCombo.setValue(drug_data.DrugNomen_DrugNds_id);
                        ndsCombo.fireEvent('select', ndsCombo);
                    } else if (wdssNds) {
						ndsCombo.setValueByCode(wdssNds);
					} else {
                        ndsCombo.setValueByCode(0);
					}
				}

                wnd.form.findField('Firm_Name').setValue(drug_data.Firm_Name);
                wnd.form.findField('Country_Name').setValue(drug_data.Country_Name);
                wnd.form.findField('Reg_Num').setValue(drug_data.Reg_Num);
                wnd.form.findField('Reg_Firm').setValue(drug_data.Reg_Firm);
                wnd.form.findField('Reg_Country').setValue(drug_data.Reg_Country);
                wnd.form.findField('Reg_Period').setValue(drug_data.Reg_Period);
                wnd.form.findField('Reg_ReRegDate').setValue(drug_data.Reg_ReRegDate);
                wnd.setGoodsUnit(drug_data);
                wnd.setMaxSalePrice();
            }
        });

		wnd.drugprep_combo = new sw.Promed.SwBaseRemoteCombo ({
            fieldLabel: lang['torgovoe_naimenovanie'],
            hiddenName: 'DrugPrep_id',
            displayField: 'DrugPrep_Name',
            valueField: 'DrugPrep_id',
            allowBlank: true,
            editable: true,
            width: 500,
            triggerAction: 'all',
            tpl: new Ext.XTemplate(
                '<tpl for="."><div class="x-combo-list-item">',
                '<table style="width:100%;border: 0;"><td style="width:80%;"><h3>{DrugPrep_Name}</h3></td><td style="width:20%;"></td></tr></table>',
                '</div></tpl>'
            ),
            store: new Ext.data.SimpleStore({
                autoLoad: false,
                fields: [
                    {name: 'DrugPrep_id', mapping: 'DrugPrep_id'},
					{name: 'DrugPrep_Name', mapping: 'DrugPrep_Name'},
					{name: 'DrugPrepFas_id', mapping: 'DrugPrepFas_id'}
                ],
                key: 'DrugPrep_id',
                sortInfo: { field: 'DrugPrep_Name' },
                url:'/?c=Farmacy&m=loadDrugPrepList'
            }),
            childrenList: [],
            listeners: {
                'change': function(combo, newValue) {
                    
                }
            },
            loadData: function(params) {
                var combo = this;
                var cParams = {};
                if(params){
                	cParams = params;
                }
                combo.getStore().load({
                	params: cParams,
                    callback: function(){
                        //combo.setValue(null);
                    }
                });
            }
        });

		var form = new Ext.Panel({
			autoScroll: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			border: false,
			frame: true,
			region: 'center',
			labelAlign: 'right',
			items: [{
				xtype: 'form',
				autoHeight: true,
				id: 'WhsDocumentSupplySpecEditForm',
				style: 'margin-bottom: 0.5em;',
				bodyStyle:'background:#DFE8F6;padding:5px;',
				border: true,
				labelWidth: 200,
				labelAlign: 'right',
				collapsible: true,
				region: 'north',
				url:'/?c=WhsDocumentSupplySpec&m=save',
				items: [{
					name: 'WhsDocumentSupplySpec_id',
					xtype: 'hidden',
					value: 0
				}, {
					allowDecimals: false,
					allowNegative: false,
					allowBlank: true,
					fieldLabel: lang['№_p_p'],
					name: 'WhsDocumentSupplySpec_PosCode',
					xtype: 'numberfield',
					maxLength: 3,
					width: 129
				}, {
					xtype: 'panel',
					title: lang['lot'],
					layout: 'form',
					frame: true,
					collapsible: true,
					style: 'margin-top: 7px; margin-bottom: 7px;',
					bodyStyle: 'padding: 7px;',
					labelWidth: 187,
					items: [
                        wnd.proc_spec_combo,
						{
							layout: 'form',
							items: [{
								xtype: 'textfield',
								name: 'Tradenames_Name',
								fieldLabel: lang['torgovoe_naimenovanie'],
								anchor: '95%',
								disabled: true
							}]
						},
						{
							layout: 'form',
							items: [{
                                xtype: 'textfield',
                                name: 'Manufacturer_Country_Name',
                                fieldLabel: 'Страна производства',
                                readOnly: true,
                                width: 150
                            }]
						},
						{
							layout: 'column',
							labelWidth: 41,
							border: false,
							items: [{
								layout: 'form',
								labelWidth: 187,
								items: [{
									xtype: 'numberfield',
									name: 'WhsDocumentProcurementRequestSpec_Kolvo',
									fieldLabel: 'Кол-во в закупе',
									allowDecimals: true,
									allowNegative: false,
									width: 129,
                                    disabled: true/*,
									listeners: {
										'change': function() {
											wnd.setNotSupply();
										}
									}*/
								}]
							}, {
                                layout: 'form',
                                style: 'font-size:12px; padding-top: 3px; padding-left: 5px;',
                                items: [{
                                    xtype: 'label',
                                    text: lang['up'],
                                    width: 25
                                }]
                            }, {
                                layout: 'form',
                                labelWidth: 100,
                                style: 'padding: 0; padding-left: 15px;',
                                items: [{
                                    xtype: 'numberfield',
                                    width: 130,
                                    hideLabel: true,
                                    name: 'WhsDocumentProcurementRequestSpec_GoodsCount',
                                    disabled: true
                                }]
                            }, {
                                layout: 'form',
                                labelWidth: 100,
                                style: 'padding: 0; padding-left: 5px;',
                                items: [{
                                    xtype: 'swgoodsunitcombo',
                                    width: 130,
                                    hideLabel: true,
                                    hiddenName: 'WhsDocumentProcurementRequestSpec_GoodsUnit_id',
                                    disabled: true
                                }]
                            }]
						},
						{
							layout: 'column',
							labelWidth: 41,
							border: false,
							items: [{
								layout: 'form',
								labelWidth: 187,
								items: [{
									xtype: 'numberfield',
									name: 'Ostat_Kolvo',
									fieldLabel: lang['ostatok'],
									allowDecimals: true,
									allowNegative: false,
									width: 129,
                                    disabled: true
								}]
							}, {
                                layout: 'form',
                                style: 'font-size:12px; padding-top: 3px; padding-left: 5px;',
                                items: [{
                                    xtype: 'label',
                                    text: lang['up'],
                                    width: 25
                                }]
                            }, {
                                layout: 'form',
                                labelWidth: 100,
                                style: 'padding: 0; padding-left: 15px;',
                                items: [{
                                    xtype: 'numberfield',
                                    width: 130,
                                    hideLabel: true,
                                    name: 'Ostat_GoodsCount',
                                    disabled: true
                                }]
                            }, {
                                layout: 'form',
                                labelWidth: 100,
                                style: 'padding: 0; padding-left: 5px;',
                                items: [{
                                    xtype: 'swgoodsunitcombo',
                                    width: 130,
                                    hideLabel: true,
                                    hiddenName: 'Ostat_GoodsUnit_id',
                                    disabled: true
                                }]
							}]
						}
					]
				}, 
				{
					xtype: 'panel',
					title: 'Медикамент прайса',
					layout: 'form',
					frame: true,
					name: 'Price_Drugs',
					id: 'Price_Drugs',
					collapsible: true,
					style: 'margin-top: 7px; margin-bottom: 7px;',
                    bodyStyle: 'padding: 7px;',
                    labelWidth: 187,
                    items: [
                    	{
							allowDecimals: false,
							allowNegative: false,
							allowBlank: true,
							fieldLabel: 'Код СКП',
							name: 'CommercialOfferDrug_PriceDetail',
							xtype: 'numberfield',
							width: 130,
							listeners: {
								'change': function(field,value)
								{
									wnd.form.findField('CommercialOfferDrug_MnnName').setValue('');
									wnd.form.findField('CommercialOfferDrug_PharmName').setValue('');
									wnd.form.findField('CommercialOfferDrug_Form').setValue('');
									wnd.form.findField('CommercialOfferDrug_Package').setValue('');
									wnd.form.findField('CommercialOfferDrug_Prod').setValue('');
									wnd.form.findField('CommercialOfferDrug_RegCertName').setValue('');
									wnd.form.findField('CommercialOfferDrug_UnitName').setValue('');
									var combo = wnd.drug_combo;
									var actmatters_combo = wnd.actmatters_combo;
									if(!Ext.isEmpty(value))
									{
										Ext.Ajax.request({
											url: '/?c=CommercialOffer&m=getCommercialOfferDrugDetail',
											params: {
												CommercialOfferDrug_PriceDetail: value
											},
											success: function(response){
												var result = Ext.util.JSON.decode(response.responseText);
												if(result[0])
												{
													wnd.form.findField('CommercialOfferDrug_MnnName').setValue(result[0].CommercialOfferDrug_MnnName);
													wnd.form.findField('CommercialOfferDrug_PharmName').setValue(result[0].CommercialOfferDrug_PharmName);
													wnd.form.findField('CommercialOfferDrug_Form').setValue(result[0].CommercialOfferDrug_Form);
													wnd.form.findField('CommercialOfferDrug_Package').setValue(result[0].CommercialOfferDrug_Package);
													wnd.form.findField('CommercialOfferDrug_Prod').setValue(result[0].CommercialOfferDrug_Prod);
													wnd.form.findField('CommercialOfferDrug_RegCertName').setValue(result[0].CommercialOfferDrug_RegCertName);
													wnd.form.findField('CommercialOfferDrug_UnitName').setValue(result[0].CommercialOfferDrug_UnitName);
													var DrugComplexMnn_id = result[0].DrugComplexMnn_id;
													var ACTMATTERS_ID = result[0].ACTMATTERS_ID;
													if(!Ext.isEmpty(result[0].Drug_id) && result[0].Drug_id > 0)
													{
														actmatters_combo.getStore().removeAll();
								                    	actmatters_combo.getStore().baseParams[actmatters_combo.valueField] = ACTMATTERS_ID;
								                    	actmatters_combo.getStore().load({
								                    		params: {
								                    			DrugComplexMnn_id: DrugComplexMnn_id
								                    		},
								                    		callback: function(){
								                    			actmatters_combo.setValue(ACTMATTERS_ID);
								                    			actmatters_combo.fireEvent('change', actmatters_combo, ACTMATTERS_ID);
								                    			combo.getStore().removeAll();
		                                                		combo.getStore().baseParams[combo.valueField] = result[0].Drug_id;
		                                                		combo.getStore().load({
		                                                			params: {
				                                                		Drug_id: result[0].Drug_id
				                                                	},
				                                                	callback: function(){
				                                                		combo.setValue(result[0].Drug_id);
										                    			combo.fireEvent('change', combo, result[0].Drug_id);
				                                                	}
		                                                		});
								                    		}
								                    	});
								                    	actmatters_combo.disable();
								                    	combo.disable();
													}
												}
											}
										});
									}
									else
									{
										actmatters_combo.enable();
										actmatters_combo.clearValue();

								        combo.enable();
								        combo.clearValue();

								        combo.getStore().baseParams[combo.valueField] = null;
								        actmatters_combo.getStore().baseParams[actmatters_combo.valueField] = null;

								        actmatters_combo.getStore().removeAll();
								        actmatters_combo.getStore().load();

								        combo.getStore().removeAll();
								        combo.getStore().load();
									}
								}
							}
						},
						{
							fieldLabel: 'Непат. наим.',
							disabled: true,
							allowBlank: true,
							name: 'CommercialOfferDrug_MnnName',
							xtype: 'textfield',
							width: 400
						},
						{
							fieldLabel: 'Наименование',
							disabled: true,
							allowBlank: true,
							name: 'CommercialOfferDrug_PharmName',
							xtype: 'textfield',
							width: 400	
						},
						{
							fieldLabel: 'Лекарственная форма',
							disabled: true,
							allowBlank: true,
							name: 'CommercialOfferDrug_Form',
							xtype: 'textfield',
							width: 400
						},
						{
							fieldLabel: 'Фасовка',
							disabled: true,
							allowBlank: true,
							name: 'CommercialOfferDrug_Package',
							xtype: 'textfield',
							width: 400
						},
						{
							fieldLabel: 'Производитель',
							disabled: true,
							allowBlank: true,
							name: 'CommercialOfferDrug_Prod',
							xtype: 'textfield',
							width: 400
						},
						{
							fieldLabel: '№ РУ',
							disabled: true,
							allowBlank: true,
							name: 'CommercialOfferDrug_RegCertName',
							xtype: 'textfield',
							width: 400
						},
						{
							fieldLabel: 'Единица измерения',
							disabled: true,
							allowBlank: true,
							name: 'CommercialOfferDrug_UnitName',
							xtype: 'textfield',
							width: 400
						}
                    ]
				},
				{
                    xtype: 'panel',
                    title: 'Закуплено',
                    layout: 'form',
                    frame: true,
                    collapsible: true,
                    style: 'margin-top: 7px; margin-bottom: 7px;',
                    bodyStyle: 'padding: 7px;',
                    labelWidth: 187,
                    items: [
                    wnd.actmatters_combo,
                    {
                        fieldLabel: lang['regionalnyiy_kod'],
                        name: 'DrugNomen_Code',
                        xtype: 'textfield',
                        listeners: {
                            'change': function(field, newValue, oldValue) {
                                if (!Ext.isEmpty(newValue)) {
                                    Ext.Ajax.request({
                                        url: '/?c=DrugNomen&m=getDrugByDrugNomenCode',
                                        params: {
                                            DrugNomen_Code: newValue
                                        },
                                        success: function(response){
                                            var result = Ext.util.JSON.decode(response.responseText);
                                            if (result[0] && result[0].Drug_id) {
                                                var combo = wnd.drug_combo;

                                                combo.fireEvent('beforeselect', combo);

                                                combo.getStore().removeAll();
                                                combo.getStore().loadData([{
                                                    Drug_id: result[0].Drug_id,
                                                    Drug_Name: result[0].Drug_Name,
                                                    DrugNomen_Code: newValue
                                                }], true);

                                                combo.setValue(result[0].Drug_id);
                                                var index = combo.getStore().findBy(function(rec) { return rec.get('Drug_id') == result[0].Drug_id; });

                                                if (index == -1) {
                                                    return false;
                                                }

                                                var record = combo.getStore().getAt(index);

                                                if ( typeof record == 'object' ) {
                                                    combo.fireEvent('select', combo, record, 0);
                                                    combo.fireEvent('change', combo, record.get('Drug_id'));
                                                }
                                            } else {
                                                field.setValue(oldValue);
                                            }
                                        }
                                    });
                                } else {
                                    wnd.drug_combo.reset();
                                    wnd.drug_combo.fireEvent('change', wnd.drug_combo, null);
                                }
                            }
                        }
                    },
                    {
                    	layout: 'column',
                    	border: false,
                    	items: [{
                        	layout: 'form',
                        	border: false,
                        	items: [{
		                        fieldLabel: lang['kod'],
		                        name: 'DrugPrepFasCode_Code',
		                        xtype: 'textfield',
		                        listeners: {
		                            'change': function(field, newValue, oldValue) {
		                                if (!Ext.isEmpty(newValue)) {
		                                	wnd.drugprep_combo.loadData({'DrugPrepFasCode_Code':newValue});
		                                }
		                            }
		                        }
		                    }]
                        }, {
                        	layout: 'form',
                        	border: false,
                        	labelWidth: 175,
                        	items: [wnd.drugprep_combo]
                        }]
                    },
                    wnd.drug_combo,
                    {
                        layout: 'column',
                        items: [{
                            layout: 'form',
                            items: [{
                                xtype: 'textfield',
                                name: 'Firm_Name',
                                fieldLabel: 'Производитель',
                                disabled: true,
                                width: 150
                            }]
                        }, {
                            layout: 'form',
                            labelWidth: 166,
                            items: [{
                                xtype: 'textfield',
                                name: 'Country_Name',
                                fieldLabel: 'Страна производителя',
                                disabled: true,
                                width: 150
                            }]
                        }]
                    }, {
                        layout: 'column',
                        items: [{
                            layout: 'form',
                            items: [{
                                xtype: 'textfield',
                                name: 'Reg_Num',
                                fieldLabel: 'РУ',
                                disabled: true,
                                width: 150
                            }]
                        }, {
                            layout: 'form',
                            labelWidth: 166,
                            items: [{
                                xtype: 'textfield',
                                name: 'Reg_Period',
                                fieldLabel: 'Период действия',
                                disabled: true,
                                width: 150
                            }]
                        }, {
                            layout: 'form',
                            labelWidth: 166,
                            items: [{
                                xtype: 'textfield',
                                name: 'Reg_ReRegDate',
                                fieldLabel: 'Дата переоформления',
                                disabled: true,
                                width: 150
                            }]
                        }]
                    }, {
                        layout: 'column',
                        items: [{
                            layout: 'form',
                            items: [{
                                xtype: 'textfield',
                                name: 'Reg_Firm',
                                fieldLabel: 'Держатель/Владелец РУ',
                                disabled: true,
                                width: 150
                            }]
                        }, {
                            layout: 'form',
                            labelWidth: 166,
                            items: [{
                                xtype: 'textfield',
                                name: 'Reg_Country',
                                fieldLabel: 'Страна владельца',
                                disabled: true,
                                width: 150
                            }]
                        }]
                    }, {
                        xtype: 'numberfield',
                        allowDecimals:true,
                        allowNegative:true,
                        allowBlank:false,
                        fieldLabel: 'Срок хранения (%)',
                        name: 'WhsDocumentSupplySpec_ShelfLifePersent'
                    }, {
                        xtype: 'textfield',
                        name: 'IsJnvlp',
                        fieldLabel: lang['jnvlp'],
                        disabled: true
                    }, {
                        xtype: 'panel',
                        title: lang['predelnyie_tsenyi_na_jnvlp'] + ' (' + getCurrencyName() + ')',
                        layout: 'form',
                        frame: true,
                        collapsible: true,
                        style: 'margin-top: 7px; margin-bottom: 7px;',
                        bodyStyle: 'padding: 7px;',
                        labelWidth: 175,
                        items: [{
                            layout: 'form',
                            items: [{
                                xtype: 'textfield',
                                name: 'MakerPrice',
                                fieldLabel: lang['proizvoditelya'],
                                allowBlank: true,
                                disabled: true,
                                width: 129
                            }]
                        }, {
                            layout: 'form',
                            items: [{
                                xtype: 'textfield',
                                name: 'MaxRetailPriceNDS',
                                fieldLabel: lang['roznichnaya_s_nds'],
                                allowBlank: true,
                                disabled: true,
                                width: 129
                            }]
                        }, {
                            layout: 'form',
                            items: [{
                                xtype: 'textfield',
                                name: 'MaxWholeSalePriceNDS',
                                fieldLabel: lang['optovaya_s_nds'],
                                allowBlank: true,
                                disabled: true,
                                width: 129
                            }]
                        }]
                    }, {
                        layout: 'column',
                        items: [{
                            layout: 'form',
                            items: [{
                                xtype: 'numberfield',
                                name: 'WhsDocumentSupplySpec_Count',
                                fieldLabel: 'Кол-во из лота',
                                allowDecimals: true,
                                allowNegative: false,
                                allowBlank: true,
                                disabled: false,
                                width: 129,
                                listeners: {
                                    'change': function(combo, newValue) {
                                        wnd.setSpecCount(newValue);
                                        wnd.setSpecCountKoef();
                                    }
                                }
                            }]
                        }, {
                            layout: 'form',
                            style: 'font-size:12px; padding-top: 3px; padding-left: 5px;',
                            items: [{
                                xtype: 'label',
                                text: lang['up'],
                                width: 25
                            }]
                        }, {
                            layout: 'form',
                            labelWidth: 100,
                            style: 'padding: 0; padding-left: 15px;',
                            items: [{
                                xtype: 'numberfield',
                                width: 130,
                                hideLabel: true,
                                name: 'SpecCount_GoodsCount',
                                disabled: true
                            }]
                        }, {
                            layout: 'form',
                            labelWidth: 100,
                            style: 'padding: 0; padding-left: 5px;',
                            items: [{
                                xtype: 'swgoodsunitcombo',
                                width: 130,
                                hideLabel: true,
                                hiddenName: 'SpecCount_GoodsUnit_id',
                                disabled: true
                            }]
                        }, {
                            layout: 'form',
                            labelWidth: 100,
                            style: 'padding: 0; padding-left: 5px;',
                            items: [{
                                xtype: 'numberfield',
                                width: 130,
                                hideLabel: true,
                                name: 'SpecCount_Koef',
                                allowNegative: false,
                                allowDecimal: true,
                                listeners: {
                                    'change': function(field, newValue) {
                                        wnd.setSpecCountKoef(newValue);
                                    }
                                }
                            }]
                        }]
                    }, {
						layout: 'column',
						items: [{
                            layout: 'form',
                            items: [{
                                fieldLabel: langs('Единица поставки (ОКЕИ)'),
                                hiddenName: 'Okei_id',
                                xtype: 'swcommonsprcombo',
                                allowBlank:false,
                                sortField:'Okei_Code',
                                comboSubject: 'Okei',
                                displayedField: 'Okei_Name',
                                width: 129,
                                disabled: true
                            }]
						}, {
                            layout: 'form',
                            items: [{
                                fieldLabel: 'Ед.изм.',
                                hiddenName: 'contr_GoodsUnit_id',
                                xtype: 'swcommonsprcombo',
                                sortField:'GoodsUnit_Code',
                                comboSubject: 'GoodsUnit',
                                displayedField: 'GoodsUnit_Name',
                                width: 129,
                                editable: true,
                                listeners: {
                                    'change':function(){
                                        wnd.setGoodsUnitQty();
                                    }
                                },
                                getSelectedRecordData: function() {
                                    var combo = this;
                                    var value = combo.getValue();
                                    var data = new Object();
                                    if (value > 0) {
                                        var idx = this.getStore().findBy(function(record) {
                                            return (record.get(combo.valueField) == value);
                                        })
                                        if (idx > -1) {
                                            Ext.apply(data, this.getStore().getAt(idx).data);
                                        }
                                    }
                                    return data;
                                },
                                getRecordDataByName: function(name) {
                                    var combo = this;
                                    var data = new Object();
                                    if (!Ext.isEmpty(name)) {
                                        var idx = this.getStore().findBy(function(record) {
                                            return (record.get(combo.displayedField) == name);
                                        })
                                        if (idx > -1) {
                                            Ext.apply(data, this.getStore().getAt(idx).data);
                                        }
                                    }
                                    return data;
                                }
                            }]
                        }]
                    }, {
						layout: 'column',
						items: [{
                            layout: 'form',
                            items: [{
                                xtype: 'numberfield',
                                name: 'WhsDocumentSupplySpec_KolvoUnit',
                                fieldLabel: langs('Количество'),
                                allowDecimals: true,
                                allowNegative: false,
                                allowBlank: false,
                                width: 129,
                                listeners: {
                                    'change': function(field, newValue) {
                                        if (newValue == 0) {
                                            field.setValue(null);
                                        }

                                        var price = wnd.form.findField('WhsDocumentSupplySpec_Price').getValue();
                                        var price_nds = wnd.form.findField('WhsDocumentSupplySpec_PriceNDS').getValue();
                                        if (price > 0) {
                                            wnd.setSumFromPrice();
                                        } else if (price_nds > 0) {
                                            wnd.setSumFromPriceNDS();
                                        }
                                        wnd.setSpecCountKoef();
                                        wnd.setGoodsUnitQty();
                                    }
                                }
                            }]
						}, {
                            layout: 'form',
                            items: [{
                                xtype: 'numberfield',
                                name: 'WhsDocumentSupplySpec_GoodsUnitQty',
                                width: 129,
                                fieldLabel: 'Кол-во ед.изм.'
                            }]
                        }]
                    }, {
                        layout: 'column',
                        items: [{
                            layout: 'form',
                            items: [{
                                xtype: 'numberfield',
                                name: 'WhsDocumentSupplySpec_PriceNDS',
                                fieldLabel: lang['tsena_s_nds'] + ' (' + getCurrencyName() + ')',
                                allowBlank:false,
                                allowDecimals: true,
                                allowNegative: false,
                                width: 129,
                                minValue: 0.01,
                                listeners: {
                                    'change': function() {
                                        wnd.setSumFromPriceNDS();
                                    }
                                }
                            }]
                        }, {
                            layout: 'form',
                            items: [{
                                xtype: 'numberfield',
                                name: 'WhsDocumentSupplySpec_SuppPrice',
                                allowDecimals: true,
                                allowNegative: false,
                                width: 129,
                                fieldLabel: 'Цена поставщика'
                            }]
                        }, {
                            layout: 'form',
                            hidden: true,
                            items: [{
                                xtype: 'numberfield',
                                name: 'SumField',
                                allowDecimals: true,
                                allowNegative: false,
                                width: 129,
                                fieldLabel: lang['summa_rub'],
                                allowBlank:false
                            }]
                        }]
                    }, {
                        layout: 'form',
                        items: [{
							// ставка НДС спецификации
                            xtype: 'swdrugndscombo',
                            hiddenName: 'DrugNds_id',
							name: 'DrugNds_id',
                            valueField: 'DrugNds_id',
                            fieldLabel: lang['stavka_nds_%'],
                            allowBlank: false,
                            width: 125,
                            listeners: {
                                'select': function(combo) {
                                    wnd.form.findField('WhsDocumentSupplySpec_NDS').setValue(combo.getCode());
                                    if (wnd.form.findField('WhsDocumentSupplySpec_PriceNDS').getValue()) {
                                        wnd.setSumFromPriceNDS();
                                    } else {
                                        wnd.setSumFromPrice();
                                    }
                                }
                            },
                            setValueByCode: function(code) {
                                var id = null;
                                this.getStore().each(function(record){
                                    if (record.get('DrugNds_Code') == code) {
                                        id = record.get('DrugNds_id');
                                        return false;
                                    }
                                });
                                this.setValue(id);
                            },
                            getCode: function() {
                                var id = this.getValue();
                                var code = null;
                                this.getStore().each(function(record){
                                    if (record.get('DrugNds_id') == id) {
                                        code = record.get('DrugNds_Code');
                                        return false;
                                    }
                                });
                                return code;
                            }
                        }, {
							// ставка НДС спецификации (не передается, хранит временные значения)
                            layout: 'form',
                            hidden: true,
                            items: [{
                                xtype: 'textfield',
                                disabled: true,
                                name: 'WhsDocumentSupplySpec_NDS',
                                fieldLabel: lang['stavka_nds_%'],
                                allowBlank: false,
                                width: 129
                            }]
                        }, {
                            xtype: 'textfield',
                            disabled: true,
                            name: 'Nds_Sum',
                            fieldLabel: lang['v_t_ch_nds'],
                            allowBlank: false,
                            width: 129
                        }]
                    }, {
                        layout: 'form',
                        hidden: true,
                        items: [{
                            xtype: 'numberfield',
                            name: 'WhsDocumentSupplySpec_Price',
                            fieldLabel: lang['tsena_rub'],
                            allowBlank:false,
                            allowDecimals: true,
                            allowNegative: false,
                            width: 129,
                            listeners: {
                                'change': function() {
                                    wnd.setSumFromPrice();
                                }
                            }
                        }]
                    }, {
                        layout: 'form',
                        items: [{
                            xtype: 'numberfield',
                            name: 'WhsDocumentSupplySpec_SumNDS',
                            fieldLabel: lang['summa_s_nds'] + ' (' + getCurrencyName() + ')',
                            disabled: true,
                            width: 129
                        }]
                    }]
                }]
			}],
			reader: new Ext.data.JsonReader({
				success: Ext.emptyFn
			}, [
				{name: 'WhsDocumentSupplySpec_id'}, 
				{name: 'WhsDocumentSupply_id'}, 
				{name: 'WhsDocumentSupplySpec_PosCode'}, 
				{name: 'DrugComplexMnn_id'}, 
				{name: 'FIRMNAMES_id'}, 
				{name: 'WhsDocumentSupplySpec_KolvoForm'}, 
				{name: 'DRUGPACK_id'}, 
				{name: 'Okei_id'}, 
				{name: 'WhsDocumentSupplySpec_KolvoUnit'}, 
				{name: 'WhsDocumentSupplySpec_KolvoMin'}, 
				{name: 'WhsDocumentSupplySpec_Price'}, 
				{name: 'WhsDocumentSupplySpec_NDS'}, 
				{name: 'WhsDocumentSupplySpec_SumNDS'}, 
				{name: 'WhsDocumentSupplySpec_PriceNDS'}, 
				{name: 'WhsDocumentSupplySpec_ShelfLifePersent'}
			]),
			url: '/?c=WhsDocumentSupplySpec&m=save'
		});
		Ext.apply(this, {
			layout: 'border',
			buttons: [{
				handler: function() {
					this.ownerCt.doSave();
				},
				iconCls: 'save16',
				text: BTN_FRMSAVE
			}, {
				text: '-'
			},
			HelpButton(this, 0), //todo проставить табиндексы
			{
				handler: function() {
					this.ownerCt.hide();
				},
				iconCls: 'cancel16',
				text: BTN_FRMCANCEL
			}],
			items:[form]
		});
		sw.Promed.swWhsDocumentSupplySpecEditWindow.superclass.initComponent.apply(this, arguments);
		this.form = this.findById('WhsDocumentSupplySpecEditForm').getForm();
	}	
});