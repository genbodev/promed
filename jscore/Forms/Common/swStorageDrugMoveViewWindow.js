/**
* swStorageDrugMoveViewWindow - окно просмотра Выписки из журнала перемещений
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @access       public
* @copyright    Copyright (c) 2017 Swan Ltd.
* @author       Alexander Kurakin
* @version      02.2017
* @comment      
*/
sw.Promed.swStorageDrugMoveViewWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: false,
	title: 'Выписка из журнала перемещений',
	layout: 'border',
	id: 'StorageDrugMoveViewWindow',
	modal: true,
	shim: false,
	width: 800,
	height: 600,
	resizable: false,
	maximizable: true,
	maximized: false,
	doSearch: function() {
		var wnd = this;
		var params = new Object();

		wnd.SearchGrid.removeAll();

		params.start = 0;
		params.limit = 100;
		params.Drug_id = this.Drug_id;
		params.StorageZone_id = this.StorageZone_id;
		wnd.SearchGrid.getGrid().getStore().baseParams.Drug_id = this.Drug_id;
		wnd.SearchGrid.getGrid().getStore().baseParams.StorageZone_id = this.StorageZone_id;
		wnd.SearchGrid.loadData({params: params, globalFilters: params});
	},
	show: function() {
        var wnd = this;
		sw.Promed.swStorageDrugMoveViewWindow.superclass.show.apply(this, arguments);	
		this.Drug_id = null;
		this.StorageZone_id = null;	
		if(arguments[0].Drug_id){
			this.Drug_id = arguments[0].Drug_id;
		}
		if(arguments[0].StorageZone_id){
			this.StorageZone_id = arguments[0].StorageZone_id;
		}
		wnd.SearchGrid.removeAll();
		this.doSearch();
	},
	initComponent: function() {
		var wnd = this;

		this.SearchGrid = new sw.Promed.ViewFrame({
			actions: [
				{name: 'action_add', hidden: true, disabled: true},
				{name: 'action_edit', hidden: true, disabled: true},
				{name: 'action_view', hidden: true, disabled: true},
				{name: 'action_delete', hidden: true, disabled: true},
				{name: 'action_refresh'},
				{name: 'action_print'}
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 125,
			autoLoadData: false,
			border: true,
			dataUrl: '/?c=StorageZone&m=loadStorageDrugMoveList',
			height: 180,
			object: 'StorageDrugMove',
			id: wnd.id + 'StorageDrugMoveGrid',
			paging: true,
			style: 'margin-bottom: 10px',
			root:'data',
			stringfields: [
				{ name: 'StorageDrugMove_id', type: 'int', header: 'ID', key: true },
				{ name: 'StorageDrugMove_setDate', type: 'date', header: 'Дата', width:70 },
				{ name: 'StorageDrugMove_Count', type: 'float', header: 'Количество', width:80 },
				{ name: 'GoodsUnit_Name', header: 'Ед.учета', width: 100 },
				{ name: 'oStorageZone', type: 'string', header: 'Откуда', width:200 },
				{ name: 'nStorageZone', type: 'string', header: 'Куда', width:200 },
				{ name: 'DrugShipment_Name', header: 'Партия', width: 100 },
				{ name: 'DocumentUc', header: 'Документ', width: 200 }
			],
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
		sw.Promed.swStorageDrugMoveViewWindow.superclass.initComponent.apply(this, arguments);
	}	
});