/**
* swCmpCallCardNewCloseCardWindow Наследник карты закрытия вызова
* Спецификация Уфы
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @autor		Dyomin Dmitry
* @copyright    Copyright (c) 2012 Swan Ltd.
* @version      21.01.2013
*/

sw.Promed.swCmpCallCardNewCloseCardWindow = Ext.extend(sw.Promed.swMainCloseCardWindow,{
	objectName: 'swCmpCallCardNewCloseCardWindow',
	cls: 'swCmpCallCardNewCloseCardWindow',

	initComponent: function(){
		sw.Promed.swCmpCallCardNewCloseCardWindow.superclass.initComponent.apply(this, arguments);
	},

	//Коды диагнозов, при которых активизируется вкладка ОКС:
	isOKS: ['I20.0','I21.0','I21.1','I21.2','I21.3','I21.4','I21.9','I22.0','I22.1','I22.8','I22.9'],

	argAction: '',

	show: function(){

		sw.Promed.swCmpCallCardNewCloseCardWindow.superclass.show.apply(this, arguments);
		
		var me = this;

		if ( arguments[0] && arguments[0].action ) {
			me.argAction = arguments[0].action;
		}

		me.tabPanel.setActiveTab(0); //всегда начинаем с первой вкладки
		
		var tabOKS = Ext.getCmp('tabOKC');
		tabOKS.setDisabled(true);
		
		var base_form = this.FormPanel.getForm();
		base_form.findField('Diag_id').reset();
		
		var diagIdCombo = me.FormPanel.getForm().findField('Diag_id');
		diagIdCombo.getStore().on('load', 
			function(){
				//запуск события для подгрузки значений вкладки ОКС
				if ( me.argAction == 'edit' ) {

					// сброс таймера
					var DiagOKS_code = diagIdCombo.getStore().getAt(0).get('Diag_Code');
					if (DiagOKS_code.inlist(me.isOKS)){
						document.getElementById('timerOks').innerHTML = '00:00:00';
						document.getElementById('timerOks').style.color = 'red';
					}
				}
				if ( diagIdCombo.getValue() ) {
					setTimeout(function(){
						diagIdCombo.fireEvent('checkOKS', diagIdCombo);
					}, 1000);
				}
			}
		);

		Ext.getCmp('absoluteTLT_grid').getGrid().getStore().load({
			params : {
				BSKObservRecomendationType_id : 3,
				Person_id: base_form.findField('Person_id').getValue()                                                                  
			},
			callback : function(){
				//Ext.getCmp('swCmpCallCardNewCloseCardWindow').hideLoadMask();
			}
		});

		Ext.getCmp('relativeTLT_grid').getGrid().getStore().load({
			params : {
				BSKObservRecomendationType_id : 4,
				Person_id: base_form.findField('Person_id').getValue()                                                                  
			},
			callback : function(){
				//Ext.getCmp('swCmpCallCardNewCloseCardWindow').hideLoadMask();
			}
		});
		
		//if(me.UslugaPanel) me.loadUslugaPanelData();
	},

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
				text: 'Сохранить'
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
                text: 'Сохранить и Распечатать'
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
			defaults: {	border: false },
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
					items: me.getJalobFields(),
					autoHeight: true
				},
				{
					title: '<b>4.</b> Диагноз',
					items: me.getDiagnozFields(),
					autoHeight: true
				},
				{
					title: '<b>5.</b> Манипуляции',
					items: me.getProcedureFields(),
					autoHeight: true
				},
				{
					title: '<b>6.</b> Результат',
					items: me.getResultFields(),
					autoHeight: true
				},
				{
					title: '<b>7.</b> ОКС',
					items: me.getOKSFields(),
					disabled: true,
					id: 'tabOKC',
					autoHeight: true,
					listeners: {
						enable: function() {
							//Заполнение ранее сохраненной карты карты вызова из БД

							var cmpCallCardValId = me.FormPanel.getForm().findField('CmpCallCard_id').getValue();

							if (!cmpCallCardValId) return false;

							Ext.Ajax.request ({
								url: '/?c=BSK_Register_User&m=getOKSdata',
								params: {
									CmpCallCard_id: cmpCallCardValId
								},
								callback: function(opt, success, response) {

									var baseForm = me.FormPanel.getForm(),
										EvnEKG_setDate = baseForm.findField('EvnEKG_setDate'),
										EvnEKG_setTime = baseForm.findField('EvnEKG_setTime'),
										TLT_setDate = baseForm.findField('TLT_setDate'),
										TLT_setTime = baseForm.findField('TLT_setTime'),
										TLTres = baseForm.findField('TLTres'),
										TLTreason = baseForm.findField('TLTreason'),
										TLTreason_f = Ext.getCmp('TLTreason_f'),
										TLTres_f = Ext.getCmp('TLTres_f'),
										pain_setDate = baseForm.findField('pain_setDate'),
										pain_setTime = baseForm.findField('pain_setTime'),
										Lpu = baseForm.findField('Lpu'),
										paramedic = baseForm.findField('paramedic'),
										EvnEKG_rezEKG = baseForm.findField('EvnEKG_rezEKG');

									if (success) {
										//лочим если не пустые
										var setDisabled = function() {
											EvnEKG_setDate.setDisabled( EvnEKG_setDate.getValue() || false );
											EvnEKG_setTime.setDisabled( EvnEKG_setTime.getValue() || false );
											EvnEKG_rezEKG.setDisabled( EvnEKG_rezEKG.getValue() || false);
											TLT_setDate.setDisabled( TLT_setDate.getValue() || false );
											TLT_setTime.setDisabled( TLT_setTime.getValue() || false );
											TLTres.setDisabled( TLT_setDate.getValue() || TLTreason.getValue() || false );
											TLTreason.setDisabled( TLTreason.getValue() || false );
											pain_setDate.setDisabled( pain_setDate.getValue() || false );
											pain_setTime.setDisabled( pain_setTime.getValue() || false );
											Lpu.setDisabled( Lpu.getValue() || false );
											paramedic.setDisabled( paramedic.getValue() || false );
										};
										
										var data = Ext.util.JSON.decode(response.responseText);
										if (data.length >0 ) {

											var AbsoluteTLT_grid = Ext.getCmp('absoluteTLT_grid').getGrid();
											var RelativeTLT_grid = Ext.getCmp('relativeTLT_grid').getGrid();
											var TLTDT = true;

											var changeColorRec = function(grid, index){
												grid.getSelectionModel().selectRow(index, true);
												var rec = grid.getStore().getAt(index);
												if(rec){
													rec.set('BSKObservRecomendation_text', '<span style="color:red!important">'+rec.get('BSKObservRecomendation_text')+'<span>');
													rec.commit();
												}
												
											};

											var k0abs = -1,//начало абсолютных противопоказаний
												k0rel = -1,//начало относительных противопоказаний
												absolutTltFlag = false, //лочим чекбоксы если не пустой раздел
												relativeTltFlag = false;
											AbsoluteTLT_grid.getSelectionModel().unlock();
											RelativeTLT_grid.getSelectionModel().unlock();
											AbsoluteTLT_grid.getSelectionModel().clearSelections();
											RelativeTLT_grid.getSelectionModel().clearSelections();
											for (var k in data)  {
												if (typeof data[k] == 'object') {
													if (data[k].formName == 'AbsoluteList') {
														absolutTltFlag = true;
														if (k0abs < 0) k0abs = k;//запоминаем начальный индекс
														if(data[k].BSKRegistryData_data.inlist(['Да', 'Да.'])){

															var rowIndex = k - k0abs;

															AbsoluteTLT_grid.getSelectionModel().selectRow(rowIndex, true);

															if(data[k].checked == 1 && data[k].isDoctor == 1){
																changeColorRec(AbsoluteTLT_grid, rowIndex);
															}
														}
													}
													else if(data[k].formName == 'RelativeList') {
														relativeTltFlag = true;
														if (k0rel < 0) k0rel = k;//запоминаем начальный индекс
														if(data[k].BSKRegistryData_data.inlist(['Да', 'Да.'])){
															var rowIndex = k - k0rel;

															RelativeTLT_grid.getSelectionModel().selectRow(rowIndex, true);

															if(data[k].checked == 1 && data[k].isDoctor == 1){
																changeColorRec(RelativeTLT_grid, rowIndex);
															}

														}
													}
													else if(data[k].formName == 'ECGDT') {
														var EvnEKG_m = data[k].BSKRegistryData_data.split(' ');
														EvnEKG_setDate.setValue(EvnEKG_m[0]);
														EvnEKG_setTime.setValue(EvnEKG_m[1]);
													}
													else if(data[k].formName == 'ResultECG') {
														EvnEKG_rezEKG.setValue(data[k].BSKRegistryData_data);
													}
													else if(data[k].formName == 'TLTDT') {
														if (data[k].BSKRegistryData_data == 'нет' || !data[k].BSKRegistryData_data) {
															TLTres.setValue('Не выполнено');
															TLT_setDate.hide();
															TLT_setTime.hide();
															TLTreason_f.show();
															TLTres_f.getEl().dom.setAttribute('style', 'margin-left:-215px');
															var TLTDT = false;
														}
														else {
															TLTres.setValue('Выполнено');
															TLT_setDate.show();
															TLT_setTime.show();
															TLTreason_f.hide();
															TLTres_f.getEl().dom.setAttribute('style', 'margin-left:0px');
															var TLTDT = true;
															var TLT_set_m = data[k].BSKRegistryData_data.split(' ');
															TLT_setDate.setValue(TLT_set_m[0]);
															TLT_setTime.setValue(TLT_set_m[1]);
														}
													}
													else if(data[k].formName == 'FailTLT') {
														if (!TLTDT) {
															TLTreason.setValue(data[k].BSKRegistryData_data);
														}
													}
													else if(data[k].formName == 'PainDT') {
														var PainDT_m = data[k].BSKRegistryData_data.split(' ');
														pain_setDate.setValue(PainDT_m[0]);
														pain_setTime.setValue(PainDT_m[1]);
													}
													else if(data[k].formName == 'MOHospital') {
														Lpu.setValue(data[k].BSKRegistryData_data);
													}
													else if(data[k].formName == 'MedStaffFact_num') {
														paramedic.setValue(data[k].BSKRegistryData_data);
													}
												}
											}

											//Блокируем доступ к содержимому полей раздела 7.ОКС, т.к. эти данные грузятся из БД
											if(absolutTltFlag)
												AbsoluteTLT_grid.getSelectionModel().lock();
											if(relativeTltFlag)
												RelativeTLT_grid.getSelectionModel().lock();
											setDisabled();
										}
										else
										{
											var AbsoluteTLT_grid = Ext.getCmp('absoluteTLT_grid').getGrid();
											var RelativeTLT_grid = Ext.getCmp('relativeTLT_grid').getGrid();
											AbsoluteTLT_grid.getSelectionModel().unlock();
											RelativeTLT_grid.getSelectionModel().unlock();
											AbsoluteTLT_grid.getSelectionModel().clearSelections();
											RelativeTLT_grid.getSelectionModel().clearSelections();
											setDisabled();
										}
										me.getResponsibilityMOZone();
									}
									else {
										Ext.Msg.alert('Ajax.request', 'Запрос карты ОКС неуспешен');
									}
								}
							});
						},
                        activate: function(p) {

							var cmpCallCardValId = me.FormPanel.getForm().findField('CmpCallCard_id').getValue();

							if (!cmpCallCardValId) return false;

                            Ext.Ajax.request ({
                                url: '/?c=BSK_Register_User&m=getPerson_id',
                                params: {
                                    CmpCallCard_id: cmpCallCardValId
                                },
                                callback: function(opt, success, response) {
                                    if (success) {
                                        var data = Ext.util.JSON.decode(response.responseText);
                                        //Ext.getCmp('swCmpCallCardNewCloseCardWindow').Person_id = data[0].Person_id;
                                    }
                                    else {
                                        Ext.Msg.alert('Ajax.request', 'Запрос getPerson_id неуспешен');
                                    }
                                }
                            }
                            );
                        }
                    }
				},
				{
					title: '<b>8.</b> Использование медикаментов',
					items: [
						{
							html: 'Для ввода медикаментов необходимо заполнить поле "Станция (подстанция), отделение"',
							style: 'margin-bottom: 30px; text-align: center; font-size: 16px;',
							height: 700
						}
					]
					// items: me.getDrugFields(),
				},
				{
					title: '<b>9.</b> Экспертная оценка',
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
				name: 'Person_IsUnknown',
				value: '',
				xtype: 'hidden'
			},
			{
				name: 'PersonFields_IsDirty',
				value: false,
				xtype: 'hidden'
			},
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
			url: '/?c=CmpCallCard&m=saveCmpCloseCard110',
			items: [
				{xtype: 'container', items: me.hiddenItems, autoEl: {}, hidden: true},
				me.tabPanel
			]
		});
		
		//if(me.UslugaPanel) me.initUslugaElements();
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
										allowBlank: false,
										regex: /\d/,
										validator: function(a){	return (a.match(/^[1-9]\d*$/))?true:false;}
									}, {
										fieldLabel: 'Номер вызова за год',
										name: 'Year_num',
										xtype: 'textfield',
										allowBlank: false,
										regex: /\d/,
										validator: function(a){	return (a.match(/^[1-9]\d*$/))?true:false;}
									}
								]
							},
							{
								border: false,
								layout: 'form',
								width: 400,
								labelWidth: 100,
								items: [
									{
										xtype: 'swpaytypecombo',
										allowBlank: false,
										disabledClass: 'field-disabled',
										loadParams: {params: {where: ' where PayType_Code in(1,2,4,5)'}},
										checkAllowLinkedFields: function(payType_Code){
											var base_form = me.FormPanel.getForm(),
												diagField = base_form.findField('Diag_id');

											//если полис не ОМС то делаем поле результат выезда необязательным
											//иначе - обязательный
											diagField.allowBlank = !payType_Code.inlist([1]);
											diagField.validate();

										},
										listeners: {
											change: function(cmp, newVal){
												cmp.checkAllowLinkedFields(this.store.getById(newVal).get('PayType_Code'));
											},
											select: function(cmp, rec, ind){
												cmp.checkAllowLinkedFields(rec.get('PayType_Code'));
											}
										}
									},
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
									me.loadEmergencyTeamsWorkedInATime();

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
									//OKS
									if (me.FormPanel.getForm().findField('ArriveTime') && me.istimeOKS == true) {
										if (me.tltInterval > 0) {
											clearInterval(me.tltInterval); 
											me.tltInterval = setInterval(function() {me.timerTLT()},1000);
										}
										else {
											me.tltInterval = setInterval(function() {me.timerTLT()},1000);
										}

									} 
									else {
										if (me.tltInterval > 0) {
											clearInterval(me.tltInterval);  
										}                                                        
									}
									//
								}.createDelegate(this),
								xtype: 'swdatetimefield'
							},
							{
								dateLabel: 'Начала транспортировки больного',
								hiddenName: 'TransportTime',
								hiddenId: this.getId() + '-TransportTime',
								onChange: function(field, newValue){
									var base_form = me.FormPanel.getForm();

									//base_form.findField(me.getId() + '-EndTime').setValue(newValue);

									me.calcSummTime();
								},
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
								dateLabel: 'Отзвона',
								hiddenName: 'CmpCloseCard_PassTime',
								hiddenId: this.getId() + '-CmpCloseCard_PassTime',
								onChange: function(field, newValue){
									me.calcSummTime();
								}.createDelegate(this),
								xtype: 'swdatetimefield'
							},
							{
								dateLabel: 'Возвращения на подстанцию',
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
									beforeselect: function (combo,record,index) {
										var base_form = me.FormPanel.getForm();
										// форма расхода медикаментов должна зависеть от настроек подразделения, которое выбрано в Карте вызова
										var idLpuBuilding = combo.getValue();
										var newLpuBuilding = record.get("LpuBuilding_id");
										var LBIsWithoutBalance = base_form.findField('LpuBuilding_IsWithoutBalance').getValue();
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
																		msg: lang['izmenenie_podstancii_udalit_medicamenty'],
																		title: lang['podtverjdenie'],
																		buttons: Ext.Msg.YESNO,
																		fn: function (buttonId, text, obj) {
																			var dataDrug = new Array();
																			if ('yes' == buttonId) {
																				if(me.action == 'stream'){
																					store.each(function (rec) {
																						grid.getStore().remove(rec);
																					});
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
									allowBlank: false,
									width: 350,
									listWidth: 350,
									store: new Ext.data.Store({
										//autoLoad: true,
										url: '/?c=EmergencyTeam&m=loadEmergencyTeamCCC',
										reader: new Ext.data.JsonReader({
											id: 'EmergencyTeam_id'
										},[
											{name: 'EmergencyTeam_id'},
											{name: 'EmergencyTeam_Num'},
											{name: 'EmergencyTeamSpec_Name'},
											{name: 'EmergencyTeamSpec_Code'},
											{name: 'MedPersonal_id'},
											{name: 'MedStaffFact_id'},
											{name: 'EmergencyTeam_HeadShiftFIO'},
											{name: 'LpuBuilding_id'},
											{name: 'Person_Fin'}
										])
									}),
									listeners: {
										select: function(combo,record,index){
											var base_form = me.FormPanel.getForm(),
												EmergencyTeamNum = base_form.findField('EmergencyTeamNum');
											console.log('record=', record)
											if(EmergencyTeamNum) EmergencyTeamNum.setValue(record.get('EmergencyTeam_Num'));
											base_form.findField('MedPersonal_id').setValue(record.get('MedPersonal_id'));
											base_form.findField('MedStaffFact_id').setValue(record.get('MedStaffFact_id'));
										}.createDelegate(this)
									},
									getHeadShift: function() {
										var combo = this;
										var row_index = this.store.findBy(function(rec) { return rec.get('EmergencyTeam_id') == combo.getValue() });
										var rec = this.store.getAt(row_index);
										if(rec) return rec.get('EmergencyTeam_Num') + ' ' + rec.get('Person_Fin');
									}
								}]
							},
							{
								xtype: 'hidden',
								name: 'EmergencyTeamNum'
							},{
								name: 'LpuBuilding_IsWithoutBalance',
								xtype: 'hidden',
								hiddenId: 'LpuBuilding_IsWithoutBalance'
							},
							{
								xtype: 'hidden',
								name: 'MedStaffFact_id'
							},
							{
								xtype: 'hidden',
								name: 'MedPersonal_id'
							},
							{
								text: 'Выбрать',
								xtype: 'button',
								id: 'BrigSelectBtn',
								handler: function() {
									var parentObject = this;
                                    var base_form = parentObject.FormPanel.getForm();

                                    getWnd('swSelectEmergencyTeamWindow').show({
                                        AcceptTime: Ext.util.Format.date(new Date(base_form.findField('AcceptTime').getValue()), 'd.m.Y H:i:s'),
										//CmpCallCard: parentObject.FormPanel.getForm().findField('CmpCallCard_id').getValue(),
										callback: function(data) {
											//var base_form = parentObject.FormPanel.getForm();
											base_form.findField('EmergencyTeamNum').setValue(data.EmergencyTeam_Num);
											//parentObject.setEmergencyTeam(base_form.findField('CmpCallCard_id').getValue(), data.EmergencyTeam_id);
											base_form.findField('EmergencyTeam_id').getStore().load({
												callback: function() {
													//this.setValue(this.getValue());
													base_form.findField('EmergencyTeam_id').setValue(data.EmergencyTeam_id);
													//this.fireEvent('beforeselect', this, this.getStore().getAt(this.getStore().findBy(function(rec) { return rec.get('SubRGN_id') == this.getValue(); }.createDelegate(this))));
												}.createDelegate(this)
											});
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
											base_form.findField('CmpCloseCard_UlicSecond').reset();
											base_form.findField('CmpCloseCard_UlicSecond').getStore().removeAll();
											
											if( record.get('KLRGN_id') ){
												base_form.findField('KLRgn_id').setValue(record.get('KLRGN_id'));
											}

											if( record.get('KLSubRGN_id') != '' ) {

												base_form.findField('StreetAndUnformalizedAddressDirectory_id').getStore().load({
													params: {
														town_id: record.get('SubRGN_id')
													},
													callback: function(recs){
														//@todo loadData загружает пустые записи потом исправить
														base_form.findField('CmpCloseCard_UlicSecond').getStore().data = base_form.findField('StreetAndUnformalizedAddressDirectory_id').getStore().data;
													}
												});

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
										base_form.findField('CmpCloseCard_UlicSecond').enable();
										base_form.findField('CmpCloseCard_UlicSecond').reset();
										base_form.findField('CmpCloseCard_UlicSecond').getStore().removeAll();
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
												base_form.findField('CmpCloseCard_UlicSecond').getStore().removeAll();

												base_form.findField('StreetAndUnformalizedAddressDirectory_id').getStore().load({
													params: {
														town_id: record.get('SubRGN_id')
													},
													callback: function(recs){
														//@todo loadData загружает пустые записи потом исправить
														base_form.findField('CmpCloseCard_UlicSecond').getStore().data = base_form.findField('StreetAndUnformalizedAddressDirectory_id').getStore().data;
											}
												});
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
													streetField = base_form.findField('StreetAndUnformalizedAddressDirectory_id'),
													secondStreetField = base_form.findField('CmpCloseCard_UlicSecond');

												townField.getStore().removeAll();
												townField.getStore().load({params: {city_id: record.get('City_id')}});
												streetField.getStore().removeAll();
												secondStreetField.getStore().removeAll();

												if(record.get('City_id')){
													streetField.getStore().load({
														params: {
															town_id: record.get('City_id'),
															showSocr: 1
														},
														callback: function(recs){
															//@todo loadData загружает пустые записи потом исправить
															secondStreetField.getStore().data = streetField.getStore().data;
											}
													});
												};
											}
										}.createDelegate(this)
									}
								}, 
								{
									disabled: false,
									lastQuery: '',
									fieldLabel: 'Район города',
									hidden: true,
									hiddenName: 'PersonSprTerrDop_idEdit',
									hideLabel: true,
									listWidth: 400,
									width: 200,
									xtype: 'swpersonsprterrdop'
                                },
								{
									disabled: false,
									enableKeyEvents: true,
									//allowBlank: false,
									listeners: {
										beforeselect: function(combo, record) {
											var base_form = this.FormPanel.getForm(),
												cityField = base_form.findField('City_id'),
												streetField = base_form.findField('StreetAndUnformalizedAddressDirectory_id'),
												secondStreetField = base_form.findField('CmpCloseCard_UlicSecond');
											
											combo.setValue(record.get(combo.valueField));

											streetField.clearValue();
											streetField.getStore().removeAll();
											secondStreetField.clearValue();
											secondStreetField.getStore().removeAll();

											if(combo.getValue()){
												streetField.getStore().load({
													params: {
														town_id: record.get('Town_id')
													},
													callback: function(recs){
														//@todo loadData загружает пустые записи потом исправить
														secondStreetField.getStore().data = streetField.getStore().data;
													}
												});
											};

										}.createDelegate(this)
									},
									minChars: 0,
									hiddenName: 'Town_id',
									name: 'Town_id',
									width: 250,
									xtype: 'swtowncombo',
									onTrigger2Click: function() {
										me.showTownSearchWindow();
									}
								}, 
								/*
								{
									disabled: false,
									xtype: 'swstreetcombo',
									fieldLabel: 'Улица',
									hiddenName: 'Street_id',
									name: 'Street_id',									
									width: 250,
									editable: true
								},
								*/
								{
									xtype: 'swstreetandunformalizedaddresscombo',
									fieldLabel: lang['ulitsa_object'],
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
								items :[{
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
								}, {
									border: false,
									layout: 'form',
									items : [{
										fieldLabel: 'Фамилия',
										disabled: false,
										name: 'Fam',
										width: 180,
										toUpperCase: true,
										xtype: 'textfieldpmw',
										allowBlank: false
									}, {
										fieldLabel: 'Имя',
										disabled: false,
										name: 'Name',
										toUpperCase: true,
										width: 180,
										xtype: 'textfieldpmw',
										allowBlank: false
									}, {
										fieldLabel: 'Отчество',
										disabled: false,
										name: 'Middle',
										toUpperCase: true,
										width: 180,
										xtype: 'textfieldpmw'
									}
									]
								}]
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
									validator: function(a){	return (a.match(/^[1-9]\d*$/))?true:false;},
									listeners: {
										change: function() {
											this.setMKB();
										}.createDelegate(this)
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
								disabled: false,
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
							},
							{
								fieldLabel: 'Серия полиса',
								disabled: false,
								name: 'Person_PolisSer',
								width: 180,
								xtype: 'textfield',
								editable: false,
							},
							{
								fieldLabel: 'Номер полиса',
								disabled: false,
								name: 'Person_PolisNum',
								width: 180,
								xtype: 'textfield',
								editable: false,
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
								fieldLabel: lang['lpu_peredachi'],
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
							},*/
							{
								disabledClass: 'field-disabled',
								fieldLabel: lang['dopolnitelnaya_informatsiya_utochnennyiy_adres'],
								toUpperCase: true,
								height: 100,
								name: 'CmpCloseCard_DopInfo',
								width: 350,
								xtype: 'textarea'
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
								}
							]
					},
					/*{
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
							}
						]
					},*/
					{
						xtype      : 'fieldset',
						autoHeight: true,
						frame	   : true,
						//width: '100%',
						items      : [{
								columns: 1,
								vertical: true,
								width: '100%',
								xtype: 'radiogroup',
								fieldLabel: ++me.panelNumber + '. Пациент',
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
								items: this.getCombo('PersonSocial_id')
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
					/*{
						comboSubject: 'CmpReason',
						disabledClass: 'field-disabled',
						fieldLabel: 'Повод талона вызова',
						hiddenName: 'CallPovod_id',
						id: 'idCallPovod_id',
						width: 350,
						listWidth: 300,
						editable: true,
						xtype: 'swcommonsprcombo'
					},*/
					{
						comboSubject: 'CmpReason',
						disabledClass: 'field-disabled',
						fieldLabel: 'Повод',
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
                        fieldLabel: 'Причина обращения',
						comboSubject: 'CmpReasonNew',
						disabledClass: 'field-disabled',
                        allowBlank: false,
						hiddenName: 'CallPovodNew_id',
						width: 350,
                        listWidth: 350,
						autoLoad: true,
						editable: true,
						xtype: 'swcustomobjectcombo'
                    },
				]
			},
			{
				xtype      : 'fieldset',
				autoHeight: true,
				frame	   : true,
				items      : [{
						fieldLabel	   : ++me.panelNumber + '. Кратность вызова',
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
					fieldLabel: 'Исполнение',
					width: '100%',
					xtype: 'checkboxgroup',
					singleValue: true,	
					items: this.getCombo('Ispolnenie_id')
				}]
			},
			{
				xtype      : 'fieldset',
				autoHeight: true,
				frame	   : true,
				items      : [{
						columns: 1,
						vertical: true,
						fieldLabel: 'Передан бригаде',
						width: '100%',
						xtype: 'checkboxgroup',
						singleValue: true,	
						items: this.getCombo('Peredan_id')
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
								fields: [{name: 'CmpCallPlaceType_id'}, {name: 'CmpCallPlaceType_Name'}, {name: 'is_checked'}]
							}),
						cbRenderer:function(){},
						cbHandler:function(){},
						items:[{boxLabel:'Loading'},{boxLabel:'Loading'}],
						fieldId: 'CmpCallPlaceType_id',
						fieldName: 'CmpCallPlaceType_Name',
						boxLabel: 'CmpCallPlaceType_Name',
						fieldValue: 'CmpCallPlaceType_id',
						fieldChecked: 'is_checked'
					})
				]
			},
			{
				xtype      : 'fieldset',
				autoHeight: true,

				frame	   : true,
				items      : [{
						columns: 2,
						vertical: true,
						fieldLabel	   : 'Обстоятельства травмы',
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
						columns: 1,
						vertical: true,
						fieldLabel	   : 'Полиция на месте вызова',
						width: '100%',
						xtype: 'checkboxgroup',
						singleValue: true,	
						items: this.getCombo('Cop_id')
				}]
			}, {
				xtype      : 'fieldset',
				autoHeight: true,
				frame	   : true,
				items      : [{
						columns: 1,
						vertical: true,
						fieldLabel	   : 'В РУВД сообщено',
						width: '100%',
						xtype: 'checkboxgroup',
						singleValue: true,	
						items: this.getCombo('Ruvd_id')
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
				labelWidth : 200,
				items      : [{
					dateLabel: 'Время ухудшения состояния',
					hiddenName: 'Bad_DT',
					xtype: 'swdatetimefield',
					dateFieldWidth: 100,
					timeLabelWidth: 60,
					dateLabelWidth: 100,
					dateLabelWidth1: 350,
					timeLabelWidth1: 200,
					maxValue: new Date()
				}]
			},
			{
				xtype      : 'fieldset',
				autoHeight: true,
				labelWidth : 200,
				items      : [{
					dateLabel: 'Дата последних mensis',
					hiddenName: 'Mensis_DT',
					xtype: 'swdatetimefield',
					dateFieldWidth: 100,
					timeLabelWidth: 60,
					dateLabelWidth: 100,
					dateLabelWidth1: 350,
					timeLabelWidth1: 200,
					maxValue: new Date()
				}]
			},
			{
				xtype      : 'fieldset',
				autoHeight: true,
				frame	   : true,
				labelWidth: 100,
				items      : [{
						fieldLabel: 'Аллергия',
						name: 'Alerg',
						width: '90%',
						xtype: 'textfield'
				}]
			},
			{
				xtype      : 'fieldset',
				autoHeight: true,
				frame	   : true,
				labelWidth: 100,
				items      : [{
						fieldLabel: 'Эпид. анамнез',
						name: 'Epid',
						width: '90%',
						xtype: 'textarea'
				}]
			},
			{
				xtype      : 'fieldset',
				autoHeight: true,
				title	   : '',
				frame	   : true,
				items      : [
					{
					layout	   : 'column',
					items: [{
						xtype      : 'panel',
						title	   : 'Выезд за пределы РФ',
						frame	   : true,
						width : '20%',
						height : 130,
						items : [{
								columns: 1,
								vertical: true,
								width: '100%',
								xtype: 'radiogroup',
								items: this.getCombo('FromRf_id')
						}]
					}, {
						xtype      : 'panel',
						title	   : 'Выезд в сельскую местность',
						frame	   : true,
						width : '20%',
						height : 130,
						items : [{
								columns: 1,
								vertical: true,
								width: '100%',
								xtype: 'radiogroup',
								items: this.getCombo('InSh_id')
						}]
					}, {
						xtype      : 'panel',
						title	   : 'Контакт с инф. больными, животными, грызунами',
						frame	   : true,
						width : '20%',
						height : 130,
						items : [{
								columns: 1,
								vertical: true,
								width: '100%',
								xtype: 'radiogroup',
								items: this.getCombo('Infect_id')
						}]
					}, {
						xtype      : 'panel',
						title	   : 'Вакцинация от гриппа',
						frame	   : true,
						width : '20%',
						height : 130,
						items : [{
								fieldLabel: 'Вакцинация от гриппа',
								hiddenName: 'isVac',
								width: 40,
								comboSubject: 'YesNo',
								xtype: 'swcommonsprcombo'
						}]
					}, {
						xtype      : 'panel',
						title	   : 'Употребление некачественных продуктов',
						frame	   : true,
						width : '20%',
						height : 130,
						items : [{
								columns: 1,
								vertical: true,
								width: '100%',
								xtype: 'radiogroup',
								items: this.getCombo('Product_id')
						}]
					}]
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
							title	   : 'Положение',
							frame	   : true,
							width : '25%',
							height : 200,
							items : [{
									columns: 1,
									vertical: true,
									width: '100%',
									xtype: 'radiogroup',
									items: this.getCombo('Polozenie_id')
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
							title	   : 'Большой родничок напряжен',
							frame	   : true,
							width : '25%',
							height : 200,
							items : [{
									columns: 2,
									vertical: true,
									width: '100%',
									xtype: 'radiogroup',
									items: this.getCombo('Rodn_id')
							}]
						}, {
							xtype      : 'panel',
							title	   : 'Корнеальные рефлексы',
							frame	   : true,
							width : '25%',
							height : 200,
							items : [{
									columns: 2,
									vertical: true,
									width: '100%',
									xtype: 'radiogroup',
									items: this.getCombo('Korneal_id')
							}]
						}, {
							xtype      : 'panel',
							title	   : 'С-м Белоглазова',
							frame	   : true,
							width : '25%',
							height : 200,
							items : [{
									columns: 2,
									vertical: true,
									width: '100%',
									xtype: 'radiogroup',
									items: this.getCombo('Beloglaz_id')
							}]
						}, {
							xtype      : 'panel',
							title	   : 'Видимые повреждения',
							frame	   : true,
							width : '25%',
							height : 200,
							items : [{
									columns: 1,
									vertical: true,
									width: '100%',
									xtype: 'radiogroup',
									items: this.getCombo('Damage_id')
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
							title	   : 'Зев',
							frame	   : true,
							width : '25%',
							height : 200,
							items : [{
									fieldLabel: 'Зев',
									name: 'Zev',
									xtype: 'textfield',
									width: 250
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
							title	   : 'Перкуторно',
							frame	   : true,
							width : '25%',
							height : 200,
							items : [{
									fieldLabel: 'Перкуторно',
									name: 'Perk',
									xtype: 'textfield',
									width: 250
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
									//style: 'padding: 0 10px;'
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
					},	{
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
					},
					/*{
							fieldLabel: 'АД, мм.рт.ст.',
							name: 'AD',
							xtype: 'textfield'
					},*/ {
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
			label: lang['soputstvuyuschiy_diagnoz'],
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
				text: lang['dobavit']
			}, {
				text: '-'
			}],
			createField: function (counter) {
				var conf_combo = {
					value: null,
					fieldLabel: lang['soputstvuyuschiy_diagnoz'],
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
			label: lang['oslojnenie_osnovnogo'],
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
				text: lang['dobavit']
			}, {
				text: '-'
			}],
			createField: function (counter) {
				var conf_combo = {
					value: null,
					fieldLabel: lang['oslojnenie_osnovnogo'],
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
									labelStyle: 'width: 50px;',
									width: 274,
								xtype: 'swdiagcombo',
								allowBlank: false,
								disabledClass: 'field-disabled',
								MKB: {
									isMain: true
								},
								withGroups: false,
								listeners: {
										select: function(combo, record, index){
											var tabOKS = Ext.getCmp('tabOKC');

										var DiagOKS_code = record.get('Diag_Code');

										if (DiagOKS_code.inlist(me.isOKS)){
											tabOKS.setDisabled(false);

											if(me.argAction !== 'edit') {
												if (me.FormPanel.getForm().findField('ArriveTime').getStringValue() > '') {
													me.tltInterval = setInterval(function() {me.timerTLT()},1000);
												}												
											}
											}
										else {
											tabOKS.setDisabled(true);
										}
									},
										blur: function() {
											rec = this.getStore().getById( this.getValue() );
											if(!rec) return;
											var DiagOKS_code = rec.get('Diag_Code');
											log(rec);
											if(DiagOKS_code.inlist(me.isOKS)) {
												Ext.Msg.alert('Проверка введенного диагноза','При данном диагнозе необходимо обязательно заполнить раздел 7.ОКС');
											}
										},
										checkOKS: function(combo) {
										this.dqTask.cancel();
										combo.collapse();

										if ( combo.getRawValue() == '' ) {
											combo.setValue('');
											if ( this.onChange && typeof this.onChange == 'function' ) {
												this.onChange(this, '');
											}
										} else {
											var store = combo.getStore();
											// Получаем уже обработанный код
											var val = this.getDiagCode(this.getRawValue().toString().substr(0, this.countSymbolsCode));
											// Вместо load пробежимся по найденным записям и уставим выбранное значение
											this.getStore().each(function(r){
												if ( r.data.Diag_Code == val )
												{
													this.setValue(r.get(this.valueField));
														combo.fireEvent('select', combo, r, 0);
												}
											}.createDelegate(this));
										}
									}
								}
								},
								this.diag_sid_panel,
								this.diag_ooid_panel
							]
						}
					]
				}]
			},
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
			cls: 'uslugaPanel',
			defaults: {				
				bodyStyle:'padding:20px',
				labelWidth: 150
			},
			layoutConfig: {},
			items: []
		});
*/
		
		/*
        me.UslugaPanel = new sw.Promed.ViewFrame({
            object: 'CmpCallCardUsluga',
            dataUrl: '/?c=CmpCallCard&m=loadCmpCallCardUslugaGrid',
            height: 200,
            autoLoadData: false,
            border: true,
            useEmptyRecord: false,
            actions: [],
            stringfields: [
                {name: 'CmpCallCardUsluga_id', type: 'int', header: 'ID', key: true},
                {name: 'CmpCallCard_id', type: 'int', hidden: true},
                {name: 'UslugaComplex_id', type: 'int', hidden: true},
                {name: 'MedPersonal_id', type: 'int', hidden: true},
                {name: 'MedStaffFact_id', type: 'int', hidden: true},
                {name: 'Person_id', type: 'int', hidden: true},
                {name: 'PayType_id', type: 'int', hidden: true},
                {name: 'UslugaCategory_id', type: 'int', hidden: true},
                {name: 'UslugaComplexTariff_id', type: 'int', hidden: true},
                {name: 'CmpCallCardUsluga_setDate', type: 'string', header: 'Дата', width: 120},
                {name: 'CmpCallCardUsluga_setTime', type: 'string', header: 'Время', width: 120},
                {name: 'UslugaComplex_Code', type: 'string', header: 'Код', width: 160},
                {name: 'UslugaComplex_Name', type: 'string', header: 'Наименовение', id: 'autoexpand'},
                {name: 'CmpCallCardUsluga_Cost', type: 'int', header: 'Цена'},
                {name: 'CmpCallCardUsluga_Kolvo', type: 'int', header: 'Количество'},
                {name: 'status', type: 'string', hidden: true}

            ]
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
						name: 'HelpAuto',
						width: '99%',
						xtype: 'textarea'
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
										validator: function(a){	return (a.match(/^[1-9]\d*$/))?true:false;},
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
									validator: function(a){	return (a.match(/^[1-9]\d*$/))?true:false;},
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
										validator: function(a){	return (a.match(/^[1-9]\d*$/))?true:false;}
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
										validator: function(a){	return (a.match(/^[1-9]\d*$/))?true:false;},
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
										validator: function(a){	return (a.match(/^[1-9]\d*$/))?true:false;},
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
										validator: function(a){	return (a.match(/^[1-9]\d*$/))?true:false;},
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
							},
							{
								xtype      : 'fieldset',
								autoHeight: true,
								labelWidth: 100,
								border: false,
								width: 300,
								items : [{
									fieldLabel: 'Купированы боли/приступ',
									hiddenName: 'isKupir',
									width: 40,
									comboSubject: 'YesNo',
									xtype: 'swcommonsprcombo'
								}]
							}
						]
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
				//id: 'CCCNCC_SMPUslugaPanel',
				layout: 'form',
				style: 'margin-bottom: 0.5em;',
				title: ++me.panelNumber + '. Услуги',
				items: [me.UslugaPanel]
			}
			*/
		];
	},

	//получение списка компонентов вкладки ОКС
	getOKSFields: function(){
		var me = this;

		return [
			{
				layout: 'column',
				frame: false,
				xtype: 'panel',
				autoHeight: true,
				id: 'oks',
				html: '<div style="float:left; width:79%; padding: 5px;">\n\
							<table  style="float:left; width:100%;" >\n\
								<tr>\n\
									<td style="font-size: 16px; font-weight: bold; width:25%; vertical-align: top; height: 28px;">Зона ответственности МО:</td>\n\
									<td id="mo" style="font-size: 12px; vertical-align: top;">не определена</td>\n\
								</tr>\n\
								<tr>\n\
									<td style="font-size: 16px; font-weight: bold; vertical-align: top; height: 28px;">Зона ответственности ЧКВ:</td>\n\
									<td id="chkv" style="font-size: 12px; vertical-align: top;">не определена</td>\n\
								</tr>\n\
							</table>\n\
					   </div>\n\
					   <div style="float:right; width:19%; text-align: right; font-size: 14px; padding-top: 5px; padding-right:5px;">\n\
							<div>Осталось для проведения ТЛТ</div>\n\
							<div style="font-size: 40px; font-weight: bold;" id="timerOks"> </div>\n\
					   </div>',
				items: [
					{
						xtype: 'tabpanel',
						activeTab: 0,
						deferredRender: false,
						items: [
							{
								title: 'Анамнез/ЭКГ',
								id: 'Anamnesis',
								items: [
									{
										border: false,
										style: 'margin-top:5px',
										layout: 'column',
										items: [
											{
												border: false,
												layout: 'form',
												width: 350,
												items: [{
														fieldLabel: 'Время начала болевых симптомов',
														format: 'd.m.Y',
														name: 'pain_setDate',
														id: 'pain_setDate',
														selectOnFocus: true,
														xtype: 'swdatefield',
														allowBlank: true
												}]
											},
											{
												border: false,
												layout: 'form',
												width: 90,
												items: [{
														fieldLabel: 'Время',
														hideLabel: true,
														allowBlank: true,
														plugins: [ new Ext.ux.InputTextMask('99:99', true) ],
														name: 'pain_setTime',
														id: 'pain_setTime',
														onTriggerClick: function() {
																var base_form = this.FormPanel.getForm();
																var time_field = base_form.findField('pain_setTime');
																if ( time_field.disabled ) {
																		return false;
																}
																setCurrentDateTime({
																		dateField: base_form.findField('pain_setDate'),
																		setDate: true,
																		setDateMaxValue: false,
																		setDateMinValue: false,
																		setTime: true,
																		timeField: time_field,
																		windowId: this.id
																});
														}.createDelegate(this),
														validateOnBlur: false,
														xtype: 'swtimefield'
												}]
										}]
									},
									{
										layout: 'column',
										style: 'margin-top:5px',
										items: [
											{
												border: false,
												layout: 'form',
												width: 350,
												items: [{
													fieldLabel: 'Результат ЭКГ',
													format: 'd.m.Y',
													name: 'EvnEKG_setDate',
													id: 'EvnEKG_setDate',
													allowBlank: true,
													selectOnFocus: true,
													xtype: 'swdatefield'
													//hidden: true,
													//hideLabel: true,
												}]
											},
											{
												border: false,
												layout: 'form',
												width: 90,
												items: [{
													fieldLabel: 'Время',
													hideLabel: true,
													name: 'EvnEKG_setTime',
													plugins: [ new Ext.ux.InputTextMask('99:99', true) ],
													id: 'EvnEKG_setTime',
													allowBlank: true,
													labelStyle: 'paddind:0',
													onTriggerClick: function() {
															var base_form = this.FormPanel.getForm();
															var time_field = base_form.findField('EvnEKG_setTime');
															if ( time_field.disabled ) {
																	return false;
															}
															setCurrentDateTime({
																dateField: base_form.findField('EvnEKG_setDate'),
																setDate: true,
																setDateMaxValue: false,
																setDateMinValue: false,
																setTime: true,
																timeField: time_field,
																windowId: this.id
															});
													}.createDelegate(this),
													validateOnBlur: false,
													xtype: 'swtimefield'
												}]
											},
											{
												border: false,
												layout: 'form',
												width: 680,
												items: [{
														hideLabel: true,
														listWidth: 600,
														style:'width:580px',
														mode:'local',
														editable: false,
														name: 'EvnEKG_rezEKG',
														id: 'EvnEKG_rezEKG',
														allowBlank: true,
														store:new Ext.data.Store({
																autoLoad:true,
																baseParams: {
																	KLrgn_id: 2
																},
																reader:new Ext.data.JsonReader({

																}, [
																		{name: 'ReferenceECGResult_code'},
																		{name: 'ReferenceECGResult_Name'},
																		{name: 'subgroupOKC'}
																]),
																url:'/?c=BSK_Register_User&m=getReferenceECGResult'
														}),
														displayField:'ReferenceECGResult_Name',
														valueField:'ReferenceECGResult_code',
														triggerAction: 'all',
														xtype: 'combo',
														listeners: {
															select: function(combo, record, index){
																  me.getResponsibilityMOZone();
																  //me.getResponsibilityMOZone(true, index);
															}
														}
											}]
											}]
									},
									{
										layout: 'column',
										style: 'margin-top:5px',
										items: [
											{
												border: false,
												layout: 'form',
												width: 350,
												items: [{
													format: 'd.m.Y',
													fieldLabel: 'ТЛТ',
													name: 'TLT_setDate',
													id: 'TLT_setDate',
													allowBlank: true,
													selectOnFocus: true,
													xtype: 'swdatefield',
													hidden: true
												}]
											},
											{
												border: false,
												layout: 'form',
												width: 90,
												items: [{
													fieldLabel: 'Время',
													hideLabel: true,
													hidden: true,
													name: 'TLT_setTime',
													plugins: [ new Ext.ux.InputTextMask('99:99', true) ],
													id: 'TLT_setTime',
													labelStyle: 'paddind:0',
													allowBlank: true,
													onTriggerClick: function() {
															var base_form = this.FormPanel.getForm();
															var time_field = base_form.findField('TLT_setTime');
															if ( time_field.disabled ) {
																return false;
															}
															setCurrentDateTime({
																dateField: base_form.findField('TLT_setDate'),
																setDate: true,
																setDateMaxValue: false,
																setDateMinValue: false,
																setTime: true,
																timeField: time_field,
																windowId: this.id
															});
													}.createDelegate(this),
													validateOnBlur: false,
													xtype: 'swtimefield'
												}]
											},
											{
												border: false,
												layout: 'form',
												width: 140,
												id: 'TLTres_f',
												style:'margin-left:-215px',
												items: [{
														listWidth: 110,
														style:'width:90px',
														mode:'local',
														hideLabel: true,
														allowBlank: true,
														editable: false,
														name: 'TLTres',
														id: 'TLTres',
														store:new Ext.data.SimpleStore(  {
															fields: [{ name:'TLTres', type:'string'}, {name:'TLTresid', type:'int'}],
															data: [
																['Не выполнено', 1],
																['Выполнено', 2]
															]
														}),
														displayField:'TLTres',
														valueField:'TLTresid',
														triggerAction: 'all',
														value: 1,
														xtype: 'combo',
														listeners: {
															select: function() {
																if (Ext.getCmp('TLTres').getValue() == 1)
																	{
																	Ext.getCmp('TLTreason_f').show();
																	//console.log('TLTreason_f',Ext.getCmp('TLTreason_f'));
																	Ext.getCmp('TLTres_f').getEl().dom.setAttribute('style', 'margin-left:-215px');
																	Ext.getCmp('TLT_setDate').hide();
																	Ext.getCmp('TLT_setDate').setValue('');
																	Ext.getCmp('TLT_setTime').hide();
																	Ext.getCmp('TLT_setTime').setValue('');
																}
																else {
																	Ext.getCmp('TLTreason').setValue('');
																	Ext.getCmp('TLTreason_f').hide();
																	Ext.getCmp('TLTres_f').getEl().dom.setAttribute('style', 'margin-left:0px');
																	Ext.getCmp('TLT_setTime').show();
																	Ext.getCmp('TLT_setDate').show();
																}
															}
														}
												}]
											},
											{
												border: false,
												layout: 'form',
												labelWidth: 50,
												width: 300,
												hidden:false,
												style:'margin-left:-80px',
												id: 'TLTreason_f',
												items: [{
														fieldLabel: 'Причина',
														listWidth: 230,
														style:'width:210px',
														mode:'local',
														allowBlank: true,
														editable: false,
														id: 'TLTreason',
														name: 'TLTreason',
														store:new Ext.data.SimpleStore(  {
																  fields: [{ name:'TLTreason', type:'string'}, {name:'TLTreasonid', type:'int'}],
																  data: [
																		  ['Противопоказания', 1],
																		  ['Отказ пациента', 2],
																		  ['Вышло время', 3],
																		  ['Пациент направлен на ЧКВ', 4],
																		  ['Отсутствие лекарственного средства', 5],
																		  ['Нет показаний', 6]
																		  ]
																}),
														displayField:'TLTreason',
														valueField:'TLTreasonid',
														triggerAction: 'all',
														xtype: 'combo'
												}]
											}

										]
									},
									{


										layout: 'column',
										style: 'margin-top:5px',
										items: [
											{
												border: false,
												layout: 'form',
												width: 500,
												id: 'Lpu_f',
												items: [{
														listWidth: 230,
														mode:'local',
														width: 230,
														fieldLabel: 'Выбор МО для госпитализации',
														allowBlank: true,
														editable: false,
														name: 'Lpu',
														id: 'Lpu',
														store:new Ext.data.Store({
																autoLoad:true,
																reader:new Ext.data.JsonReader({

																}, [
																	{name: 'Lpu_id'},
																	{name: 'Org_Nick'}
																]),
																url:'?c=BSK_Register_User&m=getMOforOKS'
														}),
														displayField:'Org_Nick',
														valueField:'Lpu_id',
														triggerAction: 'all',
														xtype: 'combo'
												}]
											}
										]
									},
									{
										layout: 'column',
										style: 'margin-top:5px',
										items: [
											{
												border: false,
												layout: 'form',
												width: 330,
												id: 'paramedic_f',
												items: [{
														width: 90,
														fieldLabel: 'Номер фельдшера по приёму вызова',
														allowBlank: true,
														name: 'paramedic',
														id: 'paramedic',
														xtype: 'numberfield'
												}]
											}
										]
									},
									{
										xtype: 'fieldset',
										title: 'Абсолютные противопоказания к проведению ТЛТ',
										autoHeight:true,
										items:[
											new sw.Promed.ViewFrame({
												id: 'absoluteTLT_grid',
												title : '',
												selectionModel: 'multiselect',
												region: 'center',
												disabled : false,
												contextmenu: true,
												border: false,
												height: 250,
												object: 'absoluteTLT_grid',
												dataUrl: ' /?c=BSK_Register_User&m=getContraindicationsTLT',
												autoLoadData: false,
												multi: true,
												style: 'margin-top:5px',
												ignoreRightMouseSelection: true,
												//autoExpandColumn : 'autoexpand',
												focusOnFirstLoad: false,
												autoScroll: true,
												stringfields: [
													{name: 'BSKObservRecomendation_id', type: 'int', header: 'ID', hidden: true},
													{name: 'BSKObservRecomendation_text', header: 'Наименование', type: 'string', width:Ext.getBody().getWidth()*0.8},
													{name: 'checked', header: 'checked', hidden: true}
												],
												actions: [
													{name:'action_add', text: 'Создать', hidden: true},
													{name:'action_edit', text: 'Изменить',  hidden: true},
													{name:'action_delete',  text: 'Удалить', hidden: true },
													{name:'action_view', hidden: true},
													{name:'action_refresh', hidden: true},
													{name:'action_print', hidden: true}
															],
												toolbar : false
											})
										],
										listeners: {
											render: function() {
												/*
												Ext.getCmp('absoluteTLT_grid').getGrid().getStore().baseParams = {
													BSKObservRecomendationType_id : 3,
													Person_id: 12
												};
												*/

											}
										}
									},
									{
										xtype: 'fieldset',
										title: 'Относительные противопоказания к проведению ТЛТ',
										autoHeight:true,
										items:[
											new sw.Promed.ViewFrame({
												id: 'relativeTLT_grid',
												title : '',
												selectionModel: 'multiselect',
												region: 'center',
												disabled : false,
												contextmenu: true,
												border: false,
												height: 300,
												object: 'GridObjects_TLT_rel',
												dataUrl: ' /?c=BSK_Register_User&m=getContraindicationsTLT',
												autoLoadData: false,
												multi: true,
												focusOnFirstLoad: false,
												ignoreRightMouseSelection: true,
												toolbar: false,
												autoScroll: true,
												stringfields: [
													{name: 'BSKObservRecomendation_id', type: 'int', header: 'ID', hidden: true},
													{name: 'BSKObservRecomendation_text', header: 'Наименование', type: 'string', width:Ext.getBody().getWidth()*0.8},
													{name: 'checked', header: 'checked', hidden: true}
												],
												actions: [
													{name:'action_add', text: 'Создать', hidden: true},
													{name:'action_edit', text: 'Изменить',  hidden: true},
													{name:'action_delete',  text: 'Удалить', hidden: true },
													{name:'action_view', hidden: true},
													{name:'action_refresh', hidden: true},
													{name:'action_print', hidden: true}
												]
											})
										]
									}

									//Ext.getCmp('absoluteTLT_grid').getGrid().getStore().on('load', function(){  })
								]
							},
							{
								title: 'Рекомендуемое лечение',
								//items: procedure_fieds,
								//disabled:true,
								id: 'recomend',
								autoHeight:true,
								items: [
								],
								listeners: {
									//Активация вкладки в "Рекомендуемое лечение" в зависимости от АРМ (врач или фельдшер)
									render: function(p) {
										me.setRecommendations();
									},
									activate: function(p) {
										//console.log('Activate');
										var index = Ext.getCmp('EvnEKG_rezEKG').getValue()-1;

										if (index < 0) return false;
										var pST = Ext.getCmp('EvnEKG_rezEKG').getStore().data.items[index].get('subgroupOKC');
										if (pST == 1)
										{
											Ext.Ajax.request ({
												url: '/?c=BSK_Register_User&m=getDolgnost',
												params: {
													MedPersonal_id:  getGlobalOptions().medpersonal_id
												},
												callback: function(opt, success, response) {
													if (success) {
														var data = Ext.util.JSON.decode(response.responseText);
														var str = data[0].Dolgnost_Name;
														var DolgnostName = /врач/gi.test(str) ? 1 : 2;
														me.getRecomendation('panel'+DolgnostName);
													}
													else {
														Ext.Msg.alert('Ajax.request', 'Запрос getDolgnost неуспешен');
													}
												}
											}
											);
										}
										else {
											me.getRecomendation('panel0');
										}
									}
								}
							}

						]

					}

				]
			}
			/*,
			{
				handler: function() {

					//На рабочем или тестовом
					var object_value = null;
					//var url = ((getGlobalOptions().birtpath != '/birt-viewer/') ? getGlobalOptions().birtpath : 'http://192.168.200.46:91/birt-viewer')  +'/run?__report=Report/';
					//var url = 'http://192.168.200.46:91/birt-viewer/run?__report=Report/';

					var form = Ext.getCmp('swCmpCallCardNewCloseCardWindow');
					var base_form = form.FormPanel.getForm();
					var report = '/LoadOKSData.rptdesign&Person_id='+form.Person_id+'&CmpCloseCard_id='+base_form.findField('CmpCallCard_id').getValue();
					var paramStr = report +'&__format=pdf';
					var url = ((getGlobalOptions().birtpath)?getGlobalOptions().birtpath:'')+'run?__report=report';

					var home =  window.location.host == '127.0.0.1' ? 'http://192.168.200.29:8080' : '';
					window.open(home+url+paramStr, '_blank');
				},
				xtype: 'button',
				iconCls: 'print16',
				text: 'Печать'
			}
			*/
		]
	},

	//рекоммендации

	 setRecommendations: function() {
		this.showLoadMask('Подождите...');
		Ext.Ajax.request({
			callback: function(opt, success, response) {
					this.hideLoadMask();
					if (success) {
						var response_obj = Ext.util.JSON.decode(response.responseText);
						//console.log('Рекомендации',response_obj);
						var count_r = response_obj.count;
						for (var i in response_obj)  {
							if (typeof response_obj[i] == 'object') {
								//console.log('forWhom',response_obj[i]);
								//var num = ++i;
								var recomend =  new sw.Promed.Panel({
									autoHeight: true,
									border: true,
									id: 'panel'+i,
									collapsed: true,
									title: response_obj[i].forWhom,
									html: '<div style="padding: 5px; font-size: 14px;">'+response_obj[i].BSKObservRecomendation_text+ '</div>',
									collapsible: true
								});
								//console.log('recomend', recomend);
								Ext.getCmp('recomend').add(recomend);
								Ext.getCmp('recomend').doLayout();

							}
						}
					}
					else {
						sw.swMsg.alert('Ошибка', 'Ошибка загрузки рекомендаций по лечению');
					}
			}.createDelegate(this),
			url: '/?c=BSK_Register_User&m=getRecomendationOKS'
		});
	},
	//Открытие и закрытие панелей для рекомендаций
	getRecomendation:  function(panel) {
		var panels = [
		  'panel0','panel1','panel2','panel3'
		];
		//console.log('panel', panel);
		for(var k in panels){
			//console.log('panel', panels[k],typeof panels[k] );
			if (typeof panels[k] == 'string') {

				if(panel == panels[k]){
					//console.log('panelexpand', panels[k]);
					Ext.getCmp(panels[k]).expand(true);
					//Ext.getCmp(panel).doLayout();
				}
				else{
					//console.log('panelcollapse', panels[k]);
					Ext.getCmp(panels[k]).collapse(true);
				}
			}
		}
	},

	//end рекоммендации

	//ОКС
	
	 //Обратный отсчет времени для ТЛТ
	timerTLT: function () {
		var me = this;
		
		if(!me.FormPanel.getForm().findField('ArriveTime')) {
			clearInterval(me.tltInterval);
		} else {
			
			var datetimeCMP = new Date(me.FormPanel.getForm().findField('ArriveTime').getValue()); 

			//+30 мин для проведения ТЛТ
			var datetimeTLT30 = new Date(datetimeCMP.getTime()+30*60000);  

			var today = new Date();        
			var timeTLT = Math.floor((datetimeTLT30-today)/1000);
			
			if(datetimeCMP == 'Invalid Date'){
				document.getElementById('timerOks').innerHTML='--:--:--';
				clearInterval(me.tltInterval);
			}
			else if(timeTLT >= 0){
				var tsec=timeTLT%60;  timeTLT=Math.floor(timeTLT/60); if(tsec<10)tsec='0'+tsec;                
				var tmin=timeTLT%60;  timeTLT=Math.floor(timeTLT/60); if(tmin<10)tmin='0'+tmin;
				var thour=timeTLT%24; timeTLT=Math.floor(timeTLT/24);

				var timestr='0'+thour  +":" + tmin + ":"+ tsec

				document.getElementById('timerOks').innerHTML=timestr;
			}
			else{
				document.getElementById('timerOks').innerHTML='00:00:00';    
			}
			if (document.getElementById('timerOks').innerHTML == '00:00:00') {
				document.getElementById('timerOks').style.color = 'red';
			}
			else {
				document.getElementById('timerOks').style.color = 'black';
			}
		}
	},

	//Определение зоны ответственности МО и ЧКВ

	getResponsibilityMOZone: function() {
		var form =  this.FormPanel.getForm(),
			KLStreet_id = form.findField('Street_id').getValue(),
			LpuRegionStreet_HouseSet = form.findField('House').getValue(),
			KLArea_id = form.findField('City_id').getValue(),
			KLSubRgn_id = form.findField('Area_id').getValue(),
			EvnEKG_rezEKG = form.findField('EvnEKG_rezEKG').getValue();
			
		if(!EvnEKG_rezEKG) return false;
		
		Ext.Ajax.request ({
			url: '?c=BSK_Register_User&m=getResponsibilityMOZone',
			params: {
				KLStreet_id: KLStreet_id,				
				EvnEKG_rezEKG: EvnEKG_rezEKG,
				KLArea_id: KLArea_id,
				KLSubRgn_id: KLSubRgn_id
			},
			callback: function(opt, success, response) {
				if (success) {
					var data = Ext.util.JSON.decode(response.responseText);
					
					var LpuMO_Nick = '', LpuChkv_Nick = '';
						
					if(data.length){
						for(var i = 0; i<data.length; i++){
							var delimeter = (i<data.length-1)?', ':''
							LpuMO_Nick += (data[i].LpuMO_Nick)+ delimeter;
							LpuChkv_Nick +=( data[i].LpuChkv_Nick ) + delimeter;
						}
					}
					else{
						LpuMO_Nick = 'не определена',
						LpuChkv_Nick = 'не определена';
					}
					
					document.getElementById('mo').innerHTML = LpuMO_Nick;
					document.getElementById('chkv').innerHTML = LpuChkv_Nick;
					//document.getElementById('mo').innerHTML = (data[0] && data[0].LpuMO_Nick)? data[0].LpuMO_Nick: 'не определена';
					//document.getElementById('chkv').innerHTML = (data[0] && data[0].LpuChkv_Nick)? data[0].LpuChkv_Nick: 'не определена';
					//console.log('Результат запроса',data);
					//console.log('data.withoutSTLpu__Name', data[0].withoutSTLpu__Name)
					/*
					if (ekg) {
						var subgroupOKC = Ext.getCmp('EvnEKG_rezEKG').getStore().data.items[index].get('subgroupOKC');
						//console.log('subgroupOKC',subgroupOKC);
						if (subgroupOKC == 1) {
							document.getElementById('mo').innerHTML = (data[0].withSTLpu_Name != null)? data[0].withSTLpu_Name: 'не определена';
						}
						else if (subgroupOKC == 2) {
							document.getElementById('mo').innerHTML = (data[0].withoutSTLpu_Name != null)? data[0].withoutSTLpu_Name: 'не определена';
						}
						else {
							document.getElementById('mo').innerHTML = 'не определена';
						}
					}
					document.getElementById('chkv').innerHTML = (data[0].CHKVLpu_Name != null)? data[0].CHKVLpu_Name: 'не определена';
					*/
				}
				else {
					Ext.Msg.alert('Ajax.request', 'Запрос зоны ответственности неуспешный');
				}
			}
		})
	},

	doSaveOKS: function() {
			//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
			//Проверка вкладки ОКС на валидность входных данных, запись входных данных в переменные
			//+++++S++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

		var me = this,
			format_date = function(datestring) {
				var mas_d_t = (datestring+':00').split(' ');
				var mas_d = mas_d_t[0].split('.');
				return mas_d[2]+'-'+mas_d[1]+'-'+mas_d[0]+' '+mas_d_t[1];              
			},
			diffTime = function(bd,ed) {
				var date1 = new Date(bd);
				//console.log('date1',date1);
				var date2 = new Date(ed);
				//console.log('date2',date2);            
				var diff = date2 - date1;
				//console.log('diff',diff);
				var msec = diff;
				var hh = Math.floor(msec / 1000 / 60 / 60);
				
				msec -= hh * 1000 * 60 * 60;
				var mm = Math.floor(msec / 1000 / 60);
				return hh + ":" + mm;            
			};
		
		if (!Ext.getCmp('tabOKC').disabled) {
			this.formStatus == 'save'
			var base_form = this.FormPanel.getForm();

			var Person_id = base_form.findField('Person_id').getValue(); 

			var Diag_id = base_form.findField('Diag_id').getValue();

			var DiagOKS = base_form.findField('Diag_id').getRawValue()

			//var CmpCallCard_id = this.CmpCallCard_id;
			var CmpCallCard_id = base_form.findField('CmpCallCard_id').getValue();
			
			if (base_form.findField('ArriveTime').getStringValue() == '') {
				var ArrivalDT = null;
			}
			else {
				var ArrivalDT = format_date(base_form.findField('ArriveTime').getStringValue());
			}

			var pain_setDate = new Date(Ext.getCmp('pain_setDate').getValue());
			var pain_setTime = Ext.getCmp('pain_setTime').getValue();
			if (pain_setDate == 'Invalid Date' || pain_setTime == '') {
				var PainDT = null;
			}
			else {
				var PainDT = pain_setDate.format('Y-m-d') + ' ' + pain_setTime+':00';
			}

			var EvnEKG_setDate = new Date(Ext.getCmp('EvnEKG_setDate').getValue());
			var EvnEKG_setTime = Ext.getCmp('EvnEKG_setTime').getValue();
			var ResultECG = Ext.getCmp('EvnEKG_rezEKG').getValue();
			
			if (ResultECG > '') {
				var ECGDT = EvnEKG_setDate.format('Y-m-d') + ' ' + EvnEKG_setTime+':00';

			}
			else {
				ResultECG = 'нет';
				ECGDT = 'нет';
			}

			var FailTLT;
			var TLTDT;
			var TLT_setDate;
			var TLT_setTime;
			var TLTres;
			if (Ext.getCmp('TLTres').getValue() == 1 || Ext.getCmp('TLTres').getValue() == 'Не выполнено') {
					FailTLT = Ext.getCmp('TLTreason').getRawValue( );
					TLTDT = 'нет';
					TLTres = Ext.getCmp('TLTres').getRawValue();
			}
			else if (Ext.getCmp('TLTres').getValue() == 2) {
					TLT_setDate = new Date (Ext.getCmp('TLT_setDate').getValue());
					TLT_setTime = Ext.getCmp('TLT_setTime').getValue();
					TLTDT  = TLT_setDate.format('Y-m-d') + ' ' + TLT_setTime+':00';
					FailTLT = 'нет';
					TLTres = Ext.getCmp('TLTres').getRawValue();
			}
			
			//Время прибытия в медицинскую организацию
			if (base_form.findField('isOtkazHosp').getValue() == 2) {
				var LpuDT = 'отказ';
			}
			else {
				LpuDT = '';				
			}

			//Объект с подгружаемыми из БД абсолютными противопоказаниями по диагнозам
			var abs_obj = {};
			var abs_data = Ext.getCmp('absoluteTLT_grid').getGrid().getStore().data.items;
			for (var i in abs_data) {
				if (typeof abs_data[i]=='object' && !Ext.isEmpty(abs_data[i].data.BSKObservRecomendation_id)) {
					if (abs_data[i].data.checked == 1) {
						abs_obj[abs_data[i].id] = abs_data[i].id.inlist(this.isDB)? "Да.":"Да";						
					}
					else
					{
						abs_obj[abs_data[i].id] = abs_data[i].id.inlist(this.isDB)? "Нет.":"Нет";					  
					}
				}
			}
		
			//Объект с выделенными в форме абсолютными противопоказаниями
			var abs_obj_form = {};
			var abs_data_form = Ext.getCmp('absoluteTLT_grid').getGrid().getSelectionModel().selections.items;
			for (var i in abs_data_form) {
				if (typeof abs_data[i]=='object') {
					abs_obj_form[abs_data_form[i].id] = "Да";
				}
			}
			//console.log(abs_obj_form);
			//Объект abs_obj для сохранения абсолютных противопоказаний в БД
			for (var i in abs_obj_form) {
				if (abs_obj[i] != "Да.") {
					abs_obj[i] =  "Да"
				}
			}
			//console.log(abs_obj);
			var AbsoluteList = Ext.util.JSON.encode(abs_obj);
			//console.log('AbsoluteList', AbsoluteList);

			//Объект с подгружаемыми из БД относительными противопоказаниями по диагнозам
			var rel_obj = {};
			var rel_data = Ext.getCmp('relativeTLT_grid').getGrid().getStore().data.items;
			for (var i in rel_data) {
				if (typeof rel_data[i]=='object' && !Ext.isEmpty(rel_data[i].data.BSKObservRecomendation_id)) {
					if (rel_data[i].data.checked == 1) {
						rel_obj[rel_data[i].id] = rel_data[i].id.inlist(this.isDB)? "Да.": "Да";
						//console.log(rel_data[i].id, 'в', this.isDB, rel_obj[rel_data[i].id]);
					}
					else
					{
						rel_obj[rel_data[i].id] = rel_data[i].id.inlist(this.isDB)? "Нет.": "Нет";
						//console.log(rel_data[i].id, 'в', this.isDB, rel_obj[rel_data[i].id]);
					}
				}
			}
			//console.log(rel_obj);
			//Объект с выделенными в форме относительными противопоказаниями
			var rel_obj_form = {};
			var rel_data_form = Ext.getCmp('relativeTLT_grid').getGrid().getSelectionModel().selections.items;
			for (var i in rel_data_form) {
				if (typeof rel_data[i]=='object') {
					rel_obj_form[rel_data_form[i].id] = "Да";
				}
			}
			//console.log(rel_obj_form);
			//Объект rel_obj для сохранения относительными противопоказаний в БД
			for (var i in rel_obj_form) {
				if (rel_obj[i] != "Да.") {
					rel_obj[i] =  "Да"
				}
			}
			//console.log(rel_obj);
			var RelativeList = Ext.util.JSON.encode(rel_obj);
			//console.log('RelativeList', RelativeList);

			var ZonaMO = document.getElementById('mo').innerHTML;

			var ZonaCHKV = document.getElementById('chkv').innerHTML;

			if (base_form.findField('isOtkazHosp').getValue() == 2) {
			}
			else {
				var MOHospital = Ext.getCmp('Lpu').getRawValue();
			}
			

			var MedStaffFact_num = Ext.getCmp('paramedic').getRawValue();
		

			//var LpuBuilding_name = this.FormPanel.getForm().findField('LpuBuilding_id').getRawValue() +' ['+ Ext.getCmp('StationNum').getValue()+']';
			var LpuBuilding_name = this.FormPanel.getForm().findField('LpuBuilding_id').getRawValue();

			//var EmergencyTeam_number = Ext.getCmp('EmergencyTeam_number').getRawValue();
			var EmergencyTeam_number = this.FormPanel.getForm().findField('EmergencyTeam_id').getHeadShift();

			var AcceptTime = format_date(base_form.findField('AcceptTime').getStringValue());

			if (base_form.findField('TransTime').getStringValue() == '') {
				var TransTime = null;
			}
			else {
				var TransTime = format_date(base_form.findField('TransTime').getStringValue());
			}

			if (base_form.findField('GoTime').getStringValue() == '') {
				var GoTime = null;
			}
			else {
				var GoTime = format_date(base_form.findField('GoTime').getStringValue());
			}

			if (base_form.findField('TransportTime').getStringValue() == '') {
				var TransportTime = null;
			}
			else {
				var TransportTime = format_date(base_form.findField('TransportTime').getStringValue());
			}

			if (base_form.findField('EndTime').getStringValue() == '') {
				var EndTime = null;
			}
			else {
				var EndTime = format_date(base_form.findField('EndTime').getStringValue());
			}

			if (base_form.findField('BackTime').getStringValue() == '') {
				var BackTime = null;
			}
			else {
				var BackTime = format_date(base_form.findField('BackTime').getStringValue());
			}

			if (base_form.findField('ToHospitalTime') != undefined) {
				if (base_form.findField('ToHospitalTime').getStringValue() == '') {
					var ToHospitalTime = null;
				}
				else {
					var ToHospitalTime = format_date(base_form.findField('ToHospitalTime').getStringValue());
				}
			}
			else {
				var ToHospitalTime = null;
			}

			if (ToHospitalTime != null) {
				var SummTime = diffTime(AcceptTime, ToHospitalTime);
			}
			else if (TransportTime != null) {
				var SummTime = diffTime(AcceptTime, TransportTime);
			}
			else {
				var SummTime = null;
			}
			var UslugaList = this.FormPanel.find('refId', 'uslugaGrid');
			if(UslugaList && UslugaList[0]){
				var UslugaTLT = '';
				var listTLT = ['A11.12.003.005','A11.12.003.006','A11.12.003.007','A11.12.003.008'];
				var UslugaListTLT = UslugaList[0].getStore().data.items;
				UslugaListTLT.forEach(function(item, i, UslugaListTLT) {
					if (UslugaListTLT[i].data.UslugaComplex_Code.inlist(listTLT) && UslugaListTLT[i].data.CmpCallCardUsluga_Kolvo >= 1) {
						UslugaTLT += UslugaListTLT[i].data.UslugaComplex_Code + ';';
					}
				});
				UslugaTLT = UslugaTLT == '' ? null : UslugaTLT;
			}
			else {
				var UslugaTLT = null;
			}

			var params_OKS = {
				MorbusType_id: 19,
				Person_id: Person_id,
				Diag_id: Diag_id,
				DiagOKS: DiagOKS,
				CmpCallCard_id: CmpCallCard_id,
				ArrivalDT: ArrivalDT,
				PainDT: PainDT,
				ECGDT: ECGDT,
				ResultECG: ResultECG,
				TLTDT: TLTDT,
				FailTLT: FailTLT,
				LpuDT: ToHospitalTime,
				AbsoluteList: AbsoluteList,
				RelativeList: RelativeList,
				ZonaMO: ZonaMO,
				ZonaCHKV: ZonaCHKV,
				MOHospital: MOHospital,
				MedStaffFact_num: MedStaffFact_num,
				LpuBuilding_name: LpuBuilding_name, // Станция (подстанция), отделения:
				EmergencyTeam_number: EmergencyTeam_number,
				AcceptTime: AcceptTime,
				TransTime: TransTime,
				ToHospitalTime: ToHospitalTime,
				GoTime: GoTime,
				TransportTime: TransportTime,
				EndTime: EndTime,
				BackTime: BackTime,
				SummTime: SummTime,
				UslugaTLT: UslugaTLT,
				TLTres: TLTres
			};

			// готовим дату для поиска ОКС
			var acceptTimeFull = base_form.findField('AcceptTime').getStringValue().split(' ')[0];
			var acceptSplit = acceptTimeFull.split('.');
			var acceptDay = acceptSplit[0];
			var acceptMonth = acceptSplit[1];
			var acceptYear = acceptSplit[2];
			var BSKRegistry_setDate = acceptYear+'-'+acceptMonth+'-'+acceptDay;

			var saveOKSAjax = function(params_OKS) {
				Ext.Ajax.request({
					params: params_OKS,
					async: false,
					url: '/?c=BSK_RegisterData&m=saveInOKS',
					success: function (response, options) {
						var response_obj = Ext.util.JSON.decode(response.responseText);
						if(response_obj.Error_Msg === undefined) {
							clearInterval(me.tltInterval);
							return true;
						} else {
							sw.swMsg.alert('Ошибка', 'При сохранении ОКС возникла ошибка');
							me.hideLoadMask();
							return false;
						}
					}
				});
			};

			// проверяем, нет ли ОКС за сегодня
			Ext.Ajax.request({
				params: {
					Person_id: Person_id,
					BSKRegistry_setDate: BSKRegistry_setDate
				},
				url: '/?c=BSK_Register_User&m=checkInOKS',
				async: false,
				success: function (response, options) {
					var response_obj = Ext.util.JSON.decode(response.responseText);
					if(response_obj.length > 0) {
						params_OKS['Registry_method'] = response_obj[0].BSKRegistry_id;
					} else {
						params_OKS['Registry_method'] = 'ins';
					}
					saveOKSAjax(params_OKS);
					return true;
				},
			});
		}
	}
	

});
