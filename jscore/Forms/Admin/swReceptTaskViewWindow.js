/**
* swReceptTaskViewWindow - окно просмотра журнала работы заданий
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2018 Swan Ltd.
* @author       Salakhov R.
* @version      12.2018
* @comment      
*/
sw.Promed.swReceptTaskViewWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: false,
	title: 'Журнал работы заданий',
	layout: 'border',
	id: 'ReceptTaskViewWindow',
	modal: true,
	shim: false,
	width: 400,
	resizable: false,
	maximizable: true,
	maximized: true,
	doSearch: function() {
		var wnd = this;
		var params = new Object();

		wnd.SearchGrid.removeAll();

		params.start = 0;
		params.limit = 100;
		params.begDate = this.begDate_Str;
		params.endDate = this.endDate_Str;

		wnd.SearchGrid.loadData({params: params, globalFilters: params});
	},
	doReset: function() {
		var wnd = this;
		wnd.SearchGrid.removeAll();
	},	
	show: function() {
        var wnd = this;
		sw.Promed.swReceptTaskViewWindow.superclass.show.apply(this, arguments);

		this.begDate_Str = null;
		this.endDate_Str = null;

		var title = getRegionNick() == 'vologda' ? 'Журнал запросов ГП Фармация' : 'Журнал работы заданий';
		this.setTitle(title);

		this.doReset();
        this.datePeriodToolbar.currentDay();
        this.datePeriodToolbar.onSelectMode('day', true);
	},
	initComponent: function() {
		var wnd = this;

        this.datePeriodToolbar = new sw.Promed.datePeriodToolbar({
            curDate: getGlobalOptions().date,
            mode: 'week',
            onSelectPeriod: function(begDate, endDate, allowLoad) {
                wnd.begDate_Str = !Ext.isEmpty(begDate) ? begDate.format('d.m.Y') : null;
                wnd.endDate_Str = !Ext.isEmpty(endDate) ? endDate.format('d.m.Y') : null;
                if(allowLoad) {
                    this.doSearch();
				}
            }.createDelegate(this)
        });

        this.datePeriodToolbar.dateMenu.addListener('blur',
            function () {
                this.datePeriodToolbar.onSelectMode('range',false);
            }.createDelegate(this)
        );

		this.SearchGrid = new sw.Promed.ViewFrame({
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 125,
			autoLoadData: false,
			border: true,
			dataUrl: '/?c=ReceptTask&m=loadList',
			height: 180,
			object: 'ReceptTask',
			id: 'rtv_ReceptTaskGrid',
			paging: false,
			style: 'margin-bottom: 10px',
			title: null,
			toolbar: false,
			contextmenu: false,
			stringfields: [
				{ name: 'ReceptTask_id', type: 'int', header: 'ID', key: true },
				{ name: 'ReceptTask_begDT', type: 'string', header: langs('Начало'), width: 200 },
				{ name: 'ReceptTask_endDT', type: 'string', header: langs('Окончание'), width: 200 },
				{ name: 'ReceptTaskType_Code', hidden: true },
				{ name: 'ReceptTaskType_Name', type: 'string', header: langs('Тип задания'), width: 200 }
			],
            onRowSelect: function(sm,rowIdx,record) {
                if (record.get('ReceptTask_id') > 0 && record.get('ReceptTaskType_Code') == 1) { //1 - Экспорт выписанных льготных рецептов
                	var params = new Object();
                	params.ReceptTask_id = record.get('ReceptTask_id');
                    wnd.LogGrid.loadData({params: params, globalFilters: params});
				} else {
                    wnd.LogGrid.removeAll();
				}
			}
		});

		this.LogGrid = new sw.Promed.ViewFrame({
			region: 'south',
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 125,
			autoLoadData: false,
			border: true,
			dataUrl: '/?c=ReceptTask&m=loadReceptTaskLogList',
			height: 180,
			object: 'ReceptTask',
			editformclassname: 'swReceptTaskEditWindow',
			id: 'rtv_LogGrid',
			paging: false,
			style: 'margin-bottom: 10px',
			height: 200,
            title: 'Лог выполнения операций',
            toolbar: false,
            contextmenu: false,
            stringfields: [
				{ name: 'ReceptTaskLog_id', type: 'int', header: 'ID', key: true },
				{ name: 'EvnRecept_Ser', type: 'string', header: langs('Серия'), width: 200 },
				{ name: 'EvnRecept_Num', type: 'string', header: langs('Номер'), width: 200 },
				{ name: 'ReceptTaskOperation_Name', hidden: true },
				{ name: 'ReceptTaskErrorType_Name', type: 'string', header: langs('Ошибка'), id: 'autoexpand' }
			]
		});

		Ext.apply(this, {
			layout: 'border',
			tbar: this.datePeriodToolbar,
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
					items:[
						{
							border: false,
							region: 'center',
							layout: 'fit',
							items: [this.SearchGrid]
						},
						this.LogGrid
					]
				}
			]
		});
		sw.Promed.swReceptTaskViewWindow.superclass.initComponent.apply(this, arguments);
	}	
});