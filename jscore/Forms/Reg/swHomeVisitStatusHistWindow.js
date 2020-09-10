/**
* swHomeVisitStatusHistWindow - история статусов вызова на дом
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Reg
* @access       public
* @copyright    Copyright (c) 2015 Swan Ltd.
* @author       Alexandr Chebukin
* @version      11.12.2015
* @comment      
*/
/*NO PARSE JSON*/
sw.Promed.swHomeVisitStatusHistWindow = Ext.extend(sw.Promed.BaseForm,
{
	maximizable: true,
	maximized: false,
	height: 500,
	width: 800,
	id: 'HomeVisitStatusHistWindow',
	title: 'История статусов', 
	layout: 'border',
	resizable: true,
	initComponent: function() 
	{
		var form = this;
		
		this.HomeVisitStatusHistGrid = new sw.Promed.ViewFrame(
		{
			id: form.id+'CheckStatusHistoryGrid',
			title:'',
			object: 'HomeVisit',
			dataUrl: '/?c=HomeVisit&m=loadHomeVisitStatusHist',
			autoLoadData: false,
			region: 'center',
			toolbar: true,
			totalProperty: 'totalCount',
			stringfields:
			[
				{name: 'HomeVisitStatusHist_id', type: 'int', key: true, hidden: true},
				{name: 'HomeVisitStatus_Name', type: 'string', header: 'Статус', width: 120, id: 'autoexpand'},
				{name: 'HomeVisitStatusHist_setDT', type: 'datetime', header: 'Дата', width: 120},
				{name: 'pmUser_Name', type: 'string', header: 'ФИО', width: 120}
			],
			actions:
			[
				{name:'action_add', disabled: true, hidden: true},
				{name:'action_edit', disabled: true, hidden: true},
				{name:'action_print', disabled: false, hidden: false},
				{name:'action_view', disabled: true, hidden: true},
				{name:'action_delete', disabled: true, hidden: true}
			]
		});
		
		this.formPanel = new Ext.Panel(
		{
			region: 'center',
			labelAlign: 'right',
			layout: 'border',
			labelWidth: 50,
			border: false,
			items:
			[
				this.HomeVisitStatusHistGrid
			]
		});
		
		Ext.apply(this, 
		{
			items: 
			[ 
				form.formPanel
			],
			buttons:
			[{
				text: '-'
			},
			HelpButton(this, TABINDEX_RCSH + 1),
			{
				iconCls: 'close16',
				tabIndex: TABINDEX_RCSH + 2,
				onTabAction: function()
				{
					this.buttons[1].focus();
				},
				handler: function() {
					form.hide();
				},
				text: BTN_FRMCLOSE
			}]
		});
		sw.Promed.swHomeVisitStatusHistWindow.superclass.initComponent.apply(this, arguments);
	},
	show: function() {
		sw.Promed.swHomeVisitStatusHistWindow.superclass.show.apply(this, arguments);
		
		if ( !arguments[0] || !arguments[0].HomeVisit_id ) {
			sw.swMsg.alert('Сообщение', 'Неверные параметры', function() { this.hide(); }.createDelegate(this) );
			return false;
		}
		
		this.HomeVisit_id = arguments[0].HomeVisit_id;
		
		var filters = {};
		filters.HomeVisit_id = this.HomeVisit_id || null;
		this.HomeVisitStatusHistGrid.loadData({ globalFilters: filters });
	}
});