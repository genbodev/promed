/*
	Формирование карточек бригад
*/

Ext.define('common.DispatcherStationWP.tools.swEditEmergencyTeamTemplate', {
	alias: 'widget.swEditEmergencyTeamTemplate',
	extend: 'sw.standartToolsWindow',
	width: 700,
	height: 470,
	
	saveEmergencyTeamTemplate: function(callback){
		
		if (!this.down('form').getForm().isValid()){
			Ext.Msg.alert('Проверка данных формы', 'Не все поля формы заполнены.<br>Незаполненные поля выделены особо.');
		}
		else{
			var conf = {},
				win = this,
				frm = this.down('form');		
			
			conf = frm.getForm().getValues();			

			conf.EmergencyTeam_isTemplate = 2;			
			conf.EmergencyTeamDuty_DTStart = Ext.Date.format(frm.getForm().findField('EmergencyTeamDuty_DTStart').getValue(), 'm.d.Y H:i');
			conf.EmergencyTeamDuty_DTFinish = Ext.Date.format(frm.getForm().findField('EmergencyTeamDuty_DTFinish').getValue(), 'm.d.Y H:i');
			
			Ext.Ajax.request({
				url: '/?c=EmergencyTeam4E&m=saveEmergencyTeam',
				params: conf,
				callback: function(opt, success, response) {
					if (success){
						var res = Ext.JSON.decode(response.responseText);
						if (!res || !res.EmergencyTeam_id || res.Error_Msg)  {
							Ext.Msg.alert('Ошибка', res.Error_Msg || 'При сохранении шаблона произошла ошибка');
						}
						//неуникальное имя шаблона
						if(res && res.Error_Code) frm.getForm().findField('EmergencyTeam_TemplateName').markInvalid(res.Error_Msg);
						
						if(res && res.EmergencyTeam_id)	win.fireEvent('aftersave', win, res);
					}
				}
			});
		}
		
	},
	
	showMedPersonsWindow: function(val){
		var me = this;

		var win = Ext.create('sw.standartToolsWindow', {
			title: 'Выбор сотрудника',
			height: 300,
			width: 800,
			layout: 'fit',
			modal: true,
			autoShow: true,
			configComponents: {
				center: {
					xtype: 'grid',
					border: false,
					store: me.medPersonallist,
					columns: [
						{ dataIndex: 'MedPersonal_id', text: 'id', hidden: true  },
						{ dataIndex: 'MedPersonal_Fio', text: 'ФИО', flex: 1, hideable: false  },
						{ dataIndex: 'LpuBuilding_Name', text: 'Структурное подразделение', flex: 1, hideable: false },
						{ dataIndex: 'PostMed_Name', text: 'Должность', flex: 1, hideable: false  },
						{ dataIndex: 'MedStaffFact_Stavka', text: 'Ставка', width: 80, hideable: false  },
						{ dataIndex: 'WorkData_begDate', text: 'Дата начала работы', width: 120, hideable: false },
						{ dataIndex: 'WorkData_endDate', text: 'Дата окночания работы', width: 130, hideable: false  }
					],
					listeners: {
						celldblclick: function( cmp, td, cellIndex, record, tr, rowIndex, e, eOpts ){
							var win =  this.up('window');
							win.fireEvent('selectMedPerson', record);
							win.close();
						},
						select: function(){
							var win =  this.up('window');
							win.down('button[refId=okButton]').enable();
						}
					}
				},
				leftButtons: {
					xtype: 'button',
					text: 'Выбрать',
					iconCls: 'ok16',
					refId: 'okButton',
					disabled: true,
					handler: function(){
						var win =  this.up('window');
						win.fireEvent('selectMedPerson', win.down('grid').getSelectionModel().getSelection()[0] );
						win.close();
					}
				}
			},
			listeners: {
				show: function(){
					var gr = this.down('grid');

					if(val){
						gr.getSelectionModel().select( me.medPersonallist.findRecord('MedPersonal_id', val) );
					}

					this.addEvents({
						selectMedPerson: true
					});
				}
			}
		});

		return win;
	},
	
	loadEmergencyTeamTemplate: function(params){
		Ext.Ajax.request({
			url: '/?c=EmergencyTeam4E&m=loadEmergencyTeam',
			params: {
				EmergencyTeam_id: params.EmergencyTeam_id
			},
			callback: function(opt, success, response) {
				if (success){
					var res = Ext.JSON.decode(response.responseText)[0],
						frm = this.down('form').getForm(),
						startDate = Ext.Date.parse(res.EmergencyTeamDuty_DTStart, "Y-m-d H:i:s"),
						endDate = Ext.Date.parse(res.EmergencyTeamDuty_DTFinish, "Y-m-d H:i:s");

					res.EmergencyTeamDuty_DTStart = startDate;
					res.EmergencyTeamDuty_DTFinish = endDate;				

					frm.setValues(res);
					frm.findField('EmergencyTeamDuty_DTStart').setValue(startDate);
					frm.findField('EmergencyTeamDuty_DTFinish').setValue(endDate);
					
					frm.isValid();
				}
			}.bind(this)
		})
	},
	
	lockForm: function(){
		var frm = this.down('form').getForm();
		frm.applyToFields({
			readOnly: true,
			hideTrigger: true
		});
		this.down('button[refId=saveButton]').hide();
	},
	
	
	initComponent: function() {
		
		this.addEvents('aftersave');
		
		var win = this,
			conf = win.initialConfig,
			curArm = sw.Promed.MedStaffFactByUser.current.ARMType || sw.Promed.MedStaffFactByUser.last.ARMType,
			title = 'Шаблон наряда';

		win.isNmpArm = curArm.inlist(['dispnmp','dispcallnmp', 'dispdirnmp']);

		switch(conf.action){
			case 'add' :  { title +=': Добавление'; break; }
			case 'edit' : {
				title +=': Редактирование'; 
				break;
			}
			case 'view' : {
				title +=': Просмотр';
				break;
			}
		}
		
		win.on('show', function(cmp){
			switch(conf.action){
				case 'add' : {
					
					break;
				}
				case 'edit' : {
					win.loadEmergencyTeamTemplate(conf.config);
					break;
				}
				case 'view' : {
					win.loadEmergencyTeamTemplate(conf.config);
					win.lockForm();
					break;
				}
			};
			
		})
		
		win.title = title;
		
		win.medPersonallist = Ext.create('Ext.data.Store', {
			autoLoad: true,
			fields: [
				{name: 'MedPersonal_id', type:'int'},
				{name: 'MedPersonal_Code', type:'int'},
				{name: 'MedPersonal_Fio', type:'string'},
				{name: 'PostMed_Name', type:'string'},
				{name: 'MedStaffFact_Stavka', type:'string'},
				{name: 'MedStaffFact_id', type:'int'},
				{name: 'WorkData_begDate', type:'string'},
				{name: 'WorkData_endDate', type:'string'},
				{name: 'LpuBuilding_Name', type:'string'}
			],
			proxy: {
				limitParam: undefined,
				startParam: undefined,
				paramName: undefined,
				pageParam: undefined,
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
				},
				extraParams: {
					LpuBuildingType_id: win.isNmpArm ? 28 : 27 //подразделение с типом Скорая медицинская помощь или НМП
				}
			}
		});


		win.topContent = Ext.create('Ext.container.Container', {
			layout: {
				type: 'vbox',
				align: 'stretch',
				padding: '0 10 0 10'
			},
			defaults: {
				labelAlign: 'right',
				labelWidth: 190
			},
			items:
				[
					{
						xtype: 'hidden',
						value: '',
						name: 'EmergencyTeam_id'
					},
					{
						xtype: 'hidden',
						value: '',
						name: 'EmergencyTeamDuty_id'
					},
					{
						xtype: 'transFieldDelbut',
						fieldLabel: 'Имя шаблона',	
						translate: false,
						allowBlank: false,
						name: 'EmergencyTeam_TemplateName'
					},
					{
						xtype: 'timefield',
						name: 'EmergencyTeamDuty_DTStart',						
						triggerCls: 'x-form-clock-trigger',
						format: 'H:i',
						maxWidth: 300,
						align: 'left',
						fieldLabel: 'Время начала',
						plugins: [new Ux.InputTextMask('99:99')],
						invalidText: 'Неправильный формат времени',
						onTriggerClick: function(e) {
							e.stopEvent();
							this.setValue(Ext.Date.format(new Date(), 'H:i'));							
						},
						allowBlank: false,
						value: new Date()
					},
					{
						xtype: 'timefield',
						name: 'EmergencyTeamDuty_DTFinish',						
						triggerCls: 'x-form-clock-trigger',
						format: 'H:i',
						maxWidth: 300,
						fieldLabel: 'Время окончания',
						plugins: [new Ux.InputTextMask('99:99')],
						invalidText: 'Неправильный формат времени',
						onTriggerClick: function(e) {
							e.stopEvent();
							this.setValue(Ext.Date.format(new Date(), 'H:i'));							
						},
						allowBlank: false,
						value: new Date()
					},
					/*{
						xtype: 'smpUnits',
						name: 'LpuBuilding_id',
						fieldLabel: 'Подразделение СМП',
						allowBlank: false,
						readOnly: false
					},*/
					{
						xtype: 'SmpUnitsFromOptions',
						name: 'LpuBuilding_id',
						fieldLabel: win.isNmpArm ? 'Подразделение НМП' : 'Подразделение СМП',
						allowBlank: true,
						defaultValueCurrentLpuBuilding: (conf.action=='add'),
						editable: false,
						loadSelectSmp: true,
						listeners: {
							select: function(){
								/*
								#116418 изменены условия вывода автомобилей
								var combo = this,
									frm = win.down('form').getForm(),
									autoField = frm.findField('MedProductCard_id');
								
								autoField.getStore().load({params: {filterLpuBuilding: combo.getValue()}})
								*/
							}
						}
					},
                  	{
                        xtype: 'transFieldDelbut',
                        fieldLabel: 'Номер бригады',

                        translate: false,
                        allowBlank: false,
                        name: 'EmergencyTeam_Num',
                        maskRe: (getGlobalOptions().region.nick != 'ufa' && getGlobalOptions().region.nick != 'krym')?/[0-9:/]/:false
                    },
                    {
                        xtype: 'container',
                        layout: 'hbox',
                        margin: '0 0 5 0',
                        items: [
                            {
                                xtype: 'EmergencyCars',
                                //allowBlank: false,
								allowBlank: win.isNmpArm,
                                labelWidth: 190,
                                width: 430,
                                labelAlign: 'right',
                                name: 'MedProductCard_id'
                            },
                            {
                                xtype: 'checkbox',
                                boxLabel: 'Показать все автомобили МО',
                                name: 'viewAllMO',
                                labelWidth: 190,
                                labelSeparator: ':',
                                labelAlign: 'right',
                                margin: '0 5 0 20',
                                listeners: {
                                    change: function (cmp, val) {
                                        var frm = win.down('form').getForm(),
                                            autoField = frm.findField('MedProductCard_id');

                                        if(val) autoField.getStore().load({params: {viewAllMO: +val}});
                                        else autoField.getStore().load();
                                    },
                                    afterrender: function (cmp, e) {
                                        cmp.setValue(getRegionNick().inlist(['krym']));
                                    }
                                }
                            }
                        ]
                    },

					{
						xtype: 'swEmergencyTeamSpecCombo',
						fieldLabel: 'Профиль бригады',	
						allowBlank: true,
						name: 'EmergencyTeamSpec_id',
						defaultListConfig: {
							resizable: true,
							maxHeight: 300
						}
					},
					{
						fieldLabel: 'Старший бригады',
						name: 'EmergencyTeam_HeadShift',
						xtype: 'swEmergencyFIOCombo',
						store: win.medPersonallist,
						typeAhead: true,
						editable: true,
						autoFilter: true,
						forceSelection: 'false',
						allowBlank: true,
						listeners: {
							select: function(cmb,recs){
								var rec = recs.length > 0 ? recs[0] : null;
								if(rec){
									var wpfield = win.down('form').getForm().findField('EmergencyTeam_HeadShiftWorkPlace');
									wpfield.setValue(rec.get('MedStaffFact_id'));
								}
							}
						},
						onTrigger2Click: function(e) {
							var trigger = this,
								w = win.showMedPersonsWindow(trigger.getValue()),
								wpfield = win.down('form').getForm().findField('EmergencyTeam_HeadShiftWorkPlace');

							w.on('selectMedPerson', function(rec){
								trigger.setValue(rec.get('MedPersonal_id'));
								wpfield.setValue(rec.get('MedStaffFact_id'));
							});
						}
					},
					{
						name: 'EmergencyTeam_HeadShiftWorkPlace',
						xtype: 'hidden'
					},
					{
						fieldLabel: 'Помощник 1',
						name: 'EmergencyTeam_HeadShift2',
						xtype: 'swEmergencyFIOCombo',
						store: win.medPersonallist,
						typeAhead: true,
						editable: true,
						autoFilter: true,
						forceSelection: 'false',
						triggerClear: true,
						listeners: {
							select: function(cmb,recs){
								var rec = recs.length > 0 ? recs[0] : null;
								if(rec){
									var wpfield = win.down('form').getForm().findField('EmergencyTeam_HeadShift2WorkPlace');
									wpfield.setValue(rec.get('MedStaffFact_id'));
								}
							}
						},
						onTrigger2Click: function(e) {
							var trigger = this,
								w = win.showMedPersonsWindow(trigger.getValue()),
								wpfield = win.down('form').getForm().findField('EmergencyTeam_HeadShift2WorkPlace');

							w.on('selectMedPerson', function(rec){
								trigger.setValue(rec.get('MedPersonal_id'));
								wpfield.setValue(rec.get('MedStaffFact_id'));
							});
						}
					},
					{
						name: 'EmergencyTeam_HeadShift2WorkPlace',
						xtype: 'hidden'
					},
					{
						fieldLabel: 'Помощник 2',
						name: 'EmergencyTeam_Assistant1',
						xtype: 'swEmergencyFIOCombo',
						store: win.medPersonallist,
						typeAhead: true,
						editable: true,
						autoFilter: true,
						forceSelection: 'false',
						triggerClear: true,
						listeners: {
							select: function(cmb,recs){
								var rec = recs.length > 0 ? recs[0] : null;
								if(rec){
									var wpfield = win.down('form').getForm().findField('EmergencyTeam_Assistant1WorkPlace');
									wpfield.setValue(rec.get('MedStaffFact_id'));
								}
							}
						},
						onTrigger2Click: function(e) {
							var trigger = this,
								w = win.showMedPersonsWindow(trigger.getValue()),
								wpfield = win.down('form').getForm().findField('EmergencyTeam_Assistant1WorkPlace');

							w.on('selectMedPerson', function(rec){
								trigger.setValue(rec.get('MedPersonal_id'));
								wpfield.setValue(rec.get('MedStaffFact_id'));
							});
						}
					},
					{
						name: 'EmergencyTeam_Assistant1WorkPlace',
						xtype: 'hidden'
					},
					{
						fieldLabel: 'Водитель',
						name: 'EmergencyTeam_Driver',
						xtype: 'swEmergencyFIOCombo',
						store: win.medPersonallist,
						typeAhead: true,
						editable: true,
						autoFilter: true,
						forceSelection: 'false',
						triggerClear: true,
						allowBlank: (getRegionNick().inlist(['perm', 'ekb', 'khak'])),
						listeners: {
							select: function(cmb,recs){
								var rec = recs.length > 0 ? recs[0] : null;
								if(rec){
									var wpfield = win.down('form').getForm().findField('EmergencyTeam_DriverWorkPlace');
									wpfield.setValue(rec.get('MedStaffFact_id'));
								}
							}
						},
						onTrigger2Click: function(e) {
							var trigger = this,
								w = win.showMedPersonsWindow(trigger.getValue()),
								wpfield = win.down('form').getForm().findField('EmergencyTeam_DriverWorkPlace');


							w.on('selectMedPerson', function(rec){
								trigger.setValue(rec.get('MedPersonal_id'));
								wpfield.setValue(rec.get('MedStaffFact_id'));
							});
						}
					},
					{
						name: 'EmergencyTeam_DriverWorkPlace',
						xtype: 'hidden'
					},
					{
						xtype: 'CMPTabletPC',
						name: 'CMPTabletPC_id',
						fieldLabel: 'Планшетный компьютер'
					},
					{
						xtype: 'textfield',
						fieldLabel: 'Телефон',
						enableKeyEvents : true,
						plugins: [new Ux.InputTextMask('+7(999)-999-99-99', true)],
						labelAlign: 'right',
						name: 'EmergencyTeam_Phone'
					}
					/*
					{
						xtype: 'transFieldDelbut',
						fieldLabel: 'Марка машины',
						labelAlign: 'right',
						//style:{textTransform: 'uppercase'},
						//plugins: [new Ux.Translit(true, false)],
						labelWidth: 190,
						translate: false,
						name: 'EmergencyTeam_CarBrand',
						listeners: {
							keypress: function(c, e, o){
								if ( (e.getKey()==13) )
								{								
									c.nextNode().focus();
									return;
								}
							}
						}
					},
					
					{
						xtype: (getGlobalOptions().region.number == 2) ? 'swEmergencyTeamTNCCombo' : 'swEmergencyTeamWialonCombo',
						labelAlign: 'right',
						fieldLabel: 'GPS/ГЛОНАСС',
						allowBlank: true,
						labelWidth: 190,
						//hidden: true,
						name: 'GeoserviceTransport_id',
						listeners: {
							keypress: function(c, e, o){
								if ( (e.getKey()==13) )
								{
									win.down('form').getForm().findField('DutyTimeStart').focus();
									return;
								}
							}
						}
					}*/					
				]
		});
		
		win.centerContent = Ext.create('Ext.container.Container', {
			layout: {
				type: 'vbox',
				align: 'stretch',
				padding: 10
			},
			items: [win.topContent]
		})
		
		win.saveBtn = Ext.create('Ext.button.Button', {
			text: 'Сохранить',
			iconCls: 'ok16',
			refId: 'saveButton',
			disabled: false,
			handler: function(){
				this.saveEmergencyTeamTemplate();
				
				
			}.bind(this)
		});
		
		
		//отправляем сборку
		win.configComponents = {
			//top: win.topTbar,
			center: win.centerContent,
			leftButtons: win.saveBtn
		};
		
		win.callParent(arguments);
	}
})