/**
* swJNVLPPriceViewWindow - окно просмотра цен на ЖНВЛП
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2014 Swan Ltd.
* @author       Salakhov R.
* @version      01.2014
* @comment      
*/
sw.Promed.swJNVLPPriceViewWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: false,
	title: lang['tsenyi_na_jnvlp'],
	layout: 'border',
	id: 'JNVLPPriceViewWindow',
	modal: false,
	shim: false,
	width: 400,
	resizable: false,
	maximizable: true,
	maximized: true,
	doSearch: function() {
		var wnd = this;
		var form = wnd.FilterPanel.getForm();
		var params = new Object();

		wnd.DrugGrid.removeAll();
		params = form.getValues();

		params.start = 0;
		params.limit = 100;

		wnd.DrugGrid.loadData({params: params, globalFilters: params});
	},
	doReset: function() {
		var wnd = this;
		var form = wnd.FilterPanel.getForm();
		form.reset();
		wnd.DrugGrid.removeAll();
	},
	exportToCSV: function() {
		var wnd = this;
		var form = wnd.FilterPanel.getForm();
		var params = new Object();

		params = form.getValues();

		wnd.getLoadMask(lang['formirovanie_fayla']).show();
		Ext.Ajax.request({
			scope: this,
			params: params,
			url: '/?c=RlsDrug&m=exportJNVLPPrice',
			callback: function(o, s, r) {
				wnd.getLoadMask().hide();
				if(s) {
					var obj = Ext.util.JSON.decode(r.responseText);
					if( obj.success ) {
						window.open(obj.url);
					}
				}
			}
		});
	},
	show: function() {
        var wnd = this;
		sw.Promed.swJNVLPPriceViewWindow.superclass.show.apply(this, arguments);

		wnd.DrugMarkupDeliveryCombo.ownerCt.hide();
		//wnd.DrugMarkupDeliveryCombo.getStore().load();

		if(!wnd.DrugGrid.getAction('export_csv')) {
			wnd.DrugGrid.addActions({
				name: 'actions',
				iconCls: 'rpt-xls',
				text: lang['eksport_v_csv'],
				handler: wnd.exportToCSV.createDelegate(wnd),
                hidden: getWnd('swWorkPlaceSpecMEKLLOWindow').isVisible()
			});
		}

		wnd.doReset();
	},
	initComponent: function() {
		var wnd = this;

		wnd.DrugMarkupDeliveryCombo = new Ext.form.ComboBox({
			name: 'DrugMarkup_Delivery',
			fieldLabel: lang['zona'],
			width: 250,
			typeAhead: true,
			triggerAction: 'all',
			lazyRender: true,
			readOnly: true,
			mode: 'local',
			store: new Ext.data.Store({
				autoLoad: false,
				reader: new Ext.data.JsonReader({
					id: 'DrugMarkup_Delivery'
				}, [
					{ name: 'DrugMarkup_Delivery', mapping: 'DrugMarkup_Delivery' }
				]),
				sortInfo: {
					field: 'DrugMarkup_Delivery'
				},
				url: '/?c=RlsDrug&m=loadDrugMarkupDeliveryList',
				listeners: {
					'load': function(store) {
						if (store.getCount() > 1) { //для отображения комбобокса должно быть минимум 2 зоны доступных для выбора
							wnd.DrugMarkupDeliveryCombo.ownerCt.show();
						} else {
							wnd.DrugMarkupDeliveryCombo.ownerCt.hide();
						}
						wnd.doLayout();
					}
				}
			}),
			tpl:'<tpl for="."><div class="x-combo-list-item">'+
				'{DrugMarkup_Delivery}&nbsp;'+
				'</div></tpl>',
			valueField: 'DrugMarkup_Delivery',
			displayField: 'DrugMarkup_Delivery'
		});

		wnd.FilterFieldsPanel = new sw.Promed.Panel({
			autoScroll: true,
			bodyBorder: false,
			border: false,
			frame: true,
			items: [{
				layout:'form',
				items: [{
					name: 'ActMatters_RusName',
					fieldLabel: lang['mnn'],
					width: 250,
					xtype: 'textfield'
				}]
			}, {
				layout:'form',
				items: [{
					name: 'Prep_Name',
					fieldLabel: lang['torgovoe_naim'],
					width: 250,
					xtype: 'textfield'
				}]
			}, {
				layout:'form',
				items: [{
					name: 'DrugForm_Name',
					fieldLabel: lang['lekarstvennaya_forma'],
					width: 250,
					xtype: 'textfield'
				}]
			}, {
				layout:'form',
				items: [{
					name: 'IsNarko',
					hiddenName: 'IsNarko',
					fieldLabel: lang['narkotika'],
					width: 250,
					valueField: 'YesNo_Code',
					xtype: 'swyesnocombo'
				}]
			}, {
				layout:'form',
				items: [wnd.DrugMarkupDeliveryCombo]
			}, {
				layout: 'column',
				items: [{
					layout:'form',
					items: [{
						style: "padding-left: 10px",
						xtype: 'button',
						id: 'DNSW_BtnSearch',
						text: lang['rasschitat'],
						iconCls: 'ok16',
						minWidth: 100,
						handler: function() {
							wnd.doSearch();
						}.createDelegate(this)
					}]
				}, {
					layout:'form',
					items: [{
						style: "padding-left: 10px",
						xtype: 'button',
						id: 'DNSW_BtnReset',
						text: lang['sbros'],
						iconCls: 'reset16',
						minWidth: 100,
						handler: function() {
							wnd.doReset();
							//wnd.doSearch();
						}.createDelegate(this)
					}]
				}]
			}]
		});

		wnd.FilterPanel = getBaseFiltersFrame({
			region: 'north',
			defaults: {bodyStyle:'background:#DFE8F6;width:100%;'},
			ownerWindow: wnd,
			toolBar: wnd.WindowToolbar,
			items: [
				wnd.FilterFieldsPanel
			]
		});

		wnd.DrugGrid = new sw.Promed.ViewFrame({
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			autoLoadData: false,
			//editformclassname: 'swDrugNomenEditWindow',
			dataUrl: '/?c=RlsDrug&m=loadJNVLPPriceGrid',
			id: wnd.id + 'DrugGrid',
			object: 'Drug',
			scheme: 'rls',
			paging: true,
			region: 'center',
			root: 'data',
			actions: [
				{name: 'action_add', disabled: true, hidden: true},
				{name: 'action_edit', disabled: true, hidden: true},
				{name: 'action_view', disabled: true, hidden: true},
				{name: 'action_delete', disabled: true, hidden: true},
				{name: 'action_refresh'},
				{name: 'action_print'}
			],
			stringfields: [
				{name: 'Key_id', type: 'int', header: 'ID', key: true },
				{name: 'Nomen_id', hidden: true },
				{name: 'ActMatters_RusName', header: lang['mnn'], width: 80 },
				{name: 'Prep_Name', header: lang['lp'], width: 80, id: 'autoexpand' },
				{name: 'DrugForm_Name', header: lang['lekarstvennayaforma'], width: 95 },
				{name: 'Drug_Dose', header: lang['dozirovka'], width: 80 },
				{name: 'Drug_Fas', header: lang['fasovka'], width: 120 },
				{name: 'Reg_Num', header: lang['№_ru'], width: 80 },
				{name: 'Firm_Name', header: lang['proizvoditel'], width: 80 },
				{name: 'Price_Date', header: lang['data_reg_tsenyi'], width: 80 },
				{name: 'Price_Order', header: lang['№_resheniya'], width: 80 },
				{name: 'Price', header: lang['zareg_tsena_proizv_rub'], type: 'float', width: 80 },
				{name: 'Drugmarkup_Delivery', header: lang['zona'], width: 80 },
				{name: 'Wholesale_Markup', header: lang['opt_nadb'], type: 'float', width: 80 },
				{name: 'Wholesale_Price', header: lang['opt_tsena'], type: 'float', width: 80 },
				{name: 'Wholesale_NdsPrice', header: lang['opt_tsenas_nds'], type: 'float', width: 80 },
				{name: 'Retail_Markup', header: lang['rozn_nadb'], type: 'float', width: 80 },
				{name: 'Retail_Price', header: lang['rozn_tsena'], type: 'float', width: 80 },
				{name: 'Retail_NdsPrice', header: lang['rozn_tsenas_nds'], width: 80 }
		],
			totalProperty: 'totalCount'
		});

		Ext.apply(this, {
			layout: 'border',
			buttons:
			[{
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
			items:[
				wnd.FilterPanel,
				wnd.DrugGrid
			]
		});
		sw.Promed.swJNVLPPriceViewWindow.superclass.initComponent.apply(this, arguments);
	}
});