/*
	Шаблоны нарядов список
*/

Ext.define('common.DispatcherStationWP.tools.swEmergencyTeamTemplateWindow', {
	alias: 'widget.swEmergencyTeamTemplateWindow',
	extend: 'sw.standartToolsWindow',
	title: 'Шаблоны нарядов',
	//width: 800,
	//height: 400,

	setSelectTemplateWindowView: function(){
		var win = this,
			baseForm = win.down('form').getForm(),
			saveBtn = win.down('button[refId=selectButton]');

		win.swEmergencyTeamTemplateGrid.columns[0].setVisible(true);
		saveBtn.setVisible(true);
		//win.swEmergencyTeamTemplateGrid.getStore().getProxy().extraParams = {'display': 'opened'};
	},
	
	deleteEmTeamTemplate: function(){
		var win = this,
			selectedTeam = win.swEmergencyTeamTemplateGrid.getSelectionModel().getSelection()[0],
			params = {
				EmergencyTeam_id: selectedTeam.get('EmergencyTeam_id'),
				EmergencyTeamDuty_id: selectedTeam.get('EmergencyTeamDuty_id')
			};
			
		Ext.Msg.show({
			title:'Удаление шаблона',
			msg: 'Вы действительно хотите удалить выбранный шаблон?',
			buttons: Ext.Msg.YESNO,
			icon: Ext.Msg.WARNING,
			fn: function(btn){
				if (btn == 'yes'){
					Ext.Ajax.request({
						url: '/?c=EmergencyTeam4E&m=deleteEmergencyTeam',
						params: {
							EmergencyTeam_id: params.EmergencyTeam_id
						},
						callback: function(opt, success, response) {
							if (success){
								if ( response.success == false ) {
									Ext.Msg.alert('Ошибка', 'Ошибка при удалении карточки бригады');
								}
								else{
									var rec = win.swEmergencyTeamTemplateGrid.getSelectionModel().getSelection()[0];					
									win.swEmergencyTeamTemplateGrid.store.remove(rec);
									
									if(rec.EmergencyTeamDuty_id){
										Ext.Ajax.request({
											url: '/?c=EmergencyTeam4E&m=deleteEmergencyTeamDutyTime',
											params: {
												EmergencyTeamDuty_id: rec.EmergencyTeamDuty_id
											},
											callback: function(opt, success, response) {
												if (success){
													win.swEmergencyTeamTemplateGrid.getSelectionModel().select(0);	
												}
											}
										})
									}
									
								}
							}
						}
					})
				}
			}
		})
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
			win.swEmergencyTeamTemplateGrid.store.load();
		});

		win.addEvents({
			//событие на выбор шаблонов
			selectTeamsFromTemplate: true
		});
		
		win.topTbar = Ext.create('Ext.form.FieldSet', {
			itemId: 'winTopToolbar',
			title: 'Фильтры',
			flex: 1,
			items: [
				{
					xtype: 'BaseForm',
					id: win.id+'_mainPanel',
					border: false,
					frame: true,
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
								align: 'left'
							},
							flex: 1,
							xtype: 'container',
							defaults: {
								labelWidth: 120,
								//width: 300,
								flex: 1,
								margin: '2 10',
								labelAlign: 'right',
								keyNavEnabled: false,
								enableKeyEvents : true,
								mouseWheelEnabled: false
							},
							items:[
								{
									xtype: 'emergencyTempalteNames',
									name: 'EmergencyTeam_TemplateName'									
								},
								/*
								{
									xtype: 'SmpUnitsFromOptions',
									name: 'LpuBuilding_id',
									fieldLabel: 'Подразделение СМП',
									displayTpl: '<tpl for="."> {LpuBuilding_Code}. {LpuBuilding_Name} </tpl>',
									tpl: '<tpl for="."><div class="x-boundlist-item">'+
										'<font color="red">{LpuBuilding_Code}</font> {LpuBuilding_Name}'+
										'</div></tpl>',
									listeners: {
										select: function(a,b,c){
											//if(b[0])
												//audioFilters.down('lpuLocalCombo').setValue(b[0].get('Lpu_id'));
										}
									}
								},
								*/
								{
									xtype: 'SmpUnitsFromOptions',
									name: 'LpuBuilding_id',
									fieldLabel: 'Подразделение СМП',
									allowBlank: true,
									defaultValueCurrentLpuBuilding: true,
									editable: false,
									listeners: {
										change: function(){
											
											var combo = this,
												frm = win.topTbar.down('form').getForm(),
												autoField = frm.findField('MedProductCard_id');
											
											autoField.getStore().load({params: {filterLpuBuilding: combo.getValue()}});
											win.down('button[refId=filterTemplateList]').handler();
										}
									}
								},
								{
									xtype: 'textfield',
									fieldLabel: 'Номер бригады',
									name: 'EmergencyTeam_Num',
									maxWidth: 200
								},
								{
									xtype: 'swEmergencyTeamSpecCombo',
									name: 'EmergencyTeamSpec_id'
								},								
								{
									xtype: 'EmergencyCars',
									name: 'MedProductCard_id'
								}
							],

						},
						{
							layout: 'hbox',
							align: 'stretch',
							flex: 1,
							xtype: 'container',
							margin: '10 0',
							defaults: {
								labelWidth: 140,
								width: 80,
								margin: '2 10',
								labelAlign: 'right'
							},
							items:[
								{
									xtype: 'tbfill'
								},
								{
									xtype: 'button',
									refId: 'filterTemplateList',
									//disabled: false,
									text: 'Найти',
									iconCls: 'refresh16',
									handler: function(){
										var frm = win.topTbar.down('form').getForm(),
											values = frm.getValues();
											
										win.swEmergencyTeamTemplateGrid.store.clearFilter();
										for(var i in values){
											if(values[i]) win.swEmergencyTeamTemplateGrid.store.filter(i, values[i])
										}
										//win.searchAudioCalls();
									}
								},
								{
									xtype: 'button',
									refId: 'resetFilters',
									text: 'Сброс',
									iconCls: 'delete16',
									handler: function(){
										var frm = win.topTbar.down('form').getForm();
											
										frm.reset();	
										win.swEmergencyTeamTemplateGrid.store.clearFilter();
										
									}
								}
							]
						}
					],
					listeners: {
						render: function(p){
							// Обновление формы по нажатию Enter
							new Ext.util.KeyMap({
								target: p.body,
								key: Ext.EventObject.ENTER,
								fn: function(){
									win.down('button[refId=filterTemplateList]').handler();
								}
							});						
						}
					}
				}
			]
		});

		win.swEmergencyTeamTemplateGrid = Ext.create('Ext.grid.Panel', {
			flex: 1,
			stripeRows: true,
			refId: 'swEmergencyTeamTemplateGP',
			viewConfig: {
				loadingText: 'Загрузка',
				markDirty: false
			},
			tbar: Ext.create('Ext.toolbar.Toolbar', {
				items: [
					{
						xtype: 'button',
						text: 'Добавить',
						iconCls: 'add16',
						refId: 'addTemplate',
						handler: function(){
							
							var store = win.swEmergencyTeamTemplateGrid.getStore(),
								selectionModel = win.swEmergencyTeamTemplateGrid.getSelectionModel();
								//rec = selectionModel.getSelection();
							
							Ext.create('common.DispatcherStationWP.tools.swEditEmergencyTeamTemplate', {
								action: 'add',
								//config: params,
								listeners : {
									'aftersave': function(wnd, EmergencyTeam_data){	
										
										store.reload({
											callback: function() {
												var rec = store.findRecord('EmergencyTeam_id', EmergencyTeam_data.EmergencyTeam_id);
												
												if(rec)
													selectionModel.select( rec );
												wnd.close();
											}
										})
									}
								}
							}).show();
						}
						
					},
					{
						xtype: 'button',
						text: 'Изменить',
						refId: 'editTemplate',
						iconCls: 'edit16',
						handler: function(){
							
							var store = win.swEmergencyTeamTemplateGrid.getStore(),
								selectionModel = win.swEmergencyTeamTemplateGrid.getSelectionModel(),
								rec = selectionModel.getSelection();
							
							if(rec && rec[0]){
								var params = rec[0].getData();
								
								Ext.create('common.DispatcherStationWP.tools.swEditEmergencyTeamTemplate', {
									action: 'edit',
									config: params,
									listeners : {
										'aftersave': function(wnd, EmergencyTeam_data){	
											
											store.reload({
												callback: function() {
													selectionModel.select( store.findRecord('EmergencyTeam_id', EmergencyTeam_data.EmergencyTeam_id) );
													wnd.close();
												}
											})
										}
									}
								}).show();
							}
						}
					},
					{
						xtype: 'button',
						text: 'Просмотр',
						iconCls: 'search16',
						refId: 'viewTemplate',
						//disabled: true,
						handler: function(){
							
							var store = win.swEmergencyTeamTemplateGrid.getStore(),
								selectionModel = win.swEmergencyTeamTemplateGrid.getSelectionModel(),
								rec = selectionModel.getSelection();
							
							if(rec && rec[0]){
								var params = rec[0].getData();
								
								Ext.create('common.DispatcherStationWP.tools.swEditEmergencyTeamTemplate', {
									action: 'view',
									config: params,
									listeners : {
										'aftersave': function(wnd, EmergencyTeam_data){	
											
											store.reload({
												callback: function() {
													selectionModel.select( store.findRecord('EmergencyTeam_id', EmergencyTeam_data.EmergencyTeam_id) );
													wnd.close();
												}
											})
										}
									}
								}).show();
							}
						}
					},
					{
						xtype: 'button',
						text: 'Удалить',
						iconCls: 'delete16',
						refId: 'deleteTeam',
						handler: function(){
							win.deleteEmTeamTemplate();
						}
					},
					{
						xtype: 'button',
						text: 'Обновить',
						refId: 'refreshTemplateList',
						iconCls: 'refresh16',
						handler: function(){
							win.swEmergencyTeamTemplateGrid.getStore().reload();
						}
					},
					{
						xtype: 'button',
						text: 'Печать',
						refId: 'printTeam',
						iconCls: 'print16',
						handler: function(){
							Ext.ux.grid.Printer.print(win.swEmergencyTeamTemplateGrid);
						}
					}
				]
			}),
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
					{name: 'MedProductClass_Name', type: 'string'},
					{name: 'EmergencyTeam_Num', type: 'string'},
					{name: 'EmergencyTeam_CarNum', type: 'string'},
					{name: 'EmergencyTeam_CarBrand', type: 'string'},					
					{name: 'MedProductCard_id', type: 'int'},
					{name: 'CMPTabletPC_id', type: 'int'},
					{name: 'GeoserviceTransport_id', type: 'int'},
					{name: 'checked', type: 'boolean', defaultValue: false},
					{name: 'EmergencyTeamSpec_id', type: 'int'},
					{name: 'EmergencyTeam_HeadShift', type: 'int'},
					{name: 'EmergencyTeam_HeadShiftWorkPlace', type: 'int'},
					{name: 'EmergencyTeam_HeadShift2', type: 'int'},
					{name: 'EmergencyTeam_HeadShift2WorkPlace', type: 'int'},
					{name: 'EmergencyTeam_Assistant1', type: 'int'},
					{name: 'EmergencyTeam_Assistant1WorkPlace', type: 'int'},
					{name: 'EmergencyTeam_Assistant2', type: 'int'},
					{name: 'EmergencyTeam_Driver', type: 'int'},
					{name: 'EmergencyTeam_DriverWorkPlace', type: 'int'},
					{name: 'EmergencyTeam_Driver2', type: 'int'},
					{name: 'EmergencyTeamSpec_Name', type: 'string'},
					{name: 'EmergencyTeam_HeadShiftFIO', type: 'string'},
					{name: 'EmergencyTeam_DriverFIO', type: 'string'},
					{name: 'EmergencyTeam_id', type: 'int'},
					{name: 'EmergencyTeamDuty_ChangeComm', type: 'string'},
					{name: 'EmergencyTeam_TemplateName', type: 'string'},
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
				],
				proxy: {
					limitParam: undefined,
					startParam: undefined,
					paramName: undefined,
					pageParam: undefined,
					type: 'ajax',
					url: '/?c=EmergencyTeam4E&m=loadEmergencyTeamTemplateList',
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
						win.swEmergencyTeamTemplateGrid.getSelectionModel().select(0);
					}
				}
			}),
			columns: [
				{ dataIndex: 'checked', text: '', width: 55, xtype: 'checkcolumn', sortable: false, hidden: true,
					listeners: {
						beforecheckchange: function( column, rowIndex, checked, eOpts ){
							
							//выбор 1 значения
							var selIndex = win.swEmergencyTeamTemplateGrid.getStore().findBy( function(r){ if(r.get('checked')) return true; })
							if(checked && selIndex!=-1)
							{
								return false;
							}
						},
						checkchange: function( column, rowIndex, checked, eOpts ){
							var saveBtn = win.down('button[refId=selectButton]');
							saveBtn.setDisabled(!checked);
						}
					}
				},
				{ dataIndex: 'EmergencyTeam_TemplateName', text: 'Имя шаблона', flex: 1, hideable: false },
				{ dataIndex: 'LpuBuilding_Name', text: 'Подразделение СМП', flex: 1, hideable: false },
				{ dataIndex: 'EmergencyTeam_Num', text: 'Номер бригады', width: 100, hideable: false },
				{ dataIndex: 'EmergencyTeamSpec_Name', text: 'Профиль бригады', width: 220, hideable: false },				
				{ dataIndex: 'EmergencyTeamDuty_DTStart', text: 'Время с', width: 60, hideable: false },
				{ dataIndex: 'EmergencyTeamDuty_DTFinish', text: 'Время по', width: 60, hideable: false },
				{ dataIndex: 'MedProductClass_Name', text: 'Автомобиль', width: 200, hideable: false },
				{ dataIndex: 'EmergencyTeam_HeadShiftFIO', text: 'Старший бригады', flex: 1, hideable: false },
				{ dataIndex: 'EmergencyTeam_DriverFIO', text: 'Водитель', flex: 1, hideable: false }
			]
		});
		
		win.selectBtn = Ext.create('Ext.button.Button', {
			text: 'Создать наряд',
			iconCls: 'ok16',
			refId: 'selectButton',
			disabled: true,
			hidden: true,
			handler: function(){

				var recs = [];

				win.swEmergencyTeamTemplateGrid.getStore().findBy( function(r){ if(r.get('checked'))recs.push(r); })

				win.fireEvent('selectTeamsFromTemplate', recs);
			}
		});

		//отправляем сборку
		win.configComponents = {
			top: win.topTbar,
			center: [win.swEmergencyTeamTemplateGrid],
			leftButtons: win.selectBtn
		};

		win.callParent(arguments);
	}
})

