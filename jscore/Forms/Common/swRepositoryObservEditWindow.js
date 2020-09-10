/**
 * swRepositoryObservEditWindow - окно редактирования/добавления наблюдения за пациентом
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://rtmis.ru/
 *
 *
 * @package      Common
 * @region       All
 * @access       public
 * @copyright    Copyright (c) 2020 RT MIS Ltd.
 * @author       Stanislav Bykov
 * @version      03.04.2020
 */

sw.Promed.swRepositoryObservEditWindow = Ext.extend(sw.Promed.BaseForm, {
	/* свойства */
	action: null,
	buttonAlign: 'left',
	closable: true,
	closeAction: 'hide',
	draggable: true,
	firstTabIndex: 27130,
	height: 700,
	id: 'RepositoryObservEditWindow',
	layout: 'border',
	modal: true,
	plain: true,
	resizable: false,
	split: true,
	width: 800,
	quarantineSymptomFields: [
		'RepositoryObserv_IsHighTemperature',
		'Dyspnea_id',
		'Cough_id',
		'RepositoryObserv_Other',
		'RepositoryObserv_IsRunnyNose',
		'RepositoryObserv_IsSoreThroat',
		'RepositoryObserv_IsSputum'
	],
	/* методы */
	calcPaO2FiO2: function() {
		var base_form = this.RepositoryObservForm.getForm();

		var
			FiO2 = base_form.findField('RepositoryObserv_FiO2').getValue(),
			PaO2 = base_form.findField('RepositoryObserv_PaO2').getValue(),
			PaO2FiO2;

		if (!Ext.isEmpty(FiO2) && !Ext.isEmpty(PaO2)) {
			PaO2FiO2 = (PaO2 / FiO2).toFixed(2);
		}

		base_form.findField('RepositoryObserv_PaO2FiO2').setValue(PaO2FiO2);
	},
	callback: Ext.emptyFn,
	doPrint: function() {
		var RepositoryObserv_id = this.RepositoryObservForm.getForm().findField('RepositoryObserv_id').getValue();
		
		if (!RepositoryObserv_id || RepositoryObserv_id == 0) {
			this.doSave({print: true});
			return false;
		}
		
		printBirt({
			'Report_FileName': 'printObserv_covid_daily.rptdesign',
			'Report_Params': '&RepositoryObserv_id=' + RepositoryObserv_id,
			'Report_Format': 'pdf'
		});
	},
	doSave: function(options) {
		var
			base_form = this.RepositoryObservForm.getForm(),
			win = this;

		if ( typeof options != 'object' ) {
			options = {
				params: {}
			};
		}

		// логики на сохранение разбить по режимам
		switch (win.useCase) {
			case 'quarantine':
				var notInterviewed = base_form.findField('notInterviewed').getValue();
				var IsEmptySymptom = base_form.findField('IsEmptySymptom').getValue();

				// опрос не проведен или симптомы отсутствуют
				if(notInterviewed || IsEmptySymptom) break;

				var validFlag = false;
				for (var i = 0 ; i< win.quarantineSymptomFields.length; ++i) {
					var field = base_form.findField(  win.quarantineSymptomFields[i] );
					if( field.hiddenName ) { // есть хотя бы 1 заполненный комбобокс
						if(field.getValue() && field.getValue() != 1) {
							validFlag = true;
							break;
						}
					} else if ( !!field.getValue() ) {
						validFlag = true;
						break;
					}
				}

				base_form.findField('RepositoryObserv_IsCVIQuestion').setValue(notInterviewed ? 1 : 2);
				if(!validFlag) {
					sw.swMsg.alert('Сообщение', langs("На форме должны быть заполнены обязательные параметры, а также либо должен быть установлен флаг «Опрос не проведён», либо флаг «Симптомы отсутствуют», либо хотя бы в одном из симптомов «Повышенная температура тела», «Насморк», «Боль в горле», «Кашель», «Одышка», «Мокрота», «Иные симптомы» должно быть введено значение / выбрано значение, отличное от «Нет» / «Отсутствует»."));
					return;
				}
				break;
		}

		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					win.RepositoryObservForm.getFirstInvalidEl().focus(true);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Сохранение..."});
		loadMask.show();

		base_form.findField('MedPersonal_id').setValue(base_form.findField('MedStaffFact_id').getFieldValue('MedPersonal_id'));

		if (win.PersonPanel.getFieldValue('Sex_Code') == 1) {
			base_form.findField('GenConditFetus_id').clearValue();
			base_form.findField('RepositoryObserv_PregnancyPeriod').setValue(null);
		}
		
		if (this.useCase.inlist(['evnpspriem'])) {
			var params = base_form.getValues();
			win.callback(params);
			win.hide();
			return true;
		}

		base_form.submit({
			params: options.params,
			success: function(result_form, action) {
				loadMask.hide();

				if ( action.result ) {
					if (action.result.Alert_Msg) {
						var msg = action.result.Alert_Msg;
						Ext.Msg.show({
							buttons: Ext.Msg.YESNO,
							icon: Ext.MessageBox.QUESTION,
							msg: msg,
							title: langs('Продолжить сохранение?'),
							fn: function (buttonId, text, obj) {
								options.params[action.result.ignoreParam] = 1;
								options.params.createConsult = action.result.createConsult;
								if (buttonId == 'yes') {
									if (action.result.ignoreParam == 'ignoreRemoteConsultCheck') {
										var CVIConsultRKC_id = action.result.CVIConsultRKC_id || null;
										var RepositoryObserv_sid = action.result.RepositoryObserv_sid || null;
										var Evn_id = base_form.findField('Evn_id').getValue();
										var dirData = {
											object: 'EvnSection',
											object_id: Evn_id,
											CVIConsultRKC_id: CVIConsultRKC_id,
											RepositoryObserv_sid: RepositoryObserv_sid,
											isRKC: true
										};
										var dirType = new Ext.data.Record({
											DirType_Code: 13,
											DirType_Name: "На удаленную консультацию",
											DirType_id: 17
										});
										if (win.parentWin.createDirection) {
											win.parentWin.createDirection(dirData, dirType, []);
										} else {
											var ctrl = win.parentWin.EvnPrescrPanel.getController();
											ctrl.createDirection(dirType, [], dirData);
										}
										
									}
								}
								
								win.doSave(options);
							}
						});
					} else if (action.result.RepositoryObserv_id) {
						base_form.findField('RepositoryObserv_id').setValue(action.result.RepositoryObserv_id);
						win.callback();
						if (options.print) {
							win.doPrint();
						} else {
							win.hide();
						}
					} else {
						sw.swMsg.show({
							buttons: Ext.Msg.OK,
							fn: function() {
								//win.hide();
							},
							icon: Ext.Msg.ERROR,
							msg: langs('При выполнении операции сохранения произошла ошибка') + '.<br/>' + langs('Пожалуйста, повторите попытку позже') + '.',
							title: langs('Ошибка')
						});
					}
				}
			}
		});
	},
	onHide: Ext.emptyFn,
	setPregnancyBlockAllowBLank: function() {
		if (this.useCase.inlist(['evnvizitpl','quarantine', 'evnpspriem'])) {
			return;
		}
		var
			allowBlank = true,
			base_form = this.RepositoryObservForm.getForm(),
			fields = [
				'RepositoryObserv_PregnancyPeriod',
				'GenConditFetus_id'
			];

		for (var i in fields) {
			if (typeof fields[i] == 'string' && !Ext.isEmpty(base_form.findField(fields[i]).getValue())) {
				allowBlank = false;
			}
		} 

		for (var i in fields) {
			if (typeof fields[i] == 'string') {
				base_form.findField(fields[i]).setAllowBlank(allowBlank);
			}
		} 
	},
	/**
	 * Показать поля для режимов запуска формы: ['quarantine','evnvizitpl','evnsection','reanim']
	 * свойства компонентов:
	 * useCases - показывает/скрывает поля, (если не описано то показывает)
	 * allowBlankCases - список режимов которым разрешено/запрещено пустое значение
	 * disabledCases - список режимов которым доступно/недоступно редактирование
	 */
	showFieldsForUseCase: function() {
		var win = this;
		var base_form = this.RepositoryObservForm.getForm();
		win.temperaturePanel.setVisible(win.useCase != 'quarantine');
		base_form.items.each(function(f) {
			f.setAllowBlank( win.getAllowBlankForField(f) );
			f.setContainerVisible( win.getVisibledForField(f) );
			f.setDisabled ( win.getDisabledForField(f) );
		});
	},
	// поле недоступно если описано в disabledCases или в disabled
	getDisabledForField: function( field ) {
		var win = this;
		var disabled = false;
		switch (true) {
			case win.action == 'view':
				disabled = true;
				break;
			case field.disabledCases && typeof field.disabledCases[win.useCase] == "boolean":
				disabled = field.disabledCases[win.useCase];
				break;
			case typeof field.initialConfig.disabled == 'boolean':
				disabled = field.initialConfig.disabled;
				break;
		}
		return disabled;
	},
	// поле видимо если режим описан режим или вообще не описаны режимы useCases
	getVisibledForField: function(field) {
		return !field.useCases || field.useCases && this.useCase.inlist(field.useCases)
	},
	updateFields: function() {
		var win = this,
			baseForm = win.RepositoryObservForm.getForm();

		switch(win.useCase) {
			case 'quarantine':
				var isInterviewed = !baseForm.findField('notInterviewed').getValue(), // опрошен
					isSetSymptom = isInterviewed && !baseForm.findField('IsEmptySymptom').getValue();

				baseForm.findField('RepositoryObserv_IsCVIQuestion').setValue(isInterviewed ? 2 : 1);

				// причина непроведения опроса
				win.setDisabledField('RepositoryObserv_CVIQuestionNotReason',isInterviewed);
				baseForm.findField('RepositoryObserv_CVIQuestionNotReason').setContainerVisible(!isInterviewed);

				// симптомы
				win.quarantineSymptomFields.forEach(function (fieldName) {
					win.setDisabledField(fieldName, !isSetSymptom);
				});

				// остальные поля
				var otherFields = ['RepositoryObserv_Cho','RepositoryObserv_GLU','RepositoryObserv_SpO2','RepositoryObserv_Diastolic','RepositoryObserv_Systolic','RepositoryObserv_Pulse','RepositoryObserv_BreathFrequency','IsEmptySymptom'];
				otherFields.forEach(function (fieldName) {
					win.setDisabledField(fieldName, !isInterviewed);
				});

				win.setDisabledField('RepositoryObserv_TemperatureFrom', !isSetSymptom || baseForm.findField('RepositoryObserv_IsHighTemperature').getValue() !=2);
				baseForm.findField('RepositoryObserv_TemperatureFrom').setContainerVisible(isSetSymptom && baseForm.findField('RepositoryObserv_IsHighTemperature').getValue()==2);
				break;

		}
	},
	setDisabledField: function(field, disabled) {
		var field = this.RepositoryObservForm.getForm().findField(field);
		field.setDisabled(disabled || this.action == 'view');
		if(this.action == 'view') return;
		!disabled || field.reset();
		!disabled || !field.clearValue || field.clearValue();
	},
	// поле обязательно если:
	// 1) если описано в allowBlankCases
	// 2) описан allowBlank и useCases
	// 3) описан allowBlank и useCases вообще не описан
	getAllowBlankForField: function(field) {
		var win = this;
		var allowBlank = true;
		switch (true) {
			// описано для режима в allowBlankCases
			case field.allowBlankCases && typeof field.allowBlankCases[win.useCase] == 'boolean':
				allowBlank = field.allowBlankCases[win.useCase];
				break;

			// описан режим и allowBlank
			case win.useCase.inlist(field.useCases) && typeof field.initialConfig.allowBlank == 'boolean':
			case !field.useCases && typeof field.initialConfig.allowBlank == 'boolean':
				allowBlank = field.initialConfig.allowBlank;
				break;

		}
		return allowBlank;
	},
	show: function() {
		sw.Promed.swRepositoryObservEditWindow.superclass.show.apply(this, arguments);

		var
			base_form = this.RepositoryObservForm.getForm(),
			win = this;

		if ( !arguments[0] || !arguments[0].Person_id ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.ERROR,
				msg: 'Ошибка открытия формы ' + win.id + '.<br/>Не указаны нужные входные параметры.',
				title: 'Ошибка'
			});
			win.hide();
			return false;
		}

		base_form.reset();

		win.RepositoryObservSopDiagGrid.hide();
		win.RepositoryObservSopDiagGrid.removeAll();
		win.doLayout();

		win.action = 'add';
		win.parentWin = null;
		win.useCase = 'evnsection';
		win.callback = Ext.emptyFn;
		win.onHide = Ext.emptyFn;
		win.RepositoryObserv_Height = null;
		win.RepositoryObserv_Weight = null;

		if ( arguments[0].action ) {
			win.action = arguments[0].action;
		}
		if ( arguments[0].parentWin ) {
			win.parentWin = arguments[0].parentWin;
		}

		if ( arguments[0].useCase ) {
			win.useCase = arguments[0].useCase;
		}

		if ( typeof arguments[0].callback == 'function' ) {
			win.callback = arguments[0].callback;
		}

		if ( typeof arguments[0].onHide == 'function' ) {
			win.onHide = arguments[0].onHide;
		}

		if ( !Ext.isEmpty(arguments[0].RepositoryObserv_Height) ) {
			win.RepositoryObserv_Height = arguments[0].RepositoryObserv_Height;
		}

		if ( !Ext.isEmpty(arguments[0].RepositoryObserv_Weight) ) {
			win.RepositoryObserv_Weight = arguments[0].RepositoryObserv_Weight;
		}
			
		win.hasPrev = arguments[0].hasPrev || false;

		win.showFieldsForUseCase();
		win.StatusFieldSet.setVisible(win.useCase.inlist(['evnsection', 'reanim']));
		base_form.setValues(arguments[0]);
		base_form.findField('RepositoryObserv_IVL').fireEvent('change', base_form.findField('RepositoryObserv_IVL'), base_form.findField('RepositoryObserv_IVL').getValue());
		base_form.findField('PlaceArrival_id').fireEvent('change', base_form.findField('PlaceArrival_id'), base_form.findField('PlaceArrival_id').getValue());

		var focusField = (win.useCase.inlist(['evnsection', 'reanim']) ? 'RepositoryObserv_IsResuscit' : 'PlaceArrival_id');
		
		this.PersonPanel.load({
			Person_id: arguments[0].Person_id,
			callback: function() {
				// Скрыть поля, зависящие от пола
				if (win.PersonPanel.getFieldValue('Sex_Code') == 1) {
					base_form.findField('GenConditFetus_id').clearValue();
					base_form.findField('GenConditFetus_id').setContainerVisible(false);
					base_form.findField('RepositoryObserv_PregnancyPeriod').setValue(null);
					base_form.findField('RepositoryObserv_PregnancyPeriod').setContainerVisible(false);
				}
				else {
					base_form.findField('GenConditFetus_id').setContainerVisible(win.useCase.inlist(['evnsection', 'reanim']));
					base_form.findField('RepositoryObserv_PregnancyPeriod').setContainerVisible(true);
				}
			}
		});

		var loadMask = new Ext.LoadMask(win.getEl(),{msg: LOAD_WAIT});
		loadMask.show();

		var title = win.getTitleName(win.useCase);

		switch ( win.action ) {
			case 'add':
				win.setTitle(title + ': ' + langs('Добавление'));
				win.setDefaultValues();

				if (!win.hasPrev) {
					Ext.Ajax.request({
						failure: function(response, options) {
							loadMask.hide();

							sw.swMsg.show({
								buttons: Ext.Msg.OK,
								fn: Ext.emptyFn,
								icon: Ext.Msg.ERROR,
								msg: 'Ошибка запроса к серверу.',
								title: 'Ошибка'
							});
						},
						success: function(response, options) {
							if (!Ext.isEmpty(response.responseText)) {
								var response_obj = Ext.util.JSON.decode(response.responseText);
								base_form.setValues(response_obj);

								if ( !Ext.isEmpty(win.RepositoryObserv_Height) ) {
									base_form.findField('RepositoryObserv_Height').setValue(win.RepositoryObserv_Height);
								}

								if ( !Ext.isEmpty(win.RepositoryObserv_Weight) ) {
									base_form.findField('RepositoryObserv_Weight').setValue(win.RepositoryObserv_Weight);
								}

								win.setPregnancyBlockAllowBLank();
								base_form.findField('RepositoryObserv_IVL').fireEvent('change', base_form.findField('RepositoryObserv_IVL'), base_form.findField('RepositoryObserv_IVL').getValue());
							}
						}.createDelegate(this),
						params: {
							Person_id: arguments[0].Person_id,
							Evn_id: arguments[0].Evn_id,
							CovidType_id: arguments[0].CovidType_id
						},
						url: '/?c=RepositoryObserv&m=getRepositoryObservDefaultData'
					});
				} else {
					base_form.findField('RepositoryObserv_IVL').fireEvent('change', base_form.findField('RepositoryObserv_IVL'), base_form.findField('RepositoryObserv_IVL').getValue());
					base_form.findField('PlaceArrival_id').fireEvent('change', base_form.findField('PlaceArrival_id'), base_form.findField('PlaceArrival_id').getValue());
					base_form.findField('RepositoryObserv_setDate').fireEvent('change', base_form.findField('RepositoryObserv_setDate'), base_form.findField('RepositoryObserv_setDate').getValue());
				}

				setCurrentDateTime({
					callback: function() {
						base_form.findField('RepositoryObserv_setDate').fireEvent('change', base_form.findField('RepositoryObserv_setDate'), base_form.findField('RepositoryObserv_setDate').getValue());
					},
					dateField: base_form.findField('RepositoryObserv_setDate'),
					loadMask: true,
					setDate: true,
					setDateMaxValue: true,
					setDateMinValue: false,
					setTime: true,
					timeField: base_form.findField('RepositoryObserv_setTime'),
					windowId: win.id
				});

				if (win.useCase.inlist(['evnsection', 'reanim']) && !Ext.isEmpty(base_form.findField('Evn_id').getValue())) {
					win.RepositoryObservSopDiagGrid.show();
					win.doLayout();
					win.RepositoryObservSopDiagGrid.loadData({
						globalFilters: {
							Evn_id: base_form.findField('Evn_id').getValue()
						},
						noFocusOnLoad: true
					});
				}

				base_form.findField(focusField).focus(true, 50);
				break;

			case 'edit':
			case 'view':
				if (win.action == 'edit') {
					win.setTitle(title + ': ' + langs('Редактирование'));
				}
				else {
					win.setTitle(title + ': ' + langs('Просмотр'));
				}

				base_form.load({
					params: {
						RepositoryObserv_id: base_form.findField('RepositoryObserv_id').getValue()
					},
					failure: function() {
						loadMask.hide();

						sw.swMsg.show({
							buttons: Ext.Msg.OK,
							fn: function() {
								win.hide();
							},
							icon: Ext.Msg.ERROR,
							msg: 'Ошибка запроса к серверу. Попробуйте повторить операцию.',
							title: 'Ошибка'
						});
					},
					success: function() {
						loadMask.hide();

						win.calcPaO2FiO2();
						win.setPregnancyBlockAllowBLank();

						if(win.useCase == "quarantine") {
							// не опрошен
							var notInterviewed = base_form.findField('RepositoryObserv_IsCVIQuestion').getValue() == 1;
							base_form.findField('notInterviewed').setValue(notInterviewed);

							// если опрошен
							if(!notInterviewed) {
								var isEmptySymptom = true;
								for (var i = 0; i < win.quarantineSymptomFields.length; ++i) {
									var fieldName = win.quarantineSymptomFields[i];
									if (base_form.findField(fieldName).getValue()) {
										isEmptySymptom = false;
										break;
									}
								}
								base_form.findField('IsEmptySymptom').setValue(isEmptySymptom);
							}
						}

						var fireEventFields = ['RepositoryObserv_IVL','PlaceArrival_id','RepositoryObserv_setDate', 'RepositoryObserv_IsHighTemperature' ];
						fireEventFields.forEach( function(fieldName)  {
							var field = base_form.findField(fieldName);
							field.fireEvent('change', field, field.getValue());
						});

						if (win.useCase.inlist(['evnsection', 'reanim']) && !Ext.isEmpty(base_form.findField('Evn_id').getValue())) {
							win.RepositoryObservSopDiagGrid.show();
							win.doLayout();
							win.RepositoryObservSopDiagGrid.loadData({
								globalFilters: {
									Evn_id: base_form.findField('Evn_id').getValue()
								},
								noFocusOnLoad: true
							});
						}

						if ( win.action == 'edit' ) {
							base_form.findField(focusField).focus(true, 50);
						}
						else {
							win.buttons[win.buttons.length - 1].focus();
						}
					},
					url: '/?c=RepositoryObserv&m=load'
				});
				break;
		}
	},
	getTitleName: function(useCase) {
		var title = langs('Наблюдение за пациентом');
		switch (useCase) {
			case 'quarantine':
				title = langs('Наблюдение за пациентом на карантине');
				break;
			case 'evnvizitpl':
				title = langs('Динамическое наблюдение по COVID-19');
				break;
		}
		return title;
	},
	setDefaultValues: function() {
		var form = this,
			baseForm = form.RepositoryObservForm.getForm();
		switch (form.useCase) {
			case 'quarantine':
				baseForm.setValues({
					'RepositoryObserv_IsHighTemperature': 1,
					'Dyspnea_id': 1,
					'Cough_id': 1,
					'RepositoryObserv_IsSoreThroat': 1,
					'RepositoryObserv_IsRunnyNose': 1,
					'RepositoryObserv_IsSputum': 1
				});
				break;
		}
	},
	//функция нужна только из зато этого firstTabIndex, если опишу в initComponent сломаю кнопку Tab
	getComponent: function(compName) {
		var form = this;
		var component = null
		switch (compName) {
			case 'temperaturePanel':
				form.temperaturePanel = new Ext.Panel({
					border: false,
					layout: 'column',
					items: [
						{
							border: false,
							layout: 'form',
							items: [{
								allowBlank: false,
								allowBlankCases: {quarantine: true},
								disabledCases: {quarantine: true},
								allowDecimals: true,
								allowNegative: false,
								decimalPrecision: 1,
								fieldLabel: langs('Температура тела, <sup>0</sup>С от'),
								name: 'RepositoryObserv_TemperatureFrom',
								tabIndex: form.firstTabIndex++,
								width: 50,
								xtype: 'numberfield'
							}]
						}, {
							border: false,
							labelWidth: 30,
							layout: 'form',
							items: [{
								allowBlank: false,
								allowBlankCases: {quarantine: true},
								disabledCases: {quarantine: true},
								allowDecimals: true,
								allowNegative: false,
								decimalPrecision: 1,
								fieldLabel: langs('до'),
								name: 'RepositoryObserv_TemperatureTo',
								tabIndex: form.firstTabIndex++,
								width: 50,
								xtype: 'numberfield'
							}]
						}]
				});
				return form.temperaturePanel;
		}
	},
	/* конструктор */
	initComponent: function() {
		// Форма с полями
		var form = this;

		this.PersonPanel = new sw.Promed.PersonInformationPanelShort({
			id: form.id + 'PersonInformationFrame',
			region: 'north'
		});
		this.RepositoryObservForm = new Ext.form.FormPanel({
			autoScroll: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px',
			border: false,
			buttonAlign: 'left',
			frame: false,
			id: 'RepositoryObservEditForm',
			labelAlign: 'right',
			labelWidth: 220,
			layout: 'form',
			region: 'center',
			items: [{
				name: 'RepositoryObserv_id',
				xtype: 'hidden'
			}, {
				name: 'Person_id',
				xtype: 'hidden'
			}, {
				name: 'Evn_id',
				xtype: 'hidden'
			},{
				name: 'PersonQuarantine_id',
				xtype: 'hidden'
			}, {
				name: 'CmpCallCard_id',
				xtype: 'hidden'
			}, {
				name: 'HomeVisit_id',
				xtype: 'hidden'
			}, {
				name: 'CVIQuestion_id',
				xtype: 'hidden'
			}, {
				name: 'MedPersonal_id',
				xtype: 'hidden'
			}, {
				allowBlank: false,
				comboSubject: 'PlaceArrival',
				hiddenName: 'PlaceArrival_id',
				fieldLabel: langs('Место прибытия'),
				tabIndex: form.firstTabIndex++,
				listeners: {
					'change': function(combo, newValue) {
						if (form.useCase.inlist(['evnvizitpl', 'evnpspriem'])) {
							var base_form = form.RepositoryObservForm.getForm();
							base_form.findField('KLCountry_id').setDisabled(form.action == 'view' || newValue != 1);
							base_form.findField('KLCountry_id').setAllowBlank(newValue != 1);
							base_form.findField('TransportMeans_id').setContainerVisible(newValue == 1);
							base_form.findField('TransportMeans_id').setAllowBlank(newValue != 1);
							base_form.findField('RepositoryObserv_TransportDesc').setContainerVisible(newValue == 1);
							base_form.findField('RepositoryObserv_TransportPlace').setContainerVisible(newValue == 1);
							base_form.findField('RepositoryObserv_TransportPlace').setAllowBlank(newValue != 1);
							base_form.findField('RepositoryObserv_TransportRoute').setContainerVisible(newValue == 1);
							base_form.findField('KLRgn_id').setDisabled(form.action == 'view' || newValue != 2);
							base_form.findField('KLRgn_id').setAllowBlank(newValue != 2);
							base_form.findField('RepositoryObserv_arrivalDate').setDisabled(form.action == 'view' || (newValue != 1 && newValue != 2));
							base_form.findField('RepositoryObserv_arrivalDate').setAllowBlank(newValue != 1 && newValue != 2);
							base_form.findField('RepositoryObserv_FlightNumber').setDisabled(form.action == 'view' || (newValue != 1 && newValue != 2));
							base_form.findField('RepositoryObserv_FlightNumber').setAllowBlank(newValue != 1 && newValue != 2);
							if (newValue != 1) {
								base_form.findField('KLCountry_id').clearValue();
								base_form.findField('TransportMeans_id').clearValue();
								base_form.findField('RepositoryObserv_TransportDesc').setValue('');
								base_form.findField('RepositoryObserv_TransportPlace').setValue('');
								base_form.findField('RepositoryObserv_TransportRoute').setValue('');
							}
							if (newValue != 2) {
								base_form.findField('KLRgn_id').clearValue();
							}
							if (newValue != 1 && newValue != 2) {
								base_form.findField('RepositoryObserv_arrivalDate').setValue('');
								base_form.findField('RepositoryObserv_FlightNumber').setValue('');
							}
						}
					}
				},
				useCases: ['evnvizitpl', 'evnpspriem'],
				width: 300,
				xtype: 'swcommonsprcombo'
			}, {
				hiddenName: 'KLCountry_id',
				fieldLabel: langs('Страна'),
				tabIndex: form.firstTabIndex++,
				useCases: ['evnvizitpl', 'evnpspriem'],
				width: 300,
				xtype: 'swklcountrycombo',
			}, {
				comboSubject: 'KLRgnRF',
				fieldLabel: langs('Регион РФ'),
				hiddenName: 'KLRgn_id',
				tabIndex: form.firstTabIndex++,
				useCases: ['evnvizitpl', 'evnpspriem'],
				width: 300,
				xtype: 'swcommonsprcombo'
			}, {
				fieldLabel: langs('Дата прибытия'),
				name: 'RepositoryObserv_arrivalDate',
				plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
				tabIndex: form.firstTabIndex++,
				useCases: ['evnvizitpl', 'evnpspriem'],
				xtype: 'swdatefield'
			}, {
				comboSubject: 'TransportMeans',
				fieldLabel: langs('Средство передвижения при въезде в РФ'),
				hiddenName: 'TransportMeans_id',
				prefix: 'nsi_',
				tabIndex: form.firstTabIndex++,
				useCases: ['evnvizitpl', 'evnpspriem'],
				width: 300,
				xtype: 'swcommonsprcombo'
			}, {
				fieldLabel: langs('Средство передвижения при въезде в РФ (детально)'),
				maxLength: 400,
				name: 'RepositoryObserv_TransportDesc',
				tabIndex: form.firstTabIndex++,
				useCases: ['evnvizitpl', 'evnpspriem'],
				width: 300,
				xtype: 'textfield'
			}, {
				fieldLabel: langs('Место въезда на территорию РФ'),
				maxLength: 400,
				name: 'RepositoryObserv_TransportPlace',
				tabIndex: form.firstTabIndex++,
				useCases: ['evnvizitpl', 'evnpspriem'],
				width: 300,
				xtype: 'textfield'
			}, {
				fieldLabel: langs('Маршрут передвижения по РФ'),
				maxLength: 400,
				name: 'RepositoryObserv_TransportRoute',
				tabIndex: form.firstTabIndex++,
				useCases: ['evnvizitpl', 'evnpspriem'],
				width: 300,
				xtype: 'textfield'
			}, {
				fieldLabel: langs('Рейс'),
				name: 'RepositoryObserv_FlightNumber',
				tabIndex: form.firstTabIndex++,
				useCases: ['evnvizitpl', 'evnpspriem'],
				width: 300,
				xtype: 'textfield'
			}, {
				allowBlank: false,
				fieldLabel: langs('Контакт с человеком с подтвержденным диагнозом КВИ'),
				hiddenName: 'RepositoryObserv_IsCVIContact',
				tabIndex: form.firstTabIndex++,
				useCases: ['evnvizitpl', 'evnpspriem'],
				width: 100,
				xtype: 'swyesnocombo'
			},  {
				allowBlank: true,
				hiddenName: 'RepositoryObserv_IsResuscit',
				fieldLabel: langs('Реанимация'),
				tabIndex: form.firstTabIndex++,
				useCases: ['evnsection', 'reanim'],
				width: 100,
				xtype: 'swyesnocombo'
			}, {
				allowBlank: true,
				comboSubject: 'LpuWardType',
				hiddenName: 'LpuWardType_id',
				fieldLabel: langs('Тип палаты'),
				loadParams: {params: {where: ' where LpuWardType_Code in (13,15)'}},
				tabIndex: form.firstTabIndex++,
				useCases: ['evnsection', 'reanim'],
				width: 300,
				xtype: 'swcommonsprcombo'
			}, {
				allowBlank: true,
				fieldLabel: langs('Номер телемедицинской консультации'),
				name: 'RepositoryObserv_NumberTMK',
				tabIndex: form.firstTabIndex++,
				useCases: ['evnsection', 'reanim'],
				width: 300,
				xtype: 'textfield'
			}, {
				border: false,
				layout: 'column',
				items: [{
					border: false,
					layout: 'form',
					items: [{
						allowBlank: false,
						fieldLabel: langs('Дата'),
						listeners: {
							'change': function(field, newValue, oldValue) {
								var base_form = form.RepositoryObservForm.getForm();
								var MedStaffFact_id = base_form.findField('MedStaffFact_id').getValue();

								base_form.findField('MedStaffFact_id').clearValue();

								var medstafffact_filter_params = {};

								if ( newValue ) {
									medstafffact_filter_params.onDate = Ext.util.Format.date(newValue, 'd.m.Y');
								}

								setMedStaffFactGlobalStoreFilter(medstafffact_filter_params);

								base_form.findField('MedStaffFact_id').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));

								if (!Ext.isEmpty(MedStaffFact_id)) {
									var index = base_form.findField('MedStaffFact_id').getStore().findBy(function(rec) {
										return (rec.get('MedStaffFact_id') == MedStaffFact_id);
									});

									if (index >= 0) {
										base_form.findField('MedStaffFact_id').setValue(MedStaffFact_id);
									}
									else {
										base_form.findField('MedStaffFact_id').clearValue();
									}
								}
							},
							'keydown': function (inp, e) {
								if ( e.shiftKey == true && e.getKey() == Ext.EventObject.TAB && false ) {
									e.stopEvent();
									form.buttons[me.buttons.length - 1].focus();
								}
							}
						},
						name: 'RepositoryObserv_setDate',
						plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
						tabIndex: form.firstTabIndex++,
						useCases: ['quarantine','evnsection', 'reanim'],
						xtype: 'swdatefield'
					}]
				}, {
					border: false,
					labelWidth: 50,
					layout: 'form',
					items: [{
						allowBlank: false,
						fieldLabel: langs('Время'),
						listeners: {
							'keydown': function (inp, e) {
								if ( e.getKey() == Ext.EventObject.F4 ) {
									e.stopEvent();
									inp.onTriggerClick();
								}
							}
						},
						name: 'RepositoryObserv_setTime',
						onTriggerClick: function() {
							var
								base_form = form.RepositoryObservForm.getForm(),
								time_field = base_form.findField('RepositoryObserv_setTime');

							if ( time_field.disabled ) {
								return false;
							}

							setCurrentDateTime({
								callback: function() {
									base_form.findField('RepositoryObserv_setDate').fireEvent('change', base_form.findField('RepositoryObserv_setDate'), base_form.findField('RepositoryObserv_setDate').getValue());
								},
								dateField: base_form.findField('RepositoryObserv_setDate'),
								loadMask: true,
								setDate: true,
								setDateMaxValue: true,
								setDateMinValue: false,
								setTime: true,
								timeField: time_field,
								windowId: form.id
							});
						},
						plugins: [ new Ext.ux.InputTextMask('99:99', true) ],
						tabIndex: form.firstTabIndex++,
						validateOnBlur: false,
						width: 60,
						useCases: ['quarantine','evnsection', 'reanim'],
						xtype: 'swtimefield'
					}]
				}]
			}, {
				allowBlank: false,
				allowDecimals: true,
				allowNegative: false,
				decimalPrecision: 1,
				fieldLabel: langs('Рост, см'),
				name: 'RepositoryObserv_Height',
				tabIndex: form.firstTabIndex++,
				useCases: ['evnsection', 'reanim'],
				width: 100,
				xtype: 'numberfield'
			}, {
				allowBlank: false,
				allowDecimals: true,
				allowNegative: false,
				decimalPrecision: 1,
				fieldLabel: langs('Вес пациента, кг'),
				name: 'RepositoryObserv_Weight',
				tabIndex: form.firstTabIndex++,
				useCases: ['evnsection', 'reanim'],
				width: 100,
				xtype: 'numberfield'
			}, {
				allowDecimals: false,
				allowNegative: false,
				fieldLabel: langs('Срок беременности'),
				listeners: {
					'change': function() {
						form.setPregnancyBlockAllowBLank();
					}
				},
				maxValue: 42,
				name: 'RepositoryObserv_PregnancyPeriod',
				tabIndex: form.firstTabIndex++,
				width: 100,
				xtype: 'numberfield'
			}, {
				comboSubject: 'GenConditFetus',
				hiddenName: 'GenConditFetus_id',
				fieldLabel: langs('Состояние плода (норма/патология)'),
				lastQuery: '',
				listeners: {
					'change': function() {
						form.setPregnancyBlockAllowBLank();
					},
					'select': function() {
						form.setPregnancyBlockAllowBLank();
					}
				},
				tabIndex: form.firstTabIndex++,
				typeCode: 'int',
				width: 300,
				xtype: 'swcommonsprcombo'
			}, {
				name: 'RepositoryObserv_IsCVIQuestion',
				xtype: 'hidden'
			}, {
				fieldLabel: langs('Опрос не проведен'),
				xtype: 'checkbox',
				name: 'notInterviewed',
				useCases: ['quarantine'],
				listeners: {
					change: function (me, newValue) {
						form.updateFields();
					}
				}
			}, {
				fieldLabel: langs('Причина непроведения опроса'),
				xtype: 'textarea',
				name: 'RepositoryObserv_CVIQuestionNotReason',
				width: 300,
				maxLength: 500,
				useCases: [] // [] используется для скрытия поля, в любом режиме
			}, {
				fieldLabel: langs('Симптомы отсутствуют'),
				xtype: 'checkbox',
				name: 'IsEmptySymptom',
				useCases: [ 'quarantine' ],
				listeners: {
					change: function(combo, newValue) {
						form.updateFields();
					}
				}
			}, {
				hiddenName: 'RepositoryObserv_IsHighTemperature',
				fieldLabel: langs('Повышенная температура'),
				tabIndex: form.firstTabIndex++,
				width: 100,
				xtype: 'swyesnocombo',
				useCases: ['quarantine'],
				listeners: {
					change: function(combo, newValue) {
						form.updateFields();
					}
				}
			},
			form.getComponent('temperaturePanel'),
			{
				allowBlankCases: {quarantine: true},
				allowBlank: false,
				allowDecimals: false,
				allowNegative: false,
				fieldLabel: langs('ЧДД в мин'),
				name: 'RepositoryObserv_BreathFrequency',
				tabIndex: form.firstTabIndex++,
				width: 100,
				xtype: 'numberfield'
			}, {
				allowBlankCases: {quarantine: true},
				allowBlank: false,
				allowDecimals: false,
				allowNegative: false,
				fieldLabel: langs('ЧСС в мин'),
				name: 'RepositoryObserv_Pulse',
				tabIndex: form.firstTabIndex++,
				width: 100,
				xtype: 'numberfield'
			}, {
				allowBlankCases: {quarantine: true},
				allowBlank: false,
				allowDecimals: false,
				allowNegative: false,
				fieldLabel: langs('Систолическое АД, мм рт.ст.'),
				name: 'RepositoryObserv_Systolic',
				tabIndex: form.firstTabIndex++,
				width: 100,
				xtype: 'numberfield'
			}, {
				allowBlankCases: {quarantine: true},
				allowBlank: false,
				allowDecimals: false,
				allowNegative: false,
				fieldLabel: langs('Диастолическое АД, мм рт.ст.'),
				name: 'RepositoryObserv_Diastolic',
				tabIndex: form.firstTabIndex++,
				width: 100,
				xtype: 'numberfield'
			}, {
				allowBlankCases: {quarantine: true},
				allowBlank: false,
				allowDecimals: true,
				minValue: 0.01,
				decimalPrecision: 2,
				//fieldLabel: langs('SpO<sub>2</sub>, %'),
				fieldLabel: 'Уровень насыщения крови кислородом, %',
				name: 'RepositoryObserv_SpO2',
				tabIndex: form.firstTabIndex++,
				useCases: [ 'quarantine', 'evnvizitpl', 'evnsection', 'reanim'],
				width: 100,
				xtype: 'numberfield'
			}, {
				allowBlankCases: {quarantine: true},
				allowDecimals: true,
				allowNegative: false,
				decimalPrecision: 2,
				fieldLabel: 'Уровень сахара в крови',
				name: 'RepositoryObserv_GLU',
				tabIndex: form.firstTabIndex++,
				useCases: ['quarantine'],
				width: 100,
				xtype: 'numberfield'
			}, {
				allowDecimals: true,
				allowBlankCases: {quarantine: true},
				allowNegative: false,
				decimalPrecision: 2,
				fieldLabel: 'Общий холестерин',
				name: 'RepositoryObserv_Cho',
				tabIndex: form.firstTabIndex++,
				useCases: ['quarantine'],
				width: 100,
				xtype: 'numberfield'
			}, {
				allowBlank: false,
				allowBlankCases: {quarantine: true},
				comboSubject: 'Dyspnea',
				hiddenName: 'Dyspnea_id',
				fieldLabel: langs('Одышка'),
				lastQuery: '',
				tabIndex: form.firstTabIndex++,
				typeCode: 'int',
				width: 300,
				xtype: 'swcommonsprcombo'
			}, {
				allowBlankCases: {quarantine: true},
				allowBlank: false,
				comboSubject: 'Cough',
				hiddenName: 'Cough_id',
				fieldLabel: langs('Кашель'),
				lastQuery: '',
				tabIndex: form.firstTabIndex++,
				typeCode: 'int',
				width: 300,
				xtype: 'swcommonsprcombo'
			}, {
				allowBlankCases: {quarantine: true},
				allowBlank: false,
				hiddenName: 'RepositoryObserv_IsSputum',
				fieldLabel: langs('Мокрота'),
				tabIndex: form.firstTabIndex++,
				width: 100,
				xtype: 'swyesnocombo'
			}, {
				allowBlankCases: {quarantine: true},
				allowBlank: false,
				hiddenName: 'RepositoryObserv_IsRunnyNose',
				fieldLabel: langs('Насморк'),
				tabIndex: form.firstTabIndex++,
				width: 100,
				xtype: 'swyesnocombo',
				useCases: ['quarantine']
			}, {
				allowBlank: false,
				allowBlankCases: {quarantine: true},
				hiddenName: 'RepositoryObserv_IsSoreThroat',
				fieldLabel: langs('Боль в горле'),
				tabIndex: form.firstTabIndex++,
				width: 100,
				xtype: 'swyesnocombo',
				useCases: ['quarantine']
			}, {
				fieldLabel: 'Иные симптомы',
				allowBlankCases: {quarantine: true},
				name: 'RepositoryObserv_Other',
				tabIndex: form.firstTabIndex++,
				useCases: [ 'quarantine','evnvizitpl', 'evnpspriem'],
				width: 300,
				xtype: 'textfield'
			}, {
				allowBlank: true,
				allowDecimals: true,
				allowNegative: false,
				decimalPrecision: 2,
				fieldLabel: langs('Гемоглобин, г/л'),
				name: 'RepositoryObserv_Hemoglobin',
				tabIndex: form.firstTabIndex++,
				useCases: ['evnsection', 'reanim'],
				width: 100,
				xtype: 'numberfield'
			}, {
				allowBlank: true,
				allowDecimals: true,
				allowNegative: false,
				decimalPrecision: 2,
				fieldLabel: langs('Лейкоциты, х 10<sup>9</sup>/л'),
				name: 'RepositoryObserv_Leukocytes',
				tabIndex: form.firstTabIndex++,
				useCases: ['evnsection', 'reanim'],
				width: 100,
				xtype: 'numberfield'
			}, {
				allowBlank: true,
				allowDecimals: true,
				allowNegative: false,
				decimalPrecision: 2,
				fieldLabel: langs('Лимфоциты, %'),
				name: 'RepositoryObserv_Lymphocytes',
				tabIndex: form.firstTabIndex++,
				useCases: ['evnsection', 'reanim'],
				width: 100,
				xtype: 'numberfield'
			}, {
				allowBlank: true,
				allowDecimals: true,
				allowNegative: false,
				decimalPrecision: 2,
				fieldLabel: langs('Тромбоциты, х 10<sup>9</sup>/л'),
				name: 'RepositoryObserv_Platelets',
				tabIndex: form.firstTabIndex++,
				useCases: ['evnsection', 'reanim'],
				width: 100,
				xtype: 'numberfield'
			}, {
				allowBlank: true,
				allowDecimals: true,
				allowNegative: false,
				decimalPrecision: 2,
				fieldLabel: langs('СОЭ, мм/ч'),
				name: 'RepositoryObserv_SOE',
				tabIndex: form.firstTabIndex++,
				useCases: ['evnsection', 'reanim'],
				width: 100,
				xtype: 'numberfield'
			}, {
				allowBlank: true,
				allowDecimals: true,
				allowNegative: false,
				decimalPrecision: 2,
				fieldLabel: langs('СРБ, мм/ч'),
				name: 'RepositoryObserv_SRB',
				tabIndex: form.firstTabIndex++,
				useCases: ['evnsection', 'reanim'],
				width: 100,
				xtype: 'numberfield'
			}, {
				allowBlank: true,
				allowDecimals: true,
				allowNegative: false,
				decimalPrecision: 2,
				fieldLabel: langs('pH'),
				name: 'RepositoryObserv_PH',
				tabIndex: form.firstTabIndex++,
				useCases: ['evnsection', 'reanim'],
				width: 100,
				xtype: 'numberfield'
			}, {
				allowBlank: false,
				fieldLabel: langs('Кислород'),
				forceSelection: true,
				hiddenName: 'RepositoryObserv_Oxygen',
				store: [
					[1, 'Нет'],
					[2, 'Нуждается'],
					[3, 'Да']
				],
				tabIndex: form.firstTabIndex++,
				tpl: new Ext.XTemplate(
					'<tpl for="."><div class="x-combo-list-item">',
					'<font color="red">{value}</font>&nbsp',
					'{text}',
					'</div></tpl>'
				),
				triggerAction: 'all',
				useCases: ['evnsection', 'reanim'],
				width: 100,
				xtype: 'combo'
			}, {
				allowBlank: true,
				allowDecimals: false,
				allowNegative: false,
				fieldLabel: langs('РаО<sub>2</sub>'),
				listeners: {
					'change': function() {
						form.calcPaO2FiO2();
					}
				},
				name: 'RepositoryObserv_PaO2',
				tabIndex: form.firstTabIndex++,
				useCases: ['evnsection', 'reanim'],
				width: 100,
				xtype: 'numberfield'
			}, {
				allowBlank: true,
				allowDecimals: false,
				allowNegative: false,
				fieldLabel: langs('Фракция кислорода на вдохе (FiO<sub>2</sub>), %'),
				listeners: {
					'change': function() {
						form.calcPaO2FiO2();
					}
				},
				name: 'RepositoryObserv_FiO2',
				tabIndex: form.firstTabIndex++,
				useCases: ['evnsection', 'reanim'],
				width: 100,
				xtype: 'numberfield'
			}, {
				allowBlank: false,
				allowDecimals: false,
				allowNegative: false,
				disabled: true,
				fieldLabel: langs('Респираторный индекс (PaO<sub>2</sub>/FiO<sub>2</sub>)'),
				name: 'RepositoryObserv_PaO2FiO2',
				tabIndex: form.firstTabIndex++,
				useCases: ['evnsection', 'reanim'],
				width: 100,
				xtype: 'numberfield'
			}, {
				allowBlank: false,
				fieldLabel: langs('ИВЛ'),
				forceSelection: true,
				hiddenName: 'RepositoryObserv_IVL',
				listeners: {
					'change': function(combo, newValue) {
						if (form.useCase.inlist(['evnsection', 'reanim'])) {
							var base_form = form.RepositoryObservForm.getForm();
							var allowBlankIvl = newValue != 3;
							form.IVLFieldSet.setVisible(!allowBlankIvl);
							// base_form.findField('RepositoryObserv_RegimVenting').setAllowBlank(allowBlankIvl);
							base_form.findField('IVLRegim_id').setAllowBlank(allowBlankIvl);
							base_form.findField('RepositoryObserv_BreathRate').setAllowBlank(allowBlankIvl);
							base_form.findField('RepositoryObserv_BreathVolume').setAllowBlank(allowBlankIvl);
							base_form.findField('RepositoryObserv_BreathPressure').setAllowBlank(allowBlankIvl);
							base_form.findField('RepositoryObserv_BreathPeep').setAllowBlank(allowBlankIvl);
							base_form.findField('RepositoryObserv_IsPronPosition').setAllowBlank(allowBlankIvl);
							base_form.findField('RepositoryObserv_IsMyoplegia').setAllowBlank(allowBlankIvl);
							base_form.findField('RepositoryObserv_IsSedation').setAllowBlank(allowBlankIvl);
						} else {
							form.IVLFieldSet.setVisible(false);
						}
					}	
				},
				store: [
					[1, 'Нет'],
					[2, 'Нуждается'],
					[3, 'Да']
				],
				tabIndex: form.firstTabIndex++,
				tpl: new Ext.XTemplate(
					'<tpl for="."><div class="x-combo-list-item">',
					'<font color="red">{value}</font>&nbsp',
					'{text}',
					'</div></tpl>'
				),
				triggerAction: 'all',
				useCases: ['evnsection', 'reanim'],
				width: 100,
				xtype: 'combo'
			}, form.IVLFieldSet = new Ext.form.FieldSet({
				autoHeight: true,
				labelWidth: 220,
				layout: 'form',
				style: 'padding: 0',
				title: langs('Параметры ИВЛ'),
				xtype: 'fieldset',
				items: [/*{
					anchor: '95%',
					fieldLabel: langs('Режим вентиляции (аббревиатура)'),
					name: 'RepositoryObserv_RegimVenting',
					tabIndex: form.firstTabIndex++,
					xtype: 'textfield'
				},*/ {
					anchor: '95%',
					comboSubject: 'IVLRegim',
					hiddenName: 'IVLRegim_id',
					fieldLabel: langs('Режим вентиляции'),
					lastQuery: '',
					tabIndex: form.firstTabIndex++,
					useCases: ['evnsection', 'reanim'],
					typeCode: 'int',
					xtype: 'swcommonsprcombo'
				}, {
					allowDecimals: false,
					allowNegative: false,
					fieldLabel: langs('Частота дыхания (f), в мин'),
					name: 'RepositoryObserv_BreathRate',
					tabIndex: form.firstTabIndex++,
					useCases: ['evnsection', 'reanim'],
					width: 100,
					xtype: 'numberfield'
				}, {
					allowDecimals: false,
					allowNegative: false,
					fieldLabel: langs('Дыхательный объем (Vt), мл'),
					name: 'RepositoryObserv_BreathVolume',
					tabIndex: form.firstTabIndex++,
					useCases: ['evnsection', 'reanim'],
					width: 100,
					xtype: 'numberfield'
				}, {
					allowDecimals: false,
					allowNegative: false,
					fieldLabel: langs('Давление на вдохе (Ppeak), см вод.ст.'),
					name: 'RepositoryObserv_BreathPressure',
					tabIndex: form.firstTabIndex++,
					useCases: ['evnsection', 'reanim'],
					width: 100,
					xtype: 'numberfield'
				}, {
					allowDecimals: true,
					allowNegative: false,
					decimalPrecision: 2,
					fieldLabel: langs('ПДКВ (PEEP), см вод.ст.'),
					name: 'RepositoryObserv_BreathPeep',
					tabIndex: form.firstTabIndex++,
					useCases: ['evnsection', 'reanim'],
					width: 100,
					xtype: 'numberfield'
				}, {
					hiddenName: 'RepositoryObserv_IsPronPosition',
					fieldLabel: langs('Прон-позиция'),
					tabIndex: form.firstTabIndex++,
					useCases: ['evnsection', 'reanim'],
					width: 100,
					xtype: 'swyesnocombo'
				}, {
					hiddenName: 'RepositoryObserv_IsMyoplegia',
					fieldLabel: langs('Миоплегия'),
					tabIndex: form.firstTabIndex++,
					useCases: ['evnsection', 'reanim'],
					width: 100,
					xtype: 'swyesnocombo'
				}, {
					hiddenName: 'RepositoryObserv_IsSedation',
					fieldLabel: langs('Седация'),
					tabIndex: form.firstTabIndex++,
					useCases: ['evnsection', 'reanim'],
					width: 100,
					xtype: 'swyesnocombo'
				}]
			}), form.StatusFieldSet = new Ext.form.FieldSet({
				autoHeight: true,
				labelWidth: 220,
				layout: 'form',
				style: 'padding: 0',
				title: langs('Оценка состояния'),
				xtype: 'fieldset',
				items: [{
					allowBlank: false,
					comboSubject: 'StateDynamic',
					hiddenName: 'StateDynamic_id',
					fieldLabel: langs('Динамика'),
					lastQuery: '',
					tabIndex: form.firstTabIndex++,
					useCases: ['evnsection', 'reanim'],
					typeCode: 'int',
					width: 300,
					xtype: 'swcommonsprcombo'
				}, {
					allowBlank: false,
					comboSubject: 'DiagSetPhase',
					hiddenName: 'DiagSetPhase_id',
					fieldLabel: langs('Состояние'),
					lastQuery: '',
					loadParams: {params: {where: ' where DiagSetPhase_id in (1,2,3,6)'}},
					tabIndex: form.firstTabIndex++,
					useCases: ['evnsection', 'reanim'],
					typeCode: 'int',
					width: 300,
					xtype: 'swcommonsprcombo'
				}]
			}), {
				allowBlank: false,
				fieldLabel: langs('ЭКМО'),
				hiddenName: 'RepositoryObserv_IsEKMO',
				tabIndex: form.firstTabIndex++,
				useCases: ['evnsection', 'reanim'],
				width: 100,
				xtype: 'swyesnocombo'
			}, {
				allowBlank: false,
				fieldLabel: langs('Противовирусное лечение'),
				hiddenName: 'RepositoryObserv_IsAntivirus',
				tabIndex: form.firstTabIndex++,
				useCases: ['evnsection', 'reanim'],
				width: 100,
				xtype: 'swyesnocombo'
			}, {
				comboSubject: 'CovidType',
				hiddenName: 'CovidType_id',
				fieldLabel: langs('Коронавирус'),
				tabIndex: form.firstTabIndex++,
				useCases: ['evnsection', 'reanim'],
				width: 300,
				xtype: 'swcommonsprcombo'
			}, {
				allowBlank: false,
				comboSubject: 'DiagConfirmType',
				hiddenName: 'DiagConfirmType_id',
				fieldLabel: langs('Диагноз подтвержден рентгенологически'),
				tabIndex: form.firstTabIndex++,
				width: 300,
				xtype: 'swcommonsprcombo'
			}, {
				allowBlank: false,
				anchor: '95%',
				fieldLabel: langs('Врач'),
				hiddenName: 'MedStaffFact_id',
				lastQuery: '',
				listWidth: 750,
				tabIndex: form.firstTabIndex++,
				useCases: ['evnsection', 'reanim'],
				xtype: 'swmedstafffactglobalcombo'
			}, {
				fieldLabel: langs('Контактный телефон врача') + ' +7',
				fieldWidth: 120,
				name: 'MedPersonal_Phone',
				tabIndex: form.firstTabIndex++,
				useCases: ['evnsection', 'reanim'],
				xtype: 'swphonefield'
			}, {
				fieldLabel: langs('E-mail врача'),
				name: 'MedPersonal_Email',
				width: 300,
				tabIndex: form.firstTabIndex++,
				useCases: ['evnsection', 'reanim'],
				xtype: 'textfield'
			}],
			keys: [{
				alt: true,
				fn: function(inp, e) {
					switch ( e.getKey() ) {
						case Ext.EventObject.C:
							if (form.action != 'view') {
								form.doSave(false);
							}
							break;

						case Ext.EventObject.J:
							form.hide();
							break;
					}
				},
				key: [ Ext.EventObject.C, Ext.EventObject.J ],
				scope: this,
				stopEvent: true
			}],
			reader: new Ext.data.JsonReader({
				success: Ext.emptyFn
			}, [
				{ name: 'RepositoryObserv_id' },
				{ name: 'PersonQuarantine_id' },
				{ name: 'Person_id' },
				{ name: 'Evn_id' },
				{ name: 'CmpCallCard_id' },
				{ name: 'HomeVisit_id' },
				{ name: 'CVIQuestion_id' },
				{ name: 'LpuWardType_id' },
				{ name: 'RepositoryObserv_arrivalDate' },
				{ name: 'RepositoryObserv_BreathFrequency' },
				{ name: 'RepositoryObserv_BreathPeep' },
				{ name: 'RepositoryObserv_BreathPressure' },
				{ name: 'RepositoryObserv_BreathRate' },
				{ name: 'RepositoryObserv_BreathVolume' },
				{ name: 'RepositoryObserv_Diastolic' },
				{ name: 'RepositoryObserv_FiO2' },
				{ name: 'RepositoryObserv_FlightNumber' },
				{ name: 'RepositoryObserv_Height' },
				{ name: 'RepositoryObserv_Hemoglobin' },
				{ name: 'RepositoryObserv_IsAntivirus' },
				{ name: 'RepositoryObserv_IsCVIContact' },
				{ name: 'RepositoryObserv_IsEKMO' },
				{ name: 'RepositoryObserv_IsMyoplegia' },
				{ name: 'RepositoryObserv_IsPronPosition' },
				{ name: 'RepositoryObserv_IsResuscit' },
				{ name: 'RepositoryObserv_IsSedation' },
				{ name: 'RepositoryObserv_IsSputum' },
				{ name: 'RepositoryObserv_Leukocytes' },
				{ name: 'RepositoryObserv_Lymphocytes' },
				{ name: 'RepositoryObserv_NumberTMK' },
				{ name: 'RepositoryObserv_PaO2' },
				{ name: 'RepositoryObserv_PaO2FiO2' },
				{ name: 'RepositoryObserv_IVL' },
				{ name: 'RepositoryObserv_PH' },
				{ name: 'RepositoryObserv_Oxygen' },
				{ name: 'RepositoryObserv_Other' },
				{ name: 'RepositoryObserv_Platelets' },
				{ name: 'RepositoryObserv_PregnancyPeriod' },
				{ name: 'RepositoryObserv_Pulse' },
				{ name: 'RepositoryObserv_RegimVenting' },
				{ name: 'RepositoryObserv_setDate' },
				{ name: 'RepositoryObserv_setTime' },
				{ name: 'RepositoryObserv_SOE' },
				{ name: 'RepositoryObserv_SpO2' },
				{ name: 'RepositoryObserv_SRB' },
				{ name: 'RepositoryObserv_Systolic' },
				{ name: 'RepositoryObserv_TemperatureFrom' },
				{ name: 'RepositoryObserv_TemperatureTo' },
				{ name: 'RepositoryObserv_TransportDesc' },
				{ name: 'RepositoryObserv_TransportPlace' },
				{ name: 'RepositoryObserv_TransportRoute' },
				{ name: 'RepositoryObserv_Weight' },
				{ name: 'RepositoryObserv_IsSoreThroat' },
				{ name: 'RepositoryObserv_IsRunnyNose' },
				{ name: 'RepositoryObserv_GLU' },
				{ name: 'RepositoryObserv_Cho' },
				{ name: 'RepositoryObserv_IsCVIQuestion' },
				{ name: 'RepositoryObserv_IsHighTemperature' },
				{ name: 'RepositoryObserv_CVIQuestionNotReason' },
				{ name: 'Cough_id' },
				{ name: 'StateDynamic_id' },
				{ name: 'DiagSetPhase_id' },
				{ name: 'Dyspnea_id' },
				{ name: 'GenConditFetus_id' },
				{ name: 'IVLRegim_id' },
				{ name: 'PlaceArrival_id' },
				{ name: 'KLCountry_id' },
				{ name: 'KLRgn_id' },
				{ name: 'TransportMeans_id' },
				{ name: 'MedPersonal_id' },
				{ name: 'MedStaffFact_id' },
				{ name: 'CovidType_id' },
				{ name: 'DiagConfirmType_id' },
				{ name: 'MedPersonal_Email' },
				{ name: 'MedPersonal_Phone' }
			]),
			timeout: 600,
			url: '/?c=RepositoryObserv&m=save'
		});

		form.RepositoryObservSopDiagGrid = new sw.Promed.ViewFrame({
			actions: [
				{name: 'action_add', disabled: true, hidden: true},
				{name: 'action_edit', disabled: true, hidden: true},
				{name: 'action_view', disabled: true, hidden: true},
				{name: 'action_delete', disabled: true, hidden: true},
				{name: 'action_refresh', disabled: true, hidden: true},
				{name: 'action_print', disabled: true, hidden: true}
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 200,
			autoLoadData: false,
			border: true,
			dataUrl: '/?c=RepositoryObserv&m=loadSopDiagList',
			height: 120,
			id: form.id + 'RepositoryObservSopDiagGrid',
			paging: false,
			region: 'south',
			stringfields: [
				{name: 'Diag_id', type: 'int', header: 'ID', key: true},
				{name: 'Diag_FullName', type: 'string', header: langs('Сопутствующие диагнозы'), width: 200, id: 'autoexpand'}
			],
			title: '',
			toolbar: false
		});

		Ext.apply(this, {
			buttons: [{
				handler: function() {
					form.doSave();
				},
				iconCls: 'save16',
				tabIndex: form.firstTabIndex++,
				text: BTN_FRMSAVE
			}, {
				handler: function()	{
					form.doPrint();
				},
				iconCls: 'print16',
				tabIndex: form.firstTabIndex++,
				hidden: getRegionNick() != 'msk',
				text: BTN_FRMPRINT
			}, {
				text: '-'
			},
			HelpButton(this, form.firstTabIndex++),
			{
				handler: function() {
					form.hide();
				},
				iconCls: 'cancel16',
				tabIndex: form.firstTabIndex++,
				text: BTN_FRMCANCEL
			}],
			items: [
				form.PersonPanel,
				form.RepositoryObservForm,
				form.RepositoryObservSopDiagGrid
			]
		});

		sw.Promed.swRepositoryObservEditWindow.superclass.initComponent.apply(this, arguments);
	}
});