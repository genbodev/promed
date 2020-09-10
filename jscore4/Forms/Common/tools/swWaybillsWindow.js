/* 
	Учет путевых листов и ГСМ
*/


Ext.define('sw.tools.swWaybillsWindow', {
	alias: 'widget.swWaybillsWindow',
	extend: 'Ext.window.Window',
	title: 'Учет путевых листов и ГСМ',
	width: 600,
	height: 300,
	//maximizable: true,
	modal: true,
	layout: {
        align: 'stretch',
        type: 'vbox'
    },
	initComponent: function() {
		var me = this;
		
		var cald = Ext.create('sw.datePickerRange', {
			maxValue: 'unlimited',
			dateFields: ['dateStart', 'dateFinish'],
			refId: 'dateCalendar'
			});
		
			me.tbar = Ext.create('Ext.toolbar.Toolbar', {
			region: 'north',
			items: [
				Ext.create('sw.datePrevDay', {width: 100}),
				cald,
				Ext.create('sw.dateNextDay', {width: 100}),
				Ext.create('sw.dateCurrentDay', {
					enableToggle: true,
					toggleGroup: 'dateGroup'
				}),
				Ext.create('sw.dateCurrentWeek', {
					enableToggle: true,
					toggleGroup: 'dateGroup'
				}),
				Ext.create('sw.dateCurrentMonth', {
					enableToggle: true,
					toggleGroup: 'dateGroup'
				})
			]
		});
		
		
		
		var swWaybillsGrid = Ext.create('Ext.grid.Panel', {
			viewConfig: {
				loadingText: 'Загрузка'
			},
			flex: 1,
			autoScroll: true,
			stripeRows: true,
			refId: 'waybillGrid',
			tbar: [
				{
					xtype: 'button',
					itemId: 'addWaybillsGridButton',
					disabled: false,
					text: 'Добавить',
					iconCls: 'add16',
					handler: function(){
						var daterange = me.down('toolbar datePickerRange'),
							conf = {
								action: 'add',
								dateFrom: daterange.dateFrom,
								dateTo: daterange.dateTo
							};
							
						Ext.create('sw.tools.subtools.swSmpWaybillsEditWindow', conf).show()
					}
				},
				{
					xtype: 'button',
					itemId: 'viewWaybillsGridButton',
					disabled: false,
					text: 'Просмотр',
					iconCls: 'view16',
					handler: function(){
						var rec = swWaybillsGrid.getSelectionModel().getSelection()[0],
							daterange = me.down('toolbar datePickerRange'),
							conf = {
								action: 'view',
								wayBillsId: rec.get('Waybill_id'),
								dateFrom: daterange.dateFrom,
								dateTo: daterange.dateTo
							};
							
						if (typeof rec != 'undefined'){
							Ext.create('sw.tools.subtools.swSmpWaybillsEditWindow', conf).show();
						}
						/*
						if (typeof rec != 'undefined'){
							Ext.create('sw.tools.subtools.swSmpWaybillsEditWindow', {action: 'edit', wayBillsId: rec.get('Waybill_id')}).show()
						}*/
					}
				},
				{
					xtype: 'button',
					itemId: 'editWaybillsGridButton',
					disabled: false,
					text: 'Изменить',
					iconCls: 'edit16',
					handler: function(){
						var rec = swWaybillsGrid.getSelectionModel().getSelection()[0],
							daterange = me.down('toolbar datePickerRange'),
							conf = {
								action: 'edit',
								wayBillsId: rec.get('Waybill_id'),
								dateFrom: daterange.dateFrom,
								dateTo: daterange.dateTo
							};
							
						if (typeof rec != 'undefined'){
							Ext.create('sw.tools.subtools.swSmpWaybillsEditWindow', conf).show();
						}
						/*
						if (typeof rec != 'undefined'){
							Ext.create('sw.tools.subtools.swSmpWaybillsEditWindow', {action: 'edit', wayBillsId: rec.get('Waybill_id')}).show()
						}*/
					}
				},
				{
					xtype: 'button',
					itemId: 'refreshWaybillsGridButton',
					disabled: false,
					text: 'Обновить',
					iconCls: 'refresh16',
					handler: function(){
						swWaybillsGrid.store.reload()
					}
				},
				{
					xtype: 'button',
					itemId: 'printWaybillsGrid',
					text: 'Печать',
					iconCls: 'print16',
					handler: function(){
						var rec = swWaybillsGrid.getSelectionModel().getSelection()[0];
						if (typeof rec != 'undefined'){
							var win = window.open('?c=Waybill&m=printWaybill&Waybill_id='+rec.get('Waybill_id'));
							//Ext.ux.grid.Printer.print(swWaybillsGrid)
						}
					}
				}
			],
			store: new Ext.data.JsonStore({
				autoLoad: true,
				numLoad: 0,
				storeId: 'WaybillsStore',
				fields: [
					{name: 'Waybill_id', type: 'int'},
					{name: 'Waybill_Num', type: 'int'},
					{name: 'Waybill_Date', type: 'string'},					
					{name: 'EmergencyTeam_Driver', type: 'string'},
					{name: 'EmergencyTeam_CarNum', type: 'string'}
				],				
				proxy: {
					limitParam: undefined,
					startParam: undefined,
					paramName: undefined,
					pageParam: undefined,
					type: 'ajax',
					url: '/?c=Waybill4E&m=loadWaybillGrid',
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
					},
					extraParams:{
						dateFinish:	Ext.Date.format(cald.dateTo, 'd.m.Y'),
						dateStart:	Ext.Date.format(cald.dateFrom, 'd.m.Y')
					}
				},
				sorters: {
					property: 'EmergencyTeamDuty_DTStart',
					direction: 'ASC'
				}
			}),
			columns: [
				{ dataIndex: 'Waybill_id', text: 'ID', key: true, hidden: true, hideable: false },
				{ dataIndex: 'Waybill_Num', text: 'Номер', key: true },
				{ dataIndex: 'Waybill_Date', text: 'Дата', width: 100 },
				{ dataIndex: 'EmergencyTeam_Driver', text: 'Водитель', flex: 1 },
				{ dataIndex: 'EmergencyTeam_CarNum', text: 'Номер машины', width: 100 }			
			]
		})		
		
		Ext.applyIf(me, {
			items: [
				swWaybillsGrid
			],
			
			 dockedItems: [
                {
                    xtype: 'container',
                    dock: 'bottom',
					
                    layout: {
						align: 'right',
                        type: 'vbox',
						padding: 3
                    },
                    items: [
						{
						xtype: 'container',
						height: 30,
						layout: {
							align: 'middle',
							pack: 'center',
							type: 'hbox'
						},
							items: [
//								{
//									xtype: 'button',
//									id: 'saveBtn',
//									iconCls: 'save16',
//									text: 'Сохранить',
//									handler: function(){
//										me.saveBrigTime(me)
//									}
//								},
								 
								{
									xtype: 'button',
									id: 'helpBtn',
									text: 'Помощь',
									iconCls   : 'help16',
									handler   : function()
									{
										ShowHelp(me.title);
									}
								},
								{
									xtype: 'button',
									id: 'cancelBtn',
									iconCls: 'cancel16',
									text: 'Закрыть',
									margin: '0 5',
									handler: function(){
										me.close()
									}
								}
							]
						}
                    ]
                }
            ]
		});
		
		me.callParent();
	}
})

