/**
* swWndViewWindow - окно просмотра и редактирования данных о загружаемых JS-файлах.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Messages
* @access       public
* @copyright    Copyright (c) 2009-2011 Swan Ltd.
* @author       Марков Андрей
* @version      декабрь.2011
*
*/

sw.Promed.swWndViewWindow = Ext.extend(sw.Promed.BaseForm, {
	buttonAlign: 'right',
	modal: true,
	closable: true,
	maximized: true,
	shim: false,
	maximizable: true,
	closeAction: 'hide',
	id: 'swWndViewWindow',
	title: lang['okna'],
	iconCls: 'windows16',
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
		sw.Promed.swWndViewWindow.superclass.show.apply(this, arguments);
	},
	reloadTree: function() {
		var tree = this.GroupsPanel;
		var root = tree.getRootNode();
		root.select();
		tree.getLoader().load(root);
	},
	addNewUserToAddressBook: function() {
		var win = this;
		var args = {};
		var node = this.GroupsPanel.getSelectionModel().getSelectedNode();
		if (node) {
			this.getLoadMask().show();
			args.onHide = function() {
				//win.reloadGrid();
				win.getLoadMask().hide();
			}
			args.selectUser = function(data) {
				// Добавляем пользователя в адресную книгу 
				if (data) {
					Ext.Ajax.request({
						url: '/?c=U&m=addGroupUser',
						params: {
							// все параметры относящиеся к группе 
							filter_type: node.attributes.type,
							filter: node.attributes.id,
							// и выбранный пользователь 
							user_id: data.pmUser_id
						},
						callback: function(opt, success, resp) {
							win.getLoadMask().hide();
							win.reloadGrid(null, true);
						}
					});
				}
			}
			getWnd('swUserSearchWindow').show(args);
		}
		else {
			if (!node)
				sw.swMsg.alert(lang['oshibka'], lang['dobavlyat_polzovatelya_mojno_tolko_v_opredelennuyu_gruppu_vyiberite_nujnuyu_gruppu_i_povtorite_popyitku']);
			else 
				sw.swMsg.alert(lang['dostup_zakryit'], lang['vashi_prava_ne_pozvolyayut_izmenyat_sostav_dannoy_gruppyi']);
		}
	},
	deleteGroup: function() {
		var win = this;
		var args = {};
		var node = this.GroupsPanel.getSelectionModel().getSelectedNode();
		var record = this.WindowsPanel.getGrid().getSelectionModel().getSelected();
		if (/*node && */record/* && this.isUserAccess(node.attributes.type)*/) {
			this.getLoadMask().show();
			sw.swMsg.show({
				icon: Ext.MessageBox.QUESTION,
				msg: 'Вы хотите удалить группу "'+record.get('Group_Name')+'"?',
				title: lang['podtverjdenie'],
				buttons: Ext.Msg.YESNO,
				fn: function(buttonId, text, obj) {
					if ('yes' == buttonId) {
						Ext.Ajax.request({
							url: '/?c=User&m=deleteGroup', 
							params: {
								dn: record.get('dn'),
								Group_id: record.get('Group_id')
							},
							failure: function(response, options) {
								Ext.Msg.alert(lang['oshibka'], lang['oshibka_udaleniya_udalenie_dannoy_gruppyi_nevozmojno']);
								win.getLoadMask().hide();
							},
							success: function(response, action) {
								win.getLoadMask().hide();
								if (response.responseText) {
									var result = Ext.util.JSON.decode(response.responseText);
									if (result.success) {
										win.reloadGrid(null, true);
									}
								}
								else {
									Ext.Msg.alert(lang['oshibka'], lang['oshibka_udaleniya_otsutstvuet_otvet_servera']);
								}
							}
						});
					}
					else {
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
		var record = this.WindowsPanel.getGrid().getSelectionModel().getSelected();
		if (record) {
			this.hide();
			this.callback(record.data);
		}
	},
	// Функция вывода меню по клику правой клавиши
	onTreeContextMenu: function (node, e) {
		// На правый клик переходим на выделяемую запись
		node.select();
		// Отрабатываем метод Click
		this.GroupsPanel.fireEvent('click', node);
		var c = node.getOwnerTree().contextMenu;
		c.contextNode = node;
		c.showAt(e.getXY());
	},
	onTreeClick: function (node,e) {
		var level = node.getDepth();
		var owner = node.getOwnerTree().ownerCt;
		// Проверка на доступность  
		//log(node.attributes.type);
		this.reloadGrid(node);
	},
	reloadGrid: function(node, reload) {
		node = (node)?node:this.GroupsPanel.getSelectionModel().getSelectedNode();
		var group = this.WindowsPanel.getParam('group');
		// Перезагружаем только при изменении ветки, чтобы лишний раз не дергать сервер 
		if (node && (!group || group != node.attributes.text || reload)) {
			this.WindowsPanel.loadData({
				globalFilters:{
					group: node.attributes.text
				}
			});
		}
	},
	
	initComponent: function() {
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
			title: lang['gruppyi'],
			enableDD: false,
			autoScroll: true,
			animate: false,
			contextMenu: this.ContextMenu,
			root:
			{
				nodeType: 'async',
				text: lang['gruppyi'],
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
						log(node.getDepth());
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
				dataUrl: '/?c=promed&m=loadGroupFiles'
			})
		});
		
		this.GroupsPanel.addListener('contextmenu', this.onTreeContextMenu,this);
		this.GroupsPanel.on('click', this.onTreeClick, this);
		this.GroupsPanel.on('dblclick', function() {this.openUserGroupsWindow('edit');}.createDelegate(this), this);
		
		this.WindowsPanel = new sw.Promed.ViewFrame({
			object: 'WindowsGroup',
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 100,
			anchor: '100%',
			editformclassname: 'swWndEditWindow',
			region: 'center',
			pageSize: 20,
			actions: [
				{ name: 'action_add' },
				{ name: 'action_edit' },
				{ name: 'action_view', visible: true },
				{ name: 'action_delete', handler: function(){this.deleteRecord();}.createDelegate(this) },
				{ name: 'action_refresh' },
				{ name: 'action_print' }
			],
			autoLoadData: false,
			stripeRows: true,
			stringfields: [
				{name: 'id', type: 'string', key: true },
				{name: 'code', type: 'string', header: lang['obyekt'], width: 160, isparams: true  },
				{name: 'region', type: 'string', header: lang['region'], width: 80, isparams: true },
				{name: 'group', type: 'string', header: lang['gruppa'], width: 160, isparams: true },
				{name: 'title',  type: 'string', header: lang['nazvanie_okna'], id: 'autoexpand', isparams: true },
				{name: 'table',  type: 'string', header: lang['tablitsa'], width: 140, isparams: true },
				{name: 'desc',  type: 'string', header: lang['opisanie'], hidden: true, isparams: true },
				{name: 'path',  type: 'string', header: lang['put_k_faylu'], width: 300, isparams: true},
				{name: 'available', type: 'checkbox', header: lang['vsegda_dostupen'], width: 100, isparams: true }
			],
			onRowSelect: function(sm,index,record) {
				if (this.getCount()>0) {
					this.setActionDisabled('action_delete', (record.get('Group_UserCount')>0));
				}
			},
			dataUrl: '/?c=promed&m=loadFiles',
			onLoadData: function() {
				var node = this.GroupsPanel.getSelectionModel().getSelectedNode();
			}.createDelegate(this)
		});
		
		Ext.apply(this, 
		{
			layout: 'border',
			items: [this.GroupsPanel, this.WindowsPanel]
		});
		sw.Promed.swWndViewWindow.superclass.initComponent.apply(this, arguments);
	}
});