/**
* swEvnReceptEditWindow - окно редактирования рецепта.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      DLO
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Stas Bykov aka Savage (savage@swan.perm.ru)
* @version      0.002-29.03.2010
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
/*NO PARSE JSON*/

sw.Promed.swEvnReceptEditWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swEvnReceptEditWindow',
	objectSrc: '/jscore/Forms/Dlo/swEvnReceptEditWindow.js',

	action: null,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	collapsible: true,
	doCopy: function() {
		if ( this.action == 'add' ) {
			this.doSave({
				checkDrugRequest: true,
				checkPersonAge: true,
				checkPersonDeadDT: true,
				checkPersonSnils: true,
				copy: true,
				print: false
			});
		}
		else {
			// Открыть поля для редактирования
			this.action = 'add';

			var base_form = this.findById('EvnReceptEditForm').getForm();

			var recept_finance_id = base_form.findField('ReceptFinance_id').getValue();
			var recept_type_id = base_form.findField('ReceptType_id').getValue();

			this.setReceptValidFilter();

			base_form.findField('ReceptType_id').fireEvent('change', base_form.findField('ReceptType_id'), recept_type_id);
			base_form.findField('ReceptFinance_id').fireEvent('change', base_form.findField('ReceptFinance_id'), recept_finance_id);

			base_form.findField('EvnRecept_id').setValue(0);

			base_form.findField('EvnRecept_Kolvo').maxValue = undefined;
			base_form.findField('EvnRecept_Kolvo').setValue(1);

			this.enableEdit(true);
			Ext.getCmp('EREF_DrugResult').hide();
			Ext.getCmp('EREF_DrugResult').disable();
			this.setTitle(WND_DLO_RCPTADD);

			base_form.findField('EvnRecept_setDate').fireEvent('change', base_form.findField('EvnRecept_setDate'), base_form.findField('EvnRecept_setDate').getValue());

			base_form.findField('EvnRecept_setDate').focus(true);
			if (getRegionNick() != 'kz') {
				base_form.findField('ReceptUrgency_id').enable();
				base_form.findField('ReceptUrgency_id').clearValue();
			}

			base_form.findField('EvnRecept_IsExcessDose').setValue(0);
			base_form.findField('EvnRecept_IsExcessDose').hideContainer();

			base_form.findField('ReceptForm_id').fireEvent('change', base_form.findField('ReceptForm_id'), base_form.findField('ReceptForm_id').getValue());
		}
	},
	doSave: function(options) {
		// options @Object
		// options.checkDrugRequest @Boolean
		// options.checkPersonAge @Boolean
		// options.checkPersonDeadDT @Boolean
		// options.checkPersonSnils @Boolean
		// options.copy @Boolean 
		// options.print @Boolean Вызывать печать рецепта, если true
		var win = this;
		
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
		var record;

		if ( person_information.getFieldValue('Person_RAddress') == null || person_information.getFieldValue('Person_RAddress').toString().length == 0 ) {
			this.formStatus = 'edit';
			sw.swMsg.alert('Ошибка', 'Сохранение невозможно [Не задан адрес регистрации]');
			return false;
		}

		if ( options.checkPersonSnils && (person_information.getFieldValue('Person_Snils') == null || person_information.getFieldValue('Person_Snils').toString().length == 0) ) {
			/*sw.swMsg.show({
				buttons: Ext.Msg.YESNO,
				fn: function ( buttonId ) {
					this.formStatus = 'edit';

					if ( buttonId == 'yes' ) {
						this.doSave({
							checkDrugRequest: options.checkDrugRequest,
							checkPersonAge: options.checkPersonAge,
							checkPersonDeadDT: options.checkPersonDeadDT,
							checkPersonSnils: false,
							copy: options.copy,
							print: options.print
						});
					}
				}.createDelegate(this),
				msg: 'У пациента не задан СНИЛС. Продолжить сохранение рецепта?',
				title: 'Проверка СНИЛС'
			});*/
			if((getGlobalOptions().region.nick.inlist(['perm','ufa'])) || (getGlobalOptions().region.nick.inlist(['khak','saratov']) && base_form.findField('ReceptFinance_id').getValue()==1)) //https://redmine.swan.perm.ru/issues/79194
			{
				sw.swMsg.alert('Ошибка', 'У данного пациента отсутствует СНИЛС', function() {
					win.formStatus = 'edit';
					win.findById('EREF_PersonInformationFrame').panelButtonClick(2);
				});
				return false;
			}
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
									checkDrugRequest: options.checkDrugRequest,
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

		if(win.is_vzn && !base_form.findField('Diag_id').getValue().inlist(win.vzn_diag_list)) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					this.formStatus = 'edit';
					base_form.findField('Diag_id').clearValue();
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: 'Сохранение рецепта не возможно: не верно указан диагноз',
				title: 'Ошибка'
			});
			return false;
		}

		// https://redmine.swan.perm.ru/issues/4091
		if ( options.checkPersonAge ) {
			record = base_form.findField('ReceptValid_id').getStore().getById(base_form.findField('ReceptValid_id').getValue());

			if ( !record ) {
				this.formStatus = 'edit';
				sw.swMsg.alert('Ошибка', 'Не заполнено поле "Срок действия рецепта".', function() { base_form.findField('ReceptValid_id').focus(true); });
				return false;
			}

			var sex_code = person_information.getFieldValue('Sex_Code');
			
			var hasLgotType83or84 = false;
			var is_acs_cost = (this.getComboSelectedRecordValue(this.cost_type_combo, 'WhsDocumentCostItemType_Nick') == 'acs'); //признак выбора программы "ССЗ"
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
			// проверка не производится если выбрана программа "ССЗ"
			if (getRegionNick() != 'kz' && !is_acs_cost && ( record.get('ReceptValid_Code').inlist([1,4,9,11]) ) && ((sex_code == 1 && person_age >= 60) || (sex_code == 2 && person_age >= 55) || hasLgotType83or84) ) {
				sw.swMsg.show({
					buttons: Ext.Msg.YESNO,
					fn: function ( buttonId ) {
						this.formStatus = 'edit';

						if ( buttonId == 'yes' ) {
							this.doSave({
								checkDrugRequest: options.checkDrugRequest,
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
								checkDrugRequest: options.checkDrugRequest,
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

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Проверка возможности сохранения рецепта..." });
		loadMask.show();

		if ( base_form.findField('ReceptValid_id').disabled ) {
			post.ReceptValid_id = base_form.findField('ReceptValid_id').getValue();
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

		if ( base_form.findField('Drug_Price').disabled ) {
			post.Drug_Price = base_form.findField('Drug_Price').getValue();
		}

		if ( this.cost_type_combo.disabled ) {
			post.WhsDocumentCostItemType_id = this.cost_type_combo.getValue();
		}

		record = base_form.findField('MedStaffFact_id').getStore().getById(base_form.findField('MedStaffFact_id').getValue());

		if ( !record ) {
			this.formStatus = 'edit';
			sw.swMsg.alert('Ошибка', 'Не выбран врач', function() { base_form.findField('MedStaffFact_id').focus(true); });
			loadMask.hide();
			return false;
		}

		post.LpuSection_id = base_form.findField('LpuSection_id').getValue();
        post.ReceptForm_id = base_form.findField('ReceptForm_id').getValue();
		post.PersonPrivilege_id = base_form.findField('PrivilegeType_id').getFieldValue('PersonPrivilege_id');
		post.DrugFinance_id = this.drug_finance_combo.getValue();
		post.EvnRecept_Is7Noz = this.is_vzn ? 2 : 1;
		base_form.findField('MedPersonal_id').setValue(record.get('MedPersonal_id'));

		var evn_recept_is_extemp = base_form.findField('EvnRecept_IsExtemp').getValue();

		switch ( evn_recept_is_extemp.toString() ) {
			case '1':
				// Проверка заполнения Drug_id
				post.Drug_IsKEK = base_form.findField('Drug_IsKEK').getValue();

				var index = base_form.findField('Drug_id').getStore().findBy(function(rec) {
					if ( rec.get('Drug_id') == base_form.findField('Drug_id').getValue() ) {
						return true;
					}
					else {
						return false;
					}
				});
				record = base_form.findField('Drug_id').getStore().getAt(index);

				if ( !record ) {
					this.formStatus = 'edit';
					sw.swMsg.alert('Ошибка', 'Не выбран медикамент', function() { base_form.findField('Drug_id').focus(true); });
					loadMask.hide();
					return false;
				}

				if ( !record.get('DrugRequestRow_id') && options.checkDrugRequest ) {
					sw.swMsg.show({
						buttons: Ext.Msg.YESNO,
						fn: function ( buttonId ) {
							loadMask.hide();
							this.formStatus = 'edit';

							if ( buttonId == 'yes' ) {
								this.doSave({
									checkDrugRequest: false,
									checkPersonAge: options.checkPersonAge,
									checkPersonDeadDT: options.checkPersonDeadDT,
									checkPersonSnils: options.checkPersonSnils,
									copy: options.copy,
									print: options.print
								});
							}
						}.createDelegate(this),
						msg: 'Выбранный медикамент отсутствует в заявке. Информация о выписке не в соответствии с заявкой будет отправлена в министерство здравоохранения. Выписать рецепт?',
						title: 'Проверка рецепта'
					});
					return false;
				}

				post.DrugRequestRow_id = record.get('DrugRequestRow_id');

                var diag_id = base_form.findField('Diag_id').getValue();
                var privilege_type = base_form.findField('PrivilegeType_id').getValue();
                var recept_finance = base_form.findField('ReceptFinance_id').getValue();

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
                            if (!win.is_vzn && (recept_finance=='2') && getGlobalOptions().recept_diag_control == 2){ //Проверка на соответствие диагноза выбранной льготе
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
							sw.swMsg.show({
								buttons: Ext.Msg.YESNO,
								fn: function ( buttonId ) {
									loadMask.hide();

									if ( buttonId == 'yes' ) {
                                        if (!win.is_vzn && (recept_finance=='2') && getGlobalOptions().recept_diag_control == 2){ //Проверка на соответствие диагноза выбранной льготе
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
			break;

			case '2':
				// Проверка заполнения EvnRecept_ExtempContents
                if (!win.is_vzn && (recept_finance=='2') && getGlobalOptions().recept_diag_control == 2){ //Проверка на соответствие диагноза выбранной льготе
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
			break;

			default:
				this.formStatus = 'edit';
			break;
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

						this.action = 'view';
						this.enableEdit(false);
						this.setTitle(WND_DLO_RCPTVIEW);

						base_form.findField('Drug_id').disable();
						base_form.findField('DrugMnn_id').disable();
						base_form.findField('DrugRequestMnn_id').disable();
						base_form.findField('DrugRequestRow_IsReserve').disable();
						base_form.findField('EvnRecept_Signa').disable();
						base_form.findField('EvnRecept_Signa').setAllowBlank(true);
						base_form.findField('Lpu_rid').disable();
						base_form.findField('LpuSection_id').disable();
						base_form.findField('MedPersonal_rid').disable();
						base_form.findField('MedStaffFact_id').disable();
						base_form.findField('OrgFarmacy_id').disable();
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
						response.Server_id = base_form.findField('Server_id').getValue();

						this.callback({ EvnReceptData: response });
						if ( options.print ) {
							this.buttons[2].focus();
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
	setDrugAllowedQuantity: function() {
		var wnd = this;
		var base_form = this.findById('EvnReceptEditForm').getForm();
		var q_field = base_form.findField('Drug_AllowedQuantity');
		var dt_field = base_form.findField('EvnRecept_setDate');
		var req_combo = base_form.findField('DrugRequestMnn_id');
		var params = new Object();

		params.ReceptFinance_id = base_form.findField('ReceptFinance_id').getValue();
		params.Drug_id = base_form.findField('Drug_id').getValue();
		params.Date = dt_field.getValue() != null && dt_field.getValue() != '' ? dt_field.getValue().format('Y-m-d') : null;
		params.DrugMnn_id = null;
		params.Lpu_id = Ext.globalOptions.globals.lpu_id;

		if (req_combo.getValue() > 0) {
			var idx = req_combo.getStore().findBy(function(rec) { return rec.get('DrugRequestPeriod_id') == req_combo.getValue(); });
			if (idx >= 0) {
				var record = req_combo.getStore().getAt(idx);
				params.DrugMnn_id = record.get('DrugMnn_id');
			}
		}

		if (params.ReceptFinance_id > 0 && params.Date != null && (params.DrugRequest_id > 0 || params.Drug_id > 0)) {
			Ext.Ajax.request({
				url: '/?c=DrugRequestRecept&m=getDrugRequestReceptTotalKolvo',
				params: params,
				callback: function(options, success, response) {
					var response_obj = Ext.util.JSON.decode(response.responseText);
					if (success && response_obj.success && response_obj.sum > 0) {
						q_field.setValue(response_obj.sum);
						q_field.ownerCt.show();
					} else {
						q_field.setValue(null);
						q_field.ownerCt.hide();
					}
				}
			});
		} else {
			q_field.setValue(null);
			q_field.ownerCt.hide();
		}
	},
	draggable: true,
	onEnableEdit: function(enable) {
		// кнопка Копия доступна и для режима просмотра и для режима редактирования
		this.buttons[1].show();
	},
	formStatus: 'edit',
	height: 500,
	id: 'EvnReceptEditWindow',
	initComponent: function() {
		var wnd = this;
		this.formFirstShow = true;

		this.person_register_store = new Ext.data.Store({
			autoLoad: false,
			reader: new Ext.data.JsonReader({
				id: 'PersonRegister_id'
			}, [
				{ name: 'PersonRegister_id', type: 'int', mapping: 'PersonRegister_id' },
				{ name: 'PersonRegisterType_id', type: 'int', mapping: 'PersonRegisterType_id' },
				{ name: 'Diag_id', type: 'int', mapping: 'Diag_id' },
				{ name: 'PersonRegister_setDate', type: 'date', mapping: 'PersonRegister_setDate', dateFormat: 'd.m.Y' },
				{ name: 'PersonRegister_disDate', type: 'date', mapping: 'PersonRegister_disDate', dateFormat: 'd.m.Y' }
			]),
			url: '/?c=EvnRecept&m=loadPersonRegisterList'
		});

		this.privilege_type_combo = new sw.Promed.SwBaseLocalCombo({
			allowBlank: false,
			codeField: 'PrivilegeType_VCode',
			displayField: 'PrivilegeType_Name',
			editable: false,
			fieldLabel: 'Категория',
			hiddenName: 'PrivilegeType_id',
			lastQuery: '',
			tabIndex: TABINDEX_EREF + 12,
			validateOnBlur: true,
			valueField: 'PrivilegeType_id',
			width: 517,
			store: new Ext.data.Store({
				autoLoad: false,
				reader: new Ext.data.JsonReader({
					id: 'PrivilegeType_id'
				}, [
					{ name: 'PrivilegeType_Code', mapping: 'PrivilegeType_Code', type: 'int' },
					{ name: 'PrivilegeType_VCode', mapping: 'PrivilegeType_VCode' },
					{ name: 'PrivilegeType_id', mapping: 'PrivilegeType_id' },
					{ name: 'PrivilegeType_Name', mapping: 'PrivilegeType_Name' },
					{ name: 'ReceptDiscount_id', mapping: 'ReceptDiscount_id' },
					{ name: 'ReceptFinance_id', mapping: 'ReceptFinance_id' },
					{ name: 'PersonPrivilege_id', mapping: 'PersonPrivilege_id' },
					{ name: 'PersonPrivilege_IsClosed', mapping: 'PersonPrivilege_IsClosed' },
					{ name: 'PersonPrivilege_IsNoPfr', mapping: 'PersonPrivilege_IsNoPfr' },
					{ name: 'PersonPrivilege_IsPersonDisp', mapping: 'PersonPrivilege_IsPersonDisp' },
					{ name: 'PersonPrivilege_IsAddMZ' , mapping:'PersonPrivilege_IsAddMZ'},
					{ name: 'PersonRefuse_IsRefuse', mapping: 'PersonRefuse_IsRefuse' },
					{ name: 'WhsDocumentCostItemType_id', mapping: 'WhsDocumentCostItemType_id' }
				]),
				url: C_PRIVCAT_LOAD_LIST
			}),
			tpl: new Ext.XTemplate(
				'<tpl for="."><div class="x-combo-list-item">',
				'<table style="border: 0;"><tr><td style="width: 25px;"><font color="red">{PrivilegeType_VCode}</font></td><td style="font-weight: {[ values.PersonPrivilege_IsClosed == 1 ? "bold" : "normal; color: red;" ]};">{PrivilegeType_Name}{[ values.PersonPrivilege_IsClosed == 1 ? "&nbsp;" : " (закрыта)" ]}</td></tr></table>',
				'</div></tpl>'
			),
			sortInfo: {field: 'PrivilegeType_VCode'},
			listeners: {
				'change': function(combo, newValue, oldValue) {
					// Получаем запись, соответствующую выбранному значению
					var record = combo.getStore().getById(newValue);
					var base_form = this.findById('EvnReceptEditForm').getForm();
					var recept_finance_combo = base_form.findField('ReceptFinance_id');
					var recept_discount_combo = base_form.findField('ReceptDiscount_id');

					// Если запись не найдена
					if (!record) {
						recept_finance_combo.clearValue();
						recept_discount_combo.clearValue();
						// Прерываем выполнение метода
						return false;
					}

					base_form.findField('Drug_id').getStore().baseParams.PrivilegeType_id = newValue;
					base_form.findField('DrugMnn_id').getStore().baseParams.PrivilegeType_id = newValue;
					base_form.findField('DrugRequestMnn_id').getStore().baseParams.PrivilegeType_id = newValue;

					// установка скидки
					this.setReceptDiscount();

					// установка типа финансирования (ReceptFinance_id)
					this.setReceptFinance();

					if (this.action == 'add') {
						if (record.get('PrivilegeType_Code') == '253') {
							// резерв Да
							base_form.findField('DrugRequestRow_IsReserve').setValue(2);
							base_form.findField('DrugRequestRow_IsReserve').fireEvent('change', base_form.findField('DrugRequestRow_IsReserve'), base_form.findField('DrugRequestRow_IsReserve').getValue());
						} else {
							// резерв Нет
							base_form.findField('DrugRequestRow_IsReserve').setValue(1);
							base_form.findField('DrugRequestRow_IsReserve').fireEvent('change', base_form.findField('DrugRequestRow_IsReserve'), base_form.findField('DrugRequestRow_IsReserve').getValue());
						}
					}

					wnd.setWhsDocumentCostItemTypeFilter(function() {
						wnd.setWhsDocumentCostItemTypeDefaultValue();
					});
				}.createDelegate(this),
				load: function(s) {
					s.sortData('RlsClsntfr_Name');
				}
			},
			sortData: function() {
				var f = 'PrivilegeType_VCode';
				var direction = 'ASC';

				var fn = function(r1, r2){
					var
						ret = 0,
						v1 = r1.data[f],
						v2 = r2.data[f],
						t1 = !Ext.isEmpty(v1) && v1 != v1*1 ? 'string' : 'int',
						t2 = !Ext.isEmpty(v2) && v2 != v2*1 ? 'string' : 'int';

					if ( t1 == t2 ) {
						if (t1 == 'int') {
							v1 = v1*1;
							v2 = v2*1;
						} else {
							v1 = v1.toLowerCase();
							v2 = v2.toLowerCase();
						}

						ret = v1 > v2 ? 1 : (v1 < v2 ? -1 : 0)
					}
					else {
						ret = (t1 == 'int' ? -1 : 1)
					}

					return ret;
				};
				this.data.sort(direction, fn);
				if(this.snapshot && this.snapshot != this.data){
					this.snapshot.sort(direction, fn);
				}
			}
		});

		this.cost_type_combo = new sw.Promed.SwBaseLocalCombo({
			allowBlank: false,
			codeField: 'WhsDocumentCostItemType_Code',
			displayField: 'WhsDocumentCostItemType_Name',
			editable: false,
			fieldLabel: 'Программа ЛЛО',
			hiddenName: 'WhsDocumentCostItemType_id',
			lastQuery: '',
			tabIndex: TABINDEX_EREF + 13,
			validateOnBlur: true,
			valueField: 'WhsDocumentCostItemType_id',
			width: 517,
			store: new Ext.db.AdapterStore({
				autoLoad: false,
				dbFile: 'Promed.db',
				key: 'WhsDocumentCostItemType_id',
				fields: [
					{ name: 'WhsDocumentCostItemType_id', mapping: 'WhsDocumentCostItemType_id', type: 'int' },
					{ name: 'WhsDocumentCostItemType_Code', mapping: 'WhsDocumentCostItemType_Code', type: 'int' },
					{ name: 'WhsDocumentCostItemType_Name', mapping: 'WhsDocumentCostItemType_Name', type: 'string' },
					{ name: 'WhsDocumentCostItemType_Nick', mapping: 'WhsDocumentCostItemType_Nick', type: 'string' },
					{ name: 'WhsDocumentCostItemType_begDate', mapping: 'WhsDocumentCostItemType_begDate', type: 'date', dateFormat: 'd.m.Y' },
					{ name: 'WhsDocumentCostItemType_endDate', mapping: 'WhsDocumentCostItemType_endDate', type: 'date', dateFormat: 'd.m.Y' },
					{ name: 'WhsDocumentCostItemType_IsDlo', mapping: 'WhsDocumentCostItemType_IsDlo', type: 'int' },
					{ name: 'WhsDocumentCostItemType_IsDrugRequest', mapping: 'WhsDocumentCostItemType_IsDrugRequest', type: 'int' },
					{ name: 'DrugFinance_id', mapping: 'DrugFinance_id', type: 'int' },
					{ name: 'PersonRegisterType_id', mapping: 'PersonRegisterType_id', type: 'int' }
				],
				sortInfo: {
					field: 'WhsDocumentCostItemType_id'
				},
				tableName: 'WhsDocumentCostItemType'
			}),
			tpl: new Ext.XTemplate(
				'<tpl for="."><div class="x-combo-list-item">',
				'<font color="red">{WhsDocumentCostItemType_Code}</font>&nbsp;{WhsDocumentCostItemType_Name}',
				'</div></tpl>'
			),
			listeners: {
				'change': function(combo, newValue, oldValue) {
					combo.setLinkedFieldValues();
				},
				'select': function(combo, record) {
					combo.setLinkedFieldValues();
				}
			},
			setLinkedFieldValues: function() {
				var date = new Date();
				var base_form = wnd.findById('EvnReceptEditForm').getForm();
				var recept_valid_combo = base_form.findField('ReceptValid_id');
				var selected_data = wnd.getComboSelectedRecordData(this);
				var is_onls = (selected_data.WhsDocumentCostItemType_Nick == 'fl'); //fl - ОНЛС

				wnd.setDrugRequestFieldsVisible();
				wnd.setIsVzn();

				if (wnd.action != 'view' && is_onls) {
					//фильтрация источников финанасирования и установка значения по умолчанию
					wnd.drug_finance_combo.lastQuery = '';
					wnd.drug_finance_combo.getStore().clearFilter();
					wnd.drug_finance_combo.getStore().filterBy(function(rec) {
						if ( //так как фактически справочник почти не меняется, привязал проверку актуальности записей к текущей дате, а не дате рецепта
							rec.get('DrugFinance_SysNick').toString().inlist(['fed', 'reg']) &&
							(Ext.isEmpty(rec.get('DrugFinance_begDate')) || Date.parseDate(rec.get('DrugFinance_begDate'), 'd.m.Y') < date) &&
							(Ext.isEmpty(rec.get('DrugFinance_endDate')) || Date.parseDate(rec.get('DrugFinance_endDate'), 'd.m.Y') > date)
						) {
							return true;
						} else {
							return false;
						}
					});
					if (wnd.drug_finance_combo.getStore().getCount() > 0) {
						var default_drug_finance_id = !Ext.isEmpty(selected_data.DrugFinance_id) ? selected_data.DrugFinance_id : wnd.drug_finance_combo.getStore().getAt(0).get('DrugFinance_id');
						wnd.drug_finance_combo.setValue(default_drug_finance_id);
					} else {
						wnd.drug_finance_combo.setValue(null);
					}
					wnd.drug_finance_combo.setLinkedFieldValues();

					wnd.drug_finance_combo.enable();
				} else {
					wnd.drug_finance_combo.disable();
					wnd.drug_finance_combo.getStore().clearFilter();
					wnd.drug_finance_combo.setValue(!Ext.isEmpty(selected_data.DrugFinance_id) ? selected_data.DrugFinance_id : null);
				}

				if (wnd.action != 'view') {
					if (selected_data.WhsDocumentCostItemType_Nick == 'acs') { // программа ССЗ
						var index = recept_valid_combo.getStore().findBy(function(record) {
							return (record.get('ReceptValid_Code') == 10); //10 - 90 дней
						});
						if (index >= 0) {
							recept_valid_combo.setValue(recept_valid_combo.getStore().getAt(index).get('ReceptValid_id'));
							recept_valid_combo.disable();
						}
					} else {
						wnd.setReceptValidDefaultValue();
						recept_valid_combo.enable();
					}
				}

				base_form.findField('DrugMnn_id').getStore().baseParams.WhsDocumentCostItemType_id = this.getValue();
				base_form.findField('Drug_id').getStore().baseParams.WhsDocumentCostItemType_id = this.getValue();
			}
		});

		this.drug_finance_combo = new sw.Promed.SwCommonSprCombo({
			allowBlank: false,
			comboSubject: 'DrugFinance',
			disabled: true,
			fieldLabel: 'Тип финансирования',
			hiddenName: 'DrugFinance_id',
			tabIndex: TABINDEX_EREF + 14,
			width: 200,
			moreFields: [
				{name: 'DrugFinance_SysNick', mapping: 'DrugFinance_SysNick'},
				{name: 'DrugFinance_begDate', mapping: 'DrugFinance_begDate'},
				{name: 'DrugFinance_endDate', mapping: 'DrugFinance_endDate'}
			],
			listeners: {
				'change': function(combo, newValue, oldValue) {
					combo.setLinkedFieldValues();
				}
			},
			setLinkedFieldValues: function() {
				wnd.setReceptFinance();
			}
		});

		this.recept_finance_combo = new sw.Promed.SwCommonSprCombo({
			comboSubject: 'ReceptFinance',
			fieldLabel: 'Тип финансирования',
			hiddenName: 'ReceptFinance_id',
			lastQuery: '',
			width: 200,
			listWidth: 200,
			tabIndex: TABINDEX_EREF + 14,
			allowBlank: true,
			autoLoad: false,
			validateOnBlur: true,
			listeners: {
				'change': function(combo, newValue, oldValue) {
					combo.setLinkedFieldValues();
				}
			},
			setLinkedFieldValues: function() {
				var base_form = wnd.findById('EvnReceptEditForm').getForm();
				var recept_finance_id = this.getValue();
				var record = this.getStore().getById(recept_finance_id);

				base_form.findField('Drug_id').getStore().baseParams.ReceptFinance_Code = 0;
				base_form.findField('DrugMnn_id').getStore().baseParams.ReceptFinance_Code = 0;
				base_form.findField('DrugRequestMnn_id').getStore().baseParams.ReceptFinance_Code = 0;

				if (record && !Ext.isEmpty(recept_finance_id)) {
					var recept_finance_code = record.get('ReceptFinance_Code');

					base_form.findField('Drug_id').getStore().baseParams.ReceptFinance_Code = recept_finance_code;
					base_form.findField('DrugMnn_id').getStore().baseParams.ReceptFinance_Code = recept_finance_code;
					base_form.findField('DrugRequestMnn_id').getStore().baseParams.ReceptFinance_Code = recept_finance_code;
					base_form.findField('DrugProtoMnn_id').getStore().baseParams.ReceptFinance_id = recept_finance_id;
				}

				wnd.setDrugAllowedQuantity();
			}
		});

		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.doSave({
						checkDrugRequest: true,
						checkPersonAge: true,
						checkPersonDeadDT: true,
						checkPersonSnils: true,
						copy: false,
						print: false
					});
				}.createDelegate(this),
				iconCls: 'save16',
				testId: 'EREF_btn_Save',
				tabIndex: TABINDEX_EREF + 25,
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
				tabIndex: TABINDEX_EREF + 26,
				text: 'Копи<u>я</u>',
				tooltip: 'Копия рецепта'
			},
            {
                handler: function() {
                    //this.printRecept();
                    var base_form = this.findById('EvnReceptEditForm').getForm();

                    var privilege_type_combo = base_form.findField('PrivilegeType_id');
                    var recept_finance_combo = base_form.findField('ReceptFinance_id');

                    var person_id = base_form.findField('Person_id').getValue();
                    var diag_id = base_form.findField('Diag_id').getValue();
                    var medstafffact_id = this.findById('EREF_MedStaffFactCombo').getValue();
                    var privilege_type_id = privilege_type_combo.getValue();
                    var recept_finance_id = recept_finance_combo.getValue();

                    if(Ext.isEmpty(diag_id) || Ext.isEmpty(medstafffact_id) || Ext.isEmpty(recept_finance_id) || Ext.isEmpty(privilege_type_id)){
                        sw.swMsg.show({
                            buttons: Ext.Msg.OK,
                            icon: Ext.Msg.WARNING,
                            msg: 'Поля ""Врач", Диагноз", "Тип финансирования" и "Категория" должны быть заполнены!',
                            title: "Ошибка открытия формы"
                        });
                    }
                    else{
                        getWnd('swDrugRequestDopAddWindow').show({
                                person_id: person_id,
                                medstafffact_id: medstafffact_id,
                                diag_id: diag_id,
                                recept_finance_id: recept_finance_id,
                                privilege_type_id: privilege_type_id
                            }
                        );
                    }
                }.createDelegate(this),
                testId: 'EREW_DRDAW',
                hidden: !(getGlobalOptions().region.nick == 'perm'),
                tabIndex: TABINDEX_EREF + 27,
                text: '<u>Д</u>ополнительная заявка',
                tooltip: 'Добавть дополнительную заявку'
            },
            {
				handler: function() {
					this.printRecept();
				}.createDelegate(this),
				iconCls: 'print16',
				testId: 'EREF_btn_Print',
				tabIndex: TABINDEX_EREF + 28,
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
				tabIndex: TABINDEX_EREF + 29,
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

					wnd.privilege_type_combo.getStore().load({
						callback: function(records, options, success) {
							// фильтрация списка
							wnd.setPrivilegeTypeFilter();

							if (!wnd.privilege_type_combo.disabled) {
								wnd.privilege_type_combo.focus(false);
							} else {
								base_form.findField('EvnRecept_setDate').focus(false);
							}
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
									var base_form = this.findById('EvnReceptEditForm').getForm();

									this.setReceptFormFilter();
									this.setReceptValidFilter();

									var lpu_section_combo = base_form.findField('LpuSection_id');
									var med_personal_combo = base_form.findField('MedPersonal_rid');
									var med_staff_fact_combo = base_form.findField('MedStaffFact_id');
									var privilege_type_combo = base_form.findField('PrivilegeType_id');
									var recept_finance_combo = base_form.findField('ReceptFinance_id');
									var recept_num_field = base_form.findField('EvnRecept_Num');
									var recept_ser_field = base_form.findField('EvnRecept_Ser');
									var recept_type_combo = base_form.findField('ReceptType_id');

									// Сохраняем старые значения полей
									var lpu_section_id = lpu_section_combo.getValue();
									var med_personal_rid = med_personal_combo.getValue();
									var med_staff_fact_id = med_staff_fact_combo.getValue();
									var privilege_type_id = privilege_type_combo.getValue();

									lpu_section_combo.clearValue();
									lpu_section_combo.getStore().removeAll();
									med_personal_combo.clearValue();
									med_personal_combo.getStore().removeAll();
									med_staff_fact_combo.clearValue();
									med_staff_fact_combo.getStore().removeAll();
									recept_finance_combo.clearValue();
									privilege_type_combo.getStore().clearFilter();
									privilege_type_combo.getStore().removeAll();

									if ( !newValue ) {
										lpu_section_combo.disable();
										med_personal_combo.disable();
										med_staff_fact_combo.disable();
										recept_finance_combo.disable();

										recept_finance_combo.fireEvent('change', recept_finance_combo, null, 1);

										if ( this.findById('EREF_DrugRequestOtovPanel').wasExpanded === true ) {
											this.findById('EREF_DrugRequestOtovGrid').getStore().removeAll();
										}

										return false;
									}

									wnd.setDrugAllowedQuantity();

									// Устанавливаем значения даты для списков, зависящих от даты выписки рецепта
									this.findById('EREF_DrugRequestOtovGrid').getStore().baseParams.Date = Ext.util.Format.date(newValue, 'd.m.Y');
									this.findById('EREF_DrugRequestRowGrid').getStore().baseParams.Date = Ext.util.Format.date(newValue, 'd.m.Y');
									base_form.findField('Drug_id').getStore().baseParams.Date = Ext.util.Format.date(newValue, 'd.m.Y');
									base_form.findField('DrugMnn_id').getStore().baseParams.Date = Ext.util.Format.date(newValue, 'd.m.Y');
									base_form.findField('DrugRequestMnn_id').getStore().baseParams.Date = Ext.util.Format.date(newValue, 'd.m.Y');
									privilege_type_combo.getStore().baseParams.Date = Ext.util.Format.date(newValue, 'd.m.Y');

									if ( this.findById('EREF_DrugRequestOtovPanel').wasExpanded === true ) {
										this.findById('EREF_DrugRequestOtovGrid').getStore().load();
									}

									lpu_section_combo.enable();
									med_personal_combo.enable();
									med_staff_fact_combo.enable();
									recept_finance_combo.enable();

									// Загружаем список льгот человека
									wnd.privilege_type_combo.getStore().load({
										callback: function(records, options, success) {
											// фильтрация списка
											wnd.setPrivilegeTypeFilter();

											var idx = -1;
											if (!Ext.isEmpty(privilege_type_id)) {
												var idx = wnd.privilege_type_combo.getStore().findBy(function(record) {
													return (record.get('PrivilegeType_id') == privilege_type_id);
												});
											}

											if (idx > -1) {
												wnd.privilege_type_combo.setValue(privilege_type_id);
												wnd.privilege_type_combo.fireEvent('change', wnd.privilege_type_combo, privilege_type_id);
											} else {
												// установка значения по умолчанию
												wnd.setPrivilegeTypeDefaultValue();
											}
										}.createDelegate(this)
									});

									var section_filter_params = {
										allowLowLevel: 'yes',
										isDlo: true,
										onDate: Ext.util.Format.date(newValue, 'd.m.Y')
									};

									var medstafffact_filter_params = {
										allowLowLevel: 'yes',
										isDlo: true,
										onDate: Ext.util.Format.date(newValue, 'd.m.Y')
									};

									if ( getGlobalOptions().region ) {
										medstafffact_filter_params.regionCode = getGlobalOptions().region.number;
										section_filter_params.regionCode = getGlobalOptions().region.number;
									}

									if ( this.action == 'add' ) {
										// Фильтр на конкретное место работы
										if ( !Ext.isEmpty(this.userMedStaffFact.LpuSection_id) && !Ext.isEmpty(this.userMedStaffFact.MedStaffFact_id) ) {
											section_filter_params.id = this.userMedStaffFact.LpuSection_id;
											medstafffact_filter_params.id = this.userMedStaffFact.MedStaffFact_id;
										}
									}

									// Фильтруем список отделений на выбранную дату
									setLpuSectionGlobalStoreFilter(section_filter_params);

									// Фильтруем список врачей на выбранную дату
									setMedStaffFactGlobalStoreFilter(medstafffact_filter_params);

									// Загружаем список отделений
									lpu_section_combo.getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));
									// Загружаем список врачей
									med_staff_fact_combo.getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));

									// Ищем в списках записи, соответствующие старым значениям
									var lpu_section_record = lpu_section_combo.getStore().getById(lpu_section_id);
									var med_staff_fact_record = med_staff_fact_combo.getStore().getById(med_staff_fact_id);

									// Если запись не найдена
									if ( !lpu_section_record ) {
										// значение комбо не устанавливается, вызывается снятие фильтра по отделению со списка врачей
										lpu_section_combo.fireEvent('change', lpu_section_combo, -1, lpu_section_id);
									}
									else {
										// устанавливается старое значение комбо
										lpu_section_combo.setValue(lpu_section_id);
									}

									// Если запись не найдена
									if ( !med_staff_fact_record ) {
										// значение комбо не устанавливается, вызывается снятие фильтра по отделению со списка врачей
										med_staff_fact_combo.fireEvent('change', med_staff_fact_combo, -1, med_staff_fact_id);
									}
									else {
										// устанавливается старое значение комбо
										med_staff_fact_combo.setValue(med_staff_fact_id);
									}
									//log([med_staff_fact_id, med_staff_fact_record, swMedStaffFactGlobalStore.getCount(), swMedStaffFactGlobalStore]);

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

									if (!Ext.isEmpty(base_form.findField('Lpu_rid').getValue())) {
										// Загружаем список врачей, которые заявляли медикаменты на пациента
										med_personal_combo.getStore().load({
											callback: function () {
												wnd.setMedPersonalValue(med_personal_combo, med_personal_rid);
											}.createDelegate(this),
											params: {
												Date: Ext.util.Format.date(newValue, 'd.m.Y'),
												Lpu_rid: base_form.findField('Lpu_rid').getValue(),
												Person_id: base_form.findField('Person_id').getValue()
											}
										});
									}

									// фильтрация списка диагнозов
									wnd.setDiagFilter();

									var recept_type_id = recept_type_combo.getValue();
									var recept_type_idx = recept_type_combo.getStore().findBy(function(rec) { return (rec.get('ReceptType_id') == recept_type_id) });
									var recept_type_code = null;
									if (recept_type_idx > -1) {
										recept_type_code = recept_type_combo.getStore().getAt(recept_type_idx).get('ReceptType_Code');
									}

									if (recept_type_code == 1) {
										recept_num_field.enable();
										recept_ser_field.enable();
										if (this.action == 'add') {
											recept_num_field.setValue(null);
											recept_ser_field.setValue(null);
										}
									} else if (recept_type_code == 2 || recept_type_code == 3) {
										recept_num_field.disable();
										recept_ser_field.disable();
										this.setReceptNumber();
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
                                    var recept_num_field = base_form.findField('EvnRecept_Num');
                                    var recept_ser_field = base_form.findField('EvnRecept_Ser');
                                    var recept_type_combo = base_form.findField('ReceptType_id');

                                    if (newValue == 2){
                                        base_form.findField('Drug_IsMnn').setValue(1);
                                        base_form.findField('EvnRecept_Signa').disable();
                                        base_form.findField('EvnRecept_Signa').setAllowBlank(true);
                                    }
                                    else{
                                        base_form.findField('Drug_IsMnn').setValue(2);
                                        base_form.findField('EvnRecept_Signa').enable();
                                        base_form.findField('EvnRecept_Signa').setAllowBlank(false);
                                    }

									wnd.setReceptValidFilter();

                                    var recept_type_id = recept_type_combo.getValue();
                                    var recept_type_idx = recept_type_combo.getStore().findBy(function(rec) { return (rec.get('ReceptType_id') == recept_type_id) });
                                    var recept_type_code = null;
                                    if ( recept_type_idx > -1 ) {
                                        recept_type_code = recept_type_combo.getStore().getAt(recept_type_idx).get('ReceptType_Code');
                                    }

                                    if ( recept_type_code == 1 ) {
                                        recept_num_field.enable();
                                        recept_ser_field.enable();
                                        if (this.action == 'add') {
                                            recept_num_field.setValue(null);
                                            recept_ser_field.setValue(null);
                                        }
                                    } else if ( recept_type_code == 2 || recept_type_code == 3 ) {
                                        recept_num_field.disable();
                                        recept_ser_field.disable();
                                        this.setReceptNumber();
                                    }

									this.setVKProtocolFieldsVisible();
                                    this.setReceptTypeFilter();
									if (getRegionNick() != 'kz') {
										var ReceptUrgencyCombo = base_form.findField('ReceptUrgency_id');
										if (newValue == 9) {
											base_form.findField('ReceptUrgency_id').enable();
											ReceptUrgencyCombo.showContainer();
										} else {
											ReceptUrgencyCombo.hideContainer();
											ReceptUrgencyCombo.clearValue();
											base_form.findField('ReceptUrgency_id').disable();
										}
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

									base_form.findField('Drug_id').getStore().baseParams.is_mi_1 = (combo.getValue() == 2) ? true : false;

									this.setDrugRequestFieldsVisible(); //метод также влючает всебя перезагрузку содержимого поля "Заявка"
                                }.createDelegate(this)
                            },
                            store: new Ext.data.Store({
                                autoLoad: false,
                                reader: new Ext.data.JsonReader({
                                    id: 'ReceptForm_id'
                                }, [
                                    { name: 'ReceptForm_id', mapping: 'ReceptForm_id', type: 'int', hidden: 'true'},
                                    { name: 'ReceptForm_Code', mapping: 'ReceptForm_Code'},
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
                        {
						allowBlank: false,
						fieldLabel: 'Тип рецепта',
						hiddenName: 'ReceptType_id',
						listeners: {
							'change': function(combo, newValue, oldValue) {
								var base_form = this.findById('EvnReceptEditForm').getForm();
								if(newValue == 2 || (newValue == 3 && base_form.findField('EvnRecept_IsSigned').getValue() == 2))
									this.buttons[3].show();
								else
									this.buttons[3].hide();
								if(getWnd('swWorkPlaceMZSpecWindow').isVisible())
									this.buttons[3].hide();
								var recept_num_field = base_form.findField('EvnRecept_Num');
								var recept_ser_field = base_form.findField('EvnRecept_Ser');
								var signa_field = base_form.findField('EvnRecept_Signa');

								recept_num_field.setRawValue('');

								var recept_type_code = 0;

								if ( !newValue ) {
									recept_type_code = 1;

									var index = combo.getStore().findBy(function(rec) {
										if ( Number(rec.get('ReceptType_Code')) == recept_type_code ) {
											return true;
										}
										else {
											return false;
										}
									});

									if ( index >= 0 ) {
										combo.setValue(combo.getStore().getAt(index).get(combo.valueField));
									}
									else {
										return false;
									}
								}
								else {
									var record = combo.getStore().getById(newValue);

									if ( record ) {
										recept_type_code = Number(record.get('ReceptType_Code'));
									}
								}

								base_form.findField('Drug_id').getStore().baseParams.ReceptType_Code = recept_type_code;
								base_form.findField('DrugMnn_id').getStore().baseParams.ReceptType_Code = recept_type_code;
								base_form.findField('DrugRequestMnn_id').getStore().baseParams.ReceptType_Code = recept_type_code;
								/*
								// Формируем серию рецепта
								var lpu_id = Ext.globalOptions.globals.lpu_id;

								var lpu_store = new Ext.db.AdapterStore({
									autoLoad: false,
									dbFile: 'Promed.db',
									fields: [
										{ name: 'Lpu_id', type: 'int' },
										{ name: 'Lpu_Ouz', type: 'int' },
										{ name: 'Lpu_RegNomC2', type: 'int' },
										{ name: 'Lpu_RegNomN2', type: 'int' }
									], 
									key: 'Lpu_id',
									tableName: 'Lpu'
								});
								*/

								// if ( recept_type_code == 1 ) {
								// 	recept_num_field.enable();
								// 	recept_ser_field.enable();
								// 	/*signa_field.disable();
								// 	signa_field.setAllowBlank(true);
								// 	signa_field.setRawValue('');*/
								// }
								// else {
								// 	recept_num_field.disable();
								// 	recept_ser_field.disable();
								// 	/*signa_field.enable();
								// 	signa_field.setAllowBlank(false);*/
								// 	this.setReceptNumber();
								// }
								/*
								lpu_store.load({
									callback: function(records, options, success) {
										var evn_recept_ser = '';

										if ( lpu_store.getCount() > 0 ) {
											evn_recept_ser = lpu_store.getAt(0).get('Lpu_Ouz');
										}

										recept_ser_field.setValue(evn_recept_ser);
									},
									params: {
										where: "where Lpu_id = " + lpu_id
									}
								});
								*/

								if (recept_type_code == 1) {
									recept_num_field.enable();
									recept_ser_field.enable();
									if (this.action == 'add') {
                                        recept_num_field.setValue(null);
                                    	recept_ser_field.setValue(null);
									}
								} else if (recept_type_code == 2 || recept_type_code == 3) {
									recept_num_field.disable();
									recept_ser_field.disable();
									this.setReceptNumber();
								}
							}.createDelegate(this),
							'keydown': function (inp, e) {
								if ( e.shiftKey == true && e.getKey() == Ext.EventObject.TAB ) {
									e.stopEvent();
									this.buttons[this.buttons.length - 1].focus();
								}
							}.createDelegate(this)
						},
						tabIndex: TABINDEX_EREF + 1,
						validateOnBlur: true,
						xtype: 'swrecepttypecombo'
					}, {
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
									maxLength: '7'
								},
								fieldLabel: 'Серия',
								name: 'EvnRecept_Ser',
								tabIndex: TABINDEX_EREF + 3,
								validateOnBlur: true,
								//xtype: 'numberfield',
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
									maxLength: 8,
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
						listeners: {
							'render': function(combo) {
								combo.getStore().load();
							}
						},
						tabIndex: TABINDEX_EREF + 5,
						validateOnBlur: true,
						value: 2,
						xtype: 'swcommonsprcombo'
					},
					new sw.Promed.SwLpuSectionGlobalCombo({
						allowBlank: false,
						id: 'EREF_LpuSectionCombo',
						lastQuery: '',
						linkedElements: [
							'EREF_MedStaffFactCombo'
						],
						listWidth: 700,
						tabIndex: TABINDEX_EREF + 6,
						validateOnBlur: true,
						width: 517,
						listeners: {
							change: function() {
								wnd.setReceptNumber();
							}
						}
					}),
					new sw.Promed.SwMedStaffFactGlobalCombo({
						allowBlank: false,
						id: 'EREF_MedStaffFactCombo',
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
					items: [
						this.privilege_type_combo,
						this.cost_type_combo,
						{
							border: false,
							layout: 'column',
							items: [{
								border: false,
								hidden: true,
								layout: 'form',
								items: [this.recept_finance_combo]
							}, {
								border: false,
								layout: 'form',
								items: [this.drug_finance_combo]
							}, {
								border: false,
								labelWidth: 93,
								layout: 'form',
								items: [{
									disabled: true,
									fieldLabel: 'Скидка',
									hiddenName: 'ReceptDiscount_id',
									listWidth: 100,
									tabIndex: TABINDEX_EREF + 15,
									validateOnBlur: true,
									width: 100,
									xtype: 'swreceptdiscountcombo',
									listeners: {
										'select': function(combo, record, index) {
											combo.setRawValue(record.get('ReceptDiscount_Code') + ". " + record.get('ReceptDiscount_Name'));
										}
									}
								}]
							}]
						}
					]
				}),
				new sw.Promed.Panel({
					bodyStyle: 'padding: 2px;',
					border: true,
					collapsible: true,
					height: 135,
					id: 'EREF_DrugRequestOtovPanel',
					wasExpanded: false,
					layout: 'border',
					listeners: {
						'expand': function(panel) {
							if ( panel.wasExpanded === false ) {
								panel.wasExpanded = true;

								if ( this.findById('EvnReceptEditForm').getForm().findField('EvnRecept_setDate').getValue() ) {
									panel.findById('EREF_DrugRequestOtovGrid').getStore().load();
								}
								else {
									panel.findById('EREF_DrugRequestOtovGrid').getStore().removeAll();
								}
							}
							panel.doLayout();
						}.createDelegate(this)
					},
					style: 'margin-bottom: 0.5em;',
					title: '3. Соответствие выписки и заявки',
					items: [ new Ext.grid.GridPanel({
						autoExpandColumn: 'autoexpand_drug_request_name',
						autoExpandMin: 200,
						border: true,
						columns: [{
							dataIndex: 'Lpu_Nick',
							header: 'ЛПУ заявки',
							hidden: false,
							resizable: true,
							sortable: true,
							width: 200
						}, {
							dataIndex: 'MedPersonal_Fio',
							header: 'Врач заявки',
							hidden: false,
							resizable: true,
							sortable: true,
							width: 200
						}, {
							dataIndex: 'DrugRequestRow_Name',
							header: 'Медикамент',
							hidden: false,
							id: 'autoexpand_drug_request_name',
							resizable: true,
							sortable: true
						}, {
							dataIndex: 'DrugRequestRow_Kolvo',
							header: 'Заявленное количество',
							hidden: false,
							resizable: true,
							sortable: true,
							width: 100
						}, {
							dataIndex: 'ER_Lpu_Nick',
							header: 'ЛПУ выписки рецепта',
							hidden: false,
							resizable: true,
							sortable: true,
							width: 200
						}, {
							dataIndex: 'ER_MedPersonal_Fio',
							header: 'Врач, выписавший рецепт',
							hidden: false,
							resizable: true,
							sortable: true,
							width: 200
						}, {
							dataIndex: 'EvnRecept_Kolvo',
							header: 'Выписанное количество',
							hidden: false,
							resizable: true,
							sortable: true,
							width: 100
						}],
						frame: false,
						height: 150,
						id: 'EREF_DrugRequestOtovGrid',
						keys: [{
							key: [
								Ext.EventObject.END,
								Ext.EventObject.HOME,
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

								var grid = Ext.getCmp('EREF_DrugRequestOtovGrid');

								switch ( e.getKey() ) {
									case Ext.EventObject.END:
										GridEnd(grid);
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
										var base_form = this.findById('EvnReceptEditForm').getForm();

										grid.getSelectionModel().clearSelections();
										grid.getSelectionModel().fireEvent('rowselect', grid.getSelectionModel());

										if ( e.shiftKey == false ) {
											if ( !this.findById('EREF_DrugPanel').collapsed && this.action != 'view' ) {
												base_form.findField('Drug_IsMnn').focus(true);
											}
											else if ( this.action != 'view' ) {
												this.buttons[0].focus();
											}
											else {
												this.buttons[1].focus();
											}
										}
										else {
											if ( !base_form.findField('DrugRequestRow_IsReserve').disabled ) {
												base_form.findField('DrugRequestRow_IsReserve').focus(true);
											}
											else if ( !base_form.findField('MedPersonal_rid').disabled ) {
												base_form.findField('MedPersonal_rid').focus(true);
											}
											else if ( !this.findById('EREF_DrugRequestRowPanel').collapsed && !base_form.findField('MedPersonal_rid').disabled ) {
												base_form.findField('DrugRequestRow_IsReserve').focus(true);
											}
											else if ( !this.findById('EREF_PrivilegePanel').collapsed && !base_form.findField('PrivilegeType_id').disabled ) {
												base_form.findField('PrivilegeType_id').focus(true);
											}
											else if ( !this.findById('EREF_PrivilegePanel').collapsed && !base_form.findField('ReceptFinance_id').disabled ) {
												base_form.findField('ReceptFinance_id').focus(true);
											}
											else if ( this.action != 'view' ) {
												base_form.findField('Diag_id').focus(true);
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
						loadMask: true,
						region: 'center',
						sm: new Ext.grid.RowSelectionModel({
							listeners: {
								'rowselect': function(sm, rowIndex, record) {
									//
								}
							}
						}),
						stripeRows: true,
						store: new Ext.data.Store({
							autoLoad: false,
							listeners: {
								'load': function(store, records, index) {
									if ( store.getCount() == 0 ) {
										LoadEmptyRow(this.findById('EREF_DrugRequestRowGrid'));
									}
								}.createDelegate(this)
							},
							reader: new Ext.data.JsonReader({
								id: 'ERDRR_id'
							}, [{
								mapping: 'ERDRR_id',
								name: 'ERDRR_id',
								type: 'int'
							}, {
								mapping: 'DrugRequestRow_id',
								name: 'DrugRequestRow_id',
								type: 'int'
							}, {
								mapping: 'Lpu_Nick',
								name: 'Lpu_Nick',
								type: 'string'
							}, {
								mapping: 'MedPersonal_Fio',
								name: 'MedPersonal_Fio',
								type: 'string'
							}, {
								mapping: 'DrugRequestRow_Name',
								name: 'DrugRequestRow_Name',
								type: 'string'
							}, {
								mapping: 'DrugRequestRow_Kolvo',
								name: 'DrugRequestRow_Kolvo',
								type: 'float'
							}, {
								mapping: 'ER_Lpu_Nick',
								name: 'ER_Lpu_Nick',
								type: 'string'
							}, {
								mapping: 'ER_MedPersonal_Fio',
								name: 'ER_MedPersonal_Fio',
								type: 'string'
							}, {
								mapping: 'EvnRecept_Kolvo',
								name: 'EvnRecept_Kolvo',
								type: 'float'
							}]),
							url: '/?c=EvnRecept&m=loadDrugRequestOtovGrid'
						})
					})]
				}),
				new sw.Promed.Panel({
					bodyStyle: 'padding: 2px;',
					border: true,
					collapsible: true,
					autoHeight: true,
					id: 'EREF_DrugRequestRowPanel',
					isLoaded: false,
					layout: 'form',
					listeners: {
						'expand': function(panel) {
							panel.doLayout();
						}.createDelegate(this)
					},
					style: 'margin-bottom: 0.5em;',
					title: '4. Заявка',
					items: [{
						bodyStyle: 'padding-top: 0.5em;',
						border: false,
						autoHeight: true,
						layout: 'form',
						region: 'center',

						items: [{
							allowBlank: false,
							fieldLabel: 'Резерв',
							hiddenName: 'DrugRequestRow_IsReserve',
							listeners: {
								'change': function(combo, newValue, oldValue) {
									var base_form = this.findById('EvnReceptEditForm').getForm();

									base_form.findField('Drug_id').clearValue();
									base_form.findField('Drug_id').getStore().removeAll();

									// Устанавливаем значения параметра DrugRequestRow_IsReserve для списков, зависящих от признака "Выписка из резерва"
									this.findById('EREF_DrugRequestRowGrid').getStore().baseParams.DrugRequestRow_IsReserve = newValue;
									var recept_finance_id = base_form.findField('ReceptFinance_id').getValue();
									this.findById('EREF_DrugRequestRowGrid').getStore().baseParams.ReceptFinance_id = recept_finance_id;
									base_form.findField('Drug_id').getStore().baseParams.DrugRequestRow_IsReserve = newValue;
									base_form.findField('DrugMnn_id').getStore().baseParams.DrugRequestRow_IsReserve = newValue;
									base_form.findField('DrugRequestMnn_id').getStore().baseParams.DrugRequestRow_IsReserve = newValue;

									if (!Ext.isEmpty(newValue) && newValue == 2) {
										// поле МО видимо и доступно, если резерв = ДА
										base_form.findField('Lpu_rid').enable();
										base_form.findField('Lpu_rid').showContainer();
										base_form.findField('MedPersonal_rid').enable();
										base_form.findField('MedPersonal_rid').showContainer();
										base_form.findField('DrugProtoMnn_id').enable();
										base_form.findField('DrugProtoMnn_id').showContainer();
										if (!Ext.isEmpty(getGlobalOptions().lpu_id)) {
											var index = base_form.findField('Lpu_rid').getStore().findBy(function (rec) {
												if (rec.get('Lpu_id') == getGlobalOptions().lpu_id) {
													return true;
												}

												return false;
											});

											if ( index >= 0 ) {
												base_form.findField('Lpu_rid').setValue(getGlobalOptions().lpu_id);
												this.findById('EREF_DrugRequestRowGrid').getStore().baseParams.CurrentLpu_id = getGlobalOptions().lpu_id;
											}
										}
									} else {
										base_form.findField('Lpu_rid').disable();
										base_form.findField('Lpu_rid').hideContainer();
										base_form.findField('Lpu_rid').clearValue();
										base_form.findField('MedPersonal_rid').disable();
										base_form.findField('MedPersonal_rid').hideContainer();
										base_form.findField('MedPersonal_rid').clearValue();
										base_form.findField('DrugProtoMnn_id').disable();
										base_form.findField('DrugProtoMnn_id').hideContainer();
										base_form.findField('DrugProtoMnn_id').clearValue();
									}

									base_form.findField('Lpu_rid').fireEvent('change', base_form.findField('Lpu_rid'), base_form.findField('Lpu_rid').getValue(), null);
									base_form.findField('MedPersonal_rid').fireEvent('change', base_form.findField('MedPersonal_rid'), base_form.findField('MedPersonal_rid').getValue(), null);
									base_form.findField('DrugProtoMnn_id').fireEvent('change', base_form.findField('DrugProtoMnn_id'), base_form.findField('DrugProtoMnn_id').getValue(), null);

									// Вызываем загрузку списков
									this.loadDrugRequestRowGrid();
									this.loadDrugRequestMnnCombo();
								}.createDelegate(this)
							},
							tabIndex: TABINDEX_EREF + 12,
							value: 1,
							width: 80,
							xtype: 'swyesnocombo'
						}, {
							fieldLabel: 'МО',
							hiddenName: 'Lpu_rid',
							listeners: {
								'change': function(combo, newValue, oldValue) {
									var base_form = this.findById('EvnReceptEditForm').getForm();

									var med_personal_combo = base_form.findField('MedPersonal_rid');
									var med_staff_fact_combo = base_form.findField('MedStaffFact_id');

									var med_personal_rid = med_personal_combo.getValue();
									var med_staff_fact_id = med_staff_fact_combo.getValue();

									// Загружаем список врачей из выбранной ЛПУ, которые заявляли медикаменты на пациента
									if (!Ext.isEmpty(newValue) && newValue > 0) {
										med_personal_combo.getStore().load({
											callback: function () {
												wnd.setMedPersonalValue(med_personal_combo, med_personal_rid);
											}.createDelegate(this),
											params: {
												Date: Ext.util.Format.date(base_form.findField('EvnRecept_setDate').getValue(), 'd.m.Y'),
												Lpu_rid: newValue,
												Person_id: base_form.findField('Person_id').getValue()
											}
										});
									} else {
										med_personal_combo.clearValue();
										med_personal_combo.getStore().removeAll();
									}
								}.createDelegate(this)
							},
							listWidth: 600,
							tabIndex: TABINDEX_EREF + 13,
							width: 517,
							xtype: 'swlpucombo'
						}, {
							codeField: 'MedPersonal_TabCode',
							displayField: 'MedPersonal_Fio',
							editable: true,
							enableKeyEvents: true,
							fieldLabel: 'По заявке врача',
							hiddenName: 'MedPersonal_rid',
							lastQuery: '',
							listeners: {
								'change': function(combo, newValue, oldValue) {
									var base_form = this.findById('EvnReceptEditForm').getForm();

									base_form.findField('Drug_id').clearValue();
									base_form.findField('Drug_id').getStore().removeAll();

									// Устанавливаем значения параметра MedPersonal_id для списков, зависящих от выбранного врача
									this.findById('EREF_DrugRequestRowGrid').getStore().baseParams.MedPersonal_id = newValue;
									base_form.findField('Drug_id').getStore().baseParams.MedPersonal_id = newValue;
									base_form.findField('DrugMnn_id').getStore().baseParams.MedPersonal_id = newValue;
									base_form.findField('DrugRequestMnn_id').getStore().baseParams.MedPersonal_id = newValue;
									var recept_finance_id = base_form.findField('ReceptFinance_id').getValue();
									this.findById('EREF_DrugRequestRowGrid').getStore().baseParams.ReceptFinance_id = recept_finance_id;
									this.findById('EREF_DrugRequestRowGrid').getStore().baseParams.CurrentLpu_id = base_form.findField('Lpu_rid').getValue();

									if (!Ext.isEmpty(newValue) && newValue > 0) {
										base_form.findField('DrugProtoMnn_id').clearValue();
										base_form.findField('DrugProtoMnn_id').disable();
									} else {
										base_form.findField('DrugProtoMnn_id').enable();
									}

									// Вызываем загрузку списков
									wnd.loadDrugRequestRowGrid();
									wnd.loadDrugRequestMnnCombo();
								}.createDelegate(this)
							},
							listWidth: 700,
							store: new Ext.data.Store({
								reader: new Ext.data.JsonReader({
									id: 'MedPersonal_id'
								}, [
									{ name: 'MedPersonal_id', mapping: 'MedPersonal_id' },
									{ name: 'MedPersonal_IsMain', mapping: 'MedPersonal_IsMain', type: 'int' },
									{ name: 'MedPersonal_DloCode', mapping: 'MedPersonal_DloCode' },
									{ name: 'MedPersonal_TabCode', mapping: 'MedPersonal_TabCode' },
									{ name: 'MedPersonal_Fio', mapping: 'MedPersonal_Fio' },
									{ name: 'MedPersonal_IsRequest', mapping: 'MedPersonal_IsRequest' },
									{ name: 'MedPersonal_ReserveEnable', mapping: 'MedPersonal_ReserveEnable' }
								]),
								url: '/?c=EvnRecept&m=loadDrugRequestMedPersonalList'
							}),
							tabIndex: TABINDEX_EREF + 14,
							tpl: new Ext.XTemplate(
								'<tpl for="."><div class="x-combo-list-item">',
								'<table style="border: 0;">',
								'<td style="width: 45px;"><font color="red">{MedPersonal_TabCode}&nbsp;</font></td>',
								'<td style="width: 45px;">{MedPersonal_DloCode}&nbsp;</td>',
								'<td style="font-weight: bold;">{MedPersonal_Fio}&nbsp;</td>',
								'</tr></table>',
								'</div></tpl>'
							),
							triggerAction: 'all',
							valueField: 'MedPersonal_id',
							width: 517,
							xtype: 'swbaselocalcombo'
						}, {
							width: 517,
							fieldLabel: 'Медикамент',
							allowBlank: true,
							forceSelection: true,
							hiddenName: 'DrugProtoMnn_id',
							xtype: 'swdrugprotomnnsimplecombo',
							tabIndex: TABINDEX_EREF + 14,
							loadingText: 'Идет поиск...',
							minLengthText: 'Поле должно быть заполнено',
							queryDelay: 250,
							listeners:
							{
								'change': function(combo, newValue)
								{
									var base_form = wnd.findById('EvnReceptEditForm').getForm();
									combo.getStore().baseParams.DrugProtoMnn_id = '';

									// Вызываем загрузку списков
									wnd.findById('EREF_DrugRequestRowGrid').getStore().baseParams.DrugProtoMnn_id = newValue;
									base_form.findField('Drug_id').getStore().baseParams.DrugProtoMnn_id = newValue;
									base_form.findField('DrugMnn_id').getStore().baseParams.DrugProtoMnn_id = newValue;

									wnd.loadDrugRequestRowGrid();
									wnd.loadDrugRequestMnnCombo();
								},
								'keydown': function(inp, e)
								{
									if (e.getKey() == Ext.EventObject.DELETE || e.getKey() == Ext.EventObject.F4)
									{
										e.stopEvent();
										if (e.browserEvent.stopPropagation)
										{
											e.browserEvent.stopPropagation();
										}
										else
										{
											e.browserEvent.cancelBubble = true;
										}
										if (e.browserEvent.preventDefault)
										{
											e.browserEvent.preventDefault();
										}
										else
										{
											e.browserEvent.returnValue = false;
										}

										e.returnValue = false;

										if (Ext.isIE)
										{
											e.browserEvent.keyCode = 0;
											e.browserEvent.which = 0;
										}
										switch (e.getKey())
										{
											case Ext.EventObject.DELETE:
												inp.clearValue();
												inp.setRawValue(null);
												break;
											case Ext.EventObject.F4:
												inp.onTrigger2Click();
												break;
										}
									}
								}
							},
							onTrigger2Click: function()
							{
								return false;
							}
						}]
					}, {
						layout: 'border',
						border: false,
						height: 150,
						items: [
							new Ext.grid.GridPanel({
								autoExpandColumn: 'autoexpand_drug_request',
								autoExpandMin: 200,
								border: true,
								columns: [
									{
										dataIndex: 'DrugRequestPeriod_Name',
										header: 'Период заявки',
										hidden: false,
										resizable: true,
										sortable: true
									},
									{
										dataIndex: 'DrugRequestRow_Name',
										header: 'Медикамент',
										hidden: false,
										id: 'autoexpand_drug_request',
										resizable: true,
										sortable: true
									}, {
										dataIndex: 'DrugRequestRow_Kolvo',
										header: 'Количество',
										hidden: false,
										resizable: false,
										sortable: true,
										width: 100
									}, {
										dataIndex: 'DrugRequestRow_Price',
										header: 'Цена',
										hidden: false,
										resizable: false,
										sortable: true,
										width: 100
									}, {
										dataIndex: 'DrugRequestRow_Summa',
										header: 'Сумма',
										hidden: false,
										resizable: false,
										sortable: true,
										width: 100
									}, {
										dataIndex: 'DrugRequestType_Name',
										header: 'Тип',
										hidden: false,
										resizable: true,
										sortable: true,
										width: 150
									}, {
										dataIndex: 'MedPersonal_Fio',
										header: 'Врач',
										hidden: false,
										resizable: true,
										sortable: true,
										width: 200
									}, {
										dataIndex: 'Lpu_Nick',
										header: 'МО',
										hidden: false,
										resizable: true,
										sortable: true,
										width: 200
									}, {
										dataIndex: 'DrugRequestRow_insDate',
										header: 'Внесен',
										hidden: false,
										renderer: Ext.util.Format.dateRenderer('d.m.Y'),
										resizable: false,
										sortable: true,
										width: 100
									}, {
										dataIndex: 'DrugRequestRow_updDate',
										header: 'Изменен',
										hidden: false,
										renderer: Ext.util.Format.dateRenderer('d.m.Y'),
										resizable: false,
										sortable: true,
										width: 100
									}, {
										dataIndex: 'DrugRequestRow_delDate',
										header: 'Удален',
										hidden: false,
										renderer: Ext.util.Format.dateRenderer('d.m.Y'),
										resizable: false,
										sortable: true,
										width: 100
									}],
								frame: false,
								id: 'EREF_DrugRequestRowGrid',
								keys: [{
									key: [
										Ext.EventObject.END,
										Ext.EventObject.HOME,
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

										var grid = Ext.getCmp('EREF_DrugRequestRowGrid');

										switch ( e.getKey() ) {
											case Ext.EventObject.END:
												GridEnd(grid);
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
												var base_form = this.findById('EvnReceptEditForm').getForm();

												grid.getSelectionModel().clearSelections();
												grid.getSelectionModel().fireEvent('rowselect', grid.getSelectionModel());

												if ( e.shiftKey == false ) {
													if ( !this.findById('EREF_DrugPanel').collapsed && this.action != 'view' ) {
														base_form.findField('Drug_IsMnn').focus(true);
													}
													else if ( this.action != 'view' ) {
														this.buttons[0].focus();
													}
													else {
														this.buttons[1].focus();
													}
												}
												else {
													if ( !base_form.findField('DrugRequestRow_IsReserve').disabled ) {
														base_form.findField('DrugRequestRow_IsReserve').focus(true);
													}
													else if ( !base_form.findField('MedPersonal_rid').disabled ) {
														base_form.findField('MedPersonal_rid').focus(true);
													}
													else if ( !this.findById('EREF_DrugRequestRowPanel').collapsed && !base_form.findField('MedPersonal_rid').disabled ) {
														base_form.findField('DrugRequestRow_IsReserve').focus(true);
													}
													else if ( !this.findById('EREF_PrivilegePanel').collapsed && !base_form.findField('PrivilegeType_id').disabled ) {
														base_form.findField('PrivilegeType_id').focus(true);
													}
													else if ( !this.findById('EREF_PrivilegePanel').collapsed && !base_form.findField('ReceptFinance_id').disabled ) {
														base_form.findField('ReceptFinance_id').focus(true);
													}
													else if ( this.action != 'view' ) {
														base_form.findField('Diag_id').focus(true);
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
								loadMask: true,
								region: 'center',
								sm: new Ext.grid.RowSelectionModel({
									listeners: {
										'rowselect': function(sm, rowIndex, record) {
											var base_form = wnd.findById('EvnReceptEditForm').getForm();

											if (!Ext.isEmpty(record.get('MedPersonal_id'))) {
												// устанавливаем врача
												if (base_form.findField('MedPersonal_rid').getStore().getById(record.get('MedPersonal_id'))) {
													base_form.findField('MedPersonal_rid').setValue(record.get('MedPersonal_id'));
												}
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
												LoadEmptyRow(this.findById('EREF_DrugRequestRowGrid'));
											}
										}.createDelegate(this)
									},
									reader: new Ext.data.JsonReader({
										id: 'DrugRequestRow_id'
									}, [{
										mapping: 'DrugRequestRow_id',
										name: 'DrugRequestRow_id',
										type: 'int'
									}, {
										mapping: 'DrugRequestPeriod_Name',
										name: 'DrugRequestPeriod_Name',
										type: 'string'
									}, {
										mapping: 'DrugRequestRow_Name',
										name: 'DrugRequestRow_Name',
										type: 'string'
									}, {
										mapping: 'DrugRequestRow_Kolvo',
										name: 'DrugRequestRow_Kolvo',
										type: 'float'
									}, {
										mapping: 'DrugRequestRow_Price',
										name: 'DrugRequestRow_Price',
										type: 'float'
									}, {
										mapping: 'DrugRequestRow_Summa',
										name: 'DrugRequestRow_Summa',
										type: 'float'
									}, {
										mapping: 'DrugRequestType_Name',
										name: 'DrugRequestType_Name',
										type: 'string'
									}, {
										mapping: 'MedPersonal_Fio',
										name: 'MedPersonal_Fio',
										type: 'string'
									}, {
										mapping: 'MedPersonal_id',
										name: 'MedPersonal_id',
										type: 'string'
									}, {
										mapping: 'Lpu_Nick',
										name: 'Lpu_Nick',
										type: 'string'
									}, {
										dateFormat: 'd.m.Y',
										mapping: 'DrugRequestRow_insDate',
										name: 'DrugRequestRow_insDate',
										type: 'date'
									}, {
										dateFormat: 'd.m.Y',
										mapping: 'DrugRequestRow_updDate',
										name: 'DrugRequestRow_updDate',
										type: 'date'
									}, {
										dateFormat: 'd.m.Y',
										mapping: 'DrugRequestRow_delDate',
										name: 'DrugRequestRow_delDate',
										type: 'date'
									}]),
									url: '/?c=EvnRecept&m=loadDrugRequestRowGrid'
								}),
								title: 'Список заявленных медикаментов'
							})
						]
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
					title: '5. Медикамент',
					items: [ new Ext.Panel({
						autoHeight: true,
						border: false,
						id: 'EREF_EvnReceptDrugPanel',
						layout: 'form',

						items: [{
							border: false,
							layout: 'column',
							width: 900,
							items: [{
								border: false,
								layout: 'form',
								items: [ new sw.Promed.SwYesNoCombo({
									disabled: false,
									fieldLabel: 'Выписка по МНН',
									hiddenName: 'Drug_IsMnn',
									listeners: {
										'keydown': function(inp, e) {
											if ( e.getKey() == Ext.EventObject.TAB && e.shiftKey == true ) {
												if ( !this.findById('EREF_DrugRequestRowPanel').collapsed && this.findById('EREF_DrugRequestRowGrid').getStore().getCount() > 0 ) {
													e.stopEvent();
													this.findById('EREF_DrugRequestRowGrid').getView().focusRow(0);
													this.findById('EREF_DrugRequestRowGrid').getSelectionModel().selectFirstRow();
												}
												else if ( !this.findById('EREF_DrugRequestRowPanel').collapsed && !this.findById('EvnReceptEditForm').getForm().findField('DrugRequestRow_IsReserve').disabled ) {
													e.stopEvent();
													this.findById('EvnReceptEditForm').getForm().findField('DrugRequestRow_IsReserve').focus(true);
												}
												else if ( !this.findById('EREF_DrugRequestRowPanel').collapsed && !this.findById('EvnReceptEditForm').getForm().findField('MedPersonal_rid').disabled ) {
													e.stopEvent();
													this.findById('EvnReceptEditForm').getForm().findField('DrugRequestRow_IsReserve').focus(true);
												}
												else if ( !this.findById('EREF_PrivilegePanel').collapsed && !this.findById('EvnReceptEditForm').getForm().findField('PrivilegeType_id').disabled ) {
													e.stopEvent();
													this.findById('EvnReceptEditForm').getForm().findField('PrivilegeType_id').focus(true);
												}
												else if ( !this.findById('EREF_PrivilegePanel').collapsed && !this.findById('EvnReceptEditForm').getForm().findField('ReceptFinance_id').disabled ) {
													e.stopEvent();
													this.findById('EvnReceptEditForm').getForm().findField('ReceptFinance_id').focus(true);
												}
											}
										}.createDelegate(this),
										'change': function(combo, newValue, oldValue) {
											combo.setLinkedFieldValues();
										}
									},
									listWidth: 80,
									tabIndex: TABINDEX_EREF + 16,
									validateOnBlur: true,
									value: 2,
									width: 80,
									setLinkedFieldValues: function() {
										var base_form = wnd.findById('EvnReceptEditForm').getForm();
										var drug_mnn_combo = base_form.findField('DrugMnn_id');
										var is_mnn = (wnd.getComboSelectedRecordValue(this, 'YesNo_Code') == 1);

										if (wnd.action != 'view') {
											drug_mnn_combo.setAllowBlank(wnd.is_request || !is_mnn);
										}
									}
								})]
							}, {
								border: false,
								layout: 'form',
								items: [ new sw.Promed.SwYesNoCombo({
									fieldLabel: 'Протокол ВК',
									hiddenName: 'Drug_IsKEK',
									tabIndex: TABINDEX_EREF + 17,
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
                                    tabIndex: TABINDEX_EREF + 17,
                                    width: 80,
                                    xtype: 'textfield'
                                }]
                            }, {
                                border: false,
                                layout: 'form',
                                items: [{
                                    fieldLabel: 'Дата протокола ВК',
                                    name: 'EvnRecept_VKProtocolDT',
                                    tabIndex: TABINDEX_EREF + 17,
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
                                    tabIndex: TABINDEX_EREF + 17,
									width: 300,
									comboSubject: 'CauseVK',
									xtype: 'swcommonsprcombo'
								}]
                            }]
						},
							{
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
													return (rec.get('PrescrSpecCause_Code') == 1);    //по ТЗ должна быть доступна только первая запись справочника
												});
											}
										}
									}]
								}]
							},
							{
							displayField: 'DrugRequestRow_Name',
							fieldLabel: 'Заявка',
							hiddenName: 'DrugRequestMnn_id',
							listeners: {
								'beforeselect': function() {
									this.findById('EvnReceptEditForm').getForm().findField('Drug_id').lastQuery = '';
									return true;
								}.createDelegate(this),
								'change': function(combo, newValue, oldValue) {
									// Выбрано значение поля "Заявка"
									var base_form = this.findById('EvnReceptEditForm').getForm();

									var drug_combo = base_form.findField('Drug_id');
									var drug_mnn_combo = base_form.findField('DrugMnn_id');

									drug_combo.clearValue();
									drug_combo.getStore().removeAll();
									drug_combo.lastQuery = '';

									base_form.findField('Drug_Price').setRawValue('');

									// Ищем запись, соответствующую выбранному значению
									var index = combo.getStore().findBy(function(rec) {
										if ( rec.get('id') == newValue ) {
											return true;
										}
										else {
											return false;
										}
									});
									var record = combo.getStore().getAt(index);

									// Если запись не выбрана
									if ( !record ) {
										// прерываем выполнение метода
										return false;
									}

									// Устанавливаем значения базовых параметров поля "Торговое наименование"
									drug_combo.getStore().baseParams.Drug_DoseCount = record.get('Drug_DoseCount');
									drug_combo.getStore().baseParams.Drug_Dose = record.get('Drug_Dose');
									drug_combo.getStore().baseParams.Drug_Fas = record.get('Drug_Fas');
									drug_combo.getStore().baseParams.DrugFormGroup_id = record.get('DrugFormGroup_id');
									drug_combo.getStore().baseParams.DrugMnn_id = record.get('DrugMnn_id');
									drug_combo.getStore().baseParams.query = '';
									drug_combo.getStore().baseParams.RequestDrug_id = record.get('RequestDrug_id');
									drug_combo.getStore().baseParams.DrugRequestRow_id = record.get('DrugRequestRow_id');

									drug_mnn_combo.getStore().load({
										callback: function() {
											// устанавливаем значение комбо "МНН"
											drug_mnn_combo.setValue(record.get('DrugMnn_id'));
										},
										params: {
											DrugMnn_id: record.get('DrugMnn_id')
										}
									})

									// Загружаем список медикаментов
									drug_combo.getStore().baseParams.is_mi_1 = (base_form.findField('ReceptForm_id').getValue() == 2) ? true : false;
									drug_combo.getStore().load();

									wnd.setDrugAllowedQuantity();

									return true;
								}.createDelegate(this),
								'keydown': function(inp, e) {
									if ( e.getKey() == Ext.EventObject.F4 || e.getKey() == Ext.EventObject.DELETE ) {
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

										var drug_combo = this.findById('EvnReceptEditForm').getForm().findField('Drug_id');

										switch ( e.getKey() ) {
											case Ext.EventObject.DELETE:
												inp.clearValue();

												// Проверяем: если одна запись и DrugRequestRow_id пустое, то перезагружаем комбо
												if ( inp.getStore().getCount() == 2 && !inp.getStore().getAt(1).get('DrugRequestRow_id') ) {
													inp.getStore().removeAll();
													inp.getStore().load();
												}

												drug_combo.getStore().baseParams.Drug_DoseCount = null;
												drug_combo.getStore().baseParams.Drug_Dose = null;
												drug_combo.getStore().baseParams.Drug_Fas = null;
												drug_combo.getStore().baseParams.DrugFormGroup_id = null;
												drug_combo.getStore().baseParams.DrugMnn_id = null;
												drug_combo.getStore().baseParams.query = '';
												drug_combo.getStore().baseParams.RequestDrug_id = null;
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
							onTrigger2Click: function() {
								var base_form = this.findById('EvnReceptEditForm').getForm();

								if ( base_form.findField('DrugRequestMnn_id').disabled ) {
									return false;
								}
								var drug_combo = base_form.findField('Drug_id');
								var drug_request_mnn_combo = base_form.findField('DrugRequestMnn_id');
								var recept_finance_combo = base_form.findField('ReceptFinance_id');
								var recept_type_combo = base_form.findField('ReceptType_id');
								var privilege_type_combo = base_form.findField('PrivilegeType_id');
								var drug_mnn_combo = base_form.findField('DrugMnn_id');

								var recept_finance_code = 0;
								var recept_type_code = 0;

								var record = recept_finance_combo.getStore().getById(recept_finance_combo.getValue());

								if ( record ) {
									recept_finance_code = record.get('ReceptFinance_Code');
								}

								if ( recept_finance_code == 0 ) {
									sw.swMsg.alert('Ошибка', 'Не выбран тип финансирования льготы', function() { recept_finance_combo.focus(true); });
									return false;
								}

								record = recept_type_combo.getStore().getById(recept_type_combo.getValue());

								if ( record ) {
									recept_type_code = record.get('ReceptType_Code');
								}

								if ( recept_type_code == 0 ) {
									sw.swMsg.alert('Ошибка', 'Не выбран тип рецепта', function() { recept_type_combo.focus(true); });
									return false;
								}

								privilege_type_id = privilege_type_combo.getValue();

								getWnd('swDrugRequestMnnSearchWindow').show({
									EvnRecept_setDate: Ext.util.Format.date(base_form.findField('EvnRecept_setDate').getValue(), 'd.m.Y'),
                                    ReceptFinance_Code: recept_finance_code,
                                    ReceptType_Code: recept_type_code,
                                    PrivilegeType_id: privilege_type_id,
                                    mode: recept_type_code == '1' ? 'only_search_form_filters' : 'all',
                                    onClose: function() {
										drug_request_mnn_combo.focus(false);
									},
									onSelect: function(drugRequestMnnData) {
										drug_request_mnn_combo.getStore().removeAll();
										drug_request_mnn_combo.getStore().loadData([ drugRequestMnnData ]);
										drug_request_mnn_combo.setValue(drugRequestMnnData.id);

										var index = drug_request_mnn_combo.getStore().findBy(function(rec) {
											if ( rec.get('id') == drugRequestMnnData.id ) {
												return true;
											}
											else {
												return false;
											}
										});
										var record = drug_request_mnn_combo.getStore().getAt(index);
										/*
										 if ( record ) {
										 drug_request_mnn_combo.fireEvent('change', drug_request_mnn_combo, record.get('id'), 0);
										 }
										 */
										drug_combo.clearValue();
										drug_combo.getStore().removeAll();
										drug_combo.lastQuery = '';

										base_form.findField('Drug_Price').setRawValue('');

										// Устанавливаем значения базовых параметров поля "Торговое наименование"
										drug_combo.getStore().baseParams.Drug_DoseCount = record.get('Drug_DoseCount');
										// drug_combo.getStore().baseParams.Drug_Dose = record.get('Drug_Dose');
										// drug_combo.getStore().baseParams.Drug_Fas = record.get('Drug_Fas');
										drug_combo.getStore().baseParams.DrugFormGroup_id = record.get('DrugFormGroup_id');
										drug_combo.getStore().baseParams.DrugMnn_id = record.get('DrugMnn_id');
										drug_combo.getStore().baseParams.mode = 'all';
										drug_combo.getStore().baseParams.query = '';
										drug_combo.getStore().baseParams.RequestDrug_id = record.get('RequestDrug_id');

										drug_mnn_combo.getStore().load({
											callback: function() {
												// устанавливаем значение комбо "МНН"
												drug_mnn_combo.setValue(record.get('DrugMnn_id'));
											},
											params: {
												DrugMnn_id: record.get('DrugMnn_id')
											}
										})

										// Загружаем список медикаментов
										drug_combo.getStore().baseParams.is_mi_1 = (base_form.findField('ReceptForm_id').getValue()==2)?true:false;
										drug_combo.getStore().load();

										drug_combo.getStore().baseParams.mode = 'request';

										getWnd('swDrugRequestMnnSearchWindow').hide();

										drug_request_mnn_combo.collapse();
										drug_request_mnn_combo.el.removeClass('x-form-focus');
										drug_combo.focus(true);
									}.createDelegate(this)
								});
							}.createDelegate(this),
							initComponent: function() {
								Ext.form.TwinTriggerField.prototype.initComponent.apply(this, arguments);

								this.store = new Ext.data.JsonStore({
									autoLoad: false,
									fields: [
										{name: 'id', type: 'int'},
										{name: 'RequestDrug_id', type: 'int'},
										{name: 'DrugMnn_id', type: 'int'},
										{name: 'DrugFormGroup_id', type: 'int'},
										{name: 'Drug_DoseCount', type: 'float'},
										{name: 'Drug_Dose', type: 'string'},
										{name: 'Drug_Fas', type: 'float'},
										{name: 'DrugRequestRow_id', type: 'int'},
										{name: 'DrugRequestRow_Name', type: 'string'},
										{name: 'MedPersonal_Name', type: 'string'}
									],
									key: 'id',
									sortInfo: {
										field: 'id'
									},
									url: '/?c=EvnRecept&m=loadDrugRequestMnnList'
								});
							},
							tabIndex: TABINDEX_EREF + 18,
							width: 517,
							xtype: 'swdrugrequestmnncombo'
						}, {
							displayField: 'DrugMnn_Name',
							allowBlank: false,
							fieldLabel: 'МНН',
							hiddenName: 'DrugMnn_id',
							lastQuery: '',
							listWidth: 800,
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

									drug_combo.clearValue();
									drug_combo.getStore().removeAll();
									drug_combo.lastQuery = '';

									base_form.findField('Drug_Price').setRawValue('');

									// Устанавливаем значения базовых параметров поля "Торговое наименование"
									drug_combo.getStore().baseParams.Drug_DoseCount = null;
									drug_combo.getStore().baseParams.Drug_Dose = null;
									drug_combo.getStore().baseParams.Drug_Fas = null;
									drug_combo.getStore().baseParams.DrugFormGroup_id = null;
									drug_combo.getStore().baseParams.DrugMnn_id = newValue;
									drug_combo.getStore().baseParams.query = '';
									drug_combo.getStore().baseParams.RequestDrug_id = null;

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
							store: new Ext.data.Store({
								autoLoad: false,
								reader: new Ext.data.JsonReader({
									id: 'DrugMnn_id'
								}, [
									{ name: 'DrugMnn_id', type: 'int' },
									{ name: 'DrugMnn_Code', type: 'int' },
									{ name: 'DrugMnn_Name', type: 'string' }
								]),
								url: C_DRUG_MNN_LIST
							}),
							initComponent: function() {
								Ext.form.ComboBox.prototype.initComponent.apply(this, arguments);
								this.initTrigger = Ext.form.ComboBox.prototype.initTrigger;

								this.store = new Ext.data.JsonStore({
									autoLoad: false,
									fields: [
										{name: 'DrugMnn_id', type: 'int'},
										{name: 'DrugMnn_Code', type: 'int'},
										{name: 'DrugMnn_Name', type: 'string'}
									],
									key: 'DrugMnn_id',
									sortInfo: {
										field: 'DrugMnn_Name'
									},
									url: C_DRUG_MNN_LIST
								});
							},
							tabIndex: TABINDEX_EREF + 19,
							validateOnBlur: true,
							valueField: 'DrugMnn_id',
							width: 517,
							xtype: 'swdrugmnncombo'
						}, {
							allowBlank: false,
							hiddenName: 'Drug_id',
							listeners: {
								'beforeselect': function(combo, record, index) {
									// Навесить функционал, зависящий от выбранного значения поля "Тип финансирования"

									var base_form = this.findById('EvnReceptEditForm').getForm();

									combo.setValue(record.get('Drug_id'));
									base_form.findField('Drug_Price').setValue(record.get('Drug_Price'));
									if(record.get('Drug_IsKEK') == 2) { //если у выбранного препарата проставлен признак выписки через ВК
										base_form.findField('Drug_IsKEK').setValue(2); //2 - Да
									}
                                    this.setVKProtocolFieldsVisible();

									// Получаем запись для выбранного типа финансирования
									var recept_finance_record = base_form.findField('ReceptFinance_id').getStore().getById(base_form.findField('ReceptFinance_id').getValue());

									switch ( Number(recept_finance_record.get('ReceptFinance_Code')) ) {
										case 1:
										case 2:
											// Если тип финансирования "Федеральный бюджет" или "Субъект РФ"
											var drug_mnn_request_combo = base_form.findField('DrugRequestMnn_id');

											var index = drug_mnn_request_combo.getStore().findBy(function(rec) {
												if ( rec.get('DrugMnn_id') == record.get('DrugMnn_id') &&
														// rec.get('Drug_Fas') == record.get('Drug_Fas') &&
														// rec.get('Drug_Dose') == record.get('Drug_Dose') &&
													rec.get('Drug_DoseCount') == record.get('Drug_DoseCount') &&
													rec.get('DrugFormGroup_id') == record.get('DrugFormGroup_id')
												) {
													return true;
												}
												else {
													return false;
												}
											});

											var drug_mnn_request_record = drug_mnn_request_combo.getStore().getAt(index);

											if ( drug_mnn_request_record ) {
												drug_mnn_request_combo.setValue(drug_mnn_request_record.get('id'));
											}
											// break; идем в следующий case чтобы заполнилось поле МНН

										case 3:
											// Если тип финансирования "7 нозологий"
											var drug_mnn_combo = base_form.findField('DrugMnn_id');
											var drug_mnn_record = drug_mnn_combo.getStore().getById(record.get('DrugMnn_id'));

											drug_mnn_combo.lastQuery = '';

											// Если есть соответствующая запись в комбо "МНН"
											if ( drug_mnn_record ) {
												// устанавливаем значение комбо "МНН"
												drug_mnn_combo.setValue(record.get('DrugMnn_id'));
											}
											// Если нет соответствующей записи в комбо "МНН"
											else {
												// загружаем нужную запись в комбо "МНН"
												drug_mnn_combo.getStore().load({
													callback: function() {
														// устанавливаем значение комбо "МНН"
														drug_mnn_combo.setValue(record.get('DrugMnn_id'));
													},
													params: {
														DrugMnn_id: record.get('DrugMnn_id')
													}
												})
											}
										break;
									}

									// Выводим предупреждение, если медикамент выписывается через ВК
									if ( record.get('Drug_IsKEK_Code') == 1 ) {
										sw.swMsg.alert('Сообщение', 'Внимание! Данный медикамент выписывается через ВК', function() { combo.focus(true); });
									}

									return true;
								}.createDelegate(this),
								'change': function(combo, newValue, oldValue) {
									wnd.setDrugAllowedQuantity();

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
							minChars: 3,
							minLengthText: 'Поле должно быть заполнено',
							onTrigger2Click: function() {
								var base_form = this.findById('EvnReceptEditForm').getForm();

								if ( base_form.findField('Drug_id').disabled ) {
									return false;
								}
								var drug_combo = base_form.findField('Drug_id');
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

								var privilege_type_id = wnd.privilege_type_combo.getValue();
								var cost_type_id = wnd.cost_type_combo.getValue();

								getWnd('swDrugTorgSearchWindow').show({
									EvnRecept_Is7Noz_Code: wnd.is_vzn ? 1 : 0,
									EvnRecept_setDate: Ext.util.Format.date(base_form.findField('EvnRecept_setDate').getValue(), 'd.m.Y'),
									is_mi_1: (base_form.findField('ReceptForm_id').getValue()==2)?true:false,
                                    ReceptFinance_Code: recept_finance_code,
                                    ReceptType_Code: recept_type_code,
                                    PrivilegeType_id: privilege_type_id,
                                    WhsDocumentCostItemType_id: cost_type_id,
                                    mode: recept_type_code == '1' ? 'only_search_form_filters' : 'all',
									onHide: function() {
										drug_combo.focus(false);
									},
									onSelect: function(drugTorgData) {
										drug_combo.getStore().removeAll();

										drug_combo.getStore().loadData([ drugTorgData ]);

										drug_combo.getStore().baseParams.DrugMnn_id = 0;
										drug_combo.getStore().baseParams.RequestDrug_id = 0;
										record = drug_combo.getStore().getById(drugTorgData.Drug_id);

										if ( record ) {
											drug_combo.fireEvent('beforeselect', drug_combo, record);
										}

										getWnd('swDrugTorgSearchWindow').hide();
										wnd.setDrugAllowedQuantity();
									}
								});
							}.createDelegate(this),
							initComponent: function() {
								Ext.form.TwinTriggerField.prototype.initComponent.apply(this, arguments);

								this.store = new Ext.data.Store({
									autoLoad: false,
									reader: new Ext.data.JsonReader({
										id: 'Drug_id'
									}, [
										{ name: 'Drug_id', type: 'int' },
										{ name: 'DrugRequestRow_id', type: 'int' },
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
							tabIndex: TABINDEX_EREF + 20,
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
							layout: 'column',
							border: false,
							items: [{
								layout: 'form',
								border: false,
								items: [{
									disabled: true,
									fieldLabel: 'Цена',
									name: 'Drug_Price',
									xtype: 'textfield'
								}]
							}, {
								layout: 'form',
								border: false,
								labelWidth: 150,
								hidden: true,
								items: [{
									disabled: true,
									fieldLabel: 'Доступно для выписки',
									name: 'Drug_AllowedQuantity',
									xtype: 'textfield'
								}]
							}]
						}]
					}),
					new Ext.Panel({
						autoHeight: true,
						border: false,
						id: 'EREF_EvnReceptExtempPanel',
						layout: 'form',

						items: [{
							fieldLabel: 'Состав',
							height: 100,
							name: 'EvnRecept_ExtempContents',
							tabIndex: TABINDEX_EREF + 21,
							width: 517,
							xtype: 'textarea'
						}]
					}), {
							fieldLabel: 'Превышение дозировки',
							name: 'EvnRecept_IsExcessDose',
							xtype: 'checkbox'
						}, {
						allowBlank: false,
						fieldLabel: 'Аптека пациента',
						hiddenName: 'OrgFarmacy_id',
						lastQuery: '',
						listWidth: 800,
						tabIndex: TABINDEX_EREF + 22,
						width: 517,
						tpl: new Ext.XTemplate(
							'<tpl for="."><div class="x-combo-list-item">',
							'<div style="width: 55%;{[values.OrgFarmacy_id != -1 ? "" : "font-weight:bold;"]}">{[values.OrgFarmacy_Name==""?"&nbsp;":values.OrgFarmacy_Name]} <span style="font-style:italic;">{values.OrgFarmacy_HowGo}</span></div>',
							'</div></tpl>'
						),
						xtype: 'sworgfarmacycombo'
					}, {
						allowBlank: false,
						allowNegative: false,
						fieldLabel: 'Количество (D. t. d.)',
						minValue: 0.01,
						name: 'EvnRecept_Kolvo',
						tabIndex: TABINDEX_EREF + 23,
						validateOnBlur: true,
						value: 1,
						xtype: 'numberfield'
					}, {
						allowBlank: false,
						fieldLabel: 'Signa',
						name: 'EvnRecept_Signa',
						testId: 'EREF_field_Signa',
						tabIndex: TABINDEX_EREF + 24,
						validateOnBlur: true,
						width: 517,
						xtype: 'textfield'
					}]
				}),
				/*new sw.Promed.Panel({
					autoHeight: true,
					bodyStyle: 'padding-top: 0.5em;',
					border: true,
					collapsible: true,
					id: 'EREF_DrugWrong',
					layout: 'form',
					style: 'margin-bottom: 0.5em;',
					title: '6. Информация об отказе',
					items: [{
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
				})],*/
				new sw.Promed.Panel({
					autoHeight: true,
					bodyStyle: 'padding-top: 0.5em;',
					border: true,
					collapsible: true,
					id: 'EREF_DrugResult',
					layout: 'form',
					title: '6. Результат',
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
				})],

				labelAlign: 'right',
				labelWidth: 130,
				reader: new Ext.data.JsonReader({
					success: Ext.emptyFn
				}, [
					{ name: 'Diag_id' },
					{ name: 'Drug_id' },
					// { name: 'Drug_IsKEK' },
					{ name: 'Drug_IsMnn' },
					{ name: 'DrugMnn_id' },
					{ name: 'Drug_Price' },
					{ name: 'Drug_IsKEK' },
					{ name: 'DrugRequestMnn_id' },
					{ name: 'EvnRecept_ExtempContents' },
					{ name: 'EvnRecept_IsExtemp' },
					{ name: 'EvnRecept_Kolvo' },
					{ name: 'EvnRecept_Num' },
					{ name: 'EvnRecept_pid' },
					{ name: 'EvnRecept_Ser' },
					{ name: 'EvnRecept_setDate' },
					{ name: 'EvnRecept_Signa' },
					{ name: 'EvnRecept_IsDelivery' },
					{ name: 'Lpu_rid' },
					{ name: 'LpuSection_id' },
					{ name: 'MedPersonal_id' },
					{ name: 'MedPersonal_rid' },
					{ name: 'OrgFarmacy_id' },
					{ name: 'PrivilegeType_id' },
					{ name: 'WhsDocumentCostItemType_id' },
					{ name: 'ReceptDiscount_id' },
					{ name: 'ReceptFinance_id' },
					{ name: 'DrugFinance_id' },
					{ name: 'ReceptType_id' },
                    { name: 'ReceptForm_id' },
					{ name: 'ReceptValid_id' },
					{ name: 'Recept_Result'},
					{ name: 'Recept_Result_Code'},
					{ name: 'Recept_Delay_Info'},
					{ name: 'EvnRecept_Drugs'},
					{ name: 'ReceptOtov_Farmacy' },
					{ name: 'ReceptOtov_Date' },
					{ name: 'EvnRecept_VKProtocolNum' },
					{ name: 'EvnRecept_VKProtocolDT' },
					{ name: 'CauseVK_id' },
					{ name: 'PrescrSpecCause_id' },
					{ name: 'ReceptUrgency_id' },
					{ name: 'EvnRecept_IsExcessDose' }
					//{ name: 'ReceptWrong_Decr' }
				]),
				region: 'center',
				trackResetOnLoad: true,
				url: C_EVNREC_SAVE
			})]
		});

		sw.Promed.swEvnReceptEditWindow.superclass.initComponent.apply(this, arguments);

		this.findById('EREF_MedStaffFactCombo').addListener('change', function(combo, newValue, oldValue) {
			var base_form = this.findById('EvnReceptEditForm').getForm();

			var evn_recept_is_extemp = base_form.findField('EvnRecept_IsExtemp').getValue().toString();

			var record = combo.getStore().getById(newValue);

			if ( !record ) {
				return true;
			}

			var med_personal_id = record.get('MedPersonal_id');

			if ( base_form.findField('MedPersonal_rid').getStore().getById(med_personal_id) ) {
				base_form.findField('MedPersonal_rid').setValue(med_personal_id);
				base_form.findField('MedPersonal_rid').fireEvent('change', base_form.findField('MedPersonal_rid'), med_personal_id);
			}
		}.createDelegate(this));
	},
	setMedPersonalValue: function(med_personal_combo, med_personal_rid) {
		var base_form = this.findById('EvnReceptEditForm').getForm();
		// Ищем запись, соответствующую старому значению
		var med_personal_id = base_form.findField('MedStaffFact_id').getFieldValue('MedPersonal_id');
		var med_personal_record = med_personal_combo.getStore().getById(med_personal_rid);

		var index = med_personal_combo.getStore().findBy(function(rec) {
			if (rec.get('MedPersonal_IsMain') == 2) {
				return true;
			}

			return false;
		});

		// Если запись не найдена
		if (!med_personal_record) {
			if ( !Ext.isEmpty(med_personal_id) && base_form.findField('MedPersonal_rid').getStore().getById(med_personal_id) ) {
				// выбираем запись из основного комбо
				med_personal_combo.setValue(med_personal_id);
				med_personal_combo.fireEvent('change', med_personal_combo, med_personal_id);
			} else if (index >= 0) {
				// иначе выбираем врача, являющийся текущим участковым врачом на участке в МО прикрепления пациента по основному типу прикрепления.
				med_personal_rid = med_personal_combo.getStore().getAt(index).get('MedPersonal_id');
				med_personal_combo.setValue(med_personal_rid);
				med_personal_combo.fireEvent('change', med_personal_combo, med_personal_rid);
			} else {
				med_personal_combo.clearValue();
				med_personal_combo.fireEvent('change', med_personal_combo, null);
			}
		}
		else {
			// устанавливаем старое значение
			med_personal_combo.setValue(med_personal_rid);
			// вызываем метод change списка врачей
			med_personal_combo.fireEvent('change', med_personal_combo, med_personal_rid);
		}
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
						checkDrugRequest: true,
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
					Ext.getCmp('EREF_DrugRequestRowPanel').toggleCollapse();
				break;

				case Ext.EventObject.NUM_FOUR:
				case Ext.EventObject.FOUR:
					Ext.getCmp('EREF_DrugPanel').toggleCollapse();
				break;

				case Ext.EventObject.NUM_FIVE:
				case Ext.EventObject.FIVE:
					Ext.getCmp('EREF_DrugRequestOtovPanel').toggleCollapse();
				break;
			}
		},
		key: [
			Ext.EventObject.C,
			Ext.EventObject.FIVE,
			Ext.EventObject.FOUR,
			Ext.EventObject.G,
			Ext.EventObject.J,
			Ext.EventObject.NUM_FIVE,
			Ext.EventObject.NUM_FOUR,
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
		'collapse': function(win) {
			win.findById('EREF_DrugRequestRowPanel').doLayout();
		},
		'expand': function(win) {
			win.findById('EREF_DrugRequestRowPanel').doLayout();
		},
		'hide': function() {
			this.buttons[5].focus();
			this.onHide();
		}
	},
	loadDrugRequestRowGrid: function() {
		this.findById('EREF_DrugRequestRowGrid').getStore().removeAll();

		var base_form = this.findById('EvnReceptEditForm').getForm();

		var drug_request_row_is_reserve = base_form.findField('DrugRequestRow_IsReserve').getValue();
		var evn_recept_set_date = base_form.findField('EvnRecept_setDate').getValue();
		var med_personal_rid = base_form.findField('MedPersonal_rid').getValue();
		var drugprotomdd_id = base_form.findField('DrugProtoMnn_id').getValue();
		var person_id = base_form.findField('Person_id').getValue();

		if ( (Ext.isEmpty(med_personal_rid) && Ext.isEmpty(drugprotomdd_id) && drug_request_row_is_reserve == 2) || !evn_recept_set_date || !person_id ) {
			LoadEmptyRow(this.findById('EREF_DrugRequestRowGrid'));
			return false;
		}

		var record = base_form.findField('ReceptFinance_id').getStore().getById(base_form.findField('ReceptFinance_id').getValue());

		// Проверяем тип финансирования. Загружаем данные по заявке, только если выбран тип "Федеральный" или "Субъект РФ"
		if ( !record || (record.get('ReceptFinance_Code') != 1 && record.get('ReceptFinance_Code') != 2) ) {
			LoadEmptyRow(this.findById('EREF_DrugRequestRowGrid'));
			return false;
		}

		this.findById('EREF_DrugRequestRowGrid').getStore().load();
	},
	loadDrugRequestMnnCombo: function() {
		if ( this.action == 'view' ) {
			return false;
		}

		var base_form = this.findById('EvnReceptEditForm').getForm();
		var combo = base_form.findField('DrugRequestMnn_id');

		combo.clearValue();
		combo.getStore().removeAll();

		var drug_request_row_is_reserve = base_form.findField('DrugRequestRow_IsReserve').getValue();
		var evn_recept_set_date = base_form.findField('EvnRecept_setDate').getValue();
		var med_personal_rid = base_form.findField('MedPersonal_rid').getValue();
		var drugprotomdd_id = base_form.findField('DrugProtoMnn_id').getValue();
		var person_id = base_form.findField('Person_id').getValue();
		var recept_finance_id = base_form.findField('ReceptFinance_id').getValue();

		if ( (Ext.isEmpty(med_personal_rid) && Ext.isEmpty(drugprotomdd_id) && drug_request_row_is_reserve == 2) || !evn_recept_set_date || !person_id ) {
			return false;
		}

		var record = base_form.findField('ReceptFinance_id').getStore().getById(recept_finance_id);

		// Проверяем тип финансирования. Загружаем данные по заявке, только если выбран тип "Федеральный" или "Субъект РФ"
		if ( !record || (record.get('ReceptFinance_Code') != 1 && record.get('ReceptFinance_Code') != 2) ) {
			return false;
		}

		combo.getStore().load();
	},
	loadPersonRegisterStore: function(callback) {
		var wnd = this;
		var base_form = this.findById('EvnReceptEditForm').getForm();

		if (typeof callback != 'function') {
			callback = Ext.emptyFn;
		}

		if (wnd.person_register_store.state != 'loaded') {
			this.person_register_store.load({
				callback: function(records, options, success) {
					callback();
					wnd.person_register_store.state = 'loaded'
				},
				params: {
					Person_id: base_form.findField('Person_id').getValue()
				}
			});
		} else {
			callback();
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
					checkDrugRequest: true,
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
				//base_form.findField('Recept_Result_Code').getValue()
				if(this.findById('EvnReceptEditForm').getForm().findField('Recept_Result_Code').getValue()==4)
				{
					Ext.Msg.alert(langs('Ошибка'), 'Рецепт удален и не может быть распечатан');
					return false;
				}
				var evn_recept_set_date = this.findById('EvnReceptEditForm').getForm().findField('EvnRecept_setDate').getValue().format('Y-m-d');
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
                                                    } else{
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
                                                    if (getRegionNick() == 'msk') {
                                                        printBirt({
                                                            'Report_FileName': 'EvnReceptPrint_148_1u04_2InA4_2020.rptdesign',
                                                            'Report_Params': '&paramEvnRecept=' + evn_recept_id,
                                                            'Report_Format': 'pdf'
                                                        });
                                                    } else {
                                                        //игнорируем настройки и печатаем сразу обе стороны
                                                        printBirt({
                                                            'Report_FileName': 'EvnReceptPrint_148_1u04_2InA4_2019.rptdesign',
                                                            'Report_Params': '&paramEvnRecept=' + evn_recept_id,
                                                            'Report_Format': 'pdf'
                                                        });
                                                    }
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
		var wnd = this;
		var base_form = this.findById('EvnReceptEditForm').getForm();
        var recept_ser_field = base_form.findField('EvnRecept_Ser');

		// чтобы не отрабатывало, пока форма не прогрузится
		if (Ext.isEmpty(base_form.findField('ReceptForm_id').getValue())) {
			return false;
		}

		//Для МСК получение номера происходит только после заполнения отделения
		var lpuSectionComboValue = base_form.findField('EREF_LpuSectionCombo').getValue();
		if (getRegionNick() == 'msk' && Ext.isEmpty(lpuSectionComboValue)) {
			return false;
		}

		if (base_form.findField('ReceptType_id').getValue() == 1) {
			return false;
		}

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT });
		loadMask.show();
		Ext.Ajax.request({
			params: {
				ReceptForm_id: base_form.findField('ReceptForm_id').getValue(),
				LpuSection_id: lpuSectionComboValue,
				EvnRecept_setDate: Ext.util.Format.date(base_form.findField('EvnRecept_setDate').getValue(), 'd.m.Y')
			},
			callback: function(options, success, response) {
				loadMask.hide();
				if ( success ) {
					var response_obj = Ext.util.JSON.decode(response.responseText);
					if (!response_obj.Error_Msg) {
						base_form.findField('EvnRecept_Num').setValue(response_obj.EvnRecept_Num);
						if (response_obj.EvnRecept_Ser) {
							base_form.findField('EvnRecept_Ser').setValue(response_obj.EvnRecept_Ser);
						} else {
							wnd.getLpuOuz(function (lpu_ouz) {
								recept_ser_field.setValue(lpu_ouz);
							});
						}
					}
				}
				else {
					sw.swMsg.alert('Ошибка', 'Ошибка при определении номера рецепта', function() { base_form.findField('EvnRecept_setDate').focus(true); }.createDelegate(this) );
				}
			}.createDelegate(this),
			url: C_RECEPT_NUM
		});
	},
	setEditFormPanelAutoNum: function() { //автонумерация видимых блоков
		var form_items = this.findById('EvnReceptEditForm').items.items;
		var start_num = 1;

		for (var i = 0; i < form_items.length; i++) {
			if (!form_items[i].hidden && form_items[i].title && form_items[i].title.indexOf('.') > -1) {
				var title = (start_num++)+form_items[i].title.substr(form_items[i].title.indexOf('.'));
				form_items[i].setTitle(title);
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
        var is_visible = ((getRegionNick() == 'msk' || form_id == 9) && is_vk); //9	- 148-1/у-04(л); нужно переделать на проверку кода, когда истечет срок действия записи с идентификатором 1
        var cause_is_visible = (form_id == 9 && is_vk); //9	- 148-1/у-04(л); нужно переделать на проверку кода, когда истечет срок действия записи с идентификатором 1

        if (is_visible) {
            num_field.ownerCt.show();
            date_field.ownerCt.show();
        } else {
            num_field.ownerCt.hide();
            date_field.ownerCt.hide();
            if (this.action != 'view') {
                num_field.setValue(null);
                date_field.setValue(null);
            }
        }
        if (cause_is_visible) {
			cause_field.ownerCt.show();
		} else {
			cause_field.ownerCt.hide();
			if (this.action != 'view') {
				cause_field.setValue(null);
			}
		}
        num_field.setAllowBlank(!is_visible || this.action == 'view');
        date_field.setAllowBlank(!is_visible || this.action == 'view');
		cause_field.setAllowBlank(!cause_is_visible || this.action == 'view');

		this.findById('EvnReceptEditForm').doLayout();
    },
	setDrugRequestFieldsVisible: function() {
		var base_form = this.findById('EvnReceptEditForm').getForm();
		var is_mnn_combo = base_form.findField('Drug_IsMnn');
		var cost_combo = base_form.findField('WhsDocumentCostItemType_id');
		var request_combo = base_form.findField('DrugRequestMnn_id');
		var drug_mnn_combo = base_form.findField('DrugMnn_id');
		var drug_combo = base_form.findField('Drug_id');
		var is_request = (this.getComboSelectedRecordValue(cost_combo, 'WhsDocumentCostItemType_IsDrugRequest') == 2);
		var is_mnn = (this.getComboSelectedRecordValue(is_mnn_combo, 'YesNo_Code') == 1);
		var is_mi_1 = (base_form.findField('ReceptForm_id').getValue() == 2);

		this.is_request = is_request;

		if (is_request) {
			this.findById('EREF_DrugRequestOtovPanel').show(); //соответствие выписки и заявки
			this.findById('EREF_DrugRequestRowPanel').show(); //заявка
		} else {
			this.findById('EREF_DrugRequestOtovPanel').hide();
			this.findById('EREF_DrugRequestRowPanel').hide();
		}

		this.setEditFormPanelAutoNum();

		if (this.action == 'add') {
			// чистим поле "Заявка"
			request_combo.clearValue();
			request_combo.disable();
			request_combo.getStore().removeAll();

			// чистим поле "МНН"
			drug_mnn_combo.clearValue();
			drug_mnn_combo.disable();
			drug_mnn_combo.lastQuery = '';
			drug_mnn_combo.getStore().removeAll();

			// чистим поле "Торговое наименование"
			drug_combo.clearValue();
			drug_combo.enable();
			drug_combo.lastQuery = '';
			drug_combo.getStore().removeAll();
			drug_combo.getStore().baseParams.mode = 'all';
			drug_combo.getStore().baseParams.DrugMnn_id = 0;
			drug_combo.getStore().baseParams.RequestDrug_id = 0;

			if (is_request) {
				// открываем поле DrugRequestMnn_id
				request_combo.enable();
				drug_combo.getStore().baseParams.mode = 'request';
				this.loadDrugRequestMnnCombo();
			} else {
				// открываем поле DrugMnn_id
				drug_mnn_combo.enable();
			}

			request_combo.setAllowBlank(!is_request || is_mi_1);
			drug_mnn_combo.setAllowBlank(is_request || !is_mnn);
		} else {
			request_combo.setAllowBlank(true);
			drug_mnn_combo.setAllowBlank(true);
		}

		if (is_request) {
			request_combo.showContainer();
		} else {
			request_combo.hideContainer();
		}

		this.findById('EvnReceptEditForm').doLayout();
	},
	setPrivilegeTypeFilter: function() {
		this.privilege_type_combo.lastQuery = '';
		this.privilege_type_combo.getStore().clearFilter();

		this.privilege_type_combo.getStore().filterBy(function(record) {
			var is_kardio = (record.get('PrivilegeType_Code') == '508'); //ДЛО Кардио
			var is_closed = (record.get('PersonPrivilege_IsClosed') == 2);

			return (!is_kardio && !is_closed);
		});
	},
	setWhsDocumentCostItemTypeFilter: function(callback) {
		var wnd = this;
		var base_form = this.findById('EvnReceptEditForm').getForm();
		var date_field = base_form.findField('EvnRecept_setDate');

		if (typeof callback != 'function') {
			callback = Ext.emptyFn;
		}

		this.cost_type_combo.lastQuery = '';
		this.cost_type_combo.getStore().clearFilter();

		if (this.action != 'view') { //фильтрация не нужна в режиме просмотра
			var dt = date_field.getValue();
			if (!(dt instanceof Date)) {
				dt = new Date();
				dt.setHours(0, 0, 0, 0);
			}

			wnd.loadPersonRegisterStore(function() {
				var privilege_cost_id = wnd.getComboSelectedRecordValue(wnd.privilege_type_combo, 'WhsDocumentCostItemType_id');
				var register_type_list = new Array();

				wnd.person_register_store.each(function(record) {
					var correct_register = (!Ext.isEmpty(record.get('PersonRegisterType_id')));
					var correct_beg_date = (Ext.isEmpty(record.get('PersonRegister_setDate')) || dt >= record.get('PersonRegister_setDate'));
					var correct_end_date = (Ext.isEmpty(record.get('PersonRegister_disDate')) || dt <= record.get('PersonRegister_disDate'));

					if (correct_register && correct_beg_date && correct_end_date) {
						register_type_list.push(record.get('PersonRegisterType_id'));
					}
				});

				wnd.cost_type_combo.getStore().filterBy(function (record) {
					var is_llo = (record.get('WhsDocumentCostItemType_IsDlo') == 2);
					var correct_cost = (record.get('WhsDocumentCostItemType_id') == privilege_cost_id);
					var correct_register = record.get('PersonRegisterType_id').inlist(register_type_list);
					var correct_beg_date = (Ext.isEmpty(record.get('WhsDocumentCostItemType_begDate')) || dt >= record.get('WhsDocumentCostItemType_begDate'));
					var correct_end_date = (Ext.isEmpty(record.get('WhsDocumentCostItemType_endDate')) || dt <= record.get('WhsDocumentCostItemType_endDate'));

					return (is_llo && correct_beg_date && correct_end_date && (correct_cost || correct_register));
				});

				callback();
			});
		}
	},
	setDiagFilter: function() {
		var wnd = this;
		var base_form = this.findById('EvnReceptEditForm').getForm();
		var diag_combo = base_form.findField('Diag_id');
		var date_field = base_form.findField('EvnRecept_setDate');

		if (this.action != 'view') { //фильтрация не нужна в режиме просмотра
			this.loadPersonRegisterStore(function() {
				if(wnd.is_vzn) {
					var vzn_diag_list = new Array();
					var vzn_register_type_id = wnd.getComboSelectedRecordValue(wnd.cost_type_combo, 'PersonRegisterType_id');
					var dt = date_field.getValue();
					if (!(dt instanceof Date)) {
						dt = new Date();
						dt.setHours(0, 0, 0, 0);
					}

					//получаем список диагнозов из регистра ВЗН на дату
					wnd.person_register_store.each(function(record) {
						var correct_diag = (!Ext.isEmpty(record.get('Diag_id')) && !record.get('Diag_id').inlist(vzn_diag_list));
						var correct_register = (record.get('PersonRegisterType_id') == vzn_register_type_id);
						var correct_beg_date = (Ext.isEmpty(record.get('PersonRegister_setDate')) || dt >= record.get('PersonRegister_setDate'));
						var correct_end_date = (Ext.isEmpty(record.get('PersonRegister_disDate')) || dt <= record.get('PersonRegister_disDate'));

						if (correct_diag && correct_register && correct_beg_date && correct_end_date) {
							vzn_diag_list.push(record.get('Diag_id'));
						}
					});

					base_form.findField('Diag_id').setBaseFilter(function(record, id) {
						return (record.get('Diag_id') && record.get('Diag_id').inlist(vzn_diag_list));
					});

					//если в списке только один диагноз, выбираем его
					if (vzn_diag_list.length == 1) {
						diag_combo.getStore().load({
							callback: function() {
								diag_combo.getStore().each(function(record) {
									if (record.get('Diag_id') == vzn_diag_list[0]) {
										diag_combo.fireEvent('select', diag_combo, record, 0);
										diag_combo.fireEvent('blur', diag_combo);
									}
								});
							},
							params: { where: "where Diag_id = " + vzn_diag_list[0] }
						});
					}

					wnd.vzn_diag_list = vzn_diag_list;
				} else {
					base_form.findField('Diag_id').clearBaseFilter();
					base_form.findField('Diag_id').clearValue();
				}
			});
		}
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

            if (getRegionNick().inlist(['kz'])) {
                default_form_code = '132';
                allowedReceptFormCodes = ['132'];
            }

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

			if (!base_form.findField('ReceptType_id').disabled) {
				this.setReceptType();
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
				var index = base_form.findField('ReceptType_id').getStore().findBy(function (rec) {
					return (rec.get('ReceptType_Code') == 3);
				});
				if (index >= 0) {
					base_form.findField('ReceptType_id').setValue(base_form.findField('ReceptType_id').getStore().getAt(index).get('ReceptType_id'));
				}
			} else {
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
	setReceptDiscount: function() {
		var base_form = this.findById('EvnReceptEditForm').getForm();
		var discount_id = null;

		if (this.is_vzn) {
			// размер скидки 100%
			var index = base_form.findField('ReceptDiscount_id').getStore().findBy(function(rec) {
				return (Number(rec.get('ReceptDiscount_Code')) == 1);
			});
			if (index >= 0) {
				discount_id = base_form.findField('ReceptDiscount_id').getStore().getAt(index).get('ReceptDiscount_id');
			}
		} else {
			// получаем скидку из выбранной категории
			discount_id = this.getComboSelectedRecordValue(this.privilege_type_combo, 'ReceptDiscount_id');
		}

		base_form.findField('ReceptDiscount_id').setValue(discount_id);
	},
	setReceptFinance: function() { //автоматическая установка значения в поле ReceptFinance_id, в зависимости от выбранной категории, программы и источника финансирования
		var base_form = this.findById('EvnReceptEditForm').getForm();
		var recept_finance_combo = base_form.findField('ReceptFinance_id');
		var recept_finance_id = null;

		if (!Ext.isEmpty(this.privilege_type_combo.getValue())) {
			var is_onls = (this.getComboSelectedRecordValue(this.cost_type_combo, 'WhsDocumentCostItemType_Nick') == 'fl'); //fl - ОНЛС

			if (is_onls) {
				var df_nick = this.getComboSelectedRecordValue(this.drug_finance_combo, 'DrugFinance_SysNick');
				switch (df_nick) {
					case 'fed':
						recept_finance_id = 1; //1 - Федеральный бюджет
						break;
					case 'reg':
						recept_finance_id = 2; //2 - Субъект РФ
						break;
				}
			} else {
				recept_finance_id = this.getComboSelectedRecordValue(this.privilege_type_combo, 'ReceptFinance_id');
			}
		}

		recept_finance_combo.setValue(recept_finance_id);
		recept_finance_combo.fireEvent('change', recept_finance_combo, recept_finance_id);
	},
	setIsVzn: function() {
		var base_form = this.findById('EvnReceptEditForm').getForm();
		var is_vzn = (this.getComboSelectedRecordValue(this.cost_type_combo, 'WhsDocumentCostItemType_Nick') == 'vzn');

		if (this.is_vzn != is_vzn) {
			this.is_vzn = is_vzn;

			// фильтрация списка диагнозов
			this.setDiagFilter();

			// установка скидки
			this.setReceptDiscount();

			base_form.findField('DrugMnn_id').getStore().baseParams.EvnRecept_Is7Noz_Code = this.is_vzn ? 1 : 0;
			base_form.findField('Drug_id').getStore().baseParams.EvnRecept_Is7Noz_Code = this.is_vzn ? 1 : 0;
		}
	},
	setReceptValidDefaultValue: function() {
		var base_form = this.findById('EvnReceptEditForm').getForm();
		var date_field = base_form.findField('EvnRecept_setDate');
		var recept_valid_combo = base_form.findField('ReceptValid_id');
		var recept_date = Ext.util.Format.date(!Ext.isEmpty(date_field.getValue()) ? date_field.getValue() : new Date(), 'Y-m-d');
		var person_information = this.findById('EREF_PersonInformationFrame');
		var person_age = swGetPersonAge(person_information.getFieldValue('Person_Birthday'), recept_date);
		var sex_code = person_information.getFieldValue('Sex_Code');
		var is_retired = ((sex_code == 2 && person_age >= 55) || (sex_code == 1 && person_age >= 60)); //опредлеяем, пенсионер ли наш пациент

		if(this.action != 'view') {
			var index = recept_valid_combo.getStore().findBy(function(record) {
				if(recept_date >= '2016-01-01') {
					return is_retired ? (record.get('ReceptValid_Code') == 10) : (record.get('ReceptValid_Code') == 9);
				} else {
					return is_retired ? (record.get('ReceptValid_Code') == 2) : (record.get('ReceptValid_Code') == 1);
				}
			});
			if (index >= 0) {
				recept_valid_combo.setValue(recept_valid_combo.getStore().getAt(index).get('ReceptValid_id'));
			}
		}
	},
	setReceptValidFilter: function() {
		var base_form = this.findById('EvnReceptEditForm').getForm();
		var recept_date_field = base_form.findField('EvnRecept_setDate');
		var recept_valid_combo = base_form.findField('ReceptValid_id');
		var recept_form_combo = base_form.findField('ReceptForm_id');

		recept_valid_combo.getStore().clearFilter();

		if (this.action != 'view') { //фильтрация не нужна в режиме просмотра
			var dt = recept_date_field.getValue();
			if (!(dt instanceof Date)) {
				dt = new Date();
				dt.setHours(0, 0, 0, 0);
			}

			var recept_date = dt.format('Y-m-d');
			var recept_valid_id = recept_valid_combo.getValue();
			var recept_form_id = recept_form_combo.getValue();
			var recept_valid_code_array = new Array();

			if(recept_form_id == 2) { //1-МИ
				recept_valid_code_array = recept_date >= '2016-01-01' ? ['4','9','10','11'] : ['1','2'];
			} else {
				recept_valid_code_array = recept_date >= '2016-01-01' ? ['4','9','10','11'] : ['1','2','4','7'];
			}

			if (recept_date >= '2016-07-30') {
				switch (recept_form_id) {
					case 1:	//148-1/у-04(л), 148-1/у-06(л)
						recept_valid_code_array = ['9', '10', '11'];
						break;
					case 2: //1-МИ
						recept_valid_code_array = ['1', '2'];
						break;
					case 3: //107-1/у "Рецептурный бланк"
						recept_valid_code_array = ['8'];
						break;
					case 5: //148-1/у-88
						recept_valid_code_array = ['11'];
						break;
				}
			}

			if (recept_valid_code_array.length > 0) {
				recept_valid_combo.getStore().filterBy(function(rec) {
					return (rec.get('ReceptValid_Code').toString().inlist(recept_valid_code_array));
				});
			}

			//ищем сохраненное значение в отсортированном списке
			if (!Ext.isEmpty(recept_valid_id)) {
				var idx = recept_valid_combo.getStore().findBy(function(rec) {
					return (rec.get('ReceptValid_id') == recept_valid_id);
				});
				if (idx < 0) {
					recept_valid_id = null;
				}
			}

			if (Ext.isEmpty(recept_valid_id)) {
				//на случай если значение по-умолчанию не прописано, установим первое значенеи из списка доступных
				if (recept_valid_combo.getStore().getCount() > 0) {
					recept_valid_combo.setValue(recept_valid_combo.getStore().getAt(0).get('ReceptValid_id'));
				} else {
					recept_valid_combo.setValue(null);
				}

				// Устанавливаем значение по-умолчанию
				this.setReceptValidDefaultValue();
			}
		}
	},
	setPrivilegeTypeDefaultValue: function() {
		var privilege_type_id = null;
		var store = this.privilege_type_combo.getStore();

		if (store.getCount() > 0) {
			privilege_type_id = store.getAt(0).get('PrivilegeType_id');
		}

		if (this.action != 'view' && !Ext.isEmpty(privilege_type_id)) {
			this.privilege_type_combo.setValue(privilege_type_id);
			this.privilege_type_combo.fireEvent('change', this.privilege_type_combo, privilege_type_id);
		}
	},
	setWhsDocumentCostItemTypeDefaultValue: function() {
		var base_form = this.findById('EvnReceptEditForm').getForm();
		var date_field = base_form.findField('EvnRecept_setDate');

		if (this.action != 'view') {
			var dt = date_field.getValue();
			if (!(dt instanceof Date)) {
				dt = new Date();
				dt.setHours(0, 0, 0, 0);
			}

			var vzn_cost_id = null;
			var priv_cost_id = null;
			var priv_cost_list = new Array();

			this.privilege_type_combo.getStore().each(function(record) {
				if (!Ext.isEmpty(record.get('WhsDocumentCostItemType_id'))) {
					priv_cost_list.push(record.get('WhsDocumentCostItemType_id'));
				}
			});

			this.cost_type_combo.getStore().each(function(record) {
				if (Ext.isEmpty(vzn_cost_id) && record.get('WhsDocumentCostItemType_Nick') == 'vzn') { // проверка есть ли ВЗН
					vzn_cost_id = record.get('WhsDocumentCostItemType_id');
				}
				if (Ext.isEmpty(priv_cost_id) && record.get('WhsDocumentCostItemType_id').inlist(priv_cost_list)) { // проверка есть программа связанная с льготной категорией
					priv_cost_id = record.get('WhsDocumentCostItemType_id');
				}
			});

			var cost_id = !Ext.isEmpty(vzn_cost_id) ? vzn_cost_id : priv_cost_id

			if (!Ext.isEmpty(cost_id)) {
				this.cost_type_combo.setValue(cost_id);
				this.cost_type_combo.fireEvent('change', this.cost_type_combo, cost_id);
			}
		}
	},
    getLpuOuz: function(callback) {
		// Формируем серию рецепта
		var wnd = this;
		var lpu_id = getGlobalOptions().lpu_id;
		var lpu_ouz = null;

		if (Ext.isEmpty(this.LpuOuzData)) {
            this.LpuOuzData = new Object();
            this.LpuOuzData[lpu_id] = new Object({
                Lpu_Ouz: null
			});
		} else {
			if (!Ext.isEmpty(this.LpuOuzData[lpu_id]) && !Ext.isEmpty(this.LpuOuzData[lpu_id].Lpu_Ouz)) {
                lpu_ouz = this.LpuOuzData[lpu_id].Lpu_Ouz;
			} else {
                this.LpuOuzData[lpu_id] = new Object({
                    Lpu_Ouz: null
                });
			}
		}

		if (Ext.isEmpty(lpu_ouz)) {
            var lpu_store = new Ext.db.AdapterStore({
                autoLoad: false,
                dbFile: 'Promed.db',
                fields: [
                    { name: 'Lpu_id', type: 'int' },
                    { name: 'Lpu_Ouz', type: 'int' },
                    { name: 'Lpu_RegNomC2', type: 'int' },
                    { name: 'Lpu_RegNomN2', type: 'int' }
                ],
                key: 'Lpu_id',
                tableName: 'Lpu'
            });

            lpu_store.load({
					params: {
					where: "where Lpu_id = " + lpu_id
				},
                callback: function(records, options, success) {
                    lpu_ouz = null;

                    if (lpu_store.getCount() > 0) {
                        lpu_ouz = lpu_store.getAt(0).get('Lpu_Ouz');
                    }

                    wnd.LpuOuzData[lpu_id].Lpu_Ouz = lpu_ouz;

                    if(typeof callback == 'function'){
                        callback(lpu_ouz);
                    }
                }
            });
		} else {
            if(typeof callback == 'function'){
                callback(lpu_ouz);
            }
		}

		return true;
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
	loadFormFieldsStore: function(elements, options) {
		var base_form = this.findById('EvnReceptEditForm').getForm();

		// функция загрузки справочников для нужных элементов.
		if ( elements.length < 1 ) {
			this.show(options); 
		}
		else {
			var params = new Object();
			var sprName = elements.shift();

			base_form.findField(sprName).getStore().removeAll();

			base_form.findField(sprName).getStore().load({
				callback: function() {
					this.loadFormFieldsStore(elements, options);
				}.createDelegate(this),
				params: params
			});
		}
	},
	getComboSelectedRecordData: function(combo) {
		var value = combo.getValue();
		var data = new Object();
		if (value > 0) {
			var idx = combo.getStore().findBy(function(record) {
				return (record.get(combo.valueField) == value);
			})
			if (idx > -1) {
				Ext.apply(data, combo.getStore().getAt(idx).data);
			}
		}
		return data;
	},
	getComboSelectedRecordValue: function(combo, key) {
		var value = null;
		var data = this.getComboSelectedRecordData(combo);
		if (data && data[key] != undefined) {
			value = data[key];
		}
		return value;
	},
	getLoadMask: function(txt) {
		if ( Ext.isEmpty(txt) ) {
			txt = 'Подождите...';
		}

		if ( !this.loadMask ) {
			this.loadMask = new Ext.LoadMask(this.getEl(), { msg: txt });
		}

		return this.loadMask;
	},
	show: function() {
		sw.Promed.swEvnReceptEditWindow.superclass.show.apply(this, arguments);

		var win = this;

		var base_form = this.findById('EvnReceptEditForm').getForm();
		base_form.reset();

		if ( !arguments[0] ) {
			sw.swMsg.alert('Ошибка', 'Отсутствуют необходимые параметры', function() { this.hide(); }.createDelegate(this) );
			return false;
		}

		this.person_register_store.state = null;
		
		if ( this.formFirstShow == true ) {
			this.formFirstShow = false;

			this.getLoadMask("Загрузка справочников...").show();

			// Прогружаем справочники
			var comboList = [
				'ReceptFinance_id',
				'ReceptDiscount_id'
			];

			this.loadFormFieldsStore(comboList, arguments[0]);

			return false;
		}
		else {
			this.getLoadMask().hide();
		}

		this.action = null;
		this.callback = Ext.emptyFn;
		this.formStatus = 'edit';
		this.onHide = Ext.emptyFn;
		this.viewOnly = false;
		this.is_vzn = false;
		this.is_request = false;
		this.vzn_diag_list = new Array();
		this.recept_electronic_is_agree = null; //согласие пациента на выписку рецепта в форме электронного документа
		this.restore();
		this.center();
		this.maximize();

		if ( arguments[0].action ) {
			this.action = arguments[0].action;
		}

		if ( arguments[0].callback ) {
			this.callback = arguments[0].callback;
		}

		if ( arguments[0].onHide ) {
			this.onHide = arguments[0].onHide;
		}

		if ( arguments[0].streamInput ) {
			this.streamInput = arguments[0].streamInput;
		}

		if ( arguments[0].viewOnly ) {
			this.viewOnly = arguments[0].viewOnly;
		}

		this.userMedStaffFact = (this.streamInput == true || Ext.isEmpty(sw.Promed.MedStaffFactByUser.current) || sw.Promed.MedStaffFactByUser.current.ARMType == 'mstat' ? {} : sw.Promed.MedStaffFactByUser.current);
        if (this.userMedStaffFact.MedStaffFact_id) {
            this.UserMedStaffFact_id = this.userMedStaffFact.MedStaffFact_id;
        }
		// Если рецепт добавляется и к пользователю привязан врач...
		if ( this.action == 'add' && (this.UserMedStaffFact_id || this.UserMedStaffFacts) ) {
			// ... проверяем для врача возможность выписывать рецепты
			setMedStaffFactGlobalStoreFilter({
				allowLowLevel: 'yes',
				id: this.UserMedStaffFact_id,
				ids: this.UserMedStaffFacts,
				isDlo: true,
                onDate: getGlobalOptions().date,
				regionCode: (getGlobalOptions().region ? getGlobalOptions().region.number : '')
			});

			if ( swMedStaffFactGlobalStore.getCount() == 0 ) {
				sw.swMsg.alert('Ошибка', 'Вы не внесены в список врачей, работающих с ЛЛО.', function() { this.hide(); }.createDelegate(this) );
				return false;
			}
		}

		this.findById('EREF_DrugPanel').expand();
		this.findById('EREF_DrugRequestOtovPanel').collapse();
		this.findById('EREF_DrugRequestRowPanel').expand();
		this.findById('EREF_DrugRequestRowPanel').doLayout();
		this.findById('EREF_PrivilegePanel').expand();
		this.findById('EREF_ReceptPanel').expand();

		this.findById('EREF_DrugRequestOtovPanel').wasExpanded = false;

		var diag_combo = base_form.findField('Diag_id');
		var drug_combo = base_form.findField('Drug_id');
		var drug_is_mnn_combo = base_form.findField('Drug_IsMnn');
		var drug_mnn_combo = base_form.findField('DrugMnn_id');
		var drug_request_mnn_combo = base_form.findField('DrugRequestMnn_id');
		var evn_recept_set_date_field = base_form.findField('EvnRecept_setDate');
		var lpu_section_combo = base_form.findField('LpuSection_id');
		var med_personal_combo = base_form.findField('MedStaffFact_id');
		var org_farmacy_combo = base_form.findField('OrgFarmacy_id');
		var privilege_type_combo = base_form.findField('PrivilegeType_id');
		var recept_discount_combo = base_form.findField('ReceptDiscount_id');
		var recept_finance_combo = base_form.findField('ReceptFinance_id');
		var recept_type_combo = base_form.findField('ReceptType_id');

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
		recept_finance_combo.getStore().clearFilter();
		recept_discount_combo.clearValue();
		privilege_type_combo.clearValue();
		privilege_type_combo.disable();
		privilege_type_combo.getStore().clearFilter();
		privilege_type_combo.getStore().removeAll();
		this.cost_type_combo.clearValue();
		this.cost_type_combo.getStore().clearFilter();
		drug_is_mnn_combo.setValue(2);
		base_form.findField('DrugMnn_id').enable();
		drug_request_mnn_combo.clearValue();
		drug_request_mnn_combo.disable();
		drug_request_mnn_combo.getStore().removeAll();
		drug_mnn_combo.clearValue();
		drug_mnn_combo.disable();
		drug_mnn_combo.getStore().removeAll();
		drug_combo.clearValue();
		drug_combo.disable();
		drug_combo.getStore().removeAll();
		org_farmacy_combo.clearValue();
		org_farmacy_combo.disable();
		org_farmacy_combo.getStore().removeAll();
		base_form.findField('Drug_IsKEK').setValue(1); //1 - Нет
		base_form.findField('Drug_Price').setValue('');
		base_form.findField('EvnRecept_Kolvo').maxValue = undefined;
		base_form.findField('EvnRecept_Kolvo').setValue(1);
		base_form.findField('EvnRecept_Signa').setRawValue('');
		base_form.findField('EvnRecept_Signa').disable();
		base_form.findField('EvnRecept_Signa').setAllowBlank(true);
		if (getRegionNick() != 'kz') {
			base_form.findField('ReceptUrgency_id').clearValue();
		}
		recept_finance_combo.fireEvent('change', recept_finance_combo, null);

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

		if ( arguments[0].Diag_id )
			diag_id = arguments[0].Diag_id;

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


		this.findById('EREF_DrugRequestOtovGrid').getStore().baseParams = {
			Date: null,
			Person_id: person_id
		};

		this.findById('EREF_DrugRequestRowGrid').getStore().baseParams = {
			Date: null,
			DrugRequestRow_IsReserve: 1,
			MedPersonal_id: null,
			DrugProtoMnn_id: null,
			Person_id: person_id
		};

		drug_combo.getStore().baseParams = {
			Date: null,
			DrugMnn_id: 0,
			DrugRequestRow_IsReserve: 1,
			EvnRecept_Is7Noz_Code: 0,
			mode: 'all',
			MedPersonal_id: null,
			DrugProtoMnn_id: null,
			Person_id: person_id,
			ReceptFinance_Code: 0,
			ReceptType_Code: 0,
			RequestDrug_id: 0,
			PrivilegeType_id: 0,
			ignoreCheck: this.action == 'view'
		};
		drug_combo.lastQuery = '';

		drug_mnn_combo.getStore().baseParams = {
			Date: null,
			EvnRecept_Is7Noz_Code: 0,
			ReceptFinance_Code: 0,
			ReceptType_Code: 0,
			PrivilegeType_id: 0,
			DrugRequestRow_IsReserve: 1,
			MedPersonal_id: null,
			DrugProtoMnn_id: null,
			Person_id: person_id
		};
		drug_mnn_combo.lastQuery = '';

		drug_request_mnn_combo.getStore().baseParams = {
			Date: null,
			DrugRequestRow_IsReserve: 1,
			MedPersonal_id: 0,
			DrugProtoMnn_id: null,
			Person_id: person_id,
			ReceptFinance_Code: 0,
			ReceptType_Code: 0,
			PrivilegeType_id: 0
		};
		drug_request_mnn_combo.lastQuery = '';

		// прогрузим список аптек привязанных + любимую аптеку человека.
		org_farmacy_combo.getStore().baseParams.add_without_orgfarmacy_line = 0;
		org_farmacy_combo.getStore().load({
			params: {
				Person_id: person_id,
				Lpu_id: getGlobalOptions().lpu_id,
				onlyAttachLpu: 1
			},
			callback: function() {
				// выбираем любимую аптеку, если есть
				var index = org_farmacy_combo.getStore().findBy(function(rec) {
					return rec.get('OrgFarmacy_IsFavorite') && rec.get('OrgFarmacy_IsFavorite') == 2;
				});
				if ( index >= 0 ) {
					org_farmacy_combo.setValue(org_farmacy_combo.getStore().getAt(index).get('OrgFarmacy_id'));
				}
			}
		});

		privilege_type_combo.getStore().baseParams = {
			Person_id: person_id
		};

		base_form.setValues({
			EvnRecept_id: evn_recept_id,
			EvnRecept_pid: evn_recept_pid,
			Person_id: person_id,
			PersonEvn_id: person_evn_id,
			Server_id: server_id
		});

		//установка видимости полей связанных с заявкой
		this.setDrugRequestFieldsVisible();

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

				var new_date = Ext.util.Format.date(base_form.findField('EvnRecept_setDate').getValue(), 'Y-m-d');

				var LpuArr = [];
				if (!Ext.isEmpty(getGlobalOptions().lpu_id)) {
					//LpuArr.push(person_information.getFieldValue('Lpu_id'));
					LpuArr.push(getGlobalOptions().lpu_id);
					// в комбо с МО должны остаться только МО выписки и МО предшественника МО выписки, поэтому получим список МО предшественника
					Ext.Ajax.request({
						url: '/?c=EvnRecept&m=getLpuPrev',
						params: {
							//Lpu_id: person_information.getFieldValue('Lpu_id')
							Lpu_id: getGlobalOptions().lpu_id
						},
						callback: function(options, success, response) {
							var response_obj = Ext.util.JSON.decode(response.responseText);
							if (success && response_obj.length > 0 &&  response_obj[0].Lpu_id > 0) {
								response_obj.forEach(function(rec) {
									LpuArr.push(rec.Lpu_id);
								});
							}

							base_form.findField('Lpu_rid').getStore().clearFilter();
							base_form.findField('Lpu_rid').lastQuery = '';
							base_form.findField('Lpu_rid').getStore().filterBy(function(rec) {
								return rec.get('Lpu_id').inlist(LpuArr);
							});
						}
					});
				} else {
					base_form.findField('Lpu_rid').getStore().clearFilter();
					base_form.findField('Lpu_rid').lastQuery = '';
					base_form.findField('Lpu_rid').getStore().filterBy(function(rec) {
						return false;
					});
				}

				win.setReceptValidFilter();
			}
		});

		var index;

		base_form.findField('PrescrSpecCause_id').hideContainer();
		base_form.findField('PrescrSpecCause_id').allowBlank = true;
		base_form.findField('PrescrSpecCause_id').disable();

		base_form.findField('ReceptUrgency_id').hideContainer();
		base_form.findField('ReceptUrgency_id').disable();

		base_form.findField('EvnRecept_IsExcessDose').hideContainer();
		base_form.findField('PrescrSpecCause_cb').hideContainer();
		//Ext.getCmp('EREF_DrugResult').hide();

		switch ( this.action ) {
			case 'add':
				this.enableEdit(true);
				this.setTitle(WND_DLO_RCPTADD);
				Ext.getCmp('EREF_DrugResult').hide();
				Ext.getCmp('EREF_DrugResult').disable();
				base_form.findField('DrugRequestRow_IsReserve').fireEvent('change', base_form.findField('DrugRequestRow_IsReserve'), base_form.findField('DrugRequestRow_IsReserve').getValue());

				if ( !evn_recept_set_date ) {
					evn_recept_set_date = dt;
				}

                if (recept_type_id) {
                    recept_type_combo.disable();
                }
				base_form.findField('ReceptForm_id').getStore().load({
					callback: function() {
						evn_recept_set_date_field.setValue(evn_recept_set_date);
						evn_recept_set_date_field.fireEvent('change', evn_recept_set_date_field, evn_recept_set_date, null);

						recept_type_combo.setValue(recept_type_id);
						recept_type_combo.fireEvent('change', recept_type_combo, recept_type_id);
					}
				});

				/*recept_finance_combo.getStore().filterBy(function(rec) {
					return (rec.get('ReceptFinance_Code').toString().inlist([ '1', '2' ]));
				});*/

				lpu_section_combo.setValue(lpu_section_id);
                //med_personal_combo.setValue(med_personal_id);
                med_personal_combo.setValue(this.UserMedStaffFact_id);
                //log([this.UserMedStaffFact_id, med_personal_id, swMedStaffFactGlobalStore.getCount(), swMedStaffFactGlobalStore]);

                privilege_type_combo.setValue(privilege_type_id);

				/*evn_recept_set_date_field.setValue(evn_recept_set_date);
				evn_recept_set_date_field.fireEvent('change', evn_recept_set_date_field, evn_recept_set_date, null);*/



				if ( diag_id ) {
					diag_combo.getStore().load({
						callback: function() {
							diag_combo.getStore().each(function(record) {
								if ( record.get('Diag_id') == diag_id ) {
									diag_combo.fireEvent('select', diag_combo, record, 0);
									diag_combo.fireEvent('blur', diag_combo);
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

                this.setVKProtocolFieldsVisible();
				this.setReceptTypeFilter();
				if (getRegionNick() != 'kz') {
					base_form.findField('ReceptUrgency_id').enable();
					base_form.findField('ReceptUrgency_id').showContainer();
				}
			break;

			case 'edit':
				this.action = 'view';

			case 'view':
				this.enableEdit(false);
				this.setTitle(WND_DLO_RCPTVIEW);

				if (base_form.findField('ReceptForm_id').getStore().getCount() == 0) {
                    base_form.findField('ReceptForm_id').getStore().load();
				}

				base_form.load({
					failure: function(a,b,c) {
                        var response_obj = Ext.util.JSON.decode(b.response.responseText);
                        loadMask.hide();
                        if(!Ext.isEmpty(response_obj) && !Ext.isEmpty(response_obj.Error_Msg) && response_obj.Error_Msg != 'Вы не можете открыть рецепт, созданный в другом ЛПУ')
						    sw.swMsg.alert('Ошибка', 'Ошибка при загрузке данных формы', function() { this.hide(); }.createDelegate(this) );
                        else
                            this.hide();
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

						if (this.viewOnly) {
							this.buttons[1].hide();
							this.buttons[3].hide();
						} else {
							this.buttons[1].show();
							this.buttons[3].show();
						}

						if (!Ext.isEmpty (response_obj[0].PrescrSpecCause_id)) {
							base_form.findField('PrescrSpecCause_cb').showContainer();
							base_form.findField('PrescrSpecCause_cb').setValue(1);
							base_form.findField('PrescrSpecCause_cb').disable();
							base_form.findField('PrescrSpecCause_id').showContainer();
							base_form.findField('PrescrSpecCause_id').allowBlank = false;
							base_form.findField('PrescrSpecCause_id').disable();
						}

						if (response_obj[0].EvnRecept_IsSigned) {
							base_form.findField('EvnRecept_IsSigned').setValue(response_obj[0].EvnRecept_IsSigned);
						}

						if (getRegionNick() != 'kz') {
							if (!Ext.isEmpty(response_obj[0].ReceptUrgency_id)) {
								base_form.findField('ReceptUrgency_id').showContainer();
							}
						}
						var drug_id = drug_combo.getValue();
						var drug_mnn_id = drug_mnn_combo.getValue();
						var drug_request_row_id = drug_request_mnn_combo.getValue();
						var evn_recept_is_extemp = base_form.findField('EvnRecept_IsExtemp').getValue().toString();
						var med_personal_rid = base_form.findField('MedPersonal_rid').getValue();
						var recept_finance_code = 0;
						var recept_finance_id = recept_finance_combo.getValue();
						var recept_type_code = 0;

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

						this.findById('EREF_DrugRequestRowGrid').getStore().baseParams.Date = evn_recept_set_date;
						this.findById('EREF_DrugRequestOtovGrid').getStore().baseParams.Date = evn_recept_set_date;

						var recept_type_record = recept_type_combo.getStore().getById(recept_type_id);

						if ( recept_type_record ) {
							recept_type_code = recept_type_record.get('ReceptType_Code');
						}
						if((recept_type_code == 2 || (recept_type_code == 3 && base_form.findField('EvnRecept_IsSigned').getValue() == 2)) && (!getWnd('swWorkPlaceMZSpecWindow').isVisible())){
							win.buttons[3].show();
						}
						else
						{
							win.buttons[3].hide();
						}
						if(getWnd('swWorkPlaceMZSpecWindow').isVisible())
							win.buttons[1].hide();
						else
							win.buttons[1].show();
                        lpu_section_combo.lastQuery = '';
                        med_personal_combo.lastQuery = '';
                        if ('view' == this.action) {
                            swMedStaffFactGlobalStore.clearFilter();
                            swLpuSectionGlobalStore.clearFilter();
                        } else {
                            setLpuSectionGlobalStoreFilter({
                                allowLowLevel: 'yes',
                                isDlo: true,
                                onDate: evn_recept_set_date,
                                regionCode: (getGlobalOptions().region ? getGlobalOptions().region.number : '')

                            });
                            setMedStaffFactGlobalStoreFilter({
                                allowLowLevel: 'yes',
                                isDlo: true,
                                onDate: evn_recept_set_date,
                                regionCode: (getGlobalOptions().region ? getGlobalOptions().region.number : '')
                            });
                        }
						lpu_section_combo.getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));
						med_personal_combo.getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));

						var lpu_section_record = lpu_section_combo.getStore().getById(lpu_section_id);

						if ( !lpu_section_record ) {
							lpu_section_combo.clearValue();
							lpu_section_combo.fireEvent('change', lpu_section_combo, -1);
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
						} else if ('view' == this.action && lpu_section_id && med_personal_id && 0 == swMedStaffFactGlobalStore.getCount()) {
                            med_personal_combo.getStore().load({
                                callback: function() {
                                    index = med_personal_combo.getStore().findBy(function(rec) {
                                        if ( rec.get('LpuSection_id') == lpu_section_id && rec.get('MedPersonal_id') == med_personal_id ) {
                                            return true;
                                        }
                                        else {
                                            return false;
                                        }
                                    });
                                    if ( index >= 0 ) {
                                        med_personal_combo.setValue(med_personal_combo.getStore().getAt(index).get('MedStaffFact_id'));
                                    } else {
                                        log(index, lpu_section_id, med_personal_id);
                                        med_personal_combo.clearValue();
                                        med_personal_combo.fireEvent('change', med_personal_combo, -1);
                                    }
                                },
                                params: {
                                    MedPersonal_id: med_personal_id,
                                    LpuSection_id: lpu_section_id
                                }
                            });
                        } else {
                            log(this.action, lpu_section_id, med_personal_id, swMedStaffFactGlobalStore.getCount());
							med_personal_combo.clearValue();
							med_personal_combo.fireEvent('change', med_personal_combo, -1);
						}

						if ( med_personal_rid ) {
							base_form.findField('MedPersonal_rid').getStore().load({
								callback: function() {
									if ( base_form.findField('MedPersonal_rid').getStore().getCount() == 1 ) {
										base_form.findField('MedPersonal_rid').setValue(base_form.findField('MedPersonal_rid').getStore().getAt(0).get('MedPersonal_id'));
									}
								},
								params: {
									Date: evn_recept_set_date,
									MedPersonal_rid: med_personal_rid,
									Person_id: base_form.findField('Person_id').getValue()
								}
							});
						}

						//установка видимости полей связанных с заявкой
						win.setDrugRequestFieldsVisible();

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
								drug_combo.getStore().baseParams.PrivilegeType_id = privilege_type_id;
								
								drug_mnn_combo.getStore().baseParams.Date = evn_recept_set_date;
								drug_mnn_combo.getStore().baseParams.ReceptType_Code = recept_type_code;
								drug_mnn_combo.getStore().baseParams.PrivilegeType_id = privilege_type_id;

								switch ( evn_recept_is_extemp ) {
									case '1':
										drug_combo.getStore().load({
											callback: function(records, options, success) {
												drug_combo.setValue(drug_id);

												var selected_record = drug_combo.getStore().getById(drug_id);

												if ( selected_record ) {
													//base_form.findField('Drug_Price').setValue(selected_record.get('Drug_Price'));
													if(selected_record.get('Drug_IsKEK') == 2) { //если у выбранного препарата проставлен признак выписки через ВК
														base_form.findField('Drug_IsKEK').setValue(2); //2 - Да
													}
													this.setVKProtocolFieldsVisible();
												}
												else {
													drug_combo.clearValue();
												}

												recept_type_combo.disable();
												base_form.findField('EvnRecept_Num').disable();
												base_form.findField('EvnRecept_Ser').disable();

												if ( drug_request_row_id ) {
													drug_request_mnn_combo.getStore().load({
														callback: function() {
															if ( drug_request_mnn_combo.getStore().getCount() == 1 ) {
																drug_request_mnn_combo.setValue(drug_request_mnn_combo.getStore().getAt(0).get('id'));
															}
															else {
																drug_request_mnn_combo.clearValue();
															}

															loadMask.hide();

															base_form.clearInvalid();

															this.buttons[this.buttons.length - 1].focus();
														}.createDelegate(this),
														params: {
															DrugRequestRow_id: drug_request_row_id
														}
													});
												}
												else {
													drug_mnn_combo.getStore().load({
														callback: function(records, options, success) {
															drug_mnn_combo.setValue(drug_mnn_id);

															loadMask.hide();

															base_form.clearInvalid();

															this.buttons[this.buttons.length - 1].focus();
														}.createDelegate(this),
														params: {
															DrugMnn_id: drug_mnn_id
														}
													});
												}
											}.createDelegate(this),
											params: {
												Drug_id: drug_id
											}
										});
									break;

									case '2':

									break;

									default:
										sw.swMsg.alert('Ошибка', 'Неверное значение признака экстемпорального рецепта', function() { this.hide(); }.createDelegate(this) );
									break;
								}
							}.createDelegate(this)
						});

						/*if (base_form.findField('ReceptWrongDelayType_id').getValue() != 3) {
							Ext.getCmp('EREF_DrugWrong').show();
						}*/
						Ext.getCmp('EREF_DrugResult').show();
						Ext.getCmp('EREF_DrugResult').disable();
					}.createDelegate(this),
					url: C_EVNREC_LOAD
				});
			break;

			default:
				sw.swMsg.alert('Ошибка', 'Неверно указан режим открытия формы', function() { this.hide(); }.createDelegate(this) );
			break;
		}
	},
	title: WND_DLO_RECADD,
	width: 700
});
