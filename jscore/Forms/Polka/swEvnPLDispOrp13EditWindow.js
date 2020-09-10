/**
* swEvnPLDispOrp13EditWindow - окно редактирования/добавления карты по диспасеризации детей-сирот с 2013 года
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package    Polka
* @access     public
* @copyright  Copyright (c) 2013 Swan Ltd.
* @author     Dmitry Vlasenko
* @originalauthor	Марков Андрей / Stas Bykov aka Savage (savage1981@gmail.com)
* @version    24.05.2013
* @comment    Префикс для id компонентов epldo13ef (EvnPLDispOrp13EditForm)
*	            TABINDEX_EPLDO13EF: 9300
*
*
* @input data: action - действие (add, edit, view)
*              EvnPLDispOrp_id - ID карты для редактирования или просмотра
*              Person_id - ID человека
*              PersonEvn_id - ?
*              Server_id - ?
*
*
* Использует: окно просмотра истории болезни (swPersonCureHistoryWindow)
*             окно просмотра льгот (swPersonPrivilegeViewWindow)
*             окно редактирования человека (swPersonEditWindow)
*             окно добавления/редактирования услуги по ДД (swEvnUslugaDispOrp13EditWindow)
*             окно добавления/редактирования посещения по ДД (swEvnVizitDispOrp13EditWindow)
*/
/*NO PARSE JSON*/
sw.Promed.swEvnPLDispOrp13EditWindow = Ext.extend(sw.Promed.BaseForm,
{
	action: 'add',
	autoScroll: true,
	callback: Ext.emptyFn,
	checkEvnPLDispOrp13IsSaved: function() {
		var base_form = this.findById('EvnPLDispOrp13EditForm').getForm();
		if (!(!Ext.isEmpty(base_form.findField('EvnPLDispOrp_id').getValue()) && base_form.findField('EvnPLDispOrp_id').getValue() > 0) || !this.PersonFirstStageAgree || !this.DDICSaved) {
			// дисаблим все разделы кроме информированного добровольного согласия, а также основную кнопки сохранить и печать
			this.EvnUslugaDispOrpPanel.collapse();
			this.EvnUslugaDispOrpPanel.disable();
			this.EvnDiagAndRecomendationPanel.collapse();
			this.EvnDiagAndRecomendationPanel.disable();
			this.EvnDiagDopDispAndRecomendationPanel.collapse();
			this.EvnDiagDopDispAndRecomendationPanel.disable();
			this.EvnPLDispOrpVizitPanel.collapse();
			this.EvnPLDispOrpVizitPanel.disable();
			this.EvnUslugaDispOrpPanel.collapse();
			this.EvnUslugaDispOrpPanel.disable();
			this.DispAppointPanel.collapse();
			this.DispAppointPanel.disable();
			this.findById('EPLDOEW_CommonHealthCheck').collapse();
			this.findById('EPLDOEW_CommonHealthCheck').disable();
			this.buttons[0].hide();
			this.buttons[1].hide();
			this.buttons[2].hide();
			return false;
		} else {
			this.EvnUslugaDispOrpPanel.expand();
			this.EvnUslugaDispOrpPanel.enable();
			this.EvnDiagAndRecomendationPanel.expand();
			this.EvnDiagAndRecomendationPanel.enable();
			this.EvnDiagDopDispAndRecomendationPanel.expand();
			this.EvnDiagDopDispAndRecomendationPanel.enable();
			this.EvnPLDispOrpVizitPanel.expand();
			this.EvnPLDispOrpVizitPanel.enable();
			this.EvnUslugaDispOrpPanel.expand();
			this.EvnUslugaDispOrpPanel.enable();
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
			this.findById('EPLDOEW_CommonHealthCheck').expand();
			this.findById('EPLDOEW_CommonHealthCheck').enable();
			if (this.action != 'view') {
				this.buttons[0].show();
			}
			this.buttons[1].show();
			this.buttons[2].show();
			return true;
		}
	},
	closable: true,
	closeAction: 'hide',
	collapsible: true,
	/* */
	codeRefresh: true,
	objectName: 'swEvnPLDispOrp13EditWindow',
	objectSrc: '/jscore/Forms/Polka/swEvnPLDispOrp13EditWindow.js',
	checkIfSetDateShouldBeDisabled: function()
	{
		// http://redmine.swan.perm.ru/issues/20499
		return false;

		// Дата, обязательно для ввода, по умолчанию текущая дата. Недоступно для редактирования, если сохранен хотя бы один осмотр врача или обследование.
		var win = this;
		var i = 0;
		win.findById('epldo13efEvnVizitDispOrpGrid').getStore().each(function(rec) {
			if ( rec.data.Record_Status != 3 && !Ext.isEmpty(rec.data.EvnVizitDispOrp_id) )
				i++;
		});
		win.findById('epldo13efEvnUslugaDispOrpGrid').getStore().each(function(rec) {
			if ( rec.data.Record_Status != 3 && !Ext.isEmpty(rec.data.EvnUslugaDispOrp_id) )
				i++;
		});
		
		if (i>0) {
			win.findById('epldo13efEvnPLDispOrp_setDate').disable();
		} else {
			win.findById('epldo13efEvnPLDispOrp_setDate').enable();
		}
		
		if (Ext.isEmpty(win.findById('epldo13efEvnPLDispOrp_setDate').getValue())) {
			var set_date = Date.parseDate(getGlobalOptions().date, 'd.m.Y');
			win.findById('epldo13efEvnPLDispOrp_setDate').setValue(set_date);
		}
	},
	deleteEvnVizitDispOrp: function() 
	{
		var win = this;
		
		sw.swMsg.show(
		{
			buttons: sw.swMsg.YESNO,
			fn: function(buttonId, text, obj) 
			{
				if ('yes' == buttonId)
				{
					var current_window = this;
					var evnvizitdispdop_grid = current_window.findById('epldo13efEvnVizitDispOrpGrid');

					if (!evnvizitdispdop_grid.getSelectionModel().getSelected())
					{
						return false;
					}

					var selected_record = evnvizitdispdop_grid.getSelectionModel().getSelected();
					var EvnVizitDispOrp_id = selected_record.get('EvnVizitDispOrp_id');
					
					win.getLoadMask().show();
					Ext.Ajax.request({
						url: '/?c=EvnPLDispOrp13&m=deleteEvnVizitDispOrp',
						params: {EvnVizitDispOrp_id: EvnVizitDispOrp_id},
						failure: function(response, options) {
							win.getLoadMask().hide();
						},
						success: function(response, action) {
							win.getLoadMask().hide();
							win.EvnPLDispOrpVizitPanel.getStore().reload();
					}
						});
					
					// удаляем соответствующую строку из грида "Диагнозы и рекомендации по результатам диспансеризации / профосмотра"
					var index = win.EvnDiagAndRecomendationPanel.getGrid().getStore().findBy(function(rec) {
						if ( rec.get('EvnVizitDispOrp_id') == EvnVizitDispOrp_id ) {
							return true;
						}
						else {
							return false;
						}
					});
					
					if ( index >= 0 ) {
						var record = win.EvnDiagAndRecomendationPanel.getGrid().getStore().getAt(index);
						if (record) {
							win.EvnDiagAndRecomendationPanel.getGrid().getStore().remove(record);
						}
					}

					if (evnvizitdispdop_grid.getStore().getCount() == 0)
					{
						evnvizitdispdop_grid.getTopToolbar().items.items[1].disable();
						evnvizitdispdop_grid.getTopToolbar().items.items[2].disable();
						evnvizitdispdop_grid.getTopToolbar().items.items[3].disable();
					}
					else
					{
						evnvizitdispdop_grid.getView().focusRow(0);
						evnvizitdispdop_grid.getSelectionModel().selectFirstRow();
					}
					
					/*if ( evnvizitdispdop_grid.getStore().getCount() == 0 )
						LoadEmptyRow(evnvizitdispdop_grid);*/
					current_window.checkIfSetDateShouldBeDisabled();
				}
			}.createDelegate(this),
			icon: Ext.MessageBox.QUESTION,
			msg: lang['udalit_osmotr_vracha-spetsialista'],
			title: lang['vopros']
		})
	},
	deleteEvnUslugaDispOrp: function() {
		sw.swMsg.show({
			buttons: sw.swMsg.YESNO,
			fn: function(buttonId, text, obj) {
				if ('yes' == buttonId)
				{
					var current_window = this;
					var evnuslugadispdop_grid = current_window.findById('epldo13efEvnUslugaDispOrpGrid');

					if (!evnuslugadispdop_grid.getSelectionModel().getSelected())
					{
						return false;
					}					

					var selected_record = evnuslugadispdop_grid.getSelectionModel().getSelected();
					if (selected_record.data.Record_Status == 0)
					{
						evnuslugadispdop_grid.getStore().remove(selected_record);
					}
					else
					{
						selected_record.set('Record_Status', 3);
						selected_record.commit();
						evnuslugadispdop_grid.getStore().filterBy(function(record) {
							if (record.data.Record_Status != 3)
							{
								return true;
							}
						});
					}

					if (evnuslugadispdop_grid.getStore().getCount() == 0)
					{
						evnuslugadispdop_grid.getTopToolbar().items.items[1].disable();
						evnuslugadispdop_grid.getTopToolbar().items.items[2].disable();
						evnuslugadispdop_grid.getTopToolbar().items.items[3].disable();
					}
					else
					{
						evnuslugadispdop_grid.getView().focusRow(0);
						evnuslugadispdop_grid.getSelectionModel().selectFirstRow();
					}
					
					/*if ( evnuslugadispdop_grid.getStore().getCount() == 0 )
						LoadEmptyRow(evnuslugadispdop_grid);*/
					current_window.checkIfSetDateShouldBeDisabled();
				}
			}.createDelegate(this),
			icon: Ext.MessageBox.QUESTION,
			msg: lang['udalit_laboratornoe_issledovanie'],
			title: lang['vopros']
		})
	},
	draggable: true,
	doSave: function(callback, print, check_finish, options) {
		options = options||{};
		var current_window = this;

		var base_form = current_window.findById('EvnPLDispOrp13EditForm').getForm();
		var evnpldispdop_form = current_window.findById('EvnPLDispOrp13EditForm');
		var evnvizitdispdop_grid = current_window.findById('epldo13efEvnVizitDispOrpGrid');
		var evnuslugadispdop_grid = current_window.findById('epldo13efEvnUslugaDispOrpGrid');
		var evndiagandrecomendation_grid = current_window.EvnDiagAndRecomendationPanel.getGrid();
		var evndiagdopdispandrecomendation_grid = current_window.EvnDiagDopDispAndRecomendationPanel.getGrid();
		var i = 0;

		if (!evnpldispdop_form.getForm().isValid())
		{
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					// current_window.findById('epldo13efAttachTypeCombo').focus(false);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		//  на второй этап диспансеризации могут быть переведены пациенты окончившие первый этап
		if ( base_form.findField('EvnPLDispOrp_IsTwoStage').getValue() == 2 ) {
			if ( base_form.findField('EvnPLDispOrp_IsFinish').getValue() != 2 ) {
				sw.swMsg.alert(lang['oshibka'], lang['na_vtoroy_etap_dispanserizatsii_mogut_byit_perevedenyi_tolko_patsientyi_okonchivshie_pervyiy_etap']);
				return false;
			}
		}

		if (
			getRegionNick() == 'adygeya'
			&& base_form.findField('EvnPLDispOrp_IsSuspectZNO').getValue() == 2
			&& base_form.findField('HealthKind_id').getValue() == 1
		) {
			sw.swMsg.alert('Ошибка', 'Нельзя выбрать I группу здоровья при подозрении на ЗНО.');
			return false;
		}

		if (
			!Ext.isEmpty(base_form.findField('HealthKind_id').getValue())
			&& base_form.findField('HealthKind_id').getValue() == 1
			&& evndiagandrecomendation_grid.getStore().getCount() > 0
			&& !Ext.isEmpty(evndiagandrecomendation_grid.getStore().getAt(0).get('EvnVizitDispOrp_id'))
		) {
			sw.swMsg.alert(lang['oshibka'], 'Нельзя выбрать I группу здоровья при указании диагнозов и рекомендаций по результатам диспансеризации / профосмотра');
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
			return false;
		}

		// Проверяем заполнение данных в диагнозах и рекомендациях по результатам диспансеризации / профосмотра
		// @task https://redmine.swan.perm.ru/issues/77880
		if (
			base_form.findField('EvnPLDispOrp_IsFinish').getValue() == 2 
			&& evndiagandrecomendation_grid.getStore().getCount() > 0
			&& !Ext.isEmpty(evndiagandrecomendation_grid.getStore().getAt(0).get('EvnVizitDispOrp_id'))
			&& !getRegionNick().inlist([ 'ufa' ])
		) {
			var
				FormDataJSON,
				noRequiredData = false;

			evndiagandrecomendation_grid.getStore().each(function(rec) {
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
				sw.swMsg.alert(lang['oshibka'], lang['ne_zapolnenyi_obyazatelnyie_polya_v_razdele_diagnozyi_i_rekomendatsii_po_rezultatam_dispanserizatsii_profosmotra']);
				return false;
			}
		}

		// https://redmine.swan.perm.ru/issues/20001
		// 2. При сохранении карты диспансеризации реализовать контроль: Дата оказания любой услуги (осмотра/исследования) должна быть не раньше, чем за 3 месяца до
		// осмотра врача-педиатра. При невыполнении данного контроля выводить сообщение: "Дата осмотра/исследования, проведенного ранее должна быть не раньше, чем за
		// 3 месяца до проведения осмотра врача-педиатра", сохранение отменить.
		var EvnUslugaDispOrp_maxDate, EvnUslugaDispOrp_minDate, EvnUslugaDispOrp_fluDate, EvnUslugaDispOrp_neiroDate, EvnUslugaDispOrp_ultrazDate, EvnVizitDispOrp_pedDate, EvnVizitDispOrp_maxDate, EvnVizitDispOrp_minDate, maxDate, minDate;
		var EvnPLDispOrp_setDate = base_form.findField('EvnPLDispOrp_setDate').getValue();
		var FluoUslugaComplex_Code = ''; // Код услуги для флюорографии
		var NeiroUslugaComplex_Code = ''; // Код услуги для нейросонографии
		var UltrazUslugaComplex_Code = ''; // Код услуги для ультразвукового исследования тазобедренных суставов

		// Ищем код услуги "Флюорография"
		// Необходимо для https://redmine.swan.perm.ru/issues/22793
		this.dopDispInfoConsentGrid.getGrid().getStore().each(function(rec) {
			if ( rec.get('SurveyType_Code') == 16 ) {
				FluoUslugaComplex_Code = rec.get('UslugaComplex_Code');
			} else if ( rec.get('SurveyType_Code') == 26 ) {
				NeiroUslugaComplex_Code = rec.get('UslugaComplex_Code');
			} else if ( rec.get('SurveyType_Code') == 25 ) {
				UltrazUslugaComplex_Code = rec.get('UslugaComplex_Code');
			}
		});

		var age = swGetPersonAge(this.PersonInfoPanel.getFieldValue('Person_Birthday'), EvnPLDispOrp_setDate);

		if ( age == -1 ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.ERROR,
				msg: "Ошибка при определении возраста пациента",
				title: lang['oshibka']
			});
			return false;
		}

		// Вытаскиваем минимальную и максимальную дату осмотра и дату осмотра врачом терапевтом
		this.EvnPLDispOrpVizitPanel.getStore().each(function(rec) {
			if ( rec.get('OrpDispSpec_Code') == 1 ) {
				EvnVizitDispOrp_pedDate = rec.get('EvnVizitDispOrp_setDate');
			}
			else {
				if ( Ext.isEmpty(EvnVizitDispOrp_maxDate) || EvnVizitDispOrp_maxDate < rec.get('EvnVizitDispOrp_setDate') ) {
					EvnVizitDispOrp_maxDate = rec.get('EvnVizitDispOrp_setDate');
				}

				if ( Ext.isEmpty(EvnVizitDispOrp_minDate) || EvnVizitDispOrp_minDate > rec.get('EvnVizitDispOrp_setDate') ) {
					EvnVizitDispOrp_minDate = rec.get('EvnVizitDispOrp_setDate');
				}
			}
		});

		// https://redmine.swan.perm.ru/issues/20485
		// Дата осмотра врача-педиатра не может быть больше 14 дней, чем дата начала диспансеризации (отдельное поле есть в карте). При невыполнении контроля выводить
		// сообщение "Длительность 1 этапа диспансеризации несовершеннолетнего не может быть больше 10 рабочих дней. ОК". Сохранение отменить
		if ( getRegionNick() != 'ekb' && !Ext.isEmpty(EvnVizitDispOrp_pedDate) && EvnVizitDispOrp_pedDate.add(Date.DAY, -14) > EvnPLDispOrp_setDate ) {
			// @task https://redmine.swan.perm.ru/issues/146666
			if ( getRegionNick() == 'krym' ) {
				if ( !options.ignoreOsmotrDlit ) {
			sw.swMsg.show({
						buttons: Ext.Msg.YESNO,
						fn: function ( buttonId ) {
							if ( buttonId == 'yes' ) {
								options.ignoreOsmotrDlit = true;
								current_window.doSave(callback, print, check_finish, options);
							}
							else {
								return false;
							}
						},
						msg: 'Длительность 1 этапа диспансеризации несовершеннолетнего не может быть больше 10 рабочих дней. Продолжить сохранение?',
						title: 'Предупреждение'
					});
					return false;
				}
			}
			else {
				sw.swMsg.show({
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.ERROR,
				msg: "Длительность 1 этапа диспансеризации несовершеннолетнего не может быть больше 10 рабочих дней.",
				title: lang['oshibka']
			});
			return false;
		}
		}

		// https://redmine.swan.perm.ru/issues/20499
		// Дата осмотра врача-педиатра не может быть раньше, чем дата начала диспансеризации
		if ( !Ext.isEmpty(EvnVizitDispOrp_pedDate) && EvnVizitDispOrp_pedDate < EvnPLDispOrp_setDate ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.ERROR,
				msg: "Дата осмотра врача-педиатра не может быть раньше, чем дата начала диспансеризации.",
				title: lang['oshibka']
			});
			return false;
		}

		// Вытаскиваем минимальную и максимальную дату услуги, а также дату проведения флюорографии
		this.EvnUslugaDispOrpPanel.getStore().each(function(rec) {
			if ( !Ext.isEmpty(FluoUslugaComplex_Code) && rec.get('UslugaComplex_Code') == FluoUslugaComplex_Code ) {
				EvnUslugaDispOrp_fluDate = rec.get('EvnUslugaDispOrp_setDate'); // Дата проведения флюорографии
			} else if ( !Ext.isEmpty(NeiroUslugaComplex_Code) && rec.get('UslugaComplex_Code') == NeiroUslugaComplex_Code ) {
				EvnUslugaDispOrp_neiroDate = rec.get('EvnUslugaDispOrp_setDate'); // Дата проведения нейросонографии
			} else if ( !Ext.isEmpty(UltrazUslugaComplex_Code) && rec.get('UslugaComplex_Code') == UltrazUslugaComplex_Code ) {
				EvnUslugaDispOrp_ultrazDate = rec.get('EvnUslugaDispOrp_setDate'); // Дата проведения ультразвукового исследования тазобедренных суставов
			}
			else {
				if ( Ext.isEmpty(EvnUslugaDispOrp_maxDate) || EvnUslugaDispOrp_maxDate < rec.get('EvnUslugaDispOrp_setDate') ) {
					EvnUslugaDispOrp_maxDate = rec.get('EvnUslugaDispOrp_setDate');
				}

				if ( Ext.isEmpty(EvnUslugaDispOrp_minDate) || EvnUslugaDispOrp_minDate > rec.get('EvnUslugaDispOrp_setDate') ) {
					EvnUslugaDispOrp_minDate = rec.get('EvnUslugaDispOrp_setDate');
				}
			}
		});

		// Получаем максимальную дату осмотра/исследования
		if ( !Ext.isEmpty(EvnVizitDispOrp_maxDate) ) {
			maxDate = EvnVizitDispOrp_maxDate;
		}

		if ( Ext.isEmpty(maxDate) || (!Ext.isEmpty(EvnUslugaDispOrp_maxDate) && maxDate < EvnUslugaDispOrp_maxDate) ) {
			maxDate = EvnUslugaDispOrp_maxDate;
		}

		// Получаем минимальную дату осмотра/исследования
		if ( !Ext.isEmpty(EvnVizitDispOrp_minDate) ) {
			minDate = EvnVizitDispOrp_minDate;
		}

		if ( Ext.isEmpty(minDate) || (!Ext.isEmpty(EvnUslugaDispOrp_minDate) && minDate > EvnUslugaDispOrp_minDate) ) {
			minDate = EvnUslugaDispOrp_minDate;
		}

		// https://redmine.swan.perm.ru/issues/20485
		// Дата осмотра врача-педиатра должна быть больше (равна) датам всех остальных осмотров / исследований. При невыполнение данного контроля выводить сообщение:
		// "Дата осмотра / исследования по диспансеризации несовершеннолетнего не может быть больше даты осмотра врача-педиатра. ОК ". Сохранение карты отменить.
		if ( !Ext.isEmpty(maxDate) && !Ext.isEmpty(EvnVizitDispOrp_pedDate) && maxDate > EvnVizitDispOrp_pedDate ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.ERROR,
				msg: "Дата осмотра/исследования по диспансеризации несовершеннолетнего не может быть больше даты осмотра врача-педиатра.",
				title: lang['oshibka']
			});
			return false;
		}

		// https://redmine.swan.perm.ru/issues/20485
		// Для детей младше 2 лет.
		// Дата любого исследования / осмотра не может быть меньше 1 месяца, чем дата осмотра врача-педиатра. При невыполнении выводить сообщение "Дата любого
		// исследования не может быть раньше, чем 1 месяц до даты осмотра врача-педиатра. ОК". Сохранение отменить
		// Для детей старше 2 лет.
		// Дата любого исследования / осмотра не может быть меньше 3 месяцев, чем дата осмотра врача-педиатра. При невыполнении выводить сообщение "Дата любого
		// исследования не может быть раньше, чем 3 месяца до даты осмотра врача-педиатра. ОК". Сохранение отменить
		if ( !Ext.isEmpty(minDate) && !Ext.isEmpty(EvnVizitDispOrp_pedDate) && minDate < EvnVizitDispOrp_pedDate.add(Date.MONTH, (age < 2 ? -1 : -3)) ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.ERROR,
				msg: "Дата любого исследования не может быть раньше, чем " + (age < 2 ? "1 месяц" : "3 месяца") + " до даты осмотра врача-педиатра.",
				title: lang['oshibka']
			});
			return false;
		}

		// http://redmine.swan.perm.ru/issues/21226
		// Дата исследования "Флюорография" не может быть меньше 12 месяца, чем дата осмотра врача-педиатра. При невыполнении выводить сообщение "Дата исследования 
		// "Флюорография" не может быть раньше, чем 12 месяцев до даты осмотра врача-педиатра. ОК". Сохранение отменить.
		if ( !Ext.isEmpty(EvnUslugaDispOrp_fluDate) && !Ext.isEmpty(EvnVizitDispOrp_pedDate) && EvnUslugaDispOrp_fluDate < EvnVizitDispOrp_pedDate.add(Date.MONTH, -12) ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.ERROR,
				msg: lang['data_issledovaniya_flyuorografiya_ne_mojet_byit_ranshe_chem_12_mesyatsev_do_datyi_osmotra_vracha-pediatra'],
				title: lang['oshibka']
			});
			return false;
		}

		// http://redmine.swan.perm.ru/issues/88167
		// Дата исследования "Ультразвуковое исследование тазобедренных суставов" не может быть меньше 12 месяца, чем дата осмотра врача-педиатра. При невыполнении выводить сообщение "Дата исследования
		// "Ультразвуковое исследование тазобедренных суставов" не может быть раньше, чем 12 месяцев до даты осмотра врача-педиатра. ОК". Сохранение отменить.
		if ( !Ext.isEmpty(EvnUslugaDispOrp_ultrazDate) && !Ext.isEmpty(EvnVizitDispOrp_pedDate) && EvnUslugaDispOrp_ultrazDate < EvnVizitDispOrp_pedDate.add(Date.MONTH, -12) ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.ERROR,
				msg: 'Дата исследования "Ультразвуковое исследование тазобедренных суставов" не может быть раньше, чем 12 месяцев до даты осмотра врача-педиатра.',
				title: lang['oshibka']
			});
			return false;
		}

		// http://redmine.swan.perm.ru/issues/88167
		// Дата исследования "Нейросонография" не может быть меньше 12 месяца, чем дата осмотра врача-педиатра. При невыполнении выводить сообщение "Дата исследования
		// "Нейросонография" не может быть раньше, чем 12 месяцев до даты осмотра врача-педиатра. ОК". Сохранение отменить.
		if ( !Ext.isEmpty(EvnUslugaDispOrp_neiroDate) && !Ext.isEmpty(EvnVizitDispOrp_pedDate) && EvnUslugaDispOrp_neiroDate < EvnVizitDispOrp_pedDate.add(Date.MONTH, -12) ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.ERROR,
				msg: 'Дата исследования "Нейросонография" не может быть раньше, чем 12 месяцев до даты осмотра врача-педиатра.',
				title: lang['oshibka']
			});
			return false;
		}

		// https://redmine.swan.perm.ru/issues/20001
		// 2. Группа состояния здоровья. Комбобокс выбор из справочника (обязательно для заполнения, если сохранен осмотр врача-педиатра)
		if ( !Ext.isEmpty(EvnVizitDispOrp_pedDate) && Ext.isEmpty(base_form.findField('HealthKind_id').getValue()) ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					base_form.findField('HealthKind_id').focus(true);
				},
				icon: Ext.Msg.ERROR,
				msg: lang['pole_gruppa_zdorovya_obyazatelno_dlya_zapolneniya_esli_proveden_osmotr_vracha-pediatra'],
				title: lang['oshibka']
			});
			return false;
		}

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
					msg: lang['pri_vyibrannoy_invalidnosti_neobhodimo_zapolnit_blok_vidyi_narusheniy_hotya_byi_odnim_znacheniem'],
					title: lang['oshibka']
				});
				return false;
			}
		}

		//проверки из задачи https://redmine.swan.perm.ru/issues/74660
		if(!options.ignoreRequiredFields && getGlobalOptions().disp_control != 1) //Если выбрано предупреждение или запрет
		{
			//Получаем возраст и пол:
			var age = current_window.PersonInfoPanel.getFieldValue('Person_Age');
			var sex_id = current_window.PersonInfoPanel.getFieldValue('Sex_id');//1-муж, 2-жен
			var fields_list = "";

			if ( !Ext.isEmpty(base_form.findField('EvnPLDispOrp_setDate').getValue()) ) {
				age = swGetPersonAge(current_window.PersonInfoPanel.getFieldValue('Person_Birthday'), base_form.findField('EvnPLDispOrp_setDate').getValue());
			}

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
				fields_list += lang['massa_kg'];
			}

			if(Ext.isEmpty(base_form.findField('AssessmentHealth_Height').getValue())) {
				fields_list += lang['rost_sm'];
			}

			if(Ext.isEmpty(this.PersonInfoPanel.getFieldValue('DocumentType_id'))) {
				fields_list += lang['tip_dokumenta'];
			}

			if(Ext.isEmpty(this.PersonInfoPanel.getFieldValue('Document_Num'))) {
				fields_list += lang['nomer_dokumenta'];
			}

			// в зависимости от типа полиса номер или единый номер
			if(Ext.isEmpty(this.PersonInfoPanel.getFieldValue('Polis_Num'))) {
				fields_list += lang['nomer_polisa'];
			}

			if(Ext.isEmpty(this.PersonInfoPanel.getFieldValue('UAddress_id')) && Ext.isEmpty(this.PersonInfoPanel.getFieldValue('PAddress_id'))) {
				fields_list += lang['adres_registratsii_projivaniya'];
			}

			if(fields_list.length > 0)
			{
				if(getGlobalOptions().disp_control == 2 && !options.ignoreEmptyFields)
				{
					sw.swMsg.show({
						buttons: Ext.Msg.YESNO,
						fn: function ( buttonId ) {
							if ( buttonId == 'yes' )
							{
								options.ignoreEmptyFields = true;
								current_window.doSave(callback, print, check_finish, options);
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

		// Закомментировал проверку для Екатеринбурга
		// @task https://redmine.swan.perm.ru/issues/106244
		/*if (getRegionNick().inlist(['ekb'])) {
			// Для Екатеринбруга При сохранении карты диспансеризации, если в поле «Случай закончен» выбрано значение «Да», то должны быть сохранены все осмотры / исследования, с услугами у которых SurveyTypeLink_IsNeedUsluga = Yes. При невыполнении данного контроля выводить сообщение «Сохранены не все обязательные осмотры / исследования. ОК» , сохранение карты отменить.
			if ( base_form.findField('EvnPLDispOrp_IsFinish').getValue() == 2 ) {
				// считаем количество сохраненных осмотров/исследований
				var savedAll = true;

				var usedOrpDispSpecCodeList = [];
				current_window.findById('epldo13efEvnVizitDispOrpGrid').getStore().each(function(rec) {
					if ( rec.data.Record_Status != 3 )
						usedOrpDispSpecCodeList.push(rec.data.OrpDispSpec_Code);
				});
				
				var usedUslugaComplexCodeList = [];
				current_window.findById('epldo13efEvnUslugaDispOrpGrid').getStore().each(function(rec) {
					if ( rec.data.Record_Status != 3 )
						usedUslugaComplexCodeList.push(rec.data.UslugaComplex_Code);
				});
				
				current_window.dopDispInfoConsentGrid.getGrid().getStore().each(function(rec) {
					if ( rec.get('SurveyTypeLink_IsNeedUsluga') == 2 ) {
						if (!Ext.isEmpty(rec.get('OrpDispSpec_Code')) && !rec.get('OrpDispSpec_Code').inlist(usedOrpDispSpecCodeList)) {
							savedAll = false;
						} else if (Ext.isEmpty(rec.get('OrpDispSpec_Code')) && !Ext.isEmpty(rec.get('UslugaComplex_Code')) && !rec.get('UslugaComplex_Code').inlist(usedUslugaComplexCodeList)) {
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
			// нет каких либо ограничений по количеству заведенных услуг
			if ( base_form.findField('EvnPLDispOrp_IsFinish').getValue() == 2 && Ext.isEmpty(EvnVizitDispOrp_pedDate) ) {
				sw.swMsg.alert(lang['oshibka'], lang['data_vyipolneniya_osmotra_vracha_pediatra_obyazatelna_dlya_zapolneniya']);
				return false;
			}
		} else {
			// проверка на заполненность всех услуг и посещений, если пытаются закрыть карту по ДД
			var spec_is_full = false;
			var usluga_is_full = false;
			
			var usedOrpDispSpecCodeList = [];
			current_window.findById('epldo13efEvnVizitDispOrpGrid').getStore().each(function(rec) {
				if ( rec.data.Record_Status != 3 )
					usedOrpDispSpecCodeList.push(rec.data.OrpDispSpec_Code);
			});
				
			var orpDispSpecAllowed = current_window.orpDispSpecAllowed;
			var neworpDispSpecAllowed = [];
			// убираем из списка занятые специальности (usedOrpDispSpecCodeList)
			for(var key in orpDispSpecAllowed) {
				if (typeof orpDispSpecAllowed[key] == 'string' && !orpDispSpecAllowed[key].inlist(usedOrpDispSpecCodeList)) {
					neworpDispSpecAllowed.push(orpDispSpecAllowed[key]);
				}
			}
			
			var pedSpec = 1;
			// педиатр должен быть сохранён обязательно при закрытии, проверяем, даже если не дано согласие на педиатра.
			if (neworpDispSpecAllowed.length == 0 && !pedSpec.inlist(usedOrpDispSpecCodeList)) {
				neworpDispSpecAllowed.push(pedSpec);
			}
			
			if (neworpDispSpecAllowed.length == 0) {
				spec_is_full = true;
			}
			
			var usedUslugaComplexCodeList = [];
			current_window.findById('epldo13efEvnUslugaDispOrpGrid').getStore().each(function(rec) {
				if ( rec.data.Record_Status != 3 )
					usedUslugaComplexCodeList.push(rec.data.UslugaComplex_Code);
			});
				
			var uslugaComplexAllowed = current_window.uslugaComplexAllowed;
			var newuslugaComplexAllowed = [];
			// убираем из списка занятые услуги (usedUslugaComplexCodeList)
			for(var key in uslugaComplexAllowed) {
				if (typeof uslugaComplexAllowed[key] == 'string' && !uslugaComplexAllowed[key].inlist(usedUslugaComplexCodeList)) {
					newuslugaComplexAllowed.push(uslugaComplexAllowed[key]);
				}
			}
			
			if (newuslugaComplexAllowed.length == 0) {
				usluga_is_full = true;
			}
			
			if (typeof callback == 'function') {
				// если карта ещё не была сохранена значит точно не все услуги введены
				spec_is_full = false;
				usluga_is_full = false;
			}
			
			if ( Ext.getCmp('epldo13efIsFinishCombo').getValue() == 2 )
			{			
				if ( !spec_is_full || !usluga_is_full )
				{
					sw.swMsg.show({
						buttons: Ext.Msg.OK,
						fn: function() {
							current_window.findById('epldo13efIsFinishCombo').setValue(1);
							current_window.findById('epldo13efIsFinishCombo').focus();
						},
						icon: Ext.Msg.WARNING,
						msg: "Случай не может быть закончен, так как заполнены не все исследования или осмотры.",
						title: ERR_INVFIELDS_TIT
					});
					return false;
				}
			}
			else
			{
				if ( spec_is_full && usluga_is_full && check_finish != 2  )
				{
					sw.swMsg.show({
						buttons: Ext.Msg.YESNO,
						fn: function(buttonId, text, obj) {
							if (buttonId == 'yes')
							{
								Ext.getCmp('epldo13efIsFinishCombo').setValue(2);
								current_window.doSave(null, print, 2, options);
							}
							else
								current_window.doSave(null, print, 2, options);
						},
						icon: Ext.MessageBox.QUESTION,
						msg: lang['v_karte_zapolnenyi_vse_neobhodimyie_dannyie_prostavit_priznak_zakonchennosti_sluchaya'],
						title: lang['vopros']
					});
					return;
				}
			}
		}

		// Функция get_grid_records возвращает записи из store
		function get_grid_records(store, save_trigger)
		{
			var fields = new Array();
			var result = new Array();

			store.fields.eachKey(function(key, item) {
				if (save_trigger == true && key.indexOf('_Name') == -1 && key.indexOf('_Fio') == -1)
				{
					fields.push(key);
				}
				else if (save_trigger == false)
				{
					fields.push(key);
				}
			});

			store.clearFilter();
			store.each(function(record) {
				if (record.data.Record_Status == 0 || record.data.Record_Status == 2 || record.data.Record_Status == 3)
				{
					var temp_record = new Object();
					for (i = 0; i < fields.length; i++)
					{
						if (save_trigger == true && fields[i].indexOf('Date') != -1)
						{
							temp_record[fields[i]] = Ext.util.Format.date(record.data[fields[i]], 'd.m.Y');
						}
						else
						{
							temp_record[fields[i]] = record.data[fields[i]];
						}
					}
					result.push(temp_record);
				}
			});

			store.filterBy(function(record) {
				if (record.data.Record_Status != 3)
				{
					return true;
				}
			});

			return result;
		}

		// Собираем данные из гридов
		var params = new Object();
		params.EvnVizitDispOrp = Ext.util.JSON.encode(get_grid_records(evnvizitdispdop_grid.getStore(), true));
		params.EvnUslugaDispOrp = Ext.util.JSON.encode(get_grid_records(evnuslugadispdop_grid.getStore(), true));
		params.EvnDiagAndRecomendation = Ext.util.JSON.encode(getStoreRecords(evndiagandrecomendation_grid.getStore()));

		if ( base_form.findField('EvnPLDispOrp_setDate').disabled )  {
			params.EvnPLDispOrp_setDate = Ext.util.Format.date(EvnPLDispOrp_setDate, 'd.m.Y');
		}
		
		if ( base_form.findField('ChildStatusType_id').disabled )  {
			params.ChildStatusType_id = base_form.findField('ChildStatusType_id').getValue();
		}
		
		if ( base_form.findField('PayType_id').disabled )  {
			params.PayType_id = base_form.findField('PayType_id').getValue();
		}
		params.ignoreParentEvnDateCheck = (!Ext.isEmpty(options.ignoreParentEvnDateCheck) && options.ignoreParentEvnDateCheck === 1) ? 1 : 0;
		params.checkAttributeforLpuSection = (!Ext.isEmpty(options.checkAttributeforLpuSection)) ? options.checkAttributeforLpuSection : 0;
		if(base_form.findField('EvnPLDispOrp_IsMobile').checked){
			params.checkAttributeforLpuSection=2;
		}

		var vaccinSelected = [];
		var vaccinFieldset = this.findById('EPLDO13EW_VaccinFieldset');
		vaccinFieldset.items.items.forEach( function (item) {
			if (item.checked) {
				vaccinSelected.push(item.value);
			}
		});
		params.AssessmentHealthVaccinData = Ext.util.JSON.encode(vaccinSelected);

		// если "Проведение профилактических прививок" == "нуждается в проведении вакцинации (ревакцинации)", обязателен хотя бы один чекбокс
		if (base_form.findField('ProfVaccinType_id').getValue() == 6 && vaccinSelected.length <= 0) {
			sw.swMsg.alert(lang['oshibka'], lang['dlya_patsienta_nujdayuschegosya_v_provedenii_vaktsinatsii_revaktsinatsii_doljna_byit_vyibrana_hotya_byi_odna_privivka']);
			return false;
		}

		var loadMask = new Ext.LoadMask(Ext.get('EvnPLDispOrp13EditWindow'), { msg: "Подождите, идет сохранение..." });
		loadMask.show();

		evnpldispdop_form.getForm().submit({
			failure: function(result_form, action) {
				loadMask.hide();
				if (action.result)
				{
					if (action.result.Alert_Msg) {
						sw.swMsg.show({
							buttons: Ext.Msg.YESNO,
							fn: function(buttonId, text, obj) {
								if ( buttonId == 'yes' ) {
									switch (action.result.Error_Code) {
										case 109:
											options.ignoreParentEvnDateCheck = 1;
											break;
										case 110:
											options.checkAttributeforLpuSection = 2;
											break;
									}
									
									current_window.doSave(callback, print, check_finish, options);
								
								}else{
									switch (action.result.Error_Code) {
										case 110:
											options.checkAttributeforLpuSection = 1;
											current_window.doSave(callback, print, check_finish, options);
											break;
									}
								}
							}.createDelegate(this),
							icon: Ext.MessageBox.QUESTION,
							msg: action.result.Alert_Msg,
							title: lang['prodoljit_sohranenie']
						});
					} else
					if (action.result.Error_Msg)
					{
						if ( action.result.Error_Code && action.result.Error_Code != 666 )
						{
							switch ( action.result.Error_Code )
							{
								// не все исследования и осмотры
								case 10:
									Ext.Msg.alert(lang['oshibka'], action.result.Error_Msg, function() {
										Ext.getCmp('epldo13efIsFinishCombo').focus(true, 200);
									});
								break;
								default:
									Ext.Msg.alert(lang['oshibka'], action.result.Error_Msg);
							}
						}
						/*else
							Ext.Msg.alert(lang['oshibka'], action.result.Error_Msg);*/
					}
					else
					{
						Ext.Msg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_1]']);
					}
				}
			},
			params: params,
			success: function(result_form, action) {
				if (action.result)
				{
					if (!Ext.isEmpty(action.result.EvnPLDispOrp_id)) {
						current_window.findById('epldo13efEvnPLDispOrp_id').setValue(action.result.EvnPLDispOrp_id);
					}
					var evnpldispdop_id = current_window.findById('epldo13efEvnPLDispOrp_id').getValue();
					
					loadMask.hide();
					if ( print )
					{
						// перечитать данные гридов
						// загрузка грида осмотров
						current_window.EvnPLDispOrpVizitPanel.getStore().load({ 
							params: { EvnPLDispOrp_id: evnpldispdop_id },
							callback: function() {
								if ( Ext.getCmp('epldo13efEvnVizitDispOrpGrid').getStore().getCount() == 0 )
									LoadEmptyRow(Ext.getCmp('epldo13efEvnVizitDispOrpGrid'));
							}
						});

						// загрузка грида "Диагнозы и рекомендации по результатам диспансеризации / профосмотра"
						current_window.EvnDiagAndRecomendationPanel.loadData({
							params: {
								EvnPLDispOrp_id: evnpldispdop_id
							},
							globalFilters: {
								EvnPLDispOrp_id: evnpldispdop_id
							},
							noFocusOnLoad: true
						});

						// загрузка грида "Диагнозы и рекомендации по результатам диспансеризации / профосмотра"
						current_window.EvnDiagDopDispAndRecomendationPanel.loadData({
							params: {
								EvnPLDisp_id: evnpldispdop_id
							},
							globalFilters: {
								EvnPLDisp_id: evnpldispdop_id
							},
							noFocusOnLoad: true
						});

						// загрузка грида обследований
						current_window.EvnUslugaDispOrpPanel.getStore().load({ 
							params: { EvnPLDispOrp_id: evnpldispdop_id },
							callback: function() {
								if ( Ext.getCmp('epldo13efEvnUslugaDispOrpGrid').getStore().getCount() == 0 )
									LoadEmptyRow(Ext.getCmp('epldo13efEvnUslugaDispOrpGrid'));
							}
						});
								
						var evn_pl_id = current_window.findById('epldo13efEvnPLDispOrp_id').getValue();
						var server_id = current_window.findById('epldo13efServer_id').getValue();
						window.open(C_EPLDO13_PRINT + '&EvnPLDispOrp_id=' + evn_pl_id + '&Server_id=' + server_id, '_blank');
					}
					else
					{
						current_window.callback();
						if (typeof callback == 'function') {
							callback();
						} else {
							current_window.hide();
						}
					}
				}
				else
				{
					loadMask.hide();
					Ext.Msg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_2]']);
				}
			}
		});
	},
	loadUslugaComplex: function() {
		var win = this;
		var base_form = win.findById('EvnPLDispOrp13EditForm').getForm();
		var UslugaComplex_id = base_form.findField('UslugaComplex_id').getValue();

		if (getRegionNick() == 'buryatiya') {
			base_form.findField('UslugaComplex_id').clearValue();
			base_form.findField('UslugaComplex_id').getStore().baseParams.dispOnly = 1;
			base_form.findField('UslugaComplex_id').getStore().baseParams.DispClass_id = base_form.findField('DispClass_id').getValue();
			base_form.findField('UslugaComplex_id').getStore().baseParams.UslugaComplex_Date = (typeof base_form.findField('EvnPLDispOrp_setDate').getValue() == 'object' ? Ext.util.Format.date(base_form.findField('EvnPLDispOrp_setDate').getValue(), 'd.m.Y') : base_form.findField('EvnPLDispOrp_setDate').getValue());
			base_form.findField('UslugaComplex_id').getStore().baseParams.Person_id = base_form.findField('Person_id').getValue();
			base_form.findField('UslugaComplex_id').getStore().load({
				callback: function() {
					if (base_form.findField('UslugaComplex_id').getStore().getCount() > 0) {
						var index = 0;

						if ( !Ext.isEmpty(UslugaComplex_id) ) {
							index = base_form.findField('UslugaComplex_id').getStore().findBy(function(rec) {
								return (rec.get('UslugaComplex_id') == UslugaComplex_id);
							});

							if ( index == -1 ) {
								index = 0;
							}
						}

						base_form.findField('UslugaComplex_id').setValue(base_form.findField('UslugaComplex_id').getStore().getAt(index).get('UslugaComplex_id'));
					}
				}
			});
		}
	},
	evnVizitDispOrpEditWindow: null,
	genId: function(obj)
	{
		var id_field = null;
		var index = 0;
		var result = null;
		var store = null;

		switch (obj)
		{

			case 'vizit':
				id_field = 'EvnVizitDispOrp_id';
				store = this.findById('epldo13efEvnVizitDispOrpGrid').getStore();
				break;

			case 'usluga':
				id_field = 'EvnUslugaDispOrp_id';
				store = this.findById('epldo13efEvnUslugaDispOrpGrid').getStore();
				break;

			default:
				return result;
				break;
		}

		while (index >= 0 || result == 0)
		{
			result = Math.floor(Math.random() * 1000000);
			index = store.findBy(function(rec) { return rec.get(id_field) == result; });
		}

		return result;
	},
	height: 570,
	id: 'EvnPLDispOrp13EditWindow',
	saveDopDispInfoConsent: function(options) {
		if ( typeof options != 'object' ) {
			options = new Object();
		}
		
		var win = this;

		if (win.blockSaveDopDispInfoConsent) {
			win.saveDopDispInfoConsentAfterLoad = true;
			return false;
		}

		win.getLoadMask(lang['sohranenie_informirovannogo_dobrovolnogo_soglasiya']).show();
		var base_form = win.findById('EvnPLDispOrp13EditForm').getForm();
		// берём все записи из грида и посылаем на сервер, разбираем ответ
		var form = this.findById('EvnPLDispOrp13EditForm');
		
		// проверить гриды с осмотрами и исследованиями
		var saved = true;
		win.EvnPLDispOrpVizitPanel.getStore().each(function(rec) {
			if (rec.data.Record_Status == 0 || rec.data.Record_Status == 2 || rec.data.Record_Status == 3) {
				saved = false;
			}
		});

		win.EvnUslugaDispOrpPanel.getStore().each(function(rec) {
			if (rec.data.Record_Status == 0 || rec.data.Record_Status == 2 || rec.data.Record_Status == 3) {
				saved = false;
			}
		});	
		
		if (!saved && !options.saveNotSaved) {
			sw.swMsg.show(
			{
				buttons: Ext.Msg.YESNO,
				scope : this,
				fn: function(buttonId) 
				{
					if ( buttonId == 'yes' )
					{
						options.saveNotSaved = true;
						win.saveDopDispInfoConsent(options);
					}
				},
				icon: Ext.Msg.QUESTION,
				msg: lang['ne_vse_osmotryi_obsledovaniya_sohranenyi_deystvitelno_ochistit_kartu'],
				title: lang['vopros']
			});
			
			win.getLoadMask().hide();
			return false;
		}

		var grid = win.dopDispInfoConsentGrid.getGrid();
		var params = {};
		
		params.PersonEvn_id = base_form.findField('PersonEvn_id').getValue();
		params.Server_id = base_form.findField('Server_id').getValue();
		params.DispClass_id = base_form.findField('DispClass_id').getValue();
		params.EvnPLDispOrp_id = base_form.findField('EvnPLDispOrp_id').getValue();
		params.EvnPLDispOrp_consDate = Ext.util.Format.date(form.findById('epldo13efEvnPLDispOrp_consDate').getValue(), 'd.m.Y');
		params.EvnPLDispOrp_setDate = Ext.util.Format.date(form.findById('epldo13efEvnPLDispOrp_setDate').getValue(), 'd.m.Y');
		params.DopDispInfoConsentData = Ext.util.JSON.encode(getStoreRecords( grid.getStore(), {
			exceptionFields: [
				'SurveyType_Name'
			]
		}));
		
		Ext.Ajax.request(
		{
			url: '/?c=EvnPLDispOrp13&m=saveDopDispInfoConsent',
			params: params,
			failure: function(response, options)
			{
				win.getLoadMask().hide();
			},
			success: function(response, action)
			{
				win.getLoadMask().hide();
				if (response.responseText)
				{
					var answer = Ext.util.JSON.decode(response.responseText);
					if (answer.success)
					{
						win.getLoadMask(lang['zagruzka_dannyih_formyi']).show();
						win.DDICSaved = true;
						win.checkEvnPLDispOrp13IsSaved();
						win.reloadUslugaComplexAllowed();

						form.getForm().load({
							failure: function() {
								win.getLoadMask().hide();
							},
							params: {
								EvnPLDispOrp_id: base_form.findField('EvnPLDispOrp_id').getValue()
							},
							success: function() {
								var loadGrid1Finished = false;
								var loadGrid2Finished = false;
								var loadGrid3Finished = false;
								var loadGrid4Finished = false;
								var loadGrid5Finished = false;
								// загрузка грида осмотров
								win.EvnPLDispOrpVizitPanel.getStore().load({ 
									params: { EvnPLDispOrp_id: base_form.findField('EvnPLDispOrp_id').getValue() },
									callback: function() {
										loadGrid1Finished = true;
										if ( loadGrid1Finished && loadGrid2Finished && loadGrid3Finished && loadGrid4Finished && loadGrid5Finished )
										{
											win.getLoadMask().hide();
										}
										if ( Ext.getCmp('epldo13efEvnVizitDispOrpGrid').getStore().getCount() == 0 )
											LoadEmptyRow(Ext.getCmp('epldo13efEvnVizitDispOrpGrid'));
									}
								});
								// загрузка грида "Диагнозы и рекомендации по результатам диспансеризации / профосмотра"
								win.EvnDiagAndRecomendationPanel.loadData({
									params: {
										EvnPLDispOrp_id: base_form.findField('EvnPLDispOrp_id').getValue()
									},
									globalFilters: {
										EvnPLDispOrp_id: base_form.findField('EvnPLDispOrp_id').getValue()
									},
									callback: function() {
										loadGrid2Finished = true;
										if ( loadGrid1Finished && loadGrid2Finished && loadGrid3Finished && loadGrid4Finished && loadGrid5Finished )
										{
											win.getLoadMask().hide();
										}
									},
									noFocusOnLoad: true
								});	
								// загрузка грида "Диагнозы и рекомендации по результатам диспансеризации / профосмотра"
								win.EvnDiagDopDispAndRecomendationPanel.loadData({
									params: {
										EvnPLDisp_id: base_form.findField('EvnPLDispOrp_id').getValue()
									},
									globalFilters: {
										EvnPLDisp_id: base_form.findField('EvnPLDispOrp_id').getValue()
									},
									callback: function() {
										loadGrid3Finished = true;
										if ( loadGrid1Finished && loadGrid2Finished && loadGrid3Finished && loadGrid4Finished && loadGrid5Finished )
										{
											win.getLoadMask().hide();
										}
									},
									noFocusOnLoad: true
								});
								// загрузка грида обследований
								win.EvnUslugaDispOrpPanel.getStore().load({ 
									params: { EvnPLDispOrp_id: base_form.findField('EvnPLDispOrp_id').getValue() },
									callback: function() {
										loadGrid4Finished = true;
										if ( loadGrid1Finished && loadGrid2Finished && loadGrid3Finished && loadGrid4Finished && loadGrid5Finished )
										{
											win.getLoadMask().hide();
										}
										if ( Ext.getCmp('epldo13efEvnUslugaDispOrpGrid').getStore().getCount() == 0 )
											LoadEmptyRow(Ext.getCmp('epldo13efEvnUslugaDispOrpGrid'));
									}
								});

								if (getRegionNick() != 'kz') {
									win.DispAppointGrid.loadData({
										params: {
											EvnPLDisp_id: base_form.findField('EvnPLDispOrp_id').getValue(),
											object: 'EvnPLDispOrp'
										},
										globalFilters: {
											EvnPLDisp_id: base_form.findField('EvnPLDispOrp_id').getValue()
										},
										callback: function() {
											loadGrid5Finished = true;
											if ( loadGrid1Finished && loadGrid2Finished && loadGrid3Finished && loadGrid4Finished && loadGrid5Finished )
											{
												win.getLoadMask().hide();
											}
										},
										noFocusOnLoad: true
									});
								} else {
									loadGrid5Finished = true;
								}
							},
							url: C_EPLDO13_LOAD
						});
					}
				}
			}
		});
	},
	reloadUslugaComplexAllowed: function() {
		var win = this;
		// создать список UslugaComplex разрешенных к выбору в исследованиях
		// создать список OrpDispSpec разрешенных к выбору в осмотрах
		var form = this.findById('EvnPLDispOrp13EditForm');
		var base_form = form.getForm();
		var evnpldispdop_id = form.findById('epldo13efEvnPLDispOrp_id').getValue();
		var person_id = form.findById('epldo13efPerson_id').getValue();
		var set_date = form.findById('epldo13efEvnPLDispOrp_setDate').getValue();
		
		// загружаем грид Информированного добровольного согласия..
		var EvnPLDispOrp_setDate = Ext.util.Format.date(set_date, 'd.m.Y');
		
		if (!Ext.isEmpty(this.lastEvnPLDispOrp_setDate) && EvnPLDispOrp_setDate != this.lastEvnPLDispOrp_setDate)
		{
			this.DDICSaved = false;
			this.checkEvnPLDispOrp13IsSaved();
		}
		
		this.lastEvnPLDispOrp_setDate = EvnPLDispOrp_setDate;
		
		win.dopDispInfoConsentGrid.loadData({
			params: {
				EvnPLDispOrp_id: evnpldispdop_id,
				DispClass_id: base_form.findField('DispClass_id').getValue(),
				Person_id: person_id, 
				EvnPLDispOrp_setDate: EvnPLDispOrp_setDate
			},
			globalFilters: {
				EvnPLDispOrp_id: evnpldispdop_id,
				DispClass_id: base_form.findField('DispClass_id').getValue(),
				Person_id: person_id, 
				EvnPLDispOrp_setDate: EvnPLDispOrp_setDate
			},
			noFocusOnLoad: true,
			callback: function () {
				var birth_date = win.PersonInfoPanel.getFieldValue('Person_Birthday');
				if (!Ext.isEmpty(set_date) && !Ext.isEmpty(birth_date)) {
					var age = (birth_date.getMonthsBetween(set_date) - (birth_date.getMonthsBetween(set_date) % 12)) / 12;
				} else {
					var age = win.PersonInfoPanel.getFieldValue('Person_Age');
				}
				
				var sex_id = win.PersonInfoPanel.getFieldValue('Sex_id');
				
				win.uslugaComplexAllowed = [];
				win.orpDispSpecAllowed = [];
				// фильтруем грид доступных исследований
				/*
				win.dopDispInfoConsentGrid.getGrid().getStore().filterBy(function(rec) {
					var needAdd = true;
					
					if (!Ext.isEmpty(rec.get('OrpDispSpec_Code'))) {
						// возможность выбора «врач-акушер-гинеколог» только для девочек
						if ( rec.get('OrpDispSpec_Code') == 6 && sex_id != 2 ) {
							needAdd = false;
						}
						// возможность выбора «врач-детский уролог-андролог (врач-детский хирург / врач-уролог)» только для мальчиков
						if ( rec.get('OrpDispSpec_Code') == 10 && sex_id != 1 ) {
							needAdd = false;
						}
						// возможность выбора «врач-стоматолог детский (врач-стоматолог)» только для пациентов с возраста 3 лет
						if ( rec.get('OrpDispSpec_Code') == 7 && age <= 3 ) {
							needAdd = false;
						}
						// возможность выбора «врач-детский эндокринолог (врач-эндокринолог)» только для пациентов с возраста 5 лет
						if ( rec.get('OrpDispSpec_Code') == 11 && age <= 5 ) {
							needAdd = false;
						}
						
						// – «психиатр детский» отображать только для пациентов до возраста 14 лет
						if ( rec.get('SurveyType_Code') == 33 && age > 14 ) {
							needAdd = false;
						}
						
						// – «психиатр подростковый» отображать только для пациентов с возраста 14 лет
						if ( rec.get('SurveyType_Code') == 37 && age < 14 ) {
							needAdd = false;
						}
					}
					
					if (!Ext.isEmpty(rec.get('UslugaComplex_Code'))) {
						// «флюорография» отображать только для пациентов с возраста 15 лет
						if ( rec.get('UslugaComplex_Code') == '02002301' && age < 15 ) {
							needAdd = false;
						}
						// «ультразвуковое исследование щитовидной железы» отображать только для пациентов с возраста 7 лет
						if ( rec.get('UslugaComplex_Code') == '02001307' && age < 7 ) {
							needAdd = false;
						}
						// «ультразвуковое исследование органов репродуктивной сферы» отображать только для пациентов с возраста 7 лет
						if ( rec.get('UslugaComplex_Code') == '02001355' && age < 7 ) {
							needAdd = false;
						}
						// «ультразвуковое исследование тазобедренных суставов» отображать только для пациентов до возраста 1 год
						if ( rec.get('UslugaComplex_Code') == '02001356' && age > 1 ) {
							needAdd = false;
						}
						// «нейросонография» отображать только для пациентов до возраста 1 год
						if ( rec.get('UslugaComplex_Code') == '02001314' && age > 1 ) {
							needAdd = false;
						}
					}
					
					
					return needAdd;
				});
				*/
				
				// идём по всем записям, ищем одобренные и запихиваем в uslugaComplexAllowed
				win.dopDispInfoConsentGrid.getGrid().getStore().each(function(rec){
					if (Ext.isEmpty(rec.get('OrpDispSpec_Code')) && !Ext.isEmpty(rec.get('UslugaComplex_Code')) && (rec.get('DopDispInfoConsent_IsAgree') == true || rec.get('DopDispInfoConsent_IsEarlier') == true)) {
						win.uslugaComplexAllowed.push(rec.get('UslugaComplex_Code'));
					}
					if (!Ext.isEmpty(rec.get('OrpDispSpec_Code')) && (rec.get('DopDispInfoConsent_IsAgree') == true || rec.get('DopDispInfoConsent_IsEarlier') == true)) {
					
						var needAdd = true;
/*
						// возможность выбора «врач-акушер-гинеколог» только для девочек
						if ( rec.get('OrpDispSpec_Code') == 6 && sex_id != 2 ) {
							needAdd = false;
						}
						// возможность выбора «врач-детский уролог-андролог (врач-детский хирург / врач-уролог)» только для мальчиков
						if ( rec.get('OrpDispSpec_Code') == 10 && sex_id != 1 ) {
							needAdd = false;
						}
						// возможность выбора «врач-стоматолог детский (врач-стоматолог)» только для пациентов до возраста 3 лет
						if ( rec.get('OrpDispSpec_Code') == 7 && age > 3 ) {
							needAdd = false;
						}
						// возможность выбора «врач-детский эндокринолог (врач-эндокринолог)» только для пациентов до возраста 5 лет
						if ( rec.get('OrpDispSpec_Code') == 11 && age > 5 ) {
							needAdd = false;
						}
*/
						// возможность выбора «врач-психиатр-детский (врач-психиатр)» только для пациентов до возраста 14 лет
						// возможность выбора «врач-психиатр подростковый (врач-психиатр)» только для пациентов с возраста 14 лет
						// врач психиатр в бд один..
						
						// возможность выбора «врач-педиатр (врач общей врачебной практики)» только после ввода осмотров всех остальных специалистов (согласно вышеуказанным контролям)
						if ( rec.get('OrpDispSpec_Code') == 1 ) {
							needAdd = false;
						}
						
						if (needAdd) {
							win.orpDispSpecAllowed.push(rec.get('OrpDispSpec_Code'));
						}
					}
					
					if (!Ext.isEmpty(rec.get('OrpDispSpec_Code')) && rec.get('OrpDispSpec_Code') == 1) {
						if (rec.get('DopDispInfoConsent_IsAgree') == true || rec.get('DopDispInfoConsent_IsEarlier') == true) {
							win.allowPed = true;
						} else {
							win.allowPed = false;
						}
					}
				});

				win.blockSaveDopDispInfoConsent = false;
				if (win.saveDopDispInfoConsentAfterLoad) {
					win.saveDopDispInfoConsent({
						saveNotSaved: true
					});
				}
				win.saveDopDispInfoConsentAfterLoad = false;
			}
		});

	},
	uslugaComplexAllowed: [],
	orpDispSpecAllowed: [],
	openEvnDiagAndRecomendationEditWindow: function(action) {
		var grid = this.EvnDiagAndRecomendationPanel.getGrid();
		var record = grid.getSelectionModel().getSelected();
		
		if (!record) {
			return false;
		}
		
		var params = {
			callback: function(FormDataJSON) {
				// обновляем JSON-поле.
				record.set('FormDataJSON', FormDataJSON);
				record.commit();
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
				msg: lang['okno_sostoyanie_zdorovya_redaktirovanie_uje_otkryito'],
				title: ERR_WND_TIT
			});
			return false;
		}
		params.archiveRecord = this.archiveRecord;
		getWnd('swEvnDiagAndRecomendationEditWindow').show(params);
	},
	deleteEvnDiagDopDispAndRecomendation: function() {
		var win = this;
		var base_form = win.findById('EvnPLDispOrp13EditForm').getForm();
		var grid = this.EvnDiagDopDispAndRecomendationPanel.getGrid();
		
		if (!win.action.inlist(['add','edit'])) {
			return false;
		}
		
		var record = grid.getSelectionModel().getSelected();

		if (!record || Ext.isEmpty(record.get('EvnDiagDopDisp_id'))) {
			return false;
		}
		
		win.getLoadMask(lang['udalenie_diagnoza']).show();
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
		var base_form = win.findById('EvnPLDispOrp13EditForm').getForm();
		var grid = this.EvnDiagDopDispAndRecomendationPanel.getGrid();
		
		if (!win.action.inlist(['add','edit'])) {
			action = 'view';
		}
		
		var params = {
			action: action,
			callback: function() {
				grid.getStore().reload();
			}
		};
		
		if (Ext.isEmpty(base_form.findField('EvnPLDispOrp_id').getValue())) {
			return false;
		}
		
		if (action != 'add') {
			var record = grid.getSelectionModel().getSelected();
			
			if (!record || Ext.isEmpty(record.get('EvnDiagDopDisp_id'))) {
				return false;
			}
			
			params.EvnDiagDopDisp_id = record.get('EvnDiagDopDisp_id');
		}
		
		params.EvnDiagDopDisp_pid = base_form.findField('EvnPLDispOrp_id').getValue();
		params.PersonEvn_id = base_form.findField('PersonEvn_id').getValue();
		params.Server_id = base_form.findField('Server_id').getValue();
		
		if (getWnd('swEvnDiagDopDispAndRecomendationEditWindow').isVisible())
		{
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: Ext.emptyFn,
				icon: Ext.Msg.WARNING,
				msg: lang['okno_sostoyanie_zdorovya_redaktirovanie_uje_otkryito'],
				title: ERR_WND_TIT
			});
			return false;
		}
		params.archiveRecord = this.archiveRecord;
		getWnd('swEvnDiagDopDispAndRecomendationEditWindow').show(params);
	},
	onEnableEdit: function(enable) {
		this.EvnDiagDopDispAndRecomendationPanel.setActionDisabled('action_add', !enable);
	},
	initComponent: function() {
		var win = this;
		
		this.dopDispInfoConsentGrid = new sw.Promed.ViewFrame({
			autoLoadData: false,
			id: 'EPLDO13EF_dopDispInfoConsentGrid',
			dataUrl: '/?c=EvnPLDispOrp13&m=loadDopDispInfoConsent',
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
				{ name: 'MedSpecOms_id', type: 'int', hidden: true },
				{ name: 'SurveyType_Code', type: 'int', hidden: true },
				{ name: 'UslugaComplex_Code', type: 'string', hidden: true },
				{ name: 'OrpDispSpec_Code', type: 'string', hidden: true },
				{ name: 'SurveyType_Name', type: 'string', sortable: false, header: lang['osmotr_issledovanie'], id: 'autoexpand' },
				{ name: 'DopDispInfoConsent_IsEarlier', sortable: false, type: 'checkcolumnedit', isparams: true, header: lang['proydeno_ranee'], width: 180 },
				{ name: 'DopDispInfoConsent_IsAgree', sortable: false, type: 'checkcolumnedit', isparams: true, header: lang['soglasie_grajdanina'], width: 180 }
			],
			onLoadData: function() {
				this.checkIsAgree();
				//win.reloadUslugaComplexAllowed();
			},
			checkIsAgree: function() {
				// проверить согласие для первой строки..
				var record = win.dopDispInfoConsentGrid.getGrid().getStore().getAt(0);

				if (record) {
					win.dopDispInfoConsentGrid.getGrid().getStore().each(function(rec) {
						if ( !Ext.isEmpty(rec.get('SurveyType_Code')) ) {
							if (rec.get('SurveyType_Code') != 1) {
								rec.beginEdit();
								if (record.get('DopDispInfoConsent_IsAgree') != true) {
									rec.set('DopDispInfoConsent_IsAgree', 'hidden');
									rec.set('DopDispInfoConsent_IsEarlier', 'hidden');
								} else if (rec.get('DopDispInfoConsent_IsAgree') == 'hidden') {
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
				
				win.checkEvnPLDispOrp13IsSaved();
			},
			onAfterEdit: function(o) {
				if (o && o.field) {
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
					
					// при снятии чекбокса в поле первый этап диспансеризации снимать все остальные и делать недоступными
					if (o.record.get('SurveyType_Code') == 1) {
						this.checkIsAgree();
					}
				}
			}
		});
		
		this.DopDispInfoConsentPanel = new sw.Promed.Panel({
			items: [
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
								handler: function() {
									// если добавление то сохраним сначала всю карту, а потом уже к нему согласие..
									var base_form = win.findById('EvnPLDispOrp13EditForm').getForm();
									if (!Ext.isEmpty(base_form.findField('EvnPLDispOrp_id').getValue()) && base_form.findField('EvnPLDispOrp_id').getValue() > 0) {
										win.saveDopDispInfoConsent();
									} else {
										win.doSave(function(){
											win.saveDopDispInfoConsent({
												saveNotSaved: true
											});
										}, null, null, {
											ignoreRequiredFields: true
										});
									}
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
									var base_form = win.findById('EvnPLDispOrp13EditForm').getForm();
									var paramEvnPLOrpDisp = base_form.findField('EvnPLDispOrp_id').getValue();
									if (paramEvnPLOrpDisp) {
										var dialog_wnd = Ext.Msg.show({
											title: lang['vid_soglasovaniya'],
											msg: lang['vyiberite_tip_soglasiya'],
											buttons: {yes: "От имени пациента", no: "От имени законного представителя", cancel: "Отмена"},
											fn: function(button){
												if (button == 'cancel') {
													return;
												}
												if (button == 'yes') {	//От имени пациента
													printBirt({
														'Report_FileName': 'EvnPLOrpDispInfoConsent.rptdesign',
														'Report_Params': '&paramEvnPLOrpDisp=' + paramEvnPLOrpDisp,
														'Report_Format': 'pdf'
													});
												}
												if (button == 'no') {	//От имени законного представителя
													printBirt({
														'Report_FileName': 'EvnPLOrpDispInfoConsent_Deputy.rptdesign',
														'Report_Params': '&paramEvnPLOrpDisp=' + paramEvnPLOrpDisp,
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
			animCollapse: false,
			title: lang['informirovannoe_dobrovolnoe_soglasie']
		});
		
		this.EvnDiagAndRecomendationPanel = new sw.Promed.ViewFrame({
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
			id: 'EPLDO13EF_EvnDiagAndRecomendationGrid',
			dataUrl: C_EPLDO13_VIZITRECOMEND_LIST,
			region: 'center',
			height: 200,
			onLoadData: function() {
				this.setActionDisabled('action_edit', (!win.action.inlist(['add','edit'])));
			},
			title: lang['diagnozyi_i_rekomendatsii_po_rezultatam_dispanserizatsii_profosmotra'],
			toolbar: true,
			stringfields: [
				{ name: 'EvnVizitDispOrp_id', type: 'int', header: 'ID', key: true },
				{ name: 'Diag_id', type: 'int', hidden: true },
				{ name: 'FormDataJSON', type: 'string', hidden: true }, // данные формы "Состояние здоровья: Редактирование"
				{ name: 'OrpDispSpec_Name', type: 'string', header: lang['spetsialnost'], width: 300 },
				{ name: 'Diag_Name', type: 'string', header: lang['diagnoz'], id: 'autoexpand' }
			]
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
		
		this.EvnDiagDopDispAndRecomendationPanel = new sw.Promed.ViewFrame({
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
			id: 'EPLDO13EF_EvnDiagDopDispAndRecomendationGrid',
			dataUrl: '/?c=EvnDiagDopDisp&m=loadEvnDiagDopDispAndRecomendationGrid',
			region: 'center',
			height: 200,
			onLoadData: function() {
				this.setActionDisabled('action_edit', (!win.action.inlist(['add','edit'])));
				this.setActionDisabled('action_delete', (!win.action.inlist(['add','edit'])));
			},
			title: lang['sostoyanie_zdorovya_do_provedeniya_dispanserizatsii_profosmotra'],
			toolbar: true,
			stringfields: [
				{ name: 'EvnDiagDopDisp_id', type: 'int', header: 'ID', key: true },
				{ name: 'Diag_id', type: 'int', hidden: true },
				{ name: 'DeseaseDispType_Name', type: 'string', header: lang['ustanovlen_vpervyie'], width: 150 },
				{ name: 'DispSurveilType_Name', type: 'string', header: lang['dispansernoe_nablyudenie'], width: 150 },
				{ name: 'Diag_Name', type: 'string', header: lang['diagnoz'], id: 'autoexpand' }
			]
		});
		
		
		this.EvnPLDispOrpVizitPanel = new Ext.grid.GridPanel({
			animCollapse: false,
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 100,
			columns: [{
				dataIndex: 'EvnVizitDispOrp_setDate',
				header: lang['data_posescheniya'],
				hidden: false,
				renderer: Ext.util.Format.dateRenderer('d.m.Y'),
				resizable: false,
				sortable: true,
				width: 100
			}, {
				dataIndex: 'OrpDispSpec_Name',
				header: lang['spetsialnost'],
				hidden: false,
				resizable: true,
				sortable: true,
				width: 130
			}, {
				dataIndex: 'LpuSection_Name',
				header: lang['otdelenie'],
				hidden: false,
				resizable: true,
				sortable: true,
				width: 130
			}, {
				dataIndex: 'MedPersonal_Fio',
				header: lang['vrach'],
				hidden: false,
				id: 'autoexpand',
				resizable: true,
				sortable: true
			}, {
				dataIndex: 'Diag_Code',
				header: lang['diagnoz'],
				hidden: false,
				resizable: true,
				sortable: true,
				width: 130
			},
			{
				dataIndex: 'TumorStage_Name',
				header: 'Стадия выявленного ЗНО',
				hidden: true,
				resizable: true,
				sortable: true,
				width: 180
			}
			],
			collapsible: true,
			frame: false,
			height: 200,
			id: 'epldo13efEvnVizitDispOrpGrid',
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

					e.returnValue = false;

					if (Ext.isIE)
					{
						e.browserEvent.keyCode = 0;
						e.browserEvent.which = 0;
					}

					var grid = Ext.getCmp('epldo13efEvnVizitDispOrpGrid');

					switch (e.getKey())
					{
						case Ext.EventObject.DELETE:
							Ext.getCmp('EvnPLDispOrp13EditWindow').deleteEvnVizitDispOrp();
							break;

						case Ext.EventObject.END:
							if (!grid.getSelectionModel().getSelected())
							{
								return false;
							}

							if (grid.getStore().getCount() > 0)
							{
								grid.getView().focusRow(grid.getStore().getCount() - 1);
								grid.getSelectionModel().selectLastRow();
							}

							break;

						case Ext.EventObject.ENTER:
						case Ext.EventObject.F3:
						case Ext.EventObject.F4:
						case Ext.EventObject.INSERT:
							if (!grid.getSelectionModel().getSelected())
							{
								return false;
							}

							var action = 'add';

							if (e.getKey() == Ext.EventObject.F3)
							{
								action = 'view';
							}
							else if (e.getKey() == Ext.EventObject.F4)
							{
								action = 'edit';
							}
							else if (e.getKey() == Ext.EventObject.ENTER)
							{
								action = 'edit';
							}

							Ext.getCmp('EvnPLDispOrp13EditWindow').openEvnVizitDispOrpEditWindow(action);

							break;

						case Ext.EventObject.HOME:
							if (!grid.getSelectionModel().getSelected())
							{
								return false;
							}

							if (grid.getStore().getCount() > 0)
							{
								grid.getView().focusRow(0);
								grid.getSelectionModel().selectFirstRow();
							}
							break;

						case Ext.EventObject.TAB:
							if ( e.shiftKey )
							{
								Ext.getCmp('epldo13efIsFinishCombo').focus(true, 200);
							}
							else
							{
								var usluga_grid = Ext.getCmp('epldo13efEvnUslugaDispOrpGrid');
								if ( usluga_grid.getStore().getCount() > 0 )
								{
									usluga_grid.focus();
									usluga_grid.getSelectionModel().selectFirstRow();
									usluga_grid.getView().focusRow(0);
								}
								else
								{
									Ext.getCmp('epldo13efSaveButton').focus();
								}
							}
						break;
							
						case Ext.EventObject.PAGE_DOWN:
							var records_count = grid.getStore().getCount();

							if (records_count > 0 && grid.getSelectionModel().getSelected())
							{
								var selected_record = grid.getSelectionModel().getSelected();

								var index = grid.getStore().findBy(function(rec) { return rec.get('EvnVizitDispOrp_id') == selected_record.data.EvnVizitDispOrp_id; });

								if (index + 10 <= records_count - 1)
								{
									index = index + 10;
								}
								else
								{
									index = records_count - 1;
								}

								grid.getView().focusRow(index);
								grid.getSelectionModel().selectRow(index);
							}
							break;

						case Ext.EventObject.PAGE_UP:
							var records_count = grid.getStore().getCount();

							if (records_count > 0 && grid.getSelectionModel().getSelected())
							{
								var selected_record = grid.getSelectionModel().getSelected();

								var index = grid.getStore().findBy(function(rec) { return rec.get('EvnVizitDispOrp_id') == selected_record.data.EvnVizitDispOrp_id; });

								if (index - 10 >= 0)
								{
									index = index - 10;
								}
								else
								{
									index = 0;
								}

								grid.getView().focusRow(index);
								grid.getSelectionModel().selectRow(index);
							}
							break;
					}
				},
				stopEvent: true
			}],
			listeners: {
				'rowdblclick': function(grid, number, obj) {
					Ext.getCmp('EvnPLDispOrp13EditWindow').openEvnVizitDispOrpEditWindow('edit');
				}
			},
			loadMask: true,
			region: 'center',
			sm: new Ext.grid.RowSelectionModel({
				listeners: {
					'rowselect': function(sm, rowIndex, record) {
						var evn_vizitdispdop_id = sm.getSelected().data.EvnVizitDispOrp_id;
						var record_status = sm.getSelected().data.Record_Status;
						var toolbar = this.grid.getTopToolbar();
						toolbar.items.items[1].disable();
						toolbar.items.items[2].disable();
						toolbar.items.items[3].disable();
						if (evn_vizitdispdop_id) {
							toolbar.items.items[2].enable();
							if (win.action != 'view') {
								toolbar.items.items[1].enable();
								toolbar.items.items[3].enable();
							}
						}
					}
				}
			}),
			stripeRows: true,
			store: new Ext.data.Store({
				autoLoad: false,
				listeners: {
					'load': function(store, records, options) {
						store.filterBy(function(record){
							if (record.data.Record_Status != 3 && record.data.Record_Status != 2)
							{
								return true;
							}
						});
						win.checkIfSetDateShouldBeDisabled();
					}
				},
				reader: new Ext.data.JsonReader({
					id: 'EvnVizitDispOrp_id'
				}, [{
					mapping: 'EvnVizitDispOrp_id',
					name: 'EvnVizitDispOrp_id',
					type: 'int'
				}, {
					mapping: 'EvnDiagDopDispGridData',
					name: 'EvnDiagDopDispGridData'
				}, {
					mapping: 'Server_id',
					name: 'Server_id',
					type: 'int'
				}, {
					mapping: 'PersonEvn_id',
					name: 'PersonEvn_id',
					type: 'int'
				}, {
					mapping: 'LpuSection_id',
					name: 'LpuSection_id',
					type: 'int'
				}, {
					mapping: 'Lpu_uid',
					name: 'Lpu_uid',
					type: 'int'
				}, {
					mapping: 'MedSpecOms_id',
					name: 'MedSpecOms_id',
					type: 'int'
				}, {
					mapping: 'LpuSectionProfile_id',
					name: 'LpuSectionProfile_id',
					type: 'int'
				}, {
					mapping: 'OrpDispSpec_id',
					name: 'OrpDispSpec_id',
					type: 'int'
				}, {
					mapping: 'UslugaComplex_id',
					name: 'UslugaComplex_id',
					type: 'int'
				}, {
					mapping: 'DopDispInfoConsent_id',
					name: 'DopDispInfoConsent_id',
					type: 'int'
				}, {
					mapping: 'OrpDispSpec_Code',
					name: 'OrpDispSpec_Code',
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
					dateFormat: 'd.m.Y',
					mapping: 'EvnVizitDispOrp_setDate',
					name: 'EvnVizitDispOrp_setDate',
					type: 'date'
				}, {
					mapping: 'EvnVizitDispOrp_setTime',
					name: 'EvnVizitDispOrp_setTime',
					type: 'string'
				}, {
					dateFormat: 'd.m.Y',
					mapping: 'EvnVizitDispOrp_disDate',
					name: 'EvnVizitDispOrp_disDate',
					type: 'date'
				}, {
					mapping: 'EvnVizitDispOrp_disTime',
					name: 'EvnVizitDispOrp_disTime',
					type: 'string'
				}, {
					mapping: 'Diag_id',
					name: 'Diag_id',
					type: 'int'
				}, {
					mapping: 'TumorStage_id',
					name: 'TumorStage_id',
					type: 'int'
				}, {
					mapping: 'LpuSection_Name',
					name: 'LpuSection_Name',
					type: 'string'
				}, {
					mapping: 'OrpDispSpec_Name',
					name: 'OrpDispSpec_Name',
					type: 'string'
				}, {
					mapping: 'MedPersonal_Fio',
					name: 'MedPersonal_Fio',
					type: 'string'
				}, {
					mapping: 'Diag_Code',
					name: 'Diag_Code',
					type: 'string'
				}, {
					mapping: 'TumorStage_Name',
					name: 'TumorStage_Name',
					type: 'string'
				}, {
					mapping: 'DopDispDiagType_id',
					name: 'DopDispDiagType_id',
					type: 'int'
				}, {
					mapping: 'DopDispAlien_id',
					name: 'DopDispAlien_id',
					type: 'int'
				}, {
					mapping: 'Record_Status',
					name: 'Record_Status',
					type: 'int'
				}]),
				url: C_EPLDO13_VIZIT_LIST
			}),
			tbar: new sw.Promed.Toolbar({
				buttons: [{
					handler: function() {
						Ext.getCmp('EvnPLDispOrp13EditWindow').openEvnVizitDispOrpEditWindow('add');
					},
					iconCls: 'add16',
					text: BTN_GRIDADD,
					tooltip: BTN_GRIDADD_TIP
				}, {
					handler: function() {
						Ext.getCmp('EvnPLDispOrp13EditWindow').openEvnVizitDispOrpEditWindow('edit');
					},
					iconCls: 'edit16',
					text: BTN_GRIDEDIT,
					tooltip: BTN_GRIDEDIT_TIP
				}, {
					handler: function() {
						Ext.getCmp('EvnPLDispOrp13EditWindow').openEvnVizitDispOrpEditWindow('view');
					},
					iconCls: 'view16',
					text: BTN_GRIDVIEW,
					tooltip: BTN_GRIDVIEW_TIP
				}, {
					handler: function() {
						Ext.getCmp('EvnPLDispOrp13EditWindow').deleteEvnVizitDispOrp();
					},
					iconCls: 'delete16',
					text: BTN_GRIDDEL,
					tooltip: BTN_GRIDDEL_TIP
				}]
			}),
			title: lang['osmotr_vracha-spetsialista']
		});
		
		this.EvnUslugaDispOrpPanel = new Ext.grid.GridPanel({
			animCollapse: false,
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 100,
			columns: [{
				dataIndex: 'EvnUslugaDispOrp_setDate',
				header: lang['issledovan'],
				hidden: false,
				renderer: Ext.util.Format.dateRenderer('d.m.Y'),
				resizable: false,
				sortable: true,
				width: 100
			}, {
				dataIndex: 'EvnUslugaDispOrp_didDate',
				header: lang['rezultat'],
				hidden: false,
				renderer: Ext.util.Format.dateRenderer('d.m.Y'),
				resizable: false,
				sortable: true,
				width: 100
			}, {
				dataIndex: 'LpuSection_Name',
				header: lang['otdelenie'],
				hidden: false,
				resizable: true,
				sortable: true,
				width: 130
			}, {
				dataIndex: 'MedPersonal_Fio',
				header: lang['vrach'],
				hidden: false,
				id: 'autoexpand',
				resizable: true,
				sortable: true
			}, {
				dataIndex: 'UslugaComplex_Code',
				header: lang['kod'],
				hidden: false,
				resizable: true,
				sortable: true,
				width: 130
			}, {
				dataIndex: 'UslugaComplex_Name',
				header: lang['naimenovanie'],
				hidden: false,
				resizable: true,
				sortable: true,
				width: 130
			}],
			frame: false,
			height: 200,
			id: 'epldo13efEvnUslugaDispOrpGrid',
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

					e.returnValue = false;

					if (Ext.isIE)
					{
						e.browserEvent.keyCode = 0;
						e.browserEvent.which = 0;
					}

					var grid = Ext.getCmp('epldo13efEvnUslugaDispOrpGrid');

					switch (e.getKey())
					{
						case Ext.EventObject.DELETE:
							Ext.getCmp('EvnPLDispOrp13EditWindow').deleteEvnUslugaDispOrp();
							break;

						case Ext.EventObject.END:
							if (!grid.getSelectionModel().getSelected())
							{
								return false;
							}

							if (grid.getStore().getCount() > 0)
							{
								grid.getView().focusRow(grid.getStore().getCount() - 1);
								grid.getSelectionModel().selectLastRow();
							}

							break;
							
						case Ext.EventObject.TAB:
							if ( e.shiftKey )
							{
								var vizit_grid = Ext.getCmp('epldo13efEvnVizitDispOrpGrid');
								if ( vizit_grid.getStore().getCount() > 0 )
								{
									vizit_grid.focus();
									vizit_grid.getSelectionModel().selectFirstRow();
									vizit_grid.getView().focusRow(0);
								}
								else
								{
									Ext.getCmp('epldo13efIsFinishCombo').focus(true, 200);
								}
							}
							else
							{												
								Ext.getCmp('epldo13efSaveButton').focus();
							}
						break;

						case Ext.EventObject.ENTER:
						case Ext.EventObject.F3:
						case Ext.EventObject.F4:
						case Ext.EventObject.INSERT:
							if (!grid.getSelectionModel().getSelected())
							{
								return false;
							}

							var action = 'add';

							if (e.getKey() == Ext.EventObject.F3)
							{
								action = 'view';
							}
							else if ((e.getKey() == Ext.EventObject.F4) || (e.getKey() == Ext.EventObject.ENTER))
							{
								action = 'edit';
							}

							Ext.getCmp('EvnPLDispOrp13EditWindow').openEvnUslugaDispOrpEditWindow(action);

							break;

						case Ext.EventObject.HOME:
							if (!grid.getSelectionModel().getSelected())
							{
								return false;
							}

							if (grid.getStore().getCount() > 0)
							{
								grid.getView().focusRow(0);
								grid.getSelectionModel().selectFirstRow();
							}
							break;

						case Ext.EventObject.PAGE_DOWN:
							var records_count = grid.getStore().getCount();

							if (records_count > 0 && grid.getSelectionModel().getSelected())
							{
								var selected_record = grid.getSelectionModel().getSelected();

								var index = grid.getStore().findBy(function(rec) { return rec.get('EvnUslugaDispOrp_id') == selected_record.data.EvnUslugaDispOrp_id; });

								if (index + 10 <= records_count - 1)
								{
									index = index + 10;
								}
								else
								{
									index = records_count - 1;
								}

								grid.getView().focusRow(index);
								grid.getSelectionModel().selectRow(index);
							}
							break;

						case Ext.EventObject.PAGE_UP:
							var records_count = grid.getStore().getCount();

							if (records_count > 0 && grid.getSelectionModel().getSelected())
							{
								var selected_record = grid.getSelectionModel().getSelected();

								var index = grid.getStore().findBy(function(rec) { return rec.get('EvnUslugaDispOrp_id') == selected_record.data.EvnUslugaDispOrp_id; });

								if (index - 10 >= 0)
								{
									index = index - 10;
								}
								else
								{
									index = 0;
								}

								grid.getView().focusRow(index);
								grid.getSelectionModel().selectRow(index);
							}
							break;
					}
				},
				stopEvent: true
			}],
			listeners: {
				'rowdblclick': function(grid, number, obj) {
					Ext.getCmp('EvnPLDispOrp13EditWindow').openEvnUslugaDispOrpEditWindow('edit');
				}
			},
			loadMask: true,
			region: 'south',
			sm: new Ext.grid.RowSelectionModel({
				listeners: {
					'rowselect': function(sm, rowIndex, record) {
						var evn_uslugadispdop_id = sm.getSelected().data.EvnUslugaDispOrp_id;
						var record_status = sm.getSelected().data.Record_Status;
						var toolbar = this.grid.getTopToolbar();
						toolbar.items.items[1].disable();
						toolbar.items.items[2].disable();
						toolbar.items.items[3].disable();
						if (evn_uslugadispdop_id) {
							toolbar.items.items[2].enable();
							if (win.action != 'view') {
								toolbar.items.items[1].enable();
								toolbar.items.items[3].enable();
							}
						}
					}
				}
			}),
			stripeRows: true,
			store: new Ext.data.Store({
				autoLoad: false,
				listeners: {
					'load': function(store, records, options) {
						store.filterBy(function(record){
							if (record.data.Record_Status != 3 && record.data.Record_Status != 2)
							{
								return true;
							}
						});
						win.checkIfSetDateShouldBeDisabled();
					}
				},
				reader: new Ext.data.JsonReader({
					id: 'EvnUslugaDispOrp_id'
				}, [{
					mapping: 'EvnUslugaDispOrp_id',
					name: 'EvnUslugaDispOrp_id',
					type: 'int'
				}, {
					dateFormat: 'd.m.Y',
					mapping: 'EvnUslugaDispOrp_setDate',
					name: 'EvnUslugaDispOrp_setDate',
					type: 'date'
				}, {
					mapping: 'EvnUslugaDispOrp_setTime',
					name: 'EvnUslugaDispOrp_setTime',
					type: 'string'
				}, {
					dateFormat: 'd.m.Y',
					mapping: 'EvnUslugaDispOrp_disDate',
					name: 'EvnUslugaDispOrp_disDate',
					type: 'date'
				}, {
					mapping: 'EvnUslugaDispOrp_disTime',
					name: 'EvnUslugaDispOrp_disTime',
					type: 'string'
				}, {
					dateFormat: 'd.m.Y',
					mapping: 'EvnUslugaDispOrp_didDate',
					name: 'EvnUslugaDispOrp_didDate',
					type: 'date'
				}, {
					mapping: 'ExaminationPlace_id',
					name: 'ExaminationPlace_id',
					type: 'int'
				}, {
					mapping: 'LpuSection_id',
					name: 'LpuSection_id',
					type: 'int'
				}, {
					mapping: 'Lpu_uid',
					name: 'Lpu_uid',
					type: 'int'
				}, {
					mapping: 'MedSpecOms_id',
					name: 'MedSpecOms_id',
					type: 'int'
				}, {
					mapping: 'LpuSectionProfile_id',
					name: 'LpuSectionProfile_id',
					type: 'int'
				}, {
					mapping: 'LpuSection_Name',
					name: 'LpuSection_Name',
					type: 'string'
				}, {
					mapping: 'MedPersonal_id',
					name: 'MedPersonal_id',
					type: 'int'
				}, {
					mapping: 'MedStaffFact_id',
					name: 'MedStaffFact_id',
					type: 'int'
				}, {
					mapping: 'MedPersonal_Fio',
					name: 'MedPersonal_Fio',
					type: 'string'
				}, {
					mapping: 'UslugaComplex_id',
					name: 'UslugaComplex_id',
					type: 'int'
				}, {
					mapping: 'UslugaComplex_Code',
					name: 'UslugaComplex_Code',
					type: 'string'
				}, {
					mapping: 'UslugaComplex_Name',
					name: 'UslugaComplex_Name',
					type: 'string'
				}, {
					mapping: 'EvnUslugaDispOrp_Result',
					name: 'EvnUslugaDispOrp_Result',
					type: 'string'
				}, {
					mapping: 'Record_Status',
					name: 'Record_Status',
					type: 'int'
				}]),
				url: C_EPLDO13_USLUGA_LIST
			}),
			tbar: new sw.Promed.Toolbar({
				buttons: [{
					handler: function() {
						Ext.getCmp('EvnPLDispOrp13EditWindow').openEvnUslugaDispOrpEditWindow('add');
					},
					iconCls: 'add16',
					text: BTN_GRIDADD,
					tooltip: BTN_GRIDADD_TIP
				}, {
					handler: function() {
						Ext.getCmp('EvnPLDispOrp13EditWindow').openEvnUslugaDispOrpEditWindow('edit');
					},
					iconCls: 'edit16',
					text: BTN_GRIDEDIT,
					tooltip: BTN_GRIDEDIT_TIP
				}, {
					handler: function() {
						Ext.getCmp('EvnPLDispOrp13EditWindow').openEvnUslugaDispOrpEditWindow('view');
					},
					iconCls: 'view16',
					text: BTN_GRIDVIEW,
					tooltip: BTN_GRIDVIEW_TIP
				}, {
					handler: function() {
						Ext.getCmp('EvnPLDispOrp13EditWindow').deleteEvnUslugaDispOrp();
					},
					iconCls: 'delete16',
					text: BTN_GRIDDEL,
					tooltip: BTN_GRIDDEL_TIP
				}]
			}),
			title: lang['obsledovaniya']
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
				{ name: 'RecordStatus_Code', type: 'int', hidden: true },
				{ name: 'DispAppointType_id', type: 'int', hidden: true },
				{ name: 'MedSpecOms_id', type: 'int', hidden: true },
				{ name: 'ExaminationType_id', type: 'int', hidden: true },
				{ name: 'LpuSectionProfile_id', type: 'int', hidden: true },
				{ name: 'LpuSectionBedProfile_id', type: 'int', hidden: true },
				{ name: 'LpuSectionBedProfile_fid', type: 'int', hidden: true },
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
		
		this.PersonInfoPanel = new sw.Promed.PersonInformationPanel({
			additionalFields: [
				'UAddress_id',
				'PAddress_id',
				'DocumentType_id'
			],
			button2Callback: function(callback_data) {
				var current_window = Ext.getCmp('EvnPLDispOrp13EditWindow');

				current_window.findById('epldo13efPersonEvn_id').setValue(callback_data.PersonEvn_id);
				current_window.findById('epldo13efServer_id').setValue(callback_data.Server_id);
				
				current_window.PersonInfoPanel.load( { Person_id: callback_data.Person_id, Server_id: callback_data.Server_id } );
			},
			region: 'north'
		});
		
		Ext.apply(this, {
			items: [
				new Ext.Panel ({
					autoScroll: true,
					border: false,
					layout: 'form',
					region: 'north',
					height: (!Ext.isIE) ? 230 : 250,
					items: [
						win.PersonInfoPanel,
						new Ext.form.FormPanel({
							bodyBorder: false,
							border: false,
							buttonAlign: 'left',
							frame: false,
							autoHeight: true,
							id: 'EvnPLDispOrp13EditForm',
							labelAlign: 'right',
							labelWidth: 200,
							items: [{
									id: 'epldo13efEvnPLDispOrp_id',
									name: 'EvnPLDispOrp_id',
									value: 0,
									xtype: 'hidden'
								}, {
									name:'EvnPLDispOrp_IsPaid',
									xtype:'hidden'
								}, {
									name:'EvnPLDispOrp_IndexRep',
									xtype:'hidden'
								}, {
									name:'EvnPLDispOrp_IndexRepInReg',
									xtype:'hidden'
								}, {
									name: 'accessType',
									xtype: 'hidden'
								}, {
									name: 'EvnPLDispOrp_fid',
									xtype: 'hidden'
								}, {
									id: 'epldo13efPerson_id',
									name: 'Person_id',
									value: 0,
									xtype: 'hidden'
								}, {
									id: 'epldo13efPersonEvn_id',
									name: 'PersonEvn_id',
									value: 0,
									xtype: 'hidden'
								}, {
									name: 'DispClass_id',
									value: 0,
									xtype: 'hidden'
								}, {
									id: 'epldo13efServer_id',
									name: 'Server_id',
									value: 0,
									xtype: 'hidden'
								}, {
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

											var base_form = win.findById('EvnPLDispOrp13EditForm').getForm();
	
											var
												EvnPLDispOrp_IndexRep = parseInt(base_form.findField('EvnPLDispOrp_IndexRep').getValue()),
												EvnPLDispOrp_IndexRepInReg = parseInt(base_form.findField('EvnPLDispOrp_IndexRepInReg').getValue()),
												EvnPLDispOrp_IsPaid = parseInt(base_form.findField('EvnPLDispOrp_IsPaid').getValue());
	
											var diff = EvnPLDispOrp_IndexRepInReg - EvnPLDispOrp_IndexRep;
	
											if ( EvnPLDispOrp_IsPaid != 2 || EvnPLDispOrp_IndexRepInReg == 0 ) {
												return false;
											}
	
											if ( value == true ) {
												if ( diff == 1 || diff == 2 ) {
													EvnPLDispOrp_IndexRep = EvnPLDispOrp_IndexRep + 2;
												}
												else if ( diff == 3 ) {
													EvnPLDispOrp_IndexRep = EvnPLDispOrp_IndexRep + 4;
												}
											}
											else if ( value == false ) {
												if ( diff <= 0 ) {
													EvnPLDispOrp_IndexRep = EvnPLDispOrp_IndexRep - 2;
												}
											}
	
											base_form.findField('EvnPLDispOrp_IndexRep').setValue(EvnPLDispOrp_IndexRep);
										}
									},
									name: 'EvnPLDispOrp_RepFlag',
									xtype: 'checkbox'
								}, {
									allowBlank: false,
									typeCode: 'int',
									useCommonFilter: true,
									width: 300,
									xtype: 'swpaytypecombo'
								}, {
									allowBlank: false,
									comboSubject: 'ChildStatusType',
									fieldLabel: lang['status_rebenka'],
									hiddenName: 'ChildStatusType_id',
									onLoadStore: function() {
										win.filtersByDispClass();
									},
									lastQuery: '',
									width: 300,
									xtype: 'swcommonsprcombo'
								}, {
									name: 'EvnPLDispOrp_setDate',
									allowBlank: false,
									fieldLabel: lang['data_nachala_dispanserizatsii'],
									format: 'd.m.Y',
									id: 'epldo13efEvnPLDispOrp_setDate',
									listeners: {
										'change': function(field, newValue, oldValue) {
											win.loadUslugaComplex();

											if (getRegionNick() == 'perm' && !Ext.isEmpty(oldValue) && !Ext.isEmpty(newValue) && win.checkEvnPLDispOrp13IsSaved() && newValue.format('Y') != oldValue.format('Y')) {
												sw.swMsg.show({
													buttons: Ext.Msg.YESNO,
													fn: function ( buttonId ) {
														if ( buttonId == 'yes' ) {
															win.saveDopDispInfoConsentAfterLoad = true;
															win.blockSaveDopDispInfoConsent = true;
															var age = -1;
															var base_form = win.findById('EvnPLDispOrp13EditForm').getForm();

															if ( !Ext.isEmpty(newValue) ) {
																age = swGetPersonAge(win.PersonInfoPanel.getFieldValue('Person_Birthday'), newValue);
															}

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

															win.reloadUslugaComplexAllowed();
														} else {
															win.findById('epldo13efEvnPLDispOrp_setDate').setValue(oldValue);
														}
													},
													msg: lang['pri_izmenenii_datyi_nachala_dispanserizatsii_vvedennaya_informatsiya_po_osmotram_issledovaniyam_budet_udalena_izmenit'],
													title: lang['podtverjdenie']
												});
												return false;
											}

											var age = -1;
											var base_form = win.findById('EvnPLDispOrp13EditForm').getForm();

											if ( !Ext.isEmpty(newValue) ) {
												age = swGetPersonAge(win.PersonInfoPanel.getFieldValue('Person_Birthday'), newValue);
											}

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
											
											win.reloadUslugaComplexAllowed();
										}
									},
									plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
									xtype: 'swdatefield'
								}, {
									allowBlank: false,
									codeField: 'YesNo_Code',
									displayField: 'YesNo_Name',
									editable: false,
									value: 1,
									enableKeyEvents: true,
									fieldLabel: lang['sluchay_zakonchen'],
									hiddenName: 'EvnPLDispOrp_IsFinish',
									id: 'epldo13efIsFinishCombo',
									lastQuery: '',
									listeners: {
										'keydown': function(inp, e) {
											if ( !e.shiftKey && e.getKey() == Ext.EventObject.TAB )
											{
												e.stopEvent();
												var usluga_grid = Ext.getCmp('epldo13efEvnUslugaDispOrpGrid');
												var vizit_grid = Ext.getCmp('epldo13efEvnVizitDispOrpGrid');
												if ( vizit_grid.getStore().getCount() > 0 )
												{
													vizit_grid.focus();
													vizit_grid.getSelectionModel().selectFirstRow();
													vizit_grid.getView().focusRow(0);
													return true;
												}
												if ( usluga_grid.getStore().getCount() > 0 )
												{
													usluga_grid.focus();
													usluga_grid.getSelectionModel().selectFirstRow();
													usluga_grid.getView().focusRow(0);
													return true;
												}
												Ext.getCmp('epldo13efSaveButton').focus();
											}
										},
										'change': function() {
											win.checkForCostPrintPanel();
										}
									},
									listWidth: 150,
									store: new Ext.db.AdapterStore({
										autoLoad: true,
										dbFile: 'Promed.db',
										fields: [
											{ name: 'YesNo_id', type: 'int' },
											{ name: 'YesNo_Code', type: 'int' },
											{ name: 'YesNo_Name', type: 'string' }
										],
										key: 'YesNo_id',
										sortInfo: { field: 'YesNo_Code' },
										tableName: 'YesNo'
									}),
									tabIndex: 2405,
									tpl: new Ext.XTemplate(
										'<tpl for="."><div class="x-combo-list-item">',
										'<font color="red">{YesNo_Code}</font>&nbsp;{YesNo_Name}',
										'</div></tpl>'
									),
									valueField: 'YesNo_id',
									width: 80,
									xtype: 'swbaselocalcombo'
								}, {
									allowBlank: false,
									fieldLabel: lang['data_podpisaniya_soglasiya_otkaza'],
									format: 'd.m.Y',
									id: 'epldo13efEvnPLDispOrp_consDate',
									listeners: {
										'change': function(field, newValue, oldValue) {
											// win.reloadUslugaComplexAllowed(); // дата подписания не влияет на список согласия.
										}
									},
									name: 'EvnPLDispOrp_consDate',
									plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
									width: 100,
									xtype: 'swdatefield'
								}, {
									fieldLabel: lang['sluchay_obslujen_mobilnoy_brigadoy'],
									name: 'EvnPLDispOrp_IsMobile',
									xtype: 'checkbox',
									listeners: {
										'check': function(checkbox, value) {
											var base_form = win.findById('EvnPLDispOrp13EditForm').getForm();
											
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
										
										var base_form = win.findById('EvnPLDispOrp13EditForm').getForm();
										
										getWnd('swOrgSearchWindow').show({
											enableOrgType: false,
											onlyFromDictionary: true,
											object: 'lpu',
											DispClass_id: base_form.findField('DispClass_id').getValue(),
											Disp_consDate: (typeof win.findById('epldo13efEvnPLDispOrp_consDate').getValue() == 'object' ? Ext.util.Format.date(win.findById('epldo13efEvnPLDispOrp_consDate').getValue(), 'd.m.Y') : win.findById('epldo13efEvnPLDispOrp_consDate').getValue()),
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
									name: 'EvnPLDispOrp_IsOutLpu',
									xtype: 'checkbox'
								},
								// Добровольное информированное согласие
								win.DopDispInfoConsentPanel,
								// Осмотры
								win.EvnPLDispOrpVizitPanel,
								// Обследования
								win.EvnUslugaDispOrpPanel,
								// Диагнозы и рекомендации по результатам диспансеризации / профосмотра
								win.EvnDiagAndRecomendationPanel,
								// Состояние здоровья до проведения диспансеризации / профосмотра
								win.EvnDiagDopDispAndRecomendationPanel,
								// Общая оценка здоровья
								{
									title: lang['obschaya_otsenka_zdorovya'],
									id: 'EPLDOEW_CommonHealthCheck',
									animCollapse: false,
									layout: 'form',
									border: false,
									xtype: 'panel',
									bodyStyle: 'padding: 5px;',
									items: [
										// группбокс
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
													fieldLabel: lang['okrujnost_golovyi'],
													minValue: 6,
													maxValue: 99,
													name: 'AssessmentHealth_Head',
													xtype: 'numberfield'
												},
												{
													fieldLabel: lang['otklonenie_massa'],
													listeners: {
														'change': function(combo, newValue, oldValue) {
															var base_form = win.findById('EvnPLDispOrp13EditForm').getForm();
															if (newValue == 2) {
																base_form.findField('WeightAbnormType_id').enable();
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
															var base_form = win.findById('EvnPLDispOrp13EditForm').getForm();
															if (newValue == 2) {
																base_form.findField('HeightAbnormType_id').enable();
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
													id: 'epldo13ef_menarhe',
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
													id: 'epldo13ef_menses',
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
											autoHeight: true,
											style: 'padding: 0px;',
											title: lang['invalidnost'],
											width: 600,
											items: [
												{
													comboSubject: 'InvalidType',
													fieldLabel: lang['invalidnost'],
													hiddenName: 'InvalidType_id',
													lastQuery: '',
                                                    listeners: {
                                                        'change': function(combo,value){
                                                            var base_form = win.findById('EvnPLDispOrp13EditForm').getForm();
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
													fieldLabel: lang['data_ustanovleniya'],
													name: 'AssessmentHealth_setDT',
													xtype: 'swdatefield'
												},
												{
													fieldLabel: lang['data_poslednego_osvidetelstvovaniya'],
													name: 'AssessmentHealth_reExamDT',
													xtype: 'swdatefield'
												},
												{
													comboSubject: 'InvalidDiagType',
													fieldLabel: lang['zabolevaniya_obuslovivshie_vozniknovenie_invalidnosti'],
													hiddenName: 'InvalidDiagType_id',
													lastQuery: '',
													width: 300,
													xtype: 'swcommonsprcombo'
												},
												{
													autoHeight: true,
													style: 'padding: 0px;',
													title: lang['vidyi_narusheniy'],
													width: 580,
													items: [
														{
															boxLabel: lang['umstvennyie'],
															hideLabel: true,
															name: 'AssessmentHealth_IsMental',
															xtype: 'checkbox'
														},
														{
															boxLabel: lang['drugie_psihologicheskie'],
															hideLabel: true,
															name: 'AssessmentHealth_IsOtherPsych',
															xtype: 'checkbox'
														},
														{
															boxLabel: lang['yazyikovyie_i_rechevyie'],
															hideLabel: true,
															name: 'AssessmentHealth_IsLanguage',
															xtype: 'checkbox'
														},
														{
															boxLabel: lang['sluhovyie_i_vestibulyarnyie'],
															hideLabel: true,
															name: 'AssessmentHealth_IsVestibular',
															xtype: 'checkbox'
														},
														{
															boxLabel: lang['zritelnyie'],
															hideLabel: true,
															name: 'AssessmentHealth_IsVisual',
															xtype: 'checkbox'
														},
														{
															boxLabel: lang['vistseralnyie_i_metabolicheskie_rasstroystva_pitaniya'],
															hideLabel: true,
															name: 'AssessmentHealth_IsMeals',
															xtype: 'checkbox'
														},
														{
															boxLabel: lang['dvigatelnyie'],
															hideLabel: true,
															name: 'AssessmentHealth_IsMotor',
															xtype: 'checkbox'
														},
														{
															boxLabel: lang['uroduyuschie'],
															hideLabel: true,
															name: 'AssessmentHealth_IsDeform',
															xtype: 'checkbox'
														},
														{
															boxLabel: lang['obschie_i_generalizovannyie'],
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
													title: lang['individualnaya_programma_reabilitatsii_rebenka_invalida'],
													width: 580,
													items: [
														{
															fieldLabel: lang['data_naznacheniya'],
															name: 'AssessmentHealth_ReabDT',
															xtype: 'swdatefield'
														},
														{
															comboSubject: 'RehabilitEndType',
															fieldLabel: lang['vyipolnenie_na_moment_dispanserizatsii'],
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
											fieldLabel: lang['provedenie_profilakticheskih_privivok'],
											hiddenName: 'ProfVaccinType_id',
											lastQuery: '',
											width: 300,
											xtype: 'swcommonsprcombo',
											listeners:{
												change: function(combo, newValue) {
													var vaccinFieldset = win.findById('EPLDO13EW_VaccinFieldset');
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
											fieldLabel: lang['tip_privivki'],
											hiddenName: 'VaccinType_id',
											lastQuery: '',
											width: 300,
											xtype: 'swcommonsprcombo'
										},
										{
											autoHeight: true,
											style: 'padding: 0px;',
											title: lang['privivki'],
											id: 'EPLDO13EW_VaccinFieldset',
											width: 600,
											items: [],
											bodyStyle: 'padding: 5px;',
											xtype: 'fieldset'
										},
										{
											fieldLabel:'Рекомендации по формированию здорового образа жизни',
											name:'AssessmentHealth_HealthRecom',
											width: 300,
											xtype:'textfield'
										},
										{
											fieldLabel: 'Подозрение на ЗНО',
											hiddenName: 'EvnPLDispOrp_IsSuspectZNO',
											id: 'epldo13ef_EvnPLDispOrp_IsSuspectZNO',
											width: 100,
											xtype: 'swyesnocombo',
											listeners:{
												'change':function (combo, newValue, oldValue) {
													var base_form = win.findById('EvnPLDispOrp13EditForm').getForm();
													
													var index = combo.getStore().findBy(function (rec) {
														return (rec.get(combo.valueField) == newValue);
													});
													combo.fireEvent('select', combo, combo.getStore().getAt(index), index);

													if (base_form.findField('EvnPLDispOrp_IsSuspectZNO').getValue() == 2) {
														Ext.getCmp('EPLDO13EF_PrintKLU').enable();
														Ext.getCmp('EPLDO13EF_PrintOnko').enable();
													} else {
														Ext.getCmp('EPLDO13EF_PrintKLU').disable();
														Ext.getCmp('EPLDO13EF_PrintOnko').disable();
													}
												},
												'select':function (combo, record, idx) {
													if (record.get('YesNo_id') == 2) {
														Ext.getCmp('epldo13ef_Diag_spid').showContainer();
														Ext.getCmp('epldo13ef_Diag_spid').setAllowBlank(!getRegionNick().inlist([ 'astra', 'perm' ]));
													} else {
														Ext.getCmp('epldo13ef_Diag_spid').setValue('');
														Ext.getCmp('epldo13ef_Diag_spid').hideContainer();
														Ext.getCmp('epldo13ef_Diag_spid').setAllowBlank(true);
													}
												}
											}
										}, 
										{
											fieldLabel: 'Подозрение на диагноз',
											hiddenName: 'Diag_spid',
											id: 'epldo13ef_Diag_spid',
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
											fieldLabel: langs('Группа здоровья'),
											hiddenName: 'HealthKind_id',
											listeners: {
												'change': function(combo, newValue, oldValue) {
													win.checkEvnPLDispOrp13IsSaved();
												}
											},
											loadParams: {params: {where: ' where HealthKind_Code <= 5'}},
											xtype: 'swhealthkindcombo'
										},
										{
											fieldLabel: lang['napravlen_na_2_etap_dispanserizatsii'],
											hiddenName: 'EvnPLDispOrp_IsTwoStage',
											xtype: 'swyesnocombo'
										}
									]
								},
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
												this.doSave(null, false);
											}
											break;

										case Ext.EventObject.G:
											this.printEvnPLDispOrp();
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
								{ name: 'EvnPLDispOrp_id' },
								{ name: 'EvnPLDispOrp_IsPaid' },
								{ name: 'EvnPLDispOrp_IndexRep' },
								{ name: 'EvnPLDispOrp_IndexRepInReg' },
								{ name: 'accessType' },
								{ name: 'EvnPLDispOrp_fid' },
								{ name: 'EvnPLDispOrp_IsBud' },
								{ name: 'EvnPLDispOrp_IsFinish' },
								{ name: 'UslugaComplex_id' },
								{ name: 'ChildStatusType_id' },
								{ name: 'PersonEvn_id' },
								{ name: 'Server_id' },
								{ name: 'DispClass_id' },
								{ name: 'PayType_id' },
								{ name: 'Lpu_mid' },
								{ name: 'EvnPLDispOrp_IsMobile' },
								{ name: 'EvnPLDispOrp_IsOutLpu' },
								{ name: 'EvnPLDispOrp_setDate' },
								{ name: 'EvnPLDispOrp_consDate' },
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
								{ name: 'AssessmentHealth_HealthRecom' },
								{ name: 'RehabilitEndType_id' },
								{ name: 'ProfVaccinType_id' },
								{ name: 'HealthKind_id' },
								{ name: 'EvnPLDispOrp_IsTwoStage' },
								{ name: 'NormaDisturbanceType_id' },
								{ name: 'NormaDisturbanceType_eid' },
								{ name: 'NormaDisturbanceType_uid' },
								{ name: 'EvnCostPrint_setDT' },
								{ name: 'EvnCostPrint_Number' },
								{ name: 'EvnCostPrint_IsNoPrint' },
								{ name: 'EvnPLDispOrp_IsSuspectZNO' },
								{ name: 'Diag_spid' }
							]),
							region: 'center',
							url: C_EPLDO13_SAVE
						})
					]
				})
			],
			buttons: [{
				handler: function() {
					this.doSave(null, false);
				}.createDelegate(this),				
				iconCls: 'save16',
				id: 'epldo13efSaveButton',
				onTabAction: function() {
					Ext.getCmp('epldo13efPrintButton').focus(true, 200);
				},
				onShiftTabAction: function() {
					var usluga_grid = Ext.getCmp('epldo13efEvnUslugaDispOrpGrid');
					var vizit_grid = Ext.getCmp('epldo13efEvnVizitDispOrpGrid');
					if ( usluga_grid.getStore().getCount() > 0 )
					{
						usluga_grid.focus();
						usluga_grid.getSelectionModel().selectFirstRow();
						usluga_grid.getView().focusRow(0);
						return true;
					}
					if ( vizit_grid.getStore().getCount() > 0 )
					{
						vizit_grid.focus();
						vizit_grid.getSelectionModel().selectFirstRow();
						vizit_grid.getView().focusRow(0);
						return true;
					}					
					Ext.getCmp('epldo13efIsFinishCombo').focus(true, 200);
				},
				tabIndex: 2406,
				text: BTN_FRMSAVE
			}, {
				handler: function() {
					this.printEvnPLDispOrp();
				}.createDelegate(this),
				iconCls: 'print16',
				id: 'epldo13efPrintButton',
				tabIndex: 2407,
				text: BTN_FRMPRINT
			}, {
				hidden: getRegionNick() == 'kz',
				handler: function() {
					this.printKLU();
				}.createDelegate(this),
				iconCls: 'print16',
				id: 'EPLDO13EF_PrintKLU',
				tabIndex: 2408,
				text: 'Печать КЛУ при ЗНО'
			}, {
				hidden: getRegionNick() != 'ekb',
				handler: function() {
					this.printOnko();
				}.createDelegate(this),
				iconCls: 'print16',
				id: 'EPLDO13EF_PrintOnko',
				tabIndex: 2409,
				text: 'Печать выписки по онкологии'
			}/*, {
				handler: function() {
					var current_window = Ext.getCmp('EvnPLDispOrp13EditWindow');
					var person_birthday = current_window.PersonInfoPanel.getFieldValue('Person_Birthday');
					var person_surname = current_window.PersonInfoPanel.getFieldValue('Person_Surname');
					var person_firname = current_window.PersonInfoPanel.getFieldValue('Person_Firname');
					var person_secname = current_window.PersonInfoPanel.getFieldValue('Person_Secname');
					var params = {
						onHide: function() {
							this.focus();
						}.createDelegate(this),
						Person_Birthday: person_birthday,
						Person_Firname: person_firname,
						Person_Secname: person_secname,
						Person_Surname: person_surname,
						Person_id: current_window.findById('epldo13efPerson_id').getValue(),
						Server_id: current_window.findById('epldo13efServer_id').getValue(),
						isOrpDisp: true
					};
					
					if (getWnd('swPersonDispHistoryWindow').isVisible())
					{
						sw.swMsg.show({
							buttons: Ext.Msg.OK,
							fn: Ext.emptyFn,
							icon: Ext.Msg.WARNING,
							msg: lang['okno_uje_otkryito'],
							title: ERR_WND_TIT
						});
						return false;
					}

					getWnd('swPersonDispHistoryWindow').show(params);
					
				}.createDelegate(this),
				id: 'epldo13efDispButton',
				tabIndex: 2408,
				text: lang['dispansernyiy_uchet']
			}*/, '-',
			HelpButton(this, -1),
			{
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'cancel16',
				id: 'epldo13efCancelButton',
				onTabAction: function() {
					//Ext.getCmp('epldo13efAttachTypeCombo').focus(true, 200);
				},
				onShiftTabAction: function() {
					//Ext.getCmp('epldo13efAttachTypeCombo').focus(true, 200);
				},
				tabIndex: 2409,
				text: BTN_FRMCANCEL
			}]
		});
		sw.Promed.swEvnPLDispOrp13EditWindow.superclass.initComponent.apply(this, arguments);
	},
	keys: [{
		alt: true,
		fn: function(inp, e) {
			var current_window = Ext.getCmp('EvnPLDispOrp13EditWindow');
			var tabbar = current_window.findById('epldo13efEvnPLTabbar');

			switch (e.getKey())
			{
				case Ext.EventObject.C:
					current_window.doSave();
					break;

				case Ext.EventObject.J:
					current_window.hide();
					break;
			}
		},
		key: [
			Ext.EventObject.C,
			Ext.EventObject.J
		],
		stopEvent: true
	}],
	layout: 'fit',
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
	openEvnVizitDispOrpEditWindow: function(action) {
        var current_window = this;
		var base_form = this.findById('EvnPLDispOrp13EditForm').getForm();

		if (getWnd('swEvnVizitDispOrp13EditWindow').isVisible())
		{
			Ext.Msg.alert(lang['soobschenie'], lang['okno_redaktirovaniya_osmotra_vracha-spetsialista_uje_otkryito']);
			return false;
		}

		var params = new Object();

		var person_id = current_window.PersonInfoPanel.getFieldValue('Person_id');
		var person_birthday = current_window.PersonInfoPanel.getFieldValue('Person_Birthday');
		var person_surname = current_window.PersonInfoPanel.getFieldValue('Person_Surname');
		var person_firname = current_window.PersonInfoPanel.getFieldValue('Person_Firname');
		var person_secname = current_window.PersonInfoPanel.getFieldValue('Person_Secname');
		var sex_id = current_window.PersonInfoPanel.getFieldValue('Sex_id');
		var age = current_window.PersonInfoPanel.getFieldValue('Person_Age');

		var selected_record = current_window.findById('epldo13efEvnVizitDispOrpGrid').getSelectionModel().getSelected();

		if (action == 'add')
		{
			params = current_window.params;

			// буду собирать максимальную дату осмотра или анализов
			var max_date = false;
			
			params.EvnVizitDispOrp_id = null;
			params.Record_Status = 0;
			params['EvnPLDispOrp_id'] = base_form.findField('EvnPLDispOrp_id').getValue();
			params['Server_id'] = base_form.findField('Server_id').getValue();
			params['PersonEvn_id'] = base_form.findField('PersonEvn_id').getValue();
			params['Not_Z_Group_Diag'] = false;
			/*current_window.findById('epldo13efEvnVizitDispOrpGrid').getStore().each(function(rec) {				
				if ( rec.data.OrpDispSpec_id != 1 )
				{
					var diag_code = rec.data.Diag_Code.substr(0,3);
					if ( !diag_code.inlist( Array('Z00', 'Z01', 'Z02', 'Z04', 'Z10') ) );
						params['Not_Z_Group_Diag'] = true;
				}
			})*/;

			var usedOrpDispSpecCodeList = [];
			current_window.findById('epldo13efEvnVizitDispOrpGrid').getStore().each(function(rec) {
				if ( rec.data.Record_Status != 3 )
					usedOrpDispSpecCodeList.push(rec.data.OrpDispSpec_Code);
			});
			params['usedOrpDispSpecCodeList']=usedOrpDispSpecCodeList;
		}
		else if ((action == 'edit') || (action == 'view'))
		{			
			if (!current_window.findById('epldo13efEvnVizitDispOrpGrid').getSelectionModel().getSelected())
			{
				return false;
			}

			if ( !selected_record.data.EvnVizitDispOrp_id == null || selected_record.data.EvnVizitDispOrp_id == '' )
				return;
			
			params = selected_record.data;
			
			params['EvnPLDispOrp_id'] = base_form.findField('EvnPLDispOrp_id').getValue();
			params['Not_Z_Group_Diag'] = false;
			/*current_window.findById('epldo13efEvnVizitDispOrpGrid').getStore().each(function(rec) {				
				if ( rec.data.OrpDispSpec_id != 1 )
				{
					var diag_code = rec.data.Diag_Code.substr(0,3);
					if ( !diag_code.inlist( Array('Z00', 'Z01', 'Z02', 'Z04', 'Z10') ) );
						params['Not_Z_Group_Diag'] = true;
				}
			});*/
			
			var usedOrpDispSpecCodeList = [];
			current_window.findById('epldo13efEvnVizitDispOrpGrid').getStore().each(function(rec) {
				if (rec!=selected_record && rec.data.Record_Status != 3 )
					usedOrpDispSpecCodeList.push(rec.data.OrpDispSpec_Code);
			});
			params['usedOrpDispSpecCodeList']=usedOrpDispSpecCodeList;
		}
		else
		{
			return false;
		}

		var orpDispSpecAllowed = current_window.orpDispSpecAllowed;
		var neworpDispSpecAllowed = [];
		// убираем из списка занятые специальности (usedOrpDispSpecCodeList)
		for(var key in orpDispSpecAllowed) {
			if (typeof orpDispSpecAllowed[key] == 'string' && !orpDispSpecAllowed[key].inlist(usedOrpDispSpecCodeList)) {
				neworpDispSpecAllowed.push(orpDispSpecAllowed[key]);
			}
		}
		
		var pedSpec = 1;
		if ((getRegionNick() == 'buryatiya' && !pedSpec.inlist(usedOrpDispSpecCodeList)) || (this.allowPed && neworpDispSpecAllowed.length == 0 && !pedSpec.inlist(usedOrpDispSpecCodeList))) {
			neworpDispSpecAllowed.push(pedSpec);
		}
		var set_date = base_form.findField('EvnPLDispOrp_setDate').getValue();
		// передаём связи DopDispInfoConsent_id/MedSpecOms_id с OrpDispSpec_Code
		var dopDispInfoConsentData = getStoreRecords( this.dopDispInfoConsentGrid.getGrid().getStore(), {
			exceptionFields: [
				'SurveyType_Name',
				'SurveyType_Code',
				'UslugaComplex_Code',
				'SurveyType_Name',
				'DopDispInfoConsent_IsAgree'
			]
		});
		
        getWnd('swEvnVizitDispOrp13EditWindow').show({
			archiveRecord: this.archiveRecord,
        	action: action,
			EvnPLDisp_id: base_form.findField('EvnPLDispOrp_id').getValue(),
			orpDispSpecAllowed: neworpDispSpecAllowed, // передаём список разрешённых осмотров
			dopDispInfoConsentData: dopDispInfoConsentData,
        	callback: function(data, add_flag) {
				var i;
				var vizit_fields = new Array();

				current_window.findById('epldo13efEvnVizitDispOrpGrid').getStore().fields.eachKey(function(key, item) {
					vizit_fields.push(key);
				});

				current_window.EvnPLDispOrpVizitPanel.getStore().reload();
				
				if ( add_flag == true )
        		{
					// добавляем соответствующую строку в грид "Диагнозы и рекомендации по результатам диспансеризации / профосмотра"
					if (!Ext.isEmpty(data[0].Diag_Code) && data[0].Diag_Code.substring(0, 1) != 'Z') {
						data[0].FormDataJSON = Ext.util.JSON.encode({ EvnVizitDisp_IsFirstTime: data[0].DopDispDiagType_id });

						if ( current_window.EvnDiagAndRecomendationPanel.getGrid().getStore().getCount() == 1
							&& Ext.isEmpty(current_window.EvnDiagAndRecomendationPanel.getGrid().getStore().getAt(0).get('EvnVizitDispOrp_id'))
						) {
							current_window.EvnDiagAndRecomendationPanel.getGrid().getStore().remove(current_window.EvnDiagAndRecomendationPanel.getGrid().getStore().getAt(0));
						}

						current_window.EvnDiagAndRecomendationPanel.getGrid().getStore().loadData(data, true);
					}
	        	}
				else {

					index = -1;
					
					// ищем соответствующую строку в гриде "Диагнозы и рекомендации по результатам диспансеризации / профосмотра", если нет, то добавляем, иначе редактируем
					if (!Ext.isEmpty(data[0].Diag_Code) && data[0].Diag_Code.substring(0, 1) != 'Z') {
						index = current_window.EvnDiagAndRecomendationPanel.getGrid().getStore().findBy(function(rec) { return rec.get('EvnVizitDispOrp_id') == data[0].EvnVizitDispOrp_id; });
						if (index == -1)
						{
							data[0].FormDataJSON = Ext.util.JSON.encode({ EvnVizitDisp_IsFirstTime: data[0].DopDispDiagType_id });

							if ( current_window.EvnDiagAndRecomendationPanel.getGrid().getStore().getCount() == 1
								&& Ext.isEmpty(current_window.EvnDiagAndRecomendationPanel.getGrid().getStore().getAt(0).get('EvnVizitDispOrp_id'))
							) {
								current_window.EvnDiagAndRecomendationPanel.getGrid().getStore().remove(current_window.EvnDiagAndRecomendationPanel.getGrid().getStore().getAt(0));
							}

							current_window.EvnDiagAndRecomendationPanel.getGrid().getStore().loadData(data, true);
						} else {
							var record = current_window.EvnDiagAndRecomendationPanel.getGrid().getStore().getAt(index);

							if ( !Ext.isEmpty(record.get('FormDataJSON')) ) {
								var jsonData = Ext.util.JSON.decode(record.get('FormDataJSON'));

								// Ориентируемся на заполнение DispSurveilType_id, т.к. при добавлении записи EvnVizitDisp_IsFirstTime подставляется автоматически,
								// но если пользователь зайдет в осмотр и отредактирует значение поля DopDispDiagType_id, то в диагнозах и рекомендациях
								// останется несоответствующее значение
								if ( Ext.isEmpty(jsonData.DispSurveilType_id) && (data[0].DopDispDiagType_id == 1 || data[0].DopDispDiagType_id == 2) ) {
									jsonData.EvnVizitDisp_IsFirstTime = data[0].DopDispDiagType_id;
									record.set('FormDataJSON', Ext.util.JSON.encode(jsonData));
								}
							}

							record.set('Diag_id', data[0].Diag_id);
							record.set('Diag_Name', data[0].Diag_Name);
							record.set('OrpDispSpec_Name', data[0].OrpDispSpec_Name);
							record.commit();
						}
					} else {
						index = current_window.EvnDiagAndRecomendationPanel.getGrid().getStore().findBy(function(rec) { return rec.get('EvnVizitDispOrp_id') == data[0].EvnVizitDispOrp_id; });
						if (index != -1)
						{
							var record = current_window.EvnDiagAndRecomendationPanel.getGrid().getStore().getAt(index);
							current_window.EvnDiagAndRecomendationPanel.getGrid().getStore().remove(record);
						}			
					}
				}

				if (getRegionNick() == 'buryatiya' && data[0].OrpDispSpec_Code == 1) {
					if (data[0].Diag_Code && data[0].Diag_Code == 'Z03.1') {
						base_form.findField('EvnPLDispOrp_IsSuspectZNO').setValue(2);
					} else {
						base_form.findField('EvnPLDispOrp_IsSuspectZNO').clearValue();
					}
					base_form.findField('EvnPLDispOrp_IsSuspectZNO').fireEvent('change', base_form.findField('EvnPLDispOrp_IsSuspectZNO'), base_form.findField('EvnPLDispOrp_IsSuspectZNO').getValue());
				}

				current_window.checkIfSetDateShouldBeDisabled();
				
        		return true;
        	},			
        	formParams: params,
        	onHide: function() {
				if (current_window.findById('epldo13efEvnVizitDispOrpGrid').getStore().getCount()) {
					current_window.findById('epldo13efEvnVizitDispOrpGrid').getSelectionModel().selectFirstRow();
				}			
			},
			ownerWindow: current_window,
			DispClass_id: base_form.findField('DispClass_id').getValue(),
			Person_id: person_id,
			Person_Birthday: person_birthday,
			Person_Surname: person_surname,
			Person_Firname: person_firname,
			Person_Secname: person_secname,
			EvnPLDispOrp_setDate: set_date,
			Year: this.Year,
			Sex_id: sex_id,
			Person_Age: age,
			max_date: max_date
		});
	},
	openEvnUslugaDispOrpEditWindow: function(action) {
        var current_window = this;
		var base_form = this.findById('EvnPLDispOrp13EditForm').getForm();

		if (getWnd('swEvnUslugaDispOrp13EditWindow').isVisible())
		{
			Ext.Msg.alert(lang['soobschenie'], lang['okno_redaktirovaniya_laboratornogo_issledovaniya_uje_otkryito']);
			return false;
		}

		var params = new Object();

		var person_id = current_window.PersonInfoPanel.getFieldValue('Person_id');
		var person_birthday = current_window.PersonInfoPanel.getFieldValue('Person_Birthday');
		var person_surname = current_window.PersonInfoPanel.getFieldValue('Person_Surname');
		var person_firname = current_window.PersonInfoPanel.getFieldValue('Person_Firname');
		var person_secname = current_window.PersonInfoPanel.getFieldValue('Person_Secname');
		var sex_id = current_window.PersonInfoPanel.getFieldValue('Sex_id');
		var age = current_window.PersonInfoPanel.getFieldValue('Person_Age');
		
		if (current_window.action == 'add' && Ext.getCmp('epldo13efEvnPLDispOrp_setDate').getValue()=="") {
			var set_date = Date.parseDate(getGlobalOptions().date, 'd.m.Y');
			Ext.getCmp('epldo13efEvnPLDispOrp_setDate').setValue(set_date);
		} else {
			var set_date = Date.parseDate(Ext.getCmp('epldo13efEvnPLDispOrp_setDate').getValue(), 'd.m.Y');
		}

		if (action == 'add')
		{
			params = current_window.params;

			params.EvnUslugaDispOrp_id = swGenTempId(this.findById('epldo13efEvnUslugaDispOrpGrid').getStore(), 'EvnUslugaDispOrp_id');
			params.Record_Status = 0;
			var usedUslugaComplexCodeList = [];
			current_window.findById('epldo13efEvnUslugaDispOrpGrid').getStore().each(function(rec) {
				if ( rec.data.Record_Status != 3 )
					usedUslugaComplexCodeList.push(rec.data.UslugaComplex_Code);
			});
			params['usedUslugaComplexCodeList']=usedUslugaComplexCodeList;
		}
		else if ((action == 'edit') || (action == 'view'))
		{
			if (!current_window.findById('epldo13efEvnUslugaDispOrpGrid').getSelectionModel().getSelected())
			{
				return false;
			}

			var selected_record = current_window.findById('epldo13efEvnUslugaDispOrpGrid').getSelectionModel().getSelected();
			
			if ( !selected_record.data.EvnUslugaDispOrp_id == null || selected_record.data.EvnUslugaDispOrp_id == '' )
				return;

			params = selected_record.data;
			var usedUslugaComplexCodeList = [];
			current_window.findById('epldo13efEvnUslugaDispOrpGrid').getStore().each(function(rec) {
				if (rec!=selected_record && rec.data.Record_Status != 3 )
					usedUslugaComplexCodeList.push(rec.data.UslugaComplex_Code);
			});
			params['usedUslugaComplexCodeList']=usedUslugaComplexCodeList;
		}
		else
		{
			return false;
		}
		
		var uslugaComplexAllowed = current_window.uslugaComplexAllowed;
		var newuslugaComplexAllowed = [];
		// убираем из списка занятые услуги (usedUslugaComplexCodeList)
		for(var key in uslugaComplexAllowed) {
			if (typeof uslugaComplexAllowed[key] == 'string' && !uslugaComplexAllowed[key].inlist(usedUslugaComplexCodeList)) {
				newuslugaComplexAllowed.push(uslugaComplexAllowed[key]);
			}
		}

		var cons_date = base_form.findField('EvnPLDispOrp_consDate').getValue();
		// передаём связи DopDispInfoConsent_id/MedSpecOms_id с UslugaComplex_Code
		var dopDispInfoConsentData = getStoreRecords( this.dopDispInfoConsentGrid.getGrid().getStore(), {
			exceptionFields: [
				'SurveyType_Name',
				'SurveyTypeLink_id',
				'SurveyType_Code',
				'OrpDispSpec_Code',
				'SurveyType_Name',
				'DopDispInfoConsent_IsAgree'
			]
		});
		
        getWnd('swEvnUslugaDispOrp13EditWindow').show({
			archiveRecord: this.archiveRecord,
        	action: action,
			EvnPLDisp_id: base_form.findField('EvnPLDispOrp_id').getValue(),
			uslugaComplexAllowed: newuslugaComplexAllowed, // передаём список разрешённых услуг
			dopDispInfoConsentData: dopDispInfoConsentData,
        	callback: function(data, add_flag) {
				var i;
				var usluga_fields = new Array();

				current_window.findById('epldo13efEvnUslugaDispOrpGrid').getStore().fields.eachKey(function(key, item) {
					usluga_fields.push(key);
				});
				if (add_flag == true)
        		{
					// удаляем пустую строку если она есть					
					if ( current_window.findById('epldo13efEvnUslugaDispOrpGrid').getStore().getCount() == 1 )
					{
						var selected_record = current_window.findById('epldo13efEvnUslugaDispOrpGrid').getStore().getAt(0);
						if ( !selected_record.data.EvnUslugaDispOrp_id == null || selected_record.data.EvnUslugaDispOrp_id == '' )
							current_window.findById('epldo13efEvnUslugaDispOrpGrid').getStore().removeAll();
					}
					
					current_window.findById('epldo13efEvnUslugaDispOrpGrid').getStore().clearFilter();
					current_window.findById('epldo13efEvnUslugaDispOrpGrid').getStore().loadData(data, add_flag);
					current_window.findById('epldo13efEvnUslugaDispOrpGrid').getStore().filterBy(function(record) {
						if (record.data.Record_Status != 3)
						{
							return true;
						}
					});
	        	}
				else {
	        		index = current_window.findById('epldo13efEvnUslugaDispOrpGrid').getStore().findBy(function(rec) { return rec.get('EvnUslugaDispOrp_id') == data[0].EvnUslugaDispOrp_id; });

	        		if (index == -1)
	        		{
	        			return false;
	        		}

					var record = current_window.findById('epldo13efEvnUslugaDispOrpGrid').getStore().getAt(index);

					for (i = 0; i < usluga_fields.length; i++)
					{
						record.set(usluga_fields[i], data[0][usluga_fields[i]]);
					}

					record.commit();
				}
				
				current_window.checkIfSetDateShouldBeDisabled();
				
        		return true;
        	},
        	formParams: params,
        	onHide: function() {
				current_window.findById('epldo13efEvnUslugaDispOrpGrid').getSelectionModel().selectFirstRow();
				current_window.findById('epldo13efEvnUslugaDispOrpGrid').getView().focusRow(0);				
			},
        	ownerWindow: current_window,
			DispClass_id: base_form.findField('DispClass_id').getValue(),
		    Person_id: person_id,
		    Person_Birthday: person_birthday,
			Person_Surname: person_surname,
		    Person_Firname: person_firname,
			Person_Secname: person_secname,
			EvnPLDispOrp_consDate: cons_date,
			Sex_id: sex_id,
			Person_Age: age,
			set_date: set_date,
			UslugaComplex_Date: current_window.findById('epldo13efEvnPLDispOrp_consDate').getValue()
		});
	},
	openPersonCureHistoryWindow: function() {
		var current_window = this;
		var form = current_window.findById('EvnPLDispOrp13EditForm');

		if (getWnd('swPersonCureHistoryWindow').isVisible())
		{
			current_window.showMessage(lang['soobschenie'], lang['okno_prosmotra_istorii_lecheniya_cheloveka_uje_otkryito']);
			return false;
		}

		var person_id = form.findById('epldo13efPerson_id').getValue();
		var server_id = form.findById('epldo13efServer_id').getValue();

		getWnd('swPersonCureHistoryWindow').show({
			onHide: function() {
				if (current_window.action == 'view')
				{
					form.buttons[1].focus();
				}
			},
			Person_id: person_id,
			Server_id: server_id
		});
	},
	params: {
		EvnVizitPL_setDate: null,
		LpuSection_id: null,
		MedPersonal_id: null
	},
	plain: true,
	printEvnPLDispOrp: function() {
		if ((this.action == 'add') || (this.action == 'edit'))
		{
			this.doSave(null, true);
		}
		else if (this.action == 'view')
		{
			var evn_pl_id = this.findById('epldo13efEvnPLDispOrp_id').getValue();
			var server_id = this.findById('epldo13efServer_id').getValue();

			window.open(C_EPLDO13_PRINT + '&EvnPLDispOrp_id=' + evn_pl_id + '&Server_id=' + server_id, '_blank');
		}
	},
	printKLU: function() {
		var win = this;
		var base_form = this.findById('EvnPLDispOrp13EditForm').getForm();
		
		var print = function() {
			var evn_pl_id = base_form.findField('EvnPLDispOrp_id').getValue();
			printBirt({
				'Report_FileName': 'CheckList_MedCareOnkoPatients.rptdesign',
				'Report_Params': '&Evn_id=' + evn_pl_id, 
				'Report_Format': 'pdf'
			});
		}
		if ( 'add' == this.action || 'edit' == this.action ) {
            this.doSave( print );
        }
        else if ( 'view' == this.action ) {
            print();
        }
	},
	printOnko: function() {
		var win = this;
		var base_form = this.findById('EvnPLDispOrp13EditForm').getForm();

		var print = function() {
			var evn_pl_id = base_form.findField('EvnPLDispOrp_id').getValue();
			printBirt({
				'Report_FileName': 'WritingOut_MedCareOnkoPatients.rptdesign',
				'Report_Params': '&Evn_id=' + evn_pl_id,
				'Report_Format': 'pdf'
			});
		}
		if ( 'add' == this.action || 'edit' == this.action ) {
			this.doSave( print );
		}
		else if ( 'view' == this.action ) {
			print();
		}
	},
	resizable: true,
	filtersByDispClass: function() {
		var base_form = this.findById('EvnPLDispOrp13EditForm').getForm();
		
		base_form.findField('ChildStatusType_id').clearFilter();
		if (base_form.findField('DispClass_id').getValue().inlist([3,4])) { // обычные дети-сироты
			base_form.findField('ChildStatusType_id').setValue(1);
			base_form.findField('ChildStatusType_id').disable();
		} else { // усыновленные
			base_form.findField('ChildStatusType_id').getStore().filterBy(function(record) {
				return (record.get('ChildStatusType_Code') > 1);
			});
			if (this.action != 'view') {
				base_form.findField('ChildStatusType_id').enable();
			}
		}
		
		base_form.findField('EvnPLDispOrp_IsTwoStage').showContainer();
	},
	checkForCostPrintPanel: function() {
		var base_form = this.findById('EvnPLDispOrp13EditForm').getForm();

		this.CostPrintPanel.hide();
		base_form.findField('EvnCostPrint_setDT').setAllowBlank(true);
		base_form.findField('EvnCostPrint_Number').setContainerVisible(getRegionNick() == 'khak');
		base_form.findField('EvnCostPrint_IsNoPrint').setAllowBlank(true);

		// если справка уже печаталась и случай закрыт, отображаем раздел с данными справки
		if (base_form.findField('EvnPLDispOrp_IsFinish').getValue() == 2 && !Ext.isEmpty(base_form.findField('EvnCostPrint_setDT').getValue()) && getRegionNick().inlist(['perm', 'kz', 'ufa'])) {
			this.CostPrintPanel.show();
			// поля обязтаельные
			base_form.findField('EvnCostPrint_setDT').setAllowBlank(false);
			base_form.findField('EvnCostPrint_IsNoPrint').setAllowBlank(false);
		}
	},
	show: function() {
		sw.Promed.swEvnPLDispOrp13EditWindow.superclass.show.apply(this, arguments);
		
		var current_window = this;

		this.restore();
		this.center();
		this.maximize();

		current_window.blockSaveDopDispInfoConsent = false;
		current_window.saveDopDispInfoConsentAfterLoad = false;

		this.DDICSaved = true;
		this.lastEvnPLDispOrp_setDate = null;
		current_window.ignoreEmptyFields = false;
		this.allowPed = false;

		// this.findById('epldo13efEvnPLTabbar').setActiveTab(4);

		var form = this.findById('EvnPLDispOrp13EditForm');
		var base_form = form.getForm();
		base_form.findField('VaccinType_id').hideContainer();
		// добавляем чекбоксы типов прививок
		var vaccinFieldset = this.findById('EPLDO13EW_VaccinFieldset');
		if (vaccinFieldset.items.items.length == 0) {
			base_form.findField('VaccinType_id').getStore().each(function(rec) {
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

		base_form.findField('ProfVaccinType_id').fireEvent('change', base_form.findField('ProfVaccinType_id'), base_form.findField('ProfVaccinType_id').getValue());

		base_form.reset();
		this.checkForCostPrintPanel();

		base_form.findField('EvnPLDispOrp_RepFlag').hideContainer();

		base_form.findField('InvalidType_id').getStore().filterBy(function(rec) {
			return rec.get('InvalidType_Code').toString().inlist([ '1', '2', '3' ]);
		});

		base_form.findField('HeightAbnormType_id').setAllowBlank(true);
		base_form.findField('WeightAbnormType_id').setAllowBlank(true);
        base_form.findField('AssessmentHealth_setDT').setAllowBlank(true);
        base_form.findField('AssessmentHealth_reExamDT').setAllowBlank(true);
        base_form.findField('InvalidDiagType_id').setAllowBlank(true);
        Ext.getCmp('EPLDO13EF_PrintKLU').disable();
        Ext.getCmp('EPLDO13EF_PrintOnko').disable();
		this.PersonFirstStageAgree = false; // Пациент не согласен на первый этап диспансеризации
		this.callback = Ext.emptyFn;
		this.onHide = Ext.emptyFn;

		if (!arguments[0] || (Ext.isEmpty(arguments[0].DispClass_id) && Ext.isEmpty(arguments[0].EvnPLDispOrp_id)))
		{
			Ext.Msg.alert(lang['soobschenie'], lang['nevernyie_parametryi']);
			return false;
		}

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

		//Проверяем возможность редактирования документа
		if (this.action === 'edit' && arguments[0].EvnPLDispOrp_id) {
			Ext.Ajax.request({
				failure: function (response, options) {
					sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_zagruzke_dannyih_formyi'], function () {
						this.hide();
					}.createDelegate(this));
				},
				params: {
					Evn_id: arguments[0].EvnPLDispOrp_id,
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
		
		var form = this.findById('EvnPLDispOrp13EditForm');
		var base_form = form.getForm();
		var current_window = this;
		
		var isbud_combo = this.findById('epldo13efIsBudCombo');
		var isfinish_combo = this.findById('epldo13efIsFinishCombo');

		var evnpldispdop_id = form.findById('epldo13efEvnPLDispOrp_id').getValue();
		var person_id = form.findById('epldo13efPerson_id').getValue();
		var server_id = form.findById('epldo13efServer_id').getValue();
		var vaccinFieldset = this.findById('EPLDO13EW_VaccinFieldset');
		
		var loadMask = new Ext.LoadMask(Ext.get('EvnPLDispOrp13EditWindow'), { msg: LOAD_WAIT });
		loadMask.show();

		this.EvnDiagAndRecomendationPanel.getGrid().getStore().removeAll();
		this.EvnDiagDopDispAndRecomendationPanel.getGrid().getStore().removeAll();
		this.DispAppointGrid.getGrid().getStore().removeAll();

		this.findById('epldo13efEvnVizitDispOrpGrid').getStore().removeAll();
		LoadEmptyRow(this.findById('epldo13efEvnVizitDispOrpGrid'));
		this.findById('epldo13efEvnVizitDispOrpGrid').getTopToolbar().items.items[0].disable();
		if (this.action != 'view') {
			this.findById('epldo13efEvnVizitDispOrpGrid').getTopToolbar().items.items[0].enable();
		}
		this.findById('epldo13efEvnVizitDispOrpGrid').getTopToolbar().items.items[1].disable();
		this.findById('epldo13efEvnVizitDispOrpGrid').getTopToolbar().items.items[2].disable();
		this.findById('epldo13efEvnVizitDispOrpGrid').getTopToolbar().items.items[3].disable();

		this.findById('epldo13efEvnUslugaDispOrpGrid').getStore().removeAll();
		LoadEmptyRow(this.findById('epldo13efEvnUslugaDispOrpGrid'));
		this.findById('epldo13efEvnUslugaDispOrpGrid').getTopToolbar().items.items[0].disable();
		if (this.action != 'view') {
			this.findById('epldo13efEvnUslugaDispOrpGrid').getTopToolbar().items.items[0].enable();
		}
		this.findById('epldo13efEvnUslugaDispOrpGrid').getTopToolbar().items.items[1].disable();
		this.findById('epldo13efEvnUslugaDispOrpGrid').getTopToolbar().items.items[2].disable();
		this.findById('epldo13efEvnUslugaDispOrpGrid').getTopToolbar().items.items[3].disable();

		if (Ext.isEmpty(base_form.findField('PayType_id').getValue())) {
			base_form.findField('PayType_id').setFieldValue('PayType_SysNick', 'oms');
		}

		this.PersonInfoPanel.load({ 
			Person_id: person_id, 
			Server_id: server_id, 
			callback: function() {
				var sex_id = current_window.PersonInfoPanel.getFieldValue('Sex_id');

				if ( sex_id == 1 ) {
					// скрыть поля для девочек
					base_form.findField('AssessmentHealth_Ma').hideContainer();
					base_form.findField('AssessmentHealth_Me').hideContainer();
					current_window.findById('epldo13ef_menarhe').hide();
					current_window.findById('epldo13ef_menses').hide();
				}
				else {
					base_form.findField('AssessmentHealth_Ma').showContainer();
					base_form.findField('AssessmentHealth_Me').showContainer();
					current_window.findById('epldo13ef_menarhe').show();
					current_window.findById('epldo13ef_menses').show();
				}
				
				if ( sex_id == 2 ) {
					// скрыть поля для мальчиков
					base_form.findField('AssessmentHealth_Fa').hideContainer();
				}
				else {
					base_form.findField('AssessmentHealth_Fa').showContainer();
				}
				
				switch ( current_window.action ) {
					case 'add':
						current_window.setTitle(lang['karta_dispanserizatsii_nesovershennoletnego_-_1_etap_dobavlenie']);
						current_window.enableEdit(true);

						current_window.filtersByDispClass();
						
						setCurrentDateTime({
							callback: function(date) {
								base_form.findField('EvnPLDispOrp_setDate').setValue(date);

								base_form.findField('EvnPLDispOrp_consDate').fireEvent('change', base_form.findField('EvnPLDispOrp_consDate'), date);
								base_form.findField('EvnPLDispOrp_setDate').fireEvent('change', base_form.findField('EvnPLDispOrp_setDate'), date);

								base_form.findField('HeightAbnormType_YesNo').setValue(1);
								base_form.findField('WeightAbnormType_YesNo').setValue(1);

								base_form.findField('WeightAbnormType_YesNo').fireEvent('change', base_form.findField('WeightAbnormType_YesNo'), base_form.findField('WeightAbnormType_YesNo').getValue());
								base_form.findField('HeightAbnormType_YesNo').fireEvent('change', base_form.findField('HeightAbnormType_YesNo'), base_form.findField('HeightAbnormType_YesNo').getValue());

								loadMask.hide();
							},
							dateField: base_form.findField('EvnPLDispOrp_consDate'),
							loadMask: false,
							setDate: true,
							setDateMaxValue: true,
							windowId: current_window.id
						});
						
						base_form.findField('EvnPLDispOrp_IsMobile').fireEvent('check', base_form.findField('EvnPLDispOrp_IsMobile'), base_form.findField('EvnPLDispOrp_IsMobile').getValue());

						current_window.findById('epldo13efIsFinishCombo').setValue(1);
						
						base_form.findField('Diag_spid').setContainerVisible(false);
						base_form.findField('Diag_spid').setAllowBlank(true);
					break;

					case 'edit':
					case 'view':
						base_form.load({
							failure: function() {
								swEvnPLDispOrp13EditWindow.hide();
								loadMask.hide();
							},
							params: {
								EvnPLDispOrp_id: evnpldispdop_id,
								archiveRecord: current_window.archiveRecord
							},
							success: function(form, action) {
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

								if (!Ext.isEmpty(base_form.findField('EvnPLDispOrp_IsSuspectZNO')) && base_form.findField('EvnPLDispOrp_IsSuspectZNO').getValue() == 2) {
									Ext.getCmp('EPLDO13EF_PrintKLU').enable();
									Ext.getCmp('EPLDO13EF_PrintOnko').enable();
								} else {
									Ext.getCmp('EPLDO13EF_PrintKLU').disable();
									Ext.getCmp('EPLDO13EF_PrintOnko').disable();
								}

								base_form.findField('ProfVaccinType_id').fireEvent('change', base_form.findField('ProfVaccinType_id'), base_form.findField('ProfVaccinType_id').getValue());

								if ( base_form.findField('accessType').getValue() == 'view' ) {
									current_window.action = 'view';
								}
								
								if ( current_window.action == 'edit' ) {
									current_window.setTitle(lang['karta_dispanserizatsii_nesovershennoletnego_-_1_etap_redaktirovanie']);
									current_window.enableEdit(true);
								}
								else {
									current_window.setTitle(lang['karta_dispanserizatsii_nesovershennoletnego_-_1_etap_prosmotr']);
									current_window.enableEdit(false);				
								}
								loadMask.hide();

								if ( getRegionNick() == 'perm' && base_form.findField('EvnPLDispOrp_IsPaid').getValue() == 2 && parseInt(base_form.findField('EvnPLDispOrp_IndexRepInReg').getValue()) > 0 ) {
									base_form.findField('EvnPLDispOrp_RepFlag').showContainer();

									if ( parseInt(base_form.findField('EvnPLDispOrp_IndexRep').getValue()) >= parseInt(base_form.findField('EvnPLDispOrp_IndexRepInReg').getValue()) ) {
										base_form.findField('EvnPLDispOrp_RepFlag').setValue(true);
									}
									else {
										base_form.findField('EvnPLDispOrp_RepFlag').setValue(false);
									}
								}

								if (Ext.isEmpty(base_form.findField('PayType_id').getValue())) {
									base_form.findField('PayType_id').setFieldValue('PayType_SysNick', 'oms');
								}

								current_window.checkForCostPrintPanel();

								current_window.filtersByDispClass();
						
								base_form.findField('EvnPLDispOrp_setDate').fireEvent('change', base_form.findField('EvnPLDispOrp_setDate'), base_form.findField('EvnPLDispOrp_setDate').getValue());
								base_form.findField('WeightAbnormType_YesNo').fireEvent('change', base_form.findField('WeightAbnormType_YesNo'), base_form.findField('WeightAbnormType_YesNo').getValue());
								base_form.findField('HeightAbnormType_YesNo').fireEvent('change', base_form.findField('HeightAbnormType_YesNo'), base_form.findField('HeightAbnormType_YesNo').getValue());

								current_window.reloadUslugaComplexAllowed();

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
								
								base_form.findField('EvnPLDispOrp_IsMobile').fireEvent('check', base_form.findField('EvnPLDispOrp_IsMobile'), base_form.findField('EvnPLDispOrp_IsMobile').getValue());
				
								// загрузка грида осмотров
								current_window.EvnPLDispOrpVizitPanel.getStore().load({ 
									params: { EvnPLDispOrp_id: evnpldispdop_id },
									callback: function() {
										if ( Ext.getCmp('epldo13efEvnVizitDispOrpGrid').getStore().getCount() == 0 )
											LoadEmptyRow(Ext.getCmp('epldo13efEvnVizitDispOrpGrid'));
									}
								});

								// загрузка грида "Диагнозы и рекомендации по результатам диспансеризации / профосмотра"
								current_window.EvnDiagAndRecomendationPanel.loadData({
									params: {
										EvnPLDispOrp_id: evnpldispdop_id
									},
									globalFilters: {
										EvnPLDispOrp_id: evnpldispdop_id
									},
									noFocusOnLoad: true
								});
								
								// загрузка грида "Диагнозы и рекомендации по результатам диспансеризации / профосмотра"
								current_window.EvnDiagDopDispAndRecomendationPanel.loadData({
									params: {
										EvnPLDisp_id: evnpldispdop_id
									},
									globalFilters: {
										EvnPLDisp_id: evnpldispdop_id
									},
									noFocusOnLoad: true
								});

								// загрузка грида обследований
								current_window.EvnUslugaDispOrpPanel.getStore().load({ 
									params: { EvnPLDispOrp_id: evnpldispdop_id },
									callback: function() {
										if ( Ext.getCmp('epldo13efEvnUslugaDispOrpGrid').getStore().getCount() == 0 )
											LoadEmptyRow(Ext.getCmp('epldo13efEvnUslugaDispOrpGrid'));
									}
								});

								if (getRegionNick() != 'kz') {
									current_window.DispAppointGrid.loadData({
										params: {EvnPLDisp_id: evnpldispdop_id, object: 'EvnPLDispOrp'},
										globalFilters: {EvnPLDisp_id: evnpldispdop_id},
										noFocusOnLoad: true
									});
								}

								if ( !base_form.findField('PayType_id').disabled ) {
									base_form.findField('PayType_id').focus(false);
								}
								else {
									current_window.buttons[1].focus();
								}
                                base_form.findField('InvalidType_id').fireEvent('change',base_form.findField('InvalidType_id'),base_form.findField('InvalidType_id').getValue());
						
								base_form.findField('Diag_spid').setContainerVisible(base_form.findField('EvnPLDispOrp_IsSuspectZNO').getValue() == 2);
								base_form.findField('Diag_spid').setAllowBlank(!getRegionNick().inlist([ 'astra', 'perm' ]) || base_form.findField('EvnPLDispOrp_IsSuspectZNO').getValue() != 2);
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
							url: C_EPLDO13_LOAD
						});
					break;
				}

				form.getForm().clearInvalid();
				current_window.doLayout();
			} 
		});
	},
	title: lang['karta_dispanserizatsii_nesovershennoletnego'],
	width: 800
});