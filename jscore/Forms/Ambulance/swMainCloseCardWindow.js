/**
* swMainCloseCardWindow Базовая карта закрытия вызова
* Внимание! - карта сделана для определения родительского класса для 110 и поточного ввода
* Все региональные ньюансы переопределяем в дочерние элементы
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @autor		Dyomin Dmitry
* @copyright    Copyright (c) 2012 Swan Ltd.
* @version      19.10.2016
*/

sw.Promed.swMainCloseCardWindow = Ext.extend(sw.Promed.BaseForm,{
	objectName: 'swMainCloseCardWindow',
	objectSrc: '/jscore/Forms/Ambulance/swMainCloseCardWindow.js',
	cls: 'swMainCloseCardWindow',
	modal: true,
	maximized: true,
	resizable: true,
	plain: false,
	onCancel: Ext.emptyFn,
	callback: Ext.emptyFn,
	DocumentUc_id: null,
	DrugGrid: null,
	delDocsView: false,
	initActions: function(){

		var me = this;

		me.panelNumber = 0;

		me.initRadiosAndChecks();

		me.buttons = [
			{
				handler: function() {
					me.doSave();
				},
				iconCls: 'save16',
				onTabAction: function() {
					me.buttons[me.buttons.length - 1].focus();
				},
				text: BTN_FRMSAVE
			},
			{
				handler: function() {
                    var withPrint = true;
					me.doSave(withPrint);
				},
				iconCls: 'save16',
				onTabAction: function() {
					me.buttons[me.buttons.length - 2].focus();
				},
				text: BTN_FRMSAVE + ' и Распечатать'
			},
			{
				text: '-'
			},
				HelpButton(me, -1),
			{
				handler: function() {
					me.hide();
					me.closeWindow();
				},
				iconCls: 'cancel16',
				onShiftTabAction: function() {
					if ( me.action != 'view' ) {
						me.buttons[0].focus();
					}
				},
				onTabAction: function() {
					if ( !me.FormPanel.getForm().findField('Person_Surname').disabled ) {
						me.FormPanel.getForm().findField('Person_Surname').focus(true);
					}
				},
				text: BTN_FRMCANCEL
			}
		];

		me.tabPanel = new Ext.TabPanel({
			name: 'CMPCLOSE_TabPanel',
			activeTab: 0,
			deferredRender: false,
			cls: 'x-tab-panel-autoscroll',
			defaults: {border: false},
			items: [
				{
					title: '<b>1.</b> Информация о вызове',
					itemId: 'CMPCLOSE_TabPanel_FirstShowedTab',
					items: me.getPersonFields(),
					autoHeight: true
				},
				{
					title: '<b>2.</b> Повод к вызову',
					items: me.getPovodFields(),
					autoHeight: true
				},
				{
					title: '<b>3.</b> Жалобы и объективные данные',
					name: 'patientDataFieldset',
					items: me.getJalobFields(),
					autoHeight: true
				},
				{
					title: '<b>4.</b> Диагноз',
					name: 'diagTabPanel',
					items: me.getDiagnozFields(),
					autoHeight: true
				},
				{
					title: '<b>5.</b> Манипуляции',
					name: 'medicalActionsTab',
					items: me.getProcedureFields(),
					autoHeight: true
				},
				{
					title: '<b>6.</b> Результат',
					items: me.getResultFields(),
					autoHeight: true
				},
				{
					title: '<b>7.</b> Использование медикаментов',
					items: [
						{
							html: 'Для ввода медикаментов необходимо заполнить поле "Станция (подстанция), отделение"',
							style: 'margin-top: 10px; text-align: center; font-size: 16px;',
							height: 700
						}
						// me.getDrugFields()
						//me.getEvnDrugFields()
					]
				},
				{
					title: '<b>8.</b> Экспертная оценка',
					items: me.getExpertResponseFields()
				}
			]
		});

		me.hiddenItems = [
			{
				name: 'accessType',
				value: '',
				xtype: 'hidden'
			},
			{
				name: 'AgeType_id2',
				value: '',
				xtype: 'hidden'
			},
			{
				name: 'SocStatusNick',
				value: '',
				xtype: 'hidden'
			},
			{
				name: 'CmpCallCard_id',
				value: 0,
				xtype: 'hidden'
			},
			{
				name: 'MedPersonal_id',
				value: 0,
				xtype: 'hidden'
			},
			{
				name: 'EmergencyTeam_HeadShift2_id',
				value: 0,
				xtype: 'hidden'
			},
			{
				name: 'MedPersonalAssistant_id',
				value: 0,
				xtype: 'hidden'
			},
			{
				name: 'MedPersonalDriver_id',
				value: 0,
				xtype: 'hidden'
			},
			{
				name: 'CmpReason_id',
				value: 0,
				xtype: 'hidden'
			},
			{
				name: 'ARMType',
				value: '',
				xtype: 'hidden'
			},
			{
				name: 'Person_id',
				value: '',
				xtype: 'hidden'
			},
			{
				name: 'Person_IsUnknown',
				value: '',
				xtype: 'hidden'
			},
			{
				name: 'PersonFields_IsDirty',
				value: false,
				xtype: 'hidden'
			},
			{
				name: 'Person_deadDT',
				value: '',
				xtype: 'hidden'
			},
			{
				name: 'CmpCloseCard_id',
				value: '',
				xtype: 'hidden'
			},
			{
				name: 'CmpCloseCard_Street',
				value: '',
				xtype: 'hidden'
			},
			{
				name: 'Lpu_ppdid',
				value: '',
				xtype: 'hidden'
			},
			/*{
				name: 'LpuBuilding_IsWithoutBalance',
				xtype: 'hidden',
				hiddenId: 'LpuBuilding_IsWithoutBalance'
			},*/
			{
				name: 'CmpCallCard_IsPaid',
				xtype: 'hidden'
			},
			{
				name: 'CmpCallCard_IndexRep',
				xtype: 'hidden'
			},
			{
				name: 'CmpCallCard_IndexRepInReg',
				xtype: 'hidden'
			}
		];

		var flds = [];

		for(var i = 0 ; i < me.hiddenItems.length; i++){
			flds.push({'name': me.hiddenItems[i].name});
		}

		me.tabPanel.findBy(function(a,b,c){
			var name = a.hiddenName || a.name;
			if(name){
				flds.push({'name': name});
			}
		});

		me.FormPanel = new Ext.form.FormPanel({
			bodyBorder: false,
			bodyStyle: 'padding: 5px',
			border: false,
			frame: true,
			labelAlign: 'right',
			labelWidth: 220,
			layout: 'fit',
			region: 'center',
			reader: new Ext.data.JsonReader( {success: Ext.emptyFn}, flds ),
			//url: '/?c=CmpCallCard&m=saveCmpCloseCard110',
			items: [
				{xtype: 'container', items: me.hiddenItems, autoEl: {}, hidden: true},
				me.tabPanel
			]
		});
		
		//if(me.UslugaPanel) me.initUslugaElements();
		//me.initUslugaElements();
	},

	initComponent: function(){

		this.initActions();

		Ext.apply(this, {
			items: [
				this.FormPanel
			],
			layout: 'border'
		});

		sw.Promed.swMainCloseCardWindow.superclass.initComponent.apply(this, arguments);
	},

	show: function(){

		sw.Promed.swMainCloseCardWindow.superclass.show.apply(this, arguments);

		if ( !arguments[0] || !arguments[0].formParams ) {
			sw.swMsg.alert('Сообщение', 'Неверные параметры', function() {this.hide();}.createDelegate(this) );
			return false;
		}

		var me = this,
			base_form = me.FormPanel.getForm();

		base_form.reset();
/*
		if(me.uslugaWrapper){
			for (var i = 0; i < me.uslugaWrapper.items.length; i++) {
				var cmp = me.uslugaWrapper.items.items[i].items.items[0];
				if ( typeof cmp.setRawValue == 'function' ) {
					cmp.setRawValue('');
					cmp.removeClass('dirtyNumberfield');
				}
			}
		}
*/
        me.action = null;
		me.searchWindow = null;

		me.formStatus = 'edit';
		me.onHide = Ext.emptyFn;

		me.showLoadMask(LOAD_WAIT);

/*
		if(me.UslugaViewFrame){
			//@to do загрузка услуг
			me.UslugaViewFrame.getGrid().getStore().removeAll();
		}
*/
		base_form.setValues(arguments[0].formParams);

		if ( arguments[0].callback ) {
			me.callback = arguments[0].callback;
		}
		if ( arguments[0].onHide ) {
			me.onHide = arguments[0].onHide;
		}
		if ( arguments[0].action ) {
			me.action = arguments[0].action;
		}
		if ( arguments[0].AutoBrigadeStatusChange ){
			me.AutoBrigadeStatusChange = arguments[0].AutoBrigadeStatusChange;
		}
		if ( arguments[0].searchWindow ) {
			me.searchWindow = arguments[0].searchWindow;
		}
		if (arguments[0].delDocsView) {
			this.delDocsView = arguments[0].delDocsView;
		}
		//очистка экспертных оценок
		if(me.ExpertResponseFields){
			var groups = me.ExpertResponseFields.findByType('checkboxgroup'),
				//id раздела : группа доступа
				accesses = {
					1: 'smpheaddoctor',//Старший врач
					2: 'smpnachmed',//СМП Начмед
					3: 'smpnachmed',//СМП Начмед
					4: 'zmk' //АРМ центра медицины катастроф».
				};
			for (var i = 0; i < groups.length; i++) {
				groups[i].setDisabled(!isUserGroup(accesses[groups[i].ExpertResponseType_id]));
				groups[i].reset()
			}
		}
		var params = {};

		if (arguments[0].formParams.CmpCloseCard_id) {
			params.CmpCloseCard_id = arguments[0].formParams.CmpCloseCard_id;
		} else if (arguments[0].formParams.CmpCallCard_id) {
			params.CmpCallCard_id = arguments[0].formParams.CmpCallCard_id
		} else {
			var cmp_call_card_id = base_form.findField('CmpCallCard_id').getValue();
			if ( !cmp_call_card_id ) {
				me.hideLoadMask();
				sw.swMsg.alert('Сообщение', 'Не передан идентификатор', function(){me.hide();});
				return false;
			}
			params.CmpCallCard_id = cmp_call_card_id;
		}
		params.delDocsView = this.delDocsView ? 1 : 0;
/*
		if (!Ext.isEmpty(params.CmpCloseCard_id)){
			var param_str = 'CmpCloseCard_id=' + params.CmpCloseCard_id;
			syncCheckEvnInRegistry(me, param_str);
		};
*/
		me.ARMType = base_form.findField('ARMType').getValue();

		//действия при редактировании/добавлении/просмотре
		this.formData = null;
		if (arguments[0].formData) {
			this.formData = arguments[0].formData;
			base_form.setValues(arguments[0].formData);
			me.setFieldsValuesOnLoad(arguments[0].formData);
			//me.setTitle(WND_AMB_CCCEFCLOSE);
			me.hideLoadMask();
			me.action = arguments[0].formData.action;

			if (!Ext.isEmpty(base_form.findField('Person_id').getValue())) {
				base_form.findField('Fam').disable();
				base_form.findField('Name').disable();
				base_form.findField('Middle').disable();
				base_form.findField('Sex_id').disable();

				var documentNumField = base_form.findField('DocumentNum'),
					polisSerField = base_form.findField('CmpCloseCard_PolisSer'),
					polisNumField = base_form.findField('CmpCloseCard_PolisNum'),
					polisEdnumField = base_form.findField('CmpCloseCard_PolisEdNum'),
					personSnilsField = base_form.findField('Person_Snils');

				base_form.findField('Work').disable();
				base_form.findField('Age').disable();

				if(documentNumField){
					documentNumField.disable();
				};

				if(polisSerField){
					polisSerField.disable();
				};

				if(polisNumField){
					polisNumField.disable();
				};

				if(polisEdnumField){
					polisEdnumField.disable();
				};

				if(personSnilsField){
					personSnilsField.disable();
				};
			}
		} else {
			//me.initUslugaElements
			me.actionCaseFunction(me.action, params, arguments);
		}

		var CmpCallCardEvnDrugUsage = base_form.findField('LpuBuilding_IsWithoutBalance');

        //загрузка справочников монго в сгенеренных элементах
        me.loadComboStoresMongo();
		me.checkIsCallControllFlag();
/*
		if (CmpCallCardEvnDrugUsage && CmpCallCardEvnDrugUsage.getValue() == 2) {
			me.DrugGrid = me.EvnDrugGrid
		} else {
			me.DrugGrid = me.FullDrugGrid
		}

		if (me.DrugGrid) {
			me.DrugGrid.show();
			me.DrugGrid.setParam('CmpCallCard_id', null, true);
			me.DrugGrid.removeAll();
		}
*/

	},
	//получение списка компонентов для вкладки Информация о вызове
	getPersonFields: function(){
		var me = this;
		return [
			{
				xtype      : 'fieldset',
				autoHeight: true,
				frame	   : true,
				labelWidth : 400,
				items      : [
					{
						border: false,
						layout: 'column',
						style: 'padding: 0px;',
						items: [
							{
								border: false,
								layout: 'form',
								width: 800,
								style: 'padding: 0px',
								items: [
									{
										xtype: 'swcmpclosecardisextracombo',
										hiddenName: 'CmpCloseCard_IsExtra',
										allowBlank: false,
										disabled: false
									},
									{
										fieldLabel: 'Номер вызова за день',
										name: 'Day_num',
										xtype: 'textfield',
										regex: /\d/,
										allowBlank: false,
										autoCreate: {tag: "input", type: "text", maxLength: "15", autocomplete: "off"},
										validator: function(a){return (a.match(/^[1-9]\d{0,15}$/))?true:false;}
									}, {
										fieldLabel: 'Номер вызова за год',
										name: 'Year_num',
										xtype: 'textfield',
										regex: /\d/,
										allowBlank: false,
										autoCreate: {tag: "input", type: "text",  maxLength: "15", autocomplete: "off"},
										validator: function(a){return (a.match(/^[1-9]\d{0,15}$/))?true:false;}
									}
								]
							},
							{
								border: false,
								labelWidth: 50,
								layout: 'form',
								items: [
									{
										xtype: 'checkbox',
										labelSeparator: '',
										boxLabel: 'На контроле',
										name: 'CmpCallCard_isControlCall'
									}
								]
							}
						]
					},
					{
						border: false,
						layout: 'column',
						style: 'padding: 0px;',
						items: [
							{
								border: false,
								layout: 'form',
								style: 'padding: 0px',
								items: []
							}
							]

					},
					{
						title: ++me.panelNumber + '. Время',
						xtype      : 'fieldset',
						id : 'timeBlock',
						autoHeight: true,
						items : [
							{
								dateLabel: 'Приема вызова',
								hiddenName: 'AcceptTime',
								hiddenId: this.getId() + '-AcceptTime',
								onChange: function(field, newValue){
									me.calcSummTime();
									var base_form = me.FormPanel.getForm();
									var date = new Date(newValue);
									if (me.action != 'view') {
										base_form.findField('Diag_id').setFilterByDate(date);
										base_form.findField('CallType_id').setFilterByDate(date);
									}

									// проверка на уникальность введенного номера вызова за день и за год
									if( newValue && me.action == 'stream' ) me.existenceNumbersDayYear();
								}.createDelegate(this),
								onTriggerClick: Ext.emptyFn,
								xtype: 'swdatetimefield'
							},
							{
								dateLabel: 'Передачи вызова бригаде СМП',
								hiddenName: 'TransTime',
								hiddenId: this.getId() + '-TransTime',
								onChange: function(field, newValue){
									me.calcSummTime();
									me.loadEmergencyTeamsWorkedInATime();
								}.createDelegate(this),
								xtype: 'swdatetimefield'
							},
							{
								dateLabel: 'Выезда на вызов',
								hiddenName: 'GoTime',
								hiddenId: this.getId() + '-GoTime',
								onChange: function(field, newValue){
									me.calcSummTime();
									me.getTheDistanceInATimeInterval();
								}.createDelegate(this),
								xtype: 'swdatetimefield'
							},
							{
								dateLabel: 'Прибытия на место вызова',
								hiddenName: 'ArriveTime',
								hiddenId: this.getId() + '-ArriveTime',
								onChange: function(field, newValue){
									me.calcSummTime();
								}.createDelegate(this),
								xtype: 'swdatetimefield'
							},
							{
								dateLabel: 'Начала транспортировки больного',
								hiddenName: 'TransportTime',
								hiddenId: this.getId() + '-TransportTime',
								onChange: function(field, newValue){
									me.calcSummTime();
								}.createDelegate(this),
								xtype: 'swdatetimefield'
							},
							{
								dateLabel: 'Прибытия в медицинскую организацию',
								hiddenName: 'ToHospitalTime',
								hiddenId: this.getId() + '-ToHospitalTime',
								onChange: function(field, newValue){
									me.calcSummTime();
								}.createDelegate(this),
								xtype: 'swdatetimefield'
							},
							{
								dateLabel: 'Окончания вызова',
								hiddenName: 'EndTime',
								hiddenId: this.getId() + '-EndTime',
								onChange: function(field, newValue){
									me.calcSummTime();
									me.getTheDistanceInATimeInterval();
								}.createDelegate(this),
								xtype: 'swdatetimefield'
							},
							{
								dateLabel: 'Возвращения на станцию (подстанцию, отделение)',
								hiddenName: 'BackTime',
								hiddenId: this.getId() + '-BackTime',
								xtype: 'swdatetimefield',
								onChange: function(field, newValue){
									me.getTheDistanceInATimeInterval();
								}.createDelegate(this)
							},
							{
								fieldLabel: 'Затраченное на выполнения вызова',
								name: 'SummTime',
								width: 90,
								xtype: 'textfield',
								maskRe: /[0-9:]/,
								regex:/^\d{1,}(:\d{1,2})?$/
							}
						]
					},
					{
						title: ++me.panelNumber + '. Подразделение СМП',
						xtype      : 'fieldset',
						autoHeight: true,
						items : [
							{
								xtype: 'swsmpunitscombo',
								fieldLabel: 'Станция (подстанция), отделения',
								hiddenName:'LpuBuilding_id',
								disabledClass: 'field-disabled',
								width: 350,
								allowBlank: false,
								listWidth: 300,
								listeners: {
									blur: function(cmp, b, c){
										if(!cmp.getValue()){
											var indTab = ( getRegionNick().inlist(['ufa']) ) ? 7 : 6,
												tabMed = me.tabPanel.getItem(indTab),
												txt = 'Для ввода медикаментов необходимо заполнить поле “Номер станции (подстанции), отделения"';

											if(getRegionNick().inlist(['perm', 'krym', 'buryatiya', 'astra', 'kareliya', 'khak'])){
												txt = 'Для ввода медикаментов необходимо заполнить поле “Станция (подстанция), отделение"';
											};

											tabMed.removeAll();
											tabMed.add({
												html: txt,
												style: 'margin-top: 10px; text-align: center; font-size: 16px;',
												height: 700
											});
											tabMed.doLayout();
										}
									},

									beforeselect: function (combo,record,index) {
											var base_form = me.FormPanel.getForm();
											// форма расхода медикаментов должна зависеть от настроек подразделения, которое выбрано в Карте вызова
											var idLpuBuilding = combo.getValue();
											var newLpuBuilding = record.get("LpuBuilding_id");
											var LBIsWithoutBalance = base_form.findField('LpuBuilding_IsWithoutBalance').getValue();

											var indTab = ( getRegionNick().inlist(['ufa']) ) ? 7 : 6;
											var tabMed=me.tabPanel.getItem(indTab);

											if (tabMed) {
												tabMed.removeAll();
												tabMed.add({items: me.getDrugFields(newLpuBuilding)});
												me.tabPanel.doLayout();
											}

											Ext.Ajax.request({
												params: {LpuBuilding_id: record.get("LpuBuilding_id")},
												url: '/?c=LpuStructure&m=getLpuBuildingData',
												callback: function (obj, success, response) {
													if (success) {
														var response_obj = Ext.util.JSON.decode(response.responseText);
														//если в настройках пришел LpuBuilding_IsWithoutBalance
														if(response_obj[0] && response_obj[0].LpuBuilding_IsWithoutBalance){
															if(LBIsWithoutBalance != response_obj[0].LpuBuilding_IsWithoutBalance)
															{
																base_form.findField('LpuBuilding_IsWithoutBalance').setValue(response_obj[0].LpuBuilding_IsWithoutBalance);
																var indTab = ( getRegionNick().inlist(['ufa']) ) ? 7 : 6;
																var tabMed=me.tabPanel.getItem(indTab);
																var view_frame;

																if(LBIsWithoutBalance != '') {

																	view_frame = Ext.getCmp('CCCNCC_CmpCallCardEvnDrugGrid');
																	if(Ext.isEmpty(view_frame))
																		view_frame = Ext.getCmp('CCCNCC_CmpCallCardDrugGrid');
																	var store = view_frame.getGrid().getStore(),
																		grid = view_frame.getGrid();
																	if(store.getCount() > 0) {
																		sw.swMsg.show({
																			icon: Ext.MessageBox.QUESTION,
																			msg: langs('При изменении подстанции СМП все использованные медикаменты будут удалены. Продолжить изменение?'),
																			title: langs('Подтверждение'),
																			buttons: Ext.Msg.YESNO,
																			fn: function (buttonId, text, obj) {
																				var dataDrug = new Array();
																				if ('yes' == buttonId) {
																					if(me.action == 'stream'){
																						tabMed.removeAll();
																						tabMed.add({items: me.getDrugFields(newLpuBuilding)});
																						me.tabPanel.doLayout();
																						combo.setValue(newLpuBuilding);
																						return false;
																					}
																					var CmpCallCard_id = me.FormPanel.getForm().findField('CmpCallCard_id').getValue();
																					if (!(CmpCallCard_id > 0)) {
																						Ext.Msg.alert('Карта не определена');
																						return false;
																					}
																					if (Ext.isEmpty(LBIsWithoutBalance)) {
																						Ext.Msg.alert('Не удается определить параметр учета остатков на складе');
																						return false;
																					}
																					store.each(function (rec) {
																						if (rec.get('state') == 'add') {
																							grid.getStore().remove(rec);
																						}
																						else {
																							rec.set('state', 'delete');
																							rec.commit();
																							dataDrug.push(rec.data);
																						}
																					});
																					// view_frame.setFilter(); интересно, зачем это?
																					var drugGridJsonData = dataDrug.length > 0 ? Ext.util.JSON.encode(dataDrug) : "";
																					Ext.Ajax.request({
																						params: {
																							CmpCallCard_id: CmpCallCard_id,
																							CmpCallCardDrugJSON: drugGridJsonData,
																							LpuBuilding_id: idLpuBuilding,
																							LpuBuilding_IsWithoutBalance: LBIsWithoutBalance
																						},
																						url: '/?c=CmpCallCard&m=deleteCmpCallCardEvnDrug',
																						callback: function (obj, success, response) {
																							if (success) {
																								var response_obj = Ext.util.JSON.decode(response.responseText);
																								tabMed.removeAll();
																								tabMed.add({items: me.getDrugFields(idLpuBuilding)});
																								me.tabPanel.doLayout();
																								combo.setValue(newLpuBuilding);

																							}
																						}
																					});
																				} else {
																					base_form.findField('LpuBuilding_IsWithoutBalance').setValue(LBIsWithoutBalance);
																					combo.setValue(idLpuBuilding);
																					return false
																				}
																			}
																		});
																	}
																	else {
																		combo.setValue(newLpuBuilding);
																		if (tabMed) {
																			tabMed.removeAll();
																			tabMed.add({items: me.getDrugFields(newLpuBuilding)});
																			me.tabPanel.doLayout();
																		}
																	}
																}
																else {
																	if (tabMed) {
																		tabMed.removeAll();
																		tabMed.add({items: me.getDrugFields(newLpuBuilding)});
																		me.tabPanel.doLayout();
																	}
																}
																combo.setValue(newLpuBuilding);
															}
															else{
																combo.setValue(newLpuBuilding);
															}

														}
													}
												}
											});
										}

								}
							},
							{
								xtype: 'container',
								autoEl: {},
								layout: 'form',
								items: [{
									xtype: 'swEmergencyTeamCCC',
									fieldLabel:	'Бригада скорой медицинской помощи',
									hiddenName: 'EmergencyTeam_id',
									allowBlank: !getRegionNick().inlist(['vologda']),
									width: 350,
									listWidth: 350,
									listeners: {
										select: function(combo,record,index){
											var EmergencyTeamNum = me.FormPanel.getForm().findField('EmergencyTeamNum'),
												EmergencyTeamSpec = me.FormPanel.getForm().findField('EmergencyTeamSpec_id'),
												rec = EmergencyTeamSpec.findRecord('EmergencyTeamSpec_Code', record.get('EmergencyTeamSpec_Code'));

											if(rec)EmergencyTeamSpec.setValue(rec.get('EmergencyTeamSpec_id'));

											if(EmergencyTeamNum) EmergencyTeamNum.setValue(record.get('EmergencyTeam_Num'));
										}.createDelegate(this)
									}
								}]
							},
							{
								xtype: 'hidden',
								name: 'EmergencyTeamNum'
							},
							{
								name: 'LpuBuilding_IsWithoutBalance',
								xtype: 'hidden',
								hiddenId: 'LpuBuilding_IsWithoutBalance'
							},
							{
								xtype: 'container',
								autoEl: {},
								layout: 'form',
								//width: 750,
								items: [
								{
									fieldLabel: 'Профиль бригады скорой медицинской помощи',
									comboSubject: 'EmergencyTeamSpec',
									hiddenName: 'EmergencyTeamSpec_id',
									id: this.id+'EmergencyTeamSpec_id',
									allowBlank: false,
									width: 350,
									listWidth: 300,
									xtype: 'swcustomobjectcombo'
								}]
							},
							{
								allowBlank: false,
								dateFieldId: 'EVPLEF_EvnVizitPL_setDate',
								enableOutOfDateValidation: true,
								hiddenName: 'MedStaffFact_id',
								name: 'MedStaffFact_id',
								id: 'CMP_MedStaffFactRecCmb',
								lastQuery: '',
								listWidth: 600,
								parentElementId: 'CMP_LpuSectionCmb',
								width: 350,
								xtype: 'swmedstafffactglobalcombo',
								listeners: {
									select: function(combo, record, index){
										if (record.data.MedPersonal_id > 0) {
											var medPersField = this.FormPanel.getForm().findField('MedPersonal_id');
											if(medPersField) medPersField.setValue(record.data.MedPersonal_id);
										}
									}.createDelegate(this),
									focus: function(cb){

										if (me.FormPanel.getForm().findField('TransTime').getStringValue() != '') {
											var time_start = Date.parseDate( me.FormPanel.getForm().findField('TransTime').getStringValue(), 'd.m.Y H:i' );
										} else {
											var time_start = new Date();
										}
										var onDate = Ext.util.Format.date(time_start, 'd.m.Y');

										cb.baseFilterFn = setMedStaffFactGlobalStoreFilter({
											LpuBuildingType_id: 27,
											withoutLpuSection: true,
											onDate: onDate // не уволены
										} , cb.store, true);

									}.createDelegate(this)
								}
							},
							{
								text: 'Выбрать',
								xtype: 'button',
								id: 'BrigSelectBtn',
								handler: function() {
									var parentObject = this;
									getWnd('swSelectEmergencyTeamWindow').show({
										CmpCallCard: parentObject.FormPanel.getForm().findField('CmpCallCard_id').getValue(),
										AcceptTime: Ext.util.Format.date(parentObject.FormPanel.getForm().findField('AcceptTime').getValue(), 'd.m.Y H:i:s'),
										callback: function(data) {
											parentObject.setEmergencyTeam(parentObject.FormPanel.getForm().findField('CmpCallCard_id').getValue(), data);

										}

									});
								}.createDelegate(this)

							}
						]
					},
					{
						title : ++me.panelNumber + '. Адрес вызова',
						xtype      : 'fieldset',
						refId : 'addressBlock',
						autoHeight: true,
						items : [
								{
									enableKeyEvents: true,
									disabled: false,
									hiddenName: 'KLAreaStat_idEdit',
									listeners: {
										beforeselect: function(combo, record) {
											if ( typeof record != 'undefined' ) {
											if( record.get('KLAreaStat_id') == '' ) {
												combo.onClearValue();
												return;
											}

											var base_form = this.FormPanel.getForm();
											base_form.findField('Area_id').reset();
											base_form.findField('City_id').reset();
											base_form.findField('Town_id').reset();
											base_form.findField('Street_id').reset();
											base_form.findField('StreetAndUnformalizedAddressDirectory_id').reset();
											
											if( record.get('KLRGN_id') ){
												base_form.findField('KLRgn_id').setValue(record.get('KLRGN_id'));
											}

											if( record.get('KLSubRGN_id') != '' ) {
												base_form.findField('StreetAndUnformalizedAddressDirectory_id').getStore().load({params: {town_id: record.get('SubRGN_id')}});
												base_form.findField('Area_id').setValue(record.get('KLSubRGN_id'));
												base_form.findField('Area_id').getStore().removeAll();
												base_form.findField('Area_id').getStore().load({
													params: {region_id: record.get('KLRGN_id')},
													callback: function() {
														this.setValue(this.getValue());
														this.fireEvent('beforeselect', this, this.getStore().getAt(this.getStore().findBy(function(rec) {return rec.get('SubRGN_id') == this.getValue();}.createDelegate(this))));
													}.createDelegate(base_form.findField('Area_id'))
												});
											} else if( record.get('KLCity_id') != '' ) {
												base_form.findField('City_id').setValue(record.get('KLCity_id'));
												base_form.findField('City_id').getStore().removeAll();
												base_form.findField('City_id').getStore().load({
													params: {subregion_id: record.get('KLRGN_id')},
													callback: function() {
														this.setValue(this.getValue());
														this.fireEvent('beforeselect', this, this.getStore().getAt(this.getStore().findBy(function(rec) {return rec.get('City_id') == this.getValue();}.createDelegate(this))));
													}.createDelegate(base_form.findField('City_id'))
												});
											}
											//KLTown_id
											}
										}.createDelegate(this)
									},
									onClearValue: function() {
										var base_form = this.FormPanel.getForm();
										base_form.findField('KLAreaStat_idEdit').clearValue();
										base_form.findField('Area_id').enable();
										base_form.findField('City_id').enable();
										base_form.findField('Town_id').enable();
										base_form.findField('Town_id').reset();
										base_form.findField('Town_id').getStore().removeAll();
										base_form.findField('Street_id').enable();
										base_form.findField('Street_id').reset();
										base_form.findField('StreetAndUnformalizedAddressDirectory_id').enable();
										base_form.findField('StreetAndUnformalizedAddressDirectory_id').reset();
										base_form.findField('StreetAndUnformalizedAddressDirectory_id').getStore().removeAll();
									}.createDelegate(this),
									width: 180,
									xtype: 'swklareastatcombo'
								},
								{
									xtype: 'swregioncombo',
									name: 'KLRgn_id',
									allowBlank: true,
									hiddenName: 'KLRgn_id',
									width: 300
								},
								{
									disabled: false,
									enableKeyEvents: true,
									fieldLabel: 'Район',
									hiddenName: 'Area_id',
									width: 180,
									listeners: {
										'beforeselect': function(combo, record) {
											combo.setValue(record.get(combo.valueField));
											var base_form = this.FormPanel.getForm();
											if( record.get('SubRGN_id') > 0 ) {
												base_form.findField('City_id').reset();
												base_form.findField('City_id').getStore().removeAll();
												base_form.findField('City_id').getStore().load({params: {subregion_id: record.get('SubRGN_id')}});
												base_form.findField('Town_id').getStore().removeAll();
												base_form.findField('Town_id').getStore().load({params: {city_id: record.get('SubRGN_id')}});
												base_form.findField('StreetAndUnformalizedAddressDirectory_id').getStore().removeAll();
												base_form.findField('StreetAndUnformalizedAddressDirectory_id').getStore().load({params: {town_id: record.get('SubRGN_id')}});
											}
										}.createDelegate(this)
									},
									xtype: 'swsubrgncombo'
								}, 
								{
									hiddenName: 'City_id',
									disabled: false,
									name: 'City_id',
									width: 180,
									//allowBlank: false,
									xtype: 'swcitycombo',
									onTrigger2Click: function() {
										me.showSearchCityWindow();
									},
									listeners: {
										'beforeselect': function(combo, record) {
											combo.setValue(record.get(combo.valueField));
											var base_form = this.FormPanel.getForm();
											if( record && record.get('City_id') > 0 ) {
												var townField = base_form.findField('Town_id'),
													streetField = base_form.findField('StreetAndUnformalizedAddressDirectory_id');

												townField.getStore().removeAll();
												townField.getStore().load({params: {city_id: record.get('City_id')}});
												streetField.getStore().removeAll();
												if(record.get('City_id'))
													streetField.getStore().load({params: {town_id: record.get('City_id'), showSocr: 1}});
											}
										}.createDelegate(this)
									}
								}, 
								{
									minChars: 0,
									hiddenName: 'Town_id',
									name: 'Town_id',
									width: 250,
									xtype: 'swtowncombo',
									onTrigger2Click: function() {
										me.showTownSearchWindow();
									},
									disabled: false,
									enableKeyEvents: true,
									//allowBlank: false,
									listeners: {
										beforeselect: function(combo, record) {
											var base_form = this.FormPanel.getForm(),
												cityField = base_form.findField('City_id'),
												streetField = base_form.findField('StreetAndUnformalizedAddressDirectory_id');
											
											combo.setValue(record.get(combo.valueField));

											streetField.clearValue();
											streetField.getStore().removeAll();
											if(combo.getValue())
												streetField.getStore().load({params: {town_id: record.get('Town_id')}});
										}.createDelegate(this)
									}									
								}, 
								{
									xtype: 'swstreetandunformalizedaddresscombo',
									fieldLabel: langs('Улица/Объект'),
									hiddenName: 'StreetAndUnformalizedAddressDirectory_id',
									listeners: {
										blur: function(c){
											var base_form = this.FormPanel.getForm();
											if(
												!c.store.getCount() || 
												c.store.findBy(function(rec) { return rec.get('StreetAndUnformalizedAddressDirectory_id') == c.getValue(); }) == -1 
											)
											{
												base_form.findField('UnformalizedAddressDirectory_id').setValue(null);
												base_form.findField('Street_id').setValue(null);
												base_form.findField('CmpCallCard_Ulic').setValue(c.getRawValue());
											}
										}.createDelegate(this),
										beforeselect: function(combo, record) {											
											if ( typeof record != 'undefined' ) { combo.setValue(record.get(combo.valueField)); }
											var base_form = this.FormPanel.getForm();
											base_form.findField('UnformalizedAddressDirectory_id').setValue(record.get('UnformalizedAddressDirectory_id'));
											base_form.findField('Street_id').setValue(record.get('KLStreet_id'));										
										}.createDelegate(this)
									},
									width: 250,
									editable: true
								},
								{
									xtype: 'swstreetandunformalizedaddresscombo',
									fieldLabel: langs('Улица'),
									hiddenName: 'CmpCloseCard_UlicSecond',
									trigger2Class: 'x-form-clear-trigger',
									listeners: {},
									width: 250,
									editable: true,
									onTrigger2Click: function() {
										var base_form = this.FormPanel.getForm(),
											CmpCallCard_Dom = base_form.findField('House'),
											CmpCloseCard_UlicSecond = base_form.findField('CmpCloseCard_UlicSecond');

										CmpCloseCard_UlicSecond.reset();
										this.checkCrossRoadsFields(true);
									}.createDelegate(this),
									listeners: {
										blur: function(combo){
											this.checkCrossRoadsFields(true);
										}.createDelegate(this),

										beforeselect: function(combo, record) {
											if ( typeof record != 'undefined' ) {
												combo.setValue(record.get(combo.valueField));
											}
										}.createDelegate(this),

										select: function(combo, rec) {
											this.checkCrossRoadsFields(true);
										}.createDelegate(this)
									}
								},
								{
									xtype: 'hidden',
									name: 'UnformalizedAddressDirectory_id'
								},
								{
									xtype: 'hidden',
									name: 'Street_id'
								},
								{
									xtype: 'hidden',
									name: 'CmpCallCard_Ulic'
								},
								{
									disabledClass: 'field-disabled',
									disabled: false,
									fieldLabel: 'Дом',
									//name: 'CmpCallCard_Dom',
									name: 'House',
									width: 100,
									xtype: 'textfield',
									enableKeyEvents: true,
									listeners: {
										keyup: function(c, e) {
											this.checkCrossRoadsFields(true, e);
										}.createDelegate(this)
									}
								}, {
									disabledClass: 'field-disabled',
									disabled: false,
									fieldLabel: 'Корпус',
									//name: 'CmpCallCard_Dom',
									name: 'Korpus',
									width: 100,
									xtype: 'textfield'
								}, {
									disabled: false,
									disabledClass: 'field-disabled',
									fieldLabel: 'Квартира',
									maxLength: 5,
									autoCreate: {tag: "input", size:14, maxLength: "5", autocomplete: "off"},
									//maskRe: /^([а-яА-Я0-9]{1,5})$/,
									//name: 'CmpCallCard_Kvar',
									name: 'Office',
									width: 100,
									xtype: 'textfieldpmw'
								}, {
									disabled: false,
									disabledClass: 'field-disabled',
									fieldLabel: 'Комната',
									//name: 'CmpCallCard_Kvar',
									name: 'Room',
									width: 100,
									xtype: 'textfield'
								}, {
									disabled: false,
									disabledClass: 'field-disabled',
									fieldLabel: 'Подъезд',
									//name: 'CmpCallCard_Podz',
									name: 'Entrance',
									width: 100,
									xtype: 'textfield'
								}, {
									disabled: false,
									disabledClass: 'field-disabled',
									fieldLabel: 'Этаж',
									//name: 'CmpCallCard_Etaj',
									name: 'Level',
									width: 100,
									xtype: 'textfield'
								}, {
									disabled: false,
									disabledClass: 'field-disabled',
									fieldLabel: 'Код замка в подъезде (домофон)',
									//name: 'CmpCallCard_Kodp',
									name: 'CodeEntrance',
									width: 100,
									xtype: 'textfield'
								}
						]
					}, {
						title : ++me.panelNumber + '. Сведения о больном',
						xtype      : 'fieldset',
						autoHeight: true,
						items : [
							{
								layout: 'column',
								items :[
									{
										border: false,
										layout: 'form',
										items : [{
											handler: function() {
												this.personSearch();
											}.createDelegate(this),
											iconCls: 'search16',
											id: 'CCCSEF_PersonSearchBtn',
											text: 'Поиск',
											xtype: 'button'
										},
										{
											handler: function() {
												this.personReset();
											}.createDelegate(this),
											iconCls: 'reset16',
											id: 'CCCSEF_PersonResetBtn',
											text: 'Сброс',
											xtype: 'button'
										},
										{
											handler: function() {
												this.personUnknown();
											}.createDelegate(this),
											iconCls: 'reset16',
											id: 'CCCSEF_PersonUnknownBtn',
											text: 'Неизвестен',
											xtype: 'button'
										}]
									},
									{
										border: false,
										layout: 'form',
										items : [
											{
												fieldLabel: 'Фамилия',
												disabled: false,
												name: 'Fam',
												toUpperCase: true,
												width: 180,
												xtype: 'textfieldpmw',
												allowBlank: false,
												listeners: {
													change: function(){
														var PersonFields_IsDirty = me.FormPanel.getForm().findField('PersonFields_IsDirty');
														if(PersonFields_IsDirty) PersonFields_IsDirty.setValue(true);														
													},
													blur: function(){
														me.checkPersonIdentification();
													}
												}
											}, {
												fieldLabel: 'Имя',
												disabled: false,
												name: 'Name',
												toUpperCase: true,
												width: 180,
												xtype: 'textfieldpmw',
												allowBlank: false,
												listeners: {
													change: function(){
														var PersonFields_IsDirty = me.FormPanel.getForm().findField('PersonFields_IsDirty');
														if(PersonFields_IsDirty) PersonFields_IsDirty.setValue(true);														
													},
													blur: function(){
														me.checkPersonIdentification();
													}
												}
											}, {
												fieldLabel: 'Отчество',
												disabled: false,
												name: 'Middle',
												toUpperCase: true,
												width: 180,
												xtype: 'textfieldpmw',
												listeners: {
													change: function(){
														var PersonFields_IsDirty = me.FormPanel.getForm().findField('PersonFields_IsDirty');
														if(PersonFields_IsDirty) PersonFields_IsDirty.setValue(true);														
													},
													blur: function(){
														me.checkPersonIdentification();
													}
												}
											},
											{
												fieldLabel: 'Серия полиса',
												name: 'Person_PolisSer',
												width: 180,
												xtype: 'textfield',
												editable: false,
												disabled: false
											},
											{
												fieldLabel: 'Номер полиса',
												disabled: false,
												name: 'Person_PolisNum',
												width: 180,
												xtype: 'textfield',
												editable: false
											},
											{
												fieldLabel: 'Единый номер',
												disabled: false,
												name: 'CmpCloseCard_PolisEdNum',
												width: 180,
												xtype: 'textfield',
												editable: false
											},
											/*
											{
												valueField: 'Lpu_id',
												//allowBlank: false,
												//disabled: true,
												autoLoad: true,
												width: 350,
												listWidth: 350,
												fieldLabel: langs('ЛПУ передачи'),
												disabledClass: 'field-disabled',
												hiddenName: 'Lpu_ppdid',
												displayField: 'Lpu_Nick',
												medServiceTypeId: 18,
												handler: function() {
													this.selectLpuTransmit();
												}.createDelegate(this),
												comAction: 'AllAddress',
												listeners: {
													beforeselect: function(combo, record) {
														var base_form = this.FormPanel.getForm();
														if(record.get('Lpu_id') == '0')
														{
															combo.getStore().load({params:
															{
																Object: 'LpuWithMedServ',
																comAction: 'AllAddress',
																MedServiceType_id: 18,
																KLAreaStat_idEdit: base_form.findField('KLAreaStat_idEdit').getValue(),
																KLSubRgn_id: base_form.findField('Area_id').getValue(),
																KLCity_id: base_form.findField('City_id').getValue(),
																KLTown_id: base_form.findField('Town_id').getValue(),
																KLStreet_id: base_form.findField('Street_id').getValue(),
																CmpCallCard_Dom: base_form.findField('House').getValue(),
																Person_Age: base_form.findField('Age').getValue()
															}
															});
															return false;
														}
														//определяем метод загрузки лпу передачи
														//this.selectLpuTransmit();
														}.createDelegate(this),
													select: function(combo, record){
														if (record.data.Lpu_id == null)
														{
															combo.setValue('');
														}
													}
												},
												xtype: 'swlpuwithmedservicecombo'
											},
											*/
											{
												disabledClass: 'field-disabled',
												fieldLabel: langs('Дополнительная информация/ Уточненный адрес'),
												toUpperCase: true,
												height: 100,
												name: 'CmpCloseCard_DopInfo',
												width: 350,
												xtype: 'textarea'
											}
										]
									}
								]
							},
							{
								xtype      : 'fieldset',
								autoHeight: true,
								items      : [
								{
									allowDecimals: false,
									allowNegative: false,
									disabledClass: 'field-disabled',
									fieldLabel: 'Возраст',
									disabled: false,
									allowBlank: false,
									name: 'Age',
									toUpperCase: true,
									width: 180,
									xtype: 'numberfield',
									validator: function(a){return (a.match(/^[1-9]\d*$/))?true:false;},
									listeners: {
										change: function() {
											var PersonFields_IsDirty = me.FormPanel.getForm().findField('PersonFields_IsDirty');
											if(PersonFields_IsDirty) PersonFields_IsDirty.setValue(true);
											
											me.setMKB();
										},
										blur: function(){
											me.checkPersonIdentification();
										}
									}
								}, {
									columns: 1,
									vertical: true,
									width: 600,
									xtype: 'checkboxgroup',
									singleValue: true,
									disabled: false,
									allowBlank: false,
									fieldLabel: 'Единица измерения возраста',
									items: this.getCombo('AgeType_id'),
									listeners: {
										change: function() {
											this.setMKB();
										}.createDelegate(this)
									}
								}]
							},
							{
								comboSubject: 'Sex',
								disabledClass: 'field-disabled',
								fieldLabel: 'Пол',
								hiddenName: 'Sex_id',
								allowBlank: false,
								width: 130,
								xtype: 'swcommonsprcombo',
								listeners: {
									change: function() {
										this.setMKB();
									}.createDelegate(this)
								}
							}, {
								xtype: 'textfield',
								disabled: false,
								width: 180,
								name: 'Work',
								fieldLabel: 'Место работы'
							}, {
								xtype: 'textfield',
								disabled: false,
								width: 180,
								name: 'DocumentNum',
								fieldLabel: 'Серия и номер документа, удостоверяющего личность'
							}
						]
					}, {
						xtype: 'fieldset',
						autoHeight: true,
						items: [
								// @todo Сделать компонент и вынести в библиотеку
								{
									xtype: 'swcommonsprcombo',
									fieldLabel: ++me.panelNumber + '. Кто вызывает',
									comboSubject: 'CmpCallerType',
									hiddenName: 'Ktov',
									displayField: 'CmpCallerType_Name',
									disabledClass: 'field-disabled',
									editable: true,
									forceSelection: false,
									width: 350,
									listeners: {
										blur: function(el){
											var base_form = me.FormPanel.getForm(),
												CmpCallerTypeField = base_form.findField('CmpCallerType_id'),
												raw_value = el.getRawValue(),
												rec = el.findRecord( el.displayField, raw_value );

											// Запись в комбобоксе присутствует
											if ( rec ) {
												CmpCallerTypeField.setValue( rec.get( el.valueField ) );
											}
											// Пользователь указал свое значение
											else {
												CmpCallerTypeField.setValue(null);
											}
											el.setValue(raw_value);
										}
									}
								},
								{
									xtype: 'hidden',
									name: 'CmpCallerType_id'
								},
								{
									fieldLabel: '№ телефона вызывающего',
									name: 'Phone',
									width: 250,
									xtype: 'textfield'
								}
							]
					},
					{
						xtype      : 'fieldset',
						autoHeight: true,
						items : [
							{
								xtype: 'swmedpersonalcombo',
								fieldLabel: ++me.panelNumber + '. Фельдшер, принявший вызов',
								hiddenName: 'FeldsherAccept',
								allowBlank:true,
								width: 250,
								listeners: {
									select: function(combo,record,index){

										var appendCombo = this.FormPanel.getForm().findField('FeldsherAcceptCall');
										if(appendCombo)appendCombo.setValue(combo.getValue());
									}.createDelegate(this)
								}
							},
							{
								xtype: 'swmedpersonalcombo',
								fieldLabel: ++me.panelNumber + '. Фельдшер, передавший вызов',
								hiddenName: 'FeldsherTrans',
								allowBlank:true,
								width: 250
							},
							{
								xtype: 'textfield',
								disabled: true,
								fieldLabel: ++me.panelNumber + '. Пользователь, закрывший карту вызова',
								name: 'pmUser_insName',
								allowBlank:true,
								width: 250
							}
						]
					},
					{
						xtype      : 'fieldset',
						autoHeight: true,
						frame	   : true,
						//width: '100%',
						items      : [{
								columns: 1,
								vertical: true,
								width: '100%',
								xtype: 'checkboxgroup',
								singleValue: true,
								fieldLabel: ++me.panelNumber + '. Место регистрации больного',
								items: this.getCombo('PersonRegistry_id')
						}]
					},
					{
						xtype      : 'fieldset',
						autoHeight: true,
						frame	   : true,
						//width: '100%',
						items      : [
							{
								name: "SocialCombo",
								columns: 2,
								vertical: true,
								allowBlank: false,
								fieldLabel	   : ++me.panelNumber + '. Социальное положение больного',
								width: 600,
								xtype: 'checkboxgroup',
								singleValue: true,
								items: this.getCombo('PersonSocial_id'),
                                listeners:{
                                    change: function(){
                                        me.FormPanel.getForm().findField('SocialCombo').allowBlank = true;
                                        me.FormPanel.getForm().findField('SocialCombo').validate();
                                    }.createDelegate(this)
                                }
							}
						]
					}
				]
			}
		];
	},
	//получение списка компонентов для вкладки Повод к вызову
	getPovodFields: function(){
		var me = this;
		return [
			{
				xtype      : 'fieldset',
				autoHeight: true,
				frame	   : true,
				items      :
				[
					{
						comboSubject: 'CmpReason',
						disabledClass: 'field-disabled',
						fieldLabel: ++me.panelNumber + '. Повод',
						allowBlank: false,
						hiddenName: 'CallPovod_id',
						id: this.id+'idCallPovod_id',
						width: 350,
						listWidth: 300,
						editable: true,
						xtype: 'swreasoncombo',
						listeners: {
							change: function(cmp, newVal){
								var base_form = me.FormPanel.getForm(),
									radioGroupResultTrip = base_form.findField('resultEmergencyTrip'),
									diagField = base_form.findField('Diag_id'),
									reasonRec = cmp.store.getById(newVal);

								//если повод - "ошибка" то делаем поле результат выезда необязательным и диагноз
								//иначе - обязательный
								if(reasonRec && reasonRec.get('CmpReason_Code').inlist(['01!'])){
									if(radioGroupResultTrip) radioGroupResultTrip.allowBlank = true;
									diagField.allowBlank = true;
								}
								else{
									if(radioGroupResultTrip) radioGroupResultTrip.allowBlank = false;
									diagField.allowBlank = false;
								}
								if(radioGroupResultTrip) radioGroupResultTrip.validate();
								diagField.validate();
							}
						}
					},
					{
						name: 'CmpCloseCard_IsNMP',
						id: this.id+'_CmpCloseCard_IsNMP',
						fieldLabel: 'Неотложная помощь',
						xtype: 'checkbox'
					}
				]
			},
			{
				xtype      : 'fieldset',
				autoHeight: true,
				frame	   : true,
				items      : [{
						fieldLabel	   : ++me.panelNumber + '. Вызов',
						hiddenName: 'CallType_id',
						xtype: 'swcmpcalltypecombo',
						width: 300,
						listWidth: 300
				}]
			},
			{
				xtype      : 'fieldset',
				autoHeight: true,
				frame	   : true,
				items      : [{
						columns: 1,
						vertical: true,
						fieldLabel	   : ++me.panelNumber + '. Место получения вызова бригадой скорой медицинской помощи',
						width: '100%',
						xtype: 'checkboxgroup',
						singleValue: true,
						items: this.getCombo('CallTeamPlace_id')
				}]
			},
			{
				xtype      : 'fieldset',
				autoHeight: true,
				frame	   : true,
				items      : [{
						columns: 1,
						vertical: true,
						fieldLabel	   : ++me.panelNumber + '. Причины выезда с опозданием',
						width: '100%',
						xtype: 'checkboxgroup',
						singleValue: true,
						items: this.getCombo('Delay_id')
				}]
			},
			{
				xtype      : 'fieldset',
				autoHeight: true,
				frame	   : true,
				items      : [{
						columns: 1,
						vertical: true,
						fieldLabel	   : ++me.panelNumber + '. Состав бригады скорой медицинской помощи',
						width: '100%',
						xtype: 'checkboxgroup',
						items: this.getCombo('TeamComplect_id')
				}]
			},
			{
				xtype      : 'fieldset',
				autoHeight: true,
				frame	   : true,
				items      : [
					new Ext.ux.RemoteCheckboxGroup({
						name: 'CmpCallPlaceType_id',
						columns: 1,
						vertical: true,
						fieldLabel	   : ++me.panelNumber + '. Тип места вызова',
						url: '/?c=CmpCallCard&m=getCmpCallPlaces',
						method: 'post',
						singleValue: true,
						reader: new Ext.data.JsonReader(
							{
								totalProperty: 'totalCount',
								root: 'data',
								fields: [{name: 'CmpCallPlaceType_id'}, {name: 'CmpCallPlaceType_Name'}, {name: 'CmpCallPlaceType_Code'}, {name: 'is_checked'}]
							}),
						cbRenderer:function(){},
						cbHandler:function(){
							if(!me.existOsmUslugaComplex()){
								me.addOsmUslugaComplex()
							}
						},
						items:[{boxLabel:'Loading'},{boxLabel:'Loading'}],
						fieldId: 'CmpCallPlaceType_id',
						fieldName: 'CmpCallPlaceType_Name',
						boxLabel: 'CmpCallPlaceType_Name',
						fieldValue: 'CmpCallPlaceType_id',
						fieldCode: 'CmpCallPlaceType_Code',
						fieldChecked: 'is_checked'
					})//,
					//{
					//	columns: 1,
					//	vertical: true,
					//	fieldLabel	   : ++me.panelNumber + '. Тип места вызова',
					//	width: '100%',
					//	xtype: 'checkboxgroup',
					//	singleValue: true,
					//	name: 'CmpCallPlaceType_id',
					//	items: this.getCombo('CallPlace_id'),
					//	listeners: {
					//		scope: this,
					//		change: function( obj, checked ){
					//			this.getCallUrgencyAndProfile();
					//			if(!me.existOsmUslugaComplex()){
					//				me.addOsmUslugaComplex()
					//			}
					//		}
					//	}
					//}
				]
			},
			{
				xtype      : 'fieldset',
				autoHeight: true,
				frame	   : true,
				items      : [{
						columns: 2,
						fieldLabel	   : ++me.panelNumber + '. Причина несчастного случая',
						name: 'reasonCheckgroup',
						vertical: true,
						width: '100%',
						xtype: 'checkboxgroup',
						items: this.getCombo('AccidentReason_id')
				}]
			},
			{
				xtype      : 'fieldset',
				autoHeight: true,
				frame	   : true,
				items      : [{
						columns: 2,
						vertical: true,
						fieldLabel	   : 'Травма',
						name: 'traumaCheckgroup',
						width: '100%',
						xtype: 'checkboxgroup',
						items: this.getCombo('Trauma_id')
				}]
			},
			{
				xtype      : 'fieldset',
				autoHeight: true,
				frame	   : true,
				items      : [{
						fieldLabel: ++me.panelNumber + '. Наличие клиники опьянения',
						hiddenName: 'isAlco',
						width: 40,
						comboSubject: 'YesNo',
						xtype: 'swcommonsprcombo'
				}]
			}
		];
	},
	//получение списка компонентов для вкладки Жалобы
	getJalobFields: function(){
		var me = this;

		return [
			{
				xtype      : 'fieldset',
				autoHeight: true,
				frame	   : true,
				labelWidth: 100,
				items      : [{
						fieldLabel	   : ++me.panelNumber + '. Жалобы',
						name: 'Complaints',
						width: '90%',
						xtype: 'textarea'
				}]
			},
			{
				xtype      : 'fieldset',
				autoHeight: true,
				frame	   : true,
				labelWidth: 100,
				items      : [{
						fieldLabel: ++me.panelNumber + '. Анамнез',
						name: 'Anamnez',
						width: '90%',
						xtype: 'textarea'
				}]
			},
			{
				xtype      : 'fieldset',
				autoHeight: true,
				title	   : ++me.panelNumber + '. Объективные данные',
				frame	   : true,
				items      : [
					{
					layout	   : 'column',
					items: [
						{
							xtype      : 'panel',
							title	   : 'Общее состояние',
							frame	   : true,
							width : '25%',
							height : 200,
							items : [{
									columns: 1,
									vertical: true,
									width: '100%',
									xtype: 'checkboxgroup',
									singleValue: true,
									items: this.getCombo('Condition_id')
							}]
						}, {
							xtype      : 'panel',
							title	   : 'Поведение',
							frame	   : true,
							width : '25%',
							height : 200,
							items : [{
									columns: 1,
									vertical: true,
									width: '100%',
									xtype: 'checkboxgroup',
									singleValue: true,
									items: this.getCombo('Behavior_id')
							}]
						}, {
							xtype      : 'panel',
							title	   : 'Сознание',
							frame	   : true,
							width : '25%',
							height : 200,
							items : [{
									columns: 1,
									vertical: true,
									width: '100%',
									xtype: 'checkboxgroup',
									singleValue: true,
									items: this.getCombo('Cons_id')
							}, {
								xtype      : 'fieldset',
								autoHeight: true,
								labelWidth: 150,
								items : [{
									fieldLabel: 'Менингеальные знаки',
									hiddenName: 'isMenen',
									width: 40,
									comboSubject: 'YesNo',
									xtype: 'swcommonsprcombo'
								}]
							}]
						}, {
							xtype      : 'panel',
							title	   : 'Зрачки',
							frame	   : true,
							width : '25%',
							height : 200,
							items : [{
									columns: 3,
									vertical: true,
									width: '100%',
									xtype: 'checkboxgroup',
									singleValue: true,
									items: this.getCombo('Pupil_id')
							}, {
								xtype      : 'fieldset',
								autoHeight: true,
								labelWidth: 100,
								items : [{
									fieldLabel: 'Нистагм',
									hiddenName: 'isNist',
									width: 40,
									comboSubject: 'YesNo',
									xtype: 'swcommonsprcombo'
								}]
							}, {
								xtype      : 'fieldset',
								autoHeight: true,
								labelWidth: 100,
								items : [{
									fieldLabel: 'Анизокория',
									hiddenName: 'isAnis',
									width: 40,
									comboSubject: 'YesNo',
									xtype: 'swcommonsprcombo'
								}]
							}, {
								xtype      : 'fieldset',
								autoHeight: true,
								labelWidth: 100,
								items : [{
									fieldLabel: 'Реакция на свет',
									hiddenName: 'isLight',
									width: 40,
									comboSubject: 'YesNo',
									xtype: 'swcommonsprcombo'
								}]
							}]
						}, {
							xtype      : 'panel',
							title	   : 'Кожные покровы',
							frame	   : true,
							width : '25%',
							height : 200,
							items : [{
									columns: 2,
									vertical: true,
									width: '100%',
									xtype: 'checkboxgroup',
									items: this.getCombo('Kozha_id')
							}, {
								xtype      : 'fieldset',
								autoHeight: true,
								labelWidth: 80,
								items : [{
									fieldLabel: 'Акроцианоз',
									width: 50,
									hiddenName: 'isAcro',
									comboSubject: 'YesNo',
									xtype: 'swcommonsprcombo'
								}]
							}, {
								xtype      : 'fieldset',
								autoHeight: true,
								labelWidth: 80,
								items : [{
									fieldLabel: 'Мраморность',
									width: 50,
									hiddenName: 'isMramor',
									comboSubject: 'YesNo',
									xtype: 'swcommonsprcombo'
								}]
							}]
						}, {
							xtype      : 'panel',
							title	   : 'Отеки',
							frame	   : true,
							width : '25%',
							height : 200,
							items : [{
									columns: 1,
									vertical: true,
									width: '100%',
									xtype: 'checkboxgroup',
									singleValue: true,
									items: this.getCombo('Hypostas_id')
							}]
						}, {
							xtype      : 'panel',
							title	   : 'Сыпь',
							frame	   : true,
							width : '25%',
							height : 200,
							items : [{
									columns: 1,
									vertical: true,
									width: '100%',
									xtype: 'checkboxgroup',
									singleValue: true,
									items: this.getCombo('Crop_id')
							}]
						}, {
							xtype      : 'panel',
							title	   : 'Дыхание',
							frame	   : true,
							width : '25%',
							height : 200,
							items : [{
									columns: 1,
									vertical: true,
									width: '100%',
									xtype: 'checkboxgroup',
									singleValue: true,
									items: this.getCombo('Hale_id')
							}]
						}, {
							xtype      : 'panel',
							title	   : 'Хрипы',
							frame	   : true,
							width : '25%',
							height : 200,
							items : [{
									columns: 1,
									vertical: true,
									width: '100%',
									xtype: 'checkboxgroup',
									singleValue: true,
									items: this.getCombo('Rattle_id')
							}]
						}, {
							xtype      : 'panel',
							title	   : 'Одышка',
							frame	   : true,
							width : '25%',
							height : 200,
							items : [{
									columns: 1,
									vertical: true,
									width: '100%',
									xtype: 'checkboxgroup',
									singleValue: true,
									items: this.getCombo('Shortwind_id')
							}]
						},
						// Органы системы кровообращения
						{
							xtype      : 'panel',
							title	   : 'Тоны сердца',
							frame	   : true,
							width : '25%',
							height : 200,
							items : [{
									columns: 1,
									vertical: true,
									width: '100%',
									xtype: 'checkboxgroup',
									items: this.getCombo('Heart_id')
							}]
						}, {
							xtype      : 'panel',
							title	   : 'Шум',
							frame	   : true,
							width : '25%',
							height : 200,
							items : [{
									columns: 1,
									vertical: true,
									width: '100%',
									xtype: 'checkboxgroup',
									items: this.getCombo('Noise_id')
							}]
						}, {
							xtype      : 'panel',
							title	   : 'Пульс',
							frame	   : true,
							width : '25%',
							height : 200,
							items : [{
									columns: 2,
									vertical: true,
									width: '100%',
									xtype: 'checkboxgroup',
									singleValue: true,
									items: this.getCombo('Pulse_id')
							}]
						},
						// Органы пищеварения
						{
							xtype      : 'panel',
							title	   : 'Язык',
							frame	   : true,
							width : '25%',
							height : 200,
							items : [{
									columns: 1,
									vertical: true,
									width: '100%',
									xtype: 'checkboxgroup',
									items: this.getCombo('Lang_id')
							}]
						}, {
							xtype      : 'panel',
							title	   : 'Живот',
							frame	   : true,
							width : '25%',
							height : 200,
							items : [{
									columns: 2,
									vertical: true,
									width: '100%',
									xtype: 'checkboxgroup',
									items: this.getCombo('Gaste_id')
							}, {
								xtype      : 'fieldset',
								labelWidth: 160,
								autoHeight: true,
								items : [{
									fieldLabel: 'Участвует в акте дыхания',
									hiddenName: 'isHale',
									comboSubject: 'YesNo',
									width: 40,
									xtype: 'swcommonsprcombo'
								}]
							}, {
								xtype      : 'fieldset',
								labelWidth: 200,
								autoHeight: true,
								items : [{
									fieldLabel: 'Симптомы раздражения брюшины',
									hiddenName: 'isPerit',
									comboSubject: 'YesNo',
									width: 40,
									xtype: 'swcommonsprcombo'
								}]
							}]
						}, {
							xtype      : 'panel',
							title	   : 'Печень',
							frame	   : true,
							width : '25%',
							height : 200,
							items : [{
									columns: 1,
									vertical: true,
									width: '100%',
									xtype: 'checkboxgroup',
									singleValue: true,
									items: this.getCombo('Liver_id')
							}]
						}]
					}, {
						height: 20
					}, {
							fieldLabel: 'Мочеиспускание',
							name: 'Urine',
							width: 400,
							xtype: 'textfield'
					}, {
							fieldLabel: 'Стул',
							name: 'Shit',
							xtype: 'textfield'
					}, {
							fieldLabel: 'Другие симптомы',
							name: 'OtherSympt',
							width: 400,
							xtype: 'textarea'
					}, {
							fieldLabel: 'Дополнительные данные',
							name: 'CmpCloseCard_AddInfo',
							width: 400,
							xtype: 'textarea'
					},

					{
							xtype: 'container',
							autoEl: {},
							layout: 'column',
							items:
							[
								{
									xtype: 'fieldset',
									border: false,
									autoHeight: true,
									width: 315,
									labelWidth : 220,
									items: [{
										fieldLabel: 'Рабочее АД, мм.рт.ст.',
										name: 'sub1WorkAD',
										width: 60,
										xtype: 'textfield',
										maskRe: /\d/,
										maxLength:3,
										listeners: {
											'blur': function(me){
												var baseform = this.FormPanel.getForm(),
													workadfield = baseform.findField('WorkAD'),
													workad2field = baseform.findField('sub2WorkAD');

												workadfield.setValue(me.getValue()+'/'+workad2field.getValue());
											}.createDelegate(this)
										}
									}]
								},
								{
									xtype: 'label',
									text: '/'
								},
								{
									xtype: 'textfield',
									name: 'sub2WorkAD',
									width: 65,
									maskRe: /\d/,
									maxLength:3,
									style: 'margin: 0 0 0 10px;',
									listeners: {
										'blur': function(me){
											var baseform = this.FormPanel.getForm(),
												workadfield = baseform.findField('WorkAD'),
												workad1field = baseform.findField('sub1WorkAD');

											workadfield.setValue(workad1field.getValue()+'/'+me.getValue());
										}.createDelegate(this)
									}
								}
							]
					},
					{
							name: 'WorkAD',
							xtype: 'hidden'

					},

					{
						xtype: 'container',
						autoEl: {},
						layout: 'column',
						items:
						[
							{
								xtype: 'fieldset',
								border: false,
								autoHeight: true,
								width: 315,
								labelWidth : 220,
								items: [{
									fieldLabel: 'АД, мм.рт.ст.',
									name: 'sub1AD',
									width: 60,
									xtype: 'textfield',
									maskRe: /\d/,
									maxLength:3,
									listeners: {
										'blur': function(me){
											var baseform = this.FormPanel.getForm(),
												workadfield = baseform.findField('AD'),
												workad2field = baseform.findField('sub2AD');

											workadfield.setValue(me.getValue()+'/'+workad2field.getValue());
										}.createDelegate(this)
									}
								}]
							},
							{
								xtype: 'label',
								text: '/'
								//style: 'padding: 0 10px;'
							},
							{
								xtype: 'textfield',
								name: 'sub2AD',
								width: 65,
								maskRe: /\d/,
								maxLength:3,
								style: 'margin: 0 0 0 10px;',
								listeners: {
									'blur': function(me){
										var baseform = this.FormPanel.getForm(),
											workadfield = baseform.findField('AD'),
											workad1field = baseform.findField('sub1AD');

										workadfield.setValue(workad1field.getValue()+'/'+me.getValue());
									}.createDelegate(this)
								}
							}
						]
					},
					{
							fieldLabel: 'АД, мм.рт.ст.',
							name: 'AD',
							xtype: 'hidden'
					},{
							fieldLabel: 'ЧСС, мин.',
							name: 'Chss',
							xtype: 'textfield',
							maskRe: /\d/,
							maxLength:3
					}, {
							fieldLabel: 'Пульс, уд/мин',
							name: 'Pulse',
							xtype: 'textfield',
							maskRe: /\d/,
							maxLength:3
					}, {
							fieldLabel: 'Температура',
							name: 'Temperature',
							xtype: 'textfield',
							plugins: [ new Ext.ux.InputTextMask('99.9', true) ]
					}, {
							fieldLabel: 'ЧД, мин.',
							name: 'Chd',
							xtype: 'textfield',
							maskRe: /\d/,
							maxLength:3
					}, {
							fieldLabel: 'Пульсоксиметрия',
							name: 'Pulsks',
							xtype: 'textfield',
							maskRe: /\d/,
							maxLength:3
					}, {
							fieldLabel: 'Глюкометрия',
							name: 'Gluck',
							xtype: 'textfield',
							plugins: [ new Ext.ux.InputTextMask('99.9', true) ]
					}, {
							fieldLabel: 'Дополнительные объективные данные. Локальный статус.',
							name: 'LocalStatus',
							width: 400,
							xtype: 'textarea'
					}, {
							fieldLabel: 'ЭКГ до оказания медицинской помощи',
							name: 'Ekg1',
							width: 90,
							xtype: 'textfield'
					}, {
							fieldLabel: 'ЭКГ до оказания медицинской помощи (время)',
							name: 'Ekg1Time',
							plugins: [ new Ext.ux.InputTextMask('99:99', true) ],
							validateOnBlur: false,
							width: 90,
							xtype: 'swtimefield'
					}, {
							fieldLabel: 'ЭКГ после оказания медицинской помощи',
							name: 'Ekg2',
							width: 90,
							xtype: 'textfield'
					}, {
							fieldLabel: 'ЭКГ после оказания медицинской помощи (время)',
							name: 'Ekg2Time',
							plugins: [ new Ext.ux.InputTextMask('99:99', true) ],
							validateOnBlur: false,
							width: 90,
							xtype: 'swtimefield'
					}
				]
			}
		]
	},
	//получение списка компонентов для вкладки Диагноз
	getDiagnozFields: function(){
		var me = this;
		this.diag_sid_panel = new sw.Promed.swMultiFieldPanel({
			label: langs('Сопутствующий диагноз'),
			deleteBtnText: '',
			id: 'diag_sid_panel',
			panelFiledName: 'Diag_sid',
			hiddenDelAll: true,
			firstColWidth: 434,
			//border: false,
			frame: false,
			buttons: [{
				style: 'margin-left: 97px;',
				handler: function() {
					this.ownerCt.addField();
				},
				iconCls: 'add16',
				text: langs('Добавить')
			}, {
				text: '-'
			}],
			createField: function (counter) {
				var conf_combo = {
					value: null,
					fieldLabel: langs('Сопутствующий диагноз'),
					hiddenName: 'Diag_sid'+counter,
					labelStyle: 'width: 141px;',
					width: 290,
					checkAccessRights: true,
					xtype: 'swdiagcombo',
					autoShow: true,
					withGroups: false,
					disabledClass: 'field-disabled',
					MKB: {
						isMain: true
					}
				};

				if (this.firstTabIndex) {
					conf_combo.tabIndex = this.firstTabIndex + this.lastItemsIndex;
				}
				if (this.PrescriptionType_Code) {
					conf_combo.PrescriptionType_Code = this.PrescriptionType_Code;
					var c = new sw.Promed.SwDiagCombo(conf_combo);
				} else {
					conf_combo.onTrigger2Click = function () {
						if (this.disabled) {
							return;
						}
						this.clearValue();
						this.fireEvent('change', this, this.getValue());
					};

					var c = new sw.Promed.SwDiagCombo(conf_combo);
				}
				return c;
			},
			onFieldAdd: function (data) {
				if(data.value > 0){
					data.getStore().load({
						scope: data,
						params: {where: "where Diag_id = " + data.value},
						callback: function() {
							this.setValue(data.value);
						}
					})
				};
			},
			onFieldDelete: function (data) {},
			onResetPanel: function () {},
			getData: function () {
				var res_arr = new Array();
				var arrfields = this.findByType('swdiagcombo');
				for(var i=0;i<arrfields.length;i++){
					if(!Ext.isEmpty(arrfields[i].getValue()))
						res_arr.push(arrfields[i].getValue());
				}
				return res_arr;
			},
			getIDsAndCodes: function () {
				var res_arr = new Array();
				var arrfields = this.findByType('swdiagcombo');
				for(var i=0;i<arrfields.length;i++){
					var field = arrfields[i];
					var rec = field.getStore().getById(arrfields[i].getValue());
					if(rec){
						var diag_code = rec.get('Diag_Code').substr(0, 3); }
					if(!Ext.isEmpty(arrfields[i].getValue()))
						res_arr.push({id: arrfields[i].getValue(), code: diag_code});
				}
				return res_arr;
			}
		});
		this.diag_ooid_panel = new sw.Promed.swMultiFieldPanel({
			label: langs('Осложнение основного'),
			deleteBtnText: '',
			id: 'diag_ooid_panel',
			hiddenDelAll: true,
			panelFiledName: 'Diag_ooid',
			firstColWidth: 434,
			frame: false,
			buttons: [{
				style: 'margin-left: 97px;',
				handler: function() {
					this.ownerCt.addField();
				},
				iconCls: 'add16',
				text: langs('Добавить')
			}, {
				text: '-'
			}],
			createField: function (counter) {
				var conf_combo = {
					value: null,
					fieldLabel: langs('Осложнение основного'),
					hiddenName: 'Diag_ooid'+counter,
					labelStyle: 'width: 141px;',
					width: 290,
					checkAccessRights: true,
					xtype: 'swdiagcombo',
					autoShow: true,
					withGroups: false,
					disabledClass: 'field-disabled',
					MKB: {
						isMain: true
					}
				};

				if (this.firstTabIndex) {
					conf_combo.tabIndex = this.firstTabIndex + this.lastItemsIndex;
				}
				if (this.PrescriptionType_Code) {
					conf_combo.PrescriptionType_Code = this.PrescriptionType_Code;
					var c = new sw.Promed.SwDiagCombo(conf_combo);
				} else {
					conf_combo.onTrigger2Click = function () {
						if (this.disabled) {
							return;
						}
						this.clearValue();
						this.fireEvent('change', this, this.getValue());
					};

					var c = new sw.Promed.SwDiagCombo(conf_combo);
				}
				return c;
			},
			onFieldAdd: function (data) {
				if(data.value > 0){
					data.getStore().load({
						scope: data,
						params: {where: "where Diag_id = " + data.value},
						callback: function() {
							this.setValue(data.value);
						}
					})
				};
			},
			onFieldDelete: function (data) {
			},
			onResetPanel: function () {
			},
			getData: function () {
				var res_arr = new Array();
				var arrfields = this.findByType('swdiagcombo');
				for(var i=0;i<arrfields.length;i++){
					if(!Ext.isEmpty(arrfields[i].getValue()))
						res_arr.push(arrfields[i].getValue());
				}
				return res_arr;
			}
		});
		return [
			{
				xtype      : 'panel',
				title	   : ++me.panelNumber + '. Диагноз',
				cls		   : 'diags-panel-close-card',
				frame	   : true,
				refId: 'diagPanel',
				labelWidth: 200,
				items: [{
					columns: 2,
					layout	   : 'column',
					width:'100%',
					items: [
						{
							xtype: 'fieldset',
							style: "padding-left: 50px;",
							border: false,
							autoHeight: true,
							width: 700,
							items:[
								{
									checkAccessRights: true,
									hiddenName: 'Diag_id',
									xtype: 'swdiagcombo',
									allowBlank: false,
									labelStyle: 'width: 50px;',
									width: 274,
									withGroups: getGlobalOptions().region.nick.inlist(['perm']),
									disabledClass: 'field-disabled',
									MKB: {
										isMain: true
									},
									listeners: {
										/*select: function(){
											me.checkEmergencyStandart();
										}
										*/
										/*change: function(combo, newValue){

											var diag_uid = me.FormPanel.getForm().findField('Diag_uid');

											if( (newValue.length == 0) && diag_uid){

												diag_uid.clearValue();
												diag_uid.setDisabled(true);
											};

											combo.setValue(newValue);

										},

										select: function(combo, select_item){

											var diag_uid = me.FormPanel.getForm().findField('Diag_uid');

											if (diag_uid) {
												if(select_item.get('DiagLevel_id') == 3){
													diag_uid.setDisabled(false);
													diag_uid.Diag_level3_code =  select_item.get('Diag_Code');
													diag_uid.doQuery(); //обновляем данные поля "Уточненный диагноз"
												}else{
													diag_uid.clearValue();
													diag_uid.setDisabled(true);
												}
											};

										}
										*/
									}
								},
								this.diag_sid_panel,
								this.diag_ooid_panel
							]
						}
					]
				}]
			},

/*
			// TODO: код МКБ-10
			{
				xtype      : 'panel',
				title	   : ++me.panelNumber + '. Осложнения',
				frame	   : true,
				items      : [{
						columns: 3,
						vertical: true,
						width: '100%',
						xtype: 'checkboxgroup',
						items: this.getCombo('Complicat_id')
				}]
			},*/
            {
                xtype      : 'panel',
                title	   : ++me.panelNumber + '. Осложнения',
                frame	   : true,
                items      : [{
                    columns: [400,400,400],
                    vertical: true,
                    width: '100%',
                    name: 'GroupComplicat',
                    xtype: 'checkboxgroup',
                    items: this.getCombo('Complicat_id')
                }]
            },
			{
				xtype      : 'panel',
				title	   : ++me.panelNumber + '. Эффективность мероприятий при осложнении',
				frame	   : true,
				items      : [{
						columns: 3,
						vertical: true,
						width: '100%',
						xtype: 'checkboxgroup',
						singleValue: true,
						items :this.getCombo('ComplicatEf_id')
				}]
			}
		]
	},
	//получение списка компонентов для вкладки Манипуляции
	getProcedureFields: function(){
		var me = this;

        /*
		me.UslugaPanel = new Ext.Panel({
			//title: ++me.panelNumber + '. Услуги тестовые не удалаять',
			//layout:'table',
			cls: 'uslugaPanel',
			defaults: {				
				bodyStyle:'padding:20px',
				labelWidth: 150
			},
			layoutConfig: {
				//columns: 6
			},
			items: []
		});
		*/

		return [
			{
				xtype      : 'panel',
				title	   : ++me.panelNumber + '. Оказанная помощь на месте вызова',
				frame	   : true,
				items      : [{
						name: 'HelpPlace',
						width: '99%',
						xtype: 'textarea'
				}]
			},
			{
				xtype      : 'panel',
				title	   : ++me.panelNumber + '. Оказанная помощь в автомобиле скорой медицинской помощи',
				frame	   : true,
				items      : [
					{

						xtype: 'fieldset',
						border: false,
						autoHeight: true,
						width: '95%',
						labelWidth : 100,
						items: [
							{
									name: 'HelpAuto',
									width: '99%',
									xtype: 'textarea',
									fieldLabel: 'Оказанная помощь'
							}
						]
					}
				]
			},
			{
				xtype      : 'panel',
				title	   : ++me.panelNumber + '. Эффективность проведенных мероприятий',
				frame	   : true,
				layout	   : 'form',
				items :[
					{
						xtype: 'container',
						autoEl: {},
						layout: 'column',
						items:
						[
							{
								xtype: 'container',
								autoEl: {},
								layout: 'column',
								columnWidth: .25,
								items: [
								{
									xtype: 'fieldset',
									border: false,
									autoHeight: true,
									//width: 310,
									labelWidth : 120,
									items: [{
										fieldLabel: 'АД, мм.рт.ст.',
										name: 'sub1EAD',
										width: 50,
										xtype: 'numberfield',
										validator: function(a){return (a.match(/^[1-9]\d*$/))?true:false;},
										maxLength:3,
										listeners: {
											'blur': function(me){
												var baseform = this.FormPanel.getForm(),
													workadfield = baseform.findField('EfAD'),
													workad2field = baseform.findField('sub2EAD');

												workadfield.setValue(me.getValue()+'/'+workad2field.getValue());
											}.createDelegate(this)
										}
									}]
								},
								{
									xtype: 'label',
									text: '/',
									style: 'padding: 0 0 0 10px;'
								},
								{
									xtype: 'numberfield',
									name: 'sub2EAD',
									width: 55,
									validator: function(a){return (a.match(/^[1-9]\d*$/))?true:false;},
									maxLength:3,
									style: 'margin: 0 0 0 10px;',
									listeners: {
										'blur': function(me){
											var baseform = this.FormPanel.getForm(),
												workadfield = baseform.findField('EfAD'),
												workad1field = baseform.findField('sub1EAD');

											workadfield.setValue(workad1field.getValue()+'/'+me.getValue());
										}.createDelegate(this)
									}
								}
								]
							},
							{
								fieldLabel: 'АД, мм.рт.ст.',
								name: 'EfAD',
								xtype: 'hidden'
							},
							{
								xtype: 'fieldset',
								autoHeight: true,
								labelWidth: "150px",
								columnWidth: .25,
								border: false,
								items: [{
									fieldLabel: 'Температура',
									name: 'EfTemperature',
									xtype: 'textfield',
									plugins: [ new Ext.ux.InputTextMask('99.9', true) ]
								}]
							},
							{
								xtype: 'fieldset',
								autoHeight: true,
								labelWidth: "150px",
								columnWidth: .25,
								border: false,
								items: [{
										fieldLabel: 'ЧСС, мин.',
										name: 'EfChss',
										xtype: 'numberfield',
										//maskRe: /\d/,
										maxLength:3,
										allowDecimals: false,
										validator: function(a){return (a.match(/^[1-9]\d*$/))?true:false;}
									}]
							}, {
								xtype: 'fieldset',
								autoHeight: true,
								labelWidth: "150px",
								columnWidth: .25,
								border: false,
								items: [{
										fieldLabel: 'Пульс, уд/мин',
										name: 'EfPulse',
										xtype: 'numberfield',
										validator: function(a){return (a.match(/^[1-9]\d*$/))?true:false;},
										maxLength:3,
										allowDecimals: false
									}]
							}
						]
					},

					{
						xtype: 'container',
						autoEl: {},
						layout: 'column',
						items:
						[
							{
								xtype: 'fieldset',
								autoHeight: true,
								labelWidth: "150px",
								columnWidth: .25,
								border: false,
								items: [{
										fieldLabel: 'ЧД, мин.',
										name: 'EfChd',
										xtype: 'numberfield',
										validator: function(a){return (a.match(/^[1-9]\d*$/))?true:false;},
										maxLength:3	,
										allowDecimals: false
									}]
							}, {
								xtype: 'fieldset',
								autoHeight: true,
								labelWidth: "150px",
								columnWidth: .25,
								border: false,
								items: [{
										fieldLabel: 'Пульсоксиметрия',
										name: 'EfPulsks',
										xtype: 'numberfield',
										validator: function(a){return (a.match(/^[1-9]\d*$/))?true:false;},
										maxLength:3
									}]
							}, {
								xtype: 'fieldset',
								autoHeight: true,
								labelWidth: "150px",
								columnWidth: .25,
								border: false,
								items: [{
									fieldLabel: 'Глюкометрия',
									name: 'EfGluck',
									xtype: 'textfield',
									plugins: [ new Ext.ux.InputTextMask('99.9', true) ]
								}]
							}
						]
					}
				]
			},
			{
				xtype      : 'fieldset',
				hidden: !getRegionNick().inlist(['perm']),
				autoHeight: true,
				title	   : ++me.panelNumber + '. Вид оплаты',
				labelWidth : 150,
				frame	   : true,
				items : [
					{
						name: 'PayType_id',
						xtype: 'swpaytypecombo',
						lastQuery: '',
						allowBlank:false,
						enableKeyEvents: true,
						labelWidth : 100,
						listWidth: 300,
						listeners: {
							select: function (cmp, rec) {
								var code = rec.data.PayType_Code, //10 - МБТ (незастрахованные), 11 - МБТ (СЗЗ)
									uslugaPanel = me.FormPanel.find('refId', 'uslugaGrid'),
									form = me.FormPanel.getForm(),
									cardId = form.findField('CmpCallCard_id').getValue(),
									acceptTime = form.findField('AcceptTime').getValue(),
									payType;

								Date.parse(acceptTime, 'd.m.Y H:i')? acceptTime = Date.parse(acceptTime, 'd.m.Y H:i'): null;

								if (code.inlist([10, 11])) {
									payType = code;
								}

								if (uslugaPanel && uslugaPanel[0]){
									uslugaPanel[0].store.load({
										params: {
											CmpCallCard_id: +cardId? +cardId: null,
											acceptTime: new Date(acceptTime) instanceof Date? Ext.util.Format.date( new Date(acceptTime), 'd.m.Y H:i'): Ext.util.Format.date( new Date(), 'd.m.Y H:i'),
											PayType_Code: payType? payType: null
										}
									});
								}
							}
						}
					}
				]
			},
            me.getUslugaPanel(++me.panelNumber)
			/*
            {
				xtype      : 'panel',
				autoHeight: true,
				border: true,
				collapsible: true,
				layout: 'form',
				style: 'margin-bottom: 0.5em;',
				title: ++me.panelNumber + '. Услуги',
				items: [me.UslugaPanel]
			}
			*/
		];

		//@todo
		//для бурятов и карелов условие

		//для Пскова условие
	},
	//получение списка компонентов для вкладки Результат
	getResultFields: function(){
		var me = this;

		return [
			{
				xtype      : 'fieldset',
				autoHeight: true,
				labelWidth: 500,
				items : [{
					fieldLabel: ++me.panelNumber + '. Согласие на медицинское вмешательство',
					hiddenName: 'isSogl',
					width: 40,
					comboSubject: 'YesNo',
					xtype: 'swcommonsprcombo'
				}, {
					fieldLabel: ++me.panelNumber + '. Отказ от медицинского вмешательства',
					hiddenName: 'isOtkazMed',
					width: 40,
					comboSubject: 'YesNo',
					xtype: 'swcommonsprcombo'
				}, {
					fieldLabel: ++me.panelNumber + '. Отказ от транспортировки для госпитализации в стационар',
					hiddenName: 'isOtkazHosp',
					width: 40,
					comboSubject: 'YesNo',
					xtype: 'swcommonsprcombo'
				}
				/*,{
					fieldLabel: 'Отказ от подписи',
					hiddenName: 'isOtkazSign',
					width: 40,
					comboSubject: 'YesNo',
					xtype: 'swcommonsprcombo'
				},
				{
					fieldLabel: 'Причина отказа от подписи',
					name: 'OtkazSignWhy',
					width: 90,
					xtype: 'textfield'
				}
				*/
				]
			},
			{
				xtype      : 'panel',
				title	   : ++me.panelNumber + '. Результат оказания скорой медицинской помощи',
				frame	   : true,
				hiddenName: 'smpResultPanel',
				items      : [{
					columns: 3,
					vertical: true,
					width: '100%',
					allowBlank: false,
					disabledClass: 'field-disabled',
					xtype: 'checkboxgroup',
					singleValue: true,
					name: 'Result_id',
					items :this.getCombo('Result_id')
				}]
			}, {
				xtype      : 'panel',
				title	   : ++me.panelNumber + '. Больной',
				frame	   : true,
				items      : [
					{
						columns: [600],
						width: '100%',
						vertical: true,
						name: 'Patient_id',
						xtype: 'checkboxgroup',
						singleValue: true,
						items : this.getCombo('Patient_id')
					}
				]
			}, {
				xtype      : 'panel',
				title	   : ++me.panelNumber + '. Способ доставки больного в автомобиль скорой медицинской помощи',
				frame	   : true,
				items      : [{
						columns: 3,
						vertical: true,
						width: '100%',
						xtype: 'checkboxgroup',
						singleValue: true,
						items :this.getCombo('TransToAuto_id')
				}]
			}, {
				xtype      : 'panel',
				title	   : ++me.panelNumber + '. Результат выезда',
				frame	   : true,
				items : [{
					columns: [600],
					vertical: true,
					width: '100%',
					xtype: 'checkboxgroup',
					singleValue: true,
					name: 'ResultEmergencyTrip',
					allowBlank: false,
					items: this.getCombo('ResultUfa_id'),
						listeners:{
							change: function(radiogroup,field,some){

								if(!field && !field[0] && !field[0].code) {
									me.FormPanel.getForm().findField('ResultEmergencyTrip').validate();
									return false;
								}

								//#96465
								/**
								 * поля
								 * 231 - Больной не найден на месте
								 * 232 - Отказ от помощи (от осмотра)
								 * 233 - Адрес не найден
								 * 234 - Ложный вызов
								 * 235 - Смерть до приезда бригады СМП
								 * 236 - Больной увезен до прибытия СМП
								 * 237 - Больной обслужен врачом поликлиники до приезда СМП
								 * 238 - Вызов отменен
								 */
								var code = field[0].code,
									value = field[0].getValue();

								var allowBlankFlag = code.inlist([231, 232, 233, 234, 235, 236, 237, 238]) && value,
									cmpsForDisable = ['patientDataFieldset', 'diagTabPanel', 'medicalActionsTab', 'smpResultPanel', 'CallPovod_id', 'TransportTime', 'ToHospitalTime', 'reasonCheckgroup', 'traumaCheckgroup', 'isAlco'],
									cmpsForReset = ['reasonCheckgroup', 'traumaCheckgroup', 'isAlco', 'CallPovod_id', 'TransportTime', 'ToHospitalTime', 'patientDataFieldset', 'diagTabPanel', 'medicalActionsTab', 'smpResultPanel'],
									tabs = me.tabPanel.items.items,
									allFields = me.getAllFieldsInComponent(me, true),
									doLockHakas = function () {
										var disableFieldsFlag = allowBlankFlag;
										
										//блокируем поля
										cmpsForDisable.forEach(function (cmp, index, array) {
											if (allFields[cmp]){
												cmp.inlist(['TransportTime', 'ToHospitalTime'])? allFields[cmp].ownerCt.setDisabled(disableFieldsFlag): allFields[cmp].setDisabled(disableFieldsFlag);
											}
										});

										//блокируем вкладки
										tabs.forEach(function (cmp, index, array) {
											if (cmp.name && cmp.name.inlist(cmpsForDisable)){
												cmp.setDisabled(disableFieldsFlag);
											}
										});

										//если залочили поля - надо их сбросить
										if (disableFieldsFlag) {
											cmpsForReset.forEach(function (cmp, index, array) {
												if (allFields[cmp]) {
													if (allFields[cmp].getXType().inlist(['swdatetimefield'])) {
														me.clearCmpFields(allFields[cmp]);
													} else {
														allFields[cmp].reset();
													}
												}
											});

											//сбрасываем всё в вкладках
											tabs.forEach(function (cmp, index, array) {
												if (cmp.name && cmp.name.inlist(cmpsForReset)) {
													me.clearCmpFields(cmp);
												}
											});
										}
									};

								if (getRegionNick() == 'khak') {
									if (allowBlankFlag && !(me.action.inlist(['edit', 'view']))) {
										Ext.Msg.show({
											title:'Безрезультатный выезд',
											msg: 'Вы выбрали безрезультатный выезд. Следующие поля Карты вызова будут очищены: «Время начала транспортировки больного», «Время прибытия в медицинскую организацию», «Причина несчастного случая», «Травма», «Наличие клиники опьянения», «Жалобы», «Анамнез», «Объективные данные», «Диагноз», «Осложнения», «Эффективность мероприятий при осложнении», «Оказанная помощь на месте вызова», «Оказанная помощь в автомобиле скорой медицинской помощи», «Эффективность проведенных мероприятий», «Результат оказания медицинской помощи»',
											buttons: Ext.Msg.YESNO,
											icon: Ext.Msg.WARNING,
											fn: function(btn){
												if (btn == 'yes'){
													doLockHakas();
												} else {
													radiogroup.reset();
												}
											}
										});
									} else {
										doLockHakas();
									}
								}

								me.FormPanel.getForm().findField('SocialCombo').allowBlank = allowBlankFlag;
								me.FormPanel.getForm().findField('SocialCombo').validate();

								me.FormPanel.getForm().findField('Diag_id').allowBlank = allowBlankFlag;
								me.FormPanel.getForm().findField('Diag_id').validate();

								me.FormPanel.getForm().findField('Result_id').allowBlank = allowBlankFlag;
								me.FormPanel.getForm().findField('Result_id').validate();

							}.createDelegate(this)
						}
				}]
			}, {
				xtype      : 'fieldset',
				autoHeight: true,
				title	   : ++me.panelNumber + '. Километраж',
				labelWidth : 100,
				frame	   : true,
				items : [{
						layout: 'column',
						id: 'PPW_PeriodFields',
						items: [{
								layout: 'form',
								labelWidth: getRegionNick().inlist(['ufa']) ? 150 : 100,
								items: [{
									allowDecimals: true,
									allowNegative: false,
									fieldLabel: getRegionNick().inlist(['ufa']) ? 'Километраж доезда' : 'Километраж',
									maxValue: 9999.99,
									name: 'CmpCloseCard_UserKilo',
									xtype: 'numberfield',
									msgTarget: 'under',
									width: 150
								}]
						},{
							layout: 'form',
							labelWidth: 140,
							hidden: !getRegionNick().inlist(['ufa']),
							items: [{
								allowDecimals: true,
								allowNegative: false,
								hidden: !getRegionNick().inlist(['ufa']),
								fieldLabel: '/   Общий километраж',
								maxValue: 9999.99,
								name: 'CmpCloseCard_UserKiloCommon',
								xtype: 'numberfield',
								msgTarget: 'under',
								width: 150
							}]
						}]
				}, {
					name: 'Kilo',
					xtype: 'hidden'
				}]
			}, {
				xtype      : 'fieldset',
				autoHeight: true,
				title	   : ++me.panelNumber + '. Примечания',
				labelWidth : 100,
				frame	   : true,
				items : [{
					fieldLabel: 'Примечания',
					name: 'DescText',
					xtype: 'textarea',
					width: '90%'
				}]
			}
		]

		//@todo для Пскова добавление
	},

	//получение списка компонентов для вкладки Медикаменты
	getDrugFields: function(id, callback){
		// форма расхода медикаментов должна зависеть от настроек подразделения, которое выбрано в Карте вызова, а не от АРМа
		var lpuBuilding = id || false;
		if( !lpuBuilding ) return false;

		var me = this;
		
		me.DrugPanel = new Ext.Panel({
			items: []
		});
		
		Ext.Ajax.request({
			params: {LpuBuilding_id: lpuBuilding},
			// url: '/?c=CmpCallCard4E&m=getLpuBuildingOptions',
			url: '/?c=LpuStructure&m=getLpuBuildingData',
				callback: function (obj, success, response) {
				if (success) {
					var response_obj = Ext.util.JSON.decode(response.responseText);

					//если в настройках пришел LpuBuilding_IsWithoutBalance - то сокращ форма
					if(response_obj[0] && response_obj[0].LpuBuilding_IsWithoutBalance && response_obj[0].LpuBuilding_IsWithoutBalance=='true'){
						
						me.DrugGrid = new sw.Promed.ViewFrame({
							actions: [
								{name: 'action_add', handler: function() {me.DrugGrid.editGrid('add')}},
								{name: 'action_edit', handler: function() {me.DrugGrid.editGrid('edit')}},
								{name: 'action_view', handler: function() {me.DrugGrid.editGrid('view')}},
								{name: 'action_delete', handler: function() {me.DrugGrid.deleteRecord()}},
								{name: 'action_refresh', hidden: true},
								{name: 'action_print'}
							],
							autoExpandColumn: 'autoexpand',
							useEmptyRecord: false,
							autoExpandMin: 125,
							autoLoadData: false,
							//hidden: true,
							border: true,
							dataUrl: '/?c=CmpCallCard&m=loadCmpCallCardSimpleDrugList',
							gridHeight: 600,
							height: 600,
							object: 'EvnDrug',
							editformclassname: 'swCmpCallCardSimpleDrugEditWindow',
							id: 'CCCNCC_CmpCallCardEvnDrugGrid',
							paging: false,
							style: 'margin-bottom: 10px',
							stringfields: [
								{name: 'state', type: 'string', header: 'state', hidden: true},
								{name: 'CmpCallCardDrug_SetDatetime', header: 'Дата и время выдачи', width: 150, renderer: function (v, p, r) {
									var dt_str = '';
									if (!Ext.isEmpty(r.get('CmpCallCardDrug_setDate'))) {
										dt_str += r.get('CmpCallCardDrug_setDate')+' ';
									}
									if (!Ext.isEmpty(r.get('CmpCallCardDrug_setTime'))) {
										dt_str += r.get('CmpCallCardDrug_setTime');
									}
									return dt_str;
								}},
								{name: 'DrugNomen_Code', type: 'string', header: 'Код', width: 150},
								{name: 'CmpCallCardDrug_Kolvo', type: 'float', header: 'Количество', width: 50},
								{name: 'GoodsUnit_Name', type: 'string', header: 'Ед.измерения', width: 150},
								{name: 'DrugNomen_Name', type: 'string', header: 'Наименование', id: 'autoexpand'},
								{name: 'MedStaffFact_id', hidden: true},
								{name: 'CmpCallCardDrug_Comment', type: 'string', header: 'Примечание', width: 300},
								{name: 'CmpCallCardDrug_setDate', hidden: true},
								{name: 'CmpCallCardDrug_setTime', hidden: true},
								{name: 'CmpCallCardDrug_id', type: 'int', header: 'ID', key: true},
								{name: 'DrugNomen_id', type: 'int',  hidden: true},
								{name: 'Drug_id', type: 'int',  hidden: true},
								{name: 'GoodsUnit_id', type: 'int',  hidden: true},
							],
							title: null,
							toolbar: true,
							onRowSelect: function(sm, rowIdx, record) {
								this.ViewActions.action_edit.setDisabled(false);
								this.ViewActions.action_delete.setDisabled(false);
								this.ViewActions.action_view.setDisabled(Ext.isEmpty(record.get('CmpCallCardDrug_id')));
							},
							editGrid: function (action) {
								if (action == null) {
									action = 'add';
								}

								var base_form = me.FormPanel.getForm(),
									view_frame = this,
									store = view_frame.getGrid().getStore();

								//регионы где в карте вызова не бригада, а врач - поля EmergencyTeam_id не будет
								if (base_form.findField('EmergencyTeam_id'))
									var EmergencyTeam_id = base_form.findField('EmergencyTeam_id').getValue();

								//регионы где в карте вызова не бригада, а врач - поля EmergencyTeamNum не будет
								if (base_form.findField('EmergencyTeamNum'))
									var EmergencyTeam_Name = base_form.findField('EmergencyTeamNum').getValue();

								if (action == 'add') {

									var drugGridActions = view_frame.actions;
									var record_count = store.getCount();

									if ( record_count == 1 && !store.getAt(0).get('CmpCallCardDrug_id') ) {
										//view_frame.removeAll({addEmptyRecord: false});
										record_count = 0;
									}

									var params = new Object();

									params.EmergencyTeam_id = EmergencyTeam_id;
									params.EmergencyTeam_Name = EmergencyTeam_Name;
									params.CmpCallCard_id = base_form.findField('CmpCallCard_id').getValue();
									params.Lpu_id = getGlobalOptions().lpu_id,
									params.action = action,

									params.onSave = function(data) {

										if ( record_count == 1 && !store.getAt(0).get('CmpCallCardDrug_id') ) {
											view_frame.removeAll({addEmptyRecord: false});
										}

										var MedStaffFact_id = base_form.findField('MedStaffFact_id');

										if(MedStaffFact_id && MedStaffFact_id.getValue()){
											data.MedStaffFact_id = base_form.findField('MedStaffFact_id').getValue()
										};

										var record = new Ext.data.Record.create(view_frame.jsonData['store']);
										view_frame.clearFilter();

										data.CmpCallCardDrug_id = Math.floor(Math.random()*10000); //генерируем временный идентификатор
										data.state = 'add';

										store.insert(record_count, new record(data));
										view_frame.setFilter();
									}.createDelegate(this);


                                    //var t = base_form.findField('ArriveTime').getValue();
                                    var t = base_form.findField('ArriveTime').value;

                                    if(t){
                                        var d = new Date(t);
                                        if(getRegionNick().inlist(['ufa'])) d.setMinutes(d.getMinutes()+1);

                                        params.EvnDrug_setDate = d.format('d.m.Y');
                                        params.EvnDrug_setTime = d.format('H:i');
                                    }
/*
                                    var a_date = base_form.findField('ArriveTime').getStringValue();

									if (!Ext.isEmpty(a_date)) {
										var a_date_arr = a_date.split(' ');
										params.EvnDrug_setDate = a_date_arr.length == 2 ? a_date_arr[0] : null;
										params.EvnDrug_setTime = a_date_arr.length == 2 ? a_date_arr[1] : null;
									}
*/
									getWnd(view_frame.editformclassname).show(params);
								}
								if (action == 'edit' || action == 'view') {

									var selected_record = view_frame.getGrid().getSelectionModel().getSelected();
									if (selected_record.get('CmpCallCardDrug_id') > 0) {

										var params = {},
											selectedRecordFields = Object.keys(selected_record.data),
											dontPassToForm = [
												'state',
												'CmpCallCardDrug_SetDatetime',
												'DrugNomen_Code',
												'DrugNomen_Name',
											];

										selectedRecordFields.forEach(function(fieldName){
											if (!(dontPassToForm.indexOf(fieldName) != -1)) {
												params[fieldName] = selected_record.data[fieldName];
											}
										})

										params.EmergencyTeam_id = EmergencyTeam_id;
										params.CmpCallCard_id = base_form.findField('CmpCallCard_id').getValue();
										params.Lpu_id = getGlobalOptions().lpu_id,
										params.action = action,
										params.onSave = function(data) {

											view_frame.clearFilter();

											for(var key in data) {
												selected_record.set(key, data[key]);
											}

											if (selected_record.get('state') != 'add') {
												selected_record.set('state', 'edit');
											}

											selected_record.commit();
											view_frame.setFilter();
										}

										getWnd(view_frame.editformclassname).show(params);
									}
								}
							},
							deleteRecord: function(){

								var view_frame = this;
								var grid = view_frame.getGrid();
								var selected_record = view_frame.getGrid().getSelectionModel().getSelected();

								sw.swMsg.show({
									icon: Ext.MessageBox.QUESTION,
									msg: langs('Вы хотите удалить запись?'),
									title: langs('Подтверждение'),
									buttons: Ext.Msg.YESNO,
									fn: function(buttonId, text, obj) {

										if ('yes' == buttonId) {
											if (selected_record.get('state') == 'add') {
												grid.getStore().remove(selected_record);
											} else {
												selected_record.set('state', 'delete');
												selected_record.commit();
												view_frame.setFilter();
											}
										} else {
											if (grid.getStore().getCount()>0) {
												grid.getView().focusRow(0);
											}
										}
									}
								});
							},
							getChangedData: function(){ //возвращает новые и измненные показатели
								var data = new Array();
								this.clearFilter();
								this.getGrid().getStore().each(function(record) {
									if (record.data.state == 'add' || record.data.state == 'edit' ||  record.data.state == 'delete') {
										data.push(record.data);
									}
								});
								this.setFilter();
								return data;
							},
							getJSONChangedData: function(){ //возвращает новые и измненные записи в виде закодированной JSON строки
								var dataObj = this.getChangedData();
								return dataObj.length > 0 ? Ext.util.JSON.encode(dataObj) : "";
							},
							clearFilter: function() { //очищаем фильтры (необходимо делать всегда перед редактированием store)
								this.getGrid().getStore().clearFilter();
							},
							setFilter: function() { //скрывает удаленные записи
								this.getGrid().getStore().filterBy(function(record){
									return (record.get('state') != 'delete');
								});
							}
						});
					}
					else{
						
						me.DrugGrid = new sw.Promed.ViewFrame({
							actions: [
								{name: 'action_add', handler: function() {me.DrugGrid.editGrid('add')}},
								{name: 'action_edit', handler: function() {me.DrugGrid.editGrid('edit')}},
								{name: 'action_view', handler: function() {me.DrugGrid.editGrid('view')}},
								{name: 'action_delete', handler: function() {me.DrugGrid.deleteRecord()}},
								{name: 'action_refresh', hidden: true},
								{name: 'action_print'}
							],
							autoExpandColumn: 'autoexpand',
							useEmptyRecord: false,
							autoExpandMin: 125,
							autoLoadData: false,
							//hidden: true,
							border: true,
							dataUrl: '/?c=CmpCallCard&m=loadCmpCallCardDrugList',
							gridHeight: 600,
							height: 600,
							object: 'CmpCallCardDrug',
							editformclassname: 'swCmpCallCardDrugEditWindow',
							id: 'CCCNCC_CmpCallCardDrugGrid',
							paging: false,
							style: 'margin-bottom: 10px',
							stringfields: [
								{name: 'CmpCallCardDrug_id', type: 'int', header: 'ID', key: true},
								{name: 'state', type: 'string', header: 'state', hidden: true},
								{name: 'Contragent_id', hidden: true},
								{name: 'Lpu_id', hidden: true},
								{name: 'Org_id', hidden: true},
								{name: 'MedStaffFact_id', hidden: true},
								{name: 'CmpCallCardDrug_setDate', hidden: true},
								{name: 'CmpCallCardDrug_setTime', hidden: true},
								{name: 'LpuBuilding_id', hidden: true},
								{name: 'Storage_id', hidden: true},
								{name: 'StorageZone_id', hidden: true},
								{name: 'Mol_id', hidden: true},
								{name: 'DrugPrepFas_id', hidden: true},
								{name: 'Drug_id', hidden: true},
								{name: 'DrugFinance_id', hidden: true},
								{name: 'WhsDocumentCostItemType_id', hidden: true},
								{name: 'CmpCallCardDrug_Cost', hidden: true},
								{name: 'CmpCallCardDrug_Kolvo', hidden: true},
								{name: 'GoodsUnit_id', hidden: true},
								{name: 'GoodsUnit_bid', hidden: true},
								{name: 'CmpCallCardDrug_KolvoUnit', header: 'Дата и время выдачи', width: 150, renderer: function (v, p, r) {
									var dt_str = '';
									if (!Ext.isEmpty(r.get('CmpCallCardDrug_setDate'))) {
										dt_str += r.get('CmpCallCardDrug_setDate')+' ';
									}
									if (!Ext.isEmpty(r.get('CmpCallCardDrug_setTime'))) {
										dt_str += r.get('CmpCallCardDrug_setTime');
									}
									return dt_str;
								}},
								{name: 'DrugNomen_Code', type: 'string', header: 'Код', width: 150},
								{name: 'CmpCallCardDrug_KolvoUnit', type: 'float', header: 'Количество', width: 150},
								{name: 'CmpCallCardDrug_Sum', hidden: true},
								{name: 'DocumentUc_id', hidden: true},
								{name: 'DocumentUcStr_id', hidden: true},
								{name: 'DocumentUcStr_oid', hidden: true},
								{name: 'PrepSeries_id', hidden: true},
								{name: 'DrugDocumentStatus_Code', hidden: true},
								{name: 'GoodsUnit_Name', type: 'string', header: 'Ед.списания', width: 150},
								{name: 'Drug_Name', type: 'string', header: 'Наименование', id: 'autoexpand'}
							],
							title: null,
							toolbar: true,
							onRowSelect: function(sm, rowIdx, record) {
								if (!this.readOnly && record.get('CmpCallCardDrug_id') > 0 && (Ext.isEmpty(record.get('DrugDocumentStatus_Code')) || record.get('DrugDocumentStatus_Code') == 1)) {
									this.ViewActions.action_edit.setDisabled(false);
									this.ViewActions.action_delete.setDisabled(false);
								} else {
									this.ViewActions.action_edit.setDisabled(true);
									this.ViewActions.action_delete.setDisabled(true);
								}
								this.ViewActions.action_view.setDisabled(Ext.isEmpty(record.get('CmpCallCardDrug_id')));
							},
							editGrid: function (action) {
								if (action == null) {
									action = 'add';
								}

								var base_form = me.FormPanel.getForm();
								var view_frame = this;
								var store = view_frame.getGrid().getStore();

								var Person_FIO = '';
								Person_FIO += base_form.findField('Fam').getValue()+' ';
								Person_FIO += base_form.findField('Name').getValue()+' ';
								Person_FIO += base_form.findField('Middle').getValue();

								var Age = base_form.findField('Age').getValue();
								var AgeType_id = base_form.getValues()['ComboCheck_AgeType_id'];
								if (!Ext.isEmpty(Person_FIO) && !Ext.isEmpty(Age)) {
									Person_FIO += ', возраст: '+Age;
									switch (AgeType_id) {
										case '221': //Дни
											Person_FIO += ' дней';
											break;
										case '220': //Месяцы
											Person_FIO += ' месяцов';
											break;
										case '219': //Годы
											Person_FIO += ' лет';
											break
									}
								}

								//регионы где в карте вызова не бригада, а врач - поля EmergencyTeam_id не будет
								if (base_form.findField('EmergencyTeam_id'))
									var EmergencyTeam_id = base_form.findField('EmergencyTeam_id').getValue();

								//регионы где в карте вызова не бригада, а врач - поля EmergencyTeamNum не будет
								if (base_form.findField('EmergencyTeamNum'))
									var EmergencyTeam_Name = base_form.findField('EmergencyTeamNum').getValue();

								if (action == 'add') {
									var record_count = store.getCount();
									if ( record_count == 1 && !store.getAt(0).get('CmpCallCardDrug_id') ) {
										view_frame.removeAll({addEmptyRecord: false});
										record_count = 0;
									}

									var params = new Object();
									//params.WhsDocumentUc_Date = wnd.form.findField('WhsDocumentUc_Date').getValue();
									params.Person_FIO = Person_FIO;
									params.EmergencyTeam_id = EmergencyTeam_id;
									params.EmergencyTeam_Name = EmergencyTeam_Name;
									params.CmpCallCardDrug_KolvoUnit = 1;

									var a_date = base_form.findField('ArriveTime').getStringValue();
									if (!Ext.isEmpty(a_date)) {
										var a_date_arr = a_date.split(' ');
										params.CmpCallCardDrug_setDate = a_date_arr.length == 2 ? a_date_arr[0] : null;
										params.CmpCallCardDrug_setTime = a_date_arr.length == 2 ? a_date_arr[1] : null;
									}

									getWnd(view_frame.editformclassname).show({
										owner: view_frame,
										action: action,
										params: params,
										onSave: function(data) {
											if ( record_count == 1 && !store.getAt(0).get('CmpCallCardDrug_id') ) {
												view_frame.removeAll({addEmptyRecord: false});
											}
											var record = new Ext.data.Record.create(view_frame.jsonData['store']);
											view_frame.clearFilter();
											data.CmpCallCardDrug_id = Math.floor(Math.random()*10000); //генерируем временный идентификатор
											data.state = 'add';
											store.insert(record_count, new record(data));
											view_frame.initActionPrint();
											view_frame.setFilter();
										}
									});
								}
								if (action == 'edit' || action == 'view') {
									var selected_record = view_frame.getGrid().getSelectionModel().getSelected();
									if (selected_record.get('CmpCallCardDrug_id') > 0) {
										var params = selected_record.data;
										//params.WhsDocumentUc_Date = wnd.form.findField('WhsDocumentUc_Date').getValue();
										params.Person_FIO = Person_FIO;
										params.EmergencyTeam_Name = EmergencyTeam_Name;

										getWnd(view_frame.editformclassname).show({
											owner: view_frame,
											action: action,
											params: params,
											onSave: function(data) {
												view_frame.clearFilter();
												for(var key in data) {
													selected_record.set(key, data[key]);
												}
												if (selected_record.get('state') != 'add') {
													selected_record.set('state', 'edit');
												}
												selected_record.commit();
												view_frame.setFilter();
											}
										});
									}
								}
							},
							deleteRecord: function(){
								var view_frame = this;
								var grid = view_frame.getGrid();
								var selected_record = view_frame.getGrid().getSelectionModel().getSelected();

								sw.swMsg.show({
									icon: Ext.MessageBox.QUESTION,
									msg: langs('Вы хотите удалить запись?'),
									title: langs('Подтверждение'),
									buttons: Ext.Msg.YESNO,
									fn: function(buttonId, text, obj) {
										if ('yes' == buttonId) {
											if (selected_record.get('state') == 'add') {
												grid.getStore().remove(selected_record);
											} else {
												selected_record.set('state', 'delete');
												selected_record.commit();
												view_frame.setFilter();
											}
											view_frame.initActionPrint();
										} else {
											if (grid.getStore().getCount()>0) {
												grid.getView().focusRow(0);
											}
										}
									}
								});
							},
							getChangedData: function(){ //возвращает новые и измненные показатели
								var data = new Array();
								this.clearFilter();
								this.getGrid().getStore().each(function(record) {
									if (record.data.state == 'add' || record.data.state == 'edit' ||  record.data.state == 'delete') {
										data.push(record.data);
									}
								});
								this.setFilter();
								return data;
							},
							getJSONChangedData: function(){ //возвращает новые и измненные записи в виде закодированной JSON строки
								var dataObj = this.getChangedData();
								return dataObj.length > 0 ? Ext.util.JSON.encode(dataObj) : "";
							},
							clearFilter: function() { //очищаем фильтры (необходимо делать всегда перед редактированием store)
								this.getGrid().getStore().clearFilter();
							},
							setFilter: function() { //скрывает удаленные записи
								this.getGrid().getStore().filterBy(function(record){
									return (record.get('state') != 'delete');
								});
							}
						});
					}
					me.DrugPanel.add(me.DrugGrid);
					me.DrugPanel.doLayout();
					
					if(callback!=undefined && typeof callback == "function"){
						callback();
					}
				}
			}
		})


		//this.FullDrugGrid = FullDrugGrid;
		//return this.FullDrugGrid;
		return me.DrugPanel;
	},

    getUslugaPanel: function(panelNumber){
        var me = this;
        return new Ext.grid.GridPanel(
            {
                plugins: [
                    new Ext.ux.grid.FilterRow({
                        width: 24,
                        hidden: false,
                        fixed: true,
                        clearFilterBtn: false,
                        clearFilters: function() {
                            this.grid.store.clearFilter();
                            this._search(true);
                        },
                        listeners:  {
                            'search':function(params){
                                this.grid.store.filterBy(function(rec){
                                    for (key in params) {
                                        if (
                                            (rec.get(key).toLowerCase().indexOf(params[key].toLowerCase()) == -1)
                                            && params[key]
                                        ){
                                            return false;
                                        }
                                    }
                                    return true;
                                });
                            }
                        }
                    })
                ],
                    refId: 'uslugaGrid',
                    autoHeight: true,
                    border: true,
                    collapsible: true,
                    layout: 'form',
                    clicksToEdit: 'auto',
                    style: 'margin-bottom: 0.5em;',
                    title: panelNumber + '. Услуги',
                    store: new Ext.data.Store({
                        autoLoad: false,
                        reader:new Ext.data.JsonReader({}, [
							{name: 'PayType_Code'},
							{name: 'VolumeType_Code'},
                            {name: 'UslugaComplex_id'},
                            {name: 'UslugaComplex_Name'},
                            {name: 'UslugaCategory_id'},
                            {name: 'UslugaComplex_Code'},
                            {name: 'CmpCallCardUsluga_Kolvo', type: 'int'}
                        ]),
                        url:'/?c=CmpCallCard&m=getUslugaFields'
                    }),
                    columns: [
						{name: 'PayType_Code', type: 'int', hidden: true, dataIndex: 'PayType_Code'},
						{name: 'VolumeType_Code', type: 'int', hidden: true, dataIndex: 'VolumeType_Code'},
                        {name: 'UslugaComplex_id', type: 'int', hidden: true, dataIndex: 'UslugaComplex_id'},
                        {name: 'UslugaCategory_id', type: 'int', hidden: true, dataIndex: 'UslugaCategory_id'},
                        {name: 'UslugaComplex_Code', type: 'int', hidden: true, dataIndex: 'UslugaComplex_Code'},
                        {name: 'UslugaComplex_Name', type: 'string', header: 'Наименовение', width: 1000, dataIndex: 'UslugaComplex_Name',
                            filter: new Ext.form.TriggerField({
                                name:'UslugaComplex_Name',
                                enableKeyEvents: true,
                                width: 1000,
                                cls: 'inputClearTextfieldsButton',
                                triggerConfig: {
                                    tag: 'span',
                                    cls: 'x-field-combo-btns',
                                    cn: [
                                        {tag: 'div', cls: 'x-form-trigger clearTextfieldsButton'},
                                    ]
                                },
                                onTriggerClick: function (e) {
                                    var uslugaPanel = me.FormPanel.find('refId', 'uslugaGrid'),
                                        plugin = uslugaPanel[0].plugins[0];

                                    this.reset();
                                    plugin._search();
                                }
                            })
                        },
                        {name: 'CmpCallCardUsluga_Kolvo', type: 'int', header: 'Количество', width: 120, dataIndex: 'CmpCallCardUsluga_Kolvo',
                            renderer: function(val, meta, rec, rowInd, colInd, store){
                                var el = '<div class="numTriggersFieldsContainer">' +
                                    '<span class="negativeTrigger"></span>' +
                                    '<span class="numberField">' + val + '</span>' +
                                    '<span class="positiveTrigger"></span>' +
                                    '</div>';
                                return el;
                            }
                        },
                    ],
                    listeners: {
                        cellclick:  function(grid, rowIndex, columnIndex, e) {
                            if (e.target && (e.target.classList.contains('negativeTrigger') || e.target.classList.contains('positiveTrigger'))) {

                                var record = grid.getStore().getAt(rowIndex);
                                var plus = e.target.classList.contains('positiveTrigger');

                                if (plus) {
									if(me.isOsmUslugaComplex(record.get('UslugaComplex_Code')) && me.existOsmUslugaComplex()){
										sw.swMsg.show(
											{
												buttons: Ext.Msg.OK,
												icon: Ext.Msg.WARNING,
												msg: 'В Карту вызова нельзя добавить больше одной услуги осмотра',
												title: langs('Сообщение')
											});
										return false;
									}
                                    record.set('CmpCallCardUsluga_Kolvo', +record.get('CmpCallCardUsluga_Kolvo') + 1);
                                }
                                else {
                                    var num = +record.get('CmpCallCardUsluga_Kolvo');

                                    num = (num-1 <= 0) ? '' : num-1;
                                    record.set('CmpCallCardUsluga_Kolvo', num);
                                }
                            }
                        }
                    },
                    sm: new Ext.grid.RowSelectionModel({singleSelect:true})
            }
        );
    },
/*
	//получение списка компонентов для вкладки Медикаменты
	getEvnDrugFields: function(){

		var me = this;

		var EvnDrugGrid = new sw.Promed.ViewFrame({
			actions: [
				{name: 'action_add', handler: function() {EvnDrugGrid.editGrid('add')}},
				{name: 'action_edit', handler: function() {EvnDrugGrid.editGrid('edit')}},
				{name: 'action_view', handler: function() {EvnDrugGrid.editGrid('view')}},
				{name: 'action_delete', handler: function() {EvnDrugGrid.deleteRecord()}},
				{name: 'action_refresh', hidden: true},
				{name: 'action_print'}
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 125,
			autoLoadData: false,
			hidden: true,
			border: true,
			dataUrl: '/?c=CmpCallCard&m=loadCmpCallCardEvnDrugList',
			height: 360,
			object: 'EvnDrug',
			editformclassname: 'swCmpCallCardEvnDrugEditWindow',
			id: 'CCCNCC_CmpCallCardEvnDrugGrid',
			paging: false,
			style: 'margin-bottom: 10px',
			stringfields: [
				{name: 'state', type: 'string', header: 'state', hidden: true},
				{name: 'EvnDrug_SetDatetime', header: 'Дата и время выдачи(Socr)', width: 150, renderer: function (v, p, r) {
					var dt_str = '';
					if (!Ext.isEmpty(r.get('EvnDrug_setDate'))) {
						dt_str += r.get('EvnDrug_setDate')+' ';
					}
					if (!Ext.isEmpty(r.get('EvnDrug_setTime'))) {
						dt_str += r.get('EvnDrug_setTime');
					}
					return dt_str;
				}},
				{name: 'DrugNomen_Code', type: 'string', header: 'Код', width: 150},
				{name: 'EvnDrug_Kolvo', type: 'float', header: 'Количество', width: 50},
				{name: 'GoodsUnit_Name', type: 'string', header: 'Ед.измерения', width: 150},
				{name: 'DrugNomen_Name', type: 'string', header: 'Наименование', id: 'autoexpand'},

				{name: 'EvnDrug_Comment', type: 'string', header: 'Примечание', width: 300},
				{name: 'EvnDrug_setDate', hidden: true},
				{name: 'EvnDrug_setTime', hidden: true},
				{name: 'EvnDrug_id', type: 'int', header: 'ID', key: true},
				{name: 'DrugNomen_id', type: 'int',  hidden: true},
				{name: 'Drug_id', type: 'int',  hidden: true},
				{name: 'GoodsUnit_id', type: 'int',  hidden: true},
			],
			title: null,
			toolbar: true,
			onRowSelect: function(sm, rowIdx, record) {
			},
			editGrid: function (action) {
				if (action == null) {
					action = 'add';
				}

				var base_form = me.FormPanel.getForm(),
					view_frame = this,
					store = view_frame.getGrid().getStore();

				//регионы где в карте вызова не бригада, а врач - поля EmergencyTeam_id не будет
				if (base_form.findField('EmergencyTeam_id'))
					var EmergencyTeam_id = base_form.findField('EmergencyTeam_id').getValue();

				//регионы где в карте вызова не бригада, а врач - поля EmergencyTeamNum не будет
				if (base_form.findField('EmergencyTeamNum'))
					var EmergencyTeam_Name = base_form.findField('EmergencyTeamNum').getValue();

				if (action == 'add') {

					var drugGridActions = view_frame.actions;
					var record_count = store.getCount();

					if ( record_count == 1 && !store.getAt(0).get('CmpCallCardDrug_id') ) {
						view_frame.removeAll({addEmptyRecord: false});
						record_count = 0;
					}

					var params = new Object();

					params.EmergencyTeam_id = EmergencyTeam_id;
					params.EmergencyTeam_Name = EmergencyTeam_Name;
					params.CmpCallCard_id = base_form.findField('CmpCallCard_id').getValue();
					params.Lpu_id = getGlobalOptions().lpu_id,
					params.action = action,

					params.onSave = function(data) {

						if ( record_count == 1 && !store.getAt(0).get('EvnDrug_id') ) {
							view_frame.removeAll({addEmptyRecord: false});
						}

						var record = new Ext.data.Record.create(view_frame.jsonData['store']);
						view_frame.clearFilter();

						data.EvnDrug_id = Math.floor(Math.random()*10000); //генерируем временный идентификатор
						data.state = 'add';

						store.insert(record_count, new record(data));
						view_frame.setFilter();
					};

					var a_date = base_form.findField('ArriveTime').getStringValue();

					if (!Ext.isEmpty(a_date)) {
						var a_date_arr = a_date.split(' ');
						params.EvnDrug_setDate = a_date_arr.length == 2 ? a_date_arr[0] : null;
						params.EvnDrug_setTime = a_date_arr.length == 2 ? a_date_arr[1] : null;
					}

					getWnd(view_frame.editformclassname).show(params);
				}
				if (action == 'edit' || action == 'view') {

					var selected_record = view_frame.getGrid().getSelectionModel().getSelected();
					if (selected_record.get('EvnDrug_id') > 0) {

						var params = {},
							selectedRecordFields = Object.keys(selected_record.data),
							dontPassToForm = [
								'state',
								'EvnDrug_SetDatetime',
								'DrugNomen_Code',
								'GoodsUnit_Name',
								'DrugNomen_Name',
							];

						selectedRecordFields.forEach(function(fieldName){
							if (!(dontPassToForm.indexOf(fieldName) != -1)) {
								params[fieldName] = selected_record.data[fieldName];
							}
						})

						params.EmergencyTeam_id = EmergencyTeam_id;
						params.CmpCallCard_id = base_form.findField('CmpCallCard_id').getValue();
						params.Lpu_id = getGlobalOptions().lpu_id,
						params.action = action,
						params.onSave = function(data) {

							view_frame.clearFilter();

							for(var key in data) {
								selected_record.set(key, data[key]);
							}

							if (selected_record.get('state') != 'add') {
								selected_record.set('state', 'edit');
							}

							selected_record.commit();
							view_frame.setFilter();
						}

						getWnd(view_frame.editformclassname).show(params);
					}
				}
			},
			deleteRecord: function(){

				var view_frame = this;
				var grid = view_frame.getGrid();
				var selected_record = view_frame.getGrid().getSelectionModel().getSelected();

				sw.swMsg.show({
					icon: Ext.MessageBox.QUESTION,
					msg: langs('Вы хотите удалить запись?'),
					title: langs('Подтверждение'),
					buttons: Ext.Msg.YESNO,
					fn: function(buttonId, text, obj) {

						if ('yes' == buttonId) {
							if (selected_record.get('state') == 'add') {
								grid.getStore().remove(selected_record);
							} else {
								selected_record.set('state', 'delete');
								selected_record.commit();
								view_frame.setFilter();
							}
						} else {
							if (grid.getStore().getCount()>0) {
								grid.getView().focusRow(0);
							}
						}
					}
				});
			},
			getChangedData: function(){ //возвращает новые и измненные показатели
				var data = new Array();
				this.clearFilter();
				this.getGrid().getStore().each(function(record) {
					if (record.data.state == 'add' || record.data.state == 'edit' ||  record.data.state == 'delete') {
						data.push(record.data);
					}
				});
				this.setFilter();
				return data;
			},
			getJSONChangedData: function(){ //возвращает новые и измненные записи в виде закодированной JSON строки
				var dataObj = this.getChangedData();
				return dataObj.length > 0 ? Ext.util.JSON.encode(dataObj) : "";
			},
			clearFilter: function() { //очищаем фильтры (необходимо делать всегда перед редактированием store)
				this.getGrid().getStore().clearFilter();
			},
			setFilter: function() { //скрывает удаленные записи
				this.getGrid().getStore().filterBy(function(record){
					return (record.get('state') != 'delete');
				});
			}
		});

		this.EvnDrugGrid = EvnDrugGrid;
		return this.EvnDrugGrid;
	},
*/
	//получение нумберфилдов для панели услуга -> манипуляции
    /*
	initUslugaElements: function(){
		var me = this,
			base_form = me.FormPanel.getForm(),
			params = {};

		if(!me.UslugaPanel) return false;
		
		me.UslugaPanel.removeAll(true);
		
		params.acceptTime = base_form.findField('AcceptTime').getStringValue();
	
		Ext.Ajax.request({
			url: "/?c=CmpCallCard&m=getUslugaFields",
			params: params,
			callback: function(opt, success, response) {
				if ( success ) {
					var response_obj = Ext.util.JSON.decode(response.responseText);
						
						me.uslugaWrapper = new Ext.Panel({						
							layout:'table',
							cls: 'uslugaPanelWrapper',
							defaults: {	
								bodyStyle:'padding:20px',
								labelWidth: 150
							},
							layoutConfig: {
								columns: 6
							},
							items: []
						});
					
					me.UslugaPanel.add( me.uslugaWrapper );
					
					for(var i = 0; i<response_obj.length; i++){
						var elem = response_obj[i];

						me.uslugaWrapper.add(
							new Ext.Container({
								autoEl: {},
								layout: 'form',
								items: [
									new Ext.ux.form.Spinner({
										maskRe: /\d/,
										fieldLabel: elem.UslugaComplex_Name,
										name: 'Usluga_'+elem.UslugaComplex_id,
										id: 'usluga_'+elem.UslugaComplex_Code,
										showSplitter: false,
										UslugaComplex_id: elem.UslugaComplex_id,
										UslugaCategory_id: elem.UslugaCategory_id,
										UslugaComplex_Code: elem.UslugaComplex_Code,
										//CmpCallCardUsluga_id: null,
										isDirty: false,
										width: 50,
										listeners: {
											change: function(cmp,newVal,oldVal){
												cmp.addClass('dirtyNumberfield');
												cmp.isDirty = true;

												if(me.isOsmUslugaComplex(cmp.UslugaComplex_Code) && newVal > 1 && me.existOsmUslugaComplex()){
													sw.swMsg.show(
														{
															buttons: Ext.Msg.OK,
															icon: Ext.Msg.WARNING,
															msg: 'В Карту вызова нельзя добавить больше одной услуги осмотра',
															title: langs('Сообщение'),
															fn: function(){
																me.enable()
															}
														});
													cmp.setValue(1);
												}
											}
										},
										strategy: new Ext.ux.form.Spinner.NumberStrategy({
											minValue:'1', 
											maxValue: '1000', 
											defaultValue: 1, 
											decimalPrecision: 0,
											spin : function(field, down, alternate){

												if(me.isOsmUslugaComplex(field.UslugaComplex_Code) && !down && me.existOsmUslugaComplex()){
													me.disable();
													sw.swMsg.show(
														{
															animEl: me.FormPanel.id,
															buttons: Ext.Msg.OK,
															icon: Ext.Msg.WARNING,
															msg: 'В Карту вызова нельзя добавить больше одной услуги осмотра',
															title: langs('Сообщение'),
															fn: function(){
																me.enable()
															}
														});
													return false;
												}
												Ext.ux.form.Spinner.NumberStrategy.superclass.spin.call(this, field, down, alternate);

												var v = parseFloat(field.getValue());
												var incr = (alternate == true) ? this.alternateIncrementValue : this.incrementValue;

												if(down){
													v -= incr
													v = (isNaN(v)) ? null : v;
												}
												else{
													v += incr
													v = (isNaN(v)) ? this.defaultValue : v;
												}
												
												v = this.fixBoundries(v);
												field.setRawValue(v);
												field.addClass('dirtyNumberfield');
												field.isDirty = true;
											},
											fixBoundries : function(value){
												var v = value;

												if(this.minValue != undefined && v < this.minValue){
													v = null;
												}
												if(this.maxValue != undefined && v > this.maxValue){
													v = this.maxValue;
												}
												
												return this.fixPrecision(v);
											}
										})
									})
								]
							})
						);						
					};
					
					me.uslugaWrapper.doLayout();
					me.UslugaPanel.doLayout();
					
					me.loadUslugaPanelData();
				}
			}
		});
		
	},

	
	loadUslugaPanelData: function(){
		var me = this,
			base_form = me.FormPanel.getForm();
			
		Ext.Ajax.request({
			url: "/?c=CmpCallCard&m=loadCmpCallCardUslugaGrid",
			params: {
				CmpCallCard_id: base_form.findField('CmpCallCard_id').getValue()
			},
			callback: function(opt, success, response) {
				if ( success ) {
					
					var response_obj = Ext.util.JSON.decode(response.responseText),
						itemzz = me.uslugaWrapper.items.items,
						recursiveFn = function (items) {
							for (var i=0; i < items.length; i++) {
								var elementos = items[i];

								if (items[i].items) {
									recursiveFn(items[i].items.items)
								}
								else{
									elementos.reset();
									elementos.removeClass('dirtyNumberfield');
									for (var k=0; k < response_obj.length; k++) {													
										if( response_obj[k] && (elementos.name == ('Usluga_'+response_obj[k].UslugaComplex_id)) ){
											elementos.setValue(response_obj[k].CmpCallCardUsluga_Kolvo);
											//elementos.CmpCallCardUsluga_id = response_obj[k].CmpCallCardUsluga_id;
										}
									}
								}
							}
						};
						
					recursiveFn(itemzz);
				}							
			}
		});
	},

    */
	//получение радио и чеков
	initRadiosAndChecks: function(){
		var me = this;
		$.ajax({
			url: "/?c=CmpCallCard&m=getComboxAll",
			async: false,
			cache: true
		}).done(function ( data ) {
			me.allfields = JSON.parse(data);
		});
	},

	//получение комбобокса по имени
	getCombo: function(field_name) {

		var parents_codes = {};
		var me = this;

		var combo_fields = this.allfields[field_name];

		for (var field in combo_fields){

			var elem_id = combo_fields[field].id;
			var ext_component_id = this.id;

			if (elem_id) {

				combo_fields[field].id = ext_component_id + '_' + elem_id;
			}

			if( field_name === 'TeamComplect_id' && combo_fields[field].code && combo_fields[field].code.inlist([3473,3474,3475,3476,3477])){
				combo_fields[field].listeners = {
					render: function(){
						var comboTeam = this.getEl();
						var xPanelBody = comboTeam.up('.x-panel-body');
						var xPanel = comboTeam.up('.x-panel');
						var txtTeamComplect= document.createElement('div'); //создадим элемент для номера телефона

						txtTeamComplect.setAttribute('id', comboTeam.id+'_TeamComplect');
						txtTeamComplect.setAttribute("style", "position: absolute;left: 180px; top: 2px;");
						
						xPanelBody.setStyle('width', '100%');
						xPanel.setStyle('width', '100%');
						comboTeam.parent().parent().appendChild(txtTeamComplect);
					}
				}
			}

			//тестовый функционал по отображению дочерних элементов
			if (combo_fields[field].parent_code) {

				//автоматически прячем элементы при рендере, прячем лэйбл
				if (combo_fields[field].fieldLabel){

					combo_fields[field].listeners = {

						'render': function(field_name){

							if (field_name.getEl().up('.x-form-item'))
								field_name.getEl().up('.x-form-item').setDisplayed(false);

						}
					}
				}

				//для типа DS свой обработчик (не скрывает элементы а зизаблит)
				if(combo_fields[field].type == 'dsComboRadioCmp'){

					combo_fields[field].listeners = {

						'render': function(field_name){
							field_name.disable();
						}
					};

					combo_fields[field].disabled = true;

				} else {
					//спрятали
					combo_fields[field].hidden = true;
				}
				
				if( field_name == 'Patient_id' && combo_fields[field].xtype == 'swlpuopenedcombo'){
					combo_fields[field].listeners = {
						select: function(combo, record, index){

							var lpu_id = record.get('Lpu_id'),
								kladraddress = Ext.getCmp(me.id+'_'+'CMPCLOSE_ComboValue_695').kladraddress;

							if(kladraddress) {
								kladraddress.lpu_id = lpu_id;
								if(lpu_id) {
									me.determinationServiceAddressMO(kladraddress, function(data){
										me.setPhoneMO(data.phone);
									});
									//me.getPhoneMO(lpu_id);
								}else{
									//me.setPhoneMO(false);
								}
							}
						},
						render: function(){
							var comboMO = this.getEl();
							var elem= comboMO.up('.x-form-item');
							var xPanelBody = comboMO.up('.x-panel-body');
							var xPanel = comboMO.up('.x-panel');
							if(elem) elem.setDisplayed(false);

							var txtPhone=document.createElement('div'); //создадим элемент для номера телефона
							txtPhone.setAttribute('id', comboMO.id+'_Phone');
							var rightPx = (this.labelStyle == 'width: 110px') ? '320px' : '410px';
							txtPhone.setAttribute("style", "position: absolute;left: "+rightPx+"; top: 2px;");
							if( xPanelBody ) xPanelBody.setStyle('width', '100%');
							if( xPanel ) xPanel.setStyle('width', '100%');
							comboMO.parent().parent().appendChild(txtPhone);
						}
					}
				}
				
				if(combo_fields[field].cls == 'addresstriggerfield'){

					var idAddr = combo_fields[field].id;

					combo_fields[field].onTrigger1Click = function(){
						me.addressClick(idAddr, 'paste', '', function(params){

							var comboMO = Ext.getCmp(me.id+'_'+'CMPCLOSE_ComboValue_693'),
								comboAddressInvite = Ext.getCmp(me.id+'_'+'CMPCLOSE_ComboValue_695');

							comboAddressInvite.kladraddress = params;
							params.action = 'form';

							me.determinationServiceAddressMO(params, function(data){

								if(data.Lpu_id && comboMO && comboMO.findRecord('Lpu_id', data.Lpu_id)){
									comboMO.setValue(data.Lpu_id);
									me.setPhoneMO(data.phone);
								}else{
									comboMO.setValue();
									me.setPhoneMO(false);
								}

							});
						});
					};

					combo_fields[field].onTrigger2Click = function(e){
						me.addressClick(idAddr, 'sequil', e.getXY(), function(params){
							var comboMO = Ext.getCmp(me.id+'_'+'CMPCLOSE_ComboValue_693'),
								comboAddressInvite = Ext.getCmp(me.id+'_'+'CMPCLOSE_ComboValue_695');

							comboAddressInvite.kladraddress = params;
							params.action = 'menu';

							me.determinationServiceAddressMO(params, function(data){

								if(data.Lpu_id && comboMO && comboMO.findRecord('Lpu_id', data.Lpu_id)){
									comboMO.setValue(data.Lpu_id);
									me.setPhoneMO(data.phone);
									//me.getPhoneMO(Lpu_id);
								}else{
									comboMO.setValue();
									me.setPhoneMO(false);
								}

							});
						} );
					};

					combo_fields[field].onTrigger3Click = function(){
						me.addressClick(idAddr, 'clear');
						var comboMO = Ext.getCmp(me.id+'_'+'CMPCLOSE_ComboValue_693');
						comboMO.setValue();
						//me.setPhoneMO(false);
					};
				}

				if (field_name == 'Patient_id' && combo_fields[field].code == 694){
					//По №59740#118 все даты до указанной даты не доступны,
					// а дальше должны быть доступны для выбора.
					combo_fields[field].setDateMaxValueWhenGetFromSrv = false;
					combo_fields[field].setDateMinValueWhenGetFromSrv = true;
				}

				//Загрузка бригад при установке даты передачи спец бригаде
				if( combo_fields[field].name == 'ComboValue_245' ){
					combo_fields[field].onChange = function(field, newValue){
						var spETcombo = Ext.getCmp(me.id+'_'+'CMPCLOSE_ComboValue_244');
						if(!spETcombo) return false;

						if(newValue){
							spETcombo.getStore().load({
								params: {
									AcceptTime: Ext.util.Format.date(new Date(newValue), 'd.m.Y H:i:s')
								},
								callback: function() {
									spETcombo.setValue(spETcombo.getValue());
								}
							})
						}else{
							spETcombo.getStore().removeAll();
						}


					}
				}

				//сбор родительского кода
				var parent_code = combo_fields[field].parent_code;

				if (parents_codes[parent_code]) {

					parents_codes[parent_code].push(
						{
							'code': combo_fields[field].code,
							'id': combo_fields[field].id
						});

				} else {

					parents_codes[parent_code] = [
						{
							'code': combo_fields[field].code,
							'id': combo_fields[field].id
						}
					];
				}
			}

			if (combo_fields[field].items){

				for (var item in combo_fields[field].items){

					var idds = combo_fields[field].items[item].id;

					if (idds) {

						combo_fields[field].items[item].id = this.id + '_' + idds;
					}
				}
			}
		}

        //есть поля, которые сбрасывать на недо
        var exeptedResetFieldCodes = [];

		//простановка листенеров на отображение/ныкание дочерних элементов
		for(var k in combo_fields){
			if(typeof combo_fields[k] == 'object'){
				if(combo_fields[k].code){

					var codeobj = parents_codes[combo_fields[k].code];

					if(codeobj){
						combo_fields[k].childsCombos = codeobj;

						//для типа DS свой обработчик (не скрывает элементы а зизаблит)
						if(combo_fields[k].type == 'dsComboRadioCmpParent'){

							combo_fields[k].listeners =
							{
								'check': function(rb,checked)
								{
									for(var cm = 0; cm < this.childsCombos.length; cm++){
										var c = Ext.getCmp(this.childsCombos[cm].id);

										if(c){
											if(checked){
												c.enable();
											}
											else{
												c.disable();
												if(c.xtype == 'swequalitytypecombo'){
													c.clearValue();
												}
											}
										}
									}
								}
							}
						}
						else{
							combo_fields[k].listeners =
							{
								'check': function(rb,checked)
								{

									for(var cm = 0; cm < this.childsCombos.length; cm++){
										var c = Ext.getCmp(this.childsCombos[cm].id);
										if(c){
											if(checked){
												c.show();
												if ( c.getEl().up('.x-form-item') && c.fieldLabel ) {
													c.getEl().up('.x-form-item').setDisplayed(true); // show label
												}
											}
											else{
												c.hide();
												if(c.xtype != 'swdatetimefield'){
													//c.reset();
												}
												if ( c.getEl().up('.x-form-item') && c.fieldLabel ) {
													c.getEl().up('.x-form-item').setDisplayed(false); // hide label
												}
											}
										}
									}
									if(field_name == 'Percussion_id') return false;
								}
							}
						};
					}else{
						//временный костыль для полей DS в блоке "перкуссия /Коробочный, Притупление /" (крым), 
						//не забыть с Токаревым посоветоваться как сделать всё правильно
						if(field_name == 'Percussion_id'){
							combo_fields[k].listeners =
							{
								'check': function(rb,checked)
								{
									return false;
								}
							}
						}
						//----------------------------------------
						//----------------------------------------
					}
				}
			}
		}

		if (!combo_fields)
			log('поля ' + field_name + ' нет в бд');

		return (combo_fields) ? combo_fields : {};
	},

	getLpuAddressTerritory: function() {
		var base_form = this.FormPanel.getForm();

		this.showLoadMask('Подождите...');

		Ext.Ajax.request({
			callback: function(opt, success, response) {
				this.hideLoadMask();

				if ( success ) {
					var response_obj = Ext.util.JSON.decode(response.responseText);

					var respData = response_obj[0];
					if( respData['KLSubRGN_id'] != '0' ) {
						base_form.findField('Area_id').setValue(respData['KLSubRGN_id']);
						base_form.findField('Area_id').getStore().removeAll();
						base_form.findField('Area_id').getStore().load({
							params: {region_id: respData['KLRGN_id']},
							callback: function() {
								this.setValue(this.getValue());
								this.fireEvent('beforeselect', this, this.getStore().getAt(this.getStore().findBy(function(rec) {return rec.get('SubRGN_id') == this.getValue();}.createDelegate(this))));
							}.createDelegate(base_form.findField('Area_id'))
						});
						base_form.findField('City_id').getStore().load({
							params: {subregion_id: respData['KLRGN_id']}
						});
					} else if( respData['KLCity_id'] != '0' ) {
						base_form.findField('City_id').setValue(respData['KLCity_id']);
						base_form.findField('City_id').getStore().removeAll();
						base_form.findField('City_id').getStore().load({
							params: {subregion_id: respData['KLRGN_id']},
							callback: function() {
								this.setValue(this.getValue());
								this.fireEvent('beforeselect', this, this.getStore().getAt(this.getStore().findBy(function(rec) {return rec.get('City_id') == this.getValue();}.createDelegate(this))));
							}.createDelegate(base_form.findField('City_id'))
						});
						/*base_form.findField('City_id').getStore().load({
							params: {region_id: respData['KLRGN_id']}
						});*/
					}
					if( respData['KLTown_id'] != '0' ) {
						base_form.findField('Town_id').setValue(respData['KLTown_id']);
						base_form.findField('Town_id').getStore().load({
							params: {city_id: respData['KLSubRGN_id'] != '0' ? respData['KLSubRGN_id'] : respData['KLCity_id']},
							callback: function() {
								this.setValue(this.getValue());
								this.fireEvent('beforeselect', this, this.getStore().getAt(this.getStore().findBy(function(rec) {return rec.get('KLTown_id') == this.getValue();}.createDelegate(this))));
							}.createDelegate(base_form.findField('Town_id'))
						});
					}

				}
				else {
					sw.swMsg.alert('Ошибка', 'Ошибка при определении местоположения ЛПУ', function() {base_form.findField('CmpCallCard_Numv').focus(true);}.createDelegate(this) );
				}
			}.createDelegate(this),
			url: '/?c=CmpCallCard&m=getLpuAddressTerritory'
		});
	},

	changePerson: function() {
		if ( !(getGlobalOptions().region && getGlobalOptions().region.nick == 'perm') ) {
			return false;
		}

		var base_form = this.FormPanel.getForm();

		if ( !base_form.findField('CmpCallCard_id').getValue() ) {
			return false;
		}

		var params = {
			CmpCallCard_id: base_form.findField('CmpCallCard_id').getValue()
		}

		getWnd('swPersonSearchWindow').show({
			onSelect: function(person_data) {
				params.Person_id = person_data.Person_id;
				params.PersonEvn_id = person_data.PersonEvn_id;
				params.Server_id = person_data.Server_id;

				//this.setAnotherPersonForDocument(params);
			}.createDelegate(this),
			personFirname: base_form.findField('Name').getValue(),
			personSecname: base_form.findField('Middle').getValue(),
			personSurname: base_form.findField('Fam').getValue(),
			searchMode: 'all'
		});
	},

	personSearch: function(autoSearch, viewOnly) {
		if ( this.action == 'view' ) {
			return false;
		}

		if ( getWnd('swPersonSearchWindow').isVisible() ) {
			sw.swMsg.alert('Сообщение', 'Окно поиска человека уже открыто');
			return false;
		}

		var me = this,
			base_form = this.FormPanel.getForm();
			parentObject = this;
		getWnd('swPersonSearchWindow').show({
			autoSearch: autoSearch?true:false,
			getPersonWorkFields: true,
			onClose: Ext.emptyFn,
			onSelect: function(person_data){
				
				getWnd('swPersonSearchWindow').hide();
				
				me.setPerson(person_data);
				//me.checkEmergencyStandart();
			},
			personFirname: base_form.findField('Name').getValue(),
			personSecname: base_form.findField('Middle').getValue(),
			personSurname: base_form.findField('Fam').getValue(),
			Person_Age: base_form.findField('Age').getValue(),
			viewOnly: viewOnly?true:false,
			searchMode: 'all'
		});
	},
	
	setPerson: function(person_data){
		var me = this,
			base_form = this.FormPanel.getForm();
			parentObject = this;
		var opts = getGlobalOptions();
		var deathDate = person_data.Person_deadDT;
		function addDays(date, days) {
			var result = new Date(date);
			result.setDate(result.getDate() + days);
			return result;
		}

		if(person_data.Person_IsDead == 'true' && deathDate){
			if(deathDate.length != '' && deathDate.length<=10)
				deathDate = new Date(deathDate.replace(/^(\d{2}).(\d{2}).(\d{4})/,'$3-$2-$1'));
			if(!Ext.isEmpty(opts.limit_days_after_death_to_create_call) && parseInt(opts.limit_days_after_death_to_create_call,10)>0)
				deathDate = addDays(deathDate,parseInt(opts.limit_days_after_death_to_create_call,10));
			var acceptTime = new Date(base_form.findField('AcceptTime').value);
			if(deathDate && deathDate < acceptTime.setHours(0,0,0,0) )
			{
				sw.swMsg.alert('Ошибка', 'Человек на дату приема вызова является умершим. Выбор человека невозможен.', function() {});
				return false;
			}
			else
				base_form.findField('Person_deadDT').setValue(person_data.Person_deadDT);
		}
		
		with(base_form){
			findField('Person_id').setValue( person_data.Person_id );

			// Наименования из формы поиска приходят с разным регистром
			// в зависимости от того: добавили или выбрали человека
			findField('Fam').disable();
			findField('Fam').setValue( person_data.Person_SurName || person_data.PersonSurName_SurName || '' );
			findField('Name').disable();
			findField('Name').setValue( person_data.Person_FirName || person_data.PersonFirName_FirName || '' );
			findField('Middle').disable();
			findField('Middle').setValue( person_data.Person_SecName || person_data.PersonSecName_SecName || '' );
			findField('Sex_id').disable();
			findField('Sex_id').setValue( person_data.PersonSex_id || person_data.Sex_id || '' );
			findField('Person_IsUnknown').setValue(1);
			findField('PersonFields_IsDirty').setValue(false);
			findField('Person_deadDT').setValue(null);
			//findField('Age').disable();
			//findField('Age').setValue( swGetPersonAge( person_data.Person_BirthDay || person_data.Person_Birthday, new Date()) );
			// Выбираем ед.измерения в годах

			var cb219 = Ext.getCmp(me.id+'_CMPCLOSE_CB_219'),
				cb220 = Ext.getCmp(me.id+'_CMPCLOSE_CB_220'),
				cb221 = Ext.getCmp(me.id+'_CMPCLOSE_CB_221'),
				documentNumField = findField('DocumentNum'),
				polisSerField = findField('Person_PolisSer'),
				polisNumField = findField('Person_PolisNum'),
				polisEdnumField = findField('CmpCloseCard_PolisEdNum'),
				birthdayField = findField('Birthday'),
				personSnilsField = findField('Person_Snils');

			if(cb219){
				cb219.disable().setValue(true);
			}
			if(cb220){
				cb220.disable().setValue(false);
			}
			if(cb221){
				cb221.disable().setValue(false);
			}

			findField('Work').disable();
			findField('Work').setValue( person_data.Person_Work || '' );

			if(documentNumField){
				documentNumField.disable();
				var DocumentNum = (person_data.Document_Ser || '') + ' ' + (person_data.Document_Num || '');
				documentNumField.setValue( DocumentNum.trim() );
			};

			if(polisSerField){
				polisSerField.disable();
				polisSerField.setValue( person_data.Polis_Ser || '' );
			};

			if(polisNumField){
				polisNumField.disable();
				polisNumField.setValue( person_data.Polis_Num || '' );
			};

			if(polisEdnumField){
				polisEdnumField.disable();
				polisEdnumField.setValue( person_data.Polis_EdNum || '' );
			};
			
			if(personSnilsField){
				personSnilsField.disable();
				personSnilsField.setValue( person_data.Person_Snils || '' );
			};
		}

		var bth = person_data.Person_BirthDay || person_data.PersonBirthDay_BirthDay;
		if (bth) {
			var acceptDate = new Date();
			if (base_form.findField('AcceptTime').getStringValue() != '') {
				acceptDate = Date.parseDate( base_form.findField('AcceptTime').getStringValue(), 'd.m.Y H:i' );
			}
			
			if(birthdayField){
				//Казахстанский компонент
				birthdayField.setValue(bth);
				me.showAgeLabel();
			}

			var b_days = Math.floor(swGetPersonAgeDay(bth, acceptDate));
			var b_month = swGetPersonAgeMonth(bth, acceptDate);
			var b_year = swGetPersonAge(bth, acceptDate);

			if (b_days >= 0 && b_days <= 30) {
				//дни
				base_form.findField('Age').setValue(b_days);
				if(cb219)cb219.setValue(false);
				if(cb220)cb220.setValue(false);
				if(cb221)cb221.setValue(true);
			}
			if (b_days > 30 && b_year == 0) {
				//Месяцы
				base_form.findField('Age').setValue(b_month);
				if(cb219)cb219.setValue(false);
				if(cb220)cb220.setValue(true);
				if(cb221)cb221.setValue(false);
			}
			if (b_year > 0) {
				//Годы
				base_form.findField('Age').setValue(b_year);
				if(cb219)cb219.setValue(true);
				if(cb220)cb220.setValue(false);
				if(cb221)cb221.setValue(false);
			}
		}
		base_form.findField('Age').disable();
		sw.swMsg.alert('Сообщение', 'Пациент идентифицирован', function() {} );
	},

	personUnknown: function() {
		var base_form = this.FormPanel.getForm(),
			fields = [
				'Fam',
				'Name',
				'Middle',
				'Sex_id'
			];

		this.personReset();

		for(var i=0; i<fields.length; i++) {
			var perField = base_form.findField(fields[i]);
			if(perField){
				if(fields[i].inlist(['Fam', 'Name', 'Middle'])){
					perField.disable().setValue('Неизвестен')
				}
				if(fields[i].inlist(['Sex_id'])){
					if(fields[i] == 'Sex_id') perField.setValue(3);
					perField.allowBlank = true;
				}
			}
		}
		
		base_form.findField('Person_IsUnknown').setValue(2);
		base_form.findField('PersonFields_IsDirty').setValue(false);

		var cb146 = Ext.getCmp(this.id+'_CMPCLOSE_CB_219');

		if(cb146) cb146.setValue(true);

		/*
		base_form.findField('Fam').setValue('Неизвестен');
		base_form.findField('Name').setValue('Неизвестен');
		base_form.findField('Middle').setValue('Неизвестен');
		base_form.findField('Fam').disable();
		base_form.findField('Name').disable();
		base_form.findField('Middle').disable();
		base_form.findField('SocialCombo').allowBlank = true;
		base_form.findField('Sex_id').allowBlank = true;
		base_form.findField('Sex_id').setValue(3);
		*/
	},

	personReset: function() {
		if ( this.action == 'view' ) {
			return false;
		}
		var me = this,
			base_form = this.FormPanel.getForm(),
			fields = [
				'Fam',
				'Name',
				'Middle',
				'Person_id',
				'Age',
				'Sex_id',
				'Work',
				'DocumentNum',
				'Person_PolisSer',
				'Person_PolisNum',
				'CmpCloseCard_PolisEdNum',
				'Birthday'
			];

		for(var i=0; i<fields.length; i++) {
			var perField = base_form.findField(fields[i]);
			if(perField){
				perField.enable();
				perField.reset();

				if(fields[i].inlist(['Sex_id', 'Age'])){
					perField.allowBlank = !perField.isVisible(); 
				}
			}
		}

		base_form.findField('Person_id').setValue(null);

		var cb219 = Ext.getCmp(this.id+'_CMPCLOSE_CB_219'),
			cb220 = Ext.getCmp(this.id+'_CMPCLOSE_CB_220'),
			cb221 = Ext.getCmp(this.id+'_CMPCLOSE_CB_221'),
			ageText = Ext.getCmp(this.id+'AgeText');

		if(cb219) cb219.enable().setValue(true);
		if(cb220) cb220.enable().setValue(false);
		if(cb221) cb221.enable().setValue(false);
		if(ageText) ageText.setText('');
		
		//me.checkEmergencyStandart();		
	},

	// Валидация формы перед сохранением
	//@todo эту кашу надо будет переписать
	doValidate: function( callbackFn ){
		var me = this,
			has_error = false,
			error = '',
			base_form = me.FormPanel.getForm(),
			diagField = base_form.findField('Diag_id'),
			callCauseField = base_form.findField('CallPovod_id'),
			payTypeCombo = base_form.findField('PayType_id'),
			callCauseIsError = (callCauseField && !Ext.isEmpty(callCauseField.getValue()) && callCauseField.getValue().inlist([509])),
			region = getGlobalOptions().region.nick;

		//проверка на умершего
		var	deadDT = new Date(base_form.findField('Person_deadDT').getValue());
		var opts = getGlobalOptions();
		function addDays(date, days) {
			var result = new Date(date);
			result.setDate(result.getDate() + days);
			return result;
		}
		
		if(getGlobalOptions().region.nick === "buryatiya"){
			var isDeath = Ext.getCmp(this.id+'_'+'CMPCLOSE_CB_229').getValue()
				|| Ext.getCmp(this.id+'_'+'CMPCLOSE_CB_230').getValue()
				|| Ext.getCmp(this.id+'_'+'CMPCLOSE_CB_235').getValue(),
				
				diagCode = diagField.getDiagCode(diagField.getRawValue());
			
			if(isDeath && diagCode.slice(0,1) === "Z" ){
				sw.swMsg.alert('Ошибка', 'Выбранный результат выезда не соответствует диагнозу Z. Укажите корректное значение');
				me.hideLoadMask();
				return false;
			}
		}

		if(!Ext.isEmpty(opts.limit_days_after_death_to_create_call) && parseInt(opts.limit_days_after_death_to_create_call,10)>0)
			deadDT = addDays(deadDT,parseInt(opts.limit_days_after_death_to_create_call,10));
		var acceptTime = new Date(base_form.findField('AcceptTime').value);
		if(deadDT && deadDT < acceptTime.setHours(0,0,0,0)){
			sw.swMsg.alert('Ошибка', 'Человек на дату приема вызова является умершим. Сохранение невозможно', function() {});
			me.hideLoadMask();
			return false;
		}
		//конец проверки на умершего
		
		//если повод ошибка - то результат выезда, результат оказания смп, диагноз - необязательные
		if(callCauseIsError){
			base_form.findField('ResultEmergencyTrip').allowBlank = true;
			base_form.findField('Result_id').allowBlank = true;
			diagField.allowBlank = true;
		}

		var chekActiv = Ext.getCmp(this.id+'_'+'CMPCLOSE_CB_111');
		//Адрес посещения
		if(chekActiv){
			var activeAddressField = Ext.getCmp(this.id+'_CMPCLOSE_ComboValue_695');
			if(activeAddressField) {
				activeAddressField.allowBlank = !chekActiv.checked;
				activeAddressField.validate();
				if (chekActiv.checked && !activeAddressField.getValue()) {
					error = 'Заполните адрес активного посещения';
				}
			}
		}

		if(getGlobalOptions().region.nick != 'ufa'){
			//номер телефона обязателен при выборе "подлежит активному посещению врачем" во вкладке "Результат"

			if(chekActiv){
				base_form.findField('Phone').allowBlank = !chekActiv.checked;
				base_form.findField('Phone').validate();
			}
		}
		
		var issetResultTripValue = false,
			allResultTripValues = false;
			
		//поля результат выезда отсутствуют на Перми
		if(!getGlobalOptions().region.nick.inlist(['perm', 'krym'])){
			issetResultTripValue =
				Ext.getCmp(this.id+'_'+'CMPCLOSE_CB_231').getValue()
				|| Ext.getCmp(this.id+'_'+'CMPCLOSE_CB_232').getValue()
				|| Ext.getCmp(this.id+'_'+'CMPCLOSE_CB_233').getValue()
				|| Ext.getCmp(this.id+'_'+'CMPCLOSE_CB_234').getValue()
				|| Ext.getCmp(this.id+'_'+'CMPCLOSE_CB_235').getValue()
				|| Ext.getCmp(this.id+'_'+'CMPCLOSE_CB_236').getValue()
				|| Ext.getCmp(this.id+'_'+'CMPCLOSE_CB_237').getValue()
				|| Ext.getCmp(this.id+'_'+'CMPCLOSE_CB_238').getValue();
			
		
			allResultTripValues = issetResultTripValue
				|| Ext.getCmp(this.id+'_'+'CMPCLOSE_CB_224').getValue()
				|| Ext.getCmp(this.id+'_'+'CMPCLOSE_CB_225').getValue()
				|| Ext.getCmp(this.id+'_'+'CMPCLOSE_CB_226').getValue()
				|| Ext.getCmp(this.id+'_'+'CMPCLOSE_CB_227').getValue()
				|| Ext.getCmp(this.id+'_'+'CMPCLOSE_CB_228').getValue()
				|| Ext.getCmp(this.id+'_'+'CMPCLOSE_CB_229').getValue()
				|| Ext.getCmp(this.id+'_'+'CMPCLOSE_CB_230').getValue()					
				|| Ext.getCmp(this.id+'_'+'CMPCLOSE_CB_239').getValue()
				|| (getGlobalOptions().region.nick == 'penza' && Ext.getCmp(this.id+'_'+'CMPCLOSE_CB_240').getValue());
		}
		else{
			issetResultTripValue = true,
			allResultTripValues = true;
		}
		
		
		if(
			(getGlobalOptions().region.nick.inlist(['kareliya','penza'])
			&& payTypeCombo && !payTypeCombo.hidden && payTypeCombo.getValue()
			&& !payTypeCombo.getValue().inlist([51]))
			|| issetResultTripValue
		) {
			diagField.allowBlank = true;
			diagField.validate();
			base_form.findField('Result_id').allowBlank = true;
			base_form.findField('Result_id').validate();
		} else {
			diagField.allowBlank = false;
			diagField.validate();
			base_form.findField('Result_id').allowBlank = false;
			base_form.findField('Result_id').validate();
		}

		if ( !base_form.isValid() ) {
			has_error = true;
			error = ERR_INVFIELDS_MSG;
		} else {
			if(!callCauseIsError){
				if (
					!( issetResultTripValue	|| (payTypeCombo && !payTypeCombo.hidden && payTypeCombo.getValue() && !payTypeCombo.getValue().inlist([51]))
					)
					&& (((diagField.getValue() == '') || (diagField.getValue() == null)) && !diagField.allowBlank)
				) {
					diagField.allowBlank = false;
					diagField.validate();
					error += (error.length?'<br />':'') + 'Заполните поле «Диагноз».';
				}

				if (
					!( issetResultTripValue )
					&& (
						!Ext.getCmp(this.id+'_'+'CMPCLOSE_CB_106').getValue()
						&& !Ext.getCmp(this.id+'_'+'CMPCLOSE_CB_107').getValue()
						&& !Ext.getCmp(this.id+'_'+'CMPCLOSE_CB_108').getValue()
					)
				) {
					base_form.findField('Result_id').allowBlank = false;
					base_form.findField('Result_id').validate();
					error += (error.length?'<br />':'') + 'Заполните поле «Результат оказания скорой медицинской помощи».';
				}

				if ( !allResultTripValues )
				{
					error += (error.length?'<br />':'') + 'Заполните поле «Результат выезда».';
				}

			}
            error += this.timeBlockValidate();
		}

		if ( error.length ) {
			me.hideLoadMask();
			this.formStatus = 'edit';
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function(){
					/*diagField.allowBlank = (getGlobalOptions().region.nick != 'buryatiya');
					Ext.getCmp(this.id+"_ResultId").allowBlank = true;
					*/
					var invalid = this.FormPanel.getInvalid()[0];
					if ( invalid ) {
						invalid.ensureVisible().focus();
					}
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: error,
				title: ERR_INVFIELDS_TIT
			});
			callbackFn(false);
			//return false;
		} else {
			var patientIdentification = function(Person, clback){
				// Проверка указанного пациента непосредственно перед сохранением
				if ( Person.getValue() == 0 ||
					Person.getValue() == null ||
					Person.getValue() == ''
				) {
					sw.swMsg.show({
						icon: Ext.MessageBox.QUESTION,
						msg: 'Данный пациент не обнаружен в базе данных пациентов РМИАС. Для оплаты карты вызова СМП, пациента необходимо добавить в базу данных пациентов РМИАС. Продолжить сохранение??',
						title: langs('Внимание'),
						buttons: Ext.Msg.YESNO,
						fn: function(buttonId, text, obj) {
							clback('yes' == buttonId);
						}
					});
				}else{
					clback(true);
				}
			}

			patientIdentification(base_form.findField('Person_id'), function(res){
				if(res){
					{
						var region = getGlobalOptions().region.nick;
						if (
							(
								//условие для крыма и типа оплаты ОМС refs #90628
								region == 'krym'
								&& payTypeCombo.getValue() == 171
								&& this.diagFinanceConfirm !== true
								&& diagField.getValue()
							)||(
								//условие для Астрахани оплачиваемых по ОМС refs #115292
								region == 'astra'
								&& diagField.getValue()
							)
						) {
							me.checkDiagFinance(function (response_obj, me) {
								if (
									response_obj &&
									(
										region == 'krym'
										&& (response_obj.DiagFinance_IsOms == 0
										|| (response_obj.Diag_Sex != null && response_obj.Diag_Sex != response_obj.Sex_id))
									) || (
										region == 'astra'
										&& (response_obj.DiagFinance_IsOms == 0
										|| (response_obj.DiagFinanceAgeGroup_Code != null && response_obj.DiagFinanceAgeGroup_Code != response_obj.PersonAgeGroup_Code))
									)
								) {
									//var me = this;
									Ext.MessageBox.show({
										title: 'Внимание!',
										msg: 'Введенный диагноз для данного пациента не оплачивается по ОМС. Продолжить сохранение?',
										buttons: Ext.Msg.YESNO,
										buttonText: {
											yes: 'Да',
											no: 'Нет'
										},
										fn: function (butn) {
											if (butn == 'no') {
												me.hideLoadMask();
												callbackFn(false);
												return false;
											} else if ('yes') {
												me.diagFinanceConfirm = true;
												callbackFn(true);
												//me.doSave();
											}
										}
									});
								} else {
									this.diagFinanceConfirm = true;
									callbackFn(true);
									//this.doSave();
								}
							})
						}else{
							callbackFn(true);
						}
					}
				}
				else{
					this.formStatus = 'edit';
					callbackFn(false);
					me.hideLoadMask();
					return false;
				}
			})
		};
	},

    timeBlockValidate: function(){
        var timeBlock = Ext.getCmp('timeBlock'),
            timeBlockItems = timeBlock.items.items,
			regionNick = getGlobalOptions().region.nick,
            error = '',
            me = this,
			allowBlankFields = ['TransportTime','ToHospitalTime','BackTime', 'CmpCloseCard_TranspEndDT','CmpCloseCard_PassTime'];

		/**
		 * поля
		 * 231 - Больной не найден на месте
		 * 232 - Отказ от помощи (от осмотра)
		 * 233 - Адрес не найден
		 * 234 - Ложный вызов
		 * 235 - Смерть до приезда бригады СМП
		 * 236 - Больной увезен до прибытия СМП
		 * 237 - Больной обслужен врачом поликлиники до приезда СМП
		 * 238 - Вызов отменен
		 */
		if (!regionNick.inlist(['perm', 'krym'])) {
			if (
				Ext.getCmp(this.id + '_' + 'CMPCLOSE_CB_231').getValue()
				|| Ext.getCmp(this.id + '_' + 'CMPCLOSE_CB_232').getValue()
				|| Ext.getCmp(this.id + '_' + 'CMPCLOSE_CB_233').getValue()
				|| Ext.getCmp(this.id + '_' + 'CMPCLOSE_CB_234').getValue()
				|| Ext.getCmp(this.id + '_' + 'CMPCLOSE_CB_235').getValue()
				|| Ext.getCmp(this.id + '_' + 'CMPCLOSE_CB_236').getValue()
				|| Ext.getCmp(this.id + '_' + 'CMPCLOSE_CB_237').getValue()
				|| Ext.getCmp(this.id + '_' + 'CMPCLOSE_CB_238').getValue()

			) {
				allowBlankFields.push('TransTime');
				allowBlankFields.push('GoTime');
				allowBlankFields.push('ArriveTime');
				allowBlankFields.push('TransportTime');
				allowBlankFields.push('ToHospitalTime');
				allowBlankFields.push('EndTime');
				allowBlankFields.push('BackTime');
			}

		} else {
			if(regionNick.inlist(['krym'])){

				/*Крымкая выборка*/
				if (
					Ext.getCmp(this.id + '_' + 'CMPCLOSE_CB_128').getValue() ||
					Ext.getCmp(this.id + '_' + 'CMPCLOSE_CB_129').getValue() ||
					Ext.getCmp(this.id + '_' + 'CMPCLOSE_CB_130').getValue() ||
					Ext.getCmp(this.id + '_' + 'CMPCLOSE_CB_135').getValue() ||
					Ext.getCmp(this.id + '_' + 'CMPCLOSE_CB_602').getValue() ||
					Ext.getCmp(this.id + '_' + 'CMPCLOSE_CB_603').getValue() ||
					Ext.getCmp(this.id + '_' + 'CMPCLOSE_CB_604').getValue() ||
					Ext.getCmp(this.id + '_' + 'CMPCLOSE_CB_605').getValue()
				) {
					allowBlankFields.push('TransTime');
					allowBlankFields.push('GoTime');
					allowBlankFields.push('ArriveTime');
					allowBlankFields.push('TransportTime');
					allowBlankFields.push('CmpCloseCard_TranspEndDT');
					allowBlankFields.push('EndTime');
					allowBlankFields.push('BackTime');
				}
			}
		}

        for(var i=0;i<timeBlockItems.length-1; i++){

            var dateTimeField = timeBlockItems[i],
                fieldLabel = dateTimeField.fieldLabel || dateTimeField.dateLabel,
                fieldDate = new Date(dateTimeField.hiddenField.value),
                fieldClearDate = dateTimeField.hiddenField.value ? new Date(dateTimeField.hiddenField.value).clearTime() : null;

			if(!dateTimeField.hiddenName.inlist(allowBlankFields) && !fieldDate.isValid()){
				error += (error.length?'<br />':'') + 'Заполните поле ' + fieldLabel;
			};

			if(fieldClearDate > new Date().clearTime()){
				error += (error.length?'<br />':'') + 'Время '+ fieldLabel + ' не может быть больше текущей даты';
			}

			//сравнение с предыдущими полями
			if(i>0){
				if(timeBlockItems[i-1].hiddenField.value){
					var prevField = timeBlockItems[i-1];
				}

				var prevDate = new Date(prevField.hiddenField.value),
					prevFieldLabel = prevField.fieldLabel || prevField.dateLabel;

				if( prevDate > fieldDate ){
					dateTimeField.ensureVisible().focus();
					prevFieldLabel = prevFieldLabel.toLowerCase().replace('время', 'времени');

					var txt = fieldLabel,
						arr = txt.split(/\s+/),
						firstWord = arr[0],
						ending = firstWord.substr(firstWord.length-2,2);

					arr.splice(0,1);

					switch(ending[1]){
						case 'и': {
							firstWord = firstWord.substr(0,firstWord.length-1) + 'a';
							break;
						}
						case 'а': {
							if(ending == 'ла'){firstWord = firstWord.substr(0,firstWord.length-1) + 'о'; break;}
							if(ending == 'на'){firstWord = firstWord.substr(0,firstWord.length-1) + ' по вызову '; break;}

							firstWord = firstWord.substr(0,firstWord.length-1) + '';
							break;
						}
						case 'я': {
							if(ending == 'мя'){break;}
							firstWord = firstWord.substr(0,firstWord.length-1) + 'е';
							break;
						}
					}
					txt = firstWord + ' ' + arr.join(' ');

					//есть нехорошие люди, которые для 2 сообщений придумали свою текстовку
					if(dateTimeField.hiddenName.inlist(['ArriveTime', 'CmpCloseCard_PassTime'])){
						switch(dateTimeField.hiddenName){
							case 'ArriveTime' : {
								txt = 'Прибытие на место вызова должно быть раньше начала транспортировки больного. Проверьте дату и время.';
								break;
							}
							case 'CmpCloseCard_PassTime' : {
								txt = 'Окончание вызова должно быть раньше отзвона по вызову. Проверьте дату и время.';
								break;
							}
						}
						error += (error.length?'<br />':'')+ txt;
					}
					else{
						error += (error.length?'<br />':'')+ txt +' не может совершиться раньше ' + prevFieldLabel;
					}
				}
			}
        }
        //здесь наверняка будут последующие доработки, тк потребуется сравнивать время не только
        //1 поля, которое в задаче а все поля в разделе, а может быть, и на форме

        var resultTripPanel = me.FormPanel.find('name', 'ResultEmergencyTrip');
        var dateCmps = (resultTripPanel && resultTripPanel[0]) ? resultTripPanel[0].panel.find('xtype', 'swdatetimefield') : [];
        var time_start = new Date(me.FormPanel.getForm().findField('AcceptTime').value);
		var transpEndDT;

		if(regionNick.inlist(['krym'])){
			transpEndDT = new Date(me.FormPanel.getForm().findField('CmpCloseCard_TranspEndDT').value);
		}
		else {
			transpEndDT = new Date(me.FormPanel.getForm().findField('ToHospitalTime').value);
		}

        for(var i=0;i<dateCmps.length-1; i++){
			if(dateCmps[i].isVisible()){
				//проверка на время в рез-те выезда с временем вкладки 1
				if( dateCmps[i].hiddenName == 'ComboValue_242' && (transpEndDT > new Date(dateCmps[i].hiddenField.value)) ){
					//error = 'Передача пациента в МО не может совершиться раньше прибытия в МО';
					error = 'Прибытие в МО должно быть раньше передачи пациента в МО. Проверьте дату и время.';
				}
				/*
				if(time_start > new Date(dateCmps[i].hiddenField.value)){
					//resultTripPanel[0].panel.find('code', dateCmps[i].parent_code)
					//error = 'Ошибка значения результат выезда.<br />' + dateCmps[i].dateLabel + ' не может совершиться раньше времени принятия вызова';
					error = 'Ошибка значения результат выезда.<br />'
						+ dateCmps[i].dateLabel
						+ ' не может совершиться раньше времени принятия вызова';
				};
				*/
			}
        }

        return error;
    },

	loadEmergencyTeamsWorkedInATime: function(){

		var base_form = this.FormPanel.getForm();

		if (base_form.findField('TransTime').getStringValue() != '') {
			var time_start = Date.parseDate( base_form.findField('TransTime').getStringValue(), 'd.m.Y H:i' );
		} else {
			var time_start = new Date();
		}
		var formattedTS = Ext.util.Format.date(time_start, 'd.m.Y H:i:s'),
			onDate = Ext.util.Format.date(time_start, 'd.m.Y'),
			team_combo = base_form.findField('EmergencyTeam_id'),
			docField = base_form.findField('MedStaffFact_id'),
			cmpCallCard_id = base_form.findField('CmpCallCard_id').getValue();


		if( team_combo && team_combo.store && (cmpCallCard_id || time_start.isValid()) ){
			team_combo.getStore().load({
				params: {
					AcceptTime: formattedTS,
					CmpCallCard_id: cmpCallCard_id
				},
				callback: function(recs) {
					team_combo.setValue(team_combo.getValue());

					var selectedRec = team_combo.getStore().getAt(team_combo.getStore().findBy(function(rec) {return rec.get('EmergencyTeam_id') == team_combo.getValue();}));

					if(!selectedRec){
						team_combo.clearValue();
						team_combo.enable();
					}
					if(selectedRec && docField && docField.xtype == 'swmedstafffactglobalcombo'){

						var doc = docField.getStore().getAt(docField.getStore().findBy(function(rec) {return rec.get( 'MedStaffFact_id') == selectedRec.json.MedStaffFact_id;}));
						if(doc){
							docField.setValue(doc.data.MedStaffFact_id);
							docField.disable();
						}
					}
				}
			})
		}

	},

	calcSummTime: function(){
		var base_form = this.FormPanel.getForm(),
			time_start = null,
			time_finish = null,
			all_min = null,
			hours = '00',
			minutes = '00',
			time_start =  new Date(base_form.findField('AcceptTime').value),
			time_finish = new Date(base_form.findField('EndTime').value),
			summTime = base_form.findField('SummTime');

		if ( time_start !== null && time_finish !== null && time_start <= time_finish) {
			var dd_diff = ( time_finish - time_start ) / 1000; // в секундах
			all_min = dd_diff / 60;
			hours = Math.floor( dd_diff / 60 / 60 );
			dd_diff -= hours * 60 * 60;
			minutes = Math.floor( dd_diff / 60);
			dd_diff -= minutes * 60;
			if ( hours < 10 ) {hours = '0'+hours;}
			if ( minutes < 10 ) {minutes = '0'+minutes;}
		}
		if(summTime)
			summTime.setValue( hours + ':' + minutes );
		return all_min;
	},

	setEmergencyTeam: function(CmpCallCard_id,EmergencyTeam_data) {
		var cb = this.setStatusCmpCallCard;
		var cb2 = this.closeCmpCallCard;
		this.showLoadMask('Назначение...');
		var parentObject = this,
			teamNumField = parentObject.FormPanel.getForm().findField('EmergencyTeamNum'),
			teamIdField = parentObject.FormPanel.getForm().findField('EmergencyTeam_id');
		if(teamNumField)
			teamNumField.setValue(EmergencyTeam_data.EmergencyTeam_Num);
		if(teamIdField)
			teamIdField.setValue(EmergencyTeam_data.EmergencyTeam_id);
		Ext.Ajax.request({
			params: {
				EmergencyTeam_id: EmergencyTeam_data.EmergencyTeam_id,
				CmpCallCard_id: CmpCallCard_id
			},
			url: '/?c=CmpCallCard&m=setEmergencyTeamWithoutSending',
			callback: function(o, s, r) {
				this.hideLoadMask();
			}.createDelegate(this)
		});
	},

	actionCaseFunction: function(action, params, arguments){
		var me = this,
			base_form = me.FormPanel.getForm();
		me.tabPanel.setActiveTab(3);
		me.resetFields();
		var diag_sid_panel = Ext.getCmp('diag_sid_panel'),
			diag_ooid_panel = Ext.getCmp('diag_ooid_panel');
		if (diag_sid_panel && !(diag_sid_panel.items.length>0))
			diag_sid_panel.addField();
		if (diag_ooid_panel && !(diag_ooid_panel.items.length>0))
			diag_ooid_panel.addField();
		me.tabPanel.setActiveTab(0);
		if(base_form.findField('CmpCloseCard_IsExtra'))
			base_form.findField('CmpCloseCard_IsExtra').focus(true);

		//для вологды делаем обязательным поле "бригада СМП"
		if(base_form.findField('EmergencyTeam_id')) {
			base_form.findField('EmergencyTeam_id').setAllowBlank(!getRegionNick().inlist(['vologda']));
		}

		switch ( action ) {
			case 'add':
			{
				me.enableEdit(true);
				me.buttons[2].hide();
				
				/*
				if(me.UslugaViewFrame){
					me.UslugaViewFrame.setReadOnly(false);
				}
				*/
				//me.AutoBrigadeStatusChange = true;
				
				var brigSelectBtn = Ext.getCmp('BrigSelectBtn');
				if(brigSelectBtn)brigSelectBtn.hide();

				base_form.load({
					url: '/?c=CmpCallCard&m=loadCmpCloseCardEditForm',
					params: params,
					failure: function() {
						me.hideLoadMask();
						sw.swMsg.alert('Ошибка', 'Ошибка при загрузке данных формы', function() {me.hide();});
					},
					success: function(form,resultData) {
						me.hideLoadMask();
						var data = Ext.util.JSON.decode(resultData.response.responseText);
						me.setFieldsValuesOnLoad(data[0]);

						me.setTitle(langs('Карта вызова: Закрытие'));

						var armType = (!Ext.isEmpty(sw.Promed.MedStaffFactByUser) && !Ext.isEmpty(sw.Promed.MedStaffFactByUser.current) && !Ext.isEmpty(sw.Promed.MedStaffFactByUser.current.ARMType)) ? sw.Promed.MedStaffFactByUser.current.ARMType : null;
						base_form.findField('ARMType').setValue(armType || me.ARMType);

						me.calcSummTime();
						me.getTheDistanceInATimeInterval();
					}
				});
				break;
			}
			case 'edit': {
				me.setTitle(langs('Карта вызова: Редактирование'));
				this.onEditShow(params);

				break;
			}

			case 'view':
			{
				me.setTitle(langs('Карта вызова: Просмотр'));
				this.onEditShow(params);

				break;
			}
			case 'stream':{
				me.setTitle(langs('Карта вызова: Поточный ввод'));
				me.showInStreamMode();
				me.initexistenceNumbersDayYearListeners();
			}

		}
	},

	showInStreamMode: function(){
		var me = this,
			base_form = me.FormPanel.getForm(),
			opts = getGlobalOptions(),
			params;

		Ext.Ajax.request({
			callback: function(opt, success, response) {
				me.hideLoadMask();

				if ( success ) {
					var response_obj = Ext.util.JSON.decode(response.responseText);

					me.enableEdit(true);

					params = {
						Area_id: opts.region.number,
						City_id: opts.region.number,
						ARMType: me.ARMType,
						Year_num: response_obj[0].CmpCallCard_Ngod,
						Day_num: response_obj[0].CmpCallCard_Numv
					};


					if(!getGlobalOptions().region.nick.inlist(['kareliya'])){
						// Фильтруем отделения которые привязаны к службе СМП
						setLpuSectionGlobalStoreFilter({
							arrayLpuUnitType: [12]
						});
					}

					//установка времени
					var timeBlock = Ext.getCmp('timeBlock'),
						timeBlockItems = timeBlock.items.items;

					for(var i=0;i<timeBlockItems.length-1; i++){
                        base_form.findField(timeBlockItems[i].hiddenName).setValue(new Date());
					}

					//активация полей адреса
					//var addressBlock = Ext.getCmp('addressBlock'),
					var addressBlock = me.FormPanel.find('refId', 'addressBlock'),
						addressBlockItems = addressBlock[0] ? addressBlock[0].items.items : [];

					for(var i=0;i<addressBlockItems.length; i++){
						addressBlockItems[i].enable();
					}

					//активация полей пациента
					base_form.findField('Name').enable();
					base_form.findField('Middle').enable();
					base_form.findField('Fam').enable();
					var ageText = Ext.getCmp(me.id+'AgeText');
					if(ageText) ageText.setText('');

					var socialCombo = base_form.findField('SocialCombo'),
						resultCombo = base_form.findField('ResultEmergencyTrip'),
						resultEmergencyTripGroup = base_form.findField('ResultEmergencyTrip');

					if(socialCombo){
						socialCombo.allowBlank = false;
						socialCombo.validate();
					};
					if(resultCombo){
						resultCombo.allowBlank = false;
						resultCombo.validate();
					};
					if(resultEmergencyTripGroup){
						resultEmergencyTripGroup.allowBlank = false;
						resultEmergencyTripGroup.validate();
					}

					//me.setTitle('Карта вызова: поточный ввод');

					var brigSelectBtn = Ext.getCmp('BrigSelectBtn');
					if(brigSelectBtn)brigSelectBtn.show();

					if (me.DrugGrid) {
						//me.DrugGrid.show();
						me.DrugGrid.setParam('CmpCallCard_id', null, true);
						me.DrugGrid.removeAll();
					}

					me.setFieldsValuesOnLoad(params);

					// Фокус на первый видимый элемент
					var active = me.FormPanel.getFirstActiveField();
					if (active) {
						active.ensureVisible().focus();
					}
				}
				else {
					sw.swMsg.alert(langs('Ошибка'), langs('Ошибка при определении номера вызова'));
				}
			},
			url: '/?c=CmpCallCard&m=getCmpCallCardNumber'
		});
	},

	onEditShow: function(params) {
		var me = this,
			base_form = me.FormPanel.getForm();

		me.enableEdit(true);

		base_form.load({
			url: '/?c=CmpCallCard&m=loadCmpCloseCardViewForm',
			failure: function() {
				me.hideLoadMask();
				sw.swMsg.alert('Ошибка', 'Ошибка при загрузке данных формы', function() {me.hide();} );
			},
			params: params,
			success: function(form,resultData) {
				var data = Ext.util.JSON.decode(resultData.response.responseText);

				//Проверяем возможность редактирования документа
				if (me.action === 'edit' && data[0].CmpCloseCard_id) {
					Ext.Ajax.request({
						url: '/?c=Evn&m=CommonChecksForEdit',
						params: {
							Evn_id: (getGlobalOptions().region.nick == 'kareliya') ? data[0].CmpCallCard_id : data[0].CmpCloseCard_id,
							MedStaffFactDoc_id: (!Ext.isEmpty(sw.Promed.MedStaffFactByUser) && !Ext.isEmpty(sw.Promed.MedStaffFactByUser.current) && !Ext.isEmpty(sw.Promed.MedStaffFactByUser.current.MedStaffFactDoc_id)) ? sw.Promed.MedStaffFactByUser.current.MedStaffFactDoc_id : null,
							ArmType: (!Ext.isEmpty(sw.Promed.MedStaffFactByUser) && !Ext.isEmpty(sw.Promed.MedStaffFactByUser.current) && !Ext.isEmpty(sw.Promed.MedStaffFactByUser.current.ARMType)) ? sw.Promed.MedStaffFactByUser.current.ARMType : null,
							isCMPCloseCard: 1
						},
						success: function (response, options) {

							if (!Ext.isEmpty(response.responseText)) {
								var response_obj = Ext.util.JSON.decode(response.responseText);

								if (response_obj.success == false) {
									sw.swMsg.alert(langs('Ошибка'), response_obj.Error_Msg ? response_obj.Error_Msg : langs('Ошибка при загрузке данных формы'));
									me.action = 'view';
								}

								if (response_obj.Alert_Msg) {
									sw.swMsg.alert(langs('Внимание'), response_obj.Alert_Msg);
								}
							}

							me.onLoadForm(data[0]);
						},
						failure: function (response, options) {
							sw.swMsg.alert(langs('Ошибка'), langs('Ошибка при загрузке данных формы'), function () {
								me.hide();
							});
						}
					});
				}
				else{
					me.onLoadForm(data[0]);
				}
			}
		});

	},
	onLoadForm: function(data) {
		var me = this,
			base_form = me.FormPanel.getForm(),
			region = getGlobalOptions().region.nick,
			isSMPServer = getGlobalOptions().IsSMPServer,
			uslugaGrid = me.FormPanel.find('refId', 'uslugaGrid')[0];

		// #160962 блок панели услуг для основного сервера на уфе, хакасии и астрахани
		if (region.inlist(['ufa', 'astra', 'khak']) && !isSMPServer && me.action == 'edit') {
			uslugaGrid.setDisabled(true);
		} else if (uslugaGrid) {
			uslugaGrid.setDisabled(false);
		}

		Ext.Ajax.request({
			url: '/?c=CmpCallCard&m=getSidOoidDiags',
			params: {
				CmpCloseCard_id:data.CmpCloseCard_id
			},
			callback: function (opt, success, response) {
				me.tabPanel.setActiveTab(3);
				me.resetFields();
				var diag_sid_panel = Ext.getCmp('diag_sid_panel'),
					diag_ooid_panel = Ext.getCmp('diag_ooid_panel'),
					response_obj = [];
				if (success) {
					if (!Ext.isEmpty(response.responseText)) {
						response_obj = Ext.util.JSON.decode(response.responseText);
					}
					for (var i = 0; i < response_obj.length; i++) {
						if (response_obj[i].DiagSetClass_id == 3)
							diag_sid_panel.addField(response_obj[i].Diag_id);
						else
							diag_ooid_panel.addField(response_obj[i].Diag_id);
					}
				}
				if (diag_sid_panel && !(diag_sid_panel.items.length>0))
					diag_sid_panel.addField();

				if (diag_ooid_panel && !(diag_ooid_panel.items.length>0))
					diag_ooid_panel.addField();

				me.tabPanel.setActiveTab(0);
			}
		});

		me.setFieldsValuesOnLoad(data);

		if (me.action == 'view') {

			me.enableEdit(false);
			if (me.DrugGrid) me.DrugGrid.setReadOnly(true);
		};

		// Заполнение комбобоксов из БД
		me.setComboValuesOnLoad();

		// Заполнение значений использованного оборудования
		if (getGlobalOptions().region.nick == 'pskov') {
			me.loadCmpCloseCardEquipmentViewForm(data);
		}
		/*
		if(me.UslugaViewFrame){
			me.UslugaViewFrame.setReadOnly(me.action == 'view');
			me.UslugaViewFrame.getGrid().getStore().load({params: {CmpCallCard_id: base_form.findField('CmpCallCard_id').getValue()}});
		}
		*/
		
		// перенес загрузку грида "Использование медикаментов" на загрузку значений в LpuBuilding_id
		// т.к. теперь форма расхода медикаментов должна зависеть от настроек подразделения, которое выбрано в Карте вызова refs #116127
		/*
		if (me.DrugGrid) {
			//загрузка грида "Использование медикаментов"
			me.DrugGrid.setParam('CmpCallCard_id', base_form.findField('CmpCallCard_id').getValue(), true);
			me.DrugGrid.loadData();
		}
		*/
		//загрузка экспертный оценок
		me.loadCmpCloseCardExpertResponses(data)

		//if(me.UslugaPanel) me.loadUslugaPanelData();

		var brigSelectBtn = Ext.getCmp('BrigSelectBtn');
		if(brigSelectBtn)brigSelectBtn.setVisible(data.addedFromStreamMode === 'true');

		//me.setTitle(WND_AMB_CCCEFCLOSE);

		me.hideLoadMask();
		var armType = (!Ext.isEmpty(sw.Promed.MedStaffFactByUser) && !Ext.isEmpty(sw.Promed.MedStaffFactByUser.current) && !Ext.isEmpty(sw.Promed.MedStaffFactByUser.current.ARMType)) ? sw.Promed.MedStaffFactByUser.current.ARMType : null;
		base_form.findField('ARMType').setValue(armType || me.ARMType);
	},
	//установка значений в поля при загрузке
	setFieldsValuesOnLoad: function(formData){
		var opts = getGlobalOptions(),
			me = this,

			base_form = me.FormPanel.getForm(),
			regionNick = getRegionNick(),
			resultIdField = base_form.findField('Result_id'),
			AcceptTimeField = base_form.findField('AcceptTime'),
			armType = (!Ext.isEmpty(sw.Promed.MedStaffFactByUser) && !Ext.isEmpty(sw.Promed.MedStaffFactByUser.current) && !Ext.isEmpty(sw.Promed.MedStaffFactByUser.current.ARMType)) ? sw.Promed.MedStaffFactByUser.current.ARMType : getGlobalOptions().curARMType,
			fieldsTop = base_form.items.items,
			allFields = [];

			var getAllFields = function(cmps){
				for(var i = 0; i < cmps.length; i++){
                    if( !cmps[i].getName){
                    }
					var fieldCmp = cmps[i],
						field = fieldCmp.getName(),
						fieldVal = fieldCmp.getValue();

					allFields.push(fieldCmp);
					if(cmps[i].items && cmps[i].items.items.length){getAllFields(cmps[i].items.items)}
				}
			}
		getAllFields(fieldsTop);

		for(var i = 0; i < allFields.length; i++){

			var fieldCmp = allFields[i],
				field = fieldCmp.getName();//  fieldCmp.name || fieldCmp.hiddenName;

			if (me.formData && fieldCmp.id.indexOf('_CMPCLOSE_CB_') > 0 && me.formData[field]) {
				// логика для восстановления чекбоксов после переоткрытия формы.
				if (typeof me.formData[field] == "object") {
					me.formData[field].forEach(function(str) {
						var cmp = Ext.getCmp(this.id+'_CMPCLOSE_CB_'+me.formData[field]);
						if (cmp) {
							cmp.setValue(true);
						}
					});
				} else {
					var cmp = Ext.getCmp(this.id+'_CMPCLOSE_CB_'+me.formData[field]).setValue(true);
					if (cmp) {
						cmp.setValue(true);
					}
				}
			}

			switch(field){
				case 'CallType_id' : {
					//установка значения первичный по умолчанию
					if(!formData.CallType_id){
						var indexRec = fieldCmp.store.findBy(function(rec) {return rec.get('CmpCallType_Code') == 1;});
						if(indexRec != -1){
							fieldCmp.setValue( fieldCmp.store.getAt(indexRec).get('CmpCallType_id') );
						}
					}
					else{
						fieldCmp.setDisabled( !me.action.inlist(['edit','stream']));
					}


					break;
				}
				case 'AgeType_id2' : {
					if(fieldCmp.getValue() > 0){
						Ext.getCmp(me.id+'_'+'CMPCLOSE_CB_'+fieldCmp.getValue()).setValue(true);
					}
					break;
				}
				case 'sub1WorkAD' : {
					if(formData.WorkAD)
					{
						fieldCmp.setValue(formData.WorkAD.split('/')[0]);
					}
					break;
				}
				case 'Year_num' : {
					fieldCmp.setValue(formData.Year_num);
					fieldCmp.disable();
					break;
				}
				case 'Day_num' : {
					fieldCmp.setValue(formData.Day_num);
					fieldCmp.disable();
					break;
				}
				case 'CmpCloseCard_DayNumPr' : {
					fieldCmp.setValue(formData.CmpCloseCard_DayNumPr);
					fieldCmp.disable();
					continue;
					break;
				}
				case 'CmpCloseCard_YearNumPr' : {
					fieldCmp.setValue(formData.CmpCloseCard_YearNumPr);
					fieldCmp.disable();
					continue;
					break;
				}
				case 'pmUser_insName' : {
					fieldCmp.setValue(formData.pmUser_insName);
					fieldCmp.disable();
					break;
				}
				case 'sub2WorkAD' : {
					if(formData.WorkAD)
					{
						fieldCmp.setValue(formData.WorkAD.split('/')[1]);
					}
					break;
				}
				case 'sub1AD' : {
					if(formData.AD)
					{
						fieldCmp.setValue(formData.AD.split('/')[0]);
					}
					break;
				}
				case 'sub2AD' : {
					if(formData.AD)
					{
						fieldCmp.setValue(formData.AD.split('/')[1]);
					}
					break;
				}
				case 'sub1EAD' : {
					if(formData.EfAD)
					{
						fieldCmp.setValue(formData.EfAD.split('/')[0]);
					}
					break;
				}
				case 'sub2EAD' : {
					if(formData.EfAD)
					{
						fieldCmp.setValue(formData.EfAD.split('/')[1]);
					}
					break;
				}
				case 'CmpCloseCard_IsNMP' : {
					if(formData.CmpCloseCard_IsNMP)
					{
						fieldCmp.setValue(formData.CmpCloseCard_IsNMP == 2 ? true : false);
					}
					break;
				}
				case 'CmpCloseCard_IsExtra' : {
					if(formData.CmpCloseCard_IsExtra)
					{
						fieldCmp.setValue(formData.CmpCloseCard_IsExtra);
						//fieldCmp.disable();
					}
					break;
				}	
				case 'CmpCloseCard_IsSignList' : {
					if(formData.CmpCloseCard_IsSignList)
					{
						fieldCmp.setValue(formData.CmpCloseCard_IsSignList == 2 ? true : false);
						//fieldCmp.disable();
					}
					break;
				}
				case 'CmpCallCard_isControlCall' : {
					if(formData.CmpCallCard_isControlCall)
					{
						fieldCmp.setValue(formData.CmpCallCard_isControlCall == 2 ? true : false);
						//fieldCmp.disable();
					}
					break;
				}
				case 'CmpSecondReason_id' : {
					if(formData.CmpSecondReason_id)
					{
						base_form.findField('CallType_id').setValue(3);
					}
					break;
				}
				case 'PayType_id' : {
					var diagField = base_form.findField('Diag_id'),
						pt_idx = fieldCmp.getStore().findBy(function(rec) {return rec.get('PayType_Code') == 1;}),
						pt_record = fieldCmp.getStore().getAt(pt_idx);

					if ( fieldCmp.getValue() == '' && (pt_idx !== -1 ) ) {
						fieldCmp.setValue(pt_record.get('PayType_id'));
					}
					//anyway
					if(typeof pt_record == 'object'){
						//если полис не ОМС то делаем поле результат выезда необязательным
						//иначе - обязательный
						diagField.allowBlank = false;
						diagField.validate();
					}

					break;
				}

				case 'Ktov': {
					var Ktov = fieldCmp;
					var CallerType = base_form.findField('CmpCallerType_id');

					if(CallerType.getValue() > 0){
						var Ktov_rec = Ktov.findRecord(Ktov.valueField, CallerType.getValue());

						if(Ktov_rec) Ktov.setRawValue(Ktov_rec.get(Ktov.displayField));
					} else {
						Ktov.setRawValue(Ktov.getValue());
					}

					Ktov.disable();


					break;
				}

				case 'CmpCallerType_id' : {
					if(fieldCmp.getValue() > 0){
						var Ktov = base_form.findField('Ktov'),
							Ktov_rec = Ktov.findRecord(Ktov.valueField, fieldCmp.getValue());

						if(Ktov_rec){
							Ktov.setRawValue( Ktov_rec.get( Ktov.displayField ) );
						}

						Ktov.disable();


					}

					break;
				}
				case 'SocStatusNick' : {
					//для всех регионов - радиогруппа
					break;
				}
				case 'Phone' : {

					fieldCmp.disable();

					break;
				}

				case 'CmpCallPlaceType_id' : {
					//для всех регионов - радиогруппа
					//временный костыль надо будет подумать над привязкой
					if(formData.CmpCallPlaceType_id){
						var valCmpCallPlaceType_id = {};
						valCmpCallPlaceType_id[formData.CmpCallPlaceType_id] = true;
						fieldCmp.setValues(valCmpCallPlaceType_id);

						//if( me.action == 'add'){
                        //
						//	var inputComboVal = formData.CmpCallPlaceType_id,
						//		allFlds = fieldCmp.items.items;
                        //
						//	for(var k=0;k<(allFlds.length-1); k++){
						//		var radioCmp = allFlds[k];
                        //
						//		//квартира
						//		if((inputComboVal == 1) && (radioCmp.code == 181)) {radioCmp.setValue(true); break;};
                        //
						//		//улица
						//		if((inputComboVal == 2) && (radioCmp.code == 180)) {radioCmp.setValue(true); break;};
                        //
						//		//общ место
						//		if((inputComboVal == 3) && (radioCmp.code == 183)) {radioCmp.setValue(true); break;};
                        //
						//		//раб место
						//		if((inputComboVal == 4) && (radioCmp.code == 182)) {radioCmp.setValue(true); break;};
                        //
						//		//трасса
						//		if((inputComboVal == 8) && (radioCmp.code == 600)) {radioCmp.setValue(true); break;};
                        //
						//		//дош учрежд
						//		if((inputComboVal == 11) && (radioCmp.code == 189)) {radioCmp.setValue(true); break;};
                        //
						//		//школа
						//		if((inputComboVal == 10) && (radioCmp.code == 188)) {radioCmp.setValue(true); break;};
                        //
						//		//лпу
						//		if((inputComboVal == 6) && (radioCmp.code == 599)) {radioCmp.setValue(true); break;};
                        //
						//		//остальное/другое/прочее
						//		if((inputComboVal == 9) && (radioCmp.code == 673)) {radioCmp.setValue(true); break;};
                        //
						//		//квартира
						//		if((inputComboVal == 12) && (radioCmp.code == 181)) {radioCmp.setValue(true); break;};
                        //
						//		//улица
						//		if((inputComboVal == 13) && (radioCmp.code == 180)) {radioCmp.setValue(true); break;};
                        //
						//		//общ место
						//		if((inputComboVal == 14) && (radioCmp.code == 183)) {radioCmp.setValue(true); break;};
                        //
						//		//раб место
						//		if((inputComboVal == 15) && (radioCmp.code == 182)) {radioCmp.setValue(true); break;};
                        //
						//		//подстанция
						//		if((inputComboVal == 16) && (radioCmp.code == 191)) {radioCmp.setValue(true); break;};
                        //
						//		//больница
						//		if((inputComboVal == 17) && (radioCmp.code == 184)) {radioCmp.setValue(true); break;};
                        //
						//		//школа
						//		if((inputComboVal == 18) && (radioCmp.code == 188)) {radioCmp.setValue(true); break;};
                        //
						//		//дош учрежд
						//		if((inputComboVal == 19) && (radioCmp.code == 189)) {radioCmp.setValue(true); break;};
                        //
						//		//дош учрежд
						//		if((inputComboVal == 20) && (radioCmp.code == 191)) {radioCmp.setValue(true); break;};
                        //
						//	}
						//}

					}

					break;
				}

				case 'Area_id' : {
					if(fieldCmp.getValue() > 0){
						fieldCmp.getStore().load({
							scope: fieldCmp,
							params: {
								region_id: opts.region.number
							},
							callback: function() {
								this.setValue(formData.Area_id);
							}
						})
					}
					break;
				}
				
				case 'KLRgn_id': {
					fieldCmp.hideContainer();
					break;
				}
				
				case 'CmpResult_id': {
					if(formData.CmpResult_id && formData.CmpResult_id.inlist([11,12,13,14,15]) ){
						base_form.findField('Lpu_hid').showContainer();
					}
					else{
						base_form.findField('Lpu_hid').hideContainer();
					}
					break;
				}

				case 'ComboValue_566':
				case 'ComboValue_241':
				case 'Lpu_hid': {
					if(formData.Lpu_hid){
						fieldCmp.setValue(formData.Lpu_hid);
					};
					break;
				}

                case 'ComboValue_687':
                case 'ComboValue_689':{
                    if(formData.Lpu_hid){
                        fieldCmp.store.load({
                            scope: fieldCmp,
                            callback: function(){
                                var indexRec = this.store.findBy(function(rec) {return rec.get('Lpu_id') == formData.Lpu_hid;});
                                if(indexRec != -1){
                                    this.setValue( this.store.getAt(indexRec).get('Org_id') );
                                }
                            }
                        });
                    }
                    break;
                }

				case 'KLAreaStat_idEdit' : {
					if(formData.Area_id > 0){
						fieldCmp.store.findBy(function(r,id){
							if (r.get('KLSubRGN_id') == formData.Area_id && id>0){
								fieldCmp.setValue(id);
								
								var KLRgnField = base_form.findField('KLRgn_id');
								
								if(KLRgnField && KLRgnField.store && r.get('KLRGN_id') && r.get('KLCountry_id')){
									KLRgnField.getStore().load({
										params: {country_id: r.get('KLCountry_id') },
										scope: KLRgnField,
										callback: function(){KLRgnField.setValue(r.get('KLRGN_id'));}
									})									
								}
							} 
						})
					} else if (formData.City_id > 0) {
						fieldCmp.store.findBy(function(r,id){
							if (r.get('KLCity_id') == formData.City_id && id>0){
								fieldCmp.setValue(id);
								
								var KLRgnField = base_form.findField('KLRgn_id');
								
								if(KLRgnField && KLRgnField.store && r.get('KLRGN_id') && r.get('KLCountry_id')){
									KLRgnField.getStore().load({
										params: {country_id: r.get('KLCountry_id') },
										scope: KLRgnField,
										callback: function(){KLRgnField.setValue(r.get('KLRGN_id'));}
									})									
								}
							}
						})
					}
					break;
				}

				case 'City_id' : {
					fieldCmp.reset();
					if(formData.City_id > 0){
						fieldCmp.getStore().load({
							scope: fieldCmp,
							params: {
								subregion_id: (formData.Area_id > 0) ? formData.Area_id : opts.region.number
							},
							callback: function() {
								var rec = this.getStore().getAt( this.getStore().findBy(function(rec) {return rec.get('City_id') == formData.City_id;}) );
								
								if(rec) {
									this.setValue(formData.City_id);
									//var townField = base_form.findField('Town_id');
									
									//townField.allowBlank = true;
									//townField.isValid();
								}
								else{
									fieldCmp.reset();
								}

								var KLAreaStat_idEdit = base_form.findField('KLAreaStat_idEdit'),
									KLArea = base_form.findField('Area_id');
									
								if(KLAreaStat_idEdit) {
									KLAreaStat_idEdit.getStore().each(function (r) {
										if (formData.City_id == r.get('KLCity_id')) {
											KLAreaStat_idEdit.setValue(r.get('KLAreaStat_id'));
										}
										;
										if (formData.City_id == r.get('KLSubRGN_id')) {
											KLAreaStat_idEdit.setValue(r.get('KLAreaStat_id'));
											//KLArea.setValue(r.get('KLSubRGN_id'));
											fieldCmp.reset();

											KLArea.getStore().load({
												params: {region_id: r.get('KLRGN_id')},
												callback: function () {
													KLArea.setValue(r.get('KLSubRGN_id'));
													fieldCmp.reset();
													//KLArea.fireEvent('beforeselect', KLArea, KLArea.getStore().getAt(KLArea.getStore().findBy(function(rec) {return rec.get('SubRGN_id') == KLArea.getValue();}.createDelegate(this))));
												}
											});

											//KLAreaStat_idEdit.fireEvent('beforeselect', KLAreaStat_idEdit, r);
										}
										;

									});
								}
							}
						})
					}
					break;
				}

				case 'Town_id' : {
					if(formData.Town_id > 0){
						fieldCmp.getStore().load({
							scope: fieldCmp,
							params: {
								city_id: (formData.Area_id > 0) ? formData.Area_id : formData.City_id
							},
							callback: function() {
								this.setValue(formData.Town_id);
								//var cityField = base_form.findField('City_id');
								
								//cityField.allowBlank = true;
								//cityField.isValid();
							}
						})
					}
					break;
				}

				case 'Street_id' : {
					if( Number(fieldCmp.getRawValue()) > 0 && fieldCmp.store ) {

						fieldCmp.getStore().load({
							scope: fieldCmp,
							params: {
								town_id: (formData.Town_id || formData.City_id || formData.Area_id || formData.Street_id),
								showSocr: 1
							},
							callback: function() {
								this.setValue(formData.Street_id);
							}
						});
					}
					else{
						fieldCmp.setValue(formData.Street_id);
					}

					break;
				}

				case 'StreetAndUnformalizedAddressDirectory_id': {

					var CmpCallCard_Dom = base_form.findField('House'),
						CmpCloseCard_UlicSecond = base_form.findField('CmpCloseCard_UlicSecond');

					//это похоже на копипасту, но не совсем так, тк при предварительной проверке на значение не ждем загрухки стора и отображаем/скрываем компонент
					//что не приводит к явно заметному исчезновению компонента
					if(formData.CmpCloseCard_UlicSecond){
						CmpCloseCard_UlicSecond.showContainer();
						CmpCallCard_Dom.hideContainer();
					}
					else {
						CmpCloseCard_UlicSecond.hideContainer();
						CmpCallCard_Dom.showContainer();
					}

					if(formData.Town_id || formData.City_id || formData.Area_id || formData.Street_id){

						var UnformalizedAddressDirectory_id = base_form.findField('UnformalizedAddressDirectory_id'),
							CmpCallCard_Ulic = base_form.findField('CmpCallCard_Ulic');

						fieldCmp.getStore().load({
							scope: fieldCmp,
							params: {
								town_id: (formData.Town_id || formData.City_id || formData.Area_id || formData.Street_id),
								showSocr: 1
							},
							callback: function() {
								var rec = this.getStore().getAt( this.getStore().findBy(function(record) {
										return record.get('StreetAndUnformalizedAddressDirectory_id') == formData.StreetAndUnformalizedAddressDirectory_id;})
									);

								if(rec){
									if(UnformalizedAddressDirectory_id) UnformalizedAddressDirectory_id.setValue(rec.get('UnformalizedAddressDirectory_id'));
									if(CmpCallCard_Ulic) CmpCallCard_Ulic.setValue(rec.get('StreetAndUnformalizedAddressDirectory_Name'));
									this.setValue(formData.StreetAndUnformalizedAddressDirectory_id);
								}
								else{
									this.setValue(formData.CmpCloseCard_Street);
								}

								CmpCloseCard_UlicSecond.getStore().data = this.getStore().data;

								if(formData.CmpCloseCard_UlicSecond){
									var secondStreetRec = CmpCloseCard_UlicSecond.getStore().getAt( this.getStore().findBy(function(record) {
											return record.get('KLStreet_id') == formData.CmpCloseCard_UlicSecond;})
										),
										disableInCrossRoadsModeFields = ['Korpus', 'Office', 'Room', 'Entrance', 'Level', 'CodeEntrance'];

									disableInCrossRoadsModeFields.forEach(function(item,i) {
										var cmp = base_form.findField(item);

										if(cmp){
											cmp.setDisabled(secondStreetRec);
										}
									});

									if(secondStreetRec){
										CmpCloseCard_UlicSecond.setValue(secondStreetRec.get('StreetAndUnformalizedAddressDirectory_id'));
										CmpCloseCard_UlicSecond.showContainer();
										CmpCallCard_Dom.hideContainer();
									}
									else{
										CmpCloseCard_UlicSecond.hideContainer();
										CmpCallCard_Dom.showContainer();
									}
								}

							}
						})
					}
					/*else{
						fieldCmp.setValue(formData.CmpCloseCard_Street);
					}*/

					break;
				}

				case 'LpuSection_id' : {
					fieldCmp.getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));
					fieldCmp.setValue(formData.LpuSection_id);
					break;
				}

				case 'LpuBuilding_IsWithoutBalance' : {
					if(formData.LpuBuilding_IsWithoutBalance)
					{
						fieldCmp.setValue(formData.LpuBuilding_IsWithoutBalance == 2 ? true : false);
					}
					break;
				}

				case 'MedPersonal_id' : {
					if(formData.MedPersonal_id > 0){
						var cb174 = Ext.getCmp(me.id+'_'+'CMPCLOSE_CB_174');
						var	cb674 = Ext.getCmp(me.id+'_'+'CMPCLOSE_ComboValue_674');
						if(getRegionNick() == 'krym'){
							var medPersonalCode = (formData.EmergencyTeam_HeadShift_Code) ? formData.EmergencyTeam_HeadShift_Code : 1;
							var cbCheck, cbCombo;
							if(medPersonalCode == 1){
								cbCheck = cb174;
								cbCombo = cb674;
							}else{
								var cbCheck = Ext.getCmp(me.id+'_'+'CMPCLOSE_CB_713');
								var	cbCombo = Ext.getCmp(me.id+'_'+'CMPCLOSE_ComboValue_714');
							}
							if(cbCheck) cbCheck.setValue(true);
							if(cbCombo) cbCombo.setValue(formData.MedPersonal_id);
						}else{
							//if(cb174) cb174.setValue(true);
							if(cb674) cb674.setValue(formData.MedPersonal_id);
						}

						fieldCmp.setValue(formData.MedPersonal_id);
					}
					break;
				}

				case 'EmergencyTeam_HeadShift2_id': {
					if(formData.EmergencyTeam_HeadShift2_id > 0){
						var cbCheck = Ext.getCmp(me.id+'_'+'CMPCLOSE_CB_606');
						var	cbCombo = Ext.getCmp(me.id+'_'+'CMPCLOSE_ComboValue_675');
						if(cbCheck) cbCheck.setValue(true);
						if(cbCombo) cbCombo.setValue(formData.EmergencyTeam_HeadShift2_id);
					}
					break;
				}

				case 'MedStaffFact_id' : {
					if(fieldCmp.store){
						fieldCmp.getStore().load({
							scope: fieldCmp,
							callback: function() {

								var fieldCmp = this,
									rec = fieldCmp.getStore().getAt(fieldCmp.getStore().findBy(function (rec) {
										return rec.get('MedStaffFact_id') == formData.MedStaffFact_id;
									})),
									LpuSection_id = base_form.findField('LpuSection_id'),
									medPersField = base_form.findField('MedPersonal_id');

								if(!formData.LpuSection_id && LpuSection_id){
									fieldCmp.disable();
								}

								if (typeof rec != 'object') {
									rec = fieldCmp.getStore().getAt(fieldCmp.getStore().findBy(function (rec) {
										return rec.get('MedPersonal_id') == formData.MedPersonal_id;
									}));
								}

								if (rec) {
									//if(LpuSection_id) LpuSection_id.setValue(rec.get('LpuSection_id'));
									fieldCmp.setValue(rec.get('MedStaffFact_id'));
									fieldCmp.setDisabled(me.action == 'add')
									if (medPersField) medPersField.setValue(rec.get('MedPersonal_id'));
								}

								else {
									fieldCmp.clearValue()

									/*
									 if (formData.MedStaffFact_id) {
									 fieldCmp.setValue(formData.MedStaffFact_id);
									 }
									 else {
									 fieldCmp.setValue(formData.MedPersonal_id);
									 }
									 var rec = fieldCmp.getStore().findBy(
									 function (r) {
									 if (r.get('MedStaffFact_id') == formData.MedStaffFact_id && medPersField) {
									 medPersField.setValue(r.get('MedPersonal_id'));
									 }
									 }
									 );*/

								}
								if(!getGlobalOptions().region.nick.inlist(['kareliya', 'krym'])){
									fieldCmp.on('focus', function(cb){

										if (base_form.findField('TransTime').getStringValue() != '') {
											var time_start = Date.parseDate( base_form.findField('TransTime').getStringValue(), 'd.m.Y H:i' );
										} else {
											var time_start = new Date();
										}
										var onDate = Ext.util.Format.date(time_start, 'd.m.Y');

										var filterParams = {
											LpuBuildingType_id: 27,
											withoutLpuSection: true,
											onDate: onDate // не уволены
										};
										/*
										if(getGlobalOptions().region.nick.inlist(['krym'])){
											filterParams.LpuBuilding_id = base_form.findField('LpuBuilding_id').getValue();
										}
										*/

										cb.baseFilterFn = setMedStaffFactGlobalStoreFilter(filterParams, cb.store, true);
									})
								}
							}
						});

                        me.loadEmergencyTeamsWorkedInATime();

					};
					break;
				}

				case 'FeldsherAccept' : {
					var FeldsherAcceptCallField = base_form.findField('FeldsherAcceptCall');

					fieldCmp.getStore().load({
						params: {
							All_Rec: 2
						},
						scope: fieldCmp,
						callback: function() {
							this.setValue(formData.FeldsherAccept);
							if(FeldsherAcceptCallField)FeldsherAcceptCallField.setValue(formData.FeldsherAccept);
						}
					});
					break;
				}

				case 'FeldsherAcceptCall' : {
					fieldCmp.getStore().load({
						params: {
							All_Rec: 2
						},
						scope: fieldCmp,
						callback: function() {
							this.setValue(formData.FeldsherAccept);
						}
					});
					break;
				}

				case 'FeldsherTrans' : {
					fieldCmp.getStore().load({
						params: {
							All_Rec: 2
						},
						scope: fieldCmp,
						callback: function() {
							this.setValue(formData.FeldsherTrans);
						}
					});
					break;
				}

				case 'MedStaffFact_cid' : {
					fieldCmp.getStore().load({
						scope: fieldCmp,
						callback: function(){
							if(formData.MedStaffFact_cid){
								this.setValue(formData.MedStaffFact_cid);
							}
						}
					});
					break;
				}

				case 'CmpReason_id' : {
					if(formData.CmpReason_id){
						fieldCmp.setValue(formData.CmpReason_id);
					}
					break;
				}


				case 'CallPovod_id' : {
					var resultEmergencyTripField = base_form.findField('ResultEmergencyTrip'),
						resultIdField = base_form.findField('Result_id'),
						diagIdField = base_form.findField('Diag_id');

					//если повод ошибка то поля необязательные
					if(formData.CallPovod_id > 0){
						var recId = fieldCmp.getStore().findBy(function(rec) {return rec.get('CmpReason_id') == formData.CallPovod_id;}),
							rec = fieldCmp.getStore().getAt(recId);

						if(rec && rec.get('CmpReason_Code').inlist(['01!'])){
							if(resultEmergencyTripField)resultEmergencyTripField.allowBlank = true;
							if(resultIdField)resultIdField.allowBlank = true;
							if(diagIdField)diagIdField.allowBlank = true;
						}

					}
					fieldCmp.setDisabled( !me.action.inlist(['stream']) && (getRegionNick().inlist(['ufa'])));
					break;
				}

				case 'EmergencyTeamSpec_id' : {
					//if(me.action != 'stream'){
					//	fieldCmp.disable();
					//}

					break;
				}

				case 'EmergencyTeam_id' : {
					if(me.action == 'stream'){
						if(fieldCmp.store){
							me.loadEmergencyTeamsWorkedInATime();
						};
					}
					else{
						if(formData.EmergencyTeam_id){
							//если у нас назначена бригада - блокируем поле иначе нет, что мы живодеры чтоль
							if(fieldCmp.store){
								var selectedRec = fieldCmp.getStore().getAt(fieldCmp.getStore().findBy(function(rec) {return rec.get('EmergencyTeam_id') == formData.EmergencyTeam_id;}));

								if(selectedRec){
									fieldCmp.setValue(formData.EmergencyTeam_id);

								}
							}
							fieldCmp.disable();
						}
						else{
							me.loadEmergencyTeamsWorkedInATime();
						}
					}




					break;
				}

				case 'Birthday' : {
					//Казахстанский компонент
					me.showAgeLabel();
					break;
				}

				case 'Diag_id' : {

					if(formData.Diag_id > 0){
						fieldCmp.getStore().load({
							scope: fieldCmp,
							params: {where: "where Diag_id = " + formData.Diag_id},
							callback: function() {
								this.setValue(formData.Diag_id);
							}
						})
					};
					break;
				}
				case 'Diag_uid' : {

					if(formData.Diag_uid > 0){
						fieldCmp.enable();
						fieldCmp.getStore().load({
							scope: fieldCmp,
							params: {where: "where Diag_id = " + formData.Diag_uid},
							callback: function() {
								this.setValue(formData.Diag_uid);
							}
						})
					}else{
						fieldCmp.disable();
					};
					break;
				}
				case 'Diag_sid' : {
						/*
					if(formData.Diag_sid > 0){
						fieldCmp.getStore().load({
							scope: fieldCmp,
							params: {where: "where Diag_id = " + formData.Diag_sid},
							callback: function() {
								this.setValue(formData.Diag_sid);
							}
						})
					};
					break;
					*/
				}
				case 'LpuBuilding_id' : {
					// вкладка медикаментов
					var indTab = ( regionNick.inlist(['ufa']) ) ? 7 : 6,
						tabMed = me.tabPanel.getItem(indTab);

					if(formData.LpuBuilding_id > 0){
						fieldCmp.getStore().load({
							scope: fieldCmp,
							callback: function() {
								this.setValue(this.getValue());
							}
						});

						if(tabMed){
							tabMed.removeAll();
							tabMed.add(
								{
									items: me.getDrugFields(formData.LpuBuilding_id, function(){
										if (me.DrugGrid && formData.CmpCallCard_id) {
											//загрузка грида "Использование медикаментов"
											me.DrugGrid.setParam('CmpCallCard_id',formData.CmpCallCard_id, true);
											me.DrugGrid.loadData();
										}
									})
								}
							);
						}

						me.tabPanel.doLayout();
						fieldCmp.disable();
					}else{
						var txt = 'Для ввода медикаментов необходимо заполнить поле “Номер станции (подстанции), отделения"';

						if(getRegionNick().inlist(['perm', 'krym', 'buryatiya', 'astra', 'kareliya', 'khak'])){
							txt = 'Для ввода медикаментов необходимо заполнить поле “Станция (подстанция), отделение"';
						};
						if(tabMed){
							tabMed.removeAll();
							tabMed.add({
								html: txt,
								style: 'margin-top: 10px; text-align: center; font-size: 16px;',
								height: 700
							});
							tabMed.doLayout();
						}

					}

					break;
				}
				case 'CmpLethalType_id' : {
					//fieldCmp.setValue(formData.CmpLethalType_id);
					break;
				}
				case 'CmpCloseCard_LethalDT' : {
					fieldCmp.setValue(formData.CmpCloseCard_LethalDT);
					break;
				}

				// Мышечный тонус DS выключаем изначально
				case 'ComboCmp_646':
				case 'ComboCmp_647':
				case 'ComboCmp_648':
				{
					fieldCmp.disable();
					break;
				}

				// Зрачки DS выключаем изначально
				case 'ComboCmp_18':
				case 'ComboCmp_19':
				case 'ComboCmp_20':
				{
					fieldCmp.disable();
					break;
				}

				case 'AcceptTime':
				{
					//if(formData.AcceptTime ){
						fieldCmp.ownerCt.dateEditField.setDisabled(me.action != 'stream');
						fieldCmp.ownerCt.timeEditField.setDisabled(me.action != 'stream');
						fieldCmp.setDisabled(me.action != 'stream');

					//}

					break;
				}
				//Если поля(ред. времени) были заполнены автоматически...
				/*
                case 'AcceptTime':
				{
					if( (armType == 'smpheadbrig') && formData.AcceptTime ){
						fieldCmp.ownerCt.dateEditField.disable();
						fieldCmp.ownerCt.timeEditField.disable();

					}
					fieldCmp.disable();
					break;
				}
				*/
				/*
				case 'TransTime':
				{
					if( (me.action == 'add')  && formData.TransTime ){
						fieldCmp.ownerCt.dateEditField.disable();
						fieldCmp.ownerCt.timeEditField.disable();
						fieldCmp.disable();
					}
					break;
				}

				case 'GoTime':
				{
					if( (me.action == 'add')  && formData.GoTime){
						fieldCmp.ownerCt.dateEditField.disable();
						fieldCmp.ownerCt.timeEditField.disable();
						fieldCmp.disable();
					}
					break;
				}

				case 'ArriveTime':
				{
					if( (me.action == 'add')  && formData.ArriveTime ){
						fieldCmp.ownerCt.dateEditField.disable();
						fieldCmp.ownerCt.timeEditField.disable();
						fieldCmp.disable();
					}
					break;
				}

				case 'TransportTime':
				{
					if( (me.action == 'add')  && formData.TransportTime ){
						fieldCmp.ownerCt.dateEditField.disable();
						fieldCmp.ownerCt.timeEditField.disable();
						fieldCmp.disable();
					}
					break;
				}

				case 'ToHospitalTime':
				{
					if( (me.action == 'add')  && formData.ToHospitalTime){
						fieldCmp.ownerCt.dateEditField.disable();
						fieldCmp.ownerCt.timeEditField.disable();
						fieldCmp.disable();
					}
					break;
				}

				case 'EndTime':
				{
					if( (me.action == 'add')  && formData.EndTime){
						fieldCmp.ownerCt.dateEditField.disable();
						fieldCmp.ownerCt.timeEditField.disable();
						fieldCmp.disable();
					}
					break;
				}

				case 'BackTime':
				{
					if( (me.action == 'add')  && formData.BackTime){
						fieldCmp.ownerCt.dateEditField.disable();
						fieldCmp.ownerCt.timeEditField.disable();
						fieldCmp.disable();
					}
					break;
				}
				*/

				//конец Если поля(ред. времени) были заполнены автоматически...

				case 'MedPersonalAssistant_id':{
					if(formData.MedPersonalAssistant_id > 0){
						/*
						var cb606 = Ext.getCmp(me.id+'_'+'CMPCLOSE_CB_606'),
							cb675 = Ext.getCmp(me.id+'_'+'CMPCLOSE_ComboValue_675');

						if(cb606) cb606.setValue(true);
						if(cb675) cb675.setValue(formData.MedPersonalAssistant_id);
						*/
						var cbCheck = Ext.getCmp(me.id+'_'+'CMPCLOSE_CB_607');
						var	cbCombo = Ext.getCmp(me.id+'_'+'CMPCLOSE_ComboValue_676');
						if(cbCheck) cbCheck.setValue(true);
						if(cbCombo) cbCombo.setValue(formData.MedPersonalAssistant_id);
					}
					break;
				}

				case 'MedPersonalDriver_id':{
					if(formData.MedPersonalDriver_id > 0){
						var cb178 = Ext.getCmp(me.id+'_'+'CMPCLOSE_CB_178'),
							cb677 = Ext.getCmp(me.id+'_'+'CMPCLOSE_ComboValue_677');

						if(cb178) cb178.setValue(true);
						if(cb677) cb677.setValue(formData.MedPersonalDriver_id);
					}
					break;
				}
				case 'LeaveType_id':{
					if(formData.LeaveType_id > 0){
						fieldCmp.getStore().load({
							scope: fieldCmp,
							callback: function() {
								this.setValue(formData.LeaveType_id);
							}
						})
					};
					break;
				}
				case 'CmpCallCard_IsPaid':{
					var RepFlagField = base_form.findField('CmpCallCard_RepFlag');
					if(RepFlagField){
						RepFlagField.getEl().up('.x-form-item').setDisplayed(formData.CmpCallCard_IsPaid == 2 && !(getGlobalOptions().IsSMPServer));
					}
					break;
				}
				case 'ComboValue_693':{
					this.setPhoneMO(false);
					break;
				}
				case 'isSogl':{
					if (regionNick.inlist(['krym'])) {
						fieldCmp.setValue(2);
					}
					break;
				}
				case 'ComboCheck_TeamComplect_id':
					if(getRegionNick().inlist(['khak'])){
						if(fieldCmp.code.inlist([3473,3474,3475,3476,3477])){
							this.setTeamComplect(formData, fieldCmp );
						}else{
							fieldCmp.setVisible(false)
						}
					}
					break;
				case 'ComboCheck_ResultUfa_id': {
					if(fieldCmp.code == formData.ComboCheck_ResultUfa_id){
						fieldCmp.setValue(true)
					}
					break;
				}
				case 'ComboValue_243':
				case 'ComboValue_854': {
					if(formData[field] > 0) {
						fieldCmp.getStore().load({
							scope: fieldCmp,
							params: {where: "where Diag_id = " + formData[field]},
							callback: function() {
								this.setValue(formData[field]);
							}
						})
					}
				}

			}

			//@todo надо будет поразмыслить над этим на досуге, тк есть компоненты которые есть только на одном регионе, и они не могут попадать под общую проверку
			if(
				(
					//#131978
					(me.action == 'stream'/* && (!getRegionNick().inlist(['perm','krym']) && field.inlist(['Day_num','Year_num']))*/)
					|| (me.action == 'add' && armType == 'mstat' && (getRegionNick().inlist(['astra']) || !field.inlist(['Day_num','Year_num'])))
					|| (me.action == 'add' && ((armType.inlist(['smpheaddoctor','smpadmin']) && !field.inlist(['Day_num','Year_num'])) || !field.inlist(['Day_num','Year_num','AcceptTime','CallType_id','CallPovod_id','LpuBuilding_id','EmergencyTeam_id','EmergencyTeamSpec_id','MedStaffFact_id','Ktov','Phone'])))
					|| (!field.inlist(['Day_num','Year_num']) && (armType.inlist(['smpheaddoctor']) || (me.searchWindow && me.action == 'add')))
					|| (!getRegionNick().inlist(['astra']) && me.action == 'edit' && armType == 'mstat' && (( field == 'AcceptTime' && getGlobalOptions().IsSMPServer  ) || !field.inlist(['Day_num','Year_num','AcceptTime','CallPovod_id','LpuBuilding_id','EmergencyTeam_id','Ktov'])))
					|| (getRegionNick().inlist(['astra']) && me.action == 'edit' && armType == 'mstat' && (getGlobalOptions().IsSMPServer || field != 'AcceptTime' ))
				)
				&& !field.inlist(['Diag_uid', 'pmUser_insName'])
			)
			{
				fieldCmp.enable();
			}
			fieldCmp.validate();
		};

		if(getRegionNick() == 'ufa' && ['add','edit'].includes(me.action)) {
			var isEmptyPerson = Ext.isEmpty(formData.Person_id) || formData.Person_IsUnknown == 2;
			base_form.findField('Fam').setDisabled(!isEmptyPerson);
			base_form.findField('Name').setDisabled(!isEmptyPerson);
			base_form.findField('Middle').setDisabled(!isEmptyPerson);
			base_form.findField('Sex_id').setDisabled(!isEmptyPerson);
			base_form.findField('Age').setDisabled(!isEmptyPerson);
			Ext.getCmp(me.id+'_CMPCLOSE_CB_219').setDisabled(!isEmptyPerson);
			Ext.getCmp(me.id+'_CMPCLOSE_CB_220').setDisabled(!isEmptyPerson);
			Ext.getCmp(me.id+'_CMPCLOSE_CB_221').setDisabled(!isEmptyPerson);
			base_form.findField('DocumentNum').setDisabled(!isEmptyPerson);
			base_form.findField('Person_PolisSer').setDisabled(!isEmptyPerson);
			base_form.findField('Person_PolisNum').setDisabled(!isEmptyPerson);
			base_form.findField('CmpCloseCard_PolisEdNum').setDisabled(!isEmptyPerson);
			base_form.findField('Age').setDisabled(!isEmptyPerson);
			base_form.findField('Work').setDisabled(!isEmptyPerson);
		}

		AcceptTimeField.ownerCt.dateEditField.setDisabled(AcceptTimeField.disabled);
		AcceptTimeField.ownerCt.timeEditField.setDisabled(AcceptTimeField.disabled);
		
		if(resultEmergencyTripField)resultEmergencyTripField.validate();
		
		//me.checkEmergencyStandart();

		//me.initUslugaElements();

        //не используйте ид в коде - пример того как можно обойтись без них:
        var uslugaPanel = me.FormPanel.find('refId', 'uslugaGrid'),
			payTypeCombo = me.FormPanel.getForm().findField('PayType_id'),
			payTypeCode = payTypeCombo && payTypeCombo.getSelectedRecordData()? payTypeCombo.getSelectedRecordData().PayType_Code: null;

        if(uslugaPanel && uslugaPanel[0]){
            uslugaPanel[0].getStore().load({
                params: {
                    CmpCallCard_id: formData.CmpCallCard_id,
                    acceptTime: formData.AcceptTime ? formData.AcceptTime : Ext.util.Format.date( new Date(), 'd.m.Y H:i'),
					PayType_Code: payTypeCode? payTypeCode: null
                }
            })
        };
	},

	//установка значений в поля-combo при загрузке
	setComboValuesOnLoad: function(){
		var me = this,
			base_form = me.FormPanel.getForm();

		this.isLoadedForm = 1;
		Ext.Ajax.request({
			url: '/?c=CmpCallCard&m=loadCmpCloseCardComboboxesViewForm',
			params: {CmpCallCard_id: base_form.findField('CmpCallCard_id').getValue()},
			callback: function(opt, success, response) {
				if ( success ) {
					var comboValues = Ext.util.JSON.decode(response.responseText);

					for (var i = 0; i < comboValues.length; i++) {
						var queryObj = comboValues[i]['CmpCloseCardCombo_id'],
							cmp = Ext.getCmp(this.id+'_CMPCLOSE_CB_'+queryObj),
							cmpp = Ext.getCmp(this.id+'_CMPCLOSE_ComboValue_'+comboValues[i]['CmpCloseCardCombo_id']),
							dsCombo = Ext.getCmp(this.id+'_CMPCLOSE_CBC_'+queryObj);




						//загрузка CMPCLOSE_CB
						if(cmp){
							cmp.setValue(true);
							// в группе checkboxgroup, если отмечен checkbox, то при первом клике поля очищаются
							// в таком случае значение не проставится, ныкнем еще раз его
							cmp.setValue(true);
						}

						//загрузка ComboValue
						if(cmpp){
							
							//установка чекбокса, в перечне состава врачей если приходит значение на врача из CloseCardRel
							if(cmpp.parent_code && cmpp.xtype == 'swmedpersonalcombo'){
								var parentCmp = Ext.getCmp(this.id+'_CMPCLOSE_CB_'+cmpp.parent_code);
								if(parentCmp){
									parentCmp.setValue(true);
								}
							};
							
							if(cmpp.store && cmpp.xtype == 'sworgcomboex'){
								cmpp.store.load({
									params: '',
									scope: cmpp,
									callback: function() {
										this.setValue(this.getValue());
									}
								});
							}

							// чтобы показывал наименование а не айдишник
							if(cmpp.store && cmpp.xtype == 'swdiagcombo'){
								cmpp.store.load({
									params: '',
									scope: cmpp,
									callback: function() {
										this.setValue(this.getValue());
									}
								});
							}

							// m.sysolin: не забываем парсить дату, иначе она не сохраняется (при повторном сохранении)
							if (cmpp.xtype == 'swdatetimefield'){

								var datetimeFromString = Date.parseDate(comboValues[i]['Localize'], 'd.m.Y H:i')

								// так не работает...
								cmpp.setValue(datetimeFromString);
								// так работает...
								var datetimeHiddenInput = Ext.getCmp('ComboValue_'+comboValues[i]['CmpCloseCardCombo_id']);
								datetimeHiddenInput.setValue(datetimeFromString);

							} else
								cmpp.setValue(comboValues[i]['Localize']);
						};

						//загрузка комбобокса(ds)
						if(dsCombo){dsCombo.setValue(comboValues[i]['Localize']);};
						
						if(cmpp && cmpp.xtype == 'swlpuopenedcombo' && queryObj == 693 && comboValues[i]['Localize']){
							// установка номера телефона МО обслуживания активного вызова (вкладка Результат, подлежит активному посещению)
							/*
							this.setPhoneMO(false);
							this.getPhoneMO(comboValues[i]['Localize']);
							*/
						}
					}

				} else {
					sw.swMsg.alert('Ошибка', 'Ошибка при загрузке динамических комбобоксов.');
					base_form.ownerCt.hide();
				}

			}.createDelegate(this)
		});

	},

	//здесть происходит сбор параметров
	doSave: function(withPrint) {
		var me = this,
			base_form = me.FormPanel.getForm(),
			allFields = base_form.items.items,
			diag_sid_panel = Ext.getCmp('diag_sid_panel');

		me.showLoadMask('Подождите, идет закрытие карты вызова...');

		if(diag_sid_panel)
			var arrDiag_sid = diag_sid_panel.getData(),
			arrDiagCodes_sid = diag_sid_panel.getIDsAndCodes();
		var	diag_ooid_panel = Ext.getCmp('diag_ooid_panel');
		if(diag_ooid_panel)
			var arrDiag_ooid = diag_ooid_panel.getData();
			var params = {},
			validateCb = function(success){
				if(!success){
					me.hideLoadMask();
					return false;
				}
				else{
					//проверка на суицид и добавление в регистр
					if((getRegionNick() == 'perm') && (me.action != 'edit')){
						if(arrDiagCodes_sid.length > 0){
							for(var i = 0;i<arrDiagCodes_sid.length;i++){
								var diag_code = arrDiagCodes_sid[i].code;
								if((diag_code >= 'X60') && (diag_code <= 'X84')){
									Ext.Ajax.request({
										url: '/?c=PersonRegister&m=save',
										params: {
											PersonRegister_setDate:base_form.findField('AcceptTime').getStringValue().substr(0,10),
											Diag_id:arrDiagCodes_sid[i].id,
											Person_id:base_form.findField('Person_id').getValue(),
											PersonRegisterType_SysNick:'suicide',
											PersonRegisterType_id:62, //суицид
											MorbusType_SysNick:'suicide',
											Lpu_iid:getGlobalOptions().lpu_id,
											MedPersonal_iid:getGlobalOptions().medpersonal_id
										},
										success: function(){
											sw.swMsg.alert('Информация', 'Пациент был включён в регистр лиц, совершивших суицидальные попытки');
										}
									})
									i = arrDiagCodes_sid.length+1;
								}
							}
						}
					}

					me.formStatus = 'save';

					params.CmpEquipment = [];

					//рекурсивная обработка
					var getFormValues = function(cmps){

						//массив полей адреса актива в поликлиннику, которые скрыты всегда и должны быть сохранены
						var arHiddenAddressCombo = [
							'ComboValue_701', //Address_ZipEdit
							'ComboValue_702', //KLCountry_idEdit
							'ComboValue_703', //KLRgn_idEdit
							'ComboValue_704', //KLSubRGN_idEdit
							'ComboValue_705', //KLCity_idEdit
							'ComboValue_706', //KLTown_idEdit
							'ComboValue_707', //KLStreet_idEdit
							'ComboValue_708', //Address_HouseEdit
							'ComboValue_709', //Address_CorpusEdit
							'ComboValue_710', //Address_FlatEdit
							'ComboValue_711', //Address_AddressEdit&
							'ComboValue_2188' //Address_AddressEdit&
						];
						for(var i = 0; i < cmps.length; i++){
							var fieldCmp = cmps[i],
								fieldName = fieldCmp.getName(),
								fieldVal = fieldCmp.getValue(),
                                xtype = fieldCmp.ownerCt ? fieldCmp.ownerCt.xtype : null;

							switch(true){
								case ( xtype == "swdatetimefield" ):{
									fieldVal = fieldCmp.getStringValue();
									params[fieldName] = fieldVal;
									break;
								}
								case (fieldVal instanceof Date): {
									//просто дата пришла
									params[fieldName] = Ext.util.Format.date( fieldVal, 'd.m.Y H:i');
									break;
								}
                                case (fieldName == 'Age') : {
                                    params.Age = fieldVal;
                                    params.Person_BirthDay = me.getBirthday();
                                    break;
                                }
								case ( fieldCmp.inputValue &&
									( (fieldCmp.inputValue > 0) || (fieldCmp.inputValue.inlist(['D', 'S'])) )
								): {
									//сбор полей радио и комбо
									if(fieldVal){
										if(params[fieldName]){
											//если значений несколько - формируем массив
											if(params[fieldName] instanceof Array){
												params[fieldName].push(fieldCmp.inputValue);
											}
											else{
												//здесь - создаем массив из того что было и добавляем свежий элемент
												params[fieldName] = [params[fieldName]];
												params[fieldName].push(fieldCmp.inputValue);
											}
										}
										else{
											//если один - не формируем массив
											params[fieldName] = fieldCmp.inputValue;
										};

									};
									break;
								}
								case (fieldName.search('CmpEquipment_') != -1): {
									if(fieldVal){
										//поля для сохранения Использованного оборудования (здесь всегда пара - массив)
										if(params.CmpEquipment[fieldName] instanceof Array){
											params.CmpEquipment[fieldName].push(fieldVal);
										}
										else{
											params.CmpEquipment[fieldName] = [fieldVal];
										}
									}
									break;
								}
								case (fieldName.search('ComboValue') != -1):{
									//если есть значение и компонент отображается
									if(fieldVal && (fieldName.inlist(arHiddenAddressCombo) || fieldCmp.isVisible()))
										params[fieldName] = fieldVal.toString();
									break;
								}
								case (fieldName == 'CmpCloseCard_UlicSecond') : {
									var secondStreetRec = fieldCmp.getStore().getAt( fieldCmp.getStore().findBy(function(record) {
										return record.get('StreetAndUnformalizedAddressDirectory_id') == fieldVal;})
									);

									if(secondStreetRec){
										params.CmpCloseCard_UlicSecond = secondStreetRec.get('KLStreet_id');
									}
									break;
								}
								case ( fieldCmp.getXType && fieldCmp.getXType() == "checkbox" ):{
									params[fieldName] = fieldVal ? 2 : 1;
									break;
								}
								default: {
									//поля время-дата или просто дата (ибо не гоже дату отправлять)
									params[fieldName] = fieldVal;
									break;
								}
							}
							//продолжение банкета
							if(cmps[i].items){getFormValues(cmps[i].items.items)}
						}
					}

					getFormValues(allFields);

					if(arrDiag_sid)
						params.arrDiag_sid = arrDiag_sid;
					if(arrDiag_ooid)
						params.arrDiag_ooid = arrDiag_ooid;
					params.saveActive = false;
					if ( params.Person_id && params.ComboValue_693 )
					{
						//if ( confirm('Сохранить информацию об активе в карте СМП ?') ){
							if(params.ComboValue_703 && (params.ComboValue_705 || params.ComboValue_706) && params.ComboValue_707 && params.ComboValue_708){
								if(Ext.isEmpty(params.ComboValue_694)){
									var acceptDate = Date.parseDate( base_form.findField('AcceptTime').getStringValue(), 'd.m.Y H:i' )
									acceptDate.setDate(acceptDate.getDate() + 1);
									params.ComboValue_694 = Ext.util.Format.date( acceptDate, 'd.m.Y H:i');
								}
								params.saveActive = true;
								//params.HomeVisitSource_id = 1;
								params.HomeVisitSource_id = 10;
							}else{
								me.hideLoadMask();
								Ext.MessageBox.alert('Ошибка передачи актива в поликлинику', 'необходимо выбрать полный адрес посещения<br> включая страну, город, улицу, дом');
								return false;
							}
						//}
					}

					//console.warn('params', params);

					//сбор параметров для сохранения услуги
					var usluga_data_array = [];

                    var uslugaPanel = me.FormPanel.find('refId', 'uslugaGrid'),
						payTypeCombo = me.FormPanel.getForm().findField('PayType_id'),
						payTypeCode = payTypeCombo && payTypeCombo.getSelectedRecordData()? payTypeCombo.getSelectedRecordData().PayType_Code: null,
						checkUslugaKolvo = false;

                    if(uslugaPanel && uslugaPanel[0]){
                        uslugaPanel[0].getStore().each(function(rec){
                            if(rec.get('CmpCallCardUsluga_Kolvo')){
                                usluga_data_array.push({
                                    'UslugaComplex_id': rec.get('UslugaComplex_id'),
                                    'UslugaCategory_id': rec.get('UslugaCategory_id'),
                                    'CmpCallCardUsluga_Kolvo': rec.get('CmpCallCardUsluga_Kolvo'),
                                    'CmpCallCardUsluga_setDate': params.AcceptTime.substr(0,10),
                                    'CmpCallCardUsluga_setTime': params.AcceptTime.substr(11,5),
									'MedStaffFact_id': params.MedStaffFact_id,
									'PayType_Code': (getRegionNick().inlist(['perm']) && base_form.findField('PayType_id').getValue()) ? base_form.findField('PayType_id').getValue() : 1
                                });
                            }

							//проверяем есть ли услуги с типом оплаты МБТ
                            if (payTypeCode.inlist([10, 11])) {
								if (rec.get('CmpCallCardUsluga_Kolvo') && (rec.get('PayType_Code') == payTypeCode)) {
									checkUslugaKolvo = true;
								}
							} else {
                            	checkUslugaKolvo = true;
							}
                        });
                    };

                    if (getRegionNick().inlist(['perm']) && !checkUslugaKolvo) {
						me.hideLoadMask();
						sw.swMsg.alert('Ошибка', 'Не указано ни одной услуги относящейся к виду оплаты "' + me.FormPanel.getForm().findField('PayType_id').getSelectedRecordData().PayType_Name + '": измените вид оплаты или добавьте в карту соответствующую услугу');
						return false;
					}

					//здесь тестируется новый функционал по сохранению услуг
					/*
                    if(me.UslugaPanel){
						for (var i = 0; i < me.uslugaWrapper.items.length; i++) {
							var cmp = me.uslugaWrapper.items.items[i].items.items[0];

							if(cmp.getValue()){

								usluga_data_array.push({
									'UslugaComplex_id': cmp.UslugaComplex_id,
									'UslugaCategory_id': cmp.UslugaCategory_id,
									'UslugaComplex_Code': cmp.UslugaComplex_Code,
									'CmpCallCardUsluga_Kolvo': cmp.getValue(),
									'CmpCallCardUsluga_setDate': params.AcceptTime.substr(0,10),
									'CmpCallCardUsluga_setTime': params.AcceptTime.substr(11,5),
									'MedStaffFact_id': params.MedStaffFact_id,
									'PayType_Code': (getRegionNick().inlist(['perm']) && base_form.findField('PayType_id').getValue()) ? base_form.findField('PayType_id').getValue() : 1,
									//'CmpCallCardUsluga_id': cmp.CmpCallCardUsluga_id
								})
							}
						}
					}
					*/

					var drugGridJsonData = '';

					if (me.DrugGrid) {
						drugGridJsonData = me.DrugGrid.getJSONChangedData();
					}

					var ExpertResponseParams = [];
					if(me.ExpertResponseFields) {
						var gpoups = me.ExpertResponseFields.findByType('checkboxgroup');
						for (var i = 0; i < gpoups.length; i++) {

							for (var j = 0; j < gpoups[i].items.items.length; j++) {
								var responsefield = gpoups[i].items.items[j];
								if(responsefield.xtype != 'checkbox') continue;

								var action = responsefield.CMPCloseCardExpertResponse_id ? "edit" : 'add';
								if(responsefield.isDirty && responsefield.CMPCloseCardExpertResponse_id && !responsefield.getValue()){
									action = 'del';
								}

								ExpertResponseParams.push(
									{
										name: responsefield.getName(),
										value: responsefield.getValue(),
										CMPCloseCardExpertResponse_id: responsefield.CMPCloseCardExpertResponse_id,
										CMPCloseCardExpertResponseType_id: gpoups[i].ExpertResponseType_id,
										AttributeValue_id:	responsefield.AttributeValue_id,
										CMPCloseCardExpertResponse_Comment: Ext.getCmp(responsefield.id + '_Comm').getValue(),
										action: action
									}
								)
							}
						}
					}

					//не собирается автоматом
					params.PayType_id = payTypeCombo ? payTypeCombo.getValue() : null;

					var all_min = me.calcSummTime();
					var megaParams = {
						CardParamsJSON: Ext.util.JSON.encode(params),
						usluga_array: JSON.stringify(usluga_data_array),
						CmpCallCardDrugJSON: drugGridJsonData,
						ExpertResponseJSON: Ext.util.JSON.encode(ExpertResponseParams)
					};

					megaParams.withPrint = withPrint;

					if (me.AutoBrigadeStatusChange) {
						megaParams.AutoBrigadeStatusChange = me.AutoBrigadeStatusChange;
						megaParams.ARMType_id = (sw.Promed.MedStaffFactByUser && sw.Promed.MedStaffFactByUser.current && sw.Promed.MedStaffFactByUser.current.ARMType_id)?sw.Promed.MedStaffFactByUser.current.ARMType_id:null;
						//megaParams.ARMType_id = sw.Promed.MedStaffFactByUser.current.ARMType_id;
					}

					if((me.action == 'stream') && (getRegionNick() == 'perm')){
						var check_dupl_params = new Object();
						check_dupl_params.CmpCallCard_Numv = params.Day_num;
						check_dupl_params.CmpCallCard_Ngod = params.Year_num;
						check_dupl_params.CmpCallCard_prmDate = params.AcceptTime.substr(0,10);

						Ext.Ajax.request({
							params: check_dupl_params,
							callback: function (opt, success, response) {
								if (success) {
									var response_obj = Ext.util.JSON.decode(response.responseText);

									if ( response_obj.data && response_obj.data.length > 0) {
										sw.swMsg.alert(langs('Ошибка'), langs('В Систему уже заведена Карта вызова с аналогичными параметрами. Сохранение дублирующей Карты вызова невозможно'));
										me.formStatus = 'edit';
										me.hideLoadMask();
									}
									else {
										if(all_min !== null && all_min > 60 && getGlobalOptions().region.nick == 'ufa')
											showSysMsg('', 'Внимание! Время, затраченное на выполнение вызова, превышает 60 мин.', null, {closable: true, delay: 15000, bodyStyle: 'text-align:left; margin-left:7px; padding: 0px 0px 20px 20px;background:transparent'});
										me.save_form(megaParams) ;
									}
								}
								else {
									me.formStatus = 'edit';
									sw.swMsg.alert(langs('Ошибка'), langs('Ошибка при проверке дублирования вызова'));
									me.hideLoadMask();
								}
							}.createDelegate(this),
							url: '/?c=CmpCallCard&m=checkDuplicateCmpCallCard'
						});
					}else{
						if(all_min !== null && all_min > 60 && getGlobalOptions().region.nick == 'ufa')
							showSysMsg('', 'Внимание! Время, затраченное на выполнение вызова, превышает 60 мин.', null, {closable: true, delay: 15000, bodyStyle: 'text-align:left; margin-left:7px; padding: 0px 0px 20px 20px;background:transparent'});
						me.save_form(megaParams) ;
					}


				}
			};

		// проверка ОКС для Уфы
		if (getRegionNick() == 'ufa' && !Ext.getCmp('tabOKC').disabled) {
			var relativeTLT_checked = Ext.getCmp('relativeTLT_grid').getGrid().getSelectionModel().getSelections().length>0?true:false;
			var absoluteTLT_checked = Ext.getCmp('absoluteTLT_grid').getGrid().getSelectionModel().getSelections().length>0?true:false;

			if(Ext.getCmp('paramedic').getValue() == '' && Ext.getCmp('Lpu').getValue() == '' && Ext.getCmp('pain_setTime').getValue() == '' &&
			   Ext.getCmp('pain_setDate').getValue() == '' && Ext.getCmp('TLTreason').getValue() == '' && Ext.getCmp('EvnEKG_rezEKG').getValue() == '' &&
			   Ext.getCmp('TLT_setTime').getValue() == '' && Ext.getCmp('TLT_setDate').getValue() == '' &&
			   Ext.getCmp('EvnEKG_setTime').getValue() == '' && Ext.getCmp('EvnEKG_setDate').getValue() == '' &&
			   relativeTLT_checked === false && absoluteTLT_checked === false
			  )
			{
				sw.swMsg.alert(langs('Ошибка'), 'Необходимо заполнить раздел ОКС');
				me.formStatus = 'edit';
				me.hideLoadMask();
				return false;
			}
		}

		//Контроль наличия услуги осмотра
		if(getRegionNick() == 'perm'){
			if(!me.existOsmUslugaComplex()){

				var code = me.getOsmUslugaComplexCode(),
					uslugaPanel = me.FormPanel.find('refId', 'uslugaGrid');

				if(uslugaPanel && uslugaPanel[0]){
					var uslugaIndex = uslugaPanel[0].getStore().find('UslugaComplex_Code', code)
				}
				if(code && uslugaIndex && uslugaIndex != -1){

					var uslugaRec = uslugaPanel[0].getStore().getAt(uslugaIndex);

					sw.swMsg.show({
						icon: Ext.MessageBox.QUESTION,
						msg: 'В Карте вызова не выбрана ни одна услуга осмотра. Добавить услугу ' + uslugaRec.get('UslugaComplex_Name') + '?',
						title: langs('Подтверждение'),
						buttons: Ext.Msg.YESNOCANCEL,
						fn: function(buttonId, text, obj) {
							if ('yes' == buttonId) {
								me.addOsmUslugaComplex(code)
							}
							if ('cancel' == buttonId) {
								me.formStatus = 'edit';
								me.hideLoadMask();
								return false;
							}
							me.doValidate( validateCb );
						}
					});

				}else{
					if ( confirm('В Карте вызова не выбрана ни одна услуга осмотра. Сохранить Карту вызова без услуги?') ){
						me.doValidate( validateCb );
					}else{
						me.formStatus = 'edit';
						me.hideLoadMask();
					}
				}

			}else{
				me.doValidate( validateCb );
			}
		}else if(getRegionNick() == 'astra'){
			var notValidate = false,
				cmp = Ext.getCmp('ResultUfa_id').items;
			for(var i = 0; i<cmp.length; i++) {
				/**
				 * поля
				 * 231 - Больной не найден на месте
				 * 232 - Отказ от помощи (от осмотра)
				 * 233 - Адрес не найден
				 * 234 - Ложный вызов
				 * 235 - Смерть до приезда бригады СМП
				 * 236 - Больной увезен до прибытия СМП
				 * 237 - Больной обслужен врачом поликлиники до приезда СМП
				 * 238 - Вызов отменен
				 * 239 - Пациент практически здоров
				 */
				if(cmp.items[i].code &&  cmp.items[i].code.inlist([231, 232, 233, 234, 236, 237, 238, 239])) {
					if(cmp.items[i].getValue() == 1) {
						notValidate = true;
						break;
					}
				}
			}
			if(!me.existOsmUslugaComplex() && !notValidate){
				alert('Не выбрана услуга осмотра')
				me.formStatus = 'edit';
				me.hideLoadMask();
				return false;
			}else{
				me.doValidate( validateCb );
			}
		}else{
			me.doValidate( validateCb );
		}
	},

	save_form: function( paramsCCC ){
		var me = this,
			base_form = this.FormPanel.getForm();

		//тестовый функционал сохранения полей
		//это, конечно хорошо, что submit и все такое, но мне нужен гибкий метод, с возможностью правки параметров
		//и потом в планах использовать форму для поточного ввода
		//me.showLoadMask('Подождите, идет закрытие карты вызова...');

		if (sw.FormHashes && sw.FormHashes[me.objectClass]) {
			paramsCCC.formHash = sw.FormHashes[me.objectClass];
			paramsCCC.formClass = me.objectClass;
		}

        //console.log('paramsCCC', paramsCCC);

		if(me.action == 'stream'){
			var urlPath =  '/?c=CmpCallCard&m=saveCmpStreamCard';
		}
		else{
			var urlPath =  '/?c=CmpCallCard&m=saveCmpCloseCard110';
		}

		Ext.Ajax.request({
			params: paramsCCC,
			url: urlPath,

			success: function (response, options) {
				if (!Ext.isEmpty(response.responseText)) {
					var response_obj = Ext.util.JSON.decode(response.responseText);
					if(response_obj.success === true)
					{
						if(response_obj.CmpCallCard_id !== undefined) {
							base_form.findField('Person_id').setValue(response_obj.Person_id);
							base_form.findField('CmpCallCard_id').setValue(response_obj.CmpCallCard_id);
						}

						if (Ext.getCmp('tabOKC') && typeof me.doSaveOKS == 'function'){
							me.doSaveOKS();
						}
		            }
                } else {
                    sw.swMsg.alert('Ошибка', 'При сохранении произошли ошибка');
                }
            },

			callback: function (obj, success, response) {
				me.hideLoadMask();
				var response_obj = Ext.util.JSON.decode(response.responseText);

				me.formStatus = 'edit';

				if (success) {
					if ( response_obj.Error_Code && response_obj.Error_Code == '901' ) {
						sw.swMsg.show({
							icon: Ext.MessageBox.QUESTION,
							msg: 'Форма ввода изменилась, необходимо обновить форму, чтобы продолжить сохранение. Обновить?',
							title: langs('Подтверждение'),
							buttons: Ext.Msg.YESNO,
							fn: function(buttonId, text, obj) {
								if ('yes' == buttonId) {
									var formData = base_form.getAllValues();
									// собрать гриды
									//formData.CmpCallCardUslugaData = getStoreRecords(me.UslugaViewFrame.getGrid().getStore());

									formData.CmpCallCardDrugData = getStoreRecords(me.DrugGrid.getGrid().getStore());

									formData.action = me.action;
									me.refreshCodeWithDependecies(formData);
								}
							}
						});
						return;
					}
					if ( response_obj.Error_Msg ) {
						sw.swMsg.alert('Ошибка', response_obj.Error_Msg);
						return;
					}

					if ( !response_obj.CmpCloseCard_id ) {
						sw.swMsg.alert('Ошибка', 'При сохранении произошли ошибки [Тип ошибки: 3]');
						return;
					}

					if (me.action == 'stream') {
						me.show({
							action: 'stream',
							formParams: {
								ARMType: 'smpadmin'
							}
						});


					}
					else{
						var params = Ext.util.JSON.decode(paramsCCC.CardParamsJSON),
						AcceptTime = Date.parseDate( params.AcceptTime, 'd.m.Y H:i' );

						var data = {
							cmpCloseCardData: {
								accessType: 'edit',
								CmpCallCard_id: params.CmpCallCard_id,
								CmpCloseCard_id: params.CmpCloseCard_id,
								Person_Surname: params.Fam,
								Person_Firname: params.Name,
								Person_Secname: params.Middle,
								CmpCallCard_prmDate: AcceptTime,
								CmpCallCard_prmTime: AcceptTime.format('H:i'),
								action: response_obj
							}
						};
						me.callback(data);
                        me.hide();
					}
                    if(paramsCCC.withPrint == true){
						var doublePagesPrintField = base_form.findField('LpuBuilding_IsPrint');
						var cmpcallcard_id = (me.action == 'stream') ? response_obj.CmpCallCard_id : params.CmpCallCard_id;

						if(doublePagesPrintField && doublePagesPrintField.getValue() == 1 && getRegionNick().inlist(['krym'])){
							var id_salt = Math.random();
							var id_salt2 = Math.random();
							var win_id = 'print_110u' + Math.floor(id_salt * 10000);
							var win_id2 = 'print_110u' + Math.floor(id_salt2 * 10000);
							var win = window.open('/?c=CmpCallCard&m=printCmpCloseCard110&page=1&CmpCallCard_id=' + cmpcallcard_id, win_id);
							var win2 = window.open('/?c=CmpCallCard&m=printCmpCloseCard110&page=2&CmpCallCard_id=' + cmpcallcard_id, win_id2);
						}else{
							var id_salt = Math.random();
							var win_id = 'print_110u' + Math.floor(id_salt * 10000);
							var win = window.open('/?c=CmpCallCard&m=printCmpCloseCard110&CmpCallCard_id=' + cmpcallcard_id, win_id);

						}

					}else{
                        var mb = Ext.Msg.show({
                            title: langs('Сообщение'),
                            msg: langs('Карта вызова сохранена'),
                            icon: Ext.Msg.INFO,
                            buttons: false,
                            modal: false,
                            animEl: this
                        });

						setTimeout(function () {
							mb.hide();
							if (me.action != 'stream') {
								me.closeWindow();
							}
						}, 1000);

						if(this.parent.Ext && this.parent.Ext.ComponentQuery){
							var grid = this.parent.Ext.ComponentQuery.query('grid[refId=CmpServedCallsList]')[0];
							if(grid) grid.store.reload();
						}



                    }
					if(response_obj.Active_Error_Msg){
						showSysMsg('', response_obj.Active_Error_Msg, null, {closable: true, delay: 15000, bodyStyle: 'text-align:left; margin-left:7px; padding: 0px 0px 20px 20px;background:transparent'});

					}

				}
				else{
					sw.swMsg.alert('Ошибка', 'При сохранении произошли ошибки [Тип ошибки: 2]');

					me.hideLoadMask();
					return;
				}
			}
		});

	},

	batchHideField: function(fields,disable,allowBlank){
		for(var i=0,cnt=fields.length;i<cnt;i++){
			this.hideField(fields[i],disable,allowBlank);
		}
	},

	hideField: function(field,disable,allowBlank){
		if ( typeof field === 'string' ) {
			field = this.FormPanel.getForm().findField( field );
		}

		if ( !field ) {
			log('Невозможно скрыть поле:',field);
			return;
		}
		if ( field.getEl().up('.x-form-item') ) {
			field.getEl().up('.x-form-item').setDisplayed(false); // hide label
		}

		if (typeof disable === 'undefined') {
			disable = true;
		}
		if ( disable ) {
			field.disable();// for validation
		}

		if (typeof allowBlank === 'undefined') {
			allowBlank = true;
		}
		if ( allowBlank ) {
			field.allowBlank = true;
		}
		field.hide();
	},

	// copied from swCmpCallCardCloseStreamWindow
	showField: function(field){
		if ( typeof field === 'string' ) {
			field = this.FormPanel.getForm().findField( field );
		}
		// typical elements
		if ( field.getEl().up('.x-form-item') ) {
			field.enable();
			field.show();
			field.getEl().up('.x-form-item').setDisplayed(true);// show label
		}
		// date with time elements
		else {
			field.show();
			field.enable();
		}
	},

    //Получение даты рождения по введенному возрасту
    getBirthday: function() {
        var base_form = this.FormPanel.getForm();
        var inp = base_form.findField('Age').getValue();
        var type = base_form.getValues()['ComboCheck_AgeType_id'];

        var date = new Date(base_form.findField('AcceptTime').value);

        var birthday = null;

        if (Ext.isEmpty(type) || Ext.isEmpty(date)) {
            return null;
        }

        switch (type) {
            case '221': //Дни
                birthday = date.add(Date.DAY, -inp);
                break;
            case '220': //Месяцы
                birthday = date.add(Date.MONTH, -inp);
                birthday.setDate(1);
                break;
            case '219': //Годы
            default:
                birthday = date.add(Date.YEAR, -inp);
                birthday.setMonth(0);
                birthday.setDate(1);
                break;
        }

        return birthday.format('Y-m-d');
        //return birthday.format('d.m.Y');
    },

	//Фильтруем диагнозы (только для Карелии)
	setMKB: function(){

		//Карелия
		if (getGlobalOptions().region.number !== 10) {
			return;
		}
		var base_form = this.FormPanel.getForm(),
			ageFieldValue = base_form.findField('Age').getValue(),
			AgeType_id = base_form.getValues()['AgeType_id'],
			sex_id = base_form.findField('Sex_id').getValue(),
			age;
		switch (AgeType_id) {
			case '221': //Дни
				age = Math.round(ageFieldValue/365);
				break;
			case '220': //Месяцы
				age = Math.round(ageFieldValue/12);
				break;
			case '219': //Годы
			default:
				age = ageFieldValue;
				break;
		}
		//base_form.findField('Diag_id').setMKBFilter(age,sex_id,true);
	},

	loadCmpCloseCardEquipmentViewForm: function(data){
		var me = this,
			base_form = me.FormPanel.getForm();

		Ext.Ajax.request({
			url: '/?c=CmpCallCard&m=loadCmpCloseCardEquipmentViewForm',
			params: {
				CmpCloseCard_id: data.CmpCloseCard_id,
				CmpCallCard_id: data.CmpCallCard_id
			},
			callback: function(opt, success, response) {
				if ( !success ) {
					me.hideLoadMask();
					sw.swMsg.alert('Ошибка', 'Ошибка при загрузке значений использованного оборудования.');
					base_form.ownerCt.hide();
					return;
				};

				var data = Ext.util.JSON.decode(response.responseText);
				for( var key in data ){
					var UsedOnSpotCnt = Ext.getCmp( me.id + 'CmpEquipment_' + data[key].CmpEquipment_id + '_UsedOnSpotCnt' );
					if ( typeof UsedOnSpotCnt !== 'undefined' ) {
						UsedOnSpotCnt.setValue( data[key].CmpCloseCardEquipmentRel_UsedOnSpotCnt );
					}

					var UsedInCarCnt = Ext.getCmp( me.id + 'CmpEquipment_' + data[key].CmpEquipment_id + '_UsedInCarCnt' );
					if ( typeof UsedInCarCnt !== 'undefined' ) {
						UsedInCarCnt.setValue( data[key].CmpCloseCardEquipmentRel_UsedInCarCnt );
					}
				}
			}
		});
	},

	getEquipment: function(){
		var items = [],
			columns = 3;

		$.ajax({
			url: "/?c=CmpCallCard&m=loadCmpEquipmentCombo",
			async: false,
			cache: false
		}).done(function(data){
			data = JSON.parse(data);

			var col_length = Math.ceil( data.length / columns );
			var column = [];

			for( var i=0; i<data.length; i++ ){
				var item = data[i];

				column.push({
					layout: 'column',
					items: [
						{
							layout: 'form',
							labelWidth: 200,
							border: false,
							bodyStyle: 'background: transparent',
							items: [
								new Ext.form.NumberField({
									fieldLabel: item.CmpEquipment_Name,
									name: 'CmpEquipment_' + item.CmpEquipment_id,
									//name: 'CmpEquipment[' + item.CmpEquipment_id + '][UsedOnSpotCnt]',
									value: '',
									allowDecimals: false,
									allowNegative: false,
									width: 50,
									validator: function(a){return (a.match(/^[1-9]\d*$/))?true:false;}
								})
							]
						},{
							layout: 'form',
							labelWidth: 15,
							border: false,
							bodyStyle: 'background: transparent',
							items: [
								new Ext.form.NumberField({
									fieldLabel: '/',
									name: 'CmpEquipment_' + item.CmpEquipment_id,
									//name: 'CmpEquipment[' + item.CmpEquipment_id + '][UsedInCarCnt]',
									value: '',
									allowDecimals: false,
									allowNegative: false,
									width: 50,
									validator: function(a){return (a.match(/^[1-9]\d*$/))?true:false;}
								})
							]
						}
					]
				});

				if ( i>0 && ( (i%col_length) === 0 || data.length == (i+1) ) ) {
					items.push({
						layout: 'column',
						items: column
					});
				}
			}
		});

		return items;
	},


	getCallUrgencyAndProfile: function(){
		/*var base_form = this.FormPanel.getForm(),
			CmpCallPlaceTypeCombo = base_form.findField('CmpCallPlaceType_id'),
			CmpReasonCombo = base_form.findField('CallPovod_id'),
			AgeField = base_form.findField('Age');

		console.log(CmpCallPlaceTypeCombo.getValue(), CmpReasonCombo.getValue(), AgeField.getValue());
		return;
		Ext.Ajax.request({
			failure: function (response, options) {},
			params: {
				CmpCallPlaceType_id: arguments[0].formParams.CmpCloseCard_id,
				CmpReason_id: (!Ext.isEmpty(sw.Promed.MedStaffFactByUser) && !Ext.isEmpty(sw.Promed.MedStaffFactByUser.current) && !Ext.isEmpty(sw.Promed.MedStaffFactByUser.current.MedStaffFactDoc_id)) ? sw.Promed.MedStaffFactByUser.current.MedStaffFactDoc_id : null,
				Person_Age: (!Ext.isEmpty(sw.Promed.MedStaffFactByUser) && !Ext.isEmpty(sw.Promed.MedStaffFactByUser.current) && !Ext.isEmpty(sw.Promed.MedStaffFactByUser.current.ARMType)) ? sw.Promed.MedStaffFactByUser.current.ARMType : null
			},
			success: function (response, options) {
				if (!Ext.isEmpty(response.responseText)) {
					var response_obj = Ext.util.JSON.decode(response.responseText);

					if (response_obj.success == false) {

					}
				}
			}.createDelegate(this),
			url: '/?c=CmpCallCard4E&m=getCallUrgencyAndProfile'
		});*/
	},
	
	determinationServiceAddressMO: function(data, callback){
		// Получение МО обслуживания адреса
		var me = this,
			base_form = me.FormPanel.getForm(),
			formValues = base_form.getAllValues(),
			age = formValues.Age,
			params = {};

		if(data.action == 'menu'){
			params = {
				KLHome: (data.Address_HouseEdit) ? data.Address_HouseEdit : '',
				KLCity_id: (data.KLCity_idEdit) ? data.KLCity_idEdit : '',
				KLCountry_id: (data.KLCountry_idEdit) ? data.KLCountry_idEdit : '',
				KLRgn_id: (data.KLRgn_idEdit) ? data.KLRgn_idEdit : '',
				KLStreet_id: (data.KLStreet_idEdit) ? data.KLStreet_idEdit : '',
				KLSubRGN_id: (data.KLSubRGN_idEdit) ? data.KLSubRGN_idEdit : '',
				KLTown_id: (data.KLTown_idEdit) ? data.KLTown_idEdit : ''
			}
		}else{
			params = {
				KLHome: (typeof data.Address_HouseEdit == 'object') ? data.Address_HouseEdit.getValue() : data.Address_HouseEdit,
				KLCity_id: (typeof data.KLCity_idEdit == 'object') ? data.KLCity_idEdit.getValue() : data.KLCity_idEdit,
				KLCountry_id: (typeof data.KLCountry_idEdit == 'object') ? data.KLCountry_idEdit.getValue() : data.KLCountry_idEdit,
				KLRgn_id: (typeof data.KLRgn_idEdit == 'object') ? data.KLRgn_idEdit.getValue() : data.KLRgn_idEdit,
				KLStreet_id: (typeof data.KLStreet_idEdit == 'object') ? data.KLStreet_idEdit.getValue() : data.KLStreet_idEdit,
				KLSubRGN_id: (typeof data.KLSubRGN_idEdit == 'object') ? data.KLSubRGN_idEdit.getValue() : data.KLSubRGN_idEdit,
				KLTown_id: (typeof data.KLTown_idEdit == 'object') ? data.KLTown_idEdit.getValue() : data.KLTown_idEdit
			}
		}

		//параметр отвечающий за выборку телефона только в указанной мо
		if(data.lpu_id){
			params.Lpu_id = data.lpu_id;
		}

		if(age){
			//var AgeType_id = base_form.getValues()['ComboCheck_AgeType_id'];
			var AgeType_id = formValues.ComboCheck_AgeType_id;
			if(AgeType_id){
				age = ( AgeType_id == 219 ) ? age : 1;
				params.Person_Age = age;
			}
		}

		if(params.KLStreet_id && (params.KLTown_id || params.KLCity_id) ){
			Ext.Ajax.request({
				url: '/?c=LpuStructure&m=getLpuAddress',
				params: params,
				success: function(response, opts){
					var response_obj = Ext.util.JSON.decode(response.responseText);
					if(response_obj.length !=0 && response_obj[0] && response_obj[0].Lpu_id){
						if(callback != undefined && typeof callback == 'function'){
							callback(response_obj[0]);
						}
					}else{
						callback(false);
					}
				},
				failure: function(response, opts){
					return false;
				}
			});
		}else{
			return false;
		}
	},
	/*
	//функция упразднена
	getPhoneMO: function(Lpu_id, callback){
		//Получить номер телефона из настроек группы отделений 
		var me = this;
		if(!Lpu_id) return false;
		var params = {
			Lpu_id: Lpu_id
		}
		Ext.Ajax.request({
			url: '/?c=LpuStructure&m=getLpuPhoneMO',
			params: params,
			success: function(response, opts){
				var response_obj = Ext.util.JSON.decode(response.responseText);
				if(response_obj.length !=0 && response_obj[0].Phone){
					if(callback != undefined && typeof callback == 'function'){
						callback(response_obj[0].Phone);
					}
					me.setPhoneMO(response_obj[0].Phone);
				}else{
					me.setPhoneMO(false);
				}
			},
			failure: function(response, opts){
				return false;
			}
		});
	},
	*/
	setPhoneMO: function(Phone){
		//отобразим номер телефона МО рядом с полем
		var me = this;
		var divPhone = document.getElementById(me.id+'_'+'CMPCLOSE_ComboValue_693_Phone');
		if(!divPhone) return false;
		if(Phone){
			divPhone.innerHTML='Тел. '+Phone;
		}else{
			divPhone.innerHTML='';
		}
	},
	setTeamComplect: function(data, cmp){
		var me = this;
		var divPhone = document.getElementById(me.id+'_'+'CMPCLOSE_CB_'+cmp.code+'_TeamComplect');
		if(!divPhone) return false;
		
		var inputValue = null;
		switch (cmp.code) {
			case 3473:
				inputValue = data.EmergencyTeam_HeadShiftFIO;
				break;
			case 3474:
				inputValue = data.EmergencyTeam_HeadShift2FIO;
				break;
			case 3475:
				inputValue = data.EmergencyTeam_Assistant1FIO;
				break;
			case 3477:
				inputValue = data.EmergencyTeam_DriverFIO;
				break;
		}
		
		divPhone.innerHTML = inputValue || '';
		
		if(inputValue){
			cmp.setValue(true);
		}
		
	},
	addressClick: function(id, action, xy, callback) {
		var arrXY = xy || false;
		if(!id || !action) return;
		var addressObj = {
			Address_ZipEdit : this.id+'_CMPCLOSE_ComboValue_701',
			KLCountry_idEdit : this.id+'_CMPCLOSE_ComboValue_702',
			KLRgn_idEdit : this.id+'_CMPCLOSE_ComboValue_703',
			KLSubRGN_idEdit : this.id+'_CMPCLOSE_ComboValue_704',
			KLCity_idEdit : this.id+'_CMPCLOSE_ComboValue_705',
			KLTown_idEdit : this.id+'_CMPCLOSE_ComboValue_706',
			KLStreet_idEdit : this.id+'_CMPCLOSE_ComboValue_707',
			Address_HouseEdit : this.id+'_CMPCLOSE_ComboValue_708',
			Address_CorpusEdit : this.id+'_CMPCLOSE_ComboValue_709',
			Address_FlatEdit : this.id+'_CMPCLOSE_ComboValue_710',
			Address_AddressEdit : this.id+'_CMPCLOSE_ComboValue_711',
			PersonSprTerrDop_idEdit: this.id+'_CMPCLOSE_ComboValue_2188'
		};
		var comboTxt = Ext.getCmp(id); // текстовое поле дополнительного адреса
		var addressFields = {};
		var obj, v;
		// объект нужных полей, дабы дальше их не искать
		for(var key in addressObj){
			v = addressObj[key];
			obj =  Ext.getCmp(v);
			if(obj){
				addressFields[key] = obj;
			}
		}
		var addressForm = getWnd('swAddressEditWindow');
		var addressSequil = function(objAddr){
			// соберем данные адресов в один объект
			var obj = {};
			if(objAddr){
				if(objAddr.UAddress_Address){
					obj['adrRegistr']={
						Address_ZipEdit : objAddr.UAddress_Zip,
						KLCountry_idEdit : objAddr.UKLCountry_id,
						KLRgn_idEdit : objAddr.UKLRGN_id,
						KLSubRGN_idEdit : objAddr.UKLSubRGN_id,
						KLCity_idEdit : objAddr.UKLCity_id,
						KLTown_idEdit : objAddr.UKLTown_id,
						KLStreet_idEdit : objAddr.UKLStreet_id,
						Address_HouseEdit : objAddr.UAddress_House,
						Address_CorpusEdit : objAddr.UAddress_Corpus,
						Address_FlatEdit : objAddr.UAddress_Flat,
						Address_AddressEdit : objAddr.UAddress_AddressText
					};
				}
				if(objAddr.PAddress_Address){
					obj['adrResidence']={
						Address_ZipEdit : objAddr.PAddress_Zip,
						KLCountry_idEdit : objAddr.PKLCountry_id,
						KLRgn_idEdit : objAddr.PKLRGN_id,
						KLSubRGN_idEdit : objAddr.PKLSubRGN_id,
						KLCity_idEdit : objAddr.PKLCity_id,
						KLTown_idEdit : objAddr.PKLTown_id,
						KLStreet_idEdit : objAddr.PKLStreet_id,
						Address_HouseEdit : objAddr.PAddress_House,
						Address_CorpusEdit : objAddr.PAddress_Corpus,
						Address_FlatEdit : objAddr.PAddress_Flat,
						Address_AddressEdit : objAddr.PAddress_AddressText
					};
				}
			}
			var adrCall={
				KLRgn_idEdit : base_form.findField('KLRgn_id').getValue(),
				KLSubRGN_idEdit : base_form.findField('Area_id').getValue(),
				KLCity_idEdit : base_form.findField('City_id').getValue(),
				KLTown_idEdit : base_form.findField('Town_id').getValue(),
				KLStreet_idEdit : base_form.findField('Street_id').getValue(),
				Address_HouseEdit : base_form.findField('House').getValue(),
				Address_CorpusEdit : base_form.findField('Korpus').getValue(),
				Address_FlatEdit : base_form.findField('Office').getValue(),
				Address_AddressEdit : '',
				KLCountry_idEdit : ''
			}
			var str='';
			if(adrCall['KLCity_idEdit']) str='г. '+base_form.findField('City_id').getRawValue();
			if(adrCall['KLTown_idEdit']) str += ', н.п. '+base_form.findField('Town_id').getRawValue();
			if(adrCall['KLStreet_idEdit']) str += ', '+base_form.findField('StreetAndUnformalizedAddressDirectory_id').getRawValue();
			if(adrCall['Address_HouseEdit']) str += ', д.'+adrCall['Address_HouseEdit'];
			if(adrCall['Address_CorpusEdit']) str += ', к.'+adrCall['Address_CorpusEdit'];
			if(adrCall['Address_FlatEdit']) str += ', кв.'+adrCall['Address_FlatEdit'];
			if(str){
				adrCall['Address_AddressEdit'] = str;
				adrCall['KLCountry_idEdit'] = (getCountryName() == "Россия") ? "643" : "398" ;
				obj['adrCall'] = adrCall;
			}
			if(Object.keys(obj).length === 0){
				showAddressMenu(false);
			}else{
				showAddressMenu(obj);
			}
		};

		var showAddressMenu = function(obj){
			// выпадающее меню
			var menu = new Ext.menu.Menu();
			if(!obj){
				var notAddress = new Ext.menu.Item({
					text: 'данные адреса не найдены',
					handler: function(){menu.hide();}
				});
				menu.add(notAddress);
			}else{
				if(obj['adrRegistr']){
					var adrRegistr = new Ext.menu.Item({
						text: '<b>Адрес регистрации:</b> '+obj['adrRegistr']['Address_AddressEdit'],
						handler: function(){setAddressFields(obj['adrRegistr']);menu.hide();}
					});
					menu.add(adrRegistr);
				}
				if(obj['adrResidence']){
					var adrResidence = new Ext.menu.Item({
						text: '<b>Адрес проживания:</b> '+obj['adrResidence']['Address_AddressEdit'],
						handler: function(){setAddressFields(obj['adrResidence']);menu.hide();}
					});
					menu.add(adrResidence);
				}
				if(obj['adrCall']){
					var adrCall = new Ext.menu.Item({
						text: '<b>Адрес вызова:</b> '+obj['adrCall']['Address_AddressEdit'],
						handler: function(){setAddressFields(obj['adrCall']);menu.hide();}
					});
					menu.add(adrCall);
				}
			}
			// покажем выпадающее меню
			menu.showAt(arrXY);

			var setAddressFields = function(obj){
				// заполняем доп поля адреса

				for(var key in obj){
					if(obj[key] && addressFields[key]){
						addressFields[key].setValue(obj[key]);
					}
				}
				comboTxt.setValue(obj['Address_AddressEdit']);
				if(callback != undefined && typeof callback == 'function'){
					callback(obj);
				}
			}
		};

		switch (action){
			case 'paste':
				var fieldsObj={};
				for(var key in addressFields){
					fieldsObj[key] = addressFields[key].value;
				}
				addressForm.show({
					fields: fieldsObj,
					callback: function(val) {
						for(var key in addressFields){
							if(val[key]){
								addressFields[key].setValue(val[key]);
							}
						}
						comboTxt.setValue(val['Address_AddressEdit']);
						if(callback != undefined && typeof callback == 'function'){
							callback(addressFields);
						}
					},
					onClose: function() {
						comboTxt.focus(true, 500);
					}
				});
				break;
			case 'sequil':
				var me = this;
				var base_form = this.FormPanel.getForm();

				var idPerson = base_form.findField('Person_id').getValue();

				if(idPerson){
					Ext.Ajax.request({
						params: {
							person_id: idPerson
						},
						url: '/?c=Person&m=getPersonEditWindow',
						success: function(response) {
							var response_obj = Ext.util.JSON.decode(response.responseText);
							addressSequil(response_obj[0]);
						}.createDelegate(this),
						failure: function(response) {
							addressSequil(false);
						}.createDelegate(this)
					});
				}else{
					addressSequil(false);
				}

				break;
			case 'clear':
				for(var key in addressFields){
					addressFields[key].setValue("");
				}
				comboTxt.setValue("");
				break;
			default:
				return false;
				break;
		}
	},
	
	showSearchCityWindow: function(){
		var me = this,
			base_form = this.FormPanel.getForm(),
			klareastatField = base_form.findField('KLAreaStat_idEdit'),
			klrgn_id = base_form.findField('KLRgn_id').getValue(),
			klrgn_name = base_form.findField('KLRgn_id').getRawValue(),
			klsubrgnField = base_form.findField('Area_id'),
			klsubrgn_id = klsubrgnField.getValue(),
			klcityField = base_form.findField('City_id'),
			klsubrgn_name = klsubrgnField.getRawValue();
		
		getWnd('swKLCitySearchWindow').show({
			onSelect: function(response_data) {

				klareastatField.getStore().clearFilter();
				if(response_data.KLRegion_id != getGlobalOptions().region.number)
				klcityField.getStore().load({
					params:{subregion_id:response_data.KLRegion_id},
					callback: function(){
						this.setValue(response_data.KLCity_id||null);
						this.fireEvent('beforeselect', this, this.getStore().getAt(this.getStore().findBy(function(rec) {return rec.get('City_id') == this.getValue();}.createDelegate(this))));

					}.createDelegate(klcityField)
				});
				klsubrgnField.getStore().load({
					params:{region_id:response_data.KLRegion_id},
					callback: function(){
						klsubrgnField.setValue(response_data.KLSubRegion_id||null);
					}
				});

				//если нет города ставим район
				var klareastat = klareastatField.getStore().findBy(function(rec){
					if(
						(rec.get('KLCity_id') == response_data.KLCity_id)
					)
					{
						klareastatField.setValue(rec.get('KLAreaStat_id'));
						return 1
					}
				});
				
				if(klareastat == -1){
					klareastatField.getStore().findBy(function(rec){
						if(
							(rec.get('KLSubRGN_id') == response_data.KLSubRegion_id)
						)
						{
							klareastatField.setValue(rec.get('KLAreaStat_id'));
						}
					})
				};


				base_form.findField('Street_id').reset();
				base_form.findField('Town_id').reset();
			}.createDelegate(this),
			params: {
				KLSubRegion_id: klsubrgn_id,
				KLSubRegion_Name: klsubrgn_name,
				KLRegion_id: klrgn_id,
				KLRegion_Name: klrgn_name
			}
		});
	},
	
	showTownSearchWindow: function(){
		
		var me = this,
			base_form = this.FormPanel.getForm(),
			klareastatField = base_form.findField('KLAreaStat_idEdit'),
			klrgnField = base_form.findField('KLRgn_id'),
			klsubrgnField = base_form.findField('Area_id'),
			klcityField = base_form.findField('City_id'),
			kltownField = base_form.findField('Town_id');
			
		getWnd('swKLTownSearchWindow').show({
			onSelect: function(response_data) {
				klsubrgnField.getStore().load({
					params: {region_id: response_data.KLRegion_id},
					callback: function() {
						klsubrgnField.setValue(response_data.KLSubRegion_id||null);
					}.createDelegate(base_form.findField('Area_id'))
				});
				
				klcityField.getStore().load({
					params: {subregion_id: response_data.KLSubRegion_id },
					scope: klcityField,
					callback: function(){klcityField.setValue(response_data.KLCity_id||null)}
				});
				
				kltownField.getStore().load({
					params: {city_id: response_data.KLCity_id||response_data.KLSubRegion_id },
					scope: kltownField,
					callback: function(){kltownField.setValue(response_data.KLTown_id);}
				});

				base_form.findField('Street_id').reset();				
			}.createDelegate(this),
			params: {
				KLRegion_id: klrgnField.getValue(),
				KLRegion_Name: klrgnField.getRawValue(),
				KLSubRegion_id: klsubrgnField.getValue(),		
				KLSubRegion_Name: klsubrgnField.getRawValue(),
				KLCity_id: klcityField.getValue(),
				KLCity_Name: klcityField.getRawValue()
			}
		});
	},

	existenceNumbersDayYear: function(){
		// проверка на уникальность введенного номера вызова за день и за год
		var me = this,
			base_form = me.FormPanel.getForm(),
			Day_num = base_form.findField('Day_num'),
			Year_num = base_form.findField('Year_num'),
			dateObj = new Date( base_form.findField('AcceptTime').value );

		if(!Day_num.getValue() || !Year_num.getValue() || Day_num.disabled || Year_num.disabled) return;

		Ext.Ajax.request({
			url: '/?c=CmpCallCard&m=existenceNumbersDayYear',
			params: {
				Day_num: Day_num.getValue(),
				Year_num: Year_num.getValue(),
				Lpu_id: getGlobalOptions().lpu_id,
				AcceptTime: Ext.util.Format.date(dateObj, 'Y-m-d H:i:s')
				//AcceptTime: Ext.util.Format.date(dateObj, 'Y-m-d')
			},
			callback: function (opt, success, response) {
				if(success){
					var res = Ext.util.JSON.decode(response.responseText);
					if(res['existenceNumbersDay'] && !Day_num.disabled ){
						//если такой номер вызова за день уже существует 
						//то поставим значение предложенное системой
						Day_num.setValue(res['nextNumberDay']);
					}
					if(res['existenceNumbersYear'] && !Year_num.disabled ){
						//если такой номер вызова за год уже существует
						Year_num.setValue(res['nextNumberYear']);
					}
				}
			}
		});
	},

	initexistenceNumbersDayYearListeners: function(){
		// навешиваем обработчики на проверку уникальности введенных номеров за день и год
		var me = this,
			base_form = me.FormPanel.getForm(),
			fieldsArr = ['Day_num', 'Year_num'];
		fieldsArr.forEach(function(item,i){
			var el=base_form.findField(item);
			if(el){
				el.addListener('blur', function(combo){
					if(combo.startValue && combo.startValue!=combo.value) {
						// проверка на уникальность введенного номера
						me.existenceNumbersDayYear();
					}
				});
			}
		});
	},
	
	checkPersonIdentification: function(){
		var me = this,
			base_form = me.FormPanel.getForm(),
			Person_IsUnknown = base_form.findField('Person_IsUnknown'),
			PersonFields_IsDirty = base_form.findField('PersonFields_IsDirty'),
			personBirtDay = Date.parseDate(me.getBirthday(), 'Y-m-d'),
			AgeType_id = base_form.getValues()['ComboCheck_AgeType_id'],
			personBirtDayFrom, personBirtDayTo,
			PersonAge_AgeFrom = base_form.findField('Age').getValue(),
			PersonAge_AgeTo = base_form.findField('Age').getValue();

		if(Person_IsUnknown && PersonFields_IsDirty && Person_IsUnknown.getValue() == '2' && PersonFields_IsDirty.getValue() == 'true'){

			PersonFields_IsDirty.setValue('false');
			
			Ext.Ajax.abort();

			switch(AgeType_id){
				case '221':
				case '220': {
					personBirtDayFrom =  Ext.util.Format.date(new Date(personBirtDay.setMonth(personBirtDay.getMonth()-1)), 'd.m.Y');
					personBirtDayTo =  Ext.util.Format.date(new Date(personBirtDay.setMonth(personBirtDay.getMonth()+1)), 'd.m.Y');
					PersonAge_AgeFrom = 0;
					PersonAge_AgeTo = 0;
					break;
				}
				default: {
					break;
				}
			}
			
			Ext.Ajax.request({
				url: '/?c=Person&m=getPersonSearchGrid',
				autoAbort: true,
				params: {
					PersonSurName_SurName: base_form.findField('Fam').getValue(),
					PersonFirName_FirName: base_form.findField('Name').getValue(),
					PersonSecName_SecName: base_form.findField('Middle').getValue(),
					PersonAge_AgeFrom: PersonAge_AgeFrom,
					PersonAge_AgeTo: PersonAge_AgeTo,
					personBirtDayFrom : personBirtDayFrom,
					personBirtDayTo : personBirtDayTo,
					checkForMainDB: true,
					Sex_id: base_form.findField('Sex_id').getValue(),
					limit: 100,
					searchMode: 'all',
					start: 0,
					ParentARM: base_form.findField('ARMType').getValue()
				},
				callback: function(o, success, r) {
					if( success ) {
						var response = Ext.util.JSON.decode(r.responseText);
						
						switch(true){
							case (response.totalCount == 0): {
								//console.warn('none', response);
								break;
							}
							case (response.totalCount == 1): {

								me.setPerson(response.data[0]);
								//console.warn('one', response);
								break;
							}
							case (response.totalCount > 1): {
								//me.personSearch(true, true);
								me.selectPersonAfterRequest(response);
								//console.warn('many', response);
								break;
							}
							default: {
								//console.warn('err?', response);
								break;
							}
						};
					}
				}
			});
		};
	},
	
	selectPersonAfterRequest: function(personsData){
		
		var me = this,
			SelectPersonWin = new Ext.Window({
				width:980,
				heigth:600,
				title:'Выбор пациента',
				modal: false,
				draggable:false,
				resizable:false,
				closable : false,
				items:[{
					xtype: 'grid',
					columns: [
						{ header: 'Person_id',  dataIndex: 'Person_id', width: 60, hidden: true },
						{ header: 'Фамилия',  dataIndex: 'PersonSurName_SurName', flex: 1 },
						{ header: 'Имя', dataIndex: 'PersonFirName_FirName', width: 80 },
						{ header: 'Отчество', dataIndex: 'PersonSecName_SecName', width: 100 },
						{ header: 'Дата рождения', dataIndex: 'PersonBirthDay_BirthDay', width: 90 },
						{ header: 'Дата смерти', dataIndex: 'Person_deadDT', width: 90 },
						{ header: 'Адрес регистрации', dataIndex: 'UAddress_AddressText', width: 140 },
						{ header: 'Адрес проживания', dataIndex: 'PAddress_AddressText', width: 140 },
						{ header: 'ЛПУ прикрепления', dataIndex: 'Lpu_Nick', width: 90 }
					],
					store:new Ext.data.GroupingStore({
						data: personsData,
						fields: [
							{name: 'Person_id'},
							{name: 'PersonSurName_SurName'},
							{name: 'PersonFirName_FirName'},
							{name: 'PersonSecName_SecName'},
							{name: 'PersonBirthDay_BirthDay'},
							{name: 'Person_deadDT'},
							{name: 'UAddress_AddressText'},
							{name: 'PAddress_AddressText'},
							{name: 'Lpu_Nick'}
						],
						reader: new Ext.data.JsonReader({
								root: 'data'
							},						
							Ext.data.Record.create([
								{name: 'Person_id'},
								{name: 'PersonSurName_SurName'},
								{name: 'PersonFirName_FirName'},
								{name: 'PersonSecName_SecName'},
								{name: 'PersonBirthDay_BirthDay'},
								{name: 'Person_deadDT'},
								{name: 'UAddress_AddressText'},
								{name: 'PAddress_AddressText'},
								{name: 'Lpu_Nick'}
							])							
						)
					}),
					height: 350,
					view: new Ext.grid.GridView({
						forceFit: false
					}),
					listeners: {					
						dblclick: function() {
							var grid = this,
								selected = grid?grid.getSelectionModel().getSelected():null;
							if(selected)
							{

								me.setPerson(selected.data);
								SelectPersonWin.close();
							}
						}
					}
				}],
				buttons:[
					{
						handler: function(){
							var grid = SelectPersonWin.findByType('grid')[0],
								selected = grid?grid.getSelectionModel().getSelected():null;
							if(selected)
							{
								me.setPerson(selected.data);
								SelectPersonWin.close();
							}
						},
						iconCls: 'ok16',
						text: langs('Выбрать')
					},
					{
						text: '-'
					},
					{
						handler: function(){ShowHelp(this.ownerCt.title);},
						text: BTN_FRMHELP,
						iconCls: 'help16'				
					},	
					{
						handler: function() {
							SelectPersonWin.close();
						},
						iconCls: 'cancel16',
						text: BTN_FRMCANCEL
					}
				]
			});
			
		SelectPersonWin.show();
	},
	/*
	//получение стандарта медицинской помощи
	checkEmergencyStandart: function(){
		var me = this,
			base_form = me.FormPanel.getForm(),
			emergencyStandartField = base_form.findField('EmergencyStandart'),
			personField = base_form.findField('Person_id'),
			diagField = base_form.findField('Diag_id');
		
		if(!emergencyStandartField) return false;
		emergencyStandartField.reset();
		if(personField && personField.getValue() && diagField && diagField.getValue())
		{
			Ext.Ajax.request({
				params: {
					Diag_id: diagField.getValue(),
					Person_id: personField.getValue()
				},
				url: '/?c=CmpCallCard&m=checkEmergencyStandart',
				callback: function (obj, success, response) {
					if (success) {
						var response_obj = Ext.util.JSON.decode(response.responseText);
						
						if(response_obj[0] && response_obj[0]["EmergencyStandart_Code"]){
							emergencyStandartField.setValue(response_obj[0]["EmergencyStandart_Code"]);
						}
					}
				}.createDelegate(this)
			});
		}
	}
	*/
	getExpertResponseFields: function () {
		var me = this;
		me.ExpertResponseFields = new Ext.Panel({
			items: []
		});

		Ext.Ajax.request({
			url: '/?c=CmpCallCard&m=getExpertResponseFields',
			callback: function (obj, success, response) {
				var response_obj = Ext.util.JSON.decode(response.responseText),
					Attributes = response_obj.Attributes,
					ExpertResponseTypes = response_obj.ExpertResponseTypes;
				if(!Attributes || Attributes.length==0 || !ExpertResponseTypes) return;

				for (var i = 0; i < ExpertResponseTypes.length; i++) {
					var type = ExpertResponseTypes[i],
						items = [];
					for (var j = 0; j < Attributes.length; j++) {

						var attr = Attributes[j],
							container = new Ext.Container({
								autoEl: {},
								layout: 'table',
								width: 700,
								defaults: {
									style:'margin:0 5px'
								},
								items: []
							});
						container.add(
							{
								name: 'ExpertResponseType_' + type.ExpertResponseType_id + '_AttributeValue_' + attr.AttributeValue_id,
								id: 'ExpertResponseType_' + type.ExpertResponseType_id + '_AttributeValue_' + attr.AttributeValue_id,
								boxLabel: attr.AttributeValue_Value + ' - ' + attr.AttributeValue_Text,
								xtype: 'checkbox',
								ctCls: 'expert-response-checkbox',
								hideLabel: true,
								width: 200,
								AttributeValue_id: attr.AttributeValue_id,
								CMPCloseCardExpertResponse_id: null,
								isDirty: false,
								listeners: {
									check: function (cmp, newVal, oldVal) {
										var labelField = Ext.getCmp(cmp.id + '_Label');
										cmp.isDirty = true;
										Ext.getCmp(cmp.id + '_Comm').setVisible(newVal);
										if(newVal && !labelField.text){
											var arrFio = getGlobalOptions().pmuser_fullname.split(' '),
												date = Ext.util.Format.date(new Date(),'d.m.Y H:i');
											labelField.setText(date + ' ' + arrFio[0] + ' ' + arrFio[1].charAt(0) + '.' + arrFio[2].charAt(0) + '.')
										}else{
											labelField.setText()
										}
										labelField.setVisible(newVal);

									}.createDelegate(this)
								}
							}
						);
						container.add(
							{
								xtype: 'textfield',
								width: 250,
								name: 'ExpertResponseType_' + type.ExpertResponseType_id + '_AttributeComm_' + attr.AttributeValue_id,
								id: 'ExpertResponseType_' + type.ExpertResponseType_id + '_AttributeValue_' + attr.AttributeValue_id + '_Comm',
								hidden: true
							}
						);
						container.add(
							{
								xtype: 'label',
								width: 250,
								disabled: true,
								hidden: true,
								id: 'ExpertResponseType_' + type.ExpertResponseType_id + '_AttributeValue_' + attr.AttributeValue_id + '_Label',
							}
						);
						items.push(container)
					}

					me.ExpertResponseFields.add(
						{
							xtype: 'fieldset',
							autoHeight: true,
							layout: 'form',
							items: [
								{
									columns: [700],
									vertical: true,
									name: 'ExpertResponseType_' + type.ExpertResponseType_id,
									ExpertResponseType_id: type.ExpertResponseType_id,
									fieldLabel: type.ExpertResponseType_Name,
									width: '100%',
									cls: 'expert-response-container',
									xtype: 'checkboxgroup',
									singleValue: false,
									items: items
								}
							]
						}
					)
				}
				me.ExpertResponseFields.doLayout();

			}.createDelegate(this)
		});
		return me.ExpertResponseFields;
	},
	loadCmpCloseCardExpertResponses: function(data){

		var me = this;

		if(!data.CmpCloseCard_id || !me.ExpertResponseFields) return false;

		Ext.Ajax.request({
			params: {
				CmpCloseCard_id: data.CmpCloseCard_id
			},
			url: '/?c=CmpCallCard&m=getCmpCloseCardExpertResponses',
			callback: function (obj, success, response) {
				var response_obj = Ext.util.JSON.decode(response.responseText)
				if(response_obj && response_obj.length){
					for(var key = 0; response_obj.length > key; key++) {
						var elem = response_obj[key],
						fieldId = 'ExpertResponseType_' + elem.CMPCloseCardExpertResponseType_id + '_AttributeValue_' + elem.AttributeValue_id,
						field = Ext.getCmp(fieldId),
						fieldComm = Ext.getCmp(fieldId + '_Comm'),
						fieldLabel = Ext.getCmp(fieldId + '_Label');

						field.CMPCloseCardExpertResponse_id = elem.CMPCloseCardExpertResponse_id;
						field.setValue(1);
						fieldLabel.setText(elem.ResponseDT + ' ' + elem.Person_FIO);
						fieldComm.setValue(elem.CMPCloseCardExpertResponse_Comment);

					}
				}
			}
		})
	},
	getTheDistanceInATimeInterval: function(){
		var me = this,
			base_form = me.FormPanel.getForm(),
			gotime =  base_form.findField('GoTime').getStringValue(),
			endtime = base_form.findField('EndTime').getStringValue(),
			backtime = base_form.findField('BackTime') ? base_form.findField('BackTime').getStringValue():null,
			emergencyTeamField = base_form.findField('EmergencyTeam_id'),
			emergencyTeam_id = emergencyTeamField ? emergencyTeamField.getValue() : null,
			kiloField = base_form.findField('Kilo'),
			userKiloField = base_form.findField('CmpCloseCard_UserKilo');

		endtime = !Ext.isEmpty(backtime) ? backtime : endtime;
		if(gotime && endtime && !Ext.isEmpty(emergencyTeam_id)){
			Ext.Ajax.request({
				params: {
					GoTime: gotime,
					EndTime: endtime,
					EmergencyTeam_id: emergencyTeam_id
				},
				url: '/?c=CmpCallCard&m=getTheDistanceInATimeInterval',
				callback: function (obj, success, response) {
					var response_obj = Ext.util.JSON.decode(response.responseText)
					if(response_obj.success){
						if(kiloField)
							kiloField.setValue(response_obj.data)

						if(userKiloField)
							userKiloField.setValue(response_obj.data)
					}
				}
			})
		}

	},
	isOsmUslugaComplex: function(code){
		if(!code) return false;
		var osmUslugaCodes = ['B01.044.002', 'B02.001.002', 'B01.044.001', 'B01.031.001', 'B01.003.001',
				'B01.032.001', 'B01.023.001', 'B01.015.001', 'B01.044.002.999', 'B01.044.001.999', 'A23.30.042.002'];

		return code.inlist(osmUslugaCodes);
	},
	//Контроль наличия услуги осмотра
	existOsmUslugaComplex: function(){

		var me = this,
            uslugaPanel = me.FormPanel.find('refId', 'uslugaGrid'),
			existOsmUslugaComplex = false;

        if(uslugaPanel && uslugaPanel[0]){
            uslugaPanel[0].getStore().each(function(rec){

				if(rec.get('CmpCallCardUsluga_Kolvo') > 0){

					if(me.isOsmUslugaComplex(rec.get('UslugaComplex_Code'))){
						existOsmUslugaComplex = true;
                    }
                };
            });
        };
		return existOsmUslugaComplex;
        /*
		if(me.UslugaPanel && me.uslugaWrapper){

			for (var i = 0; i < me.uslugaWrapper.items.length; i++) {
				var cmp = me.uslugaWrapper.items.items[i].items.items[0];

				if(cmp.getValue()){
					if(me.isOsmUslugaComplex(cmp.UslugaComplex_Code)){
						return true;
					}
				}
			}
		}
        */
	},
	//Функция определения услуги осмотра
	getOsmUslugaComplexCode: function(){
		var me = this,
			base_form = me.FormPanel.getForm(),
			CmpCallPlaceTypeCombo = base_form.findField('CmpCallPlaceType_id'),
			CmpCallPlaceTypeCode = CmpCallPlaceTypeCombo.getCodeValues()[0],
			CmpResultCombo = base_form.findField('CmpResult_id'),
			CmpResultRec = CmpResultCombo.store.getById(CmpResultCombo.getValue()),
			EmergencyTeamSpecCombo = base_form.findField('EmergencyTeamSpec_id');

		if(CmpResultRec){
			switch(CmpResultRec.get('CmpResult_Code')){
				case 21:
				case 22:
					if((EmergencyTeamSpecCombo.getValue() == 29) && (CmpCallPlaceTypeCode == 7)){
						return 'B01.044.002.999';
					}else if((CmpCallPlaceTypeCode == 7)){
						return 'B01.044.001.999';
					}
					break;
				case 15:
				case 16:
				case 20:
				case 51:
					return 'A23.30.042.002';
					break;
			}
		}

		switch (parseInt(EmergencyTeamSpecCombo.getValue())) {

			case 2:
			case 6:
			case 24:
				return 'B01.044.001';
				break;
			case 9:
			case 12:
			case 21:
			case 27:
				return 'B01.003.001';
				break;
			case 5:
			case 22:
				return 'B01.031.001';
				break;
			case 7:
			case 23:
				return 'B01.015.001';
				break;
			case 8:
			case 25:
				return 'B01.023.001';
				break;
			case 13:
				return 'B.01.032.001';
				break;
			case 29:
			case 4:
				return 'B01.044.002';
				break;
		}


	},
	addOsmUslugaComplex: function(code){
		if(!getRegionNick().inlist(['perm'])) return false;
		if(!code){
			code = this.getOsmUslugaComplexCode();
		}

		var uslugaPanel = this.FormPanel.find('refId', 'uslugaGrid');

		if(uslugaPanel && uslugaPanel[0]){
			var uslugaIndex = uslugaPanel[0].getStore().find('UslugaComplex_Code', code);
			var uslugaRec = uslugaPanel[0].getStore().getAt(uslugaIndex);

            if(uslugaRec)
			uslugaRec.set('CmpCallCardUsluga_Kolvo', 1)
		}

	},
	resetFields: function(){
		var diag_sid_panel = Ext.getCmp('diag_sid_panel'),
			diag_ooid_panel = Ext.getCmp('diag_ooid_panel');
		if(diag_sid_panel)
			diag_sid_panel.reset(false);
		if(diag_ooid_panel)
			diag_ooid_panel.reset(false);
	},
	closeWindow: function(){
		//нужно убедится, что мы во iframe АРМа ДП и тогда закрыть iframe
		var arm = getGlobalOptions().curARMType;
		// var panel = parent.Ext.getCmp('inPanel');
		var body1 = parent.Ext.getBody();
		var body2 = Ext.getBody();
		
		if(/*arm == 'smpdispatchstation' && */body1.id != body2.id){
			parent.Ext.WindowManager.getActive().close();
		}
	},
    //загрузка справочников в элементе
    loadComboStoresMongo: function(){
        var GroupComplicat = this.FormPanel.find('name','GroupComplicat')[0];

        if(!GroupComplicat) return;

        cmps = this.getAllFieldsInComponent(GroupComplicat);
        this.loadStores(cmps);
    },

    //метод получения всех полей с компонента
    getAllFieldsInComponent: function(parentEl, asObject){
        var me = this,
            parentEl = parentEl || me.FormPanel.getForm(),
            fieldsTop = parentEl.items.items,
            allFields = [],
			allFieldsObj = {};

        var getAllFields = function(cmps){
            for(var i = 0; i < cmps.length; i++){
            	if (asObject && cmps[i].getName) {
					allFieldsObj[cmps[i].getName()] = cmps[i];
				}
                allFields.push(cmps[i]);
                if(cmps[i].items && cmps[i].items.items.length){
                    getAllFields(cmps[i].items.items)
                };
            }
        };

        getAllFields(fieldsTop);

        return asObject? allFieldsObj: allFields;
    },

	checkDiagFinance: function(callback){
		var me = this,
			base_form = me.FormPanel.getForm(),
			params = {
			Diag_id: base_form.findField('Diag_id').getValue(),
			Age: base_form.findField('Age').getValue(),
			Sex_id: base_form.findField('Sex_id').getValue(),
			Person_id: base_form.findField('Person_id').getValue()
		}

		if(!params.Diag_id) return;

		Ext.Ajax.request({
			params: params,
			url: '/?c=CmpCallCard&m=checkDiagFinance',
			callback: function (obj, success, response) {
				if (success && callback && response.responseText) {
					callback(Ext.util.JSON.decode(response.responseText), me);
				}
				else{
					callback(false, me);
				}
			}
		});
	},
	setDefaultPayType: function(){

		if(!getRegionNick().inlist(['perm'])) return false;

		var me = this,
			base_form = me.FormPanel.getForm(),
			PayTypeCombo = base_form.findField('PayType_id'),
			CmpResultCombo = base_form.findField('CmpResult_id'),
			CmpResultRec = CmpResultCombo.store.getById(CmpResultCombo.getValue()),
			EmergencyTeamSpecCombo = base_form.findField('EmergencyTeamSpec_id'),
			EmergencyTeamSpecRec = EmergencyTeamSpecCombo.store.getById(EmergencyTeamSpecCombo.getValue()),
			CmpReasonCombo = base_form.findField('CallPovod_id'),
			CmpReasonRec = CmpReasonCombo.store.getById(CmpReasonCombo.getValue());

		/**
		 Значение применяется при выполнении условий:
		 o	Результат вызова входит в следующий перечень результатов (коды результатов – CmpResult_Code): 1, 2, 3, 4, 5, 6, 7, 8, 9, 11, 12, 13, 14, 15, 16, 19, 21, 22, 23, 28, 29, 31, 32, 41, 42, 51.
		 o	Профиль бригады входит в следующий перечень профилей (коды профилей бригад – EmergencyTeamSpec_Code): Т, Р, К, Н, Д, О, Л, Е, Б, Ф, БИТ.
		 o	Повод вызова указан и НЕ входит в следующий перечень поводов (код повода – CmpReason_Code): «40У», «70?».
		 o	Основной диагноз вызова входит в перечень диагнозов, оплачиваемых по ОМС. Оплачивается диагноз по ОМС или нет, проверяется по значению столбца DiagFinance_IsOms в таблице DiagFinance: «2» – оплачивается, иное значение – не оплачивается.
		 */

		if(
			!PayTypeCombo.getValue()
			&& CmpResultRec.get('CmpResult_Code')
			&& CmpResultRec.get('CmpResult_Code').inlist([1, 2, 3, 4, 5, 6, 7, 8, 9, 11, 12, 13, 14, 15, 16, 19, 21, 22, 23, 28, 29, 31, 32, 41, 42, 51])
			&& EmergencyTeamSpecRec.get('EmergencyTeamSpec_Code').inlist(['Т', 'Р', 'К', 'Н', 'Д', 'О', 'Л', 'Е', 'Б', 'Ф', 'БИТ'])
			&& !CmpReasonRec.get('CmpReason_Code').inlist(['40У', '70?'])
		){
			me.checkDiagFinance(function(response){
				if(response.DiagFinance_IsOms == 2)
					PayTypeCombo.setValue(1)
			})
		}

	},


	// функция отображения и фокусов полей для перекрестков
	// changeFocus - ставить фокус или нет
	checkCrossRoadsFields: function(changeFocus, e) {

		if(e && (e.getCharCode() == e.SHIFT)){return false;}

		var baseForm = this.FormPanel.getForm(),
			cmpCallCard_Dom = baseForm.findField('House'),
			secondStreetCombo = baseForm.findField('CmpCloseCard_UlicSecond'),
			addressBlock = this.FormPanel.find('refId', 'addressBlock'),
			addressBlockItems = (addressBlock && addressBlock[0]) ? addressBlock[0].items.items : [],
			crossRoadsMode = ((cmpCallCard_Dom.getValue() == '/' && !secondStreetCombo.isVisible()) || (secondStreetCombo.getRawValue() && secondStreetCombo.isVisible())),
			disableInCrossRoadsMode = ['Korpus', 'Office', 'Room', 'Entrance', 'Level', 'CodeEntrance'];

		//начали вводить улицу - слэш удалили
		if(secondStreetCombo.getRawValue()) cmpCallCard_Dom.reset();

		//проверка на существующий режим
		if((crossRoadsMode && secondStreetCombo.isVisible()) || (!crossRoadsMode && !secondStreetCombo.isVisible())) return;

		for(var i=0;i<addressBlockItems.length; i++){

			var addressField = addressBlockItems[i],
				addressFieldName = addressField.getName();

			if(addressFieldName && addressFieldName.inlist(disableInCrossRoadsMode)){
				addressField.setDisabled(crossRoadsMode);

				if(crossRoadsMode){
					addressField.reset();
				}
			}
		}

		if(crossRoadsMode){
			secondStreetCombo.showContainer();
			cmpCallCard_Dom.hideContainer();
		}
		else{
			secondStreetCombo.hideContainer();
			cmpCallCard_Dom.showContainer();
		}

		if(changeFocus){
			if(crossRoadsMode){
				cmpCallCard_Dom.reset();
				secondStreetCombo.focus();
			}
			else{
				cmpCallCard_Dom.reset();
				cmpCallCard_Dom.focus();
			}
		}
	},
	checkIsCallControllFlag: function(){
		var me = this,
			isControlCall = me.FormPanel.getForm().findField('CmpCallCard_isControlCall');
		Ext.Ajax.request({
			url: '/?c=CmpCallCard&m=getIsCallControllFlag',
			success: function (response){
				var responseObj = Ext.util.JSON.decode(response.responseText);

				if(responseObj.length > 0){
					responseObj = responseObj[0];
				}

				if(isControlCall){
					isControlCall.setVisible(responseObj.SmpUnitParam_IsCallControll == 'true');
				}



			}

		});
	},

	clearCmpFields: function (cmp) {
		var els = cmp.find();

		els.forEach(function (el, index, array) {
			if (el.reset && el.getValue) {
				el.reset()
			}
		});
	}
});