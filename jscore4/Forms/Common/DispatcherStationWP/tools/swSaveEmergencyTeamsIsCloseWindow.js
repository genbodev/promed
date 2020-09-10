

/* 
Отметка
*/


Ext.define('common.DispatcherStationWP.tools.swSaveEmergencyTeamsIsCloseWindow', {
	alias: 'widget.swSaveEmergencyTeamsIsCloseWindow',
	extend: 'Ext.window.Window',
	title: 'Закрытие смен',
	width: 1000,
	height: 600,
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
			dateFields: ['dateStart', 'dateFinish']
			});
		
			me.tbar = Ext.create('Ext.toolbar.Toolbar', {
			region: 'north',
			items: [
				Ext.create('sw.datePrevDay'),
				cald,
				Ext.create('sw.dateNextDay'),
				Ext.create('sw.dateCurrentDay')
			]
		});
		
		var emergencyTeamDutyTimeGrid = Ext.create('Ext.grid.Panel', {
			plugins: new Ext.grid.plugin.CellEditing({
				clicksToEdit: 1
			}),
			viewConfig: {
				loadingText: 'Загрузка'
			},
			flex: 1,
			autoScroll: true,
			stripeRows: true,
			refId: 'emergencyTeamDutyTimeGrid',
			tbar: [
				{
					xtype: 'button',
					itemId: 'refreshEmergencyTeamDutyTimeGridButton',
					disabled: false,
					text: 'Обновить',
					iconCls: 'refresh16',
					handler: function(){
						emergencyTeamDutyTimeGrid.store.reload()
					}
				},
				{
					xtype: 'button',
					itemId: 'printEmergencyTeamDutyTimeGrid',
					text: 'Печать формы за выбранный период',
					iconCls: 'print16',
					handler: function(){
						//Ext.ux.grid.Printer.print(emergencyTeamDutyTimeGrid)
						var id_salt = Math.random();
						var win_id = 'print_110u' + Math.floor(id_salt * 10000);
						var win = window.open('/?c=EmergencyTeam4E&m=printCloseDuty&dateStart=' + Ext.Date.format(cald.dateFrom, 'd.m.Y') + '&dateFinish=' + Ext.Date.format(cald.dateTo, 'd.m.Y'), win_id);
					}
				}
			],
			store: new Ext.data.JsonStore({
				autoLoad: true,
				numLoad: 0,
				storeId: 'EmergencyTeamDutyTimeStore',
				fields: [
					{name: 'EmergencyTeam_id', type: 'int'},
					{name: 'EmergencyTeamDuty_id', type: 'int'},
					{name: 'EmergencyTeam_Num', type: 'string'},
					{name: 'EmergencyTeamDuty_DTStart', type: 'string'},					
					{name: 'EmergencyTeamDuty_DStart', type: 'string'},					
					{name: 'EmergencyTeamDuty_TStart', type: 'string'},					
					{name: 'EmergencyTeamDuty_DTFinish', type: 'string'},
					{name: 'EmergencyTeamDuty_DFinish', type: 'string'},
					{name: 'EmergencyTeamDuty_TFinish', type: 'string'},
					
					{name: 'EmergencyTeam_HeadShift', type: 'int', convert: null},
					{name: 'EmergencyTeam_HeadShift2', type: 'int', convert: null},					
					{name: 'EmergencyTeam_Driver', type: 'int', convert: null},
					{name: 'EmergencyTeam_Driver2', type: 'int', convert: null},					
					{name: 'EmergencyTeam_Assistant1', type: 'int', convert: null},
					{name: 'EmergencyTeam_Assistant2', type: 'int', convert: null},
					
					{name: 'EmergencyTeam_HeadShiftFIO', type: 'string'},
					{name: 'EmergencyTeam_HeadShift2FIO', type: 'string'},					
					{name: 'EmergencyTeam_DriverFIO', type: 'string'},
					{name: 'EmergencyTeam_Driver2FIO', type: 'string'},					
					{name: 'EmergencyTeam_Assistant1FIO', type: 'string'},
					{name: 'EmergencyTeam_Assistant2FIO', type: 'string'},
					
					{name: 'EmergencyTeamDuty_factToWorkDT', type: 'string'},
					{name: 'closed', type: 'boolean'}
				],				
				proxy: {
					limitParam: undefined,
					startParam: undefined,
					paramName: undefined,
					pageParam: undefined,
					type: 'ajax',
					url: '/?c=EmergencyTeam4E&m=loadEmergencyTeamDutyTimeListGrid',
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
				},
				listeners: {
					load : function(cmp, records, successful, eOpts){
						cmp.each(function(record){
							var d = null;
							if(record.get('EmergencyTeamDuty_factToWorkDT')){
								d = Ext.Date.parse(record.get('EmergencyTeamDuty_factToWorkDT'), 'Y-m-d H:i:s');
							}
							else{
								d = Ext.Date.parse(record.get('EmergencyTeamDuty_DTStart'), 'Y-m-d H:i:s');
							}
							record.set('EmergencyTeamDuty_factToWorkDT', Ext.Date.format(d, "d-m-Y H:i:s"));
							emergencyTeamDutyTimeGrid.store.commitChanges();
						});
					}
				}
			}),
			columns: [
				{ dataIndex: 'EmergencyTeam_id', text: 'ID', key: true, hidden: true, hideable: false },
				{ dataIndex: 'EmergencyTeamDuty_id', text: 'IDduty', key: true, hidden: true, hideable: false  },
				{ dataIndex: 'EmergencyTeam_Num', text: 'Номер бригады', width: 100, hideable: false},
				{ dataIndex: 'EmergencyTeam_HeadShiftFIO', text: 'Старший смены', flex: 1, hideable: false},				
				{ dataIndex: 'EmergencyTeam_HeadShift2FIO', text: 'Старший смены', flex: 1, hideable: false},				
				{ dataIndex: 'EmergencyTeam_DriverFIO', text: 'Водитель', flex: 1, hideable: false },
				{ dataIndex: 'EmergencyTeam_Driver2FIO', text: 'Водитель', flex: 1, hideable: false },
				{ dataIndex: 'EmergencyTeam_Assistant1FIO', text: 'Помошник 1', flex: 1, hideable: false },
				{ dataIndex: 'EmergencyTeam_Assistant2FIO', text: 'Помошник 2', flex: 1, hideable: false },
				
				{ dataIndex: 'EmergencyTeamDuty_DTStart', text: 'Начало2', flex: 1, hidden: true, hideable: false },
				{ dataIndex: 'EmergencyTeamDuty_DTFinish', text: 'Конец', width: 150, hidden: true, hideable: false},
				{ dataIndex: 'EmergencyTeamDuty_DStart', text: 'Дата начала', width: 100, hideable: false},
				{ dataIndex: 'EmergencyTeamDuty_TStart', text: 'Время начала', width: 100, hideable: false},
				{ dataIndex: 'EmergencyTeamDuty_DFinish', text: 'Дата окончания', width: 100, hideable: false},
				{ dataIndex: 'EmergencyTeamDuty_TFinish', text: 'Время окончания', width: 100, hideable: false},
				{ dataIndex: 'EmergencyTeamDuty_factToWorkDT', text: 'Начало фактически', width: 150, hideable: false},
				{ dataIndex: 'closed', text: '', width: 100, xtype: 'checkcolumn', hideable: false, sortable: false,
					renderTpl: [
						'<div id="{id}-titleEl" role="presentation" {tipMarkup}class="', Ext.baseCSSPrefix, 'column-header-inner',
							'<tpl if="empty"> ', Ext.baseCSSPrefix, 'column-header-inner-empty</tpl>">',
							'<div class="customCheckAll">',
								'<span class="">&nbsp;</span>',
							'</div>',
							'<span id="{id}-textEl" class="', Ext.baseCSSPrefix, 'column-header-text',
								'{childElCls}">',
								'{text}',
							'</span>',

							'<tpl if="!menuDisabled">',
								'<div id="{id}-triggerEl" role="presentation" class="', Ext.baseCSSPrefix, 'column-header-trigger',
								'{childElCls}"></div>',
							'</tpl>',
						'</div>',
						'{%this.renderContainer(out,values)%}'
					],
					listeners: {
						headerclick: function( ct, column, e, t, eOpts ){
							var el = Ext.fly(column.getId()).select('div.customCheckAll span').first(),
								store = ct.view.store;
							el.toggleCls('checkedall');
							
							store.each(function(record){
								record.set('closed', el.hasCls('checkedall'));
				            });
						}
					}
				}				
			]
		})		
		
		Ext.applyIf(me, {
			items: [
				emergencyTeamDutyTimeGrid
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
								{
									xtype: 'button',
									iconCls: 'save16',
									text: 'Сохранить',
									handler: function(){
										me.saveClosed(me)
									}
								},
								 {
									xtype: 'button',
									iconCls: 'cancel16',
									text: 'Отменить',
									margin: '0 5',
									handler: function(){
										me.close()
									}
								},
								{
									xtype: 'button',
									text: 'Помощь',
									iconCls   : 'help16',
									handler   : function()
									{
										ShowHelp(me.title);
									}
								}
							]
						}
                    ]
                }
            ]
		});
		
		me.callParent();
	},
	saveClosed : function(cmp){
		var grid = cmp.down('grid'),
			store = grid.store,
			editedRecs = [];
			
		store.each(function(record){
			if (record.dirty){
				editedRecs.push({
					EmergencyTeam_id: record.get('EmergencyTeam_id'),
					EmergencyTeamDuty_id: record.get('EmergencyTeamDuty_id'),
//					EmergencyTeamDuty_DTStart: record.get('EmergencyTeamDuty_DTStart'),
//					EmergencyTeamDuty_DTFinish: record.get('EmergencyTeamDuty_DTFinish'),					
//					EmergencyTeamDuty_factToWorkDT: record.get('EmergencyTeamDuty_factToWorkDT'),
					closed: record.get('closed')					
				})
			}
		});
		
		if (editedRecs.length > 0){
			Ext.Ajax.request({
				url: '/?c=EmergencyTeam4E&m=setEmergencyTeamsCloseList',
				params: {
					EmergencyTeamsClose: Ext.encode(editedRecs)
				},
				callback: function(opt, success, response) {
					if (success){
						store.commitChanges();
						Ext.Msg.alert('Сохранение', 'Изменения сохранены');
						function hide_message() {
							Ext.defer(function() {
								Ext.MessageBox.hide();
							}, 1500);
						}
						hide_message();
					}
				}
			})
		}
		else{
			
		}

	}
})

