/* 
Оперативная обстановка по бригадам СМП
*/
 

Ext.define('sw.tools.swEmergencyTeamOperEnv', {
	alias: 'widget.swEmergencyTeamOperEnv',
	extend: 'Ext.window.Window',
	title: 'Оперативная обстановка по бригадам СМП',
	width: 600,
	height: 300,
	maximizable: true,
	modal: true,
	layout: {
        align: 'stretch',
        type: 'vbox'
    },
	initComponent: function() {
		var me = this
		
		var emergencyTeamOperEnvGrid = Ext.create('sw.lib.MongoGrid', {
			flex: 1,
			autoScroll: true,
			stripeRows: true,
			refId: 'emrgTeamOperEnvGrid',
			tbar: [
				{
					xtype: 'button',
					itemId: 'addEmergencyTeamOperEnvButton',
					text: 'Добавить',
					iconCls: 'add16',
					handler: function(){
						Ext.create('sw.tools.subtools.swEmergencyTeamOperEnvAddEdit', {action: 'add'}).show()
					}
				},
				{
					xtype: 'button',
					disabled: true,
					itemId: 'editEmergencyTeamOperEnvButton',
					text: 'Изменить',
					iconCls: 'edit16',
					handler: function(){
						if(emergencyTeamOperEnvGrid.getSelectionModel().getSelection())
						var emgTeam_id = emergencyTeamOperEnvGrid.getSelectionModel().getSelection()[0].get('EmergencyTeam_id')
						Ext.create('sw.tools.subtools.swEmergencyTeamOperEnvAddEdit', {
							action: 'edit',
							EmergencyTeam_id: emgTeam_id
						}).show()
					}
				},
				{
					xtype: 'button',
					disabled: true,
					itemId: 'deleteEmergencyTeamOperEnvButton',
					text: 'Удалить',
					iconCls: 'delete16',
					handler: function(){
						Ext.Msg.show({
						title:'Удаление бригады',
						msg: 'Удалить бригаду СМП?',
						buttons: Ext.Msg.YESNO,
						icon: Ext.Msg.WARNING,
						fn: function(btn){
							if (btn == 'yes'){
								var opts = getGlobalOptions();
								Ext.Ajax.request({
									url: '/?c=EmergencyTeam4E&m=deleteEmergencyTeam',
									params: {
										EmergencyTeam_id: emergencyTeamOperEnvGrid.getSelectionModel().getSelection()[0].get('EmergencyTeam_id'),
										Lpu_id: opts.lpu_id
									},
									callback: function(opt, success, response) {
										if (success){
											if ( response.success == false ) {
												Ext.Msg.alert('Ошибка', 'Ошибка при удалении карты вызова');
											}
											else{
												var rec = emergencyTeamOperEnvGrid.getSelectionModel().getSelection()[0]
												emergencyTeamOperEnvGrid.store.remove(rec)
												me.down('toolbar button[itemId=editEmergencyTeamOperEnvButton]').disable()
												me.down('toolbar button[itemId=deleteEmergencyTeamOperEnvButton]').disable()
											}
										}
									}
								})
							}
						}
					})
					}
				},
				{
					xtype: 'button',
					itemId: 'refreshEmergencyTeamOperEnvGridButton',
					disabled: false,
					text: 'Обновить',
					iconCls: 'refresh16',
					handler: function(){
						emergencyTeamOperEnvGrid.store.reload()
						me.down('toolbar button[itemId=editEmergencyTeamOperEnvButton]').disable()
						me.down('toolbar button[itemId=deleteEmergencyTeamOperEnvButton]').disable()
//						me.down('toolbar button[itemId=emergencyTeamDutyTimeGridButton]').disable()
					}
				},				
				{
					xtype: 'button',
					itemId: 'emergencyTeamDutyTimeGridButton',
					disabled: false,
					text: 'Смена',
					iconCls: 'receipt-ondelay16',
					handler: function(){
						Ext.create('sw.tools.subtools.swSmpEmergencyTeamSetDutyTimeWindow').show()
					}
				},
				{
					xtype: 'button',
					itemId: 'printEmergencyTeamDutyTimeGridButton',
					text: 'Печать',
					iconCls: 'print16',
					handler: function(){
						Ext.ux.grid.Printer.print(emergencyTeamOperEnvGrid)
					}
				}
			],
			store: new Ext.data.JsonStore({
				autoLoad: true,
				numLoad: 0,
				storeId: 'emergencyTeamOperEnvGridGridStore',
				fields: [
					{name: 'EmergencyTeam_id', type: 'int'},
					{name: 'EmergencyTeam_Num', type: 'string'},
					{name: 'Person_Fin', type: 'string'},					
					{name: 'EmergencyTeamStatus_Name', type: 'string'}
				],
				proxy: {
					limitParam: undefined,
					startParam: undefined,
					paramName: undefined,
					pageParam: undefined,
					type: 'ajax',
					url: '/?c=EmergencyTeam4E&m=loadEmergencyTeamOperEnv',
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
					datachanged : function(){}
				}
			}),
			columns: [
				{ dataIndex: 'EmergencyTeam_id', text: 'ID', key: true, hidden: true, hideable: false },
				{ dataIndex: 'EmergencyTeam_Num', text: 'Номер', width: 120 },
				{ dataIndex: 'Person_Fin', text: 'Старший бригады', flex: 1 },				
				{
					header: 'Статус',
					dataIndex: 'EmergencyTeamStatus_Name',
					width: 200,
					editor:
						new Ext.form.field.ComboBox({
							typeAhead: true,
							tableName: 'EmergencyTeamStatus',
							triggerAction: 'all',
							displayField: 'EmergencyTeamStatus_Name',
							valueField: 'EmergencyTeamStatus_Name',
							queryMode: 'local',
							editable: false,
							enableKeyEvents : true,
							listConfig: {
								minWidth: '180'
							},
							tpl: '<tpl for="."><div class="x-boundlist-item">'+
							'<font color="red" style="font-size:11px;">{EmergencyTeamStatus_Code}</font>' + ' ' +
							'<font style="font-size:11px;">{EmergencyTeamStatus_Name}</font>'+
							'</div></tpl>',
							store: Ext.create ('Ext.data.Store', {
								autoLoad: false,
								storeId: 'EmergencyTeamStatusStore',
								fields: [
									{name: 'EmergencyTeamStatus_id', type:'int'},
									{name: 'EmergencyTeamStatus_Code', type:'int'},
									{name: 'EmergencyTeamStatus_Name', type:'string'}
								],
								sorters: {
									property: 'EmergencyTeamStatus_id',
									direction: 'ASC'
								},
								url : '/?c=MongoDBWork&m=getData'
							}),
							listeners: {
								select: function( combo, records, eOpts )
								{
									var status = records[0].get('EmergencyTeamStatus_id'),
										team = emergencyTeamOperEnvGrid.getView().getSelectionModel().selected.items[0].get('EmergencyTeam_id'),
										armType = sw.Promed.MedStaffFactByUser.last.ARMType;
									if (status && team)
									{
										Ext.Ajax.request({
											url: '/?c=EmergencyTeam4E&m=setEmergencyTeamStatus',
											callback: function(opt, success, response) {
												if (success){
													var armWindow = Ext.getCmp('smpHeadDutyWin').up('window[refId=smpHeadDutyWorkPlace]'),
														response_obj = Ext.decode(response.responseText),
														data = {'EmergencyTeam_id':team};
													if (armWindow){
														armWindow.socket.emit('changeEmergencyTeamStatus', data, 'changeStatus', function(data){});
													}
												}
											}.bind(this),
											params: {
												'EmergencyTeamStatus_id': status,
												'EmergencyTeam_id':	team,
												'ARMType': armType
											}
										})
									}
								}
							}
						})
				}
				
			],
			listeners: {
				beforecellclick: function( cmp, td, cellIndex, record, tr, rowIndex, e, eOpts )
				{
					me.down('toolbar button[itemId=editEmergencyTeamOperEnvButton]').enable()
					me.down('toolbar button[itemId=deleteEmergencyTeamOperEnvButton]').enable()
					me.down('toolbar button[itemId=emergencyTeamDutyTimeGridButton]').enable()
				}
			}
		})
		

		Ext.applyIf(me, {
			items: [
				emergencyTeamOperEnvGrid
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
		
		me.callParent()
	}
})

