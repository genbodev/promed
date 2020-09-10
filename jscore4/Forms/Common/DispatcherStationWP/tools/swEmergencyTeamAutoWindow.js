/* 
	Автомобили (бывшие Шаблоны)
*/

Ext.define('common.DispatcherStationWP.tools.swEmergencyTeamAutoWindow', {
	alias: 'widget.swEmergencyTeamAutoWindow',
	extend: 'sw.standartToolsWindow',
	title: 'Автомобили',
	width: 800,
	height: 400,
	
	setSelectTemplateWindowView: function(){
		var win = this,
			baseForm = win.down('form').getForm(),
			displayModeField = baseForm.findField('displayMode');

		win.swEmergencyTeamAutoGrid.columns[0].setVisible(true);
		win.swEmergencyTeamAutoGrid.getStore().getProxy().extraParams = {'display': 'opened'};
		
		displayModeField.setValue(1);
		displayModeField.setReadOnly(true);
	},
	
	initComponent: function() {
		var win = this,
			globals = getGlobalOptions(),
			conf = win.initialConfig;
			
		win.on('show', function(){
			if(conf.action == 'chooseTemplates'){
				//режим выбора шаблона
				win.setSelectTemplateWindowView();
			}
			win.swEmergencyTeamAutoGrid.store.load();
		});
		
		win.addEvents({
			//событие на выбор шаблонов
			selectTeamsFromTemplate: true
		});
		
			
		win.swEmergencyTeamAutoGrid = Ext.create('Ext.grid.Panel', {
			flex: 1,
			stripeRows: true,
			refId: 'swEmergencyTeamTemplateGP',
			viewConfig: {
				loadingText: 'Загрузка',
				markDirty: false				
			},
			listeners: {
				itemClick: function(cmp, record, item, index, e, eOpts ){
				
				},
				cellkeydown: function(cmp, td, cellIndex, record, tr, rowIndex, e, eOpts){

				},
				celldblclick: function( cmp, td, cellIndex, record, tr, rowIndex, e, eOpts ){
					//var btn = win.down('button[refId=viewTeamTemplate]');
					//btn.handler();
				}
			},
			store: new Ext.data.JsonStore({
				autoLoad: false,
				numLoad: 0,
				//storeId: 'EmergencyTeamTemplateStore',
				fields: [		
					{name: 'LpuBuilding_id', type: 'string'},
					{name: 'LpuBuilding_Name', type: 'string'},
					{name: 'MedProductClass_Name', type: 'string', mapping: 'MedProductClass_Name'},
					{name: 'EmergencyTeam_Num', type: 'string', mapping: 'MedProductCard_BoardNumber'},
					{name: 'EmergencyTeam_CarNum', type: 'string', mapping: 'AccountingData_RegNumber'},
					{name: 'EmergencyTeam_CarBrand', type: 'string', mapping: 'MedProductClass_Model'},
					{name: 'GeoserviceTransport_name', type: 'string'},					
					{name: 'GeoserviceTransport_id', type: 'int'},					
					{name: 'MedProductCard_id', type: 'int'},					
					{name: 'checked', type: 'boolean', defaultValue: false},
					{name: 'EmergencyTeamSpec_id', type: 'int'},					
					{name: 'EmergencyTeamDuty_ChangeComm', type: 'string'}
				],
				proxy: {
					limitParam: undefined,
					startParam: undefined,
					paramName: undefined,
					pageParam: undefined,
					type: 'ajax',
					url: '/?c=EmergencyTeam4E&m=loadEmergencyTeamAutoList',
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
					load: function(){
						win.swEmergencyTeamAutoGrid.getSelectionModel().select(0);
					}
				}
			}),
			columns: [
				{ dataIndex: 'checked', text: '', width: 55, xtype: 'checkcolumn', hidden: true, sortable: false},
				{ dataIndex: 'LpuBuilding_Name', text: 'Подразделение СМП', flex: 1, hideable: false },
				{ dataIndex: 'MedProductClass_Name', text: 'Автомобиль', width: 200, hideable: false },
				{ dataIndex: 'EmergencyTeam_Num', text: 'Бортовой номер', flex: 1, hideable: false },
				{ dataIndex: 'EmergencyTeam_CarNum', text: 'Гос. номер машины', width: 160, hideable: false },
				{ dataIndex: 'EmergencyTeam_CarBrand', text: 'Марка машины', width: 160, hideable: false },
				{ dataIndex: 'GeoserviceTransport_name', text: 'GPS/ГЛОНАСС', width: 200, hideable: false }
			]
		});
		
		win.topTbar = Ext.create('Ext.form.FieldSet', {
			itemId: 'winTopToolbar',
			title: 'Фильтры',
			items: [
				{
					xtype: 'BaseForm',
					id: this.id+'_mainPanel',
					border: false,
					frame: false,
					bodyBorder: false,
					margin: 5,
					layout: {
						type: 'vbox',
						align: 'stretch'
					},
					items: 
					[
						{
							layout: {
								type: 'hbox',
								align: 'stretch'
							},
							flex: 1,
							xtype: 'container',
							defaults: {
								labelWidth: 100,
								//width: 300,
								flex: 1,
								margin: '2 10',
								labelAlign: 'right',
								keyNavEnabled: false,
								mouseWheelEnabled: false
							},
							items:[
								{
									xtype: 'combo',
									fieldLabel: 'Показать',
									store: Ext.create('Ext.data.Store', {
										fields: ['id', 'name', 'mode'],
										data : [
											{"id":0, "name":"Все автомобили", "mode":"all"},
											{"id":1, "name":"Открытые", "mode":"opened"},
											{"id":2, "name":"Закрытые", "mode":"closed"}
										]
									}),
									queryMode: 'local',
									name: 'displayMode',
									displayField: 'name',
									valueField: 'id',
									value: 0,
									listeners: {
										select: function(cmp, recs){
											win.swEmergencyTeamAutoGrid.getStore().getProxy().extraParams = {'display': recs[0].get('mode')};
											win.swEmergencyTeamAutoGrid.getStore().load();
										}
									}
								}			
							]
						}
					]
				}
			]
		});		

		win.selectBtn = Ext.create('Ext.button.Button', {
			text: 'Выбрать',
			iconCls: 'ok16',
			refId: 'selectButton',
			disabled: false,
			handler: function(){
				
				var recs = [];

				win.swEmergencyTeamAutoGrid.getStore().findBy( function(r){ if(r.get('checked'))recs.push(r); })

				win.fireEvent('selectTeamsFromTemplate', recs);
			}
		});
		
		//отправляем сборку
		win.configComponents = {
			top: win.topTbar,
			center: [win.swEmergencyTeamAutoGrid]
			//rigthButtons: selectBtn
		}

		if(typeof conf.action != "undefined" && conf.action == 'chooseTemplates'){
			//добавляем кнопку "Выбрать"
			win.configComponents.leftButtons = win.selectBtn;
		}
		
		win.callParent(arguments);
	}
})

