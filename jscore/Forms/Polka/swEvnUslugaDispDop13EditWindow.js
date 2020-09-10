/**
* swEvnUslugaDispDop13EditWindow - окно редактирования/добавления выполнения лабораторного исследования по доп. диспансеризации
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package			Polka
* @access			public
* @copyright		Copyright (c) 2013 Swan Ltd.
* @author			Dmitry Vlasenko
* @originalauthor	Ivan Petukhov aka Lich (megatherion@list.ru) / Stas Bykov aka Savage (savage1981@gmail.com)
* @version			20.05.2013
* @comment			Префикс для id компонентов EUDD13EW (swEvnUslugaDispDop13EditWindow)
*
*
* Использует: окно редактирования талона по доп. диспансеризации (swEvnPLDispDop13EditWindow)
*/

sw.Promed.swEvnUslugaDispDop13EditWindow = Ext.extend(sw.Promed.BaseForm, {
	action: null,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	collapsible: true,
	formStatus: 'edit',
	doSave: function(callback, nothide, options) {
		var win = this;
		if ( win.formStatus == 'save' || win.action == 'view' ) {
			return false;
		}
		win.formStatus = 'save';

		if ( typeof options != 'object' ) {
			options = new Object();
		}
		
		// проверяем заполненность, отправляем на сервер
		var base_form = this.findById('EUDD13EW_EvnUslugaDispDopEditForm').getForm();
		
		if ( !base_form.isValid() )
		{
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					win.formStatus = 'edit';
					win.findById('EUDD13EW_EvnUslugaDispDopEditForm').getFirstInvalidEl().focus(false);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		if (this.type.inlist(['DispTeenInspectionPeriod','DispTeenInspectionProf','DispTeenInspectionPred']) && Ext.isEmpty(this.OrpDispSpec_Code)) {
			if (getRegionNick() != 'kz' && getGlobalOptions().disp_control > 1) // Если выбрано предупреждение или запрет
			{
				var fields_list = "";

				if (Ext.isEmpty(base_form.findField('EvnUslugaDispDop_Result').getValue())) {
					fields_list += 'Результат <br>';
				}

				if (fields_list.length > 0) {
					if (getGlobalOptions().disp_control == 2 && !options.ignoreEmptyFields) {
						sw.swMsg.show({
							buttons: Ext.Msg.YESNO,
							fn: function (buttonId) {
								if (buttonId == 'yes') {
									options.ignoreEmptyFields = true;
									win.doSave(callback, nothide, options);
								}
								else {
									return false;
								}
							},
							msg: 'Внимание! Не заполнены поля, обязательные при экспорте на федеральный портал: <br>' + fields_list + '<br> Сохранить?',
							title: 'Предупреждение'
						});

						win.formStatus = 'edit';
						return false;
					}
					if (getGlobalOptions().disp_control == 3) {
						sw.swMsg.alert('Ошибка', 'Не заполнены поля, обязательные при экспорте на федеральный портал: <br>' + fields_list);

						win.formStatus = 'edit';
						return false;
					}
				}
			}
		}

		if (getRegionNick() == 'pskov' && !options.ignoreCheckPersonAge && this.SurveyType_Code == 27) {
			// Реализовать контроль при сохранении осмотра врача-педиатра (ВОП).
			// Если на дату осмотра врача-педиатра возраст пациента не соответствует значению, указанному в поле «Возрастная группа», то выводить
			// предупреждение: «Возраст пациента на дату осмотра врача-педиатра не соответствует указанной возрастной группе. (Сохранить / Отмена)».
			// При нажатии «Сохранить» - сохранять осмотр, При нажатии «Отмена» - сохранение отменить, возврат на форму редактирования осмотра.
			var newSetDate = base_form.findField('EvnUslugaDispDop_didDate').getValue();
			var age_start = -1;
			var month_start = -1;
			var age_end = -1;
			if ( !Ext.isEmpty(newSetDate) && win.AgeGroupDispRecord ) {
				age_start = swGetPersonAge(win.findById('EUDD13EW_PersonInformationFrame').getFieldValue('Person_Birthday'), newSetDate);
				var year = newSetDate.getFullYear();
				var endYearDate = new Date(year, 11, 31);
				age_end = swGetPersonAge(win.findById('EUDD13EW_PersonInformationFrame').getFieldValue('Person_Birthday'), endYearDate);
				month_start = swGetPersonAgeMonth(win.findById('EUDD13EW_PersonInformationFrame').getFieldValue('Person_Birthday'), newSetDate);

				if (!((
					win.AgeGroupDispRecord.get('AgeGroupDisp_From') <= age_end && win.AgeGroupDispRecord.get('AgeGroupDisp_To') >= age_end && age_end >= 4 // если на конец года не менее 4-ёх лет
					) || (
					win.AgeGroupDispRecord.get('AgeGroupDisp_From') <= age_start && win.AgeGroupDispRecord.get('AgeGroupDisp_To') >= age_start &&
					win.AgeGroupDispRecord.get('AgeGroupDisp_monthFrom') <= month_start && win.AgeGroupDispRecord.get('AgeGroupDisp_monthTo') >= month_start && age_end <= 3 // если на конец года не более 3 лет
				))) {
					sw.swMsg.show({
						buttons: Ext.Msg.YESNO,
						fn: function (buttonId) {
							if (buttonId == 'yes') {
								options.ignoreCheckPersonAge = true;
								win.doSave(callback, nothide, options);
							}
						},
						msg: langs('Возраст пациента на дату осмотра врача-педиатра не соответствует указанной возрастной группе. Сохранить?'),
						title: langs('Подтверждение сохранения')
					});

					win.formStatus = 'edit';
					return false;
				}
			}
		}

		if ( Ext.isEmpty(base_form.findField('UslugaComplex_id').getValue()) ) {
			sw.swMsg.alert(langs('Ошибка'), langs('Услуга должна быть заполнена'), function() {
				win.formStatus = 'edit';
				if (!base_form.findField('UslugaComplex_id').disabled) {
					base_form.findField('UslugaComplex_id').focus(true);
				}
			}.createDelegate(this));
			return false;
		}

		if ( Ext.isEmpty(base_form.findField('EvnUslugaDispDop_setDate').getValue()) && Ext.isEmpty(base_form.findField('EvnUslugaDispDop_didDate').getValue()) ) {
			sw.swMsg.alert(langs('Ошибка'), langs('Должна быть заполнена хотя бы одна дата'), function() {
				win.formStatus = 'edit';
				base_form.findField('EvnUslugaDispDop_setDate').focus(true);
			}.createDelegate(this));
			return false;
		}

		// https://redmine.swan.perm.ru/issues/44519
		if (
			options.ignoreEmptyDidDate != true
			&& Ext.isEmpty(base_form.findField('EvnUslugaDispDop_didDate').getValue())
			&& !Ext.isEmpty(base_form.findField('ExaminationPlace_id').getValue())
		) {
			win.formStatus = 'edit';

			sw.swMsg.show({
				buttons: Ext.Msg.YESNO,
				fn: function ( buttonId ) {
					if ( buttonId == 'yes' ) {
						options.ignoreEmptyDidDate = true;
						win.doSave(callback, nothide, options);
					}
					else {
						base_form.findField('EvnUslugaDispDop_didDate').focus(true);
					}
				},
				msg: langs('Дата выполнения осмотра/исследования не заполнена, продолжить сохранение?'),
				title: langs('Подтверждение сохранения')
			});

			win.formStatus = 'edit';
			return false;
		}
		// #181668 пока убрали контроль кроме Вологды
		// NGS: AN ADDITIONAL CHECK IS NOT NEEDED ANYMORE FOR VOLOGDA - #194032
		if(!(getRegionNick().inlist([/*'vologda'*/]) && win.object && win.object.inlist(['EvnPLDispDop13','EvnPLDispProf']))){
			options.ignoreCheckDiag = true;
		}
		//Проверка диагноза на наличие в EvnDiagDopDisp
		var Diag_id = base_form.findField('Diag_id').getValue();
		var Diag_Code = base_form.findField('Diag_id').getFieldValue('Diag_Code');
		var GroupDiag_Code = Diag_Code.slice(0,3);

		options.ignoreCheckDiag = options.ignoreCheckDiag == true? true : win['object'] == 'EvnPLDispTeenInspection';

		if (
			options.ignoreCheckDiag != true
			&& (
				(getRegionNick()=='krym' && Diag_Code !='Z00.0')
				|| ((getRegionNick()=='kareliya' || getRegionNick()=='penza') && Diag_Code !='Z01.8')
				|| (getRegionNick()!='krym' && getRegionNick()!='kareliya' && getRegionNick()!='penza' && Diag_Code !='Z10.8')
			)
		) {
			//раз грид не сохраняется автоматом, сделаем сначала проверку в рамках данной формы
			//на повтор диагноза:
			var index = win.EvnDiagDopDispGrid.getGrid().store.findBy(function(rec) {
				return rec.get('Diag_id') == Diag_id && rec.get('Diag_Code') != 'Z00.0';
			});
			if(index>-1) {
				win.formStatus = 'edit';
				sw.swMsg.alert(langs('Ошибка'), langs('У пациента уже указан диагноз')+' <b>'+Diag_Code+'</b><br>'
					+langs('Проверьте правильность введенных данных.'),
					function() {
						win.formStatus = 'edit';
						base_form.findField('Diag_id').focus(true);
					}.createDelegate(this)
				);
				return false;
			}
			//теперь на совпадение группы диагноза:
			index = win.EvnDiagDopDispGrid.getGrid().store.findBy(function(rec) {
				return rec.get('Diag_Code').slice(0,3) == GroupDiag_Code;
			});
			if(index>-1) {
				win.formStatus = 'edit';
				sw.swMsg.show({
					buttons: {yes: langs('Продолжить'), no: langs('Отмена')},
					fn: function ( buttonId ) {
						if ( buttonId == 'yes' ) {
							options.ignoreCheckDiag = true;
							win.doSave(callback, nothide, options);
						} else {
							win.formStatus = 'edit';
							base_form.findField('Diag_id').focus(true);
						}
					},
					msg: langs('У пациента уже указан диагноз группы')+' <b>'+GroupDiag_Code+'</b>',
					title: langs('Подтверждение сохранения'),
					width: 300
				});
				return false;
			}
			//теперь натурально проверка по бд:
			win.getLoadMask("Подождите, идет проверка диагноза...").show();

			Ext.Ajax.request({
				url: '/?c=EvnPLDispDop13&m=CheckDiag',
				params: {
					EvnPLDispDop13_id: base_form.findField('EvnVizitDispDop_pid').getValue(),
					Diag_id: base_form.findField('Diag_id').getValue(),
					EvnUslugaDispDop_id: base_form.findField('EvnUslugaDispDop_id').getValue()
				},
				failure: function(result_form, action) {
					win.getLoadMask().hide();
					sw.swMsg.show({
						buttons: Ext.Msg.YESNO,
						fn: function ( buttonId ) {
							if ( buttonId == 'yes' ) {
								options.ignoreCheckDiag = true;
								win.doSave(callback, nothide, options);
							}
							else {
								win.formStatus = 'edit';
							}
						},
						msg: langs('Ошибка при проверке на дублирование диагноза. Продолжить сохранение?'),
						title: langs('Подтверждение сохранения')
					});
				},
				success: function(response, action) {
					win.getLoadMask().hide();
					if (response.responseText != '') {
						var data = Ext.util.JSON.decode(response.responseText);
						if (data) {
							if(data == base_form.findField('Diag_id').getValue()) {
								sw.swMsg.alert(langs('Ошибка'), langs('У пациента уже указан диагноз')+' <b>'+Diag_Code+'</b><br>'
									+langs('Проверьте правильность введенных данных.'),
									function() {
										win.formStatus = 'edit';
										base_form.findField('Diag_id').focus(true);
									}.createDelegate(this)
								);
							} else {
								sw.swMsg.show({
									buttons: {yes: langs('Продолжить'), no: langs('Отмена')},
									fn: function ( buttonId ) {
										if ( buttonId == 'yes' ) {
											options.ignoreCheckDiag = true;
											win.doSave(callback, nothide, options);
										} else {
											win.formStatus = 'edit';
											base_form.findField('Diag_id').focus(true);
										}
									},
									msg: langs('У пациента уже указан диагноз группы')+' <b>'+GroupDiag_Code+'</b>',
									title: langs('Подтверждение сохранения'),
									width: 300
								});
							}
						} else {//проверка успешна, пересечений диагнозов нет
							options.ignoreCheckDiag = true;
							win.doSave(callback, nothide, options);
						}
					}
				}
			});

			win.formStatus = 'edit';
			return false;
		}

		var diag_code = '';
		var index;
		var lpu_section_profile_code = '';
		var params = {};
		var record;

		if (base_form.findField('EvnUslugaDispDop_didDate').disabled) {
			params.EvnUslugaDispDop_didDate = Ext.util.Format.date(base_form.findField('EvnUslugaDispDop_didDate').getValue(), 'd.m.Y');
		}

		params.UslugaComplex_id = base_form.findField('UslugaComplex_id').getValue();
		params.CytoUslugaComplex_id = base_form.findField('CytoUslugaComplex_id').getValue();

		index = base_form.findField('MedStaffFact_id').getStore().findBy(function(rec) {
			return (rec.get('MedStaffFact_id') == base_form.findField('MedStaffFact_id').getValue());
		});

		if ( index >= 0 ) {
			lpu_section_profile_code = base_form.findField('MedStaffFact_id').getStore().getAt(index).get('LpuSectionProfile_Code');
			base_form.findField('MedPersonal_id').setValue(base_form.findField('MedStaffFact_id').getStore().getAt(index).get('MedPersonal_id'));
		} else {
			base_form.findField('MedPersonal_id').setValue(null);
		}

		index = base_form.findField('CytoMedStaffFact_id').getStore().findBy(function(rec) {
			return (rec.get('MedStaffFact_id') == base_form.findField('CytoMedStaffFact_id').getValue());
		});

		if ( index >= 0 ) {
			base_form.findField('CytoMedPersonal_id').setValue(base_form.findField('CytoMedStaffFact_id').getStore().getAt(index).get('MedPersonal_id'));
		} else {
			base_form.findField('CytoMedPersonal_id').setValue(null);
		}

		index = base_form.findField('Diag_id').getStore().findBy(function(rec) {
			return (rec.get('Diag_id') == base_form.findField('Diag_id').getValue());
		});

		if ( index >= 0 ) {
			record = base_form.findField('Diag_id').getStore().getAt(index);

			diag_code = record.get('Diag_Code');
			if(diag_code)
				params.Diag_Code = diag_code;

			params.isOnkoDiag = (params.Diag_Code && params.Diag_Code.search(new RegExp("^(C|D0)", "i")) !== -1)?1:0;

			if ( !Ext.isEmpty(diag_code) && diag_code.substr(0, 1).toUpperCase() != 'Z' && !base_form.findField('DopDispDiagType_id').getValue() ) {
				sw.swMsg.show({
					buttons: Ext.Msg.OK,
					fn: function() {
						win.formStatus = 'edit';
						base_form.findField('DopDispDiagType_id').markInvalid(langs('Поле обязательно для заполнения при выбранном диагнозе'));
						base_form.findField('DopDispDiagType_id').focus(false);
					}.createDelegate(this),
					icon: Ext.Msg.WARNING,
					msg: langs('Не задан характер заболевания'),
					title: ERR_INVFIELDS_TIT
				});
				return false;
			}

			if ( getRegionNick() == 'ekb' ) {
				var sex_code = this.Sex_Code;
				var person_age = swGetPersonAge(this.findById('EUDD13EW_PersonInformationFrame').getFieldValue('Person_Birthday'), base_form.findField('EvnUslugaDispDop_didDate').getValue());
				var person_age_month = swGetPersonAgeMonth(this.findById('EUDD13EW_PersonInformationFrame').getFieldValue('Person_Birthday'), base_form.findField('EvnUslugaDispDop_didDate').getValue());
				var person_age_day = swGetPersonAgeDay(this.findById('EUDD13EW_PersonInformationFrame').getFieldValue('Person_Birthday'), base_form.findField('EvnUslugaDispDop_didDate').getValue());

				if ( person_age == -1 || person_age_month == -1 || person_age_day == -1 ) {
					this.formStatus = 'edit';
					sw.swMsg.alert(langs('Ошибка'), langs('Ошибка при определении возраста пациента'));
					return false;
				}
				if ( !sex_code || !(sex_code.toString().inlist([ '1', '2' ])) ) {
					this.formStatus = 'edit';
					sw.swMsg.alert(langs('Ошибка'), langs('Не указан пол пациента'));
					return false;
				}
				// если Sex_id не соответсвует полу пациента то "Выбранный диагноз не соответствует полу пациента"
				if ( !Ext.isEmpty(record.get('Sex_Code')) && Number(record.get('Sex_Code')) != Number(sex_code) ) {
					this.formStatus = 'edit';
					sw.swMsg.show({
						buttons: Ext.Msg.OK,
						fn: function(buttonId, text, obj) {
							base_form.findField('Diag_id').focus(true);
						}.createDelegate(this),
						icon: Ext.Msg.WARNING,
						msg: langs('Выбранный диагноз не соответствует полу пациента'),
						title: langs('Ошибка')
					});
					return false;
				}
				// если PersonAgeGroup_Code не соответсвует возрасту пациента то "Выбранный диагноз не соответствует возрасту пациента"
				if (
					(person_age < 18 && Number(record.get('PersonAgeGroup_Code')) == 1)
					|| ((person_age > 19 || (person_age == 18 && person_age_month >= 6)) && Number(record.get('PersonAgeGroup_Code')) == 2)
					|| ((person_age > 0 || (person_age == 0 && person_age_month >= 3)) && Number(record.get('PersonAgeGroup_Code')) == 3)
					|| (person_age_day >= 28 && Number(record.get('PersonAgeGroup_Code')) == 4)
					|| (person_age >= 4 && Number(record.get('PersonAgeGroup_Code')) == 5)
				) {
					this.formStatus = 'edit';
					sw.swMsg.show({
						buttons: Ext.Msg.OK,
						fn: function(buttonId, text, obj) {
							base_form.findField('Diag_id').focus(true);
						}.createDelegate(this),
						icon: Ext.Msg.WARNING,
						msg: langs('Выбранный диагноз не соответствует возрасту пациента'),
						title: langs('Ошибка')
					});
					return false;
				}
			} else if ( getRegionNick() == 'buryatiya' ) {
				var sex_code = this.Sex_Code;
				if ( !sex_code || !(sex_code.toString().inlist([ '1', '2' ])) ) {
					this.formStatus = 'edit';
					sw.swMsg.alert(langs('Ошибка'), langs('Не указан пол пациента'));
					return false;
				}
				// если Sex_id не соответсвует полу пациента то "Выбранный диагноз не соответствует полу"
				if ( !Ext.isEmpty(record.get('Sex_Code')) && Number(record.get('Sex_Code')) != Number(sex_code) ) {
					this.formStatus = 'edit';
					sw.swMsg.show({
						buttons: Ext.Msg.OK,
						fn: function(buttonId, text, obj) {
							base_form.findField('Diag_id').focus(true);
						}.createDelegate(this),
						icon: Ext.Msg.WARNING,
						msg: langs('Выбранный диагноз не соответствует полу'),
						title: langs('Ошибка')
					});
					return false;
				}
				if (!options.ignoreDiagFinance) {
					// если DiagFinance_IsOms = 0
					if ( record.get('DiagFinance_IsOms') == 0 ) {
						this.formStatus = 'edit';
						sw.swMsg.show({
							buttons: Ext.Msg.YESNO,
							fn: function(buttonId, text, obj) {
								if ( buttonId == 'yes' ) {
									options.ignoreDiagFinance = true;
									this.doSave(callback, nothide, options);
								} else {
									base_form.findField('Diag_id').focus(true);
								}
							}.createDelegate(this),
							icon: Ext.MessageBox.QUESTION,
							msg: langs('Выбранный диагноз не оплачивается по ОМС, продолжить сохранение?'),
							title: langs(' Продолжить сохранение?')
						});
						return false;
					}
				}
			} else if ( getRegionNick() == 'astra' ) {
				if (!options.ignoreDiagFinance) {
					// если DiagFinance_IsOms = 0
					if ( record.get('DiagFinance_IsOms') == 0 ) {
						this.formStatus = 'edit';
						sw.swMsg.show({
							buttons: Ext.Msg.YESNO,
							fn: function(buttonId, text, obj) {
								if ( buttonId == 'yes' ) {
									options.ignoreDiagFinance = true;
									this.doSave(callback, nothide, options);
								} else {
									base_form.findField('Diag_id').focus(true);
								}
							}.createDelegate(this),
							icon: Ext.MessageBox.QUESTION,
							msg: langs('Выбранный диагноз не оплачивается по ОМС, продолжить сохранение?'),
							title: langs(' Продолжить сохранение?')
						});
						return false;
					}
				}
			} else if ( getRegionNick() == 'ufa' ) {
				// Проверка на финансирование по ОМС основного диагноза
				if ( lpu_section_profile_code.inlist([ '658', '684', '558', '584' ]) ) {
					if ( record.get('DiagFinance_IsHealthCenter') != 1 ) {
						sw.swMsg.alert(langs('Ошибка'), langs('Диагноз не оплачивается для Центров здоровья'), function() {
							win.formStatus = 'edit';
							base_form.findField('Diag_id').markInvalid(langs('Диагноз не оплачивается для Центров здоровья'));
							base_form.findField('Diag_id').focus(true);
						}.createDelegate(this));
						return false;
					}
				}
				else if ( record.get('DiagFinance_IsOms') == 0 ) {
					sw.swMsg.alert(langs('Ошибка'), langs('Диагноз не оплачивается по ОМС'), function() {
						win.formStatus = 'edit';
						base_form.findField('Diag_id').markInvalid(langs('Диагноз не оплачивается по ОМС'));
						base_form.findField('Diag_id').focus(true);
					}.createDelegate(this));
					return false;
				}
				else {
					var oms_spr_terr_code = this.OmsSprTerr_Code;
					var person_age = swGetPersonAge(this.findById('EUDD13EW_PersonInformationFrame').getFieldValue('Person_Birthday'), base_form.findField('EvnUslugaDispDop_didDate').getValue());
					var sex_code = this.Sex_Code;

					if ( person_age == -1 ) {
						sw.swMsg.alert(langs('Ошибка'), langs('Ошибка при определении возраста пациента'), function() {
							win.formStatus = 'edit';
						});
						return false;
					}

					if ( Ext.isEmpty(sex_code) || !(sex_code.toString().inlist([ '1', '2' ])) ) {
						sw.swMsg.alert(langs('Ошибка'), langs('Не указан пол пациента'), function() {
							win.formStatus = 'edit';
						});
						return false;
					}

					if ( person_age >= 18 ) {
						if ( Number(record.get('PersonAgeGroup_Code')) == 2 ) {
							sw.swMsg.alert(langs('Ошибка'), langs('Диагноз не оплачивается для взрослых'), function() {
								win.formStatus = 'edit';
								base_form.findField('Diag_id').markInvalid(langs('Диагноз не оплачивается для взрослых'));
								base_form.findField('Diag_id').focus(true);
							}.createDelegate(this));
							return false;
						}
					}
					else if ( Number(record.get('PersonAgeGroup_Code')) == 1 ) {
						sw.swMsg.alert(langs('Ошибка'), langs('Диагноз не оплачивается для детей'), function() {
							win.formStatus = 'edit';
							base_form.findField('Diag_id').markInvalid(langs('Диагноз не оплачивается для детей'));
							base_form.findField('Diag_id').focus(true);
						}.createDelegate(this));
						return false;
					}

					if ( Number(sex_code) == 1 ) {
						if ( Number(record.get('Sex_Code')) == 2 ) {
							sw.swMsg.alert(langs('Ошибка'), langs('Диагноз не соответствует полу пациента'), function() {
								win.formStatus = 'edit';
								base_form.findField('Diag_id').markInvalid(langs('Диагноз не соответствует полу пациента'));
								base_form.findField('Diag_id').focus(true);
							}.createDelegate(this));
							return false;
						}
					}
					else if ( Number(record.get('Sex_Code')) == 1 ) {
						sw.swMsg.alert(langs('Ошибка'), langs('Диагноз не соответствует полу пациента'), function() {
							win.formStatus = 'edit';
							base_form.findField('Diag_id').markInvalid(langs('Диагноз не соответствует полу пациента'));
							base_form.findField('Diag_id').focus(true);
						}.createDelegate(this));
						return false;
					}

					if ( getRegionNick() == 'ufa' && (Ext.isEmpty(oms_spr_terr_code) || oms_spr_terr_code != 61) && record.get('DiagFinance_IsAlien') == '0' ) {
						sw.swMsg.alert(langs('Ошибка'), langs('Диагноз не оплачивается для пациентов, застрахованных не в РБ'), function() {
							win.formStatus = 'edit';
							base_form.findField('Diag_id').markInvalid(langs('Диагноз не оплачивается для пациентов, застрахованных не в РБ'));
							base_form.findField('Diag_id').focus(true);
						}.createDelegate(this));
						return false;
					}
				}
			}
		}

		switch ( this.SurveyType_Code ) {
			case 4:
				var person_height = base_form.findField('person_height').getValue() / 100;
				var person_weight = base_form.findField('person_weight').getValue();

				if ( !Ext.isEmpty(person_height) && !Ext.isEmpty(person_weight) ) {
					var body_mass_index = person_weight / (person_height * person_height);
					base_form.findField('body_mass_index').setValue(body_mass_index.toFixed(1));
				}
				break;
		}

		if ( base_form.findField('ExaminationPlace_id').getValue() != 3 ) {
			base_form.findField('Lpu_uid').clearValue();
			base_form.findField('LpuSectionProfile_id').clearValue();
			base_form.findField('MedSpecOms_id').clearValue();
		}

		win.EvnDiagDopDispGrid.getGrid().getStore().clearFilter();
		params.EvnDiagDopDispGridData = Ext.util.JSON.encode(getStoreRecords( win.EvnDiagDopDispGrid.getGrid().getStore() ));
		win.EvnDiagDopDispGrid.getGrid().getStore().filterBy(function(record) {
			if (record.data.Record_Status != 3) { return true; } else { return false; }
		});

		if (this.ElectronicService_id) params.ElectronicService_id = this.ElectronicService_id;
		// Если сохранение вторичное и есть параметр игнора - отправляем
		if(options.ignoreCheckMorbusOnko)
			params.ignoreCheckMorbusOnko = 1;

		if (this.SurveyType_IsVizit == 2) {
			//проверяем наличие карты диспансеризации для определенных групп диагноза
			var personinfoframe = win.findById('EUDD13EW_PersonInformationFrame');
			var loadMask = new Ext.LoadMask(Ext.get(this.id), { msg: 'Проверка наличия карты диспансеризации...' });
			loadMask.show();
			Ext.Ajax.request({
				url: '/?c=EvnDiagDopDisp&m=checkDiagDisp',
				params: {
					Person_id: personinfoframe.getFieldValue('Person_id'),
					PersonEvn_id: base_form.findField('PersonEvn_id').getValue(),
					Diag_id: base_form.findField('Diag_id').getValue(),
					Date: base_form.findField('EvnUslugaDispDop_didDate').getValue().format('Y-m-d')
				},
				callback: function (options, success, response) {
					loadMask.hide();
					if (response.responseText != '') {
						var data = Ext.util.JSON.decode(response.responseText);

						if (!data.result && data.success) {
							sw.swMsg.show({
								buttons: Ext.Msg.YESNO,
								fn: function (buttonId) {
									if (buttonId == 'yes') {
										var formParams = new Object();
										var params_disp = new Object();

										formParams.Person_id = data.Person_id;
										formParams.Server_id = base_form.findField('Server_id').getValue();
										formParams.PersonDisp_begDate = getGlobalOptions().date;
										formParams.PersonDisp_DiagDate = getGlobalOptions().date;
										formParams.Diag_id = base_form.findField('Diag_id').getValue();

										params_disp.action = 'add';
										params_disp.callback = Ext.emptyFn;
										params_disp.formParams = formParams;
										params_disp.onHide = Ext.emptyFn;

										getWnd('swPersonDispEditWindow').show(params_disp);
									}
								},
								msg: langs('Пациент с диагнозом ' + base_form.findField('Diag_id').getFieldValue('Diag_Code') + ' нуждается в диспансерном наблюдении. Создать карту диспансерного наблюдения?'),
								title: langs('Подтверждение сохранения')
							});
						}
					}
				}
			});
		}

		win.getLoadMask("Подождите, идет сохранение...").show();
		base_form.submit({
			url: '/?c=EvnUslugaDispDop&m=saveEvnUslugaDispDop',
			failure: function(result_form, action) {
				win.formStatus = 'edit';
				win.getLoadMask().hide();
				if ( action.result ) {
					if ( action.result.Error_Msg || action.result.Alert_Msg ) {
						if(action.result.Alert_Msg){
							sw.swMsg.show({
								buttons: Ext.Msg.OKCANCEL,
								fn: function(buttonId, text, obj) {
									if ( buttonId == 'ok' ) {
										if(action.result.openSpecificAfterSave && base_form.findField('EvnVizitDispDop_id').getValue()){
											// открываем специфику, раз попросили
											win.openSpecific(false,true);
										}
										if (action.result.Error_Code == 289) {
											options.ignoreCheckMorbusOnko = 1;
											win.doSave(callback, nothide, options);
										}

									}
								}.createDelegate(this),
								icon: Ext.MessageBox.QUESTION,
								msg: action.result.Alert_Msg,
								title: 'Продолжить сохранение?'
							});
						} else {
							sw.swMsg.alert(langs('Ошибка'), action.result.Error_Msg);
						}
					}
					else {
						sw.swMsg.alert(langs('Ошибка'), langs('При сохранении произошли ошибки [Тип ошибки: 1]'));
					}
				}
			},
			params: params,
			success: function(result_form, action) {
				win.formStatus = 'edit';
				win.getLoadMask().hide();
				if ( action.result ) {
					if ( action.result.EvnUslugaDispDop_id ) {

						base_form.findField('EvnUslugaDispDop_id').setValue(action.result.EvnUslugaDispDop_id);

						if (action.result.EvnVizitDispDop_id) {
							base_form.findField('EvnVizitDispDop_id').setValue(action.result.EvnVizitDispDop_id);
						}

						win.EvnDirectionGrid.getGrid().getStore().baseParams.EvnDirection_pid = base_form.findField('EvnUslugaDispDop_id').getValue();
						var params = {};
						
						var items = win.findById('EUDD13EW_ResultsPanel').items.items;
						for (var key in items) {
							var obj = items[key];
							if (obj.name) {
								if (obj.name.inlist(win.inresults)) {
									params[obj.name] = obj.getValue();
								}
							}
							if (obj.hiddenName) {
								if (obj.hiddenName.inlist(win.inresults)) {
									params[obj.hiddenName] = obj.getValue();
								}
							}
						}

						if (win.DispClass_id.inlist([19,26])) {
							win.FileUploadPanel.listParams = {Evn_id: action.result.EvnUslugaDispDop_id};
							win.FileUploadPanel.saveChanges();
						}
						params.Diag_Code = base_form.findField('Diag_id').getFieldValue('Diag_Code');
						if(!callback && action.result.openSpecificAfterSave && base_form.findField('EvnVizitDispDop_id').getValue()){
							if(action.result.Alert_Msg){
								sw.swMsg.show({
									buttons: Ext.Msg.OKCANCEL,
									fn: function(buttonId, text, obj) {
										if ( buttonId == 'ok' ) {
											win.openSpecific(false,true);
										}
									}.createDelegate(this),
									icon: Ext.MessageBox.QUESTION,
									msg: action.result.Alert_Msg,
									title: 'Продолжить сохранение?'
								});
							} else {
								// открываем специфику (тем самым создаем ее)
								win.openSpecific(false,true);
							}
							nothide = true;
						}

						win.callback(params);
						if (typeof callback == 'function') {
							callback();
						}
						if (!nothide) {
							win.hide();
						}
					}
					else {
						if ( action.result.Error_Msg ) {
							sw.swMsg.alert(langs('Ошибка'), action.result.Error_Msg);
						}
						else {
							sw.swMsg.alert(langs('Ошибка'), langs('При сохранении произошли ошибки [Тип ошибки: 3]'));
						}
					}
				}
				else {
					sw.swMsg.alert(langs('Ошибка'), langs('При сохранении произошли ошибки [Тип ошибки: 2]'));
				}
			}
		});
	},
	draggable: true,
	height: 490,
	id: 'EvnUslugaDispDop13EditWindow',
	listeners: {
		'hide': function() {
			this.onHide();
		},
		'maximize': function() {
			this.doLayout();
		}
	},
	openEvnDirectionEditWindow: function()
	{
		var win = this;
		var params = {};
		var personinfoframe = win.findById('EUDD13EW_PersonInformationFrame');
		var base_form = win.findById('EUDD13EW_EvnUslugaDispDopEditForm').getForm();
		var action = 'add';
		
		// если не сохранено, то нужно сначала сохранить
		if (Ext.isEmpty(base_form.findField('EvnUslugaDispDop_id').getValue())) {
			this.doSave(function() {
				win.openEvnDirectionEditWindow();
			}, true);
			return false;
		}
		
		params.EvnDirection_id = base_form.findField('EvnDirection_id').getValue();
		if (!Ext.isEmpty(params.EvnDirection_id)) {
			if (win.action == 'view') {
				action = 'view';
			} else {
				action = 'edit';
			}
		}
		params.EvnDirection_pid = base_form.findField('EvnUslugaDispDop_id').getValue();
		params.Person_id = personinfoframe.getFieldValue('Person_id');
		params.PersonEvn_id = base_form.findField('PersonEvn_id').getValue();
		params.Server_id = base_form.findField('Server_id').getValue();
		
		getWnd('swEvnDirectionEditWindow').show({
			action: action,
			callback: function(data) {
				if ( !data || !data.evnDirectionData ) {
					return false;
				}

				base_form.findField('EvnDirection_id').setValue(data.evnDirectionData.EvnDirection_id);
			}.createDelegate(this),
			formParams: params,
			EvnDirection_id: params.EvnDirection_id,
			Person_id: personinfoframe.getFieldValue('Person_id'),
			Person_Birthday: personinfoframe.getFieldValue('Person_Birthday'),
			Person_Firname: personinfoframe.getFieldValue('Person_Firname'),
			Person_Secname: personinfoframe.getFieldValue('Person_Secname'),
			Person_Surname: personinfoframe.getFieldValue('Person_Surname')
		});
	},
	deleteEvnDiagDopDisp: function() {
		var win = this;

		if (win.action == 'view') {
			return false;
		}

		var grid = this.EvnDiagDopDispGrid.getGrid();

		sw.swMsg.show({
			buttons: sw.swMsg.YESNO,
			fn: function(buttonId, text, obj)
			{
				if ('yes' == buttonId)
				{
					if (!grid.getSelectionModel().getSelected())
					{
						return false;
					}

					var selected_record = grid.getSelectionModel().getSelected();
					var EvnDiagDopDisp_id = selected_record.get('EvnDiagDopDisp_id');

					if (selected_record.data.Record_Status == 0)
					{
						grid.getStore().remove(selected_record);
					}
					else
					{
						selected_record.set('Record_Status', 3);
						selected_record.commit();
						grid.getStore().filterBy(function(record)
						{
							if (record.data.Record_Status != 3)
							{
								return true;
							}
						});
					}

					if (grid.getStore().getCount() == 0)
					{
						grid.getTopToolbar().items.items[1].disable();
						grid.getTopToolbar().items.items[2].disable();
						grid.getTopToolbar().items.items[3].disable();
					}
					else
					{
						grid.getView().focusRow(0);
						grid.getSelectionModel().selectFirstRow();
					}
				}
			}.createDelegate(this),
			icon: Ext.MessageBox.QUESTION,
			msg: langs('Удалить сопутствующий диагноз?'),
			title: langs('Вопрос')
		});
	},
	onCytoAgreeChange: function() {
		var win = this;
		var base_form = this.findById('EUDD13EW_EvnUslugaDispDopEditForm').getForm();
		if (base_form.findField('Cyto_IsNotAgree').checked) {
			base_form.findField('CytoEvnUsluga_setDate').setValue(null);
			base_form.findField('CytoExaminationPlace_id').clearValue();
			base_form.findField('CytoExaminationPlace_id').fireEvent('change', base_form.findField('CytoExaminationPlace_id'), base_form.findField('CytoExaminationPlace_id').getValue());
			base_form.findField('CytoLpuSection_id').clearValue();
			base_form.findField('CytoMedStaffFact_id').clearValue();
			base_form.findField('CytoEvnUsluga_setDate').disable();
			base_form.findField('CytoExaminationPlace_id').disable();
			base_form.findField('CytoLpuSection_id').disable();
			base_form.findField('CytoMedStaffFact_id').disable();
		} else {
			base_form.findField('CytoEvnUsluga_setDate').enable();
			base_form.findField('CytoExaminationPlace_id').enable();
			base_form.findField('CytoLpuSection_id').enable();
			base_form.findField('CytoMedStaffFact_id').enable();
		}
	},
	openEvnDiagDopDispEditWindow: function(action) {
		var win = this;

		if (win.action == 'view') {
			if (action == 'add') {
				return false;
			}
			action = 'view';
		}

		var base_form = this.findById('EUDD13EW_EvnUslugaDispDopEditForm').getForm();
		var grid = this.EvnDiagDopDispGrid.getGrid();

		var params = new Object();
		params.action = action;
		params.formParams = new Object();
		params.formParams.EvnDiagDopDisp_pid = base_form.findField('EvnUslugaDispDop_id').getValue();
		params.EvnDiagDopDispGridStore = grid.getStore();
		
		if (action == 'add') {
			params.formParams.EvnDiagDopDisp_id = swGenTempId(grid.getStore(), 'EvnDiagDopDisp_id');
			params.formParams.Record_Status = 0;
		} else {
			var selected_record = grid.getSelectionModel().getSelected();
			if (!selected_record.get('EvnDiagDopDisp_id')) { return false; }
			params.formParams = selected_record.data;
		}
		
		params.formParams.EvnPLDisp_id = base_form.findField('EvnVizitDispDop_pid').getValue();

		params.formMode = 'local';

		params.callback = function(data) {
			var i;
			var evndiag_fields = new Array();

			grid.getStore().fields.eachKey(function(key, item) {
				evndiag_fields.push(key);
			});

			if ( action == 'add' )
			{
				grid.getStore().clearFilter();
				grid.getStore().loadData(data, true);
				grid.getStore().filterBy(function(record) {
					if (record.data.Record_Status != 3)
					{
						return true;
					}
				});
			}
			else {
				index = grid.getStore().findBy(function(rec) { return rec.get('EvnDiagDopDisp_id') == data[0].EvnDiagDopDisp_id; });

				if (index == -1)
				{
					return false;
				}

				var record = grid.getStore().getAt(index);
				for (i = 0; i < evndiag_fields.length; i++)
				{
					record.set(evndiag_fields[i], data[0][evndiag_fields[i]]);
				}

				record.commit();
			}

			return true;
		};

		params.soputDiagsFirst = win.soputDiagsFirst;
		params.formParams.parentFormName = win.object;
		getWnd('swEvnDiagDopDispEditWindow').show(params);
	},
	setPrintItemsDisabled: function() {
		var win = this;
		var base_form = this.findById('EUDD13EW_EvnUslugaDispDopEditForm').getForm();
		switch ( this.SurveyType_Code ) {
			case 150:
				Ext.getCmp('EUDD13EW_PrintButton').menu.items.items[0].setDisabled(base_form.findField('migrant_tub').getValue() != 2);
				Ext.getCmp('EUDD13EW_PrintButton').menu.items.items[1].setDisabled(base_form.findField('migrant_tub').getValue() != 2);
				Ext.getCmp('EUDD13EW_PrintButton').menu.items.items[3].setDisabled(base_form.findField('migrant_tub').getValue() != 2);
				Ext.getCmp('EUDD13EW_PrintButton').menu.items.items[4].setDisabled(base_form.findField('migrant_tub').getValue() != 2);
				Ext.getCmp('EUDD13EW_PrintButton').menu.items.items[5].setDisabled(base_form.findField('migrant_tub').getValue() != 2);
				break;

			case 152:
				Ext.getCmp('EUDD13EW_PrintButton').menu.items.items[0].setDisabled(base_form.findField('migrant_HIV').getValue() != 2 && base_form.findField('migrant_HIV_lepr').getValue() != 2);
				Ext.getCmp('EUDD13EW_PrintButton').menu.items.items[1].setDisabled(base_form.findField('migrant_HIV').getValue() != 2 && base_form.findField('migrant_HIV_lepr').getValue() != 2);
				Ext.getCmp('EUDD13EW_PrintButton').menu.items.items[2].setDisabled(base_form.findField('migrant_HIV').getValue() != 2);
				Ext.getCmp('EUDD13EW_PrintButton').menu.items.items[3].setDisabled(base_form.findField('migrant_HIV').getValue() != 2 && base_form.findField('migrant_HIV_lepr').getValue() != 2);
				Ext.getCmp('EUDD13EW_PrintButton').menu.items.items[4].setDisabled(base_form.findField('migrant_HIV').getValue() != 2 && base_form.findField('migrant_HIV_lepr').getValue() != 2);
				break;

			case 154:
				Ext.getCmp('EUDD13EW_PrintButton').menu.items.items[0].setDisabled(base_form.findField('migrant_syphilis').getValue() != 2);
				Ext.getCmp('EUDD13EW_PrintButton').menu.items.items[1].setDisabled(base_form.findField('migrant_syphilis').getValue() != 2);
				Ext.getCmp('EUDD13EW_PrintButton').menu.items.items[3].setDisabled(base_form.findField('migrant_syphilis').getValue() != 2);
				Ext.getCmp('EUDD13EW_PrintButton').menu.items.items[4].setDisabled(base_form.findField('migrant_syphilis').getValue() != 2);
				break;
		}
	},
	addDirectionIssled: function() {
		var win = this;
		var base_form = win.findById('EUDD13EW_EvnUslugaDispDopEditForm').getForm();

		// если не сохранено, то нужно сначала сохранить
		if (Ext.isEmpty(base_form.findField('EvnUslugaDispDop_id').getValue())) {
			this.doSave(function() {
				win.addDirectionIssled();
			}, true);
			return false;
		}

		var EvnDirection_pid = base_form.findField('EvnUslugaDispDop_id').getValue();

		if (this.DispClass_id == 26 &&
			!Ext.isEmpty(base_form.findField('EvnVizitDispDop_pid').getValue())) {
			EvnDirection_pid = base_form.findField('EvnVizitDispDop_pid').getValue();
		}

		var personinfoframe = win.findById('EUDD13EW_PersonInformationFrame');
		getWnd('swDirectionMasterWindow').show({
			userMedStaffFact: sw.Promed.MedStaffFactByUser.last,
			personData: {
				Person_id: personinfoframe.getFieldValue('Person_id'),
				Server_id: personinfoframe.getFieldValue('Server_id'),
				PersonEvn_id: personinfoframe.getFieldValue('PersonEvn_id'),
				Person_Firname: personinfoframe.getFieldValue('Person_Firname'),
				Person_Secname: personinfoframe.getFieldValue('Person_Secname'),
				Person_Surname: personinfoframe.getFieldValue('Person_Surname'),
				Person_Birthday: personinfoframe.getFieldValue('Person_Birthday')
			},
			dirTypeData: {
				DirType_id: 10,
				DirType_Code: 9,
				DirType_Name: 'На исследование'
			},
			dirTypeCodeIncList: ['9'],
			directionData: {
				EvnDirection_pid: EvnDirection_pid
				,DirType_id: 10
				,Lpu_sid: getGlobalOptions().lpu_id
				,DopDispInfoConsent_id: (!Ext.isEmpty(base_form.findField('DopDispInfoConsent_id').getValue()) ? base_form.findField('DopDispInfoConsent_id').getValue() : null )
			},
			onHide: function () {
				win.EvnDirectionGrid.getGrid().getStore().reload();
			}
		});
	},
	addDirectionConsult: function() {
		var win = this;
		var base_form = win.findById('EUDD13EW_EvnUslugaDispDopEditForm').getForm();

		// если не сохранено, то нужно сначала сохранить
		if (Ext.isEmpty(base_form.findField('EvnUslugaDispDop_id').getValue())) {
			this.doSave(function() {
				win.addDirectionConsult();
			}, true);
			return false;
		}

		var EvnDirection_pid = base_form.findField('EvnUslugaDispDop_id').getValue();

		if (this.DispClass_id == 26 &&
			!Ext.isEmpty(base_form.findField('EvnVizitDispDop_pid').getValue())) {
			EvnDirection_pid = base_form.findField('EvnVizitDispDop_pid').getValue();
		}

		var personinfoframe = win.findById('EUDD13EW_PersonInformationFrame');
		getWnd('swDirectionMasterWindow').show({
			userMedStaffFact: sw.Promed.MedStaffFactByUser.last,
			personData: {
				Person_id: personinfoframe.getFieldValue('Person_id'),
				Server_id: personinfoframe.getFieldValue('Server_id'),
				PersonEvn_id: personinfoframe.getFieldValue('PersonEvn_id'),
				Person_Firname: personinfoframe.getFieldValue('Person_Firname'),
				Person_Secname: personinfoframe.getFieldValue('Person_Secname'),
				Person_Surname: personinfoframe.getFieldValue('Person_Surname'),
				Person_Birthday: personinfoframe.getFieldValue('Person_Birthday')
			},
			dirTypeData: {
				DirType_id: 3,
				DirType_Code: 3,
				DirType_Name: 'На консультацию'
			},
			dirTypeCodeExcList: ['1','4','5','6','7','8','9','10','11','13','14','15','16','17','18'],
			directionData: {
				EvnDirection_pid: EvnDirection_pid
				,DirType_id: 3
				,Lpu_sid: getGlobalOptions().lpu_id
				,DopDispInfoConsent_id: (!Ext.isEmpty(base_form.findField('DopDispInfoConsent_id').getValue()) ? base_form.findField('DopDispInfoConsent_id').getValue() : null )
			},
			onHide: function () {
				win.EvnDirectionGrid.getGrid().getStore().reload();
			}
		});
	},
	addDirectionPolka: function() {
		var win = this;
		var base_form = win.findById('EUDD13EW_EvnUslugaDispDopEditForm').getForm();

		// если не сохранено, то нужно сначала сохранить
		if (Ext.isEmpty(base_form.findField('EvnUslugaDispDop_id').getValue())) {
			this.doSave(function() {
				win.addDirectionPolka();
			}, true);
			return false;
		}

		var EvnDirection_pid = base_form.findField('EvnUslugaDispDop_id').getValue();

		if (this.DispClass_id == 26 &&
			!Ext.isEmpty(base_form.findField('EvnVizitDispDop_pid').getValue())) {
			EvnDirection_pid = base_form.findField('EvnVizitDispDop_pid').getValue();
		}

		var personinfoframe = win.findById('EUDD13EW_PersonInformationFrame');
		getWnd('swDirectionMasterWindow').show({
			userMedStaffFact: sw.Promed.MedStaffFactByUser.last,
			personData: {
				Person_id: personinfoframe.getFieldValue('Person_id'),
				Server_id: personinfoframe.getFieldValue('Server_id'),
				PersonEvn_id: personinfoframe.getFieldValue('PersonEvn_id'),
				Person_Firname: personinfoframe.getFieldValue('Person_Firname'),
				Person_Secname: personinfoframe.getFieldValue('Person_Secname'),
				Person_Surname: personinfoframe.getFieldValue('Person_Surname'),
				Person_Birthday: personinfoframe.getFieldValue('Person_Birthday')
			},
			dirTypeData: {
				DirType_id: 16,
				DirType_Code: 12,
				DirType_Name: 'На поликлинический прием'
			},
			dirTypeCodeExcList: ['1','4','5','6','7','8','9','10','11','13','14','15','16','17','18'],
			directionData: {
				EvnDirection_pid: EvnDirection_pid
				,DirType_id: 16
				,Lpu_sid: getGlobalOptions().lpu_id
				,DopDispInfoConsent_id: (!Ext.isEmpty(base_form.findField('DopDispInfoConsent_id').getValue()) ? base_form.findField('DopDispInfoConsent_id').getValue() : null )
			},
			onHide: function () {
				win.EvnDirectionGrid.getGrid().getStore().reload();
			}
		});
	},
	loadSpecificsTree: function() {
		if(!this.SurveyType_Code.inlist([19]))
			return;
		var tree = this.findById('EEPLEF_SpecificsTree');
		var root = tree.getRootNode();
		var win = this;

		if (win.specLoading) {
			clearTimeout(win.specLoading);
		}

		win.specLoading = setTimeout(function() {

			//var base_form = this.FormPanel.getForm();
			var base_form = win.findById('EUDD13EW_EvnUslugaDispDopEditForm').getForm();
			var personinfoframe = win.findById('EUDD13EW_PersonInformationFrame');
			var Diag_ids = [];
			if (base_form.findField('Diag_id').getValue() && base_form.findField('Diag_id').getFieldValue('Diag_Code')) {
				Diag_ids.push([base_form.findField('Diag_id').getValue(), 1, base_form.findField('Diag_id').getFieldValue('Diag_Code'), '']);
			}
			win.EvnDiagDopDispGrid.ViewGridStore.each(function(record) {
				if(record.get('Diag_id')) {
					Diag_ids.push([record.get('Diag_id'), 0, record.get('Diag_Code'), null]); // record.get('EvnDiagPL_id').toString() - было последним параметром (скопировано с другого кода), хз нужно ли в данном контексте
				}
			});
			tree.getLoader().baseParams.Diag_ids = Ext.util.JSON.encode(Diag_ids);
			tree.getLoader().baseParams.Person_id = personinfoframe.getFieldValue('Person_id');
			tree.getLoader().baseParams.EvnVizit_id = base_form.findField('EvnVizitDispDop_id').getValue();
			tree.getLoader().baseParams.allowCreateButton = (win.action != 'view');
			tree.getLoader().baseParams.allowDeleteButton = (win.action != 'view');

			if (!root.expanded) {
				root.expand();
			} else {
				var spLoadMask = new Ext.LoadMask(this.getEl(), { msg: "Загрузка специфик..." });
				spLoadMask.show();
				tree.getLoader().load(root, function() {
					spLoadMask.hide();
				});
			}
		}.createDelegate(this), 1000);
	},
	setVisibleSpecPanel: function (diag_code) {
		var visible = (!Ext.isEmpty(diag_code) && (diag_code.search(new RegExp("^(C|D0)", "i")) != -1)
			&& this.SurveyType_Code && this.SurveyType_Code.inlist([19]));
		var spec_panel = this.findById('EUDD13EW_SpecificsPanel');
		spec_panel.setVisible(visible);
		if(visible)
			this.loadSpecificsTree();
	},
	openSpecific: function(node,forceOpen){
		var win = this;
		var base_form = win.findById('EUDD13EW_EvnUslugaDispDopEditForm').getForm();
		var personinfoframe = win.findById('EUDD13EW_PersonInformationFrame');

		var params = {};
		params.onHide = function(isChange) {
			win.loadSpecificsTree();
		};
		if(node && typeof node === 'object'){
			params.EvnVizitDispDop_id = node.attributes.value;
			params.EvnDiagPLSop_id = node.attributes.EvnDiagPLSop_id;
			params.Morbus_id = node.attributes.Morbus_id;
		} else {
			params.EvnVizitDispDop_id = base_form.findField('EvnVizitDispDop_id').getValue();
			params.Morbus_id = null;
			params.EvnDiagPLSop_id = '';
		}

		params.MorbusOnko_pid = base_form.findField('EvnVizitDispDop_id').getValue();
		params.Person_id = personinfoframe.getFieldValue('Person_id');
		params.PersonEvn_id = base_form.findField('PersonEvn_id').getValue();
		params.Server_id = base_form.findField('Server_id').getValue();
		params.allowSpecificEdit = true;
		params.action = (this.action != 'view') ? 'edit' : 'view';
		// всегда пересохраняем, чтобы в специфику ушли актуальные данные
		if (!forceOpen && (base_form.findField('EvnVizitDispDop_id').getValue() == 0 || this.diagIsChanged)) {
			this.doSave(function() {
				params.EvnVizitDispDop_id = base_form.findField('EvnVizitDispDop_id').getValue();
				params.MorbusOnko_pid = base_form.findField('EvnVizitDispDop_id').getValue();
				getWnd('swMorbusOnkoWindow').show(params);
			}, true);
		} else {
			params.EvnVizitDispDop_id = base_form.findField('EvnVizitDispDop_id').getValue();
			params.MorbusOnko_pid = base_form.findField('EvnVizitDispDop_id').getValue();
			getWnd('swMorbusOnkoWindow').show(params);
		}
	},
	initComponent: function() {
		var win = this;

		this.EvnDiagDopDispGrid = new sw.Promed.ViewFrame({
			useEmptyRecord: false,
			autoLoadData: false,
			uniqueId: true,
			collapsible:false,
			editformclassname: 'swEvnDiagDopDispEditForm',
			object: 'EvnDiagDopDisp',
			actions: [
				{ name: 'action_add', handler: function() { win.openEvnDiagDopDispEditWindow('add'); } },
				{ name: 'action_edit', handler: function() { win.openEvnDiagDopDispEditWindow('edit'); } },
				{ name: 'action_view', handler: function() { win.openEvnDiagDopDispEditWindow('view'); } },
				{ name: 'action_delete', handler: function() { win.deleteEvnDiagDopDisp(); } },
				{ name: 'action_refresh', hidden: true, disabled: true },
				{ name: 'action_print'}
			],
			dataUrl: '/?c=EvnDiagDopDisp&m=loadEvnDiagDopDispSoputGrid',
			region: 'center',
			toolbar: true,
			height: 150,
			stringfields: [
				{ name: 'EvnDiagDopDisp_id', type: 'int', header: 'ID', key: true },
				{ name: 'Diag_Code', type: 'string', header: langs('Код')},
				{ name: 'Diag_id', type: 'int', hidden: true},
				{ name: 'DeseaseDispType_id', type: 'int', hidden: true},
				{ name: 'Record_Status', type: 'int', hidden: true},
				{ name: 'Diag_Name', type: 'string', header: langs('Наименование'), id: 'autoexpand' },
				{ name: 'DeseaseDispType_Name', type: 'string', header: langs('Характер заболевания')}
			]
		});
		
		
		this.EvnDirectionGrid = new sw.Promed.ViewFrame({
			id: 'EUDD13EW_EvnDirectionGrid',
			object: 'EvnDirection',
			dataUrl: '/?c=EvnDirection&m=loadEvnDirectionGrid',
			layout: 'fit',
			region: 'center',
			paging: false,
			root: 'data',
			totalProperty: 'totalCount',
			toolbar: true,
			autoExpandMin: 100,
			autoLoadData: false,
			stringfields: [
				{name: 'EvnDirection_id', type: 'int', header: 'ID', key: true},
				{name: 'EvnDirection_pid', type: 'int', hidden: true},
				{name: 'Person_id', type: 'int', hidden: true},
				{name: 'Server_id', type: 'int', hidden: true},
				{name: 'PersonEvn_id', type: 'int', hidden: true},
				{name: 'Diag_id', type: 'int', hidden: true},
				{name: 'DirType_id', type: 'int', hidden: true},
				{name: 'LpuSection_id', type: 'int', hidden: true},
				{name: 'MedPersonal_id', type: 'int', hidden: true},
				{name: 'MedPersonal_zid', type: 'int', hidden: true},
				{name: 'LpuSectionProfile_id', type: 'int', hidden: true},
				{name: 'EvnDirection_Descr', type: 'string', hidden: true},
				{name: 'TimetableGraf_id', type: 'string', hidden: true},
				{name: 'TimetableMedService_id', type: 'string', hidden: true},
				{name: 'TimetableResource_id', type: 'string', hidden: true},
				{name: 'EvnQueue_id', type: 'string', hidden: true},
				{name: 'EvnStatus_id', type: 'string', hidden: true},
				{name: 'EvnDirection_setDate', type: 'date', dateFormat: 'd.m.Y', header: langs('Дата выписки направления'), width: 100},
				{name: 'EvnDirection_Num', type: 'int', header: langs('Номер направления'), width: 120},
				{name: 'DirType_Name', type: 'string', header: langs('Тип направления'), width: 150},
				{name: 'LpuSectionProfile_Name', type: 'string', header: langs('Профиль'), autoexpand: true}
			],
			actions:[{
					name:'action_add',
					text: BTN_GRIDADD,
					tooltip: BTN_GRIDADD_TIP,
					id: 'EUDD13EW_EvnDirectionGrid_add',
					menu: [{
						text: 'На исследование',
						handler: function() {
							win.addDirectionIssled();
						}
					}, {
						text: 'На консультацию',
						handler: function() {
							win.addDirectionConsult();
						}
					}, {
						text: 'На поликлинический прием',
						handler: function() {
							win.addDirectionPolka();
						}
					}]
				},
				{name:'action_edit', hidden: true, disabled: true},
				{name:'action_view', handler: function(row, el, a) {
					var personinfoframe = win.findById('EUDD13EW_PersonInformationFrame'),
						grid = win.EvnDirectionGrid.getGrid(),
						rec = grid.getSelectionModel().getSelected();
					if (!rec || !rec.get('EvnDirection_id')) return false;
					var params = rec.data;
					getWnd('swEvnDirectionEditWindow').show({
						action: 'view',
						EvnDirection_id: rec.get('EvnDirection_id'),
						Person_id: personinfoframe.getFieldValue('Person_id'),
						formParams: params,
						Person_Birthday: personinfoframe.getFieldValue('Person_Birthday'),
						Person_Firname: personinfoframe.getFieldValue('Person_Firname'),
						Person_Secname: personinfoframe.getFieldValue('Person_Secname'),
						Person_Surname: personinfoframe.getFieldValue('Person_Surname')
					});
				}},
				{name:'action_delete', text: 'Отменить', tooltip: 'Отменить', handler: function() {
					var grid = win.EvnDirectionGrid.getGrid();
					rec = grid.getSelectionModel().getSelected();
					if (!rec || !rec.get('EvnDirection_id')) return false;
					sw.Promed.Direction.cancel({
						cancelType: 'cancel',
						ownerWindow: win,
						formType: 'reg',
						allowRedirect: true,
						userMedStaffFact: win.userMedStaffFact,
						EvnDirection_id: rec.get('EvnDirection_id')||null,
						DirType_Code: rec.get('DirType_id')||null,
						TimetableGraf_id: rec.get('TimetableGraf_id')||null,
						TimetableMedService_id: rec.get('TimetableMedService_id')||null,
						TimetableResource_id: rec.get('TimetableResource_id')||null,
						EvnQueue_id: rec.get('EvnQueue_id')||null,
						callback: function(cfg) {
							win.EvnDirectionGrid.getGrid().getStore().reload();
						}
					});
				}},
				{name:'action_refresh', hidden: true, disabled: true},
				{name:'action_print', handler: function() { 
					var grid = win.EvnDirectionGrid.getGrid();
					rec = grid.getSelectionModel().getSelected();
					if (!rec || !rec.get('EvnDirection_id')) return false;

					sw.Promed.Direction.print({
						EvnDirection_id: rec.get('EvnDirection_id')
					});
				}}
			],
			onRowSelect: function(sm,index,rec){
				this.getAction('action_delete').setDisabled(!rec || !rec.get('EvnDirection_id') || rec.get('EvnStatus_id').inlist([12,13,15]));
			}
		});
		
		this.FileUploadPanel = new sw.Promed.FileUploadPanel({
			id: 'EUDD13EW_FileUploadPanel',
			win: this,
			commentTextfieldWidth: 120,
			uploadFieldColumnWidth: .6,
			commentTextColumnWidth: .35,
			width: 600,
			buttonAlign: 'left',
			buttonLeftMargin: 100,
			labelWidth: 150,
			folder: 'evnmedia/',
			style: 'background: transparent',
			dataUrl: '/?c=EvnMediaFiles&m=loadEvnMediaFilesListGrid',
			saveUrl: '/?c=EvnMediaFiles&m=uploadFile',
			saveChangesUrl: '/?c=EvnMediaFiles&m=saveChanges',
			deleteUrl: '/?c=EvnMediaFiles&m=deleteFile'
		});

		this.FilePanel = new Ext.Panel({
			title: langs('Файлы'),
			id: 'EUDD13EW_FileTab',
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
			id: 'EUDD13EW_TemplPanel',
			layout: 'form',
			title: 'Протокол осмотра',
			ownerWin: this,
			options: {
				XmlType_id: sw.Promed.EvnXml.EVN_VIZIT_PROTOCOL_TYPE_ID,
				EvnClass_id: 14
			},
			onAfterLoadData: function(panel){
				var bf = this.findById('EUDD13EW_EvnUslugaDispDopEditForm').getForm();
				bf.findField('XmlTemplate_id').setValue(panel.getXmlTemplateId());
				panel.expand();
				this.syncSize();
				this.doLayout();
			}.createDelegate(this),
			onAfterClearViewForm: function(panel){
				var bf = this.findById('EUDD13EW_EvnUslugaDispDopEditForm').getForm();
				bf.findField('XmlTemplate_id').setValue(null);
			}.createDelegate(this),
			// определяем метод, который должен создать посещение перед созданием документа с помощью указанного метода
			onBeforeCreate: function (panel, method, params) {

				if (!panel || !method || typeof panel[method] != 'function') {return false; }

				var base_form = this.findById('EUDD13EW_EvnUslugaDispDopEditForm').getForm();
				var evn_id = base_form.findField('EvnVizitDispDop_id').getValue();

				if (evn_id && evn_id > 0) { panel[method](params); }
				else {

					var dontHide = true;
					this.doSave(function() {

						var created_evn_id = base_form.findField('EvnVizitDispDop_id').getValue();

						if (created_evn_id) {
							panel.setBaseParams({
								userMedStaffFact: sw.Promed.MedStaffFactByUser.last,
								UslugaComplex_id: base_form.findField('UslugaComplex_id').getValue(),
								Server_id: base_form.findField('Server_id').getValue(),
								Evn_id: created_evn_id
							});
							panel[method](params);
						} else {
							log('Невозможно определить визит услуги')
							return false;
						};
					}, dontHide);
				}
				return true;
			}.createDelegate(this)
		});

		Ext.apply(this, {
			buttons: [{
				handler: function() {
					win.doSave();
				},
				iconCls: 'save16',
				id: 'EUDD13EW_SaveButton',
				tabIndex: TABINDEX_EUDD13EW + 80,
				text: BTN_FRMSAVE
			}, {
				iconCls: 'print16',
				id: 'EUDD13EW_PrintButton',
				tabIndex: TABINDEX_EUDD13EW + 80,
				text: BTN_FRMPRINT,
				menu: [
					new Ext.Action({
						text: 'Уведомление об ответственности',
						name: 'notification_plague',
						handler: function() {
							var base_form = win.findById('EUDD13EW_EvnUslugaDispDopEditForm').getForm();

							win.doSave(function() {
								printBirt({
									'Report_FileName': 'Notification_plague.rptdesign',
									'Report_Params': 'paramEvnUslugaDispDop=' + base_form.findField('EvnUslugaDispDop_id').getValue(),
									'Report_Format': 'pdf'
								});
							}, true);
						}
					}),
					new Ext.Action({
						text: 'Уведомление в Роспотребнадзор о факте сокрытия контактных лиц',
						name: 'notification_plaguehiding',
						handler: function() {
							var base_form = win.findById('EUDD13EW_EvnUslugaDispDopEditForm').getForm(),
								personinfoframe = win.findById('EUDD13EW_PersonInformationFrame'),
								Person_id = personinfoframe.getFieldValue('Person_id'),
								Lpu_id = base_form.findField('Lpu_uid').getValue() || getGlobalOptions().lpu_id;
							printBirt({
								'Report_FileName': 'Notification_PlagueHiding.rptdesign',
								'Report_Params': 'paramPerson=' + Person_id + '&paramLpu=' + Lpu_id,
								'Report_Format': 'pdf'
							});
						}
					}),
					new Ext.Action({
						text: 'Уведомление о наличии родственников в РФ',
						name: 'notification_relatives',
						handler: function() {
							var personinfoframe = win.findById('EUDD13EW_PersonInformationFrame'),
								Person_id = personinfoframe.getFieldValue('Person_id');
							printBirt({
								'Report_FileName': 'Notification_Relatives.rptdesign',
								'Report_Params': 'paramPerson=' + Person_id,
								'Report_Format': 'pdf'
							});
						}
					}),
					new Ext.Action({
						text: 'Уведомление о выявленных в ходе медицинского освидетельствования инфекционных заболеваниях, опасных для населения, и направлении его на дообследование',
						name: 'notification_infection',
						handler: function() {
							var base_form = win.findById('EUDD13EW_EvnUslugaDispDopEditForm').getForm();

							win.doSave(function() {
								printBirt({
									'Report_FileName': 'Migrant_AddSurvey_Direction.rptdesign',
									'Report_Params': 'paramEvnUslugaDispDop=' + base_form.findField('EvnUslugaDispDop_id').getValue(),
									'Report_Format': 'pdf'
								});
							}, true);
						}
					}),
					new Ext.Action({
						text: 'Экстренное извещение об инфекционном заболевании, пищевом, остром, профессиональном отравлении, необычной реакции на прививку (ф. 058/у)',
						name: 'notification_infection_extra',
						handler: function() {
							win.doSave(function() {
								var
									base_form = win.findById('EUDD13EW_EvnUslugaDispDopEditForm').getForm(),
									EvnUslugaDispDop_id = base_form.findField('EvnUslugaDispDop_id').getValue(),
									Lpu_id = base_form.findField('Lpu_uid').getValue() || getGlobalOptions().lpu_id;

								printBirt({
									'Report_FileName': 'f058u_mgr.rptdesign',
									'Report_Params': 'EvnUslugaDispDop=' + EvnUslugaDispDop_id + '&paramLpu=' + Lpu_id,
									'Report_Format': 'pdf'
								});
							}, true);
						}
					}),
					new Ext.Action({
						text: 'Извещение о больном туберкулезом ф. 089/у-туб',
						name: 'notification_infection',
						handler: function() {
							var base_form = win.findById('EUDD13EW_EvnUslugaDispDopEditForm').getForm();
							var EvnPLDispMigrant_id = base_form.findField('EvnVizitDispDop_pid').getValue();
							printBirt({
								'Report_FileName': 'EvnPLDispMigrantTub.rptdesign',
								'Report_Params': '&paramEvnPLDispMigrant=' + EvnPLDispMigrant_id,
								'Report_Format': 'pdf'
							});
						}
					})
				]
			},
			'-',
			HelpButton(win, TABINDEX_EUDD13EW + 81),
			{
				handler: function() {
					win.hide();
				},
				iconCls: 'cancel16',
				id: 'EUDD13EW_CancelButton',
				onTabAction: function() {
					Ext.getCmp('EUDD13EW_EvnUslugaDispDop_setDate').focus(true, 200);
				},
				onShiftTabAction: function() {
					Ext.getCmp('EUDD13EW_SaveButton').focus(true, 200);
				},
				tabIndex: TABINDEX_EUDD13EW + 82,
				text: BTN_FRMCANCEL
			}],
			items: [
				new	sw.Promed.PersonInformationPanelShort({
					id: 'EUDD13EW_PersonInformationFrame',
					region: 'north'
				}),
				new Ext.Panel({
					region: 'center',
					autoScroll: true,
					bodyBorder: false,
					border: false,
					frame: false,
					items: [
						new Ext.form.FormPanel({
							bodyBorder: false,
							border: false,
							frame: false,
							id: 'EUDD13EW_EvnUslugaDispDopEditForm',
							labelAlign: 'right',
							labelWidth: 150,
							items: [
								{
									id: 'EUDD13EW_EvnUslugaDispDop_id',
									name: 'EvnUslugaDispDop_id',
									value: 0,
									xtype: 'hidden'
								}, {
									name: 'EvnVizitDispDop_id',
									xtype: 'hidden'
								}, {
									name: 'EvnVizitDispDop_pid',
									xtype: 'hidden'
								}, {
									name: 'DopDispInfoConsent_id',
									xtype: 'hidden'
								}, {
									name: 'EvnDirection_id',
									xtype: 'hidden'
								}, {
									name: 'PersonEvn_id',
									xtype: 'hidden'
								}, {
									name: 'MedPersonal_id',
									xtype: 'hidden'
								}, {
									name: 'CytoMedPersonal_id',
									xtype: 'hidden'
								}, {
									name: 'Server_id',
									xtype: 'hidden'
								}, {
									name: 'XmlTemplate_id',
									xtype: 'hidden'
								}, {
									title: 'Направление / назначение',
									bodyStyle: 'padding: 5px',
									id: 'EUDD13EW_DirectionPanel',
									layout: 'form',
									collapsible: true,
									xtype: 'panel',
									items: [{
										name: 'EvnDirection_Type',
										anchor: '100%',
										disabled: true,
										xtype: 'textfield',
										fieldLabel: 'Тип'
									}, {
										name: 'EvnDirection_insDate',
										disabled: true,
										xtype: 'textfield',
										fieldLabel: 'Дата создания'
									}, {
										name: 'EvnDirection_Num',
										anchor: '100%',
										disabled: true,
										xtype: 'textfield',
										fieldLabel: 'Номер направления'
									}, {
										name: 'EvnDirection_RecTo',
										anchor: '100%',
										disabled: true,
										xtype: 'textfield',
										fieldLabel: 'Место оказания'
									}, {
										name: 'EvnDirection_RecDate',
										anchor: '100%',
										disabled: true,
										xtype: 'textfield',
										fieldLabel: 'Запись'
									}]
								},
								{
									title: langs('Направление'),
									hidden: true, // убрали это, но пусть будет на всякий случай.
									bodyStyle: 'padding: 5px',
									layout: 'form',
									tbar: new sw.Promed.Toolbar({
										buttons: [{
											handler: function () {
												win.openEvnDirectionEditWindow();
											},
											iconCls: 'edit16',
											text: langs('Электронное направление'),
											tooltip: langs('Электронное направление')
										}]
									}),
									items: [{
										editable: false,
										enableKeyEvents: true,
										fieldLabel: langs('Место проведения'),
										maxLength: 150,
										name: 'EvnUslugaDispDop_ExamPlace',
										tabIndex: TABINDEX_EUDD13EW,
										width: 500,
										xtype: 'textfield'
									}, {
										border: false,
										layout: 'column',
										items: [{
											border: false,
											layout: 'form',
											items: [{
												allowBlank: true,
												enableKeyEvents: true,
												fieldLabel: langs('Дата'),
												format: 'd.m.Y',
												id: 'EUDD13EW_EvnUslugaDispDop_setDate',
												listeners: {
													'keydown': function (inp, e) {
														if (e.shiftKey && e.getKey() == Ext.EventObject.TAB) {
															e.stopEvent();
															Ext.getCmp('EUDD13EW_CancelButton').focus(true, 200);
														}
													},
													'change': function (field, newValue, oldValue) {
														if (blockedDateAfterPersonDeath('personpanelid', 'EUDD13EW_PersonInformationFrame', field, newValue, oldValue)) {
															return false;
														}
														/*
																									if ( !Ext.isEmpty(newValue) ) {
																										var base_form = this.findById('EUDD13EW_EvnUslugaDispDopEditForm').getForm();
																										base_form.findField('EvnUslugaDispDop_didDate').setValue(newValue);
																										base_form.findField('EvnUslugaDispDop_didDate').fireEvent('change', base_form.findField('EvnUslugaDispDop_didDate'), newValue);
																									}
														*/
													}.createDelegate(this)
												},
												name: 'EvnUslugaDispDop_setDate',
												//maxValue: Date.parseDate(getGlobalOptions().date, 'd.m.Y'),
												plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
												tabIndex: TABINDEX_EUDD13EW + 1,
												width: 100,
												xtype: 'swdatefield'
											}]
										}, {
											border: false,
											labelWidth: 80,
											layout: 'form',
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
												name: 'EvnUslugaDispDop_setTime',
												onTriggerClick: function () {
													var base_form = this.findById('EUDD13EW_EvnUslugaDispDopEditForm').getForm();
													var time_field = base_form.findField('EvnUslugaDispDop_setTime');

													if (time_field.disabled) {
														return false;
													}

													setCurrentDateTime({
														callback: function () {
															base_form.findField('EvnUslugaDispDop_setDate').fireEvent('change', base_form.findField('EvnUslugaDispDop_setDate'), base_form.findField('EvnUslugaDispDop_setDate').getValue());
														},
														dateField: base_form.findField('EvnUslugaDispDop_setDate'),
														loadMask: true,
														setDate: true,
														setDateMaxValue: false,
														setDateMinValue: false,
														setTime: true,
														timeField: time_field,
														windowId: 'EvnUslugaDispDop13EditWindow'
													});
												}.createDelegate(this),
												plugins: [new Ext.ux.InputTextMask('99:99', true)],
												tabIndex: TABINDEX_EUDD13EW + 2,
												validateOnBlur: false,
												width: 60,
												xtype: 'swtimefield'
											}]
										}]
									}]
								}, {
									title: langs('Выполнение'),
									bodyStyle: 'padding: 5px',
									layout: 'form',
									items: [
										{
											allowBlank: false,
											id: 'eudd13ewUslugaComplexCombo',
											fieldLabel: langs('Услуга'),
											hiddenName: 'UslugaComplex_id',
											listWidth: 590,
											showUslugaComplexEndDate: true,
											tabIndex: TABINDEX_EUDD13EW + 3,
											width: 400,
											nonDispOnly: false,
											xtype: 'swuslugacomplexnewcombo'
										},
										{
											border: false,
											layout: 'column',
											items: [{
												border: false,
												layout: 'form',
												labelWidth: 180,
												items: [{
													fieldLabel: langs('Дата начала выполнения'),
													format: 'd.m.Y',
													id: 'EUDD13EW_EvnUslugaDispDop_didDate',
													disabled: getRegionNick() == 'vologda',
													listeners: {
														'change': function (field, newValue, oldValue) {
															if (blockedDateAfterPersonDeath('personpanelid', 'EUDD13EW_PersonInformationFrame', field, newValue, oldValue)) {
																return false;
															}

															var base_form = this.findById('EUDD13EW_EvnUslugaDispDopEditForm').getForm();

															if (getRegionNick().inlist(['ufa', 'perm']) && !Ext.isEmpty(win.DispClass_id) && win.DispClass_id.inlist([1, 2]) && win.SurveyType_Code == 19) {
																// услуга терапевта зависи от даты выполнения, а не согласия
																win.UslugaComplex_Date = newValue;
																base_form.findField('UslugaComplex_id').getStore().baseParams.UslugaComplex_Date = (typeof win.UslugaComplex_Date == 'object' ? Ext.util.Format.date(win.UslugaComplex_Date, 'd.m.Y') : win.UslugaComplex_Date);
																win.loadUslugaComplexCombo();
															}

															base_form.findField('CytoEvnUsluga_setDate').setValue(newValue);
															base_form.findField('CytoEvnUsluga_setDate').fireEvent('change', base_form.findField('CytoEvnUsluga_setDate'), base_form.findField('CytoEvnUsluga_setDate').getValue());
															if (Ext.isEmpty(newValue)) {
																field.setAllowBlank(true);
																base_form.findField('ExaminationPlace_id').setAllowBlank(true);
																base_form.findField('Diag_id').setAllowBlank(true);
															}
															else {
																field.setAllowBlank(false);
																base_form.findField('ExaminationPlace_id').setAllowBlank(false);
																base_form.findField('Diag_id').setAllowBlank(false);

																if (Ext.isEmpty(oldValue) && Ext.isEmpty(base_form.findField('ExaminationPlace_id').getValue()) && Ext.isEmpty(base_form.findField('Diag_id').getValue())) {
																	base_form.findField('ExaminationPlace_id').setValue(1);
																	base_form.findField('ExaminationPlace_id').fireEvent('change', base_form.findField('ExaminationPlace_id'), base_form.findField('ExaminationPlace_id').getValue());

																	if (!this.type.inlist(['DispTeenInspectionPeriod', 'DispTeenInspectionProf', 'DispTeenInspectionPred'])) {
																		// диагноз по умолчанию: Z10.8
																		// для Карелии Z01.8 https://redmine.swan.perm.ru/issues/48530
																		// для Крыма Z00.0 https://redmine.swan.perm.ru/issues/115300
																		var defaultDiagCode = 'Z10.8';

																		switch (getRegionNick()) {
																			case 'kareliya':
																			case 'penza':
																				defaultDiagCode = 'Z01.8';
																				break;

																			case 'krym':
																				defaultDiagCode = 'Z00.0';
																				break;
																		}

																		base_form.findField('Diag_id').getStore().load({
																			params: {where: "where Diag_Code = '" + defaultDiagCode + "'"},
																			callback: function () {
																				var diag_id = base_form.findField('Diag_id').getStore().getAt(0).get('Diag_id');
																				base_form.findField('Diag_id').setValue(diag_id);
																				base_form.findField('Diag_id').fireEvent('select', base_form.findField('Diag_id'), base_form.findField('Diag_id').getStore().getAt(0), 0);
																				base_form.findField('Diag_id').onChange();
																			}
																		});
																	}
																}
															}
															base_form.findField('Diag_id').setFilterByDate(newValue);

															this.filterLpuCombo();
															this.setLpuSectionAndMedStaffFactFilter();
															this.filterProfile();
															this.filterMedSpec();
															this.setDisDT();
														}.createDelegate(this)
													},
													name: 'EvnUslugaDispDop_didDate',
													//maxValue: Date.parseDate(getGlobalOptions().date, 'd.m.Y'),
													plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
													tabIndex: TABINDEX_EUDD13EW + 4,
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
														'change': function () {
															this.setDisDT();
														}.createDelegate(this),
														'keydown': function (inp, e) {
															if (e.getKey() == Ext.EventObject.F4) {
																e.stopEvent();
																inp.onTriggerClick();
															}
														}
													},
													name: 'EvnUslugaDispDop_didTime',
													onTriggerClick: function () {
														var base_form = this.findById('EUDD13EW_EvnUslugaDispDopEditForm').getForm();

														var time_field = base_form.findField('EvnUslugaDispDop_didTime');

														if (time_field.disabled) {
															return false;
														}

														setCurrentDateTime({
															callback: function () {
																base_form.findField('EvnUslugaDispDop_didDate').fireEvent('change', base_form.findField('EvnUslugaDispDop_didDate'), base_form.findField('EvnUslugaDispDop_didDate').getValue());
															}.createDelegate(this),
															dateField: base_form.findField('EvnUslugaDispDop_didDate'),
															loadMask: true,
															setDate: true,
															//setDateMaxValue: true,
															setDateMinValue: false,
															setTime: true,
															timeField: time_field,
															windowId: this.id
														});
													}.createDelegate(this),
													plugins: [new Ext.ux.InputTextMask('99:99', true)],
													tabIndex: TABINDEX_EUDD13EW + 4,
													validateOnBlur: false,
													width: 60,
													xtype: 'swtimefield'
												}]
											}, {
												layout: 'form',
												style: 'padding-left: 45px',
												border: false,
												items: [{
													xtype: 'button',
													id: 'EUDD13EW_ToggleVisibleDisDTBtn',
													text: langs('Уточнить период выполнения'),
													handler: function () {
														this.toggleVisibleDisDTPanel();
													}.createDelegate(this)
												}]
											}]
										},
										{
											id: 'EUDD13EW_EvnUslugaDisDTPanel',
											border: false,
											layout: 'column',
											items: [{
												border: false,
												layout: 'form',
												labelWidth: 180,
												items: [{
													fieldLabel: langs('Дата окончания выполнения'),
													format: 'd.m.Y',
													name: 'EvnUslugaDispDop_disDate',
													//maxValue: Date.parseDate(getGlobalOptions().date, 'd.m.Y'),
													plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
													tabIndex: TABINDEX_EUDD13EW + 4,
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
													name: 'EvnUslugaDispDop_disTime',
													onTriggerClick: function () {
														var base_form = this.findById('EUDD13EW_EvnUslugaDispDopEditForm').getForm();

														var time_field = base_form.findField('EvnUslugaDispDop_disTime');

														if (time_field.disabled) {
															return false;
														}

														setCurrentDateTime({
															dateField: base_form.findField('EvnUslugaDispDop_disDate'),
															loadMask: true,
															setDate: true,
															//setDateMaxValue: true,
															setDateMinValue: false,
															setTime: true,
															timeField: time_field,
															windowId: this.id
														});
													}.createDelegate(this),
													plugins: [new Ext.ux.InputTextMask('99:99', true)],
													tabIndex: TABINDEX_EUDD13EW + 5,
													validateOnBlur: false,
													width: 60,
													xtype: 'swtimefield'
												}]
											}, {
												layout: 'form',
												border: false,
												items: [{
													xtype: 'button',
													id: 'EUDD13EW_DTCopyBtn',
													text: '=',
													handler: function () {
														var base_form = this.findById('EUDD13EW_EvnUslugaDispDopEditForm').getForm();

														base_form.findField('EvnUslugaDispDop_disDate').setValue(base_form.findField('EvnUslugaDispDop_didDate').getValue());
														base_form.findField('EvnUslugaDispDop_disTime').setValue(base_form.findField('EvnUslugaDispDop_didTime').getValue());
													}.createDelegate(this)
												}]
											}]
										},
										{
											allowBlank: false,
											enableKeyEvents: true,
											id: 'EUDD13EW_ExaminationPlaceCombo',
											listeners: {
												'change': function (field, newValue, oldValue) {
													var base_form = win.findById('EUDD13EW_EvnUslugaDispDopEditForm').getForm();
													win.setLpuSectionProfile();
													win.setMedSpecOms();
													win.setLpuSectionAndMedStaffFactFilter();
												}
											},
											name: 'ExaminationPlace_id',
											tabIndex: TABINDEX_EUDD13EW + 5,
											validateOnBlur: false,
											width: 350,
											xtype: 'swexaminationplacecombo',
											value: 1
										},
										{
											id: 'EUDD13EW_LpuCombo',
											comboSubject: 'Lpu',
											fieldLabel: langs('МО'),
											xtype: 'swcommonsprcombo',
											editable: true,
											forceSelection: true,
											displayField: 'Lpu_Nick',
											codeField: 'Lpu_Code',
											orderBy: 'Nick',
											tpl: new Ext.XTemplate(
												'<tpl for="."><div class="x-combo-list-item">',
												'{Lpu_Nick}',
												'<tpl if="!Ext.isEmpty(values.Lpu_EndDate)"> ({Lpu_BegDate} - {Lpu_EndDate})</tpl>',
												'</div></tpl>'
											),
											moreFields: [
												{name: 'Lpu_Nick', mapping: 'Lpu_Nick'},
												{name: 'Lpu_BegDate', mapping: 'Lpu_BegDate'},
												{name: 'Lpu_EndDate', mapping: 'Lpu_EndDate'}
											],
											tabIndex: TABINDEX_EUDD13EW + 6,
											width: 450,
											hiddenName: 'Lpu_uid',
											onLoadStore: function () {
												win.filterLpuCombo();
											},
											listeners: {
												'change': function (field, newValue, oldValue) {
													win.setLpuSectionAndMedStaffFactFilter();
												}
											}
										}, {
											fieldLabel: langs('Профиль'),
											listWidth: 600,
											xtype: 'swlpusectionprofileremotecombo',
											tabIndex: TABINDEX_EUDD13EW + 7,
											width: 450,
											hiddenName: 'LpuSectionProfile_id',
											listeners: {
												'change': function (field, newValue, oldValue) {
													var base_form = win.findById('EUDD13EW_EvnUslugaDispDopEditForm').getForm();
													if (base_form.findField('ExaminationPlace_id').getValue() == 3) {
														win.setLpuSectionAndMedStaffFactFilter();
													}

													if (getRegionNick() == 'perm') {
														win.filterMedSpec();
													}
												}
											}
										},
										{
											fieldLabel: langs('Специальность'),
											listWidth: 600,
											xtype: 'swmedspecomsremotecombo',
											tabIndex: TABINDEX_EUDD13EW + 8,
											width: 450,
											hiddenName: 'MedSpecOms_id',
											listeners: {
												'change': function (field, newValue, oldValue) {
													var base_form = win.findById('EUDD13EW_EvnUslugaDispDopEditForm').getForm();
													if (base_form.findField('ExaminationPlace_id').getValue() == 3) {
														win.setLpuSectionAndMedStaffFactFilter();
													}
												}
											}
										},
										{
											hiddenName: 'LpuSection_id',
											id: 'EUDD13EW_LpuSectionCombo',
											lastQuery: '',
											listWidth: 650,
											linkedElements: [
												'EUDD13EW_MedPersonalCombo'
											],
											listeners: {
												'select': function (combo, record, index) {
													combo.setValue(record.get('LpuSection_id'));
													combo.fireEvent('change', combo, combo.getValue());
												},
												'change': function (field, newValue, oldValue) {
													var base_form = win.findById('EUDD13EW_EvnUslugaDispDopEditForm').getForm();
													if (getRegionNick() == 'ufa' && !Ext.isEmpty(win.DispClass_id) && win.DispClass_id.inlist([1, 2, 10, 5])) {
														// услуга зависит от выбранного отделения
														base_form.findField('UslugaComplex_id').getStore().baseParams.LpuSection_id = base_form.findField('LpuSection_id').getValue();
														win.loadUslugaComplexCombo();
													}
												}
											},
											tabIndex: TABINDEX_EUDD13EW + 9,
											width: 450,
											xtype: 'swlpusectionglobalcombo',
											parentElementId: 'EUDD13EW_LpuCombo',
											allowBlank: !(getRegionNick() == 'buryatiya')
										},
										{
											hiddenName: 'MedStaffFact_id',
											id: 'EUDD13EW_MedPersonalCombo',
											lastQuery: '',
											listWidth: 650,
											parentElementId: 'EUDD13EW_LpuSectionCombo',
											listeners: {
												'select': function (combo, record, index) {
													combo.setValue(record.get('MedStaffFact_id'));
													combo.fireEvent('change', combo, combo.getValue());
												},
												'change': function (field, newValue, oldValue) {
													var base_form = win.findById('EUDD13EW_EvnUslugaDispDopEditForm').getForm();

													if (getRegionNick() == 'ufa' && !Ext.isEmpty(win.DispClass_id) && win.DispClass_id.inlist([1, 2, 10, 5])) {
														// услуга зависит от выбранного отделения
														base_form.findField('UslugaComplex_id').getStore().baseParams.LpuSection_id = base_form.findField('LpuSection_id').getValue();
														win.loadUslugaComplexCombo();
													}

													if (getRegionNick().inlist(['kareliya', 'penza']) && !Ext.isEmpty(newValue)) {
														var index = field.getStore().findBy(function (rec) {
															return (rec.get('MedStaffFact_id') == newValue);
														});

														if (index >= 0) {
															var
																MedSpecOms_id = field.getStore().getAt(index).get('MedSpecOms_id'),
																MedPersonal_Snils = field.getStore().getAt(index).get('Person_Snils');

															if (Ext.isEmpty(MedSpecOms_id)) {
																sw.swMsg.alert(langs('Сообщение'), langs('У врача не указана специальность'));
																return false;
															}
															else if (Ext.isEmpty(MedPersonal_Snils)) {
																sw.swMsg.alert(langs('Сообщение'), langs('У врача не указан СНИЛС'));
																return false;
															}
														}
													}
												}
											},
											tabIndex: TABINDEX_EUDD13EW + 10,
											width: 450,
											xtype: 'swmedstafffactglobalcombo',
											allowBlank: !(getRegionNick() == 'buryatiya')
										},
										{
											fieldLabel: langs('Диагноз'),
											id: 'EUDD13EW_Diag_id',
											hiddenName: 'Diag_id',
											onChange: function () {
												var diag_code = this.getFieldValue('Diag_Code');
												if (diag_code) {
													var base_form = win.findById('EUDD13EW_EvnUslugaDispDopEditForm').getForm();
													if (!Ext.isEmpty(diag_code) && diag_code.substr(0, 1).toUpperCase() == 'Z') {
														base_form.findField('DopDispDiagType_id').clearValue();
														base_form.findField('DopDispDiagType_id').disable();
														base_form.findField('DopDispDiagType_id').setAllowBlank(true);
													} else {
														if (win.action == 'view') {
															base_form.findField('DopDispDiagType_id').disable();
														}
														else {
															base_form.findField('DopDispDiagType_id').enable();
														}
														base_form.findField('DopDispDiagType_id').setAllowBlank(false);
														if (win.ShowDeseaseStageCombo) {
															//AllowEditDeseaseStageByDiag - true, если коды диагнозов от C00 до D48
															var AllowEditDeseaseStageByDiag = ((diag_code.substr(0, 1).toUpperCase() == 'C') || (diag_code.substr(0, 1).toUpperCase() == 'D' && diag_code.substr(1, 2) <= 48));
															if (win.AllowEditDeseaseStageByDate && AllowEditDeseaseStageByDiag) {
																base_form.findField('DeseaseStage').enable();
																base_form.findField('DeseaseStage').setAllowBlank(false);
															}
															else {
																base_form.findField('DeseaseStage').disable();
																base_form.findField('DeseaseStage').setAllowBlank(true);
															}
														}
													}
												}
												win.setVisibleSpecPanel(diag_code);
												win.UpdateTumorField();
												win.diagIsChanged = true;
											},
											listWidth: 600,
											tabIndex: TABINDEX_EUDD13EW + 11,
											width: 450,
											xtype: 'swdiagcombo'
										},
										{
											comboSubject: 'DopDispDiagType',
											fieldLabel: langs('Характер заболевания'),
											hiddenName: 'DopDispDiagType_id',
											value: 1,
											lastQuery: '',
											tabIndex: TABINDEX_EUDD13EW + 12,
											width: 450,
											xtype: 'swcommonsprcombo'
										},
										{
											xtype: 'swcommonsprcombo',
											comboSubject: 'TumorStage',
											hiddenName: 'TumorStage_id',
											fieldLabel: 'Стадия выявленного ЗНО',
											tabIndex: TABINDEX_EUPAREF + 82,
											width: 450
										},
										{
											allowBlank: true,
											codeField: 'DeseaseStage',
											disabled: true,
											hidden: true,
											displayField: 'DeseaseStage',
											editable: false,
											fieldLabel: langs('Стадия'),
											hiddenName: 'DeseaseStage',
											hideEmptyRow: true,
											ignoreIsEmpty: true,
											store: new Ext.data.SimpleStore({
												autoLoad: true,
												data: [
													[1],
													[2],
													[3],
													[4]
												],
												fields: [
													{name: 'DeseaseStage', type: 'int'}
												],
												key: 'DeseaseStage',
												sortInfo: {field: 'DeseaseStage'}
											}),
											tabIndex: TABINDEX_EUDD13EW + 12,
											tpl: new Ext.XTemplate(
												'<tpl for="."><div class="x-combo-list-item">',
												'<font color="blue">{DeseaseStage}</font>',
												'</div></tpl>'
											),
											valueField: 'DeseaseStage',
											width: 450,
											xtype: 'swbaselocalcombo'
										},
										{
											fieldLabel: 'Результат',
											tabIndex: TABINDEX_EUDD13EW + 12,
											width: 450,
											name: 'EvnUslugaDispDop_Result',
											xtype: 'textfield'
										},
										new sw.Promed.Panel({
											autoHeight: true,
											border: true,
											collapsible: true,
											id: 'EUDD13EW_SpecificsPanel',
											isLoaded: false,
											layout: 'form',
											style: 'margin-bottom: 0.5em;',
											title: 'Специфика',
											//header: false,
											items: [{
												border: false,
												height: 150,
												isLoaded: false,
												region: 'north',
												layout: 'border',
												items: [{
													autoScroll: true,
													border: false,
													collapsible: false,
													wantToFocus: false,
													id: 'EEPLEF_SpecificsTree',
													listeners: {
														'bodyresize': function (tree) {

														}.createDelegate(this),
														'beforeload': function (node) {

														}.createDelegate(this),
														'click': function (node, e) {
															this.openSpecific(node);
														}.createDelegate(this)
													},
													loader: new Ext.tree.TreeLoader({
														dataUrl: '/?c=Specifics&m=getStomSpecificsTree'
													}),
													region: 'west',
													root: {
														draggable: false,
														id: 'specifics_tree_root',
														nodeType: 'async',
														text: 'Специфика',
														value: 'root'
													},
													rootVisible: false,
													split: true,
													useArrows: true,
													width: 250,
													xtype: 'treepanel'
												},
													{
														border: false,
														layout: 'border',
														region: 'center',
														xtype: 'panel',
														items: [
															{
																autoHeight: true,
																border: false,
																labelWidth: 150,
																split: true,
																items: [],
																layout: 'form',
																region: 'north',
																xtype: 'panel'
															},
															{
																autoHeight: true,
																border: false,
																id: this.id + '_SpecificFormsPanel',
																items: [],
																layout: 'fit',
																region: 'center',
																xtype: 'panel'
															}
														]
													}]
											}]
										})
									]
								},
								{
									title: langs('Цитологическое исследование'),
									bodyStyle: 'padding: 5px',
									id: 'EUDD13EW_CytologPanel',
									layout: 'form',
									items: [{
										id: 'eudd13ewCytoUslugaComplexCombo',
										fieldLabel: langs('Услуга'),
										hiddenName: 'CytoUslugaComplex_id',
										disabled: true,
										listWidth: 590,
										showUslugaComplexEndDate: true,
										tabIndex: TABINDEX_EUDD13EW + 12,
										width: 400,
										nonDispOnly: false,
										xtype: 'swuslugacomplexnewcombo'
									}, {
										bodyStyle: 'padding-left: 155px; padding-bottom: 5px;',
										border: false,
										items: [{
											boxLabel: langs('Отказ / невозможно по показаниям'),
											hideLabel: true,
											listeners: {
												'check': function() {
													win.onCytoAgreeChange();
												}
											},
											name: 'Cyto_IsNotAgree',
											xtype: 'checkbox'
										}]
									}, {
										fieldLabel: langs('Дата'),
										format: 'd.m.Y',
										name: 'CytoEvnUsluga_setDate',
										//maxValue: Date.parseDate(getGlobalOptions().date, 'd.m.Y'),
										plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
										tabIndex: TABINDEX_EUDD13EW + 12,
										width: 100,
										xtype: 'swdatefield',
										listeners: {
											'change': function(field, newValue, oldValue) {
												var base_form = win.findById('EUDD13EW_EvnUslugaDispDopEditForm').getForm();

												win.filterLpuCombo('cyto');
												win.setCytoLpuSectionAndCytoMedStaffFactFilter();
												win.filterCytoProfile();
												win.filterCytoMedSpec();
											}
										}
									}, {
										allowBlank: false,
										enableKeyEvents: true,
										listeners: {
											'change': function(field, newValue, oldValue) {
												var base_form = win.findById('EUDD13EW_EvnUslugaDispDopEditForm').getForm();
												win.setCytoLpuSectionProfile();
												win.setCytoMedSpecOms();
												win.setCytoLpuSectionAndCytoMedStaffFactFilter();
											}
										},
										hiddenName: 'CytoExaminationPlace_id',
										tabIndex: TABINDEX_EUDD13EW + 12,
										validateOnBlur: false,
										width: 350,
										xtype: 'swexaminationplacecombo',
										value: 1
									}, {
										comboSubject: 'Lpu',
										fieldLabel: langs('МО'),
										xtype: 'swcommonsprcombo',
										editable: true,
										forceSelection: true,
										displayField: 'Lpu_Nick',
										codeField: 'Lpu_Code',
										orderBy: 'Nick',
										tpl: new Ext.XTemplate(
											'<tpl for="."><div class="x-combo-list-item">',
											'{Lpu_Nick}&nbsp;',
											'</div></tpl>'
										),
										moreFields: [
											{name: 'Lpu_Nick', mapping: 'Lpu_Nick'},
											{name: 'Lpu_EndDate', mapping: 'Lpu_EndDate'}
										],
										tabIndex: TABINDEX_EUDD13EW + 12,
										width: 350,
										hiddenName: 'CytoLpu_id',
										onLoadStore: function() {
											win.filterLpuCombo('cyto');
										},
										listeners: {
											'change': function(field, newValue, oldValue) {
												win.setCytoLpuSectionAndCytoMedStaffFactFilter();
											}
										}
									}, {
										fieldLabel: langs('Профиль'),
										listWidth: 600,
										xtype: 'swlpusectionprofileremotecombo',
										tabIndex: TABINDEX_EUDD13EW + 7,
										width: 350,
										hiddenName: 'CytoLpuSectionProfile_id',
										listeners: {
											'change': function(field, newValue, oldValue) {
												var base_form = win.findById('EUDD13EW_EvnUslugaDispDopEditForm').getForm();
												if (base_form.findField('CytoExaminationPlace_id').getValue() == 3) {
													win.setCytoLpuSectionAndCytoMedStaffFactFilter();
												}
												if (getRegionNick() == 'perm') {
													// win.filterCytoMedSpec(); // нужна ли для цито?
												}
											}
										}
									}, {
										hiddenName: 'CytoLpuSection_id',
										id: 'EUDD13EW_CytoLpuSectionCombo',
										lastQuery: '',
										listWidth: 650,
										linkedElements: [
											'EUDD13EW_CytoMedPersonalCombo'
										],
										tabIndex: TABINDEX_EUDD13EW + 12,
										width: 450,
										xtype: 'swlpusectionglobalcombo'
									}, {
										fieldLabel: langs('Специальность'),
										listWidth: 600,
										xtype: 'swmedspecomsremotecombo',
										tabIndex: TABINDEX_EUDD13EW + 8,
										width: 350,
										hiddenName: 'CytoMedSpecOms_id',
										listeners: {
											'change': function(field, newValue, oldValue) {
												var base_form = win.findById('EUDD13EW_EvnUslugaDispDopEditForm').getForm();
												if (base_form.findField('CytoExaminationPlace_id').getValue() == 3) {
													win.setCytoLpuSectionAndCytoMedStaffFactFilter();
												}
											}
										}
									}, {
										hiddenName: 'CytoMedStaffFact_id',
										id: 'EUDD13EW_CytoMedPersonalCombo',
										lastQuery: '',
										listWidth: 650,
										parentElementId: 'EUDD13EW_CytoLpuSectionCombo',
										tabIndex: TABINDEX_EUDD13EW + 12,
										width: 450,
										xtype: 'swmedstafffactglobalcombo'
									}]
								},
								win.EvnXmlPanel,
								{
									id: 'EUDD13EW_EvnDiagDopDispGridPanel',
									collapsible: true,
									xtype: 'panel',
									border: false,
									title: langs('Сопутствующие диагнозы'),
									items: [win.EvnDiagDopDispGrid]
								},
								{
									id: 'EUDD13EW_EvnDirectionGridPanel',
									collapsible: true,
									xtype: 'panel',
									border: false,
									title: langs('Направление на дообследование'),
								items: [win.EvnDirectionGrid,]
								},
								{
									title: langs('Результат'),
									bodyStyle: 'padding: 5px',
									defaults: {
										decimalPrecision: 2
									},
									labelWidth: 200,
									id: 'EUDD13EW_ResultsPanel',
									layout: 'form',
									items: [{
										allowDecimals: true,
										allowNegative: false,
										fieldLabel: langs('Систолическое АД (мм рт.ст.)'),
										name: 'systolic_blood_pressure',
										tabIndex: TABINDEX_EUDD13EW + 13,
										xtype: 'numberfield',
										enableKeyEvents: true
									}, {
										allowDecimals: true,
										allowNegative: false,
										fieldLabel: langs('Диастолическое АД (мм рт.ст.)'),
										name: 'diastolic_blood_pressure',
										tabIndex: TABINDEX_EUDD13EW + 14,
										xtype: 'numberfield',
										enableKeyEvents: true
									}, {
										allowDecimals: true,
										allowNegative: false,
										//decimalPrecision: 1,
										tabIndex: TABINDEX_EUDD13EW + 15,
										fieldLabel: langs('Вес (кг)'),
										maxValue: 500,
										name: 'person_weight',
										xtype: 'numberfield',
										enableKeyEvents: true
									}, {
										allowDecimals: true,
										allowNegative: false,
										//decimalPrecision: 1,
										fieldLabel: langs('Рост (см)'),
										maxValue: 275,
										name: 'person_height',
										tabIndex: TABINDEX_EUDD13EW + 16,
										xtype: 'numberfield',
										enableKeyEvents: true
									}, {
										allowDecimals: true,
										allowNegative: false,
										fieldLabel: langs('Окружность талии (см)'),
										name: 'waist_circumference',
										tabIndex: TABINDEX_EUDD13EW + 17,
										xtype: 'numberfield',
										enableKeyEvents: true
									}, {
										allowDecimals: true,
										allowNegative: false,
										//decimalPrecision: 1,
										fieldLabel: langs('Индекс массы тела (кг/м2)'),
										name: 'body_mass_index',
										readOnly: true,
										tabIndex: TABINDEX_EUDD13EW + 18,
										xtype: 'numberfield',
										enableKeyEvents: true
									}, {
										allowDecimals: true,
										allowNegative: false,
										//decimalPrecision: 1,
										fieldLabel: langs('Общий холестерин (ммоль/л)'),
										name: 'total_cholesterol',
										tabIndex: TABINDEX_EUDD13EW + 19,
										xtype: 'numberfield',
										enableKeyEvents: true
									}, {
										allowDecimals: true,
										allowNegative: false,
										//decimalPrecision: 1,
										fieldLabel: langs('Глюкоза (ммоль/л)'),
										name: 'glucose',
										tabIndex: TABINDEX_EUDD13EW + 20,
										xtype: 'numberfield',
										enableKeyEvents: true
									}, {
										fieldLabel: langs('Давление OD'),
										name: 'eye_pressure_right',
										width: 300,
										tabIndex: TABINDEX_EUDD13EW + 21,
										xtype: 'textfield',
										enableKeyEvents: true
									}, {
										fieldLabel: langs('Давление OS'),
										name: 'eye_pressure_left',
										tabIndex: TABINDEX_EUDD13EW + 22,
										width: 300,
										xtype: 'textfield',
										enableKeyEvents: true
									},/* {
									fieldLabel: langs('Норма / повышенное'),
									hiddenName: 'eye_pressure_increase',
									xtype: 'swyesnocombo'
								},*/ {
										allowDecimals: true,
										allowNegative: false,
										//decimalPrecision: 1,
										fieldLabel: langs('Число эритроцитов'),
										name: 'number_erythrocytes',
										tabIndex: TABINDEX_EUDD13EW + 23,
										xtype: 'numberfield',
										enableKeyEvents: true
									}, {
										allowDecimals: true,
										allowNegative: false,
										//decimalPrecision: 1,
										fieldLabel: langs('Гемоглобин (г/л)'),
										name: 'cln_blood_gem',
										tabIndex: TABINDEX_EUDD13EW + 24,
										xtype: 'numberfield',
										enableKeyEvents: true
									}, {
										allowDecimals: true,
										allowNegative: false,
										//decimalPrecision: 1,
										fieldLabel: langs('Гематокрит (%)'),
										name: 'hematocrit',
										tabIndex: TABINDEX_EUDD13EW + 25,
										xtype: 'numberfield',
										enableKeyEvents: true
									}, {
										allowDecimals: true,
										allowNegative: false,
										//decimalPrecision: 1,
										fieldLabel: langs('Ширина распределения эритроцитов (%)'),
										name: 'distribution_width_erythrocytes',
										tabIndex: TABINDEX_EUDD13EW + 26,
										xtype: 'numberfield',
										enableKeyEvents: true
									}, {
										allowDecimals: true,
										allowNegative: false,
										fieldLabel: langs('Средний объем эритроцита (фл)'),
										name: 'volume_erythrocyte',
										tabIndex: TABINDEX_EUDD13EW + 27,
										xtype: 'numberfield',
										enableKeyEvents: true
									}, {
										allowDecimals: true,
										allowNegative: false,
										fieldLabel: langs('Среднее содержание гемоглобина в эритроците (пг)'),
										name: 'hemoglobin_content',
										tabIndex: TABINDEX_EUDD13EW + 28,
										xtype: 'numberfield',
										enableKeyEvents: true
									}, {
										allowDecimals: true,
										allowNegative: false,
										fieldLabel: langs('Средняя концетрация гемоглобина в эритроците (г/л)'),
										name: 'concentration_hemoglobin',
										tabIndex: TABINDEX_EUDD13EW + 29,
										xtype: 'numberfield',
										enableKeyEvents: true
									}, {
										allowDecimals: true,
										allowNegative: false,
										//decimalPrecision: 1,
										fieldLabel: langs('Число тромбоцитов (х 10 в 9 степени/л)'),
										name: 'cln_blood_trom',
										tabIndex: TABINDEX_EUDD13EW + 30,
										xtype: 'numberfield',
										enableKeyEvents: true
									}, {
										allowDecimals: true,
										allowNegative: false,
										//decimalPrecision: 1,
										fieldLabel: langs('Число лейкоцитов (х 10 в 9 степени/л)'),
										name: 'cln_blood_leyck',
										tabIndex: TABINDEX_EUDD13EW + 31,
										xtype: 'numberfield',
										enableKeyEvents: true
									}, {
										allowDecimals: true,
										allowNegative: false,
										//decimalPrecision: 1,
										fieldLabel: langs('Содержание лимфоцитов (%)'),
										name: 'lymphocyte_content',
										tabIndex: TABINDEX_EUDD13EW + 32,
										xtype: 'numberfield',
										enableKeyEvents: true
									}, {
										allowDecimals: true,
										allowNegative: false,
										//decimalPrecision: 1,
										fieldLabel: langs('Содержание смеси моноцитов'),
										name: 'contents_mixture_monocit',
										tabIndex: TABINDEX_EUDD13EW + 33,
										xtype: 'numberfield',
										enableKeyEvents: true
									}, {
										allowDecimals: true,
										allowNegative: false,
										//decimalPrecision: 1,
										fieldLabel: langs('Содержание смеси эозинофилов'),
										name: 'contents_mixture_eozinofil',
										tabIndex: TABINDEX_EUDD13EW + 34,
										xtype: 'numberfield',
										enableKeyEvents: true
									}, {
										allowDecimals: true,
										allowNegative: false,
										//decimalPrecision: 1,
										fieldLabel: langs('Содержание смеси базофилов'),
										name: 'contents_mixture_bazofil',
										tabIndex: TABINDEX_EUDD13EW + 35,
										xtype: 'numberfield',
										enableKeyEvents: true
									}, {
										allowDecimals: true,
										allowNegative: false,
										//decimalPrecision: 1,
										fieldLabel: langs('Содержание смеси незрелых клеток (%)'),
										name: 'contents_mixture_nezrelklet',
										tabIndex: TABINDEX_EUDD13EW + 36,
										xtype: 'numberfield',
										enableKeyEvents: true
									}, {
										allowDecimals: true,
										allowNegative: false,
										//decimalPrecision: 1,
										fieldLabel: langs('Количество гранулоцитов (%)'),
										name: 'granulocytes',
										tabIndex: TABINDEX_EUDD13EW + 37,
										xtype: 'numberfield',
										enableKeyEvents: true
									}, {
										allowDecimals: true,
										allowNegative: false,
										//decimalPrecision: 1,
										fieldLabel: langs('Количество моноцитов (%)'),
										name: 'number_monocytes',
										tabIndex: TABINDEX_EUDD13EW + 38,
										xtype: 'numberfield',
										enableKeyEvents: true
									}, {
										allowDecimals: true,
										allowNegative: false,
										fieldLabel: langs('Скорость оседания эритроцитов (мм/ч)'),
										name: 'erythrocyte_sedimentation_rate',
										tabIndex: TABINDEX_EUDD13EW + 39,
										xtype: 'numberfield',
										enableKeyEvents: true
									}, {
										allowDecimals: true,
										allowNegative: false,
										//decimalPrecision: 1,
										fieldLabel: langs('Количество (л)'),
										name: 'amount_urine',
										tabIndex: TABINDEX_EUDD13EW + 40,
										xtype: 'numberfield',
										enableKeyEvents: true
									}, {
										allowDecimals: true,
										allowNegative: false,
										//decimalPrecision: 1,
										fieldLabel: langs('Белок (г/л)'),
										name: 'cln_urine_protein',
										tabIndex: TABINDEX_EUDD13EW + 41,
										xtype: 'numberfield',
										enableKeyEvents: true
									}, {
										allowDecimals: true,
										allowNegative: false,
										fieldLabel: langs('Альбумины (г/л)'),
										name: 'albumin',
										tabIndex: TABINDEX_EUDD13EW + 42,
										xtype: 'numberfield',
										enableKeyEvents: true
									}, {
										allowDecimals: true,
										allowNegative: false,
										decimalPrecision: 3,
										fieldLabel: langs('Креатинин (ммоль/л)'),
										name: 'bio_blood_kreatinin',
										tabIndex: TABINDEX_EUDD13EW + 43,
										xtype: 'numberfield',
										enableKeyEvents: true
									}, {
										allowDecimals: true,
										allowNegative: false,
										//decimalPrecision: 1,
										fieldLabel: langs('Билирубин общий (мкмоль/л)'),
										name: 'bio_blood_bili',
										tabIndex: TABINDEX_EUDD13EW + 44,
										xtype: 'numberfield',
										enableKeyEvents: true
									}, {
										allowDecimals: true,
										allowNegative: false,
										//decimalPrecision: 2,
										fieldLabel: langs('АсАт (аспартат-аминотрансаминазы) (ммоль/л)'),
										name: 'AsAt',
										tabIndex: TABINDEX_EUDD13EW + 45,
										xtype: 'numberfield',
										enableKeyEvents: true
									}, {
										allowDecimals: true,
										allowNegative: false,
										//decimalPrecision: 2,
										fieldLabel: langs('АлАт(аланин-аминотрансаминазы) (ммоль/л)'),
										name: 'AlAt',
										tabIndex: TABINDEX_EUDD13EW + 46,
										xtype: 'numberfield',
										enableKeyEvents: true
									}, {
										allowDecimals: true,
										allowNegative: false,
										fieldLabel: langs('Фибриноген (г/л)'),
										name: 'fibrinogen',
										tabIndex: TABINDEX_EUDD13EW + 47,
										xtype: 'numberfield',
										enableKeyEvents: true
									}, {
										allowDecimals: true,
										allowNegative: false,
										//decimalPrecision: 1,
										fieldLabel: langs('Калий (ммоль/л)'),
										name: 'potassium',
										tabIndex: TABINDEX_EUDD13EW + 48,
										xtype: 'numberfield',
										enableKeyEvents: true
									}, {
										allowDecimals: true,
										allowNegative: false,
										fieldLabel: langs('Натрий (ммоль/л)'),
										name: 'sodium',
										tabIndex: TABINDEX_EUDD13EW + 49,
										xtype: 'numberfield',
										enableKeyEvents: true
									}, {
										fieldLabel: langs('Уровень повышенный'),
										hiddenName: 'antigen_blood',
										tabIndex: TABINDEX_EUDD13EW + 50,
										xtype: 'swyesnocombo',
										enableKeyEvents: true
									}, {
										fieldLabel: langs('Положительный результат'),
										hiddenName: 'positive_result',
										tabIndex: TABINDEX_EUDD13EW + 51,
										xtype: 'swyesnocombo',
										enableKeyEvents: true
									}, {
										fieldLabel: langs('Выявлены сонографические признаки онкологических заболеваний'),
										hiddenName: 'sonographic_signs',
										tabIndex: TABINDEX_EUDD13EW + 52,
										xtype: 'swyesnocombo',
										enableKeyEvents: true
									}, {
										fieldLabel: langs('Патология обнаружена'),
										hiddenName: 'pathology_found',
										tabIndex: TABINDEX_EUDD13EW + 53,
										xtype: 'swyesnocombo',
										enableKeyEvents: true
									}, {
										enableKeyEvents: true,
										fieldLabel: langs('Количество'),
										name: 'amount_urine_s',
										tabIndex: TABINDEX_EUDD13EW + 54,
										xtype: 'textfield'
									}, {
										enableKeyEvents: true,
										fieldLabel: langs('Удельный вес'),
										name: 'specific_weight_s',
										tabIndex: TABINDEX_EUDD13EW + 55,
										xtype: 'textfield'
									}, {
										enableKeyEvents: true,
										fieldLabel: langs('Белок'),
										name: 'cln_urine_protein_s',
										tabIndex: TABINDEX_EUDD13EW + 56,
										xtype: 'textfield'
									}, {
										enableKeyEvents: true,
										fieldLabel: langs('Сахар'),
										name: 'cln_urine_sugar_s',
										tabIndex: TABINDEX_EUDD13EW + 57,
										xtype: 'textfield'
									}, {
										enableKeyEvents: true,
										fieldLabel: langs('Ацетон'),
										name: 'urine_acetone_s',
										tabIndex: TABINDEX_EUDD13EW + 58,
										xtype: 'textfield'
									}, {
										enableKeyEvents: true,
										fieldLabel: langs('Билирубин'),
										name: 'urine_bili_s',
										tabIndex: TABINDEX_EUDD13EW + 59,
										xtype: 'textfield'
									}, {
										enableKeyEvents: true,
										fieldLabel: langs('Уробилин'),
										name: 'urine_urobili_s',
										tabIndex: TABINDEX_EUDD13EW + 60,
										xtype: 'textfield'
									}, {
										enableKeyEvents: true,
										fieldLabel: langs('Эритроциты'),
										name: 'cln_urine_erit_s',
										tabIndex: TABINDEX_EUDD13EW + 61,
										xtype: 'textfield'
									}, {
										enableKeyEvents: true,
										fieldLabel: langs('Лейкоциты'),
										name: 'cln_urine_leyck_s',
										tabIndex: TABINDEX_EUDD13EW + 62,
										xtype: 'textfield'
									}, {
										enableKeyEvents: true,
										fieldLabel: langs('Цилиндры гиалиновые'),
										name: 'urine_hyal_cylin_s',
										tabIndex: TABINDEX_EUDD13EW + 63,
										xtype: 'textfield'
									}, {
										enableKeyEvents: true,
										fieldLabel: langs('Цилиндры зернистые'),
										name: 'urine_gran_cylin_s',
										tabIndex: TABINDEX_EUDD13EW + 64,
										xtype: 'textfield'
									}, {
										enableKeyEvents: true,
										fieldLabel: langs('Цилиндры восковидные'),
										name: 'urine_waxy_cylin_s',
										tabIndex: TABINDEX_EUDD13EW + 65,
										xtype: 'textfield'
									}, {
										enableKeyEvents: true,
										fieldLabel: langs('Эпителий'),
										name: 'urine_epit_s',
										tabIndex: TABINDEX_EUDD13EW + 66,
										xtype: 'textfield'
									}, {
										enableKeyEvents: true,
										fieldLabel: langs('Эпителий почечный'),
										name: 'urine_epit_kidney_s',
										tabIndex: TABINDEX_EUDD13EW + 67,
										xtype: 'textfield'
									}, {
										enableKeyEvents: true,
										fieldLabel: langs('Эпителий плоский'),
										name: 'urine_epit_flat_s',
										tabIndex: TABINDEX_EUDD13EW + 68,
										xtype: 'textfield'
									}, {
										enableKeyEvents: true,
										fieldLabel: langs('Слизь'),
										name: 'urine_mucus_s',
										tabIndex: TABINDEX_EUDD13EW + 69,
										xtype: 'textfield'
									}, {
										enableKeyEvents: true,
										fieldLabel: langs('Соли'),
										name: 'urine_salt_s',
										tabIndex: TABINDEX_EUDD13EW + 70,
										xtype: 'textfield'
									}, {
										enableKeyEvents: true,
										fieldLabel: langs('Бактерии'),
										name: 'urine_bact_s',
										tabIndex: TABINDEX_EUDD13EW + 71,
										xtype: 'textfield'
									}, {
										enableKeyEvents: true,
										fieldLabel: langs('Цвет'),
										name: 'color',
										tabIndex: TABINDEX_EUDD13EW + 72,
										xtype: 'textfield'
									}, {
										allowDecimals: true,
										allowNegative: false,
										enableKeyEvents: true,
										fieldLabel: langs('рН'),
										name: 'ph',
										tabIndex: TABINDEX_EUDD13EW + 73,
										xtype: 'numberfield'
									}, {
										enableKeyEvents: true,
										fieldLabel: langs('Запах'),
										name: 'odour',
										tabIndex: TABINDEX_EUDD13EW + 74,
										xtype: 'textfield'
									}, {
										allowDecimals: true,
										allowNegative: false,
										enableKeyEvents: true,
										fieldLabel: langs('Плотность (г/л)'),
										name: 'density',
										tabIndex: TABINDEX_EUDD13EW + 75,
										xtype: 'numberfield'
									}, {
										enableKeyEvents: true,
										fieldLabel: langs('Прозрачность'),
										name: 'transparent',
										tabIndex: TABINDEX_EUDD13EW + 76,
										xtype: 'textfield'
									}, {
										fieldLabel: 'Туберкулез',
										hiddenName: 'migrant_tub',
										xtype: 'swisdetectedcombo',
										listeners: {
											'select': function(combo, record, index) {
												win.setPrintItemsDisabled();
												var base_form = win.findById('EUDD13EW_EvnUslugaDispDopEditForm').getForm();
												base_form.findField('migrant_tub_decr').setDisabled(!(record.get('YesNo_id') && record.get('YesNo_id') == 2));
												base_form.findField('migrant_prev_fg').setDisabled(!(record.get('YesNo_id') && record.get('YesNo_id') == 2));
												base_form.findField('migrant_tub_first_dt').setDisabled(!(record.get('YesNo_id') && record.get('YesNo_id') == 2));
												base_form.findField('migrant_tub_take_dt').setDisabled(!(record.get('YesNo_id') && record.get('YesNo_id') == 2));
												base_form.findField('migrant_tub_group').setDisabled(!(record.get('YesNo_id') && record.get('YesNo_id') == 2));
												base_form.findField('migrant_tub_method').setDisabled(!(record.get('YesNo_id') && record.get('YesNo_id') == 2));
												base_form.findField('migrant_tub_decay').setDisabled(!(record.get('YesNo_id') && record.get('YesNo_id') == 2));
												base_form.findField('migrant_tub_bac').setDisabled(!(record.get('YesNo_id') && record.get('YesNo_id') == 2));
												base_form.findField('migrant_tub_bac_method').setDisabled(!(record.get('YesNo_id') && record.get('YesNo_id') == 2));
												base_form.findField('migrant_tub_morbus').setDisabled(!(record.get('YesNo_id') && record.get('YesNo_id') == 2));
												base_form.findField('migrant_tub_narko').setDisabled(!(record.get('YesNo_id') && record.get('YesNo_id') == 2));
											}
										}
									}, {
										fieldLabel: 'Принадлежность к декретированным группам',
										hiddenName: 'migrant_tub_decr',
										xtype: 'swyesnocombo',
										enableKeyEvents: true
									}, {
										fieldLabel: langs('Сроки предыдущего ФГ обследования'),
										anchor:'100%',
										hiddenName: 'migrant_prev_fg',
										xtype: 'swcommonsprcombo',
										sortField:'TubFluorSurveyPeriodType_Code',
										comboSubject: 'TubFluorSurveyPeriodType'
									}, {
										enableKeyEvents: true,
										fieldLabel: 'Дата первого обращения за медицинской помощью',
										plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
										name: 'migrant_tub_first_dt',
										xtype: 'swdatefield'
									}, {
										enableKeyEvents: true,
										fieldLabel: 'Дата взятия на учет в противотуберкулезное учреждение',
										plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
										name: 'migrant_tub_take_dt',
										xtype: 'swdatefield'
									}, {
										fieldLabel: 'Группа наблюдения',
										hiddenName: 'migrant_tub_group',
										xtype: 'swcommonsprcombo',
										sortField:'TubSurveyMigrantGroupType_Code',
										comboSubject: 'TubSurveyMigrantGroupType'
									}, {
										fieldLabel: langs('Метод выявления'),
										hiddenName: 'migrant_tub_method',
										xtype: 'swcommonsprcombo',
										sortField:'TubDetectionMethodType_Code',
										comboSubject: 'TubDetectionMethodType'
									}, {
										fieldLabel: 'Наличие распада',
										hiddenName: 'migrant_tub_decay',
										xtype: 'swyesnocombo',
										enableKeyEvents: true
									}, {
										fieldLabel: 'Подтверждение бактериовыделения',
										hiddenName: 'migrant_tub_bac',
										xtype: 'swyesnocombo',
										enableKeyEvents: true
									}, {
										fieldLabel: langs('Метод подтверждения бактериовыделения'),
										anchor:'100%',
										hiddenName: 'migrant_tub_bac_method',
										xtype: 'swcommonsprcombo',
										sortField:'TubMethodConfirmBactType_Code',
										comboSubject: 'TubMethodConfirmBactType'
									}, {
										fieldLabel: langs('Сопутствующие заболевания'),
										anchor:'100%',
										hiddenName: 'migrant_tub_morbus',
										xtype: 'swcommonsprcombo',
										sortField:'TubDiagSop_Code',
										comboSubject: 'TubDiagSop',
										onLoadStore: function(store) {
											this.lastQuery = '';
											store.clearFilter();
											store.filterBy(function(rec) {
												if (rec.get('TubDiagSop_id') == 7) rec.set('TubDiagSop_Name', 'Прочее');
												return (rec.get('TubDiagSop_id') <= 7);
											});
										}
									}, {
										fieldLabel: 'Состоит на учете в наркологическом диспансере',
										hiddenName: 'migrant_tub_narko',
										xtype: 'swyesnocombo',
										enableKeyEvents: true
									}, {
										fieldLabel: 'Сифилис',
										hiddenName: 'migrant_syphilis',
										xtype: 'swisdetectedcombo',
										enableKeyEvents: true,
										listeners: {
											'select': function(combo, record, index) {
												win.setPrintItemsDisabled();
											}
										}
									}, {
										fieldLabel: 'Наркологическое расстройство',
										hiddenName: 'migrant_narko',
										xtype: 'swisdetectedcombo',
										enableKeyEvents: true
									}, {
										enableKeyEvents: true,
										fieldLabel: 'Название и серия диагностикума на ВИЧ-инфекцию',
										name: 'migrant_HIV_diagn',
										xtype: 'textfield'
									}, {
										fieldLabel: 'ВИЧ-инфекция',
										hiddenName: 'migrant_HIV',
										xtype: 'swisdetectedcombo',
										enableKeyEvents: true,
										listeners: {
											'select': function(combo, record, index) {
												win.setPrintItemsDisabled();
											}
										}
									}, {
										fieldLabel: 'Лепра',
										hiddenName: 'migrant_HIV_lepr',
										xtype: 'swisdetectedcombo',
										enableKeyEvents: true,
										listeners: {
											'select': function(combo, record, index) {
												win.setPrintItemsDisabled();
											}
										}
									}, {
										comboSubject: 'SurveyResult',
										fieldLabel: 'АТ к ВИЧ-1, ВИЧ-2',
										hiddenName: 'migrant_HIV_at1_at2',
										xtype: 'swcommonsprcombo'
									}, {
										comboSubject: 'SurveyResult',
										fieldLabel: 'АТ к ВИЧ-1',
										hiddenName: 'migrant_HIV_at1',
										xtype: 'swcommonsprcombo'
									}, {
										comboSubject: 'SurveyResult',
										fieldLabel: 'АТ к ВИЧ-2',
										hiddenName: 'migrant_HIV_at2',
										xtype: 'swcommonsprcombo'
									}, {
										comboSubject: 'SurveyResult',
										fieldLabel: 'Диагностика сифилиса (ИФА)',
										hiddenName: 'migrant_syphilis_ifa',
										xtype: 'swcommonsprcombo'
									}, {
										comboSubject: 'SurveyResult',
										fieldLabel: 'Диагностика сифилиса (РПГА)',
										hiddenName: 'migrant_syphilis_rpga',
										xtype: 'swcommonsprcombo'
									}, {
										comboSubject: 'SurveyResult',
										fieldLabel: 'Диагностика сифилиса (РМП)',
										hiddenName: 'migrant_syphilis_rmp',
										xtype: 'swcommonsprcombo'
									}, {
										fieldLabel: 'Флюорография легких',
										hiddenName: 'migrant_fluoro',
										xtype: 'swbaselocalcombo',
										store: new Ext.data.SimpleStore({
											key: 'id',
											autoLoad: false,
											fields:[
												{name: 'Rate_id', type: 'int'},
												{name: 'Rate_Name', type: 'string'}
											],
											data: [
												[1, 'Патология не обнаружена'],
												[2, 'Патология обнаружена']
											]
										}),
										editable: false,
										displayField:'Rate_Name',
										valueField: 'Rate_id'
									}, {
										comboSubject: 'SurveyResult',
										fieldLabel: 'Проба Манту',
										hiddenName: 'migrant_mantu',
										xtype: 'swcommonsprcombo'
									}, {
										comboSubject: 'SurveyResult',
										fieldLabel: 'Проба с аллергеном туберкулезным рекомбинантным',
										hiddenName: 'migrant_allergen',
										xtype: 'swcommonsprcombo'
									}, {
										comboSubject: 'SurveyResult',
										fieldLabel: 'Результат на амфетамин',
										hiddenName: 'migrant_urine_amphet',
										xtype: 'swcommonsprcombo'
									}, {
										comboSubject: 'SurveyResult',
										fieldLabel: 'Результат на марихуану',
										hiddenName: 'migrant_urine_marij',
										xtype: 'swcommonsprcombo'
									}, {
										comboSubject: 'SurveyResult',
										fieldLabel: 'Результат на морфин',
										hiddenName: 'migrant_urine_morp',
										xtype: 'swcommonsprcombo'
									}, {
										comboSubject: 'SurveyResult',
										fieldLabel: 'Результат на кокаин',
										hiddenName: 'migrant_urine_cocaine',
										xtype: 'swcommonsprcombo'
									}, {
										comboSubject: 'SurveyResult',
										fieldLabel: 'Результат на метамфетамин',
										hiddenName: 'migrant_urine_meth',
										xtype: 'swcommonsprcombo'
									}, {
										fieldLabel: 'Противопоказания к управлению ТС',
										hiddenName: 'driver_result',
										xtype: 'swisdetectedcombo',
										enableKeyEvents: true
									}, {
										comboSubject: 'SurveyTubResult',
										fieldLabel: 'Результат',
										hiddenName: 'migrant_Tub_Probe',
										xtype: 'swcommonsprcombo'
									}, {
										allowDecimals: false,
										allowNegative: false,
										fieldLabel: 'Размер (мм)',
										maxValue: 30,
										name: 'migrant_tub_size',
										xtype: 'numberfield'
									}]
								}],
							layout: 'form',
							reader: new Ext.data.JsonReader({
								success: function() { }
							}, [
								{ name: 'EvnUslugaDispDop_id' },
								{ name: 'EvnVizitDispDop_id' },
								{ name: 'EvnVizitDispDop_pid' },
								{ name: 'DopDispInfoConsent_id' },
								{ name: 'EvnDirection_id' },
								{ name: 'PersonEvn_id' },
								{ name: 'Server_id' },
								{ name: 'EvnDirection_Type' },
								{ name: 'EvnDirection_insDate' },
								{ name: 'EvnDirection_Num' },
								{ name: 'EvnDirection_RecTo' },
								{ name: 'EvnDirection_RecDate' },
								{ name: 'EvnUslugaDispDop_ExamPlace' },
								{ name: 'EvnUslugaDispDop_setDate' },
								{ name: 'EvnUslugaDispDop_setTime' },
								{ name: 'UslugaComplex_id' },
								{ name: 'EvnUslugaDispDop_didDate' },
								{ name: 'EvnUslugaDispDop_didTime' },
								{ name: 'EvnUslugaDispDop_disDate' },
								{ name: 'EvnUslugaDispDop_disTime' },
								{ name: 'Lpu_uid' },
								{ name: 'LpuSectionProfile_id' },
								{ name: 'MedSpecOms_id' },
								{ name: 'ExaminationPlace_id' },
								{ name: 'LpuSection_id' },
								{ name: 'MedPersonal_id' },
								{ name: 'MedStaffFact_id' },
								{ name: 'Cyto_IsNotAgree' },
								{ name: 'CytoUslugaComplex_id' },
								{ name: 'CytoEvnUsluga_setDate' },
								{ name: 'CytoExaminationPlace_id' },
								{ name: 'CytoLpu_id' },
								{ name: 'CytoLpuSectionProfile_id' },
								{ name: 'CytoLpuSection_id' },
								{ name: 'CytoMedSpecOms_id' },
								{ name: 'CytoMedPersonal_id' },
								{ name: 'CytoMedStaffFact_id' },
								{ name: 'Diag_id' },
								{ name: 'TumorStage_id'},
								{ name: 'DopDispDiagType_id' },
								{ name: 'EvnUslugaDispDop_Result' },
								{ name: 'systolic_blood_pressure' },
								{ name: 'diastolic_blood_pressure' },
								{ name: 'person_weight' },
								{ name: 'person_height' },
								{ name: 'waist_circumference' },
								{ name: 'body_mass_index' },
								{ name: 'total_cholesterol' },
								{ name: 'glucose' },
								{ name: 'eye_pressure_right' },
								{ name: 'eye_pressure_left' },
								//{ name: 'eye_pressure_increase' },
								{ name: 'number_erythrocytes' },
								{ name: 'cln_blood_gem' },
								{ name: 'hematocrit' },
								{ name: 'distribution_width_erythrocytes' },
								{ name: 'volume_erythrocyte' },
								{ name: 'hemoglobin_content' },
								{ name: 'concentration_hemoglobin' },
								{ name: 'cln_blood_trom' },
								{ name: 'cln_blood_leyck' },
								{ name: 'lymphocyte_content' },
								{ name: 'contents_mixture_monocit' },
								{ name: 'contents_mixture_eozinofil' },
								{ name: 'contents_mixture_bazofil' },
								{ name: 'contents_mixture_nezrelklet' },
								{ name: 'granulocytes' },
								{ name: 'number_monocytes' },
								{ name: 'erythrocyte_sedimentation_rate' },
								{ name: 'amount_urine' },
								{ name: 'cln_urine_protein' },
								{ name: 'antigen_blood' },
								{ name: 'positive_result' },
								{ name: 'sonographic_signs' },
								{ name: 'pathology_found' },
								{ name: 'albumin' },
								{ name: 'bio_blood_kreatinin' },
								{ name: 'bio_blood_bili' },
								{ name: 'AsAt' },
								{ name: 'AlAt' },
								{ name: 'fibrinogen' },
								{ name: 'potassium' },
								{ name: 'sodium' },
								{ name: 'amount_urine_s' },
								{ name: 'specific_weight_s' },
								{ name: 'cln_urine_protein_s' },
								{ name: 'cln_urine_sugar_s' },
								{ name: 'urine_acetone_s' },
								{ name: 'urine_bili_s' },
								{ name: 'urine_urobili_s' },
								{ name: 'cln_urine_erit_s' },
								{ name: 'cln_urine_leyck_s' },
								{ name: 'urine_hyal_cylin_s' },
								{ name: 'urine_gran_cylin_s' },
								{ name: 'urine_waxy_cylin_s' },
								{ name: 'urine_epit_s' },
								{ name: 'urine_epit_kidney_s' },
								{ name: 'urine_epit_flat_s' },
								{ name: 'urine_mucus_s' },
								{ name: 'urine_salt_s' },
								{ name: 'urine_bact_s' },
								{ name: 'color' },
								{ name: 'ph' },
								{ name: 'odour' },
								{ name: 'density' },
								{ name: 'transparent' },
								{ name: 'migrant_tub' },
								{ name: 'migrant_tub_decr' },
								{ name: 'migrant_prev_fg' },
								{ name: 'migrant_tub_first_dt' },
								{ name: 'migrant_tub_take_dt' },
								{ name: 'migrant_tub_group' },
								{ name: 'migrant_tub_method' },
								{ name: 'migrant_tub_decay' },
								{ name: 'migrant_tub_bac' },
								{ name: 'migrant_tub_bac_method' },
								{ name: 'migrant_tub_morbus' },
								{ name: 'migrant_tub_narko' },
								{ name: 'migrant_narko' },
								{ name: 'migrant_syphilis' },
								{ name: 'migrant_HIV_diagn' },
								{ name: 'migrant_HIV' },
								{ name: 'migrant_HIV_lepr' },
								{ name: 'migrant_HIV_at1_at2' },
								{ name: 'migrant_HIV_at1' },
								{ name: 'migrant_HIV_at2' },
								{ name: 'migrant_syphilis_ifa' },
								{ name: 'migrant_syphilis_rpga' },
								{ name: 'migrant_syphilis_rmp' },
								{ name: 'migrant_mantu' },
								{ name: 'migrant_allergen' },
								{ name: 'migrant_fluoro' },
								{ name: 'migrant_urine_amphet' },
								{ name: 'migrant_urine_marij' },
								{ name: 'migrant_urine_morp' },
								{ name: 'migrant_urine_cocaine' },
								{ name: 'migrant_urine_meth' },
								{ name: 'driver_result' },
								{ name: 'migrant_Tub_Probe' },
								{ name: 'migrant_tub_size' }
							]),
							region: 'center'
						}),
						win.FilePanel
					]
				})
			]
		});
		sw.Promed.swEvnUslugaDispDop13EditWindow.superclass.initComponent.apply(this, arguments);

		this.findById('eudd13ewUslugaComplexCombo').addListener('change', function(combo, newValue, oldValue) {
			this.filterProfile();
			this.filterMedSpec();
			if (getRegionNick().inlist(['ekb', 'pskov'])) { // для Екб список отделений и врачей зависит от специальности в услуге (MedSpecOms_id)
				this.setLpuSectionAndMedStaffFactFilter();
			}
		}.createDelegate(this));

		this.findById('eudd13ewCytoUslugaComplexCombo').addListener('change', function(combo, newValue, oldValue) {
			this.filterCytoProfile();
			this.filterCytoMedSpec();
		}.createDelegate(this));

		this.findById('EUDD13EW_Diag_id').addListener('change', function(combo, newValue, oldValue) {
			if(this.diagIsChanged)
				this.loadSpecificsTree();
		}.createDelegate(this));

	},
	loadFirstMedPersonal: true,
	filterMedSpec: function() {
		var win = this;
		var base_form = this.findById('EUDD13EW_EvnUslugaDispDopEditForm').getForm();

		if (getRegionNick() == 'ekb' && !win.DispClass_id.inlist([19,26])) {
			win.MedSpecOmsList = base_form.findField('UslugaComplex_id').getFieldValue('MedSpecOmsList');
		}

		var curDate = getGlobalOptions().date;
		if ( !Ext.isEmpty(base_form.findField('EvnUslugaDispDop_didDate').getValue()) ) {
			curDate = Ext.util.Format.date(base_form.findField('EvnUslugaDispDop_didDate').getValue(), 'd.m.Y');
		}

		if ( getRegionNick() == 'astra' ) {
			if ( base_form.findField('MedSpecOms_id').getStore().getCount() == 0 ) {
				base_form.findField('MedSpecOms_id').getStore().load({
					params: {
						UslugaComplex_id: base_form.findField('UslugaComplex_id').getValue(),
						onDate: curDate,
						DispClass_id: win.DispClass_id
					},
					callback: function() {
						win.setMedSpecOms();
						win.loadFirstMedPersonal = true;
					}
				});
			}
		}
		else {
			base_form.findField('MedSpecOms_id').getStore().removeAll();

			if (!Ext.isEmpty(base_form.findField('UslugaComplex_id').getValue())) {
				// загружаем списки Специальность в зависимости от Услуги
				if (getRegionNick() == 'perm') { // на перми специальность зависит от профиля
					if (!Ext.isEmpty(base_form.findField('LpuSectionProfile_id').getValue())) {
						if (!win.MedSpecOms_id) {
							win.MedSpecOms_id = base_form.findField('MedSpecOms_id').getValue();
						}
						base_form.findField('MedSpecOms_id').clearValue();
						base_form.findField('MedSpecOms_id').getStore().load({
							params: {
								UslugaComplex_id: base_form.findField('UslugaComplex_id').getValue(),
								LpuSectionProfile_id: base_form.findField('LpuSectionProfile_id').getValue(),
								onDate: curDate,
								DispClass_id: win.DispClass_id
							},
							callback: function () {
								var MedSpecOms_id = win.MedSpecOms_id;
								win.MedSpecOms_id = null;
								var index = base_form.findField('MedSpecOms_id').getStore().findBy(function(rec) {
									return (rec.get('MedSpecOms_id') == MedSpecOms_id);
								});
								if (index >= 0) {
									base_form.findField('MedSpecOms_id').setValue(base_form.findField('MedSpecOms_id').getStore().getAt(index).get('MedSpecOms_id'));
								}

								win.setMedSpecOms();
								win.loadFirstMedPersonal = true;
							}
						});
					} else {
						base_form.findField('MedSpecOms_id').clearValue();
						base_form.findField('MedSpecOms_id').getStore().removeAll();
					}
				} else {
					base_form.findField('MedSpecOms_id').getStore().load({
						params: {
							UslugaComplex_id: base_form.findField('UslugaComplex_id').getValue(),
							onDate: curDate,
							DispClass_id: win.DispClass_id
						},
						callback: function () {
							win.setMedSpecOms();
							win.loadFirstMedPersonal = true;
						}
					});
				}
			}
		}
	},
	filterProfile: function() {
		var win = this;
		var base_form = this.findById('EUDD13EW_EvnUslugaDispDopEditForm').getForm();

		var curDate = getGlobalOptions().date;
		if ( !Ext.isEmpty(base_form.findField('EvnUslugaDispDop_didDate').getValue()) ) {
			curDate = Ext.util.Format.date(base_form.findField('EvnUslugaDispDop_didDate').getValue(), 'd.m.Y');
		}
		var LpuSectionProfile_id = base_form.findField('LpuSectionProfile_id').getValue();

		if ( getRegionNick() == 'astra' ) {
			if ( base_form.findField('LpuSectionProfile_id').getStore().getCount() == 0 ) {
				base_form.findField('LpuSectionProfile_id').getStore().load({
					params: {
						UslugaComplex_id: base_form.findField('UslugaComplex_id').getValue(),
						onDate: curDate,
						DispClass_id: win.DispClass_id
					},
					callback: function() {
						win.setLpuSectionProfile();
					}
				});
			}
		}
		else {
			base_form.findField('LpuSectionProfile_id').getStore().removeAll();

			if (!Ext.isEmpty(base_form.findField('UslugaComplex_id').getValue())) {
				// загружаем списки Профиль и Специальность в зависимости от Услуги
				base_form.findField('LpuSectionProfile_id').getStore().load({
					params: {
						UslugaComplex_id: base_form.findField('UslugaComplex_id').getValue(),
						onDate: curDate,
						DispClass_id: win.DispClass_id
					},
					callback: function() {
						if ( !Ext.isEmpty(LpuSectionProfile_id) ) {
							base_form.findField('LpuSectionProfile_id').setValue(LpuSectionProfile_id);
						}
						win.setLpuSectionProfile();
					}
				});
			}
		}
	},
	filterCytoMedSpec: function() {
		var win = this;
		var base_form = this.findById('EUDD13EW_EvnUslugaDispDopEditForm').getForm();

		if (getRegionNick() == 'ekb') {
			win.CytoMedSpecOms_id = base_form.findField('CytoUslugaComplex_id').getFieldValue('MedSpecOms_id');
		}

		var curDate = getGlobalOptions().date;
		if ( !Ext.isEmpty(base_form.findField('CytoEvnUsluga_setDate').getValue()) ) {
			curDate = Ext.util.Format.date(base_form.findField('CytoEvnUsluga_setDate').getValue(), 'd.m.Y');
		}

		if ( getRegionNick() == 'astra' ) {
			if ( base_form.findField('CytoMedSpecOms_id').getStore().getCount() == 0 ) {
				base_form.findField('CytoMedSpecOms_id').getStore().load({
					params: {
						UslugaComplex_id: base_form.findField('CytoUslugaComplex_id').getValue(),
						onDate: curDate,
						DispClass_id: win.DispClass_id
					},
					callback: function() {
						win.setCytoMedSpecOms();
					}
				});
			}
		}
		else {
			base_form.findField('CytoMedSpecOms_id').getStore().removeAll();

			if (!Ext.isEmpty(base_form.findField('CytoUslugaComplex_id').getValue())) {
				// загружаем списки Специальность в зависимости от Услуги
				if (false && getRegionNick() == 'perm') { // на перми спеицальность зависит от профиля
					if (!Ext.isEmpty(base_form.findField('CytoLpuSectionProfile_id').getValue())) {
						var CytoMedSpecOms_id = base_form.findField('CytoMedSpecOms_id').getValue();
						base_form.findField('CytoMedSpecOms_id').clearValue();
						base_form.findField('CytoMedSpecOms_id').getStore().load({
							params: {
								UslugaComplex_id: base_form.findField('CytoUslugaComplex_id').getValue(),
								LpuSectionProfile_id: base_form.findField('CytoLpuSectionProfile_id').getValue(),
								onDate: curDate,
								DispClass_id: win.DispClass_id
							},
							callback: function () {
								var index = base_form.findField('CytoMedSpecOms_id').getStore().findBy(function(rec) {
									return (rec.get('MedSpecOms_id') == CytoMedSpecOms_id);
								});
								if (index >= 0) {
									base_form.findField('CytoMedSpecOms_id').setValue(base_form.findField('CytoMedSpecOms_id').getStore().getAt(index).get('MedSpecOms_id'));
								}

								win.setCytoMedSpecOms();
							}
						});
					} else {
						base_form.findField('CytoMedSpecOms_id').clearValue();
						base_form.findField('CytoMedSpecOms_id').getStore().removeAll();
					}
				} else {
					base_form.findField('CytoMedSpecOms_id').getStore().load({
						params: {
							UslugaComplex_id: base_form.findField('CytoUslugaComplex_id').getValue(),
							onDate: curDate,
							DispClass_id: win.DispClass_id
						},
						callback: function () {
							win.setCytoMedSpecOms();
						}
					});
				}
			}
		}
	},
	filterCytoProfile: function() {
		var win = this;
		var base_form = this.findById('EUDD13EW_EvnUslugaDispDopEditForm').getForm();

		var curDate = getGlobalOptions().date;
		if ( !Ext.isEmpty(base_form.findField('CytoEvnUsluga_setDate').getValue()) ) {
			curDate = Ext.util.Format.date(base_form.findField('CytoEvnUsluga_setDate').getValue(), 'd.m.Y');
		}

		if ( getRegionNick() == 'astra' ) {
			if ( base_form.findField('CytoLpuSectionProfile_id').getStore().getCount() == 0 ) {
				base_form.findField('CytoLpuSectionProfile_id').getStore().load({
					params: {
						UslugaComplex_id: base_form.findField('CytoUslugaComplex_id').getValue(),
						onDate: curDate,
						DispClass_id: win.DispClass_id
					},
					callback: function() {
						win.setCytoLpuSectionProfile();
					}
				});
			}
		}
		else {
			base_form.findField('CytoLpuSectionProfile_id').getStore().removeAll();

			if (!Ext.isEmpty(base_form.findField('CytoUslugaComplex_id').getValue())) {
				// загружаем списки Профиль и Специальность в зависимости от Услуги
				base_form.findField('CytoLpuSectionProfile_id').getStore().load({
					params: {
						UslugaComplex_id: base_form.findField('CytoUslugaComplex_id').getValue(),
						onDate: curDate,
						DispClass_id: win.DispClass_id
					},
					callback: function() {
						win.setCytoLpuSectionProfile();
					}
				});
			}
		}
	},
	keys: [{
		alt: true,
		fn: function(inp, e) {
			e.stopEvent();

			if (e.browserEvent.stopPropagation)
				e.browserEvent.stopPropagation();
			else
				e.browserEvent.cancelBubble = true;

			if (e.browserEvent.preventDefault)
				e.browserEvent.preventDefault();
			else
				e.browserEvent.returnValue = false;

			e.browserEvent.returnValue = false;
			e.returnValue = false;

			if (Ext.isIE)
			{
				e.browserEvent.keyCode = 0;
				e.browserEvent.which = 0;
			}

			var current_window = Ext.getCmp('EvnUslugaDispDop13EditWindow');

			if (e.getKey() == Ext.EventObject.J)
			{
				current_window.hide();
			}
			else if (e.getKey() == Ext.EventObject.C)
			{
				if ('view' != current_window.action)
				{
					current_window.doSave();
				}
			}
		},
		key: [ Ext.EventObject.C, Ext.EventObject.J ],
		scope: this,
		stopEvent: false
	}],
	layout: 'border',
	cytoUslugaEnabled: function() {
		return (
			(
				(getRegionNick().inlist(['ekb']) && this.DispClass_id == 5 && this.UslugaComplex_Date >= Date.parseDate('01.05.2019', 'd.m.Y'))
				|| this.DispClass_id == 1
			)
			&& this.SurveyType_Code == 20
			&& (
				getRegionNick().inlist(['ekb'])
				|| (
					getRegionNick().inlist(['astra'])
					&& this.UslugaComplex_Date < Date.parseDate('01.01.2018', 'd.m.Y')
				)
				|| (
					getRegionNick().inlist(['perm'])
					&& this.UslugaComplex_Date < Date.parseDate('01.06.2019', 'd.m.Y')
				)
			)
		);
	},
	setFieldsBySurveyTypeCode: function() {
		var win = this;
		var form = win.findById('EUDD13EW_EvnUslugaDispDopEditForm');
		var base_form = form.getForm();

		this.findById('EUDD13EW_CytologPanel').hide();
		base_form.findField('CytoUslugaComplex_id').setAllowBlank(true);
		base_form.findField('CytoEvnUsluga_setDate').setAllowBlank(true);
		base_form.findField('CytoExaminationPlace_id').setAllowBlank(true);
		base_form.findField('CytoLpu_id').setAllowBlank(true);
		base_form.findField('CytoLpuSectionProfile_id').setAllowBlank(true);
		base_form.findField('CytoMedSpecOms_id').setAllowBlank(true);
		base_form.findField('CytoLpuSection_id').setAllowBlank(true);
		base_form.findField('CytoMedStaffFact_id').setAllowBlank(true);

		// список полей показываемых в результатах
		this.inresults = [];
		if (this.type.inlist(['DispTeenInspectionPeriod','DispTeenInspectionProf','DispTeenInspectionPred'])) {
			return true;
		}

		switch ( this.SurveyType_Code ) {
			case 3:
				this.inresults = ['systolic_blood_pressure','diastolic_blood_pressure'];
				break;
			case 4:
				this.inresults = ['person_weight','person_height','waist_circumference','body_mass_index'];
				break;
			case 5:
				this.inresults = ['total_cholesterol'];
				break;
			case 6:
				this.inresults = ['glucose'];
				break;
			case 8:
				this.inresults = ['eye_pressure_right', 'eye_pressure_left'/*, 'eye_pressure_increase'*/];
				break;
			case 9:
			case 10:
				this.inresults = ['number_erythrocytes','cln_blood_gem','hematocrit','distribution_width_erythrocytes','volume_erythrocyte','hemoglobin_content','concentration_hemoglobin','cln_blood_trom','cln_blood_leyck','lymphocyte_content','contents_mixture_monocit','contents_mixture_eozinofil','contents_mixture_bazofil','contents_mixture_nezrelklet','granulocytes','number_monocytes','erythrocyte_sedimentation_rate'];
				break;
			case 11:
				this.inresults = ['amount_urine_s','specific_weight_s','cln_urine_protein_s','cln_urine_sugar_s','urine_acetone_s','urine_bili_s','urine_urobili_s','cln_urine_erit_s','cln_urine_leyck_s','urine_hyal_cylin_s','urine_gran_cylin_s','urine_waxy_cylin_s','urine_epit_s','urine_epit_kidney_s','urine_epit_flat_s','urine_mucus_s','urine_salt_s','urine_bact_s','color','ph','odour','density','transparent'];
				break;
			case 12:
				this.inresults = ['glucose','cln_urine_protein','albumin','bio_blood_kreatinin','bio_blood_bili','AsAt','AlAt','fibrinogen','potassium','sodium','total_cholesterol'];
				break;
			case 13:
				this.inresults = ['antigen_blood'];
				break;
			case 14:
				this.inresults = ['positive_result'];
				break;
			case 15:
				this.inresults = ['sonographic_signs'];
				break;
			case 16:
				this.inresults = ['pathology_found'];
				break;
			case 20:
				if (this.cytoUslugaEnabled()) {
					this.findById('EUDD13EW_CytologPanel').show();
					base_form.findField('CytoUslugaComplex_id').setAllowBlank(false);

					base_form.findField('CytoLpuSection_id').enableLinkedElements();
					base_form.findField('CytoMedStaffFact_id').enableParentElement();

					base_form.findField('CytoExaminationPlace_id').setContainerVisible(false);
					base_form.findField('CytoLpu_id').setContainerVisible(false);
					base_form.findField('CytoLpuSectionProfile_id').setContainerVisible(false);
					base_form.findField('CytoMedSpecOms_id').setContainerVisible(false);

					base_form.findField('Cyto_IsNotAgree').setContainerVisible(!(getRegionNick() == 'ekb' && this.DispClass_id == 5));

					switch ( getRegionNick() ) {
						case 'ekb':
						case 'perm':
							base_form.findField('CytoExaminationPlace_id').lastQuery = '';
							base_form.findField('CytoExaminationPlace_id').getStore().filterBy(function(rec) {
								return rec.get('ExaminationPlace_id').toString().inlist([ '1', '3' ]);
							});
							base_form.findField('CytoExaminationPlace_id').setContainerVisible(true);
							base_form.findField('CytoExaminationPlace_id').setValue(1);
							base_form.findField('CytoExaminationPlace_id').fireEvent('change', base_form.findField('CytoExaminationPlace_id'), 1);
							break;
					}

					base_form.findField('CytoEvnUsluga_setDate').setAllowBlank(false);
					base_form.findField('CytoLpuSection_id').setAllowBlank(false);
					base_form.findField('CytoMedStaffFact_id').setAllowBlank(false);
				}
				break;

			case 150:
				this.inresults = ['migrant_tub', 'migrant_tub_decr', 'migrant_prev_fg', 'migrant_tub_first_dt', 'migrant_tub_take_dt', 'migrant_tub_group', 'migrant_tub_method', 'migrant_tub_decay', 'migrant_tub_bac', 'migrant_tub_bac_method', 'migrant_tub_morbus', 'migrant_tub_narko'];
				break;
			case 151:
				this.inresults = ['migrant_narko'];
				break;
			case 154:
				this.inresults = ['migrant_syphilis'];
				break;
			case 152:
				this.inresults = ['migrant_HIV_diagn', 'migrant_HIV', 'migrant_HIV_lepr'];
				break;
			case 142:
				this.inresults = ['migrant_HIV_at1'];
				break;
			case 143:
				this.inresults = ['migrant_HIV_at2'];
				break;
			case 144:
				this.inresults = ['migrant_syphilis_ifa'];
				break;
			case 145:
				this.inresults = ['migrant_syphilis_rpga'];
				break;
			case 146:
				this.inresults = ['migrant_syphilis_rmp'];
				break;
			case 147:
				this.inresults = ['migrant_mantu'];
				break;
			case 148:
				this.inresults = ['migrant_allergen'];
				break;
			case 153:
				this.inresults = ['migrant_fluoro'];
				break;
			case 149:
				this.inresults = ['migrant_urine_amphet', 'migrant_urine_marij', 'migrant_urine_morp', 'migrant_urine_cocaine', 'migrant_urine_meth'];
				break;
			case 155:
			case 156:
			case 157:
			case 158:
				this.inresults = ['driver_result'];
				break;
			case 159:
				this.inresults = ['migrant_HIV_at1_at2'];
				break;
			case 160:
				this.inresults = ['migrant_Tub_Probe', 'migrant_tub_size'];
				break;
		}

		var items = win.findById('EUDD13EW_ResultsPanel').items.items;
		for (var key in items) {
			var obj = items[key];

			// https://redmine.swan.perm.ru/issues/89093
			if ( typeof obj == 'object' && typeof obj.showContainer == 'function' ) {
				if (obj.name) {
					if (obj.name.inlist(win.inresults)) {
						obj.showContainer();
					} else {
						obj.hideContainer();
					}
				}
				if (obj.hiddenName) {
					if (obj.hiddenName.inlist(win.inresults)) {
						obj.showContainer();
					} else {
						obj.hideContainer();
					}
				}
			}
		}

		if (win.inresults.length == 0)
		{
			win.findById('EUDD13EW_ResultsPanel').hide();
		} else
		{
			win.findById('EUDD13EW_ResultsPanel').show();
		}
	},
	maximizable: true,
	minHeight: 370,
	minWidth: 700,
	modal: true,
	onHide: Ext.emptyFn,
	plain: true,
	resizable: true,
	filterLpuCombo: function(type) {
		var dateFieldName, lpuFieldName;
		if (type == 'cyto') {
			lpuFieldName = 'CytoLpu_id';
			dateFieldName = 'CytoEvnUsluga_setDate';
		} else {
			lpuFieldName = 'Lpu_uid';
			dateFieldName = 'EvnUslugaDispDop_didDate';
		}

		var base_form = this.findById('EUDD13EW_EvnUslugaDispDopEditForm').getForm();
		// фильтр на МО (отображать только открытые действующие)
		var curDate = Date.parseDate(getGlobalOptions().date, 'd.m.Y');
		if ( !Ext.isEmpty(base_form.findField(dateFieldName).getValue()) ) {
			curDate = base_form.findField(dateFieldName).getValue();
		}
		var Lpu_id = base_form.findField(lpuFieldName).getValue();
		base_form.findField(lpuFieldName).lastQuery = '';
		base_form.findField(lpuFieldName).getStore().clearFilter();
		base_form.findField(lpuFieldName).setBaseFilter(function(rec, id) {
			if (!Ext.isEmpty(rec.get('Lpu_EndDate'))) {
				var lpuEndDate = Date.parseDate(rec.get('Lpu_EndDate'), 'd.m.Y');
				if (lpuEndDate < curDate) {
					return false;
				}
			}
			if (!Ext.isEmpty(getGlobalOptions().lpu_id) && rec.get('Lpu_id') == getGlobalOptions().lpu_id) {
				return false;
			}
			return true;
		});

		var index = base_form.findField(lpuFieldName).getStore().findBy(function(rec) {
			return (rec.get('Lpu_id') == Lpu_id);
		});

		if ( index == -1 ) {
			base_form.findField(lpuFieldName).clearValue();
		}
	},
	setLpuSectionProfile: function() {
		if ( getRegionNick().inlist([ 'buryatiya', 'ekb', 'kareliya', 'penza', 'pskov', 'adygeya' ]) ) {
			return false;
		}

		var win = this;
		var base_form = this.findById('EUDD13EW_EvnUslugaDispDopEditForm').getForm();

		var LpuSectionProfile_id;

		var index = base_form.findField('LpuSectionProfile_id').getStore().findBy(function(rec) {
			return (rec.get('LpuSectionProfile_id') == base_form.findField('LpuSectionProfile_id').getValue());
		});

		if (base_form.findField('LpuSectionProfile_id').getStore().getCount() == 1) {
			LpuSectionProfile_id = base_form.findField('LpuSectionProfile_id').getStore().getAt(0).get('LpuSectionProfile_id');
			base_form.findField('LpuSectionProfile_id').setValue(LpuSectionProfile_id);
		} else if (base_form.findField('LpuSectionProfile_id').getStore().getCount() > 1) {
			if ( index >= 0 ) {
				LpuSectionProfile_id = base_form.findField('LpuSectionProfile_id').getStore().getAt(index).get('LpuSectionProfile_id');
			} else if (getRegionNick() != 'astra') {
				LpuSectionProfile_id = base_form.findField('LpuSectionProfile_id').getStore().getAt(0).get('LpuSectionProfile_id');
			}

			if ( !Ext.isEmpty(LpuSectionProfile_id) ) {
				base_form.findField('LpuSectionProfile_id').setValue(LpuSectionProfile_id);
			}
			else {
				base_form.findField('LpuSectionProfile_id').clearValue();
			}
		}

		base_form.findField('LpuSectionProfile_id').fireEvent('change', base_form.findField('LpuSectionProfile_id'), base_form.findField('LpuSectionProfile_id').getValue());
	},
	setMedSpecOms: function() {
		if ( getRegionNick().inlist([ 'buryatiya', 'ekb', 'kareliya', 'penza', 'pskov', 'adygeya' ]) ) {
			return false;
		}

		var win = this;
		var base_form = this.findById('EUDD13EW_EvnUslugaDispDopEditForm').getForm();

		var MedSpecOms_id;

		var index = base_form.findField('MedSpecOms_id').getStore().findBy(function(rec) {
			return (rec.get('MedSpecOms_id') == base_form.findField('MedSpecOms_id').getValue());
		});

		if (base_form.findField('MedSpecOms_id').getStore().getCount() == 1) {
			MedSpecOms_id = base_form.findField('MedSpecOms_id').getStore().getAt(0).get('MedSpecOms_id');
			base_form.findField('MedSpecOms_id').setValue(MedSpecOms_id);
		} else if (base_form.findField('MedSpecOms_id').getStore().getCount() > 1) {
			if ( index >= 0 ) {
				MedSpecOms_id = base_form.findField('MedSpecOms_id').getStore().getAt(index).get('MedSpecOms_id');
			} else if (getRegionNick() != 'astra') {
				MedSpecOms_id = base_form.findField('MedSpecOms_id').getStore().getAt(0).get('MedSpecOms_id');
			}

			if ( !Ext.isEmpty(MedSpecOms_id) ) {
				base_form.findField('MedSpecOms_id').setValue(MedSpecOms_id);
			}
			else {
				base_form.findField('MedSpecOms_id').clearValue();
			}
		}

		base_form.findField('MedSpecOms_id').fireEvent('change', base_form.findField('MedSpecOms_id'), base_form.findField('MedSpecOms_id').getValue());
	},
	setCytoLpuSectionProfile: function() {
		var win = this;
		var base_form = this.findById('EUDD13EW_EvnUslugaDispDopEditForm').getForm();

		var CytoLpuSectionProfile_id;

		var index = base_form.findField('CytoLpuSectionProfile_id').getStore().findBy(function(rec) {
			return (rec.get('LpuSectionProfile_id') == base_form.findField('CytoLpuSectionProfile_id').getValue());
		});

		if (base_form.findField('CytoLpuSectionProfile_id').getStore().getCount() == 1) {
			CytoLpuSectionProfile_id = base_form.findField('CytoLpuSectionProfile_id').getStore().getAt(0).get('LpuSectionProfile_id');
			base_form.findField('CytoLpuSectionProfile_id').setValue(CytoLpuSectionProfile_id);
		} else if (base_form.findField('CytoLpuSectionProfile_id').getStore().getCount() > 1) {
			if ( index >= 0 ) {
				CytoLpuSectionProfile_id = base_form.findField('CytoLpuSectionProfile_id').getStore().getAt(index).get('LpuSectionProfile_id');
			} else if (getRegionNick() != 'astra') {
				CytoLpuSectionProfile_id = base_form.findField('CytoLpuSectionProfile_id').getStore().getAt(0).get('LpuSectionProfile_id');
			}

			if ( !Ext.isEmpty(CytoLpuSectionProfile_id) ) {
				base_form.findField('CytoLpuSectionProfile_id').setValue(CytoLpuSectionProfile_id);
			}
			else {
				base_form.findField('CytoLpuSectionProfile_id').clearValue();
			}
		}

		base_form.findField('CytoLpuSectionProfile_id').fireEvent('change', base_form.findField('CytoLpuSectionProfile_id'), base_form.findField('CytoLpuSectionProfile_id').getValue());
	},
	setCytoMedSpecOms: function() {
		var win = this;
		var base_form = this.findById('EUDD13EW_EvnUslugaDispDopEditForm').getForm();

		var CytoMedSpecOms_id;

		var index = base_form.findField('CytoMedSpecOms_id').getStore().findBy(function(rec) {
			return (rec.get('MedSpecOms_id') == base_form.findField('CytoMedSpecOms_id').getValue());
		});

		if (base_form.findField('CytoMedSpecOms_id').getStore().getCount() == 1) {
			CytoMedSpecOms_id = base_form.findField('CytoMedSpecOms_id').getStore().getAt(0).get('MedSpecOms_id');
			base_form.findField('CytoMedSpecOms_id').setValue(CytoMedSpecOms_id);
		} else if (base_form.findField('CytoMedSpecOms_id').getStore().getCount() > 1) {
			if ( index >= 0 ) {
				CytoMedSpecOms_id = base_form.findField('CytoMedSpecOms_id').getStore().getAt(index).get('MedSpecOms_id');
			} else if (getRegionNick() != 'astra') {
				CytoMedSpecOms_id = base_form.findField('CytoMedSpecOms_id').getStore().getAt(0).get('MedSpecOms_id');
			}

			if ( !Ext.isEmpty(CytoMedSpecOms_id) ) {
				base_form.findField('CytoMedSpecOms_id').setValue(CytoMedSpecOms_id);
			}
			else {
				base_form.findField('CytoMedSpecOms_id').clearValue();
			}
		}

		base_form.findField('CytoMedSpecOms_id').fireEvent('change', base_form.findField('CytoMedSpecOms_id'), base_form.findField('CytoMedSpecOms_id').getValue());
	},
	setLpuSectionAndMedStaffFactFilter: function() {
		var win = this;
		var base_form = this.findById('EUDD13EW_EvnUslugaDispDopEditForm').getForm();

		// Учитываем дату и место выполнения
		var EvnUslugaDispDop_didDate = base_form.findField('EvnUslugaDispDop_didDate').getValue();
		var ExaminationPlace_id = base_form.findField('ExaminationPlace_id').getValue();
		var MedStaffFact_id = base_form.findField('MedStaffFact_id').getValue();

		if (getRegionNick() == 'perm') {
			base_form.findField('LpuSection_id').disableLinkedElements();
			base_form.findField('MedStaffFact_id').disableParentElement();
		}

		if ( !Ext.isEmpty(ExaminationPlace_id) && ExaminationPlace_id == 3 ) {
			if(getRegionNick()== 'buryatiya' && base_form.findField('Lpu_uid').getValue()==getGlobalOptions().lpu_id){
				base_form.findField('LpuSection_id').clearValue();
				base_form.findField('MedStaffFact_id').clearValue();
				base_form.findField('Lpu_uid').clearValue();
			}
			// показать поля МО, Профиль, Специальность
			base_form.findField('Lpu_uid').showContainer();
			base_form.findField('LpuSectionProfile_id').setContainerVisible(!getRegionNick().inlist([ 'buryatiya', 'ekb', 'kareliya', 'penza', 'pskov', 'adygeya' ]) || win.DispClass_id.inlist([19,26]));
			base_form.findField('MedSpecOms_id').setContainerVisible(!getRegionNick().inlist([ 'buryatiya', 'ekb', 'kareliya', 'penza', 'pskov', 'adygeya' ]) || win.DispClass_id.inlist([19,26]));

			base_form.findField('Lpu_uid').setAllowBlank(getRegionNick().inlist([ 'buryatiya', 'kareliya', 'penza', 'pskov', 'ufa', 'ekb' ]) && !win.DispClass_id.inlist([19,26]));
			base_form.findField('LpuSectionProfile_id').setAllowBlank(getRegionNick().inlist([ 'buryatiya', 'kareliya', 'penza', 'pskov', 'ufa', 'ekb', 'adygeya' ]) && !win.DispClass_id.inlist([19,26]));
			base_form.findField('MedSpecOms_id').setAllowBlank(getRegionNick().inlist([ 'buryatiya', 'kareliya', 'penza', 'pskov', 'ufa', 'ekb', 'adygeya' ]) && !win.DispClass_id.inlist([19,26]));

			if(getRegionNick() != 'buryatiya'){
				base_form.findField('LpuSection_id').setAllowBlank(true);
				base_form.findField('MedStaffFact_id').setAllowBlank(true);
			}
			if ( getRegionNick().inlist([ 'buryatiya', 'ekb', 'kareliya', 'penza', 'pskov', 'adygeya' ]) && !win.DispClass_id.inlist([19,26]) ) {
				if ( win.lastLpu_uid1 != base_form.findField('Lpu_uid').getValue() ) {
					base_form.findField('LpuSection_id').getStore().removeAll();
					base_form.findField('LpuSection_id').clearValue();
				}
			}
			else if (
				Ext.isEmpty(base_form.findField('LpuSectionProfile_id').getValue())
				|| Ext.isEmpty(base_form.findField('Lpu_uid').getValue())
			) {
				base_form.findField('LpuSection_id').getStore().removeAll();
				base_form.findField('LpuSection_id').clearValue();
				win.lastLpuSectionProfile_id = base_form.findField('LpuSectionProfile_id').getValue();
				win.lastLpu_uid1 = base_form.findField('Lpu_uid').getValue();
			}

			if ( getRegionNick().inlist([ 'buryatiya', 'ekb', 'kareliya', 'penza', 'pskov', 'adygeya' ]) && !win.DispClass_id.inlist([19,26]) ) {
				if ( win.lastLpu_uid2 != base_form.findField('Lpu_uid').getValue() ) {
					base_form.findField('MedStaffFact_id').getStore().removeAll();
					base_form.findField('MedStaffFact_id').clearValue();
				}
			}
			else if (
				Ext.isEmpty(base_form.findField('MedSpecOms_id').getValue())
				|| Ext.isEmpty(base_form.findField('Lpu_uid').getValue())
			) {
				base_form.findField('MedStaffFact_id').getStore().removeAll();
				base_form.findField('MedStaffFact_id').clearValue();
				win.lastMedSpecOms_id = base_form.findField('MedSpecOms_id').getValue();
				win.lastLpu_uid2 = base_form.findField('Lpu_uid').getValue();
			}

			var didDate = (!Ext.isEmpty(EvnUslugaDispDop_didDate) ? Ext.util.Format.date(EvnUslugaDispDop_didDate, 'd.m.Y') : null);

			if (
				(
					!getRegionNick().inlist([ 'astra', 'krym', 'perm', 'ufa' ])
					&& !Ext.isEmpty(base_form.findField('Lpu_uid').getValue())
					&& win.lastLpu_uid1 != base_form.findField('Lpu_uid').getValue()
				)
				|| (
					getRegionNick().inlist([ 'astra', 'krym', 'perm', 'ufa' ])
					&& !Ext.isEmpty(base_form.findField('LpuSectionProfile_id').getValue())
					&& !Ext.isEmpty(base_form.findField('Lpu_uid').getValue())
					&& (
						base_form.findField('Lpu_uid').getValue() != win.lastLpu_uid1
						|| base_form.findField('LpuSectionProfile_id').getValue() != win.lastLpuSectionProfile_id
					)
				)
				|| didDate != win.lastDidDate1
				||
				(
					getRegionNick().inlist([ 'buryatiya' ])
					&& Ext.isEmpty(base_form.findField('Lpu_uid').getValue())
				)
			) {
				win.lastLpuSectionProfile_id = base_form.findField('LpuSectionProfile_id').getValue();
				win.lastLpu_uid1 = base_form.findField('Lpu_uid').getValue();
				win.lastDidDate1 = didDate;

				base_form.findField('LpuSection_id').getStore().load({
					callback: function() {
						var store = base_form.findField('LpuSection_id').getStore();
						var ucid = null;
						var index = store.findBy(function (rec) {
							return (rec.get('LpuSection_id') == base_form.findField('LpuSection_id').getValue());
						});

						if ( !(getRegionNick().inlist(['buryatiya']) && Ext.isEmpty(base_form.findField('Lpu_uid').getValue())) ) {
							if (index >= 0) {
								ucid = store.getAt(index).get('LpuSection_id');
							} else if (store.getCount() && win.loadFirstMedPersonal) {
								ucid = store.getAt(0).get('LpuSection_id');
							}

							if (ucid) {
								base_form.findField('LpuSection_id').setValue(ucid);
							} else {
								base_form.findField('LpuSection_id').clearValue();
							}
						}
					}.createDelegate(this),
					params: {
						date: didDate,
						LpuSectionProfile_id: base_form.findField('LpuSectionProfile_id').getValue(),
						Lpu_id: base_form.findField('Lpu_uid').getValue(),
						mode: (getRegionNick().inlist([ 'krym', 'perm' ]))?'combo':'dispcontractcombo'
					}
				});
			}
			if (
				(
					!getRegionNick().inlist([ 'krym', 'perm' ])
					&& !Ext.isEmpty(base_form.findField('Lpu_uid').getValue())
					&& (
						base_form.findField('Lpu_uid').getValue() != win.lastLpu_uid2
						|| base_form.findField('MedSpecOms_id').getValue() != win.lastMedSpecOms_id
					)
				)
				|| (
					getRegionNick().inlist([ 'krym', 'perm' ])
					&& !Ext.isEmpty(base_form.findField('MedSpecOms_id').getValue())
					&& !Ext.isEmpty(base_form.findField('Lpu_uid').getValue())
					&& (
						base_form.findField('Lpu_uid').getValue() != win.lastLpu_uid2
						|| base_form.findField('MedSpecOms_id').getValue() != win.lastMedSpecOms_id
					)
				)
				|| didDate != win.lastDidDate2
				||
				(
					getRegionNick().inlist([ 'buryatiya' ])
					&& Ext.isEmpty(base_form.findField('Lpu_uid').getValue())
				)
			) {
				win.lastMedSpecOms_id = base_form.findField('MedSpecOms_id').getValue();
				win.lastLpu_uid2 = base_form.findField('Lpu_uid').getValue();
				win.lastDidDate2 = didDate;

				base_form.findField('MedStaffFact_id').getStore().load({
					callback: function() {
						var store = base_form.findField('MedStaffFact_id').getStore();
						var ucid = null;
						var index =store.findBy(function(rec) {
							return (rec.get('MedStaffFact_id') == MedStaffFact_id);
						});
						if ( index < 0 ) {
							index = store.findBy(function(rec) {
								return (rec.get('MedPersonal_id') == base_form.findField('MedPersonal_id').getValue());
							});
						}

						if (
							!(getRegionNick().inlist(['buryatiya']) && Ext.isEmpty(base_form.findField('Lpu_uid').getValue()))
						) {
							if (index >= 0) {
								ucid = store.getAt(index).get('MedStaffFact_id');
							} else if (store.getCount() && win.loadFirstMedPersonal) {
								ucid = store.getAt(0).get('MedStaffFact_id');
							}

							if (ucid) {
								base_form.findField('MedStaffFact_id').setValue(ucid);
								base_form.findField('LpuSection_id').setValue(base_form.findField('MedStaffFact_id').getFieldValue('LpuSection_id'));
								base_form.findField('LpuSection_id').fireEvent('change', base_form.findField('LpuSection_id'), base_form.findField('LpuSection_id').getValue());
							} else {
								base_form.findField('MedStaffFact_id').clearValue();
							}
						}
					}.createDelegate(this),
					params: {
						onDate: didDate,
						mode: (getRegionNick().inlist([ 'krym', 'perm' ]))?'combo':'dispcontractcombo',
						MedSpecOms_id: base_form.findField('MedSpecOms_id').getValue(),
						Lpu_id: base_form.findField('Lpu_uid').getValue()
					}
				});
			}
		} else {
			// скрыть поля МО, Профиль, Специальность
			base_form.findField('Lpu_uid').clearValue();
			base_form.findField('Lpu_uid').setAllowBlank(true);
			base_form.findField('Lpu_uid').hideContainer();
			base_form.findField('LpuSectionProfile_id').clearValue();
			base_form.findField('LpuSectionProfile_id').setAllowBlank(true);
			base_form.findField('LpuSectionProfile_id').hideContainer();
			base_form.findField('MedSpecOms_id').clearValue();
			base_form.findField('MedSpecOms_id').setAllowBlank(true);
			base_form.findField('MedSpecOms_id').hideContainer();

			// Убрал условие "не Бурятия", ибо https://redmine.swan.perm.ru/issues/51414
			base_form.findField('LpuSection_id').enableLinkedElements();
			base_form.findField('MedStaffFact_id').enableParentElement();

			if (Ext.isEmpty(base_form.findField('EvnUslugaDispDop_didDate').getValue()) && !getRegionNick()== 'buryatiya') {
					base_form.findField('LpuSection_id').setAllowBlank(true);
					base_form.findField('MedStaffFact_id').setAllowBlank(true);
			} else {
				var UslugaCategory_SysNick = '';
				var UslugaComplex_id = base_form.findField('UslugaComplex_id').getValue();

				if ( !Ext.isEmpty(UslugaComplex_id) ) {
					var index = base_form.findField('UslugaComplex_id').getStore().findBy(function(rec) {
						return (rec.get('UslugaComplex_id') == UslugaComplex_id);
					});

					if ( index >= 0 ) {
						UslugaCategory_SysNick = base_form.findField('UslugaComplex_id').getStore().getAt(index).get('UslugaCategory_SysNick');
					}
				}
				// https://redmine.swan.perm.ru/issues/20625
				if ( getRegionNick().inlist([ 'pskov', 'ufa']) && UslugaCategory_SysNick != 'lpusection' ) {
						base_form.findField('LpuSection_id').setAllowBlank(true);
						base_form.findField('MedStaffFact_id').setAllowBlank(true);
				}
				else {
					base_form.findField('LpuSection_id').setAllowBlank(false);
					base_form.findField('MedStaffFact_id').setAllowBlank(false);
				}
			}

			var index;
			var params = {
				allowLowLevel: 'yes'
				,allowDuplacateMSF: true
			}

			if (!getRegionNick().inlist(['ekb'])) {
				params.isNotStac = true;
			}

			if ( !Ext.isEmpty(EvnUslugaDispDop_didDate) ) {
				params.onDate = Ext.util.Format.date(EvnUslugaDispDop_didDate, 'd.m.Y');
			}

			if ( getRegionNick().inlist(['perm']) ) {
				if (!Ext.isEmpty(this.OrpDispSpec_Code)) {
					switch (this.OrpDispSpec_Code)
					{
						case 1:
							params.arrayLpuSectionProfile = ['900', '0900', '1003', '0905', '1011', '57', '68', '151'];
							switch (this.type)
							{
								case 'DispTeenInspectionPeriod':
									params.arrayLpuSectionProfile = ['900', '0900', '1003', '922', '923', '924', '0905', '1011', '48', '57', '68', '151'];
									break;

								case 'DispTeenInspectionProf':
									params.arrayLpuSectionProfile = ['900', '0900', '1003', '918', '0905', '1011', '57', '68', '151'];
									break;

								case 'DispTeenInspectionPred':
									params.arrayLpuSectionProfile = ['900', '0900', '1003', '919', '920', '921', '929', '930', '931', '939', '940', '941', '0905', '1011', '48', '57', '68', '151'];
									break;
							}
							break;
						case 2:
							params.arrayLpuSectionProfile = ['2800', '53'];
							break;
						case 3:
							params.arrayLpuSectionProfile = ['2700', '65'];
							break;
						case 4:
							params.arrayLpuSectionProfile = ['2300', '2350', '20', '112'];
							break;
						case 5:
							params.arrayLpuSectionProfile = ['2600', '162'];
							break;
						case 6:
							params.arrayLpuSectionProfile = ['2517', '2519', '136'];
							break;
						case 7:
							params.arrayLpuSectionProfile = ['1830', '1800', '85', '86'];
							if (this.type.inlist(['DispTeenInspectionPeriod', 'DispTeenInspectionProf', 'DispTeenInspectionPred'])) {
								params.arrayLpuSectionProfile = ['1830', '1800', '1802', '1810', '85', '86', '89', '87', '171'];
							}
							break;
						case 8:
							params.arrayLpuSectionProfile = ['1450', '100'];
							if (this.type.inlist(['DispTeenInspectionPeriod', 'DispTeenInspectionProf', 'DispTeenInspectionPred'])) {
								params.arrayLpuSectionProfile = ['1450', '2300', '2350', '20', '100', '112'];
							}
							break;
						case 9:
						case 12:
						case 13:
							params.arrayLpuSectionProfile = ['3710', '72'];
							break;
						case 10:
							params.arrayLpuSectionProfile = ['1530', '1500', '2350', '19', '20', '108'];
							if (this.type.inlist(['DispTeenInspectionPeriod', 'DispTeenInspectionProf', 'DispTeenInspectionPred'])) {
								params.arrayLpuSectionProfile = ['1530', '1500', '2300', '2350', '19', '20', '108', '112'];
							}
							break;
						case 11:
							params.arrayLpuSectionProfile = ['0530', '0510', '21', '122'];
							break;
					}
				} else if (this.SurveyType_Code == 27) {
					// фильтр по профилю отделения 0900, 1003
					params.arrayLpuSectionProfile = ['0900', '1003', '57', '68'];
				}
			} else if (getRegionNick().inlist(['pskov'])) {
				if (!Ext.isEmpty(this.OrpDispSpec_Code)) {
					switch (this.OrpDispSpec_Code)
					{
						case 1: // Педиатрия
							params.arrayLpuSectionProfile = ['68'];
							break;
						case 2: // Неврология
							params.arrayLpuSectionProfile = ['53'];
							break;
						case 3: // Офтальмология
							params.arrayLpuSectionProfile = ['65'];
							break;
						case 4: // Детская хирургия
							params.arrayLpuSectionProfile = ['112','20'];
							break;
						case 5: // Отоларингология
							params.arrayLpuSectionProfile = ['162'];
							break;
						case 6: // Гинекология
							params.arrayLpuSectionProfile = ['136'];
							break;
						case 7: // Стоматология детская
							params.arrayLpuSectionProfile = ['85','86','63'];
							break;
						case 8: // Ортопедия-травматология
							params.arrayLpuSectionProfile = ['100','112','20'];
							break;
						case 10: // Детская урология-андрология
							params.arrayLpuSectionProfile = ['19','108','112','20'];
							break;
						case 11: // Детская эндокринология
							params.arrayLpuSectionProfile = ['122','21'];
							break;
						case 9: // Психиатрия
						case 12: // Детская психиатрия
						case 13: // Подростковая психиатрия
							params.arrayLpuSectionProfile = ['72','74'];
							break;
					}
				}
			}

			if ( getRegionNick().inlist(['ekb']) ) {
				if (!Ext.isEmpty(this.MedSpecOmsList)) {
					params.MedSpecOmsList = this.MedSpecOmsList;
				}
			}

			var
				LpuSection_id,
				MedPersonal_id,
				MedStaffFact_id;

			// Сохраняем текущие значения
			if ( typeof this.loadedParams == 'object' && (!Ext.isEmpty(this.loadedParams.LpuSection_id) || !Ext.isEmpty(this.loadedParams.MedStaffFact_id) || !Ext.isEmpty(this.loadedParams.MedPersonal_id)) ) {
				LpuSection_id = this.loadedParams.LpuSection_id || null;
				MedPersonal_id = this.loadedParams.MedPersonal_id || null;
				MedStaffFact_id = this.loadedParams.MedStaffFact_id || null;
				this.loadedParams = new Object();
			}
			else {
				LpuSection_id = base_form.findField('LpuSection_id').getValue();
				MedStaffFact_id = base_form.findField('MedStaffFact_id').getValue();
			}

			if (Ext.isEmpty(LpuSection_id)) {
				LpuSection_id = (sw.Promed.MedStaffFactByUser.last && sw.Promed.MedStaffFactByUser.last.LpuSection_id) || null;
			}

			if (Ext.isEmpty(MedPersonal_id)) {
				MedPersonal_id = (sw.Promed.MedStaffFactByUser.last && sw.Promed.MedStaffFactByUser.last.MedPersonal_id) || null;
			}

			if (Ext.isEmpty(MedStaffFact_id)) {
				MedStaffFact_id = (sw.Promed.MedStaffFactByUser.last && sw.Promed.MedStaffFactByUser.last.MedStaffFact_id) || null;
			}

			base_form.findField('LpuSection_id').clearValue();
			base_form.findField('MedStaffFact_id').clearValue();


			if (UslugaComplex_id == null)
			{
				var UslugaComplex_id = base_form.findField('UslugaComplex_id').getValue();
			}

			if (getRegionNick() === 'pskov' && win.DispClass_id.inlist([1, 2, 5, 10]))
			{
				if (UslugaComplex_id && EvnUslugaDispDop_didDate)
				{
					params.UslugaComplex_MedSpecOms = {
						UslugaComplex_id: UslugaComplex_id,
						didDate: Ext.util.Format.date(EvnUslugaDispDop_didDate, 'd.m.Y')
					};
				}
			}

			setLpuSectionGlobalStoreFilter(params);
			setMedStaffFactGlobalStoreFilter(params);

			base_form.findField('LpuSection_id').getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));
			base_form.findField('MedStaffFact_id').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));

			if ( !Ext.isEmpty(LpuSection_id) ) {
				if (getRegionNick().inlist([ 'penza', 'pskov' ])) {
					base_form.findField('MedStaffFact_id').getStore().filterBy(function (record) {
						if (record.get('LpuSection_id') == LpuSection_id) {
							return true;
						}
					});
				}
				index = base_form.findField('LpuSection_id').getStore().findBy(function(rec) {
					return (rec.get('LpuSection_id') == LpuSection_id);
				});

				if ( index >= 0 ) {
					base_form.findField('LpuSection_id').setValue(LpuSection_id);
				}
			}

			if ( !Ext.isEmpty(MedStaffFact_id) ) {
				index = base_form.findField('MedStaffFact_id').getStore().findBy(function(rec) {
					return (rec.get('MedStaffFact_id') == MedStaffFact_id);
				});

				if ( index >= 0 ) {
					base_form.findField('MedStaffFact_id').setValue(MedStaffFact_id);
				}
			}
			else if ( !Ext.isEmpty(MedPersonal_id) ) {
				index = base_form.findField('MedStaffFact_id').getStore().findBy(function(rec) {
					return (rec.get('LpuSection_id') == LpuSection_id && rec.get('MedPersonal_id') == MedPersonal_id);
				});

				if ( index >= 0 ) {
					base_form.findField('MedStaffFact_id').setValue(base_form.findField('MedStaffFact_id').getStore().getAt(index).get('MedStaffFact_id'));
				}
			}
		}
	},
	setCytoLpuSectionAndCytoMedStaffFactFilter: function() {
		var win = this;
		var base_form = this.findById('EUDD13EW_EvnUslugaDispDopEditForm').getForm();

		// если цитологической услуги нет, то и загружать ничего не надо.
		if (!this.cytoUslugaEnabled()) {
			base_form.findField('CytoLpuSection_id').setAllowBlank(true);
			base_form.findField('CytoMedStaffFact_id').setAllowBlank(true);
			return false;
		}

		// Учитываем дату и место выполнения
		var CytoEvnUsluga_setDate = base_form.findField('CytoEvnUsluga_setDate').getValue();
		var CytoExaminationPlace_id = base_form.findField('CytoExaminationPlace_id').getValue();

		if (getRegionNick() == 'perm') {
			base_form.findField('CytoLpuSection_id').disableLinkedElements();
			base_form.findField('CytoMedStaffFact_id').disableParentElement();
		}

		if ( !Ext.isEmpty(CytoExaminationPlace_id) && CytoExaminationPlace_id == 3 ) {
			// показать поля МО, Профиль, Специальность
			base_form.findField('CytoLpu_id').showContainer();
			base_form.findField('CytoLpuSectionProfile_id').setContainerVisible(!getRegionNick().inlist([ 'buryatiya', 'ekb', 'kareliya', 'penza', 'pskov', 'adygeya' ]));
			base_form.findField('CytoMedSpecOms_id').setContainerVisible(!getRegionNick().inlist([ 'buryatiya', 'ekb', 'kareliya', 'penza', 'pskov', 'adygeya' ]));

			base_form.findField('CytoLpu_id').setAllowBlank(getRegionNick().inlist([ 'buryatiya', 'kareliya', 'penza', 'pskov', 'ufa', 'ekb' ]));
			base_form.findField('CytoLpuSectionProfile_id').setAllowBlank(getRegionNick().inlist([ 'buryatiya', 'kareliya', 'penza', 'pskov', 'ufa', 'ekb', 'adygeya' ]));
			base_form.findField('CytoMedSpecOms_id').setAllowBlank(getRegionNick().inlist([ 'buryatiya', 'kareliya', 'penza', 'pskov', 'ufa', 'ekb', 'adygeya' ]));

			base_form.findField('CytoLpuSection_id').setAllowBlank(true);
			base_form.findField('CytoMedStaffFact_id').setAllowBlank(true);
			/*
			if (getRegionNick() === 'ekb')
			{
				base_form.findField('CytoLpuSection_id').disable();
				base_form.findField('CytoMedStaffFact_id').disable();
			}
			*/
			if ( getRegionNick().inlist([ 'buryatiya', 'ekb', 'kareliya', 'penza', 'pskov', 'adygeya' ]) ) {
				if ( win.lastCytoLpu_id1 != base_form.findField('CytoLpu_id').getValue() ) {
					base_form.findField('CytoLpuSection_id').getStore().removeAll();
					base_form.findField('CytoLpuSection_id').clearValue();
				}
			}
			else if (
				Ext.isEmpty(base_form.findField('CytoLpuSectionProfile_id').getValue())
				|| Ext.isEmpty(base_form.findField('CytoLpu_id').getValue())
			) {
				base_form.findField('CytoLpuSection_id').getStore().removeAll();
				base_form.findField('CytoLpuSection_id').clearValue();
				win.lastCytoLpuSectionProfile_id = base_form.findField('CytoLpuSectionProfile_id').getValue();
				win.lastCytoLpu_id1 = base_form.findField('CytoLpu_id').getValue();
			}

			if ( getRegionNick().inlist([ 'buryatiya', 'ekb', 'kareliya', 'penza', 'pskov' ]) ) {
				if ( win.lastCytoLpu_id2 != base_form.findField('CytoLpu_id').getValue() ) {
					base_form.findField('CytoMedStaffFact_id').getStore().removeAll();
					base_form.findField('CytoMedStaffFact_id').clearValue();
				}
			}
			else if (
				Ext.isEmpty(base_form.findField('CytoMedSpecOms_id').getValue())
				|| Ext.isEmpty(base_form.findField('CytoLpu_id').getValue())
			) {
				base_form.findField('CytoMedStaffFact_id').getStore().removeAll();
				base_form.findField('CytoMedStaffFact_id').clearValue();
				win.lastCytoMedSpecOms_id = base_form.findField('CytoMedSpecOms_id').getValue();
				win.lastCytoLpu_id2 = base_form.findField('CytoLpu_id').getValue();
			}

			if (
				(
					!getRegionNick().inlist([ 'perm' ])
					&& !Ext.isEmpty(base_form.findField('CytoLpu_id').getValue())
					&& win.lastCytoLpu_id1 != base_form.findField('CytoLpu_id').getValue()
				)
				|| (
					getRegionNick().inlist([ 'perm' ])
					&& !Ext.isEmpty(base_form.findField('CytoLpuSectionProfile_id').getValue())
					&& !Ext.isEmpty(base_form.findField('CytoLpu_id').getValue())
					&& (
						base_form.findField('CytoLpu_id').getValue() != win.lastCytoLpu_id1
						|| base_form.findField('CytoLpuSectionProfile_id').getValue() != win.lastCytoLpuSectionProfile_id
					)
				)
			) {
				win.lastCytoLpuSectionProfile_id = base_form.findField('CytoLpuSectionProfile_id').getValue();
				win.lastCytoLpu_id1 = base_form.findField('CytoLpu_id').getValue();

				base_form.findField('CytoLpuSection_id').getStore().load({
					callback: function() {
						var index = base_form.findField('CytoLpuSection_id').getStore().findBy(function(rec) {
							return (rec.get('LpuSection_id') == base_form.findField('CytoLpuSection_id').getValue());
						});

						var ucid = null;

						if (base_form.findField('CytoLpuSection_id').getStore().getCount() == 1) {
							if (win.loadFirstMedPersonal) {
								ucid = base_form.findField('CytoLpuSection_id').getStore().getAt(0).get('LpuSection_id');
								base_form.findField('CytoLpuSection_id').setValue(ucid);
							}
						} else if (base_form.findField('CytoLpuSection_id').getStore().getCount() > 1) {
							if ( index >= 0 ) {
								ucid = base_form.findField('CytoLpuSection_id').getStore().getAt(index).get('LpuSection_id');
							} else {
								if (win.loadFirstMedPersonal) {
									ucid = base_form.findField('CytoLpuSection_id').getStore().getAt(0).get('LpuSection_id');
								}
							}
							base_form.findField('CytoLpuSection_id').setValue(ucid);
						}
						else {
							base_form.findField('CytoLpuSection_id').clearValue();
						}
					}.createDelegate(this),
					params: {
						CytoLpuSectionProfile_id: base_form.findField('CytoLpuSectionProfile_id').getValue(),
						Lpu_id: base_form.findField('CytoLpu_id').getValue(),
						mode: (getRegionNick() == 'perm')?'combo':'dispcontractcombo'
					}
				});
			}

			if (
				(
					!getRegionNick().inlist([ 'perm' ])
					&& !Ext.isEmpty(base_form.findField('CytoLpu_id').getValue())
					&& win.lastCytoLpu_id2 != base_form.findField('CytoLpu_id').getValue()
				)
				|| (
					getRegionNick().inlist([ 'perm' ])
					&& !Ext.isEmpty(base_form.findField('CytoMedSpecOms_id').getValue())
					&& !Ext.isEmpty(base_form.findField('CytoLpu_id').getValue())
					&& (
						base_form.findField('CytoLpu_id').getValue() != win.lastCytoLpu_id2
						|| base_form.findField('CytoMedSpecOms_id').getValue() != win.lastCytoMedSpecOms_id
					)
				)
			) {
				win.lastCytoMedSpecOms_id = base_form.findField('CytoMedSpecOms_id').getValue();
				win.lastCytoLpu_id2 = base_form.findField('CytoLpu_id').getValue();

				base_form.findField('CytoMedStaffFact_id').getStore().load({
					callback: function() {
						var index = base_form.findField('CytoMedStaffFact_id').getStore().findBy(function(rec) {
							return (rec.get('MedPersonal_id') == base_form.findField('CytoMedPersonal_id').getValue());
						});

						var ucid = null;

						if (base_form.findField('CytoMedStaffFact_id').getStore().getCount() == 1) {
							if (win.loadFirstMedPersonal) {
								ucid = base_form.findField('CytoMedStaffFact_id').getStore().getAt(0).get('MedStaffFact_id');
								base_form.findField('CytoMedStaffFact_id').setValue(ucid);
							}
						} else if (base_form.findField('CytoMedStaffFact_id').getStore().getCount() > 1) {
							if ( index >= 0 ) {
								ucid = base_form.findField('CytoMedStaffFact_id').getStore().getAt(index).get('MedStaffFact_id');
							} else {
								if (win.loadFirstMedPersonal) {
									ucid = base_form.findField('CytoMedStaffFact_id').getStore().getAt(0).get('MedStaffFact_id');
								}
							}
							base_form.findField('CytoMedStaffFact_id').setValue(ucid);
						} else {
							base_form.findField('CytoMedStaffFact_id').clearValue();
						}
					}.createDelegate(this),
					params: {
						mode: (getRegionNick() == 'perm')?'combo':'dispcontractcombo',
						CytoMedSpecOms_id: base_form.findField('CytoMedSpecOms_id').getValue(),
						Lpu_id: base_form.findField('CytoLpu_id').getValue()
					}
				});
			}
		} else {
			// скрыть поля МО, Профиль, Специальность
			base_form.findField('CytoLpu_id').clearValue();
			base_form.findField('CytoLpu_id').setAllowBlank(true);
			base_form.findField('CytoLpu_id').hideContainer();
			base_form.findField('CytoLpuSectionProfile_id').clearValue();
			base_form.findField('CytoLpuSectionProfile_id').setAllowBlank(true);
			base_form.findField('CytoLpuSectionProfile_id').hideContainer();
			base_form.findField('CytoMedSpecOms_id').clearValue();
			base_form.findField('CytoMedSpecOms_id').setAllowBlank(true);
			base_form.findField('CytoMedSpecOms_id').hideContainer();

			// Убрал условие "не Бурятия", ибо https://redmine.swan.perm.ru/issues/51414
			base_form.findField('CytoLpuSection_id').enableLinkedElements();
			base_form.findField('CytoMedStaffFact_id').enableParentElement();

			if (getRegionNick() === 'ekb')
			{
				base_form.findField('CytoLpuSection_id').enable();
				base_form.findField('CytoMedStaffFact_id').enable();
			}

			if (Ext.isEmpty(base_form.findField('CytoEvnUsluga_setDate').getValue())) {
				base_form.findField('CytoLpuSection_id').setAllowBlank(true);
				base_form.findField('CytoMedStaffFact_id').setAllowBlank(true);
			} else {
				var UslugaCategory_SysNick = '';
				var UslugaComplex_id = base_form.findField('UslugaComplex_id').getValue();

				if ( !Ext.isEmpty(UslugaComplex_id) ) {
					var index = base_form.findField('UslugaComplex_id').getStore().findBy(function(rec) {
						return (rec.get('UslugaComplex_id') == UslugaComplex_id);
					});

					if ( index >= 0 ) {
						UslugaCategory_SysNick = base_form.findField('UslugaComplex_id').getStore().getAt(index).get('UslugaCategory_SysNick');
					}
				}
				// https://redmine.swan.perm.ru/issues/20625
				if ( getRegionNick().inlist([ 'pskov', 'ufa', 'buryatiya' ]) && UslugaCategory_SysNick != 'lpusection' ) {
					base_form.findField('CytoLpuSection_id').setAllowBlank(true);
					base_form.findField('CytoMedStaffFact_id').setAllowBlank(true);
				}
				else {
					base_form.findField('CytoLpuSection_id').setAllowBlank(false);
					base_form.findField('CytoMedStaffFact_id').setAllowBlank(false);
				}
			}

			var index;
			var params = {
				allowLowLevel: 'yes'
				,allowDuplacateMSF: true
			}

			if (!getRegionNick().inlist(['ekb'])) {
				params.isNotStac = true;
			}

			if ( !Ext.isEmpty(CytoEvnUsluga_setDate) ) {
				params.onDate = Ext.util.Format.date(CytoEvnUsluga_setDate, 'd.m.Y');
			}

			if ( getRegionNick().inlist(['perm']) ) {
				if (!Ext.isEmpty(this.OrpDispSpec_Code)) {
					switch (this.OrpDispSpec_Code)
					{
						case 1:
							params.arrayLpuSectionProfile = ['900', '0900', '1003', '0905', '1011', '57', '68', '151'];
							switch (this.type)
							{
								case 'DispTeenInspectionPeriod':
									params.arrayLpuSectionProfile = ['900', '0900', '1003', '922', '923', '924', '0905', '1011', '48', '57', '68', '151'];
									break;

								case 'DispTeenInspectionProf':
									params.arrayLpuSectionProfile = ['900', '0900', '1003', '918', '0905', '1011', '57', '68', '151'];
									break;

								case 'DispTeenInspectionPred':
									params.arrayLpuSectionProfile = ['900', '0900', '1003', '919', '920', '921', '929', '930', '931', '939', '940', '941', '0905', '1011', '48', '57', '68', '151'];
									break;
							}
							break;
						case 2:
							params.arrayLpuSectionProfile = ['2800', '53'];
							break;
						case 3:
							params.arrayLpuSectionProfile = ['2700', '65'];
							break;
						case 4:
							params.arrayLpuSectionProfile = ['2300', '2350', '20', '112'];
							break;
						case 5:
							params.arrayLpuSectionProfile = ['2600', '162'];
							break;
						case 6:
							params.arrayLpuSectionProfile = ['2517', '2519', '136'];
							break;
						case 7:
							params.arrayLpuSectionProfile = ['1830', '1800', '85', '86'];
							if (this.type.inlist(['DispTeenInspectionPeriod', 'DispTeenInspectionProf', 'DispTeenInspectionPred'])) {
								params.arrayLpuSectionProfile = ['1830', '1800', '1802', '1810', '85', '86', '89', '87', '171'];
							}
							break;
						case 8:
							params.arrayLpuSectionProfile = ['1450', '100'];
							if (this.type.inlist(['DispTeenInspectionPeriod', 'DispTeenInspectionProf', 'DispTeenInspectionPred'])) {
								params.arrayLpuSectionProfile = ['1450', '2300', '2350', '20', '100', '112'];
							}
							break;
						case 9:
						case 12:
						case 13:
							params.arrayLpuSectionProfile = ['3710', '72'];
							break;
						case 10:
							params.arrayLpuSectionProfile = ['1530', '1500', '2350', '19', '20', '108'];
							if (this.type.inlist(['DispTeenInspectionPeriod', 'DispTeenInspectionProf', 'DispTeenInspectionPred'])) {
								params.arrayLpuSectionProfile = ['1530', '1500', '2300', '2350', '19', '20', '108', '112'];
							}
							break;
						case 11:
							params.arrayLpuSectionProfile = ['0530', '0510', '21', '122'];
							break;
					}
				} else if (this.SurveyType_Code == 27) {
					// фильтр по профилю отделения 0900, 1003
					params.arrayLpuSectionProfile = ['0900', '1003', '57', '68'];
				}
			} else if (getRegionNick().inlist(['pskov'])) {
				if (!Ext.isEmpty(this.OrpDispSpec_Code)) {
					switch (this.OrpDispSpec_Code)
					{
						case 1: // Педиатрия
							params.arrayLpuSectionProfile = ['68'];
							break;
						case 2: // Неврология
							params.arrayLpuSectionProfile = ['53'];
							break;
						case 3: // Офтальмология
							params.arrayLpuSectionProfile = ['65'];
							break;
						case 4: // Детская хирургия
							params.arrayLpuSectionProfile = ['112','20'];
							break;
						case 5: // Отоларингология
							params.arrayLpuSectionProfile = ['162'];
							break;
						case 6: // Гинекология
							params.arrayLpuSectionProfile = ['136'];
							break;
						case 7: // Стоматология детская
							params.arrayLpuSectionProfile = ['85','86','63'];
							break;
						case 8: // Ортопедия-травматология
							params.arrayLpuSectionProfile = ['100','112','20'];
							break;
						case 10: // Детская урология-андрология
							params.arrayLpuSectionProfile = ['19','108','112','20'];
							break;
						case 11: // Детская эндокринология
							params.arrayLpuSectionProfile = ['122','21'];
							break;
						case 9: // Психиатрия
						case 12: // Детская психиатрия
						case 13: // Подростковая психиатрия
							params.arrayLpuSectionProfile = ['72','74'];
							break;
					}
				}
			}

			if ( getRegionNick().inlist(['ekb']) ) {
				if (!Ext.isEmpty(this.CytoMedSpecOms_id)) {
					params.CytoMedSpecOms_id = this.CytoMedSpecOms_id;
				}
			}

			var
				CytoLpuSection_id,
				CytoMedPersonal_id,
				CytoMedStaffFact_id;

			// Сохраняем текущие значения
			if ( typeof this.loadedParams == 'object' && (!Ext.isEmpty(this.loadedParams.CytoLpuSection_id) || !Ext.isEmpty(this.loadedParams.CytoMedStaffFact_id) || !Ext.isEmpty(this.loadedParams.CytoMedPersonal_id)) ) {
				CytoLpuSection_id = this.loadedParams.CytoLpuSection_id || null;
				CytoMedPersonal_id = this.loadedParams.CytoMedPersonal_id || null;
				CytoMedStaffFact_id = this.loadedParams.CytoMedStaffFact_id || null;
				this.loadedParams = new Object();
			}
			else {
				CytoLpuSection_id = base_form.findField('CytoLpuSection_id').getValue();
				CytoMedStaffFact_id = base_form.findField('CytoMedStaffFact_id').getValue();
			}

			base_form.findField('CytoLpuSection_id').clearValue();
			base_form.findField('CytoMedStaffFact_id').clearValue();

			setLpuSectionGlobalStoreFilter(params);
			setMedStaffFactGlobalStoreFilter(params);

			base_form.findField('CytoLpuSection_id').getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));
			base_form.findField('CytoMedStaffFact_id').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));

			if ( !Ext.isEmpty(CytoLpuSection_id) ) {
				index = base_form.findField('CytoLpuSection_id').getStore().findBy(function(rec) {
					return (rec.get('LpuSection_id') == CytoLpuSection_id);
				});

				if ( index >= 0 ) {
					base_form.findField('CytoLpuSection_id').setValue(CytoLpuSection_id);
				}
			}

			if ( !Ext.isEmpty(CytoMedStaffFact_id) ) {
				index = base_form.findField('CytoMedStaffFact_id').getStore().findBy(function(rec) {
					return (rec.get('MedStaffFact_id') == CytoMedStaffFact_id);
				});

				if ( index >= 0 ) {
					base_form.findField('CytoMedStaffFact_id').setValue(CytoMedStaffFact_id);
				}
			}
			else if ( !Ext.isEmpty(CytoMedPersonal_id) ) {
				index = base_form.findField('CytoMedStaffFact_id').getStore().findBy(function(rec) {
					return (rec.get('LpuSection_id') == CytoLpuSection_id && rec.get('MedPersonal_id') == CytoMedPersonal_id);
				});

				if ( index >= 0 ) {
					base_form.findField('CytoMedStaffFact_id').setValue(base_form.findField('CytoMedStaffFact_id').getStore().getAt(index).get('MedStaffFact_id'));
				}
			}
		}
	},
	loadUslugaComplexCombo: function() {
		var win = this;
		var base_form = win.findById('EUDD13EW_EvnUslugaDispDopEditForm').getForm();

		// повторно грузить одно и то же не нужно
		var newUslugaComplexParams = Ext.util.JSON.encode(base_form.findField('UslugaComplex_id').getStore().baseParams);
		if (newUslugaComplexParams != win.lastUslugaComplexParams) {
			win.lastUslugaComplexParams = newUslugaComplexParams;
			var currentUslugaComplex_id = base_form.findField('UslugaComplex_id').getValue();
			win.getLoadMask(langs('Загрузка списка возможных услуг, пожалуйста подождите...')).show();
			base_form.findField('UslugaComplex_id').clearValue();
			base_form.findField('UslugaComplex_id').getStore().removeAll();
			base_form.findField('UslugaComplex_id').getStore().load({
				callback: function () {
					win.getLoadMask().hide();
					var ucid, index = base_form.findField('UslugaComplex_id').getStore().findBy(function (rec) {
						return (rec.get('UslugaComplex_id') == currentUslugaComplex_id);
					});

					if (base_form.findField('UslugaComplex_id').getStore().getCount() == 1) {
						ucid = base_form.findField('UslugaComplex_id').getStore().getAt(0).get('UslugaComplex_id');
						base_form.findField('UslugaComplex_id').setValue(ucid);
						base_form.findField('UslugaComplex_id').disable();
					}
					else if ( getRegionNick().inlist(['pskov', 'khak']) && win.DispClass_id == 10 && base_form.findField('UslugaComplex_id').getStore().getCount() > 1 ) {
						var isPay = false;

						if ( index >= 0 ) {
							ucid = base_form.findField('UslugaComplex_id').getStore().getAt(index).get('UslugaComplex_id');
							isPay = (base_form.findField('UslugaComplex_id').getStore().getAt(index).get('SurveyTypeLink_IsPay') == 2);
						}
						else {
							// по умолчанию подставляем эти услугу с SurveyTypeLink_IsPay = 2
							index = base_form.findField('UslugaComplex_id').getStore().findBy(function (rec) {
								return (rec.get('SurveyTypeLink_IsPay') == 2);
							});

							if ( index >= 0 ) {
								ucid = base_form.findField('UslugaComplex_id').getStore().getAt(index).get('UslugaComplex_id');
								isPay = true;
							}
						}

						base_form.findField('UslugaComplex_id').setValue(ucid);

						if ( win.action != 'view' && isPay == false ) {
							base_form.findField('UslugaComplex_id').enable();
						}
						else {
							base_form.findField('UslugaComplex_id').disable();
						}
					}
					else if (base_form.findField('UslugaComplex_id').getStore().getCount() > 1) {
						if (index >= 0) {
							ucid = base_form.findField('UslugaComplex_id').getStore().getAt(index).get('UslugaComplex_id');
						} else {
							// по умолчанию подставляем эти услуги
							index = base_form.findField('UslugaComplex_id').getStore().findBy(function (rec) {
								if (!Ext.isEmpty(win.DispClass_id) && win.DispClass_id.inlist([1, 2, 5])) {
									switch (win.SurveyType_Code) {
										case 14:
										case 112:
											if (getRegionNick() == 'ekb'){
												return (rec.get('UslugaComplex_Code') == 'A09.19.001.001');
											}
											break;
										case 16:
											if (getRegionNick() == 'ekb'){
												return (rec.get('UslugaComplex_Code') == 'A06.09.006.001');
											}
											else {
												return (rec.get('UslugaComplex_Code') == 'A06.09.006');
											}
											break;
										case 20:
											if (getRegionNick() == 'ekb'){
												return (rec.get('UslugaComplex_Code') == 'A11.20.025.999');
											}
											break;
										case 21:
											if (getRegionNick() == 'ekb'){
												return (rec.get('UslugaComplex_Code') == 'A06.20.004.998');
											}
											break;
										case 94:
											return (rec.get('UslugaComplex_Code') == 'A04.15.001');
											break;
										default:
											return (rec.get('UslugaComplex_Code') == 'B04.047.004');
											break;
									}
									return false;
								} else {
									switch (win.SurveyType_Code) {
										case 29:
											return (rec.get('UslugaComplex_Code') == 'B04.010.002');
											break;
										case 33:
										case 37:
											return (rec.get('UslugaComplex_Code') == 'B04.035.004');
											break;
										case 34:
											return (rec.get('UslugaComplex_Code') == 'B04.053.004');
											break;
										case 35:
											return (rec.get('UslugaComplex_Code') == 'B04.064.002');
											break;
										case 36:
											return (rec.get('UslugaComplex_Code') == 'B04.058.003');
											break;
										case 27:
											if (getRegionNick() == 'ekb') {
												return (rec.get('UslugaComplex_Code') == 'B04.031.002');
											} else {
												return (rec.get('UslugaComplex_Code') == 'B04.031.004');
											}
											break;
										// @task https://redmine.swan.perm.ru//issues/132286
										case 133:
											if (getRegionNick() == 'ekb'){
												return (rec.get('UslugaComplex_Code') == 'B04.053.004');
											}
											break;
										case 153:
											if (getRegionNick() == 'ekb'){
												return (rec.get('UslugaComplex_Code') == 'A06.09.006.001');
											}
											break;
									}
									return false;
								}
							});
							if (index >= 0) {
								ucid = base_form.findField('UslugaComplex_id').getStore().getAt(index).get('UslugaComplex_id');
							} else {
								ucid = base_form.findField('UslugaComplex_id').getStore().getAt(0).get('UslugaComplex_id');
							}
						}
						base_form.findField('UslugaComplex_id').setValue(ucid);
						if (win.action != 'view') {
							base_form.findField('UslugaComplex_id').enable();
						}
					}

					base_form.findField('UslugaComplex_id').fireEvent('change', base_form.findField('UslugaComplex_id'), base_form.findField('UslugaComplex_id').getValue());
				}
			});
		}
	},
	loadCytoUslugaComplexCombo: function() {
		var win = this;
		var base_form = win.findById('EUDD13EW_EvnUslugaDispDopEditForm').getForm();

		if (!win.cytoUslugaEnabled()) {
			return;
		}

		// повторно грузить одно и то же не нужно
		var newCytoUslugaComplexParams = Ext.util.JSON.encode(base_form.findField('CytoUslugaComplex_id').getStore().baseParams);
		if (newCytoUslugaComplexParams != win.lastCytoUslugaComplexParams) {
			var currentCytoUslugaComplex_id = base_form.findField('CytoUslugaComplex_id').getValue();
			win.getLoadMask(langs('Загрузка списка возможных услуг, пожалуйста подождите...')).show();
			base_form.findField('CytoUslugaComplex_id').clearValue();
			base_form.findField('CytoUslugaComplex_id').getStore().removeAll();
			win.lastCytoUslugaComplexParams = newCytoUslugaComplexParams;
			base_form.findField('CytoUslugaComplex_id').getStore().load({
				callback: function () {
					win.getLoadMask().hide();
					index = base_form.findField('CytoUslugaComplex_id').getStore().findBy(function (rec) {
						return (rec.get('UslugaComplex_id') == currentCytoUslugaComplex_id);
					});

					if (base_form.findField('CytoUslugaComplex_id').getStore().getCount() == 1) {
						ucid = base_form.findField('CytoUslugaComplex_id').getStore().getAt(0).get('UslugaComplex_id');
						base_form.findField('CytoUslugaComplex_id').setValue(ucid);
						base_form.findField('CytoUslugaComplex_id').disable();
					} else if (base_form.findField('CytoUslugaComplex_id').getStore().getCount() > 1) {
						if (index >= 0) {
							ucid = base_form.findField('CytoUslugaComplex_id').getStore().getAt(index).get('UslugaComplex_id');
						} else {
							ucid = base_form.findField('CytoUslugaComplex_id').getStore().getAt(0).get('UslugaComplex_id');
						}
						base_form.findField('CytoUslugaComplex_id').setValue(ucid);
						if (win.action != 'view') {
							base_form.findField('CytoUslugaComplex_id').enable();
						}
					}

					base_form.findField('CytoUslugaComplex_id').fireEvent('change', base_form.findField('CytoUslugaComplex_id'), base_form.findField('CytoUslugaComplex_id').getValue());
				}
			});
		}
	},
	setFormParamsFromDirection: function() {
		var win = this;
		var base_form = win.findById('EUDD13EW_EvnUslugaDispDopEditForm').getForm();
		var DopDispInfoConsent_id = base_form.findField('DopDispInfoConsent_id').getValue();
		if (!DopDispInfoConsent_id) return false;

		win.getLoadMask("Подождите, идет загрузка...").show();
		Ext.Ajax.request({
			url: '/?c=EvnPLDispMigrant&m=getUslugaResult',
			params: {
				DopDispInfoConsent_id: DopDispInfoConsent_id
			},
			success: function(response, action) {
				win.getLoadMask().hide();
				if (response.responseText != '') {
					var response_obj = Ext.util.JSON.decode(response.responseText);
					if (response_obj && response_obj.length) {

						var data = response_obj[0];
						var Diag_id = data.Diag_id;
						delete data.Diag_id;
						base_form.setValues(data);

						base_form.findField('EvnUslugaDispDop_didDate').disable();
						base_form.findField('EvnUslugaDispDop_didTime').disable();
						base_form.findField('EvnUslugaDispDop_disDate').disable();
						base_form.findField('EvnUslugaDispDop_disTime').disable();
						base_form.findField('ExaminationPlace_id').disable();
						base_form.findField('Lpu_uid').disable();
						base_form.findField('LpuSectionProfile_id').disable();
						base_form.findField('MedSpecOms_id').disable();
						base_form.findField('LpuSection_id').disableLinkedElements();
						base_form.findField('MedStaffFact_id').disableParentElement();

						if (!Ext.isEmpty(Diag_id)) {
							var diag_combo = base_form.findField('Diag_id');
							diag_combo.getStore().load({
								callback: function() {
									diag_combo.getStore().each(function(record) {
										if ( record.get('Diag_id') == Diag_id ) {
											diag_combo.setValue(Diag_id);
											win.diagIsChanged = false;
											diag_combo.fireEvent('select', diag_combo, record, 0);
											diag_combo.disable();
										}
									});
								},
								params: { where: "where DiagLevel_id = 4 and Diag_id = " + Diag_id }
							});
						}

						base_form.findField('LpuSection_id').getStore().load({
							callback: function() {
								index = base_form.findField('LpuSection_id').getStore().findBy(function(rec) {
									return (rec.get('LpuSection_id') == data.LpuSection_id);
								});

								if ( index >= 0 ) {
									base_form.findField('LpuSection_id').setValue(data.LpuSection_id);
									base_form.findField('LpuSection_id').fireEvent('change', base_form.findField('LpuSection_id'), base_form.findField('LpuSection_id').getStore().getAt(index).get('LpuSection_id'));
									base_form.findField('LpuSection_id').disable();
								}
							}.createDelegate(this),
							params: {
								LpuSection_id: data.LpuSection_id
							}
						});

						base_form.findField('MedStaffFact_id').getStore().load({
							callback: function() {
								index = base_form.findField('MedStaffFact_id').getStore().findBy(function(rec) {
									return (rec.get('MedStaffFact_id') == data.MedStaffFact_id);
								});

								if ( index >= 0 ) {
									base_form.findField('MedStaffFact_id').setValue(data.MedStaffFact_id);
									base_form.findField('MedStaffFact_id').fireEvent('change', base_form.findField('MedStaffFact_id'), base_form.findField('MedStaffFact_id').getValue());
									base_form.findField('MedStaffFact_id').disable();
								}
							}.createDelegate(this),
							params: {
								MedStaffFact_id: data.MedStaffFact_id
							}
						});
					}
				}
			}
		});
	},
	UpdateTumorField: function()
	{
		var base_form = this.findById('EUDD13EW_EvnUslugaDispDopEditForm').getForm();
		if(this.allowEditTumor)
		{
			var diag_code = base_form.findField('Diag_id').getFieldValue('Diag_Code');
			if(diag_code)
			{
				if((String(diag_code).slice(0,3) >= 'C00' && String(diag_code).slice(0,5) <= 'C80.9') || String(diag_code).slice(0,3) == 'C97')
				{
					base_form.findField('TumorStage_id').setContainerVisible(true);
					base_form.findField('TumorStage_id').setAllowBlank(false);
				}
				else
				{
					base_form.findField('TumorStage_id').setContainerVisible(false);
					base_form.findField('TumorStage_id').setAllowBlank(true);
					base_form.findField('TumorStage_id').setValue(null);
				}
			}
			else
			{
				base_form.findField('TumorStage_id').setContainerVisible(false);
				base_form.findField('TumorStage_id').setAllowBlank(true);
				base_form.findField('TumorStage_id').setValue(null);
			}
		}
	},
	show: function() {
		sw.Promed.swEvnUslugaDispDop13EditWindow.superclass.show.apply(this, arguments);
		this.formStatus = 'edit';
		var current_window = this;
		this.diagIsChanged = false;

		current_window.restore();
		current_window.center();

		var form = current_window.findById('EUDD13EW_EvnUslugaDispDopEditForm');
		var base_form = form.getForm();
		base_form.reset();
		current_window.EvnDiagDopDispGrid.removeAll({ clearAll: true });

		base_form.findField('ExaminationPlace_id').lastQuery = '';
		base_form.findField('ExaminationPlace_id').getStore().filterBy(function(rec) {
			return rec.get('ExaminationPlace_Code').toString().inlist([ '1', '3' ]);
		});

		base_form.findField('Diag_id').lastQuery = langs('Строка, которую никто не додумается вводить в качестве фильтра, ибо это бред искать диагноз по такой строке');

		current_window.callback = Ext.emptyFn;
		current_window.OmsSprTerr_Code = null;
		current_window.onHide = Ext.emptyFn;
		current_window.Sex_Code = null;
		current_window.isVisibleDisDTPanel = false;

		current_window.toggleVisibleDisDTPanel('hide');
		base_form.findField('Diag_id').filterDate = null;

		if (!arguments[0] || !arguments[0].formParams || !arguments[0].SurveyTypeLink_id || !arguments[0].SurveyType_Code || !arguments[0].DispClass_id)
		{
			Ext.Msg.alert(langs('Сообщение'), langs('Неверные параметры'), function() { current_window.hide(); } );
			return false;
		}

		current_window.object = 'EvnPLDispDop13';
		if (arguments[0].object)
		{
			current_window.object = arguments[0].object;
		}

		current_window.soputDiagsFirst = [];
		if (arguments[0].soputDiagsFirst)
		{
			current_window.soputDiagsFirst = arguments[0].soputDiagsFirst;
		}

		current_window.AgeGroupDispRecord = false;
		if (arguments[0].AgeGroupDispRecord)
		{
			current_window.AgeGroupDispRecord = arguments[0].AgeGroupDispRecord;
		}

		current_window.type = '';
		if (arguments[0].type)
		{
			current_window.type = arguments[0].type;
		}

		base_form.setValues(arguments[0].formParams);

		this.SurveyType_Code = +arguments[0].SurveyType_Code;
		this.SurveyType_IsVizit = arguments[0].SurveyType_IsVizit || null;
		this.SurveyTypeLink_id = arguments[0].SurveyTypeLink_id;
		this.DispClass_id = arguments[0].DispClass_id;

		this.loadFirstMedPersonal = true;
		this.MedSpecOmsList = null;
		this.CytoMedSpecOms_id = null;

		this.OrpDispSpec_Code = null;
		if (arguments[0].OrpDispSpec_Code) {
			this.OrpDispSpec_Code = arguments[0].OrpDispSpec_Code;
		}

		this.disableDidDate = false;
		if (arguments[0].disableDidDate)
		{
			this.disableDidDate = true;
		}

		base_form.findField('EvnUslugaDispDop_didDate').setMaxValue(undefined);
		base_form.findField('EvnUslugaDispDop_didDate').setMinValue(undefined);

		// для освидетельствования мигрантов направлений нет
		if (this.type.inlist(['DispMigrant']) || current_window.DispClass_id.inlist([19,26])) {
			this.findById('EUDD13EW_DirectionPanel').hide();
		}
		else {
			this.findById('EUDD13EW_DirectionPanel').show();
		}

		if (arguments[0].minDate)
		{
			base_form.findField('EvnUslugaDispDop_didDate').setMinValue(arguments[0].minDate);
		}

		if (arguments[0].maxDate)
		{
			base_form.findField('EvnUslugaDispDop_didDate').setMaxValue(arguments[0].maxDate);
		}
		else {
			base_form.findField('EvnUslugaDispDop_didDate').setMaxValue(getGlobalOptions().date);
		}

	
		this.setVisibleDeseaseStage(arguments[0].UslugaComplex_Date, arguments[0].ShowDeseaseStageCombo);

		this.lastLpuSectionProfile_id = null;
		this.lastCytoLpuSectionProfile_id = null;
		this.lastLpu_uid1 = null;
		this.lastDidDate1 = null;
		this.lastLpu_uid2 = null;
		this.lastDidDate2 = null;
		this.lastCytoLpu_id1 = null;
		this.lastCytoLpu_id2 = null;
		this.lastMedSpecOms_id = null;
		this.lastCytoMedSpecOms_id = null;

		if (getRegionNick() == 'vologda') {
			//#181608 Доработка поля даты
			base_form.findField('EUDD13EW_EvnUslugaDispDop_didDate').setValue(arguments[0].UslugaComplex_Date);
		}
		
		if (this.type.inlist(['DispTeenInspectionPeriod','DispTeenInspectionProf','DispTeenInspectionPred'])) {
			// панель результатов не нужна
			this.findById('EUDD13EW_ResultsPanel').hide();

			// но нужен результат и только для обследований.
			if (Ext.isEmpty(this.OrpDispSpec_Code)) {
				if (getRegionNick() != 'kz' && getGlobalOptions().disp_control == 3) {
					base_form.findField('EvnUslugaDispDop_Result').setAllowBlank(false);
				} else {
					base_form.findField('EvnUslugaDispDop_Result').setAllowBlank(true);
				}
				base_form.findField('EvnUslugaDispDop_Result').setContainerVisible(true);
			} else {
				base_form.findField('EvnUslugaDispDop_Result').setAllowBlank(true);
				base_form.findField('EvnUslugaDispDop_Result').setContainerVisible(false);
			}
		} else {
			this.findById('EUDD13EW_ResultsPanel').show();
			base_form.findField('EvnUslugaDispDop_Result').setAllowBlank(true);
			base_form.findField('EvnUslugaDispDop_Result').setContainerVisible(false);
		}

		this.setFieldsBySurveyTypeCode();

		// Фильтрация подразумевающая наличие для 1 SurveyType_id нескольких услуг в SurveyTypeLink
		base_form.findField('UslugaComplex_id').getStore().baseParams.SurveyTypeLink_lid = current_window.SurveyTypeLink_id;
		base_form.findField('UslugaComplex_id').getStore().baseParams.EvnPLDisp_id = base_form.findField('EvnVizitDispDop_pid').getValue();
		base_form.findField('UslugaComplex_id').getStore().baseParams.SurveyTypeLink_id = null;
		base_form.findField('UslugaComplex_id').getStore().baseParams.UslugaComplex_Date = (typeof this.UslugaComplex_Date == 'object' ? Ext.util.Format.date(this.UslugaComplex_Date, 'd.m.Y') : this.UslugaComplex_Date);
		base_form.findField('UslugaComplex_id').getStore().baseParams.LpuSection_id = null;
		base_form.findField('UslugaComplex_id').getStore().baseParams.SurveyTypeLink_ComplexSurvey = 1;

		// Фильтрация подразумевающая наличие для 1 SurveyType_id нескольких услуг в SurveyTypeLink
		base_form.findField('CytoUslugaComplex_id').getStore().baseParams.SurveyTypeLink_lid = current_window.SurveyTypeLink_id;
		base_form.findField('CytoUslugaComplex_id').getStore().baseParams.EvnPLDisp_id = base_form.findField('EvnVizitDispDop_pid').getValue();
		base_form.findField('CytoUslugaComplex_id').getStore().baseParams.SurveyTypeLink_id = null;
		base_form.findField('CytoUslugaComplex_id').getStore().baseParams.UslugaComplex_Date = (typeof this.UslugaComplex_Date == 'object' ? Ext.util.Format.date(this.UslugaComplex_Date, 'd.m.Y') : this.UslugaComplex_Date);
		base_form.findField('CytoUslugaComplex_id').getStore().baseParams.LpuSection_id = null;
		base_form.findField('CytoUslugaComplex_id').getStore().baseParams.SurveyTypeLink_ComplexSurvey = 2;

		// загрузить услуги в комбо, задисаблить комбо, если одна услуга
		base_form.findField('UslugaComplex_id').clearValue();
		base_form.findField('UslugaComplex_id').getStore().removeAll();
		this.lastUslugaComplexParams = null;

		base_form.findField('CytoUslugaComplex_id').clearValue();
		base_form.findField('CytoUslugaComplex_id').getStore().removeAll();
		this.lastCytoUslugaComplexParams = null;

		if (arguments[0].action)
		{
			current_window.action = arguments[0].action;
		}

		if (arguments[0].set_date)
		{
			current_window.set_date = arguments[0].set_date;
		}

		if (arguments[0].callback)
		{
			current_window.callback = arguments[0].callback;
		}

		if (arguments[0].onHide)
		{
			current_window.onHide = arguments[0].onHide;
		}

		if ( !Ext.isEmpty(arguments[0].Sex_Code) ) {
			current_window.Sex_Code = arguments[0].Sex_Code;
		}

		if ( !Ext.isEmpty(arguments[0].OmsSprTerr_Code) ) {
			current_window.OmsSprTerr_Code = arguments[0].OmsSprTerr_Code;
		}

		current_window.findById('EUDD13EW_PersonInformationFrame').load({
			Person_id: (arguments[0].Person_id ? arguments[0].Person_id : ''),
			Person_Birthday: (arguments[0].Person_Birthday ? arguments[0].Person_Birthday : ''),
			Person_Firname: (arguments[0].Person_Firname ? arguments[0].Person_Firname : ''),
			Person_Secname: (arguments[0].Person_Secname ? arguments[0].Person_Secname : ''),
			Person_Surname: (arguments[0].Person_Surname ? arguments[0].Person_Surname : ''),
			callback: function() {
				var field = base_form.findField('EvnUslugaDispDop_didDate');
				clearDateAfterPersonDeath('personpanelid', 'EUDD13EW_PersonInformationFrame', field);
			}
		});

		var loadMask = new Ext.LoadMask(Ext.get('EvnUslugaDispDop13EditWindow'), { msg: LOAD_WAIT });
		loadMask.show();

		var sex_id = arguments[0].Sex_id;
		var age = arguments[0].Person_Age;

		//log('args0', arguments[0]);

		var med_personal_id = 0; //arguments[0].formParams.MedPersonal_id;
		if (arguments[0].UserMedStaffFact
			&& arguments[0].UserMedStaffFact.MedPersonal_id
		) {
			med_personal_id = arguments[0].UserMedStaffFact.MedPersonal_id;
		}

		if (arguments[0].UserMedStaffFact
			&& arguments[0].UserMedStaffFact.ElectronicService_id
		) {
			this.ElectronicService_id = arguments[0].UserMedStaffFact.ElectronicService_id;
		}

		this.age = arguments[0].Person_Age;
		this.loadedParams = new Object();
		this.Sex_id = arguments[0].Sex_id;
		this.Person_Birthday = arguments[0].Person_Birthday;

		this.wintitle = langs('Осмотр, исследование');
		if (arguments[0].SurveyType_Name) {
			this.wintitle = arguments[0].SurveyType_Name;
		}

		this.EvnXmlPanel.doReset();
		this.EvnXmlPanel.setReadOnly('view' == this.action);
		this.EvnXmlPanel.LpuSectionField = base_form.findField('LpuSection_id');
		this.EvnXmlPanel.MedStaffFactField = base_form.findField('MedStaffFact_id');
		this.EvnDirectionGrid.getGrid().getStore().removeAll();

		this.EvnDirectionGrid.setReadOnly('view' == this.action);
		this.EvnDiagDopDispGrid.setReadOnly('view' == this.action);
		this.FilePanel.setDisabled('view' == this.action);

		if (
			this.SurveyType_Code.inlist([150,151,152,154])
			|| (this.DispClass_id.inlist([1, 2, 5]) && this.SurveyType_IsVizit == 2)
			|| this.DispClass_id.inlist([26, 10])
		) {
			if (this.DispClass_id == 19) {
				Ext.getCmp('EUDD13EW_PrintButton').getEl().show();
				Ext.getCmp('EUDD13EW_PrintButton').menu.items.items[0].setVisible(this.SurveyType_Code.inlist([150,152,154]));
				Ext.getCmp('EUDD13EW_PrintButton').menu.items.items[1].setVisible(this.SurveyType_Code.inlist([150,152,154]));
				Ext.getCmp('EUDD13EW_PrintButton').menu.items.items[2].setVisible(this.SurveyType_Code.inlist([152]));
				Ext.getCmp('EUDD13EW_PrintButton').menu.items.items[3].setVisible(this.SurveyType_Code.inlist([150,152,154]));
				Ext.getCmp('EUDD13EW_PrintButton').menu.items.items[4].setVisible(this.SurveyType_Code.inlist([150,152,154]));
				Ext.getCmp('EUDD13EW_PrintButton').menu.items.items[5].setVisible(this.SurveyType_Code.inlist([150]));
			} else {
				Ext.getCmp('EUDD13EW_PrintButton').getEl().hide();
			}
			this.setPrintItemsDisabled();
			this.EvnXmlPanel.show();
		}
		else {
			Ext.getCmp('EUDD13EW_PrintButton').getEl().hide();
			this.EvnXmlPanel.hide();
		}

		if (
			this.SurveyType_Code.inlist([150,151,152,154]) ||
			(this.DispClass_id.inlist([1,2,3,4,5,7,8,10,12,26]) && this.SurveyType_IsVizit == 2 && getRegionNick() != 'kz')
		) {
			this.findById('EUDD13EW_EvnDirectionGridPanel').show();

			if (this.DispClass_id && this.DispClass_id == 26 &&
				!Ext.isEmpty(base_form.findField('EvnVizitDispDop_pid').getValue())) {
				var disp_pid = base_form.findField('EvnVizitDispDop_pid').getValue();

				this.EvnDirectionGrid.loadData({
					params: {EvnDirection_pid: disp_pid, includeDeleted: 1},
					globalFilters: {EvnDirection_pid: disp_pid, includeDeleted: 1}
				});

			} else if (!Ext.isEmpty(base_form.findField('EvnUslugaDispDop_id').getValue())) {
				this.EvnDirectionGrid.loadData({
					params: {EvnDirection_pid: base_form.findField('EvnUslugaDispDop_id').getValue(), includeDeleted: 1},
					globalFilters: {EvnDirection_pid: base_form.findField('EvnUslugaDispDop_id').getValue(), includeDeleted: 1}
				});
			}
		} else {
			this.findById('EUDD13EW_EvnDirectionGridPanel').hide();
		}

		if (current_window.DispClass_id.inlist([19,26])) {
			this.FilePanel.show();
			//загружаем файлы
			this.FileUploadPanel.reset();
			if (!Ext.isEmpty(base_form.findField('EvnUslugaDispDop_id').getValue())) {
				this.FileUploadPanel.listParams = {
					Evn_id: base_form.findField('EvnUslugaDispDop_id').getValue()
				};
				this.FileUploadPanel.loadData({
					Evn_id: base_form.findField('EvnUslugaDispDop_id').getValue()
				});
			}
		}
		else {
			this.FilePanel.hide();
		}

		var TumorAllowDate = new Date(2017,1,1);
		this.allowEditTumor = false;
		/*if(getRegionNick() == 'ekb' && this.UslugaComplex_Date && this.UslugaComplex_Date >= TumorAllowDate && this.SurveyType_Code.inlist(['19','27'])) //19 - осмотр терапевта; 27 - осмотр педиатра
			this.allowEditTumor = true;*/
		base_form.findField('TumorStage_id').setContainerVisible(false);
		base_form.findField('TumorStage_id').setAllowBlank(true);

		var diag_combo = base_form.findField('Diag_id');
		diag_combo.clearBaseFilter();

		if (current_window.DispClass_id == 19) {
			switch (current_window.SurveyType_Code) {
				case 141:
					diag_combo.setBaseFilter(function (rec) {
						var Diag_Code = rec.attributes ? rec.attributes.Diag_Code : rec.get('Diag_Code');
						if (Ext.isEmpty(Diag_Code)) return false;
						return (Diag_Code == 'Z10.8' || Diag_Code.substr(0,3).inlist(['Z02']));
					});
					break;
				case 150:
					diag_combo.setBaseFilter(function (rec) {
						var Diag_Code = rec.attributes ? rec.attributes.Diag_Code : rec.get('Diag_Code');
						if (Ext.isEmpty(Diag_Code)) return false;
						return (Diag_Code == 'Z10.8' || Diag_Code.substr(0,3).inlist(['A15','A16','A17','A18','A19','Z02']));
					});
					break;
				case 154:
					diag_combo.setBaseFilter(function (rec) {
						var Diag_Code = rec.attributes ? rec.attributes.Diag_Code : rec.get('Diag_Code');
						if (Ext.isEmpty(Diag_Code)) return false;
						return (Diag_Code == 'Z10.8' || Diag_Code.substr(0,3).inlist(['A50','A51','A52','A53','Z02']));
					});
					break;
				case 151:
					diag_combo.setBaseFilter(function (rec) {
						var Diag_Code = rec.attributes ? rec.attributes.Diag_Code : rec.get('Diag_Code');
						if (Ext.isEmpty(Diag_Code)) return false;
						return (Diag_Code == 'Z10.8' || (Diag_Code.substr(0,3) >= 'F10' && Diag_Code.substr(0,3) <= 'F19') || Diag_Code.substr(0,3).inlist(['Z02']));
					});
					break;
				case 152:
					diag_combo.setBaseFilter(function (rec) {
						var Diag_Code = rec.attributes ? rec.attributes.Diag_Code : rec.get('Diag_Code');
						if (Ext.isEmpty(Diag_Code)) return false;
						return (Diag_Code == 'Z10.8' || Diag_Code.substr(0,3).inlist(['B20','B21','B22','B23','B24','A30','Z02']));
					});
					break;
				default:
					diag_combo.setBaseFilter(function (rec) {
						var Diag_Code = rec.attributes ? rec.attributes.Diag_Code : rec.get('Diag_Code');
						if (Ext.isEmpty(Diag_Code)) return false;
						return (
							(Diag_Code.substr(0,1) < 'V' || Diag_Code.substr(0,1) > 'Y')
						);
					});
					break;
			}
		} else {
			diag_combo.setBaseFilter(function (rec) {
				var Diag_Code = rec.attributes ? rec.attributes.Diag_Code : rec.get('Diag_Code');
				if (Ext.isEmpty(Diag_Code)) return false;
				return (
					(Diag_Code.substr(0,1) < 'V' || Diag_Code.substr(0,1) > 'Y')
				);
			});
		}

		// Только для осмотра врача терапевта отображаем панель специфики и грузим ее
		this.findById('EUDD13EW_SpecificsPanel').setVisible(this.SurveyType_Code.inlist([19]));

		switch (current_window.action)
		{
			case 'edit':
			case 'view':
				if (current_window.action == 'edit') {
					current_window.setTitle(this.wintitle + langs(': Редактирование'));
					current_window.enableEdit(true);
				} else {
					current_window.setTitle(this.wintitle + langs(': Просмотр'));
					current_window.enableEdit(false);
				}

				if (current_window.disableDidDate) {
					base_form.findField('EvnUslugaDispDop_didDate').disable();
				}

				base_form.findField('UslugaComplex_id').disable();
				if (base_form.findField('UslugaComplex_id').getStore().getCount() > 1) {
					if (current_window.action != 'view') {
						base_form.findField('UslugaComplex_id').enable();
					}
				}
				current_window.findById('EUDD13EW_ExaminationPlaceCombo').fireEvent('change', current_window.findById('EUDD13EW_ExaminationPlaceCombo'), current_window.findById('EUDD13EW_ExaminationPlaceCombo').getValue());
				// устанавливаем врача
				current_window.findById('EUDD13EW_MedPersonalCombo').getStore().findBy(function(record) {
					if ( record.get('MedPersonal_id') == med_personal_id )
					{
						current_window.findById('EUDD13EW_MedPersonalCombo').setValue(record.get('MedStaffFact_id'));
						return true;
					}
				});

				loadMask.hide();

				// если уже было сохранено надо грузить с сервера
				if (!Ext.isEmpty(base_form.findField('EvnUslugaDispDop_id').getValue())) {
					loadMask.show();
					base_form.load({
						failure: function() {
							loadMask.hide();
							sw.swMsg.alert(langs('Ошибка'), langs('Ошибка при загрузке данных формы'), function() { current_window.hide(); } );
						}.createDelegate(this),
						params: {
							EvnUslugaDispDop_id: base_form.findField('EvnUslugaDispDop_id').getValue(),
							archiveRecord: current_window.archiveRecord
						},
						success: function(result_form, action) {
							loadMask.hide();

							var responseObj = {};

							if ( action && action.response && action.response.responseText ) {
								responseObj = Ext.util.JSON.decode(action.response.responseText);

								if ( responseObj.length > 0 ) {
									responseObj = responseObj[0];
									base_form.findField('UslugaComplex_id').getStore().baseParams.LpuSection_id = responseObj.LpuSection_id;

									if (getRegionNick().inlist(['ufa', 'perm']) && !Ext.isEmpty(current_window.DispClass_id) && current_window.DispClass_id.inlist([1, 2]) && current_window.SurveyType_Code == 19) {
										base_form.findField('UslugaComplex_id').getStore().baseParams.UslugaComplex_Date = responseObj.EvnUslugaDispDop_didDate;
									}
								}
							}

							current_window.loadUslugaComplexCombo();
							current_window.loadCytoUslugaComplexCombo();

							if (Number(responseObj.EvnUslugaDispDop_WithDirection)) {
								// непонятно почему мы должны дизаблить форму?!
								// если мы делаем назначение через шаблон талона осмотра, то это тоже срабатывает!
								if (this.DispClass_id && !this.DispClass_id.inlist([26])) {
									current_window.setTitle(this.wintitle + langs(': Просмотр'));
									current_window.enableEdit(false);
								}
							}

							var
								ExaminationPlace_id = responseObj.ExaminationPlace_id,
								MedStaffFact_id = responseObj.MedStaffFact_id,
								MedPersonal_id = responseObj.MedPersonal_id,
								MedSpecOms_id = responseObj.MedSpecOms_id,
								Lpu_uid = responseObj.Lpu_uid,
								LpuSection_id = responseObj.LpuSection_id,
								LpuSectionProfile_id = responseObj.LpuSectionProfile_id,
								CytoExaminationPlace_id = responseObj.CytoExaminationPlace_id || null,
								CytoLpu_id = responseObj.CytoLpu_id || null,
								CytoLpuSectionProfile_id = responseObj.CytoLpuSectionProfile_id || null,
								CytoLpuSection_id = responseObj.CytoLpuSection_id || null,
								CytoMedSpecOms_id = responseObj.CytoMedSpecOms_id || null,
								CytoMedPersonal_id = responseObj.CytoMedPersonal_id || null,
								CytoEvnUsluga_setDate = responseObj.CytoEvnUsluga_setDate || null;
							DeseaseStage = responseObj.DeseaseStage || null;

							if (!Ext.isEmpty(ExaminationPlace_id) && ExaminationPlace_id == 3) {
								current_window.loadFirstMedPersonal = false;
							}

							base_form.findField('DeseaseStage').setValue(DeseaseStage);
							base_form.findField('CytoLpuSection_id').setValue(CytoLpuSection_id);

							var Diag_id = base_form.findField('Diag_id').getValue();
							var didDate = base_form.findField('EvnUslugaDispDop_didDate').getValue();
							var didTime = base_form.findField('EvnUslugaDispDop_didTime').getValue();
							var disDate = base_form.findField('EvnUslugaDispDop_disDate').getValue();
							var disTime = base_form.findField('EvnUslugaDispDop_disTime').getValue();

							if ((!Ext.isEmpty(disDate) || !Ext.isEmpty(disTime)) && (disDate-didDate != 0 || didTime != disTime)) {
								this.toggleVisibleDisDTPanel('show');
							} else {
								base_form.findField('EvnUslugaDispDop_disDate').setValue(null);
								base_form.findField('EvnUslugaDispDop_disTime').setValue(null);
							}

							base_form.findField('EvnUslugaDispDop_setDate').fireEvent('change', base_form.findField('EvnUslugaDispDop_setDate'), base_form.findField('EvnUslugaDispDop_setDate').getValue());

							if ( !Ext.isEmpty(ExaminationPlace_id) && ExaminationPlace_id == 3 ) {
								// показать поля МО, Профиль, Специальность
								base_form.findField('Lpu_uid').showContainer();
								base_form.findField('LpuSectionProfile_id').setContainerVisible(!getRegionNick().inlist([ 'buryatiya', 'ekb', 'kareliya', 'penza', 'pskov', 'adygeya' ]));
								base_form.findField('MedSpecOms_id').setContainerVisible(!getRegionNick().inlist([ 'buryatiya', 'ekb', 'kareliya', 'penza', 'pskov', 'adygeya' ]));

								base_form.findField('Lpu_uid').setAllowBlank(getRegionNick().inlist([ 'buryatiya', 'kareliya', 'penza', 'pskov', 'ufa', 'ekb' ]));
								base_form.findField('LpuSectionProfile_id').setAllowBlank(getRegionNick().inlist([ 'buryatiya', 'kareliya', 'penza', 'pskov', 'ufa', 'ekb', 'adygeya' ]));
								base_form.findField('MedSpecOms_id').setAllowBlank(getRegionNick().inlist([ 'buryatiya', 'kareliya', 'penza', 'pskov', 'ufa', 'ekb', 'adygeya' ]));
								if (getRegionNick().inlist([ 'krym', 'perm' ])) {
									base_form.findField('LpuSection_id').disableLinkedElements();
									base_form.findField('MedStaffFact_id').disableParentElement();
								}

								if(!getRegionNick()== 'buryatiya') {
									base_form.findField('LpuSection_id').setAllowBlank(true);
									base_form.findField('MedStaffFact_id').setAllowBlank(true);
								}

								base_form.findField('LpuSection_id').getStore().removeAll();
								base_form.findField('MedStaffFact_id').getStore().removeAll();

								if ( !Ext.isEmpty(Lpu_uid) ) {
									this.filterLpuCombo();
									base_form.findField('Lpu_uid').setValue(Lpu_uid);
								}

								this.setLpuSectionProfile();

								if (
									(
										!getRegionNick().inlist([ 'krym', 'perm' ])
										&& !Ext.isEmpty(Lpu_uid)
									)
									|| (
										getRegionNick().inlist([ 'krym', 'perm' ])
										&& !Ext.isEmpty(LpuSectionProfile_id)
										&& !Ext.isEmpty(Lpu_uid)
									)
								) {
									this.lastLpuSectionProfile_id = LpuSectionProfile_id;
									this.lastLpu_uid1 = Lpu_uid;
									this.lastDidDate1 = (!Ext.isEmpty(didDate) ? Ext.util.Format.date(didDate, 'd.m.Y') : null);

									base_form.findField('LpuSection_id').getStore().load({
										callback: function() {
											var index = base_form.findField('LpuSection_id').getStore().findBy(function(rec) {
												return (rec.get('LpuSection_id') == LpuSection_id);
											});

											if ( index >= 0 ) {
												base_form.findField('LpuSection_id').setValue(LpuSection_id);
											}
											else {
												base_form.findField('LpuSection_id').clearValue();
											}
										}.createDelegate(this),
										params: {
											date: (!Ext.isEmpty(didDate) ? Ext.util.Format.date(didDate, 'd.m.Y') : null),
											LpuSectionProfile_id: LpuSectionProfile_id,
											Lpu_id: Lpu_uid,
											mode: (getRegionNick().inlist([ 'krym', 'perm' ]))?'combo':'dispcontractcombo'
										}
									});
								}

								if (
									(
										!getRegionNick().inlist([ 'krym', 'perm' ])
										&& !Ext.isEmpty(Lpu_uid)
									)
									|| (
										getRegionNick().inlist([ 'krym', 'perm' ])
										&& !Ext.isEmpty(MedSpecOms_id)
										&& !Ext.isEmpty(Lpu_uid)
									)
								) {
									this.lastMedSpecOms_id = MedSpecOms_id;
									this.lastLpu_uid2 = Lpu_uid;
									this.lastDidDate2 = (!Ext.isEmpty(didDate) ? Ext.util.Format.date(didDate, 'd.m.Y') : null);

									base_form.findField('MedStaffFact_id').getStore().load({
										callback: function() {
											var index = base_form.findField('MedStaffFact_id').getStore().findBy(function(rec) {
												return (Number(rec.get('MedStaffFact_id')) == Number(MedStaffFact_id));
											});
											if ( index < 0 ) {
												index = base_form.findField('MedStaffFact_id').getStore().findBy(function(rec) {
													return (Number(rec.get('MedPersonal_id')) == Number(MedPersonal_id) && Number(rec.get('LpuSection_id')) == Number(LpuSection_id));
												});
												if ( index < 0 ) {
													index = base_form.findField('MedStaffFact_id').getStore().findBy(function (rec) {
														return (rec.get('MedPersonal_id') == base_form.findField('MedPersonal_id').getValue());
													});
												}
											}

											if ( index >= 0 ) {
												base_form.findField('MedStaffFact_id').setValue(base_form.findField('MedStaffFact_id').getStore().getAt(index).get('MedStaffFact_id'));
											}
											else {
												base_form.findField('MedStaffFact_id').clearValue();
											}
										}.createDelegate(this),
										params: {
											onDate: (!Ext.isEmpty(didDate) ? Ext.util.Format.date(didDate, 'd.m.Y') : null),
											mode: (getRegionNick().inlist([ 'krym', 'perm' ]))?'combo':'dispcontractcombo',
											MedSpecOms_id: MedSpecOms_id,
											Lpu_id: Lpu_uid
										}
									});
								}
							}
							else {
								this.loadedParams = responseObj;
								base_form.findField('EvnUslugaDispDop_didDate').fireEvent('change', base_form.findField('EvnUslugaDispDop_didDate'), base_form.findField('EvnUslugaDispDop_didDate').getValue());
							}

							var diag_combo = base_form.findField('Diag_id');
							if ( !Ext.isEmpty(Diag_id) ) {
								diag_combo.getStore().load({
									callback: function() {
										diag_combo.getStore().each(function(record) {
											if ( record.get('Diag_id') == Diag_id ) {
												diag_combo.setValue(Diag_id);
												current_window.diagIsChanged = false;
												diag_combo.fireEvent('select', diag_combo, record, 0);
												diag_combo.onChange();
											}
										});
									},
									params: { where: "where DiagLevel_id = 4 and Diag_id = " + Diag_id }
								});
							}

							base_form.findField('CytoEvnUsluga_setDate').setValue(CytoEvnUsluga_setDate);

							if ( !Ext.isEmpty(CytoLpu_id) ) {
								this.filterLpuCombo('cyto');
								base_form.findField('CytoLpu_id').setValue(CytoLpu_id);
							}

							base_form.findField('CytoExaminationPlace_id').fireEvent('change', base_form.findField('CytoExaminationPlace_id'), CytoExaminationPlace_id);
							base_form.findField('CytoLpu_id').fireEvent('change', base_form.findField('CytoLpu_id'), CytoLpu_id);

							if (getRegionNick() == 'ufa' && !Ext.isEmpty(current_window.DispClass_id) && current_window.DispClass_id.inlist([1,2,10,5])) {
								// услуга зависит от выбранного отделения
								base_form.findField('UslugaComplex_id').getStore().baseParams.LpuSection_id = base_form.findField('LpuSection_id').getValue();
								current_window.loadUslugaComplexCombo();
							}

							if (current_window.DispClass_id == 19 && current_window.SurveyType_Code == 150) {
								log(base_form.findField('migrant_tub').getStore());
								var index = base_form.findField('migrant_tub').getStore().findBy(function(rec) {
									return (rec.get('YesNo_id') == base_form.findField('migrant_tub').getValue());
								});
								if ( index >= 0 ) {
									base_form.findField('migrant_tub').fireEvent('select', base_form.findField('migrant_tub'), base_form.findField('migrant_tub').getStore().getAt(index), base_form.findField('migrant_tub').getValue());
								}
								else {
									base_form.findField('migrant_tub').fireEvent('select', base_form.findField('migrant_tub'), base_form.findField('migrant_tub').getStore().getAt(0), 0);
								}
							}

							if (current_window.DispClass_id.inlist([19,26])) { this.setPrintItemsDisabled(); }
							if (current_window.DispClass_id.inlist([19])) { this.setFormParamsFromDirection(); }

							this.EvnXmlPanel.setBaseParams({
								userMedStaffFact: sw.Promed.MedStaffFactByUser.last,
								UslugaComplex_id: base_form.findField('UslugaComplex_id').getValue(),
								Server_id: base_form.findField('Server_id').getValue(),
								Evn_id: base_form.findField('EvnVizitDispDop_id').getValue()
							});
							this.EvnXmlPanel.doLoadData();

							if (base_form.findField('Cyto_IsNotAgree').checked) // после всех fire event'ов выключим поля цистологического исследования отделение и врач
							{
								this.onCytoAgreeChange();
							}

							// Грузим специфику, так как нужен id посещения
							current_window.loadSpecificsTree();
						}.createDelegate(this),
						url: '/?c=EvnUslugaDispDop&m=loadEvnUslugaDispDop'
					});
					current_window.EvnDiagDopDispGrid.loadData({globalFilters: {EvnDiagDopDisp_pid: base_form.findField('EvnUslugaDispDop_id').getValue()}});
				}
				else {
					setCurrentDateTime({
						callback: function() {
							base_form.findField('EvnUslugaDispDop_didDate').fireEvent('change', base_form.findField('EvnUslugaDispDop_didDate'), base_form.findField('EvnUslugaDispDop_didDate').getValue());
						},
						dateField: base_form.findField('EvnUslugaDispDop_didDate'),
						loadMask: false,
						setDate: true,
						//setDateMaxValue: true,
						setDateMinValue: false,
						setTime: true,
						timeField: base_form.findField('EvnUslugaDispDop_didTime'),
						windowId: this.id
					});

					var defaultDiagCode = 'Z10.8';

					switch ( getRegionNick() ) {
						case 'kareliya':
						case 'penza':
							defaultDiagCode = 'Z01.8';
							break;

						case 'krym':
							defaultDiagCode = 'Z00.0';
							break;
					}

					// отдельная логика для периодических осмотров
					// При редактировании осмотров/исследований в маршрутной карте по умолчанию подгружать диагноз «Z00.1 Рутинное обследование состояния здоровья ребенка» (refs #23774)
					if (this.type.inlist(['DispTeenInspectionPeriod','DispTeenInspectionProf','DispTeenInspectionPred'])) {
						defaultDiagCode = 'Z00.1';
					}

					var diag_combo = base_form.findField('Diag_id');
					diag_combo.getStore().load({
						callback: function() {
							diag_combo.getStore().each(function(record) {
								if ( record.get('Diag_Code') == defaultDiagCode ) {
									diag_combo.setValue(record.get('Diag_id'));
									diag_combo.fireEvent('select', diag_combo, record, 0);
									diag_combo.onChange();
									current_window.diagIsChanged = false;
								}
							});
						},
						params: { where: "where DiagLevel_id = 4 and Diag_Code = '"+defaultDiagCode+"'" }
					});

					if (current_window.DispClass_id == 19 && current_window.SurveyType_Code == 150) {
						var index = base_form.findField('migrant_tub').getStore().findBy(function(rec) {
							return (rec.get('YesNo_id') == base_form.findField('migrant_tub').getValue());
						});
						if ( index >= 0 ) {
							base_form.findField('migrant_tub').fireEvent('select', base_form.findField('migrant_tub'), base_form.findField('migrant_tub').getStore().getAt(index), base_form.findField('migrant_tub').getValue());
						}
						else {
							base_form.findField('migrant_tub').fireEvent('select', base_form.findField('migrant_tub'), base_form.findField('migrant_tub').getStore().getAt(0), 0);
						}
					}

					if (current_window.DispClass_id.inlist([19,26])) {
						this.setFormParamsFromDirection();
					}

					if (getRegionNick() == 'ufa' && !Ext.isEmpty(current_window.DispClass_id) && current_window.DispClass_id.inlist([1,2,10,5])) {
						// услуга зависит от выбранного отделения
						base_form.findField('UslugaComplex_id').getStore().baseParams.LpuSection_id = base_form.findField('LpuSection_id').getValue();
					}
					current_window.loadUslugaComplexCombo();
					current_window.loadCytoUslugaComplexCombo();
					current_window.loadSpecificsTree();
				}

				// @task https://redmine.swan.perm.ru//issues/109117
				if ( getRegionNick() == 'perm'&& Ext.isEmpty(current_window.OrpDispSpec_Code) && current_window.DispClass_id.toString().inlist([ '6', '9', '10', '11', '12' ]) ) {
					base_form.findField('EvnUslugaDispDop_Result').setValue('Выполнено');
				}

				current_window.findById('EUDD13EW_EvnUslugaDispDop_setDate').fireEvent('change', current_window.findById('EUDD13EW_EvnUslugaDispDop_setDate'), current_window.findById('EUDD13EW_EvnUslugaDispDop_setDate').getValue());
				current_window.findById('EUDD13EW_EvnUslugaDispDop_didDate').focus(false, 250);
				break;

			default:
				current_window.hide();
		}
		/*
				//В рамках задачи https://redmine.swan.perm.ru/issues/23822 определим последний компонент в подпункте "Результат", чтобы с него осуществить переход на кнопку "Сохранить"
				var items = this.findById('EUDD13EW_ResultsPanel').items.items;
				for (var key in items) {
					var obj = items[key];
					if(!obj.hidden && obj.xtype){
						var last_obj = obj;
					}
				}
				if(last_obj)
					last_obj.addListener('keydown',function(inp,e){
						if(e.getKey() == Ext.EventObject.TAB){
							e.stopEvent();
							Ext.getCmp('EUDD13EW_SaveButton').focus(true, 200);
						}
					});
		*/

		if (this.DispClass_id && this.DispClass_id.inlist([10, 26])) {

			// не выпиливайте пожалуйста эту строчку,
			// нужна для прохождения осмотров через ЭО
			if (this.DispClass_id == 10) this.maximize();

			var edPanel = this.findById('EUDD13EW_DirectionPanel');
			if (edPanel) edPanel.collapse();

			var ddGridPanel = this.findById('EUDD13EW_EvnDiagDopDispGridPanel');
			if (ddGridPanel) ddGridPanel.collapse();

			var edGridPanel = this.findById('EUDD13EW_EvnDirectionGridPanel');
			if (edGridPanel) edGridPanel.collapse();
		}

	},
	setDisDT: function() {
		if ( this.isVisibleDisDTPanel ) {
			return false;
		}

		var base_form = this.findById('EUDD13EW_EvnUslugaDispDopEditForm').getForm();

		base_form.findField('EvnUslugaDispDop_disDate').setValue(base_form.findField('EvnUslugaDispDop_didDate').getValue());
		base_form.findField('EvnUslugaDispDop_disTime').setValue(base_form.findField('EvnUslugaDispDop_didTime').getValue());
	},
	toggleVisibleDisDTPanel: function(action) {
		var base_form = this.findById('EUDD13EW_EvnUslugaDispDopEditForm').getForm();

		if (action == 'show') {
			this.isVisibleDisDTPanel = false;
		} else if (action == 'hide') {
			this.isVisibleDisDTPanel = true;
		}

		if (this.isVisibleDisDTPanel) {
			this.findById('EUDD13EW_EvnUslugaDisDTPanel').hide();
			this.findById('EUDD13EW_ToggleVisibleDisDTBtn').setText(langs('Уточнить период выполнения'));
			base_form.findField('EvnUslugaDispDop_disDate').setAllowBlank(true);
			base_form.findField('EvnUslugaDispDop_disTime').setAllowBlank(true);
			base_form.findField('EvnUslugaDispDop_disDate').setValue(null);
			base_form.findField('EvnUslugaDispDop_disTime').setValue(null);
			base_form.findField('EvnUslugaDispDop_disDate').setMaxValue(undefined);
			this.isVisibleDisDTPanel = false;
		} else {
			this.findById('EUDD13EW_EvnUslugaDisDTPanel').show();
			this.findById('EUDD13EW_ToggleVisibleDisDTBtn').setText(langs('Скрыть поля'));
			base_form.findField('EvnUslugaDispDop_disDate').setAllowBlank(false);
			base_form.findField('EvnUslugaDispDop_disTime').setAllowBlank(false);
			base_form.findField('EvnUslugaDispDop_disDate').setMaxValue(getGlobalOptions().date);
			this.isVisibleDisDTPanel = true;
		}
	},
	// вынес в отдельную функцию, слишком много логики
	setVisibleDeseaseStage: function(UslugaComplex_Date, ShowDeseaseStageCombo){
		var base_form = this.findById('EUDD13EW_EvnUslugaDispDopEditForm').getForm();
		base_form.findField('DeseaseStage').setContainerVisible(false);
		base_form.findField('DeseaseStage').setAllowBlank(true);
		base_form.findField('DeseaseStage').disable();
		this.UslugaComplex_Date = null;
		this.AllowEditDeseaseStageByDate = false;
		this.ShowDeseaseStageCombo = false;
		if (UslugaComplex_Date)
		{
			UslugaComplex_Date = (typeof UslugaComplex_Date != 'object' ? (Date.parseDate(UslugaComplex_Date, 'd.m.Y')) : UslugaComplex_Date);
			this.UslugaComplex_Date = UslugaComplex_Date;
			if(!ShowDeseaseStageCombo){
				this.ShowDeseaseStageCombo = false;
			}
			else
			{
				if(this.UslugaComplex_Date>=Date.parseDate('01.04.2015', 'd.m.Y'))
				{
					this.ShowDeseaseStageCombo = true;
					this.AllowEditDeseaseStageByDate = true;
				}
				// #161204, #179362 Поле "Стадия" на Перми и Карелии (Из ТЗ: Если дата подписания согласия больше или равна 01.01.2019) скрыто,
				if(this.UslugaComplex_Date>=Date.parseDate('01.01.2019', 'd.m.Y') && getRegionNick().inlist([ 'perm', 'kareliya']))
				{
					this.ShowDeseaseStageCombo = false;
					this.AllowEditDeseaseStageByDate = false;
				}
			}
		}
		if(this.ShowDeseaseStageCombo)
		{
			base_form.findField('DeseaseStage').setContainerVisible(true);
			//base_form.findField('DeseaseStage').setAllowBlank(false);
		}
		else
		{
			base_form.findField('DeseaseStage').setContainerVisible(false);
			//base_form.findField('DeseaseStage').setAllowBlank(true);
		}

	},
	width: 700
});