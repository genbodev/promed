/* 
Отметка о выходе бригад СМП
*/


Ext.define('sw.tools.swSmpEmergencyTeamSetDutyTimeWindow', {
	alias: 'widget.swSmpEmergencyTeamSetDutyTimeWindow',
	extend: 'Ext.window.Window',
	title: 'Отметка о выходе бригад СМП',
	width: '90%',
	height: '90%',
	//maximizable: true,
	modal: true,
	layout: {
        align: 'stretch',
        type: 'vbox'
    },
	
	initComponent: function() {
		var me = this,
			curArm = sw.Promed.MedStaffFactByUser.current.ARMType || sw.Promed.MedStaffFactByUser.last.ARMType;
		
		me.isNmpArm = curArm.inlist(['dispnmp','dispcallnmp', 'dispdirnmp']);

		me.title = me.isNmpArm ? 'Отметка о выходе бригад НМП' : 'Отметка о выходе бригад СМП',
		
		me.addEvents({			
			setDutyTimeToEmergencyTeams: true
		});

		Ext.Date.patterns={
			rus: "d-m-Y H:i:s",
			evr: "Y-m-d H:i:s",
			env: "m/d/Y H:i:s"
		};

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
		
		me.emergencyTeamDutyTimeGrid = Ext.create('Ext.grid.Panel', {
			plugins: new Ext.grid.plugin.CellEditing({
				clicksToEdit: 1
			}),
			viewConfig: {
				loadingText: 'Загрузка'
			},
			flex: 1,
			autoScroll: true,
			stripeRows: true,
			id: 'emergencyTeamDutyTimeGrid',
			refId: 'emergencyTeamDutyTimeGrid',
			getColumn: function(dataIndex) {
				var foundColumn = null;
				this.columns.some(function(column){
					if (column.dataIndex == dataIndex) {
						foundColumn = column;
						return true;
					}
				});
				return foundColumn;
			},
			isAllChecked: function(dataIndex) {
				var store = this.getStore();
				var allChecked = (store.getCount() > 0);

				store.each(function(record) {
					if (!record.get(dataIndex)) {
						allChecked = false;
					}
				});

				return allChecked;
			},
			refreshCheckColumnHeader: function(dataIndex) {
				var column = this.getColumn(dataIndex);
				if (!column || column.xtype != 'checkcolumn') {
					return;
				}

				var el = Ext.fly(column.getId()).select('div.customCheckAll span').first();

				if (this.isAllChecked(dataIndex)) {
					el.addCls('checkedall');
				} else {
					el.removeCls('checkedall');
				}
			},
			tbar: [
				{
					xtype: 'button',
					itemId: 'refreshEmergencyTeamDutyTimeGridButton',
					disabled: false,
					text: 'Обновить',
					iconCls: 'refresh16',
					handler: function(){
						me.emergencyTeamDutyTimeGrid.store.reload()
					}
				},
				{
					xtype: 'button',
					itemId: 'printEmergencyTeamDutyTimeGrid',
					text: 'Печать формы закрытых карт',
					iconCls: 'print16',
					handler: function(){					
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
					{name: 'EmergencyTeamDuty_DTStartVis', type: 'string'},					
					{name: 'EmergencyTeamDuty_DStart', type: 'string'},					
					{name: 'EmergencyTeamDuty_TStart', type: 'string'},					
					{name: 'EmergencyTeamDuty_DTFinish', type: 'string'},
					{name: 'EmergencyTeamDuty_DTFinishVis', type: 'string'},
					{name: 'EmergencyTeamDuty_DFinish', type: 'string'},
					{name: 'EmergencyTeamDuty_TFinish', type: 'string'},
					{name: 'LpuBuilding_Name', type: 'string'},
					{name: 'EmergencyTeamStatus_Code', type: 'int'},
					{name: 'EmergencyTeamDuty_IsCancelledStart', type: 'boolean'},
					{name: 'EmergencyTeamDuty_IsCancelledClose', type: 'boolean'},

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
					{name: 'EmergencyTeamDuty_factEndWorkDT', type: 'string'},
					{name: 'ComesToWork', type: 'boolean'},
					{name: 'closed', type: 'boolean'},
					{name: 'EmergencyTeamDuty_Comm', type: 'string'},
					{name: 'EmergencyTeamDuty_ChangeComm', type: 'string'},
					{name: 'CountCmpCallCards', type: 'int'}
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
						dateStart:	Ext.Date.format(cald.dateFrom, 'd.m.Y'),
						// запросим список нарядов тех подразделений СМП, которые были выбраны  пользователем при входе в АРМ в форме «Выбор подстанций для управления»
						// если этот параметр не отправлять, то загрузится только текущий
						loadSelectSmp: true
					}
				},
				sorters: {
					property: 'EmergencyTeamDuty_DTStart',
					direction: 'ASC'
				},
				listeners: {
					load : function(cmp, records, successful, eOpts){
						/*
						Ext.Date.patterns={
							rus: "d-m-Y H:i:s",
							evr: "Y-m-d H:i:s",
							env: "m/d/Y H:i:s"
						};
						cmp.each(function(record){
							var factToWorkDT, factEndWorkDT;
							var ComesToWork =  record.get('ComesToWork');
							var Closed = record.get('closed');
							
								if(record.get('EmergencyTeamDuty_factToWorkDT')){
									factToWorkDT = Ext.Date.parse(record.get('EmergencyTeamDuty_factToWorkDT'), Ext.Date.patterns.evr);
								}
							//else{
							//	factToWorkDT = Ext.Date.parse(record.get('EmergencyTeamDuty_DTStart'), Ext.Date.patterns.evr);
							//}
							if (factToWorkDT) {
								record.set('EmergencyTeamDuty_factToWorkDT', Ext.Date.format(factToWorkDT, Ext.Date.patterns.env));
							}
							
								if(record.get('EmergencyTeamDuty_factEndWorkDT')){
									factEndWorkDT = Ext.Date.parse(record.get('EmergencyTeamDuty_factEndWorkDT'), Ext.Date.patterns.evr);
								}
							//else{
							//	factEndWorkDT = Ext.Date.parse(record.get('EmergencyTeamDuty_DTFinish'), Ext.Date.patterns.evr);
							//}
							if (factEndWorkDT) {
								record.set('EmergencyTeamDuty_factEndWorkDT', Ext.Date.format(factEndWorkDT, Ext.Date.patterns.env));
							}
							
							me.emergencyTeamDutyTimeGrid.store.commitChanges();
							me.emergencyTeamDutyTimeGrid.refreshCheckColumnHeader('ComesToWork');
							me.emergencyTeamDutyTimeGrid.refreshCheckColumnHeader('closed');
						});
						*/
					}
				}
			}),
			columns: [
				{ dataIndex: 'EmergencyTeam_id', text: 'ID', key: true, hidden: true, hideable: false },
				{ dataIndex: 'EmergencyTeamDuty_IsCancelledStart', hidden: true, hideable: false },
				{ dataIndex: 'EmergencyTeamDuty_IsCancelledClose', hidden: true, hideable: false },
				{ dataIndex: 'EmergencyTeamDuty_id', text: 'IDduty', key: true, hidden: true, hideable: false  },
				{ dataIndex: 'EmergencyTeamDuty_ChangeComm', hidden: true, },
				{ dataIndex: 'LpuBuilding_Name',
					text: me.isNmpArm ? 'Подразделение НМП' : 'Подразделение СМП',
					flex: 1, hideable: false},
				{ dataIndex: 'EmergencyTeam_Num', text: 'Номер бригады', width: 70, hideable: false},
				
				{ dataIndex: 'EmergencyTeam_HeadShiftFIO', text: 'Старший бригады', flex: 1, hideable: false},				
				{ dataIndex: 'EmergencyTeam_HeadShift2FIO', text: 'Помощник 1', flex: 1, hideable: false},
				
				{ dataIndex: 'EmergencyTeam_Driver2FIO', text: 'Водитель', flex: 1, hideable: false, hidden:true },
				{ dataIndex: 'EmergencyTeam_Assistant1FIO', text: 'Помощник 2', flex: 1, hideable: false },
				{ dataIndex: 'EmergencyTeam_Assistant2FIO', text: 'Третий работник', flex: 1, hideable: false, hidden:true },
				{ dataIndex: 'EmergencyTeam_DriverFIO', text: 'Водитель', flex: 1, hideable: false },
				{ dataIndex: 'EmergencyTeamDuty_DTStartVis', text: 'Начало плановое', width: 120, hideable: false },
				{ dataIndex: 'EmergencyTeamDuty_DTStart', hidden: true, hideable: false },
				{ dataIndex: 'EmergencyTeamDuty_DTFinishVis', text: 'Окончание плановое', width: 120,  hideable: false},
				{ dataIndex: 'EmergencyTeamDuty_DTFinish', hidden: true, hideable: false},
				
				{ dataIndex: 'EmergencyTeamDuty_factToWorkDT', text: 'Начало фактическое', width: 120, hideable: false,
					editor: {
						allowBlank: false,
						format: 'd.m.Y H:i:s',
						xtype: 'swdatetimefield',
						triggerCls: 'x-form-clock-trigger',
						cls: 'stateCombo',
					},
					renderer:Ext.util.Format.dateRenderer('d.m.Y H:i:s')
				},
				{ dataIndex: 'EmergencyTeamDuty_factEndWorkDT', text: 'Окончание фактическое', width: 120, hideable: false,
					editor: {
						allowBlank: false,
						format: 'd.m.Y H:i:s',
						xtype: 'swdatetimefield',
						triggerCls: 'x-form-clock-trigger',
						cls: 'stateCombo',
						listeners: {
							validitychange: function(datefield,newValue) { //121576
								var grid = me.down('grid'),
									tableReс = grid.getSelectionModel().getSelection()[0],
									msg = '';
								
								if (!tableReс.get('ComesToWork') ) {
									msg = 'Установить время фактического окончания возможно, если установлен флаг «Вышел»';
								}
								if (tableReс.get('EmergencyTeamStatus_Code').inlist([1,2,3,17,36,48])) {
									msg = 'Бригада назначена или находится на обслуживании вызова. Установление времени фактического окончания невозможно.';
								};
								
								if(msg){
									Ext.Msg.alert('Сообщение', msg)
									this.setValue(null);
								}
							}
						}
					},
					renderer:Ext.util.Format.dateRenderer('d.m.Y H:i:s')
				}, {
					dataIndex: 'ComesToWork', text: 'Вышел', width: 100, xtype: 'checkcolumn', hideable: false, sortable: false,
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
					onCheckChange: function(rowIndex, checked, dt) {
						var grid = me.emergencyTeamDutyTimeGrid;
						var tableRow = grid.getStore().getAt(rowIndex);

						if (checked) {
							if (Ext.isEmpty(tableRow.get('EmergencyTeamDuty_factToWorkDT'))) {
								tableRow.set('EmergencyTeamDuty_factToWorkDT', dt);
							}
						} else {
							tableRow.set('EmergencyTeamDuty_factToWorkDT', null);
							tableRow.set('EmergencyTeamDuty_IsCancelledStart', true);

						}
					},
					listeners: {
						headerclick: function( ct, column, e, t, eOpts ){
							var grid = me.down('grid'),
								el = Ext.fly(column.getId()).select('div.customCheckAll span').first(),
								store = ct.view.store,
								checked = !el.hasCls('checkedall');
							
							store.each(function(record){
								var dt = Ext.Date.parse(record.get('EmergencyTeamDuty_DTStart'), Ext.Date.patterns.evr);
								if (column.fireEvent('beforecheckchange', column, record.index, checked)) {
									record.set('ComesToWork', checked);
									column.onCheckChange(record.index, checked, dt);
								}
				            });
							grid.refreshCheckColumnHeader('ComesToWork');
						},
						beforecheckchange: function(comp, rowIndex, checked, eOpts){
							var grid = me.down('grid'),
								tableReс = grid.getStore().getAt(rowIndex),
								msg = '';
							
							if (!checked){

								if (tableReс.get('CountCmpCallCards')) {
									msg = 'Бригада находилась на обслуживании вызова. Снятие флага «Вышел» невозможно';
								};
								
								if (tableReс.get('EmergencyTeamStatus_Code').inlist([1,36,48])) {
									msg = 'Бригада находится на обслуживании вызова. Снятие флага «Вышел» невозможно';
								};
								
								if (tableReс.get('closed') ) {
									msg = 'Бригада закрыта. Снимите с бригады признак «Закрыта» и повторите действие';
								};
								
								if(msg){
									Ext.defer(function(){
										Ext.Msg.alert('Сообщение', msg)
									}, 1);
									
									return false;
								};
								
							}
							
						},
						checkchange: function(comp, rowIndex, checked, eOpts){
							var grid = me.emergencyTeamDutyTimeGrid;
							var record = grid.getStore().getAt(rowIndex);
							var dt = null;

							if (getRegionNick() == 'ufa') {
								dt = new Date();
							} else {
								dt = Ext.Date.parse(record.get('EmergencyTeamDuty_DTStart'), Ext.Date.patterns.evr);
							}

							comp.onCheckChange(rowIndex, checked, dt);
							grid.refreshCheckColumnHeader('ComesToWork');
						}
					}
				}, 
				{
					dataIndex: 'closed', text: 'Закрыта', width: 120, xtype: 'checkcolumn', hideable: false, sortable: false,
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
					onCheckChange: function(rowIndex, checked, dt) {
						var grid = me.emergencyTeamDutyTimeGrid;
						var tableRow = grid.getStore().getAt(rowIndex);

						if (checked) {
							if (Ext.isEmpty(tableRow.get('EmergencyTeamDuty_factEndWorkDT'))) {
								tableRow.set('EmergencyTeamDuty_factEndWorkDT', dt);
							}
						} else {
							tableRow.set('EmergencyTeamDuty_factEndWorkDT', null);
							tableRow.set('EmergencyTeamDuty_IsCancelledClose', true);
						}
					},
					listeners: {
						beforecheckchange: function(comp, rowIndex, checked, eOpts){
							var grid = me.down('grid'),
								tableReс = grid.getStore().getAt(rowIndex),
								msg = '';
							
							if (checked){
								if (!tableReс.get('ComesToWork') ) {
									msg = 'Установление флага «Закрыто» доступно, если установлен флаг «Вышел»';
								}
								if (tableReс.get('EmergencyTeamStatus_Code').inlist([1,2,3,17,36,48])) {
									msg = 'Бригада назначена или находится на обслуживании вызова. Установление флага «Закрыта» невозможно';
								};
							};
							
							if(msg){
								Ext.defer(function(){
									Ext.Msg.alert('Сообщение', msg)
								}, 1);
								
								return false;
							}
						},
						headerclick: function( ct, column, e, t, eOpts ){
							var grid = me.down('grid'),
								el = Ext.fly(column.getId()).select('div.customCheckAll span').first(),
								store = ct.view.store;
								//el.toggleCls('checkedall'),
								checked = !el.hasCls('checkedall');
							
							
							/*
							store.each(function(record){
								record.set('closed', el.hasCls('checkedall'));
								var dt = Ext.Date.parse(record.get('EmergencyTeamDuty_DTFinish'), Ext.Date.patterns.evr);
								column.onCheckChange(record.index, el.hasCls('checkedall'), dt);
				            });
							*/
							store.each(function(record){
								var dt = Ext.Date.parse(record.get('EmergencyTeamDuty_DTFinish'), Ext.Date.patterns.evr);
								if (column.fireEvent('beforecheckchange', column, record.index, checked)) {
									record.set('closed', checked);
									column.onCheckChange(record.index, checked, dt);
								}
							});
							grid.refreshCheckColumnHeader('closed');
						},
						'checkchange': function(comp, rowIndex, checked, eOpts){
							var grid = me.emergencyTeamDutyTimeGrid;
							var record = grid.getStore().getAt(rowIndex);
							var dt = null;

							if (getRegionNick() == 'ufa') {
								dt = new Date();
							} else {
								dt = Ext.Date.parse(record.get('EmergencyTeamDuty_DTFinish'), Ext.Date.patterns.evr);
							}

							comp.onCheckChange(rowIndex, checked, dt);
							grid.refreshCheckColumnHeader('closed');
						}
					}
				}, {
					dataIndex: 'EmergencyTeamDuty_Comm', text: 'Комментарий', width: 120, hideable: false, editor: true
				}
			]
		})		
		
		Ext.applyIf(me, {
			items: [
				me.emergencyTeamDutyTimeGrid
			],
			
			 dockedItems: [
                {
                    xtype: 'container',
                    dock: 'bottom',
					layout: 'fit',
                   /* layout: {
						align: 'right',
                        type: 'vbox',
						padding: 3
                    },*/
                    items: [
					
						{
							xtype: 'container',
							dock: 'bottom',
							refId: 'bottomButtons',
							margin: '5 4',
							layout: {
								align: 'top',
								pack: 'end',
								type: 'hbox'
							},
							items: [
								{
									xtype: 'container',
									flex: 1,
									items: [								
										//leftButtons
										{
											xtype: 'button',
											iconCls: 'save16',
											text: 'Сохранить',
											handler: function(){
												me.saveBrigTime(me)
											}
										},
									]
								},
								{
									xtype: 'container',
									layout: {
										type: 'hbox',
										align: 'middle'
									},
									items: [
										//rightButtons
										{
											xtype: 'button',
											text: 'Помощь',
											iconCls   : 'help16',
											handler   : function()
											{
												ShowHelp(me.title);
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
										}	
									]
								}
								
							]
						}
					
					/*
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
										me.saveBrigTime(me)
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
								},
								{
									xtype: 'button',
									iconCls: 'cancel16',
									text: 'Отменить',
									margin: '0 5',
									handler: function(){
										me.close()
									}
								}								
							]
						}
						*/
                    ]
                }
            ]
		});
		
		me.callParent();
	},
	saveBrigTime : function(cmp){
		var grid = cmp.down('grid'),
			store = grid.store,
			me = this,
			editedRecs = [];
			
		store.each(function(record){
			if (record.dirty){
				editedRecs.push({
					EmergencyTeam_id: record.get('EmergencyTeam_id'),
					EmergencyTeamDuty_id: record.get('EmergencyTeamDuty_id'),
					EmergencyTeamDuty_DTStart: record.get('EmergencyTeamDuty_DTStart'),
					EmergencyTeamDuty_DTFinish: record.get('EmergencyTeamDuty_DTFinish'),
					EmergencyTeamDuty_factToWorkDT: Ext.util.Format.date(record.get('EmergencyTeamDuty_factToWorkDT'),  Ext.Date.patterns.evr),
					EmergencyTeamDuty_factEndWorkDT: Ext.util.Format.date(record.get('EmergencyTeamDuty_factEndWorkDT'), Ext.Date.patterns.evr),
					EmergencyTeamDuty_Comm: record.get('EmergencyTeamDuty_Comm'),
					EmergencyTeamDuty_ChangeComm: record.get('EmergencyTeamDuty_ChangeComm'),
					ComesToWork: record.get('ComesToWork'),
					closed: record.get('closed'),
					EmergencyTeamDuty_IsCancelledStart: record.get('EmergencyTeamDuty_IsCancelledStart'),
					EmergencyTeamDuty_IsCancelledClose: record.get('EmergencyTeamDuty_IsCancelledClose')
				})
			}
		});
		
		if (editedRecs.length > 0){
			Ext.Ajax.request({
				url: '/?c=EmergencyTeam4E&m=setEmergencyTeamsWorkComingList',
				params: {
					EmergencyTeamsDutyTimesAndComing: Ext.encode(editedRecs)
				},
				callback: function(opt, success, response) {
					if (success){
						store.commitChanges();
						me.fireEvent('setDutyTimeToEmergencyTeams', editedRecs);
						Ext.Msg.alert('Сохранение', 'Изменения сохранены');
						me.close();
						function hide_message() {
							Ext.defer(function() {
								Ext.MessageBox.hide();
							}, 1500);
						};
						hide_message();
					}
				}
			})
		}
		else{
			me.close();
		}

	}
})

