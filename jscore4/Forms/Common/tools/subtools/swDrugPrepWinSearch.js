/* 
swSmpFarmacyDrugWindow - окно поиска медикамента
 */
Ext.define('sw.tools.subtools.swDrugPrepWinSearch', {
	alias: 'widget.swDrugPrepWinSearch',
	extend: 'Ext.window.Window',
	title: 'Поиск медикамента',
	height: 670,
    width: 1400,
	modal: true,
	maximizable: true,
	
	initComponent: function() {
		var me = this;
		
		me.addEvents({
			onDrugSelect: true
		});

		drugMultiList = Ext.create('Ext.grid.Panel', {
				
			viewConfig: {
				loadingText: 'Загрузка'
			},
			height: 500,
			padding: '10',

			plugins: {
					ptype: 'bufferedrenderer',
					trailingBufferZone: 20,  // Keep 20 rows rendered in the table behind scroll
					leadingBufferZone: 50   // Keep 50 rows rendered in the table ahead of scroll
				},
			stripeRows: true,
			refId: 'searchDrugListGrid',
			store: new Ext.data.JsonStore({
				autoLoad: false,
				pageSize: 50,
				storeId: 'searchDrugListGridStore',
				fields: [
					
					{name: 'DrugTorg_Name', type: 'string'},
					{name: 'Drug_id', type: 'int'},
					{name: 'DrugPrepFas_id', type: 'int'},
					{name: 'Drug_Nomen', type: 'string'},
					{name: 'Drug_Name', type: 'string'},
					{name: 'DrugForm_Name', type: 'string'},					
					{name: 'Drug_Dose', type: 'int'},
					{name: 'Drug_Fas', type: 'int'},
					{name: 'Drug_PackName', type: 'string'},
					{name: 'Drug_Firm', type: 'string'},
					{name: 'Drug_Ean', type: 'int'},
					{name: 'Drug_RegNum', type: 'string'},
					{name: 'DrugMnn', type: 'string'}
				],
				sorters: {
					property: 'DrugTorg_Name',
					direction: 'ASC'
				},
				proxy: {
					type: 'ajax',
					url: '/?c=Farmacy4E&m=loadDrugMultiList',
					reader: {
						type: 'json',
						successProperty: 'success',
						root: 'data',
						totalProperty: 'totalCount'

					},
					actionMethods: {
						create : 'POST',
						read   : 'POST',
						update : 'POST',
						destroy: 'POST'
					}
				}
			}),
			columns: [
				{ dataIndex: 'Drug_id', key: true, hidden: true, isfilter:false, hideable: false},
				{ dataIndex: 'DrugPrepFas_id', hidden: true, isfilter:false, hideable: false},
				{ dataIndex: 'DrugTorg_Name', header: 'Торговое наименование', isfilter:true, flex: 1},
				{ dataIndex: 'DrugMnn', header: 'МНН', flex: 1},
				{ dataIndex: 'DrugForm_Name', header: 'Форма выпуска', width: 140, isfilter:true},
				{ dataIndex: 'Drug_Dose', header: 'Дозировка', width: 80, isfilter:true},
				{ dataIndex: 'Drug_Fas', header: 'Фасовка', width: 60},
				{ dataIndex: 'Drug_PackName', header: 'Упаковка', width: 100},
				{ dataIndex: 'Drug_Firm', header: 'Производитель', width: 200, isfilter:true},
				{ dataIndex: 'Drug_Ean', header: 'EAN', width: 100},
				{ dataIndex: 'Drug_RegNum', header: 'РУ', width: 120}	
			],
			
			searchDrugs: function(){
				searchDrugForm = this.up('form').getForm()
				this.store.load({
					params:{
						'DrugForm_Name' : searchDrugForm.findField('SearchDrugForm_Name').getValue(),
						'DrugTorg_Name' : searchDrugForm.findField('SearchDrugTorg_Name').getValue(),
						'Drug_Dose' : searchDrugForm.findField('SearchDrug_Dose').getValue(),
						'Drug_Firm' : searchDrugForm.findField('SearchDrug_Firm').getValue(),
						'Drug_PackName' : searchDrugForm.findField('SearchDrug_PackName').getValue()
					}
				})
			},
			
			clearStoreAndFields: function(){
				searchDrugForm = this.up('form').getForm()
				searchDrugForm.getFields().each(
					function(key, value, count){
						key.setValue('')
					}
				)
				this.store.removeAll()
			},
			
			listeners: {
				itemcontextmenu: function(view, record, item, index, event, options) {
					event.stopEvent()
					//console.log(record.get('DrugTorg_Name'))
					Ext.create('Ext.menu.Menu', {
						items: [{
							id: 'chooseDrug',
							text: 'Выбрать',
							iconCls: 'ok16'
						}],
						listeners: {
							itemclick: function(item) {
								switch (item.id) {
									case 'chooseDrug': {break;}
								}
							}
						}
					}).showAt(event.getXY());
				},
				itemclick: function( cmp, record, item, index, e, eOpts ){
					this.up('form').getForm().findField('selectedDrugId').setValue(record.get('Drug_id'))
					this.up('form').getForm().findField('selectedDrugPrepFasId').setValue(record.get('DrugPrepFas_id'))					
				}
			},
			
			dockedItems: [
				{
					xtype: 'pagingtoolbar',
					dock: 'bottom',
					displayInfo: true,
					store: Ext.data.StoreManager.lookup('searchDrugListGridStore')
				}
			]
		})
		
		Ext.applyIf(me, {
			items: [
			{
				xtype: 'BaseForm',
				id: 'drugSearchForm',
				frame: true,

				layout: {
					type: 'fit'
				},
				bodyPadding: 10,
				dockedItems: [
					{
						xtype: 'hiddenfield',
						anchor: '100%',
						name: 'selectedDrugId'
					},
					{
						xtype: 'hiddenfield',
						anchor: '100%',
						name: 'selectedDrugPrepFasId'
					},
					{
						xtype: 'fieldset',
						dock: 'top',
						layout: {
							type: 'fit'
						},
						title: 'Параметры поиска',
						items: [
							{
								xtype: 'fieldcontainer',
								layout: {
									type: 'hbox'
								},
								items: [
									{
										xtype: 'transFieldDelbut',
										flex: 2,
										margin: '0 5',
										fieldLabel: 'Торговое наименование/Мнн',
										labelAlign: 'top',
										name: 'SearchDrugTorg_Name',
										labelWidth: 150,
										translate: false,
										enableKeyEvents : true,
										listeners: {
											keypress: function(c, e, o){
											if ( (e.getKey() == 13))
											{
												drugMultiList.searchDrugs()
											}}
										}
									},
									{
										xtype: 'transFieldDelbut',
										flex: 1,
										margin: '0 5',
										fieldLabel: 'Производитель',
										name: 'SearchDrug_Firm',
										labelAlign: 'top',
										labelWidth: 150,
										translate: false,
										storeName: 'searchDrugListGridStore',
										displayField: 'Drug_Firm',
										enableKeyEvents : true,
										listeners: {
											keypress: function(c, e, o){
											if ( (e.getKey() == 13))
											{
												drugMultiList.searchDrugs()
											}}
										}
									},
									{
										xtype: 'transFieldDelbut',
										flex: 1,
										margin: '0 5',
										width: 279,
										fieldLabel: 'Упаковка',
										name: 'SearchDrug_PackName',
										labelAlign: 'top',
										labelWidth: 150,
										translate: false,
										storeName: 'searchDrugListGridStore',
										displayField: 'Drug_PackName',
										enableKeyEvents : true,
										listeners: {
											keypress: function(c, e, o){
											if ( (e.getKey() == 13))
											{
												drugMultiList.searchDrugs()
											}}
										}
									},
									{
										xtype: 'transFieldDelbut',
										flex: 1,
										margin: '0 5',
										width: 280,
										fieldLabel: 'Форма выпуска',
										name: 'SearchDrugForm_Name',
										labelAlign: 'top',
										labelWidth: 150,
										translate: false,
										storeName: 'searchDrugListGridStore',
										displayField: 'DrugForm_Name',
										enableKeyEvents : true,
										listeners: {
											keypress: function(c, e, o){
											if ( (e.getKey() == 13))
											{
												drugMultiList.searchDrugs()
											}}
										}
									},
									{
										xtype: 'transFieldDelbut',
										flex: 1,
										margin: '0 5',
										width: 183,
										fieldLabel: 'Дозировка',
										name: 'SearchDrug_Dose',
										labelAlign: 'top',
										labelWidth: 150,
										translate: false,
										storeName: 'searchDrugListGridStore',
										displayField: 'Drug_Dose',
										enableKeyEvents : true,
										listeners: {
											keypress: function(c, e, o){
											if ( (e.getKey() == 13))
											{
												drugMultiList.searchDrugs()
											}}
										}
									}
								]
							},
							{
								xtype: 'container',
								width: 150,
								height: 60,
								margin: '5 4',
								layout: {
									align: 'top',
									pack: 'end',
									type: 'hbox'
								},
								items: [
									{
										xtype: 'button',
										text: 'Найти',
										iconCls: 'search16',
										margin: '0 0 0 10',
										handler: function() {
											drugMultiList.searchDrugs()
										}
									},
									{
										xtype: 'button',
										text: 'Сброс',
										iconCls: 'resetsearch16',
										margin: '0 0 0 10',
										handler: function() {
											drugMultiList.clearStoreAndFields()
											//callsCardsGrid.store.clearFilter(false);
										}
									}
								]
							}
						]
					},
					drugMultiList,
					{
						xtype: 'container',
						layout: {
							align: 'stretch',
							pack: 'end',
							type: 'hbox',
							padding: '3 10 3 0'
						},
						items: [
							{
								xtype: 'button',
								text: 'Выбрать',
								iconCls: 'ok16',
								drugId: '',
								handler: function(){
									drugId = this.up('form').getForm().findField('selectedDrugId').getValue()
									drugPrepFasId = this.up('form').getForm().findField('selectedDrugPrepFasId').getValue()
									//drudWnd = Ext.ComponentQuery.query('sw.tools.subtools.swSmpFarmacyAddDrugWindow')[0]
									//drudWnd = Ext.getCmp('sw.tools.subtools.swSmpFarmacyAddDrugWindow');
									//drudWnd.setDrug(drugId, drugPrepFasId)
									me.fireEvent('onDrugSelect', drugId, drugPrepFasId);
									me.close()
								}
							}
						]
					}
                    ]
                }
			]
		})
		
		me.callParent(arguments)
	}
})


