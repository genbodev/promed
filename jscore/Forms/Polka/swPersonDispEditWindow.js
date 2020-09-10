/**
 * swPersonDispEditWindow - окно редактирования/добавления диспансерной карты пациента.
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package	  Polka
 * @access	   public
 * @copyright	Copyright (c) 2009 Swan Ltd.
 * @author	   Pshenitcyn Ivan aka IvP (ipshon@rambler.ru)
 * @version	  05.06.2009
 * @comment	  Префикс для id компонентов PDEF (PersonDispEditForm)
 *			   tabIndex: 2600
 *
 *
 * @input data: action - действие (add, edit, view)
 *			  PersonDisp_id - ID карты для редактирования или просмотра
 *			  Person_id - ID человека
 *			  Server_id - ?
 *
 *
 */
sw.Promed.swPersonDispEditWindow = Ext.extend(sw.Promed.BaseForm, {
	action: null,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	openBirthSpecChildDeathEditWindow:function (action) {
		var params = new Object();
		var grid = this.findById('PDEF_DeadChildGrid').getGrid();
		params.action = action;


		if (!grid.getSelectionModel().getSelected()) {
			return false;
		}
		var selected_record = grid.getSelectionModel().getSelected();
		params.formParams = selected_record.data;
		params.onHide = function () {
			grid.getView().focusRow(grid.getStore().indexOf(selected_record));
			grid.getSelectionModel().selectFirstRow();
		};

		getWnd('swBirthSpecStacChildDeathEditWindow').show(params);
	},
	print030: function(personDisp){
		if (!personDisp){
			sw.swMsg.alert(lang['oshibka'], 'Не передан идентификатор для печати');
			return;
		}
		printBirt({
			'Report_FileName': 'f030_4u.rptdesign',
			'Report_Params': '&paramPersonDisp=' + personDisp,
			'Report_Format': 'pdf'
		});
	},
	enablePrint030: function(){
		var base_form = this.FormPanel.getForm();
		var show_print030 = false;
		var diag = base_form.findField('Diag_id').getValue();
		if(diag && base_form.findField('Diag_id').getStore().getById(diag)){
			var diag_code = base_form.findField('Diag_id').getStore().getById(diag).get('Diag_Code');
			if(diag_code && diag_code.substr(0,3) >= 'A15' && diag_code.substr(0,3) <= 'A19'){
				show_print030 = true;
			}
		}
		if (show_print030) {
        	this.buttons[4].enable();
        } else {
        	this.buttons[4].disable();
        }
	},
	deletePersonDispMedicament: function() {
		if (this.action == 'view') {
			return false;
		}
		var grid = this.findById('PDEF_PersonDispMedicamentGrid').getGrid();
		var row = grid.getSelectionModel().getSelected();
		if (!row) {
			return false;
		}
		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function (buttonId) {
				if (buttonId == 'yes') {
					if (row.get('PersonDispMedicament_id') <= 0) {
						grid.getStore().remove(row);
						if (grid.getStore().getCount() == 0) {
							LoadEmptyRow(grid);
						}
						grid.getView().focusRow(0);
						grid.getSelectionModel().selectFirstRow();
					} else {
						Ext.Ajax.request({
							callback: function() {
								grid.getStore().remove(row);
								if (grid.getStore().getCount() == 0) {
									LoadEmptyRow(grid);
								}
								grid.getView().focusRow(0);
								grid.getSelectionModel().selectFirstRow();
							},
							params: {
								PersonDispMedicament_id: row.get('PersonDispMedicament_id')
							},
							url: '/?c=PersonDisp&m=deletePersonDispMedicament'
						});
					}
				}
			},
			msg: 'Вы действительно желаете удалить эту запись?',
			title: 'Подтверждение удаления'
		});
	},
	deletePersonPrivilege: function() {
		var grid = this.findById('PDEF_PersonPrivilegeGrid').getGrid();
		if (!grid || !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('PersonPrivilege_id')) {
			return false;
		}
		var selected_record = grid.getSelectionModel().getSelected();
		var lpu_id = selected_record.get('Lpu_id');
		var person_privilege_id = selected_record.get('PersonPrivilege_id');
		// Вообще проверка по ID - Узкое место, по Code - нельзя потому что в других регионах может быть
		// по другому.
		// В данном случае надо тянуть в грид ReceptFinance_id и по нему проверять
		if ((Number(selected_record.get('PrivilegeType_Code')) <= 249 || lpu_id != Ext.globalOptions.globals.lpu_id) && !isSuperAdmin() /* суперадминистратор может удалить любую льготу */) {
			return false;
		}
		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId, text, obj) {
				if (buttonId == 'yes') {
					var loadMask = new Ext.LoadMask(grid.getEl(), { msg: "Подождите, идет удаление..." });
					loadMask.show();
					Ext.Ajax.request({
						callback: function(options, success/*, response*/) {
							loadMask.hide();
							if (success) {
								grid.getStore().remove(selected_record);
								if (grid.getStore().getCount() == 0) {
									LoadEmptyRow(grid);
								}
								grid.getView().focusRow(0);
								grid.getSelectionModel().selectFirstRow();
							} else {
								sw.swMsg.alert('Ошибка', 'При удалении льготы возникли ошибки');
							}
						},
						params: {
							PersonPrivilege_id: person_privilege_id
						},
						url: C_PERS_PRIV_DEL
					});
				}
			},
			icon: Ext.MessageBox.QUESTION,
			msg: 'Удалить льготу?',
			title: 'Вопрос'
		});
	},
	disablePersonDispHistActions: function(disable){
		if(this.action == 'view' && !isUserGroup('PersonDispHistEdit')){
			disable = true;
		}
		this.findById('PDEF_PersonDispHist').setActionDisabled('action_add',disable);
		this.findById('PDEF_PersonDispHist').setActionDisabled('action_edit',disable);
		this.findById('PDEF_PersonDispHist').setActionDisabled('action_delete',disable);
		this.findById('PDEF_PersonDispHist').setActionDisabled('action_refresh',disable);
	},
	doSave: function(options) {
		if (!options) {
			options = {};
		}
		// options @Object
		// options.print @Boolean Вызывать печать карты дисп. учета, если true
		if (this.formStatus == 'save' || this.action == 'view') {
			return false;
		}
		this.formStatus = 'save';
		var win = this;
		var base_form = this.FormPanel.getForm();
		var form = this.FormPanel;

		if (base_form.findField('PersonDisp_begDate').getValue() != '' && new Date() < base_form.findField('PersonDisp_begDate').getValue()) {
			sw.swMsg.alert('Ошибка', 'Дата приема на учет должна быть не позже текущей даты.', function() {
				this.formStatus = 'edit';
				base_form.findField('PersonDisp_begDate').focus(true);
			}.createDelegate(this));
			return false;
		}

		if (base_form.findField('PersonDisp_DiagDate').getValue() != '' && new Date() < base_form.findField('PersonDisp_DiagDate').getValue()) {
			sw.swMsg.alert('Ошибка', 'Дата установления диагноза должна быть не позже текущей даты.', function() {
				this.formStatus = 'edit';
				base_form.findField('PersonDisp_DiagDate').focus(true);
			}.createDelegate(this));
			return false;
		}
		if (base_form.findField('PersonDisp_endDate').getValue() != '' && new Date() < base_form.findField('PersonDisp_endDate').getValue()) {
			sw.swMsg.alert('Ошибка', 'Дата снятия должна быть не позже текущей даты.', function() {
				this.formStatus = 'edit';
				base_form.findField('PersonDisp_endDate').focus(true);
			}.createDelegate(this));
			return false;
		}
		if(base_form.findField('PersonDisp_endDate').getValue() != '' && base_form.findField('PersonDisp_endDate').getValue() < base_form.findField('PersonDisp_begDate').getValue()) {
			sw.swMsg.alert('Ошибка', 'Дата снятия должна быть не раньше даты приема.', function() {
				this.formStatus = 'edit';
				base_form.findField('PersonDisp_endDate').focus(true);
			}.createDelegate(this));
			return false;
		}

		if (!base_form.isValid()) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					this.formStatus = 'edit';
					form.getFirstInvalidEl().focus(false);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		if (!Ext.isEmpty(base_form.findField('PersonDisp_endDate').getValue())) {
			var endDate = base_form.findField('PersonDisp_endDate').getValue();
			var histBegDate = false;
			var histEndDate = false;
			this.findById('PDEF_PersonDispHist').getGrid().getStore().each(function(rec){
				if(rec.get('PersonDispHist_id') > 0 && (!histBegDate || histBegDate < rec.get('PersonDispHist_begDate'))){
					histBegDate = rec.get('PersonDispHist_begDate');
					histEndDate = rec.get('PersonDispHist_endDate');
				}
			});
			if((!Ext.isEmpty(histEndDate) && histEndDate !== false) && Ext.util.Format.date(histEndDate, 'd.m.Y') != Ext.util.Format.date(endDate, 'd.m.Y')){
				sw.swMsg.alert('Ошибка', 'Дата снятия с диспансерного наблюдения должна соответствовать дате окончания периода ответственности последнего ответственного врача.', function() {
					this.formStatus = 'edit';
					base_form.findField('PersonDisp_endDate').setValue('');
					base_form.findField('PersonDisp_endDate').fireEvent('change',base_form.findField('PersonDisp_endDate'),null);
					base_form.findField('PersonDisp_endDate').focus(true);
				}.createDelegate(this));
				return false;
			}
		} else if (getRegionNick().inlist(['perm', 'vologda']) && !options.ignoreVizitControl && !options.doNotHide) {//в тз #139101 только для Перми
			var endDate = base_form.findField('PersonDisp_endDate').getValue();
			var checkVizitControl = false;
			var curDate = Date.parseDate(getGlobalOptions().date, 'd.m.Y');
			this.findById('PDEF_PersonDispVizitGrid').getGrid().getStore().each(function(rec){
				if (rec.get('PersonDispVizit_NextDate') > curDate && !rec.get('PersonDispVizit_NextFactDate')) {
					checkVizitControl = true;
				}
			});

			if (!checkVizitControl) {
				this.formStatus = 'edit';
				if (getRegionNick().inlist(['kareliya'])) {
					sw.swMsg.show({
						buttons: Ext.Msg.OK,
						icon: Ext.MessageBox.WARNING,
						msg: langs('Поле "Назначено явиться" в разделе "Контроль посещений" не заполнено. Назначьте пациенту следующее посещение или снимите пациента с диспансерного наблюдения.'),
						title: langs('Внимание')
					});
				} else {
					sw.swMsg.show({
						buttons: Ext.Msg.YESNO,
						fn: function(buttonId, text, obj) {
							if (buttonId == 'yes') {
								options.ignoreVizitControl = true;
								win.doSave(options);
							}
						}.createDelegate(this),
						icon: Ext.MessageBox.QUESTION,
						msg: langs('Поле "Назначено явиться" в разделе "Контроль посещений" не заполнено. Продолжить сохранение?'),
						title: langs('Вопрос')
					});
				}
				return false;
			}
		}

		if (!Ext.isEmpty(base_form.findField('PregnancySpec_Count').getValue()) && !Ext.isEmpty(base_form.findField('PregnancySpec_CountBirth').getValue()) && base_form.findField('PregnancySpec_Count').getValue() < base_form.findField('PregnancySpec_CountBirth').getValue()) {
			sw.swMsg.alert('Ошибка', 'Количество родов не может быть больше количества беременностей', function() {
				this.formStatus = 'edit';
				base_form.findField('PregnancySpec_CountBirth').focus(true);
			}.createDelegate(this));
			return false;
		}

		if (!Ext.isEmpty(base_form.findField('PregnancySpec_Count').getValue()) && !Ext.isEmpty(base_form.findField('PregnancySpec_CountAbort').getValue()) && base_form.findField('PregnancySpec_Count').getValue() < base_form.findField('PregnancySpec_CountAbort').getValue()) {
			sw.swMsg.alert('Ошибка', 'Количество абортов не может быть больше количества беременностей', function() {
				this.formStatus = 'edit';
				base_form.findField('PregnancySpec_CountAbort').focus(true);
			}.createDelegate(this));
			return false;
		}

		if (base_form.findField('PersonDisp_endDate').getValue() != '' && base_form.findField('PersonDisp_endDate').getValue() < base_form.findField('PersonDisp_begDate').getValue()) {
			sw.swMsg.alert('Ошибка', 'Дата приема на учет должна быть не позже даты снятия с учета.', function() {
				this.formStatus = 'edit';
				base_form.findField('PersonDisp_begDate').focus(true);
			}.createDelegate(this));
			return false;
		}

		if (!options.ignoreAvailabilityCardCauseDeath && this.action == 'add') {
			if(!Ext.isEmpty(this.availabilityCardCauseDeath) && this.availabilityCardCauseDeath>0){
				sw.swMsg.show({
					buttons: Ext.Msg.YESNO,
					fn: function(buttonId, text, obj) {
						if (buttonId == 'yes') {
							options.ignoreAvailabilityCardCauseDeath = true;
							this.doSave(options);
						} else {
							return false;
						}
					}.createDelegate(this),
					icon: Ext.MessageBox.QUESTION,
					msg: 'У пациента имеется закрытая карта диспансерного наблюдения по причине смерти. Продолжить сохранение?',
					title: 'Вопрос'
				});
				this.formStatus = 'edit';
				return false;
			}
		}

		var med_personal_code;
		var med_personal_fio = '';
		var med_personal_id;
		var med_staff_fact_id = base_form.findField('MedStaffFact_id').getValue();
		var record;
		record = base_form.findField('MedStaffFact_id').getStore().getById(med_staff_fact_id);
		if (record) {
			med_personal_code = record.get('MedPersonal_TabCode');
			med_personal_fio = record.get('MedPersonal_Fio');
			med_personal_id = record.get('MedPersonal_id');
			base_form.findField('MedPersonal_id').setValue(med_personal_id);
		}
		var idx;
		var medicaments_array = new Array();
		var medicaments_grid = this.findById('PDEF_PersonDispMedicamentGrid').getGrid();
		var params = new Object();
		var privilege_type_id;
		var sickness_id = base_form.findField('Sickness_id').getValue();
		medicaments_grid.getStore().each(function(record) {
			if (!record.get('Drug_id')) {
				medicaments_grid.getStore().remove(record);
			}
		});
		if (this.action == 'add' && medicaments_grid.getStore().getCount() > 0 && medicaments_grid.getStore().getAt(0).get('Drug_id') > 0) {
			medicaments_array = getStoreRecords(medicaments_grid.getStore(), { convertDateFields: true, exceptionFields: [ 'PersonDispMedicament_id', 'PersonDisp_id', 'Drug_Name', 'Drug_Price' ]});
		}
		idx = base_form.findField('Sickness_id').getStore().findBy(function(rec) {
			return rec.get('Sickness_id') == sickness_id;
		});
		if (idx >= 0) {
			privilege_type_id = base_form.findField('Sickness_id').getStore().getAt(idx).get('PrivilegeType_id');
		}
		params.action = this.action;
		//params.medicaments = Ext.util.JSON.encode(medicaments_array);
		params.PrivilegeType_id = privilege_type_id;
		params.Sickness_id = sickness_id;
		if (params.Sickness_id == 100500){
			params.Sickness_id = null;
		}
		if (base_form.findField('LpuSection_id').disabled) {
			params.LpuSection_id = base_form.findField('LpuSection_id').getValue();
		}
		if (base_form.findField('Diag_id').disabled) {
			params.Diag_id = base_form.findField('Diag_id').getValue();
		}
		if (base_form.findField('PersonDisp_NumCard').disabled) {
			params.PersonDisp_NumCard = base_form.findField('PersonDisp_NumCard').getValue();
		}
		if (options && options.ignoreExistsPersonDisp) {
			params.ignoreExistsPersonDisp = 1;
		}
		if (this.PersonPregnancy_id) {
			params.PersonPregnancy_id = this.PersonPregnancy_id;
		}

		if (this.isERDB) params.HumanUID = this.HumanUID;
/*
		// Гриды специфики
		params.MorbusHepatitisDiag = this.collectGridData('MorbusHepatitisDiag');
		params.MorbusHepatitisDiagSop = this.collectGridData('MorbusHepatitisDiagSop');
		params.MorbusHepatitisLabConfirm = this.collectGridData('MorbusHepatitisLabConfirm');
		params.MorbusHepatitisFuncConfirm = this.collectGridData('MorbusHepatitisFuncConfirm');
		params.MorbusHepatitisCure = this.collectGridData('MorbusHepatitisCure');
		params.MorbusHepatitisCureEffMonitoring = this.collectGridData('MorbusHepatitisCureEffMonitoring');
		params.MorbusHepatitisVaccination = this.collectGridData('MorbusHepatitisVaccination');
		params.MorbusHepatitisQueue = this.collectGridData('MorbusHepatitisQueue');
*/
		var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Подождите, идет сохранение..." });
		loadMask.show();
		base_form.submit({
			failure: function(result_form, action) {
				this.formStatus = 'edit';
				loadMask.hide();
				if (action.result) {
					if (action.result.Error_Msg) {
						sw.swMsg.alert('Ошибка', action.result.Error_Msg, function() {
							base_form.findField('PersonDisp_NumCard').focus(true);
						});
					} else {
						sw.swMsg.alert('Ошибка', 'При сохранении произошли ошибки [Тип ошибки: 1]', function() {
							base_form.findField('PersonDisp_NumCard').focus(true);
						});
					}
				}
			}.createDelegate(this),
			params: params,
			success: function(result_form, action) {
				this.formStatus = 'edit';
				loadMask.hide();
				if (action.result) {
					if (action.result.existsPersonDisp) {
						var diag_full_name = action.result.existsPersonDisp.Diag_FullName;
						var msg = Ext.isEmpty(action.result.existsPersonDisp.PersonDisp_endDate)
								? 'У пациента уже есть действующая карта с диагнозом "'+diag_full_name+'". Продолжить сохранение карты?'
								: 'У пациента уже есть закрытая карта с диагнозом "'+diag_full_name+'", действующая на "'+action.result.existsPersonDisp.PersonDisp_endDate+'". Продолжить сохранение?';
						sw.swMsg.show({
							buttons: Ext.Msg.OKCANCEL,
							fn:function (buttonId, text, obj) {
								if (buttonId == 'ok') {
									options = options || {};
									options.ignoreExistsPersonDisp = true;
									this.doSave(options);
								}
							}.createDelegate(this),
							icon:Ext.MessageBox.QUESTION,
							msg: msg ,
							title:'Подтверждение'
						});
					} else if (action.result.PersonDisp_id) {
						var data = new Object();
						var diag_code;
						var lpu_section_code;
						var person_disp_id = action.result.PersonDisp_id;
						base_form.findField('PersonDisp_id').setValue(person_disp_id);
						record = base_form.findField('Diag_id').getStore().getById(base_form.findField('Diag_id').getValue());
						if (record) {
							diag_code = record.get('Diag_Code');
						}
						record = base_form.findField('LpuSection_id').getStore().getById(base_form.findField('LpuSection_id').getValue());
						if (record) {
							lpu_section_code = record.get('LpuSection_Code');
						}
						data.PersonDispData = {
							'accessType': 'edit',
							'PersonDisp_id': person_disp_id,
							'Person_id': base_form.findField('Person_id').getValue(),
							'Server_id': base_form.findField('Server_id').getValue(),
							'Diag_Code': diag_code,
							'LpuSection_Code': lpu_section_code,
							'MedPersonal_Code': med_personal_code,
							'MedPersonal_Fio': med_personal_fio,
							'Person_Surname': this.PersonInfo.getFieldValue('Person_Surname'),
							'Person_Firname': this.PersonInfo.getFieldValue('Person_Firname'),
							'Person_Secname': this.PersonInfo.getFieldValue('Person_Secname'),
							'Person_Birthday': this.PersonInfo.getFieldValue('Person_Birthday'),
							'PersonDisp_begDate': base_form.findField('PersonDisp_begDate').getValue(),
							'PersonDisp_endDate': base_form.findField('PersonDisp_endDate').getValue()
						};
						this.callback(data);
						//сохраняю специфику по беременности
						//log('this.specifics.panel.visible', this.specifics.panel.hidden);
						var afterSave = function (){
							 if (options && options.printPersonDispCard){
	                            var paramPersonDisp = person_disp_id;
								printBirt({
									'Report_FileName': 'PersonDispCard.rptdesign',
									'Report_Params': '&paramPersonDisp=' + paramPersonDisp,
									'Report_Format': 'pdf'
								});
	                        }
	                        if (options && options.print030Card){
	                            var paramPersonDisp = person_disp_id;
								this.print030(paramPersonDisp);
	                        }
	                        if (options && options.withSign){
								getWnd('swEMDSignWindow').show({
									EMDRegistry_ObjectName: 'PersonDisp',
									EMDRegistry_ObjectID: person_disp_id,
									callback: function(data) {
										// ok
									}
								});
	                        } else if (getGlobalOptions().hasEMDCertificate && getRegionNick() != 'kz' && !options.printParturientCard && !options.print030Card && !options.printPersonDispCard && !options.doNotHide) {
								sw.swMsg.show({
									title: langs('Вопрос'),
									msg: langs('Данные карты были изменены. Подписать карту электронной подписью?'),
									icon: Ext.MessageBox.QUESTION,
									buttons: {yes: "Да, подписать", no: "Нет, подписать позже"},
									fn: function ( buttonId ) {
										if ( buttonId == 'yes' ) {
											getWnd('swEMDSignWindow').show({
												EMDRegistry_ObjectName: 'PersonDisp',
												EMDRegistry_ObjectID: person_disp_id,
												callback: function(data) {
													// ok
												}
											});
										}
									}
								});
							}
							if (options && options.printParturientCard) {
								//this.buttons[1].focus();
								//window.open('/?c=PersonDisp&m=printPersonDisp&PersonDisp_id=' + person_disp_id, '_blank');
								var paramPersonDispBirth = person_disp_id;
								printBirt({
									'Report_FileName': 'han_ParturientCard.rptdesign',
									'Report_Params': '&paramPersonDispBirth=' + paramPersonDispBirth,
									'Report_Format': 'pdf'
								});
							} else {
								if ((options && !options.doNotHide) || !options){
									this.hide();
								}
								if (options && options.callback){
									options.callback();
								}
							}
						}.createDelegate(this);
						var lastHist = 0;
						if (
							!Ext.isEmpty(base_form.findField('PersonDisp_endDate').getValue())
							|| !Ext.isEmpty(this.PersonDispCloseDate) // Дата закрытия карты сейчас пуста, но не была пуста при открытии формы - значит восстановление наблюдения
						) {
							var endDate = base_form.findField('PersonDisp_endDate').getValue();
							var histBegDate = false;
							var histEndDate = false;
							var histId = 0;
							this.findById('PDEF_PersonDispHist').getGrid().getStore().each(function(rec){
								if(!histBegDate || histBegDate < rec.get('PersonDispHist_begDate')){
									histBegDate = rec.get('PersonDispHist_begDate');
									histEndDate = rec.get('PersonDispHist_endDate');
									histId = rec.get('PersonDispHist_id');
								}
							});
							if(histEndDate != endDate){
								lastHist = histId;
							}
						}
						if (
							(
								this.findById('PDEF_PersonDispHist').getGrid().getStore().data.length == 1
								&& this.findById('PDEF_PersonDispHist').getGrid().getStore().getAt(0).get('PersonDispHist_id') == 0
								&& !this.PersonDispHistSaved
							)
							||
							(lastHist>0)
						) {
							if(!(lastHist>0)){
								var paramsHist = {
									PersonDispHist_id:null,
									PersonDisp_id:person_disp_id,
									MedPersonal_id:med_personal_id,
									LpuSection_id:base_form.findField('LpuSection_id').getValue(),
									MedStaffFact_id:base_form.findField('MedStaffFact_id').getValue(),
									PersonDispHist_begDate:Ext.util.Format.date(base_form.findField('PersonDisp_begDate').getValue(),'Y-m-d'),
									PersonDispHist_endDate:Ext.util.Format.date(base_form.findField('PersonDisp_endDate').getValue(),'Y-m-d')
								};
							} else {
								var recordHist = this.findById('PDEF_PersonDispHist').getGrid().getStore().getById(lastHist);
								var paramsHist = {
									PersonDispHist_id:recordHist.get('PersonDispHist_id'),
									PersonDisp_id:person_disp_id,
									MedPersonal_id:recordHist.get('MedPersonal_id'),
									LpuSection_id:recordHist.get('LpuSection_id'),
									PersonDispHist_begDate:Ext.util.Format.date(recordHist.get('PersonDispHist_begDate'),'Y-m-d'),
									PersonDispHist_endDate:Ext.util.Format.date(base_form.findField('PersonDisp_endDate').getValue(),'Y-m-d')
								};
							}

							Ext.Ajax.request({
								url: '/?c=PersonDisp&m=savePersonDispHist',
								params: paramsHist,
								callback: function(options, success, response) {
									if ( success ) {
										var result = Ext.util.JSON.decode(response.responseText);
										this.PersonDispHistSaved = true;
									}
									if (!this.specifics.panel.hidden) {
										if (this.findById('PersonDispEditForm').getForm().findField('PregnancySpec_id')) {
											this.specifics.save(afterSave);
										} else {
											afterSave();
										}
									} else if (!this.MorbusNephro.panel.hidden) {
			                            this.MorbusNephro.save(afterSave);
			                        } else {
										afterSave();
									}
								}.createDelegate(this)
							});
						} else {
							if (!this.specifics.panel.hidden) {
								if (this.findById('PersonDispEditForm').getForm().findField('PregnancySpec_id')) {
									this.specifics.save(afterSave);
								} else {
									afterSave();
								}
							} else if (!this.MorbusNephro.panel.hidden) {
	                            this.MorbusNephro.save(afterSave);
	                        } else {
								afterSave();
							}
						}
					} else {
						if (action.result.Error_Msg) {
							sw.swMsg.alert('Ошибка', action.result.Error_Msg);
						} else {
							sw.swMsg.alert('Ошибка', 'При сохранении произошли ошибки [Тип ошибки: 3]');
						}
					}
				} else {
					sw.swMsg.alert('Ошибка', 'При сохранении произошли ошибки [Тип ошибки: 2]');
				}
			}.createDelegate(this)
		});
	},
	draggable: true,
	enableEdit: function(enable) {
		if (this.findById('PDEF_MorbusNephroLab')) {
			this.findById('PDEF_MorbusNephroLab').setReadOnly(!enable);
		}
		if (this.findById('PDEF_PregnancySpecComplication')) {
			this.findById('PDEF_PregnancySpecComplication').setReadOnly(!enable);
		}
		if (this.findById('PDEF_PregnancySpecExtragenitalDisease')) {
			this.findById('PDEF_PregnancySpecExtragenitalDisease').setReadOnly(!enable);
		}
		if (this.findById('PDEF_ChildGrid')) {
			this.findById('PDEF_ChildGrid').setReadOnly(!enable);
		}
		if (this.findById('PDEF_DeadChildGrid')) {
			this.findById('PDEF_DeadChildGrid').setReadOnly(!enable);
		}
		if (this.findById('PDEF_PersonPrivilegeGrid')) {
			this.findById('PDEF_PersonPrivilegeGrid').setReadOnly(!enable);
		}
		if (this.findById('PDEF_PersonDispMedicamentGrid')) {
			this.findById('PDEF_PersonDispMedicamentGrid').setReadOnly(!enable);
		}

		var base_form = this.FormPanel.getForm();
		var form_fields = new Array('Diag_id', 'LpuSection_id', 'MedStaffFact_id', 'PersonDisp_NumCard', 'PersonDisp_begDate', 'PersonDisp_endDate', 'DispOutType_id');
		var i = 0;
		for (i = 0; i < form_fields.length; i++) {
			if (enable) {
				base_form.findField(form_fields[i]).enable();
			} else {
				base_form.findField(form_fields[i]).disable();
			}
		}

		if (this.action != 'add') {
			base_form.findField('PersonDisp_NumCard').disable();
		}

		//Если открываем на просмотр или редактирование беременность и роды, то открываем кнопку печати формы 111/у
		/*if ((this.action!='add') && (this.FormPanel.getForm().findField('Sickness_id').getValue() == 9)){
			this.buttons[3].show();
		}
		//Иначе - закрываем
		else{
			this.buttons[3].hide();
		}
		this.buttons[3].show();*/

		//Если беременность и роды, то открываем кнопку печати формы 111/у
		if(this.FormPanel.getForm().findField('Sickness_id').getValue() == 9){
			this.buttons[2].show();
		}
		//Иначе - закрываем
		else{
			this.buttons[2].hide();
		}
		if (enable) {
			this.buttons[0].show();
			if (getGlobalOptions().hasEMDCertificate) {
				this.buttons[1].show();
			} else {
				this.buttons[1].hide();
			}
		} else {
			this.buttons[0].hide();
			this.buttons[1].hide();
		}
	},
	getPersonDispNumber: function() {
		if ( this.action != 'add' ) {
			return false;
		}
		var that = this;
		var persondisp_num_field = this.findById('PersonDispEditForm').getForm().findField('PersonDisp_NumCard');

		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Получение номера талона..."});
		loadMask.show();

		Ext.Ajax.request({
			callback: function(options, success, response) {
				loadMask.hide();

				if ( success ) {
					var response_obj = Ext.util.JSON.decode(response.responseText);
					persondisp_num_field.setValue(response_obj.PersonDisp_NumCard);
					persondisp_num_field.focus(true);
				}
				else {
					sw.swMsg.alert(langs('Ошибка'), langs('Ошибка при определении номера талона'));
				}
			},
			url: '/?c=PersonDisp&m=getPersonDispNumber'
		});
	},
	height: 550,
	id: 'PersonDispEditWindow',
	initComponent: function() {
		this.sicknessDiagStore = new Ext.db.AdapterStore({
			autoLoad: false,
			dbFile: 'Promed.db',
			fields: [
				{ name: 'SicknessDiag_id', type: 'int' },
				{ name: 'Sickness_id', type: 'int' },
				{ name: 'Sickness_Code', type: 'int' },
				{ name: 'PrivilegeType_id', type: 'int' },
				{ name: 'Sickness_Name', type: 'string' },
				{ name: 'Diag_id', type: 'int' },
				{ name: 'SicknessDiag_begDT', type: 'date', dateFormat: 'd.m.Y' },
				{ name: 'SicknessDiag_endDT', type: 'date', dateFormat: 'd.m.Y' }
			],
			key: 'SicknessDiag_id',
			sortInfo: {
				field: 'Diag_id'
			},
			tableName: 'SicknessDiag'
		});
		var parentWindow = this;
		var deleteEvent = function() {
			if (this.action == 'view') {
				return false;
			}
			var error = 'При удалении лабораторного обследования возникли ошибки';
			var grid = parentWindow.findById('PDEF_EvnUslugaPregnancySpecGrid');
			var question = 'Удалить лабораторное обследование?';
			var url = '/?c=EvnUsluga&m=deleteEvnUsluga';
			var params = new Object();
			if (!grid || !grid.getSelectionModel().getSelected()) {
				return false;
			} else if (!grid.getSelectionModel().getSelected().get('EvnUslugaPregnancySpec_id')) {
				return false;
			}
			var selected_record = grid.getSelectionModel().getSelected();
			params['class'] = 'EvnUslugaPregnancySpec';
			params['id'] = selected_record.get('EvnUslugaPregnancySpec_id');
			sw.swMsg.show({
				buttons: Ext.Msg.YESNO,
				fn: function(buttonId, text, obj) {
					if (buttonId == 'yes') {
						//var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Удаление записи..." });
						//loadMask.show();
						Ext.Ajax.request({
							failure: function(response, options) {
								loadMask.hide();
								sw.swMsg.alert('Ошибка', error);
							},
							params: params,
							success: function(response, options) {
								//loadMask.hide();
								var response_obj = Ext.util.JSON.decode(response.responseText);
								if (response_obj.success == false) {
									sw.swMsg.alert('Ошибка', response_obj.Error_Msg ? response_obj.Error_Msg : error);
								} else {
									grid.getStore().remove(selected_record);
									if (grid.getStore().getCount() == 0) {
										grid.getTopToolbar().items.items[1].disable();
										grid.getTopToolbar().items.items[2].disable();
										grid.getTopToolbar().items.items[3].disable();
										LoadEmptyRow(grid);
									}
								}
								grid.getView().focusRow(0);
								grid.getSelectionModel().selectFirstRow();
							},
							url: url
						});
					} else {
						grid.getView().focusRow(0);
						grid.getSelectionModel().selectFirstRow();
					}
				}.createDelegate(this),
				icon: Ext.MessageBox.QUESTION,
				msg: question,
				title: 'Вопрос'
			});
		}
		var openEvnUslugaEditWindow = function(action) {
			if (action != 'add' && action != 'edit' && action != 'view') {
				return false;
			}
			var grid = parentWindow.findById('PDEF_EvnUslugaPregnancySpecGrid');
			if (parentWindow.action == 'view') {
				if (action == 'add') {
					return false;
				} else if (action == 'edit') {
					action = 'view';
				}
			}
			var params = new Object();
			params.action = action;
			params.callback = function(data) {
				if (!data || !data.evnUslugaData) {
					grid.getStore().load({
						params: {
							PregnancySpec_id: this.findById('PersonDispEditForm').getForm().findField('PregnancySpec_id').getValue()
						}
					});
					return false;
				}
				var record = grid.getStore().getById(data.evnUslugaData.EvnUsluga_id);
				data.evnUslugaData.EUPS_setDate = data.evnUslugaData['EvnUsluga_setDate'];
				data.evnUslugaData.lpu_name = data.evnUslugaData['lpu_name'];
				data.evnUslugaData.EvnUslugaPregnancySpec_id = data.evnUslugaData['EvnUsluga_id'];
				if (!record) {
					if (grid.getStore().getCount() == 1 && !grid.getStore().getAt(0).get('EvnUslugaPregnancySpec_id')) {
						grid.getStore().removeAll();
					}
					grid.getStore().loadData([ data.evnUslugaData ], true);
				} else {
					var evn_usluga_fields = new Array();
					var i = 0;
					grid.getStore().fields.eachKey(function(key, item) {
						evn_usluga_fields.push(key);
					});
					for (i = 0; i < evn_usluga_fields.length; i++) {
						record.set(evn_usluga_fields[i], data.evnUslugaData[evn_usluga_fields[i]]);
					}
					//устанавливаю значения полей, которые не установились по каким-либо причинам в цикле
					record.commit();
				}
			}.createDelegate(parentWindow);
			params.onHide = function() {
				if (grid.getSelectionModel().getSelected()) {
					grid.getView().focusRow(grid.getStore().indexOf(grid.getSelectionModel().getSelected()));
				} else {
					grid.getView().focusRow(0);
					grid.getSelectionModel().selectFirstRow();
				}
			}.createDelegate(parentWindow);
			params.parentClass = 'PersonDisp';//'EvnPS';
			var personalInfoPanel = parentWindow.findById('PDEF_PersonInformationFrame');
			params.Person_id = personalInfoPanel.getFieldValue('Person_id');
			params.Person_Birthday = personalInfoPanel.getFieldValue('Person_Birthday');
			params.Person_Firname = personalInfoPanel.getFieldValue('Person_Firname');
			params.Person_Secname = personalInfoPanel.getFieldValue('Person_Secname');
			params.Person_Surname = personalInfoPanel.getFieldValue('Person_Surname');
			var parent_evn_combo_data = new Array();
			params.formParams = {
				Person_id: personalInfoPanel.getFieldValue('Person_id'),
				PersonEvn_id: personalInfoPanel.getFieldValue('PersonEvn_id'),
				Server_id: personalInfoPanel.getFieldValue('Server_id')
			}
			params.doSave = function(options) {
				//log('->doSave');
				// options @Object
				// options.openChildWindow @Function Открыть доченрее окно после сохранения
				var EvnUslugaCommonEditWindow = Ext.getCmp('EvnUslugaEditWindow');
				if (EvnUslugaCommonEditWindow.formStatus == 'save' || EvnUslugaCommonEditWindow.action == 'view') {
					return false;
				}
				EvnUslugaCommonEditWindow.formStatus = 'save';
				var base_form = EvnUslugaCommonEditWindow.findById('EvnUslugaEditForm').getForm();
				if (!base_form.isValid()) {
					sw.swMsg.show({
						buttons: Ext.Msg.OK,
						fn: function() {
							EvnUslugaCommonEditWindow.formStatus = 'edit';
							base_form.findField('EvnUslugaCommon_setDate').focus(true, 100);
						}.createDelegate(EvnUslugaCommonEditWindow),
						icon: Ext.Msg.WARNING,
						msg: ERR_INVFIELDS_MSG,
						title: ERR_INVFIELDS_TIT
					});
					return false;
				}
				var evn_usluga_set_time = base_form.findField('EvnUslugaCommon_setTime').getValue();
				var evn_usluga_common_pid = base_form.findField('EvnUslugaCommon_pid').getValue();
				if ((EvnUslugaCommonEditWindow.parentClass == 'EvnVizit' || EvnUslugaCommonEditWindow.parentClass == 'EvnPS') && !evn_usluga_common_pid) {
					sw.swMsg.show({
						buttons: Ext.Msg.OK,
						fn: function() {
							EvnUslugaCommonEditWindow.formStatus = 'edit';
							base_form.findField('EvnUslugaCommon_pid').focus(true);
						}.createDelegate(EvnUslugaCommonEditWindow),
						icon: Ext.Msg.WARNING,
						msg: 'Не выбрано отделение (посещение)',
						title: ERR_INVFIELDS_TIT
					});
					return false;
				}
				var med_personal_id = null;
				var med_staff_fact_id = base_form.findField('MedStaffFact_id').getValue();
				var params = new Object();
				var record = null;
				var usluga_code = '';
				var usluga_id = 0;
				var usluga_name = '';
				var usluga_place_code = 0;
				// MedPersonal_id
				index = base_form.findField('MedStaffFact_id').getStore().findBy(function(rec) {
					return (rec.get('MedStaffFact_id') == med_staff_fact_id);
				});

				if ( index >= 0 ) {
					base_form.findField('MedPersonal_id').setValue(base_form.findField('MedStaffFact_id').getStore().getAt(index).get('MedPersonal_id'));
					med_personal_id = base_form.findField('MedStaffFact_id').getStore().getAt(index).get('MedPersonal_id');
				}
				record = base_form.findField('UslugaPlace_id').getStore().getById(base_form.findField('UslugaPlace_id').getValue());
				var lpu_name = '';
				if (record) {
					usluga_place_code = Number(record.get('UslugaPlace_Code'));
					record = base_form.findField('UslugaComplex_id').getStore().getById(base_form.findField('UslugaComplex_id').getValue());
					if (record) {
						usluga_code = record.get('UslugaComplex_Code');
						usluga_id = record.get('UslugaComplex_id');
						usluga_name = record.get('UslugaComplex_Name');
					}

					switch (usluga_place_code) {
						case 1:
							//1 Отделение ЛПУ
							lpu_name = Ext.globalOptions.globals.lpu_nick;
							break;
						case 2:
							//2 Другое ЛПУ
							lpu_name = base_form.findField('Lpu_uid').getRawValue();
							break;
						case 3:
							//3 Другая организация
							lpu_name = base_form.findField('Org_uid').getRawValue();
							break;
					}
				}
				if (!usluga_id) {
					sw.swMsg.show({
						buttons: Ext.Msg.OK,
						fn: function() {
							EvnUslugaCommonEditWindow.formStatus = 'edit';
							EvnUslugaCommonEditWindow.buttons[0].focus();
						}.createDelegate(EvnUslugaCommonEditWindow),
						icon: Ext.Msg.WARNING,
						msg: 'Не выбрана оказываемая услуга',
						title: ERR_INVFIELDS_TIT
					});
					return false;
				}
				base_form.findField('UslugaComplex_id').setValue(usluga_id);
				var loadMask = new Ext.LoadMask(EvnUslugaCommonEditWindow.getEl(), { msg: LOAD_WAIT_SAVE });
				loadMask.show();
				params.EvnUslugaPregnancySpec_id = base_form.findField('EvnUslugaCommon_id').getValue();
				params.EvnUslugaPregnancySpec_rid = 0;
				params.PregnancySpec_id = parentWindow.specifics.formFields.PregnancySpec_id.getValue();
				base_form.findField('MedPersonal_id').setValue(med_personal_id)
				params.MedPersonal_id = med_personal_id;
				params.LpuSection_uid = base_form.findField('LpuSection_uid').getValue();
				if (base_form.findField('EvnUslugaCommon_pid').disabled) {
					params.EvnUslugaCommon_pid = evn_usluga_common_pid;
				}
				//var anamnez_data = Ext.util.JSON.encode(Ext.getCmp('EUComEF_TemplPanel').getSavingData());
				//var xml_template_id = Ext.getCmp('EUComEF_TemplPanel').getXmlTemplate_id();
				//params.AnamnezData = anamnez_data;
				//params.XmlTemplate_id = xml_template_id;
				base_form.submit({
					failure: function(result_form, action) {
						EvnUslugaCommonEditWindow.formStatus = 'edit';
						loadMask.hide();
						if (action.result) {
							if (action.result.Error_Msg) {
								sw.swMsg.alert('Ошибка', action.result.Error_Msg);
							} else {
								sw.swMsg.alert('Ошибка', 'При сохранении произошли ошибки [Тип ошибки: 3]');
							}
						}
					}.createDelegate(EvnUslugaCommonEditWindow),
					params: params,
					success: function(result_form, action) {
						EvnUslugaCommonEditWindow.formStatus = 'edit';
						loadMask.hide();
						if (action.result && action.result.EvnUslugaCommon_id > 0) {
							base_form.findField('EvnUslugaCommon_id').setValue(action.result.EvnUslugaCommon_id);
							if (options && typeof options.openChildWindow == 'function' && EvnUslugaCommonEditWindow.action == 'add') {
								options.openChildWindow();
							} else {
								var data = new Object();
								var set_time = base_form.findField('EvnUslugaCommon_setTime').getValue();
								if (!set_time || set_time.length == 0) {
									set_time = '00:00';
								}
								data.evnUslugaData = {
									'accessType': 'edit',
									'EvnClass_SysNick': 'EvnUslugaCommon',
									'EvnUsluga_Kolvo': base_form.findField('EvnUslugaCommon_Kolvo').getValue(),
									'EvnUsluga_id': base_form.findField('EvnUslugaCommon_id').getValue(),
									'EvnUsluga_setDate': base_form.findField('EvnUslugaCommon_setDate').getValue(),
									'EvnUsluga_setTime': set_time,
									'Usluga_Code': usluga_code,
									'Usluga_Name': usluga_name,
									'lpu_name': lpu_name
								};
								EvnUslugaCommonEditWindow.callback(data);
								EvnUslugaCommonEditWindow.hide();
							}
						} else {
							sw.swMsg.alert('Ошибка', 'При сохранении произошли ошибки [Тип ошибки: 2]');
						}
					}.createDelegate(getWnd('swEvnUslugaEditWindow'))
				});
				//log('<-doSave');
			};
			params.formUrl = '/?c=PregnancySpec&m=saveEvnUslugaPregnancySpec';
			params.formLoadUrl = '/?c=PregnancySpec&m=loadEvnUslugaPregnancySpecForm';
			switch (action) {
				case 'add':
					if (parentWindow.findById('PersonDispEditForm').getForm().findField('PersonDisp_id').getValue() != '0') {
						if (!parentWindow.specifics.formFields.PregnancySpec_id.getValue()) {
							parentWindow.specifics.save(function (){getWnd('swEvnUslugaEditWindow').show(params);});
						} else {
							getWnd('swEvnUslugaEditWindow').show(params);
						}
					} else {
						//log('starting saving followed by adding');
						parentWindow.doSave({
							doNotHide:true,
							callback: function(){
								getWnd('swEvnUslugaEditWindow').show(params);
							}
						});
					}
					break;
				case 'edit':
				case 'view':
					var selected_record = grid.getSelectionModel().getSelected();
					if (!selected_record || !selected_record.get('EvnUslugaPregnancySpec_id')) {
						return false;
					}
					var evn_usluga_id = selected_record.get('EvnUslugaPregnancySpec_id');
					params.formParams.EvnUslugaCommon_id = evn_usluga_id;
					params.parentEvnComboData = parent_evn_combo_data;
					if (!parentWindow.specifics.formFields.PregnancySpec_id.getValue()) {
						parentWindow.specifics.save(function (){getWnd('swEvnUslugaEditWindow').show(params);});
					} else {
						getWnd('swEvnUslugaEditWindow').show(params);
					}
			}
		}
		var labGrid = new Ext.grid.GridPanel({
			autoExpandColumn: 'autoexpand_usluga',
			autoExpandMin: 100,
			border: false,
			columns: [
				{
					dataIndex: 'EUPS_setDate',
					header: 'Дата',
					hidden: false,
					renderer: Ext.util.Format.dateRenderer('d.m.Y'),
					resizable: false,
					sortable: true,
					width: 100
				},
				{
					dataIndex: 'Usluga_Code',
					header: 'Код',
					hidden: false,
					resizable: false,
					sortable: true,
					width: 100
				},
				{
					dataIndex: 'Usluga_Name',
					header: 'Наименование',
					hidden: false,
					id: 'autoexpand_usluga',
					resizable: true,
					sortable: true,
					width: 100
				},
				{
					dataIndex: 'lpu_name',
					header: 'Место выполнения',
					hidden: false,
					resizable: true,
					sortable: true,
					width: 100
				},
				{
					dataIndex: 'pregPeriod',
					header: 'Срок беременности',
					hidden: false,
					resizable: true,
					sortable: true,
					width: 100
				}
			],
			frame: false,
			id: 'PDEF_EvnUslugaPregnancySpecGrid',
			keys: [
				{
					key: [
						Ext.EventObject.DELETE,
						Ext.EventObject.END,
						Ext.EventObject.ENTER,
						Ext.EventObject.F3,
						Ext.EventObject.F4,
						Ext.EventObject.HOME,
						Ext.EventObject.INSERT,
						Ext.EventObject.PAGE_DOWN,
						Ext.EventObject.PAGE_UP,
						Ext.EventObject.TAB
					],
					fn: function(inp, e) {
						e.stopEvent();
						if (e.browserEvent.stopPropagation)
							e.browserEvent.stopPropagation(); else
							e.browserEvent.cancelBubble = true;
						if (e.browserEvent.preventDefault)
							e.browserEvent.preventDefault(); else
							e.browserEvent.returnValue = false;
						e.returnValue = false;
						if (Ext.isIE) {
							e.browserEvent.keyCode = 0;
							e.browserEvent.which = 0;
						}
						var grid = this.findById('PDEF_EvnUslugaPregnancySpecGrid');
						switch (e.getKey()) {
							case Ext.EventObject.DELETE:
								deleteEvent();
								break;
							case Ext.EventObject.END:
								GridEnd(grid);
								break;
							case Ext.EventObject.ENTER:
							case Ext.EventObject.F3:
							case Ext.EventObject.F4:
							case Ext.EventObject.INSERT:
								if (!grid.getSelectionModel().getSelected()) {
									return false;
								}
								var action = 'add';
								if (e.getKey() == Ext.EventObject.F3) {
									action = 'view';
								} else if (e.getKey() == Ext.EventObject.F4 || e.getKey() == Ext.EventObject.ENTER) {
									action = 'edit';
								}
								openEvnUslugaEditWindow(action);
								break;
							case Ext.EventObject.HOME:
								GridHome(grid);
								break;
							case Ext.EventObject.PAGE_DOWN:
								GridPageDown(grid);
								break;
							case Ext.EventObject.PAGE_UP:
								GridPageUp(grid);
								break;
							case Ext.EventObject.TAB:
								var base_form = this.findById('PersonDispEditForm').getForm();
								grid.getSelectionModel().clearSelections();
								grid.getSelectionModel().fireEvent('rowselect', grid.getSelectionModel());
								if (!e.shiftKey) {
									//переходим на следующий контрол - грид 'Осложнение беременности'
									var gr = parentWindow.findById('PDEF_PregnancySpecComplication').getGrid();
									if (gr.getStore().getCount() > 0) {
										gr.getView().focusRow(0);
										gr.getSelectionModel().selectFirstRow();
									}

								} else {
									base_form.findField('PregnancySpec_IsHIV').focus();
								}
								break;
						}
					},
					scope: this,
					stopEvent: true
				}
			],
			listeners: {
				'rowdblclick': function(grid, number, obj) {
					openEvnUslugaEditWindow('edit');
				}.createDelegate(this)
			},
			loadMask: true,
			region: 'center',
			sm: new Ext.grid.RowSelectionModel({
				listeners: {
					'rowselect': function(sm) {
						var readOnly = ((parentWindow.action != 'edit') && (parentWindow.action != 'add'));
						var evn_usluga_id = null;
						var selected_record = sm.getSelected();
						var toolbar = labGrid.getTopToolbar();
						if (selected_record) {
							evn_usluga_id = selected_record.get('EvnUslugaPregnancySpec_id');
						}
						if (evn_usluga_id) {
							if (readOnly) {
								toolbar.items.items[1].disable();//Изменить
								toolbar.items.items[2].enable();//Просмотр
								toolbar.items.items[3].disable();//Удалить
							} else {
								toolbar.items.items[1].enable();
								toolbar.items.items[2].enable();
								toolbar.items.items[3].enable();
							}
						} else {
							toolbar.items.items[1].disable();
							toolbar.items.items[2].disable();
							toolbar.items.items[3].disable();
						}
					}
				}
			}),
			stripeRows: true,
			store: new Ext.data.Store({
				autoLoad: false,
				baseParams: {
					'parent': 'EvnPS'
				},
				listeners: {
					'load': function(store, records, index) {
						if (store.getCount() == 0) {
							LoadEmptyRow(this.findById('PDEF_EvnUslugaPregnancySpecGrid'));
						} else {
							calcPregPeriod();
						}
						//this.findById('PDEF_EvnUslugaPregnancySpecGrid').getView().focusRow(0);
						this.findById('PDEF_EvnUslugaPregnancySpecGrid').getSelectionModel().selectFirstRow();
					}.createDelegate(this)
				},
				reader: new Ext.data.JsonReader({
					id: 'EvnUslugaPregnancySpec_id'
				}, [
					{
						mapping: 'EvnUslugaPregnancySpec_id',
						name: 'EvnUslugaPregnancySpec_id',
						type: 'int'
					},
					{
						mapping: 'EvnClass_SysNick',
						name: 'EvnClass_SysNick',
						type: 'string'
					},
					{
						mapping: 'EvnUsluga_setTime',
						name: 'EvnUsluga_setTime',
						type: 'string'
					},
					{
						dateFormat: 'd.m.Y',
						mapping: 'EUPS_setDate',
						name: 'EUPS_setDate',
						type: 'date'
					},
					{
						mapping: 'Usluga_Code',
						name: 'Usluga_Code',
						type: 'string'
					},
					{
						mapping: 'Usluga_Name',
						name: 'Usluga_Name',
						type: 'string'
					},
					{
						mapping: 'lpu_name',
						name: 'lpu_name',
						type: 'string'
					},
					{
						mapping: 'EUPS_Kolvo',
						name: 'EUPS_Kolvo',
						type: 'float'
					}
				]),
				url: '/?c=PregnancySpec&m=loadEvnUslugaPregnancySpecGrid'
			}),
			tbar: new sw.Promed.Toolbar({
				buttons: [
					{
						handler: function() {
							openEvnUslugaEditWindow('add');
						}.createDelegate(this),
						iconCls: 'add16',
						text: 'Добавить'
					},
					{
						handler: function() {
							openEvnUslugaEditWindow('edit');
						}.createDelegate(this),
						iconCls: 'edit16',
						text: 'Изменить'
					},
					{
						handler: function() {
							openEvnUslugaEditWindow('view');//todo: проверить
						}.createDelegate(this),
						iconCls: 'view16',
						text: 'Просмотр'
					},
					{
						handler: function() {
							deleteEvent();
						}.createDelegate(this),
						iconCls: 'delete16',
						text: 'Удалить'
					}
				]
			})
		});
		var deleteGridSelectedRecord = function(gridId, idField) {
			var grid = parentWindow.findById(gridId).getGrid();
			var record = grid.getSelectionModel().getSelected();
			sw.swMsg.show({
				buttons: Ext.Msg.YESNO,
				fn: function(buttonId, text, obj) {
					if (buttonId == 'yes') {
						if (!grid || !grid.getSelectionModel() || !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get(idField)) {
							return false;
						}
						switch (Number(record.get('RecordStatus_Code'))) {
							case 0:
								grid.getStore().remove(record);
								break;
							case 1:
							case 2:
								record.set('RecordStatus_Code', 3);
								record.commit();
								grid.getStore().filterBy(function(rec) {
									if (Number(rec.get('RecordStatus_Code')) == 3) {
										return false;
									} else {
										return true;
									}
								});
								break;
						}
					}
					if (grid.getStore().getCount() > 0) {
						grid.getView().focusRow(0);
						grid.getSelectionModel().selectFirstRow();
					}
				}.createDelegate(this),
				icon: Ext.MessageBox.QUESTION,
				msg: 'Вы действительно хотите удалить эту запись?',
				title: 'Вопрос'
			});
		}
		var openPregnancySpecExtragenitalDiseaseEditWindow = function (action) {
			var params = new Object();
			var grid = parentWindow.findById('PDEF_PregnancySpecExtragenitalDisease').getGrid();
			params.formParams = {};
			params.gridRecords = getStoreRecords(grid.getStore(), {
				convertDateFields: true,
				exceptionFields: [
					 'Diag_Name'
				]
			});
			params.callback = function(data) {
				if (!data || !data.ExtragenitalDiseaseData) {
					return false;
				}
				data.ExtragenitalDiseaseData.RecordStatus_Code = 0;
				// Обновить запись в grid
				var record = grid.getStore().getById(data.ExtragenitalDiseaseData.PSED_id);
				if (record) {
					if (record.get('RecordStatus_Code') == 1) {
						data.ExtragenitalDiseaseData.RecordStatus_Code = 2;
					}
					var grid_fields = new Array();
					grid.getStore().fields.eachKey(function(key, item) {
						grid_fields.push(key);
					});
					for (i = 0; i < grid_fields.length; i++) {
						record.set(grid_fields[i], data.ExtragenitalDiseaseData[grid_fields[i]]);
					}
					record.commit();
				} else {
					if (grid.getStore().getCount() == 1 && !grid.getStore().getAt(0).get('PSED_id')) {
						grid.getStore().removeAll();
					}
					data.ExtragenitalDiseaseData.PSED_id = -swGenTempId(grid.getStore());
					grid.getStore().loadData([ data.ExtragenitalDiseaseData ], true);
				}
			}
			params.action = action;
			if (action.inlist(['view','edit'])) {
				if (!grid.getSelectionModel().getSelected()) {
					return false;
				}
				var selected_record = grid.getSelectionModel().getSelected();
				params.formParams = selected_record.data;
				params.onHide = function() {
					grid.getView().focusRow(grid.getStore().indexOf(selected_record));
				};
			} else {
				params.onHide = function() {
					grid.getView().focusRow(0);
				};
			}
			getWnd('swPregnancySpecExtragenitalDiseaseEditWindow').show(params);
		}
		var openPregnancySpecComplicationEditWindow = function (action) {
			var params = new Object();
			var grid = parentWindow.findById('PDEF_PregnancySpecComplication').getGrid();
			params.formParams = {};
			params.gridRecords = getStoreRecords(grid.getStore(), {
				convertDateFields: true,
				exceptionFields: [
					 'Diag_Name'
				]
			});
			params.callback = function(data) {
				if (!data || !data.ComplicationData) {
					return false;
				}
				data.ComplicationData.RecordStatus_Code = 0;
				// Обновить запись в grid
				var record = grid.getStore().getById(data.ComplicationData.PregnancySpecComplication_id);
				if (record) {
					if (record.get('RecordStatus_Code') == 1) {
						data.ComplicationData.RecordStatus_Code = 2;
					}
					var grid_fields = new Array();
					grid.getStore().fields.eachKey(function(key, item) {
						grid_fields.push(key);
					});
					for (i = 0; i < grid_fields.length; i++) {
						record.set(grid_fields[i], data.ComplicationData[grid_fields[i]]);
					}
					record.commit();
				} else {
					if (grid.getStore().getCount() == 1 && !grid.getStore().getAt(0).get('PregnancySpecComplication_id')) {
						grid.getStore().removeAll();
					}
					data.ComplicationData.PregnancySpecComplication_id = -swGenTempId(grid.getStore());
					grid.getStore().loadData([ data.ComplicationData ], true);
				}
			}
			params.action = action;
			if (action.inlist(['view','edit'])) {
				if (!grid.getSelectionModel().getSelected()) {
					return false;
				}
				var selected_record = grid.getSelectionModel().getSelected();
				params.formParams = selected_record.data;
				params.onHide = function() {
					grid.getView().focusRow(grid.getStore().indexOf(selected_record));
				}
			} else {
				params.onHide = function() {
					grid.getView().focusRow(0);
				};
			}
			getWnd('swPregnancySpecComplicationEditWindow').show(params);
		}
		var fillFromAnotherPersonDisp = function () {
			var form = parentWindow.findById('PersonDispEditForm').getForm();
			var field = Ext.getCmp('PersonDispEditForm').getForm().findField('LpuAnother_id');
			var pregnancySpec_id = field.getStore().getById(field.getValue()).data['PregnancySpec_id'];
			//todo: добавить предупреждение о невыбранной ДУ
			//todo: Добавить предупреждение об отсутствии информации о беременности в выбранной ДУ
			form.findField('Lpu_aid').setValue(pregnancySpec_id);
			loadPregnancySpecIntoForm(pregnancySpec_id);
		}
		var loadPregnancySpecIntoForm = function(pregnancySpec_id) {
			var form = parentWindow.findById('PersonDispEditForm').getForm();
			var params = new Object();
			params.PregnancySpec_id = pregnancySpec_id;
			var formFields = parentWindow.specifics.formFields;
			specificsPanel.findById('PDEF_ChildGrid').removeAll();
			specificsPanel.findById('PDEF_DeadChildGrid').removeAll();
			Ext.Ajax.request({
				method: 'post',
				callback: function(options, success, response) {
					var resp = Ext.util.JSON.decode(response.responseText);
					formFields.Lpu_aid.setValue(resp[0].Lpu_aid);
					formFields.PregnancySpec_Period.setValue(resp[0].PregnancySpec_Period);
					formFields.PregnancySpec_Count.setValue(resp[0].PregnancySpec_Count);
					formFields.PregnancySpec_CountBirth.setValue(resp[0].PregnancySpec_CountBirth);
					formFields.PregnancySpec_CountAbort.setValue(resp[0].PregnancySpec_CountAbort);
					formFields.PregnancySpec_BirthDT.setValue(resp[0].PregnancySpec_BirthDT);
					setTimeout(function (){formFields.BirthResult_id.setValue(resp[0].BirthResult_id)},200);
					formFields.PregnancySpec_OutcomPeriod.setValue(resp[0].PregnancySpec_OutcomPeriod);
					if(parentWindow.BirthSpecStac_OutcomDate){
						formFields.PregnancySpec_OutcomDT.setValue(parentWindow.BirthSpecStac_OutcomDate);
					}
					else{
						formFields.PregnancySpec_OutcomDT.setValue(resp[0].PregnancySpec_OutcomDT);
					}
					setTimeout(function (){formFields.BirthSpec_id.setValue(resp[0].BirthSpec_id)},200);
					formFields.PregnancySpec_IsHIVtest.setValue(resp[0].PregnancySpec_IsHIVtest);
					formFields.PregnancySpec_IsHIV.setValue(resp[0].PregnancySpec_IsHIV);
					if (resp[0].EvnSection_id) {
						formFields.EvnSection_id.setValue(resp[0].EvnSection_id);
						formFields.EvnSection_pid.setValue(resp[0].EvnSection_pid);
						//загружаю грид с детьми и мертворожденными
						specificsPanel.findById('PDEF_ChildGrid').loadData({
							globalFilters: {
								EvnSection_pid: formFields.EvnSection_pid.getValue(),
								Person_id:form.findField('Person_id').getValue()
							},
							noFocusOnLoad: true
						});
						specificsPanel.findById('PDEF_DeadChildGrid').loadData({
							globalFilters: {
								EvnSection_id: formFields.EvnSection_id.getValue()
							},
							noFocusOnLoad: true
						});
					}
					//загружаю лаб обследования
					labGrid.getStore().load({
						params: {
							PregnancySpec_id: pregnancySpec_id
						},
						noFocusOnLoad: true
					});
					//загружаю грид с осложнениями
					specificsPanel.findById('PDEF_PregnancySpecComplication').loadData({
						globalFilters: {
							PregnancySpec_id: pregnancySpec_id
						},
						noFocusOnLoad: true
					});
					specificsPanel.findById('PDEF_PregnancySpecExtragenitalDisease').loadData({
						globalFilters: {
							PregnancySpec_id: pregnancySpec_id
						},
						noFocusOnLoad: true
					});
					//todo: наполняю поля сохраненными значениями из базы
				},
				params: params,
				url: '/?c=PregnancySpec&m=load'
			});
		}
/*
		this.MorbusHepatitisSpec = new sw.Promed.Panel({
			autoHeight: true,
			style: 'margin-bottom: 0.5em;',
			bodyStyle:'background:#DFE8F6;padding:5px;',
			border: true,
			collapsible: true,
			region: 'north',
			layout: 'form',
			title: 'Специфика',
			items: [{
					name: 'HepatitisEpidemicMedHistoryType_id',
					comboSubject: 'HepatitisEpidemicMedHistoryType',
					fieldLabel: 'Эпиданамнез',
					typeCode: 'int',
					xtype: 'swcommonsprcombo',
					width: 450
				},
				new sw.Promed.ViewFrame({
					actions: [
						{name: 'action_add', handler: function() {
							this.openWindow('MorbusHepatitisDiag', 'add');
						}.createDelegate(this)},
						{name: 'action_edit', handler: function() {
							this.openWindow('MorbusHepatitisDiag', 'edit');
						}.createDelegate(this)},
						{name: 'action_view', handler: function() {
							this.openWindow('MorbusHepatitisDiag', 'view');
						}.createDelegate(this)},
						{name: 'action_delete', handler: function() {
							this.deleteGridSelectedRecord('PDEF_MorbusHepatitisDiag', 'MorbusHepatitisDiag_id');
						}.createDelegate(this)},
						{name: 'action_print'}
					],
					autoExpandColumn: 'autoexpand',
					autoExpandMin: 150,
					autoLoadData: false,
					border: false,
					dataUrl: '',
					id: 'PDEF_MorbusHepatitisDiag',
					paging: false,
					style: 'margin-bottom: 10px',
					stringfields: [
						{name: 'MorbusHepatitisDiag_id', type: 'int', header: 'ID', key: true},
						{name: 'RecordStatus_Code', type: 'int', hidden: true},
						{name: 'MorbusHepatitisDiag_setDT', type: 'date', dateFormat: 'd.m.Y', header: 'Дата', width: 120},
						{name: 'MedPersonal_id', type: 'string', hidden: true},
						{name: 'MedPersonal_Name', type: 'string', header: 'Врач', width: 320, id: 'autoexpand'},
						{name: 'HepatitisDiagType_id', type: 'string', hidden: true},
						{name: 'HepatitisDiagType_Name', type: 'string', header: 'Диагноз', width: 240},
						{name: 'HepatitisDiagActiveType_id', type: 'string', hidden: true},
						{name: 'HepatitisDiagActiveType_Name', type: 'string', header: 'Активность', width: 240},
						{name: 'HepatitisFibrosisType_id', type: 'string', hidden: true},
						{name: 'HepatitisFibrosisType_Name', type: 'string', header: 'Фиброз', width: 240}
					],
					title: 'Диагноз',
					toolbar: true
				}),
				new sw.Promed.ViewFrame({
					actions: [
						{name: 'action_add', handler: function() {
							this.openWindow('MorbusHepatitisDiagSop', 'add');
						}.createDelegate(this)},
						{name: 'action_edit', handler: function() {
							this.openWindow('MorbusHepatitisDiagSop', 'edit');
						}.createDelegate(this)},
						{name: 'action_view', handler: function() {
							this.openWindow('MorbusHepatitisDiagSop', 'view');
						}.createDelegate(this)},
						{name: 'action_delete', handler: function() {
							this.deleteGridSelectedRecord('PDEF_MorbusHepatitisDiagSop', 'MorbusHepatitisDiagSop_id');
						}.createDelegate(this)},
						{name: 'action_print'}
					],
					autoExpandColumn: 'autoexpand',
					autoExpandMin: 150,
					autoLoadData: false,
					border: false,
					dataUrl: '',
					id: 'PDEF_MorbusHepatitisDiagSop',
					paging: false,
					style: 'margin-bottom: 10px',
					stringfields: [
						{name: 'MorbusHepatitisDiagSop_id', type: 'int', header: 'ID', key: true},
						{name: 'RecordStatus_Code', type: 'int', hidden: true},
						{name: 'MorbusHepatitisDiagSop_setDT', type: 'date', dateFormat: 'd.m.Y', header: 'Дата', width: 120},
						{name: 'Diag_id', type: 'string', hidden: true},
						{name: 'Diag_Name', type: 'string', header: 'Диагноз', width: 420, id: 'autoexpand'}
					],
					title: 'Сопутствующие диагнозы',
					toolbar: true
				}),
				new sw.Promed.ViewFrame({
					actions: [
						{name: 'action_add', handler: function() {
							this.openWindow('MorbusHepatitisLabConfirm', 'add');
						}.createDelegate(this)},
						{name: 'action_edit', handler: function() {
							this.openWindow('MorbusHepatitisLabConfirm', 'edit');
						}.createDelegate(this)},
						{name: 'action_view', handler: function() {
							this.openWindow('MorbusHepatitisLabConfirm', 'view');
						}.createDelegate(this)},
						{name: 'action_delete', handler: function() {
							this.deleteGridSelectedRecord('PDEF_MorbusHepatitisLabConfirm', 'MorbusHepatitisLabConfirm_id');
						}.createDelegate(this)},
						{name: 'action_print'}
					],
					autoExpandColumn: 'autoexpand',
					autoExpandMin: 150,
					autoLoadData: false,
					border: false,
					dataUrl: '',
					id: 'PDEF_MorbusHepatitisLabConfirm',
					paging: false,
					style: 'margin-bottom: 10px',
					stringfields: [
						{name: 'MorbusHepatitisLabConfirm_id', type: 'int', header: 'ID', key: true},
						{name: 'RecordStatus_Code', type: 'int', hidden: true},
						{name: 'MorbusHepatitisLabConfirm_setDT', type: 'date', dateFormat: 'd.m.Y', header: 'Дата', width: 120},
						{name: 'HepatitisLabConfirmType_id', type: 'string', hidden: true},
						{name: 'HepatitisLabConfirmType_Name', type: 'string', header: 'Тип', width: 240},
						{name: 'MorbusHepatitisLabConfirm_Result', type: 'string', header: 'Результат', width: 240}
					],
					title: 'Лабораторные подтверждения',
					toolbar: true
				}),
				new sw.Promed.ViewFrame({
					actions: [
						{name: 'action_add', handler: function() {
							this.openWindow('MorbusHepatitisFuncConfirm', 'add');
						}.createDelegate(this)},
						{name: 'action_edit', handler: function() {
							this.openWindow('MorbusHepatitisFuncConfirm', 'edit');
						}.createDelegate(this)},
						{name: 'action_view', handler: function() {
							this.openWindow('MorbusHepatitisFuncConfirm', 'view');
						}.createDelegate(this)},
						{name: 'action_delete', handler: function() {
							this.deleteGridSelectedRecord('PDEF_MorbusHepatitisFuncConfirm', 'MorbusHepatitisFuncConfirm_id');
						}.createDelegate(this)},
						{name: 'action_print'}
					],
					autoExpandColumn: 'autoexpand',
					autoExpandMin: 150,
					autoLoadData: false,
					border: false,
					dataUrl: '',
					id: 'PDEF_MorbusHepatitisFuncConfirm',
					paging: false,
					style: 'margin-bottom: 10px',
					stringfields: [
						{name: 'MorbusHepatitisFuncConfirm_id', type: 'int', header: 'ID', key: true},
						{name: 'RecordStatus_Code', type: 'int', hidden: true},
						{name: 'MorbusHepatitisFuncConfirm_setDT', type: 'date', dateFormat: 'd.m.Y', header: 'Дата', width: 120},
						{name: 'HepatitisFuncConfirmType_id', type: 'string', hidden: true},
						{name: 'HepatitisFuncConfirmType_Name', type: 'string', header: 'Тип', width: 240},
						{name: 'MorbusHepatitisFuncConfirm_Result', type: 'string', header: 'Результат', width: 240}
					],
					title: 'Инструментальные подтверждения',
					toolbar: true
				}),
				new sw.Promed.ViewFrame({
					actions: [
						{name: 'action_add', handler: function() {
							this.openWindow('MorbusHepatitisCure', 'add');
						}.createDelegate(this)},
						{name: 'action_edit', handler: function() {
							this.openWindow('MorbusHepatitisCure', 'edit');
						}.createDelegate(this)},
						{name: 'action_view', handler: function() {
							this.openWindow('MorbusHepatitisCure', 'view');
						}.createDelegate(this)},
						{name: 'action_delete', handler: function() {
							this.deleteGridSelectedRecord('PDEF_MorbusHepatitisCure', 'MorbusHepatitisCure_id');
						}.createDelegate(this)},
						{name: 'action_print'}
					],
					autoExpandColumn: 'autoexpand',
					autoExpandMin: 150,
					autoLoadData: false,
					border: false,
					dataUrl: '',
					id: 'PDEF_MorbusHepatitisCure',
					paging: false,
					style: 'margin-bottom: 10px',
					stringfields: [
						{name: 'MorbusHepatitisCure_id', type: 'int', header: 'ID', key: true},
						{name: 'RecordStatus_Code', type: 'int', hidden: true},
						{name: 'MorbusHepatitisCure_Year', type: 'string', header: 'Год лечения', width: 120},
						{name: 'MorbusHepatitisCure_Drug', type: 'string', header: 'Препарат', width: 240},
						{name: 'HepatitisResultClass_id', type: 'int', hidden: true},
						{name: 'HepatitisResultClass_Name', type: 'string', header: 'Результат', width: 240},
						{name: 'HepatitisSideEffectType_id', type: 'int', hidden: true},
						{name: 'HepatitisSideEffectType_Name', type: 'string', header: 'Побочный эффект', width: 240}
					],
					title: 'Лечение',
					toolbar: true
				}),
				new sw.Promed.ViewFrame({
					actions: [
						{name: 'action_add', handler: function() {
							this.openWindow('MorbusHepatitisCureEffMonitoring', 'add');
						}.createDelegate(this)},
						{name: 'action_edit', handler: function() {
							this.openWindow('MorbusHepatitisCureEffMonitoring', 'edit');
						}.createDelegate(this)},
						{name: 'action_view', handler: function() {
							this.openWindow('MorbusHepatitisCureEffMonitoring', 'view');
						}.createDelegate(this)},
						{name: 'action_delete', handler: function() {
							this.deleteGridSelectedRecord('PDEF_MorbusHepatitisCureEffMonitoring', 'MorbusHepatitisCureEffMonitoring_id');
						}.createDelegate(this)},
						{name: 'action_print'}
					],
					autoExpandColumn: 'autoexpand',
					autoExpandMin: 150,
					autoLoadData: false,
					border: false,
					dataUrl: '',
					id: 'PDEF_MorbusHepatitisCureEffMonitoring',
					paging: false,
					style: 'margin-bottom: 10px',
					stringfields: [
						{name: 'MorbusHepatitisCureEffMonitoring_id', type: 'int', header: 'ID', key: true},
						{name: 'RecordStatus_Code', type: 'int', hidden: true},
						{name: 'HepatitisCurePeriodType_id', type: 'string', hidden: true},
						{name: 'HepatitisCurePeriodType_Name', type: 'string', header: 'Срок лечения', width: 320},
						{name: 'HepatitisQualAnalysisType_id', type: 'string', hidden: true},
						{name: 'HepatitisQualAnalysisType_Name', type: 'string', header: 'Качественный анализ', width: 320},
						{name: 'MorbusHepatitisCureEffMonitoring_VirusStress', type: 'string', header: 'Вирусная нагрузка', width: 120}
					],
					title: 'Мониторинг эффективности лечения',
					toolbar: true
				}),
				new sw.Promed.ViewFrame({
					actions: [
						{name: 'action_add', handler: function() {
							this.openWindow('MorbusHepatitisVaccination', 'add');
						}.createDelegate(this)},
						{name: 'action_edit', handler: function() {
							this.openWindow('MorbusHepatitisVaccination', 'edit');
						}.createDelegate(this)},
						{name: 'action_view', handler: function() {
							this.openWindow('MorbusHepatitisVaccination', 'view');
						}.createDelegate(this)},
						{name: 'action_delete', handler: function() {
							this.deleteGridSelectedRecord('PDEF_MorbusHepatitisVaccination', 'MorbusHepatitisVaccination_id');
						}.createDelegate(this)},
						{name: 'action_print'}
					],
					autoExpandColumn: 'autoexpand',
					autoExpandMin: 150,
					autoLoadData: false,
					border: false,
					dataUrl: '',
					id: 'PDEF_MorbusHepatitisVaccination',
					paging: false,
					style: 'margin-bottom: 10px',
					stringfields: [
						{name: 'MorbusHepatitisVaccination_id', type: 'int', header: 'ID', key: true},
						{name: 'RecordStatus_Code', type: 'int', hidden: true},
						{name: 'MorbusHepatitisVaccination_setDT', type: 'date', dateFormat: 'd.m.Y', header: 'Дата', width: 120},
						{name: 'MorbusHepatitisVaccination_Vaccine', type: 'string', header: 'Название вакцины', width: 320}
					],
					title: 'Вакцинация',
					toolbar: true
				}),
				new sw.Promed.ViewFrame({
					actions: [
						{name: 'action_add', handler: function() {
							this.openWindow('MorbusHepatitisQueue', 'add');
						}.createDelegate(this)},
						{name: 'action_edit', handler: function() {
							this.openWindow('MorbusHepatitisQueue', 'edit');
						}.createDelegate(this)},
						{name: 'action_view', handler: function() {
							this.openWindow('MorbusHepatitisQueue', 'view');
						}.createDelegate(this)},
						{name: 'action_delete', handler: function() {
							this.deleteGridSelectedRecord('PDEF_MorbusHepatitisQueue', 'MorbusHepatitisQueue_id');
						}.createDelegate(this)},
						{name: 'action_print'}
					],
					autoExpandColumn: 'autoexpand',
					autoExpandMin: 150,
					autoLoadData: false,
					border: false,
					dataUrl: '',
					id: 'PDEF_MorbusHepatitisQueue',
					paging: false,
					style: 'margin-bottom: 10px',
					stringfields: [
						{name: 'MorbusHepatitisQueue_id', type: 'int', header: 'ID', key: true},
						{name: 'RecordStatus_Code', type: 'int', hidden: true},
						{name: 'HepatitisQueueType_id', type: 'string', hidden: true},
						{name: 'HepatitisQueueType_Name', type: 'string', header: 'Тип очереди', width: 240},
						{name: 'MorbusHepatitisQueue_Num', type: 'string', header: 'Номер в очереди', width: 120},
						{name: 'MorbusHepatitisQueue_IsCure', type: 'string', hidden: true},
						{name: 'MorbusHepatitisQueue_IsCure_Name', type: 'string', header: 'Лечение проведено', width: 120}
					],
					title: 'Очередь',
					toolbar: true
				})]
		});
*/
        var me = this;
        this.MorbusNephro = {
            loaded: false,
            inited: false,
            onShowWindow: function(win) {
                this.hide();
                this.loaded = false;
                this.inited = false;

                this.personInfoPanel = win.findById('PDEF_PersonInformationFrame');
                var base_form = win.FormPanel.getForm();
                this.baseForm = base_form;
                this.fieldDiagId = base_form.findField('Diag_id');
                this.fieldPersonId = base_form.findField('Person_id');
                this.fieldMorbusNephroId = base_form.findField('MorbusNephro_id');
                this.fieldMorbusId = base_form.findField('Morbus_id');
                this.fieldFirstDate = base_form.findField('MorbusNephro_firstDate');
                this.comboNephroDiagConfType = base_form.findField('NephroDiagConfType_id');
                this.comboNephroCRIType = base_form.findField('NephroCRIType_id');
                this.comboIsHyperten = base_form.findField('MorbusNephro_IsHyperten');
                this.fieldPersonHeight = base_form.findField('PersonHeight_Height');
                this.fieldPersonWeight = base_form.findField('PersonWeight_Weight');
                this.fieldPersonHeightId = base_form.findField('PersonHeight_id');
                this.fieldPersonWeightId = base_form.findField('PersonWeight_id');
                this.fieldTreatment = base_form.findField('MorbusNephro_Treatment');
                if (!this.viewFrameMorbusNephroLab.getAction('action_setOnlyLast')) {
                    var sp = this;
                    this.viewFrameMorbusNephroLab.addActions({
                        name: 'action_setOnlyLast',
                        text: 'Отображать только последние',
                        handler: function()
                        {
                            var grid = sp.viewFrameMorbusNephroLab.ViewGridPanel;
                            var action = sp.viewFrameMorbusNephroLab.ViewActions.action_setOnlyLast;
                            if(grid.getStore().baseParams.isOnlyLast == 1) {
                                grid.getStore().baseParams.isOnlyLast = 0;
                                action.setText('Отображать только последние');
                            } else {
                                grid.getStore().baseParams.isOnlyLast = 1;
                                action.setText('Отображать все');
                            }
                            grid.getStore().load();
                        }
                    });
                }
                var actionSetOnlyLast = this.viewFrameMorbusNephroLab.getAction('action_setOnlyLast');
                actionSetOnlyLast.setDisabled(false);
                this.viewFrameMorbusNephroLab.ViewGridPanel.getStore().baseParams.isOnlyLast = 0;
                actionSetOnlyLast.setText('Отображать только последние');
                this.inited = true;
                this.accessType = 'view';
                this.viewFrameMorbusNephroLab.removeAll();
            },
            onChangeDiag: function(combo, newValue) {
                var sp = this;
                sp.fieldMorbusId.setValue(null);
                if (this.inited && getRegionNick().inlist([ 'perm', 'ufa' ])) {
                    Ext.Ajax.request({
                        callback: function(options, success, response) {
                            if (success){
                                var response_obj = Ext.util.JSON.decode(response.responseText);
                                if (response_obj.Morbus_id){
                                    sp.fieldMorbusId.setValue(response_obj.Morbus_id);
                                }
                                if (response_obj.MorbusNephro_id){
                                    sp.fieldMorbusNephroId.setValue(response_obj.MorbusNephro_id);
                                    sp.show();
                                    sp.btnAddEvnNotifyNephro.setDisabled(response_obj.EvnNotifyNephro_id || response_obj.PersonRegister_id);
                                } else {
                                    sp.hide();
                                    sp.fieldMorbusNephroId.setValue(null);
                                }
                            }
                        },
                        params: {
                            Diag_id: newValue,
                            Person_id: sp.fieldPersonId.getValue()
                        },
                        url: '/?c=MorbusNephro&m=checkByPersonDispForm'
                    });
                }
            },
            save: function (callback) {
                var sp = this;
                var dataToSave = {
                    Diag_id: this.fieldDiagId.getValue(),
                    Person_id: this.fieldPersonId.getValue(),
                    MorbusNephro_id: this.fieldMorbusNephroId.getValue() || null,
                    Morbus_id: this.fieldMorbusId.getValue() || null,
                    MorbusNephro_firstDate: this.fieldFirstDate.getRawValue() || null,
                    NephroDiagConfType_id: this.comboNephroDiagConfType.getValue() || null,
                    NephroCRIType_id: this.comboNephroCRIType.getValue() || null,
                    MorbusNephro_IsHyperten: this.comboIsHyperten.getValue() || null,
                    PersonHeight_Height: this.fieldPersonHeight.getValue() || null,
                    PersonWeight_Weight: this.fieldPersonWeight.getValue() || null,
                    PersonHeight_id: this.fieldPersonHeightId.getValue() || null,
                    PersonWeight_id: this.fieldPersonWeightId.getValue() || null,
                    MorbusNephro_Treatment: this.fieldTreatment.getValue() || null
                };
                var lab_grid = this.viewFrameMorbusNephroLab.getGrid();
                lab_grid.getStore().clearFilter();
                if (lab_grid.getStore().getCount() > 0) {
                    var lab_data = getStoreRecords(lab_grid.getStore(), { convertDateFields: true });
                    lab_grid.getStore().filterBy(function(rec) {
                        return Number(rec.get('RecordStatus_Code')) != 3;
                    });
                    dataToSave.MorbusNephroLabList = Ext.util.JSON.encode(lab_data);
                }
                Ext.Ajax.request({
                    callback: function(options, success, response) {
                        if (success){
                            var response_obj = Ext.util.JSON.decode(response.responseText);
                            if (response_obj.success){
                                sp.fieldMorbusId.setValue(response_obj.Morbus_id);
                                sp.fieldMorbusNephroId.setValue(response_obj.MorbusNephro_id);
                                callback();
                                var actionSetOnlyLast = sp.viewFrameMorbusNephroLab.getAction('action_setOnlyLast');
                                actionSetOnlyLast.setDisabled(false);
                            } else {
                                sw.swMsg.alert('Ошибка', response_obj.Error_Msg || 'Ошибка сохранения');
                                callback();
                            }
                        } else {
                            sw.swMsg.alert('Ошибка', 'Ошибка вызова сохранения');
                            callback();
                        }
                    },
                    params: dataToSave,
                    url: '/?c=MorbusNephro&m=doSavePersonDispForm'
                });
            },
            setEnabled: function(enable){
                if (this.inited) {
                    this.fieldFirstDate.setDisabled(!enable);
                    this.comboNephroDiagConfType.setDisabled(!enable);
                    this.comboNephroCRIType.setDisabled(!enable);
                    this.comboIsHyperten.setDisabled(!enable);
                    this.fieldPersonHeight.setDisabled(!enable);
                    this.fieldPersonWeight.setDisabled(!enable);
                    this.fieldTreatment.setDisabled(!enable);
                    this.viewFrameMorbusNephroLab.setReadOnly(!enable);
                }
            },
            load: function() {
                var sp = this;
                sp.panel.collapse();
                Ext.Ajax.request({
                    method: 'post',
                    callback: function(options, success, response) {
                        var resp = Ext.util.JSON.decode(response.responseText);
                        if (resp[0] && resp[0].Morbus_id) {
                            sp.fieldFirstDate.setValue(resp[0].MorbusNephro_firstDate);
                            sp.comboNephroDiagConfType.setValue(resp[0].NephroDiagConfType_id);
                            sp.comboNephroCRIType.setValue(resp[0].NephroCRIType_id);
                            sp.comboIsHyperten.setValue(resp[0].MorbusNephro_IsHyperten);
                            sp.fieldPersonHeight.setValue(resp[0].PersonHeight_Height);
                            sp.fieldPersonWeight.setValue(resp[0].PersonWeight_Weight);
                            sp.fieldPersonHeightId.setValue(resp[0].PersonHeight_id);
                            sp.fieldPersonWeightId.setValue(resp[0].PersonWeight_id);
                            sp.fieldTreatment.setValue(resp[0].MorbusNephro_Treatment);
                            sp.viewFrameMorbusNephroLab.removeAll();
                            sp.viewFrameMorbusNephroLab.loadData({
                                globalFilters: {
                                    MorbusNephro_id: sp.fieldMorbusNephroId.getValue()
                                },
                                noFocusOnLoad: true
                            });
                            sp.accessType = resp[0].accessType;
                            sp.setEnabled(me.action != 'view' && sp.accessType != 0);
                            sp.panel.expand();
                            sp.loaded = true;
                        } else {
                            sp.hide();
                        }
                    },
                    params: {
                        Morbus_id: sp.fieldMorbusId.getValue()
                    },
                    url: '/?c=MorbusNephro&m=doLoadEditFormMorbusNephro'
                });
            },
            show: function() {
                if (!this.loaded) {
                    this.load();
                } else {
                    this.setEnabled(me.action != 'view' && this.accessType != 0);
                }
                this.panel.show();
            },
            hide: function() {
                this.panel.hide();
            },
            beforeInitWindow: function() {
                this.btnAddEvnNotifyNephro = new Ext.Button({
                    handler: function() {
                        var sp = me.MorbusNephro;
                        var win = Ext.getCmp('PersonDispEditWindow');
                        var PersonDisp_id = win.FormPanel.getForm().findField('PersonDisp_id').getValue();
                        if(PersonDisp_id > 0){
	                        checkEvnNotifyNephro({
	                            EvnNotifyNephro_pid: null
	                            ,Server_id: sp.personInfoPanel.getFieldValue('Server_id')
	                            ,PersonEvn_id: sp.personInfoPanel.getFieldValue('PersonEvn_id')
	                            ,Person_id: sp.personInfoPanel.getFieldValue('Person_id')
	                            ,MedPersonal_id: getGlobalOptions().medpersonal_id
	                            ,Diag_id: sp.fieldDiagId.getValue()
	                            ,EvnNotifyNephro_setDate: getGlobalOptions().date
	                            ,Morbus_id: sp.fieldMorbusId.getValue()
	                            ,EvnNotifyNephro_id: null
	                            ,PersonRegister_id: null
	                            ,Diag_Name: sp.fieldDiagId.getRawValue()
	                            ,callback: function(success) {
	                                sp.btnAddEvnNotifyNephro.setDisabled(true);
	                            }
	                            ,mode: ''
	                            ,Alert_Msg: ''
	                            ,fromDispCard: true
	                        });
						} else {
							win.doSave({
								doNotHide:true,
								callback:function(){
									sp.btnAddEvnNotifyNephro.handler();
								}
							});
						}
                    },
                    id: 'PDEF_AddEvnNotifyNephroButton',
                    iconCls: 'add16',
                    disabled: true,
                    text: 'Создать Извещение по нефрологии'
                });
                this.toolbar = new Ext.Toolbar({
                    id : 'PDEF_MorbusNephroToolbar',
                    items: [this.btnAddEvnNotifyNephro]
                });
                this.viewFrameMorbusNephroLab = new sw.Promed.ViewFrame({
                    actions: [
                        { name: 'action_add', handler: function() {
                            me.openWindow('MorbusNephroLab', 'add');
                        } },
                        { name: 'action_edit', handler: function() {
                            me.openWindow('MorbusNephroLab', 'edit');
                        } },
                        { name: 'action_view', handler: function() {
                            me.openWindow('MorbusNephroLab', 'view');
                        } },
                        { name: 'action_delete', handler: function() {
                            me.deleteGridSelectedRecord('PDEF_MorbusNephroLab', 'MorbusNephroLab_id');
                        } },
                        { name: 'action_print' },
                        { name: 'action_refresh', hidden: true, disabled: true }
                    ],
                    autoExpandColumn: 'autoexpand',
                    autoExpandMin: 150,
                    autoLoadData: false,
                    border: false,
                    dataUrl: '/?c=MorbusNephro&m=doLoadGridLab',
                    id: 'PDEF_MorbusNephroLab',
                    paging: false,
                    style: 'margin-bottom: 10px',
                    stringfields: [
                        {name: 'MorbusNephroLab_id', type: 'int', header: 'ID', key: true},
                        {name: 'RecordStatus_Code', type: 'int', hidden: true},
                        {name: 'MorbusNephroLab_Date', type: 'date', dateFormat: 'd.m.Y', header: 'Дата', width: 120},
                        {name: 'MorbusNephro_id', type: 'int', hidden: true},
                        {name: 'Rate_id', type: 'int', hidden: true},
                        {name: 'RateType_id', type: 'int', hidden: true},
                        {name: 'RateType_Name', type: 'string', header: 'Показатель', width: 200},
                        {name: 'Rate_ValueStr', type: 'string', header: 'Значение', width: 240, id: 'autoexpand'}
                    ],
                    title: 'Лабораторные исследования',
                    toolbar: true
                });
                this.panel = new sw.Promed.Panel({
                    autoHeight: true,
                    style: 'margin-bottom: 0.5em;',
                    bodyStyle:'background:#DFE8F6;padding:5px;',
                    border: true,
                    collapsible: true,
                    region: 'north',
                    layout: 'form',
                    labelWidth: 270,
                    title: 'Специфика (Нефрология)',
                    tbar: this.toolbar,
                    items: [{
                        name: 'MorbusNephro_id',
                        xtype: 'hidden'
                    }, {
                        name: 'PersonHeight_id',
                        xtype: 'hidden'
                    }, {
                        name: 'PersonWeight_id',
                        xtype: 'hidden'
                    }, {
                        fieldLabel: 'Давность заболевания до установления диагноза',
                        name: 'MorbusNephro_firstDate',
                        //allowBlank: false,
                        xtype: 'swdatefield',
                        plugins: [new Ext.ux.InputTextMask('99.99.9999', false)]
                    }, {
                        fieldLabel: 'Способ установления диагноза',
                        anchor:'100%',
                        hiddenName: 'NephroDiagConfType_id',
                        xtype: 'swcommonsprcombo',
                        //allowBlank: false,
                        sortField:'NephroDiagConfType_Code',
                        comboSubject: 'NephroDiagConfType'
                    }, {
                        fieldLabel: 'Стадия ХБП',
                        anchor:'100%',
                        hiddenName: 'NephroCRIType_id',
                        xtype: 'swcommonsprcombo',
                        //allowBlank: false,
                        sortField:'NephroCRIType_Code',
                        comboSubject: 'NephroCRIType'
                    }, {
                        fieldLabel: 'Артериальная гипертензия',
                        width: 70,
                        hiddenName: 'MorbusNephro_IsHyperten',
                        //allowBlank: false,
                        xtype: 'swyesnocombo'
                    }, {
                        fieldLabel: 'Рост (в см)',
                        name: 'PersonHeight_Height',
                        width: 100,
                        xtype: 'numberfield',
                        allowNegative: false,
                        allowDecimals: false,
                        decimalPrecision: 0,
                        regex:new RegExp('(^[0-9]{0,3})$'),
                        maxValue: 999,
                        maxLength: 3,
                        maxLengthText: 'Максимальная длина этого поля 3 символа'
                    }, {
                        fieldLabel: 'Вес (в кг)',
                        name: 'PersonWeight_Weight',
                        width: 100,
                        xtype: 'numberfield',
                        allowNegative: false,
                        allowDecimals: false,
                        decimalPrecision: 0,
                        regex:new RegExp('(^[0-9]{0,3})$'),
                        maxValue: 999,
                        maxLength: 3,
                        maxLengthText: 'Максимальная длина этого поля 3 символа'
                    }, {
                        fieldLabel: 'Назначенное лечение (диета, препараты)',
                        name: 'MorbusNephro_Treatment',
                        anchor:'100%',
                        maxLength: 100,
                        maxLengthText: 'Максимальная длина этого поля 100 символов',
                        xtype: 'textfield'
                    },
                        this.viewFrameMorbusNephroLab
                    ]
                });
            }
        };
        this.MorbusNephro.beforeInitWindow();

		var specificsPanel = new sw.Promed.Panel({
			bodyStyle: 'padding-top: 0.5em;',
			collapsible: true,
			collapsed: false,
			hidden: true,
			id: 'PDEF_PregnantPanel',
			labelWidth: 270,
			layout: 'form',
			specifics: null,
			title: 'Беременность и роды',
			items:	[
				{
					name: 'PregnancySpec_id',
					value: 0,
					xtype: 'hidden'
				},
				//Lpu_aid – Наблюдалась в другом ЛПУ;   выбор из списка карт ДУ с диагнозами из группы «Беременность и роды»; отображаемые данные в списке карт: диагноз (код, наименование), ЛПУ (где стоит/состояла на учете), Дата постановки - Дата снятия (если есть); при выборе заполнить данные по специфике из выбранной карты ДУ, включая посещения, исследования, диагнозы.
				{
					name: 'Lpu_aid',
					xtype: 'hidden',
					value: null
				},
				/*{
					layout: 'column',
					border: false,
					items: [
						{
							layout: 'form',
							border: false,
							labelWidth: 180,
							width: 700,
							items: [
								{
									displayField: 'PersonDisp_Name',
									enableKeyEvents: true,
									editable: false,
									fieldLabel: 'Наблюдалась в другом ЛПУ',
									hiddenName: 'LpuAnother_id',
									listWidth: 550,
									mode: 'local',
									resizable: true,
									store: new Ext.data.Store({
										autoLoad: true,
										reader: new Ext.data.JsonReader({
											id: 'PersonDisp_id'
										}, [
											{ name: 'PersonDisp_id', mapping: 'PersonDisp_id' },
											{ name: 'PregnancySpec_id', mapping: 'PregnancySpec_id' },
											{ name: 'PersonDisp_Name', mapping: 'PersonDisp_Name' }
										]),
										url:'/?c=PregnancySpec&m=loadAnotherLpuList'
									}),
									tabIndex: 2008,
									triggerAction: 'all',
									valueField: 'PersonDisp_id',
									width : 480,
									xtype: 'swcombo'
								}
							]
						},
						{
							layout: 'form',
							border: false,
							items: [
								{
									xtype: 'button',
									text: 'Заполнить',
									tooltip: 'Заполнить форму данными из выбранной карты ДУ',
									handler: function() {
										fillFromAnotherPersonDisp();
									}
								}
							]
						}
					]
				},*/
				//PregnancySpec_Period – Срок беременности при взятии на учет (нед); целое, при переносе из пред-й карты ДУ пересчитать по новой дате постановки на учет, обязательное
				{
					allowDecimals: false,
					allowNegative: false,
					fieldLabel: 'Срок беременности при взятии на учет (нед)',
					maxValue: 50,
					minValue: 0,
					name: 'PregnancySpec_Period',
					width: 100,
					xtype: 'numberfield'
				},
				//PregnancySpec_Count – Которая беременность; целое, обязательное
				{
					allowDecimals: false,
					allowNegative: false,
					fieldLabel: 'Которая беременность',
					maxValue: 99,
					minValue: 1,
					name: 'PregnancySpec_Count',
					width: 100,
					xtype: 'numberfield'
				},
				//PregnancySpec_CountBirth – Из них закончились родами; целое, обязательное
				{
					allowDecimals: false,
					allowNegative: false,
					fieldLabel: 'Из них закончились родами',
					maxValue: 10,
					minValue: 0,
					name: 'PregnancySpec_CountBirth',
					width: 100,
					xtype: 'numberfield'
				},
				//PregnancySpec_CountAbort – Из них закончились абортами; целое, обязательное
				{
					allowDecimals: false,
					allowNegative: false,
					fieldLabel: 'Из них закончились абортами',
					maxValue: 99,
					minValue: 0,
					name: 'PregnancySpec_CountAbort',
					width: 100,
					xtype: 'numberfield'
				},
				//PregnancySpec_BirthDT – Предполагаемая дата; обязательное
				{
					fieldLabel: 'Предполагаемая дата',
					format: 'd.m.Y',
					name: 'PregnancySpec_BirthDT',
					plugins: [
						new Ext.ux.InputTextMask('99.99.9999', false)
					],
					width: 95,
					xtype: 'swdatefield'
				},
				//BirthResult_id – Исход беременности; заполняется автоматически из Характера родов в «КВС.Специфика. Сведения о родах», привязаной к карте ДУ, и не доступно
				{
					comboSubject: 'BirthResult',
					fieldLabel: 'Исход беременности',
					hiddenName: 'BirthResult_id',
					listWidth: 300,
					//disabled: true,
					width: 300,
					xtype: 'swcommonsprcombo'
				},
				//PregnancySpec_OutcomPeriod – Срок исхода (нед); целое, рассчитывается автоматически по дате родов из «КВС.Специфика. Сведения о родах»,  дате постановки на ДУ и Срока беременности при взятии на учет (нед) и не доступно
				{
					allowDecimals: false,
					allowNegative: false,
					fieldLabel: 'Срок исхода (нед)',
					maxValue: 90,
					//disabled: true,
					minValue: 0,
					name: 'PregnancySpec_OutcomPeriod',
					width: 100,
					xtype: 'numberfield'
				},
				//PregnancySpec_OutcomDT – Дата исхода; автоматически по дате родов из «КВС.Специфика. Сведения о родах» и не доступно
				{
					format: 'd.m.Y',
					fieldLabel: 'Дата исхода',
					//disabled: true,
					name: 'PregnancySpec_OutcomDT',
					plugins: [
						new Ext.ux.InputTextMask('99.99.9999', false)
					],
					width: 100,
					xtype: 'swdatefield'
				},
				//BirthSpec_id – Особенности родов;, заполняется автоматически из «КВС.Специфика. Сведения о родах», привязаной к карте ДУ, и не доступно
				{
					comboSubject: 'BirthSpec',
					fieldLabel: 'Особенности родов',
					hiddenName: 'BirthSpec_id',
					//disabled: true,
					width: 300,
					listWidth: 300,
					xtype: 'swcommonsprcombo'
				},
				//PregnancySpec_IsHIVtest – Обследована на ВИЧ; (Да/Нет);
				{
					comboSubject: 'YesNo',
					fieldLabel: 'Обследована на ВИЧ',
					hiddenName: 'PregnancySpec_IsHIVtest',
					width: 100,
					listWidth: 100,
					xtype: 'swcommonsprcombo'
				},
				//PregnancySpec_IsHIV – Наличие ВИЧ-инфекции; (Да/Нет);
				{
					comboSubject: 'YesNo',
					fieldLabel: 'Наличие ВИЧ-инфекции',
					hiddenName: 'PregnancySpec_IsHIV',
					width: 100,
					listWidth: 100,
					xtype: 'swcommonsprcombo',
					listeners: {
						'keydown': function(inp, e) {
							if ( !e.shiftKey ) {
								e.stopEvent();
								if ( e.browserEvent.stopPropagation )
									e.browserEvent.stopPropagation();
								else
									e.browserEvent.cancelBubble = true;

								if ( e.browserEvent.preventDefault )
									e.browserEvent.preventDefault();
								else
									e.browserEvent.returnValue = false;
								e.returnValue = false;
								if ( Ext.isIE ) {
									e.browserEvent.keyCode = 0;
									e.browserEvent.which = 0;
								}
								switch ( e.getKey() ) {
									case Ext.EventObject.TAB:
											if ( !this.findById('PDEF_EvnUslugaPregnancySpecGridPanel').collapsed && this.findById('PDEF_EvnUslugaPregnancySpecGrid').getStore().getCount() > 0 ) {
												this.findById('PDEF_EvnUslugaPregnancySpecGrid').getView().focusRow(0);
												this.findById('PDEF_EvnUslugaPregnancySpecGrid').getSelectionModel().selectFirstRow();
											}
											else if ( this.action != 'view' ) {
												this.buttons[0].focus();
											}
											else {
												// this.buttons[1].focus();
												this.buttons[this.buttons.length - 1].focus();
											}
									break;
								}
							}
						}.createDelegate(this)
					}
				},
				//Список. Лабораторные обследования
				new sw.Promed.Panel({
					border: true,
					collapsible: false,
					height: 200,
					id: 'PDEF_EvnUslugaPregnancySpecGridPanel',
					isLoaded: false,
					layout: 'border',
					style: 'margin-bottom: 0.5em;',
					title: 'Лабораторные обследования',
					items: [labGrid]}),
				//Список. Осложнение беременности.
				new sw.Promed.ViewFrame({
					actions: [
						{
							name: 'action_add',
							handler: function() {
								openPregnancySpecComplicationEditWindow('add');
							}
						},
						{
							name: 'action_edit',
							handler: function() {
								openPregnancySpecComplicationEditWindow('edit');
							}
						},
						{
							name: 'action_view',
							handler: function() {
								openPregnancySpecComplicationEditWindow('view');
							}
						},
						{
							name: 'action_delete',
							handler: function() {
								deleteGridSelectedRecord('PDEF_PregnancySpecComplication', 'PregnancySpecComplication_id');
							}
						},
						{ name: 'action_refresh', hidden: true, disabled: true }
					],
					autoLoadData: false,
					focusOnFirstLoad: false,
					border: false,
					dataUrl: '/?c=PregnancySpec&m=loadPregnancySpecComplication',
					height: 150,
					id: 'PDEF_PregnancySpecComplication',
					region: 'center',
					stringfields: [
						{ name: 'PregnancySpecComplication_id', type: 'int', header: 'ID', key: true, hidden: true},
						{ name: 'PregnancySpec_id', type: 'int', hidden: true},
						//– Дата/время установки; обязательное
						{ name: 'PSC_setDT', type: 'date', format: 'd.m.Y', header: 'Дата', hidden: false, width: 100},
						//– Диагноз. обязательное
						{ name: 'Diag_Name', type: 'string', header: 'Диагноз', hidden: false, width: 200},
						{ name: 'Diag_id', type: 'int', hidden: true},
						{ name: 'RecordStatus_Code', type: 'int', hidden: true}
					],
					title: 'Осложнение беременности',
					toolbar: true
				}),
				//Список. Экстрагенитальные заболевания.
				new sw.Promed.ViewFrame({
					actions: [
						{
							name: 'action_add',
							handler: function() {
								openPregnancySpecExtragenitalDiseaseEditWindow('add');
							}
						},
						{
							name: 'action_edit',
							handler: function() {
								openPregnancySpecExtragenitalDiseaseEditWindow('edit');
							}
						},
						{
							name: 'action_view',
							handler: function() {
								openPregnancySpecExtragenitalDiseaseEditWindow('view');
							}
						},
						{
							name: 'action_delete',
							handler: function() {
								deleteGridSelectedRecord('PDEF_PregnancySpecExtragenitalDisease', 'PSED_id');
							}
						},
						{ name: 'action_refresh', hidden: true, disabled: true }
					],
					autoLoadData: false,
					border: false,
					focusOnFirstLoad: false,
					dataUrl: '/?c=PregnancySpec&m=loadPregnancySpecExtragenitalDisease',
					height: 150,
					id: 'PDEF_PregnancySpecExtragenitalDisease',
					region: 'center',
					stringfields: [
						{ name: 'PSED_id', type: 'int', header: 'ID', key: true, hidden: true},
						{ name: 'PregnancySpec_id', type: 'int', hidden: true},
						//– Дата/время установки; обязательное
						{ name: 'PSED_setDT', type: 'date', header: 'Дата', hidden: false, width: 100},
						//– Диагноз. обязательное
						{ name: 'Diag_Name', type: 'string', header: 'Диагноз', hidden: false, width: 200},
						{ name: 'Diag_id', type: 'int', hidden: true},
						{ name: 'RecordStatus_Code', type: 'int', hidden: true}
					],
					title: 'Экстрагенитальные заболевания',
					toolbar: true
				}),
				new sw.Promed.ViewFrame({
							id:'PDEF_ChildGrid',
							actions:[
								{name:'action_add',disabled: true},
								{name:'action_edit',disabled: true},
								{
									name: 'action_view',
									handler: function () {
										var params = new Object();
										var grid = parentWindow.findById('PDEF_ChildGrid').getGrid();
										var record = grid.getSelectionModel().getSelected();
										params.EvnPS_id = record.get('ChildEvnPS_id');
										params.Person_id = record.get('Person_cid');
										params.Server_id = record.get('Server_id');
										params.action = 'view';
										params.childPS = true;
										getWnd({objectName:'swEvnPSEditWindow2', objectClass:'swEvnPSEditWindow'}, {params:{id:'EvnPSEditWindow2'}}).show(params);
									}
								},
								{name:'action_delete',disabled: true},
								{name:'action_refresh', disabled:true},
								{name:'action_print', disabled:true}
							],
							autoLoadData:false,
							focusOnFirstLoad:false,
							dataUrl:'/?c=BirthSpecStac&m=loadChildGridData',
							height:130,
							paging:false,
							region:'center',
							stringfields:[
								{name:'ChildEvnPS_id', type:'int', header:'ID', key:true},
								{name:'Person_F', type:'string', hidden:false, header:'Фамилия'},
								{name:'Person_I', type:'string', hidden:false, header:'Имя'},
								{name:'Person_O', type:'string', hidden:false, header:'Отчество'},
								{name:'Person_Bday', type:'date', hidden:false, format:'d.m.Y', header:'Дата рождения', width:110},
								{name:'Sex_name', type:'string', hidden:false, header:'Пол', width:80},
								{name:'Person_Weight', type:'float', hidden:true, header:'Масса при рождении', width:120},
								{name:'Okei_id', type:'int', hidden:true},
								{name:'PersonWeight_text', type:'string', hidden:false, header:'Масса при рождении', width:120},
								{name:'Person_Height', type:'float', hidden:false, header:'Рост при рождении (см)', width:110},
								{name:'BirthSvid_id', type:'int', hidden:true},
								{name:'BirthSvid_Num', type:'string', hidden:false, header:'Св-во о рождении', width:110},
								{name:'BirthResult', type:'string', hidden:false, header:'Результат родов', width:100},
								{name:'CountChild', type:'float', hidden:false, header:'Который по счету', width:110},
								{name:'RecordStatus_Code', type:'int', hidden:true},
								{name:'EvnLink_id', type:'int', hidden:true},
								{name:'Person_id', type:'int', hidden:true},
								{name:'Person_cid', type:'int', hidden:true},
								{name:'Server_id', type:'int', hidden:true}
							],
							onRowSelect:function (sm, index, record) {
								this.getAction('action_view').setDisabled(!+record.get('ChildEvnPS_id'));
								this.getAction('action_medsved').setDisabled( !+record.get('BirthSvid_id') );
							},
							title:'Дети',
							style:'margin-bottom: 10px'
						}),
				/*/Дети
				new sw.Promed.ViewFrame({
					actions: [
						{
							name: 'action_add',
							disabled: true
						},
						{
							name: 'action_edit',
							disabled: true
						},
						{
							name: 'action_delete',
							disabled: true
						},
						{
							name: 'action_view',
							handler: function () {
								var params = new Object();
								var grid = parentWindow.findById('PDEF_ChildGrid').getGrid();
								var record = grid.getSelectionModel().getSelected();
								params.EvnPS_id = record.get('ChildEvnPS_id');
								params.Person_id = record.get('Person_cid');
								params.Server_id = record.get('Server_id');
								params.action = 'view';
								params.childPS = true;
								getWnd({objectName:'swEvnPSEditWindow2', objectClass:'swEvnPSEditWindow'}, {params:{id:'EvnPSEditWindow2'}}).show(params);
							}
						},
						{ name: 'action_medsvid', hidden: true },
						{
							name: 'action_refresh',
							disabled: true
						}
					],
					autoLoadData: false,
					focusOnFirstLoad: false,
					border: false,
					dataUrl: '/?c=BirthSpecStac&m=loadChildGridData',
					height: 150,
					id: 'PDEF_ChildGrid',
					region: 'center',
					stringfields: [
						//– Фамилия;
						{ name: 'Person_F', type: 'string',  hidden: false, header: 'Фамилия'},
						//– Имя;
						{ name: 'Person_I', type: 'string',  hidden: false, header: 'Имя'},
						//– Отчество;
						{ name: 'Person_O', type: 'string',  hidden: false, header: 'Отчество'},
						//– ДР;
						{ name: 'Person_Bday', type: 'date', hidden: false, format: 'd.m.Y', header: 'Дата рождения', width: 110 },
						//– Пол;
						{ name: 'Sex_name', type: 'string',  hidden: false, header: 'Пол', width: 80 },
						//– Масса; при рождении из медпериодики ребенка
						{ name: 'Person_Weight', type: 'float', hidden: false, header: 'Масса при рождении', width: 120 },
						//– Рост; при рождении из медпериодики ребенка
						{ name: 'Person_Height', type: 'float', hidden: false, header: 'Рост при рождении', width: 110 },
						//– Результат родов; рассчитывается автоматически из КВС ребенка по данным выписки. Значения:
						{ name: 'BirthResult', type: 'string', hidden: false, header: 'Результат родов', width: 100 },
						//– Который по счету
						{ name: 'CountChild', type: 'float', hidden: false, header: 'Который по счету', width: 110 },
						{ name: 'BirthSvid_Num', type: 'string', hidden: false, header: 'Св-во о рождении', width: 110 },
						{ name: 'BirthSvid_id', type: 'int', hidden: true },
						{ name: 'ChildEvnPS_id', type: 'int', header: 'ID', key: true },
						{ name: 'RecordStatus_Code', type: 'int', hidden: true },
						{ name: 'EvnLink_id', type: 'int', hidden: true },
						{ name: 'Person_id', type: 'int', hidden: true },
						{ name: 'Person_cid', type: 'int', hidden: true },
						{ name: 'Server_id', type: 'int', hidden: true }
					],
					title: 'Дети',
					toolbar: true
				}),*/
				//Мертворожденные
				new sw.Promed.ViewFrame({
					actions: [
						{
							name: 'action_add',
							disabled: true
						},
						{
							name: 'action_edit',
							disabled: true
						},
						{
							name: 'action_delete',
							disabled: true
						},
						{name:'action_view', handler:function () {
							this.openBirthSpecChildDeathEditWindow('view');
						}.createDelegate(this)},

						{
							name: 'action_refresh',
							disabled: true
						}
					],
					border: false,
					autoLoadData: false,
					focusOnFirstLoad: false,
					dataUrl: '/?c=BirthSpecStac&m=loadChildDeathGridData',
					height: 150,
					id: 'PDEF_DeadChildGrid',
					region: 'center',
					stringfields: [
						//– Врач;
						{ name: 'MedStaffFact_Name', type: 'string', header: 'Врач', hidden: false, width: 180 },
						//– Диагноз;
						{ name: 'Diag_Name', type: 'string', header: 'Диагноз', hidden: false, width: 120},
						//– Пол;
						{ name: 'Sex_Name', type: 'string', header: 'Пол', hidden: false, width: 40},
						//– Масса;
						{ name: 'ChildDeath_Weight_text', type: 'string', header: 'Масса', width: 60 },
						//– Рост;
						{ name: 'ChildDeath_Height', type: 'float', header: 'Рост (см)', width: 60 },
						//– Наступление смерти;
						{ name: 'PntDeathTime_Name', type: 'string', header: 'Наступление смерти', hidden: false },
						//– Доношенный;
						{ name: 'ChildTermType_Name', type: 'string', header: 'Доношенный', hidden: false },
						//– Который по счету.
						{ name: 'ChildDeath_Weight', type: 'float', hidden: true, width: 60 },
						{ name: 'Okei_wid', type: 'int', hidden: true, header: 'Масса', width: 60 },
						{ name: 'ChildDeath_Count', type: 'int', header: 'Который по счету', width: 105 },
						{ name: 'ChildDeath_id', type: 'int', header: 'ID', key: true },
						{ name: 'LpuSection_id', type: 'int', hidden: true },
						{ name: 'MedStaffFact_id', type: 'int', hidden: true },
						{ name: 'Diag_id', type: 'int', hidden: true},
						{ name: 'Sex_id', type: 'int', hidden: true },
						{ name: 'PntDeathTime_id', type: 'int', hidden: true },
						{ name: 'ChildTermType_id', type: 'int', hidden: true },
						{ name: 'PntDeathSvid_id', type: 'int', hidden: true},
						{ name: 'PntDeathSvid_Num', type: 'string', header: 'Свид-во о смерти', width: 110, hidden: false},
						{ name: 'RecordStatus_Code', type: 'int', hidden: true }
					],
					title: 'Мертворожденные',
					toolbar: true
				})
			]
		});
		var calcPregPeriod = function () {
			//заполняю поле "срок беременности"
			var s = labGrid.getStore();
			var pdBeginDt = parentWindow.findById('PersonDispEditForm').getForm().findField('PersonDisp_begDate').getValue();
			var period = parentWindow.findById('PersonDispEditForm').getForm().findField('PregnancySpec_Period').getValue();
			var cnt = s.getCount();
			if (pdBeginDt && period && cnt) {
				for (var i = 0; i < cnt; i++) {
					var r = s.getAt(i);
					if (r.get('EUPS_setDate')) {
						var pregPeriod = period + Math.floor((pdBeginDt.getElapsed(r.get('EUPS_setDate')) / 1000 / 60 / 60 / 24) / 7);
						r.set('pregPeriod', pregPeriod);
						r.commit();
					}
				}
			}
		};
		this.specifics = {
			//author: gabdushev
			//===========переменные===========
			pw: this, //parentWindow сокращено до pw, т.к. употребляется часто
			loaded: false,
			inited: false,
			//===========методы===========
			//начальная загрузка формы
			load: function () {
				var pregnancySpec_id = this.formFields.PregnancySpec_id.getValue();
				/*
				Ext.getCmp('PersonDispEditForm').getForm().findField('LpuAnother_id').getStore().load({
					params: {
						PersonDisp_id: Ext.getCmp('PersonDispEditForm').getForm().findField('PersonDisp_id').getValue()
					}
				});*/
				if (pregnancySpec_id == '') {
					//специфика о беременности создается
				} else {
					//специфика о беременности редактируется
					loadPregnancySpecIntoForm(pregnancySpec_id);
				}
				//избавиться от костыля для нормальной отрисовки гридов во время первой загрузки формы
				setTimeout(function (){
					Ext.getCmp("PDEF_PregnantPanel").doLayout();
				}, 100);
			},
			reset: function(){
				labGrid.getStore().removeAll();
				this.panel.findById('PDEF_PregnancySpecComplication').getGrid().getStore().removeAll();
				this.panel.findById('PDEF_PregnancySpecExtragenitalDisease').getGrid().getStore().removeAll();
				this.panel.findById('PDEF_ChildGrid').getGrid().getStore().removeAll();
				this.panel.findById('PDEF_DeadChildGrid').getGrid().getStore().removeAll();
			},
			setValidation: function (allowBlank){
				//включить/выключить проверку
				this.formFields.PregnancySpec_Period.allowBlank = allowBlank;
				this.formFields.PregnancySpec_Count.allowBlank = allowBlank;
				this.formFields.PregnancySpec_CountBirth.allowBlank = allowBlank;
				this.formFields.PregnancySpec_CountAbort.allowBlank = allowBlank;
				this.formFields.PregnancySpec_BirthDT.allowBlank = allowBlank;
				//this.formFields.PregnancySpec_IsHIVtest.allowBlank = allowBlank;
				//this.formFields.PregnancySpec_IsHIV.allowBlank = allowBlank;
			},
			save: function (callAfterSpecificsSave) {
				//log('->specifics.save');
				var personDisp_id = parentWindow.findById('PersonDispEditForm').getForm().findField('PersonDisp_id').getValue();
				var dataToSave = new Object();
				dataToSave.PregnancySpec_id = this.formFields.PregnancySpec_id.getValue();
				if (!dataToSave.PregnancySpec_id) {
					dataToSave.PregnancySpec_id = 0;
				}
				this.setValidation(false);
				if (!parentWindow.findById('PersonDispEditForm').getForm().isValid()) {
					sw.swMsg.show({
						buttons: Ext.Msg.OK,
						fn: function() {
							this.formStatus = 'edit';
							parentWindow.findById('PersonDispEditForm').getFirstInvalidEl().focus(false);
						}.createDelegate(this),
						icon: Ext.Msg.WARNING,
						msg: ERR_INVFIELDS_MSG,
						title: ERR_INVFIELDS_TIT
					});
				} else {
					dataToSave.Lpu_aid = this.formFields.Lpu_aid.getValue();
					dataToSave.PregnancySpec_Period = this.formFields.PregnancySpec_Period.getValue();
					dataToSave.PregnancySpec_Count = this.formFields.PregnancySpec_Count.getValue();
					dataToSave.PregnancySpec_CountBirth = this.formFields.PregnancySpec_CountBirth.getValue();
					dataToSave.PregnancySpec_CountAbort = this.formFields.PregnancySpec_CountAbort.getValue();
					dataToSave.PregnancySpec_BirthDT = this.formFields.PregnancySpec_BirthDT.getValue().format('d.m.Y');
					//Эти поля не надо передавать на сохранение, они берутся из КВС. UPD: позволить редактировать здесь
					dataToSave.BirthResult_id             = this.formFields.BirthResult_id            .getValue();
					dataToSave.PregnancySpec_OutcomPeriod = this.formFields.PregnancySpec_OutcomPeriod.getValue();
					if (this.formFields.PregnancySpec_OutcomDT.getValue()) {
						dataToSave.PregnancySpec_OutcomDT     = this.formFields.PregnancySpec_OutcomDT    .getValue().format('d.m.Y');
					} else {
						dataToSave.PregnancySpec_OutcomDT = null;
					}
					dataToSave.BirthSpec_id               = this.formFields.BirthSpec_id              .getValue();
					dataToSave.PregnancySpec_IsHIVtest = this.formFields.PregnancySpec_IsHIVtest.getValue();
					dataToSave.PregnancySpec_IsHIV = this.formFields.PregnancySpec_IsHIV.getValue();
					dataToSave.PersonDisp_id = personDisp_id;
					if (this.pw.findById('PDEF_PregnancySpecComplication')) {
						var person_height_grid = this.pw.findById('PDEF_PregnancySpecComplication').getGrid();
						person_height_grid.getStore().clearFilter();
						if (person_height_grid.getStore().getCount() > 0) {
							var person_height_data = getStoreRecords(person_height_grid.getStore(), { convertDateFields: true });
							person_height_grid.getStore().filterBy(function(rec) {
								return Number(rec.get('RecordStatus_Code')) != 3;
							});
							dataToSave.PregnancySpecComplication = Ext.util.JSON.encode(person_height_data);
						}
					}
					if (this.pw.findById('PDEF_PregnancySpecExtragenitalDisease')) {
						person_height_grid = this.pw.findById('PDEF_PregnancySpecExtragenitalDisease').getGrid();
						person_height_grid.getStore().clearFilter();
						if (person_height_grid.getStore().getCount() > 0) {
							person_height_data = getStoreRecords(person_height_grid.getStore(), { convertDateFields: true });
							person_height_grid.getStore().filterBy(function(rec) {
								return Number(rec.get('RecordStatus_Code')) != 3;
							});
							dataToSave.PregnancySpecExtragenitalDisease = Ext.util.JSON.encode(person_height_data);
						}
					}
					Ext.Ajax.request({
						method: 'post',
						callback: function(options, success, response) {
							if (success){
								var response_obj = Ext.util.JSON.decode(response.responseText);
								if (response_obj.success){
									parentWindow.findById('PersonDispEditForm').getForm().findField('PregnancySpec_id').setValue(response_obj.PregnancySpec_id);
									//log("parentWindow.findById('PersonDispEditForm').getForm().findField('PregnancySpec_id').setValue", response_obj.PregnancySpec_id);
									//log("parentWindow.findById('PersonDispEditForm').getForm().findField('PregnancySpec_id').getValue", parentWindow.findById('PersonDispEditForm').getForm().findField('PregnancySpec_id').getValue());
									if (callAfterSpecificsSave) {
										var arg = null;
										if (arguments) {
											var arg = arguments;
										}
										callAfterSpecificsSave(arg);
									}
								} else {
									//todo: выдать сообщзение об ошибке сохранения
								}
							} else {
								//todo: выдать сообщзение об ошибке вызова сохранения на сервере
							}

						},
						params: dataToSave,
						url: '/?c=PregnancySpec&m=save'
					});
				}
				//log('<-specifics.save');
			},
			//отображает панель
			show: function (action) {
				//log('show', action);
				if (!this.loaded) {
					this.load();
				}

				this.setEnabled((action == 'edit')||(action == 'add'));
				this.panel.show();
			},
			setEnabled: function(enable){
				//log('->setEnabled', enable);
				if (enable) {
					this.formFields.Lpu_aid.enable();
					this.formFields.PregnancySpec_Period.enable();
					this.formFields.PregnancySpec_Count.enable();
					this.formFields.PregnancySpec_CountBirth.enable();
					this.formFields.PregnancySpec_CountAbort.enable();
					this.formFields.PregnancySpec_BirthDT.enable();
					this.formFields.BirthResult_id.enable();
					this.formFields.PregnancySpec_OutcomPeriod.enable();
					this.formFields.PregnancySpec_OutcomDT.enable();
					this.formFields.BirthSpec_id.enable();
					this.formFields.PregnancySpec_IsHIVtest.enable();
					this.formFields.PregnancySpec_IsHIV.enable();
					this.formFields.PregnancySpec_id.enable();
					this.formFields.EvnSection_id.enable();
					this.formFields.EvnSection_pid.enable();
				} else {
					this.formFields.Lpu_aid.disable();
					this.formFields.PregnancySpec_Period.disable();
					this.formFields.PregnancySpec_Count.disable();
					this.formFields.PregnancySpec_CountBirth.disable();
					this.formFields.PregnancySpec_CountAbort.disable();
					this.formFields.PregnancySpec_BirthDT.disable();
					this.formFields.BirthResult_id.disable();
					this.formFields.PregnancySpec_OutcomPeriod.disable();
					this.formFields.PregnancySpec_OutcomDT.disable();
					this.formFields.BirthSpec_id.disable();
					this.formFields.PregnancySpec_IsHIVtest.disable();
					this.formFields.PregnancySpec_IsHIV.disable();
					this.formFields.PregnancySpec_id.disable();
					this.formFields.EvnSection_id.disable();
					this.formFields.EvnSection_pid.disable();
				}
				parentWindow.findById('PDEF_PregnancySpecComplication').setReadOnly(!enable);
				parentWindow.findById('PDEF_PregnancySpecExtragenitalDisease').setReadOnly(!enable);
				//labGridOnRowselect()
			},
			//начальная инициализация
			init: function () {
				if (!this.inited) {
					var f = this.pw.findById('PersonDispEditForm').getForm();
					this.formFields.Lpu_aid = f.findField('Lpu_aid');
					this.formFields.PregnancySpec_Period = f.findField('PregnancySpec_Period');
					this.formFields.PregnancySpec_Count = f.findField('PregnancySpec_Count');
					this.formFields.PregnancySpec_CountBirth = f.findField('PregnancySpec_CountBirth');
					this.formFields.PregnancySpec_CountAbort = f.findField('PregnancySpec_CountAbort');
					this.formFields.PregnancySpec_BirthDT = f.findField('PregnancySpec_BirthDT');
					this.formFields.BirthResult_id = f.findField('BirthResult_id');
					this.formFields.PregnancySpec_OutcomPeriod = f.findField('PregnancySpec_OutcomPeriod');
					this.formFields.PregnancySpec_OutcomDT = f.findField('PregnancySpec_OutcomDT');
					this.formFields.BirthSpec_id = f.findField('BirthSpec_id');
					this.formFields.PregnancySpec_IsHIVtest = f.findField('PregnancySpec_IsHIVtest');
					this.formFields.PregnancySpec_IsHIV = f.findField('PregnancySpec_IsHIV');
					this.formFields.PregnancySpec_id = f.findField('PregnancySpec_id');
					this.formFields.EvnSection_id = f.findField('EvnSection_id');
					this.formFields.EvnSection_pid = f.findField('EvnSection_pid');
					this.inited = true;
					this.panel.specifics = this;
					//инициализируем компоненты
					var complGrid = this.panel.findById('PDEF_PregnancySpecComplication');
					var diseaGrid = this.panel.findById('PDEF_PregnancySpecExtragenitalDisease');
					var childGrid = this.panel.findById('PDEF_ChildGrid');
					var deathGrid = this.panel.findById('PDEF_DeadChildGrid');

					//focusing viewframes
					complGrid.focusPrev = labGrid;
					complGrid.focusPrev.type = 'grid';//это нужно чтобы нормально отработало событие по шифт-таб
					complGrid.focusPrev.name = 'PDEF_EvnUslugaPregnancySpec';//и это тоже
					complGrid.focusOn = diseaGrid;
					complGrid.focusOn.type = 'field';//это нужно чтобы нормально отработало событие по таб
					complGrid.focusOn.name = complGrid.focusOn.id;//и это тоже
					diseaGrid.focusPrev = complGrid;
					diseaGrid.focusPrev.type = 'field';//это нужно чтобы нормально отработало событие по шифт-таб
					diseaGrid.focusPrev.name = diseaGrid.focusPrev.id;//и это тоже
					diseaGrid.focusOn = childGrid;
					diseaGrid.focusOn.type = 'field';//это нужно чтобы нормально отработало событие по таб
					diseaGrid.focusOn.name = diseaGrid.focusOn.id;//и это тоже
					childGrid.focusPrev = diseaGrid;
					childGrid.focusPrev.type = 'field';//это нужно чтобы нормально отработало событие по шифт-таб
					childGrid.focusPrev.name = childGrid.focusPrev.id;//и это тоже
					childGrid.focusOn = deathGrid;
					childGrid.focusOn.type = 'field';//это нужно чтобы нормально отработало событие по таб
					childGrid.focusOn.name = childGrid.focusOn.id;//и это тоже
					deathGrid.focusPrev = childGrid;
					deathGrid.focusPrev.type = 'field';//это нужно чтобы нормально отработало событие по шифт-таб
					deathGrid.focusPrev.name = deathGrid.focusPrev.id;//и это тоже
					deathGrid.focusOn = this.pw.buttons[0];
					deathGrid.focusOn.type = 'field';//это нужно чтобы нормально отработало событие по таб
					deathGrid.focusOn.name = deathGrid.focusOn.id;//и это тоже
					var f = parentWindow.findById('PersonDispEditForm').getForm();
					f.add(this.formFields.PregnancySpec_Period);
					f.add(this.formFields.PregnancySpec_Count);
					f.add(this.formFields.PregnancySpec_CountBirth);
					f.add(this.formFields.PregnancySpec_CountAbort);
					f.add(this.formFields.PregnancySpec_BirthDT);
					f.add(this.formFields.BirthResult_id);
					f.add(this.formFields.PregnancySpec_OutcomPeriod);
					f.add(this.formFields.PregnancySpec_OutcomDT);
					f.add(this.formFields.BirthSpec_id);
					f.add(this.formFields.PregnancySpec_IsHIVtest);
					f.add(this.formFields.PregnancySpec_IsHIV);
					f.add(this.formFields.PregnancySpec_id);
					f.add(this.formFields.EvnSection_id);
					f.add(this.formFields.EvnSection_pid);
				} else {
					this.reset();
				}
				LoadEmptyRow(labGrid);
				LoadEmptyRow(specificsPanel.findById('PDEF_PregnancySpecComplication').getGrid());
				LoadEmptyRow(specificsPanel.findById('PDEF_PregnancySpecExtragenitalDisease').getGrid());
				LoadEmptyRow(specificsPanel.findById('PDEF_ChildGrid').getGrid());
				LoadEmptyRow(specificsPanel.findById('PDEF_DeadChildGrid').getGrid());
				this.setValidation(true);
				this.loaded = false;
			},
			//===========интерфейс===========
			//todo: сделать чтобы то что выпадает у выпадающих списков было нормального размера
			formFields: {
				Lpu_aid: null,
				PregnancySpec_Period: null,
				PregnancySpec_Count: null,
				PregnancySpec_CountBirth: null,
				PregnancySpec_CountAbort: null,
				PregnancySpec_BirthDT: null,
				BirthResult_id: null,
				PregnancySpec_OutcomPeriod: null,
				PregnancySpec_OutcomDT: null,
				BirthSpec_id: null,
				PregnancySpec_IsHIVtest: null,
				PregnancySpec_IsHIV: null,
				PregnancySpec_id: null,
				EvnSection_id: null,
				EvnSection_pid: null
			},
			panel: specificsPanel
		};
		this.FormPanel = new Ext.form.FormPanel({
			autoScroll: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			border: false,
			frame: false,
			id: 'PersonDispEditForm',
			labelAlign: 'right',
			labelWidth: 170,
			reader: new Ext.data.JsonReader({
				success: function() {
				}
			}, [
				{ name: 'accessType' },
				{ name: 'Diag_id' },
				{ name: 'DispOutType_id' },
				{ name: 'LpuSection_id' },
				{ name: 'Lpu_id' },
				{ name: 'MedPersonal_id' },
				{ name: 'Person_id' },
				{ name: 'PersonDisp_NumCard' },
				{ name: 'PersonDisp_begDate' },
				{ name: 'PersonDisp_endDate' },
				{ name: 'Server_id' },
				{ name: 'Sickness_id' },
                { name: 'PregnancySpec_id' },
                { name: 'Morbus_id' },
				{ name: 'EvnSection_id' },
				{ name: 'EvnSection_pid' },
				{ name: 'PersonDisp_DiagDate' },
				{ name: 'DiagDetectType_id' },
				{ name: 'DispGroup_id' },
				{ name: 'DeseaseDispType_id' },
				{ name: 'PersonDisp_IsTFOMS' },
				{ name: 'PersonDisp_IsSignedEP' },
				{ name: 'Label_id' }
			]),
			region: 'center',
			url: '/?c=PersonDisp&m=savePersonDisp',
			items: [
				{
					name: 'accessType',
					value: '',
					xtype: 'hidden'
				},
				{
						name: 'Lpu_id',
						value: 0,
						xtype: 'hidden'
				},
				{
					name: 'PersonDisp_id',
					value: 0,
					xtype: 'hidden'
				},
				{
					name: 'MedPersonal_id',
					value: null,
					xtype: 'hidden'
				},
				{
					name: 'Person_id',
					value: 0,
					xtype: 'hidden'
				}, {
					name: 'Label_id',
					value: 0,
					xtype: 'hidden'
				}, {
                    name: 'Morbus_id',
                    xtype: 'hidden'
                }, {
					xtype: 'hidden',
					name: 'Server_id',
					value: -1
				},
				{
					xtype: 'hidden',
					name: 'PersonDisp_IsTFOMS',
					value: 1
				},
				{
					xtype: 'hidden',
					name: 'PersonDisp_IsSignedEP'
				},
				{
					xtype: 'hidden',
					name: 'PregnancySpec_id',
					value: null
				},
				{
					xtype: 'hidden',
					name: 'EvnSection_id',//идентификтор Движения в КВС, если эта ДУ вообще связана с какой-либо спецификой беременности КВС
					value: -1
				},
				{
					xtype: 'hidden',
					name: 'EvnSection_pid',//идентификтор КВС, если эта ДУ вообще связана с какой-либо спецификой беременности КВС
					value: -1
				},
				{
					name: 'PersonRegister_id',
					xtype: 'hidden'
				},
				new sw.Promed.Panel({
					autoHeight: true,
					//hidden: true,
					bodyStyle: 'padding-top: 0.5em;',
					border: true,
					collapsible: true,
					id: 'PDEF_PersonDispPanel',
					layout: 'form',
					listeners: {
						'expand': function(panel) {
							// this.FormPanel.getForm().findField('LpuSection_id').focus(true);
						}.createDelegate(this)
					},
					style: 'margin-bottom: 0.5em;',
					title: 'Контрольная карта',
					items: [
						{
							layout: 'column',
							border: false,
							items: [{
								layout: 'form',
								border: false,
								columnWidth: 1,
								items: [{
									allowBlank: false,
									enableKeyEvents: true,
									fieldLabel: langs('Номер карты'),
									maskRe: /\d/,
									autoCreate: {tag: "input", size:14, maxLength: "9", autocomplete: "off"},
									emptyText: 'введите текст',
									listeners: {
										'keydown': function (inp, e) {
											switch (e.getKey()) {
												case Ext.EventObject.F2:
													e.stopEvent();
													this.getPersonDispNumber();
													break;

												case Ext.EventObject.TAB:
													if (e.shiftKey == true) {
														e.stopEvent();
														this.buttons[this.buttons.length - 1].focus();
													}
													break;

											}
										}.createDelegate(this),
										render: function(field, p, rec) {
											var afterNumCardDiv = document.createElement('div');
											afterNumCardDiv.id = 'afterNumCardDiv';
											afterNumCardDiv.innerText = '* Номер может состоять только из цифр от 0 до 999999999';
											afterNumCardDiv.setAttribute("style", "position: absolute;font-size: 0.8em;top: 2px;left: 340px;color: red; display:none;");
											field.container.dom.appendChild(afterNumCardDiv);
										},
										focus: function(field){
											var afterNumCardDiv = document.getElementById('afterNumCardDiv');
											if(afterNumCardDiv) afterNumCardDiv.style.display = 'block';
										},
										blur: function(field){
											var afterNumCardDiv = document.getElementById('afterNumCardDiv');
											if(afterNumCardDiv) afterNumCardDiv.style.display = 'none';
										}
									},
									name: 'PersonDisp_NumCard',
									onTriggerClick: function () {
										this.getPersonDispNumber();
									}.createDelegate(this),
									tabIndex: 2602,
									triggerClass: 'x-form-plus-trigger',
									validateOnBlur: false,
									width: 150,
									xtype: 'trigger'
								}]
							}, {
								layout: 'form',
								border: false,
								width: 40,
								items: [{
									html: '',
									listeners: {
										'render': function() {
											parentWindow.swEMDPanel = Ext6.create('sw.frames.EMD.swEMDPanel', {
												renderTo: this.getEl(),
												width: 40,
												height: 30
											});

											parentWindow.swEMDPanel.setReadOnly(true);
										}
									},
									xtype: 'label'
								}]
							}]
						},
						{
							allowBlank: false,
							fieldLabel: 'Взят',
							format: 'd.m.Y',
							listeners: {
								'change': function(field, newValue, oldValue) {
									blockedDateAfterPersonDeath('personpanelid', 'PDEF_PersonInformationFrame', field, newValue, oldValue);
									var base_form = parentWindow.FormPanel.getForm();
									var lpu_section_id = base_form.findField('LpuSection_id').getValue();
									var med_staff_fact_id = base_form.findField('MedStaffFact_id').getValue();
									base_form.findField('LpuSection_id').clearValue();
									base_form.findField('MedStaffFact_id').clearValue();
									if (typeof newValue != 'object') {
										base_form.findField('LpuSection_id').disable();
										base_form.findField('MedStaffFact_id').disable();
										return false;
									}

									var Diag_id = base_form.findField('Diag_id').getValue();

									base_form.findField('Diag_id').getStore().load({
										callback: function() {
											base_form.findField('Diag_id').setFilterByDate(newValue);
										},
										params: {
											//where: "where Diag_id = " + Diag_id,
											where: (getRegionNick()=='kz')?("where Diag_id = " + Diag_id):("where DiagLevel_id = 4 and Diag_id = " + Diag_id)
										}
									});

									var lpu_section_filter_params = {
										allowLowLevel: 'yes',
										isPolka: true,
										regionCode: getGlobalOptions().region.number
									};
									var medstafffact_filter_params = {
										allowLowLevel: 'yes',
										isPolka: true,
										regionCode: getGlobalOptions().region.number
									};

									if (getGlobalOptions().allowed_disp_med_staff_fact_group == 2) {
										medstafffact_filter_params.isDoctorOrMidMedPersonal = true;
									}

									medstafffact_filter_params.onEndDate = Ext.util.Format.date(newValue, 'd.m.Y');
									if (this.action == 'add') {
										/*
										// фильтр или на конкретное место работы или на список мест работы
										if (this.UserLpuSection_id && this.UserMedStaffFact_id) {
											lpu_section_filter_params.id = this.UserLpuSection_id;
											medstafffact_filter_params.id = this.UserMedStaffFact_id;
										} else if (typeof this.UserLpuSectionList == 'object' && this.UserLpuSectionList.length > 0 && typeof this.UserMedStaffFactList == 'object' && this.UserMedStaffFactList.length > 0) {
											lpu_section_filter_params.ids = this.UserLpuSectionList;
											medstafffact_filter_params.ids = this.UserMedStaffFactList;
										}
										*/
										// # redmine.swan.perm.ru/issues/133439
										var userLpuSectionList = (typeof this.UserLpuSectionList == 'object' && this.UserLpuSectionList.length > 0) ? this.UserLpuSectionList : getGlobalOptions().lpusection;
										var userMedStaffFactList = (typeof this.UserMedStaffFactList == 'object' && this.UserMedStaffFactList.length > 0) ? this.UserMedStaffFactList : getGlobalOptions().medstafffact;

										var openedFromARM = ( this.openedFromARM && this.openedFromARM.inlist(['common', 'stom']) ) ? true : false;
										if(openedFromARM && isUserGroup('LpuUser')){
											if (this.UserLpuSection_id && this.UserMedStaffFact_id) {
												lpu_section_filter_params.id = this.UserLpuSection_id;
												medstafffact_filter_params.id = this.UserMedStaffFact_id;
											} else if(typeof userLpuSectionList == 'object' && userLpuSectionList.length > 0 && typeof userMedStaffFactList == 'object' && userMedStaffFactList.length > 0){
												lpu_section_filter_params.ids = userLpuSectionList;
												medstafffact_filter_params.ids = userMedStaffFactList;
											}
										}else{
											lpu_section_filter_params.lpu_id = getGlobalOptions().lpu_id;
											medstafffact_filter_params.lpu_id = getGlobalOptions().lpu_id;
										}
									}
									lpu_section_filter_params.isDisp = true;
									medstafffact_filter_params.isDisp = true;
									if (this.action != 'view' && haveArmType('mstat')) {
										base_form.findField('LpuSection_id').enable();
										if (this.action != 'edit') {
											base_form.findField('MedStaffFact_id').enable();
										}
									}
									base_form.findField('LpuSection_id').getStore().removeAll();
									base_form.findField('MedStaffFact_id').getStore().removeAll();
									// загружаем локальные списки отделений и мест работы
									setLpuSectionGlobalStoreFilter(lpu_section_filter_params);
									setMedStaffFactGlobalStoreFilter(medstafffact_filter_params);
									base_form.findField('LpuSection_id').getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));
									base_form.findField('MedStaffFact_id').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));
									if (base_form.findField('LpuSection_id').getStore().getById(lpu_section_id)) {
										base_form.findField('LpuSection_id').setValue(lpu_section_id);
									}
									if (base_form.findField('MedStaffFact_id').getStore().getById(med_staff_fact_id)) {
										base_form.findField('MedStaffFact_id').setValue(med_staff_fact_id);
									}
                                    var that = this;
									var msf = 0;
									if(this.UserMedStaffFact_id){
										msf = this.UserMedStaffFact_id;
									}
									else{
										msf = (sw.Promed.MedStaffFactByUser.last)?sw.Promed.MedStaffFactByUser.last.MedStaffFact_id:0;
									}
									if(msf){
										var index = base_form.findField('MedStaffFact_id').getStore().findBy(function(record, id) {
											//if (record.get('MedPersonal_id') == that.MedPersonal_id)// находит первый попавшися элемент с текущим врачом, что не правильно
											if (record.get('MedStaffFact_id') == msf){// поиск по рабочему месту #142347
												return true;
											} else {
												return false;
											}
										});
										if (index >= 0) {
											//base_form.findField('MedStaffFact_id').setValue(base_form.findField('MedStaffFact_id').getStore().getAt(index).get('MedStaffFact_id'));
											base_form.findField('MedStaffFact_id').setValue(msf);// значение есть, можно его просто установить без getAt(index)
										}
									}
                                    //base_form.findField('MedPersonal_id').setValue(MedPersonal_id);
									/*
									 если форма открыта на редактирование и задано отделение и
									 место работы или задан список мест работы, то не даем редактировать вообще
									 */
									if (this.action == 'edit' && ((this.UserLpuSection_id && this.UserMedStaffFact_id) || (typeof this.UserLpuSectionList == 'object' && this.UserLpuSectionList.length > 0 && typeof this.UserMedStaffFactList == 'object' && this.UserMedStaffFactList.length > 0))) {
										base_form.findField('LpuSection_id').disable();
										base_form.findField('MedStaffFact_id').disable();
									}
									// Если форма открыта на добавление...
									if (this.action == 'add') {
										// ... и задано отделение и место работы...
										if (this.UserLpuSection_id && this.UserMedStaffFact_id) {
											// ... то устанавливаем их и не даем редактировать поля
											base_form.findField('LpuSection_id').disable();
											base_form.findField('MedStaffFact_id').disable();
											base_form.findField('LpuSection_id').setValue(this.UserLpuSection_id);
											base_form.findField('LpuSection_id').fireEvent('change', base_form.findField('LpuSection_id'), this.UserLpuSection_id);
											base_form.findField('MedStaffFact_id').setValue(this.UserMedStaffFact_id);
										}
										// или задан список отделений и мест работы...
										else if (base_form.findField('MedStaffFact_id').getStore().getCount() > 0 && typeof this.UserLpuSectionList == 'object' && this.UserLpuSectionList.length > 0 && typeof this.UserMedStaffFactList == 'object' && this.UserMedStaffFactList.length > 0) {
											if(base_form.findField('MedStaffFact_id').findRecord('MedStaffFact_id', getGlobalOptions().CurMedStaffFact_id)){
												med_staff_fact_id = getGlobalOptions().CurMedStaffFact_id;
											}else{
												var elem = base_form.findField('MedStaffFact_id').findRecord('MedPersonal_id', getGlobalOptions().medpersonal_id);
												if(elem && elem.get('MedStaffFact_id')){
													med_staff_fact_id = elem.get('MedStaffFact_id');
												}else{
													// ... выбираем первое место работы
													med_staff_fact_id = base_form.findField('MedStaffFact_id').getStore().getAt(0).get('MedStaffFact_id');
												}
											}
											//med_staff_fact_id = base_form.findField('MedStaffFact_id').getStore().getAt(0).get('MedStaffFact_id');

											base_form.findField('MedStaffFact_id').setValue(med_staff_fact_id);
											base_form.findField('MedStaffFact_id').fireEvent('change', base_form.findField('MedStaffFact_id'), med_staff_fact_id);
											// Если в списке мест работы всего одна запись...
											if (this.UserMedStaffFactList.length == 1 && !haveArmType('mstat')) {
												// ... закрываем поля для редактирования
												base_form.findField('LpuSection_id').disable();
												base_form.findField('MedStaffFact_id').disable();
											}
										}
										if(this.findById('PDEF_PersonDispHist').getGrid().getStore().data.length == 0 && base_form.findField('PersonDisp_id').getValue() == 0){
											var rec = {PersonDispHist_id:0,MedPersonal_Fio:'',LpuSection_Name:'',PersonDispHist_begDate:newValue,PersonDispHist_endDate:''};
											var msf = base_form.findField('MedStaffFact_id').getValue();
											if(msf && typeof base_form.findField('MedStaffFact_id').getStore().getById(msf) == 'object'){
												rec.MedPersonal_Fio = base_form.findField('MedStaffFact_id').getStore().getById(msf).get('MedPersonal_Fio');
												rec.LpuSection_Name = base_form.findField('MedStaffFact_id').getStore().getById(msf).get('LpuSection_Name');
											}
											this.findById('PDEF_PersonDispHist').getGrid().getStore().loadData([rec]);
											this.findById('PDEF_PersonDispHistPanel').isLoaded = true;
											base_form.findField('PersonDispHist_MedPersonalFio').setValue(rec.MedPersonal_Fio);
											this.disablePersonDispHistActions(true);
										}
									}
									if(base_form.findField('PersonDisp_endDate').getValue()<newValue){
										base_form.findField('PersonDisp_endDate').setValue('');
									}
									base_form.findField('PersonDisp_endDate').minValue = newValue;

								}.createDelegate(this)
							},
							name: 'PersonDisp_begDate',
							plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
							tabIndex: 2602,
							width: 100,
							xtype: 'swdatefield'
						},
						{
							allowBlank: false,
							fieldLabel: 'Отделение',
							hiddenName: 'LpuSection_id',
							id: 'PDEF_LpuSectionCombo',
							linkedElements: [
								'PDEF_MedStaffactCombo'
							],
							listWidth: 650,
							tabIndex: 2603,
							width: 500,
							xtype: 'swlpusectionglobalcombo'
						},
						{
							allowBlank: false,
							fieldLabel: lang['postavivshiy_vrach'],
							hiddenName: 'MedStaffFact_id',
							id: 'PDEF_MedStaffactCombo',
							listWidth: 650,
							parentElementId: 'PDEF_LpuSectionCombo',
							tabIndex: 2604,
							width: 500,
							xtype: 'swmedstafffactglobalcombo'
						},
						{
							xtype: 'textfield',
							name: 'PersonDispHist_MedPersonalFio',
							width: 500,
							fieldLabel: lang['otvetstvenniy_vrach'],
							readOnly: true
						},
                        {
                            layout: 'column',
                            border: false,
                            items: [
                                {
                                    layout: 'form',
                                    border: false,
                                    width: 700,
                                    items: [
                                        {
											checkAccessRights: true,
                                            allowBlank: false,
											forceSelection: true,
											// Добавлено (refs #1014) - закомментировал тут #168516
                                            /*beforeBlur: function() {
                                                return true;
                                            },*/
                                            hiddenName: 'Diag_id',
                                            listeners: {
                                                'change': function(combo, newValue, oldValue) {
                                                    var action = this.action;
                                                    var do_some_things = function (combo, newValue, oldValue, load_started){
                                                        if (!load_started) {
                                                            load_started = false;
                                                        }
                                                        if (0==parentWindow.sicknessDiagStore.data.length) {
                                                            //log('store not loaded, shedulled');
                                                            if (!parentWindow.sicknessDiagStore.autoLoad && !load_started) {
                                                                parentWindow.sicknessDiagStore.load();
                                                                load_started = true;
                                                            }
                                                            setTimeout(function (){
                                                                do_some_things(combo, newValue, oldValue, load_started);
                                                            }, 100);
                                                            return;
                                                        }
                                                        var base_form = parentWindow.FormPanel.getForm();
                                                        var sickness_diag_store = parentWindow.sicknessDiagStore;
                                                        var sickness_id = null;
                                                        var needToShowSicknessPanel = false;
                                                        var needToShowPregnantPanel =false;
                                                        var idx = -1;
                                                        //определяю необходимость показа панели регистр
                                                        //log('newValue', newValue);
                                                        if (newValue != '') {
                                                            //находим диагноз
                                                            //log('sickness_diag_store.data.length', sickness_diag_store.data.length);
                                                            idx = sickness_diag_store.findBy(function(record) {
                                                                //log(record.get('Diag_id'), ' = ', newValue, '?');
                                                                if (record.get('Diag_id') == newValue) {
                                                                    //заодно определяем заболевание
                                                                    sickness_id = record.get('Sickness_id');
                                                                    return true;
                                                                }
                                                            });
                                                            //log('idx', idx);
                                                        }
                                                        //alert(sickness_id.toString());
                                                        //log('sickness_id', sickness_id);
                                                        if ((idx>=0) && (sickness_id != null)) {
                                                            //запись найдена
                                                            //log('запись найдена');
                                                            if (Ext.isEmpty(base_form.findField('Sickness_id').getValue())) {
                                                                base_form.findField('Sickness_id').setValue(sickness_diag_store.getAt(idx).get('Sickness_id'));
                                                            }
                                                            //log('sickness_id', sickness_id);
                                                            switch (sickness_id.toString()){
                                                                //Регистр надо показать для
                                                                case '1'://1 ГЕМОФИЛИЯ
                                                                case '2'://2 ОНКОГЕМАТОЛОГИЯ
                                                                case '3'://3 РАССЕЯННЫЙ СКЛЕРОЗ
                                                                case '4'://4 МУКОВИСЦИДОЗ
                                                                case '5'://5 ГИПОФИЗАРНЫЙ НАНИЗМ
                                                                case '6'://6 БОЛЕЗНЬ ГОШЕ
                                                                case '7'://7 МИЕЛОЛЕЙКОЗ
                                                                case '8'://8 ТРАНСПЛАНТАЦИИ ОРГАНОВ (ТКАНЕЙ)
                                                                    parentWindow.findById('ButtonHistory').disable();
                                                                    parentWindow.findById('ButtonHistory').hide();
                                                                    needToShowSicknessPanel = true;
                                                                    break;
                                                                // беременность надо показать для
                                                                case '9'://9 БЕРЕМЕННОСТЬ И РОДЫ
                                                                    if(action == 'edit'){
                                                                        parentWindow.findById('ButtonHistory').enable();
                                                                        parentWindow.findById('ButtonHistory').show();
                                                                    }
                                                                    needToShowPregnantPanel = true;
                                                                    break;
                                                                default:
                                                                    //todo: вывести сообщение об ошибке о неизвестном диагнозе в sickness_id
                                                                    break;
                                                            }
                                                        } else {
                                                            parentWindow.findById('ButtonHistory').disable();
                                                            parentWindow.findById('ButtonHistory').hide();
                                                            base_form.findField('Sickness_id').clearValue();
                                                        }
                                                        if (needToShowSicknessPanel) {
                                                            parentWindow.findById('PDEF_SicknessPanel').show();
                                                            parentWindow.findById('PDEF_SicknessPanel').doLayout();
                                                        } else {
                                                            parentWindow.findById('PDEF_SicknessPanel').hide();
                                                            parentWindow.findById('PDEF_PersonDispMedicamentGrid').removeAll(true);
                                                        }
                                                        if (needToShowPregnantPanel) {
                                                            parentWindow.findById('PDEF_PregnantPanel').show();
                                                            parentWindow.specifics.init();
                                                            parentWindow.specifics.show(parentWindow.action);
                                                            parentWindow.findById('PDEF_PregnantPanel').doLayout();
                                                            parentWindow.buttons[3].show();
                                                        } else {
                                                            parentWindow.findById('PDEF_PregnantPanel').hide();
                                                            //todo: очистить гриды
                                                        }
                                                        //log('needToShowPregnantPanel', needToShowPregnantPanel);
                                                        //log('<-change');
                                                    };
                                                    do_some_things(combo, newValue, oldValue);
                                                    me.MorbusNephro.onChangeDiag(combo, newValue);
                                                    this.enablePrint030();
                                                }.createDelegate(this),
                                                'select': function(combo, record, index) {
                                                    combo.setRawValue(record.get('Diag_Code') + " " + record.get('Diag_Name'));
                                                    this.enablePrint030();
                                                    // combo.focus(true);
                                                }.createDelegate(this)
                                            },
											onEmptyResults: function () {
												if(getRegionNick().inlist(['vologda'])){
													sw4.showInfoMsg({
														hideDelay: 3000,
														type: 'warning',
														text: 'Внимание!<br>Диагноз не соответствует списку уточненных диагнозов. Необходимо внести данные в сигнальную информацию пациента'
													});
												}
											},
                                            listWidth: 600,
                                            tabIndex: 2605,
                                            width: 500,
                                            xtype: 'swdiagcombo'
                                }
                                ]
                                },
                                {
                                    layout: 'form',
                                    border: false,
                                    name: 'ButtonHistory',
                                    hiddenName: 'ButtonHistory',
                                    id: 'ButtonHistory',
                                    disabled: true,
                                    hidden: true,
                                    items: [
                                        {
                                            xtype: 'button',
                                            //text: 'История диагнозов',
                                            tooltip: 'Открыть историю диагнозов',
                                            iconCls: 'diag-hist16',
                                            handler: function() {
                                                //alert(this.FormPanel.getForm().findField('PersonDisp_id').getValue());
                                                getWnd('swPersonDispDiagHistoryWindow').show({
                                                    PersonDisp_id: this.FormPanel.getForm().findField('PersonDisp_id').getValue()
                                                });
                                            }.createDelegate(this)
                                        }
                                    ]
                                },
                                {
									html: '<div style="text-align:left;padding-left:10px;">Данные пациента уже отправлены в ТФОМС, редактирование не доступно</div>',
									width: 310,
									xtype: 'label',
									id: 'Diag_id_TFOMS_msg',
									hidden: true
								}
                       ]},{
							fieldLabel: 'Дата установления диагноза',
							format: 'd.m.Y',
							allowBlank: false,
							name: 'PersonDisp_DiagDate',
							plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
							tabIndex: 2606,
							width: 100,
							xtype: 'swdatefield'
						},{
							comboSubject: 'DeseaseDispType',
							fieldLabel: 'Диагноз установлен',
							hiddenName: 'DeseaseDispType_id',
							allowBlank: true,
							width: 250,
							xtype: 'swcommonsprcombo'
						},{
							comboSubject: 'DiagDetectType',
							fieldLabel: 'Заболевание выявлено',
							hiddenName: 'DiagDetectType_id',
							allowBlank: getRegionNick() != 'kz',
							width: 250,
							xtype: 'swcommonsprcombo'
						},{
							comboSubject: 'DispGroup',
							fieldLabel: 'Диспансерная группа',
							hiddenName: 'DispGroup_id',
							hidden: getRegionNick() != 'kz',
							hideLabel: getRegionNick() != 'kz',
							allowBlank: getRegionNick() != 'kz',
							width: 250,
							xtype: 'swcommonsprcombo'
						},
						{
							enableKeyEvents: true,
							fieldLabel: 'Снят',
							format: 'd.m.Y',
							listeners: {
								'change': function(inp, newValue, oldValue) {
									var base_form = this.FormPanel.getForm();
									var dispOutTypeCombo=base_form.findField('DispOutType_id');
									dispOutTypeCombo.getStore().filterBy(
											function(record){
											  	return record.get("DispOutType_id")!=8;
											}
									);
									if (typeof newValue == 'object' && newValue !== null) {
										if (this.action != 'view') {
											dispOutTypeCombo.enable();
											dispOutTypeCombo.setAllowBlank(false);											
										}
										this.disablePersonDispHistActions(true);
									} else {
										dispOutTypeCombo.clearValue();
										dispOutTypeCombo.disable();
										dispOutTypeCombo.setAllowBlank(true);
										this.disablePersonDispHistActions(false);
									}
									if(this.action == 'add' && this.findById('PDEF_PersonDispHist').getGrid().getStore().data.length == 1 && base_form.findField('PersonDisp_id').getValue() == 0){
										var rec = this.findById('PDEF_PersonDispHist').getGrid().getStore().getAt(0);
										rec.set('PersonDispHist_endDate',newValue);
										this.disablePersonDispHistActions(true);
									}
								}.createDelegate(this)
							},
							name: 'PersonDisp_endDate',
							plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
							tabIndex: 2607,
							width: 100,
							xtype: 'swdatefield'
						},
						{
							border: false,
							layout: 'form',
							width: 430,

							items: [
								{
									comboSubject: 'DispOutType',
									fieldLabel: 'Причина снятия',
									hiddenName: 'DispOutType_id',
									lastQuery:'',
									listeners: {
										'change': function(combo, newValue, oldValue) {
											var base_form = this.FormPanel.getForm();
											if (this.action == 'view') {
												//this.findById('PDEF_PersonDispButton').disable();
												return false;
											}
											var record = combo.getStore().getById(newValue);
											if (record) {
												switch (Number(record.get('DispOutType_Code'))) {
													case 2:
													case 5:
														//this.findById('PDEF_PersonDispButton').enable();
														break;
													case 4:
														//this.findById('PDEF_PersonDispButton').disable();
														break;
													default:
														//this.findById('PDEF_PersonDispButton').disable();
														break;
												}
											} else {
												//this.findById('PDEF_PersonDispButton').enable();
											}
										}.createDelegate(this)
									},
									tabIndex: 2608,
									width: 250,
									xtype: 'swcommonsprcombo'
								}
							]
						}
					]
				}),
				new sw.Promed.Panel({
					border: true,
					//hidden: true,
					collapsible: true,
					height: 150,
					id: 'PDEF_PersonDispHistPanel',
					isLoaded: false,
					layout: 'border',
					listeners: {
						'expand': function(panel) {
							if (panel.isLoaded === false) {
								panel.isLoaded = true;
								panel.findById('PDEF_PersonDispHist').getGrid().getStore().load({
									params: {
										PersonDisp_id: this.FormPanel.getForm().findField('PersonDisp_id').getValue()
									}
								});
							}
							panel.doLayout();
						}.createDelegate(this)
					},
					style: 'margin-bottom: 0.5em;',
					title: 'История врачей, ответственных за наблюдение',
					items: [ new sw.Promed.ViewFrame({
						useEmptyRecord: false,
						userCls: 'DispHistNotNull',
						autoLoadData: false,
						uniqueId: true,
						id: 'PDEF_PersonDispHist',
						editformclassname: 'swPersonDispHistEditWindow',
						border: false,
						actions: [
							{ name: 'action_add', disabled: !isUserGroup('PersonDispHistEdit'), handler: function() { parentWindow.openPersonDispHistEditWindow('add'); } },
							{ name: 'action_edit', disabled: !isUserGroup('PersonDispHistEdit'), handler: function() { parentWindow.openPersonDispHistEditWindow('edit'); } },
							{ name: 'action_view', handler: function() { parentWindow.openPersonDispHistEditWindow('view'); } },
							{ name: 'action_delete', disabled: !isUserGroup('PersonDispHistEdit'), url:'/?c=PersonDisp&m=deletePersonDispHist', handler: function() { parentWindow.deletePersonDispHist(); } },
							{ name: 'action_refresh' },
							{ name: 'action_print'}
						],
						dataUrl: '/?c=PersonDisp&m=loadPersonDispHistlist',
						region: 'center',
						height: 200,
						toolbar: true,
						stringfields: [
							{ name: 'PersonDispHist_id', type: 'int', header: 'ID', key: true },
							{ name: 'MedPersonal_id', hidden: true},
							{ name: 'LpuSection_id', hidden: true},
							{ name: 'MedPersonal_Fio', type: 'string', header: lang['fio_vracha'], id: 'autoexpand'},
							{ name: 'LpuSection_Name', type: 'string', header: lang['otdelenie'], width: 180 },
							{ name: 'PersonDispHist_begDate', type: 'date', header: lang['nachalo'], width: 100},
							{ name: 'PersonDispHist_endDate', type: 'date', header: lang['okonchanie'], width: 100}
						],
						onLoadData: function(count) {
							if(count){
								var today = new Date();
								today.setHours(0,0,0,0);
								var index = this.findById('PDEF_PersonDispHist').getGrid().getStore().findBy(function(rec){
									return(
										(rec.get('PersonDispHist_begDate') <= today && Ext.isEmpty(rec.get('PersonDispHist_endDate')))
										|| (rec.get('PersonDispHist_begDate') <= today && rec.get('PersonDispHist_endDate') >= today)
									);
								});
								if(index > -1){
									var mp =  this.findById('PDEF_PersonDispHist').getGrid().getStore().getAt(index).get('MedPersonal_Fio');
									this.FormPanel.getForm().findField('PersonDispHist_MedPersonalFio').setValue(mp);
								}
								var base_form = this.FormPanel.getForm();
								var histBegDate = false;
								var histEndDate = false;
								this.findById('PDEF_PersonDispHist').getGrid().getStore().each(function(rec){
									if(!histBegDate || histBegDate < rec.get('PersonDispHist_begDate')){
										histBegDate = rec.get('PersonDispHist_begDate');
										histEndDate = rec.get('PersonDispHist_endDate');
									}
								});
								if(base_form.findField('PersonDisp_endDate').minValue < histBegDate){
									base_form.findField('PersonDisp_endDate').minValue = histBegDate;
								}
							}
							var disableActions = false;
							if(!Ext.isEmpty(this.PersonDispCloseDate) && !Ext.isEmpty(this.FormPanel.getForm().findField('PersonDisp_endDate').getValue())){
								disableActions = true;
							}
							this.disablePersonDispHistActions(disableActions);
						}.createDelegate(this)
					})]
				}),
				new sw.Promed.Panel({
					border: true,
					//hidden: true,
					collapsible: true,
					height: 150,
					id: 'PDEF_DiagPanel',
					isLoaded: false,
					layout: 'border',
					listeners: {
						'expand': function(panel) {
							if (panel.isLoaded === false) {
								panel.isLoaded = true;
								panel.findById('PDEF_PersonDispSopDiag').getGrid().getStore().load({
									params: {
										PersonDisp_id: this.FormPanel.getForm().findField('PersonDisp_id').getValue()
									}
								});
							}
							panel.doLayout();
						}.createDelegate(this)
					},
					style: 'margin-bottom: 0.5em;',
					title: 'Сопутствующие диагнозы',
					items: [ new sw.Promed.ViewFrame({
						useEmptyRecord: false,
						autoLoadData: false,
						uniqueId: true,
						id: 'PDEF_PersonDispSopDiag',
						border: false,
						actions: [
							{ name: 'action_add', handler: function() { parentWindow.openPersonDispSopDiagEditWindow('add'); } },
							{ name: 'action_edit', handler: function() { parentWindow.openPersonDispSopDiagEditWindow('edit'); } },
							{ name: 'action_view', handler: function() { parentWindow.openPersonDispSopDiagEditWindow('view'); } },
							{ name: 'action_delete', handler: function() { parentWindow.deletePersonDispSopDiag(); } },
							{ name: 'action_refresh' },
							{ name: 'action_print'}
						],
						dataUrl: '/?c=PersonDisp&m=loadPersonDispSopDiaglist',
						region: 'center',
						height: 200,
						toolbar: true,
						stringfields: [
							{ name: 'PersonDispSopDiag_id', type: 'int', header: 'ID', key: true },
							{ name: 'Diag_Code', type: 'string', header: 'Код'},
							{ name: 'Diag_Name', type: 'string', header: 'Наименование', id: 'autoexpand' },
							{ name: 'DopDispDiagType_Name', type: 'string', header: 'Характер заболевания', width: 300}
						]
					})]
				}),
				new sw.Promed.Panel({
					border: true,
					//hidden: true,
					collapsible: true,
					height: 150,
					id: 'PDEF_PersonPrivilegePanel',
					isLoaded: false,
					layout: 'border',
					listeners: {
						'expand': function(panel) {
							if (panel.isLoaded === false) {
								panel.isLoaded = true;
								panel.findById('PDEF_PersonPrivilegeGrid').getGrid().getStore().load({
									params: {
										Person_id: this.FormPanel.getForm().findField('Person_id').getValue(),
										Server_id: this.FormPanel.getForm().findField('Server_id').getValue()
									}
								});
							}
							panel.doLayout();
						}.createDelegate(this)
					},
					style: 'margin-bottom: 0.5em;',
					title: 'Льготы',
					items: [ new sw.Promed.ViewFrame({
						actions: [
							{ name: 'action_add', handler: function() {
								this.openPersonPrivilegeEditWindow('add');
							}.createDelegate(this) },
							{ name: 'action_edit', handler: function() {
								this.openPersonPrivilegeEditWindow('edit');
							}.createDelegate(this) },
							{ name: 'action_view', handler: function() {
								this.openPersonPrivilegeEditWindow('view');
							}.createDelegate(this) },
							{ name: 'action_delete', handler: function() {
								this.deletePersonPrivilege();
							}.createDelegate(this) }
						],
						autoLoadData: false,
						border: false,
						dataUrl: C_PRIV_LOAD_LIST,
						height: 130,
						id: 'PDEF_PersonPrivilegeGrid',
						onDblClick: function() {
							if (!this.ViewActions.action_edit.isDisabled()) {
								this.ViewActions.action_edit.execute();
							}
						},
						onEnter: function() {
							if (!this.ViewActions.action_edit.isDisabled()) {
								this.ViewActions.action_edit.execute();
							}
						},
						onLoadData: function() {
							//
						},
						onRowSelect: function(sm, index, record) {
							//
						},
						paging: false,
						region: 'center',
						stringfields: [
							{ name: 'PersonPrivilege_id', type: 'int', header: 'ID', key: true },
							{ name: 'Lpu_id', type: 'int', hidden: true },
							{ name: 'Person_id', type: 'int', hidden: true },
							{ name: 'PersonPrivilege_id', type: 'int', hidden: true },
							{ name: 'Privilege_Refuse', type: 'string', hidden: true },
							{ name: 'Privilege_RefuseNextYear', type: 'string', hidden: true },
							{ name: 'PrivilegeType_id', type: 'int', hidden: true },
							{ name: 'Server_id', type: 'int', hidden: true },
							{ name: 'PrivilegeType_Code', type: 'int', header: 'Код' },
							{ name: 'PrivilegeType_Name', type: 'string', header: 'Категория', id: 'autoexpand' },
							{ name: 'Privilege_begDate', type: 'date', format: 'd.m.Y', header: 'Начало' },
							{ name: 'Privilege_endDate', type: 'date', format: 'd.m.Y', header: 'Окончание' },
							{ name: 'Lpu_Name', type: 'string', header: 'ЛПУ', width: 250 }
						]
					})]
				}),
				new sw.Promed.Panel({
					border: true,
					//hidden: true,
					collapsible: true,
					height: 150,
					id: 'PDEF_PersonDispVizitPanel',
					isLoaded: false,
					layout: 'border',
					listeners: {
						'expand': function(panel) {
							if (panel.isLoaded === false) {
								panel.isLoaded = true;
								panel.findById('PDEF_PersonDispVizitGrid').getGrid().getStore().load({
									params: {
										PersonDisp_id: this.FormPanel.getForm().findField('PersonDisp_id').getValue()
									}
								});
							}
							panel.doLayout();
						}.createDelegate(this)
					},
					style: 'margin-bottom: 0.5em;',
					title: 'Контроль посещений',
					items: [ new sw.Promed.ViewFrame({
						actions: [
							{ name: 'action_add', handler: function() {
								this.openPersonDispVizitEditWindow('add');
							}.createDelegate(this) },
							{ name: 'action_edit', handler: function() {
								this.openPersonDispVizitEditWindow('edit');
							}.createDelegate(this) },
							{ name: 'action_view', handler: function() {
								this.openPersonDispVizitEditWindow('view');
							}.createDelegate(this) },
							{ name: 'action_delete', handler: function() {
								this.deletePersonDispVizit();
							}.createDelegate(this) }
						],
						autoLoadData: false,
						useEmptyRecord: false,
						border: false,
						dataUrl: '/?c=PersonDisp&m=loadPersonDispVizitList',
						height: 130,
						id: 'PDEF_PersonDispVizitGrid',
						paging: false,
						region: 'center',
						onRowSelect: function(sm, index, record) {
							var gridPanel = this;
							var actEdit = gridPanel.getAction('action_edit');
							var actDel = gridPanel.getAction('action_delete');
							var flag = gridPanel.getAction('action_add').isDisabled();
							var isRecord = (record && !Ext.isEmpty(record.get('EvnVizitPL_id')));

							actEdit.setDisabled(isRecord || flag );
							actDel.setDisabled(isRecord || flag );
						},
						onLoadData: function() {
							parentWindow.checkEvnPLDispProfCanBeAdded();
						},
						stringfields: [
							{ name: 'PersonDispVizit_id', type: 'int', header: 'ID', key: true },
							{ name: 'EvnVizitPL_id', type: 'int', header: 'ID', key: true },
							{ name: 'PersonDispVizit_NextDate', type: 'date', format: 'd.m.Y', header: 'Назначено явиться', width: 200},
							{ name: 'PersonDispVizit_NextFactDate',
								header: 'Явился',
								renderer: function(value, p, rec) {
									if(rec.get('EvnVizitPL_id')){
										var form = Ext.getCmp('PersonDispEditForm').getForm();
										var Person_id = form.findField('Person_id').getValue();
										var Server_id = form.findField('Server_id').getValue();
										var EvnVizitPL_id = rec.get('EvnVizitPL_id');
										var openWin = 'getWnd("swEvnVizitPLEditWindow").show({"action":"view",	"formParams":{"EvnVizitPL_id":'+EvnVizitPL_id+',"Person_id":'+Person_id+',"Server_id":'+Server_id+'}})';
										return '<a href="#" title="просмотреть посещение" onclick=\''+openWin+'\'>'+value+'</a>';
									}else if(value){
										return value;
									}else{
										return '';
									}
								},
							},
							{ name: 'PersonDispVizit_IsHomeDN', header: 'ДН на дому', type: 'checkbox', width: 73 }
						]
					})]
				}),
				new sw.Promed.Panel({
					border: true,
					//hidden: true,
					collapsible: true,
					height: 150,
					id: 'PDEF_EvnPLDispProfPanel',
					isLoaded: false,
					layout: 'border',
					listeners: {
						'expand': function(panel) {
							if (panel.isLoaded === false) {
								panel.isLoaded = true;
								panel.findById('PDEF_EvnPLDispProfGrid').getGrid().getStore().load({
									params: {
										Person_id: this.FormPanel.getForm().findField('Person_id').getValue()
									}
								});
							}
							panel.doLayout();
						}.createDelegate(this)
					},
					style: 'margin-bottom: 0.5em;',
					title: 'Профилактические осмотры',
					items: [ new sw.Promed.ViewFrame({
						actions: [
							{ name: 'action_add', handler: function() {
								this.addEvnPLDispProf();
							}.createDelegate(this) },
							{ name: 'action_edit', handler: function() {
								this.openEvnPLDispProfEditWindow('edit');
							}.createDelegate(this) },
							{ name: 'action_view', handler: function() {
								this.openEvnPLDispProfEditWindow('view');
							}.createDelegate(this) },
							{ name: 'action_delete', handler: function() {
								this.deleteEvnPLDispProf();
							}.createDelegate(this) }
						],
						autoLoadData: false,
						useEmptyRecord: false,
						border: false,
						dataUrl: '/?c=EvnPLDispProf&m=loadEvnPLDispProfList',
						height: 130,
						id: 'PDEF_EvnPLDispProfGrid',
						paging: false,
						region: 'center',
						onRowSelect: function(sm, index, record) {
							this.getAction('action_edit').setDisabled(record.get('accessType') != 'edit');
							this.getAction('action_delete').setDisabled(record.get('accessType') != 'edit');
						},
						stringfields: [
							{name: 'EvnPLDispProf_id', type: 'int', header: 'ID', key: true},
							{name: 'EvnPLDispProf_IsKKND', type: 'string', header: langs('Создан из ККДН'), width: 100},
							{name: 'EvnPLDispProf_Year', type: 'int', header: langs('Год'), width: 60},
							{name: 'EvnPLDispProf_setDate', type: 'date', format: 'd.m.Y', header: langs('Дата начала'), width: 120},
							{name: 'EvnPLDispProf_disDate', type: 'date', format: 'd.m.Y', header: langs('Дата окончания'), width: 120},
							{name: 'EvnPLDispProf_IsEndStage', renderer: function(value, p, rec) {
								if (rec.get('EvnPLDispProf_id')) {
									if (value == "2") {
										return "Да";
									} else {
										return "Нет";
									}
								} else {
									return '';
								}
							}, header: langs('Профосмотр закончен')},
							{name: 'accessType', type: 'string', hidden: true},
							{name: 'Person_id', type: 'int', hidden: true},
							{name: 'Server_id', type: 'int', hidden: true},
							{name: 'Lpu_Nick', type: 'string', id: 'autoexpand', header: langs('МО')}
						]
					})]
				}),
				new sw.Promed.Panel({
					border: true,
					//hidden: true,
					collapsible: true,
					height: 250,
					id: 'PDEF_PersonDispTargetRatePanel',
					isLoaded: false,
					layout: 'border',
					listeners: {
						'expand': function(panel) {
							var base_form = this.FormPanel.getForm();
							if (panel.isLoaded === false) {
								if (base_form.findField('PersonDisp_id').getValue() != '0') {
									panel.isLoaded = true;
									panel.findById('PDEF_PersonDispTargetRateGrid').getGrid().getStore().load({
										params: {
											PersonDisp_id: me.FormPanel.getForm().findField('PersonDisp_id').getValue()
										}
									});
								} else {
									panel.collapse();
									this.doSave({
										doNotHide:true,
										callback: function(data){
											panel.expand();
											panel.isLoaded = true;
											panel.findById('PDEF_PersonDispTargetRateGrid').getGrid().getStore().load({
												params: {
													PersonDisp_id: me.FormPanel.getForm().findField('PersonDisp_id').getValue()
												}
											});
										}
									});
								}

							}
							panel.doLayout();
						}.createDelegate(this)
					},
					style: 'margin-bottom: 0.5em;',
					title: 'Целевые показатели',
					items: [new sw.Promed.ViewFrame({
						actions: [
							{ name: 'action_add', disabled: true },
							{ name: 'action_edit', handler: function() {
								this.openPersonDispTargetRateEditWindow('edit');
							}.createDelegate(this) },
							{ name: 'action_view', handler: function() {
								this.openPersonDispTargetRateEditWindow('view');
							}.createDelegate(this) },
							{ name: 'action_delete', disabled: true }
						],
						autoLoadData: false,
						useEmptyRecord: false,
						border: false,
						dataUrl: '/?c=PersonDisp&m=loadPersonDispTargetRateList',
						height: 230,
						id: 'PDEF_PersonDispTargetRateGrid',
						paging: false,
						region: 'center',
						stringfields: [
							{ name: 'RateType_id', type: 'int', header: 'ID', key: true },
							{ name: 'RateType_Name', type: 'string', header: 'Показатель', width: 300 },
							{ name: 'TargetRate_Value', type: 'float', header: 'Целевое значение', width: 200 },
							{ name: 'FactRate_Value', type: 'float', header: 'Фактическое значение', width: 200 },
							{ name: 'FactRate_setDT', type: 'date', format: 'd.m.Y', header: 'Дата результата', id: 'autoexpand' }
						]
					})]
				}),
				new sw.Promed.Panel({
					//hidden: true,
					bodyStyle: 'padding-top: 0.5em;',
					collapsible: true,
					collapsed: false,
					id: 'PDEF_SicknessPanel',
					layout: 'form',
					listeners: {
						'expand': function() {
							return false;
						}
					},
					style: 'margin-bottom: 0.5em;',
					title: 'Регистр по заболеваниям',
					items: [
						{
							codeField: 'Sickness_Code',
							displayField: 'Sickness_Name',
							disabled: true,
							editable: true,
							fieldLabel: 'Заболевание',
							hiddenName: 'Sickness_id',
							store: new Ext.db.AdapterStore({
								autoLoad: true,
								dbFile: 'Promed.db',
								fields: [
									{ name: 'Sickness_id', type: 'int' },
									{ name: 'PrivilegeType_id', type: 'int' },
									{ name: 'Sickness_Code', type: 'int' },
									{ name: 'Sickness_Name', type: 'string' }
								],
								sortInfo: {
									field: 'Sickness_Code'
								},
								tableName: 'Sickness'
							}),
							tabIndex: 2612,
							tpl: '<tpl for="."><div class="x-combo-list-item">' + '<font color="red">{Sickness_Code}</font>&nbsp;{Sickness_Name}' + '</div></tpl>',
							valueField: 'Sickness_id',
							width: 500,
							xtype: 'swbaselocalcombo'
						},
						new sw.Promed.ViewFrame({
							actions: [
								{ name: 'action_add', handler: function() {
									this.openPersonDispMedicamentEditWindow('add');
								}.createDelegate(this) },
								{ name: 'action_edit', handler: function() {
									this.openPersonDispMedicamentEditWindow('edit');
								}.createDelegate(this) },
								{ name: 'action_view', handler: function() {
									this.openPersonDispMedicamentEditWindow('view');
								}.createDelegate(this) },
								{ name: 'action_delete', handler: function() {
									this.deletePersonDispMedicament();
								}.createDelegate(this) },
								{ name: 'action_refresh', disabled: true },
								{ name: 'action_print' }
							],
							autoLoadData: false,
							border: false,
							dataUrl: '/?c=PersonDisp&m=getPersonDispMedicamentList',
							id: 'PDEF_PersonDispMedicamentGrid',
							region: 'center',
							stringfields: [
								{ name: 'PersonDispMedicament_id', type: 'int', header: 'ID', key: true },
								{ name: 'PersonDisp_id', type: 'int', hidden: true },
								{ name: 'Drug_id', type: 'int', hidden: true },
								{ name: 'DrugMnn_id', type: 'int', hidden: true },
								{ name: 'Drug_Name', type: 'string', header: 'Медикамент', id: 'autoexpand' },
								{ name: 'Drug_Price', type: 'string', header: 'Цена' },
								{ name: 'Drug_Count', type: 'string', header: 'Мес. курс' },
								{ name: 'PersonDispMedicament_begDate', type: 'date', header: 'Дата начала' },
								{ name: 'PersonDispMedicament_endDate', type: 'date', header: 'Дата оконч' }
							],
							title: 'Назначенные медикаменты',
							toolbar: true
						})
					]
				}),
				this.specifics.panel,
				this.MorbusNephro.panel/*,
				this.MorbusHepatitisSpec*/
			]
		});
		this.PersonInfo = new sw.Promed.PersonInfoPanel({
			button1OnHide: function() {
				if (this.action == 'view') {
					this.buttons[this.buttons.length - 1].focus();
				} else {
					this.FormPanel.getForm().findField('PersonDisp_NumCard').focus(true);
				}
			}.createDelegate(this),
			button2Callback: function(callback_data) {
				this.FormPanel.getForm().findField('Server_id').setValue(callback_data.Server_id);
				this.PersonInfo.load({ Person_id: callback_data.Person_id, Server_id: callback_data.Server_id });
			}.createDelegate(this),
			button2OnHide: function() {
				this.PersonInfo.button1OnHide();
			}.createDelegate(this),
			button3OnHide: function() {
				this.PersonInfo.button1OnHide();
			}.createDelegate(this),
			button4OnHide: function() {
				this.PersonInfo.button1OnHide();
			}.createDelegate(this),
			button5OnHide: function() {
				this.PersonInfo.button1OnHide();
			}.createDelegate(this),
			collapsible: true,
			collapsed: true,
			floatable: false,
			id: 'PDEF_PersonInformationFrame',
			plugins: [ Ext.ux.PanelCollapsedTitle ],
			region: 'north',
			title: '<div>Загрузка...</div>',
			titleCollapse: true
		});
		Ext.apply(this, {
			buttons: [
				{
					handler: function() {
						this.doSave();
					}.createDelegate(this),
					iconCls: 'save16',
					onShiftTabAction: function () {
						var base_form = this.FormPanel.getForm();
					}.createDelegate(this),
					onTabAction: function () {
						// this.buttons[1].focus(true);
						this.buttons[this.buttons.length - 1].focus(true);
					}.createDelegate(this),
					tabIndex: 12613,//todo
					text: BTN_FRMSAVE
				},
				{
					handler: function() {
						this.doSave({
							withSign: true
						});
					}.createDelegate(this),
					hidden: !getGlobalOptions().hasEMDCertificate || getRegionNick() == 'kz',
					iconCls: 'save16',
					tabIndex: 12614,//todo
					text: langs('Сохранить и подписать')
				},
				{
					handler: function() {
					if(this.action=='view'){ //Если открыли на просмотр, то печатаем карту
						var paramPersonDispBirth = this.FormPanel.getForm().findField('PersonDisp_id').getValue();
						printBirt({
							'Report_FileName': 'han_ParturientCard.rptdesign',
							'Report_Params': '&paramPersonDispBirth=' + paramPersonDispBirth,
							'Report_Format': 'pdf'
						});
					}
					else{ //Иначе (если добавление/изменение) - сохраняем карту с параметром printParturientCard
						this.doSave({
							printParturientCard: true
						});
					}
					}.createDelegate(this),
					iconCls: 'print16',
					hidden: false,
					tabIndex: 12615,
					text: 'Печать формы 111/у Индивидуальная карта беременной и родильницы'// <-https://redmine.swan.perm.ru/issues/18965
				},
                {
                    handler: function() {
                        if(this.action == 'view'){
                            var paramPersonDisp = this.FormPanel.getForm().findField('PersonDisp_id').getValue();
							printBirt({
								'Report_FileName': 'PersonDispCard.rptdesign',
								'Report_Params': '&paramPersonDisp=' + paramPersonDisp,
								'Report_Format': 'pdf'
							});
                        }
                        else{
                            this.doSave({
                                printPersonDispCard: true
                            });
                        }
                    }.createDelegate(this),
                    iconCls: 'print16',
                    hidden: false,
                    tabIndex: 12614,
                    text: 'Печать Контрольной карты дисп. наблюдения'
                },
                {
                    handler: function() {
                        if(this.action == 'view'){
                            var paramPersonDisp = this.FormPanel.getForm().findField('PersonDisp_id').getValue();
							this.print030(paramPersonDisp);
                        }
                        else{
                            this.doSave({
                                print030Card: true
                            });
                        }
                    }.createDelegate(this),
                    iconCls: 'print16',
                    hidden: false,
                    tabIndex: 12614,
                    text: 'Печать формы №030-4/у'
                },/*
				{
					handler: function() {
						this.printPersonDisp();
					}.createDelegate(this),
					iconCls: 'print16',
					onShiftTabAction: function () {
						if (this.action != 'view') {
							this.buttons[0].focus();
						} else {
							this.buttons[this.buttons.length - 1].focus(true);
						}
					}.createDelegate(this),
					onTabAction: function () {
						this.buttons[this.buttons.length - 1].focus(true);
					}.createDelegate(this),
					tabIndex: 12614,//todo
					text: BTN_FRMPRINT
				},*/
				'-',
				HelpButton(this, -1),
				{
					handler: function() {
						this.hide();
					}.createDelegate(this),
					iconCls: 'cancel16',
					onShiftTabAction: function () {
						// this.buttons[1].focus(true);
						this.buttons[0].focus(true);
					}.createDelegate(this),
					onTabAction: function () {
						if (this.action != 'view') {
							this.FormPanel.getForm().findField('PersonDisp_NumCard').focus(true);
						} else {
							// this.buttons[1].focus(true);
						}
					}.createDelegate(this),
					tabIndex: 12615,//todo
					text: BTN_FRMCANCEL
				}
			],
			items: [
				this.PersonInfo,
				this.FormPanel
			],
			layout: 'border'
		});
		sw.Promed.swPersonDispEditWindow.superclass.initComponent.apply(this, arguments);
	},
	layout: 'border',
	listeners:	{
		'hide':	function() {
			this.onHide();
		},
		'maximize':	function(win) {
			win.findById('PDEF_PersonDispPanel').doLayout();
			win.findById('PDEF_PersonPrivilegePanel').doLayout();
			win.findById('PDEF_PersonDispTargetRatePanel').doLayout();
			win.findById('PDEF_PersonDispVizitPanel').doLayout();
			win.findById('PDEF_EvnPLDispProfPanel').doLayout();
			win.findById('PDEF_PersonDispHistPanel').doLayout();
			win.findById('PDEF_DiagPanel').doLayout();
			win.findById('PDEF_PregnantPanel').doLayout();
			win.findById('PDEF_SicknessPanel').doLayout();
            if (!win.MorbusNephro.panel.hidden) {
                win.MorbusNephro.panel.doLayout();
            }
/*
			if (!win.MorbusHepatitisSpec.hidden) {
				win.MorbusHepatitisSpec.doLayout();
			}
*/
		},
		'restore': function(win) {
			win.fireEvent('maximize', win);
		}
	},
	maximizable: true,
	minHeight: 550,
	minWidth: 700,
	modal: true,
	onHide: Ext.emptyFn,
	openPersonDispTargetRateEditWindow: function(action) {
		if (typeof action != 'string' || !(action.toString().inlist([ 'edit', 'view' ]))) {
			return false;
		}
		if (getWnd('swPersonDispTargetRateEditWindow').isVisible()) {
			sw.swMsg.alert('Сообщение', 'Окно редактирования целевых показателей уже открыто');
			return false;
		}
		var win = this;
		var base_form = this.FormPanel.getForm();
		var grid = this.findById('PDEF_PersonDispTargetRateGrid').getGrid();
		var selected_record = grid.getSelectionModel().getSelected();
		if (!selected_record || !selected_record.get('RateType_id')) {
			return false;
		}
		var params = {
			action: action,
			PersonDisp_id: base_form.findField('PersonDisp_id').getValue(),
			RateType_id: selected_record.get('RateType_id'),
			callback: function(data) {
				grid.getStore().reload();
			}
		};
		getWnd('swPersonDispTargetRateEditWindow').show(params);
	},
	openPersonDispVizitEditWindow: function(action) {
		if (typeof action != 'string' || !(action.toString().inlist([ 'add', 'edit', 'view' ]))) {
			return false;
		}
		if (getWnd('swPersonDispVizitEditWindow').isVisible()) {
			sw.swMsg.alert('Сообщение', 'Окно редактирования контроля посещений уже открыто');
			return false;
		}
		if (this.action == 'view') {
			if (action == 'add') {
				return false;
			} else if (action == 'edit') {
				action = 'view';
			}
		}
		var win = this;
		var base_form = this.FormPanel.getForm();
		var grid = this.findById('PDEF_PersonDispVizitGrid').getGrid();
		var params = {
			action: action,
			callback: function(data) {
				grid.getStore().load({
					params: {PersonDisp_id: win.FormPanel.getForm().findField('PersonDisp_id').getValue()}
				});
			}
		};
		if (action != 'add') {
			var selected_record = grid.getSelectionModel().getSelected();
			if (!selected_record || !selected_record.get('PersonDispVizit_id')) {
				return false;
			}
			params.PersonDispVizit_id = selected_record.get('PersonDispVizit_id');
			getWnd('swPersonDispVizitEditWindow').show(params);
		} else {
			if (base_form.findField('PersonDisp_id').getValue() != '0') {
				params.PersonDisp_id = base_form.findField('PersonDisp_id').getValue();
				getWnd('swPersonDispVizitEditWindow').show(params);
			} else {
				win.doSave({
					doNotHide:true,
					callback: function(data){
						params.PersonDisp_id = base_form.findField('PersonDisp_id').getValue();
						getWnd('swPersonDispVizitEditWindow').show(params);
					}
				});
			}
		}
	},
	deletePersonDispVizit: function() {
		if (this.action == 'view') {
			return false;
		}
		var win = this;
		var grid = this.findById('PDEF_PersonDispVizitGrid').getGrid();
		var row = grid.getSelectionModel().getSelected();
		if (!row) {
			return false;
		}
		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function (buttonId) {
				if (buttonId == 'yes') {
					Ext.Ajax.request({
						callback: function() {
							grid.getStore().remove(row);
							win.checkEvnPLDispProfCanBeAdded();
						},
						params: {
							PersonDispVizit_id: row.get('PersonDispVizit_id')
						},
						url: '/?c=PersonDisp&m=delPersonDispVizit'
					});
				}
			},
			msg: 'Вы действительно желаете удалить эту запись?',
			title: 'Подтверждение удаления'
		});
	},
	checkEvnPLDispProfCanBeAdded: function() {
		// в текущем году у пациента не было ни одной явки на осмотр в рамках диспансерного наблюдения (см.раздел Контроль посещений, колонка «Явился».)
		// МО пользователя является МО прикрепления пациента по основному типу прикрепления.
		var hasFactDate = false,
			curYear = getGlobalOptions().date.substr(6);
		this.findById('PDEF_PersonDispVizitGrid').getGrid().getStore().each(function(rec){
			var year = rec.get('PersonDispVizit_NextFactDate');
			if (!Ext.isEmpty(year) && year.substr(6) == curYear) {
				hasFactDate = true;
			}
		});

		if (!hasFactDate && this.findById('PDEF_PersonInformationFrame').getFieldValue('Lpu_id') == getGlobalOptions().lpu_id) {
			this.findById('PDEF_EvnPLDispProfGrid').setActionDisabled('action_add', false);
		} else {
			this.findById('PDEF_EvnPLDispProfGrid').setActionDisabled('action_add', true);
		}
	},
	addEvnPLDispProf: function() {
		var win = this;
		var EvnPLDispProf_grid = win.findById('PDEF_EvnPLDispProfGrid').getGrid();

		var base_form = this.FormPanel.getForm();
		var Person_id = base_form.findField('Person_id').getValue();
		var Server_id = base_form.findField('Server_id').getValue();

		win.getLoadMask('Проверка наличия у пациента случая ДВН или ПОВН в текущем году').show();
		Ext.Ajax.request({
			url: '/?c=EvnPLDispProf&m=checkBeforeAddEvnPLDisp',
			params: {
				Person_id: Person_id
			},
			callback: function(options, success, response) {
				win.getLoadMask().hide();

				if ( success ) {
					var response_obj = Ext.util.JSON.decode(response.responseText);
					if (response_obj.Error_Msg) {
						sw.swMsg.alert(langs('Ошибка'), response_obj.Error_Msg);
						return false;
					}

					getWnd('swEvnPLDispProfEditWindow').show({
						action: 'add',
						DispClass_id: 5,
						EvnPLDispProf_id: null,
						parentForm: 'swPersonDispEditWindow',
						onHide: Ext.emptyFn,
						callback: function() {
							EvnPLDispProf_grid.getStore().reload();
						},
						Person_id: Person_id,
						Server_id: Server_id
					});
				}
			}
		});
	},
	openEvnPLDispProfEditWindow: function(action) {
		var win = this;
		var EvnPLDispProf_grid = win.findById('PDEF_EvnPLDispProfGrid').getGrid();

		if (getWnd('swEvnPLDispProfEditWindow').isVisible())
		{
			Ext.Msg.alert(langs('Сообщение'), langs('Окно редактирования профосмотра уже открыто'));
			return false;
		}

		var record = EvnPLDispProf_grid.getSelectionModel().getSelected();
		if (!record)
		{
			return false;
		}

		var EvnPLDispProf_id = record.get('EvnPLDispProf_id');
		var person_id = record.get('Person_id');
		var server_id = record.get('Server_id');

		if (EvnPLDispProf_id > 0) {
			var params = {
				action: action,
				DispClass_id: 5,
				EvnPLDispProf_id: EvnPLDispProf_id,
				parentForm: 'swPersonDispEditWindow',
				onHide: Ext.emptyFn,
				callback: function() {
					EvnPLDispProf_grid.getStore().reload();
				},
				Person_id: person_id,
				Server_id: server_id
			};
			getWnd('swEvnPLDispProfEditWindow').show(params);
		}
	},
	doDeleteEvnPLDD: function(options) {
		if (typeof options != 'object') {
			options = new Object();
		}

		var win = this;
		var grid = win.findById('PDEF_EvnPLDispProfGrid').getGrid();

		if ( !grid || !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('EvnPLDispProf_id') ) {
			return false;
		}

		var record = grid.getSelectionModel().getSelected();
		var evn_pl_dd_id = record.get('EvnPLDispProf_id');

		var params = {
			EvnPLDispProf_id: evn_pl_dd_id
		};

		if (options.ignoreCheckRegistry) {
			params.ignoreCheckRegistry = 1;
		}

		win.getLoadMask('Удаление случая ПОВН').show();
		Ext.Ajax.request({
			callback: function(options, success, response) {
				win.getLoadMask().hide();

				if ( success ) {
					var response_obj = Ext.util.JSON.decode(response.responseText);

					if ( response_obj.success == false ) {
						sw.swMsg.alert(langs('Ошибка'), response_obj.Error_Msg ? response_obj.Error_Msg : langs('Ошибка при удалении случая ПОВН'));
					}
					else if (response_obj.Alert_Msg) {
						sw.swMsg.show({
							icon: Ext.MessageBox.QUESTION,
							msg: response_obj.Alert_Msg + ' Продолжить?',
							title: langs('Подтверждение'),
							buttons: Ext.Msg.YESNO,
							fn: function(buttonId, text, obj) {
								if ('yes' == buttonId) {
									options.ignoreCheckRegistry = true;
									win.doDeleteEvnPLDD(options);
								}
							}
						});
					}
					else {
						grid.getStore().remove(record);

						if ( grid.getStore().getCount() == 0 ) {
							LoadEmptyRow(grid);
						}
					}

					grid.getView().focusRow(0);
					grid.getSelectionModel().selectFirstRow();
				}
				else {
					sw.swMsg.alert(langs('Ошибка'), langs('При удалении случая ПОВН возникли ошибки'));
				}
			},
			params: params,
			url: '/?c=EvnPLDispProf&m=deleteEvnPLDispProf'
		});
	},
	deleteEvnPLDispProf: function() {
		var win = this;
		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId, text, obj) {
				if ( buttonId == 'yes' ) {
					win.doDeleteEvnPLDD();
				}
			},
			icon: Ext.MessageBox.QUESTION,
			msg: langs('Удалить случай ПОВН?'),
			title: langs('Вопрос')
		});
	},
	openPersonDispSopDiagEditWindow: function(action) {
		if (typeof action != 'string' || !(action.toString().inlist([ 'add', 'edit', 'view' ]))) {
			return false;
		}
		if (getWnd('swPersonDispSopDiagEditWindow').isVisible()) {
			sw.swMsg.alert('Сообщение', 'Окно редактирования сопутствующих диагнозов уже открыто');
			return false;
		}
		if (this.action == 'view') {
			if (action == 'add') {
				return false;
			} else if (action == 'edit') {
				action = 'view';
			}
		}
		var win = this;
		var base_form = this.FormPanel.getForm();
		var grid = this.findById('PDEF_PersonDispSopDiag').getGrid();
		var params = {
			action: action,
			callback: function(data) {
				grid.getStore().load({
					params: {PersonDisp_id: win.FormPanel.getForm().findField('PersonDisp_id').getValue()}
				});
			}
		};
		if (action != 'add') {
			var selected_record = grid.getSelectionModel().getSelected();
			if (!selected_record || !selected_record.get('PersonDispSopDiag_id')) {
				return false;
			}
			params.PersonDispSopDiag_id = selected_record.get('PersonDispSopDiag_id');
			getWnd('swPersonDispSopDiagEditWindow').show(params);
		} else {
			if (base_form.findField('PersonDisp_id').getValue() != '0') {
				params.PersonDisp_id = base_form.findField('PersonDisp_id').getValue();
				getWnd('swPersonDispSopDiagEditWindow').show(params);
			} else {
				win.doSave({
					doNotHide:true,
					callback: function(data){
						params.PersonDisp_id = base_form.findField('PersonDisp_id').getValue();
						getWnd('swPersonDispSopDiagEditWindow').show(params);
					}
				});
			}
		}
	},
	deletePersonDispSopDiag: function() {
		if (this.action == 'view') {
			return false;
		}
		var grid = this.findById('PDEF_PersonDispSopDiag').getGrid();
		var row = grid.getSelectionModel().getSelected();
		if (!row) {
			return false;
		}
		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function (buttonId) {
				if (buttonId == 'yes') {
					Ext.Ajax.request({
						callback: function() {
							grid.getStore().remove(row);
						},
						params: {
							PersonDispSopDiag_id: row.get('PersonDispSopDiag_id')
						},
						url: '/?c=PersonDisp&m=delPersonDispSopDiag'
					});
				}
			},
			msg: 'Вы действительно желаете удалить эту запись?',
			title: 'Подтверждение удаления'
		});
	},
	openPersonDispHistEditWindow: function(action) {
		if (typeof action != 'string' || !(action.toString().inlist([ 'add', 'edit', 'view' ]))) {
			return false;
		}
		if (getWnd('swPersonDispHistEditWindow').isVisible()) {
			sw.swMsg.alert('Сообщение', 'Окно редактирования ответственного врача уже открыто');
			return false;
		}
		if (this.action == 'view') {
			if (action == 'add') {
				return false;
			} else if (action == 'edit') {
				action = 'view';
			}
		}
		var win = this;
		var base_form = this.FormPanel.getForm();
		var grid = this.findById('PDEF_PersonDispHist').getGrid();
		var params = {
			action: action,
			callback: function(data) {
				grid.getStore().load({
					params: {PersonDisp_id: win.FormPanel.getForm().findField('PersonDisp_id').getValue()}
				});
			},
			PersonDisp_begDate: win.FormPanel.getForm().findField('PersonDisp_begDate').getValue(),
			PersonDisp_endDate: win.FormPanel.getForm().findField('PersonDisp_endDate').getValue()
		};
		if (action != 'add') {
			var selected_record = grid.getSelectionModel().getSelected();
			if (!selected_record || !selected_record.get('PersonDispHist_id') || selected_record.get('PersonDispHist_id') === 0) {
				sw.swMsg.alert('Сообщение', 'Редактирование/просмотр доступны только после сохранения карты.');
				return false;
			}
			if (base_form.findField('PersonDisp_id').getValue() != '0') {
				params.PersonDispHist_id = selected_record.get('PersonDispHist_id');
				getWnd('swPersonDispHistEditWindow').show(params);
			} else {
				win.doSave({
					doNotHide:true,
					callback: function(data){
						grid.getStore().load({
							params: {PersonDisp_id: win.FormPanel.getForm().findField('PersonDisp_id').getValue()},
							callback: function(){
								if(grid.getStore().data.length > 0){
									params.PersonDispHist_id = grid.getStore().getAt(0).get('PersonDispHist_id');
									getWnd('swPersonDispHistEditWindow').show(params);
								}
							}
						});
					}
				});
			}
		} else {
			if (base_form.findField('PersonDisp_id').getValue() != '0') {
				params.PersonDisp_id = base_form.findField('PersonDisp_id').getValue();
				getWnd('swPersonDispHistEditWindow').show(params);
			} else {
				win.doSave({
					doNotHide:true,
					callback: function(data){
						params.PersonDisp_id = base_form.findField('PersonDisp_id').getValue();
						getWnd('swPersonDispHistEditWindow').show(params);
					}
				});
			}
		}
	},
	deletePersonDispHist: function() {
		if (this.action == 'view') {
			return false;
		}
		var grid = this.findById('PDEF_PersonDispHist').getGrid();
		var row = grid.getSelectionModel().getSelected();
		if (!row || row.get('PersonDispHist_id') === 0) {
			return false;
		}
		if(grid.getStore().getCount()==1) {
			sw.swMsg.alert(langs('Ошибка'), langs('Список врачей, ответственных за наблюдение, не может быть пуст'));
			return false;
		}
		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function (buttonId) {
				if (buttonId == 'yes') {
					Ext.Ajax.request({
						callback: function() {
							grid.getStore().remove(row);
						},
						params: {
							PersonDispHist_id: row.get('PersonDispHist_id')
						},
						url: '/?c=PersonDisp&m=deletePersonDispHist'
					});
				}
			},
			msg: 'Вы действительно желаете удалить эту запись?',
			title: 'Подтверждение удаления'
		});
	},
	openPersonDispMedicamentEditWindow: function(action) {
		if (typeof action != 'string' || !(action.toString().inlist([ 'add', 'edit', 'view' ]))) {
			return false;
		}
		if (getWnd('swPersonDispDrugAddWindow').isVisible()) {
			sw.swMsg.alert('Сообщение', 'Окно редактирования назначаемого медикамента уже открыто');
			return false;
		}
		if (this.action == 'view') {
			if (action == 'add') {
				return false;
			} else if (action == 'edit') {
				action = 'view';
			}
		}
		var base_form = this.FormPanel.getForm();
		var grid = this.findById('PDEF_PersonDispMedicamentGrid').getGrid();
		var params = new Object();
		var idx;
		var person_disp_beg_date = base_form.findField('PersonDisp_begDate').getValue();
		var person_disp_id = base_form.findField('PersonDisp_id').getValue();
		var privilege_type_id;
		var record;
		var sickness_id = base_form.findField('Sickness_id').getValue();
		if (!sickness_id) {
			return false;
		}
		idx = base_form.findField('Sickness_id').getStore().findBy(function(rec) {
			if (rec.get('Sickness_id') == sickness_id) {
				return true;
			} else {
				return false;
			}
		});
		if (idx >= 0) {
			privilege_type_id = base_form.findField('Sickness_id').getStore().getAt(idx).get('PrivilegeType_id');
		}
		params.callback = function(data) {
			// если медикамент был сохранен формой редактирования медикамента, то просто обновляем грид
			if (data.medicamentWasSaved) {
				grid.getStore().load({
					params: {
						PersonDisp_id: person_disp_id
					}
				});
				return;
			}
			if (grid.getStore().getCount() == 1) {
				var row = grid.getStore().getAt(0);
				if (!row.get('Drug_id')) {
					grid.getStore().removeAll();
				}
			}
			idx = grid.getStore().findBy(function(rec) {
				if (rec.get('PersonDispMedicament_id') == data.PersonDispMedicament_id) {
					return true;
				} else {
					return false;
				}
			});
			if (idx >= 0) {
				record = grid.getStore().getAt(idx);
				record.set('PersonDispMedicament_id', data.PersonDispMedicament_id);
				record.set('PersonDisp_id', 0);
				record.set('DrugMnn_id', data.DrugMnn_id);
				record.set('Drug_id', data.Drug_id);
				record.set('Drug_Name', data.Drug_Name);
				record.set('Drug_Price', data.DrugPrice);
				record.set('Drug_Count', data.Course);
				record.set('PersonDispMedicament_begDate', data.Course_begDate);
				record.set('PersonDispMedicament_endDate', data.Course_endDate);
				record.commit();
			}
		};
		params.onHide = function() {
			if (grid.getStore().getCount() > 0) {
				grid.getView().focusRow(0);
				grid.getSelectionModel().selectFirstRow();
			}
		};
		params.params = {
			action: action,
			PrivilegeType_id: privilege_type_id,
			PersonDisp_id: person_disp_id,
			PersonDisp_begDate: person_disp_beg_date
		};
		if (action == 'add') {
		} else {
			var selected_record = grid.getSelectionModel().getSelected();
			if (!selected_record || !selected_record.get('Drug_id')) {
				return false;
			}
			params.params.medicament_data = selected_record.data;
		}
		getWnd('swPersonDispDrugAddWindow').show(params);
	},
	openPersonPrivilegeEditWindow: function(action) {
		if (typeof action != 'string' || !(action.toString().inlist([ 'add', 'edit', 'view' ]))) {
			return false;
		}
		if (getWnd('swPrivilegeEditWindow').isVisible()) {
			sw.swMsg.alert('Сообщение', 'Окно редактирования льготы уже открыто');
			return false;
		}
		if (this.action == 'view') {
			if (action == 'add') {
				return false;
			} else if (action == 'edit') {
				action = 'view';
			}
		}
		var base_form = this.FormPanel.getForm();
		var grid = this.findById('PDEF_PersonPrivilegeGrid').getGrid();
		var params = new Object();
		params.action = action;
		params.callback = function(data) {
			grid.getStore().reload();
		};
		params.onHide = function() {
			if (grid.getSelectionModel().getSelected()) {
				grid.getView().focusRow(grid.getStore().indexOf(grid.getSelectionModel().getSelected()));
			} else {
				grid.getView().focusRow(0);
				grid.getSelectionModel().selectFirstRow();
			}
		};
		if (action == 'add') {
			params.Person_id = base_form.findField('Person_id').getValue();
			params.Server_id = base_form.findField('Person_id').getValue();
			getWnd('swPrivilegeEditWindow').show(params);
		} else {
			var selected_record = grid.getSelectionModel().getSelected();
			if (!selected_record || !selected_record.get('PersonPrivilege_id')) {
				return false;
			}
			var lpu_id = selected_record.get('Lpu_id');
			var person_id = selected_record.get('Person_id');
			var person_privilege_id = selected_record.get('PersonPrivilege_id');
			var privilege_end_date = selected_record.get('Privilege_endDate');
			var privilege_type_code = selected_record.get('PrivilegeType_Code');
			var server_id = selected_record.get('Server_id');
			if (lpu_id != Ext.globalOptions.globals.lpu_id) {
				params.action = 'view';
			}
			if (person_id && person_privilege_id && server_id >= 0) {
				params.Person_id = person_id;
				params.PersonPrivilege_id = person_privilege_id;
				params.Server_id = server_id;
				getWnd('swPrivilegeEditWindow').show(params);
			}
		}
	},
	params: null,
	plain: true,
	printPersonDisp: function() {
		var base_form = this.FormPanel.getForm();

		if ( !base_form.findField('PersonDisp_id').getValue() ) {
			return false;
		}

		window.open('/?c=PersonDisp&m=printPersonDisp&PersonDisp_id=' + base_form.findField('PersonDisp_id').getValue(), '_blank');
	},
	resizable: true,
	availabilityDispensaryCardCauseDeath: function(){
		//наличие диспансерной карты с причиной снятия Смерть
		var base_form = this.FormPanel.getForm();
		var Person_id = base_form.findField('Person_id').getValue();
		if(Person_id){
			Ext.Ajax.request({
				failure : function() {
					console.warn('Error getAvailabilityDispensaryCardCauseDeath');
				},
				callback: function(options, success, response) {
					if ( success ) {
						var result = Ext.util.JSON.decode(response.responseText);
						this.availabilityCardCauseDeath = (result.data) ? result.data : 0;
					}
				}.bind(this),
				params: {
					Person_id: Person_id
				},
				url: '/?c=PersonDisp&m=getAvailabilityDispensaryCardCauseDeath'
			});
		}
	},
	show: function() {
		sw.Promed.swPersonDispEditWindow.superclass.show.apply(this, arguments);
		this.restore();
		this.center();
		this.maximize();
		this.findById('PDEF_PersonDispPanel').expand();
		this.findById('PDEF_PersonPrivilegePanel').collapse();

		this.findById('PDEF_PersonDispTargetRatePanel').collapse();
		this.findById('PDEF_PersonDispVizitPanel').collapse();
		this.findById('PDEF_EvnPLDispProfPanel').collapse();
		this.findById('PDEF_EvnPLDispProfPanel').hide();
		this.findById('PDEF_DiagPanel').collapse();
		this.findById('PDEF_PersonDispHist').getGrid().getStore().removeAll();
		this.findById('PDEF_PersonDispHistPanel').collapse();

		this.findById('PDEF_SicknessPanel').hide();
		this.findById('PDEF_PregnantPanel').hide();
		//this.findById('PDEF_PregnantPanel').hide();
		this.findById('PDEF_PersonPrivilegePanel').isLoaded = false;
		this.findById('PDEF_PersonDispTargetRatePanel').isLoaded = false;
		this.findById('PDEF_PersonDispVizitPanel').isLoaded = false;
		this.findById('PDEF_EvnPLDispProfPanel').isLoaded = false;
		this.findById('PDEF_DiagPanel').isLoaded = false;
		this.findById('PDEF_PersonDispHistPanel').isLoaded = false;
		var base_form = this.FormPanel.getForm();
		base_form.reset();
		base_form.findField('Diag_id').filterDate = null;
		this.action = null;
		this.callback = Ext.emptyFn;
		this.Diag_id = null;
		this.DiagFilter_id = null;
		this.DiagLevelFilter_id = null;
		this.formStatus = 'edit';
		this.isDopDisp = false;
		this.onHide = Ext.emptyFn;
		this.Sickness_id = null;
		this.UserLpuSection_id = null;
		this.UserLpuSectionList = new Array();
		this.UserMedStaffFact_id = null;
        this.MedPersonal_id = null;
		this.UserMedStaffFactList = new Array();
		this.PersonDispHistSaved = false;
		this.PersonDispCloseDate = null;
		this.openedFromARM = null;
		this.availabilityCardCauseDeath = 0;
		this.PersonPregnancy_id = null;
		this.isERDB = null;
		this.HumanUID = null;
		if (!arguments[0] || !arguments[0].formParams) {
			sw.swMsg.alert('Сообщение', 'Неверные параметры', function() {
				this.hide();
			}.createDelegate(this));
			return false;
		}
		var childGrid = this.findById('PDEF_ChildGrid');
		childGrid.addActions({
			name:'action_medsved',
			text:'Мед. св-во о рождении',
			handler:function () {
				var formParams = new Object();
				var gcf = this.findById('PDEF_ChildGrid');
				var gc = gcf.getGrid();
				var r = gc.getSelectionModel().getSelected();
				if ((!r) || (!r.get('Person_cid'))) {
					return false;
				}
				var childPerson_id = r.get('Person_cid');
				var action = 'view';
				formParams.BirthSvid_id = r.data.BirthSvid_id;
				getWnd('swMedSvidBirthEditWindow').show(
					{
						action:action,
						formParams:formParams
					}
				);
			}.createDelegate(this)
		});
		var deathGrid = this.findById('PDEF_DeadChildGrid');
		deathGrid.addActions({
			name:'action_pntdethsvid',
			text:'Мед. св-во о перинат. смерти',
			handler:function () {
				var formParams = new Object();
				var gf = this.findById('PDEF_DeadChildGrid');
				var g = gf.getGrid();
				var r = g.getSelectionModel().getSelected();
				if ((!r) || (!r.get('ChildDeath_id'))) {
					return false;
				}
				var action = 'view';
				formParams.PntDeathSvid_id = r.get('PntDeathSvid_id');

				getWnd('swMedSvidPntDeathEditWindow').show({
					action:action,
					formParams:formParams
				});
			}.createDelegate(this)
		});
		// определенный медстафффакт
		if (arguments[0].UserMedStaffFact_id && arguments[0].UserMedStaffFact_id > 0) {
			this.UserMedStaffFact_id = arguments[0].UserMedStaffFact_id;
		}
        else if (arguments[0].formParams && arguments[0].formParams.MedPersonal_id && arguments[0].formParams.MedPersonal_id > 0) {
            this.MedPersonal_id = arguments[0].formParams.MedPersonal_id;
        }
		// если в настройках есть medstafffact, то имеем список мест работы
		else if (Ext.globalOptions.globals['medstafffact'] && Ext.globalOptions.globals['medstafffact'].length > 0) {
			this.UserMedStaffFactList = Ext.globalOptions.globals['medstafffact'];
		}
		// определенный LpuSection
		if (arguments[0].UserLpuSection_id && arguments[0].UserLpuSection_id > 0) {
			this.UserLpuSection_id = arguments[0].UserLpuSection_id;
		}
		// если в настройках есть lpusection, то имеем список мест работы
		else if (Ext.globalOptions.globals['lpusection'] && Ext.globalOptions.globals['lpusection'].length > 0) {
			this.UserLpuSectionList = Ext.globalOptions.globals['lpusection'];
		}
		base_form.setValues(arguments[0].formParams);
		this.PersonInfo.setTitle('...');
		this.PersonInfo.load({
			callback: function(params) {
				this.PersonInfo.setPersonTitle();
				if (getRegionNick() != 'kz' && this.PersonInfo.getFieldValue('Person_Age') >= 18) {
					this.findById('PDEF_EvnPLDispProfPanel').show();
				}

				base_form.findField('PersonDisp_begDate').setMinValue(this.PersonInfo.getFieldValue('Person_Birthday'));
				base_form.findField('PersonDisp_endDate').setMaxValue(this.PersonInfo.getFieldValue('Person_deadDT') || new Date());
				base_form.findField('PersonDisp_DiagDate').setMinValue(this.PersonInfo.getFieldValue('Person_Birthday'));
				base_form.findField('PersonDisp_DiagDate').setMaxValue(this.PersonInfo.getFieldValue('Person_deadDT') || new Date());
			}.createDelegate(this),
			Person_id: base_form.findField('Person_id').getValue(),
			Server_id: base_form.findField('Server_id').getValue()
		});

		if(getRegionNick() == 'kz') this.availabilityDispensaryCardCauseDeath();

		if (arguments[0].action) {
			this.action = arguments[0].action;
		}
		if (arguments[0].callback) {
			this.callback = arguments[0].callback;
		}
		if (arguments[0].Diag_id) {
			this.Diag_id = arguments[0].Diag_id;
		}
		// Диагноз или группа диагнозов, которыми ограничивать список выбора диагноза
		if (arguments[0].DiagFilter_id && arguments[0].DiagLevelFilter_id) {
			this.DiagFilter_id = arguments[0].DiagFilter_id;
			this.DiagLevelFilter_id = arguments[0].DiagLevelFilter_id;
		}
		if (arguments[0].isDopDisp) {
			this.isDopDisp = arguments[0].isDopDisp;
		}
		if (arguments[0].onHide) {
			this.onHide = arguments[0].onHide;
		}
		// Тип заболевания
		if (arguments[0].Sickness_id && arguments[0].Sickness_id > 0) {
			this.Sickness_id = arguments[0].Sickness_id;
		}
		if(arguments[0].PersonPregnancy_id){
			this.PersonPregnancy_id = arguments[0].PersonPregnancy_id;
		}
		if(arguments[0].ARMType){
			this.openedFromARM = arguments[0].ARMType;
		}
		if(arguments[0].BirthSpecStac_OutcomDate){
			this.BirthSpecStac_OutcomDate = arguments[0].BirthSpecStac_OutcomDate;
			base_form.findField('PregnancySpec_OutcomDT').setValue(arguments[0].BirthSpecStac_OutcomDate);
		}
		if(arguments[0].isERDB){
			this.isERDB = arguments[0].isERDB;
		}

		var Person_id = this.FormPanel.getForm().findField('Person_id').getValue();
		if(Person_id && getRegionNick().inlist(['vologda'])){
			this.filterDiadCombo(Person_id);
		}

		this.findById('PDEF_PersonDispMedicamentGrid').removeAll(true);

		this.findById('PDEF_EvnUslugaPregnancySpecGrid').getTopToolbar().items.items[0].enable();

		//this.MorbusHepatitisSpec.hide();
        this.MorbusNephro.onShowWindow(this);
		var diag_combo = base_form.findField('Diag_id');
		var diag_id;
		var idx;
		diag_combo.additQueryFilter = '';
		diag_combo.allQueryFilter = '';
		diag_combo.DiagFilter_id = null;
		diag_combo.DiagLevelFilter_id = null;
		var loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT });
		loadMask.show();

		this.findById('PDEF_PersonDispTargetRateGrid').setReadOnly(!this.action.inlist([ 'add', 'edit' ]));

		// var fieldPersonDisp_NumCard = base_form.findField('PersonDisp_NumCard');
		// var afterNumCardDiv = document.createElement('div');
		// afterNumCardDiv.id = 'afterNumCardDiv';
		// afterNumCardDiv.innerText = '* проба пера';
		// afterNumCardDiv.setAttribute("style", "width:330px; float:left; display: block");
		// fieldPersonDisp_NumCard.container.dom.appendChild(afterNumCardDiv);
		switch (this.action) {
			case 'add':
				this.setTitle(WND_POL_PERSDISPADD);
				this.enableEdit(true);

				this.swEMDPanel.setParams({
					EMDRegistry_ObjectName: 'PersonDisp',
					EMDRegistry_ObjectID: null
				});
				this.swEMDPanel.setIsSigned(null);

				if (arguments[0].isERDB) {

					if (arguments[0].Dgroup_kod) base_form.findField('DispGroup_id').setValue(arguments[0].Dgroup_kod);
					if (arguments[0].Nomkart) base_form.findField('PersonDisp_NumCard').setValue(arguments[0].Nomkart);
					if (arguments[0].Diag_id) base_form.findField('Diag_id').setValue(arguments[0].Diag_id);

					if (arguments[0].Dt_beg){
						base_form.findField('PersonDisp_begDate').fireEvent('change', base_form.findField('PersonDisp_begDate'), new Date(arguments[0].Dt_beg));
						base_form.findField('PersonDisp_begDate').setValue(new Date(arguments[0].Dt_beg));
					}
					if (arguments[0].Dt_end){
						base_form.findField('PersonDisp_endDate').fireEvent('change', base_form.findField('PersonDisp_endDate'), new Date(arguments[0].Dt_end));
						base_form.findField('PersonDisp_endDate').setValue(new Date(arguments[0].Dt_end));
					}

					if (arguments[0].Prich_End_ID) base_form.findField('DispOutType_id').setValue(arguments[0].Prich_End_ID);
					if (arguments[0].Vra_UID_MedStaffFact_id) base_form.findField('MedStaffFact_id').setValue(arguments[0].Vra_UID_MedStaffFact_id);
					if (arguments[0].Vra_UID_LpuSection_id) base_form.findField('LpuSection_id').setValue(arguments[0].Vra_UID_LpuSection_id);
					if (arguments[0].PersonDispHist_MedPersonalFio) base_form.findField('PersonDispHist_MedPersonalFio').setValue(arguments[0].PersonDispHist_MedPersonalFio);
					if (arguments[0].HumanUID) this.HumanUID = arguments[0].HumanUID;
				} else {
					base_form.findField('PersonDisp_begDate').fireEvent('change', base_form.findField('PersonDisp_begDate'), base_form.findField('PersonDisp_begDate').getValue());
					base_form.findField('PersonDisp_endDate').fireEvent('change', base_form.findField('PersonDisp_endDate'), base_form.findField('PersonDisp_endDate').getValue());
				}
				// this.loadListsAndFormData();
				// Дополнительные фильтры для диагноза
				if (this.DiagFilter_id > 0 && this.DiagLevelFilter_id > 0) {
					diag_combo.DiagFilter_id = this.DiagFilter_id;
					diag_combo.DiagLevelFilter_id = this.DiagLevelFilter_id;
				}
				// Типы заболеваний
				if (this.Sickness_id && this.Sickness_id > 0) {
					base_form.findField('Sickness_id').setValue(this.Sickness_id);
					if (base_form.findField('Sickness_id').getValue()) {
						var sickness_id = this.Sickness_id;
						var ids = [ 0 ];
						var sickness_diag_store = this.sicknessDiagStore;
						sickness_diag_store.load({
							callback: function() {
								idx = sickness_diag_store.findBy(function(record) {
									if (record.get('Sickness_id') == sickness_id || sickness_id == 100500) {
										ids.push(record.get('Diag_id'));
									}
								});
								if (sickness_id == 100500) {
									diag_combo.additQueryFilter = "Diag_id not in (" + ids.join(', ') + ")";
									diag_combo.additClauseFilter = '!(record["Diag_id"].inlist([' + ids.join(', ') + ']))';
								} else {
									diag_combo.additQueryFilter = "Diag_id in (" + ids.join(', ') + ")";
									diag_combo.additClauseFilter = '(record["Diag_id"].inlist([' + ids.join(', ') +']))';
								}
							}.createDelegate(this)
						})

					}
				} else {
					this.sicknessDiagStore.load();
				}
				// если переданы диагнозы, то загружаем диагноз, так же с прочими полями (это в случае новой карты, создаваемой из старой)
				if (this.Diag_id) {
					diag_id = this.Diag_id;
					diag_combo.getStore().load({
						callback: function() {
							diag_combo.setValue(diag_id);
							diag_combo.getStore().each(function(record) {
								if (record.get('Diag_id') == diag_id) {
									diag_combo.fireEvent('select', diag_combo, record, 0);
									diag_combo.fireEvent('change', diag_combo, diag_id, 0);
								}
							});
						}.createDelegate(this),
						params: {
							where: "where Diag_id = " + diag_id
						}
					});
				}
				this.findById('Diag_id_TFOMS_msg').hide();
				var yesno_id = 1;
				if (this.isDopDisp) {
					yesno_id = 2;
				}
				loadMask.hide();
				base_form.findField('PersonDisp_NumCard').focus(true, 250);
				this.specifics.init();
				break;
			case 'edit':
			case 'view':
				var person_disp_id = base_form.findField('PersonDisp_id').getValue();
				if (!person_disp_id) {
					loadMask.hide();
					this.hide();
					return false;
				}
				//this.MorbusHepatitisSpec.collapse();
				base_form.load({
					failure: function() {
						loadMask.hide();
						sw.swMsg.alert('Ошибка', 'Ошибка при загрузке данных формы', function() {
							this.hide();
						}.createDelegate(this));
					}.createDelegate(this),
					params: {
						'PersonDisp_id': person_disp_id
					},
					success: function() {
						if (base_form.findField('accessType').getValue() == 'view'
							&& !( haveArmType('mstat')
								|| isSuperAdmin() || isUserGroup('PersonDispHistEdit')
								|| (getGlobalOptions().medpersonal_id == null && base_form.findField('Lpu_id').getValue() > 0 && getGlobalOptions().lpu_id == base_form.findField('Lpu_id').getValue())
								)
							) {
							this.action = 'view';
						}
						if (this.action == 'edit') {
							this.setTitle(WND_POL_PERSDISPEDIT);
							this.enableEdit(true);
						} else {
							this.setTitle(WND_POL_PERSDISPVIEW);
							this.enableEdit(false);

							this.findById('PDEF_EvnUslugaPregnancySpecGrid').getTopToolbar().items.items[0].disable();
						}

						this.swEMDPanel.setParams({
							EMDRegistry_ObjectName: 'PersonDisp',
							EMDRegistry_ObjectID: base_form.findField('PersonDisp_id').getValue()
						});
						this.swEMDPanel.setIsSigned(base_form.findField('PersonDisp_IsSignedEP').getValue());

						if (this.action == 'edit') {
							setCurrentDateTime({
								dateField: base_form.findField('PersonDisp_begDate'),
								loadMask: false,
								setDate: false,
								setDateMaxValue: true,
								windowId: this.id
							});
						}

						if(base_form.findField('Label_id').getValue()=='1') {
							var ids = [5378,5379,5380,5381,5382,5383,5384,5385,5386,5387,5388,5389,5390,11742,11742];
							diag_combo.additQueryFilter = "Diag_id in (" + ids.join(', ') + ")";
							diag_combo.additClauseFilter = '(record["Diag_id"].inlist([' + ids.join(', ') +']))';
						}
						var diag_id = diag_combo.getValue();
						var index;
						var lpu_section_id = base_form.findField('LpuSection_id').getValue();
						var med_personal_id = base_form.findField('MedPersonal_id').getValue();
						var record;
						base_form.findField('PersonDisp_endDate').fireEvent('change', base_form.findField('PersonDisp_endDate'), base_form.findField('PersonDisp_endDate').getValue());
						this.PersonDispCloseDate = base_form.findField('PersonDisp_endDate').getValue();
						if (this.action == 'edit') {
							base_form.findField('DispOutType_id').fireEvent('change', base_form.findField('DispOutType_id'), base_form.findField('DispOutType_id').getValue());
							base_form.findField('PersonDisp_begDate').fireEvent('change', base_form.findField('PersonDisp_begDate'), base_form.findField('PersonDisp_begDate').getValue());
							base_form.findField('Diag_id').filterDate = Ext.util.Format.date(base_form.findField('PersonDisp_begDate').getValue(), 'd.m.Y');
							index = base_form.findField('MedStaffFact_id').getStore().findBy(function(record, id) {
								if (record.get('LpuSection_id') == lpu_section_id && record.get('MedPersonal_id') == med_personal_id) {
									return true;
								} else {
									return false;
								}
							});
							if (index >= 0) {
								base_form.findField('MedStaffFact_id').setValue(base_form.findField('MedStaffFact_id').getStore().getAt(index).get('MedStaffFact_id'));
							}
						} else {
							base_form.findField('LpuSection_id').getStore().load({
								callback: function() {
									index = base_form.findField('LpuSection_id').getStore().findBy(function(rec) {
										if (rec.get('LpuSection_id') == lpu_section_id) {
											return true;
										} else {
											return false;
										}
									});
									if (index >= 0) {
										base_form.findField('LpuSection_id').setValue(base_form.findField('LpuSection_id').getStore().getAt(index).get('LpuSection_id'));
										base_form.findField('LpuSection_id').fireEvent('change', base_form.findField('LpuSection_id'), base_form.findField('LpuSection_id').getStore().getAt(index).get('LpuSection_id'));
									}
								}.createDelegate(this),
								params: {
									LpuSection_id: lpu_section_id,
									mode: 'combo'
								}
							});
							base_form.findField('MedStaffFact_id').getStore().load({
								callback: function() {
									index = base_form.findField('MedStaffFact_id').getStore().findBy(function(rec) {
										if (rec.get('MedPersonal_id') == med_personal_id && rec.get('LpuSection_id') == lpu_section_id) {
											return true;
										} else {
											return false;
										}
									});
									if (index >= 0) {
										base_form.findField('MedStaffFact_id').setValue(base_form.findField('MedStaffFact_id').getStore().getAt(index).get('MedStaffFact_id'));
									}
								}.createDelegate(this),
								params: {
									//LpuSection_id: lpu_section_id,
									MedPersonal_id: med_personal_id
								}
							});
						}
						if (diag_id) {
							diag_combo.getStore().load({
								callback: function() {
/*
									diag_combo.getStore().each(function (rec) {
										if (rec.get('Diag_id') == diag_id) {
											var diag_code = rec.get('Diag_Code').substr(0, 3);
											if ( diag_code.inlist(['B15', 'B16', 'B17', 'B18', 'B19']) ) {
												this.MorbusHepatitisSpec.show();
											}
										}
									}.createDelegate(this));
*/
									diag_combo.fireEvent('select', diag_combo, diag_combo.getStore().getAt(0), 0);
								}.createDelegate(this),
								params: {
									//where: "where DiagLevel_id = 4 and Diag_id = " + diag_id
									where: (getRegionNick()=='kz')?("where Diag_id = " + diag_id):("where DiagLevel_id = 4 and Diag_id = " + diag_id)
								}
							});
						}
						base_form.findField('Diag_id').fireEvent('change', base_form.findField('Diag_id'), diag_id);
						if (!this.findById('PDEF_SicknessPanel').hidden) {
							this.findById('PDEF_PersonDispMedicamentGrid').getGrid().getStore().load({
								params: {
									PersonDisp_id: person_disp_id
								}
							});
						}
						var tfoms = base_form.findField('PersonDisp_IsTFOMS').getValue();
						if (tfoms == 2 && this.action == 'edit') {
							base_form.findField('Diag_id').disable();
							this.findById('Diag_id_TFOMS_msg').show();
						} else {
							this.findById('Diag_id_TFOMS_msg').hide();
							if(this.action == 'edit')
								base_form.findField('Diag_id').enable();
						}
						loadMask.hide();
						if (this.action == 'edit') {
							base_form.findField('PersonDisp_NumCard').focus(true, 250);
						} else {
							this.buttons[this.buttons.length - 1].focus();
						}
						if (!this.findById('PDEF_PregnantPanel').hidden){
							this.specifics.init();
							this.specifics.show(this.action);
						}
						var win = this;
						this.findById('PDEF_PersonDispHist').getGrid().getStore().load({
							params: {
								PersonDisp_id: win.FormPanel.getForm().findField('PersonDisp_id').getValue()
							},
							callback:function(){
								win.findById('PDEF_PersonDispHistPanel').isLoaded = true;
							}
						});
						this.findById('PDEF_PersonDispVizitGrid').getGrid().getStore().load({
							params: {
								PersonDisp_id: win.FormPanel.getForm().findField('PersonDisp_id').getValue()
							},
							callback:function(){
								win.findById('PDEF_PersonDispVizitPanel').isLoaded = true;
							}
						});
						this.findById('PDEF_EvnPLDispProfGrid').getGrid().getStore().load({
							params: {
								Person_id: win.FormPanel.getForm().findField('Person_id').getValue()
							},
							callback:function(){
								win.findById('PDEF_EvnPLDispProfPanel').isLoaded = true;
							}
						});
						this.enablePrint030();
						//PROMEDWEB-10455
						if (!base_form.findField('PersonDisp_NumCard').getValue()) {
							base_form.findField('PersonDisp_NumCard').enable()
						}
						
					}.createDelegate(this),
					url: '/?c=PersonDisp&m=loadPersonDispEditForm'
				});
				break;
			default:
				loadMask.hide();
				this.hide();
				break;
		}
		var today = new Date();
		base_form.findField('PersonDisp_begDate').maxValue = today;
		if(!Ext.isEmpty(base_form.findField('PersonDisp_begDate').getValue())){
			base_form.findField('PersonDisp_endDate').minValue = base_form.findField('PersonDisp_begDate').getValue();
		} else {
			base_form.findField('PersonDisp_endDate').minValue = null;
		}

		this.disableViewFrameActions(this.action=='view');
	},
	filterDiadCombo: function (Person_id) {
		var base_form = this.FormPanel.getForm(),
			diag_combo = base_form.findField('Diag_id'),
			diag_panel = diag_combo.findParentByType('panel');

		var	old_h = 24;
		if(diag_panel && diag_panel.getSize() && diag_panel.getSize().height)
			old_h = diag_panel.getSize().height;

		diag_combo.allowDiagList = null;
		diag_panel.setHeight(50);
		var loadMask = new Ext.LoadMask(diag_panel.getEl(), { msg: 'Подождите, идет загрузка доступных диагнозов' });
		loadMask.show();
		Ext.Ajax.request({
			callback: function(options, success, response) {
				loadMask.hide();
				diag_panel.setHeight(old_h);
				if ( success ) {
					var response_obj = Ext.util.JSON.decode(response.responseText);

					if ( response_obj.success == false ) {
						sw.swMsg.alert(langs('Ошибка'), response_obj.Error_Msg ? response_obj.Error_Msg : langs('Ошибка при загрузке списка уточненных диагнозов'));
					}
					else {
						if(response_obj.length > 0){
							diag_combo.allowDiagList = true;
							var ids = [0];
							var codes = [];
							response_obj.forEach(function(rec){
								ids.push(rec.Diag_id);
								codes.push(rec.Diag_Code);
							});
							var target = diag_combo.trigger;
							diag_combo.qtip = new Ext.QuickTip({
								target: target,
								text: "Список уточненных диагнозов: <br>" + codes.join(', '),
								enabled: true,
								showDelay: 60,
								trackMouse: true,
								autoShow: true
							});
							Ext.QuickTips.register(diag_combo.qtip);
							diag_combo.additQueryFilter = "Diag_id in (" + ids.join(', ') + ")";
							diag_combo.additClauseFilter = '(record["Diag_id"].inlist([' + ids.join(', ') + ']))';
						}
					}
				}
				else {
					sw.swMsg.alert(langs('Ошибка'), langs('Ошибка при загрузке списка уточненных диагнозов'));
				}
			},
			params: {
				Person_id: Person_id
			},
			url: '/?c=EvnDiag&m=loadPersonDiagPanel'
		});
		sw4.showInfoMsg({
			hideDelay: 12000,
			type: 'warning',
			text: 'Внимание!<br>Если в выпадающем списке поля \"Диагноз\" отсутствует необходимый, то требуется добавить его в Список уточненных диагнозов в Сигнальной информации в ЭМК'
		});
	},
	disableViewFrameActions: function(disable) {
		var win = this,
			frame_ids = ['PDEF_PersonDispSopDiag','PDEF_PersonDispVizitGrid'],
			actionList = ['action_add','action_edit','action_delete','action_refresh'];
		frame_ids.forEach(function(frame_id) {
			actionList.forEach(function(actionName){
				win.findById(frame_id).setActionDisabled(actionName, disable);
			});
		});
	},
    collectGridData:function (gridName) {
        var result = '';
		if (this.findById('PDEF_' + gridName)) {
			var grid = this.findById('PDEF_' + gridName).getGrid();
			grid.getStore().clearFilter();
			if (grid.getStore().getCount() > 0) {
				if ((grid.getStore().getCount() == 1) && ((grid.getStore().getAt(0).data.RecordStatus_Code == undefined))) {
					return '';
				}
				var gridData = getStoreRecords(grid.getStore(), {convertDateFields:true});
				result = Ext.util.JSON.encode(gridData);
			}
			grid.getStore().filterBy(function (rec) {
				return Number(rec.get('RecordStatus_Code')) != 3;
			});
		}
        return result;
    },
	openWindow: function(gridName, action) {
		if (!action || !action.toString().inlist(['add', 'edit', 'view'])) {
			return false;
		}

		if (action == 'add' && getWnd('sw'+gridName+'Window').isVisible()) {
			sw.swMsg.alert('Сообщение', 'Окно редактирования уже открыто');
			return false;
		}

        var viewFrame = this.findById('PDEF_'+gridName);
        var grid = viewFrame.getGrid();
		var params = {};

		params.action = action;
		params.callback = function(data) {

			if (!data || !data.BaseData) {
				return false;
			}
            if ('MorbusNephroLab' == gridName && action.toString().inlist(['add', 'edit'])) {
                var actionSetOnlyLast = viewFrame.getAction('action_setOnlyLast');
                actionSetOnlyLast.setDisabled(true);
            }
			data.BaseData.RecordStatus_Code = 0;

			// Обновить запись в grid
			var record = grid.getStore().getById(data.BaseData[gridName+'_id']);

			if (record) {
				if (record.get('RecordStatus_Code') == 1) {
					data.BaseData.RecordStatus_Code = 2;
				}

				var grid_fields = new Array();

				grid.getStore().fields.eachKey(function(key, item) {
					grid_fields.push(key);
				});

				for (i = 0; i < grid_fields.length; i++) {
					record.set(grid_fields[i], data.BaseData[grid_fields[i]]);
				}

				record.commit();
			} else {
				if (grid.getStore().getCount() == 1 && !grid.getStore().getAt(0).get(gridName+'_id')) {
					grid.getStore().removeAll();
				}

				data.BaseData[gridName+'_id'] = -swGenTempId(grid.getStore());

				grid.getStore().loadData([ data.BaseData ], true);
			}
		};
		params.formMode = 'local';
		params.formParams = new Object();

		if (action == 'add') {
			params.onHide = Ext.emptyFn;
		}
		else {
			if (!grid.getSelectionModel().getSelected()) {
				return false;
			}

			var selected_record = grid.getSelectionModel().getSelected();

			params.formParams = selected_record.data;
			params.onHide = function() {
				grid.getView().focusRow(grid.getStore().indexOf(selected_record));
			};
		}

		getWnd('sw'+gridName+'Window').show(params);

	},
	deleteGridSelectedRecord: function(gridId, idField) {
		var grid = this.findById(gridId).getGrid();
		var record = grid.getSelectionModel().getSelected();
		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId, text, obj) {
				if (buttonId == 'yes') {
					if (!grid || !grid.getSelectionModel() || !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get(idField)) {
						return false;
					}
					switch (Number(record.get('RecordStatus_Code'))) {
						case 0:
							grid.getStore().remove(record);
							break;

						case 1:
						case 2:
							record.set('RecordStatus_Code', 3);
							record.commit();

							grid.getStore().filterBy(function(rec) {
								if (Number(rec.get('RecordStatus_Code')) == 3) {
									return false;
								}
								else {
									return true;
								}
							});
							break;
					}
				}
				if (grid.getStore().getCount() > 0) {
					grid.getView().focusRow(0);
					grid.getSelectionModel().selectFirstRow();
				}
			}.createDelegate(this),
			icon: Ext.MessageBox.QUESTION,
			msg: 'Вы действительно хотите удалить эту запись?',
			title: 'Вопрос'
		});
	},
	width: 750
});
