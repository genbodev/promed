/**
* swGroupViewWindow - окно редактирования адресной книги.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Messages
* @access       public
* @copyright    Copyright (c) 2009-2010 Swan Ltd.
* @author       Марков Андрей
* @version      декабрь.2011
*
*/

sw.Promed.swGroupViewWindow = Ext.extend(sw.Promed.BaseForm, {
	buttonAlign: 'right',
	modal: true,
	closable: true,
	maximized: true,
	shim: false,
	maximizable: true,
	closeAction: 'hide',
	id: 'swGroupViewWindow',
	title: lang['gruppyi_i_prava'],
	iconCls: 'groups16',
	plain: true,
	callback: Ext.emptyFn,
	buttons: [
		'-',
		{
			text: BTN_FRMHELP,
			iconCls: 'help16',
			handler: function(button, event)
			{
				ShowHelp(this.ownerCt.title);
			}
		}, {
			text      : BTN_FRMCLOSE,
			tabIndex  : -1,
			tooltip   : lang['zakryit'],
			iconCls   : 'cancel16',
			handler   : function()
			{
				this.ownerCt.hide();
			}
		}
	],
	show: function() 
	{
		sw.Promed.swGroupViewWindow.superclass.show.apply(this, arguments);
		/*
		if (arguments[0]) {
			if ( arguments[0].onSelect ) {
				this.callback = arguments[0].onSelect;
				this.RolesPanel.setActionDisabled('action_edit', false);
			} else {
				this.RolesPanel.setActionDisabled('action_edit', true);
			}
		}
		else {
			this.callback = Ext.emptyFn;
			this.RolesPanel.setActionDisabled('action_edit', true);
		}*/
	},
	
	reloadTree: function() {
		var tree = this.GroupsPanel;
		var root = tree.getRootNode();
		root.select();
		tree.getLoader().load(root);
	},
	/** Доступ к редактированию/удалению в зависимости от типа группы 
	 * 
	 */ 
	isUserAccess: function(type) {
		return ((type>=0 && isSuperAdmin()) || (type>=1 && isLpuAdmin()) || (type==2));
	},
	
	addNewUserToAddressBook: function()
	{
		var win = this;
		var args = {};
		var node = this.GroupsPanel.getSelectionModel().getSelectedNode();
		if (node && this.isUserAccess(node.attributes.type))
		{
			this.getLoadMask().show();
			args.onHide = function()
			{
				//win.reloadGrid();
				win.getLoadMask().hide();
			}
			args.selectUser = function(data)
			{
				// Добавляем пользователя в адресную книгу 
				if (data) 
				{
					Ext.Ajax.request(
					{
						url: '/?c=U&m=addGroupUser',
						params: 
						{
							// все параметры относящиеся к группе 
							filter_type: node.attributes.type,
							filter: node.attributes.id,
							// и выбранный пользователь 
							user_id: data.pmUser_id
						},
						callback: function(opt, success, resp) 
						{
							win.getLoadMask().hide();
							win.reloadGrid(null, true);
						}
					});
				}
			}
			getWnd('swUserSearchWindow').show(args);
		}
		else 
		{
			if (!node)
				sw.swMsg.alert(lang['oshibka'], lang['dobavlyat_polzovatelya_mojno_tolko_v_opredelennuyu_gruppu_vyiberite_nujnuyu_gruppu_i_povtorite_popyitku']);
			else 
				sw.swMsg.alert(lang['dostup_zakryit'], lang['vashi_prava_ne_pozvolyayut_izmenyat_sostav_dannoy_gruppyi']);
		}
	},
	editRole: function() {
		var record = this.RolesPanel.getGrid().getSelectionModel().getSelected();
		if (record) {
			getWnd('swRoleEditWindow').show({id:record.get('Group_Code'),name:record.get('Group_Name'), type:'Group'});
		}
	},
	deleteGroup: function() {
		var win = this;
		var args = {};
		var node = this.GroupsPanel.getSelectionModel().getSelectedNode();
		var record = this.RolesPanel.getGrid().getSelectionModel().getSelected();
		if (/*node && */record/* && this.isUserAccess(node.attributes.type)*/) {
			this.getLoadMask().show();
			sw.swMsg.show({
				icon: Ext.MessageBox.QUESTION,
				msg: 'Вы хотите удалить группу "'+record.get('Group_Name')+'"?',
				title: lang['podtverjdenie'],
				buttons: Ext.Msg.YESNO,
				fn: function(buttonId, text, obj)
				{
					if ('yes' == buttonId)
					{
						Ext.Ajax.request(
						{
							url: '/?c=User&m=deleteGroup', 
							params: {
								dn: record.get('dn'),
								Group_id: record.get('Group_id'),
								pmUserCacheGroup_id: record.get('pmUserCacheGroup_id')
							},
							failure: function(response, options)
							{
								Ext.Msg.alert(lang['oshibka'], lang['oshibka_udaleniya_udalenie_dannoy_gruppyi_nevozmojno']);
								win.getLoadMask().hide();
							},
							success: function(response, action)
							{
								win.getLoadMask().hide();
								if (response.responseText)
								{
									var result = Ext.util.JSON.decode(response.responseText);
									if (result.success) {
										win.reloadGrid(null, true);
									}
								}
								else
								{
									Ext.Msg.alert(lang['oshibka'], lang['oshibka_udaleniya_otsutstvuet_otvet_servera']);
								}
							}
						});
					}
					else
					{
						win.getLoadMask().hide();
					}
				}.createDelegate(this)
			});
		} else {
			if (!node || !record)
				sw.swMsg.alert(lang['oshibka'], lang['ne_vyibrana_gruppa_dlya_udaleniya']);
			else 
				sw.swMsg.alert(lang['dostup_zakryit'], lang['vashi_prava_ne_pozvolyayut_udalyat_dannuyu_gruppu']);
		}
	},
	onSelect: function() {
		var record = this.RolesPanel.getGrid().getSelectionModel().getSelected();
		if (record) {
			this.hide();
			this.callback(record.data);
		}
	},
	// Функция вывода меню по клику правой клавиши
	onTreeContextMenu: function (node, e)
	{
		// На правый клик переходим на выделяемую запись
		node.select();
		// Отрабатываем метод Click
		this.GroupsPanel.fireEvent('click', node);
		var c = node.getOwnerTree().contextMenu;
		c.contextNode = node;
		c.showAt(e.getXY());
	},
	onTreeClick: function (node,e)
	{
		var level = node.getDepth();
		var owner = node.getOwnerTree().ownerCt;
		// Проверка на доступность  
		//log(node.attributes.type);
		var access = this.isUserAccess(node.attributes.type);
		this.reloadGrid(node);
		
	},
	reloadGrid: function(node, reload)
	{
		node = (node)?node:this.GroupsPanel.getSelectionModel().getSelectedNode();
		var group_id = this.RolesPanel.getParam('group_id');
		// Перезагружаем только при изменении ветки, чтобы лишний раз не дергать сервер 
		if (node && (!group_id || group_id != node.attributes.id || reload)) {
			this.RolesPanel.loadData({
				params:{
					filter: node.attributes.id
				}, 
				globalFilters:{
					filter: node.attributes.id
				}
			});
		}
	},
	
	initComponent: function() 
	{
		// TODO: Контекстное меню реализовать в полной мере
		
		this.TreeActions = [];
		/*this.TreeActions['refresh'] = new Ext.Action({
			tooltip: BTN_GRIDREFR, 
			text:'',
			iconCls: 'refresh16',
			handler: function()
			{
				this.reloadTree();
			}.createDelegate(this)
		});*/
		this.ContextMenu = new Ext.menu.Menu();
		/*for (key in this.TreeActions)
		{
			this.ContextMenu.add(this.TreeActions[key]);
		}*/
		this.GroupsPanel = new Ext.tree.TreePanel({
			rootVisible: false,
			autoLoad: false,
			region: 'west',
			floatable: false,
			titleCollapse: true,
			animCollapse: false,
			split: true,
			collapsible: true,
			width: 200,
			maxWidth: 200,
			maxWidth: 400,
			title: lang['filtr'],
			enableDD: false,
			autoScroll: true,
			animate: false,
			contextMenu: this.ContextMenu,
			root:
			{
				nodeType: 'async',
				text: lang['filtr'],
				id: 'all',
				draggable: false,
				expandable: true
			},
			/*tbar: new Ext.Toolbar({
				autoHeight: true,
				items: [
					this.TreeActions['refresh']
				]
			}),
			*/
			loader: new Ext.tree.TreeLoader({
				listeners:
				{
					beforeload: function(TreeLoader, node) 
					{
						TreeLoader.baseParams.level = node.getDepth();
					},
					load: function(tree, node, r)
					{
						if (node.id = 'root' && node.hasChildNodes() == true) {
							node.expand(); 
							if (node.firstChild) {
								node = node.firstChild;
								node.select();
								node.getOwnerTree().fireEvent('click', node);
							}
						}
					}
				},
				dataUrl: '/?c=User&m=getGroupTree'
			})
		});
		
		this.GroupsPanel.addListener('contextmenu', this.onTreeContextMenu,this);
		this.GroupsPanel.on('click', this.onTreeClick, this);
		this.GroupsPanel.on('dblclick', function() {this.openUserGroupsWindow('edit');}.createDelegate(this), this);
		
		this.RolesPanel = new sw.Promed.ViewFrame({
			autoExpandColumn: 'autoexpand',
			object: 'RolesGroup',
			autoExpandMin: 100,
			anchor: '100%',
			editformclassname: 'swGroupEditWindow',
			region: 'center',
			pageSize: 20,
			autoLoadData: false,
			stripeRows: true,
			actions: [
				{ name: 'action_add' },
				{ name: 'action_edit' },
				{ name: 'action_view', hidden: true },
				{ name: 'action_delete', handler: function(){this.deleteGroup();}.createDelegate(this) },
				{ name: 'action_refresh' },
				{ name: 'action_print', text: lang['prava_gruppyi'], icon:'img/icons/group-role16.png', handler: function(){this.editRole();}.createDelegate(this) }
			],
			stringfields: [
				{name: 'pmUserCacheGroup_id', type: 'string', hidden: true, key: true },
				{name: 'Group_id', type: 'string', hidden: true, key: true },
				{name: 'Group_Code',  type: 'string', hidden: true, isparams: true },
				{name: 'dn',  hidden: true, isparams: true },
				{name: 'Group_Name',  type: 'string', header: lang['nazvanie_gruppyi'], id: 'autoexpand', isparams: true },
				/*{name: 'Org_Nick',  type: 'string', header: lang['organizatsiya'], width: 180},*/
				{name: 'pmUser_Name',  type: 'string', header: lang['avtor_login'], width: 120},
				{name: 'Group_UserCount', type: 'string', header: lang['kol-vo_akkauntov'], width: 120 },
				{name: 'Group_ParallelSessions', type: 'string', hidden: true, header: langs('Количество параллельных сеансов одного пользователя'), width: 120, isparams: true },
				{name: 'Group_IsBlocked', type: 'checkbox', header: lang['zablokirovana'], width: 100, isparams: true },
				{name: 'Group_IsOnly', type: 'checkbox', header: langs('Единственность'), width: 100, isparams: true }
			],
			onEnter: function() {
				this.editRole();
			}.createDelegate(this),
			onDblClick: function() {
				this.editRole();
			}.createDelegate(this),
			onRowSelect: function(sm,index,record) {
				if (this.getCount()>0) {
					this.setActionDisabled('action_delete', (record.get('Group_UserCount')>0));
				}
			},
			dataUrl: '/?c=User&m=loadGroups',
			onLoadData: function() {
				var node = this.GroupsPanel.getSelectionModel().getSelectedNode();
			}.createDelegate(this)
		});
		
		Ext.apply(this, 
		{
			layout: 'border',
			items: [this.GroupsPanel, this.RolesPanel]
		});
		sw.Promed.swGroupViewWindow.superclass.initComponent.apply(this, arguments);
	}
});