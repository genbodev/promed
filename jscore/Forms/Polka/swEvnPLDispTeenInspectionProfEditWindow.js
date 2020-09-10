/**
* swEvnPLDispTeenInspectionProfEditWindow - окно редактирования/добавления талона по дополнительной диспансеризации
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package		Polka
* @access		public
* @copyright		Copyright (c) 2013 Swan Ltd.
* @author		Dmitry Vlasenko
* @originalauthor	Ivan Petukhov aka Lich (megatherion@list.ru) / Stas Bykov aka Savage (savage1981@gmail.com)
* @version		19.05.2013
* @comment		Префикс для id компонентов EPLDTIPRO (EvnPLDispTeenInspectionProfEditForm)
*
*/
/*NO PARSE JSON*/
sw.Promed.swEvnPLDispTeenInspectionProfEditWindow = Ext.extend(sw.Promed.BaseForm, {
	action: 'add',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	collapsible: true,
	/* */
	codeRefresh: true,
	objectName: 'swEvnPLDispTeenInspectionProfEditWindow',
	objectSrc: '/jscore/Forms/Polka/swEvnPLDispTeenInspectionProfEditWindow.js',
	draggable: true,
	getDataForCallBack: function()
	{
		var win = this;
		var base_form = win.EvnPLDispTeenInspectionFormPanel.getForm();
		var personinfo = win.PersonInfoPanel;
		
		var response = new Object();
		
		response.EvnPLDispTeenInspection_id = base_form.findField('EvnPLDispTeenInspection_id').getValue();
		response.Person_id = base_form.findField('Person_id').getValue();
		response.Server_id = base_form.findField('Server_id').getValue();
		response.Person_Surname = personinfo.getFieldValue('Person_Surname');
		response.Person_Firname = personinfo.getFieldValue('Person_Firname');
		response.Person_Secname = personinfo.getFieldValue('Person_Secname');
		response.Person_Birthday = personinfo.getFieldValue('Person_Birthday');
		response.Sex_Name = personinfo.getFieldValue('Sex_Name');
		response.ua_name = personinfo.getFieldValue('Person_RAddress');
		response.pa_name = personinfo.getFieldValue('Person_PAddress');
		response.AgeGroupDisp_Name = base_form.findField('AgeGroupDisp_id').getFieldValue('AgeGroupDisp_Name');
		response.OrgExist = base_form.findField('OrgExist').getValue();
		response.EvnPLDispTeenInspection_setDate = typeof base_form.findField('EvnPLDispTeenInspection_setDate').getValue() == 'object' ? base_form.findField('EvnPLDispTeenInspection_setDate').getValue() : Date.parseDate(base_form.findField('EvnPLDispTeenInspection_setDate').getValue(), 'd.m.Y');
		response.EvnPLDispTeenInspection_disDate = typeof base_form.findField('EvnPLDispTeenInspection_disDate').getValue() == 'object' ? base_form.findField('EvnPLDispTeenInspection_disDate').getValue() : Date.parseDate(base_form.findField('EvnPLDispTeenInspection_disDate').getValue(), 'd.m.Y');
		response.EvnPLDispTeenInspection_IsFinish = (base_form.findField('EvnPLDispTeenInspection_IsFinish').getValue() == 2) ? 'Да':'Нет';
		response.EvnPLDispTeenInspection_hasDirection = null;
		response.EvnPLDispTeenInspection_IsTwoStage = (base_form.findField('EvnPLDispTeenInspection_IsTwoStage').getValue() == 2) ? 'Да':'Нет';
		if (base_form.findField('EvnCostPrint_IsNoPrint').getValue() == 2) {
			response.EvnCostPrint_IsNoPrintText = 'Отказ от справки';
		} else if (base_form.findField('EvnCostPrint_IsNoPrint').getValue() == 1) {
			response.EvnCostPrint_IsNoPrintText = 'Справка выдана';
		} else {
			response.EvnCostPrint_IsNoPrintText = '';
		}
		response.EvnCostPrint_setDT = base_form.findField('EvnCostPrint_setDT').getValue();
		response.UslugaComplex_Name = base_form.findField('UslugaComplex_id').getFieldValue('UslugaComplex_Name');
		
		return response;
	},
	loadUslugaComplex: function() {
		var win = this;
		var base_form = win.EvnPLDispTeenInspectionFormPanel.getForm();

		if (getRegionNick() == 'buryatiya') {
			base_form.findField('UslugaComplex_id').clearValue();
			base_form.findField('UslugaComplex_id').getStore().baseParams.dispOnly = 1;
			base_form.findField('UslugaComplex_id').getStore().baseParams.DispClass_id = base_form.findField('DispClass_id').getValue();
			base_form.findField('UslugaComplex_id').getStore().baseParams.UslugaComplex_Date = (typeof win.findById('EPLDTIPRO_EvnPLDispTeenInspection_setDate').getValue() == 'object' ? Ext.util.Format.date(win.findById('EPLDTIPRO_EvnPLDispTeenInspection_setDate').getValue(), 'd.m.Y') : win.findById('EPLDTIPRO_EvnPLDispTeenInspection_setDate').getValue());
			base_form.findField('UslugaComplex_id').getStore().baseParams.Person_id = base_form.findField('Person_id').getValue();
			base_form.findField('UslugaComplex_id').getStore().load({
				callback: function() {
					if (base_form.findField('UslugaComplex_id').getStore().getCount() > 0) {
						base_form.findField('UslugaComplex_id').setValue(base_form.findField('UslugaComplex_id').getStore().getAt(0).get('UslugaComplex_id'));
					}
				}
			});
		}
	},
	verfGroup:function(){
		var wins = this;
		var base_form = wins.EvnPLDispTeenInspectionFormPanel.getForm();
		if ( base_form.findField('EvnPLDispTeenInspection_IsFinish').getValue() == 2 ) {
			//Проверка на Группу здоровья
			base_form.findField('HealthKind_id').setAllowBlank(false);
			base_form.findField('HealthKind_id').validate();
		}else{
			base_form.findField('HealthKind_id').setAllowBlank(true);
			base_form.findField('HealthKind_id').validate();
		}
	},
	doSave: function(options) {
		if ( this.formStatus == 'save' || this.action == 'view' ) {
			return false;
		}

		this.formStatus = 'save';

		if ( typeof options != 'object' ) {
			options = new Object();
		}

		var win = this;
		var EvnPLDispTeenInspection_form = win.EvnPLDispTeenInspectionFormPanel;

		var base_form = win.EvnPLDispTeenInspectionFormPanel.getForm();

		if ( !EvnPLDispTeenInspection_form.getForm().isValid() )
		{
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					win.formStatus = 'edit';
					EvnPLDispTeenInspection_form.getFirstInvalidEl().focus(false);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		var EvnDiagAndRecomendationGrid = this.EvnDiagAndRecomendationGrid.getGrid();

		if (
			!Ext.isEmpty(base_form.findField('HealthKind_id').getValue())
			&& base_form.findField('HealthKind_id').getValue() == 1
			&& EvnDiagAndRecomendationGrid.getStore().getCount() > 0
			&& !Ext.isEmpty(EvnDiagAndRecomendationGrid.getStore().getAt(0).get('EvnVizitDispDop_id'))
		) {
			sw.swMsg.alert(lang['oshibka'], 'Нельзя выбрать I группу здоровья при указании диагнозов и рекомендаций по результатам диспансеризации / профосмотра');
			win.formStatus = 'edit';
			return false;
		}

		if (
			getRegionNick() == 'adygeya'
			&& base_form.findField('EvnPLDispTeenInspection_IsSuspectZNO').getValue() == 2
			&& base_form.findField('HealthKind_id').getValue() == 1
		) {
			sw.swMsg.alert('Ошибка', 'Нельзя выбрать I группу здоровья при подозрении на ЗНО.');
			return false;
		}

		if (
			getRegionNick() == 'pskov'
			&& base_form.findField('HealthKind_id').getValue() > 2
			&& (
				this.DispAppointGrid.getGrid().getStore().getCount() == 0
				|| Ext.isEmpty(this.DispAppointGrid.getGrid().getStore().getAt(0).get('DispAppointType_id'))
			)
		) {
			sw.swMsg.alert(langs('Ошибка'), 'Раздел «Назначения» должен содержать хотя бы одну запись, так как указана группа здоровья III, IV или V.');
			win.formStatus = 'edit';
			return false;
		}

		// Проверяем заполнение данных в диагнозах и рекомендациях по результатам диспансеризации / профосмотра
		// @task https://redmine.swan.perm.ru/issues/77880
		if (
			base_form.findField('EvnPLDispTeenInspection_IsFinish').getValue() == 2 
			&& EvnDiagAndRecomendationGrid.getStore().getCount() > 0
			&& !Ext.isEmpty(EvnDiagAndRecomendationGrid.getStore().getAt(0).get('EvnVizitDispDop_id'))
			&& !getRegionNick().inlist([ 'ufa' ])
		) {
			var
				FormDataJSON,
				noRequiredData = false;

			EvnDiagAndRecomendationGrid.getStore().each(function(rec) {
				if ( Ext.isEmpty(rec.get('FormDataJSON')) ) {
					noRequiredData = true;
				}
				else {
					FormDataJSON = Ext.util.JSON.decode(rec.get('FormDataJSON'));

					if ( Ext.isEmpty(FormDataJSON.DispSurveilType_id) || Ext.isEmpty(FormDataJSON.EvnVizitDisp_IsFirstTime) || Ext.isEmpty(FormDataJSON.EvnVizitDisp_IsVMP) ) {
						noRequiredData = true;
					}
				}
			});

			if ( noRequiredData == true ) {
				sw.swMsg.alert('Ошибка', 'Не заполнены обязательные поля в разделе «Диагнозы и рекомендации по результатам диспансеризации / профосмотра».');
				win.formStatus = 'edit';
				return false;
			}
		}

		var Org_field = win.EvnPLDispTeenInspectionFormPanel.getForm().findField('Org_id');
		if (!Ext.isEmpty(Org_field.getValue()) && Ext.isEmpty(Org_field.getFieldValue('OrgStac_Code')) && this.orpAdoptedMOEmptyCode && (getRegionNick() != 'perm')) {

			sw.swMsg.show({
				icon: Ext.MessageBox.QUESTION,
				msg: 'У выбранного образовательного учреждения отсутствует федеральный код. Сохранить?',
				title: 'Вопрос',
				buttons: Ext.Msg.YESNO,
				fn: function(buttonId, text, obj) {
					if ('yes' == buttonId) {
						win.orpAdoptedMOEmptyCode = false;
						win.doSave(options);
					}
				}
			});
			win.formStatus = 'edit';
			return false;
		}

		var Org_EndDT = win.EvnPLDispTeenInspectionFormPanel.getForm().findField('Org_id').getFieldValue('Org_endDate');

		if (!Ext.isEmpty(Org_EndDT)) {
			var rOrg_EndDT = Org_EndDT.split('.').reverse().join('.'),
				curDate = new Date(),
				CurYear = curDate.getFullYear(),
				firstDayOfYear = ('01.01.'+CurYear.toString()).split('.').reverse().join('.');

				if ( !Ext.isEmpty(rOrg_EndDT) && rOrg_EndDT < firstDayOfYear && this.orpAdoptedMODateincorrect) {
				var msg = 'У выбранного образовательного учреждения указана дата закрытия '+ Org_EndDT +'. Сохранить?';

				sw.swMsg.show({
					icon: Ext.MessageBox.QUESTION,
					msg: msg,
					title: 'Вопрос',
					buttons: Ext.Msg.YESNO,
					fn: function(buttonId, text, obj) {
						if ('yes' == buttonId) {
							win.orpAdoptedMODateincorrect = false;
							win.doSave(options);
						}
					}
				});
				win.formStatus = 'edit';
				return false;
			}
		}

		if ( Ext.isEmpty(win.findById('EPLDTIPRO_EvnPLDispTeenInspection_consDate').getValue()) ) {
			win.getLoadMask().hide();

			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					win.findById('EPLDTIPRO_EvnPLDispTeenInspection_consDate').focus(true);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			win.formStatus = 'edit';
			return false;
		}

        if ( Ext.isEmpty(win.findById('EPLDTIPRO_EvnPLDispTeenInspection_setDate').getValue()) ) {
            win.getLoadMask().hide();

            sw.swMsg.show({
                buttons: Ext.Msg.OK,
                fn: function() {
                    win.findById('EPLDTIPRO_EvnPLDispTeenInspection_setDate').focus(true);
                },
                icon: Ext.Msg.WARNING,
                msg: ERR_INVFIELDS_MSG,
                title: ERR_INVFIELDS_TIT
            });
			win.formStatus = 'edit';
            return false;
        }

		win.verfGroup();
		
		base_form.findField('EvnPLDispTeenInspection_consDate').setValue(typeof win.findById('EPLDTIPRO_EvnPLDispTeenInspection_consDate').getValue() == 'object' ? Ext.util.Format.date(win.findById('EPLDTIPRO_EvnPLDispTeenInspection_consDate').getValue(), 'd.m.Y') : win.findById('EPLDTIPRO_EvnPLDispTeenInspection_consDate').getValue());

		base_form.findField('EvnPLDispTeenInspection_setDate').setValue(typeof win.findById('EPLDTIPRO_EvnPLDispTeenInspection_setDate').getValue() == 'object' ? Ext.util.Format.date(win.findById('EPLDTIPRO_EvnPLDispTeenInspection_setDate').getValue(), 'd.m.Y') : win.findById('EPLDTIPRO_EvnPLDispTeenInspection_setDate').getValue());

		if( !Ext.isEmpty(base_form.findField('InvalidType_id').getValue()) && base_form.findField('InvalidType_id').getValue().inlist(['2','3']) ) {
			if (
				!(
					base_form.findField('AssessmentHealth_IsMental').checked ||
					base_form.findField('AssessmentHealth_IsOtherPsych').checked ||
					base_form.findField('AssessmentHealth_IsLanguage').checked ||
					base_form.findField('AssessmentHealth_IsVestibular').checked ||
					base_form.findField('AssessmentHealth_IsVisual').checked ||
					base_form.findField('AssessmentHealth_IsMeals').checked ||
					base_form.findField('AssessmentHealth_IsMotor').checked ||
					base_form.findField('AssessmentHealth_IsDeform').checked ||
					base_form.findField('AssessmentHealth_IsGeneral').checked
				)
			) {
				sw.swMsg.show({
					buttons: Ext.Msg.OK,
					icon: Ext.Msg.WARNING,
					msg: 'При выбранной инвалидности необходимо заполнить блок "Виды нарушений" хотя бы одним значением',
					title: 'Ошибка'
				});
				win.formStatus = 'edit';
				return false;
			}
		}

		if (!options.ignoreAgeGroup && getRegionNick() == 'ekb' && win.calculatedAgeGroup && base_form.findField('AgeGroupDisp_id').getValue() != win.calculatedAgeGroup) {
			sw.swMsg.show({
				buttons: Ext.Msg.YESNO,
				fn: function ( buttonId ) {
					if ( buttonId == 'yes' )
					{
						options.ignoreAgeGroup = true;
						win.doSave(options);
					}
					else
					{
						return false;
					}
				},
				msg: 'Выбранная возрастная группа не соответствует возрасту пациента. Продолжить?',
				title: 'Предупреждение'
			});
			win.formStatus = 'edit';
			return false;
		}

		//проверки из задачи https://redmine.swan.perm.ru/issues/74660
		if(getGlobalOptions().disp_control != 1) //Если выбрано предупреждение или запрет
		{
			//Получаем возраст и пол:
			var age = base_form.findField('AgeGroupDisp_id').getFieldValue('AgeGroupDisp_From');
			var sex_id = win.PersonInfoPanel.getFieldValue('Sex_id');//1-муж, 2-жен
			var fields_list = "";
			if ( age >= 0 && age <= 4 ){
				if(Ext.isEmpty(base_form.findField('AssessmentHealth_Gnostic').getValue()))
					fields_list += 'Познавательная функция <br>';
				if(Ext.isEmpty(base_form.findField('AssessmentHealth_Motion').getValue()))
					fields_list += 'Моторная функция <br>';
				if(Ext.isEmpty(base_form.findField('AssessmentHealth_Social').getValue()))
					fields_list += 'Эмоциональная и социальная функции <br>';
				if(Ext.isEmpty(base_form.findField('AssessmentHealth_Speech').getValue()))
					fields_list += 'Предречевое и речевое развитие <br>';
			}
			if(age >= 5)
			{
				if(Ext.isEmpty(base_form.findField('NormaDisturbanceType_id').getValue()))
					fields_list += 'Психомоторная сфера <br>';
				if(Ext.isEmpty(base_form.findField('NormaDisturbanceType_uid').getValue()))
					fields_list += 'Интеллект <br>';
				if(Ext.isEmpty(base_form.findField('NormaDisturbanceType_eid').getValue()))
					fields_list += 'Эмоционально-вегетативная сфера <br>';
			}
			if(age >= 10)
			{
				if(Ext.isEmpty(base_form.findField('AssessmentHealth_P').getValue()))
					fields_list += 'P <br>';
				if(Ext.isEmpty(base_form.findField('AssessmentHealth_Ax').getValue()))
					fields_list += 'Ax <br>';
				if(sex_id == 1 && Ext.isEmpty(base_form.findField('AssessmentHealth_Fa').getValue()))
					fields_list += 'Fa <br>';
				if(sex_id == 2 && Ext.isEmpty(base_form.findField('AssessmentHealth_Ma').getValue()))
					fields_list += 'Ma <br>';
				if(sex_id == 2 && Ext.isEmpty(base_form.findField('AssessmentHealth_Me').getValue()))
					fields_list += 'Me <br>';
			}

			if(Ext.isEmpty(base_form.findField('AssessmentHealth_Weight').getValue())) {
				fields_list += 'Масса (кг) <br>';
			}

			if(Ext.isEmpty(base_form.findField('AssessmentHealth_Height').getValue())) {
				fields_list += 'Рост (см) <br>';
			}

			if(Ext.isEmpty(this.PersonInfoPanel.getFieldValue('DocumentType_id'))) {
				fields_list += 'Тип документа <br>';
			}

			if(Ext.isEmpty(this.PersonInfoPanel.getFieldValue('Document_Num'))) {
				fields_list += 'Номер документа <br>';
			}

			// в зависимости от типа полиса номер или единый номер
			if(Ext.isEmpty(this.PersonInfoPanel.getFieldValue('Polis_Num'))) {
				fields_list += 'Номер полиса <br>';
			}

			if(Ext.isEmpty(this.PersonInfoPanel.getFieldValue('UAddress_id')) && Ext.isEmpty(this.PersonInfoPanel.getFieldValue('PAddress_id'))) {
				fields_list += 'Адрес регистрации/проживания <br>';
			}

			if(fields_list.length > 0)
			{
				if(getGlobalOptions().disp_control == 2 && !win.ignoreEmptyFields)
				{
					sw.swMsg.show({
						buttons: Ext.Msg.YESNO,
						fn: function ( buttonId ) {
							if ( buttonId == 'yes' )
							{
								win.ignoreEmptyFields = true;
								win.doSave(options);
							}
							else
							{
								return false;
							}
						},
						msg: 'Внимание! Не заполнены поля, обязательные при экспорте на федеральный портал: <br>' + fields_list + '<br> Сохранить?',
						title: 'Предупреждение'
					});
					win.formStatus = 'edit';
					return false;
				}
				if(getGlobalOptions().disp_control == 3)
				{
					sw.swMsg.alert('Ошибка', 'Не заполнены поля, обязательные при экспорте на федеральный портал: <br>' + fields_list);
					win.formStatus = 'edit';
					return false;
				}
			}

		}

		var EvnPLDispDop_setDate = win.findById('EPLDTIPRO_EvnPLDispTeenInspection_setDate').getValue();

		// При сохранении карты диспансеризации реализовать контроль: Дата оказания любой услуги (осмотра/исследования) должна быть не меньше, чем за месяц до осмотра
		// врача-терапевта. При невыполнении данного контроля выводить сообщение: "Дата любого исследования не может быть раньше, чем 1 месяц до даты осмотра врача-педиатра (ВОП)", сохранение отменить.
		var EvnUslugaDispDop_minDate, EvnUslugaDispDop_pedDate, EvnUslugaDispDop_maxDate, EvnUslugaDispDop_fluDate, EvnUslugaDispDop_audiolDate;
		var age = win.PersonInfoPanel.getFieldValue('Person_Age');

		var ErrorPedMsg = 'Дата любого исследования не может быть раньше, чем 1 месяц до даты осмотра врача-педиатра (ВОП)';
		var monthPed = 1;
		if (age >= 2) {
			ErrorPedMsg = 'Дата любого исследования не может быть раньше, чем 3 месяца до даты осмотра врача-педиатра (ВОП)';
			monthPed = 3;
		}

		// Вытаскиваем минимальную дату услуги и дату осмотра врачом терапевтом, а также дату проведения флюорографии
		this.evnUslugaDispDopGrid.getGrid().getStore().each(function(rec) {
			if ( !Ext.isEmpty(rec.get('EvnUslugaDispDop_didDate')) ) {
				if ( rec.get('SurveyType_Code') == 16 ) {
					EvnUslugaDispDop_fluDate = rec.get('EvnUslugaDispDop_didDate');
				} else if ( rec.get('SurveyType_Code') == 52 ) {
					EvnUslugaDispDop_audiolDate = rec.get('EvnUslugaDispDop_didDate');
				} else if ( rec.get('SurveyType_Code') == 27 ) {
					EvnUslugaDispDop_pedDate = rec.get('EvnUslugaDispDop_didDate');
				}
				else {
					if ( Ext.isEmpty(EvnUslugaDispDop_minDate) || EvnUslugaDispDop_minDate > rec.get('EvnUslugaDispDop_didDate') ) {
						EvnUslugaDispDop_minDate = rec.get('EvnUslugaDispDop_didDate');
					}

					if ( Ext.isEmpty(EvnUslugaDispDop_maxDate) || EvnUslugaDispDop_maxDate < rec.get('EvnUslugaDispDop_didDate') ) {
						EvnUslugaDispDop_maxDate = rec.get('EvnUslugaDispDop_didDate');
					}
				}
			}
		});

		/*if ( getRegionNick().inlist([ 'ekb' ]) ) {
			// Для Екатеринбруга При сохранении карты ДВН, если в поле «Случай диспансеризации 1 этап закончен» выбрано значение «Да», то должны быть сохранены все осмотры / исследования, для которых в связанных услугах SurveyTypeLink_IsNeedUsluga = Yes. При невыполнении данного контроля выводить сообщение «Сохранены не все обязательные осмотры / исследования. ОК» , сохранение карты отменить.
			if ( base_form.findField('EvnPLDispTeenInspection_IsFinish').getValue() == 2 ) {
				// считаем количество сохраненных осмотров/исследований
				var savedAll = true;
				var ddicar = new Array();

				win.evnUslugaDispDopGrid.getGrid().getStore().each(function(rec) {
					if (!Ext.isEmpty(rec.get('DopDispInfoConsent_id'))) {
						if ( !Ext.isEmpty(rec.get('EvnUslugaDispDop_didDate')) ) {
							ddicar.push(rec.get('DopDispInfoConsent_id'));
						}
					}
				});

				win.dopDispInfoConsentGrid.getGrid().getStore().each(function(rec) {
					if ( rec.get('SurveyTypeLink_IsNeedUsluga') == 2 ) {
						if (ddicar.indexOf(rec.get('DopDispInfoConsent_id')) < 0) {
							savedAll = false;
						}
					}
				});

				if (!savedAll) {
					sw.swMsg.alert('Ошибка', 'Сохранены не все обязательные осмотры / исследования.');
					win.formStatus = 'edit';
					return false;
				}
			}
		} else*/ if ( getRegionNick().inlist([ 'buryatiya' ]) ) {
			var bxdate = new Date(2016, 6, 1); // 01.07.2016
			if ( base_form.findField('EvnPLDispTeenInspection_IsFinish').getValue() == 2 && !Ext.isEmpty(EvnUslugaDispDop_pedDate) && EvnUslugaDispDop_pedDate >= bxdate ) {

				// считаем количество сохраненных осмотров/исследований
				var kolvo = 0;
				var kolvoAgree = 0;
				win.evnUslugaDispDopGrid.getGrid().getStore().each(function(rec) {
					if (rec.get('SurveyType_Code').inlist([51,52,55,56,59,62])) {
						kolvoAgree++;
						kolvo++;
					} else if (!Ext.isEmpty(rec.get('DopDispInfoConsent_id'))) {
						kolvo++;
						if ( !Ext.isEmpty(rec.get('EvnUslugaDispDop_didDate')) ) {
							kolvoAgree++;
						}
					}
				});
				if (kolvoAgree < kolvo) {
					sw.swMsg.alert('Ошибка', 'Случай не может быть закончен, так как заполнены не все исследования или осмотры.');
					win.formStatus = 'edit';
					return false;
				}
			}
		} else if ( getRegionNick().inlist([ 'kareliya', 'penza' ])) {
			if ( base_form.findField('EvnPLDispTeenInspection_IsFinish').getValue() == 2 ) {

				// считаем количество сохраненных осмотров/исследований
				var kolvo = 0;
				var kolvoAgree = 0;
				win.evnUslugaDispDopGrid.getGrid().getStore().each(function(rec) {
					if (!Ext.isEmpty(rec.get('DopDispInfoConsent_id'))) {
						kolvo++;
						if ( !Ext.isEmpty(rec.get('EvnUslugaDispDop_didDate')) ) {
							kolvoAgree++;
						}
					}
				});
				var xdate = new Date(2015, 0, 1);
				if (EvnPLDispDop_setDate >= xdate && kolvoAgree < kolvo) {
					sw.swMsg.alert('Предупреждение', 'Заведены не все требуемые исследования и осмотры, карта не будет оплачена. Для получения оплаты за осмотры, заведите их в ЭМК, как посещения.');
				} else if (kolvoAgree < Math.round(kolvo*0.85)) {
					sw.swMsg.alert('Предупреждение', 'Заведено меньше 85% от требуемых исследований и осмотров, случай не будет оплачен. Для получения оплаты за посещения специалистов, занесите их в ЭМК.');
				}
			}
		} else {
			if ( base_form.findField('EvnPLDispTeenInspection_IsFinish').getValue() == 2 ) {

				// считаем количество сохраненных осмотров/исследований
				var kolvo = 0;
				var kolvoAgree = 0;
				win.evnUslugaDispDopGrid.getGrid().getStore().each(function(rec) {
					if (rec.get('SurveyType_Code').inlist([51,52,55,56,59,62])) {
						kolvoAgree++;
						kolvo++;
					} else if (!Ext.isEmpty(rec.get('DopDispInfoConsent_id'))) {
						kolvo++;
						if ( !Ext.isEmpty(rec.get('EvnUslugaDispDop_didDate')) ) {
							kolvoAgree++;
						}
					}
				});
				if (kolvoAgree < kolvo) {
					sw.swMsg.alert('Ошибка', 'Случай не может быть закончен, так как заполнены не все исследования или осмотры.');
					win.formStatus = 'edit';
					return false;
				}
			}
		}

		if ( getRegionNick() == 'buryatiya' && base_form.findField('EvnPLDispTeenInspection_IsFinish').getValue() == 2 && Ext.isEmpty(EvnUslugaDispDop_pedDate) ) {
			sw.swMsg.alert('Ошибка', 'Дата выполнения осмотра врача педиатра обязательна для заполнения.');
			win.formStatus = 'edit';
			return false;
		}

		if ( !Ext.isEmpty(EvnUslugaDispDop_minDate) && !Ext.isEmpty(EvnUslugaDispDop_pedDate) && EvnUslugaDispDop_minDate < EvnUslugaDispDop_pedDate.add(Date.MONTH, -monthPed) ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					//
				},
				icon: Ext.Msg.ERROR,
				msg: ErrorPedMsg,
				title: 'Ошибка'
			});
			win.formStatus = 'edit';
			return false;
		}
		
		// http://redmine.swan.perm.ru/issues/21226
		// Дата исследования "Флюорография" не может быть меньше 12 месяца, чем дата осмотра врача-педиатра. При невыполнении выводить сообщение "Дата исследования 
		// "Флюорография" не может быть раньше, чем 12 месяцев до даты осмотра врача-педиатра. ОК". Сохранение отменить.
		if ( !Ext.isEmpty(EvnUslugaDispDop_fluDate) && !Ext.isEmpty(EvnUslugaDispDop_pedDate) && EvnUslugaDispDop_fluDate < EvnUslugaDispDop_pedDate.add(Date.MONTH, -12) ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.ERROR,
				msg: 'Дата исследования "Флюорография" не может быть раньше, чем 12 месяцев до даты осмотра врача-педиатра.',
				title: 'Ошибка'
			});
			win.formStatus = 'edit';
			return false;
		}

		// http://redmine.swan.perm.ru/issues/88167
		// Дата исследования "Аудиологический скрининг" не может быть меньше 3 месяцев, чем дата осмотра врача-педиатра. При невыполнении выводить сообщение "Дата исследования
		// "Аудиологический скрининг" не может быть раньше, чем 3 месяца до даты осмотра врача-педиатра. ОК". Сохранение отменить.
		if ( !Ext.isEmpty(EvnUslugaDispDop_audiolDate) && !Ext.isEmpty(EvnUslugaDispDop_pedDate) && EvnUslugaDispDop_audiolDate < EvnUslugaDispDop_pedDate.add(Date.MONTH, -3) ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.ERROR,
				msg: 'Дата исследования "Аудиологический скрининг" не может быть раньше, чем 3 месяца до даты осмотра врача-педиатра.',
				title: 'Ошибка'
			});
			win.formStatus = 'edit';
			return false;
		}
		
		// @task https://redmine.swan.perm.ru/issues/111587
		if ( getRegionNick() == 'ekb' ) {
			if ( age < 2 && !Ext.isEmpty(EvnUslugaDispDop_pedDate) && EvnUslugaDispDop_pedDate.add(Date.DAY, -30) > EvnPLDispDop_setDate ) {
				sw.swMsg.show({
					buttons: Ext.Msg.OK,
					icon: Ext.Msg.ERROR,
					msg: "Длительность 1 этапа профилактического осмотра не может быть больше 30 календарных дней.",
					title: lang['oshibka']
				});
				win.formStatus = 'edit';
				return false;
			}

			if ( age >= 2 && !Ext.isEmpty(EvnUslugaDispDop_pedDate) && EvnUslugaDispDop_pedDate.add(Date.DAY, -90) > EvnPLDispDop_setDate ) {
				sw.swMsg.show({
					buttons: Ext.Msg.OK,
					icon: Ext.Msg.ERROR,
					msg: "Длительность 1 этапа профилактического осмотра не может быть больше 90 календарных дней.",
					title: lang['oshibka']
				});
				win.formStatus = 'edit';
				return false;
			}
		}
		// @task https://redmine.swan.perm.ru/issues/146666
		else if ( getRegionNick() == 'krym' ) {
			if ( !options.ignoreOsmotrDlit ) {
				var xdate = new Date(2018, 0, 1);
				if (EvnPLDispDop_setDate >= xdate) {
					// Дата осмотра врача-педиатра не может быть больше 20 рабочих дней, чем дата начала диспансеризации (отдельное поле есть в карте). При невыполнении контроля выводить
					// сообщение "Длительность 1 этапа диспансеризации несовершеннолетнего не может быть больше 20 рабочих дней. Продолжить сохранение?. Да/Нет".
					// При нажатии «Да» сообщение закрывается и производится сохранение. При нажатии «Нет» сообщение закрывается, сохранение отменяется.
					if (!Ext.isEmpty(EvnUslugaDispDop_pedDate) && EvnUslugaDispDop_pedDate.add(Date.DAY, -28) > EvnPLDispDop_setDate) {
						sw.swMsg.show({
							buttons: Ext.Msg.YESNO,
							fn: function ( buttonId ) {
								if ( buttonId == 'yes' ) {
									options.ignoreOsmotrDlit = true;
									win.doSave(options);
								}
								else {
									return false;
								}
							},
							msg: 'Длительность 1 этапа диспансеризации несовершеннолетнего не может быть больше 20 рабочих дней. Продолжить сохранение?',
							title: 'Предупреждение'
						});
						win.formStatus = 'edit';
						return false;
					}
				} else {
					// Дата осмотра врача-педиатра не может быть больше 10 рабочих дней, чем дата начала диспансеризации (отдельное поле есть в карте). При невыполнении контроля выводить
					// сообщение "Длительность 1 этапа диспансеризации несовершеннолетнего не может быть больше 10 рабочих дней. Продолжить сохранение?. Да/Нет".
					// При нажатии «Да» сообщение закрывается и производится сохранение. При нажатии «Нет» сообщение закрывается, сохранение отменяется.
					if (!Ext.isEmpty(EvnUslugaDispDop_pedDate) && EvnUslugaDispDop_pedDate.add(Date.DAY, -14) > EvnPLDispDop_setDate) {
						sw.swMsg.show({
							buttons: Ext.Msg.YESNO,
							fn: function ( buttonId ) {
								if ( buttonId == 'yes' ) {
									options.ignoreOsmotrDlit = true;
									win.doSave(options);
								}
								else {
									return false;
								}
							},
							msg: 'Длительность 1 этапа диспансеризации несовершеннолетнего не может быть больше 10 рабочих дней. Продолжить сохранение?',
							title: 'Предупреждение'
						});
						win.formStatus = 'edit';
						return false;
					}
				}
			}
		}
		else {
			var xdate = new Date(2018, 0, 1);
			if (EvnPLDispDop_setDate >= xdate) {
				// Дата осмотра врача-педиатра не может быть больше 28 дней, чем дата начала диспансеризации (отдельное поле есть в карте). При невыполнении контроля выводить
				// сообщение "Длительность 1 этапа диспансеризации несовершеннолетнего не может больше 20 рабочих дней. ОК". Сохранение отменить
				if (!Ext.isEmpty(EvnUslugaDispDop_pedDate) && EvnUslugaDispDop_pedDate.add(Date.DAY, -28) > EvnPLDispDop_setDate) {
					sw.swMsg.show({
						buttons: Ext.Msg.OK,
						icon: Ext.Msg.ERROR,
						msg: "Длительность 1 этапа профилактического осмотра не может больше 20 рабочих дней.",
						title: 'Ошибка'
					});
					win.formStatus = 'edit';
					return false;
				}
			} else {
				// Дата осмотра врача-педиатра не может быть больше 14 дней, чем дата начала диспансеризации (отдельное поле есть в карте). При невыполнении контроля выводить
				// сообщение "Длительность 1 этапа диспансеризации несовершеннолетнего не может больше 10 рабочих дней. ОК". Сохранение отменить
				if (!Ext.isEmpty(EvnUslugaDispDop_pedDate) && EvnUslugaDispDop_pedDate.add(Date.DAY, -14) > EvnPLDispDop_setDate) {
					sw.swMsg.show({
						buttons: Ext.Msg.OK,
						icon: Ext.Msg.ERROR,
						msg: "Длительность 1 этапа профилактического осмотра не может больше 10 рабочих дней.",
						title: 'Ошибка'
					});
					win.formStatus = 'edit';
					return false;
				}
			}
		}
		
		// Дата осмотра врача-педиатра (ВОП) должна быть больше (равна) дате начала диспансеризации. При невыполнении данного контроля выводить сообщение:
		// "Дата окончания диспансеризации не может быть меньше даты начала диспансеризации. ОК". Сохранение отменить.
		// https://redmine.swan.perm.ru/issues/44504
		if ( !Ext.isEmpty(EvnUslugaDispDop_pedDate) && EvnPLDispDop_setDate > EvnUslugaDispDop_pedDate ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.ERROR,
				msg: "Дата окончания диспансеризации не может быть меньше даты начала диспансеризации.",
				title: 'Ошибка'
			});
			win.formStatus = 'edit';
			return false;
		}

		// Дата осмотра врача-педиатра должна быть больше (равна) датам всех остальных осмотров / исследований. При невыполнение данного контроля выводить сообщение:
		// "Дата осмотра / исследования по диспансеризации несовершеннолетнего не может быть больше даты осмотра врача-педиатра. ОК ". Сохранение карты отменить.
		if ( !Ext.isEmpty(EvnUslugaDispDop_maxDate) && !Ext.isEmpty(EvnUslugaDispDop_pedDate) && EvnUslugaDispDop_maxDate > EvnUslugaDispDop_pedDate ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.ERROR,
				msg: "Дата любого осмотра/исследования не может быть больше даты осмотра врача-педиатра (ВОП).",
				title: 'Ошибка'
			});
			win.formStatus = 'edit';
			return false;
		}

		var params = new Object();
		params.AgeGroupDisp_id = base_form.findField('AgeGroupDisp_id').getValue();

		var vaccinSelected = [];
		var vaccinFieldset = this.findById('EPLDTIPRO_VaccinFieldset');
		vaccinFieldset.items.items.forEach( function (item) {
			if (item.checked) {
				vaccinSelected.push(item.value);
			}
		});
		params.AssessmentHealthVaccinData = Ext.util.JSON.encode(vaccinSelected);

		// если "Проведение профилактических прививок" == "нуждается в проведении вакцинации (ревакцинации)", обязателен хотя бы один чекбокс
		if (base_form.findField('ProfVaccinType_id').getValue() == 6 && vaccinSelected.length <= 0) {
			sw.swMsg.alert('Ошибка', 'Для пациента, нуждающегося в проведении вакцинации (ревакцинации), должна быть выбрана хотя бы одна прививка.');
			win.formStatus = 'edit';
			return false;
		}

		var DispAppointGridStore = win.DispAppointGrid.getGrid().getStore();

		DispAppointGridStore.clearFilter();

		if ( DispAppointGridStore.getCount() > 0 ) {
			var DispAppointData = getStoreRecords(DispAppointGridStore, {
				exceptionFields: [
					'DispAppointType_Name',
					'DispAppoint_Comment'
				]
			});

			params.DispAppointData = Ext.util.JSON.encode(DispAppointData);

			win.filterDispAppointGrid();
		}

		params.checkAttributeforLpuSection = (!Ext.isEmpty(options.checkAttributeforLpuSection)) ? options.checkAttributeforLpuSection : 0;
		if(base_form.findField('EvnPLDispTeenInspection_IsMobile').checked){
			params.checkAttributeforLpuSection=2;
		}
		
		win.getLoadMask("Подождите, идет сохранение...").show();

		EvnPLDispTeenInspection_form.getForm().submit({
			failure: function(result_form, action) {
				win.getLoadMask().hide();
				win.formStatus = 'edit';
				if (action.result){
					if (action.result.Alert_Msg) {
						sw.swMsg.show({
							buttons: Ext.Msg.YESNO,
							fn: function(buttonId, text, obj) {
								if ( buttonId == 'yes' ) {
									switch (action.result.Error_Code) {
										case 110:
											options.checkAttributeforLpuSection = 2;
											break;
									}

									win.doSave(options);

								}else{
									switch (action.result.Error_Code) {
										case 110:
											options.checkAttributeforLpuSection = 1;
											win.doSave(options);
											break;
									}
								}
							}.createDelegate(this),
							icon: Ext.MessageBox.QUESTION,
							msg: action.result.Alert_Msg,
							title: lang['prodoljit_sohranenie']
						});
					} else{
						Ext.Msg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_1]']);
					}
				}
			},
			params: params,
			success: function(result_form, action) {
				win.getLoadMask().hide();
				win.formStatus = 'edit';
				
				if (action.result)
				{
					win.callback({evnPLDispTeenInspectionData: win.getDataForCallBack()});

					if (options.callback) {
						options.callback();
					} else {
						win.hide();
					}
				}
				else
				{
					Ext.Msg.alert('Ошибка', 'При сохранении произошли ошибки [Тип ошибки: 2]');
				}
			}
		});
	},
	height: 570,
	id: 'EvnPLDispTeenInspectionProfEditWindow',
	showEvnUslugaDispDopEditWindow: function(action) {
		var base_form = this.EvnPLDispTeenInspectionFormPanel.getForm();
		var grid = this.evnUslugaDispDopGrid.getGrid();
		var win = this;
		
		var record = grid.getSelectionModel().getSelected();
		
		if ( !record || !record.get('DopDispInfoConsent_id') ) {
			return false;
		}
		
		if (!win.action.inlist(['add','edit'])) {
			action = 'view';
		}
		
		// если опрос то открываем форму анкетирования.
		if (record.get('SurveyType_Code') == 2) {
			getWnd('swDopDispQuestionEditWindow').show({
				archiveRecord: this.archiveRecord,
				action: action,
				object: 'EvnPLDispTeenInspection',
				DopDispQuestion_setDate: record.get('EvnUslugaDispDop_didDate'),
				EvnPLDisp_id: base_form.findField('EvnPLDispTeenInspection_id').getValue(),
				EvnUslugaDispDop_id: record.get('EvnUslugaDispDop_id'),
				onHide: Ext.emptyFn,
				callback: function(qdata) {
					// обновить грид
					grid.getStore().reload();
					// сюда приходит ответ по нажатию кнопки расчёт на форме анкетирования => нужно заполнить соответсвующие поля на форме.
				}
				
			});
		// иначе форму услуги
		} else {
			var personinfo = win.PersonInfoPanel;

			var AgeGroupDispRecord = false;
			if (getRegionNick() == 'pskov') {
				index = base_form.findField('AgeGroupDisp_id').getStore().findBy(function (rec) {
					return (rec.get('AgeGroupDisp_id') == base_form.findField('AgeGroupDisp_id').getValue());
				});
				if ( index >= 0 ) {
					AgeGroupDispRecord = base_form.findField('AgeGroupDisp_id').getStore().getAt(index);
				}
			}

			getWnd('swEvnUslugaDispDop13EditWindow').show({
				archiveRecord: this.archiveRecord,
				action: action,
				object: 'EvnPLDispTeenInspection',
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
				AgeGroupDispRecord: AgeGroupDispRecord,
				formParams: {
					DopDispInfoConsent_id: record.get('DopDispInfoConsent_id'),
					EvnVizitDispDop_pid: base_form.findField('EvnPLDispTeenInspection_id').getValue(),
					PersonEvn_id: base_form.findField('PersonEvn_id').getValue(),
					Server_id: base_form.findField('Server_id').getValue(),
					EvnUslugaDispDop_id: record.get('EvnUslugaDispDop_id')
				},
				DopDispInfoConsent_id: record.get('DopDispInfoConsent_id'),
				SurveyTypeLink_id: record.get('SurveyTypeLink_id'),
				SurveyType_Code: record.get('SurveyType_Code'),
				SurveyType_IsVizit: record.get('SurveyType_IsVizit'),
				OrpDispSpec_Code: record.get('OrpDispSpec_Code'),
				SurveyType_Name: record.get('SurveyType_Name'),
				type: 'DispTeenInspectionProf',
				UslugaComplex_Date: win.findById('EPLDTIPRO_EvnPLDispTeenInspection_setDate').getValue(),
				onHide: Ext.emptyFn,
				callback: function(data) {
					// обновить грид!
					grid.getStore().reload();

					if (getRegionNick() == 'buryatiya' && record.get('SurveyType_Code') == 27) {
						if (data.Diag_Code && data.Diag_Code == 'Z03.1') {
							base_form.findField('EvnPLDispTeenInspection_IsSuspectZNO').setValue(2);
						} else {
							base_form.findField('EvnPLDispTeenInspection_IsSuspectZNO').clearValue();
						}
						base_form.findField('EvnPLDispTeenInspection_IsSuspectZNO').fireEvent('change', base_form.findField('EvnPLDispTeenInspection_IsSuspectZNO'), base_form.findField('EvnPLDispTeenInspection_IsSuspectZNO').getValue());
					}

					// обновляем грид рекомендаций
					win.EvnDiagAndRecomendationGrid.getGrid().getStore().reload();
				}
				
			});
		}		
	},
	openEvnDiagAndRecomendationEditWindow: function(action) {
		var win = this;
		var grid = this.EvnDiagAndRecomendationGrid.getGrid();
		var record = grid.getSelectionModel().getSelected();
		
		if (!record) {
			return false;
		}
		
		if (!win.action.inlist(['add','edit'])) {
			action = 'view';
		}
		
		var params = {
			callback: function(FormDataJSON) {
				// обновляем JSON-поле.
				record.set('FormDataJSON', FormDataJSON);
				record.commit();
				
				var params = {};
				params.FormDataJSON = FormDataJSON;
				params.EvnVizitDispDop_id = record.get('EvnVizitDispDop_id');
				
				win.getLoadMask('Сохранение рекомендаций').show();
				// сохраняем на сервере
				Ext.Ajax.request(
				{
					url: '/?c=EvnPLDispTeenInspection&m=saveEvnDiagAndRecomendation',
					params: params,
					failure: function(response, options)
					{
						win.getLoadMask().hide();
					},
					success: function(response, action)
					{
						win.getLoadMask().hide();
					}
				});
			},
			FormDataJSON: record.get('FormDataJSON'),
			Diag_id: record.get('Diag_id'),
			action: action
		};
		
		if (getWnd('swEvnDiagAndRecomendationEditWindow').isVisible())
		{
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: Ext.emptyFn,
				icon: Ext.Msg.WARNING,
				msg: 'Окно "Состояние здоровья: Редактирование" уже открыто',
				title: ERR_WND_TIT
			});
			return false;
		}
		params.archiveRecord = this.archiveRecord;
		getWnd('swEvnDiagAndRecomendationEditWindow').show(params);
	},
	deleteEvnDiagDopDispAndRecomendation: function() {
		var win = this;
		var base_form = this.EvnPLDispTeenInspectionFormPanel.getForm();
		var grid = this.EvnDiagDopDispAndRecomendationGrid.getGrid();
		
		if (!win.action.inlist(['add','edit'])) {
			return false;
		}
		
		var record = grid.getSelectionModel().getSelected();

		if (!record || Ext.isEmpty(record.get('EvnDiagDopDisp_id'))) {
			return false;
		}
		
		win.getLoadMask('Удаление диагноза').show();
		Ext.Ajax.request(
		{
			url: '/?c=EvnDiagDopDisp&m=delEvnDiagDopDisp',
			params: {
				EvnDiagDopDisp_id: record.get('EvnDiagDopDisp_id')
			},
			failure: function(response, options)
			{
				win.getLoadMask().hide();
			},
			success: function(response, action)
			{
				win.getLoadMask().hide();
				grid.getStore().reload();
			}
		});
	},	
	openEvnDiagDopDispAndRecomendationEditWindow: function(action) {
		var win = this;
		var base_form = this.EvnPLDispTeenInspectionFormPanel.getForm();
		var grid = this.EvnDiagDopDispAndRecomendationGrid.getGrid();
		
		if (!win.action.inlist(['add','edit'])) {
			action = 'view';
		}
		
		var params = {
			action: action,
			callback: function() {
				grid.getStore().reload();
			}
		};
		
		if (Ext.isEmpty(base_form.findField('EvnPLDispTeenInspection_id').getValue())) {
			return false;
		}
		
		if (action != 'add') {
			var record = grid.getSelectionModel().getSelected();
			
			if (!record || Ext.isEmpty(record.get('EvnDiagDopDisp_id'))) {
				return false;
			}
			
			params.EvnDiagDopDisp_id = record.get('EvnDiagDopDisp_id');
		}
		
		params.EvnDiagDopDisp_pid = base_form.findField('EvnPLDispTeenInspection_id').getValue();
		params.PersonEvn_id = base_form.findField('PersonEvn_id').getValue();
		params.Server_id = base_form.findField('Server_id').getValue();
		
		if (getWnd('swEvnDiagDopDispAndRecomendationEditWindow').isVisible())
		{
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: Ext.emptyFn,
				icon: Ext.Msg.WARNING,
				msg: 'Окно "Состояние здоровья: Редактирование" уже открыто',
				title: ERR_WND_TIT
			});
			return false;
		}
		params.archiveRecord = this.archiveRecord;
		getWnd('swEvnDiagDopDispAndRecomendationEditWindow').show(params);
	},
	setVacNameStat: function(val){
		var base_form = this.EvnPLDispTeenInspectionFormPanel.getForm();
		var vacName = base_form.findField('AssessmentHealth_VaccineName');
		if(val=="6"){
			vacName.setDisabled(false);
		}else{
			vacName.setValue('');
			vacName.setDisabled(true);
		}
		
	},
	onEnableEdit: function(enable) {
		this.EvnDiagDopDispAndRecomendationGrid.setActionDisabled('action_add', !enable);
	},
	initComponent: function() {
		var win = this;
		
		this.dopDispInfoConsentGrid = new sw.Promed.ViewFrame({
			autoLoadData: false,
			id: 'EPLDTIPRO_dopDispInfoConsentGrid',
			dataUrl: '/?c=EvnPLDispTeenInspection&m=loadDopDispInfoConsent',
			region: 'center',
			height: 200,
			title: '',
			toolbar: false,
			saveAtOnce: false, 
			saveAllParams: false, 
			actions: [
				{ name: 'action_add', disabled: true, hidden: true },
				{ name: 'action_edit', disabled: true, hidden: true },
				{ name: 'action_view', disabled: true, hidden: true },
				{ name: 'action_delete', disabled: true, hidden: true },
				{ name: 'action_refresh' },
				{ name: 'action_print', disabled: true, hidden: true },
				{ name: 'action_save', disabled: true, hidden: true }
			],
			stringfields: [
				{ name: 'DopDispInfoConsent_id', type: 'int', header: 'ID', key: true },
				{ name: 'SurveyTypeLink_id', type: 'int', hidden: true },
				{ name: 'SurveyTypeLink_IsNeedUsluga', type: 'int', hidden: true },
				{ name: 'SurveyType_IsVizit', type: 'int', hidden: true },
				{ name: 'SurveyType_Code', type: 'int', hidden: true },
				{ name: 'SurveyTypeLink_IsDel', type: 'int', hidden: true },
				{ name: 'SurveyType_Name', type: 'string', sortable: false, header: 'Осмотр, исследование', id: 'autoexpand' },
				{ name: 'DopDispInfoConsent_IsEarlier', sortable: false, type: 'checkcolumnedit', isparams: true, header: 'Пройдено ранее', width: 180 },
				{ name: 'DopDispInfoConsent_IsAgree', sortable: false, type: 'checkcolumnedit', isparams: true, header: 'Согласие гражданина', width: 180 }
			],
			onLoadData: function() {
				this.checkIsAgree();
				this.doLayout(); // почему то не показывается скролл у грида без этого
			},
			checkIsAgree: function() {
				// проверить согласие для первой строки..
				var record = win.dopDispInfoConsentGrid.getGrid().getStore().getAt(0);

				if (record) {
					win.dopDispInfoConsentGrid.getGrid().getStore().each(function(rec) {
						if ( !Ext.isEmpty(rec.get('SurveyType_Code')) ) {
							if (!rec.get('SurveyType_Code').inlist([50,67,68])) {
								rec.beginEdit();
								if (record.get('DopDispInfoConsent_IsAgree') != true) {
									rec.set('DopDispInfoConsent_IsAgree', 'hidden');
									rec.set('DopDispInfoConsent_IsEarlier', 'hidden');
								} else if (rec.get('DopDispInfoConsent_IsAgree') == 'hidden') {
									// https://redmine.swan.perm.ru/issues/19835
									// 4. Флаги в добровольном согласии по умолчанию должны быть все проставлены как для детей-сирот, так и для взрослого населения.
									// Сейчас в новых картах флаги не проставлены и приходится "протыкивать" всю карту.
									rec.set('DopDispInfoConsent_IsAgree', true);
									rec.set('DopDispInfoConsent_IsEarlier', false);
								}
								
								// если оба отмечены, то снимаем флаг "пройдено ранее", т.к. оба флага не могут быть одновременно подняты
								if (rec.get('DopDispInfoConsent_IsEarlier') == true && rec.get('DopDispInfoConsent_IsAgree') == true) {
									rec.set('DopDispInfoConsent_IsEarlier', false);
								}
								rec.endEdit();
							} else {
								rec.set('DopDispInfoConsent_IsEarlier', 'hidden'); // убрать пройдено ранее для строки первый этап диспансеризации
							}

							rec.commit();
						}
					});
					
					if (record.get('DopDispInfoConsent_IsAgree') != true) {
						win.PersonFirstStageAgree = false;
					} else {
						win.PersonFirstStageAgree = true;
					}
				}
				
				win.checkEvnPLDispTeenInspectionIsSaved();
			},
			onAfterEdit: function(o) {
				if (o && o.field) {
					if (o.record.get('SurveyTypeLink_IsDel') == 2) {
						o.record.set('DopDispInfoConsent_IsAgree', false);
						o.record.set('DopDispInfoConsent_IsEarlier', false);
						o.value = false;
					}
					if (o.field == 'DopDispInfoConsent_IsEarlier' && o.value == true) {
						if (o.record.get('DopDispInfoConsent_IsAgree') != 'hidden') {
							o.record.set('DopDispInfoConsent_IsAgree', false);
						}
					}
					
					if (o.field == 'DopDispInfoConsent_IsAgree' && o.value == true) {
						if (o.record.get('DopDispInfoConsent_IsEarlier') != 'hidden') {
							o.record.set('DopDispInfoConsent_IsEarlier', false);
						}
					}
					
					// при снятии чекбокса в поле этап диспансеризации снимать все остальные и делать недоступными
					if (o.record.get('SurveyType_Code').inlist([50,67,68])) {
						this.checkIsAgree();
					}
				}
			}
		});
		
		this.evnUslugaDispDopGrid = new sw.Promed.ViewFrame({
			autoLoadData: false,
			actions: [
				{ name: 'action_add', disabled: true, hidden: true },
				{ name: 'action_edit', handler: function() { win.showEvnUslugaDispDopEditWindow('edit'); } },
				{ name: 'action_view', handler: function() { win.showEvnUslugaDispDopEditWindow('view'); } },
				{ name: 'action_delete', disabled: true, hidden: true },
				{ name: 'action_refresh' },
				{ name: 'action_print'}
			],
			onLoadData: function() {
				this.setActionDisabled('action_edit', (!win.action.inlist(['add','edit'])));
				this.doLayout();
			},
			id: 'EPLDTIPRO_evnUslugaDispDopGrid',
			dataUrl: '/?c=EvnPLDispTeenInspection&m=loadEvnUslugaDispDopGrid',
			region: 'center',
			height: 200,
			title: '',
			toolbar: true,
			stringfields: [
				{ name: 'DopDispInfoConsent_id', type: 'int', header: 'ID', key: true },
				{ name: 'SurveyTypeLink_id', type: 'int', hidden: true },
				{ name: 'SurveyType_Code', type: 'int', hidden: true },
				{ name: 'SurveyType_IsVizit', type: 'int', hidden: true },
				{ name: 'OrpDispSpec_Code', type: 'int', hidden: true },
				{ name: 'EvnUslugaDispDop_id', type: 'int', hidden: true },
				{ name: 'SurveyType_Name', type: 'string', header: 'Наименование осмотра (исследования)', id: 'autoexpand' },
				{ name: 'EvnUslugaDispDop_ExamPlace', type: 'string', header: 'Место проведения', width: 200 },
				//{ name: 'EvnUslugaDispDop_setDate', renderer: Ext.util.Format.dateRenderer('d.m.Y H:i:s'), header: 'Дата и время проведения', width: 200 },
				{ name: 'EvnUslugaDispDop_didDate', type: 'date', header: 'Дата выполнения', width: 100 },
				{ name: 'EvnUslugaDispDop_WithDirection', type: 'checkbox', header: 'Направление / назначение', width: 100 }
			]
		});
	
		this.PersonInfoPanel = new sw.Promed.PersonInformationPanel({
			additionalFields: [
				'UAddress_id',
				'PAddress_id',
				'DocumentType_id'
			],
			button2Callback: function(callback_data) {
				var base_form = win.EvnPLDispTeenInspectionFormPanel.getForm();
				
				base_form.findField('PersonEvn_id').setValue(callback_data.PersonEvn_id);
				base_form.findField('Server_id').setValue(callback_data.Server_id);
				
				win.PersonInfoPanel.load( { Person_id: callback_data.Person_id, Server_id: callback_data.Server_id } );
			},
			region: 'north'
		});
		
		this.FirstPanel = new sw.Promed.Panel({
			items: [{
				border: false,
				labelWidth: 200,
				layout: 'form',
				style: 'padding: 5px;',
				items: [{
					hidden: getRegionNick() != 'buryatiya',
					layout: 'form',
					border: false,
					items: [{
						hiddenName: 'UslugaComplex_id',
						width: 400,
						fieldLabel: 'Услуга диспансеризации',
						disabled: true,
						emptyText: '',
						nonDispOnly: false,
						xtype: 'swuslugacomplexnewcombo'
					}]
				}, {
					fieldLabel: 'Повторная подача',
					listeners: {
						'check': function(checkbox, value) {
							if ( getRegionNick() != 'perm' ) {
								return false;
							}

							var base_form = win.EvnPLDispTeenInspectionFormPanel.getForm();

							var
								EvnPLDispTeenInspection_IndexRep = parseInt(base_form.findField('EvnPLDispTeenInspection_IndexRep').getValue()),
								EvnPLDispTeenInspection_IndexRepInReg = parseInt(base_form.findField('EvnPLDispTeenInspection_IndexRepInReg').getValue()),
								EvnPLDispTeenInspection_IsPaid = parseInt(base_form.findField('EvnPLDispTeenInspection_IsPaid').getValue());

							var diff = EvnPLDispTeenInspection_IndexRepInReg - EvnPLDispTeenInspection_IndexRep;

							if ( EvnPLDispTeenInspection_IsPaid != 2 || EvnPLDispTeenInspection_IndexRepInReg == 0 ) {
								return false;
							}

							if ( value == true ) {
								if ( diff == 1 || diff == 2 ) {
									EvnPLDispTeenInspection_IndexRep = EvnPLDispTeenInspection_IndexRep + 2;
								}
								else if ( diff == 3 ) {
									EvnPLDispTeenInspection_IndexRep = EvnPLDispTeenInspection_IndexRep + 4;
								}
							}
							else if ( value == false ) {
								if ( diff <= 0 ) {
									EvnPLDispTeenInspection_IndexRep = EvnPLDispTeenInspection_IndexRep - 2;
								}
							}

							base_form.findField('EvnPLDispTeenInspection_IndexRep').setValue(EvnPLDispTeenInspection_IndexRep);
						}
					},
					name: 'EvnPLDispTeenInspection_RepFlag',
					xtype: 'checkbox'
				}, {
					allowBlank: false,
					typeCode: 'int',
					useCommonFilter: true,
					width: 300,
					xtype: 'swpaytypecombo'
				}, {
					disabled: !getRegionNick().inlist(['pskov', 'ekb']),
					allowBlank: false,
					comboSubject: 'AgeGroupDisp',
					fieldLabel: 'Возрастная группа',
					loadParams: {params: {where: "where DispType_id = 4"}},
					hiddenName: 'AgeGroupDisp_id',
					listeners: {
						'change': function(combo, newValue, oldValue) {
							win.onChangeAgeGroupDisp(oldValue, newValue, win.findById('EPLDTIPRO_EvnPLDispTeenInspection_setDate').getValue(), win.findById('EPLDTIPRO_EvnPLDispTeenInspection_setDate').getValue());
						}
					},
					moreFields: [
						{ name: 'AgeGroupDisp_From', mapping: 'AgeGroupDisp_From' },
						{ name: 'AgeGroupDisp_To', mapping: 'AgeGroupDisp_To' },
						{ name: 'AgeGroupDisp_monthFrom', mapping: 'AgeGroupDisp_monthFrom' },
						{ name: 'AgeGroupDisp_monthTo', mapping: 'AgeGroupDisp_monthTo' },
						{ name: 'AgeGroupDisp_begDate', mapping: 'AgeGroupDisp_begDate', type: 'date', dateFormat: 'd.m.Y' },
						{ name: 'AgeGroupDisp_endDate', mapping: 'AgeGroupDisp_endDate', type: 'date', dateFormat: 'd.m.Y' }
					],
					lastQuery: '',
					width: 300,
					xtype: 'swcommonsprcombo'
				}, {
					fieldLabel: 'Обучающийся',
					name: 'OrgExist',
					listeners: {
						'check': function(checkbox, value) {
							var base_form = win.EvnPLDispTeenInspectionFormPanel.getForm();
							
							if ( value == true && win.action != 'view' ) {
								base_form.findField('Org_id').setAllowBlank(false);
								base_form.findField('Org_id').enable();
							} else {
								base_form.findField('Org_id').setAllowBlank(true);
								base_form.findField('Org_id').clearValue();
								base_form.findField('Org_id').disable();
							}
						}
					},
					xtype: 'checkbox'
				}, {
					editable: false,
					allowBlank: true,
					enableKeyEvents: true,
					fieldLabel: 'Образовательное учреждение',
					hiddenName: 'Org_id',
					triggerAction: 'none',
					width: 300,
					xtype: 'sworgcombo',
					onTrigger1Click: function() {
						var combo = this;
						if (combo.disabled) {
							return false;
						}
						getWnd('swOrgSearchWindow').show({
							enableOrgType: true,
							showOrgStacFilters : true,
							onSelect: function(orgData) {
								if ( orgData.Org_id > 0 )
								{
									combo.getStore().load({
										params: {
											Object:'Org',
											Org_id: orgData.Org_id,
											Org_Name:''
										},
										callback: function()
										{
											combo.setValue(orgData.Org_id);
											combo.focus(true, 500);
											combo.fireEvent('change', combo);
										}
									});
								}
								getWnd('swOrgSearchWindow').hide();
							},
							onClose: function() {combo.focus(true, 200)}
						});
					}
				}, {
                    allowBlank: false,
                    fieldLabel: 'Дата начала медицинского осмотра',
                    format: 'd.m.Y',
                    id: 'EPLDTIPRO_EvnPLDispTeenInspection_setDate',
					listeners: {
						'focus': function(field) {
							field.oldValue = field.getValue();
						},
						'blur': function(field) {
							if (field.oldValue instanceof Date && field.getValue() instanceof Date && field.oldValue - field.getValue() == 0) {
								win.setAgeGroupDispCombo(field.getValue(), field.getValue(), true);
							}
						},
						'change': function(field, newValue, oldValue) {
							win.loadUslugaComplex();

							if (getRegionNick().inlist([ 'perm', 'ufa' ]) && !Ext.isEmpty(oldValue) && !Ext.isEmpty(newValue) && win.checkEvnPLDispTeenInspectionIsSaved() && newValue.format('Y') != oldValue.format('Y')) {
								sw.swMsg.show({
									buttons: Ext.Msg.YESNO,
									fn: function ( buttonId ) {
										if ( buttonId == 'yes' ) {
											win.saveDopDispInfoConsentAfterLoad = true;
											win.blockSaveDopDispInfoConsent = true;
											win.setAgeGroupDispCombo(newValue, null, true);
										} else {
											win.findById('EPLDTIPRO_EvnPLDispTeenInspection_setDate').setValue(oldValue);
											win.loadUslugaComplex();
										}
									},
									msg: 'При изменении даты начала медицинского осмотра введенная информация по осмотрам / исследованиям будет удалена. Изменить?',
									title: 'Подтверждение'
								});
								return false;
							}

							win.setAgeGroupDispCombo(newValue, oldValue, true);
						}
					},
                    plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
                    width: 100,
                    xtype: 'swdatefield'
				}]
			}],
			layout: 'form',
			border: false,
			autoHeight: true,
			collapsible: false,
			title: ''
		});
		
		this.DopDispInfoConsentPanel = new sw.Promed.Panel({
			items: [{
				border: false,
				labelWidth: 200,
				layout: 'form',
				style: 'padding: 5px;',
				items: [{
					allowBlank: false,
					fieldLabel: 'Дата подписания согласия/отказа',
					format: 'd.m.Y',
					id: 'EPLDTIPRO_EvnPLDispTeenInspection_consDate',
					plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
					width: 100,
					xtype: 'swdatefield'
				}, {
					fieldLabel: 'Случай обслужен мобильной бригадой',
					name: 'EvnPLDispTeenInspection_IsMobile',
					xtype: 'checkbox',
					listeners: {
						'check': function(checkbox, value) {
							var base_form = win.EvnPLDispTeenInspectionFormPanel.getForm();
							
							if ( value == true && win.action != 'view' ) {
								base_form.findField('Lpu_mid').setAllowBlank(getRegionNick() == 'krasnoyarsk');
								base_form.findField('Lpu_mid').enable();
							} else {
								base_form.findField('Lpu_mid').setAllowBlank(true);
								base_form.findField('Lpu_mid').clearValue();
								base_form.findField('Lpu_mid').disable();
							}
						}
					}
				}, {
					fieldLabel: 'МО мобильной бригады',
					valueField: 'Lpu_id',
					hiddenName: 'Lpu_mid',
					xtype: 'sworgcombo',
					onTrigger1Click: function() {
						var combo = this;
						if (combo.disabled) {
							return false;
						}
						
						getWnd('swOrgSearchWindow').show({
							enableOrgType: false,
							onlyFromDictionary: true,
							object: 'lpu',
							DispClass_id: 10,
							Disp_consDate: (typeof win.findById('EPLDTIPRO_EvnPLDispTeenInspection_consDate').getValue() == 'object' ? Ext.util.Format.date(win.findById('EPLDTIPRO_EvnPLDispTeenInspection_consDate').getValue(), 'd.m.Y') : win.findById('EPLDTIPRO_EvnPLDispTeenInspection_consDate').getValue()),
							onSelect: function(lpuData) {
								if ( lpuData.Lpu_id > 0 )
								{
									combo.getStore().load({
										params: {
											OrgType: 'lpu',
											Lpu_oid: lpuData.Lpu_id
										},
										callback: function()
										{
											combo.setValue(lpuData.Lpu_id);
											combo.focus(true, 500);
											combo.fireEvent('change', combo);
										}
									});
								}
								getWnd('swOrgSearchWindow').hide();
							},
							onClose: function() {combo.focus(true, 200)}
						});
					}
				},  {
					fieldLabel: 'Проведен вне МО',
					listeners: {
						'render': function() {
							if (!getRegionNick().inlist([ 'ekb', 'penza' ])) {
								this.hideContainer();
							}
						}
					},
					name: 'EvnPLDispTeenInspection_IsOutLpu',
					xtype: 'checkbox'
				}]
			},
				win.dopDispInfoConsentGrid,
				// кнопки Печать и Сохранить
				{
					border: false,
					bodyStyle: 'padding:5px;',
					layout: 'column',
					items: [{
						border: false,
						layout: 'form',
						items: [
							new Ext.Button({
								id: 'EPLDTIPRO_DopDispInfoConsentSaveBtn',
								handler: function() {
									win.saveDopDispInfoConsent();
								}.createDelegate(this),
								iconCls: 'save16',
								text: BTN_FRMSAVE
							})
						]
					}, {
						border: false,
						bodyStyle: 'margin-left: 5px;',
						layout: 'form',
						items: [
							new Ext.Button({
								handler: function() {
									var base_form = win.EvnPLDispTeenInspectionFormPanel.getForm();
									var isOtkaz = false;
									win.dopDispInfoConsentGrid.getGrid().getStore().each(function(rec) {
										if(rec.get('DopDispInfoConsent_IsAgree') != true) {
											isOtkaz = true;
										}
									});
									var pattern = '';//Шаблон для печати от имени пациента
									var pattern_dep = '';//Шаблон для печати от имени представителя
									var title = ''; //Название формы вопроса о выборе варианта печати
									if (!isOtkaz) { //согласие
										pattern = 'EvnPLDispTeenInspectionConsent.rptdesign';
										pattern_dep = 'EvnPLDispTeenInspectionConsent_Deputy.rptdesign';
										title = ' согласия';
									}
									else{ //отказ
										pattern = 'EvnPLDispTeenInspectionOtkaz.rptdesign';
										pattern_dep = 'EvnPLDispTeenInspectionOtkaz_Deputy.rptdesign';
										title = ' отказа';
									}
									var paramEvnPLDispTeenInspection = base_form.findField('EvnPLDispTeenInspection_id').getValue();
									if(paramEvnPLDispTeenInspection) {
										var dialog_wnd = Ext.Msg.show({
											title: 'Вид' + title,
											msg:'Выберите вид' + title,
											buttons: {yes: "От лица пациента", no: "От лица представителя", cancel: "Отмена"},
											fn: function(btn){
												if (btn == 'cancel') {
													return;
												}
												if(btn == 'yes'){ //От имени пациента
													printBirt({
														'Report_FileName': pattern,
														'Report_Params': '&EvnPLDispTeenInspection=' + paramEvnPLDispTeenInspection + '&paramMedPersonal=' + sw.Promed.MedStaffFactByUser.current.MedPersonal_id,
														'Report_Format': 'pdf'
													});
												}
												if(btn == 'no') { //От имени законного представителя
													printBirt({
														'Report_FileName': pattern_dep,
														'Report_Params': '&EvnPLDispTeenInspection=' + paramEvnPLDispTeenInspection + '&paramMedPersonal=' + sw.Promed.MedStaffFactByUser.current.MedPersonal_id,
														'Report_Format': 'pdf'
													});
												}
											}
										});
									}
								}.createDelegate(this),
								iconCls: 'print16',
								text: BTN_FRMPRINT
							})
						]
					}]
				}
			],
			layout: 'form',
			border: false,
			autoHeight: true,
			collapsible: true,
			title: 'Информированное добровольное согласие 1 этап'
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
			title: 'Маршрутная карта'
		});
		
		this.EvnDiagAndRecomendationGrid = new sw.Promed.ViewFrame({
			autoLoadData: false,
			editformclassname: 'swEvnDiagAndRecomendationEditForm',
			object: 'EvnDiagAndRecomendation',
			actions: [
				{ name: 'action_add', disabled: true, hidden: true },
				{ name: 'action_edit', handler: function() {
					win.openEvnDiagAndRecomendationEditWindow('edit');
				}},
				{ name: 'action_view', handler: function() {
					win.openEvnDiagAndRecomendationEditWindow('view');
				}},
				{ name: 'action_delete', disabled: true, hidden: true },
				{ name: 'action_refresh', disabled: true, hidden: true },
				{ name: 'action_print', disabled: true, hidden: true }
			],
			id: 'EPLDTIPRO_EvnDiagAndRecomendationGrid',
			dataUrl: '/?c=EvnPLDispTeenInspection&m=loadEvnDiagAndRecomendationGrid',
			region: 'center',
			height: 200,
			onLoadData: function() {
				this.setActionDisabled('action_edit', (!win.action.inlist(['add','edit'])));
			},
			title: '',
			toolbar: true,
			stringfields: [
				{ name: 'EvnVizitDispDop_id', type: 'int', header: 'ID', key: true },
				{ name: 'Diag_id', type: 'int', hidden: true },
				{ name: 'FormDataJSON', type: 'string', hidden: true }, // данные формы "Состояние здоровья: Редактирование"
				{ name: 'OrpDispSpec_Name', type: 'string', header: 'Специальность', width: 300 },
				{ name: 'Diag_Name', type: 'string', header: 'Диагноз', id: 'autoexpand' }
			]
		});
		
		this.EvnDiagAndRecomendationPanel = new sw.Promed.Panel({
			items: [
				win.EvnDiagAndRecomendationGrid
			],
			animCollapse: true,
			layout: 'form',
			border: false,
			autoHeight: true,
			collapsible: true,
			title: 'Диагнозы и рекомендации по результатам диспансеризации / профосмотра'
		});
		
		this.EvnDiagDopDispAndRecomendationGrid = new sw.Promed.ViewFrame({
			autoLoadData: false,
			editformclassname: 'swEvnDiagDopDispAndRecomendationEditForm',
			object: 'EvnDiagDopDispAndRecomendation',
			actions: [
				{ name: 'action_add', handler: function() {
					win.openEvnDiagDopDispAndRecomendationEditWindow('add');
				}},
				{ name: 'action_edit', handler: function() {
					win.openEvnDiagDopDispAndRecomendationEditWindow('edit');
				}},
				{ name: 'action_view', handler: function() {
					win.openEvnDiagDopDispAndRecomendationEditWindow('view');
				}},
				{ name: 'action_delete', handler: function() {
					win.deleteEvnDiagDopDispAndRecomendation();
				}},
				{ name: 'action_refresh' },
				{ name: 'action_print' }
			],
			id: 'EPLDTIPRO_EvnDiagDopDispAndRecomendationGrid',
			dataUrl: '/?c=EvnDiagDopDisp&m=loadEvnDiagDopDispAndRecomendationGrid',
			region: 'center',
			height: 200,
			onLoadData: function() {
				this.setActionDisabled('action_edit', (!win.action.inlist(['add','edit'])));
				this.setActionDisabled('action_delete', (!win.action.inlist(['add','edit'])));
			},
			title: '',
			toolbar: true,
			stringfields: [
				{ name: 'EvnDiagDopDisp_id', type: 'int', header: 'ID', key: true },
				{ name: 'Diag_id', type: 'int', hidden: true },
				{ name: 'DeseaseDispType_Name', type: 'string', header: 'Установлен впервые', width: 150 },
				{ name: 'DispSurveilType_Name', type: 'string', header: 'Диспансерное наблюдение', width: 150 },
				{ name: 'Diag_Name', type: 'string', header: 'Диагноз', id: 'autoexpand' }
			]
		});
		
		this.EvnDiagDopDispAndRecomendationPanel = new sw.Promed.Panel({
			items: [
				win.EvnDiagDopDispAndRecomendationGrid
			],
			animCollapse: true,
			layout: 'form',
			border: false,
			autoHeight: true,
			collapsible: true,
			title: 'Состояние здоровья до проведения диспансеризации / профосмотра'
		});

		this.DispAppointGrid = new sw.Promed.ViewFrame({
			autoLoadData: false,
			object: 'DispAppoint',
			actions: [
				{ name: 'action_add', handler: function() { win.openDispAppointEditWindow('add'); }},
				{ name: 'action_edit', handler: function() { win.openDispAppointEditWindow('edit'); }},
				{ name: 'action_view', handler: function() { win.openDispAppointEditWindow('view'); }},
				{ name: 'action_delete', handler: function() { win.deleteDispAppoint(); }},
				{ name: 'action_refresh', disabled: true, hidden: true },
				{ name: 'action_print'}
			],
			uniqueId: true,
			dataUrl: '/?c=DispAppoint&m=loadDispAppointGrid',
			region: 'center',
			height: 200,
			title: '',
			toolbar: true,
			stringfields: [
				{ name: 'DispAppoint_id', type: 'int', header: 'ID', key: true },
				{ name: 'DispAppointType_id', type: 'int', hidden: true },
				{ name: 'MedSpecOms_id', type: 'int', hidden: true },
				{ name: 'ExaminationType_id', type: 'int', hidden: true },
				{ name: 'LpuSectionProfile_id', type: 'int', hidden: true },
				{ name: 'LpuSectionBedProfile_id', type: 'int', hidden: true },
				{ name: 'LpuSectionBedProfile_fid', type: 'int', hidden: true },
				{ name: 'RecordStatus_Code', type: 'int', hidden: true },
				{ name: 'DispAppointType_Name', type: 'string', header: 'Назначение', width: 350 },
				{ name: 'DispAppoint_Comment', type: 'string', header: 'Комментарий', id: 'autoexpand' }
			]
		});

		this.DispAppointPanel = new sw.Promed.Panel({
			hidden: getRegionNick() == 'kz',
			items: [
				win.DispAppointGrid
			],
			animCollapse: true,
			layout: 'form',
			border: false,
			autoHeight: true,
			collapsible: true,
			title: 'Назначения'
		});
		
		this.EvnPLDispTeenInspectionMainResultsPanel = new sw.Promed.Panel({
			bodyBorder: false,
			title: 'Общая оценка здоровья',
			border: false,
			collapsible: true,
			titleCollapse: true,
			animCollapse: false,
			buttonAlign: 'left',
			frame: false,
			bodyStyle: 'padding: 5px',
			labelAlign: 'right',
			labelWidth: 195,
			items: [{
					name: 'EvnPLDispTeenInspection_id',
					value: null,
					xtype: 'hidden'
				}, {
					name:'EvnPLDispTeenInspection_IsPaid',
					xtype:'hidden'
				}, {
					name:'EvnPLDispTeenInspection_IndexRep',
					xtype:'hidden'
				}, {
					name:'EvnPLDispTeenInspection_IndexRepInReg',
					xtype:'hidden'
				}, {
					name: 'accessType',
					xtype: 'hidden'
				}, {
					name: 'PersonDispOrp_id',
					value: null,
					xtype: 'hidden'
				}, {
					name: 'DispClass_id',
					value: 10,
					xtype: 'hidden'
				}, {
					name: 'EvnPLDispTeenInspection_fid',
					value: null,
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
					name: 'EvnPLDispTeenInspection_setDate',
					xtype: 'hidden'
				}, {
					name: 'EvnPLDispTeenInspection_disDate',
					xtype: 'hidden'
				}, {
					name: 'EvnPLDispTeenInspection_consDate',
					xtype: 'hidden'
				}, {
					name: 'Server_id',
					value: 0,
					xtype: 'hidden'
				},
				{
					autoHeight: true,
					style: 'padding: 0px;',
					title: 'Оценка физического развития',
					width: 600,
					items: [
						{
							fieldLabel: 'Масса (кг)',
							name: 'AssessmentHealth_Weight',
							decimalPrecision: 1,
							minValue: 2,
							maxValue: 500,
							xtype: 'numberfield'
						},
						{
							fieldLabel: 'Рост (см)',
							name: 'AssessmentHealth_Height',
							minValue: 20,
							maxValue: 275,
							xtype: 'numberfield'
						},
						{
							fieldLabel: 'Окружность головы (см)',
							name: 'AssessmentHealth_Head',
							minValue: 6,
							maxValue: 99,
							xtype: 'numberfield'
						},
						{
							fieldLabel: 'Отклонение (масса)',
							listeners: {
								'change': function(combo, newValue, oldValue) {
									var base_form = win.EvnPLDispTeenInspectionFormPanel.getForm();
									if (newValue == 2) {
										if (win.action != 'view') {
											base_form.findField('WeightAbnormType_id').enable();
										}
										base_form.findField('WeightAbnormType_id').setAllowBlank(false);
									} else {
										base_form.findField('WeightAbnormType_id').clearValue();
										base_form.findField('WeightAbnormType_id').disable();
										base_form.findField('WeightAbnormType_id').setAllowBlank(true);
									}
								}
							},
							hiddenName: 'WeightAbnormType_YesNo',
							xtype: 'swyesnocombo'
						},
						{
							comboSubject: 'WeightAbnormType',
							disabled: true,
							fieldLabel: 'Тип отклонения (масса)',
							hiddenName: 'WeightAbnormType_id',
							lastQuery: '',
							width: 300,
							xtype: 'swcommonsprcombo'
						},
						{
							fieldLabel: 'Отклонение (рост)',
							listeners: {
								'change': function(combo, newValue, oldValue) {
									var base_form = win.EvnPLDispTeenInspectionFormPanel.getForm();
									if (newValue == 2) {
										if (win.action != 'view') {
											base_form.findField('HeightAbnormType_id').enable();
										}
										base_form.findField('HeightAbnormType_id').setAllowBlank(false);
									} else {
										base_form.findField('HeightAbnormType_id').clearValue();
										base_form.findField('HeightAbnormType_id').disable();
										base_form.findField('HeightAbnormType_id').setAllowBlank(true);
									}
								}
							},
							hiddenName: 'HeightAbnormType_YesNo',
							xtype: 'swyesnocombo'
						},
						{
							comboSubject: 'HeightAbnormType',
							disabled: true,
							fieldLabel: 'Тип отклонения (рост)',
							hiddenName: 'HeightAbnormType_id',
							lastQuery: '',
							width: 300,
							xtype: 'swcommonsprcombo'
						}
					],
					bodyStyle: 'padding: 5px;',
					xtype: 'fieldset'
				},
				{
					autoHeight: true,
					style: 'padding: 0px;',
					title: 'Оценка психического развития (состояния)',
					width: 600,
					items: [
						{
							allowDecimals: false,
							allowNegative: false,
							fieldLabel: 'Познавательная функция (возраст развития) (мес.)',
							minValue: 0,
							name: 'AssessmentHealth_Gnostic',
							xtype: 'numberfield'
						},
						{
							allowDecimals: false,
							allowNegative: false,
							fieldLabel: 'Моторная функция (возраст развития) (мес.)',
							minValue: 0,
							name: 'AssessmentHealth_Motion',
							xtype: 'numberfield'
						},
						{
							allowDecimals: false,
							allowNegative: false,
							fieldLabel: 'Эмоциональная и социальная (контакт с окружающим миром) функции (возраст развития) (мес.)',
							minValue: 0,
							name: 'AssessmentHealth_Social',
							xtype: 'numberfield'
						},
						{
							allowDecimals: false,
							allowNegative: false,
							fieldLabel: 'Предречевое и речевое развитие (возраст развития) (мес.)',
							minValue: 0,
							name: 'AssessmentHealth_Speech',
							xtype: 'numberfield'
						},
						{
							fieldLabel: 'Психомоторная сфера',
							hiddenName: 'NormaDisturbanceType_id',
							xtype: 'swnormadisturbancetypecombo'
						},
						{
							fieldLabel: 'Интеллект',
							hiddenName: 'NormaDisturbanceType_uid',
							xtype: 'swnormadisturbancetypecombo'
						},
						{
							fieldLabel: 'Эмоционально-вегетативная сфера',
							hiddenName: 'NormaDisturbanceType_eid',
							xtype: 'swnormadisturbancetypecombo'
						}
					],
					bodyStyle: 'padding: 5px;',
					xtype: 'fieldset'
				},
				{
					autoHeight: true,
					style: 'padding: 0px;',
					title: 'Оценка полового развития',
					width: 600,
					items: [
						{
							fieldLabel: 'P',
							minValue: 0,
							maxValue: 5,
							name: 'AssessmentHealth_P',
							xtype: 'numberfield'
						},
						{
							fieldLabel: 'Ax',
							minValue: 0,
							maxValue: 5,
							name: 'AssessmentHealth_Ax',
							xtype: 'numberfield'
						},
						{
							fieldLabel: 'Fa',
							minValue: 0,
							maxValue: 5,
							name: 'AssessmentHealth_Fa',
							xtype: 'numberfield'
						},
						{
							fieldLabel: 'Ma',
							minValue: 0,
							maxValue: 5,
							name: 'AssessmentHealth_Ma',
							xtype: 'numberfield'
						},
						{
							fieldLabel: 'Me',
							minValue: 0,
							maxValue: 5,
							name: 'AssessmentHealth_Me',
							xtype: 'numberfield'
						},
						{
							autoHeight: true,
							style: 'padding: 0px;',
							id: 'EPLDTIPRO_menarhe',
							title: 'Характеристика менструальной функции: menarhe',
							width: 580,
							items: [
								{
									fieldLabel: 'Лет',
									minValue: 6,
									maxValue: 17,
									name: 'AssessmentHealth_Years',
									xtype: 'numberfield'
								},
								{
									fieldLabel: 'Месяцев',
									minValue: 0,
									maxValue: 12,
									name: 'AssessmentHealth_Month',
									xtype: 'numberfield'
								}
							],
							bodyStyle: 'padding: 5px;',
							xtype: 'fieldset'
						},
						{
							autoHeight: true,
							style: 'padding: 0px;',
							id: 'EPLDTIPRO_menses',
							title: 'menses (характеристика)',
							width: 580,
							items: [
								{
									boxLabel: 'Регулярные',
									hideLabel: true,
									name: 'AssessmentHealth_IsRegular',
									xtype: 'checkbox'
								},
								{
									boxLabel: 'Нерегулярные',
									hideLabel: true,
									name: 'AssessmentHealth_IsIrregular',
									xtype: 'checkbox'
								},
								{
									boxLabel: 'Обильные',
									hideLabel: true,
									name: 'AssessmentHealth_IsAbundant',
									xtype: 'checkbox'
								},
								{
									boxLabel: 'Умеренные',
									hideLabel: true,
									name: 'AssessmentHealth_IsModerate',
									xtype: 'checkbox'
								},
								{
									boxLabel: 'Скудные',
									hideLabel: true,
									name: 'AssessmentHealth_IsScanty',
									xtype: 'checkbox'
								},
								{
									boxLabel: 'Болезненные',
									hideLabel: true,
									name: 'AssessmentHealth_IsPainful',
									xtype: 'checkbox'
								},
								{
									boxLabel: 'Безболезненные',
									hideLabel: true,
									name: 'AssessmentHealth_IsPainless',
									xtype: 'checkbox'
								}
							],
							bodyStyle: 'padding: 5px;',
							xtype: 'fieldset'
						}
					],
					bodyStyle: 'padding: 5px;',
					xtype: 'fieldset'
				},
				{
					autoHeight: true,
					style: 'padding: 0px;',
					title: 'Инвалидность',
					width: 600,
					items: [
						{
							comboSubject: 'InvalidType',
							fieldLabel: 'Инвалидность',
							hiddenName: 'InvalidType_id',
							loadParams: {params: {where: ' where InvalidType_Code <= 3'}},
							lastQuery: '',
                            listeners: {
                                'change': function(combo,value){
                                    var base_form = win.EvnPLDispTeenInspectionFormPanel.getForm();
                                    if(value == 2 || value == 3)
                                    {
                                        base_form.findField('AssessmentHealth_setDT').setAllowBlank(false);
                                        base_form.findField('AssessmentHealth_reExamDT').setAllowBlank(false);
                                        base_form.findField('InvalidDiagType_id').setAllowBlank(false);
                                    }
                                    else
                                    {
                                        base_form.findField('AssessmentHealth_setDT').setAllowBlank(true);
                                        base_form.findField('AssessmentHealth_reExamDT').setAllowBlank(true);
                                        base_form.findField('InvalidDiagType_id').setAllowBlank(true);
                                    }
                                }
                            },
							xtype: 'swcommonsprcombo'
						},
						{
							fieldLabel: 'Дата установления',
							name: 'AssessmentHealth_setDT',
							xtype: 'swdatefield'
						},
						{
							fieldLabel: 'Дата последнего освидетельствования',
							name: 'AssessmentHealth_reExamDT',
							xtype: 'swdatefield'
						},
						{
							comboSubject: 'InvalidDiagType',
							fieldLabel: 'Заболевания, обусловившие возникновение инвалидности',
							hiddenName: 'InvalidDiagType_id',
							lastQuery: '',
							width: 300,
							xtype: 'swcommonsprcombo'
						},
						{
							autoHeight: true,
							style: 'padding: 0px;',
							title: 'Виды нарушений',
							width: 580,
							items: [
								{
									boxLabel: 'Умственные',
									hideLabel: true,
									name: 'AssessmentHealth_IsMental',
									xtype: 'checkbox'
								},
								{
									boxLabel: 'Другие психологические',
									hideLabel: true,
									name: 'AssessmentHealth_IsOtherPsych',
									xtype: 'checkbox'
								},
								{
									boxLabel: 'Языковые и речевые',
									hideLabel: true,
									name: 'AssessmentHealth_IsLanguage',
									xtype: 'checkbox'
								},
								{
									boxLabel: 'Слуховые и вестибулярные',
									hideLabel: true,
									name: 'AssessmentHealth_IsVestibular',
									xtype: 'checkbox'
								},
								{
									boxLabel: 'Зрительные',
									hideLabel: true,
									name: 'AssessmentHealth_IsVisual',
									xtype: 'checkbox'
								},
								{
									boxLabel: 'Висцеральные и метаболические расстройства питания',
									hideLabel: true,
									name: 'AssessmentHealth_IsMeals',
									xtype: 'checkbox'
								},
								{
									boxLabel: 'Двигательные',
									hideLabel: true,
									name: 'AssessmentHealth_IsMotor',
									xtype: 'checkbox'
								},
								{
									boxLabel: 'Уродующие',
									hideLabel: true,
									name: 'AssessmentHealth_IsDeform',
									xtype: 'checkbox'
								},
								{
									boxLabel: 'Общие и генерализованные',
									hideLabel: true,
									name: 'AssessmentHealth_IsGeneral',
									xtype: 'checkbox'
								}
							],
							bodyStyle: 'padding: 5px;',
							xtype: 'fieldset'
						},
						{
							autoHeight: true,
							style: 'padding: 0px;',
							title: 'Индивидуальная программа реабилитации ребенка инвалида',
							width: 580,
							items: [
								{
									fieldLabel: 'Дата назначения',
									name: 'AssessmentHealth_ReabDT',
									xtype: 'swdatefield'
								},
								{
									comboSubject: 'RehabilitEndType',
									fieldLabel: 'Выполнение на момент диспансеризации',
									hiddenName: 'RehabilitEndType_id',
									lastQuery: '',
									xtype: 'swcommonsprcombo'
								}
							],
							bodyStyle: 'padding: 5px;',
							xtype: 'fieldset'
						}
					],
					bodyStyle: 'padding: 5px;',
					xtype: 'fieldset'
				},
				{
					comboSubject: 'ProfVaccinType',
					value: 1,
					fieldLabel: 'Проведение профилактических прививок',
					hiddenName: 'ProfVaccinType_id',
					lastQuery: '',
					width: 300,
					xtype: 'swcommonsprcombo',
					listeners:{
						
						select:function(l,rec,s){
							if(rec&&rec.get('ProfVaccinType_Code'))
							win.setVacNameStat(rec.get('ProfVaccinType_Code'))
						},
						change: function(combo, newValue) {
							var vaccinFieldset = win.findById('EPLDTIPRO_VaccinFieldset');
							if (Ext.isEmpty(newValue) || newValue != 6) {
								vaccinFieldset.items.items.forEach( function (item) {
									item.disable();
									item.setValue(0);
								});
							} else {
								vaccinFieldset.items.items.forEach( function (item) {
									item.enable();
								});
							}
						}
					}
				},
				{
					comboSubject: 'VaccinType',
					fieldLabel: 'Тип прививки',
					hiddenName: 'VaccinType_id',
					lastQuery: '',
					width: 300,
					xtype: 'swcommonsprcombo'
				},
				{
					autoHeight: true,
					style: 'padding: 0px;',
					title: 'Прививки',
					id: 'EPLDTIPRO_VaccinFieldset',
					width: 600,
					items: [],
					bodyStyle: 'padding: 5px;',
					xtype: 'fieldset'
				},
				{
					fieldLabel:'Наименование прививки',
					name:'AssessmentHealth_VaccineName',
					width: 300,
					xtype:'textfield'
				},
				{
					fieldLabel:'Рекомендации по формированию здорового образа жизни',
					name:'AssessmentHealth_HealthRecom',
					width: 300,
					xtype:'textfield'
				},
				{
					fieldLabel:'Рекомендации о необходимости установления или продолжения диспансерного наблюдения',
					name:'AssessmentHealth_DispRecom',
					width: 300,
					xtype:'textfield'
				},
				{
					fieldLabel: 'Подозрение на ЗНО',
					hiddenName: 'EvnPLDispTeenInspection_IsSuspectZNO',
					id: 'EPLDTIPRO_EvnPLDispTeenInspection_IsSuspectZNO',
					width: 100,
					xtype: 'swyesnocombo',
					listeners:{
						'change':function (combo, newValue, oldValue) {
							var base_form = win.EvnPLDispTeenInspectionFormPanel.getForm();

							var index = combo.getStore().findBy(function (rec) {
								return (rec.get(combo.valueField) == newValue);
							});
							combo.fireEvent('select', combo, combo.getStore().getAt(index), index);

							if (base_form.findField('EvnPLDispTeenInspection_IsSuspectZNO').getValue() == 2) {
								Ext.getCmp('EPLDTIPRO_PrintKLU').enable();
								Ext.getCmp('EPLDTIPRO_PrintOnko').enable();
							} else {
								Ext.getCmp('EPLDTIPRO_PrintKLU').disable();
								Ext.getCmp('EPLDTIPRO_PrintOnko').disable();
							}
						},
						'select':function (combo, record, idx) {
							if (record && record.get('YesNo_id') == 2) {
								Ext.getCmp('EPLDTIPRO_Diag_spid').showContainer();
								Ext.getCmp('EPLDTIPRO_Diag_spid').setAllowBlank(!getRegionNick().inlist([ 'astra', 'perm' ]));
							} else {
								Ext.getCmp('EPLDTIPRO_Diag_spid').setValue('');
								Ext.getCmp('EPLDTIPRO_Diag_spid').hideContainer();
								Ext.getCmp('EPLDTIPRO_Diag_spid').setAllowBlank(true);
							}
						}
					}
				},
				{
					fieldLabel: 'Подозрение на диагноз',
					hiddenName: 'Diag_spid',
					id: 'EPLDTIPRO_Diag_spid',
					additQueryFilter: "(Diag_Code like 'C%' or Diag_Code like 'D0%')",
					baseFilterFn: function(rec){
						if(typeof rec.get == 'function') {
							return (rec.get('Diag_Code').substr(0,1) == 'C' || rec.get('Diag_Code').substr(0,2) == 'D0');
						} else if (rec.attributes && rec.attributes.Diag_Code) {
							return (rec.attributes.Diag_Code.substr(0,1) == 'C' || rec.attributes.Diag_Code.substr(0,2) == 'D0');
						} else {
							return true;
						}
					},
					width: 300,
					xtype: 'swdiagcombo'
				},
				{
					fieldLabel: 'Группа здоровья',
					hiddenName: 'HealthKind_id',
					listeners: {
						'change': function(combo, newValue, oldValue) {
							win.checkEvnPLDispTeenInspectionIsSaved();
						}
					},
					loadParams: {params: {where: ' where HealthKind_Code <= 5'}},
					xtype: 'swhealthkindcombo'
				},
				{
					comboSubject: 'HealthGroupType',
					fieldLabel: 'Медицинская группа для занятий физ.культурой до проведения обследования',
					hiddenName: 'HealthGroupType_oid',
					lastQuery: '',
					width: 300,
					xtype: 'swcommonsprcombo'
				},
				{
					comboSubject: 'HealthGroupType',
					fieldLabel: 'Медицинская группа для занятий физ.культурой',
					hiddenName: 'HealthGroupType_id',
					lastQuery: '',
					width: 300,
					xtype: 'swcommonsprcombo'
				},
				{
					fieldLabel: 'Направлен на 2 этап',
					hiddenName: 'EvnPLDispTeenInspection_IsTwoStage',
					allowBlank: false,
					width: 100,
					xtype: 'swyesnocombo'
				},
				{
					fieldLabel: 'Случай закончен',
					hiddenName: 'EvnPLDispTeenInspection_IsFinish',
					allowBlank: false,
					width: 100,
					xtype: 'swyesnocombo',
					listeners:{
						'select':function (combo, record) {
							win.verfGroup();
						},
						'change': function() {
							win.checkForCostPrintPanel();
						}
					}
				}
			],
			layout: 'form',
			region: 'center'
		});

		this.CostPrintPanel = new sw.Promed.Panel({
			bodyBorder: false,
			title: 'Справка о стоимости лечения',
			hidden: ! getRegionNick().inlist(['perm', 'kz', 'ufa']),
			border: false,
			collapsible: true,
			titleCollapse: true,
			animCollapse: false,
			buttonAlign: 'left',
			frame: false,
			labelAlign: 'right',
			labelWidth: 195,
			items: [{
				bodyStyle: 'padding: 5px',
				border: false,
				height: 90,
				layout: 'form',
				region: 'center',
				items: [{
					fieldLabel: 'Дата выдачи справки/отказа',
					width: 100,
					plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
					name: 'EvnCostPrint_setDT',
					xtype: 'swdatefield'
				},{
					fieldLabel: 'Отказ',
					hiddenName: 'EvnCostPrint_IsNoPrint',
					width: 60,
					xtype: 'swyesnocombo'
				}]
			}]
		});

		this.EvnPLDispTeenInspectionFormPanel = new Ext.form.FormPanel({
			border: false,
			layout: 'form',
			region: 'center',
			autoScroll: true,
			items: [
				win.FirstPanel,
				// информированное добровольное согласие
				win.DopDispInfoConsentPanel,
				// маршрутная карта
				win.EvnUslugaDispDopPanel,
				// Диагнозы и рекомендации по результатам диспансеризации / профосмотра
				win.EvnDiagAndRecomendationPanel,
				// Состояние здоровья до проведения диспансеризации / профосмотра
				win.EvnDiagDopDispAndRecomendationPanel,
				// общая карта здоровья
				win.EvnPLDispTeenInspectionMainResultsPanel,
				// назначения
				win.DispAppointPanel,
				// Справка о стоимости лечения
				win.CostPrintPanel
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
							this.printEvnPLDispTeenInspProf();
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
				{ name: 'EvnPLDispTeenInspection_id' },
				{ name: 'EvnPLDispTeenInspection_IsPaid' },
				{ name: 'EvnPLDispTeenInspection_IndexRep' },
				{ name: 'EvnPLDispTeenInspection_IndexRepInReg' },
				{ name: 'accessType' },
				{ name: 'PersonDispOrp_id' },
				{ name: 'DispClass_id' },
				{ name: 'PayType_id' },
				{ name: 'EvnPLDispTeenInspection_fid' },
				{ name: 'Person_id' },
				{ name: 'PersonEvn_id' },
				{ name: 'Server_id' },
				{ name: 'EvnPLDispTeenInspection_setDate' },
				{ name: 'EvnPLDispTeenInspection_consDate' },
				{ name: 'EvnPLDispTeenInspection_disDate' },
				{ name: 'EvnPLDispTeenInspection_eduDT' },
				{ name: 'AgeGroupDisp_id' },
				{ name: 'OrgExist' },
				{ name: 'Org_id' },
				{ name: 'Lpu_mid' },
				{ name: 'EvnPLDispTeenInspection_IsMobile' },
				{ name: 'EvnPLDispTeenInspection_IsOutLpu' },
				{ name: 'InstitutionNatureType_id' },
				{ name: 'InstitutionType_id' },
				{ name: 'AssessmentHealth_Weight' },
				{ name: 'AssessmentHealth_Height' },
				{ name: 'AssessmentHealth_Head' },
				{ name: 'WeightAbnormType_YesNo' },
				{ name: 'WeightAbnormType_id' },
				{ name: 'HeightAbnormType_YesNo' },
				{ name: 'HeightAbnormType_id' },
				{ name: 'AssessmentHealth_Gnostic' },
				{ name: 'AssessmentHealth_Motion' },
				{ name: 'AssessmentHealth_Social' },
				{ name: 'AssessmentHealth_Speech' },
				{ name: 'AssessmentHealth_P' },
				{ name: 'AssessmentHealth_Ax' },
				{ name: 'AssessmentHealth_Fa' },
				{ name: 'AssessmentHealth_Ma' },
				{ name: 'AssessmentHealth_Me' },
				{ name: 'AssessmentHealth_Years' },
				{ name: 'AssessmentHealth_Month' },
				{ name: 'AssessmentHealth_IsRegular' },
				{ name: 'AssessmentHealth_IsIrregular' },
				{ name: 'AssessmentHealth_IsAbundant' },
				{ name: 'AssessmentHealth_IsModerate' },
				{ name: 'AssessmentHealth_IsScanty' },
				{ name: 'AssessmentHealth_IsPainful' },
				{ name: 'AssessmentHealth_IsPainless' },
				{ name: 'InvalidType_id' },
				{ name: 'AssessmentHealth_setDT' },
				{ name: 'AssessmentHealth_reExamDT' },
				{ name: 'InvalidDiagType_id' },
				{ name: 'AssessmentHealth_IsMental' },
				{ name: 'AssessmentHealth_IsOtherPsych' },
				{ name: 'AssessmentHealth_IsLanguage' },
				{ name: 'AssessmentHealth_IsVestibular' },
				{ name: 'AssessmentHealth_IsVisual' },
				{ name: 'AssessmentHealth_IsMeals' },
				{ name: 'AssessmentHealth_IsMotor' },
				{ name: 'AssessmentHealth_IsDeform' },
				{ name: 'AssessmentHealth_IsGeneral' },
				{ name: 'AssessmentHealth_ReabDT' },
				{ name: 'RehabilitEndType_id' },
				{ name: 'ProfVaccinType_id' },
				{ name: 'AssessmentHealth_VaccineName' },
				{ name: 'AssessmentHealth_HealthRecom' },
				{ name: 'AssessmentHealth_DispRecom' },
				{ name: 'HealthGroupType_oid' },
				{ name: 'HealthGroupType_id' },
				{ name: 'HealthKind_id' },
				{ name: 'EvnPLDispTeenInspection_IsTwoStage' },
				{ name: 'EvnPLDispTeenInspection_IsFinish' },
				{ name: 'NormaDisturbanceType_eid' },
				{ name: 'NormaDisturbanceType_id' },
				{ name: 'NormaDisturbanceType_uid' },
				{ name: 'EvnCostPrint_setDT' },
				{ name: 'EvnCostPrint_IsNoPrint' },
				{ name: 'EvnPLDispTeenInspection_IsSuspectZNO' },
				{ name: 'Diag_spid' }
			]),
			url: '/?c=EvnPLDispTeenInspection&m=saveEvnPLDispTeenInspection'
		});
		
		Ext.apply(this, {
			items: [
				// паспортная часть человека
				win.PersonInfoPanel,
				win.EvnPLDispTeenInspectionFormPanel
			],
			buttons: [{
				handler: function() {
					this.doSave(false);
				}.createDelegate(this),
				iconCls: 'save16',
				id: 'EPLDTIPRO_SaveButton',
				onTabAction: function() {
					Ext.getCmp('EPLDTIPRO_PrintButton').focus(true, 200);
				},
				onShiftTabAction: function() {
					var base_form = win.EvnPLDispTeenInspectionFormPanel.getForm();
					base_form.findField('EvnPLDispTeenInspection_IsFinish').focus(true, 200);
				},
				tabIndex: 2406,
				text: BTN_FRMSAVE
			},{
				handler: function() {
					this.printEvnPLDispTeenInspMedZak();
				}.createDelegate(this),
				iconCls: 'print16',
				id: 'EPLDTIPRO_PrintZak',
				tabIndex: 2408,
				text: 'Печать мед. заключения',
				tooltip:'Печать медицинского заключения о принадлежности несовершеннолетнего к медициснкой группе для занятий физической культурой'
			}, {
				hidden: true,
				handler: function() {
					this.printEvnPLDispTeenInspProf();
				}.createDelegate(this),
				iconCls: 'print16',
				id: 'EPLDTIPRO_PrintButton',
				tabIndex: 2407,
				text: 'Печать карты мед. осмотра'
			}, {
				hidden: getRegionNick() == 'kz',
				handler: function() {
					this.printKLU();
				}.createDelegate(this),
				iconCls: 'print16',
				id: 'EPLDTIPRO_PrintKLU',
				tabIndex: 2409,
				text: 'Печать КЛУ при ЗНО'
			}, {
				hidden: getRegionNick() != 'ekb',
				handler: function() {
					this.printOnko();
				}.createDelegate(this),
				iconCls: 'print16',
				id: 'EPLDTIPRO_PrintOnko',
				tabIndex: 2410,
				text: 'Печать выписки по онкологии'
			}, '-',
			HelpButton(this, -1),
			{
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'cancel16',
				id: 'EPLDTIPRO_CancelButton',
				tabIndex: 2411,
				text: BTN_FRMCANCEL
			}]
		});
		sw.Promed.swEvnPLDispTeenInspectionProfEditWindow.superclass.initComponent.apply(this, arguments);
	},
	keys: [{
		alt: true,
		fn: function(inp, e) {
			var win = Ext.getCmp('EvnPLDispTeenInspectionProfEditWindow');
			var tabbar = win.findById('EPLDTIPRO_EvnPLTabbar');

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
	openDispAppointEditWindow: function(action) {
		if ( typeof action != 'string' || !(action.inlist([ 'add', 'edit', 'view' ])) ) {
			return false;
		}

		if ( getWnd('swDispAppointEditForm').isVisible() ) {
			sw.swMsg.alert(langs('Ошибка'), 'Окно редактирования назначения уже открыто');
			return false;
		}

		var
			formParams = new Object(),
			grid = this.DispAppointGrid.getGrid(),
			params = new Object(),
			wnd = this;

		params.action = action;
		params.EvnPLDisp_consDate = this.findById('EPLDTIPRO_EvnPLDispTeenInspection_consDate').getValue();
		params.callback = function(data) {
			if ( typeof data != 'object' || typeof data.DispAppointData != 'object' ) {
				sw.swMsg.alert(langs('Ошибка'), langs('Отсутствуют необходимые данные'));
				return false;
			}

			var doublesCount = 0;

			grid.getStore().each(function(rec) {
				if (
					rec.get('DispAppoint_id') != data.DispAppointData.DispAppoint_id
					&& rec.get('DispAppointType_id') == data.DispAppointData.DispAppointType_id
					&& rec.get('MedSpecOms_id') == data.DispAppointData.MedSpecOms_id
					&& rec.get('ExaminationType_id') == data.DispAppointData.ExaminationType_id
					&& rec.get('LpuSectionProfile_id') == data.DispAppointData.LpuSectionProfile_id
					&& rec.get('LpuSectionBedProfile_id') == data.DispAppointData.LpuSectionBedProfile_id
					&& rec.get('LpuSectionBedProfile_fid') == data.DispAppointData.LpuSectionBedProfile_fid
				) {
					doublesCount++;
				}
			});

			if ( doublesCount > 0 ) {
				sw.swMsg.alert(langs('Ошибка'), langs('Обнаружено дублирование назначений, сохранение невозможно.'));
				return false;
			}

			data.DispAppointData.RecordStatus_Code = 0;

			var index = grid.getStore().findBy(function(rec) {
				return (rec.get('DispAppoint_id') == data.DispAppointData.DispAppoint_id);
			});

			if ( index >= 0 ) {
				var record = grid.getStore().getAt(index);

				if ( record.get('RecordStatus_Code') == 1 ) {
					data.DispAppointData.RecordStatus_Code = 2;
				}

				var grid_fields = new Array();

				grid.getStore().fields.eachKey(function(key, item) {
					grid_fields.push(key);
				});

				for ( i = 0; i < grid_fields.length; i++ ) {
					record.set(grid_fields[i], data.DispAppointData[grid_fields[i]]);
				}

				record.commit();
			}
			else {
				if ( grid.getStore().getCount() == 1 && Ext.isEmpty(grid.getStore().getAt(0).get('DispAppoint_id')) ) {
					grid.getStore().removeAll();
				}
				
				data.DispAppointData.DispAppoint_id = -swGenTempId(grid.getStore());

				grid.getStore().loadData([ data.DispAppointData ], true);
			}

			setTimeout(function() { wnd.filterDispAppointGrid(); }, 250);
			return true;
		};
		params.formMode = 'local';

		if ( action == 'add' ) {
			params.formParams = formParams;
			params.onHide = function() {
				if ( grid.getStore().getCount() > 0 ) {
					grid.getView().focusRow(0);
				}
			};
		}
		else {
			if ( !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('DispAppoint_id') ) {
				return false;
			}

			var selectedRecord = grid.getSelectionModel().getSelected();

			formParams = selectedRecord.data;
			params.onHide = function() {
				grid.getView().focusRow(grid.getStore().indexOf(selectedRecord));
			};
		}

		params.formParams = formParams;
		getWnd('swDispAppointEditForm').show(params);

		return true;
	},
	deleteDispAppoint: function() {
		var wnd = this;

		if ( wnd.action == 'view' ) {
			return false;
		}
				
		var grid = wnd.DispAppointGrid.getGrid();

		if ( !grid || !grid.getSelectionModel() || !grid.getSelectionModel().getSelected() || Ext.isEmpty(grid.getSelectionModel().getSelected().get('DispAppoint_id')) ) {
			return false;
		}

		var record = grid.getSelectionModel().getSelected();

		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId, text, obj) {
				if ( buttonId == 'yes' ) {
					switch ( Number(record.get('RecordStatus_Code')) ) {
						case 0:
							grid.getStore().remove(record);
						break;

						case 1:
						case 2:
							record.set('RecordStatus_Code', 3);
							record.commit();
							wnd.filterDispAppointGrid();
						break;
					}

					if ( grid.getStore().getCount() > 0 ) {
						grid.getView().focusRow(0);
						grid.getSelectionModel().selectFirstRow();
					}
				}
			}.createDelegate(this),
			icon: Ext.MessageBox.QUESTION,
			msg: langs('Удалить запись?') + '?',
			title: langs('Вопрос')
		});

		return true;
	},
	filterDispAppointGrid: function() {
		this.DispAppointGrid.getGrid().getStore().clearFilter();
		this.DispAppointGrid.getGrid().getStore().filterBy(function(rec) {
			return (rec.get('RecordStatus_Code') != 3);
		});
			
		return true;
	},
	params: {
		EvnVizitPL_setDate: null,
		LpuSection_id: null,
		MedPersonal_id: null
	},

	plain: true,
	resizable: true,
	checkEvnPLDispTeenInspectionIsSaved: function() {
		var base_form = this.EvnPLDispTeenInspectionFormPanel.getForm();
		if (Ext.isEmpty(base_form.findField('EvnPLDispTeenInspection_id').getValue()) || !this.PersonFirstStageAgree) {
			// дисаблим все разделы кроме информированного добровольного согласия, а также основную кнопки сохранить и печать
			this.EvnUslugaDispDopPanel.collapse();
			this.EvnUslugaDispDopPanel.disable();
			this.EvnDiagAndRecomendationPanel.collapse();
			this.EvnDiagAndRecomendationPanel.disable();
			this.EvnDiagDopDispAndRecomendationPanel.collapse();
			this.EvnDiagDopDispAndRecomendationPanel.disable();
			this.EvnPLDispTeenInspectionMainResultsPanel.collapse();
			this.EvnPLDispTeenInspectionMainResultsPanel.disable();
			this.DispAppointPanel.collapse();
			this.DispAppointPanel.disable();
			this.buttons[0].hide();
			this.buttons[1].hide();
			this.buttons[2].hide();
			//this.DopDispInfoConsentPanel.items.items[2].items.items[1].disable(); //Закрываем кнопку "Печать"
			return false;
		} else {
			this.EvnUslugaDispDopPanel.expand();
			this.EvnUslugaDispDopPanel.enable();
			this.EvnDiagAndRecomendationPanel.expand();
			this.EvnDiagAndRecomendationPanel.enable();
			this.EvnDiagDopDispAndRecomendationPanel.expand();
			this.EvnDiagDopDispAndRecomendationPanel.enable();
			this.EvnPLDispTeenInspectionMainResultsPanel.expand();
			this.EvnPLDispTeenInspectionMainResultsPanel.enable();
			if (
				getRegionNick() == 'krym' ||
				(
					!Ext.isEmpty(base_form.findField('HealthKind_id').getValue())
					&& base_form.findField('HealthKind_id').getValue() != 1
					&& base_form.findField('HealthKind_id').getValue() != 2
				)
			) {
				this.DispAppointPanel.expand();
				this.DispAppointPanel.enable();
			} else {
				this.DispAppointPanel.collapse();
				this.DispAppointPanel.disable();
			}

			base_form.findField('WeightAbnormType_YesNo').fireEvent('change', base_form.findField('WeightAbnormType_YesNo'), base_form.findField('WeightAbnormType_YesNo').getValue());
			base_form.findField('HeightAbnormType_YesNo').fireEvent('change', base_form.findField('HeightAbnormType_YesNo'), base_form.findField('HeightAbnormType_YesNo').getValue());
		
			if (this.action != 'view') {
				this.buttons[0].show();
			}
			this.buttons[1].show();
			this.buttons[2].show();
			this.DopDispInfoConsentPanel.items.items[2].items.items[1].enable(); //Открываем кнопку "Печать"
			return true;
		}
	},
	saveDopDispInfoConsent: function(options) {
		var win = this;
		var btn = win.findById('EPLDTIPRO_DopDispInfoConsentSaveBtn');
		if ( btn.disabled || win.action == 'view' ) {
			return false;
		}

		if (win.blockSaveDopDispInfoConsent) {
			win.saveDopDispInfoConsentAfterLoad = true;
			return false;
		}

		if ( typeof options != 'object' ) {
			options = new Object();
		}

		btn.disable();

		var base_form = win.EvnPLDispTeenInspectionFormPanel.getForm();

		if (Ext.isEmpty(base_form.findField('AgeGroupDisp_id').getValue())) {
			btn.enable();
			sw.swMsg.alert('Ошибка', 'Нельзя сохранить согласие без выбранной возрастной группы.');
			return false;
		}

		win.getLoadMask('Сохранение информированного добровольного согласия').show();
		// берём все записи из грида и посылаем на сервер, разбираем ответ
		// на сервере создать саму карту EvnPLDispTeenInspection, если EvnPLDispTeenInspection_id не задано, сохранить её информ. согласие DopDispInfoConsent, вернуть EvnPLDispTeenInspection_id
		var grid = win.dopDispInfoConsentGrid.getGrid();
		var params = {};

		if ( Ext.isEmpty(base_form.findField('PayType_id').getValue()) ) {
			btn.enable();
			win.getLoadMask().hide();

			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					base_form.findField('PayType_id').focus(true);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});

			return false;
		}

		if ( Ext.isEmpty(win.findById('EPLDTIPRO_EvnPLDispTeenInspection_consDate').getValue()) ) {
			btn.enable();
			win.getLoadMask().hide();

			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					win.findById('EPLDTIPRO_EvnPLDispTeenInspection_consDate').focus(true);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});

			return false;
		}

		var xdate = new Date(2015,0,1);
		if ( getRegionNick().inlist([ 'kareliya' ]) && win.findById('EPLDTIPRO_EvnPLDispTeenInspection_consDate').getValue() >= xdate ) {
			// отказов быть не должно
			var IsOtkaz = false;
			win.dopDispInfoConsentGrid.getGrid().getStore().each(function(rec) {
				if (rec.get('DopDispInfoConsent_IsAgree') != true && rec.get('DopDispInfoConsent_IsEarlier') != true) {
					IsOtkaz = true;
				}
			});

			if (IsOtkaz && !options.ignoreRefuse) {
				btn.enable();
				win.getLoadMask().hide();
				sw.swMsg.show({
					buttons: {yes: 'Сохранить', cancel: 'Отмена'},
					fn: function ( buttonId ) {
						if ( buttonId == 'yes' ) {
							win.saveDopDispInfoConsent({
								ignoreRefuse: true
							});
						}
					},
					msg: 'Карта подлежит оплате только при проведении всех осмотров / исследований. Продолжить сохранение?',
					title: 'Подтверждение'
				});
				return false;
			}
		}

		if (
			getRegionNick().inlist(['ufa'])
			&& win.findById('EPLDTIPRO_EvnPLDispTeenInspection_consDate').getValue() >= new Date(2019, 0, 1)
			// @task https://redmine.swan-it.ru/issues/175976
			&& win.findById('EPLDTIPRO_EvnPLDispTeenInspection_consDate').getValue() < new Date(2019, 7, 1)
		) {
			// отказов быть не должно
			var IsOtkaz = false;
			win.dopDispInfoConsentGrid.getGrid().getStore().each(function(rec) {
				if (rec.get('SurveyType_IsVizit') == 2 && (rec.get('DopDispInfoConsent_IsAgree') != true && rec.get('DopDispInfoConsent_IsEarlier') != true)) {
					IsOtkaz = true;
				}
			});

			if (IsOtkaz) {
				btn.enable();
				win.getLoadMask().hide();
				sw.swMsg.alert("Ошибка", "Проверьте установку флагов. Для включения в реестр и оплаты в карте для всех осмотров должен быть установлен флаг «Согласие» или «Пройдено ранее»");
				return false;
			}
		}

		params.EvnPLDispTeenInspection_consDate = (typeof win.findById('EPLDTIPRO_EvnPLDispTeenInspection_consDate').getValue() == 'object' ? Ext.util.Format.date(win.findById('EPLDTIPRO_EvnPLDispTeenInspection_consDate').getValue(), 'd.m.Y') : win.findById('EPLDTIPRO_EvnPLDispTeenInspection_consDate').getValue());
		params.EvnPLDispTeenInspection_setDate = (typeof win.findById('EPLDTIPRO_EvnPLDispTeenInspection_setDate').getValue() == 'object' ? Ext.util.Format.date(win.findById('EPLDTIPRO_EvnPLDispTeenInspection_setDate').getValue(), 'd.m.Y') : win.findById('EPLDTIPRO_EvnPLDispTeenInspection_setDate').getValue());
		params.EvnPLDispTeenInspection_IsMobile = (base_form.findField('EvnPLDispTeenInspection_IsMobile').checked) ? true : null;
		params.EvnPLDispTeenInspection_IsOutLpu = (base_form.findField('EvnPLDispTeenInspection_IsOutLpu').checked) ? true : null;
		params.Lpu_mid = base_form.findField('Lpu_mid').getValue();
		params.PersonEvn_id = base_form.findField('PersonEvn_id').getValue();
		params.Person_id = base_form.findField('Person_id').getValue();
		params.Server_id = base_form.findField('Server_id').getValue();
		params.EvnPLDispTeenInspection_id = base_form.findField('EvnPLDispTeenInspection_id').getValue();
		params.PersonDispOrp_id = base_form.findField('PersonDispOrp_id').getValue();
		params.EvnPLDispTeenInspection_fid = base_form.findField('EvnPLDispTeenInspection_fid').getValue();
		params.DispClass_id = base_form.findField('DispClass_id').getValue();
		params.PayType_id = base_form.findField('PayType_id').getValue();
		params.AgeGroupDisp_id = base_form.findField('AgeGroupDisp_id').getValue();
		params.Org_id = base_form.findField('Org_id').getValue();

		params.DopDispInfoConsentData = Ext.util.JSON.encode(getStoreRecords( grid.getStore(), {
			exceptionFields: [
				'SurveyType_Name'
			]
		}));

		Ext.Ajax.request(
		{
			url: '/?c=EvnPLDispTeenInspection&m=saveDopDispInfoConsent',
			params: params,
			failure: function(response, options)
			{
				btn.enable();
				win.getLoadMask().hide();
			},
			success: function(response, action)
			{
				btn.enable();
				win.getLoadMask().hide();
				if (response.responseText)
				{
					var answer = Ext.util.JSON.decode(response.responseText);
					if (answer.success && answer.EvnPLDispTeenInspection_id > 0)
					{
						base_form.findField('EvnPLDispTeenInspection_id').setValue(answer.EvnPLDispTeenInspection_id);
						win.checkEvnPLDispTeenInspectionIsSaved();
						// обновляем грид
						grid.getStore().load({
							params: {
								EvnPLDispTeenInspection_id: answer.EvnPLDispTeenInspection_id
							}
						});

						// Перезагружаем форму и запускаем callback, чтобы обновить грид в родительском окне
						win.loadForm(answer.EvnPLDispTeenInspection_id, true);
					}
				}
			}
		});
	},
	printEvnPLDispTeenInspProf: function() {
		var win = this;
		var base_form = this.EvnPLDispTeenInspectionFormPanel.getForm();

		if ( win.action != 'view' ) {
			win.doSave({
				callback: function() {
					var paramEvnPLTeen = base_form.findField('EvnPLDispTeenInspection_id').getValue();
					var paramDispType = base_form.findField('DispClass_id').getValue();
					printBirt({
						'Report_FileName': 'pan_EvnPLTeenCard.rptdesign',
						'Report_Params': '&paramEvnPLTeen=' + paramEvnPLTeen + '&paramDispType=' + paramDispType,
						'Report_Format': 'pdf'
					});
				}
			});
		}
		else {
			var paramEvnPLTeen = base_form.findField('EvnPLDispTeenInspection_id').getValue();
			var paramDispType = base_form.findField('DispClass_id').getValue();
			printBirt({
				'Report_FileName': 'pan_EvnPLTeenCard.rptdesign',
				'Report_Params': '&paramEvnPLTeen=' + paramEvnPLTeen + '&paramDispType=' + paramDispType,
				'Report_Format': 'pdf'
			});
		}
	},
	printEvnPLDispTeenInspMedZak: function(){
		var win = this;
		var base_form = this.EvnPLDispTeenInspectionFormPanel.getForm();

		if ( win.action != 'view' ) {
			win.doSave({
				callback: function() {
					var paramEvnPLTeen = base_form.findField('EvnPLDispTeenInspection_id').getValue();
					printBirt({
						'Report_FileName': 'pan_EvnPLTeenMedZak.rptdesign',
						'Report_Params': '&paramEvnPLTeen=' + paramEvnPLTeen,
						'Report_Format': 'pdf'
					});
				}
			});
		}
		else {
			var paramEvnPLTeen = base_form.findField('EvnPLDispTeenInspection_id').getValue();
			printBirt({
				'Report_FileName': 'pan_EvnPLTeenMedZak.rptdesign',
				'Report_Params': '&paramEvnPLTeen=' + paramEvnPLTeen,
				'Report_Format': 'pdf'
			});
		}
	},
	printKLU: function() {
		var win = this;
		var base_form = this.EvnPLDispTeenInspectionFormPanel.getForm();
		
		var print = function() {
			var evn_pl_id = base_form.findField('EvnPLDispTeenInspection_id').getValue();
			printBirt({
				'Report_FileName': 'CheckList_MedCareOnkoPatients.rptdesign',
				'Report_Params': '&Evn_id=' + evn_pl_id, 
				'Report_Format': 'pdf'
			});
		}
		if ( 'add' == this.action || 'edit' == this.action ) {
            this.doSave({
            	callback:print,
            	print: true
            });
        }
        else if ( 'view' == this.action ) {
            print();
        }
	},
	printOnko: function() {
		var win = this;
		var base_form = this.EvnPLDispTeenInspectionFormPanel.getForm();
		var print = function() {
			var evn_pl_id = base_form.findField('EvnPLDispTeenInspection_id').getValue();
			printBirt({
				'Report_FileName': 'WritingOut_MedCareOnkoPatients.rptdesign',
				'Report_Params': '&Evn_id=' + evn_pl_id,
				'Report_Format': 'pdf'
			});
		}
		if ( 'add' == this.action || 'edit' == this.action ) {
			this.doSave({
				callback:print,
				print: true
			});
		}
		else if ( 'view' == this.action ) {
			print();
		}
	},
	onChangeAge: function(age, setDate) {
		var win = this;
		var base_form = this.EvnPLDispTeenInspectionFormPanel.getForm();
		if ( age >= 0 && age <= 4 ) {
			if ( win.action != 'view' ) {
				base_form.findField('NormaDisturbanceType_id').clearValue();
				base_form.findField('NormaDisturbanceType_uid').clearValue();
				base_form.findField('NormaDisturbanceType_eid').clearValue();

				base_form.findField('AssessmentHealth_Gnostic').enable();
				base_form.findField('AssessmentHealth_Motion').enable();
				base_form.findField('AssessmentHealth_Social').enable();
				base_form.findField('AssessmentHealth_Speech').enable();
				base_form.findField('NormaDisturbanceType_id').disable();
				base_form.findField('NormaDisturbanceType_uid').disable();
				base_form.findField('NormaDisturbanceType_eid').disable();
			}
		}
		else if ( age >= 5 && age <= 17 ) {
			if ( win.action != 'view' ) {
				base_form.findField('AssessmentHealth_Gnostic').setRawValue('');
				base_form.findField('AssessmentHealth_Motion').setRawValue('');
				base_form.findField('AssessmentHealth_Social').setRawValue('');
				base_form.findField('AssessmentHealth_Speech').setRawValue('');

				base_form.findField('AssessmentHealth_Gnostic').disable();
				base_form.findField('AssessmentHealth_Motion').disable();
				base_form.findField('AssessmentHealth_Social').disable();
				base_form.findField('AssessmentHealth_Speech').disable();
				base_form.findField('NormaDisturbanceType_id').enable();
				base_form.findField('NormaDisturbanceType_uid').enable();
				base_form.findField('NormaDisturbanceType_eid').enable();
			}
		}
		else {
			// Закрыть для редактирования все поля блока "Оценка психического развития (состояния)"
			base_form.findField('AssessmentHealth_Gnostic').setRawValue('');
			base_form.findField('AssessmentHealth_Motion').setRawValue('');
			base_form.findField('AssessmentHealth_Social').setRawValue('');
			base_form.findField('AssessmentHealth_Speech').setRawValue('');
			base_form.findField('NormaDisturbanceType_id').clearValue();
			base_form.findField('NormaDisturbanceType_uid').clearValue();
			base_form.findField('NormaDisturbanceType_eid').clearValue();

			base_form.findField('AssessmentHealth_Gnostic').disable();
			base_form.findField('AssessmentHealth_Motion').disable();
			base_form.findField('AssessmentHealth_Social').disable();
			base_form.findField('AssessmentHealth_Speech').disable();
			base_form.findField('NormaDisturbanceType_id').disable();
			base_form.findField('NormaDisturbanceType_uid').disable();
			base_form.findField('NormaDisturbanceType_eid').disable();
		}

		if (!Ext.isEmpty(setDate)) {
			win.blockSaveDopDispInfoConsent = true;
			win.dopDispInfoConsentGrid.loadData({
				params: {
					 Person_id: base_form.findField('Person_id').getValue()
					,DispClass_id: base_form.findField('DispClass_id').getValue()
					,EvnPLDispTeenInspection_id: base_form.findField('EvnPLDispTeenInspection_id').getValue()
					,EvnPLDispTeenInspection_consDate: (typeof setDate == 'object' ? Ext.util.Format.date(setDate, 'd.m.Y') : setDate)
					,AgeGroupDisp_id: base_form.findField('AgeGroupDisp_id').getValue()
				},
				globalFilters: {
					 Person_id: base_form.findField('Person_id').getValue()
					,DispClass_id: base_form.findField('DispClass_id').getValue()
					,EvnPLDispTeenInspection_id: base_form.findField('EvnPLDispTeenInspection_id').getValue()
					,EvnPLDispTeenInspection_consDate: (typeof setDate == 'object' ? Ext.util.Format.date(setDate, 'd.m.Y') : setDate)
					,AgeGroupDisp_id: base_form.findField('AgeGroupDisp_id').getValue()
				},
				noFocusOnLoad: true,
				callback: function() {
					win.blockSaveDopDispInfoConsent = false;
					if (win.saveDopDispInfoConsentAfterLoad) {
						win.saveDopDispInfoConsent();
					}
					win.saveDopDispInfoConsentAfterLoad = false;
				}
			});
		}
	},
	calculatedAgeGroup: null,
	setAgeGroupDispCombo: function(newSetDate, oldSetDate, recalc) {
		var
			win = this,
			base_form = this.EvnPLDispTeenInspectionFormPanel.getForm(),
			age_start = -1,
			month_start = -1,
			age_end = -1,
			Person_Birthday = win.PersonInfoPanel.getFieldValue('Person_Birthday'),
			isBirthdayDate = false,
			dateX20180101 = new Date(2018, 0, 1),
			dateX20180201 = new Date(2018, 1, 1),
			dateX20190101 = new Date(2019, 0, 1),
			dateX31052018 = new Date(2018, 4, 31),
			dateX20180601 = new Date(2018, 5, 1),
			dateX20191031 = new Date(2019, 9, 31),
			dateX20191101 = new Date(2019, 10, 1);

		if (!Ext.isEmpty(newSetDate)) {
			age_start = swGetPersonAge(Person_Birthday, newSetDate);
			var year = newSetDate.getFullYear();
			var endYearDate = new Date(year, 11, 31);
			age_end = swGetPersonAge(Person_Birthday, endYearDate);
			month_start = swGetPersonAgeMonth(Person_Birthday, newSetDate);
		}

		if (typeof Person_Birthday == 'object' && typeof newSetDate == 'object') {
			isBirthdayDate = (Person_Birthday.getDate() == newSetDate.getDate());
		}
		
		var agegroupcombo = base_form.findField('AgeGroupDisp_id');
		agegroupcombo.getStore().clearFilter();
		/*
		log(age_start);
		log(month_start);
		log(age_end);
		*/

		var age1 = 3;
		var age2 = 4;
		if (getRegionNick() != 'kz' && newSetDate >= dateX20180101) {
			age1 = 2;
			age2 = 3;
		}
		var findAgeGroups = function(record) {
			return (
				(
					age_start <= age1 && age_end != age2 &&
					record.get('AgeGroupDisp_From') <= age_start && record.get('AgeGroupDisp_To') >= age_start &&
					record.get('AgeGroupDisp_monthFrom') <= month_start && record.get('AgeGroupDisp_monthTo') >= month_start
				) || (
					age_start == age1 && age_end == age2 &&
					record.get('AgeGroupDisp_From') == age_end && record.get('AgeGroupDisp_To') == age_end
				) || (
					age_start >= age2 &&
					record.get('AgeGroupDisp_From') <= (getRegionNick() == 'ufa' && newSetDate >= dateX20190101 && age_start >= 17 && age_start <= 18  ? age_start : age_end) && record.get('AgeGroupDisp_To') >= (getRegionNick() == 'ufa' && newSetDate >= dateX20190101 && age_start >= 17 && age_start <= 18 ? age_start : age_end)
				)
			);
		};
		if (getRegionNick() == 'kareliya' && newSetDate >= dateX20180201) {
			findAgeGroups = function (record) {
				if(record.get('AgeGroupDisp_From') === 2 && record.get('AgeGroupDisp_To') === 2 && age_start === 2 && age_end === 2) {
					return true;
				}
				return (
					(
						age_start < 2 &&
						record.get('AgeGroupDisp_From') <= age_start && record.get('AgeGroupDisp_To') >= age_start &&
						record.get('AgeGroupDisp_monthFrom') <= month_start && record.get('AgeGroupDisp_monthTo') >= month_start
					) || (
						newSetDate <= dateX31052018 &&
						age_start == 2 && month_start <= 6 &&
						record.get('AgeGroupDisp_From') == 2 && record.get('AgeGroupDisp_To') == 2 &&
						record.get('AgeGroupDisp_monthFrom') == 0 && record.get('AgeGroupDisp_monthTo') == 11
					) || (
						newSetDate <= dateX31052018 &&
						age_start == 2 && month_start > 6 &&
						record.get('AgeGroupDisp_From') == 3 && record.get('AgeGroupDisp_To') == 3 &&
						record.get('AgeGroupDisp_monthFrom') == 0 && record.get('AgeGroupDisp_monthTo') == 11
					) || (
						newSetDate >= dateX20180601 && newSetDate <= dateX20191031 &&
						age_start == 2 && (month_start < 6 || (month_start == 6 && isBirthdayDate == true)) &&
						record.get('AgeGroupDisp_From') == 2 && record.get('AgeGroupDisp_To') == 2 &&
						record.get('AgeGroupDisp_monthFrom') == 0 && record.get('AgeGroupDisp_monthTo') == 11
					) || (
						newSetDate >= dateX20180601 && newSetDate <= dateX20191031 &&
						age_start == 2 && (month_start > 6 || (month_start == 6 && isBirthdayDate == false)) &&
						record.get('AgeGroupDisp_From') == 3 && record.get('AgeGroupDisp_To') == 3 &&
						record.get('AgeGroupDisp_monthFrom') == 0 && record.get('AgeGroupDisp_monthTo') == 11
					) || (
						newSetDate >= dateX20191101 && age_start == 2 &&
						record.get('AgeGroupDisp_From') <= age_end && record.get('AgeGroupDisp_To') >= age_end
					) || (
						age_start >= 3 &&
						record.get('AgeGroupDisp_From') <= age_end && record.get('AgeGroupDisp_To') >= age_end
					) || (
						age_start >= 17 &&
						record.get('AgeGroupDisp_From') <= age_start && record.get('AgeGroupDisp_To') >= age_start
					)
				);
			};
		}
		// определяем расчётную возрастную группу
		var index = agegroupcombo.getStore().findBy(function(record) {
			if (newSetDate && record.get('AgeGroupDisp_begDate') && record.get('AgeGroupDisp_begDate') > newSetDate) {
				return false;
			} else if (newSetDate && record.get('AgeGroupDisp_endDate') && record.get('AgeGroupDisp_endDate') < newSetDate) {
				return false;
			}
			return findAgeGroups(record);
		});
		win.calculatedAgeGroup = null;
		win.beforeCalculatedAgeGroup = null;
		win.afterCalculatedAgeGroup = null;

		var ageFrom = null;
		var ageMonthFrom = null;
		if (index >= 0) {
			win.calculatedAgeGroup = agegroupcombo.getStore().getAt(index).get('AgeGroupDisp_id');
			ageFrom = agegroupcombo.getStore().getAt(index).get('AgeGroupDisp_From');
			ageMonthFrom = agegroupcombo.getStore().getAt(index).get('AgeGroupDisp_monthFrom');
		} else {
			// костыль: если пациент 18 лет, то смежная для него 17 лет
			if (getRegionNick() == 'ekb' && age_end == 18) {
				win.calculatedAgeGroup = 9999;
				ageFrom = 18;
				ageMonthFrom = 0;
			}
		}

		if (getRegionNick() == 'ekb' && win.calculatedAgeGroup) {
			// ищем смежные группы
			win.beforeCalculatedAgeGroupRecord = null;
			win.afterCalculatedAgeGroupRecord = null;

			agegroupcombo.getStore().each(function(record) {
				if (newSetDate && record.get('AgeGroupDisp_begDate') && record.get('AgeGroupDisp_begDate') > newSetDate) {
					return true;
				} else if (newSetDate && record.get('AgeGroupDisp_endDate') && record.get('AgeGroupDisp_endDate') < newSetDate) {
					return true;
				}

				if (
					(
						// группа до текущей
						record.get('AgeGroupDisp_From') < ageFrom
						|| (record.get('AgeGroupDisp_From') == ageFrom && record.get('AgeGroupDisp_monthFrom') < ageMonthFrom)
					) && (
						!win.beforeCalculatedAgeGroupRecord // группа ещё не найдена
						|| ( // либо найденная группа до новой найденной
							win.beforeCalculatedAgeGroupRecord.get('AgeGroupDisp_From') < record.get('AgeGroupDisp_From')
							|| (win.beforeCalculatedAgeGroupRecord.get('AgeGroupDisp_From') == record.get('AgeGroupDisp_From') && win.beforeCalculatedAgeGroupRecord.get('AgeGroupDisp_monthFrom') < record.get('AgeGroupDisp_monthFrom'))
						)
					)
				) {
					win.beforeCalculatedAgeGroupRecord = record;
				}

				if (
					(
						// группа после текущей
						record.get('AgeGroupDisp_From') > ageFrom
						|| (record.get('AgeGroupDisp_From') == ageFrom && record.get('AgeGroupDisp_monthFrom') > ageMonthFrom)
					) && (
						!win.afterCalculatedAgeGroupRecord // группа ещё не найдена
						|| ( // либо найденная группа после новой найденной
							win.afterCalculatedAgeGroupRecord.get('AgeGroupDisp_From') > record.get('AgeGroupDisp_From')
							|| (win.afterCalculatedAgeGroupRecord.get('AgeGroupDisp_From') == record.get('AgeGroupDisp_From') && win.afterCalculatedAgeGroupRecord.get('AgeGroupDisp_monthFrom') > record.get('AgeGroupDisp_monthFrom'))
						)
					)
				) {
					win.afterCalculatedAgeGroupRecord = record;
				}
			});

			if (win.beforeCalculatedAgeGroupRecord) {
				win.beforeCalculatedAgeGroup = win.beforeCalculatedAgeGroupRecord.get('AgeGroupDisp_id');
			}
			if (win.afterCalculatedAgeGroupRecord) {
				win.afterCalculatedAgeGroup = win.afterCalculatedAgeGroupRecord.get('AgeGroupDisp_id');
			}
		}

		// если нет направления
		if (recalc && Ext.isEmpty(base_form.findField('PersonDispOrp_id').getValue())) {
			var oldValue = agegroupcombo.getValue();
			var newValue = null;
			agegroupcombo.getStore().filterBy(function(record) {
				if (newSetDate && record.get('AgeGroupDisp_begDate') && record.get('AgeGroupDisp_begDate') > newSetDate) {
					return false;
				} else if (newSetDate && record.get('AgeGroupDisp_endDate') && record.get('AgeGroupDisp_endDate') < newSetDate) {
					return false;
				}

				return findAgeGroups(record);
			});
			
			if (agegroupcombo.getStore().getCount() > 0) {
				newValue = agegroupcombo.getStore().getAt(0).get('AgeGroupDisp_id');
			}

			if (getRegionNick() == 'pskov') {
				agegroupcombo.getStore().filterBy(function(record) {
					if (newSetDate && record.get('AgeGroupDisp_begDate') && record.get('AgeGroupDisp_begDate') > newSetDate) {
						return false;
					} else if (newSetDate && record.get('AgeGroupDisp_endDate') && record.get('AgeGroupDisp_endDate') < newSetDate) {
						return false;
					}

					return true;
				});
			} else if (getRegionNick() == 'ekb' && win.calculatedAgeGroup) {
				// особый фильтр, своя возрастная группа + смежные
				agegroupcombo.getStore().filterBy(function(record) {
					if (record.get('AgeGroupDisp_id') == win.calculatedAgeGroup) {
						return true;
					}
					if (record.get('AgeGroupDisp_id') == win.beforeCalculatedAgeGroup) {
						return true;
					}
					if (record.get('AgeGroupDisp_id') == win.afterCalculatedAgeGroup) {
						return true;
					}

					return false;
				});
			}

			win.onChangeAgeGroupDisp(oldValue, newValue, oldSetDate, newSetDate);
		} else {
			if (getRegionNick() == 'pskov') {
				agegroupcombo.getStore().filterBy(function (record) {
					if (newSetDate && record.get('AgeGroupDisp_begDate') && record.get('AgeGroupDisp_begDate') > newSetDate) {
						return false;
					} else if (newSetDate && record.get('AgeGroupDisp_endDate') && record.get('AgeGroupDisp_endDate') < newSetDate) {
						return false;
					}

					return true;
				});
			} else if (getRegionNick() == 'ekb' && win.calculatedAgeGroup) {
				// особый фильтр, своя возрастная группа + смежные
				agegroupcombo.getStore().filterBy(function (record) {
					if (record.get('AgeGroupDisp_id') == win.calculatedAgeGroup) {
						return true;
					}
					if (record.get('AgeGroupDisp_id') == win.beforeCalculatedAgeGroup) {
						return true;
					}
					if (record.get('AgeGroupDisp_id') == win.afterCalculatedAgeGroup) {
						return true;
					}

					return false;
				});
			}
			if (!Ext.isEmpty(agegroupcombo.getValue())) {
				agegroupcombo.setValue(agegroupcombo.getValue());
			}
			win.onChangeAge(agegroupcombo.getFieldValue('AgeGroupDisp_From'), newSetDate);

			base_form.findField('AgeGroupDisp_id').setValue(agegroupcombo.getStore().getAt(index).data.AgeGroupDisp_id);
		}
	},
	onChangeAgeGroupDisp: function(oldValue, newValue, oldSetDate, newSetDate) {
		var win = this;
		var base_form = this.EvnPLDispTeenInspectionFormPanel.getForm();
		var agegroupcombo = base_form.findField('AgeGroupDisp_id');

		if (!Ext.isEmpty(oldValue) && newValue != oldValue && win.checkEvnPLDispTeenInspectionIsSaved() && !Ext.isEmpty(oldSetDate)) {
			sw.swMsg.show({
				buttons: Ext.Msg.YESNO,
				fn: function ( buttonId ) {
					if ( buttonId == 'yes' )
					{
						agegroupcombo.setValue(newValue);
						win.onChangeAge(agegroupcombo.getFieldValue('AgeGroupDisp_From'), newSetDate);
					}
					else
					{
						agegroupcombo.setValue(oldValue);
						if (oldSetDate) {
							win.findById('EPLDTIPRO_EvnPLDispTeenInspection_setDate').setValue(oldSetDate);
						}
						win.loadUslugaComplex();
					}
				},
				msg: 'При изменении значения в поле "Возрастная группа" изменится набор осмотров/исследований профилактического осмотра. Информация по введённым осмотрам/исследованиям может быть потеряна. Изменить?',
				title: 'Подтверждение'
			});
			return false;
		} else {
			agegroupcombo.setValue(newValue);
			win.onChangeAge(agegroupcombo.getFieldValue('AgeGroupDisp_From'), newSetDate);
		}
	},
	checkForCostPrintPanel: function() {
		var base_form = this.EvnPLDispTeenInspectionFormPanel.getForm();

		this.CostPrintPanel.hide();
		base_form.findField('EvnCostPrint_setDT').setAllowBlank(true);
		base_form.findField('EvnCostPrint_IsNoPrint').setAllowBlank(true);

		// если справка уже печаталась и случай закрыт, отображаем раздел с данными справки
		if (base_form.findField('EvnPLDispTeenInspection_IsFinish').getValue() == 2 && !Ext.isEmpty(base_form.findField('EvnCostPrint_setDT').getValue()) && getRegionNick().inlist(['perm', 'kz', 'ufa'])) {
			this.CostPrintPanel.show();
			// поля обязтаельные
			base_form.findField('EvnCostPrint_setDT').setAllowBlank(false);
			base_form.findField('EvnCostPrint_IsNoPrint').setAllowBlank(false);
		}
	},
	show: function() {
		sw.Promed.swEvnPLDispTeenInspectionProfEditWindow.superclass.show.apply(this, arguments);
		
		if (!arguments[0])
		{
			Ext.Msg.alert('Сообщение', 'Неверные параметры');
			return false;
		}
		
		var win = this;
		win.getLoadMask(LOAD_WAIT).show();

		this.formStatus = 'edit';
		this.restore();
		this.center();
		this.maximize();
		this.orpAdoptedMOEmptyCode = true;
		this.orpAdoptedMODateincorrect = true;

		win.blockSaveDopDispInfoConsent = false;
		win.saveDopDispInfoConsentAfterLoad = false;
		win.ignoreEmptyFields = false;

		var form = this.EvnPLDispTeenInspectionFormPanel;
		form.getForm().findField('VaccinType_id').hideContainer();
		// добавляем чекбоксы типов прививок
		var vaccinFieldset = this.findById('EPLDTIPRO_VaccinFieldset');
		if (vaccinFieldset.items.items.length == 0) {
			form.getForm().findField('VaccinType_id').getStore().each(function(rec) {
				vaccinFieldset.add(
					new Ext.form.Checkbox({
						name: 'VaccinType',
						hideLabel: true,
						value: rec.get('VaccinType_id'),
						boxLabel: rec.get('VaccinType_Name')
					})
				);
			});
		}

		vaccinFieldset.items.items.forEach( function (item) {
			item.setValue(0); // чекбокс off
		});

		form.getForm().findField('ProfVaccinType_id').fireEvent('change', form.getForm().findField('ProfVaccinType_id'), form.getForm().findField('ProfVaccinType_id').getValue());

		form.getForm().reset();
		this.checkForCostPrintPanel();

		win.DispAppointGrid.getGrid().getStore().removeAll();

		win.findById('EPLDTIPRO_EvnPLDispTeenInspection_consDate').setRawValue('');
		Ext.getCmp('EPLDTIPRO_PrintKLU').disable();
		Ext.getCmp('EPLDTIPRO_PrintOnko').disable();

		this.PersonFirstStageAgree = false; // Пациент не согласен на этап диспансеризации
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

		//Проверяем возможность редактирования документа
		if (this.action === 'edit' && arguments[0].EvnPLDispTeenInspection_id) {
			Ext.Ajax.request({
				failure: function (response, options) {
					sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_zagruzke_dannyih_formyi'], function () {
						this.hide();
					}.createDelegate(this));
				},
				params: {
					Evn_id: arguments[0].EvnPLDispTeenInspection_id,
					MedStaffFact_id: (!Ext.isEmpty(sw.Promed.MedStaffFactByUser) && !Ext.isEmpty(sw.Promed.MedStaffFactByUser.current) && !Ext.isEmpty(sw.Promed.MedStaffFactByUser.current.MedStaffFact_id)) ? sw.Promed.MedStaffFactByUser.current.MedStaffFact_id : null,
					ArmType: (!Ext.isEmpty(sw.Promed.MedStaffFactByUser) && !Ext.isEmpty(sw.Promed.MedStaffFactByUser.current) && !Ext.isEmpty(sw.Promed.MedStaffFactByUser.current.ARMType)) ? sw.Promed.MedStaffFactByUser.current.ARMType : null
				},
				success: function (response, options) {
					if (!Ext.isEmpty(response.responseText)) {
						var response_obj = Ext.util.JSON.decode(response.responseText);

						if (response_obj.success == false) {
							sw.swMsg.alert(lang['oshibka'], response_obj.Error_Msg ? response_obj.Error_Msg : lang['oshibka_pri_zagruzke_dannyih_formyi']);
							this.action = 'view';
						}

						if (response_obj.Alert_Msg) {
							sw.swMsg.alert(lang['vnimanie'], response_obj.Alert_Msg);
						}
					}

					//вынес продолжение show в отдельную функцию, т.к. иногда callback приходит после выполнения логики
					this.onShow();
				}.createDelegate(this),
				url: '/?c=Evn&m=CommonChecksForEdit'
			});
		} else {
			this.onShow();
		}
	},
	
	onShow: function(){
		var win = this;
		var form = this.EvnPLDispTeenInspectionFormPanel;
		var base_form = this.EvnPLDispTeenInspectionFormPanel.getForm();
		var EvnPLDispTeenInspection_id = base_form.findField('EvnPLDispTeenInspection_id').getValue();
		var person_id = base_form.findField('Person_id').getValue();
		var server_id = base_form.findField('Server_id').getValue();
		var DispClass_id = base_form.findField('DispClass_id').getValue();

		base_form.findField('EvnPLDispTeenInspection_RepFlag').hideContainer();
		base_form.findField('Diag_spid').hideContainer();

		if (Ext.isEmpty(base_form.findField('PayType_id').getValue())) {
			base_form.findField('PayType_id').setFieldValue('PayType_SysNick', 'oms');
		}

        base_form.findField('AssessmentHealth_setDT').setAllowBlank(true);
        base_form.findField('AssessmentHealth_reExamDT').setAllowBlank(true);
        base_form.findField('InvalidDiagType_id').setAllowBlank(true);

		base_form.findField('HeightAbnormType_YesNo').setValue(1);
		base_form.findField('WeightAbnormType_YesNo').setValue(1);

		base_form.findField('WeightAbnormType_YesNo').fireEvent('change', base_form.findField('WeightAbnormType_YesNo'), base_form.findField('WeightAbnormType_YesNo').getValue());
		base_form.findField('HeightAbnormType_YesNo').fireEvent('change', base_form.findField('HeightAbnormType_YesNo'), base_form.findField('HeightAbnormType_YesNo').getValue());
		
		win.DopDispInfoConsentPanel.setTitle('Информированное добровольное согласие');
		
		if (win.action == 'edit') {
			win.setTitle('Профилактический осмотр несовершеннолетнего - 1 этап: Редактирование');
		} else {
			win.setTitle('Профилактический осмотр несовершеннолетнего - 1 этап: Просмотр');
		}
		
		// пока не сохранена карта (сохраняется при информационно добровольном согласии) нельзя редактировать разделы кроме согласия
		this.checkEvnPLDispTeenInspectionIsSaved();
		
		inf_frame_is_loaded = false;

		var orgcombo = base_form.findField('Org_id');
		if (!Ext.isEmpty(orgcombo.getValue())) {
			orgcombo.getStore().load({
				params: {
					Object:'Org',
					Org_id: orgcombo.getValue(),
					Org_Name:''
				},
				callback: function()
				{
					orgcombo.setValue(orgcombo.getValue());
					orgcombo.focus(true, 500);
					orgcombo.fireEvent('change', orgcombo);
				}
			});
		}
		
		this.PersonInfoPanel.load({ 
			Person_id: person_id, 
			Server_id: server_id, 
			callback: function() {
				win.getLoadMask().hide();
				inf_frame_is_loaded = true; 
				
				var sex_id = win.PersonInfoPanel.getFieldValue('Sex_id');
				var age = win.PersonInfoPanel.getFieldValue('Person_Age');

				if (getRegionNick().inlist([ 'perm', 'ufa' ]) && Ext.isEmpty(EvnPLDispTeenInspection_id) && age < 3 && win.PersonInfoPanel.getFieldValue('Lpu_id') != getGlobalOptions().lpu_id) {
					sw.swMsg.alert('Внимание!', 'Дети младше 3-х лет должны проходить профилактический осмотр по месту основного прикрепления');
				}

				base_form.findField('Server_id').setValue(win.PersonInfoPanel.getFieldValue('Server_id'));
				base_form.findField('PersonEvn_id').setValue(win.PersonInfoPanel.getFieldValue('PersonEvn_id'));
				
				if ( sex_id == 1 ) {
					// скрыть поля для девочек
					base_form.findField('AssessmentHealth_Ma').hideContainer();
					base_form.findField('AssessmentHealth_Me').hideContainer();
					win.findById('EPLDTIPRO_menarhe').hide();
					win.findById('EPLDTIPRO_menses').hide();
				}
				else {
					base_form.findField('AssessmentHealth_Ma').showContainer();
					base_form.findField('AssessmentHealth_Me').showContainer();
					win.findById('EPLDTIPRO_menarhe').show();
					win.findById('EPLDTIPRO_menses').show();
				}
				
				if ( sex_id == 2 ) {
					// скрыть поля для мальчиков
					base_form.findField('AssessmentHealth_Fa').hideContainer();
				}
				else {
					base_form.findField('AssessmentHealth_Fa').showContainer();
				}
				
				if (win.action == 'edit') {
					win.enableEdit(true);
				} else {
					win.enableEdit(false);
				}
				Ext.getCmp('EPLDTIPRO_PrintKLU').hide();
				Ext.getCmp('EPLDTIPRO_PrintOnko').hide();

				base_form.findField('EvnPLDispTeenInspection_IsMobile').fireEvent('check', base_form.findField('EvnPLDispTeenInspection_IsMobile'), base_form.findField('EvnPLDispTeenInspection_IsMobile').getValue());
				base_form.findField('OrgExist').fireEvent('check', base_form.findField('OrgExist'), base_form.findField('OrgExist').getValue());

				if (!Ext.isEmpty(EvnPLDispTeenInspection_id)) {
					win.loadForm(EvnPLDispTeenInspection_id);
				}
				else {
					// Грузим текущую дату
					setCurrentDateTime({
						callback: function(date) {
							win.findById('EPLDTIPRO_EvnPLDispTeenInspection_consDate').fireEvent('change', win.findById('EPLDTIPRO_EvnPLDispTeenInspection_consDate'), date);
						},
						dateField: win.findById('EPLDTIPRO_EvnPLDispTeenInspection_consDate'),
						loadMask: true,
						setDate: true,
						setDateMaxValue: true,
						windowId: win.id
					});
                    setCurrentDateTime({
						callback: function(date) {
							win.setAgeGroupDispCombo(date, null, true);
						},
                        dateField: win.findById('EPLDTIPRO_EvnPLDispTeenInspection_setDate'),
                        loadMask: true,
                        setDate: true,
                        setDateMaxValue: true,
                        windowId: win.id
                    });
				}
				
				win.buttons[0].focus();
			} 
		});
		
		form.getForm().clearInvalid();
		this.doLayout();
	},
	
	loadForm: function(EvnPLDispTeenInspection_id, allowCallback) {
	
		var win = this;
		var base_form = this.EvnPLDispTeenInspectionFormPanel.getForm();
		win.getLoadMask(LOAD_WAIT).show();

		base_form.load({
			failure: function() {
				win.getLoadMask().hide();
				swEvnPLDispTeenInspectionProfEditWindow.hide();
			},
			params: {
				EvnPLDispTeenInspection_id: EvnPLDispTeenInspection_id,
				archiveRecord: win.archiveRecord
			},
			success: function(form, action) {
				var vaccinFieldset = win.findById('EPLDTIPRO_VaccinFieldset');
				if (action.response && action.response.responseText) {
					var response = Ext.util.JSON.decode(action.response.responseText);
					if (response[0] && response[0].AssessmentHealthVaccinData) {
						vaccinFieldset.items.items.forEach(function (item) {
							if (item.value.inlist(response[0].AssessmentHealthVaccinData)) {
								item.setValue(1); // чекбокс on
							} else {
								item.setValue(0); // чекбокс off
							}
						});
					}
				}

				
				Ext.getCmp('EPLDTIPRO_PrintKLU').show();
				if (getRegionNick() == 'ekb') {
					Ext.getCmp('EPLDTIPRO_PrintOnko').show();
				}

				if (!Ext.isEmpty(base_form.findField('EvnPLDispTeenInspection_IsSuspectZNO')) && base_form.findField('EvnPLDispTeenInspection_IsSuspectZNO').getValue() == 2) {
					Ext.getCmp('EPLDTIPRO_PrintKLU').enable();
					Ext.getCmp('EPLDTIPRO_PrintOnko').enable();
				} else {
					Ext.getCmp('EPLDTIPRO_PrintKLU').disable();
					Ext.getCmp('EPLDTIPRO_PrintOnko').disable();
				}

				base_form.findField('ProfVaccinType_id').fireEvent('change', base_form.findField('ProfVaccinType_id'), base_form.findField('ProfVaccinType_id').getValue());

				win.getLoadMask().hide();
				
				if ( base_form.findField('accessType').getValue() == 'view' ) {
					win.action = 'view';
					win.enableEdit(false);
				}

				if ( getRegionNick() == 'perm' && base_form.findField('EvnPLDispTeenInspection_IsPaid').getValue() == 2 && parseInt(base_form.findField('EvnPLDispTeenInspection_IndexRepInReg').getValue()) > 0 ) {
					base_form.findField('EvnPLDispTeenInspection_RepFlag').showContainer();

					if ( parseInt(base_form.findField('EvnPLDispTeenInspection_IndexRep').getValue()) >= parseInt(base_form.findField('EvnPLDispTeenInspection_IndexRepInReg').getValue()) ) {
						base_form.findField('EvnPLDispTeenInspection_RepFlag').setValue(true);
					}
					else {
						base_form.findField('EvnPLDispTeenInspection_RepFlag').setValue(false);
					}
				}

				win.checkForCostPrintPanel();
				
				// грузим грид услуг
				win.evnUslugaDispDopGrid.loadData({
					params: { EvnPLDispTeenInspection_id: EvnPLDispTeenInspection_id, object: 'EvnPLDispTeenInspection' }, globalFilters: { EvnPLDispTeenInspection_id: EvnPLDispTeenInspection_id }, noFocusOnLoad: true
				});
				
				// грузим грид рекомендаций
				win.EvnDiagAndRecomendationGrid.loadData({
					params: { EvnPLDispTeenInspection_id: EvnPLDispTeenInspection_id, object: 'EvnPLDispTeenInspection' }, globalFilters: { EvnPLDispTeenInspection_id: EvnPLDispTeenInspection_id }, noFocusOnLoad: true
				});
				
				// грузим грид диагнозов и рекомендаций
				win.EvnDiagDopDispAndRecomendationGrid.loadData({
					params: { EvnPLDisp_id: EvnPLDispTeenInspection_id, object: 'EvnPLDispTeenInspection' }, globalFilters: { EvnPLDisp_id: EvnPLDispTeenInspection_id }, noFocusOnLoad: true
				});

				if (getRegionNick() != 'kz') {
					win.DispAppointGrid.loadData({
						params: {EvnPLDisp_id: EvnPLDispTeenInspection_id, object: 'EvnPLDispTeenInspection'},
						globalFilters: {EvnPLDisp_id: EvnPLDispTeenInspection_id},
						noFocusOnLoad: true
					});
				}

				if (Ext.isEmpty(base_form.findField('PayType_id').getValue())) {
					base_form.findField('PayType_id').setFieldValue('PayType_SysNick', 'oms');
				}
		
				if ( base_form.findField('EvnPLDispTeenInspection_IsFinish').getValue() == 2 ) {
				//Проверка на Группу здоровья
					base_form.findField('HealthKind_id').setAllowBlank(false);
					base_form.findField('HealthKind_id').validate();
				}else{
					base_form.findField('HealthKind_id').setAllowBlank(true);
					base_form.findField('HealthKind_id').validate();
				}
				win.setVacNameStat(base_form.findField('ProfVaccinType_id').getValue());
				var orgcombo = base_form.findField('Org_id');
				if (!Ext.isEmpty(orgcombo.getValue())) {
					orgcombo.getStore().load({
						params: {
							Object:'Org',
							Org_id: orgcombo.getValue(),
							Org_Name:''
						},
						callback: function()
						{
							orgcombo.setValue(orgcombo.getValue());
							orgcombo.focus(true, 500);
							orgcombo.fireEvent('change', orgcombo);
						}
					});
				}
				
				var lpucombo = base_form.findField('Lpu_mid');
				if (!Ext.isEmpty(lpucombo.getValue())) {
					lpucombo.getStore().load({
						params: {
							OrgType: 'lpu',
							Lpu_oid: lpucombo.getValue()
						},
						callback: function()
						{
							lpucombo.setValue(lpucombo.getValue());
							lpucombo.focus(true, 500);
							lpucombo.fireEvent('change', lpucombo);
						}
					});
				}
				
				base_form.findField('WeightAbnormType_YesNo').fireEvent('change', base_form.findField('WeightAbnormType_YesNo'), base_form.findField('WeightAbnormType_YesNo').getValue());
				base_form.findField('HeightAbnormType_YesNo').fireEvent('change', base_form.findField('HeightAbnormType_YesNo'), base_form.findField('HeightAbnormType_YesNo').getValue());
				base_form.findField('OrgExist').fireEvent('check', base_form.findField('OrgExist'), base_form.findField('OrgExist').getValue());
				base_form.findField('EvnPLDispTeenInspection_IsMobile').fireEvent('check', base_form.findField('EvnPLDispTeenInspection_IsMobile'), base_form.findField('EvnPLDispTeenInspection_IsMobile').getValue());
				
				win.findById('EPLDTIPRO_EvnPLDispTeenInspection_consDate').setValue(base_form.findField('EvnPLDispTeenInspection_consDate').getValue());
				win.findById('EPLDTIPRO_EvnPLDispTeenInspection_consDate').fireEvent('change', win.findById('EPLDTIPRO_EvnPLDispTeenInspection_consDate'), win.findById('EPLDTIPRO_EvnPLDispTeenInspection_consDate').getValue());

                win.findById('EPLDTIPRO_EvnPLDispTeenInspection_setDate').setValue(base_form.findField('EvnPLDispTeenInspection_setDate').getValue());
                win.loadUslugaComplex();
				win.setAgeGroupDispCombo(win.findById('EPLDTIPRO_EvnPLDispTeenInspection_setDate').getValue(), null, false);

				if ( allowCallback == true ) {
					win.callback({evnPLDispTeenInspectionData: win.getDataForCallBack()});
				}
                base_form.findField('InvalidType_id').fireEvent('change',base_form.findField('InvalidType_id'),base_form.findField('InvalidType_id').getValue());

                if(Ext.isEmpty(base_form.findField('HealthGroupType_oid').getValue())) //https://redmine.swan.perm.ru/issues/108777
				{
					Ext.Ajax.request({
						params: {
							EvnPLDispTeenInspection_id: EvnPLDispTeenInspection_id,
							Person_id: base_form.findField('Person_id').getValue(),
							Lpu_id: getGlobalOptions().lpu_id,
							DispClass_id: 10
						},
						success: function (response, options) {
							if (!Ext.isEmpty(response.responseText)) {
								var response_obj = Ext.util.JSON.decode(response.responseText);

								if(!Ext.isEmpty(response_obj) && !Ext.isEmpty(response_obj[0]) && !Ext.isEmpty(response_obj[0].HealthGroupType_id))
									base_form.findField('HealthGroupType_oid').setValue(response_obj[0].HealthGroupType_id);
								else
									base_form.findField('HealthGroupType_oid').setValue(1);

								base_form.findField('HealthGroupType_oid').fireEvent('change',base_form.findField('HealthGroupType_oid'),base_form.findField('HealthGroupType_oid').getValue());
							}

						}.createDelegate(this),
						url: '/?c=EvnPLDisp&m=getPrevHealthGroupType'
					});
				}
				
				base_form.findField('Diag_spid').setContainerVisible(base_form.findField('EvnPLDispTeenInspection_IsSuspectZNO').getValue() == 2);
				base_form.findField('Diag_spid').setAllowBlank(!getRegionNick().inlist([ 'astra', 'perm' ]) || base_form.findField('EvnPLDispTeenInspection_IsSuspectZNO').getValue() != 2);
				var diag_spid = base_form.findField('Diag_spid').getValue();
				if (diag_spid) {
					base_form.findField('Diag_spid').getStore().load({
						callback:function () {
							base_form.findField('Diag_spid').getStore().each(function (rec) {
								if (rec.get('Diag_id') == diag_spid) {
									base_form.findField('Diag_spid').fireEvent('select', base_form.findField('Diag_spid'), rec, 0);
								}
							});
						},
						params:{where:"where DiagLevel_id = 4 and Diag_id = " + diag_spid}
					});
				}
			},
			url: '/?c=EvnPLDispTeenInspection&m=loadEvnPLDispTeenInspectionEditForm'
		});
		
	},
	title: '',
	width: 800
}
);
