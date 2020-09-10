/**
* swOrgStructureWindow - структура организации
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
sw.Promed.swOrgStructureWindow = Ext.extend(sw.Promed.BaseForm,
{
	maximizable: true,
	maximized: true,
	height: 500,
	width: 800,
	id: 'OrgStructureWindow',
	title: WND_ORGSTRUCT_ORGSTRUCTURE, 
	layout: 'border',
	resizable: true,
	firstLoad: true,
	onTreeSelect: function(sm, node) {
		if ( !node ) {
			return false;
		}

		this.OrgStructGrid.removeAll();
		this.MedServiceGrid.removeAll();
		this.StorageGrid.removeAll();

		var params = {
			 limit: 100
			,start: 0
			,Org_id: this.Org_id
		};
		
		params.OrgStruct_NumLevel = 0;
		params.OrgStruct_pid = null;
		
		switch ( node.attributes.object ) {
			case 'OrgStruct':
				params.OrgStruct_pid = node.attributes.object_value.replace('orgstruct', '');
				params.OrgStruct_NumLevel = node.attributes.OrgStruct_NumLevel;
			break;
		}

		this.OrgStructGrid.loadData({params: params, globalFilters: params, noFocusOnLoad: true});

		params.OrgStruct_id = params.OrgStruct_pid;
		this.PersonWorkGrid.loadData({params: params, globalFilters: params, noFocusOnLoad: true});

		var tab_id = this.OrgStructureTabs.getActiveTab().id;
		if (tab_id == 'tab_medservice') {
			this.MedServiceGrid.loadData({params: params, globalFilters: params, noFocusOnLoad: true});
		}
		if (tab_id == 'tab_storage') {
			params.OrgStruct_id = params.OrgStruct_pid;
			this.StorageGrid.loadData({params: params, globalFilters: params, noFocusOnLoad: true});
		}
	},
	onTabChange: function(node) {
		var params = {
			limit: 100,
			start: 0,
			Org_id: this.Org_id
		};

		params.OrgStruct_NumLevel = 0;
		params.OrgStruct_id = null;
		params.OrgStruct_pid = null;

		switch ( node.attributes.object ) {
			case 'OrgStruct':
				params.OrgStruct_id = node.attributes.object_value.replace('orgstruct', '');
				params.OrgStruct_NumLevel = node.attributes.OrgStruct_NumLevel;
				break;
		}
		
		var tab_id = this.OrgStructureTabs.getActiveTab().id;
		if (tab_id == 'tab_medservice') {
			params.OrgStruct_pid = params.OrgStruct_id;
			
			this.MedServiceGrid.loadData({params: params, globalFilters: params});
		}
		if (tab_id == 'tab_storage') {
			this.StorageGrid.loadData({params: params, globalFilters: params});
		}
	},
	openStorageEditWindow: function(action) {
		if (!action.inlist(['add','edit','view'])) {
			return false;
		}

		var node = this.orgStructTree.getSelectionModel().getSelectedNode();
		var viewframe = this.findById('OSW_StoragePanel');

		var args = {struct: {
			Lpu_id: null,
			LpuBuilding_id: null,
			LpuUnit_id: null,
			LpuSection_id: null,
			MedService_id: null,
			Org_id: null,
			OrgStruct_id: null
		}};
		args.action = action;
		args.mode = 'org';

		args.struct.Org_id = this.Org_id;
		if (node.attributes.object == 'OrgStruct') {
			args.struct.OrgStruct_id = node.attributes.object_value.replace('orgstruct', '');
		}

		if (action != 'add') {
			var record = viewframe.getGrid().getSelectionModel().getSelected();
			if (!record || Ext.isEmpty(record.get('Storage_id'))) {
				return false;
			}

			args.formParams = {
				Storage_id: record.get('Storage_id'),
				Storage4Lpu_id: record.get('Storage4Lpu_id')
			};
		}

		args.owner = viewframe;
		args.callback = function(){
			if (!viewframe.getAction('action_refresh').isDisabled())
			{
				viewframe.getAction('action_refresh').execute();
			}
		}.createDelegate(this);
		getWnd('swStorageEditWindow').show(args);
	},
	deleteStorage: function() {
		var frms = this;
		var viewframe = this.findById('OSW_StoragePanel');
		var record = viewframe.getGrid().getSelectionModel().getSelected();
		if (!record || Ext.isEmpty(record.get('Storage_id'))) {
			return false;
		}

		sw.swMsg.show({
			buttons:Ext.Msg.YESNO,
			fn:function (buttonId, text, obj) {
				if (buttonId == 'yes') {
					var loadMask = new Ext.LoadMask(frms.getEl(), {msg:lang['udalenie']});
					loadMask.show();
					Ext.Ajax.request({
						callback:function (options, success, response) {
							loadMask.hide();
							if (success) {
								var response_obj = Ext.util.JSON.decode(response.responseText);
								if (response_obj.success == false) {
									sw.swMsg.alert(lang['oshibka'], response_obj.Error_Msg ? response_obj.Error_Msg : lang['pri_udalenii_proizoshla_oshibka']);
								}
								else {
									viewframe.getAction('action_refresh').execute();
								}
								/*if (grid.getStore().getCount() > 0) {
								 grid.getView().focusRow(0);
								 grid.getSelectionModel().selectFirstRow();
								 }*/
							}
							/*else {
							 sw.swMsg.alert(lang['oshibka'], lang['pri_udalenii_sklada_voznikli_oshibki']);
							 }*/
						},
						params:{
							Storage_id: record.get('Storage_id')
						},
						url:'/?c=Storage&m=deleteStorage'
					});
				}
			},
			icon:Ext.MessageBox.QUESTION,
			msg:lang['sklad_budet_udalen_so_vseh_strukturnyih_urovney_vyi_hotite_udalit_sklad'],
			title:lang['podtverjdenie']
		});
	},
	openPersonWorkEditWindow: function(action) {
		if (!action.inlist(['add','edit','view'])) {
			return false;
		}

		var viewframe = this.PersonWorkGrid;

		var params = {};

		if (action == 'add') {
			params.Org_id = this.Org_id;
		} else {
			var record = viewframe.getGrid().getSelectionModel().getSelected();
			if (!record || Ext.isEmpty(record.get('PersonWork_id'))) {
				return false;
			}
			params.PersonWork_id = record.get('PersonWork_id');
		}

		params.action = action;
		params.callback = function(){
			if (!viewframe.getAction('action_refresh').isDisabled()) {
				viewframe.getAction('action_refresh').execute();
			}
		}.createDelegate(this);

		getWnd('swPersonWorkEditWindow').show(params);
	},
	deletePersonWork: function() {
		var wnd = this;
		var viewframe = this.PersonWorkGrid;
		var record = viewframe.getGrid().getSelectionModel().getSelected();
		if (!record || Ext.isEmpty(record.get('PersonWork_id'))) {
			return false;
		}

		sw.swMsg.show({
			buttons:Ext.Msg.YESNO,
			fn:function (buttonId, text, obj) {
				if (buttonId == 'yes') {
					var loadMask = new Ext.LoadMask(wnd.getEl(), {msg: lang['udalenie']});
					loadMask.show();
					Ext.Ajax.request({
						callback:function (options, success, response) {
							loadMask.hide();
							if (success) {
								var response_obj = Ext.util.JSON.decode(response.responseText);
								if (response_obj.success) {
									viewframe.getAction('action_refresh').execute();
								}
							}
						},
						params:{
							PersonWork_id: record.get('PersonWork_id')
						},
						url:'/?c=Person&m=deletePersonWork'
					});
				}
			},
			icon:Ext.MessageBox.QUESTION,
			msg:lang['vyi_hotite_udalit_zapis'],
			title:lang['podtverjdenie']
		});
	},
	initComponent: function() 
	{
		var form = this;
		
		form.orgStructTree = new Ext.tree.TreePanel({
			animate: false,
			autoLoad: false,
			autoScroll: true,
			border: true,
			enableDD: false,
			stateful: false,
			getLoadTreeMask: function(MSG) {
				if ( MSG )  {
					delete(this.loadMask);
				}

				if ( !this.loadMask ) {
					this.loadMask = new Ext.LoadMask(Ext.get(this.id), { msg: MSG });
				}

				return this.loadMask;
			},
			loader: new Ext.tree.TreeLoader({
				listeners: {
					'beforeload': function (tl, node) {
						if ( Ext.isEmpty(form.Org_id) ) {
							return false;
						}

						form.orgStructTree.getLoadTreeMask(lang['zagruzka_strukturyi_organizatsii']).show();

						tl.baseParams.level = node.getDepth();
						tl.baseParams.Org_id = form.Org_id;

						if ( node.getDepth() > 0 ) {
							if ( node.attributes.object == 'OrgStruct' ) {
								tl.baseParams.OrgStruct_pid = node.attributes.object_value.replace('orgstruct', '');
							}
						}
					},
					'load': function(node) {
						if (form.firstLoad && form.orgStructTree.getRootNode().firstChild) {
							form.orgStructTree.getSelectionModel().select(form.orgStructTree.getRootNode().firstChild);
							form.orgStructTree.fireEvent('click', form.orgStructTree.getRootNode().firstChild);
							form.firstLoad = false;
						}
						form.orgStructTree.getLoadTreeMask().hide();
					}
				},
				dataUrl:'/?c=OrgStruct&m=loadOrgStructureTree'
			}),
			region: 'west',
			root: {
				nodeType: 'async',
				text: lang['struktura'],
				id: 'root',
				expanded: false
			},
			rootVisible: false,
			selModel: new Ext.tree.KeyHandleTreeSelectionModel(),
			split: true,
			title: '',
			width: 300
		});
		
		form.orgStructTree.getSelectionModel().on('selectionchange', function(sm, node) {
			form.onTreeSelect(sm, node);
		});
		
		this.OrgStructGrid = new sw.Promed.ViewFrame({
			id: form.id+'OrgStructGrid',
			//title: lang['urovni'],
			object: 'OrgStruct',
			dataUrl: '/?c=OrgStruct&m=loadOrgStructGrid',
			editformclassname: 'swOrgStructEditWindow',
			autoLoadData: false,
			paging: true,
			root: 'data',
			//region: 'north',
			height: 220,
			toolbar: true,
			totalProperty: 'totalCount',
			border: false,
			stringfields:
			[
				{name: 'OrgStruct_id', type: 'int', header: 'OrgStruct_id', key: true, hidden: true},
				{name: 'OrgStruct_Code', header: lang['kod'], width: 120},
				{name: 'OrgStruct_Name', header: lang['naimenovanie'], width: 200, id: 'autoexpand'},
				{name: 'OrgStruct_Nick', header: lang['kratkoe_naimenovanie'], width: 150},
				{name: 'OrgStruct_begDT', type:'date', header: lang['data_otkryitiya'], width: 120},
				{name: 'OrgStruct_endDT', type:'date', header: lang['data_zakryitiya'], width: 120},
				{name: 'OrgStructLevelType_Name', header: lang['tip_strukturnogo_urovnya'], width: 200}
			],
			afterDeleteRecord: function() {
				this.onRefresh();
			},
			onRefresh: function() {
				// обновить и текущую ветку дерева
				var tree = form.orgStructTree;
				tree.getLoader().baseParams.OrgStruct_pid = this.getGrid().getStore().baseParams.OrgStruct_pid || null;
				tree.getLoader().load(tree.getSelectionModel().getSelectedNode());
			},
			actions:
			[
				{name:'action_add'},
				{name:'action_edit'},
				{name:'action_print'},
				{name:'action_view'},
				{name:'action_delete'}
			]
		});
		
		this.MedServiceGrid = new sw.Promed.ViewFrame(
		{
			id: form.id+'MedServiceGrid',
			object: 'MedService',
			dataUrl: '/?c=OrgStruct&m=loadMedServiceGrid',
			editformclassname: 'swMedServiceEditWindow',
			autoLoadData: false,
			paging: true,
			root: 'data',
			toolbar: true,
			totalProperty: 'totalCount',
			border: false,
			stringfields:
			[
				{name: 'MedService_id', type: 'int', header: 'MedService_id', key: true, hidden: true},
				{name: 'MedServiceType_Name', header: lang['tip'], width: 120},
				{name: 'MedService_Name', header: lang['naimenovanie'], width: 200, id: 'autoexpand'},
				{name: 'MedService_Nick', header: lang['kratkoe_naimenovanie'], width: 150},
				{name: 'MedService_begDT', type:'date', header: lang['data_otkryitiya'], width: 120},
				{name: 'MedService_endDT', type:'date', header: lang['data_zakryitiya'], width: 120}
			],
			actions:
			[
				{name:'action_add'},
				{name:'action_edit'},
				{name:'action_print'},
				{name:'action_view'},
				{name:'action_delete'}
			]
		});

		this.StorageGrid = new sw.Promed.ViewFrame(
		{
			id: 'OSW_StoragePanel',
			object: 'Storage',
			editformclassname: 'swStorageEditWindow',
			dataUrl: '/?c=Storage&m=loadStorageGrid',
			toolbar: true,
			autoLoadData: false,
			paging: false,
			root: 'data',
			border: false,
			stringfields: [
				{name: 'StorageStructLevel_id', type: 'int', header: 'ID', key: true},
				{name: 'Storage_id', type: 'int', hidden: true},
				{name: 'StorageType_id',  type: 'int', hidden: true},
				{name: 'Lpu_id', type: 'int', hidden: true},
				{name: 'LpuBuilding_id', type: 'int', hidden: true},
				{name: 'LpuUnit_id', type: 'int', hidden: true},
				{name: 'LpuUnitType_id', type: 'int', hidden: true},
				{name: 'LpuSection_id', type: 'int', hidden: true},
				{name: 'Storage_Code',  type: 'string', header: lang['nomer'], width: 60},
				{name: 'Storage_Name',  type: 'string', header: lang['naimenovanie'], width: 140},
				{name: 'StorageStructLevel_Name',  type: 'string', header: lang['uroven'], width: 140},
				{name: 'StorageType_Name',  type: 'string', header: lang['tip'], width: 120},
				{id: 'autoexpand', name: 'Address_Address',  type: 'string', header: lang['adres']},
				{name: 'Storage4Lpu_id',  type: 'int', header: 'Storage4Lpu_id', hidden: true},
				{name: 'Storage4Lpu_Nick',  type: 'string', header: lang['prikreplennaya_mo'], width: 140, hidden: getGlobalOptions().region.nick != 'ufa' },
				{name: 'Storage_begDate',  type: 'date', header: lang['data_otkryitiya'], width: 100},
				{name: 'Storage_endDate',  type: 'date', header: lang['data_zakryitiya'], width: 100}
			],
			actions: [
				{name: 'action_add', handler: function(){this.openStorageEditWindow('add');}.createDelegate(this)},
				{name: 'action_edit', handler: function(){this.openStorageEditWindow('edit');}.createDelegate(this)},
				{name: 'action_view', handler: function(){this.openStorageEditWindow('view');}.createDelegate(this)},
				{name: 'action_delete', handler: function(){this.deleteStorage();}.createDelegate(this)}
			]
		});

		this.OrgStructureTabs = new Ext.TabPanel({
			id: 'lpustructure-tabs-panel',
			autoScroll: true,
			plain: true,
			border: false,
			activeTab: 0,
			resizeTabs: true,
			enableTabScroll: true,
			height: 240,
			minTabWidth: 120,
			tabWidth: 'auto',
			defaults: {bodyStyle:'background:#DFE8F6;width:100%;'},
			layoutOnTabChange: true,
			listeners: {
				tabchange: function(tab, panel)
				{
					// Загрузка соответсвующего грида
					var node = this.orgStructTree.getSelectionModel().getSelectedNode();

					if (node) {
						this.onTabChange(node);
					}
				}.createDelegate(this)
			},
			items: [{
				title: lang['slujbyi'],
				layout: 'fit',
				id: 'tab_medservice',
				iconCls: 'medservice16',
				border:false,
				items: [this.MedServiceGrid]
			},{
				title: lang['skladyi'],
				layout: 'fit',
				id: 'tab_storage',
				iconCls: 'product16',
				border:false,
				items: [this.StorageGrid]
			}]
		});

		this.PersonWorkGrid = new sw.Promed.ViewFrame({
			id: 'OSW_PersonWorkPanel',
			object: 'PersonWork',
			editformclassname: 'swPersonWorkEditWindow',
			dataUrl: '/?c=Person&m=loadPersonWorkGrid',
			toolbar: true,
			autoLoadData: false,
			paging: true,
			root: 'data',
			totalProperty: 'totalCount',
			border: false,
			height: 480,
			stringfields: [
				{name: 'PersonWork_id', type: 'int', header: 'ID', key: true},
				{name: 'Person_id', type: 'int', hidden: true},
				{name: 'Post_id', type: 'int', hidden: true},
				{name: 'Person_Fio',  type: 'string', header: lang['fio'], id: 'autoexpand'},
				{name: 'Person_BirthDay',  type: 'date', header: lang['data_rojdeniya'], width: 120},
				{name: 'Post_Name',  type: 'string', header: lang['doljnost'], width: 280},
				{name: 'PersonWork_begDate',  type: 'date', header: lang['nachalo_rabotyi'], width: 120},
				{name: 'PersonWork_endDate',  type: 'date', header: lang['okonchanie_rabotyi'], width: 120}
			],
			actions: [
				{name: 'action_add', handler: function(){this.openPersonWorkEditWindow('add')}.createDelegate(this)},
				{name: 'action_edit', handler: function(){this.openPersonWorkEditWindow('edit')}.createDelegate(this)},
				{name: 'action_view', handler: function(){this.openPersonWorkEditWindow('view')}.createDelegate(this)},
				{name: 'action_delete', handler: function(){this.deletePersonWork()}.createDelegate(this)}
			]
		});

		var onPanelCollapseToggle = function() {
			this.doLayout();
		}.createDelegate(this);

		this.rightPanel = new Ext.Panel({
			region: 'center',
			labelAlign: 'right',
			layout: 'form',
			labelWidth: 50,
			border: false,
			autoScroll: true,
			items: [{
				collapsible: true,
				title: lang['urovni'],
				border: true,
				autoHeight: true,
				listeners: {
					expand: onPanelCollapseToggle,
					collapse: onPanelCollapseToggle
				},
				items: [this.OrgStructGrid]
			}, {
				collapsible: true,
				title: 'Службы и склады',
				border: true,
				autoHeight: true,
				listeners: {
					expand: onPanelCollapseToggle,
					collapse: onPanelCollapseToggle
				},
				items: [this.OrgStructureTabs]
			}, {
				collapsible: true,
				title: 'Сотрудники',
				border: true,
				autoHeight: true,
				listeners: {
					expand: onPanelCollapseToggle,
					collapse: onPanelCollapseToggle
				},
				items: [this.PersonWorkGrid]
			}]
		});
		
		Ext.apply(this, 
		{
			items: 
			[ 
				form.orgStructTree,
				form.rightPanel
			],
			buttons:
			[{
				text: '-'
			},
			HelpButton(this, TABINDEX_OS + 1),
			{
				iconCls: 'close16',
				tabIndex: TABINDEX_OS + 2,
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
		sw.Promed.swOrgStructureWindow.superclass.initComponent.apply(this, arguments);
	},
	show: function() {
		sw.Promed.swOrgStructureWindow.superclass.show.apply(this, arguments);

		var form = this;
		var Org_id;

		this.rightPanel.body.scrollTo('top');

		if ( typeof arguments == 'object' && arguments[0] && !Ext.isEmpty(arguments[0].Org_id) ) {
			Org_id = arguments[0].Org_id;
		}
		else {
			Org_id = getGlobalOptions().org_id;
		}
		if(!this.MedServiceGrid.getAction('action_schedule'))
		{
			this.MedServiceGrid.addActions({
				iconCls: 'eph-record16',
				name:'action_schedule',
				text:lang['raspisanie'],
				handler: function()
				{
					if ( this.MedServiceGrid.ViewGridPanel.getSelectionModel().getSelected() )
					{
						var record = this.MedServiceGrid.ViewGridPanel.getSelectionModel().getSelected();
						var MedService_id = record.get('MedService_id');
						var MedService_Name = record.get('MedService_Name');
						getWnd('swTTMSOScheduleEditWindow').show({
							MedService_id : MedService_id,
							MedService_Name : MedService_Name,
							readOnly: (this.action=='view')
						});
					}
				}.createDelegate(this)
			});
		}
		if ( form.Org_id != Org_id ) {
			form.firstLoad = true;
			form.Org_id = Org_id;
			form.orgStructTree.getLoader().load(form.orgStructTree.getRootNode());
		}
/*
		if (form.orgStructTree.getRootNode().firstChild) {
			form.orgStructTree.getSelectionModel().select(form.orgStructTree.getRootNode().firstChild);
			form.orgStructTree.fireEvent('click', form.orgStructTree.getRootNode().firstChild);
		}
*/
	}
});