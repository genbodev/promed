/* 
	Наряды (бывшее Формирование наряда)
*/

Ext.define('common.DispatcherStationWP.tools.swEmergencyTeamTemplateSetDuty', {
	alias: 'widget.swEmergencyTeamTemplateSetDuty',
	extend: 'sw.standartToolsWindow',
	title: 'Наряды',
	width: 800,
	height: 400,
	onEsc: Ext.emptyFn,
	cls: 'swEmergencyTeamTemplateSetDuty',
	defaultFocus: 'gridpanel[refId=swEmergencyTeamGrid] tableview',
	initComponent: function() {
		var win = this,
			conf = win.initialConfig,
			isheaddoctor = (conf.armType == 'smpheaddoctor'),
			globals = getGlobalOptions();
		
		win.medPersonallist = Ext.create('Ext.data.Store', {
			autoLoad: true,
			fields: [
				{name: 'MedPersonal_id', type:'int'},
				{name: 'MedPersonal_Code', type:'int'},
				{name: 'MedPersonal_Fio', type:'string'},
				{name: 'LpuSection_id', type:'int'}
			],
			proxy: {
				limitParam: undefined,
				startParam: undefined,
				paramName: undefined,
				pageParam: undefined,
				type: 'ajax',
				url: '/?c=MedPersonal4E&m=loadMedPersonalCombo',
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
		});
		
		win.cald = Ext.create('sw.datePickerRange', {
			maxValue: 'unlimited',
			dateFields: ['dateStart', 'dateFinish']
		});
		
		win.swEmergencyTeamGrid = Ext.create('Ext.grid.Panel', {
			flex: 1,
			stripeRows: true,
			refId: 'swEmergencyTeamGrid',
			viewConfig: {
				loadingText: 'Загрузка',
				markDirty: false,
				cls: 'swEmergencyTeamGridViewTable'
			},
			//margin: 5,
			// plugins: new Ext.grid.plugin.CellEditing({
				// clicksToEdit: 1
			// }),

			tbar: Ext.create('Ext.toolbar.Toolbar', {
				items: [
					Ext.create('sw.datePrevDay'),
					win.cald,
					Ext.create('sw.dateNextDay'),
					{
						xtype: 'combo',
						fieldLabel: 'Показать',
						store: Ext.create('Ext.data.Store', {
							fields: ['id', 'name', 'mode'],
							data : [
								{"id":0, "name":"Все наряды", "mode":"all"},
								{"id":1, "name":"Текущие наряды", "mode":"current"}
							]
						}),
						labelAlign: 'right',
						refId: 'showCurrentTeamsByFactCombo',
						queryMode: 'local',
						displayField: 'name',
						valueField: 'id',
						value: 0,
						listeners: {
							select: function(cmp, recs){					
								var showCurrentTeamsByFactFlag = (recs[0].get('id') == 1)? true: false;
								
								win.swEmergencyTeamGrid.getStore().getProxy().extraParams.showCurrentTeamsByFact = showCurrentTeamsByFactFlag;
								
								if(!showCurrentTeamsByFactFlag){
									win.swEmergencyTeamGrid.getStore().getProxy().extraParams.dateFinish = Ext.Date.format(win.cald.dateTo, 'd.m.Y');
									win.swEmergencyTeamGrid.getStore().getProxy().extraParams.dateStart = Ext.Date.format(win.cald.dateFrom, 'd.m.Y');
								}
								else{
									win.swEmergencyTeamGrid.getStore().getProxy().extraParams.dateFinish = Ext.Date.format(new Date, 'd.m.Y');
									win.swEmergencyTeamGrid.getStore().getProxy().extraParams.dateStart = Ext.Date.format(new Date, 'd.m.Y');
								}
								win.swEmergencyTeamGrid.getStore().load();
							}
						}
					},
					{
						xtype: 'button',
						text: 'Добавить по шаблону',
						iconCls: 'add16',
						hidden: isheaddoctor,
						handler: function(){
							var EmergencyTeamTemplateWindow = Ext.create('common.DispatcherStationWP.tools.swEmergencyTeamTemplateWindow', {
								layout: {
									type: 'fit',
									align: 'stretch'
								},
								maximized: true,
								constrain: true,
								renderTo: Ext.getCmp('inPanel').body,
								action: 'chooseTemplates',
								listeners:{
									selectTeamsFromTemplate: function(recs){
										
										win.showTeamEditWindow('addTemplate', recs[0], function(){
											EmergencyTeamTemplateWindow.close();
										});
										//EmergencyTeamTemplateWindow.close();
										
										//var nowDate = win.cald.dateTo;
										//win.saveEmergencyTeamWithTime(recs, true, nowDate);
									}
								}
							});
							
							EmergencyTeamTemplateWindow.show();
						}
					},
					{
						xtype: 'button',
						text: 'Добавить',
						iconCls: 'add16',
						hidden: isheaddoctor,
						handler: function(){
							win.showTeamEditWindow('add');
							/*
							var EmergencyTeamAutoWindow = Ext.create('common.DispatcherStationWP.tools.swEmergencyTeamAutoWindow', {
								layout: {
									type: 'fit',
									align: 'stretch'
								},
								maximized: true,
								constrain: true,
								renderTo: Ext.getCmp('inPanel').body,
								action: 'chooseTemplates',
								listeners:{
									selectTeamsFromTemplate: function(recs){
										EmergencyTeamAutoWindow.close();
										var nowDate = win.cald.dateTo;
										win.saveEmergencyTeamWithTime(recs, true, nowDate);
									}
								}
							});
							
							EmergencyTeamAutoWindow.show();
							*/
						}
					},
					{
						xtype: 'button',
						text: 'Изменить',
						refId: 'editTeam',
						hidden: isheaddoctor,
						iconCls: 'edit16',
						handler: function(){
							var rec = win.swEmergencyTeamGrid.getSelectionModel().getSelection()[0];
							if ( !rec ) {
								return;
							}
							win.showTeamEditWindow('edit');
						}
					},
					{
						xtype: 'button',
						text: 'Просмотр',
						refId: 'viewTeam',
						iconCls: 'view16',
						handler: function(){
							var rec = win.swEmergencyTeamGrid.getSelectionModel().getSelection()[0];
							if ( !rec ) {
								return;
							}
							win.showTeamEditWindow('view');
						}
					},
					{
						xtype: 'button',
						text: 'Копировать наряд',
						iconCls: 'copy16',
						refId: 'copyTeam',
						hidden: isheaddoctor,
						disabled: true,
						handler: function(btn){
							win.showCopyTeamsWindow(btn.getX(), btn.getY()+btn.getHeight());
						}
					},
					{
						xtype: 'button',
						text: 'Удалить',
						iconCls: 'delete16',
						refId: 'deleteTeam',
						hidden: isheaddoctor,
						handler: function(){
							var rec = win.swEmergencyTeamGrid.getSelectionModel().getSelection()[0];
							if ( !rec ) {
								return;
							}
							win.deleteEmTeamTemplate(globals.lpu_id);
						}
					},
					{
						xtype: 'button',
						text: 'Изменить состав наряда',
						refId: 'editStuffTeam',
						hidden: isheaddoctor,
						iconCls: 'edit16',
						handler: function(){
							var rec = win.swEmergencyTeamGrid.getSelectionModel().getSelection()[0];
							if ( !rec ) {
								return;
							}
							win.showTeamEditWindow('split');
						}
					},
					{
						xtype: 'button',
						text: 'Печать',
						refId: 'printTeam',
						iconCls: 'print16',
						handler: function(){
							Ext.ux.grid.Printer.print(win.swEmergencyTeamGrid);
						}
					},
					{
						xtype: 'button',
						text: 'Аудит записи',
						refId: 'auditRecord',
						iconCls: null,
						hidden: !getRegionNick().inlist(['ufa']),
						handler: function () {
							var rec = win.swEmergencyTeamGrid.getSelectionModel().getSelection()[0];
							if (!rec) {
								return;
							}
							var EmergencyTeamAuditRecord = Ext.create('sw.tools.swEmergencyTeamAuditRecord', {
								key_id: rec.raw.EmergencyTeam_id,
								key_field: 'EmergencyTeam_id'
							});
							EmergencyTeamAuditRecord.show();
						}
					},
					// Кнопка потеряла актуальность. Наряд формируется "на лету"
					/*
					{
						xtype: 'button',
						text: 'Сформировать наряд',
						iconCls: 'save16',
						handler: function(){
							win.saveEditedEmergencyTeams();
						}
					},
					*/
				]
			}),
			listeners: {
				boxready: function(me){
					me.el.on('focus', function(e,t){
						
					});
					me.el.on('click', function(e,t){
					//признаться честно, без яваскриптовых хаков не обошлось1
					//проверяем, кликнули не на ячейку - закрылись
						if( (win.winPopupGrid) && ((" " + t.className + " " ).indexOf( " "+'x-grid-cell-inner'+" " ) == -1) )
						{
							win.winPopupGrid.close();
						}
					});
				},
				itemdblclick: function( cmp, rec, item, index, e, eOpts ){
					
					if(!rec) return false;
					var factEnd = rec.get('EmergencyTeamDuty_factEndWorkDT');
					
					if (factEnd != "")
						return false;
					
					win.showTeamEditWindow('edit');
				},
				beforeselect: function(cmp, rec){
					if(!rec) return false;
					var EmergencyTeamDuty_factToWorkDT = (rec.get('EmergencyTeamDuty_factToWorkDT'))?true:false;
					
					//Кнопка активна, если наряд еще не выходил на смену
					// win.swEmergencyTeamGrid.down('button[refId=deleteTeam]').setDisabled( EmergencyTeamDuty_factToWorkDT );
					
					var factStart = rec.get('EmergencyTeamDuty_factToWorkDT'),
						factEnd = rec.get('EmergencyTeamDuty_factEndWorkDT');
						
					//Кнопка активна только для нарядов, которые в текущее время находятся на смене
					win.swEmergencyTeamGrid.down('button[refId=editStuffTeam]').setDisabled( !(factStart && !factEnd) );					
					
					win.swEmergencyTeamGrid.down('button[refId=editTeam]').setDisabled(factEnd != "");
					
				},
				cellkeydown: function(cmp, td, cellIndex, record, tr, rowIndex, e, eOpts){
					switch(e.getKey()){
						case 13: {
							win.showTeamEditWindow('edit');
							break;
						}
					}
					if (getRegionNick().inlist(['ufa'])) {
						if (e.getKey() == 114 && e.altKey == true) {
							var EmergencyTeamAuditRecord = Ext.create('sw.tools.swEmergencyTeamAuditRecord', {
								key_id: record.raw.EmergencyTeam_id,
								key_field: 'EmergencyTeam_id'
							});
							EmergencyTeamAuditRecord.show();
						}
					}
					if (e.getKey() == 13){
						
						//признаться честно, без яваскриптовых хаков не обошлось2
					}					
				}
			},
			store: new Ext.data.JsonStore({
				autoLoad: true,
				//storeId: 'EmergencyTeamTemplateSetDutyStore',
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
					{name: 'EmergencyTeam_HeadShiftWorkPlace', type: 'int', convert: null},
					{name: 'EmergencyTeam_HeadShift2', type: 'int', convert: null},
					{name: 'EmergencyTeam_HeadShift2WorkPlace', type: 'int', convert: null},
					{name: 'EmergencyTeam_Driver', type: 'int', convert: null},
					{name: 'EmergencyTeam_DriverWorkPlace', type: 'int', convert: null},
					{name: 'EmergencyTeam_Driver2', type: 'int', convert: null},
					{name: 'EmergencyTeam_Assistant1', type: 'int', convert: null},
					{name: 'EmergencyTeam_Assistant1WorkPlace', type: 'int', convert: null},
					{name: 'EmergencyTeam_Assistant2', type: 'int', convert: null},
					{name: 'EmergencyTeamSpec_id', type: 'int'},					
					{name: 'EmergencyTeamDuty_ChangeComm', type: 'string'},
					
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
					
					{name: 'EmergencyTeam_HeadShiftFIO', type: 'string'},
					{name: 'EmergencyTeam_HeadShift2FIO', type: 'string'},					
					{name: 'EmergencyTeam_DriverFIO', type: 'string'},
					{name: 'EmergencyTeam_Driver2FIO', type: 'string'},					
					{name: 'EmergencyTeam_Assistant1FIO', type: 'string'},
					{name: 'EmergencyTeam_Assistant2FIO', type: 'string'},
					
					{name: 'EmergencyTeam_DutyTime', type: 'string'},
					{name: 'GeoserviceTransport_id', type: 'int'},
					{name: 'EmergencyTeamDuty_id', type: 'int'},
					{name: 'MedProductCard_id', type: 'int'},
					{name: 'MedProduct_Name', type: 'string'},
					{name: 'MedProductCard_BoardNumber', type: 'string'},
					{name: 'MedProductClass_Model', type: 'string'},
					{name: 'AccountingData_RegNumber', type: 'string'},

					{name: 'EmergencyTeam_HeadShiftPost', type: 'string', defaultValue: 'Старший бригады'},
					
					{name: 'EmergencyTeamDuty_DStart', type: 'string'},
					{name: 'EmergencyTeamDuty_DFinish', type: 'string'},
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
					{name: 'EmergencyTeamSpec_Name', type: 'string'},
					//{name: 'checked', type: 'boolean', defaultValue: false},
					{name: 'GeoserviceTransport_name', type: 'string'},
					{name: 'CMPTabletPC_id', type: 'int'},
					{name: 'locked', type: 'boolean'},
					{name: 'EmergencyTeamDuty_comesToWorkDT', type: 'string'},
					{name: 'EmergencyTeamDuty_factToWorkDT', type: 'string'},
					{name: 'EmergencyTeamDuty_factEndWorkDT', type: 'string'},
					{name: 'EmergencyTeam_Phone', type: 'string'},
					{name: 'EmergencyTeamDuty_IsCancelledStart', type: 'int'},
					{name: 'EmergencyTeamDuty_IsCancelledClose', type: 'int'}
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
						var success = (successful && records.length);
						
						win.swEmergencyTeamGrid.down('button[refId=editTeam]').setDisabled(!success);
						//win.swEmergencyTeamGrid.down('button[refId=copyTeam]').setDisabled(!success);
						win.swEmergencyTeamGrid.down('button[refId=deleteTeam]').setDisabled(!success);
						win.swEmergencyTeamGrid.down('button[refId=printTeam]').setDisabled(!success);
						if(!success){							
							c.removeAll();
						}
						else{
							win.swEmergencyTeamGrid.getSelectionModel().select(0);
						}
					}
				}
			}),
			columns: [
				{ dataIndex: 'checked', text: 'Копировать', width: 80, xtype: 'checkcolumn', hideable: false, sortable: false,
					listeners: {
						'checkchange': function(a,b,c){
							var copyBtn = win.swEmergencyTeamGrid.down('button[refId=copyTeam]');
							var deleteTeam = win.swEmergencyTeamGrid.down('button[refId=deleteTeam]');
							var teamGridStore = win.swEmergencyTeamGrid.getStore();
							var teamElem = teamGridStore.getAt(b).data;
							var checked = !teamGridStore.findRecord( 'checked', true );
							if(c){
								copyBtn.setDisabled(false);
								//deleteTeam.setDisabled(false);
							}else{
								copyBtn.setDisabled(checked);
								//deleteTeam.setDisabled(checked);
							}
						}
					}
				},
				{ dataIndex: 'EmergencyTeam_id', text: 'ID', key: true, hidden: true, hideable: false },
				{ dataIndex: 'CMPTabletPC_id', hidden: true, hideable: false },
				{ dataIndex: 'EmergencyTeamDuty_IsCancelledStart', hidden: true, hideable: false },
				{ dataIndex: 'EmergencyTeamDuty_IsCancelledClose', hidden: true, hideable: false },
				{ dataIndex: 'EmergencyTeam_Phone', hidden: true, hideable: false },
				{ dataIndex: 'locked', hidden: true, hideable: false },
				{ dataIndex: 'LpuBuilding_Name', text: 'Подразделение СМП', flex:1, hideable: false },
				{ dataIndex: 'EmergencyTeam_Num', text: 'Номер бригады', width: 100, hideable: false },
				{ dataIndex: 'MedProduct_Name', text: 'Автомобиль', width: 100, hideable: false },
				{ dataIndex: 'GeoserviceTransport_name', text: 'GPS/ГЛОНАСС', width: 120, hideable: false },
				{ dataIndex: 'EmergencyTeamDuty_DStart', text: 'Дата начала', width: 80, hideable: false },
				{ dataIndex: 'EmergencyTeamDuty_DTStart', text: 'Смена с', width: 60, hideable: false },
				{ dataIndex: 'EmergencyTeamDuty_DFinish', text: 'Дата окончания', width: 100, hideable: false },
				{ dataIndex: 'EmergencyTeamDuty_DTFinish', text: 'Смена по', width: 60, hideable: false },
				{ dataIndex: 'EmergencyTeam_HeadShiftFIO', text: 'Старший бригады', flex: 1, hideable: false },
				{ dataIndex: 'EmergencyTeam_HeadShift2FIO', text: 'Помощник 1', flex: 1, hideable: false },
				{ dataIndex: 'EmergencyTeam_Assistant1FIO', text: 'Помощник 2', flex: 1, hideable: false },
				{ dataIndex: 'EmergencyTeam_DriverFIO', text: 'Водитель', flex: 1, hideable: false },
				{ dataIndex: 'EmergencyTeamDuty_comesToWorkDT', hidden: true, hideable: false },
				//{ dataIndex: 'EmergencyTeamDuty_factToWorkDT', text: 'Начало фактическое', hideable: false },
				//{ dataIndex: 'EmergencyTeamDuty_factEndWorkDT', text: 'Окончание фактическое', hideable: false },
				
				//{ dataIndex: 'EmergencyTeam_Assistant2FIO', text: 'Третий работник', flex: 1, hideable: false },
				//{ dataIndex: 'EmergencyTeamSpec_Name', text: 'Профиль',  flex:1, hideable: false },
				
				//{ dataIndex: 'EmergencyTeam_HeadShiftPost', text: 'Должность', width: 150, hideable: false },
				
				
				
				
				
				{ dataIndex: 'EmergencyTeam_Driver2FIO', text: 'Водитель', flex: 1, hideable: false, hidden: true },
				{ dataIndex: 'EmergencyTeamDuty_ChangeComm', text: 'Комментарий', flex: 1, hideable: false }
			]
		});
		
		/*
		win.swEmergencyTeamShiftGridSM = Ext.create('Ext.selection.CheckboxModel',{
			listeners: {
				beforedeselect: function( model,record, index, eOpts ) {
					if (record.data.locked) {
						return false;
					}
				}
			}
		});
		*/
		win.gridContainer = Ext.create('Ext.container.Container', {
			layout: 'fit',
			items: win.swEmergencyTeamGrid
		});

		
		//отправляем сборку
		win.configComponents = {
			//top: win.topTbar,
			center: [win.gridContainer]
			//subBottomItems: [],
			//leftButtons: win.printButton
		}
		
		win.callParent(arguments);
	},

	saveEmergencyTeamWithTime: function(recs, o, date){
		var win = this;		
			collectTemplates = [],
			dataPickerValue = win.cald.dateFrom;
		
		for(var j in recs){
			var sendData = recs[j].getData(),
				selectedDate1 = date,
				selectedDate2 = date,
				//если указанная дата до меньше от то финиш - следующий день
				//selectedDate2 = (Ext.Date.parse(sendData.EmergencyTeamDuty_DTStart,'H:i').getTime() !== Ext.Date.parse(sendData.EmergencyTeamDuty_DTFinish,'H:i').getTime()) ? selectedDate1 : new Date(dataPickerValue.getTime() + (24 * 60 * 60 * 1000)),
				fs = Ext.Date.format(selectedDate1, "Y-m-d ") + ((sendData.EmergencyTeamDuty_DTStart) ? sendData.EmergencyTeamDuty_DTStart : '00:00:00'),
				fe = Ext.Date.format(selectedDate2, "Y-m-d ") + ((sendData.EmergencyTeamDuty_DTFinish) ? sendData.EmergencyTeamDuty_DTFinish : '00:00:00'),
				sd = Ext.Date.parse(fs, "Y-m-d H:i:s"),
				ed = Ext.Date.parse(fe, "Y-m-d H:i:s"),
				diff = ed - sd,
				hours = Math.floor(diff / 3600000);

			//если надо новую запись - о true
			if (o) {
				sendData.EmergencyTeam_id = null;
			}

			sendData.EmergencyTeam_isTemplate = '1';
			sendData.EmergencyTeam_DutyTime = hours;
			sendData.EmergencyTeamDuty_DTStart = fs;
			sendData.EmergencyTeamDuty_DTFinish = fe;
			sendData.EmergencyTeamDuty_factToWorkDT = null;
			sendData.EmergencyTeamDuty_factEndWorkDT = null;
			sendData.EmergencyTeamStatus_id = null;
			collectTemplates.push(sendData);
		};

		Ext.Ajax.request({
			url: '/?c=EmergencyTeam4E&m=saveEmergencyTeams',
			params: {
				EmergencyTeams: Ext.encode(collectTemplates)
			},
			callback: function(opt, success, response) {
				if (success){					
					var callbackParams =  Ext.decode(response.responseText);					
					if ( callbackParams[0].success == false ) {
						var error_msg = (callbackParams[0].Error_Msg)?callbackParams[0].Error_Msg:'Ошибка сохранения';
						Ext.Msg.alert('Ошибка', error_msg);
                        win.swEmergencyTeamGrid.store.reload();
					} else {
						var armWindowEl = win.getEl(),
							alertBox = Ext.create('Ext.window.Window', {
								title: 'Сохранено',
								height: 50,
								width: 300,
								layout: 'fit',
								constrain: true,
								cls: 'waitingForAnswerHeadDoctorWindow',
								header: false,
								constrainTo: armWindowEl,
								layout: {
									type: 'vbox',
									align: 'bottom'
								},
								items: [
									{
										xtype: 'label',
										flex: 1,
										html: "<a>Наряды скопированы успешно</a>"
									},
									{
										xtype: 'button',
										text: 'Закрыть',
										renderTpl: [
											'<span id="{id}-btnWrap" class="{baseCls}-wrap closeBtn',
											'<tpl if="splitCls"> {splitCls}</tpl>',
											'{childElCls}" unselectable="on">',
											'<span id="{id}-btnEl" class="{baseCls}-button">',
											'X',
											'</span>'
										],
										handler: function(){
											alertBox.close();
										}
									}
								]
							});

						alertBox.showAt([(armWindowEl.getWidth()-350), armWindowEl.getHeight()-50]);
						setTimeout(function(){alertBox.close()},3000)
						win.swEmergencyTeamGrid.store.reload();
					}
				} else {
					Ext.Msg.alert('Ошибка', 'Ошибка сохранения');
				}
			}
		})		
	},
	
	saveEditedEmergencyTeams: function(){
		//здесь - групповое редактирование бригад
		//а именно - состав
		var win = this,
			modRecs = win.swEmergencyTeamGrid.store.getModifiedRecords(); 
		
		win.saveEmergencyTeamWithTime(modRecs, false);
	},
	
	showTeamEditWindow: function(mode, rec, clback){
		var win = this,
			params = {};
			
		//да, некоторые вещи дублируются, но пока у нас постоянные изменения в этом инструментарии, лучше разведу по разным углам
		//от греха подальше
		switch(true){
			case mode.inlist(['edit', 'view', 'split']) : {

				var parentSelRec = win.swEmergencyTeamGrid.getSelectionModel().getSelection()[0],
					params = parentSelRec.getData(),
					start = Ext.Date.parse( parentSelRec.raw.EmergencyTeamDuty_DTStart, 'Y-m-d H:i:s'),
					end = Ext.Date.parse( parentSelRec.raw.EmergencyTeamDuty_DTFinish, 'Y-m-d H:i:s');

					params.EmergencyTeamDuty_DStart = new Date(start);
					params.EmergencyTeamDuty_DFinish = new Date(end);

					params.EmergencyTeamDuty_DTStart = new Date(start.setHours(0,0,0,0));
					params.EmergencyTeamDuty_DTFinish = new Date(end.setHours(0,0,0,0));

				break;
			}
			
			case mode.inlist(['add']) : {
				//добавление наряда
				
				var start = win.cald.dateFrom,
					end = win.cald.dateTo;
					
				params.EmergencyTeamDuty_DTStart = start;
				params.EmergencyTeamDuty_DTFinish = end;
				
				params.EmergencyTeamDuty_DStart = new Date(start.setHours(0,0,0,0));
				params.EmergencyTeamDuty_DFinish = new Date(end.setHours(0,0,0,0));
				
				break;
			}
			
			case mode.inlist(['addTemplate']) : {
				var parentSelRec = rec,
					params = rec.getData(),
					start = Ext.Date.parse( parentSelRec.raw.EmergencyTeamDuty_DTStart, 'Y-m-d H:i:s'),
					end = Ext.Date.parse( parentSelRec.raw.EmergencyTeamDuty_DTFinish, 'Y-m-d H:i:s');
					
					params.EmergencyTeam_id = null;
					
					params.EmergencyTeamDuty_DTStart = null;
					params.EmergencyTeamDuty_DTFinish = null;
					
					params.EmergencyTeamDuty_DStart = start;
					params.EmergencyTeamDuty_DFinish = end;
					
					console.warn('params', params)
				break;
			}
		}
		
		params.mode = mode;

		win.winPopupWindow = Ext.create('sw.tools.subtools.swEmergencyTeamPopupEditWindow', params).show();
		
		win.winPopupWindow.on('saveTeam', function(teamId){
			if(clback) clback();
			win.swEmergencyTeamGrid.store.reload({
				callback: function(){
					var rec = win.swEmergencyTeamGrid.store.findRecord('EmergencyTeam_id', teamId);
					win.swEmergencyTeamGrid.getSelectionModel().select(rec);
				}
			});
			
		});
	},
	
	showCopyTeamsWindow: function(x,y){

		var win = this,
			copyTeamTo = Ext.create('Ext.window.Window', {
			title: 'Выберите дату копирования наряда',
			height: 258,
			width: 250,
			layout: 'hbox',
			modal: true,
			items: [Ext.create('Ext.picker.Date', {
				region: 'north',
				split: true,
				name: 'CmpCallCard_DateTper',
				floating: false,
				flex: 1
			})],
			bbar: [
				{
					xtype: 'button',
					iconCls: 'ok16',
					text: 'Выбрать',
					handler: function(){
						var recs = [];

						win.swEmergencyTeamGrid.getStore().findBy( function(r){ if(r.get('checked'))recs.push(r); })
							
						win.saveEmergencyTeamWithTime(recs, true, copyTeamTo.down('datepicker').getValue());
						
						copyTeamTo.close();
					}
				},
				{ xtype: 'tbfill' },
				{
					xtype: 'button',
					iconCls: 'cancel16',
					text: 'Закрыть',
					handler: function(){
						copyTeamTo.close();
					}
				}
			],
		});

		copyTeamTo.showAt(x,y);
		/*
		var EmergencyTeamTemplateSetDuty = Ext.create('common.DispatcherStationWP.tools.swSelectEmergencyTeamWindow',
		{
			layout: {
				type: 'fit',
				align: 'stretch'
			},
			maximized: true,
			constrain: true,
			renderTo: Ext.getCmp('inPanel').body
		}),
		win = this;
		
		EmergencyTeamTemplateSetDuty.on('selectTeams', function(recs){
			EmergencyTeamTemplateSetDuty.close();
			win.saveEmergencyTeamWithTime(recs, true);
		});
		
		EmergencyTeamTemplateSetDuty.show();
		*/
	},
	
	deleteEmTeamTemplate: function(lpu_id){
		var selectedTeam = this.swEmergencyTeamGrid.getSelectionModel().getSelection()[0],
			params = {
				EmergencyTeam_id: selectedTeam.get('EmergencyTeam_id'),
				EmergencyTeamDuty_id: selectedTeam.get('EmergencyTeamDuty_id')
			};
		if(!selectedTeam) return false;
		if(selectedTeam.get('EmergencyTeamDuty_factToWorkDT'))
		{
			var nariad_i = 'наряд № <b>'+selectedTeam.get('EmergencyTeam_Num')+'</b> был выведен на смену. ';
			Ext.Msg.alert('Удаление невозможно', 'Удаление невозможно: ' + nariad_i + '<br>Снимите наряд со смены и повторите действие.');
			return false;
		}

		// Пройдёт еще 4 месяца, может вновь передумают
		/*var selectedTeamArr = [];
		var selectedTeamERR = [];
		this.swEmergencyTeamGrid.getStore().findBy( function(r){ 
			if(r.get('checked')) {
				if( r.get('EmergencyTeamDuty_factToWorkDT') ) {
					selectedTeamERR.push( r.get('EmergencyTeam_Num') );
				}else{
					selectedTeamArr.push( r.get('EmergencyTeam_id') );
				}
			}
		});
		if(selectedTeamERR.length>0){
			var str = '<b>'+selectedTeamERR.join(', ')+'</b>';
			var nariad_i = ( selectedTeamERR.length == 1 ) ? 'наряд № '+str+' был выведен на смену. ' : 'наряды № '+str+' были выведены на смену. ';
			Ext.Msg.alert('Удаление невозможно', 'Удаление невозможно: ' + nariad_i + '<br>Снимите '+( (selectedTeamERR.length==1)?' наряд':' наряды' )+' со смены и повторите действие.');
			return false;
		}
		if(selectedTeamArr.length == 0) return false;*/
		Ext.Msg.show({
			title:'Удаление бригады',
			msg: 'Вы действительно хотите удалить выбранный наряд?',
			buttons: Ext.Msg.YESNO,
			icon: Ext.Msg.WARNING,
			fn: function(btn){
				if (btn == 'yes'){
					Ext.Ajax.request({
						url: '/?c=EmergencyTeam4E&m=deleteEmergencyTeam',
						//url: '/?c=EmergencyTeam4E&m=deleteEmergencyTeamList',
						params: {
							//EmergencyTeamsList: Ext.encode(selectedTeamArr),
							EmergencyTeam_id: params.EmergencyTeam_id,
							Lpu_id: lpu_id
						},
						callback: function(opt, success, response) {
							//debugger;
							if (success){
								if ( response.success == false ) {
									Ext.Msg.alert('Ошибка', 'Ошибка при удалении карточки бригады');
								}
								else{									
									var rec = this.swEmergencyTeamGrid.getSelectionModel().getSelection()[0];
									this.swEmergencyTeamGrid.store.remove(rec);
									Ext.Ajax.request({
										url: '/?c=EmergencyTeam4E&m=deleteEmergencyTeamDutyTime',
										params: {
											EmergencyTeamDuty_id: params.EmergencyTeamDuty_id
										},
										callback: function(opt, success, response) {
											if (success){
												this.swEmergencyTeamGrid.getSelectionModel().select(0);	
											}
										}.bind(this)
									})
									/*var teamArr = Ext.decode(response.responseText);
									var storeTeamGrid = this.swEmergencyTeamGrid.store;
									var EmergencyTeamDutyList=[];
									if(typeof teamArr){
										for(var key in teamArr){
											if(teamArr[key]) {
												var el=storeTeamGrid.findRecord('EmergencyTeam_id', key);
												storeTeamGrid.remove(el);
												EmergencyTeamDutyList.push(key);
											}
										}
									}

									if(EmergencyTeamDutyList.length == 0) return false;
									Ext.Ajax.request({
										url: '/?c=EmergencyTeam4E&m=deleteEmergencyTeamDutyTimeList',
										params: {
											EmergencyTeamDutyList:  Ext.encode(EmergencyTeamDutyList)
										},
										callback: function(opt, success, response) {
											if (success){
												this.swEmergencyTeamGrid.getSelectionModel().select(0);	
											}
										}.bind(this)
									})*/
								}
							}
						}.bind(this)
					})
				}
			}.bind(this)
		})
	}
})

