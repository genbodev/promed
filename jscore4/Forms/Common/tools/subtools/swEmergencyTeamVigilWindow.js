/*
	Окно дежурство
	vigil - бдение, дежурство, бодрствование
*/


Ext.define('sw.tools.subtools.swEmergencyTeamVigilWindow', {
	alias: 'widget.swEmergencyTeamVigilWindow',
	extend: 'sw.standartToolsWindow',
	width: 500,
	height: 330,
	title: 'Дежурство',

	listeners:{
		show: function(conf){
			var me = this,
				frm = me.down('form').getForm();

			me.action = conf.action;
			me.setEnableFields(conf.action);

			if(conf.EmergencyTeam_id){

				Ext.Ajax.request({
					url: '/?c=EmergencyTeam4E&m=loadSingleEmergencyTeamVigil',
					params: {
						EmergencyTeam_id : conf.EmergencyTeam_id,
						CmpEmTeamDuty_id : conf.CmpEmTeamDuty_id
					},
					callback: function(opt, success, response) {
						if (success){
							var callbackParams =  Ext.decode(response.responseText);

							if (callbackParams[0].Error_Msg != null) {
								Ext.Msg.alert('Ошибка', callbackParams[0].Error_Msg);
							} else {
								
								var formValues = callbackParams[0],
									planBegDT, planEndDT, factBegDT, factEndDT;

								//при добавлении если пришли параметры при загрузке - вставляем их, иначе с базы								
								if(conf.action == 'add'){
									planBegDT = Ext.Date.parse((conf.EmergencyTeamDuty_DTStart||formValues.CmpEmTeamDuty_PlanBegDT), 'Y-m-d H:i:s');
									planEndDT = Ext.Date.parse((conf.EmergencyTeamDuty_DTFinish||formValues.CmpEmTeamDuty_PlanEndDT), 'Y-m-d H:i:s');
								}
								else{
									//редактирование и просмотр - только с базы
									planBegDT = Ext.Date.parse(formValues.CmpEmTeamDuty_PlanBegDT, 'Y-m-d H:i:s');
									planEndDT = Ext.Date.parse(formValues.CmpEmTeamDuty_PlanEndDT, 'Y-m-d H:i:s');									
								}
								factBegDT = Ext.Date.parse((conf.CmpEmTeamDuty_FactBegDT||formValues.CmpEmTeamDuty_FactBegDT), 'Y-m-d H:i:s');
								factEndDT = Ext.Date.parse((conf.CmpEmTeamDuty_FactEndDT||formValues.CmpEmTeamDuty_FactEndDT), 'Y-m-d H:i:s');
								
								//factBegDT = Ext.Date.parse(formValues.CmpEmTeamDuty_FactBegDT, 'Y-m-d H:i:s');
								//factEndDT = Ext.Date.parse(formValues.CmpEmTeamDuty_FactEndDT, 'Y-m-d H:i:s');
								
								formValues.CmpEmTeamDuty_PlanBegDTDate = planBegDT;
								formValues.CmpEmTeamDuty_PlanBegDTTime = planBegDT;
								formValues.CmpEmTeamDuty_PlanBegDT = Ext.Date.format(planBegDT, 'm.d.Y H:i:s');
								
								formValues.CmpEmTeamDuty_PlanEndDTDate = planEndDT;
								formValues.CmpEmTeamDuty_PlanEndDTTime = planEndDT;
								formValues.CmpEmTeamDuty_PlanEndDT = Ext.Date.format(planEndDT, 'm.d.Y H:i:s');		
								
								formValues.CmpEmTeamDuty_FactBegDTDate = factBegDT;
								formValues.CmpEmTeamDuty_FactBegDTTime = factBegDT;
								formValues.CmpEmTeamDuty_FactBegDT = Ext.Date.format(factBegDT, 'm.d.Y H:i:s');
								
								formValues.CmpEmTeamDuty_FactEndDTDate = factEndDT;
								formValues.CmpEmTeamDuty_FactEndDTTime = factEndDT;
								formValues.CmpEmTeamDuty_FactEndDT = Ext.Date.format(factEndDT, 'm.d.Y H:i:s');
								formValues.EmergencyTeam_Num = conf.EmergencyTeam_Num;

								frm.setValues(formValues);
							}
						}
					}
				});
			} else{ // Нет EmergencyTeam_id значит дежурство добавляется для новой бригады
				var formValues = {},

					planBegDT,planEndDT;
				// При добавлении дежурства параметры времени добавляются с формы бригады
				if(conf.action == 'add'){
					planBegDT = Ext.Date.parse(conf.EmergencyTeamDuty_DTStart, "Y-m-d H:i:s");
					planEndDT = Ext.Date.parse(conf.EmergencyTeamDuty_DTFinish, "Y-m-d H:i:s");
				}
				else{ // при редактировании/просмотре параметры времени добавляются с грида дежурств
					var EmergencyTeamVigils = Ext.ComponentQuery.query('grid[refId=EmergencyTeamVigils]')[0];
					me.EmergencyTeamVigilsIndex = EmergencyTeamVigils.store.indexOf(EmergencyTeamVigils.getSelectionModel().getSelection()[0]);

					formValues = EmergencyTeamVigils.getSelectionModel().getSelection()[0].data;
					planBegDT = Ext.Date.parse(formValues.CmpEmTeamDuty_PlanBegDT, "d.m.Y H:i:s");
					planEndDT = Ext.Date.parse(formValues.CmpEmTeamDuty_PlanEndDT, "d.m.Y H:i:s");
				}
				formValues.CmpEmTeamDuty_PlanBegDTDate = planBegDT;
				formValues.CmpEmTeamDuty_PlanBegDTTime = planBegDT;

				formValues.CmpEmTeamDuty_PlanEndDTDate = planEndDT;
				formValues.CmpEmTeamDuty_PlanEndDTTime = planEndDT;
				formValues.EmergencyTeam_Num = conf.EmergencyTeam_Num;

				frm.setValues(formValues);
				me.setHiddenDateFields();
			}
		}
	},
	
	setEnableFields: function(action){
		var me = this,
			frm = me.down('form').getForm(),
			saveBtn = me.down('button[refId=saveButton]'),
			readOnly = action.inlist(['view']);
			
		frm.findField("CmpEmTeamDuty_PlanBegDTDate").setReadOnly(readOnly);
		frm.findField("CmpEmTeamDuty_PlanBegDTTime").setReadOnly(readOnly);
		frm.findField("CmpEmTeamDuty_PlanEndDTDate").setReadOnly(readOnly);
		frm.findField("CmpEmTeamDuty_PlanEndDTTime").setReadOnly(readOnly);
		frm.findField("address_AddressText").setReadOnly(readOnly);
		frm.findField("CmpEmTeamDuty_Discription").setReadOnly(readOnly);
		saveBtn.setDisabled(readOnly);
	},
	
	checkDates: function(){
		var me = this,
			frm = me.down('form').getForm(),
			hiddenStartDTfield = frm.findField("CmpEmTeamDuty_PlanBegDT"),
			hiddenEndDTfield = frm.findField("CmpEmTeamDuty_PlanEndDT");
		
		if(hiddenEndDTfield.getValue() < hiddenStartDTfield.getValue()){			
			return false;
		}
		return true;
	},
	
	setHiddenDateFields: function(){
		var me = this,
			frm = me.down('form').getForm(),
			hiddenStartDTfield = frm.findField("CmpEmTeamDuty_PlanBegDT"),
			hiddenEndDTfield = frm.findField("CmpEmTeamDuty_PlanEndDT"),
			planBegDTDatefield = frm.findField("CmpEmTeamDuty_PlanBegDTDate"),
			planBegDTTimefield = frm.findField("CmpEmTeamDuty_PlanBegDTTime"),
			planEndDTDatefield = frm.findField("CmpEmTeamDuty_PlanEndDTDate"),
			planEndDTTimefield = frm.findField("CmpEmTeamDuty_PlanEndDTTime");

		var hiddenPlanBegDTDateVal = Ext.Date.format(planBegDTDatefield.getValue(), 'm.d.Y') + ' ' +planBegDTTimefield.getRawValue();
		var hiddenPlanEndDTDateVal = Ext.Date.format(planEndDTDatefield.getValue(), 'm.d.Y') + ' ' +planEndDTTimefield.getRawValue();
		hiddenStartDTfield.setValue(hiddenPlanBegDTDateVal);
		hiddenEndDTfield.setValue(hiddenPlanEndDTDateVal);
	},

	initComponent: function() {
		var win = this,
			conf = win.initialConfig;

		win.addEvents({
			saveVigil: true
		});

		win.centerContent = Ext.create('Ext.container.Container', {
			layout: {
				type: 'table',
				columns: 3
			},
			padding: 5,
			defaults: {
				labelWidth: 100				
			},
			items: [
				{					
					xtype: 'container',
					hidden: true,
					colspan: 3,
					items: [
						{
							xtype: 'hidden',
							name: 'EmergencyTeam_id'
						},
						{
							xtype: 'hidden',
							name: 'CmpEmTeamDuty_id'
						},
						{
							xtype: 'hidden',
							name: 'KLRgn_id'
						},
						{
							xtype: 'hidden',
							name: 'KLSubRgn_id'
						},
						{
							xtype: 'hidden',
							name: 'KLCity_id'
						},
						{
							xtype: 'hidden',
							name: 'KLTown_id'
						},
						{
							xtype: 'hidden',
							name: 'KLStreet_id'
						},
						{
							xtype: 'hidden',
							name: 'CmpEmTeamDuty_House'
						},
						{
							xtype: 'hidden',
							name: 'CmpEmTeamDuty_Corpus'
						},
						{
							xtype: 'hidden',
							name: 'CmpEmTeamDuty_Flat'
						},
						{
							xtype: 'hidden',
							name: 'UnformalizedAddressDirectory_id'
						}
					]
				},
				{
					xtype: 'textfield',
					fieldLabel: '№ бригады',
					width: 240,
					name: 'EmergencyTeam_Num',
				//	allowBlank: false,
					colspan: 3,
					readOnly: true
					//readOnly: ( mode == 'edit' )? false: true,
				},

				{
					fieldLabel: 'Начало план',
					xtype: 'datefield',
					name: 'CmpEmTeamDuty_PlanBegDTDate',
					width: 260,
					format: 'd.m.Y',
					invalidText: 'Неправильный формат даты',
					plugins: [new Ux.InputTextMask('99:99:9999')],
					listeners: {
						select: function(e) {
							win.setHiddenDateFields();
						}
					}
				},
				{
					xtype: 'datefield',
					name: 'CmpEmTeamDuty_PlanBegDTTime',
					width: 80,
					triggerCls: 'x-form-clock-trigger',
					format: 'H:i',
					plugins: [new Ux.InputTextMask('99:99')],
					invalidText: 'Неправильный формат времени',
					onTriggerClick: function(e) {
						this.setValue(Ext.Date.format(new Date(), 'H:i'));
						win.setHiddenDateFields();
					},
					listeners: {
						blur: function(){
							win.setHiddenDateFields();
						}
					}
				},
				{
					xtype: 'hidden',
					name: 'CmpEmTeamDuty_PlanBegDT'
				},

				{
					fieldLabel: 'Окончание план',
					xtype: 'datefield',
					name: 'CmpEmTeamDuty_PlanEndDTDate',
					width: 260,
					format: 'd.m.Y',
					invalidText: 'Неправильный формат даты',
					plugins: [new Ux.InputTextMask('99:99:9999')],
					listeners: {
						select: function(e) {
							win.setHiddenDateFields();
						}
					}
				},
				{
					xtype: 'datefield',
					name: 'CmpEmTeamDuty_PlanEndDTTime',
					width: 80,
					triggerCls: 'x-form-clock-trigger',
					format: 'H:i',
					plugins: [new Ux.InputTextMask('99:99')],
					invalidText: 'Неправильный формат времени',
					onTriggerClick: function(e) {
						this.setValue(Ext.Date.format(new Date(), 'H:i'));
						win.setHiddenDateFields();
					},
					listeners: {
						blur: function(){
							win.setHiddenDateFields();
						}
					}
				},
				{
					xtype: 'hidden',
					name: 'CmpEmTeamDuty_PlanEndDT'
				},

				{
					fieldLabel: 'Начало факт',
					xtype: 'datefield',
					name: 'CmpEmTeamDuty_FactBegDTDate',
					width: 260,
					format: 'd.m.Y',
					invalidText: 'Неправильный формат даты',
					//plugins: [new Ux.InputTextMask('99:99:9999')],
					readOnly: true
				},
				{
					xtype: 'datefield',
					name: 'CmpEmTeamDuty_FactBegDTTime',
					width: 80,
					triggerCls: 'x-form-clock-trigger',
					format: 'H:i',
					readOnly: true,
					//plugins: [new Ux.InputTextMask('99:99')],
					invalidText: 'Неправильный формат времени',
					onTriggerClick: function(e) {
						var dt = new Date(),
							dtfield = win.down('form').getForm().findField('CmpEmTeamDuty_FactBegDTDate'),
							hiddenDTfield = win.down('form').getForm().findField('CmpEmTeamDuty_FactBegDT');

						this.setValue(Ext.Date.format(dt, 'H:i'));
						dtfield.setValue(Ext.Date.format(dt, 'd.m.Y'));
						hiddenDTfield.setValue(Ext.Date.format(dt, 'm.d.Y H:i'));
					}
				},
				{
					xtype: 'hidden',
					name: 'CmpEmTeamDuty_FactBegDT'
				},

				{
					fieldLabel: 'Окончание факт',
					xtype: 'datefield',
					name: 'CmpEmTeamDuty_FactEndDTDate',
					width: 260,
					format: 'd.m.Y',
					invalidText: 'Неправильный формат даты',
					//plugins: [new Ux.InputTextMask('99:99:9999')],
					readOnly: true
				},
				{
					xtype: 'datefield',
					name: 'CmpEmTeamDuty_FactEndDTTime',
					width: 80,
					triggerCls: 'x-form-clock-trigger',
					format: 'H:i',
					readOnly: true,
					//plugins: [new Ux.InputTextMask('99:99')],
					invalidText: 'Неправильный формат времени',
					onTriggerClick: function(e) {
						var dt = new Date(),
							dtfield = win.down('form').getForm().findField('CmpEmTeamDuty_FactEndDTDate'),
							hiddenDTfield = win.down('form').getForm().findField('CmpEmTeamDuty_FactEndDT');

						this.setValue(Ext.Date.format(dt, 'H:i'));
						dtfield.setValue(Ext.Date.format(dt, 'd.m.Y'));
						hiddenDTfield.setValue(Ext.Date.format(dt, 'm.d.Y H:i'));
					}
				},
				{
					xtype: 'hidden',
					name: 'CmpEmTeamDuty_FactEndDT',
					width: 100
				},
				{
					xtype: 'AddressCombo',
					fieldLabel: 'Адрес',
					name: 'address_AddressText',
					allowBlank: false,
					allowOnlyWhitespace: false,
					width: 450,
					colspan: 3,
					onTrigger2Click : function(){
						var field = this;

						field.reset();
					},
					onTrigger1Click : function(){
						var field = this,
							form = win.down('form').getForm(),
							values = form.getValues(),
							addressObj = {
								Country_id: ( getRegionNick() == 'kz' ) ? 398 : 643,
								KLRegion_id: ( values.KLRgn_id ) ? parseInt(values.KLRgn_id) : getGlobalOptions().region.number,
								KLSubRGN_id: (values.KLSubRgn_id) ? parseInt(values.KLSubRgn_id): 0,
								KLCity_id: (values.KLCity_id) ? parseInt(values.KLCity_id): 0,
								KLTown_id: (values.KLTown_id) ? parseInt(values.KLTown_id): 0,
								KLStreet_id: (values.KLStreet_id) ? parseInt(values.KLStreet_id): 0,
								House: values.CmpEmTeamDuty_House,
								Corpus: values.CmpEmTeamDuty_Corpus,
								Flat: values.CmpEmTeamDuty_Flat
							};

						field.showAddressWindow(addressObj, function(dataAddress){
							form.findField('KLRgn_id').setValue(dataAddress.KLRegion_id);
							form.findField('KLSubRgn_id').setValue(dataAddress.KLSubRGN_id);
							form.findField('KLCity_id').setValue(dataAddress.KLCity_id);
							form.findField('KLTown_id').setValue(dataAddress.KLTown_id);
							form.findField('KLStreet_id').setValue(dataAddress.KLStreet_id);
							form.findField('CmpEmTeamDuty_House').setValue(dataAddress.House);
							form.findField('CmpEmTeamDuty_Corpus').setValue(dataAddress.Corpus);
							form.findField('CmpEmTeamDuty_Flat').setValue(dataAddress.Flat);
							field.setValue(dataAddress.full_address);
						});
					}
				},
				{
					xtype: 'textarea',
					fieldLabel: 'Описание',
					name: 'CmpEmTeamDuty_Discription',
					colspan: 3,
					width: 450
				}
			]
		})

		win.saveBtn = Ext.create('Ext.button.Button', {
			text: 'Сохранить',
			iconCls: 'ok16',
			refId: 'saveButton',
			disabled: false,
			handler: function(){

				var form = win.down('form').getForm(),
					values = form.getValues();
				
				if(!win.checkDates()){
					Ext.Msg.alert('Ошибка', 'Дата и время окончания план дежурства не могут быть меньше даты и времени начала план дежурства');
					return false;
				}
				if(conf.EmergencyTeam_id){ // Если передена EmergencyTeam_id значит бригада существует. Сохраняем дежурство в БД
					Ext.Ajax.request({
						url: '/?c=EmergencyTeam4E&m=saveEmergencyTeamVigil',
						params: values,
						callback: function(opt, success, response) {
							if (success){
								var callbackParams =  Ext.decode(response.responseText);

								if (callbackParams[0].Error_Msg != null) {
									Ext.Msg.alert('Ошибка', callbackParams[0].Error_Msg);
								} else {
									win.fireEvent('saveVigil');
								}
							}
						}
					});
				} else{ // Если нет EmergencyTeam_id значит новая бригада. Сохраняем дежурство в грид дежурств
					var EmergencyTeamVigils = Ext.ComponentQuery.query('grid[refId=EmergencyTeamVigils]')[0].store;
					values.CmpEmTeamDuty_PlanBegDT = Ext.Date.format(Ext.Date.parse(values.CmpEmTeamDuty_PlanBegDT, "m.d.Y H:i"), 'Y-m-d H:i:s');
					values.CmpEmTeamDuty_PlanEndDT = Ext.Date.format(Ext.Date.parse(values.CmpEmTeamDuty_PlanEndDT, "m.d.Y H:i"), 'Y-m-d H:i:s');

					if(win.action =='add'){
						EmergencyTeamVigils.add(values);
					}else{
						EmergencyTeamVigils.removeAt(win.EmergencyTeamVigilsIndex);
						EmergencyTeamVigils.insert(win.EmergencyTeamVigilsIndex, values);
					}
					win.close();
				}
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