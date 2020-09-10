/**
* swOrgTypeWindow - типы организаций
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      OrgStruct
* @access       public
* @copyright    Copyright (c) 2012 Swan Ltd.
* @author       Dmitry Vlasenko
* @version      07.12.2012
* @comment      
*/
/*NO PARSE JSON*/
sw.Promed.swOrgTypeWindow = Ext.extend(sw.Promed.BaseForm,
{
	maximizable: true,
	maximized: true,
	height: 500,
	width: 800,
	id: 'OrgTypeWindow',
	title: WND_ORGSTRUCT_ORGTYPE, 
	layout: 'border',
	resizable: true,
	tabGridReload: function() 
	{
		var form = this;
		var record = form.OrgTypeGrid.getGrid().getSelectionModel().getSelected();
		if (record) {
			var params = {
				 limit: 100
				,start: 0
				,OrgType_id: record.get('OrgType_id')
			}
			
			switch (form.tabPanel.getActiveTab().id) 
			{
				case 'tab_orgstructleveltype':
					form.OrgStructLevelTypeGrid.loadData({
						params: params,
						globalFilters: params
					});
				break;
				
				case 'tab_orgservicetype':
					form.OrgServiceTypeGrid.loadData({
						params: params,
						globalFilters: params
					});
				break;
			}
		}
	},
	deleteOrgStructLevelType: function() {
		var grid = this.OrgStructLevelTypeGrid.getGrid();

		if ( !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('OrgStructLevelType_id') ) {
			return false;
		}

		var record = grid.getSelectionModel().getSelected();

		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId, text, obj) {
				if ( buttonId == 'yes' ) {
					Ext.Ajax.request({
						callback: function(options, success, response) {
							if ( success ) {
								var response_obj = Ext.util.JSON.decode(response.responseText);

								if ( response_obj.success == false ) {
									sw.swMsg.alert(lang['oshibka'], response_obj.Error_Msg ? response_obj.Error_Msg : lang['oshibka_pri_udalenii_tipa_strukturnogo_urovnya']);
								}
								else {
									grid.getStore().remove(record);
								}

								if ( grid.getStore().getCount() > 0 ) {
									grid.getView().focusRow(0);
									grid.getSelectionModel().selectFirstRow();
								}
							}
							else {
								sw.swMsg.alert(lang['oshibka'], lang['pri_udalenii_tipa_strukturnogo_urovnya_voznikli_oshibki']);
							}
						},
						params: {
							OrgStructLevelType_id: record.get('OrgStructLevelType_id')
						},
						url: '/?c=OrgStruct&m=deleteOrgStructLevelType'
					});
				}
			},
			icon: Ext.MessageBox.QUESTION,
			msg: lang['udalit_tip_strukturnogo_urovnya'],
			title: lang['vopros']
		});
	},
	deleteOrgServiceType: function() {
		var grid = this.OrgServiceTypeGrid.getGrid();

		if ( !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('OrgServiceType_id') ) {
			return false;
		}

		var record = grid.getSelectionModel().getSelected();

		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId, text, obj) {
				if ( buttonId == 'yes' ) {
					Ext.Ajax.request({
						callback: function(options, success, response) {
							if ( success ) {
								var response_obj = Ext.util.JSON.decode(response.responseText);

								if ( response_obj.success == false ) {
									sw.swMsg.alert(lang['oshibka'], response_obj.Error_Msg ? response_obj.Error_Msg : lang['oshibka_pri_udalenii_tipa_slujbyi']);
								}
								else {
									grid.getStore().remove(record);
								}

								if ( grid.getStore().getCount() > 0 ) {
									grid.getView().focusRow(0);
									grid.getSelectionModel().selectFirstRow();
								}
							}
							else {
								sw.swMsg.alert(lang['oshibka'], lang['pri_udalenii_tipa_slujbyi_voznikli_oshibki']);
							}
						},
						params: {
							OrgServiceType_id: record.get('OrgServiceType_id')
						},
						url: '/?c=OrgStruct&m=deleteOrgServiceType'
					});
				}
			},
			icon: Ext.MessageBox.QUESTION,
			msg: lang['udalit_tip_slujbyi'],
			title: lang['vopros']
		});
	},
	deleteOrgType: function() {
		var grid = this.OrgTypeGrid.getGrid();

		if ( !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('OrgType_id') ) {
			return false;
		}

		var record = grid.getSelectionModel().getSelected();

		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId, text, obj) {
				if ( buttonId == 'yes' ) {
					Ext.Ajax.request({
						callback: function(options, success, response) {
							if ( success ) {
								var response_obj = Ext.util.JSON.decode(response.responseText);

								if ( response_obj.success == false ) {
									sw.swMsg.alert(lang['oshibka'], response_obj.Error_Msg ? response_obj.Error_Msg : lang['oshibka_pri_udalenii_tipa_slujbyi']);
								}
								else {
									grid.getStore().remove(record);
								}

								if ( grid.getStore().getCount() > 0 ) {
									grid.getView().focusRow(0);
									grid.getSelectionModel().selectFirstRow();
								}
							}
							else {
								sw.swMsg.alert(lang['oshibka'], lang['pri_udalenii_tipa_slujbyi_voznikli_oshibki']);
							}
						},
						params: {
							OrgType_id: record.get('OrgType_id')
						},
						url: '/?c=OrgStruct&m=deleteOrgType'
					});
				}
			},
			icon: Ext.MessageBox.QUESTION,
			msg: lang['udalit_tip_organizatsii'],
			title: lang['vopros']
		});
	},
	initComponent: function() 
	{
		var form = this;
		
		this.OrgStructLevelTypeGrid = new sw.Promed.ViewFrame(
		{
			id: form.id+'OrgStructLevelTypeGrid',
			title: '',
			object: 'OrgStructLevelType',
			dataUrl: '/?c=OrgStruct&m=loadOrgStructLevelTypeGrid',
			editformclassname: 'swOrgStructLevelTypeEditWindow',
			autoLoadData: false,
			paging: true,
			root: 'data',
			region: 'north',
			height: 300,
			toolbar: true,
			totalProperty: 'totalCount',
			stringfields:
			[
				{name: 'OrgStructLevelType_id', type: 'int', header: 'OrgStructLevelType_id', key: true, hidden: true},
				{name: 'OrgStructLevelType_Code', header: lang['kod'], width: 120},
				{name: 'OrgStructLevelType_Name', header: lang['naimenovanie'], width: 200, id: 'autoexpand'},
				{name: 'OrgStructLevelType_Nick', header: lang['kratkoe_naimenovanie'], width: 150},
				{name: 'OrgStructLevelType_begDT', type:'date', header: lang['data_otkryitiya'], width: 120},
				{name: 'OrgStructLevelType_endDT', type:'date', header: lang['data_zakryitiya'], width: 120},
				{name: 'OrgStructLevelType_LevelNumber', header: lang['nomer_urovnya'], width: 150}
			],
			actions:
			[
				{name:'action_add'},
				{name:'action_edit'},
				{name:'action_print'},
				{name:'action_view'},
				{name:'action_delete', handler: function() {
					form.deleteOrgStructLevelType();
				}}
			]
		});
		
		this.OrgServiceTypeGrid = new sw.Promed.ViewFrame(
		{
			id: form.id+'OrgServiceTypeGrid',
			title: '',
			object: 'OrgServiceType',
			dataUrl: '/?c=OrgStruct&m=loadOrgServiceTypeGrid',
			editformclassname: 'swOrgServiceTypeEditWindow',
			autoLoadData: false,
			paging: true,
			root: 'data',
			region: 'center',
			toolbar: true,
			totalProperty: 'totalCount',
			stringfields:
			[
				{name: 'OrgServiceType_id', type: 'int', header: 'OrgServiceType_id', key: true, hidden: true},
				{name: 'OrgServiceType_Code', header: lang['kod'], width: 120},
				{name: 'OrgServiceType_Name', header: lang['naimenovanie'], width: 200, id: 'autoexpand'},
				{name: 'OrgServiceType_Nick', header: lang['kratkoe_naimenovanie'], width: 150},
				{name: 'OrgServiceType_begDT', type:'date', header: lang['data_otkryitiya'], width: 120},
				{name: 'OrgServiceType_endDT', type:'date', header: lang['data_zakryitiya'], width: 120}
			],
			actions:
			[
				{name:'action_add'},
				{name:'action_edit'},
				{name:'action_print'},
				{name:'action_view'},
				{name:'action_delete', handler: function() {
					form.deleteOrgServiceType();
				}}
			]
		});
		
		this.OrgTypeGrid = new sw.Promed.ViewFrame(
		{
			id: form.id+'OrgTypeGrid',
			title: '',
			object: 'OrgType',
			dataUrl: '/?c=OrgStruct&m=loadOrgTypeGrid',
			editformclassname: 'swOrgTypeEditWindow',
			autoLoadData: false,
			paging: false,
			// root: 'data',
			// totalProperty: 'totalCount',
			region: 'north',
			toolbar: true,
			onRowSelect: function(sm,index,record)
			{
				form.tabGridReload();
			},
			stringfields:
			[
				{name: 'OrgType_id', type: 'int', header: 'OrgType_id', key: true, hidden: true},
				{name: 'OrgType_Code', header: lang['kod'], width: 120},
				{name: 'OrgType_Name', header: lang['naimenovanie'], width: 200, id: 'autoexpand'},
				{name: 'OrgType_Nick', header: lang['kratkoe_naimenovanie'], width: 150},
				{name: 'OrgType_begDT', type:'date', header: lang['data_otkryitiya'], width: 120},
				{name: 'OrgType_endDT', type:'date', header: lang['data_zakryitiya'], width: 120}
			],
			actions:
			[
				{name:'action_add', disabled: true},
				{name:'action_edit', disabled: true},
				{name:'action_print'},
				{name:'action_view'},
				{name:'action_delete', disabled: true, handler: function() {
					form.deleteOrgType();
				}}
			]
		});
		
		this.tabPanel = new Ext.TabPanel(
		{
			region: 'center',
			labelAlign: 'right',
			labelWidth: 50,
			border: false,
			activeTab: 0,
			listeners:
			{
				tabchange: function(tab, panel)
				{
					form.tabGridReload();
				}
			},
			layoutOnTabChange: true,
			items:
			[{
				title: lang['tipyi_strukturnyih_urovney'],
				layout: 'fit',
				id: 'tab_orgstructleveltype',
				border:false,
				items: [this.OrgStructLevelTypeGrid]
			}, {
				title: lang['tipyi_slujb'],
				layout: 'fit',
				id: 'tab_orgservicetype',
				border:false,
				items: [this.OrgServiceTypeGrid]
			}]
		});
		
		Ext.apply(this, 
		{
			items: 
			[ 
				form.OrgTypeGrid,
				form.tabPanel
			],
			buttons:
			[{
				text: '-'
			},
			HelpButton(this, TABINDEX_OT + 1),
			{
				iconCls: 'close16',
				tabIndex: TABINDEX_OT + 2,
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
		sw.Promed.swOrgTypeWindow.superclass.initComponent.apply(this, arguments);
	},
	show: function() {
		sw.Promed.swOrgTypeWindow.superclass.show.apply(this, arguments);
		
		this.OrgTypeGrid.loadData();
	}
});