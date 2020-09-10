/**
* swDrugNonpropNamesViewWindow - Непатентованные наименования окно просмотра списка
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Farmacy
* @access       public
* @copyright    Copyright (c) 2016 Swan Ltd.
* @author       Kurakin A.
* @version      09.2016
* @comment      
*/
sw.Promed.swDrugNonpropNamesViewWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: false,
	title: 'Справочник непатентованные наименования',//lang['spravochnik_nepatentovannye_naimenovaniya'],
	layout: 'border',
	id: 'DrugNonpropNamesViewWindow',
	modal: true,
	shim: false,
	width: 400,
	resizable: false,
	maximizable: true,
	maximized: true,
	onSelect: Ext.emptyFn,
	doSearch: function() {
		var wnd = this;
		var form = wnd.FilterPanel.getForm();
		var params = new Object();

		wnd.SearchGrid.removeAll();
		params = form.getValues();
		params.start = 0;
		params.limit = 100;
		wnd.SearchGrid.loadData({params: params, globalFilters: params});
	},
	doReset: function() {
		var form = this.FilterPanel.getForm();
		form.reset();
		this.SearchGrid.removeAll();
	},	
	show: function() {
		sw.Promed.swDrugNonpropNamesViewWindow.superclass.show.apply(this, arguments);
		if(arguments[0].mode && arguments[0].mode == 'search'){
			if(typeof arguments[0].onSelect == 'function'){
				this.onSelect = arguments[0].onSelect;
			}
			this.buttons[0].show();
		} else {
			this.buttons[0].hide();
		}
		if(!( haveArmType('superadmin') || haveArmType('adminllo') || haveArmType('minzdravdlo') || haveArmType('headnurse') )){
			this.SearchGrid.denyActions();
		}
		this.doReset();
	},
	initComponent: function() {
		var wnd = this;

		this.FilterCommonPanel = new sw.Promed.Panel({
			layout: 'form',
			autoScroll: true,
			bodyBorder: false,
			labelAlign: 'right',
			labelWidth: 100,
			border: false,
			frame: true,
			items: [{
				layout: 'column',
				border: false,
				defaults: { border: false },
				autoHeight: true,
				labelWidth: 100,
				items:[{
					layout: 'form',
					defaults: {
						width: 185
					},
					items:[{
						xtype: 'textfield',
						fieldLabel: lang['kod'],
						name: 'DrugNonpropNames_Code',
						width:185
					}, {
						xtype: 'textfield',
						fieldLabel: lang['naimenovanie'],
						name: 'DrugNonpropNames_Nick',
						width:185
					}]
				}, {
					layout: 'form',
					defaults: {
						width: 185
					},
					items:[{
						xtype: 'textfield',
						fieldLabel: lang['svoystvo'],
						name: 'DrugNonpropNames_Property',
						width:185
					}, {
						layout: 'column',
						width: 300,
						style:'padding-left:95px',
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
								text: 'Сброс',
								iconCls: 'reset16',
								minWidth: 100,
								handler: function() {
									wnd.doReset();
								}
							}]
						}]
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
				this.FilterCommonPanel
			]
		});

		this.SearchGrid = new sw.Promed.ViewFrame({
			actions: [
				{name: 'action_add'},
				{name: 'action_edit'},
				{name: 'action_view'},
				{name: 'action_delete', url:'/?c=DrugNonpropNames&m=deleteDrugNonpropNames',
					handler: function(){
						if ( wnd.SearchGrid.ViewGridPanel.getSelectionModel().getSelected() )
						{
							var row = wnd.SearchGrid.ViewGridPanel.getSelectionModel().getSelected();
							
							Ext.Ajax.request({
								url: '/?c=DrugNonpropNames&m=checkDrugNonpropNames',
								params: {
									DrugNonpropNames_id: row.data.DrugNonpropNames_id
								},
								callback: function(options, success, response) {
									var result = Ext.util.JSON.decode(response.responseText);
									if(result && result[0] && result[0].tbl_desc){
										var text = 'Удаление записи невозможно, т.к. данные используются в '+result[0].tbl_desc;
										sw.swMsg.alert(lang['soobschenie'], text);
										return false;
									} else {
										wnd.SearchGrid.deleteRecord();
									}
								}
							});
						}
					}
				},
				{name: 'action_print'}
			],
			region: 'center',
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 125,
			autoLoadData: false,
			border: true,
			autoScroll: true,
			dataUrl: '/?c=DrugNonpropNames&m=loadDrugNonpropNamesList',
			object: 'DrugNonpropNames',
			editformclassname: 'swDrugNonpropNamesEditWindow',
			id: 'DrugNonpropNamesGrid',
			paging: true,
			pageSize: 100,
			totalProperty: 'totalCount',
			root: 'data',
			style: 'margin-bottom: 10px',
			stringfields: [
				{ name: 'DrugNonpropNames_id', type: 'int', header: 'ID', key: true },
				{ name: 'DrugNonpropNames_Code', type: 'int', header: lang['kod'], width: 80 },
				{ name: 'DrugNonpropNames_Name', type: 'string', header: lang['naimenovanie'], id: 'autoexpand' },
				{ name: 'DrugNonpropNames_Nick', type: 'string', header: lang['kratkoe_naimenovanie'], width: 160 },
				{ name: 'DrugNonpropNames_Property', type: 'string', header: lang['svoystvo'], width: 160 }
			],
			title: lang['nepatentovannye_naimenovaniya'],
			toolbar: true
		});

		this.CenterPanel = new Ext.form.FormPanel({
			region: 'center',
			border: false,
			layout: 'border',
			items: [this.SearchGrid]
		});

		Ext.apply(this, {
			layout: 'border',
			buttons:
			[{
				handler: function() 
				{
					if ( wnd.SearchGrid.ViewGridPanel.getSelectionModel().getSelected() )
					{
						var row = wnd.SearchGrid.ViewGridPanel.getSelectionModel().getSelected();
						if(row.data.DrugNonpropNames_id > 0){
							wnd.onSelect(row.data.DrugNonpropNames_id);
							wnd.hide();
						}
					} else {
						sw.swMsg.alert(lang['vnimanie'],lang['ne_vyibrana_zapis']);
						return true;
					}
				},
				iconCls: 'ok16',
				text: lang['vyibrat']
			}, {
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
				wnd.FilterPanel,this.CenterPanel
			]
		});
		sw.Promed.swDrugNonpropNamesViewWindow.superclass.initComponent.apply(this, arguments);
	}	
});