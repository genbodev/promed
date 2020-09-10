/**
* swWhsDocumentSupplySpecDrugEditWindow - окно редактирования синонима
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Farmacy
* @access       public
* @copyright    Copyright (c) 2016 Swan Ltd.
* @author       Salakhov R.
* @version      05.2016
* @comment      
*/
sw.Promed.swWhsDocumentSupplySpecDrugEditWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: false,
	title: 'Синоним медикамента в контракте',
	layout: 'border',
	id: 'WhsDocumentSupplySpecDrugEditWindow',
	modal: true,
	shim: false,
	width: 400,
	resizable: false,
	maximizable: false,
	maximized: true,
    calculateSynonymKolvo: function() {
        var coef = this.form.findField('WhsDocumentSupplySpecDrug_Coeff').getValue();
        var spec_kolvo = this.form.findField('WhsDocumentSupplySpec_KolvoUnit').getValue();
        var syn_kolvo_field = this.form.findField('Synonym_Kolvo');
        var syn_kolvo = 0;

        coef = coef*1 != coef ? 0 : coef*1;
        spec_kolvo = spec_kolvo*1 != spec_kolvo ? 0 : spec_kolvo*1;
        syn_kolvo = spec_kolvo*coef;

        syn_kolvo_field.setValue(syn_kolvo);
    },
    setDisabled: function(disable) {
        var field_arr = [
            'WhsDocumentSupply_id',
            'WhsDocumentSupplySpec_id',
            'WhsDocumentSupplySpecDrug_Coeff',
            'Drug_sid'
        ];
        var obj_arr = ['wdseFileUploadPanel', 'wdseBtnGraphEdit'];

        for (var i in field_arr) if (this.form.findField(field_arr[i])) {
            var field = this.form.findField(field_arr[i]);
            if (disable || field.enable_blocked) {
                field.disable();
            } else {
                field.enable();
            }
        }

        if (disable) {
            this.buttons[0].disable();
        } else {
            this.buttons[0].enable();
        }
    },
    setDefaultValues: function() {
        this.form.findField('WhsDocumentSupplySpecDrug_Coeff').setValue(1);
        this.calculateSynonymKolvo();
    },
    setDrugData: function(data) {
        var wnd = this;

        var params = {
            Object: 'Drug',
            Drug_id: null,
            Date: null
        };

        params = Ext.apply(params, data);

        if (!Ext.isEmpty(data.Combo)) {
            var id = data.Combo.getValue();

            data.Combo.getStore().each(function (record) {
                if  (record.get(data.Combo.valueField) == id) {
                    params.Drug_id = record.get('Drug_id');
                    return false;
                }
            });
        }

        wnd.form.findField(params.Object+'_DrugNomen_Code').setValue(null);
        wnd.form.findField(params.Object+'_Actmatters_Name').setValue(null);
        wnd.form.findField(params.Object+'_IsJnvlp').setValue(null);
        wnd.form.findField(params.Object+'_MakerPrice').setValue(null);
        wnd.form.findField(params.Object+'_MaxRetailPriceNDS').setValue(null);
        wnd.form.findField(params.Object+'_MaxWholeSalePriceNDS').setValue(null);

        if (params.Drug_id > 0) {
            Ext.Ajax.request({
                params: params,
                url: '/?c=WhsDocumentSupply&m=getWhsDocumentSupplySpecDrugContext',
                callback: function(options, success, response) {
                    if (success) {
                        var response_obj = Ext.util.JSON.decode(response.responseText);

                        if (response_obj.length > 0) {
                            wnd.form.findField(params.Object+'_DrugNomen_Code').setValue(response_obj[0].DrugNomen_Code);
                            wnd.form.findField(params.Object+'_Actmatters_Name').setValue(response_obj[0].Actmatters_Name);
                            wnd.form.findField(params.Object+'_IsJnvlp').setValue(response_obj[0].IsJnvlp);
                            wnd.form.findField(params.Object+'_MakerPrice').setValue(sw.Promed.Format.rurMoney(response_obj[0].MakerPrice));
                            wnd.form.findField(params.Object+'_MaxRetailPriceNDS').setValue(sw.Promed.Format.rurMoney(response_obj[0].MaxRetailPriceNDS));
                            wnd.form.findField(params.Object+'_MaxWholeSalePriceNDS').setValue(sw.Promed.Format.rurMoney(response_obj[0].MaxWholeSalePriceNDS));
                        }

                        if (response_obj[0].IsJnvlp > 0) {
                            wnd.form.findField(params.Object+'_MakerPrice').ownerCt.show();
                        } else {
                            wnd.form.findField(params.Object+'_MakerPrice').ownerCt.hide();
                        }
                    }
                }
            });
        }
    },
	doSave: function() {
		var wnd = this;
		if ( !this.form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					wnd.findById('WhsDocumentSupplySpecDrugEditForm').getFirstInvalidEl().focus(true);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
        this.submit();
		return true;		
	},
	submit: function() {
		var wnd = this;
		var params = new Object();

        params.WhsDocumentSupplySpecDrug_id = this.WhsDocumentSupplySpecDrug_id;
        params.WhsDocumentSupplySpec_id = this.spec_combo.getValue();
        params.Drug_id = this.spec_combo.getSelectedRecordData().Drug_id;
        params.Drug_sid = this.syn_combo.getValue();
        params.WhsDocumentSupplySpecDrug_Price = wnd.form.findField('WhsDocumentSupplySpecDrug_Price').getValue();
        params.WhsDocumentSupplySpecDrug_PriceSyn = wnd.form.findField('WhsDocumentSupplySpecDrug_PriceSyn').getValue();

        if (params.WhsDocumentSupplySpecDrug_Price <= 0 || params.WhsDocumentSupplySpecDrug_PriceSyn <= 0) {
            Ext.Msg.alert('Ошибка', 'Цена медикамента должна быть больше нуля, сохранение прервано');
            return false;
        }

		wnd.getLoadMask('Подождите, идет сохранение...').show();
		this.form.submit({
			params: params,
			failure: function(result_form, action) {
				wnd.getLoadMask().hide();
				if (action.result) {
					if (action.result.Error_Code) {
						Ext.Msg.alert('Ошибка #'+action.result.Error_Code, action.result.Error_Message);
					}
				}
			},
			success: function(result_form, action) {
				wnd.getLoadMask().hide();
				if (action.result && action.result.WhsDocumentSupplySpecDrug_id > 0) {
					var id = action.result.WhsDocumentSupplySpecDrug_id;
					wnd.form.findField('WhsDocumentSupplySpecDrug_id').setValue(id);
					wnd.callback(wnd.owner, id);
					wnd.hide();
				}
			}
		});
	},
	show: function() {
        var wnd = this;
		sw.Promed.swWhsDocumentSupplySpecDrugEditWindow.superclass.show.apply(this, arguments);		
		this.action = '';
		this.callback = Ext.emptyFn;
		this.WhsDocumentSupplySpecDrug_id = null;
		this.WhsDocumentSupply_id = null;
		this.WhsDocumentSupplySpec_id = null;

        if ( !arguments[0] ) {
            sw.swMsg.alert('Ошибка', 'Не указаны входные данные', function() { wnd.hide(); });
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
		if ( arguments[0].WhsDocumentSupplySpecDrug_id ) {
			this.WhsDocumentSupplySpecDrug_id = arguments[0].WhsDocumentSupplySpecDrug_id;
		}
		if ( arguments[0].WhsDocumentSupply_id ) {
			this.WhsDocumentSupply_id = arguments[0].WhsDocumentSupply_id;
		}
		if ( arguments[0].WhsDocumentSupplySpec_id ) {
			this.WhsDocumentSupplySpec_id = arguments[0].WhsDocumentSupplySpec_id;
		}

        this.supply_combo.enable_blocked = (this.WhsDocumentSupply_id > 0);
        this.spec_combo.enable_blocked = (this.WhsDocumentSupplySpec_id > 0);

		this.setTitle("Синоним медикамента в контракте");
		this.form.reset();
        this.form.findField('Drug_MakerPrice').ownerCt.hide();
        this.form.findField('Synonym_MakerPrice').ownerCt.hide();

        var loadMask = new Ext.LoadMask(this.form.getEl(), {msg:'Загрузка...'});
        loadMask.show();
		switch (this.action) {
			case 'add':
				this.setTitle(this.title + ": Добавление");
                this.setDisabled(false);
                this.setDefaultValues();
                if (this.WhsDocumentSupply_id > 0) {
                    this.supply_combo.setValueById(this.WhsDocumentSupply_id);
                }
                if (this.WhsDocumentSupplySpec_id > 0) {
                    this.spec_combo.setValueById(this.WhsDocumentSupplySpec_id);
                }
				loadMask.hide();
				break;
			case 'edit':
            case 'view':
				this.setTitle(this.title + (this.action == "edit" ? ": Редактирование" : ": Просмотр"));
                Ext.Ajax.request({
                    params:{
                        WhsDocumentSupplySpecDrug_id: wnd.WhsDocumentSupplySpecDrug_id
                    },
                    failure:function () {
                        sw.swMsg.alert(lang['oshibka'], lang['ne_udalos_poluchit_dannyie_s_servera']);
                        loadMask.hide();
                        wnd.hide();
                    },
                    success: function (response) {
                        var result = Ext.util.JSON.decode(response.responseText);
                        if (result[0]) {
                            wnd.form.setValues(result[0]);

                            wnd.supply_combo.setValueById(result[0].WhsDocumentSupply_id);
                            wnd.spec_combo.setValueById(result[0].WhsDocumentSupplySpec_id);
                            wnd.syn_combo.setValueById(result[0].Drug_sid);
                        }
                        wnd.setDisabled(wnd.action == 'view');
                        loadMask.hide();
                    },
                    url:'/?c=WhsDocumentSupply&m=loadWhsDocumentSupplySpecDrug'
                });
				break;
		}
	},
	initComponent: function() {
		var wnd = this;

        wnd.supply_combo = new sw.Promed.SwBaseRemoteCombo ({
            fieldLabel: lang['kontrakt'],
            hiddenName: 'WhsDocumentSupply_id',
            displayField: 'WhsDocumentUc_Name',
            valueField: 'WhsDocumentSupply_id',
            allowBlank: false,
            editable: true,
            anchor: '60%',
            tpl: new Ext.XTemplate(
                '<table cellpadding="0" cellspacing="0" style="width: 100%;"><tr style="font-family: tahoma; font-size: 10pt; font-weight: bold;">',
                '<td style="padding: 2px; width: 25%;">Наименование</td>',
                '<td style="padding: 2px; width: 25%;">Источник финансирования</td>',
                '<td style="padding: 2px; width: 25%;">Статья расхода</td>',
                '<td style="padding: 2px; width: 25%;">Поставщик</td></tr>',
                '<tpl for="."><tr class="x-combo-list-item" {[values.PrepSeries_IsDefect==2?"color: red;":""]}>',
                '<td style="padding: 2px;">{WhsDocumentUc_Name}&nbsp;</td>',
                '<td style="padding: 2px;">{DrugFinance_Name}&nbsp;</td>',
                '<td style="padding: 2px;">{WhsDocumentCostItemType_Name}&nbsp;</td>',
                '<td style="padding: 2px;">{Supplier_Name}&nbsp;</td>',
                '</tr></tpl>',
                '</table>'
            ),
            store: new Ext.data.SimpleStore({
                autoLoad: false,
                fields: [
                    { name: 'WhsDocumentSupply_id', mapping: 'WhsDocumentSupply_id' },
                    { name: 'WhsDocumentUc_Name', mapping: 'WhsDocumentUc_Name' },
                    { name: 'DrugFinance_Name', mapping: 'DrugFinance_Name' },
                    { name: 'WhsDocumentCostItemType_Name', mapping: 'WhsDocumentCostItemType_Name' },
                    { name: 'Supplier_Name', mapping: 'Supplier_Name' },
                    { name: 'WhsDocumentUc_Date', mapping: 'WhsDocumentUc_Date' }
                ],
                key: 'WhsDocumentSupply_id',
                sortInfo: { field: 'WhsDocumentUc_Name' },
                url:'/?c=WhsDocumentSupply&m=loadSynonymSupplyCombo'
            }),
            childrenList: ['WhsDocumentSupplySpec_id'],
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

        wnd.spec_combo = new sw.Promed.SwBaseRemoteCombo ({
            fieldLabel: 'Торговое наименование',
            hiddenName: 'WhsDocumentSupplySpec_id',
            displayField: 'Drug_Name',
            valueField: 'WhsDocumentSupplySpec_id',
            allowBlank: false,
            editable: true,
            anchor: '60%',
            triggerAction: 'all',
            tpl: new Ext.XTemplate(
                '<tpl for="."><div class="x-combo-list-item">',
                '{Drug_Name}&nbsp;',
                '</div></tpl>'
            ),
            store: new Ext.data.SimpleStore({
                autoLoad: false,
                fields: [
                    { name: 'WhsDocumentSupplySpec_id', mapping: 'WhsDocumentSupplySpec_id' },
                    { name: 'Drug_id', mapping: 'Drug_id' },
                    { name: 'Drug_Name', mapping: 'Drug_Name' },
                    { name: 'Actmatters_id', mapping: 'Actmatters_id' },
                    { name: 'WhsDocumentSupplySpec_PriceNDS', mapping: 'WhsDocumentSupplySpec_PriceNDS' },
                    { name: 'WhsDocumentSupplySpec_KolvoUnit', mapping: 'WhsDocumentSupplySpec_KolvoUnit' }
                ],
                key: 'WhsDocumentSupplySpec_id',
                sortInfo: { field: 'Drug_Name' },
                url:'/?c=WhsDocumentSupply&m=loadSynonymSupplySpecCombo'
            }),
            childrenList: ['Drug_sid'],
            listeners: {
                'change': function(combo, newValue) {
                    var supply_data = wnd.supply_combo.getSelectedRecordData();
                    var spec_data = wnd.spec_combo.getSelectedRecordData();
                    combo.childrenList.forEach(function(field_name){
                        var f_combo = wnd.form.findField(field_name);
                        if (!f_combo.disabled) {
                            f_combo.clearValue();
                            f_combo.getStore().baseParams['Actmatters_id'] = !Ext.isEmpty(spec_data.Actmatters_id) ? spec_data.Actmatters_id : null;
                            f_combo.loadData();
                        }
                    });
                    wnd.setDrugData({
                        Object: 'Drug',
                        Date: !Ext.isEmpty(supply_data.WhsDocumentUc_Date) ? supply_data.WhsDocumentUc_Date : null,
                        Combo: combo
                    });
                    combo.setLinkedFieldValues(true);
                }
            },
            setValueById: function(id) {
                var combo = this;
                combo.getStore().baseParams[combo.valueField] = id;
                combo.getStore().load({
                    callback: function(){
                        combo.setValue(id);
                        combo.getStore().baseParams[combo.valueField] = null;
                        var supply_data = wnd.supply_combo.getSelectedRecordData();
                        var spec_data = wnd.spec_combo.getSelectedRecordData();
                        combo.childrenList.forEach(function(field_name){
                            var f_combo = wnd.form.findField(field_name);
                            f_combo.getStore().baseParams['Actmatters_id'] = !Ext.isEmpty(spec_data.Actmatters_id) ? spec_data.Actmatters_id : null;
                        });
                        wnd.setDrugData({
                            Object: 'Drug',
                            Date: !Ext.isEmpty(supply_data.WhsDocumentUc_Date) ? supply_data.WhsDocumentUc_Date : null,
                            Combo: combo
                        });
                        combo.setLinkedFieldValues(wnd.action == 'add');
                    }
                });
            },
            setLinkedFieldValues: function(set_default_values) {
                var spec_data = this.getSelectedRecordData();
                if (set_default_values) {
                    wnd.form.findField('WhsDocumentSupplySpecDrug_Price').setValue(spec_data.WhsDocumentSupplySpec_PriceNDS*1);
                    wnd.form.findField('WhsDocumentSupplySpecDrug_PriceSyn').setValue(spec_data.WhsDocumentSupplySpec_PriceNDS*1);
                }
                wnd.form.findField('WhsDocumentSupplySpec_KolvoUnit').setValue(spec_data.WhsDocumentSupplySpec_KolvoUnit*1);
                wnd.calculateSynonymKolvo();
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
                sw.Promed.SwBaseRemoteCombo.superclass.clearValue.apply(this, arguments);
                this.childrenList.forEach(function(field_name){
                    var f_combo = wnd.form.findField(field_name);
                    if (!f_combo.disabled) {
                        f_combo.getStore().baseParams['Actmatters_id'] = null;
                        f_combo.clearValue();
                    }
                });
                wnd.setDrugData({Object: 'Drug'});
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

        wnd.syn_combo = new sw.Promed.SwBaseRemoteCombo ({
            fieldLabel: 'Торговое наименование',
            hiddenName: 'Drug_sid',
            displayField: 'Drug_Name',
            valueField: 'Drug_id',
            allowBlank: false,
            editable: true,
            anchor: '60%',
            triggerAction: 'all',
            tpl: new Ext.XTemplate(
                '<tpl for="."><div class="x-combo-list-item">',
                '{Drug_Name}&nbsp;',
                '</div></tpl>'
            ),
            store: new Ext.data.SimpleStore({
                autoLoad: false,
                fields: [
                    { name: 'Drug_id', mapping: 'Drug_id' },
                    { name: 'Drug_Name', mapping: 'Drug_Name' }
                ],
                key: 'Drug_id',
                sortInfo: { field: 'Drug_Name' },
                url:'/?c=WhsDocumentSupply&m=loadSynonymDrugCombo'
            }),
            listeners: {
                'change': function(combo, newValue) {
                    wnd.setDrugData({
                        Object: 'Synonym',
                        Combo: combo
                    });
                }
            },
            setValueById: function(id) {
                var combo = this;
                combo.getStore().baseParams[combo.valueField] = id;
                combo.getStore().load({
                    callback: function(){
                        combo.setValue(id);
                        combo.getStore().baseParams[combo.valueField] = null;
                        wnd.setDrugData({
                            Object: 'Synonym',
                            Combo: combo
                        });
                    }
                });
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
                sw.Promed.SwBaseRemoteCombo.superclass.clearValue.apply(this, arguments);
                wnd.setDrugData({Object: 'Synonym'});
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
		
		var form = new Ext.Panel({
			autoScroll: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			height: 70,
			border: false,			
			frame: true,
			region: 'center',
			labelAlign: 'right',
			items: [{
				xtype: 'form',
				autoHeight: true,
				id: 'WhsDocumentSupplySpecDrugEditForm',
				style: 'margin-bottom: 0.5em;',
				bodyStyle:'background:#DFE8F6;padding:5px;',
				border: true,
				labelWidth: 200,
                labelAlign: 'right',
				collapsible: true,
				url:'/?c=WhsDocumentSupply&m=saveWhsDocumentSupplySpecDrug',
				items: [{					
					xtype: 'hidden',
					name: 'WhsDocumentSupplySpecDrug_id'
				},
                wnd.supply_combo,
                {
                    xtype: 'fieldset',
                    title: 'Медикамент в контракте',
                    autoHeight: true,
                    labelWidth: 190,
                    items: [{
                        xtype: 'textfield',
                        fieldLabel: 'Региональный код',
                        name: 'Drug_DrugNomen_Code',
                        disabled: true
                    }, {
                        xtype: 'textfield',
                        fieldLabel: lang['mnn'],
                        name: 'Drug_Actmatters_Name',
                        disabled: true,
                        anchor: '60%'
                    },
                    wnd.spec_combo,
                    {
                        xtype: 'checkbox',
                        fieldLabel: 'ЖНВЛП',
                        name: 'Drug_IsJnvlp',
                        disabled: true
                    }, {
                        xtype: 'fieldset',
                        title: 'Предельные цены на ЖНВЛП',
                        autoHeight: true,
                        labelWidth: 180,
                        items: [{
                            xtype: 'textfield',
                            fieldLabel: 'Производителя',
                            name: 'Drug_MakerPrice',
                            disabled: true
                        }, {
                            xtype: 'textfield',
                            fieldLabel: 'Розничная с НДС',
                            name: 'Drug_MaxRetailPriceNDS',
                            disabled: true
                        }, {
                            xtype: 'textfield',
                            fieldLabel: 'Оптовая с НДС',
                            name: 'Drug_MaxWholeSalePriceNDS',
                            disabled: true
                        }]
                    }, {
                        xtype: 'numberfield',
                        fieldLabel: 'Цена с НДС (руб.)',
                        name: 'WhsDocumentSupplySpecDrug_Price',
                        disabled: true,
                        allowBlank: false,
                        allowNegative: false
                    }, {
                        xtype: 'textfield',
                        fieldLabel: 'Количество',
                        name: 'WhsDocumentSupplySpec_KolvoUnit',
                        disabled: true
                    }]
                }, {
                    xtype: 'numberfield',
                    fieldLabel: 'Коэффициент перерасчета упаковок',
                    name: 'WhsDocumentSupplySpecDrug_Coeff',
                    allowNegative: false,
                    allowDecimals: false,
                    listeners: {
                        'change': function() {
                            wnd.calculateSynonymKolvo();
                        }
                    }
                }, {
                    xtype: 'fieldset',
                    title: 'Медикамент-синоним',
                    autoHeight: true,
                    labelWidth: 190,
                    items: [{
                        xtype: 'textfield',
                        fieldLabel: 'Региональный код',
                        name: 'Synonym_DrugNomen_Code',
                        disabled: true
                    }, {
                        xtype: 'textfield',
                        fieldLabel: lang['mnn'],
                        name: 'Synonym_Actmatters_Name',
                        disabled: true,
                        anchor: '60%'
                    },
                    wnd.syn_combo,
                    {
                        xtype: 'checkbox',
                        fieldLabel: 'ЖНВЛП',
                        name: 'Synonym_IsJnvlp',
                        disabled: true
                    }, {
                        xtype: 'fieldset',
                        title: 'Предельные цены на ЖНВЛП',
                        autoHeight: true,
                        labelWidth: 180,
                        items: [{
                            xtype: 'textfield',
                            fieldLabel: 'Производителя',
                            name: 'Synonym_MakerPrice',
                            disabled: true
                        }, {
                            xtype: 'textfield',
                            fieldLabel: 'Розничная с НДС',
                            name: 'Synonym_MaxRetailPriceNDS',
                            disabled: true
                        }, {
                            xtype: 'textfield',
                            fieldLabel: 'Оптовая с НДС',
                            name: 'Synonym_MaxWholeSalePriceNDS',
                            disabled: true
                        }]
                    }, {
                        xtype: 'numberfield',
                        fieldLabel: 'Цена с НДС (руб.)',
                        name: 'WhsDocumentSupplySpecDrug_PriceSyn',
                        disabled: true,
                        allowBlank: false,
                        allowNegative: false
                    }, {
                        xtype: 'textfield',
                        fieldLabel: 'Количество',
                        name: 'Synonym_Kolvo',
                        disabled: true
                    }]
                }]
			}]
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
			HelpButton(this, 0),//todo проставить табиндексы
			{
				handler: function() 
				{
					this.ownerCt.hide();
				},
				iconCls: 'cancel16',
				text: BTN_FRMCANCEL
			}],
			items:[form]
		});
		sw.Promed.swWhsDocumentSupplySpecDrugEditWindow.superclass.initComponent.apply(this, arguments);
		this.form = this.findById('WhsDocumentSupplySpecDrugEditForm').getForm();
	}	
});