/**
 * Панель группы крови и резус фактора
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
Ext6.define('common.EMK.SignalInfo.PersonBloodGroupPanel', {
	extend: 'swPanel',
	title: 'ГРУППА КРОВИ И РЕЗУС ФАКТОР',
	btnAddClickEnable: true,
	allTimeExpandable: false,
	collapseOnOnlyTitle: true,
	onBtnAddClick: function(){
		this.openPersonBloodGroupEditWindow('add');
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
		this.PersonBloodGroupGrid.getStore().load({
			params: {
				Person_id: me.Person_id
			}
		});
	},
	deletePersonBloodGroup: function() {
		var me = this;

		var PersonBloodGroup_id = me.PersonBloodGroupGrid.recordMenu.PersonBloodGroup_id;
		if (PersonBloodGroup_id) {
			checkDeleteRecord({
				callback: function () {
					me.mask('Удаление записи...');
					Ext6.Ajax.request({
						url: '/?c=PersonBloodGroup&m=deletePersonBloodGroup',
						params: {
							PersonBloodGroup_id: PersonBloodGroup_id
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
	openPersonBloodGroupEditWindow: function(action) {
		var me = this;
		var formParams = new Object();

		formParams.Person_id = me.Person_id;
		formParams.Server_id = me.Server_id;

		if ( action == 'add' ) {
			formParams.PersonBloodGroup_id = 0;
		} else {
			var PersonBloodGroup_id = me.PersonBloodGroupGrid.recordMenu.PersonBloodGroup_id;
			if (!PersonBloodGroup_id) {
				return false;
			}

			formParams.PersonBloodGroup_id = PersonBloodGroup_id;
		}

		getWnd('swPersonBloodGroupEditWindow').show({
			action: action,
			PersonBloodGroup_id: formParams.PersonBloodGroup_id,
			callback: function(data) {
				if ( !data || !data.personBloodGroupData )
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

		this.PersonBloodGroupGrid = Ext6.create('Ext6.grid.Panel', {
			border: false,
			minHeight: 33,
			recordMenu: Ext6.create('Ext6.menu.Menu', {
				items: [{
					text: 'Редактировать',
					handler: function() {
						me.openPersonBloodGroupEditWindow('edit');
					}
				}, {
					text: 'Удалить запись',
					handler: function() {
						me.deletePersonBloodGroup();
					}
				}]
			}),
			showRecordMenu: function(el, PersonBloodGroup_id) {
				this.recordMenu.PersonBloodGroup_id = PersonBloodGroup_id;
				this.recordMenu.showBy(el);
			},
			userCls: 'blood-group-type',
			columns: [{
				flex: 1,
				minWidth: 100,
				tdCls: 'padLeft20',
				dataIndex: 'BloodGroupType_Name',
				renderer: function (value, metaData, record) {
					return record.get('BloodGroupType_Name') + ' ' + record.get('RhFactorType_Name');
				}
			}, {
				width: 40,
				dataIndex: 'PersonBloodGroup_Action',
				renderer: function (value, metaData, record) {
					return "<div class='x6-tool-plusmenu'></div>";
				}
			}, {
				width: 40,
				dataIndex: 'PersonBloodGroup_Action',
				renderer: function (value, metaData, record) {
					return "<div class='x6-tool-threedots' onclick='Ext6.getCmp(\"" + me.PersonBloodGroupGrid.id + "\").showRecordMenu(this, " + record.get('PersonBloodGroup_id') + ");'></div>";
				}
			}],
			disableSelection: true,
			store: Ext6.create('Ext6.data.Store', {
				fields: [
					{ name: 'PersonBloodGroup_id', type: 'int' },
					{ name: 'BloodGroupType_Name', type: 'string' },
					{ name: 'RhFactorType_Name', type: 'string' }
				],
				listeners: {
					'load': function(store, records) {
						me.setTitleCounter(records.length);
					}
				},
				proxy: {
					type: 'ajax',
					actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
					url: '/?c=PersonBloodGroup&m=loadPersonBloodGroupPanel',
					reader: {
						type: 'json',
						rootProperty: 'data',
						totalProperty: 'totalCount'
					}
				},
				sorters: [
					'PersonBloodGroup_id'
				]
			})
		});

		Ext6.apply(this, {
			items: [
				this.PersonBloodGroupGrid
			],
			tools: [{
				type: 'plusmenu',
				tooltip: 'Добавить',
				minWidth: 23,
				handler: function() {
					me.openPersonBloodGroupEditWindow('add');
				}
			}]
		});

		this.callParent(arguments);
	}
});