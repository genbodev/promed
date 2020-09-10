/**
 * swEvnPSEditWindow - окно редактирования/добавления карты выбывшего из стационара.
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Hospital
 * @access       public
 * @copyright    Copyright (c) 2009 Swan Ltd.
 * @author       Stas Bykov aka Savage (savage@swan.perm.ru)
 * @version      0.001-09.03.2010
 * @comment      Префикс для id компонентов EPSEF (EvnPSEditForm)
 *
 *
 * @input data: action - действие (add, edit, view)
 *              EvnPS_id - ID КВС для редактирования или просмотра
 *              Person_id - ID человека
 *              PersonEvn_id - ID состояния человека
 *              Server_id - ID сервера
 *
 *
 * Использует: окно редактирования диагноза в стационаре (swEvnDiagPSEditWindow)
 *             окно редактирования движения пациента в стационаре (swEvnSectionEditWindow)
 *             окно выписки листа нетрудоспособности (swEvnStickEditWindow)
 *             окно редактирования общей услуги (swEvnUslugaCommonEditWindow)
 *             окно добавления комплексной услуги (swEvnUslugaComplexEditWindow)
 *             окно добавления оперативной услуги (swEvnUslugaOperEditWindow)
 */
sw.Promed.swEvnPSEditWindow = Ext.extend(sw.Promed.BaseForm, {
	action: null,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: false,
	closeAction: 'hide',
	collapsible: true,
	deleteEvent: function(event, options) {
		options = options || {};
		// @options.ignoreEvnStickIsClosed int
		/*if ( this.action == 'view') {
			return false;
		}*/

		if ( !event.inlist(['EvnStick', 'EvnUsluga', 'EvnDiagPSHosp', 'EvnDiagPSRecep', 'EvnSection', 'EvnDrug']) ) {
			return false;
		}

		var base_form = this.findById('EvnPSEditForm').getForm();
		var error = '';
		var grid = null;
		var question = '';
		var params = new Object();
		var url = '';

		switch ( event ) {
			case 'EvnDrug':
				grid = this.findById('EPSEF_EvnDrugGrid');
				break;

			case 'EvnSection':
				grid = this.findById('EPSEF_EvnSectionGrid');
				break;

			case 'EvnStick':
				grid = this.findById('EPSEF_EvnStickGrid');
				break;

			case 'EvnUsluga':
				grid = this.findById('EPSEF_EvnUslugaGrid');
				break;

			case 'EvnDiagPSHosp':
				grid = this.findById('EPSEF_EvnDiagPSHospGrid');
				break;

			case 'EvnDiagPSRecep':
				grid = this.findById('EPSEF_EvnDiagPSRecepGrid');
				break;
		}

		if ( !grid || !grid.getSelectionModel().getSelected() ) {
			return false;
		}
		else if ( (event == 'EvnDiagPSHosp' || event == 'EvnDiagPSRecep') && !grid.getSelectionModel().getSelected().get('EvnDiagPS_id') ) {
			return false;
		}
		else if ( event != 'EvnDiagPSHosp' && event != 'EvnDiagPSRecep' && !grid.getSelectionModel().getSelected().get(event + '_id') ) {
			return false;
		}

		var selected_record = grid.getSelectionModel().getSelected();

		if (selected_record.get('EvnClass_SysNick') == 'EvnUslugaPar') {
			return false;
		}
		
		switch ( event ) {
			case 'EvnDrug':
				error = lang['pri_udalenii_sluchaya_ispolzovaniya_medikamentov_voznikli_oshibki'];
				question = lang['udalit_sluchay_ispolzovaniya_medikamentov'];
				url = '/?c=EvnDrug&m=deleteEvnDrug';

				params['EvnDrug_id'] = selected_record.get('EvnDrug_id');
				break;

			case 'EvnSection':
				error = lang['pri_udalenii_sluchaya_dvijeniya_patsienta_v_statsionare_voznikli_oshibki'];
				question = lang['udalit_sluchay_dvijeniya_patsienta_v_statsionare'];
				url = '/?c=Evn&m=deleteEvn';

				params['Evn_id'] = selected_record.get('EvnSection_id');
				break;

			case 'EvnStick':
				var evn_ps_id = base_form.findField('EvnPS_id').getValue();
				var evn_stick_mid = selected_record.get('EvnStick_mid');

				if ( selected_record.get('evnStickType') == 3 ) {
					if ( evn_ps_id == evn_stick_mid ) {
						error = lang['pri_udalenii_spravki_uchaschegosya_voznikli_oshibki'];
						question = lang['udalit_spravku_uchaschegosya'];
					}
					else {
						error = lang['pri_udalenii_svyazi_spravki_uchaschegosya_s_tekuschim_dokumentom_voznikli_oshibki'];
						question = lang['udalit_svyaz_spravki_uchaschegosya_s_tekuschim_dokumentom'];
					}

					url = '/?c=Stick&m=deleteEvnStickStudent';

					params['EvnStickStudent_id'] = selected_record.get('EvnStick_id');
					params['EvnStickStudent_mid'] = evn_ps_id;
				}
				else {
					error = lang['pri_udalenii_lvn_voznikli_oshibki'];
					question = lang['udalit_lvn'];

					url = '/?c=Stick&m=deleteEvnStick';

					params['EvnStick_id'] = selected_record.get('EvnStick_id');
					params['EvnStick_mid'] = evn_ps_id;
				}
				break;

			case 'EvnUsluga':
				error = lang['pri_udalenii_uslugi_voznikli_oshibki'];
				question = lang['udalit_uslugu'];
				url = '/?c=EvnUsluga&m=deleteEvnUsluga';

				params['class'] = selected_record.get('EvnClass_SysNick');
				params['id'] = selected_record.get('EvnUsluga_id');
				break;

			case 'EvnDiagPSHosp':
			case 'EvnDiagPSRecep':
				error = lang['pri_udalenii_diagnoza_voznikli_oshibki'];
				question = lang['udalit_diagnoz'];
				url = '/?c=EvnDiag&m=deleteEvnDiag';

				params['class'] = 'EvnDiagPS';
				params['id'] = selected_record.get('EvnDiagPS_id');
				break;
		}

		var alert = {
			EvnSection: {
				'701': {
					buttons: Ext.Msg.YESNO,
					fn: function(buttonId, scope) {
						if (buttonId == 'yes') {
							options.ignoreDoc = true;
							scope.deleteEvent(event, options);
						}
					}
				}
			}
		};

		if (options.ignoreDoc) {
			params.ignoreDoc = options.ignoreDoc;
		}

		var doDelete = function() {
			var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Удаление записи..."});
			loadMask.show();

			Ext.Ajax.request({
				callback: function(options, success, response) {
					loadMask.hide();
					if ( success ) {
						var response_obj = Ext.util.JSON.decode(response.responseText);

						if ( response_obj.success == false ) {
							if (response_obj.Alert_Msg) {
								var a_params = alert[event][response_obj.Alert_Code];
								sw.swMsg.show({
									buttons: a_params.buttons,
									fn: function(buttonId) {
										a_params.fn(buttonId, this);
									}.createDelegate(this),
									msg: response_obj.Alert_Msg,
									icon: Ext.MessageBox.QUESTION,
									title: lang['vopros']
								});
							} else {
								sw.swMsg.alert(lang['oshibka'], response_obj.Error_Msg ? response_obj.Error_Msg : error);
							}
						} else {
							grid.getStore().remove(selected_record);

							if ( grid.getStore().getCount() == 0 ) {
								grid.getTopToolbar().items.items[1].disable();
								grid.getTopToolbar().items.items[2].disable();
								grid.getTopToolbar().items.items[3].disable();
								LoadEmptyRow(grid);
							}
							
							grid.getView().focusRow(0);
							grid.getSelectionModel().selectFirstRow();
						}

					}
					else {
						sw.swMsg.alert(lang['oshibka'], error);
					}
				}.createDelegate(this),
				params: params,
				url: url
			});
		}.createDelegate(this);


		if (options.ignoreQuestion) {
			doDelete();
		} else {
			sw.swMsg.show({
				buttons: Ext.Msg.YESNO,
				fn: function(buttonId, text, obj) {
					if ( buttonId == 'yes' ) {
						options.ignoreQuestion = true;
						doDelete();
					} else {
						grid.getView().focusRow(0);
						grid.getSelectionModel().selectFirstRow();
					}
				}.createDelegate(this),
				icon: Ext.MessageBox.QUESTION,
				msg: question,
				title: lang['vopros']
			});
		}
	},
	refreshFieldsVisibility: function(fieldNames) {
		var win = this;
		var base_form = win.findById('EvnPSEditForm').getForm();
		if (typeof fieldNames == 'string') fieldNames = [fieldNames];

		var action = win.action;

		base_form.items.each(function(field){
			if (!Ext.isEmpty(fieldNames) && !field.getName().inlist(fieldNames)) return;

			var value = field.getValue();
			var allowBlank = null;
			var visible = null;
			var enable = null;
			var filter = null;

			var EvnPS_setDate = base_form.findField('EvnPS_setDate').getValue();
			var EvnPS_OutcomeDate = base_form.findField('EvnPS_OutcomeDate').getValue();

			switch(field.getName()) {
				case 'DiagSetPhase_did':
				case 'DiagSetPhase_pid':
					field.getStore().clearFilter();
					field.lastQuery = '';
					var cmpdate = new Date();
					if(!Ext.isEmpty(EvnPS_OutcomeDate)) cmpdate = EvnPS_OutcomeDate;
					else if(!Ext.isEmpty(EvnPS_setDate)) cmpdate = EvnPS_setDate;
					
					field.getStore().filterBy(function(rec) {
						return (!rec.get('DiagSetPhase_begDT') || rec.get('DiagSetPhase_begDT') <= cmpdate)
								&& (!rec.get('DiagSetPhase_endDT') || rec.get('DiagSetPhase_endDT') >= cmpdate);
					});
					var DSPid = field.getStore().findBy(function(rec){
							return rec.get('DiagSetPhase_id')==field.getValue();
						});
					if(DSPid<0) field.clearValue(); else field.setValue(field.getValue());
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
	setMKB: function(){
		var parentWin =this
		var base_form = this.findById('EvnPSEditForm').getForm();
		var sex = parentWin.findById('EPSEF_PersonInformationFrame').getFieldValue('Sex_Code');
		var age = swGetPersonAge(parentWin.findById('EPSEF_PersonInformationFrame').getFieldValue('Person_Birthday'),base_form.findField('EvnPS_setDate').getValue());
		base_form.findField('Diag_pid').setMKBFilter(age,sex,true);
		base_form.findField('Diag_did').setMKBFilter(age,sex,true);
	},
	updateLookChange: function() {
		var base_form = this.findById('EvnPSEditForm').getForm();

		for (fieldName in this.lookChange) {
			if (base_form.findField(fieldName)) {
				this.lookChange[fieldName] = base_form.findField(fieldName).getValue();
			}
		}
	},
	isChange: function(fieldName) {
		var base_form = this.findById('EvnPSEditForm').getForm();
		var compare1 = this.lookChange[fieldName];
		var compare2 = base_form.findField(fieldName).getValue();

		if (Ext.isDate(compare1)) {
			compare1 = Ext.util.Format.date(compare1, 'd.m.Y');
		}
		if (Ext.isDate(compare2)) {
			compare2 = Ext.util.Format.date(compare2, 'd.m.Y');
		}

		return (compare1 != compare2);
	},
	
	getFinanceSource: function() {
		var win = this,
			base_form = this.findById('EvnPSEditForm').getForm();
		
		//if (this.IsLoading) return false;
		//if (this.IsProfLoading) return false;

		if (base_form.findField('EvnPS_IsWithoutDirection').getValue() == 2) return false;
		
		if (getRegionNick() != 'kz') return false;
		
		if (this.action.inlist(['view'])) return false;
			
		var params = {
			DirType_id: 1,
			EvnPS_id: base_form.findField('EvnPS_id').getValue(),
			EvnDirection_setDate: Ext.util.Format.date(base_form.findField('EvnPS_setDate').getValue(), 'd.m.Y'),
			LpuUnitType_id: this.getEvnSectionInfo('first').LpuUnitType_id,
			PrehospType_id: base_form.findField('PrehospType_id').getValue(),
			PurposeHospital_id: base_form.findField('PurposeHospital_id').getValue(),
			isStac: 2,
			Person_id: base_form.findField('Person_id').getValue(),
			Diag_cid: base_form.findField('Diag_cid').getValue(),
			Diag_id: base_form.findField('Diag_pid').getValue()
		};
			
		if (!params.Diag_id) return false;
		
		var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Получение источника финансирования..." });
		loadMask.show();

		Ext.Ajax.request({
			callback: function (options, success, response) {
				loadMask.hide();

				if (success) {
					var response_obj = Ext.util.JSON.decode(response.responseText);
					base_form.findField('PayType_id').setValue(response_obj.PayType_id);
				}
				else {
					sw.swMsg.alert(langs('Ошибка'), langs('Ошибка при определении источника финансирования'));
				}
			}.createDelegate(this),
			params: params,
			url: '/?c=ExchangeBL&m=getPayType'
		});
	},
	
	doSave: function(options) {
		// options @Object
		// options.print @Boolean Вызывать печать КВС, если true
		// options.callback @Function Функция, выполняемая после сохранения
		// options.ignoreSetDateDieError @Boolean Игнорировать проверку (даты ЛВН = даты КВС и исхода, если исход = умер)
		var wnd = this;
		
		if ( this.formStatus == 'save' || this.action == 'view' ) {
			return false;
		}
		
		if ( typeof options != 'object' ) {
			options = new Object();
		}

		this.formStatus = 'save';
		
		var base_form = this.findById('EvnPSEditForm').getForm();

		if(this.getEvnSectionInfo('first')){
			if ( base_form.findField('EvnPS_OutcomeDate').getValue()>this.getEvnSectionInfo('first').EvnSection_setDT ) {
				sw.swMsg.show({
					buttons: Ext.Msg.OK,
					fn: function() {
						this.formStatus = 'edit';
						base_form.findField('EvnPS_OutcomeDate').focus(false);
					}.createDelegate(this),
					icon: Ext.Msg.WARNING,
					msg: "Дата исхода из приемного не может быть больше даты госпитализации!",
					title: ERR_INVFIELDS_TIT
				});
				return false;
			}
		}
		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					this.formStatus = 'edit';
					this.findById('EvnPSEditForm').getFirstInvalidEl().focus(false);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		if ( !options.ignoreControlEvnSectionDates ) {
			// контроль на совпадение дат лечения в стационаре с датами движений (refs #7872)

			var stacBegDate = null;
			var stacEndDate = null;

			var flagControlEvnSectionDates = false;
			var controlEvnStickNumber = "";
			var controlEvnStickType = null;

			var emptyEndDate = false;

			this.findById('EPSEF_EvnSectionGrid').getStore().each(function(rec) {
				if (stacBegDate > Ext.util.Format.date(rec.get('EvnSection_setDate'), 'd.m.Y') || stacBegDate == null) {
					stacBegDate = Ext.util.Format.date(rec.get('EvnSection_setDate'), 'd.m.Y');
				}

				if (stacEndDate < Ext.util.Format.date(rec.get('EvnSection_disDate'), 'd.m.Y') || stacEndDate == null) {
					if (rec.get('EvnSection_disDate').length == 0) {emptyEndDate = true;}
					stacEndDate = Ext.util.Format.date(rec.get('EvnSection_disDate'), 'd.m.Y');
				}
			});

			if (emptyEndDate) {
				stacEndDate = null;
			}

			var checkStacBegDate = '';
			var checkStacEndDate = '';
			var checkBegDate = '';
			var checkEndDate = '';

			this.findById('EPSEF_EvnStickGrid').getStore().each(function(rec) {

				if (rec.get('EvnStick_id')) {

					if (rec.get('EvnSection_setDate').length == 0 || (Date.parseDate(rec.get('EvnSection_setDate'), 'd.m.Y') > Date.parseDate(stacBegDate, 'd.m.Y'))) {
						checkStacBegDate = stacBegDate;
					} else {
						checkStacBegDate = rec.get('EvnSection_setDate');
					}

					if (rec.get('EvnSection_setDate').length == 0) {
						checkStacEndDate = stacEndDate;
					} else if (rec.get('EvnSection_disDate').length == 0 || stacEndDate == null) {
						checkStacEndDate = '';
					} else if (Date.parseDate(rec.get('EvnSection_disDate'), 'd.m.Y') < Date.parseDate(stacEndDate, 'd.m.Y')) {
						checkStacEndDate = stacEndDate;
					} else {
						checkStacEndDate = rec.get('EvnSection_disDate');
					}

					if (checkStacEndDate == null) {
						checkStacEndDate = '';
					}

					if (rec.get('evnStickType') == 3) {
						checkBegDate = rec.get('EvnStickWorkRelease_begDate');
						checkEndDate = rec.get('EvnStickWorkRelease_endDate');

						if ( !(checkStacBegDate >= checkBegDate && checkStacBegDate <= checkEndDate && (Ext.isEmpty(checkStacEndDate) || (checkStacEndDate >= checkBegDate && checkStacEndDate <= checkEndDate))) ) {
							flagControlEvnSectionDates = true;
							controlEvnStickNumber = rec.get('EvnStick_Num');
							controlEvnStickType = rec.get('evnStickType');
						}

					} else {
						checkBegDate = rec.get('EvnStick_stacBegDate');
						checkEndDate = rec.get('EvnStick_stacEndDate');

						if ((checkBegDate != checkStacBegDate) || (checkStacEndDate.length > 0 && checkEndDate != checkStacEndDate) || (checkStacEndDate.length == 0 && checkEndDate.length > 0) ) {
							flagControlEvnSectionDates = true;
							controlEvnStickNumber = rec.get('EvnStick_Num');
							controlEvnStickType = rec.get('evnStickType');
						}
					}
				}
			});


			if (flagControlEvnSectionDates) {
				var msg = '';
				if (controlEvnStickType == 3) {
					msg = 'Период лечения в движениях связных КВС не находится в рамках дат освобождения от занятий ('+controlEvnStickNumber+'), Продолжить?';
				} else {
					msg = lang['period_lecheniya_v_statsionare_v_lvn']+controlEvnStickNumber+lang['ne_sovpadaet_s_dannyimi_dvijeniy_svyazannyih_kvs_prodoljit'];
				}

				sw.swMsg.show({
					buttons: Ext.Msg.YESNO,
					fn: function(buttonId, text, obj) {
						this.formStatus = 'edit';

						if ( 'yes' == buttonId ) {
							options.ignoreControlEvnSectionDates = true;
							this.doSave(options);
						}
						else {
							this.buttons[0].focus();
						}
					}.createDelegate(this),
					icon: Ext.MessageBox.QUESTION,
					msg: msg,
					title: lang['vopros']
				});
				return false;
			}
		}
		
		var evnps_setdate = base_form.findField('EvnPS_setDate').getValue();
		var Person_Birthday = this.findById('EPSEF_PersonInformationFrame').getFieldValue('Person_Birthday');
		if (evnps_setdate && Person_Birthday) {
			var age = swGetPersonAge(Person_Birthday, evnps_setdate);
			if (!options.ignoreLpuSectionAgeCheck && ((base_form.findField('LpuSection_pid').getFieldValue('LpuSectionAge_id') == 1 && age <= 17) || (base_form.findField('LpuSection_pid').getFieldValue('LpuSectionAge_id') == 2 && age >= 18))) {
				this.formStatus = 'edit';
				sw.swMsg.show({
					buttons: Ext.Msg.YESNO,
					fn: function(buttonId, text, obj) {
						if ( buttonId == 'yes' ) {
							options.ignoreLpuSectionAgeCheck = true;
							this.doSave(options);
						}
					}.createDelegate(this),
					icon: Ext.MessageBox.QUESTION,
					msg: lang['vozrastnaya_gruppa_otdeleniya_ne_sootvetstvuyut_vozrastu_patsienta_prodoljit'],
					title: lang['vopros']
				});
				
				return false;
			}
		}

		var params = new Object();

		var index;
		var med_staff_fact_pid = base_form.findField('MedStaffFact_pid').getValue();
		var record;

		record = base_form.findField('MedStaffFact_pid').getStore().getById(med_staff_fact_pid);
		if ( record ) {
			params.MedPersonal_pid = record.get('MedPersonal_id');
		}

		if ( base_form.findField('EvnDirection_Num').disabled ) {
			params.EvnDirection_Num = base_form.findField('EvnDirection_Num').getRawValue();
		}

		if ( base_form.findField('Org_did').disabled ) {
			params.Org_did = base_form.findField('Org_did').getValue();
		}

		if ( base_form.findField('LpuSection_did').disabled ) {
			params.LpuSection_did = base_form.findField('LpuSection_did').getValue();
		}

		if ( base_form.findField('PrehospDirect_id').disabled ) {
			params.PrehospDirect_id = base_form.findField('PrehospDirect_id').getValue();
		}

        if ( base_form.findField('PayType_id').disabled ) {
            params.PayType_id = base_form.findField('PayType_id').getValue();
        }

		params.EvnDirection_setDate = Ext.util.Format.date(base_form.findField('EvnDirection_setDate').getValue(), 'd.m.Y');
		var tmp_bool = (/*(base_form.findField('PrehospDirect_id').getValue() == 2 || base_form.findField('PrehospDirect_id').getValue() == 1) &&*/ base_form.findField('EvnDirection_id').getValue() > 0 && !base_form.findField('Diag_did').getValue());

		if (tmp_bool) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					this.formStatus = 'edit';
					base_form.findField('Diag_did').focus(false);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: lang['pri_vyibrannom_napravlenii_pole_osnovnoy_diagnoz_napravivshego_uchrejdeniya_obyazatelno_dlya_zapolneniya'],
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		var diag_name;
		var evn_ps_dis_dt;
		var first_evn_section_info = this.getEvnSectionInfo('first');
		var last_evn_section_info = this.getEvnSectionInfo('last');
		var leave_type_id;
		var leave_type_name;
		var lpu_section_name;
		var pay_type_name;
		var evn_ps_outcome_dt = getValidDT(Ext.util.Format.date(base_form.findField('EvnPS_OutcomeDate').getValue(), 'd.m.Y'), base_form.findField('EvnPS_OutcomeTime').getValue() ? base_form.findField('EvnPS_OutcomeTime').getValue() : '');
		var evn_ps_set_dt = getValidDT(Ext.util.Format.date(base_form.findField('EvnPS_setDate').getValue(), 'd.m.Y'), base_form.findField('EvnPS_setTime').getValue() ? base_form.findField('EvnPS_setTime').getValue() : '');
		var lpu_unit_type_id;

		if ( !Ext.isEmpty(evn_ps_outcome_dt) && evn_ps_outcome_dt < evn_ps_set_dt ) {
			var LpuSectionPriem_Name = base_form.findField('LpuSection_pid').getFieldValue('LpuSection_Name');
			this.formStatus = 'edit';
			sw.swMsg.alert(
				langs('Ошибка'),
				langs('Дата и время поступления в стационар') + ' ' + evn_ps_set_dt.format('d.m.Y H:i') + ' ' + langs('позже даты и времени исхода пребывания в приемном отделении') + ' ' + LpuSectionPriem_Name + ' ' + evn_ps_outcome_dt.format('d.m.Y H:i'),
				function() {
					base_form.findField('EvnPS_OutcomeDate').focus(false);
				}
			);
			return false;
		}

		if ( !Ext.isEmpty(evn_ps_outcome_dt) && (evn_ps_outcome_dt.getTime() - evn_ps_set_dt.getTime()) > 86400000 ) {
			this.formStatus = 'edit';
			sw.swMsg.alert(
				langs('Ошибка'),
				langs('Дата и время поступления в стационар') +' ' + evn_ps_set_dt.format('d.m.Y H:i') + ' ' + langs('раньше даты исхода из приемного отделения') + ' ' + evn_ps_outcome_dt.format('d.m.Y H:i') + ' ' + langs('больше, чем на сутки') + '.',
				function() {
					this.formStatus = 'edit';
					base_form.findField('EvnPS_OutcomeDate').focus(false);
				}.createDelegate(this)
			);
			return false;
		}

		if ( last_evn_section_info.EvnSection_id > 0 ) {
			evn_ps_dis_dt = last_evn_section_info.EvnSection_disDT;
			leave_type_id = last_evn_section_info.LeaveType_id;
			leave_type_name = last_evn_section_info.LeaveType_Name;
			lpu_unit_type_id = last_evn_section_info.LpuUnitType_id;
			pay_type_name = last_evn_section_info.PayType_Name;

			index = this.findById('EPSEF_EvnSectionGrid').getStore().each(function(rec) {
				if ( rec.get('EvnSection_id') == last_evn_section_info.EvnSection_id ) {
					diag_name = rec.get('Diag_Name');
					lpu_section_name = rec.get('LpuSection_Name');
				}
			});
		}

		tmp_bool = (first_evn_section_info.EvnSection_setDT && typeof first_evn_section_info.EvnSection_setDT == 'object');

		if ( tmp_bool ) {
			if ( first_evn_section_info.EvnSection_setDT.getTime() < evn_ps_set_dt.getTime() ) {
				this.formStatus = 'edit';
				sw.swMsg.alert(
					langs('Ошибка'),
					langs('Дата и время поступления в стационар') + ' ' + evn_ps_set_dt.format('d.m.Y H:i') + ' ' + langs('позже даты и времени поступления в отделение') + ' ' + first_evn_section_info.LpuSection_Name + ' ' + first_evn_section_info.EvnSection_setDT.format('d.m.Y H:i'),
					function() {
						base_form.findField('EvnPS_setDate').focus(false);
					}
				);
				return false;
			}
			else if (!options.ignoreOutProfilDT && (first_evn_section_info.EvnSection_setDT.getTime() - evn_ps_set_dt.getTime()) > 86400000 ) {
				this.formStatus = 'edit';
				sw.swMsg.show({
					buttons: Ext.Msg.YESNO,
					fn: function(buttonId, text, obj) {
						if ( buttonId == 'yes' ) {
							options.ignoreOutProfilDT = true;
							this.doSave(options);
						}else{
							base_form.findField('EvnPS_setDate').focus(false);
						}
					}.createDelegate(this),
					icon: Ext.Msg.WARNING,
					msg: 'Дата и время поступления в стационар ' + evn_ps_set_dt.format('d.m.Y H:i') + ' раньше даты и времени поступления в отделение ' + first_evn_section_info.LpuSection_Name + ' ' + first_evn_section_info.EvnSection_setDT.format('d.m.Y H:i') + ' больше чем на сутки. Продолжить?',
					title: ERR_INVFIELDS_TIT
				});
				return false;
			}
		}

		// По задаче #5536 + #6270
		// Если заполнен "Исход госпитализации" и "Тип госпитализации" = Плановая
		// TODO: Второе правильное условие: base_form.findField('PrehospType_id').getFieldValue('PrehospType_SysNick') == 'plan'
		// Поскольку требуется проверка на тип стационара, которая подразумевает обращение к базе - убрал эту проверку на сервер полностью 
		tmp_bool = (leave_type_id && (base_form.findField('EvnPS_IsCont').getValue()==1) && base_form.findField('PrehospType_id').getFieldValue('PrehospType_Code') == 1 && base_form.findField('PrehospDirect_id').getValue() != 1 && base_form.findField('PayType_id').getFieldValue('PayType_SysNick') == 'oms');
		if ( tmp_bool ) {
			// Если направлен другим ЛПУ, то направление д.б. электронным (- данное условие не меняется), а в остальных случаях "ручной ввод".
			// if ( base_form.findField('PrehospDirect_id').getFieldValue('') == 'lpu' ) {}
			// TODO: Данное условие в рамках задачи для нас непринципиально, поскольку проверяем мы на наличие любого направления

			// Контроль на наличие направления
			if (base_form.findField('EvnDirection_Num').getRawValue().length==0) {
				sw.swMsg.show({
					buttons: Ext.Msg.OK,
					fn: function() {
						this.formStatus = 'edit';
						base_form.findField('PrehospDirect_id').focus(false);
					}.createDelegate(this),
					icon: Ext.Msg.WARNING,
					msg: lang['v_sluchae_esli_gospitalizatsiya_planovaya_doljnyi_byit_zapolnenyi_dannyie_o_napravlenii'],
					title: ERR_INVFIELDS_TIT
				});
				return false;
			}
		}

		// Проверка отсутствия движений при отказе в госпитализации #152365
		if (
			base_form.findField('PrehospWaifRefuseCause_id').getValue()
			&& this.findById('EPSEF_EvnSectionGrid').getStore().getAt(0)
			&& this.findById('EPSEF_EvnSectionGrid').getStore().getAt(0).get('EvnSection_id')
		) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					this.formStatus = 'edit';
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: 'Невозможно сохранить КВС с отказом от госпитализации при наличии движения. Удалите движения или очистите поле «Отказ» в разделе  «Исход пребывания в приемном отделении».',
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		// Регион: Казахстан
		// При сохранении формы «Карта выбывшего из стационара» производить контроль:
		// Если Тип госпитализации «Планово» и поле «№ направления» в разделе «Госпитализация» НЕ заполнено, то ошибка
		// «При плановой госпитализации поле «Номер направления» обязательно для заполнения. Выберите электронное
		// направление или заполните поле вручную.». Кнопка «Ок». Сохранение НЕ производится.

		if(base_form.findField('PrehospType_id').getFieldValue('PrehospType_Code') == 1 && base_form.findField('EvnDirection_Num').getRawValue().length == 0){
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					this.formStatus = 'edit';
					base_form.findField('PrehospDirect_id').focus(false);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: langs('При плановой госпитализации поле «Номер направления» обязательно для заполнения. Выберите электронное направление или заполните поле вручную.'),
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}


		// проверки по контролю направлений согласно #8881
		if (base_form.findField('PrehospType_id').getFieldValue('PrehospType_Code') == 1 &&
			base_form.findField('PayType_id').getFieldValue('PayType_SysNick') == 'oms' &&
			evn_ps_dis_dt > getValidDT('31.03.2012', '') &&
			lpu_unit_type_id == 1 &&
			base_form.findField('EvnPS_IsCont').getValue() == 1
			) {
			if ( !options.ignoreControlEvnDate21Day ) {
				var EvnDirection_setDate = base_form.findField('EvnDirection_setDate').getValue();
				// Если минимальная Дата поступления из движений КВС минус дата направления > 21 дня то "Случай, в котором дата направления ранее даты начала лечения более чем на 21 день, может быть не оплачен по ОМС. Продолжить сохранение?"
				tmp_bool = (first_evn_section_info.EvnSection_setDT && EvnDirection_setDate && typeof EvnDirection_setDate == 'object' && typeof first_evn_section_info.EvnSection_setDT == 'object' && first_evn_section_info.EvnSection_setDT.getTime() > EvnDirection_setDate.add(Date.DAY, 21).getTime() );
				if (tmp_bool) {
					sw.swMsg.show({
						buttons: Ext.Msg.YESNO,
						fn: function(buttonId, text, obj) {
							this.formStatus = 'edit';

							if ( 'yes' == buttonId ) {
								options.ignoreControlEvnDate21Day = true;
								this.doSave(options);
							}
							else {
								this.buttons[0].focus();
							}
						}.createDelegate(this),
						icon: Ext.MessageBox.QUESTION,
						msg: lang['sluchay_v_kotorom_data_napravleniya_ranee_datyi_nachala_lecheniya_bolee_chem_na_21_den_otmechaetsya_dlya_provedeniya_ekspertizyi_smo_prodoljit_sohranenie'],
						title: lang['vopros']
					});
					return false;
				}
			}
		}


		params.Diag_did = base_form.findField('Diag_did').getValue();
		params.childPS = this.childPS;
		if ( evn_ps_dis_dt ) {
			params.EvnPS_disDate = Ext.util.Format.date(evn_ps_dis_dt, 'd.m.Y');
			params.EvnPS_disTime = Ext.util.Format.date(evn_ps_dis_dt, 'H:i');
		}

		if ( base_form.findField('EvnPS_IsPLAmbulance').disabled ) {
			params.EvnPS_IsPLAmbulance = base_form.findField('EvnPS_IsPLAmbulance').getValue();
		}

		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение карты выбывшего из стационара..."});
		loadMask.show();

		// Необходимо, что бы ЛВН закрывался датой смерти пациента.
		// ajax запрос на проверку + калбэк.
		var checkparams = params;
		checkparams.EvnPS_id = base_form.findField('EvnPS_id').getValue();
		checkparams.LeaveType_id = leave_type_id;

		/*
		* this.findById('EPSEF_EvnSectionGrid').getStore().getCount()  && !grid.getStore().getAt(0).get('EvnSection_id')
		*/
		/*
		* Отправляем флаг "addEvnSection" для создания пустого движения в том случае,
		* Когда КВС добавляется из АРМа стационара по нажатию кнопки "добавить пациента" (this.form_mode == 'arm_stac_add_patient')
		* Для Уфы когда КВС добавляется из Журнала госпитализаций (this.form_mode == 'dj_hosp')
		*
		* Флаг не должен отправляться,
		* Когда КВС сохраняется перед открытием дочернего окна
		 * Когда в КВС было добавлено движение
		 * Когда в КВС было указан отказ в госпитализации
		*/
		var evnsection_grid = this.findById('EPSEF_EvnSectionGrid');
		if ( 
			this.params.addEvnSection
			&& !options.callback
			&& ( 0 == evnsection_grid.getStore().getCount() || !evnsection_grid.getStore().getAt(0).get('EvnSection_id') )
			&& !base_form.findField('PrehospWaifRefuseCause_id').getValue()
		) {
			params.addEvnSection = 1;
			params.LpuSection_id = this.params.LpuSection_id || null;
			params.MedPersonal_id = this.params.MedPersonal_id || null;
			params.MedStaffFact_id = this.params.MedStaffFact_id || null;
		} else {
			params.addEvnSection = 0;
		}
		
		Ext.Ajax.request(
			{
				url: '/?c=EvnPS&m=CheckEvnPSDie',
				params: checkparams,
				callback: function(opt, scs, response)
				{
					if ( !options.ignoreSetDateDieError ) {

						if (scs)
						{
							if ( response.responseText.length > 0 )
							{
								var result = Ext.util.JSON.decode(response.responseText);
								if (!result.success)
								{
									sw.swMsg.show({
										buttons: Ext.Msg.YESNO,
										fn: function(buttonId, text, obj) {
											this.formStatus = 'edit';

											if ( 'yes' == buttonId ) {
												options.ignoreSetDateDieError = true;
												this.doSave(options);
											}
											else {
												loadMask.hide();
												this.buttons[0].focus();
											}
										}.createDelegate(this),
										icon: Ext.MessageBox.QUESTION,
										msg: lang['ishod_gospitalizatsii_i_ishod_lvn_ne_sovpadayut_libo_otlichayutsya_datyi_smerti_v_lvn_i_kvs_prodoljit'],
										title: lang['vopros']
									});
									return false;
								}
							}
						}
					}

                    if ( base_form.findField('LpuSection_eid').disabled ) {
                        params.LpuSection_eid = base_form.findField('LpuSection_eid').getValue();
                    }
                    if ( options && typeof options.callback == 'function') {
                        params.isAutoCreate = 1;
                    }

					params.vizit_direction_control_check = (options && !Ext.isEmpty(options.vizit_direction_control_check) && options.vizit_direction_control_check === 1) ? 1 : 0;
					params.ignoreParentEvnDateCheck = (!Ext.isEmpty(options.ignoreParentEvnDateCheck) && options.ignoreParentEvnDateCheck === 1) ? 1 : 0;
					params.ignoreEvnPSDoublesCheck = (!Ext.isEmpty(options.ignoreEvnPSDoublesCheck) && options.ignoreEvnPSDoublesCheck === 1) ? 1 : 0;
					params.ignoreEvnPSTimeDeseaseCheck = (!Ext.isEmpty(options.ignoreEvnPSTimeDeseaseCheck) && options.ignoreEvnPSTimeDeseaseCheck === 1) ? 1 : 0;
					base_form.submit({
						failure: function(result_form, action) {
							this.formStatus = 'edit';
							loadMask.hide();

							if ( action.result ) {
								if ( action.result.Alert_Msg ) {
									var msg = getMsgForCheckDoubles(action.result);

									sw.swMsg.show({
										buttons: Ext.Msg.YESNO,
										fn: function(buttonId, text, obj) {
											if ( buttonId == 'yes' ) {
												if (action.result.Error_Code == 102) {
													options.ignoreUslugaComplexTariffCountCheck = 1;
												}
												if (action.result.Error_Code == 112) {
													options.vizit_direction_control_check = 1;
												}
												if (action.result.Error_Code == 113) {
													options.ignoreEvnPSDoublesCheck = 1;
												}
												if (action.result.Error_Code == 109) {
													options.ignoreParentEvnDateCheck = 1;
												}
												if (action.result.Error_Code == 114) {
													options.ignoreEvnPSTimeDeseaseCheck = 1;
												}

												this.doSave(options);
											}
										}.createDelegate(this),
										icon: Ext.MessageBox.QUESTION,
										msg: msg,
										title: lang['prodoljit_sohranenie']
									});
								} else if ( action.result.Error_Msg ) {
									sw.swMsg.alert(lang['oshibka'], action.result.Error_Msg, function() {
										switch ( action.result.Error_Code ) {
											case 1: // Дублирование номера карты
												base_form.findField('EvnPS_NumCard').focus(true);
												break;
										}
									});
								}
								else {
									sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_1]']);
								}
							}
						}.createDelegate(this),
						params: params,
						success: function(result_form, action) {
							this.formStatus = 'edit';
							loadMask.hide();

							if ( action.result ) {
								if ( action.result.EvnPS_id ) {
									var evn_ps_id = action.result.EvnPS_id;
									var koiko_dni = 0;

									var grid = this.findById('EPSEF_EvnSectionGrid');

									this.updateLookChange();

									if ( grid.getStore().getCount() > 0 && grid.getStore().getAt(0).get('EvnSection_id') ) {
										grid.getStore().each(function(rec) {
											if ( rec.get('EvnSection_KoikoDni') ) {
												koiko_dni = koiko_dni + parseInt(rec.get('EvnSection_KoikoDni'));
											}
										});
									}
									// https://redmine.swan.perm.ru/issues/37241
									else if ( !Ext.isEmpty(evn_ps_outcome_dt) ) {
										// Дата исхода из приемного
										evn_ps_dis_dt = evn_ps_outcome_dt; 
										// Койко-дни
										koiko_dni = daysBetween(evn_ps_set_dt, evn_ps_dis_dt);

										if ( koiko_dni > 0 ) {
											koiko_dni = koiko_dni + 1;
										}

										// Диагноз приемного
										if ( !Ext.isEmpty(base_form.findField('Diag_pid').getValue()) ) {
											diag_name = base_form.findField('Diag_pid').getFieldValue('Diag_Code') + '. ' + base_form.findField('Diag_pid').getFieldValue('Diag_Name');
										}

										// Исход из приемного
										if ( !Ext.isEmpty(base_form.findField('PrehospWaifRefuseCause_id').getValue()) ) {
											leave_type_name = base_form.findField('PrehospWaifRefuseCause_id').getFieldValue('PrehospWaifRefuseCause_Name');
										}

										// Вид оплаты
										if ( !Ext.isEmpty(base_form.findField('PayType_id').getValue()) ) {
											pay_type_name = base_form.findField('PayType_id').getFieldValue('PayType_Name');
										}
									}

									base_form.findField('EvnPS_id').setValue(evn_ps_id);

									if ( action.result.PersonChild_id && base_form.findField('PersonChild_id') ) {
										base_form.findField('PersonChild_id').setValue(action.result.PersonChild_id);
									}

									if ( options && (typeof options.callback == 'function') /*&& (this.action == 'add')*/ ) {
										options.callback();
									}
									else {
										var date = null;
										var person_information = this.findById('EPSEF_PersonInformationFrame');
										var response = new Object();

										response.Diag_Name = diag_name;
										response.EvnPS_id = evn_ps_id;
										response.EvnPS_disDate = evn_ps_dis_dt;
										response.EvnPS_KoikoDni = koiko_dni;
										response.EvnPS_NumCard = base_form.findField('EvnPS_NumCard').getValue();
										response.EvnPS_setDate = base_form.findField('EvnPS_setDate').getValue();
										response.LpuSection_Name = lpu_section_name;
										response.Person_Birthday = person_information.getFieldValue('Person_Birthday');
										response.Person_Firname = person_information.getFieldValue('Person_Firname');
										response.Person_id = base_form.findField('Person_id').getValue();
										response.Person_Secname = person_information.getFieldValue('Person_Secname');
										response.Person_Surname = person_information.getFieldValue('Person_Surname');
										response.PersonEvn_id = base_form.findField('PersonEvn_id').getValue();
										response.Server_id = base_form.findField('Server_id').getValue();
										response.Sex_Name = person_information.getFieldValue('Sex_Name');
										response.BirthWeight = this.BirthWeight;
										response.PersonWeight_text = this.PersonWeight_text;
										response.Okei_id = this.Okei_id;
										response.BirthHeight = this.BirthHeight;
										response.countChild = this.countChild;
										response.LeaveType_Name = leave_type_name;
										response.PayType_Name = pay_type_name;

										if (this.childPS) {
											//наличие этой переменной как бы намекает, что окно КВС было вызвано из поиска человека
											//передаю ее дальше по каллбэкам
											this.callback({evnPSData: response}, {opener: this.opener});
										} else {
											this.callback({evnPSData: response});
										}

										if ( action.result.Alert_Msg ) {
											sw.swMsg.alert(lang['preduprejdenie'], action.result.Alert_Msg);
										}

										if ( options && options.print == true ) {
											if ( lpu_unit_type_id == 1 ) {
												printBirt({
													'Report_FileName': 'han_EvnPS_f066u.rptdesign',
													'Report_Params': '&paramEvnPS=' + evn_ps_id,
													'Report_Format': 'pdf'
												});
											}
											else {
												printBirt({
													'Report_FileName': 'han_EvnPS_f066_4u2.rptdesign',
													'Report_Params': '&paramEvnPS=' + evn_ps_id,
													'Report_Format': 'pdf'
												});
											}

											this.action = 'edit';
											this.setTitle(WND_HOSP_EPSEDIT);
										}
										else {
											if(options.printRefuse != true)
												this.hide();
											else
												window.open('/?c=EvnPS&m=printEvnPSPrehospWaifRefuseCause&EvnPS_id='+evn_ps_id, '_blank');
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

				}.createDelegate(this)
			});
	},
	draggable: true,
	evnPSAbortStore: null,
	firstRun: true,
	formStatus: 'edit',
	getEvnPSNumber: function() {
		var evn_ps_num_field = this.findById('EvnPSEditForm').getForm().findField('EvnPS_NumCard');

		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Получение номера карты выбывшего из стационара..."});
		loadMask.show();

		var params = new Object();

		if ( !Ext.isEmpty(this.findById('EvnPSEditForm').getForm().findField('EvnPS_setDate').getValue()) ) {
			params.year = this.findById('EvnPSEditForm').getForm().findField('EvnPS_setDate').getValue().format('Y');
		}

		Ext.Ajax.request({
			callback: function(options, success, response) {
				loadMask.hide();

				if ( success ) {
					var response_obj = Ext.util.JSON.decode(response.responseText);

					evn_ps_num_field.setValue(response_obj.EvnPS_NumCard);
					evn_ps_num_field.focus(true);
				}
				else {
					sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_opredelenii_nomera_kvs']);
				}
			},
			params: params,
			url: '/?c=EvnPS&m=getEvnPSNumber'
		});
	},
	getEvnSectionInfo: function(type, data) {
		if ( !type || typeof type != 'string' || !type.inlist([ 'first', 'last', 'next', 'prev' ]) ) {
			return false;
		}

		if ( typeof data != 'object' ) {
			data = new Object();
		}

		var base_form = this.findById('EvnPSEditForm').getForm();
		var grid = this.findById('EPSEF_EvnSectionGrid');

		var chooseEvnSection;
		var diag_id;
		var diag_name;
		var dis_dt;
		var evn_section_dis_dt;
		var evn_section_id;
		var evn_section_set_dt;
		var leave_type_code, leave_type_sys_nick;
		var leave_type_id;
		var leave_type_name;
		var pay_type_name;
		var set_dt;
		var lpu_unit_type_id;
		var lpu_unit_type_sys_nick;
		var lpu_section_name;

		// Получаем id, дату поступления и дату выписки искомого отделения
		if ( grid.getStore().getCount() > 0 && grid.getStore().getAt(0).get('EvnSection_id') ) {
			grid.getStore().each(function(rec) {
				chooseEvnSection = false;
				dis_dt = getValidDT(Ext.util.Format.date(rec.get('EvnSection_disDate'), 'd.m.Y'), rec.get('EvnSection_disTime'));
				set_dt = getValidDT(Ext.util.Format.date(rec.get('EvnSection_setDate'), 'd.m.Y'), rec.get('EvnSection_setTime'));

				switch ( type ) {
					case 'first':
						if ( !evn_section_set_dt || evn_section_set_dt > set_dt || (evn_section_set_dt == set_dt && (!evn_section_dis_dt || evn_section_dis_dt > dis_dt)) ) {
							chooseEvnSection = true;
						}
						break;

					case 'last':
						if ( !evn_section_set_dt || evn_section_set_dt < set_dt || (evn_section_set_dt == set_dt && (!dis_dt || (evn_section_dis_dt && evn_section_dis_dt < dis_dt))) ) {
							chooseEvnSection = true;
						}
						break;

					case 'next':
						if ( typeof data.EvnSection_setDT == 'object' && rec.get('EvnSection_id') != data.EvnSection_id && data.EvnSection_setDT < set_dt && (!evn_section_set_dt || evn_section_set_dt > set_dt) ) {
							chooseEvnSection = true;
						}
						break;

					case 'prev':
						if ( typeof data.EvnSection_setDT == 'object' && rec.get('EvnSection_id') != data.EvnSection_id && data.EvnSection_setDT > set_dt && (!evn_section_set_dt || evn_section_set_dt < set_dt) ) {
							chooseEvnSection = true;
						}
						break;
				}

				if ( chooseEvnSection == true ) {
					evn_section_dis_dt = dis_dt;
					evn_section_id = rec.get('EvnSection_id');
					evn_section_set_dt = set_dt;
					diag_id = rec.get('Diag_id');
					diag_name = rec.get('Diag_Name')
					leave_type_code = rec.get('LeaveType_Code');
					leave_type_sys_nick = rec.get('LeaveType_SysNick');
					leave_type_id = rec.get('LeaveType_id');
					leave_type_name = rec.get('LeaveType_Name');
					pay_type_name = rec.get('PayType_Name');
					lpu_unit_type_id = rec.get('LpuUnitType_id');
					lpu_unit_type_sys_nick = rec.get('LpuUnitType_SysNick');
					lpu_section_name = rec.get('LpuSection_Name');
				}
			});
		}

		return {
			EvnSection_disDT: evn_section_dis_dt
			,Diag_id: diag_id
			,Diag_name: diag_name
			,EvnSection_id: evn_section_id
			,EvnSection_setDT: evn_section_set_dt
			,LeaveType_Code: leave_type_code
			,LeaveType_SysNick: leave_type_sys_nick
			,LeaveType_id: leave_type_id
			,LeaveType_Name: leave_type_name
			,LpuUnitType_id: lpu_unit_type_id
			,LpuUnitType_SysNick: lpu_unit_type_sys_nick
			,PayType_Name: pay_type_name
			,LpuSection_Name: lpu_section_name
		}
	},
	height: 550,
	id: 'EvnPSEditWindow',
	initComponent: function() {
		var win = this;
		if (this.id == 'EvnPSEditWindow'){
			this.tabindex = TABINDEX_EPSEF;
		} else {
			this.tabindex = TABINDEX_EPSEF2;
		}
		this.keyHandlerAlt = {
			alt: true,
			fn: function(inp, e) {
				var current_window = this;
				switch ( e.getKey() ) {
					case Ext.EventObject.C:
						current_window.doSave();
						break;

					case Ext.EventObject.G:
						current_window.printEvnPS();
						break;

					case Ext.EventObject.J:
						current_window.onCancelAction();
						break;

					case Ext.EventObject.NUM_ONE:
					case Ext.EventObject.ONE:
						if ( !current_window.findById('EPSEF_HospitalisationPanel').hidden ) {
							current_window.findById('EPSEF_HospitalisationPanel').toggleCollapse();
						}
						break;

					case Ext.EventObject.NUM_TWO:
					case Ext.EventObject.TWO:
						if ( !current_window.findById('EPSEF_DirectDiagPanel').hidden ) {
							current_window.findById('EPSEF_DirectDiagPanel').toggleCollapse();
						}
						break;

					case Ext.EventObject.NUM_THREE:
					case Ext.EventObject.THREE:
						if ( !current_window.findById('EPSEF_AdmitDepartPanel').hidden ) {
							current_window.findById('EPSEF_AdmitDepartPanel').toggleCollapse();
						}
						break;

					case Ext.EventObject.FOUR:
					case Ext.EventObject.NUM_FOUR:
						if ( !current_window.findById('EPSEF_AdmitDiagPanel').hidden ) {
							current_window.findById('EPSEF_AdmitDiagPanel').toggleCollapse();
						}
						break;

					case Ext.EventObject.FIVE:
					case Ext.EventObject.NUM_FIVE:
						if ( !current_window.findById('EPSEF_EvnSectionPanel').hidden ) {
							current_window.findById('EPSEF_EvnSectionPanel').toggleCollapse();
						}
						break;

					case Ext.EventObject.NUM_SIX:
					case Ext.EventObject.SIX:
						if ( !current_window.findById('EPSEF_EvnStickPanel').hidden ) {
							current_window.findById('EPSEF_EvnStickPanel').toggleCollapse();
						}
						break;

					case Ext.EventObject.NUM_SEVEN:
					case Ext.EventObject.SEVEN:
						if ( !current_window.findById('EPSEF_EvnUslugaPanel').hidden ) {
							current_window.findById('EPSEF_EvnUslugaPanel').toggleCollapse();
						}
						break;

					case Ext.EventObject.EIGHT:
					case Ext.EventObject.NUM_EIGHT:
						if ( !current_window.findById('EPSEF_EvnDrugPanel').hidden ) {
							current_window.findById('EPSEF_EvnDrugPanel').toggleCollapse();
						}
						break;
				}
			},
			key: [
				Ext.EventObject.C,
				Ext.EventObject.EIGHT,
				Ext.EventObject.G,
				Ext.EventObject.FOUR,
				Ext.EventObject.FIVE,
				Ext.EventObject.J,
				Ext.EventObject.NUM_EIGHT,
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
				Ext.EventObject.THREE
			],
			stopEvent: true,
			scope: this
		};
		this.keyHandler = {
			alt: false,
			fn: function(inp, e) {
				var current_window = this;

				switch ( e.getKey() ) {
					case Ext.EventObject.F6:
						current_window.findById('EPSEF_PersonInformationFrame').panelButtonClick(1);
						break;

					case Ext.EventObject.F10:
						current_window.findById('EPSEF_PersonInformationFrame').panelButtonClick(2);
						break;

					case Ext.EventObject.F11:
						current_window.findById('EPSEF_PersonInformationFrame').panelButtonClick(3);
						break;

					case Ext.EventObject.F12:
						if ( e.ctrlKey == true ) {
							current_window.findById('EPSEF_PersonInformationFrame').panelButtonClick(5);
						}
						else {
							current_window.findById('EPSEF_PersonInformationFrame').panelButtonClick(4);
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
			stopEvent: true,
			scope: this
		};
		Ext.apply(this, {
			keys: [this.keyHandlerAlt, this.keyHandler],
			buttons: [{
				handler: function() {
					this.doSave();
				}.createDelegate(this),
				iconCls: 'save16',
				onShiftTabAction: function() {
					var base_form = this.findById('EvnPSEditForm').getForm();

					if ( !this.findById('EPSEF_EvnUslugaPanel').collapsed && this.findById('EPSEF_EvnUslugaGrid').getStore().getCount() > 0 ) {
						this.findById('EPSEF_EvnUslugaGrid').getView().focusRow(0);
						this.findById('EPSEF_EvnUslugaGrid').getSelectionModel().selectFirstRow();
					}
					else if ( !this.findById('EPSEF_EvnStickPanel').collapsed && this.findById('EPSEF_EvnStickGrid').getStore().getCount() > 0 ) {
						this.findById('EPSEF_EvnStickGrid').getView().focusRow(0);
						this.findById('EPSEF_EvnStickGrid').getSelectionModel().selectFirstRow();
					}
					else if ( !this.findById('EPSEF_EvnSectionPanel').collapsed && this.findById('EPSEF_EvnSectionGrid').getStore().getCount() > 0 ) {
						this.findById('EPSEF_EvnSectionGrid').getView().focusRow(0);
						this.findById('EPSEF_EvnSectionGrid').getSelectionModel().selectFirstRow();
					}
					else if ( !this.findById('EPSEF_AdmitDiagPanel').collapsed && this.findById('EPSEF_EvnDiagPSRecepGrid').getStore().getCount() > 0 ) {
						this.findById('EPSEF_EvnDiagPSRecepGrid').getView().focusRow(0);
						this.findById('EPSEF_EvnDiagPSRecepGrid').getSelectionModel().selectFirstRow();
					}
					else if ( !this.findById('EPSEF_AdmitDepartPanel').collapsed && this.action != 'view' ) {
						if ( !base_form.findField('Diag_pid').disabled ) {
							base_form.findField('Diag_pid').focus(true);
						}
						else {
							base_form.findField('MedStaffFact_pid').focus(true);
						}
					}
					else if ( !this.findById('EPSEF_DirectDiagPanel').collapsed && this.findById('EPSEF_EvnDiagPSHospGrid').getStore().getCount() > 0 ) {
						this.findById('EPSEF_EvnDiagPSHospGrid').getView().focusRow(0);
						this.findById('EPSEF_EvnDiagPSHospGrid').getSelectionModel().selectFirstRow();
					}
					else if ( !this.findById('EPSEF_HospitalisationPanel').collapsed && this.action != 'view' ) {
						base_form.findField('EvnPS_IsDiagMismatch').focus(true);
					}
					else {
						this.buttons[this.buttons.length - 1].focus();
					}
				}.createDelegate(this),
				onTabAction: function() {
					this.buttons[1].focus();
				}.createDelegate(this),
				tabIndex: this.tabindex + 36,
				text: BTN_FRMSAVE
			}, /*{ //Скрываем, ненужную на данный момент, кнопку http://redmine.swan.perm.ru/issues/23282
				handler: function() {
					// Надо передать с формы: EvnPS_id, scope, button, callback
					var base_form = this.findById('EvnPSEditForm').getForm();
					var config = {};
					config.Evn_id = base_form.findField('EvnPS_id').getValue();
					config.Evn_IsSigned = base_form.findField('EvnPS_IsSigned').getValue();
					config.scope = this;
					config.callback = function(success) {
						if (success) {
							/*
							 this.setTitle(WND_HOSP_EPSVIEW);
							 this.enableEdit(false);
							 */
							/*var btn = Ext.getCmp(this.id+'_BtnSign');
							if (isSuperAdmin()) {
								btn.setText(lang['otmenit_podpis']);
							} else {
								btn.disable();
								btn.setText(lang['dokument_podpisan']);
							}*/
							/*if (this.childPS) {
							 this.callback({evnPSData: response}, {opener: this.opener});
							 } else {
							 this.callback({ evnPSData: response });
							 }
							this.hide();
						}
					}.createDelegate(this);

					 config.button = ;

					//log(config);
					signedDocument(config);
				}.createDelegate(this),
				id: this.id+'_BtnSign',
				iconCls: 'digital-sign16',
				tabIndex: this.tabindex + 37,
				text: BTN_FRMSIGN
			},*/ {
				handler: function() {
					this.printEvnPS();
				}.createDelegate(this),
				iconCls: 'print16',
				onShiftTabAction: function() {
					var base_form = this.findById('EvnPSEditForm').getForm();

					if ( this.action != 'view' ) {
						this.buttons[0].focus();
					}
					else if ( !this.findById('EPSEF_EvnUslugaPanel').collapsed && this.findById('EPSEF_EvnUslugaGrid').getStore().getCount() > 0 ) {
						this.findById('EPSEF_EvnUslugaGrid').getView().focusRow(0);
						this.findById('EPSEF_EvnUslugaGrid').getSelectionModel().selectFirstRow();
					}
					else if ( !this.findById('EPSEF_EvnStickPanel').collapsed && this.findById('EPSEF_EvnStickGrid').getStore().getCount() > 0 ) {
						this.findById('EPSEF_EvnStickGrid').getView().focusRow(0);
						this.findById('EPSEF_EvnStickGrid').getSelectionModel().selectFirstRow();
					}
					else if ( !this.findById('EPSEF_EvnSectionPanel').collapsed && this.findById('EPSEF_EvnSectionGrid').getStore().getCount() > 0 ) {
						this.findById('EPSEF_EvnSectionGrid').getView().focusRow(0);
						this.findById('EPSEF_EvnSectionGrid').getSelectionModel().selectFirstRow();
					}
					else if ( !this.findById('EPSEF_AdmitDiagPanel').collapsed && this.findById('EPSEF_EvnDiagPSRecepGrid').getStore().getCount() > 0 ) {
						this.findById('EPSEF_EvnDiagPSRecepGrid').getView().focusRow(0);
						this.findById('EPSEF_EvnDiagPSRecepGrid').getSelectionModel().selectFirstRow();
					}
					else if ( !this.findById('EPSEF_DirectDiagPanel').collapsed && this.findById('EPSEF_EvnDiagPSHospGrid').getStore().getCount() > 0 ) {
						this.findById('EPSEF_EvnDiagPSHospGrid').getView().focusRow(0);
						this.findById('EPSEF_EvnDiagPSHospGrid').getSelectionModel().selectFirstRow();
					}
					else {
						this.buttons[this.buttons.length - 1].focus();
					}
				}.createDelegate(this),
				onTabAction: function() {
					this.buttons[this.buttons.length - 1].focus();
				}.createDelegate(this),
				tabIndex: this.tabindex + 37,
				text: BTN_FRMPRINT
			},
			{
				text: '-'
			},
				HelpButton(this, -1),
				{
					handler: function() {
						this.onCancelAction();
					}/*.createDelegate(this)*/,
					scope: this,
					iconCls: 'cancel16',
					onShiftTabAction: function () {
						this.buttons[1].focus();
					}.createDelegate(this),
					onTabAction: function() {
						var base_form = this.findById('EvnPSEditForm').getForm();
						if ( !this.findById('EPSEF_HospitalisationPanel').collapsed && this.action != 'view' ) {
							base_form.findField('EvnPS_IsCont').focus(true);
						}
						else if ( !this.findById('EPSEF_DirectDiagPanel').collapsed && this.findById('EPSEF_EvnDiagPSHospGrid').getStore().getCount() > 0 ) {
							this.findById('EPSEF_EvnDiagPSHospGrid').getView().focusRow(0);
							this.findById('EPSEF_EvnDiagPSHospGrid').getSelectionModel().selectFirstRow();
						}
						else if ( !this.findById('EPSEF_AdmitDepartPanel').collapsed && this.action != 'view' ) {
							base_form.findField('PrehospToxic_id').focus(true);
						}
						else if ( !this.findById('EPSEF_AdmitDiagPanel').collapsed && this.findById('EPSEF_EvnDiagPSRecepGrid').getStore().getCount() > 0 ) {
							this.findById('EPSEF_EvnDiagPSRecepGrid').getView().focusRow(0);
							this.findById('EPSEF_EvnDiagPSRecepGrid').getSelectionModel().selectFirstRow();
						}
						else if ( !this.findById('EPSEF_EvnSectionPanel').collapsed && this.findById('EPSEF_EvnSectionGrid').getStore().getCount() > 0 ) {
							this.findById('EPSEF_EvnSectionGrid').getView().focusRow(0);
							this.findById('EPSEF_EvnSectionGrid').getSelectionModel().selectFirstRow();
						}
						else if ( !this.findById('EPSEF_EvnStickPanel').collapsed && this.findById('EPSEF_EvnStickGrid').getStore().getCount() > 0 ) {
							this.findById('EPSEF_EvnStickGrid').getView().focusRow(0);
							this.findById('EPSEF_EvnStickGrid').getSelectionModel().selectFirstRow();
						}
						else if ( !this.findById('EPSEF_EvnUslugaPanel').collapsed && this.findById('EPSEF_EvnUslugaGrid').getStore().getCount() > 0 ) {
							this.findById('EPSEF_EvnUslugaGrid').getView().focusRow(0);
							this.findById('EPSEF_EvnUslugaGrid').getSelectionModel().selectFirstRow();
						}
						else if ( this.action != 'view' ) {
							this.buttons[0].focus();
						}
						else {
							this.buttons[1].focus();
						}
					}.createDelegate(this),
					tabIndex: this.tabindex + 38,
					text: BTN_FRMCANCEL
				}],
			items: [ new sw.Promed.PersonInfoPanel({
				button1OnHide: function() {
					if ( this.action == 'view' ) {
						this.buttons[this.buttons.length - 1].focus();
					}
					else {
						this.findById('EvnPSEditForm').getForm().findField('EvnPS_NumCard').focus(true);
					}
				}.createDelegate(this),
				button2Callback: function(callback_data) {
					var base_form = this.findById('EvnPSEditForm').getForm();
					this.findById('EPSEF_PersonInformationFrame').load({
						Person_id: base_form.findField('Person_id').getValue(),
						Server_id: base_form.findField('Server_id').getValue()
					});
				}.createDelegate(this),
				button2OnHide: function() {
					this.findById('EPSEF_PersonInformationFrame').button1OnHide();
				}.createDelegate(this),
				button3OnHide: function() {
					this.findById('EPSEF_PersonInformationFrame').button1OnHide();
				}.createDelegate(this),
				button4OnHide: function() {
					this.findById('EPSEF_PersonInformationFrame').button1OnHide();
				}.createDelegate(this),
				button5OnHide: function() {
					this.findById('EPSEF_PersonInformationFrame').button1OnHide();
				}.createDelegate(this),
				collapsible: true,
				collapsed: true,
				floatable: false,
				id: 'EPSEF_PersonInformationFrame',
				plugins: [ Ext.ux.PanelCollapsedTitle ],
				region: 'north',
				title: lang['zagruzka'],
				titleCollapse: true
			}),
				new Ext.form.FormPanel({
					autoScroll: true,
					bodyBorder: false,
					bodyStyle: 'padding: 5px 5px 0',
					border: false,
					frame: false,
					id: 'EvnPSEditForm',
					labelAlign: 'right',
					labelWidth: 180,
					items: [
						{
						name: 'accessType',
						value: '',
						xtype: 'hidden'
					}, {
						name:'Lpu_id',
						xtype:'hidden'
					}, {
						name: 'EvnPS_id',
						value: 0,
						xtype: 'hidden'
					},
						/*{
						name: 'EvnPS_IsSigned',
						value: null,
						xtype: 'hidden'
					},*/
						{
						name: 'EvnPS_IsPrehospAcceptRefuse',
						value: null,
						xtype: 'hidden'
					},
						{
						name: 'EvnPS_PrehospAcceptRefuseDT',
						value: null,
						xtype: 'hidden'
					},
						{
						name: 'EvnPS_PrehospWaifRefuseDT',
						value: null,
						xtype: 'hidden'
					},
						{
						name: 'EvnDirection_id',
						value: 0,
						xtype: 'hidden'
					},
						{
						name: 'EvnQueue_id',
						value: 0,
						xtype: 'hidden'
					},
						{
						name: 'PrehospStatus_id',
						value: 0,
						xtype: 'hidden'
					},
						{
						name: 'Person_id',
						value: 0,
						xtype: 'hidden'
					},
						{
						name: 'PersonEvn_id',
						value: 0,
						xtype: 'hidden'
					},
						{
						name: 'Server_id',
						value: -1,
						xtype: 'hidden'
					}, /*{
					 name: 'LpuSection_id',
					 value: 0,
					 xtype: 'hidden'
					 }, {
					 name: 'PrehospWaifRefuseCause_id',
					 value: 0,
					 xtype: 'hidden'
					 }, {
					 name: 'EvnPS_IsTransfCall',
					 value: 0,
					 xtype: 'hidden'
					 }, {
					 name: 'EvnPS_IsWaif',
					 value: 0,
					 xtype: 'hidden'
					 }, {
					 name: 'PrehospWaifArrive_id',
					 value: 0,
					 xtype: 'hidden'
					 }, {
					 name: 'PrehospWaifReason_id',
					 value: 0,
					 xtype: 'hidden'
					 },*/
						new sw.Promed.Panel({
							autoHeight: true,
							bodyStyle: 'padding-top: 0.5em;',
							border: true,
							collapsible: true,
							id: 'EPSEF_HospitalisationPanel',
							layout: 'form',
							listeners: {
								'expand': function(panel) {
									// this.findById('EvnPSEditForm').getForm().findField('EvnPS_IsCont').focus(true);
								}.createDelegate(this)
							},
							style: 'margin-bottom: 0.5em;',
							title: lang['1_gospitalizatsiya'],
							items: [
								{
								allowBlank: false,
								fieldLabel: lang['pereveden'],
								hiddenName: 'EvnPS_IsCont',
								listeners: {
									'change': function(combo, newValue, oldValue) {
										var base_form = this.findById('EvnPSEditForm').getForm();
										var prehosp_direct_field = base_form.findField('PrehospDirect_id');
										var iswd_combo = base_form.findField('EvnPS_IsWithoutDirection');
										var diag_did_field = base_form.findField('Diag_did');
										var record = combo.getStore().getById(newValue);

										var prehosp_direct_id = prehosp_direct_field.getValue();

										prehosp_direct_field.clearValue();
										prehosp_direct_field.getStore().clearFilter();
										diag_did_field.clearValue();

										if ( record ) {
											switch ( Number(record.get('YesNo_Code')) ) {
												case 0:
													iswd_combo.setDisabled( this.action == 'view' );

													base_form.findField('EvnPS_IsImperHosp').setAllowBlank(false);
													base_form.findField('EvnPS_IsShortVolume').setAllowBlank(false);
													base_form.findField('EvnPS_IsWrongCure').setAllowBlank(false);
													base_form.findField('EvnPS_IsDiagMismatch').setAllowBlank(false);
													if ( this.action == 'add' ) {
														base_form.findField('LpuSection_pid').setAllowBlank(true);
													}
												break;

												case 1: //yes
													base_form.findField('EvnPS_IsImperHosp').setAllowBlank(true);
													base_form.findField('EvnPS_IsShortVolume').setAllowBlank(true);
													base_form.findField('EvnPS_IsWrongCure').setAllowBlank(true);
													base_form.findField('EvnPS_IsDiagMismatch').setAllowBlank(true);
													base_form.findField('LpuSection_pid').setAllowBlank(true);

													iswd_combo.setValue(1);
													iswd_combo.disable();
												break;
											}
										}

										base_form.findField('EvnPS_setDate').fireEvent('change', base_form.findField('EvnPS_setDate'), base_form.findField('EvnPS_setDate').getValue());
										iswd_combo.fireEvent('change', iswd_combo, iswd_combo.getValue());
									}.createDelegate(this),
									'keydown': function(inp, e) {
										if ( e.getKey() == Ext.EventObject.TAB && e.shiftKey == true ) {
											e.stopEvent();
											this.buttons[this.buttons.length - 1].focus();
										}
									}.createDelegate(this)
								},
								tabIndex: this.tabindex + 1,
								value: 1,
								width: 70,
								xtype: 'swyesnocombo'
							},
								{
								allowBlank: false,
								autoCreate: { tag: "input", type: "text", maxLength: "50", autocomplete: "off" },
								enableKeyEvents: true,
								fieldLabel: lang['№_meditsinskoy_kartyi'],
								listeners: {
									'keydown': function(inp, e) {
										switch ( e.getKey() ) {
											case Ext.EventObject.F4:
												e.stopEvent();
												this.getEvnPSNumber();
												break;
										}
									}.createDelegate(this)
								},
								maxLength: 50,
								name: 'EvnPS_NumCard',
								onTriggerClick: function() {
									this.getEvnPSNumber();
								}.createDelegate(this),
								tabIndex: this.tabindex + 2,
								triggerClass: 'x-form-plus-trigger',
								validateOnBlur: false,
								width: 300,
								xtype: 'trigger'
							},
								{
								allowBlank: true,
								//disabled: true,
								useCommonFilter: true,
								fieldLabel: 'Источник финансирования',
								tabIndex: this.tabindex + 3,
								width: 300,
								xtype: 'swpaytypecombo',
								listeners: {
									'change': function(field, newValue, oldValue) {
										this.setBedListAllowBlank();
									}.createDelegate(this)
								}
							},
								{
								border: false,
								layout: 'column',
								items: [
									{
									border: false,
									layout: 'form',
									items: [{
										allowBlank: false,
										fieldLabel: lang['data_postupleniya'],
										format: 'd.m.Y',
										id: this.id + 'EPSEF_EvnPS_setDate',
										listeners: {
											'change': function(field, newValue, oldValue) {
												if (blockedDateAfterPersonDeath('personpanelid', 'EPSEF_PersonInformationFrame', field, newValue, oldValue)) return;
												var base_form = this.findById('EvnPSEditForm').getForm();

												var lpu_section_did = base_form.findField('LpuSection_did').getValue();
												var lpu_section_pid = base_form.findField('LpuSection_pid').getValue();
												var med_staff_fact_pid = base_form.findField('MedStaffFact_pid').getValue();

												base_form.findField('LpuSection_did').clearValue();
												base_form.findField('LpuSection_pid').clearValue();
												base_form.findField('MedStaffFact_pid').clearValue();

												var WithoutChildLpuSectionAge = false;
												var Person_Birthday = this.findById('EPSEF_PersonInformationFrame').getFieldValue('Person_Birthday');
												
												if ( !newValue ) {
													setLpuSectionGlobalStoreFilter({
														isStac: (base_form.findField('EvnPS_IsCont').getValue() == 2)
													});
													base_form.findField('LpuSection_did').getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));
													
													var age = swGetPersonAge(Person_Birthday, new Date());
													if (age >= 18) {
														WithoutChildLpuSectionAge = true;
													}
												
													setLpuSectionGlobalStoreFilter({
														isStacReception: true,
														WithoutChildLpuSectionAge: WithoutChildLpuSectionAge
													});
													base_form.findField('LpuSection_pid').getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));

													setMedStaffFactGlobalStoreFilter({
														isStac: true,
														isPriemMedPers: true
													});
													base_form.findField('MedStaffFact_pid').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));
												}
												else {
													setLpuSectionGlobalStoreFilter({
														isStac: (base_form.findField('EvnPS_IsCont').getValue() == 2),
														onDate: Ext.util.Format.date(newValue, 'd.m.Y')
													});
													base_form.findField('LpuSection_did').getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));
													
													var age = swGetPersonAge(Person_Birthday, newValue);
													if (age >= 18) {
														WithoutChildLpuSectionAge = true;
													}
													
													setLpuSectionGlobalStoreFilter({
														isStacReception: true,
														onDate: Ext.util.Format.date(newValue, 'd.m.Y'),
														WithoutChildLpuSectionAge: WithoutChildLpuSectionAge
													});
													base_form.findField('LpuSection_pid').getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));

													setMedStaffFactGlobalStoreFilter({
														isStac: true,
														isPriemMedPers: true,
														onDate: Ext.util.Format.date(newValue, 'd.m.Y')
													});
													base_form.findField('MedStaffFact_pid').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));
													this.setMKB();
											}

												if ( base_form.findField('LpuSection_did').getStore().getById(lpu_section_did) ) {
													base_form.findField('LpuSection_did').setValue(lpu_section_did);
												}

												if ( base_form.findField('LpuSection_pid').getStore().getById(lpu_section_pid) ) {
													base_form.findField('LpuSection_pid').setValue(lpu_section_pid);
													base_form.findField('LpuSection_pid').fireEvent('change', base_form.findField('LpuSection_pid'), lpu_section_pid);
												} else {
													base_form.findField('LpuSection_pid').fireEvent('change', base_form.findField('LpuSection_pid'), null);
												}

												if ( base_form.findField('MedStaffFact_pid').getStore().getById(med_staff_fact_pid) ) {
													base_form.findField('MedStaffFact_pid').setValue(med_staff_fact_pid);
													base_form.findField('MedStaffFact_pid').fireEvent('change', base_form.findField('MedStaffFact_pid'), base_form.findField('MedStaffFact_pid').getValue());
												}

												// Если дата госпитализации пустая или дата направления больше даты госпитализации,
												// то очищаем данные по направлению
												if ( !newValue || base_form.findField('EvnDirection_setDate').getValue() > newValue ) {
													base_form.findField('EvnDirection_id').setValue(0);
													base_form.findField('EvnDirection_Num').setValue('');
													base_form.findField('EvnDirection_setDate').setRawValue('');
													base_form.findField('LpuSection_did').clearValue();
													base_form.findField('Org_did').clearValue();
													base_form.findField('Diag_did').clearValue();
												}

												base_form.findField('Diag_pid').setFilterByDate(newValue);
												this.refreshFieldsVisibility(['DiagSetPhase_did', 'DiagSetPhase_pid']);
											}.createDelegate(this)
										},
										name: 'EvnPS_setDate',
										plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
										selectOnFocus: true,
										tabIndex: this.tabindex + 4,
										width: 100,
										xtype: 'swdatefield'
									}]
								}, {
									border: false,
									labelWidth: 50,
									layout: 'form',
									items: [{
										allowBlank: false,
										fieldLabel: lang['vremya'],
										listeners: {
											'keydown': function (inp, e) {
												if ( e.getKey() == Ext.EventObject.F4 ) {
													e.stopEvent();
													inp.onTriggerClick();
												}
											}
										},
										name: 'EvnPS_setTime',
										onTriggerClick: function() {
											var base_form = this.findById('EvnPSEditForm').getForm();
											var time_field = base_form.findField('EvnPS_setTime');

											if ( time_field.disabled ) {
												return false;
											}

											setCurrentDateTime({
												dateField: base_form.findField('EvnPS_setDate'),
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
										tabIndex: this.tabindex + 5,
										validateOnBlur: false,
										width: 60,
										xtype: 'swtimefield'
									}]
								}]
							},
								{
								autoHeight: true,
								style: 'padding: 0px;',
								title: lang['kem_napravlen'],
								width: 730,
								xtype: 'fieldset',

								items: [{
									border: false,
									layout: 'column',
									items: [{
										border: false,
										layout: 'form',
										width: 300,
										items: [ new sw.Promed.SwYesNoCombo({
											fieldLabel: lang['s_elektronnyim_napravleniem'],
											hiddenName: 'EvnPS_IsWithoutDirection',
											value: 2,
											allowBlank: false,
											tabIndex: this.tabindex + 5,
											width: 60,
											listeners:
											{
												'change': function (iswd_combo, newValue, oldValue)
												{
													var base_form = this.findById('EvnPSEditForm').getForm();
													if ( newValue == 2 ) {
														// поля заполняются из эл.направления
														base_form.findField('EvnDirection_Num').disable();
														base_form.findField('EvnDirection_setDate').disable();
														base_form.findField('Org_did').disable();
														base_form.findField('Diag_did').disable();
														base_form.findField('PayType_id').disable();
														base_form.findField('PrehospDirect_id').fireEvent('change', base_form.findField('PrehospDirect_id'), base_form.findField('PrehospDirect_id').getValue());
													}
													else {
														base_form.findField('EvnDirection_Num').setDisabled( this.action == 'view' );
														base_form.findField('EvnDirection_setDate').setDisabled( this.action == 'view' );
														base_form.findField('Org_did').setDisabled( this.action == 'view' );
														base_form.findField('Diag_did').setDisabled( this.action == 'view' );
														base_form.findField('PayType_id').setDisabled( this.action == 'view' );
														base_form.findField('PrehospDirect_id').fireEvent('change', base_form.findField('PrehospDirect_id'), base_form.findField('PrehospDirect_id').getValue());
													}
												}.createDelegate(this),
												'select': function(combo, record, index) {
													combo.fireEvent('change', combo, record.get(combo.valueField));
												}.createDelegate(this)
											}
										})]
									}, {
										border: false,
										layout: 'form',
										width: 200,
										items: [{
											handler: function() {
												this.openEvnDirectionSelectWindow();
											}.createDelegate(this),
											iconCls: 'add16',
											id: 'EPSEF_EvnDirectionSelectButton',
											tabIndex: this.tabindex + 6,
											text: lang['vyibrat_napravlenie'],
											tooltip: lang['vyibor_napravleniya'],
											xtype: 'button'
										}]
									}]
								}, {
									hiddenName: 'PrehospDirect_id',
									lastQuery: '',
									listeners: {
										'change': function(combo, newValue, oldValue) {
											var base_form = this.findById('EvnPSEditForm').getForm();

											var
												evn_direction_set_date_field = base_form.findField('EvnDirection_setDate'),
												evn_direction_num_field = base_form.findField('EvnDirection_Num'),
												lpu_section_combo = base_form.findField('LpuSection_did'),
												org_combo = base_form.findField('Org_did'),
												iswd_combo = base_form.findField('EvnPS_IsWithoutDirection');

											var lpu_section_id = lpu_section_combo.getValue();

											base_form.findField('EvnDirection_id').setValue(0);
											evn_direction_set_date_field.setValue(null);
											evn_direction_num_field.setValue(null);
											lpu_section_combo.clearValue();
											org_combo.clearValue();

											base_form.findField('Diag_did').setAllowBlank(true);
											base_form.findField('Diag_pid').setAllowBlank(true);

											if ( this.action != 'add' ) {
												base_form.findField('LpuSection_pid').setAllowBlank(true);
											}

											base_form.findField('MedStaffFact_pid').setAllowBlank(true);

											var record = combo.getStore().getById(newValue);

											var prehosp_direct_code = (typeof record == 'object' && record.get('PrehospDirect_Code') ? record.get('PrehospDirect_Code') : null);
											
											//evn_direction_set_date_field.disable();
											//evn_direction_num_field.disable()
											lpu_section_combo.disable();
											lpu_section_combo.setAllowBlank(true);
											org_combo.disable();

											if ( Ext.isEmpty(prehosp_direct_code) ) {
												return false;
											}

											switch ( Number(prehosp_direct_code) ) {
												case 1:
												case 2:
												case 3:
												case 4:
												case 5:
												case 6:
													evn_direction_set_date_field.setDisabled(this.action == 'view');
													evn_direction_num_field.setDisabled(this.action == 'view');
													org_combo.setDisabled(this.action == 'view');
													org_combo.setAllowBlank(false);
												break;

												default:
													//evn_direction_set_date_field.disable();
													//evn_direction_num_field.setDisabled(this.action == 'view');
													org_combo.disable();
													org_combo.setAllowBlank(true);
												break;
											}

											if (2 == Number(iswd_combo.getValue())) {
												evn_direction_set_date_field.disable();
												evn_direction_num_field.disable()
												org_combo.disable();
												org_combo.setAllowBlank(true);
											}
										}.createDelegate(this),
										'select': function(combo, record, index) {
											combo.fireEvent('change', combo, record.get(combo.valueField));
										}.createDelegate(this)
									},
									tabIndex: this.tabindex + 7,
									width: 300,
									xtype: 'swprehospdirectcombo'
								}, {
									disabled: true,
									hiddenName: 'LpuSection_did',
									tabIndex: this.tabindex + 8,
									width: 500,
									xtype: 'swlpusectionglobalcombo'
								}, {
									displayField: 'Org_Name',
									editable: false,
									enableKeyEvents: true,
									fieldLabel: lang['organizatsiya'],
									hiddenName: 'Org_did',
									listeners: {
										'keydown': function( inp, e ) {
											if ( inp.disabled )
												return;

											if ( e.F4 == e.getKey() ) {
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

												inp.onTrigger1Click();
												return false;
											}
										},
										'keyup': function(inp, e) {
											if ( e.F4 == e.getKey() ) {
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

												return false;
											}
										}
									},
									mode: 'local',
									onTrigger1Click: function() {
										var base_form = this.findById('EvnPSEditForm').getForm();
										var combo = base_form.findField('Org_did');

										if ( combo.disabled ) {
											return false;
										}

										var prehosp_direct_combo = base_form.findField('PrehospDirect_id');
										var prehosp_direct_id = prehosp_direct_combo.getValue();
										var record = prehosp_direct_combo.getStore().getById(prehosp_direct_id);

										if ( !record ) {
											return false;
										}

										var prehosp_direct_code = record.get('PrehospDirect_Code');
										var org_type = '';

										switch ( prehosp_direct_code ) {
											case 1:
											case 2:
											case 3:
											case 6:
												org_type = 'org';
											break;

											case 4:
												org_type = 'lpu';
											break;

											case 5:
												org_type = 'military';
											break;

											default:
												return false;
											break;
										}

										getWnd('swOrgSearchWindow').show({
											object: org_type,
											onClose: function() {
												combo.focus(true, 200)
											},
											onSelect: function(org_data) {
												if ( org_data.Org_id > 0 ) {
													combo.getStore().loadData([{
														Org_id: org_data.Org_id,
														Org_Name: org_data.Org_Name
													}]);
													combo.setValue(org_data.Org_id);
													getWnd('swOrgSearchWindow').hide();
													combo.collapse();
												}
											}
										});
									}.createDelegate(this),
									store: new Ext.data.JsonStore({
										autoLoad: false,
										fields: [
											{name: 'Org_id', type: 'int'},
											{name: 'Org_Name', type: 'string'}
										],
										key: 'Org_id',
										sortInfo: {
											field: 'Org_Name'
										},
										url: C_ORG_LIST
									}),
									tabIndex: this.tabindex + 9,
									tpl: new Ext.XTemplate(
										'<tpl for="."><div class="x-combo-list-item">',
										'{Org_Name}',
										'</div></tpl>'
									),
									trigger1Class: 'x-form-search-trigger',
									triggerAction: 'none',
									valueField: 'Org_id',
									width: 500,
									xtype: 'swbaseremotecombo'
								}, {
									border: false,
									layout: 'column',
									items: [{
										border: false,
										layout: 'form',
										items: [{
											fieldLabel: lang['№_napravleniya'],
											name: 'EvnDirection_Num',
											tabIndex: this.tabindex + 10,
											width: 150,
											autoCreate: {tag: "input", type: "text", maxLength: "16", autocomplete: "off"},
											xtype: 'numberfield'
										}]
									}, {
										border: false,
										labelWidth: 200,
										layout: 'form',
										items: [{
											fieldLabel: lang['data_napravleniya'],
											format: 'd.m.Y',
											name: 'EvnDirection_setDate',
											plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
											tabIndex: this.tabindex + 11,
											listeners: {
												'change': function (combo, newValue, oldValue) {
													var base_form = win.findById('EvnPSEditForm').getForm();
													base_form.findField('Diag_did').setFilterByDate(newValue);
												}
											},
											width: 100,
											xtype: 'swdatefield'
										}]
									}]
								}]
							},
								{
								autoHeight: true,
								style: 'padding: 0px;',
								title: lang['kem_dostavlen'],
								width: 730,
								xtype: 'fieldset',

								items: [{
									fieldLabel: lang['kem_dostavlen'],
									hiddenName: 'PrehospArrive_id',
									listeners: {
										'change': function(combo, newValue, oldValue) {
											var base_form = this.findById('EvnPSEditForm').getForm();

											base_form.findField('EvnPS_CodeConv').setValue('');
											base_form.findField('EvnPS_NumConv').setValue('');
											if ( this.action == 'add' )
												base_form.findField('EvnPS_IsPLAmbulance').setValue(1);

											var record = combo.getStore().getById(newValue);

											if ( !record || record.get('PrehospArrive_Code') == 1 ) {
												base_form.findField('EvnPS_CodeConv').disable();
												base_form.findField('EvnPS_NumConv').disable();
												base_form.findField('EvnPS_IsPLAmbulance').disable();
											}
											else if ( record.get('PrehospArrive_Code') == 2 ) {
												base_form.findField('EvnPS_CodeConv').setDisabled( this.action == 'view' );
												base_form.findField('EvnPS_NumConv').setDisabled( this.action == 'view' );
												base_form.findField('EvnPS_IsPLAmbulance').setDisabled( this.action == 'view' );
												if ( this.action == 'add' && base_form.findField('PrehospDirect_id').getFieldValue('PrehospDirect_Code') == 3 ) {
													base_form.findField('EvnPS_IsPLAmbulance').setValue(2);
												}
											}
											else {
												base_form.findField('EvnPS_CodeConv').setDisabled( this.action == 'view' );
												base_form.findField('EvnPS_NumConv').setDisabled( this.action == 'view' );
												base_form.findField('EvnPS_IsPLAmbulance').disable();
											}
										}.createDelegate(this)
									},
									tabIndex: this.tabindex + 12,
									width: 300,
									xtype: 'swprehosparrivecombo'
								}, {
									fieldLabel: lang['kod'],
									maxLength: 10,
									name: 'EvnPS_CodeConv',
									tabIndex: this.tabindex + 13,
									width: 150,
									xtype: 'textfield'
								}, {
									fieldLabel: lang['nomer_naryada'],
									maxLength: 10,
									name: 'EvnPS_NumConv',
									tabIndex: this.tabindex + 14,
									width: 150,
									xtype: 'textfield'
								},{
									comboSubject: 'YesNo',
									disabled: true,
									fieldLabel: lang['talon_peredan_na_ssmp'],
									hiddenName: 'EvnPS_IsPLAmbulance',
									tabIndex: this.tabindex + 15,
									width: 150,
									value: 1,
									xtype: 'swcommonsprcombo'
								}]
							},
								new sw.Promed.swDiagPanel({
								labelWidth: 180,
								phaseDescrName: 'EvnPS_PhaseDescr_did',
								diagSetPhaseName: 'DiagSetPhase_did',
								diagField: {
									checkAccessRights: true,
									// allowBlank: false,
									MKB:null,
									fieldLabel: lang['diagnoz_napr_uchr-ya'],
									hiddenName: 'Diag_did',
									id: 'EPSEF_DiagHospCombo',
									onChange: function(combo, newValue) {
										var base_form = this.findById('EvnPSEditForm').getForm();
										if ( !newValue ) {
											return true;
										}
										base_form.findField('LpuSection_pid').fireEvent('change', base_form.findField('LpuSection_pid'), base_form.findField('LpuSection_pid').getValue());
									}.createDelegate(this),
									tabIndex: this.tabindex + 16,
									width: 500,
									xtype: 'swdiagcombo',
								},
								diagPhase: {
									xtype: 'swdiagsetphasecombo',
									fieldLabel: 'Состояние пациента',
									hiddenName: 'DiagSetPhase_did',
									allowBlank: true,
									tabIndex: this.tabindex + 16,
									width: 500,
									editable: false
								}
							}),
								{
									autoHeight: true,
									labelWidth: 300,
									style: 'padding: 0px;',
									title: lang['defektyi_dogospitalnogo_etapa'],
									width: 730,
									xtype: 'fieldset',

									items: [{
										allowBlank: false,
										fieldLabel: lang['nesvoevremennost_gospitalizatsii'],
										hiddenName: 'EvnPS_IsImperHosp',
										tabIndex: this.tabindex + 17,
										value: 1,
										width: 100,
										xtype: 'swyesnocombo'
									}, {
										allowBlank: false,
										fieldLabel: lang['nedost_obyem_kliniko-diag_obsledovaniya'],
										hiddenName: 'EvnPS_IsShortVolume',
										tabIndex: this.tabindex + 18,
										value: 1,
										width: 100,
										xtype: 'swyesnocombo'
									}, {
										allowBlank: false,
										fieldLabel: lang['nepravilnaya_taktika_lecheniya'],
										hiddenName: 'EvnPS_IsWrongCure',
										tabIndex: this.tabindex + 19,
										value: 1,
										width: 100,
										xtype: 'swyesnocombo'
									}, {
										allowBlank: false,
										fieldLabel: lang['nesovpadenie_diagnoza'],
										hiddenName: 'EvnPS_IsDiagMismatch',
										listeners: {
											'keydown': function(inp, e) {
												var base_form = this.findById('EvnPSEditForm').getForm();

												if ( e.getKey() == Ext.EventObject.TAB && e.shiftKey == false ) {
													e.stopEvent();

													if ( !this.findById('EPSEF_DirectDiagPanel').collapsed && this.findById('EPSEF_EvnDiagPSHospGrid').getStore().getCount() > 0 ) {
														this.findById('EPSEF_EvnDiagPSHospGrid').getView().focusRow(0);
														this.findById('EPSEF_EvnDiagPSHospGrid').getSelectionModel().selectFirstRow();
													}
													else if ( !this.findById('EPSEF_AdmitDepartPanel').collapsed && this.action != 'view' ) {
														base_form.findField('PrehospToxic_id').focus(true);
													}
													else if ( !this.findById('EPSEF_AdmitDiagPanel').collapsed && this.findById('EPSEF_EvnDiagPSRecepGrid').getStore().getCount() > 0 ) {
														this.findById('EPSEF_EvnDiagPSRecepGrid').getView().focusRow(0);
														this.findById('EPSEF_EvnDiagPSRecepGrid').getSelectionModel().selectFirstRow();
													}
													else if ( !this.findById('EPSEF_EvnSectionPanel').collapsed && this.findById('EPSEF_EvnSectionGrid').getStore().getCount() > 0 ) {
														this.findById('EPSEF_EvnSectionGrid').getView().focusRow(0);
														this.findById('EPSEF_EvnSectionGrid').getSelectionModel().selectFirstRow();
													}
													else if ( !this.findById('EPSEF_EvnStickPanel').collapsed && this.findById('EPSEF_EvnStickGrid').getStore().getCount() > 0 ) {
														this.findById('EPSEF_EvnStickGrid').getView().focusRow(0);
														this.findById('EPSEF_EvnStickGrid').getSelectionModel().selectFirstRow();
													}
													else if ( !this.findById('EPSEF_EvnUslugaPanel').collapsed && this.findById('EPSEF_EvnUslugaGrid').getStore().getCount() > 0 ) {
														this.findById('EPSEF_EvnUslugaGrid').getView().focusRow(0);
														this.findById('EPSEF_EvnUslugaGrid').getSelectionModel().selectFirstRow();
													}
													else if ( this.action != 'view' ) {
														this.buttons[0].focus();
													}
													else {
														this.buttons[1].focus();
													}
												}
											}.createDelegate(this)
										},
										tabIndex: this.tabindex + 20,
										value: 1,
										width: 100,
										xtype: 'swyesnocombo'
									}]
								}]
						}),
						new sw.Promed.Panel({
							border: true,
							collapsible: true,
							height: 125,
							id: 'EPSEF_DirectDiagPanel',
							isLoaded: false,
							layout: 'border',
							listeners: {
								'expand': function(panel) {
									if ( panel.isLoaded === false ) {
										panel.isLoaded = true;
										panel.findById('EPSEF_EvnDiagPSHospGrid').getStore().load({
											params: {
												'class': 'EvnDiagPSHosp',
												EvnDiagPS_pid: this.findById('EvnPSEditForm').getForm().findField('EvnPS_id').getValue()
											}
										});
									}

									panel.doLayout();
								}.createDelegate(this)
							},
							style: 'margin-bottom: 0.5em;',
							title: lang['2_soputstvuyuschie_diagnozyi_napravivshego_uchrejdeniya'],
							items: [ new Ext.grid.GridPanel({
								autoExpandColumn: 'autoexpand_diag_hosp',
								autoExpandMin: 100,
								border: false,
								columns: [{
									dataIndex: 'EvnDiagPS_setDate',
									header: lang['data'],
									hidden: false,
									renderer: Ext.util.Format.dateRenderer('d.m.Y'),
									resizable: false,
									sortable: true,
									width: 100
								}, {
									dataIndex: 'DiagSetClass_Name',
									header: lang['vid_diagnoza'],
									hidden: false,
									resizable: true,
									sortable: true,
									width: 200
								}, {
									dataIndex: 'Diag_Code',
									header: lang['kod_diagnoza'],
									hidden: false,
									resizable: true,
									sortable: true,
									width: 100
								}, {
									dataIndex: 'Diag_Name',
									header: lang['diagnoz'],
									hidden: false,
									id: 'autoexpand_diag_hosp',
									resizable: true,
									sortable: true
								}],
								frame: false,
								height: 200,
								id: 'EPSEF_EvnDiagPSHospGrid',
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

										var grid = this.findById('EPSEF_EvnDiagPSHospGrid');



										switch ( e.getKey() ) {
											case Ext.EventObject.DELETE:
												this.deleteEvent('EvnDiagPSHosp');
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

												this.openEvnDiagPSEditWindow(action, 'hosp');
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
												var base_form = this.findById('EvnPSEditForm').getForm();

												grid.getSelectionModel().clearSelections();
												grid.getSelectionModel().fireEvent('rowselect', grid.getSelectionModel());

												if ( e.shiftKey == false ) {
													if ( !this.findById('EPSEF_AdmitDepartPanel').collapsed && this.action != 'view' ) {
														base_form.findField('PrehospToxic_id').focus(true);
													}
													else if ( !this.findById('EPSEF_AdmitDiagPanel').collapsed && this.findById('EPSEF_EvnDiagPSRecepGrid').getStore().getCount() > 0 ) {
														this.findById('EPSEF_EvnDiagPSRecepGrid').getView().focusRow(0);
														this.findById('EPSEF_EvnDiagPSRecepGrid').getSelectionModel().selectFirstRow();
													}
													else if ( !this.findById('EPSEF_EvnSectionPanel').collapsed && this.findById('EPSEF_EvnSectionGrid').getStore().getCount() > 0 ) {
														this.findById('EPSEF_EvnSectionGrid').getView().focusRow(0);
														this.findById('EPSEF_EvnSectionGrid').getSelectionModel().selectFirstRow();
													}
													else if ( !this.findById('EPSEF_EvnStickPanel').collapsed && this.findById('EPSEF_EvnStickGrid').getStore().getCount() > 0 ) {
														this.findById('EPSEF_EvnStickGrid').getView().focusRow(0);
														this.findById('EPSEF_EvnStickGrid').getSelectionModel().selectFirstRow();
													}
													else if ( !this.findById('EPSEF_EvnUslugaPanel').collapsed && this.findById('EPSEF_EvnUslugaGrid').getStore().getCount() > 0 ) {
														this.findById('EPSEF_EvnUslugaGrid').getView().focusRow(0);
														this.findById('EPSEF_EvnUslugaGrid').getSelectionModel().selectFirstRow();
													}
													else if ( this.action != 'view' ) {
														this.buttons[0].focus();
													}
													else {
														this.buttons[1].focus();
													}
												}
												else {
													if ( !this.findById('EPSEF_HospitalisationPanel').collapsed && this.action != 'view' ) {
														base_form.findField('EvnPS_IsDiagMismatch').focus(true);
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
								listeners: {
									'rowdblclick': function(grid, number, obj) {
										this.openEvnDiagPSEditWindow('edit', 'hosp');
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
											var toolbar = this.findById('EPSEF_EvnDiagPSHospGrid').getTopToolbar();

											if ( selected_record ) {
												access_type = selected_record.get('accessType');
												id = selected_record.get('EvnDiagPS_id');
											}

											toolbar.items.items[1].disable();
											toolbar.items.items[3].disable();

											if ( id ) {
												toolbar.items.items[2].enable();

												if ( this.action != 'view' /*&& access_type == 'edit'*/ ) {
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
										'class': 'EvnDiagPSHosp'
									},
									listeners: {
										'load': function(store, records, index) {
											if ( store.getCount() == 0 ) {
												LoadEmptyRow(this.findById('EPSEF_EvnDiagPSHospGrid'));
											}

											// this.findById('EPSEF_EvnDiagPSHospGrid').getView().focusRow(0);
											// this.findById('EPSEF_EvnDiagPSHospGrid').getSelectionModel().selectFirstRow();
										}.createDelegate(this)
									},
									reader: new Ext.data.JsonReader({
										id: 'EvnDiagPS_id'
									}, [{
										mapping: 'EvnDiagPS_id',
										name: 'EvnDiagPS_id',
										type: 'int'
									}, {
										mapping: 'EvnDiagPS_pid',
										name: 'EvnDiagPS_pid',
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
										mapping: 'DiagSetPhase_id',
										name: 'DiagSetPhase_id',
										type: 'int'
									}, {
										mapping: 'EvnDiagPS_PhaseDescr',
										name: 'EvnDiagPS_PhaseDescr',
										type: 'string'
									}, {
										mapping: 'DiagSetClass_id',
										name: 'DiagSetClass_id',
										type: 'int'
									}, {
										mapping: 'DiagSetType_id',
										name: 'DiagSetType_id',
										type: 'int'
									}, {
										dateFormat: 'd.m.Y',
										mapping: 'EvnDiagPS_setDate',
										name: 'EvnDiagPS_setDate',
										type: 'date'
									}, {
										mapping: 'DiagSetClass_Name',
										name: 'DiagSetClass_Name',
										type: 'string'
									}, {
										mapping: 'Diag_Code',
										name: 'Diag_Code',
										type: 'string'
									}, {
										mapping: 'Diag_Name',
										name: 'Diag_Name',
										type: 'string'
									}]),
									url: '/?c=EvnDiag&m=loadEvnDiagPSGrid'
								}),
								tbar: new sw.Promed.Toolbar({
									buttons: [{
										handler: function() {
											this.openEvnDiagPSEditWindow('add', 'hosp');
										}.createDelegate(this),
										iconCls: 'add16',
										text: lang['dobavit']
									}, {
										handler: function() {
											this.openEvnDiagPSEditWindow('edit', 'hosp');
										}.createDelegate(this),
										iconCls: 'edit16',
										text: lang['izmenit']
									}, {
										handler: function() {
											this.openEvnDiagPSEditWindow('view', 'hosp');
										}.createDelegate(this),
										iconCls: 'view16',
										text: lang['prosmotr']
									}, {
										handler: function() {
											this.deleteEvent('EvnDiagPSHosp');
										}.createDelegate(this),
										iconCls: 'delete16',
										text: lang['udalit']
									}]
								})
							})]
						}),
						new sw.Promed.Panel({
							autoHeight: true,
							bodyStyle: 'padding-top: 0.5em;',
							border: true,
							collapsible: true,
							id: 'EPSEF_AdmitDepartPanel',
							layout: 'form',
							listeners: {
								'expand': function(panel) {
									// this.findById('EvnPSEditForm').getForm().findField('EvnPS_IsCont').focus(true);
								}.createDelegate(this)
							},
							style: 'margin-bottom: 0.5em;',
							title: lang['3_priemnoe'],
							items: [{
								fieldLabel: lang['sostoyanie_opyaneniya'],
								hiddenName: 'PrehospToxic_id',
								listeners: {
									'keydown': function(inp, e) {
										if ( e.getKey() == Ext.EventObject.TAB && e.shiftKey == true ) {
											e.stopEvent();
											var base_form = this.findById('EvnPSEditForm').getForm();

											if ( !this.findById('EPSEF_DirectDiagPanel').collapsed && this.findById('EPSEF_EvnDiagPSHospGrid').getStore().getCount() > 0 ) {
												this.findById('EPSEF_EvnDiagPSHospGrid').getView().focusRow(0);
												this.findById('EPSEF_EvnDiagPSHospGrid').getSelectionModel().selectFirstRow();
											}
											else if ( !this.findById('EPSEF_HospitalisationPanel').collapsed && this.action != 'view' ) {
												base_form.findField('EvnPS_IsDiagMismatch').focus(true);
											}
											else {
												this.buttons[this.buttons.length - 1].focus();
											}
										}
									}.createDelegate(this)
								},
								tabIndex: this.tabindex + 21,
								width: 300,
								xtype: 'swprehosptoxiccombo'
							}, {
								allowBlank: false,
								fieldLabel: lang['tip_gospitalizatsii'],
								hiddenName: 'PrehospType_id',
								id: this.id  + 'PrehospType_id',
								tabIndex: this.tabindex + 22,
								width: 300,
								xtype: 'swprehosptypecombo',
								listeners: {
									'change': function(field, newValue, oldValue) {
										var base_form = this.findById('EvnPSEditForm').getForm();
										base_form.findField('PurposeHospital_id').setContainerVisible(newValue == 2);
									}.createDelegate(this)
								}
							}, {
								fieldLabel: 'Цель госпитализации',
								comboSubject: 'PurposeHospital',
								width: 300,
								prefix: 'r101_',
								xtype: 'swcommonsprcombo',
								listeners: {
									'change': function(field, newValue, oldValue) {
										//this.getFinanceSource();
									}.createDelegate(this)
								}
							}, {
								allowDecimals: false,
								allowNegative: false,
								fieldLabel: lang['kolichestvo_gospitalizatsiy'],
								minValue: 0,
								maxValue: 99,
								name: 'EvnPS_HospCount',
								tabIndex: this.tabindex + 23,
								width: 100,
								xtype: 'numberfield'
							}, {
								layout: 'column',
								border: false,
								items: [{
									layout: 'form',
									border: false,
									items: [{
										fieldLabel: lang['vremya_s_nachala_zabolevaniya'],
										hiddenName: 'Okei_id',
										displayField: 'Okei_Name',
										tpl: new Ext.XTemplate(
											'<tpl for="."><div class="x-combo-list-item">',
											'{Okei_Name}',
											'</div></tpl>'
										),
										tabIndex: this.tabindex + 24,
										width: 80,
										xtype: 'swokeicombo',
										loadParams: {params: {where: ' where Okei_id in (100,101,102,104,107)'}}
									}]
								}, {
									layout: 'form',
									border: false,
									items: [{
										hideLabel: true,
										allowNegative: false,
										maxValue: 999,
										name: 'EvnPS_TimeDesease',
										tabIndex: this.tabindex + 24,
										width: 100,
										xtype: 'numberfield'
									}]
								}]
							}, {
								allowBlank: true,
								fieldLabel: lang['sluchay_zapuschen'],
								hiddenName: 'EvnPS_IsNeglectedCase',
								tabIndex: this.tabindex + 25,
								width: 100,
								xtype: 'swyesnocombo'
							}, {
								autoHeight: true,
								style: 'padding: 0px;',
								title: '',
								width: 730,
								xtype: 'fieldset',

								items: [ new sw.Promed.SwPrehospTraumaCombo({
									hiddenName: 'PrehospTrauma_id',
									lastQuery: '',
									listeners: {
										'change': function(combo, newValue, oldValue) {
											var base_form = this.findById('EvnPSEditForm').getForm();

											var is_unlaw_combo = base_form.findField('EvnPS_IsUnlaw');
											var record = combo.getStore().getById(newValue);

											if ( !record ) {
												is_unlaw_combo.clearValue();
												is_unlaw_combo.disable();
												is_unlaw_combo.setAllowBlank(true);
											}
											else {
												is_unlaw_combo.setValue(1);
												is_unlaw_combo.setDisabled( this.action == 'view' );
												is_unlaw_combo.setAllowBlank(false);
											}
											
											is_unlaw_combo.fireEvent('change', is_unlaw_combo, is_unlaw_combo.getValue());
										}.createDelegate(this)
									},
									tabIndex: this.tabindex + 26,
									width: 300
								}), {
									border: false,
									layout: 'column',
									items: [{
										border: false,
										layout: 'form',
										items: [ new sw.Promed.SwYesNoCombo({
											fieldLabel: lang['protivopravnaya'],
											hiddenName: 'EvnPS_IsUnlaw',
											lastQuery: '',
											tabIndex: this.tabindex + 27,
											width: 70,
		                                    listeners: {
		                                        'change': function(combo, newValue, oldValue) {
													
													if(this.isProcessInformationFrameLoad) return false;
													
		                                            var base_form = this.findById('EvnPSEditForm').getForm();

		                                            var notificationDateField = base_form.findField('EvnPS_NotificationDate'),
														notificationTimeField = base_form.findField('EvnPS_NotificationTime'),
														msfField = base_form.findField('MedStaffFact_id'),
														policeField = base_form.findField('EvnPS_Policeman'),
														msfpidField = base_form.findField('MedStaffFact_pid');

		                                            if ( newValue != 2 ) {
		                                                notificationDateField.setValue('');
		                                                notificationDateField.disable();
		                                                notificationTimeField.setValue('');
		                                                notificationTimeField.disable();
		                                                msfField.setValue('');
		                                                msfField.disable();
		                                                policeField.setValue('');
		                                                policeField.disable();
		                                                notificationDateField.setAllowBlank(true);
		                                                notificationTimeField.setAllowBlank(true);
		                                            }
		                                            else {
		                                                notificationDateField.setDisabled(this.action == 'view');
		                                                notificationTimeField.setDisabled(this.action == 'view');
		                                                msfField.setDisabled(this.action == 'view');
		                                                policeField.setDisabled(this.action == 'view');
		                                                notificationDateField.setAllowBlank(false);
		                                                notificationTimeField.setAllowBlank(false);
														if (Ext.isEmpty(msfField.getValue()) && !Ext.isEmpty(msfpidField.getValue())) {
															msfField.setValue(msfpidField.getValue());
														}
		                                            }
		                                        }.createDelegate(this)
		                                    }
										})]
									}]
								}]
							}, {
								border: false,
								bodyStyle: 'padding-top: 0.5em;',
								layout: 'column',
								items: [{
									border: false,
									layout: 'form',
									items: [{
										fieldLabel: 'Дата, время направления Извещения',
										name: 'EvnPS_NotificationDate',
										plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
										tabIndex: this.tabindex + 28,
										width: 100,
										xtype: 'swdatefield'
									}]
								}, {
									border: false,
									labelWidth: 200,
									layout: 'form',
									items: [{
										hideLabel: true,
										name: 'EvnPS_NotificationTime',
										onTriggerClick: function() {
											var base_form = this.findById('EvnPSEditForm').getForm(), 
											time_field = base_form.findField('EvnPS_NotificationTime'), 
											date_field = base_form.findField('EvnPS_NotificationDate');

											if ( time_field.disabled ) {
												return false;
											}

											setCurrentDateTime({
												dateField: date_field,
												loadMask: true,
												setDate: true,
												setDateMaxValue: true,
												setDateMinValue: false,
												setTime: true,
												timeField: time_field,
												windowId: this.id,
												callback: function() {
													date_field.fireEvent('change', date_field, date_field.getValue());
												}
											});
										}.createDelegate(this),
										tabIndex: this.tabindex + 28,
										plugins: [ new Ext.ux.InputTextMask('99:99', true) ],
										validateOnBlur: false,
										width: 60,
										xtype: 'swtimefield'
									}]
								}]
							}, {
								fieldLabel: 'Сотрудник МО, передавший телефонограмму',
								hiddenName: 'MedStaffFact_id',
								ignoreDisableInDoc: true,
								lastQuery: '',
								tabIndex: this.tabindex + 28,
								width: 500,
								xtype:'swmedstafffactglobalcombo'
							}, {
								fieldLabel: (getRegionNick() == 'kz' ? 'Сотрудник, принявший информацию' : 'Сотрудник МВД России, принявший информацию'),
								name: 'EvnPS_Policeman',
								tabIndex: this.tabindex + 28,
								width: 500,
								xtype: 'textfield'
							}, {
								border: false,
								layout: 'form',
								items: [{
									comboSubject: 'EntranceModeType',
									hiddenName: 'EntranceModeType_id',
									fieldLabel: lang['vid_transportirovki'],
									tabIndex: this.tabindex + 29,
									width: 300,
									xtype: 'swcommonsprcombo'
								}]
							}, {
								fieldLabel: lang['priemnoe_otdelenie'],
								hiddenName: 'LpuSection_pid',
								id: this.id + '_LpuSectionRecCombo',
								listeners: {
									'change': function(field, newValue, oldValue) {

										var base_form = this.findById('EvnPSEditForm').getForm();
										var lpu_section_eid = base_form.findField('LpuSection_eid').getValue();
										base_form.findField('LpuSection_eid').clearValue();
										var priemallow = (getGlobalOptions().check_priemdiag_allow&&getGlobalOptions().check_priemdiag_allow=='1');
										if ( newValue ) {
											base_form.findField('Diag_pid').setAllowBlank(!priemallow)
											field.getStore().each(function(record) {
												if ( record.get('LpuSection_id') == newValue ) {
													var LpuUnitType_SysNick = record.get('LpuUnitType_SysNick');
													if ( LpuUnitType_SysNick.toString().inlist([ 'priem' ]) ) {
														setLpuSectionGlobalStoreFilter({
															arrayLpuUnitType: [ '2', '3', '4', '5' ],
															onDate: Ext.util.Format.date(base_form.findField('EvnPS_setDate').getValue(), 'd.m.Y')
														});
													} else if ( LpuUnitType_SysNick.toString().inlist([ 'stac', 'dstac' ]) ) {
														setLpuSectionGlobalStoreFilter({
															arrayLpuUnitType: [ '2', '3' ],
															onDate: Ext.util.Format.date(base_form.findField('EvnPS_setDate').getValue(), 'd.m.Y')
														});
													} else if ( LpuUnitType_SysNick.toString().inlist([ 'polka', 'hstac', 'pstac' ]) ) {
														setLpuSectionGlobalStoreFilter({
															arrayLpuUnitType: [ '4', '5' ],
															onDate: Ext.util.Format.date(base_form.findField('EvnPS_setDate').getValue(), 'd.m.Y')
														});
													}
													base_form.findField('LpuSection_eid').getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));
													if ( base_form.findField('LpuSection_eid').getStore().getById(lpu_section_eid) ) {
														base_form.findField('LpuSection_eid').setValue(lpu_section_eid);
													}
												}
											});
										} else {
											base_form.findField('LpuSection_eid').clearValue();
											base_form.findField('LpuSection_eid').getStore().removeAll();
											base_form.findField('Diag_pid').setAllowBlank(true);
											
										}
										base_form.findField('Diag_pid').validate();
										this.loadBedList();
										this.setBedListAllowBlank();
									}.createDelegate(this)
								},
								listWidth: 650,
								tabIndex: this.tabindex + 30,
								width: 500,
								xtype: 'swlpusectionglobalcombo'
							}, {
								hiddenName: 'GetBed_id',
								fieldLabel: 'Профиль койки',
								xtype: 'swbaselocalcombo',
								valueField: 'GetBed_id',
								codeField: 'BedProfile',
								displayField: 'BedProfileRuFull',
								store: new Ext.data.JsonStore({
									autoLoad: false,
									url: '/?c=EvnPS&m=getBedList',
									fields: [
										{name: 'GetBed_id', type: 'int'},
										{name: 'BedProfile', type: 'int'},
										{name: 'BedProfileRu', type: 'string'},
										{name: 'TypeSrcFinRu', type: 'string'},
										{name: 'StacTypeRu', type: 'string'},
										{name: 'BedProfileRuFull', type: 'string'},
									],
									key: 'GetBed_id',
									sortInfo: {
										field: 'BedProfile'
									}
								}),
								tpl: new Ext.XTemplate(
									'<tpl for="."><div class="x-combo-list-item">',
									'<font color="red">{BedProfile}</font>&nbsp;{BedProfileRuFull}',
									'</div></tpl>'
								),
								width: 500,
								listWidth: 800
							}, {
								fieldLabel: langs('Врач'),
								dateFieldId: this.id + 'EPSEF_EvnPS_setDate',
								enableOutOfDateValidation: true,
								hiddenName: 'MedStaffFact_pid',
								id: this.id + '_MedStaffFactRecCombo',
								listWidth: 650,
								tabIndex: this.tabindex + 31,
								width: 500,
								xtype: 'swmedstafffactglobalcombo'
							},
								new sw.Promed.swDiagPanel({
									labelWidth: 180,
									phaseDescrName: 'EvnPS_PhaseDescr_pid',
									diagSetPhaseName: 'DiagSetPhase_pid',
									diagField: {
										checkAccessRights: true,
										// allowBlank: false,
										MKB:null,
										fieldLabel: lang['diagnoz_priem_otd-ya'],
										hiddenName: 'Diag_pid',
										id: this.id + '_DiagRecepCombo',
										tabIndex: this.tabindex + 32,
										width: 500,
										xtype: 'swdiagcombo'/*,
										listeners: {
											'change': function(combo, newValue, oldValue) {
												win.checkTrauma(newValue)
											}
										}*/
									},
									diagPhase: {
										xtype: 'swdiagsetphasecombo',
										fieldLabel: 'Состояние пациента при поступлении',
										hiddenName: 'DiagSetPhase_pid',
										allowBlank: true,
										tabIndex: this.tabindex + 32,
										width: 500,
										editable: false
									}
								}),
								{
									fieldLabel: 'Уточняющий диагноз прием. отд-я',
									hiddenName: 'Diag_cid',
									id: this.id + '_DiagRecepComboC',
									tabIndex: this.tabindex + 32,
									width: 500,
									xtype: 'swdiagcombo',
									onChange: function (combo, value) {
										//this.getFinanceSource();
									}.createDelegate(this)
								}
							]
						}),
						new sw.Promed.Panel({
							border: true,
							collapsible: true,
							height: 125,
							id: 'EPSEF_AdmitDiagPanel',
							isLoaded: false,
							layout: 'border',
							listeners: {
								'expand': function(panel) {
									if ( panel.isLoaded === false ) {
										panel.isLoaded = true;
										panel.findById('EPSEF_EvnDiagPSRecepGrid').getStore().load({
											params: {
												'class': 'EvnDiagPSRecep',
												EvnDiagPS_pid: this.findById('EvnPSEditForm').getForm().findField('EvnPS_id').getValue()
											}
										});
									}

									panel.doLayout();
								}.createDelegate(this)
							},
							style: 'margin-bottom: 0.5em;',
							title: lang['4_soputstvuyuschie_diagnozyi_priemnogo_otdeleniya'],
							items: [ new Ext.grid.GridPanel({
								autoExpandColumn: 'autoexpand_diag_recep',
								autoExpandMin: 100,
								border: false,
								columns: [{
									dataIndex: 'EvnDiagPS_setDate',
									header: lang['data'],
									hidden: false,
									renderer: Ext.util.Format.dateRenderer('d.m.Y'),
									resizable: false,
									sortable: true,
									width: 100
								}, {
									dataIndex: 'DiagSetClass_Name',
									header: lang['vid_diagnoza'],
									hidden: false,
									resizable: true,
									sortable: true,
									width: 200
								}, {
									dataIndex: 'Diag_Code',
									header: lang['kod_diagnoza'],
									hidden: false,
									resizable: true,
									sortable: true,
									width: 100
								}, {
									dataIndex: 'Diag_Name',
									header: lang['diagnoz'],
									hidden: false,
									id: 'autoexpand_diag_recep',
									resizable: true,
									sortable: true
								}],
								frame: false,
								height: 200,
								id: 'EPSEF_EvnDiagPSRecepGrid',
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

										var grid = Ext.getCmp('EPSEF_EvnDiagPSRecepGrid');

										switch ( e.getKey() ) {
											case Ext.EventObject.DELETE:
												this.deleteEvent('EvnDiagPSRecep');
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

												this.openEvnDiagPSEditWindow(action, 'recep');
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
												var base_form = this.findById('EvnPSEditForm').getForm();

												grid.getSelectionModel().clearSelections();
												grid.getSelectionModel().fireEvent('rowselect', grid.getSelectionModel());

												if ( e.shiftKey == false ) {
													if ( !this.findById('EPSEF_EvnSectionPanel').collapsed && this.findById('EPSEF_EvnSectionGrid').getStore().getCount() > 0 ) {
														this.findById('EPSEF_EvnSectionGrid').getView().focusRow(0);
														this.findById('EPSEF_EvnSectionGrid').getSelectionModel().selectFirstRow();
													}
													else if ( !this.findById('EPSEF_EvnStickPanel').collapsed && this.findById('EPSEF_EvnStickGrid').getStore().getCount() > 0 ) {
														this.findById('EPSEF_EvnStickGrid').getView().focusRow(0);
														this.findById('EPSEF_EvnStickGrid').getSelectionModel().selectFirstRow();
													}
													else if ( !this.findById('EPSEF_EvnUslugaPanel').collapsed && this.findById('EPSEF_EvnUslugaGrid').getStore().getCount() > 0 ) {
														this.findById('EPSEF_EvnUslugaGrid').getView().focusRow(0);
														this.findById('EPSEF_EvnUslugaGrid').getSelectionModel().selectFirstRow();
													}
													else if ( this.action != 'view' ) {
														this.buttons[0].focus();
													}
													else {
														this.buttons[1].focus();
													}
												}
												else {
													if ( !this.findById('EPSEF_AdmitDepartPanel').collapsed && this.action != 'view' ) {
														if ( !base_form.findField('Diag_pid').disabled ) {
															base_form.findField('Diag_pid').focus(true);
															
														}
														else {
															base_form.findField('MedStaffFact_pid').focus(true);
														}
													}
													else if ( !this.findById('EPSEF_DirectDiagPanel').collapsed && this.findById('EPSEF_EvnDiagPSHospGrid').getStore().getCount() > 0 ) {
														this.findById('EPSEF_EvnDiagPSHospGrid').getView().focusRow(0);
														this.findById('EPSEF_EvnDiagPSHospGrid').getSelectionModel().selectFirstRow();
													}
													else if ( !this.findById('EPSEF_HospitalisationPanel').collapsed && this.action != 'view' ) {
														base_form.findField('EvnPS_IsDiagMismatch').focus(true);
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
								listeners: {
									'rowdblclick': function(grid, number, obj) {
										this.openEvnDiagPSEditWindow('edit', 'recep');
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
											var toolbar = this.findById('EPSEF_EvnDiagPSRecepGrid').getTopToolbar();

											if ( selected_record ) {
												access_type = selected_record.get('accessType');
												id = selected_record.get('EvnDiagPS_id');
											}

											toolbar.items.items[1].disable();
											toolbar.items.items[3].disable();

											if ( id ) {
												toolbar.items.items[2].enable();

												if ( this.action != 'view' /*&& access_type == 'edit'*/ ) {
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
												LoadEmptyRow(this.findById('EPSEF_EvnDiagPSRecepGrid'));
											}

											// this.findById('EPSEF_EvnDiagPSRecepGrid').getView().focusRow(0);
											// this.findById('EPSEF_EvnDiagPSRecepGrid').getSelectionModel().selectFirstRow();
										}.createDelegate(this)
									},
									reader: new Ext.data.JsonReader({
										id: 'EvnDiagPS_id'
									}, [{
										mapping: 'EvnDiagPS_id',
										name: 'EvnDiagPS_id',
										type: 'int'
									}, {
										mapping: 'EvnDiagPS_pid',
										name: 'EvnDiagPS_pid',
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
										mapping: 'DiagSetPhase_id',
										name: 'DiagSetPhase_id',
										type: 'int'
									}, {
										mapping: 'EvnDiagPS_PhaseDescr',
										name: 'EvnDiagPS_PhaseDescr',
										type: 'string'
									}, {
										mapping: 'DiagSetClass_id',
										name: 'DiagSetClass_id',
										type: 'int'
									}, {
										mapping: 'DiagSetType_id',
										name: 'DiagSetType_id',
										type: 'int'
									}, {
										dateFormat: 'd.m.Y',
										mapping: 'EvnDiagPS_setDate',
										name: 'EvnDiagPS_setDate',
										type: 'date'
									}, {
										mapping: 'DiagSetClass_Name',
										name: 'DiagSetClass_Name',
										type: 'string'
									}, {
										mapping: 'Diag_Code',
										name: 'Diag_Code',
										type: 'string'
									}, {
										mapping: 'Diag_Name',
										name: 'Diag_Name',
										type: 'string'
									}]),
									url: '/?c=EvnDiag&m=loadEvnDiagPSGrid'
								}),
								tbar: new sw.Promed.Toolbar({
									buttons: [{
										handler: function() {
											this.openEvnDiagPSEditWindow('add', 'recep');
										}.createDelegate(this),
										iconCls: 'add16',
										text: lang['dobavit']
									}, {
										handler: function() {
											this.openEvnDiagPSEditWindow('edit', 'recep');
										}.createDelegate(this),
										iconCls: 'edit16',
										text: lang['izmenit']
									}, {
										handler: function() {
											this.openEvnDiagPSEditWindow('view', 'recep');
										}.createDelegate(this),
										iconCls: 'view16',
										text: lang['prosmotr']
									}, {
										handler: function() {
											this.deleteEvent('EvnDiagPSRecep');
										}.createDelegate(this),
										iconCls: 'delete16',
										text: lang['udalit']
									}]
								})
							})]
						}),
						new sw.Promed.Panel({
							autoHeight: true,
							bodyStyle: 'padding-top: 0.5em;',
							border: true,
							collapsible: true,
							id: 'EPSEF_PriemLeavePanel',
							layout: 'form',
							listeners: {
								'expand': function(panel) {
									this.isProcessLoadForm = true;
									panel.findById('EPSEF_LpuSectionCombo').getStore().each(function(record) {
										if (record.get('LpuSection_id') == panel.findById('EPSEF_LpuSectionCombo').getValue())
										{
											panel.findById('EPSEF_LpuSectionCombo').fireEvent('select', panel.findById('EPSEF_LpuSectionCombo'), record, 0);
										}
									});
									panel.findById('EPSEF_PrehospWaifRefuseCause_id').fireEvent('change', panel.findById('EPSEF_PrehospWaifRefuseCause_id'), panel.findById('EPSEF_PrehospWaifRefuseCause_id').getValue());
									this.isProcessLoadForm = false;
									panel.doLayout();
								}.createDelegate(this)
							},
							style: 'margin-bottom: 0.5em;',
							title: lang['5_ishod_prebyivaniya_v_priemnom_otdelenii'],
							items: [{
								border: false,
								layout: 'column',
								items: [{
									border: false,
									layout: 'form',
									items: [{
										fieldLabel: lang['data_ishoda'],
										name: 'EvnPS_OutcomeDate',
										plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
										tabIndex: this.tabindex + 43,
										width: 100,
										xtype: 'swdatefield',
										listeners: {
											'change': function(field, newValue) {
												this.refreshFieldsVisibility(['DiagSetPhase_did', 'DiagSetPhase_pid']);
											}
										}
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
										name: 'EvnPS_OutcomeTime',
										onTriggerClick: function() {
											var base_form = this.findById('EvnPSEditForm').getForm();
											var time_field = base_form.findField('EvnPS_OutcomeTime');

											if ( time_field.disabled ) {
												return false;
											}

											setCurrentDateTime({
												dateField: base_form.findField('EvnPS_OutcomeDate'),
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
										tabIndex: this.tabindex + 44,
										validateOnBlur: false,
										width: 60,
										xtype: 'swtimefield'
									}]
								}]
							},{
								hiddenName: 'LpuSection_eid',
								fieldLabel: lang['gospitalizirovan_v'],
								id: 'EPSEF_LpuSectionCombo',
								tabIndex: this.tabindex + 45,
								width: 500,
								xtype: 'swlpusectionglobalcombo',
								listeners:
								{
									'select': function (combo,record,index)
									{
										if ( !Ext.isEmpty(record.get('LpuSection_id')) )
										{
											var base_form = this.findById('EvnPSEditForm').getForm();
											if (!this.isProcessLoadForm && Ext.isEmpty(base_form.findField('EvnPS_OutcomeDate').getValue())) {
												base_form.findField('EvnPS_OutcomeTime').onTriggerClick();
											}
											var rc_combo = this.findById('EPSEF_PrehospWaifRefuseCause_id');
											var oldValue = rc_combo.getValue();
											rc_combo.clearValue();
											rc_combo.fireEvent('change',rc_combo,'',oldValue);
										}
									}.createDelegate(this)
								}
							},{
								hiddenName: 'PrehospWaifRefuseCause_id',
								id: 'EPSEF_PrehospWaifRefuseCause_id',
								fieldLabel: lang['otkaz'],
								tabIndex: this.tabindex + 46,
								width: 500,
								comboSubject: 'PrehospWaifRefuseCause',
								autoLoad: true,
								xtype: 'swcommonsprcombo',
								listeners:
								{
									'change': function (combo,newValue,oldValue)
									{
										var base_form = this.findById('EvnPSEditForm').getForm();
										var is_transf_call_combo = base_form.findField('EvnPS_IsTransfCall');
										var toolbar = this.findById('EPSEF_EvnSectionGrid').getTopToolbar();
										if(Ext.isEmpty(newValue))
										{
											is_transf_call_combo.setAllowBlank(true);
											is_transf_call_combo.disable();
											this.findById('EPSEF_PrehospWaifRefuseCauseButton').disable();
											Ext.getCmp('EPSEF_EvnSection_add').setDisabled( this.action == 'view' );
											toolbar.items.items[0].setDisabled( this.action == 'view' );
										}
										else
										{
											if (!this.isProcessLoadForm && Ext.isEmpty(base_form.findField('EvnPS_OutcomeDate').getValue())) {
												base_form.findField('EvnPS_OutcomeTime').onTriggerClick();
											}
											
											is_transf_call_combo.setDisabled(this.action == 'view');
											is_transf_call_combo.setAllowBlank(false);
											this.findById('EPSEF_PrehospWaifRefuseCauseButton').enable();
											this.findById('EPSEF_LpuSectionCombo').clearValue();
											Ext.getCmp('EPSEF_EvnSection_add').setDisabled( true );
											toolbar.items.items[0].disable();
										}
									}.createDelegate(this)
								}
							}, {
								border: false,
								layout: 'column',
								items: [{
									border: false,
									layout: 'form',
									width: 300,
									items: [{
										allowBlank: false,
										id: 'EPSEF_EvnPS_IsTransfCall',
										tabIndex: this.tabindex + 47,
										comboSubject: 'YesNo',
										fieldLabel: lang['peredan_aktivnyiy_vyizov'],
										hiddenName: 'EvnPS_IsTransfCall',
										width: 100,
										value: 1,
										xtype: 'swcommonsprcombo'
									}]
								}, {
									border: false,
									layout: 'form',
									width: 300,
									items: [{
										handler: function() {
											if ( this.action == 'add' /*&& Number(this.findById('EvnPSEditForm').getForm().findField('EvnPS_id').getValue()) == 0 */) {
												this.doSave({
													printRefuse: true
												});
											}
											else{
												window.open('/?c=EvnPS&m=printEvnPSPrehospWaifRefuseCause&EvnPS_id='+this.findById('EvnPSEditForm').getForm().findField('EvnPS_id').getValue(), '_blank');
											}
										}.createDelegate(this),
										iconCls: 'print16',
										id: 'EPSEF_PrehospWaifRefuseCauseButton',
										tabIndex: this.tabindex + 48,
										text: lang['spravka_ob_otkaze_v_gospitalizatsii'],
										tooltip: lang['spravka_ob_otkaze_v_gospitalizatsii'],
										xtype: 'button'
									}]
								}]
							}]
						}),
						new sw.Promed.Panel({
							border: true,
							collapsible: true,
							height: 150,
							id: 'EPSEF_EvnSectionPanel',
							isLoaded: false,
							layout: 'border',
							listeners: {
								'expand': function(panel) {
									if ( panel.isLoaded === false ) {
										panel.isLoaded = true;
										panel.findById('EPSEF_EvnSectionGrid').getStore().load({
											params: {
												EvnSection_pid: this.findById('EvnPSEditForm').getForm().findField('EvnPS_id').getValue()
											}
										});
									}

									panel.doLayout();
								}.createDelegate(this)
							},
							style: 'margin-bottom: 0.5em;',
							title: lang['6_dvijenie'],
							items: [ new Ext.grid.GridPanel({
								autoExpandColumn: 'autoexpand_section',
								autoExpandMin: 100,
								border: false,
								columns: [{
									dataIndex: 'EvnSection_setDate',
									header: lang['postuplenie'],
									hidden: false,
									renderer: Ext.util.Format.dateRenderer('d.m.Y'),
									resizable: false,
									sortable: true,
									width: 100
								}, {
									dataIndex: 'EvnSection_disDate',
									header: lang['vyipiska'],
									hidden: false,
									renderer: Ext.util.Format.dateRenderer('d.m.Y'),
									resizable: false,
									sortable: true,
									width: 100
								}, {
									dataIndex: 'LpuSection_Name',
									header: lang['otdelenie_lpu'],
									hidden: false,
									id: 'autoexpand_section',
									resizable: true,
									sortable: true
								}, {
									dataIndex: 'MedPersonal_Fio',
									header: lang['fio_vracha'],
									hidden: false,
									resizable: true,
									sortable: true,
									width: 200
								}, {
									dataIndex: 'LpuSectionWard_Name',
									header: lang['palata'],
									hidden: false,
									resizable: true,
									sortable: true,
									width: 200
								}, {
									dataIndex: 'LpuSectionProfile_Name',
									header: lang['profil_koyki'],
									hidden: false,
									resizable: true,
									sortable: true,
									width: 150
								}, {
									dataIndex: 'PayType_Name',
									header: lang['vid_oplatyi'],
									hidden: false,
									resizable: true,
									sortable: true,
									width: 100
								}, {
									dataIndex: 'Diag_Name',
									header: lang['osnovnoy_diagnoz'],
									hidden: false,
									resizable: true,
									sortable: true,
									width: 200
								}, {
									dataIndex: 'EvnSection_KoikoDni',
									header: lang['k_dni'],
									hidden: false,
									resizable: true,
									sortable: true,
									width: 50
								}, {
									dataIndex: 'EvnSection_KoikoDniNorm',
									header: lang['normativ'],
									hidden: false,
									resizable: true,
									sortable: true,
									width: 80
								}, {
									dataIndex: 'LeaveType_Name',
									header: lang['ishod_gospitalizatsii'],
									hidden: false,
									resizable: true,
									sortable: true,
									width: 120
								}, {
									dataIndex: 'LpuUnitType_id',
									header: lang['tip_lpuunit'],
									hidden: true
								}, {
									dataIndex: 'isLast',
									header: lang['poslednee_dvijenie'],
									hidden: true
								}],
								frame: false,
								height: 200,
								id: 'EPSEF_EvnSectionGrid',
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

										var grid = this.findById('EPSEF_EvnSectionGrid');

										switch ( e.getKey() ) {
											case Ext.EventObject.DELETE:
												this.deleteEvent('EvnSection');
												break;

											case Ext.EventObject.END:
												GridEnd(grid);
												break;

											case Ext.EventObject.F3:
												if ( !e.altKey ) {
													if ( !grid.getSelectionModel().getSelected() ) {
														return false;
													}

													var action = 'view';

													this.openEvnSectionEditWindow(action);
												} else {
													var params = new Object();
													params['key_id'] = grid.getSelectionModel().getSelected().data.EvnSection_id;
													params['key_field'] = 'EvnSection_id';
													getWnd('swAuditWindow').show(params);
												}
											break;

											case Ext.EventObject.ENTER:
											case Ext.EventObject.F4:
											case Ext.EventObject.INSERT:
												if ( !grid.getSelectionModel().getSelected() ) {
													return false;
												}

												var action = 'add';

												if ( e.getKey() == Ext.EventObject.F4 || e.getKey() == Ext.EventObject.ENTER ) {
													action = 'edit';
												}

												this.openEvnSectionEditWindow(action);
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
												var base_form = this.findById('EvnPSEditForm').getForm();

												grid.getSelectionModel().clearSelections();
												grid.getSelectionModel().fireEvent('rowselect', grid.getSelectionModel());

												if ( e.shiftKey == false ) {
													if ( !this.findById('EPSEF_EvnStickPanel').collapsed && this.findById('EPSEF_EvnStickGrid').getStore().getCount() > 0 ) {
														this.findById('EPSEF_EvnStickGrid').getView().focusRow(0);
														this.findById('EPSEF_EvnStickGrid').getSelectionModel().selectFirstRow();
													}
													else if ( !this.findById('EPSEF_EvnUslugaPanel').collapsed && this.findById('EPSEF_EvnUslugaGrid').getStore().getCount() > 0 ) {
														this.findById('EPSEF_EvnUslugaGrid').getView().focusRow(0);
														this.findById('EPSEF_EvnUslugaGrid').getSelectionModel().selectFirstRow();
													}
													else if ( this.action != 'view' ) {
														this.buttons[0].focus();
													}
													else {
														this.buttons[1].focus();
													}
												}
												else {
													if ( !this.findById('EPSEF_AdmitDiagPanel').collapsed && this.findById('EPSEF_EvnDiagPSRecepGrid').getStore().getCount() > 0 ) {
														this.findById('EPSEF_EvnDiagPSRecepGrid').getView().focusRow(0);
														this.findById('EPSEF_EvnDiagPSRecepGrid').getSelectionModel().selectFirstRow();
													}
													else if ( !this.findById('EPSEF_AdmitDepartPanel').collapsed && this.action != 'view' ) {
														if ( !base_form.findField('Diag_pid').disabled ) {
															base_form.findField('Diag_pid').focus(true);
														}
														else {
															base_form.findField('MedStaffFact_pid').focus(true);
														}
													}
													else if ( !this.findById('EPSEF_DirectDiagPanel').collapsed && this.findById('EPSEF_EvnDiagPSHospGrid').getStore().getCount() > 0 ) {
														this.findById('EPSEF_EvnDiagPSHospGrid').getView().focusRow(0);
														this.findById('EPSEF_EvnDiagPSHospGrid').getSelectionModel().selectFirstRow();
													}
													else if ( !this.findById('EPSEF_HospitalisationPanel').collapsed && this.action != 'view' ) {
														base_form.findField('EvnPS_IsDiagMismatch').focus(true);
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
								listeners: {
									'rowdblclick': function(grid, number, obj) {
										var access_type = 'view',
											action = 'view',
											id = null,
											selected_record = grid.getSelectionModel().getSelected();

										if ( selected_record ) {
											access_type = selected_record.get('accessType');
											id = selected_record.get('EvnSection_id');
										}

										if (
											!Ext.isEmpty(id)
											&& this.action != 'view'
											&& access_type == 'edit'
											&& (
												Ext.isEmpty(getGlobalOptions().medpersonal_id)
												|| Ext.isEmpty(selected_record.get('MedPersonal_id'))
												|| userHasWorkPlaceAtLpuSection(selected_record.get('LpuSection_id')) == true
												|| getGlobalOptions().isMedStatUser == true
												|| isSuperAdmin() == true
											)
											//&& selected_record.get('isLast') == 1
											&& selected_record.get('EvnSection_IsSigned') == 1
										) {
											action = 'edit';
										}

										this.openEvnSectionEditWindow(action);
									}.createDelegate(this)
								},
								loadMask: true,
								region: 'center',
								sm: new Ext.grid.RowSelectionModel({
									listeners: {
										'rowselect': function(sm, rowIndex, record) {
											var access_type = 'view',
												id = null,
												selected_record = sm.getSelected(),
												curMedPers_id = '',
												toolbar = this.findById('EPSEF_EvnSectionGrid').getTopToolbar();

											if ( selected_record ) {
												access_type = selected_record.get('accessType');
												id = selected_record.get('EvnSection_id');
												curMedPers_id = getGlobalOptions().CurMedPersonal_id;
											}

											toolbar.items.items[1].disable();
											toolbar.items.items[2].disable();
											toolbar.items.items[3].disable();

											if ( id ) {
												toolbar.items.items[2].enable();

												// Кнопка "Изменить"
												if (
													this.action != 'view'
													&& access_type == 'edit'
													&& (
														Ext.isEmpty(getGlobalOptions().medpersonal_id)
														|| Ext.isEmpty(selected_record.get('MedPersonal_id'))
														|| userHasWorkPlaceAtLpuSection(selected_record.get('LpuSection_id')) == true
														|| getGlobalOptions().isMedStatUser == true
														|| isSuperAdmin() == true
													)
													//&& selected_record.get('isLast') == 1
													&& selected_record.get('EvnSection_IsSigned') == 1
												) {
													toolbar.items.items[1].enable();
												}

												// Кнопка "Удалить"
												if (
													this.action != 'view'
													&& access_type == 'edit'
													&& (
														Ext.isEmpty(getGlobalOptions().medpersonal_id)
														|| Ext.isEmpty(selected_record.get('MedPersonal_id'))
														|| (userHasWorkPlaceAtLpuSection(selected_record.get('LpuSection_id')) == true && selected_record.get('MedPersonal_id') == getGlobalOptions().medpersonal_id)
														|| getGlobalOptions().isMedStatUser == true
														|| isSuperAdmin() == true
													)
													&& (
														selected_record.get('isLast') == 1
														|| getGlobalOptions().isMedStatUser == true
														|| isSuperAdmin() == true
													)
													&& selected_record.get('EvnSection_IsSigned') == 1
												) {
													toolbar.items.items[3].enable();
												}
											}

											if (curMedPers_id == selected_record.get('MedPersonal_id') && this.action != 'view') {
												toolbar.items.items[3].enable();
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
												LoadEmptyRow(this.findById('EPSEF_EvnSectionGrid'));
											}

											// this.findById('EPSEF_EvnSectionGrid').getView().focusRow(0);
											// this.findById('EPSEF_EvnSectionGrid').getSelectionModel().selectFirstRow();
										}.createDelegate(this)
									},
									reader: new Ext.data.JsonReader({
										id: 'EvnSection_id'
									}, [{
										mapping: 'accessType',
										name: 'accessType',
										type: 'string'
									},{
										mapping: 'EvnSection_id',
										name: 'EvnSection_id',
										type: 'int'
									}, {
										mapping: 'EvnSection_IsSigned',
										name: 'EvnSection_IsSigned',
										type: 'int'
									}, {
										mapping: 'EvnSection_pid',
										name: 'EvnSection_pid',
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
										mapping: 'DiagSetPhase_id',
										name: 'DiagSetPhase_id',
										type: 'int'
									}, {
										mapping: 'EvnSection_PhaseDescr',
										name: 'EvnSection_PhaseDescr',
										type: 'string'
									}, {
										mapping: 'LeaveType_Code',
										name: 'LeaveType_Code',
										type: 'int'
									}, {
										mapping: 'LeaveType_id',
										name: 'LeaveType_id',
										type: 'int'
									}, {
										mapping: 'LpuSection_id',
										name: 'LpuSection_id',
										type: 'int'
									}, {
										mapping: 'LpuSectionWard_id',
										name: 'LpuSectionWard_id',
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
										mapping: 'PayType_id',
										name: 'PayType_id',
										type: 'int'
									}, {
										mapping: 'Mes_id',
										name: 'Mes_id',
										type: 'int'
									}, {
										mapping: 'TariffClass_id',
										name: 'TariffClass_id',
										type: 'int'
									}, {
										mapping: 'EvnSection_setTime',
										name: 'EvnSection_setTime',
										type: 'string'
									}, {
										mapping: 'EvnSection_disTime',
										name: 'EvnSection_disTime',
										type: 'string'
									}, {
										dateFormat: 'd.m.Y',
										mapping: 'EvnSection_setDate',
										name: 'EvnSection_setDate',
										type: 'date'
									}, {
										dateFormat: 'd.m.Y',
										mapping: 'EvnSection_disDate',
										name: 'EvnSection_disDate',
										type: 'date'
									}, {
										mapping: 'LeaveType_Name',
										name: 'LeaveType_Name',
										type: 'string'
									}, {
										mapping: 'LpuSection_Name',
										name: 'LpuSection_Name',
										type: 'string'
									}, {
										mapping: 'LpuSectionProfile_Name',
										name: 'LpuSectionProfile_Name',
										type: 'string'
									}, {
										mapping: 'LpuSectionWard_Name',
										name: 'LpuSectionWard_Name',
										type: 'string'
									}, {
										mapping: 'LpuUnitType_id',
										name: 'LpuUnitType_id',
										type: 'string'
									}, {
										mapping: 'LpuUnitType_SysNick',
										name: 'LpuUnitType_SysNick',
										type: 'string'
									}, {
										mapping: 'MedPersonal_Fio',
										name: 'MedPersonal_Fio',
										type: 'string'
									}, {
										mapping: 'PayType_Name',
										name: 'PayType_Name',
										type: 'string'
									}, {
										mapping: 'Diag_Name',
										name: 'Diag_Name',
										type: 'string'
									}, {
										mapping: 'EvnSection_KoikoDni',
										name: 'EvnSection_KoikoDni',
										type: 'int'
									}, {
										mapping: 'EvnSection_KoikoDniNorm',
										name: 'EvnSection_KoikoDniNorm',
										type: 'int'
									}, {
										mapping: 'isLast',
										name: 'isLast',
										type: 'int'
									}, {
										mapping: 'EvnSection_IsPaid',
										name: 'EvnSection_IsPaid',
										type: 'int'
									}]),
									url: '/?c=EvnSection&m=loadEvnSectionGrid'
								}),
								tbar: new sw.Promed.Toolbar({
									buttons: [{
										id: 'EPSEF_EvnSection_add',
										handler: function() {
											this.openEvnSectionEditWindow('add');
										}.createDelegate(this),
										iconCls: 'add16',
										text: lang['dobavit']
									}, {
										handler: function() {
											this.openEvnSectionEditWindow('edit');
										}.createDelegate(this),
										iconCls: 'edit16',
										text: lang['izmenit']
									}, {
										handler: function() {
											this.openEvnSectionEditWindow('view');
										}.createDelegate(this),
										iconCls: 'view16',
										text: lang['prosmotr']
									}, {
										handler: function() {
											this.deleteEvent('EvnSection');
										}.createDelegate(this),
										iconCls: 'delete16',
										text: lang['udalit']
									}]
								})
							})]
						}),
						new sw.Promed.Panel({
							border: true,
							collapsible: true,
							height: 200,
							id: 'EPSEF_EvnStickPanel',
							isLoaded: false,
							layout: 'border',
							listeners: {
								'expand': function(panel) {
									if ( panel.isLoaded === false ) {
										panel.isLoaded = true;
									}
									panel.doLayout();
								}.createDelegate(this)
							},
							style: 'margin-bottom: 0.5em;',
							title: lang['7_netrudosposobnost'],
							items: [ new Ext.grid.GridPanel({
								autoExpandColumn: 'autoexpand_stick',
								autoExpandMin: 100,
								border: false,
								columns: [{
									dataIndex: 'EvnStick_ParentTypeName',
									header: lang['tap_kvs'],
									hidden: false,
									resizable: true,
									sortable: true,
									width: 100
								}, {
									dataIndex: 'EvnStick_ParentNum',
									header: lang['nomer_tap_kvs'],
									hidden: false,
									resizable: true,
									sortable: true,
									width: 120
								}, {
									dataIndex: 'StickType_Name',
									header: lang['vid_dokumenta'],
									hidden: false,
									resizable: true,
									sortable: true,
									width: 150
								}, {
									dataIndex: 'EvnStick_IsOriginal',
									header: lang['originalnost'],
									hidden: false,
									resizable: true,
									sortable: true,
									width: 150
								}, {
									dataIndex: 'StickWorkType_Name',
									header: lang['tip_zanyatosti'],
									hidden: false,
									resizable: true,
									sortable: true,
									width: 150
								}, {
									dataIndex: 'EvnStick_setDate',
									header: lang['data_vyidachi'],
									hidden: false,
									renderer: Ext.util.Format.dateRenderer('d.m.Y'),
									resizable: false,
									sortable: true,
									width: 100
								}, {
									dataIndex: 'EvnStickWorkRelease_begDate',
									header: lang['osvobojden_s'],
									hidden: false,
									renderer: Ext.util.Format.dateRenderer('d.m.Y'),
									resizable: false,
									sortable: true,
									width: 100
								}, {
									dataIndex: 'EvnStickWorkRelease_endDate',
									header: lang['osvobojden_po'],
									hidden: false,
									renderer: Ext.util.Format.dateRenderer('d.m.Y'),
									resizable: false,
									sortable: true,
									width: 100
								}, {
									dataIndex: 'EvnStick_disDate',
									header: lang['data_ishoda_lvn'],
									hidden: false,
									renderer: Ext.util.Format.dateRenderer('d.m.Y'),
									resizable: false,
									sortable: true,
									width: 100
								}, {
									dataIndex: 'EvnStick_Ser',
									header: lang['seriya'],
									hidden: false,
									resizable: true,
									sortable: true,
									width: 100
								}, {
									dataIndex: 'EvnStick_Num',
									header: lang['nomer'],
									hidden: false,
									resizable: true,
									sortable: true,
									width: 100
								}, {
									dataIndex: 'StickOrder_Name',
									header: lang['poryadok_vyipiski'],
									hidden: false,
									id: 'autoexpand_stick',
									resizable: true,
									sortable: true
								}, {
									dataIndex: 'EvnStick_stacBegDate',
									hidden: true
								}, {
									dataIndex: 'EvnStick_stacEndDate',
									hidden: true
								}, {
									dataIndex: 'EvnSection_setDate',
									hidden: true
								}, {
									dataIndex: 'EvnSection_disDate',
									hidden: true
								}],
								frame: false,
								id: 'EPSEF_EvnStickGrid',
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

										var grid = this.findById('EPSEF_EvnStickGrid');

										switch ( e.getKey() ) {
											case Ext.EventObject.DELETE:
												this.deleteEvent('EvnStick');
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
												var evnStickType = 0;

												if ( e.getKey() == Ext.EventObject.F3 ) {
													action = 'view';
												}
												else if ( e.getKey() == Ext.EventObject.F4 || e.getKey() == Ext.EventObject.ENTER ) {
													action = 'edit';
												}

												this.openEvnStickEditWindow(action);
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
												var base_form = this.findById('EvnPSEditForm').getForm();

												grid.getSelectionModel().clearSelections();
												grid.getSelectionModel().fireEvent('rowselect', grid.getSelectionModel());

												if ( e.shiftKey == false ) {
													if ( !this.findById('EPSEF_EvnUslugaPanel').collapsed && this.findById('EPSEF_EvnUslugaGrid').getStore().getCount() > 0 ) {
														this.findById('EPSEF_EvnUslugaGrid').getView().focusRow(0);
														this.findById('EPSEF_EvnUslugaGrid').getSelectionModel().selectFirstRow();
													}
													else if ( this.action != 'view' ) {
														this.buttons[0].focus();
													}
													else {
														this.buttons[1].focus();
													}
												}
												else {
													if ( !this.findById('EPSEF_EvnSectionPanel').collapsed && this.findById('EPSEF_EvnSectionGrid').getStore().getCount() > 0 ) {
														this.findById('EPSEF_EvnSectionGrid').getView().focusRow(0);
														this.findById('EPSEF_EvnSectionGrid').getSelectionModel().selectFirstRow();
													}
													else if ( !this.findById('EPSEF_AdmitDiagPanel').collapsed && this.findById('EPSEF_EvnDiagPSRecepGrid').getStore().getCount() > 0 ) {
														this.findById('EPSEF_EvnDiagPSRecepGrid').getView().focusRow(0);
														this.findById('EPSEF_EvnDiagPSRecepGrid').getSelectionModel().selectFirstRow();
													}
													else if ( !this.findById('EPSEF_AdmitDepartPanel').collapsed && this.action != 'view' ) {
														if ( !base_form.findField('Diag_pid').disabled ) {
															base_form.findField('Diag_pid').focus(true);
														}
														else {
															base_form.findField('MedStaffFact_pid').focus(true);
														}
													}
													else if ( !this.findById('EPSEF_DirectDiagPanel').collapsed && this.findById('EPSEF_EvnDiagPSHospGrid').getStore().getCount() > 0 ) {
														this.findById('EPSEF_EvnDiagPSHospGrid').getView().focusRow(0);
														this.findById('EPSEF_EvnDiagPSHospGrid').getSelectionModel().selectFirstRow();
													}
													else if ( !this.findById('EPSEF_HospitalisationPanel').collapsed && this.action != 'view' ) {
														base_form.findField('EvnPS_IsDiagMismatch').focus(true);
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
										this.openEvnStickEditWindow('edit');
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
											var toolbar = this.findById('EPSEF_EvnStickGrid').getTopToolbar();

											if ( selected_record ) {
												access_type = selected_record.get('accessType');
												id = selected_record.get('EvnStick_id');
											}

											toolbar.items.items[1].disable();
											toolbar.items.items[3].disable();

											if ( id ) {
												toolbar.items.items[2].enable();

												if ( this.action != 'view' /*&& access_type == 'edit'*/ ) {
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
												LoadEmptyRow(this.findById('EPSEF_EvnStickGrid'));
											}

											// this.findById('EPSEF_EvnStickGrid').getView().focusRow(0);
											// this.findById('EPSEF_EvnStickGrid').getSelectionModel().selectFirstRow();
										}.createDelegate(this)
									},
									reader: new Ext.data.JsonReader({
										id: 'EvnStick_id'
									}, [{
										mapping: 'accessType',
										name: 'accessType',
										type: 'string'
									}, {
										mapping: 'EvnStick_id',
										name: 'EvnStick_id',
										type: 'int'
									}, {
										mapping: 'EvnStick_mid',
										name: 'EvnStick_mid',
										type: 'int'
									}, {
										mapping: 'EvnStick_pid',
										name: 'EvnStick_pid',
										type: 'int'
									}, {
										mapping: 'evnStickType',
										name: 'evnStickType',
										type: 'int'
									}, {
										mapping: 'parentClass',
										name: 'parentClass',
										type: 'string'
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
										dateFormat: 'd.m.Y',
										mapping: 'EvnStick_setDate',
										name: 'EvnStick_setDate',
										type: 'date'
									}, {
										dateFormat: 'd.m.Y',
										mapping: 'EvnStickWorkRelease_begDate',
										name: 'EvnStickWorkRelease_begDate',
										type: 'date'
									}, {
										dateFormat: 'd.m.Y',
										mapping: 'EvnStickWorkRelease_endDate',
										name: 'EvnStickWorkRelease_endDate',
										type: 'date'
									}, {
										dateFormat: 'd.m.Y',
										mapping: 'EvnStick_disDate',
										name: 'EvnStick_disDate',
										type: 'date'
									}, {
										mapping: 'StickOrder_Name',
										name: 'StickOrder_Name',
										type: 'string'
									}, {
										mapping: 'StickType_Name',
										name: 'StickType_Name',
										type: 'string'
									}, {
										mapping: 'StickWorkType_Name',
										name: 'StickWorkType_Name',
										type: 'string'
									}, {
										mapping: 'EvnStick_Ser',
										name: 'EvnStick_Ser',
										type: 'string'
									}, {
										mapping: 'EvnStick_Num',
										name: 'EvnStick_Num',
										type: 'string'
									},{
										mapping: 'EvnStick_ParentTypeName',
										name: 'EvnStick_ParentTypeName',
										type: 'string'
									},{
										mapping: 'EvnStick_ParentNum',
										name: 'EvnStick_ParentNum',
										type: 'string'
									},{
										mapping: 'EvnStick_IsOriginal',
										name: 'EvnStick_IsOriginal',
										type: 'string'
									},{
										mapping: 'EvnStick_stacBegDate',
										name: 'EvnStick_stacBegDate',
										type: 'string'
									},{
										mapping: 'EvnStick_stacEndDate',
										name: 'EvnStick_stacEndDate',
										type: 'string'
									},{
										mapping: 'EvnSection_setDate',
										name: 'EvnSection_setDate',
										type: 'string'
									},{
										mapping: 'EvnSection_disDate',
										name: 'EvnSection_disDate',
										type: 'string'
									}
									]),
									url: '/?c=Stick&m=loadEvnStickGrid'
								}),
								tbar: new sw.Promed.Toolbar({
									buttons: [{
										handler: function() {
											this.openEvnStickEditWindow('add');
										}.createDelegate(this),
										iconCls: 'add16',
										text: lang['dobavit']
									},{
										handler: function() {
											this.openEvnStickEditWindow('edit');
										}.createDelegate(this),
										iconCls: 'edit16',
										text: lang['izmenit']
									}, {
										handler: function() {
											this.openEvnStickEditWindow('view');
										}.createDelegate(this),
										iconCls: 'view16',
										text: lang['prosmotr']
									}, {
										handler: function() {
											this.deleteEvent('EvnStick');
										}.createDelegate(this),
										iconCls: 'delete16',
										text: lang['udalit']
									}]
								})
							})]
						}),
						new sw.Promed.Panel({
							border: true,
							collapsible: true,
							height: 200,
							id: 'EPSEF_EvnUslugaPanel',
							isLoaded: false,
							layout: 'border',
							listeners: {
								'expand': function(panel) {
									if ( panel.isLoaded === false ) {
										panel.isLoaded = true;
										panel.findById('EPSEF_EvnUslugaGrid').getStore().load({
											params: {
												pid: this.findById('EvnPSEditForm').getForm().findField('EvnPS_id').getValue()
											}
										});
									}

									panel.doLayout();
								}.createDelegate(this)
							},
							style: 'margin-bottom: 0.5em;',
							title: lang['8_uslugi'],
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
									dataIndex: 'EvnUsluga_setTime',
									header: lang['vremya'],
									hidden: false,
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
								}],
								frame: false,
								id: 'EPSEF_EvnUslugaGrid',
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

										var grid = this.findById('EPSEF_EvnUslugaGrid');

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
												var base_form = this.findById('EvnPSEditForm').getForm();

												grid.getSelectionModel().clearSelections();
												grid.getSelectionModel().fireEvent('rowselect', grid.getSelectionModel());

												if ( e.shiftKey == false ) {
													if ( this.action != 'view' ) {
														this.buttons[0].focus();
													}
													else {
														this.buttons[1].focus();
													}
												}
												else {
													if ( !this.findById('EPSEF_EvnStickPanel').collapsed && this.findById('EPSEF_EvnStickGrid').getStore().getCount() > 0 ) {
														this.findById('EPSEF_EvnStickGrid').getView().focusRow(0);
														this.findById('EPSEF_EvnStickGrid').getSelectionModel().selectFirstRow();
													}
													else if ( !this.findById('EPSEF_EvnSectionPanel').collapsed && this.findById('EPSEF_EvnSectionGrid').getStore().getCount() > 0 ) {
														this.findById('EPSEF_EvnSectionGrid').getView().focusRow(0);
														this.findById('EPSEF_EvnSectionGrid').getSelectionModel().selectFirstRow();
													}
													else if ( !this.findById('EPSEF_AdmitDiagPanel').collapsed && this.findById('EPSEF_EvnDiagPSRecepGrid').getStore().getCount() > 0 ) {
														this.findById('EPSEF_EvnDiagPSRecepGrid').getView().focusRow(0);
														this.findById('EPSEF_EvnDiagPSRecepGrid').getSelectionModel().selectFirstRow();
													}
													else if ( !this.findById('EPSEF_AdmitDepartPanel').collapsed && this.action != 'view' ) {
														if ( !base_form.findField('Diag_pid').disabled ) {
															base_form.findField('Diag_pid').focus(true);
														}
														else {
															base_form.findField('MedStaffFact_pid').focus(true);
														}
													}
													else if ( !this.findById('EPSEF_DirectDiagPanel').collapsed && this.findById('EPSEF_EvnDiagPSHospGrid').getStore().getCount() > 0 ) {
														this.findById('EPSEF_EvnDiagPSHospGrid').getView().focusRow(0);
														this.findById('EPSEF_EvnDiagPSHospGrid').getSelectionModel().selectFirstRow();
													}
													else if ( !this.findById('EPSEF_HospitalisationPanel').collapsed && this.action != 'view' ) {
														base_form.findField('EvnPS_IsDiagMismatch').focus(true);
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
											var evnclass_sysnick = null;
											var selected_record = sm.getSelected();
											var toolbar = this.findById('EPSEF_EvnUslugaGrid').getTopToolbar();

											if ( selected_record ) {
												access_type = selected_record.get('accessType');
												id = selected_record.get('EvnUsluga_id');
												evnclass_sysnick = selected_record.get('EvnClass_SysNick');
											}

											toolbar.items.items[1].disable();
											toolbar.items.items[3].disable();

											if ( id ) {
												toolbar.items.items[2].enable();

												if ( this.action != 'view' /*&& access_type == 'edit'*/ ) {
													toolbar.items.items[1].enable();
													if (evnclass_sysnick != 'EvnUslugaPar') {
														toolbar.items.items[3].enable();
													}
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
										'parent': 'EvnPS'
									},
									listeners: {
										'load': function(store, records, index) {
											if ( store.getCount() == 0 ) {
												LoadEmptyRow(this.findById('EPSEF_EvnUslugaGrid'));
											}

											// this.findById('EPSEF_EvnUslugaGrid').getView().focusRow(0);
											// this.findById('EPSEF_EvnUslugaGrid').getSelectionModel().selectFirstRow();
										}.createDelegate(this)
									},
									reader: new Ext.data.JsonReader({
										id: 'EvnUsluga_id'
									}, [{
										mapping: 'EvnUsluga_id',
										name: 'EvnUsluga_id',
										type: 'int'
									}, {
										mapping: 'EvnClass_SysNick',
										name: 'EvnClass_SysNick',
										type: 'string'
									}, {
										mapping: 'EvnUsluga_setTime',
										name: 'EvnUsluga_setTime',
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
									}]),
									url: '/?c=EvnUsluga&m=loadEvnUslugaGrid'
								}),
								tbar: new sw.Promed.Toolbar({
									buttons: [{
										iconCls: 'add16',
										text: lang['dobavit'],
										menu: {
											xtype: 'menu',
											plain: true,
											items: [{
												handler: function() {
													this.openEvnUslugaEditWindow('addOper');
												}.createDelegate(this),
													text: lang['dobavit_operatsiyu']
											}, {
												handler: function() {
													this.openEvnUslugaEditWindow('add');
												}.createDelegate(this),
												text: lang['dobavit_obschuyu_uslugu']
											}]
										}
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
							border: true,
							collapsible: true,
							height: 200,
							id: 'EPSEF_EvnDrugPanel',
							isLoaded: false,
							layout: 'border',
							listeners: {
								'expand': function(panel) {
									if ( panel.isLoaded === false ) {
										panel.isLoaded = true;
										panel.findById('EPSEF_EvnDrugGrid').getStore().load({
											params: {
												EvnDrug_pid: this.findById('EvnPSEditForm').getForm().findField('EvnPS_id').getValue()
											}
										});
									}

									panel.doLayout();
								}.createDelegate(this)
							},
							style: 'margin-bottom: 0.5em;',
							title: lang['9_ispolzovanie_medikamentov'],
							items: [ new Ext.grid.GridPanel({
								autoExpandColumn: 'autoexpand_drug',
								autoExpandMin: 100,
								border: false,
								columns: [{
									dataIndex: 'EvnDrug_setDate',
									header: lang['data'],
									hidden: false,
									renderer: Ext.util.Format.dateRenderer('d.m.Y'),
									resizable: false,
									sortable: true,
									width: 100
								}, {
									dataIndex: 'Drug_Code',
									header: lang['kod'],
									hidden: false,
									resizable: false,
									sortable: true,
									width: 100
								}, {
									dataIndex: 'EvnDrug_Kolvo',
									header: lang['kolichestvo'],
									hidden: false,
									resizable: true,
									sortable: true,
									width: 100
								}, {
									dataIndex: 'Drug_Name',
									header: lang['naimenovanie'],
									hidden: false,
									id: 'autoexpand_drug',
									resizable: true,
									sortable: true
								}],
								frame: false,
								id: 'EPSEF_EvnDrugGrid',
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

										var grid = this.findById('EPSEF_EvnDrugGrid');

										switch ( e.getKey() ) {
											case Ext.EventObject.DELETE:
												this.deleteEvent('EvnDrug');
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

												this.openEvnDrugEditWindow(action);
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
												var base_form = this.findById('EvnPSEditForm').getForm();

												grid.getSelectionModel().clearSelections();
												grid.getSelectionModel().fireEvent('rowselect', grid.getSelectionModel());

												if ( e.shiftKey == false ) {
													if ( this.action != 'view' ) {
														this.buttons[0].focus();
													}
													else {
														this.buttons[1].focus();
													}
												}
												else {
													if ( !this.findById('EPSEF_EvnStickPanel').collapsed && this.findById('EPSEF_EvnStickGrid').getStore().getCount() > 0 ) {
														this.findById('EPSEF_EvnStickGrid').getView().focusRow(0);
														this.findById('EPSEF_EvnStickGrid').getSelectionModel().selectFirstRow();
													}
													else if ( !this.findById('EPSEF_EvnSectionPanel').collapsed && this.findById('EPSEF_EvnSectionGrid').getStore().getCount() > 0 ) {
														this.findById('EPSEF_EvnSectionGrid').getView().focusRow(0);
														this.findById('EPSEF_EvnSectionGrid').getSelectionModel().selectFirstRow();
													}
													else if ( !this.findById('EPSEF_AdmitDiagPanel').collapsed && this.findById('EPSEF_EvnDiagPSRecepGrid').getStore().getCount() > 0 ) {
														this.findById('EPSEF_EvnDiagPSRecepGrid').getView().focusRow(0);
														this.findById('EPSEF_EvnDiagPSRecepGrid').getSelectionModel().selectFirstRow();
													}
													else if ( !this.findById('EPSEF_AdmitDepartPanel').collapsed && this.action != 'view' ) {
														if ( !base_form.findField('Diag_pid').disabled ) {
															base_form.findField('Diag_pid').focus(true);
														}
														else {
															base_form.findField('MedStaffFact_pid').focus(true);
														}
													}
													else if ( !this.findById('EPSEF_DirectDiagPanel').collapsed && this.findById('EPSEF_EvnDiagPSHospGrid').getStore().getCount() > 0 ) {
														this.findById('EPSEF_EvnDiagPSHospGrid').getView().focusRow(0);
														this.findById('EPSEF_EvnDiagPSHospGrid').getSelectionModel().selectFirstRow();
													}
													else if ( !this.findById('EPSEF_HospitalisationPanel').collapsed && this.action != 'view' ) {
														base_form.findField('EvnPS_IsDiagMismatch').focus(true);
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
										this.openEvnDrugEditWindow('edit');
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
											var toolbar = this.findById('EPSEF_EvnDrugGrid').getTopToolbar();

											if ( selected_record ) {
												access_type = selected_record.get('accessType');
												id = selected_record.get('EvnDrug_id');
											}

											toolbar.items.items[1].disable();
											toolbar.items.items[3].disable();

											if ( id ) {
												toolbar.items.items[2].enable();

												if ( this.action != 'view' /*&& access_type == 'edit'*/ ) {
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
												LoadEmptyRow(this.findById('EPSEF_EvnDrugGrid'));
											}
										}.createDelegate(this)
									},
									reader: new Ext.data.JsonReader({
										id: 'EvnDrug_id'
									}, [{
										mapping: 'EvnDrug_id',
										name: 'EvnDrug_id',
										type: 'int'
									}, {
										dateFormat: 'd.m.Y',
										mapping: 'EvnDrug_setDate',
										name: 'EvnDrug_setDate',
										type: 'date'
									}, {
										mapping: 'Drug_Code',
										name: 'Drug_Code',
										type: 'string'
									}, {
										mapping: 'Drug_Name',
										name: 'Drug_Name',
										type: 'string'
									}, {
										mapping: 'EvnDrug_Kolvo',
										name: 'EvnDrug_Kolvo',
										type: 'float'
									}]),
									url: '/?c=EvnDrug&m=loadEvnDrugGrid'
								}),
								tbar: new sw.Promed.Toolbar({
									buttons: [{
										handler: function() {
											this.openEvnDrugEditWindow('add');
										}.createDelegate(this),
										iconCls: 'add16',
										text: lang['dobavit']
									}, {
										handler: function() {
											this.openEvnDrugEditWindow('edit');
										}.createDelegate(this),
										iconCls: 'edit16',
										text: lang['izmenit']
									}, {
										handler: function() {
											this.openEvnDrugEditWindow('view');
										}.createDelegate(this),
										iconCls: 'view16',
										text: lang['prosmotr']
									}, {
										handler: function() {
											this.deleteEvent('EvnDrug');
										}.createDelegate(this),
										iconCls: 'delete16',
										text: lang['udalit']
									}, {
										iconCls: 'print16',
										text: lang['pechat'],
										handler: function() {
											var grid = this.findById('EPSEF_EvnDrugGrid');
											Ext.ux.GridPrinter.print(grid);
										}.createDelegate(this)
									}]
								})
							})]
						}),
						new sw.Promed.Panel({
							border: true,
							collapsible: true,
							height: 290,
							id: 'EPSEF_PrehospWaifPanel',
							layout: 'border',
							listeners: {
								'expand': function(panel) {
									//to-do не загружать грид, если он загружен
									this.PrehospWaifInspectionRefreshGrid();
									panel.doLayout();
								}.createDelegate(this)
							},
							style: 'margin-bottom: 0.5em;',
							title: lang['10_besprizornyiy'],
							items: [{
								bodyStyle: 'padding-top: 0.5em;',
								border: false,
								height: 90,
								layout: 'form',
								region: 'north',
								items: [{
									id: 'EPSEF_EvnPS_IsWaif',
									fieldLabel: lang['besprizornyiy'],
									hiddenName: 'EvnPS_IsWaif',
									tabIndex: this.tabindex + 50,
									width: 100,
									xtype: 'swyesnocombo',
									listeners:
									{
										'change': function (combo,newValue,oldValue)
										{
											var base_form = this.findById('EvnPSEditForm').getForm();
											var pw_arrive_combo = base_form.findField('PrehospWaifArrive_id');
											var pw_reason_combo = base_form.findField('PrehospWaifReason_id');
											var view_frame = this.findById('EPSEF_PrehospWaifInspection');
											if(Ext.isEmpty(newValue) || newValue == 1)
											{
												pw_arrive_combo.disable();
												pw_reason_combo.disable();
												pw_arrive_combo.setAllowBlank(true);
												pw_reason_combo.setAllowBlank(true);
												pw_arrive_combo.clearValue();
												pw_reason_combo.clearValue();
												view_frame.setReadOnly(true);
											}
											else
											{
												//Кем доставлен; доступно и обязательное если Беспризорный = Да.
												pw_arrive_combo.setDisabled( this.action == 'view' );
												pw_arrive_combo.setAllowBlank(false);
												// Обратился самостоятельно ставить автоматически и поле не доступно, если Беспризорный = Да и в разделе КВС Госпитализация поле Кем доставлен = Самостоятельно
												/*if (base_form.findField('PrehospArrive_id').getValue() == 1)
												 {
												 pw_arrive_combo.setValue(3);
												 pw_arrive_combo.disable();
												 }*/
												// Причина помещения в ЛПУ: доступно и обязательное если Беспризорный = Да.
												pw_reason_combo.setDisabled( this.action == 'view' );
												pw_reason_combo.setAllowBlank(false);
												view_frame.setReadOnly(false);
											}
										}.createDelegate(this)
									}
								},{
									fieldLabel: lang['kem_dostavlen'],
									tabIndex: this.tabindex + 51,
									width: 500,
									comboSubject: 'PrehospWaifArrive',
									hiddenName: 'PrehospWaifArrive_id',
									autoLoad: true,
									xtype: 'swcommonsprcombo'
								},{
									id: 'EPSEF_PrehospWaifReason_id',
									fieldLabel: lang['prichina_pomescheniya_v_lpu'],
									tabIndex: this.tabindex + 52,
									hiddenName: 'PrehospWaifReason_id',
									width: 500,
									comboSubject: 'PrehospWaifReason',
									autoLoad: true,
									xtype: 'swcommonsprcombo'
								}]
							},
								new sw.Promed.ViewFrame({
									id: 'EPSEF_PrehospWaifInspection',
									title:lang['osmotryi'],
									object: 'PrehospWaifInspection',
									editformclassname: 'swPrehospWaifInspectionEditWindow',
									dataUrl: '/?c=PrehospWaifInspection&m=loadRecordGrid',
									height:200,
									autoLoadData: false,
									stringfields:
										[
											{name: 'PrehospWaifInspection_id', type: 'int', hidden: true, key: true},
											{name: 'EvnPS_id', type: 'int', hidden: true, isparams: true},
											{name: 'LpuSection_id', type: 'int', hidden: true},
											{name: 'MedStaffFact_id', type: 'int', hidden: true},
											{name: 'Diag_id', type: 'int', hidden: true},
											{name: 'PrehospWaifInspection_SetDT',  type: 'string', header: lang['data_vremya'], width: 100},
											{name: 'LpuSection_Name',  type: 'string', header: lang['otdelenie'], width: 250},
											{name: 'MedPersonal_Fio',  type: 'string', header: lang['vrach'], width: 200},
											{id: 'autoexpand', name: 'Diag_Name',  type: 'string', header: lang['diagnoz']}
										],
									actions:
										[
											{name:'action_add', handler: function() {this.openPrehospWaifInspectionEditWindow('add');}.createDelegate(this)},
											{name:'action_edit', handler: function() {this.openPrehospWaifInspectionEditWindow('edit');}.createDelegate(this)},
											{name:'action_view', handler: function() {this.openPrehospWaifInspectionEditWindow('view');}.createDelegate(this)},
											{name:'action_delete'},
											{name:'action_refresh', handler: function() {this.PrehospWaifInspectionRefreshGrid();}.createDelegate(this)},
											{name:'action_print'}
										],
									paging: false,
									root: 'data',
									totalProperty: 'totalCount',
									focusOn: {name:'EPSEF_PrintBtn',type:'button'},
									focusPrev: {name:'EPSEF_PrehospWaifReason_id',type:'field'},
									focusOnFirstLoad: false
								})
							]
						})],
					reader: new Ext.data.JsonReader({
						success: Ext.emptyFn
					}, [
						{name: 'accessType'},
						{name: 'childPS'},
						{name: 'EvnPS_OutcomeDate'},
						{name: 'EvnPS_OutcomeTime'},
						{name: 'EvnPS_IsPLAmbulance'},
						{name: 'LpuSection_eid'},
						{name: 'PrehospWaifRefuseCause_id'},
						{name: 'LpuSectionProfile_id'},
						{name: 'EvnPS_IsTransfCall'},
						{name: 'EvnPS_IsWaif'},
						{name: 'PrehospWaifArrive_id'},
						{name: 'PrehospWaifReason_id'},
						{name: 'Diag_did'},
						{name: 'DiagSetPhase_did'},
						{name: 'EvnPS_PhaseDescr_did'},
						{name: 'Diag_pid'},
						{name: 'Diag_cid'},
						{name: 'DiagSetPhase_pid'},
						{name: 'EvnPS_PhaseDescr_pid'},
						{name: 'EvnQueue_id'},
						{name: 'EvnDirection_id'},
						{name: 'EvnDirection_Num'},
						{name: 'EvnDirection_setDate'},
						{name: 'EvnPS_CodeConv'},
						{name: 'EvnPS_HospCount'},
						{name: 'EvnPS_id'},
						{name: 'EvnPS_IsPrehospAcceptRefuse'},
						{name: 'EvnPS_IsCont'},
						{name: 'EvnPS_IsDiagMismatch'},
						{name: 'EvnPS_IsImperHosp'},
						{name: 'EvnPS_IsNeglectedCase'},
						{name: 'EvnPS_IsWrongCure'},
						{name: 'EvnPS_IsUnlaw'},
						{name: 'EvnPS_IsUnport'},
						{name: 'EvnPS_NotificationDate'},
						{name: 'EvnPS_NotificationTime'},
						{name: 'MedStaffFact_id'},
						{name: 'EvnPS_Policeman'},
						{name: 'EvnPS_IsShortVolume'},
						{name: 'EvnPS_IsWithoutDirection'},
						{name: 'EvnPS_NumCard'},
						{name: 'EvnPS_NumConv'},
						{name: 'EvnPS_PrehospAcceptRefuseDT'},
						{name: 'EvnPS_PrehospWaifRefuseDT'},
						{name: 'EvnPS_setDate'},
						{name: 'EvnPS_setTime'},
						{name: 'EvnPS_TimeDesease'},
						{name: 'Okei_id'},
						{name: 'LpuSection_did'},
						{name: 'LpuSection_pid'},
						{name: 'GetBed_id'},
						{name: 'MedStaffFact_pid'},
						{name: 'Lpu_id'},
						{name: 'Org_did'},
						{name: 'Lpu_did'},
						{name: 'PayType_id'},
						{name: 'Person_id'},
						{name: 'PersonEvn_id'},
						{name: 'PrehospArrive_id'},
						{name: 'PrehospDirect_id'},
						{name: 'PrehospStatus_id'},
						{name: 'PrehospToxic_id'},
						{name: 'PrehospTrauma_id'},
						{name: 'PrehospType_id'},
						{name: 'PurposeHospital_id'},
						{name: 'EntranceModeType_id'},
						{name: 'Server_id'}
					]),
					region: 'center',
					url: '/?c=EvnPS&m=saveEvnPS'
				})]
		});

		sw.Promed.swEvnPSEditWindow.superclass.initComponent.apply(this, arguments);

		this.findById(this.id + '_LpuSectionRecCombo').addListener('change', function(combo, newValue, oldValue) {
			var base_form = this.findById('EvnPSEditForm').getForm();

			var diag_d_combo = base_form.findField('Diag_did');
			var diag_p_combo = base_form.findField('Diag_pid');

			if ( !newValue ) {
				diag_p_combo.clearValue();
				diag_p_combo.disable();
				return false;
			}
			diag_p_combo.setDisabled( this.action == 'view' );
			diag_p_combo.validate();
			var diag_did = diag_d_combo.getValue();
			var diag_pid = diag_p_combo.getValue();

			if ( !diag_did || diag_pid ) {
				return false;
			}

			diag_p_combo.getStore().load({
				callback: function() {
					diag_p_combo.setValue(diag_did);
					diag_p_combo.fireEvent('select', diag_p_combo, diag_p_combo.getStore().getAt(0), 0);
				},
				params: {
					where: "where DiagLevel_id = 4 and Diag_id = " + diag_did
				}
			});
		}.createDelegate(this));
		
		this.findById(this.id + '_MedStaffFactRecCombo').addListener('keydown', function(inp, e) {
			var base_form = this.findById('EvnPSEditForm').getForm();

			if ( e.getKey() == Ext.EventObject.TAB && e.shiftKey == false && base_form.findField('Diag_pid').disabled ) {
				e.stopEvent();

				if ( !this.findById('EPSEF_AdmitDiagPanel').collapsed && this.findById('EPSEF_EvnDiagPSRecepGrid').getStore().getCount() > 0 ) {
					this.findById('EPSEF_EvnDiagPSRecepGrid').getView().focusRow(0);
					this.findById('EPSEF_EvnDiagPSRecepGrid').getSelectionModel().selectFirstRow();
				}
				else if ( !this.findById('EPSEF_EvnSectionPanel').collapsed && this.findById('EPSEF_EvnSectionGrid').getStore().getCount() > 0 ) {
					this.findById('EPSEF_EvnSectionGrid').getView().focusRow(0);
					this.findById('EPSEF_EvnSectionGrid').getSelectionModel().selectFirstRow();
				}
				else if ( !this.findById('EPSEF_EvnStickPanel').collapsed && this.findById('EPSEF_EvnStickGrid').getStore().getCount() > 0 ) {
					this.findById('EPSEF_EvnStickGrid').getView().focusRow(0);
					this.findById('EPSEF_EvnStickGrid').getSelectionModel().selectFirstRow();
				}
				else if ( !this.findById('EPSEF_EvnUslugaPanel').collapsed && this.findById('EPSEF_EvnUslugaGrid').getStore().getCount() > 0 ) {
					this.findById('EPSEF_EvnUslugaGrid').getView().focusRow(0);
					this.findById('EPSEF_EvnUslugaGrid').getSelectionModel().selectFirstRow();
				}
				else if ( this.action != 'view' ) {
					this.buttons[0].focus();
				}
				else {
					this.buttons[1].focus();
				}
			}
		}.createDelegate(this));

		this.findById(this.id + '_DiagRecepCombo').addListener('keydown', function(inp, e) {
			var base_form = this.findById('EvnPSEditForm').getForm();

			if ( e.getKey() == Ext.EventObject.TAB && e.shiftKey == false ) {
				e.stopEvent();

				if ( !this.findById('EPSEF_AdmitDiagPanel').collapsed && this.findById('EPSEF_EvnDiagPSRecepGrid').getStore().getCount() > 0 ) {
					this.findById('EPSEF_EvnDiagPSRecepGrid').getView().focusRow(0);
					this.findById('EPSEF_EvnDiagPSRecepGrid').getSelectionModel().selectFirstRow();
				}
				else if ( !this.findById('EPSEF_EvnSectionPanel').collapsed && this.findById('EPSEF_EvnSectionGrid').getStore().getCount() > 0 ) {
					this.findById('EPSEF_EvnSectionGrid').getView().focusRow(0);
					this.findById('EPSEF_EvnSectionGrid').getSelectionModel().selectFirstRow();
				}
				else if ( !this.findById('EPSEF_EvnStickPanel').collapsed && this.findById('EPSEF_EvnStickGrid').getStore().getCount() > 0 ) {
					this.findById('EPSEF_EvnStickGrid').getView().focusRow(0);
					this.findById('EPSEF_EvnStickGrid').getSelectionModel().selectFirstRow();
				}
				else if ( !this.findById('EPSEF_EvnUslugaPanel').collapsed && this.findById('EPSEF_EvnUslugaGrid').getStore().getCount() > 0 ) {
					this.findById('EPSEF_EvnUslugaGrid').getView().focusRow(0);
					this.findById('EPSEF_EvnUslugaGrid').getSelectionModel().selectFirstRow();
				}
				else if ( this.action != 'view' ) {
					this.buttons[0].focus();
				}
				else {
					this.buttons[1].focus();
				}
			}
		}.createDelegate(this));

		//this.findById(this.id + '_DiagRecepCombo').addListener('change', function(inp, e) {
			//this.getFinanceSource();
		//}.createDelegate(this));

		var base_form = this.findById('EvnPSEditForm').getForm();

		this.setDirection = function(data) {
			var iswd_combo = base_form.findField('EvnPS_IsWithoutDirection');
			var PrehospDirect_id = (data.PrehospDirect_id || (data.Lpu_id != getGlobalOptions().lpu_id ? 16 : 15));
			if (data.LpuBuildingType_id == 27) {
				PrehospDirect_id = 10;
			}

			base_form.findField('EvnDirection_Num').setValue('');
			base_form.findField('EvnDirection_setDate').setValue('');
			base_form.findField('LpuSection_did').clearValue();
			base_form.findField('Org_did').clearValue();

			base_form.findField('PrehospDirect_id').disable();
			base_form.findField('PrehospDirect_id').setValue(PrehospDirect_id);
			base_form.findField('PurposeHospital_id').setValue(data.PurposeHospital_id || '');
			base_form.findField('Diag_cid').setValue(data.Diag_cid || '');
			base_form.findField('PayType_id').setValue(data.PayType_id);
			iswd_combo.setValue(2);
			iswd_combo.fireEvent('change', iswd_combo, 2);

			base_form.findField('EvnDirection_id').setValue(data.EvnDirection_id);

			if ( !Ext.isEmpty(data.EvnDirection_id) ) {
				base_form.findField('EvnDirection_Num').setDisabled(true);
				base_form.findField('EvnDirection_setDate').setDisabled(true);
				base_form.findField('LpuSection_did').setDisabled(true);
				base_form.findField('Org_did').setDisabled(true);
				base_form.findField('PayType_id').setDisabled(true);
			}
			else {
				base_form.findField('EvnDirection_Num').setDisabled(this.action == 'view');
				base_form.findField('EvnDirection_setDate').setDisabled(this.action == 'view');
				base_form.findField('LpuSection_did').setDisabled(this.action == 'view');
				base_form.findField('Org_did').setDisabled(this.action == 'view');
			}

			base_form.findField('Org_did').getStore().load({
				callback: function(records, options, success) {
					if ( success ) {
						base_form.findField('Org_did').setValue(data.Org_did);
					}
				}.createDelegate(this),
				params: {
					Org_id: data.Org_did,
					OrgType: 'org'
				}
			});

			if ( !Ext.isEmpty(data.EvnDirection_Num) ) {
				base_form.findField('EvnDirection_Num').setValue(data.EvnDirection_Num);
			}

			if ( !Ext.isEmpty(data.EvnDirection_setDate) ) {
				base_form.findField('EvnDirection_setDate').setValue(data.EvnDirection_setDate);
			}

			if ( !Ext.isEmpty(data.Diag_did) ) {
				base_form.findField('Diag_did').getStore().load({
					callback: function() {
						base_form.findField('Diag_did').getStore().each(function(record) {
							if ( record.get('Diag_id') == data.Diag_did ) {
								base_form.findField('Diag_did').setValue(data.Diag_did);
								base_form.findField('Diag_did').fireEvent('select', base_form.findField('Diag_did'), record, 0);
							}
						});
					},
					params: {where: "where DiagLevel_id = 4 and Diag_id = " + data.Diag_did}
				});
			}

			var PrehospType_SysNick = null;
			switch(Number(data.DirType_id)) {
				case 1: PrehospType_SysNick = 'plan';break;
				case 5: PrehospType_SysNick = 'extreme';break;
			}
			if (PrehospType_SysNick) {
				base_form.findField('PrehospType_id').setFieldValue('PrehospType_SysNick', PrehospType_SysNick);
				base_form.findField('PrehospType_id', base_form.findField('PrehospType_id'), base_form.findField('PrehospType_id').getValue());
			}
		}.createDelegate(this);
	},
	isCopy: false,
	layout: 'border',
	listeners: {
		'hide': function(win) {
			win.onHide();
		},
		'maximize': function(win) {
			win.findById('EPSEF_HospitalisationPanel').doLayout();
			win.findById('EPSEF_DirectDiagPanel').doLayout();
			win.findById('EPSEF_AdmitDepartPanel').doLayout();
			win.findById('EPSEF_AdmitDiagPanel').doLayout();

			if ( !win.findById('EPSEF_EvnSectionPanel').hidden ) {
				win.findById('EPSEF_EvnSectionPanel').doLayout();
			}

			if ( !win.findById('EPSEF_EvnStickPanel').hidden ) {
				win.findById('EPSEF_EvnStickPanel').doLayout();
			}

			if ( !win.findById('EPSEF_EvnUslugaPanel').hidden ) {
				win.findById('EPSEF_EvnUslugaPanel').doLayout();
			}

		},
		'restore': function(win) {
			win.fireEvent('maximize', win);
		}
	},
	maximizable: true,
	minHeight: 550,
	minWidth: 800,
	modal: true,
	onCancelAction: function() {
		var base_form = this.findById('EvnPSEditForm').getForm();
		var evn_ps_id = base_form.findField('EvnPS_id').getValue();
		if ( evn_ps_id > 0 && (this.action == 'add' || this.isCopy || this.deleteOnCancel) ) {
			// удалить КВС
			// закрыть окно после успешного удаления
			var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Удаление КВС..."});
			loadMask.show();

			Ext.Ajax.request({
				callback: function(options, success, response) {
					loadMask.hide();

					if ( success ) {
						this.hide();
					}
					else {
						sw.swMsg.alert(lang['oshibka'], lang['pri_udalenii_kvs_voznikli_oshibki']);
						return false;
					}
				}.createDelegate(this),
				params: {
					Evn_id: evn_ps_id
				},
				url: '/?c=Evn&m=deleteEvn'
			});
		}
		else if ( this.action == 'edit' ) {
			this.hide();
		}
		else {
			this.hide();
		}
	},
	onHide: Ext.emptyFn,
	openEvnDiagPSEditWindow: function(action, type) {
		if ( action != 'add' && action != 'edit' && action != 'view' ) {
			return false;
		}

		var base_form = this.findById('EvnPSEditForm').getForm();
		var grid = null;

		if ( this.action == 'view' ) {
			if ( action == 'add' ) {
				return false;
			}
			else if ( action == 'edit' ) {
				action = 'view';
			}
		}

		if ( getWnd('swEvnDiagPSEditWindow').isVisible() ) {
			sw.swMsg.alert(lang['soobschenie'], lang['okno_redaktirovaniya_diagnoza_uje_otkryito']);
			return false;
		}

		if ( action == 'add' && base_form.findField('EvnPS_id').getValue() == 0 ) {
			this.doSave({
				callback: function() {
					this.openEvnDiagPSEditWindow(action, type);
				}.createDelegate(this),
				print: false
			});
			return false;
		}

		switch ( type ) {
			case 'hosp':
				if ( this.findById('EPSEF_HospitalisationPanel').hidden ) {
					return false;
				}

				if ( !base_form.findField('Diag_did').getValue() ) {
					sw.swMsg.alert(lang['oshibka'], lang['ne_zapolnen_osnovnoy_diagnoz_napravivshego_uchrejdeniya'], function() {base_form.findField('Diag_did').focus(true);});
					return false;
				}

				grid = this.findById('EPSEF_EvnDiagPSHospGrid');
				break;

			case 'recep':
				if ( this.findById('EPSEF_AdmitDepartPanel').hidden ) {
					return false;
				}

				if ( !base_form.findField('Diag_pid').getValue() ) {
					sw.swMsg.alert(lang['oshibka'], lang['ne_zapolnen_osnovnoy_diagnoz_v_priemnom_otdelenii'], function() {base_form.findField('Diag_pid').focus(true);});
					return false;
				}

				grid = this.findById('EPSEF_EvnDiagPSRecepGrid');
				break;

			default:
				return false;
				break;
		}

		var params = new Object();

		if ( action == 'add' ) {
			params.DiagSetClass_id = 3;
			params.EvnDiagPS_id = 0;
			params.EvnDiagPS_setDate = base_form.findField('EvnPS_setDate').getValue();
			params.EvnDiagPS_setTime = base_form.findField('EvnPS_setTime').getValue();
			params.Person_id = base_form.findField('Person_id').getValue();
			params.PersonEvn_id = base_form.findField('PersonEvn_id').getValue();
			params.Server_id = base_form.findField('Server_id').getValue();

			switch ( type ) {
				case 'hosp':
				case 'recep':
					params.EvnDiagPS_pid = base_form.findField('EvnPS_id').getValue();
					break;
			}
		}
		else {
			var selected_record = grid.getSelectionModel().getSelected();

			if ( !selected_record || !selected_record.get('EvnDiagPS_id') ) {
				return false;
			}

			params = selected_record.data;
		}

		getWnd('swEvnDiagPSEditWindow').show({
			action: action,
			callback: function(data) {
				if ( !data || !data.evnDiagPSData ) {
					return false;
				}

				var record = grid.getStore().getById(data.evnDiagPSData[0].EvnDiagPS_id);

				if ( !record ) {
					if ( grid.getStore().getCount() == 1 && !grid.getStore().getAt(0).get('EvnDiagPS_id') ) {
						grid.getStore().removeAll();
					}

					grid.getStore().loadData(data.evnDiagPSData, true);
				}
				else {
					var evn_diag_ps_fields = new Array();
					var i = 0;

					grid.getStore().fields.eachKey(function(key, item) {
						evn_diag_ps_fields.push(key);
					});

					for ( i = 0; i < evn_diag_ps_fields.length; i++ ) {
						record.set(evn_diag_ps_fields[i], data.evnDiagPSData[0][evn_diag_ps_fields[i]]);
					}

					record.commit();
				}

				switch ( type ) {
					case 'hosp':
						if ( !this.findById('EPSEF_AdmitDepartPanel').hidden ) {
							this.findById('EPSEF_EvnDiagPSRecepGrid').getStore().load({
								params: {
									'class': 'EvnDiagPSRecep',
									'EvnDiagPS_pid': base_form.findField('EvnPS_id').getValue()
								}
							});
						}
						break;
				}
			}.createDelegate(this),
			formParams: params,
			onHide: function() {
				grid.getView().focusRow(0);
				grid.getSelectionModel().selectFirstRow();
			}.createDelegate(this),
			Person_Birthday: this.findById('EPSEF_PersonInformationFrame').getFieldValue('Person_Birthday'),
			Person_Firname: this.findById('EPSEF_PersonInformationFrame').getFieldValue('Person_Firname'),
			Person_id: base_form.findField('Person_id').getValue(),
			Person_Secname: this.findById('EPSEF_PersonInformationFrame').getFieldValue('Person_Secname'),
			Person_Surname: this.findById('EPSEF_PersonInformationFrame').getFieldValue('Person_Surname'),
			type: type
		});
	},
	openEvnDirectionSelectWindow: function() {
		if ( this.action == 'view') {
			return false;
		}

		if ( getWnd('swEvnDirectionSelectWindow').isVisible() ) {
			sw.swMsg.alert(lang['soobschenie'], lang['okno_vyibora_napravleniya_uje_otkryito']);
			return false;
		}

		var base_form = this.findById('EvnPSEditForm').getForm();

		if ( !base_form.findField('EvnPS_setDate').getValue() ) {
			sw.swMsg.alert(lang['oshibka'], lang['ne_ukazana_data_gospitalizatsii'], function() {base_form.findField('EvnPS_setDate').focus();});
			return false;
		}

		getWnd('swEvnDirectionSelectWindow').show({
			callback: this.setDirection,
			onDate: base_form.findField('EvnPS_setDate').getValue(),
			onHide: function() {
				base_form.findField('PrehospArrive_id').focus(true);
			}.createDelegate(this),
			parentClass: 'EvnPS',
			Person_Birthday: this.findById('EPSEF_PersonInformationFrame').getFieldValue('Person_Birthday'),
			Person_Firname: this.findById('EPSEF_PersonInformationFrame').getFieldValue('Person_Firname'),
			Person_id: base_form.findField('Person_id').getValue(),
			Person_Secname: this.findById('EPSEF_PersonInformationFrame').getFieldValue('Person_Secname'),
			Person_Surname: this.findById('EPSEF_PersonInformationFrame').getFieldValue('Person_Surname')
		});
	},
	openEvnDrugEditWindow: function(action) {
		if ( this.findById('EPSEF_EvnDrugPanel').hidden || this.findById('EPSEF_EvnDrugPanel').collapsed ) {
			return false;
		}

		if ( action != 'add' && action != 'edit' && action != 'view' ) {
			return false;
		}

		var base_form = this.findById('EvnPSEditForm').getForm();
		var grid = this.findById('EPSEF_EvnDrugGrid');

		if ( this.action == 'view') {
			if ( action == 'add') {
				return false;
			}
			else if ( action == 'edit' ) {
				action = 'view';
			}
		}

		if ( getWnd(getEvnDrugEditWindowName()).isVisible() ) {
			sw.swMsg.alert(lang['soobschenie'], lang['okno_dobavleniya_sluchaya_ispolzovaniya_medikamentov_uje_otkryito']);
			return false;
		}

		if ( action == 'add' && base_form.findField('EvnPS_id').getValue() == 0 ) {
			this.doSave({
				callback: function() {
					this.openEvnDrugEditWindow(action);
				}.createDelegate(this),
				print: false
			});
			return false;
		}

		var parent_evn_combo_data = new Array();

		this.findById('EPSEF_EvnSectionGrid').getStore().each(function(rec) {
			parent_evn_combo_data.push({
				Evn_id: rec.get('EvnSection_id'),
				Evn_Name: Ext.util.Format.date(rec.get('EvnSection_setDate'), 'd.m.Y') + ' / ' + rec.get('LpuSection_Name') + ' / ' + rec.get('MedPersonal_Fio'),
				Evn_setDate: rec.get('EvnSection_setDate'),
				Evn_disDate: rec.get('EvnSection_disDate'),// TODO: Дата выписки пациентов, отправляем в swEvnDrugEditWindow.js
				MedStaffFact_id: rec.get('MedStaffFact_id'),
				Lpu_id: rec.get('Lpu_id'),
				LpuSection_id: rec.get('LpuSection_id'),
				MedPersonal_id: rec.get('MedPersonal_id')
			})
		});

		var formParams = new Object();
		var params = new Object();
		var person_id = this.findById('EPSEF_PersonInformationFrame').getFieldValue('Person_id');
		var person_birthday = this.findById('EPSEF_PersonInformationFrame').getFieldValue('Person_Birthday');
		var person_firname = this.findById('EPSEF_PersonInformationFrame').getFieldValue('Person_Firname');
		var person_secname = this.findById('EPSEF_PersonInformationFrame').getFieldValue('Person_Secname');
		var person_surname = this.findById('EPSEF_PersonInformationFrame').getFieldValue('Person_Surname');

		params.action = action;
		params.parentEvnComboData = parent_evn_combo_data;
		params.callback = function(data) {
			if ( !data || !data.evnDrugData ) {
				return false;
			}
			var grid = this.findById('EPSEF_EvnDrugGrid');
			var record = grid.getStore().getById(data.evnDrugData.EvnDrug_id);

			if ( !record ) {
				if ( grid.getStore().getCount() == 1 && !grid.getStore().getAt(0).get('EvnDrug_id') ) {
					grid.getStore().removeAll();
				}
				grid.getStore().loadData([data.evnDrugData], true);
			}
			else {
				//
				var grid_fields = new Array();
				var i = 0;

				grid.getStore().fields.eachKey(function(key, item) {
					grid_fields.push(key);
				});

				for ( i = 0; i < grid_fields.length; i++ ) {
					record.set(grid_fields[i], data.evnDrugData[grid_fields[i]]);
				}

				record.commit();
			}
		}.createDelegate(this);
		params.onHide = function() {
			grid.getView().focusRow(0);
			grid.getSelectionModel().selectFirstRow();
		}.createDelegate(this);
		params.Person_id = person_id;
		params.Person_Birthday = person_birthday;
		params.Person_Firname = person_firname;
		params.Person_Secname = person_secname;
		params.Person_Surname = person_surname;

		formParams.Person_id = base_form.findField('Person_id').getValue();
		formParams.PersonEvn_id = base_form.findField('PersonEvn_id').getValue();
		formParams.Server_id = base_form.findField('Server_id').getValue();

		if ( action == 'add' ) {
			formParams.EvnDrug_id = 0;
			formParams.EvnDrug_pid = base_form.findField('EvnPS_id').getValue();
		}
		else {
			var selected_record = grid.getSelectionModel().getSelected();

			if ( !selected_record || !selected_record.get('EvnDrug_id') ) {
				return false;
			}

			formParams.EvnDrug_id = selected_record.get('EvnDrug_id');
			formParams.EvnDrug_rid = base_form.findField('EvnPS_id').getValue();
		}

		params.formParams = formParams;

		getWnd(getEvnDrugEditWindowName()).show(params);
	},
	openEvnSectionEditWindow: function(action) {
		if ( this.findById('EPSEF_EvnSectionPanel').hidden ) {
			return false;
		}

		if ( action != 'add' && action != 'edit' && action != 'view' ) {
			return false;
		}

		var _this = this;
		var base_form = this.findById('EvnPSEditForm').getForm();
		var grid = this.findById('EPSEF_EvnSectionGrid');
		var last_evn_section_info = this.getEvnSectionInfo('last');
		var record;

		// Проверяем возможность добавлять новое движение, если в списке уже есть движения по отделениям
		if ( action == 'add' && last_evn_section_info.EvnSection_id ) {
			if ( !last_evn_section_info.LeaveType_Code ) {
				sw.swMsg.alert(lang['oshibka'], lang['dobavlenie_dvijeniya_nevozmojno_t_k_patsient_ne_vyipisan_iz_predyiduschego_otdeleniya']);
				return false;
			}
			else if ( !last_evn_section_info.LeaveType_Code.toString().inlist([ '5', '104', '204' ]) ) {
				sw.swMsg.alert(lang['oshibka'], lang['dobavlenie_dvijeniya_nevozmojno_t_k_ishod_gospitalizatsii_v_predyiduschem_otdelenii_oznachaet_zavershenie_sluchaya_lecheniya']);
				return false;
			}
		}

		var evn_ps_set_dt = getValidDT(Ext.util.Format.date(base_form.findField('EvnPS_setDate').getValue(), 'd.m.Y'), base_form.findField('EvnPS_setTime').getValue());
		var evn_ps_outcome_dt = getValidDT(Ext.util.Format.date(base_form.findField('EvnPS_OutcomeDate').getValue(), 'd.m.Y'), base_form.findField('EvnPS_OutcomeTime').getValue() ? base_form.findField('EvnPS_OutcomeTime').getValue() : '');

		if ( evn_ps_set_dt == null ) {
			sw.swMsg.alert(lang['oshibka'], lang['nevernoe_znachenie_datyi_vremeni_gospitalizatsii']);
			return false;
		}

		if ( this.action == 'view') {
			if ( action == 'add') {
				return false;
			}
			else if ( action == 'edit' ) {
				action = 'view';
			}
		}

		var isChange = (
			this.isChange('PrehospType_id') || this.isChange('PayType_id')
			|| this.isChange('EvnDirection_Num') || this.isChange('EvnDirection_setDate')
			|| this.isChange('PrehospDirect_id')
		);

		if ( (action == 'add' && Number(base_form.findField('EvnPS_id').getValue()) == 0) || isChange ) {
			this.doSave({
				callback: function() {
					this.findById('EPSEF_EvnSectionPanel').isLoaded = true;

					if ( Number(base_form.findField('EvnPS_id').getValue()) > 0 ) {
						grid.getStore().load({
							callback: function() {
								if ( action == 'add' && grid.getStore().getCount() == 1 && !Ext.isEmpty(grid.getStore().getAt(0).get('EvnSection_id')) ) {
									action = 'edit';
									grid.getView().focusRow(0);
									grid.getSelectionModel().selectFirstRow();
								}

								this.openEvnSectionEditWindow(action);
							}.createDelegate(this),
							params: {
								EvnSection_pid: this.findById('EvnPSEditForm').getForm().findField('EvnPS_id').getValue()
							}
						});
					}
					else {
						this.openEvnSectionEditWindow(action);
					}
				}.createDelegate(this),
				print: false
			});
			return false;
		}

		var formParams = new Object();
		var params = new Object();

		var person_id = this.findById('EPSEF_PersonInformationFrame').getFieldValue('Person_id');
		var person_birthday = this.findById('EPSEF_PersonInformationFrame').getFieldValue('Person_Birthday');
		var person_firname = this.findById('EPSEF_PersonInformationFrame').getFieldValue('Person_Firname');
		var person_secname = this.findById('EPSEF_PersonInformationFrame').getFieldValue('Person_Secname');
		var person_surname = this.findById('EPSEF_PersonInformationFrame').getFieldValue('Person_Surname');

		var lpu_section_eid = this.findById('EPSEF_LpuSectionCombo').getValue();

		params.action = action;
		params.callback = function(data) {
			if ( !data || !data.evnSectionData ) {
				return false;
			}
			record = grid.getStore().getById(data.evnSectionData[0].EvnSection_id);
			if (data.evnSectionData[0].deleted) {
				if (record) {
					grid.getStore().reload();
				}
				return;
			}
			var next_evn_section_info = this.getEvnSectionInfo('next', {
				EvnSection_id: data.evnSectionData[0].EvnSection_id,
				EvnSection_setDT: getValidDT(Ext.util.Format.date(data.evnSectionData[0].EvnSection_setDate, 'd.m.Y'), data.evnSectionData[0].EvnSection_setTime ? data.evnSectionData[0].EvnSection_setTime : '')
			});
			if ( !record ) {
				if ( grid.getStore().getCount() == 1 && !grid.getStore().getAt(0).get('EvnSection_id') ) {
					grid.getStore().removeAll();
				}

				data.evnSectionData[0].EvnSection_IsSigned = 1;
				data.evnSectionData[0].EvnSection_IsPaid = 1;

				grid.getStore().loadData(data.evnSectionData, true);
			}
			else {
				var evn_section_fields = new Array();
				var i = 0;

				data.evnSectionData[0].EvnSection_IsSigned = record.get('EvnSection_IsSigned');
				data.evnSectionData[0].EvnSection_IsPaid = record.get('EvnSection_IsPaid');

				grid.getStore().fields.eachKey(function(key, item) {
					evn_section_fields.push(key);
				});

				for ( i = 0; i < evn_section_fields.length; i++ ) {
					record.set(evn_section_fields[i], data.evnSectionData[0][evn_section_fields[i]]);
				}

				record.commit();
			}

			var LastEvnSection =  _this.getEvnSectionInfo('last');
			grid.getStore().each(function(rec) {
				if ( rec.get('EvnSection_id') == LastEvnSection['EvnSection_id'] ) {
					rec.set('isLast', 1);
				} else {
					rec.set('isLast', 0);
				}
				rec.commit();
			});

			if ( next_evn_section_info.EvnSection_id > 0 ) {
				grid.getStore().each(function(rec) {
					if ( rec.get('EvnSection_id') == next_evn_section_info.EvnSection_id ) {
						rec.set('EvnSection_setDate', data.evnSectionData[0].EvnSection_disDate);
						rec.set('EvnSection_setTime', data.evnSectionData[0].EvnSection_disTime);
						rec.commit();
					}
				});
			}

			this.BirthWeight = data.evnSectionData[0].birthWeight;
			this.PersonWeight_text = data.evnSectionData[0].PersonWeight_text;
			this.Okei_id = data.evnSectionData[0].Okei_id;
			this.BirthHeight = data.evnSectionData[0].birthHeight;
			this.countChild = data.evnSectionData[0].countChild;
			this.setEvnPSOutcomeDT();
		}.createDelegate(this);
		// params.EvnLeave_setDT = evn_leave_set_dt;
		params.EvnPS_setDT = evn_ps_set_dt;
		params.onHide = function(options) {
			if ( this.findById('EPSEF_EvnUslugaPanel').isLoaded === true && options.EvnUslugaGridIsModified === true ) {
				this.findById('EPSEF_EvnUslugaGrid').getStore().load({
					params: {
						pid: base_form.findField('EvnPS_id').getValue()
					}
				});
			}

			grid.getView().focusRow(0);
			grid.getSelectionModel().selectFirstRow();
		}.createDelegate(this);
		params.onChangeLpuSectionWard = this.onChangeLpuSectionWard;
		params.Person_id = person_id;
		params.Person_Birthday = person_birthday;
		params.Person_Firname = person_firname;
		params.Person_Secname = person_secname;
		params.Person_Surname = person_surname;
		params.DiagPred_id = null;
		params.EvnUsluga_rid = base_form.findField('EvnPS_id').getValue();
		params.PrehospType_id = base_form.findField('PrehospType_id').getValue();
        params.EvnPS_IsWithoutDirection = base_form.findField('EvnPS_IsWithoutDirection').getValue();

		var first_evn_section_info = this.getEvnSectionInfo('first');

		if ( action == 'add' ) {
			params.evnSectionIsFirst = false;
			params.evnSectionIsLast = true;

			if ( base_form.findField('Diag_pid').getValue() ) {
				formParams.Diag_id = base_form.findField('Diag_pid').getValue();
			}

			if ( base_form.findField('DiagSetPhase_pid').getValue() ) {
				formParams.DiagSetPhase_id = base_form.findField('DiagSetPhase_pid').getValue();
			}

			if ( grid.getStore().getCount() == 0 || (grid.getStore().getCount() == 1 && !grid.getStore().getAt(0).get('EvnSection_id')) ) {
				// formParams.EvnSection_disDate = this.params.EvnLeave_setDate;
				params.evnSectionIsFirst = true;
			}

			formParams.EvnSection_id = 0;
			formParams.EvnSection_pid = base_form.findField('EvnPS_id').getValue();
			formParams.PayType_id = base_form.findField('PayType_id').getValue();
			formParams.Person_id = base_form.findField('Person_id').getValue();
			formParams.PersonEvn_id = base_form.findField('PersonEvn_id').getValue();
			formParams.Server_id = base_form.findField('Server_id').getValue();
			formParams.LpuSection_eid = lpu_section_eid;

			if ( params.evnSectionIsFirst == false ) {
				formParams.EvnSection_setDate = (typeof last_evn_section_info.EvnSection_disDT == 'object' ? Ext.util.Format.date(last_evn_section_info.EvnSection_disDT, 'd.m.Y') : Ext.util.Format.date(last_evn_section_info.EvnSection_setDT, 'd.m.Y'));
				formParams.EvnSection_setTime = (typeof last_evn_section_info.EvnSection_disDT == 'object' ? Ext.util.Format.date(last_evn_section_info.EvnSection_disDT, 'H:i') : Ext.util.Format.date(last_evn_section_info.EvnSection_setDT, 'H:i'));
				params.DiagPred_id = last_evn_section_info.Diag_id;
			}
			else {
				formParams.EvnSection_setDate = Ext.util.Format.date((!Ext.isEmpty(evn_ps_outcome_dt) ? evn_ps_outcome_dt : evn_ps_set_dt), 'd.m.Y');
				formParams.EvnSection_setTime = Ext.util.Format.date((!Ext.isEmpty(evn_ps_outcome_dt) ? evn_ps_outcome_dt : evn_ps_set_dt), 'H:i');
				params.DiagPred_id = base_form.findField('Diag_pid').getValue();
			}

			if ( this.params.EvnLeave_UKL ) {
				formParams.EvnLeave_UKL = this.params.EvnLeave_UKL;
			}

			if ( this.params.EvnLeave_setDate ) {
				formParams.EvnSection_disDate = this.params.EvnLeave_setDate;
			}

			if ( this.params.LeaveCause_id ) {
				formParams.LeaveCause_id = this.params.LeaveCause_id;
			}

			if ( this.params.LeaveType_id ) {
				formParams.LeaveType_id = this.params.LeaveType_id;
			}
			
			if ( this.params.LeaveTypeFed_id ) {
				formParams.LeaveTypeFed_id = this.params.LeaveTypeFed_id;
			}

			if ( this.params.LpuSection_id ) {
				formParams.LpuSection_id = this.params.LpuSection_id;
			}

			if ( this.params.MedPersonal_id ) {
				formParams.MedPersonal_id = this.params.MedPersonal_id;
			}

			if ( this.params.ResultDesease_id ) {
				formParams.ResultDesease_id = this.params.ResultDesease_id;
			}

			if ( this.params.TariffClass_id ) {
				formParams.TariffClass_id = this.params.TariffClass_id;
			}
		}
		else {
			var selected_record = grid.getSelectionModel().getSelected();

			if ( !selected_record || !selected_record.get('EvnSection_id') ) {
				return false;
			}

			if ( selected_record.get('accessType') != 'edit'
				|| (
					!Ext.isEmpty(getGlobalOptions().medpersonal_id)
					&& !Ext.isEmpty(selected_record.get('MedPersonal_id'))
					&& userHasWorkPlaceAtLpuSection(selected_record.get('LpuSection_id')) == false
					&& getGlobalOptions().isMedStatUser != true
					&& isSuperAdmin() != true
				)
				//|| selected_record.get('isLast') != 1
				|| selected_record.get('EvnSection_IsSigned') != 1
			) {
				params.action = 'view';
			}

			var evn_section_set_dt = getValidDT(Ext.util.Format.date(selected_record.get('EvnSection_setDate'), 'd.m.Y'), selected_record.get('EvnSection_setTime'));

			params.evnSectionIsFirst = true;
			params.evnSectionIsLast = true;

			grid.getStore().each(function(rec) {
				if ( rec.get('EvnSection_id') != selected_record.get('EvnSection_id') ) {
					var set_dt = getValidDT(Ext.util.Format.date(rec.get('EvnSection_setDate'), 'd.m.Y'), rec.get('EvnSection_setTime'));

					if ( set_dt < evn_section_set_dt ) {
						params.evnSectionIsFirst = false;
					}
					else if ( set_dt > evn_section_set_dt ) {
						params.evnSectionIsLast = false;
					}
				}
			});
			formParams = selected_record.data;
			params.DiagPred_id = base_form.findField('Diag_pid').getValue();

			params.evnSectionIsFirst = (first_evn_section_info.EvnSection_id == selected_record.get('EvnSection_id'));

			if( !params.evnSectionIsFirst ) {
				var prev_evn_section_info = this.getEvnSectionInfo('prev', {
					EvnSection_id: selected_record.get('EvnSection_id'),
					EvnSection_setDT: evn_section_set_dt
				});

				if( prev_evn_section_info.Diag_id )
					params.DiagPred_id = prev_evn_section_info.Diag_id;
			}
		}

		params.formParams = formParams;

		params.EvnPS_OutcomeDate = base_form.findField('EvnPS_OutcomeDate').getValue();
		params.EvnPS_OutcomeTime = base_form.findField('EvnPS_OutcomeTime').getValue();
		params.LpuSection_eid = base_form.findField('LpuSection_eid').getValue();
		params.LpuSection_pid = base_form.findField('LpuSection_pid').getValue();
		params.EvnPS_NumCard = base_form.findField('EvnPS_NumCard').getValue();
		params.EvnPS_setDate = base_form.findField('EvnPS_setDate').getValue();

		if ( this.childPS ) {
			//наличие этой переменной как бы намекает нам, что окно КВС было вызвано из поиска человека,
			// который был вызван из поиска КВС,
			//  который был вызван из Движения,
			//   которое было вызвано из редактирования КВС матери. Так-то!
			// Поэтому открываемое окно движения будет вторым по счету, и открывать его надо с другим идентификатором.
			params.childPS = true;
			if (this.ChildTermType_id){
				params.ChildTermType_id = this.ChildTermType_id;
			}
			if (this.BirthSpecStac_CountChild){
				params.BirthSpecStac_CountChild = this.BirthSpecStac_CountChild;
			}
			if (this.PersonChild_IsAidsMother){
				params.PersonChild_IsAidsMother = this.PersonChild_IsAidsMother;
			}
			getWnd({objectName:'swEvnSectionEditWindow2', objectClass:'swEvnSectionEditWindow'},{params:{id:'EvnSectionEditWindow2'}}).show(params);
		} else {
			getWnd('swEvnSectionEditWindow').show(params);
		};
	},
	openEvnStickEditWindow: function(action) {
		if ( this.findById('EPSEF_EvnStickPanel').hidden ) {
			return false;
		}

		if ( action != 'add' && action != 'edit' && action != 'view' ) {
			return false;
		}

		var base_form = this.findById('EvnPSEditForm').getForm();
		var grid = this.findById('EPSEF_EvnStickGrid');

		if ( this.action == 'view') {
			if ( action == 'add') {
				return false;
			}
			else if ( action == 'edit' ) {
				action = 'view';
			}
		}

		if ( action == 'add' && base_form.findField('EvnPS_id').getValue() == 0 ) {
			this.doSave({
				callback: function() {
					this.openEvnStickEditWindow(action);
				}.createDelegate(this),
				print: false
			});
			return false;
		}

		var formParams = new Object();
		var joborg_id = this.findById('EPSEF_PersonInformationFrame').getFieldValue('JobOrg_id');
		var params = new Object();
		var person_id = this.findById('EPSEF_PersonInformationFrame').getFieldValue('Person_id');
		var person_birthday = this.findById('EPSEF_PersonInformationFrame').getFieldValue('Person_Birthday');
		var person_firname = this.findById('EPSEF_PersonInformationFrame').getFieldValue('Person_Firname');
		var person_post = this.findById('EPSEF_PersonInformationFrame').getFieldValue('Person_Post');
		var person_secname = this.findById('EPSEF_PersonInformationFrame').getFieldValue('Person_Secname');
		var person_surname = this.findById('EPSEF_PersonInformationFrame').getFieldValue('Person_Surname');

		params.action = action;
		params.callback = function(data) {
			if ( !data || !data.evnStickData ) {
				return false;
			}

			var record = grid.getStore().getById(data.evnStickData[0].EvnStick_id);

			if ( !record ) {
				if ( grid.getStore().getCount() == 1 && !grid.getStore().getAt(0).get('EvnStick_id') ) {
					grid.getStore().removeAll();
				}

				grid.getStore().loadData(data.evnStickData, true);
			}
			else {
				var evn_stick_fields = new Array();
				var i = 0;

				grid.getStore().fields.eachKey(function(key, item) {
					evn_stick_fields.push(key);
				});

				for ( i = 0; i < evn_stick_fields.length; i++ ) {
					record.set(evn_stick_fields[i], data.evnStickData[0][evn_stick_fields[i]]);
				}

				record.commit();
			}
		}.createDelegate(this);

		var lastEvnSection = this.getEvnSectionInfo('last');

		params.JobOrg_id = joborg_id;
		params.parentClass = 'EvnPS';
		params.Person_id = base_form.findField('Person_id').getValue();
		params.PersonEvn_id = base_form.findField('PersonEvn_id').getValue();
		params.Person_Birthday = person_birthday;
		params.Person_Firname = person_firname;
		params.Person_Secname = person_secname;
		params.Person_Surname = person_surname;
		params.Person_Post = person_post;
		params.Server_id = base_form.findField('Server_id').getValue();
		params.LpuUnitType_SysNick = lastEvnSection.LpuUnitType_SysNick;

		formParams.EvnStick_mid = base_form.findField('EvnPS_id').getValue();

		params.stacBegDate = null;
		params.stacEndDate = null;
		var emptyEndDate = false;

		this.findById('EPSEF_EvnSectionGrid').getStore().each(function(rec) {
			if (params.stacBegDate > Ext.util.Format.date(rec.get('EvnSection_setDate'), 'd.m.Y') || params.stacBegDate == null) {
				params.stacBegDate = Ext.util.Format.date(rec.get('EvnSection_setDate'), 'd.m.Y');
			}

			if (params.stacEndDate < Ext.util.Format.date(rec.get('EvnSection_disDate'), 'd.m.Y') || params.stacEndDate == null) {
				if (rec.get('EvnSection_disDate').length == 0) {emptyEndDate = true;}
				params.stacEndDate = Ext.util.Format.date(rec.get('EvnSection_disDate'), 'd.m.Y');
			}
		});

		if (emptyEndDate) {
			params.stacEndDate = null;
		}

		if ( action == 'add' ) {
			var evn_stick_beg_date = base_form.findField('EvnPS_setDate').getValue();
			var evn_section_store = this.findById('EPSEF_EvnSectionGrid').getStore();

			evn_section_store.each(function(record) {
				if ( evn_stick_beg_date == null || record.get('EvnSection_setDate') <= evn_stick_beg_date ) {
					evn_stick_beg_date = record.get('EvnSection_setDate');
				}
			});

			formParams.EvnStick_pid = base_form.findField('EvnPS_id').getValue();
			formParams.EvnStick_setDate = evn_stick_beg_date;
			formParams.Person_id = base_form.findField('Person_id').getValue();
			formParams.PersonEvn_id = base_form.findField('PersonEvn_id').getValue();
			formParams.Server_id = base_form.findField('Server_id').getValue();

			params.formParams = formParams;

			getWnd('swEvnStickChangeWindow').show(params);
		}
		else {
			var selected_record = grid.getSelectionModel().getSelected();

			if ( !selected_record || !selected_record.get('EvnStick_id') ) {
				return false;
			}

			if ( selected_record.get('accessType') != 'edit' ) {
				params.action = 'view';
			}

			formParams.EvnStick_id = selected_record.get('EvnStick_id');
			formParams.EvnStick_pid = selected_record.get('EvnStick_pid');
			formParams.Person_id = selected_record.get('Person_id');
			formParams.Server_id = selected_record.get('Server_id');

			params.evnStickType = selected_record.get('evnStickType');
			params.formParams = formParams;
			params.onHide = function() {
				grid.getView().focusRow(grid.getStore().indexOf(selected_record));
			}.createDelegate(this);
			//params.parentClass = selected_record.get('parentClass');
			params.parentNum = selected_record.get('EvnStick_ParentNum');

			switch ( selected_record.get('evnStickType') ) {
				case 1:
				case 2:
					getWnd('swEvnStickEditWindow').show(params);
					break;

				case 3:
					getWnd('swEvnStickStudentEditWindow').show(params);
					break;

				default:
					return false;
					break;
			}
		}
	},
	openEvnUslugaEditWindow: function(action) {
		if ( this.findById('EPSEF_EvnUslugaPanel').hidden ) {
			return false;
		}

		if ( action != 'add' && action != 'addOper' && action != 'edit' && action != 'view' ) {
			return false;
		}

		var base_form = this.findById('EvnPSEditForm').getForm();
		var grid = this.findById('EPSEF_EvnUslugaGrid');

		if ( this.action == 'view' ) {
			if ( action == 'add' || action == 'addOper' ) {
				return false;
			}
			else if ( action == 'edit' ) {
				action = 'view';
			}
		}

		var params = new Object();

		params.action = action;
		params.callback = function(data) {
			if ( !data || !data.evnUslugaData ) {
				grid.getStore().load({
					params: {
						pid: base_form.findField('EvnPS_id').getValue()
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
				var evn_usluga_fields = new Array();
				var i = 0;

				grid.getStore().fields.eachKey(function(key, item) {
					evn_usluga_fields.push(key);
				});

				for ( i = 0; i < evn_usluga_fields.length; i++ ) {
					record.set(evn_usluga_fields[i], data.evnUslugaData[evn_usluga_fields[i]]);
				}

				record.commit();
			}
		}.createDelegate(this);
		params.onHide = function() {
			if ( grid.getSelectionModel().getSelected() ) {
				grid.getView().focusRow(grid.getStore().indexOf(grid.getSelectionModel().getSelected()));
			}
			else {
				grid.getView().focusRow(0);
				grid.getSelectionModel().selectFirstRow();
			}
		}.createDelegate(this);
		params.parentClass = 'EvnPS';
		params.Person_id = this.findById('EPSEF_PersonInformationFrame').getFieldValue('Person_id');
		params.Person_Birthday = this.findById('EPSEF_PersonInformationFrame').getFieldValue('Person_Birthday');
		params.Person_Firname = this.findById('EPSEF_PersonInformationFrame').getFieldValue('Person_Firname');
		params.Person_Secname = this.findById('EPSEF_PersonInformationFrame').getFieldValue('Person_Secname');
		params.Person_Surname = this.findById('EPSEF_PersonInformationFrame').getFieldValue('Person_Surname');

		// Собрать данные для ParentEvnCombo
		var parent_evn_combo_data = new Array();

		this.findById('EPSEF_EvnSectionGrid').getStore().each(function(rec) {
			parent_evn_combo_data.push({
				Evn_id: rec.get('EvnSection_id'),
				Evn_Name: Ext.util.Format.date(rec.get('EvnSection_setDate'), 'd.m.Y') + ' / ' + rec.get('LpuSection_Name') + ' / ' + rec.get('MedPersonal_Fio'),
				Evn_setDate: rec.get('EvnSection_setDate'),
				Evn_disDate: rec.get('EvnSection_disDate'),
				Evn_setTime: rec.get('EvnSection_setTime'),
				MedStaffFact_id: rec.get('MedStaffFact_id'),
				LpuSection_id: rec.get('LpuSection_id'),
				MedPersonal_id: rec.get('MedPersonal_id')
			})
		});

		switch ( action ) {
			case 'addOper':
			case 'add':
				params.action = 'add';
				if ( base_form.findField('EvnPS_id').getValue() == 0 ) {
					this.doSave({
						callback: function() {
							this.openEvnUslugaEditWindow(action);
						}.createDelegate(this),
						print: false
					});
					return false;
				}

				params.formParams = {
					PayType_id: base_form.findField('PayType_id').getValue(),
					Person_id: base_form.findField('Person_id').getValue(),
					PersonEvn_id: base_form.findField('PersonEvn_id').getValue(),
					Server_id: base_form.findField('Server_id').getValue()
				}
				params.parentEvnComboData = parent_evn_combo_data;

				if ( action == 'addOper' ){
					getWnd('swEvnUslugaOperEditWindow').show(params);
				} else {
					getWnd('swEvnUslugaEditWindow').show(params);
				}
				break;

			case 'edit':
			case 'view':
				// Открываем форму редактирования услуги (в зависимости от EvnClass_SysNick)

				var selected_record = grid.getSelectionModel().getSelected();

				if ( !selected_record || !selected_record.get('EvnUsluga_id') ) {
					return false;
				}

				var evn_usluga_id = selected_record.get('EvnUsluga_id');

				switch ( selected_record.get('EvnClass_SysNick') ) {
					case 'EvnUslugaOnkoBeam':
					case 'EvnUslugaOnkoChem':
					case 'EvnUslugaOnkoGormun':
					case 'EvnUslugaCommon':
						params.formParams = {
							EvnUslugaCommon_id: evn_usluga_id
						}
						params.parentEvnComboData = parent_evn_combo_data;

						getWnd('swEvnUslugaEditWindow').show(params);
					break;
					case 'EvnUslugaOnkoSurg':
						params.EvnUslugaOnkoSurg_id = evn_usluga_id;
						params.formParams = {
							EvnUslugaOnkoSurg_id: evn_usluga_id
						}
						getWnd('swEvnUslugaOnkoSurgEditWindow').show(params);
						break;
					case 'EvnUslugaOper':
						params.formParams = {
							EvnUslugaOper_id: evn_usluga_id
						}
						params.parentEvnComboData = parent_evn_combo_data;
						getWnd('swEvnUslugaOperEditWindow').show(params);
					break;

					case 'EvnUslugaPar':
						params.formParams = {
							EvnUslugaPar_id: evn_usluga_id
						}
						params.parentEvnComboData = parent_evn_combo_data;
						getWnd('swEvnUslugaParSimpleEditWindow').show(params);
						break;
						
					default:
						return false;
					break;
				}
				/*
				 if ( evn_usluga_edit_window.isVisible() ) {
				 sw.swMsg.alert(lang['soobschenie'], lang['okno_redaktirovaniya_uslugi_uje_otkryito'], function() {
				 grid.getSelectionModel().selectFirstRow();
				 grid.getView().focusRow(0);
				 });
				 return false;
				 }
				 */

				break;
		}
	},
	openPrehospWaifInspectionEditWindow: function(action) {
		if ( this.findById('EPSEF_PrehospWaifPanel').hidden ) {
			return false;
		}

		if ( action != 'add' && action != 'edit' && action != 'view' ) {
			return false;
		}

		if ( this.action == 'view' ) {
			if ( action == 'add' ) {
				return false;
			}
			else if ( action == 'edit' ) {
				action = 'view';
			}
		}

		var base_form = this.findById('EvnPSEditForm').getForm();
		var view_frame = this.findById('EPSEF_PrehospWaifInspection');
		var grid = view_frame.getGrid();

		if ( getWnd('swPrehospWaifInspectionEditWindow').isVisible() )
		{
			sw.swMsg.alert(lang['soobschenie'], lang['okno_redaktirovaniya_osmotra_uje_otkryito'], function() {
				grid.getSelectionModel().selectFirstRow();
				grid.getView().focusRow(0);
			});
			return false;
		}

		var params = new Object();

		params.action = action;
		params.MedStaffFact_id = getGlobalOptions().CurMedStaffFact_id;
		params.LpuSection_id  = getGlobalOptions().CurLpuSection_id;
		params.PrehospWaifInspection_SetDT = base_form.findField('EvnPS_setDate').getValue();
		params.EvnPS_id = base_form.findField('EvnPS_id').getValue();
		params.Diag_id = base_form.findField('Diag_pid').getValue();
		params.callback = this.PrehospWaifInspectionRefreshGrid;
		params.onHide = function() {
			if ( grid.getSelectionModel().getSelected() ) {
				grid.getView().focusRow(grid.getStore().indexOf(grid.getSelectionModel().getSelected()));
			}
			else {
				grid.getView().focusRow(0);
				grid.getSelectionModel().selectFirstRow();
			}
		};

		switch ( action ) {
			case 'add':
				if ( base_form.findField('EvnPS_id').getValue() == 0 ) {
					this.doSave({
						callback: function() {
							this.openPrehospWaifInspectionEditWindow(action);
						}.createDelegate(this),
						print: false
					});
					return false;
				}

				getWnd('swPrehospWaifInspectionEditWindow').show(params);
				break;

			case 'edit':
			case 'view':
				var record = grid.getSelectionModel().getSelected();
				if ( record )
				{
					params.PrehospWaifInspection_id = record.get('PrehospWaifInspection_id');
					getWnd('swPrehospWaifInspectionEditWindow').show(params);
				}
				else
				{
					sw.swMsg.alert(lang['soobschenie'], lang['vyi_ne_vyibrali_osmotr'], function() {
						grid.focus();
					});
				}
				break;
		}
	},
	params: {
		EvnLeave_setDate: null,
		EvnLeave_UKL: null,
		LeaveCause_id: null,
		LeaveType_id: null,
		LeaveTypeFed_id: null,
		LpuSection_id: null,
		MedPersonal_id: null,
		ResultDesease_id: null,
		TariffClass_id: null
	},
	plain: true,
	PrehospWaifInspectionRefreshGrid: function()
	{
		if ( Ext.getCmp('EPSEF_PrehospWaifPanel').hidden ) {
			return false;
		}

		var base_form = Ext.getCmp('EvnPSEditForm').getForm();
		if ( this.action == 'add' && base_form.findField('EvnPS_id').getValue() == 0 ) {
			this.doSave({
				callback: function() {
					this.PrehospWaifInspectionRefreshGrid();
				}.createDelegate(this),
				print: false
			});
			return false;
		}
		var view_frame = Ext.getCmp('EPSEF_PrehospWaifInspection');
		view_frame.removeAll(true);
		var params = {EvnPS_id: base_form.findField('EvnPS_id').getValue()};
		params.start = 0;
		params.limit = 100;
		view_frame.loadData({globalFilters:params});
	},
	selectEvnDirection: function(ed_record) {
		var bf = this.findById('EvnPSEditForm').getForm();
		var iswd_combo = bf.findField('EvnPS_IsWithoutDirection');
		iswd_combo.setValue(2);
		iswd_combo.fireEvent('change', iswd_combo, 2);

		bf.findField('EvnDirection_id').setValue(ed_record.get('EvnDirection_id'));

		bf.findField('Org_did').getStore().loadData([{
			Org_id: ed_record.get('Org_id'),
			Org_Code: null,
			Org_Nick: ed_record.get('Org_Nick'),
			Org_Name: ed_record.get('Org_Name')
		}], false);
		bf.findField('Org_did').setValue(ed_record.get('Org_id'));

		bf.findField('EvnDirection_Num').setValue(ed_record.get('EvnDirection_Num'));
		bf.findField('EvnDirection_setDate').setValue(ed_record.get('EvnDirection_setDateTime'));
		bf.findField('PayType_id').setValue(ed_record.get('PayType_id'));

		var diag_id = ed_record.get('Diag_id');
		if ( diag_id ) {
			bf.findField('Diag_did').getStore().load({
				callback: function() {
					bf.findField('Diag_did').getStore().each(function(record) {
						if ( record.get('Diag_id') == diag_id ) {
							bf.findField('Diag_did').setValue(diag_id);
							bf.findField('Diag_did').fireEvent('select', bf.findField('Diag_did'), record, 0);
						}
					});
				},
				params: {where: "where DiagLevel_id = 4 and Diag_id = " + diag_id}
			});
		}
	},
	printEvnPS: function() {
		if ( 'add' == this.action || 'edit' == this.action ) {
			this.doSave({
				print: true
			});
		}
		else if ( 'view' == this.action ) {
			var evn_ps_id = this.findById('EvnPSEditForm').getForm().findField('EvnPS_id').getValue();
			var last_evn_section_info = this.getEvnSectionInfo('last');

			if ( last_evn_section_info.LpuUnitType_id == 1 ) {
				printBirt({
					'Report_FileName': 'han_EvnPS_f066u.rptdesign',
					'Report_Params': '&paramEvnPS=' + evn_ps_id,
					'Report_Format': 'pdf'
				});
			}
			else {
				printBirt({
					'Report_FileName': 'han_EvnPS_f066_4u2.rptdesign',
					'Report_Params': '&paramEvnPS=' + evn_ps_id,
					'Report_Format': 'pdf'
				});
			}
		}
	},
	resizable: true,
	setEvnPSOutcomeDT: function() {
		var base_form = this.findById('EvnPSEditForm').getForm();

		if ( Ext.isEmpty(base_form.findField('LpuSection_pid').getValue()) ) {
			return false;
		}

		var first_evn_section_info = this.getEvnSectionInfo('first');

		if ( !Ext.isEmpty(first_evn_section_info.EvnSection_setDT) ) {
			base_form.findField('EvnPS_OutcomeDate').setValue(first_evn_section_info.EvnSection_setDT);
			base_form.findField('EvnPS_OutcomeTime').setValue(Ext.util.Format.date(first_evn_section_info.EvnSection_setDT, 'H:i'));
		}
	},
	show: function() {
		var thisWin = this;
		sw.Promed.swEvnPSEditWindow.superclass.show.apply(this, arguments);

		if ( this.firstRun == true ) {
			this.findById('EPSEF_HospitalisationPanel').collapse();
			this.findById('EPSEF_DirectDiagPanel').collapse();
			this.findById('EPSEF_AdmitDepartPanel').collapse();
			this.findById('EPSEF_AdmitDiagPanel').collapse();
			this.findById('EPSEF_PriemLeavePanel').collapse();
			this.findById('EPSEF_PrehospWaifPanel').collapse();
			this.findById('EPSEF_EvnSectionPanel').collapse();
			this.findById('EPSEF_EvnStickPanel').collapse();
			this.findById('EPSEF_EvnUslugaPanel').collapse();
			this.findById('EPSEF_EvnDrugPanel').collapse();
		}

		this.findById('EPSEF_HospitalisationPanel').hide();
		this.findById('EPSEF_DirectDiagPanel').hide();
		this.findById('EPSEF_AdmitDepartPanel').hide();
		this.findById('EPSEF_AdmitDiagPanel').hide();
		this.findById('EPSEF_PriemLeavePanel').hide();
		this.findById('EPSEF_PrehospWaifPanel').hide();
		this.findById('EPSEF_EvnSectionPanel').hide();
		this.findById('EPSEF_EvnStickPanel').hide();
		this.findById('EPSEF_EvnUslugaPanel').hide();
		this.findById('EPSEF_EvnDrugPanel').hide();

		this.restore();
		this.center();
		this.maximize();

		var base_form = this.findById('EvnPSEditForm').getForm(),
				_this = this;
		base_form.reset();

		this.action = null;
		this.callback = Ext.emptyFn;
		this.formStatus = 'edit';
		this.isCopy = false;
		this.onHide = Ext.emptyFn;
		this.params = new Object();
		this.lookChange = new Object();
		base_form.findField('Diag_pid').filterDate = null;
		base_form.findField('Diag_did').filterDate = null;

		base_form.findField('EvnDirection_Num').disable();
		base_form.findField('EvnDirection_setDate').disable();
		base_form.findField('LpuSection_did').disable();
		base_form.findField('Org_did').disable();

		if ( !arguments[0] ) {
			sw.swMsg.alert(lang['soobschenie'], lang['nevernyie_parametryi']);
			return false;
		}

		this.form_mode = arguments[0].form_mode || null;
		this.onChangeLpuSectionWard = arguments[0].onChangeLpuSectionWard || null;
		if (arguments[0].childPS) {
			//редактируется КВС ребенка
			this.BirthWeight = null;
			this.PersonWeight_text = null;
			this.Okei_id = null;
			this.BirthHeight = null;
			this.childPS = true;
			this.findById(this.id  + 'PrehospType_id').getStore().load();//todo разобраться, почему getDataAll не вызывается для второго экземпляра формы и убрать явную загрузку
			if (arguments[0].opener){//передано кто открыл
				this.opener = arguments[0].opener;
			}
			if (arguments[0].ChildTermType_id) {
				this.ChildTermType_id = arguments[0].ChildTermType_id;
			} else {
				this.ChildTermType_id = null;
			}
			if (arguments[0].BirthSpecStac_CountChild) {
				this.BirthSpecStac_CountChild = arguments[0].BirthSpecStac_CountChild;
			} else {
				this.BirthSpecStac_CountChild = null;
			}
			if (arguments[0].PersonChild_IsAidsMother) {
				this.PersonChild_IsAidsMother = arguments[0].PersonChild_IsAidsMother;
			} else {
				this.PersonChild_IsAidsMother = null;
			}
		}

		base_form.setValues(arguments[0]);
		if ( arguments[0].action ) {
			this.action = arguments[0].action;
		}

		if ( arguments[0].callback ) {
			this.callback = arguments[0].callback;
		}

		this.deleteOnCancel = false;

		if ( arguments[0].deleteOnCancel ) {
			this.deleteOnCancel = arguments[0].deleteOnCancel;
		}

		if ( arguments[0].EvnLeave_setDate ) {
			this.params.EvnLeave_setDate = arguments[0].EvnLeave_setDate;
		}

		if ( arguments[0].EvnLeave_UKL ) {
			this.params.EvnLeave_UKL = arguments[0].EvnLeave_UKL;
		}

		if ( arguments[0].isCopy ) {
			this.isCopy = arguments[0].isCopy;
		}

		if ( arguments[0].LeaveCause_id ) {
			this.params.LeaveCause_id = arguments[0].LeaveCause_id;
		}

		if ( arguments[0].LeaveType_id ) {
			this.params.LeaveType_id = arguments[0].LeaveType_id;
		}

		if ( arguments[0].LeaveTypeFed_id ) {
			this.params.LeaveTypeFed_id = arguments[0].LeaveTypeFed_id;
		}

		if ( arguments[0].LpuSection_id ) {
			this.params.LpuSection_id = arguments[0].LpuSection_id;
		}

		if ( arguments[0].MedPersonal_id ) {
			this.params.MedPersonal_id = arguments[0].MedPersonal_id;
		}

		if ( arguments[0].MedStaffFact_id ) {
			this.params.MedStaffFact_id = arguments[0].MedStaffFact_id;
		}
		else {
			this.params.MedStaffFact_id = null;
		}

		if ( arguments[0].onHide ) {
			this.onHide = arguments[0].onHide;
		}

		if ( arguments[0].ResultDesease_id ) {
			this.params.ResultDesease_id = arguments[0].ResultDesease_id;
		}

		if ( arguments[0].TariffClass_id ) {
			this.params.TariffClass_id = arguments[0].TariffClass_id;
		}

		if ( arguments[0].from ) {
			this.from = arguments[0].from;
		} else {
			this.from = null;
		}

		this.lookChange.PrehospType_id = null;
		this.lookChange.EvnDirection_Num = null;
		this.lookChange.EvnDirection_setDate = null;
		this.lookChange.PrehospDirect_id = null;
		this.lookChange.PayType_id = null;
		this.updateLookChange();

		this.ed_record = null;
		if ( typeof arguments[0].EvnDirection == 'object' )
		{
			this.ed_record = arguments[0].EvnDirection;
		}
		base_form.findField('Diag_pid').setAllowBlank(true);
		if ( this.action == 'add' ) {
			this.findById('EPSEF_DirectDiagPanel').isLoaded = true;
			this.findById('EPSEF_AdmitDiagPanel').isLoaded = true;
			this.findById('EPSEF_PriemLeavePanel').isLoaded = true;
			this.findById('EPSEF_PrehospWaifPanel').isLoaded = true;
			this.findById('EPSEF_EvnSectionPanel').isLoaded = true;
			this.findById('EPSEF_EvnStickPanel').isLoaded = true;
			this.findById('EPSEF_EvnUslugaPanel').isLoaded = true;
			this.findById('EPSEF_EvnDrugPanel').isLoaded = true;
		}
		else {
			this.findById('EPSEF_DirectDiagPanel').isLoaded = false;
			this.findById('EPSEF_AdmitDiagPanel').isLoaded = false;
			this.findById('EPSEF_PriemLeavePanel').isLoaded = false;
			this.findById('EPSEF_PrehospWaifPanel').isLoaded = false;
			this.findById('EPSEF_EvnSectionPanel').isLoaded = false;
			this.findById('EPSEF_EvnStickPanel').isLoaded = false;
			this.findById('EPSEF_EvnUslugaPanel').isLoaded = false;
			this.findById('EPSEF_EvnDrugPanel').isLoaded = false;
		}

		var diag_d_combo = base_form.findField('Diag_did');
		var diag_p_combo = base_form.findField('Diag_pid');
		var diag_c_combo = base_form.findField('Diag_cid');
		var lpu_section_dir_combo = base_form.findField('LpuSection_did');
		var lpu_section_rec_combo = base_form.findField('LpuSection_pid');
		var lpu_section_hosp_combo = base_form.findField('LpuSection_eid');
		var med_staff_fact_rec_combo = base_form.findField('MedStaffFact_pid');
		var org_combo = base_form.findField('Org_did');
		var prehosp_arrive_combo = base_form.findField('PrehospArrive_id');
		var prehosp_direct_combo = base_form.findField('PrehospDirect_id');
		var prehosp_trauma_combo = base_form.findField('PrehospTrauma_id');
		var prehosp_type_combo = base_form.findField('PrehospType_id');
		var iswd_combo = base_form.findField('EvnPS_IsWithoutDirection');
		var okei_combo = base_form.findField('Okei_id');

		var evn_ps_id = base_form.findField('EvnPS_id').getValue();
		var person_id = base_form.findField('Person_id').getValue();
		var server_id = base_form.findField('Server_id').getValue();

		var loadMask = new Ext.LoadMask(this.getEl(), {msg: LOAD_WAIT});
		loadMask.show();		

		// тут жесть какая то, просто меняется win.action, хотя форма могла уже ранее загрузиться и открыться в нужном режиме.
		/*//Проверяем возможность редактирования документа
		if (this.action != 'view') {
			Ext.Ajax.request({
				failure: function(response, options) {
					sw.swMsg.alert('Ошибка', 'Ошибка при загрузке данных формы', function() { this.hide(); }.createDelegate(this) );
				},
				params: {
					Evn_id: evn_ps_id,
					from: _this.from,
					MedStaffFact_id: (!Ext.isEmpty(sw.Promed.MedStaffFactByUser) && !Ext.isEmpty(sw.Promed.MedStaffFactByUser.current) && !Ext.isEmpty(sw.Promed.MedStaffFactByUser.current.MedStaffFact_id)) ? sw.Promed.MedStaffFactByUser.current.MedStaffFact_id : null,
					ArmType: (!Ext.isEmpty(sw.Promed.MedStaffFactByUser) && !Ext.isEmpty(sw.Promed.MedStaffFactByUser.current) && !Ext.isEmpty(sw.Promed.MedStaffFactByUser.current.ARMType)) ? sw.Promed.MedStaffFactByUser.current.ARMType : null
				},
				success: function(response, options) {
					if (!Ext.isEmpty(response.responseText)) {
						var response_obj = Ext.util.JSON.decode(response.responseText);

						if ( response_obj.success == false ) {
							sw.swMsg.alert('Ошибка', response_obj.Error_Msg ? response_obj.Error_Msg : 'Ошибка при загрузке данных формы');
							_this.action = 'view';
						}
					}
				}.createDelegate(this),
				url: '/?c=Evn&m=CommonChecksForEdit'
			});
		}*/

		okei_combo.setValue(100); // По умолчанию: час

		setCurrentDateTime({
			callback: Ext.emptyFn,
			dateField: base_form.findField('EvnPS_setDate'),
			loadMask: false,
			setDate: false,
			setDateMaxValue: true,
			windowId: this.id
		});

		base_form.findField('EvnPS_setDate').setMinValue(undefined);

		this.findById('EPSEF_EvnDiagPSHospGrid').getStore().removeAll();
		this.findById('EPSEF_EvnDiagPSHospGrid').getTopToolbar().items.items[0].disable();
		this.findById('EPSEF_EvnDiagPSHospGrid').getTopToolbar().items.items[1].disable();
		this.findById('EPSEF_EvnDiagPSHospGrid').getTopToolbar().items.items[2].disable();
		this.findById('EPSEF_EvnDiagPSHospGrid').getTopToolbar().items.items[3].disable();

		this.findById('EPSEF_EvnDiagPSRecepGrid').getStore().removeAll();
		this.findById('EPSEF_EvnDiagPSRecepGrid').getTopToolbar().items.items[0].disable();
		this.findById('EPSEF_EvnDiagPSRecepGrid').getTopToolbar().items.items[1].disable();
		this.findById('EPSEF_EvnDiagPSRecepGrid').getTopToolbar().items.items[2].disable();
		this.findById('EPSEF_EvnDiagPSRecepGrid').getTopToolbar().items.items[3].disable();

		this.findById('EPSEF_EvnSectionGrid').getStore().removeAll();
		this.findById('EPSEF_EvnSectionGrid').getTopToolbar().items.items[0].disable();
		this.findById('EPSEF_EvnSectionGrid').getTopToolbar().items.items[1].disable();
		this.findById('EPSEF_EvnSectionGrid').getTopToolbar().items.items[2].disable();
		this.findById('EPSEF_EvnSectionGrid').getTopToolbar().items.items[3].disable();

		this.findById('EPSEF_EvnStickGrid').getStore().removeAll();
		this.findById('EPSEF_EvnStickGrid').getTopToolbar().items.items[0].disable();
		this.findById('EPSEF_EvnStickGrid').getTopToolbar().items.items[1].disable();
		this.findById('EPSEF_EvnStickGrid').getTopToolbar().items.items[2].disable();
		this.findById('EPSEF_EvnStickGrid').getTopToolbar().items.items[3].disable();

		this.findById('EPSEF_EvnUslugaGrid').getStore().removeAll();
		this.findById('EPSEF_EvnUslugaGrid').getTopToolbar().items.items[0].disable();
		this.findById('EPSEF_EvnUslugaGrid').getTopToolbar().items.items[1].disable();
		this.findById('EPSEF_EvnUslugaGrid').getTopToolbar().items.items[2].disable();
		this.findById('EPSEF_EvnUslugaGrid').getTopToolbar().items.items[3].disable();

		this.findById('EPSEF_EvnDrugGrid').getStore().removeAll();
		this.findById('EPSEF_EvnDrugGrid').getTopToolbar().items.items[0].disable();
		this.findById('EPSEF_EvnDrugGrid').getTopToolbar().items.items[1].disable();
		this.findById('EPSEF_EvnDrugGrid').getTopToolbar().items.items[2].disable();
		this.findById('EPSEF_EvnDrugGrid').getTopToolbar().items.items[3].disable();

		setLpuSectionGlobalStoreFilter();

		//lpu_section_dir_combo.getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));
		
		setMedStaffFactGlobalStoreFilter({
			EvnClass_SysNick: 'EvnPS',
			isStac:true
		});
		base_form.findField('MedStaffFact_id').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));

		prehosp_direct_combo.getStore().clearFilter();
		prehosp_direct_combo.fireEvent('change', prehosp_direct_combo, null);

		var is_waif_combo = base_form.findField('EvnPS_IsWaif');
		is_waif_combo.setAllowBlank(true);

		switch ( this.action ) {
			case 'add':
				this.setTitle(WND_HOSP_EPSADD);
				this.enableEdit(true);

				lpu_section_hosp_combo.getStore().removeAll();

				base_form.findField('PrehospWaifRefuseCause_id').fireEvent('change', base_form.findField('PrehospWaifRefuseCause_id'), base_form.findField('PrehospWaifRefuseCause_id').getValue());
				
				this.findById('EPSEF_PersonInformationFrame').setTitle('...');
				this.findById('EPSEF_PersonInformationFrame').clearPersonChangeParams();
				this.findById('EPSEF_PersonInformationFrame').load({
					callback: function() {
						this.findById('EPSEF_PersonInformationFrame').setPersonTitle();

						base_form.findField('EvnPS_setDate').setMinValue(this.findById('EPSEF_PersonInformationFrame').getFieldValue('Person_Birthday'));

						if ( this.findById('EPSEF_PersonInformationFrame').getFieldValue('Person_Age') < 18 ) {
							this.findById('EPSEF_PrehospWaifPanel').show();
							is_waif_combo.setAllowBlank(false);
							is_waif_combo.setValue(1);
							is_waif_combo.fireEvent('change', is_waif_combo,1, null);
						}
						this.setMKB();
					}.createDelegate(this),
					onExpand: true,
					Person_id: person_id,
					Server_id: server_id
					
				});
				lpu_section_rec_combo.setAllowBlank(true);
				this.findById('EPSEF_HospitalisationPanel').show();
				this.findById('EPSEF_DirectDiagPanel').show();
				this.findById('EPSEF_AdmitDepartPanel').show();
				this.findById('EPSEF_AdmitDiagPanel').show();
				this.findById('EPSEF_PriemLeavePanel').show();
				//this.findById('EPSEF_PrehospWaifPanel').show();
				this.findById('EPSEF_EvnSectionPanel').show();
				this.findById('EPSEF_EvnStickPanel').show();
				this.findById('EPSEF_EvnUslugaPanel').show();
				this.findById('EPSEF_EvnDrugPanel').show();
				if ( this.firstRun == true ) {
					this.findById('EPSEF_HospitalisationPanel').expand();
					this.findById('EPSEF_AdmitDepartPanel').expand();
					this.findById('EPSEF_EvnSectionPanel').expand();
					this.firstRun = false;
				}
				this.findById('EPSEF_EvnDiagPSHospGrid').getTopToolbar().items.items[0].enable();
				this.findById('EPSEF_EvnDiagPSRecepGrid').getTopToolbar().items.items[0].enable();
				this.findById('EPSEF_EvnSectionGrid').getTopToolbar().items.items[0].enable();
				this.findById('EPSEF_EvnStickGrid').getTopToolbar().items.items[0].enable();
				this.findById('EPSEF_EvnUslugaGrid').getTopToolbar().items.items[0].enable();
				this.findById('EPSEF_EvnDrugGrid').getTopToolbar().items.items[0].enable();

				LoadEmptyRow(this.findById('EPSEF_EvnDiagPSHospGrid'));
				LoadEmptyRow(this.findById('EPSEF_EvnDiagPSRecepGrid'));
				LoadEmptyRow(this.findById('EPSEF_EvnSectionGrid'));
				LoadEmptyRow(this.findById('EPSEF_EvnStickGrid'));
				LoadEmptyRow(this.findById('EPSEF_EvnUslugaGrid'));
				LoadEmptyRow(this.findById('EPSEF_EvnDrugGrid'));

				if ( !prehosp_type_combo.getValue() ) {
					prehosp_type_combo.setValue(2);
				}
				prehosp_type_combo.getStore().on('load', function(store, records, index){
					prehosp_type_combo.setValue(2);
				});

				lpu_section_rec_combo.fireEvent('change', lpu_section_rec_combo, null);
				prehosp_arrive_combo.fireEvent('change', prehosp_arrive_combo, prehosp_arrive_combo.getValue());
				prehosp_trauma_combo.fireEvent('change', prehosp_trauma_combo, prehosp_trauma_combo.getValue());
				base_form.findField('EvnPS_IsUnlaw').fireEvent('change', base_form.findField('EvnPS_IsUnlaw'), base_form.findField('EvnPS_IsUnlaw').getValue());
				base_form.findField('EvnPS_setDate').fireEvent('change', base_form.findField('EvnPS_setDate'), base_form.findField('EvnPS_setDate').getValue());

				if ( this.ed_record )
				{
					this.selectEvnDirection(this.ed_record);
				} else {
					if (prehosp_direct_combo.getValue() == 1 || prehosp_direct_combo.getValue() == 2) {
						iswd_combo.setValue(2);
					} else {
						iswd_combo.setValue(1);
					}
					/*
					 var evn_direction_id = base_form.findField('EvnDirection_id').getValue();
					 var evn_direction_num = base_form.findField('EvnDirection_Num').getValue();
					 var evn_direction_set_date = base_form.findField('EvnDirection_setDate').getValue();
					 var lpu_section_did = lpu_section_dir_combo.getValue();
					 var org_did = org_combo.getValue();
					 */
					iswd_combo.fireEvent('change', iswd_combo, iswd_combo.getValue());
				}
				
				if (getRegionNick()=='kz' && Ext.isEmpty(base_form.findField('PayType_id').getValue())) {
					base_form.findField('PayType_id').fireEvent('change', base_form.findField('PayType_id'), 151);
					base_form.findField('PayType_id').setValue(151);
				}
				
				loadMask.hide();

				//base_form.clearInvalid();

				//если уже выбрано приемное отделение то позволяем выбирать диагноз (refs #6987)
				var lpu_section_pid = lpu_section_rec_combo.getValue();
				if ( lpu_section_pid ) {
					diag_p_combo.enable();
				}
				
				if(this.form_mode == 'arm_stac_add_patient') {
					this.params.addEvnSection = true;
					this.params.MedStaffFact_id = (Ext.isEmpty(this.params.MedStaffFact_id) && !Ext.isEmpty(sw.Promed.MedStaffFactByUser) && typeof sw.Promed.MedStaffFactByUser.current == 'object' && !Ext.isEmpty(sw.Promed.MedStaffFactByUser.current) && !Ext.isEmpty(sw.Promed.MedStaffFactByUser.current.MedStaffFact_id) ? sw.Promed.MedStaffFactByUser.current.MedStaffFact_id : null);
					setLpuSectionGlobalStoreFilter({
						onDate: Ext.util.Format.date(base_form.findField('EvnPS_setDate').getValue(), 'd.m.Y')
					});
					lpu_section_hosp_combo.getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));
					lpu_section_hosp_combo.setValue(this.params.LpuSection_id);
				}

				this.getEvnPSNumber();
				this.loadBedList();
				this.setBedListAllowBlank();

				base_form.items.each(function(f){
					f.validate();
				});

				break;

			case 'edit':
			case 'view':
				this.findById('EPSEF_HospitalisationPanel').show();
				this.findById('EPSEF_DirectDiagPanel').show();
				this.findById('EPSEF_AdmitDepartPanel').show();
				this.findById('EPSEF_AdmitDiagPanel').show();
				this.findById('EPSEF_PriemLeavePanel').show();
				//this.findById('EPSEF_PrehospWaifPanel').show();
				this.findById('EPSEF_EvnSectionPanel').show();
				this.findById('EPSEF_EvnStickPanel').show();
				this.findById('EPSEF_EvnUslugaPanel').show();
				this.findById('EPSEF_EvnDrugPanel').show();

				this.isProcessLoadForm = true;
				base_form.load({
					failure: function() {
						loadMask.hide();
						sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_zagruzke_dannyih_formyi'], function() {this.hide();}.createDelegate(this) );
					}.createDelegate(this),
					params: {
						EvnPS_id: evn_ps_id,
						archiveRecord: _this.archiveRecord
					},
					success: function(a,v,b) {
						if ( base_form.findField('accessType').getValue() == 'view' ) {
							this.action = 'view';
						}

						if (v.result.data.childPS) {
							this.childPS = true;
						}
						
						if ( this.action == 'edit' ) {
							this.setTitle(WND_HOSP_EPSEDIT);
							this.enableEdit(true);

							this.findById('EPSEF_EvnDiagPSHospGrid').getTopToolbar().items.items[0].enable();
							this.findById('EPSEF_EvnDiagPSRecepGrid').getTopToolbar().items.items[0].enable();
							this.findById('EPSEF_EvnSectionGrid').getTopToolbar().items.items[0].enable();
							this.findById('EPSEF_EvnStickGrid').getTopToolbar().items.items[0].enable();
							this.findById('EPSEF_EvnUslugaGrid').getTopToolbar().items.items[0].enable();
							this.findById('EPSEF_EvnDrugGrid').getTopToolbar().items.items[0].enable();

							this.findById('EPSEF_PersonInformationFrame').setPersonChangeParams({
								 callback: function(data) {
									this.hide();
								 }.createDelegate(this)
								,Evn_id: evn_ps_id
								,isEvnPS: true
							});
						}
						else {
							this.setTitle(WND_HOSP_EPSVIEW);
							this.enableEdit(false);

							this.findById('EPSEF_PersonInformationFrame').clearPersonChangeParams();
						}

						this.updateLookChange();

						this.findById('EPSEF_EvnStickGrid').getStore().load({
							params: {
								EvnStick_pid: this.findById('EvnPSEditForm').getForm().findField('EvnPS_id').getValue()
							}
						});

						var evnDirectionData = new Object();

						evnDirectionData.Diag_did = arguments[1].result.data.Diag_did;
						evnDirectionData.EvnDirection_id = arguments[1].result.data.EvnDirection_id;
						evnDirectionData.EvnDirection_Num = arguments[1].result.data.EvnDirection_Num;
						evnDirectionData.EvnDirection_setDate = arguments[1].result.data.EvnDirection_setDate;
						evnDirectionData.LpuSection_id = arguments[1].result.data.LpuSection_did;
						evnDirectionData.Org_did = arguments[1].result.data.Org_did;
						evnDirectionData.Lpu_id = arguments[1].result.data.Lpu_did;
						evnDirectionData.PrehospDirect_id = arguments[1].result.data.PrehospDirect_id;
						evnDirectionData.PayType_id = arguments[1].result.data.PayType_id;
						evnDirectionData.PurposeHospital_id = arguments[1].result.data.PurposeHospital_id;
						evnDirectionData.Diag_cid = arguments[1].result.data.Diag_cid;

						this.findById('EPSEF_PriemLeavePanel').expand();
						this.findById('EPSEF_PriemLeavePanel').collapse();

						if ( this.form_mode == 'edit_priem' ) {
							// приемное
							this.findById('EPSEF_HospitalisationPanel').collapse();
							this.findById('EPSEF_DirectDiagPanel').collapse();
							this.findById('EPSEF_AdmitDepartPanel').expand();
							this.findById('EPSEF_AdmitDiagPanel').expand();
							this.findById('EPSEF_PriemLeavePanel').collapse();
							this.findById('EPSEF_PrehospWaifPanel').collapse();
							this.findById('EPSEF_EvnSectionPanel').collapse();
							this.findById('EPSEF_EvnStickPanel').collapse();
							this.findById('EPSEF_EvnUslugaPanel').collapse();
							this.findById('EPSEF_EvnDrugPanel').collapse();
							this.firstRun = true;
						}
						else {
							if ( this.firstRun == true ) {
								this.findById('EPSEF_EvnSectionPanel').expand();
								this.firstRun = false;
							}
							else {
								this.findById('EPSEF_EvnSectionPanel').fireEvent('expand', this.findById('EPSEF_EvnSectionPanel'));
							}
						}

						// Остальные гриды - только если развернуты панельки
						if ( !this.findById('EPSEF_DirectDiagPanel').collapsed ) {
							this.findById('EPSEF_DirectDiagPanel').fireEvent('expand', this.findById('EPSEF_DirectDiagPanel'));
						}

						if ( !this.findById('EPSEF_AdmitDiagPanel').collapsed ) {
							this.findById('EPSEF_AdmitDiagPanel').fireEvent('expand', this.findById('EPSEF_AdmitDiagPanel'));
						}

						if ( !this.findById('EPSEF_PrehospWaifPanel').collapsed ) {
							this.findById('EPSEF_PrehospWaifPanel').fireEvent('expand', this.findById('EPSEF_PrehospWaifPanel'));
						}

						if ( !this.findById('EPSEF_EvnStickPanel').collapsed ) {
							this.findById('EPSEF_EvnStickPanel').fireEvent('expand', this.findById('EPSEF_EvnStickPanel'));
						}

						if ( !this.findById('EPSEF_EvnUslugaPanel').collapsed ) {
							this.findById('EPSEF_EvnUslugaPanel').fireEvent('expand', this.findById('EPSEF_EvnUslugaPanel'));
						}

						if ( !this.findById('EPSEF_EvnDrugPanel').collapsed ) {
							this.findById('EPSEF_EvnDrugPanel').fireEvent('expand', this.findById('EPSEF_EvnDrugPanel'));
						}
						
						base_form.findField('PrehospWaifRefuseCause_id').fireEvent('change', base_form.findField('PrehospWaifRefuseCause_id'), base_form.findField('PrehospWaifRefuseCause_id').getValue());
						
						this.findById('EPSEF_PersonInformationFrame').setTitle('...');
						this.isProcessInformationFrameLoad = true;
						this.findById('EPSEF_PersonInformationFrame').load({
							callback: function() {
								this.findById('EPSEF_PersonInformationFrame').setPersonTitle();

								base_form.findField('EvnPS_setDate').setMinValue(this.findById('EPSEF_PersonInformationFrame').getFieldValue('Person_Birthday'));

								var omsSprTerrCode = this.findById('EPSEF_PersonInformationFrame').getFieldValue('OmsSprTerr_Code');

								var diag_did = diag_d_combo.getValue();
								var diag_pid = diag_p_combo.getValue();
								var diag_cid = diag_c_combo.getValue();
								var evn_direction_id = base_form.findField('EvnDirection_id').getValue();
								var evn_direction_num = base_form.findField('EvnDirection_Num').getValue();
								var evn_direction_set_date = base_form.findField('EvnDirection_setDate').getValue();
								var evn_ps_code_conv = base_form.findField('EvnPS_CodeConv').getValue();
								var evn_ps_is_cont = base_form.findField('EvnPS_IsCont').getValue();
								var evn_ps_is_unlaw = base_form.findField('EvnPS_IsUnlaw').getValue();
								var evn_ps_num_conv = base_form.findField('EvnPS_NumConv').getValue();
								var evn_ps_set_date = base_form.findField('EvnPS_setDate').getValue();
								var lpu_section_did = lpu_section_dir_combo.getValue();
								var lpu_section_pid = lpu_section_rec_combo.getValue();
								var med_staff_fact_pid = med_staff_fact_rec_combo.getValue();
								var org_did = org_combo.getValue();
								var prehosp_arrive_id = prehosp_arrive_combo.getValue();
								var prehosp_direct_id = prehosp_direct_combo.getValue();
								var prehosp_trauma_id = prehosp_trauma_combo.getValue();

								var index;
								var record;

								base_form.findField('EvnPS_IsCont').fireEvent('change', base_form.findField('EvnPS_IsCont'), evn_ps_is_cont);
								prehosp_direct_combo.setValue(prehosp_direct_id);
								base_form.findField('EvnPS_setDate').fireEvent('change', base_form.findField('EvnPS_setDate'), evn_ps_set_date);

								if ( lpu_section_pid ) {
									diag_p_combo.setDisabled( this.action == 'view' );
								}

								if ( this.action == 'view' ) {
									lpu_section_rec_combo.clearValue();
									lpu_section_rec_combo.getStore().load({
										callback: function() {
											index = lpu_section_rec_combo.getStore().findBy(function(record, id) {
												if ( record.get('LpuSection_id') == lpu_section_pid )
													return true;
												else
													return false;
											})

											if ( index >= 0 ) {
												lpu_section_rec_combo.setValue(lpu_section_pid);
											}
										},
										params: {
											Lpu_id: base_form.findField('Lpu_id').getValue() || getGlobalOptions().lpu_id,
											LpuSection_id: lpu_section_pid
										}
									});

									med_staff_fact_rec_combo.clearValue();
									med_staff_fact_rec_combo.getStore().load({
										callback: function() {
											index = med_staff_fact_rec_combo.getStore().findBy(function(record, id) {
												if ( record.get('MedStaffFact_id') == med_staff_fact_pid )
													return true;
												else
													return false;
											})

											if ( index >= 0 ) {
												med_staff_fact_rec_combo.setValue(med_staff_fact_rec_combo.getStore().getAt(index).get('MedStaffFact_id'));
												med_staff_fact_rec_combo.fireEvent('change', med_staff_fact_rec_combo, med_staff_fact_rec_combo.getValue());
											}
										},
										params: {
											Lpu_id: base_form.findField('Lpu_id').getValue() || getGlobalOptions().lpu_id,
											MedStaffFact_id: med_staff_fact_pid
										}
									});
								}
								else {
									index = med_staff_fact_rec_combo.getStore().findBy(function(record, id) {
										if ( record.get('MedStaffFact_id') == med_staff_fact_pid )
											return true;
										else
											return false;
									})

									if ( index >= 0 ) {
										med_staff_fact_rec_combo.setValue(med_staff_fact_rec_combo.getStore().getAt(index).get('MedStaffFact_id'));
										med_staff_fact_rec_combo.fireEvent('change', med_staff_fact_rec_combo, med_staff_fact_rec_combo.getValue());
									}
									else {
										Ext.Ajax.request({
											failure: function(response, options) {
												loadMask.hide();
											},
											params: {
												Lpu_id: base_form.findField('Lpu_id').getValue() || getGlobalOptions().lpu_id,
												MedStaffFact_id: med_staff_fact_pid
											},
											success: function(response, options) {
												loadMask.hide();
												
												med_staff_fact_rec_combo.getStore().loadData(Ext.util.JSON.decode(response.responseText), true);

												index = med_staff_fact_rec_combo.getStore().findBy(function(rec) {
													if ( rec.get('MedStaffFact_id') == med_staff_fact_pid ) {
														return true;
													}
													else {
														return false;
													}
												});

												if ( index >= 0 ) {
													med_staff_fact_rec_combo.setValue(med_staff_fact_rec_combo.getStore().getAt(index).get('MedStaffFact_id'));
													med_staff_fact_rec_combo.validate();
													med_staff_fact_rec_combo.fireEvent('change', med_staff_fact_rec_combo, med_staff_fact_rec_combo.getValue());
												}
											}.createDelegate(this),
											url: C_MEDPERSONAL_LIST
										});
									}
								}

								if ( !Ext.isEmpty(prehosp_direct_id) ) {
									record = prehosp_direct_combo.getStore().getById(prehosp_direct_id);

									if ( !record ) {
										loadMask.hide();
										return false;
									}

									var prehosp_direct_code = record.get('PrehospDirect_Code');
									var org_type = '';

									if ( !Ext.isEmpty(evn_direction_id) ) {
										iswd_combo.setValue(2);
									} else {
										iswd_combo.setValue(1);
									}
									iswd_combo.fireEvent('change', iswd_combo, iswd_combo.getValue());
									// prehosp_direct_combo.fireEvent('change', prehosp_direct_combo, prehosp_direct_id, -1);

									base_form.findField('EvnDirection_id').setValue(evn_direction_id);

									switch ( prehosp_direct_code ) {
										case 4:
											org_type = 'lpu';
											org_combo.setAllowBlank(false);
										break;

										case 1:
										case 2:
										case 3:
										case 5:
										case 6:
											org_type = 'org';
											org_combo.setAllowBlank(false);
										break;

										default:
											org_combo.setAllowBlank(true);
										break;
									}

									if ( org_type.length > 0 && org_did ) {
										org_combo.getStore().load({
											callback: function(records, options, success) {
												org_combo.clearValue();

												if ( success ) {
													org_combo.setValue(org_did);
												}
											},
											params: {
												Org_id: org_did,
												OrgType: org_type
											}
										});
									}
								}

								if ( diag_did ) {
									diag_d_combo.getStore().load({
										callback: function() {
											diag_d_combo.setValue(diag_did);
											diag_d_combo.fireEvent('select', diag_d_combo, diag_d_combo.getStore().getAt(0), 0);
										},
										params: {
											where: "where DiagLevel_id = 4 and Diag_id = " + diag_did
										}
									});
								}

								if ( diag_pid ) {
									diag_p_combo.getStore().load({
										callback: function() {
											diag_p_combo.setValue(diag_pid);
											diag_p_combo.fireEvent('select', diag_p_combo, diag_p_combo.getStore().getAt(0), 0);
										},
										params: {
											where: "where DiagLevel_id = 4 and Diag_id = " + diag_pid
										}
									});
								}

								if ( diag_cid ) {
									diag_c_combo.getStore().load({
										callback: function() {
											diag_c_combo.setValue(diag_cid);
											diag_c_combo.fireEvent('select', diag_c_combo, diag_c_combo.getStore().getAt(0), 0);
										},
										params: {
											where: "where DiagLevel_id = 4 and Diag_id = " + diag_cid
										}
									});
								}

								base_form.findField('EvnDirection_Num').setValue(evn_direction_num);
								base_form.findField('EvnDirection_setDate').setValue(evn_direction_set_date);

								prehosp_arrive_combo.fireEvent('change', prehosp_arrive_combo, prehosp_arrive_id, -1);
								base_form.findField('EvnPS_CodeConv').setValue(evn_ps_code_conv);
								base_form.findField('EvnPS_NumConv').setValue(evn_ps_num_conv);

								prehosp_trauma_combo.fireEvent('change', prehosp_trauma_combo, prehosp_trauma_id, -1);
								base_form.findField('EvnPS_IsUnlaw').setValue(evn_ps_is_unlaw);
								base_form.findField('EvnPS_IsUnlaw').fireEvent('change', base_form.findField('EvnPS_IsUnlaw'), base_form.findField('EvnPS_IsUnlaw').getValue());

								loadMask.hide();

								if ( this.action == 'edit' ) {
									if ( this.findById('EPSEF_EvnSectionGrid').getStore().getCount() > 0 ) {
										this.findById('EPSEF_EvnSectionGrid').getView().focusRow(0);
										this.findById('EPSEF_EvnSectionGrid').getSelectionModel().selectFirstRow();
									}
									else {
										base_form.findField('EvnPS_IsCont').focus(true, 250);
									}
								}
								else {
									this.buttons[this.buttons.length - 1].focus();
								}

								if (evnDirectionData) {
									if (evnDirectionData.EvnDirection_id) {
										this.setDirection(evnDirectionData);
									} else {
										base_form.findField('PrehospDirect_id').setDisabled( this.action == 'view' );
										base_form.findField('EvnPS_IsWithoutDirection').setDisabled( this.action == 'view' );
										if (getRegionNick()=='kz' && Ext.isEmpty(base_form.findField('PayType_id').getValue())) {
											base_form.findField('PayType_id').fireEvent('change', base_form.findField('PayType_id'), 151);
											base_form.findField('PayType_id').setValue(151);
										}
									}
								} else {
									base_form.findField('PrehospDirect_id').setDisabled( this.action == 'view' );
									base_form.findField('EvnPS_IsWithoutDirection').setDisabled( this.action == 'view' );
									if (getRegionNick()=='kz' && Ext.isEmpty(base_form.findField('PayType_id').getValue())) {
										base_form.findField('PayType_id').fireEvent('change', base_form.findField('PayType_id'), 151);
										base_form.findField('PayType_id').setValue(151);
									}
								}

								if(this.findById('EPSEF_PersonInformationFrame').getFieldValue('Person_Age') < 18)
								{
									this.findById('EPSEF_PrehospWaifPanel').show();
									is_waif_combo.setAllowBlank(false);
									is_waif_combo.fireEvent('change', is_waif_combo, is_waif_combo.getValue(), null);
								}

								base_form.items.each(function(f){
									f.validate();
								});
								this.setMKB();
								this.isProcessInformationFrameLoad = false;
							}.createDelegate(this),
							onExpand: true,
							Person_id: base_form.findField('Person_id').getValue(),
							Server_id: base_form.findField('Server_id').getValue()
						});
						
						this.loadBedList();
						this.setBedListAllowBlank();
						
						this.isProcessLoadForm = false;
					}.createDelegate(this),
					url: '/?c=EvnPS&m=loadEvnPSEditForm'
				});
				break;

			default:
				loadMask.hide();
				break;
		}
	},
	setBedListAllowBlank: function() {
		var win = this,
			base_form = this.getFormPanel()[0].getForm(),
			getbed_field = base_form.findField('GetBed_id'),
			allowBlank = !base_form.findField('LpuSection_pid').getValue() || !(base_form.findField('PayType_id').getFieldValue('PayType_SysNick') && base_form.findField('PayType_id').getFieldValue('PayType_SysNick').inlist(['bud', 'Resp']));
			
		getbed_field.setAllowBlank(allowBlank);
	},
	loadBedList: function() {
		var win = this,
			base_form = this.getFormPanel()[0].getForm(),
			getbed_field = base_form.findField('GetBed_id');
		
		getbed_field.lastQuery = '';
		getbed_field.getStore().load({
			params: {
				Lpu_id: base_form.findField('Lpu_id').getValue() || getGlobalOptions().lpu_id,
				LpuSection_id: base_form.findField('LpuSection_pid').getValue(),
				Person_id: base_form.findField('Person_id').getValue(),
				GetBed_id: win.action == 'view' ? getbed_field.getValue() : null
			},
			callback: function() {
				getbed_field.setValue(getbed_field.getValue());
			}		
		});
	},
	width: 800
});