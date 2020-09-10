/**
* swEvnPLDispScreenChildEditWindow - окно редактирования/добавления скринингового исследования
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package		Polka
* @access		public
* @copyright	Copyright (c) 2015 Swan Ltd.
* @author		Dmitry Vlasenko
* @comment		Префикс для id компонентов EPLDSCEF (EvnPLDispScreenChildEditForm)
*
*/
/*NO PARSE JSON*/
sw.Promed.swEvnPLDispScreenChildEditWindow = Ext.extend(sw.Promed.BaseForm, {
	action: 'add',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	collapsible: true,
	/* */
	codeRefresh: true,
	objectName: 'swEvnPLDispScreenChildEditWindow',
	objectSrc: '/jscore/Forms/Polka/swEvnPLDispScreenChildEditWindow.js',
	draggable: true,
	getDataForCallBack: function()
	{
		var win = this;
		var base_form = win.EvnPLDispScreenChildFormPanel.getForm();
		var personinfo = win.PersonInfoPanel;

		var response = new Object();

		var EvnUslugaDispDop_minDate, EvnUslugaDispDop_maxDate;
		this.evnUslugaDispDopGrid.getGrid().getStore().each(function(rec) {
			if ( !Ext.isEmpty(rec.get('EvnUslugaDispDop_setDate')) ) {
				if ( Ext.isEmpty(EvnUslugaDispDop_minDate) || EvnUslugaDispDop_minDate > rec.get('EvnUslugaDispDop_setDate') ) {
					EvnUslugaDispDop_minDate = rec.get('EvnUslugaDispDop_setDate');
				}

				if ( Ext.isEmpty(EvnUslugaDispDop_maxDate) || EvnUslugaDispDop_maxDate < rec.get('EvnUslugaDispDop_setDate') ) {
					EvnUslugaDispDop_maxDate = rec.get('EvnUslugaDispDop_setDate');
				}
			}
		});

		response.EvnPLDispScreenChild_id = base_form.findField('EvnPLDispScreenChild_id').getValue();
		response.Person_id = base_form.findField('Person_id').getValue();
		response.Server_id = base_form.findField('Server_id').getValue();
		response.Person_Surname = personinfo.getFieldValue('Person_Surname');
		response.Person_Firname = personinfo.getFieldValue('Person_Firname');
		response.Person_Secname = personinfo.getFieldValue('Person_Secname');
		response.Person_Birthday = personinfo.getFieldValue('Person_Birthday');
		response.Sex_Name = personinfo.getFieldValue('Sex_Name');
		response.AgeGroupDisp_Name = base_form.findField('AgeGroupDisp_id').getFieldValue('AgeGroupDisp_Name');
		response.EvnPLDispScreenChild_setDate = typeof EvnUslugaDispDop_minDate == 'object' ? EvnUslugaDispDop_minDate : null;
		response.EvnPLDispScreenChild_disDate = typeof EvnUslugaDispDop_maxDate == 'object' && base_form.findField('EvnPLDispScreenChild_IsEndStage').getValue() == 2 ? EvnUslugaDispDop_maxDate : null;
		response.EvnPLDispScreenChild_IsEndStage = (base_form.findField('EvnPLDispScreenChild_IsEndStage').getValue() == 2) ? langs('Да'):langs('Нет');

		log(response);
		return response;
	},
	verfGroup: function(){
		var win = this;
		var base_form = win.EvnPLDispScreenChildFormPanel.getForm();
		if ( base_form.findField('EvnPLDispScreenChild_IsEndStage').getValue() == 2 ) {
			// Проверка на Группу здоровья
			base_form.findField('HealthKind_id').setAllowBlank(false);
		}else{
			base_form.findField('HealthKind_id').setAllowBlank(true);
		}
	},
	doSave: function(options) {
		if ( typeof options != 'object' ) {
			options = new Object();
		}

		var win = this;
		var base_form = win.EvnPLDispScreenChildFormPanel.getForm();

		if ( !base_form.isValid() )
		{
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					win.EvnPLDispScreenChildFormPanel.getFirstInvalidEl().focus(false);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		var params = new Object();

		if (base_form.findField('AgeGroupDisp_id').disabled) {
			params.AgeGroupDisp_id = base_form.findField('AgeGroupDisp_id').getValue();
		}

		if (base_form.findField('EvnPLDispScreenChild_IsLowWeight').disabled) {
			params.EvnPLDispScreenChild_IsLowWeight = base_form.findField('EvnPLDispScreenChild_IsLowWeight').getValue();
		}

		var riskFactorTypeSelected = [];
		var riskFactorTypeFieldset = this.findById('EPLDSCEF_RiskFactorTypeFieldset');
		riskFactorTypeFieldset.items.items.forEach( function (item) {
			if (item.checked) {
				riskFactorTypeSelected.push(item.value);
			}
		});
		params.RiskFactorTypeData = Ext.util.JSON.encode(riskFactorTypeSelected);

		if (options.ignoreEvnPLDispScreenChildExists) {
			params.ignoreEvnPLDispScreenChildExists = 1;
		}
		
		params.EvnPLDispScreenChild_IsInvalid = base_form.findField('EvnPLDispScreenChild_IsInvalid').getValue();

		win.getLoadMask("Подождите, идет сохранение...").show();
		base_form.submit({
			failure: function(result_form, action) {
				win.getLoadMask().hide()
			},
			params: params,
			success: function(result_form, action) {

				if (action.result)
				{
					if (action.result.Alert_Code) {
						win.getLoadMask().hide();
						sw.swMsg.show({
							buttons: Ext.Msg.YESNO,
							fn: function(buttonId, text, obj) {
								if ( buttonId == 'yes' ) {
									if (action.result.Alert_Code == 105) {
										options.ignoreEvnPLDispScreenChildExists = 1;
									}

									win.doSave(options);
								}
							}.createDelegate(this),
							icon: Ext.MessageBox.QUESTION,
							msg: action.result.Alert_Msg,
							title: 'Продолжить сохранение?'
						});
					} else {
						
						Ext.Ajax.request({
							params: {
								EvnPLDispScreenChild_id: action.result.EvnPLDispScreenChild_id,
								EvnPLDispScreen_IsEndStage: base_form.findField('EvnPLDispScreenChild_IsEndStage').getValue()
							},
							url: '/?c=ExchangeBL&m=sendEvnPLDispScreenAPP',
							success: function (response) {
								win.getLoadMask().hide();
								
								var resultAPP = Ext.util.JSON.decode(response.responseText);
								
								if (resultAPP.success) {
									if (!resultAPP.isAlreadySendedToApp) {
										sw.swMsg.show({
											buttons: Ext.Msg.OK,
											fn: function (buttonId) {
												base_form.findField('EvnPLDispScreenChild_id').setValue(action.result.EvnPLDispScreenChild_id);
												win.callback({evnPLDispScreenChildData: win.getDataForCallBack()});
												
												if (options.callback) {
													options.callback();
												} else {
													win.hide();
												}
											},
											icon: Ext.Msg.INFO,
											msg: 'Скрининговое исследование добавлено',
											title: 'Сообщение'
										});
									} else {
										base_form.findField('EvnPLDispScreenChild_id').setValue(action.result.EvnPLDispScreenChild_id);
										win.callback({evnPLDispScreenChildData: win.getDataForCallBack()});
										
										if (options.callback) {
											options.callback();
										} else {
											win.hide();
										}
									}
								} else {
									if (!resultAPP.info) resultAPP.info = resultAPP.Error_Msg;
									sw.swMsg.show({
										buttons: Ext.Msg.OK,
										icon: Ext.Msg.WARNING,
										title: "Ошибка отправки в сервис.",
										msg: resultAPP.info
									});
								}
							},
							failure: function (response) {
								win.getLoadMask().hide();
								sw.swMsg.show({
									buttons: Ext.Msg.OK,
									icon: Ext.Msg.WARNING,
									title: "Ошибка отправки в сервис.",
									msg: 'Ошибка отправки в сервис.'
								});
							}
						});
					}
				}
				else
				{
					Ext.Msg.alert(langs('Ошибка'), langs('При сохранении произошли ошибки [Тип ошибки: 2]'));
				}
			}
		});
	},
	height: 570,
	Year: 2015,
	id: 'EvnPLDispScreenChildEditWindow',
	showEvnUslugaDispScreenEditWindow: function(action) {
		var base_form = this.EvnPLDispScreenChildFormPanel.getForm();
		var grid = this.evnUslugaDispDopGrid.getGrid();
		var win = this;

		var record = grid.getSelectionModel().getSelected();

		var personinfo = win.PersonInfoPanel;

		if (Ext.isEmpty(base_form.findField('EvnPLDispScreenChild_id').getValue())){
			win.doSave({
				callback: function() {
					win.showEvnUslugaDispScreenEditWindow(action);
				}
			});
			return false;
		}

		getWnd('swEvnUslugaDispScreenChildEditWindow').show({
			EvnDirection_id: record.get('EvnDirection_id'),
			AgeGroupDisp_id: base_form.findField('AgeGroupDisp_id').getValue(),
			SurveyTypeLink_IsLowWeight: base_form.findField('EvnPLDispScreenChild_IsLowWeight').getValue(),
			ScreenType_id: base_form.findField('ScreenType_id').getValue(),
			archiveRecord: this.archiveRecord,
			action: action,
			object: 'EvnPLDispScreenChild',
			minDate: '01.01.'+ win.Year,
			maxDate: '31.12.'+ win.Year,
			DispClass_id: base_form.findField('DispClass_id').getValue(),
			OmsSprTerr_Code: personinfo.getFieldValue('OmsSprTerr_Code'),
			Person_id: personinfo.getFieldValue('Person_id'),
			Person_Birthday: personinfo.getFieldValue('Person_Birthday'),
			Person_Firname: personinfo.getFieldValue('Person_Firname'),
			Person_Secname: personinfo.getFieldValue('Person_Secname'),
			Person_Surname: personinfo.getFieldValue('Person_Surname'),
			Sex_id: personinfo.getFieldValue('Sex_id'),
			Sex_Code: personinfo.getFieldValue('Sex_Code'),
			Person_Age: personinfo.getFieldValue('Person_Age'),
			UserLpuSection_id: win.UserLpuSection_id,
			UserMedStaffFact_id: win.UserMedStaffFact_id,
			formParams: {
				EvnUslugaDispDop_pid: base_form.findField('EvnPLDispScreenChild_id').getValue(),
				PersonEvn_id: base_form.findField('PersonEvn_id').getValue(),
				Server_id: base_form.findField('Server_id').getValue(),
				EvnUslugaDispDop_id: record.get('EvnUslugaDispDop_id'),
				SurveyType_id: record.get('SurveyType_id')
			},
			SurveyTypeLink_id: record.get('SurveyTypeLink_id'),
			SurveyType_Code: record.get('SurveyType_Code'),
			SurveyType_Name: record.get('SurveyType_Name'),
			ShowDeseaseStageCombo: getRegionNick().inlist(['perm','buryatiya','kareliya'])?true:false,
			onHide: Ext.emptyFn,
			callback: function(data) {
				// обновить грид услуг
				win.evnUslugaDispDopGrid.loadData({
					/*params: {
						EvnPLDispScreenChild_id: base_form.findField('EvnPLDispScreenChild_id').getValue(),
						Person_id: base_form.findField('Person_id').getValue(),
						AgeGroupDisp_id: base_form.findField('AgeGroupDisp_id').getValue()
					},*/
					globalFilters: {
						EvnPLDispScreenChild_id: base_form.findField('EvnPLDispScreenChild_id').getValue(),
						Person_id: base_form.findField('Person_id').getValue(),
						AgeGroupDisp_id: base_form.findField('AgeGroupDisp_id').getValue()
					}, noFocusOnLoad: true
				});
			}

		});
	},
	initComponent: function() {
		var win = this;

		this.evnUslugaDispDopGrid = new sw.Promed.ViewFrame({
			autoLoadData: false,
			actions: [
				{ name: 'action_add', disabled: true, hidden: true },
				{ name: 'action_edit', handler: function() { win.showEvnUslugaDispScreenEditWindow('edit'); } },
				{ name: 'action_view', handler: function() { win.showEvnUslugaDispScreenEditWindow('view'); } },
				{ name: 'action_delete', disabled: true, hidden: true },
				{ name: 'action_refresh', disabled: true, hidden: true },
				{ name: 'action_print' }
			],
			onLoadData: function() {
				this.doLayout();
			},
			onRowSelect: function(sm, index, record) {
				//this.setActionDisabled('action_edit', Ext.isEmpty(record.get('EvnUslugaDispDop_setDate')) && record.get('noIndication'));
				//this.setActionDisabled('action_view', Ext.isEmpty(record.get('EvnUslugaDispDop_setDate')));
				
				if (record.get('EvnDirection_id')) {
					this.setActionDisabled('action_showDirection',false);
					this.setActionDisabled('action_cancelDirection',false);
					this.setActionDisabled('action_addDirection',true);
				} else {
					this.setActionDisabled('action_showDirection',true);
					this.setActionDisabled('action_cancelDirection',true);
					this.setActionDisabled('action_addDirection',false);
				}
			},
			id: 'EPLDSCEF_evnUslugaDispDopGrid',
			dataUrl: '/?c=EvnPLDispScreenChild&m=loadEvnUslugaDispDopGrid',
			region: 'center',
			height: 200,
			title: '',
			toolbar: true,
			stringfields: [
				{ name: 'SurveyType_id', type: 'int', hidden: true },
				{ name: 'SurveyTypeLink_id', type: 'int', hidden: true },
				{ name: 'SurveyType_Code', type: 'int', hidden: true },
				{ name: 'EvnUslugaDispDop_id', type: 'int', hidden: true },
				{ name: 'UslugaComplex_id', type: 'string', hidden: true, header: 'USLUGA' },
				{ name: 'EvnDirection_id', type: 'string', hidden: true, header: 'DIRECTION' },
				{ name: 'MedPersonal_id', type: 'string', hidden: true, header: 'MedPersonal_id' },
				{ name: 'MedStaffFact_id', type: 'string', hidden: true, header: 'MedStaffFact_id' },
				{ name: 'LpuSection_id', type: 'string', hidden: true, header: 'LpuSection_id' },
				{ name: 'Diag_id', type: 'string', hidden: true, header: 'Diag_id' },
				{ name: 'SurveyType_Name', type: 'string', header: langs('Наименование осмотра (исследования)'), id: 'autoexpand' },
				{ name: 'EvnUslugaDispDop_setDate', type: 'date', header: langs('Дата выполнения'), width: 150 }
			]
		});

		this.PersonInfoPanel = new sw.Promed.PersonInformationPanel({
			additionalFields: ['PersonChild_IsInvalid', 'Diag_id', 'PersonChild_invDate'],
			button2Callback: function(callback_data) {
				var base_form = win.EvnPLDispScreenChildFormPanel.getForm();

				base_form.findField('PersonEvn_id').setValue(callback_data.PersonEvn_id);
				base_form.findField('Server_id').setValue(callback_data.Server_id);

				win.PersonInfoPanel.load( { Person_id: callback_data.Person_id, Server_id: callback_data.Server_id } );
			},
			region: 'north'
		});

		this.EvnUslugaDispDopPanel = new sw.Promed.Panel({
			items: [
				win.evnUslugaDispDopGrid
			],
			animCollapse: true,
			layout: 'form',
			border: false,
			autoHeight: true,
			collapsible: true,
			title: langs('Маршрутная карта')
		});

		this.EvnPLDispScreenChildMainResultsPanel = new sw.Promed.Panel({
			bodyBorder: false,
			title: langs('Основные результаты'),
			border: false,
			collapsible: true,
			titleCollapse: true,
			animCollapse: false,
			buttonAlign: 'left',
			frame: false,
			layout: 'form',
			labelAlign: 'right',
			bodyStyle: 'padding: 5px;',
			labelWidth: 360,
			items: [{
					name: 'EvnPLDispScreenChild_id',
					value: 0,
					xtype: 'hidden'
				}, {
					name: 'accessType',
					xtype: 'hidden'
				}, {
					name: 'Lpu_id',
					xtype: 'hidden'
				}, {
					name: 'EvnPLDispScreenChild_setDate',
					xtype: 'hidden'
				}, {
					name: 'DispClass_id',
					value: 15,
					xtype: 'hidden'
				}, {
					name: 'Person_id',
					value: 0,
					xtype: 'hidden'
				}, {
					name: 'PersonEvn_id',
					value: 0,
					xtype: 'hidden'
				}, {
					name: 'Server_id',
					value: 0,
					xtype: 'hidden'
				}, {
					fieldLabel: langs('Рост (см)'),
					name: 'PersonHeight_Height',
					maxValue: 999,
					maxLength: 3,
					allowDecimal: false,
					allowNegative: false,
					xtype: 'numberfield'
				}, {
					fieldLabel: langs('Вес (кг)'),
					name: 'PersonWeight_Weight',
					maxValue: 999,
					allowDecimal: true,
					allowNegative: false,
					xtype: 'numberfield'
				}, {
					fieldLabel: langs('Окружность головы, см'),
					name: 'EvnPLDispScreenChild_Head',
					maxValue: 9999,
					maxLength: 4,
					allowDecimal: false,
					allowNegative: false,
					xtype: 'numberfield'
				}, {
					fieldLabel: langs('Окружность грудной клетки (см)'),
					name: 'EvnPLDispScreenChild_Breast',
					maxValue: 9999,
					maxLength: 4,
					allowDecimal: false,
					allowNegative: false,
					xtype: 'numberfield'
				}, {
					fieldLabel: langs('Курение (хотя бы одну сигарету в день)'),
					hiddenName: 'EvnPLDispScreenChild_IsSmoking',
					xtype: 'swyesnocombo'
				}, {
					fieldLabel: langs('Употребление алкогольных напитков'),
					hiddenName: 'EvnPLDispScreenChild_IsAlco',
					xtype: 'swyesnocombo'
				}, {
					fieldLabel: langs('Физическая активность, ежедневная физическая нагрузка (зарядка, пешие прогулки, посещение спортивных секций и т.д.) не менее 30 минут'),
					hiddenName: 'EvnPLDispScreenChild_IsActivity',
					xtype: 'swyesnocombo'
				}, {
					layout: 'column',
					border: false,
					defaults: { bodyStyle: 'padding: 0px', border: false },
					items: [{
						layout:'form',
						items:[{
							fieldLabel: langs('Артериальное давление (систолическое/диастолическое) 1-е'),
							name: 'EvnPLDispScreenChild_ArteriaSistolPress',
							maxValue: 9999,
							maxLength: 4,
							allowDecimal: false,
							allowNegative: false,
							xtype: 'numberfield',
							width: 87
						}]
					}, {
						html: ' / ',
						bodyStyle: 'padding: 3px 5px'
					}, {
						layout:'form',
						items:[{
							name: 'EvnPLDispScreenChild_ArteriaDiastolPress',
							hideLabel: true,
							maxValue: 9999,
							maxLength: 4,
							allowDecimal: false,
							allowNegative: false,
							xtype: 'numberfield',
							width: 87
						}]
					}]
				}, {
					layout: 'column',
					border: false,
					defaults: { bodyStyle: 'padding: 0px', border: false },
					items: [{
						layout:'form',
						items:[{
							fieldLabel: langs('Артериальное давление (систолическое/диастолическое) 2-е'),
							name: 'EvnPLDispScreenChild_SystlcPressure',
							maxValue: 9999,
							maxLength: 4,
							allowDecimal: false,
							allowNegative: false,
							xtype: 'numberfield',
							width: 87
						}]
					}, {
						html: ' / ',
						bodyStyle: 'padding: 3px 5px'
					}, {
						layout:'form',
						items:[{
							name: 'EvnPLDispScreenChild_DiastlcPressure',
							hideLabel: true,
							maxValue: 9999,
							maxLength: 4,
							allowDecimal: false,
							allowNegative: false,
							xtype: 'numberfield',
							width: 87
						}]
					}]
				}, {
					fieldLabel: langs('Определение остроты слуха'),
					hiddenName: 'EvnPLDispScreenChild_IsDecreaseEar',
					store: new Ext.data.SimpleStore({
						autoLoad: false,
						fields: [
							{name: 'YesNo_id', type: 'int'},
							{name: 'YesNo_Code', type: 'string'},
							{name: 'YesNo_Name', type: 'string'}
						],
						key: 'YesNo_id',
						sortInfo: {field: 'YesNo_Code'},
						data: [
							[1, 0, langs('Норма')], [2, 1, langs('Снижение')]
						]
					}),
					xtype: 'swyesnocombo'
				}, {
					fieldLabel: langs('Определение остроты зрения'),
					hiddenName: 'EvnPLDispScreenChild_IsDecreaseEye',
					store: new Ext.data.SimpleStore({
						autoLoad: false,
						fields: [
							{name: 'YesNo_id', type: 'int'},
							{name: 'YesNo_Code', type: 'string'},
							{name: 'YesNo_Name', type: 'string'}
						],
						key: 'YesNo_id',
						sortInfo: {field: 'YesNo_Code'},
						data: [
							[1, 0, langs('Норма')], [2, 1, langs('Снижение')]
						]
					}),
					xtype: 'swyesnocombo'
				}, {
					fieldLabel: langs('Оценка плантограммы'),
					hiddenName: 'EvnPLDispScreenChild_IsFlatFoot',
					store: new Ext.data.SimpleStore({
						autoLoad: false,
						fields: [
							{name: 'YesNo_id', type: 'int'},
							{name: 'YesNo_Code', type: 'string'},
							{name: 'YesNo_Name', type: 'string'}
						],
						key: 'YesNo_id',
						sortInfo: {field: 'YesNo_Code'},
						data: [
							[1, 0, langs('Норма')], [2, 1, langs('Плоскостопие')]
						]
					}),
					xtype: 'swyesnocombo'
				}, {
					fieldLabel: langs('Оценка нервно-психического развития'),
					hiddenName: 'PsychicalConditionType_id',
					comboSubject: 'PsychicalConditionType',
					xtype: 'swcommonsprcombo'
				}, {
					fieldLabel: langs('Оценка полового развития'),
					hiddenName: 'SexualConditionType_id',
					comboSubject: 'SexualConditionType',
					xtype: 'swcommonsprcombo'
				}, {
					fieldLabel: langs('Признаки жестокого обращения'),
					hiddenName: 'EvnPLDispScreenChild_IsAbuse',
					xtype: 'swyesnocombo'
				}, {
					fieldLabel: langs('Здоров'),
					hiddenName: 'EvnPLDispScreenChild_IsHealth',
					xtype: 'swyesnocombo'
				}, {
					autoHeight: true,
					style: 'padding: 0px;',
					title: langs('Выявлены поведенческие факторы риска'),
					id: 'EPLDSCEF_RiskFactorTypeFieldset',
					width: 580,
					items: [
						{
							boxLabel: langs('Курение'),
							hideLabel: true,
							name: 'RiskFactorType',
							value: 3,
							xtype: 'checkbox'
						},
						{
							boxLabel: langs('Употребление алкоголя'),
							hideLabel: true,
							name: 'RiskFactorType',
							value: 4,
							xtype: 'checkbox'
						},
						{
							boxLabel: langs('Избыточная масса тела'),
							hideLabel: true,
							name: 'RiskFactorType',
							value: 7,
							xtype: 'checkbox'
						},
						{
							boxLabel: langs('Низкая физическая активность'),
							hideLabel: true,
							name: 'RiskFactorType',
							value: 6,
							xtype: 'checkbox'
						}
					],
					bodyStyle: 'padding: 5px;',
					xtype: 'fieldset'
				}, {
					fieldLabel: langs('Направлен к врачу ПМСП'),
					hiddenName: 'EvnPLDispScreenChild_IsPMSP',
					xtype: 'swyesnocombo'
				}, {
					fieldLabel: langs('Группа здоровья'),
					hiddenName: 'HealthKind_id',
					loadParams: {params: {where: ' where HealthKind_Code in (12,13,14,15,16)'}},
					xtype: 'swhealthkindcombo'
				}, {
					boxLabel: langs('Установлена инвалидность'),
					hideLabel: false,
					labelSeparator: '',
					name: 'EvnPLDispScreenChild_IsInvalid',
					value: 2,
					xtype: 'checkbox',
					listeners: {
						'check': function(checkbox, checked) {
							var base_form = this.EvnPLDispScreenChildFormPanel.getForm();
							if (!checked) {
								base_form.findField('InvalidGroup_id').setValue('');
								base_form.findField('EvnPLDispScreenChild_YearInvalid').setValue('');
								base_form.findField('EvnPLDispScreenChild_InvalidPeriod').setValue('');
								base_form.findField('InvalidDiag_id').setValue('');
							}
							this.findById('EPLDSCEF_InvalidFieldset').setVisible(checked);
							base_form.findField('InvalidGroup_id').setAllowBlank(!checked);
							base_form.findField('EvnPLDispScreenChild_YearInvalid').setAllowBlank(!checked);
							base_form.findField('EvnPLDispScreenChild_InvalidPeriod').setAllowBlank(!checked);
							base_form.findField('InvalidDiag_id').setAllowBlank(!checked);
						}.createDelegate(this)
					}
				}, {
					autoHeight: true,
					style: 'padding: 0;',
					border: false,
					bodyStyle: 'padding: 0;',
					xtype: 'fieldset',
					id: 'EPLDSCEF_InvalidFieldset',
					labelWidth: 360,
					items: [{
						fieldLabel: langs('Группа инвалидности'),
						hiddenName: 'InvalidGroup_id',
						comboSubject: 'InvalidGroup',
						showCodefield: false,
						xtype: 'swcommonsprcombo'
					}, {
						fieldLabel: langs('Год установления инвалидности'),
						name: 'EvnPLDispScreenChild_YearInvalid',
						minLength: 4,
						maxLength: 4,
						allowDecimal: false,
						allowNegative: false,
						width: 188,
						xtype: 'numberfield'
					}, {
						fieldLabel: langs('На какой срок установлена инвалидность (до 16 лет) (в годах)'),
						name: 'EvnPLDispScreenChild_InvalidPeriod',
						minValue: 1,
						maxValue: 16,
						maxLength: 2,
						allowDecimal: false,
						allowNegative: false,
						width: 188,
						xtype: 'numberfield'
					}, {
						fieldLabel: langs('Диагноз по инвалидности'),
						hiddenName: 'InvalidDiag_id',
						width: 188,
						xtype: 'swdiagcombo'
					}]
				}, {
					fieldLabel: langs('Случай закончен'),
					allowBlank: false,
					value: 1,
					hiddenName: 'EvnPLDispScreenChild_IsEndStage',
					listeners: {
						'change': function(combo, newValue) {
							var base_form = win.EvnPLDispScreenChildFormPanel.getForm();
							
							win.verfGroup();

							if (newValue == 2) {
								win.buttons[1].show();
								
								base_form.findField('ScreenEndCause_id').showContainer();
								base_form.findField('ScreenEndCause_id').setAllowBlank(false);
							} else {
								win.buttons[1].hide();
								
								base_form.findField('ScreenEndCause_id').hideContainer();
								base_form.findField('ScreenEndCause_id').setAllowBlank(true);
							}
						}
					},
					xtype: 'swyesnocombo'
				}, {
					border: false,
					layout: 'form',
					hidden: getRegionNick()!='kz',
					items: [{
						comboSubject: 'ScreenEndCause',
						fieldLabel: 'Причина завершения',
						prefix: 'r101_',
						xtype: 'swcommonsprcombo'
					}]
				}
			],
			region: 'center'
		});

		this.EvnPLDispScreenChildFormPanel = new Ext.form.FormPanel({
			border: false,
			layout: 'form',
			region: 'center',
			autoScroll: true,
			items: [{
				border: false,
				labelWidth: 200,
				layout: 'form',
				style: 'padding: 5px;',
				items: [{
					allowBlank: false,
					disabled: false,
					comboSubject: 'AgeGroupDisp',
					disabled: getRegionNick() == 'kz',
					fieldLabel: langs('Возрастная группа'),
					listeners: {
						'change': function(combo, newValue, oldValue) {
							sw.swMsg.show({
								buttons: Ext.Msg.YESNO,
								fn: function ( buttonId ) {
									if ( buttonId == 'yes' ) {
										win.onAgeGroupDispChange();
									} else {
										combo.setValue(oldValue);
									}
								},
								msg: langs('При изменении возрастной группы может измениться набор осмотров / исследований. Ранее заведенная информация может быть потеряна. Продолжить?'),
								title: langs('Подтверждение')
							});
							return false;
						}
					},
					loadParams: {params: {where: "where DispType_id = 6"}},
					hiddenName: 'AgeGroupDisp_id',
					moreFields: [
						{ name: 'AgeGroupDisp_From', mapping: 'AgeGroupDisp_From' },
						{ name: 'AgeGroupDisp_To', mapping: 'AgeGroupDisp_To' },
						{ name: 'AgeGroupDisp_monthFrom', mapping: 'AgeGroupDisp_monthFrom' },
						{ name: 'AgeGroupDisp_monthTo', mapping: 'AgeGroupDisp_monthTo' }
					],
					lastQuery: '',
					width: 300,
					xtype: 'swcommonsprcombo'
				}, {
					border: false,
					layout: 'form',
					hidden: getRegionNick()!='kz',
					items: [{
						comboSubject: 'ScreenType',
						fieldLabel: 'Целевая категория',
						prefix: 'r101_',
						allowBlank: getRegionNick()!='kz',
						listeners: {
							change: function (combo, newValue, oldValue) {
								if (newValue == oldValue) return true;
								
								var base_form = win.EvnPLDispScreenChildFormPanel.getForm();
								
								win.evnUslugaDispDopGrid.loadData({
									/*params: {
										EvnPLDispScreenChild_id: base_form.findField('EvnPLDispScreenChild_id').getValue(),
										Person_id: base_form.findField('Person_id').getValue(),
										AgeGroupDisp_id: base_form.findField('AgeGroupDisp_id').getValue(),
										EvnPLDispScreenChild_IsLowWeight: base_form.findField('EvnPLDispScreenChild_IsLowWeight').getValue()
									},*/
									globalFilters: {
										EvnPLDispScreenChild_id: base_form.findField('EvnPLDispScreenChild_id').getValue(),
										Person_id: base_form.findField('Person_id').getValue(),
										AgeGroupDisp_id: base_form.findField('AgeGroupDisp_id').getValue(),
										ScreenType_id: newValue,
										EvnPLDispScreenChild_IsLowWeight: base_form.findField('EvnPLDispScreenChild_IsLowWeight').getValue()
										//EvnPLDispScreenChild_setDate: Ext.util.Format.date(base_form.findField('EvnPLDispScreenChild_setDate').getValue(), 'd.m.Y')
									}, noFocusOnLoad: true
								});
							}
						},
						width: 600,
						xtype: 'swcommonsprcombo'
					}]
				}, {
					fieldLabel: langs('Недоношенные дети с массой тела менее 1500 г при рождении'),
					value: 1,
					allowBlank: false,
					hiddenName: 'EvnPLDispScreenChild_IsLowWeight',
					listeners: {
						'change': function(combo, newValue, oldValue) {
							sw.swMsg.show({
								buttons: Ext.Msg.YESNO,
								fn: function ( buttonId ) {
									if ( buttonId == 'yes' ) {
										win.onIsLowWeightChange();
									} else {
										combo.setValue(oldValue);
									}
								},
								msg: langs('При изменении атрибута недоношенности может измениться набор осмотров / исследований. Ранее заведенная информация может быть потеряна. Продолжить?'),
								title: langs('Подтверждение')
							});
							return false;
						}
					},
					xtype: 'swyesnocombo'
				}]
			},
				// маршрутная карта
				win.EvnUslugaDispDopPanel,
				// основные результаты диспансеризации
				win.EvnPLDispScreenChildMainResultsPanel
			],
			keys: [{
				alt: true,
				fn: function(inp, e) {
					switch (e.getKey())
					{
						case Ext.EventObject.C:
							if (this.action != 'view')
							{
								this.doSave(false);
							}
							break;

						case Ext.EventObject.G:
							this.printEvnPLDispScreenChild();
							break;

						case Ext.EventObject.J:
							this.hide();
							break;
					}
				},
				key: [ Ext.EventObject.C, Ext.EventObject.G, Ext.EventObject.J ],
				scope: this,
				stopEvent: true
			}],
			reader: new Ext.data.JsonReader({
				success: Ext.emptyFn
			}, [
				{ name: 'EvnPLDispScreenChild_id' },
				{ name: 'accessType' },
				{ name: 'EvnPLDispScreenChild_setDate' },
				{ name: 'DispClass_id' },
				{ name: 'Lpu_id' },
				{ name: 'Person_id' },
				{ name: 'PersonEvn_id' },
				{ name: 'Server_id' },
				{ name: 'PersonHeight_Height' },
				{ name: 'PersonWeight_Weight' },
				{ name: 'EvnPLDispScreenChild_ArteriaSistolPress' },
				{ name: 'EvnPLDispScreenChild_ArteriaDiastolPress' },
				{ name: 'EvnPLDispScreenChild_SystlcPressure' },
				{ name: 'EvnPLDispScreenChild_DiastlcPressure' },
				{ name: 'EvnPLDispScreenChild_IsAlco' },
				{ name: 'EvnPLDispScreenChild_IsSmoking' },
				{ name: 'HealthKind_id' },
				{ name: 'AgeGroupDisp_id' },
				{ name: 'ScreenType_id' },
				{ name: 'ScreenEndCause_id' },
				{ name: 'EvnPLDispScreenChild_IsLowWeight' },
				{ name: 'EvnPLDispScreenChild_Head' },
				{ name: 'EvnPLDispScreenChild_Breast' },
				{ name: 'EvnPLDispScreenChild_IsActivity' },
				{ name: 'EvnPLDispScreenChild_IsDecreaseEar' },
				{ name: 'EvnPLDispScreenChild_IsDecreaseEye' },
				{ name: 'EvnPLDispScreenChild_IsFlatFoot' },
				{ name: 'PsychicalConditionType_id' },
				{ name: 'SexualConditionType_id' },
				{ name: 'EvnPLDispScreenChild_IsAbuse' },
				{ name: 'EvnPLDispScreenChild_IsHealth' },
				{ name: 'EvnPLDispScreenChild_IsPMSP' },
				{ name: 'EvnPLDispScreenChild_IsEndStage' },
				{ name: 'EvnPLDispScreenChild_IsInvalid' },
				{ name: 'InvalidGroup_id' },
				{ name: 'EvnPLDispScreenChild_YearInvalid' },
				{ name: 'EvnPLDispScreenChild_InvalidPeriod' },
				{ name: 'InvalidDiag_id' }
			]),
			url: '/?c=EvnPLDispScreenChild&m=saveEvnPLDispScreenChild'
		});

		Ext.apply(this, {
			items: [
				// паспортная часть человека
				win.PersonInfoPanel,
				win.EvnPLDispScreenChildFormPanel
			],
			buttons: [{
				handler: function() {
					this.doSave(false);
				}.createDelegate(this),
				iconCls: 'save16',
				id: 'EPLDSCEF_SaveButton',
				onTabAction: function() {
					Ext.getCmp('EPLDSCEF_PrintButton').focus(true, 200);
				},
				onShiftTabAction: function() {
					Ext.getCmp('EPLDSCEF_IsFinishCombo').focus(true, 200);
				},
				tabIndex: 2406,
				text: BTN_FRMSAVE
			}, {
				hidden: true,
				handler: function() {
					this.printEvnPLDispScreenChild();
				}.createDelegate(this),
				iconCls: 'print16',
				id: 'EPLDSCEF_PrintButton',
				tabIndex: 2407,
				text: langs('Печать "Статистическая карта (форма 025-07/у)"')
			}, '-',
			HelpButton(this, -1),
			{
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'cancel16',
				id: 'EPLDSCEF_CancelButton',
				tabIndex: 2409,
				text: BTN_FRMCANCEL
			}]
		});
		sw.Promed.swEvnPLDispScreenChildEditWindow.superclass.initComponent.apply(this, arguments);
	},
	loadScoreField: function() {
		// расчёт поля SCORE
		var win = this;
		var base_form = this.EvnPLDispScreenChildFormPanel.getForm();

		win.getLoadMask(langs('Расчет суммарного сердечно-сосудистого риска')).show();
		Ext.Ajax.request({
			callback: function(options, success, response) {
				win.getLoadMask().hide();
				if ( success ) {
					var response_obj = Ext.util.JSON.decode(response.responseText);

					if ( response_obj.SCORE ) {
						base_form.findField('EvnPLDispScreenChild_SumRick').setValue(response_obj.SCORE);
					}
				}
				else {
					sw.swMsg.alert(langs('Ошибка'), langs('Ошибка расчета суммарного сердечно-сосудистого риска'));
				}
			},
			params: {
				EvnPLDisp_id: base_form.findField('EvnPLDispScreenChild_id').getValue()
			},
			url: '/?c=EvnUslugaDispDop&m=loadScoreField'
		});
	},
	keys: [{
		alt: true,
		fn: function(inp, e) {
			var win = Ext.getCmp('EvnPLDispScreenChildEditWindow');
			var tabbar = win.findById('EPLDSCEF_EvnPLTabbar');

			switch (e.getKey())
			{
				case Ext.EventObject.C:
					win.doSave();
					break;

				case Ext.EventObject.J:
					win.hide();
					break;
			}
		},
		key: [
			Ext.EventObject.C,
			Ext.EventObject.J
		],
		stopEvent: true
	}],
	layout: 'border',
	listeners: {
		'hide': function() {
			this.onHide();
		}
	},
	maximizable: true,
	minHeight: 570,
	minWidth: 800,
	modal: true,
	onHide: Ext.emptyFn,
	params: {
		EvnVizitPL_setDate: null,
		LpuSection_id: null,
		MedPersonal_id: null
	},

	plain: true,
	printEvnPLDispScreenChild: function(print_blank) {
		var win = this;
		var base_form = this.EvnPLDispScreenChildFormPanel.getForm();
		win.doSave({
			callback: function() {
				var evn_pl_id = base_form.findField('EvnPLDispScreenChild_id').getValue();

				var template = 'han_DispScreenChild_f025_07u.rptdesign';
				printBirt({
					'Report_FileName': template,
					'Report_Params': '&paramEvnPLDispScreenChild_id=' + evn_pl_id,
					'Report_Format': 'pdf'
				});
			}
		});
	},
	resizable: true,
	setAgeGroupDispCombo: function() {
		var win = this,
			base_form = this.EvnPLDispScreenChildFormPanel.getForm(),
			curDate = new Date(),
			endOfYear = new Date(curDate.getFullYear(), 12, 0),
			age = swGetPersonAge(win.PersonInfoPanel.getFieldValue('Person_Birthday'), curDate),
			age2 = swGetPersonAge(win.PersonInfoPanel.getFieldValue('Person_Birthday'), endOfYear),
			agemonth = swGetPersonAgeMonth(win.PersonInfoPanel.getFieldValue('Person_Birthday'), curDate);
		
		// 3 года и 10 месяцев
		if ((age * 12 + agemonth) >= 46) {
			age = age2;
		}

		var agegroupcombo = base_form.findField('AgeGroupDisp_id');
		agegroupcombo.getStore().clearFilter();
		var index = agegroupcombo.getStore().findBy(function(record) {
			if (agegroupcombo.getValue() == record.get('AgeGroupDisp_id')) {
				return true;
			}
			if (
				record.get('AgeGroupDisp_From') <= age && record.get('AgeGroupDisp_To') >= age &&
				record.get('AgeGroupDisp_monthFrom') <= agemonth && record.get('AgeGroupDisp_monthTo') >= agemonth
			) {
				return true;
			}
			else {
				return false;
			}
		});

		if (index >= 0) {
			agegroupcombo.setValue(agegroupcombo.getStore().getAt(index).get('AgeGroupDisp_id'));
		}

		win.onAgeGroupDispChange();
	},
	onAgeGroupDispChange: function() {
		var win = this;
		var base_form = win.EvnPLDispScreenChildFormPanel.getForm();

		var age = base_form.findField('AgeGroupDisp_id').getFieldValue('AgeGroupDisp_From');

		if (age >= 1) {
			base_form.findField('EvnPLDispScreenChild_IsLowWeight').setValue(1);
			base_form.findField('EvnPLDispScreenChild_IsLowWeight').disable();
		} else {
			base_form.findField('EvnPLDispScreenChild_IsLowWeight').enable();
		}

		if (age < 3) {
			base_form.findField('EvnPLDispScreenChild_Head').showContainer();
			base_form.findField('EvnPLDispScreenChild_Breast').showContainer();
			base_form.findField('EvnPLDispScreenChild_IsActivity').hideContainer();
			base_form.findField('EvnPLDispScreenChild_IsActivity').clearValue();
		} else {
			base_form.findField('EvnPLDispScreenChild_Head').hideContainer();
			base_form.findField('EvnPLDispScreenChild_Head').setValue('');
			base_form.findField('EvnPLDispScreenChild_Breast').hideContainer();
			base_form.findField('EvnPLDispScreenChild_Breast').setValue('');
			base_form.findField('EvnPLDispScreenChild_IsActivity').showContainer();
		}

		if (age >= 5) {
			base_form.findField('EvnPLDispScreenChild_IsFlatFoot').showContainer();
		} else {
			base_form.findField('EvnPLDispScreenChild_IsFlatFoot').hideContainer();
			base_form.findField('EvnPLDispScreenChild_IsFlatFoot').clearValue();
		}

		if (age >= 7) {
			base_form.findField('EvnPLDispScreenChild_IsSmoking').showContainer();
			base_form.findField('EvnPLDispScreenChild_IsAlco').showContainer();
		} else {
			base_form.findField('EvnPLDispScreenChild_IsSmoking').hideContainer();
			base_form.findField('EvnPLDispScreenChild_IsSmoking').clearValue();
			base_form.findField('EvnPLDispScreenChild_IsAlco').hideContainer();
			base_form.findField('EvnPLDispScreenChild_IsAlco').clearValue();
		}

		win.onIsLowWeightChange();
	},
	onIsLowWeightChange: function() {
		var win = this;
		// прогрузить маршрутную карту
		win.loadEvnUslugaDispDopGrid();
	},
	showEvnDirectionEditWindow: function(action) {
		
		var base_form = this.EvnPLDispScreenChildFormPanel.getForm();
		var grid = this.evnUslugaDispDopGrid.getGrid();
		var win = this;
		
		var record = grid.getSelectionModel().getSelected();
		
		if ( typeof record != 'object' ) {
			return false;
		}
		
		//обновить грид услуг
		var reloadGrid  = function () {
			win.evnUslugaDispDopGrid.loadData({
				/*params: {
					EvnPLDispScreen_id: base_form.findField('EvnPLDispScreenChild_id').getValue(),
					Person_id: base_form.findField('Person_id').getValue(),
					AgeGroupDisp_id: base_form.findField('AgeGroupDisp_id').getValue()
				},*/
				globalFilters: {
					//withoutAgeGroups: win.withoutAgeGroups,
					EvnPLDispScreenChild_id: base_form.findField('EvnPLDispScreenChild_id').getValue(),
					Person_id: base_form.findField('Person_id').getValue(),
					AgeGroupDisp_id: base_form.findField('AgeGroupDisp_id').getValue(),
					EvnPLDispScreenChild_IsLowWeight: base_form.findField('EvnPLDispScreenChild_IsLowWeight').getValue(),
					ScreenType_id: Ext.isEmpty(base_form.findField('ScreenType_id').getValue())?'':base_form.findField('ScreenType_id').getValue()
				}, noFocusOnLoad: true
			});
		}
		
		if (Ext.isEmpty(base_form.findField('EvnPLDispScreenChild_id').getValue())){
			win.doSave({
				callback: function() {
					win.showEvnDirectionEditWindow(action);
				},
				autoSave: true
			});
			return false;
		}
		
		if (action == 'add') {
			getWnd('swEvnDirectionEditWindow').show({
				action: 'add',
				Person_id: base_form.findField('Person_id').getValue(),
				kzScreening: 1,
				callback: function (data) {
					Ext.Ajax.request({
						url: '/?c=EvnPLDispScreenChild&m=saveEvnUslugaDispDop',
						params: {
							'MedPersonal_id': record.get('MedPersonal_id'),
							'MedStaffFact_id': record.get('MedStaffFact_id'),
							'LpuSection_id': record.get('LpuSection_id'),
							'Diag_id': record.get('Diag_id'),
							'EvnUslugaDispDop_id': record.get('EvnUslugaDispDop_id'),
							'EvnUslugaDispDop_pid': base_form.findField('EvnPLDispScreenChild_id').getValue(),
							'EvnDirection_id': data.evnDirectionData.EvnDirection_id,
							'SurveyType_id': record.get('SurveyType_id'),
							'EvnUslugaDispDop_setDate': Ext.isEmpty(record.get('EvnUslugaDispDop_setDate'))?Ext.util.Format.date(data.evnDirectionData.EvnDirection_setDate, 'd.m.Y'):Ext.util.Format.date(record.get('EvnUslugaDispDop_setDate'), 'd.m.Y'),
							'UslugaComplex_id': record.get('UslugaComplex_id'),
							'Person_id': base_form.findField('Person_id').getValue(),
							'PersonEvn_id': base_form.findField('PersonEvn_id').getValue(),
							'Server_id': base_form.findField('Server_id').getValue()
						},
						success: function (response, action) {
							if (response && response.responseText) {
								var answer = Ext.util.JSON.decode(response.responseText);
								if (answer.success) {
									reloadGrid();
								} else if (answer.Error_Msg) {
									//Ext.Msg.alert('Ошибка', answer.Error_Msg);
								}
							} else {
								Ext.Msg.alert(lang['oshibka'], lang['Ошибка при сохранении исследования']);
							}
						}
					});
				},
				formParams: {
					'DirType_id': 10,
					'UslugaComplex_did': record.get('UslugaComplex_id')
				}
			});
		} else if (action == 'view') {
			getWnd('swEvnDirectionEditWindow').show({
				action: 'view',
				Person_id: base_form.findField('Person_id').getValue(),
				EvnDirection_id: record.get('EvnDirection_id'),
				formParams: {}
			});
		} else if (action == 'cancel') {
			var params = {
				EvnDirection_id: record.get('EvnDirection_id'),
				cancelType: 'cancel',
				ownerWindow: win,
				callback: function (data) {
					/*Ext.Ajax.request({
						url: '/?c=EvnPLDispScreen&m=deleteEvnUslugaDispDop',
						params: {
							'EvnUslugaDispDop_id': record.get('EvnUslugaDispDop_id')
						},
						success: function (response, action) {
							if (response && response.responseText) {
								var answer = Ext.util.JSON.decode(response.responseText);
								if (answer.success) {
									reloadGrid();
								} else if (answer.Error_Msg) {
									//Ext.Msg.alert('Ошибка', answer.Error_Msg);
								}
							} else {
								Ext.Msg.alert(lang['oshibka'], 'Ошибка при удалении исследования');
							}
						}
					});*/
					Ext.Ajax.request({
						url: '/?c=EvnPLDispScreenChild&m=saveEvnUslugaDispDop',
						params: {
							'MedPersonal_id': record.get('MedPersonal_id'),
							'MedStaffFact_id': record.get('MedStaffFact_id'),
							'LpuSection_id': record.get('LpuSection_id'),
							'Diag_id': record.get('Diag_id'),
							'EvnUslugaDispDop_id': record.get('EvnUslugaDispDop_id'),
							'EvnUslugaDispDop_pid': base_form.findField('EvnPLDispScreenChild_id').getValue(),
							//'EvnDirection_id': data.evnDirectionData.EvnDirection_id,
							'SurveyType_id': record.get('SurveyType_id'),
							'EvnUslugaDispDop_setDate': Ext.util.Format.date(record.get('EvnUslugaDispDop_setDate'), 'd.m.Y'),
							'UslugaComplex_id': record.get('UslugaComplex_id'),
							'Person_id': base_form.findField('Person_id').getValue(),
							'PersonEvn_id': base_form.findField('PersonEvn_id').getValue(),
							'Server_id': base_form.findField('Server_id').getValue()
						},
						success: function (response, action) {
							if (response && response.responseText) {
								var answer = Ext.util.JSON.decode(response.responseText);
								if (answer.success) {
									reloadGrid();
								} else if (answer.Error_Msg) {
									//Ext.Msg.alert('Ошибка', answer.Error_Msg);
								}
							} else {
								Ext.Msg.alert(lang['oshibka'], lang['Ошибка при сохранении исследования']);
							}
						}
					});
				}
			}
			
			sw.Promed.Direction.cancel(params);
		}
	},
	show: function() {
		sw.Promed.swEvnPLDispScreenChildEditWindow.superclass.show.apply(this, arguments);

		if (!arguments[0])
		{
			Ext.Msg.alert(langs('Сообщение'), langs('Неверные параметры'));
			return false;
		}

		var win = this;
		win.getLoadMask(LOAD_WAIT).show();

		this.restore();
		this.center();
		this.maximize();

		var form = this.EvnPLDispScreenChildFormPanel;
		form.getForm().reset();
		
		if (!this.evnUslugaDispDopGrid.getAction('action_cancelDirection')) {
			this.evnUslugaDispDopGrid.addActions({
				handler: function () {
					this.showEvnDirectionEditWindow('cancel');
				}.createDelegate(this),
				name: 'action_cancelDirection',
				text: 'Отменить направление',
				disabled: true
			});
		}
		
		if (!this.evnUslugaDispDopGrid.getAction('action_showDirection')) {
			this.evnUslugaDispDopGrid.addActions({
				handler: function () {
					this.showEvnDirectionEditWindow('view');
				}.createDelegate(this),
				name: 'action_showDirection',
				text: 'Просмотр направления',
				disabled: true
			});
		}
		
		if (!this.evnUslugaDispDopGrid.getAction('action_addDirection')) {
			this.evnUslugaDispDopGrid.addActions({
				handler: function () {
					this.showEvnDirectionEditWindow('add');
				}.createDelegate(this),
				name: 'action_addDirection',
				text: 'Добавить направление',
				disabled: true
			});
		}

		this.callback = Ext.emptyFn;
		this.onHide = Ext.emptyFn;

		form.getForm().setValues(arguments[0]);

		if (arguments[0].action)
		{
			this.action = arguments[0].action;
		}

		if (arguments[0].Year)
		{
			this.Year = arguments[0].Year;
		}
		else
		{
			this.Year = null;
		}

		if (arguments[0].callback)
		{
			this.callback = arguments[0].callback;
		}

		if (arguments[0].onHide)
		{
			this.onHide = arguments[0].onHide;
		}

		// определенный медстафффакт
		if ( arguments[0].UserMedStaffFact_id && arguments[0].UserMedStaffFact_id > 0 )
		{
			this.UserMedStaffFact_id = arguments[0].UserMedStaffFact_id;
		}
		else
		{
			this.UserMedStaffFact_id = null;
			// если в настройках есть medstafffact, то имеем список мест работы
			if ( Ext.globalOptions.globals['medstafffact'] && Ext.globalOptions.globals['medstafffact'].length > 0 )
			{
				this.UserMedStaffFacts = Ext.globalOptions.globals['medstafffact'];
				this.UserLpuSections = Ext.globalOptions.globals['lpusection'];
			}
			else
			{
				// свободный выбор врача и отделения
				this.UserMedStaffFacts = null;
				this.UserLpuSections = null;
			}
		}

		// определенный LpuSection
		if ( arguments[0].UserLpuSection_id && arguments[0].UserLpuSection_id > 0 )
		{
			this.UserLpuSection_id = arguments[0].UserLpuSection_id;
		}
		else
		{
			this.UserLpuSection_id = null;
			// если в настройках есть lpusection, то имеем список мест работы
			if ( Ext.globalOptions.globals['lpusection'] && Ext.globalOptions.globals['lpusection'].length > 0 )
			{
				this.UserLpuSections = Ext.globalOptions.globals['lpusection'];
			}
			else
			{
				// свободный выбор врача и отделения
				this.UserLpuSectons = null;
			}
		}

		var base_form = this.EvnPLDispScreenChildFormPanel.getForm();
		var EvnPLDispScreenChild_id = base_form.findField('EvnPLDispScreenChild_id').getValue();
		var person_id = base_form.findField('Person_id').getValue();
		var server_id = base_form.findField('Server_id').getValue();

		switch (win.action) {
			case 'add':
				win.setTitle(WND_POL_EPLDSCADD);
				break;
			case 'edit':
				win.setTitle(WND_POL_EPLDSCEDIT);
				break;
			case 'view':
				win.setTitle(WND_POL_EPLDSCVIEW);
				break;
		}

		this.PersonInfoPanel.load({
			Person_id: person_id,
			Server_id: server_id,
			callback: function() {
				win.getLoadMask().hide();

				var sex_id = win.PersonInfoPanel.getFieldValue('Sex_id');
				var age = win.PersonInfoPanel.getFieldValue('Person_Age');
				base_form.findField('Server_id').setValue(win.PersonInfoPanel.getFieldValue('Server_id'));
				base_form.findField('PersonEvn_id').setValue(win.PersonInfoPanel.getFieldValue('PersonEvn_id'));
				
				if (win.action != 'view') {
					win.enableEdit(true);
					win.evnUslugaDispDopGrid.setReadOnly(false);
				} else {
					win.enableEdit(false);
					win.evnUslugaDispDopGrid.setReadOnly(true);
				}

				if (!Ext.isEmpty(EvnPLDispScreenChild_id)) {
					win.loadForm(EvnPLDispScreenChild_id);
				} else {
					win.onLoadForm();
				}
				
				win.buttons[0].focus();
			} 
		});

		form.getForm().clearInvalid();
		this.doLayout();
	},
	loadForm: function(EvnPLDispScreenChild_id) {
		var win = this;
		var base_form = this.EvnPLDispScreenChildFormPanel.getForm();
		win.getLoadMask(LOAD_WAIT).show();

		base_form.load({
			failure: function() {
				win.getLoadMask().hide();
				swEvnPLDispScreenChildEditWindow.hide();
			},
			params: {
				EvnPLDispScreenChild_id: EvnPLDispScreenChild_id,
				archiveRecord: win.archiveRecord
			},
			success: function(form, action) {
				win.getLoadMask().hide();
				win.Year = Ext.util.Format.date(Date.parseDate(base_form.findField('EvnPLDispScreenChild_setDate').getValue(), 'd.m.Y'), 'Y');

				if ( base_form.findField('accessType').getValue() == 'view' ) {
					win.action = 'view';
					win.enableEdit(false);
				}

				var riskFactorTypeFieldset = win.findById('EPLDSCEF_RiskFactorTypeFieldset');
				if (action.response && action.response.responseText) {
					var response = Ext.util.JSON.decode(action.response.responseText);
					if (response[0] && response[0].RiskFactorTypeData) {
						riskFactorTypeFieldset.items.items.forEach(function (item) {
							if (item.value.inlist(response[0].RiskFactorTypeData)) {
								item.setValue(1); // чекбокс on
							} else {
								item.setValue(0); // чекбокс off
							}
						});
					}
				}

				win.onLoadForm();
			},
			url: '/?c=EvnPLDispScreenChild&m=loadEvnPLDispScreenChildEditForm'
		});
	},
	loadEvnUslugaDispDopGrid: function() {
		var win = this;
		var base_form = win.EvnPLDispScreenChildFormPanel.getForm();
		win.evnUslugaDispDopGrid.loadData({
			/*params: {
				EvnPLDispScreenChild_id: base_form.findField('EvnPLDispScreenChild_id').getValue(),
				Person_id: base_form.findField('Person_id').getValue(),
				AgeGroupDisp_id: base_form.findField('AgeGroupDisp_id').getValue(),
				EvnPLDispScreenChild_IsLowWeight: base_form.findField('EvnPLDispScreenChild_IsLowWeight').getValue()
			},*/
			globalFilters: {
				EvnPLDispScreenChild_id: base_form.findField('EvnPLDispScreenChild_id').getValue(),
				Person_id: base_form.findField('Person_id').getValue(),
				AgeGroupDisp_id: base_form.findField('AgeGroupDisp_id').getValue(),
				EvnPLDispScreenChild_IsLowWeight: base_form.findField('EvnPLDispScreenChild_IsLowWeight').getValue(),
				ScreenType_id: Ext.isEmpty(base_form.findField('ScreenType_id').getValue())?'':base_form.findField('ScreenType_id').getValue()
			}, noFocusOnLoad: true
		});
	},
	onLoadForm: function() {
		var win = this;
		var base_form = win.EvnPLDispScreenChildFormPanel.getForm();
		var idiag_combo = base_form.findField('InvalidDiag_id');
		if(!base_form.findField('AgeGroupDisp_id').getValue()) {
			win.setAgeGroupDispCombo();
		}

		var endOfYear = new Date(new Date().getFullYear(), 12, 0),
			age = swGetPersonAge(win.PersonInfoPanel.getFieldValue('Person_Birthday'), endOfYear);
		base_form.findField('EvnPLDispScreenChild_IsInvalid').setContainerVisible(age >= 16);
		var endOfYear = new Date(new Date().getFullYear(), 12, 0),
			age = swGetPersonAge(win.PersonInfoPanel.getFieldValue('Person_Birthday'), endOfYear);
		base_form.findField('EvnPLDispScreenChild_IsInvalid').setContainerVisible(age >= 16);
		if (win.action == 'add') {
			if (win.PersonInfoPanel.getFieldValue('PersonChild_IsInvalid') == 2) {
				base_form.findField('EvnPLDispScreenChild_IsInvalid').setValue(true);
			}
			if (!!win.PersonInfoPanel.getFieldValue('Diag_id')) {
				idiag_combo.setValue(win.PersonInfoPanel.getFieldValue('Diag_id'));
			}
		}
		var idiag = idiag_combo.getValue();
		if (!Ext.isEmpty(idiag)) {
			idiag_combo.getStore().load({
				params: {
					where: "where DiagLevel_id = 4 and Diag_id = " + idiag
				},
				callback: function() {
					idiag_combo.setValue(idiag);
				}
			});
		}
		base_form.findField('EvnPLDispScreenChild_IsEndStage').fireEvent('change', base_form.findField('EvnPLDispScreenChild_IsEndStage'), base_form.findField('EvnPLDispScreenChild_IsEndStage').getValue());
		base_form.findField('EvnPLDispScreenChild_IsInvalid').fireEvent('check', base_form.findField('EvnPLDispScreenChild_IsInvalid'), base_form.findField('EvnPLDispScreenChild_IsInvalid').getValue());
		base_form.findField('EvnPLDispScreenChild_YearInvalid').minValue = win.PersonInfoPanel.getFieldValue('Person_Birthday').getFullYear();
		base_form.findField('EvnPLDispScreenChild_YearInvalid').maxValue = new Date().getFullYear();
		if (win.PersonInfoPanel.getFieldValue('PersonChild_invDate') && win.action == 'add') {
			base_form.findField('EvnPLDispScreenChild_YearInvalid').setValue(Date.parseDate(win.PersonInfoPanel.getFieldValue('PersonChild_invDate'), 'd.m.Y').getFullYear());
		}
		
		win.evnUslugaDispDopGrid.loadData({
			/*params: {
				EvnPLDispScreenChild_id: base_form.findField('EvnPLDispScreenChild_id').getValue(),
				Person_id: base_form.findField('Person_id').getValue(),
				AgeGroupDisp_id: base_form.findField('AgeGroupDisp_id').getValue(),
				EvnPLDispScreenChild_IsLowWeight: base_form.findField('EvnPLDispScreenChild_IsLowWeight').getValue()
			},*/
			globalFilters: {
				EvnPLDispScreenChild_id: base_form.findField('EvnPLDispScreenChild_id').getValue(),
				Person_id: base_form.findField('Person_id').getValue(),
				AgeGroupDisp_id: base_form.findField('AgeGroupDisp_id').getValue(),
				EvnPLDispScreenChild_IsLowWeight: base_form.findField('EvnPLDispScreenChild_IsLowWeight').getValue(),
				ScreenType_id: Ext.isEmpty(base_form.findField('ScreenType_id').getValue())?'':base_form.findField('ScreenType_id').getValue()
			}, noFocusOnLoad: true
		});
	},
	title: WND_POL_EPLDSCADD,
	width: 800
});

