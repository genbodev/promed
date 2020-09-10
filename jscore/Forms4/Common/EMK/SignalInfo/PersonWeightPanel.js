/**
 * Панель массы тела
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
Ext6.define('common.EMK.SignalInfo.PersonWeightPanel', {
	extend: 'swPanel',
	title: 'МАССА ТЕЛА',
	allTimeExpandable: false,
	btnAddClickEnable: true,
	collapseOnOnlyTitle: true,
	onBtnAddClick: function(){
		this.openPersonWeightEditWindow('add');
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
		this.PersonWeightGrid.getStore().load({
			params: {
				Person_id: me.Person_id
			}
		});
	},
	deletePersonWeight: function() {
		var me = this;

		var PersonWeight_id = me.PersonWeightGrid.recordMenu.PersonWeight_id;
		if (PersonWeight_id) {
			checkDeleteRecord({
				callback: function () {
					me.mask('Удаление записи...');
					Ext6.Ajax.request({
						url: '/?c=PersonWeight&m=deletePersonWeight',
						params: {
							PersonWeight_id: PersonWeight_id
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
	openPersonWeightEditWindow: function(action) {
		var me = this;
		var formParams = new Object();

		if ( action == 'add' ) {
			formParams.PersonWeight_id = 0;
			formParams.Person_id = me.Person_id;
			formParams.Server_id = me.Server_id;
		} else {
			var PersonWeight_id = me.PersonWeightGrid.recordMenu.PersonWeight_id;
			if (!PersonWeight_id) {
				return false;
			}

			formParams.PersonWeight_id = PersonWeight_id;
		}

		getWnd('swPersonWeightEditWindow').show({
			action: action,
			measureTypeExceptions: [ 1, 2 ],
			PersonWeight_id: formParams.PersonWeight_id,
			callback: function(data) {
				if ( !data || !data.personWeightData )
				{
					return false;
				}

				me.load();
			}.createDelegate(this),
			formParams: formParams,
			Okei_InterNationSymbol: 'kg'
		});
	},
	initComponent: function() {
		var me = this;

		this.PersonWeightGrid = Ext6.create('Ext6.grid.Panel', {
			border: true,
			viewConfig: {
				minHeight: 33
			},
			recordMenu: Ext6.create('Ext6.menu.Menu', {
				items: [{
					text: 'Редактировать',
					handler: function() {
						me.openPersonWeightEditWindow('edit');
					}
				}, {
					text: 'Удалить запись',
					handler: function() {
						me.deletePersonWeight();
					}
				}]
			}),
			showRecordMenu: function(el, PersonWeight_id) {
				this.recordMenu.PersonWeight_id = PersonWeight_id;
				this.recordMenu.showBy(el);
			},
			cls: 'EmkGrid',
			padding: 10,
			columns: [{
				width: 120,
				header: 'Масса (кг)',
				dataIndex: 'PersonWeight_Weight'
			}, {
				width: 120,
				header: 'Дата',
				dataIndex: 'PersonWeight_setDate'
			}, {
				width: 120,
				header: 'Вид замера',
				dataIndex: 'WeightMeasureType_Name'
			}, {
				width: 120,
				header: 'Отклонение',
				dataIndex: 'WeightAbnormType_Name'
			}, {
				width: 120,
				flex: 1,
				header: 'ИМТ',
				dataIndex: 'PersonWeight_Imt'
			}, {
				width: 40,
				dataIndex: 'PersonWeight_Action',
				renderer: function (value, metaData, record) {
					return "<div class='x6-tool-threedots' onclick='Ext6.getCmp(\"" + me.PersonWeightGrid.id + "\").showRecordMenu(this, " + record.get('PersonWeight_id') + ");'></div>";
				}
			}],
			disableSelection: true,
			store: Ext6.create('Ext6.data.Store', {
				fields: [
					{ name: 'PersonWeight_id', type: 'int' },
					{ name: 'PersonWeight_Weight', type: 'float' },
					{ name: 'PersonWeight_setDate', type: 'string' },
					{ name: 'WeightMeasureType_Name', type: 'string' },
					{ name: 'WeightAbnormType_Name', type: 'string' },
					{ name: 'PersonWeight_Imt', type: 'string' }
				],
				listeners: {
					'load': function(store, records) {
						me.setTitleCounter(records.length);
					}
				},
				proxy: {
					type: 'ajax',
					actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
					url: '/?c=PersonWeight&m=loadPersonWeightPanel',
					reader: {
						type: 'json',
						rootProperty: 'data',
						totalProperty: 'totalCount'
					}
				},
				sorters: [
					'PersonWeight_id'
				]
			})
		});

		Ext6.apply(this, {
			items: [
				this.PersonWeightGrid
			],
			tools: [{
				type: 'plusmenu',
				tooltip: 'Добавить',
				minWidth: 23,
				handler: function() {
					me.openPersonWeightEditWindow('add');
				}
			}]
		});

		this.callParent(arguments);
	}
});