/**
 *	swEvnReceptRlsEditWindow - окно редактирования рецепта с использованием справочника РЛС.
 *
 *	PromedWeb - The New Generation of Medical Statistic Software
 *	http://swan.perm.ru/PromedWeb
 *
 *
 *	@package      DLO.Saratov
 *	@access       public
 *	@copyright    Copyright (c) 2009 Swan Ltd.
 *	@author       Stas Bykov aka Savage (savage@swan.perm.ru)
 *	@version      28.02.2013
 *	@comment      Префикс для id компонентов ERREF (EvnReceptRlsEditForm)
 */

sw.Promed.swEvnReceptRlsEditWindow = Ext.extend(sw.Promed.BaseForm, {
	action: null,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	collapsible: true,
	doCopy: function(ignoreSave) {
		var win = this,
			base_form = win.FormPanel.getForm(),
			loadMask = new Ext.LoadMask(win.getEl(), { msg: LOADING_MSG });

		loadMask.show();
		Ext.Ajax.request({
			callback: function(options, success, response) {
				loadMask.hide();
				if (success && getRegionNick() == 'msk' && win.userMedStaffFact.ARMForm == 'swWorkPlacePolkaLLOWindow') {
					var response_obj = Ext.util.JSON.decode(response.responseText);
					if (!response_obj.EvnVizitPL_id || Ext.isEmpty(response_obj.EvnVizitPL_id)) {
						sw.swMsg.alert('Ошибка', 'Копирование рецепта доступно только при наличии посещения с текущей датой. Создайте посещение и повторите попытку');
						return;
					} else {
						base_form.findField('EvnRecept_pid').setValue(response_obj.EvnVizitPL_id);
						base_form.findField('MedPersonal_id').setValue(response_obj.MedPersonal_id);
						base_form.findField('MedStaffFact_id').setValue(response_obj.MedStaffFact_id);
					}
				}

				if ( this.action == 'add' /*|| (this.action == 'edit' && base_form.findField('EvnRecept_IsSigned').getValue() != '2' && ignoreSave != true)*/ ) {
					this.doSave({
						checkPersonAge: true,
						checkPersonDeadDT: true,
						checkPersonSnils: true,
						copy: true,
						print: false,
						sign: false
					});
				}
				else {
					// Открыть поля для редактирования
					this.action = 'add';
					this.setTitleByAction();
					this.enableEdit(true);

					base_form.findField('EvnRecept_setDate').setValue(new Date());

					Ext.getCmp('EREF_DrugResult').hide();
					Ext.getCmp('EREF_DrugResult').disable();

		            this.setEvnCourseTreatFieldsVisible();

					base_form.findField('ReceptType_id').fireEvent('change', base_form.findField('ReceptType_id'), base_form.findField('ReceptType_id').getValue());

					if (getRegionNick() == 'msk') {
						base_form.findField('EvnRecept_Signa').focus(true);
					}
					else {
						base_form.findField('DrugComplexMnn_id').clearValue();
						base_form.findField('DrugComplexMnn_id').getStore().removeAll();
						base_form.findField('DrugComplexMnn_id').fireEvent('change', base_form.findField('DrugComplexMnn_id'), null);
						base_form.findField('EvnRecept_Ser').focus(true);
					}
					base_form.findField('EvnRecept_id').setValue(0);
					base_form.findField('ReceptDelayType_id').setValue(null);
					base_form.findField('EvnRecept_IsSigned').setValue(1);
					base_form.findField('EvnRecept_IsPrinted').setValue(1);
					base_form.findField('EvnRecept_Kolvo').maxValue = undefined;
					base_form.findField('EvnRecept_Kolvo').setValue(getRegionNick() != 'msk' ? 1 : null);
					base_form.findField('EvnRecept_Signa').setRawValue('');

					//this.buttons[2].hide(); // Напечатать
					//this.buttons[3].show(); // Подписать

					var ReceptForm_id = Ext.isEmpty(base_form.findField('ReceptForm_id').getValue())?base_form.findField('ReceptForm_id').getValue():0;
					var new_date = base_form.findField('EvnRecept_setDate').getValue().format('Y-m-d');
					if(ReceptForm_id == 2) {
						base_form.findField('ReceptValid_id').getStore().filterBy(function (rec) {
							return (rec.get('ReceptValid_Code').toString().inlist(new_date >= '2016-01-01' ? ['4', '9', '10', '11'] : ['1', '2']));
						});
					} else {
						base_form.findField('ReceptValid_id').getStore().filterBy(function (rec) {
							return (rec.get('ReceptValid_Code').toString().inlist(new_date >= '2016-01-01' ? ['4', '9', '10', '11'] : ['1', '2', '4', '7']));
						});
					}
					if (getRegionNick() == 'kz') {
						this.refreshFieldsVisibility(['ReceptValid_id']);
					}

					this.showPrintButton(base_form.findField('ReceptType_id').getValue() == 2);

					//добавил в рамках задачи https://redmine.swan-it.ru/issues/166119, иначе при создании копии рецепта, запрос на загрузку стора "Торговое наименование"
					// содержит в передаваемых параметрах Drug_rlsid прошлого рецепта, и загружается только этот препарат
					base_form.findField('Drug_rlsid').getStore().baseParams.Drug_rlsid = '';

					base_form.findField('ReceptForm_id').fireEvent('change', base_form.findField('ReceptForm_id'), base_form.findField('ReceptForm_id').getValue());
					if (getRegionNick() != 'kz') {
						base_form.findField('ReceptUrgency_id').enable();
						base_form.findField('ReceptUrgency_id').clearValue();
					}
				}
			}.createDelegate(win),
			params: {Person_id: base_form.findField('Person_id').getValue()},
			url: '/?c=EvnVizit&m=getLastEvnVisitPLToday'
		});
	},
	refreshFieldsVisibility: function(fieldNames) {
		var win = this;
		var base_form = win.FormPanel.getForm();
		if (typeof fieldNames == 'string') fieldNames = [fieldNames];

		var action = win.action;
		var Region_Nick = getRegionNick();

		var createDT = function(date, time) {
			var dt = (date instanceof Date)?date:new Date();
			var t = (!Ext.isEmpty(time)?time:'00:00').split(':');
			dt.setHours(t[0], t[1], 0, 0);
			return dt;
		};

		base_form.items.each(function(field){
			if (!Ext.isEmpty(fieldNames) && !field.getName().inlist(fieldNames)) return;

			var value = field.getValue();
			var allowBlank = null;
			var visible = null;
			var enable = null;
			var filter = null;

			var date20160730 = new Date(2016, 6, 30); // 30.07.2016
			var EvnSection_setDate = base_form.findField('EvnRecept_setDate').getValue();

			var set_or_cur_date = createDT(EvnSection_setDate);

			switch(field.getName()) {
				case 'ReceptValid_id':
					var filterDate = function(record) {
						return (
							(!record.get('ReceptValid_begDT') || record.get('ReceptValid_begDT') <= set_or_cur_date) &&
							(!record.get('ReceptValid_endDT') || record.get('ReceptValid_endDT') > set_or_cur_date)
						);
					};
					if (Region_Nick.inlist(['kz'])) {
						field.clearFilter();
						filter = function(record){
							var code = String(record.get('ReceptValid_Code'));
							return filterDate(record) && code.inlist(['1','7']);
						};
						if (Ext.isEmpty(value)) {
							var index = field.getStore().findBy(function(record) {
								return filter(record) && record.get('ReceptValid_Code') == 1;
							});
							var record = field.getStore().getAt(index);
							if (record) value = record.get('ReceptValid_id');
						}
					}
					break;
			}

			if (visible === false && win.formLoaded) {
				value = null;
			}
			if (value != field.getValue()) {
				field.setValue(value);
				field.fireEvent('change', field, value);
			}
			if (allowBlank !== null) {
				field.setAllowBlank(allowBlank);
			}
			if (visible !== null) {
				field.setContainerVisible(visible);
			}
			if (enable !== null) {
				field.setDisabled(!enable || action == 'view');
			}
			if (typeof filter == 'function' && field.store) {
				field.lastQuery = '';
				if (typeof field.setBaseFilter == 'function') {
					field.setBaseFilter(filter);
				} else {
					field.store.filterBy(filter);
				}
			}
		});
	},
	provideEvnRecept: function() {
		var current_window = this;
		var base_form = current_window.FormPanel.getForm();
		var params = new Object();
		var m_store = sw.Promed.MedStaffFactByUser.store;

		if (base_form.findField('Drug_rlsid').getValue() <= 0) {
			Ext.Msg.alert('Сообщение', 'Обеспечение рецепта невозможно. Отсутсвует информация о медикаменте.');
			return false;
		}

		//current_window.buttons[4].disable();
		current_window.buttons[3].disable();

		params.Contragent_id = getGlobalOptions().Contragent_id;
		params.EvnRecept_id = base_form.findField('EvnRecept_id').getValue();
		params.Drug_id = base_form.findField('Drug_rlsid').getValue();
		params.Drug_Name = base_form.findField('Drug_rlsid').getFieldValue('Drug_Name');
		params.EvnRecept_Kolvo = base_form.findField('EvnRecept_Kolvo').getValue();
		params.MedService_id = null;
		if (m_store) {
			var idx = m_store.findBy(function(rec) { return rec.get('MedServiceType_SysNick') == 'dpoint'; });
			if (idx >= 0) {
				params.MedService_id = m_store.getAt(idx).get('MedService_id');
			}
		}

		params.onHide = function() {
			//current_window.buttons[4].enable();
			current_window.buttons[3].enable();
		}

		params.callback = function() {
			current_window.callback();
			current_window.hide();
		}

		//проверка рецепта по сроку годности
		Ext.Ajax.request({
			callback: function(options, success, response) {
				if (success) {
					var response_obj = Ext.util.JSON.decode(response.responseText);
					switch(response_obj) {
						case 'true':
							//предварительная проверка наличия медикамента на остатках
							Ext.Ajax.request({
								callback: function(options, success, response) {
									if (success) {
										var response_obj = Ext.util.JSON.decode(response.responseText);
										var need = params.EvnRecept_Kolvo;
										for(var i = 0; i < response_obj.length; i++) {
											need -= response_obj[i].DrugOstatRegistry_Kolvo;
											if (need <= 0) {
												break;
											}
										}
										if (need <= 0) { //если остатков достаточно для обеспечения медикаментов - открываем форму для обеспечения
											getWnd('swEvnReceptRlsProvideWindow').show(params);
										} else { // иначе ставим на отсрочку
											var ReceptDelayType_id = base_form.findField('ReceptDelayType_id').getValue();
											if (ReceptDelayType_id != 2) { //Если рецепт еще не отсрочке
												current_window.putEvnReceptOnDelay({msg: 'На остатках недостаточно медикамента. Поставить рецепт на отсрочку?'});
											} else {
												sw.swMsg.alert('Ошибка', 'На остатках недостаточно медикамента для обеспечения рецепта');
											}
										}
									} else {
										sw.swMsg.alert('Ошибка', 'При проверке наличия медикамента на остатках возникли ошибки');
									}
								},
								params: {
									EvnRecept_id: params.EvnRecept_id,
									MedService_id: params.MedService_id
								},
								url: '/?c=Farmacy&m=getDrugOstatForProvide'
							});
							break;
						case 'false':
							sw.swMsg.alert('Ошибка', 'Рецепт не может быть обеспечен, так как срок его действия истек');
							break;
						case 'error':
							sw.swMsg.alert('Ошибка', 'При проверке срока годности рецепта возникли ошибки');
							break;
					}
				} else {
					sw.swMsg.alert('Ошибка', 'При проверке срока годности рецепта возникли ошибки');
				}
			},
			params: {
				EvnRecept_id: params.EvnRecept_id,
				Date: Ext.util.Format.date(new Date(), 'd.m.Y')
			},
			url: '/?c=EvnRecept&m=checkReceptValidByDate'
		});
	},
	putEvnReceptOnDelay: function() { // Постановка рецепта на отсрочку
		var current_window = this;
		var base_form = current_window.FormPanel.getForm();
		var params = new Object();
		var evn_recept_id = 0;
		var evn_recept_obr_date = new Date();
		var msg = 'Рецепт попадает в разряд отсроченных. Продолжить?';

		if (arguments[0] && arguments[0].msg && arguments[0].msg != '') {
			msg = arguments[0].msg;
		}

		//current_window.buttons[5].disable();
		current_window.buttons[4].disable();

		evn_recept_id = base_form.findField('EvnRecept_id').getValue();
		params.EvnRecept_obrDate = Ext.util.Format.date(evn_recept_obr_date, 'd.m.Y');
		params.EvnRecept_id = evn_recept_id;
		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId, text, obj) {
				if ( buttonId == 'yes' ) {
					var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Подождите, выполняется постановка рецепта на отсрочку..." });
					loadMask.show();
					Ext.Ajax.request({
						callback: function(options, success, response) {
							//current_window.buttons[5].enable();
							current_window.buttons[4].enable();
							loadMask.hide();
							if (success) {
								var response_obj = Ext.util.JSON.decode(response.responseText);
								if ( response_obj.Error_Msg && response_obj.Error_Msg.toString().length > 0 ) {
									sw.swMsg.alert('Ошибка', response_obj.Error_Msg, function() { this.hide(); }.createDelegate(this) );
									return false;
								}
								current_window.callback();
								current_window.hide();
								sw.swMsg.alert('Сообщение', 'Рецепт был успешно поставлен на отсрочку', function() { }.createDelegate(this) );
							} else {
								sw.swMsg.alert('Ошибка', 'Ошибка при постановке рецепта на отсрочку');
							}
						}.createDelegate(this),
						params: params,
						url: '/?c=Farmacy&m=putEvnReceptOnDelay'
					});
				} else {
					//current_window.buttons[5].enable();
					current_window.buttons[4].enable();
				}
			}.createDelegate(this),
			icon: Ext.MessageBox.QUESTION,
			msg: msg,
			title: 'Подтверждение'
		});

		return true;
	},
	doSave: function(options) {
		log(['dosave', options]);
		// options @Object
		// options.checkPersonAge @Boolean
		// options.checkPersonDeadDT @Boolean
		// options.checkPersonSnils @Boolean
		// options.copy @Boolean 
		// options.print @Boolean Вызывать печать рецепта, если true
		// options.sign @Boolean Подписать рецепт, если true
		// options.callback @Function Обратный вызов для сабмита

		if ( !options || typeof options != 'object' ) {
			return false;
		}

		if ( this.formStatus == 'save' || this.action == 'view' ) {
			return false;
		}

		this.formStatus = 'save';
        if (!options.IsOtherDiag) {
            options.IsOtherDiag = false;
        }
        if (!options.checkEvnMatterRecept) {
            options.checkEvnMatterRecept = false;
        }
        if (!options.checkFarmacyRlsOstatListOnlsFed) { //теперь более не используется #193378
            options.checkFarmacyRlsOstatListOnlsFed = false;
        }
        if (!options.checkSelectedOstatByZero) {
            options.checkSelectedOstatByZero = false;
        }
        if (!options.checkEvnVisitPLDiag) {
            options.checkEvnVisitPLDiag = false;
        }
        if (!options.checkLastPersonPrivilegeModeration) {
            options.checkLastPersonPrivilegeModeration = false;
        }
        if (!options.checkReceptKardioReissue) {
            options.checkReceptKardioReissue = false;
        }
        if (!options.checkReceptKardioTicagrelor) {
            options.checkReceptKardioTicagrelor = false;
        }
        if (!options.checkReceptKardioSetDate) {
            options.checkReceptKardioSetDate = false;
        }

		var base_form = this.FormPanel.getForm();
		var index;
		var postParams = new Object();
		var record;
		var win = this;

		var SeriyaCombo = base_form.findField('EvnRecept_Ser');
		if (Ext.isEmpty(SeriyaCombo)) {
			sw.swMsg.alert('Ошибка', 'Сохранение невозможно. Не заполнено поле "Серия". Если серия рецепта задается нумератором, проверьте правильность настроек нумератора');
			return false;
		}

		if ( Ext.isEmpty(this.PersonInfo.getFieldValue('Person_RAddress')) ) {
			this.formStatus = 'edit';
			sw.swMsg.alert('Ошибка', 'Сохранение невозможно [Не задан адрес регистрации]');
			return false;
		}
		
		if ( getRegionNick() != 'kz' && Ext.isEmpty(this.PersonInfo.getFieldValue('Polis_begDate')) ) { //для Казахстана проверка отключена #131846
			this.formStatus = 'edit';
			sw.swMsg.alert('Ошибка', 'У данного пациента отсутствует полис');
			return false;
		}

		if ( getRegionNick() == 'kz' && Ext.isEmpty(this.PersonInfo.getFieldValue('Person_Inn')) ) {
			this.formStatus = 'edit';
			sw.swMsg.alert('Ошибка', 'Сохранение рецепта невозможно – у пациента не указан ИИН.  Заполните ИИН и повторите сохранение рецепта');
			return false;
		}

		if ( options.checkPersonSnils && Ext.isEmpty(this.PersonInfo.getFieldValue('Person_Snils')) ) {
			/*sw.swMsg.show({
				buttons: Ext.Msg.YESNO,
				fn: function ( buttonId ) {
					this.formStatus = 'edit';

					if ( buttonId == 'yes' ) {
						this.doSave({
							checkPersonAge: options.checkPersonAge,
							checkPersonDeadDT: options.checkPersonDeadDT,
							checkPersonSnils: false,
							copy: options.copy,
							print: options.print,
							sign: options.sign
						});
					}
				}.createDelegate(this),
				msg: 'У пациента не задан СНИЛС. Продолжить сохранение рецепта?',
				title: 'Проверка СНИЛС'
			});*/
			// Вычисляем значение ReceptFinance_id
			var pt_combo = base_form.findField('PrivilegeType_id');
			var index = pt_combo.getStore().findBy(function(rec) { return rec.get('PrivilegeType_id') == pt_combo.getValue(); });
			var ReceptFinance_id = -1;
			if (index >= 0) {
				ReceptFinance_id = pt_combo.getStore().getAt(index).get('ReceptFinance_id');
			}
			if((getGlobalOptions().region.nick.inlist(['perm','ufa'])) || (getGlobalOptions().region.nick.inlist(['khak','saratov']) && ReceptFinance_id==1)) //https://redmine.swan.perm.ru/issues/79194
			{
				sw.swMsg.alert('Ошибка', 'У данного пациента отсутствует СНИЛС', function() {
					win.formStatus = 'edit';
					win.findById('ERREF_PersonInformationFrame').panelButtonClick(2);
				});
				return false;
			}
		}

		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					this.formStatus = 'edit';
					this.FormPanel.getFirstInvalidEl().focus(false);
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
			Person_deadDT = this.PersonInfo.getFieldValue('Person_deadDT');

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
									print: options.print,
									sign: options.sign,
									callback: options.callback
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

		var person_age = swGetPersonAge(this.PersonInfo.getFieldValue('Person_Birthday'), EvnRecept_setDate);

		if ( person_age == -1 ) {
			this.formStatus = 'edit';
			sw.swMsg.alert('Ошибка', 'Проверьте правильность ввода даты выписки рецепта и даты рождения пациента. Возможно, дата рождения пациента больше даты выписки рецепта.');
			return false;
		}

		// https://redmine.swan.perm.ru/issues/4091
		// https://redmine.swan.perm.ru/issues/4371
		if ( options.checkPersonAge ) {
			index = base_form.findField('ReceptValid_id').getStore().findBy(function(rec) {
				return (rec.get('ReceptValid_id') == base_form.findField('ReceptValid_id').getValue());
			});
			record = base_form.findField('ReceptValid_id').getStore().getAt(index);

			if ( !record ) {
				this.formStatus = 'edit';
				sw.swMsg.alert('Ошибка', 'Не заполнено поле "Срок действия рецепта".', function() { base_form.findField('ReceptValid_id').focus(true); });
				return false;
			}

			var sex_code = this.PersonInfo.getFieldValue('Sex_Code');

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
			if (getRegionNick() != 'kz' && ( record.get('ReceptValid_Code').inlist([1,4,9,11]) ) && ((sex_code == 1 && person_age >= 60) || (sex_code == 2 && person_age >= 55) || hasLgotType83or84) ) {
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
								print: options.print,
								sign: options.sign,
								callback: options.callback
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
			// и не имеет категорий 83 (инвалид 1 группы) и 84 (дети-инвалиды)
			// выдавать информацию "Пациенту нельзя выписать рецепт сроком действия на 3 месяца, т.к. он не достиг пенсионного возраста (женщины 55 лет, мужчины 60 лет) и не имеет категорий 83 (инвалид 1 группы) и 84 (дети-инвалиды)".
			// После этого возвращать на форму " Льготные рецепты: добавление" , для исправления срока действия рецепта на 1 месяц. (refs #7366)
			if ( (record.get('ReceptValid_Code') == 2 || record.get('ReceptValid_Code') == 10) && ((sex_code == 1 && person_age < 60) || (sex_code == 2 && person_age < 55)) && !hasLgotType83or84 && !this.isKardio ) {
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
								print: options.print,
								sign: options.sign,
								callback: options.callback
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

		index = base_form.findField('MedStaffFact_id').getStore().findBy(function(rec) {
			return (rec.get('MedStaffFact_id') == base_form.findField('MedStaffFact_id').getValue());
		});

		if ( index == -1 ) {
			this.formStatus = 'edit';
			sw.swMsg.alert('Ошибка', 'Не выбран врач', function() { base_form.findField('MedStaffFact_id').focus(true); });
			loadMask.hide();
			return false;
		}

		base_form.findField('MedPersonal_id').setValue(base_form.findField('MedStaffFact_id').getStore().getAt(index).get('MedPersonal_id'));

		if (this.isKardio) {
            postParams.isKardio = 1;
		}

		// Вычисляем значение ReceptFinance_id
		var pt_combo = base_form.findField('PrivilegeType_id');
		var index = pt_combo.getStore().findBy(function(rec) { return rec.get('PrivilegeType_id') == pt_combo.getValue(); });

		if (index >= 0) {
			postParams.ReceptFinance_id = pt_combo.getStore().getAt(index).get('ReceptFinance_id');
		} else {
			postParams.ReceptFinance_id = -1;
		}

		// Вычисляем значение EvnRecept_Is7Noz
		index = base_form.findField('WhsDocumentCostItemType_id').getStore().findBy(function(rec) {
			return (rec.get(base_form.findField('WhsDocumentCostItemType_id').valueField) == base_form.findField('WhsDocumentCostItemType_id').getValue());
		});

		if ( index >= 0 && base_form.findField('WhsDocumentCostItemType_id').getStore().getAt(index).get('WhsDocumentCostItemType_SysNick') == 'vzn' ) {
			postParams.EvnRecept_Is7Noz = 2;
		}
		else {
			postParams.EvnRecept_Is7Noz = 1;
		}

		// Получаем идентификатор строки остатков
		if ( base_form.findField('DrugComplexMnn_id') && base_form.findField('DrugComplexMnn_id').getValue() > 0 ) {
			var drug_mnn_store =  base_form.findField('DrugComplexMnn_id').getStore();
			var idx = drug_mnn_store.findBy(function(rec) { return rec.get('DrugComplexMnn_id') == base_form.findField('DrugComplexMnn_id').getValue(); });
			if (idx >= 0) {
				postParams.DrugOstatRegistry_id = drug_mnn_store.getAt(idx).get('DrugOstatRegistry_id');
				postParams.DrugRequestRow_id = drug_mnn_store.getAt(idx).get('DrugRequestRow_id');
			}
		}

		// Получаем значения неактивных полей
		if ( base_form.findField('EvnRecept_setDate').disabled ) {
			postParams.EvnRecept_setDate = Ext.util.Format.date(base_form.findField('EvnRecept_setDate').getValue(), 'd.m.Y');
		}

		if ( base_form.findField('DrugFinance_id').disabled ) {
			postParams.DrugFinance_id = base_form.findField('DrugFinance_id').getValue();
		}

		if ( base_form.findField('EvnRecept_Num').disabled ) {
			postParams.EvnRecept_Num = base_form.findField('EvnRecept_Num').getValue();
		}

		if ( base_form.findField('EvnRecept_Ser').disabled ) {
			postParams.EvnRecept_Ser = base_form.findField('EvnRecept_Ser').getValue();
		}

		if ( base_form.findField('ReceptDiscount_id').disabled ) {
			postParams.ReceptDiscount_id = base_form.findField('ReceptDiscount_id').getValue();
		}

		if ( base_form.findField('MedStaffFact_id').disabled ) {
			postParams.MedStaffFact_id = base_form.findField('MedStaffFact_id').getValue();
		}

		if ( base_form.findField('ReceptType_id').disabled ) {
			postParams.ReceptType_id = base_form.findField('ReceptType_id').getValue();
		}

		if ( base_form.findField('ReceptForm_id').disabled ) {
			postParams.ReceptForm_id = base_form.findField('ReceptForm_id').getValue();
		}

		if ( base_form.findField('ReceptValid_id').disabled ) {
			postParams.ReceptValid_id = base_form.findField('ReceptValid_id').getValue();
		}

		if ( base_form.findField('WhsDocumentCostItemType_id').disabled ) {
			postParams.WhsDocumentCostItemType_id = base_form.findField('WhsDocumentCostItemType_id').getValue();
		}

		if ( base_form.findField('PrivilegeType_id').disabled ) {
			postParams.PrivilegeType_id = base_form.findField('PrivilegeType_id').getValue();
		}

		if ( base_form.findField('EvnRecept_IsKEK').disabled ) {
			postParams.EvnRecept_IsKEK = base_form.findField('EvnRecept_IsKEK').getValue();
		}

		if ( base_form.findField('EvnRecept_IsMnn').disabled ) {
			postParams.EvnRecept_IsMnn = base_form.findField('EvnRecept_IsMnn').getValue();
		}

		if ( base_form.findField('DrugComplexMnn_id').disabled ) {
			postParams.DrugComplexMnn_id = base_form.findField('DrugComplexMnn_id').getValue();
		}

		if ( base_form.findField('Drug_Price').disabled ) {
			postParams.Drug_Price = base_form.findField('Drug_Price').getValue();
		}
		
		if ( base_form.findField('LpuSection_id') ) {
			postParams.LpuSection_id = base_form.findField('LpuSection_id').getValue();
		}
        postParams.ReceptForm_id = base_form.findField('ReceptForm_id').getValue();
        postParams.PersonPrivilege_id = base_form.findField('PrivilegeType_id').getFieldValue('PersonPrivilege_id');
        if (options.IsOtherDiag) {
            postParams.EvnRecept_IsOtherDiag = 1;
        }

        if (!options.checkEvnMatterRecept) {
            Ext.Ajax.request({
                failure: function(result_form, action) {
                    this.formStatus = 'edit';

                    loadMask.hide();

                    if ( action.result ) {
                        if ( action.result.Error_Msg ) {
                            sw.swMsg.alert('Ошибка', action.result.Error_Msg);
                        }
                        else {
                            sw.swMsg.alert('Ошибка', 'При проверке соответствия диагноза выписываемому ЛС произошли ошибки [Тип ошибки: 4]');
                        }
                    }
                }.createDelegate(this),
                success: function(response) {
                    loadMask.hide();
                    var result = Ext.util.JSON.decode(response.responseText);
                    var SaveRecept = false;
                    var validReceptCodes = '';

                    for (var i = 0; i < result.length; i++) {
                        if (result[i].Matter_in_diag == 1) {
                            SaveRecept = true;
                        }

                        validReceptCodes = validReceptCodes + result[i].Name + ', ';
                    }

                    if (SaveRecept) {
                        this.formStatus = 'edit';
                        this.doSave({
                            checkPersonAge: options.checkPersonAge,
							checkPersonDeadDT: options.checkPersonDeadDT,
                            checkPersonSnils: options.checkPersonSnils,
                            checkEvnMatterRecept: true,
                            copy: options.copy,
                            print: options.print,
                            sign: options.sign,
							callback: options.callback
                        });
                    } else {
                        sw.swMsg.show({
                            buttons: Ext.Msg.YESNO,
                            fn: function ( buttonId ) {
                                if ( buttonId == 'yes' ) {
                                    this.formStatus = 'edit';
                                    this.doSave({
                                        checkPersonAge: options.checkPersonAge,
										checkPersonDeadDT: options.checkPersonDeadDT,
                                        checkPersonSnils: options.checkPersonSnils,
                                        checkEvnMatterRecept: true,
										copy: options.copy,
                                        IsOtherDiag: true,
                                        print: options.print,
                                        sign: options.sign,
										callback: options.callback
                                    });
                                }
                                else {
                                    this.formStatus = 'edit';
                                    return false;
                                }
                            }.createDelegate(this),
                            msg: this.setDiagErrorMsg('Диагноз, указанный в рецепте, не соответствует показаниям для применения выписываемого лекарственного средства. Выписанному лекарственному средству соответствуют диагнозы с кодами: {diag_list}.  Сохранить рецепт?', validReceptCodes.substring(0, validReceptCodes.length - 2)),
                            title: 'Проверка рецепта'
                        });
                    }
                }.createDelegate(this),
                params: {
                    DrugComplexMnn_id: base_form.findField('DrugComplexMnn_id').getValue(),
                    Diag_id: base_form.findField('Diag_id').getValue()
                },
                url: C_EVNREC_MATTER_CHECK
            });
            return false;
        }

        //проверка необходимости и возможности использования региональных остатков
        /*if (!options.checkFarmacyRlsOstatListOnlsFed) {
        	this.checkFarmacyRlsOstatListOnlsFed(function() {
                win.formStatus = 'edit';
                win.doSave({
                    checkPersonAge: options.checkPersonAge,
                    checkPersonDeadDT: options.checkPersonDeadDT,
                    checkPersonSnils: options.checkPersonSnils,
                    checkEvnMatterRecept: options.checkEvnMatterRecept,
					checkFarmacyRlsOstatListOnlsFed: true,
                    copy: options.copy,
                    IsOtherDiag: options.IsOtherDiag,
                    print: options.print,
                    sign: options.sign,
					callback: options.callback
                });
			}, loadMask);
            return false;
        }*/

        //проверка необходимости и возможности использования региональных остатков
        if (!options.checkSelectedOstatByZero) {
        	this.checkSelectedOstatByZero(function() {
                win.formStatus = 'edit';
                win.doSave({
					checkPersonAge: options.checkPersonAge,
					checkPersonDeadDT: options.checkPersonDeadDT,
					checkPersonSnils: options.checkPersonSnils,
					checkEvnMatterRecept: options.checkEvnMatterRecept,
					checkFarmacyRlsOstatListOnlsFed: options.checkFarmacyRlsOstatListOnlsFed,
					checkSelectedOstatByZero: true,
					copy: options.copy,
					IsOtherDiag: options.IsOtherDiag,
					print: options.print,
					sign: options.sign,
					callback: options.callback
                });
			}, loadMask);
            return false;
        }

        //проверка на соответстиве диагноза в рецепте диагнозу в посещении
        /*if (!options.checkEvnVisitPLDiag && !Ext.isEmpty(postParams.EvnRecept_pid)) {
        	this.checkEvnVisitPLDiag(function() { //todo: дописать
                win.formStatus = 'edit';
                win.doSave({
                    checkPersonAge: options.checkPersonAge,
                    checkPersonDeadDT: options.checkPersonDeadDT,
                    checkPersonSnils: options.checkPersonSnils,
                    checkEvnMatterRecept: options.checkEvnMatterRecept,
                    checkFarmacyRlsOstatListOnlsFed: options.checkFarmacyRlsOstatListOnlsFed,
                    checkEvnVisitPLDiag: true,
                    copy: options.copy,
                    IsOtherDiag: options.IsOtherDiag,
                    print: options.print,
                    sign: options.sign
                });
			}, loadMask);
            return false;
        }*/

        //проверка последенй модерации льготы
        if (!options.checkLastPersonPrivilegeModeration && !Ext.isEmpty(getGlobalOptions().person_privilege_add_request_postmoderation)) { //проверка актуальна только для режима постмодерации
        	this.checkLastPersonPrivilegeModeration(function() {
                win.formStatus = 'edit';
                win.doSave({
                    checkPersonAge: options.checkPersonAge,
                    checkPersonDeadDT: options.checkPersonDeadDT,
                    checkPersonSnils: options.checkPersonSnils,
                    checkEvnMatterRecept: options.checkEvnMatterRecept,
					checkFarmacyRlsOstatListOnlsFed: options.checkFarmacyRlsOstatListOnlsFed,
					checkSelectedOstatByZero: options.checkSelectedOstatByZero,
                    checkEvnVisitPLDiag: options.checkEvnVisitPLDiag,
                    checkLastPersonPrivilegeModeration: true,
                    copy: options.copy,
                    IsOtherDiag: options.IsOtherDiag,
                    print: options.print,
                    sign: options.sign,
					callback: options.callback
                });
			}, loadMask);
            return false;
        }

        //проверка на повторную выписку рецепта ЛКО Кардио
        if (!options.checkReceptKardioReissue && win.isKardio && getRegionNick() == 'perm') { //проверка актуальна только для рецептов по программе "ЛЛО Кардио"
        	this.checkReceptKardio('checkReceptKardioReissue', function() {
                win.formStatus = 'edit';
                options.checkReceptKardioReissue = true;
                win.doSave(options);
			}, loadMask);
            return false;
        }

        //проверка на выписку ЛП Тикагрелор в стационаре и поликлинике
        if (!options.checkReceptKardioTicagrelor && win.isKardio && getRegionNick() == 'perm') { //проверка актуальна только для рецептов по программе "ЛЛО Кардио"
        	this.checkReceptKardio('checkReceptKardioTicagrelor', function() {
                win.formStatus = 'edit';
                options.checkReceptKardioTicagrelor = true;
                win.doSave(options);
			}, loadMask);
            return false;
        }

        //проверка даты выписки рецепта
        if (!options.checkReceptKardioSetDate && win.isKardio && getRegionNick() == 'perm') { //проверка актуальна только для рецептов по программе "ЛЛО Кардио"
        	this.checkReceptKardio('checkReceptKardioSetDate', function() {
                win.formStatus = 'edit';
                options.checkReceptKardioSetDate = true;
                win.doSave(options);
			}, loadMask);
            return false;
        }

        //проверка на повторную выписку рецепта ЛКО Кардио
        if (!options.checkReceptKardioReissue && win.isKardio && getRegionNick() == 'perm') { //проверка актуальна только для рецептов по программе "ЛЛО Кардио"
        	this.checkReceptKardio('checkReceptKardioReissue', function() {
                win.formStatus = 'edit';
                options.checkReceptKardioReissue = true;
                win.doSave(options);
			}, loadMask);
            return false;
        }

        //проверка на выписку ЛП Тикагрелор в стационаре и поликлинике
        if (!options.checkReceptKardioTicagrelor && win.isKardio && getRegionNick() == 'perm') { //проверка актуальна только для рецептов по программе "ЛЛО Кардио"
        	this.checkReceptKardio('checkReceptKardioTicagrelor', function() {
                win.formStatus = 'edit';
                options.checkReceptKardioTicagrelor = true;
                win.doSave(options);
			}, loadMask);
            return false;
        }

        //проверка даты выписки рецепта
        if (!options.checkReceptKardioSetDate && win.isKardio && getRegionNick() == 'perm') { //проверка актуальна только для рецептов по программе "ЛЛО Кардио"
        	this.checkReceptKardio('checkReceptKardioSetDate', function() {
                win.formStatus = 'edit';
                options.checkReceptKardioSetDate = true;
                win.doSave(options);
			}, loadMask);
            return false;
        }

        var WhsDocumentCostItemType = base_form.findField('WhsDocumentCostItemType_id').getValue();
        var diag_id = base_form.findField('Diag_id').getValue();
        var privilege_type = base_form.findField('PrivilegeType_id').getValue();
		if ( Ext.isEmpty(base_form.findField('EvnRecept_id').getValue()) ) {
			Ext.Ajax.request({
				callback: function(opt, success, resp) {
					loadMask.hide();

					if ( resp.responseText == 'error' ) {
						this.formStatus = 'edit';

						sw.swMsg.alert('Ошибка', 'Ошибка при проверке возможности выдачи рецепта');
						return false;
					}
					else if ( resp.responseText == 'true' ) {
                        if ((WhsDocumentCostItemType=='2') && getGlobalOptions().recept_diag_control == 2){ //Проверка на соответствие диагноза выбранной льготе
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
											postData: postParams,
											print: options.print,
											sign: options.sign,
											callback: options.callback
										});
									} else {
										var Msg = this.setDiagErrorMsg('Диагноз, указанный в рецепте, не соответствует указанной льготе. Указанной льготе соответствуют диагнозы с кодами: {diag_list}.', validReceptCodes.substring(0, validReceptCodes.length - 2));
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
                                            postData: postParams,
                                            print: options.print,
                                            sign: options.sign
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
						/*this.doSubmit({
							copy: options.copy,
							postData: postParams,
							print: options.print,
							sign: options.sign
						});*/
					}
					else {
						sw.swMsg.show({
							buttons: Ext.Msg.YESNO,
							fn: function ( buttonId ) {
								if ( buttonId == 'yes' ) {
                                    if ((WhsDocumentCostItemType=='2') && getGlobalOptions().recept_diag_control == 2){ //Проверка на соответствие диагноза выбранной льготе
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
														postData: postParams,
														print: options.print,
														sign: options.sign,
														callback: options.callback
													});
												} else {
													var Msg = this.setDiagErrorMsg('Диагноз, указанный в рецепте, не соответствует указанной льготе. Указанной льготе соответствуют диагнозы с кодами: {diag_list}.', validReceptCodes.substring(0, validReceptCodes.length - 2));
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
                                                        postData: postParams,
                                                        print: options.print,
                                                        sign: options.sign
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
                                            print: options.print,
                                            sign: options.sign,
											callback: options.callback
                                        });
                                    }
									/*this.doSubmit({
										copy: options.copy,
										postData: postParams,
										print: options.print,
										sign: options.sign
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
					Drug_rlsid: base_form.findField('Drug_rlsid').getValue(),
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
            if ((WhsDocumentCostItemType=='2') && getGlobalOptions().recept_diag_control == 2){ //Проверка на соответствие диагноза выбранной льготе
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
								postData: postParams,
								print: options.print,
								sign: options.sign,
								callback: options.callback
							});
						} else {
							var Msg = this.setDiagErrorMsg('Диагноз, указанный в рецепте, не соответствует указанной льготе. Указанной льготе соответствуют диагнозы с кодами: {diag_list}.', validReceptCodes.substring(0, validReceptCodes.length - 2));
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
                                postData: postParams,
                                print: options.print,
                                sign: options.sign
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
                    print: options.print,
                    sign: options.sign,
					callback: options.callback
                });
            }
			/*this.doSubmit({
				copy: options.copy,
				postData: postParams,
				print: options.print,
				sign: options.sign
			});*/
		}
	},
	doSubmit: function(options) {
		// options @Object
		// options.copy @Boolean 
		// options.postData @Object Данные для сохранения
		// options.print @Boolean Вызывать печать рецепта, если true
		// options.sign @Boolean Подписать рецепт, если true
		// options.callback @Function Обратный вызов
		if ( !options || typeof options != 'object' ) {
			this.formStatus = 'edit';
			return false;
		}

		var base_form = this.FormPanel.getForm();

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
					log(['action.result', action.result]);
					if ( action.result.EvnRecept_id ) {
						var Drug_Name = '';
						var EvnRecept_id = action.result.EvnRecept_id;
						var EvnCourseTreatDrug_id = !Ext.isEmpty(action.result.EvnCourseTreatDrug_id) ? action.result.EvnCourseTreatDrug_id : null;
						var index;
						var MedPersonal_Fio = '';
						var PersonRegisterType_id;
						var PrivilegeType_Code = null;
						var record;
						var Server_id = base_form.findField('Server_id').getValue();

						this.action = 'edit';
						this.enableEdit(true);
						this.setTitleByAction();

						base_form.findField('EvnRecept_id').setValue(EvnRecept_id);

						index = base_form.findField('Drug_rlsid').getStore().findBy(function(rec) {
							return (rec.get('Drug_rlsid') == base_form.findField('Drug_rlsid').getValue());
						});

						if ( index >= 0 ) {
							Drug_Name = base_form.findField('Drug_rlsid').getStore().getAt(index).get('Drug_Name');
						}

						index = base_form.findField('MedStaffFact_id').getStore().findBy(function(rec) {
							return (rec.get('MedStaffFact_id') == base_form.findField('MedStaffFact_id').getValue());
						});

						if ( index >= 0 ) {
							MedPersonal_Fio = base_form.findField('MedStaffFact_id').getStore().getAt(index).get('MedPersonal_Fio');
						}

						index = base_form.findField('PrivilegeType_id').getStore().findBy(function(rec) {
							return (rec.get('PrivilegeType_id') == base_form.findField('PrivilegeType_id').getValue());
						});

						if ( index >= 0 ) {
							PrivilegeType_Code = base_form.findField('PrivilegeType_id').getStore().getAt(index).get('PrivilegeType_Code');
						}

						index = base_form.findField('WhsDocumentCostItemType_id').getStore().findBy(function(rec) {
							return (rec.get('WhsDocumentCostItemType_id') == base_form.findField('WhsDocumentCostItemType_id').getValue());
						});

						if ( index >= 0 ) {
							PersonRegisterType_id = base_form.findField('WhsDocumentCostItemType_id').getStore().getAt(index).get('PersonRegisterType_id');
						}

						var response = {
							 Drug_Name: Drug_Name
							,EvnRecept_id: EvnRecept_id
							,EvnCourseTreatDrug_id: EvnCourseTreatDrug_id
							,EvnRecept_Num: base_form.findField('EvnRecept_Num').getValue()
							,EvnRecept_pid: base_form.findField('EvnRecept_pid').getValue()
							,EvnRecept_Ser: base_form.findField('EvnRecept_Ser').getValue()
							,EvnRecept_setDate: base_form.findField('EvnRecept_setDate').getValue()
							,MedPersonal_Fio: MedPersonal_Fio
							,PersonRegisterType_id: PersonRegisterType_id
							,Person_Birthday: this.PersonInfo.getFieldValue('Person_Birthday')
							,Person_Firname: this.PersonInfo.getFieldValue('Person_Firname')
							,Person_id: base_form.findField('Person_id').getValue()
							,Person_Secname: this.PersonInfo.getFieldValue('Person_Secname')
							,Person_Surname: this.PersonInfo.getFieldValue('Person_Surname')
							,PersonEvn_id: base_form.findField('PersonEvn_id').getValue()
							,PrivilegeType_Code: PrivilegeType_Code
							,Server_id: Server_id
						};

						this.callback({ EvnReceptData: response });
                        var ReceptForm_id = base_form.findField('ReceptForm_id').getValue();
						var evn_recept_set_date = base_form.findField('EvnRecept_setDate').getValue().format('Y-m-d');
						if ( options.print == true ) {
							var that = this;
                            var region_nick = getRegionNick();

							saveEvnReceptIsPrinted({
								allowQuestion: false
								, callback: function (success) {
									if ( success == true ) {
										if (Ext.globalOptions.recepts.print_extension == 3) {
											if (ReceptForm_id != 2) {
                                                window.open(C_EVNREC_PRINT_DS, '_blank');
											}
											window.open(C_EVNREC_PRINT + '&EvnRecept_id=' + EvnRecept_id, '_blank');

                                            if (region_nick == 'msk') {
                                                that.showPrintButton(false);
                                            }
										} else {
											Ext.Ajax.request({
												url: '/?c=EvnRecept&m=getPrintType',
												callback: function (options, success, response) {
													if (success) {
														var result = Ext.util.JSON.decode(response.responseText);
														var PrintType = '';

                                                        if (region_nick == 'msk') {
                                                            that.showPrintButton(false);
                                                        }

														switch (result.PrintType) {
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
                                                                if (result.CopiesCount == 1) {
                                                                    printBirt({
                                                                        'Report_FileName': 'EvnReceptPrint4_1MI.rptdesign',
                                                                        'Report_Params': '&paramEvnRecept=' + EvnRecept_id,
                                                                        'Report_Format': 'pdf'
                                                                    });
                                                                } else {
                                                                    if (PrintType == '') {
                                                                        printBirt({
                                                                            'Report_FileName': 'EvnReceptPrint1_1MI.rptdesign',
                                                                            'Report_Params': '&paramEvnRecept=' + EvnRecept_id,
                                                                            'Report_Format': 'pdf'
                                                                        });
                                                                    } else {
                                                                        printBirt({
                                                                            'Report_FileName': 'EvnReceptPrint' + PrintType + '_1MI.rptdesign',
                                                                            'Report_Params': '&paramEvnRecept=' + EvnRecept_id,
                                                                            'Report_Format': 'pdf'
                                                                        });
                                                                    }
                                                                }
																break;
															case 9: //148-1/у-04(л)
                                                                if (region_nick == 'msk') {
                                                                    printBirt({
                                                                        'Report_FileName': 'EvnReceptPrint_148_1u04_2InA4_2020.rptdesign',
                                                                        'Report_Params': '&paramEvnRecept=' + EvnRecept_id,
                                                                        'Report_Format': 'pdf'
                                                                    });
                                                                } else {
                                                                    //игнорируем настройки и печатаем сразу обе стороны
                                                                    printBirt({
                                                                        'Report_FileName': 'EvnReceptPrint_148_1u04_2InA4_2019.rptdesign',
                                                                        'Report_Params': '&paramEvnRecept=' + EvnRecept_id,
                                                                        'Report_Format': 'pdf'
                                                                    });
                                                                }
                                                                printBirt({
                                                                    'Report_FileName': 'EvnReceptPrint_148_1u04_2InA4_2019Oborot.rptdesign',
                                                                    'Report_Params': '&paramEvnRecept=' + EvnRecept_id,
                                                                    'Report_Format': 'pdf'
                                                                });
																break;
															case 10: //148-1/у-04 (к)
                                                                //игнорируем настройки и печатаем сразу обе стороны
                                                                printBirt({
                                                                    'Report_FileName': 'EvnReceptPrint_148_1u04k_2InA4_2019.rptdesign',
                                                                    'Report_Params': '&paramEvnRecept=' + EvnRecept_id,
                                                                    'Report_Format': 'pdf'
                                                                });
                                                                printBirt({
                                                                    'Report_FileName': 'EvnReceptPrint_148_1u04_2InA4_2019Oborot.rptdesign',
                                                                    'Report_Params': '&paramEvnRecept=' + EvnRecept_id,
                                                                    'Report_Format': 'pdf'
                                                                });
																break;
                                                            case 1: //148-1/у-04(л), 148-1/у-06(л)
                                                                if (region_nick == 'msk') {
                                                                    printBirt({
                                                                        'Report_FileName': 'EvnReceptPrint_148_1u04_4InA4_2019.rptdesign',
                                                                        'Report_Params': '&paramEvnRecept=' + EvnRecept_id,
                                                                        'Report_Format': 'pdf'
                                                                    });
                                                                    break; //в пределах условия для того, чтобы в других регионах выполнение проваливалось в дефолтную секцию
                                                                }
															default:
                                                                var ReportName = 'EvnReceptPrint' + PrintType;
                                                                var ReportNameOb = 'EvnReceptPrintOb' + PrintType;
                                                                if (result.CopiesCount == 1) {
                                                                    if (evn_recept_set_date >= '2016-07-30') {
                                                                        ReportName = 'EvnReceptPrint4_2016_new';
                                                                    } else if (evn_recept_set_date >= '2016-01-01') {
                                                                        ReportName = 'EvnReceptPrint4_2016';
                                                                    } else {
                                                                        ReportName = 'EvnReceptPrint2_2015';
                                                                    }
                                                                    ReportNameOb = 'EvnReceptPrintOb2_2015';
                                                                } else {
                                                                    if (evn_recept_set_date >= '2016-07-30') {
                                                                        ReportName = ReportName + '_2016_new';
																	} else if (evn_recept_set_date >= '2016-01-01') {
                                                                        ReportName = ReportName + '_2016';
																	}
                                                                }
                                                                if (Ext.globalOptions.recepts.print_extension == 1) {
                                                                    printBirt({
                                                                        'Report_FileName': ReportNameOb + '.rptdesign',
                                                                        'Report_Params': '&paramEvnRecept=' + EvnRecept_id + '&paramProMedPort=' + result.server_port + '&paramProMedProto=' + result.server_http,
                                                                        'Report_Format': 'pdf'
                                                                    });
                                                                }
                                                                if (result.server_port != null) {
                                                                    printBirt({
                                                                        'Report_FileName': ReportName + '.rptdesign',
                                                                        'Report_Params': '&paramEvnRecept=' + EvnRecept_id + '&paramProMedPort=' + result.server_port + '&paramProMedProto=' + result.server_http,
                                                                        'Report_Format': 'pdf'
                                                                    });
                                                                } else {
                                                                    printBirt({
                                                                        'Report_FileName': ReportName + '.rptdesign',
                                                                        'Report_Params': '&paramEvnRecept=' + EvnRecept_id + '&paramProMedProto=' + result.server_http,
                                                                        'Report_Format': 'pdf'
                                                                    });
                                                                }
																break;
														}
													}
												}.createDelegate(that)
											});
										}
									} else {
										sw.swMsg.alert('Ошибка', 'Ошибка при выполнении процедуры подписания рецепта');
									}
								}.createDelegate(this)
								,Evn_id: EvnRecept_id
							})
						}
						else if ( options.copy ) {
							log('save copy');
							this.doCopy(true);
						}
						else if ( options.sign ) {
							//this.signRecept();
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

				if (typeof(options.callback) == 'function') {
					options.callback();
				}
			}.createDelegate(this)
		});
	},
	draggable: true,
	enableEdit: function(enable) {
		var base_form = this.FormPanel.getForm();
		var form_fields = new Array(
			'Diag_id',
			'Drug_rlsid',
			'DrugComplexMnn_id',
			'EvnRecept_IsKEK',
			'EvnRecept_IsMnn',
			'EvnRecept_Kolvo',
			'EvnRecept_Num',
			'EvnRecept_Ser',
			'EvnRecept_setDate',
			'EvnRecept_Signa',
			'EvnRecept_IsDelivery',
			'LpuSection_id',
			'MedStaffFact_id',
			'OrgFarmacy_id',
			'PrivilegeType_id',
			'ReceptType_id',
			'ReceptValid_id',
			'WhsDocumentCostItemType_id',
            'ReceptForm_id',
            'EvnRecept_VKProtocolNum',
            'EvnRecept_VKProtocolDT',
            'CauseVK_id',
            'PersonAmbulatCard_id',
            'EvnCourseTreatDrug_KolvoEd',
            'EvnCourseTreatDrug_Kolvo',
            'GoodsUnit_sid',
            'GoodsUnit_id',
            'EvnCourseTreat_CountDay',
            'PrescriptionIntroType_id',
            'EvnCourseTreat_setDate',
            'EvnCourseTreat_Duration',
			'PrescrSpecCause_cb',
			'PrescrSpecCause_id',
			'ReceptUrgency_id',
            'EvnRecept_IsExcessDose'
		);

		for ( var i = 0; i < form_fields.length; i++ ) {
			var field = base_form.findField(form_fields[i]);
			if ( enable && !field.enable_blocked ) {
				field.enable();
			} else {
                field.disable();
			}
		}

		if ( enable ) {
			this.buttons[0].show();
		} else {
			this.buttons[0].hide();
		}
	},
	formStatus: 'edit',
	get7NozDiagList: function() {
		return [ 'C92.1', 'C88.0', 'C90.0', 'C82.', 'C82.0', 'C82.1', 'C82.2', 'C82.7', 'C82.9', 'C83.0', 'C83.1', 'C83.3', 'C83.4', 'C83.8', 'C83.9', 'C85', 'C85.0', 'C85.1', 'C85.7', 'C85.9', 'C91.1', 'E84.', 'E84.0', 'E84.1', 'E84.8', 'E84.9', 'D66.', 'D67.', 'D68.0', 'G35.', 'E23.0', 'E75.5', 'Z94.0', 'Z94.1', 'Z94.4', 'Z94.8' ]
	},
	height: 500,
	id: 'EvnReceptRlsEditWindow',
	setReceptDiscount: function() {
		var base_form = this.FormPanel.getForm();
		var cost_combo = base_form.findField('WhsDocumentCostItemType_id');
		var priv_combo = base_form.findField('PrivilegeType_id');

		var cost_nick = null;
		var discount_id = null;
		var idx = -1;
		var record = null;

		idx = cost_combo.getStore().findBy(function(rec) { return rec.get('WhsDocumentCostItemType_id') == cost_combo.getValue(); });
		if (idx >= 0) {
			cost_nick = cost_combo.getStore().getAt(idx).get('WhsDocumentCostItemType_Nick');
		}

		if (!Ext.isEmpty(cost_nick) && cost_nick.inlist(['common', 'common_fl', 'common_rl', 'rl', 'fl'])) { //извлекаем скидку из PrivilegeType
			idx = priv_combo.getStore().findBy(function(rec) { return rec.get('PrivilegeType_id') == priv_combo.getValue(); });
			if (idx >= 0) {
				record = priv_combo.getStore().getAt(idx);
				discount_id = record.get('ReceptDiscount_id');
			}
		} else {
			discount_id = 1; //100%
		}
		
		if(getRegionNick() == 'krym'){
			var PrivilegeType_Code = priv_combo.getFieldValue('PrivilegeType_Code');
			if(PrivilegeType_Code.inlist(['211','401','402','403']))
				discount_id = 2;
		}
		if (discount_id > 0) {
			base_form.findField('ReceptDiscount_id').setValue(discount_id);
		}
	},
	initComponent: function() {
		var wnd = this;
        var region_nick = getRegionNick();

        this.ambulat_card_combo =  new sw.Promed.SwCustomRemoteCombo({
            fieldLabel: langs('Номер карты'),
            hiddenName: 'PersonAmbulatCard_id',
            displayField: 'PersonAmbulatCard_Name',
            valueField: 'PersonAmbulatCard_id',
            editable: true,
            allowBlank: false,
            width: 517,
            listWidth: 517,
            triggerAction: 'all',
            store: new Ext.data.Store({
                autoLoad: false,
                reader: new Ext.data.JsonReader({
                    id: 'PersonAmbulatCard_id'
                }, [
                    {name: 'PersonAmbulatCard_id', mapping: 'PersonAmbulatCard_id'},
                    {name: 'PersonAmbulatCard_Name', mapping: 'PersonAmbulatCard_Name'}
                ]),
                url: '/?c=EvnRecept&m=loadPersonAmbulatCardCombo'
            }),
            tpl: new Ext.XTemplate(
                '<tpl for="."><div class="x-combo-list-item">',
                '<table><tr><td>{PersonAmbulatCard_Name}&nbsp;</td></tr></table>',
                '</div></tpl>'
            ),
            setValueByDefaultValue: function() {
                var combo = this;
                //combo.getStore().baseParams.LpuAttachType_Code = 1; //1 - Основной
                combo.getStore().load({
                    callback: function(){
                    	if (combo.getStore().getCount() == 1) {
                    		var id = combo.getStore().getAt(0).get('PersonAmbulatCard_id');
                            combo.setValue(id);
						}
                        //combo.getStore().baseParams.LpuAttachType_Code = null;
                    }
                });
            }
        });

        this.privilege_type_combo = new sw.Promed.SwBaseLocalCombo({
            valueField: 'PrivilegeType_id',
			codeField: 'PrivilegeType_VCode',
			displayField: 'PrivilegeType_Name',
            fieldLabel: getRegionNick().inlist(['kz']) ? langs('Категория / Нозология') : langs('Категория'),
            hiddenName: 'PrivilegeType_id',
			editable: false,
            allowBlank: false,
            validateOnBlur: true,
			lastQuery: '',
			trigger2Class: region_nick == 'msk' ? 'x-form-plus-trigger' : 'x-form-clear-trigger',
            width: 517,
            tabIndex: TABINDEX_ERREF + 9,
            store: new Ext.data.Store({
                autoLoad: false,
                reader: new Ext.data.JsonReader({
                    id: 'PrivilegeType_id'
                }, [
                    { name: 'PrivilegeType_Code', mapping: 'PrivilegeType_Code', type: 'int' },
                    { name: 'PrivilegeType_VCode', mapping: 'PrivilegeType_VCode' },
                    { name: 'PrivilegeType_id', mapping: 'PrivilegeType_id' },
                    { name: 'PrivilegeType_Name', mapping: 'PrivilegeType_Name' },
                    { name: 'PrivilegeType_SysNick', mapping: 'PrivilegeType_SysNick' },
                    { name: 'ReceptDiscount_id', mapping: 'ReceptDiscount_id' },
                    { name: 'ReceptFinance_id', mapping: 'ReceptFinance_id' },
                    { name: 'DrugFinance_id', mapping: 'DrugFinance_id' },
                    { name: 'PersonPrivilege_id', mapping: 'PersonPrivilege_id' },
                    { name: 'PersonPrivilege_IsClosed', mapping: 'PersonPrivilege_IsClosed' },
                    { name: 'PersonPrivilege_IsNoPfr', mapping: 'PersonPrivilege_IsNoPfr' },
                    { name: 'PersonPrivilege_IsPersonDisp', mapping: 'PersonPrivilege_IsPersonDisp' },
                    { name: 'PersonRefuse_IsRefuse', mapping: 'PersonRefuse_IsRefuse' },
                    { name: 'SubCategoryPrivType_id', mapping: 'SubCategoryPrivType_id' },
                    { name: 'SubCategoryPrivType_Code', mapping: 'SubCategoryPrivType_Code' },
                    { name: 'SubCategoryPrivType_Name', mapping: 'SubCategoryPrivType_Name' },
                    { name: 'WhsDocumentCostItemType_id', mapping: 'WhsDocumentCostItemType_id' }
                ]),
                url: C_PRIVCAT_LOAD_LIST
            }),
			tpl: new Ext.XTemplate(
                '<tpl for="."><div class="x-combo-list-item">',
                '<table style="border: 0;"><tr><td style="width: 25px;">',
                '<font color="red">{PrivilegeType_VCode}</font></td>',
                '<td><span style="font-weight: {[ values.PersonPrivilege_IsClosed == 1 ? "bold" : "normal; color: red;" ]};">{PrivilegeType_Name}</span>',
                '<tpl if="!Ext.isEmpty(values.SubCategoryPrivType_id)"> / <font color="blue">{SubCategoryPrivType_Code}. {SubCategoryPrivType_Name}</font></tpl>',
                '{[ values.PersonPrivilege_IsClosed == 1 ? "&nbsp;" : " (закрыта)" ]}',
                '</td></tr></table>',
                '</div></tpl>'
            ),
            listeners: {
                'change': function(combo, newValue, oldValue) {
                    var base_form = this.FormPanel.getForm();

                    base_form.findField('Drug_rlsid').getStore().baseParams.PrivilegeType_id = newValue;
                    base_form.findField('DrugComplexMnn_id').getStore().baseParams.PrivilegeType_id = newValue;

                    var index = combo.getStore().findBy(function(rec) {
                        return (rec.get('PrivilegeType_id') == newValue);
                    });

                    // Если запись не найдена
                    if ( index == -1 ) {
                        // Чистим значение поля "Скидка"
                        base_form.findField('ReceptDiscount_id').clearValue();
                        // Прерываем выполнение метода
                        return false;
                    } else {
                        this.setReceptDiscount();
                    }

                    // Собираем список доступных регистров заболеваний
                    var PersonRegisterTypeList = new Array();

                    this.personRegisterStore.each(function(rec) {
                    	if (!Ext.isEmpty(rec.get('PersonRegisterType_id'))) {
							PersonRegisterTypeList.push(rec.get('PersonRegisterType_id').toString());
						}
                    });

                    var WhsDocumentCostItemType_id = 0;
                    var record = combo.getStore().getAt(index);
					var DrugFinance_id = record.get('DrugFinance_id');

                    var fin_combo = base_form.findField('DrugFinance_id');
                    var cost_combo = base_form.findField('WhsDocumentCostItemType_id');

                    if(this.action != 'view'){
                        if (!Ext.isEmpty(DrugFinance_id)) {
                            fin_combo.setValue(DrugFinance_id);
                            fin_combo.fireEvent('change', fin_combo, DrugFinance_id);

                            var EvnRecept_setDate = base_form.findField('EvnRecept_setDate').getValue();
							//получаем идентификатор программы ЛЛО, на которую ссылается выбранная льготная категория
                            var recordIndex = combo.getStore().findBy(function(rec) { return rec.get('PrivilegeType_id') == newValue; });
							var PrivilegeTypeCost;
                            if (recordIndex >= 0) {
								var PrivilegeTypeCost = combo.getStore().getAt(recordIndex).get('WhsDocumentCostItemType_id');
							}
                            if (getRegionNick() != 'kz') {
                            	cost_combo.getStore().clearFilter();
                            	cost_combo.getStore().filterBy(function (rec) {
									return (
										rec.get('WhsDocumentCostItemType_IsDlo') == 2
										&& (
											typeof EvnRecept_setDate != 'object'
											|| (
												(Ext.isEmpty(rec.get('WhsDocumentCostItemType_begDate')) || EvnRecept_setDate >= rec.get('WhsDocumentCostItemType_begDate'))
												&& (Ext.isEmpty(rec.get('WhsDocumentCostItemType_endDate')) || EvnRecept_setDate <= rec.get('WhsDocumentCostItemType_endDate'))
											)
										)
										&& (
											rec.get('WhsDocumentCostItemType_id') == PrivilegeTypeCost
											||
											(
												rec.get('DrugFinance_id') == DrugFinance_id
												&& rec.get('PersonRegisterType_id').toString().inlist(PersonRegisterTypeList)

											)
										)
									);
								});
							}
                            if (cost_combo.getStore().getCount() > 0) {
								if (!Ext.isEmpty(PrivilegeTypeCost)) {
									cost_combo.setValue(PrivilegeTypeCost);
									cost_combo.fireEvent('change', cost_combo, PrivilegeTypeCost);
								} else {
									WhsDocumentCostItemType_id = cost_combo.getStore().getAt(0).get('WhsDocumentCostItemType_id');
									cost_combo.setValue(WhsDocumentCostItemType_id);
									cost_combo.fireEvent('change', cost_combo, WhsDocumentCostItemType_id);
								}
                            }
                        }
                    }
                }.createDelegate(this)
            },
			initComponent: function() {
                this.getTrigger =  Ext.form.TwinTriggerField.prototype.getTrigger;
                this.initTrigger = Ext.form.TwinTriggerField.prototype.initTrigger;
                this.onTrigger1Click = sw.Promed.SwBaseLocalCombo.prototype.onTriggerClick;

                sw.Promed.SwBaseLocalCombo.prototype.initComponent.apply(this, arguments);
                Ext.form.TwinTriggerField.prototype.initComponent.apply(this, arguments);
            },
            onTrigger2Click: function() {
            	var combo = this;
            	if (region_nick == 'msk') {
            		if (!Ext.isEmpty(getGlobalOptions().person_privilege_add_request_postmoderation)) { //пока данная секция кода имеет смысл только в том случае, если запросы на добавление в льготные регистры работают в режиме постмодерации
                        var base_form = wnd.FormPanel.getForm();
                        var params = new Object();

                        params.action = 'add';
                        params.Person_id = base_form.findField('Person_id').getValue();
                        params.userMedStaffFact = wnd.userMedStaffFact;
                        params.onSave = function(data) {
                            if (!Ext.isEmpty(data.PersonPrivilegeReq_id) && !Ext.isEmpty(data.PrivilegeType_id)) {
                                combo.reloadAndSetValueById(data.PrivilegeType_id);
                            }
                        };

                        getWnd('swPersonPrivilegeReqEditWindow').show(params);
					}
                } else {
                    this.setValue(null);
                    this.fireEvent('change', this, null);
				}
            },
			filterClosedPrivilege: function() {
                this.getStore().clearFilter();
                if (wnd.action != 'view') {
					this.getStore().filterBy(function(rec) {
						return (rec.get('PersonPrivilege_IsClosed') == 1 && (rec.get('ReceptFinance_id') != 1 || (rec.get('ReceptFinance_id') == 1 && rec.get('PersonRefuse_IsRefuse') != 2)));
					});
				}
			},
			reloadData: function(callback) {
            	var base_form = wnd.FormPanel.getForm();
                var EvnRecept_setDate = base_form.findField('EvnRecept_setDate').getValue() ? Ext.util.Format.date(base_form.findField('EvnRecept_setDate').getValue(), 'd.m.Y') : null;
                var Person_id = base_form.findField('Person_id').getValue();

                this.getStore().load({
                    params: {
						date: EvnRecept_setDate,
						Person_id: Person_id
					},
                    callback: function(records, options, success) {
                        if (typeof callback == 'function') {
                        	callback();
						}
                    }
                });
			},
			reloadAndSetValueById: function(id) {
				var combo = this;
				combo.reloadData(function() {
					//фильтруем список, исключая закрытые льготы
					combo.filterClosedPrivilege();

                    //устанавливем указанное значение
                    var idx = combo.getStore().findBy(function(rec) {
						return (rec.get('PrivilegeType_id') == id);
					});
                    if (idx > -1) {
                        combo.setValue(id);
                        combo.fireEvent('change', combo, id);
                    }
				});
			}
        });

		this.PersonInfo = new sw.Promed.PersonInformationPanel({
			button2Callback: function(callback_data) {
				var base_form = this.FormPanel.getForm();

				base_form.findField('PersonEvn_id').setValue(callback_data.PersonEvn_id);
				base_form.findField('Server_id').setValue(callback_data.Server_id);

				this.PersonInfo.load({
					Person_id: callback_data.Person_id,
					Server_id: callback_data.Server_id
				});
			}.createDelegate(this),
			button2OnHide: function() {
				var base_form = this.FormPanel.getForm();

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
				var base_form = this.FormPanel.getForm();

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
				var base_form = this.FormPanel.getForm();

				var EvnRecept_setDate = base_form.findField('EvnRecept_setDate').getValue();

				if ( Ext.isEmpty(EvnRecept_setDate) ) {
					base_form.findField('EvnRecept_setDate').focus(false);
					return false;
				}

				var drugFinanceCombo = base_form.findField('DrugFinance_id');
				var privilegeTypeCombo = base_form.findField('PrivilegeType_id');

				var DrugFinance_id = drugFinanceCombo.getValue();
				var Person_id = base_form.findField('Person_id').getValue();
				var PrivilegeType_id = privilegeTypeCombo.getValue();

				privilegeTypeCombo.getStore().load({
					callback: function(records, options, success) {
						var index;

						// Фильтруем закрытые записи
                        privilegeTypeCombo.filterClosedPrivilege();

						if ( privilegeTypeCombo.getStore().getCount() == 1 ) {
							index = 0;
						}
						else {
							index = privilegeTypeCombo.getStore().findBy(function(rec) {
								return (rec.get('PrivilegeType_id') == PrivilegeType_id);
							});
						}

						if ( index >= 0 ) {
							drugFinanceCombo.setValue(privilegeTypeCombo.getStore().getAt(index).get('DrugFinance_id'));
							drugFinanceCombo.fireEvent('change', drugFinanceCombo, drugFinanceCombo.getValue());
						}

						if ( !privilegeTypeCombo.disabled ) {
							privilegeTypeCombo.focus(false);
						}
						else {
							base_form.findField('EvnRecept_setDate').focus(false);
						}
					}.createDelegate(this),
					params: {
						 date: Ext.util.Format.date(base_form.findField('EvnRecept_setDate').getValue(), 'd.m.Y')
						,Person_id: Person_id
					}
				});
			}.createDelegate(this),
			id: 'ERREF_PersonInformationFrame',
			region: 'north'
		});

		this.FormPanel = new Ext.form.FormPanel({
			autoScroll: true,
			bodyStyle: 'padding: 0.5em;',
			border: false,
			frame: false,
			id: 'EvnReceptRlsEditForm',
			items: [{
				name: 'accessType',
				value: '',
				xtype: 'hidden'
			}, {
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
				name: 'Lpu_id',
				xtype: 'hidden'
			}, {
				name: 'MedPersonal_id',
				value: -1,
				xtype: 'hidden'
			}, {
				name: 'Person_id',
				value: -1,
				xtype: 'hidden'
			}, {
				name: 'PersonEvn_id',
				value: -1,
				xtype: 'hidden'
			}, {
				name: 'Server_id',
				value: -1,
				xtype: 'hidden'
			}, {
				name: 'WhsDocumentUc_id',
				xtype: 'hidden'
			}, {
				name: 'EvnRecept_IsNotOstat',
				xtype: 'hidden'
			}, {
				name: 'ReceptDelayType_id',
				xtype: 'hidden'
			}, {
				name: 'EvnRecept_IsPrinted',
				xtype: 'hidden'
			},
			{
				name: 'EvnUslugaTelemed',		//установка в единицу означает открытие данной формы из формы Оказание телемедицинской услуги
				value: 0,
				xtype: 'hidden'
			},
			new sw.Promed.Panel({
				autoHeight: true,
				bodyStyle: 'padding-top: 0.5em;',
				border: true,
				collapsible: true,
				id: 'ERREF_ReceptPanel',
				layout: 'form',
				style: 'margin-bottom: 0.5em;',
				title: '1. Рецепт',
				items: [
                    {
						allowBlank: false,
						fieldLabel: 'Дата рецепта',
						format: 'd.m.Y',
						listeners: {
							'change': function(field, newValue, oldValue) {
								/*if ( blockedDateAfterPersonDeath('personpanelid', 'ERREF_PersonInformationFrame', field, newValue, oldValue) ) {
									return false;
								}*/

								var base_form = this.FormPanel.getForm();

								var index;
								var LpuSection_id = base_form.findField('LpuSection_id').getValue();
								var MedStaffFact_id = base_form.findField('MedStaffFact_id').getValue();
								var PrivilegeType_id = base_form.findField('PrivilegeType_id').getValue();
								var WhsDocumentCostItemType_id = base_form.findField('WhsDocumentCostItemType_id').getValue();

								base_form.findField('LpuSection_id').clearValue();
								if (!this.isKardio) {
									base_form.findField('MedStaffFact_id').clearValue();
									base_form.findField('PrivilegeType_id').clearValue();
								}

								if ( Ext.isEmpty(newValue) ) {
									base_form.findField('LpuSection_id').disable();
									base_form.findField('MedStaffFact_id').disable();
									base_form.findField('PrivilegeType_id').disable();

									base_form.findField('PrivilegeType_id').fireEvent('change', base_form.findField('PrivilegeType_id'), null);

									this.setReceptFormFilter();

									return false;
								}

								var
									date20160101 = new Date(2016, 0, 1),
									date20160730 = new Date(2016, 6, 30);

								var new_date = newValue.format('Y-m-d');
								var person_information = wnd.PersonInfo;
								//var person_age = swGetPersonAge(person_information.getFieldValue('Person_Birthday'), base_form.findField('EvnRecept_setDate').getValue());
								var person_age = swGetPersonAge(person_information.getFieldValue('Person_Birthday'), newValue);
								var sex_code = person_information.getFieldValue('Sex_Code');
								var is_retired = ((sex_code == 2 && person_age >= 55) || (sex_code == 1 && person_age >= 60)); //опредлеяем, пенсионер ли наш пациент
								var ReceptForm_id = !Ext.isEmpty(base_form.findField('ReceptForm_id').getValue())?base_form.findField('ReceptForm_id').getValue():0;
								if(ReceptForm_id == 2)
									base_form.findField('ReceptValid_id').getStore().filterBy(function(rec) {
										return (rec.get('ReceptValid_Code').toString().inlist(newValue >= date20160101?['4','9','10','11']:['1', '2']));
									});
								else
									base_form.findField('ReceptValid_id').getStore().filterBy(function(rec) {
										return (rec.get('ReceptValid_Code').toString().inlist(newValue >= date20160101?['4', '9', '10', '11']:['1', '2', '4', '7']));
									});

								//https://redmine.swan.perm.ru/issues/91119
								if(newValue >= date20160730){
									if (getRegionNick() == 'kz') {
										this.refreshFieldsVisibility(['ReceptValid_id']);
									} else {
										if (ReceptForm_id == 5) {
											base_form.findField('ReceptValid_id').getStore().filterBy(function (rec) {
												return (rec.get('ReceptValid_Code').toString().inlist(['11']));
											});
										} else if (ReceptForm_id == 1) {
											base_form.findField('ReceptValid_id').getStore().filterBy(function (rec) {
												return (rec.get('ReceptValid_Code').toString().inlist(['9', '10', '11']));
											});
										} else if (ReceptForm_id == 3) {
											base_form.findField('ReceptValid_id').getStore().filterBy(function (rec) {
												return (rec.get('ReceptValid_Code').toString().inlist(['8']));
											});
										} else if (ReceptForm_id == 2) {
											base_form.findField('ReceptValid_id').getStore().filterBy(function (rec) {
												return (rec.get('ReceptValid_Code').toString().inlist(['1', '2', '5']));
											});
										}
									}
								}
								else {
									// Устанавливаем значение по-умолчанию
									index = base_form.findField('ReceptValid_id').getStore().findBy(function(rec) {
										if (getRegionNick() == 'kz') {
											return (rec.get('ReceptValid_Code') == 9);
										}
										if(new_date >= date20160101)
											return is_retired?(rec.get('ReceptValid_Code') == 10):(rec.get('ReceptValid_Code') == 9);
										else
											return is_retired?(rec.get('ReceptValid_Code') == 2):(rec.get('ReceptValid_Code') == 1);
									});
									if ( index >= 0 ) {
										base_form.findField('ReceptValid_id').setValue(base_form.findField('ReceptValid_id').getStore().getAt(index).get('ReceptValid_id'));
									}
								}

								if (this.isKardio) {
									base_form.findField('ReceptValid_id').getStore().clearFilter();
									var idx = base_form.findField('ReceptValid_id').getStore().findBy(function(record) {
										return (record.get('ReceptValid_Code') == '10');
									});
									if (idx >= 0) {
										var id = base_form.findField('ReceptValid_id').getStore().getAt(idx).get('ReceptValid_id');
										base_form.findField('ReceptValid_id').setValue(id);
									}
								}

								this.setReceptNumber();
								this.setReceptSerial();

								base_form.findField('Drug_rlsid').getStore().baseParams.Date = Ext.util.Format.date(newValue, 'd.m.Y');
								base_form.findField('DrugComplexMnn_id').getStore().baseParams.Date = Ext.util.Format.date(newValue, 'd.m.Y');

								if ( this.action != 'view' ) {
									base_form.findField('LpuSection_id').enable();
									if (!this.isKardio) {
										base_form.findField('MedStaffFact_id').enable();
										base_form.findField('PrivilegeType_id').enable();
									}
								}

								base_form.findField('LpuSection_id').getStore().removeAll();
								if (!this.isKardio) {
									base_form.findField('MedStaffFact_id').getStore().removeAll();
									base_form.findField('PrivilegeType_id').getStore().removeAll();
								}

								// Фильтр на записи в регистре заболеваний
								this.personRegisterStore.clearFilter();

								if ( this.personRegisterStore.getCount() > 0 ) {
									this.personRegisterStore.filterBy(function(rec) {
										return (
											(Ext.isEmpty(rec.get('PersonRegister_setDate')) || newValue >= rec.get('PersonRegister_setDate'))
											&& (Ext.isEmpty(rec.get('PersonRegister_disDate')) || newValue <= rec.get('PersonRegister_disDate'))
										);
									});
								}

								// Загружаем список льгот человека
								if (!this.isKardio) {
									base_form.findField('PrivilegeType_id').getStore().load({
										callback: function(records, options, success) {
											// Фильтруем закрытые записи
                                            base_form.findField('PrivilegeType_id').filterClosedPrivilege();

											var index;
											if (getRegionNick() != 'kz') {
												// Фильтруем список программ ЛЛО
												var PersonRegisterTypeList = new Array();
												var PrivilegeTypeList = new Array();

												base_form.findField('PrivilegeType_id').getStore().each(function (rec) {
													if (!Ext.isEmpty(rec.get('WhsDocumentCostItemType_id'))) {
														PrivilegeTypeList.push(rec.get('WhsDocumentCostItemType_id').toString());
													}
												});

												this.personRegisterStore.each(function (rec) {
													if (!Ext.isEmpty(rec.get('PersonRegisterType_id'))) {
														PersonRegisterTypeList.push(rec.get('PersonRegisterType_id').toString());
													}
												});
												base_form.findField('WhsDocumentCostItemType_id').lastQuery = '';
												base_form.findField('WhsDocumentCostItemType_id').getStore().clearFilter();
												if (wnd.action != 'view') {
													base_form.findField('WhsDocumentCostItemType_id').getStore().filterBy(function (rec) {
														return (
															rec.get('WhsDocumentCostItemType_IsDlo') == 2
															&& (Ext.isEmpty(rec.get('WhsDocumentCostItemType_begDate')) || newValue >= rec.get('WhsDocumentCostItemType_begDate'))
															&& (Ext.isEmpty(rec.get('WhsDocumentCostItemType_endDate')) || newValue <= rec.get('WhsDocumentCostItemType_endDate'))
															&& (
																rec.get('WhsDocumentCostItemType_id').toString().inlist(PrivilegeTypeList)
																|| rec.get('PersonRegisterType_id').toString().inlist(PersonRegisterTypeList)
															)
														);
													});
												}
											}
											index = base_form.findField('WhsDocumentCostItemType_id').getStore().findBy(function(rec) {
												return (rec.get('WhsDocumentCostItemType_id') == WhsDocumentCostItemType_id);
											});

											if ( index >= 0 ) {
												base_form.findField('WhsDocumentCostItemType_id').setValue(WhsDocumentCostItemType_id);
											}
											else {
												base_form.findField('WhsDocumentCostItemType_id').clearValue();
											}

											base_form.findField('WhsDocumentCostItemType_id').fireEvent('change', base_form.findField('WhsDocumentCostItemType_id'), base_form.findField('WhsDocumentCostItemType_id').getValue());

											// Подставляем значение
											if ( base_form.findField('PrivilegeType_id').getStore().getCount() == 1 ) {
												index = 0;
											}
											else {
												index = base_form.findField('PrivilegeType_id').getStore().findBy(function(rec) {
													return (rec.get('PrivilegeType_id') == PrivilegeType_id);
												});
											}

											if ( index >= 0 ) {
												base_form.findField('PrivilegeType_id').setValue(base_form.findField('PrivilegeType_id').getStore().getAt(index).get('PrivilegeType_id'));
												base_form.findField('PrivilegeType_id').fireEvent('change', base_form.findField('PrivilegeType_id'), base_form.findField('PrivilegeType_id').getValue());
											}
										}.createDelegate(this),
										params: {
											Person_id: base_form.findField('Person_id').getValue()
										}
									});
								} else {
									base_form.findField('PrivilegeType_id').getStore().load({
										callback: function(records, options, success) {
											var index;

											base_form.findField('WhsDocumentCostItemType_id').getStore().clearFilter();

											index = base_form.findField('WhsDocumentCostItemType_id').getStore().findBy(function(rec) {
												return (rec.get('WhsDocumentCostItemType_Nick') == 'kardio');
											});

											if ( index >= 0 ) {
												WhsDocumentCostItemType_id = base_form.findField('WhsDocumentCostItemType_id').getStore().getAt(index).get('WhsDocumentCostItemType_id');
												base_form.findField('WhsDocumentCostItemType_id').setValue(WhsDocumentCostItemType_id);
												base_form.findField('WhsDocumentCostItemType_id').fireEvent('change', base_form.findField('WhsDocumentCostItemType_id'), base_form.findField('WhsDocumentCostItemType_id').getValue());
											}

											// Подставляем значение
											index = base_form.findField('PrivilegeType_id').getStore().findBy(function(rec) {
												return (rec.get('PrivilegeType_SysNick') == 'kardio');
											});
											if ( index >= 0 ) {
												base_form.findField('PrivilegeType_id').setValue(base_form.findField('PrivilegeType_id').getStore().getAt(index).get('PrivilegeType_id'));
												//base_form.findField('PrivilegeType_id').fireEvent('change', base_form.findField('PrivilegeType_id'), base_form.findField('PrivilegeType_id').getValue());
											}
										}.createDelegate(this),
										params: {
											Person_id: base_form.findField('Person_id').getValue()
										}
									});
								}

								// Устанавливаем параметры для загрузки списка отделений и врачей (для программы ЛЛО Кардио они игнорируются)
								var lpuSectionFilter = !this.isKardio ? {
									isDlo: true
									,allowLowLevel: 'yes'
									,onDate: Ext.util.Format.date(newValue, 'd.m.Y')
								} : {};

								var medStaffFactFilter = !this.isKardio ? {
									isDlo: true
									,allowLowLevel: 'yes'
									,onDate: Ext.util.Format.date(newValue, 'd.m.Y')
									,fromRecept: true
								} : {};

								if ( this.action == 'add' ) {
									// Фильтр на конкретное место работы
									if ( !Ext.isEmpty(this.userMedStaffFact.LpuSection_id) && !Ext.isEmpty(this.userMedStaffFact.MedStaffFact_id) ) {
										lpuSectionFilter.id = this.userMedStaffFact.LpuSection_id;
										medStaffFactFilter.id = this.userMedStaffFact.MedStaffFact_id;
									}
								}
								setLpuSectionGlobalStoreFilter(lpuSectionFilter);
								setMedStaffFactGlobalStoreFilter(medStaffFactFilter);

								base_form.findField('LpuSection_id').getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));
								base_form.findField('MedStaffFact_id').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));

								index = base_form.findField('LpuSection_id').getStore().findBy(function(rec) {
									return (rec.get('LpuSection_id') == LpuSection_id);
								});

								if ( index >= 0 ) {
									base_form.findField('LpuSection_id').setValue(LpuSection_id);

									base_form.findField('Drug_rlsid').getStore().baseParams.LpuSection_id = LpuSection_id;
									base_form.findField('DrugComplexMnn_id').getStore().baseParams.LpuSection_id = LpuSection_id;
									base_form.findField('OrgFarmacy_id').getStore().baseParams.LpuSection_id = LpuSection_id;
								}

								index = base_form.findField('MedStaffFact_id').getStore().findBy(function(rec) {
									return (rec.get('MedStaffFact_id') == MedStaffFact_id);
								});

								if ( index >= 0 ) {
									base_form.findField('MedStaffFact_id').setValue(MedStaffFact_id);
								}

								/**
								 *	Если форма открыта на добавление или редактирование и задано отделение и
								 *	место работы, то не даем редактировать вообще
								 */
								if ( this.action.inlist([ 'add', 'edit']) && !Ext.isEmpty(this.userMedStaffFact.LpuSection_id) && !Ext.isEmpty(this.userMedStaffFact.MedStaffFact_id) ) {
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

											base_form.findField('Drug_rlsid').getStore().baseParams.LpuSection_id = this.userMedStaffFact.LpuSection_id;
											base_form.findField('DrugComplexMnn_id').getStore().baseParams.LpuSection_id = this.userMedStaffFact.LpuSection_id;
											base_form.findField('OrgFarmacy_id').getStore().baseParams.LpuSection_id = this.userMedStaffFact.LpuSection_id;
										}

										index = base_form.findField('MedStaffFact_id').getStore().findBy(function(rec) {
											return (rec.get('MedStaffFact_id') == this.userMedStaffFact.MedStaffFact_id);
										}.createDelegate(this));

										if ( index >= 0 ) {
											base_form.findField('MedStaffFact_id').setValue(this.userMedStaffFact.MedStaffFact_id);
										}
									}
								}

								if (this.action == 'add' && region_nick == 'msk') {
									this.ambulat_card_combo.getStore().baseParams.Date = !Ext.isEmpty(newValue) ? newValue.format('d.m.Y') : null;
									this.ambulat_card_combo.setValueByDefaultValue();
								}

								this.setReceptFormFilter();
							}.createDelegate(this),
							'keydown': function (inp, e) {
								if ( e.shiftKey == true && e.getKey() == Ext.EventObject.TAB ) {
									e.stopEvent();

									if ( !this.FormPanel.getForm().findField('ReceptType_id').disabled ) {
										this.FormPanel.getForm().findField('ReceptType_id').focus(true);
									}
									else {
										this.buttons[this.buttons.length - 1].focus();
									}
								}
							}.createDelegate(this)
						},
						name: 'EvnRecept_setDate',
						plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
						tabIndex: TABINDEX_ERREF + 2,
						validateOnBlur: true,
						xtype: 'swdatefield'
					}, {
                    	fieldLabel: 'Форма рецепта',
						comboSubject: 'ReceptForm',
						xtype: 'swcommonsprcombo',
						width: 517,
						allowBlank: false,
						editable: false,
                        moreFields: [
                            {name: 'ReceptForm_begDate', type: 'date', dateFormat: 'd.m.Y'},
                            {name: 'ReceptForm_endDate', type: 'date', dateFormat: 'd.m.Y'}
                        ],
						listeners: {
							'change': function(combo, newValue, oldValue) {
								var base_form = this.FormPanel.getForm();
								var set_date_value = base_form.findField('EvnRecept_setDate').getValue();
								var new_date = new Date().format('Y-m-d');
								if (set_date_value instanceof Date) {
									new_date = set_date_value.format('Y-m-d');
								}

								var person_information = wnd.PersonInfo;
								var person_age = swGetPersonAge(person_information.getFieldValue('Person_Birthday'), new_date);
								var sex_code = person_information.getFieldValue('Sex_Code');
								var is_retired = ((sex_code == 2 && person_age >= 55) || (sex_code == 1 && person_age >= 60)); //опредлеяем, пенсионер ли наш пациент

								var dcmCombo = base_form.findField('DrugComplexMnn_id');
								var drugCombo = base_form.findField('Drug_rlsid');

								dcmCombo.clearValue();
								dcmCombo.getStore().removeAll();
								dcmCombo.lastQuery = '';
								dcmCombo.getStore().baseParams.query = '';

								drugCombo.clearValue();
								drugCombo.getStore().removeAll();
								drugCombo.lastQuery = '';
								drugCombo.getStore().baseParams.query = '';

								if (newValue == 2){
									/*base_form.findField('ReceptValid_id').getStore().filterBy(function(rec) {
										return (rec.get('ReceptValid_Code').toString().inlist([ '1', '2']));
									});*/
									base_form.findField('ReceptValid_id').getStore().filterBy(function(rec) {
										return (rec.get('ReceptValid_Code').toString().inlist(new_date >= '2016-01-01'?['4','9','10','11']:['1', '2']));
									});
									base_form.findField('EvnRecept_Signa').disable();
									base_form.findField('EvnRecept_Signa').setAllowBlank(true);
									//dcmCombo.disable();
									dcmCombo.setAllowBlank(true);
									dcmCombo.getStore().baseParams.is_mi_1 = true;
									drugCombo.getStore().baseParams.is_mi_1 = true;
									this.setReceptNumber();
								} else{
									/*base_form.findField('ReceptValid_id').getStore().filterBy(function(rec) {
										return (rec.get('ReceptValid_Code').toString().inlist([ '1', '2', '4', '7' ]));
									});*/
									base_form.findField('ReceptValid_id').getStore().filterBy(function(rec) {
										return (rec.get('ReceptValid_Code').toString().inlist(new_date >= '2016-01-01'?['4', '9', '10', '11']:['1', '2', '4', '7']));
									});
									base_form.findField('EvnRecept_Signa').enable();
									base_form.findField('EvnRecept_Signa').setAllowBlank(false);
									//dcmCombo.enable();
									dcmCombo.setAllowBlank(false);
									dcmCombo.getStore().baseParams.is_mi_1 = false;
									drugCombo.getStore().baseParams.is_mi_1 = false;
									this.setReceptNumber();
								}
								//https://redmine.swan.perm.ru/issues/91119
								if(new_date >= '2016-07-30') {
									if (getRegionNick() == 'kz') {
										this.refreshFieldsVisibility(['ReceptValid_id']);
									} else {
										if (newValue == 5) {
											base_form.findField('ReceptValid_id').getStore().filterBy(function (rec) {
												return (rec.get('ReceptValid_Code').toString().inlist(['11']));
											});
										} else if (newValue == 1) {
											base_form.findField('ReceptValid_id').getStore().filterBy(function (rec) {
												return (rec.get('ReceptValid_Code').toString().inlist(['9', '10', '11']));
											});
										} else if (newValue == 3) {
											base_form.findField('ReceptValid_id').getStore().filterBy(function (rec) {
												return (rec.get('ReceptValid_Code').toString().inlist(['8']));
											});
										} else if (newValue == 2) {
											base_form.findField('ReceptValid_id').getStore().filterBy(function (rec) {
												return (rec.get('ReceptValid_Code').toString().inlist(['1', '2', '5']));
											});
										}
										base_form.findField('ReceptValid_id').clearValue();
									}
								}
								else {
									// Устанавливаем значение по-умолчанию
									var index = base_form.findField('ReceptValid_id').getStore().findBy(function(rec) {
										if (getRegionNick() == 'kz') {
											return (rec.get('ReceptValid_Code') == 9)
										}
										if(new_date >= '2016-01-01')
											return is_retired?(rec.get('ReceptValid_Code') == 10):(rec.get('ReceptValid_Code') == 9);
										else
											return is_retired?(rec.get('ReceptValid_Code') == 2):(rec.get('ReceptValid_Code') == 1);
									});
									if ( index >= 0 ) {
										base_form.findField('ReceptValid_id').setValue(base_form.findField('ReceptValid_id').getStore().getAt(index).get('ReceptValid_id'));
									}
								}
								drugCombo.getStore().load();

                                wnd.setEvnReceptIsMnnDefaultValue('change_receptform');
								wnd.setVKProtocolFieldsVisible();
								wnd.setReceptTypeFilter();
								if (getRegionNick() != 'kz') {
									var ReceptUrgencyCombo = base_form.findField('ReceptUrgency_id');
									if (newValue == 9) {
										ReceptUrgencyCombo.enable();
										ReceptUrgencyCombo.showContainer();
									} else {
										ReceptUrgencyCombo.hideContainer();
										base_form.findField('ReceptUrgency_id').clearValue();
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
							}.createDelegate(this)
						}
                    },
                    {
					allowBlank: false,
					listeners: {
						'change': function(combo, newValue, oldValue) {
							var base_form = this.FormPanel.getForm();
							if((newValue == 2 || (newValue == 3 && base_form.findField('EvnRecept_IsSigned').getValue() == 2)) && base_form.findField('Lpu_id').getValue() == getGlobalOptions().lpu_id) {
                                this.showPrintButton(true);
							} else {
                                this.showPrintButton(false);
							}
							var index = combo.getStore().findBy(function(rec) {
								return (rec.get(combo.valueField) == newValue);
							});
							var record;

							if ( index == -1 ) {
								index = combo.getStore().findBy(function(rec) {
									return (rec.get('ReceptType_Code') == 2);
								});
								record = combo.getStore().getAt(index);

								combo.setValue(record.get(combo.valueField));
							}
							else {
								record = combo.getStore().getAt(index);
							}

							base_form.findField('EvnRecept_Num').setRawValue('');
							base_form.findField('EvnRecept_Ser').setRawValue('');

							base_form.findField('Drug_rlsid').getStore().baseParams.ReceptType_Code = record.get('ReceptType_Code');
							base_form.findField('DrugComplexMnn_id').getStore().baseParams.ReceptType_Code = record.get('ReceptType_Code');
							base_form.findField('OrgFarmacy_id').getStore().baseParams.ReceptType_Code = record.get('ReceptType_Code');

							if ( record.get('ReceptType_Code') == 1 ) {
								base_form.findField('EvnRecept_Num').enable();
								base_form.findField('EvnRecept_Ser').enable();
								base_form.findField('EvnRecept_Num').enable_blocked = false;
								base_form.findField('EvnRecept_Ser').enable_blocked = false;
								//base_form.findField('EvnRecept_Signa').disable();
								//base_form.findField('EvnRecept_Signa').setAllowBlank(true);
								//base_form.findField('EvnRecept_Signa').setRawValue('');
							}
							else {
								base_form.findField('EvnRecept_Num').disable();
								base_form.findField('EvnRecept_Ser').disable();
								base_form.findField('EvnRecept_Num').enable_blocked = true;
								base_form.findField('EvnRecept_Ser').enable_blocked = true;
								//base_form.findField('EvnRecept_Signa').enable();
								//base_form.findField('EvnRecept_Signa').setAllowBlank(false);
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
					tabIndex: TABINDEX_ERREF + 1,
					validateOnBlur: true,
					xtype: 'swrecepttypecombo',
					width: 180
				}, {
					border: false,
					layout: 'column',
					items: [{
						border: false,
						layout: 'form',
						hidden: getRegionNick().inlist(['kz']),
						items: [{
							allowBlank: getRegionNick().inlist(['kz']),
							allowDecimals: false,
							allowNegative: false,
							autoCreate: {
								tag: 'input',
								type: 'text',
								maxLength: getGlobalOptions().region.nick == 'ufa' ? '10' : '7'
							},
							fieldLabel: 'Серия',
							name: 'EvnRecept_Ser',
							tabIndex: TABINDEX_ERREF + 3,
							validateOnBlur: true,
							xtype: 'textfield',
							width: 180
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
							tabIndex: TABINDEX_ERREF + 4,
							validateOnBlur: true,
							xtype: 'textfield',
							width: 180
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
					moreFields: [
						{name: 'ReceptValid_begDT', type: 'date', dateFormat: 'd.m.Y'},
						{name: 'ReceptValid_endDT', type: 'date', dateFormat: 'd.m.Y'},
						{name: 'ReceptValid_Value', type: 'int'},
						{name: 'ReceptValidType_id', type: 'int'}
					],
					lastQuery: '',
					tabIndex: TABINDEX_ERREF + 5,
					validateOnBlur: true,
					xtype: 'swcommonsprcombo',
					width: 180,
						listeners: {
							change: function() {
								wnd.setEvnReceptMaxKurs();
							}
						}
					}, {
					allowBlank: false,
					id: 'ERREF_LpuSectionCombo',
					lastQuery: '',
					linkedElements: [
						'ERREF_MedStaffFactCombo'
					],
					listWidth: 700,
					tabIndex: TABINDEX_ERREF + 6,
					validateOnBlur: true,
					width: 517,
					xtype: 'swlpusectionglobalcombo',
					listeners: {
                        change: function(combo, newValue, oldValue) {
                            var base_form = wnd.FormPanel.getForm();
                            base_form.findField('Drug_rlsid').getStore().baseParams.LpuSection_id = newValue;
                            base_form.findField('DrugComplexMnn_id').getStore().baseParams.LpuSection_id = newValue;
                            base_form.findField('OrgFarmacy_id').getStore().baseParams.LpuSection_id = newValue;
							wnd.setReceptNumber();
						}
					}
				}, {
					allowBlank: false,
					id: 'ERREF_MedStaffFactCombo',
					lastQuery: '',
					parentElementId: 'ERREF_LpuSectionCombo',
					listWidth: 700,
					tabIndex: TABINDEX_ERREF + 7,
					validateOnBlur: true,
					width: 517,
					xtype: 'swmedstafffactglobalcombo',
					listeners: {
						change: function(combo, newValue, oldValue) {
							var base_form = wnd.FormPanel.getForm();
							var lpusection_id = null;

							if (newValue > 0) {
                                var idx = combo.getStore().findBy(function(rec) {
                                	return rec.get('MedStaffFact_id') == newValue;
                                });
                                if (idx >= 0) {
                                    lpusection_id = combo.getStore().getAt(idx).get('LpuSection_id');
                                }
							}

							base_form.findField('Drug_rlsid').getStore().baseParams.LpuSection_id = lpusection_id;
							base_form.findField('DrugComplexMnn_id').getStore().baseParams.LpuSection_id = lpusection_id;
							base_form.findField('OrgFarmacy_id').getStore().baseParams.LpuSection_id = lpusection_id;
						}
					}
				}, {
					checkAccessRights: true,
					allowBlank: false,
					fieldLabel: 'Диагноз',
					hiddenName: 'Diag_id',
					listWidth: 600,
					tabIndex: TABINDEX_ERREF + 8,
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
				id: 'ERREF_PrivilegePanel',
				layout: 'form',
				style: 'margin-bottom: 0.5em;',
				title: '2. Льгота',
				items: [
					this.privilege_type_combo,
				{
					allowBlank: false,
					codeField: 'WhsDocumentCostItemType_Code',
					displayField: 'WhsDocumentCostItemType_Name',
					editable: false,
					fieldLabel: 'Программа ЛЛО',
					hiddenName: 'WhsDocumentCostItemType_id',
					lastQuery: '',
					listeners: {
						'change': function(combo, newValue, oldValue) {
							var index = combo.getStore().findBy(function(rec) {
								return (rec.get(combo.valueField) == newValue);
							});

							combo.fireEvent('select', combo, combo.getStore().getAt(index));

							if ( index >= 0 ) {
								var base_form = this.FormPanel.getForm();

								base_form.findField('Drug_rlsid').clearValue();
								base_form.findField('Drug_rlsid').getStore().removeAll();
								if(wnd.action != 'view') //Костылина по задаче https://redmine.swan.perm.ru/issues/81914
								{
									base_form.findField('DrugComplexMnn_id').clearValue();
									base_form.findField('DrugComplexMnn_id').getStore().removeAll();
								}
								base_form.findField('OrgFarmacy_id').clearValue();
								base_form.findField('OrgFarmacy_id').getStore().removeAll();
								base_form.findField('EvnRecept_IsNotOstat').setValue(null);
								base_form.findField('ReceptDelayType_id').setValue(null);
							}

							this.setReceptNumber();
							this.setReceptSerial();
						}.createDelegate(this),
						'select': function(combo, record) {
							var base_form = this.FormPanel.getForm();

							if ( record ) {
								/*if ( record.get('WhsDocumentCostItemType_Code') == 2 ) {
									base_form.findField('ReceptDiscount_id').setValue(2);
								}
								else {
									base_form.findField('ReceptDiscount_id').setValue(1);
								}*/
								this.setReceptDiscount();

								base_form.findField('DrugFinance_id').setValue(record.get('DrugFinance_id'));

								base_form.findField('Drug_rlsid').getStore().baseParams.DrugFinance_id = record.get('DrugFinance_id');
								base_form.findField('Drug_rlsid').getStore().baseParams.WhsDocumentCostItemType_id = record.get('WhsDocumentCostItemType_id');
								base_form.findField('Drug_rlsid').getStore().baseParams.PersonRegisterType_id = record.get('PersonRegisterType_id');
								if(wnd.action != 'view') //Костылина по задаче https://redmine.swan.perm.ru/issues/81914
								{
									base_form.findField('DrugComplexMnn_id').getStore().baseParams.DrugFinance_id = record.get('DrugFinance_id');
									base_form.findField('DrugComplexMnn_id').getStore().baseParams.WhsDocumentCostItemType_id = record.get('WhsDocumentCostItemType_id');
									base_form.findField('DrugComplexMnn_id').getStore().baseParams.PersonRegisterType_id = record.get('PersonRegisterType_id');
								}
								base_form.findField('OrgFarmacy_id').getStore().baseParams.WhsDocumentCostItemType_id = record.get('WhsDocumentCostItemType_id');
							}
							else {
								base_form.findField('ReceptDiscount_id').clearValue();
								base_form.findField('DrugFinance_id').clearValue();

								base_form.findField('Drug_rlsid').getStore().baseParams.WhsDocumentCostItemType_id = 0;
								base_form.findField('Drug_rlsid').getStore().baseParams.PersonRegisterType_id = 0;
								if(wnd.action != 'view') //Костылина по задаче https://redmine.swan.perm.ru/issues/81914
								{
									base_form.findField('DrugComplexMnn_id').getStore().baseParams.WhsDocumentCostItemType_id = 0;
									base_form.findField('DrugComplexMnn_id').getStore().baseParams.PersonRegisterType_id = 0;
								}
								base_form.findField('OrgFarmacy_id').getStore().baseParams.WhsDocumentCostItemType_id = 0;
							}
						}.createDelegate(this)
					},
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
							{ name: 'DrugFinance_id', mapping: 'DrugFinance_id', type: 'int' },
							{ name: 'PersonRegisterType_id', mapping: 'PersonRegisterType_id', type: 'int' }
						],
						sortInfo: {
							field: 'WhsDocumentCostItemType_id'
						},
						tableName: 'WhsDocumentCostItemType'
					}),
					tabIndex: TABINDEX_ERREF + 10,
					tpl: new Ext.XTemplate(
						'<tpl for="."><div class="x-combo-list-item">',
						'<font color="red">{WhsDocumentCostItemType_Code}</font>&nbsp;{WhsDocumentCostItemType_Name}',
						'</div></tpl>'
					),
					validateOnBlur: true,
					valueField: 'WhsDocumentCostItemType_id',
					width: 517,
					xtype: 'swbaselocalcombo'
				}, {
					border: false,
					layout: 'column',
					items: [{
						border: false,
						layout: 'form',
						items: [{
							allowBlank: false,
							comboSubject: 'DrugFinance',
							disabled: true,
							fieldLabel: 'Тип финансирования',
							hiddenName: 'DrugFinance_id',
							moreFields: [
								{name: 'DrugFinance_SysNick', mapping: 'DrugFinance_SysNick'}
							],
							tabIndex: TABINDEX_ERREF + 11,
							width: 200,
							xtype: 'swcommonsprcombo',
                            listeners: {
                                'change': function(combo, newValue, oldValue) {
                                    var base_form = this.FormPanel.getForm();

									base_form.findField('Drug_rlsid').getStore().baseParams.DrugFinance_id = newValue;
									if(wnd.action != 'view') //Костылина по задаче https://redmine.swan.perm.ru/issues/81914
									{
										base_form.findField('DrugComplexMnn_id').getStore().baseParams.DrugFinance_id = newValue;
									}
								}.createDelegate(this)
							}
						}]
					}, {
						border: false,
						labelWidth: 120,
						layout: 'form',
						items: [{
							comboSubject: 'ReceptDiscount',
							disabled: true,
							fieldLabel: 'Скидка',
							hiddenName: 'ReceptDiscount_id',
							listWidth: 100,
							tabIndex: TABINDEX_ERREF + 12,
							validateOnBlur: true,
							width: 100,
							xtype: 'swcommonsprcombo'
						}]
					}]
				},
					wnd.ambulat_card_combo
				]
			}),
			new sw.Promed.Panel({
				autoHeight: true,
				bodyStyle: 'padding-top: 0.5em;',
				border: true,
				collapsible: true,
				id: 'ERREF_DrugPanel',
				layout: 'form',
				style: 'margin-bottom: 0.5em;',
				title: '3. Медикамент',
				items: [{
                    border: false,
                    layout: 'column',
                    items: [{
                        border: false,
                        layout: 'form',
                        items: [{
                            fieldLabel: 'Протокол ВК',
                            hiddenName: 'EvnRecept_IsKEK',
                            tabIndex: TABINDEX_ERREF + 14,
                            width: 80,
                            xtype: 'swyesnocombo',
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
								var base_form = wnd.FormPanel.getForm();
								var dcm_combo = base_form.findField('DrugComplexMnn_id');
								dcm_combo.getStore().baseParams.EvnRecept_IsKEK = this.getValue();
								wnd.setVKProtocolFieldsVisible();
								wnd.setDrugFieldVisible();
							}
                        }]
                    }, {
                        border: false,
                        layout: 'form',
                        labelWidth: 40,
                        items: [{
                            fieldLabel: '№',
                            name: 'EvnRecept_VKProtocolNum',
                            tabIndex: TABINDEX_ERREF + 14,
                            width: 80,
                            xtype: 'textfield',
                            maxLength: 100
                        }]
                    }, {
                        border: false,
                        layout: 'form',
                        labelWidth: 40,
                        items: [{
                            fieldLabel: 'дата',
                            name: 'EvnRecept_VKProtocolDT',
                            tabIndex: TABINDEX_ERREF + 14,
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
							tabIndex: TABINDEX_ERREF + 17,
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
								id: 'ERREF_PrescrSpecCause_cb',
								xtype: 'checkbox',
								listeners: {
									'check': function (checkbox, value) {
										var base_form = wnd.FormPanel.getForm();
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
										var base_form = wnd.FormPanel.getForm();
										base_form.findField('PrescrSpecCause_id').getStore().filterBy(function (rec) {
											return (rec.get('PrescrSpecCause_Code') == 1);
										});

									}
								}
							}]
						}]
					}, {
					border: false,
					layout: 'column',
					items: [{
						border: false,
						layout: 'form',
						items: [{
							fieldLabel: 'Выписка по МНН',
							hiddenName: 'EvnRecept_IsMnn',
							listWidth: 80,
							tabIndex: TABINDEX_ERREF + 13,
							validateOnBlur: true,
							width: 80,
							xtype: 'swyesnocombo',
							listeners: {
								'select': function(combo, record) {
									var base_form = this.FormPanel.getForm();
									var mnn_combo = base_form.findField('DrugComplexMnn_id');
									var drug_rls_combo = base_form.findField('Drug_rlsid');

									mnn_combo.clearValue();
									mnn_combo.getStore().removeAll();
									drug_rls_combo.clearValue();
									drug_rls_combo.getStore().removeAll();

									combo.setLinkedFieldValues();
								}.createDelegate(this)
							},
							setLinkedFieldValues: function() {
								var base_form = wnd.FormPanel.getForm();
								var mnn_combo = base_form.findField('DrugComplexMnn_id');
								var drug_rls_combo = base_form.findField('Drug_rlsid');
								mnn_combo.getStore().baseParams.EvnRecept_IsMnn = this.getValue();
								drug_rls_combo.getStore().baseParams.EvnRecept_IsMnn = this.getValue();
								wnd.setDrugFieldVisible();
							}
						}]
					}]
				}, {
					hiddenName: 'DrugComplexMnn_id',
					fieldLabel: 'Наименование',
					listWidth: 800,
                    tabIndex: TABINDEX_ERREF + 15,
                    width: 517,
                    xtype: 'swreceptdrugcomplexmnncombo',
					allowBlank: false,
					listeners: {
						'beforequery': function(qe){
							delete qe.combo.lastQuery;
						},
						'beforeselect': function() {
							this.FormPanel.getForm().findField('Drug_rlsid').lastQuery = '';
							return true;
						}.createDelegate(this),
						'change': function(combo, newValue, oldValue) {
							// Выбрано значение поля "МНН"
							var base_form = this.FormPanel.getForm();

							var drugCombo = base_form.findField('Drug_rlsid');
							var orgFarmacyCombo = base_form.findField('OrgFarmacy_id');

							drugCombo.clearValue();
							drugCombo.getStore().removeAll();
							drugCombo.lastQuery = '';

							base_form.findField('Drug_Price').setRawValue('');
							base_form.findField('WhsDocumentUc_id').setValue(null);

							// Устанавливаем значения базовых параметров поля "Торговое наименование"
							drugCombo.getStore().baseParams.DrugComplexMnn_id = newValue;
							drugCombo.getStore().baseParams.query = '';

							if (this.SelectDrugFromList && this.SelectDrugFromList.inlist(['allocation','request_and_allocation'])) {
								drugCombo.getStore().baseParams.DrugOstatRegistry_id = combo.getFieldValue('DrugOstatRegistry_id');

								orgFarmacyCombo.getStore().baseParams.WhsDocumentSupply_id = combo.getFieldValue('WhsDocumentSupply_id');
								orgFarmacyCombo.getStore().baseParams.query = '';
							}

							wnd.setPrice();

							// Если поле не пустое
							if ( !Ext.isEmpty(newValue) ) {
								// загружаем список медикаментов
								drugCombo.getStore().load();
								wnd.setDefaultDrugPackValues();
								base_form.findField('PrescriptionIntroType_id').setValue(1); //при вводе медикамента стандартное значение "пероральное введение"
							}

							wnd.setEvnReceptMaxKurs();
							wnd.loadOrgFarmacyComboByDrugData();
							wnd.setEvnReceptIsMnnDefaultValue('change_drugcomplexmnn');
							if (!Ext.isEmpty(newValue)) {
								Ext.Ajax.request({
									params: {
										DrugComplexMnn_id: newValue
									},
									success: function (response, options) {
										var result = Ext.util.JSON.decode(response.responseText);
										if (result['isNarcoOrStrongDrug']) {
											var ReceptForm_id = base_form.findField('ReceptForm_id').getValue();
											if (ReceptForm_id == 9) {
												base_form.findField('EvnRecept_IsExcessDose').showContainer();
											}
										}
									}.createDelegate(this),
									url: '/?c=EvnRecept&m=isNarcoOrStrongDrug'
								});
							}
							return true;
						}.createDelegate(this),
						'keydown': function(inp, e) {
							if ( e.getKey() == Ext.EventObject.DELETE || e.getKey() == Ext.EventObject.F4 ) {
								e.stopEvent();

								var base_form = this.FormPanel.getForm();

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
										base_form.findField('Drug_rlsid').getStore().baseParams.DrugComplexMnn_id = 0;
									break;

									case Ext.EventObject.F4:
										inp.onTrigger2Click();
									break;
								}
							}

							return true;
						}.createDelegate(this)
					},
					onTrigger2Click: function() {
						var base_form = this.FormPanel.getForm();
						var search_wnd_params = new Object();
						var search_url = C_DRUG_COMPLEX_MNN_LIST;

						var drugMnnCombo = base_form.findField('DrugComplexMnn_id');
						var receptTypeCombo = base_form.findField('ReceptType_id');
						var receptDateField = base_form.findField('EvnRecept_setDate');

						if (drugMnnCombo.disabled) {
							return false;
						}

						if (region_nick.inlist(['msk'])) {
							search_wnd_params.fixed_search_params = new Object();
							Ext.apply(search_wnd_params.fixed_search_params, drugMnnCombo.getStore().baseParams)
						} else {
							search_wnd_params.EvnRecept_setDate = !Ext.isEmpty(receptDateField.getValue()) ? Ext.util.Format.date(receptDateField.getValue(), 'd.m.Y') : null;
							search_wnd_params.ReceptType_Code = 0;
							search_wnd_params.WhsDocumentCostItemType_id = base_form.findField('WhsDocumentCostItemType_id').getValue();
							search_wnd_params.PersonRegisterType_id = base_form.findField('WhsDocumentCostItemType_id').getFieldValue('PersonRegisterType_id');
							search_wnd_params.Person_id = base_form.findField('Person_id').getValue();
							search_wnd_params.EvnRecept_IsMnn = base_form.findField('EvnRecept_IsMnn').getValue();
							search_wnd_params.EvnRecept_IsKEK = base_form.findField('EvnRecept_IsKEK').getValue();

							var index = receptTypeCombo.getStore().findBy(function(rec) {
								return (rec.get('ReceptType_id') == receptTypeCombo.getValue());
							});
							if (index >= 0) {
								search_wnd_params.ReceptType_Code = receptTypeCombo.getStore().getAt(index).get('ReceptType_Code');
							}

							if (Ext.isEmpty(search_wnd_params.EvnRecept_setDate)) {
								sw.swMsg.alert('Ошибка', 'Не указана дата выписки рецепта', function() { base_form.findField('EvnRecept_setDate').focus(true); });
								return false;
							}
							else if (search_wnd_params.ReceptType_Code == 0) {
								sw.swMsg.alert('Ошибка', 'Не выбран тип рецепта', function() { base_form.findField('ReceptType_id').focus(true); });
								return false;
							}
							else if (Ext.isEmpty(search_wnd_params.WhsDocumentCostItemType_id)) {
								sw.swMsg.alert('Ошибка', 'Не выбрана программа ЛЛО', function() { base_form.findField('WhsDocumentCostItemType_id').focus(true); });
								return false;
							}

							if (!region_nick.inlist(['saratov']) && !this.isKardio) {
								search_url = '/?c=Drug&m=loadDrugComplexMnnJnvlpList';
							}
						}

						search_wnd_params.onClose = function() {
							drugMnnCombo.focus(false);
						};

						search_wnd_params.onSelect = function(drugMnnData) {
							drugMnnCombo.getStore().removeAll();
							drugMnnCombo.getStore().loadData([ drugMnnData ]);
							drugMnnCombo.setValue(drugMnnData.DrugComplexMnn_id);

							var record = drugMnnCombo.getStore().getAt(0);
							if (record) {
								drugMnnCombo.fireEvent('change', drugMnnCombo, record.get('DrugComplexMnn_id'), 0);
							}

							getWnd('swDrugComplexMnnSearchWindow').hide();

							drugMnnCombo.focus(false);
						};

						search_wnd_params.forRecept = true;
						search_wnd_params.searchUrl = search_url;
						search_wnd_params.isKardio = this.isKardio;

						getWnd('swDrugComplexMnnSearchWindow').show(search_wnd_params);
					}.createDelegate(this),
					tabIndex: TABINDEX_ERREF + 15,
					width: 517,
					xtype: 'swreceptdrugcomplexmnncombo',
					clearValue: function() {
						sw.Promed.SwReceptDrugComplexMnnCombo.superclass.clearValue.apply(this, arguments);

						wnd.setEvnReceptIsMnnDefaultValue('change_drugcomplexmnn');
					}
				}, {
					hiddenName: 'Drug_rlsid',
					listeners: {
						'beforequery': function(qe){
							delete qe.combo.lastQuery;
						},
						'beforeselect': function(combo, record, index) {
							var base_form = this.FormPanel.getForm();

							combo.setValue(record.get('Drug_rlsid'));

                            this.setVKProtocolFieldsVisible();

							base_form.findField('WhsDocumentUc_id').setValue(record.get('WhsDocumentUc_id'));

							//автоматическая установка значения в поле "Наименование"Drug_model
							var drug_mnn_combo = base_form.findField('DrugComplexMnn_id');
							var drug_mnn_record = drug_mnn_combo.getStore().getById(record.get('DrugComplexMnn_id'));

							drug_mnn_combo.lastQuery = '';

							if ( drug_mnn_record ) {
								drug_mnn_combo.setValue(record.get('DrugComplexMnn_id'));
							} else {
								drug_mnn_combo.getStore().removeAll();
								drug_mnn_combo.getStore().load({
									callback: function() {
										if (drug_mnn_combo.getStore().getById(record.get('DrugComplexMnn_id'))) {
											drug_mnn_combo.setValue(record.get('DrugComplexMnn_id'));
										} else {
											drug_mnn_combo.clearValue();
										}
									},
									params: {
										DrugComplexMnn_id: record.get('DrugComplexMnn_id')
										,withOptions: true
									}
								});
							}

							if ( record.get('Drug_rlsid') > 0 ) {
								wnd.loadOrgFarmacyComboByDrugData({
									Drug_id: record.get('Drug_rlsid')
								});
							}

							if ( record.get('Drug_IsKEK_Code') == 1 ) {
								sw.swMsg.alert('Сообщение', 'Внимание! Данный медикамент выписывается через ВК', function() { combo.focus(true); });
							}

							return true;
						}.createDelegate(this),
						'blur': function(inp, e) {
							// Если значение поля пустое
							if ( inp.getValue() == '' ) {
								// чистим список аптек
								this.FormPanel.getForm().findField('OrgFarmacy_id').clearValue();
								this.FormPanel.getForm().findField('OrgFarmacy_id').getStore().removeAll();
								this.FormPanel.getForm().findField('EvnRecept_IsNotOstat').setValue(null);
								this.FormPanel.getForm().findField('ReceptDelayType_id').setValue(null);
							}

							return true;
						}.createDelegate(this),
						'keydown': function(inp, e) {
							if ( e.getKey() == Ext.EventObject.DELETE || e.getKey() == Ext.EventObject.F4 ) {
								e.stopEvent();

								var base_form = this.FormPanel.getForm();

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
										base_form.findField('WhsDocumentUc_id').setValue(null);
										base_form.findField('OrgFarmacy_id').clearValue();
										base_form.findField('OrgFarmacy_id').getStore().removeAll();
										base_form.findField('EvnRecept_IsNotOstat').setValue(null);
										base_form.findField('ReceptDelayType_id').setValue(null);
									break;

									case Ext.EventObject.F4:
										inp.onTrigger2Click();
									break;
								}
							}

							return true;
						}.createDelegate(this),
						'change': function() {
							wnd.setPrice();
							wnd.setEvnReceptIsKEKDefaultValue('change_drug');
							wnd.setEvnReceptIsMnnDefaultValue('change_drug');
						}
					},
					listWidth: 800,
					loadingText: 'Идет поиск...',
					onTrigger2Click: function() {
						var base_form = this.FormPanel.getForm();
						var search_wnd_params = new Object();

						var drugCombo = base_form.findField('Drug_rlsid');
						var receptTypeCombo = base_form.findField('ReceptType_id');
						var receptDateField = base_form.findField('EvnRecept_setDate');

						if (drugCombo.disabled) {
							return false;
						}

						if (region_nick.inlist(['msk'])) {
							search_wnd_params.fixed_search_params = new Object();
							Ext.apply(search_wnd_params.fixed_search_params, drugCombo.getStore().baseParams)
						} else {
							search_wnd_params.EvnRecept_setDate = !Ext.isEmpty(receptDateField.getValue()) ? Ext.util.Format.date(receptDateField.getValue(), 'd.m.Y') : null;
							search_wnd_params.ReceptType_Code = 0;
							search_wnd_params.WhsDocumentCostItemType_id = base_form.findField('WhsDocumentCostItemType_id').getValue();
							search_wnd_params.DrugOstatRegistry_id = base_form.findField('DrugComplexMnn_id').getFieldValue('DrugOstatRegistry_id');
							search_wnd_params.PersonRegisterType_id = base_form.findField('WhsDocumentCostItemType_id').getFieldValue('PersonRegisterType_id');
							search_wnd_params.Person_id = base_form.findField('Person_id').getValue();
							search_wnd_params.EvnRecept_IsMnn = base_form.findField('EvnRecept_IsMnn').getValue();
							search_wnd_params.EvnRecept_IsKEK = base_form.findField('EvnRecept_IsKEK').getValue();

							var index = receptTypeCombo.getStore().findBy(function(rec) {
								return (rec.get('ReceptType_id') == receptTypeCombo.getValue());
							});
							if (index >= 0) {
								search_wnd_params.ReceptType_Code = receptTypeCombo.getStore().getAt(index).get('ReceptType_Code');
							}

							if (Ext.isEmpty(search_wnd_params.EvnRecept_setDate)) {
								sw.swMsg.alert('Ошибка', 'Не указана дата выписки рецепта', function() { base_form.findField('EvnRecept_setDate').focus(true); });
								return false;
							}
							else if (search_wnd_params.ReceptType_Code == 0) {
								sw.swMsg.alert('Ошибка', 'Не выбран тип рецепта', function() { base_form.findField('ReceptType_id').focus(true); });
								return false;
							}
							else if (Ext.isEmpty(search_wnd_params.WhsDocumentCostItemType_id)) {
								sw.swMsg.alert('Ошибка', 'Не выбрана программа ЛЛО', function() { base_form.findField('WhsDocumentCostItemType_id').focus(true); });
								return false;
							}
						}

						search_wnd_params.onHide = function() {
							drugCombo.focus(false);
						};

						search_wnd_params.onSelect = function(drugData) {
							drugCombo.getStore().removeAll();
							drugCombo.getStore().loadData([drugData]);
							drugCombo.setValue(drugData.Drug_rlsid);

							var record = drugCombo.getStore().getAt(0);
							if (record) {
								drugCombo.fireEvent('beforeselect', drugCombo, record);
							}

							getWnd('swDrugRlsSearchWindow').hide();

							drugCombo.focus(false);
						};

						search_wnd_params.isKardio = this.isKardio;
						search_wnd_params.is_mi_1 = (base_form.findField('ReceptForm_id').getValue() == 2);

						getWnd('swDrugRlsSearchWindow').show(search_wnd_params);
					}.createDelegate(this),
					tabIndex: TABINDEX_ERREF + 16,
					tpl: new Ext.XTemplate(
						'<tpl for="."><div class="x-combo-list-item">',
						'<table style="width: 100%;"><tr style=\'font-weight: bold; color: #{[values.DrugOstat_Flag == 2 ? "f00" : (values.DrugOstat_Flag == 1 ? "00f" : "000" )]};\'>',
						'<td style="width: 70%;">{Drug_Name}&nbsp;</td>',
						'<td style="width: 30%; text-align: right;">{[values.DrugOstat_Flag == 2 ? "остатков нет" : (values.DrugOstat_Flag == 1 ? "остатки на РАС" : "&nbsp;" )]}</td>',
						'</tr></table>',
						'</div></tpl>'
					),
					width: 517,
					xtype: 'swreceptdrugrlscombo',
					clearValue: function() {
						sw.Promed.SwReceptDrugRlsCombo.superclass.clearValue.apply(this, arguments);

						wnd.setEvnReceptIsKEKDefaultValue('change_drug');
						wnd.setEvnReceptIsMnnDefaultValue('change_drug');
					}
				}, {
                    layout: 'form',
                    border: false,
                    items: [{
                        name: 'EvnCourseTreatDrug_id',
                        xtype: 'hidden'
                    }, {
                        layout: 'column',
                        border: false,
                        items: [{
                            layout: 'form',
                            border: false,
                            items: [{
                                name: 'EvnCourseTreatDrug_KolvoEd',
                                fieldLabel: langs('Кол-во ЛС на 1 прием'),
                                xtype: 'numberfield',
								qtip: 'Указывается количество лекарственного средства (медикамента) в виде количества лекарственных форм,  которое должно быть принято за 1 прием – таблетки, мл, капли и т.п.',
                                allowNegative: false,
                                listeners: {
                                    change: function(field, newValue) {
                                        wnd.setEvnReceptSignaByEvnCourseTreat();

										var fieldKolvo = wnd.FormPanel.getForm().findField('EvnCourseTreatDrug_Kolvo');
										var kolvo = fieldKolvo.getValue();
										var kolvoEd = newValue > 0 ? newValue : 0;
										var dpd = wnd.getDrugPackData();

										if (dpd.GoodsUnit_id ==  wnd.FormPanel.getForm().findField('GoodsUnit_sid').getValue() &&
											((dpd.FasMass_GoodsUnit_id ==  wnd.FormPanel.getForm().findField('GoodsUnit_id').getValue() && dpd.DoseMass_Type == 'Concen')
												|| (dpd.DoseMass_GoodsUnit_id ==  wnd.FormPanel.getForm().findField('GoodsUnit_id').getValue() && dpd.DoseMass_Type == 'Mass'))) { // Если емкость первичной упаковки совпадает ед. измерения дозировки;
											if (kolvoEd > 0 && dpd.Fas_NKolvo > 0) { //если фасовка № 1 перерасчет не происходит
												if (dpd.Fas_Kolvo > 0 && dpd.DoseMass_Kolvo > 0 && (dpd.DoseMass_Type == 'Mass' || dpd.DoseMass_Type == 'KolACT')) { //если указано значащее количество лекарственных форм в первичной упаковке; если дозировка задана ед. изм. массы или активными ед.
													kolvo = kolvoEd * dpd.DoseMass_Kolvo;
												} else if (dpd.FasMass_Kolvo > 0) { //указаны или масса или объем первичной упаковки
													kolvo = kolvoEd*dpd.FasMass_Kolvo;
			                                    }
			                                }

											fieldKolvo.setValue(wnd.floatRound(kolvo));
											wnd.reCountPrescrDose();
										}
                                    }
                                }
                            }]
                        }, {
                            layout: 'form',
                            border: false,
                            style: 'padding: 0; padding-left: 5px;',
                            items: [{
                                hiddenName: 'GoodsUnit_sid',
                                xtype: 'swgoodsunitcombo',
								qtip: 'Указывается количество лекарственного средства (медикамента) в виде количества лекарственных форм,  которое должно быть принято за 1 прием – таблетки, мл, капли и т.п.',
                                hideLabel: true,
                                listWidth: 160,
                                width: 132,
                                listeners: {
                                    select: function() {
                                        wnd.setEvnReceptSignaByEvnCourseTreat();
                                    },
                                    change: function() {
                                        wnd.setEvnReceptSignaByEvnCourseTreat();
                                    }
                                }
                            }]
                        }]
                    }, {
                        layout: 'column',
                        border: false,
                        items: [{
                            layout: 'form',
                            border: false,
                            items: [{
                                name: 'EvnCourseTreatDrug_Kolvo',
                                fieldLabel: langs('Разовая доза'),
                                xtype: 'numberfield',
								qtip: 'Количество действующего вещества в дозе должно быть указано в мерах веса или объема лекарственного вещества – граммах, мили- или микрограммах,  миллилитрах, каплях, или в специальных единицах измерения – Ед, МЕ и др.',
                                allowNegative: false,
								listeners: {
                                	change: function(field, newValue) {
										var fieldKolvoEd = wnd.FormPanel.getForm().findField('EvnCourseTreatDrug_KolvoEd');
										var kolvoEd = fieldKolvoEd.getValue();
										var kolvo = newValue > 0 ? newValue : 0;
										var dpd = wnd.getDrugPackData();

										if (dpd.GoodsUnit_id ==  wnd.FormPanel.getForm().findField('GoodsUnit_sid').getValue() &&
											((dpd.FasMass_GoodsUnit_id ==  wnd.FormPanel.getForm().findField('GoodsUnit_id').getValue() && dpd.DoseMass_Type == 'Concen')
												|| (dpd.DoseMass_GoodsUnit_id ==  wnd.FormPanel.getForm().findField('GoodsUnit_id').getValue() && dpd.DoseMass_Type == 'Mass'))) { // Если емкость первичной упаковки совпадает ед. измерения дозировки;
											if (kolvo > 0 && dpd.Fas_NKolvo > 0) { //если фасовка № 1 перерасчет не происходит
												if (dpd.Fas_Kolvo > 0 && dpd.DoseMass_Kolvo > 0 && (dpd.DoseMass_Type == 'Mass' || dpd.DoseMass_Type == 'KolACT')) { //если указано значащее количество лекарственных форм в первичной упаковке; если дозировка задана ед. изм. массы или активными ед.
													kolvoEd = kolvo/dpd.DoseMass_Kolvo;
												} else if (dpd.FasMass_Kolvo > 0) { //указаны или масса или объем первичной упаковки
													kolvoEd = kolvo/dpd.FasMass_Kolvo;
												}
											}

											fieldKolvoEd.setValue(wnd.floatRound(kolvoEd));
											wnd.reCountPrescrDose();
										}
									}
								}
                            }]
                        }, {
                            layout: 'form',
                            border: false,
                            style: 'padding: 0; padding-left: 5px;',
                            items: [{
                                hiddenName: 'GoodsUnit_id',
                                xtype: 'swgoodsunitcombo',
								qtip: 'Количество действующего вещества в дозе должно быть указано в мерах веса или объема лекарственного вещества – граммах, мили- или микрограммах,  миллилитрах, каплях, или в специальных единицах измерения – Ед, МЕ и др.',
                                hideLabel: true,
                                listWidth: 160,
                                width: 132,
								listeners: {
									change: function() {
										wnd.reCountPrescrDose();
									}
								}
                            }]
                        }]
                    }, {
                        name: 'EvnCourseTreat_CountDay',
                        fieldLabel: langs('Приемов в сутки'),
                        xtype: 'numberfield',
                        allowNegative: false,
                        allowDecimals: false,
                        listeners: {
                            change: function() {
                                wnd.setEvnReceptSignaByEvnCourseTreat();
								wnd.reCountPrescrDose();
                            }
                        }
                    }, {
                        name: 'PrescriptionIntroType_id',
                        fieldLabel: langs('Способ применения'),
                        xtype: 'swcommonsprcombo',
                        comboSubject: 'PrescriptionIntroType',
                        width: 350,
                        listeners: {
                            select: function() {
                                wnd.setEvnReceptSignaByEvnCourseTreat();
                            },
                            change: function() {
                                wnd.setEvnReceptSignaByEvnCourseTreat();
                            }
                        }
                    }, {
                        layout: 'column',
                        border: false,
                        items: [{
                            layout: 'form',
                            border: false,
                            items: [{
                                name: 'EvnCourseTreat_setDate',
                                fieldLabel: langs('Начать с'),
                                plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
                                tabIndex: TABINDEX_ERREF + 2,
                                validateOnBlur: true,
                                xtype: 'swdatefield'
                            }]
                        }, {
                            layout: 'form',
                            border: false,
                            labelWidth: 120,
                            items: [{
                                name: 'EvnCourseTreat_Duration',
                                fieldLabel: langs('Дней приема'),
                                xtype: 'numberfield',
                                allowNegative: false,
                                allowDecimals: false,
                                listeners: {
                                    change: function() {
                                        wnd.setEvnReceptSignaByEvnCourseTreat();
                                        wnd.reCountPrescrDose();
                                    }
                                }
                            }]
                        }]
                    }, {
						layout: 'form',
						border: false,
						items: [{
							name: 'EvnCourseTreat_PrescrDose',
							fieldLabel: langs('Курсовая доза'),
							readOnly: true,
							xtype: 'textfield',
							allowNegative: false,
							allowDecimals: false
						}]
					}]
                }, {
					fieldLabel: 'Превышение дозировки',
					name: 'EvnRecept_IsExcessDose',
					xtype: 'checkbox',
					width: 20
				}, {
					layout: 'column',
					border: false,
					items: [{
						layout: 'form',
						border: false,
						items: [{
							displayField: 'OrgFarmacy_Name',
							enableKeyEvents: true,
							fieldLabel: 'Аптека',
							forceSelection: true,
							hiddenName: 'OrgFarmacy_id',
							lastQuery: '',
							listeners: {
								'select': function(combo, record, index) {
									if (record) {
										if (record.get('OrgFarmacy_id') < 0) {
											combo.reset();
										}
									}
									wnd.loadStorageCombo();
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
										if ( store.getCount() == 1 && store.getAt(0).get('OrgFarmacy_id') > 0 ) {
											var combo = this.FormPanel.getForm().findField('OrgFarmacy_id');
											combo.setValue(store.getAt(0).get('OrgFarmacy_id'));
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
									{ name: 'Storage_id', mapping: 'Storage_id' },
									{ name: 'Storage_Name', mapping: 'Storage_Name' },
									{ name: 'Storage_Kolvo', mapping: 'Storage_Kolvo' },
									{ name: 'DrugOstatRegistry_Kolvo', mapping: 'DrugOstatRegistry_Kolvo' },
									{ name: 'DrugOstatRegistry_Cost', mapping: 'DrugOstatRegistry_Cost' },
									{ name: 'DrugOstatRegistry_updDT', mapping: 'DrugOstatRegistry_updDT' },
									{ name: 'index_exists', mapping: 'index_exists' }
								]),
								url: '/?c=Drug&m=loadFarmacyRlsOstatList'
							}),
							tabIndex: TABINDEX_ERREF + 17,
							tpl: new Ext.XTemplate(
								'<tpl for="."><div class="x-combo-list-item">',
								'<table style="border: 0; width: 100%;{[values.index_exists > 0 ? " font-weight: bolder;" : ""]}{[values.OrgFarmacy_id<0?"color:grey;":""]}"><tr>',
								'<td style="width: 45%;">{OrgFarmacy_Name}&nbsp;{[values.OrgFarmacy_HowGo && values.OrgFarmacy_HowGo.length > 0 ? "<br/>"+values.OrgFarmacy_HowGo : ""]}</td>',
								'<td style="width: 35%;">{Storage_Name}</td>',
								'<td style="width: 20%; text-align: right;">{[this.showOstat && values.OrgFarmacy_id ? values.Storage_Kolvo : "&nbsp;"]}{[!Ext.isEmpty(values.DrugOstatRegistry_updDT) && this.showOstat ? " ("+values.DrugOstatRegistry_updDT+")" : ""]}</td>',
								'</tr></table>',
								'</div></tpl>',
								{
									showOstat: function() {
										return (getGlobalOptions().recept_by_farmacy_drug_ostat > 0 || region_nick != 'msk');
									}
								}
							),
							triggerAction: 'all',
							validateOnBlur: true,
							valueField: 'OrgFarmacy_id',
							width: 517,
							xtype: 'combo'
						}]
					}, {
						layout: 'form',
						border: false,
						items: [{
							text: langs('Обновить'),
							id: 'ERREF_BtnOstatUpd',
							xtype: 'button',
							iconCls: 'refresh16',
							style: 'margin-left: 5px;',
							handler: function() {
								wnd.updateFarmacyRlsOstatListBySpoUlo();
							}
						}]
					}]
				}, {
                    xtype:'combo',
                    displayField: 'Storage_Name',
                    valueField: 'Storage_id',
                    hiddenName: 'Storage_id',
                    fieldLabel: langs('Склад'),
                    mode: 'local',
                    editable: false,
                    allowBlank: true,
                    forceSelection: true,
                    triggerAction: 'all',
                    width:  517,
                    selectOnFocus: true,
                    store: new Ext.data.SimpleStore({
                        id: 0,
                        fields: [
                            'Storage_id',
                            'Storage_Name'
                        ],
                        data: []
                    }),
                    tpl: new Ext.XTemplate(
                        '<tpl for="."><div class="x-combo-list-item">',
                        '{Storage_Name}&nbsp;',
                        '</div></tpl>'
                    ),
					listeners: {
                        'change': function (combo, newValue, oldValue) {
                            wnd.setMaxKolvo();
                            wnd.setPrice();
                            return true;
                        }
                    },
					fullReset: function() {
                        this.getStore().removeAll();
                        this.setAllowBlank(true);
                        this.disable();
                        this.setValue(null);
					}
                }, {
					disabled: !getRegionNick().inlist(['kz']),
					fieldLabel: 'Цена',
					name: 'Drug_Price',
					tabIndex: TABINDEX_ERREF + 18,
					xtype: 'textfield'
				}, {
					border: false,
					layout: 'column',
					items: [{
						border: false,
						layout: 'form',
						items: [{
							allowBlank: false,
							allowNegative: false,
							fieldLabel: 'Количество',
							name: 'EvnRecept_Kolvo',
							tabIndex: TABINDEX_ERREF + 19,
							validateOnBlur: true,
							value: region_nick != 'msk' ? 1 : null,
							xtype: 'numberfield',
							listeners: {
								'change': function (combo, ER_Kolvo) {
									var labelWarning = Ext.getCmp('EvnRecept_KolvoWarning');
									if(labelWarning.hidden == false) {
										labelWarning.hide();
									}
									var MaxKurs = this.FormPanel.getForm().findField('EvnRecept_MaxKurs').getValue();
									if (!Ext.isEmpty(MaxKurs) && ER_Kolvo * 1 > MaxKurs * 1) {
										labelWarning.show();
									}
								}.createDelegate(this)
							}
						}]
					}, {
						border: false,
						labelWidth: 120,
						layout: 'form',
						items: [{
							xtype: 'label',
							style: 'color: red; font-weight: bolder; padding: 2px 10px; margin: 1px 0px; display: block; font: normal 11px tahoma,arial,helvetica,sans-serif;',
							text: 'Превышение!',
							name: 'EvnRecept_KolvoWarning',
							id: 'EvnRecept_KolvoWarning',
						}]
					}]
				}, {
					allowBlank: true,
					fieldLabel: 'Макс. по рецепту',
					name: 'EvnRecept_MaxKurs',
					tabIndex: TABINDEX_ERREF + 20,
					//validateOnBlur: true,
					width: 517,
					xtype: 'textfield',
					listeners: {
						'change': function(combo, MaxKurs) {
							var labelWarning = Ext.getCmp('EvnRecept_KolvoWarning');
							if(labelWarning.hidden == false) {
								labelWarning.hide();
							}
							var ER_Kolvo = this.FormPanel.getForm().findField('EvnRecept_Kolvo').getValue();
							if (ER_Kolvo*1 > MaxKurs*1) {
								var labelWarning = Ext.getCmp('EvnRecept_KolvoWarning');
								labelWarning.show();
							}
						}.createDelegate(this)
					}

				}, {
					allowBlank: false,
					fieldLabel: 'Signa',
					name: 'EvnRecept_Signa',
					tabIndex: TABINDEX_ERREF + 20,
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
				id: 'ERREF_DrugWrong',
				layout: 'form',
				style: 'margin-bottom: 0.5em;',
				title: '4. Информация об отказе',
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
			})*/
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
			labelWidth: 140,
			reader: new Ext.data.JsonReader({
				success: Ext.emptyFn
			}, [
				{ name: 'accessType' },
				{ name: 'Diag_id' },
				{ name: 'Drug_rlsid' },
				{ name: 'DrugFinance_id' },
				{ name: 'DrugComplexMnn_id' },
				{ name: 'EvnRecept_IsKEK' },
				{ name: 'EvnRecept_IsMnn' },
				{ name: 'EvnRecept_Kolvo' },
				{ name: 'EvnRecept_Num' },
				{ name: 'EvnRecept_id' },
				{ name: 'EvnRecept_IsSigned' },
				{ name: 'EvnRecept_pid' },
				{ name: 'EvnRecept_Ser' },
				{ name: 'EvnRecept_setDate' },
				{ name: 'EvnRecept_Signa' },
				{ name: 'EvnRecept_IsDelivery' },
				{ name: 'Lpu_id' },
				{ name: 'LpuSection_id' },
				{ name: 'MedPersonal_id' },
				{ name: 'OrgFarmacy_id' },
				{ name: 'PrivilegeType_id' },
				{ name: 'ReceptDiscount_id' },
				{ name: 'ReceptForm_id' },
				{ name: 'ReceptType_id' },
				{ name: 'ReceptValid_id' },
				{ name: 'WhsDocumentCostItemType_id' },
				{ name: 'WhsDocumentUc_id' },
				{ name: 'Drug_Price' },
				{ name: 'ReceptDelayType_id' },
                { name: 'ReceptForm_id' },
                { name: 'ReceptValid_id' },
				{ name: 'Recept_Result'},
				{ name: 'Recept_Result_Code'},
				{ name: 'Recept_Delay_Info'},
				{ name: 'EvnRecept_Drugs'},
				{ name: 'ReceptOtov_Farmacy' },
				{ name: 'ReceptOtov_Date' },
				//{ name: 'ReceptWrongDelayType_id' },
				//{ name: 'ReceptWrong_DT' },
				//{ name: 'ReceptWrong_Decr' },
				{ name: 'Person_id' },
				{ name: 'EvnRecept_VKProtocolNum' },
				{ name: 'EvnRecept_VKProtocolDT' },
				{ name: 'CauseVK_id' },
				{ name: 'EvnRecept_IsPrinted' },
				{ name: 'PersonAmbulatCard_id' },
                { name: 'EvnCourseTreatDrug_id' },
                { name: 'EvnCourseTreatDrug_KolvoEd' },
                { name: 'EvnCourseTreatDrug_Kolvo' },
                { name: 'GoodsUnit_sid' },
                { name: 'GoodsUnit_id' },
                { name: 'EvnCourseTreat_CountDay' },
                { name: 'PrescriptionIntroType_id' },
                { name: 'EvnCourseTreat_setDate' },
                { name: 'EvnCourseTreat_Duration' },
				{ name: 'PrescrSpecCause_id' },
				{ name: 'ReceptUrgency_id' },
                { name: 'EvnRecept_IsExcessDose' }
			]),
			region: 'center',
			url: C_EVNREC_SAVE_RLS
			/*refreshPanelTitles: function() { //функция пересчитывает номера в наименованиях видимых панелей
                var items = this.items.items;
                var panel_num = 0;
                for (var i = 0; i < items.length; i++) {
                    if (items[i].title != undefined && !items[i].hidden) {
                        panel_num++;
                        var new_title = panel_num+items[i].title.substr(items[i].title.indexOf('.'));
                        items[i].setTitle(new_title);
                    }
                }
			}*/
		});
	
		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.doSave({
						checkPersonAge: true,
						checkPersonDeadDT: true,
						checkPersonSnils: true,
						copy: false,
						print: false,
						sign: false
					});
				}.createDelegate(this),
				iconCls: 'save16',
				tabIndex: TABINDEX_ERREF + 21,
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
					else if ( !this.FormPanel.getForm().findField('EvnRecept_Signa').disabled ) {
						this.FormPanel.getForm().findField('EvnRecept_Signa').focus(true);
					}
					else if ( !this.FormPanel.getForm().findField('EvnRecept_Kolvo').disabled ) {
						this.FormPanel.getForm().findField('EvnRecept_Kolvo').focus(true);
					}
					else {
						this.buttons[this.buttons.length - 1].focus();
					}
				}.createDelegate(this),
				onTabAction: function () {
					this.buttons[2].focus();
				}.createDelegate(this),
				tabIndex: TABINDEX_ERREF + 22,
				text: 'Копи<u>я</u>',
				tooltip: 'Копия рецепта',
				hidden:(getGlobalOptions().lpu_id==null)
			}, {
				handler: function() {
					this.printRecept();
				}.createDelegate(this),
				iconCls: 'print16',
				tabIndex: TABINDEX_ERREF + 23,
				text: '<u>П</u>ечать',
				tooltip: 'Напечатать рецепт'
			}, /*{
				handler: function() {
					this.signRecept();
				}.createDelegate(this),
				iconCls: 'digital-sign16',
				tabIndex: TABINDEX_ERREF + 24,
				text: 'Подписать рецепт',
				tooltip: 'Подписать рецепт'
			},*/ {
				handler: function() {
					this.provideEvnRecept();
				}.createDelegate(this),
				tabIndex: TABINDEX_ERREF + 25,
				text: 'Обеспечить',
				tooltip: 'Обеспечить',
				hidden: true
			}, {
				handler: function() {
					this.putEvnReceptOnDelay();
				}.createDelegate(this),
				tabIndex: TABINDEX_ERREF + 26,
				text: 'Поставить на отсрочку',
				tooltip: 'Поставить на отсрочку',
				hidden: true
			}, {
				handler: function() {
					this.rePrintRecept();
				}.createDelegate(this),
				iconCls: 'print16',
				tabIndex: TABINDEX_ERREF + 27,
				text: '<u>Р</u>аспечатать рецепт повторно',
				tooltip: 'Распечатать рецепт повторно'
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
					if ( !this.buttons[2].hidden ) {
						this.buttons[2].focus();
					}
					else if ( !this.buttons[1].hidden ) {
						this.buttons[1].focus();
					}
					else if ( !this.buttons[0].hidden ) {
						this.buttons[0].focus();
					}
				}.createDelegate(this),
				onTabAction: function () {
					if ( !this.FormPanel.getForm().findField('ReceptType_id').disabled ) {
						this.FormPanel.getForm().findField('ReceptType_id').focus(true);
					}
					else if ( !this.FormPanel.getForm().findField('EvnRecept_setDate').disabled ) {
						this.FormPanel.getForm().findField('EvnRecept_setDate').focus(true);
					}
					else if ( this.action != 'view' ) {
						this.buttons[0].focus();
					}
					else {
						this.buttons[1].focus();
					}
				}.createDelegate(this),
				tabIndex: TABINDEX_ERREF + 27,
				text: BTN_FRMCANCEL,
				tooltip: 'Закрыть окно'
			}],
			items: [
				 this.PersonInfo
				,this.FormPanel
			]
		});

		sw.Promed.swEvnReceptRlsEditWindow.superclass.initComponent.apply(this, arguments);
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
					Ext.getCmp('ERREF_PersonInformationFrame').panelButtonClick(1);
				break;

				case Ext.EventObject.F10:
					Ext.getCmp('ERREF_PersonInformationFrame').panelButtonClick(2);
				break;

				case Ext.EventObject.F11:
					Ext.getCmp('ERREF_PersonInformationFrame').panelButtonClick(3);
				break;

				case Ext.EventObject.F12:
					if (e.ctrlKey == true) {
						Ext.getCmp('ERREF_PersonInformationFrame').panelButtonClick(5);
					}
					else {
						Ext.getCmp('ERREF_PersonInformationFrame').panelButtonClick(4);
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
			var current_window = Ext.getCmp('EvnReceptRlsEditWindow');

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
						print: false,
						sign: false
					});
				break;

				case Ext.EventObject.NUM_ONE:
				case Ext.EventObject.ONE:
					Ext.getCmp('ERREF_ReceptPanel').toggleCollapse();
				break;

				case Ext.EventObject.NUM_TWO:
				case Ext.EventObject.TWO:
					Ext.getCmp('ERREF_PrivilegePanel').toggleCollapse();
				break;

				case Ext.EventObject.NUM_THREE:
				case Ext.EventObject.THREE:
					Ext.getCmp('ERREF_DrugPanel').toggleCollapse();
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
			this.onHide();
		}
	},
	maximizable: true,
	minHeight: 500,
	minWidth: 700,
	modal: true,
	onHide: Ext.emptyFn,
	personRegisterStore: new Ext.data.Store({
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
	}),
	PersonRegisterTypeStore: new Ext.db.AdapterStore({
		autoLoad: true,
		dbFile: 'Promed.db',
		key: 'PersonRegisterType_id',
		fields: [
			{ name: 'PersonRegisterType_id', mapping: 'PersonRegisterType_id', type: 'int' },
			{ name: 'PersonRegisterType_SysNick', mapping: 'PersonRegisterType_SysNick', type: 'string' }
		],
		sortInfo: {
			field: 'PersonRegisterType_id'
		},
		tableName: 'PersonRegisterType'
	}),
	plain: true,
	printRecept: function() {
		var evn_recept_id = this.FormPanel.getForm().findField('EvnRecept_id').getValue();
		var evn_recept_is_signed = this.FormPanel.getForm().findField('EvnRecept_IsSigned').getValue();
        var ReceptForm_id = this.FormPanel.getForm().findField('ReceptForm_id').getValue();
		var evn_recept_set_date = this.FormPanel.getForm().findField('EvnRecept_setDate').getValue().format('Y-m-d');
        switch (this.action) {
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
				if (this.FormPanel.getForm().findField('Recept_Result_Code').getValue() == 4) {
					Ext.Msg.alert(langs('Ошибка'), 'Рецепт удален и не может быть распечатан');
					return false;
				}
				var that = this;
                var region_nick = getRegionNick();

				saveEvnReceptIsPrinted({
					Evn_id: evn_recept_id,
					callback: function(success) {
						if (success == true) {
							if (Ext.globalOptions.recepts.print_extension == 3) {
								if(ReceptForm_id != 2) {
									window.open(C_EVNREC_PRINT_DS, '_blank');
								}
								window.open(C_EVNREC_PRINT + '&EvnRecept_id=' + evn_recept_id, '_blank');

								if (region_nick == 'msk') {
									that.showPrintButton(false);
								}
							} else {
								Ext.Ajax.request({
									url: '/?c=EvnRecept&m=getPrintType',
									callback: function(options, success, response) {
										if (success) {
											var result = Ext.util.JSON.decode(response.responseText);
											var PrintType = '';

											if (region_nick == 'msk') {
												that.showPrintButton(false);
											}

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
													if(PrintType=='') {
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
													break;
												case 9: //148-1/у-04(л)
													if (region_nick == 'msk') {
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
												case 10: //148-1/у-04 (к)
													//игнорируем настройки и печатаем сразу обе стороны
													printBirt({
														'Report_FileName': 'EvnReceptPrint_148_1u04k_2InA4_2019.rptdesign',
														'Report_Params': '&paramEvnRecept=' + evn_recept_id,
														'Report_Format': 'pdf'
													});
													printBirt({
														'Report_FileName': 'EvnReceptPrint_148_1u04_2InA4_2019Oborot.rptdesign',
														'Report_Params': '&paramEvnRecept=' + evn_recept_id,
														'Report_Format': 'pdf'
													});
													break;
												case 1: //148-1/у-04(л), 148-1/у-06(л)
													if (region_nick == 'msk') {
														printBirt({
															'Report_FileName': 'EvnReceptPrint_148_1u04_4InA4_2019.rptdesign',
															'Report_Params': '&paramEvnRecept=' + evn_recept_id,
															'Report_Format': 'pdf'
														});
														break; //в пределах условия для того, чтобы в других регионах выполнение проваливалось в дефолтную секцию
													}
												default:
													var ReportName = 'EvnReceptPrint' + PrintType;
													var ReportNameOb = 'EvnReceptPrintOb' + PrintType;
													if (result.CopiesCount == 1) {
														if (evn_recept_set_date >= '2016-07-30') {
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
														} else if (evn_recept_set_date >= '2016-01-01') {
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
													if (result.server_port != null) {
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
						} else {
							sw.swMsg.alert('Ошибка', 'Ошибка при проставлении признака подписания рецепта');
						}
					}
				});

                break;
        }
	},
	rePrintRecept: function() {
		var that = this,
			base_form = that.FormPanel.getForm(),
			evn_recept_id = base_form.findField('EvnRecept_id').getValue();

		if (base_form.findField('Recept_Result_Code').getValue() == 4) {
			Ext.Msg.alert(langs('Ошибка'), 'Рецепт удален и не может быть распечатан');
			return false;
		}

		//для сохранения форма должна быть редактируемой
		that.action = 'add';
		that.setTitleByAction();
		that.enableEdit(true);

		base_form.findField('EvnRecept_id').setValue(0);
		base_form.findField('EvnRecept_IsSigned').setValue(1);
		base_form.findField('EvnRecept_IsPrinted').setValue(1);
		that.setReceptNumber(function () {
			that.doSave({
				checkPersonAge: true,
				checkPersonDeadDT: true,
				checkPersonSnils: true,
				copy: false,
				print: true,
				callback: function () {
					that.action = 'view';
					that.setTitleByAction();
					that.enableEdit(false);

					var loadMask = new Ext.LoadMask(that.getEl(), {msg: "Загрузка..."});
					loadMask.show();

					Ext.Ajax.request({
						callback: function (options, success, response) {
							loadMask.hide();

							if (success) {
								var response_obj = Ext.util.JSON.decode(response.responseText);

								if (response_obj.success == false) {
									sw.swMsg.alert(langs('Ошибка'), response_obj.Error_Msg ? response_obj.Error_Msg : langs('При '+(getRegionNick()=='msk'?'аннулировании':'удалении')+' старого рецепта произошла ошибка'));
								}
							}
							else {
								sw.swMsg.alert(langs('Ошибка'), langs('При '+(getRegionNick()=='msk'?'аннулировании':'удалении')+' старого рецепта произошла ошибка'));
							}
						}.createDelegate(that),
						params: {
							EvnRecept_id: evn_recept_id
							, ReceptRemoveCauseType_id: 10
							, DeleteType: 1
						},
						url: C_EVNREC_DEL
					});
				}
			});
		});
	},
	receptNumberParams: {
		 EvnRecept_setDate: null
		,WhsDocumentCostItemType_id: null
	},
	resizable: true,
	floatRound: function(value)
	{
		value = parseFloat(value);
		var value_str = value.toString();
		var parts = value_str.split('.');
		if (parts.length > 1 && parts[1].length > 5) {
			value = parseFloat(value.toPrecision(5 + parts[0].length));
		}
		return value;
	},
	reCountPrescrDose: function()
	{
		var base_form = this.FormPanel.getForm(),
			EvnCourseTreat_PrescrDoseField = base_form.findField('EvnCourseTreat_PrescrDose'),
			EvnCourseTreatDrug_Kolvo = base_form.findField('EvnCourseTreatDrug_Kolvo').getValue(), //разовая доза
			EvnCourseTreatDrug_KolvoEd = base_form.findField('EvnCourseTreatDrug_KolvoEd').getValue(), //количество за 1 прием
			EvnCourseTreat_CountDay = base_form.findField('EvnCourseTreat_CountDay').getValue(), //приемов в сутки
			EvnCourseTreat_Duration = base_form.findField('EvnCourseTreat_Duration').getValue(), //дней приема
			ed = base_form.findField('GoodsUnit_id').getRawValue(); //единицы измерения

		if (Ext.isEmpty(EvnCourseTreatDrug_Kolvo) || Ext.isEmpty(EvnCourseTreatDrug_KolvoEd)) {
			EvnCourseTreat_PrescrDoseField.setValue(null);
			return;
		}
		EvnCourseTreat_CountDay = Ext.isEmpty(EvnCourseTreat_CountDay) ? 1:EvnCourseTreat_CountDay;
		EvnCourseTreat_Duration = Ext.isEmpty(EvnCourseTreat_Duration) ? 1:EvnCourseTreat_Duration;

		EvnCourseTreat_PrescrDoseField.setValue(
			(this.floatRound(EvnCourseTreat_Duration * EvnCourseTreat_CountDay * EvnCourseTreatDrug_Kolvo * EvnCourseTreatDrug_KolvoEd)) + ' ' + ed
		);
	},
	getDrugPackData: function() {
		var object_id = this.FormPanel.getForm().findField('DrugComplexMnn_id').getValue();
		var data = {
			Fas_Kolvo: null,
			Fas_NKolvo: null,
			FasMass_Kolvo: null,
			FasMass_GoodsUnit_id: null,
			FasMass_GoodsUnit_Nick: null,
			DoseMass_Kolvo: null,
			DoseMass_Type: null,
			DoseMass_GoodsUnit_id: null,
			DoseMass_GoodsUnit_Nick: null,
			GoodsUnit_id: null,
			GoodsUnit_Nick: null
		};

		if (!Ext.isEmpty(this.DrugPackData) && !Ext.isEmpty(this.DrugPackData[object_id])) {
			Ext.apply(data, this.DrugPackData[object_id]);
		}
		return data;
	},
	setDefaultDrugPackValues: function() {
		var that = this,
			base_form = that.FormPanel.getForm(),
			EvnCourseTreatDrug_KolvoEd = base_form.findField('EvnCourseTreatDrug_KolvoEd').getValue(),
			object_id = base_form.findField('DrugComplexMnn_id').getValue();

		if (Ext.isEmpty(that.DrugPackData)) {
			that.DrugPackData = {};
		}

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT });
		loadMask.show();
		Ext.Ajax.request({
			params: {
				DrugComplexMnn_id: object_id
			},
			url: '/?c=EvnPrescr&m=getDrugPackData',
			callback: function(opt, scs, response) {
				loadMask.hide();
				var response_obj = Ext.util.JSON.decode(response.responseText);
				if (!Ext.isEmpty(response_obj)) {
					that.DrugPackData[object_id] = response_obj;
					var values = {
						EvnCourseTreatDrug_Kolvo: null,
						EvnCourseTreatDrug_KolvoEd: null,
						GoodsUnit_id: null,
						GoodsUnit_sid: null
					};

					if (!Ext.isEmpty(response_obj)) {
						if (EvnCourseTreatDrug_KolvoEd && EvnCourseTreatDrug_KolvoEd > 0) {  // Если KolvoEd заполнено при редактировании
							values.EvnCourseTreatDrug_KolvoEd = EvnCourseTreatDrug_KolvoEd;
						} else {
							values.EvnCourseTreatDrug_KolvoEd = 1;
						}

						//Доза на 1 прием
						if (response_obj.Fas_Kolvo > 0 && response_obj.DoseMass_Kolvo > 0) { //если указано значащее количество лекарственных форм в первичной упаковке
							values.EvnCourseTreatDrug_Kolvo = response_obj.DoseMass_Kolvo * values.EvnCourseTreatDrug_KolvoEd;
							values.GoodsUnit_id = response_obj.DoseMass_GoodsUnit_id; //в качестве ед. изм. - лекарственная форма
						} else if (response_obj.FasMass_Kolvo > 0) {
							values.EvnCourseTreatDrug_Kolvo = response_obj.FasMass_Kolvo * values.EvnCourseTreatDrug_KolvoEd;
							values.GoodsUnit_id = response_obj.FasMass_GoodsUnit_id;
						}

						values.GoodsUnit_sid = response_obj.GoodsUnit_id; //в качестве ед. изм. - лекарственная форма
					}

					base_form.setValues(values);
					that.reCountPrescrDose();
				}
			}
		});
	},
	setEvnReceptIsMnnDefaultValue: function(event_name) {
		var region_nick = getRegionNick();
        var base_form = this.FormPanel.getForm();
		var dcm_combo = base_form.findField('DrugComplexMnn_id');
		var drug_combo = base_form.findField('Drug_rlsid');
		var is_mnn_combo = base_form.findField('EvnRecept_IsMnn');

        var is_mi_1 = (base_form.findField('ReceptForm_id').getValue() == 2); // 2 - 1-МИ
		var is_empty_dcm = Ext.isEmpty(dcm_combo.getValue());
		var is_empty_am = (!is_empty_dcm && Ext.isEmpty(dcm_combo.getFieldValue('Actmatters_id')));
		var is_not_empty_drug = !Ext.isEmpty(drug_combo.getValue());

		if (this.action != 'view') {
			var is_mnn_disabled = false;
			var is_mnn_new_value = null;

			if (event_name == 'show_form') { //выполняется при открытии формы
				is_mnn_new_value = 2; // 2 - Да
			} else {
				if (region_nick == 'msk') {
					if (is_mi_1 || is_empty_am || is_not_empty_drug) {
						is_mnn_new_value = 1; // 1 - Нет
					}
				} else {
					if (is_mi_1 || is_empty_am) {
						is_mnn_new_value = 1; // 1 - Нет
					}
				}

				if (is_mi_1 || (!is_empty_dcm && is_empty_am)) {
					is_mnn_disabled = true;
				}
			}

			if (!Ext.isEmpty(is_mnn_new_value)) {
				is_mnn_combo.setValue(is_mnn_new_value);
				is_mnn_combo.setLinkedFieldValues();
			}
			is_mnn_combo.enable_blocked = is_mnn_disabled;

			this.enableEdit(true);
		}
	},
	setEvnReceptIsKEKDefaultValue: function(event_name) {
		var region_nick = getRegionNick();
        var base_form = this.FormPanel.getForm();
        var drug_combo = base_form.findField('Drug_rlsid');
        var vk_combo = base_form.findField('EvnRecept_IsKEK');

		if (this.action != 'view') {
			/*if (event_name == 'change_drug' && region_nick != 'msk') {
				var is_kek = drug_combo.getFieldValue('Drug_IsKEK');
				var enable_blocked = false;

				if (drug_combo.getValue() > 0 && is_kek > 0) {
					if (is_kek == 2) {
						enable_blocked = true;
					}
				} else {
					is_kek = null;
				}
				vk_combo.setValue(is_kek);
				vk_combo.setLinkedFieldValues();
				vk_combo.enable_blocked = enable_blocked;

				this.enableEdit(true);
			}*/
			if (event_name == 'show_form' && region_nick == 'msk') {
				vk_combo.setValue(1); //1 - Нет
				vk_combo.setLinkedFieldValues();
			}
		}
	},
	setReceptNumber: function(callback) {
		// https://redmine.swan.perm.ru/issues/9875
		// [2012-05-18]: добавил передачу даты выписки рецепта, т.к. в новой схеме получения номера (задача #9292) не учитывается год, номер
		// генерируется по всем рецептам текущего ЛПУ

		// для просмотра новый номер генерить не нужно.
		if (this.action == 'view') {
			return false;
		}

		var base_form = this.FormPanel.getForm();

		//Для МСК получение номера происходит только после заполнения отделения
		var lpuSectionComboValue = base_form.findField('ERREF_LpuSectionCombo').getValue();
		if (getRegionNick() == 'msk' && Ext.isEmpty(lpuSectionComboValue)) {
			return false;
		}
		//Если тип рецепта "На бланке", прерываем функцию
		var rt_combo = base_form.findField('ReceptType_id');
		var index = rt_combo.getStore().findBy(function(rec) { return rec.get('ReceptType_id') == rt_combo.getValue(); });
		if (index > -1) {
			if (rt_combo.getStore().getAt(index).get('ReceptType_Code') == 1) { //на бланке
				return false;
			}
		}
        var is_mi_1 = (base_form.findField('ReceptForm_id').getValue()==2)?true:false;
		if (( Ext.isEmpty(base_form.findField('EvnRecept_setDate').getValue())
			|| Ext.isEmpty(base_form.findField('WhsDocumentCostItemType_id').getValue())
		) && (!is_mi_1)) {
			base_form.findField('EvnRecept_Num').setValue('');
			return false;
		}
		else if (
			this.receptNumberParams.EvnRecept_setDate == base_form.findField('EvnRecept_setDate').getValue()
			&& this.receptNumberParams.WhsDocumentCostItemType_id == base_form.findField('WhsDocumentCostItemType_id').getValue()
		) {
			return false;
		}

		this.receptNumberParams = {
			 EvnRecept_setDate: base_form.findField('EvnRecept_setDate').getValue()
			,WhsDocumentCostItemType_id: base_form.findField('WhsDocumentCostItemType_id').getValue()
		}

		var params = new Object();

		params.ReceptType_id = rt_combo.getValue();
		params.ReceptForm_id = base_form.findField('ReceptForm_id').getValue();
		params.EvnRecept_setDate = Ext.util.Format.date(base_form.findField('EvnRecept_setDate').getValue(), 'd.m.Y');
		params.WhsDocumentCostItemType_id = base_form.findField('WhsDocumentCostItemType_id').getValue();
        params.is_mi_1 = is_mi_1;
        params.EvnRecept_Ser = base_form.findField('EvnRecept_Ser').getValue();
        params.DrugFinance_id = base_form.findField('DrugFinance_id').getValue();
		params.isRLS = 1;
		params.LpuSection_id = lpuSectionComboValue;
		base_form.findField('EvnRecept_Num').setValue('');

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT });
		loadMask.show();
		Ext.Ajax.request({
			callback: function(options, success, response) {
				loadMask.hide();
				if ( success ) {
					var response_obj = Ext.util.JSON.decode(response.responseText);
					if (!response_obj.Error_Msg) {
						base_form.findField('EvnRecept_Num').setValue(response_obj.EvnRecept_Num);
						if (response_obj.EvnRecept_Ser) {
							base_form.findField('EvnRecept_Ser').setValue(response_obj.EvnRecept_Ser);
						}
					}
				}
				else {
					sw.swMsg.alert('Ошибка', 'Ошибка при определении номера рецепта', function() { base_form.findField('EvnRecept_setDate').focus(true); }.createDelegate(this) );
				}
				if (typeof(callback) == 'function') {
					callback();
				}
			}.createDelegate(this),
			params: params,
			url: C_RECEPT_NUM
		});
	},
	setReceptSerial: function() {
		var base_form = this.FormPanel.getForm();

		//Если тип рецепта "На бланке", прерываем функцию
		var rt_combo = base_form.findField('ReceptType_id');
		var index = rt_combo.getStore().findBy(function(rec) { return rec.get('ReceptType_id') == rt_combo.getValue(); });
		if (index > -1) {
			if (rt_combo.getStore().getAt(index).get('ReceptType_Code') == 1) { //на бланке
				return false;
			}
		}
		
		// Если настроено на нумераторы, серия берётся оттуда
		if ((Ext.globalOptions.globals.use_numerator_for_recept && Ext.globalOptions.globals.use_numerator_for_recept == 2)
			||(getRegionNick() == 'msk' && Ext.globalOptions.globals.use_external_service_for_recept_num && Ext.globalOptions.globals.use_external_service_for_recept_num == 1)) {
			return false;
		}

		// Формируем серию рецепта
		switch (getGlobalOptions().region.nick) {
			case 'saratov':
				// https://redmine.swan.perm.ru/issues/14075
				base_form.findField('EvnRecept_Ser').setValue('0063');
				break;
			case 'khak':
				var lpu_id = Ext.globalOptions.globals.lpu_id || 0;
				var fin_nick = base_form.findField('DrugFinance_id').getFieldValue('DrugFinance_SysNick');
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

				if (fin_nick != 'fed' && fin_nick != 'reg') {
					base_form.findField('EvnRecept_Ser').setValue(null);
					return false;
				}

				lpu_store.load({
					callback: function(records, options, success) {
						var ser = '';

						if (fin_nick == 'fed') {
							ser = '951';
						}
						if (fin_nick == 'reg') {
							ser = '950';
						}

						if ( lpu_store.getCount() > 0 ) {
							lpu_ouz = lpu_store.getAt(0).get('Lpu_Ouz');
							if (lpu_ouz) {
								lpu_ouz = (lpu_ouz+'').substr(0,2);
								lpu_ouz = '00'.substr(0,2-lpu_ouz.length)+lpu_ouz;
							} else {
								lpu_ouz = '00';
							}
							ser += lpu_ouz;
						}
						base_form.findField('EvnRecept_Ser').setValue(ser);
					},
					params: {
						where: "where Lpu_id = " + lpu_id
					}
				});
				break;
			case 'ufa':
				var recept_form_field =  base_form.findField('ReceptForm_id');
				// 122894. Если форма рецепта 1-МИ, то серия рецепта, дополняется в начале строки строкой 'МИ'. 
				var serPrefix = ( recept_form_field.getValue() == 2 ) ? 'МИ' : '';

				var ser = '';
				if ( base_form.findField('DrugFinance_id').getFieldValue('DrugFinance_SysNick') == 'fed' ) {
					ser = serPrefix + Ext.globalOptions.recepts.evn_recept_fed_ser;
				} else {
					ser = serPrefix + Ext.globalOptions.recepts.evn_recept_reg_ser;
				}
				base_form.findField('EvnRecept_Ser').setValue(ser);
				break;
			default:
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

				lpu_store.load({
					callback: function(records, options, success) {
						var evn_recept_ser = '';

						if ( lpu_store.getCount() > 0 ) {
							evn_recept_ser = lpu_store.getAt(0).get('Lpu_Ouz');
						}

						base_form.findField('EvnRecept_Ser').setValue(evn_recept_ser);
					},
					params: {
						where: "where Lpu_id = " + lpu_id
					}
				});
				break;
		}
	},
    setTitleByAction: function() {
		var title = "Рецепт";

		if (this.isKardio) {
            title = "Рецепт";
            switch (this.action) {
                case 'add':
                	title += ": Добавление";
                    break;
                case 'edit':
                	title += ": Редактирование";
                	break;
                case 'view':
                    title += ": Просмотр";
                    break;
            }
		} else {
            switch (this.action) {
                case 'add':
                    title = WND_DLO_RCPTADD;
                    break;
                case 'edit':
                    title = WND_DLO_RCPTEDIT;
                    break;
                case 'view':
                    title = WND_DLO_RCPTVIEW;
                    break;
            }
		}

        this.setTitle(title);
    },
    setKardioMode: function(set_mode) {
	    var set_kardio = false;
        var base_form = this.FormPanel.getForm();

	    if (set_mode == 'auto') {
            set_kardio = this.isKardio;
        }

        if (set_kardio) {
			var idx;
			var id;
			var field;

            //Врач равен врачу из случая лечения (не смотря на наличие данных о включении врача в программу ЛЛО). Не редактируется
            base_form.findField('MedStaffFact_id').disable();

            //Тип рецепта – на листе.  Не редактируется
            field = base_form.findField('ReceptType_id');
            idx = field.getStore().findBy(function(record) {
                return (record.get('ReceptType_Code') == '2');
            });
            if (idx >= 0) {
                id = field.getStore().getAt(idx).get('ReceptType_id');
                field.setValue(id);
                //field.fireEvent('change', field, id);
            }
            field.disable();

            //Форма рецепта равна 148-1/у-04(к) и не редактируется.
            field = base_form.findField('ReceptForm_id');
            idx = field.getStore().findBy(function(record) {
                return (record.get('ReceptForm_Code') == '148 (к)');
            });
            if (idx >= 0) {
                id = field.getStore().getAt(idx).get('ReceptForm_id');
                field.setValue(id);
                //field.fireEvent('change', field, id);
            }
            field.disable();

            //Срок действия рецепта равен 90 дней и не редактируется.
            field = base_form.findField('ReceptValid_id');
            idx = field.getStore().findBy(function(record) {
                return (record.get('ReceptValid_Code') == '10');
            });
            if (idx >= 0) {
                id = field.getStore().getAt(idx).get('ReceptValid_id');
                field.setValue(id);
                //field.fireEvent('change', field, id);
            }
            field.disable();

            //Программа ЛЛО равна «ДЛО Кардио» и не редактируется.
            field = base_form.findField('WhsDocumentCostItemType_id');
            idx = field.getStore().findBy(function(record) {
                return (record.get('WhsDocumentCostItemType_Nick') == 'kardio');
            });
            if (idx >= 0) {
                id = field.getStore().getAt(idx).get('WhsDocumentCostItemType_id');
                field.setValue(id);
                //field.fireEvent('change', field, id);
            }
            field.disable();

            //Льгота пациента равна «ДЛО Кардио» и не редактируется.
            field = base_form.findField('PrivilegeType_id');
            idx = field.getStore().findBy(function(record) {
                return (record.get('PrivilegeType_SysNick') == 'kardio');
            });
            if (idx >= 0) {
                id = field.getStore().getAt(idx).get('PrivilegeType_id');
                field.setValue(id);
                //field.fireEvent('change', field, id);
            }
            field.disable();

            //МНН ЛП:  МНН ЛП из остатков прикрепленных аптек, у которых МНН равно выбранному МНН из нормативного перечня
            base_form.findField('Drug_rlsid').getStore().baseParams.recept_drug_ostat_control = 1;
            base_form.findField('Drug_rlsid').getStore().baseParams.recept_empty_drug_ostat_allow = 1;
            base_form.findField('Drug_rlsid').getStore().baseParams.select_drug_from_list = 'jnvlp';
            base_form.findField('DrugComplexMnn_id').getStore().baseParams.recept_drug_ostat_control = 1;
            base_form.findField('DrugComplexMnn_id').getStore().baseParams.recept_empty_drug_ostat_allow = 1;
            base_form.findField('DrugComplexMnn_id').getStore().baseParams.select_drug_from_list = 'jnvlp';
        } else {
            base_form.findField('MedStaffFact_id').enable(); //Врач равен врачу из случая лечения (не смотря на наличие данных о включении врача в программу ЛЛО). Не редактируется
            base_form.findField('ReceptType_id').enable(); //Тип рецепта – на листе.  Не редактируется
            base_form.findField('ReceptForm_id').enable(); //ReceptForm_id //Форма рецепта равна 148-1/у-04(к) и не редактируется.
            base_form.findField('ReceptValid_id').enable(); //ReceptValid_id //Срок действия рецепта равен 90 дней и не редактируется.
            base_form.findField('WhsDocumentCostItemType_id').enable();  //Программа ЛЛО равна «ДЛО Кардио» и не редактируется.
            base_form.findField('PrivilegeType_id').enable(); //Льгота пациента равна «ДЛО Кардио» и не редактируется.

            base_form.findField('Drug_rlsid').getStore().baseParams.recept_drug_ostat_control = -1;
            base_form.findField('Drug_rlsid').getStore().baseParams.recept_empty_drug_ostat_allow = -1;
            base_form.findField('Drug_rlsid').getStore().baseParams.select_drug_from_list = '';
            base_form.findField('DrugComplexMnn_id').getStore().baseParams.recept_drug_ostat_control = -1;
            base_form.findField('DrugComplexMnn_id').getStore().baseParams.recept_empty_drug_ostat_allow = -1;
            base_form.findField('DrugComplexMnn_id').getStore().baseParams.select_drug_from_list = '';
        }
    },
    setVKProtocolFieldsVisible: function() {
        var base_form = this.FormPanel.getForm();
		var vk_combo = base_form.findField('EvnRecept_IsKEK');
		var num_field = base_form.findField('EvnRecept_VKProtocolNum');
		var date_field = base_form.findField('EvnRecept_VKProtocolDT');
		var cause_field = base_form.findField('CauseVK_id');
		var form_field = base_form.findField('ReceptForm_id');
		var form_id = form_field.getValue();
		var is_vk = (vk_combo.getValue() == 2);
		var is_visible = ((getRegionNick() == 'msk' || form_id == 9 || form_id == 10) && is_vk); //9 - 148-1/у-04(л), 10 - 148-1/у-04 (к); нужно переделать на проверку кода, когда истечет срок действия записи с идентификатором 1
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

		this.FormPanel.doLayout();
	},
    setEvnCourseTreatFieldsVisible: function() {
        var base_form = this.FormPanel.getForm();
        var region_nick = getRegionNick();

		var er_pid = base_form.findField('EvnRecept_pid').getValue();
		var ect_exists = (base_form.findField('EvnRecept_pid').getValue() > 0);

		var is_visible = ((this.action == 'view' && region_nick == 'msk' && er_pid > 0) || ect_exists); //отображаем поля назначения либо если регон - Москва и указан родитель рецепта, либо если назначение уже сохранено в данных рецепта

        if (is_visible) {
            base_form.findField('EvnCourseTreatDrug_id').ownerCt.show();
        } else {
            base_form.findField('EvnCourseTreatDrug_id').ownerCt.hide();
        }
	},
	setDrugFieldVisible: function() { //функция которая определяет видимость и обязательность поля "Торговое наименование"
		var base_form = this.FormPanel.getForm();
		var region_nick = getRegionNick();
		var ostat_viewing = (getGlobalOptions().recept_drug_ostat_viewing == '1');
		var from_list = getGlobalOptions().select_drug_from_list;

		var vk_combo = base_form.findField('EvnRecept_IsKEK');
		var ismnn_combo = base_form.findField('EvnRecept_IsMnn');

		var is_vk = (vk_combo.getValue() == 2);
		var is_mnn = (ismnn_combo.getValue() == 2);
		var is_visible = (true);
		var allow_blank = true;

		if (region_nick == 'msk') {
			switch (from_list) {
				case 'jnvlp':
				case 'request':
					is_visible = is_vk;
					allow_blank = true;
					break;
			}
		} else {
			switch (from_list) {
				case 'jnvlp':
				case 'request':
					if (!ostat_viewing && is_mnn) { //без просмотра остатков и "выписка по МНН" = "да"
						is_visible = false;
					}
					break;
			}
		}

		base_form.findField('Drug_rlsid').setContainerVisible(is_visible);
		base_form.findField('Drug_rlsid').setAllowBlank(allow_blank || !is_visible);
	},
	setReceptFormFilter: function() {
        var base_form = this.FormPanel.getForm();
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

            if (this.isKardio) {
                allowedReceptFormCodes.push('148 (к)');
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
                });
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
		var base_form = this.FormPanel.getForm();
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
	setMaxKolvo: function() { //установка максимального количества  и цены исходя из выбранных аптек и склада
        var region_nick = getRegionNick();
		var base_form = this.FormPanel.getForm();
        var EmptyDrugOstatAllow = (getGlobalOptions().recept_empty_drug_ostat_allow == '1');

        var recept_type_combo = base_form.findField('ReceptType_id');
        var farmacy_combo = base_form.findField('OrgFarmacy_id');
        var storage_combo = base_form.findField('Storage_id');
        var kolvo_field = base_form.findField('EvnRecept_Kolvo');

        var farmacy_id = farmacy_combo.getValue();
        var storage_id = storage_combo.getValue();

        kolvo_field.maxValue = undefined;

        var dor_kolvo = 0; //количество медикаментов на остатках
        var dor_cost = 0; //цена медикаментов на остатках
		var ras_kolvo = 0; //количество медикаментов на остатках РАС

		//расчет количества медикаментов на остатках РАС
        var idx = farmacy_combo.getStore().findBy(function(rec){
            return (rec.get('OrgFarmacy_id') < 0 && rec.get('DrugOstatRegistry_Kolvo') > 0);
        });
        if (idx >= 0) {
            var record = farmacy_combo.getStore().getAt(idx);
            if (record) {
                ras_kolvo = record.get('DrugOstatRegistry_Kolvo')*1;
            }
		}

		//получение кода типа рецепта
        var recept_type_code = null;
        var idx = recept_type_combo.getStore().findBy(function(rec){
            return (rec.get('ReceptType_id') == recept_type_combo.getValue());
        });
        if (idx >= 0) {
            var record = recept_type_combo.getStore().getAt(idx);
            if (record) {
                recept_type_code = record.get('ReceptType_Code');
            }
        }

        //получение данных об остатках аптеки
        if (!Ext.isEmpty(farmacy_id)) {
            idx = farmacy_combo.getStore().findBy(function(rec){
                return (rec.get('OrgFarmacy_id') == farmacy_id);
            });
            record = farmacy_combo.getStore().getAt(idx);
            if (record) {
				dor_kolvo = record.get('DrugOstatRegistry_Kolvo') * 1;
				dor_cost = record.get('DrugOstatRegistry_Cost') * 1;
			}
        }

        //получение данных об остатках склада
		if (!Ext.isEmpty(storage_id)) {
            idx = storage_combo.getStore().findBy(function(rec){
                return (rec.get('Storage_id') == storage_id);
            });
            record = storage_combo.getStore().getAt(idx);
            if (record) {
                dor_kolvo = record.get('Storage_Kolvo') * 1;
            }
        }

		if (!Ext.isEmpty(farmacy_id)) { //если аптека выбрана можно делать расчеты
            switch(this.OstatType) {
                case 0:
                case 1:
                    base_form.findField('ReceptDelayType_id').setValue(null);
                    break;
                case 2:
                    if (dor_kolvo == 0) {
                        sw.swMsg.alert('Предупреждение', 'ЛС нет на остатках аптеки, его выписка не доступна', function() { farmacy_combo.reset(); storage_combo.fullReset(); farmacy_combo.focus(true); });
                        base_form.findField('ReceptDelayType_id').setValue(null);
                    }
                    break;
                case 3:
                    if (dor_kolvo == 0 && (ras_kolvo == 0) ) {
                        sw.swMsg.alert('Предупреждение', 'ЛС нет на остатках аптеки и Регионального аптечного склада, его выписка не доступна', function() { farmacy_combo.reset(); storage_combo.fullReset(); farmacy_combo.focus(true); });
                        base_form.findField('ReceptDelayType_id').setValue(null);
                    }
                    break;
            }

            if (this.OstatType > 0) {
                if (dor_kolvo > 0) {
                    if (!EmptyDrugOstatAllow && (!recept_type_code || recept_type_code == 2)) {
                        kolvo_field.maxValue = dor_kolvo;
                    }
                } else if (this.OstatType != 2) {
                    if (ras_kolvo > 0) {
                        if (!EmptyDrugOstatAllow && (!recept_type_code || recept_type_code == 2)) {
                            kolvo_field.maxValue = ras_kolvo;
                        }
                    }
                }
            }
        }
	},
	setPrice: function() { //установка максимального количества  и цены исходя из выбранных аптек и склада
        var region_nick = getRegionNick();
		var base_form = this.FormPanel.getForm();

        var farmacy_combo = base_form.findField('OrgFarmacy_id');
        var drug_combo = base_form.findField('Drug_rlsid');
        var dcm_combo = base_form.findField('DrugComplexMnn_id');
        var price_field = base_form.findField('Drug_Price');

        var farmacy_id = farmacy_combo.getValue();
        var drug_id = drug_combo.getValue();
        var dcm_id = dcm_combo.getValue();

        var set_price = (region_nick != 'kz'); //флаг установки цены

        if (set_price) {
        	var new_price = null;
            var dor_cost = 0; //цена медикаментов на остатках
            var drug_price = 0; //цена для тоговоого наименования
            var dcm_price = 0; //цена комплексного мнн

            var ostat_viewing = (getGlobalOptions().recept_drug_ostat_viewing == '1');
            var ostat_control = (getGlobalOptions().recept_drug_ostat_control == '1');

            //получение данных об остатках аптеки
            if (!Ext.isEmpty(farmacy_id)) {
                idx = farmacy_combo.getStore().findBy(function(rec){
                    return (rec.get('OrgFarmacy_id') == farmacy_id);
                });
                record = farmacy_combo.getStore().getAt(idx);
                if (record) {
                    dor_cost = record.get('DrugOstatRegistry_Cost') * 1;
                }
            }

            //получение данных о торговом наименовании
            if (!Ext.isEmpty(drug_id)) {
                idx = drug_combo.getStore().findBy(function(rec){
                    return (rec.get('Drug_rlsid') == drug_id);
                });
                record = drug_combo.getStore().getAt(idx);
                if (record) {
                    drug_price = record.get('Drug_Price') * 1;
                }
            }

            //получение данных о комплексном МНН
            if (!Ext.isEmpty(dcm_id)) {
                idx = dcm_combo.getStore().findBy(function(rec){
                    return (rec.get('DrugComplexMnn_id') == dcm_id);
                });
                record = dcm_combo.getStore().getAt(idx);
                if (record) {
                    dcm_price = record.get('Drug_Price') * 1;
                }
            }

            //приоритет выбора цены: 1) остатки аптеки (если включен контроль или просмотр), 2) тороговое, 3) кмплексное, 4) остатки аптеки  (если контроль остатков выключен)
			if (dor_cost > 0 && (ostat_viewing || ostat_control)) {
            	new_price = dor_cost;
			} else if (drug_price > 0) {
                new_price = drug_price;
			} else if (dcm_price > 0) {
                new_price = dcm_price;
			} else if (dor_cost > 0) {
                new_price = dor_cost;
			}

            price_field.setValue(new_price);
        }
	},
    setEvnReceptSignaByEvnCourseTreat: function() {
        var base_form = this.FormPanel.getForm();
        var signa = '';

        if (
        	this.action == 'add' &&
        	//!Ext.isEmpty(base_form.findField('PrescriptionIntroType_id').getValue()) &&
        	!Ext.isEmpty(base_form.findField('EvnCourseTreatDrug_KolvoEd').getValue()) &&
        	!Ext.isEmpty(base_form.findField('GoodsUnit_sid').getValue()) &&
        	!Ext.isEmpty(base_form.findField('EvnCourseTreat_CountDay').getValue()) /*&&
        	!Ext.isEmpty(base_form.findField('EvnCourseTreat_Duration').getValue())*/
		) {
        	var pit_combo = base_form.findField('PrescriptionIntroType_id');
        	var gus_combo = base_form.findField('GoodsUnit_sid');
        	var pit_id = pit_combo.getValue();
        	var gus_id = gus_combo.getValue();
        	var pit_name = '';
        	var gus_name = '';

            var idx = pit_combo.getStore().findBy(function(record) {
                return (record.get('PrescriptionIntroType_id') == pit_id);
            });
            if (idx > -1) {
                pit_name = pit_combo.getStore().getAt(idx).get('PrescriptionIntroType_Name');
            }

            idx = gus_combo.getStore().findBy(function(record) {
                return (record.get('GoodsUnit_id') == gus_id);
            });
            if (idx > -1) {
                gus_name = gus_combo.getStore().getAt(idx).get('GoodsUnit_Nick');
            }

        	//signa += pit_name + ' ';
        	signa += base_form.findField('EvnCourseTreatDrug_KolvoEd').getValue() + ' ' + gus_name + ' ';
        	signa += base_form.findField('EvnCourseTreat_CountDay').getValue() + ' раз в день';
        	//signa += ' в течение ' + base_form.findField('EvnCourseTreat_Duration').getValue() + ' дн.';
		}

        base_form.findField('EvnRecept_Signa').setValue(signa);
	},
	setDiagErrorMsg: function(err_msg, diag_list) { //функция, которая в сообщении об ошибке, при ниобходимости прячет список диагнозов в скрываемый блок
		var msg = err_msg;

		if (diag_list && diag_list.length > 50) { //если суммарная длинна строки с диагнозами превышает 50 символов, то оборачиваем диагнозы в скрываемый блок
			var btn_function = "getWnd('swEvnReceptRlsEditWindow').setDiagErrorMsgVisible(this);";
			var btn_style = 'color: blue; cursor: pointer; text-decoration: underline; margin-right: 5px;';
			var btn = '<span style="'+btn_style+'" onclick="'+btn_function+'">Показать</span>';
			var list = '<span style="display: none;">'+diag_list+'</span>';
			diag_list = btn+list;
		}

		msg = msg.replace('{diag_list}', diag_list);
		return msg;
	},
	setDiagErrorMsgVisible: function(btn) { //вспомогательная функция для отображения/скрытия блока с диагнозами
		var list = btn.nextSibling;
		if (list) {
			if (!list.dispalyed) {
				btn.innerHTML = 'Скрыть';
				list.dispalyed = true;
				list.style = 'display: inline;';
			} else {
				btn.innerHTML = 'Показать';
				list.dispalyed = false;
				list.style = 'display: none;';
			}

			//подразумевается что сообщение будет находится внутри sw.swMsg, поэтому при раскрытии/скрытии блока с диагнозами нужно пересчитать размеры окна с сообщением
			var dialog = sw.swMsg.getDialog();
			if (dialog) {
				dialog.syncShadow();
				dialog.center();
			}
		}
	},
	getReceptElectronicAllow: function(callback) { //вычисление допустимости выбора электронного рецпта, исходя из настроек и наличия у пациента разрешения на фвписку такого рецепта
		var wnd = this;
		var base_form = this.FormPanel.getForm();
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
	updateFarmacyRlsOstatListBySpoUlo: function(mode, callback) { //функция выполняет обращение к сервису обновления остатков (АПИ СПО УЛО), после обновления, выполняется обновление содержимого комбобокса дял выбора аптеки
		var wnd = this;
		var base_form = this.FormPanel.getForm();
		var err_msg = '';
		var params = new Object();
		var field_array = new Array( //список обязательных полей
			'LpuSection_id',
			'PrivilegeType_id',
			'WhsDocumentCostItemType_id',
			'DrugFinance_id',
			'DrugComplexMnn_id',
			'Drug_rlsid'
		);

		for (var i = 0; i < field_array.length; i++) {
			var name = field_array[i];
			var field = base_form.findField(name);
			var value = field.getValue();
			if (!Ext.isEmpty(value)) {
				if (name == 'Drug_rlsid') {
					name = 'Drug_id';
				}
				params[name] = value;
			} else {
				if (name != 'DrugComplexMnn_id' && (name != 'Drug_rlsid' || Ext.isEmpty(params.DrugComplexMnn_id))) {
					if (name == 'Drug_rlsid') {
						err_msg = 'Необходимо заполнить поле Наименование или '+field.fieldLabel;
					} else {
						err_msg = 'Необходимо заполнить поле '+field.fieldLabel;
					}
					break;
				}
			}
		}

		if (Ext.isEmpty(err_msg)) {
			var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Обновление данных..." });
			var of_field = base_form.findField('OrgFarmacy_id');
			if (mode != 'mute') {
				loadMask.show();
			}
			Ext.Ajax.request({
				params: params,
				url: '/?c=Drug&m=updateFarmacyRlsOstatListBySpoUlo',
				callback: function (options, success, response) {
					if (success) {
						var response_obj = Ext.util.JSON.decode(response.responseText);
						if (response_obj.success) {
							of_field.setValue(null);
							of_field.getStore().reload({
								callback: function() {
									if (mode != 'mute') {
										loadMask.hide();
									}
									if (typeof callback == 'function') {
										callback({
											event_name: 'store_reload',
											success: true
										});
									}
								}
							});
						} else {
							if (mode != 'mute') {
								loadMask.hide();
							}
							if (typeof callback == 'function') {
								callback({
									event_name: 'spu_ulo_request',
									success: false
								});
							}
						}
					} else {
						if (mode != 'mute') {
							loadMask.hide();
						}
						if (typeof callback == 'function') {
							callback({
								event_name: 'spu_ulo_request',
								success: false
							});
						}
					}
				}
			});
		}

		if (!Ext.isEmpty(err_msg)) {
			if (mode != 'mute') {
				sw.swMsg.alert('Ошибка', err_msg);
			}
			if (typeof callback == 'function') {
				callback({
					event_name: 'update_function',
					success: false
				});
			}
		}
		return true;
	},
	checkFarmacyRlsOstatListEmptiness: function() { //проверка пусты ли текущие остатки в комбобоксе аптека
		var base_form = this.FormPanel.getForm();
		var farmacy_combo = base_form.findField('OrgFarmacy_id');
		var is_empty = true;

		//проверяем наличие ненулевых остатаков
		farmacy_combo.getStore().each(function(record) {
			if (record.get('DrugOstatRegistry_Kolvo') > 0) {
				is_empty = false;
				return false;
			}
		});

		return is_empty;
	},
	/*checkFarmacyRlsOstatListUpdateNecessarity: function() { //проверка и при необходимости обновление списка остатков
		var base_form = this.FormPanel.getForm();
		var farmacy_combo = base_form.findField('OrgFarmacy_id');
		var region_nick = getRegionNick();
		var need_update = (region_nick == 'msk'); //проверка работает только для Москвы

		if (need_update) {
			//проверяем наличие ненулевых остатаков
			need_update = this.checkFarmacyRlsOstatListEmptiness();
		}

		if (need_update) {
			this.updateFarmacyRlsOstatListBySpoUlo('mute');
		}
	},*/
	/*checkFarmacyRlsOstatListOnlsFed: function(callback, load_mask) { //проверка наличия региональных остатков (при сохранении рецепта по ОНЛС) и при необходимости обновление списка остатков
		var wnd = this;
		var base_form = this.FormPanel.getForm();
		var df_combo = base_form.findField('DrugFinance_id');
		var wdcit_combo = base_form.findField('WhsDocumentCostItemType_id');
		var drug_combo = base_form.findField('Drug_rlsid');
		var farmacy_combo = base_form.findField('OrgFarmacy_id');
		var region_nick = getRegionNick();
		var need_update = false;

		if (typeof callback != 'function') {
			callback = Ext.emptyFn;
		}
		var returnToFormEditing = function(error_message) {
			wnd.formStatus = 'edit';

			if (load_mask) {
				load_mask.hide();
			}

			if (!Ext.isEmpty(error_message)) {
				sw.swMsg.alert('Ошибка', error_message);
			}
		};

		var wdcit_code = null;
		if (!Ext.isEmpty(wdcit_combo.getValue())) {
			wdcit_code = wdcit_combo.getFieldValue('WhsDocumentCostItemType_Code');
		}

		//запоминаем первоначальные значения полей
		var old_org_farmacy_id = farmacy_combo.getValue();
		var old_drug_finance_id = df_combo.getValue();
		var new_org_farmacy_id = null;
		var new_drug_finance_id = 27; //27 - Региональный бюджет

		var ofc_base_params = farmacy_combo.getStore().baseParams;
		var ofc_drugfinance_id = ofc_base_params.DrugFinance_id ? ofc_base_params.DrugFinance_id : null;

		var restoreOrgFarmacyComboState = function() {
			var is_restored = false;

			//пытаемся восстановить значение в поле Аптека, если оно было указано
			if (!Ext.isEmpty(old_org_farmacy_id)) {
				var index = farmacy_combo.getStore().findBy(function(record) {
					return record.get('OrgFarmacy_id') == old_org_farmacy_id;
				});
				if (index > -1) {
					farmacy_combo.setValue(old_org_farmacy_id);
					is_restored = true;
				}
			} else {
				is_restored = true;
			}
			farmacy_combo.getStore().baseParams.DrugFinance_id = ofc_drugfinance_id;

			return is_restored;

			if (restoreOrgFarmacyComboState()) {
				callback();
			} else {
				returnToFormEditing(langs('После обновления данных не удалось восстановить значение в поле аптека'));
			}
		}

		if (region_nick == 'msk' && !Ext.isEmpty(drug_combo.getValue()) && wdcit_code == 1 &&  old_drug_finance_id != new_drug_finance_id) { //1 - ОНЛС; если медикамент не указан, проверять остатки нет смысла, они в любом случае будут пустыми
			//проверяем наличие ненулевых остатаков
			need_update = this.checkFarmacyRlsOstatListEmptiness();
		}

		if (need_update) {
			//меняем финансирование на региональное
			df_combo.setValue(new_drug_finance_id);

			//установливаем новые параметры загрузки для комбобокса с остатками по аптекам
			farmacy_combo.getStore().baseParams.DrugFinance_id = new_drug_finance_id;

			//обновляем остатки через сервис
			this.updateFarmacyRlsOstatListBySpoUlo('mute', function(data) {
				if (data.success) {
					//если обновление прошло успешно, снова проверяем остатки
					var kolvo = base_form.findField('EvnRecept_Kolvo').getValue();
					var ostat_exists = false;

					farmacy_combo.getStore().each(function(record) {
						if (record.get('DrugOstatRegistry_Kolvo') >= kolvo) {
							new_org_farmacy_id = record.get('OrgFarmacy_id');
							ostat_exists = true;
							return false;
						}
					});

					//выбираем подходящую аптеку с остатками
					if (!Ext.isEmpty(new_org_farmacy_id)) {
						farmacy_combo.setValue(new_org_farmacy_id);
					}

					//восстанвливаем базовые параметры комбобокса с аптеками
					farmacy_combo.getStore().baseParams.DrugFinance_id = ofc_drugfinance_id;

					if (ostat_exists) { //если после смены финансирования остатки нашлись, предлагаем выписть рецепт с другим финансированием
						sw.swMsg.show({
							buttons: Ext.Msg.YESNO,
							fn: function(buttonId, text, obj) {
								if (buttonId == 'yes') {
									callback();
								} else {
									//восстанваливаем прежние значения полей
									df_combo.setValue(old_drug_finance_id);

									if (restoreOrgFarmacyComboState()) {
										callback();
									} else {
										returnToFormEditing(langs('После обновления данных не удалось восстановить значение в поле аптека'));
									}
								}
							}.createDelegate(this),
							icon: Ext.MessageBox.QUESTION,
							msg: 'Выписать рецепт за счет регионального бюджета?',
							title: 'Подтверждение'
						});
					} else {
						//восстанваливаем прежние значения полей
						df_combo.setValue(old_drug_finance_id);

						if (restoreOrgFarmacyComboState()) {
							callback();
						} else {
							returnToFormEditing(langs('После обновления данных не удалось восстановить значение в поле аптека'));
						}
					}
				} else {
					//восстанваливаем прежние значения полей
					df_combo.setValue(old_drug_finance_id);

					if (restoreOrgFarmacyComboState()) {
						if (data.event_name == 'update_function') { //ошибка на стадии подготовки данных для обновлнения, не хватает данных. В этом случае просто продолжаем выполнение сохранения
							callback();
						} else {
							returnToFormEditing(lsngs('При обновлении данных об остатках произошла ошибка'));
						}
					} else {
						returnToFormEditing(langs('После обновления данных не удалось восстановить значение в поле аптека'));
					}
				}
			});
		} else {
			callback();
		}
	},*/
    checkSelectedOstatByZero: function(callback, load_mask) { //проверка выбранного остатка по аптеке или складу на ноль
		var wnd = this;
		var region_nick = getRegionNick();
		var base_form = this.FormPanel.getForm();
		var recept_type_id = base_form.findField('ReceptType_id').getValue();
		var need_check = (region_nick != 'msk' && recept_type_id == 2 && (getGlobalOptions().recept_drug_ostat_control == '1' || getGlobalOptions().recept_drug_ostat_viewing == '1')); //в Москве проверка всегда отключена, в других регионах проверка включена только когда выписка производится "на листе" и в настройках включен контроль или просмотр остатков

		if (need_check) {
			base_form.findField('EvnRecept_IsNotOstat').setValue(null);

			var ostat_is_empty = true;
			var farmacy_combo = base_form.findField('OrgFarmacy_id');
			var storage_combo = base_form.findField('Storage_id');
			var farmacy_id = farmacy_combo.getValue();
			var storage_id = storage_combo.getValue();

			if (!Ext.isEmpty(farmacy_id)) { //проверяем выбрана ли аптека
				if (!Ext.isEmpty(storage_id)) { //проверяем выбран ли склад
					//проверяем остаток на выбранном складе
					storage_combo.getStore().each(function(record) {
						if (record.get('Storage_id') == storage_id) {
							if (record.get('Storage_Kolvo') > 0) {
								ostat_is_empty = false;
							}
							return false;
						}
					});
				} else {
					//проверяем остаток на выбранной аптеке
					farmacy_combo.getStore().each(function(record) {
						if (record.get('OrgFarmacy_id') == farmacy_id) {
							if (record.get('DrugOstatRegistry_Kolvo') > 0) {
								ostat_is_empty = false;
							}
							return false;
						}
					});
				}
			}

			if (ostat_is_empty) {
				sw.swMsg.show({
					buttons: Ext.Msg.YESNO,
					fn: function(buttonId) {
						if (buttonId == 'yes') {
							base_form.findField('EvnRecept_IsNotOstat').setValue(2); //2 - Да
							if (typeof callback == 'function') {
								callback();
							}
						} else {
							wnd.formStatus = 'edit';

							if (load_mask) {
								load_mask.hide();
							}
						}
					},
					icon: Ext.MessageBox.QUESTION,
					msg: langs('Рецепт попадет на отсроченное обслуживание. Продолжить сохранение рецепта?'),
					title: langs('Подтверждение')
				});
			} else if (typeof callback == 'function') {
				callback();
			}
		} else if (typeof callback == 'function') {
			callback();
		}
	},
    checkEvnVisitPLDiag: function(callback, load_mask) { //проверка соответствия диагноза в рецепте, диагнозу в родительском посещении
		if (typeof callback == 'function') {
            callback();
		}
	},
	checkLastPersonPrivilegeModeration: function(callback, load_mask) { //набор проверок данных о последней модерации льготы
		var wnd = this;
        var base_form = this.FormPanel.getForm();
        var current_date = (new Date());
        var recept_date = base_form.findField('EvnRecept_setDate').getValue(); //дата не должна быть птсой, так как поле обязательно для заполнения
		var privilege_type_id = base_form.findField('PrivilegeType_id').getValue();
		var person_id = base_form.findField('Person_id').getValue();

        current_date.setHours(0, 0, 0, 0);
        recept_date.setHours(0, 0, 0, 0);

        if (typeof callback != 'function') {
            callback = Ext.emptyFn;
        }

        Ext.Ajax.request({
            params: {
            	Person_id: person_id,
                PrivilegeType_id: privilege_type_id,
                EvnRecept_setDate: recept_date.format('d.m.Y')
        	},
        	url: '/?c=EvnRecept&m=getLastPersonPrivilegeModerationData',
            callback: function(options, success, response) {
                wnd.formStatus = 'edit';
                if (load_mask) {
                    load_mask.hide();
                }
                if (success) {
                    var response_obj = Ext.util.JSON.decode(response.responseText);
                    if (Ext.isEmpty(response_obj.Error_Msg)) {
						var attention_msg = ''; //сообщение для врача
						var create_req = false; //необходимость создания запроса по текущей льготе

                        //конвертация дат
						var priv_date = !Ext.isEmpty(response_obj.PersonPrivilege_begDate) ? Date.parseDate(response_obj.PersonPrivilege_begDate, 'd.m.Y') : null;
						var last_req_date = !Ext.isEmpty(response_obj.LastRequest_Date) ? Date.parseDate(response_obj.LastRequest_Date, 'd.m.Y') : null;
						var last_mod_date = !Ext.isEmpty(response_obj.LastAcceptedRequest_Date) ? Date.parseDate(response_obj.LastAcceptedRequest_Date, 'd.m.Y') : null;
						var next_mod_date = !Ext.isEmpty(response_obj.NextModeration_Date) ? Date.parseDate(response_obj.NextModeration_Date, 'd.m.Y') : null;
						var att_date = !Ext.isEmpty(response_obj.NextAttention_Date) ? Date.parseDate(response_obj.NextAttention_Date, 'd.m.Y') : null;

                        //формирование списка документов в зависимости от возраста
						var age = swGetPersonAge(wnd.PersonInfo.getFieldValue('Person_Birthday'), new Date());
                        var doc_str = '';
                        /*var doc_arr = ['паспорт родителя (законного представителя)', 'свидетельство о рождении', 'СНИЛС'];
                        if (age >= 18) {
                            doc_arr = ['паспорт', 'СНИЛС'];
                        } else if (age >= 14) {
                            doc_arr = ['паспорт или свидетельство о рождении', 'паспорт родителя (законного представителя)', 'СНИЛС'];
                        }
                        doc_arr.push('документы подтверждающие право на льготу');*/
						var doc_arr = new Array();
						if (age < 14) {
							doc_arr.push('подтверждение регистрации (форма 8)');
						} else {
							doc_arr.push('паспорт');
						}
						doc_arr.push('СНИЛС');
						doc_arr.push('документы подтверждающие право на льготу');
						doc_arr.push('согласие на обработку перс. данных');
                        for (var i = 0; i < doc_arr.length; i++) {
                            doc_str += '<br/> - '+doc_arr[i];
                        }

                        //если необходимо, формируем сообщение для врача
                        if (att_date && next_mod_date && recept_date >= att_date && recept_date <= next_mod_date) { //если дата выписки рецепта входит в период между датами, соответствующими сроку завершения периода постмодерации и сроку выдачи предупреждения
                             attention_msg = 'Если следующее посещение пациента планируется  около '+response_obj.NextModeration_Date+', то предупредите пациента о необходимости принести пакет документов: '+doc_str;
						} else if (priv_date && priv_date < current_date && Ext.isEmpty(response_obj.LastRequest_Status) && response_obj.BeforeCreatedRecept_Cnt == 0) { //если у пациента дата начала льготы меньше текущей даты и нет запроса на постмодерацию по льготе и нет рецептов, дата выписки которых меньше текущей даты
                             attention_msg = 'Предупредите пациента о необходимости принести на следующий прием пакет документов: '+doc_str;
						}

                        //нужно создать запрос на модерацию если:
                        if (
							(priv_date == current_date && Ext.isEmpty(response_obj.LastRequest_Status)) || //дата начала льготы, указанной в рецепте, равна текущей дате и нет запроса на постмодерацию по этой льготе
							(priv_date < current_date && Ext.isEmpty(response_obj.LastRequest_Status) && response_obj.BeforeCreatedRecept_Cnt > 0) || //дата начала льготы, указанной в рецепте, меньше текущей даты и нет запроса на посмодерацию по этой льготе, и у пациента есть рецепты по этой льготе, у которых дата выписки меньше текущей даты
							(next_mod_date && (current_date >= next_mod_date || response_obj.MonthsBeforeNextModeration < 1) && last_req_date == last_mod_date) //с момента последней постмодерации с одобренным результатом прошло N и более месяцев или до даты окончания периода посмодерации осталось менее 1 месяца и нет другого запроса на постмодерацию на эту же льготу, дата создания которого больше даты последнего положительного запроса на постмодерацию
						) {
                            create_req = true;
						}

						//готовим параметры для добавления запроса
                        var params = new Object();
                        if (create_req) {
                            params.action = 'add';
                            params.Person_id = person_id;
                            params.PrivilegeType_id = privilege_type_id;
                            params.userMedStaffFact = wnd.userMedStaffFact;
                            params.onSave = function (data) {
                                if (!Ext.isEmpty(data.PersonPrivilegeReq_id) && !Ext.isEmpty(data.PrivilegeType_id)) {
                                    callback();
                                } else {
                                    sw.swMsg.alert('Ошибка', 'При сохранении данных запроса произошла ошибка');
                                }
                            };
                        }

						//если есть сообщение для врача, показываем его
						if (!Ext.isEmpty(attention_msg)) {
                            sw.swMsg.alert('Сообщение', attention_msg, function() {
                                if (create_req) { //если установлен сллтветствующий флаг, открываем форму для добавления запроса
                                    getWnd('swPersonPrivilegeReqEditWindow').show(params);
                                } else { //в противном случае сразу возвращаемся к выполнению предыдущих действий
                                    callback();
                                }
							});
						} else {
                            if (create_req) { //если установлен сллтветствующий флаг, открываем форму для добавления запроса
                                getWnd('swPersonPrivilegeReqEditWindow').show(params);
                            } else { //в противном случае сразу возвращаемся к выполнению предыдущих действий
                                callback();
                            }
						}
					} else {
                        sw.swMsg.alert('Ошибка', response_obj.Error_Msg);
					}
                } else {
                    sw.swMsg.alert('Ошибка', 'При проверке данных о модерации льготы произошла ошибка');
                }
            }
        });
	},
	checkReceptKardio: function(check_name, callback, load_mask) { //универсальная функция для проверки рейцептов выписанных по программе ЛЛО Кардио
		var wnd = this;
        var base_form = this.FormPanel.getForm();
        var params = new Object();
        var url = '/?c=EvnRecept&m='+check_name;
        var default_error_msg = langs('При проверке рецепта произошла ошибка');

		if (typeof callback != 'function') {
			callback = Ext.emptyFn;
		}

		switch (check_name) { //настройка уникальных параметров проверки
			case 'checkReceptKardioReissue': //проверка на повторную выписку рецепта ЛКО Кардио
				params.Person_id = base_form.findField('Person_id').getValue();
				params.Drug_id = base_form.findField('Drug_rlsid').getValue();
				params.DrugComplexMnn_id = base_form.findField('DrugComplexMnn_id').getValue();

				default_error_msg = langs('При проверке рецепта на повторную выписку произошла ошибка');
				break;
			case 'checkReceptKardioTicagrelor': //проверка на выписку ЛП Тикагрелор в стационаре и поликлинике
				var recept_date = base_form.findField('EvnRecept_setDate').getValue();

				params.Drug_id = base_form.findField('Drug_rlsid').getValue();
				params.DrugComplexMnn_id = base_form.findField('DrugComplexMnn_id').getValue();
				params.Lpu_id = base_form.findField('Lpu_id').getValue();
				params.EvnRecept_setDate = !Ext.isEmpty(recept_date) ? Ext.util.Format.date(recept_date, 'd.m.Y') : null;

				default_error_msg = langs('При проверке рецепта на выписку ЛП Тикагрелор произошла ошибка');
				break;
			case 'checkReceptKardioSetDate': //проверка даты выписки рецепта
				var recept_date = base_form.findField('EvnRecept_setDate').getValue();

				params.Person_id = base_form.findField('Person_id').getValue();
				params.EvnRecept_pid = base_form.findField('EvnRecept_pid').getValue();
				params.EvnRecept_setDate = !Ext.isEmpty(recept_date) ? Ext.util.Format.date(recept_date, 'd.m.Y') : null;

				default_error_msg = langs('При проверке даты рецепта произошла ошибка');
				break;
			default:
				sw.swMsg.alert(langs('Ошибка'), langs('Не определен тип проверки'));
				break;
		}

        Ext.Ajax.request({
            params: params,
        	url: url,
            callback: function(options, success, response) {
                if (success) {
                    var response_obj = Ext.util.JSON.decode(response.responseText);
                    if (response_obj.check_result) {
						callback();
					} else if (!Ext.isEmpty(response_obj.Question_Msg)) {
						sw.swMsg.show({
							buttons: Ext.Msg.YESNO,
							fn: function ( buttonId ) {
								if (buttonId == 'yes') {
									callback();
								} else {
									wnd.formStatus = 'edit';
									if (load_mask) {
										load_mask.hide();
									}
								}
							}.createDelegate(this),
							msg: response_obj.Question_Msg,
							title: langs('Проверка рецепта')
						});
					} else {
						wnd.formStatus = 'edit';
						if (load_mask) {
							load_mask.hide();
						}
                        sw.swMsg.alert(langs('Ошибка'), !Ext.isEmpty(response_obj.Error_Msg) ? response_obj.Error_Msg : default_error_msg);
					}
                } else {
					wnd.formStatus = 'edit';
					if (load_mask) {
						load_mask.hide();
					}
                    sw.swMsg.alert(langs('Ошибка'), default_error_msg);
                }
            }
        });
	},
	loadStorageCombo: function() {
        var base_form = this.FormPanel.getForm();
        var farmacy_combo = base_form.findField('OrgFarmacy_id');
        var storage_combo = base_form.findField('Storage_id');
		var storage_array = new Array();
		var farmacy_id = farmacy_combo.getValue();

		if (farmacy_id > 0) {
            var idx = farmacy_combo.getStore().findBy(function(rec) {
            	return rec.get('OrgFarmacy_id') == farmacy_id;
            });
            if (idx >= 0) {
                var record = farmacy_combo.getStore().getAt(idx);
                if (!Ext.isEmpty(record.get('Storage_id'))) {
                	var id_arr = record.get('Storage_id').split('<br/>');
                	var name_arr = record.get('Storage_Name').split('<br/>');
                	var kolvo_arr = record.get('Storage_Kolvo').split('<br/>');
                	for (var i = 0; i < id_arr.length; i++) {
                        storage_array.push({
							Storage_id: id_arr[i],
							Storage_Name: i < name_arr.length ? name_arr[i] : '',
                            Storage_Kolvo: i < kolvo_arr.length ? kolvo_arr[i]*1 : 0
						});
					}
				}
            }
		}

        var store = storage_combo.getStore();
        storage_combo.fullReset();

		if (storage_array.length > 0) {
            var record = new Ext.data.Record.create(store);

            for (var i = 0; i < storage_array.length; i++) {
                store.insert(i, new record({
                    Storage_id: storage_array[i].Storage_id,
                    Storage_Name: storage_array[i].Storage_Name,
                    Storage_Kolvo: storage_array[i].Storage_Kolvo
                }));
			}
            storage_combo.setAllowBlank(false);
            storage_combo.enable();

			//По умолчанию в поле устанавливаем первый склад, на котором есть остатки медикаментов
			if (storage_combo.getStore().data.length > 0){
				idx = storage_combo.getStore().findBy(function(rec){
					return (rec.get('Storage_Kolvo') > 0);
				})
			}
			record = storage_combo.getStore().getAt(idx);
			if (record){
			Storage_id = record.get('Storage_id');
			storage_combo.setValue(Storage_id);
			} else {
				storage_combo.setAllowBlank(true);
				storage_combo.disable();
			}
		}

        this.setMaxKolvo();
        this.setPrice();
	},
	loadOrgFarmacyComboByDrugData: function(options) {
		var wnd = this;
		var region_nick = getRegionNick();
		var base_form = this.FormPanel.getForm();
		var org_farmacy_combo = base_form.findField('OrgFarmacy_id');
		var drug_combo = base_form.findField('Drug_rlsid');
		var dcm_combo = base_form.findField('DrugComplexMnn_id');
		var params = new Object();

		//определяется требуется ли загрузка конкретной аптеки
		var set_by_id = false;

		if (options && !Ext.isEmpty(options.OrgFarmacy_id)) {
 			params.OrgFarmacy_id = options.OrgFarmacy_id;
			set_by_id = true;
		}

		//определяем медикамент
		var drug_is_defined = false;

		//определяем разрешена ли загрузка списка аптек по комплексному МНН
		var load_by_dcm_enabled = ((region_nick == 'msk') || (region_nick == 'perm' && wnd.isKardio));

		if (options && !Ext.isEmpty(options.Drug_id)) {
			params.Drug_rlsid = options.Drug_id;
			drug_is_defined = true;
		}
		if (!drug_is_defined && load_by_dcm_enabled && options && !Ext.isEmpty(options.DrugComplexMnn_id)) {
			params.DrugComplexMnn_id = options.DrugComplexMnn_id;
			drug_is_defined = true;
		}
		if (!drug_is_defined && drug_combo.getValue()) {
			params.Drug_rlsid = drug_combo.getValue();
			drug_is_defined = true;
		}
		if (!drug_is_defined && load_by_dcm_enabled && dcm_combo.getValue()) {
			params.DrugComplexMnn_id = dcm_combo.getValue();
			drug_is_defined = true;
		}

		org_farmacy_combo.clearValue();
		org_farmacy_combo.getStore().removeAll();

		if (drug_is_defined || set_by_id) {
			org_farmacy_combo.getStore().load({
				params: params,
				callback: function() {
					var idx = -1;

					if (set_by_id) {
						idx = base_form.findField('OrgFarmacy_id').getStore().findBy(function(rec) {
							return (rec.get('OrgFarmacy_id') == params.OrgFarmacy_id);
						});

						if (idx >= 0) {
							base_form.findField('OrgFarmacy_id').setValue(params.OrgFarmacy_id);
						}

						if (region_nick == 'penza') {
							wnd.loadStorageCombo();
							if (wnd.action == 'view') {
								var storage_combo = base_form.findField('Storage_id');
								storage_combo.disable();
							}
						}
					} else {
						//определение и установка значения по умолчанию
						if (org_farmacy_combo.getStore().getCount() == 1) {
							idx = 0;
						} else {
							var default_id = getGlobalOptions().OrgFarmacy_id;
							if (wnd.action == 'add' && !Ext.isEmpty(default_id)) {
								idx = org_farmacy_combo.getStore().findBy(function(record) {
									return (record.get('OrgFarmacy_id') == default_id);
								});
							}
						}

						if (idx >= 0) {
							org_farmacy_combo.fireEvent('change', org_farmacy_combo, org_farmacy_combo.getStore().getAt(idx).get('OrgFarmacy_id'));
						}
					}

					//для Москвы: если в списке загруженных аптек нет остатков, то пробуем обновить остатки через сревис и перезагрузить
					/*if (region_nick == 'msk') {
						wnd.checkFarmacyRlsOstatListUpdateNecessarity();
					}*/

					if (options && typeof options.callback == 'function') {
						options.callback();
					}
				}
			});
		}
	},
	showPrintButton: function(show) {
        var base_form = this.FormPanel.getForm();
        var region_nick = getRegionNick();
        var set_date = Ext.util.Format.date(base_form.findField('EvnRecept_setDate').getValue(), 'Y-m-d');
        var new_date = new Date().format('Y-m-d');

		this.buttons[5].hide();
        if (region_nick == 'msk' && base_form.findField('EvnRecept_IsPrinted').getValue() == 2) { //если рецепт уже напечатан то кнопка скрывается
			show = false;
			this.buttons[5].show() //открываем кнопку для перепечати
		}

        if (show) {
        	this.buttons[2].show();
		} else {
            this.buttons[2].hide();
		}

		if (set_date != new_date) {
			this.buttons[5].hide();
		}
	},
	loadPersonRegisterStore: function () {
		var me = this;
		var base_form = this.FormPanel.getForm();
		// Загружаем записи из регистра заболевания для выбранного пациента
		me.personRegisterStore.load({
			callback: function(records, options, success) {
				base_form.findField('EvnRecept_setDate').fireEvent('change', base_form.findField('EvnRecept_setDate'), base_form.findField('EvnRecept_setDate').getValue());
				base_form.findField('ReceptType_id').fireEvent('change', base_form.findField('ReceptType_id'), base_form.findField('ReceptType_id').getValue());
				me.refreshFieldsVisibility();

				base_form.clearInvalid();

				if ( !base_form.findField('ReceptType_id').disabled ) {
					base_form.findField('ReceptType_id').focus(true, 250);
				}
				else {
					base_form.findField('EvnRecept_setDate').focus(true, 250);
				}
			},
			params: {
				Person_id: base_form.findField('Person_id').getValue()
			}
		});
	},
	setReceptType: function () {
		var wnd = this;
		var base_form = this.FormPanel.getForm();

		this.getReceptElectronicAllow(function (allow_data) {
			var receptForm = base_form.findField('ReceptForm_id').getValue();

			//если возможно выписывать с типом "Электронный документ" и форма рецепта не МИ-1
			if (getRegionNick() !== 'kz' && receptForm != 2 && allow_data.recept_electronic_allow) {
				base_form.findField('ReceptType_id').enable();
				var index = base_form.findField('ReceptType_id').getStore().findBy(function (rec) {
					return (rec.get('ReceptType_Code') == 3);
				});
				if (index >= 0) {
					base_form.findField('ReceptType_id').setValue(base_form.findField('ReceptType_id').getStore().getAt(index).get('ReceptType_id'));
				}
			}
			// Если поле "Тип рецепта" пустое...
			else if (Ext.isEmpty(base_form.findField('ReceptType_id').getValue())) {
				// ... то устанавливаем значение по-умолчанию "Тип рецепта" = "На листе"
				base_form.findField('ReceptType_id').enable();
				index = base_form.findField('ReceptType_id').getStore().findBy(function (rec) {
					return (rec.get('ReceptType_Code') == 2);
				});
				if (index >= 0) {
					base_form.findField('ReceptType_id').setValue(base_form.findField('ReceptType_id').getStore().getAt(index).get('ReceptType_id'));
				}
			} else {
				base_form.findField('ReceptType_id').disable();
			}
			wnd.loadPersonRegisterStore();
			wnd.setKardioMode('auto');
			wnd.setVKProtocolFieldsVisible();
		});
	},
	show: function() {
		sw.Promed.swEvnReceptRlsEditWindow.superclass.show.apply(this, arguments);

		var win = this;
		var region_nick = getRegionNick();

		if ( !arguments[0] ) {
			sw.swMsg.alert('Ошибка', 'Отсутствуют необходимые параметры', function() { this.hide(); }.createDelegate(this) );
			return false;
		}

		this.action = null;
		this.ARMType = '';
		this.callback = Ext.emptyFn;
		this.formStatus = 'edit';
		this.onHide = Ext.emptyFn;
		this.streamInput = false;
		this.viewOnly = false;
		this.isKardio = !!(arguments[0].isKardio); //флаг свидетельствующий о том, что форма работает в режиме "ДЛО Кардио"
		this.recept_electronic_is_agree = null; //согласие пациента на выписку рецепта в форме электронного документа

		this.restore();
		this.center();
		this.maximize();

        this.showPrintButton(false); // Напечатать
		//this.buttons[3].hide(); // Подписать

		this.findById('ERREF_DrugPanel').expand();
		this.findById('ERREF_PrivilegePanel').expand();
		this.findById('ERREF_ReceptPanel').expand();

		// Очищаем список записей из регистра заболеваний
		this.personRegisterStore.clearFilter();
		this.personRegisterStore.removeAll();

		// Очищаем список параметров для определения номера рецепта
		this.receptNumberParams = {
			 EvnRecept_setDate: null
			,WhsDocumentCostItemType_id: null
		};

		if ( !Ext.isEmpty(arguments[0].action) ) {
			this.action = arguments[0].action;
		}

		if ( typeof arguments[0].callback == 'function' ) {
			this.callback = arguments[0].callback;
		}

		if ( typeof arguments[0].onHide == 'function' ) {
			this.onHide = arguments[0].onHide;
		}

		if ( arguments[0].streamInput ) {
			this.streamInput = arguments[0].streamInput;
		}

		if ( arguments[0].viewOnly ) {
			this.viewOnly = arguments[0].viewOnly;
		}

		if ( arguments[0].userMedStaffFact ) {
			this.userMedStaffFact = arguments[0].userMedStaffFact;
		} else {
            this.userMedStaffFact = (this.streamInput == true || Ext.isEmpty(sw.Promed.MedStaffFactByUser.current) || sw.Promed.MedStaffFactByUser.current.ARMType == 'mstat' ? new Object() : sw.Promed.MedStaffFactByUser.current);
		}

		if ( !Ext.isEmpty(arguments[0].ARMType)  ) {
			this.ARMType = arguments[0].ARMType;
		} else {
			this.ARMType = this.userMedStaffFact.ARMType || '';
		}

		var isSaratov = (getRegionNick() == 'saratov');
		if (isSaratov && this.ARMType == 'dpoint') {
			this.buttons[1].hide();
		}

		var base_form = this.FormPanel.getForm();
		base_form.reset();
		base_form.setValues(arguments[0]);

        if (getRegionNick() == 'msk') {
            base_form.findField('EvnRecept_Signa').maxLength = 35;
        }
		base_form.findField('EvnRecept_setDate').enable_blocked = (region_nick == 'msk');

		base_form.findField('Drug_rlsid').clearBaseParams();
		base_form.findField('Drug_rlsid').getStore().removeAll();
		base_form.findField('Drug_rlsid').lastQuery = '';

		base_form.findField('DrugComplexMnn_id').clearBaseParams();
		base_form.findField('DrugComplexMnn_id').getStore().removeAll();
		base_form.findField('DrugComplexMnn_id').lastQuery = '';

		base_form.findField('Drug_rlsid').getStore().baseParams.Drug_rlsid = null;
		base_form.findField('Drug_rlsid').getStore().baseParams.DrugComplexMnn_id = null;
		base_form.findField('Drug_rlsid').getStore().baseParams.Date = null;
		base_form.findField('Drug_rlsid').getStore().baseParams.ReceptType_Code = null;
		base_form.findField('Drug_rlsid').getStore().baseParams.DrugFinance_id = null;
		base_form.findField('Drug_rlsid').getStore().baseParams.WhsDocumentCostItemType_id = null;
		base_form.findField('Drug_rlsid').getStore().baseParams.LpuSection_id = null;
		base_form.findField('DrugComplexMnn_id').getStore().baseParams.Date = null;
		base_form.findField('DrugComplexMnn_id').getStore().baseParams.ReceptType_Code = null;
		base_form.findField('DrugComplexMnn_id').getStore().baseParams.DrugFinance_id = null;
		base_form.findField('DrugComplexMnn_id').getStore().baseParams.WhsDocumentCostItemType_id = null;
		base_form.findField('DrugComplexMnn_id').getStore().baseParams.LpuSection_id = null;
		base_form.findField('DrugComplexMnn_id').getStore().baseParams.PersonRegisterType_id = null;
		base_form.findField('DrugComplexMnn_id').getStore().baseParams.Person_id = null;
		base_form.findField('DrugComplexMnn_id').getStore().baseParams.Diag_id = null;
		base_form.findField('DrugComplexMnn_id').getStore().baseParams.EvnRecept_IsKEK = null;
		base_form.findField('DrugComplexMnn_id').getStore().baseParams.EvnRecept_IsMnn = null;

		base_form.findField('EvnRecept_IsKEK').enable_blocked = false;

        this.ambulat_card_combo.fullReset();
        this.ambulat_card_combo.getStore().baseParams.Lpu_id = !Ext.isEmpty(getGlobalOptions().lpu_id) ? getGlobalOptions().lpu_id : null;
        this.ambulat_card_combo.getStore().baseParams.Person_id = base_form.findField('Person_id').getValue();

        if (region_nick == 'msk') {
            this.ambulat_card_combo.showContainer();
            this.ambulat_card_combo.setAllowBlank(false);
		} else {
            this.ambulat_card_combo.hideContainer();
            this.ambulat_card_combo.setAllowBlank(true);
		}

        base_form.findField('Storage_id').fullReset();
        if (getRegionNick() == 'penza') {
            base_form.findField('Storage_id').showContainer();
		} else {
            base_form.findField('Storage_id').hideContainer();
		}

        if(arguments[0].MedPersonal_id)
            base_form.findField('MedStaffFact_id').setValue(arguments[0].MedPersonal_id);

		this.SelectDrugFromList = getGlobalOptions().select_drug_from_list;

		var DrugOstatViewing = (getGlobalOptions().recept_drug_ostat_viewing == '1'),
			DrugOstatControl = (getGlobalOptions().recept_drug_ostat_control == '1'),
			EmptyDrugOstatAllow = (getGlobalOptions().recept_empty_drug_ostat_allow == '1');

		this.OstatType = 0;
		if (DrugOstatViewing) {
			this.OstatType = 1;
		}
		if (DrugOstatControl) {
			this.OstatType = 2;
		}
		if (EmptyDrugOstatAllow) {
			this.OstatType = 3;
		}

		if (this.OstatType > 0) {
			//Отображать остатки
			base_form.findField('Drug_rlsid').setContainerVisible(true);
			base_form.findField('Drug_rlsid').setAllowBlank(this.OstatType == 1);

			base_form.findField('Drug_Price').setContainerVisible(region_nick != 'msk');
		} else {var condition = (this.SelectDrugFromList.inlist(['allocation','request_and_allocation']));

			base_form.findField('Drug_rlsid').setContainerVisible(condition);
			base_form.findField('Drug_rlsid').setAllowBlank(!condition);

			base_form.findField('Drug_Price').setContainerVisible((condition || getRegionNick() == 'kz') && region_nick != 'msk');
		}

		if (this.SelectDrugFromList == 'jnvlp' && !DrugOstatViewing && !DrugOstatControl) { //если выписка идет из ЖНВЛП и без просмотра/контроля остатков, то поле не видимо
			base_form.findField('OrgFarmacy_id').setContainerVisible(false);
			base_form.findField('OrgFarmacy_id').setAllowBlank(true);
			this.findById('ERREF_BtnOstatUpd').hide();
		} else {
			base_form.findField('OrgFarmacy_id').setContainerVisible(true);
			base_form.findField('OrgFarmacy_id').setAllowBlank(this.isKardio && region_nick != 'msk');
			if (region_nick == 'msk') {
				this.findById('ERREF_BtnOstatUpd').show();
			} else {
				this.findById('ERREF_BtnOstatUpd').hide();
			}
		}

		base_form.findField('EvnRecept_IsMnn').setAllowBlank(region_nick != 'msk');

		//base_form.findField('OrgFarmacy_id').clearBaseParams();
		//base_form.findField('OrgFarmacy_id').lastQuery = '';
        base_form.findField('OrgFarmacy_id').getStore().baseParams.isKardio = (this.isKardio ? 1 : 0);

		/*base_form.findField('ReceptValid_id').getStore().filterBy(function(rec) {
			return (rec.get('ReceptValid_Code').toString().inlist([ '1', '2', '4', '7' ]));
		});*/

		if ( !Ext.isEmpty(arguments[0].EvnUslugaTelemed) ) {
			base_form.findField('EvnUslugaTelemed').setValue(arguments[0].EvnUslugaTelemed);
		}

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT });
		loadMask.show();

		var index;
		var record;
		var ismnn_combo = base_form.findField('EvnRecept_IsMnn');

		base_form.findField('ReceptUrgency_id').disable();
		base_form.findField('ReceptUrgency_id').hideContainer();

		this.setReceptFormFilter();
		this.setKardioMode('unset');
		this.setDrugFieldVisible();
		this.setEvnReceptIsKEKDefaultValue('show_form');
		this.setEvnReceptIsMnnDefaultValue('show_form');

		base_form.findField('PrescrSpecCause_id').hideContainer();
		base_form.findField('PrescrSpecCause_id').allowBlank = true;
		base_form.findField('PrescrSpecCause_id').disable();
		base_form.findField('PrescrSpecCause_cb').hideContainer();

		//Ext.getCmp('ERREF_DrugWrong').hide();
		Ext.getCmp('EvnRecept_KolvoWarning').hide();
		base_form.findField('EvnRecept_MaxKurs').hideContainer();

		base_form.findField('EvnRecept_IsExcessDose').hideContainer();

		switch ( this.action ) {
            case 'add':
				Ext.getCmp('EREF_DrugResult').hide();
				Ext.getCmp('EREF_DrugResult').disable();

                this.setEvnCourseTreatFieldsVisible();
				this.enableEdit(true);
				this.setTitleByAction();

				base_form.findField('Lpu_id').setValue(getGlobalOptions().lpu_id);

				//значение по умолчанию для поля "Протокол ВК"
				var vk_combo = base_form.findField('EvnRecept_IsKEK');
				if (Ext.isEmpty(vk_combo.getValue())) {
					vk_combo.setValue(1); //1 - Нет
					vk_combo.setLinkedFieldValues();
				}

				//this.buttons[3].show(); // Подписать
				this.PersonInfo.load({
					Person_id: base_form.findField('Person_id').getValue(),
					Server_id: base_form.findField('Server_id').getValue(),
					callback: function() {
						//clearDateAfterPersonDeath('personpanelid', 'ERREF_PersonInformationFrame', base_form.findField('EvnRecept_setDate'));

						var person_information = win.PersonInfo;

						// @task https://redmine.swan.perm.ru//issues/122143
						if ( !Ext.isEmpty(base_form.findField('ReceptType_id').getValue()) && base_form.findField('ReceptType_id').getValue() == 2 && !Ext.isEmpty(person_information.getFieldValue('Person_deadDT')) ) {
							sw.swMsg.alert(langs('Ошибка'), langs('У пациента указана дата смерти, выписка рецепта с типом «на листе» невозможна.'), function() {
								win.hide();
							});
							return false;
						}

						var set_date_value = base_form.findField('EvnRecept_setDate').getValue();
						var new_date = new Date().format('Y-m-d');
						if (set_date_value instanceof Date) {
							new_date = set_date_value.format('Y-m-d');
						}
						var person_age = swGetPersonAge(person_information.getFieldValue('Person_Birthday'), new_date);
						var sex_code = person_information.getFieldValue('Sex_Code');
						var is_retired = ((sex_code == 2 && person_age >= 55) || (sex_code == 1 && person_age >= 60)); //опредлеяем, пенсионер ли наш пациент
						var ReceptForm_id = Ext.isEmpty(base_form.findField('ReceptForm_id').getValue())?base_form.findField('ReceptForm_id').getValue():0;
						if(ReceptForm_id == 2) {
							base_form.findField('ReceptValid_id').getStore().filterBy(function (rec) {
								return (rec.get('ReceptValid_Code').toString().inlist(new_date >= '2016-01-01' ? ['4', '9', '10', '11'] : ['1', '2']));
							});
						} else {
							base_form.findField('ReceptValid_id').getStore().filterBy(function (rec) {
								return (rec.get('ReceptValid_Code').toString().inlist(new_date >= '2016-01-01' ? ['4', '9', '10', '11'] : ['1', '2', '4', '7']));
							});
						}

						// Устанавливаем значение по-умолчанию
						if (getRegionNick() == 'kz') {
							win.refreshFieldsVisibility();
						} else {
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
				// Устанавливаем значение по-умолчанию "Срок действия" = "Три месяца"
				index = base_form.findField('ReceptValid_id').getStore().findBy(function(rec) {
					if (getRegionNick() == 'kz') {
						return (rec.get('ReceptValid_Code') == 9);
					}
					return (rec.get('ReceptValid_Code') == 2);
				});

				if ( index >= 0 ) {
					base_form.findField('ReceptValid_id').setValue(base_form.findField('ReceptValid_id').getStore().getAt(index).get('ReceptValid_id'));
				}

				if ( Ext.isEmpty(base_form.findField('EvnRecept_setDate').getValue()) ) {
					base_form.findField('EvnRecept_setDate').setValue(new Date());
				}

				base_form.findField('DrugComplexMnn_id').getStore().baseParams.Person_id = base_form.findField('Person_id').getValue();
				base_form.findField('Drug_rlsid').getStore().baseParams.Person_id = base_form.findField('Person_id').getValue();

				if (getRegionNick() == 'kz') {
					base_form.findField('ReceptType_id').setValue(1);
				}
				this.setReceptType();
				this.setReceptTypeFilter();

				// Подставляем диагноз, если в параметрах формы был передан Diag_id
				if ( !Ext.isEmpty(base_form.findField('Diag_id').getValue()) ) {
					base_form.findField('Diag_id').getStore().load({
						callback: function() {
							base_form.findField('Diag_id').getStore().each(function(record) {
								if ( record.get('Diag_id') == base_form.findField('Diag_id').getValue() ) {
									base_form.findField('Diag_id').fireEvent('select', base_form.findField('Diag_id'), record, 0);
								}
							});
						},
						params: { where: "where Diag_id = " + base_form.findField('Diag_id').getValue() }
					});
				}

				if (base_form.findField('ReceptForm_id').getValue() == 9){
					base_form.findField('PrescrSpecCause_cb').showContainer();
				}

				loadMask.hide();

			break;

			case 'edit':
			case 'view':

				if ( Ext.isEmpty(base_form.findField('EvnRecept_id').getValue()) ) {
					loadMask.hide();
					sw.swMsg.alert('Ошибка', 'Ошибка при загрузке данных формы', function() { this.hide(); }.createDelegate(this) );
					return false;
				}

				base_form.load({
					failure: function() {
						loadMask.hide();
						sw.swMsg.alert('Ошибка', 'Ошибка при загрузке данных формы', function() { this.hide(); }.createDelegate(this) );
					}.createDelegate(this),
					params: {
						EvnRecept_id: base_form.findField('EvnRecept_id').getValue(),
						archiveRecord: win.archiveRecord
					},
					success: function(frm, action) {
						//if ( base_form.findField('accessType').getValue() == 'view' || base_form.findField('EvnRecept_IsSigned').getValue() == '2' ) {
							this.action = 'view';
							var that = this;
						//}
						if (getRegionNick() != 'kz') {
							var response_obj = Ext.util.JSON.decode(action.response.responseText);
							if (!Ext.isEmpty(response_obj[0].ReceptUrgency_id)) {
								base_form.findField('ReceptUrgency_id').showContainer();
							}
						}

						var response_obj = Ext.util.JSON.decode(action.response.responseText);
						if (!Ext.isEmpty (response_obj[0].PrescrSpecCause_id)) {
							base_form.findField('PrescrSpecCause_cb').showContainer();
							base_form.findField('PrescrSpecCause_cb').setValue(1);
							base_form.findField('PrescrSpecCause_cb').disable();
							base_form.findField('PrescrSpecCause_id').showContainer();
							base_form.findField('PrescrSpecCause_id').allowBlank = false;
							base_form.findField('PrescrSpecCause_id').disable();
						}

						if (getRegionNick() != 'kz') {
							if (!Ext.isEmpty(response_obj[0].ReceptUrgency_id)) {
								base_form.findField('ReceptUrgency_id').showContainer();
							}
						}
						if (!Ext.isEmpty (response_obj[0].EvnRecept_IsExcessDose) && response_obj[0].EvnRecept_IsExcessDose == 2) {
							base_form.findField('EvnRecept_IsExcessDose').setValue(1);
							base_form.findField('EvnRecept_IsExcessDose').disable();
							base_form.findField('EvnRecept_IsExcessDose').showContainer();
						}

						if ( this.action == 'view' ) {
							this.setTitleByAction();
							this.enableEdit(false);
						}
						else {
							this.setTitleByAction();
							this.enableEdit(true);
						}
						this.reCountPrescrDose();

						this.PersonInfo.load({
							Person_id: base_form.findField('Person_id').getValue(),
							Server_id: base_form.findField('Server_id').getValue(),
							callback: function(arr, params, c) {
								//clearDateAfterPersonDeath('personpanelid', 'ERREF_PersonInformationFrame', base_form.findField('EvnRecept_setDate'));

								var new_date = base_form.findField('EvnRecept_setDate').getValue().format('Y-m-d');
								/*var person_information = win.PersonInfo;
								var person_age = swGetPersonAge(person_information.getFieldValue('Person_Birthday'), new_date);
								var sex_code = person_information.getFieldValue('Sex_Code');
								var is_retired = ((sex_code == 2 && person_age >= 55) || (sex_code == 1 && person_age >= 60)); //опредлеяем, пенсионер ли наш пациент
								*/
								if(that.action == 'view'){
									var ReceptForm_id = Ext.isEmpty(base_form.findField('ReceptForm_id').getValue())?base_form.findField('ReceptForm_id').getValue():0;
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
								/*var index = base_form.findField('ReceptValid_id').getStore().findBy(function(rec) {
									if(new_date >= '2016-01-01')
										return is_retired?(rec.get('ReceptValid_Code') == 10):(rec.get('ReceptValid_Code') == 9);
									else
										return is_retired?(rec.get('ReceptValid_Code') == 2):(rec.get('ReceptValid_Code') == 1);
								});
								if ( index >= 0 ) {
									base_form.findField('ReceptValid_id').setValue(base_form.findField('ReceptValid_id').getStore().getAt(index).get('ReceptValid_id'));
								}*/
								if (!Ext.isEmpty(arr) && !Ext.isEmpty(arr[0])) {
									if (base_form.findField('PersonEvn_id').getValue() == -1) {
										base_form.findField('PersonEvn_id').setValue(arr[0].get('PersonEvn_id'));
									}

									if (base_form.findField('Server_id').getValue() == -1) {
										base_form.findField('Server_id').setValue(arr[0].get('Server_id'));
									}
								}
									win.refreshFieldsVisibility();
							}
						});
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

						// Кнопка "Печать"
						// @task https://redmine.swan.perm.ru//issues/96229
						if ( base_form.findField('Lpu_id').getValue() == getGlobalOptions().lpu_id && getRegionNick() != 'kz' ) {
							this.showPrintButton(true);
						}

						if (isSaratov && this.ARMType == 'dpoint') {
							var ReceptDelayType_id = base_form.findField('ReceptDelayType_id').getValue();

							if ( ReceptDelayType_id == 1 ) {
								//this.buttons[4].hide();
								//this.buttons[5].hide();
								this.buttons[3].hide();
								this.buttons[4].hide();
							} else
							if ( ReceptDelayType_id == 2 ) {
								//this.buttons[4].show();
								//this.buttons[5].hide();
								this.buttons[3].show();
								this.buttons[4].hide();
							}else {
								//this.buttons[4].show();
								//this.buttons[5].show();
								this.buttons[3].show();
								this.buttons[4].show();
							}
						}

						if (this.viewOnly) {
							this.buttons[1].hide();
							//this.buttons[2].hide();
							//this.buttons[3].hide();
						}

						base_form.findField('EvnRecept_Num').disable();
						base_form.findField('EvnRecept_Ser').disable();
						base_form.findField('ReceptType_id').disable();

						var Diag_id = base_form.findField('Diag_id').getValue();
						var Drug_rlsid = base_form.findField('Drug_rlsid').getValue();
						var DrugFinance_id = base_form.findField('DrugFinance_id').getValue();
						var DrugComplexMnn_id = base_form.findField('DrugComplexMnn_id').getValue();
						var EvnRecept_setDate = Ext.util.Format.date(base_form.findField('EvnRecept_setDate').getValue(), 'd.m.Y');
						var EvnRecept_setDate_Obj = base_form.findField('EvnRecept_setDate').getValue();
						var LpuSection_id = base_form.findField('LpuSection_id').getValue();
						var MedPersonal_id = base_form.findField('MedPersonal_id').getValue();
						var OrgFarmacy_id = base_form.findField('OrgFarmacy_id').getValue();
						var Person_id = base_form.findField('Person_id').getValue();
						var PrivilegeType_id = base_form.findField('PrivilegeType_id').getValue();
						var ReceptType_Code = 0;
						var ReceptType_id = base_form.findField('ReceptType_id').getValue();
						var WhsDocumentCostItemType_id = base_form.findField('WhsDocumentCostItemType_id').getValue();
						var PersonRegisterType_id = base_form.findField('WhsDocumentCostItemType_id').getFieldValue('PersonRegisterType_id');
						var EvnRecept_IsKEK = base_form.findField('EvnRecept_IsKEK').getValue();
						var EvnRecept_IsMnn = base_form.findField('EvnRecept_IsMnn').getValue();
						var PersonAmbulatCard_id = base_form.findField('PersonAmbulatCard_id').getValue();

						base_form.findField('Diag_id').clearValue();
						base_form.findField('Drug_rlsid').clearValue();
						base_form.findField('DrugComplexMnn_id').clearValue();
						base_form.findField('LpuSection_id').clearValue();
						base_form.findField('OrgFarmacy_id').clearValue();
						base_form.findField('PrivilegeType_id').clearValue();
						base_form.findField('EvnRecept_IsNotOstat').setValue(null);
						//base_form.findField('ReceptDelayType_id').setValue(null);

						// Устанавливаем значение комбо "Выписка по МНН"
						index = ismnn_combo.getStore().findBy(function(rec) { return rec.get('YesNo_id') == ismnn_combo.getValue(); });
						if (index >= 0) {
							ismnn_combo.fireEvent('select', ismnn_combo, ismnn_combo.getStore().getAt(index), 0);
						}

						// Получаем код типа рецепта
						index = base_form.findField('ReceptType_id').getStore().findBy(function(rec) {
							return (rec.get('ReceptType_id') == ReceptType_id);
						});

						if ( index >= 0 ) {
							ReceptType_Code = base_form.findField('ReceptType_id').getStore().getAt(index).get('ReceptType_Code');
						}
						if((ReceptType_Code == 2 || (ReceptType_Code == 3 && base_form.findField('EvnRecept_IsSigned').getValue() == 2))&& base_form.findField('Lpu_id').getValue() == getGlobalOptions().lpu_id){
							win.showPrintButton(true);
						}

                        // Фильтр на список отделений
                        var lpuSectionFilter = !this.isKardio ? {
                            allowLowLevel: 'yes',
                            isDlo: true,
                            onDate: EvnRecept_setDate
                        } : {};

                        // Фильтр на список врачей (мест работы)
                        var medStaffFactFilter = !this.isKardio ? {
                            allowLowLevel: 'yes',
                            isDlo: true,
                            onDate: EvnRecept_setDate,
                            fromRecept: true
                        } : {};

                        if ( !Ext.isEmpty(this.userMedStaffFact.LpuSection_id) && !Ext.isEmpty(this.userMedStaffFact.MedStaffFact_id) ) {
                            if ( this.action == 'edit' ) {
                                base_form.findField('LpuSection_id').disable();
                                base_form.findField('MedStaffFact_id').disable();
                            }
                            if (win.isKardio) {
                                lpuSectionFilter.LpuSection_id = this.userMedStaffFact.LpuSection_id;
                                medStaffFactFilter.MedStaffFact_id = this.userMedStaffFact.MedStaffFact_id;
							}
                        }

						// Фильтр на список отделений
						setLpuSectionGlobalStoreFilter(lpuSectionFilter);

						// Фильтр на список врачей (мест работы)
						setMedStaffFactGlobalStoreFilter(medStaffFactFilter);

						// Загружаем локальные списки отделений и мест работы врачей
						base_form.findField('LpuSection_id').getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));
						base_form.findField('MedStaffFact_id').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));

						// Устанавливаем значение поля "Отделение"
						index = base_form.findField('LpuSection_id').getStore().findBy(function(rec) {
							return (rec.get('LpuSection_id') == LpuSection_id);
						});

						if ( index >= 0 ) {
							base_form.findField('LpuSection_id').setValue(LpuSection_id);
						}
						else {
							// Если нужного отделения нет в локальном списке, то тянем данные с сервера
							Ext.Ajax.request({
								failure: function(response, options) {
									//
								},
								params: {
									LpuSection_id: LpuSection_id
								},
								success: function(response, options) {
									base_form.findField('LpuSection_id').getStore().loadData(Ext.util.JSON.decode(response.responseText), true);

									index = base_form.findField('LpuSection_id').getStore().findBy(function (rec, id) {
										return (rec.get('LpuSection_id') == LpuSection_id);
									});

									if ( index >= 0 ) {
										base_form.findField('LpuSection_id').setValue(LpuSection_id);
									}
								}.createDelegate(this),
								url: C_LPUSECTION_LIST
							});
						}

						// Устанавливаем значение поля "Врач"
						index = base_form.findField('MedStaffFact_id').getStore().findBy(function(rec, id) {
							return (rec.get('LpuSection_id') == LpuSection_id && rec.get('MedPersonal_id') == MedPersonal_id);
						})

						if ( index >= 0 ) {
							base_form.findField('MedStaffFact_id').setValue(base_form.findField('MedStaffFact_id').getStore().getAt(index).get('MedStaffFact_id'));
						}
						else {
							// Если нужного отделения нет в локальном списке, то тянем данные с сервера
							Ext.Ajax.request({
								failure: function(response, options) {
									//
								},
								params: {
									 LpuSection_id: LpuSection_id
									,MedPersonal_id: MedPersonal_id
								},
								success: function(response, options) {
									base_form.findField('MedStaffFact_id').getStore().loadData(Ext.util.JSON.decode(response.responseText), true);

									index = base_form.findField('MedStaffFact_id').getStore().findBy(function(rec) {
										return (rec.get('LpuSection_id') == LpuSection_id && rec.get('MedPersonal_id') == MedPersonal_id);
									});

									if ( index >= 0 ) {
										base_form.findField('MedStaffFact_id').setValue(base_form.findField('MedStaffFact_id').getStore().getAt(index).get('MedStaffFact_id'));
									}
								}.createDelegate(this),
								url: C_MEDPERSONAL_LIST
							});
						}

						// Загружаем диагноз
						base_form.findField('Diag_id').getStore().load({
							callback: function() {
								index = base_form.findField('Diag_id').getStore().findBy(function(rec) {
									return (rec.get('Diag_id') == Diag_id);
								});

								if ( index >= 0 ) {
									base_form.findField('Diag_id').setValue(Diag_id);
									base_form.findField('Diag_id').fireEvent('select', base_form.findField('Diag_id'), base_form.findField('Diag_id').getStore().getAt(index));
								}
							},
							params: { where: "where Diag_id = " + Diag_id }
						});

						// Загружаем карту
						if (PersonAmbulatCard_id) {
							this.ambulat_card_combo.setValueById(PersonAmbulatCard_id);
						}

						// Устанавливаем видимость полей связанных с протоколом ВК
						base_form.findField('EvnRecept_IsKEK').setLinkedFieldValues();

						// Устанавливаем базовые параметры для списков МНН и медикаментов
						base_form.findField('Drug_rlsid').getStore().baseParams.Date = EvnRecept_setDate;
						base_form.findField('Drug_rlsid').getStore().baseParams.ReceptType_Code = ReceptType_Code;
						base_form.findField('Drug_rlsid').getStore().baseParams.DrugFinance_id = DrugFinance_id;
						base_form.findField('Drug_rlsid').getStore().baseParams.WhsDocumentCostItemType_id = WhsDocumentCostItemType_id;
						base_form.findField('Drug_rlsid').getStore().baseParams.LpuSection_id = LpuSection_id;
						base_form.findField('DrugComplexMnn_id').getStore().baseParams.Date = EvnRecept_setDate;
						base_form.findField('DrugComplexMnn_id').getStore().baseParams.ReceptType_Code = ReceptType_Code;
						base_form.findField('DrugComplexMnn_id').getStore().baseParams.DrugFinance_id = DrugFinance_id;
						base_form.findField('DrugComplexMnn_id').getStore().baseParams.WhsDocumentCostItemType_id = WhsDocumentCostItemType_id;
						base_form.findField('DrugComplexMnn_id').getStore().baseParams.LpuSection_id = LpuSection_id;
						base_form.findField('DrugComplexMnn_id').getStore().baseParams.PersonRegisterType_id = PersonRegisterType_id;
						base_form.findField('DrugComplexMnn_id').getStore().baseParams.Person_id = Person_id;
						base_form.findField('DrugComplexMnn_id').getStore().baseParams.Diag_id = Diag_id;
						base_form.findField('DrugComplexMnn_id').getStore().baseParams.EvnRecept_IsKEK = EvnRecept_IsKEK;
						base_form.findField('DrugComplexMnn_id').getStore().baseParams.EvnRecept_IsMnn = EvnRecept_IsMnn;
						base_form.findField('OrgFarmacy_id').getStore().baseParams.Date = EvnRecept_setDate;
						base_form.findField('OrgFarmacy_id').getStore().baseParams.ReceptType_Code = ReceptType_Code;
						base_form.findField('OrgFarmacy_id').getStore().baseParams.WhsDocumentCostItemType_id = WhsDocumentCostItemType_id;
						base_form.findField('OrgFarmacy_id').getStore().baseParams.LpuSection_id = LpuSection_id;

						// Загружаем записи из регистра заболевания для выбранного пациента
						this.personRegisterStore.load({
							callback: function(records, options, success) {
								base_form.clearInvalid();

								// Загружаем список категорий льгот пациента
								base_form.findField('PrivilegeType_id').lastQuery = '';
								base_form.findField('PrivilegeType_id').getStore().load({
									callback: function(records, options, success) {
										// Фильтруем закрытые записи
										if (this.action != 'view') {
											base_form.findField('PrivilegeType_id').filterClosedPrivilege();
										}

										//в режиме просмотра фильтр по программе не нужен
										base_form.findField('WhsDocumentCostItemType_id').lastQuery = '';
										base_form.findField('WhsDocumentCostItemType_id').getStore().clearFilter();

										index = base_form.findField('WhsDocumentCostItemType_id').getStore().findBy(function(rec) {
											return (rec.get('WhsDocumentCostItemType_id') == WhsDocumentCostItemType_id);
										});

										if ( index >= 0 ) {
											base_form.findField('WhsDocumentCostItemType_id').setValue(WhsDocumentCostItemType_id);
										}
										else {
											base_form.findField('WhsDocumentCostItemType_id').clearValue();
										}

										// Подставляем значение
										index = base_form.findField('PrivilegeType_id').getStore().findBy(function(rec) {
											return (rec.get('PrivilegeType_id') == PrivilegeType_id);
										});

										if ( index >= 0 ) {
											base_form.findField('PrivilegeType_id').setValue(PrivilegeType_id);
                                            base_form.findField('PrivilegeType_id').fireEvent('change', base_form.findField('PrivilegeType_id'), PrivilegeType_id);
										}
									}.createDelegate(this),
									params: {
										 date: EvnRecept_setDate
										,Person_id: Person_id
									}
								});

								// Загружаем МНН
								base_form.findField('DrugComplexMnn_id').getStore().load({
									callback: function(records, options, success) {
										index = base_form.findField('DrugComplexMnn_id').getStore().findBy(function(rec) {
											return (rec.get('DrugComplexMnn_id') == DrugComplexMnn_id);
										});

										if ( index >= 0 ) {
											base_form.findField('DrugComplexMnn_id').setValue(DrugComplexMnn_id);
										}

										//на случай если аптека указана а торговое наименование нет, подружаем список соатков по комплексному (в данный момент доступно только для Москвы)
										if ( !Ext.isEmpty(OrgFarmacy_id) && !Ext.isEmpty(DrugComplexMnn_id) && Ext.isEmpty(Drug_rlsid) ) {
											win.loadOrgFarmacyComboByDrugData({
												DrugComplexMnn_id: DrugComplexMnn_id,
												OrgFarmacy_id: OrgFarmacy_id
											});
										}

										// Загружаем медикамент
										base_form.findField('Drug_rlsid').getStore().baseParams.Drug_rlsid = Drug_rlsid;
										base_form.findField('Drug_rlsid').getStore().baseParams.DrugComplexMnn_id = DrugComplexMnn_id;
										base_form.findField('Drug_rlsid').getStore().load({
											callback: function(records, options, success) {
												index = base_form.findField('Drug_rlsid').getStore().findBy(function(rec) {
													return (rec.get('Drug_rlsid') == Drug_rlsid);
												});

												if (index >= 0) {
													base_form.findField('Drug_rlsid').setValue(Drug_rlsid);
												}

												// Загружаем аптеку
												if (!Ext.isEmpty(OrgFarmacy_id) && !Ext.isEmpty(Drug_rlsid)) {
													win.loadOrgFarmacyComboByDrugData({
														Drug_id: Drug_rlsid,
														OrgFarmacy_id: OrgFarmacy_id,
														callback: function() {
															loadMask.hide();

															if ( !base_form.findField('ReceptType_id').disabled ) {
																base_form.findField('ReceptType_id').focus(true, 250);
															} else if ( !base_form.findField('EvnRecept_setDate').disabled ) {
																base_form.findField('EvnRecept_setDate').focus(true, 250);
															} else {
																win.buttons[win.buttons.length - 1].focus();
															}
														}
													});
												} else {
													loadMask.hide();

													if ( !base_form.findField('ReceptType_id').disabled ) {
														base_form.findField('ReceptType_id').focus(true, 250);
													} else if ( !base_form.findField('EvnRecept_setDate').disabled ) {
														base_form.findField('EvnRecept_setDate').focus(true, 250);
													} else {
														this.buttons[this.buttons.length - 1].focus();
													}
												}
											}.createDelegate(this),
											params: {
												Drug_rlsid: Drug_rlsid
											}
										});
									}.createDelegate(this),
									params: {
										DrugComplexMnn_id: DrugComplexMnn_id
									}
								});
							}.createDelegate(this),
							params: {
								Person_id: base_form.findField('Person_id').getValue()
							}
						});

						//if (base_form.findField('ReceptWrongDelayType_id').getValue() == 3) {
							//Ext.getCmp('ERREF_DrugWrong').show();
						//}

						if(win.action=='view') {
                            Ext.getCmp('EREF_DrugResult').show();
						} else {
                            Ext.getCmp('EREF_DrugResult').hide();
						}
						Ext.getCmp('EREF_DrugResult').disable();

                        win.setEvnCourseTreatFieldsVisible();

                        //при сокрытии или отображении панелей, может сбиться нумерация панелей, поэтому обновляем её
						//win.FormPanel.refreshPanelTitles();
					}.createDelegate(this),
					url: C_EVNREC_LOAD
				});
			break;

			default:
				sw.swMsg.alert('Ошибка', 'Неверно указан режим открытия формы', function() { this.hide(); }.createDelegate(this) );
			break;
		}
	},
	signRecept: function() {
		var base_form = this.FormPanel.getForm();

		// Добавить на сервере проверку признака "Подписан" для существующего рецепта
		if ( this.action == 'add' ) {
			this.doSave({
				checkPersonAge: true,
				checkPersonDeadDT: true,
				checkPersonSnils: true,
				copy: false,
				print: false,
				sign: true
			});
		}
		else {
			signedDocument({
				 allowQuestion: false
				,callback: function(success) {
					if ( success == true ) {
						this.action = 'view';

						// Не показывать кнопку Печать при открытии из АРМ Товароведа и Провизора
						/*if ( !(getGlobalOptions().region.nick == 'saratov' && this.ARMType.inlist(['merch','dpoint'])) ) {
							this.showPrintButton(true);
						}*/
						if ( base_form.findField('Lpu_id').getValue() == getGlobalOptions().lpu_id ) {
                            this.showPrintButton(true);
						}
						//this.buttons[3].hide();

						base_form.findField('EvnRecept_IsSigned').setValue(2);

						this.setTitleByAction();
						this.enableEdit(false);

						sw.swMsg.alert('Сообщение', 'Рецепт успешно подписан', function() {
							if ( !(getGlobalOptions().region.nick == 'saratov' && this.ARMType.inlist(['merch','dpoint'])) ) {
								this.buttons[2].focus();
							} else {
								//this.buttons[7].focus();
								this.buttons[6].focus();
							}
						}.createDelegate(this));
					}
					else {
						sw.swMsg.alert('Ошибка', 'Ошибка при выполнении процедуры подписания рецепта');
					}
				 }.createDelegate(this)
				,Evn_id: base_form.findField('EvnRecept_id').getValue()
			});
		}
	},

	/**
	 * Вычисляем и устанавливаем максимальное количество препарата по рецепту в комбобокс "Макс. по рецепту"
	 */
	setMaxKurs: function(DosKurs) {
		var base_form = this.FormPanel.getForm(),
			RVCombo = base_form.findField('ReceptValid_id'),
			ReceptValid_id = RVCombo.getValue(),
			MaxKurs, RVType, RVValue;

		var index = RVCombo.getStore().findBy(function(rec){
			return (rec.get('ReceptValid_id') == ReceptValid_id);
		});
		var rec = RVCombo.getStore().getAt(index);
		RVType = rec.get('ReceptValidType_id');
		RVValue = rec.get('ReceptValid_Value');

		if (RVType == 1){										//срок задан в днях
			MaxKurs = (DosKurs * (RVValue/30)).toFixed(1);
		} else if (RVType == 2) {								//срок задан в месяцах
			MaxKurs = (DosKurs * RVValue).toFixed(1);
		} else if (RVType == 3) {								//срок задан в годах
			MaxKurs = ((DosKurs * RVValue)/12).toFixed(1);
		}
		base_form.findField('EvnRecept_MaxKurs').setValue(MaxKurs);
		base_form.findField('EvnRecept_MaxKurs').fireEvent('change', base_form.findField('EvnRecept_MaxKurs'), MaxKurs);
	},

	/**
	 * Определяем видимость комбобокса "Макс. по рецепту", одновременно получаем максимальное количество препарата в расчете на 1 месяц
	 */
	setEvnReceptMaxKurs: function() {
		//делаем видимым поле "Макс. по рецепту", если в таблице v_DrugComplexMnnCode для выбранного препарата не пустое поле
		// DrugComplexMnnCode_DosKurs (максимальное кол-во упаковок препарата на 1 месяц)
		var base_form = this.FormPanel.getForm();
		var DrugComplexMnn_id = base_form.findField('DrugComplexMnn_id').getValue();

		var labelWarning = Ext.getCmp('EvnRecept_KolvoWarning');
		if(labelWarning.hidden == false) {
			labelWarning.hide();
		}
		base_form.findField('EvnRecept_MaxKurs').hideContainer();
		base_form.findField('EvnRecept_MaxKurs').setRawValue('');

		if (!Ext.isEmpty(DrugComplexMnn_id)) {
			Ext.Ajax.request({
				callback: function (options, success, response) {
					if (success) {
						var response_obj = Ext.util.JSON.decode(response.responseText);
						if (response_obj && response_obj.length > 0 && !Ext.isEmpty(response_obj[0].DrugComplexMnnCode_DosKurs)) {
							base_form.findField('EvnRecept_MaxKurs').showContainer();
							var DosKurs = response_obj[0].DrugComplexMnnCode_DosKurs;
							this.setMaxKurs(DosKurs);
						}
					}
				}.createDelegate(this),
				params: {DrugComplexMnn_id: DrugComplexMnn_id},
				url: '/?c=EvnRecept&m=getDosKurs'
			});
		}
	},
	title: WND_DLO_RECADD,
	width: 1000
});

