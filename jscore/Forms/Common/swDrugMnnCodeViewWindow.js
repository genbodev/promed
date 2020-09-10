/**
* swDrugMnnCodeViewWindow - окно просмотра справочника МНН
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2014 Swan Ltd.
* @author       Salakhov R.
* @version      09.2014
* @comment      
*/
sw.Promed.swDrugMnnCodeViewWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: false,
	title: lang['spravochnik_mnn'],
	layout: 'border',
	id: 'DrugMnnCodeViewWindow',
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
		params.DrugRequest_id = wnd.DrugRequest_id;
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
		sw.Promed.swDrugMnnCodeViewWindow.superclass.show.apply(this, arguments);

		this.action = 'edit';
		if (arguments[0] && arguments[0].action) {
			this.action = arguments[0].action;
		}

		this.SearchGrid.setReadOnly(this.action != 'edit');

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
				name: 'DrugMnnCode_Code',
				anchor: null,
				width: 250
			}, {
				xtype: 'textfield',
				fieldLabel: lang['naimenovanie'],
				name: 'query',
				anchor: null,
				width: 500
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
				{name: 'action_view', hidden: true},
				{name: 'action_delete', url: '/?c=DrugNomen&m=deleteDrugMnnCode'},
				{name: 'action_print', hidden: true}
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			autoLoadData: false,
			border: true,
			dataUrl: '/?c=DrugNomen&m=loadDrugMnnCodeList',
			height: 180,
			region: 'center',
			object: 'DrugMnnCode',
			editformclassname: 'swDrugMnnCodeEditWindow',
			id: 'DrugMnnCodeGrid',
			paging: true,
			pageSize: 100,
			root: 'data',
			totalProperty: 'totalCount',
			style: 'margin-bottom: 10px',
			stringfields: [
				{name: 'DrugMnnCode_id', type: 'int', header: 'ID', key: true},
				{name: 'DrugMnnCode_Code', type: 'string', header: lang['kod'], width: 120},
				{name: 'ACTMATTERS_RUSNAME', type: 'string', header: lang['naimenovanie'], width: 120, id: 'autoexpand'}
			],
			title: lang['spisok_regionalnyih_kodov_mnn'],
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
		sw.Promed.swDrugMnnCodeViewWindow.superclass.initComponent.apply(this, arguments);
	}	
});