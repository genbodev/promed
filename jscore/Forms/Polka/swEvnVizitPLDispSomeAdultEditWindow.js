/**
* swEvnVizitPLDispSomeAdultEditWindow - окно редактирования/добавления посещения пациентом поликлиники.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Polka
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Stas Bykov aka Savage (savage@swan.perm.ru)
* @version      0.001-23.07.2009
* @comment      Префикс для id компонентов EVPLDSAEF (EvnVizitPLDispSomeAdultEditForm)
*
*
* @input data: action - действие (add, edit, view)
*
*
* Использует: окно редактирования диагноза (swEvnDiagPLEditWindow)
*             окно редактирования рецепта (swEvnReceptEditWindow)
*             окно выбора типа услуги (swEvnUslugaSetWindow)
*             окно редактирования услуги (swEvnUslugaEditWindow)
*/
/*NO PARSE JSON*/

sw.Promed.swEvnVizitPLDispSomeAdultEditWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swEvnVizitPLDispSomeAdultEditWindow',
	objectSrc: '/jscore/Forms/Polka/swTemplatesEvnVizitPLDispSomeAdultEditWindow.js',

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

		if ( event != 'EvnDiagPL' && event != 'EvnDirection' && event != 'EvnRecept' && event != 'EvnUsluga' ) {
			return false;
		}

		var base_form = this.findById('EvnVizitPLDispSomeAdultEditForm').getForm();
		var error = '';
		var grid = null;
		var question = '';
		var params = new Object();
		var url = '';

		switch ( event ) {
			case 'EvnDiagPL':
				error = lang['pri_udalenii_soputstvuyuschego_diagnoza_voznikli_oshibki'];
				grid = this.findById('EVPLDSAEF_EvnDiagPLGrid');
				question = lang['udalit_soputstvuyuschiy_diagnoz'];
				url = '/?c=EvnPL&m=deleteEvnDiagPL';
			break;

			/*case 'EvnDirection':
				error = lang['pri_udalenii_napravleniya_voznikli_oshibki'];
				grid = this.findById('EVPLDSAEF_EvnDirectionGrid');
				question = lang['udalit_napravlenie'];
				url = '/?c=EvnDirection&m=deleteEvnDirection';
			break;*/

			case 'EvnRecept':
				grid = this.findById('EVPLDSAEF_EvnReceptGrid');
			break;

			case 'EvnUsluga':
				error = lang['pri_udalenii_uslugi_voznikli_oshibki'];
				grid = this.findById('EVPLDSAEF_EvnUslugaGrid');
				question = lang['udalit_uslugu'];
				url = '/?c=EvnUsluga&m=deleteEvnUsluga';
			break;
		}

		if ( !grid || !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get(event + '_id') ) {
			return false;
		}

		var selected_record = grid.getSelectionModel().getSelected();

		switch ( event ) {
			case 'EvnDiagPL':
				params['EvnDiagPL_id'] = selected_record.get('EvnDiagPL_id');
			break;

			case 'EvnDirection':
				params['EvnDirection_id'] = selected_record.get('EvnDirection_id');
			break;

			case 'EvnRecept':
				params['EvnRecept_id'] = selected_record.get('EvnRecept_id');
			break;

			case 'EvnUsluga':
				params['class'] = selected_record.get('EvnClass_SysNick');
				params['id'] = selected_record.get('EvnUsluga_id');
			break;
		}

		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId, text, obj) {
				if ( buttonId == 'yes' ) {
					if ( event == 'EvnRecept' ) {
						getWnd('swEvnReceptDeleteWindow').show({
							callback: function() {
								grid.getStore().reload();
							},
							EvnRecept_id: params['EvnRecept_id'],
							onHide: function() {
								
							}
						});
					}
					else {
						var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Удаление записи..." });
						loadMask.show();

						Ext.Ajax.request({
							failure: function(response, options) {
								loadMask.hide();
								sw.swMsg.alert(lang['oshibka'], error);
							},
							params: params,
							success: function(response, options) {
								loadMask.hide();
								
								var response_obj = Ext.util.JSON.decode(response.responseText);

								if ( response_obj.success == false ) {
									sw.swMsg.alert(lang['oshibka'], response_obj.Error_Msg ? response_obj.Error_Msg : error);
								}
								else {
									grid.getStore().remove(selected_record);

									if ( event == 'EvnUsluga' ) {
										this.EvnUslugaGridIsModified = true;
										this.uetValuesRecount();
									}

									if ( grid.getStore().getCount() == 0 ) {
										grid.getTopToolbar().items.items[1].disable();
										grid.getTopToolbar().items.items[2].disable();
										grid.getTopToolbar().items.items[3].disable();
										LoadEmptyRow(grid);

										if ( event == 'EvnUsluga' ) {
											base_form.findField('EvnVizitPL_Uet').enable();
											base_form.findField('EvnVizitPL_UetOMS').enable();
										}
									}
								}
								
								grid.getView().focusRow(0);
								grid.getSelectionModel().selectFirstRow();
							}.createDelegate(this),
							url: url
						});
					}
				}
			}.createDelegate(this),
			icon: Ext.MessageBox.QUESTION,
			msg: question,
			title: lang['vopros']
		});
	},
	/*deletePersonDisp: function() {
		var grid = this.findById('EVPLDSAEF_PersonDispGrid');

		if ( !grid || !grid.getSelectionModel().getSelected() ) {
			return false;
		}

		var selected_record = grid.getSelectionModel().getSelected();

		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId, text, obj) {
				if ( buttonId == 'yes' ) {
					var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Удаление записи..." });
					loadMask.show();

					Ext.Ajax.request({
						failure: function(response, options) {
							loadMask.hide();
							sw.swMsg.alert(lang['oshibka'], lang['pri_udalenii_kartyi_dispansernogo_ucheta_patsienta_voznikli_oshibki']);
						},
						params: {
							PersonDisp_id: selected_record.get('PersonDisp_id')
						},
						success: function(response, options) {
							loadMask.hide();
							grid.getStore().remove(selected_record);

							if ( grid.getStore().getCount() == 0 ) {
								LoadEmptyRow(grid);
							}

							grid.getView().focusRow(0);
							grid.getSelectionModel().selectFirstRow();
						},
						url: '/?c=PersonDisp&m=deletePersonDisp'
					});
				}
			}.createDelegate(this),
			icon: Ext.MessageBox.QUESTION,
			msg: lang['vyi_deystvitelno_jelaete_udalit_kartu_dispansernogo_ucheta_patsienta'],
			title: lang['podtverjdenie_udaleniya']
		});
	},*/
	getTemplateFavorites: function() {
		var cur_wnd = this;
		var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Загрузка списка часто используемых шаблонов..." });
		loadMask.show();
        Ext.Ajax.request({
            failure: function(response, options) {
                loadMask.hide();
                sw.swMsg.alert(lang['oshibka'], lang['pri_zagruzke_spiska_chasto_ispolzuemyih_shablonov_voznikli_oshibki']);
            },
            params: {EvnClass_id: cur_wnd.EvnXmlPanel.getOption('EvnClass_id')},
            success: function(response, options) {
                loadMask.hide();
                if ( response.responseText )
                {
                    var result = {
                        data: Ext.util.JSON.decode(response.responseText)
                    };
                    if ( Ext.isArray(result.data) && result.data.length > 0 )
                    {
                        var TemplateFavoritesContextMenu = new Ext.menu.Menu();
                        for (i=0; i < result.data.length; i++)
                        {
                            TemplateFavoritesContextMenu.add(new Ext.Action({
                                name: result.data[i].XmlTemplateFavorites_id,
                                //text: result.data[i].XmlTemplate_Caption + ' (' + result.data[i].XmlTemplateFavorites_CountLoad + ')',
                                text: '<B>' + result.data[i].XmlTemplate_Caption + '</B>',
                                tooltip: result.data[i].XmlTemplate_Caption,
                                template_id: result.data[i].XmlTemplate_id,
                                iconCls : 'template16',
                                handler: function() {
                                    cur_wnd.EvnXmlPanel.onBeforeCreate(cur_wnd.EvnXmlPanel, 'onSelectXmlTemplate', this.template_id);
                                }
                            }));
                        }
                        TemplateFavoritesContextMenu.showAt(Ext.getCmp('EVPLDSAEF_TemplateFavorites_btn').getEl().getXY());
                    } else {
                        var msg = result.data.Error_Msg || lang['spisok_nedavnih_shablonov_pust'];
                        sw.swMsg.alert(lang['uvedomlenie'], msg);
                    }
                }
            },
            url: '/?c=XmlTemplate&m=getFavorites'
        });
	},
	doSave: function(options) {
		// options @Object
		// options.ignoreEvnUslugaCountCheck @Boolean Не проверять наличие выполненных услуг, если true
		// options.ignoreEvnVizitPLSetDateCheck @Boolean Не проверять дату посещения, если true
		// options.openChildWindow @Function Открыть дочернее окно после сохранения

		if ( this.formStatus == 'save' ) {
			return false;
		}

		if ( typeof options != 'object' ) {
			options = new Object();
		}

		this.formStatus = 'save';

		var base_form = this.findById('EvnVizitPLDispSomeAdultEditForm').getForm();
		var form = this.findById('EvnVizitPLDispSomeAdultEditForm');
		var isNotPerm = (getGlobalOptions().region && getGlobalOptions().region.nick != 'perm');

		if ( !base_form.isValid() ) {
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
/*
		// Если Уфа и ignoreEvnUslugaCountCheck = false либо не задан, то проверяем количество введенных услуг
		// Если не введено ни одно услуги, то посещение не сохраняем и выдаем сообщение
		// если введено более одной услуги, то тоже выдаем сообщение
		if ( (!options || !options.ignoreEvnUslugaCountCheck) && getGlobalOptions().region && getGlobalOptions().region.nick == 'ufa' ) {
			var evn_usluga_store = this.findById('EVPLDSAEF_EvnUslugaGrid').getStore();

			if ( evn_usluga_store.getCount() == 0 || evn_usluga_store.getCount() > 1 || !evn_usluga_store.getAt(0).get('EvnUsluga_id') ) {
				sw.swMsg.show({
					buttons: Ext.Msg.OK,
					fn: function() {
						this.formStatus = 'edit';
						this.findById('EVPLDSAEF_EvnUslugaGrid').getView().focusRow(0);
						this.findById('EVPLDSAEF_EvnUslugaGrid').getSelectionModel().selectFirstRow();
					}.createDelegate(this),
					icon: Ext.Msg.WARNING,
					msg: lang['posescheniyu_doljna_sootvetstvovat_odna_usluga'],
					title: ERR_INVFIELDS_TIT
				});
				return false;
			}
		}
*/
		// Если ignoreEvnVizitPLSetDateCheck = false либо не задан и посещение добавляется, то проверяем дату посещения
		if ( !options.ignoreEvnVizitPLSetDateCheck && this.action == 'add' ) {
			var evn_vizit_pl_set_dt = getValidDT(Ext.util.Format.date(base_form.findField('EvnVizitPL_setDate').getValue(), 'd.m.Y'), base_form.findField('EvnVizitPL_setTime').getValue());
			var min_available_date = new Date().add(Date.MONTH, -3);

			if ( evn_vizit_pl_set_dt < min_available_date ) {
				sw.swMsg.show({
					buttons: Ext.Msg.YESNO,
					fn: function(buttonId, text, obj) {
						this.formStatus = 'edit';

						if ( buttonId == 'yes' ) {
							options.ignoreEvnVizitPLSetDateCheck = true;
							this.doSave(options);
						}
						else {
							base_form.findField('EvnVizitPL_setDate').focus(true);
						}
					}.createDelegate(this),
					icon: Ext.MessageBox.QUESTION,
					msg: lang['data_posescheniya_otlichaetsya_ot_tekuschey_bolee_chem_na_3_mesyatsa_sohranit_poseschenie'],
					title: lang['vopros']
				});
				return false;
			}
		}

		var params = new Object();
		var record = null;

		if ( typeof options == 'object' ) {
/*
			if ( options.ignoreEvnUslugaCountCheck == true ) {
				params.ignoreEvnUslugaCountCheck = 1;
			}
*/
            if ( options.ignoreEvnVizitPLSetDateCheck == true ) {
                params.ignoreEvnVizitPLSetDateCheck = 1;
            }
            if ( options.ignoreDayProfileDuplicateVizit == true ) {
                params.ignoreDayProfileDuplicateVizit = 1;
            }

		}

		var diag_code = '';
		var diag_name = '';
		var med_personal_id;
		var med_staff_fact_id = base_form.findField('MedStaffFact_id').getValue();
		var lpu_section_profile_code = '';
		var med_personal_fio = '';
		var pay_type_nick = '';
		var service_type_name = '';

		record = base_form.findField('MedStaffFact_id').getStore().getById(med_staff_fact_id);
		if ( record ) {
			lpu_section_profile_code = record.get('LpuSectionProfile_Code');
			med_personal_id = record.get('MedPersonal_id');
			med_personal_fio = record.get('MedPersonal_Fio');
			base_form.findField('MedPersonal_id').setValue(record.get('MedPersonal_id'));
		}

		record = base_form.findField('MedStaffFact_sid').getStore().getById(base_form.findField('MedStaffFact_sid').getValue());
		if ( record ) {
			base_form.findField('MedPersonal_sid').setValue(record.get('MedPersonal_id'));
		}

		record = base_form.findField('PayType_id').getStore().getById(base_form.findField('PayType_id').getValue());
		if ( record ) {
			pay_type_nick = record.get('PayType_SysNick');
		}

		record = base_form.findField('Diag_id').getStore().getById(base_form.findField('Diag_id').getValue());
/*
		// Диагноз для Уфы - обязательное поле
		if ( !record && getGlobalOptions().region && getGlobalOptions().region.nick == 'ufa' ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					this.formStatus = 'edit';
					base_form.findField('Diag_id').focus(true);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: lang['pole_diagnoz_obyazatelno_dlya_zapolneniya'],
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
*/
		if ( record ) {
			diag_code = record.get('Diag_Code');
			diag_name = record.get('Diag_Name');

			if ( diag_code.substr(0, 1).toUpperCase() != 'Z' && !base_form.findField('DeseaseType_id').getValue() ) {
				sw.swMsg.show({
					buttons: Ext.Msg.OK,
					fn: function() {
						this.formStatus = 'edit';
						base_form.findField('DeseaseType_id').markInvalid(lang['pole_obyazatelno_dlya_zapolneniya_pri_vyibrannom_diagnoze']);
						base_form.findField('DeseaseType_id').focus(false);
					}.createDelegate(this),
					icon: Ext.Msg.WARNING,
					msg: lang['ne_zadan_harakter_zabolevaniya'],
					title: ERR_INVFIELDS_TIT
				});
				return false;
			}

			if ( getRegionNick() == 'ekb' ) {
				var sex_code = this.findById('EVPLDSAEF_PersonInformationFrame').getFieldValue('Sex_Code');
				var person_age = swGetPersonAge(this.findById('EVPLDSAEF_PersonInformationFrame').getFieldValue('Person_Birthday'), base_form.findField('EvnVizitPL_setDate').getValue());
				var person_age_month = swGetPersonAgeMonth(this.findById('EVPLDSAEF_PersonInformationFrame').getFieldValue('Person_Birthday'), base_form.findField('EvnVizitPL_setDate').getValue());
				var person_age_day = swGetPersonAgeDay(this.findById('EVPLDSAEF_PersonInformationFrame').getFieldValue('Person_Birthday'), base_form.findField('EvnVizitPL_setDate').getValue());

				if ( person_age == -1 || person_age_month == -1 || person_age_day == -1 ) {
					this.formStatus = 'edit';
					sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_opredelenii_vozrasta_patsienta']);
					return false;
				}
				if ( !sex_code || !(sex_code.toString().inlist([ '1', '2' ])) ) {
					this.formStatus = 'edit';
					sw.swMsg.alert(lang['oshibka'], lang['ne_ukazan_pol_patsienta']);
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
						msg: lang['vyibrannyiy_diagnoz_ne_sootvetstvuet_polu_patsienta'],
						title: lang['oshibka']
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
						msg: lang['vyibrannyiy_diagnoz_ne_sootvetstvuet_vozrastu_patsienta'],
						title: lang['oshibka']
					});
					return false;
				}
			} else if ( getRegionNick() == 'buryatiya' ) {
				if (pay_type_nick == 'oms' ) {
					var sex_code = this.findById('EVPLDSAEF_PersonInformationFrame').getFieldValue('Sex_Code');
					if (!sex_code || !(sex_code.toString().inlist(['1', '2']))) {
						this.formStatus = 'edit';
						sw.swMsg.alert(lang['oshibka'], lang['ne_ukazan_pol_patsienta']);
						return false;
					}
					// если Sex_id не соответсвует полу пациента то "Выбранный диагноз не соответствует полу"
					if (!Ext.isEmpty(record.get('Sex_Code')) && Number(record.get('Sex_Code')) != Number(sex_code)) {
						this.formStatus = 'edit';
						sw.swMsg.show({
							buttons: Ext.Msg.OK,
							fn: function (buttonId, text, obj) {
								base_form.findField('Diag_id').focus(true);
							}.createDelegate(this),
							icon: Ext.Msg.WARNING,
							msg: lang['vyibrannyiy_diagnoz_ne_sootvetstvuet_polu'],
							title: lang['oshibka']
						});
						return false;
					}
					if (!options.ignoreDiagFinance) {
						// если DiagFinance_IsOms = 0
						if (record.get('DiagFinance_IsOms') == 0) {
							this.formStatus = 'edit';
							sw.swMsg.show({
								buttons: Ext.Msg.YESNO,
								fn: function (buttonId, text, obj) {
									if (buttonId == 'yes') {
										options.ignoreDiagFinance = true;
										this.doSave(options);
									} else {
										base_form.findField('Diag_id').focus(true);
									}
								}.createDelegate(this),
								icon: Ext.MessageBox.QUESTION,
								msg: lang['vyibrannyiy_diagnoz_ne_oplachivaetsya_po_oms_prodoljit_sohranenie'],
								title: lang['prodoljit_sohranenie']
							});
							return false;
						}
					}
				}
			} else if ( getRegionNick() == 'astra' ) {
				if (pay_type_nick == 'oms' ) {
					if (!options.ignoreDiagFinance) {
						// если DiagFinance_IsOms = 0
						if (record.get('DiagFinance_IsOms') == 0) {
							this.formStatus = 'edit';
							sw.swMsg.show({
								buttons: Ext.Msg.YESNO,
								fn: function (buttonId, text, obj) {
									if (buttonId == 'yes') {
										options.ignoreDiagFinance = true;
										this.doSave(options);
									} else {
										base_form.findField('Diag_id').focus(true);
									}
								}.createDelegate(this),
								icon: Ext.MessageBox.QUESTION,
								msg: lang['vyibrannyiy_diagnoz_ne_oplachivaetsya_po_oms_prodoljit_sohranenie'],
								title: lang['prodoljit_sohranenie']
							});
							return false;
						}
					}
				}
			} else if ( getRegionNick() == 'kareliya' ) {
				if (!options.ignoreDiagFinance && pay_type_nick == 'oms') {
					var sex_code = this.findById('EVPLDSAEF_PersonInformationFrame').getFieldValue('Sex_Code');
					if ( !sex_code || !(sex_code.toString().inlist([ '1', '2' ])) ) {
						this.formStatus = 'edit';
						sw.swMsg.alert(lang['oshibka'], lang['ne_ukazan_pol_patsienta']);
						return false;
					}
					
					// если DiagFinance_IsOms = 1 и Sex_id = NULL то "Выбранный диагноз не оплачивается по ОМС, продолжить сохранение?" - пример N98.1
					if ( (Ext.isEmpty(record.get('DiagFinance_IsOms')) || record.get('DiagFinance_IsOms') == 0) && Ext.isEmpty(record.get('Sex_Code'))) {
						this.formStatus = 'edit';
						sw.swMsg.show({
							buttons: Ext.Msg.YESNO,
							fn: function(buttonId, text, obj) {
								if ( buttonId == 'yes' ) {
									options.ignoreDiagFinance = true;
									this.doSave(options);
								} else {
									base_form.findField('Diag_id').focus(true);
								}
							}.createDelegate(this),
							icon: Ext.MessageBox.QUESTION,
							msg: lang['vyibrannyiy_diagnoz_ne_oplachivaetsya_po_oms_prodoljit_sohranenie'],
							title: lang['prodoljit_sohranenie']
						});
						return false;
					}
					
					// если DiagFinance_IsOms = 1 и Sex_id = 1 то "Выбранный диагноз не оплачивается по ОМС для мужчин, продолжить сохранение?" - пример N70.1
					if ( (Ext.isEmpty(record.get('DiagFinance_IsOms')) || record.get('DiagFinance_IsOms') == 0) && Number(record.get('Sex_Code')) == Number(sex_code) && Number(record.get('Sex_Code')) == 1 ) {
						this.formStatus = 'edit';
						sw.swMsg.show({
							buttons: Ext.Msg.YESNO,
							fn: function(buttonId, text, obj) {
								if ( buttonId == 'yes' ) {
									options.ignoreDiagFinance = true;
									this.doSave(options);
								} else {
									base_form.findField('Diag_id').focus(true);
								}
							}.createDelegate(this),
							icon: Ext.MessageBox.QUESTION,
							msg: lang['vyibrannyiy_diagnoz_ne_oplachivaetsya_po_oms_dlya_mujchin_prodoljit_sohranenie'],
							title: lang['prodoljit_sohranenie']
						});
						return false;
					}
					
					// если DiagFinance_IsOms = 1 и Sex_id = 2 то "Выбранный диагноз не оплачивается по ОМС для женщин, продолжить сохранение?" - пример N51.8
					if ( (Ext.isEmpty(record.get('DiagFinance_IsOms')) || record.get('DiagFinance_IsOms') == 0) && Number(record.get('Sex_Code')) == Number(sex_code) && Number(record.get('Sex_Code')) == 2 ) {
						this.formStatus = 'edit';
						sw.swMsg.show({
							buttons: Ext.Msg.YESNO,
							fn: function(buttonId, text, obj) {
								if ( buttonId == 'yes' ) {
									options.ignoreDiagFinance = true;
									this.doSave(options);
								} else {
									base_form.findField('Diag_id').focus(true);
								}
							}.createDelegate(this),
							icon: Ext.MessageBox.QUESTION,
							msg: lang['vyibrannyiy_diagnoz_ne_oplachivaetsya_po_oms_dlya_jenschin_prodoljit_sohranenie'],
							title: lang['prodoljit_sohranenie']
						});
						return false;
					}

					// если DiagFinance_IsOms = 2, заполнен Sex_id и он не совпадает в Sex_id пациента, то "Выбранный диагноз не оплачивается по ОМС для женщин/мужчин, продолжить сохранение?" - пример O43.2
					if ( record.get('DiagFinance_IsOms') == 1 && !Ext.isEmpty(record.get('Sex_Code')) && Number(record.get('Sex_Code')) != Number(sex_code) ) {
						this.formStatus = 'edit';
						sw.swMsg.show({
							buttons: Ext.Msg.YESNO,
							fn: function(buttonId, text, obj) {
								if ( buttonId == 'yes' ) {
									options.ignoreDiagFinance = true;
									this.doSave(options);
								} else {
									base_form.findField('Diag_id').focus(true);
								}
							}.createDelegate(this),
							icon: Ext.MessageBox.QUESTION,
							msg: 'Выбранный диагноз не оплачивается по ОМС для ' + (sex_code == 1 ? 'мужчин' : 'женщин') + ', продолжить сохранение?',
							title: lang['prodoljit_sohranenie']
						});
						return false;
					}
				}
			} else {
				// https://redmine.swan.perm.ru/issues/4081
				// Проверка на финансирование по ОМС основного диагноза
				if ( isNotPerm == true && pay_type_nick == 'oms' ) {
					if ( lpu_section_profile_code.inlist([ '658', '684', '558', '584' ]) ) {
						if ( record.get('DiagFinance_IsHealthCenter') != 1 ) {
							sw.swMsg.alert(lang['oshibka'], lang['diagnoz_ne_oplachivaetsya_dlya_tsentrov_zdorovya'], function() {
								this.formStatus = 'edit';
								base_form.findField('Diag_id').markInvalid(lang['diagnoz_ne_oplachivaetsya_dlya_tsentrov_zdorovya']);
								base_form.findField('Diag_id').focus(true);
							}.createDelegate(this));
							return false;
						}
					}
					else if ( record.get('DiagFinance_IsOms') == 0 ) {
						sw.swMsg.alert(lang['oshibka'], lang['diagnoz_ne_oplachivaetsya_po_oms'], function() {
							this.formStatus = 'edit';
							base_form.findField('Diag_id').markInvalid(lang['diagnoz_ne_oplachivaetsya_po_oms']);
							base_form.findField('Diag_id').focus(true);
						}.createDelegate(this));
						return false;
					}
					else {
						var oms_spr_terr_code = this.findById('EVPLDSAEF_PersonInformationFrame').getFieldValue('OmsSprTerr_Code');
						var person_age = swGetPersonAge(this.findById('EVPLDSAEF_PersonInformationFrame').getFieldValue('Person_Birthday'), base_form.findField('EvnVizitPL_setDate').getValue());
						var sex_code = this.findById('EVPLDSAEF_PersonInformationFrame').getFieldValue('Sex_Code');

						if ( person_age == -1 ) {
							this.formStatus = 'edit';
							sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_opredelenii_vozrasta_patsienta']);
							return false;
						}

						if ( !sex_code || !(sex_code.toString().inlist([ '1', '2' ])) ) {
							this.formStatus = 'edit';
							sw.swMsg.alert(lang['oshibka'], lang['ne_ukazan_pol_patsienta']);
							return false;
						}

						if ( person_age >= 18 ) {
							if ( Number(record.get('PersonAgeGroup_Code')) == 2 ) {
								sw.swMsg.alert(lang['oshibka'], lang['diagnoz_ne_oplachivaetsya_dlya_vzroslyih'], function() {
									this.formStatus = 'edit';
									base_form.findField('Diag_id').markInvalid(lang['diagnoz_ne_oplachivaetsya_dlya_vzroslyih']);
									base_form.findField('Diag_id').focus(true);
								}.createDelegate(this));
								return false;
							}
						}
						else if ( Number(record.get('PersonAgeGroup_Code')) == 1 ) {
							sw.swMsg.alert(lang['oshibka'], lang['diagnoz_ne_oplachivaetsya_dlya_detey'], function() {
								this.formStatus = 'edit';
								base_form.findField('Diag_id').markInvalid(lang['diagnoz_ne_oplachivaetsya_dlya_detey']);
								base_form.findField('Diag_id').focus(true);
							}.createDelegate(this));
							return false;
						}

						if ( Number(sex_code) == 1 ) {
							if ( Number(record.get('Sex_Code')) == 2 ) {
								sw.swMsg.alert(lang['oshibka'], lang['diagnoz_ne_sootvetstvuet_polu_patsienta'], function() {
									this.formStatus = 'edit';
									base_form.findField('Diag_id').markInvalid(lang['diagnoz_ne_sootvetstvuet_polu_patsienta']);
									base_form.findField('Diag_id').focus(true);
								}.createDelegate(this));
								return false;
							}
						}
						else if ( Number(record.get('Sex_Code')) == 1 ) {
							sw.swMsg.alert(lang['oshibka'], lang['diagnoz_ne_sootvetstvuet_polu_patsienta'], function() {
								this.formStatus = 'edit';
								base_form.findField('Diag_id').markInvalid(lang['diagnoz_ne_sootvetstvuet_polu_patsienta']);
								base_form.findField('Diag_id').focus(true);
							}.createDelegate(this));
							return false;
						}

						if ( getRegionNick() == 'ufa' && oms_spr_terr_code != 61 && record.get('DiagFinance_IsAlien') == '0' ) {
							sw.swMsg.alert(lang['oshibka'], lang['diagnoz_ne_oplachivaetsya_dlya_patsientov_zastrahovannyih_ne_v_rb'], function() {
								this.formStatus = 'edit';
								base_form.findField('Diag_id').markInvalid(lang['diagnoz_ne_oplachivaetsya_dlya_patsientov_zastrahovannyih_ne_v_rb']);
								base_form.findField('Diag_id').focus(true);
							}.createDelegate(this));
							return false;
						}
					}
				}
			}
		}

		record = base_form.findField('ServiceType_id').getStore().getById(base_form.findField('ServiceType_id').getValue());
		if ( record ) {
			service_type_name = record.get('ServiceType_Name');

			if ( record.get('ServiceType_SysNick') == 'neotl' && base_form.findField('EvnVizitPL_setTime').getValue().toString().length == 0 ) {
				sw.swMsg.show({
					buttons: Ext.Msg.OK,
					fn: function() {
						this.formStatus = 'edit';
						base_form.findField('EvnVizitPL_setTime').focus(false);
					}.createDelegate(this),
					icon: Ext.Msg.WARNING,
					msg: lang['ne_ukazano_vremya_posescheniya'],
					title: ERR_INVFIELDS_TIT
				});
				return false;
			}
		}

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Подождите, идет сохранение посещения..." });
		loadMask.show();
		
		//params.AnamnezData = Ext.util.JSON.encode(this.EvnXmlPanel.getSavingData());
		//params.XmlTemplate_id = this.EvnXmlPanel.getXmlTemplate_id();
		
		params.FormType = this.FormType;
		params.action = this.action;
		params.from = this.from;
		params.TimetableGraf_id = (base_form.findField('TimetableGraf_id').getValue() > 0 ? base_form.findField('TimetableGraf_id').getValue() : this.TimetableGraf_id);

		// Гриды специфики		
		params.MorbusHepatitisDiag = this.collectGridData('MorbusHepatitisDiag');		
		params.MorbusHepatitisDiagSop = this.collectGridData('MorbusHepatitisDiagSop');		
		params.MorbusHepatitisLabConfirm = this.collectGridData('MorbusHepatitisLabConfirm');		
		params.MorbusHepatitisFuncConfirm = this.collectGridData('MorbusHepatitisFuncConfirm');		
		params.MorbusHepatitisCure = this.collectGridData('MorbusHepatitisCure');		
		params.MorbusHepatitisCureEffMonitoring = this.collectGridData('MorbusHepatitisCureEffMonitoring');		
		params.MorbusHepatitisVaccination = this.collectGridData('MorbusHepatitisVaccination');		
		params.MorbusHepatitisQueue = this.collectGridData('MorbusHepatitisQueue');	

		if ( base_form.findField('LpuSection_id').disabled ) {
			params.LpuSection_id = base_form.findField('LpuSection_id').getValue();
		}

		if ( base_form.findField('MedStaffFact_id').disabled ) {
			params.MedStaffFact_id = base_form.findField('MedStaffFact_id').getValue();
		}

		if ( base_form.findField('PayType_id').disabled ) {
			params.PayType_id = base_form.findField('PayType_id').getValue();
		}

		if ( base_form.findField('EvnVizitPL_Uet').disabled ) {
			params.EvnVizitPL_Uet = base_form.findField('EvnVizitPL_Uet').getValue();
		}

		if ( base_form.findField('EvnVizitPL_UetOMS').disabled ) {
			params.EvnVizitPL_UetOMS = base_form.findField('EvnVizitPL_UetOMS').getValue();
		}

		base_form.submit({
			failure: function(result_form, action) {
				this.formStatus = 'edit';
				loadMask.hide();

				if ( action.result ) {
					if (action.result.Alert_Msg) {
                        sw.swMsg.show({
                            buttons: Ext.Msg.YESNO,
                            fn: function(buttonId, text, obj) {
                                this.formStatus = 'edit';

                                if ( buttonId == 'yes' ) {
                                    options.ignoreDayProfileDuplicateVizit = true;
                                    this.doSave(options);
                                }
                                else {
                                    base_form.findField('EvnVizitPL_setDate').focus(true);
                                }
                            }.createDelegate(this),
                            icon: Ext.MessageBox.QUESTION,
                            msg: action.result.Alert_Msg,
                            title: lang['prodoljit_sohranenie']
                        });
                    } else {
                        if ( action.result.Error_Msg ) {
                            sw.swMsg.alert(lang['oshibka'], action.result.Error_Msg);
                        }
                        else {
                            sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_1]']);
                        }
                    };
                    /*
                     */
				}
			}.createDelegate(this),
			params: params,
			success: function(result_form, action) {
				this.formStatus = 'edit';
				loadMask.hide();

				if ( action.result ) {
					if ( action.result.EvnVizitPL_id ) {
						base_form.findField('EvnVizitPL_id').setValue(action.result.EvnVizitPL_id);
                        this.EvnXmlPanel.setBaseParams({
                            userMedStaffFact: this.userMedStaffFact,
                            Server_id: base_form.findField('Server_id').getValue(),
                            Evn_id: base_form.findField('EvnVizitPL_id').getValue()
                        });
                        this.EvnXmlPanel.onEvnSave();

						if ( action.result.EvnUslugaCommon_id ) {
							base_form.findField('EvnUslugaCommon_id').setValue(action.result.EvnUslugaCommon_id);
						}

						if ( options && typeof options.openChildWindow == 'function' && this.action == 'add' ) {
							if ( action.result.Alert_Msg && action.result.Alert_Msg.toString().length > 0 ) {
								sw.swMsg.alert(lang['preduprejdenie'], action.result.Alert_Msg, function() {
									options.openChildWindow();
								});
							}
							else {
								options.openChildWindow();
							}
						}
						else {
							var data = new Object();

							var lpu_section_id = base_form.findField('LpuSection_id').getValue();
							var lpu_section_name = '';
							var lpu_unit_set_code = 0;
							var pay_type_name = '';
							var usluga_complex_code = '';
							var usluga_complex_name = '';
							var vizit_type_name = '';

							record = base_form.findField('LpuSection_id').getStore().getById(lpu_section_id);
							if ( record ) {
								lpu_section_name = record.get('LpuSection_Code') + '. ' + record.get('LpuSection_Name');
								lpu_unit_set_code = record.get('LpuUnitSet_Code');
							}

							record = base_form.findField('PayType_id').getStore().getById(base_form.findField('PayType_id').getValue());
							if ( record ) {
								pay_type_name = record.get('PayType_Name');
							}

							record = base_form.findField('VizitType_id').getStore().getById(base_form.findField('VizitType_id').getValue());
							if ( record ) {
								vizit_type_name = record.get('VizitType_Name');
							}

							/*var mh_reg = new RegExp("^(A0[0-9]|A2[0-8]|A[3-4]|A7[5-9]|A[8-9]|B0[0-9]|B1[5-9]|B2|B3[0-4]|B[5-7]|B8[0-3]|B9[0-6]|B97.[0-8]|B99)");
							if(mh_reg.test(diag_code)) {
								requestEvnInfectNotify({
									EvnInfectNotify_pid: base_form.findField('EvnVizitPL_id').getValue()
									,Diag_Name: diag_code + ' ' + diag_name
									//,Diag_id: base_form.findField('Diag_id').getValue()
									,Server_id: base_form.findField('Server_id').getValue()
									,PersonEvn_id: base_form.findField('PersonEvn_id').getValue()
									,MedPersonal_id: base_form.findField('MedPersonal_id').getValue()
									,EvnInfectNotify_FirstTreatDate: base_form.findField('EvnVizitPL_setDate').getValue()
									,EvnInfectNotify_SetDiagDate: base_form.findField('EvnVizitPL_setDate').getValue()
								});
							}*/

							if ( getGlobalOptions().region && getGlobalOptions().region.nick.inlist([ 'ufa' ]) ) {
								var usluga_complex_id = base_form.findField('UslugaComplex_uid').getValue();
								var index = base_form.findField('UslugaComplex_uid').getStore().findBy(function(rec) {
									return (rec.get('UslugaComplex_id') == usluga_complex_id);
								});

								if ( index >= 0 ) {
									usluga_complex_code = base_form.findField('UslugaComplex_uid').getStore().getAt(index).get('UslugaComplex_Code');
									usluga_complex_name = base_form.findField('UslugaComplex_uid').getStore().getAt(index).get('UslugaComplex_Code') + '. ' + base_form.findField('UslugaComplex_uid').getStore().getAt(index).get('UslugaComplex_Name');
								}
							}
							
							data.evnVizitPLData = [{
								'accessType': 'edit',
								'EvnVizitPL_id': base_form.findField('EvnVizitPL_id').getValue(),
								'EvnPL_id': base_form.findField('EvnPL_id').getValue(),
								'Person_id': base_form.findField('Person_id').getValue(),
								'PersonEvn_id': base_form.findField('PersonEvn_id').getValue(),
								'Server_id': base_form.findField('Server_id').getValue(),
								'Diag_id': base_form.findField('Diag_id').getValue(),
								'EvnVizitPL_setDate': base_form.findField('EvnVizitPL_setDate').getValue(),
								'Diag_Code': diag_code,
								'Diag_Name': diag_name,
								'MedStaffFact_id': med_staff_fact_id,
								'LpuSection_id': lpu_section_id,
								'MedPersonal_id': med_personal_id,
								'LpuSection_Name': lpu_section_name,
								'LpuUnitSet_Code': lpu_unit_set_code,
								'MedPersonal_Fio': med_personal_fio,
								'ServiceType_Name': service_type_name,
								'VizitType_Name': vizit_type_name,
								'PayType_Name': pay_type_name,
								'UslugaComplex_Code': usluga_complex_code,
								'UslugaComplex_Name': usluga_complex_name
							}];

							// Для ВОВ еще одно поле 
							if ( this.FormType == 'EvnVizitPLWow' ) {
								data.evnVizitPLData[0].DispWOWSpec_id = base_form.findField('DispWowSpec_id').getValue();
								data.evnVizitPLData[0].DispWOWSpec_Name = base_form.findField('DispWowSpec_id').getRawValue();
							}

							if ( action.result.Alert_Msg && action.result.Alert_Msg.toString().length > 0 ) {
								sw.swMsg.alert(lang['preduprejdenie'], action.result.Alert_Msg, function() {
									this.callback(data);
									this.hide();
								}.createDelegate(this) );
							}
							else {
								this.callback(data);
								this.hide();
							}
						}
					}
					else {
						if ( action.result.Error_Msg ) {
							sw.swMsg.alert(lang['oshibka'], action.result.Error_Msg);
						}
						else {
							sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_3]']);
						}
					}
				}
				else {
					sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_2]']);
				}
			}.createDelegate(this)
		});
	},
	draggable: true,
	enableEdit: function(enable) {
		var base_form = this.findById('EvnVizitPLDispSomeAdultEditForm').getForm();
		var form_fields = new Array(
			'EvnVizitPL_setDate',
			'EvnVizitPL_setTime',
			'LpuSection_id',
			'MedStaffFact_id',
			'MedStaffFact_sid',
			'ServiceType_id',
			'VizitClass_id',
			'VizitType_id',
			'PayType_id',
			'EvnVizitPL_Time',
			'ProfGoal_id',
			'Diag_agid',
			'Diag_id',
			'DeseaseType_id'
		);
		var i = 0;

		if ( getGlobalOptions().region && getGlobalOptions().region.nick.inlist([ 'pskov', 'ufa' ]) ) {
			form_fields.push('UslugaComplex_uid');
		}

		if ( this.FormType == 'EvnVizitPLWow' ) {
			form_fields.push('DispWowSpec_id');
		}

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
        this.EvnXmlPanel.setReadOnly(!enable);
	},
	firstRun: true,
	getListDispWowSpec: function (gridpanel, value)
	{
		var list = Array();
		if (gridpanel.getCount()>0)
		{
			gridpanel.getGrid().getStore().each(function(rec) 
			{
				if ((rec.get('DispWOWSpec_id')!=value) && (rec.get('DispWOWSpec_id')!=10))
				{
					list.push(rec.get('DispWOWSpec_id'));
				}
			});
		}
		return list;
	},
	EvnUslugaGridIsModified: false,
	formStatus: 'edit',
	height: 550,
	id: 'EvnVizitPLDispSomeAdultEditWindow',
	initComponent: function() {
		var current_window = this;
		
		this.MorbusHepatitisSpec = new sw.Promed.Panel({
			autoHeight: true,
			style: 'margin-bottom: 0.5em;',
			bodyStyle:'background:#DFE8F6;padding:5px;',
			border: true,
			collapsible: true,
			region: 'north',
			layout: 'form',
			title: lang['spetsifika'],
			items: [{
					name: 'HepatitisEpidemicMedHistoryType_id',
					comboSubject: 'HepatitisEpidemicMedHistoryType',
					fieldLabel: lang['epidanamnez'],
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
							this.deleteGridSelectedRecord('MHW_MorbusHepatitisDiag', 'MorbusHepatitisDiag_id');
						}.createDelegate(this)},
						{name: 'action_print'}
					],
					autoExpandColumn: 'autoexpand',
					autoExpandMin: 150,
					autoLoadData: false,
					border: false,
					dataUrl: '',
					id: 'MHW_MorbusHepatitisDiag',
					paging: false,
					style: 'margin-bottom: 10px',
					stringfields: [
						{name: 'MorbusHepatitisDiag_id', type: 'int', header: 'ID', key: true},
						{name: 'RecordStatus_Code', type: 'int', hidden: true},
						{name: 'MorbusHepatitisDiag_setDT', type: 'date', dateFormat: 'd.m.Y', header: lang['data'], width: 120},
						{name: 'MedPersonal_id', type: 'string', hidden: true},
						{name: 'MedPersonal_Name', type: 'string', header: lang['vrach'], width: 320, id: 'autoexpand'},
						{name: 'HepatitisDiagType_id', type: 'string', hidden: true},
						{name: 'HepatitisDiagType_Name', type: 'string', header: lang['diagnoz'], width: 240},
						{name: 'HepatitisDiagActiveType_id', type: 'string', hidden: true},
						{name: 'HepatitisDiagActiveType_Name', type: 'string', header: lang['aktivnost'], width: 240},
						{name: 'HepatitisFibrosisType_id', type: 'string', hidden: true},
						{name: 'HepatitisFibrosisType_Name', type: 'string', header: lang['fibroz'], width: 240}
					],
					title: lang['diagnoz'],
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
							this.deleteGridSelectedRecord('MHW_MorbusHepatitisDiagSop', 'MorbusHepatitisDiagSop_id');
						}.createDelegate(this)},
						{name: 'action_print'}
					],
					autoExpandColumn: 'autoexpand',
					autoExpandMin: 150,
					autoLoadData: false,
					border: false,
					dataUrl: '',
					id: 'MHW_MorbusHepatitisDiagSop',
					paging: false,
					style: 'margin-bottom: 10px',
					stringfields: [
						{name: 'MorbusHepatitisDiagSop_id', type: 'int', header: 'ID', key: true},
						{name: 'RecordStatus_Code', type: 'int', hidden: true},
						{name: 'MorbusHepatitisDiagSop_setDT', type: 'date', dateFormat: 'd.m.Y', header: lang['data'], width: 120},
						{name: 'Diag_id', type: 'string', hidden: true},
						{name: 'Diag_Name', type: 'string', header: lang['diagnoz'], width: 420, id: 'autoexpand'}
					],
					title: lang['soputstvuyuschie_diagnozyi'],
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
							this.deleteGridSelectedRecord('MHW_MorbusHepatitisLabConfirm', 'MorbusHepatitisLabConfirm_id');
						}.createDelegate(this)},
						{name: 'action_print'}
					],
					autoExpandColumn: 'autoexpand',
					autoExpandMin: 150,
					autoLoadData: false,
					border: false,
					dataUrl: '',
					id: 'MHW_MorbusHepatitisLabConfirm',
					paging: false,
					style: 'margin-bottom: 10px',
					stringfields: [
						{name: 'MorbusHepatitisLabConfirm_id', type: 'int', header: 'ID', key: true},
						{name: 'RecordStatus_Code', type: 'int', hidden: true},
						{name: 'MorbusHepatitisLabConfirm_setDT', type: 'date', dateFormat: 'd.m.Y', header: lang['data'], width: 120},
						{name: 'HepatitisLabConfirmType_id', type: 'string', hidden: true},
						{name: 'HepatitisLabConfirmType_Name', type: 'string', header: lang['tip'], width: 240},
						{name: 'MorbusHepatitisLabConfirm_Result', type: 'string', header: lang['rezultat'], width: 240}
					],
					title: lang['laboratornyie_podtverjdeniya'],
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
							this.deleteGridSelectedRecord('MHW_MorbusHepatitisFuncConfirm', 'MorbusHepatitisFuncConfirm_id');
						}.createDelegate(this)},
						{name: 'action_print'}
					],
					autoExpandColumn: 'autoexpand',
					autoExpandMin: 150,
					autoLoadData: false,
					border: false,
					dataUrl: '',
					id: 'MHW_MorbusHepatitisFuncConfirm',
					paging: false,
					style: 'margin-bottom: 10px',
					stringfields: [
						{name: 'MorbusHepatitisFuncConfirm_id', type: 'int', header: 'ID', key: true},
						{name: 'RecordStatus_Code', type: 'int', hidden: true},
						{name: 'MorbusHepatitisFuncConfirm_setDT', type: 'date', dateFormat: 'd.m.Y', header: lang['data'], width: 120},
						{name: 'HepatitisFuncConfirmType_id', type: 'string', hidden: true},
						{name: 'HepatitisFuncConfirmType_Name', type: 'string', header: lang['tip'], width: 240},
						{name: 'MorbusHepatitisFuncConfirm_Result', type: 'string', header: lang['rezultat'], width: 240}
					],
					title: lang['instrumentalnyie_podtverjdeniya'],
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
							this.deleteGridSelectedRecord('MHW_MorbusHepatitisCure', 'MorbusHepatitisCure_id');
						}.createDelegate(this)},
						{name: 'action_print'}
					],
					autoExpandColumn: 'autoexpand',
					autoExpandMin: 150,
					autoLoadData: false,
					border: false,
					dataUrl: '',
					id: 'MHW_MorbusHepatitisCure',
					paging: false,
					style: 'margin-bottom: 10px',
					stringfields: [
						{name: 'MorbusHepatitisCure_id', type: 'int', header: 'ID', key: true},
						{name: 'RecordStatus_Code', type: 'int', hidden: true},
						{name: 'MorbusHepatitisCure_Year', type: 'string', header: lang['god_lecheniya'], width: 120},
						{name: 'MorbusHepatitisCure_Drug', type: 'string', header: lang['preparat'], width: 240},
						{name: 'HepatitisResultClass_id', type: 'int', hidden: true},
						{name: 'HepatitisResultClass_Name', type: 'string', header: lang['rezultat'], width: 240},
						{name: 'HepatitisSideEffectType_id', type: 'int', hidden: true},
						{name: 'HepatitisSideEffectType_Name', type: 'string', header: lang['pobochnyiy_effekt'], width: 240}
					],
					title: lang['lechenie'],
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
							this.deleteGridSelectedRecord('MHW_MorbusHepatitisCureEffMonitoring', 'MorbusHepatitisCureEffMonitoring_id');
						}.createDelegate(this)},
						{name: 'action_print'}
					],
					autoExpandColumn: 'autoexpand',
					autoExpandMin: 150,
					autoLoadData: false,
					border: false,
					dataUrl: '',
					id: 'MHW_MorbusHepatitisCureEffMonitoring',
					paging: false,
					style: 'margin-bottom: 10px',
					stringfields: [
						{name: 'MorbusHepatitisCureEffMonitoring_id', type: 'int', header: 'ID', key: true},
						{name: 'RecordStatus_Code', type: 'int', hidden: true},
						{name: 'HepatitisCurePeriodType_id', type: 'string', hidden: true},
						{name: 'HepatitisCurePeriodType_Name', type: 'string', header: lang['srok_lecheniya'], width: 320},
						{name: 'HepatitisQualAnalysisType_id', type: 'string', hidden: true},
						{name: 'HepatitisQualAnalysisType_Name', type: 'string', header: lang['kachestvennyiy_analiz'], width: 320},
						{name: 'MorbusHepatitisCureEffMonitoring_VirusStress', type: 'string', header: lang['virusnaya_nagruzka'], width: 120}
					],
					title: lang['monitoring_effektivnosti_lecheniya'],
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
							this.deleteGridSelectedRecord('MHW_MorbusHepatitisVaccination', 'MorbusHepatitisVaccination_id');
						}.createDelegate(this)},
						{name: 'action_print'}
					],
					autoExpandColumn: 'autoexpand',
					autoExpandMin: 150,
					autoLoadData: false,
					border: false,
					dataUrl: '',
					id: 'MHW_MorbusHepatitisVaccination',
					paging: false,
					style: 'margin-bottom: 10px',
					stringfields: [
						{name: 'MorbusHepatitisVaccination_id', type: 'int', header: 'ID', key: true},
						{name: 'RecordStatus_Code', type: 'int', hidden: true},
						{name: 'MorbusHepatitisVaccination_setDT', type: 'date', dateFormat: 'd.m.Y', header: lang['data'], width: 120},
						{name: 'MorbusHepatitisVaccination_Vaccine', type: 'string', header: lang['nazvanie_vaktsinyi'], width: 320}
					],
					title: lang['vaktsinatsiya'],
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
							this.deleteGridSelectedRecord('MHW_MorbusHepatitisQueue', 'MorbusHepatitisQueue_id');
						}.createDelegate(this)},
						{name: 'action_print'}
					],
					autoExpandColumn: 'autoexpand',
					autoExpandMin: 150,
					autoLoadData: false,
					border: false,
					dataUrl: '',
					id: 'MHW_MorbusHepatitisQueue',
					paging: false,
					style: 'margin-bottom: 10px',
					stringfields: [
						{name: 'MorbusHepatitisQueue_id', type: 'int', header: 'ID', key: true},
						{name: 'RecordStatus_Code', type: 'int', hidden: true},
						{name: 'HepatitisQueueType_id', type: 'string', hidden: true},
						{name: 'HepatitisQueueType_Name', type: 'string', header: lang['tip_ocheredi'], width: 240},
						{name: 'MorbusHepatitisQueue_Num', type: 'string', header: lang['nomer_v_ocheredi'], width: 120},
						{name: 'MorbusHepatitisQueue_IsCure', type: 'string', hidden: true},
						{name: 'MorbusHepatitisQueue_IsCure_Name', type: 'string', header: lang['lechenie_provedeno'], width: 120}
					],
					title: lang['ochered'],
					toolbar: true
				})]
		});
		
		this.DirectionInfoData = new Ext.DataView({
			border: false,
			frame: false,
			itemSelector: 'div',
			layout: 'fit',
			//region: 'center',
			getFieldValue: function(field) 
			{
				var result = '';
				if (this.getStore().getAt(0))
					result = this.getStore().getAt(0).get(field);
				return result;
			},
			store: new Ext.data.JsonStore({
				autoLoad: false,
				fields: [
					{ name: 'EvnDirection_id'}, 
					{ name: 'TimetableGraf_id' },
					{ name: 'EvnDirection_Num' }, // Номер
					{ name: 'EvnDirection_setDate', dateFormat: 'd.m.Y', type: 'date' }, // Дата 
					{ name: 'EvnDirection_getDate', dateFormat: 'd.m.Y', type: 'date' }, // Дата 
					{ name: 'DirType_id' },
					{ name: 'DirType_Name' }, // Тип направления 
					{ name: 'Lpu_did' },
					{ name: 'Lpu_Nick' }, // ЛПУ направления 
					{ name: 'LpuSectionProfile_id' },
					{ name: 'LpuSectionProfile_Code' }, // Профиль
					{ name: 'LpuSectionProfile_Name' }, // Профиль
					{ name: 'EvnDirection_setDateTime'}, // Время записи
					{ name: 'Diag_id' },
					{ name: 'Diag_Code' }, 
					{ name: 'Diag_Name' }, 
					{ name: 'EvnDirection_Descr' }, // Описание 
					{ name: 'MedStaffFact_id' }, 
					{ name: 'MedStaffFact_FIO' }, // Врач 
					{ name: 'MedStaffFact_zid' }, 
					{ name: 'MedStaffFact_ZFIO' } // Зав.отделением
				],
				url: '/?c=EvnDirection&m=getDirectionIf'// '/?c=EvnDirection&m=loadEvnDirectionEditForm'
			}),
			tpl: new Ext.XTemplate(
				'<tpl for=".">',
				'<div>Направление №<font style="color: blue; font-weight: bold;">{EvnDirection_Num}</font>, выписано: <font style="color: blue;">{[Ext.util.Format.date(values.EvnDirection_setDate, "d.m.Y")]}</font>, тип направления: <font style="color: blue;">{DirType_Name}</font> </div>',
				'<div>ЛПУ направления: <font style="color: blue;">{Lpu_Nick}</font>, по профилю: <font style="color: blue;">{LpuSectionProfile_Code}.{LpuSectionProfile_Name}</font> ',
				'<div>Диагноз: <font style="color: blue;">{Diag_Code}.{Diag_Name}</font></div>',
				'<div>Врач: <font style="color: blue;">{MedStaffFact_FIO}</font>,  Зав.отделением: <font style="color: blue;">{MedStaffFact_ZFIO}</font></div>',
				'<div>Время записи: <font style="color: blue;">{EvnDirection_setDateTime}</font>',
				'</tpl>'
			)
		});
				
		this.DirectionInfoPanel = new sw.Promed.Panel({
			autoHeight: true,
			border: true,
			frame: true,
			collapsible: true,
			id: 'EVPLDSAEF_DirectInfoPanel',
			isLoaded: false,
			layout: 'form',
			listeners: {
				'expand': function(panel) {
					if ( panel.isLoaded === false ) {
						var form = Ext.getCmp('EvnVizitPLDispSomeAdultEditWindow');
						panel.isLoaded = true;
					}
					panel.doLayout();
				}.createDelegate(this)
			},
			style: 'margin-bottom: 0.5em;',
			title: lang['1_po_napravleniyu'],
			items: [this.DirectionInfoData]
		});

        this.EvnXmlPanel = new sw.Promed.EvnXmlPanel({
            autoHeight: true,
            bodyStyle: 'padding-top: 0.5em;',
            border: false,
            collapsible: true,
            id: 'EVPLDSAEF_AnamnezPanel',
            layout: 'form',
            style: 'margin-bottom: 0.5em;',
            title: lang['2_osmotr'],
            isLoaded: false,
            ownerWin: this,
            options: {
                XmlType_id: sw.Promed.EvnXml.EVN_VIZIT_PROTOCOL_TYPE_ID, // только протоколы осмотра
                EvnClass_id: 11 // документы и шаблоны только категории посещение поликлиники
            },
            // определяем метод, который должен создать посещение перед созданием документа с помощью указанного метода
            onBeforeCreate: function (panel, method, params) {
                if (!panel || !method || typeof panel[method] != 'function') {
                    return false;
                }
                var base_form = this.findById('EvnVizitPLDispSomeAdultEditForm').getForm();
                var evn_id_field = base_form.findField('EvnVizitPL_id');
                var evn_id = evn_id_field.getValue();
                if (evn_id && evn_id > 0) {
                    // посещение было создано ранее
                    // все базовые параметры уже должно быть установлены
                    panel[method](params);
                } else {
                    this.doSave({
                        openChildWindow: function() {
                            panel.setBaseParams({
                                userMedStaffFact: this.userMedStaffFact,
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

		var form = this;
		// Формирование списка экшенов левой панели
		var configActions = 
		{
			action_Direction: 
			{
				nn: 'action_Direction',
				tooltip: lang['zapisat_patsienta_k_vrachu'],
				text: lang['napravit_patsienta'],
				iconCls : 'eph-record16',
				disabled: false, 
				handler: function() 
				{
					var base_form = form.findById('EvnVizitPLDispSomeAdultEditForm').getForm();
					var pif = form.findById('EVPLDSAEF_PersonInformationFrame');
					
					var openMPRecordWindow = function(base_form,pif){
						var params = 
						{
							EvnDirection_pid: base_form.findField('EvnVizitPL_id').getValue(),
							PersonEvn_id: base_form.findField('PersonEvn_id').getValue(),
							Server_id: base_form.findField('Server_id').getValue(),
							UserMedStaffFact_id: base_form.findField('MedStaffFact_id').getValue(),
							Person_id: pif.getFieldValue('Person_id'),
							Person_Birthday: pif.getFieldValue('Person_Birthday'),
							Person_Firname: pif.getFieldValue('Person_Firname'),
							Person_Secname: pif.getFieldValue('Person_Secname'),
							Person_Surname: pif.getFieldValue('Person_Surname'),
							formMode:'vizit_PL'
						}
						if ( getWnd('swMPRecordWindow').isVisible() ) {
							sw.swMsg.show({
								buttons: Ext.Msg.OK,
								fn: Ext.emptyFn,
								icon: Ext.Msg.WARNING,
								msg: lang['okno_uje_otkryito'],
								title: ERR_WND_TIT
							});
							return false;
						}
						getWnd('swMPRecordWindow').show(params);
					};
					
					//проверяем жив ли человек
					if (pif && pif.getFieldValue('Person_deadDT') != '') {
						sw.swMsg.alert(lang['oshibka'], lang['zapis_nevozmojna_v_svyazi_so_smertyu_patsienta']);
					} else {
						if ( base_form.findField('EvnVizitPL_id').getValue() < 1) {
							sw.swMsg.show(
							{
								icon: Ext.MessageBox.QUESTION,
								msg: lang['poseschenie_ne_sohraneno_poetomu_elektronnoe_napravlenie_mojet_byit_vyipisano_s_oshibkami_vyi_hotite_sohranit_poseschenie_i_zapisat_patsienta_bez_oshibok'],
								title: lang['vopros'],
								buttons: Ext.Msg.YESNO,
								fn: function(buttonId, text, obj)
								{
									//если пользователь отказал, то вернуть в форму посещения
									if ('yes' != buttonId)
									{
										return false;
									}
									// если пользователь подтвердил, сохранить и перейти к требуемой форме,
									form.doSave({
										//ignoreEvnUslugaCountCheck: true,
										openChildWindow: function() {
											openMPRecordWindow(base_form,pif);
										}.createDelegate(this)
									});
								}
							});
						} else {
							openMPRecordWindow(base_form,pif);
						}
					}
				}
			},
			action_JournalDirections: 
			{
				nn: 'action_JournalDirections',
				tooltip: lang['otkryit_jurnal_napravleniy'],
				text: lang['jurnal_napravleniy'],
				iconCls : 'mp-directions32',
				disabled: false, 
				handler: function() 
				{
					var base_form = form.findById('EvnVizitPLDispSomeAdultEditForm').getForm();
					var pif = form.findById('EVPLDSAEF_PersonInformationFrame');
					var openJournalDirectionsWindow = function(base_form,pif){
						var params = 
						{
							Form_data: {
								EvnDirection_pid: base_form.findField('EvnVizitPL_id').getValue(),
								Person_id: base_form.findField('Person_id').getValue(),
								PersonEvn_id: base_form.findField('PersonEvn_id').getValue(),
								Server_id: base_form.findField('Server_id').getValue(),
								UserMedStaffFact_id: base_form.findField('MedStaffFact_id').getValue()
							},
							PersonInformationFrame_data: {
								person_id: pif.getFieldValue('Person_id'),
								person_birthday: pif.getFieldValue('Person_Birthday'),
								person_firname: pif.getFieldValue('Person_Firname'),
								person_secname: pif.getFieldValue('Person_Secname'),
								person_surname: pif.getFieldValue('Person_Surname')
							}
						}
						if ( getWnd('swJournalDirectionsWindow').isVisible() ) {
							sw.swMsg.show({
								buttons: Ext.Msg.OK,
								fn: Ext.emptyFn,
								icon: Ext.Msg.WARNING,
								msg: lang['okno_uje_otkryito'],
								title: ERR_WND_TIT
							});
							return false;
						}
						getWnd('swJournalDirectionsWindow').show(params);
					};
					if ( base_form.findField('EvnVizitPL_id').getValue() < 1)
					{
						sw.swMsg.show(
						{
							icon: Ext.MessageBox.QUESTION,
							msg: lang['poseschenie_ne_sohraneno_poetomu_elektronnyie_napravleniya_mogut_byit_vyipisanyi_s_oshibkami_iz_jurnala_napravleniy_vyi_hotite_sohranit_poseschenie_i_vyipisyivat_napravleniya_patsienta_bez_oshibok'],
							title: lang['vopros'],
							buttons: Ext.Msg.YESNO,
							fn: function(buttonId, text, obj)
							{
								//если пользователь отказал, то вернуть в форму посещения
								if ('yes' != buttonId)
								{
									return false;
								}
								// если пользователь подтвердил, сохранить и перейти к требуемой форме,
								form.doSave({
									//ignoreEvnUslugaCountCheck: true,
									openChildWindow: function() {
										openJournalDirectionsWindow(base_form,pif);
									}.createDelegate(this)
								});
							}
						});
					}
					else
					{
						openJournalDirectionsWindow(base_form,pif);
					}
				}
			},
			action_Disp: 
			{
				nn: 'action_Disp',
				tooltip: lang['otkryit_istoriyu_dispanserizatsii_patsienta'],
				text: lang['dispanserizatsiya'],
				iconCls : 'mp-disp32',
				disabled: false, 
				handler: function() 
				{
					var base_form = form.findById('EvnVizitPLDispSomeAdultEditForm').getForm();
					var pif = form.findById('EVPLDSAEF_PersonInformationFrame');
					var params = 
					{
						PersonEvn_id: base_form.findField('PersonEvn_id').getValue(),
						Server_id: base_form.findField('Server_id').getValue(),
						Person_id: pif.getFieldValue('Person_id'),
						Person_Birthday: pif.getFieldValue('Person_Birthday'),
						Person_Firname: pif.getFieldValue('Person_Firname'),
						Person_Secname: pif.getFieldValue('Person_Secname'),
						Person_Surname: pif.getFieldValue('Person_Surname')
					}
					if ( getWnd('swPersonDispHistoryWindow').isVisible() ) {
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
				}
			},
			action_TemplateFavorites: 
			{
				nn: 'action_TemplateFavorites',
				tooltip: lang['nedavnie_shablonyi_osmotrov'],
				text: lang['nedavnie_shablonyi'],
				id: 'EVPLDSAEF_TemplateFavorites_btn',
				iconCls : 'template-fav32',
				disabled: false, 
				handler: function() 
				{
					current_window.getTemplateFavorites();
				}
			}
		}
		// Копируем все действия для создания панели кнопок
		form.PanelActions = {};
		for(var key in configActions)
		{
			var iconCls = configActions[key].iconCls.replace(/16/g, '32');
			var z = Ext.applyIf({cls: 'x-btn-large', iconCls: iconCls, text: ''}, configActions[key]);
			this.PanelActions[key] = new Ext.Action(z);
		}
		var actions_list = ['action_Direction','action_JournalDirections','action_Disp','action_TemplateFavorites'];
		// Создание кнопок для панели
		form.BtnActions = new Array();
		var i = 0;
		for( key in form.PanelActions)
		{
			if (key.inlist(actions_list))
			{
				form.BtnActions.push(new Ext.Button(form.PanelActions[key]));
				i++;
			}
		}
		this.leftMenu = new Ext.Panel(
		{
			region: 'west',
			border: false,
			layout:'form',
			layoutConfig:
			{
				titleCollapse: true,
				animate: true,
				activeOnTop: false
			},
			items: form.BtnActions
		});
		this.leftPanel =
		{
			animCollapse: false,
			bodyStyle: 'padding: 5px',
			width: 60,
			minSize: 60,
			maxSize: 120,
			id: 'EVPLDSAEF_LeftPanel',
			region: 'west',
			floatable: false,
			collapsible: true,
			layoutConfig:
			{
				titleCollapse: true,
				animate: true,
				activeOnTop: false
			},
			listeners:
			{
				collapse: function()
				{
					return;
				},
				resize: function (p,nW, nH, oW, oH)
				{
					return;
				}
			},
			border: true,
			title: ' ',
			split: true,
			items: [this.leftMenu]
		};
		
		Ext.apply(this, {
			layout: 'border',
			buttons: [{
				handler: function() {
					this.doSave({
						ignoreEvnUslugaCountCheck: false
					});
				}.createDelegate(this),
				iconCls: 'save16',
				onShiftTabAction: function () {
					var base_form = this.findById('EvnVizitPLDispSomeAdultEditForm').getForm();

					if ( !this.findById('EVPLDSAEF_EvnReceptPanel').collapsed && this.findById('EVPLDSAEF_EvnReceptGrid').getStore().getCount() > 0 ) {
						this.findById('EVPLDSAEF_EvnReceptGrid').getView().focusRow(0);
						this.findById('EVPLDSAEF_EvnReceptGrid').getSelectionModel().selectFirstRow();
					}
					else if ( !this.findById('EVPLDSAEF_EvnUslugaPanel').hidden && !this.findById('EVPLDSAEF_EvnUslugaPanel').collapsed && this.findById('EVPLDSAEF_EvnUslugaGrid').getStore().getCount() > 0 ) {
						this.findById('EVPLDSAEF_EvnUslugaGrid').getView().focusRow(0);
						this.findById('EVPLDSAEF_EvnUslugaGrid').getSelectionModel().selectFirstRow();
					}
					else if ( !this.findById('EVPLDSAEF_EvnDiagPLPanel').collapsed && this.findById('EVPLDSAEF_EvnDiagPLGrid').getStore().getCount() > 0 ) {
						this.findById('EVPLDSAEF_EvnDiagPLGrid').getView().focusRow(0);
						this.findById('EVPLDSAEF_EvnDiagPLGrid').getSelectionModel().selectFirstRow();
					}
					else if ( this.action != 'view' ) {
						if ( !this.findById('EVPLDSAEF_DiagPanel').collapsed ) {
							base_form.findField('DeseaseType_id').focus(true);
						}
/*
						else if ( !this.EvnXmlPanel.collapsed ) {
							base_form.findField('EvnVizitPL_Recomendations').focus(true);
						}
*/
						else if ( !base_form.findField('ProfGoal_id').disabled ) {
							base_form.findField('ProfGoal_id').focus(true);
						}
						else {
							base_form.findField('EvnVizitPL_Time').focus(true);
						}
					}
					else {
						this.buttons[this.buttons.length - 1].focus();
					}
				}.createDelegate(this),
				onTabAction: function () {
					this.buttons[this.buttons.length - 1].focus(true);
				}.createDelegate(this),
				tabIndex: TABINDEX_EVPLDSAEF + 19,
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
					else {
						if ( !this.findById('EVPLDSAEF_EvnReceptPanel').collapsed && this.findById('EVPLDSAEF_EvnReceptGrid').getStore().getCount() > 0 ) {
							this.findById('EVPLDSAEF_EvnReceptGrid').getView().focusRow(0);
							this.findById('EVPLDSAEF_EvnReceptGrid').getSelectionModel().selectFirstRow();
						}
						else if ( !this.findById('EVPLDSAEF_EvnUslugaPanel').hidden && !this.findById('EVPLDSAEF_EvnUslugaPanel').collapsed && this.findById('EVPLDSAEF_EvnUslugaGrid').getStore().getCount() > 0 ) {
							this.findById('EVPLDSAEF_EvnUslugaGrid').getView().focusRow(0);
							this.findById('EVPLDSAEF_EvnUslugaGrid').getSelectionModel().selectFirstRow();
						}
						else if ( !this.findById('EVPLDSAEF_EvnDiagPLPanel').collapsed && this.findById('EVPLDSAEF_EvnDiagPLGrid').getStore().getCount() > 0 ) {
							this.findById('EVPLDSAEF_EvnDiagPLGrid').getView().focusRow(0);
							this.findById('EVPLDSAEF_EvnDiagPLGrid').getSelectionModel().selectFirstRow();
						}
					}
				}.createDelegate(this),
				onTabAction: function () {
					if ( this.action != 'view' ) {
						this.findById('EvnVizitPLDispSomeAdultEditForm').getForm().findField('EvnVizitPL_setDate').focus(true);
					}
					else {
						if ( !this.findById('EVPLDSAEF_EvnDiagPLPanel').collapsed && this.findById('EVPLDSAEF_EvnDiagPLGrid').getStore().getCount() > 0 ) {
							this.findById('EVPLDSAEF_EvnDiagPLGrid').getView().focusRow(0);
							this.findById('EVPLDSAEF_EvnDiagPLGrid').getSelectionModel().selectFirstRow();
						}
						else if ( !this.findById('EVPLDSAEF_EvnUslugaPanel').hidden && !this.findById('EVPLDSAEF_EvnUslugaPanel').collapsed && this.findById('EVPLDSAEF_EvnUslugaGrid').getStore().getCount() > 0 ) {
							this.findById('EVPLDSAEF_EvnUslugaGrid').getView().focusRow(0);
							this.findById('EVPLDSAEF_EvnUslugaGrid').getSelectionModel().selectFirstRow();
						}
						else if ( !this.findById('EVPLDSAEF_EvnReceptPanel').collapsed && this.findById('EVPLDSAEF_EvnReceptGrid').getStore().getCount() > 0 ) {
							this.findById('EVPLDSAEF_EvnReceptGrid').getView().focusRow(0);
							this.findById('EVPLDSAEF_EvnReceptGrid').getSelectionModel().selectFirstRow();
						}
					}
				}.createDelegate(this),
				tabIndex: TABINDEX_EVPLDSAEF + 20,
				text: BTN_FRMCANCEL
			}],
			items: [ new sw.Promed.PersonInformationPanelShort({
				id: 'EVPLDSAEF_PersonInformationFrame',
				region: 'north'
			}),
			// СЮДА ЛЕВУЮ ПАНЕЛЬ,
			this.leftPanel,
			new Ext.form.FormPanel({
				autoScroll: true,
				bodyBorder: false,
				bodyStyle: 'padding: 5px 5px 0',
				border: false,
				frame: false,
				id: 'EvnVizitPLDispSomeAdultEditForm',
				labelAlign: 'right',
				labelWidth: 150,
				items: [{
					name: 'accessType',
					value: '',
					xtype: 'hidden'
				}, {
					name: 'EvnVizitPL_id',
					value: 0,
					xtype: 'hidden'
				}, {
					name: 'EvnPL_id',
					value: 0,
					xtype: 'hidden'
				}, {
					name: 'EvnUslugaCommon_id',
					value: 0,
					xtype: 'hidden'
				}, {
					name: 'EvnDirection_id',
					value: null,
					xtype: 'hidden'
				}, {
					name: 'TimetableGraf_id',
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
					name: 'Server_id',
					value: -1,
					xtype: 'hidden'
				}, {
					name: 'MedPersonal_id',
					value: 0,
					xtype: 'hidden'
				}, {
					name: 'MedPersonal_sid',
					value: 0,
					xtype: 'hidden'
				}, {
					border: false,
					layout: 'column',
					items: [{
						border: false,
						layout: 'form',
						items: [{
							allowBlank: false,
							fieldLabel: lang['data'],
							format: 'd.m.Y',
							id: 'EVPLDSAEF_EvnVizitPL_setDate',
							listeners: {
								'change': function(field, newValue, oldValue) {
									if ( blockedDateAfterPersonDeath('personpanelid', 'EVPLDSAEF_PersonInformationFrame', field, newValue, oldValue) ) {
										return;
									}

									var base_form = this.findById('EvnVizitPLDispSomeAdultEditForm').getForm();

									var index;
									var lpu_section_id = base_form.findField('LpuSection_id').getValue();
									var med_staff_fact_id = base_form.findField('MedStaffFact_id').getValue();
									var med_staff_fact_sid = base_form.findField('MedStaffFact_sid').getValue();
									var ServiceType_id = base_form.findField('ServiceType_id').getValue();

									// Фильтр на поле ServiceType_id
									// https://redmine.swan.perm.ru/issues/17571
									base_form.findField('ServiceType_id').clearValue();
									base_form.findField('ServiceType_id').getStore().clearFilter();
									base_form.findField('ServiceType_id').lastQuery = '';

									if ( !Ext.isEmpty(newValue) ) {
										base_form.findField('ServiceType_id').getStore().filterBy(function(rec) {
											return (
												(Ext.isEmpty(rec.get('ServiceType_begDate')) || rec.get('ServiceType_begDate') <= newValue)
												&& (Ext.isEmpty(rec.get('ServiceType_endDate')) || rec.get('ServiceType_endDate') >= newValue)
											);
										});
									}

									index = base_form.findField('ServiceType_id').getStore().findBy(function(rec) {
										return (rec.get('ServiceType_id') == ServiceType_id);
									});

									if ( index >= 0 ) {
										base_form.findField('ServiceType_id').setValue(ServiceType_id);
									}

									base_form.findField('ServiceType_id').fireEvent('change', base_form.findField('ServiceType_id'), base_form.findField('ServiceType_id').getValue());

									// Устанавливаем дату для кодов посещений
									var uslugaComplexDateChanged = false;

									if ( base_form.findField('UslugaComplex_uid').getStore().baseParams.UslugaComplex_Date != Ext.util.Format.date(newValue, 'd.m.Y') ) {
										uslugaComplexDateChanged = true;
										base_form.findField('UslugaComplex_uid').setUslugaComplexDate(Ext.util.Format.date(newValue, 'd.m.Y'));
									}

									if ( getGlobalOptions().region && getGlobalOptions().region.nick.inlist([ 'pskov' ]) && uslugaComplexDateChanged == true ) {
										base_form.findField('UslugaComplex_uid').clearValue();
										base_form.findField('UslugaComplex_uid').lastQuery = 'This query sample that is not will never appear';
										base_form.findField('UslugaComplex_uid').getStore().removeAll();
										base_form.findField('UslugaComplex_uid').getStore().baseParams.query = '';
									}

									if ( this.action != 'view' ) {
										base_form.findField('LpuSection_id').enable();
										base_form.findField('MedStaffFact_id').enable();
									}

									base_form.findField('LpuSection_id').clearValue();
									base_form.findField('MedStaffFact_id').clearValue();
									base_form.findField('MedStaffFact_sid').clearValue();

									var lpu_section_filter_params = {
										allowLowLevel: 'yes',
										isPolka: true,
										onDate: Ext.util.Format.date(newValue, 'd.m.Y')
									};

									var medstafffact_filter_params = {
										allowLowLevel: 'yes',
										isPolka: true,
										onDate: Ext.util.Format.date(newValue, 'd.m.Y')
									};

									if ( !Ext.isEmpty(newValue) ) {
										lpu_section_filter_params.onDate = Ext.util.Format.date(newValue, 'd.m.Y');
										medstafffact_filter_params.onDate = Ext.util.Format.date(newValue, 'd.m.Y');
									}

									base_form.findField('LpuSection_id').getStore().removeAll();
									base_form.findField('MedStaffFact_id').getStore().removeAll();
									base_form.findField('MedStaffFact_sid').getStore().removeAll();

									// сначала фильтруем средний медперсонал, 
									// потому что для него не нужен фильтр по месту работы текущего пользователя
									
									setMedStaffFactGlobalStoreFilter(medstafffact_filter_params);

									base_form.findField('MedStaffFact_sid').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));

									if ( this.action == 'add' ) {
										// Фильтр на конкретное место работы
										if ( !Ext.isEmpty(this.userMedStaffFact.LpuSection_id) && !Ext.isEmpty(this.userMedStaffFact.MedStaffFact_id) ) {
											lpu_section_filter_params.id = this.userMedStaffFact.LpuSection_id;
											medstafffact_filter_params.id = this.userMedStaffFact.MedStaffFact_id;
										}
									}

									medstafffact_filter_params.EvnClass_SysNick = 'EvnVizit';

									setLpuSectionGlobalStoreFilter(lpu_section_filter_params);
									setMedStaffFactGlobalStoreFilter(medstafffact_filter_params);

									base_form.findField('LpuSection_id').getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));
									base_form.findField('MedStaffFact_id').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));									

									if ( base_form.findField('LpuSection_id').getStore().getById(lpu_section_id) ) {
										base_form.findField('LpuSection_id').setValue(lpu_section_id);
										base_form.findField('LpuSection_id').fireEvent('change', base_form.findField('LpuSection_id'), lpu_section_id);
									}

									if ( base_form.findField('MedStaffFact_id').getStore().getById(med_staff_fact_id) ) {
										base_form.findField('MedStaffFact_id').setValue(med_staff_fact_id);
									}

									if ( base_form.findField('MedStaffFact_sid').getStore().getById(med_staff_fact_sid) ) {
										base_form.findField('MedStaffFact_sid').setValue(med_staff_fact_sid);
									}
									
									/**
									 *	если форма открыта на добавление или редактирование и задано отделение и 
									 *	место работы или задан список мест работы, то не даем редактировать вообще
									 */
									if ( this.action.inlist([ 'add', 'edit' ]) && !Ext.isEmpty(this.userMedStaffFact.LpuSection_id) && !Ext.isEmpty(this.userMedStaffFact.MedStaffFact_id) ) {
										base_form.findField('LpuSection_id').disable();
										base_form.findField('MedStaffFact_id').disable();

										// Если форма открыта на добавление...
										if ( this.action == 'add' ) {
											// ... то устанавливаем заданные значения отделения и места работы, если они есть в списке
											index = base_form.findField('LpuSection_id').getStore().findBy(function(rec) {
												return (rec.get('LpuSection_id') == this.userMedStaffFact.LpuSection_id);
											}.createDelegate(this));

											if ( index >= 0 ) {
												base_form.findField('LpuSection_id').setValue(this.userMedStaffFact.LpuSection_id);
												base_form.findField('LpuSection_id').fireEvent('change', base_form.findField('LpuSection_id'), this.userMedStaffFact.LpuSection_id);
											}

											index = base_form.findField('MedStaffFact_id').getStore().findBy(function(rec) {
												return (rec.get('MedStaffFact_id') == this.userMedStaffFact.MedStaffFact_id);
											}.createDelegate(this));

											if ( index >= 0 ) {
												base_form.findField('MedStaffFact_id').setValue(this.userMedStaffFact.MedStaffFact_id);
											}
										}
									}

									this.getDirectionIf();
								}.createDelegate(this),
								'keydown': function(inp, e) {
									if ( e.getKey() == Ext.EventObject.TAB && e.shiftKey == true ) {
										e.stopEvent();
										this.buttons[this.buttons.length - 1].focus();
									}
								}.createDelegate(this)
							},
							name: 'EvnVizitPL_setDate',
							plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
							selectOnFocus: true,
							tabIndex: TABINDEX_EVPLDSAEF + 1,
							width: 100,
							xtype: 'swdatefield'
						}]
					}, {
						border: false,
						labelWidth: 50,
						layout: 'form',
						items: [{
							fieldLabel: lang['vremya'],
							listeners: {
								'keydown': function (inp, e) {
									if ( e.getKey() == Ext.EventObject.F4 ) {
										e.stopEvent();
										inp.onTriggerClick();
									}
								}
							},
							name: 'EvnVizitPL_setTime',
							onTriggerClick: function() {
								var base_form = this.findById('EvnVizitPLDispSomeAdultEditForm').getForm();
								var time_field = base_form.findField('EvnVizitPL_setTime');

								if ( time_field.disabled ) {
									return false;
								}

								setCurrentDateTime({
									callback: function() {
										base_form.findField('EvnVizitPL_setDate').fireEvent('change', base_form.findField('EvnVizitPL_setDate'), base_form.findField('EvnVizitPL_setDate').getValue());
									},
									dateField: base_form.findField('EvnVizitPL_setDate'),
									loadMask: true,
									setDate: true,
									setDateMaxValue: true,
									setDateMinValue: false,
									setTime: true,
									timeField: time_field,
									windowId: this.id
								});
							}.createDelegate(this),	
							plugins: [ new Ext.ux.InputTextMask('99:99', true) ],
							tabIndex: TABINDEX_EVPLDSAEF + 2,
							validateOnBlur: false,
							width: 60,
							xtype: 'swtimefield'
						}]
					}, {
						border: false,
						labelWidth: 130,
						layout: 'form',
						items: [{
							comboSubject: 'VizitClass',
							fieldLabel: lang['vid_posescheniya'],
							tabIndex: TABINDEX_EVPLDSAEF + 3,
							width: 100,
							xtype: 'swcommonsprcombo'
						}]
					}]
				}, { // Night, поле специальности врача
					border: false,
					xtype: 'panel',
					layout: 'form',
					hidden: true,
					id: 'EVPLDSAEF_DispWowSpecComboSet',
					items: [{
						allowBlank: true,
						hiddenName: 'DispWowSpec_id',
						id: 'EVPLDSAEF_DispWowSpecCombo',
						listWidth: 650,
						tabIndex: TABINDEX_EVPLDSAEF + 4,
						width: 433,
						xtype: 'swdispwowspeccombo',
						listeners: 
						{
							change: 
								function(field, newValue, oldValue) 
								{
									var far = new Array();
									far.length = 0;
									switch (newValue.toString())
									{
										case '1': far.push('1000', '1003', '1007', '1009', '1010'); break; 
										case '2': far.push('2300'); break; 
										case '3': far.push('2800', '2801', '2805'); break; 
										case '4': far.push('2600', '2601', '2610', '2620'); break; 
										case '5': far.push('2700'); break; 
										case '6': far.push('0510', '0511', '0520'); break; 
										case '7': far.push('2509', '2510', '2517', '2518'); break; 
										case '8': far.push('1500'); break; 
										case '9': far.push('1450'); break; 
										case '10':
												far.push('1000','1007','1008','7108','7100','1001','1015','1016','1014','1002','9100','1006',
													'2300','2330','7233','2350','7230','2301','8231','7231','7232','2320','8232','9230',
													'2810','2801','2800','2805','8280','7280','9280',
													'2601','8260','7260',
													'7271','2712','2700','2713','2710','8271','7270','2711','9270',
													'0510','0530','8051','7051','9051','0520','0511',
													'9521','2519','2518','7259','7257','2517',
													'1500','8150','7150','9150',
													'7145','1450');
												break;
										default: far.push('1000', '1003', '1007', '1009', '1010', '2300','2800', '2801', '2805','2600', '2601', '2610', '2620','2700','0510', '0511', '0520','2509', '2510', '2517', '2518', '1500', '1450'); break; 
									}

									var grid_Vizit = getWnd('EvnPLWOWEditWindow').findById('EvnPLWOWVizitGrid');
									var mass_Vizit = newValue;
									grid_Vizit.getGrid().getStore().each(function(record)
									{
										if (record.get('DispWOWSpec_id').inlist(mass_Vizit))
										{
											alert(lang['osmotr_etogo_vracha-spetsialista_uje_zaveden']);
										}
									});


									/*
									var combo = field.ownerCt.ownerCt.findById('EVPLDSAEF_LpuSectionCombo');
									combo.getStore().clearFilter();
									combo.lastQuery = '';
									var id = combo.getValue();
									
									combo.getStore().filterBy(function(record) 
									{
										if (newValue!=10)
										{
											return (record.get('LpuSectionProfile_Code').inlist(far));
										}
										else 
										{
											return true;
										}
									});
									var fs = false;
									combo.getStore().each(function(record) 
									{
										if (record.get('LpuSection_id') == id)
										{
											combo.fireEvent('select', combo, record, 0);
											fs = true;
										}
									});
									if (!fs) 
									{
										combo.setValue('');
										combo.clearInvalid();
									}
									*/
									Ext.getCmp('EvnVizitPLDispSomeAdultEditWindow').setFilterProfile(field, far, newValue);
									/*
									var combo = field.ownerCt.ownerCt.findById('EVPLDSAEF_MedPersonalCombo');
									combo.getStore().clearFilter();
									combo.lastQuery = '';
									combo.getStore().filterBy(function(record) 
									{
										if (newValue!=10)
										{
											return (record.get('LpuSectionProfile_Code').inlist(far));
										}
										else 
										{
											return true;
										}
									});
									combo.setValue('');
									combo.clearInvalid();
									*/
								}
						}
					}]
				}, {
					allowBlank: false,
					hiddenName: 'LpuSection_id',
					id: 'EVPLDSAEF_LpuSectionCombo',
					lastQuery: '',
					listWidth: 650,
					linkedElements: [
						'EVPLDSAEF_MedPersonalCombo'/*,
						'EVPLDSAEF_MidMedPersonalCombo'*/
					],
					tabIndex: TABINDEX_EVPLDSAEF + 5,
					width: 450,
					xtype: 'swlpusectionglobalcombo'
				}, {
					allowBlank: false,
					dateFieldId: 'EVPLDSAEF_EvnVizitPL_setDate',
					enableOutOfDateValidation: true,
					hiddenName: 'MedStaffFact_id',
					id: 'EVPLDSAEF_MedPersonalCombo',
					lastQuery: '',
					listWidth: 650,
					parentElementId: 'EVPLDSAEF_LpuSectionCombo',
					tabIndex: TABINDEX_EVPLDSAEF + 6,
					width: 450,
					xtype: 'swmedstafffactglobalcombo'
				}, {
					fieldLabel: lang['sred_m_personal'],
					hiddenName: 'MedStaffFact_sid',
					id: 'EVPLDSAEF_MidMedPersonalCombo',
					listWidth: 650,
					// parentElementId: 'EVPLDSAEF_LpuSectionCombo',
					tabIndex: TABINDEX_EVPLDSAEF + 7,
					width: 450,
					xtype: 'swmedstafffactglobalcombo'
				}, {
					allowBlank: false,
					hiddenName: 'ServiceType_id',
					listeners: {
						'change': function(combo, newValue, oldValue) {
							var record = combo.getStore().getById(newValue);

							if ( !record ) {
								return false;
							}

							if ( record.get('ServiceType_SysNick') == 'neotl' ) {
								this.findById('EvnVizitPLDispSomeAdultEditForm').getForm().findField('PayType_id').setValue(1);
							}
						}.createDelegate(this)
					},
					tabIndex: TABINDEX_EVPLDSAEF + 8,
					width: 300,
					xtype: 'swservicetypecombo'
				}, {
					allowBlank: false,
					listeners: {
						'change': function(combo, newValue, oldValue) {
							var base_form = this.findById('EvnVizitPLDispSomeAdultEditForm').getForm();
							var prof_goal_combo = base_form.findField('ProfGoal_id');

							if ( newValue != null && newValue.toString().length > 0 ) {
								var record = combo.getStore().getById(newValue);

								if ( record ) {
									if ( record.get('VizitType_SysNick') == 'prof' ) {
										prof_goal_combo.enable();
									}
									else {
										prof_goal_combo.disable();
										prof_goal_combo.clearValue();
									}
								}
							}
							else {
								prof_goal_combo.disable();
								prof_goal_combo.clearValue();
							}
						}.createDelegate(this)
					},
					tabIndex: TABINDEX_EVPLDSAEF + 9,
					width: 300,
					EvnClass_id: 11, //36 EvnVizitPLWOW
					xtype: 'swvizittypecombo'
				}, {
					allowBlank: false,
					id: 'EVPLDSAEF_PayType_id',
					tabIndex: TABINDEX_EVPLDSAEF + 10,
					width: 300,
					listeners:
					{
						'change': function(combo, newValue, oldValue) {
							if(getGlobalOptions().region.nick=='ufa')
							{
								var base_form = this.findById('EvnVizitPLDispSomeAdultEditForm').getForm();
								var uslugacomplex_combo = base_form.findField('UslugaComplex_uid');

								//Проверяем по SysNick
								var pay_type = base_form.findField('PayType_id').getStore().getById(base_form.findField('PayType_id').getValue());
								if ( pay_type ) {
									var pay_type_nick = pay_type.get('PayType_SysNick');
									if ((pay_type_nick!='oms')&&(pay_type_nick!='dopdisp'))
									{
										uslugacomplex_combo.setAllowBlank(true);
									}
									else
									{
										uslugacomplex_combo.setAllowBlank(false);
									}
								}
							}
						}.createDelegate(this)
					},

					useCommonFilter: true,
					xtype: 'swpaytypecombo'
				}, {
					border: false,
					hidden: !(getGlobalOptions().region && getGlobalOptions().region.nick.inlist([ 'pskov', 'ufa' ])), // Открыто для Пскова и Уфы
					layout: 'form',
					items: [{
						allowBlank: !(getGlobalOptions().region && getGlobalOptions().region.nick.inlist([ 'pskov', 'ufa' ])),
						fieldLabel: lang['kod_posescheniya'],
						hiddenName: 'UslugaComplex_uid',
						to: 'EvnVizitPL',
						id: 'EVPLDSAEF_UslugaComplex',
						listWidth: 600,
						tabIndex: TABINDEX_EVPLDSAEF + 11,
						width: 450,
						xtype: 'swuslugacomplexnewcombo'
					}]
				}, {
					allowDecimals: false,
					allowNegative: false,
					enableKeyEvents: true,
					fieldLabel: lang['vremya_priema_min'],
					listeners: {
						'keydown': function(inp, e) {
							switch ( e.getKey() ) {
								case Ext.EventObject.TAB:
									var base_form = this.findById('EvnVizitPLDispSomeAdultEditForm').getForm();

									if ( e.shiftKey == false && base_form.findField('ProfGoal_id').disabled && this.findById('EVPLDSAEF_DiagPanel').collapsed ) {
										e.stopEvent();

										if ( !this.findById('EVPLDSAEF_EvnDiagPLPanel').collapsed && this.findById('EVPLDSAEF_EvnDiagPLGrid').getStore().getCount() > 0 ) {
											this.findById('EVPLDSAEF_EvnDiagPLGrid').getView().focusRow(0);
											this.findById('EVPLDSAEF_EvnDiagPLGrid').getSelectionModel().selectFirstRow();
										}
										else if ( !this.findById('EVPLDSAEF_EvnUslugaPanel').hidden && !this.findById('EVPLDSAEF_EvnUslugaPanel').collapsed && this.findById('EVPLDSAEF_EvnUslugaGrid').getStore().getCount() > 0 ) {
											this.findById('EVPLDSAEF_EvnUslugaGrid').getView().focusRow(0);
											this.findById('EVPLDSAEF_EvnUslugaGrid').getSelectionModel().selectFirstRow();
										}
										else if ( !this.findById('EVPLDSAEF_EvnReceptPanel').collapsed && this.findById('EVPLDSAEF_EvnReceptGrid').getStore().getCount() > 0 ) {
											this.findById('EVPLDSAEF_EvnReceptGrid').getView().focusRow(0);
											this.findById('EVPLDSAEF_EvnReceptGrid').getSelectionModel().selectFirstRow();
										}
										else if ( this.action == 'view' ) {
											this.buttons[this.buttons.length - 1].focus();
										}
										else {
											this.buttons[0].focus();
										}
									}
								break;
							}
						}.createDelegate(this)
					},
					name: 'EvnVizitPL_Time',
					tabIndex: TABINDEX_EVPLDSAEF + 12,
					width: 70,
					xtype: 'numberfield'
				}, {
					enableKeyEvents: true,
					hiddenName: 'ProfGoal_id',
					listeners: {
						'keydown': function(inp, e) {
							switch ( e.getKey() ) {
								case Ext.EventObject.TAB:
									var base_form = this.findById('EvnVizitPLDispSomeAdultEditForm').getForm();

									if ( e.shiftKey == false && this.findById('EVPLDSAEF_DiagPanel').collapsed ) {
										e.stopEvent();

										if ( !this.findById('EVPLDSAEF_EvnDiagPLPanel').collapsed && this.findById('EVPLDSAEF_EvnDiagPLGrid').getStore().getCount() > 0 ) {
											this.findById('EVPLDSAEF_EvnDiagPLGrid').getView().focusRow(0);
											this.findById('EVPLDSAEF_EvnDiagPLGrid').getSelectionModel().selectFirstRow();
										}
										else if ( !this.findById('EVPLDSAEF_EvnUslugaPanel').hidden && !this.findById('EVPLDSAEF_EvnUslugaPanel').collapsed && this.findById('EVPLDSAEF_EvnUslugaGrid').getStore().getCount() > 0 ) {
											this.findById('EVPLDSAEF_EvnUslugaGrid').getView().focusRow(0);
											this.findById('EVPLDSAEF_EvnUslugaGrid').getSelectionModel().selectFirstRow();
										}
										else if ( !this.findById('EVPLDSAEF_EvnReceptPanel').collapsed && this.findById('EVPLDSAEF_EvnReceptGrid').getStore().getCount() > 0 ) {
											this.findById('EVPLDSAEF_EvnReceptGrid').getView().focusRow(0);
											this.findById('EVPLDSAEF_EvnReceptGrid').getSelectionModel().selectFirstRow();
										}
										else if ( this.action == 'view' ) {
											this.buttons[this.buttons.length - 1].focus();
										}
										else {
											this.buttons[0].focus();
										}
									}
								break;
							}
						}.createDelegate(this)
					},
					tabIndex: TABINDEX_EVPLDSAEF + 13,
					width: 450,
					xtype: 'swprofgoalcombo'
				}, {
					allowDecimals: true,
					allowNegative: false,
					disabled: true,
					enableKeyEvents: true,
					fieldLabel: lang['uet_fakt'],
					name: 'EvnVizitPL_Uet',
					tabIndex: TABINDEX_EVPLDSAEF + 14,
					xtype: 'numberfield'
				}, {
					allowDecimals: true,
					allowNegative: false,
					disabled: true,
					enableKeyEvents: true,
					fieldLabel: lang['uet_fakt_po_oms'],
					name: 'EvnVizitPL_UetOMS',
					tabIndex: TABINDEX_EVPLDSAEF + 15,
					xtype: 'numberfield'
				},
				
				/* Панель входящего направления (по направлению) */
				this.DirectionInfoPanel,
                /* Панель протокола осмотра */
                this.EvnXmlPanel,

				new sw.Promed.Panel({
					autoHeight: true,
					bodyStyle: 'padding-top: 0.5em;',
					border: true,
					collapsible: true,
					id: 'EVPLDSAEF_DiagPanel',
					layout: 'form',
					style: 'margin-bottom: 0.5em;',
					title: lang['3_osnovnoy_diagnoz'],
					items: [{
					    allowBlank: !(getGlobalOptions().region && getGlobalOptions().region.nick == 'ufa'),
						hiddenName: 'Diag_id',
						tabIndex: TABINDEX_EVPLDSAEF + 16,
						width: 450,
						xtype: 'swdiagcombo',
						onChange: function(combo, newValue, oldValue) {
							combo.getStore().each(function (rec) {
								if (rec.get('Diag_id') == newValue) {
									var diag_code = rec.get('Diag_Code').substr(0, 3);
									if ( diag_code.inlist(['B15', 'B16', 'B17', 'B18', 'B19']) ) {
										this.MorbusHepatitisSpec.show();
									} else {
										this.MorbusHepatitisSpec.hide();
									}
								}
							}.createDelegate(this));
						}.createDelegate(this)
					}, {
						hiddenName: 'DeseaseType_id',
						listeners: {
							'keydown': function(inp, e) {
								switch ( e.getKey() ) {
									case Ext.EventObject.TAB:
										var base_form = this.findById('EvnVizitPLDispSomeAdultEditForm').getForm();

										if ( e.shiftKey == false ) {
											e.stopEvent();

											if ( !this.findById('EVPLDSAEF_EvnDiagPLPanel').collapsed && this.findById('EVPLDSAEF_EvnDiagPLGrid').getStore().getCount() > 0 ) {
												this.findById('EVPLDSAEF_EvnDiagPLGrid').getView().focusRow(0);
												this.findById('EVPLDSAEF_EvnDiagPLGrid').getSelectionModel().selectFirstRow();
											}
											else if ( !this.findById('EVPLDSAEF_EvnUslugaPanel').hidden && !this.findById('EVPLDSAEF_EvnUslugaPanel').collapsed && this.findById('EVPLDSAEF_EvnUslugaGrid').getStore().getCount() > 0 ) {
												this.findById('EVPLDSAEF_EvnUslugaGrid').getView().focusRow(0);
												this.findById('EVPLDSAEF_EvnUslugaGrid').getSelectionModel().selectFirstRow();
											}
											else if ( !this.findById('EVPLDSAEF_EvnReceptPanel').collapsed && this.findById('EVPLDSAEF_EvnReceptGrid').getStore().getCount() > 0 ) {
												this.findById('EVPLDSAEF_EvnReceptGrid').getView().focusRow(0);
												this.findById('EVPLDSAEF_EvnReceptGrid').getSelectionModel().selectFirstRow();
											}
											else if ( this.action == 'view' ) {
												this.buttons[this.buttons.length - 1].focus();
											}
											else {
												this.buttons[0].focus();
											}
										}
									break;
								}
							}.createDelegate(this)
						},
						tabIndex: TABINDEX_EVPLDSAEF + 17,
						width: 450,
						xtype: 'swdeseasetypecombo'
					}, {
						border: false,
						hidden: !(getGlobalOptions().region && getGlobalOptions().region.nick == 'ufa'),
						layout: 'form',
						items: [{
							// allowBlank: false,
							fieldLabel: lang['oslojnenie'],
							hiddenName: 'Diag_agid',
							tabIndex: TABINDEX_EVPLDSAEF + 18,
							width: 450,
							xtype: 'swdiagcombo'
						}]
					}]
				}),
				new sw.Promed.Panel({
					border: true,
					collapsible: true,
					height: 200,
					id: 'EVPLDSAEF_EvnDiagPLPanel',
					isLoaded: false,
					layout: 'border',
					listeners: {
						'expand': function(panel) {
							if ( panel.isLoaded === false ) {
								panel.isLoaded = true;
								panel.findById('EVPLDSAEF_EvnDiagPLGrid').getStore().load({
									params: {
										EvnVizitPL_id: this.findById('EvnVizitPLDispSomeAdultEditForm').getForm().findField('EvnVizitPL_id').getValue()
									}
								});
							}
							panel.doLayout();
						}.createDelegate(this)
					},
					style: 'margin-bottom: 0.5em;',
					title: lang['4_soputstvuyuschie_diagnozyi'],
					items: [ new Ext.grid.GridPanel({
						autoExpandColumn: 'autoexpand_diag',
						autoExpandMin: 100,
						border: false,
						columns: [{
							dataIndex: 'EvnDiagPL_setDate',
							header: lang['data_ustanovki'],
							hidden: false,
							renderer: Ext.util.Format.dateRenderer('d.m.Y'),
							resizable: false,
							sortable: true,
							width: 130
						}, {
							dataIndex: 'Diag_Code',
							header: lang['kod'],
							hidden: false,
							resizable: false,
							sortable: true,
							width: 80
						}, {
							dataIndex: 'Diag_Name',
							header: lang['naimenovanie'],
							hidden: false,
							resizable: false,
							sortable: true,
							width: 250
						}, {
							dataIndex: 'DeseaseType_Name',
							header: lang['harakter'],
							hidden: false,
							id: 'autoexpand_diag',
							sortable: true
						}],
						frame: false,
						id: 'EVPLDSAEF_EvnDiagPLGrid',
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

								var grid = this.findById('EVPLDSAEF_EvnDiagPLGrid');

								switch ( e.getKey() ) {
									case Ext.EventObject.DELETE:
										this.deleteEvent('EvnDiagPL');
									break;

									case Ext.EventObject.END:
										GridEnd(grid);
									break;

									case Ext.EventObject.ENTER:
									case Ext.EventObject.F3:
									case Ext.EventObject.F4:
									case Ext.EventObject.INSERT:
										if ( !grid.getSelectionModel().getSelected() ) {
											return false;
										}

										var action = 'add';

										if ( e.getKey() == Ext.EventObject.F3 ) {
											action = 'view';
										}
										else if ( e.getKey() == Ext.EventObject.F4 || e.getKey() == Ext.EventObject.ENTER ) {
											action = 'edit';
										}

										this.openEvnDiagPLEditWindow(action);
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
										grid.getSelectionModel().clearSelections();
										grid.getSelectionModel().fireEvent('rowselect', grid.getSelectionModel());

										if ( e.shiftKey == false ) {
											if ( !this.findById('EVPLDSAEF_EvnUslugaPanel').hidden && !this.findById('EVPLDSAEF_EvnUslugaPanel').collapsed && this.findById('EVPLDSAEF_EvnUslugaGrid').getStore().getCount() > 0 ) {
												this.findById('EVPLDSAEF_EvnUslugaGrid').getView().focusRow(0);
												this.findById('EVPLDSAEF_EvnUslugaGrid').getSelectionModel().selectFirstRow();
											}
											else if ( !this.findById('EVPLDSAEF_EvnReceptPanel').collapsed && this.findById('EVPLDSAEF_EvnReceptGrid').getStore().getCount() > 0 ) {
												this.findById('EVPLDSAEF_EvnReceptGrid').getView().focusRow(0);
												this.findById('EVPLDSAEF_EvnReceptGrid').getSelectionModel().selectFirstRow();
											}
											else if ( this.action == 'view' ) {
												this.buttons[this.buttons.length - 1].focus();
											}
											else {
												this.buttons[0].focus();
											}
										}
										else {
											var base_form = this.findById('EvnVizitPLDispSomeAdultEditForm').getForm();

											if ( this.action != 'view' ) {
												if ( !this.findById('EVPLDSAEF_DiagPanel').collapsed ) {
													base_form.findField('DeseaseType_id').focus(true);
												}
												else if ( !base_form.findField('ProfGoal_id').disabled ) {
													base_form.findField('ProfGoal_id').focus(true);
												}
												else {
													base_form.findField('EvnVizitPL_Time').focus(true);
												}
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
						layout: 'fit',
						listeners: {
							'rowdblclick': function(grid, number, obj) {
								this.openEvnDiagPLEditWindow('edit');
							}.createDelegate(this)
						},
						loadMask: true,
						region: 'center',
						sm: new Ext.grid.RowSelectionModel({
							listeners: {
								'rowselect': function(sm, rowIndex, record) {
									var access_type = 'view';
									var id = null;
									var selected_record = sm.getSelected();
									var toolbar = this.findById('EVPLDSAEF_EvnDiagPLGrid').getTopToolbar();

									if ( selected_record ) {
										access_type = selected_record.get('accessType');
										id = selected_record.get('EvnDiagPL_id');
									}

									toolbar.items.items[1].disable();
									toolbar.items.items[3].disable();

									if ( id ) {
										toolbar.items.items[2].enable();

										if ( this.action != 'view' && access_type == 'edit' ) {
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
								'load': function(store, records, index) {
									if ( store.getCount() == 0 ) {
										LoadEmptyRow(this.findById('EVPLDSAEF_EvnDiagPLGrid'));
									}

									// this.findById('EVPLDSAEF_EvnDiagPLGrid').getView().focusRow(0);
									// this.findById('EVPLDSAEF_EvnDiagPLGrid').getSelectionModel().selectFirstRow();
								}.createDelegate(this)
							},
							reader: new Ext.data.JsonReader({
								id: 'EvnDiagPL_id'
							}, [{
								mapping: 'accessType',
								name: 'accessType',
								type: 'string'
							}, {
								mapping: 'EvnDiagPL_id',
								name: 'EvnDiagPL_id',
								type: 'int'
							}, {
								mapping: 'EvnVizitPL_id',
								name: 'EvnVizitPL_id',
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
								mapping: 'DeseaseType_id',
								name: 'DeseaseType_id',
								type: 'int'
							}, {
								mapping: 'Diag_id',
								name: 'Diag_id',
								type: 'int'
							}, {
								mapping: 'LpuSection_id',
								name: 'LpuSection_id',
								type: 'int'
							}, {
								mapping: 'MedPersonal_id',
								name: 'MedPersonal_id',
								type: 'int'
							}, {
								dateFormat: 'd.m.Y',
								mapping: 'EvnDiagPL_setDate',
								name: 'EvnDiagPL_setDate',
								type: 'date'
							}, {
								mapping: 'LpuSection_Name',
								name: 'LpuSection_Name',
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
								mapping: 'Diag_Name',
								name: 'Diag_Name',
								type: 'string'
							}, {
								mapping: 'DeseaseType_Name',
								name: 'DeseaseType_Name',
								type: 'string'
							}]),
							url: '/?c=EvnPL&m=loadEvnDiagPLGrid'
						}),
						tbar: new sw.Promed.Toolbar({
							buttons: [{
								handler: function() {
									this.openEvnDiagPLEditWindow('add');
								}.createDelegate(this),
								iconCls: 'add16',
								text: BTN_GRIDADD,
								tooltip: BTN_GRIDADD_TIP
							}, {
								handler: function() {
									this.openEvnDiagPLEditWindow('edit');
								}.createDelegate(this),
								iconCls: 'edit16',
								text: BTN_GRIDEDIT,
								tooltip: BTN_GRIDEDIT_TIP
							}, {
								handler: function() {
									this.openEvnDiagPLEditWindow('view');
								}.createDelegate(this),
								iconCls: 'view16',
								text: BTN_GRIDVIEW,
								tooltip: BTN_GRIDVIEW_TIP
							}, {
								handler: function() {
									this.deleteEvent('EvnDiagPL');
								}.createDelegate(this),
								iconCls: 'delete16',
								text: BTN_GRIDDEL,
								tooltip: BTN_GRIDDEL_TIP
							}]
						})
					})]
				}),
				new sw.Promed.Panel({
					border: true,
					collapsible: true,
					height: 200,
					hidden: (getGlobalOptions().region && getGlobalOptions().region.nick.inlist([ 'pskov' ])),
					id: 'EVPLDSAEF_EvnUslugaPanel',
					isLoaded: false,
					layout: 'border',
					listeners: {
						'expand': function(panel) {
							if ( panel.hidden ) {
								return false;
							}

							if ( panel.isLoaded === false ) {
								panel.isLoaded = true;
								panel.findById('EVPLDSAEF_EvnUslugaGrid').getStore().load({
									params: {
										pid: this.findById('EvnVizitPLDispSomeAdultEditForm').getForm().findField('EvnVizitPL_id').getValue()
									}
								});
							}
							panel.doLayout();
						}.createDelegate(this)
					},
					style: 'margin-bottom: 0.5em;',
					title: lang['5_obsledovaniya'],
					items: [ new Ext.grid.GridPanel({
						autoExpandColumn: 'autoexpand_usluga',
						autoExpandMin: 100,
						border: false,
						columns: [{
							dataIndex: 'EvnUsluga_setDate',
							header: lang['data'],
							hidden: false,
							renderer: Ext.util.Format.dateRenderer('d.m.Y'),
							resizable: false,
							sortable: true,
							width: 100
						}, {
							dataIndex: 'Usluga_Code',
							header: lang['kod'],
							hidden: false,
							resizable: false,
							sortable: true,
							width: 100
						}, {
							dataIndex: 'Usluga_Name',
							header: lang['naimenovanie'],
							hidden: false,
							id: 'autoexpand_usluga',
							resizable: true,
							sortable: true
						}, {
							dataIndex: 'EvnUsluga_Kolvo',
							header: lang['kolichestvo'],
							hidden: false,
							resizable: true,
							sortable: true,
							width: 100
						}, {
							dataIndex: 'EvnUsluga_Price',
							header: lang['tsena_uet'],
							hidden: false,
							resizable: true,
							sortable: true,
							renderer: twoDecimalsRenderer,
							width: 100
						}, {
							dataIndex: 'EvnUsluga_Summa',
							header: lang['summa_uet'],
							hidden: false,
							renderer: twoDecimalsRenderer,
							resizable: true,
							sortable: true,
							width: 100
						}],
						frame: false,
						id: 'EVPLDSAEF_EvnUslugaGrid',
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

								var grid = this.findById('EVPLDSAEF_EvnUslugaGrid');

								switch ( e.getKey() ) {
									case Ext.EventObject.DELETE:
										this.deleteEvent('EvnUsluga');
									break;

									case Ext.EventObject.END:
										GridEnd(grid);
									break;

									case Ext.EventObject.ENTER:
									case Ext.EventObject.F3:
									case Ext.EventObject.F4:
									case Ext.EventObject.INSERT:
										if ( !grid.getSelectionModel().getSelected() ) {
											return false;
										}

										var action = 'add';

										if ( e.getKey() == Ext.EventObject.F3 ) {
											action = 'view';
										}
										else if ( e.getKey() == Ext.EventObject.F4 || e.getKey() == Ext.EventObject.ENTER ) {
											action = 'edit';
										}

										this.openEvnUslugaEditWindow(action);
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
										grid.getSelectionModel().clearSelections();
										grid.getSelectionModel().fireEvent('rowselect', grid.getSelectionModel());

										if ( e.shiftKey == false ) {
											if ( !this.findById('EVPLDSAEF_EvnReceptPanel').collapsed && this.findById('EVPLDSAEF_EvnReceptGrid').getStore().getCount() > 0 ) {
												this.findById('EVPLDSAEF_EvnReceptGrid').getView().focusRow(0);
												this.findById('EVPLDSAEF_EvnReceptGrid').getSelectionModel().selectFirstRow();
											}
											else if ( this.action == 'view' ) {
												this.buttons[this.buttons.length - 1].focus();
											}
											else {
												this.buttons[0].focus();
											}
										}
										else {
											var base_form = this.findById('EvnVizitPLDispSomeAdultEditForm').getForm();

											if ( !this.findById('EVPLDSAEF_EvnDiagPLPanel').collapsed && this.findById('EVPLDSAEF_EvnDiagPLGrid').getStore().getCount() > 0 ) {
												this.findById('EVPLDSAEF_EvnDiagPLGrid').getView().focusRow(0);
												this.findById('EVPLDSAEF_EvnDiagPLGrid').getSelectionModel().selectFirstRow();
											}
											else if ( this.action != 'view' ) {
												if ( !this.findById('EVPLDSAEF_DiagPanel').collapsed ) {
													base_form.findField('DeseaseType_id').focus(true);
												}
												else if ( !base_form.findField('ProfGoal_id').disabled ) {
													base_form.findField('ProfGoal_id').focus(true);
												}
												else {
													base_form.findField('EvnVizitPL_Time').focus(true);
												}
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
							'rowdblclick': function(grid, number, obj) {
								this.openEvnUslugaEditWindow('edit');
							}.createDelegate(this)
						},
						loadMask: true,
						region: 'center',
						sm: new Ext.grid.RowSelectionModel({
							listeners: {
								'rowselect': function(sm, rowIndex, record) {
									var access_type = 'view';
									var id = null;
									var selected_record = sm.getSelected();
									var toolbar = this.findById('EVPLDSAEF_EvnUslugaGrid').getTopToolbar();

									if ( selected_record ) {
										access_type = selected_record.get('accessType');
										id = selected_record.get('EvnUsluga_id');
									}

									toolbar.items.items[1].disable();
									toolbar.items.items[3].disable();

									if ( id ) {
										toolbar.items.items[2].enable();

										if ( this.action != 'view' && access_type == 'edit' ) {
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
							baseParams: {
								'parent': 'EvnVizitPL'
							},
							listeners: {
								'load': function(store, records, index) {
									if ( store.getCount() == 0 ) {
										LoadEmptyRow(this.findById('EVPLDSAEF_EvnUslugaGrid'));
									}

									var base_form = this.findById('EvnVizitPLDispSomeAdultEditForm').getForm();

									if ( store.getCount() == 0 || (store.getCount() == 1 && !store.getAt(0).get('EvnUsluga_id')) ) {
										base_form.findField('EvnVizitPL_Uet').enable();
										base_form.findField('EvnVizitPL_UetOMS').enable();
									}
									else {
										base_form.findField('EvnVizitPL_Uet').disable();
										base_form.findField('EvnVizitPL_UetOMS').disable();
									}

									// this.findById('EVPLDSAEF_EvnUslugaGrid').getView().focusRow(0);
									// this.findById('EVPLDSAEF_EvnUslugaGrid').getSelectionModel().selectFirstRow();
								}.createDelegate(this)
							},
							reader: new Ext.data.JsonReader({
								id: 'EvnUsluga_id'
							}, [{
								mapping: 'accessType',
								name: 'accessType',
								type: 'string'
							}, {
								mapping: 'EvnUsluga_id',
								name: 'EvnUsluga_id',
								type: 'int'
							}, {
								mapping: 'EvnClass_SysNick',
								name: 'EvnClass_SysNick',
								type: 'string'
							}, {
								mapping: 'PayType_SysNick',
								name: 'PayType_SysNick',
								type: 'string'
							}, {
								dateFormat: 'd.m.Y',
								mapping: 'EvnUsluga_setDate',
								name: 'EvnUsluga_setDate',
								type: 'date'
							}, {
								mapping: 'Usluga_Code',
								name: 'Usluga_Code',
								type: 'string'
							}, {
								mapping: 'Usluga_Name',
								name: 'Usluga_Name',
								type: 'string'
							}, {
								mapping: 'EvnUsluga_Kolvo',
								name: 'EvnUsluga_Kolvo',
								type: 'float'
							}, {
								mapping: 'EvnUsluga_Price',
								name: 'EvnUsluga_Price',
								type: 'float'
							}, {
								mapping: 'EvnUsluga_Summa',
								name: 'EvnUsluga_Summa',
								type: 'float'
							}]),
							url: '/?c=EvnUsluga&m=loadEvnUslugaGrid'
						}),
						tbar: new sw.Promed.Toolbar({
							buttons: [{
								handler: function() {
									this.openEvnUslugaEditWindow('add');
								}.createDelegate(this),
								iconCls: 'add16',
								text: lang['dobavit']
							}, {
								handler: function() {
									this.openEvnUslugaEditWindow('edit');
								}.createDelegate(this),
								iconCls: 'edit16',
								text: lang['izmenit']
							}, {
								handler: function() {
									this.openEvnUslugaEditWindow('view');
								}.createDelegate(this),
								iconCls: 'view16',
								text: lang['prosmotr']
							}, {
								handler: function() {
									this.deleteEvent('EvnUsluga');
								}.createDelegate(this),
								iconCls: 'delete16',
								text: lang['udalit']
							}]
						})
					})]
				}),
				new sw.Promed.Panel({
					bodyStyle: 'padding-top: 0.5em;',
					border: true,
					collapsible: true,
					height: 200,
					id: 'EVPLDSAEF_EvnReceptPanel',
					layout: 'border',
					listeners: {
						'expand': function(panel) {
							if ( panel.isLoaded === false ) {
								panel.isLoaded = true;
								panel.findById('EVPLDSAEF_EvnReceptGrid').getStore().load({
									params: {
										EvnRecept_pid: this.findById('EvnVizitPLDispSomeAdultEditForm').getForm().findField('EvnVizitPL_id').getValue()
									}
								});
							}
							panel.doLayout();
						}.createDelegate(this)
					},
					style: 'margin-bottom: 0.5em;',
					title: lang['6_retseptyi'],
					items: [ new Ext.grid.GridPanel({
						autoExpandColumn: 'autoexpand_recept',
						autoExpandMin: 100,
						border: false,
						columns: [{
							dataIndex: 'EvnRecept_setDate',
							header: lang['data'],
							hidden: false,
							renderer: Ext.util.Format.dateRenderer('d.m.Y'),
							resizable: false,
							sortable: true,
							width: 80
						}, {
							dataIndex: 'MedPersonal_Fio',
							header: lang['vrach'],
							hidden: false,
							resizable: false,
							sortable: true,
							width: 250
						}, {
							dataIndex: 'Drug_Name',
							header: lang['medikament'],
							hidden: false,
							id: 'autoexpand_recept',
							sortable: true
						}, {
							dataIndex: 'EvnRecept_Ser',
							header: lang['seriya'],
							hidden: false,
							resizable: false,
							sortable: true,
							width: 100
						}, {
							dataIndex: 'EvnRecept_Num',
							header: lang['nomer'],
							hidden: false,
							resizable: false,
							sortable: true,
							width: 100
						}],
						frame: false,
						id: 'EVPLDSAEF_EvnReceptGrid',
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

								var grid = this.findById('EVPLDSAEF_EvnReceptGrid');

								switch ( e.getKey() ) {
									case Ext.EventObject.DELETE:
										this.deleteEvent('EvnRecept');
									break;

									case Ext.EventObject.END:
										GridEnd(grid);
									break;

									case Ext.EventObject.ENTER:
									case Ext.EventObject.F3:
									case Ext.EventObject.F4:
									case Ext.EventObject.INSERT:
										if ( !grid.getSelectionModel().getSelected() ) {
											return false;
										}

										var action = 'add';

										if ( e.getKey() == Ext.EventObject.F3 ) {
											action = 'view';
										}
										else if ( e.getKey() == Ext.EventObject.ENTER || e.getKey() == Ext.EventObject.F4 ) {
											action = 'edit';
										}

										this.openEvnReceptEditWindow(action);
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
										grid.getSelectionModel().clearSelections();
										grid.getSelectionModel().fireEvent('rowselect', grid.getSelectionModel());

										if ( e.shiftKey == false ) {
											if ( this.action == 'view' ) {
												this.buttons[this.buttons.length - 1].focus();
											}
											else {
												this.buttons[0].focus();
											}
										}
										else {
											var base_form = this.findById('EvnVizitPLDispSomeAdultEditForm').getForm();

											if ( !this.findById('EVPLDSAEF_EvnUslugaPanel').hidden && !this.findById('EVPLDSAEF_EvnUslugaPanel').collapsed && this.findById('EVPLDSAEF_EvnUslugaGrid').getStore().getCount() > 0 ) {
												this.findById('EVPLDSAEF_EvnUslugaGrid').getView().focusRow(0);
												this.findById('EVPLDSAEF_EvnUslugaGrid').getSelectionModel().selectFirstRow();
											}
											else if ( !this.findById('EVPLDSAEF_EvnDiagPLPanel').collapsed && this.findById('EVPLDSAEF_EvnDiagPLGrid').getStore().getCount() > 0 ) {
												this.findById('EVPLDSAEF_EvnDiagPLGrid').getView().focusRow(0);
												this.findById('EVPLDSAEF_EvnDiagPLGrid').getSelectionModel().selectFirstRow();
											}
											else if ( this.action != 'view' ) {
												if ( !this.findById('EVPLDSAEF_DiagPanel').collapsed ) {
													base_form.findField('DeseaseType_id').focus(true);
												}
												else if ( !base_form.findField('ProfGoal_id').disabled ) {
													base_form.findField('ProfGoal_id').focus(true);
												}
												else {
													base_form.findField('EvnVizitPL_Time').focus(true);
												}
											}
											else {
												this.buttons[this.buttons.length - 1].focus();
											}
										}
									break;
								}
							}.createDelegate(this),
							stopEvent: true
						}],
						layout: 'fit',
						listeners: {
							'rowdblclick': function(grid, number, obj) {
								this.openEvnReceptEditWindow('edit');
							}.createDelegate(this)
						},
						loadMask: true,
						region: 'center',
						sm: new Ext.grid.RowSelectionModel({
							listeners: {
								'rowselect': function(sm, rowIndex, record) {
									var id = null;
									var selected_record = sm.getSelected();
									var toolbar = this.findById('EVPLDSAEF_EvnReceptGrid').getTopToolbar();

									if ( selected_record ) {
										id = selected_record.get('EvnUsluga_id');
									}

									toolbar.items.items[1].disable();
									toolbar.items.items[3].disable();

									if ( id ) {
										toolbar.items.items[2].enable();

										if ( this.action != 'view' ) {
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
								'load': function(store, records, index) {
									if ( store.getCount() == 0 ) {
										LoadEmptyRow(this.findById('EVPLDSAEF_EvnReceptGrid'));
									}

									// this.findById('EVPLDSAEF_EvnReceptGrid').getView().focusRow(0);
									// this.findById('EVPLDSAEF_EvnReceptGrid').getSelectionModel().selectFirstRow();
								}.createDelegate(this)
							},
							reader: new Ext.data.JsonReader({
								id: 'EvnRecept_id'
							}, [{
								mapping: 'EvnRecept_id',
								name: 'EvnRecept_id',
								type: 'int'
							}, {
								mapping: 'EvnRecept_pid',
								name: 'EvnRecept_pid',
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
								mapping: 'ReceptRemoveCauseType_id',
								name: 'ReceptRemoveCauseType_id',
								type: 'int'
							}, {
								mapping: 'Server_id',
								name: 'Server_id',
								type: 'int'
							}, {
								dateFormat: 'd.m.Y',
								mapping: 'EvnRecept_setDate',
								name: 'EvnRecept_setDate',
								type: 'date'
							}, {
								mapping: 'MedPersonal_Fio',
								name: 'MedPersonal_Fio',
								type: 'string'
							}, {
								mapping: 'Drug_Name',
								name: 'Drug_Name',
								type: 'string'
							}, {
								mapping: 'EvnRecept_Ser',
								name: 'EvnRecept_Ser',
								type: 'string'
							}, {
								mapping: 'EvnRecept_Num',
								name: 'EvnRecept_Num',
								type: 'string'
							}]),
							url: C_EVNREC_LIST
						}),
						tbar: new sw.Promed.Toolbar({
							buttons: [{
								handler: function() {
									this.openEvnReceptEditWindow('add');
								}.createDelegate(this),
								iconCls: 'add16',
								text: BTN_GRIDADD,
								tooltip: BTN_GRIDADD_TIP
							}, {
								handler: function() {
									this.openEvnReceptEditWindow('edit');
								}.createDelegate(this),
								iconCls: 'edit16',
								text: BTN_GRIDEDIT,
								tooltip: BTN_GRIDEDIT_TIP
							}, {
								handler: function() {
									this.openEvnReceptEditWindow('view');
								}.createDelegate(this),
								iconCls: 'view16',
								text: BTN_GRIDVIEW,
								tooltip: BTN_GRIDVIEW_TIP
							}, {
								handler: function() {
									this.deleteEvent('EvnRecept');
								}.createDelegate(this),
								iconCls: 'delete16',
								text: BTN_GRIDDEL,
								tooltip: BTN_GRIDDEL_TIP
							}]
						}),
						view: new Ext.grid.GridView({
							getRowClass: function (row, index) {
								var cls = '';

								if ( parseInt(row.get('ReceptRemoveCauseType_id')) > 0 ) {
									cls = cls + 'x-grid-rowgray';
								}
								else {
									cls = 'x-grid-panel'; 
								}

								return cls;
							}
						})
					})]
				}),
				this.MorbusHepatitisSpec
				/*new sw.Promed.Panel({
					border: true,
					collapsible: true,
					height: 200,
					id: 'EVPLDSAEF_PersonDispPanel',
					isLoaded: false,
					layout: 'border',
					listeners: {
						'expand': function(panel) {
							if ( panel.isLoaded === false ) {
								panel.isLoaded = true;
								panel.findById('EVPLDSAEF_PersonDispGrid').getStore().load({
									params: {
										Person_id: this.findById('EvnVizitPLDispSomeAdultEditForm').getForm().findField('Person_id').getValue(),
										Server_id: this.findById('EvnVizitPLDispSomeAdultEditForm').getForm().findField('Server_id').getValue()
									}
								});
							}
							panel.doLayout();
						}.createDelegate(this)
					},
					style: 'margin-bottom: 0.5em;',
					title: lang['7_dispansernyiy_uchet'],
					items: [ new Ext.grid.GridPanel({
						autoExpandColumn: 'autoexpand_disp',
						autoExpandMin: 100,
						border: false,
						columns: [{
							dataIndex: 'Diag_Code',
							header: lang['diagnoz'],
							hidden: false,
							resizable: false,
							sortable: true,
							width: 100
						}, {
							dataIndex: 'PersonDisp_begDate',
							header: lang['vzyat'],
							hidden: false,
							renderer: Ext.util.Format.dateRenderer('d.m.Y'),
							resizable: false,
							sortable: true,
							width: 100
						}, {
							dataIndex: 'PersonDisp_endDate',
							header: lang['snyat'],
							hidden: false,
							renderer: Ext.util.Format.dateRenderer('d.m.Y'),
							resizable: false,
							sortable: true,
							width: 100
						}, {
							dataIndex: 'PersonDisp_NextDate',
							header: lang['data_sled_yavki'],
							hidden: false,
							renderer: Ext.util.Format.dateRenderer('d.m.Y'),
							resizable: false,
							sortable: true,
							width: 100
						}, {
							dataIndex: 'LpuSection_Code',
							header: lang['otdelenie'],
							hidden: false,
							id: 'autoexpand_usluga',
							resizable: true,
							sortable: true
						}, {
							dataIndex: 'MedPersonal_Code',
							header: lang['vrach'],
							hidden: false,
							resizable: true,
							sortable: true,
							width: 100
						}, {
							dataIndex: 'LpuRegion_Name',
							header: lang['uchastok'],
							hidden: false,
							id: 'autoexpand_disp',
							resizable: true,
							sortable: true
						}],
						frame: false,
						id: 'EVPLDSAEF_PersonDispGrid',
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

								var grid = this.findById('EVPLDSAEF_PersonDispGrid');

								switch ( e.getKey() ) {
									case Ext.EventObject.DELETE:
										this.deletePersonDisp();
									break;

									case Ext.EventObject.END:
										GridEnd(grid);
									break;

									case Ext.EventObject.ENTER:
									case Ext.EventObject.F3:
									case Ext.EventObject.F4:
									case Ext.EventObject.INSERT:
										if ( !grid.getSelectionModel().getSelected() ) {
											return false;
										}

										var action = 'add';

										if ( e.getKey() == Ext.EventObject.F3 ) {
											action = 'view';
										}
										else if ( e.getKey() == Ext.EventObject.ENTER || e.getKey() == Ext.EventObject.F4 ) {
											action = 'edit';
										}

										this.openPersonDispEditWindow(action);
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
										grid.getSelectionModel().clearSelections();
										grid.getSelectionModel().fireEvent('rowselect', grid.getSelectionModel());

										if ( e.shiftKey == false ) {
											if ( !this.findById('EVPLDSAEF_EvnDirectionPanel').collapsed ) {
												this.findById('EVPLDSAEF_EvnDirectionGrid').getView().focusRow(0);
												this.findById('EVPLDSAEF_EvnDirectionGrid').getSelectionModel().selectFirstRow();
											}
											else if ( this.action == 'view' ) {
												this.buttons[this.buttons.length - 1].focus();
											}
											else {
												this.buttons[0].focus();
											}
										}
										else {
											var base_form = this.findById('EvnVizitPLDispSomeAdultEditForm').getForm();

											if ( !this.findById('EVPLDSAEF_EvnReceptPanel').collapsed ) {
												this.findById('EVPLDSAEF_EvnReceptGrid').getView().focusRow(0);
												this.findById('EVPLDSAEF_EvnReceptGrid').getSelectionModel().selectFirstRow();
											}
											else if ( !this.findById('EVPLDSAEF_EvnUslugaPanel').collapsed ) {
												this.findById('EVPLDSAEF_EvnUslugaGrid').getView().focusRow(0);
												this.findById('EVPLDSAEF_EvnUslugaGrid').getSelectionModel().selectFirstRow();
											}
											else if ( !this.findById('EVPLDSAEF_EvnDiagPLPanel').collapsed ) {
												this.findById('EVPLDSAEF_EvnDiagPLGrid').getView().focusRow(0);
												this.findById('EVPLDSAEF_EvnDiagPLGrid').getSelectionModel().selectFirstRow();
											}
											else if ( this.action != 'view' ) {
												if ( !this.findById('EVPLDSAEF_DiagPanel').collapsed ) {
													base_form.findField('DeseaseType_id').focus(true);
												}
												else if ( !base_form.findField('ProfGoal_id').disabled ) {
													base_form.findField('ProfGoal_id').focus(true);
												}
												else {
													base_form.findField('EvnVizitPL_Time').focus(true);
												}
											}
											else {
												this.buttons[this.buttons.length - 1].focus();
											}
										}
									break;
								}
							}.createDelegate(this),
							stopEvent: true
						}],
						listeners: {
							'rowdblclick': function(grid, number, obj) {
								this.openPersonDispEditWindow('edit');
							}.createDelegate(this)
						},
						loadMask: true,
						region: 'center',
						sm: new Ext.grid.RowSelectionModel({
							listeners: {
								'rowselect': function(sm, rowIndex, record) {
									var person_disp_id = null;
									var selected_record = sm.getSelected();
									var toolbar = this.grid.getTopToolbar();

									if ( selected_record ) {
										person_disp_id = selected_record.get('PersonDisp_id');
									}

									var toolbar = this.grid.getTopToolbar();

									if ( person_disp_id ) {
										toolbar.items.items[1].enable();
										toolbar.items.items[2].enable();
										toolbar.items.items[3].enable();
									}
									else {
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
							listeners: {
								'load': function(store, records, index) {
									if ( store.getCount() == 0 ) {
										LoadEmptyRow(this.findById('EVPLDSAEF_PersonDispGrid'));
									}

									// this.findById('EVPLDSAEF_PersonDispGrid').getView().focusRow(0);
									// this.findById('EVPLDSAEF_PersonDispGrid').getSelectionModel().selectFirstRow();
								}.createDelegate(this)
							},
							reader: new Ext.data.JsonReader({
								id: 'PersonDisp_id'
							}, [{
								mapping: 'PersonDisp_id',
								name: 'PersonDisp_id',
								type: 'int'
							}, {
								mapping: 'Person_id',
								name: 'Person_id',
								type: 'int'
							}, {
								mapping: 'Server_id',
								name: 'Server_id',
								type: 'int'
							}, {
								mapping: 'Diag_Code',
								name: 'Diag_Code',
								type: 'string'
							}, {
								dateFormat: 'd.m.Y',
								mapping: 'PersonDisp_begDate',
								name: 'PersonDisp_begDate',
								type: 'date'
							}, {
								dateFormat: 'd.m.Y',
								mapping: 'PersonDisp_endDate',
								name: 'PersonDisp_endDate',
								type: 'date'
							}, {
								dateFormat: 'd.m.Y',
								mapping: 'PersonDisp_NextDate',
								name: 'PersonDisp_NextDate',
								type: 'date'
							}, {
								mapping: 'LpuSection_Code',
								name: 'LpuSection_Code',
								type: 'string'
							}, {
								mapping: 'MedPersonal_Code',
								name: 'MedPersonal_Code',
								type: 'string'
							}, {
								mapping: 'LpuRegion_Name',
								name: 'LpuRegion_Name',
								type: 'string'
							}]),
							url: '/?c=PersonDisp&m=loadPersonDispGrid'
						}),
						tbar: new sw.Promed.Toolbar({
							buttons: [{
								handler: function() {
									this.openPersonDispEditWindow('add');
								}.createDelegate(this),
								iconCls: 'add16',
								text: lang['dobavit']
							}, {
								handler: function() {
									this.openPersonDispEditWindow('edit');
								}.createDelegate(this),
								iconCls: 'edit16',
								text: lang['izmenit']
							}, {
								handler: function() {
									this.openPersonDispEditWindow('view');
								}.createDelegate(this),
								iconCls: 'view16',
								text: lang['prosmotr']
							}, {
								handler: function() {
									this.deletePersonDisp();
								}.createDelegate(this),
								iconCls: 'delete16',
								text: lang['udalit']
							}]
						})
					})]
				}),
				*/
				/*
				new sw.Promed.Panel({
					border: true,
					collapsible: true,
					height: 200,
					id: 'EVPLDSAEF_EvnDirectionPanel',
					isLoaded: false,
					layout: 'border',
					listeners: {
						'expand': function(panel) {
							if ( panel.isLoaded === false ) {
								panel.isLoaded = true;
								panel.findById('EVPLDSAEF_EvnDirectionGrid').getStore().load({
									params: {
										EvnDirection_pid: this.findById('EvnVizitPLDispSomeAdultEditForm').getForm().findField('EvnVizitPL_id').getValue()
									}
								});
							}
							panel.doLayout();
						}.createDelegate(this)
					},
					style: 'margin-bottom: 0.5em;',
					title: lang['7_napravleniya'],
					items: [ new Ext.grid.GridPanel({
						autoExpandColumn: 'autoexpand_direct',
						autoExpandMin: 100,
						border: false,
						columns: [{
							dataIndex: 'EvnDirection_setDate',
							header: lang['data_vyipiski_napravleniya'],
							hidden: false,
							renderer: Ext.util.Format.dateRenderer('d.m.Y'),
							resizable: false,
							sortable: true,
							width: 150
						}, {
							dataIndex: 'EvnDirection_Num',
							header: lang['nomer_napravleniya'],
							hidden: false,
							resizable: false,
							sortable: true,
							width: 120
						}, {
							dataIndex: 'DirType_Name',
							header: lang['tip_napravleniya'],
							hidden: false,
							resizable: false,
							sortable: true,
							width: 250
						}, {
							dataIndex: 'LpuSectionProfile_Name',
							header: lang['profil'],
							hidden: false,
							id: 'autoexpand_direct',
							sortable: true
						}],
						frame: false,
						id: 'EVPLDSAEF_EvnDirectionGrid',
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

								if ( e.browserEvent.stopPropagation ) {
									e.browserEvent.stopPropagation();
								}
								else {
									e.browserEvent.cancelBubble = true;
								}

								if ( e.browserEvent.preventDefault ) {
									e.browserEvent.preventDefault();
								}
								else {
									e.browserEvent.returnValue = false;
								}

								e.returnValue = false;

								if ( Ext.isIE ) {
									e.browserEvent.keyCode = 0;
									e.browserEvent.which = 0;
								}

								var grid = this.findById('EVPLDSAEF_EvnDirectionGrid');

								switch ( e.getKey() ) {
									case Ext.EventObject.DELETE:
										this.deleteEvent('EvnDirection');
									break;

									case Ext.EventObject.END:
										GridEnd(grid);
									break;

									case Ext.EventObject.ENTER:
									case Ext.EventObject.F3:
									case Ext.EventObject.F4:
									case Ext.EventObject.INSERT:
										if ( !grid.getSelectionModel().getSelected() ) {
											return false;
										}

										var action = 'add';

										if ( e.getKey() == Ext.EventObject.F3 ) {
											action = 'view';
										}
										else if ( e.getKey() == Ext.EventObject.F4 || e.getKey() == Ext.EventObject.ENTER ) {
											action = 'edit';
										}

										this.openEvnDirectionEditWindow(action);
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
										grid.getSelectionModel().clearSelections();
										grid.getSelectionModel().fireEvent('rowselect', grid.getSelectionModel());

										if ( e.shiftKey == false ) {
											if ( this.action == 'view' ) {
												this.buttons[this.buttons.length - 1].focus();
											}
											else {
												this.buttons[0].focus();
											}
										}
										else {
											var base_form = this.findById('EvnVizitPLDispSomeAdultEditForm').getForm();

											
											//if ( !this.findById('EVPLDSAEF_PersonDispPanel').collapsed ) {
												//this.findById('EVPLDSAEF_PersonDispGrid').getView().focusRow(0);
												//this.findById('EVPLDSAEF_PersonDispGrid').getSelectionModel().selectFirstRow();
											//}
											//else 
											if ( !this.findById('EVPLDSAEF_EvnReceptPanel').collapsed ) {
												this.findById('EVPLDSAEF_EvnReceptGrid').getView().focusRow(0);
												this.findById('EVPLDSAEF_EvnReceptGrid').getSelectionModel().selectFirstRow();
											}
											else if ( !this.findById('EVPLDSAEF_EvnUslugaPanel').collapsed ) {
												this.findById('EVPLDSAEF_EvnUslugaGrid').getView().focusRow(0);
												this.findById('EVPLDSAEF_EvnUslugaGrid').getSelectionModel().selectFirstRow();
											}
											else if ( !this.findById('EVPLDSAEF_EvnDiagPLPanel').collapsed ) {
												this.findById('EVPLDSAEF_EvnDiagPLGrid').getView().focusRow(0);
												this.findById('EVPLDSAEF_EvnDiagPLGrid').getSelectionModel().selectFirstRow();
											}
											else if ( this.action != 'view' ) {
												if ( !this.findById('EVPLDSAEF_DiagPanel').collapsed ) {
													base_form.findField('DeseaseType_id').focus(true);
												}
												else if ( !base_form.findField('ProfGoal_id').disabled ) {
													base_form.findField('ProfGoal_id').focus(true);
												}
												else {
													base_form.findField('EvnVizitPL_Time').focus(true);
												}
											}
											else {
												this.buttons[this.buttons.length - 1].focus();
											}
										}
									break;
								}
							}.createDelegate(this),
							stopEvent: true
						}],
						layout: 'fit',
						listeners: {
							'rowdblclick': function(grid, number, obj) {
								this.openEvnDirectionEditWindow('edit');
							}.createDelegate(this)
						},
						loadMask: true,
						region: 'center',
						sm: new Ext.grid.RowSelectionModel({
							listeners: {
								'rowselect': function(sm, rowIndex, record) {
									var evn_direction_id = null;
									var selected_record = sm.getSelected();
									var toolbar = this.grid.getTopToolbar();

									if ( selected_record ) {
										evn_direction_id = selected_record.get('EvnDirection_id');
									}

									if ( evn_direction_id ) {
										toolbar.items.items[1].enable();
										toolbar.items.items[2].enable();
										toolbar.items.items[3].enable();
									}
									else {
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
							listeners: {
								'load': function(store, records, index) {
									if ( store.getCount() == 0 ) {
										LoadEmptyRow(this.findById('EVPLDSAEF_EvnDirectionGrid'));
									}

									// this.findById('EVPLDSAEF_EvnDirectionGrid').getView().focusRow(0);
									// this.findById('EVPLDSAEF_EvnDirectionGrid').getSelectionModel().selectFirstRow();
								}.createDelegate(this)
							},
							reader: new Ext.data.JsonReader({
								id: 'EvnDirection_id'
							}, [{
								mapping: 'EvnDirection_id',
								name: 'EvnDirection_id',
								type: 'int'
							}, {
								mapping: 'EvnDirection_pid',
								name: 'EvnDirection_pid',
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
								mapping: 'Diag_id',
								name: 'Diag_id',
								type: 'int'
							}, {
								mapping: 'DirType_id',
								name: 'DirType_id',
								type: 'int'
							}, {
								mapping: 'LpuSection_id',
								name: 'LpuSection_id',
								type: 'int'
							}, {
								mapping: 'MedPersonal_id',
								name: 'MedPersonal_id',
								type: 'int'
							}, {
								mapping: 'MedPersonal_zid',
								name: 'MedPersonal_zid',
								type: 'int'
							}, {
								mapping: 'LpuSectionProfile_id',
								name: 'LpuSectionProfile_id',
								type: 'int'
							}, {
								mapping: 'EvnDirection_Num',
								name: 'EvnDirection_Num',
								type: 'int'
							}, {
								mapping: 'EvnDirection_Descr',
								name: 'EvnDirection_Descr',
								type: 'string'
							}, {
								dateFormat: 'd.m.Y',
								mapping: 'EvnDirection_setDate',
								name: 'EvnDirection_setDate',
								type: 'date'
							}, {
								mapping: 'DirType_Name',
								name: 'DirType_Name',
								type: 'string'
							}, {
								mapping: 'LpuSectionProfile_Name',
								name: 'LpuSectionProfile_Name',
								type: 'string'
							}]),
							url: '/?c=EvnDirection&m=loadEvnDirectionGrid'
						}),
						tbar: new sw.Promed.Toolbar({
							buttons: [{
								handler: function() {
									this.openEvnDirectionEditWindow('add');
								}.createDelegate(this),
								iconCls: 'add16',
								text: BTN_GRIDADD,
								tooltip: BTN_GRIDADD_TIP
							}, {
								handler: function() {
									this.openEvnDirectionEditWindow('edit');
								}.createDelegate(this),
								iconCls: 'edit16',
								text: BTN_GRIDEDIT,
								tooltip: BTN_GRIDEDIT_TIP
							}, {
								handler: function() {
									this.openEvnDirectionEditWindow('view');
								}.createDelegate(this),
								iconCls: 'view16',
								text: BTN_GRIDVIEW,
								tooltip: BTN_GRIDVIEW_TIP
							}, {
								handler: function() {
									this.deleteEvent('EvnDirection');
								}.createDelegate(this),
								iconCls: 'delete16',
								text: BTN_GRIDDEL,
								tooltip: BTN_GRIDDEL_TIP
							}]
						})
					})]
				})*/
				],
				
				reader: new Ext.data.JsonReader(
				{
					success: function() 
					{ 
						//
					}
				}, 
				[
					{ name: 'accessType' },
					{ name: 'EvnVizitPL_id' },
					{ name: 'EvnPL_id' },
					{ name: 'EvnUslugaCommon_id' },
					{ name: 'Person_id' },
					{ name: 'PersonEvn_id' },
					{ name: 'Server_id' },
					{ name: 'TimetableGraf_id' },
					{ name: 'EvnDirection_id' },
					{ name: 'EvnVizitPL_setDate' },
					{ name: 'EvnVizitPL_setTime' },
					{ name: 'EvnVizitPL_Uet' },
					{ name: 'EvnVizitPL_UetOMS' },
					{ name: 'VizitClass_id' },
					{ name: 'DispWowSpec_id' },
					{ name: 'LpuSection_id' },
					{ name: 'MedPersonal_id' },
					{ name: 'MedPersonal_sid' },
					{ name: 'ServiceType_id' },
					{ name: 'VizitType_id' },
					{ name: 'PayType_id' },
					{ name: 'ProfGoal_id' },
					{ name: 'Diag_agid' },
					{ name: 'Diag_id' },
					{ name: 'DeseaseType_id' },
					{ name: 'UslugaComplex_uid' },
					{ name: 'action'}
				]),
				region: 'center',
				timeout: 300,
				url: '/?c=EvnPL&m=saveEvnVizitPL'
			})]
		});
		
		sw.Promed.swEvnVizitPLDispSomeAdultEditWindow.superclass.initComponent.apply(this, arguments);

		this.findById('EVPLDSAEF_LpuSectionCombo').addListener('change', function(combo, newValue, oldValue) {
			if ( getGlobalOptions().region && getGlobalOptions().region.nick == 'ufa' ) {
				var base_form = this.findById('EvnVizitPLDispSomeAdultEditForm').getForm();

				var uslugacomplex_combo = base_form.findField('UslugaComplex_uid');

				if ( !newValue ) {
					// uslugacomplex_combo.setLpuLevelCode(0);
					return false;
				}

				var index = combo.getStore().findBy(function(rec) {
					if ( rec.get('LpuSection_id') == newValue ) {
						return true;
					}
					else {
						return false;
					}
				});
				var record = combo.getStore().getAt(index);

				uslugacomplex_combo.clearValue();
				uslugacomplex_combo.lastQuery = '';
				uslugacomplex_combo.getStore().removeAll();
				uslugacomplex_combo.getStore().baseParams.query = '';

				if ( record ) {
					// uslugacomplex_combo.setLpuLevelCode(record.get('LpuSectionProfile_Code'));
					uslugacomplex_combo.getStore().load();
				}
			}
		}.createDelegate(this));

		this.findById('EVPLDSAEF_MedPersonalCombo').addListener('change', function(combo, newValue, oldValue) {
			if ( getGlobalOptions().region && getGlobalOptions().region.nick == 'ufa' ) {
				var base_form = this.findById('EvnVizitPLDispSomeAdultEditForm').getForm();

				var uslugacomplex_combo = base_form.findField('UslugaComplex_uid');

				if ( !newValue ) {
					// uslugacomplex_combo.setLpuLevelCode(0);
					return false;
				}

				var index = combo.getStore().findBy(function(rec) {
					if ( rec.get('MedStaffFact_id') == newValue ) {
						return true;
					}
					else {
						return false;
					}
				});
				var record = combo.getStore().getAt(index);

				uslugacomplex_combo.clearValue();
				uslugacomplex_combo.lastQuery = '';
				uslugacomplex_combo.getStore().removeAll();
				uslugacomplex_combo.getStore().baseParams.query = '';

				if ( record ) {
					// uslugacomplex_combo.setLpuLevelCode(record.get('LpuSectionProfile_Code'));
					uslugacomplex_combo.getStore().load();
				}
			}
		}.createDelegate(this));
	},
	keys: [{
		alt: true,
		fn: function(inp, e) {
			var current_window = Ext.getCmp('EvnVizitPLDispSomeAdultEditWindow');

			switch ( e.getKey() ) {
				case Ext.EventObject.C:
					current_window.doSave({
						ignoreEvnUslugaCountCheck: false
					});
				break;

				case Ext.EventObject.J:
					current_window.onCancelAction();
				break;
				
				case Ext.EventObject.NUM_ONE:
				case Ext.EventObject.ONE:
					current_window.findById('EVPLDSAEF_DirectInfoPanel').toggleCollapse();
				break;
				
				case Ext.EventObject.NUM_TWO:
				case Ext.EventObject.TWO:
					current_window.EvnXmlPanel.toggleCollapse();
				break;

				case Ext.EventObject.NUM_THREE:
				case Ext.EventObject.THREE:
					current_window.findById('EVPLDSAEF_DiagPanel').toggleCollapse();
				break;

				case Ext.EventObject.NUM_FOUR:
				case Ext.EventObject.FOUR:
					current_window.findById('EVPLDSAEF_EvnDiagPLPanel').toggleCollapse();
				break;

				case Ext.EventObject.NUM_FIVE:
				case Ext.EventObject.FIVE:
					if  ( !current_window.findById('EVPLDSAEF_EvnUslugaPanel').hidden ) {
						current_window.findById('EVPLDSAEF_EvnUslugaPanel').toggleCollapse();
					}
				break;

				case Ext.EventObject.NUM_SIX:
				case Ext.EventObject.SIX:
					current_window.findById('EVPLDSAEF_EvnReceptPanel').toggleCollapse();
				break;
				/*
				case Ext.EventObject.NUM_SEVEN:
				case Ext.EventObject.SEVEN:
					current_window.findById('EVPLDSAEF_EvnDirectionPanel').toggleCollapse();
				break;

				case Ext.EventObject.NUM_EIGHT:
				case Ext.EventObject.EIGHT:
					
				break;
				*/
			}
		},
		key: [
			Ext.EventObject.C,
			Ext.EventObject.FOUR,
			Ext.EventObject.FIVE,
			Ext.EventObject.J,
			Ext.EventObject.NUM_FOUR,
			Ext.EventObject.NUM_FIVE,
			Ext.EventObject.NUM_ONE,
			Ext.EventObject.NUM_SEVEN,
			Ext.EventObject.NUM_SIX,
			Ext.EventObject.NUM_TWO,
			Ext.EventObject.NUM_THREE,
			Ext.EventObject.ONE,
			Ext.EventObject.SEVEN,
			Ext.EventObject.SIX,
			Ext.EventObject.TWO,
			Ext.EventObject.THREE,
			Ext.EventObject.EIGHT,
			Ext.EventObject.NUM_EIGHT
		],
		stopEvent: true
	}],
	layout: 'border',
	listeners: {
		'hide': function(win) {
			win.onHide({
				EvnUslugaGridIsModified: win.EvnUslugaGridIsModified
			});
		},
		'maximize': function(win) {
			win.EvnXmlPanel.doLayout();
			win.findById('EVPLDSAEF_DiagPanel').doLayout();
			win.findById('EVPLDSAEF_EvnDiagPLPanel').doLayout();
			//win.findById('EVPLDSAEF_EvnDirectionPanel').doLayout();
			win.findById('EVPLDSAEF_EvnReceptPanel').doLayout();
			win.findById('EVPLDSAEF_EvnUslugaPanel').doLayout();
			//win.findById('EVPLDSAEF_PersonDispPanel').doLayout();
		},
		'restore': function(win) {
			win.EvnXmlPanel.doLayout();
			win.findById('EVPLDSAEF_DiagPanel').doLayout();
			win.findById('EVPLDSAEF_EvnDiagPLPanel').doLayout();
			//win.findById('EVPLDSAEF_EvnDirectionPanel').doLayout();
			win.findById('EVPLDSAEF_EvnReceptPanel').doLayout();
			win.findById('EVPLDSAEF_EvnUslugaPanel').doLayout();
			//win.findById('EVPLDSAEF_PersonDispPanel').doLayout();
		}
	},
	maximizable: true,
	minHeight: 550,
	minWidth: 700,
	modal: true,
	/*
	loadDirection: function()
	{
		var form = this;
		var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Загрузка данных направления..." });
		loadMask.show();
		var d = {EvnDirection_id:null,TimetableGraf_id:null,EvnDirection_Num:null,EvnDirection_setDate:null,DirType_id:null,Lpu_did:null,LpuSectionProfile_id:null,EvnDirection_setDateTime:null,Diag_id:null,EvnDirection_Descr:null,MedStaffFact_id:null,MedStaffFact_zid:null};
		form.setDirection(d);
		Ext.Ajax.request(
		{
			url: '/?c=EvnDirection&m=loadEvnDirectionEditForm',
			params: 
			{	
				EvnVizitPL_id: form.findById('EvnVizitPLDispSomeAdultEditForm').getForm().findField('EvnVizitPL_id').getValue()
			},
			callback: function(options, success, response) 
			{
				loadMask.hide();
				if (success)
				{
					var result = Ext.util.JSON.decode(response.responseText);
					if ( result.Error_Msg == '' || result.Error_Msg == null )
					{
						// Заполняем поля направления
						form.setDirection(result[0]);
					}
				}
				else
				{
					Ext.Msg.alert(lang['oshibka'], lang['vo_vremya_chteniya_napravleniya_proizoshla_oshibka']);
				}
			}
		});
	},
	setDirection: function(dt)
	{
		var bf = this.findById('EVPLDSAEF_DirectInfoPanel');
		if (dt)
		{
			bf.findById('EDIREF_EvnDirection_id').setValue(dt.EvnDirection_id);
			bf.findById('EDIREF_TimetableGraf_id').setValue(dt.TimetableGraf_id);
			bf.findById('EDIREF_EvnDirection_Num').setValue(dt.EvnDirection_Num);
			bf.findById('EDIREF_EvnDirection_setDate').setValue(dt.EvnDirection_setDate);
			bf.findById('EDIREF_DirType_id').setValue(dt.DirType_id);
			bf.findById('EDIREF_Lpu_did').setValue(dt.Lpu_did);
			bf.findById('EDIREF_LpuSectionProfile_id').setValue(dt.LpuSectionProfile_id);
			bf.findById('EDIREF_EvnDirection_setDateTime').setValue(dt.EvnDirection_setDateTime);
			bf.findById('EDIREF_Diag_id').setValue(dt.Diag_id);
			if ( dt.Diag_id != null && dt.Diag_id.toString().length > 0 ) {
				bf.findById('EDIREF_Diag_id').getStore().load({
					callback: function() {
						bf.findById('EDIREF_Diag_id').getStore().each(function(record) {
							if ( record.get('Diag_id') == dt.Diag_id ) {
								bf.findById('EDIREF_Diag_id').fireEvent('select', bf.findById('EDIREF_Diag_id'), record, 0);
							}
						});
						bf.findById('EDIREF_Diag_id').setDisabled(true);
					},
					params: { where: "where DiagLevel_id = 4 and Diag_id = " + dt.Diag_id }
				});
			}
			bf.findById('EDIREF_Diag_id').setDisabled(true);
			
			bf.findById('EDIREF_MedStaffFact_id').setValue(dt.MedStaffFact_id);
			bf.findById('EDIREF_MedStaffFact_zid').setValue(dt.MedStaffFact_zid);
					
			bf.findById('EDIREF_EvnDirection_Descr').setValue(dt.EvnDirection_Descr);
			if (dt.MedStaffFact_id != null && dt.MedStaffFact_id.toString().length > 0 ) {
				bf.findById('EDIREF_MedStaffFact_id').getStore().load({
					params: { MedPersonal_id: dt.MedStaffFact_id },
					callback: function() {
						bf.findById('EDIREF_MedStaffFact_id').setValue(dt.MedStaffFact_id);
						if (dt.MedStaffFact_zid != null && dt.MedStaffFact_zid.toString().length > 0 ) {
							bf.findById('EDIREF_MedStaffFact_zid').getStore().load({
								params: { MedPersonal_id: dt.MedStaffFact_zid },
								callback: function() {
									bf.findById('EDIREF_MedStaffFact_zid').setValue(dt.MedStaffFact_zid);
								}
							});
						}
					}
				});
			}
		}
	},
	*/
	/* Проверка на то, что у врача нет посещений на указанную дату и что на данную дату есть направление (в расписании) */
	getDirectionIf: function()
	{
		var form = this;
		var bf = this.findById('EvnVizitPLDispSomeAdultEditForm').getForm();
		
		/*
		var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Проверка наличия входящего направления" });
		loadMask.show();
		*/
		var setDate = bf.findField('EvnVizitPL_setDate').getValue();
		var Person_id = bf.findField('Person_id').getValue();
		var EvnVizitPL_id = bf.findField('EvnVizitPL_id').getValue();
		var ddate = form.DirectionInfoData.getFieldValue('EvnDirection_getDate');
		
		var EvnDirection_id = bf.findField('EvnDirection_id').getValue();
		
		/*
		log(ddate);
		log(setDate);
		log(EvnDirection_id);
		log((ddate != setDate));
		*/
		if (((EvnDirection_id>0) && (ddate) && (ddate != setDate)) || (form.action=='add'))
		{
			EvnDirection_id = null;
		}
		
		if (setDate && !Ext.isEmpty(form.userMedStaffFact.MedStaffFact_id) && !Ext.isEmpty(form.userMedStaffFact.Lpusection_id) && form.TimetableGraf_id)
		{
			form.DirectionInfoData.getStore().load(
			{
				params: 
				{
					TimetableGraf_id: form.TimetableGraf_id,
					EvnDirection_id: EvnDirection_id,
					EvnVizitPL_id: EvnVizitPL_id,
					Person_id: Person_id,
					MedStaffFact_id: form.userMedStaffFact.MedStaffFact_id,
					LpuSection_id: form.userMedStaffFact.Lpusection_id,
					setDate: Ext.util.Format.date(setDate, 'd.m.Y')
				},
				callback: function()
				{
					var form = Ext.getCmp('EvnVizitPLDispSomeAdultEditWindow');
					if (form.DirectionInfoData.getStore().getCount()>0)
					{
						// Экспандим панель входящего направления 
						form.DirectionInfoPanel.expand();
						// и используем полученный EvnDirection_id 
						bf.findField('EvnDirection_id').setValue(form.DirectionInfoData.getFieldValue('EvnDirection_id'));
					}
					else 
					{
						// Коллапсим и скрываем (?) панель направления
						form.DirectionInfoPanel.collapse();
						// и обнуляем полученный EvnDirection_id
						bf.findField('EvnDirection_id').setValue(null);
					}
				}
			});
		}
	},
	onCancelAction: function() {
		var evn_vizit_pl_id = this.findById('EvnVizitPLDispSomeAdultEditForm').getForm().findField('EvnVizitPL_id').getValue();

		if ( evn_vizit_pl_id > 0 ) {
			switch ( this.action ) {
				case 'add':
					// удалить посещение
					// закрыть окно после успешного удаления
					var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Удаление посещения..." });
					loadMask.show();

					Ext.Ajax.request({
						callback: function(options, success, response) {
							loadMask.hide();

							if ( success ) {
								this.hide();
							}
							else {
								sw.swMsg.alert(lang['oshibka'], lang['pri_udalenii_posescheniya_voznikli_oshibki']);
							}
						}.createDelegate(this),
						params: {
							Evn_id: evn_vizit_pl_id
						},
						url: '/?c=Evn&m=deleteEvn'
					});
				break;

				case 'edit':
				case 'view':
					this.hide();
				break;
			}
		}
		else {
			this.hide();
		}
	},
	onHide: Ext.emptyFn,
	openEvnDiagPLEditWindow: function(action) {
		if ( action != 'add' && action != 'edit' && action != 'view' ) {
			return false;
		}

		var base_form = this.findById('EvnVizitPLDispSomeAdultEditForm').getForm();
		var grid = this.findById('EVPLDSAEF_EvnDiagPLGrid');

		if ( this.action == 'view') {
			if ( action == 'add') {
				return false;
			}
			else if ( action == 'edit' ) {
				action = 'view';
			}
		}

		if ( getWnd('swEvnDiagPLEditWindow').isVisible() ) {
			sw.swMsg.alert(lang['soobschenie'], lang['okno_redaktirovaniya_diagnoza_uje_otkryito']);
			return false;
		}

		if ( action == 'add' && base_form.findField('EvnVizitPL_id').getValue() == 0 ) {
			this.doSave({
				ignoreEvnUslugaCountCheck: true,
				openChildWindow: function() {
					this.openEvnDiagPLEditWindow(action);
				}.createDelegate(this)
			});
			return false;
		}

		var params = new Object();

		var person_id = this.findById('EVPLDSAEF_PersonInformationFrame').getFieldValue('Person_id');
		var person_birthday = this.findById('EVPLDSAEF_PersonInformationFrame').getFieldValue('Person_Birthday');
		var person_firname = this.findById('EVPLDSAEF_PersonInformationFrame').getFieldValue('Person_Firname');
		var person_secname = this.findById('EVPLDSAEF_PersonInformationFrame').getFieldValue('Person_Secname');
		var person_surname = this.findById('EVPLDSAEF_PersonInformationFrame').getFieldValue('Person_Surname');

		var record;
		var vizit_combo_data = new Array();

		var evn_vizit_pl_id = base_form.findField('EvnVizitPL_id').getValue();
		var evn_vizit_pl_set_date = base_form.findField('EvnVizitPL_setDate').getValue();
		var lpu_section_name = '';
		var lpu_section_id = base_form.findField('LpuSection_id').getValue();
		var med_personal_fio = '';
		var med_personal_id = null;
		var med_staff_fact_id = base_form.findField('MedStaffFact_id').getValue();

		if ( action == 'add' ) {
			params.EvnDiagPL_id = 0;
			params.EvnVizitPL_id = evn_vizit_pl_id;
			params.Person_id = base_form.findField('Person_id').getValue();
			params.PersonEvn_id = base_form.findField('PersonEvn_id').getValue();
			params.Server_id = base_form.findField('Server_id').getValue();
		}
		else {
			var selected_record = grid.getSelectionModel().getSelected();

			if ( !selected_record || !selected_record.get('EvnDiagPL_id') ) {
				return false;
			}

			if ( selected_record.get('accessType') != 'edit' ) {
				action = 'view';
			}

			params = selected_record.data;
		}

		// Формируем vizit_combo_data
		record = base_form.findField('LpuSection_id').getStore().getById(lpu_section_id);
		if ( record ) {
			lpu_section_name = record.get('LpuSection_Name');
		}

		record = base_form.findField('MedStaffFact_id').getStore().getById(med_staff_fact_id);
		if ( record ) {
			med_personal_fio = record.get('MedPersonal_Fio');
			med_personal_id = record.get('MedPersonal_id');
		}

		if ( !evn_vizit_pl_set_date || !lpu_section_id || !med_personal_id ) {
			sw.swMsg.alert(lang['soobschenie'], lang['ne_zadanyi_obyazatelnyie_parametryi_posescheniya']);
			return false;
		}

		vizit_combo_data.push({
			EvnVizitPL_id: evn_vizit_pl_id,
			LpuSection_id: lpu_section_id,
			MedPersonal_id: med_personal_id,
			EvnVizitPL_Name: Ext.util.Format.date(evn_vizit_pl_set_date, 'd.m.Y') + ' / ' + lpu_section_name + ' / ' + med_personal_fio,
			EvnVizitPL_setDate: evn_vizit_pl_set_date
		});

		getWnd('swEvnDiagPLEditWindow').show({
			action: action,
			callback: function(data) {
				if ( !data || !data.evnDiagPLData ) {
					return false;
				}

				var record = grid.getStore().getById(data.evnDiagPLData[0].EvnDiagPL_id);

				if ( !record ) {
					if ( grid.getStore().getCount() == 1 && !grid.getStore().getAt(0).get('EvnDiagPL_id') ) {
						grid.getStore().removeAll();
					}

					grid.getStore().loadData(data.evnDiagPLData, true);
				}
				else {
					var grid_fields = new Array();
					var i = 0;

					grid.getStore().fields.eachKey(function(key, item) {
						grid_fields.push(key);
					});

					for ( i = 0; i < grid_fields.length; i++ ) {
						record.set(grid_fields[i], data.evnDiagPLData[0][grid_fields[i]]);
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
			Person_Surname: person_surname,
			vizitComboData: vizit_combo_data
		});
	},
	/*openEvnDirectionEditWindow: function(action) {
		if ( action != 'add' && action != 'edit' && action != 'view' ) {
			return false;
		}

		//return false;

		var base_form = this.findById('EvnVizitPLDispSomeAdultEditForm').getForm();
		var grid = this.findById('EVPLDSAEF_EvnDirectionGrid');

		if ( this.action == 'view') {
			if ( action == 'add') {
				return false;
			}
			else if ( action == 'edit' ) {
				action = 'view';
			}
		}

		if ( getWnd('swEvnDirectionEditWindow').isVisible() ) {
			sw.swMsg.alert(lang['soobschenie'], lang['okno_vyipiski_napravleniya_uje_otkryito']);
			return false;
		}

		if ( action == 'add' && base_form.findField('EvnVizitPL_id').getValue() == 0 ) {
			this.doSave({
				ignoreEvnUslugaCountCheck: true,
				openChildWindow: function() {
					this.openEvnDirectionEditWindow(action);
				}.createDelegate(this),
				print: false
			});
			return false;
		}

		var params = new Object();

		var person_id = this.findById('EVPLDSAEF_PersonInformationFrame').getFieldValue('Person_id');
		var person_birthday = this.findById('EVPLDSAEF_PersonInformationFrame').getFieldValue('Person_Birthday');
		var person_firname = this.findById('EVPLDSAEF_PersonInformationFrame').getFieldValue('Person_Firname');
		var person_secname = this.findById('EVPLDSAEF_PersonInformationFrame').getFieldValue('Person_Secname');
		var person_surname = this.findById('EVPLDSAEF_PersonInformationFrame').getFieldValue('Person_Surname');

		if ( action == 'add' ) {
			params.EvnDirection_id = 0;
			params.EvnDirection_pid = base_form.findField('EvnVizitPL_id').getValue();
			params.Person_id = base_form.findField('Person_id').getValue();
			params.PersonEvn_id = base_form.findField('PersonEvn_id').getValue();
			params.Server_id = base_form.findField('Server_id').getValue();
		}
		else {
			var selected_record = grid.getSelectionModel().getSelected();

			if ( !selected_record || !selected_record.get('EvnDirection_id') ) {
				return false;
			}

			params = selected_record.data;
		}

		getWnd('swEvnDirectionEditWindow').show({
			action: action,
			callback: function(data) {
				if ( !data || !data.evnDirectionData ) {
					return false;
				}

				var record = grid.getStore().getById(data.evnDirectionData.EvnDirection_id);

				if ( !record ) {
					if ( grid.getStore().getCount() == 1 && !grid.getStore().getAt(0).get('EvnDirection_id') ) {
						grid.getStore().removeAll();
					}

					grid.getStore().loadData(data.evnDirectionData, true);
				}
				else {
					var evn_direction_fields = new Array();
					var i = 0;

					grid.getStore().fields.eachKey(function(key, item) {
						evn_direction_fields.push(key);
					});

					for ( i = 0; i < evn_direction_fields.length; i++ ) {
						record.set(evn_direction_fields[i], data.evnDirectionData[evn_direction_fields[i]]);
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
	},*/
	openEvnReceptEditWindow: function(action) {
		if ( action != 'add' && action != 'edit' && action != 'view' ) {
			return false;
		}

		var base_form = this.findById('EvnVizitPLDispSomeAdultEditForm').getForm();
		var grid = this.findById('EVPLDSAEF_EvnReceptGrid');

		if ( this.action == 'view') {
			if ( action == 'add') {
				return false;
			}
			else if ( action == 'edit' ) {
				action = 'view';
			}
		}

		if ( getWnd('swEvnReceptEditWindow').isVisible() ) {
			sw.swMsg.alert(lang['soobschenie'], lang['okno_redaktirovaniya_retsepta_uje_otkryito']);
			return false;
		}

		if ( action == 'add' && base_form.findField('EvnVizitPL_id').getValue() == 0 ) {
			this.doSave({
				ignoreEvnUslugaCountCheck: true,
				openChildWindow: function() {
					this.openEvnReceptEditWindow(action);
				}.createDelegate(this),
				print: false
			});
			return false;
		}

		var params = new Object();

		if ( action == 'add' ) {
			var evn_vizit_pl_set_date = base_form.findField('EvnVizitPL_setDate').getValue();
			var lpu_section_id = base_form.findField('LpuSection_id').getValue();
			var med_staff_fact_id = base_form.findField('MedStaffFact_id').getValue();

			if ( !evn_vizit_pl_set_date || !lpu_section_id || !med_staff_fact_id ) {
				sw.swMsg.alert(lang['soobschenie'], lang['ne_zadanyi_obyazatelnyie_parametryi_posescheniya']);
				return false;
			}

			var record = base_form.findField('MedStaffFact_id').getStore().getById(med_staff_fact_id);
			if ( !record ) {
				return false;
			}

			params.Diag_id = base_form.findField('Diag_id').getValue();
			params.EvnRecept_id = 0;
			params.EvnRecept_pid = base_form.findField('EvnVizitPL_id').getValue();
			params.EvnRecept_setDate = evn_vizit_pl_set_date;
			params.LpuSection_id = lpu_section_id;
			params.MedPersonal_id = med_staff_fact_id; // record.get('MedPersonal_id');
			params.Person_id = base_form.findField('Person_id').getValue();
			params.PersonEvn_id = base_form.findField('PersonEvn_id').getValue();
			params.Server_id = base_form.findField('Server_id').getValue();
		}
		else {
			var selected_record = grid.getSelectionModel().getSelected();

			if ( !selected_record || !selected_record.get('EvnRecept_id') ) {
				return false;
			}

			params.EvnRecept_id = selected_record.get('EvnRecept_id');
			params.Person_id = selected_record.get('Person_id');
			params.PersonEvn_id = selected_record.get('PersonEvn_id');
			params.Server_id = selected_record.get('Server_id');
		}

		params.action = action;
		params.callback = function(data) {
			if ( !data || !data.EvnReceptData ) {
				return false;
			}

			var record = grid.getStore().getById(data.EvnReceptData.EvnRecept_id);

			if ( !record ) {
				if ( grid.getStore().getCount() == 1 && !grid.getStore().getAt(0).get('EvnRecept_id') ) {
					grid.getStore().removeAll();
				}

				grid.getStore().loadData([ data.EvnReceptData ], true);
			}
			else {
				var grid_fields = new Array();
				var i = 0;

				grid.getStore().fields.eachKey(function(key, item) {
					grid_fields.push(key);
				});

				for ( i = 0; i < grid_fields.length; i++ ) {
					record.set(grid_fields[i], data.EvnReceptData[grid_fields[i]]);
				}

				record.commit();
			}
		}.createDelegate(this);
		params.onHide = function() {
			grid.getView().focusRow(0);
			grid.getSelectionModel().selectFirstRow();
		}.createDelegate(this);

		getWnd('swEvnReceptEditWindow').show(params);
    },
	openEvnUslugaEditWindow: function(action) {
		if ( getGlobalOptions().region && getGlobalOptions().region.nick.inlist([ 'pskov' ]) ) {
			return false;
		}

		if ( action != 'add' && action != 'edit' && action != 'view' ) {
			return false;
		}

		var base_form = this.findById('EvnVizitPLDispSomeAdultEditForm').getForm();
		var grid = this.findById('EVPLDSAEF_EvnUslugaGrid');

		if ( this.action == 'view') {
			if ( action == 'add') {
				return false;
			}
			else if ( action == 'edit' ) {
				action = 'view';
			}
		}

		var params = new Object();

		params.action = action;
		params.allowDispSomeAdultLabOnly = true;
		params.Sex_Code = this.Sex_Code;
		
		params.callback = function(data) {
			if ( !data || !data.evnUslugaData ) {
				grid.getStore().load({
					params: {
						pid: base_form.findField('EvnVizitPL_id').getValue()
					}
				});
				return false;
			}

			var record = grid.getStore().getById(data.evnUslugaData.EvnUsluga_id);

			if ( !record ) {
				if ( grid.getStore().getCount() == 1 && !grid.getStore().getAt(0).get('EvnUsluga_id') ) {
					grid.getStore().removeAll();
				}

				grid.getStore().loadData([ data.evnUslugaData ], true);
			}
			else {
				var grid_fields = new Array();
				var i = 0;

				grid.getStore().fields.eachKey(function(key, item) {
					grid_fields.push(key);
				});

				for ( i = 0; i < grid_fields.length; i++ ) {
					record.set(grid_fields[i], data.evnUslugaData[grid_fields[i]]);
				}

				record.commit();
			}

			this.EvnUslugaGridIsModified = true;
			this.uetValuesRecount();
		}.createDelegate(this);
		params.onHide = function() {
			grid.getView().focusRow(0);
			grid.getSelectionModel().selectFirstRow();
		}.createDelegate(this);
		params.parentClass = 'EvnVizit';
		params.Person_id = this.findById('EVPLDSAEF_PersonInformationFrame').getFieldValue('Person_id');
		params.Person_Birthday = this.findById('EVPLDSAEF_PersonInformationFrame').getFieldValue('Person_Birthday');
		params.Person_Firname = this.findById('EVPLDSAEF_PersonInformationFrame').getFieldValue('Person_Firname');
		params.Person_Secname = this.findById('EVPLDSAEF_PersonInformationFrame').getFieldValue('Person_Secname');
		params.Person_Surname = this.findById('EVPLDSAEF_PersonInformationFrame').getFieldValue('Person_Surname');

		// Собрать данные для ParentEvnCombo
		var parent_evn_combo_data = new Array();

		// Формируем parent_evn_combo_data
		var evn_vizit_id = base_form.findField('EvnVizitPL_id').getValue();
		var evn_vizit_set_date = base_form.findField('EvnVizitPL_setDate').getValue();
		var evn_vizit_set_time = base_form.findField('EvnVizitPL_setTime').getValue();
		var lpu_section_id = base_form.findField('LpuSection_id').getValue();
		var lpu_section_name = '';
		var med_personal_fio = '';
		var med_personal_id = null;
		var med_staff_fact_id = base_form.findField('MedStaffFact_id').getValue();

		if ( action == 'add' && (!evn_vizit_set_date || !lpu_section_id || !med_staff_fact_id) ) {
			sw.swMsg.alert(lang['oshibka'], lang['ne_zapolnenyi_obyazatelnyie_polya_po_posescheniyu']);
			return false;
		}

		record = base_form.findField('LpuSection_id').getStore().getById(lpu_section_id);
		if ( record ) {
			lpu_section_name = record.get('LpuSection_Name');
		}

		record = base_form.findField('MedStaffFact_id').getStore().getById(med_staff_fact_id);
		if ( record ) {
			med_personal_fio = record.get('MedPersonal_Fio');
			med_personal_id = record.get('MedPersonal_id');
		}

		parent_evn_combo_data.push({
			Evn_id: evn_vizit_id,
			Evn_Name: Ext.util.Format.date(evn_vizit_set_date, 'd.m.Y') + ' / ' + lpu_section_name + ' / ' + med_personal_fio,
			Evn_setDate: evn_vizit_set_date,
			Evn_setTime: evn_vizit_set_time,
			MedStaffFact_id: med_staff_fact_id,
			LpuSection_id: lpu_section_id,
			MedPersonal_id: med_personal_id
		});

		switch ( action ) {
			case 'add':
				if ( base_form.findField('EvnVizitPL_id').getValue() == 0 ) {
					this.doSave({
						ignoreEvnUslugaCountCheck: true,
						openChildWindow: function() {
							this.openEvnUslugaEditWindow(action);
						}.createDelegate(this)
					});
					return false;
				}

				params.formParams = {
					Person_id: base_form.findField('Person_id').getValue(),
					PersonEvn_id: base_form.findField('PersonEvn_id').getValue(),
					Server_id: base_form.findField('Server_id').getValue()
				}
				params.parentEvnComboData = parent_evn_combo_data;
				
				if ( getGlobalOptions().region && getGlobalOptions().region.nick != 'perm' ) {
					params.formParams.EvnUslugaCommon_id = 0;
					params.formParams.EvnUslugaCommon_rid = base_form.findField('EvnVizitPL_id').getValue();

					getWnd('swEvnUslugaEditWindow').show(params);
				}
				else {
					// Открываем форму выбора класса услуги
					if ( getWnd('swEvnUslugaSetWindow').isVisible() ) {
						sw.swMsg.alert(lang['soobschenie'], lang['okno_vyibora_tipa_uslugi_uje_otkryito'], function() {
							grid.getSelectionModel().selectFirstRow();
							grid.getView().focusRow(0);
						});
						return false;
					}

					params.formParams = {
						Person_id: base_form.findField('Person_id').getValue(),
						PersonEvn_id: base_form.findField('PersonEvn_id').getValue(),
						Server_id: base_form.findField('Server_id').getValue()
					}
					params.parentEvnComboData = parent_evn_combo_data;

					getWnd('swEvnUslugaSetWindow').show({
						EvnUsluga_rid: base_form.findField('EvnVizitPL_id').getValue(),
						onHide: function() {
							if ( grid.getSelectionModel().getSelected() ) {
								grid.getView().focusRow(grid.getStore().indexOf(grid.getSelectionModel().getSelected()));
							}
							else {
								grid.getView().focusRow(0);
								grid.getSelectionModel().selectFirstRow();
							}
						},
						params: params,
						parentEvent: 'EvnVizitPL'
					});
				}
			break;

			case 'edit':
			case 'view':
				// Открываем форму редактирования услуги (в зависимости от EvnClass_SysNick)

				var selected_record = grid.getSelectionModel().getSelected();

				if ( !selected_record || !selected_record.get('EvnUsluga_id') ) {
					return false;
				}

				if ( selected_record.get('accessType') != 'edit' ) {
					params.action = 'view';
				}

				var evn_usluga_id = selected_record.get('EvnUsluga_id');

				switch ( selected_record.get('EvnClass_SysNick') ) {
					case 'EvnUslugaCommon':
						params.formParams = {
							EvnUslugaCommon_id: evn_usluga_id
						}
						params.parentEvnComboData = parent_evn_combo_data;
					break;

					default:
						return false;
					break;
				}

				if ( getWnd('swEvnUslugaEditWindow').isVisible() ) {
					sw.swMsg.alert(lang['soobschenie'], lang['okno_redaktirovaniya_uslugi_uje_otkryito'], function() {
						grid.getSelectionModel().selectFirstRow();
						grid.getView().focusRow(0);
					});
					return false;
				}

				getWnd('swEvnUslugaEditWindow').show(params);
			break;
		}
	},
	/*openPersonDispEditWindow: function(action) {
		if ( action != 'add' && action != 'edit' && action != 'view' ) {
			return false;
		}

		return false;

		var current_window = this;

		if ( current_window.action == 'view') {
			if ( action == 'add') {
				return false;
			}
			else if ( action == 'edit' ) {
				action = 'view';
			}
		}

		if ( current_window.action != 'edit' || current_window.EvnPLAction != 'edit' ) {
			return false;
		}

		if ( getWnd('swPersonDispEditWindow').isVisible() ) {
			sw.swMsg.alert(lang['soobschenie'], lang['okno_redaktirovaniya_dispansernoy_kartyi_patsienta_uje_otkryito']);
			return false;
		}

		var params = new Object();
		var person_disp_grid = current_window.findById('EVPLDSAEF_PersonDispGrid');

		params.action = action;
		params.callback = function(data) {
			if ( !data || !data.PersonDispData ) {
				person_disp_grid.getStore().reload();
			}
			else {
				// Добавить или обновить запись в person_disp_grid
				var record = person_disp_grid.getStore().getById(data.PersonDispData.PersonDisp_id);

				if ( record ) {
					// Обновление
					record.set('Diag_Code', data.PersonDispData.Diag_Code);
					record.set('LpuRegion_Name', data.PersonDispData.LpuRegion_Name);
					record.set('LpuSection_Code', data.PersonDispData.LpuSection_Code);
					record.set('MedPersonal_Code', data.PersonDispData.MedPersonal_Code);
					record.set('Person_id', data.PersonDispData.Person_id);
					record.set('PersonDisp_begDate', data.PersonDispData.PersonDisp_begDate);
					record.set('PersonDisp_endDate', data.PersonDispData.PersonDisp_endDate);
					record.set('PersonDisp_id', data.PersonDispData.PersonDisp_id);
					record.set('PersonDisp_NextDate', data.PersonDispData.PersonDisp_NextDate);
					record.set('Server_id', data.PersonDispData.Server_id);

					record.commit();
				}
				else {
					// Добавление
					if ( person_disp_grid.getStore().getCount() == 1 && !person_disp_grid.getStore().getAt(0).get('PersonDisp_id') ) {
						person_disp_grid.getStore().removeAll();
					}

					person_disp_grid.getStore().loadData([ data.PersonDispData ], true);
				}
			}
		};
		params.Person_id = current_window.findById('EVPLDSAEF_Person_id').getValue();
		params.Server_id = current_window.findById('EVPLDSAEF_Server_id').getValue();

		if ( action != 'add' ) {
			if ( !person_disp_grid.getSelectionModel().getSelected() ) {
				return false;
			}

			var record = person_disp_grid.getSelectionModel().getSelected();

			if ( !record.get('PersonDisp_id') ) {
				return false;
			}

			params.PersonDisp_id = record.get('PersonDisp_id');
			params.onHide = function() {
				person_disp_grid.getSelectionModel().selectRow(person_disp_grid.getStore().indexOf(record));
			};
		}

		getWnd('swPersonDispEditWindow').show(params);
	},
	*/
	plain: true,
	resizable: true,
	setFilter: function(value)
	{
		form = this;
		var mass = form.getListDispWowSpec(form.owner,value);
		var combo = form.findById('EVPLDSAEF_DispWowSpecCombo');
		combo.getStore().clearFilter();
		combo.lastQuery = '';
		combo.getStore().filterBy(function(record) 
		{
			if (value==record.get('DispWowSpec_id'))
			{
				combo.fireEvent('select', combo, record, 0);
			}
			return (!(record.get('DispWowSpec_id').inlist(mass))) && (!((form.Sex_id == 1) && (record.get('DispWowSpec_id').inlist([7]))));
		});
		if (value==0)
		{
			combo.fireEvent('change', combo, '', '');
		}
	},
	setFilterValue: function(combo, field_name)
	{
		var id = combo.getValue();
		var fs = false;
		combo.getStore().each(function(record) 
		{
			if (record.get(field_name) == id)
			{
				combo.fireEvent('select', combo, record, 0);
				fs = true;
			}
		});
		if (!fs) 
		{
			combo.setValue('');
			combo.clearInvalid();
		}
	},
	setFilterProfile: function(field, far, type)
	{
		var form = this;
		var bf = field.ownerCt.ownerCt.getForm();
		var dateValue = bf.findField('EvnVizitPL_setDate').getValue();
		var params = new Object();
		params.isPolka = true;
		//if (type!=10)
//		{
//			params.arrayLpuSectionProfile = far;
		//}
		if (type==10)
		{
			params.arrayLpuSectionProfileNot = far;
		}
		if ( !dateValue ) 
		{
			params.onDate = Ext.util.Format.date(dateValue, 'd.m.Y');
		}

		setLpuSectionGlobalStoreFilter(params);

		params.EvnClass_SysNick = 'EvnVizit';
		setMedStaffFactGlobalStoreFilter(params);
		
		bf.findField('LpuSection_id').getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));
		bf.findField('MedStaffFact_id').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));
		
		params.EvnClass_SysNick = null;
		setMedStaffFactGlobalStoreFilter(params);
		bf.findField('MedStaffFact_sid').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));
		form.setFilterValue(bf.findField('LpuSection_id'), 'LpuSection_id');
		form.setFilterValue(bf.findField('MedStaffFact_id'), 'MedStaffFact_id');
		form.setFilterValue(bf.findField('MedStaffFact_sid'), 'MedStaffFact_sid');
		/*
		
		var combo = field.ownerCt.ownerCt.findById(combo_name);
		combo.getStore().clearFilter();
		combo.lastQuery = '';
		var id = combo.getValue();
		log(combo_name);
		combo.getStore().filterBy(function(record) 
		{
			if (type!=10)
			{
				log(record.get('LpuSectionProfile_Code'));
				return (record.get('LpuSectionProfile_Code').inlist(far));
			}
			else 
			{
				return true;
			}
		});
		var fs = false;
		combo.getStore().each(function(record) 
		{
			if (record.get(field_name) == id)
			{
				combo.fireEvent('select', combo, record, 0);
				fs = true;
			}
		});
		if (!fs) 
		{
			combo.setValue('');
			combo.clearInvalid();
		}*/
	},
	show: function() {		
		sw.Promed.swEvnVizitPLDispSomeAdultEditWindow.superclass.show.apply(this, arguments);
		
		this.restore();
		this.center();
		this.maximize();

        var base_form = this.findById('EvnVizitPLDispSomeAdultEditForm').getForm();

		if ( this.firstRun == true ) {
			this.EvnXmlPanel.collapse();
            this.EvnXmlPanel.LpuSectionField = base_form.findField('LpuSection_id');
            this.EvnXmlPanel.MedStaffFactField = base_form.findField('MedStaffFact_id');
			this.findById('EVPLDSAEF_EvnDiagPLPanel').collapse();
			this.findById('EVPLDSAEF_EvnReceptPanel').collapse();

			this.firstRun = false;
		}

		this.DirectionInfoData.getStore().removeAll();
		this.findById('EVPLDSAEF_DirectInfoPanel').isLoaded = false;

		base_form.reset();
        this.EvnXmlPanel.doReset();
        this.doLayout();

		if (!this.MorbusHepatitisSpec.hidden) {
			this.MorbusHepatitisSpec.doLayout();
		}
		this.MorbusHepatitisSpec.hide();
		
		var enable_usluga_section_load_filter = getUslugaOptions().enable_usluga_section_load_filter;			
		var isPskov = (getGlobalOptions().region && getGlobalOptions().region.nick == 'pskov');
		var isUfa = (getGlobalOptions().region && getGlobalOptions().region.nick == 'ufa');
		
		this.action = null;
		this.allowMorbusVizitOnly = 0;
		this.allowNonMorbusVizitOnly = 0;
		this.callback = Ext.emptyFn;
		this.EvnUslugaGridIsModified = false;
		this.formStatus = 'edit';
		this.FormType = null;
		this.from = null;
		this.loadLastData = false;
		this.onHide = Ext.emptyFn;
		this.owner = null;
		this.Sex_id = null;
		this.streamInput = false;
		this.TimetableGraf_id = null;
		this.ServiceType_SysNick = null;
		
		if ( !arguments[0] || (!arguments[0].formParams && !arguments[0].FormType) ) { // http://172.19.61.24:85/issues/show/2428 открывается и из ВОВ (осмотр ВОВ - обычное посещение, а formParams там нету)
			sw.swMsg.alert(lang['soobschenie'], lang['nevernyie_parametryi'], function() { this.hide(); }.createDelegate(this) );
			return false;
		}

		// For EvnVizitPLWow, Night
		// Если поле FormType есть и равно EvnVizitPLWow, то это посещение ВОВ, в данном случае переданные поля преобразуем в необходимые дальше
		if ( arguments[0].FormType && arguments[0].FormType == 'EvnVizitPLWow' ) 
		{
			this.FormType = arguments[0].FormType;

			arguments[0].formParams = new Object();

			if ( arguments[0].action == 'add' )
			{
				arguments[0].formParams.EvnVizitPL_id = 0;
			}
			else
			{
				arguments[0].formParams.EvnVizitPL_id = arguments[0].EvnVizitPLWOW_id;
			}

			arguments[0].formParams.EvnPL_id = arguments[0].EvnPLWOW_id;
			arguments[0].formParams.Person_id = arguments[0].Person_id;
			arguments[0].formParams.PersonEvn_id = arguments[0].PersonEvn_id;
			arguments[0].formParams.Server_id = arguments[0].Server_id;						
				
			if ( arguments[0].Sex_id ) {
				this.Sex_id = arguments[0].Sex_id;
			}
			base_form.findField('Diag_id').setAllowBlank(false);
			this.findById('EVPLDSAEF_DispWowSpecComboSet').setVisible(true);
			this.findById('EVPLDSAEF_DispWowSpecCombo').setAllowBlank(false);
			this.findById('EVPLDSAEF_EvnUslugaPanel').setVisible(false);
			if (getGlobalOptions().region && getGlobalOptions().region.nick == 'ufa') {
				this.findById('EVPLDSAEF_PayType_id').setValue(9);
			} else {
				this.findById('EVPLDSAEF_PayType_id').setValue(1);
			}
		}
		else 
		{
			this.findById('EVPLDSAEF_DispWowSpecComboSet').setVisible(false);
			this.findById('EVPLDSAEF_DispWowSpecCombo').setAllowBlank(true);
			// this.findById('EVPLDSAEF_EvnUslugaPanel').setVisible(true);
		}
		
		if ( arguments[0].action ) {
			this.action = arguments[0].action;

			if ( this.action == 'add' ) {
				if ( arguments[0].allowMorbusVizitOnly == true ) {
					this.allowMorbusVizitOnly = 1;
				}

				if ( arguments[0].allowNonMorbusVizitOnly == true ) {
					this.allowNonMorbusVizitOnly = 1;
				}
			}
		}

		if ( arguments[0].callback ) {
			this.callback = arguments[0].callback;
		}
		
		if ( arguments[0].from ) {
			this.from = arguments[0].from;
		}

		if ( arguments[0].loadLastData ) {
			this.loadLastData = arguments[0].loadLastData;
		}

		if ( arguments[0].owner ) {
			this.owner = arguments[0].owner;
		}

		if ( arguments[0].ServiceType_SysNick ) {
			this.ServiceType_SysNick = arguments[0].ServiceType_SysNick;
		}
		
		if ( arguments[0].onHide ) {
			this.onHide = arguments[0].onHide;
		}

		if ( arguments[0].streamInput ) {
			this.streamInput = arguments[0].streamInput;
		}

		if ( arguments[0].TimetableGraf_id ) {
			this.TimetableGraf_id = arguments[0].TimetableGraf_id;
		}

		this.userMedStaffFact = (this.streamInput == true || Ext.isEmpty(sw.Promed.MedStaffFactByUser.current) || sw.Promed.MedStaffFactByUser.current.ARMType == 'mstat' ? new Object() : sw.Promed.MedStaffFactByUser.current);

		// Устанавливаем фильтры для кодов посещений
		if ( isPskov == true || isUfa == true ) {
			base_form.findField('UslugaComplex_uid').clearBaseParams();

			base_form.findField('UslugaComplex_uid').getStore().baseParams.allowDispSomeAdultOnly = 1;
			base_form.findField('UslugaComplex_uid').getStore().baseParams.Sex_Code = arguments[0].Sex_Code ? arguments[0].Sex_Code : '';

			if ( isPskov ) {
				base_form.findField('UslugaComplex_uid').setAllowedUslugaComplexAttributeList([ 'vizit' ]);
				base_form.findField('UslugaComplex_uid').setUslugaCategoryList([ 'pskov_foms' ]);
			}
			else if ( isUfa ) {
				base_form.findField('UslugaComplex_uid').setUslugaCategoryList([ 'lpusection' ]);
			}
		}

		this.Sex_Code = arguments[0].Sex_Code ? arguments[0].Sex_Code : '';
		
		this.findById('EVPLDSAEF_PersonInformationFrame').load({			
			Person_id: (arguments[0].Person_id ? arguments[0].Person_id : ''),
			Person_Birthday: (arguments[0].Person_Birthday ? arguments[0].Person_Birthday : ''),
			Person_Firname: (arguments[0].Person_Firname ? arguments[0].Person_Firname : ''),
			Person_Secname: (arguments[0].Person_Secname ? arguments[0].Person_Secname : ''),
			Person_Surname: (arguments[0].Person_Surname ? arguments[0].Person_Surname : ''),
			Person_closeDT: (arguments[0].Person_closeDT ? arguments[0].Person_closeDT : ''),
			Person_deadDT: (arguments[0].Person_deadDT ? arguments[0].Person_deadDT : ''),
			OmsSprTerr_Code: (arguments[0].OmsSprTerr_Code ? arguments[0].OmsSprTerr_Code : ''),
			Sex_Code: (arguments[0].Sex_Code ? arguments[0].Sex_Code : ''),
			callback: function() {
				var field = base_form.findField('EvnVizitPL_setDate');
				clearDateAfterPersonDeath('personpanelid', 'EVPLDSAEF_PersonInformationFrame', field);
			}
		});

		if ( this.action == 'add' ) {
			this.findById('EVPLDSAEF_EvnDiagPLPanel').isLoaded = true;
			//this.findById('EVPLDSAEF_EvnDirectionPanel').isLoaded = true;
			this.findById('EVPLDSAEF_EvnReceptPanel').isLoaded = true;
			this.findById('EVPLDSAEF_EvnUslugaPanel').isLoaded = true;
			//this.findById('EVPLDSAEF_PersonDispPanel').isLoaded = true;
		}
		else {
			this.findById('EVPLDSAEF_EvnDiagPLPanel').isLoaded = false;
			//this.findById('EVPLDSAEF_EvnDirectionPanel').isLoaded = false;
			this.findById('EVPLDSAEF_EvnReceptPanel').isLoaded = false;
			this.findById('EVPLDSAEF_EvnUslugaPanel').isLoaded = false;
			//this.findById('EVPLDSAEF_PersonDispPanel').isLoaded = false;
		}

		this.findById('EVPLDSAEF_EvnDiagPLGrid').getStore().removeAll();
		this.findById('EVPLDSAEF_EvnDiagPLGrid').getTopToolbar().items.items[0].enable();
		this.findById('EVPLDSAEF_EvnDiagPLGrid').getTopToolbar().items.items[1].disable();
		this.findById('EVPLDSAEF_EvnDiagPLGrid').getTopToolbar().items.items[2].disable();
		this.findById('EVPLDSAEF_EvnDiagPLGrid').getTopToolbar().items.items[3].disable();

		this.findById('EVPLDSAEF_EvnReceptGrid').getStore().removeAll();
		this.findById('EVPLDSAEF_EvnReceptGrid').getTopToolbar().items.items[0].enable();
		this.findById('EVPLDSAEF_EvnReceptGrid').getTopToolbar().items.items[1].disable();
		this.findById('EVPLDSAEF_EvnReceptGrid').getTopToolbar().items.items[2].disable();
		this.findById('EVPLDSAEF_EvnReceptGrid').getTopToolbar().items.items[3].disable();

		this.findById('EVPLDSAEF_EvnUslugaGrid').getStore().removeAll();
		this.findById('EVPLDSAEF_EvnUslugaGrid').getTopToolbar().items.items[0].enable();
		this.findById('EVPLDSAEF_EvnUslugaGrid').getTopToolbar().items.items[1].disable();
		this.findById('EVPLDSAEF_EvnUslugaGrid').getTopToolbar().items.items[2].disable();
		this.findById('EVPLDSAEF_EvnUslugaGrid').getTopToolbar().items.items[3].disable();

		/*
		this.findById('EVPLDSAEF_PersonDispGrid').getStore().removeAll();
		this.findById('EVPLDSAEF_PersonDispGrid').getTopToolbar().items.items[0].disable();
		this.findById('EVPLDSAEF_PersonDispGrid').getTopToolbar().items.items[1].disable();
		this.findById('EVPLDSAEF_PersonDispGrid').getTopToolbar().items.items[2].disable();
		this.findById('EVPLDSAEF_PersonDispGrid').getTopToolbar().items.items[3].disable();
		this.findById('EVPLDSAEF_EvnDirectionGrid').getStore().removeAll();
		//this.findById('EVPLDSAEF_EvnDirectionGrid').getTopToolbar().items.items[0].disable();
		this.findById('EVPLDSAEF_EvnDirectionGrid').getTopToolbar().items.items[1].disable();
		this.findById('EVPLDSAEF_EvnDirectionGrid').getTopToolbar().items.items[2].disable();
		this.findById('EVPLDSAEF_EvnDirectionGrid').getTopToolbar().items.items[3].disable();
		*/
		
		this.formParams = arguments[0].formParams;
		base_form.setValues(this.formParams);
		// врач и младший мед. персонал
		// base_form.findField('MedStaffFact_id').setFieldValue('MedPersonal_id', this.formParams.MedPersonal_id);
		// base_form.findField('MedStaffFact_sid').setFieldValue('MedPersonal_id', this.formParams.MedPersonal_sid);

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT });
		loadMask.show();

		switch ( this.action ) {
			case 'add':
				// Варианты:
				// 1 - загружаем данные с сервера
				// 2 - устанавливаем параметры с формы поточного ввода
				this.setTitle(WND_POL_EVPLDSAADD);
				this.enableEdit(true);

				LoadEmptyRow(this.findById('EVPLDSAEF_EvnDiagPLGrid'));
				//LoadEmptyRow(this.findById('EVPLDSAEF_EvnDirectionGrid'));
				LoadEmptyRow(this.findById('EVPLDSAEF_EvnUslugaGrid'));
				LoadEmptyRow(this.findById('EVPLDSAEF_EvnReceptGrid'));
				//LoadEmptyRow(this.findById('EVPLDSAEF_PersonDispGrid'));

				var set_date_flag = Ext.isEmpty(base_form.findField('EvnVizitPL_setDate').getValue());
				
				var time_field = base_form.findField('EvnVizitPL_setTime');

				if ( !Ext.isEmpty(this.userMedStaffFact.MedStaffFact_id) ) {
					// устанавливаем дату/время, если пришли из рабочего места врача
					time_field.setAllowBlank(false);
				} 
				else {
					time_field.setAllowBlank(true);
				}

				setCurrentDateTime({
					callback: function() {
						base_form.findField('EvnVizitPL_setDate').fireEvent('change', base_form.findField('EvnVizitPL_setDate'), base_form.findField('EvnVizitPL_setDate').getValue());

						if ( this.loadLastData === true ) {
							// Загружаем данные о последнем посещении с сервера
							Ext.Ajax.request({
								callback: function(options, success, response) {
									var response_obj = Ext.util.JSON.decode(response.responseText);

									if ( typeof response_obj == 'object' && response_obj.length > 0 ) {
										var index;
										var record;

										base_form.findField('DeseaseType_id').setValue(response_obj[0].DeseaseType_id);
										base_form.findField('PayType_id').setValue(response_obj[0].PayType_id);
										base_form.findField('ServiceType_id').setValue(response_obj[0].ServiceType_id);
										base_form.findField('VizitType_id').setValue(response_obj[0].VizitType_id);

										if ( isPskov || isUfa ) {
											var usluga_complex_id = response_obj[0].UslugaComplex_uid;

											if ( isUfa ) {
												// Дернуть код профиля и установить фильтр
												index = base_form.findField('LpuSection_id').getStore().findBy(function(rec) {
													if ( rec.get('LpuSection_id') == response_obj[0].LpuSection_id ) {
														return true;
													}
													else {
														return false;
													}
												});
												record = base_form.findField('LpuSection_id').getStore().getAt(index);

												if ( record ) {
													// base_form.findField('UslugaComplex_uid').setLpuLevelCode(record.get('LpuSectionProfile_Code'));
												}
											}

											if ( usluga_complex_id ) {
												base_form.findField('UslugaComplex_uid').getStore().load({
													callback: function() {
														index = base_form.findField('UslugaComplex_uid').getStore().findBy(function(rec) {
															if ( rec.get('UslugaComplex_id') == usluga_complex_id ) {
																return true;
															}
															else {
																return false;
															}
														});

														if ( index >= 0 ) {
															base_form.findField('UslugaComplex_uid').setValue(usluga_complex_id);
														}
														else {
															base_form.findField('UslugaComplex_uid').clearValue();
														}
													}.createDelegate(this),
													params: {
														//UslugaComplex_id: usluga_complex_id
													}
												});
											}
										}

										if ( typeof base_form.findField('EvnVizitPL_setDate').getValue() == 'object' && Ext.isEmpty(this.userMedStaffFact.LpuSection_id) && Ext.isEmpty(this.userMedStaffFact.MedStaffFact_id) ) {
											var lpu_section_id = response_obj[0].LpuSection_id;

											base_form.findField('LpuSection_id').setValue(lpu_section_id);
											base_form.findField('LpuSection_id').fireEvent('change', base_form.findField('LpuSection_id'), lpu_section_id);

											// врач и младший мед. персонал
											index = base_form.findField('MedStaffFact_id').getStore().findBy(function(record, id) {
												if ( record.get('LpuSection_id') == lpu_section_id && record.get('MedPersonal_id') == response_obj[0].MedPersonal_id ) {
													base_form.findField('MedStaffFact_id').setValue(record.get('MedStaffFact_id'));
													return true;
												}
												else {
													return false;
												}
											});										
											
											index = base_form.findField('MedStaffFact_sid').getStore().findBy(function(record, id) {
												if ( record.get('LpuSection_id') == lpu_section_id && record.get('MedPersonal_id') == response_obj[0].MedPersonal_sid ) {
													base_form.findField('MedStaffFact_sid').setValue(record.get('MedStaffFact_id'));
													return true;
												}
												else {
													return false;
												}
											});
										}

										base_form.findField('VizitType_id').fireEvent('change', base_form.findField('VizitType_id'), base_form.findField('VizitType_id').getValue());

										if ( !Ext.isEmpty(response_obj[0].Diag_id) ) {
											base_form.findField('Diag_id').getStore().load({
												callback: function() {
													base_form.findField('Diag_id').getStore().each(function(record) {
														if ( record.get('Diag_id') == response_obj[0].Diag_id ) {
															base_form.findField('Diag_id').setValue(response_obj[0].Diag_id);
															base_form.findField('Diag_id').fireEvent('select', base_form.findField('Diag_id'), record, 0);
														}
													});
												},
												params: { where: "where DiagLevel_id = 4 and Diag_id = " + response_obj[0].Diag_id }
											});
										}
									}

									loadMask.hide();

									base_form.clearInvalid();

									base_form.findField('EvnVizitPL_setDate').focus(true, 250);																		
								}.createDelegate(this),
								params: {
									EvnVizitPL_pid: base_form.findField('EvnPL_id').getValue()
								},
								url: '/?c=EvnVizit&m=loadLastEvnVizitPLData'
							});
                            this.EvnXmlPanel.loadLastEvnProtocolData(base_form.findField('EvnPL_id').getValue());
						}
						else {
							// Night, если посещение создается из места работы врача (то есть под врачом), то заполняем по умолчанию 
							// Место - скорее всего поликлиника
							// Цель посещения - скорее всего лечебно-диагностическая 
							// Вид оплаты - скорее всего ОМС 
							if ( !Ext.isEmpty(this.userMedStaffFact.MedStaffFact_id) ) {
								base_form.findField('PayType_id').setFieldValue('PayType_SysNick', 'oms');
								base_form.findField('ServiceType_id').setFieldValue('ServiceType_SysNick', 'polka');
								base_form.findField('VizitType_id').setFieldValue('VizitType_SysNick', 'desease');
							}
							
							// Если задан ServiceType то проставляем его
							if (this.ServiceType_SysNick && this.ServiceType_SysNick != null) {
								base_form.findField('ServiceType_id').setFieldValue('ServiceType_SysNick', this.ServiceType_SysNick);
							}

							// Night: если для ВОВ то автоматически ставил тип оплаты - ОМС и не даем изменять
							if ( this.FormType == 'EvnVizitPLWow' && base_form.findField('PayType_id').getValue() > 0 ) {
								base_form.findField('PayType_id').setDisabled(true);
							}

							loadMask.hide();

							base_form.clearInvalid();

							base_form.findField('EvnVizitPL_setDate').focus(true, 250);
						}

						var index;
						var lpu_section_id = base_form.findField('LpuSection_id').getValue();
						var lpu_section_pid;
						var med_personal_id = base_form.findField('MedPersonal_id').getValue();
						var med_personal_sid = base_form.findField('MedPersonal_sid').getValue();
						var record;

						index = base_form.findField('LpuSection_id').getStore().findBy(function(rec, id) {
							return (rec.get('LpuSection_id') == lpu_section_id);
						}.createDelegate(this));
						record = base_form.findField('LpuSection_id').getStore().getAt(index);

						if ( record ) {
							lpu_section_pid = record.get('LpuSection_pid');
						}

						// врач
						index = base_form.findField('MedStaffFact_id').getStore().findBy(function(rec, id) {
							return (rec.get('LpuSection_id').inlist([ lpu_section_id, lpu_section_pid ]) && rec.get('MedPersonal_id') == med_personal_id);
						}.createDelegate(this));										
						record = base_form.findField('MedStaffFact_id').getStore().getAt(index);

						if ( record ) {
							base_form.findField('MedStaffFact_id').setValue(record.get('MedStaffFact_id'));
						}

						// средний мед. персонал
						index = base_form.findField('MedStaffFact_sid').getStore().findBy(function(rec, id) {
							return (rec.get('MedPersonal_id') == med_personal_sid);
						}.createDelegate(this));
						record = base_form.findField('MedStaffFact_sid').getStore().getAt(index);

						if ( record ) {
							base_form.findField('MedStaffFact_sid').setValue(record.get('MedStaffFact_id'));
						}
						
						var diag_id = this.formParams.Diag_id;
						if ( diag_id != null && diag_id.toString().length > 0 ) {
							base_form.findField('Diag_id').getStore().load({
								callback: function() {
									base_form.findField('Diag_id').getStore().each(function(record) {
										if ( record.get('Diag_id') == diag_id ) {
											base_form.findField('Diag_id').fireEvent('select', base_form.findField('Diag_id'), record, 0);
										}
									});
								},
								params: { where: "where DiagLevel_id = 4 and Diag_id = " + diag_id }
							});
						}
					}.createDelegate(this),
					dateField: base_form.findField('EvnVizitPL_setDate'),
					loadMask: false,
					setDate: set_date_flag,
					setDateMaxValue: true,
					setDateMinValue: false,
					setTime: set_date_flag,
					timeField: base_form.findField('EvnVizitPL_setTime'),
					windowId: this.id
				});
				if (getGlobalOptions().region && getGlobalOptions().region.nick == 'ufa')
				this.findById('EVPLDSAEF_PayType_id').fireEvent('change',this.findById('EVPLDSAEF_PayType_id'));
			break;

			case 'edit':
			case 'view':
				this.MorbusHepatitisSpec.collapse();
				// Делаем загрузку данных с сервера
				base_form.load({
					failure: function() {
						loadMask.hide();
						sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_zagruzke_dannyih_formyi'], function() { this.hide(); }.createDelegate(this) );
					}.createDelegate(this),
					params: {
						EvnVizitPL_id: base_form.findField('EvnVizitPL_id').getValue(),
						FormType: this.FormType
					},
					success: function() {
						// В зависимости от accessType переопределяем this.action
						if ( base_form.findField('accessType').getValue() == 'view' ) {
							this.action = 'view';
						}

						if ( this.action == 'view' ) {
							this.setTitle(WND_POL_EVPLDSAVIEW);
							this.enableEdit(false);
						}
						else {
							this.setTitle(WND_POL_EVPLDSAEDIT);
							this.enableEdit(true);
							if ( this.FormType == 'EvnVizitPLWow' && base_form.findField('PayType_id').getValue() > 0 ) {
								base_form.findField('PayType_id').setDisabled(true);
							}
							if (getGlobalOptions().region && getGlobalOptions().region.nick == 'ufa')
							this.findById('EVPLDSAEF_PayType_id').fireEvent('change',this.findById('EVPLDSAEF_PayType_id'));
						}

						if ( !getGlobalOptions().region || !getGlobalOptions().region.nick.inlist([ 'pskov' ]) ) {
							this.findById('EVPLDSAEF_EvnUslugaPanel').fireEvent('expand', this.findById('EVPLDSAEF_EvnUslugaPanel'));
						}

						// Остальные гриды - только если развернуты панельки
						if ( !this.findById('EVPLDSAEF_EvnDiagPLPanel').collapsed ) {
							this.findById('EVPLDSAEF_EvnDiagPLPanel').fireEvent('expand', this.findById('EVPLDSAEF_EvnDiagPLPanel'));
						}

						if ( !this.findById('EVPLDSAEF_EvnReceptPanel').collapsed ) {
							this.findById('EVPLDSAEF_EvnReceptPanel').fireEvent('expand', this.findById('EVPLDSAEF_EvnReceptPanel'));
						}

						var diag_agid = base_form.findField('Diag_agid').getValue();
						var diag_id = base_form.findField('Diag_id').getValue();
						var index;
						var lpu_section_id = base_form.findField('LpuSection_id').getValue();
						var lpu_section_pid;
						var med_personal_id = base_form.findField('MedPersonal_id').getValue();
						var med_personal_sid = base_form.findField('MedPersonal_sid').getValue();
						var record;
						var service_type_id = base_form.findField('ServiceType_id').getValue();

						if ( isPskov == true || isUfa == true ) {
							var usluga_complex_id = base_form.findField('UslugaComplex_uid').getValue();
						}

						base_form.findField('ServiceType_id').clearValue();
						base_form.findField('ServiceType_id').getStore().clearFilter();
						base_form.findField('ServiceType_id').lastQuery = '';

						// Фильтр на поле ServiceType_id
						// https://redmine.swan.perm.ru/issues/17571
						if ( !Ext.isEmpty(base_form.findField('EvnVizitPL_setDate').getValue()) ) {
							base_form.findField('ServiceType_id').getStore().filterBy(function(rec) {	
								return (
									(Ext.isEmpty(rec.get('ServiceType_begDate')) || rec.get('ServiceType_begDate') <= base_form.findField('EvnVizitPL_setDate').getValue())
									&& (Ext.isEmpty(rec.get('ServiceType_endDate')) || rec.get('ServiceType_endDate') >= base_form.findField('EvnVizitPL_setDate').getValue())
								);
							});
						}

						index = base_form.findField('ServiceType_id').getStore().findBy(function(rec, id) {
							return (rec.get('ServiceType_id') == service_type_id);
						}.createDelegate(this));

						if ( index >= 0 ) {
							base_form.findField('ServiceType_id').setValue(service_type_id);
						}

						if ( !Ext.isEmpty(this.userMedStaffFact.LpuSection_id) && !Ext.isEmpty(this.userMedStaffFact.MedStaffFact_id) ) {
							if ( this.action == 'edit' ) {
								base_form.findField('LpuSection_id').disable();
								base_form.findField('MedStaffFact_id').disable();
							}
						}

						index = base_form.findField('LpuSection_id').getStore().findBy(function(rec, id) {
							if ( rec.get('LpuSection_id') == lpu_section_id ) {
								return true;
							}
							else {
								return false;
							}
						}.createDelegate(this));
						record = base_form.findField('LpuSection_id').getStore().getAt(index);

						if ( record ) {
							lpu_section_pid = record.get('LpuSection_pid');
						}

						if ( this.action == 'edit' ) {
							base_form.findField('EvnVizitPL_setDate').fireEvent('change', base_form.findField('EvnVizitPL_setDate'), base_form.findField('EvnVizitPL_setDate').getValue());
							base_form.findField('VizitType_id').fireEvent('change', base_form.findField('VizitType_id'), base_form.findField('VizitType_id').getValue());

							index = base_form.findField('MedStaffFact_id').getStore().findBy(function(rec, id) {
								if ( rec.get('LpuSection_id').inlist([ lpu_section_id, lpu_section_pid ]) && rec.get('MedPersonal_id') == med_personal_id ) {
									return true;
								}
								else {
									return false;
								}
							})
							record = base_form.findField('MedStaffFact_id').getStore().getAt(index);

							if ( record ) {
								base_form.findField('MedStaffFact_id').setValue(record.get('MedStaffFact_id'));
							}
							else {
								Ext.Ajax.request({
									failure: function(response, options) {
										loadMask.hide();
									},
									params: {
										LpuSection_id: lpu_section_id,
										MedPersonal_id: med_personal_id
									},
									success: function(response, options) {
										loadMask.hide();
										
										base_form.findField('MedStaffFact_id').getStore().loadData(Ext.util.JSON.decode(response.responseText), true);

										index = base_form.findField('MedStaffFact_id').getStore().findBy(function(rec) {
											if ( rec.get('MedPersonal_id') == med_personal_id && rec.get('LpuSection_id') == lpu_section_id ) {
												return true;
											}
											else {
												return false;
											}
										});

										if ( index >= 0 ) {
											base_form.findField('MedStaffFact_id').setValue(base_form.findField('MedStaffFact_id').getStore().getAt(index).get('MedStaffFact_id'));
											base_form.findField('MedStaffFact_id').validate();
										}
									}.createDelegate(this),
									url: C_MEDPERSONAL_LIST
								});
							}

							index = base_form.findField('MedStaffFact_sid').getStore().findBy(function(rec, id) {
								if ( rec.get('LpuSection_id').inlist([ lpu_section_id, lpu_section_pid ]) && rec.get('MedPersonal_id') == med_personal_sid ) {
									return true;
								}
								else {
									return false;
								}
							});
							record = base_form.findField('MedStaffFact_sid').getStore().getAt(index);

							if ( record ) {
								base_form.findField('MedStaffFact_sid').setValue(record.get('MedStaffFact_id'));
							}
						}
						else {
							base_form.findField('LpuSection_id').getStore().load({
								callback: function() {
									index = base_form.findField('LpuSection_id').getStore().findBy(function(rec) {
										if ( rec.get('LpuSection_id') == lpu_section_id ) {
											return true;
										}
										else {
											return false;
										}
									});

									if ( index >= 0 ) {
										base_form.findField('LpuSection_id').setValue(base_form.findField('LpuSection_id').getStore().getAt(index).get('LpuSection_id'));
										//base_form.findField('LpuSection_id').fireEvent('change', base_form.findField('LpuSection_id'), base_form.findField('LpuSection_id').getStore().getAt(index).get('LpuSection_id'));
									}
								}.createDelegate(this),
								params: {
									LpuSection_id: lpu_section_id
								}
							});

							base_form.findField('MedStaffFact_id').getStore().load({
								callback: function() {
									index = base_form.findField('MedStaffFact_id').getStore().findBy(function(rec) {
										if ( rec.get('MedPersonal_id') == med_personal_id && rec.get('LpuSection_id') == lpu_section_id ) {
											return true;
										}
										else {
											return false;
										}
									});

									if ( index >= 0 ) {
										base_form.findField('MedStaffFact_id').setValue(base_form.findField('MedStaffFact_id').getStore().getAt(index).get('MedStaffFact_id'));
										base_form.findField('MedStaffFact_id').validate();
									}
								}.createDelegate(this),
								params: {
									LpuSection_id: lpu_section_id,
									MedPersonal_id: med_personal_id
								}
							});

							if ( med_personal_sid ) {
								base_form.findField('MedStaffFact_sid').getStore().load({
									callback: function() {
										index = base_form.findField('MedStaffFact_sid').getStore().findBy(function(rec) {
											if ( rec.get('MedPersonal_id') == med_personal_id && rec.get('LpuSection_id') == lpu_section_id ) {
												return true;
											}
											else {
												return false;
											}
										});

										if ( index >= 0 ) {
											base_form.findField('MedStaffFact_sid').setValue(base_form.findField('MedStaffFact_sid').getStore().getAt(index).get('MedStaffFact_id'));
										}
									}.createDelegate(this),
									params: {
										LpuSection_id: lpu_section_id,
										MedPersonal_id: med_personal_sid
									}
								});
							}
						}

						if ( isPskov == true || isUfa == true ) {
							if ( isUfa == true ) {
								// Дернуть код профиля и установить фильтр
								index = base_form.findField('LpuSection_id').getStore().findBy(function(rec) {
									if ( rec.get('LpuSection_id') == lpu_section_id ) {
										return true;
									}
									else {
										return false;
									}
								});
								record = base_form.findField('LpuSection_id').getStore().getAt(index);

								if ( record ) {
									// base_form.findField('UslugaComplex_uid').setLpuLevelCode(record.get('LpuSectionProfile_Code'));
								}
							}

							if ( usluga_complex_id ) {
								base_form.findField('UslugaComplex_uid').getStore().load({
									callback: function() {
										index = base_form.findField('UslugaComplex_uid').getStore().findBy(function(rec) {
											if ( rec.get('UslugaComplex_id') == usluga_complex_id ) {
												return true;
											}
											else {
												return false;
											}
										});

										if ( index >= 0 ) {
											base_form.findField('UslugaComplex_uid').setValue(usluga_complex_id);
										}
										else {
											base_form.findField('UslugaComplex_uid').clearValue();
										}
									}.createDelegate(this),
									params: {
										UslugaComplex_id: usluga_complex_id
									}
								});
							}
						}

						if ( diag_agid != null && diag_agid.toString().length > 0 ) {
							base_form.findField('Diag_agid').getStore().load({
								callback: function() {
									base_form.findField('Diag_agid').getStore().each(function(record) {
										if ( record.get('Diag_id') == diag_agid ) {
											base_form.findField('Diag_agid').fireEvent('select', base_form.findField('Diag_agid'), record, 0);
										}
									});
								},
								params: { where: "where DiagLevel_id = 4 and Diag_id = " + diag_agid }
							});
						}

						if ( diag_id != null && diag_id.toString().length > 0 ) {
							base_form.findField('Diag_id').getStore().load({
								callback: function() {
									base_form.findField('Diag_id').getStore().each(function(record) {
										if ( record.get('Diag_id') == diag_id ) {
											base_form.findField('Diag_id').fireEvent('select', base_form.findField('Diag_id'), record, 0);
											var diag_code = record.get('Diag_Code').substr(0, 3);
											if ( diag_code.inlist(['B15', 'B16', 'B17', 'B18', 'B19']) ) {
												this.MorbusHepatitisSpec.show();
											}
										}
									}.createDelegate(this));
								}.createDelegate(this),
								params: { where: "where DiagLevel_id = 4 and Diag_id = " + diag_id }
							});
						}

						if ( this.FormType == 'EvnVizitPLWow' ) {
							this.setFilter(base_form.findField('DispWowSpec_id').getValue());
						}

						this.getDirectionIf();

						loadMask.hide();

						base_form.clearInvalid();

                        this.EvnXmlPanel.setBaseParams({
                            userMedStaffFact: this.userMedStaffFact,
                            Server_id: base_form.findField('Server_id').getValue(),
                            Evn_id: base_form.findField('EvnVizitPL_id').getValue()
                        });
                        this.EvnXmlPanel.doLoadData();
                        // this.EvnXmlPanel.expand();

                        if ( this.action == 'edit' ) {
							base_form.findField('EvnVizitPL_setDate').focus(true, 250);
						} else {
							this.buttons[this.buttons.length - 1].focus();
						}
					}.createDelegate(this),
					url: '/?c=EvnVizit&m=loadEvnVizitPLEditForm'
				});
			break;

			default:
				loadMask.hide();
				this.hide();
			break;
		}
	},
    collectGridData:function (gridName) {
        var result = '';
		if (this.findById('MHW_' + gridName)) {
			var grid = this.findById('MHW_' + gridName).getGrid();
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
			sw.swMsg.alert(lang['soobschenie'], lang['okno_redaktirovaniya_uje_otkryito']);
			return false;
		}

		var grid = this.findById('MHW_'+gridName).getGrid();
		var params = new Object();

		params.action = action;
		params.callback = function(data) {
			
			if (!data || !data.BaseData) {
				return false;
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
			}
			else {
				if (grid.getStore().getCount() == 1 && !grid.getStore().getAt(0).get(gridName+'_id')) {
					grid.getStore().removeAll();
				}

				data.BaseData[gridName+'_id'] = -swGenTempId(grid.getStore());

				grid.getStore().loadData([ data.BaseData ], true);
			}
		}
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
			msg: lang['vyi_deystvitelno_hotite_udalit_etu_zapis'],
			title: lang['vopros']
		});
	},
	uetValuesRecount: function() {
		var base_form = this.findById('EvnVizitPLDispSomeAdultEditForm').getForm();
		var grid = this.findById('EVPLDSAEF_EvnUslugaGrid');

		var evn_usluga_stom_uet = 0;
		var evn_usluga_stom_uet_oms = 0;

		grid.getStore().each(function(record) {
			if ( record.get('PayType_SysNick') == 'oms' ) {
				evn_usluga_stom_uet_oms = evn_usluga_stom_uet_oms + Number(record.get('EvnUsluga_Summa'));
			}

			evn_usluga_stom_uet = evn_usluga_stom_uet + Number(record.get('EvnUsluga_Summa'));
		});

		base_form.findField('EvnVizitPL_Uet').setValue(evn_usluga_stom_uet.toFixed(2));
		base_form.findField('EvnVizitPL_UetOMS').setValue(evn_usluga_stom_uet_oms.toFixed(2));
	},
	width: 700
});