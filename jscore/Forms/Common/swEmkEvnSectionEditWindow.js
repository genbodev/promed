/**
 * swEmkEvnSectionEditWindow - окно установки исхода госпитализации в отделении стационара (для ЭМК).
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package		Hospital
 * @access		public
 * @copyright	Copyright (c) 2009 Swan Ltd.
 * @author		Stas Bykov aka Savage (savage@swan.perm.ru)
 * @version		07.02.2012
 * @comment		Префикс для id компонентов EESecEF (EmkEvnSectionEditForm)
 */
sw.Promed.swEmkEvnSectionEditWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swEmkEvnSectionEditWindow',
	objectSrc: '/jscore/Forms/Common/swEmkEvnSectionEditWindow.js',
	action: null,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: false,
	closeAction: 'hide',
	collapsible: true,
	changedDates: false,
	deleteEvnDiagPS: function() {
		if ( this.action == 'view' ) {
			return false;
		}

		var grid = this.findById('EESecEF_AnatomDiagGrid').getGrid();

		if ( !grid || !grid.getSelectionModel() || !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('EvnDiagPS_id') ) {
			return false;
		}

		var record = grid.getSelectionModel().getSelected();

		switch ( Number(record.get('RecordStatus_Code')) ) {
			case 0:
				grid.getStore().remove(record);
			break;

			case 1:
			case 2:
				record.set('RecordStatus_Code', 3);
				record.commit();

				grid.getStore().filterBy(function(rec) {
					if ( Number(rec.get('RecordStatus_Code')) == 3 ) {
						return false;
					}
					else {
						return true;
					}
				});
			break;
		}

		if ( grid.getStore().getCount() == 0 ) {
			LoadEmptyRow(grid);
		}

		grid.getView().focusRow(0);
		grid.getSelectionModel().selectFirstRow();
	},
	doSave: function(options) {
		// options @Object

		if ( this.formStatus == 'save' || this.action == 'view' ) {
			return false;
		}
		
		if ( typeof options != 'object' ) {
			options = new Object();
		}

		this.formStatus = 'save';
		var that = this;
		var base_form = this.FormPanel.getForm();
		var PayType_SysNick = base_form.findField('PayType_SysNick').getValue();
		var LeaveType_Id = base_form.findField('LeaveType_id').getValue();
		var ResultDesease_Id = base_form.findField('ResultDesease_id').getValue();

		if ( getRegionNick() == 'ufa' ) {
			if (!options.ignoreIsPaid) {
				if ( !Ext.isEmpty(base_form.findField('EvnSection_IsPaid').getValue()) && base_form.findField('EvnSection_IsPaid').getValue() == 2 ) {
					this.formStatus = 'edit';
					sw.swMsg.show({
						buttons: Ext.Msg.YESNO,
						fn: function(buttonId, text, obj) {
							if ( buttonId == 'yes' ) {
								options.ignoreIsPaid = true;
								this.doSave(options);
							}
						}.createDelegate(this),
						icon: Ext.MessageBox.QUESTION,
						msg: lang['dannyiy_sluchay_oplachen_vyi_deystvitelno_hotite_vnesti_izmeneniya'],
						title: lang['prodoljit_sohranenie']
					});
					return false;
				}
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

		if (
			!Ext.isEmpty(base_form.findField('EvnSection_KoikoDni').getValue())
			&& !Ext.isEmpty(base_form.findField('EvnSection_Absence').getValue())
			&& base_form.findField('EvnSection_KoikoDni').getValue() < 0
			&& getRegionNick().inlist([ 'ekb', 'vologda' ])
		) {
			sw.swMsg.alert('Ошибка', 'Внимание! Количество дней, которые отсутствовал пациент, не должно превышать общее количество дней, фактически проведенных в стационаре. Проверьте данные, указанные в полях: «Дата поступления», «Дата выписки», «Отсутствовал (дней)»');
			this.formStatus = 'edit';
			return false;
		}

		var
			EvnSection_IsAdultEscort = base_form.findField('EvnSection_IsAdultEscort').getValue(),
			EvnSection_IsMedReason = base_form.findField('EvnSection_IsMedReason').getValue(),
			Person_Age = swGetPersonAge(this.findById('EESecEF_PersonInformationFrame').getFieldValue('Person_Birthday'), base_form.findField('EvnSection_setDate').getValue());

		if ( !options.ignoreAdultEscortValue && EvnSection_IsAdultEscort == 2 && Person_Age >= 4 && EvnSection_IsMedReason == 1 ) {
			this.formStatus = 'edit';
			sw.swMsg.show({
				buttons: Ext.Msg.YESNO,
				fn: function (buttonId, text, obj) {
					if (buttonId == 'yes') {
						options.ignoreAdultEscortValue = true;
						this.doSave(options);
					} else {
						base_form.findField('EvnSection_IsAdultEscort').focus(true);
					}
				}.createDelegate(this),
				icon: Ext.MessageBox.QUESTION,
				msg: 'Внимание! Возраст пациента более 4 лет. Сопровождение взрослым допускается при наличии медицинских показаний. Продолжить сохранение?',
				title: 'Вопрос'
			});
			return false;
		}

		var record = base_form.findField('Diag_id').getStore().getById(base_form.findField('Diag_id').getValue());

		if ( getRegionNick() == 'ekb' ) {
			var sex_code = this.findById('EESecEF_PersonInformationFrame').getFieldValue('Sex_Code');
			var person_age = swGetPersonAge(this.findById('EESecEF_PersonInformationFrame').getFieldValue('Person_Birthday'), base_form.findField('EvnSection_setDate').getValue());
			var person_age_month = swGetPersonAgeMonth(this.findById('EESecEF_PersonInformationFrame').getFieldValue('Person_Birthday'), base_form.findField('EvnSection_setDate').getValue());
			var person_age_day = swGetPersonAgeDay(this.findById('EESecEF_PersonInformationFrame').getFieldValue('Person_Birthday'), base_form.findField('EvnSection_setDate').getValue());

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
			if ( Ext.isEmpty(base_form.findField('UslugaComplex_id').getValue()) && PayType_SysNick == 'oms' ) {
				this.formStatus = 'edit';
				sw.swMsg.alert(lang['oshibka'], lang['ne_ukazana_profilnaya_usluga_sohranenie_nedostupno']);
				return false;
			}

			var sex_code = this.findById('EESecEF_PersonInformationFrame').getFieldValue('Sex_Code'),
				leaveTypeFed_code = base_form.findField('LeaveTypeFed_id').getFieldValue('LeaveTypeFed_Code'),
				diag_id_code = base_form.findField('Diag_id').getFieldValue('Diag_Code');
				
			if ( !sex_code || !(sex_code.toString().inlist([ '1', '2' ])) ) {
				this.formStatus = 'edit';
				sw.swMsg.alert(lang['oshibka'], lang['ne_ukazan_pol_patsienta']);
				return false;
			}
			// если Sex_id не соответсвует полу пациента то "Выбранный диагноз не соответствует полу"
			if ( !Ext.isEmpty(record.get('Sex_Code')) && Number(record.get('Sex_Code')) != Number(sex_code) ) {
				this.formStatus = 'edit';
				sw.swMsg.show({
					buttons: Ext.Msg.OK,
					fn: function(buttonId, text, obj) {
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
				if ( record.get('DiagFinance_IsOms') == 0 ) {
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
			}
			
			if (!Ext.isEmpty(diag_id_code) && !Ext.isEmpty(leaveTypeFed_code)){

					if(
							leaveTypeFed_code.toString().inlist([105,106,205,206])
							&& diag_id_code.toString().substr(0, 1).inlist(['Z'])
						)
					{
						sw.swMsg.alert(langs('Ошибка'), langs('Выбранный исход госпитализации не соответствует диагнозу Z. Укажите корректное значение.'));
						return false;
					}
			}
			
			
			
		} else if ( getRegionNick() == 'astra' ) {
			if (!options.ignoreDiagFinance) {
				// если DiagFinance_IsOms = 0
				if ( record.get('DiagFinance_IsOms') == 0 ) {
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
			}
		} else if ( getRegionNick() == 'kaluga' ) {
			if (!options.ignoreDiagFinance) {
				// если DiagFinance_IsOms = 0
				if ( record.get('DiagFinance_IsOms') == 0 ) {
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
						msg: 'Выбранный диагноз не оплачивается по ОМС, поэтому случай не будет включен в реестр. Продолжить сохранение?',
						title: 'Продолжить сохранение?'
					});
					return false;
				}
			}
		} else if ( getRegionNick() == 'kareliya' ) {
			if (!options.ignoreDiagFinance) {
				var sex_code = this.findById('EESecEF_PersonInformationFrame').getFieldValue('Sex_Code');
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
		}

		if (
			Ext.isEmpty(base_form.findField('LpuSectionBedProfileLink_fedid').getValue())
			&& (
				(getRegionNick() == 'perm' && (PayType_SysNick == 'oms' || PayType_SysNick == 'ovd'))
				|| (getRegionNick().inlist(['astra', 'ufa', 'krym', 'pskov', 'buryatiya']) && PayType_SysNick == 'oms')
				|| getRegionNick() == 'penza'
			)
		) {
			this.formStatus = 'edit';
			sw.swMsg.alert(langs('Ошибка'), langs('Не заполнено поле "Профиль коек"! Откройте КВС на редактирование и заполните профиль коек'));
			return false;
		}

		var evn_section_dis_dt = getValidDT(Ext.util.Format.date(base_form.findField('EvnSection_disDate').getValue(), 'd.m.Y'), base_form.findField('EvnSection_disTime').getValue());
		var evn_section_set_dt = getValidDT(Ext.util.Format.date(base_form.findField('EvnSection_setDate').getValue(), 'd.m.Y'), base_form.findField('EvnSection_setTime').getValue());

		if ( !evn_section_set_dt ) {
			this.formStatus = 'edit';
			sw.swMsg.alert(lang['oshibka'], lang['nevernoe_znachenie_datyi_vremeni_postupleniya_v_otdelenie']);
			return false;
		}
		else if ( typeof evn_section_dis_dt == 'object' && evn_section_set_dt > evn_section_dis_dt ) {
			this.formStatus = 'edit';
			sw.swMsg.alert(lang['oshibka'], lang['data_vremya_vyipiski_iz_otdeleniya_menshe_datyi_vremeni_postupleniya']);
			return false;
		}
		
		var Person_Birthday = this.findById('EESecEF_PersonInformationFrame').getFieldValue('Person_Birthday');
		if (!Ext.isEmpty(base_form.findField('LpuSection_oid').getValue()) && evn_section_dis_dt && Person_Birthday) {
			var age = swGetPersonAge(Person_Birthday, evn_section_dis_dt);
			if (!options.ignoreLpuSectionAgeCheck && ((base_form.findField('LpuSection_oid').getFieldValue('LpuSectionAge_id') == 1 && age <= 17) || (base_form.findField('LpuSection_oid').getFieldValue('LpuSectionAge_id') == 2 && age >= 18))) {
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

		if ( !getRegionNick().inlist(['kz','perm','khak']) && !Ext.isEmpty(LeaveType_Id) && !Ext.isEmpty(ResultDesease_Id)) {
			var ResultDesease_Code=base_form.findField('ResultDesease_id').getFieldValue('ResultDesease_Code');
			var LeaveType_Code=base_form.findField('LeaveType_id').getFieldValue('LeaveType_Code'),
				LeaveType_Code_Check="",
				LpuUnitType_SysNick = base_form.findField('LpuUnitType_SysNick').getValue();

			if(getRegionNick()=='astra'){
				var LeaveCause_Code= base_form.findField('LeaveCause_id').getFieldValue('LeaveCause_Code');

				if(
					(
						LeaveType_Code.toString().inlist([2,4,5])
						&& ResultDesease_Code==101
					)
					||
					(
						LpuUnitType_SysNick.toString().inlist(['dstac', 'hstac', 'pstac'])// дневной стационар
						&& LeaveType_Code==1
						&& LeaveCause_Code.toString().inlist([6,7])
						&& ResultDesease_Code==101
					)
				)
				{
					sw.swMsg.alert(langs('Ошибка'), langs('Выбранный исход не соответствует исходу госпитализации. Укажите корректный исход заболевания'));
				}
			}else{

				if(LpuUnitType_SysNick.toString().inlist(['dstac', 'hstac']) && getRegionNick()=='vologda'){
					LeaveType_Code_Check=[102, 103, 104, 109, 202, 203, 204];
				}else {
					LeaveType_Code_Check = [102, 103, 104, 109, 202, 203, 204, 207, 208];
				}

				if(
					(
						LeaveType_Code.toString().inlist(LeaveType_Code_Check)
						&& ResultDesease_Code.toString().inlist([101, 201])
					)
				) {
					this.formStatus = 'edit';
					if (getRegionNick().inlist(['kareliya','krym'])){
						sw.swMsg.alert(langs('Ошибка'), langs('Выбранный исход госпитализации не соответствует результату госпитализации. Укажите корректный исход госпитализации'));
					}else {
						sw.swMsg.alert(langs('Ошибка'), langs('Выбранный исход заболевания не соответствует исходу госпитализации. Укажите корректный исход заболевания'));
					}
					return false;
				}
			}
		}

		var params = new Object();

		var med_staff_fact_aid = base_form.findField('MedStaffFact_aid').getValue();
		var med_staff_fact_did = base_form.findField('MedStaffFact_did').getValue();
		var record;

		base_form.findField('MedPersonal_aid').setValue(0);
		base_form.findField('MedPersonal_did').setValue(0);

		record = base_form.findField('MedStaffFact_aid').getStore().getById(med_staff_fact_aid);
		if ( record ) {
			base_form.findField('MedPersonal_aid').setValue(record.get('MedPersonal_id'));
		}

		record = base_form.findField('MedStaffFact_did').getStore().getById(med_staff_fact_did);
		if ( record ) {
			base_form.findField('MedPersonal_did').setValue(record.get('MedPersonal_id'));
		}

		params.EvnSection_disDate = Ext.util.Format.date(evn_section_dis_dt, 'd.m.Y');
		params.EvnSection_setDate = Ext.util.Format.date(evn_section_set_dt, 'd.m.Y');

		if ( base_form.findField('EvnSection_disTime').disabled ) {
			params.EvnSection_disTime = base_form.findField('EvnSection_disTime').getRawValue();
		}

		if ( base_form.findField('CureResult_id').disabled ) {
			params.CureResult_id = base_form.findField('CureResult_id').getValue();
		}

		if ( base_form.findField('EvnSection_setTime').disabled ) {
			params.EvnSection_setTime = base_form.findField('EvnSection_setTime').getRawValue();
		}

        if ( base_form.findField('LeaveType_fedid').disabled ) {
            params.LeaveType_fedid = base_form.findField('LeaveType_fedid').getValue();
        }

        if ( base_form.findField('ResultDeseaseType_fedid').disabled ) {
            params.ResultDeseaseType_fedid = base_form.findField('ResultDeseaseType_fedid').getValue();
        }

        if ( base_form.findField('DeseaseBegTimeType_id').disabled ) {
            params.DeseaseBegTimeType_id = base_form.findField('DeseaseBegTimeType_id').getValue();
        }

		// Собираем данные из таблицы "Сопутствующие патологоанатомические диагнозы"
		var anatom_diag_grid = this.findById('EESecEF_AnatomDiagGrid').getGrid();

		anatom_diag_grid.getStore().clearFilter();

		if ( anatom_diag_grid.getStore().getCount() > 0 && anatom_diag_grid.getStore().getAt(0).get('EvnDiagPS_id') ) {
			var anatom_diag_data = getStoreRecords(anatom_diag_grid.getStore(), {
				convertDateFields: true,
				exceptionFields: [
					 'EvnDiagPS_pid'
					,'Person_id'
					,'PersonEvn_id'
					,'Server_id'
					,'DiagSetClass_Name'
					,'Diag_Code'
					,'Diag_Name'
				]
			});

			params.anatomDiagData = Ext.util.JSON.encode(anatom_diag_data);

			anatom_diag_grid.getStore().filterBy(function(rec) {
				if ( Number(rec.get('RecordStatus_Code')) == 3 ) {
					return false;
				}
				else {
					return true;
				}
			});
		}

		params.vizit_direction_control_check = (options && !Ext.isEmpty(options.vizit_direction_control_check) && options.vizit_direction_control_check === 1) ? 1 : 0;
		params.ignoreDiagKSGCheck = (options && !Ext.isEmpty(options.ignoreDiagKSGCheck) && options.ignoreDiagKSGCheck === 1) ? 1 : 0;
		params.ignoreParentEvnDateCheck = (options && !Ext.isEmpty(options.ignoreParentEvnDateCheck) && options.ignoreParentEvnDateCheck === 1) ? 1 : 0;
		params.ignoreCheckEvnUslugaChange = (options && !Ext.isEmpty(options.ignoreCheckEvnUslugaChange) && options.ignoreCheckEvnUslugaChange === 1) ? 1 : 0;
		params.ignoreCheckEvnUslugaDates = (options && !Ext.isEmpty(options.ignoreCheckEvnUslugaDates) && options.ignoreCheckEvnUslugaDates === 1) ? 1 : 0;
		params.ignoreCheckKSGisEmpty = (options && !Ext.isEmpty(options.ignoreCheckKSGisEmpty) && options.ignoreCheckKSGisEmpty === 1) ? 1 : 0;
		params.ignoreCheckTNM = (options && !Ext.isEmpty(options.ignoreCheckTNM) && options.ignoreCheckTNM === 1) ? 1 : 0;
		params.ignoreMorbusOnkoDrugCheck = (options && !Ext.isEmpty(options.ignoreMorbusOnkoDrugCheck) && options.ignoreMorbusOnkoDrugCheck === 1) ? 1 : 0;
		params.ignoreFirstDisableCheck = (!Ext.isEmpty(options.ignoreFirstDisableCheck) && options.ignoreFirstDisableCheck === 1) ? 1 : 0;

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Подождите, идет сохранение случая движения пациента в стационаре..."});
		loadMask.show();

		base_form.submit({
			failure: function(result_form, action) {
				this.formStatus = 'edit';
				loadMask.hide();

				if ( action.result ) {
					if ( action.result.Error_Msg && 'YesNo' != action.result.Error_Msg && 'Ok' != action.result.Error_Msg) {

						sw.swMsg.alert(langs('Ошибка'), action.result.Error_Msg);
					} else if ('Ok' == action.result.Error_Msg) {

						if (action.result.Error_Code == 301) {
							sw.swMsg.show({
								buttons: Ext.Msg.OK,
								fn: function (buttonId, text, obj) {

									if(buttonId == 'ok') {
										var params = {
											EvnSection_id: that.formParams.EvnSection_id,
											MorbusOnko_pid: that.formParams.EvnSection_id,
											Person_id: that.formParams.Person_id,
											PersonEvn_id: that.formParams.PersonEvn_id,
											Server_id: that.formParams.Server_id,
											allowSpecificEdit: true
										};
										getWnd('swMorbusOnkoWindow').show(params);
									}
								}.createDelegate(this),
								icon: Ext.Msg.WARNING,
								msg: action.result.Alert_Msg,
								title: 'Ошибка'
							});
						}
						
					} else if ( action.result.Alert_Msg && 'YesNo' == action.result.Error_Msg ) {
						sw.swMsg.show({
							buttons: Ext.Msg.YESNO,
							fn: function(buttonId, text, obj) {
								if ( buttonId == 'yes' ) {
									if (action.result.Error_Code == 112) {
										options.vizit_direction_control_check = 1;
									}
									if (action.result.Error_Code == 103) {
										options.ignoreDiagKSGCheck = 1;
									}
									if (action.result.Error_Code == 106) {
										options.ignoreMorbusOnkoDrugCheck = 1;
									}
									if (action.result.Error_Code == 109) {
										options.ignoreParentEvnDateCheck = 1;
									}
									if (action.result.Error_Code == 113) {
										options.ignoreCheckOnkoKSG = 1;
									}
									if (action.result.Error_Code == 114) {
										options.ignoreCheckEvnUslugaChange = 1;
									}
									if (action.result.Error_Code == 115) {
										options.ignoreCheckEvnUslugaDates = 1;
									}
									if (action.result.Error_Code == 116) {
										options.ignoreCheckKSGisEmpty = 1;
									}
									if (action.result.Error_Code == 181) {
										options.ignoreCheckTNM = 1;
									}
									if (action.result.Error_Code == 107) {
										options.ignoreFirstDisableCheck = 1;
									}

									this.doSave(options);
								}
								else {
									base_form.findField('EvnSection_setDate').focus(true);
								}
							}.createDelegate(this),
							icon: Ext.MessageBox.QUESTION,
							msg: action.result.Alert_Msg,
							title: lang['prodoljit_sohranenie']
						});
					} else {
						sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_1]']);
					}
				}
			}.createDelegate(this),
			params: params,
			success: function(result_form, action) {
				that.formStatus = 'edit';
				loadMask.hide();
				if ( action.result ) {
					if ( action.result.EvnSection_id ) {
						sw.Promed.EvnSection.onSaveEditForm({
							EvnSection_id:base_form.findField('EvnSection_id').getValue(),
							EvnSection_pid:base_form.findField('EvnSection_pid').getValue(),
							Diag_id:base_form.findField('Diag_id').getValue(),
							EvnSection_setDate:base_form.findField('EvnSection_setDate').getValue(),
							EvnSection_setTime:base_form.findField('EvnSection_setTime').getValue(),
							LpuSection_id:base_form.findField('LpuSection_id').getValue(),
							MedPersonal_id: base_form.findField('MedPersonal_id').getValue(),
							MedStaffFact_id: base_form.findField('MedStaffFact_id').getValue(),
							LpuSectionProfile_eid: base_form.findField('LpuSectionProfile_eid').getValue(),
							Person_id:base_form.findField('Person_id').getValue(),
							PersonEvn_id:base_form.findField('PersonEvn_id').getValue(),
							Server_id:base_form.findField('Server_id').getValue(),
							Person_Surname:that.PersonInfo.getFieldValue('Person_Surname'),
							Person_Firname:that.PersonInfo.getFieldValue('Person_Firname'),
							Person_Secname:that.PersonInfo.getFieldValue('Person_Secname'),
							Person_Birthday:that.PersonInfo.getFieldValue('Person_Birthday'),
							Person_IsDead:that.PersonInfo.getFieldValue('Person_IsDead'),
							EvnSection_disDate:base_form.findField('EvnSection_disDate').getValue(),
							EvnSection_disTime:base_form.findField('EvnSection_disTime').getValue(),
							LeaveType_SysNick:base_form.findField('LeaveType_id').getFieldValue('LeaveType_SysNick'),
							EvnDie_id:base_form.findField('EvnDie_id').getValue(),
							EvnLeave_id:base_form.findField('EvnLeave_id').getValue(),
							EvnOtherSection_id:base_form.findField('EvnOtherSection_id').getValue(),
							EvnOtherSectionBedProfile_id:base_form.findField('EvnOtherSectionBedProfile_id').getValue(),
							EvnOtherLpu_id:base_form.findField('EvnOtherLpu_id').getValue(),
							Org_oid: base_form.findField('Org_oid').getValue(),
							Lpu_oid: base_form.findField('Org_oid').getFieldValue('Lpu_id'),
							EvnOtherStac_id:base_form.findField('EvnOtherStac_id').getValue(),
							LpuUnitType_oid: base_form.findField('LpuUnitType_oid').getValue(),
							LpuSection_oid: base_form.findField('LpuSection_oid').getValue(),
							LpuUnit_oid: base_form.findField('LpuSection_oid').getFieldValue('LpuUnit_id'),
							LpuSectionProfile_oid: base_form.findField('LpuSection_oid').getFieldValue('LpuSectionProfile_id'),
							LpuSectionAge_oid: base_form.findField('LpuSection_oid').getFieldValue('LpuSectionAge_id'),
							DeseaseBegTimeType_id: base_form.findField('DeseaseBegTimeType_id').getValue(),
							callback: function() {
								that.callback();
								that.hide();
							}
						});
					} else {
						if ( action.result.Error_Msg ) {
							sw.swMsg.alert(lang['oshibka'], action.result.Error_Msg);
						} else {
							sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_3]']);
						}
					}
				} else {
					sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_2]']);
				}
			}
		});
	},
	draggable: true,
	enableEdit: function(enable) {
		var base_form = this.FormPanel.getForm();
		var form_fields = new Array(
			 'CureResult_id'
			,'Diag_id'
			,'DiagSetPhase_id'
			,'DiagSetPhase_aid'
			,'PrivilegeType_id'
			,'EvnSection_disDate'
			,'EvnSection_disTime'
			,'EvnSection_PhaseDescr'
			,'LpuSectionBedProfile_id'
			,'LpuSectionProfile_id'
			,'Mes_id'
			,'Mes2_id'
			,'LeaveType_id'
			,'LeaveTypeFed_id'
			,'EvnSection_IsAdultEscort'
			,'EvnSection_IsMedReason'
			,'EvnSection_IsTerm'
			,'EvnLeave_UKL'
			,'ResultDesease_id'
			,'LeaveCause_id'
			,'EvnLeave_IsAmbul'
			,'GetRoom_id'
			,'GetBed_id'
		);
		var i = 0;

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
	},
	filterLpuSectionBedProfilesByLpuSectionProfile: function (LpuSectionProfile_id, fieldName){
		//фильтрую профиль коек по профилю с помощью v_LpuSectionBedProfileLink
		var that = this;
		sw.Promed.LpuSectionBedProfile.getLpuSectionBedProfilesByLpuSectionProfile({
			LpuSectionProfile_id: LpuSectionProfile_id,
			callback: function(response_obj) {
				var base_form = that.FormPanel.getForm();;
				//парсю ответ, сую все профили в одномерный массив
				var LpuSectionBedProfiles = [];
				response_obj.forEach(function (el){LpuSectionBedProfiles.push(parseInt(el.LpuSectionBedProfile_id))});
				//накладываю фильтр на профили коек
				var LpuSectionBedProfileCombo = base_form.findField(fieldName);
				LpuSectionBedProfileCombo.lastQuery = '';
				LpuSectionBedProfileCombo.getStore().filterBy(function (el) {
					return (0 <= LpuSectionBedProfiles.indexOf(el.data.LpuSectionBedProfile_id));
				});

				LpuSectionBedProfileCombo.setBaseFilter(function(rec) {
					return (0 <= LpuSectionBedProfiles.indexOf(rec.get('LpuSectionBedProfile_id')));
				});

				//если значение, которые было установлено отфильтровалось, очищаю комбик
				if ( Ext.isEmpty(LpuSectionBedProfileCombo.getStore().getById(LpuSectionBedProfileCombo.getValue())) ) {
					LpuSectionBedProfileCombo.clearValue();
				}
			}
		});
	},
	/** В случае если работа идёт из АРМ врача, исход = смерть, неоходимость экспертизы = да, меняет обязательность полей раздела патологоанатомическая экспертиза и открывает кнопку выписки направления
	 **/
	setPatoMorphGistDirection: function() {
		var base_form = this.findById('EmkEvnSectionEditForm').getForm(),
			_this = this,
			EvnDie_IsAnatom = base_form.findField('EvnDie_IsAnatom').getValue(),
			LeaveTypeFed_id = base_form.findField('LeaveTypeFed_id').getValue(),
			LeaveType_Code = base_form.findField('LeaveType_id').getFieldValue('LeaveType_Code');

		if (!Ext.isEmpty(this.ARMType_id) && this.ARMType_id == 3 && EvnDie_IsAnatom == 2 && (LeaveType_Code == 3 || (getRegionNick() == 'khak' && base_form.findField('ResultDesease_id').getFieldValue('ResultDesease_Code') == 6 && LeaveType_Code == 1))){
			base_form.findField('EvnDie_expTime').setAllowBlank(true);
			base_form.findField('AnatomWhere_id').setAllowBlank(true);
			base_form.findField('Diag_aid').setAllowBlank(true);
			_this.findById('ESEW_addPatoMorphHistoDirectionButton').disable();
			_this.findById('ESEW_addPatoMorphHistoDirectionButton').show();

			if (_this.needCheckMorfoHistologic) {
				_this.needCheckMorfoHistologic = false;
				var loadMask = new Ext.LoadMask(this.getEl(), {msg:"Проверка возможности выписки направления на патоморфогистологическое исследование..."});
				loadMask.show();
				Ext.Ajax.request({
					params: {
						EvnSection_pid: base_form.findField('EvnSection_pid').getValue()
					},
					failure: function() {
						loadMask.hide();
						sw.swMsg.alert(lang['oshibka'], lang['pri_proverke_vozmojnosti_vyipiski_napravleniya_na_patomorfogistologicheskoe_issledovanie_voznikli_oshibki']);
						return false;
					},
					success: function(response, options) {
						loadMask.hide();
						var result = Ext.util.JSON.decode(response.responseText);
						_this.setDisabledMorfoHistologic = result.response;
						_this.findById('ESEW_addPatoMorphHistoDirectionButton').setDisabled(_this.setDisabledMorfoHistologic);
					},
					url: '/?c=EvnDirectionMorfoHistologic&m=checkEvnDirectionMorfoHistologic'
				});
			} else {
				_this.findById('ESEW_addPatoMorphHistoDirectionButton').setDisabled(_this.setDisabledMorfoHistologic);
			}
		} else {
			this.findById('ESEW_addPatoMorphHistoDirectionButton').hide();
			return false;
		}
	},
	addPatoMorphHistoDirection: function() {

		var base_form = this.findById('EmkEvnSectionEditForm').getForm(),
			_this = this,
			params = {},
			diagsFromDirection = [],
			Diag_oid, //осложнение
			seek_oid = true,
			Diag_sid,
			seek_sid = true; //Сопутствующий
		
		var evn_diag_ps = new Ext.data.JsonStore({
			url: '/?c=EvnDiag&m=loadEvnDiagPSGrid',
			fields: [
				{name:'EvnDiagPS_id', type:'int'},
				{name:'Diag_id', type:'int'},
				{name:'DiagSetClass_id', type:'int'}
			],
			key:'EvnDiagPS_id'
		});
		
		evn_diag_ps.load({
			params: {
				'class': 'EvnDiagPSSect',
				'EvnDiagPS_pid': base_form.findField('EvnSection_id').getValue()
			},
			callback: function () {			
				evn_diag_ps.each(function(rec){
					if (rec.get('DiagSetClass_id') == 3) {
						Diag_sid = seek_sid ? rec.get('Diag_id') : null; // если диагнозов больше 1, поле не заполняем
						seek_sid = false;
					}

					if (rec.get('DiagSetClass_id') == 2) {
						Diag_oid = seek_oid ? rec.get('Diag_id') : null; // если диагнозов больше 1, поле не заполняем
						seek_oid = false;
					}

					diagsFromDirection.push(rec.get('Diag_id'));
				});

				diagsFromDirection.push(base_form.findField('Diag_id').getValue());

				params.action = 'add';
				params.Diag_filter = diagsFromDirection;
				params.UserLpuSection_id = base_form.findField('LpuSection_id').getValue() || null;
				params.UserMedStaffFact_id = getGlobalOptions().CurMedStaffFact_id || null;
				params.formParams = {};
				params.formParams.Person_id =  base_form.findField('Person_id').getValue() || null;
				params.formParams.PersonEvn_id = base_form.findField('PersonEvn_id').getValue() || null;
				params.formParams.Server_id = base_form.findField('Server_id').getValue() || null;
				params.formParams.Diag_id = base_form.findField('Diag_id').getValue() || null;
				params.formParams.EvnPS_id = this.EvnPS_id || null;
				params.formParams.Diag_oid = Diag_oid || null;
				params.formParams.Diag_sid = Diag_sid || null;
				params.formParams.EvnPS_Title = (!Ext.isEmpty(this.EvnPS_NumCard)?(this.EvnPS_NumCard + ', '):'') + Ext.util.Format.date(this.EvnPS_setDate, 'd.m.Y');
				params.callback = function(){
					_this.needCheckMorfoHistologic = true;
					_this.setPatoMorphGistDirection();
				};

				getWnd('swEvnDirectionMorfoHistologicEditWindow').show(params);			
			}
		});
	},
	refreshFieldsVisibility: function(fieldNames) {
		var win = this;
		var base_form = win.FormPanel.getForm();
		if (typeof fieldNames == 'string') fieldNames = [fieldNames];

		var action = win.action;
		var Region_Nick = getRegionNick();
		var EvnSectionList = win.OtherEvnSectionList;

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

			var dateX = new Date(2017, 8, 1); // 01.09.2017
			var EvnSection_disDate = base_form.findField('EvnSection_disDate').getValue();
			var EvnSection_setDate = base_form.findField('EvnSection_setDate').getValue();
			var EvnSection_setTime = base_form.findField('EvnSection_setTime').getValue();
			var LpuUnitType_SysNick = base_form.findField('LpuUnitType_SysNick').getValue();
			var LpuSection_Code = base_form.findField('LpuSection_Code').getValue();
			var Diag_Code = base_form.findField('Diag_id').getFieldValue('Diag_Code');
			var DeseaseType_SysNick = base_form.findField('DeseaseType_id').getFieldValue('DeseaseType_SysNick');
			var LeaveType_SysNick = base_form.findField('LeaveType_id').getFieldValue('LeaveType_SysNick');

			var set_or_cur_date = createDT(EvnSection_setDate, EvnSection_setTime);
			var diag_code_full = !Ext.isEmpty(Diag_Code)?String(Diag_Code).slice(0, 3):'';
			var lpu_section_code_part = !Ext.isEmpty(LpuSection_Code)?String(LpuSection_Code).slice(2):'';

			switch(field.getName()) {
				case 'DeseaseBegTimeType_id':
					visible = (
						Region_Nick == 'kareliya' &&
						LpuUnitType_SysNick == 'stac' &&
						lpu_section_code_part.inlist(['111', '112']) && (
							(diag_code_full >= 'I60' && diag_code_full <= 'I64') ||
							(diag_code_full >= 'G45' && diag_code_full <= 'G46')
						) && (
							(!Ext.isEmpty(EvnSection_setDate) && EvnSection_setDate >= dateX)
							|| (!Ext.isEmpty(EvnSection_disDate) && EvnSection_disDate >= dateX)
						)
					);
					allowBlank = !visible;
					break;
				case 'DeseaseType_id':
					var dateX20181101 = new Date(2018, 10, 1); // 01.11.2018

					visible = (
						Region_Nick != 'kz'
						&& !Ext.isEmpty(diag_code_full)
						&& diag_code_full.substr(0, 1) != 'Z'
					);
					allowBlank = true;

					if (
						visible == true
						&& (
							Region_Nick == 'ufa'
							|| (typeof EvnSection_setDate == 'object' && EvnSection_setDate >= dateX20181101)
							|| (typeof EvnSection_disDate == 'object' && EvnSection_disDate >= dateX20181101)
							|| (
								Region_Nick == 'kareliya'
								&& (
									(diag_code_full >= 'C00' && diag_code_full <= 'C97')
									|| (diag_code_full >= 'D00' && diag_code_full <= 'D09')
								)
							)
						)
					) {
						allowBlank = false;
					}

					var releaseDate = base_form.findField('EvnSection_disDate').getValue();

					if ( !releaseDate ) {
						releaseDate = getValidDT(getGlobalOptions().date, '');
					}

					base_form.findField('DeseaseType_id').getStore().clearFilter();
					base_form.findField('DeseaseType_id').lastQuery = '';
					base_form.findField('DeseaseType_id').getStore().filterBy(function(rec) {
						return (
							(!rec.get('DeseaseType_begDT') || rec.get('DeseaseType_begDT') <= releaseDate)
							&& (!rec.get('DeseaseType_endDT') || rec.get('DeseaseType_endDT') >= releaseDate)
						)
					});
					break;

				case 'TumorStage_id':
					var dateX20180601 = new Date(2018, 5, 1);// 01.06.2018

					visible = (
						Region_Nick.inlist(['kareliya','ekb']) && (
							(diag_code_full >= 'C00' && diag_code_full <= 'C97') ||
							(diag_code_full >= 'D00' && diag_code_full <= 'D09')
						) && (
							!Region_Nick.inlist(['kareliya'])
							|| (!Ext.isEmpty(EvnSection_setDate) && EvnSection_setDate >= dateX)
							|| (!Ext.isEmpty(EvnSection_disDate) && EvnSection_disDate >= dateX)
						)
					);

					if ( Region_Nick == 'ekb' && EvnSection_setDate >= dateX20180601 ) { visible = false }

					if (visible) {
						enable = Region_Nick.inlist(['ekb']) || DeseaseType_SysNick == 'new';
						if (getRegionNick() != 'ekb') {
							filter = function (record) {
								return record.get('TumorStage_Code').inlist([0, 1, 2, 3, 4])
							};
						}
						if (!enable) value = null;
					}
					allowBlank = !enable;
					break;
				case 'PrivilegeType_id':
					visible = getRegionNick().inlist(['astra','buryatiya','krym']);

					filter = function (rec) {
						return rec.get('PrivilegeType_Code').inlist([81,82,83,84]);
					};
					if(getRegionNick() == 'krym'){
						filter = function (rec) {
							return (rec.get('PrivilegeType_Code').inlist([81,82,83,84]) && rec.get('ReceptFinance_id') == 1);
						};
					}
					break;
				case 'PayTypeERSB_id':
					visible = getRegionNick() == 'kz' && !Ext.isEmpty(LeaveType_SysNick) && LeaveType_SysNick.inlist(['leave', 'other', 'die', 'stac', 'ksdiepp', 'ksprerv']);
					allowBlank = !visible || !base_form.findField('PayType_SysNick').getValue().inlist(['bud', 'Resp']);
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
	formStatus: 'edit',
	height: 550,
	id: 'EmkEvnSectionEditWindow',
	initComponent: function() {
		this.formFirstShow = true;
		var _this = this;
		this.diagPanel = new sw.Promed.swDiagPanel({
			bodyStyle: 'padding: 0px;',
			diagField: {
				checkAccessRights: true,
				allowBlank: false,
				enableNativeTabSupport: false,
				fieldLabel: lang['osnovnoy_diagnoz'],
				id: this.id + '_DiagCombo',
				hiddenName: 'Diag_id',
				onChange: function() {
					this.loadMesCombo();
					this.loadMes2Combo(-1, true);
					this.setDiagFilterForKSGEkb();
					this.setDiagEidAllowBlank();
					this.refreshFieldsVisibility(['DeseaseBegTimeType_id','DeseaseType_id','TumorStage_id']);
				}.createDelegate(this),
				tabIndex: this.tabIndex + 7,
				width: 500,
				xtype: 'swdiagcombo'
			},
			diagSetPhaseName: 'DiagSetPhase_id',
			diagPhaseFieldLabel: langs('Состояние пациента при поступлении'),
			labelWidth: 180,
			phaseDescrName: 'EvnSection_PhaseDescr',
			showHSN: true
		});

		this.keyHandlerAlt = {
			alt: true,
			fn: function(inp, e) {
				switch (e.getKey()) {
					case Ext.EventObject.C:
						this.doSave();
					break;

					case Ext.EventObject.J:
						this.hide();
					break;

					case Ext.EventObject.NUM_ONE:
					case Ext.EventObject.ONE:
						if ( !this.findById('EESecEF_EvnSectionPanel').hidden ) {
							this.findById('EESecEF_EvnSectionPanel').toggleCollapse();
						}
					break;

					case Ext.EventObject.NUM_TWO:
					case Ext.EventObject.TWO:
						if ( !this.findById('EESecEF_LeavePanel').hidden) {
							this.findById('EESecEF_LeavePanel').toggleCollapse();
						}
					break;
				}
			},
			key: [
				Ext.EventObject.C,
				Ext.EventObject.J,
				Ext.EventObject.NUM_ONE,
				Ext.EventObject.NUM_TWO,
				Ext.EventObject.ONE,
				Ext.EventObject.TWO
			],
			stopEvent: true,
			scope: this
		};

		this.PersonInfo = new sw.Promed.PersonInformationPanelShort({
			id: 'EESecEF_PersonInformationFrame',
			region: 'north'
		});

		var mesTemplate = new Ext.XTemplate(
			'<table cellpadding="0" cellspacing="0" style="width: 100%;"><tr style="font-family: tahoma; font-size: 10pt; font-weight: bold;">',
			'<td style="padding: 2px; width: 50%;">Код</td>',
			'<td style="padding: 2px; width: 50%;">Нормативный срок</td></tr>',
			'<tpl for="."><tr class="x-combo-list-item" style="white-space: normal; overflow: auto; text-overflow: clip;">',
			'<td style="padding: 2px;">{Mes_Code}&nbsp;</td>',
			'<td style="padding: 2px;">{Mes_KoikoDni}&nbsp;</td>',
			'</tr></tpl>',
			'</table>'
		);
		
		if (getGlobalOptions().region && getGlobalOptions().region.nick == 'perm') {
			mesTemplate = new Ext.XTemplate(
				'<table cellpadding="0" cellspacing="0" style="width: 100%;"><tr style="font-family: tahoma; font-size: 10pt; font-weight: bold;">',
				'<td style="padding: 2px; width: 20%;">Код</td>',
				'<td style="padding: 2px; width: 30%;">Нормативный срок</td>',
				'<td style="padding: 2px; width: 40%;">Вид мед. помощи</td>',
				'<td style="padding: 2px; width: 10%;">Возрастная группа</td></tr>',
				'<tpl for="."><tr class="x-combo-list-item" style="white-space: normal; overflow: auto; text-overflow: clip;">',
				'<td style="padding: 2px;">{Mes_Code}&nbsp;</td>',
				'<td style="padding: 2px;">{Mes_KoikoDni}&nbsp;</td>',
				'<td style="padding: 2px;">{MedicalCareKind_Name}&nbsp;</td>',
				'<td style="padding: 2px;">{MesAgeGroup_Name}&nbsp;</td>',
				'</tr></tpl>',
				'</table>'
			);
		}
		
		this.addMaxDateDays = 0;
		if (getGlobalOptions().region){
			if (getGlobalOptions().region.nick == 'astra'){
				this.addMaxDateDays = 3;
			}
		}

		if (getRegionNick() == 'ekb') {
			this.MesSidField = {
				allowBlank: true,
				fieldLabel: lang['ksg'],
				hiddenName: 'Mes_sid',
				listWidth: 600,
				tabIndex:this.tabIndex + 9,
				width: 450,
				xtype: 'swksgekbcombo'
			}
		} else {
			this.MesSidField = {
				name:'Mes_sid', // КСГ найденная через услугу
				xtype:'hidden'
			}
		}

		this.FormPanel = new Ext.form.FormPanel({
			autoScroll: true,
			autoheight: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			border: false,
			frame: false,
			id: 'EmkEvnSectionEditForm',
			labelAlign: 'right',
			labelWidth: 180,
			reader: new Ext.data.JsonReader({
				success: Ext.emptyFn
			}, [
				{ name: 'accessType' },
				{ name: 'AnatomWhere_id' },
				{ name: 'CureResult_id' },
				{ name: 'DeathPlace_id' },
				{ name: 'DeseaseBegTimeType_id' },
				{ name: 'DeseaseType_id' },
				{ name: 'Diag_aid' },
				{ name: 'Diag_eid' },
				{ name: 'Diag_id' },
				{ name: 'Diag_spid' },
				{ name: 'DiagSetPhase_id' },
				{ name: 'DiagSetPhase_aid' },
				{ name: 'PrivilegeType_id' },
				{ name: 'DrugTherapyScheme_ids' },
				{ name: 'EvnDie_expDate' },
				{ name: 'EvnDie_expTime' },
				{ name: 'EvnDie_id' },
				{ name: 'EvnDie_IsWait' },
				{ name: 'EvnDie_IsAnatom' },
				{ name: 'EvnLeave_id' },
				{ name: 'EvnLeave_IsAmbul' },
				{ name: 'EvnLeave_UKL' },
				{ name: 'EvnOtherLpu_id' },
				{ name: 'EvnOtherSection_id' },
				{ name: 'EvnOtherSectionBedProfile_id' },
				{ name: 'EvnOtherStac_id' },
				{ name: 'EvnSection_BarthelIdx' },
				{ name: 'EvnSection_CoeffCTP' },
				{ name: 'EvnSection_disDate' },
				{ name: 'EvnSection_disTime' },
				{ name: 'EvnSection_GraceScalePoints' },
				{ name: 'EvnSection_id' },
				{ name: 'EvnSection_IndexRep' },
				{ name: 'EvnSection_IndexRepInReg' },
				{ name: 'EvnSection_insideNumCard' },
				{ name: 'EvnSection_InsultScale' },
				{ name: 'EvnSection_IsAdultEscort' },
				{ name: 'EvnSection_IsCardShock' },
				{ name: 'EvnSection_IsMeal' },
				{ name: 'EvnSection_IsMedReason' },
				{ name: 'EvnSection_isPartialPay' },
				{ name: 'EvnSection_IsPaid' },
				{ name: 'EvnSection_IsRehab' },
				{ name: 'EvnSection_IsST' },
				{ name: 'EvnSection_IsTerm' },
				{ name: 'EvnSection_IsZNO' },
				{ name: 'EvnSection_PhaseDescr' },
				{ name: 'EvnSection_Absence' },
				{ name: 'EvnSection_pid' },
				{ name: 'EvnSection_setDate' },
				{ name: 'EvnSection_setTime' },
				{ name: 'EvnSection_SofaScalePoints' },
				{ name: 'EvnSection_StartPainHour' },
				{ name: 'EvnSection_StartPainMin' },
				{ name: 'HTMedicalCareClass_id' },
				{ name: 'LeaveCause_id' },
				{ name: 'LeaveType_id' },
				{ name: 'LeaveTypeFed_id' },
                { name: 'LeaveType_fedid' },
				{ name: 'LeaveType_SysNick' },
				{ name: 'LpuSection_aid' },
				{ name: 'LpuSection_Code' },
				{ name: 'LpuSection_id' },
				{ name: 'LpuSectionTransType_id' },
				{ name: 'LpuSection_oid' },
				{ name: 'LpuSectionBedProfile_oid' },
				{ name: 'LpuSectionBedProfileLink_fedoid' },
				{ name: 'LpuSectionBedProfile_id' },
				{ name: 'LpuSectionBedProfileLink_fedid' },
				{ name: 'LpuSectionProfile_eid' },
				{ name: 'LpuSectionProfile_id' },
				{ name: 'LpuUnitType_Code' },
				{ name: 'LpuUnitType_id' },
				{ name: 'LpuUnitType_oid' },
				{ name: 'LpuUnitType_SysNick' },
				{ name: 'LpuSectionWard_id' },
				{ name: 'MedPersonal_aid' },
				{ name: 'MedPersonal_did' },
				{ name: 'MedPersonal_id' },
				{ name: 'MedStaffFact_id' },
				{ name: 'Mes_id' },
				{ name: 'Mes2_id' },
				{ name: 'Mes_kid' },
				{ name: 'Mes_sid' },
				{ name: 'Mes_tid' },
				{ name: 'MesTariff_id' },
				{ name: 'Org_aid' },
				{ name: 'Org_oid' },
				{ name: 'PainIntensity_id' },
				{ name: 'PayType_id' },
				{ name: 'PayTypeERSB_id' },
				{ name: 'PayType_SysNick' },
				{ name: 'Person_id' },
				{ name: 'PersonEvn_id' },
				{ name: 'PregnancyEvnPS_Period' },
				{ name: 'PrehospTrauma_id' },
				{ name: 'RankinScale_id' },
				{ name: 'RankinScale_sid' },
				{ name: 'RehabScale_id' },
				{ name: 'ResultDesease_id' },
                { name: 'ResultDeseaseType_fedid' },
				{ name: 'Server_id' },
				{ name: 'TariffClass_id' },
				{ name: 'TumorStage_id' },
				{ name: 'GetRoom_id' },
				{ name: 'GetBed_id' },
				{ name: 'BedProfile' },
				{ name: 'UslugaComplex_id' }
			]),
			region: 'center',
			url: '/?c=EvnSection&m=saveEvnSection',
			items: [{
				name: 'accessType',
				value: 'edit',
				xtype: 'hidden'
			}, {
				name:'Mes_tid', // КСГ найденная через диагноз
				xtype:'hidden'
			}, {
				name:'Mes_kid', // КПГ
				xtype:'hidden'
			}, {
				name:'MesTariff_id', // коэффициент
				xtype:'hidden'
			}, {
				name:'RankinScale_id',
				xtype:'hidden'
			}, {
				name:'RankinScale_sid',
				xtype:'hidden'
			}, {
				name:'BedProfile',
				xtype:'hidden'
			}, {
				name:'EvnSection_InsultScale',
				xtype:'hidden'
			}, {
				name: 'EvnDie_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'EvnLeave_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'EvnOtherLpu_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'EvnOtherSection_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'EvnOtherSectionBedProfile_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'EvnOtherStac_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'EvnSection_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'EvnSection_pid',
				value: -1,
				xtype: 'hidden'
			}, {
				name:'EvnSection_IsPaid',
				xtype:'hidden'
			}, {
				name: 'MedPersonal_aid',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'MedPersonal_did',
				value: 0,
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
				name: 'LpuSection_id',
				value: -1,
				xtype: 'hidden'
			}, {
				name: 'LpuSectionWard_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'LpuUnitType_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'MedPersonal_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'MedStaffFact_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'PayType_id',
				value: -1,
				xtype: 'hidden'
			}, {
				name: 'PayType_SysNick',
				value: '',
				xtype: 'hidden'
			}, {
				name: 'LeaveType_SysNick',
				value: '',
				xtype: 'hidden'
			}, {
				name: 'LpuSection_Code',
				value: '',
				xtype: 'hidden'
			}, {
				name: 'LpuUnitType_SysNick',
				value: '',
				xtype: 'hidden'
			}, {
				name: 'LpuUnitType_Code',
				value: '',
				xtype: 'hidden'
			}, {
				name: 'TariffClass_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'EvnSection_IsMeal',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'UslugaComplex_id',
				xtype: 'hidden'
			}, {
				name: 'HTMedicalCareClass_id',
				xtype: 'hidden'
			}, {
				name: 'LpuSectionProfile_eid',
				xtype: 'hidden'
			}, {
				name: 'EvnSection_IsRehab',
				xtype: 'hidden'
			}, {
				name: 'DrugTherapyScheme_ids',
				xtype: 'hidden'
			}, {
				name: 'EvnSection_CoeffCTP',
				xtype: 'hidden'
			}, {
				name: 'EvnSection_IndexRep',
				xtype: 'hidden'
			}, {
				name: 'EvnSection_IndexRepInReg',
				xtype: 'hidden'
			}, {
				name: 'LpuSectionTransType_id',
				xtype: 'hidden'
			}, {
				name: 'LpuSectionBedProfileLink_fedid',
				xtype: 'hidden'
			}, {
				name: 'LpuSectionBedProfileLink_fedoid',
				xtype: 'hidden'
			}, {
				name: 'EvnSection_insideNumCard',
				xtype: 'hidden'
			}, {
				name: 'PrehospTrauma_id',
				xtype: 'hidden'
			}, {
				name: 'EvnSection_IsST',
				xtype: 'hidden'
			}, {
				name: 'EvnSection_IsCardShock',
				xtype: 'hidden'
			}, {
				name: 'EvnSection_StartPainHour',
				xtype: 'hidden'
			}, {
				name: 'EvnSection_StartPainMin',
				xtype: 'hidden'
			}, {
				name: 'EvnSection_GraceScalePoints',
				xtype: 'hidden'
			}, {
				name: 'PregnancyEvnPS_Period',
				xtype: 'hidden'
			}, {
				name: 'EvnSection_BarthelIdx',
				xtype: 'hidden'
			}, {
				name: 'RehabScale_id',
				xtype: 'hidden'
			}, {
				name: 'EvnSection_SofaScalePoints',
				xtype: 'hidden'
			}, {
				name: 'PainIntensity_id',
				xtype: 'hidden'
			}, {
				name: 'EvnSection_isPartialPay',
				xtype: 'hidden'
			}, {
				name: 'EvnSection_IsZNO',
				xtype: 'hidden'
			}, {
				name: 'EvnSection_Absence',
				xtype: 'hidden'
			}, {
				name: 'EvnSection_KoikoDni',
				xtype: 'hidden'
			}, {
				name: 'Diag_spid',
				xtype: 'hidden'
			},
			new sw.Promed.Panel({
				autoHeight: true,
				bodyStyle: 'padding-top: 0.5em;',
				border: true,
				collapsible: true,
				id: 'EESecEF_EvnSectionPanel',
				layout: 'form',
				listeners: {
					'expand': function(panel) {
						// this.findById('EmkEvnSectionEditForm').getForm().findField('EvnSection_setDate').focus(true);
					}.createDelegate(this)
				},
				style: 'margin-bottom: 0.5em;',
				title: lang['1_ustanovka_sluchaya_dvijeniya'],
				items: [{
					border: false,
					layout: 'column',
					items: [{
						border: false,
						layout: 'form',
						items: [{
							allowBlank: false,
							disabled: true,
							fieldLabel: lang['data_postupleniya'],
							format: 'd.m.Y',
							listeners: {
								'change': function(field, newValue, oldValue) {
									this.setDiagFilterByDate();
									var base_form = this.FormPanel.getForm();
									base_form.findField('EvnSection_disDate').setMinValue(newValue);
									base_form.findField('Diag_aid').setFilterByDate(newValue);
									this.leaveTypeFilter();
								}.createDelegate(this)
							},
							name: 'EvnSection_setDate',
							plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
							selectOnFocus: true,
							tabIndex: this.tabIndex + 1,
							width: 100,
							xtype: 'swdatefield'
						}, {
							allowBlank: false,
							fieldLabel: lang['data_vyipiski'],
							format: 'd.m.Y',
							listeners: {
								'change': function(field, newValue, oldValue) {
									this.onChangeDates();
									this.loadMesCombo();
									this.setDiagFilterByDate();

									var base_form = this.FormPanel.getForm();

									var med_staff_fact_id = base_form.findField('MedStaffFact_did').getValue();

									base_form.findField('MedStaffFact_did').clearValue();

									if ( !newValue ) {
										setMedStaffFactGlobalStoreFilter({
											 isStac: true
										});
										base_form.findField('MedStaffFact_did').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));
									}
									else {
										setMedStaffFactGlobalStoreFilter({
											 isStac: true
											,onDate: Ext.util.Format.date(newValue, 'd.m.Y')
										});
										base_form.findField('MedStaffFact_did').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));
									}

									if ( base_form.findField('MedStaffFact_did').getStore().getById(med_staff_fact_id) ) {
										base_form.findField('MedStaffFact_did').setValue(med_staff_fact_id);
									}
									this.leaveTypeFilter();

									sw.Promed.EvnSection.calcFedResultDeseaseType({
										date: newValue,
										LpuUnitType_SysNick: base_form.findField('LpuUnitType_SysNick').getValue(),
										LeaveType_SysNick: base_form.findField('LeaveType_id').getFieldValue('LeaveType_SysNick'),
										ResultDesease_Code: base_form.findField('ResultDesease_id').getFieldValue('ResultDesease_Code'),
										fieldFedResultDeseaseType: base_form.findField('ResultDeseaseType_fedid')
									});
									sw.Promed.EvnSection.calcFedLeaveType({
										date: newValue,
										LpuUnitType_SysNick: base_form.findField('LpuUnitType_SysNick').getValue(),
										LeaveType_SysNick: base_form.findField('LeaveType_id').getFieldValue('LeaveType_SysNick'),
										LeaveCause_Code: base_form.findField('LeaveCause_id').getFieldValue('LeaveCause_Code'),
										fieldFedLeaveType: base_form.findField('LeaveType_fedid'),
										fieldFedResultDeseaseType: base_form.findField('ResultDeseaseType_fedid')
									});
									sw.Promed.EvnSection.filterFedResultDeseaseType({
										LpuUnitType_SysNick: base_form.findField('LpuUnitType_SysNick').getValue(),
										fieldFedLeaveType: base_form.findField('LeaveType_fedid'),
										fieldFedResultDeseaseType: base_form.findField('ResultDeseaseType_fedid')
									})
									sw.Promed.EvnSection.filterFedLeaveType({
										LpuUnitType_SysNick: base_form.findField('LpuUnitType_SysNick').getValue(),
										fieldFedLeaveType: base_form.findField('LeaveType_fedid'),
										fieldLeaveType: base_form.findField('LeaveType_id'),
										fieldFedResultDeseaseType: base_form.findField('ResultDeseaseType_fedid')
									});

									base_form.findField('LeaveType_id').fireEvent('change', base_form.findField('LeaveType_id'), base_form.findField('LeaveType_id').getValue());
									this.setDiagEidAllowBlank();
									this.refreshFieldsVisibility(['DeseaseBegTimeType_id', 'DeseaseType_id', 'TumorStage_id']);
									this.recountKoikoDni();
								}.createDelegate(this),
								'keydown': function(inp, e) {
									if ( e.getKey() == Ext.EventObject.TAB && e.shiftKey == true ) {
										e.stopEvent();
										this.buttons[this.buttons.length - 1].focus();
									}
								}.createDelegate(this)
							},
							name: 'EvnSection_disDate',
							plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
							selectOnFocus: true,
							tabIndex: this.tabIndex + 3,
							width: 100,
							xtype: 'swdatefield'
						}]
					}, {
						border: false,
						labelWidth: 50,
						layout: 'form',
						items: [{
							allowBlank: false,
							disabled: (getGlobalOptions().region && getGlobalOptions().region.nick != 'ufa'),
							fieldLabel: lang['vremya'],
							name: 'EvnSection_setTime',
							onTriggerClick: function() {
								return false;
							}.createDelegate(this),
							plugins: [ new Ext.ux.InputTextMask('99:99', true) ],
							tabIndex: this.tabIndex + 2,
							validateOnBlur: false,
							width: 60,
							xtype: 'swtimefield'
						}, {
							allowBlank: false,
							fieldLabel: lang['vremya'],
							listeners: {
								'change': function(field, newValue, oldValue) {
									this.changedDates = true;
									var base_form = this.FormPanel.getForm();
								}.createDelegate(this),
								'keydown': function (inp, e) {
									if (e.getKey() == Ext.EventObject.F4) {
										e.stopEvent();
										inp.onTriggerClick();
									}
								}
							},
							name: 'EvnSection_disTime',
							onTriggerClick: function() {
								var base_form = this.FormPanel.getForm();
								var time_field = base_form.findField('EvnSection_disTime');

								if ( time_field.disabled ) {
									return false;
								}

								setCurrentDateTime({
									callback: function() {
										base_form.findField('EvnSection_disDate').fireEvent('change', base_form.findField('EvnSection_disDate'), base_form.findField('EvnSection_disDate').getValue());
									}.createDelegate(this),
									dateField: base_form.findField('EvnSection_disDate'),
									loadMask: true,
									setDate: true,
									setDateMaxValue: true,
									addMaxDateDays: this.addMaxDateDays,
									setDateMinValue: false,
									setTime: true,
									timeField: time_field,
									windowId: this.id
								});
							}.createDelegate(this),
							plugins: [ new Ext.ux.InputTextMask('99:99', true) ],
							tabIndex: this.tabIndex + 4,
							validateOnBlur: false,
							width: 60,
							xtype: 'swtimefield'
						}]
					},
					{
						border:false,
						labelWidth:210,
						layout:'form',
						items: [{
							xtype: 'swyesnocombo',
							tabIndex:this.tabIndex + 5,
							name: 'EvnSection_IsAdultEscort',
							hiddenName: 'EvnSection_IsAdultEscort',
							listeners: {
								'change': function(combo, newValue, oldValue) {
									var index = combo.getStore().findBy(function(rec) {
										return (rec.get(combo.valueField) == newValue);
									});
									combo.fireEvent('select', combo, combo.getStore().getAt(index), index);
								},
								'select': function(combo, record, index) {
									if (getRegionNick() == 'astra') {
										this.setIsMedReason();
									}
								}.createDelegate(this)
							},
							allowBlank: true,
							value: 1,
							width: 70,
							fieldLabel: lang['soprovojdaetsya_vzroslyim']
						}, {
							xtype: 'swyesnocombo',
							tabIndex:this.tabIndex + 6,
							hiddenName: 'EvnSection_IsMedReason',
							allowBlank: true,
							value: 1,
							width: 70,
							fieldLabel: 'По медицинским показаниям'
						}]
					}]
				}, {
					hiddenName: 'GetRoom_id',
					fieldLabel: 'Палата',
					xtype: 'swbaselocalcombo',
					valueField: 'GetRoom_id',
					codeField: 'Number',
					displayField: 'NameSetRoomRuFull',
					store: new Ext.data.JsonStore({
						autoLoad: false,
						url: '/?c=EvnSection&m=getRoomList',
						fields: [
							{name: 'GetRoom_id', type: 'int'},
							{name: 'Number', type: 'string'},
							{name: 'NameSetRoomRuFull', type: 'string'},
						],
						key: 'GetRoom_id',
						sortInfo: {
							field: 'Number'
						}
					}),
					tpl: new Ext.XTemplate(
						'<tpl for="."><div class="x-combo-list-item">',
						'<font color="red">{Number}</font>&nbsp;{NameSetRoomRuFull}',
						'</div></tpl>'
					),
					width: 500,
					listWidth: 800,
					listeners: {
						'change': function(field, newValue, oldValue) {
							this.loadBedList(true);
						}.createDelegate(this)
					}
				}, {
					hiddenName: 'GetBed_id',
					fieldLabel: 'Койка',
					xtype: 'swbaselocalcombo',
					valueField: 'GetBed_id',
					codeField: 'BedProfile',
					displayField: 'BedProfileRuFull',
					store: new Ext.data.JsonStore({
						autoLoad: false,
						url: '/?c=EvnSection&m=getBedList',
						fields: [
							{name: 'GetBed_id', type: 'int'},
							{name: 'BedProfile', type: 'int'},
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
				},
				this.diagPanel, {
					xtype: 'swcommonsprcombo',
					comboSubject: 'DeseaseBegTimeType',
					hiddenName: 'DeseaseBegTimeType_id',
					fieldLabel: 'Время с начала заболевания',
					width: 300
				}, {
					allowSysNick: true,
					comboSubject: 'DeseaseType',
					fieldLabel: langs('Характер'),
					hiddenName: 'DeseaseType_id',
					listeners: {
						'change': function(combo, newValue, oldValue) {
							this.refreshFieldsVisibility(['TumorStage_id']);
						}.createDelegate(this)
					},
					moreFields: [
						{ name: 'DeseaseType_begDT', type: 'date', dateFormat: 'd.m.Y' },
						{ name: 'DeseaseType_endDT', type: 'date', dateFormat: 'd.m.Y' }
					],
					width: 300,
					xtype: 'swcommonsprcombo'
				}, {
					fieldLabel:langs('Стадия выявленного ЗНО'),
					hiddenName:'TumorStage_id',
					xtype:'swtumorstagenewcombo',
					width: 300,
					loadParams: getRegionNumber().inlist([58,66,101]) ? {mode: 1} : {mode:0} // только свой регион / + нулловый рег
				},{
					border: false,
					hidden: (getRegionNick().inlist(['kz','ufa'])),
                    layout: 'column',
                    items:[
                        {
                        	border: false,
							hidden: (getRegionNick().inlist(['kz','ufa'])),
                            layout: 'form',
                            items: [{
                                checkAccessRights: true,
								MKB: null,
                                fieldLabel: 'Внешняя причина',
                                hiddenName: 'Diag_eid',
                                registryType: 'ExternalCause',
                                baseFilterFn: function(rec){
                                	if(typeof rec.get == 'function'){
                                		return (rec.get('Diag_Code').search(new RegExp("^[VWXY]", "i")) >= 0);
                                	} else {
                                		return true;
                                	}
                                },
                                width: 500,
                                xtype: 'swdiagcombo'
                            }]
                        },
                        {
                        	border: false,
							hidden: (getRegionNick().inlist(['kz','ufa'])),
                            layout: 'form',
                            items: [{
								text:'=',
								tooltip:'Скопировать из приемного отделения',
								handler:function () {
									var win = this;
									var base_form = this.FormPanel.getForm();
									Ext.Ajax.request({
										url: '/?c=EvnSection&m=getPriemDiag',
										params: {
											EvnPS_id: base_form.findField('EvnSection_pid').getValue()
										},
										callback:function (options, success, response) {
											if (success) {
												var response_obj = Ext.util.JSON.decode(response.responseText);
												if (!Ext.isEmpty(response_obj.Diag_id)) {
													if(base_form.findField('Diag_eid').getStore().getById(response_obj.Diag_id)){
														base_form.findField('Diag_eid').setValue(response_obj.Diag_id);
													} else {
														base_form.findField('Diag_eid').getStore().load({
															params: {where:"where Diag_Code like 'X%' or Diag_Code like 'V%' or Diag_Code like 'W%' or Diag_Code like 'Y%'"},
															callback: function(){
																base_form.findField('Diag_eid').setValue(response_obj.Diag_id);
																win.setDiagEidAllowBlank();
															}
														});
													}
												}
											}
										}
									});
								}.createDelegate(this),
								id:'copyExternalCauseBtn',
								xtype:'button'
							}]
                        }
                    ]
                }, {
					border: false,
					layout: 'column',
					items: [{
						border: false,
						layout: 'form',
						items: [{
							beforeBlur: function() {
								return true;
							},
							displayField: 'Mes_Code',
							editable: true,
							enableKeyEvents: true,
							fieldLabel: getMESAlias(),
							forceSelection: false,
							hiddenName: 'Mes_id',
							listeners: {
								'change':function (combo, newValue, oldValue) {
									var base_form = this.FormPanel.getForm();

									var record = combo.getStore().getById(newValue);

									if (record) {
										if (record.get('Mes_Code')[0] && record.get('Mes_Code')[0] == 9) {
											if (this.action != 'view') {
												base_form.findField('Mes2_id').enable();
											}
										} else {
											base_form.findField('Mes2_id').clearValue();
											base_form.findField('Mes2_id').disable();
										}
									}
									else {
										base_form.findField('Mes2_id').clearValue();
										base_form.findField('Mes2_id').disable();
									}
								}.createDelegate(this),
								'keydown': function(inp, e) {
									//
								}.createDelegate(this)
							},
							mode: 'local',
							resizable: true,
							selectOnFocus: true,
							store: new Ext.data.Store({
								autoLoad: false,
								reader: new Ext.data.JsonReader({
									id: 'Mes_id'
								}, [
									{ name: 'Mes_id', mapping: 'Mes_id' },
									{ name: 'Mes_Code', mapping: 'Mes_Code' },
									{ name: 'Mes_KoikoDni', mapping: 'Mes_KoikoDni' },
									{ name: 'MedicalCareKind_Name', mapping: 'MedicalCareKind_Name'},
									{ name: 'MesAgeGroup_Name', mapping: 'MesAgeGroup_Name'},
									{ name: 'MesNewUslovie', mapping: 'MesNewUslovie', type: 'int'},
									{ name: 'MesOperType_Name', mapping: 'MesOperType_Name' }
								]),
								url: '/?c=EvnSection&m=loadMesList'
							}),
							tabIndex: this.tabIndex + 8,
							tpl: mesTemplate,
							triggerAction: 'all',
							valueField: 'Mes_id',
							width: (getGlobalOptions().region && getGlobalOptions().region.nick == 'ufa')?222:500,
							xtype: 'combo'
						}]
					}, {
						border: false,
						labelWidth: 50,
						layout: 'form',
						hidden: getRegionNick() != 'ufa',
						items: [{
							displayField: 'Mes2_Code',
							editable: true,
							enableKeyEvents: true,
							fieldLabel: getMESAlias() + '2',
							forceSelection: false,
							disabled: true,
							hiddenName: 'Mes2_id',
							mode: 'local',
							resizable: true,
							selectOnFocus: true,
							store: new Ext.data.Store({
								autoLoad: false,
								reader: new Ext.data.JsonReader({
									id: 'Mes2_id'
								}, [
									{ name: 'Mes2_id', mapping: 'Mes2_id' },
									{ name: 'Mes2_Code', mapping: 'Mes2_Code' },
									{ name: 'Mes2_KoikoDni', mapping: 'Mes2_KoikoDni' }
								]),
								url: '/?c=EvnSection&m=loadMes2List'
							}),
							tabIndex: this.tabIndex + 9,
							tpl: new Ext.XTemplate(
								'<table cellpadding="0" cellspacing="0" style="width: 100%;"><tr style="font-family: tahoma; font-size: 10pt; font-weight: bold;">',
								'<td style="padding: 2px; width: 50%;">Код</td>',
								'<td style="padding: 2px; width: 50%;">Нормативный срок</td></tr>',
								'<tpl for="."><tr class="x-combo-list-item" style="white-space: normal; overflow: auto; text-overflow: clip;">',
								'<td style="padding: 2px;">{Mes2_Code}&nbsp;</td>',
								'<td style="padding: 2px;">{Mes2_KoikoDni}&nbsp;</td>',
								'</tr></tpl>',
								'</table>'
							),
							triggerAction: 'all',
							valueField: 'Mes2_id',
							width: 222,
							xtype: 'combo'
						}]
					}]
				},
				this.MesSidField, {
					border: false,
					hidden: !getRegionNick().inlist([ 'kaluga' ]),
					layout: 'form',
					xtype: 'panel',
					items: [{
						allowBlank: !getRegionNick().inlist([ 'kaluga' ]),
						fieldLabel: 'Профиль',
						hiddenName: 'LpuSectionProfile_id',
						listeners: {
							'change': function(combo, newValue, oldValue) {
								if ( getRegionNick().inlist(['kaluga']) ) {
									this.filterLpuSectionBedProfilesByLpuSectionProfile(newValue, 'LpuSectionBedProfile_id');
								}
							}.createDelegate(this)
						},
						listWidth: 600,
						tabIndex: this.tabIndex + 10,
						width: 500,
						xtype: 'swlpusectionprofiledopremotecombo'
					}, {
						allowBlank: !getRegionNick().inlist([ 'kaluga' ]),
						hiddenName: 'LpuSectionBedProfile_id',
						tabIndex: this.tabIndex + 11,
						width:500,
						xtype:'swlpusectionbedprofilecombo'
					}]
				}]
			}),
			new sw.Promed.Panel({
				autoHeight: true,
				bodyStyle: 'padding-top: 0.5em;',
				border: true,
				collapsible: true,
				id: 'EESecEF_EvnLeavePanel',
				layout: 'form',
				listeners: {
					'expand': function(panel) {
						// this.findById('EmkEvnSectionEditForm').getForm().findField('EvnSection_setDate').focus(true);
					}.createDelegate(this)
				},
				style: 'margin-bottom: 0.5em;',
				title: lang['2_ishod_gospitalizatsii'],
				items: [{
					fieldLabel: (getRegionNick().inlist([ 'kareliya', 'khak' ])?lang['rezultat_gospitalizatsii']:lang['ishod_gospitalizatsii']),
					hiddenName: 'LeaveTypeFed_id',
					listeners: {
						'change': function (combo, newValue, oldValue) {
							var index = combo.getStore().findBy(function (rec) {
								return (rec.get('LeaveType_id') == newValue);
							});

							combo.fireEvent('select', combo, combo.getStore().getAt(index));
						},
						'select': function (combo, record) {
							var base_form = this.FormPanel.getForm();
							var LeaveTypeCombo = base_form.findField('LeaveType_id');
							LeaveTypeCombo.clearValue();

							if ( typeof record == 'object' ) {
								LeaveTypeCombo.setFieldValue('LeaveType_fedid', record.get('LeaveType_id'));

								switch ( record.get('LeaveType_SysNick') ) {
									case 'ksdie':
									case 'dsdie':
										base_form.findField('EvnDie_IsWait').setValue(1);
									break;

									case 'diepp':
									case 'ksdiepp':
									case 'dsdiepp':
										base_form.findField('EvnDie_IsWait').setValue(2);
									break;
								}
							}
							
							var index = LeaveTypeCombo.getStore().findBy(function (rec) {
								return (rec.get('LeaveType_id') == LeaveTypeCombo.getValue());
							});

							LeaveTypeCombo.fireEvent('select', LeaveTypeCombo, LeaveTypeCombo.getStore().getAt(index));
						}.createDelegate(this)
					},
					tabIndex:this.tabIndex + 12,
					width:300,
					xtype: 'swleavetypefedcombo'
				}, {
					autoLoad: false,
					fieldLabel: (getRegionNick().inlist([ 'kareliya' ])?lang['rezultat_gospitalizatsii']:lang['ishod_gospitalizatsii']),
					hiddenName: 'LeaveType_id',
					listeners: {
						'change':function (combo, newValue, oldValue) {
							var index = combo.getStore().findBy(function (rec) {
								return (rec.get('LeaveType_id') == newValue);
							});

							combo.fireEvent('select', combo, combo.getStore().getAt(index));
						}.createDelegate(this),
						'select': function(combo, record) {
							var isBuryatiya = (getRegionNick() == 'buryatiya');
							var isKareliya = (getRegionNick() == 'kareliya');
							var isPenza = (getRegionNick() == 'penza');
							var isUfa = (getRegionNick() == 'ufa');
							var base_form = this.FormPanel.getForm();
							this.leaveCauseFilter();
							this.setPatoMorphGistDirection();

							// 1. Чистим и скрываем все поля
							// 2. В зависимости от выбранного значения, открываем поля

							this.findById('EESecEF_AnatomPanel').hide();
							this.findById('EESecEF_AnatomDiagPanel').hide();

							base_form.findField('AnatomWhere_id').setAllowBlank(true);
							base_form.findField('Diag_aid').setAllowBlank(true);
							base_form.findField('EvnDie_expTime').setAllowBlank(true);
							base_form.findField('LpuSection_aid').setAllowBlank(true);
							base_form.findField('Org_aid').setAllowBlank(true);
							base_form.findField('MedStaffFact_aid').setAllowBlank(true);

							base_form.findField('EvnDie_IsWait').setAllowBlank(true);
							base_form.findField('EvnDie_IsWait').setContainerVisible(false);
							base_form.findField('EvnDie_IsAnatom').setAllowBlank(true);
							base_form.findField('EvnDie_IsAnatom').setContainerVisible(false);
							base_form.findField('EvnLeave_IsAmbul').setAllowBlank(true);
							base_form.findField('EvnLeave_IsAmbul').setContainerVisible(false);
							base_form.findField('EvnLeave_UKL').setAllowBlank(true);
							base_form.findField('EvnLeave_UKL').setContainerVisible(false);
							base_form.findField('LeaveCause_id').setAllowBlank(true);
							base_form.findField('LeaveCause_id').setContainerVisible(false);
							base_form.findField('Org_oid').setAllowBlank(true);
							base_form.findField('Org_oid').setContainerVisible(false);
							base_form.findField('LpuSection_oid').setAllowBlank(true);
							base_form.findField('LpuSection_oid').setContainerVisible(false);
							//base_form.findField('LpuSectionBedProfile_oid').setAllowBlank(true);
							base_form.findField('LpuSectionBedProfile_oid').setContainerVisible(false);
							base_form.findField('LpuUnitType_oid').setAllowBlank(true);
							base_form.findField('LpuUnitType_oid').setContainerVisible(false);
							base_form.findField('MedStaffFact_did').setAllowBlank(true);
							base_form.findField('MedStaffFact_did').setContainerVisible(false);
							base_form.findField('DeathPlace_id').setAllowBlank(true);
							base_form.findField('DeathPlace_id').setContainerVisible(false);
							base_form.findField('ResultDesease_id').setAllowBlank(true);
							base_form.findField('ResultDesease_id').setContainerVisible(false);

							diag_a_phase_combo = base_form.findField('DiagSetPhase_aid');
							diag_a_phase_combo.setAllowBlank(true);
							diag_a_phase_combo.setContainerVisible(false);
										
							this.refreshFieldsVisibility(['PayTypeERSB_id']);
							if (getRegionNick() == 'kz' && base_form.findField('PayTypeERSB_id').isVisible()) {
								var BedProfile = base_form.findField('BedProfile').getValue();
								if (!!BedProfile && BedProfile.inlist(8200, 8300, 10100, 10200, 10300, 10400, 10500, 10600, 10700, 10800, 10900, 11000, 11100, 11200, 11300, 11400)) {
									base_form.findField('PayTypeERSB_id').setValue(2);
								} else if (!!BedProfile && BedProfile.inlist(1700, 1800, 13300)) {
									base_form.findField('PayTypeERSB_id').setValue(3);
								} else {
									base_form.findField('PayTypeERSB_id').setValue(1);
								}
							}

							if (getRegionNick().inlist(['astra', 'krasnoyarsk', 'krym', 'perm'])) {
								base_form.findField('CureResult_id').setAllowBlank(getRegionNick() != 'astra');
								base_form.findField('CureResult_id').setContainerVisible(false);
								base_form.findField('CureResult_id').getStore().clearFilter();

								if ( !getRegionNick().inlist([ 'krasnoyarsk', 'krym' ]) ) {
									base_form.findField('CureResult_id').setFieldValue('CureResult_Code', 1);//Значение по умолчанию
								}
							}

                            sw.Promed.EvnSection.calcFedLeaveType({
                                date: base_form.findField('EvnSection_disDate').getValue(),
                                LpuUnitType_SysNick: base_form.findField('LpuUnitType_SysNick').getValue(),
                                LeaveType_SysNick: base_form.findField('LeaveType_id').getFieldValue('LeaveType_SysNick')|| null,
                                LeaveCause_Code: base_form.findField('LeaveCause_id').getFieldValue('LeaveCause_Code'),
                                fieldFedLeaveType: base_form.findField('LeaveType_fedid'),
								fieldFedResultDeseaseType: base_form.findField('ResultDeseaseType_fedid')
                            });
                            sw.Promed.EvnSection.calcFedResultDeseaseType({
                                date: base_form.findField('EvnSection_disDate').getValue(),
                                LpuUnitType_SysNick: base_form.findField('LpuUnitType_SysNick').getValue(),
                                LeaveType_SysNick:  base_form.findField('LeaveType_id').getFieldValue('LeaveType_SysNick') || null,
                                ResultDesease_Code: base_form.findField('ResultDesease_id').getFieldValue('ResultDesease_Code'),
                                fieldFedResultDeseaseType: base_form.findField('ResultDeseaseType_fedid')
                            });
							
							sw.Promed.EvnSection.filterFedResultDeseaseType({
								LpuUnitType_SysNick: base_form.findField('LpuUnitType_SysNick').getValue(),
								fieldFedLeaveType: base_form.findField('LeaveType_fedid'),
								fieldFedResultDeseaseType: base_form.findField('ResultDeseaseType_fedid')
							})
							sw.Promed.EvnSection.filterFedLeaveType({
								LpuUnitType_SysNick: base_form.findField('LpuUnitType_SysNick').getValue(),
								fieldFedLeaveType: base_form.findField('LeaveType_fedid'),
								fieldLeaveType: base_form.findField('LeaveType_id'),
								fieldFedResultDeseaseType: base_form.findField('ResultDeseaseType_fedid')
							});
							
							if ( typeof record != 'object' || Ext.isEmpty(record.get('LeaveType_id')) ) {
								if (getRegionNick().inlist(['astra', 'krasnoyarsk', 'krym', 'perm', 'kareliya'])) {
									base_form.findField('CureResult_id').clearValue();
									base_form.findField('CureResult_id').fireEvent('change', base_form.findField('CureResult_id'), base_form.findField('CureResult_id').getValue());
								}
								return true;
							}

							base_form.findField('EvnLeave_UKL').setAllowBlank(false);
							base_form.findField('EvnLeave_UKL').setContainerVisible(true);

							if (getRegionNick().inlist(['astra', 'krasnoyarsk', 'krym', 'perm'])) {
								base_form.findField('CureResult_id').setContainerVisible(true);
								base_form.findField('CureResult_id').enable();
								base_form.findField('CureResult_id').setAllowBlank(false);

								if (getRegionNick() == 'perm') {
									switch ( record.get('LeaveType_SysNick') ) {
										case 'stac':
										case 'section':
											base_form.findField('CureResult_id').setFieldValue('CureResult_Code', 2);
											break;
										default:
											base_form.findField('CureResult_id').disable();
											break;
									}
								}
								else if (getRegionNick() == 'krasnoyarsk') {
									if (!Ext.isEmpty(record.get('LeaveType_SysNick'))) {
										if (
											record.get('LeaveType_SysNick').inlist(['ksleave', 'ksother',
												'ksdie', 'ksinicpac', 'ksiniclpu', 'ksprerv', 'dsleave',
												'dsother', 'dsdie', 'dsinicpac', 'dsiniclpu'
											])
										) {
											base_form.findField('CureResult_id').setFieldValue('CureResult_Code', 1);
										}
										else {
											base_form.findField('CureResult_id').setFieldValue('CureResult_Code', 2);
										}
									}
								}
								else if (getRegionNick() == 'krym') {
									if ( !Ext.isEmpty(record.get('LeaveType_SysNick')) ) {
										if ( !record.get('LeaveType_SysNick').inlist([ 'ksper', 'dsper', 'ksprod', 'psprod' ]) ) {
											base_form.findField('CureResult_id').disable();
											base_form.findField('CureResult_id').setFieldValue('CureResult_Code', 1);
										}
									}
								}
								else if (getRegionNick() == 'astra') {
									switch ( record.get('LeaveType_SysNick') ) {
										case 'section':
											base_form.findField('CureResult_id').setFieldValue('CureResult_Code', 3);
											break;
									}
								}
							}

							if ( !base_form.findField('EvnLeave_UKL').getValue() ) {
								base_form.findField('EvnLeave_UKL').setValue(1);
							}

							diag_a_phase_combo.setAllowBlank(false);
							diag_a_phase_combo.setContainerVisible(true);

							if ( record.get('LeaveType_SysNick') && record.get('LeaveType_SysNick').inlist(sw.Promed.EvnSection.listLeaveTypeSysNickEvnOtherLpu) ) {
								// Перевод в другую МО
								base_form.findField('LeaveCause_id').setAllowBlank(false);
								base_form.findField('LeaveCause_id').setContainerVisible(true);
								base_form.findField('Org_oid').setAllowBlank(false);
								base_form.findField('Org_oid').setContainerVisible(true);
								base_form.findField('ResultDesease_id').setAllowBlank(false);
								base_form.findField('ResultDesease_id').setContainerVisible(true);
								base_form.findField('LeaveCause_id').setFieldLabel(lang['prichina_perevoda']);
							}
							if ( record.get('LeaveType_SysNick') && record.get('LeaveType_SysNick').inlist(sw.Promed.EvnSection.listLeaveTypeSysNickEvnOtherStac) ) {
								// Перевод в стационар другого типа
								if ( isKareliya || isBuryatiya || isPenza ) {
									var LpuUnitType_SysNick = base_form.findField('LpuUnitType_SysNick').getValue();

									if ( !Ext.isEmpty(LpuUnitType_SysNick) ) {
										if ( record.get('LeaveType_SysNick') == 'ksstac' && LpuUnitType_SysNick == 'stac' ) {
											base_form.findField('LpuUnitType_oid').getStore().filterBy(function (rec) {
												return (rec.get('LpuUnitType_Code').inlist([3,4,5]));
											});
										}
										else if ( record.get('LeaveType_SysNick') == 'dsstac' && LpuUnitType_SysNick.inlist([ 'dstac', 'hstac' ])) {
											base_form.findField('LpuUnitType_oid').getStore().filterBy(function (rec) {
												return (rec.get('LpuUnitType_Code').inlist([2,3,4]));
											});
										}
									}
								}
								base_form.findField('LeaveCause_id').setContainerVisible(true);
								base_form.findField('LpuSection_oid').setAllowBlank(false);
								base_form.findField('LpuSection_oid').setContainerVisible(true);
								base_form.findField('LpuUnitType_oid').setAllowBlank(false);
								base_form.findField('LpuUnitType_oid').setContainerVisible(true);
								base_form.findField('ResultDesease_id').setAllowBlank(false);
								base_form.findField('ResultDesease_id').setContainerVisible(true);

								base_form.findField('LeaveCause_id').setFieldLabel(lang['prichina_perevoda']);

								base_form.findField('LpuUnitType_oid').fireEvent('change', base_form.findField('LpuUnitType_oid'), base_form.findField('LpuUnitType_oid').getValue());
							}



							if (isKareliya){

								// Поле видимо и обязательно для заполнения, если поле «Результат госпитализации» имеет ненулевое значение.
								if ( ! Ext.isEmpty(record.get('LeaveType_SysNick'))) {

									// поле видимо
									base_form.findField('CureResult_id').setContainerVisible(true);

									// поле обязательно
									base_form.findField('CureResult_id').setAllowBlank(false);

									if( ! this.isProcessLoadForm){

										// Если в поле «Результат госпитализации» указано значение "103 Переведён в дневной стационар" или "203 переведён в стационар",
										// то поле доступно для редактирования. При этом по умолчанию подставляется значение "Лечение завершено".
										if (record.get('LeaveType_SysNick').inlist([ 'ksstac', 'dsstac']) ) {

											// Лечение завершено
											base_form.findField('CureResult_id').setFieldValue('CureResult_Code', 1);

											base_form.findField('CureResult_id').enable();
										}
										else {

											// При других значениях поля «Результат госпитализации» поле не доступно для редактирования, но заполняется.
											base_form.findField('CureResult_id').disable();

											// Для значений "104. Переведён на другой профиль коек" и "204. Переведён на другой профиль коек"
											// указывается «Лечение продолжено».
											if (record.get('LeaveType_SysNick').inlist([ 'ksper', 'dsper']) ) {

												// Лечение продолжено
												base_form.findField('CureResult_id').setFieldValue('CureResult_Code', 2);

											}
											else { // Для других значений указывается «Лечение завершено».

												// Лечение завершено
												base_form.findField('CureResult_id').setFieldValue('CureResult_Code', 1);
											}
										}


									}
								}
							}


							switch ( record.get('LeaveType_SysNick') ) {
								// Выписка
								case 'leave':
								case 'ksleave':
								case 'dsleave':
									base_form.findField('EvnLeave_IsAmbul').setAllowBlank(false);
									base_form.findField('EvnLeave_IsAmbul').setContainerVisible(true);
									base_form.findField('LeaveCause_id').setAllowBlank(false);
									base_form.findField('LeaveCause_id').setContainerVisible(true);
									base_form.findField('ResultDesease_id').setAllowBlank(false);
									base_form.findField('ResultDesease_id').setContainerVisible(true);

									base_form.findField('LeaveCause_id').setFieldLabel(lang['prichina_vyipiski']);

									//base_form.findField('LeaveCause_id').fireEvent('change', base_form.findField('LeaveCause_id'), base_form.findField('LeaveCause_id').getValue());

									if ( !base_form.findField('EvnLeave_IsAmbul').getValue() ) {
										base_form.findField('EvnLeave_IsAmbul').setValue(1);
									}

									if (getRegionNick() == 'astra'){
										base_form.findField('CureResult_id').setAllowBlank(false);

										base_form.findField('CureResult_id').getStore().filterBy(function(rec){
											return rec.get('CureResult_id') != 3;
										});

										if (Ext.isEmpty(base_form.findField('CureResult_id').getStore().getAt(base_form.findField('CureResult_id').getValue()))) {
											base_form.findField('CureResult_id').clearValue();
										}
									}

									if (getRegionNick() == 'khak' && base_form.findField('ResultDesease_id').getFieldValue('ResultDesease_Code') == 6 ){
										this.findById('EESecEF_AnatomPanel').show();
										base_form.findField('LeaveCause_id').setContainerVisible(false);
										base_form.findField('LeaveCause_id').setAllowBlank(true);
										base_form.findField('EvnLeave_IsAmbul').setContainerVisible(false);
										base_form.findField('MedStaffFact_did').setContainerVisible(true);
										base_form.findField('MedStaffFact_did').setAllowBlank(false);
										base_form.findField('EvnDie_IsAnatom').setAllowBlank(false);
										base_form.findField('EvnDie_IsAnatom').setContainerVisible(true);
									}
								break;

								// Смерть
								case 'die':
								case 'ksdie':
								case 'diepp':
								case 'ksdiepp':
								case 'dsdie':
								case 'dsdiepp':
								case 'kslet':
								case 'ksletitar':
									this.findById('EESecEF_AnatomPanel').show();
									if ( isKareliya || isBuryatiya || isPenza ) {
										base_form.findField('EvnDie_IsWait').setAllowBlank(false);
										base_form.findField('EvnDie_IsWait').setContainerVisible(true);

										switch ( record.get('LeaveType_SysNick') ) {
											case 'ksdie':
											case 'dsdie':
											case 'kslet':
											case 'ksletitar':
												base_form.findField('EvnDie_IsWait').setValue(1);
											break;

											case 'diepp':
											case 'ksdiepp':
											case 'dsdiepp':
												base_form.findField('EvnDie_IsWait').setValue(2);
											break;
										}
									}
									diag_a_phase_combo.setAllowBlank(true);
									diag_a_phase_combo.setContainerVisible(false);
									base_form.findField('EvnDie_IsAnatom').setAllowBlank(false);
									base_form.findField('EvnDie_IsAnatom').setContainerVisible(true);
									base_form.findField('MedStaffFact_did').setAllowBlank(false);
									base_form.findField('MedStaffFact_did').setContainerVisible(true);
									base_form.findField('DeathPlace_id').setAllowBlank(true);
									base_form.findField('DeathPlace_id').setContainerVisible(false);
									this.setPatoMorphGistDirection();

									base_form.findField('EvnDie_IsAnatom').fireEvent('change', base_form.findField('EvnDie_IsAnatom'), base_form.findField('EvnDie_IsAnatom').getValue());

									if ( base_form.findField('EvnDie_IsAnatom').getValue() == 2 ) {
										base_form.findField('AnatomWhere_id').fireEvent('change', base_form.findField('AnatomWhere_id'), base_form.findField('AnatomWhere_id').getValue());
									}

									if (getRegionNick() == 'astra'){
										base_form.findField('CureResult_id').setAllowBlank(false);
										base_form.findField('CureResult_id').getStore().filterBy(function(rec){
											return rec.get('CureResult_id') != 3;
										});

										if (Ext.isEmpty(base_form.findField('CureResult_id').getStore().getAt(base_form.findField('CureResult_id').getValue()))) {
											base_form.findField('CureResult_id').clearValue();
										}
									}
								break;

								// Перевод в другое отделение
								case 'section':
								case 'dstac':
								case 'kstac':
									base_form.findField('LeaveCause_id').setContainerVisible(true);
									base_form.findField('LpuSection_oid').setAllowBlank(false);
									base_form.findField('LpuSection_oid').setContainerVisible(true);
									base_form.findField('ResultDesease_id').setAllowBlank(false);
									base_form.findField('ResultDesease_id').setContainerVisible(true);

									base_form.findField('LeaveCause_id').setFieldLabel(lang['prichina_perevoda']);

									var date = base_form.findField('EvnSection_disDate').getValue();
									var lpu_section_oid = base_form.findField('LpuSection_oid').getValue();
									var params = new Object();

									base_form.findField('LpuSection_oid').clearValue();

									params.isStac = true;

									if ( getRegionNick() == 'khak' ) {
										if ( record.get('LeaveType_SysNick') == 'dstac' ) {
											params.arrayLpuUnitType = [ 3, 5 ];
										}
										else {
											params.arrayLpuUnitType = [ 2 ];
										}
									}

									if ( typeof date == 'object' ) {
										params.onDate = Ext.util.Format.date(date, 'd.m.Y');
									}

									var WithoutChildLpuSectionAge = false;
									var Person_Birthday = this.findById('EESecEF_PersonInformationFrame').getFieldValue('Person_Birthday');

									if ( typeof date == 'object' ) {
										var age = swGetPersonAge(Person_Birthday, date);
									}
									else {
										var age = swGetPersonAge(Person_Birthday, new Date());
									}

									if ( age >= 18 &&!isUfa) {
										params.WithoutChildLpuSectionAge = true;
									}

									setLpuSectionGlobalStoreFilter(params);

									base_form.findField('LpuSection_oid').getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));

									if ( base_form.findField('LpuSection_oid').getStore().getById(lpu_section_oid) ) {
										base_form.findField('LpuSection_oid').setValue(lpu_section_oid);
									}
								break;

								// Перевод на другой профиль коек
								case 'ksper':
								case 'dsper':
									base_form.findField('LeaveCause_id').setContainerVisible(true);
									base_form.findField('LpuSection_oid').setAllowBlank(false);
									base_form.findField('LpuSection_oid').setContainerVisible(true);
									//base_form.findField('LpuSectionBedProfile_oid').setAllowBlank(false);
									base_form.findField('LpuSectionBedProfile_oid').setContainerVisible(true);
									base_form.findField('ResultDesease_id').setAllowBlank(false);
									base_form.findField('ResultDesease_id').setContainerVisible(true);

									base_form.findField('LeaveCause_id').setFieldLabel(lang['prichina_perevoda']);

									var date = base_form.findField('EvnSection_disDate').getValue();
									var lpu_section_oid = base_form.findField('LpuSection_oid').getValue();
									var params = new Object();

									base_form.findField('LpuSection_oid').clearValue();

									params.arrayLpuUnitType = [ base_form.findField('LpuUnitType_Code').getValue() ]
									params.isStac = true;

									if ( date ) {
										params.onDate = Ext.util.Format.date(date, 'd.m.Y');
									}

									var WithoutChildLpuSectionAge = false;
									var Person_Birthday = this.findById('EESecEF_PersonInformationFrame').getFieldValue('Person_Birthday');
									if (date) {
										var age = swGetPersonAge(Person_Birthday, date);
									} else {
										var age = swGetPersonAge(Person_Birthday, new Date());
									}
									if (age >= 18&&!isUfa) {
										params.WithoutChildLpuSectionAge = true;
									}
									
									setLpuSectionGlobalStoreFilter(params);

									base_form.findField('LpuSection_oid').getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));

									if (base_form.findField('LpuSection_oid').getStore().getById(lpu_section_oid)) {
										base_form.findField('LpuSection_oid').setValue(lpu_section_oid);
									}

									base_form.findField('LpuSection_oid').fireEvent('change', base_form.findField('LpuSection_oid'), base_form.findField('LpuSection_oid').getValue());
								break;

								// https://redmine.swan.perm.ru/issues/30661
								// 107. Лечение прервано по инициативе пациента
								// 108. Лечение прервано по инициативе МО
								// 110. Самовольно прерванное лечение
								// 207. Лечение прервано по инициативе пациента
								// 208. Лечение прервано по инициативе МО
								case 'inicpac':
								case 'ksinicpac':
								case 'ksiniclpu':
								case 'iniclpu':
								case 'prerv':
								case 'ksprerv':
								case 'dsinicpac':
								case 'dsiniclpu':
								case 'ksprod':
									base_form.findField('ResultDesease_id').setAllowBlank(false);
									base_form.findField('ResultDesease_id').setContainerVisible(true);
								break;
							}

							base_form.findField('LeaveType_SysNick').setValue(record.get('LeaveType_SysNick'));

							if (base_form.findField('ResultDesease_id').hidden) {
								// если поле в итоге скрыли, то очистим его
								base_form.findField('ResultDesease_id').clearValue();
							}

							if ( getRegionNick().inlist([ 'astra', 'krasnoyarsk', 'krym', 'perm', 'kareliya' ]) ) {
								base_form.findField('CureResult_id').fireEvent('change', base_form.findField('CureResult_id'), base_form.findField('CureResult_id').getValue());
							}

                            if (
                                this.action == 'add'
                                && record.get('LeaveType_SysNick').toString().inlist([ 'other', 'stac', 'ksother', 'ksstac', 'dsother', 'dsstac' ])
                                && base_form.findField('EvnSection_pid').getValue() > 0
                            ) {
                                sw.Promed.Direction.loadDirectionDataForLeave({
                                    loadMask: this.getLoadMask(lang['podojdite_idet_poluchenie_dannyih_napravleniya']),
                                    EvnClass_SysNick: 'EvnPS',
                                    Evn_rid: base_form.findField('EvnSection_pid').getValue(),
                                    callback: function(data) {
                                        if (data && data.Org_oid) {
                                            var Org_oidCombo = base_form.findField('Org_oid');
                                            Org_oidCombo.getStore().load({
                                                callback: function(records, options, success) {
													if ( Org_oidCombo.getStore().getCount() > 0 ) {
														Org_oidCombo.setValue(data.Org_oid);
													}
													else {
														Org_oidCombo.clearValue();
													}
                                                },
                                                params: {
                                                    Org_id: data.Org_oid
                                                }
                                            });
                                        }
                                    }
                                });
                            }
						}.createDelegate(this),
						'keydown': function(inp, e) {
							if ( e.getKey() == Ext.EventObject.TAB && e.shiftKey == true ) {
								e.stopEvent();

								var base_form = this.FormPanel.getForm();

								if ( !this.findById('EESecEF_EvnSectionPanel').collapsed && !base_form.findField('Mes_id').disabled ) {
									base_form.findField('Mes_id').focus();
								}
								else {
									this.buttons[this.buttons.length - 1].focus();
								}
							}
						}.createDelegate(this)
					},
					tabIndex: this.tabIndex + 13,
					typeCode: 'int',
					width: 300,
					xtype: 'swleavetypecombo'
				}, {
					allowDecimals: true,
					allowNegative: false,
					fieldLabel: lang['uroven_kachestva_lecheniya'],
					maxValue: 1,
					minValue: 0,
					name: 'EvnLeave_UKL',
					tabIndex: this.tabIndex + 14,
					width: 70,
					value: 1,
					xtype: 'numberfield'
				}, {
					autoLoad: false,
					comboSubject: 'ResultDesease',
					fieldLabel: ('kareliya' == getRegionNick() ? lang['ishod_gospitalizatsii'] : ('khak' == getRegionNick() ? lang['rezultat_gospitalizatsii'] : lang['ishod_zabolevaniya'])),
					hiddenName: 'ResultDesease_id',
					lastQuery: '',
					listWidth: 700,
					tabIndex: this.tabIndex + 15,
					typeCode: 'int',
					listeners: {
						'change': function (combo, newValue, oldValue) {
							var index = combo.getStore().findBy(function (rec) {
								return (rec.get('LeaveType_id') == newValue);
							});

							combo.fireEvent('select', combo, combo.getStore().getAt(index));
						},
						'select': function (combo, record) {
							var base_form = _this.findById('EmkEvnSectionEditForm').getForm();
							var LeaveTypeCombo = base_form.findField('LeaveType_id');

							var index = LeaveTypeCombo.getStore().findBy(function (rec) {
								return (rec.get('LeaveType_id') == LeaveTypeCombo.getValue());
							});

							LeaveTypeCombo.fireEvent('select', LeaveTypeCombo, LeaveTypeCombo.getStore().getAt(index));
						}
					},
					width: 500,
					xtype: 'swcommonsprcombo'
				}, {
					autoLoad: false,
					fieldLabel: lang['prichina_vyipiski'],
					hiddenName: 'LeaveCause_id',
					tabIndex: this.tabIndex + 16,
					typeCode: 'int',
					width: 300,
					xtype: 'swleavecausecombo'
				}, {
					autoLoad: false,
					comboSubject: 'YesNo',
					fieldLabel: lang['napravlen_na_amb_lechenie'],
					hiddenName: 'EvnLeave_IsAmbul',
					tabIndex: this.tabIndex + 17,
					typeCode: 'int',
					width: 70,
					xtype: 'swcommonsprcombo'
				}, {
					displayField: 'Org_Name',
					editable: false,
					enableKeyEvents: true,
					fieldLabel: lang['lpu'],
					hiddenName: 'Org_oid',
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
						var base_form = this.FormPanel.getForm();
						var combo = base_form.findField('Org_oid');

						if ( combo.disabled ) {
							return false;
						}

						getWnd('swOrgSearchWindow').show({
							OrgType_id: 11,
							onClose: function() {
								combo.focus(true, 200)
							},
							onSelect: function(org_data) {
								if ( org_data.Org_id > 0 ) {
									combo.getStore().loadData([{
										Org_id: org_data.Org_id,
										Lpu_id:org_data.Lpu_id,
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
							{ name: 'Org_id', type: 'int' },
							{ name: 'Lpu_id', type: 'int' },
							{ name: 'Org_Name', type: 'string' }
						],
						key: 'Org_id',
						sortInfo: {
							field: 'Org_Name'
						},
						url: C_ORG_LIST
					}),
					tabIndex: this.tabIndex + 18,
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
					autoLoad: false,
					comboSubject: 'LpuUnitType',
					fieldLabel: lang['tip_statsionara'],
					hiddenName: 'LpuUnitType_oid',
					lastQuery: '',
					listeners: {
						'change': function(combo, newValue, oldValue) {
							var base_form = this.FormPanel.getForm();
							var isUfa = (getGlobalOptions().region && getGlobalOptions().region.nick == 'ufa');
							var date = base_form.findField('EvnSection_disDate').getValue();
							var lpu_section_oid = base_form.findField('LpuSection_oid').getValue();
							var params = new Object();
							var record = combo.getStore().getById(newValue);

							base_form.findField('LpuSection_oid').clearValue();

							params.isStac = true;

							if ( record ) {
								params.arrayLpuUnitType = [ record.get('LpuUnitType_Code') ];
							}

							if ( date ) {
								params.onDate = Ext.util.Format.date(date, 'd.m.Y');
							}

							var WithoutChildLpuSectionAge = false;
							var Person_Birthday = this.findById('EESecEF_PersonInformationFrame').getFieldValue('Person_Birthday');
							if (date) {
								var age = swGetPersonAge(Person_Birthday, date);
							} else {
								var age = swGetPersonAge(Person_Birthday, new Date());
							}
							if (age >= 18&&!isUfa) {
								params.WithoutChildLpuSectionAge = true;
							}
													
							setLpuSectionGlobalStoreFilter(params);

							base_form.findField('LpuSection_oid').getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));

							if ( base_form.findField('LpuSection_oid').getStore().getById(lpu_section_oid) ) {
								base_form.findField('LpuSection_oid').setValue(lpu_section_oid);
							}
						}.createDelegate(this)
					},
					tabIndex: this.tabIndex + 19,
					typeCode: 'int',
					width: 300,
					xtype: 'swcommonsprcombo'
				}, {
					fieldLabel: lang['otdelenie'],
					hiddenName: 'LpuSection_oid',
					listWidth: 650,
					tabIndex: this.tabIndex + 20,
					width: 500,
					xtype: 'swlpusectionglobalcombo'
				}, {
					fieldLabel: lang['profil_koek'],
					hiddenName: 'LpuSectionBedProfile_oid',
					tabIndex: this.tabIndex + 21,
					width: 500,
					xtype: 'swlpusectionbedprofilecombo'
				}, {
					fieldLabel: lang['vrach_ustanovivshiy_smert'],
					hiddenName: 'MedStaffFact_did',
					listWidth: 650,
					tabIndex: this.tabIndex + 22,
					width: 500,
					xtype: 'swmedstafffactglobalcombo'
				},{
                    dateFieldId: this.id + 'ESecEF_EvnSection_disDate',
                    //enableOutOfDateValidation: true,
                    fieldLabel:lang['mesto_smerti'],
                    hiddenName:'DeathPlace_id',
                    id: this.id + '_DeathPlace_id',
                    allowSysNick: true,
                    autoLoad: false,
                    comboSubject: 'DeathPlace',
                    listWidth:650,
                    tabIndex:this.tabIndex + 23,
                    width:500,
                    xtype:'swcommonsprcombo'
                },{
					autoLoad: false,
					comboSubject: 'YesNo',
					fieldLabel: lang['umer_v_priemnom_pokoe'],
					hiddenName: 'EvnDie_IsWait',
					tabIndex: this.tabIndex + 24,
					typeCode: 'int',
					width: 70,
					xtype: 'swcommonsprcombo'
				}, {
					border:false,
					layout:'column',
					items:[{
						border:false,
						layout:'form',
							items:[{
								autoLoad: false,
								comboSubject: 'YesNo',
								fieldLabel: lang['neobhodimost_ekspertizyi'],
								hiddenName: 'EvnDie_IsAnatom',
								listeners: {
									'change': function(combo, newValue, oldValue) {
										var index = combo.getStore().findBy(function(rec) {
											if ( rec.get('YesNo_id') == newValue ) {
												return true;
											}
											else {
												return false;
											}
										});
										var record = combo.getStore().getAt(index);

										combo.fireEvent('select', combo, record);
									}.createDelegate(this),
									'select': function(combo, record) {
										var base_form = this.FormPanel.getForm();

										this.setPatoMorphGistDirection();

										if ( typeof record != 'object' || parseInt(record.get('YesNo_Code')) == 0 ) {
											this.findById('EESecEF_AnatomPanel').hide();
											this.findById('EESecEF_AnatomDiagPanel').hide();

											base_form.findField('LpuSection_aid').setAllowBlank(true);
											base_form.findField('Org_aid').setAllowBlank(true);
											base_form.findField('MedStaffFact_aid').setAllowBlank(true);

											base_form.findField('AnatomWhere_id').clearValue();
											base_form.findField('Diag_aid').clearValue();
											base_form.findField('EvnDie_expDate').setRawValue('');
											base_form.findField('EvnDie_expTime').setRawValue('');
											base_form.findField('LpuSection_aid').clearValue();
											base_form.findField('MedStaffFact_aid').clearValue();
											base_form.findField('Org_aid').clearValue();

											base_form.findField('EvnDie_expDate').fireEvent('change', base_form.findField('EvnDie_expDate'), base_form.findField('EvnDie_expDate').getValue());

											return false;
										}

										this.findById('EESecEF_AnatomPanel').show();
										this.findById('EESecEF_AnatomDiagPanel').show();

										if ( this.findById('EESecEF_AnatomDiagPanel').isLoaded == false ) {
											this.findById('EESecEF_AnatomDiagPanel').isLoaded = true;

											this.findById('EESecEF_AnatomDiagGrid').getGrid().removeAll();

											if ( base_form.findField('EvnDie_id').getValue() ) {
												this.findById('EESecEF_AnatomDiagGrid').loadData({
													globalFilters: {
														'class': 'EvnDiagPSDie',
														EvnDiagPS_pid: base_form.findField('EvnDie_id').getValue()
													},
													noFocusOnLoad: true
												});
											}
											else {
												LoadEmptyRow(this.findById('EESecEF_AnatomDiagGrid').getGrid());
											}
										}

										base_form.findField('EvnDie_expDate').fireEvent('change', base_form.findField('EvnDie_expDate'), base_form.findField('EvnDie_expDate').getValue());
										base_form.findField('AnatomWhere_id').fireEvent('change', base_form.findField('AnatomWhere_id'), base_form.findField('AnatomWhere_id').getValue());
									}.createDelegate(this)
								},
								tabIndex: this.tabIndex + 25,
								typeCode: 'int',
								width: 70,
								xtype: 'swcommonsprcombo'
							}]
					},{
						border:false,
						layout:'form',
						style: '{margin: 0 0 0 30px;}',
						items: [{
							id: 'ESEW_addPatoMorphHistoDirectionButton',
							text: "Выписать направление",
							hidden: true,
							tooltip: lang['vyipisat_napravlenie_na_patomorfogistologicheskoe_issledovanie'],
							//tabIndex: TABINDEX_RLW + 20,
							handler: function() {
								_this.addPatoMorphHistoDirection();
							},
							xtype:'button'
						}]
					}]
				}, {
					autoHeight: true,
					id: 'EESecEF_AnatomPanel',
					style: 'padding: 0px;',
					title: lang['patologoanatomicheskaya_ekspertiza'],
					width: 750,
					xtype: 'fieldset',

					items: [{
						border: false,
						layout: 'column',

						items: [{
							border: false,
							layout: 'form',

							items: [{
								fieldLabel: lang['data_ekspertizyi'],
								format: 'd.m.Y',
								listeners: {
									'change': function(field, newValue, oldValue) {
										var base_form = this.FormPanel.getForm();

										var lpu_section_aid = base_form.findField('LpuSection_aid').getValue();
										var med_staff_fact_aid = base_form.findField('MedStaffFact_aid').getValue();

										base_form.findField('LpuSection_aid').clearValue();
										base_form.findField('MedStaffFact_aid').clearValue();

										if ( !newValue ) {
											setLpuSectionGlobalStoreFilter({
												// isStac: true
											});
											base_form.findField('LpuSection_aid').getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));

											setMedStaffFactGlobalStoreFilter();
											base_form.findField('MedStaffFact_aid').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));

											base_form.findField('AnatomWhere_id').setAllowBlank(true);
											base_form.findField('Diag_aid').setAllowBlank(true);
											base_form.findField('EvnDie_expTime').setAllowBlank(true);
										}
										else {
											setLpuSectionGlobalStoreFilter({
												// isStac: true,
												onDate: Ext.util.Format.date(newValue, 'd.m.Y')
											});
											base_form.findField('LpuSection_aid').getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));

											setMedStaffFactGlobalStoreFilter({
												onDate: Ext.util.Format.date(newValue, 'd.m.Y')
											});
											base_form.findField('MedStaffFact_aid').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));

											base_form.findField('AnatomWhere_id').setAllowBlank(false);
											base_form.findField('Diag_aid').setAllowBlank(false);
											base_form.findField('EvnDie_expTime').setAllowBlank(false);
										}

										if ( base_form.findField('LpuSection_aid').getStore().getById(lpu_section_aid) ) {
											base_form.findField('LpuSection_aid').setValue(lpu_section_aid);
										}

										if ( base_form.findField('MedStaffFact_aid').getStore().getById(med_staff_fact_aid) ) {
											base_form.findField('MedStaffFact_aid').setValue(med_staff_fact_aid);
										}
									}.createDelegate(this)
								},
								name: 'EvnDie_expDate',
								plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
								selectOnFocus: true,
								tabIndex: this.tabIndex + 26,
								width: 100,
								xtype: 'swdatefield'
							}]
						}, {
							border: false,
							labelWidth: 50,
							layout: 'form',
							items: [{
								fieldLabel: lang['vremya'],
								name: 'EvnDie_expTime',
								listeners: {
									'keydown': function (inp, e) {
										if ( e.getKey() == Ext.EventObject.F4 ) {
											e.stopEvent();
											inp.onTriggerClick();
										}
									}
								},
								onTriggerClick: function() {
									var base_form = this.FormPanel.getForm();
									var time_field = base_form.findField('EvnDie_expTime');

									if ( time_field.disabled ) {
										return false;
									}

									setCurrentDateTime({
										callback: function() {
											base_form.findField('EvnDie_expDate').fireEvent('change', base_form.findField('EvnDie_expDate'), base_form.findField('EvnDie_expDate').getValue());
										}.createDelegate(this),
										dateField: base_form.findField('EvnDie_expDate'),
										loadMask: true,
										setDate: true,
										setDateMaxValue: false,
										setDateMinValue: false,
										setTime: true,
										timeField: time_field,
										windowId: this.id
									});
								}.createDelegate(this),
								plugins: [ new Ext.ux.InputTextMask('99:99', true) ],
								tabIndex: this.tabIndex + 27,
								validateOnBlur: false,
								width: 60,
								xtype: 'swtimefield'
							}]
						}]
					}, {
						autoLoad: false,
						comboSubject: 'AnatomWhere',
						fieldLabel: lang['mesto_provedeniya'],
						hiddenName: 'AnatomWhere_id',
						lastQuery: '',
						listeners: {
							'change': function(combo, newValue, oldValue) {
								var index = combo.getStore().findBy(function(rec) {
									if ( rec.get('AnatomWhere_id') == newValue ) {
										return true;
									}
									else {
										return false;
									}
								});
								var record = combo.getStore().getAt(index);

								combo.fireEvent('select', combo, record);
							}.createDelegate(this),
							'select': function(combo, record) {
								var base_form = this.FormPanel.getForm();

								var lpu_section_combo = base_form.findField('LpuSection_aid');
								var med_staff_fact_combo = base_form.findField('MedStaffFact_aid');
								var org_combo = base_form.findField('Org_aid');

								lpu_section_combo.clearValue();
								med_staff_fact_combo.clearValue();
								org_combo.clearValue();

								if (this.setPatoMorphGistDirection()) {
									return false;
								}
								if ( !record ) {
									lpu_section_combo.disable();
									med_staff_fact_combo.disable();
									org_combo.disable();

									return false;
								}

								switch ( parseInt(record.get('AnatomWhere_Code')) ) {
									case 1:
										lpu_section_combo.enable();
										med_staff_fact_combo.enable();
										org_combo.disable();
									break;

									case 2:
									case 3:
										lpu_section_combo.disable();
										med_staff_fact_combo.disable();
										org_combo.enable();
									break;

									default:
										lpu_section_combo.disable();
										med_staff_fact_combo.disable();
										org_combo.disable();
									break;
								}
							}.createDelegate(this)
						},
						tabIndex: this.tabIndex + 28,
						typeCode: 'int',
						width: 300,
						xtype: 'swcommonsprcombo'
					}, {
						displayField: 'Org_Name',
						editable: false,
						enableKeyEvents: true,
						fieldLabel: lang['organizatsiya'],
						hiddenName: 'Org_aid',
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
							var base_form = this.FormPanel.getForm();
							var combo = base_form.findField('Org_aid');

							if ( combo.disabled ) {
								return false;
							}

							var anatom_where_combo = base_form.findField('AnatomWhere_id');
							var anatom_where_id = anatom_where_combo.getValue();
							var record = anatom_where_combo.getStore().getById(anatom_where_id);

							if ( !record ) {
								return false;
							}

							var anatom_where_code = record.get('AnatomWhere_Code');
							var org_type = '';

							switch ( parseInt(anatom_where_code) ) {
								case 2:
									org_type = 'lpu';
								break;

								case 3:
									org_type = 'anatom_old';
								break;

								default:
									return false;
								break;
							}

							getWnd('swOrgSearchWindow').show({
								object: org_type,
								onlyFromDictionary: true,
								onClose: function() {
									combo.focus(true, 200)
								},
								onSelect: function(org_data) {
									if ( org_data.Org_id > 0 ) {
										combo.getStore().loadData([{
											Org_id: org_data.Org_id,
											Lpu_id:org_data.Lpu_id,
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
								{ name: 'Org_id', type: 'int' },
								{ name: 'Lpu_id', type: 'int' },
								{ name: 'Org_Name', type: 'string' }
							],
							key: 'Org_id',
							sortInfo: {
								field: 'Org_Name'
							},
							url: C_ORG_LIST
						}),
						tabIndex: this.tabIndex + 30,
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
						hiddenName: 'LpuSection_aid',
						id: 'EESecEF_LpuSectionAnatomCombo',
						linkedElements: [
							'EESecEF_MedStaffFactAnatomCombo'
						],
						tabIndex: this.tabIndex + 29,
						width: 500,
						xtype: 'swlpusectionglobalcombo'
					}, {
						fieldLabel: lang['vrach'],
						hiddenName: 'MedStaffFact_aid',
						id: 'EESecEF_MedStaffFactAnatomCombo',
						listWidth: 650,
						parentElementId: 'EESecEF_LpuSectionAnatomCombo',
						tabIndex: this.tabIndex + 31,
						width: 500,
						xtype: 'swmedstafffactglobalcombo'
					}, {
                        checkAccessRights: true,
						fieldLabel: lang['osn_patologoanat-y_diagnoz'],
						hiddenName: 'Diag_aid',
						id: 'ESecEF_DiagAnatomCombo',
						tabIndex: this.tabIndex + 32,
						width: 500,
						xtype: 'swdiagcombo'
					}, {
						border: false,
						height: 150,
						id: 'EESecEF_AnatomDiagPanel',
						isLoaded: false,
						layout: 'border',
						// style: 'margin-left: 165px; margin-right: 0.5em; padding-bottom: 4px;',

						items: [ new sw.Promed.ViewFrame({
							actions: [
								{ name: 'action_add', handler: function() { this.openEvnDiagPSEditWindow('add', 'die'); }.createDelegate(this) },
								{ name: 'action_edit', handler: function() { this.openEvnDiagPSEditWindow('edit', 'die'); }.createDelegate(this) },
								{ name: 'action_view', handler: function() { this.openEvnDiagPSEditWindow('view', 'die'); }.createDelegate(this) },
								{ name: 'action_delete', handler: function() { this.deleteEvnDiagPS('EvnDiagPS'); }.createDelegate(this), tooltip: lang['udalit_diagnoz_iz_spiska'] },
								{ name: 'action_refresh', disabled: true, hidden: true },
								{ name: 'action_print', disabled: true, hidden: true }
							],
							autoLoadData: false,
							border: false,
							dataUrl: '/?c=EvnDiag&m=loadEvnDiagPSGrid',
							id: 'EESecEF_AnatomDiagGrid',
							region: 'center',
							stringfields: [
								{ name: 'EvnDiagPS_id', type: 'int', header: 'ID', key: true },
								{ name: 'EvnDiagPS_pid', type: 'int', hidden: true },
								{ name: 'Diag_id', type: 'int', hidden: true },
								{ name: 'DiagSetClass_id', type: 'int', hidden: true },
								{ name: 'DiagSetPhase_id', type: 'int', hidden: true },
								{ name: 'DiagSetType_id', type: 'int', hidden: true },
								{ name: 'EvnDiagPS_PhaseDescr', type: 'string', hidden: true },
								{ name: 'EvnDiagPS_setTime', type: 'string', hidden: true },
								{ name: 'Person_id', type: 'int', hidden: true },
								{ name: 'PersonEvn_id', type: 'int', hidden: true },
								{ name: 'Server_id', type: 'int', hidden: true },
								{ name: 'RecordStatus_Code', type: 'int', hidden: true },
								{ name: 'EvnDiagPS_setDate', type: 'date', format: 'd.m.Y', header: lang['data'], width: 90 },
								{ name: 'DiagSetClass_Name', type: 'string', header: lang['vid_diagnoza'], width: 200 },
								{ name: 'Diag_Code', type: 'string', header: lang['kod_diagnoza'], width: 100 },
								{ name: 'Diag_Name', type: 'string', header: lang['diagnoz'], id: 'autoexpand' }
							],
							style: 'margin-bottom: 0.5em;',
							title: lang['soputstvuyuschie_patologoanatomicheskie_diagnozyi'],
							toolbar: true
						})]
					}]
                }, {
                    border: false,
                    hidden: !(getRegionNick().inlist([ 'perm' ])), // Открыто для Перми
                    layout: 'form',
                    items: [{
                        fieldLabel: lang['fed_rezultat'],
                        hiddenName: 'LeaveType_fedid',
                        listWidth: 600,
						lastQuery:'',
						tabIndex:this.tabIndex + 33,
                        width: 500,
                        xtype: 'swleavetypefedcombo'
                    }, {
                        fieldLabel: lang['fed_ishod'],
                        hiddenName: 'ResultDeseaseType_fedid',
                        listWidth: 600,
                        tabIndex:this.tabIndex + 34,
						lastQuery:'',
                        width: 500,
                        xtype: 'swresultdeseasetypefedcombo'
                    }]
				}, {
					border: false,
					hidden: !(getRegionNick().inlist([ 'astra', 'krasnoyarsk', 'krym', 'perm', 'kareliya' ])), // Открыто для Астрахани, Крыма Перми
					layout: 'form',
					items: [{
						comboSubject:'CureResult',
						fieldLabel:lang['itog_lecheniya'],
						hiddenName:'CureResult_id',
						lastQuery: '',
						listeners: {
							'change': function (combo, newValue, oldValue) {
								var index = combo.getStore().findBy(function (rec) {
									return (rec.get(combo.valueField) == newValue);
								});

								combo.fireEvent('select', combo, combo.getStore().getAt(index));
							},
							'select': function (combo, record) {
								if ( getRegionNick() != 'perm' ) {
									return false;
								}

								var base_form = this.FormPanel.getForm();

								var EvnSection_IsTerm = base_form.findField('EvnSection_IsTerm').getValue() || 1;

								if ( typeof record == 'object' && record.get('CureResult_Code') == 1 ) {
									base_form.findField('EvnSection_IsTerm').setContainerVisible(true);
									base_form.findField('EvnSection_IsTerm').setAllowBlank(false);
									base_form.findField('EvnSection_IsTerm').setValue(EvnSection_IsTerm);
								}
								else {
									base_form.findField('EvnSection_IsTerm').setContainerVisible(false);
									base_form.findField('EvnSection_IsTerm').setAllowBlank(true);
									base_form.findField('EvnSection_IsTerm').clearValue();
								}
							}.createDelegate(this)
						},
						tabIndex:this.tabIndex + 35,
						width: 350,
						xtype:'swcommonsprcombo'
					}]
				}, {
					border: false,
					hidden: !(getRegionNick().inlist([ 'perm' ])), // Открыто для Перми
					layout: 'form',
					items: [{
						fieldLabel: 'Случай прерван',
						hiddenName: 'EvnSection_IsTerm',
						lastQuery: '',
						tabIndex:this.tabIndex + 36,
						width: 70,
						xtype: 'swyesnocombo'
					}]
				}, {
					xtype: 'swdiagsetphasecombo',
					hiddenName: 'DiagSetPhase_aid',
					fieldLabel: langs('Состояние пациента при выписке'),
					width: 300,
					tabIndex: this.tabIndex + 37,
					editable: false
				}, {
					fieldLabel: 'Впервые выявленная инвалидность',
					hiddenName: 'PrivilegeType_id',
					xtype: 'swprivilegetypecombo',
					width: 300
				}, {
					comboSubject: 'PayTypeERSB',
					fieldLabel: 'Тип оплаты',
					hiddenName: 'PayTypeERSB_id',
					lastQuery: '',
					tabIndex: this.tabIndex + 37,
					typeCode: 'int',
					width: 300,
					xtype: 'swcommonsprcombo'
				}]
			})]
		});

		Ext.apply(this, {
			keys: [
				this.keyHandlerAlt
			],
			buttons: [{
				handler: function() {
					this.doSave();
				}.createDelegate(this),
				iconCls: 'save16',
				onShiftTabAction: function() {
					this.buttons[this.buttons.length - 1].focus();
				}.createDelegate(this),
				onTabAction: function() {
					this.buttons[this.buttons.length - 1].focus();
				}.createDelegate(this),
				tabIndex: this.tabIndex + 37,
				text: BTN_FRMSAVE
			}, {
				text: '-'
			},
			HelpButton(this, -1),
			{
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'cancel16',
				onShiftTabAction: function () {
					if ( this.action != 'view' ) {
						this.buttons[0].focus();
					}
				}.createDelegate(this),
				onTabAction: function() {
					if ( !this.findById('EESecEF_EvnSectionPanel').collapsed && this.action != 'view' ) {
						if ( !this.FormPanel.getForm().findField('EvnSection_setDate').disabled ) {
							this.FormPanel.getForm().findField('EvnSection_setDate').focus(true);
						}
						else {
							this.FormPanel.getForm().findField('EvnSection_disDate').focus(true);
						}
					}
					else if (this.action != 'view') {
						this.buttons[0].focus();
					}
				}.createDelegate(this),
				tabIndex: this.tabIndex + 38,
				text: BTN_FRMCANCEL
			}],
			items: [
				 this.PersonInfo
				,this.FormPanel
			]
		});

		sw.Promed.swEmkEvnSectionEditWindow.superclass.initComponent.apply(this, arguments);
		
        this.FormPanel.on('render', function(formPanel){
			var base_form = formPanel.getForm();

			base_form.findField('DiagSetPhase_id').setAllowBlank(false);

            formPanel.getForm().findField('ResultDesease_id').on('change', function (combo, newValue) {
                var index = combo.getStore().findBy(function (rec) {
                    return (rec.get('ResultDesease_id') == newValue);
                });
                combo.fireEvent('select', combo, combo.getStore().getAt(index));
            });
			base_form.findField('LeaveType_fedid').on('change', function (combo, newValue) {
				sw.Promed.EvnSection.filterFedResultDeseaseType({
					LpuUnitType_SysNick: base_form.findField('LpuUnitType_SysNick').getValue(),
					fieldFedLeaveType: base_form.findField('LeaveType_fedid'),
					fieldFedResultDeseaseType: base_form.findField('ResultDeseaseType_fedid')
				})
			});
			base_form.findField('ResultDeseaseType_fedid').on('change', function (combo, newValue) {
				sw.Promed.EvnSection.filterFedLeaveType({
					LpuUnitType_SysNick: base_form.findField('LpuUnitType_SysNick').getValue(),
					fieldFedLeaveType: base_form.findField('LeaveType_fedid'),
					fieldLeaveType: base_form.findField('LeaveType_id'),
					fieldFedResultDeseaseType: base_form.findField('ResultDeseaseType_fedid')
				});
			});
            formPanel.getForm().findField('ResultDesease_id').on('select', function (combo, record) {
                var base_form = formPanel.getForm();
                sw.Promed.EvnSection.calcFedResultDeseaseType({
                    date: base_form.findField('EvnSection_disDate').getValue(),
                    LpuUnitType_SysNick: base_form.findField('LpuUnitType_SysNick').getValue(),
                    LeaveType_SysNick: base_form.findField('LeaveType_id').getFieldValue('LeaveType_SysNick'),
                    ResultDesease_Code: (record && record.get('ResultDesease_Code')) || null,
                    fieldFedResultDeseaseType: base_form.findField('ResultDeseaseType_fedid')
                });
				sw.Promed.EvnSection.filterFedResultDeseaseType({
					LpuUnitType_SysNick: base_form.findField('LpuUnitType_SysNick').getValue(),
					fieldFedLeaveType: base_form.findField('LeaveType_fedid'),
					fieldFedResultDeseaseType: base_form.findField('ResultDeseaseType_fedid')
				})
				sw.Promed.EvnSection.filterFedLeaveType({
					LpuUnitType_SysNick: base_form.findField('LpuUnitType_SysNick').getValue(),
					fieldFedLeaveType: base_form.findField('LeaveType_fedid'),
					fieldLeaveType: base_form.findField('LeaveType_id'),
					fieldFedResultDeseaseType: base_form.findField('ResultDeseaseType_fedid')
				});
            });
            formPanel.getForm().findField('LeaveCause_id').on('change', function (combo, newValue) {
                var index = combo.getStore().findBy(function (rec) {
                    return (rec.get('LeaveCause_id') == newValue);
                });
                combo.fireEvent('select', combo, combo.getStore().getAt(index));
            });
            formPanel.getForm().findField('LeaveCause_id').on('select', function (combo, record) {
                var base_form = formPanel.getForm();
                sw.Promed.EvnSection.calcFedLeaveType({
                    date: base_form.findField('EvnSection_disDate').getValue(),
                    LpuUnitType_SysNick: base_form.findField('LpuUnitType_SysNick').getValue(),
                    LeaveType_SysNick: base_form.findField('LeaveType_id').getFieldValue('LeaveType_SysNick'),
                    LeaveCause_Code: (record && record.get('LeaveCause_Code')) || null,
                    fieldFedLeaveType: base_form.findField('LeaveType_fedid'),
					fieldFedResultDeseaseType: base_form.findField('ResultDeseaseType_fedid')
                });
				sw.Promed.EvnSection.filterFedResultDeseaseType({
					LpuUnitType_SysNick: base_form.findField('LpuUnitType_SysNick').getValue(),
					fieldFedLeaveType: base_form.findField('LeaveType_fedid'),
					fieldFedResultDeseaseType: base_form.findField('ResultDeseaseType_fedid')
				})
				sw.Promed.EvnSection.filterFedLeaveType({
					LpuUnitType_SysNick: base_form.findField('LpuUnitType_SysNick').getValue(),
					fieldFedLeaveType: base_form.findField('LeaveType_fedid'),
					fieldLeaveType: base_form.findField('LeaveType_id'),
					fieldFedResultDeseaseType: base_form.findField('ResultDeseaseType_fedid')
				});
            });
        });
	},
	layout: 'border',
	listeners: {
		'hide': function(win) {
			win.onHide();
		},
		'maximize': function(win) {
			win.findById('EESecEF_EvnSectionPanel').doLayout();
			win.findById('EESecEF_EvnLeavePanel').doLayout();
		},
		'restore': function(win) {
			win.fireEvent('maximize', win);
		}
	},
	loadMes2Combo:function(mes2_id, selectIfOne) {
		if (getGlobalOptions().region && getGlobalOptions().region.nick == 'ufa') {
			var base_form = this.FormPanel.getForm();
			var diag_id = base_form.findField('Diag_id').getValue();
			
			if ( !diag_id || Ext.isEmpty(diag_id) ) {
				return false;
			}
		
			base_form.findField('Mes2_id').clearValue();
			base_form.findField('Mes2_id').getStore().removeAll();
			
			base_form.findField('Mes2_id').getStore().load({
				callback:function () {
					var record = null;
					
					// Записей нет
					if (base_form.findField('Mes2_id').getStore().getCount() == 0) {
					}
					else {
						// Если запись одна
						if (base_form.findField('Mes2_id').getStore().getCount() == 1 && selectIfOne) {
							record = base_form.findField('Mes2_id').getStore().getAt(0);
						}
						// Запись, соответствующая старому значению
						else {
							record = base_form.findField('Mes2_id').getStore().getById(mes2_id);
						}
					}

					if (record && !base_form.findField('Mes2_id').disabled) {
						base_form.findField('Mes2_id').setValue(record.get('Mes2_id'));
						base_form.findField('Mes2_id').fireEvent('change', base_form.findField('Mes2_id'), record.get('Mes2_id'));
					}
				}.createDelegate(this),
				params:{
					Diag_id:diag_id
				}
			});
		}
	},
	leaveTypeFedFilter: function() {
		var base_form = this.FormPanel.getForm();

		if ( getRegionNick().inlist([ 'buryatiya', 'ekb', 'penza', 'pskov' ]) ) {
			var LeaveTypeFed_id = base_form.findField('LeaveTypeFed_id').getValue();

			var fedIdList = new Array();

			// Получаем список доступных исходов из федерального справочника
			base_form.findField('LeaveType_id').getStore().each(function(rec) {
				if ( !Ext.isEmpty(rec.get('LeaveType_fedid')) && !rec.get('LeaveType_fedid').toString().inlist(fedIdList) ) {
					fedIdList.push(rec.get('LeaveType_fedid').toString());
				}
			});

			base_form.findField('LeaveTypeFed_id').clearFilter();
			base_form.findField('LeaveTypeFed_id').lastQuery = '';

			var LpuUnitType_SysNick = base_form.findField('LpuUnitType_SysNick').getValue();

			var EvnSection_setDate = base_form.findField('EvnSection_setDate').getValue();
			var EvnSection_disDate = base_form.findField('EvnSection_disDate').getValue();

			if ( !Ext.isEmpty(LpuUnitType_SysNick) ) {
				if (LpuUnitType_SysNick == 'stac') {
					// круглосуточный
					base_form.findField('LeaveTypeFed_id').getStore().filterBy(function (rec) {
						if (!Ext.isEmpty(rec.get('LeaveType_begDate')) && !Ext.isEmpty(EvnSection_disDate) && rec.get('LeaveType_begDate') > EvnSection_disDate) {
							return false;
						}
						if (!Ext.isEmpty(rec.get('LeaveType_endDate')) && rec.get('LeaveType_endDate') < EvnSection_setDate) {
							return false;
						}

						return (
							rec.get('LeaveType_id').toString().inlist(fedIdList)
							&& rec.get('LeaveType_Code') > 100
							&& rec.get('LeaveType_Code') < 200
						);
					});
				} else {
					// https://redmine.swan.perm.ru/issues/18318
					base_form.findField('LeaveTypeFed_id').getStore().filterBy(function (rec) {
						if (!Ext.isEmpty(rec.get('LeaveType_begDate')) && !Ext.isEmpty(EvnSection_disDate) && rec.get('LeaveType_begDate') > EvnSection_disDate) {
							return false;
						}
						if (!Ext.isEmpty(rec.get('LeaveType_endDate')) && rec.get('LeaveType_endDate') < EvnSection_setDate) {
							return false;
						}

						return (
							rec.get('LeaveType_id').toString().inlist(fedIdList)
							&& rec.get('LeaveType_Code') > 200
							&& rec.get('LeaveType_Code') < 300
							&& !(getRegionNick() != 'adygeya' && LpuUnitType_SysNick.inlist([ 'dstac', 'hstac' ]) && rec.get('LeaveType_Code').toString().inlist([ '207', '208' ]))
						);
					});
				}
			}
			else {
				base_form.findField('LeaveTypeFed_id').getStore().filterBy(function (rec) {
					if (!Ext.isEmpty(rec.get('LeaveType_begDate')) && !Ext.isEmpty(EvnSection_disDate) && rec.get('LeaveType_begDate') > EvnSection_disDate) {
						return false;
					}
					if (!Ext.isEmpty(rec.get('LeaveType_endDate')) && rec.get('LeaveType_endDate') < EvnSection_setDate) {
						return false;
					}

					return (rec.get('LeaveType_id').toString().inlist(fedIdList));
				});
			}

			if ( !Ext.isEmpty(LeaveTypeFed_id) ) {
				var index = base_form.findField('LeaveTypeFed_id').getStore().findBy(function(rec) {
					return (rec.get('LeaveType_id') == LeaveTypeFed_id);
				});

				if ( index == -1 ) {
					base_form.findField('LeaveTypeFed_id').clearValue();
					base_form.findField('LeaveTypeFed_id').fireEvent('change', base_form.findField('LeaveTypeFed_id'));
				}
			}
		}
	},
	leaveTypeFilter: function() {
		var base_form = this.FormPanel.getForm();

		if ( getRegionNick().inlist([ 'kareliya', 'krasnoyarsk', 'krym', 'msk', 'yaroslavl', 'adygeya']) ) {
			var LeaveType_id = base_form.findField('LeaveType_id').getValue();

			base_form.findField('LeaveType_id').clearFilter();
			base_form.findField('LeaveType_id').lastQuery = '';

			var LpuUnitType_SysNick = base_form.findField('LpuUnitType_SysNick').getValue();

			var EvnSection_setDate = base_form.findField('EvnSection_setDate').getValue();
			var EvnSection_disDate = base_form.findField('EvnSection_disDate').getValue();

			if ( !Ext.isEmpty(LpuUnitType_SysNick) ) {
				if (LpuUnitType_SysNick == 'stac') {
					// круглосуточный
					base_form.findField('LeaveType_id').getStore().filterBy(function (rec) {
						if (!Ext.isEmpty(rec.get('LeaveType_begDate')) && !Ext.isEmpty(EvnSection_disDate) && rec.get('LeaveType_begDate') > EvnSection_disDate) {
							return false;
						}
						if (!Ext.isEmpty(rec.get('LeaveType_endDate')) && rec.get('LeaveType_endDate') < EvnSection_setDate) {
							return false;
						}
						return (
							rec.get('LeaveType_Code') > 100
							&& rec.get('LeaveType_Code') < 200
							&& !(rec.get('LeaveType_Code').toString().inlist([ '111', '112', '113', '114', '115' ]))
						);
					});
				} else {
					base_form.findField('LeaveType_id').getStore().filterBy(function (rec) {
						if (!Ext.isEmpty(rec.get('LeaveType_begDate')) && !Ext.isEmpty(EvnSection_disDate) && rec.get('LeaveType_begDate') > EvnSection_disDate) {
							return false;
						}
						if (!Ext.isEmpty(rec.get('LeaveType_endDate')) && rec.get('LeaveType_endDate') < EvnSection_setDate) {
							return false;
						}
						return (
							rec.get('LeaveType_Code') > 200
							&& rec.get('LeaveType_Code') < 300
							&& !(!getRegionNick().inlist(['kareliya', 'adygeya']) && LpuUnitType_SysNick.inlist([ 'dstac', 'hstac' ]) && rec.get('LeaveType_Code').toString().inlist([ '207', '208' ]))
							&& !(rec.get('LeaveType_Code').toString().inlist([ '210', '211', '212', '213', '215' ]))
						);
					});
				}
			}
			else {
				base_form.findField('LeaveType_id').getStore().filterBy(function (rec) {
					if (!Ext.isEmpty(rec.get('LeaveType_begDate')) && !Ext.isEmpty(EvnSection_disDate) && rec.get('LeaveType_begDate') > EvnSection_disDate) {
						return false;
					}
					if (!Ext.isEmpty(rec.get('LeaveType_endDate')) && rec.get('LeaveType_endDate') < EvnSection_setDate) {
						return false;
					}
					return (
						!(rec.get('LeaveType_Code').toString().inlist([ '111', '112', '113', '114', '115', '210', '211', '212', '213', '215' ]))
					);
				});
			}

			if ( !Ext.isEmpty(LeaveType_id) ) {
				var index = base_form.findField('LeaveType_id').getStore().findBy(function(rec) {
					return (rec.get('LeaveType_id') == LeaveType_id);
				});

				if ( index == -1 ) {
					base_form.findField('LeaveType_id').clearValue();
					base_form.findField('LeaveType_id').fireEvent('change', base_form.findField('LeaveType_id'));
				}
			}
		}
	},
	leaveCauseFilter: function() {
		var base_form = this.FormPanel.getForm();

		if ( getRegionNick().inlist([ 'buryatiya', 'kareliya', 'krasnoyarsk', 'penza', 'pskov', 'yaroslavl', 'adygeya' ]) ) {
			var oldValue = base_form.findField('LeaveCause_id').getValue();

			base_form.findField('LeaveCause_id').clearFilter();
			base_form.findField('LeaveCause_id').lastQuery = '';

			switch ( base_form.findField('LpuUnitType_SysNick').getValue() ) {
				case 'stac': // Круглосуточный стационар
					base_form.findField('LeaveCause_id').getStore().filterBy(function (rec) {
						return (!rec.get('LeaveCause_Code').inlist([ 210, 211, 212 ]));
					});
				break;

				default:
					base_form.findField('LeaveCause_id').getStore().filterBy(function (rec) {
						return (rec.get('LeaveCause_Code').inlist([ 1, 6, 7, 27, 28, 29, 210, 211, 212 ]));
					});
				break;
			}
			
			var index = base_form.findField('LeaveCause_id').getStore().findBy(function (rec) {
				return (rec.get('LeaveCause_id') == oldValue);
			});
			
			if ( index == -1 ) {
				base_form.findField('LeaveCause_id').clearValue();
			}
			
			if ( base_form.findField('LeaveCause_id').getStore().getCount() == 1 ) {
				base_form.findField('LeaveCause_id').setValue(base_form.findField('LeaveCause_id').getStore().getAt(0).get('LeaveCause_id'));
			}
		}
	},
	resultDeseaseFilter: function() {
		var base_form = this.FormPanel.getForm();
		if ( getRegionNick().inlist([ 'astra', 'buryatiya', 'ekb', 'kareliya', 'krasnoyarsk', 'krym', 'penza', 'pskov' , 'msk', 'yaroslavl', 'adygeya']) ) {
			var oldValue = base_form.findField('ResultDesease_id').getValue();
			base_form.findField('ResultDesease_id').clearFilter();
			base_form.findField('ResultDesease_id').lastQuery = '';
			if (base_form.findField('LpuUnitType_Code').getValue() == 2) {
				// круглосуточный
				base_form.findField('ResultDesease_id').getStore().filterBy(function (rec) {
					return (rec.get('ResultDesease_Code') > 100 && rec.get('ResultDesease_Code') < 200 );
				});
			} else {
				base_form.findField('ResultDesease_id').getStore().filterBy(function (rec) {
					return (rec.get('ResultDesease_Code') > 200 && rec.get('ResultDesease_Code') < 300 );
				});
			}
			
			var index = base_form.findField('ResultDesease_id').getStore().findBy(function (rec) {
				return (rec.get('ResultDesease_id') == oldValue);
			});
			
			if (index == -1) {
				base_form.findField('ResultDesease_id').clearValue();
			}
			else {
				base_form.findField('ResultDesease_id').setValue(oldValue);
			}
		}
	},
	checkLpuUnitType:function() {
		var base_form = this.FormPanel.getForm();
		var Person_Age = swGetPersonAge(this.findById('EESecEF_PersonInformationFrame').getFieldValue('Person_Birthday'), base_form.findField('EvnSection_setDate').getValue());

		if (base_form.findField('LpuUnitType_Code').getValue() == 2 && Person_Age != -1) {
			base_form.findField('EvnSection_IsAdultEscort').showContainer();
			if ( Ext.isEmpty(base_form.findField('EvnSection_IsAdultEscort').getValue()) ) {

				if ( Person_Age < 4 ) {
					base_form.findField('EvnSection_IsAdultEscort').setValue(2);
				}
				else {
					base_form.findField('EvnSection_IsAdultEscort').setValue(1);
				}
			}
		} else {
			base_form.findField('EvnSection_IsAdultEscort').hideContainer();
			base_form.findField('EvnSection_IsAdultEscort').clearValue();
		}

		base_form.findField('EvnSection_IsAdultEscort').fireEvent('change', base_form.findField('EvnSection_IsAdultEscort'), base_form.findField('EvnSection_IsAdultEscort').getValue());

		this.leaveTypeFedFilter();
		this.leaveTypeFilter();
		this.leaveCauseFilter();
		this.resultDeseaseFilter();

        if ( false && this.action == 'add' ) {
            sw.Promed.EvnSection.calcFedLeaveType({
                date: base_form.findField('EvnSection_disDate').getValue(),
                LpuUnitType_SysNick: base_form.findField('LpuUnitType_SysNick').getValue(),
                LeaveType_SysNick: base_form.findField('LeaveType_id').getFieldValue('LeaveType_SysNick'),
                LeaveCause_Code: base_form.findField('LeaveCause_id').getFieldValue('LeaveCause_Code'),
                fieldFedLeaveType: base_form.findField('LeaveType_fedid'),
				fieldFedResultDeseaseType: base_form.findField('ResultDeseaseType_fedid')
            });
            sw.Promed.EvnSection.calcFedResultDeseaseType({
                date: base_form.findField('EvnSection_disDate').getValue(),
                LpuUnitType_SysNick: base_form.findField('LpuUnitType_SysNick').getValue(),
                LeaveType_SysNick: base_form.findField('LeaveType_id').getFieldValue('LeaveType_SysNick'),
                ResultDesease_Code: base_form.findField('ResultDesease_id').getFieldValue('ResultDesease_Code'),
                fieldFedResultDeseaseType: base_form.findField('ResultDeseaseType_fedid')
            });
			sw.Promed.EvnSection.filterFedResultDeseaseType({
				LpuUnitType_SysNick: base_form.findField('LpuUnitType_SysNick').getValue(),
				fieldFedLeaveType: base_form.findField('LeaveType_fedid'),
				fieldFedResultDeseaseType: base_form.findField('ResultDeseaseType_fedid')
			})
			sw.Promed.EvnSection.filterFedLeaveType({
				LpuUnitType_SysNick: base_form.findField('LpuUnitType_SysNick').getValue(),
				fieldFedLeaveType: base_form.findField('LeaveType_fedid'),
				fieldLeaveType: base_form.findField('LeaveType_id'),
				fieldFedResultDeseaseType: base_form.findField('ResultDeseaseType_fedid')
			});
        }
	},
	setDiagFilterForKSGEkb: function() {
		var base_form = this.FormPanel.getForm();
		if (getRegionNick() != 'ekb') {
			return true;
		}

		base_form.findField('Mes_sid').getStore().baseParams.Diag_id = base_form.findField('Diag_id').getValue();
		base_form.findField('Mes_sid').clearValue();
		base_form.findField('Mes_sid').lastQuery = 'This query sample that is not will never appear';
		base_form.findField('Mes_sid').getStore().removeAll();
	},
	onChangeDates: function() {
		var base_form = this.FormPanel.getForm();
		if (getRegionNick() != 'ekb') {
			return true;
		}

		base_form.findField('Mes_sid').getStore().baseParams.onDate = null;

		var date = null;

		if (!Ext.isEmpty(base_form.findField('EvnSection_setDate').getValue())) {
			date = base_form.findField('EvnSection_setDate').getValue();
			base_form.findField('Mes_sid').getStore().baseParams.onDate = Ext.util.Format.date(base_form.findField('EvnSection_setDate').getValue(), 'd.m.Y');
			base_form.findField('Mes_sid').getStore().baseParams.EvnSection_setDate = Ext.util.Format.date(base_form.findField('EvnSection_setDate').getValue(), 'd.m.Y');
		}
		if (!Ext.isEmpty(base_form.findField('EvnSection_disDate').getValue())) {
			date = base_form.findField('EvnSection_disDate').getValue();
			base_form.findField('Mes_sid').getStore().baseParams.onDate = Ext.util.Format.date(base_form.findField('EvnSection_disDate').getValue(), 'd.m.Y');
		}

		// log(date, base_form.findField('Mes_sid').getFieldValue('UslugaComplex_begDT'), base_form.findField('Mes_sid').getFieldValue('UslugaComplex_endDT'));
		if (!Ext.isEmpty(date) && (base_form.findField('Mes_sid').getFieldValue('UslugaComplex_begDT') > date || (!Ext.isEmpty(base_form.findField('Mes_sid').getFieldValue('UslugaComplex_endDT')) && base_form.findField('Mes_sid').getFieldValue('UslugaComplex_endDT') < date))) {
			base_form.findField('Mes_sid').clearValue();
			base_form.findField('Mes_sid').lastQuery = 'This query sample that is not will never appear';
			base_form.findField('Mes_sid').getStore().removeAll();
		}
	},
	loadMesCombo: function() {
		var win = this;
		var base_form = this.FormPanel.getForm();
		// текущий мэс
		win.mes_id = base_form.findField('Mes_id').getValue() || win.mes_id;
		
		var diag_id = base_form.findField('Diag_id').getValue();
		var evn_section_dis_date = base_form.findField('EvnSection_disDate').getValue();
		var evn_section_set_date = base_form.findField('EvnSection_setDate').getValue();
		var lpu_section_id = base_form.findField('LpuSection_id').getValue();
		var person_id = base_form.findField('Person_id').getValue();
		var EvnSection_id = base_form.findField('EvnSection_id').getValue();
		
		var allowBlankMes = (getGlobalOptions().region && getGlobalOptions().region.nick.inlist(['ufa','pskov','kareliya','buryatiya']));
		
		base_form.findField('Mes_id').clearValue();
		base_form.findField('Mes_id').disable();
		base_form.findField('Mes_id').getStore().removeAll();

		if ( !diag_id || !evn_section_set_date || !lpu_section_id || !person_id ) {
			return false;
		}

		if ( this.action != 'view' ) {
			base_form.findField('Mes_id').enable();
		}

		base_form.findField('Mes_id').getStore().load({
			callback: function() {
				var record = null;

				// Записей нет
				if ( allowBlankMes || base_form.findField('Mes_id').getStore().getCount() == 0 ) {
					base_form.findField('Mes_id').setAllowBlank(true);
				}
				else {
					base_form.findField('Mes_id').setAllowBlank(false);
				}

                if ( base_form.findField('Mes_id').getStore().getCount() > 0 ) {
                    // Если запись одна
                    if (base_form.findField('Mes_id').getStore().getCount() == 1 && win.mes_id != null) {
                        record = base_form.findField('Mes_id').getStore().getAt(0);
                    }
                    // Запись, соответствующая старому значению
                    else {
                        record = base_form.findField('Mes_id').getStore().getById(win.mes_id);
                    }
                }

				// для Перми: если запись одна и выбрана не по новому условию, то нужно сделать поле необязательным.
				if (getGlobalOptions().region && getGlobalOptions().region.nick.inlist(['perm']) && base_form.findField('Mes_id').getStore().getCount() == 1) {
					var onerecord = base_form.findField('Mes_id').getStore().getAt(0);
					if ( onerecord.get('MesNewUslovie') == 0 ) {
						base_form.findField('Mes_id').setAllowBlank(true);
					}
				}

                //log(['loadMesCombo', win.mes_id, record, base_form.findField('Mes_id').getStore()]);

				if ( record ) {
					base_form.findField('Mes_id').setValue(record.get('Mes_id'));
					base_form.findField('Mes_id').fireEvent('change', base_form.findField('Mes_id'), record.get('Mes_id'));
				}
			}.createDelegate(this),
			params: {
				 Diag_id: diag_id
				,EvnSection_disDate: Ext.util.Format.date(evn_section_dis_date, 'd.m.Y')
				,EvnSection_setDate: Ext.util.Format.date(evn_section_set_date, 'd.m.Y')
				,LpuSection_id: lpu_section_id
				,Person_id: person_id
				,EvnSection_id: EvnSection_id
			}
		});
	},
	maximizable: true,
	minHeight: 550,
	minWidth: 800,
	modal: true,
	onHide: Ext.emptyFn,
	openEvnDiagPSEditWindow: function(action, type) {
		if ( typeof action != 'string' || !action.toString().inlist([ 'add', 'edit', 'view' ]) ) {
			return false;
		}
		else if ( typeof type != 'string' || !type.toString().inlist([ 'die' ]) ) {
			return false;
		}

		if ( getWnd('swEvnDiagPSEditWindow').isVisible() ) {
			sw.swMsg.alert(lang['soobschenie'], lang['okno_redaktirovaniya_diagnoza_uje_otkryito']);
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

		var base_form = this.FormPanel.getForm();
		var grid = this.findById('EESecEF_AnatomDiagGrid').getGrid();
		var formMode = 'local';
		var formParams = new Object();
		var params = new Object();

		if ( action == 'add' ) {
			formParams.Person_id = base_form.findField('Person_id').getValue();
			formParams.PersonEvn_id = base_form.findField('PersonEvn_id').getValue();
			formParams.Server_id = base_form.findField('Server_id').getValue();
		}

		if ( action == 'add' ) {
			formParams.EvnDiagPS_pid = base_form.findField('EvnDie_id').getValue();

			if ( base_form.findField('EvnDie_expDate').getValue() ) {
				formParams.EvnDiagPS_setDate = base_form.findField('EvnDie_expDate').getValue();
			}

			if ( base_form.findField('EvnDie_expTime').getValue() ) {
				formParams.EvnDiagPS_setTime = base_form.findField('EvnDie_expTime').getValue();
			}
		}
		else {
			var selected_record = grid.getSelectionModel().getSelected();

			if ( !selected_record || !selected_record.get('EvnDiagPS_id') ) {
				return false;
			}

			formParams = selected_record.data;
		}

		params.action = action;
		params.callback = function(data) {
			if ( typeof data != 'object' || typeof data.evnDiagPSData != 'object' ) {
				return false;
			}

			var record = grid.getStore().getById(data.evnDiagPSData[0].EvnDiagPS_id);

			data.evnDiagPSData[0].RecordStatus_Code = 0;

			if ( record ) {
				if ( record.get('RecordStatus_Code') == 1 ) {
					data.evnDiagPSData[0].RecordStatus_Code = 2;
				}

				var grid_fields = new Array();

				grid.getStore().fields.eachKey(function(key, item) {
					grid_fields.push(key);
				});

				for ( i = 0; i < grid_fields.length; i++ ) {
					record.set(grid_fields[i], data.evnDiagPSData[0][grid_fields[i]]);
				}

				record.commit();
			}
			else {
				if ( grid.getStore().getCount() == 1 && !grid.getStore().getAt(0).get('EvnDiagPS_id') ) {
					grid.getStore().removeAll();
				}

				data.evnDiagPSData[0].EvnDiagPS_id = -swGenTempId(grid.getStore());

				grid.getStore().loadData(data.evnDiagPSData, true);
			}
		}.createDelegate(this);
		params.formMode = formMode;
		params.formParams = formParams;
		params.onHide = function() {
			if ( typeof selected_record == 'object' ) {
				grid.getView().focusRow(grid.getStore().indexOf(selected_record));
			}
			else if ( grid.getStore().getCount() > 0 ) {
				grid.getView().focusRow(0);
			}
		}.createDelegate(this);
		params.Person_Birthday = this.PersonInfo.getFieldValue('Person_Birthday');
		params.Person_Firname = this.PersonInfo.getFieldValue('Person_Firname');
		params.Person_id = base_form.findField('Person_id').getValue();
		params.Person_Secname = this.PersonInfo.getFieldValue('Person_Secname');
		params.Person_Surname = this.PersonInfo.getFieldValue('Person_Surname');
		params.type = type;

		getWnd('swEvnDiagPSEditWindow').show(params);
	},
	plain: true,
	resizable: true,
	loadFormFieldsStore: function(elements, options) {
		var base_form = this.FormPanel.getForm();

		// функция загрузки справочников для нужных элементов.
		if ( elements.length < 1 ) {
			this.show(options); 
		}
		else {
			var params = new Object();
			var sprName = elements.shift();

			base_form.findField(sprName).getStore().removeAll();

			switch ( sprName ) {
				case 'LpuUnitType_oid':
					params.where = 'where LpuUnitType_Code in (2, 3, 4, 5)';
				break;
			}

			base_form.findField(sprName).getStore().load({
				callback: function() {
					this.loadFormFieldsStore(elements, options);
				}.createDelegate(this),
				params: params
			});
		}
	},
	getLoadMask: function(txt) {
		if ( Ext.isEmpty(txt) ) {
			txt = lang['podojdite'];
		}

		if ( !this.loadMask ) {
			this.loadMask = new Ext.LoadMask(this.getEl(), { msg: txt });
		}

		return this.loadMask;
	},
	recountKoikoDni: function () {
		var base_form = this.FormPanel.getForm();

		// Если по стат. суткам, то время тоже учитывается
		// Если по календарным суткам, то время не учитывается
		var
			evn_section_dis_date = base_form.findField('EvnSection_disDate').getValue(),
			evn_section_set_date = base_form.findField('EvnSection_setDate').getValue(),
			koiko_dni = 0,
			EvnSection_Absence = base_form.findField('EvnSection_Absence').getValue(),
			LpuUnitType_Code = base_form.findField('LpuUnitType_Code').getValue(),
			stat_sutki = false;

		if (typeof evn_section_dis_date == 'object' && typeof evn_section_set_date == 'object') {
			koiko_dni = 0;

			if (stat_sutki == true) {
				if (evn_section_set_date.getDay() != evn_section_set_date.add(Date.HOUR, -9).getDay()) {
					koiko_dni = koiko_dni + 1;
				}

				evn_section_dis_date = evn_section_dis_date.add(Date.HOUR, -9);
				evn_section_set_date = evn_section_set_date.add(Date.HOUR, -9);
			}

			koiko_dni = koiko_dni + Math.round((evn_section_dis_date.getTime() - evn_section_set_date.getTime()) / 864e5) + 1;

			if (LpuUnitType_Code && Number(LpuUnitType_Code) == 2 && koiko_dni > 1) {
				koiko_dni = koiko_dni - 1;
			}
		}

		if ( !Ext.isEmpty(EvnSection_Absence) ) {
			koiko_dni = koiko_dni - EvnSection_Absence;
		}

		base_form.findField('EvnSection_KoikoDni').setValue(koiko_dni);
	},
	setDiagFilterByDate: function() {
		var base_form = this.FormPanel.getForm();
		if (!Ext.isEmpty(base_form.findField('EvnSection_disDate').getValue())) {
			base_form.findField('Diag_id').setFilterByDate(base_form.findField('EvnSection_disDate').getValue());
		} else {
			base_form.findField('Diag_id').setFilterByDate(base_form.findField('EvnSection_setDate').getValue());
		}
	},
	setIsMedReason: function() {
		var base_form = this.FormPanel.getForm();

		var
			EvnSection_IsAdultEscort = base_form.findField('EvnSection_IsAdultEscort').getValue(),
			Person_Age = swGetPersonAge(this.findById('EESecEF_PersonInformationFrame').getFieldValue('Person_Birthday'), base_form.findField('EvnSection_setDate').getValue());

		if ( getRegionNick().inlist([ 'astra' ]) && EvnSection_IsAdultEscort == 2 && Person_Age >= 4 ) {
			base_form.findField('EvnSection_IsMedReason').setAllowBlank(false);
			base_form.findField('EvnSection_IsMedReason').setContainerVisible(true);

			if ( Ext.isEmpty(base_form.findField('EvnSection_IsMedReason').getValue()) ) {
				base_form.findField('EvnSection_IsMedReason').setValue(1);
			}
		}
		else {
			base_form.findField('EvnSection_IsMedReason').setAllowBlank(true);
			base_form.findField('EvnSection_IsMedReason').setContainerVisible(false);
			base_form.findField('EvnSection_IsMedReason').setValue(1);
		}

		return true;
	},
	show: function() {
		sw.Promed.swEmkEvnSectionEditWindow.superclass.show.apply(this, arguments);

		if ( !arguments[0] || !arguments[0].formParams ) {
			sw.swMsg.alert(lang['soobschenie'], lang['nevernyie_parametryi']);
			return false;
		}

		this.restore();
		this.center();

		var base_form = this.FormPanel.getForm();
		base_form.reset();

		base_form.findField('GetRoom_id').setContainerVisible(getRegionNick() == 'kz');
		base_form.findField('GetBed_id').setContainerVisible(getRegionNick() == 'kz');
		
		this.diagPanel.personId = arguments[0].Person_id;
		this.diagPanel.hideHSNField();
		this.diagPanel.hideMsg =  arguments[0].action != 'add';

		this.action = null;
		this.callback = Ext.emptyFn;
		this.formParams = new Object();
		this.formStatus = 'edit';
		this.onHide = Ext.emptyFn;
		this.needCheckMorfoHistologic = true;
		var win = this;

		this.formParams = arguments[0].formParams;
        //base_form.setValues(arguments[0].formParams);

		if ( arguments[0].action ) {
			this.action = arguments[0].action;
		}

		if ( arguments[0].callback ) {
			this.callback = arguments[0].callback;
		}

		if ( arguments[0].onHide ) {
			this.onHide = arguments[0].onHide;
		}

		if (arguments[0] && arguments[0].ARMType_id) {
			this.ARMType_id = arguments[0].ARMType_id;
		} else if (arguments[0] && arguments[0].userMedStaffFact && arguments[0].userMedStaffFact.ARMType_id) {
			this.ARMType_id = arguments[0].userMedStaffFact.ARMType_id;
		} else {
			this.ARMType_id = null;
		}

		if (this.formParams && this.formParams.EvnPS_id) {
			this.EvnPS_id = this.formParams.EvnPS_id;
		} else if (arguments[0].EvnPS_id) {
			this.EvnPS_id = arguments[0].EvnPS_id;
		} else {
			this.EvnPS_id = null;
		}

		if ( !this.action || !this.action.toString().inlist([ 'add', 'edit', 'view' ]) ) {
			this.hide();
			return false;
		}

		this.PersonInfo.load({
			Person_id: (arguments[0].Person_id ? arguments[0].Person_id : ''),
			Person_Birthday: (arguments[0].Person_Birthday ? arguments[0].Person_Birthday : ''),
			Person_Firname: (arguments[0].Person_Firname ? arguments[0].Person_Firname : ''),
			Person_Secname: (arguments[0].Person_Secname ? arguments[0].Person_Secname : ''),
			Person_Surname: (arguments[0].Person_Surname ? arguments[0].Person_Surname : ''),
			callback: function() {
				// var field = base_form.findField('EvnSection_disDate');
				// clearDateAfterPersonDeath('personpanelid', 'EESecEF_PersonInformationFrame', field);
			}
		});

        this.findById('EESecEF_AnatomDiagPanel').isLoaded = ( this.action == 'add' );
		this.findById('EESecEF_AnatomDiagGrid').getGrid().getStore().removeAll();

		base_form.findField('EvnSection_disDate').setMaxValue(undefined);
		base_form.findField('EvnSection_setDate').setMaxValue(undefined);
		base_form.findField('EvnSection_disDate').setMinValue(undefined);
		base_form.findField('EvnSection_setDate').setMinValue(undefined);

		base_form.findField('LpuUnitType_oid').getStore().clearFilter();

		if ( getRegionNick().inlist([ 'buryatiya', 'ekb', 'penza', 'pskov' ]) ) {
			// убираем исход госпитализации и показываем федеральный справочник
			base_form.findField('LeaveType_id').hideContainer();
			base_form.findField('LeaveTypeFed_id').showContainer();

			this.leaveTypeFedFilter();
		}
		else {
			base_form.findField('LeaveTypeFed_id').hideContainer();
			base_form.findField('LeaveType_id').showContainer();

			this.leaveTypeFilter();
		}
		base_form.findField('Diag_eid').getStore().load({
			params: {where:"where Diag_Code like 'X%' or Diag_Code like 'V%' or Diag_Code like 'W%' or Diag_Code like 'Y%'"}
		});

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT });
		loadMask.show();

		base_form.load({
			failure: function() {
				loadMask.hide();

				sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_zagruzke_dannyih_formyi'], function() {
					this.hide();
				}.createDelegate(this));
			}.createDelegate(this),
			params: {
				EvnSection_id: this.formParams.EvnSection_id
			},
			success: function(form, act) {
				if ( !act || !act.response || !act.response.responseText ) {
					loadMask.hide();
					sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_zagruzke_dannyih_formyi'], function() { this.hide(); }.createDelegate(this) );
				}

				var response_obj = Ext.util.JSON.decode(act.response.responseText);

				if ( response_obj[0].accessType == 'view' ) {
					this.action = 'view';
				}
				sw.Promed.EvnSection.filterFedResultDeseaseType({
					LpuUnitType_SysNick: base_form.findField('LpuUnitType_SysNick').getValue(),
					fieldFedLeaveType: base_form.findField('LeaveType_fedid'),
					fieldFedResultDeseaseType: base_form.findField('ResultDeseaseType_fedid')
				})
				sw.Promed.EvnSection.filterFedLeaveType({
					LpuUnitType_SysNick:base_form.findField('LpuUnitType_SysNick').getValue(),
					fieldFedLeaveType: base_form.findField('LeaveType_fedid'),
					fieldLeaveType: base_form.findField('LeaveType_id'),
					fieldFedResultDeseaseType: base_form.findField('ResultDeseaseType_fedid')
				});
				switch ( this.action ) {
					case 'add':
						this.setTitle(WND_HOSP_EESECADD);
						this.enableEdit(true);
					break;

					case 'edit':
						this.setTitle(WND_HOSP_EESECEDIT);
						this.enableEdit(true);
					break;

					case 'view':
						this.setTitle(WND_HOSP_EESECVIEW);
						this.enableEdit(false);
					break;
				}

				if ( getRegionNick() == 'buryatiya' && Ext.isEmpty(base_form.findField('UslugaComplex_id').getValue()) && base_form.findField('PayType_SysNick').getValue() == 'oms' ) {
					sw.swMsg.alert(lang['oshibka'], lang['ne_ukazana_profilnaya_usluga_ukazanie_ishoda_nedostupno'], function() {
						this.hide();
					}.createDelegate(this));
					return false;
				}

				if (getRegionNick() == 'ekb') {
					if (112 == base_form.findField('PayType_id').getValue()) {
						base_form.findField('Mes_sid').clearValue();
					}
					base_form.findField('Mes_sid').setDisabled(112 == base_form.findField('PayType_id').getValue());
					var mes_sid = base_form.findField('Mes_sid').getValue();
					if ( !Ext.isEmpty(mes_sid) ) {
						base_form.findField('Mes_sid').getStore().load({
							callback: function() {
								index = base_form.findField('Mes_sid').getStore().findBy(function(rec) {
									return (rec.get('Mes_id') == mes_sid);
								});

								if ( index >= 0 ) {
									base_form.findField('Mes_sid').setValue(mes_sid);
								}
								else {
									base_form.findField('Mes_sid').clearValue();
								}
							}.createDelegate(this),
							params: {
								Mes_id: mes_sid
							}
						});
					}

					base_form.findField('Mes_sid').clearBaseParams();
					base_form.findField('Mes_sid').getStore().baseParams.UslugaComplexPartition_CodeList = Ext.util.JSON.encode([101]);

					base_form.findField('Mes_sid').setPersonId(base_form.findField('Person_id').getValue());

					base_form.findField('Mes_sid').getStore().baseParams.LpuSection_id = base_form.findField('LpuSection_id').getValue();
					base_form.findField('Mes_sid').getStore().baseParams.MedPersonal_id = base_form.findField('MedPersonal_id').getValue();

					if ((base_form.findField('LpuUnitType_Code').getValue() == 3) || ((base_form.findField('LpuUnitType_Code').getValue() == 5))) {
						base_form.findField('Mes_sid').getStore().baseParams.UslugaComplexPartition_CodeList = Ext.util.JSON.encode([201]);
					} else {
						base_form.findField('Mes_sid').getStore().baseParams.UslugaComplexPartition_CodeList = Ext.util.JSON.encode([101]);
					}

					this.onChangeDates();
				}

				var leave_type_combo = base_form.findField('LeaveType_id');
				var evndie_isanatom_combo = base_form.findField('EvnDie_IsAnatom');

				if (getRegionNick().inlist([ 'buryatiya', 'ekb', 'penza', 'pskov' ])) {
					var leave_type_combo = base_form.findField('LeaveTypeFed_id');
				}

                if ( this.action != 'view' && this.formParams.LeaveType_id ) {
                    leave_type_combo.setValue(this.formParams.LeaveType_id);
                }

				var anatom_where_code;
				var anatom_where_id = response_obj[0].AnatomWhere_id;
				var diag_aid = response_obj[0].Diag_aid;
				var diag_id = response_obj[0].Diag_id;
				var diag_eid = response_obj[0].Diag_eid;
				var evn_die_exp_date = response_obj[0].EvnDie_expDate;
				var evn_section_dis_date = base_form.findField('EvnSection_disDate').getValue();
				var index;
				var lpu_section_aid = response_obj[0].LpuSection_aid;
				var lpu_unit_type_id = response_obj[0].LpuUnitType_id;
				var lpu_unit_type_oid = response_obj[0].LpuUnitType_oid;
				var med_personal_aid = response_obj[0].MedPersonal_aid;
				var med_personal_did = response_obj[0].MedPersonal_did;
				var org_aid = response_obj[0].Org_aid;
				var Org_oidCombo = base_form.findField('Org_oid');
				var Org_oid = response_obj[0].Org_oid;
				var EvnSection_IsTerm = response_obj[0].EvnSection_IsTerm;
				var PayType_SysNick = response_obj[0].PayType_SysNick;

				if (
					Ext.isEmpty(response_obj[0].LpuSectionBedProfileLink_fedid)
					&& (
						(getRegionNick() == 'perm' && (PayType_SysNick == 'oms' || PayType_SysNick == 'ovd'))
						|| (getRegionNick().inlist(['astra', 'ufa', 'krym', 'pskov', 'buryatiya']) && PayType_SysNick == 'oms')
						|| getRegionNick() == 'penza'
					)
				) {
					sw.swMsg.alert(langs('Ошибка'), langs('Не заполнено поле "Профиль коек"! Откройте КВС на редактирование и заполните профиль коек'), function() { win.hide(); });
					return false;
				}
				// if (lpu_unit_type_id && lpu_unit_type_id == 1) {
				// 	base_form.findField('EvnSection_IsAdultEscort').showContainer();
				// 	if ( Ext.isEmpty(base_form.findField('EvnSection_IsAdultEscort').getValue()) ) {
				// 		base_form.findField('EvnSection_IsAdultEscort').setValue(1);
				// 	}
				// } else {
				// 	base_form.findField('EvnSection_IsAdultEscort').hideContainer();
				// 	base_form.findField('EvnSection_IsAdultEscort').setValue(1);
				// }

				base_form.findField('EvnSection_IsAdultEscort').fireEvent('change', base_form.findField('EvnSection_IsAdultEscort'), base_form.findField('EvnSection_IsAdultEscort').getValue());

				setCurrentDateTime({
					callback: Ext.emptyFn,
					dateField: base_form.findField('EvnSection_disDate'),
					loadMask: false,
					setDate: false,
					setDateMaxValue: true,
					addMaxDateDays: this.addMaxDateDays,
					windowId: this.id
				});

				// Выполняются действия, которые должны выполняться после смены даты выписки
				base_form.findField('MedStaffFact_did').clearValue();

				if ( !evn_section_dis_date ) {
					setMedStaffFactGlobalStoreFilter({
						isStac: true
					});
					base_form.findField('MedStaffFact_did').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));
				}
				else {
					setMedStaffFactGlobalStoreFilter({
						 isStac: true
						,onDate: Ext.util.Format.date(evn_section_dis_date, 'd.m.Y')
					});
					base_form.findField('MedStaffFact_did').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));
				}

				// Выполняются действия, которые должны выполняться после изменения даты проведения экспертизы
				base_form.findField('LpuSection_aid').clearValue();
				base_form.findField('MedStaffFact_aid').clearValue();

				if ( !evn_die_exp_date ) {
					setLpuSectionGlobalStoreFilter();
					base_form.findField('LpuSection_aid').getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));

					setMedStaffFactGlobalStoreFilter();
					base_form.findField('MedStaffFact_aid').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));
				}
				else {
					setLpuSectionGlobalStoreFilter({
						onDate: evn_die_exp_date
					});
					base_form.findField('LpuSection_aid').getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));

					setMedStaffFactGlobalStoreFilter({
						onDate: evn_die_exp_date
					});
					base_form.findField('MedStaffFact_aid').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));
				}

				// запоминаем, что было сохранено
				var CureResult_id = base_form.findField('CureResult_id').getValue();
				leave_type_combo.fireEvent('change', leave_type_combo, leave_type_combo.getValue());
				evndie_isanatom_combo.fireEvent('change', evndie_isanatom_combo, evndie_isanatom_combo.getValue());

				// восстанавливаем
				if (CureResult_id) {
					base_form.findField('CureResult_id').setValue(CureResult_id);
					base_form.findField('CureResult_id').fireEvent('change', base_form.findField('CureResult_id'), CureResult_id);
				}

				index = base_form.findField('AnatomWhere_id').getStore().findBy(function(record, id) {
					return ( parseInt(record.get('AnatomWhere_id')) == parseInt(anatom_where_id) );
				});

				if ( index >= 0 ) {
					anatom_where_code = parseInt(base_form.findField('AnatomWhere_id').getStore().getAt(index).get('AnatomWhere_Code'));

					base_form.findField('AnatomWhere_id').fireEvent('change', base_form.findField('AnatomWhere_id'), anatom_where_id);
				}

				index = base_form.findField('MedStaffFact_did').getStore().findBy(function(record, id) {
                    return ( record.get('MedPersonal_id') == med_personal_did );
				});

				if ( index >= 0 ) {
					base_form.findField('MedStaffFact_did').setValue(base_form.findField('MedStaffFact_did').getStore().getAt(index).get('MedStaffFact_id'));
				}

				index = base_form.findField('LpuSection_aid').getStore().findBy(function(record, id) {
                    return ( record.get('LpuSection_id') == lpu_section_aid );
				});

				if ( index >= 0 ) {
					base_form.findField('LpuSection_aid').setValue(lpu_section_aid);
				}

				index = base_form.findField('MedStaffFact_aid').getStore().findBy(function(record, id) {
					if ( record.get('LpuSection_id') == lpu_section_aid && record.get('MedPersonal_id') == med_personal_aid ) {
						return true;
					}
					else {
						return false;
					}
				});

				if ( index >= 0 ) {
					base_form.findField('MedStaffFact_aid').setValue(base_form.findField('MedStaffFact_aid').getStore().getAt(index).get('MedStaffFact_id'));
				}

				if ( diag_aid ) {
					base_form.findField('Diag_aid').getStore().load({
						callback: function() {
							base_form.findField('Diag_aid').getStore().each(function(rec) {
								if ( rec.get('Diag_id') == diag_aid ) {
									base_form.findField('Diag_aid').fireEvent('select', base_form.findField('Diag_aid'), rec, 0);
									base_form.findField('EvnSection_setDate').fireEvent('change', base_form.findField('EvnSection_setDate'), base_form.findField('EvnSection_setDate').getValue());
								}
							});
						},
						params: { where: "where DiagLevel_id = 4 and Diag_id = " + diag_aid }
					});
				}

				if ( diag_id ) {
					base_form.findField('Diag_id').getStore().load({
						callback: function() {
							base_form.findField('Diag_id').getStore().each(function(rec) {
								if ( rec.get('Diag_id') == diag_id ) {
									base_form.findField('Diag_id').setValue(diag_id);
									base_form.findField('Diag_id').fireEvent('select', base_form.findField('Diag_id'), rec, 0);
									base_form.findField('EvnSection_setDate').fireEvent('change', base_form.findField('EvnSection_setDate'), base_form.findField('EvnSection_setDate').getValue());
									win.diagPanel.Diag_Code = base_form.findField('Diag_id').getFieldValue('Diag_Code');
									win.diagPanel.refreshHSN();
									win.setDiagEidAllowBlank();
									win.refreshFieldsVisibility(['DeseaseBegTimeType_id','DeseaseType_id','TumorStage_id']);
								}
							});
						},
						params: { where: "where DiagLevel_id = 4 and Diag_id = " + diag_id }
					});
				}

				if ( diag_eid ) {
					base_form.findField('Diag_eid').getStore().load({
						callback: function() {
							base_form.findField('Diag_eid').getStore().each(function(rec) {
								if ( rec.get('Diag_id') == diag_eid ) {
									base_form.findField('Diag_eid').setValue(diag_eid);
									base_form.findField('Diag_eid').fireEvent('select', base_form.findField('Diag_eid'), rec, 0);
								}
							});
						},
						params: { where: "where DiagLevel_id = 4 and Diag_id = " + diag_eid }
					});
				}

				if ( org_aid ) {
					var org_type;

					switch ( anatom_where_code ) {
						case 2: org_type = 'lpu'; break;
						case 3: org_type = 'anatom_old'; break;
					}

					if ( org_type ) {
						base_form.findField('Org_aid').getStore().load({
							callback: function(records, options, success) {
								if ( success ) {
									base_form.findField('Org_aid').setValue(org_aid);
								}
							},
							params: {
								Org_id: org_aid,
								OrgType: org_type,
								onlyFromDictionary: true
							}
						});
					}
				}
				
				if ( Org_oid ) {
					Org_oidCombo.getStore().load({
						callback: function(records, options, success) {
							Org_oidCombo.clearValue();
							if ( success ) {
								Org_oidCombo.setValue(Org_oid);
							}
						},
						params: {
							Org_id: Org_oid
						}
					});
				}

				base_form.findField('LpuUnitType_oid').getStore().filterBy(function(rec) {
					if ( rec.get('LpuUnitType_id') != lpu_unit_type_id ) {
						return true;
					}
					else {
						return false;
					}
				});

				if ( lpu_unit_type_oid && lpu_unit_type_oid != lpu_unit_type_id ) {
					base_form.findField('LpuUnitType_oid').setValue(lpu_unit_type_oid);
				}

				var mes2_id = base_form.findField('Mes2_id').getValue();
				this.loadMesCombo();
				this.loadMes2Combo(mes2_id, false);

				this.checkLpuUnitType();

				if ( getRegionNick().inlist([ 'kaluga' ]) ) {
					var oldValue = base_form.findField('LpuSectionProfile_id').getValue();

					if ( !Ext.isEmpty(base_form.findField('LpuSection_id').getValue()) ) {
						if (
							!base_form.findField('LpuSectionProfile_id').getStore().baseParams.LpuSection_id
							|| base_form.findField('LpuSection_id').getValue() != base_form.findField('LpuSectionProfile_id').getStore().baseParams.LpuSection_id
						) {
							base_form.findField('LpuSectionProfile_id').lastQuery = '';
							base_form.findField('LpuSectionProfile_id').getStore().removeAll();
							base_form.findField('LpuSectionProfile_id').getStore().baseParams.LpuSection_id = base_form.findField('LpuSection_id').getValue();
							base_form.findField('LpuSectionProfile_id').getStore().baseParams.onDate = (!Ext.isEmpty(base_form.findField('EvnSection_setDate').getValue()) ? base_form.findField('EvnSection_setDate').getValue().format('d.m.Y') : getGlobalOptions().date);
							base_form.findField('LpuSectionProfile_id').getStore().load({
								callback: function () {
									var index = base_form.findField('LpuSectionProfile_id').getStore().findBy(function (rec) {
										return (rec.get('LpuSectionProfile_id') == oldValue);
									});

									if ( index == -1 ) {
										// выбираем первый попавшийся
										if ( base_form.findField('LpuSectionProfile_id').getStore().getCount() > 0 ) {
											base_form.findField('LpuSectionProfile_id').setValue(base_form.findField('LpuSectionProfile_id').getStore().getAt(0).get('LpuSectionProfile_id'));
										}
									}
									else {
										base_form.findField('LpuSectionProfile_id').setValue(oldValue);
									}

									base_form.findField('LpuSectionProfile_id').fireEvent('change', base_form.findField('LpuSectionProfile_id'), base_form.findField('LpuSectionProfile_id').getValue());
								}
							});
						}
					}
				}

				if ( !Ext.isEmpty(EvnSection_IsTerm) ) {
					base_form.findField('EvnSection_IsTerm').setValue(EvnSection_IsTerm);
				}

				this.setIsMedReason();
				this.setDiagEidAllowBlank();
				this.refreshFieldsVisibility(['DeseaseBegTimeType_id','DeseaseType_id','TumorStage_id', 'PrivilegeType_id', 'PayTypeERSB_id']);
				this.loadRoomList();
				this.setBedListAllowBlank();
				loadMask.hide();

				//base_form.clearInvalid();
				base_form.items.each(function(f) {
					f.validate();
				});

				if ( !base_form.findField('EvnSection_disDate').disabled ) {
					base_form.findField('EvnSection_disDate').focus(true, 250);
				}
				else {
					this.buttons[this.buttons.length - 1].focus();
				}
			}.createDelegate(this),
			url: '/?c=EvnSection&m=loadEvnSectionEditForm'
		});
	},
	setDiagEidAllowBlank: function() {
		if(!getRegionNick().inlist(['kz','ufa'])){
			var base_form = this.FormPanel.getForm();
			var date = base_form.findField('EvnSection_setDate').getValue();
			var field = base_form.findField('Diag_eid');
			var xdate = new Date(2016,0,1);
			var diag_combo = base_form.findField('Diag_id');
			var diag_id = diag_combo.getValue();
			if(!Ext.isEmpty(diag_id) 
				&& diag_combo.getStore().getById(diag_id) 
				&& diag_combo.getStore().getById(diag_id).get('Diag_Code').search(new RegExp("^[ST]", "i")) >= 0 
				&& (Ext.isEmpty(date) || date>=xdate)
				&& this.action != 'view'
			) {
				field.setAllowBlank(false);
				field.enable();
			} else {
				field.setAllowBlank(true);
				field.disable();
			}
		}
	},
	setBedListAllowBlank: function() {
		var win = this,
			base_form = this.FormPanel.getForm(),
			getroom_field = base_form.findField('GetRoom_id'),
			getbed_field = base_form.findField('GetBed_id'),
			allowBlank = !base_form.findField('PayType_SysNick').getValue().inlist(['bud', 'Resp']) || getRegionNick() != 'kz';

		getroom_field.setAllowBlank(allowBlank);
		getbed_field.setAllowBlank(allowBlank);
	},
	loadRoomList: function() {
		if (getRegionNick() != 'kz') return false;
		var win = this,
			base_form = this.FormPanel.getForm(),
			getroom_field = base_form.findField('GetRoom_id');

		getroom_field.lastQuery = '';
		getroom_field.getStore().load({
			params: {
				Lpu_id: getGlobalOptions().lpu_id,
				LpuSection_id: base_form.findField('LpuSection_id').getValue(),
				Person_id: base_form.findField('Person_id').getValue(),
				GetRoom_id: win.action == 'view' ? getroom_field.getValue() : null
			},
			callback: function() {
				getroom_field.setValue(getroom_field.getValue());
				win.loadBedList();
			}
		});
	},
	loadBedList: function(autoValue) {
		if (getRegionNick() != 'kz') return false;
		var win = this,
			base_form = this.FormPanel.getForm(),
			getroom_id = base_form.findField('GetRoom_id').getValue(),
			getbed_field = base_form.findField('GetBed_id');

		if(!getroom_id) return false;

		getbed_field.lastQuery = '';
		getbed_field.getStore().load({
			params: {
				GetRoom_id: getroom_id,
				GetBed_id: win.action == 'view' ? getbed_field.getValue() : null
			},
			callback: function() {
				if (autoValue) {
					getbed_field.setValue(getbed_field.getStore().getAt(0).get('GetBed_id'));
				} else {
					getbed_field.setValue(getbed_field.getValue());
				}

			}
		});
	},
	tabIndex: TABINDEX_EESecEF,
	width: 800
});