/**
* swEvnPLDispProfEditWindow - окно редактирования/добавления талона по профосмотру
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
* @version		20.06.2013
* @comment		Префикс для id компонентов EPLDPEF (EvnPLDispProfEditForm)
*
*/
/*NO PARSE JSON*/
sw.Promed.swEvnPLDispProfEditWindow = Ext.extend(sw.Promed.BaseForm, {
	action: 'add',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	collapsible: true,
	/* */
	codeRefresh: true,
	objectName: 'swEvnPLDispProfEditWindow',
	objectSrc: '/jscore/Forms/Polka/swEvnPLDispProfEditWindow.js',
	draggable: true,
	verfGroup:function(){
		var wins = this;
		var base_form = wins.EvnPLDispProfFormPanel.getForm();
		if ( base_form.findField('EvnPLDispProf_IsEndStage').getValue() == 2 ) {
		//Проверка на Группу здоровья
			base_form.findField('HealthKind_id').setAllowBlank(false);
			base_form.findField('HealthKind_id').validate();
		}else{
			base_form.findField('HealthKind_id').setAllowBlank(true);
			base_form.findField('HealthKind_id').validate();
		}
	},
	doSave: function( options ) {
		if ( typeof options != 'object' ) {
			options = new Object();
		}

		var win = this;
		var EvnPLDispProf_form = win.EvnPLDispProfFormPanel;

		var base_form = win.EvnPLDispProfFormPanel.getForm();

		if (
			getRegionNick().inlist(['pskov', 'astra','krym'])
			&& base_form.findField('HealthKind_id').getValue() > 2
			&& (
				this.DispAppointGrid.getGrid().getStore().getCount() == 0
				|| Ext.isEmpty(this.DispAppointGrid.getGrid().getStore().getAt(0).get('DispAppointType_id'))
			)
		) {
			sw.swMsg.alert(langs('Ошибка'), 'Раздел «Назначения» должен содержать хотя бы одну запись, так как указана группа здоровья IIIа или IIIб.');
			return false;
		}

		if (
			getRegionNick() == 'adygeya'
			&& base_form.findField('EvnPLDispProf_IsSuspectZNO').getValue() == 2
			&& base_form.findField('HealthKind_id').getValue() == 1
		) {
			sw.swMsg.alert('Ошибка', 'Нельзя выбрать I группу здоровья при подозрении на ЗНО.');
			return false;
		}

		if ( Ext.isEmpty(win.findById('EPLDP_EvnPLDispProf_consDate').getValue()) ) {
			win.getLoadMask().hide();

			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					win.findById('EPLDP_EvnPLDispProf_consDate').focus(true);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});

			return false;
		}

		var newDVNDate = getNewDVNDate();
		if (
			getRegionNick().inlist(['pskov', 'ufa'])
			|| (getRegionNick() != 'ekb' && !Ext.isEmpty(newDVNDate) && win.findById('EPLDP_EvnPLDispProf_consDate').getValue() >= newDVNDate)
		) {
			// для Уфы: Если проставляем "Случай диспансеризации 1 этап закончен: ДА" и если количество заведенных (сохраненных) осмотров / исследований меньше, чем указано для данного пола/возраста меньше 85%, то выводить сообщение "Количество сохраненных осмотров\исследований составляет менее 85% от объема, установленного для данного возраста и пола. Ок", Сохранение отменить.
			if ( base_form.findField('EvnPLDispProf_IsEndStage').getValue() == 2 ) {
				// считаем количество сохраненных осмотров/исследований
				var kolvo = 0;
				var kolvoAgree = 0;
				win.dopDispInfoConsentGrid.getGrid().getStore().each(function(rec) {
					if (!Ext.isEmpty(rec.get('SurveyType_Code')) && rec.get('SurveyType_Code') != 49 && rec.get('DopDispInfoConsent_IsAgeCorrect') == 1
						&& (
							rec.get('DopDispInfoConsent_IsImpossible_disabled') == 'disabled'
							|| rec.get('DopDispInfoConsent_IsImpossible') == false
						)
					){
						kolvo++;
					}
				});
				win.evnUslugaDispDopGrid.getGrid().getStore().each(function(rec) {
					if (!Ext.isEmpty(rec.get('DopDispInfoConsent_id')) && rec.get('DopDispInfoConsent_IsAgeCorrect') == 1) {
						if ( !Ext.isEmpty(rec.get('EvnUslugaDispDop_didDate')) ) {
							kolvoAgree++;
						}
					}
				});
				if (kolvoAgree < Math.round(kolvo*0.85)) {
					sw.swMsg.alert(lang['oshibka'], lang['kolichestvo_sohranennyih_osmotrov_issledovaniy_sostavlyaet_menee_85%_ot_obyema_ustanovlennogo_dlya_dannogo_vozrasta_i_pola']);
					return false;
				}
			}
			win.verfGroup();
		} else if ( getRegionNick().inlist([ 'buryatiya' ]) ) {
			// нет каких либо ограничений по количеству заведенных услуг
		} else if ( getRegionNick().inlist([ 'ekb' ]) ) {
			// Для Екатеринбруга При сохранении карты ДВН, если в поле «Случай диспансеризации 1 этап закончен» выбрано значение «Да», то должны быть сохранены все осмотры / исследования, для которых в связанных услугах SurveyTypeLink_IsNeedUsluga = Yes. При невыполнении данного контроля выводить сообщение «Сохранены не все обязательные осмотры / исследования. ОК» , сохранение карты отменить.
			if ( base_form.findField('EvnPLDispProf_IsEndStage').getValue() == 2 ) {
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
					if (rec.get('SurveyTypeLink_IsNeedUsluga') == 2 && (rec.get('DopDispInfoConsent_IsAgree') == true || rec.get('DopDispInfoConsent_IsEarlier') == true)) {
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
		} else {
			// Для всех регионов, кроме Уфы Реализовать контроль при сохранении карты ДВН, если проставляем "Случай диспансеризации 1 этап закончен: ДА": Должны быть сохранены все осмотры/исследования, для которых в информированном согласии проставлены чекбоксы "Согласие гражданина" или "Пройдено ранее". При невыполнении данного контроля выводить сообщение "Заведена не вся информация по осмотрам/исследованиям. ОК", сохранение отменить. Т.е. если для осмотра/исследования не проставлен отказ в согласии, то по нему должна быть сохранена услуга (услуга, дата, врач)
			if ( base_form.findField('EvnPLDispProf_IsEndStage').getValue() == 2 ) {
				
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
					sw.swMsg.alert(lang['oshibka'], lang['zavedena_ne_vsya_informatsiya_po_osmotram_issledovaniyam']);
					return false;
				}
			}
			win.verfGroup();
		}

		if ( !EvnPLDispProf_form.getForm().isValid() )
		{
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					EvnPLDispProf_form.getFirstInvalidEl().focus(false);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		base_form.findField('EvnPLDispProf_consDate').setValue(typeof win.findById('EPLDP_EvnPLDispProf_consDate').getValue() == 'object' ? Ext.util.Format.date(win.findById('EPLDP_EvnPLDispProf_consDate').getValue(), 'd.m.Y') : win.findById('EPLDP_EvnPLDispProf_consDate').getValue());

		var xdate2 = new Date(2015,3,1);

		if ( getRegionNick().inlist(['astra','kareliya']) ) {
			xdate2 = new Date(2019, 5, 1);
		}

		if (getRegionNick().inlist(['ekb','penza'])) {
			xdate2 = new Date(2019, 6, 1);
		}

		if (getRegionNick() == 'krym') {
			xdate2 = new Date(2019, 4, 25);
		}

		if ( getRegionNick().inlist([ 'perm', 'kareliya', 'buryatiya', 'ufa']) && win.findById('EPLDP_EvnPLDispProf_consDate').getValue() >= xdate2 && base_form.findField('HealthKind_id').getValue() == 3 ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function () {
					base_form.findField('HealthKind_id').focus(true);
				},
				icon: Ext.Msg.ERROR,
				msg: langs('Присвоение III группы здоровья допускается только для случаев диспансеризации, открытых до ' + xdate2.format('d.m.Y') + '.'),
				title: langs('Ошибка')
			});
			return false;
		}

		// https://redmine.swan.perm.ru/issues/19835
		// 2. При сохранении карты диспансеризации реализовать контроль: Дата оказания любой услуги (осмотра/исследования) должна быть не раньше, чем за год до осмотра
		// врача-терапевта. При невыполнении данного контроля выводить сообщение: "Дата осмотра/исследования, проведенного ранее должна быть не раньше, чем за год до
		// проведения осмотра врача-терапевта", сохранение отменить.
		var
			EvnUslugaDispDop_terDate,
			hasEvnUslugaAfterNewDVNDate = false,
			newDVNDate = getNewDVNDate();

		// Вытаскиваем минимальную дату услуги и дату осмотра врачом терапевтом
		this.evnUslugaDispDopGrid.getGrid().getStore().each(function(rec) {
			if ( !Ext.isEmpty(rec.get('EvnUslugaDispDop_didDate')) ) {
				if ( rec.get('SurveyType_Code') == 19 ) {
					EvnUslugaDispDop_terDate = rec.get('EvnUslugaDispDop_didDate');
				}
				if (rec.get('EvnUslugaDispDop_didDate') >= newDVNDate) {
					hasEvnUslugaAfterNewDVNDate = true;
				}
			}
		});

		if (
			getRegionNick().inlist(['astra', 'krasnoyarsk', 'krym', 'perm', 'vologda'])
			&& base_form.findField('EvnPLDispProf_IsNewOrder').getValue() != 2 // Для карты отсутствует признак «Переопределён набор услуг по новому приказу»;
			&& !Ext.isEmpty(newDVNDate)
			&& win.findById('EPLDP_EvnPLDispProf_consDate').getValue() < newDVNDate // Дата согласия меньше даты нового ДВН
			&& base_form.findField('EvnPLDispProf_IsEndStage').getValue() == 2 // В поле случай закончен установлено значение «Да»
			&& hasEvnUslugaAfterNewDVNDate // В маршрутной карте есть хотя бы один осмотр / исследование с датой выполнения больше или равной даты нового ДВН
		) {
			sw.swMsg.alert('Ошибка', 'По требованиям ТФОМС случаи, законченные после ' + newDVNDate.format('d.m.Y') + ' должны содержать перечень осмотров / исследований в соответствии с приказом 124н. Выполните переопределение перечня осмотров / исследований, нажав на кнопку «Услуги по 124н» в разделе Информированное добровольное согласие.');
			return false;
		}

		if ( base_form.findField('EvnPLDispProf_IsEndStage').getValue() == 2 && Ext.isEmpty(EvnUslugaDispDop_terDate) ) {
			sw.swMsg.alert(lang['oshibka'], lang['data_vyipolneniya_osmotra_vracha_terapevta_obyazatelna_dlya_zapolneniya']);
			return false;
		}

		if (getRegionNick().inlist(['astra', 'ekb', 'krym', 'penza', 'vologda']) && !Ext.isEmpty(EvnUslugaDispDop_terDate) && EvnUslugaDispDop_terDate >= xdate2 && base_form.findField('HealthKind_id').getValue() == 3) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function () {
					base_form.findField('HealthKind_id').focus(true);
				},
				icon: Ext.Msg.ERROR,
				msg: langs('Присвоение III группы здоровья допускается только для случаев с датой осмотра врача-терапевта до ' + xdate2.format('d.m.Y') + '.'),
				title: langs('Ошибка')
			});
			return false;
		}

		if (!Ext.isEmpty(EvnUslugaDispDop_terDate)) {
			var errorInDates = false;

			this.evnUslugaDispDopGrid.getGrid().getStore().each(function(rec) {
				if (!Ext.isEmpty(rec.get('EvnUslugaDispDop_didDate'))) {
					if (rec.get('SurveyType_Code') == 19) {
						// терапевт :)
					} else if (win.findById('EPLDP_EvnPLDispProf_consDate').getValue() >= newDVNDate && rec.get('SurveyType_Code') && rec.get('SurveyType_Code').inlist([16, 21])) {
						if (!Ext.isEmpty(rec.get('EvnUslugaDispDop_didDate')) && rec.get('EvnUslugaDispDop_didDate') < EvnUslugaDispDop_terDate.add('Y', -2)) {
							sw.swMsg.alert('Ошибка', 'Дата осмотра/исследования "' + rec.get('SurveyType_Name') + '", проведенного ранее, должна быть не раньше, чем за 2 года до проведения осмотра врача-терапевта');
							errorInDates = true;
			return false;
		}
					} else {
						if (!Ext.isEmpty(rec.get('EvnUslugaDispDop_didDate')) && rec.get('EvnUslugaDispDop_didDate') < EvnUslugaDispDop_terDate.add('Y', -1)) {
							sw.swMsg.alert('Ошибка', 'Дата осмотра/исследования, проведенного ранее, должна быть не раньше, чем за год до проведения осмотра врача-терапевта');
							errorInDates = true;
							return false;
						}
					}
				}
			});

			if (errorInDates) {
				return false;
			}
		}

		// https://redmine.swan.perm.ru/issues/19835
		// 3. При сохранении карты диспансеризации (с указанным значением "Да" в поле "Случай закончен") реализовать контроль: Осмотр врача-терапевта должен быть
		// сохранен. При невыполнении данного контроля выводить сообщение "Осмотра врача-терапевта обязателен при проведении диспансеризации взрослого населения",
		// сохранение отменить.
		if ( base_form.findField('EvnPLDispProf_IsEndStage').getValue() == 2 ) {
			var allowSave = false;

			this.evnUslugaDispDopGrid.getGrid().getStore().each(function(rec) {
				if ( rec.get('SurveyType_Code') == 19 && !Ext.isEmpty(rec.get('EvnUslugaDispDop_didDate')) ) {
					allowSave = true;
				}
			});

			if ( allowSave == false ) {
				sw.swMsg.show({
					buttons: Ext.Msg.OK,
					fn: function() {
						base_form.findField('EvnPLDispProf_IsEndStage').focus(true);
					},
					icon: Ext.Msg.ERROR,
					msg: lang['osmotra_vracha-terapevta_obyazatelen_pri_provedenii_profosmotra_vzroslogo_naseleniya'],
					title: lang['oshibka']
				});
				return false;
			}
		}

		var params = new Object();
		
		win.getLoadMask("Подождите, идет сохранение...").show();

		if ( !Ext.isEmpty(options.AttachmentAnswer) ) {
			params.AttachmentAnswer = 1;
		}
		
		params.EvnPLDispProf_IsKKND = (win.parentForm == 'swPersonDispEditWindow') ? 2 : 1;

		params.checkAttributeforLpuSection = (!Ext.isEmpty(options.checkAttributeforLpuSection)) ? options.checkAttributeforLpuSection : 0;
		if(base_form.findField('EvnPLDispProf_IsMobile').checked){
			params.checkAttributeforLpuSection=2;
		}
		
		EvnPLDispProf_form.getForm().submit({
			failure: function(result_form, action) {
				win.getLoadMask().hide()
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
				win.getLoadMask().hide()
				
				if (action.result)
				{
					if ( action.result.tip == 'AttachmentAnswer'){
						sw.swMsg.show({
							buttons: Ext.Msg.YESNO,
							fn: function(buttonId, text, obj) {
								if ( buttonId == 'yes' ) {
									options.AttachmentAnswer = 1;
									win.doSave( options );
								}
							},
							icon: Ext.MessageBox.QUESTION,
							msg: action.result.Alert_Msg,
							title: langs('Продолжить сохранение?')
						});
					} else {
						if ( options.print && typeof options.callback == 'function' ) {
							options.callback();
						}
						else {
							win.callback();
							win.hide();
						}
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
	id: 'EvnPLDispProfEditWindow',
	showEvnUslugaDispDopEditWindow: function(action) {
		var base_form = this.EvnPLDispProfFormPanel.getForm();
		var grid = this.evnUslugaDispDopGrid.getGrid();
		var win = this;
		
		var record = grid.getSelectionModel().getSelected();
		
		if ( !record || !record.get('DopDispInfoConsent_id') ) {
			return false;
		}
		
		// если опрос то открываем форму анкетирования.
		if (record.get('SurveyType_Code') == 2) {
			//NGS: get PayType_id if is number otherwise null
			var	payTypeID = !isNaN(base_form.findField('PayType_id').getValue()) ? base_form.findField('PayType_id').getValue() : null;
			
			getWnd('swDopDispQuestionEditWindow').show({
				archiveRecord: this.archiveRecord,
				action: action,
				object: 'EvnPLDispProf',
				DopDispQuestion_setDate: record.get('EvnUslugaDispDop_didDate'),
				EvnPLDisp_consDate: (typeof win.findById('EPLDP_EvnPLDispProf_consDate').getValue() == 'object' ? Ext.util.Format.date(win.findById('EPLDP_EvnPLDispProf_consDate').getValue(), 'd.m.Y') : win.findById('EPLDP_EvnPLDispProf_consDate').getValue()),
				EvnPLDisp_id: base_form.findField('EvnPLDispProf_id').getValue(),
				EvnUslugaDispDop_id: record.get('EvnUslugaDispDop_id'),
				DispClass_id: base_form.findField('DispClass_id').getValue(),
				PayType_id: payTypeID, //NGS: pass payTypeID
				onHide: Ext.emptyFn,
				callback: function(qdata) {
					// обновить грид
					grid.getStore().reload();
					// сюда приходит ответ по нажатию кнопки расчёт на форме анкетирования => нужно заполнить соответсвующие поля на форме.
					var alcodepend = 0;
					if (!Ext.isEmpty(qdata)) {
						base_form.findField('EvnPLDispProf_IsStenocard').setValue(1);
						base_form.findField('EvnPLDispProf_IsDoubleScan').setValue(1);
						base_form.findField('EvnPLDispProf_IsTub').setValue(1);
						base_form.findField('EvnPLDispProf_IsEsophag').setValue(1);
						base_form.findField('EvnPLDispProf_IsSmoking').setValue(1);
						base_form.findField('EvnPLDispProf_IsRiskAlco').setValue(1);
						base_form.findField('EvnPLDispProf_IsLowActiv').setValue(1);
						base_form.findField('EvnPLDispProf_IsAlcoDepend').setValue(1);
					
						for(var k in qdata) {
							if (!Ext.isEmpty(qdata[k].QuestionType_id)) {
								switch ( qdata[k].QuestionType_id ) {
									case 55:
									case 56:
										// Автоматически указывать значение «Имеется», если при анкетировании на вопрос №13 или №14 сохранен ответ «Да» 
										if ( (qdata[k].AnswerType_id == 1 && qdata[k].DopDispQuestion_IsTrue == 2) || (qdata[k].AnswerType_id == 3 && qdata[k].DopDispQuestion_ValuesStr == 2) ) {
											base_form.findField('EvnPLDispProf_IsStenocard').setValue(2);
										}
									break;

									case 56:
									case 57:
									case 58:
									case 59:
									case 60:
										// Автоматически указывать значение «Имеется», если при анкетировании хотя бы на один из вопросов №14-18 сохранен ответ «Да»
										if ( (qdata[k].AnswerType_id == 1 && qdata[k].DopDispQuestion_IsTrue == 2) || (qdata[k].AnswerType_id == 3 && qdata[k].DopDispQuestion_ValuesStr == 2) ) {
											base_form.findField('EvnPLDispProf_IsDoubleScan').setValue(2);
										}
									break;

									case 61:
									case 62:
										// Автоматически указывать значение «Имеется», если при анкетировании хотя бы на один из вопросов №19-20 сохранен ответ «Да» 
										if ( (qdata[k].AnswerType_id == 1 && qdata[k].DopDispQuestion_IsTrue == 2) || (qdata[k].AnswerType_id == 3 && qdata[k].DopDispQuestion_ValuesStr == 2) ) {
											base_form.findField('EvnPLDispProf_IsTub').setValue(2);
										}
									break;

									case 63:
									case 66:
										// Автоматически указывать значение «Имеется», если при анкетировании хотя бы на один из вопросов №19-20 сохранен ответ «Да» 
										if ( (qdata[k].AnswerType_id == 1 && qdata[k].DopDispQuestion_IsTrue == 2) || (qdata[k].AnswerType_id == 3 && qdata[k].DopDispQuestion_ValuesStr == 2) ) {
											base_form.findField('EvnPLDispProf_IsEsophag').setValue(2);
										}
									break;

									case 67:
										if ( (qdata[k].AnswerType_id == 1 && qdata[k].DopDispQuestion_IsTrue == 2) || (qdata[k].AnswerType_id == 3 && qdata[k].DopDispQuestion_ValuesStr == 2) ) {
											base_form.findField('EvnPLDispProf_IsSmoking').setValue(2);
										}
									break;

									case 68:
									case 69:
									case 70:
									case 71:
										if ( (qdata[k].AnswerType_id == 1 && qdata[k].DopDispQuestion_IsTrue == 2) || (qdata[k].AnswerType_id == 3 && qdata[k].DopDispQuestion_ValuesStr == 2) ) {
											base_form.findField('EvnPLDispProf_IsRiskAlco').setValue(2);
											alcodepend++;
										}
									break;

									case 72:
										// на вопрос №31 сохранен ответ «до 30 минут»
										if ( qdata[k].DopDispQuestion_ValuesStr == 1 )  {
											base_form.findField('EvnPLDispProf_IsLowActiv').setValue(2);
										}
									break;
								}
							}
						}
					}
					
					if (alcodepend > 3) {
						base_form.findField('EvnPLDispProf_IsAlcoDepend').setValue(2);
					}
					
					win.EvnDiagDopDispBeforeGrid.getGrid().getStore().reload();
					win.HeredityDiag.getGrid().getStore().reload();
					// win.NeedConsultGrid.getGrid().getStore().reload();
				}
				
			});
		// иначе форму услуги
		} else {
			var UslugaComplex_Date = win.findById('EPLDP_EvnPLDispProf_consDate').getValue();
			if (base_form.findField('EvnPLDispProf_IsNewOrder').getValue() == 2) {
				// Список осмотров (исследований) должен определяться по дате на конец года
				UslugaComplex_Date.setMonth(11); // декабрь
				UslugaComplex_Date.setDate(31); // 31-ое число
			}

			var personinfo = win.PersonInfoPanel;
			
			getWnd('swEvnUslugaDispDop13EditWindow').show({
				archiveRecord: this.archiveRecord,
				action: action,
				object: 'EvnPLDispProf',
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
					EvnVizitDispDop_pid: base_form.findField('EvnPLDispProf_id').getValue(),
					PersonEvn_id: base_form.findField('PersonEvn_id').getValue(),
					Server_id: base_form.findField('Server_id').getValue(),
					EvnUslugaDispDop_id: record.get('EvnUslugaDispDop_id')
				},
				DopDispInfoConsent_id: record.get('DopDispInfoConsent_id'),
				SurveyTypeLink_id: record.get('SurveyTypeLink_id'),
				SurveyType_Code: record.get('SurveyType_Code'),
				SurveyType_Name: record.get('SurveyType_Name'),
				SurveyType_IsVizit: record.get('SurveyType_IsVizit'),
				UslugaComplex_Date: UslugaComplex_Date,
				ShowDeseaseStageCombo: getRegionNick().inlist(['perm','buryatiya','kareliya','penza','ufa'])?true:false,
				onHide: Ext.emptyFn,
				callback: function(data) {
					// обновить грид!
					grid.getStore().reload();
					
					// обновляем результаты в нередактируемых полях
					for (var key in data) {
						if (!Ext.isEmpty(base_form.findField(key))) {
							if(key == 'systolic_blood_pressure'||key=='diastolic_blood_pressure'){
								base_form.findField('systolic_and_diastolic').setValue(
									data['systolic_blood_pressure']+"/"+data['diastolic_blood_pressure']);
							}
							if ( key == 'glucose' ) {
								if ( record.get('SurveyType_Code').inlist([6,12]) ) {
									base_form.findField(key).setValue(data[key]);
								}
							}
							else {
								base_form.findField(key).setValue(data[key]);
							}
						}
					}

					if (getRegionNick() == 'buryatiya' && record.get('SurveyType_Code') == 19) {
						if (data.Diag_Code && data.Diag_Code == 'Z03.1') {
							base_form.findField('EvnPLDispProf_IsSuspectZNO').setValue(2);
						} else {
							base_form.findField('EvnPLDispProf_IsSuspectZNO').clearValue();
						}
						base_form.findField('EvnPLDispProf_IsSuspectZNO').fireEvent('change', base_form.findField('EvnPLDispProf_IsSuspectZNO'), base_form.findField('EvnPLDispProf_IsSuspectZNO').getValue());
					}

					// обновляем грид с впервые выявленными
					win.EvnDiagDopDispFirstGrid.getGrid().getStore().reload();
					win.EvnDiagDopDispBeforeGrid.getGrid().getStore().reload();

					if ( record.get('SurveyType_Code') == 4 && !Ext.isEmpty(data.body_mass_index) ) {
						base_form.findField('body_mass_index').fireEvent('change', base_form.findField('body_mass_index'), base_form.findField('body_mass_index').getValue());
					}

					if (getRegionNick().inlist(['astra','ekb','krym','penza','vologda']) && record.get('SurveyType_Code') == 19) {
						win.filterHealthKindCombo();
					}
				}
			});
		}		
	},
	printRouteCard: function(){
		var base_form = this.EvnPLDispProfFormPanel.getForm();
		var evn_pl_id = base_form.findField('EvnPLDispProf_id').getValue();
		var person_id = base_form.findField('Person_id').getValue();
		var evnpldispprof_setdate = base_form.findField('EvnPLDispProf_setDate').getValue();
		var evnpldispprof_year = new Date(evnpldispprof_setdate.replace(/(\d+).(\d+).(\d+)/, '$3/$2/$1')).getFullYear();
        var paramIsEarlier = 2;
        if(Ext.globalOptions.dispprof.do_not_show_unchecked_research){
            paramIsEarlier = 1;
        }
		printBirt({
			'Report_FileName': 'pan_RouteCardDD.rptdesign',
			'Report_Params': '&paramPerson=' + person_id + '&paramDispClass=5&paramYear=' + evnpldispprof_year + '&paramIsEarlier='+ paramIsEarlier,
			'Report_Format': 'pdf'
		});
		printBirt({
			'Report_FileName': 'pan_RouteCardDD2.rptdesign',
			'Report_Params': '',
			'Report_Format': 'pdf'
		});
	},
	loadDopDispInfoConsentGrid: function(EvnPLDispProf_consDate) {
		var win = this;
		var base_form = win.EvnPLDispProfFormPanel.getForm();

		win.dopDispInfoConsentGrid.getGrid().getStore().removeAll();
		win.dopDispInfoConsentGrid.loadData({
			params: {
				Person_id: base_form.findField('Person_id').getValue(),
				DispClass_id: base_form.findField('DispClass_id').getValue(),
				EvnPLDispProf_id: base_form.findField('EvnPLDispProf_id').getValue(),
				EvnPLDispProf_IsNewOrder: base_form.findField('EvnPLDispProf_IsNewOrder').getValue(),
				EvnPLDispProf_consDate: (typeof EvnPLDispProf_consDate == 'object' ? Ext.util.Format.date(EvnPLDispProf_consDate, 'd.m.Y') : EvnPLDispProf_consDate)
			},
			globalFilters: {
				Person_id: base_form.findField('Person_id').getValue(),
				DispClass_id: base_form.findField('DispClass_id').getValue(),
				EvnPLDispProf_id: base_form.findField('EvnPLDispProf_id').getValue(),
				EvnPLDispProf_IsNewOrder: base_form.findField('EvnPLDispProf_IsNewOrder').getValue(),
				EvnPLDispProf_consDate: (typeof EvnPLDispProf_consDate == 'object' ? Ext.util.Format.date(EvnPLDispProf_consDate, 'd.m.Y') : EvnPLDispProf_consDate)
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
	},
	filterHealthKindCombo: function() {
		var
			win = this,
			base_form = win.EvnPLDispProfFormPanel.getForm(),
			HealthKind_id = base_form.findField('HealthKind_id').getValue(),
			index,
			onDate = win.findById('EPLDP_EvnPLDispProf_consDate').getValue(),
			terDate,
			xDate = new Date(2015, 3, 1);

		if (getRegionNick().inlist(['astra', 'ekb', 'krym', 'penza', 'vologda'])) {
			if (getRegionNick().inlist(['ekb','penza'])) {
				xDate = new Date(2019, 6, 1);
			} else if  (getRegionNick() == 'krym') {
				xDate = new Date(2019, 4, 25);
			} else {
				xDate = new Date(2019, 5, 1);
			}
			win.evnUslugaDispDopGrid.getGrid().getStore().each(function(rec) {
				if ( rec.get('SurveyType_Code') == 19 && !Ext.isEmpty(rec.get('EvnUslugaDispDop_didDate')) ) {
					terDate = rec.get('EvnUslugaDispDop_didDate');
				}
			});

			if ( !Ext.isEmpty(terDate) ) {
				onDate = terDate;
			}
			else {
				onDate = getValidDT(getGlobalOptions().date, '');
			}
		}

		if ( getRegionNick().inlist(['kareliya']) ) {
			xDate = new Date(2019, 5, 1);
		}

		base_form.findField('HealthKind_id').getStore().clearFilter();
		base_form.findField('HealthKind_id').lastQuery = '';

		if ( getRegionNick().inlist(['adygeya', 'astra', 'ekb', 'kareliya', 'krasnoyarsk', 'krym', 'perm', 'buryatiya', 'penza', 'pskov', 'ufa', 'vologda']) && onDate >= xDate ) {
			// Группа здоровья, выбор из справочника: I, II, IIIа, IIIб
			base_form.findField('HealthKind_id').getStore().filterBy(function (rec) {
				return (!Ext.isEmpty(rec.get('HealthKind_Code')) && rec.get('HealthKind_Code').inlist([1, 2, 6, 7]));
			});
		}
		else {
			base_form.findField('HealthKind_id').getStore().filterBy(function (rec) {
				return (!Ext.isEmpty(rec.get('HealthKind_Code')) && rec.get('HealthKind_Code').inlist([1, 2, 3]));
			});
		}

		index = base_form.findField('HealthKind_id').getStore().findBy(function(rec) {
			return (rec.get('HealthKind_id') == HealthKind_id);
		});

		if ( index >= 0 ) {
			base_form.findField('HealthKind_id').setValue(HealthKind_id);
		}
		else if (
			!('form'.inlist(win.notLoadedParts)) // если форма ещё в процессе загрузки, то очищать поле не надо
			&& !('uslugagrid'.inlist(win.notLoadedParts))
		) {
			base_form.findField('HealthKind_id').clearValue();
		}
	},
	initComponent: function() {
		var win = this;
		
		this.dopDispInfoConsentGrid = new sw.Promed.ViewFrame({
			autoLoadData: false,
			id: 'EPLDPEF_dopDispInfoConsentGrid',
			dataUrl: '/?c=EvnPLDispProf&m=loadDopDispInfoConsent',
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
				{ name: 'DopDispInfoConsent_IsAgeCorrect', type: 'int', hidden: true },
				{ name: 'SurveyType_Name', type: 'string', sortable: false, header: langs('Осмотр, исследование'), id: 'autoexpand' },
				{ name: 'DopDispInfoConsent_IsEarlier', sortable: false, type: 'checkcolumnedit', isparams: true, header: langs('Пройдено ранее'), width: 180 },
				{ name: 'DopDispInfoConsent_IsAgree', sortable: false, type: 'checkcolumnedit', isparams: true, header: langs('Согласие гражданина'), width: 180 },
				{ name: 'DopDispInfoConsent_IsImpossible_disabled', type: 'string', hidden: true },
				{ name: 'DopDispInfoConsent_IsImpossible',dataIndex: 'DopDispInfoConsent_IsImpossible_disabled', sortable: false, type: 'checkcolumnedit', isparams: true, header: langs('Невозможно по показаниям'), width: 180,hidden: getGlobalOptions().region.nick == 'kz'},
			],
			onLoadData: function() {
				if ( win.action != 'view' ) {
					win.saveConsentButton.enable();
				}

				this.checkIsAgree();
				this.doLayout(); // почему то не показывается скролл у грида без этого
			},
			checkIsAgree: function() {
				// проверить согласие для первой строки..
				var record = win.dopDispInfoConsentGrid.getGrid().getStore().getAt(0);

				if (record) {
					win.dopDispInfoConsentGrid.getGrid().getStore().each(function(rec) {
						if ( !Ext.isEmpty(rec.get('DopDispInfoConsent_id')) ) {
							if (rec.get('SurveyType_Code') != 49) {
								rec.beginEdit();
								if (record.get('DopDispInfoConsent_IsAgree') != true) {
									rec.set('DopDispInfoConsent_IsAgree', 'hidden');
									rec.set('DopDispInfoConsent_IsEarlier', 'hidden');
									rec.set('DopDispInfoConsent_IsImpossible', 'hidden');

								} else if (rec.get('DopDispInfoConsent_IsAgree') == 'hidden') {
									// https://redmine.swan.perm.ru/issues/19835
									// 4. Флаги в добровольном согласии по умолчанию должны быть все проставлены как для детей-сирот, так и для взрослого населения.
									// Сейчас в новых картах флаги не проставлены и приходится "протыкивать" всю карту.
									rec.set('DopDispInfoConsent_IsAgree', true);
									rec.set('DopDispInfoConsent_IsEarlier', false);
									rec.set('DopDispInfoConsent_IsImpossible', false);
								}
								
								// если оба отмечены, то снимаем флаг "пройдено ранее", т.к. оба флага не могут быть одновременно подняты
								if (rec.get('DopDispInfoConsent_IsEarlier') == true && rec.get('DopDispInfoConsent_IsAgree') == true) {
									rec.set('DopDispInfoConsent_IsEarlier', false);
								}

								if (rec.get('DopDispInfoConsent_IsImpossible') == true) {
									rec.set('DopDispInfoConsent_IsEarlier', false);
									rec.set('DopDispInfoConsent_IsAgree', false);
								}

								if (rec.get('DopDispInfoConsent_IsImpossible_disabled') == 'disabled') {
									rec.set('DopDispInfoConsent_IsImpossible', 'hidden');
								}

								rec.endEdit();
							} else {
								rec.set('DopDispInfoConsent_IsEarlier', 'hidden'); // убрать пройдено ранее для строки первый этап диспансеризации
								rec.set('DopDispInfoConsent_IsImpossible', 'hidden');
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
				
				win.checkEvnPLDispProfIsSaved();
			},
			onAfterEdit: function(o) {
				if (o && o.field) {
					if (o.field == 'DopDispInfoConsent_IsEarlier' && o.value == true) {
						if (o.record.get('DopDispInfoConsent_IsAgree') != 'hidden') {
							o.record.set('DopDispInfoConsent_IsAgree', false);
						}
						if (o.record.get('DopDispInfoConsent_IsImpossible') != 'hidden') {
							o.record.set('DopDispInfoConsent_IsImpossible', false);
						}
					}
					
					if (o.field == 'DopDispInfoConsent_IsAgree' && o.value == true) {
						if (o.record.get('DopDispInfoConsent_IsEarlier') != 'hidden') {
							o.record.set('DopDispInfoConsent_IsEarlier', false);
						}
						if (o.record.get('DopDispInfoConsent_IsImpossible') != 'hidden') {
							o.record.set('DopDispInfoConsent_IsImpossible', false);
						}
					}

					if (o.field == 'DopDispInfoConsent_IsImpossible' && o.value == true) {
						o.record.set('DopDispInfoConsent_IsEarlier', false);
						o.record.set('DopDispInfoConsent_IsAgree', false);
					}

					// при снятии чекбокса в поле профосмотр вцелом снимать все остальные и делать недоступными
					if (o.record.get('SurveyType_Code') == 49) {
						this.checkIsAgree();
					}
				}
			},
		});
		
		this.evnUslugaDispDopGrid = new sw.Promed.ViewFrame({
			autoLoadData: false,
			actions: [
				{ name: 'action_add', disabled: true, hidden: true },
				{ name: 'action_edit', handler: function() { win.showEvnUslugaDispDopEditWindow('edit'); } },
				{ name: 'action_view', handler: function() { win.showEvnUslugaDispDopEditWindow('view'); } },
				{ name: 'action_delete', disabled: true, hidden: true },
				{ name: 'action_refresh' },
				{ name: 'action_print', handler: function() {win.printRouteCard();}}
			],
			onLoadData: function() {
				win.checkIsNewOrderButton();
				this.doLayout();

				var index = win.notLoadedParts.indexOf('uslugagrid');
				if (index !== -1) win.notLoadedParts.splice(index, 1);

				if (getRegionNick().inlist(['astra','ekb','krym','penza','vologda'])) {
					win.filterHealthKindCombo();
				}
			},
			id: 'EPLDPEF_evnUslugaDispDopGrid',
			dataUrl: '/?c=EvnPLDispProf&m=loadEvnUslugaDispDopGrid',
			region: 'center',
			height: 200,
			title: '',
			toolbar: true,
			stringfields: [
				{ name: 'DopDispInfoConsent_id', type: 'int', header: 'ID', key: true },
				{ name: 'SurveyTypeLink_id', type: 'int', hidden: true },
				{ name: 'SurveyType_Code', type: 'int', hidden: true },
				{ name: 'DopDispInfoConsent_IsAgeCorrect', type: 'int', hidden: true },
				{ name: 'SurveyType_IsVizit', type: 'int', hidden: true },
				{ name: 'EvnUslugaDispDop_id', type: 'int', hidden: true },
				{ name: 'SurveyType_Name', type: 'string', header: 'Наименование осмотра (исследования)', id: 'autoexpand' },
				{ name: 'EvnUslugaDispDop_ExamPlace', type: 'string', header: 'Место проведения', width: 200 },
				//{ name: 'EvnUslugaDispDop_setDate', renderer: Ext.util.Format.dateRenderer('d.m.Y H:i:s'), header: 'Дата и время проведения', width: 200 },
				{ name: 'EvnUslugaDispDop_didDate', type: 'date', header: 'Дата выполнения', width: 100 },
				{ name: 'EvnUslugaDispDop_WithDirection', type: 'checkbox', header: 'Направление / назначение', width: 100 }
			]
		});
	
		this.PersonInfoPanel = new sw.Promed.PersonInformationPanel({
			button2Callback: function(callback_data) {
				var base_form = win.EvnPLDispProfFormPanel.getForm();
				
				base_form.findField('PersonEvn_id').setValue(callback_data.PersonEvn_id);
				base_form.findField('Server_id').setValue(callback_data.Server_id);
				
				win.PersonInfoPanel.load( { Person_id: callback_data.Person_id, Server_id: callback_data.Server_id } );
			},
			region: 'north'
		});
		
		this.DopDispInfoConsentPanel = new sw.Promed.Panel({
			items: [{
				border: false,
				labelWidth: 200,
				layout: 'form',
				style: 'padding: 5px;',
				items: [/*{
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
				},*/ {
					fieldLabel: lang['povtornaya_podacha'],
					listeners: {
						'check': function(checkbox, value) {
							if ( getRegionNick() != 'perm' ) {
								return false;
							}

							var base_form = win.EvnPLDispProfFormPanel.getForm();

							var
								EvnPLDispProf_IndexRep = parseInt(base_form.findField('EvnPLDispProf_IndexRep').getValue()),
								EvnPLDispProf_IndexRepInReg = parseInt(base_form.findField('EvnPLDispProf_IndexRepInReg').getValue()),
								EvnPLDispProf_IsPaid = parseInt(base_form.findField('EvnPLDispProf_IsPaid').getValue());

							var diff = EvnPLDispProf_IndexRepInReg - EvnPLDispProf_IndexRep;

							if ( EvnPLDispProf_IsPaid != 2 || EvnPLDispProf_IndexRepInReg == 0 ) {
								return false;
							}

							if ( value == true ) {
								if ( diff == 1 || diff == 2 ) {
									EvnPLDispProf_IndexRep = EvnPLDispProf_IndexRep + 2;
								}
								else if ( diff == 3 ) {
									EvnPLDispProf_IndexRep = EvnPLDispProf_IndexRep + 4;
								}
							}
							else if ( value == false ) {
								if ( diff <= 0 ) {
									EvnPLDispProf_IndexRep = EvnPLDispProf_IndexRep - 2;
								}
							}

							base_form.findField('EvnPLDispProf_IndexRep').setValue(EvnPLDispProf_IndexRep);
						}
					},
					name: 'EvnPLDispProf_RepFlag',
					xtype: 'checkbox'
				}, {
					allowBlank: false,
					fieldLabel: lang['data_podpisaniya_soglasiya_otkaza'],
					format: 'd.m.Y',
					id: 'EPLDP_EvnPLDispProf_consDate',
					listeners: {
						'change': function(field, newValue, oldValue) {
							//win.loadUslugaComplex();

							var base_form = win.EvnPLDispProfFormPanel.getForm();

							base_form.findField('Diag_id').setFilterByDate(newValue);

							if ( !win.saveConsentButton.disabled ) {
								win.saveConsentButton.disable();
							}

							var newDVNDate = getNewDVNDate();

							if (!Ext.isEmpty(oldValue) && !Ext.isEmpty(newValue) && win.checkEvnPLDispProfIsSaved() && (
								getRegionNick().inlist([ 'perm', 'ufa' ]) && newValue.format('Y') != oldValue.format('Y')
								|| (!Ext.isEmpty(newDVNDate) && newValue < newDVNDate && oldValue >= newDVNDate) // при переходе через 06.05.2019 / 01.06.2019
								|| (!Ext.isEmpty(newDVNDate) && oldValue < newDVNDate && newValue >= newDVNDate)
							)) {
								sw.swMsg.show({
									buttons: Ext.Msg.YESNO,
									fn: function (buttonId) {
										if (buttonId == 'yes') {
											win.saveDopDispInfoConsentAfterLoad = true;
											win.blockSaveDopDispInfoConsent = true;
											win.loadDopDispInfoConsentGrid(newValue);
										} else {
											win.findById('EPLDP_EvnPLDispProf_consDate').setValue(oldValue);
										}
									},
									msg: 'При изменении даты подписания информированного согласия изменится набор осмотров / исследований. Заведенная информация по осмотрам / исследованиям может быть потеряна. Изменить дату?',
									title: langs('Подтверждение')
								});
								return false;
							}

							win.checkIsNewOrderButton();
							win.filterHealthKindCombo();

							win.blockSaveDopDispInfoConsent = true;
							win.loadDopDispInfoConsentGrid(newValue);
						}
					},
					plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
					width: 100,
					xtype: 'swdatefield'
				}, {
					fieldLabel: lang['sluchay_obslujen_mobilnoy_brigadoy'],
					name: 'EvnPLDispProf_IsMobile',
					xtype: 'checkbox',
					listeners: {
						'check': function(checkbox, value) {
							var base_form = win.EvnPLDispProfFormPanel.getForm();
							
							if ( value == true ) {
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
					fieldLabel: lang['mo_mobilnoy_brigadyi'],
					valueField: 'Lpu_id',
					hiddenName: 'Lpu_mid',
					xtype: 'sworgcombo',
					onTrigger1Click: function() {
						var combo = this;
						if (combo.disabled) {
							return false;
						}
						
						var base_form = win.EvnPLDispProfFormPanel.getForm();
						
						getWnd('swOrgSearchWindow').show({
							enableOrgType: false,
							onlyFromDictionary: true,
							object: 'lpu',
							DispClass_id: base_form.findField('DispClass_id').getValue(),
							Disp_consDate: (typeof win.findById('EPLDP_EvnPLDispProf_consDate').getValue() == 'object' ? Ext.util.Format.date(win.findById('EPLDP_EvnPLDispProf_consDate').getValue(), 'd.m.Y') : win.findById('EPLDP_EvnPLDispProf_consDate').getValue()),
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
				}, {
					fieldLabel: 'Проведен вне МО',
					listeners: {
						'render': function() {
							if (getRegionNick() != 'ekb') {
								this.hideContainer();
							}
						}
					},
					name: 'EvnPLDispProf_IsOutLpu',
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
							win.saveConsentButton = new Ext.Button({
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
									var base_form = win.EvnPLDispProfFormPanel.getForm();
									var paramEvnPLDispProf = base_form.findField('EvnPLDispProf_id').getValue();
									if(paramEvnPLDispProf) {
										var dialog_wnd = Ext.Msg.show({
											title: lang['vid_soglasiya'],
											msg:lang['vyiberite_vid_soglasiya'],
											buttons: {yes: "От имени пациента", no: "От имени законного представителя", cancel: "Отмена"},
											fn: function(btn){
												if (btn == 'cancel') {
													return;
												}
												if(btn == 'yes'){ //От имени пациента
													printBirt({
														'Report_FileName': 'EvnPLProfInfoConsent.rptdesign',
														'Report_Params': '&paramEvnPLProf=' + paramEvnPLDispProf,
														'Report_Format': 'pdf'
													});
												}
												if(btn == 'no') { //От имени законного представителя
													printBirt({
														'Report_FileName': 'EvnPLProfInfoConsent_Deputy.rptdesign',
														'Report_Params': '&paramEvnPLProf=' + paramEvnPLDispProf,
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
					}, {
						border: false,
						bodyStyle: 'margin-left: 5px;',
						layout: 'form',
						items: [
							win.setIsNewOrderButton = new Ext.Button({
								handler: function() {
									sw.swMsg.show({
										buttons: Ext.Msg.YESNO,
										fn: function ( buttonId ) {
											if ( buttonId == 'yes' ) {
												var base_form = win.EvnPLDispProfFormPanel.getForm();
												var consDate = win.findById('EPLDP_EvnPLDispProf_consDate').getValue();
												win.saveDopDispInfoConsentAfterLoad = true;
												win.blockSaveDopDispInfoConsent = true;
												base_form.findField('EvnPLDispProf_IsNewOrder').setValue(2);
												win.loadDopDispInfoConsentGrid(consDate);
											}
										},
										msg: 'Перечень осмотров / исследований будет изменён в соответствии с приказом 124н. Некоторые выполненные осмотры / исследования могут быть удалены. Продолжить?',
										title: 'Подтверждение'
									});
								}.createDelegate(this),
								text: 'Услуги по 124н',
								tooltip: 'Переопределить набор осмотров / исследований в соответствии с возрастом и полом по приказу 124н'
							})
						]
					}]
				}
			],
			layout: 'form',
			border: false,
			autoHeight: true,
			collapsible: true,
			title: lang['informirovannoe_dobrovolnoe_soglasie']
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
		
		this.EvnDiagDopDispBeforeGrid = new sw.Promed.ViewFrame({
			autoLoadData: false,
			editformclassname: 'swEvnDiagDopDispEditForm',
			object: 'EvnDiagDopDisp',
			actions: [
				{ name: 'action_add' },
				{ name: 'action_edit' },
				{ name: 'action_view' },
				{ name: 'action_delete' },
				{ name: 'action_refresh' },
				{ name: 'action_print'}
			],
			id: 'EPLDPEF_EvnDiagDopDispBefore',
			dataUrl: '/?c=EvnDiagDopDisp&m=loadEvnDiagDopDispGrid',
			region: 'center',
			height: 200,
			title: lang['ranee_izvestnyie_imeyuschiesya_zabolevaniya'],
			toolbar: true,
			stringfields: [
				{ name: 'EvnDiagDopDisp_id', type: 'int', header: 'ID', key: true },
				{ name: 'Diag_id', type: 'int', hidden: true },
				{ name: 'Diag_Name', type: 'string', header: lang['naimenovanie'], id: 'autoexpand' },
				{ name: 'EvnDiagDopDisp_setDate', type: 'date', header: lang['data_postanovki_diagnoza'], width: 100 }
			]
		});
		
		this.HeredityDiag = new sw.Promed.ViewFrame({
			autoLoadData: false,
			editformclassname: 'swHeredityDiagEditForm',
			object: 'HeredityDiag',
			actions: [
				{ name: 'action_add' },
				{ name: 'action_edit' },
				{ name: 'action_view' },
				{ name: 'action_delete' },
				{ name: 'action_refresh' },
				{ name: 'action_print'}
			],
			id: 'EPLDPEF_HeredityDiag',
			dataUrl: '/?c=HeredityDiag&m=loadHeredityDiagGrid',
			region: 'center',
			height: 200,
			title: lang['nasledstvennost_po_zabolevaniyam'],
			toolbar: true,
			stringfields: [
				{ name: 'HeredityDiag_id', type: 'int', header: 'ID', key: true },
				{ name: 'Diag_id', type: 'int', hidden: true },
				{ name: 'HeredityType_id', type: 'int', hidden: true },
				{ name: 'Diag_Name', type: 'string', header: lang['naimenovanie'], id: 'autoexpand' },
				{ name: 'HeredityType_Name', type: 'string', header: lang['nasledstvennost'], width: 150 }
			]
		});

		this.ProphConsultGrid = new sw.Promed.ViewFrame({
			autoLoadData: false,
			editformclassname: 'swProphConsultEditForm',
			object: 'ProphConsult',
			actions: [
				{ name: 'action_add' },
				{ name: 'action_edit' },
				{ name: 'action_view' },
				{ name: 'action_delete' },
				{ name: 'action_refresh' },
				{ name: 'action_print'}
			],
			id: 'EPLDPEF_ProphConsult',
			dataUrl: '/?c=ProphConsult&m=loadProphConsultGrid',
			region: 'center',
			height: 200,
			title: lang['pokazaniya_k_uglublennomu_profilakticheskomu_konsultirovaniyu'],
			toolbar: true,
			stringfields: [
				{ name: 'ProphConsult_id', type: 'int', header: 'ID', key: true },
				{ name: 'RiskFactorType_id', type: 'int', hidden: true },
				{ name: 'RiskFactorType_Name', type: 'string', header: lang['faktor_riska'], id: 'autoexpand' }
			]
		});
		
		/*this.NeedConsultGrid = new sw.Promed.ViewFrame({
			autoLoadData: false,
			editformclassname: 'swNeedConsultEditForm',
			object: 'NeedConsult',
			actions: [
				{ name: 'action_add' },
				{ name: 'action_edit' },
				{ name: 'action_view' },
				{ name: 'action_delete' },
				{ name: 'action_refresh' },
				{ name: 'action_print'}
			],
			id: 'EPLDPEF_NeedConsult',
			dataUrl: '/?c=NeedConsult&m=loadNeedConsultGrid',
			region: 'center',
			height: 200,
			title: lang['pokazaniya_k_konsultatsii_vracha-spetsialista'],
			toolbar: true,
			stringfields: [
				{ name: 'NeedConsult_id', type: 'int', header: 'ID', key: true },
				{ name: 'ConsultationType_id', type: 'int', hidden: true },
				{ name: 'Post_id', type: 'int', hidden: true },
				{ name: 'Post_Name', type: 'string', header: lang['vrach-spetsialist'], id: 'autoexpand' },
				{ name: 'ConsultationType_Name', type: 'string', header: lang['mesto_provedeniya'], width: 150 }
			]
		});*/

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

		this.EvnDiagDopDispFirstGrid = new sw.Promed.ViewFrame({
			autoLoadData: false,
			editformclassname: 'swEvnDiagDopDispEditForm',
			object: 'EvnDiagDopDisp',
			actions: [
				{ name: 'action_add' },
				{ name: 'action_edit' },
				{ name: 'action_view' },
				{ name: 'action_delete' },
				{ name: 'action_refresh' },
				{ name: 'action_print'}
			],
			id: 'EPLDPEF_EvnDiagDopDispFirst',
			dataUrl: '/?c=EvnDiagDopDisp&m=loadEvnDiagDopDispGrid',
			region: 'center',
			height: 200,
			title: lang['vpervyie_vyiyavlennyie_zabolevaniya'],
			toolbar: true,
			stringfields: [
				{ name: 'EvnDiagDopDisp_id', type: 'int', header: 'ID', key: true },
				{ name: 'Diag_id', type: 'int', hidden: true },
				{ name: 'Diag_Name', type: 'string', header: lang['naimenovanie'], id: 'autoexpand' },
				{ name: 'DiagSetClass_Name', type: 'string', header: lang['tip'], width: 150 }
			]
		});
		
		this.EvnPLDispProfMainResultsPanel = new sw.Promed.Panel({
			bodyBorder: false,
			title: lang['osnovnyie_rezultatyi_profosmotra'],
			border: false,
			collapsible: true,
			titleCollapse: true,
			animCollapse: false,
			buttonAlign: 'left',
			frame: false,
			labelAlign: 'right',
			labelWidth: 195,
			items: [{
					name: 'EvnPLDispProf_id',
					value: 0,
					xtype: 'hidden'
				}, {
					name:'EvnPLDispProf_IsPaid',
					xtype:'hidden'
				}, {
					name:'EvnPLDispProf_IsNewOrder',
					xtype:'hidden'
				}, {
					name:'EvnPLDispProf_IndexRep',
					xtype:'hidden'
				}, {
					name:'EvnPLDispProf_IndexRepInReg',
					xtype:'hidden'
				}, {
					name: 'accessType',
					xtype: 'hidden'
				}, {
					name: 'DispClass_id',
					value: 0,
					xtype: 'hidden'
				}, {
					name: 'EvnPLDispProf_fid',
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
					name: 'EvnPLDispProf_setDate',
					xtype: 'hidden'
				}, {
					name: 'EvnPLDispProf_disDate',
					xtype: 'hidden'
				}, {
					name: 'EvnPLDispProf_consDate',
					xtype: 'hidden'
				}, {
					name: 'Server_id',
					value: 0,
					xtype: 'hidden'
				},
				// подраздел "Ранее известные имеющиеся заболевания" - грид
				win.EvnDiagDopDispBeforeGrid,
				// подраздел "Наследственность по заболеваниям" - грид
				win.HeredityDiag,
				// подраздел "Подозрение на заболевания, медицинские показания к обследованиям"
				new Ext.Panel({
					title: "Подозрение на заболевания, медицинские показания к обследованиям",
					layout: 'form',
					bodyStyle: 'padding: 5px',
					items: [
						{
							fieldLabel: 'Подозрение на ЗНО',
							hiddenName: 'EvnPLDispProf_IsSuspectZNO',
							id: 'EPLDPEF_EvnPLDispProf_IsSuspectZNO',
							width: 100,
							xtype: 'swyesnocombo',
							listeners:{
								'change':function (combo, newValue, oldValue) {
									var base_form = win.EvnPLDispProfFormPanel.getForm();

									var index = combo.getStore().findBy(function (rec) {
										return (rec.get(combo.valueField) == newValue);
									});
									combo.fireEvent('select', combo, combo.getStore().getAt(index), index);

									if (base_form.findField('EvnPLDispProf_IsSuspectZNO').getValue() == 2) {
										Ext.getCmp('EPLDPEF_PrintKLU').enable();
										Ext.getCmp('EPLDPEF_PrintOnko').enable();
									} else {
										Ext.getCmp('EPLDPEF_PrintKLU').disable();
										Ext.getCmp('EPLDPEF_PrintOnko').disable();
									}

								},
								'select':function (combo, record, idx) {
									if (typeof record == 'object' && record.get('YesNo_id') == 2) {
										Ext.getCmp('EPLDPEF_Diag_spid').showContainer();
										Ext.getCmp('EPLDPEF_Diag_spid').setAllowBlank(!getRegionNick().inlist([ 'astra', 'perm' ]));
									} else {
										Ext.getCmp('EPLDPEF_Diag_spid').hideContainer();
										Ext.getCmp('EPLDPEF_Diag_spid').setAllowBlank(true);
									}
								}
							}
						}, {
							fieldLabel: 'Подозрение на диагноз',
							hiddenName: 'Diag_spid',
							id: 'EPLDPEF_Diag_spid',
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
						}, {
							fieldLabel: langs('Подозрение на наличие стенокардии напряжения'),
							hiddenName: 'EvnPLDispProf_IsStenocard',
							xtype: 'swyesnocombo'
						}, {
							fieldLabel: lang['pokazaniya_k_provedeniyu_dupleksnogo_skanirovaniya_brahitsefalnyih_arteriy'],
							hiddenName: 'EvnPLDispProf_IsDoubleScan',
							xtype: 'swyesnocombo'
						}, {
							fieldLabel: lang['podozrenie_na_nalichie_tuberkuleza_hronicheskogo_zabolevaniya_legkih_ili_novoobrazovaniya_legkih'],
							hiddenName: 'EvnPLDispProf_IsTub',
							xtype: 'swyesnocombo'
						}, {
							fieldLabel: lang['pokazaniya_k_provedeniyu_ezofagogastroduodenoskopii'],
							hiddenName: 'EvnPLDispProf_IsEsophag',
							xtype: 'swyesnocombo'
						},
						win.ProphConsultGrid
						// win.NeedConsultGrid
					]
				}),
				// подраздел "Поведенческие факторы риска"
				new Ext.Panel({
					title: "Поведенческие факторы риска",
					layout: 'form',
					bodyStyle: 'padding: 5px',
					items: [{
							fieldLabel: lang['kurenie'],
							hiddenName: 'EvnPLDispProf_IsSmoking',
							xtype: 'swyesnocombo'
						}, {
							fieldLabel: lang['risk_pagubnogo_potrebleniya_alkogolya'],
							hiddenName: 'EvnPLDispProf_IsRiskAlco',
							xtype: 'swyesnocombo'
						}, {
							fieldLabel: lang['podozrenie_na_zavisimost_ot_alkogolya'],
							hiddenName: 'EvnPLDispProf_IsAlcoDepend',
							xtype: 'swyesnocombo'
						}, {
							fieldLabel: lang['nizkaya_fizicheskaya_aktivnost'],
							hiddenName: 'EvnPLDispProf_IsLowActiv',
							xtype: 'swyesnocombo'
						}, {
							fieldLabel: lang['neratsionalnoe_pitanie'],
							hiddenName: 'EvnPLDispProf_IsIrrational',
							xtype: 'swyesnocombo'
						}
					]
				}),
				// подраздел "Впервые выявленные заболевания" - грид
				win.EvnDiagDopDispFirstGrid,
				// подраздел "Значения параметров, потенциальных или имеющихся биологических факторов риска"
				new Ext.Panel({
					title: "Значения параметров, потенциальных или имеющихся биологических факторов риска",
					layout: 'form',
					bodyStyle: 'padding: 5px',
					items: [
					{
						name: 'systolic_blood_pressure',
						xtype: 'hidden'
						
					},
					{
						name: 'diastolic_blood_pressure',
						xtype: 'hidden'
					},
					{
						fieldLabel: lang['ad_mm_rt_st'],
						name:'systolic_and_diastolic',
						readOnly: true,
						xtype: 'textfield'
						
					},
					{
						fieldLabel: lang['gipotenzivnaya_terapiya'],
						hiddenName: 'EvnPLDispProf_IsHypoten',
						xtype: 'swyesnocombo'
					},
					{
						fieldLabel: lang['ves_kg'],
						name: 'person_weight',
						readOnly: true,
						xtype: 'numberfield'
					},
					{
						fieldLabel: lang['rost_sm'],
						name: 'person_height',
						readOnly: true,
						xtype: 'numberfield'
					},
					{
						fieldLabel: lang['indeks_massyi_tela_kg_m2'],
						listeners: {
							'change': function(field, newValue, oldValue) {
								// https://redmine.swan.perm.ru/issues/19835
								var base_form = win.EvnPLDispProfFormPanel.getForm();
								var CardioRiskType_Code = 0;

								if ( !Ext.isEmpty(newValue) || newValue == 0 ) {
									/*
										https://redmine.swan.perm.ru/issues/19835
										меньше 18.5 - низкий
										18.5-24.9 - обычный
										25-29,9 - повышенный
										30,0-34,9 - высокий
										35-39,9 - очень высокий
										больше 40 - чрезвычайно высокий
									*/
									if ( newValue < 18.5 ) {
										CardioRiskType_Code = 1;
									}
									else if ( newValue >= 18.5 && newValue < 25 ) {
										CardioRiskType_Code = 2;
									}
									else if ( newValue >= 25 && newValue < 30 ) {
										CardioRiskType_Code = 3;
									}
									else if ( newValue >= 30 && newValue < 35 ) {
										CardioRiskType_Code = 4;
									}
									else if ( newValue >= 35 && newValue < 40 ) {
										CardioRiskType_Code = 5;
									}
									else if ( newValue >= 40 ) {
										CardioRiskType_Code = 6;
									}

									if ( !Ext.isEmpty(CardioRiskType_Code) ) {
										var index = base_form.findField('CardioRiskType_id').getStore().findBy(function(rec) {
											return (rec.get('CardioRiskType_Code') == CardioRiskType_Code);
										});

										if ( index >= 0 ) {
											base_form.findField('CardioRiskType_id').setValue(base_form.findField('CardioRiskType_id').getStore().getAt(index).get('CardioRiskType_id'));
										}
									}
								}
							}
						},
						name: 'body_mass_index',
						readOnly: true,
						xtype: 'textfield'
					},
					{
						comboSubject: 'CardioRiskType',
						fieldLabel: lang['risk_serdechno-sosudistyih_zabolevaniy'],
						hiddenName: 'CardioRiskType_id',
						width: 150,
						xtype: 'swcommonsprcombo'
					},
					{
						fieldLabel: lang['okrujnost_talii_sm'],
						name: 'waist_circumference',
						readOnly: true,
						xtype: 'textfield'
					},
					{
						fieldLabel: lang['obschiy_holesterin_mmol_l'],
						name: 'total_cholesterol',
						readOnly: true,
						xtype: 'textfield'
					},
					{
						fieldLabel: lang['gipolipidemicheskaya_terapiya'],
						hiddenName: 'EvnPLDispProf_IsLipid',
						xtype: 'swyesnocombo'
					},
					{
						fieldLabel: lang['glyukoza_mmol_l'],
						name: 'glucose',
						readOnly: true,
						xtype: 'textfield'
					},
					{
						fieldLabel: lang['gipoglikemicheskaya_terapiya'],
						hiddenName: 'EvnPLDispProf_IsHypoglyc',
						xtype: 'swyesnocombo'
					},
					{
						fieldLabel: lang['podozrenie_na_hronicheskoe_neinfektsionnoe_zabolevanie_trebuyuschee_doobsledovaniya'],
						width: 300,
						hiddenName: 'Diag_id',
						xtype: 'swdiagcombo'
					},
					{
						fieldLabel: lang['vzyat_na_dispansernoe_nablyudenie'],
						hiddenName: 'EvnPLDispProf_IsDisp',
						xtype: 'swyesnocombo'
					},
					{
						comboSubject: 'NeedDopCure',
						fieldLabel: lang['nujdaetsya_v_dopolnitelnom_lechenii_obsledovanii'],
						hiddenName: 'NeedDopCure_id',
						width: 300,
						xtype: 'swcommonsprcombo'
					},
					/*{
						fieldLabel: lang['nujdaetsya_v_stats_spets_v_t_ch_vyisokotehnologichnom_dopolnitelnom_lechenii_obsledovanii'],
						hiddenName: 'EvnPLDispProf_IsStac',
						xtype: 'swyesnocombo'
					},*/
					{
						fieldLabel: lang['nujdaetsya_v_sanatorno-kurortnom_lechenii'],
						hiddenName: 'EvnPLDispProf_IsSanator',
						xtype: 'swyesnocombo'
					},
					// группбокс
					{
						autoHeight: true,
						style: 'padding: 0px;',
						title: lang['summarnyiy_serdechno-sosudistyiy_risk'],
						width: 500,
						items: [{
							border: false,
							layout: 'column',
							items: [{
								border: false,
								layout: 'form',
								width: 380,
								items: [{
									fieldLabel: lang['protsent_%'],
									minValue: 0,
									maxValue: 100,
									anchor: '-10',
									name: 'EvnPLDispProf_SumRick',
									xtype: 'numberfield'
								}]
							}, {
								border: false,
								layout: 'form',
								items: [{
									handler: function() {
										win.loadScoreField();
									},
									text: lang['rasschitat'],
									tooltip: lang['raschet_summarnogo_serdechno-sosudistogo_riska'],
									xtype: 'button'
								}]
							}]
						},
						{
							comboSubject: 'RiskType',
							fieldLabel: lang['tip_riska'],
							hiddenName: 'RiskType_id',
							xtype: 'swcommonsprcombo'
						}],
						xtype: 'fieldset'
					},
					{
						fieldLabel: lang['shkola_patsienta_provedena'],
						hiddenName: 'EvnPLDispProf_IsSchool',
						xtype: 'swyesnocombo'
					},
					{
						fieldLabel: lang['uglublennoe_profilakticheskoe_konsultirovanie_provedeno'],
						hiddenName: 'EvnPLDispProf_IsProphCons',
						xtype: 'swyesnocombo'
					},
					{
						fieldLabel: lang['gruppa_zdorovya'],
						hiddenName: 'HealthKind_id',
						listeners: {
							'change': function(combo, newValue, oldValue) {
								win.checkEvnPLDispProfIsSaved();
							}
						},
						loadParams: {params: {where: ' where HealthKind_Code in (1,2,3,6,7)'}},
						xtype: 'swhealthkindcombo'
					},
					{
						fieldLabel: lang['sluchay_profosmotra_zakonchen'],
						hiddenName: 'EvnPLDispProf_IsEndStage',
						xtype: 'swyesnocombo',
						listeners:{
							'select':function (combo, record) {
								win.verfGroup();
								win.checkIsNewOrderButton();
							},
							'change': function() {
								win.checkForCostPrintPanel();
							}
						}
					}]
				})
			],
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
		
		this.EvnPLDispProfFormPanel = new Ext.form.FormPanel({
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
					typeCode: 'int',
					useCommonFilter: true,
					width: 300,
					xtype: 'swpaytypecombo'
				}]
			},
				// информированное добровольное согласие
				win.DopDispInfoConsentPanel,
				// маршрутная карта
				win.EvnUslugaDispDopPanel,
				// основные результаты диспансеризации
				win.EvnPLDispProfMainResultsPanel,
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
							this.printEvnPLDispProf();
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
				{ name: 'EvnPLDispProf_id' },
				{ name: 'EvnPLDispProf_IsPaid' },
				{ name: 'EvnPLDispProf_IsNewOrder' },
				{ name: 'EvnPLDispProf_IndexRep' },
				{ name: 'EvnPLDispProf_IndexRepInReg' },
				{ name: 'accessType' },
				{ name: 'DispClass_id' },
				{ name: 'EvnPLDispProf_fid' },
				{ name: 'PayType_id' },
				{ name: 'Lpu_mid' },
				{ name: 'EvnPLDispProf_IsMobile' },
				{ name: 'EvnPLDispProf_IsOutLpu' },
				{ name: 'Person_id' },
				{ name: 'PersonEvn_id' },
				{ name: 'EvnPLDispProf_setDate' },
				{ name: 'EvnPLDispProf_consDate' },
				{ name: 'Server_id' },
				{ name: 'EvnPLDispProf_IsStenocard' },
				{ name: 'EvnPLDispProf_IsDoubleScan' },
				{ name: 'EvnPLDispProf_IsTub' },
				{ name: 'EvnPLDispProf_IsEsophag' },
				{ name: 'EvnPLDispProf_IsSmoking' },
				{ name: 'EvnPLDispProf_IsRiskAlco' },
				{ name: 'EvnPLDispProf_IsAlcoDepend' },
				{ name: 'EvnPLDispProf_IsLowActiv' },
				{ name: 'EvnPLDispProf_IsIrrational' },
				{ name: 'systolic_blood_pressure' },
				{ name: 'diastolic_blood_pressure' },
				{ name: 'person_weight' },
				{ name: 'person_height' },
				{ name: 'body_mass_index' },
				{ name: 'waist_circumference' },
				{ name: 'total_cholesterol' },
				{ name: 'glucose' },
				{ name: 'EvnPLDispProf_IsHypoten' },
				{ name: 'EvnPLDispProf_IsLipid' },
				{ name: 'EvnPLDispProf_IsHypoglyc' },
				{ name: 'Diag_id' },
				{ name: 'EvnPLDispProf_IsDisp' },
				{ name: 'NeedDopCure_id' },
				// { name: 'EvnPLDispProf_IsStac' },
				{ name: 'EvnPLDispProf_IsSanator' },
				{ name: 'EvnPLDispProf_SumRick' },
				{ name: 'RiskType_id' },
				{ name: 'EvnPLDispProf_IsSchool' },
				{ name: 'EvnPLDispProf_IsProphCons' },
				{ name: 'HealthKind_id' },
				{ name: 'EvnPLDispProf_IsEndStage' },
				{ name: 'CardioRiskType_id' },
				{ name: 'EvnCostPrint_setDT' },
				{ name: 'EvnCostPrint_Number' },
				{ name: 'EvnCostPrint_IsNoPrint' },
				{ name: 'EvnPLDispProf_IsSuspectZNO' },
				{ name: 'Diag_spid' },
				{ name: 'EvnPLDispProf_IsKKND' }
			]),
			url: '/?c=EvnPLDispProf&m=saveEvnPLDispProf'
		});
		
		Ext.apply(this, {
			items: [
				// паспортная часть человека
				win.PersonInfoPanel,
				win.EvnPLDispProfFormPanel
			],
			buttons: [{
				handler: function() {
					this.doSave(false);
				}.createDelegate(this),				
				iconCls: 'save16',
				id: 'EPLDPEF_SaveButton',
				onTabAction: function() {
					Ext.getCmp('EPLDPEF_PrintButton').focus(true, 200);
				},
				onShiftTabAction: function() {
					Ext.getCmp('EPLDPEF_IsFinishCombo').focus(true, 200);
				},
				tabIndex: 2406,
				text: BTN_FRMSAVE
			}, {
				handler: function() {
					this.printEvnPLDispProfPassport();
				}.createDelegate(this),
				iconCls: 'print16',
				id: 'EPLDPEF_PrintPassportButton',
				tabIndex: 2408,
				text: lang['pechat_pasporta_zdorovya']
			}, {
				hidden: true,
				handler: function() {
					this.printEvnPLDispProf();
				}.createDelegate(this),
				iconCls: 'print16',
				id: 'EPLDPEF_PrintButton',
				tabIndex: 2407,
				text: lang['pechat_kartyi_ucheta_profilakticheskih_meditsinskih_osmotrov']
			}, {
				hidden: getRegionNick() == 'kz',
				handler: function() {
					this.printKLU();
				}.createDelegate(this),
				iconCls: 'print16',
				id: 'EPLDPEF_PrintKLU',
				tabIndex: 2408,
				text: 'Печать КЛУ при ЗНО'
			}, {
				hidden: getRegionNick() != 'ekb',
				handler: function() {
					this.printOnko();
				}.createDelegate(this),
				iconCls: 'print16',
				id: 'EPLDPEF_PrintOnko',
				tabIndex: 2409,
				text: 'Печать выписки по онкологии'
			}, '-',
			HelpButton(this, -1),
			{
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'cancel16',
				id: 'EPLDPEF_CancelButton',
				tabIndex: 2410,
				text: BTN_FRMCANCEL
			}]
		});
		sw.Promed.swEvnPLDispProfEditWindow.superclass.initComponent.apply(this, arguments);
	},
	loadScoreField: function() {
		// расчёт поля SCORE
		var win = this;
		var base_form = this.EvnPLDispProfFormPanel.getForm();
		
		win.getLoadMask(lang['raschet_summarnogo_serdechno-sosudistogo_riska']).show();
		Ext.Ajax.request({
			callback: function(options, success, response) {
				win.getLoadMask().hide();
				if ( success ) {
					var response_obj = Ext.util.JSON.decode(response.responseText);

					if ( response_obj.SCORE ) {
						base_form.findField('EvnPLDispProf_SumRick').setValue(response_obj.SCORE);
					}
				}
				else {
					sw.swMsg.alert(lang['oshibka'], lang['oshibka_rascheta_summarnogo_serdechno-sosudistogo_riska']);
				}
			},
			params: {
				EvnPLDisp_id: base_form.findField('EvnPLDispProf_id').getValue()
			},
			url: '/?c=EvnUslugaDispDop&m=loadScoreField'
		});
	},
	checkIsNewOrderButton: function() {
		var win = this;
		var hasEvnUslugaAfterNewDVNDate = false;
		var newDVNDate = getNewDVNDate();
		var base_form = win.EvnPLDispProfFormPanel.getForm();
		win.evnUslugaDispDopGrid.getGrid().getStore().each(function (rec) {
			if (!Ext.isEmpty(rec.get('EvnUslugaDispDop_didDate')) && rec.get('EvnUslugaDispDop_didDate') >= newDVNDate) {
				hasEvnUslugaAfterNewDVNDate = true;
			}
		});

		if (
			getRegionNick().inlist(['astra', 'krasnoyarsk', 'krym', 'perm', 'vologda'])
			&& base_form.findField('EvnPLDispProf_IsNewOrder').getValue() != 2 // Для карты отсутствует признак «Переопределён набор услуг по новому приказу»;
			&& !Ext.isEmpty(newDVNDate)
			&& win.findById('EPLDP_EvnPLDispProf_consDate').getValue() < newDVNDate // Дата согласия меньше даты нового ДВН
			&& (
				hasEvnUslugaAfterNewDVNDate
				|| base_form.findField('EvnPLDispProf_IsEndStage').getValue() != 2 // В поле случай закончен установлено значение «Нет»
			)
		) {
			win.setIsNewOrderButton.show();
		} else {
			win.setIsNewOrderButton.hide();
		}
	},
	keys: [{
		alt: true,
		fn: function(inp, e) {
			var win = Ext.getCmp('EvnPLDispProfEditWindow');
			var tabbar = win.findById('EPLDPEF_EvnPLTabbar');

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
	printEvnPLDispProf: function(print_blank) {
		var win = this;
		if ('add' == win.action || 'edit' == win.action) {
			win.doSave({
				callback: win.printEvnPLDispProf_callback.createDelegate(win),
				print: true
			});
		} else if (win.action == 'view'){
			win.printEvnPLDispProf_callback();
		}
	},
	printEvnPLDispProf_callback: function() {
		var base_form = this.EvnPLDispProfFormPanel.getForm();
		var evn_pl_id = base_form.findField('EvnPLDispProf_id').getValue();
		var person_id = base_form.findField('Person_id').getValue();
		var evnpldispprof_setdate = base_form.findField('EvnPLDispProf_setDate').getValue();
		var evnpldispprof_year = new Date(evnpldispprof_setdate.replace(/(\d+).(\d+).(\d+)/, '$3/$2/$1')).getFullYear();

		var template = 'pan_DispCard_2015.rptdesign';
		if(base_form.findField('EPLDP_EvnPLDispProf_consDate').getValue() < new Date(2015,3,1))
		{
			template = 'pan_DispCard.rptdesign';
		}
		printBirt({
			'Report_FileName': template,
			'Report_Params': '&paramPerson=' + person_id + '&paramDispClass=5&paramYear=' + evnpldispprof_year,
			'Report_Format': 'pdf'
		});
	},
	printEvnPLDispProfPassport: function(print_blank) {
		var win = this;
		if ('add' == win.action || 'edit' == win.action) {
			win.doSave({
				callback: win.printEvnPLDispProfPassport_callback.createDelegate(win),
				print: true
			});
		} else if (win.action == 'view'){
			win.printEvnPLDispProfPassport_callback();
		}
	},
	printEvnPLDispProfPassport_callback: function() {
		var base_form = this.EvnPLDispProfFormPanel.getForm();
		var evn_pl_id = base_form.findField('EvnPLDispProf_id').getValue();
		var server_id = base_form.findField('Server_id').getValue();
		if(!getGlobalOptions().region.nick.inlist([ 'pskov', 'ufa' ])){
			var dialog_wnd = Ext.Msg.show({ //shorev: Добавил в рамках задачи https://redmine.swan.perm.ru/issues/26202
				title: lang['pechat_zabolevaniy'],
				msg:lang['pechatat_dannyie_v_razdel_10_ustanovlennyie_zabolevaniya'],
				buttons: {yes: "Да", no: "Нет", cancel: "Отмена"},
				fn: function(btn){
					if (btn == 'cancel') {
						return;
					}
					if(btn == 'yes'){ //Выводим диагнозы (printDiag=1)
						window.open('/?c=EvnPLDispProf&m=printEvnPLDispProfPassport&EvnPLDispProf_id=' + evn_pl_id + '&Server_id=' + server_id + '&printDiag=1', '_blank');
					}
					if(btn == 'no') { //Не выводим диагнозы (printDiag=0)
						window.open('/?c=EvnPLDispProf&m=printEvnPLDispProfPassport&EvnPLDispProf_id=' + evn_pl_id + '&Server_id=' + server_id + '&printDiag=0', '_blank');
					}
				}
			});
		}
		else{ //Для Уфы - всегда выводим диагнозы (printDiag=1)
			window.open('/?c=EvnPLDispProf&m=printEvnPLDispProfPassport&EvnPLDispProf_id=' + evn_pl_id + '&Server_id=' + server_id + '&printDiag=1', '_blank');
		}
	},
	printKLU: function() {
		var win = this;
		var base_form = this.EvnPLDispProfFormPanel.getForm();
		
		var print = function() {
			var evn_pl_id = base_form.findField('EvnPLDispProf_id').getValue();
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
		var base_form = this.EvnPLDispProfFormPanel.getForm();

		var print = function() {
			var evn_pl_id = base_form.findField('EvnPLDispProf_id').getValue();
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
	resizable: true,
	checkEvnPLDispProfIsSaved: function() {
		var base_form = this.EvnPLDispProfFormPanel.getForm();
		if (Ext.isEmpty(base_form.findField('EvnPLDispProf_id').getValue()) || !this.PersonFirstStageAgree) {
			// дисаблим все разделы кроме информированного добровольного согласия, а также основную кнопки сохранить и печать
			this.EvnUslugaDispDopPanel.collapse();
			this.EvnUslugaDispDopPanel.disable();
			this.EvnPLDispProfMainResultsPanel.collapse();
			this.EvnPLDispProfMainResultsPanel.disable();
			this.DispAppointPanel.collapse();
			this.DispAppointPanel.disable();
			this.buttons[0].hide();
			this.buttons[1].hide();
			this.buttons[2].hide();
			this.DopDispInfoConsentPanel.items.items[2].items.items[1].disable(); //Закрываем кнопку "Печать"
			return false;
		} else {
			this.EvnUslugaDispDopPanel.expand();
			this.EvnUslugaDispDopPanel.enable();
			this.EvnPLDispProfMainResultsPanel.expand();
			this.EvnPLDispProfMainResultsPanel.enable();
			if (
				( getRegionNick() == 'krym' ) || //182073
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
			if (this.action != 'view') {
				this.buttons[0].show();
			}
			this.buttons[1].show();
			this.buttons[2].show();
			this.DopDispInfoConsentPanel.items.items[2].items.items[1].enable(); //Открываем кнопку "Печать"
			return true;
		}
	},
	saveDopDispInfoConsent: function(btn, options) {
		var win = this;
		var btn = win.saveConsentButton;
		var base_form = win.EvnPLDispProfFormPanel.getForm();
		if ( typeof options != 'object' ) {
			options = new Object();
		}

		if ( btn.disabled || win.action == 'view' ) {
			return false;
		}

		if (win.blockSaveDopDispInfoConsent) {
			win.saveDopDispInfoConsentAfterLoad = true;
			return false;
		}

		btn.disable();

		var newDVNDate = getNewDVNDate();
		// для Уфы: Если в карте ДВН количество согласий (кол-во выбранных чекбоксов в поле "Согласие гражданина") для осмотров / исследований меньше 85%, то выводить сообщение "Количество осмотров\исследований составляет менее 85% от объема, установленного для данного возраста и пола. Ок", Сохранение отменить.
		if (
			getRegionNick().inlist(['pskov', 'ufa'])
			|| (getRegionNick() != 'ekb' && !Ext.isEmpty(newDVNDate) && win.findById('EPLDP_EvnPLDispProf_consDate').getValue() >= newDVNDate)
		) {
			// считаем количество помеченных согласий
			var
				kolvo = 0,
				kolvoAgree = 0,
				surveyAgree = false;

			// Определяем согласие/отказ по проф. осмотру в целом
			win.dopDispInfoConsentGrid.getGrid().getStore().each(function(rec) {
				if (!Ext.isEmpty(rec.get('SurveyType_Code')) && rec.get('SurveyType_Code').inlist([49]) && rec.get('DopDispInfoConsent_IsAgree') == true) {
					surveyAgree = true;
				}
			});

			if ( surveyAgree == true ) {
				win.dopDispInfoConsentGrid.getGrid().getStore().each(function(rec) {
					if (
						!Ext.isEmpty(rec.get('SurveyType_Code')) && rec.get('SurveyType_Code') != 49 && rec.get('DopDispInfoConsent_IsAgeCorrect') == 1
						&& (
							rec.get('DopDispInfoConsent_IsImpossible_disabled') == 'disabled'
							|| rec.get('DopDispInfoConsent_IsImpossible') == false
						)
					) {
						kolvo++;
						if (rec.get('DopDispInfoConsent_IsAgree') == true || rec.get('DopDispInfoConsent_IsEarlier') == true ) {
							kolvoAgree++;
						}
					}
				});
				if (kolvoAgree < Math.round(kolvo*0.85)) {
					btn.enable();
					sw.swMsg.alert(lang['oshibka'], lang['kolichestvo_osmotrov_issledovaniy_sostavlyaet_menee_85%_ot_obyema_ustanovlennogo_dlya_dannogo_vozrasta_i_pola']);
					return false;
				}
			}
		}

		if (
			getRegionNick() == 'adygeya'
			&& base_form.findField('PayType_id').getFieldValue('PayType_SysNick') == 'oms'
			&& win.findById('EPLDP_EvnPLDispProf_consDate').getValue() >= new Date(2020, 0, 1)
		) {
			var required = [];

			// Определяем согласие/отказ по диспансеризации в целом
			win.dopDispInfoConsentGrid.getGrid().getStore().each(function(rec) {
				if (!Ext.isEmpty(rec.get('SurveyType_Code')) && rec.get('SurveyType_Code').inlist([2, 19, 31]) && rec.get('DopDispInfoConsent_IsAgree') != true && rec.get('DopDispInfoConsent_IsEarlier') != true && rec.get('DopDispInfoConsent_IsImpossible') != true) {
					required.push(rec.get('SurveyType_Name'));
				}
			});

			if (required.length > 0) {
				btn.enable();
				sw.swMsg.alert('Ошибка', 'При профилактическом осмотре взрослого населения обязательно проведение следующих осмотров (исследований): <br> - ' + required.join('<br> - ') + '<br>Установите один из флагов: «Пройдено ранее», «Согласие гражданина» или «Невозможно по показаниям» для перечисленных осмотров');
				return false;
			}
		}

		win.getLoadMask(lang['sohranenie_informirovannogo_dobrovolnogo_soglasiya']).show();
		var base_form = win.EvnPLDispProfFormPanel.getForm();
		// берём все записи из грида и посылаем на сервер, разбираем ответ
		// на сервере создать саму карту EvnPLDispProf, если EvnPLDispProf_id не задано, сохранить её информ. согласие DopDispInfoConsent, вернуть EvnPLDispProf_id
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

		if ( Ext.isEmpty(win.findById('EPLDP_EvnPLDispProf_consDate').getValue()) ) {
			btn.enable();
			win.getLoadMask().hide();

			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					win.findById('EPLDP_EvnPLDispProf_consDate').focus(true);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});

			return false;
		}

		params.EvnPLDispProf_consDate = (typeof win.findById('EPLDP_EvnPLDispProf_consDate').getValue() == 'object' ? Ext.util.Format.date(win.findById('EPLDP_EvnPLDispProf_consDate').getValue(), 'd.m.Y') : win.findById('EPLDP_EvnPLDispProf_consDate').getValue());
		params.EvnPLDispProf_IsMobile = (base_form.findField('EvnPLDispProf_IsMobile').checked) ? true : null;
		params.EvnPLDispProf_IsOutLpu = (base_form.findField('EvnPLDispProf_IsOutLpu').checked) ? true : null;
		params.Lpu_mid = base_form.findField('Lpu_mid').getValue();
		params.PersonEvn_id = base_form.findField('PersonEvn_id').getValue();
		params.Person_id = base_form.findField('Person_id').getValue();
		params.Server_id = base_form.findField('Server_id').getValue();
		params.EvnPLDispProf_id = base_form.findField('EvnPLDispProf_id').getValue();
		params.EvnPLDispProf_fid = base_form.findField('EvnPLDispProf_fid').getValue();
		params.EvnPLDispProf_IsNewOrder = base_form.findField('EvnPLDispProf_IsNewOrder').getValue();
		params.DispClass_id = base_form.findField('DispClass_id').getValue();
		params.PayType_id = base_form.findField('PayType_id').getValue();

		params.DopDispInfoConsentData = Ext.util.JSON.encode(getStoreRecords( grid.getStore(), {
			exceptionFields: [
				'SurveyType_Name'
			]
		}));

		if ( !Ext.isEmpty(options.ignoreDVN) ) {
			params.ignoreDVN = 1;
		}

		if ( !Ext.isEmpty(options.AttachmentAnswer) ) {
			params.AttachmentAnswer = 1;
		}

		Ext.Ajax.request(
		{
			url: '/?c=EvnPLDispProf&m=saveDopDispInfoConsent',
			params: params,
			failure: function(response, opt)
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
					if ( !Ext.isEmpty(answer.Alert_Msg) ) {
						if (!Ext.isEmpty(answer.tip) && answer.tip=='AttachmentAnswer') {
						sw.swMsg.show({
							buttons: Ext.Msg.YESNO,
							fn: function(buttonId, text, obj) {
								if ( buttonId == 'yes' ) {
										options.AttachmentAnswer = 1;
										win.saveDopDispInfoConsent(btn, options);
									}
								},
								icon: Ext.MessageBox.QUESTION,
								msg: answer.Alert_Msg,
								title: langs(' Продолжить сохранение?')
							});
						}else if(Ext.isEmpty(answer.tip) && answer.tip!='AttachmentAnswer'){
							sw.swMsg.show({
								buttons: Ext.Msg.YESNO,
								fn: function (buttonId, text, obj) {
									if (buttonId == 'yes') {
									switch (true) {
										case (101 == answer.Error_Code):
											options.ignoreDVN = 1;
											break;
									}

									win.saveDopDispInfoConsent(btn, options);
								}
							},
							icon: Ext.MessageBox.QUESTION,
							msg: answer.Alert_Msg,
							title: lang['prodoljit_sohranenie']
						});
					}
					}
					else if (answer.success && answer.EvnPLDispProf_id > 0)
					{
						base_form.findField('EvnPLDispProf_id').setValue(answer.EvnPLDispProf_id);
						win.checkEvnPLDispProfIsSaved();
						// запускаем callback чтобы обновить грид в родительском окне
						win.callback();
						// обновляем грид
						grid.getStore().load({
							params: {
								EvnPLDispProf_id: answer.EvnPLDispProf_id
							}
						});

						win.loadForm(answer.EvnPLDispProf_id);
					}
				}
			}
		});
	},
	checkForCostPrintPanel: function() {
		var base_form = this.EvnPLDispProfFormPanel.getForm();

		this.CostPrintPanel.hide();
		base_form.findField('EvnCostPrint_setDT').setAllowBlank(true);
		base_form.findField('EvnCostPrint_Number').setContainerVisible(getRegionNick() == 'khak');
		base_form.findField('EvnCostPrint_IsNoPrint').setAllowBlank(true);

		// если справка уже печаталась и случай закрыт, отображаем раздел с данными справки
		if (base_form.findField('EvnPLDispProf_IsEndStage').getValue() == 2 && !Ext.isEmpty(base_form.findField('EvnCostPrint_setDT').getValue()) && getRegionNick().inlist(['perm', 'kz', 'ufa'])) {
			this.CostPrintPanel.show();
			// поля обязтаельные
			base_form.findField('EvnCostPrint_setDT').setAllowBlank(false);
			base_form.findField('EvnCostPrint_IsNoPrint').setAllowBlank(false);
		}
	},
	show: function() {
		sw.Promed.swEvnPLDispProfEditWindow.superclass.show.apply(this, arguments);
		
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

		var form = this.EvnPLDispProfFormPanel;
		form.getForm().reset();
		win.dopDispInfoConsentGrid.getGrid().getStore().removeAll();
		win.evnUslugaDispDopGrid.getGrid().getStore().removeAll();
		win.EvnDiagDopDispBeforeGrid.getGrid().getStore().removeAll();
		win.HeredityDiag.getGrid().getStore().removeAll();
		win.ProphConsultGrid.getGrid().getStore().removeAll();
		// win.NeedConsultGrid.getGrid().getStore().removeAll();
		win.DispAppointGrid.getGrid().getStore().removeAll();
		

		this.checkForCostPrintPanel();
		this.checkIsNewOrderButton();

		win.findById('EPLDP_EvnPLDispProf_consDate').setRawValue('');
		
		this.PersonFirstStageAgree = false; // Пациент не согласен на профосмотр
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
		
		if(arguments[0].parentForm && arguments[0].parentForm =='swPersonDispEditWindow'){
			this.parentForm = arguments[0].parentForm;
		}

		var base_form = this.EvnPLDispProfFormPanel.getForm();
		var EvnPLDispProf_id = base_form.findField('EvnPLDispProf_id').getValue();
		var person_id = base_form.findField('Person_id').getValue();
		var server_id = base_form.findField('Server_id').getValue();

		if (Ext.isEmpty(base_form.findField('PayType_id').getValue())) {
			base_form.findField('PayType_id').setFieldValue('PayType_SysNick', 'oms');
		}

		base_form.findField('EvnPLDispProf_RepFlag').hideContainer();

		//Проверяем возможность редактирования документа
		if (this.action === 'edit' && arguments[0].EvnPLDispProf_id) {
			Ext.Ajax.request({
				failure: function (response, options) {
					sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_zagruzke_dannyih_formyi'], function () {
						this.hide();
					}.createDelegate(this));
				},
				params: {
					Evn_id: arguments[0].EvnPLDispProf_id,
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
		var base_form = this.EvnPLDispProfFormPanel.getForm();
		var EvnPLDispProf_id = base_form.findField('EvnPLDispProf_id').getValue();
		var person_id = base_form.findField('Person_id').getValue();
		var server_id = base_form.findField('Server_id').getValue();
		var form = this.EvnPLDispProfFormPanel;
		
		switch (win.action) {
			case 'add':
				win.setTitle(WND_POL_EPLDPADD);
				break;
			case 'edit':
				win.setTitle(WND_POL_EPLDPEDIT);
				break;
			case 'view':
				win.setTitle(WND_POL_EPLDPVIEW);
				break;
		}

		base_form.findField('Diag_id').filterDate = null;
		base_form.findField('EvnPLDispProf_IsMobile').fireEvent('check', base_form.findField('EvnPLDispProf_IsMobile'), base_form.findField('EvnPLDispProf_IsMobile').getValue());
		
		// пока не сохранена карта (сохраняется при информационно добровольном согласии) нельзя редактировать разделы кроме согласия
		this.checkEvnPLDispProfIsSaved();
		
		inf_frame_is_loaded = false;

		this.PersonInfoPanel.load({ 
			Person_id: person_id, 
			Server_id: server_id, 
			callback: function() {
				win.getLoadMask().hide();
				inf_frame_is_loaded = true; 
				
				var sex_id = win.PersonInfoPanel.getFieldValue('Sex_id');
				var age = win.PersonInfoPanel.getFieldValue('Person_Age');
				var a_d_presurre=base_form.findField('systolic_blood_pressure').getValue()+"/"+base_form.findField('diastolic_blood_pressure').getValue();
				base_form.findField('systolic_and_diastolic').setValue(a_d_presurre);
				base_form.findField('Server_id').setValue(win.PersonInfoPanel.getFieldValue('Server_id'));
				base_form.findField('PersonEvn_id').setValue(win.PersonInfoPanel.getFieldValue('PersonEvn_id'));
				
				if (win.action != 'view') {
					win.enableEdit(true);
					win.evnUslugaDispDopGrid.setReadOnly(false);
					win.EvnDiagDopDispBeforeGrid.setReadOnly(false);
					win.HeredityDiag.setReadOnly(false);
					win.EvnDiagDopDispFirstGrid.setReadOnly(false);
					win.ProphConsultGrid.setReadOnly(false);
					//win.NeedConsultGrid.setReadOnly(false);
					win.DispAppointGrid.setReadOnly(false);

				} else {
						
					win.enableEdit(false);
					win.evnUslugaDispDopGrid.setReadOnly(true);
					win.EvnDiagDopDispBeforeGrid.setReadOnly(true);
					win.HeredityDiag.setReadOnly(true);
					win.EvnDiagDopDispFirstGrid.setReadOnly(true);
					win.ProphConsultGrid.setReadOnly(true);
					win.DispAppointGrid.setReadOnly(true);
				}

				Ext.getCmp('EPLDPEF_PrintKLU').hide();
				Ext.getCmp('EPLDPEF_PrintOnko').hide();

				if (!Ext.isEmpty(EvnPLDispProf_id)) {
					win.loadForm(EvnPLDispProf_id);
				}
				else {
					/*var index = base_form.findField('PayType_id').getStore().findBy(function(rec){return (rec.get('PayType_SysNick')=='oms')})
					log(index,'index')
					if ( index >= 0 ) {
						base_form.findField('PayType_id').setValue(base_form.findField('PayType_id').getStore().getAt(index).get('PayType_id'));
					}*/
					// Грузим текущую дату
					setCurrentDateTime({
						callback: function(date) {
							win.findById('EPLDP_EvnPLDispProf_consDate').fireEvent('change', win.findById('EPLDP_EvnPLDispProf_consDate'), date);
						},
						dateField: win.findById('EPLDP_EvnPLDispProf_consDate'),
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
	notLoadedParts: [],
	loadForm: function(EvnPLDispProf_id) {
		var win = this;
		var base_form = this.EvnPLDispProfFormPanel.getForm();
		win.getLoadMask(LOAD_WAIT).show();
		win.notLoadedParts = ['form', 'uslugagrid'];

		base_form.load({
			failure: function() {
				win.getLoadMask().hide();
				swEvnPLDispProfEditWindow.hide();
			},
			params: {
				EvnPLDispProf_id: EvnPLDispProf_id,
				archiveRecord: win.archiveRecord
			},
			success: function() {
				win.getLoadMask().hide();
				
				if ( base_form.findField('accessType').getValue() == 'view' ) {
					win.action = 'view';
					win.enableEdit(false);
				}

				if ( getRegionNick() == 'perm' && base_form.findField('EvnPLDispProf_IsPaid').getValue() == 2 && parseInt(base_form.findField('EvnPLDispProf_IndexRepInReg').getValue()) > 0 ) {
					base_form.findField('EvnPLDispProf_RepFlag').showContainer();

					if ( parseInt(base_form.findField('EvnPLDispProf_IndexRep').getValue()) >= parseInt(base_form.findField('EvnPLDispProf_IndexRepInReg').getValue()) ) {
						base_form.findField('EvnPLDispProf_RepFlag').setValue(true);
					}
					else {
						base_form.findField('EvnPLDispProf_RepFlag').setValue(false);
					}
				}


				Ext.getCmp('EPLDPEF_PrintKLU').show();
				if (getRegionNick() == 'ekb') {
					Ext.getCmp('EPLDPEF_PrintOnko').show();
				}

				if (base_form.findField('EvnPLDispProf_IsSuspectZNO').getValue() == 2) {
					Ext.getCmp('EPLDPEF_PrintKLU').enable();
					Ext.getCmp('EPLDPEF_PrintOnko').enable();
				} else {
					Ext.getCmp('EPLDPEF_PrintKLU').disable();
					Ext.getCmp('EPLDPEF_PrintOnko').disable();
				}

				win.checkForCostPrintPanel();
				win.checkIsNewOrderButton();

				// грузим грид услуг
				win.evnUslugaDispDopGrid.loadData({
					params: { EvnPLDispProf_id: EvnPLDispProf_id, object: 'EvnPLDispProf' }, globalFilters: { EvnPLDispProf_id: EvnPLDispProf_id }, noFocusOnLoad: true
				});
				// и все остальные гриды тоже
				win.EvnDiagDopDispBeforeGrid.loadData({
					params: { EvnPLDisp_id: EvnPLDispProf_id, object: 'EvnPLDispProf', DeseaseDispType_id: 1, PersonEvn_id: base_form.findField('PersonEvn_id').getValue(), Server_id: base_form.findField('Server_id').getValue() }, globalFilters: { EvnPLDisp_id: EvnPLDispProf_id, DeseaseDispType_id: 1 }, noFocusOnLoad: true
				});
				win.HeredityDiag.loadData({
					params: { EvnPLDisp_id: EvnPLDispProf_id, object: 'EvnPLDispProf' }, globalFilters: { EvnPLDisp_id: EvnPLDispProf_id }, noFocusOnLoad: true
				});
				win.EvnDiagDopDispFirstGrid.loadData({
					params: { EvnPLDisp_id: EvnPLDispProf_id, object: 'EvnPLDispProf', DeseaseDispType_id: 2, PersonEvn_id: base_form.findField('PersonEvn_id').getValue(), Server_id: base_form.findField('Server_id').getValue() }, globalFilters: { EvnPLDisp_id: EvnPLDispProf_id, DeseaseDispType_id: 2 }, noFocusOnLoad: true
				});
				win.ProphConsultGrid.loadData({
					params: { EvnPLDisp_id: EvnPLDispProf_id, object: 'EvnPLDispProf' }, globalFilters: { EvnPLDisp_id: EvnPLDispProf_id }, noFocusOnLoad: true
				});
				/*win.NeedConsultGrid.loadData({
					params: { EvnPLDisp_id: EvnPLDispProf_id, object: 'EvnPLDispProf' }, globalFilters: { EvnPLDisp_id: EvnPLDispProf_id }, noFocusOnLoad: true
				});*/
				win.DispAppointGrid.loadData({
					params: { EvnPLDisp_id: EvnPLDispProf_id, object: 'EvnPLDispProf' }, globalFilters: { EvnPLDisp_id: EvnPLDispProf_id }, noFocusOnLoad: true
				});

				if (Ext.isEmpty(base_form.findField('PayType_id').getValue())) {
					base_form.findField('PayType_id').setFieldValue('PayType_SysNick', 'oms');
				}
		
				var a_d_presurre=base_form.findField('systolic_blood_pressure').getValue()+"/"+base_form.findField('diastolic_blood_pressure').getValue();
				base_form.findField('systolic_and_diastolic').setValue(a_d_presurre);
				
				var diag_combo = base_form.findField('Diag_id');
				var diag_id = diag_combo.getValue();
				if (!Ext.isEmpty(diag_id)) {
					diag_combo.getStore().load({
						params: { where: "where Diag_id = " + diag_id },
						callback: function(data) {
							diag_combo.getStore().each(function(record) {
								if ( record.get('Diag_id') == diag_id ) {
									diag_combo.fireEvent('select', diag_combo, record, 0);
								}
							});
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
				
				base_form.findField('EvnPLDispProf_IsMobile').fireEvent('check', base_form.findField('EvnPLDispProf_IsMobile'), base_form.findField('EvnPLDispProf_IsMobile').getValue());

				win.findById('EPLDP_EvnPLDispProf_consDate').setValue(base_form.findField('EvnPLDispProf_consDate').getValue());
				win.findById('EPLDP_EvnPLDispProf_consDate').fireEvent('change', win.findById('EPLDP_EvnPLDispProf_consDate'), win.findById('EPLDP_EvnPLDispProf_consDate').getValue());
				
				base_form.findField('Diag_spid').setContainerVisible(base_form.findField('EvnPLDispProf_IsSuspectZNO').getValue() == 2);
				base_form.findField('Diag_spid').setAllowBlank(!getRegionNick().inlist([ 'astra', 'perm' ]) || base_form.findField('EvnPLDispProf_IsSuspectZNO').getValue() != 2);
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

				var index = win.notLoadedParts.indexOf('form');
				if (index !== -1) win.notLoadedParts.splice(index, 1);
			},
			url: '/?c=EvnPLDispProf&m=loadEvnPLDispProfEditForm'
		});
	},
	title: WND_POL_EPLDPADD,
	width: 800
});
