/*
	Наряд / изменение состава наряда
*/

Ext.define('sw.tools.subtools.swEmergencyTeamPopupEditWindow', {
    extend: 'Ext.window.Window',
	modal:true,
	width: 800,
    height: 700,
    title: 'Наряд',
	resizable: false,

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
						{ dataIndex: 'MedStaffFact_id', text: 'id места работы', hidden: true  },
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
					me.medPersonallist.clearFilter();

					if(val && me.medPersonallist.findRecord('MedStaffFact_id', val)){
						gr.getSelectionModel().select( me.medPersonallist.findRecord('MedStaffFact_id', val) );
					}

					this.addEvents({
						selectMedPerson: true
					});
				}
			}
		});

		return win;
	},

	getBaseForm: function(mode){
		var me = this;
		//me.title += this.sel;
		me.medPersonallist = Ext.create('Ext.data.Store', {
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
					LpuBuildingType_id: me.isNmpArm ? 28 : 27 //подразделение с типом Скорая медицинская помощь или НМП
				}
			}
		});

		return Ext.create('sw.BaseForm',{
			xtype: 'BaseForm',
			id: this.id+'_BaseForm',
			items: [
			{
				xtype: 'hidden', name: 'EmergencyTeam_id'
			},
			{
				xtype: 'hidden', name: 'EmergencyTeam_CarNum'
			},
			{
				xtype: 'hidden', name: 'EmergencyTeam_CarBrand'
			},
			{
				xtype: 'hidden', name: 'EmergencyTeamDuty_id'
			},
            {
				xtype: 'hidden', name: 'checkCarByDutyDate', value: true
			},
            {
				xtype: 'hidden', name: 'EmergencyTeamDuty_IsCancelledStart'
			},
            {
				xtype: 'hidden', name: 'EmergencyTeamDuty_IsCancelledClose'
			},
			{
				xtype: 'container',
				padding: '10 0 0 0',
				width: '100%',
				bodyPadding: 10,
				layout: 'column',
				defaults: {
					labelAlign: 'left',
					labelWidth: 250
				},
				items: [{
					border: false,
					padding: '10 10 10 10',
					xtype: 'container',
                    layout: 'column',
					items:[
						{
							fieldLabel: 'Применить изменения с',
							xtype: 'datefield',
							name: 'ApplyChangesFrom',
							width: 260,
							startDay: 1,
							labelWidth: 150,
							format: 'd.m.Y',
							labelAlign: 'left',
							invalidText: 'Неправильный формат даты',
							//hidden: ( mode == 'edit' )? true: false,
							allowBlank: ( mode == 'edit' )? true: false,
							plugins: [new Ux.InputTextMask('99:99:9999')],
							value: new Date()
						},
						{
							xtype: 'timefield',
							name: 'ApplyChangesFromTime',
							width: 80,
							triggerCls: 'x-form-clock-trigger',
							format: 'H:i',
							plugins: [new Ux.InputTextMask('99:99')],
							labelAlign: 'right',
							invalidText: 'Неправильный формат времени',
							onTriggerClick: function(e) {
								e.stopEvent();

								var dt = new Date(),
									dtfield = me.down('form').getForm().findField('ApplyChangesFrom');

								this.setValue(Ext.Date.format(dt, 'H:i'));
								dtfield.setValue(Ext.Date.format(dt, 'd.m.Y'));
							},
							//hidden: ( mode == 'edit' )? true: false,
							allowBlank: ( mode == 'edit' )? true: false,
							value: new Date()
						},
						{
							height: 10, width:700, border: 0, hidden: ( mode!='split' )? true: false
						},
						{
							fieldLabel: 'Дата и время с',
							xtype: 'datefield',
							name: 'EmergencyTeamDuty_DTStart',
							width: 260,
							labelWidth: 150,
							format: 'd.m.Y',
							labelAlign: 'left',
							startDay: 1,
							invalidText: 'Неправильный формат даты',
							plugins: [new Ux.InputTextMask('99.99.9999')],
							//hidden: ( mode=='edit' )? false: true
						},
						{
							xtype: 'datefield',
							name: 'EmergencyTeamDuty_DStart',
							width: 80,
							triggerCls: 'x-form-clock-trigger',
							format: 'H:i',
							plugins: [new Ux.InputTextMask('99:99')],
							labelAlign: 'right',
							invalidText: 'Неправильный формат времени',
							//hidden: ( mode=='edit' )? false: true,
							onTriggerClick: function(e) {							
								var dt = new Date(),
									dtfield = me.down('form').getForm().findField('EmergencyTeamDuty_DTStart');

								this.setValue(Ext.Date.format(dt, 'H:i'));
								dtfield.setValue(Ext.Date.format(dt, 'd.m.Y'));
							}
						},
						{
							height: 10, width:700, border: 0, hidden: ( mode!='split' )? false: true
						},
						{
							fieldLabel: 'Дата и время по',
							xtype: 'datefield',
							name: 'EmergencyTeamDuty_DTFinish',
							width: 260,
							labelWidth: 150,
							format: 'd.m.Y',
							startDay: 1,
							labelAlign: 'left',
							invalidText: 'Неправильный формат даты',
							//hidden: ( mode=='edit' )? false: true,
							plugins: [new Ux.InputTextMask('99.99.9999')]
						},
						{
							xtype: 'datefield',
							name: 'EmergencyTeamDuty_DFinish',
							width: 80,
							triggerCls: 'x-form-clock-trigger',
							format: 'H:i',
							plugins: [new Ux.InputTextMask('99:99')],
							labelAlign: 'right',
							invalidText: 'Неправильный формат времени',
							//hidden: ( mode=='edit' )? false: true,
							onTriggerClick: function(e) {
								var dt = new Date(),
									dtfield = me.down('form').getForm().findField('EmergencyTeamDuty_DTFinish');

								this.setValue(Ext.Date.format(dt, 'H:i'));
								dtfield.setValue(Ext.Date.format(dt, 'd.m.Y'));
							}
						},
						{
							height: 10, width:700, border: 0, hidden: ( mode!='split' )? false: true
						},
						{
							xtype: 'smpUnits',
							name: 'LpuBuilding_id',
							fieldLabel: me.isNmpArm ? 'Подразделение НМП' : 'Подразделение СМП',
							allowBlank: false,
							//readOnly: true,
							width: 750,
							labelWidth: 150,
							loadSelectSmp: true
						},
						{
							height: 10, width:700, border: 0
						},
						{
							xtype: 'textfield',
							fieldLabel: '№ бригады',
							labelWidth: 150,
							width: 240,
							name: 'EmergencyTeam_Num',
							allowBlank: false,
							listeners: {
										'change': function (cmp, value) {
											var form = me.down('form').getForm(),
													value = value,
													medProductCard = form.findField('MedProductCard_id');
											if ((value) && (value > 0)) {
												medProductCard.setValue(null);
												var params = {};
												var formVals = form.getValues();

												params.dStart = Ext.Date.format(me.down('form').getForm().findField('EmergencyTeamDuty_DTStart').getValue(), 'Y-m-d');
												params.dFinish = Ext.Date.format(me.down('form').getForm().findField('EmergencyTeamDuty_DTFinish').getValue(), 'Y-m-d');

												params.dtStart = params.dStart + ' ' + formVals.EmergencyTeamDuty_DStart;
												params.dtFinish = params.dFinish + ' ' + formVals.EmergencyTeamDuty_DFinish;

												params.viewAllMO = formVals.viewAllMO == 'on' ? 1 : null;

												medProductCard.getStore().load({
													params: {
														params: params
													},
													callback: function (r, o, s) {
														if (r.length > 0) {
															for (var i = 0; i < r.length; i++) {
																if (r[i].data.MedProductCard_BoardNumber == value) {
																	medProductCard.setValue(r[i].data.MedProductCard_id);
																	break;
																}
															}
														}
													}
												});
											}
										}
							}
							//readOnly: ( mode == 'edit' )? false: true,
						},
						{
							height: 10, width:700, border: 0
						},
                        {
                            xtype: 'container',
                            layout: 'hbox',
                            width:750,
                            // margin: '0 0 5 0',
                            items: [
                                {
                                    xtype: 'EmergencyCars',
                                    labelWidth: 150,
                                    width: 550,
                                    autoFilter: false,
                                    allowBlank: me.isNmpArm,
									isNmpArm: me.isNmpArm,
                                    name: 'MedProductCard_id',
                                    listeners: {
                                        expand: function(cmp){
                                            var form = me.down('form').getForm();
                                            var params = {};
                                            var formVals = form.getValues();

                                            params.dStart = Ext.Date.format(me.down('form').getForm().findField('EmergencyTeamDuty_DTStart').getValue(), 'Y-m-d');
                                            params.dFinish = Ext.Date.format(me.down('form').getForm().findField('EmergencyTeamDuty_DTFinish').getValue(), 'Y-m-d');

                                            params.dtStart = params.dStart + ' '+ formVals.EmergencyTeamDuty_DStart;
                                            params.dtFinish = params.dFinish + ' '+ formVals.EmergencyTeamDuty_DFinish;

                                            params.viewAllMO = formVals.viewAllMO == 'on'? 1 : null;

                                            cmp.store.load({params: params});

                                            //убрал прокси тк почему-то настройки прокси копируются на другие элементы с данным стором
                                            //this.store.proxy.extraParams = params;
                                            //this.getStore().reload();

                                        },
                                        select: function(){
                                            me.getDefaultPhoneNumber();
                                        }
                                    }
                                },
                                {
                                    xtype: 'checkbox',
                                    boxLabel: 'Показать все автомобили МО',
                                    name: 'viewAllMO',
                                    labelWidth: 200,
                                    labelSeparator: ':',
                                    labelAlign: 'right',
                                    margin: '0 5 0 20',
                                    listeners: {
                                        change: function (cmp, val) {
                                           /* var frm = me.down('form').getForm(),
                                                autoField = frm.findField('MedProductCard_id');

                                            if(val) autoField.getStore().load({params: {viewAllMO: +val}});
                                            else autoField.getStore().load();
                                            */
                                        },
                                        afterrender: function (cmp, e) {
                                            cmp.setValue(getRegionNick().inlist(['krym']));
                                        }
                                    }
                                }
                            ]
                        },
						{
							height: 10, width:700, border: 0
						},
						{
							xtype: 'swEmergencyTeamWialonCombo',
							fieldLabel: 'GPS/ГЛОНАСС',
							allowBlank: true,
							labelWidth: 150,
							labelAlign: 'left',
							width: 550,
							hidden: !(me.isNmpArm),
							name: 'GeoserviceTransport_id'
						},
						{
							height: 10, width:700, border: 0
						},
						{
							xtype: 'swEmergencyTeamSpecCombo',
							labelAlign: 'left',
							labelWidth: 150,
							width: 550,
							allowBlank: false,
							name: 'EmergencyTeamSpec_id',
							fieldLabel: 'Профиль бригады',
						},
						{
							height: 10, width:700, border: 0
						},
						{
							fieldLabel: 'Старший бригады',
							name: 'EmergencyTeam_HeadShift',
							xtype: 'swEmergencyFIOCombo',
							store: me.medPersonallist,
							width: 750,
							typeAhead: true,
							editable: true,
							autoFilter: true,
							forceSelection: 'false',
							triggerClear: true,
							labelWidth: 150,
							allowBlank: false,
							listeners: {
								select: function(cmb,recs){
									var rec = recs.length > 0 ? recs[0] : null;
									if(rec){
										var wpfield = me.down('form').getForm().findField('EmergencyTeam_HeadShiftWorkPlace');
										wpfield.setValue(rec.get('MedStaffFact_id'));
									}
									me.getDefaultPhoneNumber();
								}
							},
							onTrigger2Click: function(e) {
								var trigger = this,
									wpfield = me.down('form').getForm().findField('EmergencyTeam_HeadShiftWorkPlace'),
									w = me.showMedPersonsWindow(wpfield.getValue());


								w.on('selectMedPerson', function(rec){
									trigger.setValue(rec.get('MedPersonal_id'));
									wpfield.setValue(rec.get('MedStaffFact_id'));
									me.getDefaultPhoneNumber();
								});
							}
						},
						{
							name: 'EmergencyTeam_HeadShiftWorkPlace',
							xtype: 'hidden'
						},
						{
							height: 10, width:700, border: 0
						},
						{
							fieldLabel: 'Помощник 1',
							name: 'EmergencyTeam_HeadShift2',
							xtype: 'swEmergencyFIOCombo',
							store: me.medPersonallist,
							width: 750,
							typeAhead: true,
							editable: true,
							autoFilter: true,
							forceSelection: 'false',
							triggerClear: true,
							labelWidth: 150,
							listeners: {
								select: function(cmb,recs){
									var rec = recs.length > 0 ? recs[0] : null;
									if(rec){
										var wpfield = me.down('form').getForm().findField('EmergencyTeam_HeadShift2WorkPlace');
										wpfield.setValue(rec.get('MedStaffFact_id'));
									}
								}
							},
							onTrigger2Click: function(e) {
								var trigger = this,
									wpfield = me.down('form').getForm().findField('EmergencyTeam_HeadShift2WorkPlace'),
									w = me.showMedPersonsWindow(wpfield.getValue());


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
								height: 10, width:700, border: 0
						},
						{
							fieldLabel: 'Помощник 2',
							name: 'EmergencyTeam_Assistant1',
							xtype: 'swEmergencyFIOCombo',
							store: me.medPersonallist,
							width: 750,
							typeAhead: true,
							editable: true,
							autoFilter: true,
							forceSelection: 'false',
							triggerClear: true,
							labelWidth: 150,
							listeners: {
								select: function(cmb,recs){
									var rec = recs.length > 0 ? recs[0] : null;
									if(rec){
										var wpfield = me.down('form').getForm().findField('EmergencyTeam_Assistant1WorkPlace');
										wpfield.setValue(rec.get('MedStaffFact_id'));
									}
								}
							},
							onTrigger2Click: function(e) {
								var trigger = this,
									wpfield = me.down('form').getForm().findField('EmergencyTeam_Assistant1WorkPlace'),
									w = me.showMedPersonsWindow(wpfield.getValue());


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
							height: 10, width:700, border: 0
						},
						{
							fieldLabel: 'Водитель',
							name: 'EmergencyTeam_Driver',
							xtype: 'swEmergencyFIOCombo',
							store: me.medPersonallist,
							width: 750,
							typeAhead: true,
							editable: true,
							autoFilter: true,
							forceSelection: 'false',
							triggerClear: true,
							labelWidth: 150,
							allowBlank: (getRegionNick().inlist(['perm', 'ekb', 'khak'])),
							listeners: {
								select: function(cmb,recs){
									var rec = recs.length > 0 ? recs[0] : null;
									if(rec){
										var wpfield = me.down('form').getForm().findField('EmergencyTeam_DriverWorkPlace');
										wpfield.setValue(rec.get('MedStaffFact_id'));
									}
								}
							},
							onTrigger2Click: function(e) {
								var trigger = this,
									wpfield = me.down('form').getForm().findField('EmergencyTeam_DriverWorkPlace'),
									w = me.showMedPersonsWindow(wpfield.getValue());



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
								height: 10, width: 700, border: 0
						},
							Ext.create('sw.CMPTabletPC', {
								name: 'CMPTabletPC_id',
								fieldLabel: 'Планшетный компьютер',
								allowBlank: true,
								width: 750,
								labelWidth: 150,
								listeners:{
									expand: function(){
										this.getStore().suspendEvents()
									}
								},
								tpl: '<tpl for="."><div class=" x-boundlist-item">' +
								'{CMPTabletPC_Name}' + ' {CMPTabletPC_SIM}' +
								'</div></tpl>'
						}),
                        {
								height: 10, width: 700, border: 0
						},
                        {
								xtype: 'textfield',
								fieldLabel: 'Комментарий',
								labelWidth: 150,
								width: 750,
								name: 'EmergencyTeamDuty_ChangeComm'
						},
						{
							height: 10, width: 700, border: 0
						},
						{
							xtype: 'textfield',
							fieldLabel: 'Телефон',
							enableKeyEvents: true,
							labelWidth: 150,
							width: 750,
							plugins: [new Ux.InputTextMask('+7(999)-999-99-99', true)],
							name: 'EmergencyTeam_Phone'
						},
						{
								height: 10, width: 700, border: 0
						},
						{
							xtype: 'grid',
							width: 760,
							height: 200,
							refId: 'EmergencyTeamVigils',
							title: 'Дежурства',
							hidden: me.isNmpArm,
							viewConfig: {loadingText: 'Загрузка'},
							tbar: [
								{
									xtype: 'button',
									refId: 'add',
									iconCls: 'add16',
									text: 'Добавить',
									handler: function(){
										me.showEmergencyTeamVigilWindow({action: 'add'});
									}
								},
								{
									xtype: 'button',
									iconCls: 'edit16',
									text: 'Изменить',
									refId: 'edit',
									disabled: true,
									handler: function(){
										me.showEmergencyTeamVigilWindow({action: 'edit'});
									}
								},
								{
									xtype: 'button',
									iconCls: 'view16',
									text: 'Просмотр',
									refId: 'view',
									disabled: true,
									handler: function(){
										me.showEmergencyTeamVigilWindow({action: 'view'});
									}
								},
								{
									xtype: 'button',
									iconCls: 'delete16',
									text: 'Удалить',
									refId: 'delete',
									disabled: true,
									handler: function(){
										var frm = me.down('form').getForm(),
											emergencyTeamVigilsGrid = me.down('grid[refId=EmergencyTeamVigils]'),
											selectedRow = emergencyTeamVigilsGrid.getSelectionModel().getSelection()[0],
											selectedRowIndex = emergencyTeamVigilsGrid.store.indexOf(selectedRow);
										Ext.Msg.show({
											title:'Удаление дежурства',
											msg: 'Удалить дежурство?',
											buttons: Ext.Msg.YESNO,
											icon: Ext.Msg.WARNING,
											fn: function(btn){
												if (btn == 'yes'){
													if(me.saveFromGrid){
														emergencyTeamVigilsGrid.store.removeAt(selectedRowIndex)
													}else {
														Ext.Ajax.request({
															url: '/?c=EmergencyTeam4E&m=deleteEmergencyTeamVigil',
															params: {CmpEmTeamDuty_id: selectedRow.get("CmpEmTeamDuty_id")},
															callback: function (opt, success, response) {
																if (success) {
																	var callbackParams = Ext.decode(response.responseText);

																	if (callbackParams[0].Error_Msg != null) {
																		Ext.Msg.alert('Ошибка', callbackParams[0].Error_Msg);
																	} else {
																		emergencyTeamVigilsGrid.getStore().reload();
																	}
																}
															}
														});
													}
												}
											}
										});
									}
								}
							],
							store: Ext.create('Ext.data.Store', {
								autoLoad: false,
								fields: [
									{name: 'CmpEmTeamDuty_id', type:'int'},
									{name: 'EmergencyTeam_id', type:'int'},
									{name: 'CmpEmTeamDuty_PlanBegDT', type:'date', convert: function(v, record){
										var e = Ext.Date.parse(v, "Y-m-d H:i:s");
										return Ext.Date.format(e, 'd.m.Y H:i:s');
									}},
									{name: 'CmpEmTeamDuty_PlanEndDT', type:'date', convert: function(v, record){
										var e = Ext.Date.parse(v, "Y-m-d H:i:s");
										return Ext.Date.format(e, 'd.m.Y H:i:s');
									}},
									{name: 'CmpEmTeamDuty_FactBegDT', type:'date', convert: function(v, record){
										var e = Ext.Date.parse(v, "Y-m-d H:i:s");
										return Ext.Date.format(e, 'd.m.Y H:i:s');
									}},
									{name: 'CmpEmTeamDuty_FactEndDT', type:'date', convert: function(v, record){
										var e = Ext.Date.parse(v, "Y-m-d H:i:s");
										return Ext.Date.format(e, 'd.m.Y H:i:s');
									}},
									{name: 'address_AddressText', type:'string'},
									{name: 'CmpEmTeamDuty_Discription', type:'string'}
								],
								proxy: {
									limitParam: undefined,
									startParam: undefined,
									paramName: undefined,
									pageParam: undefined,
									type: 'ajax',
									url: '/?c=EmergencyTeam4E&m=loadEmergencyTeamVigils',
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
								{ text: 'id',  dataIndex: 'CmpEmTeamDuty_id', hidden: true },
								{ text: 'teamId',  dataIndex: 'EmergencyTeam_id', hidden: true },
								{ text: 'Начало план', dataIndex: 'CmpEmTeamDuty_PlanBegDT', width: 120 },
								{ text: 'Окончание план', dataIndex: 'CmpEmTeamDuty_PlanEndDT', width: 120 },
								{ text: 'Начало факт', dataIndex: 'CmpEmTeamDuty_FactBegDT', width: 120 },
								{ text: 'Окончание факт', dataIndex: 'CmpEmTeamDuty_FactEndDT', width: 120 },
								{ text: 'Адрес', dataIndex: 'address_AddressText', flex: 1 },
								{ text: 'Описание', dataIndex: 'CmpEmTeamDuty_Discription', flex: 1 }
							],
							listeners: {
								selectionchange: function(selmod, sel){
									var disable = (sel[0])?false:true;
									me.down('grid button[refId=view]').setDisabled(disable);
									if (me.mode!='view') {
										me.down('grid button[refId=edit]').setDisabled(disable);
										me.down('grid button[refId=delete]').setDisabled(disable);
									}
								}
							}
						}
					]
				}]
			}]
		});
	},
	
	setView: function(mode){
		var me = this,
			frm = me.down('form').getForm(),
			fields = frm.getFields(),
			
			applyChangesFromField = frm.findField('ApplyChangesFrom'),
			applyChangesFromTimeField = frm.findField('ApplyChangesFromTime'),
			emergencyTeamDuty_DTStartField = frm.findField('EmergencyTeamDuty_DTStart'),
			emergencyTeamDuty_DStartField = frm.findField('EmergencyTeamDuty_DStart'),
			emergencyTeamDuty_DTFinishField = frm.findField('EmergencyTeamDuty_DTFinish'),
			emergencyTeamDuty_DFinishField = frm.findField('EmergencyTeamDuty_DFinish'),
			emergencyTeam_NumField = frm.findField('EmergencyTeam_Num'),
			lpuBuilding_idField = frm.findField('LpuBuilding_id');
		
		switch(mode){
			case 'add':{
				applyChangesFromField.hide();
				applyChangesFromTimeField.hide();
				emergencyTeamDuty_DTStartField.show();
				emergencyTeamDuty_DStartField.show();
				emergencyTeamDuty_DTFinishField.show();
				emergencyTeamDuty_DFinishField.show();
				emergencyTeam_NumField.setReadOnly(false);
				lpuBuilding_idField.setReadOnly(false);
				lpuBuilding_idField.setCurrentLpuBuilding();
				
				emergencyTeamDuty_DTStartField.allowBlank = false;
				emergencyTeamDuty_DStartField.allowBlank = false;
				emergencyTeamDuty_DTFinishField.allowBlank = false;
				emergencyTeamDuty_DFinishField.allowBlank = false;
				break;
			}
			case 'addTemplate':{
				//добавить по шаблону
				applyChangesFromField.hide();
				applyChangesFromTimeField.hide();
				emergencyTeamDuty_DTStartField.show();
				emergencyTeamDuty_DStartField.show();
				emergencyTeamDuty_DTFinishField.show();
				emergencyTeamDuty_DFinishField.show();
				emergencyTeam_NumField.setReadOnly(false);
				lpuBuilding_idField.setReadOnly(true);
				//lpuBuilding_idField.setCurrentLpuBuilding();
				
				emergencyTeamDuty_DTStartField.allowBlank = false;
				emergencyTeamDuty_DStartField.allowBlank = false;
				emergencyTeamDuty_DTFinishField.allowBlank = false;
				emergencyTeamDuty_DFinishField.allowBlank = false;
				break;
			}
			case 'edit':{
				applyChangesFromField.hide();
				applyChangesFromTimeField.hide();
				emergencyTeamDuty_DTStartField.show();
				emergencyTeamDuty_DStartField.show();
				emergencyTeamDuty_DTFinishField.show();
				emergencyTeamDuty_DFinishField.show();
				emergencyTeam_NumField.setReadOnly(false);
				lpuBuilding_idField.setReadOnly(true);
				break;
			}
			case 'split':{
				applyChangesFromField.show();
				applyChangesFromTimeField.show();
				emergencyTeamDuty_DTStartField.hide();
				emergencyTeamDuty_DStartField.hide();
				emergencyTeamDuty_DTFinishField.hide();
				emergencyTeamDuty_DFinishField.hide();
				emergencyTeam_NumField.setReadOnly(true);
				lpuBuilding_idField.setReadOnly(true);
				break;
			}
			case 'view':{
				applyChangesFromField.hide();
				applyChangesFromTimeField.hide();
				me.down('button[refId=saveBtn]').disable();
				me.down('button[refId=add]').disable();
				me.down('button[refId=edit]').disable();
				me.down('button[refId=delete]').disable();
				fields.items.forEach(function(item){item.setReadOnly(true)})
				break;
		}
		}
		
		frm.isValid();
	},

    initComponent: function() {
        var me = this,
			conf = me.initialConfig,
			curArm = sw.Promed.MedStaffFactByUser.current.ARMType || sw.Promed.MedStaffFactByUser.last.ARMType,
			medServiceType_id = sw.Promed.MedStaffFactByUser.last.MedServiceType_id;

		me.isNmpArm = curArm.inlist(['dispnmp','dispcallnmp', 'dispdirnmp']);

		me.height = me.isNmpArm ? 500 : 700;

		me.addEvents({
			saveTeam: true
		});

		me.on('show', function(cmp){
			var form = me.down('form').getForm(),
				EmergencyTeamVigilsGrid = me.down('panel[refId=EmergencyTeamVigils]');
				
			me.setView(conf.mode);
			me.saveFromGrid = false;
			me.mode = conf.mode;

			if(conf.mode.inlist(['add','addTemplate'])){
				me.saveFromGrid = true;
				me.getDefaultPhoneNumber();
			}

			form.findField('EmergencyTeamDuty_DTStart').originalValue = conf.EmergencyTeamDuty_DTStart;
			form.findField('EmergencyTeamDuty_DStart').originalValue = conf.EmergencyTeamDuty_DStart;
			form.findField('EmergencyTeamDuty_DTFinish').originalValue = conf.EmergencyTeamDuty_DTFinish;
			form.findField('EmergencyTeamDuty_DFinish').originalValue = conf.EmergencyTeamDuty_DFinish;
			form.setValues(conf);

			form.findField('LpuBuilding_id').store.load({
				callback: function (recs, opts, success) {
					if (success && recs && recs[0]) {
						form.findField('LpuBuilding_id').setValue(conf.LpuBuilding_id? parseInt(conf.LpuBuilding_id): null)
					}
				}
			});

			EmergencyTeamVigilsGrid.getStore().load({params: {EmergencyTeam_id: conf.EmergencyTeam_id}});

		});

        Ext.applyIf(me, {
            layout: {
                type: 'vbox',
                align: 'stretch'
            },
            items: [
               this.getBaseForm(conf.mode)
            ],
			dockedItems: [{
				xtype: 'container',
				dock: 'bottom',
				layout: {
					type: 'hbox',
					align: 'stretch',
					padding: 4
				},
				items: [{
					xtype: 'container',
					layout: 'column',
					items: []
				}, {
					xtype: 'container',
					flex: 1,
					layout: {
						type: 'hbox',
						align: 'stretch',
						pack: 'end'
					},
					items: [
						{
							xtype: 'button',
							iconCls: 'ok16',
							text: 'Сохранить',
							refId: 'saveBtn',
							handler: function(){
								me.saveTeam(me.initialConfig.mode);
							}
						},
						{xtype: 'tbfill'},
						{
							xtype: 'button',
							text: 'Помощь',
							iconCls   : 'help16',
							tabIndex: 30,
							handler   : function()
							{
								ShowHelp(this.up('window').title);								
							}
						},
						{
							xtype: 'button',
							iconCls: 'cancel16',
							text: 'Закрыть',
							margin: '0 5',
							handler: function(){
								me.close()
							}
						}
					]
				}]
			}]
        });
        me.callParent(arguments);
    },
	
	showEmergencyTeamVigilWindow: function(params){

		var me = this,
			frm = me.down('form').getForm(),
			emergencyTeamVigilsGrid = me.down('grid[refId=EmergencyTeamVigils]'),
			selectedRow = emergencyTeamVigilsGrid.getSelectionModel().getSelection()[0],
			startDt = frm.findField('EmergencyTeamDuty_DTStart').getValue(),
			startTime = frm.findField('EmergencyTeamDuty_DStart').getValue(),
			finishDt = frm.findField('EmergencyTeamDuty_DTFinish').getValue(),
			finishTime = frm.findField('EmergencyTeamDuty_DFinish').getValue();
		
		//берем некоторые параметры с формы, тк на добавление тоже нужен EmergencyTeam_id
		//чтобы не возникало вопросов, почему на форме одно а выходит другое, даже если не сохранили
		params.EmergencyTeam_Num = frm.findField('EmergencyTeam_Num').getValue();
		params.EmergencyTeam_id = frm.findField("EmergencyTeam_id").getValue();
		params.EmergencyTeamDuty_DTStart = Ext.Date.format(startDt, 'Y-m-d') + ' '+ Ext.Date.format(startTime, 'H:i:s');
		params.EmergencyTeamDuty_DTFinish = Ext.Date.format(finishDt, 'Y-m-d') + ' '+ Ext.Date.format(finishTime, 'H:i:s');

		if(params.action.inlist(['edit', 'view'])){
			params.CmpEmTeamDuty_id = selectedRow.get("CmpEmTeamDuty_id");
		}
		else{
			params.CmpEmTeamDuty_FactBegDT = 'none';
		}
		
		var emergencyTeamVigilWindow = Ext.create('sw.tools.subtools.swEmergencyTeamVigilWindow', params);
		
		emergencyTeamVigilWindow.show();
		
		emergencyTeamVigilWindow.on('saveVigil', function(){
			emergencyTeamVigilWindow.close();
			if(!me.saveFromGrid){
				emergencyTeamVigilsGrid.getStore().reload();
			}
		})
	},
	
	saveTeam: function(mode){
		var me = this,
			frm = me.down('form').getForm(),
			params = frm.getValues(),
			showAutoMsg = false,
			startDt = frm.findField('EmergencyTeamDuty_DTStart').getValue(),
			finishDt = frm.findField('EmergencyTeamDuty_DTFinish').getValue(),
			applyChangesFrom = frm.findField('ApplyChangesFrom').getValue(),
			medProductCard = frm.findField('MedProductCard_id');

		if(frm.findField('EmergencyTeamDuty_DTStart').isDirty() ||
			frm.findField('EmergencyTeamDuty_DStart').isDirty() ||
			frm.findField('EmergencyTeamDuty_DTFinish').isDirty() ||
			frm.findField('EmergencyTeamDuty_DFinish').isDirty())
		{
			showAutoMsg = true;
			params.EmergencyTeamDuty_IsCancelledStart = false;
			params.EmergencyTeamDuty_IsCancelledClose = false;
		}

		params.EmergencyTeam_DutyTime = ( startDt - finishDt )/3600000;

		params.EmergencyTeamDuty_DTStart = Ext.Date.format(startDt, 'm.d.Y') + ' '+ params.EmergencyTeamDuty_DStart;
		params.EmergencyTeamDuty_DTFinish = Ext.Date.format(finishDt, 'm.d.Y') + ' '+ params.EmergencyTeamDuty_DFinish;
		params.applyChangesFrom = Ext.Date.format(applyChangesFrom, 'm.d.Y') + ' '+ params.ApplyChangesFromTime;

		if (!frm.isValid()) {
			Ext.Msg.alert(ERR_INVFIELDS_TIT, ERR_INVFIELDS_MSG);
			return;
		}
		
		if( mode.inlist(['add', 'edit']) && params.MedProductCard_id){

			if(!params.GeoserviceTransport_id){
				var mdc_id = medProductCard.findRecord('MedProductCard_id', params.MedProductCard_id);
				var geoservisID = ( mdc_id ) ? mdc_id.get('GeoserviceTransport_id') : false;
				params.GeoserviceTransport_id = (geoservisID) ? geoservisID : null;
			}

		}
		
		var dateStart = Ext.Date.parse(params.EmergencyTeamDuty_DTStart, 'm.d.Y H:i'),
			dateFinish = Ext.Date.parse(params.EmergencyTeamDuty_DTFinish, 'm.d.Y H:i');
		
		if(dateStart >= dateFinish){
			Ext.Msg.alert('Ошибка', 'Дата и время окончания смены наряда должны быть больше даты и времени начала смены');
			return false;
		}
		
		if(mode=='split'){
			var dateApplyTo = Ext.Date.parse(params.applyChangesFrom, 'm.d.Y H:i'),
				initParams = me.initialConfig;
				
			if(dateApplyTo >= dateFinish){
				Ext.Msg.alert('Ошибка', 'Дата и время изменения состава наряда не могут быть больше даты и времени окончания смены наряда');
				return false;
			}
			
			if(dateApplyTo <= dateStart){
				Ext.Msg.alert('Ошибка', 'Дата и время изменения состава наряда не могут быть меньше или равны дате и времени начала смены наряда');
				return false;
			}
			
			// Если не изменились значения ни в одном из следующих полей: 
			// «Автомобиль», «Старший бригады», «Помощник», «Помощник»,«Водитель», 
			// то выводится ошибка «Состав наряда не изменен. Сохранение невозможно».
			var checkUsersDirty = function(){
				for(var param in params){					
					params[param] = (params[param] == '') ? null : params[param];
					if(
						param.inlist(["MedProductCard_id", "EmergencyTeam_HeadShift", "EmergencyTeam_HeadShift2", "EmergencyTeam_Assistant1", "EmergencyTeam_Driver"]) 
						&& ( params[param] != initParams[param]) 
					){
						return true;
					}
				}
				return false;
			};
			
			if(!checkUsersDirty()){
				Ext.Msg.alert('Ошибка', 'Состав наряда не изменен. Сохранение невозможно');
				return false;
			}
			
		}

		if(
			params.EmergencyTeam_HeadShift2 > 0 &&
			(
				params.EmergencyTeam_HeadShift2	== params.EmergencyTeam_HeadShift ||
				params.EmergencyTeam_HeadShift2	== params.EmergencyTeam_Assistant1 ||
				params.EmergencyTeam_HeadShift2	== params.EmergencyTeam_Assistant2 ||
				params.EmergencyTeam_HeadShift2	== params.EmergencyTeam_Driver ||
				params.EmergencyTeam_HeadShift2	== params.EmergencyTeam_Driver2
			) ||
			params.EmergencyTeam_Assistant1 > 0 &&
			(
				params.EmergencyTeam_Assistant1 == params.EmergencyTeam_HeadShift ||
				params.EmergencyTeam_Assistant1 == params.EmergencyTeam_Assistant2 ||
				params.EmergencyTeam_Assistant1 == params.EmergencyTeam_Driver ||
				params.EmergencyTeam_Assistant1 == params.EmergencyTeam_Driver2
			) ||
			params.EmergencyTeam_Assistant2 > 0 &&
			(
				params.EmergencyTeam_Assistant2 == params.EmergencyTeam_HeadShift ||
				params.EmergencyTeam_Assistant2 == params.EmergencyTeam_Driver ||
				params.EmergencyTeam_Assistant2 == params.EmergencyTeam_Driver2
			) ||
				params.EmergencyTeam_Driver > 0 && (
				params.EmergencyTeam_Driver == params.EmergencyTeam_HeadShift ||
				params.EmergencyTeam_Driver == params.EmergencyTeam_Driver2
			) ||
			params.EmergencyTeam_Driver2 &&
			params.EmergencyTeam_Driver2 == params.EmergencyTeam_HeadShift
		)
		{
			Ext.Msg.alert('Ошибка', 'Пересечение врачей в наряде.');
		}
		else {
			var url = '/?c=EmergencyTeam4E&m=saveEmergencyTeams';
			
			if(mode.inlist(['add', 'edit', 'addTemplate'])){
				url = '/?c=EmergencyTeam4E&m=saveEmergencyTeams';
			}
			else{
				url = '/?c=EmergencyTeam4E&m=saveEmergencyTeamsSplit';
			}
			
			Ext.Ajax.request({
				url: url,
				params: {
					EmergencyTeams: Ext.encode([params])
				},
				callback: function(opt, success, response) {
					if (success){
						var callbackParams =  Ext.decode(response.responseText)[0];
						if (callbackParams.Error_Msg != null) {
                            switch (callbackParams.Error_Code){
                                case '1': {
                                    Ext.Msg.show({
                                        title:'Внимание',
                                        msg: callbackParams.Error_Msg + ' Продолжить сохранение?',
                                        buttons: Ext.Msg.YESNO,
                                        icon: Ext.Msg.WARNING,
                                        fn: function(btn){
                                            if (btn == 'yes'){
                                                frm.findField('checkCarByDutyDate').setValue(false);
                                                me.saveTeam(mode);
                                            }
                                        }
                                    });
                                    break;
                                }
                                default:{
                                    Ext.Msg.alert('Ошибка', callbackParams.Error_Msg);
                                    break;
                                }
                            }

						} else {
							if(me.saveFromGrid){
								me.saveGrid(callbackParams[0].EmergencyTeam_id);
							}
							me.fireEvent('saveTeam', callbackParams[0].EmergencyTeam_id);
							//me.callback(callbackParams);
							if(showAutoMsg){
								var autoMsg = '';
								if(callbackParams.SmpUnitParam_IsAutoEmergDuty == 2){
									autoMsg= 'Бригада будет выведена на смену автоматически';
								}
								if(callbackParams.SmpUnitParam_IsAutoEmergDutyClose == 2) {
									autoMsg = 'Смена бригады будет закрыта автоматически';
								}
								if(callbackParams.SmpUnitParam_IsAutoEmergDuty == 2 && callbackParams.SmpUnitParam_IsAutoEmergDutyClose == 2){
									autoMsg = 'Вывод бригады и закрытие смены будут произведены автоматически';
								}
								if(autoMsg.length > 0)
									me.showYellowMsg(autoMsg, 3000)
							}
							me.close();

							if (me.callback) {
								me.callback()
							}
						}
					}
				}
			});
		}
	},

	saveGrid: function(EmergencyTeam_id){
		var	EmergencyTeamVigils = Ext.ComponentQuery.query('grid[refId=EmergencyTeamVigils]')[0].getStore();

		EmergencyTeamVigils.data.each(function (record) {
			var values = record.raw;
			values.EmergencyTeam_id = EmergencyTeam_id;

			Ext.Ajax.request({
				url: '/?c=EmergencyTeam4E&m=saveEmergencyTeamVigil',
				params: values,
				callback: function(opt, success, response) {
					if (success){
						var callbackParams =  Ext.decode(response.responseText);
						if (callbackParams[0].Error_Msg != null) {
							Ext.Msg.alert('Ошибка', callbackParams[0].Error_Msg);
						}
					}
				}
			})
		});
		EmergencyTeamVigils.reload();
	},

	getDefaultPhoneNumber: function(){
		var me = this,
			frm = me.down('form').getForm(),
			MedProductCard = frm.findField('MedProductCard_id'),
			EmergencyTeam_Phone = frm.findField('EmergencyTeam_Phone'),
			EmergencyTeam_HeadShiftWorkPlace = frm.findField('EmergencyTeam_HeadShiftWorkPlace');
		if(!EmergencyTeam_Phone.getValue() && !Ext.isEmpty(MedProductCard.getValue()) && !Ext.isEmpty(EmergencyTeam_HeadShiftWorkPlace.getValue())){
			Ext.Ajax.request({
				url: '/?c=EmergencyTeam4E&m=getDefaultPhoneNumber',
				params: {
					MedProductCard_id: MedProductCard.getValue(),
					EmergencyTeam_HeadShift:EmergencyTeam_HeadShiftWorkPlace.getValue()
				},
				callback: function(opt, success, response) {
					if (success){
						var res =  Ext.decode(response.responseText);
						if(res[0]){
							EmergencyTeam_Phone.setValue(res[0])
						}

					}
				}
			});

		}
	},
	showYellowMsg: function(msg, delay){
		var div = document.createElement('div');

		div.style.width='300px';
		div.style.height='65px';
		div.style.background='#edcd4b';
		div.style.border='solid 2px #efefb3';
		div.style.position='absolute';
		div.style.padding='10px';
		div.style.zIndex='99999';
		div.innerHTML = msg;
		div.style.right = 0;
		div.style.bottom = '50px';
		document.body.appendChild(div);

		setTimeout(function(){
			div.parentNode.removeChild(div);
		}, delay);
	}
});