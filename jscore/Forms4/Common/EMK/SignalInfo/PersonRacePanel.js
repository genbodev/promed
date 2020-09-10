/**
 * Панель расы
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
Ext6.define('common.EMK.SignalInfo.PersonRacePanel', {
	extend: 'swPanel',
	title: 'РАСА',
	allTimeExpandable: false,
	btnAddClickEnable: true,
	collapseOnOnlyTitle: true,
	onBtnAddClick: function(){
		this.openPersonRaceEditWindow('add');
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
		this.PersonRaceGrid.getStore().load({
			params: {
				Person_id: me.Person_id
			}
		});
	},
	openPersonRaceEditWindow: function(action) {
		var me = this;
		var formParams = new Object();

		if ( action == 'add' ) {
			formParams.PersonRace_id = 0;
			formParams.Server_id = me.Server_id;
		} else {
			var PersonRace_id = me.PersonRaceGrid.recordMenu.PersonRace_id;
			if (!PersonRace_id) {
				return false;
			}
			formParams.RaceType_id = me.PersonRaceGrid.getStore().data.items[0].get('RaceType_id');
			formParams.PersonRace_id = PersonRace_id;
		}
		formParams.Person_id = me.Person_id;

		getWnd('swPersonRaceEditWindow').show({
			action: action,
			PersonRace_id: formParams.PersonRace_id,
			callback: function(data) {
				if ( !data || !data.personRaceData ) return false;
				me.load();
			}.createDelegate(this),
			formParams: formParams,
		});
	},
	initComponent: function() {
		var me = this;

		this.PersonRaceGrid = Ext6.create('Ext6.grid.Panel', {
			border: true,
			viewConfig: {
				minHeight: 33
			},
			recordMenu: Ext6.create('Ext6.menu.Menu', {
				items: [{
					text: 'Редактировать',
					handler: function() {
						me.openPersonRaceEditWindow('edit');
					}
				},  {
					text: 'Удалить запись',
					handler: function() {
						me.deletePersonRace();
					}
				}]
			}),
			showRecordMenu: function(el, PersonRace_id) {
				this.recordMenu.PersonRace_id = PersonRace_id;
				this.recordMenu.showBy(el);
			},
			cls: 'EmkGrid',
			padding: 10,
			columns: [{
				flex: 1,
				header: 'Раса',
				dataIndex: 'RaceType_Name'
			}, {
				width: 120,
				header: 'Дата',
				dataIndex: 'PersonRace_setDT',
				renderer: function (value) {
					return value ? value.format('d.m.Y') : '';
				}
			}, {
				width: 40,
				dataIndex: 'PersonRace_Action',
				renderer: function (value, metaData, record) {
					return "<div class='x6-tool-threedots' onclick='Ext6.getCmp(\"" + me.PersonRaceGrid.id + "\").showRecordMenu(this, " + record.get('PersonRace_id') + ");'></div>";
				}
			}],
			disableSelection: true,
			store: Ext6.create('Ext6.data.Store', {
				fields: [
					{ name: 'PersonRace_id', type: 'int' },
					{ name: 'RaceType_id', type: 'int' },
					{ name: 'RaceType_Name', type: 'string' },
					{ name: 'PersonRace_setDT', type: 'date', dateFormat: 'd.m.Y' }
				],
				listeners: {
					'load': function(store, records) {
						me.setTitleCounter(records.length);
					}
				},
				proxy: {
					type: 'ajax',
					actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
					url: '/?c=PersonRace&m=loadGrid',
					reader: {
						type: 'json',
						rootProperty: 'data',
						totalProperty: 'totalCount'
					}
				},
				sorters: [
					'PersonRace_id'
				]
			})
		});

		Ext6.apply(this, {
			items: [
				this.PersonRaceGrid
			],
			tools: [{
				type: 'plusmenu',
				tooltip: 'Добавить',
				minWidth: 23,
				handler: function() {
					me.openPersonRaceEditWindow('add');
				}
			}]
		});

		this.callParent(arguments);
	},
	deletePersonRace: function() {
		var me = this;

		var PersonRace_id = me.PersonRaceGrid.recordMenu.PersonRace_id;
		if (PersonRace_id) {
			checkDeleteRecord({
				callback: function () {
					me.mask('Удаление записи...');
					Ext6.Ajax.request({
						url: '/?c=PersonRace&m=delete',
						params: {
							PersonRace_id: PersonRace_id
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
});
