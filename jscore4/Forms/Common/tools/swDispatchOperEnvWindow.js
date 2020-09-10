/* 
Оперативная обстановка по диспетчерам СМП
*/


Ext.define('sw.tools.swDispatchOperEnvWindow', {
	alias: 'widget.swDispatchOperEnvWindow',
	extend: 'Ext.window.Window',
	title: 'Оперативная обстановка по диспетчерам СМП',
	width: 800,
	height: 300,
	//maximizable: true,
	modal: true,
	layout: {
        align: 'stretch',
        type: 'vbox'
    },
	initComponent: function() {
		var me = this;
		
		var dispatchOperEnvWindowGrid = Ext.create('Ext.grid.Panel', {
			viewConfig: {
				loadingText: 'Загрузка'
			},
			flex: 1,
			autoScroll: true,
			stripeRows: true,
			refId: 'dispatchOperEnvWindowGrid',

			store: new Ext.data.JsonStore({
				autoLoad: true,
				numLoad: 0,
				storeId: 'dispatchOperEnvWindowGridStore',
				fields: [
					{name: 'pmUser_id', type: 'int'},
//					{name: 'EmergencyTeamDuty_id', type: 'int'},
					{name: 'Lpu_Name', type: 'string'},
					{name: 'pmUser_name', type: 'string'},					
					{name: 'online', type: 'string'}
//					{name: 'EmergencyTeam_HeadShiftFIO', type: 'string'},
//					{name: 'EmergencyTeamDuty_factToWorkDT', type: 'string'},
//					{name: 'ComesToWork', type: 'boolean'}
				],				
				proxy: {
					limitParam: undefined,
					startParam: undefined,
					paramName: undefined,
					pageParam: undefined,
					type: 'ajax',
					url: '/?c=EmergencyTeam4E&m=loadDispatchOperEnv',
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
//					,extraParams:{
//						dateFinish:	Ext.Date.format(cald.dateTo, 'd.m.Y'),
//						dateStart:	Ext.Date.format(cald.dateFrom, 'd.m.Y')
//					}
				},
				sorters: {
					property: 'EmergencyTeamDuty_DTStart',
					direction: 'ASC'
				},
				filters: [{
					property: 'online',
					value: 'true'
				}] 
			}),
			columns: [
				{ dataIndex: 'pmUser_id', text: 'ID', hidden: true, hideable: false },
				{ dataIndex: 'Lpu_Name', text: 'ЛПУ', hidden: false, flex:2 },
				{ dataIndex: 'pmUser_name', text: 'Диспетчер', hidden: false, flex:1},
				{ dataIndex: 'online', text: 'онлайн', hidden: false, width: 60,
					renderer: function(value){
						if (value == 'true') {
							return '<div class="x-grid3-check-on x-grid3-cc-ext-gen2118"></div>';
						}
						else{
							return ''
						}
					}
				}
			]
		});
		
		var checkViewOnline = Ext.create('Ext.button.Button', {
			id: 'showAll',
			text: 'Все диспетчеры',
			width: 100,
			online: true,
			handler: function(){				
				if (this.online){
					this.setText('Онлайн');
					this.online = false;
					dispatchOperEnvWindowGrid.store.clearFilter();
				}
				else{
					this.setText('Все диспетчеры');
					this.online = true;
					dispatchOperEnvWindowGrid.store.filter('online', 'true');
					
				}
			}
		});
		
		Ext.applyIf(me, {
			items: [
				dispatchOperEnvWindowGrid
			],
			
			 dockedItems: [
				                 {
                    xtype: 'container',
                    flex: 1,
					padding: 3,
                    dock: 'bottom',
                    layout: {
                        type: 'hbox',
                        align: 'stretch'
                    },
                    items: [
                        {
                            xtype: 'container',
                            layout: {
                                type: 'vbox',
                                align: 'stretch'
                            },
                            items: [
                                checkViewOnline
                            ]
                        },
                        {
                            xtype: 'container',
                            flex: 1,
                            layout: {
                                type: 'hbox',
                                align: 'right',
                                pack: 'end'
                            },
                            items: [                               
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
/*                {
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
								{
									xtype: 'button',
									id: 'saveBtn',
									//iconCls: 'save16',
									text: 'Все диспетчеры',
									handler: function(){
										me.saveBrigTime(me)
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
								},
								{
									xtype: 'button',
									id: 'helpBtn',
									text: 'Помощь',
									iconCls   : 'help16',
									handler   : function()
									{
										ShowHelp(this.ownerCt.title);
									}
								}
							]
						}
                    ]
                }*/
            ]
		});
		
		me.callParent();
	}
})

