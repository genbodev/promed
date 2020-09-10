/**
* swWhsDocumentSupplySelectWindow - окно установки фильтров для списка заявок врачей
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Farmacy
* @access       public
* @copyright    Copyright (c) 2014 Swan Ltd.
* @author       Salakhov R.
* @version      07.2014
* @comment      
*/
sw.Promed.swWhsDocumentSupplySelectWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: false,
	title: lang['vyibor_goskontrakta'],
	layout: 'border',
	id: 'WhsDocumentSupplySelectWindow',
	modal: true,
	shim: false,
	width: 900,
	height: 500,
	resizable: false,
	maximizable: false,
	maximized: false,
	listeners: {
		hide: function() {
			this.onHide();
		}
	},
	onHide: Ext.emptyFn,
	doSearch: function() {
		var params = new Object();
		var form = this.FilterPanel.getForm();
		var user_org_id = getGlobalOptions().org_id;

		Ext.apply(params, this.params);
		if (this.FilterPanelEnabled || this.CustomFilterPanelEnabled) {
			Ext.apply(params, form.getValues());
		}
		if (this.CustomFilterPanelEnabled) {
			params.WhsDocumentUc_Num = params.WhsDocumentUc_NumSec;
			delete params.WhsDocumentUc_NumSec;
		}

		params.WhsDocumentRightRecipientOrg_id = form.findField('OrgRid_isEqualUserOrg').checked && user_org_id > 0 ? user_org_id : null;
		params.start = 0;
		params.limit = 100;

		if (
			!Ext.isEmpty(params.WhsDocumentUc_Num) 
			|| !Ext.isEmpty(params.WhsDocumentUc_DateRange) 
			|| !Ext.isEmpty(params.DrugFinance_id) 
			|| !Ext.isEmpty(params.WhsDocumentCostItemType_id) 
			|| !Ext.isEmpty(params.WhsDocumentUc_Num) 
			|| !Ext.isEmpty(params.OrgCid_Name) 
			|| !Ext.isEmpty(params.WhsDocumentRightRecipientOrg_id)
			|| !Ext.isEmpty(params.DrugFinance_Name) 
			|| !Ext.isEmpty(params.WhsDocumentCostItemType_Name) 
			|| !Ext.isEmpty(params.DrugRequest_Name) 
			|| !Ext.isEmpty(params.OrgSid_Name) 
		) {
			this.SearchGrid.loadData({
				globalFilters: params
			});
            this.SearchGrid.getGrid().getBottomToolbar().enable();
		} else {
			this.doReset();
            this.SearchGrid.getGrid().getBottomToolbar().disable();
		}
	},
	doReset: function() {
		this.FilterPanel.getForm().reset();
		this.SearchGrid.removeAll({clearAll: true});
	},
	doSelect:  function() {
		var wnd = this;
		var record = wnd.SearchGrid.getGrid().getSelectionModel().getSelected();

		if (record.get('WhsDocumentSupply_id') <= 0) {
			return false;
		}

		wnd.onSelect(record.data);
		wnd.hide();

		return true;
	},	
	show: function() {
		sw.Promed.swWhsDocumentSupplySelectWindow.superclass.show.apply(this, arguments);

		this.onSelect = Ext.emptyFn;
		this.FilterPanelEnabled = false;
		this.CustomFilterPanelEnabled = false;
		this.ARMType = null;
		this.params = new Object();
		this.searchUrl = '/?c=Farmacy&m=loadWhsDocumentSupplyList';
		var form = this.FilterPanel.getForm();

		if (arguments[0].onSelect && typeof arguments[0].onSelect == 'function') {
			this.onSelect = arguments[0].onSelect;
		}
		if (typeof(arguments[0].FilterPanelEnabled) != 'undefined') {
			this.FilterPanelEnabled = arguments[0].FilterPanelEnabled;
		}
		if (typeof(arguments[0].CustomFilterPanelEnabled) != 'undefined') {
			this.CustomFilterPanelEnabled = arguments[0].CustomFilterPanelEnabled;
		}
		if (arguments[0].searchUrl && arguments[0].searchUrl != '') {
			this.searchUrl = arguments[0].searchUrl;
		}
		if (arguments[0].ARMType) {
			this.ARMType = arguments[0].ARMType;
		}
		if (arguments[0].params) {
			this.params = arguments[0].params;

			if (arguments[0].params.query != undefined) {
				delete arguments[0].params.query;
			}
		}
		
		form.findField('DrugFinance_id').enable();
		form.findField('WhsDocumentCostItemType_id').enable();

		if (this.FilterPanelEnabled) {
			this.FilterPanel.show();
			this.FilterFieldsPanel.show();
			this.CustomFilterFieldsPanel.hide();
		} else if (this.CustomFilterPanelEnabled) {
			this.FilterPanel.show();
			this.FilterFieldsPanel.hide();
			this.CustomFilterFieldsPanel.show();
		} else {
			this.FilterPanel.hide();
		}
		this.doLayout();

		this.SearchGrid.getGrid().getStore().proxy.conn.url = this.searchUrl;
		this.doReset();
		if (this.params.DrugFinance_id && !Ext.isEmpty(this.params.DrugFinance_id)) {
			form.findField('DrugFinance_id').setValue(this.params.DrugFinance_id);
			form.findField('DrugFinance_id').disable();
		}
		if (this.params.WhsDocumentCostItemType_id && !Ext.isEmpty(this.params.WhsDocumentCostItemType_id)) {
			form.findField('WhsDocumentCostItemType_id').setValue(this.params.WhsDocumentCostItemType_id);
			form.findField('WhsDocumentCostItemType_id').disable();
		}

		if (this.ARMType == 'adminllo'){
			form.findField('DrugRequest_Name').showContainer();
		} else {
			form.findField('DrugRequest_Name').hideContainer();
		}

		var hideCol = true;
		if(this.ARMType == 'adminllo' || (this.ARMType == 'merch' && getGlobalOptions().orgtype.inlist(['farm','reg_dlo']))){
			hideCol = false;
		}
		this.SearchGrid.setColumnHidden('DrugRequestPurchaseSpec_string',hideCol);
		this.SearchGrid.setColumnHidden('WhsDocumentUc_Name',!this.CustomFilterPanelEnabled);
		this.SearchGrid.setColumnHidden('DrugFinance_Name',this.CustomFilterPanelEnabled);
		this.SearchGrid.setColumnHidden('WhsDocumentCostItemType_Name',this.CustomFilterPanelEnabled);

		this.doSearch();
	},
	initComponent: function() {
		var wnd = this;

		//Фильтры
		this.FilterFieldsPanel = new sw.Promed.Panel({
			region: 'north',
			layout: 'form',
			autoScroll: true,
			bodyBorder: false,
			labelAlign: 'right',
			labelWidth: 170,
			border: false,
			frame: true,
			items: [
				{
					fieldLabel: lang['nomer_kontrakta'],
					name: 'WhsDocumentUc_Num',
					width: 250,
					xtype: 'textfield'
				},
				new Ext.form.DateRangeField({
					width: 250,
					fieldLabel: lang['data_kontrakta'],
					name: 'WhsDocumentUc_DateRange',
					plugins: [
						new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)
					]
				}),
				{
					fieldLabel: lang['istochnik_finans'],
					xtype: 'swdrugfinancecombo',
					name: 'DrugFinance_id',
					width: 250
				},
				{
					fieldLabel: lang['statya_rashodov'],
					xtype: 'swwhsdocumentcostitemtypecombo',
					name: 'WhsDocumentCostItemType_id',
					width: 250
				},
				{
					fieldLabel: lang['zakazchik'],
					xtype: 'textfield',
					name: 'OrgCid_Name',
					width: 250
				},
				{
					layout: 'form',
					labelWidth: 407,
					items: [{
						fieldLabel: lang['organizatsiya_polzovatelya_yavlyaetsya_poluchatelem_po_kontraktu'],
						xtype: 'checkbox',
						name: 'OrgRid_isEqualUserOrg',
						width: 250
					}]
				}
			]
		});

		//Другой вид фильтров
		this.CustomFilterFieldsPanel = new sw.Promed.Panel({
			region: 'north',
			layout: 'form',
			autoScroll: true,
			bodyBorder: false,
			labelAlign: 'right',
			labelWidth: 170,
			border: false,
			frame: true,
			items: [
				{
					fieldLabel: lang['istochnik_finans'],
					name: 'DrugFinance_Name',
					width: 250,
					xtype: 'textfield'
				},
				{
					fieldLabel: lang['statya_rashodov'],
					name: 'WhsDocumentCostItemType_Name',
					width: 250,
					xtype: 'textfield'
				},
				{
					layout:'form',
					border:false,
					items:[{
						fieldLabel: 'Заявка',
						name: 'DrugRequest_Name',
						width: 250,
						xtype: 'textfield'
					}]
				},
				{
					fieldLabel: lang['nomer_kontrakta'],
					name: 'WhsDocumentUc_NumSec',
					width: 250,
					xtype: 'textfield'
				},
				{
					fieldLabel: 'Поставщик',
					xtype: 'textfield',
					name: 'OrgSid_Name',
					width: 250
				}
			]
		});

		//Кнопки
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
						text: lang['nayti'],
						iconCls: 'search16',
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
						text: lang['sbros'],
						iconCls: 'reset16',
						minWidth: 100,
						handler: function() {
							wnd.doReset();
							wnd.doSearch();
						}.createDelegate(this)
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
				this.FilterFieldsPanel,
				this.CustomFilterFieldsPanel,
				this.FilterButtonsPanel
			]
		});

		this.SearchGrid = new sw.Promed.ViewFrame({
			region: 'center',
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 125,
			autoLoadData: false,
			border: true,
			dataUrl: null,
			height: 180,
			object: 'WhsDocumentSupply',
			id: 'wdssw_SearchGrid',
			paging: true,
			root: 'data',
			totalProperty: 'totalCount',
			style: 'margin-bottom: 10px',
			stringfields: [
				{ name: 'WhsDocumentSupply_id', type: 'int', header: 'ID', key: true },
				{ name: 'WhsDocumentUc_id', hidden: true },
				{ name: 'WhsDocumentUc_Year', type: 'string', header: lang['god'], width: 95 },
				{ name: 'WhsDocumentUc_Sum', type: 'string', header: lang['summa'], width: 95 },
				{ name: 'WhsDocumentUc_Date', type: 'string', header: lang['data'], width: 95 },
				{ name: 'WhsDocumentUc_DateRange', type: 'string', header: 'Период действия', width: 180 },
				{ name: 'Org_sid_Nick', type: 'string', header: lang['postavschik'], width:100, id: 'autoexpand' },
				{ name: 'WhsDocumentUc_Name', type: 'string', header: lang['naimenovanie'], width: 220, hidden: true },
				{ name: 'DrugRequestPurchaseSpec_string', type: 'string',header: 'Заявка и лот', hidden: true },
				{ name: 'WhsDocumentUc_Num', type: 'string', header: lang['№_kontrakta'], width: 220 },
				{ name: 'DrugFinance_id', hidden: true },
				{ name: 'DrugFinance_Name', type: 'string', header: lang['istochnik_finansirovaniya'], width:175 },
				{ name: 'WhsDocumentCostItemType_id', hidden: true },
				{ name: 'WhsDocumentCostItemType_Name', type: 'string', header: lang['statya_rashodov'], width:125 },
				{ name: 'Contragent_sid', hidden: true },
				{ name: 'DrugNds_Code', hidden: true },
				{ name: 'WhsDocumentProcurementRequest_id', hidden: true },
				{ name: 'Org_pid', hidden: true }
			],
			title: null,
			toolbar: false,
			contextmenu: false,
			onDblClick: function(grid, number, object) {
				wnd.doSelect();
			}
		});

		Ext.apply(this, {
			layout: 'border',
			buttons:
			[{
				handler: function() 
				{
					this.ownerCt.doSelect();
				},
				iconCls: 'ok16',
				text: lang['vyibrat']
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
				wnd.FilterPanel,
				wnd.SearchGrid
			]
		});
		sw.Promed.swWhsDocumentSupplySelectWindow.superclass.initComponent.apply(this, arguments);
	}
});