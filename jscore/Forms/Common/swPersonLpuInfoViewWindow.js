/**
* swPersonLpuInfoViewWindow - окно просмотра списка истории согласий и отзывов согласий на обработку перс.данных
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @access       public
* @copyright    Copyright (c) 2014 Swan Ltd.
* @author       Alexander Kurakin
* @version      06.2016
* @comment      
*/
sw.Promed.swPersonLpuInfoViewWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: false,
	title: 'История документа',
	layout: 'border',
	id: 'PersonLpuInfoViewWindow',
	modal: true,
	shim: false,
	width: 600,
	resizable: false,
	maximizable: true,
	maximized: false,
	doSearch: function(data) {
		var wnd = this;
		var params = new Object();

		wnd.SearchGrid.removeAll();
		params.Person_id = data.Person_id;

		wnd.SearchGrid.loadData({params: params, globalFilters: params});
	},
	doReset: function() {
		var wnd = this;
		wnd.SearchGrid.removeAll();
	},	
	show: function() {
        var wnd = this;
		sw.Promed.swPersonLpuInfoViewWindow.superclass.show.apply(this, arguments);		
		if (!arguments[0] || !arguments[0].Person_id) 
		{
			sw.swMsg.show(
			{
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.ERROR,
				msg: 'Ошибка открытия формы "'+wnd.title+'".<br/>Не указаны нужные входные параметры.',
				title: lang['oshibka']
			});
		}
		this.doReset();
		this.doSearch({Person_id:arguments[0].Person_id});
	},
	initComponent: function() {
		var wnd = this;

		this.SearchGrid = new sw.Promed.ViewFrame({
			actions: [
				{name: 'action_add', hidden: true, disabled: true},
				{name: 'action_edit', hidden: true, disabled: true},
				{name: 'action_view', hidden: true, disabled: true},
				{name: 'action_delete', hidden: true, disabled: true},
				{name: 'action_print', hidden: true, disabled: true},
				{name: 'action_refresh', hidden: true, disabled: true}
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 125,
			autoLoadData: false,
			border: true,
			dataUrl: '/?c=Person&m=loadPersonLpuInfoList',
			height: 180,
			object: 'PersonLpuInfo',
			id: 'PersonLpuInfoGrid',
			paging: false,
			style: 'margin-bottom: 10px',
			stringfields: [
				{ name: 'PersonLpuInfo_id', type: 'int', header: 'ID', key: true },
				{ name: 'Doc_type', type: 'string', header: 'Документ', width:100 },
				{ name: 'PersonLpuInfo_setDT', type: 'string', header: 'Дата', width:100 },
				{ name: 'PMUser_Name', type: 'string', header: 'Пользователь', id: 'autoexpand' }
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
			{
				handler: function() 
				{
					this.ownerCt.hide();
				},
				iconCls: 'cancel16',
				text: lang['zakryit']
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
		sw.Promed.swPersonLpuInfoViewWindow.superclass.initComponent.apply(this, arguments);
	}	
});