/**
 * Панель онкоскрининга
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Polka
 * @access       public
 * @copyright    Copyright (c) 2018 Swan Ltd.
 *
 */
Ext6.define('common.EMK.RepositoryObservPanel', {
	extend: 'swPanel',
	title: 'АНКЕТИРОВАНИЕ ПАЦИЕНТА С ПОДОЗРЕНИЕМ НА COVID-19',
	allTimeExpandable: false,
	collapsed: true,
	collapseOnOnlyTitle: true,
	// Добавляем кнопку "добавить" в header и исполняемую функцию
	btnAddClickEnable: true,
	onBtnAddClick: function() {
		this.openRepositoryObservEditWindow('add');
	},
	setParams: function(params) {
		var me = this;
		me.Evn_id = params.Evn_id;
		me.EvnClass_id = params.EvnClass_id;
		me.ownerPanel = params.ownerPanel;
		me.userMedStaffFact = params.userMedStaffFact;
		me.Person_id = params.Person_id;
		me.Server_id = params.Server_id;
		me.PersonEvn_id = params.PersonEvn_id;
		me.loaded = false;

		if (!me.collapsed) {
			me.load();
		}
	},
	listeners: {
		'expand': function() {
			if (!this.loaded) {
				this.load();
			}
		}
	},
	callback: Ext6.emptyFn,
	loaded: false,
	load: function() {
		var me = this;
		me.loaded = true;
		this.RepositoryObservGrid.getStore().load({
			params: {
				Evn_id: me.Evn_id
			}
		});
	},
	deleteRepositoryObserv: function() {
		var me = this;

		var RepositoryObserv_id = me.RepositoryObservGrid.recordMenu.RepositoryObserv_id;
		var record = this.RepositoryObservGrid.getStore().findRecord('RepositoryObserv_id', RepositoryObserv_id);

		if (!record) {
			return false;
		}

		if (RepositoryObserv_id) {
			checkDeleteRecord({
				callback: function() {
					me.mask('Удаление...');
					Ext6.Ajax.request({
						url: '/?c=RepositoryObserv&m=delete',
						params: {
							RepositoryObserv_id: RepositoryObserv_id
						},
						callback: function() {
							me.unmask();
							me.load();
						}
					})
				}
			}, 'наблюдение');
		}
	},
	openRepositoryObservEditWindow: function(action) {
		var me = this;

		var params = {};
		params.action = action;
		params.useCase = 'evnvizitpl';
		params.callback = function() {
			me.load();
		}.createDelegate(this);
		params.Evn_id = me.Evn_id;
		params.MedStaffFact_id = me.userMedStaffFact.MedStaffFact_id;
		params.Person_id = me.Person_id;
		params.parentWin = me.ownerPanel;

		if (action.inlist(['edit', 'view'])) {
			params.RepositoryObserv_id = me.RepositoryObservGrid.recordMenu.RepositoryObserv_id;
			if (!params.RepositoryObserv_id) {
				return false;
			}
		}

		getWnd('swRepositoryObservEditWindow').show(params);
	},
	initComponent: function() {
		var me = this;

		this.RepositoryObservGrid = Ext6.create('Ext6.grid.Panel', {
			border: false,
			recordMenu: Ext6.create('Ext6.menu.Menu', {
				items: [{
					text: 'Редактировать',
					iconCls: 'panicon-edit',
					handler: function() {
						me.openRepositoryObservEditWindow('edit');
					}
				}, {
					text: 'Просмотр',
					iconCls: 'panicon-view',
					handler: function() {
						me.openRepositoryObservEditWindow('view');
					}
				}, {
					text: 'Удалить',
					iconCls: 'panicon-delete',
					handler: function() {
						me.deleteRepositoryObserv();
					}
				}]
			}),
			showRecordMenu: function(el, RepositoryObserv_id) {
				this.recordMenu.RepositoryObserv_id = RepositoryObserv_id;
				this.recordMenu.showBy(el);
			},
			columns: [{
				flex: 1,
				tdCls: 'padLeft20',
				minWidth: 100,
				dataIndex: 'RepositoryObserv_Data',
				renderer: function(value, metaData, record) {
					return 'Дата наблюдения: ' + record.get('RepositoryObserv_setDT') + ', врач: ' + record.get('MedPersonal_FIO')
				}
			}, {
				width: 40,
				dataIndex: 'RepositoryObserv_Action',
				renderer: function(value, metaData, record) {
					return "<div class='x6-tool-threedots' onclick='Ext6.getCmp(\"" + me.RepositoryObservGrid.id + "\").showRecordMenu(this, " + record.get('RepositoryObserv_id') + ");'></div>";
				}
			}],
			disableSelection: true,
			store: Ext6.create('Ext6.data.Store', {
				fields: [
					{name: 'RepositoryObserv_id', type: 'int'},
				],
				listeners: {
					'load': function(store, records) {
						me.setTitleCounter(records.length);
					}
				},
				proxy: {
					type: 'ajax',
					actionMethods: {create: "POST", read: "POST", update: "POST", destroy: "POST"},
					url: '/?c=RepositoryObserv&m=loadList',
					reader: {
						type: 'json',
						rootProperty: 'data',
						totalProperty: 'totalCount'
					}
				},
				sorters: [
					'RepositoryObserv_id'
				]
			})
		});

		Ext6.apply(this, {
			items: [
				this.RepositoryObservGrid
			],
			tools: [{
				type: 'plusmenu',
				tooltip: 'Добавить',
				minWidth: 23,
				handler: function(butt) {
					me.openRepositoryObservEditWindow('add');
				}
			}]
		});

		this.callParent(arguments);
	}
});