/*NO PARSE JSON*/

(function(){
	var objects = {};

	objects.onLoadPanel = function() {
		objects.createAccessRightsPrivilegeTypeLimitToolbar();

		objects.resize(objects.MainPanel.findById('AccessRightsPrivilegeTypePanel'));
		objects.resize(objects.MainPanel.findById('AccessRightsPrivilegeTypeLimitPanel'));

		objects.AccessRightsPrivilegeTypeGrid.getGrid().getStore().removeAll();
		objects.AccessRightsPrivilegeTypeLimitGrid.getGrid().getStore().removeAll();

		objects.MainPanel.findById('AccessRightsPrivilegeTypePanel').isLoaded = true;
		objects.AccessRightsPrivilegeTypeGrid.loadData();
	};

	objects.createAccessRightsPrivilegeTypeLimitToolbar = function() {
		var toolbar = objects.AccessRightsPrivilegeTypeLimitGrid.getGrid().getTopToolbar();
		toolbar.items.each(function(item){item.destroy()});
		toolbar.add({
			id: 'addAccessRightsLimitButton',
			iconCls: 'add16',
			text: 'Добавить',
			menu: {
				xtype: 'menu',
				items: [{
					text: 'Должность врача',
					handler: function() {objects.openAccessRightsLimitEditWindow('post')}
				}, {
					text: 'МО',
					handler: function() {objects.openAccessRightsLimitEditWindow('lpu')}
				}, {
					text: 'Группа пользователей',
					handler: function() {objects.openAccessRightsLimitEditWindow('usergroups')}
				}, {
					text: 'Пользователь',
					handler: function() {objects.openAccessRightsLimitEditWindow('user')}
				}]
			}
		});
		toolbar.add({
			id: 'deleteAccessRightsLimitButton',
			iconCls: 'delete16',
			text: 'Удалить',
			handler: function(){objects.deleteAccessRightsLimit()}
		});
		toolbar.add('->');
		toolbar.add('Фильтр:');
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
						[1, 'Должность врача', 'post'],
						[2, 'МО', 'lpu'],
						[3, 'Группа пользователей', 'usergroups'],
						[4, 'Пользователь', 'user']
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
			fieldLabel: 'Фильтр',
			listeners: {
				'select': function(combo, record, index) {
					var diag_grid = objects.AccessRightsPrivilegeTypeGrid.getGrid();
					var diag_record = diag_grid.getSelectionModel().getSelected();

					if (diag_record && !Ext.isEmpty(diag_record.get('AccessRightsName_id'))) {
						var params = {
							AccessRightsName_id: diag_record.get('AccessRightsName_id'),
							AccessRightsLimitType_SysNick: record.get('AccessRightsLimitType_SysNick')
						};
						objects.MainPanel.findById('AccessRightsPrivilegeTypeLimitPanel').isLoaded = true;
						objects.AccessRightsPrivilegeTypeLimitGrid.loadData({params: params, globalFilters: params});
					}
				}
			}
		});
	};

	objects.openAccessRightsPrivilegeTypeEditWindow = function(action){
		if ( !action.inlist(['add','edit','view']) ) {
			return false;
		}

		var grid = objects.AccessRightsPrivilegeTypeGrid.getGrid();
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

		getWnd('swAccessRightsPrivilegeTypeEditWindow').show(params);
	};

	objects.deleteAccessRightsPrivilegeType = function(){
		var grid = objects.AccessRightsPrivilegeTypeGrid.getGrid();
		var idField = 'AccessRightsName_id';
		var url = '/?c=AccessRightsPrivilegeType&m=deleteAccessRights';
		var question = 'Удалить выбранную запись?';

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
								Ext.Msg.alert('Ошибка', 'Ошибка запроса к серверу. Попробуйте повторить операцию.');
							}
						},
						params: params,
						url: url
					});
				}
			},
			icon: Ext.MessageBox.QUESTION,
			msg: question,
			title: 'Вопрос'
		});
	};

	objects.openAccessRightsLimitEditWindow = function(type) {
		var grid = objects.AccessRightsPrivilegeTypeLimitGrid.getGrid();
		var record = objects.AccessRightsPrivilegeTypeGrid.getGrid().getSelectionModel().getSelected();

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
				params.title = 'Доступ к льготе '+record.get('AccessRightsName_Name')+' для должности';
				wnd = 'swAccessRightsLimitEditWindow';
				break;
			case 'lpu':
				params.type = type;
				params.title = 'Доступ к льготе '+record.get('AccessRightsName_Name')+' для МО';
				wnd = 'swAccessRightsLimitEditWindow';
				break;
			case 'usergroups':
				params.type = type;
				params.title = 'Доступ к льготе '+record.get('AccessRightsName_Name')+' для группы пользователей';
				wnd = 'swAccessRightsLimitEditWindow';
				break;
			case 'user':
				params.title = 'Доступ к льготе '+record.get('AccessRightsName_Name')+' для пользователей';
				wnd = 'swAccessRightsLimitUsersSelectWindow';
				break;
		}

		getWnd(wnd).show(params);
	};

	objects.deleteAccessRightsLimit = function() {
		var grid = objects.AccessRightsPrivilegeTypeLimitGrid.getGrid();
		var idField = 'AccessRightsLimit_id';
		var url = '/?c=AccessRightsPrivilegeType&m=deleteAccessRightsLimit';
		var question = 'Удалить выбранную запись?';

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
								Ext.Msg.alert('Ошибка', 'Ошибка запроса к серверу. Попробуйте повторить операцию.');
							}
						},
						params: params,
						url: url
					});
				}
			},
			icon: Ext.MessageBox.QUESTION,
			msg: question,
			title: 'Вопрос'
		});
	};

	objects.resize = function(panel, maximum) {
		var pSize, gSize;
		pSize = maximum ? 400 : 213;
		gSize = pSize-25;

		panel.setHeight(pSize);
		panel.items.itemAt(0).setHeight(gSize);
	};

	objects.AccessRightsPrivilegeTypeGrid = new sw.Promed.ViewFrame({
		dataUrl: '/?c=AccessRightsPrivilegeType&m=loadAccessRightsGrid',
		id: 'AccessRightsPrivilegeTypeGrid',
		border: false,
		layout: 'fit',
		autoLoadData: false,
		showCountInTop: false,
		stripeRows: true,
		root: 'data',
		stringfields: [
			{name: 'AccessRightsPrivilegeType_id', type: 'int', key: true},
			{name: 'AccessRightsName_id', type: 'int', hidden: true},
			{name: 'AccessRightsName_Name', type: 'string', hidden: true}, // AccessRightsName_Name подставляется из наименования льготы
			{name: 'PrivilegeType_Code', type: 'string', header: 'Код льготы', width: 100},
			{name: 'PrivilegeType_Name', type: 'string', header: 'Наименование льготы', id: 'autoexpand'}
		],
		actions: [
			{name:'action_add', handler: function(){objects.openAccessRightsPrivilegeTypeEditWindow('add');}},
			{name:'action_edit', handler: function(){objects.openAccessRightsPrivilegeTypeEditWindow('edit');}},
			{name:'action_view', hidden: true},
			{name:'action_delete', handler: function(){objects.deleteAccessRightsPrivilegeType();}},
			{name:'action_refresh', hidden: true},
			{name:'action_print', hidden: true}
		],
		onRowSelect: function(sm,index,record){
			var title = '';

			if (Ext.isEmpty(record.get('AccessRightsName_id'))) {
				title = 'Доступ к льготе';
				Ext.getCmp('addAccessRightsLimitButton').disable();
				Ext.getCmp('deleteAccessRightsLimitButton').disable();
			} else {
				title = 'Доступ к льготе: <span style="color:black;font-weight:bold;">'+record.get('PrivilegeType_Name')+'</span>';
				Ext.getCmp('addAccessRightsLimitButton').enable();
				Ext.getCmp('deleteAccessRightsLimitButton').enable();
			}

			objects.MainPanel.findById('AccessRightsPrivilegeTypeLimitPanel').setTitle(title);

			var params = {AccessRightsName_id: record.get('AccessRightsName_id')};
			objects.MainPanel.findById('AccessRightsPrivilegeTypeLimitPanel').isLoaded = true;
			objects.AccessRightsPrivilegeTypeLimitGrid.loadData({params: params, globalFilters: params});
		}
	});

	objects.AccessRightsPrivilegeTypeLimitGrid = new sw.Promed.ViewFrame({
		dataUrl: '/?c=AccessRightsPrivilegeType&m=loadAccessRightsLimitGrid',
		id: 'AccessRightsPrivilegeTypeLimitGrid',
		border: false,
		layout: 'fit',
		autoLoadData: false,
		showCountInTop: false,
		stripeRows: true,
		root: 'data',
		stringfields: [
			{name: 'AccessRightsLimit_id', type: 'int', header: 'ID', key: true},
			{name: 'AccessRightsLimitType_Name', type: 'string', header: 'Наименование', width: 180},
			{name: 'AccessRightsLimit_Value', type: 'string', header: 'Значение', id: 'autoexpand'}
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
				title: 'Льгота',
				id: 'AccessRightsPrivilegeTypePanel',
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
							objects.AccessRightsPrivilegeTypeGrid.getGrid().getStore().load();
						}
						objects.MainPanel.doLayout();
					},
					'collapse': function(panel) {
						objects.MainPanel.doLayout();
					},
					'beforeexpand': function(panel) {
						var diag = panel;
						var limit = objects.MainPanel.findById('AccessRightsPrivilegeTypeLimitPanel');
						limit.collapsed ? objects.resize(diag, true) : objects.resize(limit);
					},
					'beforecollapse': function(panel) {
						var diag = panel;
						var limit = objects.MainPanel.findById('AccessRightsPrivilegeTypeLimitPanel');
						limit.collapsed ? objects.resize(diag) : objects.resize(limit, true);
					}
				},
				items: [objects.AccessRightsPrivilegeTypeGrid]
			},
			{
				title: 'Доступ к льготе',
				id: 'AccessRightsPrivilegeTypeLimitPanel',
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
						var diag = objects.MainPanel.findById('AccessRightsPrivilegeTypePanel');
						var limit = panel;
						diag.collapsed ? objects.resize(limit, true) : objects.resize(diag);
					},
					'beforecollapse': function(panel) {
						var diag = objects.MainPanel.findById('AccessRightsPrivilegeTypePanel');
						var limit = panel;
						diag.collapsed ? objects.resize(limit) : objects.resize(diag, true);
					}
				},
				items: [objects.AccessRightsPrivilegeTypeLimitGrid]
			}
		]
	});

	return objects.MainPanel;
}())