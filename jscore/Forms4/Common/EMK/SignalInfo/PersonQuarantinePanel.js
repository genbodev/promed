/**
 * Панель списка уточненных диагнозов
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
Ext6.define('common.EMK.SignalInfo.PersonQuarantinePanel', {
	extend: 'swPanel',
	btnAddClickEnable: true,
	collapseOnOnlyTitle: true,
	allTimeExpandable: false,
	onBtnAddClick: function(){
		this.openQuarantineEditWindow('add');
	},
	title: 'СПИСОК КОНТРОЛЬНЫХ КАРТ ПО КАРАНТИНУ',
	collapsed: true,
	setParams: function(params) {
		var me = this;

		me.userMedStaffFact = params.userMedStaffFact;
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
		this.PersonQuarantineGrid.getStore().load({
			params: {
				Person_id: me.Person_id
			}
		});
	},
	initComponent: function() {
		var me = this;

		var editAction = new Ext6.menu.Item({
			name: 'edit',
			text: 'Редактирование',
			handler: function() {
				me.openQuarantineEditWindow('edit');
			}
		});
		this.PersonQuarantineGrid = Ext6.create('Ext6.grid.Panel', {
			border: true,
			cls: 'EmkGrid',
			padding: 10,
			recordMenu: Ext6.create('Ext6.menu.Menu', {
				items: [{
					name: 'view',
					text: 'Просмотр',
					handler: function() {
						me.openQuarantineEditWindow('view');
					}
				},editAction ]
			}),
			showRecordMenu: function(el, PersonQuarantine_id) {
				this.recordMenu.PersonQuarantine_id = PersonQuarantine_id;
				var index = this.store.find('PersonQuarantine_id', PersonQuarantine_id);
				var record = this.store.getAt(index);
				editAction.setDisabled(!!record.get('PersonQuarantine_endDate'));
				this.recordMenu.showBy(el);
			},
			columns: [{
				flex: 1,
				header: 'Дата открытия',
				dataIndex: 'PersonQuarantine_begDate',
				renderer: Ext6.util.Format.dateRenderer('d.m.Y')
			}, {
				flex: 2,
				header: 'Причина открытия',
				dataIndex: 'PersonQuarantineOpenReason_Name'
			},{
				flex: 1,
				header: 'Дата контакта/прибытия',
				dataIndex: 'arrivalOrContactDate',
				renderer: Ext6.util.Format.dateRenderer('d.m.Y')
			}, {
				flex: 1,
				header: 'Дней на карантине',
				dataIndex: 'QuarantineDays'
			}, {
				flex: 1,
				header: 'Дата выявления заболевания',
				dataIndex: 'PersonQuarantine_approveDate',
				renderer: Ext6.util.Format.dateRenderer('d.m.Y')
			}, {
				flex: 1,
				header: 'Дата закрытия',
				dataIndex: 'PersonQuarantine_endDate',
				renderer: Ext6.util.Format.dateRenderer('d.m.Y')
			},{
				flex: 2,
				header: 'Причина закрытия',
				dataIndex: 'PersonQuarantineCloseReason_Name'
			}, {
				width: 40,
				dataIndex: 'PersonQuarantine_Action',
				renderer: function (value, metaData, record) {
					return "<div class='x6-tool-threedots' onclick='Ext6.getCmp(\"" + me.PersonQuarantineGrid.id + "\").showRecordMenu(this, " + record.get('PersonQuarantine_id') + ");'></div>";
				}
			}],
			disableSelection: true,
			store: Ext6.create('Ext6.data.Store', {
				fields: [
					{ name: 'PersonQuarantine_id', type: 'int' },
					{ name: 'PersonQuarantine_begDate', type: 'date', dateFormat: 'd.m.Y'},
					{ name: 'PersonQuarantineOpenReason_Name', type: 'string' },
					{ name: 'arrivalOrContactDate', type: 'date', dateFormat: 'd.m.Y' },
					{ name: 'QuarantineDays', type: 'string' },
					{ name: 'PersonQuarantine_approveDate', type: 'date', dateFormat: 'd.m.Y' },
					{ name: 'PersonQuarantine_endDate', type: 'date', dateFormat: 'd.m.Y' },
					{ name: 'PersonQuarantineCloseReason_Name', type: 'string' }
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
					url: '/?c=PersonQuarantine&m=loadGrid',
					reader: {
						type: 'json',
						rootProperty: 'data',
						totalProperty: 'totalCount'
					}
				},
				sorters: {
					property: 'PersonQuarantine_begDate',
					direction: 'DESC'
				}
			})
		});

		Ext6.apply(this, {
			items: [
				this.PersonQuarantineGrid
			],
			tools: [{
				type: 'plusmenu',
				tooltip: 'Добавить',
				minWidth: 23,
				handler: function() {
					me.openQuarantineEditWindow('add');
				}
			}]
		});

		this.callParent(arguments);
	},
	openQuarantineEditWindow: function(action) {
		var me = this;
		var params = new Object();

		params.PersonQuarantine_id = action!='add' ? me.PersonQuarantineGrid.recordMenu.PersonQuarantine_id : null;
		params.MedStaffFact_id = me.userMedStaffFact.MedStaffFact_id;
		params.Person_id = me.Person_id;
		params.Server_id = me.Server_id;
		params.action = action;
		params.callback = function() {
			me.load();
		};

		getWnd('swPersonQuarantineEditWindow').show(params);
	}
});