/**
* swEvnReceptEditWindow - окно редактирования рецепта.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      DLO.Ufa
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Stas Bykov aka Savage (savage@swan.perm.ru)
* @version      0.001-31.07.2009
* @comment      Префикс для id компонентов EREF (EvnReceptEditForm)
*
*
* @input data: action - действие (add, view)
*              EvnRecept_id - ID рецепта
*              Person_id - ID человека
*              PersonEvn_id - ID состояния человека
*              Server_id - ID сервера
*
*              Потоковый ввод:
*                  EvnRecept_setDate - дата выписки рецепта
*                  LpuSection_id - отделение
*                  MedPersonal_id - врач
*                  ReceptType_id - тип рецепта
*
*              ТАП -> Посещение:
*                  Diag_id - диагноз
*                  EvnVizitPL_id - ID посещения
*                  EvnVizitPL_setDate - дата посещения
*                  LpuSection_id - отделение (не редактируемое)
*                  MedPersonal_id - врач (не редактируемое)
*                  ??? Список доступных диагнозов
*/

sw.Promed.swEvnReceptEditWindow = Ext.extend(sw.Promed.BaseForm, {
	action: null,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	collapsible: true,
	doCopy: function() {
		if ( this.action == 'add' ) {
			this.doSave({
				checkPersonAge: true,
				checkPersonDeadDT: true,
				checkPersonSnils: true,
				copy: true,
				print: false
			});
		}
		else {
			// Открыть поля для редактирования
			var base_form = this.findById('EvnReceptEditForm').getForm();

			this.action = 'add';
			this.setTitle(WND_DLO_RCPTADD);

			var recept_type_id = base_form.findField('ReceptType_id').getValue();
			base_form.findField('ReceptType_id').fireEvent('change', base_form.findField('ReceptType_id'), recept_type_id);

			base_form.findField('Drug_id').enable();
			base_form.findField('DrugMnn_id').enable();
			base_form.findField('LpuSection_id').enable();
			base_form.findField('MedStaffFact_id').enable();
			base_form.findField('OrgFarmacy_id').enable();
			base_form.findField('PrivilegeType_id').enable();
			base_form.findField('ReceptFinance_id').enable();

			base_form.findField('EvnRecept_id').setValue(0);
			base_form.findField('DrugMnn_id').clearValue();
			base_form.findField('DrugMnn_id').getStore().removeAll();
			base_form.findField('DrugMnn_id').fireEvent('change', base_form.findField('DrugMnn_id'), null);
			base_form.findField('EvnRecept_Kolvo').maxValue = undefined;
			base_form.findField('EvnRecept_Kolvo').setValue(1);
			base_form.findField('EvnRecept_Signa').setRawValue('');

			this.enableEdit(true);
			Ext.getCmp('EREF_DrugResult').hide();
			Ext.getCmp('EREF_DrugResult').disable();
			base_form.findField('DrugMnn_id').focus(true);

			var ReceptForm_id = Ext.isEmpty(base_form.findField('ReceptForm_id').getValue())?base_form.findField('ReceptForm_id').getValue():0;
			var new_date = Ext.util.Format.date(base_form.findField('EvnRecept_setDate').getValue(), 'Y-m-d');
			if(ReceptForm_id == 2)
				base_form.findField('ReceptValid_id').getStore().filterBy(function(rec) {
					return (rec.get('ReceptValid_Code').toString().inlist(new_date >= '2016-01-01'?['4','9','10','11']:['1', '2']));
				});
			else
				base_form.findField('ReceptValid_id').getStore().filterBy(function(rec) {
					return (rec.get('ReceptValid_Code').toString().inlist(new_date >= '2016-01-01'?['4', '9', '10', '11']:['1', '2', '4', '7']));
				});
				base_form.findField('ReceptUrgency_id').enable();
				base_form.findField('ReceptUrgency_id').clearValue();

			base_form.findField('EvnRecept_IsExcessDose').setValue(0);
			base_form.findField('EvnRecept_IsExcessDose').hideContainer();

			base_form.findField('ReceptForm_id').fireEvent('change', base_form.findField('ReceptForm_id'), base_form.findField('ReceptForm_id').getValue());
		}
	},
	doSave: function(options) {
		// options @Object
		// options.checkPersonAge @Boolean
		// options.checkPersonDeadDT @Boolean
		// options.checkPersonSnils @Boolean
		// options.copy @Boolean 
		// options.print @Boolean Вызывать печать рецепта, если true

		if ( !options || typeof options != 'object' ) {
			return false;
		}

		if ( this.formStatus == 'save' || this.action == 'view' ) {
			return false;
		}

		this.formStatus = 'save';

		var base_form = this.findById('EvnReceptEditForm').getForm();
		var form = this.findById('EvnReceptEditForm');
		var person_information = this.findById('EREF_PersonInformationFrame');
		var post = new Object();
		var record, win = this;

		if ( person_information.getFieldValue('Person_RAddress') == null || person_information.getFieldValue('Person_RAddress').toString().length == 0 ) {
			this.formStatus = 'edit';
			sw.swMsg.alert('Ошибка', 'Сохранение невозможно [Не задан адрес регистрации]');
			return false;
		}

		if ( Ext.isEmpty(person_information.getFieldValue('Polis_begDate')) ) {
			this.formStatus = 'edit';
			sw.swMsg.alert('Ошибка', 'У данного пациента отсутствует полис');
			return false;
		}

		if ( options.checkPersonSnils && (person_information.getFieldValue('Person_Snils') == null || person_information.getFieldValue('Person_Snils').toString().length == 0) ) {
			sw.swMsg.show({
				buttons: Ext.Msg.YESNO,
				fn: function ( buttonId ) {
					this.formStatus = 'edit';

					if ( buttonId == 'yes' ) {
						this.doSave({
							checkPersonAge: options.checkPersonAge,
							checkPersonDeadDT: options.checkPersonDeadDT,
							checkPersonSnils: false,
							copy: options.copy,
							print: options.print
						});
					}
					else
					{
						win.formStatus = 'edit';
						return false;
					}
				}.createDelegate(this),
				msg: 'У пациента не задан СНИЛС. Продолжить сохранение рецепта?',
				title: 'Проверка СНИЛС'
			});
			return false;
			/*sw.swMsg.alert('Ошибка', 'У данного пациента отсутствует СНИЛС', function() {
				win.formStatus = 'edit';
				win.findById('EREF_PersonInformationFrame').panelButtonClick(2); 
			});
			return false;*/
		}

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

		// @task https://redmine.swan.perm.ru//issues/122143
		var
			EvnRecept_setDate = base_form.findField('EvnRecept_setDate').getValue(),
			Person_deadDT = person_information.getFieldValue('Person_deadDT');

		if ( !Ext.isEmpty(Person_deadDT) && typeof Person_deadDT == 'object' ) {
			if ( base_form.findField('ReceptType_id').getFieldValue('ReceptType_Code') == 2 ) {
				win.formStatus = 'edit';
				sw.swMsg.alert(langs('Ошибка'), langs('У пациента указана дата смерти, выписка рецепта с типом «на листе» невозможна.'));
				return false;
			}

			if ( base_form.findField('ReceptType_id').getFieldValue('ReceptType_Code') == 1 ) {
				if ( EvnRecept_setDate > Person_deadDT ) {
					win.formStatus = 'edit';
					sw.swMsg.alert(langs('Ошибка'), langs('Дата выписки рецепта с типом «на бланке» не может быть больше даты смерти пациента.'));
					return false;
				}

				if ( EvnRecept_setDate.format('d.m.Y') == Person_deadDT.format('d.m.Y') && options.checkPersonDeadDT == true ) {
					sw.swMsg.show({
						buttons: Ext.Msg.YESNO,
						fn: function ( buttonId ) {
							win.formStatus = 'edit';

							if ( buttonId == 'yes' ) {
								win.doSave({
									checkPersonAge: options.checkPersonAge,
									checkPersonDeadDT: false,
									checkPersonSnils: options.checkPersonSnils,
									copy: options.copy,
									print: options.print
								});
							}
							else {
								win.formStatus = 'edit';
								return false;
							}
						},
						msg: langs('Дата выписки рецепта равна дате смерти пациента. Продолжить сохранение рецепта?'),
						title: langs('Проверка даты смерти')
					});
					return false;
				}
			}
		}

		var person_age = swGetPersonAge(person_information.getFieldValue('Person_Birthday'), EvnRecept_setDate);

		if ( person_age == -1 ) {
			this.formStatus = 'edit';
			sw.swMsg.alert('Ошибка', 'Проверьте правильность ввода даты выписки рецепта и даты рождения пациента. Возможно, дата рождения пациента больше даты выписки рецепта.');
			return false;
		}

		// https://redmine.swan.perm.ru/issues/4091
		// https://redmine.swan.perm.ru/issues/4371
		if ( options.checkPersonAge ) {
			record = base_form.findField('ReceptValid_id').getStore().getById(base_form.findField('ReceptValid_id').getValue());

			if ( !record ) {
				this.formStatus = 'edit';
				sw.swMsg.alert('Ошибка', 'Не заполнено поле "Срок действия рецепта".', function() { base_form.findField('ReceptValid_id').focus(true); });
				return false;
			}

			var sex_code = person_information.getFieldValue('Sex_Code');
			
			var hasLgotType83or84 = false;
			var privilege_type_combo = base_form.findField('PrivilegeType_id');
			privilege_type_combo.getStore().each(function(rec) {
				if ( rec.get('PrivilegeType_Code').inlist([83,84]) ) {
					hasLgotType83or84 = true;
				}
			});

			// Если пациент старше пенсионного возраста (женщины 55 лет, мужчины 60 лет), а ему срок действия рецепта указан 1 месяц, то
			// или имеет категорий 83 (инвалид 1 группы)и 84 (дети-инвалиды)
			// выводить информацию: "Данный пациент достиг пенсионного возраста или имеет категории 83 (инвалид 1 группы) и 84 (дети-инвалиды). Вы действительно хотите сохранить рецепт?"
			// И сделать выбор кнопками "ДА" и "НЕТ" , при выборе кнопки "ДА", продолжать сохранение рецепта, при выборе кнопки "НЕТ",
			// возвращаться обратно на форму
			if ( ( record.get('ReceptValid_Code').inlist([1,4,9,11]) ) && ((sex_code == 1 && person_age >= 60) || (sex_code == 2 && person_age >= 55) || hasLgotType83or84) ) {
				sw.swMsg.show({
					buttons: Ext.Msg.YESNO,
					fn: function ( buttonId ) {
						this.formStatus = 'edit';

						if ( buttonId == 'yes' ) {
							this.doSave({
								checkPersonAge: false,
								checkPersonDeadDT: options.checkPersonDeadDT,
								checkPersonSnils: options.checkPersonSnils,
								copy: options.copy,
								print: options.print
							});
						}
						else {
							base_form.findField('ReceptValid_id').focus(true);
						}
					}.createDelegate(this),
					msg: 'Данный пациент достиг пенсионного возраста или имеет категории 83 (инвалид 1 группы) и 84 (дети-инвалиды). Вы действительно хотите сохранить рецепт?',
					title: 'Проверка срока действия рецепта'
				});
				return false;
			}

			// Если пациент младше пенсионного возраста (женщины 55 лет, мужчины 60 лет), а ему срок действия рецепта указан 3 месяца,
			// и не имеет категорий 83 (инвалид 1 группы)и 84 (дети-инвалиды)
			// выдавать информацию "Пациенту нельзя выписать рецепт сроком действия на 3 месяца, т.к. он не достиг пенсионного возраста (женщины 55 лет, мужчины 60 лет) и не имеет категорий 83 (инвалид 1 группы) и 84 (дети-инвалиды)".
			// После этого возвращать на форму " Льготные рецепты: добавление" , для исправления срока действия рецепта на 1 месяц. (refs #7366)
			if ( (record.get('ReceptValid_Code') == 2 || record.get('ReceptValid_Code') == 10) && ((sex_code == 1 && person_age < 60) || (sex_code == 2 && person_age < 55)) && !hasLgotType83or84 ) {
				/*this.formStatus = 'edit';
				sw.swMsg.alert('Ошибка', 'Пациенту нельзя выписать рецепт сроком действия на 3 месяца, т.к. он не достиг пенсионного возраста (женщины 55 лет, мужчины 60 лет) и не имеет категорий 83 (инвалид 1 группы) и 84 (дети-инвалиды)', function() { base_form.findField('ReceptValid_id').focus(true); });
				return false;*/
				sw.swMsg.show({
					buttons: Ext.Msg.YESNO,
					fn: function ( buttonId ) {
						this.formStatus = 'edit';

						if ( buttonId == 'yes' ) {
							this.doSave({
								checkPersonAge: false,
								checkPersonDeadDT: options.checkPersonDeadDT,
								checkPersonSnils: options.checkPersonSnils,
								copy: options.copy,
								print: options.print
							});
						}
						else {
							base_form.findField('ReceptValid_id').focus(true);
						}
					}.createDelegate(this),
					msg: 'Пациент не достиг пенсионного возраста (женщины 55 лет, мужчины 60 лет) и не имеет категорий 83 (инвалид 1 группы) и 84 (дети-инвалиды). Рецепт со сроком действия 90 дней ему можно выписать только в случае, если он является хроническим больным. Продолжить сохранение?',
					title: 'Проверка срока действия рецепта'
				});
				return false;
			}
		}

		var isSaratov = (getGlobalOptions().region && getGlobalOptions().region.nick == 'saratov');

		if ( !isSaratov && ((isSuperAdmin() && getGlobalOptions().recept_drug_ostat_control == 3) || getGlobalOptions().recept_drug_ostat_control == 4) && !base_form.findField('OrgFarmacy_id').getValue() ) {
			this.formStatus = 'edit';
			sw.swMsg.alert('Ошибка', 'Не выбрана аптека', function() { base_form.findField('OrgFarmacy_id').focus(true); });
			return false;
		}

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Проверка возможности сохранения рецепта..." });
		loadMask.show();

		record = base_form.findField('MedStaffFact_id').getStore().getById(base_form.findField('MedStaffFact_id').getValue());

		if ( !record ) {
			this.formStatus = 'edit';
			sw.swMsg.alert('Ошибка', 'Не выбран врач', function() { base_form.findField('MedStaffFact_id').focus(true); });
			loadMask.hide();
			return false;
		}

//                record = new object;
//                record.MedPersonal_id = 1;

		post.Drug_IsKEK = base_form.findField('Drug_IsKEK').getValue();
		base_form.findField('MedPersonal_id').setValue(record.get('MedPersonal_id'));

		if ( base_form.findField('EvnRecept_Is7Noz').disabled ) {
			post.EvnRecept_Is7Noz = base_form.findField('EvnRecept_Is7Noz').getValue();
		}

		if ( base_form.findField('EvnRecept_Num').disabled ) {
			post.EvnRecept_Num = base_form.findField('EvnRecept_Num').getValue();
		}

		if ( base_form.findField('EvnRecept_Ser').disabled ) {
			post.EvnRecept_Ser = base_form.findField('EvnRecept_Ser').getValue();
		}

		if ( base_form.findField('ReceptDiscount_id').disabled ) {
			post.ReceptDiscount_id = base_form.findField('ReceptDiscount_id').getValue();
		}

		if ( base_form.findField('ReceptFinance_id').disabled ) {
			post.ReceptFinance_id = base_form.findField('ReceptFinance_id').getValue();
		}

		if ( base_form.findField('ReceptType_id').disabled ) {
			post.ReceptType_id = base_form.findField('ReceptType_id').getValue();
		}
		
		if ( base_form.findField('LpuSection_id') ) {
			post.LpuSection_id = base_form.findField('LpuSection_id').getValue();
		}
        if (base_form.findField('ReceptForm_id')) {
            post.ReceptForm_id = base_form.findField('ReceptForm_id').getValue();
        }
		
		if( base_form.findField('Drug_Price').disabled ) {
			post.Drug_Price = base_form.findField('Drug_Price').getValue();
		}
		post.PersonPrivilege_id = base_form.findField('PrivilegeType_id').getFieldValue('PersonPrivilege_id');
        post.is_mi_1 = (base_form.findField('ReceptForm_id').getValue() == 2) ? true : false;

        var is7noz = base_form.findField('EvnRecept_Is7Noz').getValue();
        var diag_id = base_form.findField('Diag_id').getValue();
        var privilege_type = base_form.findField('PrivilegeType_id').getValue();
        var recept_finance = base_form.findField('ReceptFinance_id').getValue();

		if ( base_form.findField('EvnRecept_id').getValue() == 0 ) {
			Ext.Ajax.request({
				callback: function(opt, success, resp) {
					loadMask.hide();

					if ( resp.responseText == 'error' ) {
						this.formStatus = 'edit';
						loadMask.hide();

						sw.swMsg.alert('Ошибка', 'Ошибка при проверке возможности выдачи рецепта');
						return false;
					}
					else if ( resp.responseText == 'true' ) {
						loadMask.hide();
                        if ((is7noz=='1') && (recept_finance=='2') && getGlobalOptions().recept_diag_control == 2){ //Проверка на соответствие диагноза выбранной льготе
                            Ext.Ajax.request({
								failure: function(result_form, action) {
									this.formStatus = 'edit';
									loadMask.hide();

									if ( action.result ) {
										if ( action.result.Error_Msg ) {
											sw.swMsg.alert('Ошибка', action.result.Error_Msg);
										}
										else {
											sw.swMsg.alert('Ошибка', 'При проверке соответствия диагноза и льготы произошли ошибки');
										}
									}
								}.createDelegate(this),
								success: function(response) {
									loadMask.hide();
									var result = Ext.util.JSON.decode(response.responseText);
									var SaveRecept = false;
									var validReceptCodes = '';

									for (var i = 0; i < result.length; i++) {
										if (result[i].Diag_exists == 1) {
											SaveRecept = true;
										}

										validReceptCodes = validReceptCodes + result[i].Diag_Code + ', ';
									}
									if(result.length==0)
										SaveRecept = true;
									if (SaveRecept) {
										this.doSubmit({
											copy: options.copy,
											postData: post,
											print: options.print
										});
									} else {
										var Msg = ' Диагноз, указанный в рецепте, не соответствует указанной льготе. Указанной льготе соответствуют диагнозы с кодами: ' + validReceptCodes.substring(0, validReceptCodes.length - 2) + '.';
										base_form.findField('Diag_id').clearValue();
										this.formStatus = 'edit';
										loadMask.hide();
										sw.swMsg.alert('Ошибка', Msg);
										return false;

									}
								}.createDelegate(this),
								/*
                                callback: function(opt, success, resp) {
                                    loadMask.hide();
                                    var response_obj = Ext.util.JSON.decode(resp.responseText);

                                    if(!response_obj.success){

                                        base_form.findField('Diag_id').clearValue();
                                        this.formStatus = 'edit';
                                        loadMask.hide();
                                        sw.swMsg.alert('Ошибка', response_obj.Error_Msg);
                                        return false;
                                    }
                                    else {
                                        this.doSubmit({
                                            copy: options.copy,
                                            postData: post,
                                            print: options.print
                                        });
                                    }
                                }.createDelegate(this),
                                */
                                params: {
                                    Diag_id: diag_id,
                                    PrivilegeType_id: privilege_type
                                },
                                url: C_EVNDIAGPRIV_CHECK
                            });
                        }
                        else{
                            this.doSubmit({
                                copy: options.copy,
                                postData: post,
                                print: options.print
                            });
                        }
						/*this.doSubmit({
							copy: options.copy,
							postData: post,
							print: options.print
						});*/
					}
					else {
						sw.swMsg.show({
							buttons: Ext.Msg.YESNO,
							fn: function ( buttonId ) {
								loadMask.hide();

								if ( buttonId == 'yes' ) {
                                    if ((is7noz=='1') && (recept_finance=='2') && getGlobalOptions().recept_diag_control == 2){ //Проверка на соответствие диагноза выбранной льготе
                                        Ext.Ajax.request({
											failure: function(result_form, action) {
												this.formStatus = 'edit';
												loadMask.hide();

												if ( action.result ) {
													if ( action.result.Error_Msg ) {
														sw.swMsg.alert('Ошибка', action.result.Error_Msg);
													}
													else {
														sw.swMsg.alert('Ошибка', 'При проверке соответствия диагноза и льготы произошли ошибки');
													}
												}
											}.createDelegate(this),
											success: function(response) {
												loadMask.hide();
												var result = Ext.util.JSON.decode(response.responseText);
												var SaveRecept = false;
												var validReceptCodes = '';

												for (var i = 0; i < result.length; i++) {
													if (result[i].Diag_exists == 1) {
														SaveRecept = true;
													}

													validReceptCodes = validReceptCodes + result[i].Diag_Code + ', ';
												}
												if(result.length==0)
													SaveRecept = true;
												if (SaveRecept) {
													this.doSubmit({
														copy: options.copy,
														postData: post,
														print: options.print
													});
												} else {
													var Msg = ' Диагноз, указанный в рецепте, не соответствует указанной льготе. Указанной льготе соответствуют диагнозы с кодами: ' + validReceptCodes.substring(0, validReceptCodes.length - 2) + '.';
													base_form.findField('Diag_id').clearValue();
													this.formStatus = 'edit';
													loadMask.hide();
													sw.swMsg.alert('Ошибка', Msg);
													return false;

												}
											}.createDelegate(this),
                                            /*callback: function(opt, success, resp) {
                                                loadMask.hide();
                                                var response_obj = Ext.util.JSON.decode(resp.responseText);

                                                if(!response_obj.success){

                                                    base_form.findField('Diag_id').clearValue();
                                                    this.formStatus = 'edit';
                                                    loadMask.hide();
                                                    sw.swMsg.alert('Ошибка', response_obj.Error_Msg);
                                                    return false;
                                                }
                                                else {
                                                    this.doSubmit({
                                                        copy: options.copy,
                                                        postData: post,
                                                        print: options.print
                                                    });
                                                }
                                            }.createDelegate(this),*/
                                            params: {
                                                Diag_id: diag_id,
                                                PrivilegeType_id: privilege_type
                                            },
                                            url: C_EVNDIAGPRIV_CHECK
                                        });
                                    }
                                    else{
                                        this.doSubmit({
                                            copy: options.copy,
                                            postData: post,
                                            print: options.print
                                        });
                                    }
									/*this.doSubmit({
										copy: options.copy,
										postData: post,
										print: options.print
									});*/
								}
								else {
									this.formStatus = 'edit';
								}
							}.createDelegate(this),
							msg: 'Указанный медикамент уже был выписан сегодня данному пациенту. Сохранить рецепт?',
							title: 'Проверка рецепта'
						});
					}
				}.createDelegate(this),
				params: {
					Drug_id: base_form.findField('Drug_id').getValue(),
					EvnRecept_id: base_form.findField('EvnRecept_id').getValue(),
					EvnRecept_setDate: Ext.util.Format.date(base_form.findField('EvnRecept_setDate').getValue(), 'd.m.Y'),
					mode: 'ident',
					Person_id: base_form.findField('Person_id').getValue()
				},
				url: C_EVNREC_CHECK
			});
		}
		else {
			loadMask.hide();
			/*this.doSubmit({
				copy: options.copy,
				postData: post,
				print: options.print
			});*/
            if ((is7noz=='1') && (recept_finance=='2') && getGlobalOptions().recept_diag_control == 2){ //Проверка на соответствие диагноза выбранной льготе
                Ext.Ajax.request({
					failure: function(result_form, action) {
						this.formStatus = 'edit';
						loadMask.hide();

						if ( action.result ) {
							if ( action.result.Error_Msg ) {
								sw.swMsg.alert('Ошибка', action.result.Error_Msg);
							}
							else {
								sw.swMsg.alert('Ошибка', 'При проверке соответствия диагноза и льготы произошли ошибки');
							}
						}
					}.createDelegate(this),
					success: function(response) {
						loadMask.hide();
						var result = Ext.util.JSON.decode(response.responseText);
						var SaveRecept = false;
						var validReceptCodes = '';

						for (var i = 0; i < result.length; i++) {
							if (result[i].Diag_exists == 1) {
								SaveRecept = true;
							}

							validReceptCodes = validReceptCodes + result[i].Diag_Code + ', ';
						}
						if(result.length==0)
							SaveRecept = true;
						if (SaveRecept) {
							this.doSubmit({
								copy: options.copy,
								postData: post,
								print: options.print
							});
						} else {
							var Msg = ' Диагноз, указанный в рецепте, не соответствует указанной льготе. Указанной льготе соответствуют диагнозы с кодами: ' + validReceptCodes.substring(0, validReceptCodes.length - 2) + '.';
							base_form.findField('Diag_id').clearValue();
							this.formStatus = 'edit';
							loadMask.hide();
							sw.swMsg.alert('Ошибка', Msg);
							return false;

						}
					}.createDelegate(this),
                    /*callback: function(opt, success, resp) {
                        loadMask.hide();
                        var response_obj = Ext.util.JSON.decode(resp.responseText);

                        if(!response_obj.success){

                            base_form.findField('Diag_id').clearValue();
                            this.formStatus = 'edit';
                            loadMask.hide();
                            sw.swMsg.alert('Ошибка', response_obj.Error_Msg);
                            return false;
                        }
                        else {
                            this.doSubmit({
                                copy: options.copy,
                                postData: post,
                                print: options.print
                            });
                        }
                    }.createDelegate(this),*/
                    params: {
                        Diag_id: diag_id,
                        PrivilegeType_id: privilege_type
                    },
                    url: C_EVNDIAGPRIV_CHECK
                });
            }
            else{
                this.doSubmit({
                    copy: options.copy,
                    postData: postParams,
                    print: options.print
                });
            }
		}
	},
	doSubmit: function(options) {
		// options @Object
		// options.copy @Boolean 
		// options.postData @Object Данные для сохранения
		// options.print @Boolean Вызывать печать рецепта, если true

		if ( !options || typeof options != 'object' ) {
			this.formStatus = 'edit';
			return false;
		}

		var base_form = this.findById('EvnReceptEditForm').getForm();
		var person_information = this.findById('EREF_PersonInformationFrame');

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Подождите, идет сохранение..." });
		loadMask.show();
        //Проверка на соответствие диагнозов и льготных категорий

        //Сохранение
		base_form.submit({
			failure: function(result_form, action) {
				this.formStatus = 'edit';
				loadMask.hide();

				if ( action.result ) {
					if ( action.result.Error_Msg ) {
						sw.swMsg.alert('Ошибка', action.result.Error_Msg);
					}
					else {
						sw.swMsg.alert('Ошибка', 'При сохранении произошли ошибки [Тип ошибки: 3]');
					}
				}
			}.createDelegate(this),
			params: options.postData,
			success: function(result_form, action) {
				this.formStatus = 'edit';
				loadMask.hide();

				if ( action.result ) {
					if ( action.result.EvnRecept_id ) {
						var drug_name = null;
						var evn_recept_id = action.result.EvnRecept_id;
						var med_personal_fio = null;
						var privilege_type_code = null;
						var response = new Object();
						var server_id = base_form.findField('Server_id').getValue();

						this.action = 'view';
						this.enableEdit(false);
						this.setTitle(WND_DLO_RCPTVIEW);

						//base_form.findField('Drug_id').disable(); Пока закомментировал   !!!
						//base_form.findField('DrugMnn_id').disable(); Пока закомментировал
						//base_form.findField('EvnRecept_Signa').disable();
						base_form.findField('EvnRecept_Signa').setAllowBlank(true);
						base_form.findField('LpuSection_id').disable();
						base_form.findField('MedStaffFact_id').disable();
						//base_form.findField('OrgFarmacy_id').disable(); Пока закомментировал
						base_form.findField('PrivilegeType_id').disable();
						base_form.findField('ReceptFinance_id').disable();

						base_form.findField('EvnRecept_id').setValue(evn_recept_id);
						base_form.findField('ReceptType_id').disable();

						var drug_record = base_form.findField('Drug_id').getStore().getById(base_form.findField('Drug_id').getValue());
						if ( drug_record ) {
							drug_name = drug_record.get('Drug_Name');
						}

						var med_personal_record = base_form.findField('MedStaffFact_id').getStore().getById(base_form.findField('MedStaffFact_id').getValue());
						if ( med_personal_record ) {
							med_personal_fio = med_personal_record.get('MedPersonal_Fio');
						}

						var privilege_type_record = base_form.findField('PrivilegeType_id').getStore().getById(base_form.findField('PrivilegeType_id').getValue());
						if ( privilege_type_record ) {
							privilege_type_code = privilege_type_record.get('PrivilegeType_Code');
						}

						response.Drug_Name = drug_name;
						response.EvnRecept_id = evn_recept_id;
						response.EvnRecept_Num = base_form.findField('EvnRecept_Num').getValue();
						response.EvnRecept_pid = base_form.findField('EvnRecept_pid').getValue();
						response.EvnRecept_Ser = base_form.findField('EvnRecept_Ser').getValue();
						response.EvnRecept_setDate = base_form.findField('EvnRecept_setDate').getValue();
						response.MedPersonal_Fio = med_personal_fio;
						response.MorbusType_id = 1;
						response.Person_Birthday = person_information.getFieldValue('Person_Birthday');
						response.Person_Firname = person_information.getFieldValue('Person_Firname');
						response.Person_id = base_form.findField('Person_id').getValue();
						response.Person_Secname = person_information.getFieldValue('Person_Secname');
						response.Person_Surname = person_information.getFieldValue('Person_Surname');
						response.PersonEvn_id = base_form.findField('PersonEvn_id').getValue();
						response.PrivilegeType_Code = privilege_type_code;
						response.Server_id = server_id;

						this.callback({ EvnReceptData: response });

						if ( options.print ) {
                            var evn_recept = new sw.Promed.EvnRecept({EvnRecept_id: evn_recept_id});
                            evn_recept.print();
						}
						else if ( options.copy ) {
							this.doCopy();
						}
						else {
							this.hide();
						}
					}
					else {
						if ( action.result.Error_Msg ) {
							sw.swMsg.alert('Ошибка', action.result.Error_Msg);
						}
						else {
							sw.swMsg.alert('Ошибка', 'При сохранении произошли ошибки [Тип ошибки: 1]');
						}
					}
				}
				else {
					sw.swMsg.alert('Ошибка', 'При сохранении произошли ошибки [Тип ошибки: 2]');
				}
			}.createDelegate(this)
		});
	},
	draggable: true,
	enableEdit: function(enable) {
		var base_form = this.findById('EvnReceptEditForm').getForm();

		var form_fields = new Array(
			'Diag_id',
			'Drug_IsKEK',
			'EvnRecept_VKProtocolNum',
			'EvnRecept_VKProtocolDT',
			'CauseVK_id',
			'Drug_IsMnn',
			//---
			'Drug_id',
			'DrugMnn_id',
			'OrgFarmacy_id',
			'Drug_Price',
			'EvnRecept_Kolvo',
			'EvnRecept_Signa',
			'EvnRecept_IsDelivery',
			//--
			'EvnRecept_Is7Noz',
			'EvnRecept_Kolvo',
			'EvnRecept_setDate',
			'ReceptValid_id',
            'ReceptForm_id',
			'PrescrSpecCause_cb',
			'PrescrSpecCause_id',
			'ReceptUrgency_id',
			'EvnRecept_IsExcessDose'
		);

		if ( enable ) {
			this.buttons[0].show();
			// this.buttons[1].show();

			for ( var i = 0; i < form_fields.length; i++ ) {
				base_form.findField(form_fields[i]).enable();
			}
		}
		else {
			this.buttons[0].hide();
			// this.buttons[1].hide();

			for ( var i = 0; i < form_fields.length; i++ ) {
				base_form.findField(form_fields[i]).disable();
			}
		}
	},
	formStatus: 'edit',
	get7NozDiagList: function() {
		//return [ 'C92.1', 'C88.0', 'C90.0', 'C82.', 'C82.0', 'C82.1', 'C82.2', 'C82.7', 'C82.9', 'C83.0', 'C83.1', 'C83.3', 'C83.4', 'C83.8', 'C83.9', 'C85', 'C85.0', 'C85.1', 'C85.7', 'C85.9', 'C91.1', 'E84.', 'E84.0', 'E84.1', 'E84.8', 'E84.9', 'D66.', 'D67.', 'D68.0', 'G35.', 'E23.0', 'E75.2', 'E75.5', 'Z94.0', 'Z94.1', 'Z94.4', 'Z94.8', 'C92', 'C88', 'C90', 'C82', 'C83', 'C85', 'C91', 'E84', 'D66', 'D67', 'D68', 'G35', 'E23', 'E75', 'Z94' ]
		return [ 'C92.1', 'C88.0', 'C90.0', 'C82', 'C82.0', 'C82.1', 'C82.2', 'C82.7', 'C82.9', 'C82.3', 'C82.4', 'C82.5', 'C82.6', 'C83.0', 'C83.1', 'C83.3', 'C83.4', 'C83.8', 'C83.9', 'C85', 'C85.1', 'C85.7', 'C85.9', 'C85.2', 'C91.1', 'E84', 'E84.0', 'E84.1', 'E84.8', 'E84.9', 'D66.', 'D66', 'D67.', 'D67', 'D68.0', 'G35.','G35', 'E23.0', 'E75.2', 'Z94.0', 'Z94.1', 'Z94.4', 'Z94.8' ];
	},
	height: 500,
	id: 'EvnReceptEditWindow',
	initComponent: function() {
		var wnd = this;
		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.doSave({
						checkPersonAge: true,
						checkPersonDeadDT: true,
						checkPersonSnils: true,
						copy: false,
						print: false
					});
				}.createDelegate(this),
				iconCls: 'save16',
				tabIndex: TABINDEX_EREF + 20,
				text: BTN_FRMSAVE,
				tooltip: 'Сохранить введенные данные'
			}, {
				handler: function() {
					this.doCopy();
				}.createDelegate(this),
				iconCls: 'copy16',
				onShiftTabAction: function () {
					if ( !this.buttons[0].hidden ) {
						this.buttons[0].focus();
					}
					else if ( !this.findById('EvnReceptEditForm').getForm().findField('EvnRecept_Signa').disabled ) {
						this.findById('EvnReceptEditForm').getForm().findField('EvnRecept_Signa').focus(true);
					}
					else if ( !this.findById('EvnReceptEditForm').getForm().findField('EvnRecept_Kolvo').disabled ) {
						this.findById('EvnReceptEditForm').getForm().findField('EvnRecept_Kolvo').focus(true);
					}
					else {
						this.buttons[this.buttons.length - 1].focus();
					}
				}.createDelegate(this),
				onTabAction: function () {
					this.buttons[2].focus();
				}.createDelegate(this),
				tabIndex: TABINDEX_EREF + 21,
				text: 'Копи<u>я</u>',
				tooltip: 'Копия рецепта'
			}, {
				handler: function() {
					this.printRecept();
				}.createDelegate(this),
				iconCls: 'print16',
				tabIndex: TABINDEX_EREF + 22,
				text: '<u>П</u>ечать',
				tooltip: 'Напечатать рецепт'
			}, {
				text: '-'
			},
			HelpButton(this),
			{
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'cancel16',
				onShiftTabAction: function () {
					this.buttons[2].focus();
				}.createDelegate(this),
				onTabAction: function () {
					if ( !this.findById('EvnReceptEditForm').getForm().findField('ReceptType_id').disabled ) {
						this.findById('EvnReceptEditForm').getForm().findField('ReceptType_id').focus(true);
					}
					else if ( !this.findById('EvnReceptEditForm').getForm().findField('EvnRecept_setDate').disabled ) {
						this.findById('EvnReceptEditForm').getForm().findField('EvnRecept_setDate').focus(true);
					}
					else if ( this.action != 'view' ) {
						this.buttons[0].focus();
					}
					else {
						this.buttons[1].focus();
					}
				}.createDelegate(this),
				tabIndex: TABINDEX_EREF + 23,
				text: BTN_FRMCANCEL,
				tooltip: 'Закрыть окно'
			}],
			items: [ new sw.Promed.PersonInformationPanel({
				button2Callback: function(callback_data) {
					var base_form = this.findById('EvnReceptEditForm').getForm();

					base_form.findField('PersonEvn_id').setValue(callback_data.PersonEvn_id);
					base_form.findField('Server_id').setValue(callback_data.Server_id);

					this.findById('EREF_PersonInformationFrame').load({
						Person_id: callback_data.Person_id,
						Server_id: callback_data.Server_id
					});
				}.createDelegate(this),
				button2OnHide: function() {
					var base_form = this.findById('EvnReceptEditForm').getForm();

					if ( !base_form.findField('ReceptType_id').disabled ) {
						base_form.findField('ReceptType_id').focus(false);
					}
					else if ( !base_form.findField('EvnRecept_setDate').disabled ) {
						base_form.findField('EvnRecept_setDate').focus(true);
					}
					else {
						this.buttons[this.buttons.length - 1].focus();
					}
				}.createDelegate(this),
				button3OnHide: function() {
					var base_form = this.findById('EvnReceptEditForm').getForm();

					if ( !base_form.findField('ReceptType_id').disabled ) {
						base_form.findField('ReceptType_id').focus(false);
					}
					else if ( !base_form.findField('EvnRecept_setDate').disabled ) {
						base_form.findField('EvnRecept_setDate').focus(true);
					}
					else {
						this.buttons[this.buttons.length - 1].focus();
					}
				}.createDelegate(this),
				button4OnHide: function() {
					var base_form = this.findById('EvnReceptEditForm').getForm();

					var evn_recept_set_date = base_form.findField('EvnRecept_setDate').getValue();

					if ( !evn_recept_set_date ) {
						base_form.findField('EvnRecept_setDate').focus(false);
						return false;
					}

					var privilege_type_combo = base_form.findField('PrivilegeType_id');
					var recept_finance_combo = base_form.findField('ReceptFinance_id');

					var person_id = base_form.findField('Person_id').getValue();
					var privilege_type_id = privilege_type_combo.getValue();
					var recept_finance_id = recept_finance_combo.getValue();

					privilege_type_combo.getStore().load({
						callback: function(records, options, success) {
							var privilege_type_record = null;

							if ( privilege_type_combo.getStore().getCount() == 1 ) {
								privilege_type_record = privilege_type_combo.getStore().getAt(0);
							}
							else {
								privilege_type_record = privilege_type_combo.getStore().getById(privilege_type_id);
							}

							if ( privilege_type_record ) {
								recept_finance_combo.setValue(privilege_type_record.get('ReceptFinance_id'));
								recept_finance_combo.fireEvent('change', recept_finance_combo, privilege_type_record.get('ReceptFinance_id'), null);
							}
							else {
								var only_fed = true;
								var only_reg = true;

								privilege_type_combo.getStore().each(function(record) {
									if ( record.get('PersonPrivilege_IsClosed') == 1 ) {
										if ( record.get('ReceptFinance_id') == 1 ) {
											only_reg = false;
										}
										else if ( record.get('ReceptFinance_id') == 2 ) {
											only_fed = false;
										}
									}
								});

								if ( only_fed == true && only_reg == false ) {
									recept_finance_combo.setValue(1);
									recept_finance_combo.fireEvent('change', recept_finance_combo, 1, null);
								}
								else if ( only_fed == false && only_reg == true ) {
									recept_finance_combo.setValue(2);
									recept_finance_combo.fireEvent('change', recept_finance_combo, 2, null);
								}
								else if ( recept_finance_id ) {
									recept_finance_combo.setValue(recept_finance_id);
									recept_finance_combo.fireEvent('change', recept_finance_combo, recept_finance_id, null);
								}
							}

							if ( !privilege_type_combo.disabled ) {
								privilege_type_combo.focus(false);
							}
							else {
								base_form.findField('EvnRecept_setDate').focus(false);
							}
						}.createDelegate(this),
						params: {
							date: Ext.util.Format.date(evn_recept_set_date, 'd.m.Y'),
							Person_id: person_id
						}
					});
				}.createDelegate(this),
				id: 'EREF_PersonInformationFrame',
				region: 'north'
			}),
			new Ext.form.FormPanel({
				autoScroll: true,
				bodyStyle: 'padding: 0.5em;',
				border: false,
				frame: false,
				id: 'EvnReceptEditForm',
				items: [{
					name: 'EvnRecept_id',
					value: 0,
					xtype: 'hidden'
				}, {
					name: 'EvnRecept_pid',
					value: 0,
					xtype: 'hidden'
				}, {
					name: 'EvnRecept_IsSigned',
					value: 0,
					xtype: 'hidden'
				}, {
					name: 'Person_id',
					value: 0,
					xtype: 'hidden'
				}, {
					name: 'MedPersonal_id',
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
					name: 'EvnRecept_IsExtemp',
					value: 1,
					xtype: 'hidden'
				},
				new sw.Promed.Panel({
					autoHeight: true,
					bodyStyle: 'padding-top: 0.5em;',
					border: true,
					collapsible: true,
					id: 'EREF_ReceptPanel',
					layout: 'form',
					style: 'margin-bottom: 0.5em;',
					title: '1. Рецепт',
					items: [
						{
							allowBlank: false,
							fieldLabel: 'Дата',
							format: 'd.m.Y',
							listeners: {
								'change': function(field, newValue, oldValue) {
									//if (blockedDateAfterPersonDeath('personpanelid', 'EREF_PersonInformationFrame', field, newValue, oldValue)) return;
									var that = this;
									var base_form = this.findById('EvnReceptEditForm').getForm();

									this.setReceptFormFilter();

									var new_date = newValue.format('Y-m-d');
									var person_information = this.findById('EREF_PersonInformationFrame');
									//var person_age = swGetPersonAge(person_information.getFieldValue('Person_Birthday'), base_form.findField('EvnRecept_setDate').getValue());
									var person_age = swGetPersonAge(person_information.getFieldValue('Person_Birthday'), new_date);
									var sex_code = person_information.getFieldValue('Sex_Code');
									var is_retired = ((sex_code == 2 && person_age >= 55) || (sex_code == 1 && person_age >= 60)); //опредлеяем, пенсионер ли наш пациент
									var ReceptForm_id = !Ext.isEmpty(base_form.findField('ReceptForm_id').getValue())?base_form.findField('ReceptForm_id').getValue():0;

									//https://redmine.swan.perm.ru/issues/91119
									if(new_date >= '2016-07-30'){
										if(ReceptForm_id == 5)
											base_form.findField('ReceptValid_id').getStore().filterBy(function(rec) {
												return (rec.get('ReceptValid_Code').toString().inlist(['11']));
											});
										if(ReceptForm_id == 1)
											base_form.findField('ReceptValid_id').getStore().filterBy(function(rec) {
												return (rec.get('ReceptValid_Code').toString().inlist(['9','10','11']));
											});
										if(ReceptForm_id == 3)
											base_form.findField('ReceptValid_id').getStore().filterBy(function(rec) {
												return (rec.get('ReceptValid_Code').toString().inlist(['8']));
											});
										if(ReceptForm_id == 2)
											base_form.findField('ReceptValid_id').getStore().filterBy(function(rec) {
												return (rec.get('ReceptValid_Code').toString().inlist(['1','2','5']));
											});
									}
									else {
										if(ReceptForm_id == 2)
											base_form.findField('ReceptValid_id').getStore().filterBy(function(rec) {
												return (rec.get('ReceptValid_Code').toString().inlist(new_date >= '2016-01-01'?['4','9','10','11']:['1', '2']));
											});
										else
											base_form.findField('ReceptValid_id').getStore().filterBy(function(rec) {
												return (rec.get('ReceptValid_Code').toString().inlist(new_date >= '2016-01-01'?['4', '9', '10', '11']:['1', '2', '4', '7']));
											});

										// Устанавливаем значение по-умолчанию
										var index = base_form.findField('ReceptValid_id').getStore().findBy(function(rec) {
											if(new_date >= '2016-01-01')
												return is_retired?(rec.get('ReceptValid_Code') == 10):(rec.get('ReceptValid_Code') == 9);
											else
												return is_retired?(rec.get('ReceptValid_Code') == 2):(rec.get('ReceptValid_Code') == 1);
										});
										if ( index >= 0 ) {
											base_form.findField('ReceptValid_id').setValue(base_form.findField('ReceptValid_id').getStore().getAt(index).get('ReceptValid_id'));
										}
									}
									var drug_combo = base_form.findField('Drug_id');
									var drug_mnn_combo = base_form.findField('DrugMnn_id');
									var lpu_section_combo = base_form.findField('LpuSection_id');
									var med_personal_combo = base_form.findField('MedStaffFact_id');
									var privilege_type_combo = base_form.findField('PrivilegeType_id');
									var recept_finance_combo = base_form.findField('ReceptFinance_id');

									var lpu_section_id = lpu_section_combo.getValue();
									var med_staff_fact_id = med_personal_combo.getValue();
									var person_id = base_form.findField('Person_id').getValue();
									var privilege_type_id = privilege_type_combo.getValue();
									var recept_finance_id = recept_finance_combo.getValue();

									lpu_section_combo.clearValue();
									lpu_section_combo.getStore().removeAll();
									med_personal_combo.clearValue();
									med_personal_combo.getStore().removeAll();
									recept_finance_combo.clearValue();
									privilege_type_combo.getStore().clearFilter();

									if ( !newValue ) {
										lpu_section_combo.disable();
										med_personal_combo.disable();
										recept_finance_combo.disable();
										base_form.findField('EvnRecept_Is7Noz').disable();

										recept_finance_combo.fireEvent('change', recept_finance_combo, null, 1);

										return false;
									}
									if(base_form.findField('ReceptType_id').getValue() != '1')
									{
										this.setReceptNumber();
										this.setReceptSerial();
									}

									drug_combo.getStore().baseParams.Date = Ext.util.Format.date(newValue, 'd.m.Y');
									drug_mnn_combo.getStore().baseParams.Date = Ext.util.Format.date(newValue, 'd.m.Y');

									lpu_section_combo.enable();
									med_personal_combo.enable();
									recept_finance_combo.enable();

									// Загружаем список льгот человека
									privilege_type_combo.getStore().load({
										callback: function(records, options, success) {
											var privilege_type_record = null;
											var enableNoz = false;

											privilege_type_combo.getStore().each(function(record) {
												// Если льгота не закрыта
												if ( record.get('PersonPrivilege_IsClosed') == 1 && (record.get('PersonRefuse_IsRefuse') != 2 || record.get('PersonPrivilege_IsPersonDisp') == 2) ) {
													enableNoz = true;
												}
											});

											/*
											 * Закомментировано, т.к. нозология контролируется по регистру
											if ( enableNoz == true ) {
												base_form.findField('EvnRecept_Is7Noz').enable(); console.log('enable');
											}
											else {
												base_form.findField('EvnRecept_Is7Noz').disable();
												base_form.findField('EvnRecept_Is7Noz').setValue(1);
											}
											*/

											// Выбираем запись для установки значения
											if ( privilege_type_combo.getStore().getCount() == 1 ) {
												// Если запись всего одна
												privilege_type_record = privilege_type_combo.getStore().getAt(0);
											}
											else {
												// Если есть запись, соответствующая старому значению
												privilege_type_record = privilege_type_combo.getStore().getById(privilege_type_id);
											}

											// Запись найдена
											if ( privilege_type_record ) {
												if ( !recept_finance_id ) {
													recept_finance_id = privilege_type_record.get('ReceptFinance_id');
												}

												// Устанавливаем значение поля "Тип финансирования"
												recept_finance_combo.setValue(recept_finance_id);
												recept_finance_combo.fireEvent('change', recept_finance_combo, recept_finance_id, null);
											}
											// Запись не найдена
											else {
												// Проверяем список льгот по типу льготы
												var only_fed = true; // Признак "Только федеральные"
												var only_reg = true; // Признак "Только региональные"

												privilege_type_combo.getStore().each(function(record) {
													// Если льгота не закрыта
													if ( record.get('PersonPrivilege_IsClosed') == 1 ) {
														// Если тип финансирования "Федеральный бюджет"
														if ( record.get('ReceptFinance_id') == 1 ) {
															only_reg = false; // то не только региональные
														}
														// Если тип финансирования "Субъект РФ"
														else if ( record.get('ReceptFinance_id') == 2 ) {
															only_fed = false; // то не только федеральные
														}
													}
												});

												// Если только федеральные льготы
												if ( only_fed == true && only_reg == false ) {
													// устанавливаем тип финансирования "Федеральный бюджет"
													recept_finance_combo.setValue(1);
													recept_finance_combo.fireEvent('change', recept_finance_combo, 1, null);
												}
												// Если только региональные льготы
												else if ( only_fed == false && only_reg == true ) {
													// устанавливаем тип финансирования "Субъект РФ"
													recept_finance_combo.setValue(2);
													recept_finance_combo.fireEvent('change', recept_finance_combo, 2, null);
												}
											}


											wnd.list = [];

											Ext.Ajax.request({
												callback: function(options, success, response) {
													if ( success ) {
														var response_obj = Ext.util.JSON.decode(response.responseText);

														if ( response_obj.length == 0 ) {
															base_form.findField('EvnRecept_Is7Noz').setValue(1);
															base_form.findField('EvnRecept_Is7Noz').setDisabled(true);

															if ( that.action == 'add' ) {
																base_form.findField('EvnRecept_Is7Noz').fireEvent('change', base_form.findField('EvnRecept_Is7Noz'), base_form.findField('EvnRecept_Is7Noz').getValue());
															}
														}
														else if (base_form.findField('EvnRecept_Is7Noz').getValue() == 2)
														{
															if ( that.action == 'add' ) {
																base_form.findField('EvnRecept_Is7Noz').fireEvent('change', base_form.findField('EvnRecept_Is7Noz'), base_form.findField('EvnRecept_Is7Noz').getValue());
																base_form.findField('ReceptFinance_id').fireEvent('change', base_form.findField('ReceptFinance_id'), base_form.findField('ReceptFinance_id').getValue());
															}

														} else
														{
															if ( that.action == 'add' ) {
																base_form.findField('EvnRecept_Is7Noz').setDisabled(false);
															}
														}
													}
													else {
														sw.swMsg.alert('Ошибка', 'Ошибка при получении диагнозов по ВЗН', function() {} );
													}
												}.createDelegate(this),
												url: '/?c=PersonRegister&m=loadList',
												params: {
													Person_id: base_form.findField('Person_id').getValue(),//1893958
													PersonRegisterType_id: 49,
													PersonRegister_Date: newValue
												}
											});
										}.createDelegate(this)
									});

									var user_med_staff_fact_id = this.UserMedStaffFact_id;
									var user_lpu_section_id = this.UserLpuSection_id;
									var user_med_staff_facts = this.UserMedStaffFacts;
									var user_lpu_sections = this.UserLpuSections;

									var section_filter_params = {
										isDlo: true,
										onDate: Ext.util.Format.date(newValue, 'd.m.Y')
									}

									var medstafffact_filter_params = {
										isDlo: true,
										onDate: Ext.util.Format.date(newValue, 'd.m.Y'),
										regionCode: 2,
										fromRecept: true
									}

									// фильтр или на конкретное место работы или на список мест работы
									if ( user_med_staff_fact_id && user_lpu_section_id && this.action == 'add' )
									{
										section_filter_params.id = user_lpu_section_id;
										medstafffact_filter_params.id = user_med_staff_fact_id;
									}
									else
									if ( user_med_staff_facts && user_lpu_sections && this.action == 'add' )
									{
										//section_filter_params.ids = user_lpu_sections;
										//medstafffact_filter_params.ids = user_med_staff_facts;
									}


									setLpuSectionGlobalStoreFilter(section_filter_params);
									setMedStaffFactGlobalStoreFilter(medstafffact_filter_params);

									//sw.Promed.vac.utils.consoleLog('medstafffact_filter_params');
									//sw.Promed.vac.utils.consoleLog( medstafffact_filter_params);
									//console.log(swMedStaffFactGlobalStore);
									//swLpuSectionGlobalStore.load();
									//setMedStaffFactGlobalStoreFilter(medstafffact_filter_params);
									//swMedStaffFactGlobalStore.load();

									lpu_section_combo.getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));
									med_personal_combo.getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));

									var lpu_section_record = lpu_section_combo.getStore().getById(lpu_section_id);
									var med_personal_record = med_personal_combo.getStore().getById(med_staff_fact_id);

									if ( !lpu_section_record ) {
										lpu_section_combo.fireEvent('change', lpu_section_combo, -1, lpu_section_id);
									}
									else {
										lpu_section_combo.setValue(lpu_section_id);
									}
									if ( !med_personal_record ) {
										med_personal_combo.fireEvent('change', med_personal_combo, -1, med_staff_fact_id);
									}
									else {
										med_personal_combo.setValue(med_staff_fact_id);
										base_form.findField('MedStaffFact_id').setValue(med_staff_fact_id);
									}

									/*
										если форма отурыта на добавление и задано отделение и
										место работы, то устанавливаем их не даем редактировать вообще
									*/
									if ( this.action == 'add' && user_med_staff_fact_id && user_lpu_section_id )
									{
										if ( lpu_section_combo.getStore().getById(user_lpu_section_id) ) {
											lpu_section_combo.setValue(user_lpu_section_id);
											lpu_section_combo.disable();
										}
										if ( med_personal_combo.getStore().getById(user_med_staff_fact_id) ) {
											med_personal_combo.setValue(user_med_staff_fact_id);
											med_personal_combo.disable();
										}
									}
									else
									/*
										если форма отурыта на добавление и задан список отделений и
										мест работы, но он состоит из одного элемета,
										то устанавливаем значение и не даем редактировать
									*/
									if ( this.action == 'add' && this.UserMedStaffFacts && this.UserMedStaffFacts.length == 1 )
									{
										// список состоит из одного элемента (устанавливаем значение и не даем редактировать)
										if ( lpu_section_combo.getStore().getById(this.UserLpuSections[0]) ) {
											lpu_section_combo.setValue(this.UserLpuSections[0]);
											lpu_section_combo.disable();
										}
										if ( med_personal_combo.getStore().getById(this.UserMedStaffFacts[0]) ) {
											med_personal_combo.setValue(this.UserMedStaffFacts[0]);
											med_personal_combo.disable();
										}
									}
								}.createDelegate(this),
								'keydown': function (inp, e) {
									if ( e.shiftKey == true && e.getKey() == Ext.EventObject.TAB ) {
										e.stopEvent();

										if ( !this.findById('EvnReceptEditForm').getForm().findField('ReceptType_id').disabled ) {
											this.findById('EvnReceptEditForm').getForm().findField('ReceptType_id').focus(true);
										}
										else {
											this.buttons[this.buttons.length - 1].focus();
										}
									}
								}.createDelegate(this)
							},
							name: 'EvnRecept_setDate',
							plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
							tabIndex: TABINDEX_EREF + 2,
							validateOnBlur: true,
							xtype: 'swdatefield'
						}, {
                            allowBlank: false,
                            codeField: 'ReceptForm_Code',
                            displayField: 'ReceptForm_Name',
                            editable: false,
                            fieldLabel: 'Форма рецепта',
                            hiddenName: 'ReceptForm_id',
                            lastQuery: '',
                            listeners: {
                                'change': function(combo, newValue, oldValue) {
                                    var base_form = this.findById('EvnReceptEditForm').getForm();
									var new_date = Ext.util.Format.date(base_form.findField('EvnRecept_setDate').getValue(), 'Y-m-d');

									var person_information = wnd.findById('EREF_PersonInformationFrame');
									var person_age = swGetPersonAge(person_information.getFieldValue('Person_Birthday'), new_date);
									var sex_code = person_information.getFieldValue('Sex_Code');
									var is_retired = ((sex_code == 2 && person_age >= 55) || (sex_code == 1 && person_age >= 60)); //опредлеяем, пенсионер ли наш пациент
									// Устанавливаем значение по-умолчанию
									var index = base_form.findField('ReceptValid_id').getStore().findBy(function(rec) {
										if(new_date >= '2016-01-01')
											return is_retired?(rec.get('ReceptValid_Code') == 10):(rec.get('ReceptValid_Code') == 9);
										else
											return is_retired?(rec.get('ReceptValid_Code') == 2):(rec.get('ReceptValid_Code') == 1);
									});
									if ( index >= 0 ) {
										base_form.findField('ReceptValid_id').setValue(base_form.findField('ReceptValid_id').getStore().getAt(index).get('ReceptValid_id'));
									}

                                    var drug_combo = base_form.findField('Drug_id');
                                    drug_combo.clearValue();
                                    drug_combo.getStore().removeAll();
                                    drug_combo.lastQuery = '';
                                    drug_combo.getStore().baseParams.query = '';
                                    if (newValue == 2){
                                        /*base_form.findField('ReceptValid_id').getStore().filterBy(function(rec) {
                                            return (rec.get('ReceptValid_Code').toString().inlist([ '1', '2']));
                                        });*/
										base_form.findField('ReceptValid_id').getStore().filterBy(function(rec) {
											return (rec.get('ReceptValid_Code').toString().inlist(new_date >= '2016-01-01'?['4','9','10','11']:['1', '2']));
										});
                                        base_form.findField('Drug_IsMnn').setValue(1);
                                        //base_form.findField('EvnRecept_Signa').disable();
                                        base_form.findField('EvnRecept_Signa').setAllowBlank(true);
                                        //base_form.findField('DrugMnn_id').disable(); Пока закомментировал
                                        base_form.findField('DrugMnn_id').setAllowBlank(true);
                                        drug_combo.getStore().baseParams.is_mi_1 = true;
                                    }
                                    else{
                                        /*base_form.findField('ReceptValid_id').getStore().filterBy(function(rec) {
                                            return (rec.get('ReceptValid_Code').toString().inlist([ '1', '2', '4', '7' ]));
                                        });*/
										base_form.findField('ReceptValid_id').getStore().filterBy(function(rec) {
											return (rec.get('ReceptValid_Code').toString().inlist(new_date >= '2016-01-01'?['4', '9', '10', '11']:['1', '2', '4', '7']));
										});
                                        base_form.findField('Drug_IsMnn').setValue(2);
                                        base_form.findField('EvnRecept_Signa').enable();
                                        base_form.findField('EvnRecept_Signa').setAllowBlank(false);
                                        base_form.findField('DrugMnn_id').enable();
                                        base_form.findField('DrugMnn_id').setAllowBlank(false);
                                        drug_combo.getStore().baseParams.is_mi_1 = false;
                                    }
									//https://redmine.swan.perm.ru/issues/91119
									if(new_date >= '2016-07-30'){
										if(newValue == 5)
											base_form.findField('ReceptValid_id').getStore().filterBy(function(rec) {
												return (rec.get('ReceptValid_Code').toString().inlist(['11']));
											});
										if(newValue == 1)
											base_form.findField('ReceptValid_id').getStore().filterBy(function(rec) {
												return (rec.get('ReceptValid_Code').toString().inlist(['9','10','11']));
											});
										if(newValue == 3)
											base_form.findField('ReceptValid_id').getStore().filterBy(function(rec) {
												return (rec.get('ReceptValid_Code').toString().inlist(['8']));
											});
										if(newValue == 2)
											base_form.findField('ReceptValid_id').getStore().filterBy(function(rec) {
												return (rec.get('ReceptValid_Code').toString().inlist(['1','2','5']));
											});
									}
                                    if(base_form.findField('ReceptType_id').getValue() != '1'){
                                        this.setReceptNumber();
                                        this.setReceptSerial();
                                    }
                                    base_form.findField('ReceptValid_id').setValue();
                                    drug_combo.getStore().load();

									this.setVKProtocolFieldsVisible();
                                    this.setReceptTypeFilter();
									var ReceptUrgencyCombo = base_form.findField('ReceptUrgency_id');
									if (newValue == 9) {
										base_form.findField('ReceptUrgency_id').enable();
										ReceptUrgencyCombo.showContainer();
									} else {
										ReceptUrgencyCombo.hideContainer();
										ReceptUrgencyCombo.clearValue();
										base_form.findField('ReceptUrgency_id').disable();
									}

									if (newValue != 9) {
										base_form.findField('EvnRecept_IsExcessDose').hideContainer();
									}

									if (base_form.findField('ReceptForm_id').getValue() == 9){
										base_form.findField('PrescrSpecCause_cb').showContainer();
									} else {
										base_form.findField('PrescrSpecCause_cb').hideContainer();
										base_form.findField('PrescrSpecCause_cb').setValue(0);
									}

                                }.createDelegate(this)
                            },
                            store: new Ext.data.Store({
                                autoLoad: false,
                                reader: new Ext.data.JsonReader({
                                    id: 'ReceptForm_id'
                                }, [
                                    { name: 'ReceptForm_id', mapping: 'ReceptForm_id', type: 'int', hidden: 'true' },
                                    { name: 'ReceptForm_Code', mapping: 'ReceptForm_Code' },
                                    { name: 'ReceptForm_Name', mapping: 'ReceptForm_Name' },
                                    { name: 'ReceptForm_begDate', type: 'date', dateFormat: 'd.m.Y' },
                                    { name: 'ReceptForm_endDate', type: 'date', dateFormat: 'd.m.Y' }
                                ]),
                                url: C_RECEPTFORM_GET_LIST
                            }),

                            tpl: new Ext.XTemplate(
                                '<tpl for="."><div class="x-combo-list-item">',
                                '<table style="border: 0;"><tr><td style="width: 25px;"><font color="red">{ReceptForm_Code}</font></td><td style="font-weight: normal;">{ReceptForm_Name}</td></tr></table>',
                                '</div></tpl>'
                            ),
                            validateOnBlur: true,
                            valueField: 'ReceptForm_id',
                            width: 517,
                            xtype: 'swbaselocalcombo'
                        },

                        new sw.Promed.SwReceptTypeCombo({
						allowBlank: false,
						listeners: {
							'change': function(combo, newValue, oldValue) {
								var base_form = this.findById('EvnReceptEditForm').getForm();
								if(newValue == 2 || (newValue == 3 && base_form.findField('EvnRecept_IsSigned').getValue() == 2))
									this.buttons[2].show();
								else
									this.buttons[2].hide();
								var record = combo.getStore().getById(newValue);

								var drug_combo = base_form.findField('Drug_id');
								var drug_mnn_combo = base_form.findField('DrugMnn_id');
								var org_farmacy_combo = base_form.findField('OrgFarmacy_id');
								var recept_num_field = base_form.findField('EvnRecept_Num');
								var recept_ser_field = base_form.findField('EvnRecept_Ser');
								var signa_field = base_form.findField('EvnRecept_Signa');
                                var recept_form_combo = base_form.findField('ReceptForm_id');
								recept_num_field.setRawValue('');
								recept_ser_field.setRawValue('');

								if ( !record ) {
									combo.setValue(1);
									drug_combo.getStore().baseParams.ReceptType_Code = 0;
									drug_mnn_combo.getStore().baseParams.ReceptType_Code = 0;
									org_farmacy_combo.getStore().baseParams.ReceptType_Code = 0;
									recept_num_field.enable();
									recept_ser_field.enable();
									/*signa_field.disable();
									signa_field.setAllowBlank(true);
									signa_field.setRawValue('');*/
									return false;
								}

								drug_combo.getStore().baseParams.ReceptType_Code = record.get('ReceptType_Code');
								drug_mnn_combo.getStore().baseParams.ReceptType_Code = record.get('ReceptType_Code');
								org_farmacy_combo.getStore().baseParams.ReceptType_Code = record.get('ReceptType_Code');

								if ( record.get('ReceptType_Code') == 1 ) {
									recept_num_field.enable();
									recept_ser_field.enable();
									/*signa_field.disable();
									signa_field.setAllowBlank(true);
									signa_field.setRawValue('');*/
								}
								else {
									recept_num_field.disable();
									recept_ser_field.disable();
                                    /*if (recept_form_combo.getValue()!=2){
                                        signa_field.enable();
                                        signa_field.setAllowBlank(false);
                                    }
                                    else{
                                        signa_field.disable();
                                        signa_field.setAllowBlank(true);
                                        signa_field.setRawValue('');
                                    }*/
									this.setReceptNumber();
									this.setReceptSerial();
								}
							}.createDelegate(this),
							'keydown': function (inp, e) {
								if ( e.shiftKey == true && e.getKey() == Ext.EventObject.TAB ) {
									e.stopEvent();
									this.buttons[this.buttons.length - 1].focus();
								}
							}.createDelegate(this)
						},
						listWidth: 400,
						tabIndex: TABINDEX_EREF + 1,
						validateOnBlur: true
					}), {
						border: false,
						layout: 'column',
						items: [{
							border: false,
							layout: 'form',
							items: [{
								allowBlank: false,
								allowDecimals: false,
								allowNegative: false,
								autoCreate: {
									tag: 'input',
									type: 'text',
									maxLength: '10'
								},
								fieldLabel: 'Серия',
								name: 'EvnRecept_Ser',
								tabIndex: TABINDEX_EREF + 3,
								validateOnBlur: true,
								xtype: 'textfield'
							}]
						}, {
							border: false,
							layout: 'form',
							items: [{
								allowBlank: false,
								allowDecimals: false,
								allowNegative: false,
								autoCreate: {
									maxLength: 13,
									tag: 'input',
									type: 'text'
								},
								fieldLabel: 'Номер',
								maskRe: /\d/,
								name: 'EvnRecept_Num',
								tabIndex: TABINDEX_EREF + 4,
								validateOnBlur: true,
								xtype: 'textfield'
							}]
						}]
					}, {
						allowBlank: true,
						comboSubject: 'ReceptUrgency',
						fieldLabel: 'Срочность',
						hiddenName: 'ReceptUrgency_id',
						xtype: 'swcommonsprcombo',
						width: 100
					}, {
						allowBlank: false,
						autoLoad: false,
						comboSubject: 'ReceptValid',
						fieldLabel: 'Срок действия',
						hiddenName: 'ReceptValid_id',
						lastQuery: '',
						tabIndex: TABINDEX_EREF + 5,
						validateOnBlur: true,
						value: 2,
						xtype: 'swcommonsprcombo'
					},
					new sw.Promed.SwLpuSectionGlobalCombo({
						//allowBlank: false,
                                                autoLoad: true,
						id: 'EREF_LpuSectionCombo',
						lastQuery: '',
						linkedElements: [
							'EREF_MedStaffFactCombo'
						],
						listWidth: 700,
						tabIndex: TABINDEX_EREF + 6,
						validateOnBlur: true,
						width: 517
					}),
					new sw.Promed.SwMedStaffFactGlobalCombo({
						//allowBlank: false,
						id: 'EREF_MedStaffFactCombo',
                        autoLoad: true,
						lastQuery: '',
						parentElementId: 'EREF_LpuSectionCombo',
						listWidth: 700,
						tabIndex: TABINDEX_EREF + 7,
						validateOnBlur: true,
						width: 517
					}), {
						checkAccessRights: true,
						allowBlank: false,
						fieldLabel: 'Диагноз',
						hiddenName: 'Diag_id',
						listWidth: 600,
						tabIndex: TABINDEX_EREF + 8,
						validateOnBlur: true,
						width: 517,
						withGroups: true,
						xtype: 'swdiagcombo'
					}, {
						fieldLabel: 'Выдан уполномоченному лицу',
						name: 'EvnRecept_IsDelivery',
						hiddenName: 'EvnRecept_IsDelivery',
						xtype: 'checkbox'
					}]
				}),
				new sw.Promed.Panel({
					autoHeight: true,
					bodyStyle: 'padding-top: 0.5em;',
					border: true,
					collapsible: true,
					id: 'EREF_PrivilegePanel',
					layout: 'form',
					style: 'margin-bottom: 0.5em;',
					title: '2. Льгота',
					items: [{
						border: false,
						layout: 'column',
						items: [{
							border: false,
							layout: 'form',
							items: [{
								allowBlank: false,
								autoLoad: false,
								comboSubject: 'ReceptFinance',
								fieldLabel: 'Тип финансирования',
								hiddenName: 'ReceptFinance_id',
								lastQuery: '',
								listeners: {
									'change': function(combo, newValue, oldValue) {
										var base_form = this.findById('EvnReceptEditForm').getForm();

										var evn_recept_is7noz_combo = base_form.findField('EvnRecept_Is7Noz');

										var record = combo.getStore().getById(newValue);

										//this.setReceptNumber();
                                        if(base_form.findField('ReceptType_id').getValue() != '1')
										    this.setReceptSerial();

										base_form.findField('Drug_id').getStore().baseParams.ReceptFinance_Code = 0;
										base_form.findField('DrugMnn_id').getStore().baseParams.ReceptFinance_Code = 0;
										base_form.findField('OrgFarmacy_id').getStore().baseParams.ReceptFinance_Code = 0;

										// Если в списке отсутствует запись для выбранного значения
										if ( !record ) {
											evn_recept_is7noz_combo.fireEvent('change', evn_recept_is7noz_combo, evn_recept_is7noz_combo.getValue());
											// прерываем выполнение метода
											return false;
										}

										var recept_finance_code = record.get('ReceptFinance_Code');

										base_form.findField('Drug_id').getStore().baseParams.ReceptFinance_Code = recept_finance_code;
										base_form.findField('DrugMnn_id').getStore().baseParams.ReceptFinance_Code = recept_finance_code;
										base_form.findField('OrgFarmacy_id').getStore().baseParams.ReceptFinance_Code = recept_finance_code;

										evn_recept_is7noz_combo.fireEvent('change', evn_recept_is7noz_combo, evn_recept_is7noz_combo.getValue());
									}.createDelegate(this)
								},
								listWidth: 200,
								tabIndex: TABINDEX_EREF + 9,
								validateOnBlur: true,
								width: 200,
								xtype: 'swcommonsprcombo'
							}]
						}, {
							border: false,
							labelWidth: 93,
							layout: 'form',
							items: [{
								disabled: true,
								fieldLabel: 'Скидка',
								hiddenName: 'ReceptDiscount_id',
								listeners: {
									'select': function(combo, record, index) {
										combo.setRawValue(record.get('ReceptDiscount_Code') + ". " + record.get('ReceptDiscount_Name'));
									}
								},
								listWidth: 100,
								tabIndex: TABINDEX_EREF + 10,
								validateOnBlur: true,
								width: 100,
								xtype: 'swreceptdiscountcombo'
							}]
						}]
					}, {
						allowBlank: false,
						fieldLabel: '7 Нозологий', //(getGlobalOptions().region && getGlobalOptions().region.nick == 'saratov' ? 'ВЗН' : '7 Нозологий'),
						hiddenName: 'EvnRecept_Is7Noz',
						id:'EREF_EvnRecept_Is7Noz',
						listeners: {
							'change': function(combo, newValue, oldValue) {
								var index;
								// Если значение не выбрано
								if ( !newValue ) {
									index = combo.getStore().findBy(function(rec) {
										if ( Number(rec.get('YesNo_Code')) == 0 ) {
											return true;
										}
										else {
											return false;
										}
									});

									if ( index >= 0 ) {
										newValue = combo.getStore().getAt(index).get('YesNo_id');
									}
									else {
										return false;
									}

									combo.setValue(newValue);
								}

								if ( newValue == oldValue ) {
									return false;
								}

								var base_form = this.findById('EvnReceptEditForm').getForm();

								if ( base_form.findField('ReceptType_id').getValue() != '1' ) {
								    this.setReceptNumber();
								}
                                                                
								var privilege_type_combo = base_form.findField('PrivilegeType_id');
								var recept_finance_combo = base_form.findField('ReceptFinance_id');

								var diag_id = base_form.findField('Diag_id').getValue(); // Значение поля "Диагноз"
								var privilege_type_id = privilege_type_combo.getValue(); // Запоминаем значение поля "Категория льготы"
								var recept_finance_code = 0;
								var recept_finance_id = recept_finance_combo.getValue(); // Значение поля "Тип финансирования"

								// Получаем запись, соответствующую выбранному значению
								var record = combo.getStore().getById(newValue);

								// Код признака 7 нозологий
								var code = (record ? record.get('YesNo_Code') : 0);

								base_form.findField('Drug_id').getStore().baseParams.EvnRecept_Is7Noz_Code = code;
								base_form.findField('DrugMnn_id').getStore().baseParams.EvnRecept_Is7Noz_Code = code;
								base_form.findField('OrgFarmacy_id').getStore().baseParams.EvnRecept_Is7Noz_Code = code;
                                                                
								if ( code == 1  && base_form.findField('ReceptFinance_id').getValue() != 1 ) {
									sw.swMsg.alert('Внимание', 'Для выбора 7 нозологий необходимо выбрать тип финансирования - Федеральный бюджет', function() { combo.setValue(oldValue); });
									return false;
								}
							
								if ( code == 1 ) {
									Ext.Ajax.request({
										callback: function(options, success, response) {
											if ( success ) {
												var response_obj = Ext.util.JSON.decode(response.responseText);
												var i;
												var diag_combo = base_form.findField('Diag_id');

												for ( i = 0; i<response_obj.length; i++ ) {
													//log(response_obj[i].Diag_id);
													if ( !Ext.isEmpty(response_obj[i]) && !Ext.isEmpty(response_obj[i].Diag_id) ) {
														wnd.list.push(response_obj[i].Diag_id);
														wnd.list.push(response_obj[i].Diag_pid);
													}
												}


												diag_combo.setBaseFilter(function(rec, id) {
													return rec.get('Diag_id') && rec.get('Diag_id').inlist(wnd.list)
												});

												if ( diag_id && ! diag_id.inlist(wnd.list) )
												{
													diag_combo.clearValue();
												}


												 if ( wnd.list.length > 0 && ! diag_id )
												 {
													diag_combo.getStore().load({
														callback: function()
														{
															diag_combo.getStore().each(function(record)
															{
																if ( record.get('Diag_id') == wnd.list[0] )
																{
																	diag_combo.fireEvent('select', diag_combo, record, 0);
																	diag_combo.fireEvent('blur', diag_combo);
																}
															});
														},
														params: { where: "where Diag_id = " + wnd.list[0] }
													});
												 }
											}
											else {
												sw.swMsg.alert('Ошибка', 'Ошибка при получении диагнозов по ВЗН', function() {} );
											}
										}.createDelegate(this),
										url: '/?c=PersonRegister&m=loadList',
										params: {
											Person_id: base_form.findField('Person_id').getValue(),
											PersonRegisterType_id: 49,
											PersonRegister_Date: Ext.util.Format.date(base_form.findField('EvnRecept_setDate').getValue(), 'Y-m-d')
										}
									});
								} else
								{

									//if (base_form.findField('Diag_id').getValue() != diag_id){
									// из-за этого условия возникала ошибка в #115065, не ставились диагнозы вне взн, потому что фильтр с комбика не убирался
									// не понял для чего нужно было это условие, ведь кажется значение поля diag_id не меняется за все это время
									// оставляю на случай, если это что-то сломает и условие все-таки было нужно

										base_form.findField('Diag_id').clearBaseFilter();
										//base_form.findField('Diag_id').clearValue();
										wnd.list = [];
									//}
								}

								// Закрываем для редактирования поле "Категория льготы"
								privilege_type_combo.clearValue();
								privilege_type_combo.disable();
								privilege_type_combo.getStore().clearFilter();

								index = recept_finance_combo.getStore().findBy(function(rec) {
									if ( rec.get('ReceptFinance_id') == recept_finance_id ) {
										return true;
									}
									else {
										return false;
									}
								});

								if ( index >= 0 ) {
									recept_finance_code = recept_finance_combo.getStore().getAt(index).get('ReceptFinance_Code');
								}

								if ( recept_finance_code.toString().inlist([ '1', '2' ]) ) {
									// Открываем для редактирования поле "Категория льготы"
									privilege_type_combo.enable();

									var records_count = 0;

									// Устанавливаем фильтр на поле "Категория льготы"
									privilege_type_combo.getStore().filterBy(function(rec) {
										// Признак 7 нозологий = "Да"
										if ( code == 1 ) {
											// не отказник или состоит на дисп учете по 7 нозологиям
											if ( parseInt(rec.get('PersonPrivilege_IsClosed')) == 1 && (parseInt(rec.get('PersonRefuse_IsRefuse')) != 2 || parseInt(rec.get('PersonPrivilege_IsPersonDisp')) == 2) ) {
												return true;
											}
											else {
												// иначе не попадает
												return false;
											}
										}
										// Признак 7 нозологий = "Нет"
										// Если льгота не закрыта и пациент не отказник
										else if ( parseInt(rec.get('PersonPrivilege_IsClosed')) == 1 && parseInt(rec.get('PersonRefuse_IsRefuse')) != 2 ) {
											// Если тип финансирования льготы совпадает с выбранным типом финансирования рецепта
											if ( rec.get('ReceptFinance_id') == recept_finance_id ) {
												// запись попадет в список
												privilege_type_id = rec.get('PrivilegeType_id');
												return true;
											}
											/*  Заккоментировано #PROMEDWEB-13860  */
											// Тип финансирования - "Субъект РФ"
											else if ( recept_finance_id == 2 ) {
												// запись попадет в список
												return true;
											}										
											// В остальных случаях
											else {
												// запись в список не попадет 
												return false;
											}
										}
										// В остальных случаях запись в список не попадет 
										else {
											return false;
										}
									});

									// Считаем количество записей в отфильтрованном списке категорий льготы
									privilege_type_combo.getStore().each(function(rec) {
										// if ( rec.get('PersonPrivilege_IsClosed') == 1 ) {
											records_count = records_count + 1;
										// }
									});

									// Ищем запись в списке категорий льготы, соответствующую ранее выбранному значению
									var privilege_type_record = privilege_type_combo.getStore().getById(privilege_type_id);

									// Если запись найдена
									if ( privilege_type_record ) {
										// Устанавливаем значение поля "Категория льготы"
										privilege_type_combo.setValue(privilege_type_id);
										privilege_type_combo.fireEvent('change', privilege_type_combo, privilege_type_id, null);
									}
									// Если всего одна запись
									else if ( records_count == 1 ) {
										// ищем незакрытую льготу
										privilege_type_combo.getStore().each(function(rec) {
											if ( rec.get('PersonPrivilege_IsClosed') == 1 ) {
												privilege_type_id = rec.get('PrivilegeType_id');
											}
										});

										// Устанавливаем значение поля "Категория льготы"
										privilege_type_combo.fireEvent('change', privilege_type_combo, privilege_type_id, null);
										privilege_type_combo.setValue(privilege_type_id);
									}
								}

								if ( Number(code) == 1 ) {
									// Устанавливаем размер скидки 100%
									index = base_form.findField('ReceptDiscount_id').getStore().findBy(function(rec) {
										if ( Number(rec.get('ReceptDiscount_Code')) == 1 ) {
											return true;
										}
										else {
											return false;
										}
									});

									if ( index >= 0 ) {
										base_form.findField('ReceptDiscount_id').setValue(base_form.findField('ReceptDiscount_id').getStore().getAt(index).get('ReceptDiscount_id'));
									}
								}
								else {
									privilege_type_combo.getStore().each(function(rec) {
										if ( rec.get('PrivilegeType_id') == privilege_type_combo.getValue() ) {
											base_form.findField('ReceptDiscount_id').setValue(rec.get('ReceptDiscount_id'));
										}
									});
								}

								base_form.findField('Drug_Price').setRawValue('');

								this.setVKProtocolFieldsVisible();

								// Чистим поле "МНН"
								base_form.findField('DrugMnn_id').clearValue();
								//base_form.findField('DrugMnn_id').disable(); Пока закомментировал
								//console.log(combo); console.log(this.action); console.log(base_form.findField('ReceptForm_id'));
								base_form.findField('DrugMnn_id').lastQuery = '';
								base_form.findField('DrugMnn_id').getStore().removeAll();

								// Чистим поле "Торговое наименование"
								base_form.findField('Drug_id').clearValue();
								//base_form.findField('Drug_id').disable(); Пока закомментировал
								base_form.findField('Drug_id').lastQuery = '';
								base_form.findField('Drug_id').getStore().removeAll();
								base_form.findField('Drug_id').getStore().baseParams.DrugMnn_id = 0;
								base_form.findField('Drug_id').getStore().baseParams.mode = 'start';

								if ( !combo.disabled && this.action == 'add' ) {
                                    if(base_form.findField('ReceptForm_id').getValue()!=2)
									    base_form.findField('DrugMnn_id').enable();
                                    else {
                                        //base_form.findField('DrugMnn_id').disable(); Пока закомментировал
                                        base_form.findField('DrugMnn_id').setAllowBlank(true);
                                    }
									base_form.findField('Drug_id').enable();
									base_form.findField('OrgFarmacy_id').enable();
								}
							}.createDelegate(this)
						},
						tabIndex: TABINDEX_EREF + 11,
						value: 1,
						width: 100,
						xtype: 'swyesnocombo'
					}, {
						allowBlank: false,
						codeField: 'PrivilegeType_Code',
						displayField: 'PrivilegeType_Name',
						editable: false,
						fieldLabel: 'Категория',
						hiddenName: 'PrivilegeType_id',
						lastQuery: '',
						listeners: {
							'change': function(combo, newValue, oldValue) {
								// Получаем запись, соответствующую выбранному значению
								var record = combo.getStore().getById(newValue);
								var base_form = this.findById('EvnReceptEditForm').getForm();

								var evn_recept_is7noz_combo = base_form.findField('EvnRecept_Is7Noz');
								var recept_discount_combo = base_form.findField('ReceptDiscount_id');

								// Если запись не найдена
								if ( !record ) {
									// Чистим значение поля "Скидка"
									recept_discount_combo.clearValue();
									// Прерываем выполнение метода
									return false;
								}

								base_form.findField('Drug_id').getStore().baseParams.PrivilegeType_id = newValue;
								base_form.findField('DrugMnn_id').getStore().baseParams.PrivilegeType_id = newValue;
								
								// Чистим поле "МНН"
								base_form.findField('DrugMnn_id').clearValue();
								base_form.findField('DrugMnn_id').lastQuery = '';
								base_form.findField('DrugMnn_id').getStore().removeAll();

								// Чистим поле "Торговое наименование"
								base_form.findField('Drug_id').clearValue();
								base_form.findField('Drug_id').lastQuery = '';
								base_form.findField('Drug_id').getStore().removeAll();
								base_form.findField('Drug_id').getStore().baseParams.DrugMnn_id = 0;
								base_form.findField('Drug_id').getStore().baseParams.mode = 'start';
								
								evn_recept_is7noz_record = evn_recept_is7noz_combo.getStore().getById(evn_recept_is7noz_combo.getValue());

								// Если значение поля "7 нозологий" пустое или равно "Нет"...
								if ( !evn_recept_is7noz_record || Number(evn_recept_is7noz_record.get('YesNo_Code')) == 0 ) {
									// ... устанавливаем значение поля "Тип финансирования", соответствующее выбранной льготе
									recept_discount_combo.setValue(record.get('ReceptDiscount_id'));
								}
							}.createDelegate(this)
						},
						store: new Ext.data.Store({
							autoLoad: false,
							reader: new Ext.data.JsonReader({
								id: 'PrivilegeType_id'
							}, [
								{ name: 'PrivilegeType_Code', mapping: 'PrivilegeType_Code', type: 'int' },
								{ name: 'PrivilegeType_id', mapping: 'PrivilegeType_id' },
								{ name: 'PrivilegeType_Name', mapping: 'PrivilegeType_Name' },
								{ name: 'ReceptDiscount_id', mapping: 'ReceptDiscount_id' },
								{ name: 'ReceptFinance_id', mapping: 'ReceptFinance_id' },
								{ name: 'PersonPrivilege_id', mapping: 'PersonPrivilege_id' },
								{ name: 'PersonPrivilege_IsClosed', mapping: 'PersonPrivilege_IsClosed' },
								{ name: 'PersonPrivilege_IsNoPfr', mapping: 'PersonPrivilege_IsNoPfr' },
								{ name: 'PersonPrivilege_IsPersonDisp', mapping: 'PersonPrivilege_IsPersonDisp' },
								{ name: 'PersonRefuse_IsRefuse', mapping: 'PersonRefuse_IsRefuse' }
							]),
							url: C_PRIVCAT_LOAD_LIST
						}),
						tabIndex: TABINDEX_EREF + 12,
						tpl: new Ext.XTemplate(
							'<tpl for="."><div class="x-combo-list-item">',
							'<table style="border: 0;"><tr><td style="width: 25px;"><font color="red">{PrivilegeType_Code}</font></td><td style="font-weight: {[ values.PersonPrivilege_IsClosed == 1 ? "bold" : "normal; color: red;" ]};">{PrivilegeType_Name}{[ values.PersonPrivilege_IsClosed == 1 ? "&nbsp;" : " (закрыта)" ]}</td></tr></table>',
							'</div></tpl>'
						),
						validateOnBlur: true,
						valueField: 'PrivilegeType_id',
						width: 517,
						xtype: 'swbaselocalcombo'
					}]
				}),
				new sw.Promed.Panel({
					autoHeight: true,
					bodyStyle: 'padding-top: 0.5em;',
					border: true,
					collapsible: true,
					id: 'EREF_DrugPanel',
					layout: 'form',
					style: 'margin-bottom: 0.5em;',
					title: '3. Медикамент',
					items: [{
						border: false,
						layout: 'column',
						items: [{
							border: false,
							layout: 'form',
							items: [ new sw.Promed.SwYesNoCombo({
								disabled: false,
								fieldLabel: 'Выписка по МНН',
								hiddenName: 'Drug_IsMnn',
								listWidth: 80,
								tabIndex: TABINDEX_EREF + 12,
								validateOnBlur: true,
								value: 2,
								width: 80,
								listeners: {
									'change': function(combo, newValue, oldValue){
										var base_form = this.findById('EvnReceptEditForm').getForm();
										if(newValue==1){
											//base_form.findField('DrugMnn_id').disable(); Пока закомментировал
											base_form.findField('DrugMnn_id').setAllowBlank(true);
										}
										else
										{
											base_form.findField('DrugMnn_id').enable();
											base_form.findField('DrugMnn_id').setAllowBlank(false);
										}
									}.createDelegate(this)
								}
							})]
						}, {
							border: false,
							layout: 'form',
							items: [ new sw.Promed.SwYesNoCombo({
								// disabled: true,
								fieldLabel: 'Протокол ВК',
								hiddenName: 'Drug_IsKEK',
								tabIndex: TABINDEX_EREF + 13,
								width: 80,
								listeners: {
									'change': function(combo, newValue, oldValue) {
										combo.setLinkedFieldValues();
									}
								},
								clearValue: function() {
									sw.Promed.SwYesNoCombo.superclass.clearValue.apply(this, arguments);
									this.setLinkedFieldValues();
								},
								setLinkedFieldValues: function() {
									wnd.setVKProtocolFieldsVisible();
								}
							})]
						}, {
							border: false,
							layout: 'form',
							items: [{
								fieldLabel: 'Номер протокола ВК',
								name: 'EvnRecept_VKProtocolNum',
								tabIndex: TABINDEX_EREF + 13,
								width: 80,
								xtype: 'textfield'
							}]
						}, {
							border: false,
							layout: 'form',
							items: [{
								fieldLabel: 'Дата протокола ВК',
								name: 'EvnRecept_VKProtocolDT',
								tabIndex: TABINDEX_EREF + 13,
								xtype: 'swdatefield',
								format: 'd.m.Y',
								plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
								validateOnBlur: true
							}]
						}, {
							border: false,
							layout: 'form',
							labelWidth: 200,
							items: [{
								fieldLabel: 'Основание для проведения ВК',
								name: 'CauseVK',
								hiddenName: 'CauseVK_id',
								tabIndex: TABINDEX_EREF + 13,
								width: 300,
								comboSubject: 'CauseVK',
								xtype: 'swcommonsprcombo'
							}]
						}]
					}, {
						border: false,
						layout: 'column',
						items: [{
							border: false,
							layout: 'form',
							items: [{
								fieldLabel: 'По специальному назначению',
								name: 'PrescrSpecCause_cb',
								xtype: 'checkbox',
								listeners: {
									'check': function(checkbox, value) {
										var base_form = wnd.findById('EvnReceptEditForm').getForm();
										if (this.checked && base_form.findField('ReceptForm_id').getValue() == 9) {
											base_form.findField('PrescrSpecCause_id').setValue(1);
											base_form.findField('PrescrSpecCause_id').showContainer();
											base_form.findField('PrescrSpecCause_id').allowBlank = false;
											base_form.findField('PrescrSpecCause_id').enable();
										}  else {
											base_form.findField('PrescrSpecCause_id').clearValue();
											base_form.findField('PrescrSpecCause_id').hideContainer();
											base_form.findField('PrescrSpecCause_id').allowBlank = true;
											base_form.findField('PrescrSpecCause_id').disable();
										}
									}
								},
								width: 20
							}]
						}, {
							border: false,
							layout: 'form',
							labelWidth: 230,
							items: [{
								fieldLabel: 'Причина специального назначения',
								comboSubject: 'PrescrSpecCause',
								xtype: 'swcommonsprcombo',
								editable: false,
								width: 260,
								listWidth: 400,
								hiddenName: 'PrescrSpecCause_id',
								listeners: {
									'expand': function() {
										var base_form = wnd.findById('EvnReceptEditForm').getForm();
										base_form.findField('PrescrSpecCause_id').getStore().filterBy(function (rec) {
											return (rec.get('PrescrSpecCause_Code') == 1);
										});
									}
								}
							}]
						}]
					}, {
						allowBlank: false,
						displayField: 'DrugMnn_Name',
						emptyText: 'Начните вводить МНН...',
						enableKeyEvents: true,
						fieldLabel: 'МНН',
						forceSelection: true,
						hiddenName: 'DrugMnn_id',
						lastQuery: '',
						listWidth: 800,
						minChars: 2,
						minLength: 1,
						minLengthText: 'Поле должно быть заполнено',
						listeners: {
							'beforeselect': function() {
								this.findById('EvnReceptEditForm').getForm().findField('Drug_id').lastQuery = '';
								return true;
							}.createDelegate(this),
							'change': function(combo, newValue, oldValue) {
								// Выбрано значение поля "МНН"

								var base_form = this.findById('EvnReceptEditForm').getForm();

								var drug_combo = base_form.findField('Drug_id');
								var org_farmacy_combo = base_form.findField('OrgFarmacy_id');

								drug_combo.clearValue();
								drug_combo.getStore().removeAll();
								drug_combo.lastQuery = '';

								var isSaratov = (getGlobalOptions().region && getGlobalOptions().region.nick == 'saratov');

								if ( !isSaratov && ((isSuperAdmin() && getGlobalOptions().recept_drug_ostat_control == 3) || getGlobalOptions().recept_drug_ostat_control == 4) ) {
									org_farmacy_combo.clearValue();
									org_farmacy_combo.getStore().removeAll();
								}

								base_form.findField('Drug_Price').setRawValue('');

								this.setVKProtocolFieldsVisible();

								// Устанавливаем значения базовых параметров поля "Торговое наименование"
								// drug_combo.getStore().baseParams.Drug_DoseCount = null;
								// drug_combo.getStore().baseParams.Drug_Dose = null;
								// drug_combo.getStore().baseParams.Drug_Fas = null;
								// drug_combo.getStore().baseParams.DrugFormGroup_id = null;
								drug_combo.getStore().baseParams.DrugMnn_id = newValue;
								drug_combo.getStore().baseParams.query = '';
                                                                 drug_combo.getStore().baseParams.Lpu_id = Ext.globalOptions.globals.lpu_id;
                                drug_combo.getStore().baseParams.is_mi_1 = (base_form.findField('ReceptForm_id').getValue() == 2) ? true : false;
								// Если поле не пустое
								if ( newValue > 0 ) {
									// загружаем список медикаментов
									drug_combo.getStore().load();
								}

								return true;
							}.createDelegate(this),
							'keydown': function(inp, e) {
								if ( e.getKey() == Ext.EventObject.DELETE || e.getKey() == Ext.EventObject.F4 ) {
									e.stopEvent();

									var base_form = this.findById('EvnReceptEditForm').getForm();

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

									if ( Ext.isIE ) {
										e.browserEvent.keyCode = 0;
										e.browserEvent.which = 0;
									}

									switch ( e.getKey() ) {
										case Ext.EventObject.DELETE:
											inp.clearValue();
											base_form.findField('Drug_id').getStore().baseParams.DrugMnn_id = 0;
										break;

										case Ext.EventObject.F4:
											inp.onTrigger2Click();
										break;
									}
								}

								return true;
							}.createDelegate(this)
						},
						mode: 'remote',
						onTrigger2Click: function() {
							var base_form = this.findById('EvnReceptEditForm').getForm();

							if ( base_form.findField('DrugMnn_id').disabled ) {
								return false;
							}

							var drug_combo = base_form.findField('Drug_id');
							var drug_mnn_combo = base_form.findField('DrugMnn_id');
							var org_farmacy_combo = base_form.findField('OrgFarmacy_id');
							var recept_finance_combo = base_form.findField('ReceptFinance_id');
							var recept_type_combo = base_form.findField('ReceptType_id');

							var recept_finance_code = 0;
							var recept_type_code = 0;

							var record = recept_finance_combo.getStore().getById(recept_finance_combo.getValue());

							if ( record ) {
								recept_finance_code = record.get('ReceptFinance_Code');
							}

							if ( recept_finance_code == 0 ) {
								sw.swMsg.alert('Ошибка', 'Не выбран тип финансирования льготы', function() { base_form.findField('ReceptFinance_id').focus(true); });
								return false;
							}

							record = recept_type_combo.getStore().getById(recept_type_combo.getValue());

							if ( record ) {
								recept_type_code = record.get('ReceptType_Code');
							}

							if ( recept_type_code == 0 ) {
								sw.swMsg.alert('Ошибка', 'Не выбран тип рецепта', function() { base_form.findField('ReceptType_id').focus(true); });
								return false;
							}

							getWnd('swDrugMnnSearchWindow').show({
								EvnRecept_setDate: Ext.util.Format.date(base_form.findField('EvnRecept_setDate').getValue(), 'd.m.Y'),
								onClose: function() {
									drug_mnn_combo.focus(false);
								},
								onSelect: function(drugMnnData) {
									drug_mnn_combo.getStore().removeAll();
									drug_mnn_combo.getStore().loadData([ drugMnnData ]);
									drug_mnn_combo.setValue(drugMnnData.DrugMnn_id);

									var record = drug_mnn_combo.getStore().getAt(0);

									if ( record ) {
										drug_mnn_combo.fireEvent('change', drug_mnn_combo, record.get('DrugMnn_id'), 0);
									}

									getWnd('swDrugMnnSearchWindow').hide();

									drug_mnn_combo.focus(false);
								}.createDelegate(this),
								ReceptFinance_Code: recept_finance_code,
								ReceptType_Code: recept_type_code
							});
						}.createDelegate(this),
						queryDelay: 1000,
						resizable: true,
						selectOnFocus: true,
						store: new Ext.data.Store({
							autoLoad: false,
							reader: new Ext.data.JsonReader({
								id: 'DrugMnn_id'
							}, [
								{ name: 'DrugMnn_id', type: 'int' },
								{ name: 'DrugMnn_Code', type: 'int' },
								{ name: 'DrugMnn_Name', type: 'string' },
								{ name: 'vzn', type: 'int' }
							]),
							url: C_DRUG_MNN_LIST
						}),
						tabIndex: TABINDEX_EREF + 14,
						tpl: new Ext.XTemplate(
							'<tpl for="."><div class="x-combo-list-item">',
							'<h3>{DrugMnn_Name}&nbsp;</h3>',
							'</div></tpl>'
						),
						validateOnBlur: true,
						valueField: 'DrugMnn_id',
						width: 517,
						xtype: 'swDrugMnnVznCombo'
					}, {
						allowBlank: false,
						hiddenName: 'Drug_id',
						listeners: {
							'beforeselect': function(combo, record, index) {
								var base_form = this.findById('EvnReceptEditForm').getForm();

								combo.setValue(record.get('Drug_id'));
								base_form.findField('Drug_Price').setValue(record.get('Drug_Price'));
								
								if(record.get('Drug_IsKEK') == 2) { //если у выбранного препарата проставлен признак выписки через ВК
									base_form.findField('Drug_IsKEK').setValue(2); //2 - Да
								}
								base_form.findField('Drug_IsKEK').setLinkedFieldValues();

								var drug_mnn_combo = base_form.findField('DrugMnn_id');
								var drug_mnn_record = drug_mnn_combo.getStore().getById(record.get('DrugMnn_id'));
								var org_farmacy_combo = base_form.findField('OrgFarmacy_id');

								var isSaratov = (getGlobalOptions().region && getGlobalOptions().region.nick == 'saratov');

								drug_mnn_combo.lastQuery = '';

								/*if ( drug_mnn_record ) {
									drug_mnn_combo.setValue(record.get('DrugMnn_id'));
								}
								else {*/
								drug_mnn_combo.getStore().load({
									callback: function() {
										drug_mnn_combo.setValue(record.get('DrugMnn_id'));
									},
									params: {
										DrugMnn_id: record.get('DrugMnn_id')
									}
								})
								//}

							 //alert('1');
							 var org_farmacy_combo = base_form.findField('OrgFarmacy_id');

							 // Если медикамент выбран
							 if ( record.get('Drug_id') > 0 ) {
							 // Очищаем и загружаем список аптек
								 org_farmacy_combo.clearValue();
								 org_farmacy_combo.getStore().removeAll();
								 org_farmacy_combo.getStore().load({
									 callback: function() {
										 if ( org_farmacy_combo.getStore().getCount() >= 1 ) {
										
											
											//  Поиск первой записи с ненулевым количеством
											$data = org_farmacy_combo.store.data.items
											
										   $Sort = $data[0].data.OrgFarmacy_Sort;
										   
											for(k in $data){
												
												if(typeof $data[k] == 'object' ) {
													if (parseFloat($data[k].data.DrugOstat_Kolvo) > 0) {
														//$OrgFarmacy_id = $data[k].data.OrgFarmacy_id;
                                                                                                                $Sort = $data[k].data.OrgFarmacy_Sort;
                                                                                                               //console.log('$Sort = ' + $Sort);
                                                                                                                
														break;
													}
												}
												 
											};
                                                                                        org_farmacy_combo.setFieldValue('OrgFarmacy_Sort', $Sort);
									 }
									 }.createDelegate(this),
									 params: {
										Drug_id: record.get('Drug_id'),
										PrivilegeType_id: base_form.findField('PrivilegeType_id').getValue()
									 }
								 });
							 }
							/*alert('1');
								if ( record.get('Drug_id') > 0 && (isSaratov || ((isSuperAdmin() && getGlobalOptions().recept_drug_ostat_control == 3) || getGlobalOptions().recept_drug_ostat_control == 4)) ) {
									alert('2');
									org_farmacy_combo.clearValue();
									org_farmacy_combo.getStore().removeAll();
									org_farmacy_combo.getStore().load({
										callback: function() {
											if ( org_farmacy_combo.getStore().getCount() == 1 ) {
												org_farmacy_combo.fireEvent('change', org_farmacy_combo, org_farmacy_combo.getStore().getAt(0).get('OrgFarmacy_id'), 0);
											}
										}.createDelegate(this),
										params: {
											Drug_id: record.get('Drug_id')
										}
									});
								}*/

								if ( record.get('Drug_IsKEK_Code') == 1 ) {
									sw.swMsg.alert('Сообщение', 'Внимание! Данный медикамент выписывается через ВК', function() { combo.focus(true); });
								}

								return true;
							}.createDelegate(this),
							'change': function(combo, newValue, oldValue) {
								var base_form = wnd.findById('EvnReceptEditForm').getForm();
								if (!Ext.isEmpty(newValue)) {
									Ext.Ajax.request({
										params: {
											Drug_id: newValue
										},
										success: function (response, options) {
											var result = Ext.util.JSON.decode(response.responseText);
											if (result['isNarcoOrStrongDrug']) {
												var ReceptForm_id = base_form.findField('ReceptForm_id').getValue();
												if (ReceptForm_id == 9) {
													base_form.findField('EvnRecept_IsExcessDose').showContainer();
												}
											}
										},
										url: '/?c=EvnRecept&m=isNarcoOrStrongDrug'
									});
								} else {
									base_form.findField('EvnRecept_IsExcessDose').hideContainer();
								}
							},
							'blur': function(inp, e) {
								// Если значение поля пустое
								if ( inp.getValue() == '' ) {
									// чистим список аптек
									this.findById('EvnReceptEditForm').getForm().findField('OrgFarmacy_id').clearValue();
									this.findById('EvnReceptEditForm').getForm().findField('OrgFarmacy_id').getStore().removeAll();
								}

								return true;
							}.createDelegate(this),
							'keydown': function(inp, e) {
								if ( e.getKey() == Ext.EventObject.DELETE || e.getKey() == Ext.EventObject.F4 ) {
									e.stopEvent();

									var base_form = this.findById('EvnReceptEditForm').getForm();

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

									switch ( e.getKey() ) {
										case Ext.EventObject.DELETE:
											inp.clearValue();
											base_form.findField('Drug_Price').setRawValue(null);
											base_form.findField('OrgFarmacy_id').clearValue();
											base_form.findField('OrgFarmacy_id').getStore().removeAll();
										break;

										case Ext.EventObject.F4:
											inp.onTrigger2Click();
										break;
									}
								}

								return true;
							}.createDelegate(this)
						},
						listWidth: 800,
						loadingText: 'Идет поиск...',
						minLengthText: 'Поле должно быть заполнено',
						onTrigger2Click: function() {
							var base_form = this.findById('EvnReceptEditForm').getForm();

							if ( base_form.findField('Drug_id').disabled ) {
								return false;
							}

							var drug_combo = base_form.findField('Drug_id');
							var org_farmacy_combo = base_form.findField('OrgFarmacy_id');
							var recept_finance_combo = base_form.findField('ReceptFinance_id');
							var recept_type_combo = base_form.findField('ReceptType_id');
							var privilege_type_combo = base_form.findField('PrivilegeType_id');

							var evn_recept_is7noz_code = 0;
							var recept_finance_code = 0;
							var recept_type_code = 0;

							var record = recept_finance_combo.getStore().getById(recept_finance_combo.getValue());

							if ( record ) {
								recept_finance_code = record.get('ReceptFinance_Code');
							}

							if ( recept_finance_code == 0 ) {
								sw.swMsg.alert('Ошибка', 'Не выбран тип финансирования льготы', function() { base_form.findField('ReceptFinance_id').focus(true); });
								return false;
							}

							record = recept_type_combo.getStore().getById(recept_type_combo.getValue());

							if ( record ) {
								recept_type_code = record.get('ReceptType_Code');
							}

							if ( recept_type_code == 0 ) {
								sw.swMsg.alert('Ошибка', 'Не выбран тип рецепта', function() { base_form.findField('ReceptType_id').focus(true); });
								return false;
							}
							
							privilege_type_id = privilege_type_combo.getValue();

							record = base_form.findField('EvnRecept_Is7Noz').getStore().getById(base_form.findField('EvnRecept_Is7Noz').getValue());

							if ( record ) {
								evn_recept_is7noz_code = record.get('YesNo_Code');
							}

							getWnd('swDrugTorgSearchWindow').show({
								EvnRecept_Is7Noz_Code: evn_recept_is7noz_code,
								EvnRecept_setDate: Ext.util.Format.date(base_form.findField('EvnRecept_setDate').getValue(), 'd.m.Y'),
                                is_mi_1: (base_form.findField('ReceptForm_id').getValue()==2)?true:false,
								onHide: function() {
									drug_combo.focus(false);
								},
								onSelect: function(drugTorgData) {
									drug_combo.getStore().removeAll();

									drug_combo.getStore().loadData([ drugTorgData ]);

									drug_combo.getStore().baseParams.DrugMnn_id = 0;
									record = drug_combo.getStore().getById(drugTorgData.Drug_id);

									if ( record ) {
										drug_combo.fireEvent('beforeselect', drug_combo, record);
									}

									getWnd('swDrugTorgSearchWindow').hide();
								},
								ReceptFinance_Code: recept_finance_code,
								ReceptType_Code: recept_type_code,
								PrivilegeType_id: privilege_type_id
							});
						}.createDelegate(this),
/*
						initComponent: function() {
							Ext.form.TwinTriggerField.prototype.initComponent.apply(this, arguments);

							this.store = new Ext.data.Store({
								autoLoad: false,
								reader: new Ext.data.JsonReader({
									id: 'Drug_id'
								}, [
									{ name: 'Drug_id', type: 'int' },
									{ name: 'DrugMnn_id', type: 'int' },
									{ name: 'DrugFormGroup_id', type: 'int' },
									{ name: 'Drug_IsKEK', type: 'int' },
									{ name: 'Drug_Name', type: 'string' },
									{ name: 'Drug_DoseCount', type: 'float' },
									{ name: 'Drug_Dose', type: 'string' },
									{ name: 'Drug_Fas', type: 'float' },
									{ name: 'Drug_Price', type: 'float' },
									{ name: 'Drug_IsKEK_Code', type: 'int' },
									{ name: 'DrugOstat_Flag', type: 'int' }
								]),
								url: '/?c=EvnRecept&m=loadDrugList'
							});
						},
*/
						tabIndex: TABINDEX_EREF + 15,
						tpl: new Ext.XTemplate(
							'<tpl for="."><div class="x-combo-list-item">',
							'<table style="width: 100%;"><tr style=\'font-weight: bold; color: #{[values.DrugOstat_Flag == 2 ? "f00" : (values.DrugOstat_Flag == 1 ? "00f" : "000" )]};\'>',
							'<td style="width: 70%;">{Drug_Name}&nbsp;</td>',
							'<td style="width: 30%; text-align: right;">{[values.DrugOstat_Flag == 2 ? "остатков нет" : (values.DrugOstat_Flag == 1 ? "остатки на РАС" : "&nbsp;" )]}</td>',
							'</tr></table>',
							'</div></tpl>'
						),
						// triggerAction: 'all',
						validateOnBlur: true,
						width: 517,
						xtype: 'swdrugcombo'
					}, {
						fieldLabel: 'Превышение дозировки',
						name: 'EvnRecept_IsExcessDose',
						xtype: 'checkbox'
					}, {	//https://redmine.swan.perm.ru/issues/62108 - другой компонент для аптеки
						allowBlank: false,
						displayField: 'OrgFarmacy_Name',
						fieldLabel: 'Аптека',
						hiddenName: 'OrgFarmacy_id',
                                                id: 'sEvnRecept_OrgFarmacy',
						lastQuery: '',
						trigger2Class: 'hideTrigger',
						listeners: {
							'change': function(combo, newValue, oldValue) {
								var base_form = this.findById('EvnReceptEditForm').getForm();
								var record = combo.getStore().getById(newValue);
                                                                
                                                                base_form.findField('EvnRecept_Kolvo').maxValue = undefined;

								var evn_recept_is_extemp = base_form.findField('EvnRecept_IsExtemp').getValue().toString();

								if ( record && evn_recept_is_extemp == '1' ) {
									if ( record.get('DrugOstat_Kolvo') == 0 ) {
										 //comboStorage_tid.getFieldValue('Lpu_Nick');
										comboMNN = base_form.findField('DrugMnn_id');
										//console.log('record = ' );
                                                                                //console.log(record );
										if (comboMNN.getFieldValue('vzn') == 1 && base_form.findField('Drug_id').getStore().baseParams.EvnRecept_Is7Noz_Code == 0 && 1 == 2) {
											sw.swMsg.alert('Предупреждение', 'Данный медикамент предназначен для выписке по ВЗН. ');
											
											 // Чистим поле "МНН"
											base_form.findField('DrugMnn_id').clearValue();
											base_form.findField('DrugMnn_id').getStore().removeAll();

											// Чистим поле "Торговое наименование"
											base_form.findField('Drug_id').clearValue();
											base_form.findField('Drug_id').getStore().removeAll();
											return false;
										}
										else {
											sw.swMsg.alert('Предупреждение', 'На данный момент выбранный медикамент отсутствует в аптеке. Рецепт попадет на отсроченное обслуживание.');
										}
									}
									else {
										recept_type_record = base_form.findField('ReceptType_id').getStore().getById(base_form.findField('ReceptType_id').getValue());

										//закоментил по задаче https://redmine.swan.perm.ru/issues/62108:
										/*if ( !recept_type_record || recept_type_record.get('ReceptType_Code') == 2 ) {
											base_form.findField('EvnRecept_Kolvo').maxValue = record.get('DrugOstat_Kolvo');
										}*/
									}
								}

								return true;
							}.createDelegate(this),
							'keydown': function(inp, e) {
								if ( e.getKey() == Ext.EventObject.DELETE || e.getKey() == Ext.EventObject.F4 ) {
									e.stopEvent();

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

									if ( Ext.isIE ) {
										e.browserEvent.keyCode = 0;
										e.browserEvent.which = 0;
									}

									switch ( e.getKey() ) {
										case Ext.EventObject.DELETE:
											inp.clearValue();
											break;

									}
								}

								return true;
							}
						},
						listWidth: 800,
						store: new Ext.data.Store({
							autoLoad: false,
                                                        listeners: {
								'load': function(store, records, options) {
									if ( store.getCount() == 1 ) {
										this.findById('EvnReceptEditForm').getForm().findField('OrgFarmacy_id').setValue(store.getAt(0).get('OrgFarmacy_id'));
									}
								}.createDelegate(this)
							},
							reader: new Ext.data.JsonReader({
								id: 'OrgFarmacy_id'
							}, [
								{ name: 'OrgFarmacy_id', mapping: 'OrgFarmacy_id' },
								{ name: 'OrgFarmacy_Name', mapping: 'OrgFarmacy_Name' },
								{ name: 'OrgFarmacy_HowGo', mapping: 'OrgFarmacy_HowGo' },
								{ name: 'OrgFarmacy_IsFarmacy', mapping: 'OrgFarmacy_IsFarmacy' },
								{ name: 'DrugOstat_Kolvo', mapping: 'DrugOstat_Kolvo' },
								{ name: 'OrgFarmacy_Sort', mapping: 'Sort', type: 'int' }
							]),
							url: '/?c=EvnRecept&m=loadOrgFarmacyList'
						}),
						tabIndex: TABINDEX_EREF + 16,
						valueField: 'OrgFarmacy_id',
						width: 517,
						xtype: 'sworgfarmacyostatcombo'
					},

					/*{
						displayField: 'OrgFarmacy_Name',
						enableKeyEvents: true,
						fieldLabel: 'Аптека',
						forceSelection: true,
						hiddenName: 'OrgFarmacy_id',
						lastQuery: '',
						listeners: {
							'change': function(combo, newValue, oldValue) {
								var base_form = this.findById('EvnReceptEditForm').getForm();
								var record = combo.getStore().getById(newValue);

								base_form.findField('EvnRecept_Kolvo').maxValue = undefined;

								if ( record ) {
									if ( record.get('DrugOstat_Kolvo') == 0 ) {
										sw.swMsg.alert('Предупреждение', 'На данный момент выбранный медикамент отсутствует в аптеке. Рецепт попадет на отсроченное обслуживание.', function() { this.focus(true); }.createDelegate(combo) );
									}
									else {
										var recept_type_record = base_form.findField('ReceptType_id').getStore().getById(base_form.findField('ReceptType_id').getValue());

										if ( !recept_type_record || recept_type_record.get('ReceptType_Code') == 2 ) {
											base_form.findField('EvnRecept_Kolvo').maxValue = record.get('DrugOstat_Kolvo');
										}
									}
								}

								return true;
							}.createDelegate(this),
							'keydown': function(inp, e) {
								if ( e.getKey() == Ext.EventObject.DELETE ) {
									e.stopEvent();

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

									if ( Ext.isIE ) {
										e.browserEvent.keyCode = 0;
										e.browserEvent.which = 0;
									}

									switch (e.getKey()) {
										case Ext.EventObject.DELETE:
											inp.clearValue();
										break;
									}
								}

								return true;
							}
						},
						listWidth: 800,
						loadingText: 'Идет поиск...',
						minChars: 1,
						minLength: 1,
						minLengthText: 'Поле должно быть заполнено',
						mode: 'local',
						resizable: true,
						selectOnFocus: true,
						store: new Ext.data.Store({
							autoLoad: false,
							listeners: {
								'load': function(store, records, options) {
									if ( store.getCount() == 1 ) {
										this.findById('EvnReceptEditForm').getForm().findField('OrgFarmacy_id').setValue(store.getAt(0).get('OrgFarmacy_id'));
									}
								}.createDelegate(this)
							},
							reader: new Ext.data.JsonReader({
								id: 'OrgFarmacy_id'
							}, [
								{ name: 'OrgFarmacy_id', mapping: 'OrgFarmacy_id' },
								{ name: 'OrgFarmacy_Name', mapping: 'OrgFarmacy_Name' },
								{ name: 'OrgFarmacy_HowGo', mapping: 'OrgFarmacy_HowGo' },
								{ name: 'OrgFarmacy_IsFarmacy', mapping: 'OrgFarmacy_IsFarmacy' },
								{ name: 'DrugOstat_Kolvo', mapping: 'DrugOstat_Kolvo' }
							]),
							url: '/?c=Drug&m=loadFarmacyOstatList'
						}),
						tabIndex: TABINDEX_EREF + 16,
						tpl: new Ext.XTemplate(
							'<tpl for="."><div class="x-combo-list-item">',
							'<table style="border: 0; width: 100%;"><tr>',
							'<td style="width: 55%;">{OrgFarmacy_Name}</td>',
							'<td style="width: 40%;">{OrgFarmacy_HowGo}</td>',
							'<td style="width: 5%; text-align: right;">{DrugOstat_Kolvo}</td>',
							'</tr></table>',
							'</div></tpl>'
						),
						triggerAction: 'all',
						validateOnBlur: true,
						valueField: 'OrgFarmacy_id',
						width: 517,
						xtype: 'combo'
					},*/ {
						disabled: true,
						fieldLabel: 'Цена',
						name: 'Drug_Price',
						tabIndex: TABINDEX_EREF + 17,
						xtype: 'textfield'
					}, {
						allowBlank: false,
						allowNegative: false,
						fieldLabel: 'Количество (D. t. d.)',
						name: 'EvnRecept_Kolvo',
						tabIndex: TABINDEX_EREF + 18,
						validateOnBlur: true,
						value: 1,
						xtype: 'numberfield'
					}, {
						allowBlank: false,
						fieldLabel: 'Signa',
						name: 'EvnRecept_Signa',
						tabIndex: TABINDEX_EREF + 19,
						validateOnBlur: true,
						width: 517,
						xtype: 'textfield'
					}]
				}),
					/*
                            new sw.Promed.Panel({
				autoHeight: true,
				bodyStyle: 'padding-top: 0.5em;',
				border: true,
				collapsible: true,
				id: 'EREF_DrugWrong',
				layout: 'form',
				style: 'margin-bottom: 0.5em;',
				title: '4. Информация об отказе',
                                //hidden: true,
				items: [
                                            {
                                        name: 'ReceptWrongDelayType_id',
                                        tabIndex: -1,
                                        xtype: 'hidden'
                                    },
                                        {
					allowBlank: false,
					fieldLabel: 'Дата отказа',
					format: 'd.m.Y',
					
					name: 'ReceptWrong_DT',
                                        disabled: true,
					plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
					tabIndex: TABINDEX_ERREF + 2,
					validateOnBlur: true,
					xtype: 'swdatefield'
				},
                                        {
					fieldLabel: 'Причина отказа',
                                        name: 'ReceptWrong_Decr',
                                        disabled: true,
					tabIndex: TABINDEX_ERREF + 20,
					validateOnBlur: true,
					width: 517,
					xtype: 'textfield'
                                        
                                        }]
                            }*/
					new sw.Promed.Panel({
						autoHeight: true,
						bodyStyle: 'padding-top: 0.5em;',
						border: true,
						collapsible: true,
						id: 'EREF_DrugResult',
						layout: 'form',
						title: '4. Результат',
						items:[
							{
								fieldLabel: '',
								hidden: true,
								hideLabel:true,
								name:'Recept_Result_Code',
								xtype:'textfield'
							},
							{
								fieldLabel: 'Результат',
								name: 'Recept_Result',
								disabled: false,
								tabIndex: TABINDEX_ERREF + 20,
								width: 517,
								xtype: 'textfield'
							},
							{
								fieldLabel: '',
								hideLabel: true,
								name: 'Recept_Delay_Info',
								disabled: false,
								tabIndex: TABINDEX_ERREF + 20,
								width: 517,
								height: 60,
								style: 'margin-left: 135px',
								//xtype: 'textfield'
								xtype: 'textarea'
							},
							{
								fieldLabel: 'Дата обращения',
								format: 'd.m.Y',
								name: 'ReceptOtov_Date',
								disabled: false,
								plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
								tabIndex: TABINDEX_ERREF + 2,
								validateOnBlur: true,
								xtype: 'swdatefield'
							},
							{
								fieldLabel: 'Аптека обращения',
								name: 'ReceptOtov_Farmacy',
								disabled: false,
								tabIndex: TABINDEX_ERREF + 20,
								validateOnBlur: true,
								width: 517,
								xtype: 'textfield'
							},
							{
								fieldLabel: 'Выданы медикаменты',
								height: 70,
								name: 'EvnRecept_Drugs',
								tabIndex: TABINDEX_EREF + 21,
								width: 517,
								xtype: 'textarea'
							},
							{
								name: 'EmptyCmp',
								disabled: false,
								xtype: 'label',
								text: '_'
							}
						]
					})
				],
				labelAlign: 'right',
				labelWidth: 130,
				reader: new Ext.data.JsonReader({
					success: Ext.emptyFn
				}, [
					{ name: 'Diag_id' },
					{ name: 'Drug_id' },
					{ name: 'Drug_IsKEK' },
					{ name: 'EvnRecept_VKProtocolNum' },
					{ name: 'EvnRecept_VKProtocolDT' },
					{ name: 'CauseVK_id' },
					{ name: 'Drug_IsMnn' },
					{ name: 'DrugMnn_id' },
					{ name: 'Drug_Price' },
					{ name: 'EvnRecept_Is7Noz' },
					{ name: 'EvnRecept_Kolvo' },
					{ name: 'EvnRecept_Num' },
					{ name: 'EvnRecept_pid' },
					{ name: 'EvnRecept_Ser' },
					{ name: 'EvnRecept_setDate' },
					{ name: 'EvnRecept_Signa' },
					{ name: 'EvnRecept_IsDelivery' },
					{ name: 'LpuSection_id' },
					{ name: 'MedPersonal_id' },
					{ name: 'OrgFarmacy_id' },
					{ name: 'PrivilegeType_id' },
					{ name: 'ReceptDiscount_id' },
					{ name: 'ReceptFinance_id' },
					{ name: 'ReceptType_id' },
					{ name: 'Recept_Result'},
					{ name: 'Recept_Result_Code'},
					{ name: 'Recept_Delay_Info'},
					{ name: 'EvnRecept_Drugs'},
					{ name: 'ReceptOtov_Farmacy' },
					{ name: 'ReceptOtov_Date' },
                                        { name: 'ReceptForm_id' },
					{ name: 'ReceptValid_id' },
					{ name: 'PrescrSpecCause_id' },
					{ name: 'ReceptUrgency_id' },
					{ name: 'EvnRecept_IsExcessDose' }
                                        //  Добавлены новые поля
                                        //{ name: 'ReceptWrongDelayType_id' },
                                        //{ name: 'ReceptWrong_DT' },
                                        //{ name: 'ReceptWrong_Decr' }
                                        
                                       
				]),
				region: 'center',
				trackResetOnLoad: true,
				url: C_EVNREC_SAVE
			})]
		});
		sw.Promed.swEvnReceptEditWindow.superclass.initComponent.apply(this, arguments);
	},

	keys: [{
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

			if ( Ext.isIE ) {
				e.browserEvent.keyCode = 0;
				e.browserEvent.which = 0;
			}

			switch ( e.getKey() ) {
				case Ext.EventObject.F6:
					Ext.getCmp('EREF_PersonInformationFrame').panelButtonClick(1);
				break;

				case Ext.EventObject.F10:
					Ext.getCmp('EREF_PersonInformationFrame').panelButtonClick(2);
				break;

				case Ext.EventObject.F11:
					Ext.getCmp('EREF_PersonInformationFrame').panelButtonClick(3);
				break;

				case Ext.EventObject.F12:
					if (e.ctrlKey == true) {
						Ext.getCmp('EREF_PersonInformationFrame').panelButtonClick(5);
					}
					else {
						Ext.getCmp('EREF_PersonInformationFrame').panelButtonClick(4);
					}
				break;
			}
		},
		key: [
			Ext.EventObject.F6,
			Ext.EventObject.F10,
			Ext.EventObject.F11,
			Ext.EventObject.F12
		],
		scope: this,
		stopEvent: false
	}, {
		alt: true,
		fn: function(inp, e) {
			var current_window = Ext.getCmp('EvnReceptEditWindow');

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

			if ( Ext.isIE ) {
				e.browserEvent.keyCode = 0;
				e.browserEvent.which = 0;
			}

			switch (e.getKey()) {
				case Ext.EventObject.G:
					current_window.printRecept();
				break;

				case Ext.EventObject.J:
					current_window.hide();
				break;

				case Ext.EventObject.R:
				case Ext.EventObject.Z:
					current_window.doCopy();
				break;

				case Ext.EventObject.C:
					current_window.doSave({
						checkPersonAge: true,
						checkPersonDeadDT: true,
						checkPersonSnils: true,
						copy: false,
						print: false
					});
				break;

				case Ext.EventObject.NUM_ONE:
				case Ext.EventObject.ONE:
					Ext.getCmp('EREF_ReceptPanel').toggleCollapse();
				break;

				case Ext.EventObject.NUM_TWO:
				case Ext.EventObject.TWO:
					Ext.getCmp('EREF_PrivilegePanel').toggleCollapse();
				break;

				case Ext.EventObject.NUM_THREE:
				case Ext.EventObject.THREE:
					Ext.getCmp('EREF_DrugPanel').toggleCollapse();
				break;
			}
		},
		key: [
			Ext.EventObject.C,
			Ext.EventObject.G,
			Ext.EventObject.J,
			Ext.EventObject.NUM_ONE,
			Ext.EventObject.NUM_THREE,
			Ext.EventObject.NUM_TWO,
			Ext.EventObject.ONE,
			Ext.EventObject.R,
			Ext.EventObject.THREE,
			Ext.EventObject.TWO,
			Ext.EventObject.Z
		],
		scope: this,
		stopEvent: false
	}],
	layout: 'border',
	listeners: {
		'hide': function() {
			this.buttons[5].focus();
			this.onHide();
		}
	},
	maximizable: true,
	minHeight: 500,
	minWidth: 700,
	modal: true,
	onHide: Ext.emptyFn,
	plain: true,
	printRecept: function() {
		switch ( this.action ) {
			case 'add':
				this.doSave({
					checkPersonAge: true,
					checkPersonDeadDT: true,
					checkPersonSnils: true,
					copy: false,
					print: true
				});
			break;

			case 'view':
				var evn_recept_id = this.findById('EvnReceptEditForm').getForm().findField('EvnRecept_id').getValue();
                var ReceptForm_id = this.findById('EvnReceptEditForm').getForm().findField('ReceptForm_id').getValue();
				var evn_recept_set_date = this.findById('EvnReceptEditForm').getForm().findField('EvnRecept_setDate').getValue().format('Y-m-d');
				if(this.findById('EvnReceptEditForm').getForm().findField('Recept_Result_Code').getValue()==4)
				{
					Ext.Msg.alert(lang['oshibka'], 'Рецепт удален и не может быть распечатан');
					return false;
				}
				var that = this;
				saveEvnReceptIsPrinted({
					allowQuestion: false
					,callback: function(success) {
						if ( success == true ) {
							if (Ext.globalOptions.recepts.print_extension == 3) {
								if(ReceptForm_id != 2)
									window.open(C_EVNREC_PRINT_DS, '_blank');
								window.open(C_EVNREC_PRINT + '&EvnRecept_id=' + evn_recept_id, '_blank');
							} else {
								Ext.Ajax.request({
									url: '/?c=EvnRecept&m=getPrintType',
									callback: function(options, success, response) {
										if (success) {
											var result = Ext.util.JSON.decode(response.responseText);
											var PrintType = '';
											switch(result.PrintType) {
												case '1':
													PrintType = 2;
													break;
												case '2':
													PrintType = 3;
													break;
												case '3':
													PrintType = '';
													break;
											}

                                            switch (ReceptForm_id*1) {
                                                case 2: //1-МИ
                                                    if(result.CopiesCount == 1){
                                                        printBirt({
                                                            'Report_FileName': 'EvnReceptPrint4_1MI.rptdesign',
                                                            'Report_Params': '&paramEvnRecept=' + evn_recept_id,
                                                            'Report_Format': 'pdf'
                                                        });
                                                    } else {
                                                        if(PrintType == '') {
                                                            printBirt({
                                                                'Report_FileName': 'EvnReceptPrint1_1MI.rptdesign',
                                                                'Report_Params': '&paramEvnRecept=' + evn_recept_id,
                                                                'Report_Format': 'pdf'
                                                            });
                                                        } else {
                                                            printBirt({
                                                                'Report_FileName': 'EvnReceptPrint' + PrintType + '_1MI.rptdesign',
                                                                'Report_Params': '&paramEvnRecept=' + evn_recept_id,
                                                                'Report_Format': 'pdf'
                                                            });
                                                        }
                                                    }
                                                    break;
                                                case 9: //148-1/у-04(л)
                                                    //игнорируем настройки и печатаем сразу обе стороны
                                                    printBirt({
                                                        'Report_FileName': 'EvnReceptPrint_148_1u04_2InA4_2019.rptdesign',
                                                        'Report_Params': '&paramEvnRecept=' + evn_recept_id,
                                                        'Report_Format': 'pdf'
                                                    });
                                                    printBirt({
                                                        'Report_FileName': 'EvnReceptPrint_148_1u04_2InA4_2019Oborot.rptdesign',
                                                        'Report_Params': '&paramEvnRecept=' + evn_recept_id,
                                                        'Report_Format': 'pdf'
                                                    });
                                                    break;
                                                default:
                                                    var ReportName = 'EvnReceptPrint' + PrintType;
                                                    var ReportNameOb = 'EvnReceptPrintOb' + PrintType;
                                                    if(result.CopiesCount == 1) {
                                                        if(evn_recept_set_date >= '2016-07-30') {
                                                            ReportName = 'EvnReceptPrint4_2016_new';
                                                        } else if(evn_recept_set_date >= '2016-01-01') {
                                                            ReportName = 'EvnReceptPrint4_2016';
                                                        } else {
                                                            ReportName = 'EvnReceptPrint2_2015';
                                                        }
                                                        ReportNameOb = 'EvnReceptPrintOb2_2015';
                                                    } else {
                                                        if (evn_recept_set_date >= '2016-07-30') {
                                                            ReportName = ReportName + '_2016_new';
														} else if(evn_recept_set_date >= '2016-01-01') {
															ReportName = ReportName + '_2016';
														}
                                                    }
                                                    if (Ext.globalOptions.recepts.print_extension == 1) {
                                                        printBirt({
                                                            'Report_FileName': ReportNameOb + '.rptdesign',
                                                            'Report_Params': '&paramEvnRecept=' + evn_recept_id + '&paramProMedPort=' + result.server_port + '&paramProMedProto=' + result.server_http,
                                                            'Report_Format': 'pdf'
                                                        });
                                                    }
                                                    if(result.server_port != null) {
                                                        printBirt({
                                                            'Report_FileName': ReportName + '.rptdesign',
                                                            'Report_Params': '&paramEvnRecept=' + evn_recept_id + '&paramProMedPort=' + result.server_port + '&paramProMedProto=' + result.server_http,
                                                            'Report_Format': 'pdf'
                                                        });
                                                    } else {
                                                        printBirt({
                                                            'Report_FileName': ReportName + '.rptdesign',
                                                            'Report_Params': '&paramEvnRecept=' + evn_recept_id + '&paramProMedProto=' + result.server_http,
                                                            'Report_Format': 'pdf'
                                                        });
                                                    }
                                                    break;
                                            }
										}
									}.createDelegate(that)
								});
							}
						}
						else {
							sw.swMsg.alert('Ошибка', 'Ошибка при сохранении признака распечатывания рецепта');
						}
					}.createDelegate(this)
					,Evn_id: evn_recept_id
				});
				break;
		}
	},
	resizable: true,
	setReceptNumber: function() {
		// Для Уфы передается параметр выбранного типа рецепта (федеральный )

		// https://redmine.swan.perm.ru/issues/9875
		// [2012-05-18]: добавил передачу даты выписки рецепта, т.к. в новой схеме получения номера (задача #9292) не учитывается год, номер
		// генерируется по всем рецептам текущего ЛПУ

		var base_form = this.findById('EvnReceptEditForm').getForm();
		base_form.findField('EvnRecept_Num').setValue('');

		if ( !base_form.findField('EvnRecept_setDate').getValue() || !base_form.findField('ReceptFinance_id').getValue() ) {
			return false;
		}

		var isSaratov = (getGlobalOptions().region && getGlobalOptions().region.nick == 'saratov');
		var params = new Object();
		var url;

		params.EvnRecept_setDate = Ext.util.Format.date(base_form.findField('EvnRecept_setDate').getValue(), 'd.m.Y');
		params.ReceptFinance_id = base_form.findField('ReceptFinance_id').getValue();
        params.is_mi_1 = (base_form.findField('ReceptForm_id').getValue() == 2) ? true : false;
		url = C_RECEPT_NUM;
		if ( isSaratov == true ) {
			params.EvnRecept_Is7Noz = (base_form.findField('EvnRecept_Is7Noz').getValue() ? base_form.findField('EvnRecept_Is7Noz').getValue() : 1);
		}
		//console.log('params = ');
		//console.log(params);
		Ext.Ajax.request({
			callback: function(options, success, response) {
				if ( success ) {
					var response_obj = Ext.util.JSON.decode(response.responseText);
					base_form.findField('EvnRecept_Num').setValue(response_obj.EvnRecept_Num);
				}
				else {
					sw.swMsg.alert('Ошибка', 'Ошибка при определении номера рецепта', function() { base_form.findField('EvnRecept_setDate').focus(true); }.createDelegate(this) );
				}
			}.createDelegate(this),
			params: params,
			url: url
		});
	},
	setReceptSerial: function() {
		/*
		Длы Уфы:
		1. Для Федеральный льготников: Утверждена единая серия рецептов на 2010 год 02-10 для всех ЛПУ
		2. Для Региональных льготников: Для каждого ЛПУ утверждена своя серия рецептов РР-xxx, где
		xxx - уникальный номер для каждого ЛПУ

		Для Саратова:
		В качестве серия берется 0063 (https://redmine.swan.perm.ru/issues/14075)
		*/

		var base_form = this.findById('EvnReceptEditForm').getForm();
		var recept_ser_field = base_form.findField('EvnRecept_Ser');
		var recept_form_field =  base_form.findField('ReceptForm_id');
		// 122894. Если форма рецепта 1-МИ, то серия рецепта, дополняется в начале строки строкой 'МИ'. 
		var serPrefix = ( recept_form_field.getValue() == 2 ) ? 'МИ' : '';

		var isSaratov = (getGlobalOptions().region && getGlobalOptions().region.nick == 'saratov');

		if ( isSaratov == true ) {
			// https://redmine.swan.perm.ru/issues/14075
			recept_ser_field.setValue('0063');
		}
		else {
			if ( base_form.findField('ReceptFinance_id').getValue().toString() == '1' ) {
				recept_ser_field.setValue(serPrefix + Ext.globalOptions.recepts.evn_recept_fed_ser); 
			}
			else if ( base_form.findField('ReceptFinance_id').getValue().toString() == '2' ) {
				recept_ser_field.setValue(serPrefix + Ext.globalOptions.recepts.evn_recept_reg_ser);
			}
			else {
				recept_ser_field.setValue('');
			}
		}
	},
	setVKProtocolFieldsVisible: function() {
		var base_form = this.findById('EvnReceptEditForm').getForm();
		var vk_combo = base_form.findField('Drug_IsKEK');
		var num_field = base_form.findField('EvnRecept_VKProtocolNum');
		var date_field = base_form.findField('EvnRecept_VKProtocolDT');
		var cause_field = base_form.findField('CauseVK_id');
		var form_field = base_form.findField('ReceptForm_id');
		var form_id = form_field.getValue();
		var is_vk = (vk_combo.getValue() == 2);
		var is_visible = (form_id == 9 && is_vk); //9 - 148-1/у-04(л); нужно переделать на проверку кода, когда истечет срок действия записи с идентификатором 1

		if (is_visible) {
			num_field.ownerCt.show();
			date_field.ownerCt.show();
			cause_field.ownerCt.show();
		} else {
			num_field.ownerCt.hide();
			date_field.ownerCt.hide();
			cause_field.ownerCt.hide();
			if (this.action != 'view') {
				num_field.setValue(null);
				date_field.setValue(null);
				cause_field.setValue(null);
			}
		}
		num_field.setAllowBlank(!is_visible || this.action == 'view');
		date_field.setAllowBlank(!is_visible || this.action == 'view');
		cause_field.setAllowBlank(!is_visible || this.action == 'view');

		this.findById('EvnReceptEditForm').doLayout();
	},
    setReceptFormFilter: function() {
        var base_form = this.findById('EvnReceptEditForm').getForm();
        var form_field = base_form.findField('ReceptForm_id');
        var date_field = base_form.findField('EvnRecept_setDate');
        var form_id = form_field.getValue();

        form_field.lastQuery = '';
        form_field.getStore().clearFilter();

        if (this.action != 'view') { //фильтрация не нужна в режиме просмотра
            var dt = date_field.getValue();
            if (!(dt instanceof Date)) {
                dt = new Date();
                dt.setHours(0, 0, 0, 0);
            }

            var default_form_code = '148';
            var allowedReceptFormCodes = ['148','1-МИ'];

            form_field.getStore().filterBy(function(record) {
                var correct_code = record.get('ReceptForm_Code').inlist(allowedReceptFormCodes);
                var correct_beg_date = (Ext.isEmpty(record.get('ReceptForm_begDate')) || dt >= record.get('ReceptForm_begDate'));
                var correct_end_date = (Ext.isEmpty(record.get('ReceptForm_endDate')) || dt <= record.get('ReceptForm_endDate'));
                return (correct_code && correct_beg_date && correct_end_date);
            });

            //установка значения по умолчанию (если есть необходимость)
            var set_default_value = false;
            if (!Ext.isEmpty(form_id)) {
                var idx = form_field.getStore().findBy(function(record) {
                    return (record.get('ReceptForm_id') == form_id);
                })
                if (idx < 0) {
                    set_default_value = true;
                }
            } else {
                set_default_value = true;
            }

            if (set_default_value && !Ext.isEmpty(default_form_code)) {
                var idx = form_field.getStore().findBy(function(record) {
                    return (record.get('ReceptForm_Code') == default_form_code);
                })
                if (idx > -1) {
                    form_id = form_field.getStore().getAt(idx).get('ReceptForm_id');
                    form_field.setValue(form_id);
                    form_field.fireEvent('change', form_field, form_id);
                }
            }
        }
    },
	setReceptTypeFilter: function() {
		var base_form = this.findById('EvnReceptEditForm').getForm();
		var form_field = base_form.findField('ReceptForm_id');
		var type_field = base_form.findField('ReceptType_id');

		if (this.action != 'view' && getRegionNick() != 'kz') { //фильтрация не нужна в режиме просмотра, а также не применяется в регионе Казахстан
			this.getReceptElectronicAllow(function (allow_data) {
				type_field.lastQuery = '';
				type_field.getStore().clearFilter();

				var form_id = form_field.getValue();
				var type_id = type_field.getValue(); //запоминаем выбранную форму

				type_field.getStore().filterBy(function(record) {
					return (record.get('ReceptType_Code') != 3 || (allow_data.recept_electronic_allow && form_id != 2)); //3 - Электронный документ;  2 - МИ-1
				});

				var record_idx = type_field.getStore().findBy(function(record) {
					return (record.get('ReceptType_id') == type_id);
				});
				if (!Ext.isEmpty(type_id) && record_idx < 0 && type_field.getStore().getCount() > 0) {
					type_id = type_field.getStore().getAt(0).get('ReceptType_id');
					type_field.setValue(type_id);
					type_field.fireEvent('change', type_field, type_id);
				}
			});
		}
	},
	setReceptType: function () {
		var base_form = this.findById('EvnReceptEditForm').getForm();

		this.getReceptElectronicAllow(function (allow_data) {
			var receptForm = base_form.findField('ReceptForm_id').getValue();

			//если возможно выписывать с типом "Электронный документ" и форма рецепта не МИ-1
			if (receptForm != 2 && allow_data.recept_electronic_allow) {
				base_form.findField('ReceptType_id').enable();
				var index = base_form.findField('ReceptType_id').getStore().findBy(function (rec) {
					return (rec.get('ReceptType_Code') == 3);
				});
				if (index >= 0) {
					base_form.findField('ReceptType_id').setValue(base_form.findField('ReceptType_id').getStore().getAt(index).get('ReceptType_id'));
				}
			} else {
				base_form.findField('ReceptType_id').enable();
				// устанавливаем значение по-умолчанию "Тип рецепта" = "На листе"
				index = base_form.findField('ReceptType_id').getStore().findBy(function (rec) {
					return (rec.get('ReceptType_Code') == 2);
				});
				if (index >= 0) {
					base_form.findField('ReceptType_id').setValue(base_form.findField('ReceptType_id').getStore().getAt(index).get('ReceptType_id'));
				}
			}
			base_form.findField('ReceptType_id').fireEvent('change', base_form.findField('ReceptType_id'), base_form.findField('ReceptType_id').getValue());
		});
	},
	getReceptElectronicAllow: function(callback) { //вычисление допустимости выбора электронного рецпта, исходя из настроек и наличия у пациента разрешения на фвписку такого рецепта
		var wnd = this;
		var base_form = this.findById('EvnReceptEditForm').getForm();
		var recept_electronic_allow = getGlobalOptions().recept_electronic_allow; //разрешение выписки рецептов в электронной форме
		var result_data = new Object();

		result_data.recept_electronic_allow = (!Ext.isEmpty(recept_electronic_allow) && wnd.recept_electronic_is_agree == 2);

		if (typeof callback != 'function') {
			callback = Ext.emptyFn;
		}

		if (wnd.recept_electronic_is_agree != null) { //если уже есть информация о согласии пациента
			callback(result_data);
		} else { //если информации о согласии пациента еще нет, то грузим её с сервера
			Ext.Ajax.request({
				url: '/?c=Person&m=isReceptElectronicStatus',
				params: {
					Person_id: base_form.findField('Person_id').getValue()
				},
				callback: function(options, success, response) {
					var error_msg = null;
					if (success) {

						var response_obj = Ext.util.JSON.decode(response.responseText);
						if (response_obj && response_obj.Error_Msg) {
							error_msg = langs('Ошибка при получении сведений о согласии на рецепты в электронной форме');
						} else if (response_obj && !Ext.isEmpty(response_obj[0]['ReceptElectronic_IsAgree'])) {
							wnd.recept_electronic_is_agree = response_obj[0]['ReceptElectronic_IsAgree'];
							result_data.recept_electronic_allow = (!Ext.isEmpty(recept_electronic_allow) && wnd.recept_electronic_is_agree == 2);
						}
					} else {
						error_msg = langs('Ошибка при получении сведений о согласии на рецепты в электронной форме');
					}

					if (Ext.isEmpty(error_msg)) {
						callback(result_data);
					} else {
						sw.swMsg.alert(langs('Ошибка'), error_msg);
					}
				}
			});
		}
	},
	show: function() {
		sw.Promed.swEvnReceptEditWindow.superclass.show.apply(this, arguments);

//Ext.getCmp('EREF_MedStaffFactCombo').load();

		var win = this;

		var base_form = this.findById('EvnReceptEditForm').getForm();

		if ( !arguments[0] ) {
			sw.swMsg.alert('Ошибка', 'Отсутствуют необходимые параметры', function() { this.hide(); }.createDelegate(this) );
			return false;
		}

		this.action = null;
		this.callback = Ext.emptyFn;
		this.formStatus = 'edit';
		this.onHide = Ext.emptyFn;
		this.recept_electronic_is_agree = null; //согласие пациента на выписку рецепта в форме электронного документа

		this.restore();
		this.center();
		this.maximize();

		this.findById('EREF_DrugPanel').expand();
		this.findById('EREF_PrivilegePanel').expand();
		this.findById('EREF_ReceptPanel').expand();

		var diag_combo = base_form.findField('Diag_id');
		var drug_combo = base_form.findField('Drug_id');
		var drug_is_mnn_combo = base_form.findField('Drug_IsMnn');
		var drug_mnn_combo = base_form.findField('DrugMnn_id');
		var evn_recept_set_date_field = base_form.findField('EvnRecept_setDate');
		var lpu_section_combo = base_form.findField('LpuSection_id');
		var med_personal_combo = base_form.findField('MedStaffFact_id');
		var org_farmacy_combo = base_form.findField('OrgFarmacy_id');
		var privilege_type_combo = base_form.findField('PrivilegeType_id');
		var recept_discount_combo = base_form.findField('ReceptDiscount_id');
		var recept_finance_combo = base_form.findField('ReceptFinance_id');
		var recept_type_combo = base_form.findField('ReceptType_id');

		var diag_id = null;
		var dt = new Date();
		var evn_recept_id = 0;
		var evn_recept_pid = 0;
		var evn_recept_set_date = null;
		var lpu_section_id = null;
		var med_personal_id = null;
		var med_staff_fact_id = null;
		var person_id = 0;
		var person_evn_id = 0;
		var privilege_type_id = null;
		var recept_type_id = null;
		var server_id = 0;

		base_form.findField('EvnRecept_id').setValue(0);
		base_form.findField('Person_id').setValue(0);
		base_form.findField('PersonEvn_id').setValue(0);
		base_form.findField('Server_id').setValue(0);
		recept_type_combo.clearValue();
		recept_type_combo.enable();
		evn_recept_set_date_field.setRawValue('');
		base_form.findField('EvnRecept_Ser').setRawValue('');
		base_form.findField('EvnRecept_Num').setRawValue('');
		base_form.findField('ReceptValid_id').getStore().clearFilter();
		lpu_section_combo.clearValue();
		lpu_section_combo.disable();
		lpu_section_combo.getStore().removeAll();
		med_personal_combo.clearValue();
		med_personal_combo.disable();
		med_personal_combo.getStore().removeAll();
		diag_combo.clearValue();
		diag_combo.getStore().removeAll();
		recept_finance_combo.clearValue();
		recept_finance_combo.disable();
		recept_discount_combo.clearValue();
		privilege_type_combo.clearValue();
		privilege_type_combo.disable();
		privilege_type_combo.getStore().clearFilter();
		privilege_type_combo.getStore().removeAll();
		drug_is_mnn_combo.setValue(2);
		drug_mnn_combo.clearValue();
		//drug_mnn_combo.disable(); Пока закомментировал
		drug_mnn_combo.getStore().removeAll();
		drug_combo.clearValue();
		//drug_combo.disable(); Пока закомментировал
		drug_combo.getStore().removeAll();
		org_farmacy_combo.clearValue();
		//org_farmacy_combo.disable(); Пока закомментировал
		org_farmacy_combo.getStore().removeAll();
		base_form.findField('Drug_IsKEK').setValue(1); //1 - Нет
		base_form.findField('Drug_Price').setValue('');
		base_form.findField('EvnRecept_Kolvo').maxValue = undefined;
		base_form.findField('EvnRecept_Kolvo').setValue(1);
		base_form.findField('EvnRecept_Signa').setRawValue('');
		base_form.findField('ReceptUrgency_id').clearValue();
		/*
		base_form.findField('EvnRecept_Signa').disable();
		base_form.findField('EvnRecept_Signa').setAllowBlank(true);
		*/
		if ( arguments[0].action )
			this.action = arguments[0].action;

		if ( arguments[0].callback )
			this.callback = arguments[0].callback;

		if ( arguments[0].Diag_id )
			diag_id = arguments[0].Diag_id;

		//console.log('show diag_id = ' + diag_id);

		if ( arguments[0].EvnRecept_id )
			evn_recept_id = arguments[0].EvnRecept_id;

		if ( arguments[0].EvnRecept_pid )
			evn_recept_pid = arguments[0].EvnRecept_pid;

		if ( arguments[0].EvnRecept_setDate )
			evn_recept_set_date = arguments[0].EvnRecept_setDate;

		if ( arguments[0].LpuSection_id )
			lpu_section_id = arguments[0].LpuSection_id;

		if ( arguments[0].MedPersonal_id )
			med_personal_id = arguments[0].MedPersonal_id;

		if ( arguments[0].onHide )
			this.onHide = arguments[0].onHide;

		if ( arguments[0].Person_id )
			person_id = arguments[0].Person_id;

		if ( arguments[0].PersonEvn_id )
			person_evn_id = arguments[0].PersonEvn_id;

		if ( arguments[0].PrivilegeType_id )
			privilege_type_id = arguments[0].PrivilegeType_id;

		if ( arguments[0].ReceptType_id > 0 )
			recept_type_id = arguments[0].ReceptType_id;

		if ( arguments[0].Server_id >= 0 )
			server_id = arguments[0].Server_id;
                    
                if ( arguments[0].ARMType) {
			this.ARMType = arguments[0].ARMType;
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

		drug_combo.getStore().baseParams = {
			Date: null,
			DrugMnn_id: 0,
			EvnRecept_Is7Noz_Code: 0,
			mode: 'start',
			ReceptFinance_Code: 0,
			ReceptType_Code: 0,
			PrivilegeType_id: 0
		};
		drug_combo.lastQuery = '';

		drug_mnn_combo.getStore().baseParams = {
			Date: null,
			EvnRecept_Is7Noz_Code: 0,
			ReceptFinance_Code: 0,
			ReceptType_Code: 0,
			PrivilegeType_id: 0
		};
		drug_mnn_combo.lastQuery = '';

		org_farmacy_combo.getStore().baseParams = {
			EvnRecept_Is7Noz_Code: 0,
			ReceptFinance_Code: 0,
			ReceptType_Code: 0
		};
		org_farmacy_combo.lastQuery = '';

		privilege_type_combo.getStore().baseParams = {
			Person_id: person_id
		}

		base_form.setValues({
			EvnRecept_id: evn_recept_id,
			EvnRecept_pid: evn_recept_pid,
			Person_id: person_id,
			PersonEvn_id: person_evn_id,
			Server_id: server_id
		});

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT });
		loadMask.show();
		var that = this;
		this.findById('EREF_PersonInformationFrame').load({
			Person_id: person_id,
			Server_id: server_id,
			callback: function() {
				var field = base_form.findField('EvnRecept_setDate');
				clearDateAfterPersonDeath('personpanelid', 'EREF_PersonInformationFrame', field);

				var person_information = win.findById('EREF_PersonInformationFrame');

				// @task https://redmine.swan.perm.ru//issues/122143
				if ( !Ext.isEmpty(recept_type_id) && recept_type_id == 2 && !Ext.isEmpty(person_information.getFieldValue('Person_deadDT')) ) {
					sw.swMsg.alert(langs('Ошибка'), langs('У пациента указана дата смерти, выписка рецепта с типом «на листе» невозможна.'), function() {
						win.hide();
					});
					return false;
				}

				var new_date = new Date().format('Y-m-d');
				if (base_form.findField('EvnRecept_setDate' != undefined))
					new_date = Ext.util.Format.date(base_form.findField('EvnRecept_setDate').getValue(), 'Y-m-d'); 
				var person_age = swGetPersonAge(person_information.getFieldValue('Person_Birthday'), new_date);
				var sex_code = person_information.getFieldValue('Sex_Code');
				var is_retired = ((sex_code == 2 && person_age >= 55) || (sex_code == 1 && person_age >= 60)); //опредлеяем, пенсионер ли наш пациент
				var ReceptForm_id = Ext.isEmpty(base_form.findField('ReceptForm_id').getValue())?base_form.findField('ReceptForm_id').getValue():0;
				if(that.action != 'view'){
					if(ReceptForm_id == 2)
						base_form.findField('ReceptValid_id').getStore().filterBy(function(rec) {
							return (rec.get('ReceptValid_Code').toString().inlist(new_date >= '2016-01-01'?['4','9','10','11']:['1', '2']));
						});
					else
						base_form.findField('ReceptValid_id').getStore().filterBy(function(rec) {
							return (rec.get('ReceptValid_Code').toString().inlist(new_date >= '2016-01-01'?['4', '9', '10', '11']:['1', '2', '4', '7']));
						});
				}
				// Устанавливаем значение по-умолчанию
				if(that.action == 'add'){
					var index = base_form.findField('ReceptValid_id').getStore().findBy(function(rec) {
						if(new_date >= '2016-01-01')
							return is_retired?(rec.get('ReceptValid_Code') == 10):(rec.get('ReceptValid_Code') == 9);
						else
							return is_retired?(rec.get('ReceptValid_Code') == 2):(rec.get('ReceptValid_Code') == 1);
					});
					if ( index >= 0 ) {
						base_form.findField('ReceptValid_id').setValue(base_form.findField('ReceptValid_id').getStore().getAt(index).get('ReceptValid_id'));
					}
				}
			}
		});

		recept_finance_combo.getStore().filterBy(function(rec) {
			return (rec.get('ReceptFinance_Code').toString().inlist([ '1', '2' ]));
		});

		/*base_form.findField('ReceptValid_id').getStore().filterBy(function(rec) {
			return (rec.get('ReceptValid_Code').toString().inlist([ '1', '2', '4', '7' ]));
		});*/

		base_form.findField('ReceptUrgency_id').hideContainer();
		base_form.findField('ReceptUrgency_id').disable();

		var index;

		base_form.findField('PrescrSpecCause_id').hideContainer();
		base_form.findField('PrescrSpecCause_id').allowBlank = true;
		base_form.findField('PrescrSpecCause_id').disable();
		base_form.findField('PrescrSpecCause_cb').hideContainer();

		base_form.findField('EvnRecept_IsExcessDose').hideContainer();

		switch ( this.action ) {
			case 'add': 
				Ext.getCmp('EREF_DrugResult').hide();  //  Скрываем информацию об отказе
                                this.enableEdit(true);
				Ext.getCmp('EREF_DrugResult').disable();
				this.setTitle(WND_DLO_RCPTADD);

				if ( !evn_recept_set_date ) {
					evn_recept_set_date = dt;
				}

				lpu_section_combo.setValue(lpu_section_id);
				med_personal_combo.setValue(med_personal_id);
				privilege_type_combo.setValue(privilege_type_id);
				base_form.findField('ReceptForm_id').getStore().load({
					callback: function() {
						evn_recept_set_date_field.setValue(evn_recept_set_date);
						evn_recept_set_date_field.fireEvent('change', evn_recept_set_date_field, evn_recept_set_date, null);
					}
				});

				// Устанавливаем значение по-умолчанию "Срок действия" = "Три месяца"
				index = base_form.findField('ReceptValid_id').getStore().findBy(function(rec) {
					return (rec.get('ReceptValid_Code') == 1);
				});
				if ( index >= 0 ) {
					base_form.findField('ReceptValid_id').setValue(base_form.findField('ReceptValid_id').getStore().getAt(index).get('ReceptValid_id'));
				}

				/*evn_recept_set_date_field.setValue(evn_recept_set_date);
				evn_recept_set_date_field.fireEvent('change', evn_recept_set_date_field, evn_recept_set_date, null);*/


				if ( recept_type_id ) {
					recept_type_combo.disable();
					recept_type_combo.setValue(recept_type_id);
					recept_type_combo.fireEvent('change', recept_type_combo, recept_type_id);
				} else {
					this.setReceptType();
				}
				this.setVKProtocolFieldsVisible();
				this.setReceptTypeFilter();

				if ( diag_id ) {
					diag_combo.getStore().load({
						callback: function() {
							diag_combo.getStore().each(function(record) {
								if ( record.get('Diag_id') == diag_id ) {
									diag_combo.fireEvent('select', diag_combo, record, 0);
								}
							});
						},
						params: { where: "where Diag_id = " + diag_id }
					});
				}

				//base_form.clearInvalid();

				if (base_form.findField('ReceptForm_id').getValue() == 9){
					base_form.findField('PrescrSpecCause_cb').showContainer();
				}

				loadMask.hide();

				if ( recept_type_combo.disabled ) {
					evn_recept_set_date_field.focus(true, 250);
				}
				else {
					recept_type_combo.focus(true, 250);
				}
			break;

			case 'edit':
				this.action = 'view';

			case 'view':
				this.enableEdit(false);
				this.setTitle(WND_DLO_RCPTVIEW);

                base_form.findField('ReceptForm_id').getStore().load();

				base_form.load({
					failure: function() {
						loadMask.hide();
						sw.swMsg.alert('Ошибка', 'Ошибка при загрузке данных формы', function() { this.hide(); }.createDelegate(this) );
					}.createDelegate(this),
					params: {
						EvnRecept_id: evn_recept_id,
						archiveRecord: win.archiveRecord
					},
					success: function(frm, action) {

						var response_obj = Ext.util.JSON.decode(action.response.responseText);
						if (!Ext.isEmpty (response_obj[0].EvnRecept_IsExcessDose)) {
							base_form.findField('EvnRecept_IsExcessDose').setValue(1);
							base_form.findField('EvnRecept_IsExcessDose').disable();
							base_form.findField('EvnRecept_IsExcessDose').showContainer();
						}

						var drug_id = drug_combo.getValue();
						var drug_mnn_id = drug_mnn_combo.getValue();
						var org_farmacy_id = org_farmacy_combo.getValue();
						var recept_finance_code = 0;
						var recept_finance_id = recept_finance_combo.getValue();
						var recept_type_code = 0;

						if (!Ext.isEmpty (response_obj[0].PrescrSpecCause_id)) {
							base_form.findField('PrescrSpecCause_cb').showContainer();
							base_form.findField('PrescrSpecCause_cb').setValue(1);
							base_form.findField('PrescrSpecCause_cb').disable();
							base_form.findField('PrescrSpecCause_id').showContainer();
							base_form.findField('PrescrSpecCause_id').allowBlank = false;
							base_form.findField('PrescrSpecCause_id').disable();
						}

						if (!Ext.isEmpty(response_obj[0].ReceptUrgency_id)) {
							base_form.findField('ReceptUrgency_id').showContainer();
						}

						if (response_obj[0].EvnRecept_IsSigned) {
							base_form.findField('EvnRecept_IsSigned').setValue(response_obj[0].EvnRecept_IsSigned);
						}

						var recept_finance_record = recept_finance_combo.getStore().getById(recept_finance_id);

						if ( recept_finance_record ) {
							recept_finance_code = recept_finance_record.get('ReceptFinance_Code');
						}
						var Recept_Result_Code = base_form.findField('Recept_Result_Code').getValue();
						base_form.findField('EvnRecept_Drugs').hideContainer();
						base_form.findField('Recept_Delay_Info').hideContainer();
						if(Recept_Result_Code == '0'){ //Рецепт обслужен
							base_form.findField('EvnRecept_Drugs').showContainer();
						}
						else
						{
							base_form.findField('Recept_Delay_Info').showContainer();
						}
						evn_recept_set_date = Ext.util.Format.date(evn_recept_set_date_field.getValue(), 'd.m.Y');
						lpu_section_id = lpu_section_combo.getValue();
						med_personal_id = base_form.findField('MedPersonal_id').getValue();

						privilege_type_id = privilege_type_combo.getValue();
						recept_type_id = recept_type_combo.getValue();

						var recept_type_record = recept_type_combo.getStore().getById(recept_type_id);

						if ( recept_type_record ) {
							recept_type_code = recept_type_record.get('ReceptType_Code');
						}
						if(recept_type_code == 2 || (recept_type_code == 3 && base_form.findField('EvnRecept_IsSigned').getValue() == 2)){
							win.buttons[2].show();
						}
						else
						{
							win.buttons[2].hide();
						}
						setLpuSectionGlobalStoreFilter({
							id: lpu_section_id,
							isDlo: true,
							onDate: evn_recept_set_date
						});

						setMedStaffFactGlobalStoreFilter({
							isDlo: true,
							onDate: evn_recept_set_date,
							fromRecept: true
						});
						lpu_section_combo.getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));
						med_personal_combo.getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));

						var lpu_section_record = lpu_section_combo.getStore().getById(lpu_section_id);
						var med_personal_record = med_personal_combo.getStore().getById(med_staff_fact_id);
						
						if ( !lpu_section_record ) {
							lpu_section_combo.clearValue();
							lpu_section_combo.fireEvent('change', lpu_section_combo, -1, lpu_section_id);
						}
						else {
							lpu_section_combo.setValue(lpu_section_id);
						}

						var index = med_personal_combo.getStore().findBy(function(rec) {
							if ( rec.get('LpuSection_id') == lpu_section_id && rec.get('MedPersonal_id') == med_personal_id ) {
								return true;
							}
							else {
								return false;
							}
						});
						
						if ( index >= 0 ) {
							med_personal_combo.setValue(med_personal_combo.getStore().getAt(index).get('MedStaffFact_id'));
						}
						else {
							med_personal_combo.getStore().load({
								callback: function() {
									index = med_personal_combo.getStore().findBy(function(rec) {
										if ( rec.get('MedPersonal_id') == med_personal_id && rec.get('LpuSection_id') == lpu_section_id ) {
											return true;
										}
										else {
											return false;
										}
									});

									if ( index >= 0 ) {
										med_personal_combo.setValue(med_personal_combo.getStore().getAt(index).get('MedStaffFact_id'));
									}
									else {
										med_personal_combo.clearValue();
										med_personal_combo.fireEvent('change', med_personal_combo, -1);
									}
								}.createDelegate(this),
								params: {
									LpuSection_id: lpu_section_id,
									MedPersonal_id: med_personal_id
								}
							});
						}

						privilege_type_combo.getStore().load({
							params: {
								date: evn_recept_set_date,
								Person_id: person_id
							},
							callback: function(records, options, success) {
								if ( !success ) {
									loadMask.hide();
									sw.swMsg.alert('Ошибка', 'Ошибка при загрузке справочника категорий льготы', function() { this.hide(); }.createDelegate(this) );
									return false;
								}

								privilege_type_combo.setValue(privilege_type_id);

								var privilege_type_record = privilege_type_combo.getStore().getById(privilege_type_id);

								if ( !privilege_type_record ) {
									loadMask.hide();
									sw.swMsg.alert('Ошибка', 'Не найдена запись в списке категорий', function() { this.hide(); }.createDelegate(this) );
									return false;
								}

								diag_combo.getStore().load({
									callback: function() {
										diag_combo.getStore().each(function(record) {
											if ( record.get('Diag_id') == diag_combo.getValue() ) {
												diag_combo.fireEvent('select', diag_combo, record, 0);
											}
										});
									},
									params: { where: "where Diag_id = " + diag_combo.getValue() }
								});

								drug_combo.getStore().baseParams.Date = evn_recept_set_date;
								drug_combo.getStore().baseParams.ReceptFinance_Code = recept_finance_code;
								drug_combo.getStore().baseParams.ReceptType_Code = recept_type_code;
								drug_mnn_combo.getStore().baseParams.Date = evn_recept_set_date;
								drug_mnn_combo.getStore().baseParams.ReceptType_Code = recept_type_code;

								drug_combo.getStore().load({
									callback: function(records, options, success) {
										drug_combo.setValue(drug_id);

										org_farmacy_combo.getStore().load({
											callback: function() {
												org_farmacy_combo.setValue(org_farmacy_id)
											},
											params: {
												Drug_id: drug_id,
												OrgFarmacy_id: org_farmacy_id
											}
										});

										var selected_record = drug_combo.getStore().getById(drug_id);

										if ( selected_record ) {
											//base_form.findField('Drug_Price').setValue(selected_record.get('Drug_Price'));

											if(selected_record.get('Drug_IsKEK') == 2) { //если у выбранного препарата проставлен признак выписки через ВК
												base_form.findField('Drug_IsKEK').setValue(2); //2 - Да
											}
											base_form.findField('Drug_IsKEK').setLinkedFieldValues();
										}
										else {
											drug_combo.clearValue();
										}

										drug_mnn_combo.getStore().load({
											callback: function(records, options, success) {
												drug_mnn_combo.setValue(drug_mnn_id);

												recept_type_combo.disable();
												base_form.findField('EvnRecept_Num').disable();
												base_form.findField('EvnRecept_Ser').disable();

												loadMask.hide();

												base_form.clearInvalid();

												this.buttons[this.buttons.length - 1].focus();
											}.createDelegate(this),
											params: {
												DrugMnn_id: drug_mnn_id
											}
										});
									}.createDelegate(this),
									params: {
										Drug_id: drug_id
									}
								});

								that.setVKProtocolFieldsVisible();
							}.createDelegate(this)
						});
                                                
						/*if (base_form.findField('ReceptWrongDelayType_id').getValue() == 3) {
							Ext.getCmp('EREF_DrugWrong').show();
						} else 
							{
							Ext.getCmp('EREF_DrugWrong').hide();
						}*/
						Ext.getCmp('EREF_DrugResult').show();
						Ext.getCmp('EREF_DrugResult').disable();
						if (this.ARMType == 'dpoint')
							this.buttons[1].hide();
						else
							this.buttons[1].show();
                                                
					}.createDelegate(this),
					url: C_EVNREC_LOAD
				});
			break;

			default:
				sw.swMsg.alert('Ошибка', 'Неверно указан режим открытия формы', function() { this.hide(); }.createDelegate(this) );
			break;
		}; 
	},
	title: WND_DLO_RECADD,
	width: 700
});

