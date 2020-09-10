/**
* swHTMRegisterEditWindow - Регистр ВМП: редактирование
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package	  Common
* @access	   public
* @copyright	Copyright (c) 2018 EMSIS.
* @author	   Салават Магафуров
* @version	  2018/11
*/

sw.Promed.swHTMRegisterEditWindow = Ext.extend(sw.Promed.BaseForm, {
	title: 'Регистр ВМП: Редактирование',
	id: 'swHTMRegisterEditWindow',

	maximizable:false,
	maximized: true,
	autoHeight: false,
	autoScroll: true,
	resizable: false,
	modal: true,
	shim: false,
	noStage: 100, //чтобы не разблокировать поле при открытии этапа
	editable: false,
	onHide: Ext.emptyFn,
	onSelect: Ext.emptyFn,

	buttons: [{
			iconCls: 'ok16',
			text: BTN_FRMSAVE,
			handler: function() {
				swHTMRegisterEditWindow.saveForm();
			}
		}, {
			iconCls: 'ok16',
			text: langs('Подписать'),
			handler: function() {
				Ext.Msg.confirm("ВНИМАНИЕ!","После подписания документа внесение изменений в него будет невозможно. Вы уверены, что хотите подписать документ?", function(btn){
					if (btn == 'yes'){
						swHTMRegisterEditWindow.saveForm(true);
					}
				});
			}
		}, {
			text: '-'
		},
		HelpButton(this, 0),
		{
			iconCls: 'cancel16',
			text: BTN_FRMCANCEL,
			handler: function()  {
				this.ownerCt.hide();
			}
	}],

	hideStageButtons: function(stage) {
		if(stage != undefined) {
			this.NextStageButtons[stage].hide();
		} else {
			this.NextStageButtons.forEach(button => {
				button.hide();
			});
		}
	},

	enableTab: function(stage) {
		this.setValue('HTMRegister_Stage',stage);
		this.enableStage( stage );
		this.showStageButton();
		this.TabPanel.setActiveTab( stage );
	},

	showStageButton: function(stage) {
		this.hideStageButtons();

		var stage = this.getValue('HTMRegister_Stage');
		if(stage < 6)
		this.NextStageButtons[stage].show();
	},

	disableFields: function () {
		this.getForm().items.each ( function (field) {
			if(field.stage >= 0) {
				field.setDisabled(true);
			}
		})
	},

	enableFields: function (stage) {
		this.getForm().items.each( function (field) {
			if (field.stage <= stage && !this.getTab(field.stage).disabled) {
				field.enable();
			}
		}.createDelegate(this));
		this.fireEventChange('HTMDecision_FirstId');
	},

	getTab: function(stage) {
		return this.TabPanel.getItem(stage);
	},

	setAllowBlank: function(field,allow) {
		this.getField(field).allowBlank = allow;
		this.getField(field).validate();
	},

	setDisabled: function(field,disabled) {
		this.getField(field).setDisabled(disabled);
	},

	enableStage: function(stage) {
		this.disableFields();
		this.enableFields(stage);
	},

	show: function() {
		sw.Promed.swHTMRegisterEditWindow.superclass.show.apply(this, arguments);
		var params = arguments[0];

		this.editable = params.action == 'edit';

		this.getForm().reset();
		this.ViewFrame.onRowSelect = Ext.emptyFn;

		this.TabPanel.setActiveTab(0);
		this.disableFields();
		this.hideStageButtons();

		this.setValue('Register_PersonId', params.Person_id);
		this.setValue('Lpu_id', params.Lpu_sid);

		this.setValue('QueueNumber', params.QueueNumber);
		this.ViewFrame.setParam('Register_id',params.Register_id);
		this.ViewFrame.loadData({
			valueOnFocus: params.HTMRegister_id,
			callback: function() {
				this.ViewFrame.onRowSelect = function (sm, index, record) {
					this.loadForm(record.get('HTMRegister_id'));
					this.TabPanel.setActiveTab(0);
					var lpu_id = this.getValue('Lpu_id');
					this.getForm().reset();
					this.setValue('Lpu_id',lpu_id);
					this.disableFields();
					this.hideStageButtons();
				}.createDelegate(this);
			}.createDelegate(this)
		});

		this.loadForm(params.HTMRegister_id);

		var htmedicalcareclass = this.getField('HTMedicalCareClass_id');
		htmedicalcareclass.getStore().addListener('load',function(store,rec,options) {
			var value = htmedicalcareclass.getValue();
			value = Ext.isEmpty(store.getById(value)) ? null : htmedicalcareclass.getValue();
			htmedicalcareclass.setValue ( value );
			htmedicalcareclass.fireEvent( 'change', htmedicalcareclass, htmedicalcareclass.getValue() );
		})

	},
	
	getDate: function(field) {

		if(this.isEmpty(field))
			return null;
		return this.getValue(field).format('d.m.Y');

	},

	saveForm: function(sign = false) {
		var baseForm = this.getForm();

		var rejReason_id = this.getValue('HTMRejectionReason_id'),
			htmdecision_id1 = this.getValue('HTMDecision_FirstId'),
			HTMDecision_SecondId = this.getValue('HTMDecision_SecondId'),
			htmdecision_id4 = this.getValue('HTMDecision_FourthId'),
			waitingreason_id = this.getValue('HTMWaitingReason_id'),
			waitBegDate = this.getDate('HTMRegister_WaitBegDate'),
			waitEndDate = this.getDate('HTMRegister_WaitEndDate'),
			isWait = !Ext.isEmpty(waitingreason_id) && Ext.isEmpty(waitEndDate);

		if(!Ext.isEmpty(rejReason_id) || htmdecision_id4 == 9 || htmdecision_id4 == 11) {
			this.setValue('HTMQueueType_id',3); //Исключение из очереди
		} else if (isWait) {
			this.setValue('HTMQueueType_id',2);
		} else {
			this.setValue('HTMQueueType_id',1);
		}

		if(sign) {
			switch(true) {
				case !this.isValid(): return;
				case isWait:
					this.showMessage('Сообщение', 'Заполните поле "Дата окончания ожидания"');
					return;
				case this.getValue('HTMRegister_Stage') != 6:
					this.showMessage('Сообщение', 'Перейдите на 6 этап');
					return;
			}
		}

		if(!this.getField('HTMRegister_WaitBegDate').isValid()) {
			this.showMessage('Сообщение', 'Заполните поле "Дата начала ожидания"');
			return;
		}

		if(!this.getField('HTMRegister_WaitEndDate').isValid()) {
			this.showMessage('Сообщение', 'Некорректно заполнено поле "Дата окончания ожидания"');
			return;
		}
		var params = new Object();
		params.HTMRegister_Number = this.getValue('HTMRegister_Number');
		params.HTMRegister_IsSigned = sign ? 2 : 1;

		//Решили оказать вмп
		if(htmdecision_id1 == 1) {
			//Не отказали на втором этапе
			if(HTMDecision_SecondId && HTMDecision_SecondId != 5 && HTMDecision_SecondId != 8) {
				//Госпитализировали на 4 этапе
				if(htmdecision_id4 == 9) {
					params.HTMRegister_ApplicationDate = this.getDate('HTMRegister_ApplicationDate');
					params.HTMRegister_DisDate = this.getDate('HTMRegister_DisDate');
				}
			}
		}

		if(HTMDecision_SecondId != 5 && HTMDecision_SecondId != 8)
			params.HTMRegister_PlannedHospDate = this.getDate('HTMRegister_PlannedHospDate');
		params.HTMedicalCareClass_id = this.getValue('HTMedicalCareClass_id');
		params.Diag_FirstId = this.getValue('Diag_FirstId');
		params.HTMRejectionReason_id = this.getValue('HTMRejectionReason_id');
		params.HTMWaitingReason_id = waitingreason_id;
		params.HTMRegister_WaitBegDate = waitBegDate;
		params.HTMRegister_WaitEndDate = waitEndDate;

		baseForm.baseParams = params;

		baseForm.submit({
			clientValidation: false,
			url: '/?c=HTMRegister&m=doSave',
			success: function(form, action) {
				this.getLoadMask().hide();
				var result = action.result;
				if(result) {
					if(result.HTMRegister_id)
						this.hide();
					else
						this.showMessage('Сообщение', 'Неизвестная ошибка');
				} else {
					this.showMessage('Сообщение', 'Ошибка при сохранении, попробуйте еще раз');
				}
			}.createDelegate(this),

			failure: function(form, action) {
				this.getLoadMask().hide();
				switch (action.failureType) {
					case Ext.form.Action.CLIENT_INVALID:
						Ext.Msg.alert("Ошибка", "Не заполнены обязательные поля");
				break;
					case Ext.form.Action.CONNECT_FAILURE:
						Ext.Msg.alert("Ошибка", "Ошибка соединения");
				break;
					case Ext.form.Action.SERVER_INVALID:
						Ext.Msg.alert("Ошибка", action.result.msg);
				}
			}.createDelegate(this)
		})
	},

	loadStore: function(field,params = null) {
		var store = this.getField(field).getStore();
		store.baseParams = Object.assign(store.baseParams,params);
		store.load();
	},

	getForm: function() {
		return this.FormPanel.getForm();
	},

	getField: function(name) {
		return this.getForm().findField(name);
	},

	getValue: function(field) {
		return this.getField(field).getValue();
	},

	setValue: function(field,value) {
		this.getField(field).setValue(value);
	},

	setRawValue: function(field,value) {
		this.getField(field).setRawValue(value);
	},

	isEmpty: function(field) {
		return Ext.isEmpty(this.getValue(field));
	},

	fireEventChange: function(field,value) {
		field = this.getField(field);
		if(value === undefined)
			value = field.getValue();
		field.setValue(value);
		field.fireEvent('change',field, value);
	},

	isValid: function() {
		if(!this.getForm().isValid()) {
			this.showMessage('Сообщение','Не заполнены обязательные поля');
			return false;
		}
		var stage = this.getValue('HTMRegister_Stage');
		//Если госпитализируем, нужно проверить поля "Дата обращения в МО-ВМП","Дата выписки пациента из МО-ВМП"
		if(this.getValue('HTMDecision_FourthId') == 9 && stage>=4 && !this.getTab(4).disabled) {
			if(this.isEmpty('HTMRegister_ApplicationDate')) {
				this.showMessage('Сообщение','Поле "Дата обращения пациента в МО-ВМП" обязательно для заполнения');
				return false;
			}
			if(this.isEmpty('HTMRegister_DisDate') && stage >= 5 && !this.getTab(5).disabled) {
				this.showMessage('Сообщение','Поле "Дата выписки пациента из МО-ВМП" обязательно для заполнения');
				return false;
			}
		}
		
		return this.getField('HTMRegister_WaitBegDate').isValid();
	},

	isAllowedToPlanDate: function(HTMRegister_Number) {
		Ext.Ajax.request({
			url: '/?c=HTMRegister&m=isAllowedToPlanDate',
			params: { 'HTMRegister_Number': HTMRegister_Number,
				'Lpu_id': this.getValue('Lpu_id') },
			callback: function(options, success, response) {
				if(success) {
					var HTMRegister_id = Ext.util.JSON.decode(response.responseText)[0];
					this.getField('HTMRegister_PlannedHospDate').isAllowedToPlanDate = Ext.isEmpty(HTMRegister_id);
					this.getField('HTMRegister_PlannedHospDate').validate();
				} else {

				}
			}.createDelegate(this)
		})
	},

	loadForm: function(HTMRegister_id) {
		this.getLoadMask('Загрузка').show();
		var params = new Object();
		params.url = '/?c=HTMRegister&m=loadEditForm';
		params.params = new Object();
		params.params.HTMRegister_id = HTMRegister_id;
		params.callback = function(options, success, response) {
			this.getLoadMask('Загрузка').hide();
			if(success) {
				var data = Ext.util.JSON.decode(response.responseText)[0];

				var signed = data.HTMRegister_IsSigned == "2";

				this.editable = this.editable && !signed ? true : false;

				if(this.editable) {
					this.enableTab(data.HTMRegister_Stage);
					this.isAllowedToPlanDate(data.HTMRegister_Number);
					
					this.buttons[0].show();
					this.buttons[1].show();
					
				} else {
					this.buttons[0].hide();
					this.buttons[1].hide();
				}

				var HRPerson_Name = data.HRPerson_Name;
				var EvnPS_NumCard = data.EvnPS_NumCard;

				delete data.HRPerson_Name;
				delete data.HTMRegister_IsSigned;
				delete data.EvnPS_NumCard;

				this.getForm().setValues(data);

				if( !Ext.isEmpty(data.EvnPS_id) && !signed)
					this.loadEvnPSData(data.EvnPS_id);

				if(data.Diag_FirstId) {
					var diag_id1_combo = this.getField('Diag_FirstId');
					diag_id1_combo.getStore().load({
						callback: function() {
							diag_id1_combo.setValue(data.Diag_FirstId);
							diag_id1_combo.fireEvent('select',diag_id1_combo, diag_id1_combo.getStore().getAt(0), 0);
							this.fireEventChange('Diag_FirstId',data.Diag_FirstId);
						}.createDelegate(this),
						params: {
							where: "where Diag_id = " + data.Diag_FirstId
						}
					})
				}

				if(data.Diag_FifthId) {
					var diag_id5_combo = this.getField('Diag_FifthId');
					diag_id5_combo.getStore().load({
						callback: function() {
							diag_id5_combo.setValue(data.Diag_FifthId);
							diag_id5_combo.fireEvent('select', diag_id5_combo, diag_id5_combo.getStore().getAt(0), 0);
						},
						params: {
							where: "where Diag_id = " + data.Diag_FifthId
						}
					})
				}

				var Lpu_id = this.getValue('Lpu_id');
				this.loadStore('MedPersonal_FirstId', { MedPersonal_id: data.MedPersonal_FirstId, Lpu_id: Lpu_id });
				this.loadStore('MedPersonal_SecondId', { MedPersonal_id: data.MedPersonal_SecondId, Lpu_id: Lpu_id });
				this.loadStore('MedPersonal_ThirdId', { MedPersonal_id: data.MedPersonal_ThirdId, Lpu_id: Lpu_id });
				this.loadStore('MedPersonal_FourthId', { MedPersonal_id: data.MedPersonal_FourthId, Lpu_id: Lpu_id });
				this.loadStore('MedPersonal_FifthId', { MedPersonal_id: data.MedPersonal_FifthId, Lpu_id: Lpu_id });

				this.setRawValue('Person_id',HRPerson_Name);
				this.setRawValue('EvnPS_id',EvnPS_NumCard);

				this.fireEventChange('HTMWaitingReason_id');
				this.fireEventChange('HTMedicalCareClass_id');
				this.fireEventChange('HTMDecision_FirstId');

			} else {
				this.showMessage('Сообщение','Ошибка при загрузке');
			}
		}.createDelegate(this);
		Ext.Ajax.request(params);
	},

	loadEvnPSData: function(EvnPS_id) {
		Ext.Ajax.request({
			url: '/?c=HTMRegister&m=getEvnPSData',
			params: { EvnPS_id: EvnPS_id },
			callback: function(options, success, response) {
				if(success) {
					var data = Ext.util.JSON.decode(response.responseText)[0];
					var diag_name = data.Diag_FullName;
					var isDead = data.isDead;

					delete data.isDead;
					delete data.Diag_FullName;

					if(Ext.isEmpty(data.HTMRegister_OperDate))
						delete data.HTMRegister_OperDate;
					if(Ext.isEmpty(data.HTMedicalCareClass_id))
						delete data.HTMedicalCareClass_id;
					if(data.HTMedicalCareClass_id) {
						this.fireEventChange('HTMedicalCareClass_id', data.HTMedicalCareClass_id);
						this.fireEventChange('HTMDirectionResult_id', 1);
						if(isDead) {
							this.fireEventChange('HTMResult_id',5);
						}
					} else {
						if(isDead) {
							this.fireEventChange('HTMDirectionResult_id',3);
						}
					}
					this.getForm().setValues(data);

					if(data.Diag_FifthId) {
						var diag_id5_combo = this.getField('Diag_FifthId');
						diag_id5_combo.getStore().load({
							callback: function() {
								diag_id5_combo.setValue(data.Diag_FifthId);
								diag_id5_combo.fireEvent('select', diag_id5_combo, diag_id5_combo.getStore().getAt(0), 0);
							},
							params: {
								where: "where Diag_id = " + data.Diag_FifthId
							}
						})
					}

				} else {
					this.showMessage('Сообщение', 'Произошла ошибка при загрузке данных КВС')
				}
			}.createDelegate(this)
		})
	},

	initComponent: function() {

		var NextStageButton = Ext.extend(Ext.Button,{
			text: 'Перейти к следующему этапу',
			iconCls: 'ok16',
			handler: function() {
				if(!this.isValid()) return;
				this.enableTab( this.getValue('HTMRegister_Stage') + 1 );
			}.createDelegate(this)
		});

		this.NextStageButtons = [
			new NextStageButton({
				style: 'margin-left: 10px;'
			}),
			new NextStageButton({
				handler: function() {
					if(!this.isValid()) return;
					var nextStage = this.getField('HTMDecision_FirstId').isReject ? 6 : 2;
					this.enableTab(nextStage);
				}.createDelegate(this)
			}),
			new NextStageButton({
				handler: function() {
					if(!this.isValid()) return;
					var nextStage = this.getField('HTMDecision_SecondId').isReject ? 6 : 3;
					this.enableTab(nextStage);
				}.createDelegate(this)
			}),
			new NextStageButton(),
			new NextStageButton({
				handler: function() {
					if(!this.isValid()) return;
					var nextStage = this.getField('HTMDecision_FourthId').isReject ? 6 : 5;
					this.enableTab(nextStage);
				}.createDelegate(this)
			}),
			new NextStageButton()
		];

		// форма с табпанелью
		var centerPanel = new Ext.Panel({
			style: "border-left: 1px solid #99bbe8;",
			labelAlign: 'top',
			labelWidth: 300,
			columnWidth: 0.8,
			autoWidth: false,
			border: false,
			listeners: {
				resize: function(panel) {
					panel.setHeight(this.FormPanel.getInnerHeight());
				}.createDelegate(this)
			},
			items: [ this.TabPanel = new Ext.TabPanel({
				deferredRender: false,
				layoutOnTabChange: true,
				activeTab: 0,
				margins: '3 3 3 0', 
				border: false,
				defaults: {
					autoScroll: true,
					autoHeight: true,
					layout: 'form',
					cls: 'HTM-form'
				},
				items: [ {
					title: 'Паспортная часть',
					cls: '',
					border: false,
					items:[{
						layout: 'column',
						border: false,
						defaults: {
							border: true,
							xtype: 'fieldset',
							columnWidth: 0.5,
							cls: 'HTM-form',
							autoHeight: true,
							defaults: {
								hideTrigger: true
							}
						},
						items: [{
							hidden: true,
							defaults: { xtype: 'numberfield' },
							items: [{
								name: 'HTMRegister_id'
							}, {
								name: 'Register_id'
							}, {
								name: 'EvnDirectionHTM_id'
							}, {
								name: 'HTMRegister_Stage'
							}, {
								name: 'Register_PersonId'
							}, {
								name: 'Lpu_id'
							}, {
								name: 'HTMQueueType_id'
							}]
						},{
							title: 'Справочные сведения о пациенте',
							items: [ {
								xtype: 'fieldset',
								layout: 'column',
								autoHeight: true,
								border: false,
								columnWidth: 0.3,
								cls: 'HTM-fieldset',
								defaults: {
									xtype: 'panel',
									layout: 'form',
									columnWidth: 0.3,
									border: false,
									//style: 'margin: 0px 10px 0px 10px',
									defaults: {
										xtype: 'textfield',
										stage: this.noStage,
										disabled: true
									}
								},
								items: [{
									items: [ {
										fieldLabel: 'Фамилия',
										name: 'Person_SurName'
									}]
								}, {
									items: [{
										fieldLabel: 'Имя',
										name: 'Person_FirName'
									}]
								}, {
									items: [{
										fieldLabel: 'Отчество',
										name: 'Person_SecName'
									}]
								} ]
							}, {
								xtype: 'fieldset',
								layout: 'column',
								autoHeight: true,
								border: false,
								labelWidth: 100,
								cls: 'HTM-fieldset',
								defaults: {
									columnWidth: 0.3,
									xtype: 'panel',
									layout: 'form',
									//style: 'margin: 0px 10px 0px 10px',
									border: false,
									defaults: {
										width: 147,
										disabled: true,
										hideTrigger: true,
										stage: this.noStage
									}
								},
								items: [ {
									items: [ {
										fieldLabel: 'СНИЛС',
										xtype: 'textfield',
										name: 'Person_Snils'
									}]
								}, {
									items: [ {
										fieldLabel: 'Пол',
										hiddenName: 'Sex_id',
										xtype: 'swpersonsexcombo'
									}]
								}, {
									items: [ {
										fieldLabel: 'Дата рождения',
										name: 'Person_BirthDay',
										xtype: 'textfield'
									}]
								}]
							}, {
								fieldLabel: 'Местность',
								name: 'Address_Address',
								xtype: 'textfield',
								width: 400,
								disabled: true
							}, {
								fieldLabel: 'Категория льготы',
								hiddenName: 'PrivilegeType_id',
								xtype: 'swprivilegetypecombo',
								width: 200,
								disabled: true
							}, {
								fieldLabel: 'Занятость (социальная группа)',
								hiddenName: 'HTMSocGroup_id',
								xtype: 'swhtmsocgroupcombo',
								width: 400,
								disabled: true
							}, {
								fieldLabel: 'Наименование страховой медицинской организации',
								hiddenName: 'OrgSMO_id',
								xtype: 'sworgsmocombo',
								width: 400,
								disabled: true
							}, {
								fieldLabel: 'Номер полиса ОМС',
								name: 'Polis_Num',
								xtype: 'numberfield',
								width: 150,
								disabled: true
							}, {
								fieldLabel: 'Документ, удостоверяющий личность',
								hiddenName: 'DocumentType_id',
								xtype: 'swdocumenttypecombo',
								width: 400,
								disabled: true
							}, {
								xtype: 'fieldset',
								layout: 'column',
								autoHeight: true,
								border: false,
								labelWidth: 100,
								cls: 'HTM-fieldset',
								defaults: {
									columnWidth: 0.3,
									xtype: 'panel',
									layout: 'form',
									//style: 'margin: 0px 10px 0px 10px',
									border: false,
									width: 140,
									defaults: {
										xtype: 'textfield',
										disabled: true,
										stage: this.noStage
									}
								},
								items: [ {
									items: [ {
										fieldLabel: 'Серия документа',
										name: 'Document_Ser'
									}]
								}, {
									items: [ {
										fieldLabel: 'Номер документа',
										name: 'Document_Num'
									}]
								}, {
									items: [ {
										fieldLabel: 'Дата выдачи',
										name: 'Document_begDate'
									}]
								}]
							}, {
								xtype: 'sworgdepcombo',
								hiddenName: 'OrgDep_id',
								fieldLabel: langs('Кем выдан документ'),
								width: 400,
								disabled: true
							}, {
								fieldLabel: 'Адрес регистрации по месту жительства (пребывания) пациента',
								name: 'Person_Address',
								xtype: 'textfield',
								width: 400,
								disabled: true
							}, {
								fieldLabel: 'Контактный телефон',
								name: 'Person_Phone',
								xtype: 'textfield',
								width: 100,
								disabled: true
							}, {
								fieldLabel: 'Согласие на использование персональных данных',
								hiddenName: 'HTMRegister_IsAllowPersonData',
								xtype: 'swyesnocombo',
								hideTrigger: false,
								width: 100,
								allowBlank: false,
								stage: 0
							}]
						}, {
							title: 'Паспортная часть талона',
							defaults: {
								width: 250,
								stage: this.noStage
							},
							items: [{
								fieldLabel: '№ талона на оказание ВМП',
								name: 'HTMRegister_Number',
								xtype: 'numberfield',
								disabled: true,
								editable: false
							}, {
								fieldLabel: 'Наименование ОУЗ (МО-ОМС)',
								name: 'Org_Nick',
								xtype: 'textfield',
								disabled: true
							}, {
								fieldLabel: 'ОКПО ОУЗ (МО-ОМС)',
								name: 'Org_OKPO',
								xtype: 'numberfield',
								disabled: true
							}, {
								fieldLabel: 'ОКАТО ОУЗ',
								name: 'Org_OKATO',
								xtype: 'numberfield',
								disabled: true
							}, {
								fieldLabel: 'Почтовый индекс ОУЗ (МО-ОМС)',
								name: 'OrgAddress_Zip',
								xtype: 'textfield',
								disabled: true
							}, {
								fieldLabel: 'Почтовый адрес ОУЗ (МО-ОМС)',
								name: 'Org_Address',
								xtype: 'textfield',
								disabled: true,
								anchor: '95%'
							}, {
								fieldLabel: 'Адрес электронной почты ОУЗ (МО-ОМС)',
								name: 'Org_Email',
								xtype: 'textfield',
								disabled: true
							}, {
								fieldLabel: 'Дата оформления талона',
								name: 'EvnDirectionHTM_setDate',
								xtype: 'textfield',
								disabled: true
							}, {
								fieldLabel: langs('Обращение пациента за ВМП'),
								disabled: true,
								xtype: 'textfield',
								name: 'EvnDirectionHTM_IsHTM'
							}, {
								fieldLabel: 'Источник оказания ВМП',
								disabled: true,
								hideTrigger:true,
								hiddenName: 'HTMFinance_id',
								xtype: 'swcommonsprcombo',
								comboSubject: 'HTMFinance'
							}, {
								xtype: 'fieldset',
								layout: 'column',
								autoHeight: true,
								border: false,
								width: '100%',
								cls: 'HTM-fieldset',
								defaults: {
									xtype: 'panel',
									layout: 'form',
									columnWidth: 0.5,
									border: false,
									//style: 'margin: 0px 10px 0px 10px',
									defaults: {
										stage: this.noStage,
										disabled: true
									}
								},
								items: [{
									items: [{
										fieldLabel: 'Направление на ВМП',
										disabled: true,
										hideTrigger:true,
										hiddenName: 'HTMOrgDirect_id',
										xtype: 'swcommonsprcombo',
										comboSubject: 'HTMOrgDirect'
									}]
								},{
									items: [{
										fieldLabel: langs('Профиль'),
										xtype: 'swlpusectionprofilecombo',
										hiddenName: 'LpuSectionProfile_id'
									}]
								}]
							}, {
								fieldLabel: 'Комментарии',
								name: 'HTMRegister_Comment',
								hiddenName: 'HTMRegister_Comment',
								xtype: 'textarea',
								width: 500,
								maxLength: 500,
								height: '65px',
								disabled: false,
								stage: 0
							}]
						}]
					},
					this.NextStageButtons[0]
					]
				},{
					title: '1 этап (ОУЗ,МО-ОМС)',
					defaults: {
						stage: 1,
						allowBlank: false
					},
					items: [{
						fieldLabel: 'Код принятого решения (ОУЗ)',
						hiddenName: 'HTMDecision_FirstId',
						xtype: 'swcommonsprcombo',
						comboSubject: 'HTMDecision',
						showCodefield: false,
						width: 500,
						listWidth: 500,
						listeners: {
							expand: function() {
								this.store.filter('HTMDecision_Code',/A/,true,false);
							},
							change: function(combo,newValue,oldValue) {
								var rec = combo.getStore().getById(newValue);
								var code = Ext.isEmpty(rec) ? null : rec.get('HTMDecision_Code');
								var isReject = code == "A2";

								for(var i=2;i<=5;++i)
									this.getTab(i).setDisabled(isReject);

								this.setAllowBlank('Diag_FirstId',isReject);
								this.setAllowBlank('HTMedicalCareClass_id',isReject);
								this.setAllowBlank('HTMRegister_DocSentDate',isReject);
								this.setAllowBlank('HTMRegister_FirstDocReceiveDate',isReject);

								combo.isReject = isReject;

								if(code == 'A3')
									this.hideStageButtons();

								if(!isReject && code != 'A3')
									this.fireEventChange('HTMDecision_SecondId');
							}.createDelegate(this),
							select: function(combo,rec,idx) {
								var isReject = Ext.isEmpty(rec) ? false : rec.get('HTMDecision_Code') == "A2";
								var rejectField = this.getField('HTMRejectionReason_id');
								//rejectField.allowBlank = !isReject;
								rejectField.setValue(isReject ? 1 : null);
								this.enableTab(combo.stage);
								this.fireEventChange('HTMResult_id',null);

								var isWait = Ext.isEmpty(rec) ? false : rec.get('HTMDecision_Code') == 'A3';
								if(isWait)
									this.hideStageButtons();
							}.createDelegate(this)
						}
					}, {
						fieldLabel: 'Дата принятия решения (ОУЗ)',
						name: 'HTMRegister_FirstDecisionDate',
						hiddenName: 'HTMRegister_FirstDecisionDate',
						xtype: 'swdatefield'
					}, {
						fieldLabel: 'Диагноз (ОУЗ)',
						hiddenName: 'Diag_FirstId',
						width: 500,
						xtype: 'swdiagcombo',
						listeners: {
							change: function(combo,newValue,oldValue) {
								this.loadHTMedicalCareClassCombo();
							}.createDelegate(this)
						}
					}, {
						fieldLabel: 'Метод ВМП',
						hiddenName: 'HTMedicalCareClass_id',
						xtype: 'swhtmedicalcareclasslocalcombo',
						width: 500,
						listeners: {
							change: function(combo,newValue,oldValue) {
								var rec = combo.getStore().getById(newValue);
								var careName = Ext.isEmpty(rec) ? null : rec.get('HTMedicalCareType_Name');
								this.setValue('HTMedicalCareType_Name1', careName);
								this.setValue('HTMedicalCareType_Name2', careName);
								this.setValue('HTMedicalCareType_Name5', careName);
								//this.fireEventChange('HTMedicalCareClass_id2', newValue);
								//this.fireEventChange('HTMedicalCareClass_id5', newValue);
								this.setRawValue('HTMedicalCareClass_id2', combo.getRawValue());
								this.setRawValue('HTMedicalCareClass_id5', combo.getRawValue());
							}.createDelegate(this)
						}
					}, {
						fieldLabel: 'Наименование вида ВМП',
						name: 'HTMedicalCareType_Name1',
						xtype: 'textarea',
						width: 500,
						height: 70,
						stage: this.noStage,
						disabled: true
					}, {
						fieldLabel: 'Дата направления документов в МО ВМП (ОУЗ)',
						name: 'HTMRegister_DocSentDate',
						xtype: 'swdatefield'
					}, {
						fieldLabel: 'Дата получения документов (МО-ОМС)',
						name: 'HTMRegister_FirstDocReceiveDate',
						xtype: 'swdatefield'
					}, {
						fieldLabel: 'ФИО должностного лица (ОУЗ, МО-ОМС)',
						hiddenName: 'MedPersonal_FirstId',
						xtype:'swmedpersonalremotecombo'
					}, {
						fieldLabel: 'Комментарии',
						name: 'HTMRegister_FirstComment',
						xtype: 'textarea',
						allowBlank: true,
						width: 500,
						maxLength: 500,
						height: 70
					}, this.NextStageButtons[1]
					]
				}, {
					title: '2 этап (МО-ВМП)',
					defaults: {
						stage: 2,
						allowBlank: false
					},
					items: [{
						fieldLabel: 'Дата получения документов',
						name: 'HTMRegister_SecondDocReceiveDate',
						xtype: 'swdatefield'
					}, {
						fieldLabel: 'Дата оформления документов МО-ВМП',
						name: 'HTMRegister_DocExecDate',
						xtype: 'swdatefield'
					}, {
						fieldLabel: 'Код принятого решения',
						hiddenName: 'HTMDecision_SecondId',
						xtype: 'swcommonsprcombo',
						comboSubject: 'HTMDecision',
						showCodefield: false,
						width: 500,
						listWidth: 500,
						listeners: {
							expand: function() {
								this.store.filter('HTMDecision_Code',/B/,true,false);
							},
							change: function(combo,newValue,oldValue) {
								var rec = combo.getStore().getById(newValue);
								var code = Ext.isEmpty(rec) ? null : rec.get('HTMDecision_Code');
								var isReject = ["B2","B5"].includes(code);
								
								for(var i=3; i<=5; ++i)
									this.getTab(i).setDisabled(isReject);

								this.setAllowBlank('HTMRegister_PlannedHospDate',isReject);
								this.setAllowBlank('HTMRegister_NotifyDate', isReject);
								this.setAllowBlank('HTMNotificationType_id', isReject);

								combo.isReject = isReject;

								if(!isReject && code != 'B5') {
									this.fireEventChange('HTMDecision_FourthId');
									this.fireEventChange('HTMRegister_IsTravelTicket');
									this.fireEventChange('HTMRegister_IsNeedAccompany');
								}

								if(code == 'B3') {
									this.hideStageButtons();
								}
							}.createDelegate(this),
							select: function(combo,rec,idx) {
								var code = Ext.isEmpty(rec) ? false : rec.get('HTMDecision_Code');
								var rejectField = this.getField('HTMRejectionReason_id');
								//rejectField.allowBlank = !['B2','B5'].includes(code);
								if(code == 'B2')
									rejectField.setValue(2);
								else if(code == 'B5')
									rejectField.setValue(3);
								else
									rejectField.setValue(null);
								this.fireEventChange('HTMResult_id',null);
								this.enableTab(combo.stage);

								if(code == 'B3')
									this.hideStageButtons();
							}.createDelegate(this)
						}
					}, {
						fieldLabel: 'Дата принятия решения',
						name: 'HTMRegister_SecondDecisionDate',
						xtype: 'swdatefield'
					}, {
						fieldLabel: 'Метод ВМП',
						hiddenName: 'HTMedicalCareClass_id2',
						xtype: 'swhtmedicalcareclasslocalcombo',
						width: 500,
						stage: this.noStage
					}, {
						fieldLabel: 'Наименование вида ВМП',
						name: 'HTMedicalCareType_Name2',
						xtype: 'textarea',
						width: 500,
						height: 70,
						stage: this.noStage,
						disabled: true
					}, {
						fieldLabel: 'Дата планируемой госпитализации',
						name: 'HTMRegister_PlannedHospDate',
						xtype: 'swdatefield',
						invalidText: langs('У данного Талона невозможно запланировать дату госпитализации, т.к. есть Талоны на оказание ВМП с меньшим номером и непомещённые в ожидание по причине с незапланированной датой госпитализации'),
						validator: function() {
							var planDate = this.getField('HTMRegister_PlannedHospDate');
							var isReject = !Ext.isEmpty(this.getValue('HTMRejectionReason_id'));
							var isResult = !Ext.isEmpty( this.getValue('HTMResult_id') );
							if(planDate.isAllowedToPlanDate || isReject || isResult)
								return true;
							return false;
						}.createDelegate(this),
						listeners: {

							change: function(field,newValue,oldValue) {
								this.setValue('HTMRegister_PlannedHospDate3',newValue);
							}.createDelegate(this)
						}
					}, {
						fieldLabel: 'Дата уведомления пациента о дате госпитализации',
						name: 'HTMRegister_NotifyDate',
						xtype: 'swdatefield'
					}, {
						fieldLabel: 'Способ уведомления',
						hiddenName: 'HTMNotificationType_id',
						xtype: 'swcommonsprcombo',
						comboSubject: 'HTMNotificationType',
						listWidth: 175
					}, {
						fieldLabel: 'ФИО должностного лица (ОУЗ, МО-ОМС)',
						hiddenName: 'MedPersonal_SecondId',
						xtype: 'swmedpersonalremotecombo'
					}, {
						fieldLabel: 'Комментарии',
						name: 'HTMRegister_SecondComment',
						xtype: 'textarea',
						allowBlank: true,
						width: 500,
						maxLength: 500,
						height: 70
					}, this.NextStageButtons[2]
					]
				}, {
					title: '3 этап (ОУЗ)',
					defaults: {
						stage: 3,
						allowBlank: false
					},
					items: [{
						fieldLabel: 'Талоны на проезд предоставляются',
						hiddenName: 'HTMRegister_IsTravelTicket',
						xtype: 'swcommonsprcombo',
						comboSubject: 'YesNo',
						width: 70,
						listWidth: 70,
						listeners: {
							change: function(combo,newValue,oldValue) {
								var ticketDate = this.getField('HTMRegister_TicketIssueDate');
								ticketDate.setDisabled(!this.editable || newValue != 2 || combo.disabled);
								if(newValue != 2)
									ticketDate.setValue(null);
							}.createDelegate(this)
						}
					}, {
						fieldLabel: 'Дата выдачи талонов',
						name: 'HTMRegister_TicketIssueDate',
						xtype: 'swdatefield',
						stage: this.noStage

					},{
						fieldLabel: 'Нуждается в сопровождении',
						hiddenName: 'HTMRegister_IsNeedAccompany',
						xtype: 'swcommonsprcombo',
						width: 70,
						listWidth: 70,
						comboSubject: 'YesNo',
						listeners: {
							change: function(combo,newValue,oldValue) {
								var personField = this.getField('Person_id');
								personField.setDisabled(!this.editable || newValue != 2 || combo.disabled);
								if( newValue != 2 )
									personField.setValue(null);
							}.createDelegate(this)
						}
					},{
						fieldLabel: 'ФИО сопровождающего лица',
						hiddenName: 'Person_id',
						xtype: 'swpersoncomboex',
						stage: this.noStage,
						width: 500,
						onTriggerClick: function() {
							var combo = this.getField('Person_id');
							if (combo.disabled) return false;
							getWnd('swPersonSearchWindow').show({
								onHide: function() {
									combo.focus(false);
								},
								onSelect: function(personData) {
									if(personData.Person_IsDead == "true") {
										this.showMessage('Сообщение','Невозможно выбрать умершего человека');
										return;
									}
									var store = combo.getStore();
									combo.setValue(personData[combo.valueField]);
									combo.hiddenValue = personData[combo.valueField];
									combo.setRawValue(
										personData.PersonSurName_SurName.trim() + " " +
										personData.PersonFirName_FirName.trim() + " " +
										personData.PersonSecName_SecName.trim()
										);
									getWnd('swPersonSearchWindow').hide();
									combo.onSelectPerson(personData);
								}.createDelegate(this)
							});
						}.createDelegate(this)
					},{
						fieldLabel: 'Дата планируемой госпитализации',
						name: 'HTMRegister_PlannedHospDate3',
						stage: this.noStage,
						xtype: 'swdatefield'
					},{
						fieldLabel: 'ФИО должностного лица (ОУЗ, МО-ОМС)',
						hiddenName: 'MedPersonal_ThirdId',
						xtype: 'swmedpersonalremotecombo'
					}, {
						fieldLabel: 'Комментарии',
						name: 'HTMRegister_ThirdComment',
						xtype: 'textarea',
						allowBlank: true,
						width: 500,
						maxLength: 500,
						height: '70px',
						disabled: false
					}, this.NextStageButtons[3]]
				}, {
					title: '4 этап (МО-ВМП)',
					defaults: {
						stage: 4,
						allowBlank: false
					},
					items: [{
						fieldLabel: 'Код принятого решения',
						hiddenName: 'HTMDecision_FourthId',
						xtype: 'swcommonsprcombo',
						showCodefield: false,
						comboSubject: 'HTMDecision',
						width: 500,
						listWidth: 500,
						listeners: {
							expand: function() {
								this.store.filter('HTMDecision_Code',/D/,true,false);
							},
							change: function(combo, newValue, oldValue) {
								var rec = combo.getStore().getById(newValue);
								var code = Ext.isEmpty(rec) ? null : rec.get('HTMDecision_Code');
								var isReject = ["D2","D3"].includes(code);
								this.setDisabled('EvnPS_id',!this.editable || code != "D1" || combo.disabled);
								if( isReject )
									this.fireEventChange('EvnPS_id',null);

								this.setAllowBlank('HTMRegister_FourthComment',!isReject);

								this.getTab(5).setDisabled(isReject);

								this.fireEventChange('HTMResult_id');
								this.fireEventChange('HTMDirectionResult_id');

								if(code == "D2") {
									this.hideStageButtons(combo.stage);
								}
								this.getField('HTMRejectionReason_id').stage = (code == "D3"? 6 : this.noStage);

								combo.isReject = isReject;
							}.createDelegate(this),
							select: function(combo,rec,idx) {
								var code = Ext.isEmpty(rec)? false : rec.get('HTMDecision_Code');

								var rejectField = this.getField('HTMRejectionReason_id');
								//rejectField.allowBlank = code != "D3";
								rejectField.setValue(null);
								this.fireEventChange('HTMResult_id',null);
								this.enableTab(combo.stage);
								if(code == "D2") {
									this.hideStageButtons(combo.stage);
								}
							}.createDelegate(this)
						}
					},{
						fieldLabel: '№ КВС',
						hiddenName: 'EvnPS_id',
						xtype: 'swbaseremotecombo',
						trigger1Class: 'x-form-search-trigger',
						triggerAction: 'all',
						anchor: '15%',
						stage: this.noStage,
						editable: false,
						valueField: 'EvnPS_id',
						displayField: 'EvnPS_NumCard',
						store: new Ext.data.JsonStore({
							autoLoad: true,
							data: [
							],
							fields: [
								{ name: 'EvnPS_id', type: 'int'},
								{ name: 'EvnPS_NumCard', type: 'int'}
							],
							key: 'EvnPS_id'
						}),
						onTrigger1Click: function() {
							var combo = this.getField('EvnPS_id');

							if(combo.disabled) return false;

							getWnd('swEvnPLEvnPSSearchWindow').show({
								Person_id: this.getValue('Register_PersonId'),
								Lpu_id: this.getValue('Lpu_id'),
								EvnClass_SysNick: 'EvnPS',
								onHide: function() {
									combo.focus(false);
								},
								onSelect: function(persData) {
									if(persData.EvnClass_Name != "КВС") {
										this.showMessage('Сообщение','Выберите КВС');
										return;
									}
									combo.setValue(persData.Evn_id);
									combo.setRawValue(persData.Evn_NumCard);
									getWnd('swEvnPLEvnPSSearchWindow').hide();
									this.loadEvnPSData(persData.Evn_id);
								}.createDelegate(this)
							});
						}.createDelegate(this),
						onTrigger2Click: function() {
							this.fireEventChange('EvnPS_id',null);
						}.createDelegate(this),
						listeners: {
							change: function(combo,newValue,oldValue) {
								if(Ext.isEmpty(newValue)) {
									this.setValue('HTMRegister_DisDate',null);
									this.setValue('HTMRegister_ApplicationDate',null);
									this.setValue('HTMDirectionResult_id',null);
									this.fireEventChange('HTMResult_id',null);
								}
							}.createDelegate(this)
						}
					}, {
						fieldLabel: 'Дата обращения пациента в МО-ВМП',
						name: 'HTMRegister_ApplicationDate',
						xtype: 'swdatefield',
						stage: this.noStage
					},{
						fieldLabel: 'ФИО должностного лица (ОУЗ, МО-ОМС)',
						hiddenName: 'MedPersonal_FourthId',
						xtype: 'swmedpersonalremotecombo'
					},{
						fieldLabel: 'Комментарии',
						name: 'HTMRegister_FourthComment',
						xtype: 'textarea',
						allowBlank: true,
						width: 500,
						maxLength: 500,
						height: '70px',
						disabled: false
					}, this.NextStageButtons[4]]
				}, {
					title: '5 этап (МО-ВМП)',
					defaults: {
						stage: 5,
						allowBlank: false
					},
					items: [{
						fieldLabel: 'Дата выписки пациента из МО-ВМП',
						name: 'HTMRegister_DisDate',
						xtype: 'swdatefield',
						stage: this.noStage
					},{
						fieldLabel: 'Результат направления на ВМП',
						hiddenName: 'HTMDirectionResult_id',
						xtype: 'swcommonsprcombo',
						comboSubject: 'HTMDirectionResult',
						width: 230,
						listWidth: 230,
						listeners: {
							change: function(combo,newValue,oldValue) {
								var newValue = parseInt(newValue);
								var disabled = combo.disabled || [2,3].includes(newValue) || !this.editable;
								this.setAllowBlank('HTMRegister_OperDate',disabled);
								this.setAllowBlank('HTMResult_id',disabled);
								this.setAllowBlank('HTMRecomendation_id',disabled);
							}.createDelegate(this),

							select: function(combo,rec,idx) {
								var value = parseInt(combo.getValue());
								switch(value) {
									case 1: //ВМП оказана
										this.setValue('HTMRejectionReason_id',null);
										break;

									case 2: //Отказ пациента от ВМП
									case 3: //Летальный исход до оказания ВМП
										this.setValue('HTMRejectionReason_id', value == 2 ? 4 : 5);
										this.setValue('HTMRegister_OperDate',null);
										this.setValue('HTMRecomendation_id',null);
										this.fireEventChange('HTMResult_id',null); //сотрет значение с 6 этапа
										break;
								}
							}.createDelegate(this)
						}
					},{
						fieldLabel: 'Диагноз при выписке',
						hiddenName: 'Diag_FifthId',
						width: 500,
						xtype: 'swdiagcombo'
					},{
						fieldLabel: 'Метод ВМП',
						hiddenName: 'HTMedicalCareClass_id5',
						xtype: 'swhtmedicalcareclasslocalcombo',
						width: 500,
						stage: this.noStage
					},{
						fieldLabel: 'Наименование вида ВМП',
						name: 'HTMedicalCareType_Name5',
						xtype: 'textarea',
						width: 500,
						height: 70,
						stage: this.noStage,
						disabled: true
					},{
						fieldLabel: 'Дата проведения оперативного вмешательства',
						name: 'HTMRegister_OperDate',
						xtype: 'swdatefield',
						allowBlank: true
					},{
						fieldLabel: 'Результат оказания ВМП',
						hiddenName: 'HTMResult_id',
						xtype: 'swcommonsprcombo',
						comboSubject: 'HTMResult',
						listWidth: 175,
						listeners: {
							change: function(combo,newValue,oldValue) {
								this.setValue('HTMResult_id6',newValue);
								//Если летальный исход
								this.setDisabled('HTMRecomendation_id',combo.disabled || !this.editable || newValue == 5  ? true : false);
							}.createDelegate(this)
						}
					},{
						fieldLabel: 'Рекомендовано',
						hiddenName: 'HTMRecomendation_id',
						xtype: 'swcommonsprcombo',
						comboSubject: 'HTMRecomendation',
						stage: this.noStage,
						listWidth: 300
					},{
						fieldLabel: 'ФИО должностного лица (ОУЗ, МО-ОМС)',
						hiddenName: 'MedPersonal_FifthId',
						xtype: 'swmedpersonalremotecombo'
					},{
						fieldLabel: 'Комментарии',
						name: 'HTMRegister_FifthComment',
						xtype: 'textarea',
						allowBlank: true,
						width: 500,
						maxLength: 500,
						height: '70px',
						disabled: false
					}, this.NextStageButtons[5]]
				}, {
					title: '6 этап. Заключение',
					defaults: {
						stage: 6
					},
					items: [{
						fieldLabel: 'Результат оказания ВМП',
						hiddenName: 'HTMResult_id6',
						xtype: 'swcommonsprcombo',
						comboSubject: 'HTMResult',
						width: 150,
						listWidth: 150,
						stage: this.noStage
					},{
						fieldLabel: 'Отказано',
						hiddenName: 'HTMRejectionReason_id',
						xtype: 'swcommonsprcombo',
						comboSubject: 'HTMRejectionReason',
						width: 500,
						listWidth: 600,
						allowBlank: false,
						stage: this.noStage
					}]
				}]
			})]
		});

		// левая панелька с талонами
		var leftPanel = new Ext.Panel({
			title: 'Очередь',
			labelAlign: 'top',
			border: false,
			columnWidth: 0.2,
			width: 300,
			listeners: {
				resize: function(panel) {
					panel.setHeight(this.FormPanel.getInnerHeight());
				}.createDelegate(this)
			},
			items: [{
				//title: 'Статус очереди',
				xtype: 'fieldset',
				border: false,
				autoHeight: true,
				items: [{
					fieldLabel: 'Номер в очереди',
					name: 'QueueNumber',
					xtype: 'textfield',
					stage: this.noStage
				},{
					fieldLabel: 'Ожидание по причине',
					hiddenName: 'HTMWaitingReason_id',
					xtype: 'swcommonsprcombo',
					comboSubject: 'HTMWaitingReason',
					allowBlank: true,
					listWidth: 300,
					listeners: {
						change: function(combo,newValue) {
							this.setAllowBlank('HTMRegister_WaitBegDate', Ext.isEmpty(newValue));
						}.createDelegate(this)
					}
				},{
					fieldLabel: 'Дата начала ожидания',
					name: 'HTMRegister_WaitBegDate',
					xtype: 'swdatefield',
					maxValue: new Date(),
					onChange: function(field, newValue, oldValue) {
						this.getField('HTMRegister_WaitEndDate').validate();
					}.createDelegate(this)
				},{
					fieldLabel: 'Дата окончания ожидания',
					name: 'HTMRegister_WaitEndDate',
					xtype: 'swdatefield',
					validator: function() {
						var begDate = this.getValue('HTMRegister_WaitBegDate'),
							endDate = this.getValue('HTMRegister_WaitEndDate');
						if(!begDate || !endDate) return true;
						if(begDate > endDate) return false;
						return true;
					}.createDelegate(this)
				}]
			},
			this.ViewFrame = new sw.Promed.ViewFrame({
				title: 'Талоны',
				autoLoadData: false,
				contextmenu: false,
				toolbar: false,
				border: false,
				paging: false,
				dataUrl: '/?c=HTMRegister&m=loadGrid',
				pageSize: 100,
				stringfields: [
					{ name: 'HTMRegister_id', type: 'int',sort: true, direction: 'DESC', hidden: true },
					{ name: 'HTMRegister_Number', type: 'int', width: 100, header: '№ Талона'},
					{ name: 'LpuSectionProfile_Name', type: 'string', width: 195, header: 'Профиль' },
					{ name: 'QueueNumber', type: 'string', hidden: true}
				]
			})]
		});

		//Всё содержимое
		this.FormPanel = new Ext.form.FormPanel({
			layout: 'column',
			listeners: {
				'beforeaction': function() {
					this.getLoadMask('Загрузка').show();
				}.createDelegate(this),

				'actioncomplete': function(form,action) {
					this.getLoadMask().hide();
				}.createDelegate(this),

				'actionfailed': function() {
					this.getLoadMask().hide();
				}.createDelegate(this)
			},
			items: [
				leftPanel, centerPanel
			]
		});

		Ext.apply(this, {
			layout: 'fit',
			items: [ this.FormPanel ]});

		sw.Promed.swHTMRegisterEditWindow.superclass.initComponent.apply(this, arguments);

	},

	showMessage: function(title, message, fn) {
		if ( !fn ) fn = function(){};

		Ext.MessageBox.show({
			buttons: Ext.Msg.OK,
			fn: fn,
			icon: Ext.Msg.WARNING,
			msg: message,
			title: title
		});
	},

	delete: function(HTMRegister_id) {
		Ext.Ajax.request({
			url: '/?c=Utils&m=ObjectRecordDelete',
			params: {
				object: 'HTMRegister',
				id: 'HTMRegister_id',
				scheme: 'r2'
			}
		})
	},

	loadHTMedicalCareClassCombo: function() {
		var combo = this.getField('HTMedicalCareClass_id');

		var diag_id = this.getValue('Diag_FirstId');
		if( Ext.isEmpty(diag_id) ) {
			return;
		}

		var params = new Object();
		params.Diag_ids = Ext.util.JSON.encode([diag_id]);

		combo.getStore().load({params: params});
	}
});