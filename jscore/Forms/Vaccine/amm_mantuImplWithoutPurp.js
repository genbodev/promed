/**
 * amm_mantuImplWithoutPurp - окно Исполнения манту минуя назначение
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 *
 * @copyright    Copyright (c) 2012 
 * @author       
 * @version      11.07.2012
 * @comment      
 */

sw.Promed.amm_mantuImplWithoutPurp = Ext.extend(sw.Promed.BaseForm, {
	id: 'amm_mantuImplWithoutPurp',
	title: "Исполнение Манту",
	border: false,
	width: 800,
	height: 500,
	maximizable: false,
	closeAction: 'hide',
	layout: 'border',
	codeRefresh: true,
	modal: true,
	objectName: 'amm_mantuImplWithoutPurp',
	objectSrc: '/jscore/Forms/Vaccine/amm_mantuImplWithoutPurp.js',
	onHide: Ext.emptyFn,
	listeners: {
		'show': function (c) {
			//исп-ся вместе с защитой от повторных нажатий
			sw.Promed.vac.utils.resetButsDis(c);
		},
		'hide': function (c) {
			//исп-ся вместе с защитой от повторных нажатий
			sw.Promed.vac.utils.resetButsDis(c);
		}
	},
	initVacWay: function () {
		var comboVacWay = this.vacEditForm.form.findField('VaccineWayPlace_id');
		var comboDiagnosisType = this.vacEditForm.form.findField('TubDiagnosisType_id');
		comboVacWay.reset();
		if(!comboDiagnosisType.getValue()) return false;
		var dt = Ext.getCmp('vacMantuDateImpl').value
		var parsVacWay = new Object();
		parsVacWay.birthday = this.formParams.birthday;
		if (dt != undefined) {
			parsVacWay.date_purpose = dt;
		} else if (this.formParams.date_purpose != undefined) {
			parsVacWay.date_purpose = this.formParams.date_purpose;
		} else if (this.formParams.date_impl != undefined) {
			parsVacWay.date_purpose = this.formParams.date_impl;
		} else {
			parsVacWay.date_purpose = (new Date).format('d.m.Y');
		}
		parsVacWay.vaccine_id = this.formParams.vaccine_id;
		comboVacWay.getStore().load({
			params: parsVacWay
			, callback: function () {
				var val = '';
				/*
				if ((comboVacWay.getStore().getCount() > 0) && (this.formParams.status_type_id == 0 || this.formParams.status_type_id == undefined)) {
					val = comboVacWay.getStore().getAt(0).get('VaccineWayPlace_id');
				} else if (this.formParams.status_type_id == 1) {
					val = this.formParams.vaccine_way_place_id;
				}
				*/
				if (this.formParams.status_type_id == 1 && this.formParams.vaccine_way_place_id) {
					val = this.formParams.vaccine_way_place_id;
					if(!comboVacWay.findRecord('id_VaccineWayPlace_VaccinePlace', val) && this.formParams.way_place_id){
						//возможно запись старая и содержит не составной идентификатор
						val = '';
						var rec = comboVacWay.findRecord('VaccineWayPlace_id', this.formParams.way_place_id);
						if(rec && rec.get('id_VaccineWayPlace_VaccinePlace')){
							val = rec.get('id_VaccineWayPlace_VaccinePlace');
						}
					}
				}else if(comboVacWay.getStore().getCount() > 0){
					val = comboVacWay.getStore().getAt(0).get('id_VaccineWayPlace_VaccinePlace');
				}
				comboVacWay.setValue(val);
			}.createDelegate(this)
		});

	},
	initVacSeria: function ($type) {
		if(!$type) return false;
		var params = new Object();
		params.Lpu_id = this.formParams.Lpu_id;
		if ($type == 1)  //Это манту
			params.vaccine_id = 26;  //  Туберкулин
		else
			params.vaccine_id = 27;  // Диаскинтест

		var comboVacSeria = this.vacEditForm.form.findField('VaccineSeria_id');
		var comboCityManufacturer = this.vacEditForm.form.findField('cityManufacturer');
		comboVacSeria.getStore().load({
			//params: this.formParams
			params: params
			, callback: function () {
				comboVacSeria.reset();
				comboCityManufacturer.reset();
				var val = '';
				var notInList = 0;
				if (comboVacSeria.getStore().getCount() > 0) {
					/*
					//sw.Promed.vac.utils.consoleLog('1');
					if (this.formParams.status_type_id == 0 || this.formParams.status_type_id == undefined) {
						val = comboVacSeria.getStore().getAt(0).get('VacPresence_id');
						//sw.Promed.vac.utils.consoleLog('2');
						sw.Promed.vac.utils.consoleLog(val);
						//this.vacEditForm.form.findField('cityManufacturer').setValue(record.get('Manufacturer'))
					} else if (this.formParams.status_type_id == 1) {
						val = this.formParams.vac_presence_id;
						// sw.Promed.vac.utils.consoleLog('3');
					}
					var indx = comboVacSeria.getStore().findBy(function (rec) {
						return rec.get('VacPresence_id') == val;
					});
					if (indx != -1) {//-1 если не найдено
						comboVacSeria.setValue(val);
						//comboVacSeria.reset();
						//sw.Promed.vac.utils.consoleLog('4');
						sw.Promed.vac.utils.consoleLog(val);
					} else {
						notInList = 1;
						//sw.Promed.vac.utils.consoleLog('5');
					}
					*/
					if (this.formParams.status_type_id == 1 && this.formParams.vac_presence_id) {
						val = this.formParams.vac_presence_id;
					}else {
						val = comboVacSeria.getStore().getAt(0).get('VacPresence_id');
					}
					notInList = (val && comboVacSeria.findRecord('VacPresence_id', val)) ? 0 : 1;
					comboVacSeria.setValue(val);

				} else {
					notInList = 1;
					// sw.Promed.vac.utils.consoleLog('6');
				}

				if (notInList) {
					var arr = [];
					//sw.Promed.vac.utils.consoleLog('7');
					if (this.formParams.vac_presence_seria != undefined)
						arr.push(this.formParams.vac_presence_seria);
					if (this.formParams.vac_presence_period != undefined)
						arr.push(this.formParams.vac_presence_period);
					val = arr.join(' - ');


					if (val == '' && this.formParams.vac_seria_txt) val = this.formParams.vac_seria_txt;
					comboVacSeria.setValue(val);
				}
				var implVaccineSeriaCombo = Ext.getCmp('mantu_implVaccineSeriaCombo');
				if (this.formParams.vac_presence_manufacturer != undefined) {
					this.vacEditForm.form.findField('cityManufacturer').setValue(this.formParams.vac_presence_manufacturer);
				} else if (val && (implVaccineSeriaCombo.getStore().getCount()>0 && implVaccineSeriaCombo.getStore().getAt(0).json != undefined)) {
					this.vacEditForm.form.findField('cityManufacturer').setValue(Ext.getCmp('mantu_implVaccineSeriaCombo').getStore().getAt(0).json.Manufacturer);
				}


			}.createDelegate(this)
		});
	},
	initComponent: function () {
		var params = new Object();
		var form = this;
		//объект для контроля дат формы:
		this.validateVacImplementDate = sw.Promed.vac.utils.getValidateObj({
			formId: 'mantuImplWithoutPurpEditForm',
			fieldName: 'vacMantuDateImpl'
		});

		/*
		 * хранилище для доп сведений
		 */
		this.formStore = new Ext.data.JsonStore({
			fields: ['JournalMantu_id', 'VacPresence_id', 'Dose', 'WayPlace_id', 'React_id', 'DatePurpose'
				, 'JournalMantu_ReactDescription', //'LocalReactDesc'
				, 'DateVac', 'DateReact', 'Lpu_id', 'MedPersonal_id', 'StatusType_id'
				, 'VacPresence_Seria', 'VacPresence_Period', 'VacPresence_Manufacturer'
				, 'JournalMantu_Seria'
				, 'Person_id', 'person_BirthDay'
				, 'Reaction30min', 'ReactionSize'
				, 'TubDiagnosisType_id'
				, 'DiaskinTestReactionType_id'
				, 'id_VaccineWayPlace_VaccinePlace'
			],
			url: '/?c=VaccineCtrl&m=loadJournalMantuFormInfo',
			key: 'JournalMantu_id',
			root: 'data'
		});

		this.PersonInfoPanel = new sw.Promed.PersonInfoPanel({
			titleCollapse: true,
			floatable: false,
			collapsible: true,
			collapsed: true,
			border: true,
			plugins: [Ext.ux.PanelCollapsedTitle],
			region: 'north'
					//,autoLoad: true
		});

		this.setAllowBlank = function (allowBlank) {
			this.vacEditForm.getForm().findField('MedStaffFact_id').allowBlank = allowBlank;
			this.vacEditForm.getForm().findField('MedStaffFact_id').isValid();
			this.vacEditForm.getForm().findField('VaccineSeria_id').allowBlank = allowBlank;
			this.vacEditForm.getForm().findField('VaccineSeria_id').isValid();
		};

		Ext.apply(this, {
			formParams: null,
			buttons: [
				{text: 'Сохранить',
					iconCls: 'save16',
					tabIndex: TABINDEX_MANTUIMPNPURPFRM + 30,
					handler: function (b) {
						//alert('Сохранить');	
						b.setDisabled(true);//деактивируем кнопку (исключен повторных нажатий)
						//return false;
//					alert('Сохранить');
						var implForm = Ext.getCmp('mantuImplWithoutPurpEditForm');
						if (!implForm.form.isValid()) {
//					if (!implForm.form.isValid() || !implForm.getForm().findField('vacMantuDateReact').isValid()) {
//						alert(implForm.getForm().findField('vacMantuDateReact').getValue());
							sw.Promed.vac.utils.msgBoxNoValidForm();
							b.setDisabled(false);
							return false;
						}
						//Проверка поля 'Дата проверки':
//					if (implForm.getForm().findField('vacMantuReactDesc').getValue()) {
						if (implForm.getForm().findField('ReactionSize').getValue() || implForm.getForm().findField('TypeReaction_id').getValue()) {
							if (Ext.isEmpty(implForm.getForm().findField('vacMantuDateReact').getValue())) {
								Ext.MessageBox.show({
									title: "Неверное значение поля",
									msg: "Поле 'Дата проверки' должно быть заполнено!",
									buttons: Ext.Msg.OK,
									icon: Ext.Msg.WARNING
									, fn: function () {
										implForm.getForm().findField('vacMantuDateReact').focus(true, 100);
									}
								});
								b.setDisabled(false);
								return false;
							}
						}
						var vacMantuDateReact = implForm.getForm().findField('vacMantuDateReact').getValue();
						//alert(vacMantuDateReact);
						if ((vacMantuDateReact != undefined) && (vacMantuDateReact != '')) {
							if (vacMantuDateReact < implForm.getForm().findField('vacMantuDateImpl').getValue()) {
								//if (Ext.isEmpty(implForm.getForm().findField('vacMantuDateReact').getValue())) {
								Ext.MessageBox.show({
									title: "Неверное значение поля",
									msg: "Значение поля 'Дата исполнения' должно быть меньше<br/>значения поля 'Дата проверки'!",
									buttons: Ext.Msg.OK,
									icon: Ext.Msg.WARNING
									, fn: function () {
										implForm.getForm().findField('vacMantuDateReact').focus(true, 100);
									}
								});
								b.setDisabled(false);
								return false;
							}
						}
						// }

						var implWin = Ext.getCmp('amm_mantuImplWithoutPurp');
						var pars = new Object();


						pars.diagnosis_type = Ext.getCmp('mantu_TubDiagnosisTypeCombo').getValue();
						//ReactionSize
						if (pars.diagnosis_type == 1) {  // Если манту
							pars.reaction_size = implForm.getForm().findField('ReactionSize').getValue();
						} else {
							pars.diaskin_type_reaction = Ext.getCmp('mantu_DiaskinTypeReactionCombo').getValue();
						}

						pars.JournalMantu_ReactDescription = implForm.getForm().findField('JournalMantu_ReactDescription').getValue();
						pars.date_impl = implForm.getForm().findField('vacMantuDateImpl').getValue().format('d.m.Y');
						pars.date_react = implForm.getForm().findField('vacMantuDateReact').getValue() ? implForm.getForm().findField('vacMantuDateReact').getValue().format('d.m.Y') : '';
						pars.plan_tub_id = this.formParams.plan_tub_id;
						pars.fix_tub_id = this.formParams.fix_tub_id;
						pars.person_id = this.formParams.person_id;
						pars.status_type_id = 1;//(vacMantuForm.form.findField('vacMantuStatus').getValue() ? 1 : 0);

						var idx = implForm.form.findField('VaccineSeria_id').getStore().findBy(function (rec) {
							return rec.get('VacPresence_id') == implForm.form.findField('VaccineSeria_id').getValue();
						});
						var seriaRecord = implForm.form.findField('VaccineSeria_id').getStore().getAt(idx);
						if (typeof (seriaRecord) == 'object') {
							pars.vac_presence_id = implForm.form.findField('VaccineSeria_id').getValue();
							pars.vac_seria = seriaRecord.get('Seria');
							pars.vac_period = seriaRecord.get('Period');
						} else {
							pars.vac_seria = implForm.form.findField('VaccineSeria_id').getRawValue();
						}

						//pars.vaccine_way_place_id = implForm.form.findField('VaccineWayPlace_id').getValue();
						pars.vaccine_way_place_id = implForm.form.findField('VaccineWayPlace_id').getFieldValue('VaccineWayPlace_id');
						pars.vaccine_place_id = implForm.form.findField('VaccineWayPlace_id').getFieldValue('VaccinePlace_id');

						pars.vac_doze = implForm.form.findField('VaccineDose_Name').getValue();//vacPurpForm.form.findField('VaccineDoze_id').getValue();
						pars.med_staff_fact_id = implForm.form.findField('MedStaffFact_id').getValue();

						pars.lpu_id = implForm.getForm().findField('mantu_implLpuCombo').getValue();
						pars.reaction_type = implForm.getForm().findField('TypeReaction_id').getValue();
						//pars.reaction_desc = implForm.getForm().findField('vacMantuReactDesc').getValue();


						//pars.local_reaction_desc = implForm.getForm().findField('vacReactDesc').getValue();
						pars.checkbox_reaction30min = implForm.getForm().findField('Reaction30min').getValue();

						var modelMantuSave = '';
						var arrKeys = [];
						switch (this.formParams.source) {
							case 'TubPlan':
								arrKeys.push(pars.plan_tub_id);
								modelMantuSave = 'saveMantu';
								if (pars.status_type_id == 1)
									pars.date_purpose = pars.date_impl;
								break;
							case 'TubAssigned':
							case 'TubReaction':
								arrKeys.push(pars.fix_tub_id);
								modelMantuSave = 'saveMantuFixed';
								// modelMantuSave = 'saveMantuFixed';
								break;
							default:
								modelMantuSave = 'saveMantu';
								break;
						}
						if (this.formParams.add_new_mantu == 1) {
							modelMantuSave = 'saveMantu';
							if (pars.status_type_id == 1)
								pars.date_purpose = pars.date_impl;
						}

						Ext.Ajax.request({
							// url: '/?c=VaccineCtrl&m=saveMantuImplWithoutPurp',
							// url: '/?c=VaccineCtrl&m=saveMantu',
							url: '/?c=VaccineCtrl&m=' + modelMantuSave,
							method: 'POST',
							params: pars, //implWin.formParams
							success: function (response, opts) {
								sw.Promed.vac.utils.consoleLog(response);

								if (sw.Promed.vac.utils.msgBoxErrorBd(response) == 0) {
									// alert(this.formParams.parent_id);
									// alert(this.formParams.source);
									Ext.getCmp(this.formParams.parent_id).fireEvent('success', this.formParams.source, {
										// keys: [pars.fix_tub_id]
										keys: arrKeys
									});
								}
								form.hide();
							}.createDelegate(this),
							failure: function (response, opts) {
								sw.Promed.vac.utils.consoleLog('server-side failure with status code: ' + response.status);
							}
						});
					}.createDelegate(this)

				}, {
					text: '-'
				},
				HelpButton(this, TABINDEX_MANTUIMPNPURPFRM + 31),
				{handler: function () {
						this.hide();
					}.createDelegate(this),
					iconCls: 'close16',
					tabIndex: TABINDEX_MANTUIMPNPURPFRM + 32,
					onTabAction: function () {
						this.vacEditForm.form.findField('vacMantuDateImpl').focus();
					}.createDelegate(this),
					text: '<u>З</u>акрыть'

				}],
			items: [
				this.PersonInfoPanel,
				this.vacEditForm = new Ext.form.FormPanel({
					autoScroll: true,
					region: 'center',
					bodyBorder: false,
					bodyStyle: 'padding: 5px 5px 0',
					border: false,
					frame: false,
					id: 'mantuImplWithoutPurpEditForm',
					name: 'vacEditForm',
					labelAlign: 'right',
					labelWidth: 100,
					layout: 'form',
					items: [
						{
							height: 5,
							border: false
						},
						{
							id: 'implForm',
							border: false,
							layout: 'column',
							defaults: {
								columnWidth: 0.5,
								bodyBorder: false,
								labelWidth: 100,
								anchor: '100%'
							},
							bodyStyle: 'padding: 5px',
							//autohight: true,
							items: [{
									layout: 'form',
									items: [{
											fieldLabel: 'Дата исполнения',
											tabIndex: TABINDEX_MANTUIMPNPURPFRM + 11,
											allowBlank: false,
											xtype: 'swdatefield',
											format: 'd.m.Y',
											plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
											name: 'vacMantuDateImpl',
											id: 'vacMantuDateImpl'
											, listeners: {
												'blur': function (dt) {
													if (sw.Promed.vac.utils.strToDate(dt.value) < new Date()) {
														this.setAllowBlank(true);
													} else {
														this.setAllowBlank(false);
													}
													this.initVacWay();
												}.createDelegate(this)
											}
										},
										{
											allowBlank: false,
											id: 'mantu_TubDiagnosisTypeCombo',
											autoLoad: true,
											fieldLabel: 'Метод диагностики',
											tabIndex: TABINDEX_MANTUIMPNPURPFRM + 14,
											hiddenName: 'TubDiagnosisType_id',
											width: 260,
											listWidth: 260,
											xtype: 'amm_TubDiagnosisTypeCombo',
											listeners: {
												'select': function (combo, record, index) {
													//Ext.getCmp('mantu_TubDiagnosisTypeCombo').getValue()
													var $id = Ext.getCmp('mantu_TubDiagnosisTypeCombo').getValue();
													if(!$id) return false;
													Ext.getCmp('amm_mantuImplWithoutPurp').initVacSeria($id);
													//if (record.data.TubDiagnosisType_id == 1) {  //  Это манту
													if ($id == 1) {  //  Это манту    
														Ext.getCmp('ReactionSize').show();
														Ext.getCmp('ReactionSize').setFieldLabel('Реакция, [мм]');
														Ext.getCmp('mantu_DiaskinTypeReactionCombo').hide();
														Ext.getCmp('mantu_DiaskinTypeReactionCombo').setFieldLabel('');
													} else {
														Ext.getCmp('ReactionSize').hide();
														Ext.getCmp('ReactionSize').setFieldLabel('');
														Ext.getCmp('mantu_DiaskinTypeReactionCombo').show();
														Ext.getCmp('mantu_DiaskinTypeReactionCombo').setFieldLabel('Степень выраженности');

													}
													if ($id == 1)  //Это манту
														this.formParams.vaccine_id = 26;  //  Туберкулин
													else
														this.formParams.vaccine_id = 27;  // Диаскинтест
													this.initVacWay()
												}.createDelegate(this),
												change: function(combo, newValue, oldValue){
													if(!newValue) this.vacEditForm.form.findField('VaccineWayPlace_id').reset();
												}.createDelegate(this)
											}
										},
										{
											fieldLabel: 'Дата проверки',
											tabIndex: TABINDEX_MANTUIMPNPURPFRM + 12,
											xtype: 'swdatefield',
											format: 'd.m.Y',
											plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
											name: 'vacMantuDateReact'
										}, {
											autoLoad: false,
											fieldLabel: 'Доза введения',
											tabIndex: TABINDEX_MANTUIMPNPURPFRM + 13,
											name: 'VaccineDose_Name',
											width: 260,
											xtype: 'textfield'
										}, {
											allowBlank: false,
											id: 'mantu_implVaccineSeriaCombo',
											autoLoad: true,
											fieldLabel: 'Серия и срок годности',
											tabIndex: TABINDEX_MANTUIMPNPURPFRM + 14,
											hiddenName: 'VaccineSeria_id',
											width: 260,
											listWidth: 260,
											xtype: 'amm_VaccineSeriaCombo'
													// xtype: 'amm_TubDiagnosisTypeCombo'
											, listeners: {
												'select': function (combo, record, index) {
													this.vacEditForm.form.findField('cityManufacturer').setValue(
															record.get('Manufacturer')
															);
												}.createDelegate(this)
											}
										}, {
											fieldLabel: 'Изготовитель',
											tabIndex: TABINDEX_MANTUIMPNPURPFRM + 15,
											name: 'cityManufacturer',
											width: 260,
											readOnly: true,
											xtype: 'textfield'
										}, {
											allowBlank: false,
											id: 'mantu_implVaccineWayCombo',
											autoLoad: true,
											fieldLabel: 'Способ и место введения',
											hiddenName: 'VaccineWayPlace_id',
											tabIndex: TABINDEX_MANTUIMPNPURPFRM + 16,
											width: 260,
											listWidth: 400,
											//xtype: 'amm_VacWayPlaceCombo',
											xtype: 'amm_comboMethodAndPlaceOfIntroduction'
										},
										{
											listWidth: 260,
											id: 'mantu_implTypeReactionCombo',
											autoLoad: true,
											fieldLabel: 'Тип реакции',
											tabIndex: TABINDEX_MANTUIMPNPURPFRM + 17,
											hiddenName: 'TypeReaction_id',
											//                name: 'TypeReaction_name',
											width: 260,
											xtype: 'amm_TypeReactionCombo'
										},
										{
											listWidth: 260,
											id: 'mantu_DiaskinTypeReactionCombo',
											autoLoad: true,
											fieldLabel: 'Степень выраженности',
											tabIndex: TABINDEX_MANTUIMPNPURPFRM + 17,
											hiddenName: 'DiaskinTestReactionType_id',
											width: 260,
											xtype: 'amm_DiaskinTypeReactionCombo'
										},
										{
											fieldLabel: 'Описание реакции',
											height: 60,
											tabIndex: TABINDEX_MANTUIMPNPURPFRM + 18,
											name: 'JournalMantu_ReactDescription',
											id: 'mantu_JournalMantu_ReactDescription',
											width: 260,
											xtype: 'textarea'
										},
										{
											allowNegative: false,
											fieldLabel: 'Реакция, [мм]',
											tabIndex: TABINDEX_MANTUIMPNPURPFRM + 19,
											name: 'ReactionSize',
											id: 'ReactionSize',
											width: 260,
											xtype: 'numberfield'
										}]

								}, {
									layout: 'form',
									items: [{
											id: 'mantu_implLpuCombo',
											listWidth: 400,
											tabIndex: TABINDEX_MANTUIMPNPURPFRM + 21,
											width: 260,
											//                      xtype: 'swlpucombo'
											xtype: 'amm_LpuListCombo',
											listeners: {
												'select': function (combo) {
													Ext.getCmp('mantu_implLpuBuildingCombo').reset();
													Ext.getCmp('mantu_implLpuSectionCombo').reset();
													Ext.getCmp('mantu_implMedPersonalCombo').reset();
													//                    var vacMantuForm = Ext.getCmp('vacMantuEditForm');
													//                    var vacMantuForm = Ext.getCmp('implForm').find('hiddenName', 'MedStaffFact_id')[0];
													//                    vacMantuForm.form.findField('LpuBuilding_id').getStore().load({
													Ext.getCmp('implForm').find('hiddenName', 'LpuBuilding_id')[0].getStore().load({
														params: {Lpu_id: combo.getValue()}
													});
												}.createDelegate(this)
											}

										}, {
											autoHeight: true,
											style: 'margin: 5px; padding: 5px;',
											title: 'Назначил врач:',
											xtype: 'fieldset',
											width: 400,
											items: [{
													id: 'mantu_implLpuBuildingCombo',
													//lastQuery: '',
													listWidth: 400,
													linkedElements: [
														'mantu_implLpuSectionCombo'
													],
													tabIndex: TABINDEX_MANTUIMPNPURPFRM + 22,
													width: 260,
													xtype: 'swlpubuildingglobalcombo'
												}, {
													id: 'mantu_implLpuSectionCombo',
													linkedElements: [
														'mantu_implMedPersonalCombo'
														//                  ,'EPLSIF_MedPersonalMidCombo'
													],
													listWidth: 400,
													parentElementId: 'mantu_implLpuBuildingCombo',
													tabIndex: TABINDEX_MANTUIMPNPURPFRM + 23,
													width: 260,
													xtype: 'swlpusectionglobalcombo'
												}, {
													allowBlank: false,
													hiddenName: 'MedStaffFact_id',
													id: 'mantu_implMedPersonalCombo',
													parentElementId: 'mantu_implLpuSectionCombo',
													listWidth: 400,
													tabIndex: TABINDEX_MANTUIMPNPURPFRM + 24,
													width: 260,
													emptyText: VAC_EMPTY_TEXT,
													xtype: 'swmedstafffactglobalcombo'
												}]

										}, {
											xtype: 'checkbox',
											//				hideLabel: true,
											name: 'Reaction30min',
											id: 'Reaction30min',
											labelSeparator: '',
											checked: false,
											boxLabel: 'Реакция на прививку (ч/з 30 мин)'
										}]
								}]
						}
					]
				})
			]

		});
		sw.Promed.amm_mantuImplWithoutPurp.superclass.initComponent.apply(this, arguments);
	},
	show: function (record) {
		this.enableEdit(record.action != 'view');
		sw.Promed.amm_mantuImplWithoutPurp.superclass.show.apply(this, arguments);
		this.vacEditForm.getForm().reset();
		this.formParams = record;
		sw.Promed.vac.utils.consoleLog('show(amm_mantuImplWithoutPurp - record):');
		sw.Promed.vac.utils.consoleLog(record);
		var allowBlank = false;
		if ((record.add_new_mantu == 1) || (sw.Promed.vac.utils.strToDate(this.vacEditForm.form.findField('vacMantuDateImpl').value) < new Date()))
		{
			allowBlank = true;
		}
		this.setAllowBlank(allowBlank);

		this.vacEditForm.form.findField('mantu_implLpuCombo').getStore().load({
			callback: function () {
				this.vacEditForm.form.findField('mantu_implLpuCombo').setValue(getGlobalOptions().lpu_id);
			}.createDelegate(this)
		});

		this.vacEditForm.form.findField('LpuBuilding_id').getStore().load({
			params: {Lpu_id: getGlobalOptions().lpu_id}
		});

		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет загрузка формы..."});
		loadMask.show();
		this.formStore.load({
			params: {
				fix_tub_id: record.fix_tub_id
			},
			callback: function () {
				var formStoreCount = this.formStore.getCount() > 0;
				if (formStoreCount) {
					var formStoreRecord = this.formStore.getAt(0);
					if (record.add_new_mantu != 1) {
						this.formParams.vac_presence_id = formStoreRecord.get('VacPresence_id');
						this.formParams.med_staff_fact_id = formStoreRecord.get('MedPersonal_id');
						this.formParams.vac_doze = formStoreRecord.get('Dose');
						this.formParams.reaction_type = formStoreRecord.get('React_id');
						this.formParams.reaction_size = formStoreRecord.get('ReactionSize');
						this.formParams.checkbox_reaction30min = formStoreRecord.get('Reaction30min');

						this.formParams.way_place_id = formStoreRecord.get('WayPlace_id');
						this.formParams.vaccine_way_place_id = formStoreRecord.get('id_VaccineWayPlace_VaccinePlace');

						this.formParams.date_react = formStoreRecord.get('DateReact');
						this.formParams.lpu_id = formStoreRecord.get('Lpu_id');
						this.formParams.vac_presence_seria = formStoreRecord.get('VacPresence_Seria');
						this.formParams.vac_presence_period = formStoreRecord.get('VacPresence_Period');
						this.formParams.vac_presence_manufacturer = formStoreRecord.get('VacPresence_Manufacturer');
						this.formParams.vac_seria_txt = formStoreRecord.get('JournalMantu_Seria');
						this.formParams.tub_diagnosis_type_id = formStoreRecord.get('TubDiagnosisType_id');
						this.formParams.diaskin_type_reaction = formStoreRecord.get('DiaskinTestReactionType_id');
						this.formParams.JournalMantu_ReactDescription = formStoreRecord.get('JournalMantu_ReactDescription');

					} else //"Добавление манту"
					{
						delete record.fix_tub_id;
						delete this.formParams.plan_tub_id;
						delete this.formParams.fix_tub_id;
						//this.formParams.tub_diagnosis_type_id = 1;
						delete this.formParams.tub_diagnosis_type_id
						delete this.formParams.diaskin_type_reaction;
					}
					;
					this.formParams.status_type_id = formStoreRecord.get('StatusType_id');
					if (formStoreRecord.get('DatePurpose'))
						this.formParams.date_purpose = formStoreRecord.get('DatePurpose');
					this.formParams.date_impl = formStoreRecord.get('DateVac');
					this.formParams.person_id = formStoreRecord.get('Person_id');
					this.formParams.birthday = formStoreRecord.get('person_BirthDay');
				}
				;
				this.vacEditForm.form.findField('mantu_TubDiagnosisTypeCombo').getStore().load({
					callback: function () {
						var mantuTubDiagnosisTypeCombo = this.vacEditForm.form.findField('mantu_TubDiagnosisTypeCombo');
						if(this.formParams.tub_diagnosis_type_id && mantuTubDiagnosisTypeCombo.findRecord('TubDiagnosisType_id', this.formParams.tub_diagnosis_type_id)){
							this.vacEditForm.form.findField('mantu_TubDiagnosisTypeCombo').setValue(this.formParams.tub_diagnosis_type_id);
							Ext.getCmp('mantu_TubDiagnosisTypeCombo').fireEvent('select');
						}
					}.createDelegate(this)
				});
				var $type = this.formParams.diaskin_type_reaction;
				Ext.getCmp('mantu_DiaskinTypeReactionCombo').getStore().load({
					callback: function ()
					{
						if ($type != undefined)
						{
							Ext.getCmp('mantu_DiaskinTypeReactionCombo').setValue($type);
						}
					}
				});
				if (record.add_new_mantu == 1)
					this.formParams.add_new_mantu = record.add_new_mantu;

				//контроль диапазона дат:
				this.validateVacImplementDate.init(function (o) {
					var resObj = {};
					if (o.birthday != undefined)
						resObj.personBirthday = o.birthday;
					return resObj;
				}(this.formParams));
				this.validateVacImplementDate.getMinDate();
				this.validateVacImplementDate.getMaxDate();

				this.PersonInfoPanel.load({
					callback: function () {
						this.PersonInfoPanel.setPersonTitle();
						var Person_deadDT = Ext.getCmp('amm_mantuImplWithoutPurp').PersonInfoPanel.getFieldValue('Person_deadDT');
						if (Person_deadDT != undefined) {
							Ext.getCmp('vacMantuDateImpl').setMaxValue(Person_deadDT);
						}

						this.vacEditForm.getForm().findField('VaccineDose_Name').setValue(this.formParams.vac_doze);
						this.vacEditForm.getForm().findField('ReactionSize').setValue(this.formParams.reaction_size);
						this.vacEditForm.getForm().findField('Reaction30min').setValue(this.formParams.checkbox_reaction30min);
						this.vacEditForm.getForm().findField('JournalMantu_ReactDescription').setValue(this.formParams.JournalMantu_ReactDescription);
						switch (record.source) {
							case 'TubPlan':
								this.vacEditForm.getForm().findField('vacMantuDateReact').reset();
								break;
							case 'TubAssigned':
								this.vacEditForm.getForm().findField('vacMantuDateReact').reset();
								break;
							case 'TubReaction':
								this.vacEditForm.getForm().findField('vacMantuDateReact').reset();
								this.vacEditForm.getForm().findField('vacMantuDateReact').setValue(this.formParams.date_react);
								break;
							default:
								this.vacEditForm.getForm().findField('vacMantuDateReact').reset();
								break;
						}

						var newDateVal;
						if (this.formParams.status_type_id == 0) {
							if (record.add_new_mantu == 1)
								newDateVal = sw.Promed.vac.utils.yearAdd(this.formParams.date_purpose, 1);
							else
								newDateVal = this.formParams.date_purpose;
						} else if (this.formParams.status_type_id == 1) {
							if (record.add_new_mantu == 1)
								newDateVal = sw.Promed.vac.utils.yearAdd(this.formParams.date_impl, 1);
							else
								newDateVal = this.formParams.date_impl;
						} else
						{
							newDateVal = new Date;
						}

						if (this.formParams.date_purpose != undefined)
						{
							Ext.getCmp('vacMantuDateImpl').setValue(this.formParams.date_purpose)
						} else
							this.vacEditForm.form.findField('vacMantuDateImpl').setValue(newDateVal);

						this.vacEditForm.form.findField('vacMantuDateImpl').fireEvent('blur', this.vacEditForm.form.findField('vacMantuDateImpl'));

						this.vacEditForm.form.findField('LpuSection_id').getStore().load({
							callback: function () {
								this.vacEditForm.form.findField('MedStaffFact_id').getStore().load({
									callback: function () {
										if (this.formParams.status_type_id == 1) {
											this.vacEditForm.form.findField('MedStaffFact_id').setValue(this.formParams.med_staff_fact_id);
										} else {
											if (getGlobalOptions().medstafffact[0]) {
												this.vacEditForm.form.findField('MedStaffFact_id').setValue(getGlobalOptions().medstafffact[0]);
											}
										}

										var comboReact = this.vacEditForm.form.findField('TypeReaction_id');
										this.vacEditForm.form.findField('TypeReaction_id').getStore().load({
											callback: function () {
												if ((this.formParams.reaction_type != undefined) && (this.formParams.reaction_type != 0)) {
													comboReact.setValue(this.formParams.reaction_type);
												}
												loadMask.hide();
											}.createDelegate(this)
										});
									}.createDelegate(this)
								});
							}.createDelegate(this)
						});

					}.createDelegate(this),
					loadFromDB: true,
					Person_id: this.formParams.person_id,
					Server_id: this.formParams.Server_id
				});
			}.createDelegate(this)
		});

	}
});
