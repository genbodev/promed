/**
* swWhsDocumentUcSelectWindow - окно выбора документа
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Farmacy
* @access       public
* @copyright    Copyright (c) 2016 Swan Ltd.
* @author       Salakhov R.
* @version      08.2016
* @comment      
*/
sw.Promed.swWhsDocumentUcSelectWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: false,
	title: lang['vyibor_dokumenta'],
	layout: 'border',
	id: 'WhsDocumentUcSelectWindow',
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
		if (this.FilterPanelEnabled) {
			Ext.apply(params, form.getValues());
		}

		params.start = 0;
		params.limit = 100;

		if (!Ext.isEmpty(params.WhsDocumentUc_Num)) {
			this.SearchGrid.loadData({
				globalFilters: params
			});
		} else {
			this.doReset();
		}
	},
	doReset: function() {
		this.FilterPanel.getForm().reset();
		this.SearchGrid.removeAll({clearAll: true});
	},
	doSelect:  function() {
		var wnd = this;
		var record = wnd.SearchGrid.getGrid().getSelectionModel().getSelected();

		if (record.get('WhsDocumentUc_id') <= 0) {
			return false;
		}

		wnd.onSelect(record.data);
		wnd.hide();

		return true;
	},	
	show: function() {
		sw.Promed.swWhsDocumentUcSelectWindow.superclass.show.apply(this, arguments);

		this.onSelect = Ext.emptyFn;
		this.FilterPanelEnabled = false;
		this.params = new Object();
		this.searchUrl = '/?c=Farmacy&m=loadWhsDocumentUcList'; //на данный момент такого метода не существует, поэтому при вызове формы нужно передавать url метода загрузки списка

		if (arguments[0].onSelect && typeof arguments[0].onSelect == 'function') {
			this.onSelect = arguments[0].onSelect;
		}
		if (typeof(arguments[0].FilterPanelEnabled) != 'undefined') {
			this.FilterPanelEnabled = arguments[0].FilterPanelEnabled;
		}
		if (arguments[0].searchUrl && arguments[0].searchUrl != '') {
			this.searchUrl = arguments[0].searchUrl;
		}
		if (arguments[0].params) {
			this.params = arguments[0].params;

			if (arguments[0].params.query != undefined) {
				delete arguments[0].params.query;
			}
		}

		if (this.FilterPanelEnabled) {
			this.FilterPanel.show();
		} else {
			this.FilterPanel.hide();
		}
		this.doLayout();

		this.SearchGrid.getGrid().getStore().proxy.conn.url = this.searchUrl;
		this.doReset();
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
			items: [{
                fieldLabel: lang['nomer_dokumenta'],
                name: 'WhsDocumentUc_Num',
                width: 250,
                xtype: 'textfield'
            }]
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
			object: 'WhsDocumentUc',
			id: 'wdusw_SearchGrid',
			paging: true,
			root: 'data',
			totalProperty: 'totalCount',
			style: 'margin-bottom: 10px',
			stringfields: [
				{ name: 'WhsDocumentUc_id', type: 'int', header: 'ID', key: true },
				{ name: 'WhsDocumentUc_Name', type: 'string', header: lang['naimenovanie'], width: 220, hidden: true },
				{ name: 'WhsDocumentUc_Num', type: 'string', header: lang['№_dokumenta'], width: 220, id: 'autoexpand' },
				{ name: 'WhsDocumentUc_Date', type: 'string', header: lang['data'], width: 95 },
				{ name: 'WhsDocumentType_Code', hidden: true },
				{ name: 'DrugFinance_id', hidden: true },
				{ name: 'DrugFinance_Name', type: 'string', header: lang['istochnik_finansirovaniya'], width:175 },
				{ name: 'WhsDocumentCostItemType_id', hidden: true },
				{ name: 'WhsDocumentCostItemType_Name', type: 'string', header: lang['statya_rashodov'], width:125 }
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
		sw.Promed.swWhsDocumentUcSelectWindow.superclass.initComponent.apply(this, arguments);
	}
});