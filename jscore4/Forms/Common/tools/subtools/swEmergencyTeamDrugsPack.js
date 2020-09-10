/* 
swSmpFarmacyDrugWindow - окно поиска медикамента
 */
Ext.define('sw.tools.subtools.swEmergencyTeamDrugsPack', {
	alias: 'widget.swEmergencyTeamDrugsPack',
	extend: 'Ext.window.Window',
	title: 'Просмотр укладки бригады',
	height: 600,
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
				autoLoad: true,
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
					{name: 'DrugMnn', type: 'string'},
					{name: 'EmergencyTeamDrugPack_Total', type: 'string'}
				],				
				sorters: {
					property: 'DrugTorg_Name',
					direction: 'ASC'
				},
				proxy: {
					type: 'ajax',
					url: '/?c=EmergencyTeam4E&m=loadEmergencyTeamDrugsPack',
					//url: '/?c=Farmacy4E&m=loadDrugMultiList',
					reader: {
						type: 'json',
						successProperty: 'success',
						root: 'data',
						totalProperty: 'totalCount'

					},
					extraParams:{
						EmergencyTeam_id: me.EmergencyTeam_id
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
				{ dataIndex: 'DrugForm_Name', header: 'Форма выпуска', width: 140, isfilter:true},			
				{ dataIndex: 'EmergencyTeamDrugPack_Total', header: 'Остаток доз', width: 80, isfilter:true},
			],
			
			searchDrugs: function(){
				searchDrugForm = this.up('form').getForm()
				this.store.load({
					extraParams:{
						EmergencyTeam_id: me.EmergencyTeam_id
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
//		
//		me.on('render', function(cmp){
//			alert(1);			
//			log(me.EmergencyTeam_id);
//		})
		
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
								text: 'Справка',
								iconCls: 'help16',
								drugId: '',
								handler: function(){						
									
								}
							},						
							{
								xtype: 'button',
								text: 'Закрыть',
								iconCls: 'ok16',
								drugId: '',
								handler: function(){						
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


