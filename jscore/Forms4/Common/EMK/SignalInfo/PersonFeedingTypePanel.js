Ext6.define('common.EMK.SignalInfo.PersonFeedingTypePanel', {
	extend: 'swPanel',
	title: 'СПОСОБ ВСКАРМЛИВАНИЯ',
	btnAddClickEnable: true,
	allTimeExpandable: false,
	collapseOnOnlyTitle: true,
	onBtnAddClick: function(){
		this.openPersonFeedingTypeEditWindow('add');
	},
	collapsed: true,
	setParams: function(params) {
		var me = this;
		me.PersonChild_id = params.PersonChild_id;
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
	load: function(data) {
		var me = this;
		this.loaded = true;
		this.PersonFeedingTypeGrid.getStore().load({
			params: {
				Server_id: me.Server_id,
				Person_id: me.Person_id,
				PersonChild_id: me.PersonChild_id
			},
			callback: function() {
				if (data){
					data.callback()
				}
			}
		});
	},
	deletePersonFeedingType: function() {
		var me = this;

		var FeedingTypeAge_id = me.PersonFeedingTypeGrid.recordMenu.FeedingTypeAge_id;
		if (FeedingTypeAge_id) {
			checkDeleteRecord({
				callback: function () {
					me.mask('Удаление записи...');
					Ext6.Ajax.request({
						url: '/?c=PersonFeedingType&m=deletePersonFeedingType',
						params: {
							FeedingTypeAge_id: FeedingTypeAge_id
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
	openPersonFeedingTypeEditWindow: function(action) {
		var me = this;
		var formParams = new Object();

		if ( action == 'add' ) {
			formParams.Server_id = me.Person_id;
			formParams.FeedingTypeAge_id = 0;
			formParams.Person_id = me.Person_id;
			formParams.PersonChild_id = me.PersonChild_id;
		} else {
			var FeedingTypeAge_id = me.PersonFeedingTypeGrid.recordMenu.FeedingTypeAge_id;
			if (!FeedingTypeAge_id) {
				return false;
			}

			formParams.FeedingTypeAge_id = FeedingTypeAge_id;
		}

		getWnd('swPersonFeedingTypeEditWindow').show({
			action: action,
			FeedingTypeAge_id: formParams.FeedingTypeAge_id,
			callback: function(data) {
				if ( !data || !data.personFeedingTypeData )
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
		me.plusMenu = Ext6.create('Ext6.menu.Menu', {
			userCls: 'menuWithoutIcons',
			items: [
				{
					text: 'Печать',
					handler: function () {
						if (!me.loaded) {
							me.load({
								callback: function () {
									Ext6.ux.GridPrinter.print(me.PersonFeedingTypeGrid);
								}
							});
						}else {
							Ext6.ux.GridPrinter.print(me.PersonFeedingTypeGrid);
						}
					}
				},
				{
					text: 'Добавить',
					handler: function() {
						me.openPersonFeedingTypeEditWindow('add');
					}
				}
			]
		});
		this.PersonFeedingTypeGrid = Ext6.create('Ext6.grid.Panel', {
			border: true,
			viewConfig: {
				minHeight: 33
			},
			recordMenu: Ext6.create('Ext6.menu.Menu', {
				items: [{
					text: 'Редактировать',
					handler: function() {
						me.openPersonFeedingTypeEditWindow('edit');
					}
				}, {
					text: 'Удалить запись',
					handler: function() {
						me.deletePersonFeedingType();
					}
				}]
			}),
			showRecordMenu: function(el, FeedingTypeAge_id) {
				this.recordMenu.FeedingTypeAge_id = FeedingTypeAge_id;
				this.recordMenu.showBy(el);
			},
			cls: 'EmkGrid',
			padding: 10,
			columns: [{
				width: 200,
				header: 'Возраст (мес)',
				dataIndex: 'FeedingTypeAge_Age'
			}, {
				width: 300,
				flex: 1,
				header: 'Вид вскармливания',
				dataIndex: 'FeedingType_Name'
			}, {
				width: 40,
				dataIndex: 'PersonFeedingType_Action',
				renderer: function (value, metaData, record) {
					return "<div class='x6-tool-threedots' onclick='Ext6.getCmp(\"" + me.PersonFeedingTypeGrid.id + "\").showRecordMenu(this, " + record.get('FeedingTypeAge_id') + ");'></div>";
				}
			}],
			disableSelection: true,
			store: Ext6.create('Ext6.data.Store', {
				fields: [
					{ name: 'FeedingTypeAge_id', type: 'int' },
					{ name: 'FeedingTypeAge_Age', type: 'int' },
					{ name: 'FeedingType_Name', type: 'string' }

				],
				listeners: {
					'load': function(store, records) {
						me.setTitleCounter(records.length);
					}
				},
				proxy: {
					type: 'ajax',
					actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
					url: '/?c=PersonFeedingType&m=loadPersonFeedingType',
					reader: {
						type: 'json',
						rootProperty: 'data',
						totalProperty: 'totalCount'
					}
				},
				sorters: [
					'FeedingTypeAge_Age'
				]
			})
		});

		Ext6.apply(this, {
			items: [
				this.PersonFeedingTypeGrid
			]
		});

		this.callParent(arguments);
	}
});