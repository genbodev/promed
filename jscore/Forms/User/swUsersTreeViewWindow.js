/**
 * swUsersViewWindow - окно просмотра и редактирования пользователей.
 * 
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 * 
 * 
 * @package DLO
 * @access public
 * @copyright Copyright (c) 2009 Swan Ltd.
 * @author Pshenicyn Ivan aka IVP (ipshon@rambler.ru)
 * @version 08.04.2009
 */
		
sw.Promed.swUsersTreeViewWindow = Ext.extend(sw.Promed.BaseForm, {
	layout: 'border',
	maximized : true,
	modal : true,
	resizable : false,
	draggable : false,
	closeAction : 'hide',
	buttonAlign : 'right',
	id : 'UsersTreeViewWindow',
	plain : true,
	title : lang['polzovateli'],
	doEdit : function(mode) {
		var wnd = this;
		
		if (wnd.UsersGrid.getGrid().getSelectionModel().getSelected())
			var user_login = wnd.UsersGrid.getGrid().getSelectionModel().getSelected().data.login;
		else
			var user_login = 0;
			
		var org_id = this.OrgTreePanel.getSelectionModel().getSelectedNode().id;
		
		var params = {
			action : mode,
			fields : {
				action : mode,
				org_id : org_id,
				user_login : user_login
			},
			owner : this,
			callback : function(owner) {
				wnd.doFilter(org_id);
			},
			onClose : function() {
				var grid = wnd.UsersGrid.getGrid();
				if (grid.getStore().getCount() > 0) {
					if ( grid.getSelectionModel().getSelected() )
					{
						var selected_record = grid.getSelectionModel().getSelected();
						grid.getView().focusRow(grid.getStore().indexOf(selected_record));
					}
					else
					{
						grid.getView().focusRow(0);
						grid.getSelectionModel().selectFirstRow();
					}
				} else {
					var tree = wnd.OrgTreePanel;
					var cur_node = tree.getSelectionModel().getSelectedNode();
					if (cur_node) {
						tree.focus();
						cur_node.select();
						cur_node.getUI().getAnchor().focus();
					}
				}
			}
		}
		switch (mode) {
			case 'add' :
				break;
		}

		getWnd('swUserEditWindow').show(params);
	},
	/**
	 * Восстановление пользователя
	 */ 
	restoreUser: function(block) {
		var grid = this.UsersGrid.getGrid();
		var record = grid.getSelectionModel().getSelected();
		if (!record) {
			return false;
		}
		
		sw.swMsg.show({
			icon: Ext.MessageBox.QUESTION,
			title : lang['podtverjdenie_vosstanovleniya'],
			msg : lang['vyi_deystvitelno_hotite_vosstanovit_polzovatelya']+record.get('login')+'</b>? ',
			buttons : Ext.Msg.YESNO,
			fn : function(buttonId) {
				if (buttonId == 'yes') {
					Ext.Ajax.request({
						url: '/?c=User&m=restoreUser',
						params : {
							user_login : record.get('login')
						},
						callback : function() {
							grid.getStore().removeAll();
							grid.getStore().reload({
								callback: function() {
									if ( grid.getSelectionModel().getSelected() )
									{
										var selected_record = grid.getSelectionModel().getSelected();
										grid.getView().focusRow(grid.getStore().indexOf(selected_record));
									}
									else if (grid.getStore().getCount() > 0)
									{
										grid.getView().focusRow(0);
										grid.getSelectionModel().selectFirstRow();
									}
								}
							});
						}
					});
				}
			}
		});

	},
	blockUsers: function(block) {
		var grid = this.UsersGrid.getGrid();
		var records = grid.getSelectionModel().getSelections();

		if (records.length == 0) {
			return false;
		}

		var pmuser_ids = [];
		for(var i=0; i<records.length; i++) {
			pmuser_ids.push(records[i].get('pmUser_id'));
		}

		Ext.Ajax.request({
			url: '/?c=User&m=blockUsers',
			params: {
				pmUser_Blocked: block,
				pmUser_ids: Ext.util.JSON.encode(pmuser_ids)
			},
			callback: function() {
				grid.getStore().removeAll();
				grid.getStore().reload({
					callback: function() {
						if ( grid.getSelectionModel().getSelected() )
						{
							var selected_record = grid.getSelectionModel().getSelected();
							grid.getView().focusRow(grid.getStore().indexOf(selected_record));
						}
						else if (grid.getStore().getCount() > 0)
						{
							grid.getView().focusRow(0);
							grid.getSelectionModel().selectFirstRow();
						}
					}
				});
			}
		});
	},
	show: function() {
		sw.Promed.swUsersTreeViewWindow.superclass.show.apply(this, arguments);

		var form = this;

		this.UsersGrid.addActions({
			name:'action_isp',
			text: lang['deystviya'],
			iconCls: 'actions16',
			menu: new Ext.menu.Menu({
				items: [{
					id: 'UTVW_RestoreUser',
					text: lang['vosstanovit'],
					handler: function() {
						form.restoreUser(true);
					}
				},'-',{
					id: 'UTVW_BlockUsers',
					text: lang['zablokirovat'],
					handler: function() {
						form.blockUsers(true);
					}
				}, {
					id: 'UTVW_UnblockUsers',
					text: lang['razblokirovat'],
					handler: function() {
						form.blockUsers(false);
					}
				}, {
					id: 'UTVW_UnblockUsers',
					hidden: !isSuperAdmin(),
					text: langs('Копировать в нового пользователя'),
					handler: function() {
						form.doEdit('copy');
					}
				}]
			})
		});

		var tree = this.OrgTreePanel;
		var base_form = this.filtersPanel.getForm();
		tree.root.reload();
		tree.root.expand(false, false, function() {
			tree.root.firstChild.select();
			tree.root.firstChild.getUI().getAnchor().focus();
			base_form.findField('login').focus(true, 300);
		});

		if (base_form.findField('group').getStore().getCount() == 0) {
			base_form.findField('group').getStore().load();
		}
		
		this.UsersGrid.getGrid().getTopToolbar().items.items[0].disable();
		this.UsersGrid.getGrid().getTopToolbar().items.items[1].disable();
		this.UsersGrid.getGrid().getTopToolbar().items.items[2].disable();
		this.doResetFilters();
	},
	doResetFilters: function() {
		this.filtersPanel.getForm().reset();
	},
	doFilter: function(org_id) {
		var base_form = this.filtersPanel.getForm();
		
		if (!Ext.isEmpty(org_id)) {
			this.org_id = org_id;
		}
		
		var Org_id = this.org_id;
		
		if (!Ext.isEmpty(base_form.findField('Org_id').getValue())) {
			Org_id = base_form.findField('Org_id').getValue();
		}
		
		var filters = base_form.getValues();
		filters.start = 0;
		filters.limit = 100;
		filters.org = Org_id;
		
		var combo = base_form.findField('group');
		var store = combo.getStore();
		var row = store.getAt(store.find('Group_id', new RegExp('^' + combo.getValue() + '$')));
		if (row) {
			filters.group = row.data.Group_Name;
		}
		/*if (!this.filtersPanel.isEmpty() && this.org_id != 'all') {
			this.OrgTreePanel.root.firstChild.select();
			return true;
		}*/
		// поиск по удаленным пользователем через фильтр
		if (this.org_id == 'deleted' && !this.filtersPanel.isEmpty()){
			filters.pmUser_deleted = this.UsersGrid.getGrid().getStore().baseParams.pmUser_deleted = 'deleted';
			
			this.UsersGrid.loadData({ globalFilters: filters });
			
		} else {
			filters.pmUser_deleted = this.UsersGrid.getGrid().getStore().baseParams.pmUser_deleted = '';
			this.UsersGrid.getGrid().getStore().removeAll();
			this.UsersGrid.loadData({ globalFilters: filters });
		}
		
	},
	//Функция для передачи данных в фильтр через дерево
	TreeInFilter: function(org_id, typeOrgId){
		var base_form = this.filtersPanel.getForm();
		var groupField = base_form.findField('group');
		switch (org_id){
			case 'farmnetadmin':
				var fillFild = groupField.getStore().findBy(function(rec) { return rec.get('Group_id') == '7'; });
				var fillFildItem = groupField.getStore().getAt(fillFild);
				groupField.setValue(fillFildItem.get('Group_id'));
				break;
			case 'all':
				groupField.setValue('');
				break;
		}
		var org_id_number = parseFloat(org_id);
		if (org_id_number > 0){
			
			var orgField = base_form.findField('Org_id');
			orgField.getStore().load({
				params: {
					Object:'Org',
					Org_id: org_id_number,
					Org_Name:''
				},
				callback: function() {
					orgField.setValue(org_id_number);
					orgField.focus(true, 500);
					orgField.fireEvent('change', orgField);
				}
			});

			if ( isSuperAdmin() ) {
				var typeOrgFild = base_form.findField('OrgType_id');
				var typeOrgfillFild = typeOrgFild.getStore().findBy(function(rec) { return rec.get('OrgType_id') == typeOrgId.parentNode.id; });
				var typeOrgfillFildItem = typeOrgFild.getStore().getAt(typeOrgfillFild);
				typeOrgFild.setValue(typeOrgfillFildItem.get('OrgType_id'));
			}
		}
	},
	deleteUser: function() {
		if (isSuperAdmin() || isOrgAdmin()) { // только для суперадмина и админа ЛПУ (refs #13380, #15065)
			var user_login = this.UsersGrid.getGrid().getSelectionModel().getSelected().data.login;
			var user_groups = this.UsersGrid.getGrid().getSelectionModel().getSelected().data.groups;
			
			var userIsAdmin = (user_groups.toString().indexOf('SuperAdmin') != -1);
			if (userIsAdmin && isOrgAdmin() && !isSuperAdmin()) {
				sw.swMsg.alert(lang['oshibka'], lang['vyi_ne_mojete_udalit_polzovatelya_s_pravami_superadministratora'] );
			}
			
			var grid = this.UsersGrid.getGrid();
			sw.swMsg.show({
				icon: Ext.MessageBox.QUESTION,
				title : lang['podtverjdenie_udaleniya'],
				msg : lang['vyi_deystvitelno_hotite_udalit_polzovatelya']+user_login+'</b>?',
				buttons : Ext.Msg.YESNO,
				fn : function(buttonId) {
					if (buttonId == 'yes') {
						Ext.Ajax.request({
							url : C_USER_DROP,
							params : {
								user_login : user_login
							},
							callback : function() {
								grid.getStore().removeAll();
								grid.getStore().reload({
									callback: function() {
										if ( grid.getSelectionModel().getSelected() )
										{
											var selected_record = grid.getSelectionModel().getSelected();
											grid.getView().focusRow(grid.getStore().indexOf(selected_record));
										}
										else if (grid.getStore().getCount() > 0)
										{
											grid.getView().focusRow(0);
											grid.getSelectionModel().selectFirstRow();
										}
									}
								});
							}
						});
					}
				}
			});
		}
	},
	// Функция вывода меню по клику правой клавиши
	onTreeContextMenu: function(node, e)
	{
		var form = this;
		
		form.ContextMenu.items.items[0].disable();
		form.ContextMenu.items.items[1].disable();
		form.ContextMenu.items.items[2].disable();
			
		if (node.leaf) {
			if (node.id != 'all') {
				form.ContextMenu.items.items[2].enable();
			}
			
			if (node.attributes['object'] && node.attributes['object'] == 'Org') {
				form.ContextMenu.items.items[1].enable();
			}
			if (node.attributes['object'] && node.attributes['object'] == 'OrgDenied') {
				form.ContextMenu.items.items[0].enable();
			}
		}
		
		// На правый клик переходим на выделяемую запись
		if (!form.OrgTreePanel.getSelectionModel().isSelected(node)) 
		{
			form.OrgTreePanel.getSelectionModel().select(node); 
		}
		
		var c = node.getOwnerTree().contextMenu;
		c.contextNode = node;
		c.showAt(e.getXY());
	},
	initComponent : function() {
		var form = this;
		
		this.filtersPanel = new Ext.form.FormPanel(
		{
			xtype: 'form',
			region: 'north',
			labelAlign: 'right',
			layout: 'form',
			autoHeight: true,
			labelWidth: 50,
			bodyStyle:'background:#DFE8F6;',
			border: false,
			keys: 
			[{
				key: Ext.EventObject.ENTER,
				fn: function(e) 
				{
					form.doFilter();
				},
				stopEvent: true
			}],
			items: [{
				listeners: {
					collapse: function(p) {
						form.doLayout();
					},
					expand: function(p) {
						form.doLayout();
					}
				},
				title: lang['najmite_chtobyi_svernut_razvernut_panel_filtrov'],
				xtype: 'fieldset',
				collapsible: true,
				autoHeight: true,
				defaults:{bodyStyle:'background:#DFE8F6;'}, 
				items: 
				[
				{
					layout: 'column',
					defaults:{bodyStyle:'padding-top: 4px; background:#DFE8F6;', border: false}, //
					border: false,	
					items: [{
						layout: 'form',
						width: 300,
						labelWidth: 100,
						items: [{
								xtype: 'textfield',
								anchor: '100%',
								name: 'login',
								fieldLabel: lang['login']
							}
						]
					},
					{
						layout: 'form',
						width: 400,
						labelWidth: 150,
						items: [
							{
								xtype: 'textfieldpmw',
								anchor: '100%',
								name: 'pmUser_surName',
								fieldLabel: lang['familiya']
							}
						]
					}]
				}, {
					layout: 'column',
					defaults:{bodyStyle:'background:#DFE8F6;', border: false}, //
					border: false,
					items: [{
						layout: 'form',
						width: 300,
						labelWidth: 100,
						items: [
							{
								xtype : 'swusersgroupscombo',
								anchor: '100%',
								hiddenName : 'group',
								fieldLabel: lang['gruppa']
							}
						]
					},
					{
						layout: 'form',
						width: 400,
						labelWidth: 150,
						items: [
							{
								xtype: 'textfieldpmw',
								anchor: '100%',
								name: 'pmUser_firName',
								fieldLabel: lang['imya']
							}
						]
					}]
				}, {
					layout: 'column',
					hidden: !isSuperAdmin(),
					defaults:{bodyStyle:'background:#DFE8F6;', border: false}, //
					border: false,
					items: [{
						layout: 'form',
						width: 300,
						labelWidth: 100,
						items: [
							{
								xtype: 'sworgcombo',
								mode: 'remote',
								triggerAction: 'all',
								forceSelection: true,
								minChars: 2,
								displayField: 'Org_Nick',
								fieldLabel: lang['organizatsiya'],
								triggerAction: 'none',
								anchor: '100%',
								hiddenName: 'Org_id',
								onTrigger1Click: function() {
									if(!this.disabled){
										var combo = this;
										getWnd('swOrgSearchWindow').show({
											object: 'org',
											onSelect: function(orgData) {
												if ( orgData.Org_id > 0 ) {
													combo.getStore().load({
														params: {
															Object:'Org',
															Org_id: orgData.Org_id,
															Org_Name:''
														},
														callback: function() {
															combo.setValue(orgData.Org_id);
															combo.focus(true, 500);
															combo.fireEvent('change', combo);
														}
													});
												}

												getWnd('swOrgSearchWindow').hide();
											},
											onClose: function() {combo.focus(true, 200)}
										});
									}
								}
							}
						]
					},
					{
						layout: 'form',
						width: 400,
						labelWidth: 150,
						items: [
							{
								comboSubject: 'OrgType',
								anchor: '100%',
								typeCode: 'int',
								hiddenName: 'OrgType_id',
								fieldLabel: lang['tip_organizatsii'],
								xtype: 'swcommonsprcombo'
							}
						]
					}]
				}, {
					layout: 'column',
					defaults:{bodyStyle:'background:#DFE8F6;', border: false}, //
					border: false,
					items: [{
						layout: 'form',
						width: 300,
						labelWidth: 100,
						items: [
							{
								xtype : 'textfield',
								anchor: '100%',
								name : 'pmUser_desc',
								fieldLabel: lang['opisanie']
							}
						]
					}, {
						layout: 'form',
						width: 400,
						labelWidth: 150,
						items: [
							{
								xtype : 'swcommonsprcombo',
								anchor: '100%',
								comboSubject: 'YesNo',
								hiddenName : 'pmUser_Blocked',
								fieldLabel: lang['zablokirovan']
							}
						]
					}]
				}]
			}]
		});
		
		this.ContextMenu = new Ext.menu.Menu();
		if (isSuperAdmin()) {
			this.ContextMenu.add(
				new Ext.menu.Item({
					text: lang['otkryit_dostup_v_sistemu'],
					iconCls : 'spr-org16',
					handler: function()
					{
						var node = form.OrgTreePanel.getSelectionModel().getSelectedNode();
						var grid = form.UsersGrid.getGrid();
				
						Ext.Ajax.request({
							url: '/?c=Org&m=giveOrgAccess',
							params: {
								Org_id: node.id,
								grant: 2
							},
							callback: function(options, success, response) {
								if (success) {
									var result = Ext.util.JSON.decode(response.responseText);
									if (result.success) {
										var iconEl = node.getUI().rendered ? Ext.get(node.getUI().getIconEl()) : null;
										if (iconEl) {
											iconEl.removeClass('org-denied16');
											iconEl.addClass('spr-org16');
										}
										node.attributes['object'] = 'Org';
									} else if (result.Error_Code && result.Error_Code == 1) {
										var params = {
											action: 'add',
											formMode: 'orgaccess',
											fields: {
												action: 'add',
												org_id: node.id
											},
											onClose: function() {
												// ещё раз пытаемся открыть доступ..
												Ext.Ajax.request({
													url: '/?c=Org&m=giveOrgAccess',
													params: {
														Org_id: node.id,
														grant: 2
													},
													callback: function(options, success, response) {
														if (success) {
															var result = Ext.util.JSON.decode(response.responseText);
															if (result.success) {
																var iconEl = node.getUI().rendered ? Ext.get(node.getUI().getIconEl()) : null;
																if (iconEl) {
																	iconEl.removeClass('org-denied16');
																	iconEl.addClass('spr-org16');
																}
																node.attributes['object'] = 'Org';
															}
														}
													}
												});
												form.doFilter(node.id);
											}
										}
										getWnd('swUserEditWindow').show(params);
									}
								}
							}
						});
					}
				}),
				new Ext.menu.Item({
					text: lang['zakryit_dostup_v_sistemu'],
					iconCls : 'org-denied16',
					handler: function()
					{
						var node = form.OrgTreePanel.getSelectionModel().getSelectedNode();
						
						sw.swMsg.show({
							buttons: Ext.Msg.YESNO,
							fn: function ( buttonId ) {
								if ( buttonId == 'yes' )
								{
									Ext.Ajax.request({
										url : '/?c=Org&m=giveOrgAccess',
										params : {
											Org_id: node.id,
											grant: 1
										},
										callback : function() {													
											var iconEl = node.getUI().rendered ? Ext.get(node.getUI().getIconEl()) : null;
											if (iconEl) {
												iconEl.removeClass('spr-org16');
												iconEl.addClass('org-denied16');
											}
											node.attributes['object'] = 'OrgDenied';
										}
									});
								}
							},
							msg: lang['vyi_uverenyi_chto_namerenyi_zapretit_dostup_k_sisteme_vsem_polzovatelyam']+node.attributes['text']+'?',
							title: lang['podtverjdenie']
						});
					}
				}),
				new Ext.menu.Item({
					text: lang['perekeshirovat_dannyie'],
					iconCls : 'refresh16',
					handler: function()
					{
						var node = form.OrgTreePanel.getSelectionModel().getSelectedNode();
						
						sw.swMsg.show({
							buttons: Ext.Msg.YESNO,
							fn: function ( buttonId ) {
								if ( buttonId == 'yes' )
								{
									form.getLoadMask(lang['podojdite_vyipolnyaetsya_perekeshirovanie_dannyih']).show();
									
									Ext.Ajax.request({
										url : '/?c=User&m=syncLdapAndCacheUserData',
										params : {
											Org_id: node.id
										},
										callback : function() {
											form.getLoadMask().hide();
											form.doFilter(node.id);
										}
									});
								}
							},
							msg: lang['vyi_deystvitelno_jelaete_perekeshirovat_dannyie']+node.attributes['text']+'?',
							title: lang['podtverjdenie']
						});
					}
				})
			);
		}

		this.OrgTreePanel = new Ext.tree.TreePanel({
			title : lang['mo'],
			width : 270,
			split : true,
			region : 'west',
			useArrows : true,
			animate : true,
			enableDD : false,
			contextMenu: form.ContextMenu,
			autoScroll : true,
			border : true,
			rootVisible: false,
			root : {
				text : lang['mo'],
				draggable : false,
				id : 'root',
				expandable : true
			},
			loader : new Ext.tree.TreeLoader({
				dataUrl : C_USER_GETTREE
			}),
			enableKeyEvents : true,
			keys : [{
				key : [Ext.EventObject.TAB],
				fn : function(inp, e) {
					e.stopEvent();

					if (e.browserEvent.stopPropagation)
						e.browserEvent.stopPropagation();
					else
						e.browserEvent.cancelBubble = true;

					if (e.browserEvent.preventDefault)
						e.browserEvent.preventDefault();
					else
						e.browserEvent.returnValue = false;

					e.browserEvent.returnValue = false;
					e.returnValue = false;

					if (Ext.isIE) {
						e.browserEvent.keyCode = 0;
						e.browserEvent.which = 0;
					}

					var current_window = Ext.getCmp('UsersTreeViewWindow');
					var tree = current_window.OrgTreePanel;
					var grid = current_window.UsersGrid.getGrid();
					switch (e.getKey()) {
						case Ext.EventObject.TAB :

							if (grid.getStore().getCount() > 0
									&& grid.getSelectionModel()
											.getSelected()) {
								var selected_record = grid
										.getSelectionModel().getSelected();
								var index = grid.getStore().findBy(function(rec) { return rec.get('login') == selected_record.data.login; });
								grid.getView().focusRow(index);
								if (index == 0) {
									grid.getSelectionModel()
											.selectFirstRow();
								}
							} else if (grid.getStore().getCount() > 0) {
								grid.getSelectionModel().selectFirstRow();
								grid.getView().focusRow(0);
							}
							break;
					}
				},
				stopEvent : true
			}],
			selModel : new Ext.tree.DefaultSelectionModel({
				listeners : {
					'beforeselect' : function(s, node) {
						var grid = form.UsersGrid.getGrid();
						
						if (node.id != 'all') {
							form.doResetFilters();
						}
						
						if (node.id == 'root') {
							grid.getStore().removeAll();
							grid.getTopToolbar().items.items[0].disable();
							grid.getTopToolbar().items.items[1].disable();
							grid.getTopToolbar().items.items[2].disable();
						} else if (node.leaf) {
							form.TreeInFilter(node.id, node);
							form.doFilter(node.id);
							form.UsersGrid.setActionDisabled('action_add', !((isSuperAdmin() || node.id != 'all') && (node.id != 'deleted')) );
						}
					}
				}
			})
		});
		
		if (isSuperAdmin()) {
			this.OrgTreePanel.addListener('contextmenu', form.onTreeContextMenu,this);
		}
		
		this.UsersGrid = new sw.Promed.ViewFrame({
			id: this.id + 'UsersGrid',
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 100,
			anchor: '100%',
			title : lang['polzovateli'],
			paging: true,
			root: 'data',
			totalProperty: 'totalCount',
			region: 'center',
			editformclassname: 'swUserProfileEditWindow',
			tbar: false,
			autoLoadData: false,
			selectionModel: 'multiselect',
			actions: [
				{ name: 'action_add', handler: function(){ this.doEdit('add');}.createDelegate(this) },
				{ name: 'action_edit', handler: function(){ this.doEdit('edit');}.createDelegate(this) },
				{ name: 'action_view', handler: function(){ this.doEdit('view');}.createDelegate(this) },
				{ name: 'action_delete', handler: function(){ this.deleteUser();}.createDelegate(this) },
				{ name: 'action_refresh' },
				{ name: 'action_print' }
			],
			stripeRows: true,
			useEmptyRecord: false,
			stringfields: [
				{ name: 'pmUser_id', type: 'string', header: 'pmUser_id',  hidden: true },
				{ name: 'login', type: 'string', header: lang['login'], width: 150, sort: true },
				{ name: 'PMUser_firName', type: 'string', header: lang['imya'], width: 100 },
				{ name: 'PMUser_surName', type: 'string', header: lang['familiya'], width: 200 },
				{ name: 'groups', type: 'string', header: lang['gruppyi'], width: 150 },
				{ name: 'orgs', type: 'string', header: lang['organizatsii'], width: 150 },
				{ name: 'pmUser_desc', type: 'string', header: lang['opisanie'], id: 'autoexpand' },
				{ name: 'IsMedPersonal', type: 'checkbox', header: lang['vrach'], width: 60 },
				{ name: 'PMUser_Blocked', type: 'checkbox', header: lang['zablokirovan'], width: 60 }
			],
			/*onRowSelect: function(sm,index,record) {
				this.setActionDisabled('action_edit', false);

				var user_groups = record.get('groups');
				// выбранный пользователь - суперадмин
				var userIsAdmin = (user_groups)?(user_groups.toString().indexOf('SuperAdmin') != -1):false; 
				if ((!userIsAdmin && isOrgAdmin()) || isSuperAdmin()) {
					this.setActionDisabled('action_delete', false);
				} else {
					this.setActionDisabled('action_delete', true);
				}
			},*/
			onMultiSelectionChangeAdvanced: function(sm) {
				var records = sm.getSelections();

				if (records.length > 0) {
					var disable_block = true;
					var disable_unblock = true;

					for (var i=0; i<records.length; i++) {
						if (records[i].get('PMUser_Blocked') == 0) {
							disable_block = false;
						} else {
							disable_unblock = false;
						}
						if (!disable_block && !disable_unblock) {
							break;
						}
					}
					// получаем текущую ноду
					var node = form.OrgTreePanel.getSelectionModel().selNode, 
						nodeDeleted = (node && node.id == 'deleted');
					
					Ext.getCmp('UTVW_BlockUsers').setDisabled(disable_block || nodeDeleted);
					Ext.getCmp('UTVW_UnblockUsers').setDisabled(disable_unblock || nodeDeleted);
					Ext.getCmp('UTVW_RestoreUser').setDisabled(!nodeDeleted);
					
					this.setActionDisabled('action_delete', nodeDeleted);
					
					if (records.length == 1) {
						if (!nodeDeleted) {
							var user_groups = records[0].get('groups');
							// выбранный пользователь - суперадмин
							var userIsAdmin = (user_groups)?(user_groups.toString().indexOf('SuperAdmin') != -1):false;
							if ((!userIsAdmin && isOrgAdmin()) || isSuperAdmin()) {
								this.setActionDisabled('action_delete', false);
								Ext.getCmp('UTVW_RestoreUser').setDisabled(!nodeDeleted);
							} else {
								this.setActionDisabled('action_delete', true);
							}
						}
						this.setActionDisabled('action_edit', nodeDeleted || isUserGroup('MIACSuperAdmin'));
					} else {
						this.setActionDisabled('action_delete', true);
						Ext.getCmp('UTVW_RestoreUser').setDisabled(true);
					}
				}
			},
			dataUrl: C_USER_LIST
		});
		
		Ext.apply(this, {
			items : [
				this.OrgTreePanel,
				{
					region: 'center',
					xtype: 'panel',
					layout: 'border',
					items: [
						this.filtersPanel,
						this.UsersGrid
					]
				}
			],
			buttons : [{
				text: BTN_FILTER,
				tabIndex: TABINDEX_UTVW + 10,
				handler: function() {
					form.doFilter();
				},
				iconCls: 'search16'
			}, 
			{
				text: BTN_RESETFILTER,
				tabIndex: TABINDEX_UTVW + 11,
				handler: function() {
					form.doResetFilters();
					form.doFilter();
				},
				iconCls: 'resetsearch16'
			}, 
			'-',
			HelpButton(this, TABINDEX_UTVW + 12), 
			{
				text : BTN_FRMCLOSE,
				tabIndex: TABINDEX_UTVW + 13,
				iconCls : 'close16',
				handler : function() {
					this.ownerCt.hide();
				}
			}],
			enableKeyEvents : true,
			keys : [{
						alt : true,
						fn : function(inp, e) {
							Ext.getCmp('UsersTreeViewWindow').hide();
						},
						key : [Ext.EventObject.P],
						stopEvent : true
					}, {
						key : [Ext.EventObject.INSERT],
						fn : function(inp, e) {
							e.stopEvent();

							if (e.browserEvent.stopPropagation)
								e.browserEvent.stopPropagation();
							else
								e.browserEvent.cancelBubble = true;

							if (e.browserEvent.preventDefault)
								e.browserEvent.preventDefault();
							else
								e.browserEvent.returnValue = false;

							e.browserEvent.returnValue = false;
							e.returnValue = false;

							if (Ext.isIE) {
								e.browserEvent.keyCode = 0;
								e.browserEvent.which = 0;
							}

							var current_window = Ext
									.getCmp('UsersTreeViewWindow');
							switch (e.getKey()) {
								case Ext.EventObject.INSERT :
									current_window.doEdit('add');
									break;
							}
						},
						stopEvent : true
					}]
		});
		sw.Promed.swUsersTreeViewWindow.superclass.initComponent.apply(this,
				arguments);
	}
});