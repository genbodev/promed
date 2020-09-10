/**
 * Наблюдение за пациентом, находящимся на карантине
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * @autor      Salavat Magafurov
 * @version    08.04.2020
 */

sw.Promed.swPersonQuarantineEditWindow = Ext.extend(sw.Promed.BaseForm, {
	objectName: 'swPersonQuarantineEditWindow',
	titleString: langs('Контрольная карта пациента на карантине'),
	modal: false,
	maximized: true,
	layout: 'border',
	autoScroll: true,
	callback: Ext.emptyFn,

	saveForm: function() {
		var win = this;
		var callback = function () {
			win.hide();
			win.callback();
		};

		win.formPanel.saveConfirmMsg = langs('Контрольная карта будет закрыта, карантин снят, форма будет недоступна для редактирования. Вы уверены, что хотите продолжить?');
		win.formPanel.saveConfirmCondition = function() {
			return !!win.formPanel.getForm().findField('PersonQuarantine_endDT').getValue()
		};

		win.formPanel.saveForm({
			onSave: callback
		});
	},
	setWinAction: function (action) {
		var win = this,
			form = win.formPanel.getForm();
		win.action = action;
		form.findField('DayOfQuarantine').setContainerVisible(win.action.inlist(['edit', 'view']));
		win.updateFields();
		win.btnSave.setVisible(action != 'view');
		win.RepositoryObservGrid.setReadOnly(action == 'view');
		win.setFormTitle(action);
	},
	show: function () {
		sw.Promed.swPersonQuarantineEditWindow.superclass.show.apply(this, arguments);

		var params = arguments[0],
			win = this,
			form = win.formPanel.getForm();
		win.callback = params.callback ? params.callback : Ext.emptyFn;
		win.owner = params.owner ? params.owner : false;
		form.reset();
		form.setValues(params);
		win.setWinAction(params.action || 'view');

		if( win.action == 'add' ) {
			form.findField('PersonQuarantine_begDT').setValue( getGlobalOptions().date );
			win.updateFields();
		}

		if( win.action.inlist(['edit','view']) ) {
			var loadParams = {};
			if(!params.PersonQuarantine_id) {
				loadParams.Person_id = params.Person_id;
				win.formPanel.url = '/?c=PersonQuarantine&m=getLastOpenedQuarantineCard';
			} else {
				loadParams.PersonQuarantine_id = params.PersonQuarantine_id;
				win.formPanel.url = '/?c=PersonQuarantine&m=loadEditForm';
			}
			win.formPanel.loadForm(loadParams);
		}

		win.RepositoryObservGrid.setParam('PersonQuarantine_id', params.PersonQuarantine_id);
		win.RepositoryObservGrid.removeAll();

		win.PersonInfoFrame.setTitle('Загрузка...');
		win.PersonInfoFrame.load({
			Person_id: params.Person_id,
			Server_id: params.Server_id,
			callback: function () {
				win.PersonInfoFrame.setPersonTitle();
			}
		});
	},
	setFormTitle: function(action) {
		var win = this;
		switch (action) {
			case "edit":
				win.setTitle(win.titleString + ': ' + langs('Редактирование') );
				break;
			case "add":
				win.setTitle(win.titleString + ': ' +  langs("Добавление") );
				break;
			default:
				win.setTitle(win.titleString + ': '  + langs('Просмотр') );
		}
	},
	// логика скрытия/дизейбла/очистки полей
	updateFields: function() {
		var form = this.formPanel.getForm(),
			openReasonField = form.findField('PersonQuarantineOpenReason_id'),
			begDateField = form.findField('PersonQuarantine_begDT'),
			endDateField = form.findField('PersonQuarantine_endDT'),
			approveDateField = form.findField('PersonQuarantine_approveDT'),
			arrivalDateField = form.findField('RepositoryObserv_arrivalDate'),
			contactDateField = form.findField('RepositoryObesrv_contactDate'),
			closeReasonField = form.findField('PersonQuarantineCloseReason_id'),
			placeArrivalField = form.findField('PlaceArrival_id'),
			countryField = form.findField('KLCountry_id'),
			regionField = form.findField('KLRgn_id'),
			transportField = form.findField('TransportMeans_id'),
			transportDescr = form.findField('RepositoryObserv_TransportDesc'),
			transportPlaceField = form.findField('RepositoryObserv_TransportPlace'),
			transportRouteField = form.findField('RepositoryObserv_TransportRoute'),
			flighNumberField = form.findField('RepositoryObserv_FlightNumber'),
			quarantineEndDT = endDateField.getValue(),
			today = getGlobalOptions().date,
			isView = this.action == 'view';

		begDateField.setDisabled(isView); // дата открытия
		endDateField.setMinValue(begDateField.getValue());
		endDateField.setDisabled(isView); // дата закрытия
		openReasonField.setDisabled(isView); // причина открытия
		//closeReasonField.setDisabled(isView); // причина закрытия
		approveDateField.setDisabled(isView); // дата выявления заболевания

		// прибывший
		var isArrival = openReasonField.getValue() == 1;

		// дата прибытия
		arrivalDateField.setContainerVisible(isArrival);
		arrivalDateField.setDisabled(!isArrival || isView);
		if( isArrival && !arrivalDateField.getValue() ) arrivalDateField.setValue(today);
		if( !isArrival ) arrivalDateField.reset();

		// место прибытия
		placeArrivalField.setContainerVisible(isArrival);
		placeArrivalField.setDisabled(!isArrival || isView);
		isArrival || placeArrivalField.clearValue();

		// прибыл из другой страны
		var isCountry = placeArrivalField.getValue() == 1;

		// страна
		countryField.setContainerVisible(isCountry);
		countryField.setDisabled(!isCountry || isView);
		isCountry || countryField.clearValue();

		// средство передвижения
		transportField.setContainerVisible(isCountry);
		transportField.setDisabled(!isCountry || isView);
		isCountry || transportField.clearValue();

		// средство передвижения детально
		transportDescr.setContainerVisible(isCountry);
		transportDescr.setDisabled(!isCountry || isView);
		isCountry || transportDescr.reset();

		// место въезда в рф
		transportPlaceField.setContainerVisible(isCountry);
		transportPlaceField.setDisabled(!isCountry || isView);
		isCountry || transportPlaceField.reset();

		// маршрут
		transportRouteField.setContainerVisible(isCountry);
		transportRouteField.setDisabled(!isCountry || isView);
		isCountry || transportRouteField.reset();

		// дата выявления заболевания
		approveDateField.setAllowBlank(openReasonField.getValue() != 3);

		// прибыл из другого региона
		var isRegion = placeArrivalField.getValue() == 2;

		// регион
		regionField.setContainerVisible(isRegion);
		regionField.setDisabled(!isRegion || isView);
		isRegion || regionField.clearValue();

		flighNumberField.setContainerVisible(isCountry || isRegion);
		flighNumberField.setDisabled(!isCountry && !isRegion);
		(isCountry || isRegion) || flighNumberField.reset();

		// был контакт с человеком
		var isContact = openReasonField.getValue() == 2;

		// дата контакта
		contactDateField.setContainerVisible(isContact);
		contactDateField.setDisabled(!isContact || isView);
		if( isContact && !contactDateField.getValue() ) contactDateField.setValue(today);
		if( !isContact ) contactDateField.reset();

		// причина закрытия
		closeReasonField.setContainerVisible(!!quarantineEndDT);
		closeReasonField.setDisabled(!quarantineEndDT || isView);
		quarantineEndDT || closeReasonField.clearValue();

		this.doLayout();
	},
	initComponent: function () {
		var win = this,
			today = getGlobalOptions().date;

		win.RepositoryObservGrid = new sw.Promed.ViewFrame({
			object: 'RepositoryObserv',
			border: false,
			region: 'south',
			height: 300,
			groups:false,
			autoLoadData: false,
			useEmptyRecord: false,
			autoexpand: 'autoexpand',
			dataUrl: '/?c=RepositoryObserv&m=loadQuarantineList',
			editformclassname: 'swRepositoryObservEditWindow',
			deleteType: 'object',
			actions: [
				{ name: 'action_print', hidden: true },
				{ name: 'action_delete', msg: langs('Выбранное наблюдение будет удалено. Вы хотите продолжить?') },
				{ name: 'action_view' },
				{ name: 'action_edit' },
				{ name: 'action_refresh'}
			],
			stringfields: [
				{ type: 'int', name: 'RepositoryObserv_id',  header: 'ID', key: true },
				{ type: 'int', name: 'Cough_id', hidden: true },
				{ type: 'int', name: 'Dyspnea_id', hidden: true },
				{ name: 'DayOfQuarantine', header: langs('День карантина'), width: 100,
					renderer: function (rowNum,cell,rec) {
						var lastDT = Date.parseDate(rec.get('RepositoryObserv_setDT').format('d.m.Y'), 'd.m.Y');
						var endDT = win.formPanel.getForm().findField('PersonQuarantine_endDT').getValue();
						log(lastDT,endDT);
						if(!lastDT) return null;
						if(endDT && lastDT > endDT)
							lastDT = endDT;
						return win.getQuarantineDays( lastDT );
					}
				},
				{ name: 'RepositoryObserv_setDT', header: langs('Дата наблюдения'), type: 'datetime', width: 100 },
				{ name: 'RepositoryObserv_TemperatureFrom', header: langs('Температура тела'), type: 'float' },
				{ name: 'Cough_Name', header: langs('Кашель') },
				{ name: 'Dyspnea_Name', header: langs('Одышка') },
				{ name: 'RepositoryObserv_IsSputum', header: langs('Мокрота'),
					renderer: function (rowNum,col,rec) {
						var val = rec.get('RepositoryObserv_IsSputum');
						return !val ? '' : (val==2 ? 'Да' : 'Нет');
					}
				},
				{ name: 'RepositoryObserv_IsRunnyNose', header: langs('Насморк'),
					renderer: function (rowNum,col,rec) {
						var val = rec.get('RepositoryObserv_IsRunnyNose');
						return !val ? '' : (val==2 ? 'Да' : 'Нет');
					}
				},
				{ name: 'RepositoryObserv_IsSoreThroat', header: langs('Боль в горле'),
					renderer: function (rowNum,col,rec) {
						var val = rec.get('RepositoryObserv_IsSoreThroat');
						return !val ? '' : (val==2 ? 'Да' : 'Нет');
					}
				},
				{ name: 'RepositoryObserv_Systolic', header: langs('САД') },
				{ name: 'RepositoryObserv_Diastolic', header: langs('ДАД') },
				{ name: 'RepositoryObserv_BreathFrequency', header: langs('ЧДД, в мин') },
				{ name: 'RepositoryObserv_Pulse', header: langs('ЧСС, в мин') },
				{ name: 'RepositoryObserv_SpO2', type:'float', header: langs('SpO2,%') },
				{ name: 'RepositoryObserv_GLU', type: 'float', header: langs('Уровень сахара крови')},
				{ name: 'RepositoryObserv_Cho', type: 'float', header: langs('Общий холестерин')},
				{ name: 'RepositoryObserv_Other', header: langs('Другие симптомы'), id: 'autoexpand' }
			],
			onRowSelect: function() {
				var viewframe = this;
				var setDT = viewframe.getGrid().getSelectionModel().getSelected().get('RepositoryObserv_setDT');
				var isEditDisabled = false;
				viewframe.getGrid().getStore().each( function (rec) {
					if(rec.get('RepositoryObserv_setDT') > setDT) isEditDisabled = true;
				});
				viewframe.ViewActions.action_edit.setDisabled(isEditDisabled || win.action == 'view');
			},
			getMoreParamsForEdit: function(mode) {
				var form = win.formPanel.getForm();
				var rec = this.ViewGridPanel.getSelectionModel().getSelected();

				return {
					Person_id: form.findField('Person_id').getValue(),
					PersonQuarantine_id: form.findField('PersonQuarantine_id').getValue(),
					MedStaffFact_id: form.findField('MedStaffFact_id').getValue(),
					RepositoryObserv_id: rec.get('RepositoryObserv_id'),
					useCase: 'quarantine',
				};
			},
			function_action_add: function() {
				var viewframe = this,
					form = win.formPanel.getForm();

				var params = {
					Person_id: form.findField('Person_id').getValue(),
					MedStaffFact_id: form.findField('MedStaffFact_id').getValue(),
					action: 'add',
					useCase: 'quarantine',
					callback: function () {
						win.RepositoryObservGrid.loadData();
					}
				};

				params.PersonQuarantine_id = viewframe.getParam('PersonQuarantine_id');

				var openFormFn = function (p) {
					getWnd(viewframe.editformclassname).show(p);
				};

				if(!params.PersonQuarantine_id) {
					sw.swMsg.confirm(
						langs('Сообщение'),
						langs('Для продолжения необходимо сохранить. Сохранить?'),
						function(btn) {
							if ( btn != 'yes' ) return;
							win.formPanel.saveConfirmMsg = false;
							win.formPanel.saveConfirmCondition = false;
							win.formPanel.saveForm({
								onSave: function (data) {
									params.PersonQuarantine_id = data.PersonQuarantine_id;
									win.RepositoryObservGrid.setParam('PersonQuarantine_id', data.PersonQuarantine_id)
									openFormFn(params);
								}
							});
						}
					);
				} else {
					openFormFn(params);
				}
			},
			checkBeforeLoadData: function () {
				return !Ext.isEmpty( this.getParam('PersonQuarantine_id') );
			}
		});

		win.PersonInfoFrame = new sw.Promed.PersonInfoPanel({
			plugins: [ Ext.ux.PanelCollapsedTitle ],
			titleCollapse: true,
			readonly: true,
			floatable: false,
			collapsible: true,
			collapsed: true,
			border: true,
			region: 'north'
		});

		win.formPanel = new sw.Promed.FormPanel({
			saveUrl: '/?c=PersonQuarantine&m=doSave',
			labelWidth: 250,
			width: '100%',
			autoWidth: false,
			bodyStyle: 'padding: 5px;background:#DFE8F6;',
			region: 'center',
			items: [
				{
					xtype: 'fieldset',
					title: langs('Информация о контрольной карте'),
					autoHeight: true,
					width: 600,
					items: [
						{
							name: 'RepositoryObserv_id',
							xtype: 'hidden'
						},
						{
							name: 'Person_id',
							xtype: 'hidden'
						},
						{
							name: 'PersonQuarantine_id',
							xtype: 'hidden'
						},
						{
							name: 'MedStaffFact_id',
							xtype: 'hidden'
						},
						{
							name: 'PersonQuarantine_begDT',
							xtype: 'swdatefield',
							fieldLabel: langs('Дата создания контрольной карты'),
							maxValue: today,
							allowBlank: false,
							width: 100,
							listeners: {
								change: function() {
									win.updateFields();
								}
							}
						},
						{
							hiddenName: 'PersonQuarantineOpenReason_id',
							xtype: 'swcommonsprcombo',
							comboSubject: 'PersonQuarantineOpenReason',
							fieldLabel: langs('Причина открытия контрольной карты'),
							allowBlank: false,
							width: 300,
							listeners: {
								change: function () {
									win.updateFields();
									win.updateDaysOfQuarantine();
								}
							}
						},
						{
							name: 'RepositoryObserv_arrivalDate',
							xtype: 'swdatefield',
							fieldLabel: langs('Дата прибытия'),
							maxValue: today,
							allowBlank: false,
							width: 100,
							listeners: {
								change: function () {
									win.updateDaysOfQuarantine();
								}
							}
						},
						{
							hiddenName: 'PlaceArrival_id',
							xtype: 'swcommonsprcombo',
							comboSubject: 'PlaceArrival',
							fieldLabel: langs('Место прибытия'),
							allowBlank: false,
							width: 300,
							listeners: {
								change: function () {
									win.updateFields();
								}
							}
						},
						{
							hiddenName: 'KLCountry_id',
							xtype: 'swklcountrycombo',
							fieldLabel: langs('Страна прибытия'),
							allowBlank: false,
							width: 300,
							listWidth: 300,
						},
						{
							hiddenName: 'KLRgn_id',
							xtype: 'swcommonsprcombo',
							comboSubject: 'KLRgnRF',
							fieldLabel: langs('Регион прибытия'),
							ctxSerach: true,
							editable: true,
							allowBlank: false,
							width: 300,
							listWidth: 300
						},
						{
							name: 'RepositoryObserv_FlightNumber',
							xtype: 'textfield',
							fieldLabel: langs('Рейс'),
							width: 300
						},
						{
							hiddenName: 'TransportMeans_id',
							xtype: 'swcommonsprcombo',
							comboSubject: 'TransportMeans',
							fieldLabel: langs('Средство передвижения при въезде в РФ'),
							prefix: 'nsi_',
							allowBlank: false,
							width: 300
						},
						{
							name: 'RepositoryObserv_TransportDesc',
							xtype: 'textarea',
							fieldLabel: langs('Средство передвижения при въезде в РФ (детально)'),
							width: 300
						},
						{
							name: 'RepositoryObserv_TransportPlace',
							xtype: 'textarea',
							fieldLabel: langs('Место въезда на территорию РФ'),
							allowBlank: false,
							width: 300
						},
						{
							name: 'RepositoryObserv_TransportRoute',
							xtype: 'textarea',
							fieldLabel: langs('Маршрут передвижения по РФ'),
							width: 300
						},
						{
							name: 'RepositoryObesrv_contactDate', // ошибка в бд в названии поля
							xtype: 'swdatefield',
							fieldLabel: langs('Дата контакта'),
							maxValue: today,
							allowBlank: false,
							width: 100,
							listeners: {
								change: function () {
									win.updateDaysOfQuarantine();
								}
							}
						},
						{
							name: 'PersonQuarantine_approveDT',
							xtype: 'swdatefield',
							fieldLabel: langs('Дата выявления заболевания'),
							maxValue: today,
							width: 100
						},
						{
							name: 'PersonQuarantine_endDT',
							xtype: 'swdatefield',
							fieldLabel: langs('Дата закрытия контрольной карты'),
							maxValue: today,
							width: 100,
							listWidth: 300,
							listeners: {
								change: function () {
									win.updateFields();
									win.updateDaysOfQuarantine();
								}
							}
						},
						{
							hiddenName: 'PersonQuarantineCloseReason_id',
							xtype: 'swcommonsprcombo',
							comboSubject: 'PersonQuarantineCloseReason',
							width: 300,
							allowBlank: false,
							fieldLabel: langs('Причина закрытия контрольной карты'),
						},
						{
							name: 'DayOfQuarantine',
							xtype: 'textfield',
							fieldLabel: langs('Дней на карантине'),
							width: 100,
							disabled: true
						}
					]},
				win.RepositoryObservGrid
			],
			afterSave: function (data) {
				win.formPanel.getForm().findField('PersonQuarantine_id').setValue(data['PersonQuarantine_id']);
				win.RepositoryObservGrid.setParam('PersonQuarantine_id', data['PersonQuarantine_id']);
			},
			afterLoad: function (data) {
				if(data['PersonQuarantine_endDT'] || win.action == 'view') {
					win.setWinAction('view');
				} else {
					win.setWinAction(data['PersonQuarantine_id'] ? 'edit' : 'add');
				}
				win.updateDaysOfQuarantine();

				if( data['PersonQuarantine_id'] ) {
					win.RepositoryObservGrid.setParam('PersonQuarantine_id', data['PersonQuarantine_id']);
					win.RepositoryObservGrid.loadData();
				};
			}
		});

		var topPanel = new Ext.Panel({
			layout: 'border',
			region: 'north',
			height: 200,
			items: [
				win.PersonInfoFrame,
				win.formPanel,
			]
		});

		win.items = [
			win.PersonInfoFrame,
			win.formPanel
		];

		win.PersonInfoFrame.addListener('collapse', function (panel) {
			topPanel.setHeight(360);
			win.doLayout();
		});
		win.PersonInfoFrame.addListener('beforeexpand', function (panel) {
			topPanel.setHeight(510);
			win.doLayout();
		});

		sw.Promed.swPersonQuarantineEditWindow.superclass.initComponent.apply(this, arguments);
	},
	getFirstDateOfQuarantine: function() {
		var win = this,
			form = win.formPanel.getForm(),
			openReason = form.findField('PersonQuarantineOpenReason_id').getValue();
		var begDate = false;
		switch(parseInt(openReason)) {
			case 1:
				begDate = form.findField('RepositoryObserv_arrivalDate').getValue();
				break;
			case 2:
				begDate = form.findField('RepositoryObesrv_contactDate').getValue();
				break;
			case 3:
				begDate = form.findField('PersonQuarantine_approveDT').getValue();
				break;
		}
		return begDate;
	},
	getLastDateOfQuarantine: function() {
		return this.formPanel.getForm().findField('PersonQuarantine_endDT').getValue() || Date.parseDate(getGlobalOptions().date, 'd.m.Y');
	},
	getQuarantineDays: function(endDate) {
		var begDate = this.getFirstDateOfQuarantine();
		if(!begDate) return;
		var time = endDate-begDate;
		var days = Math.ceil(time / (1000 * 60 * 60 * 24));
		return !isNaN(days) ? days + 1 : '';
	},
	updateDaysOfQuarantine: function() {
		var win = this,
			form = win.formPanel.getForm(),
			quarantineDaysField = form.findField('DayOfQuarantine'),
			days = win.getQuarantineDays( win.getLastDateOfQuarantine() );

		quarantineDaysField.setValue( days );
	}
});