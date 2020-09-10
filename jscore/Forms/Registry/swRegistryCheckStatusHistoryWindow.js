/**
* swRegistryCheckStatusHistoryWindow - история статусов реестра
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Registry
* @access       public
* @copyright    Copyright (c) 2012 Swan Ltd.
* @author       Dmitry Vlasenko
* @version      06.12.2012
* @comment      
*/
/*NO PARSE JSON*/
sw.Promed.swRegistryCheckStatusHistoryWindow = Ext.extend(sw.Promed.BaseForm,
{
	maximizable: true,
	maximized: false,
	height: 500,
	width: 800,
	id: 'RegistryCheckStatusHistoryWindow',
	title: WND_REGISTRY_CHECKSTATUSHISTORY, 
	layout: 'border',
	resizable: true,
	initComponent: function() 
	{
		var form = this;
		
		this.CheckStatusHistoryGrid = new sw.Promed.ViewFrame(
		{
			id: form.id+'CheckStatusHistoryGrid',
			title:'',
			object: 'Registry',
			dataUrl: '/?c=Registry&m=loadRegistryCheckStatusHistory',
			autoLoadData: false,
			paging: true,
			root: 'data',
			region: 'center',
			toolbar: true,
			totalProperty: 'totalCount',
			stringfields:
			[
				{name: 'RegistryCheckStatusHistory_id', type: 'int', header: 'RegistryCheckStatusHistory_id', key: true, hidden: true},
				{name: 'RegistryCheckStatus_Name', header: lang['status'], width: 120, id: 'autoexpand'},
				{name: 'Registry_CheckStatusDate', type:'datetime', header: lang['data_v_promed'], width: 120},
				{name: 'Registry_CheckStatusTFOMSDate', type:'datetime', header: lang['data_v_tfoms'], width: 120}
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
				this.CheckStatusHistoryGrid
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
		sw.Promed.swRegistryCheckStatusHistoryWindow.superclass.initComponent.apply(this, arguments);
	},
	show: function() {
		sw.Promed.swRegistryCheckStatusHistoryWindow.superclass.show.apply(this, arguments);
		
		if ( !arguments[0] || !arguments[0].Registry_id ) {
			sw.swMsg.alert(lang['soobschenie'], lang['nevernyie_parametryi'], function() { this.hide(); }.createDelegate(this) );
			return false;
		}
		
		this.Registry_id = arguments[0].Registry_id;
		
		var filters = {};
		filters.start = 0;
		filters.limit = 100;
		filters.Registry_id = this.Registry_id || null;
		this.CheckStatusHistoryGrid.loadData({ globalFilters: filters });
	}
});