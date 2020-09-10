/**
 * Панель оперативных вмешательств
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
Ext6.define('common.EMK.SignalInfo.PersonSurgicalPanel', {
	extend: 'swPanel',
	title: 'СПИСОК ОПЕРАТИВНЫХ ВМЕШАТЕЛЬСТВ',
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
		this.PersonSurgicalGrid.getStore().load({
			params: {
				Person_id: me.Person_id
			}
		});
	},
	deletePersonSurgical: function() {
		var me = this;

		var PersonSurgical_id = me.PersonSurgicalGrid.recordMenu.PersonSurgical_id;
		if (PersonSurgical_id) {
			checkDeleteRecord({
				callback: function () {
					me.mask('Удаление записи...');
					Ext6.Ajax.request({
						url: '/?c=PersonSurgical&m=deletePersonSurgical',
						params: {
							PersonSurgical_id: PersonSurgical_id
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

		this.PersonSurgicalGrid = Ext6.create('Ext6.grid.Panel', {
			border: true,
			cls: 'EmkGrid',
			padding: 10,
			viewConfig: {
				minHeight: 33
			},
			columns: [{
				width: 120,
				header: 'Дата',
				dataIndex: 'EvnUslugaOper_setDate'
			}, {
				width: 120,
				header: 'МО',
				dataIndex: 'Lpu_Nick'
			}, {
				width: 120,
				header: 'Код услуги',
				dataIndex: 'Usluga_Code'
			}, {
				width: 120,
				flex: 1,
				header: 'Услуга',
				dataIndex: 'Usluga_Name'
			}],
			disableSelection: true,
			store: Ext6.create('Ext6.data.Store', {
				fields: [
					{ name: 'EvnUslugaOper_id', type: 'int' },
					{ name: 'EvnUslugaOper_setDate', type: 'string' },
					{ name: 'Lpu_Nick', type: 'string' },
					{ name: 'Usluga_Code', type: 'string' },
					{ name: 'Usluga_Name', type: 'string' }
				],
				listeners: {
					'load': function(store, records) {
						me.setTitleCounter(records.length);
					}
				},
				proxy: {
					type: 'ajax',
					actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
					url: '/?c=Person&m=loadPersonSurgicalPanel',
					reader: {
						type: 'json',
						rootProperty: 'data',
						totalProperty: 'totalCount'
					}
				},
				sorters: [
					'PersonSurgical_id'
				]
			})
		});

		Ext6.apply(this, {
			items: [
				this.PersonSurgicalGrid
			]
		});

		this.callParent(arguments);
	}
});