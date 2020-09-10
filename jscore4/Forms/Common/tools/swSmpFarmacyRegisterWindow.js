/* 
Регистр прихода-расхода медикаментов СМП
*/


Ext.define('sw.tools.swSmpFarmacyRegisterWindow', {
	alias: 'widget.swSmpFarmacyRegisterWindow',
	extend: 'Ext.window.Window',
	title: 'Регистр прихода-расхода медикаментов СМП',
	width: 850,
	height: 500,
	maximizable: true,
	modal: true,
	layout: {
        align: 'stretch',
        type: 'vbox'
    },
	initComponent: function() {
		var me = this;
		
		drugList = Ext.create('Ext.grid.Panel', {
			//region: 'north',
			title: 'Список медикаментов',
			flex: 1,
			autoScroll: true,
			stripeRows: true,
			refId: 'drugListGrid',
			viewConfig: {
				loadingText: 'Загрузка'
			},
			tbar: [
				{
					xtype: 'button',
					itemId: 'addDrugButton',
					text: 'Добавить',
					iconCls: 'add16',
					handler: function(){
						Ext.create('sw.tools.subtools.swSmpFarmacyAddDrugWindow').show()
					}
				},
				{
					xtype: 'button',
					disabled: true,
					itemId: 'writeOffDrugButton',
					text: 'Списать',
					iconCls: 'edit16',
					handler: function(){
						view = drugList.getView()
						data = view.getRecord(view.getSelectedNodes()[0])
						Ext.create('sw.tools.subtools.swSmpFarmacyWriteOffDrugWindow', data.data).show()
					}
				},
				{
					xtype: 'button',
					itemId: 'refreshDrugListButton',
					disabled: true,
					text: 'Обновить',
					iconCls: 'refresh16',
					handler: function(){
						drugList.store.reload()
					}
				}
			],
			store: new Ext.data.JsonStore({
				autoLoad: true,
				numLoad: 0,
				storeId: 'drugListGridStore',
				fields: [
					{name: 'Drug_id', type: 'int'},
					{name: 'CmpFarmacyBalance_id', type: 'int'},
					{name: 'AddDate', type: 'string'},					
					{name: 'DDFGT', type: 'string'},
					{name: 'Drug_Fas', type: 'string'},
					{name: 'Drug_PackName', type: 'string'},
					{name: 'CmpFarmacyBalance_PackRest', type: 'string'},					
					{name: 'CmpFarmacyBalance_DoseRest', type: 'string'}
				],
				proxy: {
					limitParam: undefined,
					startParam: undefined,
					paramName: undefined,
					pageParam: undefined,
					type: 'ajax',
					url: '/?c=CmpCallCard4E&m=loadSmpFarmacyRegister',
					reader: {
						type: 'json',
						successProperty: 'success',
						root: 'data'
					},
					actionMethods: {
						create : 'POST',
						read   : 'POST',
						update : 'POST',
						destroy: 'POST'
					}
				},
				listeners: {
					datachanged : function(){
						drugList.store.numLoad++
						if(drugList.store.numLoad != 1)
						drugList.down('toolbar button[itemId=refreshDrugListButton]').enable()
						drugList.down('toolbar button[itemId=writeOffDrugButton]').enable()
					}
				}
			}),
			columns: [
				{ dataIndex: 'Drug_id', text: 'ID', key: true, hidden: true, hideable: false },
				{ dataIndex: 'AddDate', text: 'Дата пополнения', width: 120 },
				{ dataIndex: 'DDFGT', text: 'Наименование', flex: 1 },				
				{ dataIndex: 'Drug_PackName', text: 'Единица учета', width: 120 },				
				{ dataIndex: 'Drug_Fas', text: 'Кол-во в упаковке', width: 130 },
				{ dataIndex: 'CmpFarmacyBalance_PackRest', text: 'Остаток (ед. учета)', width: 130},
				{ dataIndex: 'CmpFarmacyBalance_DoseRest', text: 'Остаток (ед. доз)', width: 130 }
				
			],
			listeners: {
				beforecellclick: function( cmp, td, cellIndex, record, tr, rowIndex, e, eOpts )
				{
					this.down('toolbar button[itemId=writeOffDrugButton]').enable()
					if (record.get('CmpFarmacyBalance_id'))
					{
						drugListHistory.store.load({
						params:{
								'CmpFarmacyBalance_id' : record.get('CmpFarmacyBalance_id'),
								start: 0, limit: 50
						}
						})
					}
				}
			}
		})
		
		drugListHistory = Ext.create('Ext.grid.Panel', {
			//region: 'south',
			title: 'История списания медикамента',
			autoScroll: true,
			stripeRows: true,
			flex: 1,
			refId: 'drugListHistoryGrid',
			viewConfig: {
				loadingText: 'Загрузка'
			},

			store: new Ext.data.JsonStore({
				autoLoad: false,
				storeId: 'drugListHistoryGridStore',
				fields: [	
					{name: 'CmpFarmacyBalanceRemoveHistory_id', type: 'int'},
					{name: 'DrugTorg_Name', type: 'string'},
					{name: 'EmergencyTeam_Num', type: 'string'},					
					{name: 'Person_Fin', type: 'string'},
					{name: 'CmpCallCard_prmDate', type: 'string'},
					{name: 'CmpFarmacyBalanceRemoveHistory_DoseCount', type: 'int'},					
					{name: 'CmpFarmacyBalanceRemoveHistory_PackCount', type: 'string'}
				],
				proxy: {
					limitParam: undefined,
					startParam: undefined,
					paramName: undefined,
					pageParam: undefined,
					type: 'ajax',
					url: '/?c=CmpCallCard4E&m=loadSmpFarmacyRegisterHistory',
					reader: {
						type: 'json',
						successProperty: 'success',
						root: 'data'
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
				{ dataIndex: 'CmpFarmacyBalanceRemoveHistory_id', text: 'ID', key: true, hidden: true, hideable: false },
				{ dataIndex: 'DrugTorg_Name', text: 'Название медикамента', flex: 1 },
				{ dataIndex: 'EmergencyTeam_Num', text: '№ бригады', width: 90 },				
				{ dataIndex: 'Person_Fin', text: 'Старший бригады', flex: 1 },				
				{ dataIndex: 'CmpCallCard_prmDate', text: 'Дата списания', width: 110 },
				{ dataIndex: 'CmpFarmacyBalanceRemoveHistory_DoseCount', text: 'Кол-во доз', width: 80 },
				{ dataIndex: 'CmpFarmacyBalanceRemoveHistory_PackCount', text: 'Кол-во (ед. учета)', width: 120 }
				
			]
		})
		
		Ext.applyIf(me, {
			items: [
				drugList,
				drugListHistory
			],
			bbar: [
				'->',
				{
					xtype: 'button',					
					text: 'Помощь',
					iconCls   : 'help16',
					tabIndex: 30,
					handler   : function()
					{
						ShowHelp(me.title);												
					}
				},
				{
					xtype: 'button',
					//id: 'cancelEmergencyTeamDutyTimeGrid',
					iconCls: 'cancel16',
					text: 'Закрыть',
					handler: function(){
						me.close()
					}
				}
			]
		})
		
		me.callParent()
	}
})

