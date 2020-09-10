/**
 * Панель списка ЛС, заявленных в рамках ЛЛО
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
Ext6.define('common.EMK.SignalInfo.PersonDrugRequestPanel', {
	extend: 'swPanel',
	collapseOnOnlyTitle: true,
	allTimeExpandable: false,
	title: 'СПИСОК ЛС, ЗАЯВЛЕННЫХ В РАМКАХ ЛЛО',
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
		this.EvnDrugGrid.getStore().load({
			params: {
				Person_id: me.Person_id
			}
		});
	},
	initComponent: function() {
		var me = this;

		this.EvnDrugGrid = Ext6.create('Ext6.grid.Panel', {
			border: true,
			cls: 'EmkGrid',
			padding: 10,
			columns: [{
				width: 380,
				header: 'ЛС',
				dataIndex: 'ls'
			}, {
				width: 120,
				header: 'Статус заявки',
				dataIndex: 'DrugRequestStatus_Name'
			}, {
				width: 120,
				header: 'Период заявки с',
				dataIndex: 'DrugRequestPeriod_begDate'
			}, {
				width: 120,
				header: 'по',
				dataIndex: 'DrugRequestPeriod_endDate'
			}, {
				width: 250,
				header: 'Выписал врач ФИО',
				dataIndex: 'FIO'
			}, {
				width: 80,
				header: 'Заявлено',
				dataIndex: 'DrugRequestPersonOrder_OrdKolvo'
			}, {
				width: 110,
				header: 'Использовано',
				dataIndex: 'DrugRequestPersonOrder_Kolvo'
			}, {
				width: 80,
				header: 'Остаток',
				dataIndex: 'ostatok'
			}],
			disableSelection: true,
			store: Ext6.create('Ext6.data.Store', {
				fields: [
					{ name: 'ls', type: 'string' },
					{ name: 'DrugRequestPeriod_begDate', type: 'string' },
					{ name: 'DrugRequestPeriod_endDate', type: 'string' },
					{ name: 'FIO', type: 'string' },
					{ name: 'DrugRequestPersonOrder_OrdKolvo', type: 'int' },
					{ name: 'DrugRequestPersonOrder_Kolvo', type: 'int' },
					{ name: 'ostatok', type: 'int' }
				],
				listeners: {
					'load': function(store, records) {
						if(records)
							me.setTitleCounter(records.length);
					}
				},
				proxy: {
					type: 'ajax',
					actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
					url: '/?c=EvnRecept&m=loadPersonDrugRequestPanel',
					reader: {
						type: 'json',
						rootProperty: 'data',
						totalProperty: 'totalCount'
					}
				},
				sorters: [
					'EvnDiag_id'
				]
			})
		});

		Ext6.apply(this, {
			items: [
				this.EvnDrugGrid
			]
		});

		this.callParent(arguments);
	}
});