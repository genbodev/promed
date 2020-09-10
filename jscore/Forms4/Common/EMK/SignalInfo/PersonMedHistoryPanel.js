/**
 * Панель анамнеза жизни
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
Ext6.define('common.EMK.SignalInfo.PersonMedHistoryPanel', {
	extend: 'swPanel',
	title: 'АНАМНЕЗ ЖИЗНИ',
	allTimeExpandable: false,
    btnAddClickEnable: true,
    collapseOnOnlyTitle: true,
    onBtnAddClick: function(){
        this.openPersonMedHistoryEditWindow('add');
    },
	collapsed: true,
	setParams: function(params) {
		var me = this;

		me.Person_id = params.Person_id;
		me.Server_id = params.Server_id;
		me.loaded = false;

		if (!me.collapsed) {
			me.load();
		}
	},
	loaded: false,
	listeners: {
		'expand': function() {
			if (!this.loaded) {
				this.load();
			}
		}
	},
	load: function() {
		var me = this;
		this.loaded = true;
		this.PersonMedHistoryGrid.getStore().load({
			params: {
				Person_id: me.Person_id
			}
		});
	},
	deletePersonMedHistory: function() {
		var me = this;

		var PersonMedHistory_id = me.PersonMedHistoryGrid.recordMenu.PersonMedHistory_id;
		if (PersonMedHistory_id) {
			checkDeleteRecord({
				callback: function () {
					me.mask('Удаление записи...');
					Ext6.Ajax.request({
						url: '/?c=PersonMedHistory&m=deletePersonMedHistory',
						params: {
							PersonMedHistory_id: PersonMedHistory_id
						},
						callback: function () {
							me.unmask();
							me.load();
						}
					})
				}
			});
		}
	},
	openPersonMedHistoryEditWindow: function(action) {
		var me = this;
		var formParams = new Object();

		if ( action == 'add' ) {
			formParams.PersonMedHistory_id = 0;
		} else {
			var PersonMedHistory_id = me.PersonMedHistoryGrid.recordMenu.PersonMedHistory_id;
			if (!PersonMedHistory_id) {
				return false;
			}

			formParams.PersonMedHistory_id = PersonMedHistory_id;
		}

		getWnd('swPersonMedHistoryEditWindow').show({
			action: action,
			PersonMedHistory_id: formParams.PersonMedHistory_id,
			callback: function() {
				me.load();
			}.createDelegate(this),
			Person_id: me.Person_id
		});
	},
	initComponent: function() {
		var me = this;

		this.PersonMedHistoryGrid = Ext6.create('Ext6.grid.Panel', {
			border: false,
			recordMenu: Ext6.create('Ext6.menu.Menu', {
				items: [{
					text: 'Редактировать',
					handler: function() {
						me.openPersonMedHistoryEditWindow('edit');
					}
				}, {
					text: 'Удалить запись',
					handler: function() {
						me.deletePersonMedHistory();
					}
				}]
			}),
			showRecordMenu: function(el, PersonMedHistory_id) {
				this.recordMenu.PersonMedHistory_id = PersonMedHistory_id;
				this.recordMenu.showBy(el);
			},
			userCls: 'person-med-history',
			columns: [{
				flex: 1,
				minWidth: 100,
				tdCls: 'padLeft20',
				dataIndex: 'PersonMedHistory_Descr'
			}, {
				width: 120,
				dataIndex: 'PersonMedHistory_setDate'
			}, {
				width: 120,
				dataIndex: 'pmUser_Name'
			}, {
				width: 40,
				dataIndex: 'PersonMedHistory_Action',
				renderer: function (value, metaData, record) {
					return "<div class='x6-tool-threedots' onclick='Ext6.getCmp(\"" + me.PersonMedHistoryGrid.id + "\").showRecordMenu(this, " + record.get('PersonMedHistory_id') + ");'></div>";
				}
			}],
			disableSelection: true,
			store: Ext6.create('Ext6.data.Store', {
				fields: [
					{ name: 'PersonMedHistory_id', type: 'int' },
					{ name: 'PersonMedHistory_setDate', type: 'string' },
					{ name: 'PersonMedHistory_Descr', type: 'string' },
					{ name: 'PersonMedHistory_Text', type: 'string' },
					{ name: 'pmUser_Name', type: 'string' }
				],
				listeners: {
					'load': function(store, records) {
						me.setTitleCounter(records.length);
					}
				},
				proxy: {
					type: 'ajax',
					actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
					url: '/?c=PersonMedHistory&m=loadPersonMedHistoryPanel',
					reader: {
						type: 'json',
						rootProperty: 'data',
						totalProperty: 'totalCount'
					}
				},
				sorters: [
					'PersonMedHistory_id'
				]
			})
		});

		Ext6.apply(this, {
			items: [
				this.PersonMedHistoryGrid
			],
			tools: [{
				type: 'plusmenu',
				tooltip: 'Добавить',
				minWidth: 23,
				handler: function() {
					me.openPersonMedHistoryEditWindow('add');
				}
			}]
		});

		this.callParent(arguments);
	}
});