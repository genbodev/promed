/*NO PARSE JSON*/

(function(){
	var objects = {};

	objects.onLoadPanel = function() {
		objects.createAccessRightsTestLimitToolbar();

		objects.resize(objects.MainPanel.findById('AccessRightsTestPanel'));
		objects.resize(objects.MainPanel.findById('AccessRightsTestLimitPanel'));

		objects.AccessRightsTestGrid.getGrid().getStore().removeAll();
		objects.AccessRightsTestLimitGrid.getGrid().getStore().removeAll();

		objects.MainPanel.findById('AccessRightsTestPanel').isLoaded = true;
		objects.AccessRightsTestGrid.loadData();
	};

	objects.createAccessRightsTestLimitToolbar = function() {
		var toolbar = objects.AccessRightsTestLimitGrid.getGrid().getTopToolbar();
		toolbar.items.each(function(item){item.destroy()});
		toolbar.add({
			id: 'addAccessRightsLimitButton',
			iconCls: 'add16',
			text: lang['dobavit'],
			menu: {
				xtype: 'menu',
				items: [{
					text: lang['doljnost_vracha'],
					handler: function() {objects.openAccessRightsLimitEditWindow('post')}
				}, {
					text: lang['mo'],
					handler: function() {objects.openAccessRightsLimitEditWindow('lpu')}
				}, {
					text: lang['gruppa_polzovateley'],
					handler: function() {objects.openAccessRightsLimitEditWindow('usergroups')}
				}, {
					text: lang['polzovatel'],
					handler: function() {objects.openAccessRightsLimitEditWindow('user')}
				}]
			}
		});
		toolbar.add({
			id: 'deleteAccessRightsLimitButton',
			iconCls: 'delete16',
			text: lang['udalit'],
			handler: function(){objects.deleteAccessRightsLimit()}
		});
		toolbar.add('->');
		toolbar.add(lang['filtr']);
		toolbar.add(' ');
		toolbar.add({
			xtype: 'swbaselocalcombo',
			id: 'LimitTypeCombo',
			store: new Ext.data.SimpleStore(
				{
					key: 'id',
					autoLoad: false,
					fields:
						[
							{name: 'AccessRightsLimitType_id', type: 'int'},
							{name: 'AccessRightsLimitType_Name', type: 'string'},
							{name: 'AccessRightsLimitType_SysNick', type: 'string'}
						],
					data: [
						[1, lang['doljnost_vracha'], 'post'],
						[2, lang['mo'], 'lpu'],
						[3, lang['gruppa_polzovateley'], 'usergroups'],
						[4, lang['polzovatel'], 'user']
					]
				}),
			tpl: new Ext.XTemplate(
				'<tpl for="."><div class="x-combo-list-item">',
				'{AccessRightsLimitType_Name}&nbsp;',
				'</div></tpl>'
			),
			editable: false,
			displayField:'AccessRightsLimitType_Name',
			valueField: 'AccessRightsLimitType_id',
			fieldLabel: lang['filtr'],
			listeners: {
				'select': function(combo, record, index) {
					var diag_grid = objects.AccessRightsTestGrid.getGrid();
					var diag_record = diag_grid.getSelectionModel().getSelected();

					if (diag_record && !Ext.isEmpty(diag_record.get('AccessRightsName_id'))) {
						var params = {
							AccessRightsName_id: diag_record.get('AccessRightsName_id'),
							AccessRightsLimitType_SysNick: record.get('AccessRightsLimitType_SysNick')
						};
						objects.MainPanel.findById('AccessRightsTestLimitPanel').isLoaded = true;
						objects.AccessRightsTestLimitGrid.loadData({params: params, globalFilters: params});
					}
				}
			}
		});
	};

	objects.openAccessRightsTestEditWindow = function(action){
		if ( !action.inlist(['add','edit','view']) ) {
			return false;
		}

		var grid = objects.AccessRightsTestGrid.getGrid();
		var record = grid.getSelectionModel().getSelected();
		var key = 'AccessRightsName_id';

		var params = new Object();
		params.action = action;
		params.formParams = new Object();

		if ( action != 'add' ) {
			params.formParams[key] = record.get(key);
		}

		params.callback = function(data) {
			grid.getStore().load();
		};

		getWnd('swAccessRightsTestEditWindow').show(params);
	};

	objects.deleteAccessRightsTest = function(){
		var grid = objects.AccessRightsTestGrid.getGrid();
		var idField = 'AccessRightsName_id';
		var url = '/?c=AccessRightsTest&m=deleteAccessRights';
		var question = lang['udalit_vyibrannuyu_zapis'];

		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId, text, obj) {
				if ( buttonId == 'yes' ) {

					if ( !grid || !grid.getSelectionModel() || !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get(idField) ) {
						return false;
					}

					var record = grid.getSelectionModel().getSelected();
					var params = new Object();
					params[idField] = record.get(idField);

					Ext.Ajax.request({
						callback: function(options, success, response) {
							if (success) {
								grid.getStore().load();
							} else {
								Ext.Msg.alert(lang['oshibka'], lang['oshibka_zaprosa_k_serveru_poprobuyte_povtorit_operatsiyu']);
							}
						},
						params: params,
						url: url
					});
				}
			},
			icon: Ext.MessageBox.QUESTION,
			msg: question,
			title: lang['vopros']
		});
	};

	objects.openAccessRightsLimitEditWindow = function(type) {
		var grid = objects.AccessRightsTestLimitGrid.getGrid();
		var record = objects.AccessRightsTestGrid.getGrid().getSelectionModel().getSelected();

		if (!record || !record.get('AccessRightsName_id')) {
			return false;
		}

		var params = new Object();
		params.AccessRightsName_id = record.get('AccessRightsName_id');

		params.callback = function(data) {
			grid.getStore().load();
		};

		var wnd = '';
		switch (type) {
			case 'post':
				params.type = type;
				params.title = lang['dostup_k_gruppe_testov']+record.get('AccessRightsName_Name')+lang['dlya_doljnosti'];
				wnd = 'swAccessRightsLimitEditWindow';
				break;
			case 'lpu':
				params.type = type;
				params.title = lang['dostup_k_gruppe_testov']+record.get('AccessRightsName_Name')+lang['dlya_mo'];
				wnd = 'swAccessRightsLimitEditWindow';
				break;
			case 'usergroups':
				params.type = type;
				params.title = lang['dostup_k_gruppe_testov']+record.get('AccessRightsName_Name')+lang['dlya_gruppyi_polzovateley'];
				wnd = 'swAccessRightsLimitEditWindow';
				break;
			case 'user':
				params.title = lang['dostup_k_gruppe_testov']+record.get('AccessRightsName_Name')+lang['dlya_polzovateley'];
				wnd = 'swAccessRightsLimitUsersSelectWindow';
				break;
		}

		getWnd(wnd).show(params);
	};

	objects.deleteAccessRightsLimit = function() {
		var grid = objects.AccessRightsTestLimitGrid.getGrid();
		var idField = 'AccessRightsLimit_id';
		var url = '/?c=AccessRightsTest&m=deleteAccessRightsLimit';
		var question = lang['udalit_vyibrannuyu_zapis'];

		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId, text, obj) {
				if ( buttonId == 'yes' ) {

					if ( !grid || !grid.getSelectionModel() || !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get(idField) ) {
						return false;
					}

					var record = grid.getSelectionModel().getSelected();
					var params = new Object();
					params[idField] = record.get(idField);

					Ext.Ajax.request({
						callback: function(options, success, response) {
							if (success) {
								grid.getStore().load();
							} else {
								Ext.Msg.alert(lang['oshibka'], lang['oshibka_zaprosa_k_serveru_poprobuyte_povtorit_operatsiyu']);
							}
						},
						params: params,
						url: url
					});
				}
			},
			icon: Ext.MessageBox.QUESTION,
			msg: question,
			title: lang['vopros']
		});
	};

	objects.resize = function(panel, maximum) {
		var pSize, gSize;
		pSize = maximum ? 400 : 213;
		gSize = pSize-25;

		panel.setHeight(pSize);
		panel.items.itemAt(0).setHeight(gSize);
	};

	objects.AccessRightsTestGrid = new sw.Promed.ViewFrame({
		dataUrl: '/?c=AccessRightsTest&m=loadAccessRightsGrid',
		id: 'AccessRightsTestGrid',
		border: false,
		layout: 'fit',
		autoLoadData: false,
		showCountInTop: false,
		stripeRows: true,
		root: 'data',
		stringfields: [
			{name: 'AccessRightsName_id', type: 'int', header: 'ID', key: true},
			{name: 'AccessRightsName_Name', type: 'string', header: lang['gruppa'], width: 180},
			{name: 'AccessRightsTest_Codes', type: 'string', header: lang['testyi'], id: 'autoexpand'}
		],
		actions: [
			{name:'action_add', handler: function(){objects.openAccessRightsTestEditWindow('add');}},
			{name:'action_edit', handler: function(){objects.openAccessRightsTestEditWindow('edit');}},
			{name:'action_view', hidden: true},
			{name:'action_delete', handler: function(){objects.deleteAccessRightsTest();}},
			{name:'action_refresh', hidden: true},
			{name:'action_print', hidden: true}
		],
		onRowSelect: function(sm,index,record){
			var title = '';

			if (Ext.isEmpty(record.get('AccessRightsName_id'))) {
				title = lang['dostup_k_gruppe_testov'];
				Ext.getCmp('addAccessRightsLimitButton').disable();
				Ext.getCmp('deleteAccessRightsLimitButton').disable();
			} else {
				title = 'Доступ к группе тестов: <span style="color:black;font-weight:bold;">'+record.get('AccessRightsName_Name')+'</span>';
				Ext.getCmp('addAccessRightsLimitButton').enable();
				Ext.getCmp('deleteAccessRightsLimitButton').enable();
			}

			objects.MainPanel.findById('AccessRightsTestLimitPanel').setTitle(title);

			var params = {AccessRightsName_id: record.get('AccessRightsName_id')};
			objects.MainPanel.findById('AccessRightsTestLimitPanel').isLoaded = true;
			objects.AccessRightsTestLimitGrid.loadData({params: params, globalFilters: params});
		}
	});

	objects.AccessRightsTestLimitGrid = new sw.Promed.ViewFrame({
		dataUrl: '/?c=AccessRightsTest&m=loadAccessRightsLimitGrid',
		id: 'AccessRightsTestLimitGrid',
		border: false,
		layout: 'fit',
		autoLoadData: false,
		showCountInTop: false,
		stripeRows: true,
		root: 'data',
		stringfields: [
			{name: 'AccessRightsLimit_id', type: 'int', header: 'ID', key: true},
			{name: 'AccessRightsLimitType_Name', type: 'string', header: lang['naimenovanie'], width: 180},
			{name: 'AccessRightsLimit_Value', type: 'string', header: lang['znachenie'], id: 'autoexpand'}
		],
		actions: [
			{name:'action_add', hidden: true},
			{name:'action_edit', hidden: true},
			{name:'action_view', hidden: true},
			{name:'action_delete', hidden: true},
			{name:'action_refresh', hidden: true},
			{name:'action_print', hidden: true}
		]
	});

	objects.MainPanel = new Ext.Panel({
		id: 'test1',
		layout: 'form',
		border: false,
		height: 431,
		autoScroll: true,
		bodyStyle:'background:#DFE8F6;',
		onLoadPanel: objects.onLoadPanel,
		items: [
			{
				title: lang['gruppa_testov'],
				id: 'AccessRightsTestPanel',
				region: 'center',
				height: 213,
				animCollapse: false,
				border: false,
				collapsible: true,
				//collapsed: true,
				style: 'margin-bottom: 5px; border-bottom: 1px solid #99bbe8; ',
				isLoaded: false,
				listeners: {
					'expand': function(panel) {
						if ( panel.isLoaded === false ) {
							panel.isLoaded = true;
							objects.AccessRightsTestGrid.getGrid().getStore().load();
						}
						objects.MainPanel.doLayout();
					},
					'collapse': function(panel) {
						objects.MainPanel.doLayout();
					},
					'beforeexpand': function(panel) {
						var diag = panel;
						var limit = objects.MainPanel.findById('AccessRightsTestLimitPanel');
						limit.collapsed ? objects.resize(diag, true) : objects.resize(limit);
					},
					'beforecollapse': function(panel) {
						var diag = panel;
						var limit = objects.MainPanel.findById('AccessRightsTestLimitPanel');
						limit.collapsed ? objects.resize(diag) : objects.resize(limit, true);
					}
				},
				items: [objects.AccessRightsTestGrid]
			},
			{
				title: lang['dostup_k_gruppe_testov'],
				id: 'AccessRightsTestLimitPanel',
				region: 'south',
				height: 213,
				animCollapse: false,
				border: false,
				collapsible: true,
				//collapsed: true,
				style: 'border-top: 1px solid #99bbe8;',
				isLoaded: false,
				listeners: {
					'expand': function(panel) {
						if ( panel.isLoaded === false ) {
							panel.isLoaded = true;
						}
						objects.MainPanel.doLayout();
					},
					'collapse': function(panel) {
						objects.MainPanel.doLayout();
					},
					'beforeexpand': function(panel) {
						var diag = objects.MainPanel.findById('AccessRightsTestPanel');
						var limit = panel;
						diag.collapsed ? objects.resize(limit, true) : objects.resize(diag);
					},
					'beforecollapse': function(panel) {
						var diag = objects.MainPanel.findById('AccessRightsTestPanel');
						var limit = panel;
						diag.collapsed ? objects.resize(limit) : objects.resize(diag, true);
					}
				},
				items: [objects.AccessRightsTestLimitGrid]
			}
		]
	});

	return objects.MainPanel;
}())