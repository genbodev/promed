/**
* swGoodsStorageViewWindow - форма справочника наименований мест хранения
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2016 Swan Ltd.
 * @author       Belousov N.
 * @comment
 */
sw.Promed.swGoodsStorageViewWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: false,
	title: lang['naimenovaniya_mest_hraneniya'],
	layout: 'border',
	id: 'GoodsStorageViewWindow',
	modal: true,
	shim: false,
	width: 400,
	resizable: false,
	maximizable: true,
	maximized: true,
	doSearch: function() {
		var wnd = this;
		var base_form = this.FilterPanel.getForm();
		var params = base_form.getValues();
		params.StorageUnitType_id = wnd.StorageUnitType_id;
		params.limit = 100;
		params.start = 0;

		wnd.SearchGrid.removeAll();
		wnd.SearchGrid.loadData({
			globalFilters: params
		});
	},
	doReset: function() {
		this.FilterPanel.getForm().reset();
	},
	show: function() {
		sw.Promed.swGoodsStorageViewWindow.superclass.show.apply(this, arguments);
		this.action = 'edit';
		if (arguments[0] && arguments[0].action) {
			this.action = arguments[0].action;
		}

		this.SearchGrid.setReadOnly(this.action != 'edit' ||
			(!isSuperAdmin() && !isLpuAdmin() && !isOrgAdmin() && arguments[0].armType != 'merch'));

		this.doReset();
		this.doSearch();
	},
	initComponent: function() {
		var wnd = this;

		//По классификации
		this.FilterFormPanel = new sw.Promed.Panel({
			region: 'north',
			layout: 'form',
			autoScroll: true,
			bodyBorder: false,
			labelAlign: 'right',
			labelWidth: 170,
			border: false,
			frame: true,
			items: [{
				xtype: 'textfield',
				fieldLabel: lang['kod'],
				name: 'StorageUnitType_Code',
				anchor: null,
				width: 180
			}, {
				xtype: 'textfield',
				fieldLabel: lang['naimenovanie'],
				name: 'StorageUnitType_Name',
				anchor: null,
				width: 180
			}, {
				xtype: 'textfield',
				fieldLabel: lang['kratkoe_naimnovanie'],
				name: 'StorageUnitType_Nick',
				anchor: null,
				width: 180
			}, {
				xtype: 'daterangefield',
				fieldLabel: lang['period'],
				plugins: [new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
				name: 'dateRange',
				anchor: null,
				width: 180
			}]
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
			ownerWindow: this,
			toolBar: this.WindowToolbar,
			items: [
				this.FilterFormPanel,
				this.FilterButtonsPanel
			]
		});

		this.SearchGrid = new sw.Promed.ViewFrame({
			actions: [
				{name: 'action_add'},
				{name: 'action_edit'},
				{name: 'action_view'},
				{name: 'action_delete', url: '/?c=Storage&m=deleteGoodsStorage'},
				{name: 'action_print'}
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			autoLoadData: false,
			border: true,
			dataUrl: '/?c=Storage&m=loadGoodsStorageGrid',
			height: 180,
			region: 'center',
			object: 'GoodsStorage',
			editformclassname: 'swGoodsStorageEditWindow',
			id: 'GoodsStorage',
			paging: true,
			pageSize: 100,
			root: 'data',
			totalProperty: 'totalCount',
			style: 'margin-bottom: 10px',
			stringfields: [
				{name: 'StorageUnitType_id', type: 'int', header: 'ID', key: true},
				{name: 'StorageUnitType_Code', type: 'string', header: lang['kod'], width: 80},
				{name: 'StorageUnitType_Name', type: 'string', header: lang['naimenovanie'], width: 150},
				{name: 'StorageUnitType_Nick', type: 'string', header: lang['kratkoe_naimnovanie'], width: 150},
				{name: 'dateRange', type: 'string', header: lang['period'], width: 150}
			],
			title: lang['spisok_naimenovaniya_mest_hraneniya'],
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
				this.FilterPanel,
				this.SearchGrid
			]
		});
		sw.Promed.swGoodsStorageViewWindow.superclass.initComponent.apply(this, arguments);
	}
});