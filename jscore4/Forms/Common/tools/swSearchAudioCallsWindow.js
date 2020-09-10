/* 
	Поиск аудиозаписи
*/

Ext.define('sw.tools.swSearchAudioCallsWindow', {
	alias: 'widget.swSearchAudioCallsWindow',
	extend: 'Ext.window.Window',
	title: 'Аудиозаписи вызовов СМП',
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
			curArm = sw.Promed.MedStaffFactByUser.current.ARMType || sw.Promed.MedStaffFactByUser.last.ARMType,
			group_list = getGlobalOptions().groups.split('|');

		me.isNmpArm = curArm.inlist(['dispnmp','dispcallnmp', 'dispdirnmp']);

		me.title = me.isNmpArm ? 'Аудиозаписи вызовов НМП' : 'Аудиозаписи вызовов СМП',
		
		me.addEvents({
			//setDutyTimeToEmergencyTeams: true
		});

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
				{ xtype: 'tbfill' },
				Ext.create('sw.dateCurrentDay'),
				Ext.create('sw.dateCurrentWeek'),
				Ext.create('sw.dateCurrentMonth')
			]
		});
		
		var audioFilters = Ext.create('Ext.form.FieldSet', {
			title: 'Фильтры',
			collapsed: true,
			collapsible: true,
			dock: 'top',			
			items: [
				{
					xtype: 'BaseForm',
					id: this.id+'_mainPanel',
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
								hideTrigger: true,
								keyNavEnabled: false,
								mouseWheelEnabled: false
							},
							items:[
								{
									labelWidth: 140,
									xtype: 'numberfield',
									name: 'CmpCallCard_Numv',
									fieldLabel: '№ вызова за день',
									enableKeyEvents : true
								},
								{
									xtype: 'numberfield',
									name: 'CmpCallCard_Ngod',
									fieldLabel: '№ вызова за год',
									enableKeyEvents : true
								}						
							],
							
						}, 
						{
							//columnWidth: 0.5,
							layout: 'hbox',
							flex: 1,
							xtype: 'container',
							defaults: {
								labelWidth: 100,
								//width: 300,
								flex: 1,
								margin: '2 10',
								labelAlign: 'right'
							},
							items:[
								{
									labelWidth: 140,
									xtype: 'textfield',
									name: 'Person_Surname',
									fieldLabel: 'Фамилия',
									enableKeyEvents : true
								},
								{
									xtype: 'textfield',
									name: 'Person_Firname',
									fieldLabel: 'Имя',
									enableKeyEvents : true
								},
								{
									xtype: 'textfield',
									name: 'Person_Secname',
									fieldLabel: 'Отчество',
									enableKeyEvents : true
								}
							]
						},
						{
							//columnWidth: 0.5,
							layout: 'hbox',
							align: 'stretch',
							flex: 1,
							xtype: 'container',
							defaults: {
								labelWidth: 100,
								//width: 400,
								flex: 1,
								margin: '2 10',
								labelAlign: 'right'
							},
							items:[
								{
									labelWidth: 140,
									autoFilter: true,
									enableKeyEvents : true,
									fieldLabel : "Диспетчер",
									name : "MedPersonal_id",
									xtype : "swmedpersonalcombo",
									typeAhead: true,
									enableKeyEvents : true
								},
								/*{
									xtype: 'lpuLocalCombo',									
									name: 'Lpu_id',
									fieldLabel: 'МО',							
									displayTpl: '<tpl for=".">{Lpu_Nick}</tpl>',
									tpl: '<tpl for="."><div class="x-boundlist-item">{Lpu_Nick}</div></tpl>',
									enableKeyEvents : true,
									listeners: {
										render: function(a){
											console.log(a.store.getCount());
										}
									}
								},*/
								{
									xtype: 'lpuWithNestedSmpUnitsCombo',									
									name: 'Lpu_id',
									lockRemoteLpuBuilding: true,
									fieldLabel: 'МО',							
									displayTpl: '<tpl for=".">{Lpu_Nick}</tpl>',
									tpl: '<tpl for="."><div class="x-boundlist-item">{Lpu_Nick}</div></tpl>',
									enableKeyEvents : true,
									listeners: {
										select: function(cmb,rec){
											if(rec[0])
											{
												audioFilters.down('SmpUnitsFromOptions').store.load({
													params: {
														Lpu_id: rec[0].get('Lpu_id')
													}
												})
											}
										}
									}
								},
								{
									xtype: 'SmpUnitsFromOptions',
									name: 'LpuBuilding_id',
									fieldLabel: 'П/С',
									displayTpl: '<tpl for="."> {LpuBuilding_Code}. {LpuBuilding_Name} </tpl>',
									tpl: '<tpl for="."><div class="x-boundlist-item">'+
										'<font color="red">{LpuBuilding_Code}</font> {LpuBuilding_Name}'+
										'</div></tpl>',
									enableKeyEvents : true,
									listeners: {
										select: function(a,b,c){
											if(b[0])
											audioFilters.down('lpuLocalCombo').setValue(b[0].get('Lpu_id'));
										}							
									}									
								}
							]
						},
						{
							//columnWidth: 0.5,
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
									itemId: 'playAudioCallButton',
									//disabled: false,
									text: 'Найти',
									//iconCls: 'refresh16',
									handler: function(){
										me.searchAudioCalls();
									}
								},
								{
									xtype: 'button',
									itemId: 'exportAudioCall',
									text: 'Сброс',
									//iconCls: 'print16',
									handler: function(){					
										me.resetFilters();
									}
								}
							]
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
							me.searchAudioCalls();
						}
					});						
				}
			}
			
		});
		
		var audioCallsGrid = Ext.create('Ext.grid.Panel', {
			plugins: new Ext.grid.plugin.CellEditing({
				clicksToEdit: 1
			}),
			viewConfig: {
				loadingText: 'Загрузка'
			},
			flex: 1,
			autoScroll: true,
			stripeRows: true,
			refId: 'audioCallsGrid',
			tbar: [
				{
					xtype: 'button',
					itemId: 'playAudioCallButton',
					disabled: false,
					text: 'Прослушать',
					disabled: true,
					//iconCls: 'refresh16',
					handler: function(){
						me.listenAudioRecord();
					}
				},
				{
					xtype: 'button',
					itemId: 'exportAudioCall',
					text: 'Экспортировать',
					disabled: true,
					handler: function(){					
						me.exportAudioRecord();
					}
				}
			],
			listeners: {
				itemclick: function( me, record, item, index, e, eOpts ){

					audioCallsGrid.down('button[itemId=playAudioCallButton]').enable();					
					
					//экспорт доступен только для группы аудита
					if(Ext.Array.contains(group_list,'recordCallsAudit'))
						audioCallsGrid.down('button[itemId=exportAudioCall]').enable();
				}
			},
			store: new Ext.data.JsonStore({
				autoLoad: true,
				storeId: 'EmergencyTeamDutyTimeStore',
				fields: [
					{name: 'CmpCallRecord_id', type: 'int'},
					{name: 'CmpCallRecord_RecordPlace', type: 'string', dateReadFormat: "D-m-Y"},
					{name: 'CmpCallRecord_insDT', type: 'string'},
					{name: 'Lpu_Nick', type: 'string'},
					{name: 'LpuBuilding_Name', type: 'string'},
					{name: 'Person_FIO', type: 'string'},		
					{name: 'Person_Surname', type: 'string'},		
					{name: 'Person_Firname', type: 'string'},		
					{name: 'Person_Secname', type: 'string'},		
					{name: 'MedPerson_FIO', type: 'string'},					
					{name: 'CmpCallCard_Numv', type: 'int'},
					{name: 'CmpCallCard_Ngod', type: 'int'},
					{name: 'MedPersonal_id', type: 'int'},
					{name: 'LpuBuilding_id', type: 'int'},
					{name: 'Lpu_id', type: 'int'},
				],				
				proxy: {
					limitParam: undefined,
					startParam: undefined,
					paramName: undefined,
					pageParam: undefined,
					type: 'ajax',
					url: '/?c=CmpCallCard4E&m=getCallAudioList',
					reader: {
						type: 'json',
						successProperty: 'success',
						root: 'data'
					},
					extraParams:{
						dateFinish:	Ext.Date.format(cald.dateTo, 'd.m.Y'),
						dateStart:	Ext.Date.format(cald.dateFrom, 'd.m.Y')
					},
					actionMethods: {
						create : 'POST',
						read   : 'POST',
						update : 'POST',
						destroy: 'POST'
					}
				},
				sorters: {
					property: 'EmergencyTeamDuty_DTStart',
					direction: 'ASC'
				},
				listeners: {
					load : function(cmp, records, successful, eOpts){

					}
				}
			}),
			columns: 
				[
					{ dataIndex: 'CmpCallRecord_id', text: 'ID', key: true, hidden: true, hideable: false },
					{ dataIndex: 'CmpCallRecord_RecordPlace', text: 'path', key: true, hidden: true, hideable: false },
					
					{
						dataIndex: 'selectedRecs', width: 30, xtype: 'checkcolumn', hideable: false, sortable: false,
						renderTpl: [
							'<div id="{id}-titleEl" role="presentation" {tipMarkup}class="', Ext.baseCSSPrefix, 'column-header-inner',
								'<tpl if="empty"> ', Ext.baseCSSPrefix, 'column-header-inner-empty</tpl>">',
								'<div class="customCheckAll" style="left: 3px;">',
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
									record.set('selectedRecs', el.hasCls('checkedall'));
								});
							}
						}
					},
					
					{ dataIndex: 'CmpCallRecord_insDT', width: 120, text: 'Дата аудиозаписи', hideable: false, renderer: Ext.util.Format.dateRenderer('d.m.Y H:i:s') },
					{ dataIndex: 'Person_FIO', text: 'ФИО пациента', flex: 1, hideable: false},
					{ dataIndex: 'Lpu_Nick', text: 'МО', flex: 1, hideable: false},
					{ dataIndex: 'LpuBuilding_Name', text: 'Подстанция', flex: 1, hideable: false},				
					{ dataIndex: 'MedPerson_FIO', text: 'ФИО диспетчера', flex: 1, hideable: false},
					{ dataIndex: 'MedPersonal_id', text: 'MedPersonal_id', hideable: false, hidden: true},
					{ dataIndex: 'Lpu_id', text: 'Lpu_id', hideable: false, hidden: true},
					{ dataIndex: 'LpuBuilding_id', text: 'LpuBuilding_id', hideable: false, hidden: true},
					{ dataIndex: 'Person_Surname', text: 'фамилия', flex: 1, hideable: false, hidden: true},
					{ dataIndex: 'Person_Firname', text: 'имя', flex: 1, hideable: false, hidden: true},
					{ dataIndex: 'Person_Secname', text: 'отчество', flex: 1, hideable: false, hidden: true},
					{ dataIndex: 'CmpCallCard_Numv', text: '№ вызова за день', width: 120, hideable: false},
					{ dataIndex: 'CmpCallCard_Ngod', text: '№ вызова за год', width: 120, hideable: false}
				]
		});
		
		Ext.applyIf(me, {
			items: [
				audioFilters,
				audioCallsGrid
			],
			
			 dockedItems: [				
                {
                    xtype: 'container',
                    dock: 'bottom',
					layout: 'fit',
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
								/*{
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
								},*/
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
                    ]
                }
            ]
		});
		
		me.callParent();
		
	},

	show: function(){

		var me = this,
			grid = me.down('grid[refId=audioCallsGrid]'),
			baseForm = Ext.getCmp(me.id + '_mainPanel'),
			lpuCombo = baseForm.getForm().findField('Lpu_id'),
			group_list = getGlobalOptions().groups.split('|');

		if(!Ext.Array.contains(group_list,'recordCallsAudit')){
			lpuCombo.setValue(parseInt(getGlobalOptions().lpu_id));
			lpuCombo.setDisabled(true);
		}else{
			lpuCombo.setDisabled(false);
			lpuCombo.clearValue();
		}

		me.searchAudioCalls();
		me.callParent();

	},
	searchAudioCalls: function(){
		var win = this,
			grid = win.down('grid[refId=audioCallsGrid]'),
			baseForm = Ext.getCmp(win.id + '_mainPanel'),
			values = baseForm.getForm().getFieldValues(),
			store = grid.getStore();
			
		store.clearFilter();
		
		for(var i in values){
			if(values[i]) store.filter(i, values[i])
		}
	},
	
	resetFilters: function(){
		var win = this,
			grid = win.down('grid[refId=audioCallsGrid]'),
			baseForm = Ext.getCmp(win.id + '_mainPanel'),
			store = grid.getStore();
		
		store.clearFilter();
		baseForm.getForm().reset();
	},
	
	listenAudioRecord: function(){
		var win = this,
			grid = win.down('grid[refId=audioCallsGrid]'),
			selectedRec = grid.getSelectionModel().getSelection()[0];
		
		if(!selectedRec)return false;
		Ext.create('common.tools.swCmpCallRecordListenerWindow',{
			record_id : selectedRec.get('CmpCallRecord_id')
		}).show();
	},
	
	exportAudioRecord: function(){
		var win = this,
			grid = win.down('grid[refId=audioCallsGrid]'),
			selectedRec = grid.getSelectionModel().getSelection()[0],
			markedRecs = [];
		
		grid.getStore().each(function(record){
			if (record.get('selectedRecs')){
				markedRecs.push( record.get('CmpCallRecord_id') );
			}
		});

		var recs = markedRecs.length?markedRecs.join(','):selectedRec.get('CmpCallRecord_id');
		
		Ext.Ajax.request({
			url: '/?c=CmpCallCard4E&m=getExportCallAudios',
			params: {
				audioIds: recs
			},
			callback: function(opt, success, response) {
				if (success){
					var res = Ext.JSON.decode(response.responseText);

					if(res.success){
						
						Ext.Msg.show({
							title:'Экспорт',
							msg: 'Сохранить выбранные аудиозаписи на ПК?',
							buttons: Ext.Msg.YESNO,
							icon: Ext.Msg.WARNING,
							fn: function(btn){
								if (btn == 'yes'){
									//window.location.assign(res.Link);
									
									window.open(res.Link, 'Download'); 
								}
							}
						})
					}
				} else {
					Ext.MessageBox.alert('Ошибка', 'Произошла ошибка при экспорте данных');
				}
			}.bind(this)
		});	
	}
});