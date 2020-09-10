/* 
История бригад СМП
*/


Ext.define('sw.tools.swBrigadesHistory', {
	alias: 'widget.swBrigadesHistory',
	extend: 'Ext.window.Window',
	EmergencyTeam_id: '',
	EmergencyTeam_Num: '',
	LpuBuilding_Name: '',
	title: 'История бригады',
	width: 800,
	height: 300,
	modal: true,
	layout: {
        align: 'stretch',
        type: 'vbox'
    },

	initComponent: function() {
		var me = this;
		var brigadesHistoryWindowGrid = Ext.create('Ext.grid.Panel', {
			viewConfig: {
				loadingText: 'Загрузка'
			},
			flex: 1,
			autoScroll: true,
			stripeRows: true,
			title: 'бригада №'+this.EmergencyTeam_Num+'. &nbsp;'+this.LpuBuilding_Name,
			refId: 'brigadesHistoryWindowGrid',

			store: new Ext.data.JsonStore({
				autoLoad: true,
				numLoad: 0,
				storeId: 'brigadesHistoryWindowGridStore',
				fields: [
					{name: 'EmergencyTeam_id', type: 'int'},
					{name: 'setTime', type: 'string'},
					{name: 'nameStatus', type: 'string'},					
					{name: 'callNum', type: 'string'}
				],				
				proxy: {
					type: 'ajax',
					url: '/?c=CmpCallCard4E&m=loadBrigadesHistory',
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
					,extraParams:{
						EmergencyTeam_id: me.EmergencyTeam_id
					}
				}
			}),
			columns: [
				{ dataIndex: 'EmergencyTeam_id', text: 'ID', hidden: true, hideable: false },
				{ dataIndex: 'setTime', text: 'Время', hidden: false, flex:1 },
				{ dataIndex: 'nameStatus', text: 'Статус', hidden: false, flex:1},
				{ dataIndex: 'callNum', text: 'Номер вызова', hidden: false, width: 140}
			]
		});

		Ext.applyIf(me, {
			items: [
				brigadesHistoryWindowGrid
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
                            flex: 1,
                            layout: {
                                type: 'hbox',
                                align: 'right',
                                pack: 'end'
                            },
                            items: [ 
								{
									xtype: 'button',
									id: 'cancelBtn',
									iconCls: 'cancel16',
									text: 'Закрыть',
									margin: '0 5',
									handler: function(){
										me.close();
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

});

