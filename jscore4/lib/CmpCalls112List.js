//Форма Журнал вызовов

Ext.define('sw.CmpCalls112List', {
	extend: 'Ext.grid.Panel',
	alias: 'widget.CmpCalls112List',
	refId: 'CmpCalls112List',
	//id: 'CmpCalls112List',
	flex: 1,
	multiSelect: true,
	selType: 'checkboxmodel',
	viewConfig: {
		loadMask: true,
		loadingText: 'Загрузка..',
		preserveScrollOnRefresh: true
	},
	columns: [
		{
			dataIndex: 'CmpCallCard112_id',
			text: 'ИД карты вызова',
			hidden: true
		},
		{
			dataIndex: 'CmpCallCard112StatusType_id',
			text: 'ИД статуса карты вызова 112',
			hidden: true
		},
		{
			dataIndex: 'Ier_IerIsoTime',
			text: 'Дата и время',
			width: 120,
			xtype: 'datecolumn',
			format: 'd.m.Y H:i:s'
		},
		{
			dataIndex: 'Ier_AcceptOperatorStr',
			text: 'Номер оператора 112',
			width: 120
		},
		{
			dataIndex: 'ExtPatientPerson_Fio',
			text: 'ФИО',
			width: 200
		},
		{
			dataIndex: 'ExtPatientPerson_BirthdateIsoStr',
			text: 'Дата рождения',
			width: 120,
			xtype: 'datecolumn',
			format: 'd.m.Y'
		},
		{
			dataIndex: 'ExtPatientPerson_Age',
			text: 'Возраст',
			width: 80
		},
		{
			dataIndex: 'Adress_Name',
			text: 'Адрес вызова',
			width: 180
		},
		{
			dataIndex: 'CmpCallCard_Numv',
			text: '№ за день',
			width: 80
		},
		{
			dataIndex: 'CmpCallCard_Ngod',
			text: '№ за год',
			width: 80
		},
		{
			dataIndex: 'CmpCallCard112StatusType_Name',
			text: 'Статус карточки',
			width: 150
		},
		{
			dataIndex: '',
			renderer: function(v, p, r) {
				//return "<a href='javascript:Ext.getCmp(\"CmpCalls112List\").showCmpCallCard112(" + r.get('CmpCallCard_id') + ");'>Карточка вызова 112</a>";
				return "<a href='javascript:Ext.ComponentQuery.query(\"grid[refId=CmpCalls112List]\")[0].showCmpCallCard112(" + r.get('CmpCallCard_id') + ");'>Карточка вызова 112</a>";
			},
			text: '',
			width: 150
		}
	],
	showCmpCallCard112: function(card_id){
		var callcard112 = Ext.create('sw.tools.swCmpCallCard112',{
			view: 'view',
			card_id: card_id
		});
		callcard112.show();
	},
	initComponent: function () {
		var me = this;
		
		me.addEvents({
			selectCalls: true			
		});

		Ext.define('CmpCalls112ListModel', {
			extend: 'Ext.data.Model',
			idProperty: 'CmpCallCard_id',
			fields: [
				{
					name: 'CmpCallCard112_id',
					type: 'int'
				},
				{
					name: 'CmpCallCard_id',
					type: 'int'
				},
				{
					name: 'CmpCallCard112StatusType_id',
					type: 'int'
				},
				{
					name: 'Ier_IerIsoTime',
					type: 'date',
					convert: function (dt) {
						return new Date(dt.replace(/(\d+).(\d+).(\d+)/, '$3/$2/$1'));
					}
				},
				{
					name: 'Ier_AcceptOperatorStr',
					type: 'string'
				},
				{
					name: 'ExtPatientPerson_Fio',
					type: 'string'
				},
				{
					name: 'ExtPatientPerson_BirthdateIsoStr',
					type: 'date'					
				},
				{
					name: 'ExtPatientPerson_Age',
					type: 'string'
				},
				{
					name: 'Adress_Name',
					type: 'string'
				},
				{
					name: 'CmpCallCard_Numv',
					type: 'string'
				},
				{
					name: 'CmpCallCard_Ngod',
					type: 'string'
				},
				{
					name: 'CmpCallCard112StatusType_Name',
					type: 'string'
				}
			]
		});


		me.store = Ext.create('Ext.data.Store', {
			storeId: me.id+'_DispatcherCallWP_CmpCalls112ListStore',
			model: 'CmpCalls112ListModel',
			autoLoad: false,
			stripeRows: true,
			numLoad: 0,
			pageSize: 100,
			sorters: [
				{
					property: 'Ier_IerIsoTime',
					direction: 'DESC'
				}
			],
			proxy: {
				type: 'ajax',
				url: '/?c=CmpCallCard4E&m=loadCmpCallCard112List',
				reader: {
					type: 'json',
					successProperty: 'success',
					totalProperty: 'totalCount',
					root: 'data'
				},
				actionMethods: {
					create: 'POST',
					read: 'POST',
					update: 'POST',
					destroy: 'POST'
				}
			}
		});

		me.bbar = Ext.create('Ext.PagingToolbar', {
			store: me.store,
			displayInfo: true,
			pageSize: 100,
			beforePageText: 'Страница',
			afterPageText: 'из {0}',
			displayMsg: 'показано {0} - {1} из {2}'
		});

		me.tbar = {
			xtype: 'BaseForm',
			id: me.id+'_CmpCalls112ListFilter112Form',
			dock: 'top',
			items: [{
				xtype: 'container',
				items: [
					{
						xtype: 'toolbar',
						layout: {
							type: 'hbox',
							align: 'stretch'
						},
						margin: '5',
						border: false,
						items: [
							{
								xtype: 'container',
								layout: {
									type: 'hbox',
									align: 'left'
								},
								items: [
									{
										xtype: 'datePickerRange',
										name: 'callListDateRange',
										width: 225
									}
								]
							}
						]
					},
					{
						xtype: 'fieldset',
						title: 'Фильтры',
						//id: 'Filter112FieldSet',
						collapsible: true,
						layout: {
							type: 'vbox',
							align: 'stretch'
						},
						padding: '0 10 5 0',
						margin: 3,
						flex: 1,
						fieldDefaults: {
							margin: 2,
							labelWidth: 200,
							flex: 1,
							minWidth: 300,
							maxWidth: 400,
							labelAlign: 'right'
						},
						defaults: {
							labelWidth: 200,
							flex: 1
						},
						items: [
							{
								xtype: 'container',
								flex: 1,
								layout: {
									type: 'hbox',
									align: 'stretch'
								},
								items: [
									{
										xtype: 'textfield',
										fieldLabel: 'Номер оператора 112',
										name: 'Ier_AcceptOperatorStr'
									},
									{
										xtype: 'swCmpCallCard112StatusTypeCombo',
										name: 'CmpCallCard112StatusType_id',
										value: 1,
										fieldLabel: 'Статус карточки',
										editable: false
									}
								]
							},
							{
								xtype: 'container',
								layout: {
									type: 'hbox',
									align: 'stretch'
								},
								margin: '0 0 0 116',
								items: [
									{
										xtype: 'button',
										refId: 'searchBtn112',
										iconCls: 'search16',
										text: 'Найти',
										width: 70,
										margin: '0 10',
										handler: function(){
											me.searchCmpCallCard112()
										}
									},
									{
										xtype: 'button',
										refId: 'resetBtn',
										iconCls: 'reset16',
										width: 70,
										text: 'Сброс',
										margin: '0',
										handler: function(){
											me.down('BaseForm').getForm().reset();
										}
									}
								]
							}
						]
					}
				]
			}]
		};

		me.dockedItems = [
			{
				xtype: 'toolbar',
				margin: '0 0 20 0',
				dock: 'bottom',
				items: [
					{
						xtype: 'button',
						text: 'Выбрать',
						refId: 'selectCallsBtn',
						iconCls: 'ok16',
						handler: function () {
							var rec = me.selModel.getSelection();
							if(rec.length)me.fireEvent('selectCalls', rec);
						}
					},
					'->',
					{
						xtype: 'button',
						text: 'Помощь',
						iconCls: 'help16',
						handler: function () {
							ShowHelp(me.up('container').title);
						}
					},
					{
						xtype: 'button',
						refId: 'cancel112Btn',
						iconCls: 'cancel16',
						text: 'Закрыть',
						margin: '0 10',
						handler: function(){
							var mainTabPanel = Ext.ComponentQuery.query('[refId=mainTabPanelDW]')[0];
							mainTabPanel.setActiveTab(0);
							Ext.ComponentQuery.query('[refId=calls112ListDW]')[0].tab.hide();
						}
					}
				]
			}
		];

		me.callParent(arguments)
	},
	listeners: {
		render: function () {
			var dateRange = this.down('datePickerRange');

			this.searchCmpCallCard112()
		},
		afterrender: function () {
			var me = this;
			var pressedkeyg = new Ext.util.KeyMap({
				target: me.el,
				binding: [
					{
						key: [Ext.EventObject.ENTER],
						fn: function () {
							me.searchCmpCallCard112()
						}
					}
				]
			});

			var tabpanel = me.up('tabpanel');

			if(tabpanel){
				tabpanel.on('tabchange', function( tabPanel, newCard, oldCard, eOpts){
					var journal112Calls = newCard.down('panel[refId=CmpCalls112List]');

					if(journal112Calls){
						me.searchCmpCallCard112();
					};

				});
			};
		},
		selectionchange: function(cmp, selected ){
			var disable = false;
			selected.forEach(function(item){
				if(item.get('CmpCallCard112StatusType_id') != 1){
					disable = true;
				}
			});
			this.down('[refId=selectCallsBtn]').setDisabled(disable);
		}
	},
	searchCmpCallCard112: function () {
		var me = this,
			baseForm = me.down('BaseForm'),
			dateRange = baseForm.down('datePickerRange'),
			params = baseForm.getValues();

		params.begDate = Ext.Date.format(dateRange.dateFrom, 'd.m.Y');
		params.endDate = Ext.Date.format(dateRange.dateTo, 'd.m.Y');

		me.store.proxy.extraParams = params;
		me.store.reload()
	}
});
