/**
* swQueryEvnListWindow - Журнал запросов сторонних МО
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      common
* @access       public
* @copyright    Copyright (c) 2018 Swan Ltd.
* @comment      
*/

/*NO PARSE JSON*/

sw.Promed.swQueryEvnListWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swQueryEvnListWindow',
	objectSrc: '/jscore/Forms/Common/swQueryEvnListWindow.js',

	buttonAlign: 'left',
	closeAction: 'hide',
	layout: 'border',
	listeners: {
		'hide': function() {
			this.onHide();
		}
	},
	title: 'Журнал запросов',
	draggable: true,
	id: 'swQueryEvnListWindow',
	width: 900,
	height: 500,
	modal: true,
	plain: true,
	resizable: false,
	//maximized: true,
	//входные параметры
	action: null,
	onSelect: Ext.emptyFn,
	onHide: Ext.emptyFn,
	doLoad: function(userType) {
		if (!userType) userType = this.TabPanel.getActiveTab().id.replace('Panel','');
		var win = this,
			grid = win.findById('viewFrame'+userType),
			QueryEvnUserType_id = null;
			
		if (!grid) return false;
		grid = grid.getGrid()
			
		switch(userType) {
			case 'Input': QueryEvnUserType_id = 2; break;
			case 'Output': QueryEvnUserType_id = 1; break;
			case 'Control': QueryEvnUserType_id = 3; break;
		}
		
		if (userType == 'Control') {
			this.TopToolbar.items.items[7].show();
			this.TopToolbar.items.items[8].show();
		} else {
			this.TopToolbar.items.items[7].hide();
			this.TopToolbar.items.items[8].hide();
		}
		
		this.TopToolbar.items.items[3].setDisabled(userType != 'Output');
		
		var params = {
			QueryEvnUserType_id: QueryEvnUserType_id
		};
		params.start = 0;
		params.limit = 30;
		
        params = Object.assign(params, this['filterRow'+userType].getFilters());
		
		params.onlyMy = (userType != 'Control' || this.TopToolbar.items.items[7].pressed) ? 1 : null;
		
		grid.getStore().load({params: params});
	},
	showHistory: function() {
		var win = this,
			grid = this.TabPanel.getActiveTab().items.items[0].getGrid(),
			record = grid.getSelectionModel().getSelected();
		
		if (!record || !record.get('QueryEvn_id')) return false;
		
		getWnd('swQueryEvnHistoryWindow').show({QueryEvn_id: record.get('QueryEvn_id')});
	},
	doAdd: function() {
		var win = this;
		getWnd('swQueryEvnEditWindow').show({
			callback: function() {
				win.doLoad();
			}
		});
	},
	doDelete: function() {
		var win = this,
			grid = this.TabPanel.getActiveTab().items.items[0].getGrid(),
			record = grid.getSelectionModel().getSelected();
		
		if (!record || !record.get('QueryEvn_id')) return false;
		
		sw.swMsg.show({
			icon: Ext.MessageBox.QUESTION,
			msg: 'Удалить выбранную запись?',
			title: langs('Подтверждение'),
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId, text, obj) {
				if ('yes' == buttonId) {
					Ext.Ajax.request({
						callback: function(options, success, response) {
							if (success) {
								grid.getStore().reload();
							}
							else {
								sw.swMsg.alert(langs('Ошибка'), langs('При удалении возникли ошибки'));
							}
						},
						params: {QueryEvn_id: record.get('QueryEvn_id')},
						url: '/?c=QueryEvn&m=delete'
					});
				}
			}
		});
	},
	doSend: function() {
		var win = this,
			grid = this.TabPanel.getActiveTab().items.items[0].getGrid(),
			record = grid.getSelectionModel().getSelected();
		
		if (!record || !record.get('QueryEvn_id')) return false;
		
		Ext.Ajax.request({
			callback: function(options, success, response) {
				if (success) {
					grid.getStore().reload();
				}
				else {
					sw.swMsg.alert(langs('Ошибка'), langs('При отправке возникли ошибки'));
				}
			},
			params: {QueryEvn_id: record.get('QueryEvn_id')},
			url: '/?c=QueryEvn&m=send'
		});
		
	},
	openEditWindow: function(addact) {
		var win = this,
			grid = this.TabPanel.getActiveTab().items.items[0].getGrid(),
			record = grid.getSelectionModel().getSelected();
		
		if (!record || !record.get('QueryEvn_id')) return false;
		
		getWnd('swQueryEvnEditWindow').show({
			QueryEvn_id: record.get('QueryEvn_id'),
			addact: addact,
			callback: function() {
				win.doLoad();
			}
		});
	},
	show: function() {
		sw.Promed.swQueryEvnListWindow.superclass.show.apply(this, arguments);
		if (!arguments[0]) {
			arguments = [{}];
		}
		this.onHide = arguments[0].onHide ||  Ext.emptyFn;
		this.ARMType = arguments[0].ARMType || '';
		
		Ext.getCmp(this.id + 'StatusFilter_idInput').setValue(3);
		Ext.getCmp(this.id + 'StatusFilter_idControl').setValue(3);
		
		if (isUserGroup('QueryEvnResp')) {
			this.TabPanel.setActiveTab(2);
		} else {
			this.TabPanel.setActiveTab(0);
		}
		
		if (!this.TopToolbar.items.items[7].pressed) {
			this.TopToolbar.items.items[7].toggle();
		}
		
		this.TopToolbar.items.items[0].setDisabled(!this.ARMType.inlist(['common', 'stac', 'vk']));
		if (this.ARMType.inlist(['common', 'stac', 'vk'])) {
			this.TabPanel.unhideTabStripItem(1);
		} else {
			this.TabPanel.hideTabStripItem(1);
		}
		if (isUserGroup('QueryEvnResp')) {
			this.TabPanel.unhideTabStripItem(2);
		} else {
			this.TabPanel.hideTabStripItem(2);
		}
	},
	initComponent: function() {
		var win = this;
		this.filterRow = {};

        this.filterRowInput = new Ext.ux.grid.FilterRow({
            id:'filterRowInput',
            fixed: true,
            parId: win.id,
            group: true,
            listeners:  {
                'search': function(params){
                    win.doLoad();
                }
            }
        });

        this.filterRowOutput = new Ext.ux.grid.FilterRow({
            id:'filterRowOutput',
            fixed: true,
            parId: win.id,
            group: true,
            listeners:  {
                'search': function(params){
                    win.doLoad();
                }
            }
        });

        this.filterRowControl = new Ext.ux.grid.FilterRow({
            id:'filterRowControl',
            fixed: true,
            parId: win.id,
            group: true,
            listeners:  {
                'search': function(params){
                    win.doLoad();
                }
            }
        });
		
		this.TopToolbar = new Ext.Toolbar({
			id : win.id+'TopToolbar',
			items:[
				new Ext.Action({name:'add', text: langs('Добавить'), tooltip: langs('Добавить'), iconCls : 'x-btn-text', icon: 'img/icons/add16.png', handler: function(){win.doAdd()}}),
				new Ext.Action({name:'open', text: langs('Открыть'), tooltip: langs('Открыть'), iconCls : 'x-btn-text', icon: 'img/icons/view16.png', handler: function(){win.openEditWindow()}}),
				new Ext.Action({name:'send', text: langs('Отправить'), tooltip: langs('Отправить'), iconCls : 'x-btn-text', icon: 'img/icons/mail-send16.png', handler: function(){win.doSend()}}),
				new Ext.Action({name:'delete', text: langs('Удалить'), tooltip: langs('Удалить'), iconCls : 'x-btn-text', icon: 'img/icons/delete16.png', handler: function(){win.doDelete()}}),
				new Ext.Action({name:'history', text: langs('История'), tooltip: langs('История'), iconCls : 'x-btn-text', icon: 'img/icons/history16.png', handler: function(){win.showHistory()}}),
				new Ext.Action({name:'actions', key: 'actions', text:langs('Действия'), menu: [
					new Ext.Action({name:'collapse_all', text: langs('Изменить исполнителя'), handler: function(){win.openEditWindow('changeExec')}}),
					new Ext.Action({name:'expand_all', text: langs('Изменить ответственного'), handler: function(){win.openEditWindow('changeResp')}}),
				], tooltip: langs('Действия'), iconCls : 'x-btn-text', icon: 'img/icons/actions16.png'}),
				{xtype: 'tbfill'},
				new Ext.Action({name:'showmy', text: langs('Только мои'), toggleGroup: 'showmy', handler: function(){win.doLoad()}}),
				new Ext.Action({name:'showall', text: langs('Все'), toggleGroup: 'showmy', handler: function(){win.doLoad()}}),
			]
		});
		
		this.viewFrameInput = new sw.Promed.ViewFrame({
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 200,
			autoLoadData: false,
			border: false,
            groups:false,
			height: 370,
            gridplugins: [this.filterRowInput],
			dataUrl: '/?c=QueryEvn&m=loadList',
			id: 'viewFrameInput',
			actions: [
				{name:'action_add' },
				{name:'action_edit'},
				{name:'action_view', text: langs('Открыть'), handler: function(){ win.openEditWindow(); } },
				{name:'action_delete', handler: function(){win.doDelete()}}
			],
			pageSize: 30,
			paging: true,
			root: 'data',
			totalProperty: 'totalCount',
			region: 'center',
			stringfields: [
				{ header: 'ID', type: 'int', name: 'QueryEvn_id', key: true },
				{ header: 'QueryEvnStatus_id', type: 'int', name: 'QueryEvnStatus_id', hidden: true },
				{ header: 'Тип запроса', type: 'string', name: 'QueryEvnType_Name', width: 140 },
				{ header: 'Дата запроса', type: 'date', name: 'QueryEvn_Date', width: 90 },
				{ header: 'Пациент', type: 'string', name: 'Person_Fio', id: 'autoexpand', filter: new Ext.form.TextField({
					enableKeyEvents: true,
					name:'Person_Fio'
				})},
				{ header: 'Выполнен', type: 'checkbox', name: 'QueryEvn_Ready', width: 100, filter: new sw.Promed.SwBaseLocalCombo({
					displayField: 'StatusFilter_Name',
					hiddenName: 'StatusFilter_id',
					name: 'StatusFilter_id',
					id: win.id + 'StatusFilter_idInput',
					editable: false,
					store: new Ext.data.SimpleStore({
						key: 'StatusFilter_id',
						autoLoad: true,
						fields:[
							{name: 'StatusFilter_id', type: 'int'},
							{name: 'StatusFilter_Code', type: 'int'},
							{name: 'StatusFilter_Name', type: 'string'}
						],
						data: [
							[1, 1, langs('Показать все')],
							[2, 2, langs('Выполненные')],
							[3, 3, langs('Новые')]
						]
					}),
					listeners: {
						'select': function(combo) {
							win.doLoad();
							setTimeout(function() {
								combo.getStore().clearFilter();
								combo.lastQuery = '';
							}, 100);
						}
					},
					valueField: 'StatusFilter_id',
					enableKeyEvents: true
				})},
				{ header: 'Автор', type: 'string', name: 'pmUser_NameCreat', width: 120 },
				{ header: 'Исполнитель', type: 'string', name: 'pmUser_NameExec', width: 120 },
				{ header: 'Ответственный ', type: 'string', name: 'pmUser_NameResp', width: 120 },
				win.filterRowInput
			],
			toolbar: false,
			onRowSelect: function(sm,rowIdx,record) {
				win.TopToolbar.items.items[5].menu.items.items[0].setDisabled(record.get('QueryEvnStatus_id') != 1);
				win.TopToolbar.items.items[5].menu.items.items[1].setDisabled(true);
				this.getAction('action_send').setDisabled(record.get('QueryEvnStatus_id') != 1);
				win.TopToolbar.items.items[2].setDisabled(record.get('QueryEvnStatus_id') != 1);
			},
			onDblClick: function(grid, rowIdx, colIdx, event) {
				win.openEditWindow();
			},
			onEnter: function() {
				win.openEditWindow();
			},
			listeners: {
				render: function(grid) {
					var menu_actions = grid.ViewContextMenu.items;
					menu_actions.each(function(a) {
						if (!a.text || !a.text.inlist(['Открыть'])) {
							a.hide();
						}
					});
					if ( !grid.getAction('action_send') ) {
						var action_send = {
							name:'action_send',
							text:langs('Отправить'),
							icon: 'img/icons/mail-send16.png',
							handler: function(){ win.doSend() }
						};
						grid.ViewActions[action_send.name] = new Ext.Action(action_send);
						grid.ViewContextMenu.addSeparator();
						grid.ViewContextMenu.add(grid.ViewActions[action_send.name]);
					}
					if ( !grid.getAction('action_history') ) {
						var action_history = {
							name:'action_history',
							text:langs('История'),
							icon: 'img/icons/history16.png',
							handler: function(){ win.showHistory() }
						};
						grid.ViewActions[action_history.name] = new Ext.Action(action_history);
						grid.ViewContextMenu.addSeparator();
						grid.ViewContextMenu.add(grid.ViewActions[action_history.name]);
					}
					if ( !grid.getAction('collapse_all') ) {
						var collapse_all = {
							name:'collapse_all',
							text:langs('Изменить исполнителя'),
							handler: function(){ win.openEditWindow('changeExec') }
						};
						grid.ViewActions[collapse_all.name] = new Ext.Action(collapse_all);
						grid.ViewContextMenu.addSeparator();
						grid.ViewContextMenu.add(grid.ViewActions[collapse_all.name]);
					}
					if ( !grid.getAction('expand_all') ) {
						var expand_all = {
							name:'expand_all',
							text:langs('Изменить ответственного'),
							handler: function(){ win.openEditWindow('changeResp') },
							disabled: true
						};
						grid.ViewActions[expand_all.name] = new Ext.Action(expand_all);
						grid.ViewContextMenu.add(grid.ViewActions[expand_all.name]);
					}
				}.createDelegate(this)
			},
		});
		
		this.viewFrameOutput = new sw.Promed.ViewFrame({
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 200,
			autoLoadData: false,
			border: false,
            groups:false,
			height: 370,
			gridplugins: [this.filterRowOutput],
			dataUrl: '/?c=QueryEvn&m=loadList',
			id: 'viewFrameOutput',
			actions: [
				{name:'action_add' },
				{name:'action_edit'},
				{name:'action_view', text: langs('Открыть'), handler: function(){ win.openEditWindow(); } },
				{name:'action_delete', handler: function(){win.doDelete()}}
			],
			pageSize: 30,
			paging: true,
			root: 'data',
			totalProperty: 'totalCount',
			region: 'center',
			stringfields:  [
				{ header: 'ID', type: 'int', name: 'QueryEvn_id', key: true },
				{ header: 'QueryEvnStatus_id', type: 'int', name: 'QueryEvnStatus_id', hidden: true },
				{ header: 'Тип запроса', type: 'string', name: 'QueryEvnType_Name', width: 140 },
				{ header: 'Дата запроса', type: 'date', name: 'QueryEvn_Date', width: 90 },
				{ header: 'Пациент', type: 'string', name: 'Person_Fio', id: 'autoexpand', filter: new Ext.form.TextField({
					enableKeyEvents: true,
					name:'Person_Fio'
				})},
				{ header: 'Выполнен', type: 'checkbox', name: 'QueryEvn_Ready', width: 100, filter: new sw.Promed.SwBaseLocalCombo({
					displayField: 'StatusFilter_Name',
					hiddenName: 'StatusFilter_id',
					name: 'StatusFilter_id',
					editable: false,
					store: new Ext.data.SimpleStore({
						key: 'StatusFilter_id',
						autoLoad: true,
						fields:[
							{name: 'StatusFilter_id', type: 'int'},
							{name: 'StatusFilter_Code', type: 'int'},
							{name: 'StatusFilter_Name', type: 'string'}
						],
						data: [
							[1, 1, langs('Показать все')],
							[2, 2, langs('Выполненные')],
							[3, 3, langs('Новые')]
						]
					}),
					listeners: {
						'select': function(combo) {
							win.doLoad();
							setTimeout(function() {
								combo.getStore().clearFilter();
								combo.lastQuery = '';
							}, 100);
						}
					},
					valueField: 'StatusFilter_id',
					enableKeyEvents: true
				})},
				{ header: 'Автор', type: 'string', name: 'pmUser_NameCreat', width: 120 },
				{ header: 'Исполнитель', type: 'string', name: 'pmUser_NameExec', width: 120 },
				{ header: 'Ответственный ', type: 'string', name: 'pmUser_NameResp', width: 120 },
				win.filterRowOutput
			],
			toolbar: false,
			onRowSelect: function(sm,rowIdx,record) {
				win.TopToolbar.items.items[5].menu.items.items[0].setDisabled(true);
				win.TopToolbar.items.items[5].menu.items.items[1].setDisabled(true);
				this.getAction('action_send').setDisabled(!Ext.isEmpty(record.get('QueryEvnStatus_id')));
				win.TopToolbar.items.items[2].setDisabled(!Ext.isEmpty(record.get('QueryEvnStatus_id')));
				this.getAction('action_delete_a').setDisabled(record.get('QueryEvnStatus_id') == 1);
				win.TopToolbar.items.items[3].setDisabled(record.get('QueryEvnStatus_id') == 1);
			},
			onDblClick: function(grid, rowIdx, colIdx, event) {
				win.openEditWindow();
			},
			onEnter: function() {
				win.openEditWindow();
			},
			listeners: {
				render: function(grid) {
					var menu_actions = grid.ViewContextMenu.items;
					menu_actions.each(function(a) {
						if (!a.text || !a.text.inlist(['Открыть'])) {
							a.hide();
						}
					});
					if ( !grid.getAction('action_send') ) {
						var action_send = {
							name:'action_send',
							text:langs('Отправить'),
							icon: 'img/icons/mail-send16.png',
							handler: function(){ win.doSend() }
						};
						grid.ViewActions[action_send.name] = new Ext.Action(action_send);
						grid.ViewContextMenu.addSeparator();
						grid.ViewContextMenu.add(grid.ViewActions[action_send.name]);
					}
					if ( !grid.getAction('action_history') ) {
						var action_history = {
							name:'action_history',
							text:langs('История'),
							icon: 'img/icons/history16.png',
							handler: function(){ win.showHistory() }
						};
						grid.ViewActions[action_history.name] = new Ext.Action(action_history);
						grid.ViewContextMenu.addSeparator();
						grid.ViewContextMenu.add(grid.ViewActions[action_history.name]);
					}
					if ( !grid.getAction('action_delete_a') ) {
						var action_delete_a = {
							name:'action_delete_a',
							text:langs('Удалить'),
							icon: 'img/icons/delete16.png',
							handler: function(){ win.doDelete() }
						};
						grid.ViewActions[action_delete_a.name] = new Ext.Action(action_delete_a);
						grid.ViewContextMenu.addSeparator();
						grid.ViewContextMenu.add(grid.ViewActions[action_delete_a.name]);
					}
				}.createDelegate(this)
			},
		});
		
		this.viewFrameControl = new sw.Promed.ViewFrame({
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 200,
			autoLoadData: false,
			border: false,
            groups:false,
			height: 370,
            gridplugins: [this.filterRowControl],
			dataUrl: '/?c=QueryEvn&m=loadList',
			id: 'viewFrameControl',
			actions: [
				{name:'action_add' },
				{name:'action_edit'},
				{name:'action_view', text: langs('Открыть'), handler: function(){ win.openEditWindow(); } },
				{name:'action_delete', handler: function(){win.doDelete()}}
			],
			pageSize: 30,
			paging: true,
			root: 'data',
			totalProperty: 'totalCount',
			region: 'center',
			stringfields:  [
				{ header: 'ID', type: 'int', name: 'QueryEvn_id', key: true },
				{ header: 'QueryEvnStatus_id', type: 'int', name: 'QueryEvnStatus_id', hidden: true },
				{ header: 'Тип запроса', type: 'string', name: 'QueryEvnType_Name', width: 140 },
				{ header: 'Дата запроса', type: 'date', name: 'QueryEvn_Date', width: 90 },
				{ header: 'Пациент', type: 'string', name: 'Person_Fio', id: 'autoexpand', filter: new Ext.form.TextField({
					enableKeyEvents: true,
					name:'Person_Fio'
				})},
				{ header: 'Выполнен', type: 'checkbox', name: 'QueryEvn_Ready', width: 100, filter: new sw.Promed.SwBaseLocalCombo({
					displayField: 'StatusFilter_Name',
					hiddenName: 'StatusFilter_id',
					name: 'StatusFilter_id',
					id: win.id + 'StatusFilter_idControl',
					editable: false,
					store: new Ext.data.SimpleStore({
						key: 'StatusFilter_id',
						autoLoad: true,
						fields:[
							{name: 'StatusFilter_id', type: 'int'},
							{name: 'StatusFilter_Code', type: 'int'},
							{name: 'StatusFilter_Name', type: 'string'}
						],
						data: [
							[1, 1, langs('Показать все')],
							[2, 2, langs('Выполненные')],
							[3, 3, langs('Новые')]
						]
					}),
					listeners: {
						'select': function(combo) {
							win.doLoad();
							setTimeout(function() {
								combo.getStore().clearFilter();
								combo.lastQuery = '';
							}, 100);
						}
					},
					valueField: 'StatusFilter_id',
					enableKeyEvents: true
				})},
				{ header: 'Автор', type: 'string', name: 'pmUser_NameCreat', width: 120 },
				{ header: 'Исполнитель', type: 'string', name: 'pmUser_NameExec', width: 120 },
				{ header: 'Ответственный ', type: 'string', name: 'pmUser_NameResp', width: 120 },
				win.filterRowControl
			],
			toolbar: false,
			onRowSelect: function(sm,rowIdx,record) {
				win.TopToolbar.items.items[5].menu.items.items[0].setDisabled(record.get('QueryEvnStatus_id') != 1);
				win.TopToolbar.items.items[5].menu.items.items[1].setDisabled(record.get('QueryEvnStatus_id') != 1 || !isUserGroup('QueryEvnResp'));
				this.getAction('action_send').setDisabled(record.get('QueryEvnStatus_id') != 1);
				win.TopToolbar.items.items[2].setDisabled(record.get('QueryEvnStatus_id') != 1);
			},
			onDblClick: function(grid, rowIdx, colIdx, event) {
				win.openEditWindow();
			},
			onEnter: function() {
				win.openEditWindow();
			},
			listeners: {
				render: function(grid) {
					var menu_actions = grid.ViewContextMenu.items;
					menu_actions.each(function(a) {
						if (!a.text || !a.text.inlist(['Открыть'])) {
							a.hide();
						}
					});
					if ( !grid.getAction('action_send') ) {
						var action_send = {
							name:'action_send',
							text:langs('Отправить'),
							icon: 'img/icons/mail-send16.png',
							handler: function(){ win.doSend() }
						};
						grid.ViewActions[action_send.name] = new Ext.Action(action_send);
						grid.ViewContextMenu.addSeparator();
						grid.ViewContextMenu.add(grid.ViewActions[action_send.name]);
					}
					if ( !grid.getAction('action_history') ) {
						var action_history = {
							name:'action_history',
							text:langs('История'),
							icon: 'img/icons/history16.png',
							handler: function(){ win.showHistory() }
						};
						grid.ViewActions[action_history.name] = new Ext.Action(action_history);
						grid.ViewContextMenu.addSeparator();
						grid.ViewContextMenu.add(grid.ViewActions[action_history.name]);
					}
					if ( !grid.getAction('collapse_all') ) {
						var collapse_all = {
							name:'collapse_all',
							text:langs('Изменить исполнителя'),
							handler: function(){ win.openEditWindow('changeExec') }
						};
						grid.ViewActions[collapse_all.name] = new Ext.Action(collapse_all);
						grid.ViewContextMenu.addSeparator();
						grid.ViewContextMenu.add(grid.ViewActions[collapse_all.name]);
					}
					if ( !grid.getAction('expand_all') ) {
						var expand_all = {
							name:'expand_all',
							text:langs('Изменить ответственного'),
							handler: function(){ win.openEditWindow('changeResp') }
						};
						grid.ViewActions[expand_all.name] = new Ext.Action(expand_all);
						grid.ViewContextMenu.add(grid.ViewActions[expand_all.name]);
					}
				}.createDelegate(this)
			},
		});
		
		this.TabPanel = new Ext.TabPanel({
			id : win.id+'TabPanel',
			autoScroll: true,
			plain: true,
			activeTab: 0,
			resizeTabs: true,
			region: 'center',
			enableTabScroll: true,
			minTabWidth: 120,
			tabWidth: 'auto',
			border: false,
			style: 'background:#DFE8F6; padding-top: 7px;',
			defaults: {bodyStyle: 'background:#DFE8F6; width:100%;', border: false},
			layoutOnTabChange: true,
			items:[{
				title: 'Входящие',
				id: 'InputPanel',
				listeners: {
					'activate': function() {
						win.doLoad('Input');
					}
				},
				items: [this.viewFrameInput]
			}, {
				title: 'Исходящие',
				id: 'OutputPanel',
				listeners: {
					'activate': function() {
						win.doLoad('Output');
					}
				},
				items: [this.viewFrameOutput]
			}, {
				title: 'Для контроля',
				id: 'ControlPanel',
				listeners: {
					'activate': function() {
						win.doLoad('Control');
					}
				},
				items: [this.viewFrameControl]
			}]
		});
		
		
		this.MainPanel = new Ext.Panel({
			region: 'center',
			border: true,
			autoHeight: true,
			tbar: this.TopToolbar,
			items: [
				this.TabPanel
			]
		});

		Ext.apply(this, {
			buttons: [{
				text: '-'
			},
			HelpButton(this),
			{
				handler: function() {
					win.hide();
				},
				iconCls: 'cancel16',
				text: BTN_FRMCLOSE
			}],
			items: [
				this.MainPanel
			]
		});
		sw.Promed.swQueryEvnListWindow.superclass.initComponent.apply(this, arguments);
	}
});