Ext6.define('common.EMK.EvnPLDispDop.controller.ConsentController', {
	extend: 'Ext6.app.ViewController',
	alias: 'controller.EvnPLDispDop13ConsentController',
	onChangeConsentRecord: function(record) {
		//контроль отображения блоков в соответствии с чекбоксами в согласии на услуги
		var contr = this,
			view = contr.getView(), //панель согласия
			win = view.ownerPanel, //основное окно ДВН. Оно же - правый блок ЭМК
			vm = win.getViewModel(), //основная модель - данные будем хранить в одном месте.
			base_form = view.ConsentForm.getForm(), //форма панели
			main_form = win.MainForm.getForm(); //основная форма всего окна ДВН

		var code = record.get('SurveyType_Code'),
			oldcodes = vm.get('SurveyType_Codes'),
			codes = oldcodes.slice();
		if(record.get('DopDispInfoConsent_IsAgree') || record.get('DopDispInfoConsent_IsEarlier')) { //значит надо показать раздел (для выполнено ранее нужен доступ к шестеренке)
			if(!codes.in_array(code)) codes.push(code);
		} else codes.remove(code);
		vm.set('SurveyType_Codes', codes);
		if(!Ext6.isEmpty(vm.get('EvnPLDispDop13_id')))
			win.getController().loadPrescribes();// Загрузка списка направлений (назначений)
	},
	saveConsent: function(options) {
		var contr = this,
			view = contr.getView(), //панель согласия
			win = view.ownerPanel, //основное окно ДВН. Оно же - правый блок ЭМК
			vm = win.getViewModel(), //основная модель - данные будем хранить в одном месте.
			base_form = view.ConsentForm.getForm(), //форма панели
			main_form = win.MainForm.getForm(), //основная форма всего окна ДВН
			btn = view.queryById('saveConsentButton');
		
		if ( btn.disabled || vm.get('action') == 'view' ) {
			return false;
		}
		
		if (vm.get('blockSaveDopDispInfoConsent')) {
			vm.set('saveDopDispInfoConsentAfterLoad', true);
			return false;
		}

		btn.disable();

		if ( Ext6.isEmpty(vm.get('PayType_id')) ) {
			btn.enable();
			view.unmask();

			Ext6.Msg.show({
				buttons: Ext6.Msg.OK,
				fn: function() {
					base_form.findField('PayType_id').focus(true);
				},
				icon: Ext6.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});

			return false;
		}

		var EvnPLDispDop13_consDate = vm.get('EvnPLDispDop13_consDate');

		if ( getRegionNick().inlist([ 'perm' ])) {
			var
				tmpIsEarlier = 0,
				tmpNotEarlier = 0;
			
			view.ConsentGrid.getStore().each(function(ddicRec) {
				if ( Ext6.isEmpty(ddicRec.get('DopDispInfoConsent_id')) || ddicRec.get('DopDispInfoConsent_id') < 0 ) {
					return false;
				}
				else if ( ddicRec.get('DopDispInfoConsent_IsAgree') == true || ddicRec.get('DopDispInfoConsent_IsEarlier') == true ) {
					// тянем соовтетствующую услугу
					/*view.evnUslugaDispDopGrid.getGrid().getStore().each(function (euddRec) {
						if (
							!Ext6.isEmpty(euddRec.get('DopDispInfoConsent_id'))
							&& euddRec.get('DopDispInfoConsent_id') == ddicRec.get('DopDispInfoConsent_id')
							&& !Ext6.isEmpty(euddRec.get('EvnUslugaDispDop_didDate'))
						) {
							if ( ddicRec.get('DopDispInfoConsent_IsEarlier') == true && euddRec.get('EvnUslugaDispDop_didDate') >= EvnPLDispDop13_consDate ) {
								tmpIsEarlier = tmpIsEarlier + 1;
							}
							else if ( ddicRec.get('DopDispInfoConsent_IsAgree') == true && euddRec.get('EvnUslugaDispDop_didDate') < EvnPLDispDop13_consDate ) {
								tmpNotEarlier = tmpNotEarlier + 1;
							}
						}
					});*/
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
				Ext6.Msg.alert('Ошибка', errorText);
				return false;
			}
		}

		/* TODO: когда расширим на регионы
		var xdate3 = new Date(2015,4,1);
		if ( getRegionNick().inlist([ 'penza' ]) || (getRegionNick().inlist([ 'pskov' ]) && base_form.findField('DispClass_id').getValue() == 1 && view.queryById('EvnPLDispDop13_consDate').getValue() >= xdate3 ) ) {
			// Осмотр врача-терапевта должен быть с пометкой «Согласие»
			var IsAgree = false;
			view.ConsentGrid.getStore().each(function(rec) {
				if (!Ext6.isEmpty(rec.get('SurveyType_Code')) && rec.get('SurveyType_Code').inlist([19])) {
					if (rec.get('DopDispInfoConsent_IsAgree') == true) {
						IsAgree = true;
					}
				}
			});

			if (!IsAgree) {
				btn.enable();
				Ext6.Msg.alert('Ошибка', 'Осмотр врача-терапевта обязателен при проведении диспансеризации взрослого населения.');
				return false;
			}
		}*/

		// для Уфы: Если в карте ДВН количество согласий (кол-во выбранных чекбоксов в поле "Согласие гражданина") для осмотров / исследований меньше 85%, то выводить сообщение "Количество осмотров\исследований составляет менее 85% от объема, установленного для данного возраста и пола. Ок", Сохранение отменить.
		/* TODO: когда расширим на регионы
		if ( getRegionNick().inlist([ 'ufa' ]) && base_form.findField('DispClass_id').getValue() != 2 ) {
			var
				kolvo = 0,
				kolvoAgree = 0,
				surveyAgree = false;

			var required = [];

			// Определяем согласие/отказ по диспансеризации в целом
			view.ConsentGrid.getStore().each(function(rec) {
				if (!Ext6.isEmpty(rec.get('SurveyType_Code')) && rec.get('SurveyType_Code').inlist([1,48]) && rec.get('DopDispInfoConsent_IsAgree') == true) {
					surveyAgree = true;
				}

				if (!Ext6.isEmpty(rec.get('SurveyType_Code')) && rec.get('SurveyType_Code').inlist([14,20,21]) && rec.get('DopDispInfoConsent_IsAgree') != true && rec.get('DopDispInfoConsent_IsEarlier') != true) {
					required.push(rec.get('SurveyType_Name'));
				}
			});

			var PayType_SysNick = base_form.findField('PayType_id').getFieldValue('PayType_SysNick');
			if (PayType_SysNick == 'oms' && view.queryById('EvnPLDispDop13_consDate').getValue() >= new Date(2019, 0, 1) && required.length > 0) {
				btn.enable();
				Ext6.Msg.alert('Ошибка', 'При диспансеризации взрослого населения обязательно проведение следующих осмотров (исследований): <br> - ' + required.join('<br> - ') + '<br>Установите флаг «Пройдено ранее» или «Согласие гражданина» для перечисленных осмотров');
				return false;
			}

			if ( surveyAgree == true ) {
				// считаем количество помеченных согласий
				view.ConsentGrid.getStore().each(function(rec) {
					if (!Ext6.isEmpty(rec.get('SurveyType_Code')) && !rec.get('SurveyType_Code').inlist([1,48])) {
						kolvo++;

						if (rec.get('DopDispInfoConsent_IsAgree') == true || rec.get('DopDispInfoConsent_IsEarlier') == true) {
							kolvoAgree++;
						}
					}
				});
				if (kolvoAgree < Math.round(kolvo*0.85)) {
					btn.enable();
					Ext6.Msg.alert('Ошибка', 'Количество осмотров / исследований составляет менее 85% от объема, установленного для данного возраста и пола, что недостаточно для проведения диспансеризации взрослого населения.');
					return false;
				}
			}
		} else if ( getRegionNick().inlist([ 'buryatiya' ]) && base_form.findField('DispClass_id').getValue() != 2 ) {
			var
				kolvo = 0,
				kolvoAgree = 0,
				surveyAgree = false;

			// Определяем согласие/отказ по диспансеризации в целом
			view.ConsentGrid.getStore().each(function(rec) {
				if (!Ext6.isEmpty(rec.get('SurveyType_Code')) && rec.get('SurveyType_Code').inlist([1,48]) && rec.get('DopDispInfoConsent_IsAgree') == true) {
					surveyAgree = true;
				}
			});

			if ( surveyAgree == true ) {
				// считаем количество помеченных согласий
				view.ConsentGrid.getStore().each(function(rec) {
					if (!Ext6.isEmpty(rec.get('SurveyType_Code')) && !rec.get('SurveyType_Code').inlist([1,48])) {
						kolvo++;

						if (rec.get('DopDispInfoConsent_IsAgree') == true || rec.get('DopDispInfoConsent_IsEarlier') == true) {
							kolvoAgree++;
						}
					}
				});
				if (kolvoAgree < this.count85Percent) {
					btn.enable();
					Ext6.Msg.alert('Ошибка', 'Количество осмотров / исследований составляет менее 85% от объема, установленного для данного возраста и пола, что недостаточно для проведения диспансеризации взрослого населения.');
					return false;
				}
			}
		} else if ( getRegionNick().inlist([ 'pskov' ]) && base_form.findField('DispClass_id').getValue() != 2 ) {
			// считаем количество помеченных согласий
			var kolvo = 0;
			var kolvoAgree = 0;
			view.ConsentGrid.getStore().each(function(rec) {
				if (!Ext6.isEmpty(rec.get('SurveyType_Code')) && !rec.get('SurveyType_Code').inlist([1,48])) {
					kolvo++;

					if (rec.get('DopDispInfoConsent_IsAgree') == true || rec.get('DopDispInfoConsent_IsEarlier') == true) {
						kolvoAgree++;
					}
				}
			});
			if (kolvoAgree < this.count85Percent) {
				btn.enable();
				Ext6.Msg.alert('Ошибка', 'Количество осмотров / исследований составляет менее 85% от объема, установленного для данного возраста и пола, что недостаточно для проведения диспансеризации взрослого населения.');
				return false;
			}
		} else if ( getRegionNick().inlist([ 'penza' ]) && base_form.findField('DispClass_id').getValue() != 2 ) {
			// считаем количество помеченных согласий
			var kolvo = 0;
			var kolvoAgree = 0;
			var kolvkoUzi = false;
			var kolvoAgreeUzi = false;
			view.ConsentGrid.getStore().each(function(rec) {
				if (!Ext6.isEmpty(rec.get('SurveyType_Code')) && !rec.get('SurveyType_Code').inlist([1,48])) {
					if (rec.get('SurveyType_Code').inlist([94,95,98,99,100])) {
						kolvkoUzi = true;
					} else {
						kolvo++;
					}

					if (rec.get('DopDispInfoConsent_IsAgree') == true || rec.get('DopDispInfoConsent_IsEarlier') == true) {
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
				btn.enable();
				Ext6.Msg.alert('Ошибка', 'Количество осмотров / исследований составляет менее 85% от объема, установленного для данного возраста и пола, что недостаточно для проведения диспансеризации взрослого населения.');
				return false;
			}
		} else if ( getRegionNick().inlist([ 'astra' ]) && base_form.findField('DispClass_id').getValue() != 2 ) {
			// считаем количество помеченных согласий
			var kolvo = 0;
			var kolvoAgree = 0;
			view.ConsentGrid.getStore().each(function(rec) {
				if (!Ext6.isEmpty(rec.get('SurveyType_Code')) && !rec.get('SurveyType_Code').inlist([1,48]) && rec.get('DopDispInfoConsent_IsAgeCorrect') == 1) {
					if (!Ext6.isEmpty(rec.get('SurveyType_Code')) && !rec.get('SurveyType_Code').inlist([1,48])) {
						kolvo++;

						if (rec.get('DopDispInfoConsent_IsAgree') == true || rec.get('DopDispInfoConsent_IsEarlier') == true) {
							kolvoAgree++;
						}
					}
				}
			});

			if (kolvoAgree < this.count85Percent) {
				btn.enable();
				Ext6.Msg.alert('Ошибка', 'Количество осмотров / исследований с пометкой «Согласие» и/или «Пройдено ранее» недостаточно для проведения диспансеризации.');
				return false;
			}
		}*/

		// https://redmine.swan.perm.ru/issues/61990
		var xdate2 = new Date(2015,3,1);
		
		if (vm.get('DispClass_id') != 2 && vm.get('EvnPLDispDop13_consDate') >= xdate2) {
			// Проверяем сохранён ли хоть один осмотр/исследование
			var kolvoSaved = 0;
			/*view.evnUslugaDispDopGrid.getGrid().getStore().each(function(rec) {
				if (!Ext6.isEmpty(rec.get('DopDispInfoConsent_id')) && !Ext6.isEmpty(rec.get('EvnUslugaDispDop_didDate')) ) {
					kolvoSaved++;
				}
			});*/

			// 1. собираем пакеты
			var Packets = {};
			view.ConsentGrid.getStore().each(function(rec) {
				if (!Ext6.isEmpty(rec.get('SurveyTypeLink_IsUslPack'))) {
					if (!Packets[rec.get('SurveyTypeLink_IsUslPack')]) {
						Packets[rec.get('SurveyTypeLink_IsUslPack')] = 1;
					} else {
						Packets[rec.get('SurveyTypeLink_IsUslPack')]++;
					}
				}
			});

			// 2. Считаем общее количество долей + количество долей осмотров/исследований с пометкой «Согласие» или «Пройдено ранее».
			
			var kolvo = 0;
			var kolvoAccept = 0;
			view.ConsentGrid.getStore().each(function(rec) {
				if (!Ext6.isEmpty(rec.get('SurveyType_Code')) /*&& !rec.get('SurveyType_Code').inlist([1,48])*/) {
					if (rec.get('DopDispInfoConsent_IsImpossible') != true) {
						if ( rec.get('SurveyTypeLink_IsUslPack') ) {
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
				}
			});

			// МД (минимальное количество долей) = 85% от общего объема и отбросить дробную часть до ближайшего целого.
			var minKolvo = 0; //vm.get('count85Percent');
			if (getRegionNick().inlist(['perm', 'pskov']) && (
				vm.get('EvnPLDispDop13_consDate') >= getNewDVNDate()
				/*|| base_form.findField('EvnPLDispDop13_IsNewOrder').getValue() == 2*/
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
					Ext6.Msg.alert('Ошибка', 'Количество осмотров/исследований недостаточно для проведения диспансеризации взрослого населения');
				} else {
					// 2. Сохранен хотя бы один осмотр/исследование в маршрутной карте
					// Если количество долей осмотров/исследований с пометкой «Согласие» или «Пройдено ранее» составляют менее минимального количества долей,
					// выводить сообщение «Количество отмеченных осмотров/исследований недостаточно для проведения диспансеризации взрослого населения.
					// Перенести проведенные осмотры/исследования в профилактический осмотр? Перенести в профилактический осмотр / Отмена».
					view.transferEvnPLDispDopToEvnPLDispProf();
				}
				btn.enable();
				return false;
			}
		}
		
		view.mask('Сохранение информированного добровольного согласия');
		// берём все записи из грида и посылаем на сервер, разбираем ответ
		// на сервере создать саму карту EvnPLDispDop13, если EvnPLDispDop13_id не задано, сохранить её информ. согласие DopDispInfoConsent, вернуть EvnPLDispDop13_id
		var grid = view.ConsentGrid;
		var params = {};
		var consDate = vm.get('EvnPLDispDop13_consDate');

		params.EvnPLDispDop13_consDate = (typeof consDate == 'object' ? Ext6.util.Format.date(consDate, 'd.m.Y') : consDate);
		params.EvnPLDispDop13_IsMobile = (base_form.findField('EvnPLDispDop13_IsMobile').checked) ? true : '';
		params.EvnPLDispDop13_IsOutLpu = (base_form.findField('EvnPLDispDop13_IsOutLpu').checked) ? true : '';
		params.Lpu_mid = vm.get('Lpu_mid') ? vm.get('Lpu_mid') : '';
		params.PersonEvn_id = vm.get('PersonEvn_id');
		params.Person_id = vm.get('Person_id');
		params.Server_id = vm.get('Server_id');
		params.EvnPLDispDop13_id = vm.get('EvnPLDispDop13_id') ? vm.get('EvnPLDispDop13_id') : '';
		params.EvnPLDispDop13_fid = vm.get('EvnPLDispDop13_fid') ? vm.get('EvnPLDispDop13_fid') : '';
		params.DispClass_id = vm.get('DispClass_id');
		params.PayType_id = vm.get('PayType_id');
		
		params.DopDispInfoConsentData = Ext6.util.JSON.encode(sw4.getStoreRecords( grid.getStore(), {
			exceptionFields: [
				'SurveyType_Name'
			]
		}));
		
		Ext6.Ajax.request(
		{
			url: '/?c=EvnPLDispDop13&m=saveDopDispInfoConsent',
			params: params,
			failure: function(response, options)
			{
				btn.enable();
				view.unmask();
			},
			success: function(response, action)
			{
				btn.enable();
				view.unmask();
				if (response.responseText)
				{
					var answer = Ext6.util.JSON.decode(response.responseText);
					if (answer.success && answer.EvnPLDispDop13_id > 0)
					{
						//Не станем обновлять форму, лучше обновим дерево ЭМК (чтобы появился новый пункт) и откроем все заново:
						win.ownerWin.loadTree({
							callback: function() {
								win.ownerWin.loadEmkViewPanel('EvnPLDispDop13', answer.EvnPLDispDop13_id, '');
							}
						});
					}
				}
			}
		});
	},

	onLoadEvnPLDispDop13Form: function(data) {
		var view = this.getView(),
			vm = this.getViewModel();
	},
	onEvnPLDispDop13_consDate: function(field, newValue, oldValue) {//on change
		var contr = this,
			view = contr.getView(),
			win = view.ownerPanel,
			vm = win.getViewModel(),
			base_form = view.ConsentForm.getForm(),
			main_form = win.MainForm.getForm();
		return;

		var xdate1 = new Date(2015,0,1);
		var xdate2 = new Date(2015,3,1);
		var xdate3 = new Date(2015,4,1);
		var newDVNDate = getNewDVNDate();

		if (!Ext6.isEmpty(oldValue) && !Ext.isEmpty(newValue) 
			&& (Ext6.isEmpty(vm.get('isEvnPLDispDop13_id')) || !vm.get('PersonAgree') ) //win.checkEvnPLDispDop13IsSaved() 
			&& (
			(newValue < xdate1 && oldValue >= xdate1) // при переходе через 01.01.2015
			|| (oldValue < xdate1 && newValue >= xdate1)
			|| (getRegionNick().inlist(['perm','kareliya','ekb', 'astra', 'krym']) && newValue < xdate2 && oldValue >= xdate2) // при переходе через 01.04.2015
			|| (getRegionNick().inlist(['perm','kareliya','ekb', 'astra', 'krym']) && oldValue < xdate2 && newValue >= xdate2)
			|| (getRegionNick().inlist(['ufa', 'pskov']) && newValue < xdate3 && oldValue >= xdate3) // при переходе через 01.05.2015
			|| (getRegionNick().inlist(['ufa', 'pskov']) && oldValue < xdate3 && newValue >= xdate3)
			|| ((getRegionNick() == 'perm' || base_form.findField('DispClass_id').getValue() != 2) && !Ext.isEmpty(newDVNDate) && newValue < newDVNDate && oldValue >= newDVNDate) // при переходе через 06.05.2019 / 01.06.2019
			|| ((getRegionNick() == 'perm' || base_form.findField('DispClass_id').getValue() != 2) && !Ext.isEmpty(newDVNDate) && oldValue < newDVNDate && newValue >= newDVNDate)
		)) {
			Ext6.Msg.show({
				buttons: Ext6.Msg.YESNO,
				fn: function ( buttonId ) {
					if ( buttonId == 'yes' ) {
						vm.set('saveDopDispInfoConsentAfterLoad', true);
						vm.set('blockSaveDopDispInfoConsent', true);
						view.ConsentGrid.getStore().load();
					} else {
						field.setValue(oldValue);
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
					if (rec.get('HealthKind_id') && rec.get('HealthKind_id').inlist([1, 2, 3, 6, 7])) {
						return true;
					} else {
						return false;
					}
				});
			} else {
				base_form.findField('HealthKind_id').getStore().filterBy(function (rec) {
					if (rec.get('HealthKind_id') && rec.get('HealthKind_id').inlist([1, 2, 6, 7])) {
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
				if (rec.get('HealthKind_id') && rec.get('HealthKind_id').inlist([1,2,3])) {
					return true;
				} else {
					return false;
				}
			});
			base_form.findField('HealthKind_id').setValue(base_form.findField('HealthKind_id').getValue());
		}
/*
		if (getRegionNick() == 'ekb') {
			// Дата информированного согласия должна быть в пределах выбранного года
			this.setMinValue('01.01.'+Ext.util.Format.date(newValue, 'Y'));
			this.setMaxValue('31.12.'+Ext.util.Format.date(newValue, 'Y'));
		}*/

		win.blockSaveDopDispInfoConsent = true;
		win.loadDopDispInfoConsentGrid(newValue);
	},
	refuse: function() {
		var contr = this,
			view = contr.getView(),
			win = view.ownerPanel,
			vm = win.getViewModel();
		EvnPLDispRefuse(vm.get('Person_id'), vm.get('DispClass_id'), getGlobalOptions().CurMedStaffFact_id, function() {win.ownerWin.loadTree();});
	},
	printConsent: function() {
		var contr = this,
			view = contr.getView(),
			win = view.ownerPanel,
			vm = win.getViewModel();
		var paramDispClass = vm.get('DispClass_id');
		var paramEvnPLDispDop13 = vm.get('EvnPLDispDop13_id');
		if(paramEvnPLDispDop13) {
			var dialog_wnd = Ext6.Msg.show({
				msg: langs('Выберите вид согласия'),
				title: langs('Вид согласия'),
				icon: Ext6.MessageBox.QUESTION,
				
				buttons: Ext6.MessageBox.YESNOCANCEL,
				buttonText: { yes: "От имени пациента", no: 'От имени законного представителя', cancel: "Отмена" },
				fn: function (buttonId) {
					if(buttonId === 'cancel') {
						return;
					} else
					if (buttonId === 'yes') {
						printBirt({
							'Report_FileName': 'EvnPLDopDispInfoConsent.rptdesign',
							'Report_Params': '&paramEvnPLDispDop13=' + paramEvnPLDispDop13 + '&paramDispClass=' + paramDispClass,
							'Report_Format': 'pdf'
						});
					}
					else {
						printBirt({
							'Report_FileName': 'EvnPLDopDispInfoConsent_Deputy.rptdesign',
							'Report_Params': '&paramEvnPLDispDop13=' + paramEvnPLDispDop13 + '&paramDispClass=' + paramDispClass,
							'Report_Format': 'pdf'
						});
					}
				}.createDelegate(this)
			});
		}
	}
});