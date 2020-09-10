/*
 swEmergencyTeamOperEnvAddEdit - окно добавления/изменения Бригад СМП
 */


Ext.define('sw.tools.subtools.swEmergencyTeamOperEnvAddEdit', {
	alias: 'widget.swEmergencyTeamOperEnvAddEdit',
	extend: 'Ext.window.Window',
	width: 800,	
	layout: 'fit',
	bodyBorder: false,
	manageHeight: false,
	modal: true,
	maximizable: true,
	initComponent: function() {
		var me = this;
		me.height = 450;
		var conf = me.initialConfig,
			mytitle = 'Информация о бригаде';
		switch(conf.action){
			case 'add' :  { mytitle +=': Добавление'; break; }
			case 'edit' : { mytitle +=': Редактирование'; break; }
			case 'view' : { mytitle +=': Просмотр'; break; }
		}
		
		me.title = mytitle;
		
		me.on('show', function(){
			
			if (conf.action=='view'){				
				var bForm = me.down('form[refId=emergencyTeamOperEnvEditFormPanel]').getForm(),
					allFields = bForm.getFields().items,
					buttonSaveTeam = me.down('button[refId=saveBtn]');

				for(var field in allFields){
					if(typeof allFields[field].setReadOnly == 'function')
						allFields[field].setReadOnly(true);
				}
				
				bForm.findField('EmergencyTeamDuty_DTFinish').setDisabled(true);
				bForm.findField('EmergencyTeamDuty_DTStart').setDisabled(true);
				buttonSaveTeam.disable();
			}
			else{
				me.down('form').isValid();
			}
		})

		var medPersonallist = Ext.create('Ext.data.Store', {
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
				//noCache:false,
				type: 'ajax',
				url: '/?c=MedPersonal4E&m=getMedPersonalCombo',
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
		
		var CMPTabletPC = Ext.create('sw.CMPTabletPC',{
			name: 'CMPTabletPC_id',
			fieldLabel: 'Планшетный компьютер',
			allowBlank: true,
			labelWidth: 170
		});
		
		var emergencyTeamOperEnvEditFormPanel = Ext.create('sw.BaseForm', {
			refId: 'emergencyTeamOperEnvEditFormPanel',
			frame: true,
			id: me.id+'BaseForm',
			border: false,
			//dock: 'top',
			layout: 'auto',			
			items: [
				{
				xtype: 'fieldset',
				title: 'Общая информация',
				refId: 'commonInfo',
				layout: {
					type: 'hbox',
					align: 'stretch'
				},
				items: [
					{
						xtype: 'hidden',
						name: 'EmergencyTeam_id'
					},
					{
						xtype: 'container',
						flex: 2,
						//refId: 'commonInfo',
						margin: '0 10',
						layout: {
							type: 'vbox',
							align: 'stretch'
						},
						items: [
							{
								xtype: 'smpUnits',
								name: 'LpuBuilding_id',
								fieldLabel: 'Подразделение СМП',
								tabIndex: 22,
								//allowBlank: false,
								labelAlign: 'right',
								labelWidth: 130
							},
							{
								xtype: 'transFieldDelbut',
								fieldLabel: 'Номер бригады',
								labelAlign: 'right',
								labelWidth: 130,
								translate: false,
								//allowBlank: false,
								name: 'EmergencyTeam_Num',
								maskRe: /[0-9:]/
							},
							{
								xtype: 'transFieldDelbut',
								fieldLabel: 'Бортовой номер',
								labelAlign: 'right',
								labelWidth: 130,
								translate: false,
								//allowBlank: false,
								name: 'MedProductCard_BoardNumber',
								maskRe: /[0-9:]/
							},
							
							/*
							{
								xtype: 'swEmergencyTeamSpecCombo',
								labelAlign: 'right',
								labelWidth: 130,
								allowBlank: false,
								name: 'EmergencyTeamSpec_id'
							},
							*/
							{
								xtype: 'transFieldDelbut',
								fieldLabel: 'Гос. номер машины',
								labelAlign: 'right',
								labelWidth: 130,
								translate: false,
								name: 'EmergencyTeam_CarNum'
							},
							{
								xtype: 'transFieldDelbut',
								fieldLabel: 'Марка машины',
								labelAlign: 'right',
								labelWidth: 130,
								translate: false,
								name: 'EmergencyTeam_CarBrand'
							}
						]
					},
					{
						xtype: 'container',
						flex: 2,
						refId: 'stuffInfo',
						margin: '0 10',
						layout: {
							type: 'vbox',
							align: 'stretch'
						},
						items: [
							{
								xtype: 'datefield',
								labelAlign: 'right',
								format: 'd.m.Y',
								fieldLabel: 'Дата',
								allowBlank: true,
								labelWidth: 170,
								name: 'EmergencyTeamDuty_DT'
							},
							{
								xtype: 'datefield',
								name: 'EmergencyTeamDuty_DTStart',
								fieldLabel: 'Смена с',
								format: 'H:i',
								hideTrigger: true,
								invalidText: 'Неправильный формат времени. Дата должна быть указана в формате ЧЧ:ММ',
								plugins: [new Ux.InputTextMask('99:99')],
								labelAlign: 'right',
								labelWidth: 170
							},{
								xtype: 'datefield',
								name: 'EmergencyTeamDuty_DTFinish',
								fieldLabel: 'Смена по',
								format: 'H:i',
								hideTrigger: true,
								invalidText: 'Неправильный формат времени. Дата должна быть указана в формате ЧЧ:ММ',
								plugins: [new Ux.InputTextMask('99:99')],
								labelAlign: 'right',
								labelWidth: 170
							},
							{
								xtype: 'swEmergencyTeamWialonCombo',
								labelAlign: 'right',
								fieldLabel: 'GPS/ГЛОНАСС',
								allowBlank: true,
								labelWidth: 170,
								name: 'GeoserviceTransport_id'
							},
								CMPTabletPC
							,{
								xtype: 'textfield',
								labelAlign: 'right',
								fieldLabel: 'Номер SIM карты',
								allowBlank: true,
								labelWidth: 170,
								name: 'CMPTabletPC_SIM'
							}
						]
					}
					]
				},
				{
					xtype: 'fieldset',
					title: 'Состав бригады',															
					layout: {
						type: 'hbox',						
						align: 'stretch',
						pack: 'center',
						padding: '0 10'
					},
					items: [						
						{
							xtype: 'container',
							flex: 1,							
							layout: {
								type: 'vbox',
								align: 'stretch'
							},
							items: [
								{
									xtype: 'container',
									layout: 'hbox',
									margin: '2 0 2 0',
									items: [
										{
											xtype: 'swEmergencyFIOCombo',
											labelAlign: 'right',
											labelWidth: 110,
											store: medPersonallist,
											fieldLabel: 'Старший бригады',
											name: 'EmergencyTeam_HeadShift',
											flex: 1
										},
										{
											xtype: 'datefield',
											width: 100,
											fieldLabel: 'С',
											labelAlign: 'right',
											labelWidth: 20,
											hideTrigger: true,
											format: 'H:i',
											name: 'EmergencyTeam_Head1StartTime',
											//plugins: [new Ux.InputTextMask('99:99')],
											invalidText: 'Неправильный формат времени. Дата должна быть указана в формате ЧЧ:ММ',
										},
										{
											xtype: 'datefield',
											margins: '0 0 0 10',
											width: 100,
											fieldLabel: 'По',
											labelAlign: 'right',
											labelWidth: 20,
											hideTrigger: true,
											format: 'H:i',
											name: 'EmergencyTeam_Head1FinishTime',
											//plugins: [new Ux.InputTextMask('99:99')],
											invalidText: 'Неправильный формат времени. Дата должна быть указана в формате ЧЧ:ММ',
										}
									]
								},
								{
									xtype: 'container',
									layout: 'hbox',
									margin: '2 0 2 0',
									items: [
										{
											xtype: 'swEmergencyFIOCombo',
											labelAlign: 'right',
											labelWidth: 110,
											store: medPersonallist,
											fieldLabel: 'Помощник 1',
											name: 'EmergencyTeam_HeadShift2',
											flex: 1
										},
										{
											xtype: 'datefield',
											width: 100,
											fieldLabel: 'С',
											labelAlign: 'right',
											labelWidth: 20,
											hideTrigger: true,
											format: 'H:i',
											name: 'EmergencyTeam_Assistant1StartTime',
											//plugins: [new Ux.InputTextMask('99:99')],
											invalidText: 'Неправильный формат времени. Дата должна быть указана в формате ЧЧ:ММ',
										},
										{
											xtype: 'datefield',
											margins: '0 0 0 10',
											width: 100,
											fieldLabel: 'По',
											labelAlign: 'right',
											labelWidth: 20,
											hideTrigger: true,
											format: 'H:i',
											name: 'EmergencyTeam_Assistant1FinishTime',
											//plugins: [new Ux.InputTextMask('99:99')],
											invalidText: 'Неправильный формат времени. Дата должна быть указана в формате ЧЧ:ММ',
										}
									]
								},
								{
									xtype: 'container',
									layout: 'hbox',
									margin: '2 0 2 0',
									items: [
										{
											xtype: 'swEmergencyFIOCombo',
											labelAlign: 'right',
											labelWidth: 110,
											store: medPersonallist,
											fieldLabel: 'Помощник 2',
											//allowBlank: false,
											name: 'EmergencyTeam_Assistant1',
											flex: 1
										},
										{
											xtype: 'datefield',
											width: 100,
											fieldLabel: 'С',
											labelAlign: 'right',
											labelWidth: 20,
											hideTrigger: true,
											format: 'H:i',
											name: 'EmergencyTeam_Assistant2StartTime',
											//plugins: [new Ux.InputTextMask('99:99')],
											invalidText: 'Неправильный формат времени. Дата должна быть указана в формате ЧЧ:ММ',
										},
										{
											xtype: 'datefield',
											margins: '0 0 0 10',
											width: 100,
											fieldLabel: 'По',
											labelAlign: 'right',
											labelWidth: 20,
											hideTrigger: true,
											format: 'H:i',
											name: 'EmergencyTeam_Assistant2FinishTime',
											//plugins: [new Ux.InputTextMask('99:99')],
											invalidText: 'Неправильный формат времени. Дата должна быть указана в формате ЧЧ:ММ',
										}
									]
								},
								{
									xtype: 'container',
									layout: 'hbox',
									margin: '2 0 2 0',
									items: [
										{
											xtype: 'swEmergencyFIOCombo',
											labelAlign: 'right',
											labelWidth: 110,
											store: medPersonallist,
											fieldLabel: 'Водитель',
											//allowBlank: false,
											name: 'EmergencyTeam_Driver',
											flex: 1
										},
										{
											xtype: 'datefield',
											width: 100,
											fieldLabel: 'С',
											labelAlign: 'right',
											labelWidth: 20,
											hideTrigger: true,
											format: 'H:i',
											name: 'EmergencyTeam_Driver1StartTime',
											//plugins: [new Ux.InputTextMask('99:99')],
											invalidText: 'Неправильный формат времени. Дата должна быть указана в формате ЧЧ:ММ',
										},
										{
											xtype: 'datefield',
											margins: '0 0 0 10',
											width: 100,
											fieldLabel: 'По',
											labelAlign: 'right',
											labelWidth: 20,
											hideTrigger: true,
											format: 'H:i',
											name: 'EmergencyTeam_Driver1FinishTime',
											//plugins: [new Ux.InputTextMask('99:99')],
											invalidText: 'Неправильный формат времени. Дата должна быть указана в формате ЧЧ:ММ',
										}
									]
								},
								/*
								{
									xtype: 'container',
									margin: '2 0 2 0',
									layout: 'hbox',
									hidden: (getGlobalOptions().region.nick.inlist('perm','krym','ekb'))?true:false,
									items: [
										{
											xtype: 'swEmergencyFIOCombo',											
											labelAlign: 'right',
											labelWidth: 110,
											store: medPersonallist,
											fieldLabel: 'Водитель',
											name: 'EmergencyTeam_Driver2',
											flex: 1
										},
										{
											xtype: 'datefield',
											width: 100,
											fieldLabel: 'С',
											labelAlign: 'right',
											labelWidth: 20,
											hideTrigger: true,
											format: 'H:i',
											name: 'EmergencyTeam_Driver2StartTime',
											plugins: [new Ux.InputTextMask('99:99')],
											invalidText: 'Неправильный формат времени. Дата должна быть указана в формате ЧЧ:ММ',
										},
										{
											xtype: 'datefield',
											margins: '0 0 0 10',
											width: 100,
											fieldLabel: 'По',
											labelAlign: 'right',
											labelWidth: 20,
											hideTrigger: true,
											format: 'H:i',
											name: 'EmergencyTeam_Driver2FinishTime',
											plugins: [new Ux.InputTextMask('99:99')],
											invalidText: 'Неправильный формат времени. Дата должна быть указана в формате ЧЧ:ММ',
										}										
									]
								},
								*/
								{
									xtype: 'container',
									margin: '2 0 2 0',
									layout: 'hbox',
									items: [
										{
											xtype: 'textfield',
											fieldLabel: 'Комментарий',						
											labelWidth: 110,
											flex: 1,
											labelAlign: 'right',
											name: 'EmergencyTeamDuty_ChangeComm'
										}
									]
								}
							]
						},
					]
				}
			]
		})
		CMPTabletPC.store.reload();
		if (conf.EmergencyTeam_id)
			me.loadForm(conf.EmergencyTeam_id)

		
		Ext.applyIf(me, {
			items: [
				emergencyTeamOperEnvEditFormPanel
            ]
			,
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
								{
									xtype: 'container',
									flex: 1,
									items: [								
										//leftButtons
										{
											xtype: 'button',
											refId: 'saveBtn',
											iconCls: 'save16',
											text: 'Сохранить',
											handler: function(){
												me.saveEmergencyTeam()
											}
										}
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
											refId: 'helpBtn',
											text: 'Помощь',
											iconCls   : 'help16',
											handler   : function()
											{
												ShowHelp(me.title);
											}
										},
										{
											xtype: 'button',
											refId: 'cancelBtn',
											iconCls: 'cancel16',
											text: 'Закрыть',
											margin: '0 5',
											handler: function(){
												this.up('window').close()
											}
										}
									]
								}
								
							]
						}
                    ]
                }
            ]
		})

		me.callParent(arguments)
	},
	
	parseFromDateToTime: function(textFormatDate){
		var dateF = Ext.Date.parse(textFormatDate, "Y-m-d H:i:s");
		if(dateF){return(Ext.Date.format(dateF, 'H:i'));}else{return false;}
	},

	loadForm: function(id){
		var cmp = this;

		Ext.Ajax.request({
			url: '/?c=EmergencyTeam4E&m=loadEmergencyTeam',
			params: {EmergencyTeam_id: id},
			callback: function(opt, success, response) {
				if (success){
					var res = Ext.JSON.decode(response.responseText)[0],
						frm = this.down('form').getForm(),
						startDate = Ext.Date.parse(res.EmergencyTeamDuty_DTStart, "Y-m-d H:i:s"),
						endDate = Ext.Date.parse(res.EmergencyTeamDuty_DTFinish, "Y-m-d H:i:s"),
						emergencyTeam_Head1StartTime = cmp.parseFromDateToTime(res.EmergencyTeam_Head1StartTime),
						emergencyTeam_Head1FinishTime = cmp.parseFromDateToTime(res.EmergencyTeam_Head1FinishTime),
						emergencyTeam_Head2StartTime = cmp.parseFromDateToTime(res.EmergencyTeam_Head2StartTime),
						emergencyTeam_Head2FinishTime = cmp.parseFromDateToTime(res.EmergencyTeam_Head2FinishTime),
						emergencyTeam_Assistant1StartTime = cmp.parseFromDateToTime(res.EmergencyTeam_Assistant1StartTime),
						emergencyTeam_Assistant1FinishTime = cmp.parseFromDateToTime(res.EmergencyTeam_Assistant1FinishTime),
						emergencyTeam_Assistant2StartTime = cmp.parseFromDateToTime(res.EmergencyTeam_Assistant2StartTime),
						emergencyTeam_Assistant2FinishTime = cmp.parseFromDateToTime(res.EmergencyTeam_Assistant2FinishTime),
						emergencyTeam_Driver1StartTime = cmp.parseFromDateToTime(res.EmergencyTeam_Driver1StartTime),
						emergencyTeam_Driver1FinishTime = cmp.parseFromDateToTime(res.EmergencyTeam_Driver1FinishTime),
						emergencyTeam_Driver2StartTime = cmp.parseFromDateToTime(res.EmergencyTeam_Driver2StartTime),
						emergencyTeam_Driver2FinishTime = cmp.parseFromDateToTime(res.EmergencyTeam_Driver2FinishTime);
						
					if (startDate) res.EmergencyTeamDuty_DTStart = Ext.Date.format(startDate, 'H:i');
					if (endDate) res.EmergencyTeamDuty_DTFinish = Ext.Date.format(endDate, 'H:i');
					
					res.EmergencyTeam_Head1StartTime = emergencyTeam_Head1StartTime?emergencyTeam_Head1StartTime:res.EmergencyTeamDuty_DTStart;
					res.EmergencyTeam_Head1FinishTime = emergencyTeam_Head1FinishTime?emergencyTeam_Head1FinishTime:res.EmergencyTeamDuty_DTFinish;
					res.EmergencyTeam_Head2StartTime = emergencyTeam_Head2StartTime?emergencyTeam_Head2StartTime:res.EmergencyTeamDuty_DTStart;
					res.EmergencyTeam_Head2FinishTime = emergencyTeam_Head2FinishTime?emergencyTeam_Head2FinishTime:res.EmergencyTeamDuty_DTFinish;					
					res.EmergencyTeam_Assistant1StartTime = emergencyTeam_Assistant1StartTime?emergencyTeam_Assistant1StartTime:res.EmergencyTeamDuty_DTStart;
					res.EmergencyTeam_Assistant1FinishTime = emergencyTeam_Assistant1FinishTime?emergencyTeam_Assistant1FinishTime:res.EmergencyTeamDuty_DTFinish;
					res.EmergencyTeam_Assistant2StartTime = emergencyTeam_Assistant2StartTime?emergencyTeam_Assistant2StartTime:res.EmergencyTeamDuty_DTStart;
					res.EmergencyTeam_Assistant2FinishTime = emergencyTeam_Assistant2FinishTime?emergencyTeam_Assistant2FinishTime:res.EmergencyTeamDuty_DTFinish;					
					res.EmergencyTeam_Driver1StartTime = emergencyTeam_Driver1StartTime?emergencyTeam_Driver1StartTime:res.EmergencyTeamDuty_DTStart;
					res.EmergencyTeam_Driver1FinishTime = emergencyTeam_Driver1FinishTime?emergencyTeam_Driver1FinishTime:res.EmergencyTeamDuty_DTFinish;					
					res.EmergencyTeam_Driver2StartTime = emergencyTeam_Driver2StartTime?emergencyTeam_Driver2StartTime:res.EmergencyTeamDuty_DTStart;
					res.EmergencyTeam_Driver2FinishTime = emergencyTeam_Driver2FinishTime?emergencyTeam_Driver2FinishTime:res.EmergencyTeamDuty_DTFinish;
					
					res.EmergencyTeam_CarNum = res.AccountingData_RegNumber;
					res.EmergencyTeam_CarBrand = res.MedProductClass_Model;

					frm.setValues(res);	
					if(res.EmergencyTeam_Driver2)
					{
						//this.showMeSecondDriver(true);
					}
					
					//
				}
			}.bind(this) 
		})
	},
		
	saveEmergencyTeam: function(){
		
		if (!this.down('form').getForm().isValid()){
			Ext.Msg.alert('Проверка данных формы', 'Не все поля формы заполнены.<br>Незаполненные поля выделены особо.');
		}
		else{
			var conf = {},
				form = this.down('form'),
				armButton = Ext.ComponentQuery.query('armButton[refId=buttonChooseArm]'),
				dtStartFieldVal = form.getForm().findField('EmergencyTeamDuty_DTStart').getValue(),
				dtFinishFieldVal = form.getForm().findField('EmergencyTeamDuty_DTFinish').getValue();
			
			conf = this.down('form').getForm().getValues();
			conf.ARMType = sw.Promed.MedStaffFactByUser.last.ARMType;
			conf.EmergencyTeam_id = form.getForm().findField('EmergencyTeam_id').getValue();
			conf.EmergencyTeamDuty_DTStart = Ext.Date.format(dtStartFieldVal, 'Y-m-d H:i:s');
			conf.EmergencyTeamDuty_DTFinish = Ext.Date.format(dtFinishFieldVal, 'Y-m-d H:i:s');
			conf.EmergencyTeam_DutyTime = Math.abs(Math.round(dtFinishFieldVal - dtStartFieldVal)/3600000);
			conf.accessType = '';
			

			Ext.Ajax.request({
				url: '/?c=EmergencyTeam4E&m=saveEmergencyTeam',
				params: conf,
				callback: function(opt, success, response) {
					if (success){
						var res = Ext.JSON.decode(response.responseText),						
							wdata = [{
							EmergencyTeam_id: res.EmergencyTeam_id,
							GeoserviceTransport_id: conf.EmergencyTeam_GpsNum
						}];
						//связка бригады и виалона
						Ext.Ajax.request({
							url: '/?c=Wialon&m=saveEmergencyTeamRel',
							params: {data: Ext.encode(wdata)}
						})
						
						form.getForm().findField('EmergencyTeam_id').setValue(res.EmergencyTeam_id);
						var grid = Ext.ComponentQuery.query('swEmergencyTeamOperEnv grid[refId=emrgTeamOperEnvGrid]')[0];
						if(res.EmergencyTeam_id && grid)
						{
							 grid.store.reload({
								 callback: function(records, operation, success) {
									 var rec = grid.store.findRecord('EmergencyTeam_id', res.EmergencyTeam_id);
									 if (rec)
									 {
										 grid.getView().select(rec);
										 form.up('window').close();
									 }									
								 }
							 })
							
						}
					}
				}
			});
			
		}
	}
})

