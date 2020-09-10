/**
 * Панель аллергологического анамнеза
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
Ext6.define('common.EMK.SignalInfo.PersonAllergicReactionPanel', {
	extend: 'swPanel',
	btnAddClickEnable: true,
	collapseOnOnlyTitle: true,
	allTimeExpandable: false,
	onBtnAddClick: function(){
		this.openPersonAllergicReactionEditWindow('add');
	},
	title: 'АЛЛЕРГОЛОГИЧЕСКИЙ АНАМНЕЗ',
	collapseOnOnlyTitle: true,
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
		this.PersonAllergicReactionGrid.getStore().load({
			params: {
				Person_id: me.Person_id
			}
		});
	},
	deletePersonAllergicReaction: function() {
		var me = this;

		var PersonAllergicReaction_id = me.PersonAllergicReactionGrid.recordMenu.PersonAllergicReaction_id;
		if (PersonAllergicReaction_id) {
			checkDeleteRecord({
				callback: function () {
					me.mask('Удаление записи...');
					Ext6.Ajax.request({
						url: '/?c=PersonAllergicReaction&m=deletePersonAllergicReaction',
						params: {
							PersonAllergicReaction_id: PersonAllergicReaction_id
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
	openPersonAllergicReactionEditWindow: function(action) {
		var me = this;
		var formParams = new Object();

		formParams.Person_id = me.Person_id;
		formParams.Server_id = me.Server_id;

		if ( action == 'add' ) {
			formParams.PersonAllergicReaction_id = 0;
		} else {
			var PersonAllergicReaction_id = me.PersonAllergicReactionGrid.recordMenu.PersonAllergicReaction_id;
			if (!PersonAllergicReaction_id) {
				return false;
			}

			formParams.PersonAllergicReaction_id = PersonAllergicReaction_id;
		}

		getWnd('swPersonAllergicReactionEditWindow').show({
			action: action,
			PersonAllergicReaction_id: formParams.PersonAllergicReaction_id,
			callback: function(data) {
				if ( !data || !data.personAllergicReactionData )
				{
					return false;
				}

				me.load();
			}.createDelegate(this),
			formParams: formParams
		});
	},
	initComponent: function() {
		var me = this;

		this.PersonAllergicReactionGrid = Ext6.create('Ext6.grid.Panel', {
			border: true,
			recordMenu: Ext6.create('Ext6.menu.Menu', {
				items: [{
					text: 'Редактировать',
					handler: function() {
						me.openPersonAllergicReactionEditWindow('edit');
					}
				}, {
					text: 'Удалить запись',
					handler: function() {
						me.deletePersonAllergicReaction();
					}
				}]
			}),
			showRecordMenu: function(el, PersonAllergicReaction_id) {
				this.recordMenu.PersonAllergicReaction_id = PersonAllergicReaction_id;
				this.recordMenu.showBy(el);
			},
			cls: 'EmkGrid',
			padding: 10,
			viewConfig: {
				minHeight: 33
			},
			columns: [{
				width: 120,
				header: 'Аллерген',
				dataIndex: 'PersonAllergicReaction_Kind'
			}, {
				width: 120,
				header: 'Тип реакции',
				dataIndex: 'AllergicReactionType_Name'
			}, {
				width: 120,
				flex: 1,
				header: 'Характер реакции',
				dataIndex: 'AllergicReactionLevel_Name'
			}, {
				width: 120,
				header: 'Дата возникновения',
				dataIndex: 'PersonAllergicReaction_setDate'
			}, {
				width: 40,
				dataIndex: 'PersonAllergicReaction_Action',
				renderer: function (value, metaData, record) {
					return "<div class='x6-tool-threedots' onclick='Ext6.getCmp(\"" + me.PersonAllergicReactionGrid.id + "\").showRecordMenu(this, " + record.get('PersonAllergicReaction_id') + ");'></div>";
				}
			}],
			disableSelection: true,
			store: Ext6.create('Ext6.data.Store', {
				fields: [
					{ name: 'PersonAllergicReaction_id', type: 'int' },
					{ name: 'PersonAllergicReaction_Kind', type: 'string' },
					{ name: 'AllergicReactionType_Name', type: 'string' },
					{ name: 'AllergicReactionLevel_Name', type: 'string' },
					{ name: 'PersonAllergicReaction_setDate', type: 'string' }
				],
				listeners: {
					'load': function(store, records) {
						me.setTitleCounter(records.length);
					}
				},
				proxy: {
					type: 'ajax',
					actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
					url: '/?c=PersonAllergicReaction&m=loadPersonAllergicReaction',
					reader: {
						type: 'json',
						rootProperty: 'data',
						totalProperty: 'totalCount'
					}
				},
				sorters: [
					'PersonAllergicReaction_id'
				]
			})
		});

		Ext6.apply(this, {
			items: [
				this.PersonAllergicReactionGrid
			],
			tools: [{
				type: 'plusmenu',
				tooltip: 'Добавить',
				minWidth: 23,
				handler: function() {
					me.openPersonAllergicReactionEditWindow('add');
				}
			}]
		});

		this.callParent(arguments);
	}
});