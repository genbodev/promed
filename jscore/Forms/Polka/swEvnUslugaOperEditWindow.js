/**
* swEvnUslugaOperEditWindow - окно редактирования/добавления выполнения общей услуги.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Polka
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Stas Bykov aka Savage (savage1981@gmail.com)
* @version      0.001-23.03.2010
* @comment      Префикс для id компонентов EUOperEF (EvnUslugaOperEditForm)
*
*
* @input data: action - действие (add, edit, view)
*              parentClass - класс родительского события
*
*
* Использует: окно добавления/редактирования осложнения (swEvnAggEditWindow)
*             окно добавления/редактирования операционной бригады (swEvnUslugaOperBrigEditWindow)
*             окно добавления/редактирования вида анестезии (swEvnUslugaOperAnestEditWindow)
*             окно поиска организации (swOrgSearchWindow)
*/

sw.Promed.swEvnUslugaOperEditWindow = Ext.extend(sw.Promed.BaseForm, {
	action: null,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: false,
	closeAction: 'hide',
	collapsible: true,
	deleteEvent: function(event) {
		if ( this.action == 'view' ) {
			return false;
		}

		if ( event != 'EvnDrug' && event != 'EvnAgg' && event != 'EvnUslugaOperAnest' && event != 'EvnUslugaOperBrig' ) {
			return false;
		}

		var error = '';
		var grid = null;
		var question = '';
		var params = new Object();
		var url = '';
		switch ( event ) {
			case 'EvnDrug':
				error = langs('При удалении случая использования медикаментов возникли ошибки');
				grid = this.findById('EUOperEF_EvnDrugGrid');
				question = langs('Удалить случай использования медикаментов?');
				url = '/?c=EvnDrug&m=deleteEvnDrug';
				break;
			
			case 'EvnAgg':
				error = langs('При удалении осложнения возникли ошибки');
				grid = this.findById('EUOperEF_EvnAggGrid');
				question = langs('Удалить осложнение?');
				url = '/?c=EvnAgg&m=deleteEvnAgg';
			break;

			case 'EvnUslugaOperAnest':
				error = langs('При удалении вида анестезии возникли ошибки');
				grid = this.findById('EUOperEF_EvnUslugaOperAnestGrid');
				question = langs('Удалить применяемый вид анестезии?');
				url = '/?c=EvnUslugaOperAnest&m=deleteEvnUslugaOperAnest';
			break;

			case 'EvnUslugaOperBrig':
				error = langs('При удалении операционной бригады возникли ошибки');
				grid = this.findById('EUOperEF_EvnUslugaOperBrigGrid');
				question = langs('Удалить операционную бригаду?');
				url = '/?c=EvnUslugaOperBrig&m=deleteEvnUslugaOperBrig';
			break;
		}

		if ( !grid || !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get(event + '_id') ) {
			return false;
		}

		var selected_record = grid.getSelectionModel().getSelected();

		params[event + '_id'] = selected_record.get(event + '_id');

		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId, text, obj) {
				if ( buttonId == 'yes' ) {
					var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Удаление записи..."});
					loadMask.show();

					Ext.Ajax.request({
						failure: function(response, options) {
							loadMask.hide();
							sw.swMsg.alert(langs('Ошибка'), error);
						},
						params: params,
						success: function(response, options) {
							loadMask.hide();
							var response_obj = Ext.util.JSON.decode(response.responseText);

							if ( response_obj.success == false ) {
								sw.swMsg.alert(langs('Ошибка'), response_obj.Error_Msg ? response_obj.Error_Msg : error);
							}
							else {
								grid.getStore().remove(selected_record);

								if ( grid.getStore().getCount() == 0 ) {
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
				}
			}.createDelegate(this),
			icon: Ext.MessageBox.QUESTION,
			msg: question,
			title: langs('Вопрос')
		});
	},
	doSave: function(options) {
		// options @Object
		// options.openChildWindow @Function Открыть доченрее окно после сохранения
		if ( this.formStatus == 'save' || this.action == 'view' ) {
			return false;
		}
		options = options||{};
		options.ignoreErrors = options.ignoreErrors || [];

		this.formStatus = 'save';

		var base_form = this.FormPanel.getForm();

		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					this.formStatus = 'edit';
					this.FormPanel.getFirstInvalidEl().focus(false);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		var set_date = base_form.findField('EvnUslugaOper_setDate').getValue(),
			set_time = base_form.findField('EvnUslugaOper_setTime').getValue(),
			dis_date = base_form.findField('EvnUslugaOper_disDate').getValue(),
			dis_time = base_form.findField('EvnUslugaOper_disTime').getValue();
			
		if (this.IsPriem) {
			var evn_setdate = base_form.findField('EvnUslugaOper_pid').getFieldValue('Evn_setDate'),
				evn_disdate = base_form.findField('EvnUslugaOper_pid').getFieldValue('Evn_disDate');
				
			if (set_date < evn_setdate || (!Ext.isEmpty(evn_disdate) && set_date > evn_disdate)) {
				Ext.MessageBox.show({
					buttons: Ext.Msg.OK,
					fn: function() {
						this.formStatus = 'edit';
						base_form.findField('EvnUslugaOper_setDate').focus(false)
					}.createDelegate(this),
					icon: Ext.Msg.WARNING,
					msg: 'Дата выполнения услуги не попадает в период выбранного движения',
					title: langs('Ошибка')
				});
				return false;
			}
		}

		if (!Ext.isEmpty(dis_date)) {
			var setDateStr = Ext.util.Format.date(set_date, 'Y-m-d')+' '+(Ext.isEmpty(set_time)?'00:00':set_time);
			var disDateStr = Ext.util.Format.date(dis_date, 'Y-m-d')+' '+(Ext.isEmpty(dis_time)?'00:00':dis_time);

			if (Date.parseDate(setDateStr, 'Y-m-d H:i') > Date.parseDate(disDateStr, 'Y-m-d H:i')) {
				Ext.MessageBox.show({
					buttons: Ext.Msg.OK,
					fn: function() {
						this.formStatus = 'edit';
						base_form.findField('EvnUslugaOper_setDate').focus(false)
					}.createDelegate(this),
					icon: Ext.Msg.WARNING,
					msg: langs('Дата окончания выполнения услуги не может быть меньше даты начала выполнения услуги.'),
					title: langs('Ошибка')
				});
				return false;
			}
		}

		var evn_usluga_set_time = base_form.findField('EvnUslugaOper_setTime').getValue();
		var evn_usluga_oper_pid = base_form.findField('EvnUslugaOper_pid').getValue();

		if ( this.parentClass.inlist([ 'EvnPL', 'EvnPS', 'EvnVizit' ]) && !evn_usluga_oper_pid && getRegionNick().inlist(['perm', 'kareliya','ekb'])) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					this.formStatus = 'edit';
					base_form.findField('EvnUslugaOper_pid').focus(true, 250);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: 'Не выбрано ' + (this.parentClass == 'EvnPS' ? 'движение' : 'посещение'),
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		var win = this;
		var pay_type_nick = base_form.findField('PayType_id').getFieldValue('PayType_SysNick');

		var sex_code = this.findById('EUOperEF_PersonInformationFrame').getFieldValue('Sex_Code');
		var person_birthday = this.findById('EUOperEF_PersonInformationFrame').getFieldValue('Person_Birthday');
		var diag_record = base_form.findField('Diag_id').getStore().getById(base_form.findField('Diag_id').getValue());
		var person_age = swGetPersonAge(person_birthday, set_date);
		var person_age_month = swGetPersonAgeMonth(person_birthday, set_date);
		var person_age_day = swGetPersonAgeDay(person_birthday, set_date);
		try {
			if ( getRegionNick() == 'ekb' && diag_record) {
				if ( person_age == -1 || person_age_month == -1 || person_age_day == -1 ) {
					throw {msg: langs('Ошибка при определении возраста пациента')};
				}
				if ( !sex_code || !(sex_code.toString().inlist([ '1', '2' ])) ) {
					throw {msg: langs('Не указан пол пациента')};
				}
				if ( !Ext.isEmpty(diag_record.get('Sex_Code')) && Number(diag_record.get('Sex_Code')) != Number(sex_code) ) {
					throw {warningMsg: langs('Выбранный диагноз не соответствует полу пациента'), fieldName: 'Diag_id'};
				}
				if ( pay_type_nick == 'oms' ) {
					var LpuSectionProfile_Code = base_form.findField('MedStaffFact_id').getFieldValue('LpuSectionProfile_Code');
					if ( LpuSectionProfile_Code && LpuSectionProfile_Code.inlist([ '658', '684', '558', '584' ]) ) {
						if ( diag_record.get('DiagFinance_IsHealthCenter') != 1 ) {
							throw {warningMsg: langs('Диагноз не оплачивается для Центров здоровья'), fieldName: 'Diag_id'};
						}
					} else if ( diag_record.get('DiagFinance_IsOms') == 0 ) {
						throw {warningMsg: langs('Данный диагноз не подлежит оплате в системе ОМС. Смените вид оплаты.'), fieldName: 'Diag_id'};
					}
				}
				if (
					(person_age < 18 && Number(diag_record.get('PersonAgeGroup_Code')) == 1)
					|| ((person_age > 19 || (person_age == 18 && person_age_month >= 6)) && Number(diag_record.get('PersonAgeGroup_Code')) == 2)
					|| ((person_age > 0 || (person_age == 0 && person_age_month >= 3)) && Number(diag_record.get('PersonAgeGroup_Code')) == 3)
					|| (person_age_day >= 28 && Number(diag_record.get('PersonAgeGroup_Code')) == 4)
					|| (person_age >= 4 && Number(diag_record.get('PersonAgeGroup_Code')) == 5)
				) {
					throw {warningMsg: langs('Выбранный диагноз не соответствует возрасту пациента'), fieldName: 'Diag_id'};
				}
			}
		} catch(err) {
			if (err.warningMsg) {
				if (false == err.warningMsg.toString().inlist(options.ignoreErrors)) {
					sw.swMsg.show({
						buttons: Ext.Msg.YESNO,
						fn: function(buttonId, text, obj) {
							win.formStatus = 'edit';
							if ('yes' == buttonId) {
								options.ignoreErrors.push(err.warningMsg);
								win.doSave(options);
							} else if (err.fieldName && base_form.findField(err.fieldName)) {
								base_form.findField(err.fieldName).markInvalid(err.warningMsg);
								base_form.findField(err.fieldName).focus(true);
							}
						},
						icon: Ext.Msg.WARNING,
						msg: '' + err.warningMsg + '<br>Продолжить сохранение?',
						title: langs('Предупреждение')
					});
					return false;
				}
			} else {
				win.formStatus = 'edit';
				sw.swMsg.alert(langs('Ошибка'), err.msg || err.toString());
				return false;
			}
		}

		var params = new Object();

		var loadMask = new Ext.LoadMask(this.getEl(), {msg: LOAD_WAIT_SAVE});
		loadMask.show();

		params.MedPersonal_id = base_form.findField('MedStaffFact_id').getFieldValue('MedPersonal_id');

		if (Ext.isEmpty(base_form.findField('EvnUslugaOper_pid').getValue()) && !Ext.isEmpty(base_form.findField('Evn_id').getValue()) && !getRegionNick().inlist(['perm', 'kareliya','ekb'])){
			base_form.findField('EvnUslugaOper_pid').setValue(base_form.findField('Evn_id').getValue());
		}
		if ( base_form.findField('EvnUslugaOper_pid').disabled ) {
			params.EvnUslugaOper_pid = evn_usluga_oper_pid;
		}

        if ( base_form.findField('EvnPrescr_id').disabled ) {
            params.EvnPrescr_id = base_form.findField('EvnPrescr_id').getValue();
        }

		if ( base_form.findField('Lpu_uid').disabled ) {
			params.Lpu_uid = base_form.findField('Lpu_uid').getValue();
		}

		if ( base_form.findField('LpuSection_uid').disabled ) {
			params.LpuSection_uid = base_form.findField('LpuSection_uid').getValue();
		}

		if ( base_form.findField('MedStaffFact_id').disabled ) {
			params.MedStaffFact_id = base_form.findField('MedStaffFact_id').getValue();
		}

		if ( base_form.findField('UslugaComplex_id').disabled ) {
			params.UslugaComplex_id = base_form.findField('UslugaComplex_id').getValue();
		}

		if ( base_form.findField('PayType_id').disabled ) {
			params.PayType_id = base_form.findField('PayType_id').getValue();
		}

		if ( base_form.findField('DiagSetClass_id').disabled ) {
			params.DiagSetClass_id = base_form.findField('DiagSetClass_id').getValue();
		}

		if(win.isVisibleExecutionPanel){
			params.UslugaExecutionType_id = base_form.findField('UslugaExecutionType_id').getValue();
			params.UslugaExecutionReason_id = base_form.findField('UslugaExecutionReason_id').getValue();
		}else{
			base_form.findField('UslugaExecutionReason_id').clearValue();
			base_form.findField('UslugaExecutionType_id').reset();
			params.UslugaExecutionType_id = null;
			params.UslugaExecutionReason_id = null;
		}

		params.ignorePaidCheck = this.ignorePaidCheck;
		params.ignoreParentEvnDateCheck = (!Ext.isEmpty(options.ignoreParentEvnDateCheck) && options.ignoreParentEvnDateCheck === 1) ? 1 : 0;
		params.ignoreBallonBegCheck = (!Ext.isEmpty(options.ignoreBallonBegCheck) && options.ignoreBallonBegCheck === 1) ? 1 : 0;
		params.ignoreCKVEndCheck = (!Ext.isEmpty(options.ignoreCKVEndCheck) && options.ignoreCKVEndCheck === 1) ? 1 : 0;

		base_form.submit({
			failure: function(result_form, action) {
				this.formStatus = 'edit';
				loadMask.hide();

				if ( action.result ) {
					if (action.result.Alert_Msg) {
						sw.swMsg.show({
							buttons: Ext.Msg.YESNO,
							fn: function(buttonId, text, obj) {
								if ( buttonId == 'yes' ) {
									if (action.result.Error_Code == 109) {
										options.ignoreParentEvnDateCheck = 1;
									}
									if (action.result.Error_Code == 110) {
										options.ignoreBallonBegCheck = 1;
									}
									if (action.result.Error_Code == 111) {
										options.ignoreCKVEndCheck = 1;
									}

									this.doSave(options);
								}
							}.createDelegate(this),
							icon: Ext.MessageBox.QUESTION,
							msg: action.result.Alert_Msg,
							title: langs(' Продолжить сохранение?')
						});
					} else if ( action.result.Error_Msg ) {
						sw.swMsg.alert(langs('Ошибка'), action.result.Error_Msg);
					}
					else {
						sw.swMsg.alert(langs('Ошибка'), langs('При сохранении произошли ошибки [Тип ошибки: 3]'));
					}
				}
			}.createDelegate(this),
			params: params,
			success: function(result_form, action) {
				this.formStatus = 'edit';
				loadMask.hide();

				if ( action.result && action.result.EvnUslugaOper_id > 0 ) {
					base_form.findField('EvnUslugaOper_id').setValue(action.result.EvnUslugaOper_id);
					this.EvnXmlPanel.onEvnSave();
					this.EvnXmlPanelNarcosis.onEvnSave();

					this.FileUploadPanel.listParams = {Evn_id: action.result.EvnUslugaOper_id};
					this.FileUploadPanel.saveChanges();

					if ( options && typeof options.openChildWindow == 'function' && this.action == 'add' ) {
						options.openChildWindow();
					}
					else {
						var data = new Object();

						var index;
						var usluga_complex_id = base_form.findField('UslugaComplex_id').getValue();
						var usluga_complex_code = '';
						var usluga_complex_name = '';

						index = base_form.findField('UslugaComplex_id').getStore().findBy(function(rec) {
							if ( rec.get('UslugaComplex_id') == usluga_complex_id ) {
								return true;
							}
							else {
								return false;
							}
						});

						record = base_form.findField('UslugaComplex_id').getStore().getAt(index);
						if ( record ) {
							usluga_complex_code = record.get('UslugaComplex_Code');
							usluga_complex_name = record.get('UslugaComplex_Name');
						}

						var set_time = base_form.findField('EvnUslugaOper_setTime').getValue();

						if ( !set_time || set_time.length == 0 ) {
							set_time = '00:00';
						}

						data.evnUslugaData = {
							'accessType': 'edit',
							'EvnClass_SysNick': 'EvnUslugaOper',
							'EvnUsluga_Kolvo': base_form.findField('EvnUslugaOper_Kolvo').getValue(),
							'EvnUsluga_id': base_form.findField('EvnUslugaOper_id').getValue(),
							'EvnUsluga_setDate': base_form.findField('EvnUslugaOper_setDate').getValue(),
							'EvnUsluga_setTime': set_time,
							'EvnUslugaOper_IsVMT': base_form.findField('EvnUslugaOper_IsVMT').getValue(),
							'EvnUslugaOper_IsMicrSurg': base_form.findField('EvnUslugaOper_IsMicrSurg').getValue(),
							'EvnUslugaOper_IsOpenHeart': base_form.findField('EvnUslugaOper_IsOpenHeart').getValue(),
							'EvnUslugaOper_IsArtCirc': base_form.findField('EvnUslugaOper_IsArtCirc').getValue(),
							'Usluga_Code': usluga_complex_code,
							'Usluga_Name': usluga_complex_name
						};

						this.callback(data);
						this.hide();
					}
				}
				else {
					sw.swMsg.alert(langs('Ошибка'), langs('При сохранении произошли ошибки [Тип ошибки: 2]'));
				}
			}.createDelegate(this)
		});
	},
	setLpuSectionAndMedStaffFactFilter: function(isLoading) {

		var base_form = this.FormPanel.getForm();
		var evn_combo = base_form.findField('EvnUslugaOper_pid');
		
		var set_date_field = base_form.findField('EvnUslugaOper_setDate');
		var set_date = set_date_field.getValue();
		var usluga_place_id = base_form.findField('UslugaPlace_id').getValue();
		var lpu_id = base_form.findField('Lpu_uid').getValue();

		/*if (evn_combo.getStore().getCount() > 0 && set_date < evn_combo.getStore().getAt(0).get('Evn_setDate'))
		 {
		 alert(langs('Дата выполнения услуги не должна быть раньше даты начала лечения!'));
		 base_form.findField('EvnUslugaOper_setDate').setValue(evn_combo.getStore().getAt(0).get('Evn_setDate'));
		 this.FormPanel.getForm().findField('EvnUslugaOper_setDate').focus(true, 100);
		 }*/

		// Устанавливаем фильтр по дате для услуг
		if (getRegionNick() == 'perm' && this.useCase != 'OperBlock') {
			var ucat_cmb = base_form.findField('UslugaCategory_id');
			var xdate = new Date(2015, 0, 1);
			if (base_form.findField('EvnUslugaOper_setDate').getValue() >= xdate) {
				index = ucat_cmb.getStore().findBy(function (rec) {
					return (rec.get('UslugaCategory_SysNick') == 'gost2011');
				});
				ucat_rec = ucat_cmb.getStore().getAt(index);

				if (ucat_rec) {
					ucat_cmb.fireEvent('select', ucat_cmb, ucat_rec);
				}
			} else {
				index = ucat_cmb.getStore().findBy(function (rec) {
					return (rec.get('UslugaCategory_SysNick') == 'tfoms');
				});
				ucat_rec = ucat_cmb.getStore().getAt(index);

				if (ucat_rec) {
					ucat_cmb.fireEvent('select', ucat_cmb, ucat_rec);
				}
			}
		}

		this.reloadUslugaComplexField();

		var lpu_section_id = base_form.findField('LpuSection_uid').getValue();
		var med_staff_fact_id = base_form.findField('MedStaffFact_id').getValue();

		base_form.findField('LpuSection_uid').clearValue();
		base_form.findField('MedStaffFact_id').clearValue();

		var lpu_section_filter_params = new Object();
		var medstafffact_filter_params = new Object();

		if (this.parentClass.inlist(['EvnPL', 'EvnVizit'])) {
			lpu_section_filter_params.isPolka = true;
			medstafffact_filter_params.isPolka = true;
		}
		else {
			lpu_section_filter_params.isNotPolka = true;
			medstafffact_filter_params.isNotPolka = true;
		}

		if (typeof set_date == 'object') {
			lpu_section_filter_params.allowLowLevel = 'yes';
			lpu_section_filter_params.onDate = Ext.util.Format.date(set_date, 'd.m.Y');
			medstafffact_filter_params.allowLowLevel = 'yes';
			medstafffact_filter_params.onDate = Ext.util.Format.date(set_date, 'd.m.Y');
		}
		
		var localLoading = true;
		
		if (getRegionNick() == 'ekb' && usluga_place_id == 2 && lpu_id) {
			index = swLpuDispContractStore.findBy(function(rec) {
				if (rec.get('Lpu_oid') == lpu_id) {
					var ldc_set_date = Date.parseDate(rec.get('LpuDispContract_setDate'), 'd.m.Y');
					var ldc_dis_date = Date.parseDate(rec.get('LpuDispContract_disDate'), 'd.m.Y');
					if ( (Ext.isEmpty(ldc_set_date) || ldc_set_date <= set_date) && (Ext.isEmpty(ldc_dis_date) || ldc_dis_date >= set_date) ) {
						return true;
					}
				}
			});
			
			if ( index >= 0 ) {
				
				lpu_section_filter_params.isAliens = true;
				medstafffact_filter_params.isAliens = true;
				lpu_section_filter_params.ldcFilterDate = true;
				medstafffact_filter_params.ldcFilterDate = true;
				lpu_section_filter_params.Lpu_id = lpu_id;
				medstafffact_filter_params.Lpu_id = lpu_id;
				lpu_section_filter_params.isPolka = null;
				medstafffact_filter_params.isPolka = null;
				lpu_section_filter_params.isNotPolka = null;
				medstafffact_filter_params.isNotPolka = null;
				
			} else {
				
				localLoading = false;

				base_form.findField('LpuSection_uid').getStore().load({
					callback: function() {
						if ( base_form.findField('LpuSection_uid').getStore().getById(lpu_section_id) ) {
							base_form.findField('LpuSection_uid').setValue(lpu_section_id);
							if (!isLoading) base_form.findField('LpuSection_uid').fireEvent('change', base_form.findField('LpuSection_uid'), lpu_section_id);
							base_form.findField('UslugaComplex_id').getStore().baseParams.LpuSection_id = lpu_section_id;
						} 
						else if (base_form.findField('LpuSection_uid').getStore().getCount() && !isLoading) {
							lpu_section_id = base_form.findField('LpuSection_uid').getStore().getAt(0).get('LpuSection_id');
							base_form.findField('LpuSection_uid').setValue(lpu_section_id);
							base_form.findField('LpuSection_uid').fireEvent('change', base_form.findField('LpuSection_uid'), lpu_section_id);
							base_form.findField('UslugaComplex_id').getStore().baseParams.LpuSection_id = lpu_section_id;
						}
					}.createDelegate(this),
					params: {
						mode: 'combo',
						date: set_date ? Ext.util.Format.date(set_date, 'd.m.Y') : null,
						Lpu_id: lpu_id
					}
				});
				base_form.findField('MedStaffFact_id').getStore().load({
					callback: function() {
						if ( base_form.findField('MedStaffFact_id').getStore().getById(med_staff_fact_id) ) {
							base_form.findField('MedStaffFact_id').setValue(med_staff_fact_id);
						} 
					},
					params: {
						mode: 'combo',
						date: set_date ? Ext.util.Format.date(set_date, 'd.m.Y') : null,
						Lpu_id: lpu_id
					}
				});
			}
			
		} 
		
		if (localLoading) {
			
			setLpuSectionGlobalStoreFilter(lpu_section_filter_params);
			setMedStaffFactGlobalStoreFilter(medstafffact_filter_params);

			base_form.findField('LpuSection_uid').getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));
			base_form.findField('MedStaffFact_id').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));

			if (base_form.findField('LpuSection_uid').getStore().getById(lpu_section_id)) {
				base_form.findField('LpuSection_uid').setValue(lpu_section_id);
				if (!isLoading) base_form.findField('LpuSection_uid').fireEvent('change', base_form.findField('LpuSection_uid'), base_form.findField('LpuSection_uid').getValue());
				//base_form.findField('UslugaComplex_id').getStore().baseParams.LpuSection_id = lpu_section_id;
			}
			else if (getRegionNick() == 'ekb' && usluga_place_id.inlist([1,2]) && base_form.findField('LpuSection_uid').getStore().getCount() && lpu_id && !isLoading) {
				lpu_section_id = base_form.findField('LpuSection_uid').getStore().getAt(0).get('LpuSection_id');
				base_form.findField('LpuSection_uid').setValue(lpu_section_id);
				if (!isLoading) base_form.findField('LpuSection_uid').fireEvent('change', base_form.findField('LpuSection_uid'), lpu_section_id);
			}

			if (base_form.findField('MedStaffFact_id').getStore().getById(med_staff_fact_id)) {
				base_form.findField('MedStaffFact_id').setValue(med_staff_fact_id);
			}
		}

		setCurrentDateTime({
			dateField: base_form.findField('EvnUslugaOper_setDate'),
			loadMask: true,
			setDate: false,
			setTimeMaxValue: true,
			setDateMaxValue: true,
			setDateMinValue: false,
			setTime: false,
			timeField: base_form.findField('EvnUslugaOper_setTime'),
			windowId: 'EvnUslugaOperEditWindow'
		});
		base_form.findField('MedSpecOms_id').onChangeDateField(set_date_field, set_date);
		base_form.findField('LpuSectionProfile_id').onChangeDateField(set_date_field, set_date);
	},
	draggable: true,
	enableEdit: function(enable) {
		var base_form = this.FormPanel.getForm();
		var form_fields = new Array(
			'EvnUslugaOper_IsEndoskop',
			'EvnUslugaOper_IsKriogen',
			'EvnUslugaOper_IsLazer',
			'EvnUslugaOper_IsRadGraf',
			'EvnUslugaOper_Kolvo',
			'EvnUslugaOper_pid',
			'EvnUslugaOper_setDate',
			'EvnUslugaOper_setTime',
			'OperDiff_id',
			'TreatmentConditionsType_id',
			'EvnUslugaOper_IsVMT',
			'EvnUslugaOper_IsMicrSurg',
			'EvnUslugaOper_IsOpenHeart',
			//'EvnUslugaOper_IsArtCirc',
			'OperType_id',
			'PayType_id',
			'UslugaCategory_id',
			'UslugaComplex_id',
			'UslugaComplexTariff_id',
			'UslugaPlace_id'
			,'MedSpecOms_id'
			,'LpuSectionProfile_id'
			,'Diag_id'
			,'DiagSetClass_id'
		);
		var i;

		for ( i = 0; i < form_fields.length; i++ ) {
			if ( enable ) {
				base_form.findField(form_fields[i]).enable();
			}
			else {
				base_form.findField(form_fields[i]).disable();
			}
		}

		if ( enable ) {
			this.buttons[0].show();
		}
		else {
			this.buttons[0].hide();
		}
	},
	height: 450,
	id: 'EvnUslugaOperEditWindow',
	initComponent: function() {
		var win = this;

		var uslugaCategoryParams = null;
		switch (getRegionNick()) {
			case 'kz':
				uslugaCategoryParams = {params: {where: "where UslugaCategory_SysNick in ('classmedus', 'MedOp')"}};
				break;
			case 'kaluga':
				uslugaCategoryParams = {params: {where: "where UslugaCategory_SysNick in ('gost2011', 'lpusectiontree')"}};
				break;
		}

		this.FileUploadPanel = new sw.Promed.FileUploadPanel({
			id: this.id+'_FileUploadPanel',
			win: this,
			buttonAlign: 'left',
			maxHeight: 150,
			buttonLeftMargin: 100,
			labelWidth: 100,
			commentTextfieldWidth: 250,
			folder: 'evnmedia/',
			style: 'background: transparent',
			dataUrl: '/?c=EvnMediaFiles&m=loadEvnMediaFilesListGrid',
			saveUrl: '/?c=EvnMediaFiles&m=uploadFile',
			saveChangesUrl: '/?c=EvnMediaFiles&m=saveChanges',
			deleteUrl: '/?c=EvnMediaFiles&m=deleteFile'
		});

		this.FilePanel = new Ext.Panel({
			title: langs('7. Файлы'),
			style: 'margin-bottom: 0.5em;',
			id: 'EUOperEF_FileTab',
			border: true,
			collapsible: true,
			autoHeight: true,
			titleCollapse: true,
			animCollapse: false,
			items: [
				this.FileUploadPanel
			]
		});

		this.EvnXmlPanel = new sw.Promed.EvnXmlPanel({
			autoHeight: true,
			border: true,
			collapsible: true,
			loadMask: {},
			id: 'EUOperEF_TemplPanel',
			layout: 'form',
			title: langs('6. Протокол операции'),
			style: 'margin-bottom: 0.5em;',
			ownerWin: this,
			options: {
				XmlType_id: sw.Promed.EvnXml.OPERATION_PROTOCOL_TYPE_ID, // только протоколы операции
				EvnClass_id: 43 // документы и шаблоны только категории оперативные услуги
			},
			onAfterLoadData: function(panel){
				var bf = this.FormPanel.getForm();
				bf.findField('XmlTemplate_id').setValue(panel.getXmlTemplateId());
				panel.expand();
				this.syncSize();
				this.doLayout();
			}.createDelegate(this),
			onAfterClearViewForm: function(panel){
				var bf = this.FormPanel.getForm();
				bf.findField('XmlTemplate_id').setValue(null);
			}.createDelegate(this),
			// определяем метод, который должен создать посещение перед созданием документа с помощью указанного метода
			onBeforeCreate: function (panel, method, params) {
				if (!panel || !method || typeof panel[method] != 'function') {
					return false;
				}
				var base_form = this.FormPanel.getForm();
				var evn_id_field = base_form.findField('EvnUslugaOper_id');
				var evn_id = evn_id_field.getValue();
				if (evn_id && evn_id > 0) {
					// услуга была создана ранее
					// все базовые параметры уже должно быть установлены
					// а вот не факт
					panel.setBaseParams({
						userMedStaffFact: sw.Promed.MedStaffFactByUser.last,
						UslugaComplex_id: base_form.findField('UslugaComplex_id').getValue(),
						Server_id: base_form.findField('Server_id').getValue(),
						Evn_id: evn_id_field.getValue()
					});
					panel[method](params);
				} else {
					this.doSave({
						openChildWindow: function() {
							panel.setBaseParams({
								userMedStaffFact: sw.Promed.MedStaffFactByUser.last,
								UslugaComplex_id: base_form.findField('UslugaComplex_id').getValue(),
								Server_id: base_form.findField('Server_id').getValue(),
								Evn_id: evn_id_field.getValue()
							});
							panel[method](params);
						}.createDelegate(this)
					});
				}
				return true;
			}.createDelegate(this)
		});

		this.EvnXmlPanelNarcosis = new sw.Promed.EvnXmlPanel({
			autoHeight: true,
			border: true,
			collapsible: true,
			loadMask: {},
			layout: 'form',
			title: langs('7. Протокол анестезии'),
			style: 'margin-bottom: 0.5em;',
			hidden:true,
			ownerWin: this,
			options: {
				XmlType_id: sw.Promed.EvnXml.NARCOSIS_PROTOCOL_TYPE_ID, // только протоколы анестезии
				EvnClass_id: 43 // документы и шаблоны только категории оперативные услуги
			},
			onAfterLoadData: function(panel){
				var bf = this.FormPanel.getForm();
				bf.findField('XmlTemplate_id').setValue(panel.getXmlTemplateId());
				panel.expand();
				this.syncSize();
				this.doLayout();
			}.createDelegate(this),
			onAfterClearViewForm: function(panel){
				var bf = this.FormPanel.getForm();
				bf.findField('XmlTemplate_id').setValue(null);
			}.createDelegate(this),
			// определяем метод, который должен создать посещение перед созданием документа с помощью указанного метода
			onBeforeCreate: function (panel, method, params) {
				if (!panel || !method || typeof panel[method] != 'function') {
					return false;
				}
				var base_form = this.FormPanel.getForm();
				var evn_id_field = base_form.findField('EvnUslugaOper_id');
				var evn_id = evn_id_field.getValue();
				if (evn_id && evn_id > 0) {
					// услуга была создана ранее
					// все базовые параметры уже должно быть установлены
					// а вот не факт
					panel.setBaseParams({
						userMedStaffFact: sw.Promed.MedStaffFactByUser.last,
						UslugaComplex_id: base_form.findField('UslugaComplex_id').getValue(),
						Server_id: base_form.findField('Server_id').getValue(),
						Evn_id: evn_id_field.getValue()
					});
					panel[method](params);
				} else {
					this.doSave({
						openChildWindow: function() {
							panel.setBaseParams({
								userMedStaffFact: sw.Promed.MedStaffFactByUser.last,
								UslugaComplex_id: base_form.findField('UslugaComplex_id').getValue(),
								Server_id: base_form.findField('Server_id').getValue(),
								Evn_id: evn_id_field.getValue()
							});
							panel[method](params);
						}
					});
				}
				return true;
			}.createDelegate(this)
		});

		this.FormPanel = new Ext.form.FormPanel({
			autoScroll: true,
			bodyBorder: false,
			border: false,
			frame: false,
			labelAlign: 'right',
			labelWidth: 130,
			layout: 'form',
			reader: new Ext.data.JsonReader({
				success: Ext.emptyFn
			}, [
				{name: 'accessType'},
				{name: 'XmlTemplate_id'},
				{name: 'EvnUslugaOper_id'},
				{name: 'EvnUslugaOper_IsEndoskop'},
				{name: 'EvnUslugaOper_IsKriogen'},
				{name: 'EvnUslugaOper_IsLazer'},
				{name: 'EvnUslugaOper_IsRadGraf'},
				{name: 'EvnUslugaOper_Kolvo'},
				{name: 'EvnUslugaOper_pid'},
				{name: 'EvnDirection_id'},
				{name: 'EvnUslugaOper_Price'},
				{name: 'EvnUslugaOper_rid'},
				{name: 'EvnUslugaOper_setDate'},
				{name: 'EvnUslugaOper_setTime'},
				{name: 'EvnUslugaOper_disDate'},
				{name: 'EvnUslugaOper_disTime'},
				{name: 'Lpu_uid'},
				{name: 'LpuSection_uid'},
				{name: 'MedStaffFact_id'},
				{name: 'Morbus_id'},
				{name: 'Org_uid'},
				{name: 'OperDiff_id'},
				{name: 'TreatmentConditionsType_id'},
				{name: 'EvnUslugaOper_IsVMT'},
				{name: 'EvnUslugaOper_IsMicrSurg'},
				{name: 'EvnUslugaOper_IsOpenHeart'},
				{name: 'EvnUslugaOper_IsArtCirc'},
				{name: 'OperType_id'},
				{name: 'PayType_id'},
				{name: 'Person_id'},
				{name: 'PersonEvn_id'},
				{name: 'Server_id'},
				{name: 'UslugaComplex_id'},
				{name: 'UslugaComplexTariff_id'},
				{name: 'DiagSetClass_id'},
				{name: 'Diag_id'},
				{name: 'EvnPrescr_id'},
				{name: 'LpuSectionProfile_id'},
				{name: 'MedSpecOms_id'},
				{name: 'EvnUslugaCommon_pid_Name'},
				{name: 'UslugaPlace_id'},
				{name: 'EvnUslugaOper_BallonBegDate'},
				{name: 'EvnUslugaOper_BallonBegTime'},
				{name: 'EvnUslugaOper_CKVEndDate'},
				{name: 'EvnUslugaOper_CKVEndTime'},
				{name: 'EvnUslugaOper_IsOperationDeath'},
				{name: 'UslugaMedType_id'}
			]),
			region: 'center',
			url: '/?c=EvnUsluga&m=saveEvnUslugaOper',
			items: [{
				name: 'accessType',
				value: '',
				xtype: 'hidden'
			}, {
				name: 'XmlTemplate_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'Evn_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'EvnUslugaOper_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'EvnUslugaOper_rid',
				value: 0,
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
				name: 'Morbus_id',
				value: 0,
				xtype: 'hidden'
			},
			{
				name:'IsCardioCheck',
				value:0,
				xtype:'hidden'
			},
				new sw.Promed.Panel({
					autoHeight: true,
					bodyStyle: 'padding-top: 0.5em;',
					border: true,
					collapsible: true,
					id: 'EUOperEF_EvnUslugaPanel',
					layout: 'form',
					style: 'margin-bottom: 0.5em;',
					title: langs('1. Услуга'),
					items: [{
						displayField: 'Evn_Name',
						//editable: false,
						allowBlank: false,
						enableKeyEvents: true,
						fieldLabel: langs('Отделение'),
						hiddenName: 'EvnUslugaOper_pid',
						lastQuery: '',
						listeners: {
							'change': function (combo, newValue, oldValue) {
								if (this.IsPriem) {
									return false;
								}
								var base_form = this.FormPanel.getForm(),
									index,
									record = combo.getStore().getById(newValue),
									lpu_section;

								if (record) {
									var curDate = record.get('Evn_setDate');
									if(getRegionNick() == 'kz'){
										var localCompDate = new Date();
										curDate = localCompDate.format('d.m.Y');
									}
									base_form.findField('EvnUslugaOper_setDate').setValue(curDate);
									base_form.findField('EvnUslugaOper_setDate').fireEvent('change', base_form.findField('EvnUslugaOper_setDate'), curDate, 0);
									
									base_form.findField('UslugaPlace_id').setValue(1);
									base_form.findField('UslugaPlace_id').fireEvent('change', base_form.findField('UslugaPlace_id'), 1, 0);

									if (getRegionNick() == 'ekb' && this.parentClass.inlist(['EvnVizit', 'EvnSection', 'EvnPL', 'EvnPS'])) {
										if (Ext.isEmpty(base_form.findField('Diag_id').getValue())) {
											var diag_id = record.get('Diag_id');

											base_form.findField('Diag_id').getStore().load({
												callback: function () {
													base_form.findField('Diag_id').setValue(diag_id);
													base_form.findField('Diag_id').onChange(base_form.findField('Diag_id'), base_form.findField('Diag_id').getValue());
												},
												params: {where: "where DiagLevel_id = 4 and Diag_id = " + diag_id}
											});
										} else {
											base_form.findField('Diag_id').onChange(base_form.findField('Diag_id'), base_form.findField('Diag_id').getValue());
										}
									}

									index = base_form.findField('LpuSection_uid').getStore().findBy(function (rec) {
										return (rec.get('LpuSection_id') == record.get('LpuSection_id'));
									});

									if (index >= 0) {
										base_form.findField('LpuSection_uid').fireEvent('beforeselect', base_form.findField('LpuSection_uid'), base_form.findField('LpuSection_uid').getStore().getAt(index));
										base_form.findField('LpuSection_uid').setValue(record.get('LpuSection_id'));
										base_form.findField('LpuSection_uid').fireEvent('change', base_form.findField('LpuSection_uid'), base_form.findField('LpuSection_uid').getValue());
									}

									if (record.get('LpuSection_id')) {
										lpu_section = record.get('LpuSection_id');
									}
									if(getRegionNick() != 'kz'){ // #143856 Для Казахстана заполнять врача не нужно
										index = base_form.findField('MedStaffFact_id').getStore().findBy(function (rec) {
											return (rec.get('MedStaffFact_id') == record.get('MedStaffFact_id'));
										});
										if (index >= 0) {
											base_form.findField('MedStaffFact_id').setValue(record.get('MedStaffFact_id'));
											this.setDefaultLpuSectionProfile();
										}
									}
								}

								var Store = base_form.findField('MedStaffFact_id').getStore();

								Store.each(function (s) {
									if (!Ext.isEmpty(lpu_section) && s.get('LpuSection_id') === lpu_section.toString()) {
										s.set('SortVal', 1);
									} else {
										s.set('SortVal', 2);
									}
									s.commit();
								});
								base_form.findField('MedStaffFact_id').getStore().sort('MedPersonal_Fio');
								base_form.findField('MedStaffFact_id').getStore().sort('SortVal');

								base_form.findField('MedStaffFact_id').getStore().applySort();
							}.createDelegate(this),
							'keydown': function (inp, e) {
								if (e.shiftKey == true && e.getKey() == Ext.EventObject.TAB) {
									e.stopEvent();
									this.buttons[this.buttons.length - 1].focus();
								}

								else if (e.getKey() == Ext.EventObject.DELETE) {
									e.stopEvent();
									inp.clearValue();
								}
							}.createDelegate(this)
						},
						listWidth: 600,
						mode: 'local',
						store: new Ext.data.JsonStore({
							autoLoad: false,
							fields: [
								{name: 'Evn_id', type: 'int'},
								{name: 'MedStaffFact_id', type: 'int'},
								{name: 'LpuSection_id', type: 'int'},
								{name: 'MedPersonal_id', type: 'int'},
								{name: 'Evn_Name', type: 'string'},
								{name: 'Evn_setDate', type: 'date', dateFormat: 'd.m.Y'},
								{name: 'ServiceType_SysNick', type: 'string'},
								{name: 'VizitType_SysNick', type: 'string'},
								{name: 'Diag_id', type: 'int'},
								{name: 'IsPriem', type: 'int'}
							],
							id: 'Evn_id'
						}),
						tabIndex: TABINDEX_EUOPEREF + 1,
						tpl: new Ext.XTemplate(
							'<tpl for="."><div class="x-combo-list-item">',
							'{Evn_Name}&nbsp;',
							'</div></tpl>'
						),
						//triggerAction: 'all',
						valueField: 'Evn_id',
						width: 500,
						xtype: 'swbaselocalcombo'
					}, {
						name: 'EvnDirection_id',
						xtype: 'hidden'
					}, {
						border: false,
						layout: 'column',
						items: [{
							border: false,
							layout: 'form',
							labelWidth: 180,
							items: [{
								allowBlank: false,
								fieldLabel: langs('Дата начала выполнения'),
								format: 'd.m.Y',
								id: 'EUOperEF_EvnUslugaOper_setDate',
								listeners: {
									'change': function (field, newValue, oldValue) {
										if (blockedDateAfterPersonDeath('personpanelid', 'EUOperEF_PersonInformationFrame', field, newValue, oldValue)) return;

										this.setLpuSectionAndMedStaffFactFilter();
										this.setDisDT();
									}.createDelegate(this),
									'keydown': function (inp, e) {
										if (e.shiftKey == true && e.getKey() == Ext.EventObject.TAB && this.FormPanel.getForm().findField('EvnUslugaOper_pid').disabled) {
											e.stopEvent();
											this.buttons[this.buttons.length - 1].focus();
										}
									}.createDelegate(this)
								},
								name: 'EvnUslugaOper_setDate',
								plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
								tabIndex: TABINDEX_EUOPEREF + 2,
								width: 100,
								xtype: 'swdatefield'
							}]
						}, {
							border: false,
							layout: 'form',
							labelWidth: 50,
							items: [{
								fieldLabel: langs('Время'),
								listeners: {
									'keydown': function (inp, e) {
										if (e.getKey() == Ext.EventObject.F4) {
											e.stopEvent();
											inp.onTriggerClick();
										}
									},
									'change': function () {
										var base_form = win.FormPanel.getForm();
										var time_field = base_form.findField('EvnUslugaOper_setTime');
										setCurrentDateTime({
											callback: function () {
												win.setDisDT();
											},
											dateField: base_form.findField('EvnUslugaOper_setDate'),
											loadMask: true,
											setDate: false,
											setTimeMaxValue: true,
											setDateMaxValue: true,
											setDateMinValue: false,
											setTime: false,
											timeField: time_field,
											windowId: 'EvnUslugaOperEditWindow'
										});
									}
								},
								name: 'EvnUslugaOper_setTime',
								onTriggerClick: function () {
									var base_form = this.FormPanel.getForm();
									var time_field = base_form.findField('EvnUslugaOper_setTime');

									if (time_field.disabled) {
										return false;
									}

									setCurrentDateTime({
										callback: function () {
											base_form.findField('EvnUslugaOper_setDate').fireEvent('change', base_form.findField('EvnUslugaOper_setDate'), base_form.findField('EvnUslugaOper_setDate').getValue());
										}.createDelegate(this),
										dateField: base_form.findField('EvnUslugaOper_setDate'),
										loadMask: true,
										setDate: true,
										setTimeMaxValue: true,
										setDateMaxValue: true,
										setDateMinValue: false,
										setTime: true,
										timeField: time_field,
										windowId: 'EvnUslugaOperEditWindow'
									});
								}.createDelegate(this),
								plugins: [new Ext.ux.InputTextMask('99:99', true)],
								tabIndex: TABINDEX_EUOPEREF + 3,
								validateOnBlur: false,
								width: 60,
								xtype: 'swtimefield'
							}]
						}, {
							layout: 'form',
							style: 'padding-left: 50px',
							border: false,
							items: [{
								xtype: 'button',
								id: 'EUOperEF_ToggleVisibleDisDTBtn',
								text: langs('Уточнить период выполнения'),
								handler: function () {
									this.toggleVisibleDisDTPanel();
								}.createDelegate(this)
							}]
						}]
					}, {
						border: false,
						layout: 'column',
						id: 'EUOperEF_EvnUslugaDisDTPanel',
						items: [{
							border: false,
							layout: 'form',
							labelWidth: 180,
							items: [{
								fieldLabel: langs('Дата окончания выполнения'),
								format: 'd.m.Y',
								id: 'EUOperEF_EvnUslugaOper_disDate',
								name: 'EvnUslugaOper_disDate',
								plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
								tabIndex: TABINDEX_EUOPEREF + 3,
								width: 100,
								xtype: 'swdatefield'
							}]
						}, {
							border: false,
							layout: 'form',
							labelWidth: 50,
							items: [{
								fieldLabel: langs('Время'),
								listeners: {
									'keydown': function (inp, e) {
										if (e.getKey() == Ext.EventObject.F4) {
											e.stopEvent();
											inp.onTriggerClick();
										}
									}
								},
								name: 'EvnUslugaOper_disTime',
								onTriggerClick: function () {
									var base_form = this.FormPanel.getForm();
									var time_field = base_form.findField('EvnUslugaOper_disTime');

									if (time_field.disabled) {
										return false;
									}

									setCurrentDateTime({
										dateField: base_form.findField('EvnUslugaOper_disDate'),
										loadMask: true,
										setDate: true,
										setTimeMaxValue: true,
										setDateMaxValue: true,
										setDateMinValue: false,
										setTime: true,
										timeField: time_field,
										windowId: 'EvnUslugaOperEditWindow'
									});
								}.createDelegate(this),
								plugins: [new Ext.ux.InputTextMask('99:99', true)],
								tabIndex: TABINDEX_EUOPEREF + 4,
								validateOnBlur: false,
								width: 60,
								xtype: 'swtimefield'
							}]
						}, {
							layout: 'form',
							border: false,
							items: [{
								xtype: 'button',
								id: 'EUOperEF_DTCopyBtn',
								text: '=',
								handler: function () {
									var base_form = win.FormPanel.getForm();

									base_form.findField('EvnUslugaOper_disDate').setValue(base_form.findField('EvnUslugaOper_setDate').getValue());
									base_form.findField('EvnUslugaOper_disTime').setValue(base_form.findField('EvnUslugaOper_setTime').getValue());
								}.createDelegate(this)
							}]
						}]
					},{
						layout: 'form',
						style: 'padding-left: 50px',
						border: false,
						hidden: false,
						id: 'EUOperEF_ToggleVisibleExecutionPanel',
						items: [{
							xtype: 'button',
							id: 'EUOperEF_ToggleVisibleExecutionPanelBtn',
							text: langs('Уточнить объем выполнения'),
							handler: function() {
								var base_form = win.FormPanel.getForm();
								if(!base_form.findField('UslugaExecutionType_id').getValue())
									base_form.findField('UslugaExecutionType_id').setValue(1);
								this.toggleVisibleExecutionPanel();

							}.createDelegate(this)
						}]
					},{
						border: false,
						layout: 'form',
						id: 'EUOperEF_EvnUslugaExecutionPanel',
						items:[
							{
								xtype: 'uslugaexecutiontyperadiogroup',
								fieldLabel: langs('Объём выполнения'),
								name: 'UslugaExecutionType_id',
								listeners: {
									change: function(cmp, item){
										var base_form = win.FormPanel.getForm();
										if(item){
											base_form.findField('UslugaExecutionReason_id').setAllowBlank(item.value == 1);
											base_form.findField('UslugaExecutionReason_id').setDisabled(item.value == 1);
											if(item.value == 1){
												base_form.findField('UslugaExecutionReason_id').reset()
											}
										}

									}
								}
							},
							{
								comboSubject: 'UslugaExecutionReason',
								xtype: 'swcommonsprcombo',
								hiddenName: 'UslugaExecutionReason_id',
								valueField: 'UslugaExecutionReason_id',
								showCodefield: false,
								disabled: true,
								width: 400,
								fieldLabel: langs('Причина частичного выполнения (невыполнения)'),
								tpl: new Ext.XTemplate('<tpl for="."><div class="x-combo-list-item">{UslugaExecutionReason_Name}&nbsp;</div></tpl>')
							}
						]
					}, {
						autoHeight: true,
						style: 'padding: 2px 0px 0px 0px;',
						xtype: 'fieldset',
						items: [new sw.Promed.SwUslugaPlaceCombo({
							allowBlank: false,
							hiddenName: 'UslugaPlace_id',
							lastQuery: '',
							listeners: {
								'change': function (combo, newValue, oldValue) {
									var base_form = this.FormPanel.getForm();
									var record = combo.getStore().getById(newValue);

									var lpu_combo = base_form.findField('Lpu_uid');
									var lpu_section_combo = base_form.findField('LpuSection_uid');
									var med_personal_combo = base_form.findField('MedStaffFact_id');
									var org_combo = base_form.findField('Org_uid');

									lpu_combo.clearValue();
									lpu_section_combo.clearValue();
									med_personal_combo.clearValue();
									org_combo.clearValue();

									lpu_combo.setAllowBlank(true);
									lpu_section_combo.setAllowBlank(true);
									med_personal_combo.setAllowBlank(true);
									org_combo.setAllowBlank(true);

									if (!record) {
										lpu_combo.disable();
										lpu_section_combo.disable();
										med_personal_combo.disable();
										org_combo.disable();
									}
									else if (record.get('UslugaPlace_Code') == 1) {
										lpu_combo.disable();
										lpu_section_combo.enable();
										med_personal_combo.enable();
										org_combo.disable();

										lpu_section_combo.setAllowBlank(false);
										med_personal_combo.setAllowBlank(false);
									}
									else if (record.get('UslugaPlace_Code') == 2) {
										lpu_combo.enable();
										if (getRegionNick() == 'ekb') {
											lpu_section_combo.enable();
											med_personal_combo.enable();
										} else {
											lpu_section_combo.disable();
											med_personal_combo.disable();
										}
										org_combo.disable();

										lpu_combo.setAllowBlank(false);
									}
									else if (record.get('UslugaPlace_Code') == 3) {
										lpu_combo.disable();
										lpu_section_combo.disable();
										med_personal_combo.disable();
										org_combo.enable();

										org_combo.setAllowBlank(false);
									}
									else {
										lpu_combo.disable();
										lpu_section_combo.disable();
										med_personal_combo.disable();
										org_combo.disable();
									}
									this.setLpuSectionAndMedStaffFactFilter();
									var code = (record && record.get('UslugaPlace_Code')) || null;
									base_form.findField('MedSpecOms_id').onChangeUslugaPlaceField(combo, code);
									base_form.findField('LpuSectionProfile_id').onChangeUslugaPlaceField(combo, code);
								}.createDelegate(this)
							},
							tabIndex: TABINDEX_EUOPEREF + 4,
							width: 300
						}), {
							hiddenName: 'LpuSection_uid',
							id: 'EUOperEF_LpuSectionCombo',
							lastQuery: '',
							linkedElements: [
								'EUOperEF_MedPersonalCombo'
							],
							linkedElementParams: {
								additionalFilterFn: checkSlaveRecordForLpuSectionService,
								ignoreFilter: false
							},
							tabIndex: TABINDEX_EUOPEREF + 5,
							width: 500,
							listeners: {
								'beforeselect': function (combo, record, index) {
									var base_form = this.FormPanel.getForm();

									if (
										typeof record == 'object'
										&& Ext.isEmpty(record.get('LpuSectionServiceList'))
										&& (
											record.get('LpuSectionProfile_SysNick') == 'priem'
											|| (getRegionNick() == 'kareliya' && record.get('LpuSectionProfile_Code') == '160')
										)
									) {
										combo.linkedElementParams.ignoreFilter = true;
									}
									else {
										combo.linkedElementParams.ignoreFilter = false;
									}
									if ( getRegionNick() == 'buryatiya' ) {
										base_form.findField('UslugaComplex_id').clearValue();
									}
								}.createDelegate(this),
								'change': function (o, n, s) {

									var base_form = win.FormPanel.getForm();
									var Store = base_form.findField('MedStaffFact_id').getStore();
									Store.setDefaultSort();
									Store.each(function (s) {
										if (s.get('LpuSection_id') === n.toString())s.set('SortVal', 1);
										else s.set('SortVal', 2);
										s.commit();
									});
									base_form.findField('MedStaffFact_id').getStore().sort('MedPersonal_Fio');
									base_form.findField('MedStaffFact_id').getStore().sort('SortVal');
									base_form.findField('MedStaffFact_id').getStore().applySort();

									this.setUslugaComplexPartitionCodeList(base_form.findField('PayType_id').getFieldValue('PayType_SysNick'));
								}.createDelegate(this)
							},
							xtype: 'swlpusectionglobalcombo'
						}, {
							hiddenName: 'LpuSectionProfile_id',
							hidden: true,
							lastQuery: '',
							tabIndex: TABINDEX_EUOPEREF + 6,
							width: 500,
							xtype: 'swlpusectionprofilewithfedcombo'
						}, {
							displayField: 'Org_Name',
							editable: false,
							enableKeyEvents: true,
							fieldLabel: langs('ЛПУ'),
							hiddenName: 'Lpu_uid',
							listeners: {
								'keydown': function (inp, e) {
									if (inp.disabled) {
										return;
									}

									if (e.F4 == e.getKey()) {
										if (e.browserEvent.stopPropagation) {
											e.browserEvent.stopPropagation();
										}
										else {
											e.browserEvent.cancelBubble = true;
										}

										if (e.browserEvent.preventDefault) {
											e.browserEvent.preventDefault();
										}
										else {
											e.browserEvent.returnValue = false;
										}

										e.returnValue = false;

										if (Ext.isIE) {
											e.browserEvent.keyCode = 0;
											e.browserEvent.which = 0;
										}
										inp.onTrigger1Click();
										return false;
									}
								},
								'keyup': function (inp, e) {
									if (e.F4 == e.getKey()) {
										if (e.browserEvent.stopPropagation) {
											e.browserEvent.stopPropagation();
										}
										else {
											e.browserEvent.cancelBubble = true;
										}

										if (e.browserEvent.preventDefault) {
											e.browserEvent.preventDefault();
										}
										else {
											e.browserEvent.returnValue = false;
										}

										e.returnValue = false;

										if (Ext.isIE) {
											e.browserEvent.keyCode = 0;
											e.browserEvent.which = 0;
										}
										return false;
									}
								}
							},
							mode: 'local',
							onTrigger1Click: function () {
								var base_form = this.FormPanel.getForm();
								var combo = base_form.findField('Lpu_uid');

								if (combo.disabled) {
									return;
								}

								var usluga_place_combo = base_form.findField('UslugaPlace_id');
								var record = usluga_place_combo.getStore().getById(usluga_place_combo.getValue());

								if (!record) {
									return false;
								}

								var org_type = 'lpu';

								getWnd('swOrgSearchWindow').show({
									onlyFromDictionary: true,
									onSelect: function (org_data) {
										if (org_data.Lpu_id > 0) {
											combo.getStore().loadData([{
												Org_id: org_data.Org_id,
												Lpu_id: org_data.Lpu_id,
												Org_Name: org_data.Org_Name
											}]);
											combo.setValue(org_data.Lpu_id);
											getWnd('swOrgSearchWindow').hide();
											win.setLpuSectionAndMedStaffFactFilter();
										}
									},
									onClose: function () {
										combo.focus(true, 200)
									},
									object: org_type
								});
							}.createDelegate(this),
							store: new Ext.data.JsonStore({
								autoLoad: false,
								fields: [
									{name: 'Org_id', type: 'int'},
									{name: 'Lpu_id', type: 'int'},
									{name: 'Org_Name', type: 'string'}
								],
								key: 'Lpu_id',
								sortInfo: {
									field: 'Org_Name'
								},
								url: C_ORG_LIST
							}),
							tabIndex: TABINDEX_EUOPEREF + 7,
							tpl: new Ext.XTemplate(
								'<tpl for="."><div class="x-combo-list-item">',
								'{Org_Name}',
								'</div></tpl>'
							),
							trigger1Class: 'x-form-search-trigger',
							triggerAction: 'none',
							valueField: 'Lpu_id',
							width: 500,
							xtype: 'swbaseremotecombo'
						}, {
							displayField: 'Org_Name',
							editable: false,
							enableKeyEvents: true,
							fieldLabel: langs('Другая организация'),
							hiddenName: 'Org_uid',
							listeners: {
								'keydown': function (inp, e) {
									if (inp.disabled) {
										return;
									}

									if (e.F4 == e.getKey()) {
										if (e.browserEvent.stopPropagation) {
											e.browserEvent.stopPropagation();
										}
										else {
											e.browserEvent.cancelBubble = true;
										}

										if (e.browserEvent.preventDefault) {
											e.browserEvent.preventDefault();
										}
										else {
											e.browserEvent.returnValue = false;
										}

										e.returnValue = false;

										if (Ext.isIE) {
											e.browserEvent.keyCode = 0;
											e.browserEvent.which = 0;
										}
										inp.onTrigger1Click();
										return false;
									}
								},
								'keyup': function (inp, e) {
									if (e.F4 == e.getKey()) {
										if (e.browserEvent.stopPropagation) {
											e.browserEvent.stopPropagation();
										}
										else {
											e.browserEvent.cancelBubble = true;
										}

										if (e.browserEvent.preventDefault) {
											e.browserEvent.preventDefault();
										}
										else {
											e.browserEvent.returnValue = false;
										}

										e.returnValue = false;

										if (Ext.isIE) {
											e.browserEvent.keyCode = 0;
											e.browserEvent.which = 0;
										}
										return false;
									}
								}
							},
							mode: 'local',
							onTrigger1Click: function () {
								var base_form = this.FormPanel.getForm();
								var combo = base_form.findField('Org_uid');

								if (combo.disabled) {
									return;
								}

								var usluga_place_combo = base_form.findField('UslugaPlace_id');
								var usluga_place_id = usluga_place_combo.getValue();
								var record = usluga_place_combo.getStore().getById(usluga_place_id);

								if (!record) {
									return false;
								}

								var org_type = 'org';

								getWnd('swOrgSearchWindow').show({
									onSelect: function (org_data) {
										if (org_data.Org_id > 0) {
											combo.getStore().loadData([{
												Org_id: org_data.Org_id,
												Org_Name: org_data.Org_Name
											}]);
											combo.setValue(org_data.Org_id);
											getWnd('swOrgSearchWindow').hide();
										}
									},
									onClose: function () {
										combo.focus(true, 200)
									},
									object: org_type
								});
							}.createDelegate(this),
							store: new Ext.data.JsonStore({
								autoLoad: false,
								fields: [
									{name: 'Org_id', type: 'int'},
									{name: 'Org_Name', type: 'string'}
								],
								key: 'Org_id',
								sortInfo: {
									field: 'Org_Name'
								},
								url: C_ORG_LIST
							}),
							tabIndex: TABINDEX_EUOPEREF + 7,
							tpl: new Ext.XTemplate(
								'<tpl for="."><div class="x-combo-list-item">',
								'{Org_Name}',
								'</div></tpl>'
							),
							trigger1Class: 'x-form-search-trigger',
							triggerAction: 'none',
							valueField: 'Org_id',
							width: 500,
							xtype: 'swbaseremotecombo'
						}, {
							hiddenName: 'MedSpecOms_id',
							hidden: true,
							lastQuery: '',
							tabIndex: TABINDEX_EUOPEREF + 7,
							width: 500,
							xtype: 'swmedspecomswithfedcombo'
						}]
					}, {
						autoHeight: true,
						style: 'padding: 2px 0px 0px 0px;',
						title: langs('Врач, выполнивший услугу'),
						xtype: 'fieldset',
						items: [{
							fieldLabel: langs('Код и ФИО врача'),
							hiddenName: 'MedStaffFact_id',
							id: 'EUOperEF_MedPersonalCombo',
							lastQuery: '',
							listWidth: 750,
							parentElementId: 'EUOperEF_LpuSectionCombo',
							tabIndex: TABINDEX_EUOPEREF + 8,
							width: 500,
							xtype: 'swmedstafffactglobalcombo'
						}]
					}, {
						allowBlank: false,
						hiddenName: 'PayType_id',
						listeners: {
							'select': function (combo, record) {
								var base_form = this.FormPanel.getForm();
								var usluga_category_combo = base_form.findField('UslugaCategory_id');
								if (getRegionNick() == 'buryatiya') {
									usluga_category_combo.lastQuery = "";
									usluga_category_combo.getStore().clearFilter();
									if (record && record.get('PayType_SysNick') == 'oms') {
										usluga_category_combo.setFieldValue('UslugaCategory_SysNick', 'gost2011');
										usluga_category_combo.fireEvent('select', usluga_category_combo, usluga_category_combo.getStore().getAt(usluga_category_combo.getStore().findBy(function (rec) {
											return (rec.get('UslugaCategory_SysNick') == 'gost2011');
										})));
									} else {
										usluga_category_combo.clearValue();
										usluga_category_combo.fireEvent('select', usluga_category_combo, null);
									}
								}
								var sysnick = (record && record.get('PayType_SysNick')) || null;
								base_form.findField('MedSpecOms_id').onChangePayTypeField(combo, sysnick);
								base_form.findField('LpuSectionProfile_id').onChangePayTypeField(combo, sysnick);
								this.setUslugaComplexPartitionCodeList(sysnick);
							}.createDelegate(this),
							'change': function (combo, newValue, oldValue) {
								this.loadUslugaComplexTariffCombo();
								return true;
							}.createDelegate(this)
						},
						tabIndex: TABINDEX_EUOPEREF + 9,
						width: 300,
						fieldLabel: getRegionNick() == 'kz' ? 'Источник финансирования' : 'Вид оплаты',
						xtype: 'swpaytypecombo'
					}, {
						hiddenName: 'EvnPrescr_id',
						listeners: {
							'change': function (combo, newValue) {
								combo.applyChanges(newValue);
							}.createDelegate(this)
						},
						tabIndex: TABINDEX_EUCOMEF + 9,
						width: 500,
						listWidth: 600,
						xtype: 'swevnprescrcombo'
					}, {
						allowBlank: false,
						fieldLabel: langs('Категория услуги'),
						hiddenName: 'UslugaCategory_id',
						listeners: {
							'select': function (combo, record) {
								var base_form = this.FormPanel.getForm();

								base_form.findField('UslugaComplex_id').clearValue();
								base_form.findField('UslugaComplex_id').getStore().removeAll();

								this.toggleVisibleExecutionBtnPanel();

								if (!record) {
									base_form.findField('UslugaComplex_id').setUslugaCategoryList();
									return false;
								}

								base_form.findField('UslugaComplex_id').setUslugaCategoryList([record.get('UslugaCategory_SysNick')]);

								return true;
							}.createDelegate(this),

							'change': function (combo, newValue) {
								var base_form = this.FormPanel.getForm();

								base_form.findField('UslugaComplex_id').clearValue();
								base_form.findField('UslugaComplex_id').getStore().removeAll();

								this.toggleVisibleExecutionBtnPanel();

								var index = combo.getStore().findBy(function (rec) {
									return (rec.get(combo.valueField) == newValue);
								});
								var record = base_form.findField('UslugaCategory_id').getStore().getAt(index);
								if (!record) {
									base_form.findField('UslugaComplex_id').setUslugaCategoryList();
									return false;
								}
								base_form.findField('UslugaComplex_id').setUslugaCategoryList([record.get('UslugaCategory_SysNick')]);

								return true;
							}.createDelegate(this)
						},
						listWidth: 400,
						loadParams: uslugaCategoryParams,
						tabIndex: TABINDEX_EUOPEREF + 10,
						width: 250,
						xtype: 'swuslugacategorycombo'
					}, {
						allowBlank: false,
						fieldLabel: langs('Услуга'),
						hiddenName: 'UslugaComplex_id',
						to: 'EvnUslugaOper',
						listeners: {
							'change': function (combo, newValue, oldValue) {
								this.toggleVisibleExecutionBtnPanel();
								this.loadUslugaComplexTariffCombo();
								var base_form = this.FormPanel.getForm(),
									prescr_combo = base_form.findField('EvnPrescr_id');
								prescr_combo.onChangedUslugaCombo(this.action, combo.getStore().getById(newValue));
								if (combo.getFieldValue('UslugaComplex_Code') && combo.getFieldValue('UslugaComplex_Code').substr(0,3)=='A16') {
									base_form.findField('EvnUslugaOper_IsOperationDeath').showContainer();
								} else {
									base_form.findField('EvnUslugaOper_IsOperationDeath').hideContainer();
								}
								if (combo.getFieldValue('UslugaComplex_Code') && combo.getFieldValue('UslugaComplex_Code').inlist(['A16.12.004.008', 'A16.12.004.009'])) {
									this.findById(this.id+'_CardioFields').show();
									base_form.findField('IsCardioCheck').setValue(1);
								} else {
									this.findById(this.id+'_CardioFields').hide();
									base_form.findField('IsCardioCheck').setValue(0);
								}
							}.createDelegate(this)
						},
						listWidth: 600,
						tabIndex: TABINDEX_EUOPEREF + 11,
						width: 500,
						xtype: 'swuslugacomplexnewcombo'
					}, {
						comboSubject: 'UslugaMedType',
						enableKeyEvents: true,
						hidden: getRegionNick() !== 'kz',
						fieldLabel: langs('Вид услуги'),
						hiddenName: 'UslugaMedType_id',
						allowBlank: getRegionNick() !== 'kz',
						lastQuery: '',
						tabIndex: TABINDEX_EUOPEREF + 12,
						typeCode: 'int',
						width: 450,
						xtype: 'swcommonsprcombo'
					}, {
						allowBlank: true,
						hiddenName: 'UslugaComplexTariff_id',
						listeners: {
							'change': function (combo, newValue, oldValue) {
								var index = combo.getStore().findBy(function (rec) {
									return (rec.get(combo.valueField) == newValue);
								});

								combo.fireEvent('select', combo, combo.getStore().getAt(index));

								return true;
							}.createDelegate(this),
							'select': function (combo, record) {
								var base_form = this.FormPanel.getForm();

								if (record) {
									if (!Ext.isEmpty(record.get(combo.valueField))) {
										combo.setRawValue(record.get('UslugaComplexTariff_Code') + ". " + record.get('UslugaComplexTariff_Name'));
									}

									base_form.findField('EvnUslugaOper_Price').setRawValue(record.get('UslugaComplexTariff_Tariff'));
								}
								else {
									base_form.findField('EvnUslugaOper_Price').setRawValue('');
								}

								return true;
							}.createDelegate(this)
						},
						listWidth: 600,
						tabIndex: TABINDEX_EUOPEREF + 12,
						width: 500,
						xtype: 'swuslugacomplextariffcombo'
					}, {
						allowBlank: (getRegionNick() != 'ekb'),
						hidden: (getRegionNick() != 'ekb'),
						fieldLabel: langs('Вид диагноза'),
						hiddenName: 'DiagSetClass_id',
						xtype: 'swdiagsetclasscombo',
						width: 250
					}, {
						checkAccessRights: true,
						allowBlank: (getRegionNick() != 'ekb'),
						hidden: (getRegionNick() != 'ekb'),
						hiddenName: 'Diag_id',
						width: 500,
						xtype: 'swdiagcombo',
						onChange: function (combo, newValue, oldValue) {
							var base_form = this.FormPanel.getForm();

							base_form.findField('DiagSetClass_id').clearFilter();
							if (!Ext.isEmpty(newValue) && newValue == base_form.findField('EvnUslugaOper_pid').getFieldValue('Diag_id')) {
								base_form.findField('DiagSetClass_id').getStore().filterBy(function (rec) {
									return (rec.get('DiagSetClass_Code').inlist(['1']));
								});
								base_form.findField('DiagSetClass_id').setFieldValue('DiagSetClass_Code', '1');
								base_form.findField('DiagSetClass_id').disable();
							} else {
								if (base_form.findField('DiagSetClass_id').getFieldValue('DiagSetClass_Code') == '1') {
									base_form.findField('DiagSetClass_id').setValue(null);
								}
								base_form.findField('DiagSetClass_id').getStore().filterBy(function (rec) {
									return (rec.get('DiagSetClass_Code').inlist(['0', '2', '3']));
								});
								if (this.action != 'view') {
									base_form.findField('DiagSetClass_id').enable();
								}
							}

							if (getRegionNick() == 'ekb' && this.parentClass.inlist(['EvnSection', 'EvnPS'])) {
								this.reloadUslugaComplexField();
							}
						}.createDelegate(this)
					}, {
						allowBlank: true,
						allowNegative: false,
						disabled: true,
						fieldLabel: langs('Цена'),
						name: 'EvnUslugaOper_Price',
						tabIndex: TABINDEX_EUOPEREF + 13,
						width: 150,
						xtype: 'numberfield'
					}, {
						allowBlank: false,
						hiddenName: 'OperType_id',
						tabIndex: TABINDEX_EUOPEREF + 16,
						width: 300,
						xtype: 'swopertypecombo'
					}, {
						allowBlank: false,
						hiddenName: 'OperDiff_id',
						lastQuery: '',
						tabIndex: TABINDEX_EUOPEREF + 17,
						width: 300,
						xtype: 'swoperdiffcombo'
					}, {
						fieldLabel: langs('Условие лечения'),
						hiddenName: 'TreatmentConditionsType_id',
						comboSubject: 'TreatmentConditionsType',
						autoLoad: true,
						typeCode: 'int',
						allowBlank: true,
						tabIndex: TABINDEX_EUOPEREF + 18,
						xtype: 'swcommonsprcombo',
						width: 300
					}, {
						fieldLabel: langs('Применение ВМТ'),
						hiddenName: 'EvnUslugaOper_IsVMT',
						autoLoad: true,
						allowBlank: true,
						tabIndex: TABINDEX_EUOPEREF + 19,
						xtype: 'swyesnocombo',
						width: 150
					}, {
						fieldLabel: langs('Микрохирургическая'),
						hiddenName: 'EvnUslugaOper_IsMicrSurg',
						autoLoad: true,
						allowBlank: true,
						tabIndex: TABINDEX_EUOPEREF + 19,
						xtype: 'swyesnocombo',
						width: 150
					}, {
						fieldLabel: langs('На открытом сердце'),
						hiddenName: 'EvnUslugaOper_IsOpenHeart',
						autoLoad: true,
						allowBlank: true,
						tabIndex: TABINDEX_EUOPEREF + 19,
						xtype: 'swyesnocombo',
						width: 150,
						listeners: {
							'change': function (combo, newvalue, oldvalue) {
								var base_form = this.FormPanel.getForm();
								var EvnUslugaOper_IsArtCirc_Combo = base_form.findField('EvnUslugaOper_IsArtCirc');
								(newvalue == '2' && this.action != 'view') ? EvnUslugaOper_IsArtCirc_Combo.enable() : EvnUslugaOper_IsArtCirc_Combo.disable();
							}.createDelegate(this)
						}
					}, {
						fieldLabel: langs('Из них с искусственным кровообращением'),
						hiddenName: 'EvnUslugaOper_IsArtCirc',
						autoLoad: true,
						allowBlank: true,
						tabIndex: TABINDEX_EUOPEREF + 19,
						xtype: 'swyesnocombo',
						width: 150,
						disabled: true
					},
						{
							autoHeight: true,
							style: 'padding: 0px;',
							title: langs('Признаки использования аппаратуры'),
							width: 640,
							xtype: 'fieldset',

							items: [{
								allowBlank: false,
								fieldLabel: langs('Эндоскопическая'),
								hiddenName: 'EvnUslugaOper_IsEndoskop',
								tabIndex: TABINDEX_EUOPEREF + 20,
								width: 150,
								xtype: 'swyesnocombo'
							}, {
								allowBlank: false,
								fieldLabel: langs('Лазерная'),
								hiddenName: 'EvnUslugaOper_IsLazer',
								tabIndex: TABINDEX_EUOPEREF + 21,
								width: 150,
								xtype: 'swyesnocombo'
							}, {
								allowBlank: false,
								fieldLabel: langs('Криогенная'),
								hiddenName: 'EvnUslugaOper_IsKriogen',
								tabIndex: TABINDEX_EUOPEREF + 22,
								width: 150,
								xtype: 'swyesnocombo'
							}, {
								allowBlank: false,
								fieldLabel: langs('Рентгенологическая'),
								hiddenName: 'EvnUslugaOper_IsRadGraf',
								tabIndex: TABINDEX_EUOPEREF + 23,
								width: 150,
								xtype: 'swyesnocombo'
							}]
						}, {
							allowBlank: false,
							allowNegative: false,
							enableKeyEvents: true,
							fieldLabel: langs('Количество'),
							listeners: {
								'keydown': function (inp, e) {
									if (e.shiftKey == false && e.getKey() == Ext.EventObject.TAB) {
										if (!this.findById('EUOperEF_EvnUslugaOperBrigPanel').collapsed && this.findById('EUOperEF_EvnUslugaOperBrigGrid').getStore().getCount() > 0) {
											e.stopEvent();
											this.findById('EUOperEF_EvnUslugaOperBrigGrid').getView().focusRow(0);
											this.findById('EUOperEF_EvnUslugaOperBrigGrid').getSelectionModel().selectFirstRow();
										}
										else if (!this.findById('EUOperEF_EvnUslugaOperAnestPanel').collapsed && this.findById('EUOperEF_EvnUslugaOperAnestGrid').getStore().getCount() > 0) {
											e.stopEvent();
											this.findById('EUOperEF_EvnUslugaOperAnestGrid').getView().focusRow(0);
											this.findById('EUOperEF_EvnUslugaOperAnestGrid').getSelectionModel().selectFirstRow();
										}
										else if (!this.findById('EUOperEF_EvnAggPanel').collapsed && this.findById('EUOperEF_EvnAggGrid').getStore().getCount() > 0) {
											e.stopEvent();
											this.findById('EUOperEF_EvnAggGrid').getView().focusRow(0);
											this.findById('EUOperEF_EvnAggGrid').getSelectionModel().selectFirstRow();
										}
										else if ( !this.findById('EUOperEF_EvnDrugPanel').collapsed && this.findById('EUOperEF_EvnDrugGrid').getStore().getCount() > 0 ) {
											e.stopEvent();
											this.findById('EUOperEF_EvnDrugGrid').getView().focusRow(0);
											this.findById('EUOperEF_EvnDrugGrid').getSelectionModel().selectFirstRow();
										}
									}
								}.createDelegate(this)
							},
							name: 'EvnUslugaOper_Kolvo',
							tabIndex: TABINDEX_EUOPEREF + 25,
							width: 150,
							xtype: 'numberfield'
						}, {
							border: false,
							layout: 'form',
							id: this.id+'_CardioFields',
							items: [{
								border: false,
								layout: 'column',
								items: [{
									border: false,
									layout: 'form',
									width: 250,
									items: [{
										fieldLabel: 'Дата и время начала раздувания баллона',
										name: 'EvnUslugaOper_BallonBegDate',
										width: 100,
										plugins:[ new Ext.ux.InputTextMask('99.99.9999', false) ],
										xtype:'swdatefield'
									}]
								}, {
									border: false,
									layout: 'form',
									items: [{
										hideLabel: true,
										name: 'EvnUslugaOper_BallonBegTime',
										onTriggerClick:function () {
											var base_form = this.FormPanel.getForm();
											var time_field = base_form.findField('EvnUslugaOper_BallonBegTime');

											if (time_field.disabled) {
												return false;
											}

											setCurrentDateTime({
												callback:function () {
													base_form.findField('EvnUslugaOper_BallonBegDate').fireEvent('change', base_form.findField('EvnUslugaOper_BallonBegDate'), base_form.findField('EvnUslugaOper_BallonBegDate').getValue());
												}.createDelegate(this),
												dateField:base_form.findField('EvnUslugaOper_BallonBegDate'),
												loadMask:true,
												setDate:true,
												setDateMaxValue:false,
												setDateMinValue:false,
												setTime:true,
												timeField:time_field,
												windowId:this.id
											});
										}.createDelegate(this),
										width: 60,
										plugins: [ new Ext.ux.InputTextMask('99:99', true) ],
										xtype:'swtimefield'
									}]
								}]
							}, {
								border: false,
								layout: 'column',
								items: [{
									border: false,
									layout: 'form',
									width: 250,
									items: [{
										fieldLabel: 'Дата и время окончания ЧКВ',
										name: 'EvnUslugaOper_CKVEndDate',
										width: 100,
										plugins:[ new Ext.ux.InputTextMask('99.99.9999', false) ],
										xtype:'swdatefield'
									}]
								}, {
									border: false,
									layout: 'form',
									items: [{
										hideLabel: true,
										name: 'EvnUslugaOper_CKVEndTime',
										onTriggerClick:function () {
											var base_form = this.FormPanel.getForm();
											var time_field = base_form.findField('EvnUslugaOper_CKVEndTime');

											if (time_field.disabled) {
												return false;
											}

											setCurrentDateTime({
												callback:function () {
													base_form.findField('EvnUslugaOper_CKVEndDate').fireEvent('change', base_form.findField('EvnUslugaOper_CKVEndDate'), base_form.findField('EvnUslugaOper_CKVEndDate').getValue());
												}.createDelegate(this),
												dateField:base_form.findField('EvnUslugaOper_CKVEndDate'),
												loadMask:true,
												setDate:true,
												setDateMaxValue:false,
												setDateMinValue:false,
												setTime:true,
												timeField:time_field,
												windowId:this.id
											});
										}.createDelegate(this),
										width: 60,
										plugins: [ new Ext.ux.InputTextMask('99:99', true) ],
										xtype:'swtimefield'
									}]
								}]
							}]
						}, {
							fieldLabel: '',
							labelSeparator: '',
							xtype: 'swcheckbox',
							boxLabel: 'Смерть наступила на операционном столе',
							name:'EvnUslugaOper_IsOperationDeath',
							inputValue: '2',
							uncheckedValue: '1'
						}]
				}),
				new sw.Promed.Panel({
					border: true,
					collapsible: true,
					height: 200,
					id: 'EUOperEF_EvnUslugaOperBrigPanel',
					isLoaded: false,
					layout: 'border',
					listeners: {
						'expand': function (panel) {
							if (panel.isLoaded === false) {
								panel.isLoaded = true;
								panel.findById('EUOperEF_EvnUslugaOperBrigGrid').getStore().load({
									params: {
										EvnUslugaOperBrig_pid: this.FormPanel.getForm().findField('EvnUslugaOper_id').getValue()
									}
								});
							}

							panel.doLayout();
						}.createDelegate(this)
					},
					style: 'margin-bottom: 0.5em;',
					title: langs('2. Операционная бригада'),
					items: [new Ext.grid.GridPanel({
						autoExpandColumn: 'autoexpand',
						autoExpandMin: 100,
						border: false,
						columns: [{
							dataIndex: 'SurgType_Name',
							header: langs('Вид'),
							hidden: false,
							id: 'autoexpand',
							sortable: true
						}, {
							dataIndex: 'MedPersonal_Code',
							header: langs('Код врача'),
							hidden: false,
							resizable: false,
							sortable: true,
							width: 100
						}, {
							dataIndex: 'MedPersonal_Fio',
							header: langs('ФИО врача'),
							hidden: false,
							resizable: false,
							sortable: true,
							width: 300
						}],
						frame: false,
						id: 'EUOperEF_EvnUslugaOperBrigGrid',
						keys: [{
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
							fn: function (inp, e) {
								e.stopEvent();

								if (e.browserEvent.stopPropagation)
									e.browserEvent.stopPropagation();
								else
									e.browserEvent.cancelBubble = true;

								if (e.browserEvent.preventDefault)
									e.browserEvent.preventDefault();
								else
									e.browserEvent.returnValue = false;

								e.returnValue = false;

								if (Ext.isIE) {
									e.browserEvent.keyCode = 0;
									e.browserEvent.which = 0;
								}

								var grid = this.findById('EUOperEF_EvnUslugaOperBrigGrid');

								switch (e.getKey()) {
									case Ext.EventObject.DELETE:
										this.deleteEvent('EvnUslugaOperBrig');
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
										}
										else if (e.getKey() == Ext.EventObject.F4 || e.getKey() == Ext.EventObject.ENTER) {
											action = 'edit';
										}

										this.openEvnUslugaOperBrigEditWindow(action);
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
										var base_form = this.FormPanel.getForm();

										grid.getSelectionModel().clearSelections();
										grid.getSelectionModel().fireEvent('rowselect', grid.getSelectionModel());

										if (e.shiftKey == false) {
											if (!this.findById('EUOperEF_EvnUslugaOperAnestPanel').collapsed && this.findById('EUOperEF_EvnUslugaOperAnestGrid').getStore().getCount() > 0) {
												this.findById('EUOperEF_EvnUslugaOperAnestGrid').getView().focusRow(0);
												this.findById('EUOperEF_EvnUslugaOperAnestGrid').getSelectionModel().selectFirstRow();
											}
											else if (!this.findById('EUOperEF_EvnAggPanel').collapsed && this.findById('EUOperEF_EvnAggGrid').getStore().getCount() > 0) {
												this.findById('EUOperEF_EvnAggGrid').getView().focusRow(0);
												this.findById('EUOperEF_EvnAggGrid').getSelectionModel().selectFirstRow();
											}
											else if ( !this.findById('EUOperEF_EvnDrugPanel').collapsed && this.findById('EUOperEF_EvnDrugGrid').getStore().getCount() > 0 ) {
												this.findById('EUOperEF_EvnDrugGrid').getView().focusRow(0);
												this.findById('EUOperEF_EvnDrugGrid').getSelectionModel().selectFirstRow();
											}
											else if (this.action == 'view') {
												this.buttons[this.buttons.length - 1].focus();
											}
											else {
												this.buttons[0].focus();
											}
										}
										else {
											if (!this.findById('EUOperEF_EvnUslugaPanel').collapsed && this.action != 'view') {
												base_form.findField('EvnUslugaOper_Kolvo').focus(true);
											}
											else {
												this.buttons[this.buttons.length - 1].focus();
											}
										}
										break;
								}
							}.createDelegate(this),
							scope: this,
							stopEvent: true
						}],
						listeners: {
							'rowdblclick': function (grid, number, obj) {
								this.openEvnUslugaOperBrigEditWindow('edit');
							}.createDelegate(this)
						},
						loadMask: true,
						region: 'center',
						sm: new Ext.grid.RowSelectionModel({
							listeners: {
								'rowselect': function (sm, rowIndex, record) {
									var access_type = 'view';
									var id = null;
									var selected_record = sm.getSelected();
									var toolbar = this.findById('EUOperEF_EvnUslugaOperBrigGrid').getTopToolbar();

									if (selected_record) {
										access_type = selected_record.get('accessType');
										id = selected_record.get('EvnUslugaOperBrig_id');
									}

									toolbar.items.items[1].disable();
									toolbar.items.items[3].disable();

									if (id) {
										toolbar.items.items[2].enable();

										if (this.action != 'view' && access_type == 'edit') {
											toolbar.items.items[1].enable();
											toolbar.items.items[3].enable();
										}
									}
									else {
										toolbar.items.items[2].disable();
									}
								}.createDelegate(this)
							}
						}),
						stripeRows: true,
						store: new Ext.data.Store({
							autoLoad: false,
							listeners: {
								'load': function (store, records, index) {
									if (store.getCount() == 0) {
										LoadEmptyRow(this.findById('EUOperEF_EvnUslugaOperBrigGrid'));
									}

									// this.findById('EUOperEF_EvnUslugaOperBrigGrid').getView().focusRow(0);
									// this.findById('EUOperEF_EvnUslugaOperBrigGrid').getSelectionModel().selectFirstRow();
								}.createDelegate(this)
							},
							reader: new Ext.data.JsonReader({
								id: 'EvnUslugaOperBrig_id'
							}, [{
								mapping: 'accessType',
								name: 'accessType',
								type: 'string'
							}, {
								mapping: 'EvnUslugaOperBrig_id',
								name: 'EvnUslugaOperBrig_id',
								type: 'int'
							}, {
								mapping: 'EvnUslugaOperBrig_pid',
								name: 'EvnUslugaOperBrig_pid',
								type: 'int'
							}, {
								mapping: 'MedPersonal_id',
								name: 'MedPersonal_id',
								type: 'int'
							}, {
								mapping: 'MedStaffFact_id',
								name: 'MedStaffFact_id',
								type: 'int'
							}, {
								mapping: 'SurgType_Code',
								name: 'SurgType_Code',
								type: 'int'
							}, {
								mapping: 'SurgType_id',
								name: 'SurgType_id',
								type: 'int'
							}, {
								mapping: 'MedPersonal_Code',
								name: 'MedPersonal_Code',
								type: 'string'
							}, {
								mapping: 'MedPersonal_Fio',
								name: 'MedPersonal_Fio',
								type: 'string'
							}, {
								mapping: 'SurgType_Name',
								name: 'SurgType_Name',
								type: 'string'
							}]),
							url: '/?c=EvnUslugaOperBrig&m=loadEvnUslugaOperBrigGrid'
						}),
						tbar: new sw.Promed.Toolbar({
							buttons: [{
								handler: function () {
									this.openEvnUslugaOperBrigEditWindow('add');
								}.createDelegate(this),
								iconCls: 'add16',
								text: BTN_GRIDADD
							}, {
								handler: function () {
									this.openEvnUslugaOperBrigEditWindow('edit');
								}.createDelegate(this),
								iconCls: 'edit16',
								text: BTN_GRIDEDIT
							}, {
								handler: function () {
									this.openEvnUslugaOperBrigEditWindow('view');
								}.createDelegate(this),
								iconCls: 'view16',
								text: BTN_GRIDVIEW
							}, {
								handler: function () {
									this.deleteEvent('EvnUslugaOperBrig');
								}.createDelegate(this),
								iconCls: 'delete16',
								text: BTN_GRIDDEL
							}]
						})
					})]
				}),
				new sw.Promed.Panel({
					border: true,
					collapsible: true,
					height: 200,
					id: 'EUOperEF_EvnUslugaOperAnestPanel',
					isLoaded: false,
					layout: 'border',
					listeners: {
						'expand': function (panel) {
							if (panel.isLoaded === false) {
								panel.isLoaded = true;
								panel.findById('EUOperEF_EvnUslugaOperAnestGrid').getStore().load({
									params: {
										EvnUslugaOperAnest_pid: this.FormPanel.getForm().findField('EvnUslugaOper_id').getValue()
									}
								});
							}

							panel.doLayout();
						}.createDelegate(this)
					},
					style: 'margin-bottom: 0.5em;',
					title: langs('3. Виды анестезии'),
					items: [new Ext.grid.GridPanel({
						autoExpandColumn: 'autoexpand_anest',
						autoExpandMin: 100,
						border: false,
						columns: [{
							dataIndex: 'AnesthesiaClass_Code',
							header: langs('Код'),
							hidden: false,
							resizable: false,
							sortable: true,
							width: 100
						}, {
							dataIndex: 'AnesthesiaClass_Name',
							header: langs('Наименование'),
							hidden: false,
							id: 'autoexpand_anest',
							sortable: true
						}],
						frame: false,
						id: 'EUOperEF_EvnUslugaOperAnestGrid',
						keys: [{
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
							fn: function (inp, e) {
								e.stopEvent();

								if (e.browserEvent.stopPropagation)
									e.browserEvent.stopPropagation();
								else
									e.browserEvent.cancelBubble = true;

								if (e.browserEvent.preventDefault)
									e.browserEvent.preventDefault();
								else
									e.browserEvent.returnValue = false;

								e.returnValue = false;

								if (Ext.isIE) {
									e.browserEvent.keyCode = 0;
									e.browserEvent.which = 0;
								}

								var grid = this.findById('EUOperEF_EvnUslugaOperAnestGrid');

								switch (e.getKey()) {
									case Ext.EventObject.DELETE:
										this.deleteEvent('EvnUslugaOperAnest');
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
										}
										else if (e.getKey() == Ext.EventObject.F4 || e.getKey() == Ext.EventObject.ENTER) {
											action = 'edit';
										}

										this.openEvnUslugaOperAnestEditWindow(action);
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
										var base_form = this.FormPanel.getForm();

										grid.getSelectionModel().clearSelections();
										grid.getSelectionModel().fireEvent('rowselect', grid.getSelectionModel());

										if (e.shiftKey == false) {
											if (!this.findById('EUOperEF_EvnAggPanel').collapsed && this.findById('EUOperEF_EvnAggGrid').getStore().getCount() > 0) {
												this.findById('EUOperEF_EvnAggGrid').getView().focusRow(0);
												this.findById('EUOperEF_EvnAggGrid').getSelectionModel().selectFirstRow();
											}
											else if ( !this.findById('EUOperEF_EvnDrugPanel').collapsed && this.findById('EUOperEF_EvnDrugGrid').getStore().getCount() > 0 ) {
												this.findById('EUOperEF_EvnDrugGrid').getView().focusRow(0);
												this.findById('EUOperEF_EvnDrugGrid').getSelectionModel().selectFirstRow();
											}
											else if (this.action == 'view') {
												this.buttons[this.buttons.length - 1].focus();
											}
											else {
												this.buttons[0].focus();
											}
										}
										else {
											if (!this.findById('EUOperEF_EvnUslugaOperBrigPanel').collapsed && this.findById('EUOperEF_EvnUslugaOperBrigGrid').getStore().getCount() > 0) {
												this.findById('EUOperEF_EvnUslugaOperBrigGrid').getView().focusRow(0);
												this.findById('EUOperEF_EvnUslugaOperBrigGrid').getSelectionModel().selectFirstRow();
											}
											else if (!this.findById('EUOperEF_EvnUslugaPanel').collapsed && this.action != 'view') {
												base_form.findField('EvnUslugaOper_Kolvo').focus(true);
											}
											else {
												this.buttons[this.buttons.length - 1].focus();
											}
										}
										break;
								}
							}.createDelegate(this),
							scope: this,
							stopEvent: true
						}],
						listeners: {
							'rowdblclick': function (grid, number, obj) {
								this.openEvnUslugaOperAnestEditWindow('edit');
							}.createDelegate(this)
						},
						loadMask: true,
						region: 'center',
						sm: new Ext.grid.RowSelectionModel({
							listeners: {
								'rowselect': function (sm, rowIndex, record) {
									var access_type = 'view';
									var id = null;
									var selected_record = sm.getSelected();
									var toolbar = this.findById('EUOperEF_EvnUslugaOperAnestGrid').getTopToolbar();

									if (selected_record) {
										access_type = selected_record.get('accessType');
										id = selected_record.get('EvnUslugaOperAnest_id');
									}

									toolbar.items.items[1].disable();
									toolbar.items.items[3].disable();

									if (id) {
										toolbar.items.items[2].enable();

										if (this.action != 'view' && access_type == 'edit') {
											toolbar.items.items[1].enable();
											toolbar.items.items[3].enable();
										}
									}
									else {
										toolbar.items.items[2].disable();
									}
								}.createDelegate(this)
							}
						}),
						stripeRows: true,
						store: new Ext.data.Store({
							autoLoad: false,
							listeners: {
								'load': function (store, records, index) {
									if (store.getCount() == 0) {
										LoadEmptyRow(this.findById('EUOperEF_EvnUslugaOperAnestGrid'));
									}

									// this.findById('EUOperEF_EvnUslugaOperAnestGrid').getView().focusRow(0);
									// this.findById('EUOperEF_EvnUslugaOperAnestGrid').getSelectionModel().selectFirstRow();
								}.createDelegate(this)
							},
							reader: new Ext.data.JsonReader({
								id: 'EvnUslugaOperAnest_id'
							}, [{
								mapping: 'accessType',
								name: 'accessType',
								type: 'string'
							}, {
								mapping: 'EvnUslugaOperAnest_id',
								name: 'EvnUslugaOperAnest_id',
								type: 'int'
							}, {
								mapping: 'EvnUslugaOperAnest_pid',
								name: 'EvnUslugaOperAnest_pid',
								type: 'int'
							}, {
								mapping: 'AnesthesiaClass_id',
								name: 'AnesthesiaClass_id',
								type: 'int'
							}, {
								mapping: 'AnesthesiaClass_Code',
								name: 'AnesthesiaClass_Code',
								type: 'string'
							}, {
								mapping: 'AnesthesiaClass_Name',
								name: 'AnesthesiaClass_Name',
								type: 'string'
							}]),
							url: '/?c=EvnUslugaOperAnest&m=loadEvnUslugaOperAnestGrid'
						}),
						tbar: new sw.Promed.Toolbar({
							buttons: [{
								handler: function () {
									this.openEvnUslugaOperAnestEditWindow('add');
								}.createDelegate(this),
								iconCls: 'add16',
								text: BTN_GRIDADD
							}, {
								handler: function () {
									this.openEvnUslugaOperAnestEditWindow('edit');
								}.createDelegate(this),
								iconCls: 'edit16',
								text: BTN_GRIDEDIT
							}, {
								handler: function () {
									this.openEvnUslugaOperAnestEditWindow('view');
								}.createDelegate(this),
								iconCls: 'view16',
								text: BTN_GRIDVIEW
							}, {
								handler: function () {
									this.deleteEvent('EvnUslugaOperAnest');
								}.createDelegate(this),
								iconCls: 'delete16',
								text: BTN_GRIDDEL
							}]
						})
					})]
				}),
				new sw.Promed.Panel({
					border: true,
					collapsible: true,
					height: 200,
					id: 'EUOperEF_EvnAggPanel',
					isLoaded: false,
					layout: 'border',
					listeners: {
						'expand': function (panel) {
							if (panel.isLoaded === false) {
								panel.isLoaded = true;
								panel.findById('EUOperEF_EvnAggGrid').getStore().load({
									params: {
										EvnAgg_pid: this.FormPanel.getForm().findField('EvnUslugaOper_id').getValue()
									}
								});
							}

							panel.doLayout();
						}.createDelegate(this)
					},
					style: 'margin-bottom: 0.5em;',
					title: langs('4. Осложнения'),
					items: [new Ext.grid.GridPanel({
						autoExpandColumn: 'autoexpand',
						autoExpandMin: 100,
						border: false,
						columns: [{
							dataIndex: 'AggType_Name',
							header: langs('Вид осложнения'),
							hidden: false,
							id: 'autoexpand',
							sortable: true
						}, {
							dataIndex: 'AggWhen_Name',
							header: langs('Контекст осложнения'),
							hidden: false,
							resizable: false,
							sortable: true,
							width: 200
						}, {
							dataIndex: 'EvnAgg_setDate',
							header: langs('Дата осложнения'),
							hidden: false,
							renderer: Ext.util.Format.dateRenderer('d.m.Y'),
							resizable: false,
							sortable: true,
							width: 130
						}],
						frame: false,
						id: 'EUOperEF_EvnAggGrid',
						keys: [{
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
							fn: function (inp, e) {
								e.stopEvent();

								if (e.browserEvent.stopPropagation)
									e.browserEvent.stopPropagation();
								else
									e.browserEvent.cancelBubble = true;

								if (e.browserEvent.preventDefault)
									e.browserEvent.preventDefault();
								else
									e.browserEvent.returnValue = false;

								e.returnValue = false;

								if (Ext.isIE) {
									e.browserEvent.keyCode = 0;
									e.browserEvent.which = 0;
								}

								var grid = this.findById('EUOperEF_EvnAggGrid');

								switch (e.getKey()) {
									case Ext.EventObject.DELETE:
										this.deleteEvent('EvnAgg');
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
										}
										else if (e.getKey() == Ext.EventObject.F4 || e.getKey() == Ext.EventObject.ENTER) {
											action = 'edit';
										}

										this.openEvnAggEditWindow(action);
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
										var base_form = this.FormPanel.getForm();

										grid.getSelectionModel().clearSelections();
										grid.getSelectionModel().fireEvent('rowselect', grid.getSelectionModel());

										if (e.shiftKey == false) {
											if (this.action == 'view') {
												this.buttons[this.buttons.length - 1].focus();
											}
											else {
												this.buttons[0].focus();
											}
										}
										else {
											if (!this.findById('EUOperEF_EvnUslugaOperAnestPanel').collapsed && this.findById('EUOperEF_EvnUslugaOperAnestGrid').getStore().getCount() > 0) {
												this.findById('EUOperEF_EvnUslugaOperAnestGrid').getView().focusRow(0);
												this.findById('EUOperEF_EvnUslugaOperAnestGrid').getSelectionModel().selectFirstRow();
											}
											else if (!this.findById('EUOperEF_EvnUslugaOperBrigPanel').collapsed && this.findById('EUOperEF_EvnUslugaOperBrigGrid').getStore().getCount() > 0) {
												this.findById('EUOperEF_EvnUslugaOperBrigGrid').getView().focusRow(0);
												this.findById('EUOperEF_EvnUslugaOperBrigGrid').getSelectionModel().selectFirstRow();
											}
											else if (!this.findById('EUOperEF_EvnUslugaPanel').collapsed && this.action != 'view') {
												base_form.findField('EvnUslugaOper_Kolvo').focus(true);
											}
											else {
												this.buttons[this.buttons.length - 1].focus();
											}
										}
										break;
								}
							},
							scope: this,
							stopEvent: true
						}],
						listeners: {
							'rowdblclick': function (grid, number, obj) {
								this.openEvnAggEditWindow('edit');
							}.createDelegate(this)
						},
						loadMask: true,
						region: 'center',
						sm: new Ext.grid.RowSelectionModel({
							listeners: {
								'rowselect': function (sm, rowIndex, record) {
									var access_type = 'view';
									var id = null;
									var selected_record = sm.getSelected();
									var toolbar = this.findById('EUOperEF_EvnAggGrid').getTopToolbar();

									if (selected_record) {
										access_type = selected_record.get('accessType');
										id = selected_record.get('EvnAgg_id');
									}

									toolbar.items.items[1].disable();
									toolbar.items.items[3].disable();

									if (id) {
										toolbar.items.items[2].enable();

										if (this.action != 'view' && access_type == 'edit') {
											toolbar.items.items[1].enable();
											toolbar.items.items[3].enable();
										}
									}
									else {
										toolbar.items.items[2].disable();
									}
								}.createDelegate(this)
							}
						}),
						stripeRows: true,
						store: new Ext.data.Store({
							autoLoad: false,
							listeners: {
								'load': function (store, records, index) {
									if (store.getCount() == 0) {
										LoadEmptyRow(this.findById('EUOperEF_EvnAggGrid'));
									}

									// this.findById('EUOperEF_EvnAggGrid').getView().focusRow(0);
									// this.findById('EUOperEF_EvnAggGrid').getSelectionModel().selectFirstRow();
								}.createDelegate(this)
							},
							reader: new Ext.data.JsonReader({
								id: 'EvnAgg_id'
							}, [{
								mapping: 'accessType',
								name: 'accessType',
								type: 'string'
							}, {
								mapping: 'EvnAgg_id',
								name: 'EvnAgg_id',
								type: 'int'
							}, {
								mapping: 'EvnAgg_pid',
								name: 'EvnAgg_pid',
								type: 'int'
							}, {
								mapping: 'Person_id',
								name: 'Person_id',
								type: 'int'
							}, {
								mapping: 'PersonEvn_id',
								name: 'PersonEvn_id',
								type: 'int'
							}, {
								mapping: 'Server_id',
								name: 'Server_id',
								type: 'int'
							}, {
								mapping: 'AggType_id',
								name: 'AggType_id',
								type: 'int'
							}, {
								mapping: 'AggWhen_id',
								name: 'AggWhen_id',
								type: 'int'
							}, {
								mapping: 'AggType_Name',
								name: 'AggType_Name',
								type: 'string'
							}, {
								mapping: 'AggWhen_Name',
								name: 'AggWhen_Name',
								type: 'string'
							}, {
								dateFormat: 'd.m.Y',
								mapping: 'EvnAgg_setDate',
								name: 'EvnAgg_setDate',
								type: 'date'
							}, {
								mapping: 'EvnAgg_setTime',
								name: 'EvnAgg_setTime',
								type: 'string'
							}]),
							url: '/?c=EvnAgg&m=loadEvnAggGrid'
						}),
						tbar: new sw.Promed.Toolbar({
							buttons: [{
								handler: function () {
									this.openEvnAggEditWindow('add');
								}.createDelegate(this),
								iconCls: 'add16',
								text: BTN_GRIDADD
							}, {
								handler: function () {
									this.openEvnAggEditWindow('edit');
								}.createDelegate(this),
								iconCls: 'edit16',
								text: BTN_GRIDEDIT
							}, {
								handler: function () {
									this.openEvnAggEditWindow('view');
								}.createDelegate(this),
								iconCls: 'view16',
								text: BTN_GRIDVIEW
							}, {
								handler: function () {
									this.deleteEvent('EvnAgg');
								}.createDelegate(this),
								iconCls: 'delete16',
								text: BTN_GRIDDEL
							}]
						})
					})]
				}),
				new sw.Promed.Panel({
					border: true,
					collapsible: true,
					height: 200,
					id: 'EUOperEF_EvnDrugPanel',
					isLoaded: false,
					layout: 'border',
					listeners: {
						'expand': function (panel) {
							if (panel.isLoaded === false) {
								panel.isLoaded = true;
								panel.findById('EUOperEF_EvnDrugGrid').getStore().load({
									params: {
										EvnDrug_pid: this.FormPanel.getForm().findField('EvnUslugaOper_id').getValue()
									}
								});
							}

							panel.doLayout();
						}.createDelegate(this)
					},
					style: 'margin-bottom: 0.5em;',
					title: langs('5. Использование медикаментов'),
					items: [new Ext.grid.GridPanel({
						autoExpandColumn: 'autoexpand_drug',
						autoExpandMin: 100,
						border: false,
						columns: [{
							dataIndex: 'EvnDrug_setDate',
							header: langs('Дата'),
							hidden: false,
							renderer: Ext.util.Format.dateRenderer('d.m.Y'),
							resizable: false,
							sortable: true,
							width: 100
						}, {
							dataIndex: 'Drug_Code',
							header: langs('Код'),
							hidden: false,
							resizable: false,
							sortable: true,
							width: 100
						}, {
							dataIndex: 'EvnDrug_Kolvo',
							header: langs('Количество'),
							hidden: false,
							resizable: true,
							sortable: true,
							width: 100
						}, {
							dataIndex: 'Drug_Name',
							header: langs('Наименование'),
							hidden: false,
							id: 'autoexpand_drug',
							resizable: true,
							sortable: true
						}],
						frame: false,
						id: 'EUOperEF_EvnDrugGrid',
						keys: [{
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
							fn: function (inp, e) {
								e.stopEvent();

								if (e.browserEvent.stopPropagation)
									e.browserEvent.stopPropagation();
								else
									e.browserEvent.cancelBubble = true;

								if (e.browserEvent.preventDefault)
									e.browserEvent.preventDefault();
								else
									e.browserEvent.returnValue = false;

								e.returnValue = false;

								if (Ext.isIE) {
									e.browserEvent.keyCode = 0;
									e.browserEvent.which = 0;
								}

								var grid = this.findById('EUOperEF_EvnDrugGrid');

								switch (e.getKey()) {
									case Ext.EventObject.DELETE:
										this.deleteEvent('EvnDrug');
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
										}
										else if (e.getKey() == Ext.EventObject.F4 || e.getKey() == Ext.EventObject.ENTER) {
											action = 'edit';
										}

										this.openEvnDrugEditWindow(action);
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
										var base_form = this.FormPanel.getForm();

										grid.getSelectionModel().clearSelections();
										grid.getSelectionModel().fireEvent('rowselect', grid.getSelectionModel());

										if (e.shiftKey == false) {
											if (this.action != 'view') {
												this.buttons[0].focus();
											}
											else {
												this.buttons[1].focus();
											}
										}
										else {

										}
										break;
								}
							}.createDelegate(this),
							scope: this,
							stopEvent: true
						}],
						listeners: {
							'rowdblclick': function (grid, number, obj) {
								this.openEvnDrugEditWindow('edit');
							}.createDelegate(this)
						},
						loadMask: true,
						region: 'center',
						sm: new Ext.grid.RowSelectionModel({
							listeners: {
								'rowselect': function (sm, rowIndex, record) {
									var access_type = 'view';
									var id = null;
									var selected_record = sm.getSelected();
									var toolbar = this.findById('EUOperEF_EvnDrugGrid').getTopToolbar();

									if (selected_record) {
										access_type = selected_record.get('accessType');
										id = selected_record.get('EvnDrug_id');
									}

									toolbar.items.items[1].disable();
									toolbar.items.items[3].disable();

									if (id) {
										toolbar.items.items[2].enable();

										if (this.action != 'view' /*&& access_type == 'edit'*/) {
											toolbar.items.items[1].enable();
											toolbar.items.items[3].enable();
										}
									}
									else {
										toolbar.items.items[2].disable();
									}
								}.createDelegate(this)
							}
						}),
						stripeRows: true,
						store: new Ext.data.Store({
							autoLoad: false,
							listeners: {
								'load': function (store, records, index) {
									if (store.getCount() == 0) {
										LoadEmptyRow(this.findById('EUOperEF_EvnDrugGrid'));
									}
								}.createDelegate(this)
							},
							reader: new Ext.data.JsonReader({
								id: 'EvnDrug_id'
							}, [{
								mapping: 'EvnDrug_id',
								name: 'EvnDrug_id',
								type: 'int'
							}, {
								dateFormat: 'd.m.Y',
								mapping: 'EvnDrug_setDate',
								name: 'EvnDrug_setDate',
								type: 'date'
							}, {
								mapping: 'Drug_Code',
								name: 'Drug_Code',
								type: 'string'
							}, {
								mapping: 'Drug_Name',
								name: 'Drug_Name',
								type: 'string'
							}, {
								mapping: 'EvnDrug_Kolvo',
								name: 'EvnDrug_Kolvo',
								type: 'float'
							}]),
							url: '/?c=EvnDrug&m=loadEvnDrugGrid'
						}),
						tbar: new sw.Promed.Toolbar({
							buttons: [{
								handler: function () {
									this.openEvnDrugEditWindow('add');
								}.createDelegate(this),
								iconCls: 'add16',
								text: langs('Добавить')
							}, {
								handler: function () {
									this.openEvnDrugEditWindow('edit');
								}.createDelegate(this),
								iconCls: 'edit16',
								text: langs('Изменить')
							}, {
								handler: function () {
									this.openEvnDrugEditWindow('view');
								}.createDelegate(this),
								iconCls: 'view16',
								text: langs('Просмотр')
							}, {
								handler: function () {
									this.deleteEvent('EvnDrug');
								}.createDelegate(this),
								iconCls: 'delete16',
								text: langs('Удалить')
							}, {
								iconCls: 'print16',
								text: langs('Печать'),
								handler: function () {
									var grid = this.findById('EUOperEF_EvnDrugGrid');
									Ext.ux.GridPrinter.print(grid);
								}.createDelegate(this)
							}]
						})
					})]
				})
			]
		});

		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.doSave();
				}.createDelegate(this),
				iconCls: 'save16',
				onShiftTabAction: function () {
					if ( !this.findById('EUOperEF_EvnDrugPanel').collapsed && this.findById('EUOperEF_EvnDrugGrid').getStore().getCount() > 0 ) {
						this.findById('EUOperEF_EvnDrugGrid').getView().focusRow(0);
						this.findById('EUOperEF_EvnDrugGrid').getSelectionModel().selectFirstRow();
					}
					else if ( !this.findById('EUOperEF_EvnAggPanel').collapsed && this.findById('EUOperEF_EvnAggGrid').getStore().getCount() > 0 ) {
						this.findById('EUOperEF_EvnAggGrid').getView().focusRow(0);
						this.findById('EUOperEF_EvnAggGrid').getSelectionModel().selectFirstRow();
					}
					else if ( !this.findById('EUOperEF_EvnUslugaOperAnestPanel').collapsed && this.findById('EUOperEF_EvnUslugaOperAnestGrid').getStore().getCount() > 0 ) {
						this.findById('EUOperEF_EvnUslugaOperAnestGrid').getView().focusRow(0);
						this.findById('EUOperEF_EvnUslugaOperAnestGrid').getSelectionModel().selectFirstRow();
					}
					else if ( !this.findById('EUOperEF_EvnUslugaOperBrigPanel').collapsed && this.findById('EUOperEF_EvnUslugaOperBrigGrid').getStore().getCount() > 0 ) {
						this.findById('EUOperEF_EvnUslugaOperBrigGrid').getView().focusRow(0);
						this.findById('EUOperEF_EvnUslugaOperBrigGrid').getSelectionModel().selectFirstRow();
					}
					else if ( !this.findById('EUOperEF_EvnUslugaPanel').collapsed ) {
						this.FormPanel.getForm().findField('EvnUslugaOper_Kolvo').focus(true);
					}
					else {
						this.buttons[this.buttons.length - 1].focus();
					}
				}.createDelegate(this),
				onTabAction: function () {
					this.buttons[this.buttons.length - 1].focus();
				}.createDelegate(this),
				tabIndex: TABINDEX_EUOPEREF + 26,
				text: BTN_FRMSAVE
			}, {
				text: '-'
			},
			HelpButton(this, -1),
			{
				handler: function() {
					this.onCancelAction();
				}.createDelegate(this),
				iconCls: 'cancel16',
				onShiftTabAction: function () {
					if ( this.action != 'view' ) {
						this.buttons[0].focus();
					}
					else if ( !this.findById('EUOperEF_EvnDrugPanel').collapsed && this.findById('EUOperEF_EvnDrugGrid').getStore().getCount() > 0 ) {
						this.findById('EUOperEF_EvnDrugGrid').getView().focusRow(0);
						this.findById('EUOperEF_EvnDrugGrid').getSelectionModel().selectFirstRow();
					}
					else if ( !this.findById('EUOperEF_EvnAggPanel').collapsed && this.findById('EUOperEF_EvnAggGrid').getStore().getCount() > 0 ) {
						this.findById('EUOperEF_EvnAggGrid').getView().focusRow(0);
						this.findById('EUOperEF_EvnAggGrid').getSelectionModel().selectFirstRow();
					}
					else if ( !this.findById('EUOperEF_EvnUslugaOperAnestPanel').collapsed && this.findById('EUOperEF_EvnUslugaOperAnestGrid').getStore().getCount() > 0 ) {
						this.findById('EUOperEF_EvnUslugaOperAnestGrid').getView().focusRow(0);
						this.findById('EUOperEF_EvnUslugaOperAnestGrid').getSelectionModel().selectFirstRow();
					}
					else if ( !this.findById('EUOperEF_EvnUslugaOperBrigPanel').collapsed && this.findById('EUOperEF_EvnUslugaOperBrigGrid').getStore().getCount() > 0 ) {
						this.findById('EUOperEF_EvnUslugaOperBrigGrid').getView().focusRow(0);
						this.findById('EUOperEF_EvnUslugaOperBrigGrid').getSelectionModel().selectFirstRow();
					}
				}.createDelegate(this),
				onTabAction: function () {
					if ( !this.findById('EUOperEF_EvnUslugaPanel').collapsed && this.action != 'view' ) {
						if ( !this.FormPanel.getForm().findField('EvnUslugaOper_pid').disabled ) {
							this.FormPanel.getForm().findField('EvnUslugaOper_pid').focus(true, 100);
						}
						else {
							this.FormPanel.getForm().findField('EvnUslugaOper_setDate').focus(true, 100);
						}
					}
					else if ( !this.findById('EUOperEF_EvnUslugaOperBrigPanel').collapsed && this.findById('EUOperEF_EvnUslugaOperBrigGrid').getStore().getCount() > 0 ) {
						this.findById('EUOperEF_EvnUslugaOperBrigGrid').getView().focusRow(0);
						this.findById('EUOperEF_EvnUslugaOperBrigGrid').getSelectionModel().selectFirstRow();
					}
					else if ( !this.findById('EUOperEF_EvnUslugaOperAnestPanel').collapsed && this.findById('EUOperEF_EvnUslugaOperAnestGrid').getStore().getCount() > 0 ) {
						this.findById('EUOperEF_EvnUslugaOperAnestGrid').getView().focusRow(0);
						this.findById('EUOperEF_EvnUslugaOperAnestGrid').getSelectionModel().selectFirstRow();
					}
					else if ( !this.findById('EUOperEF_EvnAggPanel').collapsed && this.findById('EUOperEF_EvnAggGrid').getStore().getCount() > 0 ) {
						this.findById('EUOperEF_EvnAggGrid').getView().focusRow(0);
						this.findById('EUOperEF_EvnAggGrid').getSelectionModel().selectFirstRow();
					}
					else if ( !this.findById('EUOperEF_EvnDrugPanel').collapsed && this.findById('EUOperEF_EvnDrugGrid').getStore().getCount() > 0 ) {
						this.findById('EUOperEF_EvnDrugGrid').getView().focusRow(0);
						this.findById('EUOperEF_EvnDrugGrid').getSelectionModel().selectFirstRow();
					}
					else {
						this.buttons[0].focus();
					}
				}.createDelegate(this),
				tabIndex: TABINDEX_EUOPEREF + 27,
				text: BTN_FRMCANCEL
			}],
			items: [ new sw.Promed.PersonInformationPanelShort({
				id: 'EUOperEF_PersonInformationFrame',
				region: 'north'
			}), {
				region: 'center',
				layout: 'form',
				bodyStyle: 'padding: 5px 5px 0',
				autoScroll: true,
				items: [
					win.FormPanel,
					win.EvnXmlPanel,
					win.EvnXmlPanelNarcosis,
					win.FilePanel
				]
			}]
		});

		sw.Promed.swEvnUslugaOperEditWindow.superclass.initComponent.apply(this, arguments);

		this.findById('EUOperEF_LpuSectionCombo').addListener('change', function(combo, newValue, oldValue) {
			var base_form = this.FormPanel.getForm();

			var index = combo.getStore().findBy(function(rec) {
				return ( rec.get('LpuSection_id') == newValue );
			});
			var record = combo.getStore().getAt(index);

			//base_form.findField('UslugaComplex_id').getStore().baseParams.LpuSection_id = newValue;
			//base_form.findField('UslugaComplex_id').clearValue();
			base_form.findField('UslugaComplex_id').getStore().removeAll();
			base_form.findField('UslugaComplex_id').lastQuery = 'The string that will never appear';
			if ( getRegionNick() == 'buryatiya' ) {
				if (record) {
					base_form.findField('UslugaComplex_id').setLpuSectionProfile_id(record.get('LpuSectionProfile_id'));
				}
			}
			this.loadUslugaComplexTariffCombo();
			this.setDefaultLpuSectionProfile();
			this.toggleVisibleExecutionBtnPanel();
			base_form.findField('LpuSectionProfile_id').onChangeLpuSectionId(combo, newValue);
		}.createDelegate(this));

		this.findById('EUOperEF_MedPersonalCombo').addListener('change', function(combo, newValue, oldValue) {
			var base_form = this.FormPanel.getForm();

			var index = combo.getStore().findBy(function(rec) {
				return (rec.get(combo.valueField) == newValue);
			});

			if ( index >= 0 ) {
				base_form.findField('UslugaComplex_id').getStore().baseParams.LpuSection_id = combo.getStore().getAt(index).get('LpuSection_id');
				//base_form.findField('UslugaComplex_id').clearValue();
				//base_form.findField('UslugaComplex_id').getStore().removeAll();
				//base_form.findField('UslugaComplex_id').lastQuery = 'The string that will never appear';
				base_form.findField('LpuSection_uid').setValue(combo.getStore().getAt(index).get('LpuSection_id'));
			}
			else {
				base_form.findField('UslugaComplex_id').getStore().baseParams.LpuSection_id = null;
			}

			this.loadUslugaComplexTariffCombo();
			this.setDefaultLpuSectionProfile();
			this.toggleVisibleExecutionBtnPanel();
		}.createDelegate(this));
	},
	keys: [{
		alt: true,
		fn: function(inp, e) {
			var current_window = Ext.getCmp('EvnUslugaOperEditWindow');

			switch ( e.getKey() ) {
				case Ext.EventObject.C:
					current_window.doSave();
				break;

				case Ext.EventObject.J:
					current_window.onCancelAction();
				break;

				case Ext.EventObject.NUM_ONE:
				case Ext.EventObject.ONE:
					current_window.findById('EUOperEF_EvnUslugaPanel').toggleCollapse();
				break;

				case Ext.EventObject.NUM_TWO:
				case Ext.EventObject.TWO:
					current_window.findById('EUOperEF_EvnUslugaOperBrigPanel').toggleCollapse();
				break;

				case Ext.EventObject.NUM_THREE:
				case Ext.EventObject.THREE:
					current_window.findById('EUOperEF_EvnUslugaOperAnestPanel').toggleCollapse();
				break;

				case Ext.EventObject.NUM_FOUR:
				case Ext.EventObject.FOUR:
					current_window.findById('EUOperEF_EvnAggPanel').toggleCollapse();
				break;

				case Ext.EventObject.NUM_FIVE:
				case Ext.EventObject.FIVE:
					current_window.findById('EUOperEF_EvnDrugPanel').toggleCollapse();
				break;
			}
		}.createDelegate(this),
		key: [
			Ext.EventObject.C,
			Ext.EventObject.FOUR,
			Ext.EventObject.J,
			Ext.EventObject.NUM_FOUR,
			Ext.EventObject.NUM_ONE,
			Ext.EventObject.NUM_THREE,
			Ext.EventObject.NUM_TWO,
			Ext.EventObject.ONE,
			Ext.EventObject.THREE,
			Ext.EventObject.TWO
		],
		stopEvent: true
	}],
	layout: 'border',
	listeners: {
		'hide': function(win) {
			win.onHide();
		},
		'maximize': function(win) {
			win.findById('EUOperEF_EvnDrugPanel').doLayout();
			win.findById('EUOperEF_EvnAggPanel').doLayout();
			win.findById('EUOperEF_EvnUslugaOperAnestPanel').doLayout();
			win.findById('EUOperEF_EvnUslugaOperBrigPanel').doLayout();
			win.findById('EUOperEF_EvnUslugaPanel').doLayout();
		},
		'restore': function(win) {
			win.findById('EUOperEF_EvnDrugPanel').doLayout();
			win.findById('EUOperEF_EvnAggPanel').doLayout();
			win.findById('EUOperEF_EvnUslugaOperAnestPanel').doLayout();
			win.findById('EUOperEF_EvnUslugaOperBrigPanel').doLayout();
			win.findById('EUOperEF_EvnUslugaPanel').doLayout();
		}
	},
	loadUslugaComplexTariffCombo: function () {
		var base_form = this.FormPanel.getForm();
		var combo = base_form.findField('UslugaComplexTariff_id');

		combo.setParams({
			 LpuSection_id: base_form.findField('LpuSection_uid').getValue()
			,PayType_id: base_form.findField('PayType_id').getValue()
			,Person_id: base_form.findField('Person_id').getValue()
			,UslugaComplex_id: base_form.findField('UslugaComplex_id').getValue()
			,UslugaComplexTariff_Date: base_form.findField('EvnUslugaOper_setDate').getValue()
		});

        combo.isAllowSetFirstValue = ('add' == this.action);
		combo.loadUslugaComplexTariffList();

		return true;
	},
	maximizable: true,
	minHeight: 450,
	minWidth: 700,
	modal: true,
	onCancelAction: function() {
		var evn_usluga_id = this.FormPanel.getForm().findField('EvnUslugaOper_id').getValue();

		if ( evn_usluga_id > 0 && this.action == 'add') {
			// удалить услугу
			// закрыть окно после успешного удаления
			var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Удаление услуги..."});
			loadMask.show();

			Ext.Ajax.request({
				callback: function(options, success, response) {
					loadMask.hide();

					if ( success ) {
						this.hide();
					}
					else {
						sw.swMsg.alert(langs('Ошибка'), langs('При удалении услуги возникли ошибки'));
						return false;
					}
				}.createDelegate(this),
				params: {
					'class': 'EvnUslugaOper',
					'id': evn_usluga_id
				},
				url: '/?c=EvnUsluga&m=deleteEvnUsluga'
			});
		}
		else {
			this.hide();
		}
	},
	onHide: Ext.emptyFn,
	openEvnDrugEditWindow: function(action) {
		if ( this.findById('EUOperEF_EvnDrugPanel').hidden || this.findById('EUOperEF_EvnDrugPanel').collapsed ) {
			return false;
		}

		if ( action != 'add' && action != 'edit' && action != 'view' ) {
			return false;
		}

		var base_form = this.FormPanel.getForm();
		var grid = this.findById('EUOperEF_EvnDrugGrid');

		if ( this.action == 'view') {
			if ( action == 'add') {
				return false;
			}
			else if ( action == 'edit' ) {
				action = 'view';
			}
		}

		if ( getWnd(getEvnDrugEditWindowName()).isVisible() ) {
			sw.swMsg.alert(langs('Сообщение'), langs('Окно добавления случая использования медикаментов уже открыто'));
			return false;
		}

		if ( action == 'add' && base_form.findField('EvnUslugaOper_id').getValue() == 0 ) {
			this.doSave({
				openChildWindow: function() {
					this.openEvnDrugEditWindow(action);
				}.createDelegate(this)
			});
			return false;
		}

		var formParams = new Object();
		var params = new Object();
		var person_id = this.findById('EUOperEF_PersonInformationFrame').getFieldValue('Person_id');
		var person_birthday = this.findById('EUOperEF_PersonInformationFrame').getFieldValue('Person_Birthday');
		var person_firname = this.findById('EUOperEF_PersonInformationFrame').getFieldValue('Person_Firname');
		var person_secname = this.findById('EUOperEF_PersonInformationFrame').getFieldValue('Person_Secname');
		var person_surname = this.findById('EUOperEF_PersonInformationFrame').getFieldValue('Person_Surname');

		params.parentEvnClass_SysNick = 'EvnUslugaOper';
		params.action = action;
		params.callback = function(data) {
			if ( !data || !data.evnDrugData ) {
				return false;
			}
			var grid = this.findById('EUOperEF_EvnDrugGrid');
			var record = grid.getStore().getById(data.evnDrugData.EvnDrug_id);

			if ( !record ) {
				if ( grid.getStore().getCount() == 1 && !grid.getStore().getAt(0).get('EvnDrug_id') ) {
					grid.getStore().removeAll();
				}
				grid.getStore().loadData([data.evnDrugData], true);
			}
			else {
				//
				var grid_fields = new Array();
				var i = 0;

				grid.getStore().fields.eachKey(function(key, item) {
					grid_fields.push(key);
				});

				for ( i = 0; i < grid_fields.length; i++ ) {
					record.set(grid_fields[i], data.evnDrugData[grid_fields[i]]);
				}

				record.commit();
			}
		}.createDelegate(this);
		params.onHide = function() {
			if ( grid.getStore().getCount() > 0 ) {
				grid.getView().focusRow(0);
				grid.getSelectionModel().selectFirstRow();
			}
		}.createDelegate(this);
		params.Person_id = person_id;
		params.Person_Birthday = person_birthday;
		params.Person_Firname = person_firname;
		params.Person_Secname = person_secname;
		params.Person_Surname = person_surname;

		formParams.Person_id = base_form.findField('Person_id').getValue();
		formParams.PersonEvn_id = base_form.findField('PersonEvn_id').getValue();
		formParams.Server_id = base_form.findField('Server_id').getValue();

		if ( action == 'add' ) {
			formParams.EvnDrug_id = 0;
			formParams.EvnDrug_pid = base_form.findField('EvnUslugaOper_id').getValue();
		}
		else {
			var selected_record = grid.getSelectionModel().getSelected();

			if ( !selected_record || !selected_record.get('EvnDrug_id') ) {
				return false;
			}

			formParams.EvnDrug_id = selected_record.get('EvnDrug_id');
			formParams.EvnDrug_rid = base_form.findField('EvnUslugaOper_id').getValue();
		}

		params.formParams = formParams;
		params.archiveRecord = this.archiveRecord;

		getWnd(getEvnDrugEditWindowName()).show(params);
	},
	openEvnAggEditWindow: function(action) {
		if ( action != 'add' && action != 'edit' && action != 'view' ) {
			return false;
		}

		var base_form = this.FormPanel.getForm();
		var grid = this.findById('EUOperEF_EvnAggGrid');

		if ( this.action == 'view') {
			if ( action == 'add') {
				return false;
			}
			else if ( action == 'edit' ) {
				action = 'view';
			}
		}

		if ( getWnd('swEvnAggEditWindow').isVisible() ) {
			sw.swMsg.alert(langs('Сообщение'), langs('Окно редактирования осложнения уже открыто'));
			return false;
		}

		if ( action == 'add' && base_form.findField('EvnUslugaOper_id').getValue() == 0 ) {
			this.doSave({
				openChildWindow: function() {
					this.openEvnAggEditWindow(action);
				}.createDelegate(this),
				print: false
			});
			return false;
		}

		var params = new Object();

		var person_id = this.findById('EUOperEF_PersonInformationFrame').getFieldValue('Person_id');
		var person_birthday = this.findById('EUOperEF_PersonInformationFrame').getFieldValue('Person_Birthday');
		var person_firname = this.findById('EUOperEF_PersonInformationFrame').getFieldValue('Person_Firname');
		var person_secname = this.findById('EUOperEF_PersonInformationFrame').getFieldValue('Person_Secname');
		var person_surname = this.findById('EUOperEF_PersonInformationFrame').getFieldValue('Person_Surname');
		params.Evn_setDate = Ext.util.Format.date(base_form.findField('EvnUslugaOper_setDate').getValue(),'d.m.Y');
		params.Evn_setTime = base_form.findField('EvnUslugaOper_setTime').getValue();
		if ( action == 'add' ) {

			params.EvnAgg_id = 0;
			params.EvnAgg_pid = base_form.findField('EvnUslugaOper_id').getValue();
			params.Person_id = base_form.findField('Person_id').getValue();
			params.PersonEvn_id = base_form.findField('PersonEvn_id').getValue();
			params.Server_id = base_form.findField('Server_id').getValue();
		}
		else {
			var selected_record = grid.getSelectionModel().getSelected();

			if ( !selected_record || !selected_record.get('EvnAgg_id') ) {
				return false;
			}

			if ( selected_record.get('accessType') != 'edit' ) {
				action = 'view';
			}

			params = selected_record.data;
			params.Evn_setDate=Ext.util.Format.date(selected_record.data.EvnAgg_setDate,'d.m.Y');
		}

		getWnd('swEvnAggEditWindow').show({
			action: action,
			callback: function(data) {
				if ( !data || !data.EvnAggData ) {
					return false;
				}

				var record = grid.getStore().getById(data.EvnAggData[0].EvnAgg_id);

				if ( !record ) {
					if ( grid.getStore().getCount() == 1 && !grid.getStore().getAt(0).get('EvnAgg_id') ) {
						grid.getStore().removeAll();
					}

					grid.getStore().loadData(data.EvnAggData, true);
				}
				else {
					var grid_fields = new Array();
					var i = 0;

					grid.getStore().fields.eachKey(function(key, item) {
						grid_fields.push(key);
					});

					for ( i = 0; i < grid_fields.length; i++ ) {
						record.set(grid_fields[i], data.EvnAggData[0][grid_fields[i]]);
					}

					record.commit();
				}
			}.createDelegate(this),
			formParams: params,
			onHide: function() {
				grid.getView().focusRow(0);
				grid.getSelectionModel().selectFirstRow();
			}.createDelegate(this),
			Person_id: person_id,
			Person_Birthday: person_birthday,
			Person_Firname: person_firname,
			Person_Secname: person_secname,
			Person_Surname: person_surname
		});
	},
	openEvnUslugaOperAnestEditWindow: function(action) {
		if ( action != 'add' && action != 'edit' && action != 'view' ) {
			return false;
		}

		var base_form = this.FormPanel.getForm();
		var grid = this.findById('EUOperEF_EvnUslugaOperAnestGrid');

		if ( this.action == 'view') {
			if ( action == 'add') {
				return false;
			}
			else if ( action == 'edit' ) {
				action = 'view';
			}
		}

		if ( getWnd('swEvnUslugaOperAnestEditWindow').isVisible() ) {
			sw.swMsg.alert(langs('Сообщение'), langs('Окно редактирования операционной бригады уже открыто'));
			return false;
		}

		if ( action == 'add' && base_form.findField('EvnUslugaOper_id').getValue() == 0 ) {
			this.doSave({
				openChildWindow: function() {
					this.openEvnUslugaOperAnestEditWindow(action);
				}.createDelegate(this),
				print: false
			});
			return false;
		}

		var params = new Object();

		var person_birthday = this.findById('EUOperEF_PersonInformationFrame').getFieldValue('Person_Birthday');
		var person_firname = this.findById('EUOperEF_PersonInformationFrame').getFieldValue('Person_Firname');
		var person_secname = this.findById('EUOperEF_PersonInformationFrame').getFieldValue('Person_Secname');
		var person_surname = this.findById('EUOperEF_PersonInformationFrame').getFieldValue('Person_Surname');

		if ( action == 'add' ) {
			params.EvnUslugaOperAnest_id = 0;
			params.EvnUslugaOperAnest_pid = base_form.findField('EvnUslugaOper_id').getValue();
		}
		else {
			var selected_record = grid.getSelectionModel().getSelected();

			if ( !selected_record || !selected_record.get('EvnUslugaOperAnest_id') ) {
				return false;
			}

			if ( selected_record.get('accessType') != 'edit' ) {
				action = 'view';
			}

			params = selected_record.data;
		}

		getWnd('swEvnUslugaOperAnestEditWindow').show({
			action: action,
			callback: function(data) {
				if ( !data || !data.EvnUslugaOperAnestData ) {
					return false;
				}

				var record = grid.getStore().getById(data.EvnUslugaOperAnestData[0].EvnUslugaOperAnest_id);

				if ( !record ) {
					if ( grid.getStore().getCount() == 1 && !grid.getStore().getAt(0).get('EvnUslugaOperAnest_id') ) {
						grid.getStore().removeAll();
					}

					grid.getStore().loadData(data.EvnUslugaOperAnestData, true);
				}
				else {
					var grid_fields = new Array();
					var i = 0;

					grid.getStore().fields.eachKey(function(key, item) {
						grid_fields.push(key);
					});

					for ( i = 0; i < grid_fields.length; i++ ) {
						record.set(grid_fields[i], data.EvnUslugaOperAnestData[0][grid_fields[i]]);
					}

					record.commit();
				}
			}.createDelegate(this),
			formParams: params,
			onHide: function() {
				grid.getView().focusRow(0);
				grid.getSelectionModel().selectFirstRow();
			}.createDelegate(this),
			Person_Birthday: person_birthday,
			Person_Firname: person_firname,
			Person_Secname: person_secname,
			Person_Surname: person_surname
		});
	},
	openEvnUslugaOperBrigEditWindow: function(action) {
		if ( action != 'add' && action != 'edit' && action != 'view' ) {
			return false;
		}

		var base_form = this.FormPanel.getForm();
		var grid = this.findById('EUOperEF_EvnUslugaOperBrigGrid'),
			_this = this;

		if ( this.action == 'view') {
			if ( action == 'add') {
				return false;
			}
			else if ( action == 'edit' ) {
				action = 'view';
			}
		}

		if ( getWnd('swEvnUslugaOperBrigEditWindow').isVisible() ) {
			sw.swMsg.alert(langs('Сообщение'), langs('Окно редактирования операционной бригады уже открыто'));
			return false;
		}


		if ( action == 'add' && base_form.findField('EvnUslugaOper_id').getValue() == 0 ) {
			this.doSave({
				openChildWindow: function() {
					this.openEvnUslugaOperBrigEditWindow(action);
				}.createDelegate(this),
				print: false
			});
			return false;
		}

		var params = new Object();
		var surgTypeFilter = 0;

		var person_birthday = this.findById('EUOperEF_PersonInformationFrame').getFieldValue('Person_Birthday');
		var person_firname = this.findById('EUOperEF_PersonInformationFrame').getFieldValue('Person_Firname');
		var person_secname = this.findById('EUOperEF_PersonInformationFrame').getFieldValue('Person_Secname');
		var person_surname = this.findById('EUOperEF_PersonInformationFrame').getFieldValue('Person_Surname');
		var EvnUslugaOper_setDate =  Ext.util.Format.date(_this.findById('EUOperEF_EvnUslugaOper_setDate').getValue(), 'd.m.Y');

		if ( Ext.isEmpty(EvnUslugaOper_setDate) ) {
			sw.swMsg.alert(langs('Сообщение'), langs('Для заведения врачей в операционную бригаду необходимо указать Дату начала операции.'));
			return false;
		}

		if ( action == 'add' ) {
			params.EvnUslugaOperBrig_id = 0;
			params.EvnUslugaOperBrig_pid = base_form.findField('EvnUslugaOper_id').getValue();

			grid.getStore().each(function(rec) {
				if ( rec.get('SurgType_Code') == 1 ) {
					surgTypeFilter = -1;
				}
			});

			if ( surgTypeFilter == 0 ) {
				surgTypeFilter = 1;
			}
		}
		else {
			var selected_record = grid.getSelectionModel().getSelected();

			if ( !selected_record || !selected_record.get('EvnUslugaOperBrig_id') ) {
				return false;
			}

			if ( selected_record.get('accessType') != 'edit' ) {
				action = 'view';
			}

			if ( selected_record.get('SurgType_Code') == 1 ) {
				surgTypeFilter = 1;
			}
			else {
				surgTypeFilter = -1;
			}

			params = selected_record.data;
			params.MedStaffFact_id = params.MedPersonal_id;
		}
		params.EvnUslugaOper_setDate = EvnUslugaOper_setDate;
		getWnd('swEvnUslugaOperBrigEditWindow').show({
			action: action,
			callback: function(data) {
				if ( !data || !data.EvnUslugaOperBrigData ) {
					return false;
				}

				var record = grid.getStore().getById(data.EvnUslugaOperBrigData[0].EvnUslugaOperBrig_id);

				if ( !record ) {
					if ( grid.getStore().getCount() == 1 && !grid.getStore().getAt(0).get('EvnUslugaOperBrig_id') ) {
						grid.getStore().removeAll();
					}

					grid.getStore().loadData(data.EvnUslugaOperBrigData, true);
				}
				else {
					var grid_fields = new Array();
					var i = 0;

					grid.getStore().fields.eachKey(function(key, item) {
						grid_fields.push(key);
					});

					for ( i = 0; i < grid_fields.length; i++ ) {
						record.set(grid_fields[i], data.EvnUslugaOperBrigData[0][grid_fields[i]]);
					}

					record.commit();
				}
			}.createDelegate(this),
			formParams: params,
			onHide: function() {
				grid.getView().focusRow(0);
				grid.getSelectionModel().selectFirstRow();
			}.createDelegate(this),
			Person_Birthday: person_birthday,
			Person_Firname: person_firname,
			Person_Secname: person_secname,
			Person_Surname: person_surname,
			surgTypeFilter: surgTypeFilter
		});
	},
	parentClass: null,
	plain: true,
	resizable: true,
	// урезанная версия функции из swEvnUslugaEditWindow.js
	checkIsHTMedicalCare: function() {
		if (getRegionNick() == 'ekb') {
			var base_form = this.FormPanel.getForm();
			if (base_form.findField('LpuSection_uid').getFieldValue('LpuSection_IsHTMedicalCare') == 2) {
				return true;
			}
		}
		return false;
	},
	setUslugaComplexPartitionCodeList: function(paytype_sysnick) {
		if ( getRegionNick().inlist(['ekb']) ) {
			var base_form = this.FormPanel.getForm();
			var usluga_combo = base_form.findField('UslugaComplex_id');

			if (this.only351Group) {
				usluga_combo.getStore().baseParams.UslugaComplexPartition_CodeList = Ext.util.JSON.encode([351]);
			} else if ( this.parentClass == 'EvnVizit' || this.parentClass == 'EvnPL' ) {
				if ('bud' == paytype_sysnick) {
					usluga_combo.getStore().baseParams.UslugaComplexPartition_CodeList = Ext.util.JSON.encode([351]);
				}
			} else {
				if ( base_form.findField('LpuSection_uid').getFieldValue('LpuSectionProfile_SysNick') == 'priem' ) {
					// приемное
					// https://redmine.swan.perm.ru/issues/69691
					usluga_combo.getStore().baseParams.UslugaComplexPartition_CodeList = Ext.util.JSON.encode([300,301]);
				}
				else if ((this.LpuUnitType_Code == 3) || (this.LpuUnitType_Code == 5)) {
					// днев
					usluga_combo.getStore().baseParams.UslugaComplexPartition_CodeList = Ext.util.JSON.encode([202,203,205]);
				} else {
					// кругл
					if ('oms' == paytype_sysnick && this.checkIsHTMedicalCare()) {
						usluga_combo.getStore().baseParams.UslugaComplexPartition_CodeList = Ext.util.JSON.encode([102,103,104,105,106,107]);
					}
					// @task https://redmine.swan.perm.ru/issues/109223
					else if ('fbud' == paytype_sysnick && this.checkIsHTMedicalCare()) {
						usluga_combo.getStore().baseParams.UslugaComplexPartition_CodeList = Ext.util.JSON.encode([102,103,104,105,107,156]);
					}
					else {
						usluga_combo.getStore().baseParams.UslugaComplexPartition_CodeList = Ext.util.JSON.encode([102,103,104,105,107]);
					}
				}
				if ('bud' == paytype_sysnick) {
					if (this.LpuUnitType_Code == 3 || this.LpuUnitType_Code == 5) {
						usluga_combo.getStore().baseParams.UslugaComplexPartition_CodeList = Ext.util.JSON.encode([252]);
					}
					else if (this.checkIsHTMedicalCare()) {
						usluga_combo.getStore().baseParams.UslugaComplexPartition_CodeList = Ext.util.JSON.encode([152,156]);
					} 
					else {
						usluga_combo.getStore().baseParams.UslugaComplexPartition_CodeList = Ext.util.JSON.encode([152]);
					}
				}
			}

			this.reloadUslugaComplexField();
		}
	},
	reloadUslugaComplexField: function(needUslugaComplex_id) {
		if (this.useCase == 'OperBlock' && Ext.isEmpty(needUslugaComplex_id)) {
			return;
		}

		var win = this;
		var base_form = this.FormPanel.getForm();
		var field = base_form.findField('UslugaComplex_id');

		field.getStore().baseParams.UslugaComplex_Date = Ext.util.Format.date(base_form.findField('EvnUslugaOper_setDate').getValue(), 'd.m.Y');
		field.getStore().baseParams.query = "";
		/*if (getRegionNick() == 'ekb') {
			base_form.findField('UslugaComplex_id').getStore().baseParams.UcplDiag_id = base_form.findField('Diag_id').getValue();
		}*/

		// повторно грузить одно и то же не нужно
		var newUslugaComplexParams = Ext.util.JSON.encode(field.getStore().baseParams);
		if (needUslugaComplex_id || newUslugaComplexParams != win.lastUslugaComplexParams) {
			win.lastUslugaComplexParams = newUslugaComplexParams;
			var currentUslugaComplex_id = base_form.findField('UslugaComplex_id').getValue();
			field.lastQuery = 'This query sample that is not will never appear';
			field.getStore().removeAll();

			var params = {};
			if (needUslugaComplex_id) {
				params.UslugaComplex_id = needUslugaComplex_id;
				currentUslugaComplex_id = needUslugaComplex_id;
			}

			win.getLoadMask(langs('Загрузка списка услуг')).show();
			field.getStore().load({
				callback: function (rec) {
					win.getLoadMask().hide();
					index = base_form.findField('UslugaComplex_id').getStore().findBy(function (rec) {
						return (rec.get('UslugaComplex_id') == currentUslugaComplex_id);
					});

					if (index >= 0) {
						var record = base_form.findField('UslugaComplex_id').getStore().getAt(index);
						field.setValue(record.get('UslugaComplex_id'));
						field.setRawValue(record.get('UslugaComplex_Code') + '. ' + record.get('UslugaComplex_Name'));
						base_form.findField('UslugaCategory_id').setValue(record.get('UslugaCategory_id'));
					} else {
						field.clearValue();
					}
					
					field.fireEvent('change', field, field.getValue());
					win.toggleVisibleExecutionBtnPanel();
				},
				params: params
			});
		}
	},
	setDefaultLpuSectionProfile: function() {
		var win = this;
		var base_form = this.FormPanel.getForm();

		if (base_form.findField('UslugaPlace_id').getValue() == 1 || (getRegionNick() == 'ekb' && base_form.findField('UslugaPlace_id').getValue() == 2)) {
			if (getRegionNick().inlist(['astra', 'ekb']) && !Ext.isEmpty(base_form.findField('MedStaffFact_id').getFieldValue('LpuSectionProfile_msfid'))) {
				base_form.findField('LpuSectionProfile_id').setValue(base_form.findField('MedStaffFact_id').getFieldValue('LpuSectionProfile_msfid'));
			} else if (!Ext.isEmpty(base_form.findField('LpuSection_uid').getFieldValue('LpuSectionProfile_id'))) {
				base_form.findField('LpuSectionProfile_id').setValue(base_form.findField('LpuSection_uid').getFieldValue('LpuSectionProfile_id'));
			} else {
				base_form.findField('LpuSectionProfile_id').setValue(null);
			}
		} else {
			base_form.findField('LpuSectionProfile_id').setValue(null);
		}
	},
	show: function() {
		sw.Promed.swEvnUslugaOperEditWindow.superclass.show.apply(this, arguments);

		this.findById('EUOperEF_EvnDrugPanel').collapse();
		this.findById('EUOperEF_EvnAggPanel').collapse();
		this.findById('EUOperEF_EvnUslugaOperAnestPanel').collapse();
		this.findById('EUOperEF_EvnUslugaOperBrigPanel').collapse();
		this.findById('EUOperEF_EvnUslugaPanel').expand();

		this.restore();
		this.center();
		this.maximize();
		var win = this;
		var base_form = this.FormPanel.getForm();
		base_form.reset();

		var isBuryatiya = (getGlobalOptions().region && getGlobalOptions().region.nick == 'buryatiya');
		/*linkedElements:(getGlobalOptions().region && getGlobalOptions().region.nick == 'ufa')?[]: [
								'EUOperEF_MedPersonalCombo'
							],*/
		base_form.findField('UslugaComplex_id').setAllowedUslugaComplexAttributeList([ 'oper' ]);
		base_form.findField('UslugaComplex_id').getStore().baseParams.LpuSection_id = null;
		base_form.findField('UslugaComplex_id').getStore().baseParams.UslugaComplex_Date = null;
		base_form.findField('UslugaComplex_id').getStore().baseParams.UcplDiag_id = null;
		base_form.findField('UslugaComplex_id').lastQuery = '';
		this.lastUslugaComplexParams = null;


		base_form.findField('UslugaComplexTariff_id').clearParams();

		if ( getGlobalOptions().region && getGlobalOptions().region.nick.inlist([ 'kareliya' ]) ) {
			base_form.findField('UslugaCategory_id').lastQuery = '';
			base_form.findField('UslugaCategory_id').getStore().filterBy(function(rec) {
				return !(rec.get('UslugaCategory_SysNick').inlist([ 'stomoms', 'stomklass' ]));
			});
		}

		this.action = null;
		this.callback = Ext.emptyFn;
		this.formStatus = 'edit';
		this.onHide = Ext.emptyFn;
		this.parentClass = null;
		this.LpuUnitType_Code = null;
		this.isVisibleDisDTPanel = false;
		this.only351Group = false;
		this.useCase = '';
		this.IsPriem = false;
		this.isVisibleExecutionPanel = false;

		this.toggleVisibleDisDTPanel('hide');
		base_form.findField('EvnUslugaOper_pid').getStore().removeAll();

		base_form.findField('Lpu_uid').enable();
		base_form.findField('Org_uid').disable();

		base_form.findField('Lpu_uid').disable();
		base_form.findField('LpuSection_uid').disable();
		base_form.findField('MedStaffFact_id').disable();
		base_form.findField('Org_uid').disable();

		this.findById(this.id+'_CardioFields').hide();
		base_form.findField('EvnUslugaOper_IsOperationDeath').hideContainer();

		if ( !arguments[0] || !arguments[0].formParams ) {
			sw.swMsg.alert(langs('Сообщение'), langs('Неверные параметры'), function() {this.hide();}.createDelegate(this) );
			return false;
		}

		this.ignorePaidCheck = null;
		if ( arguments[0].ignorePaidCheck ) {
			this.ignorePaidCheck = arguments[0].ignorePaidCheck;
		}

		if ( arguments[0].action ) {
			this.action = arguments[0].action;
		}

		if ( arguments[0].callback ) {
			this.callback = arguments[0].callback;
		}

		if ( arguments[0].onHide ) {
			this.onHide = arguments[0].onHide;
		}

		if ( arguments[0].parentClass ) {
			this.parentClass = arguments[0].parentClass;
		}

		if ( arguments[0].LpuUnitType_Code ) {
			this.LpuUnitType_Code = arguments[0].LpuUnitType_Code;
		}

		if ( arguments[0].only351Group ) {
			this.only351Group = arguments[0].only351Group;
		}

		if ( arguments[0].useCase ) {
			this.useCase = arguments[0].useCase;
		}

		if ( arguments[0].parentEvnComboData ) {
			base_form.findField('EvnUslugaOper_pid').getStore().loadData(arguments[0].parentEvnComboData);
		}

		if ( arguments[0].formParams.EvnUslugaOper_setDate ) {
			this.EvnUslugaOper_setDate = arguments[0].formParams.EvnUslugaOper_setDate;
		}

		if ( arguments[0].formParams.LpuSection_id ) {
			this.LpuSection_id = arguments[0].formParams.LpuSection_id;
		}

		if ( arguments[0].formParams.OperBrig ) {
			this.OperBrig = arguments[0].formParams.OperBrig;
		}

		if ( this.parentClass == 'EvnVizit' || this.parentClass == 'EvnPL' || this.parentClass == 'EvnPLStom' ) {
			base_form.findField('EvnUslugaOper_pid').setFieldLabel(langs('Посещение'));
		}
		else {
			base_form.findField('EvnUslugaOper_pid').setFieldLabel(langs('Движение'));
		}

		if ( this.action == 'add' ) {
			if (arguments[0].parentEvnComboData && arguments[0].parentEvnComboData[0] && arguments[0].parentEvnComboData[0].Evn_setTime) {
				
				var curTime = arguments[0].parentEvnComboData[0].Evn_setTime;
				if(getRegionNick() == 'kz'){
					var localCompDate = new Date();
					curTime = localCompDate.format('H:i');
				}
				base_form.findField('EvnUslugaOper_setTime').setValue(curTime);

			}
			this.findById('EUOperEF_EvnDrugPanel').isLoaded = true;
			this.findById('EUOperEF_EvnAggPanel').isLoaded = true;
			this.findById('EUOperEF_EvnUslugaOperAnestPanel').isLoaded = true;
			this.findById('EUOperEF_EvnUslugaOperBrigPanel').isLoaded = true;
			this.findById('EUOperEF_EvnUslugaPanel').isLoaded = true;
		}
		else {
			this.findById('EUOperEF_EvnDrugPanel').isLoaded = false;
			this.findById('EUOperEF_EvnAggPanel').isLoaded = false;
			this.findById('EUOperEF_EvnUslugaOperAnestPanel').isLoaded = false;
			this.findById('EUOperEF_EvnUslugaOperBrigPanel').isLoaded = false;
			this.findById('EUOperEF_EvnUslugaPanel').isLoaded = false;
		}

		this.toggleVisibleExecutionPanel('hide');
		this.toggleVisibleExecutionBtnPanel();


		if (this.useCase == 'OperBlock') {
			// поле движение не нужно, роидтельское событие определится по направлению при сохранении услуги
			base_form.findField('EvnUslugaOper_pid').hideContainer();
			base_form.findField('EvnPrescr_id').hideContainer();
		} else {
			base_form.findField('EvnUslugaOper_pid').showContainer();
			base_form.findField('EvnPrescr_id').showContainer();
		}

		base_form.findField('EvnUslugaOper_IsEndoskop').setValue(1);
		base_form.findField('EvnUslugaOper_IsKriogen').setValue(1);
		base_form.findField('EvnUslugaOper_IsLazer').setValue(1);
		base_form.findField('EvnUslugaOper_IsRadGraf').setValue(1);

		base_form.setValues(arguments[0].formParams);

		base_form.findField('MedSpecOms_id').onShowWindow(this);
		base_form.findField('LpuSectionProfile_id').onShowWindow(this);

		var index;
        var pay_type_combo = base_form.findField('PayType_id');

		var evn_combo = base_form.findField('EvnUslugaOper_pid');
		var lpu_combo = base_form.findField('Lpu_uid');
		var lpu_section_combo = base_form.findField('LpuSection_uid');
		var med_personal_combo = base_form.findField('MedStaffFact_id');
		var org_combo = base_form.findField('Org_uid');
		var usluga_combo = base_form.findField('UslugaComplex_id');
		var usluga_place_combo = base_form.findField('UslugaPlace_id');
        var prescr_combo = base_form.findField('EvnPrescr_id');
		var diag_set_class_combo = base_form.findField('DiagSetClass_id');
		var diag_combo = base_form.findField('Diag_id');
		var PersonAge = swGetPersonAge(arguments[0].Person_Birthday, new Date()) || null;
		if (PersonAge != -1){
			usluga_combo.getStore().baseParams.PersonAge = PersonAge;
		}

		if (getRegionNick() == 'ekb' && this.parentClass.inlist(['EvnVizit','EvnSection','EvnPL','EvnPS'])) {
			diag_set_class_combo.setAllowBlank(false);
			diag_set_class_combo.setContainerVisible(true);
			diag_combo.setAllowBlank(false);
			diag_combo.setContainerVisible(true);
		} else {
			diag_set_class_combo.setAllowBlank(true);
			diag_set_class_combo.setContainerVisible(false);
			diag_combo.setAllowBlank(true);
			diag_combo.setContainerVisible(false);
		}

		this.findById('EUOperEF_PersonInformationFrame').load({
			Person_id: (arguments[0].Person_id ? arguments[0].Person_id : ''),
			Person_Birthday: (arguments[0].Person_Birthday ? arguments[0].Person_Birthday : ''),
			Person_Firname: (arguments[0].Person_Firname ? arguments[0].Person_Firname : ''),
			Person_Secname: (arguments[0].Person_Secname ? arguments[0].Person_Secname : ''),
			Person_Surname: (arguments[0].Person_Surname ? arguments[0].Person_Surname : ''),
			callback: function() {
				var field = base_form.findField('EvnUslugaOper_setDate');
				if (Ext.isEmpty(PersonAge) || PersonAge == -1) {
					PersonAge = swGetPersonAge(win.findById('EUOperEF_PersonInformationFrame').getFieldValue('Person_Birthday'), new Date());
					usluga_combo.getStore().baseParams.PersonAge = PersonAge;
				}

				clearDateAfterPersonDeath('personpanelid', 'EUOperEF_PersonInformationFrame', field);
			}
		});

        prescr_combo.clearBaseParams();
        prescr_combo.getStore().removeAll();
        prescr_combo.uslugaCombo = usluga_combo;
        prescr_combo.uslugaCatCombo = base_form.findField('UslugaCategory_id');
        prescr_combo.hasLoaded = false;

		usluga_place_combo.fireEvent('change', usluga_place_combo, null);

		var loadMask = new Ext.LoadMask(this.getEl(), {msg: LOAD_WAIT});
		loadMask.show();

		this.findById('EUOperEF_EvnDrugGrid').getStore().removeAll();
		this.findById('EUOperEF_EvnDrugGrid').getTopToolbar().items.items[0].enable();
		this.findById('EUOperEF_EvnDrugGrid').getTopToolbar().items.items[1].disable();
		this.findById('EUOperEF_EvnDrugGrid').getTopToolbar().items.items[2].disable();
		this.findById('EUOperEF_EvnDrugGrid').getTopToolbar().items.items[3].disable();

		this.findById('EUOperEF_EvnAggGrid').getStore().removeAll();
		this.findById('EUOperEF_EvnAggGrid').getTopToolbar().items.items[0].enable();
		this.findById('EUOperEF_EvnAggGrid').getTopToolbar().items.items[1].disable();
		this.findById('EUOperEF_EvnAggGrid').getTopToolbar().items.items[2].disable();
		this.findById('EUOperEF_EvnAggGrid').getTopToolbar().items.items[3].disable();

		this.findById('EUOperEF_EvnUslugaOperAnestGrid').getStore().removeAll();
		this.findById('EUOperEF_EvnUslugaOperAnestGrid').getTopToolbar().items.items[0].enable();
		this.findById('EUOperEF_EvnUslugaOperAnestGrid').getTopToolbar().items.items[1].disable();
		this.findById('EUOperEF_EvnUslugaOperAnestGrid').getTopToolbar().items.items[2].disable();
		this.findById('EUOperEF_EvnUslugaOperAnestGrid').getTopToolbar().items.items[3].disable();

		this.findById('EUOperEF_EvnUslugaOperBrigGrid').getStore().removeAll();
		this.findById('EUOperEF_EvnUslugaOperBrigGrid').getTopToolbar().items.items[0].enable();
		this.findById('EUOperEF_EvnUslugaOperBrigGrid').getTopToolbar().items.items[1].disable();
		this.findById('EUOperEF_EvnUslugaOperBrigGrid').getTopToolbar().items.items[2].disable();
		this.findById('EUOperEF_EvnUslugaOperBrigGrid').getTopToolbar().items.items[3].disable();

		if ( getStacOptions().oper_usluga_full_med_personal_list
			||(getRegionNick() == 'ufa' && !Ext.isEmpty(this.parentClass) && this.parentClass.inlist([ 'EvnPS', 'EvnSection'])) ) {
			lpu_section_combo.disableLinkedElements();
			med_personal_combo.disableParentElement();
		}
		else {
			lpu_section_combo.enableLinkedElements();
			med_personal_combo.enableParentElement();
		}

		var evn_usluga_oper_pid = null;
        usluga_combo.setUslugaComplex2011Id(null);
		// эта херня сбрасывает список разрешенных атрибутов, поэтому комментирую нахрен
		// https://redmine.swan.perm.ru/issues/43796
        // usluga_combo.setPrescriptionTypeCode(null);

		this.setUslugaComplexPartitionCodeList(pay_type_combo.getFieldValue('PayType_SysNick'));

		if (isBuryatiya) {
			usluga_combo.setPersonId(base_form.findField('Person_id').getValue());
		}

		if ( getRegionNick().inlist([ 'astra', 'ufa' ]) ) {
			base_form.findField('OperDiff_id').getStore().filterBy(function(rec) {
				return (
					(getRegionNick() == 'ufa' && rec.get('OperDiff_Code').toString().inlist([ '0','1','2','3','4','5' ]))
					|| (getRegionNick() == 'astra' && rec.get('OperDiff_Code').toString().inlist([ '0','1','2','3' ]))
				);
			});
		}

		if ( getRegionNick()==='msk' ) {
			win.FilePanel.setTitle("8. Файлы");
			win.EvnXmlPanelNarcosis.show();
		}

		base_form.findField('UslugaMedType_id').setContainerVisible(getRegionNick() === 'kz');

		switch ( this.action ) {
			case 'add':
				this.setTitle(WND_POL_EUOPERADD);
				this.enableEdit(true);

				// Для Уфы проставляем количество = 1
				// if ( getGlobalOptions().region && getGlobalOptions().region.nick == 'ufa' ) {
					base_form.findField('EvnUslugaOper_Kolvo').setValue(1);
				// }

				base_form.findField('UslugaComplex_id').getStore().baseParams.Person_id = base_form.findField('Person_id').getValue();

				this.FileUploadPanel.reset();

				this.EvnXmlPanel.doReset();
				this.EvnXmlPanel.collapse();
				this.EvnXmlPanel.LpuSectionField = base_form.findField('LpuSection_uid');
				this.EvnXmlPanel.MedStaffFactField = base_form.findField('MedStaffFact_id');

				this.EvnXmlPanelNarcosis.doReset();
				this.EvnXmlPanelNarcosis.collapse();
				this.EvnXmlPanelNarcosis.LpuSectionField = base_form.findField('LpuSection_uid');
				this.EvnXmlPanelNarcosis.MedStaffFactField = base_form.findField('MedStaffFact_id');

				if ( Ext.isEmpty(pay_type_combo.getValue()) && getRegionNick() != 'kz' ) {
					pay_type_combo.setFieldValue('PayType_SysNick', getPayTypeSysNickOms());
				}
				if (pay_type_combo.getValue() > 0) {
					pay_type_combo.fireEvent('select', pay_type_combo, pay_type_combo.getStore().getAt(pay_type_combo.getStore().findBy(function(rec) {
						return (rec.get('PayType_id') == pay_type_combo.getValue());
					})));
				}
				pay_type_combo.setDisabled(getRegionNick().inlist(['ekb']) && 'bud' == pay_type_combo.getFieldValue('PayType_SysNick'));

				var ucat_cmb = base_form.findField('UslugaCategory_id');
				var ucat_rec;

				if ( ucat_cmb.getStore().getCount() == 1 ) {
					ucat_cmb.disable();
					ucat_rec = ucat_cmb.getStore().getAt(0);
					ucat_cmb.setValue(ucat_rec.get('UslugaCategory_id'));
				}
				else {
					// Для Перми по умолчанию подставляем услуги ГОСТ-2011
					// https://redmine.swan.perm.ru/issues/53028
					var UslugaCategory_SysNick = '';
					switch(getRegionNick())
					{

						// Для Перми по умолчанию подставляем услуги ГОСТ-2011
						// https://redmine.swan.perm.ru/issues/53028
						case 'perm':
						case 'pskov':
						case 'kareliya':
						case 'adygeya':	
						case 'penza':
							UslugaCategory_SysNick = 'gost2011';
							break;
						case 'ekb':
							UslugaCategory_SysNick = 'tfoms';
							break;
						case 'kaluga':
							UslugaCategory_SysNick = 'lpusectiontree';
							break;
						default:
							break;
					}
					if(UslugaCategory_SysNick){
						index = ucat_cmb.getStore().findBy(function(rec) {
							return (rec.get('UslugaCategory_SysNick') == UslugaCategory_SysNick);
						});
						ucat_rec = ucat_cmb.getStore().getAt(index);
					} else {
						ucat_rec = ucat_cmb.getStore().getById(ucat_cmb.getValue());
					}
				}

				if ( ucat_rec ) {
					ucat_cmb.fireEvent('select', ucat_cmb, ucat_rec);
				}

                if ( !Ext.isEmpty(this.parentClass) && this.parentClass.inlist([ 'EvnPLStom']) ) {
					evn_combo.disable();
                    evn_usluga_oper_pid = evn_combo.getValue();
				}
				else if ( this.parentClass.inlist([ 'EvnVizit' ]) && evn_combo.getStore().getCount() > 0 ) {
					evn_combo.disable();
					evn_usluga_oper_pid = evn_combo.getStore().getAt(0).get('Evn_id');
				}
				else if ( this.parentClass.inlist([ 'EvnPL', 'EvnPS' ]) && evn_combo.getStore().getCount() > 0 ) {
					evn_usluga_oper_pid = evn_combo.getStore().getAt(0).get('Evn_id');
				}
				else if ( this.parentClass.inlist([ 'EvnSection' ]) && evn_combo.getStore().getCount() > 0 ) {
					evn_combo.setValue(evn_combo.getStore().getAt(0).get('Evn_id'));
					evn_combo.disable();
					evn_usluga_oper_pid = evn_combo.getStore().getAt(0).get('Evn_id');
				}
				else {
					evn_usluga_oper_pid = evn_combo.getValue();
				}
                if (!evn_usluga_oper_pid) {
                    prescr_combo.setValue(null);
                    prescr_combo.disable();
					usluga_combo.getStore().baseParams.EvnUsluga_pid = null;
					usluga_combo.getStore().baseParams.LpuSection_pid = null;
                } else {
                    prescr_combo.setPrescriptionTypeCode(7);
                    prescr_combo.getStore().baseParams.EvnPrescr_pid = evn_usluga_oper_pid;
                    prescr_combo.enable();
					usluga_combo.getStore().baseParams.EvnUsluga_pid = evn_usluga_oper_pid;
					usluga_combo.getStore().baseParams.LpuSection_pid = evn_combo.getStore().getAt(0).get('LpuSection_id') || null;
                }

				var diag_set_class_id = diag_set_class_combo.getValue();
				if ( !Ext.isEmpty(diag_combo.getValue()) ) {
					diag_combo.getStore().load({
						callback: function() {
							diag_combo.setValue(diag_combo.getValue());
							diag_combo.onChange(diag_combo, diag_combo.getValue());

							if (diag_set_class_combo.getStore().findBy(function(rec) { return rec.get('DiagSetClass_id') == diag_set_class_id; }) >= 0) {
								diag_set_class_combo.setValue(diag_set_class_id);
							}
						},
						params: {where: "where DiagLevel_id = 4 and Diag_id = " + diag_combo.getValue()}
					});
				}

				base_form.findField('OperDiff_id').setFieldValue('OperDiff_Code', 0);

                if ( prescr_combo.getValue() ) {
                    //prescr_combo.disable();
                    prescr_combo.getStore().baseParams.newEvnPrescr_id = prescr_combo.getValue();
                    // при выполнении назначения с оказанием услуги
                    // нужно автоматически подставлять совпадающую по эталонным полям услугу,
                    // на комбо услуг накладывать дополнительный фильтр по атрибуту услуги соответственно типу назначения
                    prescr_combo.getStore().load({
                        callback: function(){
                            // чтобы НЕ дать возможность выбрать другое назначение
                            prescr_combo.hasLoaded = true;
                            prescr_combo.setValue(prescr_combo.getValue());
                            index = prescr_combo.getStore().findBy(function(rec) {
                                return (rec.get(prescr_combo.valueField) == prescr_combo.getValue());
                            });
                            var rec = prescr_combo.getStore().getAt(index);
                            if (rec) {
                                if (rec.get('EvnPrescr_setDate')) {
                                    base_form.findField('EvnUslugaOper_setDate').setValue(rec.get('EvnPrescr_setDate'));
                                } else {
                                    base_form.findField('EvnUslugaOper_setDate').setValue(getGlobalOptions().date);
                                }
                                base_form.findField('EvnUslugaOper_setDate').fireEvent('change', base_form.findField('EvnUslugaOper_setDate'), base_form.findField('EvnUslugaOper_setDate').getValue());

                                //если услуга добавляется по назначению, то
                                //если ЛПУ назначения и места выполнения равны
                                if ( rec.get('Lpu_id') == getGlobalOptions().lpu_id) {
                                    //указываем место выполнение Отделение ЛПУ
                                    usluga_place_combo.setValue(1);
                                    usluga_place_combo.fireEvent('change', usluga_place_combo, 1);

                                    index = lpu_section_combo.getStore().findBy(function(rec) {
                                        return ( rec.get('LpuSection_id') == getGlobalOptions().CurLpuSection_id );
                                    });

                                    if ( index >= 0 ) {
                                        lpu_section_combo.setValue(getGlobalOptions().CurLpuSection_id);
                                        lpu_section_combo.fireEvent('change', lpu_section_combo, getGlobalOptions().CurLpuSection_id);
                                    }
                                } else {
                                    //указываем место выполнение Другое ЛПУ
                                    usluga_place_combo.setValue(2);
                                    usluga_place_combo.fireEvent('change', usluga_place_combo, 2);
                                    lpu_combo.getStore().load({
                                        callback: function(records, options, success) {
                                            if (success) {
                                                lpu_combo.setValue(getGlobalOptions().lpu_id);
                                            }
                                        },
                                        params: {
                                            Org_id: getGlobalOptions().lpu_id,
                                            OrgType: 'lpu'
                                        }
                                    });
                                }
                            }
                            prescr_combo.fireEvent('change', prescr_combo, prescr_combo.getValue());
                        },
                        params: {
                            EvnPrescr_id: prescr_combo.getValue()
                        }
                    });
                }

				// Устанавливаем значение поля "Условие лечения" по умолчанию
				// https://redmine.swan.perm.ru/issues/22849
				var TreatmentConditionsType_Code = 0;

				switch ( this.parentClass ) {
					case 'EvnPL':
						TreatmentConditionsType_Code = 1;
					break;

					case 'EvnPS':
						TreatmentConditionsType_Code = 2;
					break;
				}

				index = base_form.findField('TreatmentConditionsType_id').getStore().findBy(function(rec) {
					return (rec.get('TreatmentConditionsType_Code') == TreatmentConditionsType_Code);
				});

				if ( index >= 0 ) {
					base_form.findField('TreatmentConditionsType_id').setValue(base_form.findField('TreatmentConditionsType_id').getStore().getAt(index).get('TreatmentConditionsType_id'));
				}

				LoadEmptyRow(this.findById('EUOperEF_EvnAggGrid'));
				LoadEmptyRow(this.findById('EUOperEF_EvnUslugaOperAnestGrid'));
				LoadEmptyRow(this.findById('EUOperEF_EvnUslugaOperBrigGrid'));

				var set_date = false;

				if (evn_usluga_oper_pid) {
					evn_combo.setValue(evn_usluga_oper_pid);
					evn_combo.fireEvent('change', evn_combo, evn_usluga_oper_pid, 0);
				}
				else {
					set_date = true;
				}

				setCurrentDateTime({
					callback: function(date) {
						var curDate = base_form.findField('EvnUslugaOper_setDate').getValue();
						if ( set_date || getRegionNick().inlist(['kz'])) {
							if(getRegionNick().inlist(['kz']) && date && (curDate.format('d.m.Y') != date.format('d.m.Y')) ){
								curDate = date;
								base_form.findField('EvnUslugaOper_setDate').setValue(date.format('d.m.Y'));
							}
							base_form.findField('EvnUslugaOper_setDate').fireEvent('change', base_form.findField('EvnUslugaOper_setDate'), curDate);
						}
					},
					dateField: base_form.findField('EvnUslugaOper_setDate'),
					loadMask: false,
					setDate: set_date,
					setTimeMaxValue: true,
					setDateMaxValue: true,
					setDateMinValue: false,
					setTime: false,
					timeField: base_form.findField('EvnUslugaOper_setTime'),
					windowId: this.id
				});

				// evn_combo.setValue(evn_combo.getStore().getAt(0).get('Evn_id'));

				if ( evn_combo.disabled ) {
					base_form.findField('EvnUslugaOper_setDate').focus(true, 250);
				}
				else {
					evn_combo.focus(true, 250);
				}
				
				if (!getRegionNick().inlist(['astra', 'ekb']) && arguments[0].parentEvnComboData && arguments[0].parentEvnComboData[0] && arguments[0].parentEvnComboData[0].LpuSectionProfile_id) {
					base_form.findField('LpuSectionProfile_id').setValue(arguments[0].parentEvnComboData[0].LpuSectionProfile_id);
				}

				if (getRegionNick() === 'kz') {
					base_form.findField('UslugaMedType_id').setFieldValue('UslugaMedType_Code', '1400');
					pay_type_combo.disable();
				}

				loadMask.hide();

				//base_form.clearInvalid();
				base_form.items.each(function(f) {
					f.validate();
				});
			break;

			case 'edit':
			case 'view':
				var evn_usluga_oper_id = base_form.findField('EvnUslugaOper_id').getValue();

				if ( !evn_usluga_oper_id ) {
					loadMask.hide();
					this.hide();
					return false;
				}

				//загружаем файлы
				this.FileUploadPanel.reset();
				this.FileUploadPanel.listParams = {
					Evn_id: evn_usluga_oper_id
				};
				this.FileUploadPanel.loadData({
					Evn_id: evn_usluga_oper_id
				});

				this.EvnXmlPanel.doReset();
				this.EvnXmlPanel.collapse();
				this.EvnXmlPanel.LpuSectionField = base_form.findField('LpuSection_uid');
				this.EvnXmlPanel.MedStaffFactField = base_form.findField('MedStaffFact_id');

				this.EvnXmlPanelNarcosis.doReset();
				this.EvnXmlPanelNarcosis.collapse();
				this.EvnXmlPanelNarcosis.LpuSectionField = base_form.findField('LpuSection_uid');
				this.EvnXmlPanelNarcosis.MedStaffFactField = base_form.findField('MedStaffFact_id');

				base_form.load({
					failure: function() {
						loadMask.hide();
						sw.swMsg.alert(langs('Ошибка'), langs('Ошибка при загрузке данных формы'), function() {this.hide();}.createDelegate(this) );
					}.createDelegate(this),
					params: {
						'class': 'EvnUslugaOper',
						'id': evn_usluga_oper_id
					},
					success: function(form, response) {
						// В зависимости от accessType переопределяем this.action
						if ( base_form.findField('accessType').getValue() == 'view' ) {
							this.action = 'view';
						}
						
						if (response.response && response.response.responseText) {
							var response = Ext.util.JSON.decode(response.response.responseText);
							if (response[0] && response[0].parentEvnComboData && response[0].parentEvnComboData.length) {
								evn_combo.getStore().loadData(response[0].parentEvnComboData);
							}
						}

						this.EvnXmlPanel.setReadOnly('view' == base_form.findField('accessType').getValue());
						this.EvnXmlPanel.setBaseParams({
							userMedStaffFact: sw.Promed.MedStaffFactByUser.last,
							UslugaComplex_id: base_form.findField('UslugaComplex_id').getValue(),
							Server_id: base_form.findField('Server_id').getValue(),
							Evn_id: base_form.findField('EvnUslugaOper_id').getValue()
						});
						this.EvnXmlPanel.doLoadData();

						this.EvnXmlPanelNarcosis.setReadOnly('view' == base_form.findField('accessType').getValue());
						this.EvnXmlPanelNarcosis.setBaseParams({
							userMedStaffFact: sw.Promed.MedStaffFactByUser.last,
							UslugaComplex_id: base_form.findField('UslugaComplex_id').getValue(),
							Server_id: base_form.findField('Server_id').getValue(),
							Evn_id: base_form.findField('EvnUslugaOper_id').getValue()
						});
						this.EvnXmlPanelNarcosis.doLoadData();

						this.findById('EUOperEF_PersonInformationFrame').load({
							Person_id: base_form.findField('Person_id').getValue(),
							callback: function() {
								var field = base_form.findField('EvnUslugaOper_setDate');
								if (Ext.isEmpty(PersonAge) || PersonAge == -1) {
									PersonAge = swGetPersonAge(win.findById('EUOperEF_PersonInformationFrame').getFieldValue('Person_Birthday'), new Date());
									usluga_combo.getStore().baseParams.PersonAge = PersonAge;
								}

								clearDateAfterPersonDeath('personpanelid', 'EUOperEF_PersonInformationFrame', field);
							}
						});

						if ( this.action == 'edit' ) {
							this.setTitle(WND_POL_EUOPEREDIT);
							this.enableEdit(true);
						}
						else {
							this.setTitle(WND_POL_EUOPERVIEW);
							this.enableEdit(false);
						}

						if (this.useCase == 'OperBlock') {
							base_form.findField('UslugaCategory_id').disable();
							base_form.findField('UslugaComplex_id').disable();
						}

						if ( this.action == 'edit' ) {
							setCurrentDateTime({
								dateField: base_form.findField('EvnUslugaOper_setDate'),
								loadMask: false,
								setDate: false,
								setTimeMaxValue: true,
								setDateMaxValue: true,
								windowId: this.id,
								timeField: base_form.findField('EvnUslugaOper_setTime')
							});
						}
						else {
							this.findById('EUOperEF_EvnDrugGrid').getTopToolbar().items.items[0].disable();
							this.findById('EUOperEF_EvnAggGrid').getTopToolbar().items.items[0].disable();
							this.findById('EUOperEF_EvnUslugaOperAnestGrid').getTopToolbar().items.items[0].disable();
							this.findById('EUOperEF_EvnUslugaOperBrigGrid').getTopToolbar().items.items[0].disable();
						}

						base_form.findField('UslugaComplex_id').getStore().baseParams.Person_id = base_form.findField('Person_id').getValue();
						
						// Простановка значений при выполнении операции 
						if ( this.action == 'edit' &&  this.useCase == 'OperBlock' ) {
							if ( Ext.isEmpty(base_form.findField('EvnUslugaOper_setDate').getValue()) && this.EvnUslugaOper_setDate ) {
								base_form.findField('EvnUslugaOper_setDate').setValue(Ext.util.Format.date(this.EvnUslugaOper_setDate, 'd.m.Y'));
								base_form.findField('EvnUslugaOper_setTime').setValue(Ext.util.Format.date(this.EvnUslugaOper_setDate, 'H:i'));
							}
							if ( Ext.isEmpty(base_form.findField('UslugaPlace_id').getValue()) ) {
								base_form.findField('UslugaPlace_id').setValue(1);
							}
							if ( Ext.isEmpty(base_form.findField('LpuSection_uid').getValue()) && this.LpuSection_id ) {
								base_form.findField('LpuSection_uid').setValue(this.LpuSection_id);
							}
							if ( Ext.isEmpty(base_form.findField('MedStaffFact_id').getValue()) && this.OperBrig && this.OperBrig.length ) {
								this.OperBrig.forEach(function(d, i, arr) {
									if (d.SurgType_Code == 1) {
										base_form.findField('MedStaffFact_id').setValue(d.MedStaffFact_id);
									}
								});
							}
						}

						var setDate = base_form.findField('EvnUslugaOper_setDate').getValue();
						var setTime = base_form.findField('EvnUslugaOper_setTime').getValue();
						var disDate = base_form.findField('EvnUslugaOper_disDate').getValue();
						var disTime = base_form.findField('EvnUslugaOper_disTime').getValue();

						if ((!Ext.isEmpty(disDate) || !Ext.isEmpty(disTime)) && (disDate-setDate != 0 || setTime != disTime)) {
							this.toggleVisibleDisDTPanel('show');
						}

						var diag_set_class_id = diag_set_class_combo.getValue();
						if ( !Ext.isEmpty(diag_combo.getValue()) ) {
							diag_combo.getStore().load({
								callback: function() {
									diag_combo.setValue(diag_combo.getValue());
									diag_combo.onChange(diag_combo, diag_combo.getValue());

									if (diag_set_class_combo.getStore().findBy(function(rec) { return rec.get('DiagSetClass_id') == diag_set_class_id; }) >= 0) {
										diag_set_class_combo.setValue(diag_set_class_id);	//чтобы не сбрасывалось после diag_combo.onChange
									}
								},
								params: {where: "where DiagLevel_id = 4 and Diag_id = " + diag_combo.getValue()}
							});
						}

						var evn_usluga_oper_pid = evn_combo.getValue();
						var lpu_uid = lpu_combo.getValue();
						var lpu_section_uid = lpu_section_combo.getValue();
						var lpu_section_profile_id = base_form.findField('LpuSectionProfile_id').getValue();
						var med_personal_id = med_personal_combo.getFieldValue('MedPersonal_id');
						var med_staff_fact_id = med_personal_combo.getValue();
						var org_uid = org_combo.getValue();
						var record;
						var usluga_complex_id = usluga_combo.getValue();
						var usluga_place_id = usluga_place_combo.getValue();
						var UslugaComplexTariff_id = base_form.findField('UslugaComplexTariff_id').getValue();

						lpu_section_combo.clearValue();
						med_personal_combo.clearValue();

						if ( this.action == 'edit' ) {
							var ucat_cmb = base_form.findField('UslugaCategory_id');
							var ucat_rec;
							if ( ucat_cmb.getStore().getCount() == 1 ) {
								ucat_cmb.disable();
								ucat_rec = ucat_cmb.getStore().getAt(0);
								ucat_cmb.setValue(ucat_rec.get('UslugaCategory_id'));
								ucat_cmb.fireEvent('select', ucat_cmb, ucat_rec);
							} else {
								ucat_rec = ucat_cmb.getStore().getById(ucat_cmb.getValue());
								if(ucat_rec) ucat_cmb.fireEvent('select', ucat_cmb, ucat_rec);
							}
						}

						var index = evn_combo.getStore().findBy(function(rec) {
							if ( rec.get('Evn_id') == evn_usluga_oper_pid ) {
								return true;
							}
							else {
								return false;
							}
						});

						record = evn_combo.getStore().getAt(index);
                        // Если есть права на изменение услуги, то назначение должно быть редактируемо
                        prescr_combo.setDisabled(prescr_combo.uslugaCombo.disabled);
                        //log({record: record, EvnPrescr_id: prescr_combo.getValue()});
						if ( record ) {
							evn_combo.setValue(evn_usluga_oper_pid);
                            prescr_combo.setPrescriptionTypeCode(7);
                            prescr_combo.getStore().baseParams.EvnPrescr_pid = evn_usluga_oper_pid;

                            if ( prescr_combo.getValue() ) {
                                prescr_combo.getStore().baseParams.savedEvnPrescr_id = prescr_combo.getValue();
                                prescr_combo.getStore().load({
                                    callback: function(){
                                        prescr_combo.setValue(prescr_combo.getValue());
                                        // чтобы дать возможность выбрать другое назначение
                                        prescr_combo.hasLoaded = false;
                                    },
                                    params: {
                                        EvnPrescr_id: prescr_combo.getValue()
                                    }
                                });
                            }
							usluga_combo.getStore().baseParams.EvnUsluga_pid = evn_combo.getValue();
							usluga_combo.getStore().baseParams.LpuSection_pid = record.get('LpuSection_id') || null;
						} else {

							// взрывалось, добавил проверку на наличие response.result, вероятно нужно вообще переписать данный кусок.
							//Если услуга добавлена из приёмного - подставляем приёмное
							if (response.result && response.result.data && !Ext.isEmpty(response.result.data.EvnUslugaCommon_pid_Name)) {
								evn_combo.setValue(response.result.data.EvnUslugaCommon_pid_Name);
							} else {
								evn_combo.clearValue();
							}
                            usluga_combo.getStore().baseParams.EvnUsluga_pid = null;
							usluga_combo.getStore().baseParams.LpuSection_pid = null;
						}

						if ( usluga_place_id ) {
							if ( this.action == 'edit' ) {
								usluga_place_combo.fireEvent('change', usluga_place_combo, usluga_place_id, -1);
							}

							index = usluga_place_combo.getStore().findBy(function(rec) {
								if ( rec.get('UslugaPlace_id') == usluga_place_id ) {
									return true;
								}
								else {
									return false;
								}
							});
							record = usluga_place_combo.getStore().getAt(index);

							if ( !record ) {
								loadMask.hide();
								return false;
							}

							switch ( Number(record.get('UslugaPlace_Code')) ) {
								case 1:
									// Отделение
									setLpuSectionGlobalStoreFilter({
										allowLowLevel: 'yes',
										isNotPolka: !this.parentClass.inlist([ 'EvnPL', 'EvnVizit' ]),
										isPolka: this.parentClass.inlist([ 'EvnPL', 'EvnVizit' ]),
										onDate: Ext.util.Format.date(base_form.findField('EvnUslugaOper_setDate').getValue(), 'd.m.Y')
									});

									setMedStaffFactGlobalStoreFilter({
										allowLowLevel: 'yes',
										isNotPolka: !this.parentClass.inlist([ 'EvnPL', 'EvnVizit' ]),
										isPolka: this.parentClass.inlist([ 'EvnPL', 'EvnVizit' ]),
										onDate: Ext.util.Format.date(base_form.findField('EvnUslugaOper_setDate').getValue(), 'd.m.Y')
									});

									base_form.findField('LpuSection_uid').getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));
									base_form.findField('MedStaffFact_id').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));

									index = lpu_section_combo.getStore().findBy(function(rec) {
										return (rec.get('LpuSection_id') == lpu_section_uid);
									});
									var lpu_section_record = lpu_section_combo.getStore().getAt(index);

									if ( lpu_section_record ) {
										lpu_section_combo.fireEvent('beforeselect', lpu_section_combo, lpu_section_record, index);
										lpu_section_combo.setValue(lpu_section_record.get('LpuSection_id'));
										lpu_section_combo.fireEvent('change', lpu_section_combo, lpu_section_combo.getValue());
									}

									index = med_personal_combo.getStore().findBy(function(rec) {
										if ( rec.get('MedStaffFact_id') == med_staff_fact_id ) {
											return true;
										}
										else {
											return false;
										}
									});
									var med_personal_record = med_personal_combo.getStore().getAt(index);
									if ( med_personal_record ) {
										med_personal_combo.setValue(med_personal_record.get('MedStaffFact_id'));
									}
								break;

								case 2:
									// Другое ЛПУ
									lpu_combo.getStore().load({
										callback: function(records, options, success) {
											if ( success ) {
												lpu_combo.setValue(lpu_uid);
												base_form.findField('LpuSection_uid').setValue(lpu_section_uid);
												base_form.findField('MedStaffFact_id').setValue(med_staff_fact_id);
												base_form.findField('LpuSectionProfile_id').setValue(lpu_section_profile_id);
												win.setLpuSectionAndMedStaffFactFilter(true);
											}
										},
										params: {
											Lpu_oid: lpu_uid,
											OrgType: 'lpu'
										}
									});
								break;

								case 3:
									// Другая организация
									org_combo.getStore().load({
										callback: function(records, options, success) {
											if ( success ) {
												org_combo.setValue(org_uid);
											}
										},
										params: {
											Org_id: org_uid,
											OrgType: 'org'
										}
									});
								break;

								default:
									loadMask.hide();
									return false;
								break;
							}
							base_form.findField('MedSpecOms_id').onChangeUslugaPlaceField(usluga_place_combo, record.get('UslugaPlace_Code'));
							base_form.findField('LpuSectionProfile_id').onChangeUslugaPlaceField(usluga_place_combo, record.get('UslugaPlace_Code'));
						} else {
							base_form.findField('MedSpecOms_id').onChangeUslugaPlaceField(usluga_place_combo, null);
							base_form.findField('LpuSectionProfile_id').onChangeUslugaPlaceField(usluga_place_combo, null);
						}
						
						setTimeout(function(){
							if (lpu_section_profile_id)
								base_form.findField('LpuSectionProfile_id').setValue(lpu_section_profile_id);
						}, 250);

						if ( !Ext.isEmpty(lpu_section_uid) ) {
							usluga_combo.getStore().baseParams.LpuSection_id = lpu_section_uid;
						}

						if ( !Ext.isEmpty(usluga_complex_id) ) {
							win.reloadUslugaComplexField(usluga_complex_id);
						}

						base_form.findField('UslugaComplexTariff_id').setParams({
							 LpuSection_id: lpu_section_uid
							,PayType_id: base_form.findField('PayType_id').getValue()
							,Person_id: base_form.findField('Person_id').getValue()
							,UslugaComplex_id: usluga_complex_id
							,UslugaComplexTariff_Date: base_form.findField('EvnUslugaOper_setDate').getValue()
						});

						if ( !Ext.isEmpty(UslugaComplexTariff_id) ) {
							base_form.findField('UslugaComplexTariff_id').getStore().load({
								callback: function() {
									if ( base_form.findField('UslugaComplexTariff_id').getStore().getCount() > 0 ) {
										base_form.findField('UslugaComplexTariff_id').setValue(UslugaComplexTariff_id);
										base_form.findField('UslugaComplexTariff_id').fireEvent('change', base_form.findField('UslugaComplexTariff_id'), UslugaComplexTariff_id);
									}
									else {
										base_form.findField('UslugaComplexTariff_id').clearValue();
									}
								}.createDelegate(this),
								params: {
									UslugaComplexTariff_id: UslugaComplexTariff_id
								}
							});
						}

						if ( this.action == 'edit' ) {
							pay_type_combo.setDisabled(getRegionNick().inlist(['ekb']) && 'bud' == pay_type_combo.getFieldValue('PayType_SysNick'));
							if ( evn_combo.isVisible() && evn_usluga_oper_pid != null && evn_usluga_oper_pid.toString().length > 0 && evn_combo.getFieldValue('IsPriem') == 2 ) {
								this.IsPriem = true;
								evn_combo.focus(true, 250);
							}
							else if ( this.parentClass != null || (evn_usluga_oper_pid != null && evn_usluga_oper_pid.toString().length > 0) ) {
								evn_combo.disable();
								base_form.findField('EvnUslugaOper_setDate').focus(true, 250);
							}
							else {
								evn_combo.focus(true, 250);
							}
						}
						else {
							this.buttons[this.buttons.length - 1].focus();
						}
						var EvnUslugaOper_IsOpenHeart_Combo = base_form.findField('EvnUslugaOper_IsOpenHeart');
						EvnUslugaOper_IsOpenHeart_Combo.fireEvent('change',EvnUslugaOper_IsOpenHeart_Combo,EvnUslugaOper_IsOpenHeart_Combo.getValue());
						loadMask.hide();
						
						base_form.findField('LpuSectionProfile_id').onChangeDateField(base_form.findField('EvnUslugaOper_setDate') , base_form.findField('EvnUslugaOper_setDate').getValue());

						if(!Ext.isEmpty(response[0].UslugaExecutionType_id) && response[0].UslugaExecutionType_id != 1 ){
							this.toggleVisibleExecutionPanel('show');
							base_form.findField('UslugaExecutionType_id').setValue(response[0].UslugaExecutionType_id);
							base_form.findField('UslugaExecutionReason_id').setValue(response[0].UslugaExecutionReason_id);
						}

						//base_form.clearInvalid();
						base_form.items.each(function(f) {
							f.validate();
						});

						if (getRegionNick() == 'kz') {
							pay_type_combo.disable();
						}
					}.createDelegate(this),
					url: '/?c=EvnUsluga&m=loadEvnUslugaEditForm'
				});
			break;

			default:
				loadMask.hide();
				this.hide();
			break;
		}
	},
	setDisDT: function() {
		if ( this.isVisibleDisDTPanel ) {
			return false;
		}

		var base_form = this.FormPanel.getForm();

		base_form.findField('EvnUslugaOper_disDate').setValue(base_form.findField('EvnUslugaOper_setDate').getValue());
		base_form.findField('EvnUslugaOper_disTime').setValue(base_form.findField('EvnUslugaOper_setTime').getValue());
	},
	toggleVisibleDisDTPanel: function(action) {
		var base_form = this.FormPanel.getForm();

		if (action == 'show') {
			this.isVisibleDisDTPanel = false;
		} else if (action == 'hide') {
			this.isVisibleDisDTPanel = true;
		}

		if (this.isVisibleDisDTPanel) {
			this.findById('EUOperEF_EvnUslugaDisDTPanel').hide();
			this.findById('EUOperEF_ToggleVisibleDisDTBtn').setText(langs('Уточнить период выполнения'));
			base_form.findField('EvnUslugaOper_disDate').setAllowBlank(true);
			base_form.findField('EvnUslugaOper_disTime').setAllowBlank(true);
			base_form.findField('EvnUslugaOper_disDate').setValue(null);
			base_form.findField('EvnUslugaOper_disTime').setValue(null);
			base_form.findField('EvnUslugaOper_disDate').setMaxValue(undefined);
			this.isVisibleDisDTPanel = false;
		} else {
			this.findById('EUOperEF_EvnUslugaDisDTPanel').show();
			this.findById('EUOperEF_ToggleVisibleDisDTBtn').setText(langs('Скрыть поля'));
			base_form.findField('EvnUslugaOper_disDate').setAllowBlank(false);
			base_form.findField('EvnUslugaOper_disTime').setAllowBlank(false);
			base_form.findField('EvnUslugaOper_disDate').setMaxValue(getGlobalOptions().date);
			this.isVisibleDisDTPanel = true;
		}
	},
	width: 700,
	toggleVisibleExecutionBtnPanel: function() {
		var
			base_form = this.FormPanel.getForm(),
			win = this;

		if ( getRegionNick() == 'perm' ) {
			var UslugaComplex_AttributeList = base_form.findField('UslugaComplex_id').getFieldValue('UslugaComplex_AttributeList');

			if (
				win.parentClass.inlist(['EvnPS','EvnSection'])
				|| (
					!Ext.isEmpty(UslugaComplex_AttributeList)
					&& UslugaComplex_AttributeList.indexOf('kab_early_zno') != -1
				)
			) {
				win.findById('EUOperEF_ToggleVisibleExecutionPanel').show();
			}
			else {
				win.findById('EUOperEF_ToggleVisibleExecutionPanel').hide();
				win.toggleVisibleExecutionPanel('hide');
			}
		}
		else {
			win.findById('EUOperEF_ToggleVisibleExecutionPanel').hide();
			win.toggleVisibleExecutionPanel('hide');
		}
	},
	toggleVisibleExecutionPanel: function(action){

		var win = this,
			base_form = win.FormPanel.getForm();

		if (action == 'show') {
			win.isVisibleExecutionPanel = false;
		} else if (action == 'hide') {
			win.isVisibleExecutionPanel = true;
		}
		if(!win.findById('EUOperEF_EvnUslugaExecutionPanel').isVisible() && !action){
			win.isVisibleExecutionPanel = false;
		}

		if (win.isVisibleExecutionPanel) {
			win.findById('EUOperEF_EvnUslugaExecutionPanel').hide();
			win.findById('EUOperEF_ToggleVisibleExecutionPanelBtn').setText(langs('Уточнить объём выполнения'));
			base_form.findField('UslugaExecutionReason_id').setAllowBlank(true);
			if(win.action === 'add'){
				base_form.findField('UslugaExecutionReason_id').clearValue();
				base_form.findField('UslugaExecutionType_id').reset();
			}

			win.isVisibleExecutionPanel = false;
		} else {
			win.findById('EUOperEF_EvnUslugaExecutionPanel').show();
			win.findById('EUOperEF_ToggleVisibleExecutionPanelBtn').setText(langs('Скрыть объём выполнения'));
			win.isVisibleExecutionPanel = true;

			if ( win.parentClass.inlist(['EvnPS','EvnSection']) ) {
				base_form.findField('UslugaExecutionType_id').items.items[1].show();
			}
			else {
				base_form.findField('UslugaExecutionType_id').items.items[1].hide();
			}

			base_form.findField('UslugaExecutionReason_id').getStore().clearFilter();

			if ( !win.parentClass.inlist(['EvnPS','EvnSection']) ) {
				base_form.findField('UslugaExecutionReason_id').lastQuery = '';
				base_form.findField('UslugaExecutionReason_id').getStore().filterBy(function(rec) {
					return (rec.get('UslugaExecutionReason_id') == 1 || rec.get('UslugaExecutionReason_id') == 2);
				});
			}
		}
	}
});

