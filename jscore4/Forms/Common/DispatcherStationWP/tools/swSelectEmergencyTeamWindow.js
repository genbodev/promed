/* 
	Выбрать бригаду
*/

Ext.define('common.DispatcherStationWP.tools.swSelectEmergencyTeamWindow', {
	alias: 'widget.swSelectEmergencyTeamWindow',
	extend: 'sw.standartToolsWindow',
	title: 'Копировать день/наряд',
	width: 800,
	height: 400,
	onEsc: Ext.emptyFn,
	cls: 'swSelectEmergencyTeamWindow',
	defaultFocus: 'gridpanel[refId=swSelectEmergencyTeamGrid]',
	initComponent: function() {
		var win = this,
			conf = win.initialConfig,
			globals = getGlobalOptions();
		
		win.cald = Ext.create('sw.datePickerRange', {
			maxValue: 'unlimited',
			dateFields: ['dateFinish', 'dateStart']
		});
		
		win.addEvents({
			selectTeams: true,
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

			tbar: Ext.create('Ext.toolbar.Toolbar', {
				items: [
					Ext.create('sw.datePrevDay'),
					win.cald,
					Ext.create('sw.dateNextDay'),
					{
						xtype: 'button',
						text: 'Выбрать',
						iconCls: 'add16',
						handler: function(){
							win.returnSelectedRecords();
						}
					}
				]
			}),
			listeners: {

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
					{name: 'LpuBuilding_Name', type: 'string'},
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
					
					{name: 'EmergencyTeam_Head1StartTime', type: 'string'},
					{name: 'EmergencyTeam_Head1FinishTime', type: 'string'},
					{name: 'EmergencyTeam_Head2StartTime', type: 'string'},
					{name: 'EmergencyTeam_Head2FinishTime', type: 'string'},
					{name: 'EmergencyTeam_Assistant1StartTime', type: 'string'},
					{name: 'EmergencyTeam_Assistant1FinishTime', type: 'string'},
					{name: 'EmergencyTeam_Assistant2StartTime', type: 'string'},
					{name: 'EmergencyTeam_Assistant2FinishTime', type: 'string'},
					{name: 'EmergencyTeam_Driver1StartTime', type: 'string'},
					{name: 'EmergencyTeam_Driver1FinishTime', type: 'string'},
					{name: 'EmergencyTeam_Driver2StartTime', type: 'string'},
					{name: 'EmergencyTeam_Driver2FinishTime', type: 'string'},
					
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
						dateFinish:	Ext.Date.format(win.cald.dateTo, 'd.m.Y'),
						dateStart:	Ext.Date.format(win.cald.dateFrom, 'd.m.Y'),
						// запросим список нарядов тех подразделений СМП, которые были выбраны  пользователем при входе в АРМ в форме «Выбор подстанций для управления»
						// если этот параметр не отправлять, то загрузится только текущий
						loadSelectSmp: true 
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
				{ dataIndex: 'LpuBuilding_Name', text: 'Подразделение СМП', flex: 1, hideable: false },
				{ dataIndex: 'EmergencyTeam_Num', text: 'Номер бригады', width: 100, hideable: false },				
				{ dataIndex: 'EmergencyTeamSpec_Code', text: 'Профиль',  width: 100, hideable: false },
				{ dataIndex: 'GeoserviceTransport_name', text: 'GPS/ГЛОНАСС', flex: 1, hideable: false },
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
			//top: win.topTbar,
			center: [win.gridContainer],
			//subBottomItems: [],
			//leftButtons: win.printButton
		}
		
		win.callParent(arguments);
	},
	
	returnSelectedRecords: function(){
		var arrRecs = [],
			win = this;
			
		win.swEmergencyTeamGrid.store.each(function(r){
			if(r.get('checked')){
				arrRecs.push(r);
			}
		});
		win.fireEvent('selectTeams', arrRecs);
	}
})

