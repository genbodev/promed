/**
 * ReceiptFormPanel - форма добавления рецепта за полную стоимость
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common.Admin
 * @access       public
 * @copyright    Copyright (c) 2018 Swan Ltd.
 */
Ext6.define('common.EMK.SpecificationDetail.ReceiptFormPanel', {
	/* свойства */
	title: 'РЕЦЕПТ ЗА ПОЛНУЮ СТОИМОСТЬ',
	extend: 'swPanel',
	frame: true,
	autoHeight: true,
	width: '100%',
	border: false,
	defaults: {
		border: false,
		width: '100%',
		labelWidth: 120
	},
	bodyPadding: '15 21 10 0',
	userCls: 'mode-of-application evn-course-treat-full-prise-edit',
	layout: {
		type: 'vbox'
	},
	parentCntr: {},
	printRecept: function(EvnReceptGeneral_id) {
		var me = this,
			receiptForm = me.ReceiptForm.getForm();
		if (!receiptForm)
			return false;

		if (Ext6.isEmpty(EvnReceptGeneral_id))
			EvnReceptGeneral_id = receiptForm.findField('EvnReceptGeneral_id').getValue();

		if (Ext6.isEmpty(EvnReceptGeneral_id)) {
			sw.swMsg.show({
				buttons: Ext6.Msg.YESNO,
				fn: function(buttonId, text, obj) {
					if (buttonId == 'yes') {
						me.parentPanel.doSave(true);
					}
				},
				icon: Ext6.MessageBox.QUESTION,
				msg: langs('При печати рецепта будет сохранено лекарственное назначение. Продолжить?'),
				title: langs('Подтверждение')
			});

		}
		else {
			var ReceptForm_id = receiptForm.findField('ReceptForm_id').getValue();
			var EvnReceptGeneral_setDate = !Ext6.isEmpty(receiptForm.findField('EvnReceptGeneral_setDate').getValue()) ? receiptForm.findField('EvnReceptGeneral_setDate').getValue().format('Y-m-d') : null;
			Ext.Ajax.request({
				url: '/?c=EvnRecept&m=saveEvnReceptGeneralIsPrinted',
				params: {
					EvnReceptGeneral_id: EvnReceptGeneral_id
				},
				callback: function () {

					if (getRegionNick() == 'kz') {
						printBirt({
							'Report_FileName': 'EvnReceptMoney_print.rptdesign',
							'Report_Params': '&paramEvnRecept=' + EvnReceptGeneral_id,
							'Report_Format': 'pdf'
						});
						printBirt({
							'Report_FileName': 'EvnReceptMoney_Oborot_print.rptdesign',
							'Report_Params': '&paramEvnRecept=' + EvnReceptGeneral_id,
							'Report_Format': 'pdf'
						});
					}
					else {
						switch(ReceptForm_id){
							case 2:
								printBirt({
									'Report_FileName': 'EvnReceptGenprint_1MI.rptdesign',
									'Report_Params': '&paramEvnRecept=' + EvnReceptGeneral_id,
									'Report_Format': 'pdf'
								});
								break;
							case 3:
								if (!Ext6.isEmpty(EvnReceptGeneral_setDate) && EvnReceptGeneral_setDate > '2019-04-06') {
								printBirt({
										'Report_FileName': 'EvnReceptGenprint2_new.rptdesign',
										'Report_Params': '&paramEvnRecept=' + EvnReceptGeneral_id,
										'Report_Format': 'pdf'
									});
									printBirt({
										'Report_FileName': 'EvnReceptGenPrintOb_new.rptdesign',
										'Report_Params': '',
										'Report_Format': 'pdf'
									});
								}
								else {
									printBirt({
									'Report_FileName': 'EvnReceptGenprint2.rptdesign',
									'Report_Params': '&paramEvnRecept=' + EvnReceptGeneral_id,
									'Report_Format': 'pdf'
								});
								printBirt({
									'Report_FileName': 'EvnReceptGenPrintOb.rptdesign',
									'Report_Params': '',
									'Report_Format': 'pdf'
								});
								}
								break;
							case 5: //148-1/у-88
								if (!Ext6.isEmpty(EvnReceptGeneral_setDate) && EvnReceptGeneral_setDate > '2019-04-07') {
									printBirt({
										'Report_FileName': 'EvnReceptGenprint_2019.rptdesign',
										'Report_Params': '&paramEvnRecept=' + EvnReceptGeneral_id,
										'Report_Format': 'pdf'
									});
									printBirt({
										'Report_FileName': 'EvnReceptGenPrintOb_2019.rptdesign',
										'Report_Params': '',
										'Report_Format': 'pdf'
									});
									break;
								}
							default:
								printBirt({
									'Report_FileName': 'EvnReceptGenprint.rptdesign',
									'Report_Params': '&paramEvnRecept=' + EvnReceptGeneral_id,
									'Report_Format': 'pdf'
								});
								printBirt({
									'Report_FileName': 'EvnReceptGenPrintOb.rptdesign',
									'Report_Params': '',
									'Report_Format': 'pdf'
								});
						}
					}
				}
			});
		}
	},
	filterMedStaffFactCombo: function(v) {
		var me = this;
		var base_form = me.ReceiptForm.getForm();
		var msf = me.data.userMedStaffFact;

		var medstafffact_filter_params = {
			allowLowLevel: 'yes',
			isPolka: true
		};

		if (!Ext6.isEmpty(base_form.findField('EvnReceptGeneral_setDate').getValue())) {
			medstafffact_filter_params.onDate = base_form.findField('EvnReceptGeneral_setDate').getValue().format('d.m.Y');
		}

		// Фильтр на конкретное место работы
		if (!Ext6.isEmpty(msf.LpuSection_id) && !Ext6.isEmpty(msf.MedStaffFact_id)) {
			if (msf.MedStaffFactCache_IsDisableInDoc == 2) {
				sw.swMsg.alert(langs('Сообщение'), langs('Текущее рабочее место запрещено для выбора в документах'));
				medstafffact_filter_params.id = -1;
			}
			medstafffact_filter_params.id = msf.MedStaffFact_id;
		}

		medstafffact_filter_params.allowDuplacateMSF = true;
		medstafffact_filter_params.EvnClass_SysNick = 'EvnVizit';

		setMedStaffFactGlobalStoreFilter(medstafffact_filter_params, sw4.swMedStaffFactGlobalStore);
		base_form.findField('MedStaffFact_id').getStore().loadData(sw4.getStoreRecords(sw4.swMedStaffFactGlobalStore));
		if(v){
			base_form.findField('MedStaffFact_id').setValue(v);
		}
	},
	filterLpuSectionCombo: function () {
		var me = this;
		var base_form = me.ReceiptForm.getForm(),
			LpuSectionCombo = base_form.findField('LpuSection_id'),
			LpuSection_id = base_form.findField('LpuSection_id').getValue();
		//LpuSectionProfile_Code = base_form.findField('LpuSectionProfile_id').getFieldValue('LpuSectionProfile_Code');


		if (!LpuSectionCombo.isVisible()) {
			return false;
		}

		LpuSectionCombo.getStore().clearFilter();
		LpuSectionCombo.lastQuery = '';

		var setComboValue = function (combo, id) {
			if (Ext6.isEmpty(id)) {
				return false;
			}

			var index = combo.getStore().findBy(function (rec) {
				return (rec.get('LpuSection_id') == id);
			});

			if (index == -1 && combo.isVisible()) {
				combo.clearValue();
			}
			else {
				combo.setValue(id);
			}

			return true;
		}

		//if (base_form.findField('Lpu_id').getValue() == getGlobalOptions().lpu_id) {
		setLpuSectionGlobalStoreFilter({
			isOnlyStac: true,
			/*mode: 'combo',
			Lpu_id: base_form.findField('Lpu_did').getValue()*/
			//lpuSectionProfileCode: LpuSectionProfile_Code
		});
		LpuSectionCombo.getStore().loadData(sw4.getStoreRecords(sw4.swLpuSectionGlobalStore));
		setComboValue(LpuSectionCombo, LpuSection_id);
		/*}
		else {
			LpuSectionCombo.getStore().load({
				params: {
					mode: 'combo',
					Lpu_id: base_form.findField('Lpu_id').getValue()
				},
				callback: function () {
					setComboValue(LpuSectionCombo, LpuSection_id);
				}
			});
		}*/
	},
	setValuesGeneralReceiptForm: function(values){

		var me = this,
			data = me.data,
			form = me.ReceiptForm.getForm(),
			lpu_s = form.findField('LpuSection_id'),
			hasvk = form.findField('EvnReceptGeneral_hasVK'),
			receptform = form.findField('ReceptForm_id'),
			Drug_Kolvo_Pack = 1,
			diag_id = values.Diag_id?values.Diag_id:me.parentCntr.getData().Diag_id;
		if(values.Drug_Kolvo_Pack)
			Drug_Kolvo_Pack = values.Drug_Kolvo_Pack;
		else
			Drug_Kolvo_Pack = (values.KolvoEd*values.EvnCourseTreat_CountDay*values.EvnCourseTreat_Duration);
		if(Ext6.isEmpty(diag_id) && me.parentCntr)
			diag_id = me.parentCntr.getDiagId();

		if (Ext6.isEmpty(values.EvnReceptGeneral_hasVK)) {
			values.EvnReceptGeneral_hasVK = 1;
		}

		var params = {
			"EvnReceptGeneral_setDate": values.EvnCourseTreat_setDate,
			"Lpu_id": data.userMedStaffFact.Lpu_id,
			"MedPersonal_id": data.MedPersonal_id,
			"MedPersonal_Name":data.userMedStaffFact.MedPersonal_FIO,
			"MedStaffFact_id": data.userMedStaffFact.MedStaffFact_id,
			"LpuSection_id": data.userMedStaffFact.LpuSection_id,
			"LpuSection_Name": data.userMedStaffFact.LpuSection_Name,
			"Diag_id": diag_id?parseInt(diag_id):'',
			"EvnReceptGeneral_Ser":values.EvnReceptGeneral_Ser,
			"EvnReceptGeneral_Num":values.EvnReceptGeneral_Num,
			"EvnReceptGeneral_hasVK":values.EvnReceptGeneral_hasVK,
			"EvnReceptGeneral_VKProtocolNum":values.EvnReceptGeneral_VKProtocolNum,
			"EvnReceptGeneral_VKProtocolDT":values.EvnReceptGeneral_VKProtocolDT,
			"CauseVK_id":values.CauseVK_id,
			"EvnReceptGeneral_id":values.EvnReceptGeneral_id,
			"EvnReceptGeneralDrugLink_id":values.EvnReceptGeneralDrugLink_id,
			"EvnCourseTreatDrug_id":values.id,
			"Drug_Name":values.Drug_Name,
			// (кол-во лс на прием)*(Приёмов в сутки)*(Продолжительность)*тип продолжительности / видимо надо разделить на кол-во в упаковке
			"Drug_Kolvo_Pack":Drug_Kolvo_Pack, // @todo проверить вычисления
			"Drug_Fas":values.Drug_Fas, // @todo проверить вычисления
			"Drug_Signa":values.Drug_Signa,
			// Параметры по умолчанию
			"ReceptType_id":2, // на листе
			"ReceptForm_id":values.ReceptForm_id // форма одна из трёх платных (107, МИ, 144)
		};
		lpu_s.fireEvent('change', lpu_s, params.LpuSection_id);
		hasvk.fireEvent('change', hasvk, params.EvnReceptGeneral_hasVK);
		receptform.fireEvent('change', receptform, params.ReceptForm_id);
		params = Ext6.Object.merge(values,params); // Вдруг какие поля не пришли/забыты
		form.setValues(params);
	},
	getSerNumFormReceipt: function(values, cbFn){
		var date = values.EvnCourseTreat_setDate,
			ReceptType_id = values.ReceptType_id,
			ReceptForm_id = 3, //Новый рецепт по форме 107-1/у
			url = '/?c=EvnRecept&m=checkBeforeCreateEvnReceptGeneral',
			genParams = {};
		if(!isNaN(values.id) && parseInt(values.id) > 0){
			genParams.EvnCourseTreatDrug_id = values.id;
		}
		else{
			url = '/?c=EvnRecept&m=checkFormEvnReceptGeneral';
			if(!isNaN(values.Drug_id) && parseInt(values.Drug_id) > 0)
				genParams.Drug_id = values.Drug_id;
			if(!isNaN(values.DrugComplexMnn_id) && parseInt(values.DrugComplexMnn_id) > 0)
				genParams.DrugComplexMnn_id = values.DrugComplexMnn_id;
		}

		Ext6.Ajax.request({
			url: url,
			params: genParams,
			callback: function(opt, success, response) {
				if (success && response.responseText != '')
				{
					var response_obj = Ext.util.JSON.decode(response.responseText);
					if(response_obj && response_obj[0]){
						if(response_obj[0].mi)
							ReceptForm_id = 2; // @todo а если id изменится?
						if(response_obj[0].narco)
							ReceptForm_id = 5; // @todo а если id изменится?
					}
				}

				Ext6.Ajax.request({
					params: {
						isGeneral: 1,
						ReceptForm_id: ReceptForm_id,
						ReceptType_id: ReceptType_id,
						EvnRecept_setDate: date
					},
					callback: function(options, success, response) {
						if ( success ) {
							var response_obj = Ext.util.JSON.decode(response.responseText);
							var recept_Num = (response_obj.EvnRecept_Num) ? response_obj.EvnRecept_Num : '';
							var recept_Ser = (response_obj.EvnRecept_Ser) ? response_obj.EvnRecept_Ser : '';

							cbFn(recept_Ser,recept_Num,ReceptForm_id);
						}
						else {
							sw.swMsg.alert('Ошибка', 'Ошибка при определении номера рецепта', function() {}.createDelegate(this) );
						}
					}.createDelegate(this),
					url: C_RECEPT_NUM
				});
			}
		});
	},
	getNewSerNum: function() {
		var me = this,
			base_form = me.ReceiptForm.getForm(),
			values = base_form.getValues();

		Ext6.Ajax.request({
			params: {
				isGeneral: 1,
				ReceptForm_id: values.ReceptForm_id,
				ReceptType_id: values.ReceptType_id,
				EvnRecept_setDate: values.EvnCourseTreat_setDate
			},
			callback: function (options, success, response) {
				if (success) {
					var response_obj = Ext.util.JSON.decode(response.responseText);
					var recept_Num = (response_obj.EvnRecept_Num) ? response_obj.EvnRecept_Num : '';
					var recept_Ser = (response_obj.EvnRecept_Ser) ? response_obj.EvnRecept_Ser : '';
					var newVal = {};

					newVal.EvnReceptGeneral_Ser = recept_Ser.toString();
					newVal.EvnReceptGeneral_Num = recept_Num.toString();
					base_form.setValues(newVal);
				}
				else {
					sw.swMsg.alert('Ошибка', 'Ошибка при определении номера рецепта', function () {
					}.createDelegate(this));
				}
			}.createDelegate(this),
			url: C_RECEPT_NUM
		});
	},
	initComponent: function() {
		var me = this;

		this.ReceiptForm = new Ext6.form.FormPanel({
			border: false,
			layout: {
				type: 'vbox'
			},
			defaults: {
				border: false,
				width: '100%',
				labelWidth: 120,
				margin: '0 0 5 28'
			},
			itemId: 'ReceptGeneralForm',
			padding: '0 0 0 0',
			margin: '0 0 17 0',
			/*reader: Ext6.create('Ext6.data.reader.Json', {
				type: 'json',
				model: win.id + '_ReceiptFormModel'
			}),*/
			url: '/?c=EvnRecept&m=saveEvnReceptRls',
			items: [
				{
					xtype: 'hidden',
					name: 'EvnReceptGeneral_id'
				},
				{
					xtype: 'hiddenfield',
					name: 'EvnReceptGeneralDrugLink_id'
				},
				{
					xtype: 'datefield',
					allowBlank: false,
					format: 'd.m.Y',
					plugins: [new Ext6.ux.InputTextMask('99.99.9999', false)],
					fieldLabel: 'Дата',
					maxWidth: 231,
					name: 'EvnReceptGeneral_setDate',
					listeners: {
						'change': function() {
							me.filterMedStaffFactCombo();
							me.getNewSerNum();
						}
					}
				},
				{
					layout: {
						type: 'hbox'
					},
					margin: '0 0 0 28',
					defaults: {
						border: false
					},
					items: [
						{
							flex: 3,
							defaults: {
								border: false,
								labelWidth: 120
							},
							layout: {
								type: 'vbox'
							},
							items: [
								{
									width: 435,
									disabled: true,
									displayCode: false,
									xtype: 'swReceptGenFormCombo',
									name: 'ReceptForm_id',
									listeners: {
										'change': function(combo, newValue, oldValue) {
											var base_form = me.ReceiptForm.getForm();
											if (newValue && newValue.toString().inlist(['3', '5', '8'])) {
												base_form.findField('EvnReceptGeneral_hasVK').showContainer();
												base_form.findField('EvnReceptGeneral_hasVK').setAllowBlank(false);
											} else {
												base_form.findField('EvnReceptGeneral_hasVK').hideContainer();
												base_form.findField('EvnReceptGeneral_hasVK').setAllowBlank(true);
												base_form.findField('EvnReceptGeneral_hasVK').setValue(1);
												base_form.findField('EvnReceptGeneral_hasVK').fireEvent('change', base_form.findField('EvnReceptGeneral_hasVK'), base_form.findField('EvnReceptGeneral_hasVK').getValue());
											}
										}
									}
								},
								{
									width: '100%',
									defaults: {
										labelWidth: 120
									},
									layout: {
										type: 'hbox'
									},
									items: [
										{
											disabled: true,
											width: 231,
											fieldLabel: 'Серия',
											hideTrigger: true,
											xtype: 'textfield',
											name: 'EvnReceptGeneral_Ser',
											margin: '0 50 0 0'
										},
										{
											disabled: true,
											width: 154,
											fieldLabel: 'Номер',
											labelWidth: 45,
											hideTrigger: true,
											xtype: 'textfield',
											name: 'EvnReceptGeneral_Num'
										}
									]
								}
							]
						},
						{
							flex: 2,
							layout: {
								type: 'vbox'
							},
							defaults: {
								border: false,
								labelWidth: 110
							},
							items: [
								{
									width: '100%',
									fieldLabel: 'Тип рецепта',
									displayCode: false,
									xtype: 'commonSprCombo',
									comboSubject: 'ReceptType',
									name: 'ReceptType_id',
									listeners: {
										'select': function (combo, record, eOpts) {
											let base_form = me.ReceiptForm.getForm();
											var receptForm = base_form.findField('ReceptForm_id');
											var receptGeneralSer = base_form.findField('EvnReceptGeneral_Ser');
											var receptGeneralNum = base_form.findField('EvnReceptGeneral_Num');
											if(record.get('ReceptType_id') == 1) {
												receptForm.enable();
												receptGeneralSer.enable();
												receptGeneralNum.enable();
											} else {
												receptForm.disable();
												receptGeneralSer.disable();
												receptGeneralNum.disable();

												me.getNewSerNum();
											}
										}
									}
								},
								{
									width: '100%',
									fieldLabel: 'Срок действия',
									displayCode: false,
									name: 'ReceptValid_id',
									xtype: 'commonSprCombo',
									comboSubject: 'ReceptValid'
								}
							]
						}
					]
				},
				{
					xtype: 'swLpuCombo',
					additionalRecord: {
						value: -1,
						text: langs('Все'),
						code: 0
					},
					anyMatch: true,
					hideEmptyRow: true,
					listConfig:{
						minWidth: 500
					},
					fieldLabel: 'МО',
					name: 'Lpu_id',
					/*'change': function(combo, newValue, oldValue) {
						win.filterLpuSectionCombo();
					}*/
				},
				{
					allowBlank: false,
					fieldLabel: 'Отделение',
					name: 'LpuSection_id',
					itemId: 'LpuSectionCombo',
					xtype: 'SwLpuSectionGlobalCombo',
					listeners: {
						'change': function(combo, newValue, oldValue) {
							me.filterLpuSectionCombo();/*
							var base_form = parentWindow.FormPanel.getForm();
							var MedStaffFactFilterParams = {
								allowLowLevel: 'yes',
								//onDate:
							};
							MedStaffFactFilterParams.LpuSection_id = newValue;
							setMedStaffFactGlobalStoreFilter(MedStaffFactFilterParams);
							base_form.findField('MedStaffFact_id').setValue('');
							base_form.findField('MedStaffFact_id').getStore().removeAll();
							base_form.findField('MedStaffFact_id').getStore().loadData(sw4.getStoreRecords(sw4.swMedStaffFactGlobalStore));
						*/
						}
					}
				},
				{
					xtype: 'swMedStaffFactCombo',
					fieldLabel: 'Врач',
					name: 'MedStaffFact_id',
					listeners: {
						'change': function(comp, newValue, oldValue) {
							me.filterMedStaffFactCombo(newValue);
						}
					}
				},
				{
					xtype: 'swDiagCombo',
					userCls: 'diagnoz',
					fieldLabel: 'Диагноз',
					name: 'Diag_id'
				},
				{
					xtype: 'checkbox',
					fieldLabel: 'Выдан уполномоченному лицу',
					name: 'EvnReceptGeneral_IsDelivery'
				}, {
					comboSubject: 'YesNo',
					fieldLabel: 'Протокол ВК',
					listeners: {
						'change': function(combo, newValue, oldValue) {
							var base_form = me.ReceiptForm.getForm();
							base_form.findField('EvnReceptGeneral_VKProtocolNum').setContainerVisible(newValue == 2);
							base_form.findField('EvnReceptGeneral_VKProtocolNum').setAllowBlank(newValue != 2);
							base_form.findField('EvnReceptGeneral_VKProtocolDT').setContainerVisible(newValue == 2);
							base_form.findField('EvnReceptGeneral_VKProtocolDT').setAllowBlank(newValue != 2);
							base_form.findField('CauseVK_id').setContainerVisible(newValue == 2);
							base_form.findField('CauseVK_id').setAllowBlank(newValue != 2);
						}
					},
					name: 'EvnReceptGeneral_hasVK',
					value: 1,
					xtype: 'commonSprCombo'
				}, {
					defaults: {
						border: false,
						labelWidth: 120
					},
					style: {
						marginBottom: '1px'
					},
					layout: {
						type: 'hbox'
					},
					items: [
						{
							fieldLabel: '№ протокола ВК',
							name: 'EvnReceptGeneral_VKProtocolNum',
							maxWidth: 231,
							xtype: 'textfield'
						}, {
							fieldLabel: 'Дата протокола ВК',
							format: 'd.m.Y',
							name: 'EvnReceptGeneral_VKProtocolDT',
							plugins: [new Ext6.ux.InputTextMask('99.99.9999', false)],
							maxWidth: 260,
							labelWidth: 150,
							labelAlign: 'right',
							xtype: 'datefield'
						}
					]
				}, {
					comboSubject: 'CauseVK',
					fieldLabel: 'Основание для проведения ВК',
					name: 'CauseVK_id',
					userCls: 'CauseVK_id',
					height: 27,
					maxHeight: 27,
					xtype: 'commonSprCombo',
					filterFn: function(rec) {
						if(typeof rec.get == 'function') {
							if (rec.get('CauseVK_Code') == '2') {
								return false;
							} else {
								return true;
							}
						}
					}
				}, {
					xtype: 'checkbox',
					style: {
						marginTop: '5px',
						marginLeft: '123px'
					},
					hidden: true,
					boxLabel: 'Рецепт выдан уполномоченному лицу'
				}, {
					xtype: 'fieldset',
					title: 'Медикамент',
					padding: '0 0 0 0',
					margin: 0,
					layout: 'anchor',
					defaults: {
						border: false,
						anchor: '100%',
						margin: '0 0 0 28',
						labelWidth: 120
					},
					collapsible: true,
					collapsed: false,
					items: [
						{
							name: 'EvnReceptGeneralDrugLink_id',
							value: 0,
							xtype: 'hidden'
						},
						{
							name: 'Drug_Name',
							disabled: true,
							margin: '0 0 5 28',
							fieldLabel: 'Наименование',
							width: 517,
							tabIndex: TABINDEX_EVNPRESCR + 122,
							xtype: 'textfield'
						},
						{
							width: '100%',
							layout: {
								type: 'hbox'
							},
							defaults: {
								border: false,
								labelWidth: 120
							},
							items: [
								{
									width: 231,
									allowBlank: true,
									allowNegative: false,
									fieldLabel: 'Кол-во (уп.)',
									minValue: 0.01,
									name: 'Drug_Kolvo_Pack',
									hideTrigger: true,
									validateOnBlur: true,
									margin: '0 15 5 0',
									listeners: {
										'change': function (cmp, value) {
											/*var base_form = wnd.findById('EvnReceptGeneralEditForm').getForm();
											if(!Ext.isEmpty(value))
											{
												base_form.findField('Drug_Fas0').setValue(base_form.findField('Drug_Fas_0').getValue() * value);
											}
											else
												base_form.findField('Drug_Fas0').setValue('');*/
										}
									},
									xtype: 'numberfield'
								},
								{
									allowBlank: true,
									width: 194,
									allowNegative: false,
									labelWidth: 85,
									disabled: true,
									fieldLabel: 'Кол-во (доз.)',
									hideTrigger: true,
									minValue: 0.01,
									name: 'Drug_Fas',
									validateOnBlur: true,
									//value: 1,
									xtype: 'numberfield'
								}
							]
						},
						{
							allowBlank: true,
							fieldLabel: 'Signa',
							name: 'Drug_Signa',
							validateOnBlur: true,
							width: 517,
							xtype: 'textfield',
							bodyStyle: 'margin-top: 50px;'
						}
					]
				}
			]
		});

		Ext6.apply(me, {
			tools: [
				{
					type: 'receipt-print',
					userCls: 'sw-tool',
					tooltip: 'Печать',
					margin: '0 11 0 11',
					width: 16,
					handler: function () {
						me.printRecept();
					}
				}
			],
			items: [me.ReceiptForm]
		});

		this.callParent(arguments);
	}
});

