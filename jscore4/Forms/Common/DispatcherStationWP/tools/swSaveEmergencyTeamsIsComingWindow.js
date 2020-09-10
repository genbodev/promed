/* 
	Выход на смену
*/

Ext.define('common.DispatcherStationWP.tools.swSaveEmergencyTeamsIsComingWindow', {
	alias: 'widget.swSaveEmergencyTeamsIsComingWindow',
	extend: 'sw.standartToolsWindow',
	title: 'Выход на смену',
	width: 800,
	height: 400,
	onEsc: Ext.emptyFn,
	cls: 'swSaveEmergencyTeamsIsComingWindow',
	defaultFocus: 'gridpanel[refId=swSelectEmergencyTeamGrid]',
	initComponent: function() {
		var win = this,
			conf = win.initialConfig,
			globals = getGlobalOptions();
		
		win.saveButton = Ext.create('Ext.button.Button', {
			hidden: false,
			text: 'Сохранить отметку о выходе',
			iconCls: 'save16',
			iconAlign: 'right',
			refId: 'saveButton',
			step:0,
			handler: function(){				
				var selection = win.swEmergencyTeamGrid.store.getUpdatedRecords();
				var selectedIds = []
				for (var i = 0;i<selection.length;i++) {
					if (!selection[i].data.locked) {
						selectedIds.push({
							EmergencyTeam_id:selection[i].data.EmergencyTeam_id, 
							EmergencyTeamDuty_id:selection[i].data.EmergencyTeamDuty_id
						});
					}
				}
				if (selectedIds.length == 0) {
					Ext.Msg.alert('Внимание', 'Не выбрано ни одной не сохраненной отметки о выходе на смену');
					return false;
				}
				Ext.Ajax.request({
					url:'/?c=EmergencyTeam4E&m=saveEmergencyTeamsIsComingToWorgFlag',
					params: {
						selectedEmergencyTeamIds:Ext.encode(selectedIds)
					},
					callback: function(opt, success, response) {
						if (success){
							var res = Ext.JSON.decode(response.responseText);
							if (res.Error_Msg && res.Error_Msg.length>0) {
								Ext.Msg.alert('Ошибка сохранения отметки о выходе на смену', res.Error_Msg);
							}
							win.swEmergencyTeamGrid.store.load();
						}
					}
				})
			}
		});
		
		win.topTbar = Ext.create('Ext.toolbar.Toolbar', {
			items: [
				win.saveButton
			]
		});
		
		win.swEmergencyTeamGrid = Ext.create('Ext.grid.Panel', {
			flex: 1,
			stripeRows: true,
			refId: 'swSelectEmergencyTeamGrid',
			viewConfig: {
				loadingText: 'Загрузка',
				markDirty: false,
				cls: 'swSelectEmergencyTeamGrid'
			},

			store: new Ext.data.JsonStore({
				autoLoad: true,
				storeId: 'SelectEmergencyTeamsStore',
				fields: [
					{name: 'EmergencyTeam_id', type: 'int'},
					{name: 'EmergencyTeam_Num', type: 'string'},
					{name: 'EmergencyTeam_CarNum', type: 'string'},					
					{name: 'EmergencyTeam_CarBrand', type: 'string'},
					{name: 'EmergencyTeamStatus_id', type: 'int'},
					{name: 'EmergencyTeam_IsOnline', type: 'string'},
					{name: 'Lpu_id', type: 'int'},
					
					{name: 'EmergencyTeam_CarModel', type: 'string'},
					{name: 'EmergencyTeam_PortRadioNum', type: 'int'},
					
					{name: 'EmergencyTeam_GpsNum', type: 'string'},					
					{name: 'LpuBuilding_id', type: 'string'},
					{name: 'LpuBuilding_Nick', type: 'string'},
					{name: 'EmergencyTeamSpec_id', type: 'int'},					
					
					{name: 'EmergencyTeam_HeadShift', type: 'int', convert: null},
					{name: 'EmergencyTeam_HeadShift2', type: 'int', convert: null},					
					{name: 'EmergencyTeam_Driver', type: 'int', convert: null},
					{name: 'EmergencyTeam_Driver2', type: 'int', convert: null},					
					{name: 'EmergencyTeam_Assistant1', type: 'int', convert: null},
					{name: 'EmergencyTeam_Assistant2', type: 'int', convert: null},
					{name: 'EmergencyTeamSpec_id', type: 'int'},
					
					{name: 'EmergencyTeam_HeadShiftFIO', type: 'string'},
					{name: 'EmergencyTeam_HeadShift2FIO', type: 'string'},					
					{name: 'EmergencyTeam_DriverFIO', type: 'string'},
					{name: 'EmergencyTeam_Driver2FIO', type: 'string'},					
					{name: 'EmergencyTeam_Assistant1FIO', type: 'string'},
					{name: 'EmergencyTeam_Assistant2FIO', type: 'string'},
					{name: 'EmergencyTeam_DutyTime', type: 'string'},
					{name: 'GeoserviceTransport_id', type: 'int'},
					{name: 'EmergencyTeamDuty_id', type: 'int'},
					{name: 'EmergencyTeam_HeadShiftPost', type: 'string', defaultValue: 'Старший смены'},
					
					{name: 'EmergencyTeamDuty_DTStart',
						convert: function(v, record){
							var e = Ext.Date.parse(v, "Y-m-d H:i:s")
							return Ext.Date.format(e, 'H:i')
						}
					},
					{name: 'EmergencyTeamDuty_DTFinish',
						convert: function(v, record){
							var e = Ext.Date.parse(v, "Y-m-d H:i:s")
							return Ext.Date.format(e, 'H:i')
						}
					},
					{name: 'medPersonCount', type: 'int'},
					{name: 'EmergencyTeamSpec_Code', type: 'string'},
					{name: 'checked', type: 'boolean', defaultValue: false},
					{name: 'GeoserviceTransport_name', type: 'string'}
				],
				proxy: {
					limitParam: undefined,
					startParam: undefined,
					paramName: undefined,
					pageParam: undefined,
					type: 'ajax',
					url: '/?c=EmergencyTeam4E&m=loadEmergencyTeamShiftList',
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
						dateFinish:	Ext.Date.format(new Date, 'd.m.Y'),
						dateStart:	Ext.Date.format(new Date, 'd.m.Y')
					}
				},
				listeners: {
					load: function(c, records, successful, eOpts){
						if(!successful)	c.removeAll()
						else{
							win.swEmergencyTeamGrid.getSelectionModel().select(0);
						}
					}
				}
			}),
			columns: [
				{ dataIndex: 'checked', text: '', width: 55, xtype: 'checkcolumn', hideable: false, sortable: false,
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
					]
					,listeners: {			
						headerclick: function( ct, column, e, t, eOpts ){
							var el = Ext.fly(column.getId()).select('div.customCheckAll span').first(),
								store = ct.view.store;
						
							el.toggleCls('checkedall');
							store.each(function(record){
								record.set('checked', el.hasCls('checkedall'));
							});
						}
					}
				},
				{ dataIndex: 'EmergencyTeam_id', text: 'ID', key: true, hidden: true, hideable: false },
				{ dataIndex: 'EmergencyTeam_Num', text: 'Номер', width: 50, hideable: false },				
				{ dataIndex: 'EmergencyTeamSpec_Code', text: 'Профиль',  width: 100, hideable: false },
				{ dataIndex: 'GeoserviceTransport_name', text: 'Виалон', flex: 1, hideable: false },
				{ dataIndex: 'EmergencyTeam_HeadShiftPost', text: 'Должность', width: 150, hideable: false },
				{ dataIndex: 'EmergencyTeamDuty_DTStart', text: 'Смена с', width: 80, hideable: false },
				{ dataIndex: 'EmergencyTeamDuty_DTFinish', text: 'Смена по', width: 80, hideable: false },
				{ dataIndex: 'EmergencyTeam_HeadShiftFIO', text: 'ФИО', flex: 1, hideable: false }
			]
		});

		win.gridContainer = Ext.create('Ext.container.Container', {
			layout: 'fit',
			items: win.swEmergencyTeamGrid
		});
		
		//отправляем сборку
		win.configComponents = {
			top: win.topTbar,
			center: [win.gridContainer],
			//subBottomItems: [],
			//leftButtons: win.printButton
		}
		
		win.callParent(arguments);
	}
})

