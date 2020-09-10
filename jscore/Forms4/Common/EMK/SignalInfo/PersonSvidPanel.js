/**
 * Панель свидетельств
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
Ext6.define('common.EMK.SignalInfo.PersonSvidPanel', {
	requires: [
		'sw.frames.EMD.swEMDPanel'
	],
	extend: 'swPanel',
	title: 'СВИДЕТЕЛЬСТВА',
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
		this.PersonSvidGrid.getStore().load({
			params: {
				Person_id: me.Person_id
			}
		});
	},
	deletePersonSvid: function() {
		var me = this;

		var PersonSvid_id = me.PersonSvidGrid.recordMenu.PersonSvid_id;
		if (PersonSvid_id) {
			checkDeleteRecord({
				callback: function () {
					me.mask('Удаление записи...');
					Ext6.Ajax.request({
						url: '/?c=PersonSvid&m=deletePersonSvid',
						params: {
							PersonSvid_id: PersonSvid_id
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

		this.PersonSvidGrid = Ext6.create('Ext6.grid.Panel', {
			border: true,
			cls: 'EmkGrid',
			padding: 10,
			viewConfig: {
				minHeight: 33
			},
			columns: [{
				width: 120,
				header: 'Тип свидетельства',
				dataIndex: 'PersonSvidType_Name'
			}, {
				width: 120,
				header: 'Серия',
				dataIndex: 'PersonSvid_Ser'
			}, {
				width: 120,
				header: 'Номер',
				dataIndex: 'PersonSvid_Num'
			}, {
				width: 120,
				flex: 1,
				header: 'Дата выдачи',
				dataIndex: 'PersonSvid_GiveDate'
			}, {
				width: 60,
				tdCls: 'vertical-middle',
				xtype: 'widgetcolumn',
				widget: {
					xtype: 'swEMDPanel',
					bind: {
						EMDRegistry_ObjectName: '{record.PersonSvid_Object}',
						EMDRegistry_ObjectID: '{record.PersonSvid_id}',
						IsSigned: '{record.BirthSvid_IsSigned}',
						Hidden: '{record.SignHidden}'
					}
				}
			}],
			disableSelection: true,
			store: Ext6.create('Ext6.data.Store', {
				fields: [
					{ name: 'PersonSvid_id', type: 'int' },
					{
						name: 'SignHidden',
						type: 'boolean',
						convert: function(val, row) {
							if (row.get('PersonSvid_Object') && row.get('PersonSvid_Object') == 'BirthSvid') {
								return false;
							} else {
								return true;
							}
						}
					},
					{ name: 'PersonSvid_Object', type: 'string' },
					{ name: 'PersonSvid_IsSigned', type: 'int' },
					{ name: 'PersonSvidType_Name', type: 'string' },
					{ name: 'PersonSvid_Ser', type: 'string' },
					{ name: 'PersonSvid_Num', type: 'string' },
					{ name: 'PersonSvid_GiveDate', type: 'string' }
				],
				listeners: {
					'load': function(store, records) {
						me.setTitleCounter(records.length);
					}
				},
				proxy: {
					type: 'ajax',
					actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
					url: '/?c=Person&m=loadPersonSvidPanel',
					reader: {
						type: 'json',
						rootProperty: 'data',
						totalProperty: 'totalCount'
					}
				},
				sorters: [
					'PersonSvid_id'
				]
			})
		});

		Ext6.apply(this, {
			items: [
				this.PersonSvidGrid
			]
		});

		this.callParent(arguments);
	}
});