/* 
 swSmpEmergencyTeamSetDutyTimeWindow - смены бригад СМП
 */


Ext.define('sw.tools.subtools.swSmpEmergencyTeamSetDutyTimeWindow', {
	alias: 'widget.swSmpEmergencyTeamSetDutyTimeWindow',
	extend: 'Ext.window.Window',
	title: 'Смены бригад СМП',
	width: 900,
	height: 600,
	modal: true,
	maximizable: true,
//	layout: {
//        align: 'stretch',
//        type: 'vbox'
//    },
	layout: 'border',
	bodyBorder: false,
	manageHeight: false,
	initComponent: function() {
		var me = this;

		me.width = (Ext.ComponentQuery.query('panel[id=inPanel]')[0].getWidth()/10) * 9;
		
		//модель списка бригад
		Ext.define('EmrgTeamOperEnvShortGridModel', {
			extend: 'Ext.data.Model',
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
					{name: 'EmergencyTeamSpec_id', type: 'int'},					
					
					{name: 'EmergencyTeam_HeadShift', type: 'int'},
					{name: 'EmergencyTeam_HeadShift2', type: 'int'},					
					{name: 'EmergencyTeam_Driver', type: 'int'},
					{name: 'EmergencyTeam_Driver2', type: 'int'},					
					{name: 'EmergencyTeam_Assistant1', type: 'int'},
					{name: 'EmergencyTeam_Assistant2', type: 'int'},
					{name: 'EmergencyTeam_Head1ShiftTime', type: 'string'},
					{name: 'EmergencyTeam_Head2ShiftTime', type: 'string'},
					{name: 'EmergencyTeam_Assistant1ShiftTime', type: 'string'},
					{name: 'EmergencyTeam_Assistant2ShiftTime', type: 'string'},					
					{name: 'EmergencyTeam_Driver1ShiftTime', type: 'string'},
					{name: 'EmergencyTeam_Driver2ShiftTime', type: 'string'},
					
					{name: 'EmergencyTeam_HeadShiftFIO', type: 'string'},
					{name: 'EmergencyTeam_HeadShift2FIO', type: 'string'},					
					{name: 'EmergencyTeam_DriverFIO', type: 'string'},
					{name: 'EmergencyTeam_Driver2FIO', type: 'string'},					
					{name: 'EmergencyTeam_Assistant1FIO', type: 'string'},
					{name: 'EmergencyTeam_Assistant2FIO', type: 'string'},
					{name: 'EmergencyTeam_DutyTime', type: 'string'},
					{name: 'GeoserviceTransport_id', type: 'int'},
					{name: 'EmergencyTeamDuty_DTStart', type: 'string'},
					{name: 'EmergencyTeamDuty_DTFinish', type: 'string'},
					{name: 'medPersonCount', type: 'int'},
					{name: 'EmergencyTeamSpec_Code', type: 'string'}
//					{name: 'dragGroup', type: 'bool',
//						convert: function(v){
//							return /*(v === "A" || v === true) ? true : */false;
//						}
//					}
					
				]
		});

		
		var emergencyTeamOperEnvShortGrid = Ext.create('Ext.grid.Panel', {
			width: 230,
			region: 'west',
			autoScroll: true,
			stripeRows: true,
			refId: 'emrgTeamOperEnvShortGrid',
			store: new Ext.data.JsonStore({
				autoLoad: true,
				numLoad: 0,
				storeId: 'emergencyTeamOperEnvGridGridStore',
				model: 'EmrgTeamOperEnvShortGridModel',
				proxy: {
					limitParam: undefined,
					startParam: undefined,
					paramName: undefined,
					pageParam: undefined,
					type: 'ajax',
					url: '/?c=EmergencyTeam4E&m=loadEmergencyTeamList',
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
			}),
			columns: [
				{ dataIndex: 'EmergencyTeam_id', text: 'ID', key: true, hidden: true, hideable: false },
				{ dataIndex: 'EmergencyTeam_Num', text: 'Номер', width: 50, hideable: false },
				{ dataIndex: 'EmergencyTeam_HeadShiftFIO', text: 'Старший бригады', flex: 1, hideable: false }				
			],
			listeners: {
				celldblclick: function( cmp, td, cellIndex, record, tr, rowIndex, e, eOpts )
				{
					var win = Ext.create('sw.tools.subtools.swEmergencyTeamOperEnvAddEdit', {
						action: 'view',
						EmergencyTeam_id: record.get('EmergencyTeam_id')
					});
					win.show();
				}
			}
			
		})
		
		Ext.Ajax.request({
			url: '/?c=EmergencyTeam4E&m=loadEmergencyTeamDutyTimeListGrid',
			params: {},
			callback: function(opt, success, response) {
				if (success){
					var calendarExt = Ext.create('sw.lib.CalendarExt', {
						flex: 1,
						region: 'center',
						data: Ext.JSON.decode(response.responseText).data,
						startDate: 'EmergencyTeamDuty_DTStart',
						endDate: 'EmergencyTeamDuty_DTFinish',
						subject_id: 'EmergencyTeam_id',
						addedRec_id: 'EmergencyTeamDuty_id',
						linkGrid: emergencyTeamOperEnvShortGrid,
						linkGridLengthIntervalField: 'EmergencyTeam_DutyTime',
						linkGrigMultipleDrag: true,
						dragViewFields: {'Номер бригады':'EmergencyTeam_Num', 'Ст. бригады':'EmergencyTeam_HeadShiftFIO'},
						listeners:{
							intervalClick: function(subjid){
								var win = Ext.create('sw.tools.subtools.swEmergencyTeamOperEnvAddEdit', {
									action: 'view',
									EmergencyTeam_id: subjid
								});
								win.show();
								//console.log('intervalClick', subjid)
							},
							intervalDelete: function(rec){
								
								var armWindow = Ext.getCmp('smpHeadDutyWin').up('window[refId=smpHeadDutyWorkPlace]');												
								
								if (armWindow){
									if(armWindow.socket.socket.connected){
										armWindow.socket.emit('changeEmergencyTeamStatus', rec, 'deleteTimeInterval', function(data){
											Ext.Ajax.request({
												url: '/?c=EmergencyTeam4E&m=deleteEmergencyTeamDutyTime',
												params: {
													EmergencyTeamDuty_id: rec.EmergencyTeamDuty_id
												},
												callback: function(opt, success, response) {
													if (success){

													}
												}
											})
										});
									}
									else{
										Ext.Ajax.request({
											url: '/?c=EmergencyTeam4E&m=deleteEmergencyTeamDutyTime',
											params: {
												EmergencyTeamDuty_id: rec.EmergencyTeamDuty_id
											},
											callback: function(opt, success, response) {
												if (success){

												}
											}
										})
									}
								};					
							},
							intervalAdd: function(intervalArray, callback){
								if (intervalArray)
								{
									console.log('intervalArray', intervalArray);
									if(intervalArray.length==0){return false;}
									Ext.Ajax.request({
										url: '/?c=EmergencyTeam4E&m=saveEmergencyTeamDutyTime',
										params: {
											EmergencyTeamsDutyTimes: Ext.encode(intervalArray)
										},
										callback: function(opt, success, response) {
											if (success){
												var callbackParams =  Ext.decode(response.responseText),
													armWindow = Ext.getCmp('smpHeadDutyWin').up('window[refId=smpHeadDutyWorkPlace]'),
													emergTeamStore = emergencyTeamOperEnvShortGrid.store,
													arrTeam = [];
											
												if (armWindow){
													//отправляем запись(и)
													//взять из сторе
													//мб несколько записей
													for (var i in callbackParams){
														var rec = emergTeamStore.findRecord('EmergencyTeam_id', callbackParams[i][0].EmergencyTeam_id),	
															teamData = rec.data;
															
														teamData.EmergencyTeamDuty_DTStart = intervalArray[i].EmergencyTeamDuty_DTStart;
														teamData.EmergencyTeamDuty_DTFinish = intervalArray[i].EmergencyTeamDuty_DTFinish;
														teamData.Person_Fin = teamData.EmergencyTeam_HeadShiftFIO;
														arrTeam.push(teamData);
													}
													armWindow.socket.emit('addTimeEmergencyTeams', arrTeam, 'addTimeInterval', function(data){});
												}
												callback(callbackParams);
											}
										}
									})
								}
							},
							intervalChange: function(teamid, idInterval, oldStartDate, oldEndDate, newStartDate, newEndDate){
								var rec = {
										EmergencyTeam_id: teamid,
										EmergencyTeamDuty_id: idInterval,
										EmergencyTeamDuty_DateStart: newStartDate,
										EmergencyTeamDuty_DateFinish: newEndDate
								};
								Ext.Ajax.request({
									url: '/?c=EmergencyTeam4E&m=editEmergencyTeamDutyTime',
									params: rec,
									callback: function(opt, success, response) {
										if (success){
											var armWindow = Ext.getCmp('smpHeadDutyWin').up('window[refId=smpHeadDutyWorkPlace]');												

											if (armWindow){
												armWindow.socket.emit('changeEmergencyTeamStatus', rec, 'changeTimeInterval', function(data){});
											};	
											
										}
									}
								})
							},
							refreshData: function(){
								//console.log('refreshData')
							},
							intervalAddError: function(rec, data, msg){
								Ext.Msg.show({
									title: 'Ошибка',
									msg: 'Необходимо указать продолжительность смены',
									buttons: Ext.Msg.YESNOCANCEL,
									icon: Ext.Msg.WARNING,
									fn: function(btn){
										if (btn == 'yes'){
											var win = Ext.create('sw.tools.subtools.swEmergencyTeamOperEnvAddEdit', {
												action: 'edit',
												EmergencyTeam_id: rec.get('EmergencyTeam_id')
											});
											win.show(true, function(){
												this.down('form').getForm().findField('EmergencyTeamDuty_DTStart').focus(false, 100)
											}, win);
											win.on('close', function(){
												var hoursDuty = this.down('form').getForm().findField('EmergencyTeamDuty_DTStart').getValue(),
												recordsToAdd = new Ext.util.MixedCollection();
												if (hoursDuty){
													recordsToAdd.add('fromform', {
														record:rec, hours:hoursDuty, startDate:data}
													);
													calendarExt.addInterval( recordsToAdd );
												}
											})
										}
									}
								})
							},
							multiTaskAddError: function(msg){
								Ext.Msg.show({
									title: 'Ошибка',
									msg: 'Для добавления необходимо указать продолжительность смены для бригад: </br>'+msg,
									buttons: Ext.Msg.OK,
									icon: Ext.Msg.WARNING,
									fn: function(btn){
										if (btn == 'yes'){

										}
									}
								})
							}
						}
					})
					me.add(calendarExt)			
				}
			}.bind(this)
		})		
		
		
		Ext.applyIf(me, {
			items: [
				emergencyTeamOperEnvShortGrid
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
									iconCls: 'cancel16',
									text: 'Закрыть',
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
		})
		
		me.callParent(arguments)
	}
})

