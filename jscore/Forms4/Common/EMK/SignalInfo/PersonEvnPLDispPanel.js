/**
 * Панель диспансеризаций
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
Ext6.define('common.EMK.SignalInfo.PersonEvnPLDispPanel', {
	extend: 'swPanel',
	title: 'ДИСПАНСЕРИЗАЦИЯ И МЕД. ОСМОТРЫ',
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
		this.PersonEvnPLDispGrid.getStore().load({
			params: {
				Person_id: me.Person_id
			}
		});
	},
	deletePersonEvnPLDisp: function() {
		var me = this;

		var PersonEvnPLDisp_id = me.PersonEvnPLDispGrid.recordMenu.PersonEvnPLDisp_id;
		if (PersonEvnPLDisp_id) {
			checkDeleteRecord({
				callback: function () {
					me.mask('Удаление записи...');
					Ext6.Ajax.request({
						url: '/?c=PersonEvnPLDisp&m=deletePersonEvnPLDisp',
						params: {
							PersonEvnPLDisp_id: PersonEvnPLDisp_id
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

		this.PersonEvnPLDispGrid = Ext6.create('Ext6.grid.Panel', {
			border: true,
			cls: 'EmkGrid',
			padding: 10,
			viewConfig: {
				minHeight: 33
			},
			columns: [{
				width: 120,
				header: 'Тип',
				dataIndex: 'DispClass_Name'
			}, {
				width: 120,
				header: 'Дата начала',
				dataIndex: 'EvnPLDisp_setDate'
			}, {
				width: 120,
				header: 'Дата окончания',
				dataIndex: 'EvnPLDisp_disDate'
			}, {
				width: 120,
				header: 'МО проведения',
				dataIndex: 'Lpu_Nick'
			}, {
				width: 120,
				header: 'Группа здоровья',
				dataIndex: 'HealthKind_Name'
			}, {
				width: 120,
				flex: 1,
				header: 'Диагноз, установленный впервые',
				dataIndex: 'Diag_FullName'
			}],
			disableSelection: true,
			store: Ext6.create('Ext6.data.Store', {
				fields: [
					{ name: 'EvnPLDisp_id', type: 'int' },
					{ name: 'DispClass_Name', type: 'string' },
					{ name: 'EvnPLDisp_setDate', type: 'string' },
					{ name: 'EvnPLDisp_disDate', type: 'string' },
					{ name: 'Lpu_Nick', type: 'string' },
					{ name: 'HealthKind_Name', type: 'string' },
					{ name: 'Diag_FullName', type: 'string' }
				],
				listeners: {
					'load': function(store, records) {
						me.setTitleCounter(records.length);
					}
				},
				proxy: {
					type: 'ajax',
					actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
					url: '/?c=Person&m=loadPersonEvnPLDispPanel',
					reader: {
						type: 'json',
						rootProperty: 'data',
						totalProperty: 'totalCount'
					}
				},
				sorters: [
					'PersonEvnPLDisp_id'
				]
			})
		});

		Ext6.apply(this, {
			items: [
				this.PersonEvnPLDispGrid
			]
		});

		this.callParent(arguments);
	}
});