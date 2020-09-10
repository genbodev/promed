/**
* swEvnPLDispTeenInspectionPredEditWindow - окно редактирования/добавления талона по дополнительной диспансеризации
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
* @comment		Префикс для id компонентов EPLDTIPRE (EvnPLDispTeenInspectionEditForm)
*
*/
/*NO PARSE JSON*/
sw.Promed.swEvnPLDispTeenInspectionPredEditWindow = Ext.extend(sw.Promed.BaseForm, {
	action: 'add',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	collapsible: true,
	/* */
	codeRefresh: true,
	objectName: 'swEvnPLDispTeenInspectionPredEditWindow',
	objectSrc: '/jscore/Forms/Polka/swEvnPLDispTeenInspectionPredEditWindow.js',
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
		response.ua_name = personinfo.getFieldValue('Person_RAddress');
		response.pa_name = personinfo.getFieldValue('Person_PAddress');
		response.EvnPLDispTeenInspection_setDate = typeof base_form.findField('EvnPLDispTeenInspection_setDate').getValue() == 'object' ? base_form.findField('EvnPLDispTeenInspection_setDate').getValue() : Date.parseDate(base_form.findField('EvnPLDispTeenInspection_setDate').getValue(), 'd.m.Y');
		response.EvnPLDispTeenInspection_disDate = typeof base_form.findField('EvnPLDispTeenInspection_disDate').getValue() == 'object' ? base_form.findField('EvnPLDispTeenInspection_disDate').getValue() : Date.parseDate(base_form.findField('EvnPLDispTeenInspection_disDate').getValue(), 'd.m.Y');
		response.EvnPLDispTeenInspection_VizitCount = null; // TODO
		response.EvnPLDispTeenInspection_IsFinish = (base_form.findField('EvnPLDispTeenInspection_IsFinish').getValue() == 2) ? lang['da']:lang['net'];
		if (base_form.findField('EvnCostPrint_IsNoPrint').getValue() == 2) {
			response.EvnCostPrint_IsNoPrintText = lang['otkaz_ot_spravki'];
		} else if (base_form.findField('EvnCostPrint_IsNoPrint').getValue() == 1) {
			response.EvnCostPrint_IsNoPrintText = lang['spravka_vyidana'];
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
			base_form.findField('UslugaComplex_id').getStore().baseParams.UslugaComplex_Date = (typeof win.findById('EPLDTIPRE_EvnPLDispTeenInspection_setDate').getValue() == 'object' ? Ext.util.Format.date(win.findById('EPLDTIPRE_EvnPLDispTeenInspection_setDate').getValue(), 'd.m.Y') : win.findById('EPLDTIPRE_EvnPLDispTeenInspection_setDate').getValue());
			base_form.findField('UslugaComplex_id').getStore().baseParams.Person_id = base_form.findField('Person_id').getValue();
			base_form.findField('UslugaComplex_id').getStore().baseParams.EducationInstitutionType_id = base_form.findField('EducationInstitutionType_id').getValue();
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
					EvnPLDispTeenInspection_form.getFirstInvalidEl().focus(false);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		if (Ext.isEmpty(win.EvnPLDispTeenInspectionFormPanel.getForm().findField('Org_id').getFieldValue('OrgStac_Code')) && this.orpAdoptedMOEmptyCode) {

			sw.swMsg.show({
				icon: Ext.MessageBox.QUESTION,
				msg: lang['u_vyibrannogo_obrazovatelnogo_uchrejdeniya_otsutstvuet_federalnyiy_kod_sohranit'],
				title: lang['vopros'],
				buttons: Ext.Msg.YESNO,
				fn: function(buttonId, text, obj) {
					if ('yes' == buttonId) {
						win.orpAdoptedMOEmptyCode = false;
						win.doSave(options);
					}
				}
			});
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
					title: lang['vopros'],
					buttons: Ext.Msg.YESNO,
					fn: function(buttonId, text, obj) {
						if ('yes' == buttonId) {
							win.orpAdoptedMODateincorrect = false;
							win.doSave(options);
						}
					}
				});
				return false;
			}
		}

		if ( Ext.isEmpty(win.findById('EPLDTIPRE_EvnPLDispTeenInspection_consDate').getValue()) ) {
			win.getLoadMask().hide();

			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					win.findById('EPLDTIPRE_EvnPLDispTeenInspection_consDate').focus(true);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});

			return false;
		}

        if ( Ext.isEmpty(win.findById('EPLDTIPRE_EvnPLDispTeenInspection_setDate').getValue()) ) {
            win.getLoadMask().hide();

            sw.swMsg.show({
                buttons: Ext.Msg.OK,
                fn: function() {
                    win.findById('EPLDTIPRE_EvnPLDispTeenInspection_setDate').focus(true);
                },
                icon: Ext.Msg.WARNING,
                msg: ERR_INVFIELDS_MSG,
                title: ERR_INVFIELDS_TIT
            });

            return false;
        }

		win.verfGroup();
		
		base_form.findField('EvnPLDispTeenInspection_consDate').setValue(typeof win.findById('EPLDTIPRE_EvnPLDispTeenInspection_consDate').getValue() == 'object' ? Ext.util.Format.date(win.findById('EPLDTIPRE_EvnPLDispTeenInspection_consDate').getValue(), 'd.m.Y') : win.findById('EPLDTIPRE_EvnPLDispTeenInspection_consDate').getValue());

		base_form.findField('EvnPLDispTeenInspection_setDate').setValue(typeof win.findById('EPLDTIPRE_EvnPLDispTeenInspection_setDate').getValue() == 'object' ? Ext.util.Format.date(win.findById('EPLDTIPRE_EvnPLDispTeenInspection_setDate').getValue(), 'd.m.Y') : win.findById('EPLDTIPRE_EvnPLDispTeenInspection_setDate').getValue());

		//проверки из задачи https://redmine.swan.perm.ru/issues/74660
		if(getGlobalOptions().disp_control != 1) //Если выбрано предупреждение или запрет
		{
			//Получаем возраст и пол:
			var age = win.PersonInfoPanel.getFieldValue('Person_Age');
			var sex_id = win.PersonInfoPanel.getFieldValue('Sex_id');//1-муж, 2-жен
			var fields_list = "";
			if ( age >= 0 && age <= 4 ){
				if(Ext.isEmpty(base_form.findField('AssessmentHealth_Gnostic').getValue()))
					fields_list += lang['poznavatelnaya_funktsiya'];
				if(Ext.isEmpty(base_form.findField('AssessmentHealth_Motion').getValue()))
					fields_list += lang['motornaya_funktsiya'];
				if(Ext.isEmpty(base_form.findField('AssessmentHealth_Social').getValue()))
					fields_list += lang['emotsionalnaya_i_sotsialnaya_funktsii'];
				if(Ext.isEmpty(base_form.findField('AssessmentHealth_Speech').getValue()))
					fields_list += lang['predrechevoe_i_rechevoe_razvitie'];
			}
			if(age >= 5)
			{
				if(Ext.isEmpty(base_form.findField('NormaDisturbanceType_id').getValue()))
					fields_list += lang['psihomotornaya_sfera'];
				if(Ext.isEmpty(base_form.findField('NormaDisturbanceType_uid').getValue()))
					fields_list += lang['intellekt'];
				if(Ext.isEmpty(base_form.findField('NormaDisturbanceType_eid').getValue()))
					fields_list += lang['emotsionalno-vegetativnaya_sfera'];
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
						title: lang['preduprejdenie']
					});
					return false;
				}
				if(getGlobalOptions().disp_control == 3)
				{
					sw.swMsg.alert(lang['oshibka'], lang['ne_zapolnenyi_polya_obyazatelnyie_pri_eksporte_na_federalnyiy_portal'] + fields_list);
					return false;
				}
			}

		}

		var EvnPLDispDop_setDate = win.findById('EPLDTIPRE_EvnPLDispTeenInspection_setDate').getValue();

		// При сохранении карты диспансеризации реализовать контроль: Дата оказания любой услуги (осмотра/исследования) должна быть не меньше, чем за месяц до осмотра
		// врача-терапевта. При невыполнении данного контроля выводить сообщение: "Дата любого исследования не может быть раньше, чем 1 месяц до даты осмотра врача-педиатра (ВОП)", сохранение отменить.
		var EvnUslugaDispDop_minDate, EvnUslugaDispDop_pedDate, EvnUslugaDispDop_maxDate, EvnUslugaDispDop_fluDate;
		var age = win.PersonInfoPanel.getFieldValue('Person_Age');

		var ErrorPedMsg = lang['data_lyubogo_issledovaniya_ne_mojet_byit_ranshe_chem_1_mesyats_do_datyi_osmotra_vracha-pediatra_vop'];
		var monthPed = 1;
		if (age >= 2) {
			ErrorPedMsg = lang['data_lyubogo_issledovaniya_ne_mojet_byit_ranshe_chem_3_mesyatsa_do_datyi_osmotra_vracha-pediatra_vop'];
			monthPed = 3;
		}

		// Вытаскиваем минимальную дату услуги и дату осмотра врачом терапевтом, а также дату проведения флюорографии
		this.evnUslugaDispDopGrid.getGrid().getStore().each(function(rec) {
			if ( !Ext.isEmpty(rec.get('EvnUslugaDispDop_didDate')) ) {
				if ( rec.get('SurveyType_Code') == 16 ) {
					EvnUslugaDispDop_fluDate = rec.get('EvnUslugaDispDop_didDate');
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
					sw.swMsg.alert(lang['oshibka'], lang['sohranenyi_ne_vse_obyazatelnyie_osmotryi_issledovaniya']);
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
					if (!Ext.isEmpty(rec.get('DopDispInfoConsent_id'))) {
						kolvo++;
						if ( !Ext.isEmpty(rec.get('EvnUslugaDispDop_didDate')) ) {
							kolvoAgree++;
						}
					}
				});
				if (kolvoAgree < kolvo) {
					sw.swMsg.alert(lang['oshibka'], lang['sluchay_ne_mojet_byit_zakonchen_tak_kak_zapolnenyi_ne_vse_issledovaniya_ili_osmotryi']);
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
					sw.swMsg.alert(lang['preduprejdenie'], lang['zavedenyi_ne_vse_trebuemyie_issledovaniya_i_osmotryi_karta_ne_budet_oplachena_dlya_polucheniya_oplatyi_za_osmotryi_zavedite_ih_v_emk_kak_posescheniya']);
				} else if (kolvoAgree < Math.round(kolvo*0.85)) {
					sw.swMsg.alert(lang['preduprejdenie'], lang['zavedeno_menshe_85%_ot_trebuemyih_issledovaniy_i_osmotrov_sluchay_ne_budet_oplachen_dlya_polucheniya_oplatyi_za_posescheniya_spetsialistov_zanesite_ih_v_emk']);
				}
			}
		} else {
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
				if (kolvoAgree < kolvo) {
					sw.swMsg.alert(lang['oshibka'], lang['sluchay_ne_mojet_byit_zakonchen_tak_kak_zapolnenyi_ne_vse_issledovaniya_ili_osmotryi']);
					return false;
				}
			}
		}

		if ( getRegionNick() == 'buryatiya' && base_form.findField('EvnPLDispTeenInspection_IsFinish').getValue() == 2 && Ext.isEmpty(EvnUslugaDispDop_pedDate) ) {
			sw.swMsg.alert(lang['oshibka'], lang['data_vyipolneniya_osmotra_vracha_pediatra_obyazatelna_dlya_zapolneniya']);
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
				title: lang['oshibka']
			});
			return false;
		}
		
		// http://redmine.swan.perm.ru/issues/21226
		// Дата исследования "Флюорография" не может быть меньше 12 месяца, чем дата осмотра врача-педиатра. При невыполнении выводить сообщение "Дата исследования 
		// "Флюорография" не может быть раньше, чем 12 месяцев до даты осмотра врача-педиатра. ОК". Сохранение отменить.
		if ( !Ext.isEmpty(EvnUslugaDispDop_fluDate) && !Ext.isEmpty(EvnUslugaDispDop_pedDate) && EvnUslugaDispDop_fluDate < EvnUslugaDispDop_pedDate.add(Date.MONTH, -12) ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.ERROR,
				msg: lang['data_issledovaniya_flyuorografiya_ne_mojet_byit_ranshe_chem_12_mesyatsev_do_datyi_osmotra_vracha-pediatra'],
				title: lang['oshibka']
			});
			return false;
		}

		// @task https://redmine.swan.perm.ru/issues/111587
		if ( getRegionNick() == 'ekb' ) {
			if ( age < 2 && !Ext.isEmpty(EvnUslugaDispDop_pedDate) && EvnUslugaDispDop_pedDate.add(Date.DAY, -30) > EvnPLDispDop_setDate ) {
				sw.swMsg.show({
					buttons: Ext.Msg.OK,
					icon: Ext.Msg.ERROR,
					msg: "Длительность 1 этапа предварительного осмотра не может быть больше 30 календарных дней.",
					title: lang['oshibka']
				});
				return false;
			}
			else if ( age >= 2 && !Ext.isEmpty(EvnUslugaDispDop_pedDate) && EvnUslugaDispDop_pedDate.add(Date.DAY, -90) > EvnPLDispDop_setDate ) {
				sw.swMsg.show({
					buttons: Ext.Msg.OK,
					icon: Ext.Msg.ERROR,
					msg: "Длительность 1 этапа предварительного осмотра не может быть больше 90 календарных дней.",
					title: lang['oshibka']
				});
				return false;
			}
		}
		else {
			// Дата осмотра врача-педиатра не может быть больше 14 дней, чем дата начала диспансеризации (отдельное поле есть в карте). При невыполнении контроля выводить
			// сообщение "Длительность 1 этапа диспансеризации несовершеннолетнего не может больше 10 рабочих дней. ОК". Сохранение отменить
			if ( !Ext.isEmpty(EvnUslugaDispDop_pedDate) && EvnUslugaDispDop_pedDate.add(Date.DAY, -14) > EvnPLDispDop_setDate ) {
				sw.swMsg.show({
					buttons: Ext.Msg.OK,
					icon: Ext.Msg.ERROR,
					msg: "Длительность 1 этапа предварительного осмотра не может больше 10 рабочих дней.",
					title: lang['oshibka']
				});
				return false;
			}
		}
		
		// Дата осмотра врача-педиатра должна быть больше (равна) датам всех остальных осмотров / исследований. При невыполнение данного контроля выводить сообщение:
		// "Дата осмотра / исследования по диспансеризации несовершеннолетнего не может быть больше даты осмотра врача-педиатра. ОК ". Сохранение карты отменить.
		if ( !Ext.isEmpty(EvnUslugaDispDop_maxDate) && !Ext.isEmpty(EvnUslugaDispDop_pedDate) && EvnUslugaDispDop_maxDate > EvnUslugaDispDop_pedDate ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.ERROR,
				msg: "Дата любого осмотра/исследования не может быть больше даты осмотра врача-педиатра (ВОП).",
				title: lang['oshibka']
			});
			return false;
		}

		var params = new Object();
		
		win.getLoadMask("Подождите, идет сохранение...").show();

		EvnPLDispTeenInspection_form.getForm().submit({
			failure: function(result_form, action) {
				win.getLoadMask().hide()
			},
			params: params,
			success: function(result_form, action) {
				win.getLoadMask().hide()
				
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
					Ext.Msg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_2]']);
				}
			}
		});
	},
	height: 570,
	id: 'EvnPLDispTeenInspectionPredEditWindow',
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
				type: 'DispTeenInspectionPred',
				UslugaComplex_Date: win.findById('EPLDTIPRE_EvnPLDispTeenInspection_setDate').getValue(),
				onHide: Ext.emptyFn,
				callback: function(data) {
					// обновить грид!
					grid.getStore().reload();
				}
				
			});
		}		
	},
	initComponent: function() {
		var win = this;
		
		this.dopDispInfoConsentGrid = new sw.Promed.ViewFrame({
			autoLoadData: false,
			id: 'EPLDTIPRE_dopDispInfoConsentGrid',
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
				{ name: 'SurveyType_Code', type: 'int', hidden: true },
				{ name: 'SurveyTypeLink_IsDel', type: 'int', hidden: true },
				{ name: 'SurveyType_Name', type: 'string', sortable: false, header: lang['osmotr_issledovanie'], id: 'autoexpand' },
				{ name: 'DopDispInfoConsent_IsEarlier', sortable: false, type: 'checkcolumnedit', isparams: true, header: lang['proydeno_ranee'], width: 180 },
				{ name: 'DopDispInfoConsent_IsAgree', sortable: false, type: 'checkcolumnedit', isparams: true, header: lang['soglasie_grajdanina'], width: 180 }
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
			id: 'EPLDTIPRE_evnUslugaDispDopGrid',
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
						fieldLabel: lang['usluga_dispanserizatsii'],
						disabled: true,
						emptyText: '',
						nonDispOnly: false,
						xtype: 'swuslugacomplexnewcombo'
					}]
				}, {
					fieldLabel: lang['povtornaya_podacha'],
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
					editable: false,
					allowBlank: getRegionNick().inlist(['ekb']),
					enableKeyEvents: true,
					fieldLabel: lang['obrazovatelnoe_uchrejdenie'],
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
					disabled: false,
					allowBlank: false,
					comboSubject: 'EducationInstitutionType',
					fieldLabel: lang['tip_obrazovatelnogo_uchrejdeniya'],
					hiddenName: 'EducationInstitutionType_id',
					listeners: {
						'change': function(combo, newValue, oldValue) {
							var base_form = win.EvnPLDispTeenInspectionFormPanel.getForm();
							
							if (!Ext.isEmpty(oldValue) && win.checkEvnPLDispTeenInspectionIsSaved()) {
								sw.swMsg.show({
									buttons: Ext.Msg.YESNO,
									fn: function ( buttonId ) {
										if ( buttonId == 'yes' )
										{
											base_form.findField('EducationInstitutionType_id').fireEvent('change', base_form.findField('EducationInstitutionType_id'), base_form.findField('EducationInstitutionType_id').getValue());
										} else {
											base_form.findField('EducationInstitutionType_id').setValue(oldValue);
										}
									},
									msg: lang['pri_izmenenii_znacheniya_v_pole_tip_obrazovatelnogo_uchrejdeniya_izmenitsya_nabor_osmotrov_issledovaniy_predvaritelnogo_osmotra_informatsiya_po_vvedennyim_osmotram_issledovaniyam_mojet_byit_poteryana_izmenit'],
									title: lang['podtverjdenie']
								});
								return false;
							}
							var EducationInstitutionClass_id = base_form.findField('EducationInstitutionClass_id').getValue();
							var hasOldValue = false;
							base_form.findField('EducationInstitutionClass_id').clearValue();
							base_form.findField('EducationInstitutionClass_id').getStore().clearFilter();
							
							base_form.findField('EducationInstitutionClass_id').getStore().filterBy(function(rec) {
								if (rec.get('EducationInstitutionType_id') == newValue) {
									if (rec.get('EducationInstitutionClass_id') == EducationInstitutionClass_id) {
										hasOldValue = true;
									}
									return true;
								} else {
									return false;
								}
							});
							
							if (hasOldValue) {
								base_form.findField('EducationInstitutionClass_id').setValue(EducationInstitutionClass_id);
							}
							
							if (!Ext.isEmpty(win.findById('EPLDTIPRE_EvnPLDispTeenInspection_setDate').getValue()) && !Ext.isEmpty(newValue)) {
								win.findById('EPLDTIPRE_EvnPLDispTeenInspection_setDate').fireEvent('change', win.findById('EPLDTIPRE_EvnPLDispTeenInspection_setDate'), win.findById('EPLDTIPRE_EvnPLDispTeenInspection_setDate').getValue());
							}
						}
					},
					lastQuery: '',
					width: 300,
					xtype: 'swcommonsprcombo'
				}, {
                    allowBlank: false,
                    fieldLabel: lang['data_nachala_meditsinskogo_osmotra'],
                    format: 'd.m.Y',
                    id: 'EPLDTIPRE_EvnPLDispTeenInspection_setDate',
					listeners: {
						'change': function(field, newValue, oldValue) {
							var xdate = new Date(2018,0,1);
							if (newValue && newValue >= xdate) {
								sw.swMsg.alert(langs('Ошибка'), langs('В соответствии с приказом № 514н «О порядке проведения профилактических осмотров несовершеннолетних» с 01.01.2018 предварительные осмотры несовершеннолетних не осуществляются.'), function () {
									field.setValue(null);
									field.focus(true);
								});
								return;
							}

							win.loadUslugaComplex();
							win.setAgeGroupDispCombo(newValue, oldValue, true);
							var base_form = win.EvnPLDispTeenInspectionFormPanel.getForm();

							if (getRegionNick().inlist([ 'perm', 'ufa' ]) && !Ext.isEmpty(oldValue) && !Ext.isEmpty(newValue) && win.checkEvnPLDispTeenInspectionIsSaved() && newValue.format('Y') != oldValue.format('Y')) {
								sw.swMsg.show({
									buttons: Ext.Msg.YESNO,
									fn: function ( buttonId ) {
										if ( buttonId == 'yes' ) {
											win.saveDopDispInfoConsentAfterLoad = true;
											win.blockSaveDopDispInfoConsent = true;
											win.dopDispInfoConsentGrid.loadData({
												params: {
													Person_id: base_form.findField('Person_id').getValue()
													,
													DispClass_id: base_form.findField('DispClass_id').getValue()
													,
													EvnPLDispTeenInspection_id: base_form.findField('EvnPLDispTeenInspection_id').getValue()
													,
													EvnPLDispTeenInspection_consDate: (typeof newValue == 'object' ? Ext.util.Format.date(newValue, 'd.m.Y') : newValue)
													,
													EducationInstitutionType_id: base_form.findField('EducationInstitutionType_id').getValue()
												},
												globalFilters: {
													Person_id: base_form.findField('Person_id').getValue()
													,
													DispClass_id: base_form.findField('DispClass_id').getValue()
													,
													EvnPLDispTeenInspection_id: base_form.findField('EvnPLDispTeenInspection_id').getValue()
													,
													EvnPLDispTeenInspection_consDate: (typeof newValue == 'object' ? Ext.util.Format.date(newValue, 'd.m.Y') : newValue)
													,
													EducationInstitutionType_id: base_form.findField('EducationInstitutionType_id').getValue()
												},
												noFocusOnLoad: true,
												callback: function () {
													win.blockSaveDopDispInfoConsent = false;
													if (win.saveDopDispInfoConsentAfterLoad) {
														win.saveDopDispInfoConsent();
													}
													win.saveDopDispInfoConsentAfterLoad = false;
												}
											});
										} else {
											win.findById('EPLDTIPRE_EvnPLDispTeenInspection_setDate').setValue(oldValue);
										}
									},
									msg: lang['pri_izmenenii_datyi_nachala_meditsinskogo_osmotra_vvedennaya_informatsiya_po_osmotram_issledovaniyam_budet_udalena_izmenit'],
									title: lang['podtverjdenie']
								});
								return false;
							}

							win.blockSaveDopDispInfoConsent = true;
							win.dopDispInfoConsentGrid.loadData({
								params: {
									 Person_id: base_form.findField('Person_id').getValue()
									,DispClass_id: base_form.findField('DispClass_id').getValue()
									,EvnPLDispTeenInspection_id: base_form.findField('EvnPLDispTeenInspection_id').getValue()
									,EvnPLDispTeenInspection_consDate: (typeof newValue == 'object' ? Ext.util.Format.date(newValue, 'd.m.Y') : newValue)
									,EducationInstitutionType_id: base_form.findField('EducationInstitutionType_id').getValue()
								},
								globalFilters: {
									 Person_id: base_form.findField('Person_id').getValue()
									,DispClass_id: base_form.findField('DispClass_id').getValue()
									,EvnPLDispTeenInspection_id: base_form.findField('EvnPLDispTeenInspection_id').getValue()
									,EvnPLDispTeenInspection_consDate: (typeof newValue == 'object' ? Ext.util.Format.date(newValue, 'd.m.Y') : newValue)
									,EducationInstitutionType_id: base_form.findField('EducationInstitutionType_id').getValue()
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
					fieldLabel: lang['data_podpisaniya_soglasiya_otkaza'],
					format: 'd.m.Y',
					id: 'EPLDTIPRE_EvnPLDispTeenInspection_consDate',
					plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
					width: 100,
					xtype: 'swdatefield'
				}, {
					fieldLabel: lang['sluchay_obslujen_mobilnoy_brigadoy'],
					name: 'EvnPLDispTeenInspection_IsMobile',
					xtype: 'checkbox',
					listeners: {
						'check': function(checkbox, value) {
							var base_form = win.EvnPLDispTeenInspectionFormPanel.getForm();
							
							if ( value == true && win.action != 'view' ) {
								base_form.findField('Lpu_mid').setAllowBlank(false);
								base_form.findField('Lpu_mid').enable();
							} else {
								base_form.findField('Lpu_mid').setAllowBlank(true);
								base_form.findField('Lpu_mid').clearValue();
								base_form.findField('Lpu_mid').disable();
							}
						}
					}
				}, {
					fieldLabel: lang['mo_mobilnoy_brigadyi'],
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
							DispClass_id: 9,
							Disp_consDate: (typeof win.findById('EPLDTIPRE_EvnPLDispTeenInspection_consDate').getValue() == 'object' ? Ext.util.Format.date(win.findById('EPLDTIPRE_EvnPLDispTeenInspection_consDate').getValue(), 'd.m.Y') : win.findById('EPLDTIPRE_EvnPLDispTeenInspection_consDate').getValue()),
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
							if (getRegionNick() != 'ekb') {
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
								id: 'EPLDTIPRE_DopDispInfoConsentSaveBtn',
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
									var record = win.dopDispInfoConsentGrid.getGrid().getStore().getAt(0);
									var pattern = '';//Шаблон для печати от имени пациента
									var pattern_dep = '';//Шаблон для печати от имени представителя
									var title = ''; //Название формы вопроса о выборе варианта печати
									if(record.get('DopDispInfoConsent_IsAgree')){ //согласие
										pattern = 'EvnPLDispTeenInspectionConsent.rptdesign';
										pattern_dep = 'EvnPLDispTeenInspectionConsent_Deputy.rptdesign';
										title = lang['soglasiya'];
									}
									else{ //отказ
										pattern = 'EvnPLDispTeenInspectionOtkaz.rptdesign';
										pattern_dep = 'EvnPLDispTeenInspectionOtkaz_Deputy.rptdesign';
										title = lang['otkaza'];
									}
									var paramEvnPLDispTeenInspection = base_form.findField('EvnPLDispTeenInspection_id').getValue();
									if(paramEvnPLDispTeenInspection) {
										var dialog_wnd = Ext.Msg.show({
											title: lang['vid'] + title,
											msg:lang['vyiberite_vid'] + title,
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
			title: lang['informirovannoe_dobrovolnoe_soglasie_1_etap']
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
			title: lang['marshrutnaya_karta']
		});

		this.DispAppointGrid = new sw.Promed.ViewFrame({
			autoLoadData: false,
			editformclassname: 'swDispAppointEditForm',
			object: 'DispAppoint',
			actions: [
				{ name: 'action_add' },
				{ name: 'action_edit' },
				{ name: 'action_view' },
				{ name: 'action_delete' },
				{ name: 'action_refresh' },
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
			title: lang['osnovnyie_rezultatyi_predvaritelnogo_osmotra'],
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
					value: 9,
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
					title: lang['obrazovatelnoe_uchrejdenie'],
					width: 600,
					items: [
						{
							allowBlank: true,
							fieldLabel: lang['data_postupleniya'],
							format: 'd.m.Y',
							name: 'EvnPLDispTeenInspection_eduDT',
							plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
							width: 100,
							xtype: 'swdatefield'
						},
						{
							allowBlank: false,
							comboSubject: 'EducationInstitutionClass',
							fieldLabel: lang['obrazovatelnoe_uchrejdenie'],
							hiddenName: 'EducationInstitutionClass_id',
							moreFields: [{ name: 'EducationInstitutionType_id', mapping: 'EducationInstitutionType_id' }],
							lastQuery: '',
							width: 300,
							xtype: 'swcommonsprcombo'
						},
						{
							comboSubject: 'InstitutionNatureType',
							fieldLabel: lang['harakter_uchrejdeniya'],
							hiddenName: 'InstitutionNatureType_id',
							lastQuery: '',
							width: 300,
							xtype: 'swcommonsprcombo'
						},
						{
							comboSubject: 'InstitutionType',
							fieldLabel: lang['vid_uchrejdeniya'],
							hiddenName: 'InstitutionType_id',
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
					title: lang['otsenka_fizicheskogo_razvitiya'],
					width: 600,
					items: [
						{
							fieldLabel: lang['massa_kg'],
							name: 'AssessmentHealth_Weight',
							decimalPrecision: 1,
							minValue: 2,
							maxValue: 500,
							xtype: 'numberfield'
						},
						{
							fieldLabel: lang['rost_sm'],
							name: 'AssessmentHealth_Height',
							minValue: 20,
							maxValue: 275,
							xtype: 'numberfield'
						},
						{
							fieldLabel: lang['otklonenie_massa'],
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
							fieldLabel: lang['tip_otkloneniya_massa'],
							hiddenName: 'WeightAbnormType_id',
							lastQuery: '',
							width: 300,
							xtype: 'swcommonsprcombo'
						},
						{
							fieldLabel: lang['otklonenie_rost'],
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
							fieldLabel: lang['tip_otkloneniya_rost'],
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
					title: lang['otsenka_psihicheskogo_razvitiya_sostoyaniya'],
					width: 600,
					items: [
						{
							allowDecimals: false,
							allowNegative: false,
							fieldLabel: lang['poznavatelnaya_funktsiya_vozrast_razvitiya_mes'],
							minValue: 0,
							name: 'AssessmentHealth_Gnostic',
							xtype: 'numberfield'
						},
						{
							allowDecimals: false,
							allowNegative: false,
							fieldLabel: lang['motornaya_funktsiya_vozrast_razvitiya_mes'],
							minValue: 0,
							name: 'AssessmentHealth_Motion',
							xtype: 'numberfield'
						},
						{
							allowDecimals: false,
							allowNegative: false,
							fieldLabel: lang['emotsionalnaya_i_sotsialnaya_kontakt_s_okrujayuschim_mirom_funktsii_vozrast_razvitiya_mes'],
							minValue: 0,
							name: 'AssessmentHealth_Social',
							xtype: 'numberfield'
						},
						{
							allowDecimals: false,
							allowNegative: false,
							fieldLabel: lang['predrechevoe_i_rechevoe_razvitie_vozrast_razvitiya_mes'],
							minValue: 0,
							name: 'AssessmentHealth_Speech',
							xtype: 'numberfield'
						},
						{
							fieldLabel: lang['psihomotornaya_sfera'],
							hiddenName: 'NormaDisturbanceType_id',
							xtype: 'swnormadisturbancetypecombo'
						},
						{
							fieldLabel: lang['intellekt'],
							hiddenName: 'NormaDisturbanceType_uid',
							xtype: 'swnormadisturbancetypecombo'
						},
						{
							fieldLabel: lang['emotsionalno-vegetativnaya_sfera'],
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
					title: lang['otsenka_polovogo_razvitiya'],
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
							id: 'EPLDTIPRE_menarhe',
							title: lang['harakteristika_menstrualnoy_funktsii_menarhe'],
							width: 580,
							items: [
								{
									fieldLabel: lang['let'],
									minValue: 6,
									maxValue: 17,
									name: 'AssessmentHealth_Years',
									xtype: 'numberfield'
								},
								{
									fieldLabel: lang['mesyatsev'],
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
							id: 'EPLDTIPRE_menses',
							title: lang['menses_harakteristika'],
							width: 580,
							items: [
								{
									boxLabel: lang['regulyarnyie'],
									hideLabel: true,
									name: 'AssessmentHealth_IsRegular',
									xtype: 'checkbox'
								},
								{
									boxLabel: lang['neregulyarnyie'],
									hideLabel: true,
									name: 'AssessmentHealth_IsIrregular',
									xtype: 'checkbox'
								},
								{
									boxLabel: lang['obilnyie'],
									hideLabel: true,
									name: 'AssessmentHealth_IsAbundant',
									xtype: 'checkbox'
								},
								{
									boxLabel: lang['umerennyie'],
									hideLabel: true,
									name: 'AssessmentHealth_IsModerate',
									xtype: 'checkbox'
								},
								{
									boxLabel: lang['skudnyie'],
									hideLabel: true,
									name: 'AssessmentHealth_IsScanty',
									xtype: 'checkbox'
								},
								{
									boxLabel: lang['boleznennyie'],
									hideLabel: true,
									name: 'AssessmentHealth_IsPainful',
									xtype: 'checkbox'
								},
								{
									boxLabel: lang['bezboleznennyie'],
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
					fieldLabel: lang['gruppa_zdorovya'],
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
					fieldLabel: lang['meditsinskaya_gruppa_dlya_zanyatiy_fiz_kulturoy'],
					hiddenName: 'HealthGroupType_id',
					lastQuery: '',
					width: 300,
					xtype: 'swcommonsprcombo'
				},
				{
					fieldLabel: lang['napravlen_na_2_etap'],
					hiddenName: 'EvnPLDispTeenInspection_IsTwoStage',
					allowBlank: false,
					width: 100,
					xtype: 'swyesnocombo'
				},
				{
					fieldLabel: lang['sluchay_zakonchen'],
					hiddenName: 'EvnPLDispTeenInspection_IsFinish',
					allowBlank: false,
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
			title: lang['spravka_o_stoimosti_lecheniya'],
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
					fieldLabel: lang['data_vyidachi_spravki_otkaza'],
					width: 100,
					plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
					name: 'EvnCostPrint_setDT',
					xtype: 'swdatefield'
				},{
					fieldLabel: lang['nomer_spravki_otkaza'],
					name:'EvnCostPrint_Number',
					readOnly: true,
					xtype: 'textfield'
				},{
					fieldLabel: lang['otkaz'],
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
				// основные результаты диспансеризации
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
				{ name: 'Org_id' },
				{ name: 'Lpu_mid' },
				{ name: 'EvnPLDispTeenInspection_IsMobile' },
				{ name: 'EvnPLDispTeenInspection_IsOutLpu' },
				{ name: 'EducationInstitutionType_id' },
				{ name: 'EducationInstitutionClass_id' },
				{ name: 'InstitutionNatureType_id' },
				{ name: 'InstitutionType_id' },
				{ name: 'AssessmentHealth_Weight' },
				{ name: 'AssessmentHealth_Height' },
				{ name: 'WeightAbnormType_YesNo' },
				{ name: 'WeightAbnormType_id' },
				{ name: 'HeightAbnormType_YesNo' },
				{ name: 'HeightAbnormType_id' },
				{ name: 'AssessmentHealth_Gnostic'},
				{ name: 'AssessmentHealth_Motion'},
				{ name: 'AssessmentHealth_Social'},
				{ name: 'AssessmentHealth_Speech'},
				{ name: 'NormaDisturbanceType_id'},
				{ name: 'NormaDisturbanceType_uid'},
				{ name: 'NormaDisturbanceType_eid'},
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
				{ name: 'HealthKind_id' },
				{ name: 'HealthGroupType_oid' },
				{ name: 'HealthGroupType_id' },
				{ name: 'EvnPLDispTeenInspection_IsTwoStage' },
				{ name: 'EvnPLDispTeenInspection_IsFinish' },
				{ name: 'EvnCostPrint_setDT' },
				{ name: 'EvnCostPrint_Number' },
				{ name: 'EvnCostPrint_IsNoPrint' }
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
				id: 'EPLDTIPRE_SaveButton',
				onTabAction: function() {
					Ext.getCmp('EPLDTIPRE_CancelButton').focus(true, 200);
				},
				onShiftTabAction: function() {
					var base_form = win.EvnPLDispTeenInspectionFormPanel.getForm();
					base_form.findField('EvnPLDispTeenInspection_IsFinish').focus(true, 200);
				},
				tabIndex: 2406,
				text: BTN_FRMSAVE
			}, {
				handler: function() {
					this.printEvnPLDispTeenInspMedZak();
				}.createDelegate(this),
				iconCls: 'print16',
				id: 'EPLDTIPRE_PrintZak',
				tabIndex: 2408,
				text: lang['pechat_zaklyucheniya_o_gruppe_dlya_zanyatiy_fiz_kulturoy'],
				tooltip:lang['pechat_meditsinskogo_zaklyucheniya_o_prinadlejnosti_nesovershennoletnego_k_meditsisnkoy_gruppe_dlya_zanyatiy_fizicheskoy_kulturoy']
			}, {
                hidden: true,
                handler: function() {
                    this.printEvnPLDispTeenInspProf();
                }.createDelegate(this),
                iconCls: 'print16',
                id: 'EPLDTIPRE_PrintButton',
                tabIndex: 2407,
                text: lang['pechat_kartyi_med_osmotra']
            },'-',
			HelpButton(this, -1),
			{
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'cancel16',
				id: 'EPLDTIPRE_CancelButton',
				tabIndex: 2409,
				text: BTN_FRMCANCEL
			}]
		});
		sw.Promed.swEvnPLDispTeenInspectionPredEditWindow.superclass.initComponent.apply(this, arguments);
	},
	keys: [{
		alt: true,
		fn: function(inp, e) {
			var win = Ext.getCmp('EvnPLDispTeenInspectionPredEditWindow');
			var tabbar = win.findById('EPLDTIPRE_EvnPLTabbar');

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
	printEvnPLDispTeenInspMedZak: function() {
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
	resizable: true,
	setAgeGroupDispCombo: function(newSetDate, oldSetDate, recalc) {
		var win = this;
		var base_form = this.EvnPLDispTeenInspectionFormPanel.getForm();
		var age_start = -1;
		var month_start = -1;
		var age_end = -1;
		if ( !Ext.isEmpty(newSetDate) ) {
			age_start = swGetPersonAge(win.PersonInfoPanel.getFieldValue('Person_Birthday'), newSetDate);
			var year = newSetDate.getFullYear();
			var endYearDate = new Date(year, 11, 31);
			age_end = swGetPersonAge(win.PersonInfoPanel.getFieldValue('Person_Birthday'), endYearDate);
			month_start = swGetPersonAgeMonth(win.PersonInfoPanel.getFieldValue('Person_Birthday'), newSetDate);
			win.onChangeAge(age_start, newSetDate);
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
	},
	checkEvnPLDispTeenInspectionIsSaved: function() {
		var base_form = this.EvnPLDispTeenInspectionFormPanel.getForm();
		if (Ext.isEmpty(base_form.findField('EvnPLDispTeenInspection_id').getValue()) || !this.PersonFirstStageAgree) {
			// дисаблим все разделы кроме информированного добровольного согласия, а также основную кнопки сохранить и печать
			this.EvnUslugaDispDopPanel.collapse();
			this.EvnUslugaDispDopPanel.disable();
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
			this.EvnPLDispTeenInspectionMainResultsPanel.expand();
			this.EvnPLDispTeenInspectionMainResultsPanel.enable();
			if (
				!Ext.isEmpty(base_form.findField('HealthKind_id').getValue())
				&& base_form.findField('HealthKind_id').getValue() != 1
				&& base_form.findField('HealthKind_id').getValue() != 2
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
		var btn = win.findById('EPLDTIPRE_DopDispInfoConsentSaveBtn');
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

		if (Ext.isEmpty(base_form.findField('EducationInstitutionType_id').getValue())) {
			btn.enable();
			sw.swMsg.alert(lang['oshibka'], lang['nelzya_sohranit_soglasie_bez_vyibrannogo_tipa_obrazovatelnogo_uchrejdeniya']);
			return false;
		}

		win.getLoadMask(lang['sohranenie_informirovannogo_dobrovolnogo_soglasiya']).show();
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

		if ( Ext.isEmpty(win.findById('EPLDTIPRE_EvnPLDispTeenInspection_consDate').getValue()) ) {
			btn.enable();
			win.getLoadMask().hide();

			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					win.findById('EPLDTIPRE_EvnPLDispTeenInspection_consDate').focus(true);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});

			return false;
		}

		var xdate = new Date(2015,0,1);
		if ( getRegionNick().inlist([ 'kareliya' ]) && win.findById('EPLDTIPRE_EvnPLDispTeenInspection_consDate').getValue() >= xdate ) {
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
					buttons: {yes: lang['sohranit'], cancel: lang['otmena']},
					fn: function ( buttonId ) {
						if ( buttonId == 'yes' ) {
							win.saveDopDispInfoConsent({
								ignoreRefuse: true
							});
						}
					},
					msg: lang['karta_podlejit_oplate_tolko_pri_provedenii_vseh_osmotrov_issledovaniy_prodoljit_sohranenie'],
					title: lang['podtverjdenie']
				});
				return false;
			}
		}

		// @task https://redmine.swan.perm.ru//issues/105580
		if ( getRegionNick() == 'kareliya' && swGetPersonAge(win.PersonInfoPanel.getFieldValue('Person_Birthday'), win.findById('EPLDTIPRE_EvnPLDispTeenInspection_consDate').getValue()) > 18 ) {
			btn.enable();
			sw.swMsg.alert(lang['oshibka'], 'Сохранение информированного согласия пациента доступно до достижения возраста 18 лет', function() { win.getLoadMask().hide(); });
			return false;
		}

		params.EvnPLDispTeenInspection_consDate = (typeof win.findById('EPLDTIPRE_EvnPLDispTeenInspection_consDate').getValue() == 'object' ? Ext.util.Format.date(win.findById('EPLDTIPRE_EvnPLDispTeenInspection_consDate').getValue(), 'd.m.Y') : win.findById('EPLDTIPRE_EvnPLDispTeenInspection_consDate').getValue());
		params.EvnPLDispTeenInspection_setDate = (typeof win.findById('EPLDTIPRE_EvnPLDispTeenInspection_setDate').getValue() == 'object' ? Ext.util.Format.date(win.findById('EPLDTIPRE_EvnPLDispTeenInspection_setDate').getValue(), 'd.m.Y') : win.findById('EPLDTIPRE_EvnPLDispTeenInspection_setDate').getValue());
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
		params.EducationInstitutionType_id = base_form.findField('EducationInstitutionType_id').getValue();
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
						// запускаем callback чтобы обновить грид в родительском окне
						win.callback({evnPLDispTeenInspectionData: win.getDataForCallBack()});
						// обновляем грид
						grid.getStore().load({
							params: {
								EvnPLDispTeenInspection_id: answer.EvnPLDispTeenInspection_id
							}
						});

						win.loadForm(answer.EvnPLDispTeenInspection_id);
					}
				}
			}
		});
	},
	checkForCostPrintPanel: function() {
		var base_form = this.EvnPLDispTeenInspectionFormPanel.getForm();

		this.CostPrintPanel.hide();
		base_form.findField('EvnCostPrint_setDT').setAllowBlank(true);
		base_form.findField('EvnCostPrint_Number').setContainerVisible(getRegionNick() == 'khak');
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
		sw.Promed.swEvnPLDispTeenInspectionPredEditWindow.superclass.show.apply(this, arguments);
		
		if (!arguments[0])
		{
			Ext.Msg.alert(lang['soobschenie'], lang['nevernyie_parametryi']);
			return false;
		}
		
		var win = this;
		win.getLoadMask(LOAD_WAIT).show();

		this.restore();
		this.center();
		this.maximize();

		win.blockSaveDopDispInfoConsent = false;
		win.saveDopDispInfoConsentAfterLoad = false;
		win.ignoreEmptyFields = false;
		this.orpAdoptedMOEmptyCode = true;
		this.orpAdoptedMODateincorrect = true;

		var form = this.EvnPLDispTeenInspectionFormPanel;
		form.getForm().reset();
		this.checkForCostPrintPanel();

		win.findById('EPLDTIPRE_EvnPLDispTeenInspection_consDate').setRawValue('');
		win.findById('EPLDTIPRE_EvnPLDispTeenInspection_setDate').setRawValue('');

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

		if (Ext.isEmpty(base_form.findField('PayType_id').getValue())) {
			base_form.findField('PayType_id').setFieldValue('PayType_SysNick', 'oms');
		}
		
		base_form.findField('HeightAbnormType_YesNo').setValue(1);
		base_form.findField('WeightAbnormType_YesNo').setValue(1);

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
				
		base_form.findField('WeightAbnormType_YesNo').fireEvent('change', base_form.findField('WeightAbnormType_YesNo'), base_form.findField('WeightAbnormType_YesNo').getValue());
		base_form.findField('HeightAbnormType_YesNo').fireEvent('change', base_form.findField('HeightAbnormType_YesNo'), base_form.findField('HeightAbnormType_YesNo').getValue());
		base_form.findField('EducationInstitutionType_id').fireEvent('change', base_form.findField('EducationInstitutionType_id'), base_form.findField('EducationInstitutionType_id').getValue());
		
		win.DopDispInfoConsentPanel.setTitle(lang['informirovannoe_dobrovolnoe_soglasie']);
		
		if (win.action == 'edit') {
			win.setTitle(lang['predvaritelnyiy_osmotr_nesovershennoletnego_-_1_etap_redaktirovanie']);
		} else {
			win.setTitle(lang['predvaritelnyiy_osmotr_nesovershennoletnego_-_1_etap_prosmotr']);
		}
		
		// пока не сохранена карта (сохраняется при информационно добровольном согласии) нельзя редактировать разделы кроме согласия
		this.checkEvnPLDispTeenInspectionIsSaved();
		
		inf_frame_is_loaded = false;

		this.PersonInfoPanel.load({ 
			Person_id: person_id, 
			Server_id: server_id, 
			callback: function() {
				win.getLoadMask().hide();
				inf_frame_is_loaded = true; 

				var sex_id = win.PersonInfoPanel.getFieldValue('Sex_id');
				var age = win.PersonInfoPanel.getFieldValue('Person_Age');

				if (getRegionNick().inlist([ 'perm', 'ufa' ]) && Ext.isEmpty(EvnPLDispTeenInspection_id) && age < 3 && win.PersonInfoPanel.getFieldValue('Lpu_id') != getGlobalOptions().lpu_id) {
					sw.swMsg.alert(lang['vnimanie'], lang['deti_mladshe_3-h_let_doljnyi_prohodit_profilakticheskiy_osmotr_po_mestu_osnovnogo_prikrepleniya']);
				}

				base_form.findField('Server_id').setValue(win.PersonInfoPanel.getFieldValue('Server_id'));
				base_form.findField('PersonEvn_id').setValue(win.PersonInfoPanel.getFieldValue('PersonEvn_id'));
				
				if ( sex_id == 1 ) {
					// скрыть поля для девочек
					base_form.findField('AssessmentHealth_Ma').hideContainer();
					base_form.findField('AssessmentHealth_Me').hideContainer();
					win.findById('EPLDTIPRE_menarhe').hide();
					win.findById('EPLDTIPRE_menses').hide();
				}
				else {
					base_form.findField('AssessmentHealth_Ma').showContainer();
					base_form.findField('AssessmentHealth_Me').showContainer();
					win.findById('EPLDTIPRE_menarhe').show();
					win.findById('EPLDTIPRE_menses').show();
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
				
				base_form.findField('EvnPLDispTeenInspection_IsMobile').fireEvent('check', base_form.findField('EvnPLDispTeenInspection_IsMobile'), base_form.findField('EvnPLDispTeenInspection_IsMobile').getValue());

				if (!Ext.isEmpty(EvnPLDispTeenInspection_id)) {
					win.loadForm(EvnPLDispTeenInspection_id);
				}
				else {
					// Грузим текущую дату
					setCurrentDateTime({
						dateField: win.findById('EPLDTIPRE_EvnPLDispTeenInspection_consDate'),
						loadMask: true,
						setDate: true,
						setDateMaxValue: true,
						windowId: win.id
					});
                    setCurrentDateTime({
						callback: function(date) {
							win.findById('EPLDTIPRE_EvnPLDispTeenInspection_setDate').fireEvent('change', win.findById('EPLDTIPRE_EvnPLDispTeenInspection_setDate'), date);
							win.setAgeGroupDispCombo(date, null, true);
						},
                        dateField: win.findById('EPLDTIPRE_EvnPLDispTeenInspection_setDate'),
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
	
	loadForm: function(EvnPLDispTeenInspection_id) {
	
		var win = this;
		var base_form = this.EvnPLDispTeenInspectionFormPanel.getForm();
		win.getLoadMask(LOAD_WAIT).show();

		base_form.load({
			failure: function() {
				win.getLoadMask().hide();
				swEvnPLDispTeenInspectionPredEditWindow.hide();
			},
			params: {
				EvnPLDispTeenInspection_id: EvnPLDispTeenInspection_id,
				archiveRecord: win.archiveRecord
			},
			success: function() {
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
				base_form.findField('EducationInstitutionType_id').fireEvent('change', base_form.findField('EducationInstitutionType_id'), base_form.findField('EducationInstitutionType_id').getValue());
				base_form.findField('EvnPLDispTeenInspection_IsMobile').fireEvent('check', base_form.findField('EvnPLDispTeenInspection_IsMobile'), base_form.findField('EvnPLDispTeenInspection_IsMobile').getValue());

				win.findById('EPLDTIPRE_EvnPLDispTeenInspection_consDate').setValue(base_form.findField('EvnPLDispTeenInspection_consDate').getValue());
                win.findById('EPLDTIPRE_EvnPLDispTeenInspection_setDate').setValue(base_form.findField('EvnPLDispTeenInspection_setDate').getValue());
				win.setAgeGroupDispCombo(win.findById('EPLDTIPRE_EvnPLDispTeenInspection_setDate').getValue(), null, false);
				win.findById('EPLDTIPRE_EvnPLDispTeenInspection_setDate').fireEvent('change', win.findById('EPLDTIPRE_EvnPLDispTeenInspection_setDate'), win.findById('EPLDTIPRE_EvnPLDispTeenInspection_setDate').getValue());

				if(Ext.isEmpty(base_form.findField('HealthGroupType_oid').getValue())) //https://redmine.swan.perm.ru/issues/108777
				{
					Ext.Ajax.request({
						params: {
							EvnPLDispTeenInspection_id: EvnPLDispTeenInspection_id,
							Person_id: base_form.findField('Person_id').getValue(),
							Lpu_id: getGlobalOptions().lpu_id,
							DispClass_id: 9
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
			},
			url: '/?c=EvnPLDispTeenInspection&m=loadEvnPLDispTeenInspectionEditForm'
		});
		
	},
	title: '',
	width: 800
}
);
