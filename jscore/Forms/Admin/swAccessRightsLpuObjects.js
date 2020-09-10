/*NO PARSE JSON*/

(function(){
	var objects = {};

	objects.onLoadPanel = function() {
		objects.createAccessRightsLpuLimitToolbar();

		objects.resize(objects.MainPanel.findById('AccessRightsLpuPanel'));
		objects.resize(objects.MainPanel.findById('AccessRightsLpuLimitPanel'));

		objects.AccessRightsLpuGrid.getGrid().getStore().removeAll();
		objects.AccessRightsLpuLimitGrid.getGrid().getStore().removeAll();

		objects.MainPanel.findById('AccessRightsLpuPanel').isLoaded = true;
		objects.AccessRightsLpuGrid.loadData();
	};

	objects.createAccessRightsLpuLimitToolbar = function() {
		var toolbar = objects.AccessRightsLpuLimitGrid.getGrid().getTopToolbar();
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
					text: langs('МО/Подразделения МО'),
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
					fields: [
						{name: 'AccessRightsLimitType_id', type: 'int'},
						{name: 'AccessRightsLimitType_Name', type: 'string'},
						{name: 'AccessRightsLimitType_SysNick', type: 'string'},
						{name: 'sort', type: 'int'}
					],
					data: [
						[1, langs('Должность врача'), 'post', 1],
						[2, langs('МО'), 'lpu', 2],
						[3, langs('Группа пользователей'), 'usergroups', 4],
						[4, langs('Пользователь'), 'user', 5],
						[5, langs('Подразделение'), 'lpuBuilding', 3],
					],
					sortInfo: {
						field: 'sort'
					}
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
					var lpu_grid = objects.AccessRightsLpuGrid.getGrid();
					var lpu_record = lpu_grid.getSelectionModel().getSelected();

					if (lpu_record && !Ext.isEmpty(lpu_record.get('AccessRightsName_id'))) {
						var params = {
							AccessRightsName_id: lpu_record.get('AccessRightsName_id'),
							AccessRightsLimitType_SysNick: record.get('AccessRightsLimitType_SysNick')
						};
						objects.MainPanel.findById('AccessRightsLpuLimitPanel').isLoaded = true;
						objects.AccessRightsLpuLimitGrid.loadData({params: params, globalFilters: params});
					}
				}
			}
		});
	};

	objects.openAccessRightsLpuEditWindow = function(action){
		if ( !action.inlist(['add','edit','view']) ) {
			return false;
		}

		var grid = objects.AccessRightsLpuGrid.getGrid();
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

		getWnd('swAccessRightsLpuEditWindow').show(params);
	};

	objects.deleteAccessRightsLpu = function(){
		var grid = objects.AccessRightsLpuGrid.getGrid();
		var idField = 'AccessRightsName_id';
		var url = '/?c=AccessRightsLpu&m=deleteAccessRights';
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
		var grid = objects.AccessRightsLpuLimitGrid.getGrid();
		var record = objects.AccessRightsLpuGrid.getGrid().getSelectionModel().getSelected();

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
				params.title = lang['dostup_k_gruppe_mo']+record.get('AccessRightsName_Name')+lang['dlya_doljnosti'];
				wnd = 'swAccessRightsLimitEditWindow';
				break;
			case 'lpu':
				params.type = type;
				params.title = lang['dostup_k_gruppe_mo']+record.get('AccessRightsName_Name')+lang['dlya_mo'];
				wnd = 'swAccessRightsLimitEditWindow';
				break;
			case 'usergroups':
				params.type = type;
				params.title = lang['dostup_k_gruppe_mo']+record.get('AccessRightsName_Name')+lang['dlya_gruppyi_polzovateley'];
				wnd = 'swAccessRightsLimitEditWindow';
				break;
			case 'user':
				params.title = lang['dostup_k_gruppe_mo']+record.get('AccessRightsName_Name')+lang['dlya_polzovateley'];
				wnd = 'swAccessRightsLimitUsersSelectWindow';
				break;
		}

		getWnd(wnd).show(params);
	};

	objects.deleteAccessRightsLimit = function() {
		var grid = objects.AccessRightsLpuLimitGrid.getGrid();
		var idField = 'AccessRightsLimit_id';
		var idLpuBuilding = 'LpuBuildingAccessRightsLink_id';
		var url = '/?c=AccessRightsLpu&m=deleteAccessRightsLimit';
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
					params[idLpuBuilding] = record.get(idLpuBuilding);

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

	objects.expander = new Ext.ux.grid.RowExpander({
		width: 22,
		enableCaching: false,
		lazyRender: false,
		tpl : new Ext.Template(
			'<table><tr><td width="22"></td><td>{AccessRightsLpu_Nicks}</td></tr></table>'
		)
	});

	objects.AccessRightsLpuGrid = new sw.Promed.ViewFrame({
		dataUrl: '/?c=AccessRightsLpu&m=loadAccessRightsGrid',
		id: 'AccessRightsLpuGrid',
		border: false,
		layout: 'fit',
		autoLoadData: false,
		showCountInTop: false,
		stripeRows: true,
		useEmptyRecord: false,
		root: 'data',
		gridplugins: [objects.expander],
		stringfields: [
			objects.expander,
			{name: 'AccessRightsName_id', type: 'int', header: 'ID', key: true},
			{name: 'AccessRightsLpu_Nicks', type: 'string', hidden: true},
			{name: 'AccessRightsName_Name', header: lang['gruppa'], id: 'autoexpand', renderer: function(v){return '<b>'+v+'</b>'}}
		],
		actions: [
			{name:'action_add', handler: function(){objects.openAccessRightsLpuEditWindow('add');}},
			{name:'action_edit', handler: function(){objects.openAccessRightsLpuEditWindow('edit');}},
			{name:'action_view', hidden: true},
			{name:'action_delete', handler: function(){objects.deleteAccessRightsLpu();}},
			{name:'action_refresh', hidden: true},
			{name:'action_print', hidden: true}
		],
		onRowSelect: function(sm,index,record){
			var title = '';

			if (Ext.isEmpty(record.get('AccessRightsName_id'))) {
				title = langs('Доступ к группе МО/Подразделений МО ');
				Ext.getCmp('addAccessRightsLimitButton').disable();
				Ext.getCmp('deleteAccessRightsLimitButton').disable();
			} else {
				title = 'Доступ к группе МО/Подразделений МО: <span style="color:black;font-weight:bold;">'+record.get('AccessRightsName_Name')+'</span>';
				Ext.getCmp('addAccessRightsLimitButton').enable();
				Ext.getCmp('deleteAccessRightsLimitButton').enable();
			}

			objects.MainPanel.findById('AccessRightsLpuLimitPanel').setTitle(title);

			var params = {AccessRightsName_id: record.get('AccessRightsName_id')};
			objects.MainPanel.findById('AccessRightsLpuLimitPanel').isLoaded = true;
			objects.AccessRightsLpuLimitGrid.loadData({params: params, globalFilters: params});
		}
	});

	objects.AccessRightsLpuLimitGrid = new sw.Promed.ViewFrame({
		dataUrl: '/?c=AccessRightsLpu&m=loadAccessRightsLimitGrid',
		id: 'AccessRightsLpuLimitGrid',
		border: false,
		layout: 'fit',
		autoLoadData: false,
		showCountInTop: false,
		stripeRows: true,
		root: 'data',
		stringfields: [
			{name: 'id', type: 'int', header: 'ID', key: true},
			{name: 'AccessRightsLimit_id', type: 'int', header: 'ID', hidden: true},
			{name: 'LpuBuildingAccessRightsLink_id', type: 'int', hidden: true},
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
				title: langs('Группа МО/Подразделений МО'),
				id: 'AccessRightsLpuPanel',
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
							objects.AccessRightsLpuGrid.getGrid().getStore().load();
						}
						objects.MainPanel.doLayout();
					},
					'collapse': function(panel) {
						objects.MainPanel.doLayout();
					},
					'beforeexpand': function(panel) {
						var lpu = panel;
						var limit = objects.MainPanel.findById('AccessRightsLpuLimitPanel');
						limit.collapsed ? objects.resize(Lp, true) : objects.resize(limit);
					},
					'beforecollapse': function(panel) {
						var lpu = panel;
						var limit = objects.MainPanel.findById('AccessRightsLpuLimitPanel');
						limit.collapsed ? objects.resize(lpu) : objects.resize(limit, true);
					}
				},
				items: [objects.AccessRightsLpuGrid]
			},
			{
				title: lang['dostup_k_gruppe_mo'],
				id: 'AccessRightsLpuLimitPanel',
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
						var lpu = objects.MainPanel.findById('AccessRightsLpuPanel');
						var limit = panel;
						lpu.collapsed ? objects.resize(limit, true) : objects.resize(lpu);
					},
					'beforecollapse': function(panel) {
						var lpu = objects.MainPanel.findById('AccessRightsLpuPanel');
						var limit = panel;
						lpu.collapsed ? objects.resize(limit) : objects.resize(lpu, true);
					}
				},
				items: [objects.AccessRightsLpuLimitGrid]
			}
		]
	});

	return objects.MainPanel;
}())