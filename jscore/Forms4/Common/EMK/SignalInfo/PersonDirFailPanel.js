/**
 * Панель отменённых направлений
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
Ext6.define('common.EMK.SignalInfo.PersonDirFailPanel', {
	extend: 'swPanel',
	title: 'СПИСОК ОТМЕНЁННЫХ НАПРАВЛЕНИЙ',
	allTimeExpandable: false,
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
		this.PersonDirFailGrid.getStore().load({
			params: {
				Person_id: me.Person_id
			}
		});
	},
	deletePersonDirFail: function() {
		var me = this;

		var PersonDirFail_id = me.PersonDirFailGrid.recordMenu.PersonDirFail_id;
		if (PersonDirFail_id) {
			checkDeleteRecord({
				callback: function () {
					me.mask('Удаление записи...');
					Ext6.Ajax.request({
						url: '/?c=PersonDirFail&m=deletePersonDirFail',
						params: {
							PersonDirFail_id: PersonDirFail_id
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
	initComponent: function() {
		var me = this;

		this.PersonDirFailGrid = Ext6.create('Ext6.grid.Panel', {
			border: true,
			cls: 'EmkGrid',
			padding: 10,
			viewConfig: {
				minHeight: 33
			},
			columns: [{
				width: 120,
				header: 'Дата создания',
				dataIndex: 'EvnDirection_setDate'
			}, {
				width: 120,
				header: 'Врач, создавший направление',
				dataIndex: 'Person_setFio'
			}, {
				width: 120,
				header: 'Дата отклонения',
				dataIndex: 'EvnDirection_failDate'
			}, {
				width: 120,
				header: 'Причина отклонения',
				dataIndex: 'FailCause_Name'
			}, {
				width: 120,
				flex: 1,
				header: 'Врач, отклонивший направление',
				dataIndex: 'Person_failFio'
			}],
			disableSelection: true,
			store: Ext6.create('Ext6.data.Store', {
				fields: [
					{ name: 'EvnDirection_id', type: 'int' },
					{ name: 'EvnDirection_setDate', type: 'string' },
					{ name: 'Person_setFio', type: 'string' },
					{ name: 'EvnDirection_failDate', type: 'string' },
					{ name: 'FailCause_Name', type: 'string' },
					{ name: 'Person_failFio', type: 'string' }
				],
				listeners: {
					'load': function(store, records) {
						me.setTitleCounter(records.length);
					}
				},
				proxy: {
					type: 'ajax',
					actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
					url: '/?c=Person&m=loadPersonDirFailPanel',
					reader: {
						type: 'json',
						rootProperty: 'data',
						totalProperty: 'totalCount'
					}
				},
				sorters: [
					'PersonDirFail_id'
				]
			})
		});

		Ext6.apply(this, {
			items: [
				this.PersonDirFailGrid
			]
		});

		this.callParent(arguments);
	}
});