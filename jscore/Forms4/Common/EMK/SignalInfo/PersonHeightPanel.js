/**
 * Панель роста
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
Ext6.define('common.EMK.SignalInfo.PersonHeightPanel', {
	extend: 'swPanel',
	title: 'РОСТ',
	allTimeExpandable: false,
	btnAddClickEnable: true,
	collapseOnOnlyTitle: true,
	onBtnAddClick: function(){
		this.openPersonHeightEditWindow('add');
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
		this.PersonHeightGrid.getStore().load({
			params: {
				Person_id: me.Person_id
			}
		});
	},
	deletePersonHeight: function() {
		var me = this;

		var PersonHeight_id = me.PersonHeightGrid.recordMenu.PersonHeight_id;
		if (PersonHeight_id) {
			checkDeleteRecord({
				callback: function () {
					me.mask('Удаление записи...');
					Ext6.Ajax.request({
						url: '/?c=PersonHeight&m=deletePersonHeight',
						params: {
							PersonHeight_id: PersonHeight_id
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
	openPersonHeightEditWindow: function(action) {
		var me = this;
		var formParams = new Object();

		if ( action == 'add' ) {
			formParams.PersonHeight_id = 0;
			formParams.Person_id = me.Person_id;
			formParams.Server_id = me.Server_id;
		} else {
			var PersonHeight_id = me.PersonHeightGrid.recordMenu.PersonHeight_id;
			if (!PersonHeight_id) {
				return false;
			}

			formParams.PersonHeight_id = PersonHeight_id;
		}

		getWnd('swPersonHeightEditWindow').show({
			action: action,
			measureTypeExceptions: [ 1, 2 ],
			PersonHeight_id: formParams.PersonHeight_id,
			callback: function(data) {
				if ( !data || !data.personHeightData )
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

		this.PersonHeightGrid = Ext6.create('Ext6.grid.Panel', {
			border: true,
			viewConfig: {
				minHeight: 33
			},
			recordMenu: Ext6.create('Ext6.menu.Menu', {
				items: [{
					text: 'Редактировать',
					handler: function() {
						me.openPersonHeightEditWindow('edit');
					}
				}, {
					text: 'Удалить запись',
					handler: function() {
						me.deletePersonHeight();
					}
				}]
			}),
			showRecordMenu: function(el, PersonHeight_id) {
				this.recordMenu.PersonHeight_id = PersonHeight_id;
				this.recordMenu.showBy(el);
			},
			cls: 'EmkGrid',
			padding: 10,
			columns: [{
				width: 120,
				header: 'Рост (см)',
				dataIndex: 'PersonHeight_Height'
			}, {
				width: 120,
				header: 'Дата',
				dataIndex: 'PersonHeight_setDate'
			}, {
				width: 120,
				header: 'Вид замера',
				dataIndex: 'HeightMeasureType_Name'
			}, {
				width: 120,
				flex: 1,
				header: 'Отклонение',
				dataIndex: 'HeightAbnormType_Name'
			}, {
				width: 40,
				dataIndex: 'PersonHeight_Action',
				renderer: function (value, metaData, record) {
					return "<div class='x6-tool-threedots' onclick='Ext6.getCmp(\"" + me.PersonHeightGrid.id + "\").showRecordMenu(this, " + record.get('PersonHeight_id') + ");'></div>";
				}
			}],
			disableSelection: true,
			store: Ext6.create('Ext6.data.Store', {
				fields: [
					{ name: 'PersonHeight_id', type: 'int' },
					{ name: 'PersonHeight_Height', type: 'float' },
					{ name: 'PersonHeight_setDate', type: 'string' },
					{ name: 'HeightMeasureType_Name', type: 'string' },
					{ name: 'HeightAbnormType_Name', type: 'string' },
					{ name: 'PersonHeight_Imt', type: 'string' }
				],
				listeners: {
					'load': function(store, records) {
						me.setTitleCounter(records.length);
					}
				},
				proxy: {
					type: 'ajax',
					actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
					url: '/?c=PersonHeight&m=loadPersonHeightPanel',
					reader: {
						type: 'json',
						rootProperty: 'data',
						totalProperty: 'totalCount'
					}
				},
				sorters: [
					'PersonHeight_id'
				]
			})
		});

		Ext6.apply(this, {
			items: [
				this.PersonHeightGrid
			],
			tools: [{
				type: 'plusmenu',
				tooltip: 'Добавить',
				minWidth: 23,
				handler: function() {
					me.openPersonHeightEditWindow('add');
				}
			}]
		});

		this.callParent(arguments);
	}
});