/**
* swDrugRequestReceptViewWindow - окно просмотра списка сводных заявок по медикаментам
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
sw.Promed.swDrugRequestReceptViewWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: false,
	title: lang['spisok_svodnyih_zayavok'],
	layout: 'border',
	id: 'DrugRequestReceptViewWindow',
	modal: true,
	shim: false,
	width: 400,
	resizable: false,
	maximizable: true,
	maximized: true,
	doSearch: function() {
		var params = new Object();

		this.SearchGrid.removeAll();
		params.start = 0;
		params.limit = 100;
		params.ReceptFinance_id = this.ReceptFinance_id;
		params.DrugRequestPeriod_id = this.DrugRequestPeriod_id;

		this.SearchGrid.loadData({params: params, globalFilters: params});
	},
	doReset: function() {
		var wnd = this;
		var form = wnd.FilterPanel.getForm();
		form.reset();
		wnd.SearchGrid.removeAll();
	},	
	show: function() {
        var wnd = this;
		sw.Promed.swDrugRequestReceptViewWindow.superclass.show.apply(this, arguments);

		this.ReceptFinance_id = null;
		this.DrugRequestPeriod_id = null;

		if (arguments[0] && arguments[0].ReceptFinance_id) {
			this.ReceptFinance_id = arguments[0].ReceptFinance_id;
		}
		if (arguments[0] && arguments[0].DrugRequestPeriod_id) {
			this.DrugRequestPeriod_id = arguments[0].DrugRequestPeriod_id;
		}

		this.doSearch();
	},
	initComponent: function() {
		var wnd = this;

		this.SearchGrid = new sw.Promed.ViewFrame({
			actions: [
				{name: 'action_add', hidden: true},
				{name: 'action_edit'},
				{name: 'action_view', hidden: true},
				{name: 'action_delete', hidden: true},
				{name: 'action_print'}
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 125,
			autoLoadData: false,
			border: true,
			dataUrl: '/?c=DrugRequestRecept&m=loadList',
			height: 180,
			object: 'DrugRequestRecept',
			editformclassname: 'swDrugRequestReceptEditWindow',
			id: 'DrugRequestReceptGrid',
			paging: false,
			style: 'margin-bottom: 10px',
			stringfields: [
				{ name: 'DrugRequestRecept_id', type: 'int', header: 'ID', key: true },
				{ name: 'DrugProtoMnn_Code', type: 'string', header: lang['kod'], width: 120 },
				{ name: 'DrugProtoMnn_Name', type: 'string', header: lang['medikament'], width: 120, id: 'autoexpand' },
				{ name: 'DrugRequestRecept_Kolvo', type: 'float', header: lang['zayavleno'], width: 120 },
				{ name: 'DrugRequestRecept_KolvoRAS', type: 'float', header: lang['vhodyaschiy_ostatok'], width: 120 },
				{ name: 'DrugRequestRecept_KolvoPurch', type: 'float', header: lang['zakup'], width: 120 },
				{ name: 'DrugRequestRecept_KolvoDopPurch', type: 'float', header: lang['dop_zakup'], width: 120 },
				{ name: 'KolvoSum', header: lang['k_vyipiske'], width: 120, renderer: function(v,p,r) {
					var sum = (r.get('DrugRequestRecept_KolvoRAS')*1)+(r.get('DrugRequestRecept_KolvoPurch')*1)+(r.get('DrugRequestRecept_KolvoDopPurch')*1);
					return sum;
				}},
				{ name: 'field3', header: lang['koeffitsient'], width: 120, renderer: function(v,p,r) {
					var kolvo = r.get('DrugRequestRecept_Kolvo')*1;
					var sum = (r.get('DrugRequestRecept_KolvoRAS')*1)+(r.get('DrugRequestRecept_KolvoPurch')*1)+(r.get('DrugRequestRecept_KolvoDopPurch')*1);

					return kolvo > 0 ? (sum/kolvo).toFixed(2) : '';
				}}
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
		sw.Promed.swDrugRequestReceptViewWindow.superclass.initComponent.apply(this, arguments);
	}	
});