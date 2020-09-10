/**
* swAddressBookEditWindow - окно редактирования адресной книги.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Messages
* @access       public
* @copyright    Copyright (c) 2009-2010 Swan Ltd.
* @author       Dmitry Storozhev
* @version      24.08.2011
*
*/

sw.Promed.swAddressBookEditWindow = Ext.extend(sw.Promed.BaseForm, {
	buttonAlign: 'right',
	modal: true,
	closable: true,
	maximized: true,
	shim: false,
	maximizable: true,
	closeAction: 'hide',
	id: 'swAddressBookEditWindow',
	objectName: 'swAddressBookEditWindow',
	title: lang['adresnaya_kniga'],
	iconCls: 'address-book16',
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
		sw.Promed.swAddressBookEditWindow.superclass.show.apply(this, arguments);
		if (arguments[0]) {
			if ( arguments[0].onSelect ) {
				this.callback = arguments[0].onSelect;
				this.ContactPanel.setActionDisabled('action_edit', false);
			} else {
				this.ContactPanel.setActionDisabled('action_edit', true);
			}
		}
		else {
			this.callback = Ext.emptyFn;
			this.ContactPanel.setActionDisabled('action_edit', true);
		}
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
	
	openUserGroupsWindow: function(mode)
	{
		// определение параметров 
		var node = this.GroupsPanel.getSelectionModel().getSelectedNode();
		var params = {
			callback: function() {
				this.reloadTree();
			}.createDelegate(this),
			onHide: function() {
				
			}.createDelegate(this)
		};
		if (node && mode=='edit' && this.isUserAccess(node.attributes.type)) {
			params.group_name = node.attributes.text;
			params.group_type = node.attributes.type;
			params.group_id = node.attributes.id;
			params.dn = node.attributes.dn;
			params.action = 'edit';
		} else if (mode=='add') {
			params.action = 'add';
		} else {
			sw.swMsg.alert(lang['dostup_zakryit'], lang['vashi_prava_ne_pozvolyayut_redaktirovat_dannuyu_gruppu']);
			return false;
		}
		
		getWnd('swUserGroupsEditWindow').show(params);
	},
	deleteUserGroupsWindow: function()
	{
		// определение параметров 
		var node = this.GroupsPanel.getSelectionModel().getSelectedNode();
		var params = {};
		var form = this;
		if (node && this.isUserAccess(node.attributes.type)) {
			sw.swMsg.show({
				icon: Ext.MessageBox.QUESTION,
				msg: 'Вы хотите удалить группу "'+node.attributes.desc+'" из адресной книги?<br/>Обратите внимание: <br/><b>Группа будет удалена со всеми пользователями</b>.',
				title: lang['podtverjdenie'],
				buttons: Ext.Msg.YESNO,
				fn: function(buttonId, text, obj)
				{
					if ('yes' == buttonId)
					{
						Ext.Ajax.request(
						{
							url: '/?c=Messages&m=deleteGroup',
							params: {
								group_id: node.attributes.id, 
								dn: node.attributes.dn, 
								group_name: node.attributes.desc,
								group_type: node.attributes.type
							},
							failure: function(response, options)
							{
								Ext.Msg.alert(lang['oshibka'], lang['oshibka_udaleniya_udalenie_gruppyi_nevozmojno']);
							},
							success: function(response, action)
							{
								if (response.responseText)
								{
									var answer = Ext.util.JSON.decode(response.responseText);
									form.reloadTree();
								}
								else
								{
									Ext.Msg.alert(lang['oshibka'], lang['oshibka_udaleniya_otsutstvuet_otvet_servera']);
								}
							}
						});
					}
					/*else
					{
						form.reloadTree();
					}*/
				}.createDelegate(this)
			});
		} else {
			sw.swMsg.alert(lang['dostup_zakryit'], lang['vashi_prava_ne_pozvolyayut_udalit_dannuyu_gruppu']);
			return false;
		}
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
						url: '/?c=Messages&m=addGroupUser',
						params: 
						{
							// все параметры относящиеся к группе 
							group_name: node.attributes.text,
							group_type: node.attributes.type,
							group_id: node.attributes.id,
							dn: node.attributes.dn,
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
	deleteUserFromAddressBook: function()
	{
		var win = this;
		var args = {};
		var node = this.GroupsPanel.getSelectionModel().getSelectedNode();
		var record = this.ContactPanel.getGrid().getSelectionModel().getSelected();
		if (node && record && this.isUserAccess(node.attributes.type)) {
			this.getLoadMask().show();
			sw.swMsg.show({
				icon: Ext.MessageBox.QUESTION,
				msg: lang['vyi_hotite_udalit_polzovatelya_iz_gruppyi_adresnoy_knigi'],
				title: lang['podtverjdenie'],
				buttons: Ext.Msg.YESNO,
				fn: function(buttonId, text, obj)
				{
					if ('yes' == buttonId)
					{
						Ext.Ajax.request(
						{
							url: '/?c=Messages&m=deleteGroupUser', 
							params: {
								group_id: node.attributes.id, 
								dn: node.attributes.dn, 
								group_name: node.attributes.desc,
								group_type: node.attributes.type,
								user_id: record.get('pmUser_id')
							},
							failure: function(response, options)
							{
								Ext.Msg.alert(lang['oshibka'], lang['oshibka_udaleniya_udalenie_polzovatelya_nevozmojno']);
								win.getLoadMask().hide();
							},
							success: function(response, action)
							{
								win.getLoadMask().hide();
								if (response.responseText)
								{
									var answer = Ext.util.JSON.decode(response.responseText);
									win.reloadGrid(null, true);
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
				sw.swMsg.alert(lang['oshibka'], lang['ne_vyibran_polzovatel_dlya_udaleniya']);
			else 
				sw.swMsg.alert(lang['dostup_zakryit'], lang['vashi_prava_ne_pozvolyayut_izmenyat_sostav_dannoy_gruppyi']);
		}
	},
	onSelect: function() {
		var record = this.ContactPanel.getGrid().getSelectionModel().getSelected();
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
		this.TreeActions['edit'].setDisabled(!access);
		this.TreeActions['delete'].setDisabled(!access);
		
		/*
				owner.findById('regvRightPanel').setVisible(true);
				var Lpu_id = node.parentNode.parentNode.attributes.object_value;
				var RegistryType_id = node.parentNode.attributes.object_value;
				var RegistryStatus_id = node.attributes.object_value;
				owner.AccountGrid.setActionDisabled('action_add', (RegistryStatus_id!=3));
				// скрываем/открываем колонку
				owner.AccountGrid.setColumnHidden('RegistryStacType_Name', (RegistryType_id!=1));
				
				// Меняем колонки и отображение 
				if (RegistryType_id==1)
				{
					// Для стаца одни названия 
					owner.DataGrid.setColumnHeader('RegistryData_Uet', lang['k_d_fakt']);
					owner.DataGrid.setColumnHeader('EvnVizitPL_setDate', lang['postuplenie']);
					owner.DataGrid.setColumnHidden('EvnPS_disDate', false);
					owner.DataGrid.setColumnHidden('RegistryData_KdPay', false);
					owner.DataGrid.setColumnHidden('RegistryData_KdPlan', false);
					
					// без оплаты 
					//owner.DataGrid.setColumnHeader('Evn_setDate', 'Поступление');
					//owner.NoPayGrid.setColumnHidden('Evn_disDate', false);
					owner.NoPayGrid.setColumnHidden('RegistryNoPay_KdFact', false);
					owner.NoPayGrid.setColumnHidden('RegistryNoPay_KdPlan', false);
				}
				else 
				{
					// Для остальных - другие 
					owner.DataGrid.setColumnHeader('RegistryData_Uet', lang['uet']);
					owner.DataGrid.setColumnHeader('EvnVizitPL_setDate', lang['poseschenie']);
					owner.DataGrid.setColumnHidden('EvnPS_disDate', true);
					owner.DataGrid.setColumnHidden('RegistryData_KdPay', true);
					owner.DataGrid.setColumnHidden('RegistryData_KdPlan', true);

				}
		*/
		this.reloadGrid(node);
		
	},
	reloadGrid: function(node, reload)
	{
		node = (node)?node:this.GroupsPanel.getSelectionModel().getSelectedNode();
		var group_id = this.ContactPanel.getParam('group_id');
		// Перезагружаем только при изменении ветки, чтобы лишний раз не дергать сервер 
		if (node && (!group_id || group_id != node.attributes.id || reload)) {
			this.ContactPanel.loadData({
				params:{
					group_name: node.attributes.text,
					group_type: node.attributes.type,
					group_id: node.attributes.id,
					dn: node.attributes.dn
				}, 
				globalFilters:{
					group_name: node.attributes.text,
					group_type: node.attributes.type,
					group_id: node.attributes.id,
					dn: node.attributes.dn,
					start: 0, 
					limit: 100
				}
			});
		}
	},
	
	initComponent: function() 
	{
		// TODO: Контекстное меню реализовать в полной мере
		
		this.TreeActions = [];
		this.TreeActions['add'] = new Ext.Action({
			tooltip: BTN_GRIDADD, 
			text:'',
			iconCls: 'add16',
			handler: function()
			{
				this.openUserGroupsWindow('add');
			}.createDelegate(this)
		});
		this.TreeActions['edit'] = new Ext.Action({
			tooltip: BTN_GRIDEDIT, 
			text:'',
			iconCls: 'edit16',
			handler: function()
			{
				this.openUserGroupsWindow('edit');
			}.createDelegate(this)
		});
		this.TreeActions['delete'] = new Ext.Action({
			tooltip: BTN_GRIDDEL, 
			text:'',
			iconCls: 'delete16',
			handler: function()
			{
				this.deleteUserGroupsWindow();
			}.createDelegate(this)
		});
		this.TreeActions['refresh'] = new Ext.Action({
			tooltip: BTN_GRIDREFR, 
			text:'',
			iconCls: 'refresh16',
			handler: function()
			{
				this.reloadTree();
			}.createDelegate(this)
		});
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
			tbar: new Ext.Toolbar({
				autoHeight: true,
				items: [
					this.TreeActions['add'],
					this.TreeActions['edit'],
					this.TreeActions['delete'],
					'-',
					this.TreeActions['refresh']
				]
			}),
			loader: new Ext.tree.TreeLoader({
				onBeforeLoad: function(TreeLoader, node) 
				{
					TreeLoader.baseParams.level = node.getDepth();
				},
				listeners:
				{
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
				dataUrl: '/?c=Messages&m=getGroups'
			})
		});
		
		this.GroupsPanel.addListener('contextmenu', this.onTreeContextMenu,this);
		this.GroupsPanel.on('click', this.onTreeClick, this);
		this.GroupsPanel.on('dblclick', function() {this.openUserGroupsWindow('edit');}.createDelegate(this), this);
		
		this.ContactPanel = new sw.Promed.ViewFrame({
			id: this.id + '_ContactPanel',
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 100,
			anchor: '100%',
			editformclassname: 'swUserProfileEditWindow',
			region: 'center',
			pageSize: 20,
			actions: [
				{ name: 'action_add', handler: function(){this.addNewUserToAddressBook();}.createDelegate(this) },
				{ name: 'action_edit', icon:'img/icons/ok16.png', text: lang['vyibrat'], handler: function(){this.onSelect();}.createDelegate(this)},
				{ name: 'action_view', icon:'img/icons/user16.png', text: lang['profil']},
				{ name: 'action_delete', handler: function(){this.deleteUserFromAddressBook();}.createDelegate(this)},
				{ name: 'action_refresh' },
				{ name: 'action_print', disabled: true }
			],
			autoLoadData: false,
			stripeRows: true,
			root: 'data',
			stringfields: [
				{name: 'pmUser_id', type: 'int', hidden: true, key: true },
				{name: 'pmUser_Login', type: 'string', hidden: true, isparams: true },
				{name: 'pmUser_surName',  type: 'string', header: lang['familiya'], id: 'autoexpand'},
				{name: 'pmUser_firName',  type: 'string', header: lang['imya'], width: 100},
				{name: 'pmUser_secName',  type: 'string', header: lang['otchestvo'], width: 100},
				{name: 'MedSpec_Name', type: 'string', header: lang['doljnost'], width: 150 },
				{name: 'LpuSection_Name', type: 'string', header: lang['otdelenie'], width: 150 },
				{name: 'Lpu_Nick', type: 'string', header: lang['lpu'], width: 180, isparams: true }
			],
			paging: true,
			dataUrl: '/?c=Messages&m=getGroupUser',
			totalProperty: 'totalCount',
			onLoadData: function() {
				var node = this.GroupsPanel.getSelectionModel().getSelectedNode();
				this.ContactPanel.setActionDisabled('action_add', !this.isUserAccess(node.attributes.type));
				this.ContactPanel.setActionDisabled('action_delete', !this.isUserAccess(node.attributes.type));
			}.createDelegate(this)
		});
		
		Ext.apply(this, 
		{
			layout: 'border',
			items: [this.GroupsPanel, this.ContactPanel]
		});
		sw.Promed.swAddressBookEditWindow.superclass.initComponent.apply(this, arguments);
	}
});