/**
* swWhsDocumentSupplySpecDrugViewWindow - окно просмотра списка синонимов
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
sw.Promed.swWhsDocumentSupplySpecDrugViewWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: false,
	title: 'Синонимы медикаментов в контракте',
	layout: 'border',
	id: 'WhsDocumentSupplySpecDrugViewWindow',
	modal: true,
	shim: false,
	width: 400,
	resizable: false,
	maximizable: false,
	maximized: true,
	doSearch: function() {
		var wnd = this;
		var form = wnd.FilterPanel.getForm();
		var params = new Object();

		wnd.SearchGrid.removeAll();

        params = new Object();
		params.start = 0;
		params.limit = 100;
		params.WhsDocumentSupply_id = form.findField('WhsDocumentSupply_id').getValue();
		params.WhsDocumentSupplySpec_id = form.findField('WhsDocumentSupplySpec_id').getValue();

		wnd.SearchGrid.loadData({params: params, globalFilters: params});
	},
	doReset: function() {
		var wnd = this;
		var form = wnd.FilterPanel.getForm();
		form.reset();
		wnd.SearchGrid.removeAll();
	},	
	show: function() {
        var wnd = this;
        var form = this.FilterPanel.getForm();
		sw.Promed.swWhsDocumentSupplySpecDrugViewWindow.superclass.show.apply(this, arguments);

        this.WhsDocumentSupply_id = null;
        this.WhsDocumentSupplySpec_id = null;

        if (arguments[0]) {
            if (!Ext.isEmpty(arguments[0].WhsDocumentSupply_id)) {
                this.WhsDocumentSupply_id = arguments[0].WhsDocumentSupply_id;
            }
            if (!Ext.isEmpty(arguments[0].WhsDocumentSupplySpec_id)) {
                this.WhsDocumentSupplySpec_id = arguments[0].WhsDocumentSupplySpec_id;
            }
        }

        this.doReset();

        var wds_combo = form.findField('WhsDocumentSupply_id');
        var wdss_combo = form.findField('WhsDocumentSupplySpec_id');

        if (this.WhsDocumentSupply_id > 0 && this.WhsDocumentSupplySpec_id > 0) {
            wds_combo.setValue(this.WhsDocumentSupply_id);
            wds_combo.setValueById(this.WhsDocumentSupply_id);

            wdss_combo.getStore().baseParams.WhsDocumentSupply_id = this.WhsDocumentSupply_id;
            wdss_combo.setValue(this.WhsDocumentSupplySpec_id);
            wdss_combo.setValueById(this.WhsDocumentSupplySpec_id);

            wds_combo.disable();
            wdss_combo.disable();

            this.FilterButtonsPanel.hide();
            this.doLayout();
            this.doSearch();
        } else {
            wdss_combo.getStore().baseParams.WhsDocumentSupply_id = null;

            wds_combo.enable();
            wdss_combo.enable();

            this.FilterButtonsPanel.show();
            this.doLayout();
        }

        wnd.SearchGrid.setParam('WhsDocumentSupply_id', this.WhsDocumentSupply_id, false);
        wnd.SearchGrid.setParam('WhsDocumentSupplySpec_id', this.WhsDocumentSupplySpec_id, false);
	},
	initComponent: function() {
		var wnd = this;

        wnd.supply_combo = new sw.Promed.SwBaseRemoteCombo ({
            fieldLabel: lang['kontrakt'],
            hiddenName: 'WhsDocumentSupply_id',
            displayField: 'WhsDocumentUc_Name',
            valueField: 'WhsDocumentSupply_id',
            allowBlank: true,
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
                    { name: 'Supplier_Name', mapping: 'Supplier_Name' }
                ],
                key: 'WhsDocumentSupply_id',
                sortInfo: { field: 'WhsDocumentUc_Name' },
                url:'/?c=WhsDocumentSupply&m=loadSynonymSupplyCombo'
            }),
            childrenList: ['WhsDocumentSupplySpec_id'],
            listeners: {
                'change': function(combo, newValue) {
                    combo.childrenList.forEach(function(field_name){
                        var f_combo = wnd.FilterPanel.getForm().findField(field_name);
                        if (!f_combo.disabled) {
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
                    var f_combo = wnd.FilterPanel.getForm().findField(field_name);
                    if (!f_combo.disabled) {
                        f_combo.getStore().baseParams[combo.hiddenName] = !Ext.isEmpty(combo.getValue()) ? combo.getValue()  : null;
                        f_combo.clearValue();
                    }
                });
            },
            setValueById: function(id) {
                var combo = this;
                combo.getStore().baseParams.WhsDocumentSupply_id = id;
                combo.getStore().load({
                    callback: function(){
                        combo.setValue(id);
                        combo.getStore().baseParams.WhsDocumentSupply_id = null;
                    }
                });
            }
        });

        wnd.spec_combo = new sw.Promed.SwBaseRemoteCombo ({
            fieldLabel: lang['medikament'],
            hiddenName: 'WhsDocumentSupplySpec_id',
            displayField: 'Drug_Name',
            valueField: 'WhsDocumentSupplySpec_id',
            allowBlank: true,
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
                    { name: 'Drug_Name', mapping: 'Drug_Name' }
                ],
                key: 'WhsDocumentSupplySpec_id',
                sortInfo: { field: 'Drug_Name' },
                url:'/?c=WhsDocumentSupply&m=loadSynonymSupplySpecCombo'
            }),
            childrenList: [],
            listeners: {
                'change': function(combo, newValue) {
                    combo.childrenList.forEach(function(field_name){
                        var f_combo = wnd.FilterPanel.getForm().findField(field_name);
                        if (!f_combo.disabled) {
                            f_combo.getStore().baseParams[combo.hiddenName] = !Ext.isEmpty(combo.getValue()) ? combo.getValue()  : null;
                            f_combo.loadData();
                        }
                    });
                }
            },
            setValueById: function(id) {
                var combo = this;
                combo.getStore().baseParams.WhsDocumentSupplySpec_id = id;
                combo.getStore().load({
                    callback: function(){
                        combo.setValue(id);
                        combo.getStore().baseParams.WhsDocumentSupplySpec_id = null;
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
            }
        });

		this.FilterPanel = new sw.Promed.Panel({
			layout: 'form',
			autoScroll: true,
			bodyBorder: false,
			labelAlign: 'right',
			labelWidth: 140,
			border: false,
			frame: true,
			items: [
                wnd.supply_combo,
                wnd.spec_combo
            ]
		});

		this.FilterButtonsPanel = new sw.Promed.Panel({
			autoScroll: true,
			bodyBorder: false,
			border: false,
			frame: true,
			items: [{
				layout: 'column',
				items: [{
					layout:'form',
					items: [{
						style: "padding-left: 10px",
						xtype: 'button',
						text: 'Поиск',
						iconCls: 'search16',
						minWidth: 100,
						handler: function() {
							wnd.doSearch();
						}
					}]
				}, {
					layout:'form',
					items: [{
						style: "padding-left: 10px",
						xtype: 'button',
						text: 'Очистить',
						iconCls: 'reset16',
						minWidth: 100,
						handler: function() {
							wnd.doReset();
						}
					}]
				}]
			}]
		});

		this.FilterPanel = getBaseFiltersFrame({
			region: 'north',
			defaults: {bodyStyle:'background:#DFE8F6;width:100%;'},
			ownerWindow: wnd,
			toolBar: this.WindowToolbar,
			items: [
				this.FilterPanel,
				this.FilterButtonsPanel
			]
		});

		this.SearchGrid = new sw.Promed.ViewFrame({
			actions: [
				{name: 'action_add'},
				{name: 'action_edit'},
				{name: 'action_view'},
				{name: 'action_delete', url: '/?c=WhsDocumentSupply&m=deleteWhsDocumentSupplySpecDrug'},
				{name: 'action_print'}
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 125,
			autoLoadData: false,
			border: true,
			dataUrl: '/?c=WhsDocumentSupply&m=loadWhsDocumentSupplySpecDrugList',
			height: 180,
			object: 'WhsDocumentSupplySpecDrug',
			editformclassname: 'swWhsDocumentSupplySpecDrugEditWindow',
			id: 'WhsDocumentSupplySpecDrugGrid',
			paging: true,
            pageSize: 100,
            root: 'data',
            totalProperty: 'totalCount',
			style: 'margin-bottom: 10px',
			stringfields: [
				{ name: 'WhsDocumentSupplySpecDrug_id', type: 'int', header: 'ID', key: true },
                { name: 'WhsDocumentUc_Num', type: 'string', header: '№ контракта' },
                { name: 'Drug_Name', type: 'string', header: 'Медикамент из спецификации контракта', id: 'autoexpand' },
                { name: 'WhsDocumentSupplySpec_KolvoUnit', type: 'float', header: 'Кол-во в контракте' },
                { name: 'WhsDocumentSupplySpecDrug_Price', type: 'money', header: 'Цена за одну упаковку в контракте' },
                { name: 'WhsDocumentSupplySpecDrug_Coeff', type: 'float', header: 'Коэффициент пересчета' },
                { name: 'Drug_NameSyn', type: 'string', header: 'Медикамент-синоним' },
                { name: 'WhsDocumentSupplySpecDrug_KolvoUnit', type: 'float', header: 'Кол-во «синонима»' },
                { name: 'WhsDocumentSupplySpecDrug_PriceSyn', type: 'money', header: 'Цена за одну упаковку «синонима»' }
			],
			title: null,
			toolbar: true
		});

		Ext.apply(this, {
			layout: 'border',
			buttons:
			[{
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
				wnd.FilterPanel,
				{
					border: false,
					region: 'center',
					layout: 'border',
					items:[{
						border: false,
						region: 'center',
						layout: 'fit',
						items: [this.SearchGrid]
					}]
				}
			]
		});
		sw.Promed.swWhsDocumentSupplySpecDrugViewWindow.superclass.initComponent.apply(this, arguments);
	}	
});