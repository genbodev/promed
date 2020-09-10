/**
* swEvnPLDispDop13EditWindow - окно редактирования/добавления талона по дополнительной диспансеризации
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
* @comment		Префикс для id компонентов EPLDD13EF (EvnPLDispDop13EditForm)
*
*/
/*NO PARSE JSON*/
sw.Promed.swEvnPLDispDop13EditWindow = Ext.extend(sw.Promed.BaseForm, {
	action: 'add',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	collapsible: true,
	/* */
	codeRefresh: true,
	objectName: 'swEvnPLDispDop13EditWindow',
	objectSrc: '/jscore/Forms/Polka/swEvnPLDispDop13EditWindow.js',
	draggable: true,
	verfGroup:function(){
		var wins = this;
		var base_form = wins.EvnPLDispDop13FormPanel.getForm();
		if ( base_form.findField('EvnPLDispDop13_IsEndStage').getValue() == 2 ) {
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
			options = {};
		}
		
		var win = this;
		var EvnPLDispDop13_form = win.EvnPLDispDop13FormPanel;

		var base_form = win.EvnPLDispDop13FormPanel.getForm();

		if ( !EvnPLDispDop13_form.getForm().isValid() )
		{
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					EvnPLDispDop13_form.getFirstInvalidEl().focus(false);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		if ( Ext.isEmpty(win.findById('EPLDD13_EvnPLDispDop13_consDate').getValue()) ) {
			win.getLoadMask().hide();

			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					win.findById('EPLDD13_EvnPLDispDop13_consDate').focus(true);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});

			return false;
		}

		if (
			getRegionNick() == 'adygeya'
			&& base_form.findField('EPLDD13EF_EvnPLDispDop13_IsSuspectZNO').getValue() == 2
			&& base_form.findField('HealthKind_id').getValue() == 1
		) {
			sw.swMsg.alert('Ошибка', 'Нельзя выбрать I группу здоровья при подозрении на ЗНО.');
			return false;
		}

		if (
			getRegionNick() == 'pskov'
			&& base_form.findField('EvnPLDispDop13_IsTwoStage').getValue() == 2
			&& (
				win.DispAppointGrid.getGrid().getStore().getCount() == 0
				|| (win.DispAppointGrid.getGrid().getStore().getCount() == 1 && Ext.isEmpty(win.DispAppointGrid.getGrid().getStore().getAt(0).get('DispAppoint_id')))
			)
		) {
			sw.swMsg.alert('Ошибка', 'Раздел «Назначения» должен содержать хотя бы одну запись, так как пациент направлен на 2 этап диспансеризации.');
			return false;
		}
		else if (
			!getRegionNick().inlist(['kz','pskov']) && !Ext.isEmpty(base_form.findField('HealthKind_id').getValue())
			&& base_form.findField('HealthKind_id').getValue() != 1 && base_form.findField('HealthKind_id').getValue() != 2
			&& (
				win.DispAppointGrid.getGrid().getStore().getCount() == 0
				|| (win.DispAppointGrid.getGrid().getStore().getCount() == 1 && Ext.isEmpty(win.DispAppointGrid.getGrid().getStore().getAt(0).get('DispAppoint_id')))
			)
		) {
			sw.swMsg.alert('Ошибка', 'Раздел «Назначения» должен содержать хотя бы одну запись, так как указана группа здоровья IIIа или IIIб.');
			return false;
		}
		
		//  на второй этап диспансеризации могут быть переведены пациенты, окончившие первый этап и имеющие группу здоровья - 3;
		if ( base_form.findField('EvnPLDispDop13_IsTwoStage').getValue() == 2 ) {
			var
				HealthKindArray = [2,3,6,7],
				HealthKindList = "II или III (IIIа, IIIб)";

			if ( getRegionNick().inlist([ 'astra', 'perm', 'vologda' ]) ) {
				HealthKindArray.push(1);
				HealthKindList = 'I, ' + HealthKindList;
			}
			else if ( getRegionNick().inlist([ 'ekb', 'kareliya', 'pskov' ]) ) {
				HealthKindArray.push(1);
				HealthKindList = 'I (в случае необходимости), ' + HealthKindList;
			}

			if ( base_form.findField('EvnPLDispDop13_IsEndStage').getValue() != 2 || !base_form.findField('HealthKind_id').getValue().inlist(HealthKindArray) ) {
				sw.swMsg.alert('Ошибка', 'На второй этап диспансеризации могут быть переведены только пациенты, окончившие первый этап и имеющие группу здоровья ' + HealthKindList + '.');
				return false;
			}
			else if ( getRegionNick().inlist([ 'ekb', 'kareliya' ]) && base_form.findField('HealthKind_id').getValue() == 1 && !options.ignoreFirstHealthKind ) {
				sw.swMsg.show({
					buttons: Ext.Msg.YESNO,
					fn: function (buttonId, text, obj) {
						if (buttonId == 'yes') {
							options.ignoreFirstHealthKind = true;
							win.doSave(options);
						}
					},
					icon: Ext.MessageBox.QUESTION,
					msg: 'Действительно отправить пациента с I группой здоровья на 2-й этап диспансеризации?',
					title: 'Вопрос'
				});
				return false;
			}
		}

		var
			anketSurveyExists = false,
			EvnUslugaDispDop_terDate,
			EvnUslugaDispDop_anketDate,
			indivUglProfConsultOrGroupProfConsult_didDate,
			hasEvnUslugaAfterNewDVNDate = false,
			newDVNDate = getNewDVNDate();

		// Вытаскиваем дату осмотра врачом терапевтом
		this.evnUslugaDispDopGrid.getGrid().getStore().each(function(rec) {
			if ( !Ext.isEmpty(rec.get('EvnUslugaDispDop_didDate')) ) {
				if ( rec.get('SurveyType_Code') == 19 ) {
					EvnUslugaDispDop_terDate = rec.get('EvnUslugaDispDop_didDate');
				}
				if ( rec.get('SurveyType_Code') == 2 ) {
					EvnUslugaDispDop_anketDate = rec.get('EvnUslugaDispDop_didDate');
				}
				if ( rec.get('SurveyType_Code') == 47 ) {
					indivUglProfConsultOrGroupProfConsult_didDate = rec.get('EvnUslugaDispDop_didDate');
				}
				if (rec.get('EvnUslugaDispDop_didDate') >= newDVNDate) {
					hasEvnUslugaAfterNewDVNDate = true;
				}
			}
		});

		if (
			getRegionNick().inlist(['astra', 'krasnoyarsk', 'krym', 'perm', 'vologda'])
			&& base_form.findField('EvnPLDispDop13_IsNewOrder').getValue() != 2 // Для карты отсутствует признак «Переопределён набор услуг по новому приказу»;
			&& !Ext.isEmpty(newDVNDate)
			&& win.findById('EPLDD13_EvnPLDispDop13_consDate').getValue() < newDVNDate // Дата согласия меньше даты нового ДВН
			&& base_form.findField('EvnPLDispDop13_IsEndStage').getValue() == 2 // В поле случай закончен установлено значение «Да»
			&& hasEvnUslugaAfterNewDVNDate // В маршрутной карте есть хотя бы один осмотр / исследование с датой выполнения больше или равной даты нового ДВН
		) {
			sw.swMsg.alert('Ошибка', 'По требованиям ТФОМС случаи, законченные после ' + newDVNDate.format('d.m.Y') + ' должны содержать перечень осмотров / исследований в соответствии с приказом 124н. Выполните переопределение перечня осмотров / исследований, нажав на кнопку «Услуги по 124н» в разделе Информированное добровольное согласие.');
			return false;
		}

		win.dopDispInfoConsentGrid.getGrid().getStore().each(function (rec) {
			if ( !Ext.isEmpty(rec.get('SurveyType_Code')) && rec.get('SurveyType_Code') == 2 ) {
				anketSurveyExists = true;
			}
		});

		var xdate3 = new Date(2015,4,1);
		if ( getRegionNick().inlist([ 'ufa' ]) && base_form.findField('DispClass_id').getValue() != 2 ) {
			// для Уфы: Если проставляем "Случай диспансеризации 1 этап закончен: ДА" и если количество заведенных (сохраненных) осмотров / исследований меньше, чем указано для данного пола/возраста меньше 85%, то выводить сообщение "Количество сохраненных осмотров\исследований составляет менее 85% от объема, установленного для данного возраста и пола. Ок", Сохранение отменить.
			if (true || win.findById('EPLDD13_EvnPLDispDop13_consDate').getValue() < xdate3) { // вернул контроль refs #129853
				if (base_form.findField('EvnPLDispDop13_IsEndStage').getValue() == 2) {
					// считаем количество сохраненных осмотров/исследований
					var kolvo = 0;
					var kolvoAgree = 0;
					var kolvoEarlier = 0;

					win.dopDispInfoConsentGrid.getGrid().getStore().each(function (rec) {
						if (!Ext.isEmpty(rec.get('SurveyType_Code')) && !rec.get('SurveyType_Code').inlist([1, 48])) {
							kolvo++;

							if (rec.get('DopDispInfoConsent_IsEarlier') == true) {
								kolvoEarlier++;
							}
						}
					});

					win.evnUslugaDispDopGrid.getGrid().getStore().each(function (rec) {
						if (!Ext.isEmpty(rec.get('DopDispInfoConsent_id'))) {
							if (!Ext.isEmpty(rec.get('EvnUslugaDispDop_didDate'))) {
								kolvoAgree++;
							}
						}
					});

					if (kolvoAgree + kolvoEarlier < Math.round(kolvo * 0.85)) {
						sw.swMsg.alert('Ошибка', 'Количество сохраненных осмотров / исследований составляет менее 85% от объема, установленного для данного возраста и пола.');
						return false;
					}
				}
			}
		} else if ( getRegionNick().inlist([ 'buryatiya' ]) ) {
			// нет каких либо ограничений по количеству заведенных услуг
		} else if ( getRegionNick().inlist([ 'kareliya' ]) && base_form.findField('DispClass_id').getValue() != 2 ) {
			var xdate2 = new Date(2015,3,1);
			if (win.findById('EPLDD13_EvnPLDispDop13_consDate').getValue() >= xdate2) {
				if (base_form.findField('EvnPLDispDop13_IsEndStage').getValue() == 2) {
					var EvnUslugaDispDop_anketDate;

					// Вытаскиваем дату осмотра врачом терапевтом
					win.evnUslugaDispDopGrid.getGrid().getStore().each(function(rec) {
						if ( !Ext.isEmpty(rec.get('EvnUslugaDispDop_didDate')) ) {
							if ( rec.get('SurveyType_Code') == 2 ) {
								EvnUslugaDispDop_anketDate = rec.get('EvnUslugaDispDop_didDate');
							}
						}
					});

					// считаем количество сохраненных осмотров/исследований
					var kolvo = 0;
					var kolvoAgree = 0;
					win.dopDispInfoConsentGrid.getGrid().getStore().each(function (rec) {
						if (!Ext.isEmpty(rec.get('SurveyType_Code')) && !rec.get('SurveyType_Code').inlist([1, 48])) {
							kolvo++;
						}
					});
					win.evnUslugaDispDopGrid.getGrid().getStore().each(function (rec) {
						if (!Ext.isEmpty(rec.get('DopDispInfoConsent_id'))) {
							if (!Ext.isEmpty(rec.get('EvnUslugaDispDop_didDate')) && rec.get('DopDispInfoConsent_IsEarlier') != 2 && (anketSurveyExists == false || rec.get('EvnUslugaDispDop_didDate') >= EvnUslugaDispDop_anketDate)) {
								kolvoAgree++;
							}
						}
					});
					if (kolvoAgree < this.count85Percent) { // для всех регионов
						if (getRegionNick() == 'kareliya') { // для карелии только предупреждение
							if (!options.ignoreCheckKolvo85) {
								sw.swMsg.show({
									buttons: Ext.Msg.YESNO,
									fn: function (buttonId, text, obj) {
										if (buttonId == 'yes') {
											options.ignoreCheckKolvo85 = true;
											win.doSave(options);
										}
									},
									icon: Ext.MessageBox.QUESTION,
									msg: 'Количество заведенных осмотров / исследований составляет менее 85% от общего объема обследований. Сохранить?',
									title: 'Вопрос'
								});
								return false;
							}
						} else { // для остальных ошибка
							sw.swMsg.alert('Ошибка', 'Количество заведенных осмотров / исследований составляет менее 85% от общего объема обследований, что недостаточно для закрытия карты диспансеризации взрослого населения.');
							return false;
						}
					}
				}
			}
		} else if ( getRegionNick().inlist([ 'astra' ]) && base_form.findField('DispClass_id').getValue() != 2 ) {
			var xdate2 = new Date(2015,3,1);
			if (win.findById('EPLDD13_EvnPLDispDop13_consDate').getValue() >= xdate2) {
				if (base_form.findField('EvnPLDispDop13_IsEndStage').getValue() == 2) {
					// считаем количество сохраненных осмотров/исследований
					var kolvo = 0;
					var kolvoAgree = 0;
					win.evnUslugaDispDopGrid.getGrid().getStore().each(function (rec) {
						if (!Ext.isEmpty(rec.get('DopDispInfoConsent_id'))) {
							kolvo++;
							if (!Ext.isEmpty(rec.get('EvnUslugaDispDop_didDate'))) {
								kolvoAgree++;
							}
						}
					});

					if (kolvoAgree < this.count85Percent) {
						sw.swMsg.alert('Ошибка', 'Количество заведенных осмотров/исследований в маршрутной карте недостаточно для проведения диспансеризации.');
						return false;
					}
				}
			}
		} else if ( getRegionNick().inlist([ 'penza' ]) && base_form.findField('DispClass_id').getValue() != 2 ) {
			if (!options.ignoreCheck85 && base_form.findField('EvnPLDispDop13_IsEndStage').getValue() == 2) {
				// считаем количество сохраненных осмотров/исследований
				var kolvo = 0;
				var kolvoAgree = 0;
				var kolvkoUzi = false;
				var kolvoAgreeUzi = false;
				win.evnUslugaDispDopGrid.getGrid().getStore().each(function (rec) {
					if (!Ext.isEmpty(rec.get('DopDispInfoConsent_id'))) {
						if (rec.get('SurveyType_Code').inlist([94,95,98,99,100])) {
							kolvkoUzi = true;
						} else {
							kolvo++;
						}
						if (!Ext.isEmpty(rec.get('EvnUslugaDispDop_didDate'))) {
							if (rec.get('SurveyType_Code').inlist([94,95,98,99,100])) {
								kolvoAgreeUzi = true;
							} else {
								kolvoAgree++;
							}
						}
					}
				});
				if (kolvkoUzi) {
					kolvo++;
				}
				if (kolvoAgreeUzi) {
					kolvoAgree++;
				}
				if (kolvoAgree < this.count85Percent) {
					sw.swMsg.show({
						buttons: Ext.Msg.YESNO,
						fn: function (buttonId, text, obj) {
							if (buttonId == 'yes') {
								options.ignoreCheck85 = true;
								win.doSave(options);
							}
						},
						icon: Ext.MessageBox.QUESTION,
						msg: 'Общее количество указанных в карте осмотров/исследований составляет менее 85% от установленного для данного пола и возраста пациента. Сумма к оплате случая будет рассчитана как сумма тарифов услуг, оказанных в рамках  диспансеризации. Сохранить?',
						title: 'Вопрос'
					});
					return false;
				}
			}
		} else if ( getRegionNick().inlist([ 'ekb' ]) && base_form.findField('DispClass_id').getValue() != 2 ) {
			var xdate2 = new Date(2015,3,1);
			if (win.findById('EPLDD13_EvnPLDispDop13_consDate').getValue() >= xdate2) {
				if (!options.ignoreCheck85 && base_form.findField('EvnPLDispDop13_IsEndStage').getValue() == 2) {
					// считаем количество осмотров/исследований
					var kolvo = 0;
					var kolvoProf = 0;
					win.dopDispInfoConsentGrid.getGrid().getStore().each(function (rec) {
						if (!Ext.isEmpty(rec.get('SurveyType_Code')) && !rec.get('SurveyType_Code').inlist([1, 48])) {
							kolvo++;

							// количество имеющих соответствие с профосмотром
							if (rec.get('SurveyType_Code').inlist([2,3,4,5,6,7,8,9,14,16,17,19,21,31,96,97])) {
								kolvoProf++;
							}
						}
					});

					var kolvoEarlier = 0;
					var kolvoAgree = 0;
					win.evnUslugaDispDopGrid.getGrid().getStore().each(function (rec) {
						if (!Ext.isEmpty(rec.get('DopDispInfoConsent_id'))) {
							if (!Ext.isEmpty(rec.get('EvnUslugaDispDop_didDate'))) {
								if (rec.get('DopDispInfoConsent_IsEarlier') == 2) {
									kolvoEarlier++;
								} else {
									kolvoAgree++;
								}
							}
						}
					});

					if (kolvoAgree + kolvoEarlier >= this.count85Percent && kolvoEarlier > 0 && kolvoEarlier >= Math.round(kolvo * 0.15)) {
						sw.swMsg.show({
							buttons: Ext.Msg.YESNO,
							fn: function (buttonId, text, obj) {
								if (buttonId == 'yes') {
									options.ignoreCheck85 = true;
									win.doSave(options);
								}
							},
							icon: Ext.MessageBox.QUESTION,
							msg: 'Общее количество осмотров/исследований с пометкой  «Пройдено ранее» составляет более 15%. Оплата случая диспансеризации будет произведена только за фактические выполненные услуги. Сохранить?',
							title: 'Вопрос'
						});
						return false;
					} else if (kolvoAgree + kolvoEarlier < this.count85Percent && kolvoAgree + kolvoEarlier >= Math.round(kolvoProf * 0.85)) {
						sw.swMsg.show({
							buttons: Ext.Msg.YESNO,
							fn: function (buttonId, text, obj) {
								if (buttonId == 'yes') {
									options.ignoreCheck85 = true;
									win.doSave(options);
								}
							},
							icon: Ext.MessageBox.QUESTION,
							msg: 'Общее количество осмотров/исследований составляет менее 85%. Данный случай диспансеризации будет оплачен, как профилактический медицинский осмотр. Сохранить?',
							title: 'Вопрос'
						});
						return false;
					} else if (kolvoAgree + kolvoEarlier < this.count85Percent) {
						sw.swMsg.alert('Ошибка', 'Количество заведенных осмотров / исследований недостаточно для закрытия карты диспансеризации взрослого населения.');
						return false;
					}
				}
			} else {
				// Для Екатеринбруга При сохранении карты ДВН, Если количество заведенных (сохраненных) осмотров / исследований меньше, чем количество осмотров /исследований положенных для данного пациента (использовать количество  из информированного согласия) * 0,84 , то выводить сообщение "Количество сохраненных осмотров\исследований составляет менее 84% от объема, установленного для данного возраста и пола. Ок", Сохранение отменить.
				if (base_form.findField('EvnPLDispDop13_IsEndStage').getValue() == 2) {
					// считаем количество сохраненных осмотров/исследований
					var kolvo = 0;
					var kolvoAgree = 0;
					var kolvoEarlier = 0;

					win.dopDispInfoConsentGrid.getGrid().getStore().each(function (rec) {
						if (!Ext.isEmpty(rec.get('SurveyType_Code')) && !rec.get('SurveyType_Code').inlist([1, 48])) {
							kolvo++;

							if (rec.get('DopDispInfoConsent_IsEarlier') == true) {
								kolvoEarlier++;
							}
						}
					});

					win.evnUslugaDispDopGrid.getGrid().getStore().each(function (rec) {
						if (!Ext.isEmpty(rec.get('DopDispInfoConsent_id'))) {
							if (!Ext.isEmpty(rec.get('EvnUslugaDispDop_didDate'))) {
								kolvoAgree++;
							}
						}
					});

					if (kolvoAgree + kolvoEarlier < Math.round(kolvo * 0.84)) {
						sw.swMsg.alert('Ошибка', 'Количество сохраненных осмотров / исследований составляет менее 84% от объема, установленного для данного возраста и пола.');
						return false;
					}
				}
			}
		} else if ( getRegionNick().inlist([ 'pskov' ]) && base_form.findField('DispClass_id').getValue() != 2 ) {
			var xdate3 = new Date(2015,4,1);
			if (win.findById('EPLDD13_EvnPLDispDop13_consDate').getValue() < xdate3) {
				if (base_form.findField('EvnPLDispDop13_IsEndStage').getValue() == 2) {
					// считаем количество сохраненных осмотров/исследований
					var kolvo = 0;
					var kolvoAgree = 0;
					var kolvoEarlier = 0;

					win.dopDispInfoConsentGrid.getGrid().getStore().each(function (rec) {
						if (!Ext.isEmpty(rec.get('SurveyType_Code')) && !rec.get('SurveyType_Code').inlist([1, 48])) {
							kolvo++;

							if (rec.get('DopDispInfoConsent_IsEarlier') == true) {
								kolvoEarlier++;
							}
						}
					});

					win.evnUslugaDispDopGrid.getGrid().getStore().each(function (rec) {
						if (!Ext.isEmpty(rec.get('DopDispInfoConsent_id'))) {
							if (!Ext.isEmpty(rec.get('EvnUslugaDispDop_didDate'))) {
								kolvoAgree++;
							}
						}
					});

					if (kolvoAgree + kolvoEarlier < this.count85Percent) {
						sw.swMsg.alert('Ошибка', 'Количество сохраненных осмотров / исследований составляет менее 85% от объема, установленного для данного возраста и пола.');
						return false;
					}
				}
			} else if (win.findById('EPLDD13_EvnPLDispDop13_consDate').getValue() >= xdate3) {
				if (!options.ignoreCheck85 && base_form.findField('EvnPLDispDop13_IsEndStage').getValue() == 2) {
					// считаем количество осмотров/исследований
					var kolvo = 0;
					var kolvoProf = 0;
					win.dopDispInfoConsentGrid.getGrid().getStore().each(function (rec) {
						if (!Ext.isEmpty(rec.get('SurveyType_Code')) && !rec.get('SurveyType_Code').inlist([1, 48])) {
							if (rec.get('DopDispInfoConsent_IsImpossible') == 'hidden' || rec.get('DopDispInfoConsent_IsAgree') == true || rec.get('DopDispInfoConsent_IsEarlier') == true) {
								kolvo++;

								// количество имеющих соответствие с профосмотром
								if (rec.get('SurveyType_Code').inlist([2, 3, 4, 5, 6, 7, 8, 9, 14, 16, 17, 19, 21, 31, 96, 97])) {
									kolvoProf++;
								}
							}
						}
					});

					var kolvoEarlier = 0;
					var kolvoAgree = 0;
					win.evnUslugaDispDopGrid.getGrid().getStore().each(function (rec) {
						if (!Ext.isEmpty(rec.get('DopDispInfoConsent_id'))) {
							if (!Ext.isEmpty(rec.get('EvnUslugaDispDop_didDate'))) {
								if (!Ext.isEmpty(EvnUslugaDispDop_terDate) && rec.get('EvnUslugaDispDop_didDate') < EvnUslugaDispDop_terDate.add('D', -30)) {
									kolvoEarlier++;
								} else {
									kolvoAgree++;
								}
							}
						}
					});

					var TdKolvo = this.count85Percent;
					if (
						win.findById('EPLDD13_EvnPLDispDop13_consDate').getValue() >= getNewDVNDate()
						|| base_form.findField('EvnPLDispDop13_IsNewOrder').getValue() == 2
					) {
						TdKolvo = Math.round(kolvo * 0.85);
					}

					if (kolvoAgree + kolvoEarlier >= TdKolvo && kolvoEarlier >= Math.round(kolvo * 0.15)) {
						sw.swMsg.show({
							buttons: Ext.Msg.YESNO,
							fn: function (buttonId, text, obj) {
								if (buttonId == 'yes') {
									options.ignoreCheck85 = true;
									win.doSave(options);
								}
							},
							icon: Ext.MessageBox.QUESTION,
							msg: 'Общее количество осмотров/исследований с датой ранее, чем 30 календарных дней до даты осмотра врача-терапевта составляет более 15%. Оплата случая диспансеризации будет произведена только за услуги, выполненные в период 30 календарных дней до даты осмотра врача-терапевта. Сохранить?',
							title: 'Вопрос'
						});
						return false;
					} else if (kolvoAgree + kolvoEarlier < TdKolvo && kolvoAgree + kolvoEarlier >= kolvoProf * 0.85) {
						win.transferEvnPLDispDopToEvnPLDispProf();
						return false;
					} else if (kolvoAgree + kolvoEarlier < TdKolvo) {
						sw.swMsg.alert('Ошибка', 'Количество заведенных осмотров / исследований недостаточно для закрытия карты диспансеризации взрослого населения.');
						return false;
					}
				}
			}
		} else if ( getRegionNick().inlist([ 'krasnoyarsk', 'perm', 'vologda' ]) && base_form.findField('DispClass_id').getValue() != 2 ) {
			var xdate2 = new Date(2015,3,1);
			if (win.findById('EPLDD13_EvnPLDispDop13_consDate').getValue() >= xdate2) {
				// При сохранении карты ДВН – 1 этап (если в поле «Случай закончен» указано «Да»)
				if (base_form.findField('EvnPLDispDop13_IsEndStage').getValue() == 2) {
					// Если ПРД < ТД, то выводить сообщение «Количество осмотров/исследований недостаточно для закрытия карты диспансеризации взрослого населения.. ОК».
					// 1. собираем пакеты
					var Packets = {};
					win.dopDispInfoConsentGrid.getGrid().getStore().each(function(rec) {
						if (!Ext.isEmpty(rec.get('SurveyTypeLink_IsUslPack'))) {
							if (!Packets[rec.get('SurveyTypeLink_IsUslPack')]) {
								Packets[rec.get('SurveyTypeLink_IsUslPack')] = 1;
							} else {
								Packets[rec.get('SurveyTypeLink_IsUslPack')]++;
							}
						}
					});

					// 2. считаем ПД и ПРД (Проведенное в рамках ДВН количество долей (ПД) и Проведенное в рамках ДВН и выполненных ранее количество долей (ПРД))
					var kolvoEarlier = 0;
					var kolvoDid = 0;
					win.evnUslugaDispDopGrid.getGrid().getStore().each(function(rec) {
						if (!Ext.isEmpty(rec.get('DopDispInfoConsent_id'))) {
							if ( !Ext.isEmpty(rec.get('EvnUslugaDispDop_didDate')) ) {
								if ( !Ext.isEmpty(rec.get('SurveyTypeLink_IsUslPack')) ) {
									if (Packets[rec.get('SurveyTypeLink_IsUslPack')]) {
										if (rec.get('DopDispInfoConsent_IsEarlier') == 2) {
											kolvoEarlier = kolvoEarlier + 1 / Packets[rec.get('SurveyTypeLink_IsUslPack')];
										} else {
											kolvoDid = kolvoDid + 1 / Packets[rec.get('SurveyTypeLink_IsUslPack')];
										}
									}
								} else {
									if (rec.get('DopDispInfoConsent_IsEarlier') == 2) {
										kolvoEarlier++;
									} else {
										kolvoDid++;
									}
								}
							}
						}
					});

					// 3. считаем ТД (Требуемое количество долей для ДВН)
					var kolvo = 0;
					win.dopDispInfoConsentGrid.getGrid().getStore().each(function(rec) {
						if (!Ext.isEmpty(rec.get('SurveyType_Code')) && !rec.get('SurveyType_Code').inlist([1,48])) {
							if (rec.get('DopDispInfoConsent_IsImpossible') == 'hidden' || rec.get('DopDispInfoConsent_IsAgree') == true || rec.get('DopDispInfoConsent_IsEarlier') == true) {
								if ( !Ext.isEmpty(rec.get('SurveyTypeLink_IsUslPack')) ) {
									if (Packets[rec.get('SurveyTypeLink_IsUslPack')]) {
										kolvo = kolvo + 1 / Packets[rec.get('SurveyTypeLink_IsUslPack')];
									}
								} else {
									kolvo++;
								}
							}
						}
					});

					var PrdKolvo = Math.ceil(Math.round((kolvoDid + kolvoEarlier)*100)/100);
					var TdKolvo = this.count85Percent;
					if (getRegionNick().inlist(['krasnoyarsk','perm']) && (
						win.findById('EPLDD13_EvnPLDispDop13_consDate').getValue() >= getNewDVNDate()
						|| base_form.findField('EvnPLDispDop13_IsNewOrder').getValue() == 2
					)) {
						TdKolvo = Math.ceil(Math.round(kolvo * 85)/100);
					}

					if (PrdKolvo < TdKolvo) {
						sw.swMsg.alert('Ошибка', 'Количество осмотров/исследований недостаточно для закрытия карты диспансеризации взрослого населения.');
						return false;
					}
				}
			} else {
				// Для Перми При сохранении карты ДВН, Если количество заведенных (сохраненных) осмотров / исследований меньше, чем количество осмотров /исследований положенных для данного пациента (использовать количество  из информированного согласия) * 0,84 , то выводить сообщение "Количество сохраненных осмотров\исследований составляет менее 84% от объема, установленного для данного возраста и пола. Ок", Сохранение отменить.
				if (!options.ignoreCheck85 && base_form.findField('EvnPLDispDop13_IsEndStage').getValue() == 2) {
					// считаем количество сохраненных осмотров/исследований
					var kolvo = 0;
					var kolvoAgree = 0;

					win.dopDispInfoConsentGrid.getGrid().getStore().each(function (rec) {
						if (!Ext.isEmpty(rec.get('SurveyType_Code')) && !rec.get('SurveyType_Code').inlist([1, 48])) {
							kolvo++;
						}
					});

					win.evnUslugaDispDopGrid.getGrid().getStore().each(function (rec) {
						if (!Ext.isEmpty(rec.get('DopDispInfoConsent_id'))) {
							if (!Ext.isEmpty(rec.get('EvnUslugaDispDop_didDate'))) {
								kolvoAgree++;
							}
						}
					});

					if (kolvoAgree < this.count85Percent) {
						sw.swMsg.show({
							buttons: Ext.Msg.YESNO,
							fn: function (buttonId, text, obj) {
								if (buttonId == 'yes') {
									options.ignoreCheck85 = true;
									win.doSave(options);
								}
							},
							icon: Ext.MessageBox.QUESTION,
							msg: 'Количество заведенных осмотров / исследований составляет менее 85% от общего количества, предусмотренного программой диспансеризации. Случай диспансеризации не будет оплачен в полном объеме. Сохранить?',
							title: 'Вопрос'
						});
						return false;
					}
				}
			}
		} else if (getRegionNick() != 'ekb') {
			// Для всех остальных регионов Реализовать контроль при сохранении карты ДВН, если проставляем "Случай диспансеризации 1 этап закончен: ДА": Должны быть сохранены все осмотры/исследования, для которых в информированном согласии проставлены чекбоксы "Согласие гражданина" или "Пройдено ранее". При невыполнении данного контроля выводить сообщение "Заведена не вся информация по осмотрам/исследованиям. ОК", сохранение отменить. Т.е. если для осмотра/исследования не проставлен отказ в согласии, то по нему должна быть сохранена услуга (услуга, дата, врач)
			if ( base_form.findField('EvnPLDispDop13_IsEndStage').getValue() == 2 ) {
				
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
					sw.swMsg.alert('Ошибка', 'Заведена не вся информация по осмотрам/исследованиям.');
					return false;
				}
			}
		}

		if (getRegionNick() == 'ekb') {
			// Если в маршрутной карте заведены не все осмотры / исследования, помеченные в согласии «Согласие» или «Пройдено ранее», то выводить предупреждение:
			// «В маршрутной карте заведены не все осмотры / исследования. (Сохранить / Отмена)». При нажатии «Сохранить», сохранить карту ДВН – 2 этап. При нажатии «Отмена»,
			// сохранение карты ДВН – 2 этап отменить, возврат на форму редактирования карты.
			if ( !options.ignoreNotAll && base_form.findField('EvnPLDispDop13_IsEndStage').getValue() == 2 ) {

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
					sw.swMsg.show({
						buttons: Ext.Msg.YESNO,
						fn: function(buttonId, text, obj) {
							options.ignoreNotAll = true;

							if (buttonId == 'yes')
							{
								win.doSave(options);
							}
						},
						icon: Ext.MessageBox.QUESTION,
						msg: 'В маршрутной карте заведены не все осмотры/исследования. Сохранить?',
						title: 'Вопрос'
					});
					return false;
				}
			}
		}

		var xdate4 = new Date(2016,4,1);
		if (getRegionNick() == 'perm' && !Ext.isEmpty(EvnUslugaDispDop_terDate) && EvnUslugaDispDop_terDate >= xdate4 && !options.ignoreMaxDuration && base_form.findField('EvnPLDispDop13_IsEndStage').getValue() == 2) {
			if (base_form.findField('DispClass_id').getValue() != 2) {
				// Если дата окончания карты ДВН-1 этап 01.05.2016 и позже, то продолжительность ДВН-1 этап не должна превышать 30 календарных дней. Реализовать данный контроль при сохранении карты в виде предупреждения, с возможностью сохранения карты ДВН-1этап.
				if (win.findById('EPLDD13_EvnPLDispDop13_consDate').getValue() < EvnUslugaDispDop_terDate.add('D', -30)) {
					sw.swMsg.show({
						buttons: Ext.Msg.YESNO,
						fn: function(buttonId, text, obj) {
							options.ignoreMaxDuration = true;

							if (buttonId == 'yes')
							{
								win.doSave(options);
							}
						},
						icon: Ext.MessageBox.QUESTION,
						msg: 'Продолжительность 1 этапа ДВН превышает 30 календарных дней. Продолжить сохранение?',
						title: 'Вопрос'
					});
					return false;
				}
			} else {
				// Если дата окончания карты ДВН-2 этап 01.05.2016 и позже, то общая продолжительность ДВН–1 и 2 этапа (дата начала ДВН-1этап – дата окончания ДВН-2этап) не должна превышать 90 календарных дней. Реализовать данный контроль при сохранении карты в виде предупреждения, с возможностью сохранения карты ДВН-2этап.
				if (!Ext.isEmpty(base_form.findField('EvnPLDispDop13_firstConsDate').getValue()) && Date.parseDate(base_form.findField('EvnPLDispDop13_firstConsDate').getValue(), 'd.m.Y') < EvnUslugaDispDop_terDate.add('D', -90)) {
					sw.swMsg.show({
						buttons: Ext.Msg.YESNO,
						fn: function(buttonId, text, obj) {
							options.ignoreMaxDuration = true;

							if (buttonId == 'yes')
							{
								win.doSave(options);
							}
						},
						icon: Ext.MessageBox.QUESTION,
						msg: 'Общая продолжительность 1 и 2 этапов ДВН превышает 90 календарных дней. Продолжить сохранение?',
						title: 'Вопрос'
					});
					return false;
				}
			}
		}
		
		win.verfGroup();
		
		base_form.findField('EvnPLDispDop13_consDate').setValue(typeof win.findById('EPLDD13_EvnPLDispDop13_consDate').getValue() == 'object' ? Ext.util.Format.date(win.findById('EPLDD13_EvnPLDispDop13_consDate').getValue(), 'd.m.Y') : win.findById('EPLDD13_EvnPLDispDop13_consDate').getValue());

		// https://redmine.swan.perm.ru/issues/63188
		// Если III группа выбирается в случае диспансеризации открытом после 01.04.2015, необходимо запрещать сохранение с сообщением:
		// "Присвоение III группы здоровья допускается только для случаев диспансеризации, открытых до 01.04.2015".
		var xdate2 = new Date(2015,3,1);
		if (getRegionNick() == 'buryatiya' && win.findById('EPLDD13_EvnPLDispDop13_consDate').getValue() >= xdate2 && base_form.findField('HealthKind_id').getValue() == 3) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function () {
					base_form.findField('HealthKind_id').focus(true);
				},
				icon: Ext.Msg.ERROR,
				msg: 'Присвоение III группы здоровья допускается только для случаев диспансеризации, открытых до 01.04.2015.',
				title: 'Ошибка'
			});
			return false;
		}

		// https://redmine.swan.perm.ru/issues/19835
		// 2. При сохранении карты диспансеризации реализовать контроль: Дата оказания любой услуги (осмотра/исследования) должна быть не раньше, чем за год до осмотра
		// врача-терапевта. При невыполнении данного контроля выводить сообщение: "Дата осмотра/исследования, проведенного ранее должна быть не раньше, чем за год до
		// проведения осмотра врача-терапевта", сохранение отменить.
		// Исключение.
		// – Флюорография легких (16), Маммография (21) должна быть не раньше, чем за 2 года до осмотра врача-терапевта
		// – Мазок (соскоб) с поверхности шейки матки (наружного маточного зева) и цервикального канала на цитологическое исследование (20), УЗИ поджелудочной железы (94), УЗИ почек (95), УЗИ матки и яичников (98), УЗИ простаты (99), УЗИ брюшной аорты должна быть не раньше (100), чем за 3 года до осмотра врача-терапевта
		// 
		// Карелия
		// @task https://redmine.swan.perm.ru/issues/101918
		// - УЗИ поджелудочной железы (94), УЗИ почек (95), УЗИ матки и яичников (98), УЗИ простаты (99), УЗИ брюшной аорты (100) должны быть не раньше, чем за 6 лет до осмотра врача-терапевта. 
		if (!Ext.isEmpty(EvnUslugaDispDop_terDate)) {
			var errorInDates = false;

			if (!Ext.isEmpty(newDVNDate) && win.findById('EPLDD13_EvnPLDispDop13_consDate').getValue() >= newDVNDate) {
				this.evnUslugaDispDopGrid.getGrid().getStore().each(function (rec) {
					if (!Ext.isEmpty(rec.get('EvnUslugaDispDop_didDate'))) {
						if (rec.get('SurveyType_Code') == 19) {
							// терапевт :)
						} else if (rec.get('SurveyType_Code') && rec.get('SurveyType_Code').inlist([14, 16, 21])) {
							if (!Ext.isEmpty(rec.get('EvnUslugaDispDop_didDate')) && rec.get('EvnUslugaDispDop_didDate') < EvnUslugaDispDop_terDate.add('Y', -2)) {
								sw.swMsg.alert('Ошибка', 'Дата осмотра/исследования "' + rec.get('SurveyType_Name') + '", проведенного ранее, должна быть не раньше, чем за 2 года до проведения осмотра врача-терапевта');
								errorInDates = true;
								return false;
							}
						} else if (rec.get('SurveyType_Code') && rec.get('SurveyType_Code').inlist([108])) {
							if (!Ext.isEmpty(rec.get('EvnUslugaDispDop_didDate')) && rec.get('EvnUslugaDispDop_didDate') < EvnUslugaDispDop_terDate.add('Y', -3)) {
								sw.swMsg.alert('Ошибка', 'Дата осмотра/исследования "' + rec.get('SurveyType_Name') + '", проведенного ранее, должна быть не раньше, чем за 3 года до проведения осмотра врача-терапевта');
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
			} else if (getRegionNick() == 'ufa' && win.findById('EPLDD13_EvnPLDispDop13_consDate').getValue() >= xdate3) {
				this.evnUslugaDispDopGrid.getGrid().getStore().each(function (rec) {
					if (!Ext.isEmpty(rec.get('EvnUslugaDispDop_didDate'))) {
						if (rec.get('SurveyType_Code') == 19) {
							// терапевт :)
						} else {
							if (!Ext.isEmpty(rec.get('EvnUslugaDispDop_didDate')) && rec.get('EvnUslugaDispDop_didDate') < EvnUslugaDispDop_terDate.add('Y', -1)) {
								sw.swMsg.alert('Ошибка', 'Дата осмотра/исследования, проведенного ранее, должна быть не раньше, чем за год до проведения осмотра врача-терапевта');
								errorInDates = true;
								return false;
							}
						}
					}
				});
			}
			else if (getRegionNick() == 'pskov' && win.findById('EPLDD13_EvnPLDispDop13_consDate').getValue() >= xdate3) {
				this.evnUslugaDispDopGrid.getGrid().getStore().each(function (rec) {
					if (!Ext.isEmpty(rec.get('EvnUslugaDispDop_didDate'))) {
						if (rec.get('SurveyType_Code') == 19) {
							// терапевт :)
						} else {
							if (!Ext.isEmpty(rec.get('EvnUslugaDispDop_didDate')) && rec.get('DopDispInfoConsent_IsEarlier') != 2 && rec.get('EvnUslugaDispDop_didDate') < EvnUslugaDispDop_terDate.add('D', -30)) {
								sw.swMsg.alert('Ошибка', 'Осмотры / исследования, проведенные в рамках диспансеризации взрослого населения, не могут быть ранее, чем за 30 календарных  дней до даты осмотра врача-терапевта.');
								errorInDates = true;
								return false;
							}
						}
					}
				});
			}
			else if (getRegionNick().inlist(['penza'])) {
				this.evnUslugaDispDopGrid.getGrid().getStore().each(function (rec) {
					if (!Ext.isEmpty(rec.get('EvnUslugaDispDop_didDate'))) {
						if (rec.get('SurveyType_Code') == 19) {
							// терапевт :)
						} else if (win.inWowRegister && rec.get('SurveyType_Code') && rec.get('SurveyType_Code').inlist([16, 21])) {
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
			}
			else if (getRegionNick().inlist(['perm'])) {
				this.evnUslugaDispDopGrid.getGrid().getStore().each(function (rec) {
					if (!Ext.isEmpty(rec.get('EvnUslugaDispDop_didDate'))) {
						if (rec.get('SurveyType_Code') == 19) {
							// терапевт :)
						} else if (rec.get('SurveyType_Code') && rec.get('SurveyType_Code').inlist([16, 21])) {
							if (!Ext.isEmpty(rec.get('EvnUslugaDispDop_didDate')) && rec.get('EvnUslugaDispDop_didDate') < EvnUslugaDispDop_terDate.add('Y', -2)) {
								sw.swMsg.alert('Ошибка', 'Дата осмотра/исследования "' + rec.get('SurveyType_Name') + '", проведенного ранее, должна быть не раньше, чем за 2 года до проведения осмотра врача-терапевта');
								errorInDates = true;
								return false;
							}
						} else if (rec.get('SurveyType_Code') && rec.get('SurveyType_Code').inlist([94, 95]) && rec.get('UslugaComplex_Code').inlist(['A05.14.001', 'A06.30.005'])) {
							if (!Ext.isEmpty(rec.get('EvnUslugaDispDop_didDate')) && rec.get('EvnUslugaDispDop_didDate') < EvnUslugaDispDop_terDate.add('Y', -3)) {
								sw.swMsg.alert('Ошибка', 'Дата осмотра/исследования "' + rec.get('SurveyType_Name') + '", проведенного ранее, должна быть не раньше, чем за 3 года до проведения осмотра врача-терапевта');
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
			}
			else if (getRegionNick().inlist(['kareliya'])) {
				this.evnUslugaDispDopGrid.getGrid().getStore().each(function (rec) {
					if (!Ext.isEmpty(rec.get('EvnUslugaDispDop_didDate'))) {
						if (rec.get('SurveyType_Code') == 19) {
							// терапевт :)
						}
						// Флюорография легких, Маммография должна быть не раньше, чем за 2 года до осмотра врача-терапевта
						else if (rec.get('SurveyType_Code') && rec.get('SurveyType_Code').inlist([16, 21])) {
							if (!Ext.isEmpty(rec.get('EvnUslugaDispDop_didDate')) && rec.get('EvnUslugaDispDop_didDate') < EvnUslugaDispDop_terDate.add('Y', -2)) {
								sw.swMsg.alert('Ошибка', 'Дата осмотра/исследования "' + rec.get('SurveyType_Name') + '", проведенного ранее, должна быть не раньше, чем за 2 года до проведения осмотра врача-терапевта');
								errorInDates = true;
								return false;
							}
						}
						// Мазок (соскоб) с поверхности шейки матки (наружного маточного зева) и цервикального канала на цитологическое исследование, должна быть не раньше, чем за 3 года до осмотра врача-терапевта
						else if (rec.get('SurveyType_Code') && (rec.get('SurveyType_Code').inlist([20]))) {
							if (!Ext.isEmpty(rec.get('EvnUslugaDispDop_didDate')) && rec.get('EvnUslugaDispDop_didDate') < EvnUslugaDispDop_terDate.add('Y', -3)) {
								sw.swMsg.alert('Ошибка', 'Дата осмотра/исследования "' + rec.get('SurveyType_Name') + '", проведенного ранее, должна быть не раньше, чем за 3 года до проведения осмотра врача-терапевта');
								errorInDates = true;
								return false;
							}
						}
						// УЗИ поджелудочной железы (94), УЗИ почек (95), УЗИ матки и яичников (98), УЗИ простаты (99), УЗИ брюшной аорты (100) должны быть не раньше, чем за 6 лет до осмотра врача-терапевта. 
						else if (rec.get('SurveyType_Code') && (rec.get('SurveyType_Code').inlist([94, 95, 98, 99, 100]))) {
							if (!Ext.isEmpty(rec.get('EvnUslugaDispDop_didDate')) && rec.get('EvnUslugaDispDop_didDate') < EvnUslugaDispDop_terDate.add('Y', -6)) {
								sw.swMsg.alert('Ошибка', 'Дата осмотра/исследования "' + rec.get('SurveyType_Name') + '", проведенного ранее, должна быть не раньше, чем за 6 лет до проведения осмотра врача-терапевта');
								errorInDates = true;
								return false;
							}
						}
						else {
							if (!Ext.isEmpty(rec.get('EvnUslugaDispDop_didDate')) && rec.get('EvnUslugaDispDop_didDate') < EvnUslugaDispDop_terDate.add('Y', -1)) {
								sw.swMsg.alert('Ошибка', 'Дата осмотра/исследования, проведенного ранее, должна быть не раньше, чем за год до проведения осмотра врача-терапевта');
								errorInDates = true;
								return false;
							}
						}
					}
				});
			}
			else {
				this.evnUslugaDispDopGrid.getGrid().getStore().each(function (rec) {
					if (!Ext.isEmpty(rec.get('EvnUslugaDispDop_didDate'))) {
						if (rec.get('SurveyType_Code') == 19) {
							// терапевт :)
						} else if (rec.get('SurveyType_Code') && rec.get('SurveyType_Code').inlist([16, 21])) {
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
			}

			if (errorInDates) {
				return false;
			}
		}


		var isEarlier_OsmotrVrachaTerapevta = false;
		win.dopDispInfoConsentGrid.getGrid().getStore().each(function (rec) {
			if ( ! Ext.isEmpty(rec.get('SurveyType_Code')) && rec.get('SurveyType_Code').inlist([19])) {
				if (rec.get('DopDispInfoConsent_IsEarlier') == true) {
					isEarlier_OsmotrVrachaTerapevta = true;
				}
			}
		});


		// Дата осмотра врача терапевта должна быть больше/равна Дате подписания согласия/отказа 2 этапа,
		// (#126832 Регион: Свердловская область) кроме случаев, когда в Информированном добровольном согласии Приём
		// (осмотр) врача-терапевта отмечен, как пройденный ранее. При невыполнении выводить сообщение, сохранение отменить.

		var consDate = Date.parseDate(base_form.findField('EvnPLDispDop13_consDate').getValue(), 'd.m.Y');

		if((getRegionNick() == 'ekb' && isEarlier_OsmotrVrachaTerapevta == true) == false){

			if ( !Ext.isEmpty(consDate) && !Ext.isEmpty(EvnUslugaDispDop_terDate) && consDate > EvnUslugaDispDop_terDate) {
				sw.swMsg.show({
					buttons: Ext.Msg.OK,
					fn: function(){},
					icon: Ext.Msg.ERROR,
					msg: "Дата подписания согласия/отказа не должна быть позже даты осмотра терапевта",
					title: 'Ошибка'
				});
				return false;
			}

		}


		// о ужас, для Карелии придумали такую жесть %)
		// Если Дата осмотра врача-терапевта (ВОП) 01.04.2015 и позже и Дата подписания согласия/отказа» 31.03.2015 и раньше,
		var xdate2 = new Date(2015,3,1);
		if (getRegionNick() == 'kareliya' && win.findById('EPLDD13_EvnPLDispDop13_consDate').getValue() < xdate2 && EvnUslugaDispDop_terDate >= xdate2) {
			win.getLoadMask('Пожалуйста подождите').show();
			Ext.Ajax.request({
				callback: function(options, success, response) {
					win.getLoadMask().hide();
					if ( success ) {
						var response_obj = Ext.util.JSON.decode(response.responseText);

						var changed = '';
						if (response_obj.changed) {
							changed = response_obj.changed;
						}

						sw.swMsg.show({
							buttons: Ext.Msg.YESNO,
							fn: function(buttonId, text, obj) {
								options.ignoreCheckEndStage = true;

								if (buttonId == 'yes')
								{
									win.findById('EPLDD13_EvnPLDispDop13_consDate').setValue(EvnUslugaDispDop_terDate);
									win.saveDopDispInfoConsentAfterLoad = true;
									win.blockSaveDopDispInfoConsent = true;
									win.loadDopDispInfoConsentGrid(EvnUslugaDispDop_terDate);
								}
							},
							icon: Ext.MessageBox.QUESTION,
							msg: 'Дата осмотра врача-терапевта 1.04.2015 и позже. Список осмотров/исследований в информированном согласии / маршрутной карте будет изменен. Информация по следующим осмотрам / исследованиям будет удалена:<br>' + changed + '<br>Переформировать карту?',
							title: 'Вопрос'
						});
					}
				},
				params: {
					Person_id: base_form.findField('Person_id').getValue(),
					DispClass_id: base_form.findField('DispClass_id').getValue(),
					EvnPLDispDop13_id: base_form.findField('EvnPLDispDop13_id').getValue(),
					EvnPLDispDop13_fid: base_form.findField('EvnPLDispDop13_fid').getValue(),
					EvnPLDispDop13_IsNewOrder: base_form.findField('EvnPLDispDop13_IsNewOrder').getValue(),
					EvnPLDispDop13_consDate: Ext.util.Format.date(win.findById('EPLDD13_EvnPLDispDop13_consDate').getValue(), 'd.m.Y'),
					EvnPLDispDop13_newConsDate: Ext.util.Format.date(EvnUslugaDispDop_terDate, 'd.m.Y')
				},
				url: '/?c=EvnPLDispDop13&m=getDopDispInfoConsentChanges'
			});
			return false;
		}

		var consDate = Date.parseDate(base_form.findField('EvnPLDispDop13_consDate').getValue(), 'd.m.Y');
		var DateX20180101 = new Date(2018, 0, 1);

		// https://redmine.swan.perm.ru/issues/19835
		// 3. При сохранении карты диспансеризации (с указанным значением "Да" в поле "Случай закончен") реализовать контроль: Осмотр врача-терапевта должен быть
		// сохранен. При невыполнении данного контроля выводить сообщение "Осмотра врача-терапевта обязателен при проведении диспансеризации взрослого населения",
		// сохранение отменить.
		// @task https://redmine.swan.perm.ru/issues/124302
		if ( base_form.findField('EvnPLDispDop13_IsEndStage').getValue() == 2 ) {
			if ( consDate >= DateX20180101 ) {
				if (
					base_form.findField('DispClass_id').getValue() != 2
					&& Ext.isEmpty(EvnUslugaDispDop_terDate)
					&& (!getRegionNick().inlist(['astra', 'ekb']) || win.dopDispInfoConsentGrid.getGrid().getStore().getCount() > 3)
				) {
					sw.swMsg.show({
						buttons: Ext.Msg.OK,
						fn: function () {
							base_form.findField('EvnPLDispDop13_IsEndStage').focus(true);
						},
						icon: Ext.Msg.ERROR,
						msg: 'Осмотр врача-терапевта обязателен при проведении диспансеризации взрослого населения.',
						title: 'Ошибка'
					});
					return false;
				}

				if ( base_form.findField('DispClass_id').getValue() != 2 && !getRegionNick().inlist([ 'kz' ]) ) {
					if ( anketSurveyExists == true && Ext.isEmpty(EvnUslugaDispDop_anketDate) ) {
						sw.swMsg.show({
							buttons: Ext.Msg.OK,
							fn: function () {
								base_form.findField('EvnPLDispDop13_IsEndStage').focus(true);
							},
							icon: Ext.Msg.ERROR,
							msg: 'Опрос (анкетирование) обязателен при проведении диспансеризации взрослого населения.',
							title: 'Ошибка'
						});
						return false;
					}
				}
			}
			else {
				// Действует старая логика
				if ( getRegionNick().inlist(['perm', 'ekb', 'penza', 'astra', 'kareliya', 'vologda']) && base_form.findField('DispClass_id').getValue() != 2 ) {
					if ( Ext.isEmpty(EvnUslugaDispDop_terDate) || Ext.isEmpty(EvnUslugaDispDop_anketDate) ) {
						sw.swMsg.show({
							buttons: Ext.Msg.OK,
							fn: function () {
								base_form.findField('EvnPLDispDop13_IsEndStage').focus(true);
							},
							icon: Ext.Msg.ERROR,
							msg: 'Осмотр врача-терапевта и опрос (анкетирование) обязательны при проведении диспансеризации взрослого населения.',
							title: 'Ошибка'
						});
						return false;
					}
				}
				else {
					if ( Ext.isEmpty(EvnUslugaDispDop_terDate) ) {
						if ( consDate >= DateX20180101 && !Ext.isEmpty(indivUglProfConsultOrGroupProfConsult_didDate) && getRegionNick() !== 'kz' ) {
							// Для случаев с датой подписания согласия / отказа больше или равной 01.01.2018, если в поле «Случай диспансеризации 2 этап закончен» выбрано значение «Да»
							// и сохранён осмотр «Индивидуальное углубленное профилактическое консультирование или групповое профилактическое консультирование», то при нажатии на кнопку «Сохранить»
							// контроль на наличие осмотра врача-терапевта не выполняется. #124390
						}
						else {
							sw.swMsg.show({
								buttons: Ext.Msg.OK,
								fn: function () {
									base_form.findField('EvnPLDispDop13_IsEndStage').focus(true);
								},
								icon: Ext.Msg.ERROR,
								msg: 'Осмотр врача-терапевта обязателен при проведении диспансеризации взрослого населения.',
								title: 'Ошибка'
							});
							return false;
						}
					}
				}
			}
		}
		
		// http://redmine.swan.perm.ru/issues/27943
		// Если сохранен осмотр врача-терапевта (ВОП) и если в поле "Случай диспансеризации 1 этап закончен" указано значение "Нет", то выводить предупреждение: 
		// "Сохранен осмотр врача-терапевта (ВОП), закрыть карту диспансеризации 1 этапа?. Случай закончен - "Да" / Случай закончен - "Нет". 
		// При выборе "Случай закончен - "Да" проставлять значение "Да" в поле "Случай диспансеризации 1 этап закончен".
		if ( !options.ignoreCheckEndStage && base_form.findField('EvnPLDispDop13_IsEndStage').getValue() != 2 && !Ext.isEmpty(EvnUslugaDispDop_terDate) ) {
			sw.swMsg.show({
				buttons: Ext.Msg.YESNO,
				fn: function(buttonId, text, obj) {
					options.ignoreCheckEndStage = true;
					
					if (buttonId == 'yes')
					{
						base_form.findField('EvnPLDispDop13_IsEndStage').setValue(2);
						win.doSave(options);
					}
					else
					{
						win.doSave(options);
					}
				},
				icon: Ext.MessageBox.QUESTION,
				msg: 'Сохранен осмотр врача-терапевта (ВОП), закрыть карту диспансеризации ' + (base_form.findField('DispClass_id').getValue() == 2?'2':'1') + ' этапа?',
				title: 'Вопрос'
			});
			return false;
		}

		var params = new Object();
		
		if (base_form.findField('EvnPLDispDop13_IsEndStage').disabled) {
			params.EvnPLDispDop13_IsEndStage = base_form.findField('EvnPLDispDop13_IsEndStage').getValue();
		}
		
		if (base_form.findField('EvnPLDispDop13_IsTwoStage').disabled) {
			params.EvnPLDispDop13_IsTwoStage = base_form.findField('EvnPLDispDop13_IsTwoStage').getValue();
		}

		if (base_form.findField('PayType_id').disabled) {
			params.PayType_id = base_form.findField('PayType_id').getValue();
		}
		
		if (base_form.findField('EvnPLDispDop13_IsSuspectZNO').disabled) {
			params.EvnPLDispDop13_IsSuspectZNO = base_form.findField('EvnPLDispDop13_IsSuspectZNO').getValue();
		}
		
		if (base_form.findField('Diag_spid').disabled) {
			params.Diag_spid = base_form.findField('Diag_spid').getValue();
		}

		params.checkAttributeforLpuSection = (!Ext.isEmpty(options.checkAttributeforLpuSection)) ? options.checkAttributeforLpuSection : 0;
		if(base_form.findField('EvnPLDispDop13_IsMobile').checked){
			params.checkAttributeforLpuSection=2;
		}
		
		win.getLoadMask("Подождите, идет сохранение...").show();

		EvnPLDispDop13_form.getForm().submit({
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
				win.getLoadMask().hide();
				
				if (action.result)
				{
                    if ( options && options.print == true ) {
                        options.callback();
                    }
                    else {
                        win.callback();
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
	id: 'EvnPLDispDop13EditWindow',
	setDiagSpidComboDisabled: function() {

		if (!getRegionNick().inlist(['perm', 'msk']) || this.action == 'view') return false;

		var base_form = this.EvnPLDispDop13FormPanel.getForm();
		var diag_spid_combo = base_form.findField('Diag_spid');
		var iszno_checkbox = this.findById('EPLDD13EF_EvnPLDispDop13_IsSuspectZNO');

		if (!diag_spid_combo.getValue()) return false;

		Ext.Ajax.request({
			params: {
				Person_id: base_form.findField('Person_id').getValue(),
				Diag_id: diag_spid_combo.getValue()
			},
			success: function(response, options) {
				var response_obj = Ext.util.JSON.decode(response.responseText);
				diag_spid_combo.setDisabled(response_obj.isExists == 2);
				iszno_checkbox.setDisabled(response_obj.isExists == 2);
			},
			url: '/?c=MorbusOnkoSpecifics&m=checkMorbusExists'
		});
	},
	showEvnUslugaDispDopEditWindow: function(action) {
		var base_form = this.EvnPLDispDop13FormPanel.getForm();
		var grid = this.evnUslugaDispDopGrid.getGrid();
		var win = this;
		
		var record = grid.getSelectionModel().getSelected();
		
		if ( !record || !record.get('DopDispInfoConsent_id') ) {
			return false;
		}

		var EvnPLDispDop13_consDate = base_form.findField('EvnPLDispDop13_consDate').getValue();

		if ( !Ext.isEmpty(EvnPLDispDop13_consDate) && typeof EvnPLDispDop13_consDate != 'object' ) {
			EvnPLDispDop13_consDate = Date.parseDate(EvnPLDispDop13_consDate, 'd.m.Y');
		}
		
		// если опрос то открываем форму анкетирования.
		if (record.get('SurveyType_Code') == 2) {
			var params = {
				archiveRecord: this.archiveRecord,
				action: action,
				object: 'EvnPLDispDop13',
				DopDispQuestion_setDate: record.get('EvnUslugaDispDop_didDate'),
				EvnPLDisp_consDate: (typeof win.findById('EPLDD13_EvnPLDispDop13_consDate').getValue() == 'object' ? Ext.util.Format.date(win.findById('EPLDD13_EvnPLDispDop13_consDate').getValue(), 'd.m.Y') : win.findById('EPLDD13_EvnPLDispDop13_consDate').getValue()),
				EvnPLDisp_id: base_form.findField('EvnPLDispDop13_id').getValue(),
				DispClass_id: base_form.findField('DispClass_id').getValue(),
				onHide: Ext.emptyFn,
				callback: function(qdata) {
					// обновить грид
					grid.getStore().reload();
					// сюда приходит ответ по нажатию кнопки расчёт на форме анкетирования => нужно заполнить соответсвующие поля на форме.
					var alcodepend = 0;
					var alcosum = 0;
					var isPohud = false;
					var isPohudDepend = false;
					var isPohudDependTwo = false;
					var isIrrational = false;
					var isIrrationalTwo = false;
					var isOnko = false;
					var isOnkoTwo = false;
					var isOnkoThree = false;
					var age = win.PersonInfoPanel.getFieldValue('Person_Age');

					if (!Ext.isEmpty(qdata)) {
						base_form.findField('EvnPLDispDop13_IsStenocard').setValue(1);
						base_form.findField('EvnPLDispDop13_IsBrain').setValue(1);
						base_form.findField('EvnPLDispDop13_IsDoubleScan').setValue(1);
						base_form.findField('EvnPLDispDop13_IsTub').setValue(1);
						base_form.findField('EvnPLDispDop13_IsEsophag').setValue(1);
						base_form.findField('EvnPLDispDop13_IsSmoking').setValue(1);
						base_form.findField('EvnPLDispDop13_IsRiskAlco').setValue(1);
						base_form.findField('EvnPLDispDop13_IsLowActiv').setValue(1);
						base_form.findField('EvnPLDispDop13_IsAlcoDepend').setValue(1);
						base_form.findField('EvnPLDispDop13_IsTIA').setValue(1);
						base_form.findField('EvnPLDispDop13_IsSpirometry').setValue(1);
						base_form.findField('EvnPLDispDop13_IsLungs').setValue(1);
						base_form.findField('EvnPLDispDop13_IsTopGastro').setValue(1);
						base_form.findField('EvnPLDispDop13_IsBotGastro').setValue(1);
						base_form.findField('EvnPLDispDop13_IsIrrational').setValue(1);
						base_form.findField('EvnPLDispDop13_IsUseNarko').setValue(1);
						base_form.findField('EvnPLDispDop13_IsHeartFailure').setValue(1);
						base_form.findField('EvnPLDispDop13_IsOncology').setValue(1);
						base_form.findField('EvnPLDispDop13_IsRespiratory').setValue(1);

						for(var k in qdata) {
							if (!Ext.isEmpty(qdata[k].QuestionType_id)) {
								switch ( qdata[k].QuestionType_id ) {
									case 13:
									case 107:
									case 108:
									case 149:
									case 150:
									case 691:
									case 692:
									case 724:
									case 833:
									case 874:
									case 915:
									case 956:
									case 1024:
									case 1064:
									case 1104:
									case 1144:
										// Автоматически указывать значение «Имеется», если при анкетировании на вопрос №13 или №14 сохранен ответ «Да»
										if ( (qdata[k].AnswerType_id == 1 && qdata[k].DopDispQuestion_IsTrue == 2) || (qdata[k].AnswerType_id == 3 && qdata[k].DopDispQuestion_ValuesStr == 2) ) {
											base_form.findField('EvnPLDispDop13_IsStenocard').setValue(2);
										}
										break;

									case 14:
										if ( (qdata[k].AnswerType_id == 1 && qdata[k].DopDispQuestion_IsTrue == 2) || (qdata[k].AnswerType_id == 3 && qdata[k].DopDispQuestion_ValuesStr == 2) ) {
											base_form.findField('EvnPLDispDop13_IsStenocard').setValue(2);
											base_form.findField('EvnPLDispDop13_IsDoubleScan').setValue(2);
										}
										break;

									case 693:
									case 694:
									case 695:
									case 835:
									case 876:
									case 917:
									case 958:
									case 836:
									case 877:
									case 918:
									case 959:
									case 837:
									case 878:
									case 919:
									case 960:
									case 1026:
									case 1066:
									case 1106:
									case 1146:
									case 1027:
									case 1067:
									case 1107:
									case 1147:
									case 1028:
									case 1068:
									case 1108:
									case 1148:
										if ( (qdata[k].AnswerType_id == 1 && qdata[k].DopDispQuestion_IsTrue == 2) || (qdata[k].AnswerType_id == 3 && qdata[k].DopDispQuestion_ValuesStr == 2) ) {
											base_form.findField('EvnPLDispDop13_IsTIA').setValue(2);
										}
										break;

									case 696:
									case 697:
									case 838:
									case 879:
									case 920:
									case 961:
									case 839:
									case 880:
									case 921:
									case 962:
									case 1030:
									case 1070:
									case 1110:
									case 1150:
									case 1031:
									case 1071:
									case 1111:
									case 1151:
										if ( (qdata[k].AnswerType_id == 1 && qdata[k].DopDispQuestion_IsTrue == 2) || (qdata[k].AnswerType_id == 3 && qdata[k].DopDispQuestion_ValuesStr == 2) ) {
											base_form.findField('EvnPLDispDop13_IsSpirometry').setValue(2);
											base_form.findField('EvnPLDispDop13_IsRespiratory').setValue(2);
										}
										break;

									case 698:
									case 840:
									case 881:
									case 922:
									case 963:
									case 1032:
									case 1072:
									case 1112:
									case 1152:
										if ( (qdata[k].AnswerType_id == 1 && qdata[k].DopDispQuestion_IsTrue == 2) || (qdata[k].AnswerType_id == 3 && qdata[k].DopDispQuestion_ValuesStr == 2) ) {
											base_form.findField('EvnPLDispDop13_IsLungs').setValue(2);
										}
										break;

									case 729:
										if ( (qdata[k].AnswerType_id == 1 && qdata[k].DopDispQuestion_IsTrue == 2) || (qdata[k].AnswerType_id == 3 && qdata[k].DopDispQuestion_ValuesStr == 2) ) {
											base_form.findField('EvnPLDispDop13_IsHeartFailure').setValue(2);
										}
										break;

									case 707:
									case 849:
									case 890:
									case 931:
									case 972:
										if ( (qdata[k].AnswerType_id == 1 && qdata[k].DopDispQuestion_IsTrue == 1) || (qdata[k].AnswerType_id == 3 && qdata[k].DopDispQuestion_ValuesStr == 1) ) {
											base_form.findField('EvnPLDispDop13_IsIrrational').setValue(2);
										}
										break;
										
									case 708:
									case 850:
									case 891:
									case 932:
									case 973:
										if ( (qdata[k].AnswerType_id == 1 && qdata[k].DopDispQuestion_IsTrue == 2) || (qdata[k].AnswerType_id == 3 && qdata[k].DopDispQuestion_ValuesStr == 2) ) {
											base_form.findField('EvnPLDispDop13_IsIrrational').setValue(2);
										}
										break;

									case 709:
									case 851:
									case 892:
									case 933:
									case 974:
										if ( (qdata[k].AnswerType_id == 1 && qdata[k].DopDispQuestion_IsTrue == 2) || (qdata[k].AnswerType_id == 3 && qdata[k].DopDispQuestion_ValuesStr == 2) ) {
											base_form.findField('EvnPLDispDop13_IsUseNarko').setValue(2);
										}
										break;

									case 701:
									case 843:
									case 884:
									case 925:
									case 966:
										if ( (qdata[k].AnswerType_id == 1 && qdata[k].DopDispQuestion_IsTrue == 2) || (qdata[k].AnswerType_id == 3 && qdata[k].DopDispQuestion_ValuesStr == 2) ) {
											isPohud = true;
										}
										break;

									case 699:
									case 700:
									case 841:
									case 882:
									case 923:
									case 964:
									case 842:
									case 883:
									case 924:
									case 965:
										if ( (qdata[k].AnswerType_id == 1 && qdata[k].DopDispQuestion_IsTrue == 2) || (qdata[k].AnswerType_id == 3 && qdata[k].DopDispQuestion_ValuesStr == 2) ) {
											isPohudDepend = true;
										}
										break;

									case 702:
									case 703:
									case 844:
									case 885:
									case 926:
									case 967:
									case 845:
									case 886:
									case 927:
									case 968:
										if ( (qdata[k].AnswerType_id == 1 && qdata[k].DopDispQuestion_IsTrue == 2) || (qdata[k].AnswerType_id == 3 && qdata[k].DopDispQuestion_ValuesStr == 2) ) {
											isPohudDependTwo = true;
										}
										break;

									case 733:
									case 1038:
									case 1078:
									case 1118:
									case 1158:
										if ( (qdata[k].AnswerType_id == 1 && qdata[k].DopDispQuestion_IsTrue == 1) || (qdata[k].AnswerType_id == 3 && qdata[k].DopDispQuestion_ValuesStr == 1) ) {
											isIrrational = true;
										}
										break;

									case 734:
									case 1039:
									case 1079:
									case 1119:
									case 1159:
										if ( (qdata[k].AnswerType_id == 1 && qdata[k].DopDispQuestion_IsTrue == 1) || (qdata[k].AnswerType_id == 3 && qdata[k].DopDispQuestion_ValuesStr == 1) ) {
											isIrrationalTwo = true;
										}
										break;

									case 742:
										if ( (qdata[k].AnswerType_id == 1 && qdata[k].DopDispQuestion_IsTrue == 2) || (qdata[k].AnswerType_id == 3 && qdata[k].DopDispQuestion_ValuesStr == 2) ) {
											isOnko = true;
										}
										break;

									case 743:
										if ( (qdata[k].AnswerType_id == 1 && qdata[k].DopDispQuestion_IsTrue == 1) || (qdata[k].AnswerType_id == 3 && qdata[k].DopDispQuestion_ValuesStr == 1) ) {
											isOnkoTwo = true;
										}
										break;

									case 744:
										if ( (qdata[k].AnswerType_id == 1 && qdata[k].DopDispQuestion_IsTrue == 2) || (qdata[k].AnswerType_id == 3 && qdata[k].DopDispQuestion_ValuesStr == 2) ) {
											isOnkoThree = true;
										}
										break;

									case 15:
									case 16:
									case 17:
									case 18:
										if ( (qdata[k].AnswerType_id == 1 && qdata[k].DopDispQuestion_IsTrue == 2) || (qdata[k].AnswerType_id == 3 && qdata[k].DopDispQuestion_ValuesStr == 2) ) {
											base_form.findField('EvnPLDispDop13_IsBrain').setValue(2);
											base_form.findField('EvnPLDispDop13_IsDoubleScan').setValue(2);
										}
										break;

									case 109:
									case 110:
									case 111:
									case 112:
									case 151:
									case 152:
									case 153:
									case 726:
									case 727:
									case 728:
										// Автоматически указывать значение «Имеется», если при анкетировании хотя бы на один из вопросов №14-18 сохранен ответ «Да»
										if ( (qdata[k].AnswerType_id == 1 && qdata[k].DopDispQuestion_IsTrue == 2) || (qdata[k].AnswerType_id == 3 && qdata[k].DopDispQuestion_ValuesStr == 2) ) {
											base_form.findField('EvnPLDispDop13_IsDoubleScan').setValue(2);
										}
										break;

									case 19:
									case 20:
									case 113:
									case 114:
										// Автоматически указывать значение «Имеется», если при анкетировании хотя бы на один из вопросов №19-20 сохранен ответ «Да»
										if ( (qdata[k].AnswerType_id == 1 && qdata[k].DopDispQuestion_IsTrue == 2) || (qdata[k].AnswerType_id == 3 && qdata[k].DopDispQuestion_ValuesStr == 2) ) {
											base_form.findField('EvnPLDispDop13_IsTub').setValue(2);
										}
										break;

									case 21:
									case 22:
									case 115:
									case 116:
									case 119:
										// Автоматически указывать значение «Имеется», если при анкетировании хотя бы на один из вопросов №21-22 сохранен ответ «Да»
										if ( age >= 50 && (qdata[k].AnswerType_id == 1 && qdata[k].DopDispQuestion_IsTrue == 2) || (qdata[k].AnswerType_id == 3 && qdata[k].DopDispQuestion_ValuesStr == 2) ) {
											base_form.findField('EvnPLDispDop13_IsEsophag').setValue(2);
										}
										break;
										
									case 1033:
									case 1073:
									case 1113:
									case 1153:
										if ( (qdata[k].AnswerType_id == 1 && qdata[k].DopDispQuestion_IsTrue == 2) || (qdata[k].AnswerType_id == 3 && qdata[k].DopDispQuestion_ValuesStr == 2) ) {
											base_form.findField('EvnPLDispDop13_IsEsophag').setValue(2);
											base_form.findField('EvnPLDispDop13_IsTopGastro').setValue(2);
										}
										break;

									case 26:
									case 120:
									case 155:
									case 704:
									case 730:
									case 846:
									case 887:
									case 928:
									case 969:
									case 1035:
									case 1075:
									case 1115:
									case 1155:
										if ( (qdata[k].AnswerType_id == 1 && qdata[k].DopDispQuestion_IsTrue == 2) || (qdata[k].AnswerType_id == 3 && qdata[k].DopDispQuestion_ValuesStr == 2) ) {
											base_form.findField('EvnPLDispDop13_IsSmoking').setValue(2);
										}
										break;

									case 1034:
									case 1074:
									case 1114:
									case 1154:
										if ( (qdata[k].AnswerType_id == 1 && qdata[k].DopDispQuestion_IsTrue == 2) || (qdata[k].AnswerType_id == 3 && qdata[k].DopDispQuestion_ValuesStr == 2) ) {
											base_form.findField('EvnPLDispDop13_IsBotGastro').setValue(2);
										}
										break;

									case 27:
									case 28:
									case 29:
									case 30:
									case 123:
									case 124:
									case 125:
									case 126:
										if ( (qdata[k].AnswerType_id == 1 && qdata[k].DopDispQuestion_IsTrue == 2) || (qdata[k].AnswerType_id == 3 && qdata[k].DopDispQuestion_ValuesStr == 2) ) {
											base_form.findField('EvnPLDispDop13_IsRiskAlco').setValue(2);
											alcodepend++;
										}
										break;

									case 31:
									case 127:
									case 848:
									case 889:
									case 930:
									case 971:
										// на вопрос №31 сохранен ответ «до 30 минут»
										if ( qdata[k].DopDispQuestion_ValuesStr == 1 )  {
											base_form.findField('EvnPLDispDop13_IsLowActiv').setValue(2);
										}
										break;

									case 172:
									case 706:
									case 735:
									case 1040:
									case 1080:
									case 1120:
									case 1160:
										if ( (qdata[k].AnswerType_id == 1 && qdata[k].DopDispQuestion_IsTrue == 1) || (qdata[k].AnswerType_id == 3 && qdata[k].DopDispQuestion_ValuesStr == 1) ) {
											base_form.findField('EvnPLDispDop13_IsLowActiv').setValue(2);
										}
										break;

									case 710:
									case 711:
									case 712:
									case 852:
									case 893:
									case 934:
									case 975:
									case 853:
									case 894:
									case 935:
									case 976:
									case 854:
									case 895:
									case 936:
									case 977:
										if (parseInt(qdata[k].DopDispQuestion_ValuesStr)) {
											alcosum += parseInt(qdata[k].DopDispQuestion_ValuesStr) - 64;
										}
										break;
								}
							}
						}
					}

					if (alcodepend > 3) {
						base_form.findField('EvnPLDispDop13_IsAlcoDepend').setValue(2);
					}

					var sex_id = win.PersonInfoPanel.getFieldValue('Sex_id');
					if (alcosum >= 4 || (sex_id == 2 && alcosum >= 3)) {
						base_form.findField('EvnPLDispDop13_IsRiskAlco').setValue(2);
					}

					if (isPohud && isPohudDepend) {
						base_form.findField('EvnPLDispDop13_IsEsophag').setValue(2);
						base_form.findField('EvnPLDispDop13_IsTopGastro').setValue(2);
					}

					if (isPohud && isPohudDependTwo) {
						base_form.findField('EvnPLDispDop13_IsBotGastro').setValue(2);
					}

					if (isIrrational && isIrrationalTwo) {
						base_form.findField('EvnPLDispDop13_IsIrrational').setValue(2);
					}

					if (isOnko && isOnkoTwo && isOnkoThree) {
						base_form.findField('EvnPLDispDop13_IsOncology').setValue(2);
					}

					win.EvnDiagDopDispBeforeGrid.getGrid().getStore().reload();
					win.HeredityDiag.getGrid().getStore().reload();
					win.ProphConsultGrid.getGrid().getStore().reload();
					win.NeedConsultGrid.getGrid().getStore().reload();
				}
			};

			if (getRegionNick().inlist(['astra', 'ekb', 'kareliya']) && base_form.findField('DispClass_id').getValue() == 1) {
				params.minDate = '01.01.'+Ext.util.Format.date(EvnPLDispDop13_consDate, 'Y');
				params.maxDate = '31.12.'+Ext.util.Format.date(EvnPLDispDop13_consDate, 'Y');
			}

			params.EvnUslugaDispDop_id = record.get('EvnUslugaDispDop_id');

			getWnd('swDopDispQuestionEditWindow').show(params);
		// иначе форму услуги
		} else {
			var newDVNDate = getNewDVNDate();
			var UslugaComplex_Date = win.findById('EPLDD13_EvnPLDispDop13_consDate').getValue();
			if (base_form.findField('EvnPLDispDop13_IsNewOrder').getValue() == 2) {
				// Список осмотров (исследований) должен определяться по дате на конец года
				UslugaComplex_Date.setMonth(11); // декабрь
				UslugaComplex_Date.setDate(31); // 31-ое число
			} else if (getRegionNick() != 'perm' && !Ext.isEmpty(newDVNDate) && !Ext.isEmpty(base_form.findField('EvnPLDispDop13_firstConsDate').getValue()) && Date.parseDate(base_form.findField('EvnPLDispDop13_firstConsDate').getValue(), 'd.m.Y') < newDVNDate && base_form.findField('DispClass_id').getValue() == 2) {
				// Список осмотров (исследований) должен определяться по дате согласия из карты первого этапа ДВН
				UslugaComplex_Date = Date.parseDate(base_form.findField('EvnPLDispDop13_firstConsDate').getValue(), 'd.m.Y');
			}

			var personinfo = win.PersonInfoPanel;
			var params = {
				archiveRecord: this.archiveRecord,
				action: action,
				object: 'EvnPLDispDop13',
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
					EvnVizitDispDop_pid: base_form.findField('EvnPLDispDop13_id').getValue(),
					PersonEvn_id: base_form.findField('PersonEvn_id').getValue(),
					Server_id: base_form.findField('Server_id').getValue(),
					EvnUslugaDispDop_id: record.get('EvnUslugaDispDop_id')
				},
				DopDispInfoConsent_id: record.get('DopDispInfoConsent_id'),
				SurveyTypeLink_id: record.get('SurveyTypeLink_id'),
				SurveyType_Code: record.get('SurveyType_Code'),
				SurveyType_Name: record.get('SurveyType_Name'),
				SurveyType_IsVizit: record.get('SurveyType_IsVizit'),
				disableDidDate: (!Ext.isEmpty(base_form.findField('EvnPLDispDop13Sec_id').getValue()) && record.get('SurveyType_Code') == 19)?true:false,
				type: 'DispDop',
				UslugaComplex_Date: UslugaComplex_Date,
				ShowDeseaseStageCombo: getRegionNick().inlist(['perm','buryatiya','kareliya'])?true:false,
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
							base_form.findField('EvnPLDispDop13_IsSuspectZNO').setValue(2);
						} else {
							base_form.findField('EvnPLDispDop13_IsSuspectZNO').clearValue();
						}
						base_form.findField('EvnPLDispDop13_IsSuspectZNO').fireEvent('change', base_form.findField('EvnPLDispDop13_IsSuspectZNO'), base_form.findField('EvnPLDispDop13_IsSuspectZNO').getValue());
					}
					// #172213
					if (getRegionNick() == 'perm' && record.get('SurveyType_Code') == 19) {
						if (data.Diag_Code && data.Diag_Code.search(new RegExp("^(C|D0)", "i")) >= 0) {
							base_form.findField('EvnPLDispDop13_IsSuspectZNO').disable();
							base_form.findField('EvnPLDispDop13_IsSuspectZNO').clearValue();
							base_form.findField('EvnPLDispDop13_IsSuspectZNO').fireEvent('change', base_form.findField('EvnPLDispDop13_IsSuspectZNO'), base_form.findField('EvnPLDispDop13_IsSuspectZNO').getValue());
							Ext.getCmp('EPLDD13EF_PrintKLU').enable();
						}
					}
					// обновляем грид с впервые выявленными
					win.EvnDiagDopDispFirstGrid.getGrid().getStore().reload();
					win.EvnDiagDopDispBeforeGrid.getGrid().getStore().reload();

					if ( record.get('SurveyType_Code') == 4 && !Ext.isEmpty(data.body_mass_index) ) {
						base_form.findField('body_mass_index').fireEvent('change', base_form.findField('body_mass_index'), base_form.findField('body_mass_index').getValue());
					}

				}

			};

			if (getRegionNick().inlist(['ekb','astra']) && (base_form.findField('DispClass_id').getValue() == 2 || params.SurveyType_Code == 19) && record.get('DopDispInfoConsent_IsEarlier') != 2) {
				params.minDate = '01.01.'+Ext.util.Format.date(EvnPLDispDop13_consDate, 'Y');
				params.maxDate = '31.12.'+Ext.util.Format.date(EvnPLDispDop13_consDate, 'Y');
			} else if (getRegionNick().inlist(['kareliya']) && base_form.findField('DispClass_id').getValue() == 1 && params.SurveyType_Code == 19) {
				params.minDate = '01.01.'+Ext.util.Format.date(EvnPLDispDop13_consDate, 'Y');
				params.maxDate = '31.12.'+Ext.util.Format.date(EvnPLDispDop13_consDate, 'Y');
			}
			// @task https://redmine.swan.perm.ru/issues/66282
			else if ( getRegionNick().inlist([ 'perm' ]) && !Ext.isEmpty(EvnPLDispDop13_consDate) && typeof(EvnPLDispDop13_consDate) == 'object' ) {
				if ( record.get('DopDispInfoConsent_IsEarlier') == 2 ) {
					params.maxDate = Ext.util.Format.date(EvnPLDispDop13_consDate.add(Date.DAY, -1), 'd.m.Y');
				}
				else {
					params.minDate = Ext.util.Format.date(EvnPLDispDop13_consDate, 'd.m.Y');
				}
			}

			params.soputDiagsFirst = [];
			win.EvnDiagDopDispFirstGrid.getGrid().getStore().each(function(rec) {
				if ( !Ext.isEmpty(rec.get('DiagSetClass_id')) && rec.get('DiagSetClass_id') == 3 ) {
					params.soputDiagsFirst.push(rec.get('Diag_id'));
				}
			});

			getWnd('swEvnUslugaDispDop13EditWindow').show(params);
		}		
	},
	printRouteCard: function(){
		var base_form = this.EvnPLDispDop13FormPanel.getForm();

		var person_id = base_form.findField('Person_id').getValue();
		var evnpldispdop_setdate = base_form.findField('EvnPLDispDop13_setDate').getValue();
		var evnpldispdop_year = new Date(evnpldispdop_setdate.replace(/(\d+).(\d+).(\d+)/, '$3/$2/$1')).getFullYear();
        var paramIsEarlier = 2;
        if(Ext.globalOptions.dispprof.do_not_show_unchecked_research){
            paramIsEarlier = 1;
        }
		printBirt({
			'Report_FileName': 'pan_RouteCardDD.rptdesign',
			'Report_Params': '&paramPerson=' + person_id + '&paramDispClass=1&paramYear=' + evnpldispdop_year + '&paramIsEarlier=' + paramIsEarlier,
			'Report_Format': 'pdf'
		});
		printBirt({
			'Report_FileName': 'pan_RouteCardDD2.rptdesign',
			'Report_Params': '',
			'Report_Format': 'pdf'
		});
	},
	count85Percent: 0,
	get2018Dvn185Volume: function() {
		var win = this;
		var base_form = win.EvnPLDispDop13FormPanel.getForm();

		if (
			getRegionNick().inlist(['ufa', 'kz'])
			|| (
				getRegionNick().inlist(['krasnoyarsk', 'perm', 'pskov'])
				&& (
					win.findById('EPLDD13_EvnPLDispDop13_consDate').getValue() >= getNewDVNDate()
					|| base_form.findField('EvnPLDispDop13_IsNewOrder').getValue() == 2
				)
			)
		) {
			return false;
		}

		// достаём 85% из объёма
		this.count85Percent = 0;
		win.getLoadMask('Получение данных из объёма 2018_ДВН1_85...').show();
		Ext.Ajax.request({
			callback: function(options, success, response) {
				win.getLoadMask().hide();
				if ( success ) {
					var response_obj = Ext.util.JSON.decode(response.responseText);

					if ( response_obj.count85Percent ) {
						win.count85Percent = response_obj.count85Percent;
					} else {
						sw.swMsg.alert('Ошибка', 'Проверки на выполнение 85% исследований будут пропущены, администратору необходимо завести объём 2018_ДВН1_85');
					}
				}
				else {
					sw.swMsg.alert('Ошибка', 'Ошибка получения данных из объёма 2018_ДВН1_85');
				}
			},
			params: {
				Person_id: base_form.findField('Person_id').getValue(),
				onDate: (typeof win.findById('EPLDD13_EvnPLDispDop13_consDate').getValue() == 'object' ? Ext.util.Format.date(win.findById('EPLDD13_EvnPLDispDop13_consDate').getValue(), 'd.m.Y') : win.findById('EPLDD13_EvnPLDispDop13_consDate').getValue())
			},
			url: '/?c=EvnPLDispDop13&m=get2018Dvn185Volume'
		});
	},
	loadUslugaComplex: function() {
		var win = this;
		var base_form = win.EvnPLDispDop13FormPanel.getForm();

		if (getRegionNick() == 'buryatiya') {
			base_form.findField('UslugaComplex_id').clearValue();
			base_form.findField('UslugaComplex_id').getStore().baseParams.dispOnly = 1;
			base_form.findField('UslugaComplex_id').getStore().baseParams.DispClass_id = base_form.findField('DispClass_id').getValue();
			base_form.findField('UslugaComplex_id').getStore().baseParams.UslugaComplex_Date = (typeof win.findById('EPLDD13_EvnPLDispDop13_consDate').getValue() == 'object' ? Ext.util.Format.date(win.findById('EPLDD13_EvnPLDispDop13_consDate').getValue(), 'd.m.Y') : win.findById('EPLDD13_EvnPLDispDop13_consDate').getValue());
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
	loadDopDispInfoConsentGrid: function(EvnPLDispDop13_consDate) {
		var win = this;
		var base_form = win.EvnPLDispDop13FormPanel.getForm();

		win.dopDispInfoConsentGrid.getGrid().getStore().removeAll();
		win.dopDispInfoConsentGrid.loadData({
			params: {
				Person_id: base_form.findField('Person_id').getValue(),
				DispClass_id: base_form.findField('DispClass_id').getValue(),
				EvnPLDispDop13_id: base_form.findField('EvnPLDispDop13_id').getValue(),
				EvnPLDispDop13_fid: base_form.findField('EvnPLDispDop13_fid').getValue(),
				EvnPLDispDop13_IsNewOrder: base_form.findField('EvnPLDispDop13_IsNewOrder').getValue(),
				EvnPLDispDop13_consDate: (typeof EvnPLDispDop13_consDate == 'object' ? Ext.util.Format.date(EvnPLDispDop13_consDate, 'd.m.Y') : EvnPLDispDop13_consDate)
			},
			globalFilters: {
				Person_id: base_form.findField('Person_id').getValue(),
				DispClass_id: base_form.findField('DispClass_id').getValue(),
				EvnPLDispDop13_id: base_form.findField('EvnPLDispDop13_id').getValue(),
				EvnPLDispDop13_fid: base_form.findField('EvnPLDispDop13_fid').getValue(),
				EvnPLDispDop13_IsNewOrder: base_form.findField('EvnPLDispDop13_IsNewOrder').getValue(),
				EvnPLDispDop13_consDate: (typeof EvnPLDispDop13_consDate == 'object' ? Ext.util.Format.date(EvnPLDispDop13_consDate, 'd.m.Y') : EvnPLDispDop13_consDate)
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
	initComponent: function() {
		var win = this;
		
		this.dopDispInfoConsentGrid = new sw.Promed.ViewFrame({
			autoLoadData: false,
			id: 'EPLDD13EF_dopDispInfoConsentGrid',
			dataUrl: '/?c=EvnPLDispDop13&m=loadDopDispInfoConsent',
			region: 'center',
			height: 200,
			title: '',
			toolbar: false,
			saveAtOnce: false, 
			saveAllParams: false,
			focusOn: {
				name: 'EPLDD13EF_evnUslugaDispDopGrid',
				type: 'grid'
			},
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
				{ name: 'SurveyTypeLink_IsUslPack', type: 'int', hidden: true },
				{ name: 'DopDispInfoConsent_IsAgeCorrect', type: 'int', hidden: true },
				{ name: 'SurveyType_Name', type: 'string', sortable: false, header: 'Осмотр, исследование', id: 'autoexpand' },
				{ name: 'DopDispInfoConsent_IsEarlier', sortable: false, type: 'checkcolumnedit', isparams: true, header: 'Пройдено ранее', width: 180 },
				{ name: 'DopDispInfoConsent_IsAgree', sortable: false, type: 'checkcolumnedit', isparams: true, header: 'Согласие гражданина', width: 180 },
				{ name: 'DopDispInfoConsent_IsImpossible', sortable: false, type: 'checkcolumnedit', isparams: true, header: 'Невозможно по показаниям', width: 180 }
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
						if ( !Ext.isEmpty(rec.get('SurveyType_Code')) ) {
							if (!rec.get('SurveyType_Code').inlist([1,48])) {
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
									if (rec.get('DopDispInfoConsent_IsImpossible') != 'hidden') {
										rec.set('DopDispInfoConsent_IsImpossible', false);
									}
								}
								
								// если оба отмечены, то снимаем флаг "пройдено ранее", т.к. оба флага не могут быть одновременно подняты
								if (rec.get('DopDispInfoConsent_IsEarlier') == true && rec.get('DopDispInfoConsent_IsAgree') == true) {
									rec.set('DopDispInfoConsent_IsEarlier', false);
									if (rec.get('DopDispInfoConsent_IsImpossible') != 'hidden') {
										rec.set('DopDispInfoConsent_IsImpossible', false);
									}
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
				
				win.checkEvnPLDispDop13IsSaved();
			},
			onAfterEdit: function(o) {
				if (o && o.field) {
					if (o.record.get('SurveyTypeLink_IsDel') == 2) {
						o.record.set('DopDispInfoConsent_IsAgree', false);
						o.record.set('DopDispInfoConsent_IsEarlier', false);
						if (o.record.get('DopDispInfoConsent_IsImpossible') != 'hidden') {
							o.record.set('DopDispInfoConsent_IsImpossible', false);
						}
						o.value = false;
					}
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
						if (o.record.get('DopDispInfoConsent_IsEarlier') != 'hidden') {
							o.record.set('DopDispInfoConsent_IsEarlier', false);
						}
						if (o.record.get('DopDispInfoConsent_IsAgree') != 'hidden') {
							o.record.set('DopDispInfoConsent_IsAgree', false);
						}
					}
					
					// при снятии чекбокса в поле этап диспансеризации снимать все остальные и делать недоступными
					if (o.record.get('SurveyType_Code').inlist([1,48])) {
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
				{ name: 'action_print', handler: function() {win.printRouteCard();}}
			],
			onLoadData: function() {
				win.checkDopDispQuestionSaved();
				win.checkIsNewOrderButton();
				win.checkDisableZNOCombo();
				this.doLayout();
			},
			onRowSelect: function(){
				this.EvnUslugaDispDopPanel.expand();
			}.createDelegate(this),
			id: 'EPLDD13EF_evnUslugaDispDopGrid',
			dataUrl: '/?c=EvnPLDispDop13&m=loadEvnUslugaDispDopGrid',
			region: 'center',
			height: 200,
			title: '',
			toolbar: true,
			focusOn:{
				name:'EPLDD13EF_EvnDiagDopDispBefore',
				type: 'grid'
			},
			stringfields: [
				{ name: 'DopDispInfoConsent_id', type: 'int', header: 'ID', key: true },
				{ name: 'SurveyTypeLink_id', type: 'int', hidden: true },
				{ name: 'SurveyType_Code', type: 'int', hidden: true },
				{ name: 'SurveyType_IsVizit', type: 'int', hidden: true },
				{ name: 'UslugaComplex_Code', type: 'string', hidden: true },
				{ name: 'Diag_Code', type: 'string', hidden: true },
				{ name: 'EvnUslugaDispDop_id', type: 'int', hidden: true },
				{ name: 'SurveyTypeLink_IsUslPack', type: 'int', hidden: true },
				{ name: 'DopDispInfoConsent_IsEarlier', type: 'int', hidden: true },
				{ name: 'SurveyType_Name', type: 'string', header: 'Наименование осмотра (исследования)', id: 'autoexpand' },
				{ name: 'EvnUslugaDispDop_ExamPlace', type: 'string', header: 'Место проведения', width: 200 },
				//{ name: 'EvnUslugaDispDop_setDate', renderer: Ext.util.Format.dateRenderer('d.m.Y H:i:s'), header: 'Дата и время проведения', width: 200 },
				{ name: 'EvnUslugaDispDop_didDate', type: 'date', header: 'Дата выполнения', width: 100 },
				{ name: 'EvnUslugaDispDop_WithDirection', type: 'checkbox', header: 'Направление / назначение', width: 100 }
			]
		});
	
		this.PersonInfoPanel = new sw.Promed.PersonInformationPanel({
			button2Callback: function(callback_data) {
				var base_form = win.EvnPLDispDop13FormPanel.getForm();
				
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

							var base_form = win.EvnPLDispDop13FormPanel.getForm();

							var
								EvnPLDispDop13_IndexRep = parseInt(base_form.findField('EvnPLDispDop13_IndexRep').getValue()),
								EvnPLDispDop13_IndexRepInReg = parseInt(base_form.findField('EvnPLDispDop13_IndexRepInReg').getValue()),
								EvnPLDispDop13_IsPaid = parseInt(base_form.findField('EvnPLDispDop13_IsPaid').getValue());

							var diff = EvnPLDispDop13_IndexRepInReg - EvnPLDispDop13_IndexRep;

							if ( EvnPLDispDop13_IsPaid != 2 || EvnPLDispDop13_IndexRepInReg == 0 ) {
								return false;
							}

							if ( value == true ) {
								if ( diff == 1 || diff == 2 ) {
									EvnPLDispDop13_IndexRep = EvnPLDispDop13_IndexRep + 2;
								}
								else if ( diff == 3 ) {
									EvnPLDispDop13_IndexRep = EvnPLDispDop13_IndexRep + 4;
								}
							}
							else if ( value == false ) {
								if ( diff <= 0 ) {
									EvnPLDispDop13_IndexRep = EvnPLDispDop13_IndexRep - 2;
								}
							}

							base_form.findField('EvnPLDispDop13_IndexRep').setValue(EvnPLDispDop13_IndexRep);
						}
					},
					name: 'EvnPLDispDop13_RepFlag',
					xtype: 'checkbox'
				}, {
					allowBlank: false,
					fieldLabel: 'Дата подписания согласия/отказа',
					format: 'd.m.Y',
					id: 'EPLDD13_EvnPLDispDop13_consDate',
					listeners: {
						'change': function(field, newValue, oldValue) {
							var base_form = win.EvnPLDispDop13FormPanel.getForm();

							var xdate1 = new Date(2015,0,1);
							var xdate2 = new Date(2015,3,1);
							var xdate3 = new Date(2015,4,1);
							var newDVNDate = getNewDVNDate();

							if (!Ext.isEmpty(oldValue) && !Ext.isEmpty(newValue) && win.checkEvnPLDispDop13IsSaved() && (
								(newValue < xdate1 && oldValue >= xdate1) // при переходе через 01.01.2015
								|| (oldValue < xdate1 && newValue >= xdate1)
								|| (getRegionNick().inlist(['perm','kareliya','ekb', 'astra', 'krym']) && newValue < xdate2 && oldValue >= xdate2) // при переходе через 01.04.2015
								|| (getRegionNick().inlist(['perm','kareliya','ekb', 'astra', 'krym']) && oldValue < xdate2 && newValue >= xdate2)
								|| (getRegionNick().inlist(['ufa', 'pskov']) && newValue < xdate3 && oldValue >= xdate3) // при переходе через 01.05.2015
								|| (getRegionNick().inlist(['ufa', 'pskov']) && oldValue < xdate3 && newValue >= xdate3)
								|| ((getRegionNick() == 'perm' || base_form.findField('DispClass_id').getValue() != 2) && !Ext.isEmpty(newDVNDate) && newValue < newDVNDate && oldValue >= newDVNDate) // при переходе через 06.05.2019 / 01.06.2019
								|| ((getRegionNick() == 'perm' || base_form.findField('DispClass_id').getValue() != 2) && !Ext.isEmpty(newDVNDate) && oldValue < newDVNDate && newValue >= newDVNDate)
							)) {
								sw.swMsg.show({
									buttons: Ext.Msg.YESNO,
									fn: function ( buttonId ) {
										if ( buttonId == 'yes' ) {
											win.saveDopDispInfoConsentAfterLoad = true;
											win.blockSaveDopDispInfoConsent = true;
											win.loadDopDispInfoConsentGrid(newValue);
										} else {
											win.findById('EPLDD13_EvnPLDispDop13_consDate').setValue(oldValue);
										}
									},
									msg: 'При изменении даты подписания информированного согласия изменится набор осмотров / исследований. Заведенная информация по осмотрам / исследованиям может быть потеряна. Изменить дату?',
									title: 'Подтверждение'
								});
								return false;
							}

							win.loadUslugaComplex();
							win.get2018Dvn185Volume();
							win.checkIsNewOrderButton();
							win.ProphConsultGrid.setParam('date', newValue, false);

							base_form.findField('EvnPLDispDop13_IsTIA').hideContainer();
							base_form.findField('EvnPLDispDop13_IsRespiratory').hideContainer();
							base_form.findField('EvnPLDispDop13_IsLungs').hideContainer();
							base_form.findField('EvnPLDispDop13_IsTopGastro').hideContainer();
							base_form.findField('EvnPLDispDop13_IsBotGastro').hideContainer();
							base_form.findField('EvnPLDispDop13_IsSpirometry').hideContainer();
							base_form.findField('EvnPLDispDop13_IsHeartFailure').hideContainer();
							base_form.findField('EvnPLDispDop13_IsOncology').hideContainer();
							base_form.findField('EvnPLDispDop13_IsUseNarko').hideContainer();

							// возраст
							win.ProphConsultGrid.setParam('disallowedRiskFactorTypeIds', [18,22,23,24,25,26,27,28], false);
							var age = win.PersonInfoPanel.getFieldValue('Person_Age');
							if (base_form.findField('EPLDD13_EvnPLDispDop13_consDate').getValue() >= new Date(2018, 4, 1)) {
								if (age < 75) {
									win.ProphConsultGrid.setParam('disallowedRiskFactorTypeIds', [22,23,24,25,26,27,28], false);
									base_form.findField('EvnPLDispDop13_IsTIA').showContainer();
									base_form.findField('EvnPLDispDop13_IsRespiratory').showContainer();
									base_form.findField('EvnPLDispDop13_IsLungs').showContainer();
									base_form.findField('EvnPLDispDop13_IsTopGastro').showContainer();
									base_form.findField('EvnPLDispDop13_IsBotGastro').showContainer();
									base_form.findField('EvnPLDispDop13_IsSpirometry').showContainer();
									base_form.findField('EvnPLDispDop13_IsUseNarko').showContainer();
								} else {
									win.ProphConsultGrid.setParam('disallowedRiskFactorTypeIds', [], false);
									base_form.findField('EvnPLDispDop13_IsHeartFailure').showContainer();
									base_form.findField('EvnPLDispDop13_IsOncology').showContainer();
								}
							}
							
							var no1 = base_form.findField('PersonDisp_id').getValue();//если на дисп.учете, то недоступна 1-я группа здоровья #168522

							if (
								(!getRegionNick().inlist(['buryatiya', 'ufa', 'pskov']) && newValue >= xdate2)
								|| (getRegionNick().inlist(['ufa', 'pskov']) && newValue >= xdate3)
								|| getRegionNick().inlist(['buryatiya'])
							) {
								win.dopDispInfoConsentGrid.setColumnHidden('DopDispInfoConsent_IsImpossible', false);
								// Поле «Углубленное профилактическое консультирование» не отображать
								// Группа здоровья, выбор из справочника: I, II, IIIа, IIIб
								base_form.findField('EvnPLDispDop13_IsProphCons').hideContainer();
								base_form.findField('HealthKind_id').getStore().clearFilter();
								base_form.findField('HealthKind_id').lastQuery = '';
								if (getRegionNick().inlist(['buryatiya'])) {
									base_form.findField('HealthKind_id').getStore().filterBy(function (rec) {
										if (rec.get('HealthKind_id') && rec.get('HealthKind_id').inlist([no1?0:1, 2, 3, 6, 7])) {
											return true;
										} else {
											return false;
										}
									});
								} else {
									base_form.findField('HealthKind_id').getStore().filterBy(function (rec) {
										if (rec.get('HealthKind_id') && rec.get('HealthKind_id').inlist([no1?0:1, 2, 6, 7])) {
											return true;
										} else {
											return false;
										}
									});
								}
								base_form.findField('HealthKind_id').setValue(base_form.findField('HealthKind_id').getValue());
							} else {
								win.dopDispInfoConsentGrid.setColumnHidden('DopDispInfoConsent_IsImpossible', true);
								base_form.findField('EvnPLDispDop13_IsProphCons').showContainer();
								base_form.findField('HealthKind_id').getStore().clearFilter();
								base_form.findField('HealthKind_id').lastQuery = '';
								base_form.findField('HealthKind_id').getStore().filterBy(function (rec) {
									if (rec.get('HealthKind_id') && rec.get('HealthKind_id').inlist([no1?0:1,2,3])) {
										return true;
									} else {
										return false;
									}
								});
								base_form.findField('HealthKind_id').setValue(base_form.findField('HealthKind_id').getValue());
							}

							if (getRegionNick() == 'ekb') {
								// Дата информированного согласия должна быть в пределах выбранного года
								this.setMinValue('01.01.'+Ext.util.Format.date(newValue, 'Y'));
								this.setMaxValue('31.12.'+Ext.util.Format.date(newValue, 'Y'));
							}

							win.blockSaveDopDispInfoConsent = true;
							win.loadDopDispInfoConsentGrid(newValue);
						},
						'keydown':function(inp,e){
							//e.stopEvent();
							if(e.getKey() == Ext.EventObject.TAB){
								var base_form = win.EvnPLDispDop13FormPanel.getForm();
								var InfoConsentGrid = win.dopDispInfoConsentGrid.getGrid();
								InfoConsentGrid.focus();
								InfoConsentGrid.getSelectionModel().selectFirstRow();
								InfoConsentGrid.getView().focusRow(0);
							}
						}
					},
					plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
					width: 100,
					xtype: 'swdatefield'
				}, {
					fieldLabel: 'Случай обслужен мобильной бригадой',
					name: 'EvnPLDispDop13_IsMobile',
					xtype: 'checkbox',
					listeners: {
						'check': function(checkbox, value) {
							var base_form = win.EvnPLDispDop13FormPanel.getForm();
							
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
					fieldLabel: 'МО мобильной бригады',
					valueField: 'Lpu_id',
					hiddenName: 'Lpu_mid',
					xtype: 'sworgcombo',
					onTrigger1Click: function() {
						var combo = this;
						if (combo.disabled) {
							return false;
						}
						
						var base_form = win.EvnPLDispDop13FormPanel.getForm();
						
						getWnd('swOrgSearchWindow').show({
							enableOrgType: false,
							onlyFromDictionary: true,
							object: 'lpu',
							DispClass_id: base_form.findField('DispClass_id').getValue(),
							Disp_consDate: (typeof win.findById('EPLDD13_EvnPLDispDop13_consDate').getValue() == 'object' ? Ext.util.Format.date(win.findById('EPLDD13_EvnPLDispDop13_consDate').getValue(), 'd.m.Y') : win.findById('EPLDD13_EvnPLDispDop13_consDate').getValue()),
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
					name: 'EvnPLDispDop13_IsOutLpu',
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
								},
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
									var base_form = win.EvnPLDispDop13FormPanel.getForm();
									var paramDispClass = base_form.findField('DispClass_id').getValue();
									var paramEvnPLDispDop13 = base_form.findField('EvnPLDispDop13_id').getValue();
									if(paramEvnPLDispDop13) {
										var dialog_wnd = Ext.Msg.show({
											title: 'Вид согласия',
											msg:'Выберите вид согласия',
											buttons: {yes: "От имени пациента", no: "От имени законного представителя", cancel: "Отмена"},
											fn: function(btn){
												if (btn == 'cancel') {
													return;
												}
												if(btn == 'yes'){ //От имени пациента
													printBirt({
														'Report_FileName': 'EvnPLDopDispInfoConsent.rptdesign',
														'Report_Params': '&paramEvnPLDispDop13=' + paramEvnPLDispDop13 + '&paramDispClass=' + paramDispClass,
														'Report_Format': 'pdf'
													});
												}
												if(btn == 'no') { //От имени законного представителя
													printBirt({
														'Report_FileName': 'EvnPLDopDispInfoConsent_Deputy.rptdesign',
														'Report_Params': '&paramEvnPLDispDop13=' + paramEvnPLDispDop13 + '&paramDispClass=' + paramDispClass,
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
												var base_form = win.EvnPLDispDop13FormPanel.getForm();
												var consDate = win.findById('EPLDD13_EvnPLDispDop13_consDate').getValue();
												win.saveDopDispInfoConsentAfterLoad = true;
												win.blockSaveDopDispInfoConsent = true;
												base_form.findField('EvnPLDispDop13_IsNewOrder').setValue(2);
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
			id: 'EPLDD13EF_EvnDiagDopDispBefore',
			dataUrl: '/?c=EvnDiagDopDisp&m=loadEvnDiagDopDispGrid',
			region: 'center',
			height: 200,
			title: 'Ранее известные имеющиеся заболевания',
			toolbar: true,
			focusOn:{
				name:'EPLDD13EF_HeredityDiag',
				type: 'grid'
			},
			onRowSelect: function (sm,index,record) {
				this.EvnPLDispDop13MainResultsPanel.expand();

				if (getRegionNick() == 'vologda') {
					this.EvnDiagDopDispBeforeGrid.setActionDisabled('action_edit', !record || record.get('EvnDiagDopDisp_IsSystemDataAdd') == 2 || this.action == 'view');
				}
			}.createDelegate(this),
			stringfields: [
				{ name: 'EvnDiagDopDisp_id', type: 'int', header: 'ID', key: true },
				{ name: 'Diag_id', type: 'int', hidden: true },
				{ name: 'EvnDiagDopDisp_IsSystemDataAdd', type: 'int', hidden: true },
				{ name: 'Diag_Name', type: 'string', header: 'Наименование', id: 'autoexpand' },
				{ name: 'EvnDiagDopDisp_setDate', type: 'date', header: 'Дата постановки диагноза', width: 100 }
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
			id: 'EPLDD13EF_HeredityDiag',
			dataUrl: '/?c=HeredityDiag&m=loadHeredityDiagGrid',
			region: 'center',
			height: 200,
			title: 'Наследственность по заболеваниям',
			toolbar: true,
			focusOn:{
				name:'EPLDD13EF_EvnPLDispDop13_IsStenocard',
				type: 'other'
			},
			stringfields: [
				{ name: 'HeredityDiag_id', type: 'int', header: 'ID', key: true },
				{ name: 'Diag_id', type: 'int', hidden: true },
				{ name: 'HeredityType_id', type: 'int', hidden: true },
				{ name: 'Diag_Name', type: 'string', header: 'Наименование', id: 'autoexpand' },
				{ name: 'HeredityType_Name', type: 'string', header: 'Наследственность', width: 150 }
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
			focusOn:{
				name:'EPLDD13EF_NeedConsult',
				type: 'grid'
			},
			id: 'EPLDD13EF_ProphConsult',
			dataUrl: '/?c=ProphConsult&m=loadProphConsultGrid',
			region: 'center',
			height: 200,
			title: 'Показания к углубленному профилактическому консультированию',
			toolbar: true,
			stringfields: [
				{ name: 'ProphConsult_id', type: 'int', header: 'ID', key: true },
				{ name: 'RiskFactorType_id', type: 'int', hidden: true },
				{ name: 'RiskFactorType_Name', type: 'string', header: 'Фактор риска', id: 'autoexpand' }
			]
		});
		
		this.NeedConsultGrid = new sw.Promed.ViewFrame({
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
			focusOn:{
				name: 'EPLDD13EF_EvnPLDispDop13_IsSmoking',
				type: 'other'
			},
			id: 'EPLDD13EF_NeedConsult',
			dataUrl: '/?c=NeedConsult&m=loadNeedConsultGrid',
			region: 'center',
			height: 200,
			title: 'Показания к консультации врача-специалиста',
			toolbar: true,
			stringfields: [
				{ name: 'NeedConsult_id', type: 'int', header: 'ID', key: true },
				{ name: 'ConsultationType_id', type: 'int', hidden: true },
				{ name: 'Post_id', type: 'int', hidden: true },
				{ name: 'Post_Name', type: 'string', header: 'Врач-специалист', id: 'autoexpand' },
				{ name: 'ConsultationType_Name', type: 'string', header: 'Место проведения', width: 150 }
			]
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
			focusOn: {
				name: 'EPLDD13EF_EvnPLDispDop13_IsShortCons',
				type: 'other'
			},
			id: 'EPLDD13EF_EvnDiagDopDispFirst',
			dataUrl: '/?c=EvnDiagDopDisp&m=loadEvnDiagDopDispGrid',
			region: 'center',
			height: 200,
			title: 'Впервые выявленные заболевания',
			toolbar: true,
			stringfields: [
				{ name: 'EvnDiagDopDisp_id', type: 'int', header: 'ID', key: true },
				{ name: 'DiagSetClass_id', type: 'int', hidden: true },
				{ name: 'Diag_id', type: 'int', hidden: true },
				{ name: 'Diag_Name', type: 'string', header: 'Наименование', id: 'autoexpand' },
				{ name: 'DiagSetClass_Name', type: 'string', header: 'Тип', width: 150 }
			]
		});
		
		this.EvnPLDispDop13MainResultsPanel = new sw.Promed.Panel({
			bodyBorder: false,
			title: 'Основные результаты диспансеризации',
			border: false,
			collapsible: true,
			titleCollapse: true,
			animCollapse: false,
			buttonAlign: 'left',
			frame: false,
			labelAlign: 'right',
			labelWidth: 195,
			items: [{
					name: 'EvnPLDispDop13_id',
					value: 0,
					xtype: 'hidden'
				}, {
					name:'EvnPLDispDop13_IsPaid',
					xtype:'hidden'
				}, {
					name:'EvnPLDispDop13_IsNewOrder',
					xtype:'hidden'
				}, {
					name:'EvnPLDispDop13_IndexRep',
					xtype:'hidden'
				}, {
					name:'EvnPLDispDop13_IndexRepInReg',
					xtype:'hidden'
				}, {
					name: 'accessType',
					xtype: 'hidden'
				}, {
					name: 'EvnPLDispDop13Sec_id',
					xtype: 'hidden'
				}, {
					name: 'DispClass_id',
					value: 0,
					xtype: 'hidden'
				}, {
					name: 'EvnPLDispDop13_fid',
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
					name: 'EvnPLDispDop13_setDate',
					xtype: 'hidden'
				}, {
					name: 'EvnPLDispDop13_disDate',
					xtype: 'hidden'
				}, {
					name: 'EvnPLDispDop13_consDate',
					xtype: 'hidden'
				}, {
					name: 'EvnPLDispDop13_firstConsDate',
					xtype: 'hidden'
				}, {
					name: 'Server_id',
					value: 0,
					xtype: 'hidden'
				}, {
					name: 'PersonDisp_id',
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
					id: 'test_id',
					bodyStyle: 'padding: 5px',
					items: [
						{
							fieldLabel: 'Подозрение на ЗНО',
							hiddenName: 'EvnPLDispDop13_IsSuspectZNO',
							id: 'EPLDD13EF_EvnPLDispDop13_IsSuspectZNO',
							width: 100,
							xtype: 'swyesnocombo',
							listeners:{
								'change':function (combo, newValue, oldValue) {
									var index = combo.getStore().findBy(function (rec) {
										return (rec.get(combo.valueField) == newValue);
									});
									combo.fireEvent('select', combo, combo.getStore().getAt(index), index);

									if(newValue == 2) {
										Ext.getCmp('EPLDD13EF_PrintKLU').enable();
										Ext.getCmp('EPLDD13EF_PrintOnko').enable();
									} else {
										Ext.getCmp('EPLDD13EF_PrintKLU').disable();
										Ext.getCmp('EPLDD13EF_PrintOnko').disable();
									}
								},
								'select':function (combo, record, idx) {
									if (record && record.get('YesNo_id') == 2) {
										Ext.getCmp('EPLDD13EF_Diag_spid').showContainer();
										Ext.getCmp('EPLDD13EF_Diag_spid').setAllowBlank(!getRegionNick().inlist([ 'astra', 'perm', 'msk' ]));
										Ext.getCmp('EPLDD13EF_Diag_spid').setDisabled(false);
									} else {
										Ext.getCmp('EPLDD13EF_Diag_spid').setValue('');
										Ext.getCmp('EPLDD13EF_Diag_spid').hideContainer();
										Ext.getCmp('EPLDD13EF_Diag_spid').setAllowBlank(true);
									}
								}
							}
						}, {
							fieldLabel: 'Подозрение на диагноз',
							hiddenName: 'Diag_spid',
							id: 'EPLDD13EF_Diag_spid',
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
							onChange: function() {
								win.setDiagSpidComboDisabled();
							},
							width: 300,
							xtype: 'swdiagcombo'
						}, {
							fieldLabel: 'Подозрение на наличие стенокардии напряжения',
							hiddenName: 'EvnPLDispDop13_IsStenocard',
							id: 'EPLDD13EF_EvnPLDispDop13_IsStenocard',
							width: 100,
							xtype: 'swyesnocombo'
						}, {
							fieldLabel: 'Подозрение на ранее перенесенное нарушение мозгового кровообращения',
							hiddenName: 'EvnPLDispDop13_IsBrain',
							id: 'EPLDD13EF_EvnPLDispDop13_IsBrain',
							width: 100,
							xtype: 'swyesnocombo'
						}, {
							fieldLabel: 'Показания к проведению дуплексного сканирования брахицефальных артерий',
							hiddenName: 'EvnPLDispDop13_IsDoubleScan',
							width: 100,
							xtype: 'swyesnocombo'
						}, {
							fieldLabel: 'Подозрение на наличие туберкулеза, хронического заболевания легких или новообразования легких',
							hiddenName: 'EvnPLDispDop13_IsTub',
							width: 100,
							xtype: 'swyesnocombo'
						}, {
							fieldLabel: 'Показания к проведению эзофагогастродуоденоскопии',
							hiddenName: 'EvnPLDispDop13_IsEsophag',
							xtype: 'swyesnocombo',
							width: 100
						}, {
							additQueryFilter: "(Diag_Code like 'A%' or Diag_Code like 'B%')",
							fieldLabel: 'Подозрение на некоторые инфекционные и паразитарные болезни',
							isInfectionAndParasiteDiag: true,
							width: 300,
							hiddenName: 'Diag_sid',
							xtype: 'swdiagcombo'
						}, {
							fieldLabel: 'Имеется вероятность транзиторной ишемической атаки (ТИА) или перенесенного ОНМК',
							hiddenName: 'EvnPLDispDop13_IsTIA',
							width: 100,
							xtype: 'swyesnocombo'
						}, {
							fieldLabel: 'Имеется вероятность хронического заболевания нижних дыхательных путей',
							hiddenName: 'EvnPLDispDop13_IsRespiratory',
							width: 100,
							xtype: 'swyesnocombo'
						}, {
							fieldLabel: 'Подозрение на заболевания легких (Бронхоэктазы, онкопатология, туберкулез)',
							hiddenName: 'EvnPLDispDop13_IsLungs',
							width: 100,
							xtype: 'swyesnocombo'
						}, {
							fieldLabel: 'Вероятность заболеваний верхних отделов желудочно-кишечного тракта',
							hiddenName: 'EvnPLDispDop13_IsTopGastro',
							width: 100,
							xtype: 'swyesnocombo'
						}, {
							fieldLabel: 'Вероятность заболевания нижних отделов ЖКТ',
							hiddenName: 'EvnPLDispDop13_IsBotGastro',
							width: 100,
							xtype: 'swyesnocombo'
						}, {
							fieldLabel: 'Показания к проведению спирометрии',
							hiddenName: 'EvnPLDispDop13_IsSpirometry',
							width: 100,
							xtype: 'swyesnocombo'
						}, {
							fieldLabel: 'Вероятно наличие сердечной недостаточности',
							hiddenName: 'EvnPLDispDop13_IsHeartFailure',
							width: 100,
							xtype: 'swyesnocombo'
						}, {
							fieldLabel: 'Вероятность онкопатологии',
							hiddenName: 'EvnPLDispDop13_IsOncology',
							width: 100,
							xtype: 'swyesnocombo',
							listeners:{
								'keydown':function(inp,e){
									e.stopEvent();
									if(e.getKey() == Ext.EventObject.TAB){
										var base_form = win.EvnPLDispDop13FormPanel.getForm();
										var ProphConsultGrid = win.ProphConsultGrid.getGrid();
										ProphConsultGrid.focus();
										ProphConsultGrid.getSelectionModel().selectFirstRow();
										ProphConsultGrid.getView().focusRow(0);
									}
								}
							}
						},
						win.ProphConsultGrid,
						win.NeedConsultGrid
					]
				}),
				// подраздел "Поведенческие факторы риска"
				new Ext.Panel({
					title: "Поведенческие факторы риска",
					layout: 'form',
					bodyStyle: 'padding: 5px',
					defaults: {
						width: 100
					},
					items: [{
							fieldLabel: 'Курение',
							id: 'EPLDD13EF_EvnPLDispDop13_IsSmoking',
							hiddenName: 'EvnPLDispDop13_IsSmoking',
							xtype: 'swyesnocombo'
						}, {
							fieldLabel: 'Риск пагубного потребления алкоголя',
							hiddenName: 'EvnPLDispDop13_IsRiskAlco',
							xtype: 'swyesnocombo'
						}, {
							fieldLabel: 'Подозрение на зависимость от алкоголя',
							hiddenName: 'EvnPLDispDop13_IsAlcoDepend',
							xtype: 'swyesnocombo'
						}, {
							fieldLabel: 'Низкая физическая активность',
							hiddenName: 'EvnPLDispDop13_IsLowActiv',
							xtype: 'swyesnocombo'
						}, {
							fieldLabel: 'Нерациональное питание',
							hiddenName: 'EvnPLDispDop13_IsIrrational',
							xtype: 'swyesnocombo'
						}, {
							fieldLabel: 'Потребление наркотических средств без назначения врача',
							hiddenName: 'EvnPLDispDop13_IsUseNarko',
							xtype: 'swyesnocombo',
							listeners:{
								'keydown':function(inp,e){
									e.stopEvent();
									if(e.getKey() == Ext.EventObject.TAB){
										var base_form = win.EvnPLDispDop13FormPanel.getForm();
										var EvnDiagDopDispFirstGrid = win.EvnDiagDopDispFirstGrid.getGrid();
										EvnDiagDopDispFirstGrid.focus();
										EvnDiagDopDispFirstGrid.getSelectionModel().selectFirstRow();
										EvnDiagDopDispFirstGrid.getView().focusRow(0);
									}
								}
							}
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
					defaults: {
						width: 100
					},
					items: [{
						fieldLabel: 'Проведено индивидуальное краткое профилактическое консультирование',
						hiddenName:'EvnPLDispDop13_IsShortCons',
						id:'EPLDD13EF_EvnPLDispDop13_IsShortCons',
						value: 2,
						xtype: 'swyesnocombo'
					}, {
						allowDecimals: true,
						allowNegative: false,
						fieldLabel: 'Систолическое АД (мм рт.ст.)',
						name: 'systolic_blood_pressure',
						xtype: 'numberfield',
						enableKeyEvents: true
					}, {
						allowDecimals: true,
						allowNegative: false,
						fieldLabel: 'Диастолическое АД (мм рт.ст.)',
						name: 'diastolic_blood_pressure',
						xtype: 'numberfield',
						enableKeyEvents: true
					}, {
						fieldLabel: 'АД (мм рт.ст.)',
						name:'systolic_and_diastolic',
						readOnly: true,
						xtype: 'textfield'
						
					}, {
						fieldLabel: 'Гипотензивная терапия',
						hiddenName: 'EvnPLDispDop13_IsHypoten',
						xtype: 'swyesnocombo'
					}, {
						allowDecimals: true,
						allowNegative: false,
						fieldLabel: 'Вес (кг)',
						listeners: {
							'change': function(combo, newValue) {
								win.recountBodyMassIndex();
							}
						},
						maxValue: 500,
						readOnly: getRegionNick() != 'buryatiya',
						name: 'person_weight',
						xtype: 'numberfield',
						enableKeyEvents: true
					}, {
						allowDecimals: true,
						allowNegative: false,
						fieldLabel: 'Рост (см)',
						listeners: {
							'change': function(combo, newValue) {
								win.recountBodyMassIndex();
							}
						},
						maxValue: 275,
						readOnly: getRegionNick() != 'buryatiya',
						name: 'person_height',
						xtype: 'numberfield',
						enableKeyEvents: true
					}, {
						allowDecimals: true,
						allowNegative: false,
						fieldLabel: 'Окружность талии (см)',
						readOnly: getRegionNick() != 'buryatiya',
						name: 'waist_circumference',
						xtype: 'numberfield',
						enableKeyEvents: true
					}, {
						fieldLabel: 'Индекс массы тела (кг/м2)',
						listeners: {
							'change': function(field, newValue, oldValue) {
								// https://redmine.swan.perm.ru/issues/19835
								var base_form = win.EvnPLDispDop13FormPanel.getForm();
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
						fieldLabel: 'Риск сердечно-сосудистых заболеваний',
						hiddenName: 'CardioRiskType_id',
						width: 300,
						xtype: 'swcommonsprcombo'
					},
					{
						fieldLabel: 'Общий холестерин (ммоль/л)',
						name: 'total_cholesterol',
						readOnly: true,
						xtype: 'textfield'
					},
					{
						fieldLabel: 'Гиполипидемическая терапия',
						hiddenName: 'EvnPLDispDop13_IsLipid',
						xtype: 'swyesnocombo'
					},
					{
						fieldLabel: 'Глюкоза (ммоль/л)',
						name: 'glucose',
						readOnly: true,
						xtype: 'textfield'
					},
					{
						fieldLabel: 'Гипогликемическая терапия',
						hiddenName: 'EvnPLDispDop13_IsHypoglyc',
						xtype: 'swyesnocombo'
					},
					{
						fieldLabel: 'Подозрение на хроническое неинфекционное заболевание, требующее дообследования',
						width: 300,
						hiddenName: 'Diag_id',
						xtype: 'swdiagcombo'
					},
					{
						fieldLabel: 'Взят на диспансерное наблюдение',
						hiddenName: 'EvnPLDispDop13_IsDisp',
						xtype: 'swyesnocombo'
					},
					{
						comboSubject: 'NeedDopCure',
						fieldLabel: 'Нуждается в дополнительном лечении (обследовании)',
						hiddenName: 'NeedDopCure_id',
						width: 300,
						xtype: 'swcommonsprcombo'
					},
					/*{
						fieldLabel: 'Нуждается в стац. спец., в т.ч. высокотехнологичном дополнительном лечении (обследовании)',
						hiddenName: 'EvnPLDispDop13_IsStac',
						xtype: 'swyesnocombo'
					},*/
					{
						fieldLabel: 'Нуждается в санаторно-курортном лечении',
						hiddenName: 'EvnPLDispDop13_IsSanator',
						xtype: 'swyesnocombo'
					},
					// группбокс
					{
						autoHeight: true,
						style: 'padding: 0px;',
						title: 'Суммарный сердечно-сосудистый риск',
						width: 550,
						items: [{
							border: false,
							layout: 'column',
							items: [{
								border: false,
								layout: 'form',
								items: [{
									fieldLabel: 'Процент (%)',
									minValue: 0,
									maxValue: 100,
									name: 'EvnPLDispDop13_SumRick',
									width: 100,
									xtype: 'numberfield',
									listeners: {
										'change': function(field, newValue, oldValue) {
											var base_form = win.EvnPLDispDop13FormPanel.getForm();
											var risk_combo = base_form.findField('RiskType_id');
											risk_combo.getStore().clearFilter();
											risk_combo.lastQuery = '';
											
											if (!Ext.isEmpty(newValue) && newValue >= 0) {
												risk_combo.enable();
												risk_combo.store.filter('RiskType_id', 
													newValue == 0 ? 1 :
													newValue <= 4 ? 2 :
													newValue <= 9 ? 3 :
													4
												);
											} else {
												risk_combo.disable();
											}
											if(newValue != oldValue) risk_combo.clearValue();
										}
									}
								}]
							}, {
								border: false,
								layout: 'form',
								items: [{
									handler: function() {
										win.loadScoreField();
									},
									text: 'рассчитать',
									tooltip: 'Расчёт суммарного сердечно-сосудистого риска',
									xtype: 'button'
								}]
							}]
						},
						{
							comboSubject: 'RiskType',
							fieldLabel: 'Тип риска',
							hiddenName: 'RiskType_id',
							disabled: true,
							width: 300,
							xtype: 'swcommonsprcombo'
						}],
						xtype: 'fieldset'
					},
					{
						fieldLabel: 'Школа пациента проведена',
						hiddenName: 'EvnPLDispDop13_IsSchool',
						xtype: 'swyesnocombo'
					},
					{
						fieldLabel: 'Углублённое профилактическое консультирование проведено',
						hiddenName: 'EvnPLDispDop13_IsProphCons',
						xtype: 'swyesnocombo'
					},
					{
						fieldLabel: 'Группа здоровья',
						hiddenName: 'HealthKind_id',
						listeners: {
							'change': function(combo, newValue, oldValue) {
								if (!getRegionNick().inlist(['krym','pskov'])) {
									win.checkEvnPLDispDop13IsSaved();
								}
							}
						},
						loadParams: {params: {where: ' where HealthKind_Code in (1,2,3,6,7)'}},
						xtype: 'swhealthkindcombo'
					},
					{
						fieldLabel: 'Случай диспансеризации 1 этап закончен',
						hiddenName: 'EvnPLDispDop13_IsEndStage',
						allowBlank: false,
						xtype: 'swyesnocombo',
						listeners:{
							'select':function (combo, record) {
								win.verfGroup();
								win.setAllowDispDop();
								win.checkIsNewOrderButton();
							},
							'change': function() {
								win.checkForCostPrintPanel();
							}
						}
					},
					{
						fieldLabel: 'Направлен на 2 этап диспансеризации',
						hiddenName: 'EvnPLDispDop13_IsTwoStage',
						id: 'EPLDD13EF_IsTwoStageCombo',
						xtype: 'swyesnocombo',
						focusOn:{
							name:'EPLDD13EF_SaveButton',
							type:'button'
						},
						listeners:{
							'change': function(combo, newValue, oldValue) {
								if (getRegionNick().inlist(['krym','pskov'])) {
									win.checkEvnPLDispDop13IsSaved();
								}
							},
							'keydown':function(inp,e){
								if(e.shiftKey == false && e.getKey() == Ext.EventObject.TAB){
									e.stopEvent();
									Ext.getCmp('EPLDD13EF_SaveButton').focus(true, 200);
								}
							}
						}
					}]
				})
			],
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
					fieldLabel: 'Номер справки/отказа',
					name:'EvnCostPrint_Number',
					readOnly: true,
					xtype: 'textfield'
				},{
					fieldLabel: 'Отказ',
					hiddenName: 'EvnCostPrint_IsNoPrint',
					width: 60,
					xtype: 'swyesnocombo'
				}]
			}]
		});

		this.EvnPLDispDop13FormPanel = new Ext.form.FormPanel({
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
				win.EvnPLDispDop13MainResultsPanel,
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
								this.doSave();
							}
							break;

						case Ext.EventObject.G:
							this.printEvnPLDispDop13();
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
				{ name: 'EvnPLDispDop13_id' },
				{ name: 'EvnPLDispDop13_IsPaid' },
				{ name: 'EvnPLDispDop13_IsNewOrder' },
				{ name: 'EvnPLDispDop13_IndexRep' },
				{ name: 'EvnPLDispDop13_IndexRepInReg' },
				{ name: 'accessType' },
				{ name: 'EvnPLDispDop13Sec_id' },
				{ name: 'DispClass_id' },
				{ name: 'EvnPLDispDop13_fid' },
				{ name: 'PayType_id' },
				{ name: 'Lpu_mid' },
				{ name: 'EvnPLDispDop13_IsMobile' },
				{ name: 'EvnPLDispDop13_IsOutLpu' },
				{ name: 'Person_id' },
				{ name: 'PersonEvn_id' },
				{ name: 'EvnPLDispDop13_setDate' },
				{ name: 'EvnPLDispDop13_consDate' },
				{ name: 'EvnPLDispDop13_firstConsDate' },
				{ name: 'Server_id' },
				{ name: 'EvnPLDispDop13_IsStenocard' },
				{ name: 'EvnPLDispDop13_IsShortCons' },
				{ name: 'EvnPLDispDop13_IsBrain' },
				{ name: 'EvnPLDispDop13_IsDoubleScan' },
				{ name: 'EvnPLDispDop13_IsTub' },
				{ name: 'EvnPLDispDop13_IsTIA'},
				{ name: 'EvnPLDispDop13_IsRespiratory'},
				{ name: 'EvnPLDispDop13_IsLungs'},
				{ name: 'EvnPLDispDop13_IsTopGastro'},
				{ name: 'EvnPLDispDop13_IsBotGastro'},
				{ name: 'EvnPLDispDop13_IsSpirometry'},
				{ name: 'EvnPLDispDop13_IsHeartFailure'},
				{ name: 'EvnPLDispDop13_IsOncology'},
				{ name: 'EvnPLDispDop13_IsEsophag' },
				{ name: 'EvnPLDispDop13_IsSmoking' },
				{ name: 'EvnPLDispDop13_IsRiskAlco' },
				{ name: 'EvnPLDispDop13_IsAlcoDepend' },
				{ name: 'EvnPLDispDop13_IsLowActiv' },
				{ name: 'EvnPLDispDop13_IsIrrational' },
				{ name: 'EvnPLDispDop13_IsUseNarko' },
				{ name: 'systolic_blood_pressure' },
				{ name: 'diastolic_blood_pressure' },
				{ name: 'person_weight' },
				{ name: 'person_height' },
				{ name: 'body_mass_index' },
				{ name: 'waist_circumference' },
				{ name: 'total_cholesterol' },
				{ name: 'glucose' },
				{ name: 'EvnPLDispDop13_IsHypoten' },
				{ name: 'EvnPLDispDop13_IsLipid' },
				{ name: 'EvnPLDispDop13_IsHypoglyc' },
				{ name: 'Diag_id' },
				{ name: 'Diag_sid' },
				{ name: 'EvnPLDispDop13_IsDisp' },
				{ name: 'NeedDopCure_id' },
				// { name: 'EvnPLDispDop13_IsStac' },
				{ name: 'EvnPLDispDop13_IsSanator' },
				{ name: 'EvnPLDispDop13_SumRick' },
				{ name: 'RiskType_id' },
				{ name: 'EvnPLDispDop13_IsSchool' },
				{ name: 'EvnPLDispDop13_IsProphCons' },
				{ name: 'HealthKind_id' },
				{ name: 'EvnPLDispDop13_IsEndStage' },
				{ name: 'EvnPLDispDop13_IsTwoStage' },
				{ name: 'CardioRiskType_id' },
				{ name: 'EvnCostPrint_setDT' },
				{ name: 'EvnCostPrint_Number' },
				{ name: 'EvnCostPrint_IsNoPrint' },
				{ name: 'EvnPLDispDop13_IsSuspectZNO' },
				{ name: 'Diag_spid' },
				{ name: 'PersonDisp_id' }
			]),
			url: '/?c=EvnPLDispDop13&m=saveEvnPLDispDop13'
		});
		Ext.apply(this, {
			items: [
				// паспортная часть человека
				win.PersonInfoPanel,
				win.EvnPLDispDop13FormPanel
			],
			buttons: [{
				handler: function() {
					this.doSave();
				}.createDelegate(this),
				iconCls: 'save16',
				id: 'EPLDD13EF_SaveButton',
				onTabAction: function() {
					Ext.getCmp('EPLDD13EF_PrintButton').focus(true, 200);
				},
				onShiftTabAction: function() {
					Ext.getCmp('EPLDD13EF_IsTwoStageCombo').focus(true, 200);
				},
				tabIndex: 2406,
				text: BTN_FRMSAVE
			}, {
				handler: function() {
					this.printEvnPLDispDop13Passport();
				}.createDelegate(this),
				iconCls: 'print16',
				id: 'EPLDD13EF_PrintPassportButton',
				tabIndex: 2408,
				text: 'Печать паспорта здоровья'
			}, {
				hidden: true,
				handler: function() {
					this.printEvnPLDispDop13();
				}.createDelegate(this),
				iconCls: 'print16',
				id: 'EPLDD13EF_PrintButton',
				tabIndex: 2407,
				text: 'Печать карты диспансеризации'
			}, {
				hidden: getRegionNick() == 'kz',
				handler: function() {
					this.printKLU();
				}.createDelegate(this),
				iconCls: 'print16',
				id: 'EPLDD13EF_PrintKLU',
				tabIndex: 2409,
				text: 'Печать КЛУ при ЗНО'
			}, {
				hidden: getRegionNick() != 'ekb',
				handler: function() {
					this.printOnko();
				}.createDelegate(this),
				iconCls: 'print16',
				id: 'EPLDD13EF_PrintOnko',
				tabIndex: 2410,
				text: 'Печать выписки по онкологии'
			}, '-',
			HelpButton(this, -1),
			{
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'cancel16',
				id: 'EPLDD13EF_CancelButton',
				tabIndex: 2411,
				text: BTN_FRMCANCEL
			}]
		});
		sw.Promed.swEvnPLDispDop13EditWindow.superclass.initComponent.apply(this, arguments);
		this.findById('EPLDD13EF_dopDispInfoConsentGrid').addListenersFocusOnFields();
		this.findById('EPLDD13EF_evnUslugaDispDopGrid').addListenersFocusOnFields();
		this.findById('EPLDD13EF_EvnDiagDopDispBefore').addListenersFocusOnFields();
		this.findById('EPLDD13EF_HeredityDiag').addListenersFocusOnFields();
		this.findById('EPLDD13EF_ProphConsult').addListenersFocusOnFields();
		this.findById('EPLDD13EF_NeedConsult').addListenersFocusOnFields();
		this.findById('EPLDD13EF_EvnDiagDopDispFirst').addListenersFocusOnFields();
	},
	loadScoreField: function() {
		// расчёт поля SCORE
		var win = this;
		var base_form = this.EvnPLDispDop13FormPanel.getForm();
		
		win.getLoadMask('Расчёт суммарного сердечно-сосудистого риска').show();
		Ext.Ajax.request({
			callback: function(options, success, response) {
				win.getLoadMask().hide();
				if ( success ) {
					var response_obj = Ext.util.JSON.decode(response.responseText);

					if ( response_obj.SCORE ) {
						base_form.findField('EvnPLDispDop13_SumRick').setValue(response_obj.SCORE);
						base_form.findField('EvnPLDispDop13_SumRick').fireEvent('change', base_form.findField('EvnPLDispDop13_SumRick'), base_form.findField('EvnPLDispDop13_SumRick').getValue());
					}
				}
				else {
					sw.swMsg.alert('Ошибка', 'Ошибка расчёта суммарного сердечно-сосудистого риска');
				}
			},
			params: {
				EvnPLDisp_id: base_form.findField('EvnPLDispDop13_id').getValue()
			},
			url: '/?c=EvnUslugaDispDop&m=loadScoreField'
		});
	},
	checkIsNewOrderButton: function() {
		var win = this;
		var hasEvnUslugaAfterNewDVNDate = false;
		var newDVNDate = getNewDVNDate();
		var base_form = win.EvnPLDispDop13FormPanel.getForm();
		win.evnUslugaDispDopGrid.getGrid().getStore().each(function (rec) {
			if (!Ext.isEmpty(rec.get('EvnUslugaDispDop_didDate')) && rec.get('EvnUslugaDispDop_didDate') >= newDVNDate) {
				hasEvnUslugaAfterNewDVNDate = true;
			}
		});

		if (
			getRegionNick().inlist(['astra', 'krasnoyarsk', 'krym', 'perm', 'vologda'])
			&& base_form.findField('EvnPLDispDop13_IsNewOrder').getValue() != 2 // Для карты отсутствует признак «Переопределён набор услуг по новому приказу»;
			&& !Ext.isEmpty(newDVNDate)
			&& win.findById('EPLDD13_EvnPLDispDop13_consDate').getValue() < newDVNDate // Дата согласия меньше даты нового ДВН
			&& (
				hasEvnUslugaAfterNewDVNDate
				|| base_form.findField('EvnPLDispDop13_IsEndStage').getValue() != 2 // В поле случай закончен установлено значение «Нет»
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
			var win = Ext.getCmp('EvnPLDispDop13EditWindow');
			var tabbar = win.findById('EPLDD13EF_EvnPLTabbar');

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
	printEvnPLDispDop13: function(print_blank) {
        var wnd = this;
        var print = function () {
            var base_form = wnd.EvnPLDispDop13FormPanel.getForm();
            var person_id = base_form.findField('Person_id').getValue();
            var evnpldispdop_setdate = base_form.findField('EvnPLDispDop13_setDate').getValue();
            var evnpldispdop_year = new Date(evnpldispdop_setdate.replace(/(\d+).(\d+).(\d+)/, '$3/$2/$1')).getFullYear();
			var template = 'pan_DispCard_2015.rptdesign';
			if(base_form.findField('EPLDD13_EvnPLDispDop13_consDate').getValue() < new Date(2015,3,1))
			{
				template = 'pan_DispCard.rptdesign';
			}
			printBirt({
				'Report_FileName': template,
				'Report_Params': '&paramPerson=' + person_id + '&paramDispClass=1&paramYear=' + evnpldispdop_year,
				'Report_Format': 'pdf'
			});
        };

        if ( 'add' == this.action || 'edit' == this.action ) {
            this.doSave({
                print: true,
                callback: print
            });
        }
        else if ( 'view' == this.action ) {
            print();
        }
	},
	printEvnPLDispDop13Passport: function(print_blank) {
        var wnd = this;
        var print = function() {
            var base_form = wnd.EvnPLDispDop13FormPanel.getForm();
            var evn_pl_id = base_form.findField('EvnPLDispDop13_id').getValue();
            var server_id = base_form.findField('Server_id').getValue();
			if( !getGlobalOptions().region.nick.inlist([ 'pskov', 'ufa', 'buryatiya' ]) ){
			var dialog_wnd = Ext.Msg.show({ //shorev: Добавил в рамках задачи https://redmine.swan.perm.ru/issues/26202
				title: 'Печать заболеваний',
				msg:'Печатать данные в раздел 10. Установленные заболевания?',
				buttons: {yes: "Да", no: "Нет", cancel: "Отмена"},
				fn: function(btn){
					if (btn == 'cancel') {
						return;
					}
					if(btn == 'yes'){ //Выводим диагнозы (printDiag=1)
						window.open('/?c=EvnPLDispDop13&m=printEvnPLDispDop13Passport&EvnPLDispDop13_id=' + evn_pl_id + '&Server_id=' + server_id + '&printDiag=1', '_blank');
					}
					if(btn == 'no') { //Не выводим диагнозы (printDiag=0)
						window.open('/?c=EvnPLDispDop13&m=printEvnPLDispDop13Passport&EvnPLDispDop13_id=' + evn_pl_id + '&Server_id=' + server_id + '&printDiag=0', '_blank');
					}
				}
			});
			}
			else{ //Для Уфы - всегда выводим диагнозы (printDiag=1)
            	window.open('/?c=EvnPLDispDop13&m=printEvnPLDispDop13Passport&EvnPLDispDop13_id=' + evn_pl_id + '&Server_id=' + server_id + '&printDiag=1', '_blank');
			}
        };

        if ( 'add' == this.action || 'edit' == this.action ) {
            this.doSave({
                print: true,
                callback: print
            });
        }
        else if ( 'view' == this.action ) {
            print();
        }
	},
	printKLU: function() {
		var win = this;
		var base_form = win.EvnPLDispDop13FormPanel.getForm();
		
		var print = function() {
			var evn_pl_id = base_form.findField('EvnPLDispDop13_id').getValue();
			printBirt({
				'Report_FileName': 'CheckList_MedCareOnkoPatients.rptdesign',
				'Report_Params': '&Evn_id=' + evn_pl_id, 
				'Report_Format': 'pdf'
			});
		}
		if ( 'add' == this.action || 'edit' == this.action ) {
            this.doSave({
                print: true,
                callback: print
            });
        }
        else if ( 'view' == this.action ) {
            print();
        }
	},
	printOnko: function() {
		var win = this;
		var base_form = win.EvnPLDispDop13FormPanel.getForm();
		var print = function() {
			var evn_pl_id = base_form.findField('EvnPLDispDop13_id').getValue();
			printBirt({
				'Report_FileName': 'WritingOut_MedCareOnkoPatients.rptdesign',
				'Report_Params': '&Evn_id=' + evn_pl_id,
				'Report_Format': 'pdf'
			});
		}
		if ( 'add' == this.action || 'edit' == this.action ) {
			this.doSave({
				print: true,
				callback: print
			});
		}
		else if ( 'view' == this.action ) {
			print();
		}
	},
	resizable: true,
	checkEvnPLDispDop13IsSaved: function() {
		var base_form = this.EvnPLDispDop13FormPanel.getForm();
		if (Ext.isEmpty(base_form.findField('EvnPLDispDop13_id').getValue()) || !this.PersonFirstStageAgree) {
			// дисаблим все разделы кроме информированного добровольного согласия, а также основную кнопки сохранить и печать
			this.EvnUslugaDispDopPanel.collapse();
			this.EvnUslugaDispDopPanel.disable();
			this.EvnPLDispDop13MainResultsPanel.collapse();
			this.EvnPLDispDop13MainResultsPanel.disable();
			this.DispAppointPanel.collapse();
			this.DispAppointPanel.disable();
			this.buttons[0].hide();
			this.buttons[1].hide();
			this.buttons[2].hide();
			if (Ext.isEmpty(base_form.findField('EvnPLDispDop13_id').getValue())) {
				this.DopDispInfoConsentPanel.items.items[2].items.items[1].disable(); //Закрываем кнопку "Печать"
			} else {
				this.DopDispInfoConsentPanel.items.items[2].items.items[1].enable(); //Открываем кнопку "Печать"
			}
			return false;
		} else {
			this.EvnUslugaDispDopPanel.expand();
			this.EvnUslugaDispDopPanel.enable();
			this.EvnPLDispDop13MainResultsPanel.expand();
			this.EvnPLDispDop13MainResultsPanel.enable();
			if (
				( getRegionNick() == 'krym' ) || //182073
				( getRegionNick() == 'pskov' && base_form.findField('EvnPLDispDop13_IsTwoStage').getValue() == 2 ) ||
				/*(getRegionNick().inlist([ 'krym', 'pskov' ]) && base_form.findField('EvnPLDispDop13_IsTwoStage').getValue() == 2) ||*/
				(
					!getRegionNick().inlist([ 'krym', 'pskov' ])
					&& !Ext.isEmpty(base_form.findField('HealthKind_id').getValue())
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
	saveDopDispInfoConsent: function(btn,options) {
		var win = this;

		if ( typeof options != 'object' ) {
			options = new Object();
		}

		var btn = win.saveConsentButton;
		if ( btn.disabled || win.action == 'view' ) {
			return false;
		}
		
		if (win.blockSaveDopDispInfoConsent) {
			win.saveDopDispInfoConsentAfterLoad = true;
			return false;
		}

		btn.disable();

		var base_form = win.EvnPLDispDop13FormPanel.getForm();

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

		if ( Ext.isEmpty(win.findById('EPLDD13_EvnPLDispDop13_consDate').getValue()) ) {
			btn.enable();
			win.getLoadMask().hide();

			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					win.findById('EPLDD13_EvnPLDispDop13_consDate').focus(true);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});

			return false;
		}

		if ( !win.findById('EPLDD13_EvnPLDispDop13_consDate').isValid() )
		{
			btn.enable();
			win.getLoadMask().hide();

			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					win.findById('EPLDD13_EvnPLDispDop13_consDate').focus(false);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		var EvnPLDispDop13_consDate = win.findById('EPLDD13_EvnPLDispDop13_consDate').getValue();

		if ( getRegionNick().inlist([ 'perm' ]) /*&& base_form.findField('DispClass_id').getValue() == 1*/ ) {
			var
				tmpIsEarlier = 0,
				tmpNotEarlier = 0;
			
			win.dopDispInfoConsentGrid.getGrid().getStore().each(function(ddicRec) {
				if ( Ext.isEmpty(ddicRec.get('DopDispInfoConsent_id')) || ddicRec.get('DopDispInfoConsent_id') < 0 ) {
					return false;
				}
				else if ( ddicRec.get('DopDispInfoConsent_IsAgree') == true || ddicRec.get('DopDispInfoConsent_IsEarlier') == true ) {
					// тянем соовтетствующую услугу
					win.evnUslugaDispDopGrid.getGrid().getStore().each(function (euddRec) {
						if (
							!Ext.isEmpty(euddRec.get('DopDispInfoConsent_id'))
							&& euddRec.get('DopDispInfoConsent_id') == ddicRec.get('DopDispInfoConsent_id')
							&& !Ext.isEmpty(euddRec.get('EvnUslugaDispDop_didDate'))
						) {
							if ( ddicRec.get('DopDispInfoConsent_IsEarlier') == true && euddRec.get('EvnUslugaDispDop_didDate') >= EvnPLDispDop13_consDate ) {
								tmpIsEarlier = tmpIsEarlier + 1;
							}
							else if ( ddicRec.get('DopDispInfoConsent_IsAgree') == true && euddRec.get('EvnUslugaDispDop_didDate') < EvnPLDispDop13_consDate ) {
								tmpNotEarlier = tmpNotEarlier + 1;
							}
						}
					});
				}
			});

			if ( tmpIsEarlier > 0 || tmpNotEarlier > 0 )  {
				var errorText = "Обнаружено несоответствие даты проведения осмотра/исследования и даты подписания согласия:<br />";

				if ( tmpIsEarlier > 0 ) {
					errorText = errorText + 'Кол-во услуг с отметкой "Пройдено ранее": ' + tmpIsEarlier.toString() + '<br />';
				}

				if ( tmpNotEarlier > 0 ) {
					errorText = errorText + 'Кол-во услуг без отметки "Пройдено ранее": ' + tmpNotEarlier.toString() + '<br />';
				}

				btn.enable();
				sw.swMsg.alert('Ошибка', errorText);
				return false;
			}
		}

		var xdate3 = new Date(2015,4,1);
		if ( getRegionNick().inlist([ 'penza' ]) || (getRegionNick().inlist([ 'pskov' ]) && base_form.findField('DispClass_id').getValue() == 1 && win.findById('EPLDD13_EvnPLDispDop13_consDate').getValue() >= xdate3 ) ) {
			// Осмотр врача-терапевта должен быть с пометкой «Согласие»
			var IsAgree = false;
			win.dopDispInfoConsentGrid.getGrid().getStore().each(function(rec) {
				if (!Ext.isEmpty(rec.get('SurveyType_Code')) && rec.get('SurveyType_Code').inlist([19])) {
					if (rec.get('DopDispInfoConsent_IsAgree') == true) {
						IsAgree = true;
					}
				}
			});

			if (!IsAgree) {
				btn.enable();
				sw.swMsg.alert('Ошибка', 'Осмотр врача-терапевта обязателен при проведении диспансеризации взрослого населения.');
				return false;
			}
		}

		// для Уфы: Если в карте ДВН количество согласий (кол-во выбранных чекбоксов в поле "Согласие гражданина") для осмотров / исследований меньше 85%, то выводить сообщение "Количество осмотров\исследований составляет менее 85% от объема, установленного для данного возраста и пола. Ок", Сохранение отменить.
		if ( getRegionNick().inlist([ 'ufa' ]) && base_form.findField('DispClass_id').getValue() != 2 ) {
			var required = [];

			// Определяем согласие/отказ по диспансеризации в целом
			win.dopDispInfoConsentGrid.getGrid().getStore().each(function(rec) {
				if (!Ext.isEmpty(rec.get('SurveyType_Code')) && rec.get('SurveyType_Code').inlist([14,20,21,2,19,44,108,13]) && rec.get('DopDispInfoConsent_IsAgree') != true && rec.get('DopDispInfoConsent_IsEarlier') != true && rec.get('DopDispInfoConsent_IsImpossible') != true) {
					required.push(rec.get('SurveyType_Name'));
				}
			});


			var PayType_SysNick = base_form.findField('PayType_id').getFieldValue('PayType_SysNick');
			if (PayType_SysNick == 'oms' && win.findById('EPLDD13_EvnPLDispDop13_consDate').getValue() >= new Date(2019, 0, 1) && required.length > 0) {
				btn.enable();
				sw.swMsg.alert('Ошибка', 'При диспансеризации взрослого населения обязательно проведение следующих осмотров (исследований): <br> - ' + required.join('<br> - ') + '<br>Установите один из флагов: «Пройдено ранее», «Согласие гражданина» или «Невозможно по показаниям» для перечисленных осмотров');
				return false;
			}
		}

		if ( getRegionNick().inlist([ 'ufa' ]) && base_form.findField('DispClass_id').getValue() == 2 ) {
			var required = [];

			// Определяем согласие/отказ по диспансеризации в целом
			win.dopDispInfoConsentGrid.getGrid().getStore().each(function(rec) {
				if (!Ext.isEmpty(rec.get('SurveyType_Code')) && rec.get('SurveyType_Code').inlist([19]) && rec.get('DopDispInfoConsent_IsAgree') != true && rec.get('DopDispInfoConsent_IsEarlier') != true) {
					required.push(rec.get('SurveyType_Name'));
				}
			});


			var PayType_SysNick = base_form.findField('PayType_id').getFieldValue('PayType_SysNick');
			if (PayType_SysNick == 'oms' && win.findById('EPLDD13_EvnPLDispDop13_consDate').getValue() >= new Date(2019, 0, 1) && required.length > 0) {
				btn.enable();
				sw.swMsg.alert('Ошибка', 'При диспансеризации взрослого населения обязательно проведение следующих осмотров (исследований): <br> - ' + required.join('<br> - ') + '<br>Установите флаг «Пройдено ранее» или «Согласие гражданина» для перечисленных осмотров');
				return false;
			}
		}

		if (
			getRegionNick() == 'adygeya'
			&& base_form.findField('PayType_id').getFieldValue('PayType_SysNick') == 'oms'
			&& win.findById('EPLDD13_EvnPLDispDop13_consDate').getValue() >= new Date(2020, 0, 1)
			//только первый этап
			&& (Ext.isEmpty(base_form.findField('EvnPLDispDop13_IsTwoStage').getValue()) || base_form.findField('EvnPLDispDop13_IsTwoStage').getValue() != 2)
		) {
			var required = [];

			// Определяем согласие/отказ по диспансеризации в целом
			win.dopDispInfoConsentGrid.getGrid().getStore().each(function(rec) {
				if (!Ext.isEmpty(rec.get('SurveyType_Code')) && rec.get('SurveyType_Code').inlist([2, 13, 14, 19, 20, 21, 31]) && rec.get('DopDispInfoConsent_IsAgree') != true && rec.get('DopDispInfoConsent_IsEarlier') != true && rec.get('DopDispInfoConsent_IsImpossible') != true) {
					required.push(rec.get('SurveyType_Name'));
				}
			});
			
			if (required.length > 0) {
				btn.enable();
				sw.swMsg.alert('Ошибка', 'При диспансеризации взрослого населения обязательно проведение следующих осмотров (исследований): <br> - ' + required.join('<br> - ') + '<br>Установите один из флагов: «Пройдено ранее», «Согласие гражданина» или «Невозможно по показаниям» для перечисленных осмотров');
				return false;
			}
		}
		// https://redmine.swan.perm.ru/issues/61990
		var xdate2 = new Date(2015,3,1);
		if (base_form.findField('DispClass_id').getValue() != 2 && win.findById('EPLDD13_EvnPLDispDop13_consDate').getValue() >= xdate2) {
			// Проверяем сохранён ли хоть один осмотр/исследование
			var kolvoSaved = 0;
			win.evnUslugaDispDopGrid.getGrid().getStore().each(function(rec) {
				if (!Ext.isEmpty(rec.get('DopDispInfoConsent_id')) && !Ext.isEmpty(rec.get('EvnUslugaDispDop_didDate')) ) {
					kolvoSaved++;
				}
			});

			// 1. собираем пакеты
			var Packets = {};
			win.dopDispInfoConsentGrid.getGrid().getStore().each(function(rec) {
				if (!Ext.isEmpty(rec.get('SurveyTypeLink_IsUslPack'))) {
					if (!Packets[rec.get('SurveyTypeLink_IsUslPack')]) {
						Packets[rec.get('SurveyTypeLink_IsUslPack')] = 1;
					} else {
						Packets[rec.get('SurveyTypeLink_IsUslPack')]++;
					}
				}
			});

			// 2. Считаем общее количество долей + количество долей осмотров/исследований с пометкой «Согласие» или «Пройдено ранее».
			var accepted = false;
			var kolvo = 0;
			var kolvoAccept = 0;
			win.dopDispInfoConsentGrid.getGrid().getStore().each(function(rec) {
				if (!Ext.isEmpty(rec.get('SurveyType_Code')) && !rec.get('SurveyType_Code').inlist([1,48])) {
					if (rec.get('DopDispInfoConsent_IsImpossible') != true) {
						if ( !Ext.isEmpty(rec.get('SurveyTypeLink_IsUslPack')) ) {
							if (Packets[rec.get('SurveyTypeLink_IsUslPack')]) {
								kolvo = kolvo + 1 / Packets[rec.get('SurveyTypeLink_IsUslPack')];
								if (rec.get('DopDispInfoConsent_IsAgree') == true || rec.get('DopDispInfoConsent_IsEarlier') == true) {
									kolvoAccept = kolvoAccept + 1 / Packets[rec.get('SurveyTypeLink_IsUslPack')];
								}
							}
						} else {
							kolvo++;
							if (rec.get('DopDispInfoConsent_IsAgree') == true || rec.get('DopDispInfoConsent_IsEarlier') == true) {
								kolvoAccept++;
							}
						}
					}
				} else if (!Ext.isEmpty(rec.get('SurveyType_Code')) && rec.get('SurveyType_Code').inlist([1,48])) {
					if (rec.get('DopDispInfoConsent_IsAgree') == true) {
						accepted = true;
					}
				}
			});

			if (accepted) {
				// МД (минимальное количество долей) = 85% от общего объема и отбросить дробную часть до ближайшего целого.
				var minKolvo = this.count85Percent;
				if (getRegionNick().inlist(['krasnoyarsk', 'perm', 'pskov']) && (
					win.findById('EPLDD13_EvnPLDispDop13_consDate').getValue() >= getNewDVNDate()
					|| base_form.findField('EvnPLDispDop13_IsNewOrder').getValue() == 2
				)) {
					minKolvo = Math.round(kolvo * 0.85);
				}
				// количество долей осмотров/исследований с пометкой «Согласие» или «Пройдено ранее»
				kolvoAccept = Math.floor(Math.round(kolvoAccept * 100) / 100);

				if (kolvoAccept < minKolvo) {
					if (kolvoSaved == 0) {
						// 1. Не сохранено ни одного осмотра/исследования в маршрутной карте
						// Если количество долей осмотров/исследований с пометкой «Согласие» или «Пройдено ранее» составляют менее минимального количества долей,
						// то выводить сообщение «Количество осмотров/исследований недостаточно для проведения диспансеризации взрослого населения. ОК».
						// При нажатии «ОК», сообщение закрыть, сохранение информированного согласия отменить.
						sw.swMsg.alert('Ошибка', 'Количество осмотров/исследований недостаточно для проведения диспансеризации взрослого населения');
					} else {
						// 2. Сохранен хотя бы один осмотр/исследование в маршрутной карте
						// Если количество долей осмотров/исследований с пометкой «Согласие» или «Пройдено ранее» составляют менее минимального количества долей,
						// выводить сообщение «Количество отмеченных осмотров/исследований недостаточно для проведения диспансеризации взрослого населения.
						// Перенести проведенные осмотры/исследования в профилактический осмотр? Перенести в профилактический осмотр / Отмена».
						win.transferEvnPLDispDopToEvnPLDispProf();
					}
					btn.enable();
					return false;
				}
			}
		}
		
		win.getLoadMask('Сохранение информированного добровольного согласия').show();
		// берём все записи из грида и посылаем на сервер, разбираем ответ
		// на сервере создать саму карту EvnPLDispDop13, если EvnPLDispDop13_id не задано, сохранить её информ. согласие DopDispInfoConsent, вернуть EvnPLDispDop13_id
		var grid = win.dopDispInfoConsentGrid.getGrid();
		var params = {};

		params.EvnPLDispDop13_consDate = (typeof win.findById('EPLDD13_EvnPLDispDop13_consDate').getValue() == 'object' ? Ext.util.Format.date(win.findById('EPLDD13_EvnPLDispDop13_consDate').getValue(), 'd.m.Y') : win.findById('EPLDD13_EvnPLDispDop13_consDate').getValue());
		params.EvnPLDispDop13_IsMobile = (base_form.findField('EvnPLDispDop13_IsMobile').checked) ? true : null;
		params.EvnPLDispDop13_IsOutLpu = (base_form.findField('EvnPLDispDop13_IsOutLpu').checked) ? true : null;
		params.Lpu_mid = base_form.findField('Lpu_mid').getValue();
		params.PersonEvn_id = base_form.findField('PersonEvn_id').getValue();
		params.Person_id = base_form.findField('Person_id').getValue();
		params.Server_id = base_form.findField('Server_id').getValue();
		params.EvnPLDispDop13_id = base_form.findField('EvnPLDispDop13_id').getValue();
		params.EvnPLDispDop13_fid = base_form.findField('EvnPLDispDop13_fid').getValue();
		params.EvnPLDispDop13_IsNewOrder = base_form.findField('EvnPLDispDop13_IsNewOrder').getValue();
		params.DispClass_id = base_form.findField('DispClass_id').getValue();
		params.PayType_id = base_form.findField('PayType_id').getValue();
		
		params.DopDispInfoConsentData = Ext.util.JSON.encode(getStoreRecords( grid.getStore(), {
			exceptionFields: [
				'SurveyType_Name'
			]
		}));

		/*
		if (base_form.findField('DispClass_id').getValue() == 1 && win.Year != Ext.util.Format.date(Date.parseDate(params.EvnPLDispDop13_consDate, 'd.m.Y'), 'Y')) {
			btn.enable();
			win.getLoadMask().hide();
			sw.swMsg.alert('Ошибка', 'Дата начала не соответствует году прохождения диспансеризации');
			return false;
		}
		*/

		if ( !Ext.isEmpty(options.AttachmentAnswer) ) {
			params.AttachmentAnswer = 1;
		}

		Ext.Ajax.request(
		{
			url: '/?c=EvnPLDispDop13&m=saveDopDispInfoConsent',
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
					if ( !Ext.isEmpty(answer.Alert_Msg) ) {
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
					}
					if (answer.success && answer.EvnPLDispDop13_id > 0)
					{
						base_form.findField('EvnPLDispDop13_id').setValue(answer.EvnPLDispDop13_id);
						base_form.findField('PersonEvn_id').setValue(answer.PersonEvn_id);
						base_form.findField('Server_id').setValue(answer.Server_id);
						win.checkEvnPLDispDop13IsSaved();
						// запускаем callback чтобы обновить грид в родительском окне
						win.callback();
						// обновляем грид
						grid.getStore().load({
							params: {
								EvnPLDispDop13_id: answer.EvnPLDispDop13_id
							}
						});

						win.loadForm(answer.EvnPLDispDop13_id);
					}
				}
			}
		});
	},
	checkDopDispQuestionSaved: function() {
		var win = this;
		var base_form = this.EvnPLDispDop13FormPanel.getForm();
		if (getRegionNick() == 'buryatiya') {
			// смотрим сохранён ли опрос
			var saved = false;
			win.evnUslugaDispDopGrid.getGrid().getStore().each(function (rec) {
				if (!Ext.isEmpty(rec.get('EvnUslugaDispDop_didDate')) && rec.get('SurveyType_Code') == 2 ) {
					saved = true;
				}
			});

			if (saved) {
				base_form.findField('systolic_blood_pressure').enable();
				base_form.findField('diastolic_blood_pressure').enable();
				base_form.findField('person_weight').enable();
				base_form.findField('person_height').enable();
				base_form.findField('waist_circumference').enable();
				base_form.findField('body_mass_index').enable();
			} else {
				base_form.findField('systolic_blood_pressure').disable();
				base_form.findField('diastolic_blood_pressure').disable();
				base_form.findField('person_weight').disable();
				base_form.findField('person_height').disable();
				base_form.findField('waist_circumference').disable();
				base_form.findField('body_mass_index').disable();
			}
		}
	},
	checkForCostPrintPanel: function() {
		var base_form = this.EvnPLDispDop13FormPanel.getForm();

		this.CostPrintPanel.hide();
		base_form.findField('EvnCostPrint_setDT').setAllowBlank(true);
		base_form.findField('EvnCostPrint_Number').setContainerVisible(getRegionNick() == 'khak');
		base_form.findField('EvnCostPrint_IsNoPrint').setAllowBlank(true);

		// если справка уже печаталась и случай закрыт, отображаем раздел с данными справки
		if (base_form.findField('EvnPLDispDop13_IsEndStage').getValue() == 2 && !Ext.isEmpty(base_form.findField('EvnCostPrint_setDT').getValue()) && getRegionNick().inlist(['perm', 'kz', 'ufa'])) {
			this.CostPrintPanel.show();
			// поля обязтаельные
			base_form.findField('EvnCostPrint_setDT').setAllowBlank(false);
			base_form.findField('EvnCostPrint_IsNoPrint').setAllowBlank(false);
		}
	},
	setAllowDispDop: function() {
		var base_form = this.EvnPLDispDop13FormPanel.getForm();
		// если поле «Случай диспансеризации 1 этап закончен» имеет значение «Да», то поля "Взят на диспансерное наблюдение" и
		// "Нуждается в дополнительном лечении (обследовании) делаем обязательными для заполнения
		if (getRegionNick() == 'kareliya' && base_form.findField('EvnPLDispDop13_IsEndStage').getValue() == 2) {

			base_form.findField('EvnPLDispDop13_IsDisp').setAllowBlank(false);
			base_form.findField('NeedDopCure_id').setAllowBlank(false);
		} else {
			base_form.findField('EvnPLDispDop13_IsDisp').setAllowBlank(true);
			base_form.findField('NeedDopCure_id').setAllowBlank(true);
		}
	},
	recountBodyMassIndex: function() {
		var base_form = this.EvnPLDispDop13FormPanel.getForm();
		var person_weight = base_form.findField('person_weight').getValue();
		var person_height = base_form.findField('person_height').getValue() / 100;
		base_form.findField('body_mass_index').setValue(null);
		if (person_height > 0) {
			body_mass_index = person_weight / (person_height * person_height);
			base_form.findField('body_mass_index').setValue(body_mass_index.toFixed(1));
		}

		base_form.findField('body_mass_index').fireEvent('change', base_form.findField('body_mass_index'), base_form.findField('body_mass_index').getValue());
	},
	transferEvnPLDispDopToEvnPLDispProf: function() {
		var win = this;
		var base_form = this.EvnPLDispDop13FormPanel.getForm();

		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function (buttonId, text, obj) {
				if (buttonId == 'yes') {
					// перенос в профилактический осмотр
					// При нажатии «Перенести в профилактический осмотр» реализовать контроль на наличие карты профилактического осмотра взрослого на данного пациента
					// в выбранном году или предыдущем году (год определять по дате согласия текущей карты ДВН).
					win.getLoadMask('Проверка наличия карты профилактического осмотра').show();
					Ext.Ajax.request({
						callback: function (options, success, response) {
							win.getLoadMask().hide();
							if (success) {
								var response_obj = Ext.util.JSON.decode(response.responseText);
								if (response_obj.InThisYear) {
									sw.swMsg.alert('Ошибка', 'Данный пациент в выбранном году уже проходил профилактический осмотр.');
									return false;
								}

								var newDVNDate = getNewDVNDate();
								if (Ext.isEmpty(newDVNDate) || win.findById('EPLDD13_EvnPLDispDop13_consDate').getValue() < newDVNDate) {
									if (response_obj.InPastYear) {
										sw.swMsg.alert('Ошибка', 'Данный пациент в предыдущем году уже проходил профилактический осмотр.');
										return false;
									}
								}

								// Если карта профилактического осмотра на данного пациента в выбранном году и в предыдущем году НЕ создана.
								// Надо вывести пользователю список осмотров которые можно перенести в проф. осмотр и которые нельзя перенести
								var params = base_form.getValues();
								getWnd('swEvnPLDispDop13TransferWindow').show({
									Person_id: base_form.findField('Person_id').getValue(),
									EvnPLDispDop13_id: base_form.findField('EvnPLDispDop13_id').getValue(),
									callback: function () {
										// Создаём карту проф. осмотра
										win.getLoadMask('Создание карты профилактического осмотра').show();
										Ext.Ajax.request({
											callback: function (options, success, response) {
												win.getLoadMask().hide();
												if (success) {
													var response_obj = Ext.util.JSON.decode(response.responseText);
													if (response_obj.EvnPLDispProf_id) {
														// закрываем карту ДВН
														win.callback();
														win.hide();
														// открываем карту профосмотра
														getWnd('swEvnPLDispProfEditWindow').show({
															EvnPLDispProf_id: response_obj.EvnPLDispProf_id,
															action: 'edit',
															DispClass_id: 5,
															onHide: Ext.emptyFn,
															callback: function () {

															},
															Person_id: base_form.findField('Person_id').getValue(),
															Server_id: base_form.findField('Server_id').getValue()
														});
													}
												}
											},
											params: params,
											url: '/?c=EvnPLDispProf&m=transferEvnPLDispDopToEvnPLDispProf'
										});
									}
								});
							}
							else {
								sw.swMsg.alert('Ошибка', 'Ошибка проверки наличия карты профилактического осмотра.');
								return false;
							}
						},
						params: {
							EvnPLDisp_consDate: (typeof win.findById('EPLDD13_EvnPLDispDop13_consDate').getValue() == 'object' ? Ext.util.Format.date(win.findById('EPLDD13_EvnPLDispDop13_consDate').getValue(), 'd.m.Y') : win.findById('EPLDD13_EvnPLDispDop13_consDate').getValue()),
							Person_id: base_form.findField('Person_id').getValue()
						},
						url: '/?c=EvnPLDispProf&m=checkIfEvnPLDispProfExistsInTwoYear'
					});
				}
			},
			icon: Ext.MessageBox.QUESTION,
			msg: 'Количество отмеченных осмотров/исследований недостаточно для проведения диспансеризации взрослого населения. Перенести проведенные осмотры/исследования в профилактический осмотр?',
			title: 'Вопрос'
		});
	},
	show: function() {
		sw.Promed.swEvnPLDispDop13EditWindow.superclass.show.apply(this, arguments);
		
		if (!arguments[0])
		{
			Ext.Msg.alert('Сообщение', 'Неверные параметры');
			return false;
		}
		
		var win = this;
		var base_form = this.EvnPLDispDop13FormPanel.getForm();
		win.getLoadMask(LOAD_WAIT).show();
		this.restore();
		this.center();
		this.maximize();

		this.inWowRegister = false;

		this.dopDispInfoConsentGrid.setColumnHidden('DopDispInfoConsent_IsImpossible', true);

		win.blockSaveDopDispInfoConsent = false;
		win.saveDopDispInfoConsentAfterLoad = false;
		
		var form = this.EvnPLDispDop13FormPanel;

		form.getForm().reset();
		win.dopDispInfoConsentGrid.getGrid().getStore().removeAll();
		win.evnUslugaDispDopGrid.getGrid().getStore().removeAll();
		win.EvnDiagDopDispBeforeGrid.getGrid().getStore().removeAll();
		win.HeredityDiag.getGrid().getStore().removeAll();
		win.ProphConsultGrid.getGrid().getStore().removeAll();
		win.NeedConsultGrid.getGrid().getStore().removeAll();
		win.DispAppointGrid.getGrid().getStore().removeAll();

		this.checkForCostPrintPanel();
		this.setAllowDispDop();
		this.checkIsNewOrderButton();

		win.findById('EPLDD13_EvnPLDispDop13_consDate').setRawValue('');
		win.saveConsentButton.show();
		
		this.PersonFirstStageAgree = false; // Пациент не согласен на этап диспансеризации
		this.callback = Ext.emptyFn;
		this.onHide = Ext.emptyFn;
		this.PayType_id = null;
		
		form.getForm().setValues(arguments[0]);
		
		if (arguments[0].action)
		{
			this.action = arguments[0].action;
		}
		
		if (arguments[0].callback)
		{
			this.callback = arguments[0].callback;
		}

		if (arguments[0].PayType_id)
		{
			this.PayType_id = arguments[0].PayType_id;
		}
		
		if (arguments[0].onHide)
		{
			this.onHide = arguments[0].onHide;
		}
		
		this.Year = 2013;
		
		if (arguments[0].Year)
		{
			this.Year = arguments[0].Year;
		}

		if (arguments[0].VopOsm_EvnUslugaDispDop_disDate)
		{
			this.VopOsm_EvnUslugaDispDop_disDate = arguments[0].VopOsm_EvnUslugaDispDop_disDate;
		} else {
			this.VopOsm_EvnUslugaDispDop_disDate = null;
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
		if (this.action === 'edit' && arguments[0].EvnPLDispDop13_id) {
			Ext.Ajax.request({
				failure: function (response, options) {
					sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_zagruzke_dannyih_formyi'], function () {
						this.hide();
					}.createDelegate(this));
				},
				params: {
					Evn_id: arguments[0].EvnPLDispDop13_id,
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
		var base_form = this.EvnPLDispDop13FormPanel.getForm();
		var form = this.EvnPLDispDop13FormPanel;
		var EvnPLDispDop13_id = base_form.findField('EvnPLDispDop13_id').getValue();
		var person_id = base_form.findField('Person_id').getValue();
		var server_id = base_form.findField('Server_id').getValue();
		var DispClass_id = base_form.findField('DispClass_id').getValue();

		if (getRegionNick() == 'buryatiya') {
			base_form.findField('systolic_blood_pressure').showContainer();
			base_form.findField('diastolic_blood_pressure').showContainer();
			base_form.findField('systolic_and_diastolic').hideContainer();
		} else {
			base_form.findField('systolic_blood_pressure').hideContainer();
			base_form.findField('diastolic_blood_pressure').hideContainer();
			base_form.findField('systolic_and_diastolic').showContainer();
		}

		win.findById('EPLDD13_EvnPLDispDop13_consDate').setMinValue('01.01.2013');
		win.findById('EPLDD13_EvnPLDispDop13_consDate').setMaxValue(null);
		if (DispClass_id != 2 || getRegionNick().inlist(['astra', 'ekb'])) {
			if (getRegionNick() == 'penza' && win.Year <= 2015) {
				win.findById('EPLDD13_EvnPLDispDop13_consDate').setMinValue('01.04.' + win.Year);
			} else {
				win.findById('EPLDD13_EvnPLDispDop13_consDate').setMinValue('01.01.' + win.Year);
			}
			win.findById('EPLDD13_EvnPLDispDop13_consDate').setMaxValue('31.12.' + win.Year);
		}
		if (getRegionNick() == 'penza') {
			win.findById('EPLDD13_EvnPLDispDop13_consDate').setMinValue('01.04.2015');
		}


		base_form.findField('EvnPLDispDop13_RepFlag').hideContainer();

		if (DispClass_id == 2) {

			win.findById('EPLDD13_EvnPLDispDop13_consDate').setMaxValue(new Date());
			if (!Ext.isEmpty(this.VopOsm_EvnUslugaDispDop_disDate)){
				win.findById('EPLDD13_EvnPLDispDop13_consDate').setMinValue(this.VopOsm_EvnUslugaDispDop_disDate);
			}

			base_form.findField('EvnPLDispDop13_IsTwoStage').hideContainer();
			base_form.findField('EvnPLDispDop13_IsSchool').showContainer();
			base_form.findField('EvnPLDispDop13_IsProphCons').showContainer();
			base_form.findField('EvnPLDispDop13_IsEndStage').setFieldLabel('Случай диспансеризации 2 этап закончен');
			win.DopDispInfoConsentPanel.setTitle('Информированное добровольное согласие 2 этап');
			if (win.action == 'edit') {
				win.setTitle(WND_POL_EPLDD13SEDIT);
			} else {
				win.setTitle(WND_POL_EPLDD13SVIEW);
			}
		} else {
			base_form.findField('EvnPLDispDop13_IsTwoStage').showContainer();
			base_form.findField('EvnPLDispDop13_IsSchool').hideContainer();
			base_form.findField('EvnPLDispDop13_IsProphCons').hideContainer();
			base_form.findField('EvnPLDispDop13_IsEndStage').setFieldLabel('Случай диспансеризации 1 этап закончен');
			win.DopDispInfoConsentPanel.setTitle('Информированное добровольное согласие 1 этап');
			if (win.action == 'edit') {
				win.setTitle(WND_POL_EPLDD13EDIT);
			} else {
				win.setTitle(WND_POL_EPLDD13VIEW);
			}
		}
		
		base_form.findField('EvnPLDispDop13_IsMobile').fireEvent('check', base_form.findField('EvnPLDispDop13_IsMobile'), base_form.findField('EvnPLDispDop13_IsMobile').getValue());
		
		// пока не сохранена карта (сохраняется при информационно добровольном согласии) нельзя редактировать разделы кроме согласия
		this.checkEvnPLDispDop13IsSaved();
		
		inf_frame_is_loaded = false;

		this.PersonInfoPanel.load({ 
			Person_id: person_id, 
			Server_id: server_id, 
			callback: function() {
				win.getLoadMask().hide();
				inf_frame_is_loaded = true; 

				if (getRegionNick() == 'penza') {
					// проверяем содержится ли человек в регистре
					Ext.Ajax.request({
						callback: function (options, success, response) {
							if (success) {
								var response_obj = Ext.util.JSON.decode(response.responseText);
								if (response_obj.inWowRegister) {
									win.inWowRegister = true;
								}
							}
						},
						params: {
							Person_id: person_id
						},
						url: '/?c=EvnPLDispDop13&m=checkPersonInWowRegistry'
					});
				}

				var sex_id = win.PersonInfoPanel.getFieldValue('Sex_id');
				var age = win.PersonInfoPanel.getFieldValue('Person_Age');
				var a_d_presurre=base_form.findField('systolic_blood_pressure').getValue()+"/"+base_form.findField('diastolic_blood_pressure').getValue();
				base_form.findField('systolic_and_diastolic').setValue(a_d_presurre);
				base_form.findField('Server_id').setValue(win.PersonInfoPanel.getFieldValue('Server_id'));
				base_form.findField('PersonEvn_id').setValue(win.PersonInfoPanel.getFieldValue('PersonEvn_id'));
				
				if (win.action == 'edit') {
					win.enableEdit(true);
					win.evnUslugaDispDopGrid.setReadOnly(false);
					win.EvnDiagDopDispBeforeGrid.setReadOnly(false);
					win.HeredityDiag.setReadOnly(false);
					win.EvnDiagDopDispFirstGrid.setReadOnly(false);
					win.ProphConsultGrid.setReadOnly(false);
					win.NeedConsultGrid.setReadOnly(false);
					win.DispAppointGrid.setReadOnly(false);
				} else {
					win.enableEdit(false);
					win.evnUslugaDispDopGrid.setReadOnly(true);
					win.EvnDiagDopDispBeforeGrid.setReadOnly(true);
					win.HeredityDiag.setReadOnly(true);
					win.EvnDiagDopDispFirstGrid.setReadOnly(true);
					win.ProphConsultGrid.setReadOnly(true);
					win.NeedConsultGrid.setReadOnly(true);
					win.DispAppointGrid.setReadOnly(true);
				}
				
				Ext.getCmp('EPLDD13EF_PrintKLU').hide();
				Ext.getCmp('EPLDD13EF_PrintOnko').hide();

				if (!Ext.isEmpty(EvnPLDispDop13_id)) {
					win.loadForm(EvnPLDispDop13_id);
				}
				else {
					base_form.findField('EvnPLDispDop13_SumRick').fireEvent('change', base_form.findField('EvnPLDispDop13_SumRick'), base_form.findField('EvnPLDispDop13_SumRick').getValue());
					// Устанавливаем вид оплаты по-умолчанию
					if ( !Ext.isEmpty(win.PayType_id) ) {
						base_form.findField('PayType_id').setValue(win.PayType_id);
					}
					else {
						base_form.findField('PayType_id').setFieldValue('PayType_SysNick', 'oms');
					}

					// Грузим текущую дату
					setCurrentDateTime({
						callback: function(date) {
							win.findById('EPLDD13_EvnPLDispDop13_consDate').fireEvent('change', win.findById('EPLDD13_EvnPLDispDop13_consDate'), date);
						},
						dateField: win.findById('EPLDD13_EvnPLDispDop13_consDate'),
						loadMask: true,
						setDate: true,
						// setDateMaxValue: true,
						windowId: win.id
					});
				}
				
				win.buttons[0].focus();
			} 
		});
		
		form.getForm().clearInvalid();
		this.doLayout();
	},
	
	loadForm: function(EvnPLDispDop13_id) {
	
		var win = this;
		var base_form = this.EvnPLDispDop13FormPanel.getForm();
		win.getLoadMask(LOAD_WAIT).show();

		base_form.load({
			failure: function() {
				win.getLoadMask().hide();
				swEvnPLDispDop13EditWindow.hide();
			},
			params: {
				EvnPLDispDop13_id: EvnPLDispDop13_id,
				archiveRecord: win.archiveRecord
			},
			success: function() {
				win.getLoadMask().hide();
				
				if ( base_form.findField('accessType').getValue() == 'view' ) {
					win.action = 'view';
					win.enableEdit(false);
				}

				if ( getRegionNick() == 'perm' && base_form.findField('EvnPLDispDop13_IsPaid').getValue() == 2 && parseInt(base_form.findField('EvnPLDispDop13_IndexRepInReg').getValue()) > 0 ) {
					base_form.findField('EvnPLDispDop13_RepFlag').showContainer();

					if ( parseInt(base_form.findField('EvnPLDispDop13_IndexRep').getValue()) >= parseInt(base_form.findField('EvnPLDispDop13_IndexRepInReg').getValue()) ) {
						base_form.findField('EvnPLDispDop13_RepFlag').setValue(true);
					}
					else {
						base_form.findField('EvnPLDispDop13_RepFlag').setValue(false);
					}
				}
				
				Ext.getCmp('EPLDD13EF_PrintKLU').show();
				if (getRegionNick() == 'ekb') {
					Ext.getCmp('EPLDD13EF_PrintOnko').show();
				}

				if (base_form.findField('EvnPLDispDop13_IsSuspectZNO').getValue() == 2) {
					Ext.getCmp('EPLDD13EF_PrintKLU').enable();
					Ext.getCmp('EPLDD13EF_PrintOnko').enable();
				} else {
					Ext.getCmp('EPLDD13EF_PrintKLU').disable();
					Ext.getCmp('EPLDD13EF_PrintOnko').disable();
				}

				win.checkForCostPrintPanel();
				win.setAllowDispDop();
				win.checkIsNewOrderButton();

				// грузим грид услуг
				win.evnUslugaDispDopGrid.loadData({
					params: { EvnPLDispDop13_id: EvnPLDispDop13_id, object: 'EvnPLDispDop13' }, globalFilters: { EvnPLDispDop13_id: EvnPLDispDop13_id }, noFocusOnLoad: true
				});
				// и все остальные гриды тоже
				win.EvnDiagDopDispBeforeGrid.loadData({
					params: { EvnPLDisp_id: EvnPLDispDop13_id, object: 'EvnPLDispDop13', DeseaseDispType_id: 1, PersonEvn_id: base_form.findField('PersonEvn_id').getValue(), Server_id: base_form.findField('Server_id').getValue() }, globalFilters: { EvnPLDisp_id: EvnPLDispDop13_id, DeseaseDispType_id: 1 }, noFocusOnLoad: true
				});
				win.HeredityDiag.loadData({
					params: { EvnPLDisp_id: EvnPLDispDop13_id, object: 'EvnPLDispDop13' }, globalFilters: { EvnPLDisp_id: EvnPLDispDop13_id }, noFocusOnLoad: true
				});
				win.EvnDiagDopDispFirstGrid.loadData({
					params: { EvnPLDisp_id: EvnPLDispDop13_id, object: 'EvnPLDispDop13', DeseaseDispType_id: 2, PersonEvn_id: base_form.findField('PersonEvn_id').getValue(), Server_id: base_form.findField('Server_id').getValue() }, globalFilters: { EvnPLDisp_id: EvnPLDispDop13_id, DeseaseDispType_id: 2 }, noFocusOnLoad: true
				});
				win.ProphConsultGrid.loadData({
					params: { EvnPLDisp_id: EvnPLDispDop13_id, object: 'EvnPLDispDop13' }, globalFilters: { EvnPLDisp_id: EvnPLDispDop13_id }, noFocusOnLoad: true
				});
				win.NeedConsultGrid.loadData({
					params: { EvnPLDisp_id: EvnPLDispDop13_id, object: 'EvnPLDispDop13' }, globalFilters: { EvnPLDisp_id: EvnPLDispDop13_id }, noFocusOnLoad: true
				});
				win.DispAppointGrid.loadData({
					params: { EvnPLDisp_id: EvnPLDispDop13_id, object: 'EvnPLDispDop13' }, globalFilters: { EvnPLDisp_id: EvnPLDispDop13_id }, noFocusOnLoad: true
				});
						
				if ( base_form.findField('EvnPLDispDop13_IsEndStage').getValue() == 2 ) {
				//Проверка на Группу здоровья
					base_form.findField('HealthKind_id').setAllowBlank(false);
					base_form.findField('HealthKind_id').validate();
				}else{
					base_form.findField('HealthKind_id').setAllowBlank(true);
					base_form.findField('HealthKind_id').validate();
				}
				
				// год диспансеризации берём из карты
				win.Year = Ext.util.Format.date(Date.parseDate(base_form.findField('EvnPLDispDop13_consDate').getValue(), 'd.m.Y'), 'Y');
				if (getRegionNick() == 'penza' && win.Year <= 2015) {
					win.findById('EPLDD13_EvnPLDispDop13_consDate').setMinValue('01.04.' + win.Year);
				} else {
					win.findById('EPLDD13_EvnPLDispDop13_consDate').setMinValue('01.01.' + win.Year);
				}
				win.findById('EPLDD13_EvnPLDispDop13_consDate').setMaxValue('31.12.' + win.Year);

				if (base_form.findField('DispClass_id').getValue() == 2) {

					win.findById('EPLDD13_EvnPLDispDop13_consDate').setMaxValue(new Date());
					if (!Ext.isEmpty(win.VopOsm_EvnUslugaDispDop_disDate)) {
						win.findById('EPLDD13_EvnPLDispDop13_consDate').setMinValue(win.VopOsm_EvnUslugaDispDop_disDate);
					}
				}
				// #27943
				// Если в карте ДВН - 2 этап (для данного 1 этапа) указана дата начала,  то следующие поля недоступны для редактирования:
				// - Дата осмотра врача-терапевта (ВОП) 
				// - Случай диспансеризации 1 этап закончен
				// - Направлен на 2 этап диспансеризации
				// - Дата подписания согласия/отказа
				if (!Ext.isEmpty(base_form.findField('EvnPLDispDop13Sec_id').getValue())) {
					base_form.findField('EvnPLDispDop13_IsEndStage').setValue(2);
					base_form.findField('EvnPLDispDop13_IsEndStage').disable();
					base_form.findField('EvnPLDispDop13_IsTwoStage').setValue(2);
					base_form.findField('EvnPLDispDop13_IsTwoStage').disable();
					if (!getRegionNick().inlist(['ekb', 'buryatiya'])
						|| !(sw.Promed.MedStaffFactByUser.current && sw.Promed.MedStaffFactByUser.current.ARMType == 'mstat')
						|| base_form.findField('EvnPLDispDop13_IsPaid').getValue() == 2
					) {
						win.findById('EPLDD13_EvnPLDispDop13_consDate').disable();
						win.saveConsentButton.hide();
					} else {
						win.saveConsentButton.show();
					}
				}
				else {
					win.saveConsentButton.show();
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
				
				var diag_s_combo = base_form.findField('Diag_sid');
				var diag_sid = diag_s_combo.getValue();
				if (!Ext.isEmpty(diag_sid)) {
					diag_s_combo.getStore().load({
						params: { where: "where Diag_id = " + diag_sid },
						callback: function(data) {
							diag_s_combo.getStore().each(function(record) {
								if ( record.get('Diag_id') == diag_sid ) {
									diag_s_combo.fireEvent('select', diag_s_combo, record, 0);
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
				
				base_form.findField('EvnPLDispDop13_IsMobile').fireEvent('check', base_form.findField('EvnPLDispDop13_IsMobile'), base_form.findField('EvnPLDispDop13_IsMobile').getValue());

				win.findById('EPLDD13_EvnPLDispDop13_consDate').setValue(base_form.findField('EvnPLDispDop13_consDate').getValue());
				win.findById('EPLDD13_EvnPLDispDop13_consDate').fireEvent('change', win.findById('EPLDD13_EvnPLDispDop13_consDate'), win.findById('EPLDD13_EvnPLDispDop13_consDate').getValue());
				
				base_form.findField('Diag_spid').setContainerVisible(base_form.findField('EvnPLDispDop13_IsSuspectZNO').getValue() == 2);
				base_form.findField('Diag_spid').setAllowBlank(!getRegionNick().inlist([ 'astra', 'perm' ]) || base_form.findField('EvnPLDispDop13_IsSuspectZNO').getValue() != 2);
				var diag_spid = base_form.findField('Diag_spid').getValue();
				if (diag_spid) {
					base_form.findField('Diag_spid').getStore().load({
						callback:function () {
							base_form.findField('Diag_spid').getStore().each(function (rec) {
								if (rec.get('Diag_id') == diag_spid) {
									base_form.findField('Diag_spid').fireEvent('select', base_form.findField('Diag_spid'), rec, 0);
									win.setDiagSpidComboDisabled();
								}
							});
						},
						params:{where:"where DiagLevel_id = 4 and Diag_id = " + diag_spid}
					});
				}
				
				base_form.findField('EvnPLDispDop13_SumRick').fireEvent('change', base_form.findField('EvnPLDispDop13_SumRick'), base_form.findField('EvnPLDispDop13_SumRick').getValue(), base_form.findField('EvnPLDispDop13_SumRick').getValue());
			},
			url: '/?c=EvnPLDispDop13&m=loadEvnPLDispDop13EditForm'
		});
		
	},
	// #172213
	checkDisableZNOCombo: function(){

		if(getRegionNick() !== 'perm')
			return true;
		var win = this;
		var base_form = win.EvnPLDispDop13FormPanel.getForm();

		var index = win.evnUslugaDispDopGrid.getGrid().getStore().findBy(function (rec) {
			return (rec.get('SurveyType_Code') == 19);
		});
		if(index !== -1){
			var rec_Vizit = win.evnUslugaDispDopGrid.getGrid().getStore().getAt(index);
			if(rec_Vizit.get('Diag_Code') && (rec_Vizit.get('Diag_Code').search(new RegExp("^(C|D0)", "i")) >= 0)){
				//поле ЗНО очищено и заблокировано
				base_form.findField('EvnPLDispDop13_IsSuspectZNO').disable();
				base_form.findField('EvnPLDispDop13_IsSuspectZNO').clearValue();
				base_form.findField('EvnPLDispDop13_IsSuspectZNO').fireEvent('change', base_form.findField('EvnPLDispDop13_IsSuspectZNO'), base_form.findField('EvnPLDispDop13_IsSuspectZNO').getValue());
				Ext.getCmp('EPLDD13EF_PrintKLU').enable();
			}
			else {
				//поле ЗНО доступно
				Ext.getCmp('EPLDD13EF_PrintKLU').setDisabled(!(base_form.findField('EvnPLDispDop13_IsSuspectZNO').getValue() == 2));
				base_form.findField('EvnPLDispDop13_IsSuspectZNO').enable();
			}
		} else {
			Ext.getCmp('EPLDD13EF_PrintKLU').setDisabled(!(base_form.findField('EvnPLDispDop13_IsSuspectZNO').getValue() == 2));
			base_form.findField('EvnPLDispDop13_IsSuspectZNO').enable();
		}
	},
	title: WND_POL_EPLDD13ADD,
	width: 800
}
);
