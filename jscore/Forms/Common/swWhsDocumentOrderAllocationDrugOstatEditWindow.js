/**
* swWhsDocumentOrderAllocationDrugOstatEditWindow - окно редактирования позиции сводной разнарядки
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2014 Swan Ltd.
* @author       Alexandr Chebukin
* @version      08.2016
* @comment      
*/
sw.Promed.swWhsDocumentOrderAllocationDrugOstatEditWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: false,
	title: 'Медикамент в разнарядке: Добавление',
	layout: 'border',
	id: 'WhsDocumentOrderAllocationDrugOstatEditWindow',
	modal: true,
	shim: false,
	width: 700,
	height: 480,
	callback: Ext.emptyFn,
	resizable: false,
	maximizable: false,
	maximized: false,
	doSave:  function() {
		var wnd = this;
		var base_form = wnd.DrugForm.getForm();
		
		if (!base_form.isValid()) {
			sw.swMsg.show( {
				buttons: Ext.Msg.OK,
				fn: function() {
					wnd.DrugForm.getFirstInvalidEl().focus(false);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
		
		var params = new Object();
		params.action = wnd.action;
		params.DrugFinance_id = this.DrugFinance_id;
		params.WhsDocumentCostItemType_id = this.WhsDocumentCostItemType_id;
		params.WhsDocumentUc_pid = this.supply_combo.getValue();
		params.WhsDocumentUc_Name = this.supply_combo.getFieldValue('WhsDocumentUc_Num');
		params.Supplier_Name = this.supply_combo.getFieldValue('Supplier_Name');
		params.Drug_id = base_form.findField('Drug_id').getValue();	
		params.Actmatters_RusName = this.drug_combo.getFieldValue('DrugComplexMnn_RusName');
		params.Tradenames_Name = base_form.findField('DrugTorg_Name').getValue();
		params.DrugForm_Name = base_form.findField('DrugForm_Name').getValue();
		params.Drug_Dose = base_form.findField('Drug_Dose').getValue();
		params.Drug_Fas = base_form.findField('Drug_Fas').getValue();
		params.Reg_Num = base_form.findField('Reg_Num').getValue();
		params.Reg_Firm = base_form.findField('Reg_Firm').getValue();
		params.Reg_Country = base_form.findField('Reg_Country').getValue();
		params.Reg_Period = base_form.findField('Reg_Period').getValue();
		params.Reg_ReRegDate = base_form.findField('Reg_ReRegDate').getValue();
		params.WhsDocumentOrderAllocationDrug_id = base_form.findField('WhsDocumentOrderAllocationDrug_id').getValue();
		params.WhsDocumentOrderAllocation_id = base_form.findField('WhsDocumentOrderAllocation_id').getValue();
		params.WhsDocumentSupply_id = base_form.findField('WhsDocumentSupply_id').getValue();
		params.DrugComplexMnn_id = base_form.findField('DrugComplexMnn_id').getValue();
		params.Kolvo = base_form.findField('Kolvo').getValue();
		params.DrugNds_id = base_form.findField('DrugNds_id').getValue();
		params.Price = base_form.findField('Price').getValue();
		
		wnd.callback(params);
		wnd.hide();
		
	},
	setFieldsDisabled: function(d) {
		var wnd = this;
		var base_form = wnd.DrugForm.getForm();
		
		base_form.items.each(function(f)  {
			if (f && (f.xtype!='hidden') && (f.xtype!='fieldset')  && (f.changeDisabled!==false)) {
				f.setDisabled(d);
			}
		});
		wnd.buttons[0].setDisabled(d);
	},
	show: function() {
		var wnd = this;
		sw.Promed.swWhsDocumentOrderAllocationDrugOstatEditWindow.superclass.show.apply(this, arguments);
		var base_form = wnd.DrugForm.getForm();
		this.callback = Ext.emptyFn;
		this.DrugFinance_id = null;
		this.WhsDocumentCostItemType_id = null;
		this.WhsDocumentType_id = 23;
		this.action = 'add';

        if (!arguments[0]) {
            sw.swMsg.alert('Ошибка', 'Не указаны входные данные', function() { wnd.hide(); });
            return false;
        }
		if (arguments[0].callback && typeof arguments[0].callback == 'function') {
			this.callback = arguments[0].callback;
		}
		if (arguments[0].action) {
			this.action = arguments[0].action;
		}
		if (arguments[0].params.DrugFinance_id && arguments[0].params.DrugFinance_id > 0) {
			this.DrugFinance_id = arguments[0].params.DrugFinance_id;
		}
		if (arguments[0].params.WhsDocumentCostItemType_id && arguments[0].params.WhsDocumentCostItemType_id > 0) {
			this.WhsDocumentCostItemType_id = arguments[0].params.WhsDocumentCostItemType_id;
		}
		
		base_form.reset();
		base_form.setValues(arguments[0].params);
		this.supply_combo.store.baseParams = {};
		
		if (this.action == 'add') {
			this.setTitle('Медикамент в разнарядке: Добавление');
			this.setFieldsDisabled(false);
			this.supply_combo.store.baseParams.DrugFinance_id = this.DrugFinance_id;
			this.supply_combo.store.baseParams.WhsDocumentType_ids = Ext.util.JSON.encode([19]);
			this.supply_combo.store.baseParams.WhsDocumentCostItemType_id = this.WhsDocumentCostItemType_id;
			this.supply_combo.loadData();
		} 
		else if (this.action == 'view') {
			this.setTitle('Медикамент в разнарядке: Просмотр');
			this.setFieldsDisabled(true);
			this.supply_combo.store.baseParams.WhsDocumentType_ids = Ext.util.JSON.encode([19]);
			this.supply_combo.setValueById(this.supply_combo.getValue());
			this.drug_combo.setValueById(this.drug_combo.getValue());
		}
	},
	initComponent: function() {
		var wnd = this;

        this.supply_combo = new sw.Promed.SwBaseRemoteCombo ({
            fieldLabel: lang['kontrakt'],
            hiddenName: 'WhsDocumentSupply_id',
            displayField: 'WhsDocumentUc_Name',
            valueField: 'WhsDocumentSupply_id',
            allowBlank: false,
            editable: true,
			trigger1Class: 'x-form-search-trigger',
			trigger2Class: 'x-form-plus-trigger',
            width: 500,
            triggerAction: 'all',
            tpl: new Ext.XTemplate(
                '<table cellpadding="0" cellspacing="0" style="width: 100%;"><tr style="font-family: tahoma; font-size: 8pt; font-weight: bold;">',
                '<td style="padding: 2px; width: 20%;">'+lang['№']+'</td>',
                '<td style="padding: 2px; width: 40%;">'+lang['naimenovanie']+'</td>',
                '<td style="padding: 2px; width: 40%;">'+lang['postavschik']+'</td>',
                '<tpl for="."><tr class="x-combo-list-item" style="font-family: tahoma; font-size: 8pt;">',
                '<td style="padding: 2px;">{WhsDocumentUc_Num}&nbsp;</td>',
                '<td style="padding: 2px;">{WhsDocumentUc_Name}&nbsp;</td>',
                '<td style="padding: 2px;">{Supplier_Name}&nbsp;</td>',
                '</tr></tpl>',
                '</table>'
            ),
            store: new Ext.data.SimpleStore({
                autoLoad: false,
                fields: [
                    { name: 'WhsDocumentUc_id', mapping: 'WhsDocumentUc_id' },
                    { name: 'WhsDocumentSupply_id', mapping: 'WhsDocumentSupply_id' },
                    { name: 'WhsDocumentCostItemType_id', mapping: 'WhsDocumentCostItemType_id' },
                    { name: 'DrugFinance_id', mapping: 'DrugFinance_id' },
                    { name: 'WhsDocumentUc_Name', mapping: 'WhsDocumentUc_Name' },
                    { name: 'Supplier_Name', mapping: 'Supplier_Name' },
                    { name: 'WhsDocumentSupply_Year', mapping: 'WhsDocumentSupply_Year' },
                    { name: 'WhsDocumentUc_Num', mapping: 'WhsDocumentUc_Num' }
                ],
                key: 'WhsDocumentSupply_id',
                sortInfo: { field: 'WhsDocumentUc_Name' },
                url:'/?c=WhsDocumentSupply&m=loadWhsDocumentSupplyCombo'
            }),
            onTrigger2Click: function() {
				if (this.disabled)
					return false;

				var combo = this;
				combo.disableBlurAction = true;
				var params = {};
				params.action = 'add';
				params.WhsDocumentType_id = 19;
				params.WhsDocumentType_Name = 'Контракт ввода остатков';
				params.DrugFinance_id = wnd.DrugFinance_id;
				params.WhsDocumentCostItemType_id = wnd.WhsDocumentCostItemType_id;
				params.isOstat = true;
				params.onHide = function() {
					combo.focus(false);
					combo.disableBlurAction = false;
				},
				params.afterSave = function (dt) {
					if (dt.WhsDocumentUc_id) {
						combo.setValueById(dt.WhsDocumentUc_id);
					}
				};
				getWnd('swWhsDocumentSupplyEditWindow').show(params);
            },
			onTrigger1Click: function() {
				if (this.disabled)
					return false;

				var searchWindow = 'swWhsDocumentSupplySelectWindow';
				var params = this.getStore().baseParams;
				var combo = this;
				combo.disableBlurAction = true;
				getWnd(searchWindow).show({
					params: params,
					searchUrl: '/?c=Farmacy&m=loadWhsDocumentSupplyList',
					FilterPanelEnabled: true,
					onHide: function() {
						combo.focus(false);
						combo.disableBlurAction = false;
					},
					onSelect: function (data) {
						if (data.WhsDocumentUc_id) {
							combo.setValueById(data.WhsDocumentUc_id);
						}
						getWnd(searchWindow).hide();
					}
				});
			},
            setValueById: function(id) {
                var combo = this;
                combo.store.baseParams.WhsDocumentSupply_id = id;
                combo.store.load({
                    callback: function(){
                        combo.setValue(id);
                        combo.store.baseParams.WhsDocumentSupply_id = null;
                    }
                });
            },
            loadData: function() {
                var combo = this;
                combo.store.load({
                    callback: function(){
                        combo.setValue(null);
                    }
                });
            }
        });
		
        this.drug_combo = new sw.Promed.SwBaseRemoteCombo ({
            fieldLabel: lang['naimenovanie'],
            hiddenName: 'Drug_id',
            displayField: 'Drug_Name',
            valueField: 'Drug_id',
            allowBlank: false,
            editable: true,
			width: 500,
            triggerAction: 'all',
			trigger2Class: 'x-form-search-trigger',
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
                    {name: 'DrugComplexMnn_RusName', mapping: 'DrugComplexMnn_RusName'},
                    {name: 'Firm_Name', mapping: 'Firm_Name'},
                    {name: 'Country_Name', mapping: 'Country_Name'},
                    {name: 'Reg_Num', mapping: 'Reg_Num'},
                    {name: 'Reg_Firm', mapping: 'Reg_Firm'},
                    {name: 'Reg_Country', mapping: 'Reg_Country'},
                    {name: 'Reg_Period', mapping: 'Reg_Period'},
                    {name: 'Reg_ReRegDate', mapping: 'Reg_ReRegDate'},
                    {name: 'DrugTorg_Name', mapping: 'DrugTorg_Name'},
                    {name: 'DrugForm_Name', mapping: 'DrugForm_Name'},
                    {name: 'Drug_Fas', mapping: 'Drug_Fas'},
                    {name: 'Drug_Dose', mapping: 'Drug_Dose'}
                ],
                key: 'Drug_id',
                sortInfo: { field: 'Drug_Name' },
                url:'/?c=WhsDocumentSupply&m=loadDrugCombo'
            }),
            listeners: {
                'change': function(combo, newValue) {
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
                combo.setLinkedFieldValues();
            },
            setValueById: function(id) {
                var combo = this;
                combo.getStore().baseParams[combo.valueField] = id;
                combo.getStore().load({
                    callback: function(){
                        combo.setValue(id);
                        combo.getStore().baseParams[combo.valueField] = null;
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
				var base_form = wnd.DrugForm.getForm();
                var drug_data = this.getSelectedRecordData();
                base_form.findField('DrugTorg_Name').setValue(drug_data.DrugTorg_Name);
                base_form.findField('DrugForm_Name').setValue(drug_data.DrugForm_Name);
                base_form.findField('Drug_Fas').setValue(drug_data.Drug_Fas);
                base_form.findField('Drug_Dose').setValue(drug_data.Drug_Dose);
                base_form.findField('Reg_Num').setValue(drug_data.Reg_Num);
                base_form.findField('Reg_Firm').setValue(drug_data.Reg_Firm);
                base_form.findField('Reg_Country').setValue(drug_data.Reg_Country);
                base_form.findField('Reg_Period').setValue(drug_data.Reg_Period);
                base_form.findField('Reg_ReRegDate').setValue(drug_data.Reg_ReRegDate);
            },
			onTrigger2Click: function() {
				if (this.disabled)
					return false;

				var combo = this;

				getWnd('swRlsDrugTorgSearchWindow').show({
					searchFull: true,
					FormValues: {
						DrugComplexMnn_id: combo.getStore().baseParams.DrugComplexMnn_id
					},
					onHide: function() {
						combo.focus(false);
					},
					onSelect: function(drugData) {
						combo.setValueById(drugData.Drug_id);
						getWnd('swRlsDrugTorgSearchWindow').hide();
					}
				});
			}
        });

		this.DrugForm = new Ext.FormPanel({
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			height: 450,
			border: false,			
			frame: true,
			region: 'center',
			layout: 'form',
			labelAlign: 'right',
			labelWidth: 150,
			items: [{
				xtype: 'hidden',
				name: 'WhsDocumentOrderAllocationDrug_id'
			}, {
				xtype: 'hidden',
				name: 'WhsDocumentOrderAllocation_id'
			},
			this.supply_combo,
			{
				xtype: 'swdrugcomplexmnncombo',	
				hiddenName: 'DrugComplexMnn_id',
				fieldLabel: lang['mnn'],
				width: 500,
				allowBlank: false,
				listeners: {
					'select': function(combo, record) {
					
						var drug_combo = wnd.drug_combo;
						
						drug_combo.clearValue();
						drug_combo.getStore().removeAll();
						drug_combo.lastQuery = '';
						
						drug_combo.getStore().baseParams = {};
						drug_combo.getStore().baseParams.DrugComplexMnn_id = record.get('DrugComplexMnn_id');
						
						if (record) {
							drug_combo.getStore().load();
						}
						
					}.createDelegate(this)
				}
			}, 
			this.drug_combo,
			{
				xtype: 'textfield',
				fieldLabel : lang['torgovoe_naimenovanie'],
				name: 'DrugTorg_Name',
				width: 500,
				allowBlank: true,
				disabled: true,
				changeDisabled: false
			}, {
				xtype: 'textfield',
				fieldLabel: lang['lekarstvennaya_forma'],
				name: 'DrugForm_Name',
				width: 250,
				allowBlank: true,
				disabled: true,
				changeDisabled: false
			}, {
				xtype: 'textfield',
				fieldLabel: lang['dozirovka'],
				name: 'Drug_Dose',
				width: 250,
				allowBlank: true,
				disabled: true,
				changeDisabled: false
			}, {
				xtype: 'textfield',
				fieldLabel: lang['fasovka'],
				name: 'Drug_Fas',
				width: 250,
				allowBlank: true,
				disabled: true,
				changeDisabled: false
			}, {
				xtype: 'textfield',
				fieldLabel: lang['ru'],
				name: 'Reg_Num',
				width: 250,
				allowBlank: true,
				disabled: true,
				changeDisabled: false
			}, {
				xtype: 'textfield',
				fieldLabel: lang['period_deystviya_ru'],
				name: 'Reg_Period',
				width: 250,
				allowBlank: true,
				disabled: true,
				changeDisabled: false
			}, {
				xtype: 'textfield',
				fieldLabel: lang['derjatel_vladelets_ru'],
				name: 'Reg_Firm',
				width: 500,
				allowBlank: true,
				disabled: true,
				changeDisabled: false
			}, {
				xtype: 'textfield',
				fieldLabel: lang['strana_derjatelya_vladeltsa_ru'],
				name: 'Reg_Country',
				width: 250,
				allowBlank: true,
				disabled: true,
				changeDisabled: false
			}, {
				xtype: 'hidden',
				name: 'Reg_ReRegDate'
			}, {
				xtype: 'numberfield',
				allowDecimals:true,
				allowNegative:true,
				allowBlank:false,
				fieldLabel: 'Срок хранения (%)',
				name: 'WhsDocumentSupplySpec_ShelfLifePersent',
				value: '70',
				width: 125
			}, {
				xtype: 'numberfield',
				name: 'Kolvo',
				fieldLabel: lang['kolichestvo'],
				allowDecimals: true,
				allowNegative: false,
				allowBlank: false,
				width: 125
			}, {
				xtype: 'swdrugndscombo',
				hiddenName: 'DrugNds_id',
				valueField: 'DrugNds_id',
				fieldLabel: lang['stavka_nds_%'],
				allowBlank: true,
				disabled: true,
				changeDisabled: false,
				width: 125,
				value: 2
			}, {
				xtype: 'numberfield',
				name: 'Price',
				fieldLabel: lang['tsena_s_nds'],
				allowDecimals: true,
				allowNegative: false,
				allowBlank: false,
				width: 125
			}]
		});

		Ext.apply(this, {
			layout: 'fit',
			buttons:
			[{
				handler: function() {
					this.ownerCt.doSave();
				},
				iconCls: 'ok16',
				text: 'Добавить'
			}, 
			{
				text: '-'
			},
			HelpButton(this, 0),
			{
				handler: function() {
					this.ownerCt.hide();
				},
				iconCls: 'cancel16',
				text: BTN_FRMCANCEL
			}],
			items:[this.DrugForm]
		});
		sw.Promed.swWhsDocumentOrderAllocationDrugOstatEditWindow.superclass.initComponent.apply(this, arguments);
	}
});