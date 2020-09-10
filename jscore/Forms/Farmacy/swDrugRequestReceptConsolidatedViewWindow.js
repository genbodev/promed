/**
* swDrugRequestReceptConsolidatedViewWindow - окно просмотра списка сводных заявок
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Farmacy
* @access       public
* @copyright    Copyright (c) 2015 Swan Ltd.
* @author       Salakhov R.
* @version      01.2015
* @comment      
*/
sw.Promed.swDrugRequestReceptConsolidatedViewWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: false,
	title: lang['spisok_zayavok'],
	layout: 'border',
	id: 'DrugRequestReceptConsolidatedViewWindow',
	modal: true,
	shim: false,
	width: 400,
	resizable: false,
	maximizable: true,
	maximized: true,
	changeYear: function(value) {
		var field = Ext.getCmp('drrcvYear');
		var val = field.getValue();
		if (!val || value == 0) {
			val = (new Date()).getFullYear();
		}
		field.setValue(val+value);
	},
	doSearch: function(clear, default_values) {
		var field = Ext.getCmp('drrcvYear');
		var wnd = this;

		if (clear) {
			field.setValue(null);
		}
		if (default_values) {
			wnd.changeYear(0);
		}

		var params = new Object();
		params.Year = field.getValue();
		params.limit = 100;
		params.start =  0;

		this.SearchGrid.removeAll();
		this.SearchGrid.loadData({
			globalFilters: params
		});
	},	
	show: function() {
        var wnd = this;
		sw.Promed.swDrugRequestReceptConsolidatedViewWindow.superclass.show.apply(this, arguments);

		wnd.doSearch(true, true);
	},
	initComponent: function() {
		var wnd = this;

		this.WindowToolbar = new Ext.Toolbar({
			items: [{
				xtype: 'button',
				disabled: true,
				text: lang['god']
			}, {
				text: null,
				xtype: 'button',
				iconCls: 'arrow-previous16',
				handler: function() {
					wnd.changeYear(-1);
					wnd.doSearch();
				}.createDelegate(this)
			}, {
				xtype : "tbseparator"
			}, {
				xtype : 'numberfield',
				id: 'drrcvYear',
				allowDecimal: false,
				allowNegtiv: false,
				width: 35,
				enableKeyEvents: true,
				listeners: {
					'keydown': function (inp, e) {
						if (e.getKey() == Ext.EventObject.ENTER) {
							e.stopEvent();
							wnd.doSearch();
						}
					}
				}
			}, {
				xtype : "tbseparator"
			}, {
				text: null,
				xtype: 'button',
				iconCls: 'arrow-next16',
				handler: function() {
					wnd.changeYear(1);
					wnd.doSearch();
				}.createDelegate(this)
			}, {
				xtype: 'tbfill'
			}
			]
		});

		this.SearchGrid = new sw.Promed.ViewFrame({
			actions: [
				{name: 'action_add', text: lang['import'], handler: function() {getWnd('swDrugRequestReceptImportWindow').show({callback: function() {wnd.doSearch();}});}},
				{name: 'action_edit'},
				{name: 'action_view', hidden: true},
				{name: 'action_delete', url: '/?c=DrugRequestRecept&m=deleteDrugRequestReceptConsolidated'},
				{name: 'action_print'}
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 125,
			autoLoadData: false,
			border: true,
			dataUrl: '/?c=DrugRequestRecept&m=loadDrugRequestReceptConsolidatedList',
			height: 180,
			object: 'DrugRequestReceptConsolidated',
			editformclassname: 'swDrugRequestReceptViewWindow',
			id: 'DrugRequestReceptConsolidatedGrid',
			paging: false,
			style: 'margin-bottom: 10px',
			stringfields: [
				{ name: 'DrugRequestReceptConsolidated_id', type: 'string', header: 'ID', key: true },
				{ name: 'ReceptFinance_id', hidden: true, isparams: true },
				{ name: 'ReceptFinance_Name', type: 'string', header: lang['tip'] },
				{ name: 'DrugRequestPeriod_id', hidden: true, isparams: true },
				{ name: 'DrugRequestPeriod_Name', type: 'string', header: lang['period'], id: 'autoexpand' }
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
			tbar: this.WindowToolbar,
			items:[{
				border: false,
				region: 'center',
				layout: 'border',
				items:[{
					border: false,
					region: 'center',
					layout: 'fit',
					items: [this.SearchGrid]
				}]
			}]
		});
		sw.Promed.swDrugRequestReceptConsolidatedViewWindow.superclass.initComponent.apply(this, arguments);
	}	
});