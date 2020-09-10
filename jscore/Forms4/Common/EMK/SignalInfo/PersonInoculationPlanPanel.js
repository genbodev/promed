/**
 * Панель планируемых прививок
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Polka
 * @access       public
 * @copyright    Copyright (c) 2019 Swan Ltd.
 *
 */
Ext6.define('common.EMK.SignalInfo.PersonInoculationPlanPanel', {
	extend: 'swPanel',
	title: 'ПЛАНИРУЕМЫЕ ПРИВИВКИ',
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
		this.PersonInoculationPlanGrid.getStore().load({
			params: {
				Person_id: me.Person_id
			}
		});
	},
	initComponent: function() {
		var me = this;
		
		this.PersonInoculationPlanGrid = Ext6.create('Ext6.grid.Panel', {
			border: true,
			cls: 'EmkGrid',
			padding: 10,
			viewConfig: {
				minHeight: 33
			},
			columns: [{
				width: 120,
				header: 'Статус записи',
				dataIndex: 'StatusName'
			}, {
				width: 120,
				header: 'Дата планирования',
				dataIndex: 'datePlan'
			}, {
				width: 80,
				header: 'Возраст',
				dataIndex: 'Age'
			}, {
				width: 50,
				header: 'Вид',
				dataIndex: 'typeName'
			}, {
				width: 120,
				header: 'Назначение',
				flex: 1,
				dataIndex: 'vaccineTypeName'
			}, {
				width: 50,
				header: 'Дата начала периода планирования',
				dataIndex: 'DateBegin'
			}, {
				width: 120,
				header: 'Дата окончания периода планирования',
				dataIndex: 'DateEnd'
			}],
			disableSelection: true,
			store: Ext6.create('Ext6.data.Store', {
				fields: [
					{ name: 'age', type: 'int' },
					{ name: 'DateVac', type: 'string' },
					{ name: 'VaccineType_Name', type: 'string' },
					{ name: 'Vaccine_Name', type: 'string' },
					{ name: 'Dose', type: 'int' },
					{ name: 'Seria', type: 'string' },
					{ name: 'Period', type: 'string' },
					{ name: 'WayPlace', type: 'string' },
					{ name: 'ReactGeneralDescription', type: 'string' }
				],
				listeners: {
					'load': function(store, records) {
						me.setTitleCounter(records.length);
					}
				},
				proxy: {
					type: 'ajax',
					actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
					url: '/?c=Person&m=loadPersonInoculationPlanPanel',
					reader: {
						type: 'json',
						rootProperty: 'data',
						totalProperty: 'totalCount'
					}
				},
				//~ sorters: [
					//''
				//~ ]
			})
		});

		Ext6.apply(this, {
			items: [
				this.PersonInoculationPlanGrid
			]
		});

		this.callParent(arguments);
	}
});