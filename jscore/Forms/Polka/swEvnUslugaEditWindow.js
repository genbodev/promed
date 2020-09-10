/**
 * swEvnUslugaEditWindow - окно редактирования/добавления выполнения общей услуги.
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package		Polka
 * @access		public
 * @copyright	Copyright (c) 2009 Swan Ltd.
 * @author		Stas Bykov aka Savage (savage1981@gmail.com)
 * @version		09.11.2012
 * @comment		Префикс для id компонентов EUComEF (EvnUslugaEditForm)
 *
 *
 * @input data:	action - действие (add, edit, view)
 *				parentClass - класс родительского события
 *
 *
 * Использует:	окно добавления/редактирования осложнения (swEvnAggEditWindow)
 *				окно поиска организации (swOrgSearchWindow)
 */

sw.Promed.swEvnUslugaEditWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swEvnUslugaEditWindow',
	objectSrc: '/jscore/Forms/Polka/swEvnUslugaEditWindow.js',

	action: null,
	allowRayTherapy: function() {
		var base_form = this.FormPanel.getForm();
		var combo = base_form.findField('LpuSection_uid');
		var result = false;

		if ( getGlobalOptions().region && getGlobalOptions().region.nick == 'ufa' && !Ext.isEmpty(combo.getValue()) ) {
			var index = combo.getStore().findBy(function(rec) {
				return (rec.get(combo.valueField) == combo.getValue());
			});

			if ( index >= 0 && combo.getStore().getAt(index).get('LpuSectionProfile_Code').inlist([ '577', '677', '877' ]) && this.action != 'view' ) {
				result = true;
			}
		}

		return result;
	},
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: false,
	closeAction: 'hide',
	collapsible: true,
	deleteEvent: function(event) {
		if ( this.action == 'view' ) {
			return false;
		}

		if ( event != 'EvnAgg' ) {
			return false;
		}

		var error = '';
		var grid = null;
		var question = '';
		var params = new Object();
		var url = '';

		switch ( event ) {
			case 'EvnAgg':
				error = langs('При удалении осложнения возникли ошибки');
				grid = this.findById('EUComEF_EvnAggGrid');
				question = langs('Удалить осложнение?');
				url = '/?c=EvnAgg&m=deleteEvnAgg';
			break;
		}

		if ( !grid || !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get(event + '_id') ) {
			return false;
		}

		var selected_record = grid.getSelectionModel().getSelected();
		params[event + '_id'] = selected_record.get(event + '_id');

		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId, text, obj) {
				if ( buttonId == 'yes' ) {
					var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Удаление записи..."});
					loadMask.show();

					Ext.Ajax.request({
						failure: function(response, options) {
							loadMask.hide();
							sw.swMsg.alert(langs('Ошибка'), error);
						},
						params: params,
						success: function(response, options) {
							loadMask.hide();

							var response_obj = Ext.util.JSON.decode(response.responseText);

							if ( response_obj.success == false ) {
								sw.swMsg.alert(langs('Ошибка'), response_obj.Error_Msg ? response_obj.Error_Msg : error);
							}
							else {
								grid.getStore().remove(selected_record);

								if ( grid.getStore().getCount() == 0 ) {
									grid.getTopToolbar().items.items[1].disable();
									grid.getTopToolbar().items.items[2].disable();
									grid.getTopToolbar().items.items[3].disable();
									LoadEmptyRow(grid);
								}
							}

							grid.getView().focusRow(0);
							grid.getSelectionModel().selectFirstRow();
						},
						url: url
					});
				}
			}.createDelegate(this),
			icon: Ext.MessageBox.QUESTION,
			msg: question,
			title: langs('Вопрос')
		});
	},
	
	setLpuSectionAndMedStaffFactFilter: function(isLoading) {
		
		var win = this;
		var base_form = this.FormPanel.getForm();

		var lpu_section_id = base_form.findField('LpuSection_uid').getValue();
		var med_staff_fact_id = base_form.findField('MedStaffFact_id').getValue();
		var usluga_place_id = base_form.findField('UslugaPlace_id').getValue();
		var lpu_id = base_form.findField('Lpu_uid').getValue();
		
		var set_date_field = base_form.findField('EvnUslugaCommon_setDate');
		var set_date = set_date_field.getValue();

		base_form.findField('LpuSection_uid').clearValue();
		base_form.findField('MedStaffFact_id').clearValue();

		var section_filter_params = {};
		var medstafffact_filter_params = {};

		var user_med_staff_fact_id = this.UserMedStaffFact_id;
		var user_lpu_section_id = this.UserLpuSection_id;
		var user_med_staff_facts = this.UserMedStaffFacts;
		var user_lpu_sections = this.UserLpuSections;

		medstafffact_filter_params.allowLowLevel = 'yes';
		section_filter_params.allowLowLevel = 'yes';

		// Устанавливаем фильтр по дате для услуг
		if (getRegionNick() == 'perm') {
			var ucat_cmb = base_form.findField('UslugaCategory_id');
			var xdate = new Date(2015, 0, 1);
			if (base_form.findField('EvnUslugaCommon_setDate').getValue() >= xdate) {
				index = ucat_cmb.getStore().findBy(function (rec) {
					return (rec.get('UslugaCategory_SysNick') == 'gost2011');
				});
				ucat_rec = ucat_cmb.getStore().getAt(index);

				if (ucat_rec) {
					ucat_cmb.fireEvent('select', ucat_cmb, ucat_rec);
				}
			} else {
				index = ucat_cmb.getStore().findBy(function (rec) {
					return (rec.get('UslugaCategory_SysNick') == 'tfoms');
				});
				ucat_rec = ucat_cmb.getStore().getAt(index);

				if (ucat_rec) {
					ucat_cmb.fireEvent('select', ucat_cmb, ucat_rec);
				}
			}
		}

		var UslugaComplex_Date = (typeof set_date == 'object') ? Ext.util.Format.date(set_date, 'd.m.Y') : set_date;
		if (!Ext.isEmpty(this.UslugaComplex_Date) && getValidDT(this.UslugaComplex_Date, '00:00') > getValidDT(UslugaComplex_Date, '00:00')) {
			UslugaComplex_Date = this.UslugaComplex_Date;
		}

		var UslugaComplex_id = base_form.findField('UslugaComplex_id').getValue();
		base_form.findField('UslugaComplex_id').clearValue();
		base_form.findField('UslugaComplex_id').lastQuery = 'This query sample that is not will never appear';
		base_form.findField('UslugaComplex_id').getStore().baseParams.LpuSection_id = null;
		base_form.findField('UslugaComplex_id').getStore().baseParams.UslugaComplex_Date = UslugaComplex_Date;
		base_form.findField('UslugaComplex_id').getStore().removeAll();

		if (getRegionNick() == 'ekb') {
			var xdate = new Date(2015, 0, 1);
			if (win.parentClass == 'EvnPLStom') {
				if (!Ext.isEmpty(base_form.findField('EvnUslugaCommon_setDate').getValue()) && base_form.findField('EvnUslugaCommon_setDate').getValue() >= xdate) {
					base_form.findField('UslugaComplex_id').getStore().baseParams.UslugaComplexPartition_CodeList = Ext.util.JSON.encode([303]);
				} else {
					base_form.findField('UslugaComplex_id').getStore().baseParams.UslugaComplexPartition_CodeList = Ext.util.JSON.encode([300, 301]);
				}
			}
		}

		if ( set_date ) {
			section_filter_params.onDate = Ext.util.Format.date(set_date, 'd.m.Y');
			medstafffact_filter_params.onDate = Ext.util.Format.date(set_date, 'd.m.Y');
		}

		// фильтр или на конкретное место работы или на список мест работы
		if ( user_med_staff_fact_id && user_lpu_section_id && this.action == 'add' ) {
			section_filter_params.id = user_lpu_section_id;
			medstafffact_filter_params.id = user_med_staff_fact_id;
		}
		else if ( user_med_staff_facts && user_lpu_sections && this.action == 'add' ) {
			section_filter_params.ids = user_lpu_sections;
			medstafffact_filter_params.ids = user_med_staff_facts;
		}

		if (win.parentClass.toString().inlist(['EvnPS', 'EvnSection']) && 'kareliya' === getRegionNick()){
			medstafffact_filter_params.isStac = true;
		}
		
		var localLoading = true;
		
		if (getRegionNick().inlist(['ekb','astra']) && usluga_place_id == 2 && lpu_id) {
			index = swLpuDispContractStore.findBy(function(rec) {
				if (rec.get('Lpu_oid') == lpu_id) {
					var ldc_set_date = Date.parseDate(rec.get('LpuDispContract_setDate'), 'd.m.Y');
					var ldc_dis_date = Date.parseDate(rec.get('LpuDispContract_disDate'), 'd.m.Y');
					if ( (Ext.isEmpty(ldc_set_date) || ldc_set_date <= set_date) && (Ext.isEmpty(ldc_dis_date) || ldc_dis_date >= set_date) ) {
						return true;
					}
				}
			});
			
			if ( index >= 0 ) {
				section_filter_params.isAliens = true;
				medstafffact_filter_params.isAliens = true;
				section_filter_params.ldcFilterDate = true;
				medstafffact_filter_params.ldcFilterDate = true;
				section_filter_params.Lpu_id = base_form.findField('Lpu_uid').getValue();
				medstafffact_filter_params.Lpu_id = base_form.findField('Lpu_uid').getValue();
			} else {
				
				localLoading = false;

				base_form.findField('LpuSection_uid').getStore().load({
					callback: function() {
						if ( base_form.findField('LpuSection_uid').getStore().getById(lpu_section_id) ) {
							base_form.findField('LpuSection_uid').setValue(lpu_section_id);
							if (!isLoading) base_form.findField('LpuSection_uid').fireEvent('change', base_form.findField('LpuSection_uid'), lpu_section_id);
							base_form.findField('UslugaComplex_id').getStore().baseParams.LpuSection_id = lpu_section_id;
						} 
						else if (base_form.findField('LpuSection_uid').getStore().getCount() && !isLoading) {
							lpu_section_id = base_form.findField('LpuSection_uid').getStore().getAt(0).get('LpuSection_id');
							base_form.findField('LpuSection_uid').setValue(lpu_section_id);
							base_form.findField('LpuSection_uid').fireEvent('change', base_form.findField('LpuSection_uid'), lpu_section_id);
							base_form.findField('UslugaComplex_id').getStore().baseParams.LpuSection_id = lpu_section_id;
						}
					}.createDelegate(this),
					params: {
						mode: 'combo',
						date: set_date ? Ext.util.Format.date(set_date, 'd.m.Y') : null,
						Lpu_id: lpu_id
					}
				});
				base_form.findField('MedStaffFact_id').getStore().load({
					callback: function() {
						if ( base_form.findField('MedStaffFact_id').getStore().getById(med_staff_fact_id) ) {
							base_form.findField('MedStaffFact_id').setValue(med_staff_fact_id);
						} 
					},
					params: {
						mode: 'combo',
						date: set_date ? Ext.util.Format.date(set_date, 'd.m.Y') : null,
						Lpu_id: lpu_id
					}
				});
			}
		}

		if (localLoading) {
			
			setLpuSectionGlobalStoreFilter(section_filter_params);
			setMedStaffFactGlobalStoreFilter(medstafffact_filter_params);

			base_form.findField('LpuSection_uid').getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));
			base_form.findField('MedStaffFact_id').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));

			if ( base_form.findField('LpuSection_uid').getStore().getById(user_lpu_section_id||lpu_section_id) ) {
				base_form.findField('LpuSection_uid').setValue(user_lpu_section_id||lpu_section_id);
				if (!isLoading) base_form.findField('LpuSection_uid').fireEvent('change', base_form.findField('LpuSection_uid'), user_lpu_section_id||lpu_section_id);
				base_form.findField('UslugaComplex_id').getStore().baseParams.LpuSection_id = user_lpu_section_id||lpu_section_id;
			} 
			else if (getRegionNick() == 'ekb' && usluga_place_id.inlist([1,2]) && base_form.findField('LpuSection_uid').getStore().getCount() && lpu_id && !isLoading) {
				lpu_section_id = base_form.findField('LpuSection_uid').getStore().getAt(0).get('LpuSection_id');
				base_form.findField('LpuSection_uid').setValue(lpu_section_id);
				if (!isLoading) base_form.findField('LpuSection_uid').fireEvent('change', base_form.findField('LpuSection_uid'), lpu_section_id);
				base_form.findField('UslugaComplex_id').getStore().baseParams.LpuSection_id = lpu_section_id;
			}

			if ( base_form.findField('MedStaffFact_id').getStore().getById(user_med_staff_fact_id||med_staff_fact_id) ) {
				base_form.findField('MedStaffFact_id').setValue(user_med_staff_fact_id||med_staff_fact_id);
			}
		}

		/*
			если форма открыта на редактирование и задано отделение и
			место работы или задан список мест работы, то не даем редактировать вообще
		*/
		if ( this.action == 'edit' && ((user_med_staff_fact_id && user_lpu_section_id ) || ( this.UserMedStaffFacts && this.UserMedStaffFacts.length > 0 )) ) {
			base_form.findField('LpuSection_uid').disable();
			base_form.findField('MedStaffFact_id').disable();
		}

		/*
			если форма открыта на добавление и задано отделение и
			место работы, то устанавливаем их не даем редактировать вообще
		*/
		if ( this.action == 'add' && user_med_staff_fact_id && user_lpu_section_id ) {
			base_form.findField('LpuSection_uid').setValue(user_lpu_section_id);
			base_form.findField('LpuSection_uid').disable();
			base_form.findField('MedStaffFact_id').setValue(user_med_staff_fact_id);
			base_form.findField('MedStaffFact_id').disable();
		}
		/*
			если форма открыта на добавление и задан список отделений и
			мест работы, но он состоит из одного элемета,
			то устанавливаем значение и не даем редактировать
		*/
		else if ( this.action == 'add' && this.UserMedStaffFacts && this.UserMedStaffFacts.length == 1 ) {
			// список состоит из одного элемента (устанавливаем значение и не даем редактировать)
			base_form.findField('LpuSection_uid').setValue(this.UserLpuSections[0]);
			base_form.findField('LpuSection_uid').disable();
			base_form.findField('MedStaffFact_id').setValue(this.UserMedStaffFacts[0]);
			base_form.findField('MedStaffFact_id').disable();
		}

		base_form.findField('MedSpecOms_id').onChangeDateField(set_date_field, set_date);
		base_form.findField('LpuSectionProfile_id').onChangeDateField(set_date_field, set_date);

		if (!Ext.isEmpty(UslugaComplex_id)) {
			// если была выбрана услуга, пытаемся загрузить услугу вновь
			win.loadUslugaComplex(UslugaComplex_id);
		}

		win.checkOtherLpu();
	},

	checkOtherLpu: function() {
		var base_form = this.FormPanel.getForm();
		var isMinusUslugaField = base_form.findField('EvnUslugaCommon_IsMinusUsluga');

		var isMinusUsluga = isMinusUslugaField.getValue();
		var LpuUnitType_SysNick = base_form.findField('EvnUslugaCommon_pid').getFieldValue('LpuUnitType_SysNick');
		var Lpu_uid = base_form.findField('Lpu_uid').getValue();
		var Date = Ext.util.Format.date(base_form.findField('EvnUslugaCommon_setDate').getValue());

		if (getRegionNick() == 'perm'
			&& !Ext.isEmpty(Lpu_uid) && !Ext.isEmpty(Date)
			&& LpuUnitType_SysNick == 'stac' && this.parentClass.inlist(['EvnPS','EvnSection'])
		) {
			Ext.Ajax.request({
				url: '/?c=TariffVolumes&m=getTariffClassListByLpu',
				params: {Lpu_oid: Lpu_uid, Date: Date},
				callback: function(options, success, response) {
					isMinusUslugaField.setValue(false);
					isMinusUslugaField.hideContainer();

					if (success && !Ext.isEmpty(response.responseText)) {
						var list = Ext.util.JSON.decode(response.responseText);

						if ('2015-10PSO'.inlist(list)) {
							isMinusUslugaField.setValue(isMinusUsluga);
							isMinusUslugaField.showContainer();
						}
					}
				}
			});
		} else {
			isMinusUslugaField.setValue(false);
			isMinusUslugaField.hideContainer();
		}
	},

	doSave_default: function(options) {
		// options @Object
		// options.openChildWindow @Function Открыть дочернее окно после сохранения
		// options.print @Boolean Вызвать печать после сохранения услуги
		if ( this.formStatus == 'save' || this.action == 'view' ) {
			return false;
		}
		options = options || {};
		options.ignoreErrors = options.ignoreErrors || [];
		this.formStatus = 'save';

		var base_form = this.FormPanel.getForm();
		
		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					this.formStatus = 'edit';
					this.FormPanel.getFirstInvalidEl().focus(true);
					log(this.FormPanel.getFirstInvalidEl());
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		var evn_usluga_common_pid = base_form.findField('EvnUslugaCommon_pid').getValue();

		if ( (this.parentClass == 'EvnVizit' || this.parentClass == 'EvnPS') && !evn_usluga_common_pid && getRegionNick().inlist(['perm', 'kareliya'])) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					this.formStatus = 'edit';
					base_form.findField('EvnUslugaCommon_pid').focus(true);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: langs('Не выбрано отделение (посещение)'),
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		var EvnUslugaCommon_Price = 0;
		var index;
		var med_personal_id;
		var MedPersonal_Fin;
		var med_staff_fact_id = base_form.findField('MedStaffFact_id').getValue();
		var params = new Object();
		var pay_type_nick = base_form.findField('PayType_id').getFieldValue('PayType_SysNick');
		var record;
		var usluga_complex_id = base_form.findField('UslugaComplex_id').getValue();
		var usluga_complex_code = base_form.findField('UslugaComplex_id').getFieldValue('UslugaComplex_Code');
		var usluga_complex_name = base_form.findField('UslugaComplex_id').getFieldValue('UslugaComplex_Name');

		var set_date = base_form.findField('EvnUslugaCommon_setDate').getValue(),
			set_time = base_form.findField('EvnUslugaCommon_setTime').getValue(),
			dis_date = base_form.findField('EvnUslugaCommon_disDate').getValue(),
			dis_time = base_form.findField('EvnUslugaCommon_disTime').getValue();

		if (!Ext.isEmpty(dis_date)) {
			var setDateStr = Ext.util.Format.date(set_date, 'Y-m-d')+' '+(Ext.isEmpty(set_time)?'00:00':set_time);
			var disDateStr = Ext.util.Format.date(dis_date, 'Y-m-d')+' '+(Ext.isEmpty(dis_time)?'00:00':dis_time);

			if (Date.parseDate(setDateStr, 'Y-m-d H:i') > Date.parseDate(disDateStr, 'Y-m-d H:i')) {
				Ext.MessageBox.show({
					buttons: Ext.Msg.OK,
					fn: function() {
						this.formStatus = 'edit';
						base_form.findField('EvnUslugaCommon_setDate').focus(false)
					}.createDelegate(this),
					icon: Ext.Msg.WARNING,
					msg: langs('Дата окончания выполнения услуги не может быть меньше даты начала выполнения услуги.'),
					title: langs('Ошибка')
				});
				return false;
			}
		}

		if ( !Ext.isEmpty(base_form.findField('UslugaComplexTariff_UED').getValue()) ) {
			EvnUslugaCommon_Price = EvnUslugaCommon_Price + Number(base_form.findField('UslugaComplexTariff_UED').getValue());
		}

		// MedPersonal_id
		index = base_form.findField('MedStaffFact_id').getStore().findBy(function(rec) {
			return (rec.get('MedStaffFact_id') == med_staff_fact_id);
		});

		if ( index >= 0 ) {
			base_form.findField('MedPersonal_id').setValue(base_form.findField('MedStaffFact_id').getStore().getAt(index).get('MedPersonal_id'));
			MedPersonal_Fin = base_form.findField('MedStaffFact_id').getStore().getAt(index).get('MedPersonal_Fin');
		}

		if (Ext.isEmpty(base_form.findField('EvnUslugaCommon_pid').getValue()) && !Ext.isEmpty(base_form.findField('Evn_id').getValue()) && !getRegionNick().inlist(['perm', 'kareliya','ekb'])){
			base_form.findField('EvnUslugaCommon_pid').setValue(base_form.findField('Evn_id').getValue());
		}

		// Посещение/движение
        if ( base_form.findField('EvnUslugaCommon_pid').disabled ) {
            params.EvnUslugaCommon_pid = evn_usluga_common_pid;
        }

        if ( base_form.findField('EvnPrescr_id').disabled ) {
            params.EvnPrescr_id = base_form.findField('EvnPrescr_id').getValue();
        }

		if ( base_form.findField('Lpu_uid').disabled ) {
			params.Lpu_uid = base_form.findField('Lpu_uid').getValue();
		}

		if ( base_form.findField('LpuSection_uid').disabled ) {
			params.LpuSection_uid = base_form.findField('LpuSection_uid').getValue();
		}

		if ( base_form.findField('MedStaffFact_id').disabled ) {
			params.MedStaffFact_id = base_form.findField('MedStaffFact_id').getValue();
		}

		if ( base_form.findField('PayType_id').disabled ) {
			params.PayType_id = base_form.findField('PayType_id').getValue();
		}

		if ( base_form.findField('UslugaMedType_id').disabled ) {
			params.UslugaMedType_id = base_form.findField('UslugaMedType_id').getValue();
		}

		if ( base_form.findField('DiagSetClass_id').disabled ) {
			params.DiagSetClass_id = base_form.findField('DiagSetClass_id').getValue();
		}

		if ( base_form.findField('LpuSectionProfile_id').disabled ) {
			params.LpuSectionProfile_id = base_form.findField('LpuSectionProfile_id').getValue();
		}

        var me = this;
        if ( me.uslugaPanel.isUslugaComplexPackage() ) {
            var uslugaErr = me.uslugaPanel.validateUslugaSelectedList();
            if (uslugaErr) {
                me.formStatus = 'edit';
                sw.swMsg.alert(langs('Ошибка'), uslugaErr);
                return false;
            }
            params.UslugaSelectedList = me.uslugaPanel.getUslugaSelectedList(true);
        }

		var sex_code = this.PersonInfo.getFieldValue('Sex_Code');
		var person_birthday = this.PersonInfo.getFieldValue('Person_Birthday');
		var diag_record = base_form.findField('Diag_id').getStore().getById(base_form.findField('Diag_id').getValue());
		var person_age = swGetPersonAge(person_birthday, set_date);
		var person_age_month = swGetPersonAgeMonth(person_birthday, set_date);
		var person_age_day = swGetPersonAgeDay(person_birthday, set_date);
		try {
			if ( getRegionNick() == 'ekb' && diag_record) {
				if ( person_age == -1 || person_age_month == -1 || person_age_day == -1 ) {
					throw {msg: langs('Ошибка при определении возраста пациента')};
				}
				if ( !sex_code || !(sex_code.toString().inlist([ '1', '2' ])) ) {
					throw {msg: langs('Не указан пол пациента')};
				}
				if ( !Ext.isEmpty(diag_record.get('Sex_Code')) && Number(diag_record.get('Sex_Code')) != Number(sex_code) ) {
					throw {warningMsg: langs('Выбранный диагноз не соответствует полу пациента'), fieldName: 'Diag_id'};
				}
				if ( pay_type_nick == 'oms' ) {
					var LpuSectionProfile_Code = base_form.findField('MedStaffFact_id').getFieldValue('LpuSectionProfile_Code');
					if ( LpuSectionProfile_Code && LpuSectionProfile_Code.inlist([ '658', '684', '558', '584' ]) ) {
						if ( diag_record.get('DiagFinance_IsHealthCenter') != 1 ) {
							throw {warningMsg: langs('Диагноз не оплачивается для Центров здоровья'), fieldName: 'Diag_id'};
						}
					} else if ( diag_record.get('DiagFinance_IsOms') == 0 ) {
						throw {warningMsg: langs('Данный диагноз не подлежит оплате в системе ОМС. Смените вид оплаты.'), fieldName: 'Diag_id'};
					}
				}
				if (
					(person_age < 18 && Number(diag_record.get('PersonAgeGroup_Code')) == 1)
					|| ((person_age > 19 || (person_age == 18 && person_age_month >= 6)) && Number(diag_record.get('PersonAgeGroup_Code')) == 2)
					|| ((person_age > 0 || (person_age == 0 && person_age_month >= 3)) && Number(diag_record.get('PersonAgeGroup_Code')) == 3)
					|| (person_age_day >= 28 && Number(diag_record.get('PersonAgeGroup_Code')) == 4)
					|| (person_age >= 4 && Number(diag_record.get('PersonAgeGroup_Code')) == 5)
				) {
					throw {warningMsg: langs('Выбранный диагноз не соответствует возрасту пациента'), fieldName: 'Diag_id'};
				}
			}
		} catch(err) {
			if (err.warningMsg) {
				if (false == err.warningMsg.toString().inlist(options.ignoreErrors)) {
					sw.swMsg.show({
						buttons: Ext.Msg.YESNO,
						fn: function(buttonId, text, obj) {
							me.formStatus = 'edit';
							if ('yes' == buttonId) {
								options.ignoreErrors.push(err.warningMsg);
								me.doSave(options);
							} else if (err.fieldName && base_form.findField(err.fieldName)) {
								base_form.findField(err.fieldName).markInvalid(err.warningMsg);
								base_form.findField(err.fieldName).focus(true);
							}
						},
						icon: Ext.Msg.WARNING,
						msg: '' + err.warningMsg + '<br>Продолжить сохранение?',
						title: langs('Предупреждение')
					});
					return false;
				}
			} else {
				me.formStatus = 'edit';
				sw.swMsg.alert(langs('Ошибка'), err.msg || err.toString());
				return false;
			}
		}

        var loadMask = new Ext.LoadMask(this.getEl(), {msg: LOAD_WAIT_SAVE});
		loadMask.show();

		params.EvnUslugaCommon_Price = EvnUslugaCommon_Price;
		params.EvnUslugaCommon_Summa = Number(EvnUslugaCommon_Price * base_form.findField('EvnUslugaCommon_Kolvo').getValue()).toFixed(2);
		params.ignoreParentEvnDateCheck = (!Ext.isEmpty(options.ignoreParentEvnDateCheck) && options.ignoreParentEvnDateCheck === 1) ? 1 : 0;
		params.ignorePaidCheck = this.ignorePaidCheck;


		if(me.isVisibleExecutionPanel || base_form.findField('UslugaExecutionType_id').getValue() == 1){
			params.UslugaExecutionType_id = base_form.findField('UslugaExecutionType_id').getValue();
			params.UslugaExecutionReason_id = base_form.findField('UslugaExecutionReason_id').getValue();
		}else{
			base_form.findField('UslugaExecutionReason_id').clearValue();
			base_form.findField('UslugaExecutionType_id').reset();
			params.UslugaExecutionType_id = null;
			params.UslugaExecutionReason_id = null;
		}

		var grid = this.EvnUslugaAttributeValueGrid.getGrid();

		var isAttributeValuePanelHidden = me.checkAttributeValuePanelHidden();
		if (isAttributeValuePanelHidden) {
			grid.getStore().each(function (rec) {
				rec.set('RecordStatus_Code', 3);
				rec.commit();
			});
		}

		if (getRegionNick() == 'perm' && !isAttributeValuePanelHidden) {
			var UslugaComplex_Code = base_form.findField('UslugaComplex_id').getFieldValue('UslugaComplex_Code');
			var requiredAttributeSign_Codes = [];
			switch (UslugaComplex_Code) {
				case 'A11.20.017.001':
					requiredAttributeSign_Codes = ['7'];
					break;
				case 'A11.20.017.002':
					requiredAttributeSign_Codes = ['7','8','9','10','11'];
					break;
				case 'A11.20.017.003':
					requiredAttributeSign_Codes = ['7','8','9','11'];
					break;
				case 'A11.20.030.001':
					requiredAttributeSign_Codes = ['10'];
					break;
				case 'A11.20.017':
					requiredAttributeSign_Codes = ['7','8','9','10'];
					break;
				case 'A04.20.001':
					requiredAttributeSign_Codes = ['12'];
					break;
				case 'A04.20.001.001':
					requiredAttributeSign_Codes = ['12'];
					break;
			}

			if (requiredAttributeSign_Codes && requiredAttributeSign_Codes.length > 0) {
				// проверяем обязательность заведённых атрибутов
				var AttributeSign_Codes = [];
				grid.getStore().each(function (rec) {
					if (rec.get('AttributeSign_Code')) {
						AttributeSign_Codes.push(rec.get('AttributeSign_Code').toString());
					}
				});

				var emptyAttributes = "";
				for (var k in requiredAttributeSign_Codes) {
					if (typeof requiredAttributeSign_Codes[k] != 'function' && !requiredAttributeSign_Codes[k].inlist(AttributeSign_Codes)) {
						emptyAttributes += "<br>";
						switch(requiredAttributeSign_Codes[k]) {
							case '7':
								emptyAttributes += "1 этап ЭКО (Стимуляция суперовуляции)";
								break;
							case '8':
								emptyAttributes += "2 этап ЭКО (Получение яйцеклетки)";
								break;
							case '9':
								emptyAttributes += "3 этап ЭКО (Экстракорпоральное оплодотворение и культивирование эмбрионов)";
								break;
							case '10':
								emptyAttributes += "4 этап ЭКО (Внутриматочное введение (перенос) эмбрионов)";
								break;
							case '11':
								emptyAttributes += "Криоконсервация";
								break;
							case '12':
								emptyAttributes += "Результат ЭКО";
								break;
						}
					}
				}

				if (emptyAttributes.length > 0) {
					loadMask.hide();
					me.formStatus = 'edit';
					sw.swMsg.alert(langs('Ошибка'), 'Для сохранения необходимо добавить обязательные признаки атрибутов  в раздел «Атрибуты»:' + emptyAttributes);
					return false;
				}
			}

			if (UslugaComplex_Code && UslugaComplex_Code.inlist(['A04.20.001', 'A04.20.001.001'])) {
				var ecoResultRec = null;
				grid.getStore().each(function (rec) {
					if (rec.get('AttributeSign_Code') == 12) { // Результат ЭКО
						ecoResultRec = rec;
					}
				});
				// если для УЗИ нет значения атрибута, то
				if (ecoResultRec && (!ecoResultRec.get('AttributeValueLoadParams') || ecoResultRec.get('AttributeValueLoadParams').length == 0 || ecoResultRec.get('AttributeValueLoadParams') == '[]')) {
					if (!options.ignoreUziResult) {
						if (!this.questionWin) {
							this.questionWin = new sw.Promed.BaseForm({
								width: 400,
								modal: true,
								title: langs('Отсутствует результат выполнения УЗИ'),
								resizable: false,
								closable: true,
								layout: 'form',
								autoHeight: true,
								initComponent: function () {
									var win = this;
									this.QuestionForm = new Ext.FormPanel({
										layout: 'form',
										autoHeight: true,
										items: [{
											xtype: 'label',
											style: '{' +
											'display: inline-block;' +
											' margin-left: 33px;' +
											' font-size: 12px;' +
											' margin-top: 10px;' +
											' margin-bottom: 10px;' +
											' font-weight: bold;' +
											'}',
											text: 'Внести данные о наступлении беременности?'
										}, {
											xtype: 'radiogroup',
											hideLabel: true,
											name: 'PregnancyResult',
											columns: 1,
											style: '{' +
											' margin-left: 33px;' +
											' margin-bottom: 10px;' +
											'}',
											items: [
												{
													boxLabel: 'Беременность подтверждена',
													name: 'ch',
													checked: true,
													value: 1
												},
												{boxLabel: 'Беременность не подтверждена', name: 'ch', value: 2}
											]
										}]
									});

									Ext.apply(this, {
										items: [
											this.QuestionForm
										],
										buttons: [{
											handler: function () {
												win.callback(1);
												win.hide();
											},
											text: 'Не указывать результат'
										}, '-', {
											handler: function () {
												if (win.QuestionForm.getForm().findField('PregnancyResult').items.items[0].checked) {
													win.callback(2);
												} else {
													win.callback(3);
												}
												win.hide();
											},
											text: 'Да'
										}]
									});

									sw.Promed.BaseForm.superclass.initComponent.apply(this, arguments);
								},
								show: function () {
									sw.Promed.BaseForm.superclass.show.apply(this, arguments);

									this.callback = arguments[0].callback;
									this.QuestionForm.getForm().reset();
								}
							});
						}

						loadMask.hide();
						me.formStatus = 'edit';
						this.questionWin.show({
							callback: function(ignoreUziResult) {
								options.ignoreUziResult = ignoreUziResult;
								switch(ignoreUziResult) {
									case 2:
										ecoResultRec.set('AttributeValueLoadParams', '[{"Attribute_id":"133","Attribute_SysNick":"EKOConfPregn","AttributeValue_Value":2,"AttributeValueType_SysNick":"ident","AttributeValue_TableName":"dbo.EvnUslugaCommon","AttributeVision_id":"646","RecordStatus_Code":0}]');
										ecoResultRec.set('AttributeValueSaveParams', '[{"Attribute_id":"133","Attribute_SysNick":"EKOConfPregn","AttributeValue_Value":2,"AttributeValueType_SysNick":"ident","AttributeValue_TableName":"dbo.EvnUslugaCommon","AttributeVision_id":"646","RecordStatus_Code":0}]');
										ecoResultRec.set('RecordStatus_Code', 2);
										break;
									case 3:
										ecoResultRec.set('AttributeValueLoadParams', '[{"Attribute_id":"133","Attribute_SysNick":"EKOConfPregn","AttributeValue_Value":1,"AttributeValueType_SysNick":"ident","AttributeValue_TableName":"dbo.EvnUslugaCommon","AttributeVision_id":"646","RecordStatus_Code":0}]');
										ecoResultRec.set('AttributeValueSaveParams', '[{"Attribute_id":"133","Attribute_SysNick":"EKOConfPregn","AttributeValue_Value":1,"AttributeValueType_SysNick":"ident","AttributeValue_TableName":"dbo.EvnUslugaCommon","AttributeVision_id":"646","RecordStatus_Code":0}]');
										ecoResultRec.set('RecordStatus_Code', 2);
										break;
								}
								ecoResultRec.commit();
								me.doSave();
							}
						});
						return false;
					}
				}
			}
		}


		if (getRegionNick().inlist(['adygeya', 'khak','pskov']) && !isAttributeValuePanelHidden) {
			var UslugaComplex_Code = base_form.findField('UslugaComplex_id').getFieldValue('UslugaComplex_Code');
			var requiredAttributeSign_Codes = [];
			switch (UslugaComplex_Code) {
				case 'A11.20.017':
					requiredAttributeSign_Codes = ['7','8','9','10'];
					break;
			}

			if (requiredAttributeSign_Codes && requiredAttributeSign_Codes.length > 0) {
				// проверяем обязательность заведённых атрибутов
				var AttributeSign_Codes = [];
				grid.getStore().each(function (rec) {
					if (rec.get('AttributeSign_Code')) {
						AttributeSign_Codes.push(rec.get('AttributeSign_Code').toString());
					}
				});

				var existOne = false;
				for (var k in requiredAttributeSign_Codes) {
					if (typeof requiredAttributeSign_Codes[k] != 'function' && requiredAttributeSign_Codes[k].inlist(AttributeSign_Codes)) {
						existOne = true;
					}
				}

				if (!existOne) {
					loadMask.hide();
					me.formStatus = 'edit';
					sw.swMsg.alert(langs('Ошибка'), 'Необходимо внести информацию об этапах проведения процедуры ЭКО');
					return false;
				}
			}
		}

		grid.getStore().clearFilter();
		grid.getStore().filterBy(function(rec) {
			return rec.get('RecordStatus_Code') !== null;
		});

		if ( grid.getStore().getCount() > 0 ) {
			var AttributeSignValueData = getStoreRecords(grid.getStore(), {
				convertDateFields: true,
				exceptionFields: [
					'AttributeSign_Code'
					,'AttributeSign_Name'
					,'AttributeValueLoadParams'
				]
			});

			params.AttributeSignValueData = Ext.util.JSON.encode(AttributeSignValueData);
		}
		grid.getStore().filterBy(function(rec) {
			return !(Number(rec.get('RecordStatus_Code')) == 3);
		});

		base_form.submit({
			failure: function(result_form, action) {
				this.formStatus = 'edit';
				loadMask.hide();

				if ( action.result ) {
					if (action.result.Alert_Msg) {
						sw.swMsg.show({
							buttons: Ext.Msg.YESNO,
							fn: function(buttonId, text, obj) {
								if ( buttonId == 'yes' ) {
									if (action.result.Error_Code == 109) {
										options.ignoreParentEvnDateCheck = 1;
									}

									this.doSave(options);
								}
							}.createDelegate(this),
							icon: Ext.MessageBox.QUESTION,
							msg: action.result.Alert_Msg,
							title: langs(' Продолжить сохранение?')
						});
					} else if ( action.result.Error_Msg ) {
						sw.swMsg.alert(langs('Ошибка'), action.result.Error_Msg);
					}
					else {
						sw.swMsg.alert(langs('Ошибка'), langs('При сохранении произошли ошибки [Тип ошибки: 3]'));
					}
				}
			}.createDelegate(this),
			params: params,
			success: function(result_form, action) {
				this.formStatus = 'edit';
				loadMask.hide();

				if ( action.result && action.result.EvnUslugaCommon_id > 0 ) {
					base_form.findField('EvnUslugaCommon_id').setValue(action.result.EvnUslugaCommon_id);
                    this.EvnXmlPanel.onEvnSave();

					this.FileUploadPanel.listParams = {Evn_id: action.result.EvnUslugaCommon_id};
					this.FileUploadPanel.saveChanges();

					if ( options && typeof options.openChildWindow == 'function' && this.action == 'add' ) {
						options.openChildWindow();
					}
					else {
						var data = new Object();
						var evn_usluga_common_set_time = base_form.findField('EvnUslugaCommon_setTime').getValue();

						if ( !evn_usluga_common_set_time || evn_usluga_common_set_time.length == 0 ) {
							evn_usluga_common_set_time = '00:00';
						}

						data.evnUslugaData = {
							'accessType': 'edit',
							'EvnClass_SysNick': 'EvnUslugaCommon',
							'EvnUsluga_Kolvo': base_form.findField('EvnUslugaCommon_Kolvo').getValue(),
							'EvnUsluga_id': base_form.findField('EvnUslugaCommon_id').getValue(),
							'EvnUsluga_Price': EvnUslugaCommon_Price,
							'EvnUsluga_setDate': base_form.findField('EvnUslugaCommon_setDate').getValue(),
							'EvnUsluga_setTime': evn_usluga_common_set_time,
							'EvnUsluga_Summa': Number(EvnUslugaCommon_Price * base_form.findField('EvnUslugaCommon_Kolvo').getValue()).toFixed(2),
							'PayType_id': base_form.findField('PayType_id').getValue(),
							'PayType_SysNick': pay_type_nick,
							'Usluga_Code': usluga_complex_code,
							'Usluga_Name': usluga_complex_name,
							'MedStaffFact_id': med_staff_fact_id,
							'MedPersonal_Fin': MedPersonal_Fin,
							UslugaMedType_id: base_form.findField('UslugaMedType_id').getValue()
						};

						if ( options.print == true ) {
							this.doPrint(true);

							if ( this.action == 'add' ) {
								this.callback(data);
								this.hide();
							}
						}
						else {
							var EvnXml_id = this.EvnXmlPanel.getEvnXmlId();
							if (!Ext.isEmpty(EvnXml_id)) {
								checkNeedSignature({
									EMDRegistry_ObjectName: 'EvnXml',
									EMDRegistry_ObjectID: EvnXml_id
								});
							}
							this.callback(data);
							this.hide();
						}
					}
				}
				else {
					this.callback();
					this.hide();					
				}
			}.createDelegate(this)
		});
	},
	draggable: true,
	enableEdit: function(enable) {
		var base_form = this.FormPanel.getForm();
		var form_fields = new Array(
			 'EvnUslugaCommon_Kolvo'
			,'EvnUslugaCommon_pid'
			,'EvnUslugaCommon_setDate'
			,'EvnUslugaCommon_setTime'
			,'PayType_id'
			,'UslugaCategory_id'
            ,'EvnPrescr_id'
            ,'UslugaComplex_id'
			,'UslugaComplexTariff_id'
			,'UslugaComplexTariff_UED'
			,'UslugaPlace_id'
			,'MedSpecOms_id'
			,'LpuSectionProfile_id'
			,'Diag_id'
			,'DiagSetClass_id',
			'UslugaMedType_id'
		);
		var i;

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
	height: 550,
	id: 'EvnUslugaEditWindow',
    doPrint: function(uslugaIsSaved){
        var params = {},
            _this = this;

		if ( _this.action.inlist([ 'add', 'edit' ]) && !uslugaIsSaved ) {
			_this.doSave({
				print: true
			});
			return false;
		}

		params.object =	'EvnUslugaCommon';
		params.object_id = 'EvnUslugaCommon_id';
		params.object_value	=  _this.FormPanel.getForm().findField('EvnUslugaCommon_id').getValue();
		params.view_section = 'main';

        Ext.Ajax.request({
            failure: function(response, options) {
                //loadMask.hide();
                sw.swMsg.alert(langs('Ошибка'), langs('При печати услуги произошла ошибка.'));
            },
            params: params,
            success: function(response, options) {

                _this.formStatus = 'edit';

                if ( response.responseText ) {
                    var result  = Ext.util.JSON.decode(response.responseText);
                    if (result.html)
                    {
                        var id_salt = Math.random(),
                            win_id = 'printEvent' + Math.floor(id_salt*10000),
                            win = window.open('', win_id);

                        win.document.write('<html><head><title>Печатная форма</title><link href="/css/emk.css?'+ id_salt +'" rel="stylesheet" type="text/css" /></head><body id="rightEmkPanelPrint">'+ result.html +'</body></html>');

                    } else {
                        sw.swMsg.show({
                            buttons: Ext.Msg.OK,
                            fn: function() {
                                _this.formStatus = 'edit';
                            }.createDelegate(this),
                            icon: Ext.Msg.WARNING,
                            msg: langs('Не удалось получить содержание услуги.'),
                            title: ERR_INVFIELDS_TIT
                        });
                        return false;
                    }
                } else {
                    sw.swMsg.show({
                        buttons: Ext.Msg.OK,
                        fn: function() {
                            _this.formStatus = 'edit';
                        }.createDelegate(this),
                        icon: Ext.Msg.WARNING,
                        msg: langs('Ошибка при печати услуги.'),
                        title: ERR_INVFIELDS_TIT
                    });
                    return false;
                }

                //loadMask.hide();


            }.createDelegate(this),
            url: '/?c=Template&m=getEvnForm'
        });
    },
	mesUslugaFilter: function(UslugaCategory_SysNick) {
		var base_form = this.FormPanel.getForm();
		base_form.findField('UslugaComplex_id').getStore().baseParams.MesFilter_Evn_id = null;
		base_form.findField('UslugaComplex_id').getStore().baseParams.MesFilter_Enable = 0;
	},
	initComponent: function() {
		var win = this;
		
		this.PersonInfo = new sw.Promed.PersonInformationPanelShort({
			id: 'EUComEF_PersonInformationFrame',
			region: 'north'
		});

        this.EvnXmlPanel = new sw.Promed.EvnXmlPanel({
            autoHeight: true,
            border: true,
            collapsible: true,
            style: "margin-bottom: 0.5em;",
            bodyStyle: 'padding-top: 0.5em;',
            id: 'EUComEF_TemplPanel',
            layout: 'form',
            title: langs('3. Специфика'),
            ownerWin: this,
            options: {
                XmlType_id: sw.Promed.EvnXml.EVN_USLUGA_PROTOCOL_TYPE_ID, // только протоколы услуг
                EvnClass_id: 22 // документы и шаблоны только категории EvnUslugaCommon
            },
			signEnabled: true,
            onAfterLoadData: function(panel){
                var bf = this.FormPanel.getForm();
                //bf.findField('XmlTemplate_id').setValue(panel.getXmlTemplateId());
                panel.expand();
                this.syncSize();
                this.doLayout();
            }.createDelegate(this),
			onAfterClearViewForm: function(panel){
                var bf = this.FormPanel.getForm();
                //bf.findField('XmlTemplate_id').setValue(null);
            }.createDelegate(this),
            // определяем метод, который должен создать посещение перед созданием документа с помощью указанного метода
            onBeforeCreate: function (panel, method, params) {
                if (!panel || !method || typeof panel[method] != 'function') {
                    return false;
                }
                var base_form = this.FormPanel.getForm();
                var evn_id_field = base_form.findField('EvnUslugaCommon_id');
                var evn_id = evn_id_field.getValue();
                if (evn_id && evn_id > 0) {
                    // услуга была создана ранее
                    // все базовые параметры уже должно быть установлены
					// а вот не факт
					panel.setBaseParams({
						userMedStaffFact: sw.Promed.MedStaffFactByUser.last,
						UslugaComplex_id: base_form.findField('UslugaComplex_id').getValue(),
						Server_id: base_form.findField('Server_id').getValue(),
						Evn_id: evn_id_field.getValue()
					});
                    panel[method](params);
                } else {
                    this.doSave({
                        openChildWindow: function() {
                            panel.setBaseParams({
                                userMedStaffFact: sw.Promed.MedStaffFactByUser.last,
                                UslugaComplex_id: base_form.findField('UslugaComplex_id').getValue(),
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
		
		this.ECGResult = new Ext.db.AdapterStore({
			autoLoad: true,
			dbFile: 'Promed.db',
			fields: [
				{ name: 'ECGResult_id', mapping: 'ECGResult_id' },
				{ name: 'ECGResult_Code', mapping: 'ECGResult_Code' },
				{ name: 'ECGResult_Name', mapping: 'ECGResult_Name' }
			],
			key: 'ECGResult_id',
			sortInfo: { field: 'ECGResult_Code' },
			tableName: 'ECGResult'
		});

		this.EvnUslugaAttributeValueGrid = new sw.Promed.AttributeSignValueGridPanel({
			tableName: 'dbo.EvnUslugaCommon',
			formMode: 'local',
			hideDates: true,
			denyDoubles: true,
			requireValueText: true,
			stringfields: [
				{name: 'AttributeSignValue_id', type: 'int', header: 'ID', key: true},
				{name: 'AttributeSignValue_TablePKey', type: 'int', hidden: true},
				{name: 'AttributeSign_id', type: 'int', hidden: true},
				{name: 'RecordStatus_Code', type: 'int', hidden: true},
				{name: 'AttributeValueLoadParams', type: 'string', hidden: true},	//Входящие параметры для редактирования значений атрибутов
				{name: 'AttributeValueSaveParams', type: 'string', hidden: true},	//Исходящие параметры для сохранения значений атрибутов
				{name: 'AttributeSign_Code', type: 'int', header: 'Код признака', width: 100},
				{name: 'AttributeSign_Name', type: 'string', header: 'Наименование признака', width: 150},
				{
					name: 'AttributeValue_ValueText', 
					header: 'Значение признака', 
					type: 'string', 
					id: 'autoexpand',
					renderer: function(value, metadata, record)
					{
						var data = JSON.parse(record.get('AttributeValueSaveParams') ||
							record.get('AttributeValueLoadParams') || '[]'),
							res = '',
							propName = '';

						data.forEach((item) => {
							if (res)
								res += '<br/>';
							propName = 'AttributeValue_ValueText';
							if (!item[propName]) {
								propName = 'AttributeValue_Value';
							}
							if (item[propName] && item.AttributeValueType_SysNick == 'date') {
								item[propName] = item[propName].split('T')[0].split('-').reverse().join('.');
							}
							res += (item[propName] || '');
						});

						return (res);
					}
				}
			],
			onRowSelect: function() {
				if (!this.getCount()) return false;
				this.getGrid().getStore().each(function(r) {
					var params = !Ext.isEmpty(r.get('AttributeValueSaveParams')) ? r.get('AttributeValueSaveParams') : r.get('AttributeValueLoadParams');
					if (params && params.length > 0) {
						params = Ext.util.JSON.decode(params)[0];
						if (params && params.Attribute_SysNick == 'EKGResult') {
							win.ECGResult.findBy(function (rec) {
								if (rec.get('ECGResult_id') == params.AttributeValue_Value) {
									r.set('AttributeValue_ValueText', rec.get('ECGResult_Name'));
									r.commit();
								}
							});
						}
					}
				});
			}
		});

		this.FileUploadPanel = new sw.Promed.FileUploadPanel({
			id: 'EUComEF_FileUploadPanel',
			win: this,
			commentTextfieldWidth: 120,
			uploadFieldColumnWidth: .6,
			commentTextColumnWidth: .35,
			width: 600,
			buttonAlign: 'left',
			buttonLeftMargin: 100,
			labelWidth: 150,
			folder: 'evnmedia/',
			style: 'background: transparent',
			dataUrl: '/?c=EvnMediaFiles&m=loadEvnMediaFilesListGrid',
			saveUrl: '/?c=EvnMediaFiles&m=uploadFile',
			saveChangesUrl: '/?c=EvnMediaFiles&m=saveChanges',
			deleteUrl: '/?c=EvnMediaFiles&m=deleteFile'
		});

		this.FilePanel = new Ext.Panel({
			title: '4. Файлы',
			id: 'EUComEF_FileTab',
			border: true,
			collapsible: true,
			autoHeight: true,
			titleCollapse: true,
			animCollapse: false,
			items: [
				this.FileUploadPanel
			]
		});

		this.AttributeValuePanel = new sw.Promed.Panel({
			border: true,
			collapsible: true,
			style: "margin-bottom: 0.5em;",
			height: 200,
			id: 'EUComEF_AttributeValuePanel',
			isLoaded: false,
			layout: 'border',
			listeners: {
				'expand': function(panel) {
					if ( panel.isLoaded === false ) {
						panel.isLoaded = true;
						win.EvnUslugaAttributeValueGrid.doLoad({tablePKey: win.FormPanel.getForm().findField('EvnUslugaCommon_id').getValue()});
					}
					panel.doLayout();
				}.createDelegate(this)
			},
			// style: 'margin-bottom: 0.5em;',
			title: '5. Атрибуты',
			items: [
				win.EvnUslugaAttributeValueGrid
			]
        });

        this.uslugaPanel = new sw.Promed.UslugaSelectPanel({
            id: win.getId() + 'UslugaSelectPanel',
            evnClassSysNick: 'EvnUslugaCommon',
            getBaseForm: function()
            {
                if (!this._baseForm) {
                    this._baseForm = win.FormPanel.getForm();
                }
                return this._baseForm;
            },
            isDisableUem: function()
            {
                return true;
            },
            isDisableUed: function()
            {
                return !win.allowRayTherapy();
            },
            isDisableTariff: function()
            {
                return false;
            },
            getEvnUslugaSummaField: function()
            {
                return this.getBaseForm().findField('EvnUslugaCommon_Summa');
            },
            getEvnUslugaUEDField: function()
            {
                return this.getBaseForm().findField('UslugaComplexTariff_UED');
            },
            getEvnUslugaTariffField: function()
            {
                return this.getBaseForm().findField('EvnUslugaCommon_Price');
            }
        });

		var uslugaCategoryParams = null;
		switch (getRegionNick()) {
			case 'kz':
				uslugaCategoryParams = {params: {where: "where UslugaCategory_SysNick in ('classmedus')"}};
				break;
			case 'kaluga':
				uslugaCategoryParams = {params: {where: "where UslugaCategory_SysNick in ('gost2011', 'lpusectiontree')"}};
				break;
		}

        this.FormPanel = new Ext.form.FormPanel({
			bodyBorder: false,
			border: false,
			frame: false,
			default_formLoadUrl: '/?c=EvnUsluga&m=loadEvnUslugaEditForm',
			default_url: '/?c=EvnUsluga&m=saveEvnUslugaCommon',
			id: 'EvnUslugaEditForm',
			labelAlign: 'right',
			labelWidth: 130,
			layout: 'form',
			reader: new Ext.data.JsonReader({
				success: Ext.emptyFn
			}, [
				{ name: 'accessType' },
                { name: 'EvnPrescr_id' },
				{ name: 'EvnDirection_id' },
				{ name: 'EvnUslugaCommon_id' },
				{ name: 'EvnUslugaCommon_Kolvo' },
				{ name: 'EvnUslugaCommon_pid' },
				{ name: 'EvnUslugaCommon_Price' },
				{ name: 'EvnUslugaCommon_rid' },
				{ name: 'EvnUslugaCommon_setDate' },
				{ name: 'EvnUslugaCommon_setTime' },
				{ name: 'EvnUslugaCommon_disDate' },
				{ name: 'EvnUslugaCommon_disTime' },
				{ name: 'EvnUslugaCommon_Summa' },
				{ name: 'Lpu_uid' },
				{ name: 'LpuSection_uid' },
				{ name: 'MedPersonal_id' },
				{ name: 'MedStaffFact_id' },
				{ name: 'Morbus_id' },
				{ name: 'Org_uid' },
				{ name: 'PayType_id' },
				{ name: 'Person_id' },
				{ name: 'PersonEvn_id' },
				{ name: 'Server_id' },
				{ name: 'UslugaComplex_id' },
				{ name: 'UslugaComplexTariff_id' },
				{ name: 'UslugaComplexTariff_UED' },
				{ name: 'DiagSetClass_id' },
				{ name: 'Diag_id' },
				{ name: 'UslugaPlace_id' },
				{ name: 'EvnUslugaOnkoBeam_disDate' },
				{ name: 'EvnUslugaOnkoBeam_disTime' },
				{ name: 'OnkoUslugaBeamIrradiationType_id' },
				{ name: 'OnkoUslugaBeamKindType_id' },
				{ name: 'OnkoUslugaBeamMethodType_id' },
				{ name: 'OnkoUslugaBeamRadioModifType_id' },
				{ name: 'OnkoUslugaBeamFocusType_id' },
				{ name: 'EvnUslugaOnkoBeam_TotalDoseTumor' },
				{ name: 'EvnUslugaOnkoBeam_TotalDoseRegZone' },
				{ name: 'OnkoUslugaBeamUnitType_id' },
				{ name: 'OnkoUslugaBeamUnitType_did' },
				{ name: 'EvnUslugaOnkoChem_disDate' },
				{ name: 'EvnUslugaOnkoChem_disTime' },
				{ name: 'OnkoUslugaChemKindType_id' },
				{ name: 'OnkoUslugaChemFocusType_id' },
				{ name: 'EvnUslugaOnkoChem_Dose' },
				{ name: 'EvnUslugaOnkoGormun_setDate' },
				{ name: 'EvnUslugaOnkoGormun_setTime' },
				{ name: 'EvnUslugaOnkoGormun_disDate' },
				{ name: 'EvnUslugaOnkoGormun_disTime' },
				{ name: 'EvnUslugaOnkoGormun_IsDrug' },
				{ name: 'EvnUslugaOnkoGormun_IsSurgical' },
				{ name: 'EvnUslugaOnkoGormun_IsBeam' },
				{ name: 'OnkoUslugaGormunFocusType_id' },
				{ name: 'OnkoDrug_id' },
				{ name: 'LpuSectionProfile_id' },
				{ name: 'MedSpecOms_id' },
				{ name: 'EvnUslugaCommon_pid_Name' },
				{ name: 'EvnUslugaOnkoGormun_Dose' },
				{ name: 'EvnUslugaCommon_IsMinusUsluga' },
				{ name: 'UslugaExecutionType_id' },
				{ name: 'UslugaExecutionReason_id' },
				{ name: 'UslugaMedType_id' }
			]),
			region: 'center',

			items: [{
				name: 'accessType',
				value: '',
				xtype: 'hidden'
            }, {
				name: 'EvnClass_SysNick',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'EvnUslugaCommon_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'EvnUslugaCommon_rid',
				value: 0,
				xtype: 'hidden'
			},  {
				name: 'Evn_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'EvnDirection_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'MedPersonal_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'Morbus_id',
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
			},
			new sw.Promed.Panel({
				autoHeight: true,
				// bodyStyle: 'padding: 0.5em;',
				border: true,
				collapsible: true,
				id: 'EUComEF_EvnUslugaPanel',
				layout: 'form',
				style: 'margin-bottom: 0.5em;',
				title: langs('1. Услуга'),

				items: [{
					allowBlank: false,
					displayField: 'Evn_Name',
					editable: false,
					enableKeyEvents: true,
					fieldLabel: langs('Отделение (посещение)'),
					hiddenName: 'EvnUslugaCommon_pid',
					lastQuery: '',
					listeners: {
						'change': function(combo, newValue, oldValue) {
							var base_form = this.FormPanel.getForm();
							var record = combo.getStore().getById(newValue);
							var index;
							
							this.mesUslugaFilter(base_form.findField('UslugaCategory_id').getFieldValue('UslugaCategory_SysNick'));
							
							base_form.findField('UslugaComplex_id').getStore().baseParams.EvnUsluga_pid = newValue;

							this.isPriem = false;
							
							if ( record ) {
								var MedStaffFact_id = record.get('MedStaffFact_id');
								var lpu_section_id = record.get('LpuSection_id');
								var lpu_section_pid;

								base_form.findField('EvnUslugaCommon_setDate').setValue(record.get('Evn_setDate'));
								base_form.findField('EvnUslugaCommon_setDate').fireEvent('change', base_form.findField('EvnUslugaCommon_setDate'), record.get('Evn_setDate'), 0);
								base_form.findField('EvnUslugaCommon_setTime').setValue(record.get('Evn_setTime'));
								base_form.findField('EvnUslugaCommon_setTime').fireEvent('change', base_form.findField('EvnUslugaCommon_setTime'), record.get('Evn_setTime'), 0);
								base_form.findField('UslugaPlace_id').setValue(1);
								base_form.findField('UslugaPlace_id').fireEvent('change', base_form.findField('UslugaPlace_id'), 1, 0);

								if ( getRegionNick() == 'ufa' ) {
									if (!Ext.isEmpty(record.get('Evn_setTime'))) {
                                        base_form.findField('EvnUslugaCommon_setTime').setValue(record.get('Evn_setTime'));
                                    }
								}

								var disCodes = [];

								if (!Ext.isEmpty(record.get('UslugaComplex_Code'))) {
									disCodes.push(record.get('UslugaComplex_Code'));
								}

								/*if ( getRegionNick() == 'perm' ) {
									if (
										this.parentClass.inlist(['EvnPL','EvnVizit'])
										&& record.get('ServiceType_SysNick').inlist(['home','neotl'])
										&& record.get('Evn_setDate') >= new Date('2015-01-01')
									) {
										disCodes.push('B04.069.333');
									}
								}*/

								if (disCodes.length > 0) {
									base_form.findField('UslugaComplex_id').getStore().baseParams.disallowedUslugaComplexCodeList = Ext.util.JSON.encode(disCodes);
								} else {
									base_form.findField('UslugaComplex_id').getStore().baseParams.disallowedUslugaComplexCodeList = null;
								}

								if ( getRegionNick() == 'ekb' && this.parentClass.inlist(['EvnVizit','EvnSection','EvnPL','EvnPS']) ) {
									if (Ext.isEmpty(base_form.findField('Diag_id').getValue())) {
										var diag_id = record.get('Diag_id');

										base_form.findField('Diag_id').getStore().load({
											callback: function() {
												base_form.findField('Diag_id').setValue(diag_id);
												base_form.findField('Diag_id').onChange(base_form.findField('Diag_id'), base_form.findField('Diag_id').getValue());
											},
											params: {where: "where DiagLevel_id = 4 and Diag_id = " + diag_id}
										});
									} else {
										base_form.findField('Diag_id').onChange(base_form.findField('Diag_id'), base_form.findField('Diag_id').getValue());
									}
								}

								index = base_form.findField('LpuSection_uid').getStore().findBy(function(rec) {
									return (rec.get('LpuSection_id') == record.get('LpuSection_id'));
								});

								if ( index >= 0 ) {
									base_form.findField('LpuSection_uid').fireEvent('beforeselect', base_form.findField('LpuSection_uid'), base_form.findField('LpuSection_uid').getStore().getAt(index));
									base_form.findField('LpuSection_uid').setValue(record.get('LpuSection_id'));
									base_form.findField('LpuSection_uid').fireEvent('change', base_form.findField('LpuSection_uid'), base_form.findField('LpuSection_uid').getValue());
									lpu_section_pid = base_form.findField('LpuSection_uid').getStore().getAt(index).get('LpuSection_pid');
								}

								if ( base_form.findField('LpuSection_uid').getStore().getById(lpu_section_id) ) {
									base_form.findField('LpuSection_uid').setValue(lpu_section_id);
									this.isPriem = (base_form.findField('LpuSection_uid').getFieldValue('LpuSectionProfile_SysNick') == 'priem');
									base_form.findField('LpuSection_uid').fireEvent('change', base_form.findField('LpuSection_uid'), lpu_section_id);
									lpu_section_pid = base_form.findField('LpuSection_uid').getStore().getById(lpu_section_id).get('LpuSection_pid');
								}

								index = base_form.findField('MedStaffFact_id').getStore().findBy(function(rec) {
									return (rec.get('MedStaffFact_id') == MedStaffFact_id);
								});

								if ( index >= 0 ) {
									base_form.findField('MedStaffFact_id').setValue(MedStaffFact_id);
									this.setDefaultLpuSectionProfile();
								}

								this.checkIsEco();
							}
						}.createDelegate(this),
						'keydown': function (inp, e) {
							if ( e.shiftKey == true && e.getKey() == Ext.EventObject.TAB ) {
								e.stopEvent();
								this.buttons[this.buttons.length - 1].focus();
							}
							else if (e.getKey() == Ext.EventObject.DELETE) {
								e.stopEvent();
								inp.clearValue();
							}
						}.createDelegate(this)
					},
					listWidth: 600,
					mode: 'local',
					store: new Ext.data.JsonStore({
						autoLoad: false,
						fields: [
							{ name: 'Evn_id', type: 'int' },
							{ name: 'MedStaffFact_id', type: 'int' },
							{ name: 'LpuSection_id', type: 'int' },
							{ name: 'MedPersonal_id', type: 'int' },
							{ name: 'Evn_Name', type: 'string' },
							{ name: 'Evn_setDate', type: 'date', dateFormat: 'd.m.Y' },
							{ name: 'Evn_setTime', type: 'string' },
							{ name: 'LpuUnitType_SysNick', type: 'string' },
							{ name: 'ServiceType_SysNick', type: 'string' },
							{ name: 'VizitType_SysNick', type: 'string' },
							{ name: 'UslugaComplex_Code', type: 'string' },
							{ name: 'Diag_id', type: 'int' }
						],
						id: 'Evn_id'
					}),
					tabIndex: TABINDEX_EUCOMEF + 1,
					tpl: new Ext.XTemplate('<tpl for="."><div class="x-combo-list-item">', '{Evn_Name}&nbsp;', '</div></tpl>'),
					triggerAction: 'all',
					valueField: 'Evn_id',
					width: 500,
					xtype: 'swbaselocalcombo'
				}, {
					border: false,
					layout: 'column',
					items: [{
						border: false,
						layout: 'form',
						labelWidth: 180,
						items: [{
							allowBlank: false,
							fieldLabel: langs('Дата начала выполнения'),
							format: 'd.m.Y',
							listeners: {
								'change': function(field, newValue, oldValue) {
									if ( blockedDateAfterPersonDeath('personpanelid', 'EUComEF_PersonInformationFrame', field, newValue, oldValue) )
										return false;
									
									win.setLpuSectionAndMedStaffFactFilter();
									win.setDisDT();
								}.createDelegate(this),
								'keydown': function (inp, e) {
									if ( e.shiftKey == true && e.getKey() == Ext.EventObject.TAB && this.FormPanel.getForm().findField('EvnUslugaCommon_pid').disabled ) {
										e.stopEvent();
										this.buttons[this.buttons.length - 1].focus();
									}
								}.createDelegate(this)
							},
							name: 'EvnUslugaCommon_setDate',
							plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
							tabIndex: TABINDEX_EUCOMEF + 2,
							width: 100,
							xtype: 'swdatefield'
						}]
					}, {
						border: false,
						layout: 'form',
						labelWidth: 50,
						items: [{
							fieldLabel: langs('Время'),
							listeners: {
								'change': function() {
									this.setDisDT();
								}.createDelegate(this),
								'keydown': function (inp, e) {
									if ( e.getKey() == Ext.EventObject.F4 ) {
										e.stopEvent();
										inp.onTriggerClick();
									}
								}
							},
							name: 'EvnUslugaCommon_setTime',
							onTriggerClick: function() {
								var base_form = this.FormPanel.getForm();

								var time_field = base_form.findField('EvnUslugaCommon_setTime');

								if ( time_field.disabled ) {
									return false;
								}

								setCurrentDateTime({
									callback: function() {
										this.setDisDT();
									}.createDelegate(this),
									dateField: base_form.findField('EvnUslugaCommon_setDate'),
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
							tabIndex: TABINDEX_EUCOMEF + 3,
							validateOnBlur: false,
							width: 60,
							xtype: 'swtimefield'
						}]
					}, {
						layout: 'form',
						style: 'padding-left: 45px',
						border: false,
						items: [{
							xtype: 'button',
							id: 'EUComEF_ToggleVisibleDisDTBtn',
							text: langs('Уточнить период выполнения'),
							handler: function() {
								this.toggleVisibleDisDTPanel();
							}.createDelegate(this)
						}]
					}, {
						border: false,
						layout: 'column',
						id: 'EUComEF_EvnUslugaDisDTPanel',
						items: [{
							border: false,
							layout: 'form',
							labelWidth: 180,
							items: [{
								fieldLabel: langs('Дата окончания выполнения'),
								format: 'd.m.Y',
								name: 'EvnUslugaCommon_disDate',
								plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
								tabIndex: TABINDEX_EUCOMEF + 3,
								width: 100,
								xtype: 'swdatefield'
							}]
						}, {
							border: false,
							layout: 'form',
							labelWidth: 50,
							items: [{
								fieldLabel: langs('Время'),
								listeners: {
									'keydown': function (inp, e) {
										if ( e.getKey() == Ext.EventObject.F4 ) {
											e.stopEvent();
											inp.onTriggerClick();
										}
									}
								},
								name: 'EvnUslugaCommon_disTime',
								onTriggerClick: function() {
									var base_form = this.FormPanel.getForm();

									var time_field = base_form.findField('EvnUslugaCommon_disTime');

									if ( time_field.disabled ) {
										return false;
									}

									setCurrentDateTime({
										dateField: base_form.findField('EvnUslugaCommon_disDate'),
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
								tabIndex: TABINDEX_EUCOMEF + 4,
								validateOnBlur: false,
								width: 60,
								xtype: 'swtimefield'
							}]
						}, {
							layout: 'form',
							border: false,
							items: [{
								xtype: 'button',
								id: 'EUComEF_DTCopyBtn',
								text: '=',
								handler: function() {
									var base_form = this.FormPanel.getForm();

									base_form.findField('EvnUslugaCommon_disDate').setValue(base_form.findField('EvnUslugaCommon_setDate').getValue());
									base_form.findField('EvnUslugaCommon_disTime').setValue(base_form.findField('EvnUslugaCommon_setTime').getValue());
								}.createDelegate(this)
							}]
						}]
					}
					]
				},{
					layout: 'form',
					style: 'padding-left: 20px',
					border: false,
					hidden: false,
					id: 'EUComEF_ToggleVisibleExecutionPanel',
					items: [{
						xtype: 'button',
						id: 'EUComEF_ToggleVisibleExecutionPanelBtn',
						text: langs('Уточнить объем выполнения'),
						handler: function() {
							var base_form = win.FormPanel.getForm();
							if(!base_form.findField('UslugaExecutionType_id').getValue())
								base_form.findField('UslugaExecutionType_id').setValue(1);
							this.toggleVisibleExecutionPanel();

						}.createDelegate(this)
					}]
				},{
					border: false,
					layout: 'form',
					id: 'EUComEF_EvnUslugaExecutionPanel',
					items:[
						{
							xtype: 'uslugaexecutiontyperadiogroup',
							fieldLabel: langs('Объём выполнения'),
							name: 'UslugaExecutionType_id',
							listeners: {
								change: function(cmp, item){
									var base_form = win.FormPanel.getForm();
									if(item){
										base_form.findField('UslugaExecutionReason_id').setAllowBlank(item.value == 1);
										base_form.findField('UslugaExecutionReason_id').setDisabled(item.value == 1);
										if(item.value == 1){
											base_form.findField('UslugaExecutionReason_id').reset()
										}
									}

								}
							}
						},
						{
							comboSubject: 'UslugaExecutionReason',
							xtype: 'swcommonsprcombo',
							hiddenName: 'UslugaExecutionReason_id',
							valueField: 'UslugaExecutionReason_id',
							showCodefield: false,
							disabled: true,
							width: 400,
							fieldLabel: langs('Причина частичного выполнения (невыполнения)'),
							tpl: new Ext.XTemplate('<tpl for="."><div class="x-combo-list-item">{UslugaExecutionReason_Name}&nbsp;</div></tpl>')
						}
					]
				}, {
					autoHeight: true,
					style: 'padding: 2px 0px 0px 0px;',
					xtype: 'fieldset',
					items: [{
						allowBlank: false,
						comboSubject: 'UslugaPlace',
						fieldLabel: langs('Место выполнения'),
						hiddenName: 'UslugaPlace_id',
						lastQuery: '',
						listeners: {
							'change': function(combo, newValue, oldValue) {
								var base_form = this.FormPanel.getForm();

								var record = combo.getStore().getById(newValue);
								var lpu_combo = base_form.findField('Lpu_uid');
								var lpu_section_combo = base_form.findField('LpuSection_uid');
								var med_personal_combo = base_form.findField('MedStaffFact_id');
								var org_combo = base_form.findField('Org_uid');
                                if (!base_form.findField('EvnPrescr_id').getValue()) {
                                    lpu_combo.clearValue();
                                    lpu_section_combo.clearValue();
                                    med_personal_combo.clearValue();
                                    org_combo.clearValue();
                                }
								lpu_combo.setAllowBlank(true);
								lpu_section_combo.setAllowBlank(true);
								med_personal_combo.setAllowBlank(true);
								org_combo.setAllowBlank(true);

								// Вызываем событие change для списка отделений, чтобы сбросить фильтр услуг по коду профиля отлделения (Уфа)
								// и очистить справочник услуг, указанных для отделения в структуре (Пермь)
								lpu_section_combo.fireEvent('change', lpu_section_combo, null);
								

								if ( !record ) {
									lpu_combo.disable();
									lpu_section_combo.disable();
									med_personal_combo.disable();
									org_combo.disable();
								}
								else {
									switch ( parseInt(record.get('UslugaPlace_Code')) ) {
										case 1:
											lpu_combo.disable();
											lpu_section_combo.enable();
											med_personal_combo.enable();
											org_combo.disable();
											lpu_section_combo.setAllowBlank(false);
											med_personal_combo.setAllowBlank(false);
										break;

										case 2:
											lpu_combo.enable();
											if (getRegionNick().inlist(['ekb', 'astra'])) {
												lpu_section_combo.enable();
												med_personal_combo.enable();
											} else {
												lpu_section_combo.disable();
												med_personal_combo.disable();
											}
											org_combo.disable();
											lpu_combo.setAllowBlank(false);
										break;

										case 3:
											lpu_combo.disable();
											lpu_section_combo.disable();
											med_personal_combo.disable();
											org_combo.enable();
											org_combo.setAllowBlank(false);
										break;
									}
								}
								this.setLpuSectionAndMedStaffFactFilter();
								var code = (record && record.get('UslugaPlace_Code')) || null;
								base_form.findField('MedSpecOms_id').onChangeUslugaPlaceField(combo, code);
								base_form.findField('LpuSectionProfile_id').onChangeUslugaPlaceField(combo, code);
							}.createDelegate(this),
							'render': function(combo) {
								combo.getStore().load();
							}
						},
						tabIndex: TABINDEX_EUCOMEF + 4,
						width: 500,
						xtype: 'swcommonsprcombo'
					}, {
						hiddenName: 'LpuSection_uid',
						id: 'EUComEF_LpuSectionCombo',
						lastQuery: '',
						linkedElements: [
							'EUComEF_MedPersonalCombo'
						],
						linkedElementParams: {
							additionalFilterFn: checkSlaveRecordForLpuSectionService,
							ignoreFilter: false
						},
						listeners: {
							'beforeselect': function(combo, record, index) {
								var base_form = this.FormPanel.getForm();

								if (
									typeof record == 'object'
									&& Ext.isEmpty(record.get('LpuSectionServiceList'))
									&& (
										record.get('LpuSectionProfile_SysNick') == 'priem'
										|| (getRegionNick() == 'kareliya' && record.get('LpuSectionProfile_Code') == '160')
									)
								) {
									combo.linkedElementParams.ignoreFilter = true;
								}
								else {
									combo.linkedElementParams.ignoreFilter = false;
								}
							}.createDelegate(this)
						},
						tabIndex: TABINDEX_EUCOMEF + 5,
						width: 500,
						xtype: 'swlpusectionglobalcombo'
					}, {
						hiddenName: 'LpuSectionProfile_id',
						listeners: {
							'change': function (combo, newValue) {
								var base_form = win.FormPanel.getForm();
								base_form.findField('UslugaComplex_id').getStore().baseParams.LpuSectionProfile_id = newValue;
							}
						},
						//hidden: true,
						lastQuery: '',
						tabIndex: TABINDEX_EUCOMEF + 6,
						width: 500,
						xtype: 'swlpusectionprofilewithfedcombo'
					}, {
						displayField: 'Org_Name',
						editable: false,
						enableKeyEvents: true,
						fieldLabel: langs('ЛПУ'),
						hiddenName: 'Lpu_uid',
						listeners: {
							'keydown': function(inp, e) {
								if ( inp.disabled ) {
									return;
								}

								if ( e.F4 == e.getKey() ) {
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

									inp.onTrigger1Click();

									return false;
								}
							},
							'keyup': function(inp, e) {
								if ( e.F4 == e.getKey() ) {
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

									return false;
								}
							}
						},
						mode: 'local',
						onTrigger1Click: function() {
							var base_form = this.FormPanel.getForm();

							var combo = base_form.findField('Lpu_uid');

							if ( combo.disabled ) {
								return;
							}

							var usluga_place_combo = base_form.findField('UslugaPlace_id');
							var record = usluga_place_combo.getStore().getById(usluga_place_combo.getValue());

							if ( !record ) {
								return false;
							}

							var org_type = 'lpu';

							getWnd('swOrgSearchWindow').show({
                                onlyFromDictionary: true,
								onSelect: function(org_data) {
									if ( org_data.Lpu_id > 0 ) {
										combo.getStore().loadData([{
                                            Org_id: org_data.Org_id,
                                            Lpu_id: org_data.Lpu_id,
											Org_Name: org_data.Org_Name
										}]);

										combo.setValue(org_data.Lpu_id);
										win.setLpuSectionAndMedStaffFactFilter();

										getWnd('swOrgSearchWindow').hide();
									}
								},
								onClose: function() {
									combo.focus(true, 200)
								},
								object: org_type
							});
						}.createDelegate(this),
						store: new Ext.data.JsonStore({
							autoLoad: false,
							fields: [
                                { name: 'Lpu_id', type: 'int' },
                                { name: 'Org_id', type: 'int' },
								{ name: 'Org_Name', type: 'string' }
							],
							key: 'Lpu_id',
							sortInfo: {
								field: 'Org_Name'
							},
							url: C_ORG_LIST
						}),
						tabIndex: TABINDEX_EUCOMEF + 7,
						tpl: new Ext.XTemplate('<tpl for="."><div class="x-combo-list-item">', '{Org_Name}', '</div></tpl>'),
						trigger1Class: 'x-form-search-trigger',
						triggerAction: 'none',
						valueField: 'Lpu_id',
						width: 500,
						xtype: 'swbaseremotecombo'
					}, {
						displayField: 'Org_Name',
						editable: false,
						enableKeyEvents: true,
						fieldLabel: langs('Другая организация'),
						hiddenName: 'Org_uid',
						listeners: {
							'keydown': function(inp, e) {
								if ( inp.disabled ) {
									return;
								}

								if ( e.F4 == e.getKey() ) {
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

									inp.onTrigger1Click();

									return false;
								}
							},
							'keyup': function(inp, e) {
								if ( e.F4 == e.getKey() ) {
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

									return false;
								}
							}
						},
						mode: 'local',
						onTrigger1Click: function() {
							var base_form = this.FormPanel.getForm();
							var combo = base_form.findField('Org_uid');

							if ( combo.disabled ) {
								return;
							}

							var usluga_place_combo = base_form.findField('UslugaPlace_id');
							var usluga_place_id = usluga_place_combo.getValue();
							var record = usluga_place_combo.getStore().getById(usluga_place_id);

							if ( !record ) {
								return false;
							}

							var org_type = 'org';

							getWnd('swOrgSearchWindow').show({
								onSelect: function(org_data) {
									if ( org_data.Org_id > 0 ) {
										combo.getStore().loadData([{
											Org_id: org_data.Org_id,
											Org_Name: org_data.Org_Name
										}]);

										combo.setValue(org_data.Org_id);

										getWnd('swOrgSearchWindow').hide();
									}
								},
								onClose: function() {
									combo.focus(true, 200)
								},
								object: org_type
							});
						}.createDelegate(this),
						store: new Ext.data.JsonStore({
							autoLoad: false,
							fields: [
								{ name: 'Org_id', type: 'int' },
								{ name: 'Org_Name', type: 'string' }
							],
							key: 'Org_id',
							sortInfo: {
								field: 'Org_Name'
							},
							url: C_ORG_LIST
						}),
						tabIndex: TABINDEX_EUCOMEF + 7,
						tpl: new Ext.XTemplate('<tpl for="."><div class="x-combo-list-item">', '{Org_Name}', '</div></tpl>'),
						trigger1Class: 'x-form-search-trigger',
						triggerAction: 'none',
						valueField: 'Org_id',
						width: 500,
						xtype: 'swbaseremotecombo'
					}, {
						hiddenName: 'MedSpecOms_id',
						hidden: true,
						lastQuery: '',
						tabIndex: TABINDEX_EUCOMEF + 8,
						width: 500,
						xtype: 'swmedspecomswithfedcombo'
					}, {
						name: 'EvnUslugaCommon_IsMinusUsluga',
						fieldLabel: 'Вычесть стоимость услуги',
						tabIndex: TABINDEX_EUCOMEF + 8,
						xtype: 'swcheckbox'
					}]
				}, {
					autoHeight: true,
					style: 'padding: 2px 0px 0px 0px;',
					title: langs('Врач, выполнивший услугу'),
					xtype: 'fieldset',

					items: [{
						fieldLabel: langs('Код и ФИО врача'),
						hiddenName: 'MedStaffFact_id',
						id: 'EUComEF_MedPersonalCombo',
						lastQuery: '',
						listWidth: 750,
						parentElementId: 'EUComEF_LpuSectionCombo',
						tabIndex: TABINDEX_EUCOMEF + 8,
						width: 500,
						xtype: 'swmedstafffactglobalcombo'
					}]
				}, {
					allowBlank: false,
					hiddenName: 'PayType_id',
					listeners: {
						'select': function (combo, record) {
							var base_form = win.FormPanel.getForm();
							if (getRegionNick() == 'buryatiya') {
								var usluga_category_combo = base_form.findField('UslugaCategory_id');
								usluga_category_combo.lastQuery = "";
								usluga_category_combo.getStore().clearFilter();
								if (record && record.get('PayType_SysNick') == 'oms'){
									usluga_category_combo.setFieldValue('UslugaCategory_SysNick', 'tfoms');
									usluga_category_combo.fireEvent('select', usluga_category_combo, usluga_category_combo.getStore().getAt(usluga_category_combo.getStore().findBy(function(rec) {
										return (rec.get('UslugaCategory_SysNick') == 'tfoms');
									})));
								} else {
									usluga_category_combo.clearValue();
									usluga_category_combo.fireEvent('select', usluga_category_combo, null);
								}
							}
							var sysnick = (record && record.get('PayType_SysNick')) || null;
							base_form.findField('MedSpecOms_id').onChangePayTypeField(combo, sysnick);
							base_form.findField('LpuSectionProfile_id').onChangePayTypeField(combo, sysnick);
							win.setUslugaComplexPartitionCodeList(sysnick);
						},
						'change': function (combo, newValue, oldValue) {
							var base_form = win.FormPanel.getForm();
							if (getRegionNick() == 'perm' || (getRegionNick() == 'ekb' && base_form.findField('filterUslugaComplex').getValue())) {
								base_form.findField('UslugaComplex_id').setPayType(newValue);
							}
							this.loadUslugaComplexTariffCombo();
							return true;
						}.createDelegate(this)
					},
					tabIndex: TABINDEX_EUCOMEF + 9,
					width: 250,
					fieldLabel: getRegionNick() == 'kz' ? 'Источник финансирования' : 'Вид оплаты',
					xtype: 'swpaytypecombo'
				}, {
                    hiddenName: 'EvnPrescr_id',
                    listeners: {
                        'change': function (combo, newValue) {
                            if (!newValue && 'add' == this.action && combo.getStore().baseParams.EvnPrescr_pid > 0) {
                                combo.uslugaCombo.getStore().baseParams.withoutPackage = 0;
                            } else {
                                combo.uslugaCombo.getStore().baseParams.withoutPackage = 1;
                            }
                            combo.applyChanges(newValue);
                        }.createDelegate(this)
                    },
                    tabIndex: TABINDEX_EUCOMEF + 9,
                    width: 500,
                    listWidth: 600,
                    xtype: 'swevnprescrcombo'
                }, {
					border: false,
					hidden: getRegionNick().inlist([ 'ekb' ]),
					layout: 'form',
					xtype: 'panel',
					items: [{
						allowBlank: getRegionNick().inlist([ 'ekb' ]),
						fieldLabel: langs('Категория услуги'),
						hiddenName: 'UslugaCategory_id',
						listeners: {
							'select': function (combo, record) {
								if ( getRegionNick() == 'ekb' ) {
									return false;
								}

								var base_form = this.FormPanel.getForm();

								base_form.findField('UslugaComplex_id').clearValue();
								base_form.findField('UslugaComplex_id').getStore().removeAll();

								this.toggleVisibleExecutionBtnPanel();

								if ( !record ) {
									base_form.findField('UslugaComplex_id').setUslugaCategoryList();
									return false;
								}

								if (getRegionNick() != 'ekb') {
									// не влияет на выбор услуг для Екб
									base_form.findField('UslugaComplex_id').setUslugaCategoryList([record.get('UslugaCategory_SysNick')]);
								}
								this.mesUslugaFilter(record.get('UslugaCategory_SysNick'));

								return true;
							}.createDelegate(this)
						},
						listWidth: 400,
						loadParams: uslugaCategoryParams,
						tabIndex: TABINDEX_EUCOMEF + 10,
						width: 250,
						xtype: 'swuslugacategorycombo'
					}]
				}, {
					border: false,
					hidden: !getRegionNick().inlist([ 'ekb' ]),
					layout: 'form',
					xtype: 'panel',
					items: [{
						name: 'filterUslugaComplex',
						fieldLabel: langs('Фильтровать услуги'),
						listeners: {
							'check': function(checkbox) {
								var base_form = win.FormPanel.getForm();
								
								if (checkbox.getValue()) {
									base_form.findField('UslugaComplex_id').setPersonId(base_form.findField('Person_id').getValue());
									base_form.findField('UslugaComplex_id').setPayType(base_form.findField('PayType_id').getValue());
									base_form.findField('UslugaComplex_id').getStore().baseParams.MedPersonal_id = base_form.findField('MedStaffFact_id').getFieldValue('MedPersonal_id');
								} else {
									base_form.findField('UslugaComplex_id').setPersonId(null);
									base_form.findField('UslugaComplex_id').setPayType(null);
									base_form.findField('UslugaComplex_id').getStore().baseParams.MedPersonal_id = null;
								}
								
								base_form.findField('UslugaComplex_id').clearValue();
								base_form.findField('UslugaComplex_id').lastQuery = 'This query sample that is not will never appear';
								base_form.findField('UslugaComplex_id').getStore().removeAll();
								base_form.findField('UslugaComplex_id').getStore().baseParams.query = '';

								win.toggleVisibleExecutionBtnPanel();
							}
						},
						xtype: 'checkbox'
					}]
				}, {
					allowBlank: false,
					fieldLabel: langs('Услуга'),
					hiddenName: 'UslugaComplex_id',
					to: 'EvnUslugaCommon',
					listeners: {
						'change': function (combo, newValue, oldValue) {
							this.toggleVisibleExecutionBtnPanel();
							this.loadUslugaComplexTariffCombo();
                            var base_form = this.findById('EvnUslugaEditForm').getForm(),
                                prescr_combo = base_form.findField('EvnPrescr_id');
                            prescr_combo.onChangedUslugaCombo(this.action, combo.getStore().getById(newValue));

							this.checkAttributeValuePanelHidden();
/*
							var index = combo.getStore().findBy(function(rec) {
								return (rec.get(combo.valueField) == newValue);
							});

							combo.fireEvent('select', combo, combo.getStore().getAt(index));
*/
							return true;
						}.createDelegate(this),
						'select': function (combo, record) {
							if (getRegionNick() == 'perm') {
								var base_form = this.findById('EvnUslugaEditForm').getForm();
								base_form.findField('UslugaComplex_id').getStore().baseParams.PayType_id = base_form.findField('PayType_id').getValue();
								return true;
							}
							return true;
						}.createDelegate(this)
					},
					listWidth: 700,
					tabIndex: TABINDEX_EUCOMEF + 11,
					width: 500,
					xtype: 'swuslugacomplexnewcombo'
				}, {
					comboSubject: 'UslugaMedType',
					enableKeyEvents: true,
					hidden: getRegionNick() !== 'kz',
					allowBlank: getRegionNick() !== 'kz',
					fieldLabel: langs('Вид услуги'),
					hiddenName: 'UslugaMedType_id',
					lastQuery: '',
					tabIndex: TABINDEX_EUCOMEF + 12,
					typeCode: 'int',
					width: 450,
					xtype: 'swcommonsprcombo'
				}, {
					allowBlank: true,
					hiddenName: 'UslugaComplexTariff_id',
					listeners: {
						'change': function (combo, newValue, oldValue) {
							var index = combo.getStore().findBy(function(rec) {
								return (rec.get(combo.valueField) == newValue);
							});

							combo.fireEvent('select', combo, combo.getStore().getAt(index));

							return true;
						}.createDelegate(this),
						'select': function (combo, record) {
							var base_form = this.findById('EvnUslugaEditForm').getForm();

							if ( record ) {
								if ( !Ext.isEmpty(record.get(combo.valueField)) ) {
									combo.setRawValue(record.get('UslugaComplexTariff_Code') + ". " + record.get('UslugaComplexTariff_Name'));
								}

								base_form.findField('EvnUslugaCommon_Price').setValue(record.get('UslugaComplexTariff_Tariff'));

								if ( this.allowRayTherapy() == true ) {
									base_form.findField('UslugaComplexTariff_UED').setValue(record.get('UslugaComplexTariff_UED'));
								}
							}
							else {
								base_form.findField('EvnUslugaCommon_Price').setValue('');
							}

							base_form.findField('EvnUslugaCommon_Kolvo').fireEvent('change', base_form.findField('EvnUslugaCommon_Kolvo'), base_form.findField('EvnUslugaCommon_Kolvo').getValue());

							return true;
						}.createDelegate(this)
					},
					listWidth: 600,
					tabIndex: TABINDEX_EUCOMEF + 15,
					width: 500,
					xtype: 'swuslugacomplextariffcombo'
				}, {
					allowBlank: (getRegionNick() != 'ekb'),
					hidden: (getRegionNick() != 'ekb'),
					fieldLabel: langs('Вид диагноза'),
					hiddenName: 'DiagSetClass_id',
					xtype: 'swdiagsetclasscombo',
					width: 250
				}, {
					checkAccessRights: true,
					allowBlank: (getRegionNick() != 'ekb'),
					hidden: (getRegionNick() != 'ekb'),
					hiddenName: 'Diag_id',
					width: 500,
					xtype: 'swdiagcombo',
					onChange: function(combo, newValue, oldValue) {
						var base_form = this.FormPanel.getForm();

						base_form.findField('DiagSetClass_id').clearFilter();
						if (!Ext.isEmpty(newValue) && newValue == base_form.findField('EvnUslugaCommon_pid').getFieldValue('Diag_id')) {
							base_form.findField('DiagSetClass_id').getStore().filterBy(function(rec) {
								return (rec.get('DiagSetClass_Code').inlist(['1']));
							});
							base_form.findField('DiagSetClass_id').setFieldValue('DiagSetClass_Code', '1');
							base_form.findField('DiagSetClass_id').disable();
						} else {
							if (base_form.findField('DiagSetClass_id').getFieldValue('DiagSetClass_Code') == '1') {
								base_form.findField('DiagSetClass_id').setValue(null);
							}
							base_form.findField('DiagSetClass_id').getStore().filterBy(function(rec) {
								return (rec.get('DiagSetClass_Code').inlist(['0','2','3']));
							});
							if (this.action != 'view') {
								base_form.findField('DiagSetClass_id').enable();
							}
						}

						/*if ( getRegionNick() == 'ekb' && this.parentClass.inlist(['EvnSection','EvnPS']) ) {
							base_form.findField('UslugaComplex_id').clearValue();
							base_form.findField('UslugaComplex_id').lastQuery = 'This query sample that is not will never appear';
							base_form.findField('UslugaComplex_id').getStore().removeAll();
							base_form.findField('UslugaComplex_id').getStore().baseParams.UcplDiag_id = newValue;
						}*/
					}.createDelegate(this)
				}, {
					allowBlank: true,
					allowNegative: false,
					disabled: true,
					fieldLabel: langs('Цена'),
					name: 'EvnUslugaCommon_Price',
					tabIndex: TABINDEX_EUCOMEF + 16,
					width: 150,
					xtype: 'numberfield'
				}, {
					allowBlank: true,
					allowDecimals: true,
					allowNegative: false,
					fieldLabel: langs('УЕТ'),
					listeners: {
						'change': function(field, newValue, oldValue) {
							var base_form = this.FormPanel.getForm();
							base_form.findField('EvnUslugaCommon_Kolvo').fireEvent('change', base_form.findField('EvnUslugaCommon_Kolvo'), base_form.findField('EvnUslugaCommon_Kolvo').getValue());
						}.createDelegate(this)
					},
					maxValue: (getGlobalOptions().region && getGlobalOptions().region.nick == 'ufa' ? 99 : 20),
					name: 'UslugaComplexTariff_UED',
					tabIndex: TABINDEX_EUCOMEF + 17,
					width: 100,
					xtype: 'numberfield'
				}, {
					allowBlank: false,
					allowNegative: false,
					enableKeyEvents: true,
					fieldLabel: langs('Количество'),
					listeners: {
						'change': function(field, newValue, oldValue) {
							var base_form = this.FormPanel.getForm();

							base_form.findField('EvnUslugaCommon_Summa').setValue('');

							if ( !Ext.isEmpty(newValue) ) {
								var evn_usluga_common_price = 0;

								if ( !Ext.isEmpty(base_form.findField('UslugaComplexTariff_UED').getValue()) ) {
									evn_usluga_common_price = evn_usluga_common_price + Number(base_form.findField('UslugaComplexTariff_UED').getValue());
								}

								if ( !Ext.isEmpty(evn_usluga_common_price) && evn_usluga_common_price > 0 ) {
									base_form.findField('EvnUslugaCommon_Summa').setValue(evn_usluga_common_price * newValue);
								}
								else {
									base_form.findField('EvnUslugaCommon_Summa').setValue('');
								}
							}
						}.createDelegate(this),
						'keydown': function (inp, e) {
							if ( e.shiftKey == false && e.getKey() == Ext.EventObject.TAB && !this.findById('EUComEF_EvnAggPanel').collapsed ) {
								e.stopEvent();
								this.findById('EUComEF_EvnAggGrid').getView().focusRow(0);
								this.findById('EUComEF_EvnAggGrid').getSelectionModel().selectFirstRow();
							}
						}.createDelegate(this)
					},
					name: 'EvnUslugaCommon_Kolvo',
					tabIndex: TABINDEX_EUCOMEF + 18,
					width: 150,
					xtype: 'numberfield'
				}, {
					allowDecimals: true,
					allowNegative: false,
					disabled: true,
					fieldLabel: langs('Сумма (УЕТ)'),
					name: 'EvnUslugaCommon_Summa',
					tabIndex: TABINDEX_EUCOMEF + 19,
					width: 100,
					xtype: 'numberfield'
				}]
			}),
            this.uslugaPanel,
			new sw.Promed.Panel({
				border: true,
				collapsible: true,
				style: "margin-bottom: 0.5em;",
				height: 200,
				id: 'EUComEF_EvnAggPanel',
				isLoaded: false,
				layout: 'border',
				listeners: {
					'expand': function(panel) {
						if ( panel.isLoaded === false ) {
							panel.isLoaded = true;
							panel.findById('EUComEF_EvnAggGrid').getStore().load({
								params: {
									EvnAgg_pid: this.FormPanel.getForm().findField('EvnUslugaCommon_id').getValue()
								}
							});
						}
						panel.doLayout();
					}.createDelegate(this)
				},
				// style: 'margin-bottom: 0.5em;',
				title: langs('2. Осложнения'),
				items: [ new Ext.grid.GridPanel({
					autoExpandColumn: 'autoexpand',
					autoExpandMin: 100,
					border: false,
					columns: [{
						dataIndex: 'AggType_Name',
						header: langs('Вид осложнения'),
						hidden: false,
						id: 'autoexpand',
						sortable: true
					}, {
						dataIndex: 'AggWhen_Name',
						header: langs('Контекст осложнения'),
						hidden: false,
						resizable: false,
						sortable: true,
						width: 200
					}, {
						dataIndex: 'EvnAgg_setDate',
						header: langs('Дата осложнения'),
						hidden: false,
						renderer: Ext.util.Format.dateRenderer('d.m.Y'),
						resizable: false,
						sortable: true,
						width: 130
					}],
					frame: false,
					id: 'EUComEF_EvnAggGrid',
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

							var grid = this.findById('EUComEF_EvnAggGrid');

							switch ( e.getKey() ) {
								case Ext.EventObject.DELETE:
									this.deleteEvent('EvnAgg');
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

									this.openEvnAggEditWindow(action);
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
									var base_form = this.FormPanel.getForm();

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
										if ( !this.findById('EUComEF_EvnUslugaPanel').collapsed && this.action != 'view' ) {
											base_form.findField('EvnUslugaCommon_Kolvo').focus(true);
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
							this.openEvnAggEditWindow('edit');
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
								var toolbar = this.findById('EUComEF_EvnAggGrid').getTopToolbar();

								if ( selected_record ) {
									access_type = selected_record.get('accessType');
									id = selected_record.get('EvnAgg_id');
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
									LoadEmptyRow(this.findById('EUComEF_EvnAggGrid'));
								}
								// this.findById('EUComEF_EvnAggGrid').getView().focusRow(0);
								// this.findById('EUComEF_EvnAggGrid').getSelectionModel().selectFirstRow();
							}.createDelegate(this)
						},
						reader: new Ext.data.JsonReader({
							id: 'EvnAgg_id'
						}, [{
							mapping: 'accessType',
							name: 'accessType',
							type: 'string'
						}, {
							mapping: 'EvnAgg_id',
							name: 'EvnAgg_id',
							type: 'int'
						}, {
							mapping: 'EvnAgg_pid',
							name: 'EvnAgg_pid',
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
							mapping: 'AggType_id',
							name: 'AggType_id',
							type: 'int'
						}, {
							mapping: 'AggWhen_id',
							name: 'AggWhen_id',
							type: 'int'
						}, {
							mapping: 'AggType_Name',
							name: 'AggType_Name',
							type: 'string'
						}, {
							mapping: 'AggWhen_Name',
							name: 'AggWhen_Name',
							type: 'string'
						}, {
							dateFormat: 'd.m.Y',
							mapping: 'EvnAgg_setDate',
							name: 'EvnAgg_setDate',
							type: 'date'
						}, {
							mapping: 'EvnAgg_setTime',
							name: 'EvnAgg_setTime',
							type: 'string'
						}]),
						url: '/?c=EvnAgg&m=loadEvnAggGrid'
					}),
					tbar: new sw.Promed.Toolbar({
						buttons: [{
							handler: function() {
								this.openEvnAggEditWindow('add');
							}.createDelegate(this),
							iconCls: 'add16',
							text: BTN_GRIDADD
						}, {
							handler: function() {
								this.openEvnAggEditWindow('edit');
							}.createDelegate(this),
							iconCls: 'edit16',
							text: BTN_GRIDEDIT
						}, {
							handler: function() {
								this.openEvnAggEditWindow('view');
							}.createDelegate(this),
							iconCls: 'view16',
							text: BTN_GRIDVIEW
						}, {
							handler: function() {
								this.deleteEvent('EvnAgg');
							}.createDelegate(this),
							iconCls: 'delete16',
							text: BTN_GRIDDEL
						}]
					})
				})]
			})
			]
		});

		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.doSave();
				}.createDelegate(this),
				iconCls: 'save16',
				onShiftTabAction: function () {
					if ( !this.findById('EUComEF_EvnAggPanel' ).collapsed) {
						this.findById('EUComEF_EvnAggGrid').getView().focusRow(0);
						this.findById('EUComEF_EvnAggGrid').getSelectionModel().selectFirstRow();
					}
					else {
						this.FormPanel.getForm().findField('EvnUslugaCommon_Kolvo').focus(true);
					}
				}.createDelegate(this),
				onTabAction: function () {
					this.buttons[this.buttons.length - 1].focus();
				}.createDelegate(this),
				tabIndex: TABINDEX_EUCOMEF + 18,
				text: BTN_FRMSAVE
			},{
				handler: function() {
					win.doPrint();
				},
				iconCls: 'print16',
				tabIndex: 21,
				text: BTN_FRMPRINT
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
					if (this.action != 'view') {
						this.buttons[0].focus();
					} else if (!this.findById('EUComEF_EvnAggPanel').collapsed) {
						this.findById('EUComEF_EvnAggGrid').getView().focusRow(0);
						this.findById('EUComEF_EvnAggGrid').getSelectionModel().selectFirstRow();
					}
				}.createDelegate(this),
				onTabAction: function () {
					if (!this.findById('EUComEF_EvnUslugaPanel').collapsed && this.action != 'view') {
						if (!this.FormPanel.getForm().findField('EvnUsluga_pid').disabled) {
							this.FormPanel.getForm().findField('EvnUsluga_pid').focus(true, 100);
						} else {
							this.FormPanel.getForm().findField('EvnUsluga_setDate').focus(true, 100);
						}
					} else if (!this.findById('EUComEF_EvnAggPanel').collapsed) {
						this.findById('EUComEF_EvnAggGrid').getView().focusRow(0);
						this.findById('EUComEF_EvnAggGrid').getSelectionModel().selectFirstRow();
					}
				}.createDelegate(this),
				tabIndex: TABINDEX_EUCOMEF + 19,
				text: BTN_FRMCANCEL
			}],
			items: [
				this.PersonInfo,
				{
					autoScroll: true,
					bodyStyle: 'padding: 5px 5px 0',
					layout: 'form',
					border: false,
					region: 'center',
					items: [
						this.FormPanel
						, this.EvnXmlPanel
						, this.FilePanel
						, this.AttributeValuePanel
					]
				}
			]
		});

		sw.Promed.swEvnUslugaEditWindow.superclass.initComponent.apply(this, arguments);

		this.findById('EUComEF_LpuSectionCombo').addListener('change', function(combo, newValue, oldValue) {
			this.checkIsHTMedicalCare();
			this.checkAttributeValuePanelHidden();

			var base_form = this.FormPanel.getForm();

			var index = combo.getStore().findBy(function(rec) {
				return ( rec.get('LpuSection_id') == newValue );
			});
			var record = combo.getStore().getAt(index);

			var changeUslugaComplexPlaceFilter = (getRegionNick() != 'ekb' || getUslugaOptions().enable_usluga_section_load || getUslugaOptions().enable_usluga_section_load_filter);

			if (changeUslugaComplexPlaceFilter) {
				base_form.findField('UslugaComplex_id').getStore().baseParams.LpuSection_id = newValue;
				base_form.findField('UslugaComplex_id').getStore().baseParams.LpuUnitType_id = base_form.findField('LpuSection_uid').getFieldValue('LpuUnitType_id');
				base_form.findField('UslugaComplex_id').clearValue();
				base_form.findField('UslugaComplex_id').getStore().removeAll();
				base_form.findField('UslugaComplex_id').lastQuery = 'The string that will never appear';
			}

			if ( getRegionNick() == 'buryatiya' ) {
				if (record) {
					base_form.findField('UslugaComplex_id').setLpuSectionProfile_id(record.get('LpuSectionProfile_id'));
					base_form.findField('UslugaComplex_id').clearValue();
				}
			}

			if ( this.allowRayTherapy() == true ) {
				base_form.findField('UslugaComplexTariff_UED').enable();
			}
			else {
				base_form.findField('UslugaComplexTariff_UED').disable();
				base_form.findField('UslugaComplexTariff_UED').setRawValue('');
				base_form.findField('EvnUslugaCommon_Summa').setRawValue('');
			}

			this.loadUslugaComplexTariffCombo();
			this.setUslugaComplexPartitionCodeList(base_form.findField('PayType_id').getFieldValue('PayType_SysNick'));
			this.setDefaultLpuSectionProfile();
			this.toggleVisibleExecutionBtnPanel();
			base_form.findField('LpuSectionProfile_id').onChangeLpuSectionId(combo, newValue);
		}.createDelegate(this));

		this.findById('EUComEF_MedPersonalCombo').addListener('change', function(combo, newValue, oldValue) {
			this.checkIsHTMedicalCare();
			this.checkAttributeValuePanelHidden();

			var base_form = this.FormPanel.getForm();
			var changeUslugaComplexPlaceFilter = (!getRegionNick().inlist(['ekb','astra']) || getUslugaOptions().enable_usluga_section_load || getUslugaOptions().enable_usluga_section_load_filter);

			if (getRegionNick() == 'ekb' && base_form.findField('filterUslugaComplex').getValue()) {
				base_form.findField('UslugaComplex_id').getStore().baseParams.MedPersonal_id = base_form.findField('MedStaffFact_id').getFieldValue('MedPersonal_id');
				base_form.findField('UslugaComplex_id').clearValue();
				base_form.findField('UslugaComplex_id').getStore().removeAll();
				base_form.findField('UslugaComplex_id').lastQuery = 'The string that will never appear';
			}
			
			if (getRegionNick() == 'ekb' && combo.getFieldValue('LpuSection_id')) {
				base_form.findField('LpuSection_uid').setValue(combo.getFieldValue('LpuSection_id'));
			}

			if (changeUslugaComplexPlaceFilter) {
				var index = combo.getStore().findBy(function(rec) {
					return (rec.get(combo.valueField) == newValue);
				});

				if ( index >= 0 ) {
					base_form.findField('UslugaComplex_id').getStore().baseParams.LpuSection_id = combo.getStore().getAt(index).get('LpuSection_id');
					base_form.findField('UslugaComplex_id').clearValue();
					base_form.findField('UslugaComplex_id').getStore().removeAll();
					base_form.findField('UslugaComplex_id').lastQuery = 'The string that will never appear';
				}
				else {
					base_form.findField('UslugaComplex_id').getStore().baseParams.LpuSection_id = null;
				}
			}

			this.loadUslugaComplexTariffCombo();
			this.setDefaultLpuSectionProfile();
			this.toggleVisibleExecutionBtnPanel();
		}.createDelegate(this));
	},
	checkIsHTMedicalCare: function() {
		var base_form = this.FormPanel.getForm();
		if (getRegionNick() == 'ekb') {
			// проверяем признак LpuSection_IsHTMedicalCare
			if (this.parentClass != 'EvnVizit' && this.parentClass != 'EvnPL' && this.parentClass != 'EvnPLStom') {
				var LpuUnitType_Code = base_form.findField('LpuSection_uid').getFieldValue('LpuUnitType_Code') || this.LpuUnitType_Code;
				if ((LpuUnitType_Code != 3) && (LpuUnitType_Code != 5)) {
					// кругл
					if (base_form.findField('LpuSection_uid').getFieldValue('LpuSection_IsHTMedicalCare') == 2) {
						base_form.findField('UslugaComplex_id').getStore().baseParams.UslugaComplexPartition_CodeList = Ext.util.JSON.encode([102,103,104,105,106,107]);
					} else {
						base_form.findField('UslugaComplex_id').getStore().baseParams.UslugaComplexPartition_CodeList = Ext.util.JSON.encode([102,103,104,105,107]);
					}
					var setPriemFilter = (this.isPriem && !getUslugaOptions().enable_usluga_section_load && !getUslugaOptions().enable_usluga_section_load_filter);
					if (setPriemFilter || base_form.findField('LpuSection_uid').getFieldValue('LpuSectionProfile_SysNick') == 'priem') {
						var list = Ext.util.JSON.decode(base_form.findField('UslugaComplex_id').getStore().baseParams.UslugaComplexPartition_CodeList);
						list.push(300, 301);
						base_form.findField('UslugaComplex_id').getStore().baseParams.UslugaComplexPartition_CodeList = Ext.util.JSON.encode(list);
					}
				}
			}
		}
	},
	keys: [{
		alt: true,
		fn: function(inp, e) {
			var current_window = Ext.getCmp('EvnUslugaEditWindow');

			switch ( e.getKey() ) {
				case Ext.EventObject.C:
					current_window.doSave();
				break;

				case Ext.EventObject.J:
					current_window.onCancelAction();
				break;

				case Ext.EventObject.NUM_ONE:
				case Ext.EventObject.ONE:
					current_window.findById('EUComEF_EvnUslugaPanel').toggleCollapse();
				break;

				case Ext.EventObject.NUM_TWO:
				case Ext.EventObject.TWO:
					current_window.findById('EUComEF_EvnAggPanel').toggleCollapse();
				break;
			}
		}.createDelegate(this),
		key: [
			Ext.EventObject.C,
			Ext.EventObject.J,
			Ext.EventObject.NUM_ONE,
			Ext.EventObject.NUM_TWO,
			Ext.EventObject.ONE,
			Ext.EventObject.TWO
		],
		stopEvent: true
	}],
	layout: 'border',
	listeners: {
		'hide': function(win) {
			win.onHide();
		},
		'maximize': function(win) {
			win.findById('EUComEF_EvnAggPanel').doLayout();
			win.findById('EUComEF_AttributeValuePanel').doLayout();
			win.findById('EUComEF_EvnUslugaPanel').doLayout();
		},
		'restore': function(win) {
			win.findById('EUComEF_EvnAggPanel').doLayout();
			win.findById('EUComEF_AttributeValuePanel').doLayout();
			win.findById('EUComEF_EvnUslugaPanel').doLayout();
		}
	},
	loadUslugaComplexTariffCombo: function () {
        var base_form = this.FormPanel.getForm(),
            combo = base_form.findField('UslugaComplexTariff_id'),
            uc_combo = base_form.findField('UslugaComplex_id'),
            evn_agg_panel = this.findById('EUComEF_EvnAggPanel'),
            atr_val_panel = this.findById('EUComEF_AttributeValuePanel'),
            uedField = base_form.findField('UslugaComplexTariff_UED'),
            kolvoField = base_form.findField('EvnUslugaCommon_Kolvo'),
            //isMes = (2 == base_form.findField('EvnUslugaStom_IsMes').getValue()),
            //isPerm = (getGlobalOptions().region && getGlobalOptions().region.nick == 'perm'),
            uc_id = uc_combo.getValue(),
            isPackage = false,
            params = {
                LpuSection_id: base_form.findField('LpuSection_uid').getValue()
                ,PayType_id: base_form.findField('PayType_id').getValue()
                ,Person_id: base_form.findField('Person_id').getValue()
                ,UslugaComplexTariff_Date: base_form.findField('EvnUslugaCommon_setDate').getValue()
            },
            uc_rec,
            index;
        if (uc_id) {
            index = uc_combo.getStore().findBy(function(rec) {
                return (rec.get(uc_combo.valueField) == uc_id);
            });
            uc_rec = uc_combo.getStore().getAt(index);
            isPackage = (uc_rec && 9 == uc_rec.get('UslugaComplexLevel_id'));
        }
        this.uslugaPanel.doReset();
        if (isPackage) {
            combo.clearParams();
            this.uslugaPanel.setTariffParams(params);
            /*if (isPerm == true && !Ext.isEmpty(this.Mes_id) && isMes) {
                this.uslugaPanel.setParam('Mes_id', this.Mes_id);
            }*/
            this.uslugaPanel.setParam('Mes_id', null);
            this.uslugaPanel.setParam('EvnUsluga_pid', base_form.findField('EvnUslugaCommon_pid').getValue());
            this.uslugaPanel.setParam('UslugaComplex_id', uc_id);
            this.uslugaPanel.setParam('UslugaComplexLevel_id', uc_rec.get('UslugaComplexLevel_id'));
            this.uslugaPanel.doLoad();
            evn_agg_panel.collapse();
            atr_val_panel.collapse();
            kolvoField.setValue('');
        } else {
            params['UslugaComplex_id'] = uc_id;
            combo.setParams(params);
            kolvoField.setValue(1);
        }
        if ('add' == this.action) {
            uedField.setDisabled(isPackage || !this.allowRayTherapy());
            uedField.setValue(null);
            uedField.fireEvent('change', uedField, uedField.getValue());
            combo.setDisabled(isPackage);
        }
        kolvoField.setDisabled(isPackage);
        evn_agg_panel.setDisabled(isPackage);
        atr_val_panel.setDisabled(isPackage);
        this.uslugaPanel.setVisible(isPackage);
        combo.isAllowSetFirstValue = ('add' == this.action);
        combo.loadUslugaComplexTariffList();
        return true;
	},
	maximizable: true,
	minHeight: 450,
	minWidth: 700,
	modal: true,
	onCancelAction: function() {
		var evn_usluga_id = this.FormPanel.getForm().findField('EvnUslugaCommon_id').getValue();

		if ( evn_usluga_id > 0 && this.action == 'add' ) {
			// удалить услугу
			// закрыть окно после успешного удаления
			var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Удаление услуги..." });
			loadMask.show();

			Ext.Ajax.request({
				callback: function(options, success, response) {
					loadMask.hide();

					if ( success ) {
						this.hide();
					}
					else {
						sw.swMsg.alert(langs('Ошибка'), langs('При удалении услуги возникли ошибки'));
						return false;
					}
				}.createDelegate(this),
				params: {
					'class': 'EvnUslugaCommon',
					'id': evn_usluga_id
				},
				url: '/?c=EvnUsluga&m=deleteEvnUsluga'
			});
		}
		else {
			this.hide();
		}
	},
	onHide: Ext.emptyFn,
	openEvnAggEditWindow: function(action) {
		if ( typeof action != 'string' || !action.inlist([ 'add', 'edit', 'view' ]) ) {
			return false;
		}

		var base_form = this.FormPanel.getForm();
		var grid = this.findById('EUComEF_EvnAggGrid');

		if ( this.action == 'view' ) {
			if ( action == 'add' ) {
				return false;
			}
			else if ( action == 'edit' ) {
				action = 'view';
			}
		}

		if ( getWnd('swEvnAggEditWindow').isVisible() ) {
			sw.swMsg.alert(langs('Сообщение'), langs('Окно редактирования осложнения уже открыто'));
			return false;
		}

		if ( action == 'add' && base_form.findField('EvnUslugaCommon_id').getValue() == 0 ) {
			this.doSave({
				openChildWindow: function() {
					this.openEvnAggEditWindow(action);
				}.createDelegate(this),
				print: false
			});
			return false;
		}

		var params = new Object();
		var formParams = new Object();

		params.action = action;
		params.callback = function(data) {
			if ( !data || !data.EvnAggData ) {
				return false;
			}

			var record = grid.getStore().getById(data.EvnAggData[0].EvnAgg_id);

			if ( !record ) {
				if ( grid.getStore().getCount() == 1 && !grid.getStore().getAt(0).get('EvnAgg_id') ) {
					grid.getStore().removeAll();
				}

				grid.getStore().loadData(data.EvnAggData, true);
			}
			else {
				var grid_fields = new Array();
				var i = 0;

				grid.getStore().fields.eachKey(function(key, item) {
					grid_fields.push(key);
				});

				for ( i = 0; i < grid_fields.length; i++ ) {
					record.set(grid_fields[i], data.EvnAggData[0][grid_fields[i]]);
				}

				record.commit();
			}
		}.createDelegate(this);
		params.onHide = function() {
			grid.getView().focusRow(0);
			grid.getSelectionModel().selectFirstRow();
		}.createDelegate(this);
		params.Person_id = this.PersonInfo.getFieldValue('Person_id');
		params.Person_Birthday = this.PersonInfo.getFieldValue('Person_Birthday');
		params.Person_Firname = this.PersonInfo.getFieldValue('Person_Firname');
		params.Person_Secname = this.PersonInfo.getFieldValue('Person_Secname');
		params.Person_Surname = this.PersonInfo.getFieldValue('Person_Surname');
		params.minDate =Date.parseDate(base_form.findField('EvnUslugaCommon_setDate').getRawValue()
						+' '+base_form.findField('EvnUslugaCommon_setTime').getRawValue(),'d.m.Y H:i')
		if ( action == 'add' ) {
			formParams.EvnAgg_id = 0;
			formParams.EvnAgg_pid = base_form.findField('EvnUslugaCommon_id').getValue();
			formParams.Person_id = base_form.findField('Person_id').getValue();
			formParams.PersonEvn_id = base_form.findField('PersonEvn_id').getValue();
            formParams.Server_id = base_form.findField('Server_id').getValue();
            formParams.Evn_setDate = base_form.findField('EvnUslugaCommon_setDate').getRawValue();
            formParams.Evn_setTime = base_form.findField('EvnUslugaCommon_setTime').getRawValue();
		}
		else {
			var selected_record = grid.getSelectionModel().getSelected();

			if (!selected_record || !selected_record.get('EvnAgg_id')) {
				return false;
			}

			if (selected_record.get('accessType') != 'edit') {
				params.action = 'view';
			}

			formParams.EvnAgg_id = selected_record.get('EvnAgg_id');
		}

		params.formParams = formParams;

		getWnd('swEvnAggEditWindow').show(params);
	},
	parentClass: null,
	plain: true,
	resizable: true,
	setUslugaComplexPartitionCodeList: function(paytype_sysnick) {
		if ( !getRegionNick().inlist(['ekb']) ) {
			return false;
		}

		var base_form = this.FormPanel.getForm();
		var usluga_combo = base_form.findField('UslugaComplex_id');
		var setPriemFilter = (this.isPriem && !getUslugaOptions().enable_usluga_section_load && !getUslugaOptions().enable_usluga_section_load_filter);
		var LpuUnitType_Code = base_form.findField('LpuSection_uid').getFieldValue('LpuUnitType_Code') || this.LpuUnitType_Code;
		if (this.only351Group) {
			usluga_combo.getStore().baseParams.UslugaComplexPartition_CodeList = Ext.util.JSON.encode([351]);
		} else if ( this.parentClass == 'EvnVizit' || this.parentClass == 'EvnPL' || this.parentClass == 'EvnPLStom' ) {
			var xdate = new Date(2015, 0, 1);

			if ( this.parentClass == 'EvnPLStom' ) {
				if ( !Ext.isEmpty(base_form.findField('EvnUslugaCommon_setDate').getValue()) && base_form.findField('EvnUslugaCommon_setDate').getValue() >= xdate ) {
					usluga_combo.getStore().baseParams.UslugaComplexPartition_CodeList = Ext.util.JSON.encode([303]);
				}
				else {
					usluga_combo.getStore().baseParams.UslugaComplexPartition_CodeList = Ext.util.JSON.encode([300, 301]);
				}
			}
			else {
				usluga_combo.getStore().baseParams.UslugaComplexPartition_CodeList = Ext.util.JSON.encode([300, 301]);
			}
			if ('bud' == paytype_sysnick) {
				usluga_combo.getStore().baseParams.UslugaComplexPartition_CodeList = Ext.util.JSON.encode([351]);
			}
		}
		else {

			if ( LpuUnitType_Code == 3 || LpuUnitType_Code == 5 ) {
				// днев
				usluga_combo.getStore().baseParams.UslugaComplexPartition_CodeList = Ext.util.JSON.encode([202,203,205,206]);
			}
			else {
				// кругл
				if ( base_form.findField('LpuSection_uid').getFieldValue('LpuSection_IsHTMedicalCare') == 2 ) {
					usluga_combo.getStore().baseParams.UslugaComplexPartition_CodeList = Ext.util.JSON.encode([102,103,104,105,106,107]);
				}
				else {
					usluga_combo.getStore().baseParams.UslugaComplexPartition_CodeList = Ext.util.JSON.encode([102,103,104,105,107]);
				}
			}
			if ('bud' == paytype_sysnick) {
				if (LpuUnitType_Code == 3 || LpuUnitType_Code == 5) {
					usluga_combo.getStore().baseParams.UslugaComplexPartition_CodeList = Ext.util.JSON.encode([252]);
				} else {
					usluga_combo.getStore().baseParams.UslugaComplexPartition_CodeList = Ext.util.JSON.encode([152]);
				}
			}
			if (getRegionNick() == 'ekb' && (setPriemFilter || base_form.findField('LpuSection_uid').getFieldValue('LpuSectionProfile_SysNick') == 'priem')) {
				var list = Ext.util.JSON.decode(usluga_combo.getStore().baseParams.UslugaComplexPartition_CodeList);
				list.push(300, 301);
				usluga_combo.getStore().baseParams.UslugaComplexPartition_CodeList = Ext.util.JSON.encode(list);
			}
		}
		if(LpuUnitType_Code != 3 && LpuUnitType_Code != 5 && base_form.findField('LpuSection_uid').getFieldValue('LpuSection_IsHTMedicalCare') == 2 && !this.only351Group && getRegionNick() == 'ekb')
		{
			var list = Ext.util.JSON.decode(usluga_combo.getStore().baseParams.UslugaComplexPartition_CodeList);
			switch (paytype_sysnick) {
				case 'bud':
				case 'fbud':
					list.push(156);
					break;
				case 'oms':
					list.push(106);
					break;
			}
			usluga_combo.getStore().baseParams.UslugaComplexPartition_CodeList = Ext.util.JSON.encode(list);
		}
	},
	setDisDT: function() {
		var base_form = this.FormPanel.getForm();

		if (getRegionNick() == 'ufa' && this.parentClass && this.parentClass.inlist(['EvnPS', 'EvnSection'])) {
			var setDate = base_form.findField('EvnUslugaCommon_setDate').getValue();
			base_form.findField('EvnUslugaCommon_disDate').setMinValue(setDate);
		}

		if ( this.isVisibleDisDTPanel ) {
			return false;
		}

		base_form.findField('EvnUslugaCommon_disDate').setValue(base_form.findField('EvnUslugaCommon_setDate').getValue());
		base_form.findField('EvnUslugaCommon_disTime').setValue(base_form.findField('EvnUslugaCommon_setTime').getValue());
	},
	toggleVisibleDisDTPanel: function(action) {
		var base_form = this.FormPanel.getForm();

		if (action == 'show') {
			this.isVisibleDisDTPanel = false;
		} else if (action == 'hide') {
			this.isVisibleDisDTPanel = true;
		}
		if(!this.findById('EUComEF_EvnUslugaDisDTPanel').isVisible() && !action){
			this.isVisibleDisDTPanel = false;
		}

		if (this.isVisibleDisDTPanel) {
			this.findById('EUComEF_EvnUslugaDisDTPanel').hide();
			this.findById('EUComEF_ToggleVisibleDisDTBtn').setText(langs('Уточнить период выполнения'));
			base_form.findField('EvnUslugaCommon_disDate').setAllowBlank(true);
			base_form.findField('EvnUslugaCommon_disTime').setAllowBlank(true);
			base_form.findField('EvnUslugaCommon_disDate').setValue(null);
			base_form.findField('EvnUslugaCommon_disTime').setValue(null);
			base_form.findField('EvnUslugaCommon_disDate').setMaxValue(undefined);
			base_form.findField('EvnUslugaCommon_disDate').setMinValue(undefined);
			this.isVisibleDisDTPanel = false;
		} else {
			this.findById('EUComEF_EvnUslugaDisDTPanel').show();
			this.findById('EUComEF_ToggleVisibleDisDTBtn').setText(langs('Скрыть поля'));
			base_form.findField('EvnUslugaCommon_disDate').setAllowBlank(false);
			base_form.findField('EvnUslugaCommon_disTime').setAllowBlank(false);
			if (getRegionNick() == 'ufa' && this.parentClass && this.parentClass.inlist(['EvnPS', 'EvnSection'])) {
				var setDate = base_form.findField('EvnUslugaCommon_setDate').getValue();
				var week = 1000 * 60 * 60 * 24 * 7;
				var weekTime = new Date(new Date().getTime() + week);
				base_form.findField('EvnUslugaCommon_disDate').setMinValue(setDate);
				base_form.findField('EvnUslugaCommon_disDate').setMaxValue(weekTime);
				// инициализация значений для поля
				base_form.findField('EvnUslugaCommon_disDate').hide();
				base_form.findField('EvnUslugaCommon_disDate').show();
			}
			this.isVisibleDisDTPanel = true;
		}
	},
	loadUslugaComplex: function(UslugaComplex_id) {
    	var win = this;

    	if (win.loadingInProgress) {
    		return; // если выполняется первичная загрузка формы, то эта проверка не нужна.
		}

		var base_form = this.FormPanel.getForm();
    	win.getLoadMask('Проверка доступности ранее выбранной услуги...').show();
		base_form.findField('UslugaComplex_id').getStore().load({
			params: {
				UslugaComplex_wid: UslugaComplex_id
			},
			callback: function() {
				win.getLoadMask().hide();

				var index = base_form.findField('UslugaComplex_id').getStore().findBy(function(rec) {
					return (rec.get('UslugaComplex_id') == UslugaComplex_id);
				});

				if ( index >= 0 ) {
					base_form.findField('UslugaComplex_id').setValue(UslugaComplex_id);
					base_form.findField('UslugaComplex_id').fireEvent('change', base_form.findField('UslugaComplex_id'), base_form.findField('UslugaComplex_id').getValue());
				}

				win.toggleVisibleExecutionBtnPanel();
			}
		});
	},
	checkIsEco: function() {
		if (getRegionNick() == 'perm') {
			var win = this;
			var base_form = this.FormPanel.getForm();
			var EvnUslugaCommon_pid = base_form.findField('EvnUslugaCommon_pid').getValue();

			if (this.parentClass.inlist(['EvnSection', 'EvnPS']) && !Ext.isEmpty(EvnUslugaCommon_pid)) {
				// проверяем есть ли в КВС услуга ЭКО
				Ext.Ajax.request({
					url: '/?c=EvnSection&m=checkIsEco',
					params: {
						Evn_id: EvnUslugaCommon_pid
					},
					success: function(response, options) {
						win.isEco = false;
						var response_obj = Ext.util.JSON.decode(response.responseText);
						if ( response_obj.isEco ) {
							win.isEco = true;
						}
						win.checkAttributeValuePanelHidden();
					}
				})
			}
		}
	},
	checkAttributeValuePanelHidden: function() {
		var win = this;
		var base_form = this.FormPanel.getForm();
		var UslugaComplex_Code = base_form.findField('UslugaComplex_id').getFieldValue('UslugaComplex_Code');
		var LpuUnitType_SysNick = base_form.findField('LpuSection_uid').getFieldValue('LpuUnitType_SysNick');

		var isHidden = true;
		win.EvnUslugaAttributeValueGrid.UslugaComplex_Code = UslugaComplex_Code;
		switch(getRegionNick()) {
			case 'perm':
				if (LpuUnitType_SysNick && LpuUnitType_SysNick.inlist(['dstac', 'hstac', 'pstac']) && UslugaComplex_Code && UslugaComplex_Code.inlist(['A11.20.017.001', 'A11.20.017.002', 'A11.20.017.003', 'A11.20.030.001', 'A11.20.017'])) {
					isHidden = false;
				} else if (this.isEco && LpuUnitType_SysNick && LpuUnitType_SysNick.inlist(['dstac', 'hstac', 'pstac']) && UslugaComplex_Code && UslugaComplex_Code.inlist(['A04.20.001', 'A04.20.001.001'])) {
					isHidden = false;
				} else if (UslugaComplex_Code && UslugaComplex_Code.inlist(['A05.10.002', 'A05.10.006'])) {
					isHidden = false;
				}
				break;
			case 'adygeya':
			case 'khak':
			case 'pskov':
				if (LpuUnitType_SysNick && LpuUnitType_SysNick.inlist(['dstac', 'hstac', 'pstac']) && UslugaComplex_Code && UslugaComplex_Code.inlist(['A11.20.017'])) {
					isHidden = false;
				} else if (UslugaComplex_Code && UslugaComplex_Code.inlist(['A05.10.002', 'A05.10.006'])) {
					isHidden = false;
				}
				break;

			case 'ufa':
				if (this.parentClass.inlist(['EvnPS', 'EvnSection']) && UslugaComplex_Code &&
					UslugaComplex_Code.inlist(['A06.10.006', 'A06.10.006.002']))
					isHidden = false;

				break;

			case 'msk':
				if (UslugaComplex_Code &&
					UslugaComplex_Code.inlist(['A26.05.066.002', 'A26.08.027.001', 'A26.08.027.001.1', 'A26.08.027.001.2', 'A26.08.046.002', 'A26.09.044.002', 'A26.09.060.002', 'A26.08.027.004', 'A26.08.027.004.001', 'A26.08.027.004.002', 'A26.08.027.005', 'A26.08.027.006']))
					isHidden = false;

				break;
		}
		if(!getRegionNick().inlist(['pskov','khak','perm','kz'])) {
			if (UslugaComplex_Code && UslugaComplex_Code.inlist(['A05.10.002', 'A05.10.006'])) {
				isHidden = false;
			}
		}

		if (isHidden) {
			win.AttributeValuePanel.hide();
		} else {
			win.AttributeValuePanel.show();
		}
		return isHidden;
	},
	setDefaultLpuSectionProfile: function() {
		var win = this;
		var base_form = this.FormPanel.getForm();

		if (base_form.findField('UslugaPlace_id').getValue() == 1 || (getRegionNick() == 'ekb' && base_form.findField('UslugaPlace_id').getValue() == 2)) {
			if (getRegionNick().inlist(['astra', 'ekb']) && !Ext.isEmpty(base_form.findField('MedStaffFact_id').getFieldValue('LpuSectionProfile_msfid'))) {
				base_form.findField('LpuSectionProfile_id').setValue(base_form.findField('MedStaffFact_id').getFieldValue('LpuSectionProfile_msfid'));
			} else if (!Ext.isEmpty(base_form.findField('LpuSection_uid').getFieldValue('LpuSectionProfile_id'))) {
				base_form.findField('LpuSectionProfile_id').setValue(base_form.findField('LpuSection_uid').getFieldValue('LpuSectionProfile_id'));
			} else {
				base_form.findField('LpuSectionProfile_id').setValue(null);
			}
		} else {
			base_form.findField('LpuSectionProfile_id').setValue(null);
		}
	},
	show: function() {
		sw.Promed.swEvnUslugaEditWindow.superclass.show.apply(this, arguments);

		this.findById('EUComEF_EvnAggPanel').collapse();
		this.findById('EUComEF_AttributeValuePanel').collapse();
		this.findById('EUComEF_EvnUslugaPanel').expand();

		this.restore();
		this.center();

		var base_form = this.FormPanel.getForm(),
			PersonAge;
		base_form.reset();

		this.action = null;
		this.callback = Ext.emptyFn;
		this.onHide = Ext.emptyFn;
		this.parentClass = null;
		this.LpuUnitType_Code = null;
		this.isVisibleDisDTPanel = false;
		this.isVisibleExecutionPanel = false;
		this.isPriem = false;
		this.only351Group = false;
		this.loadingInProgress = false;
		this.UslugaComplex_Date = null;
		this.isEco = false;

        var wnd = this;
		if ( !arguments[0] || !arguments[0].formParams ) {
			sw.swMsg.alert(langs('Сообщение'), langs('Неверные параметры'), function() {
                wnd.hide();
			});
			return false;
		}

		if ( arguments[0].action ) {
			this.action = arguments[0].action;
		}

		this.ignorePaidCheck = null;
		if ( arguments[0].ignorePaidCheck ) {
			this.ignorePaidCheck = arguments[0].ignorePaidCheck;
		}

		if ( arguments[0].callback ) {
			this.callback = arguments[0].callback;
		}

		if ( arguments[0].onHide ) {
			this.onHide = arguments[0].onHide;
		}

		if ( arguments[0].parentClass ) {
			this.parentClass = arguments[0].parentClass;
		}
		
		if ( arguments[0].LpuUnitType_Code ) {
			this.LpuUnitType_Code = arguments[0].LpuUnitType_Code;
		}

		if ( arguments[0].only351Group ) {
			this.only351Group = arguments[0].only351Group;
		}

		if ( arguments[0].UslugaComplex_Date ) {
			this.UslugaComplex_Date = arguments[0].UslugaComplex_Date;
		}
		
		this.notFilterByEvnVizitMes = null;
		if ( arguments[0].notFilterByEvnVizitMes ) {
			this.notFilterByEvnVizitMes = arguments[0].notFilterByEvnVizitMes;
		}
		
		this.MesOldVizit_id = null;
		if ( arguments[0].MesOldVizit_id ) {
			this.MesOldVizit_id = arguments[0].MesOldVizit_id;
		}

		this.PrescriptionType_Code = arguments[0].PrescriptionType_Code || null;

		this.toggleVisibleDisDTPanel('hide');
		this.toggleVisibleExecutionPanel('hide');
		this.toggleVisibleExecutionBtnPanel();

		base_form.findField('EvnUslugaCommon_IsMinusUsluga').hideContainer();

		var changeUslugaComplexPlaceFilter = (getRegionNick() != 'ekb' || getUslugaOptions().enable_usluga_section_load || getUslugaOptions().enable_usluga_section_load_filter);

		base_form.findField('UslugaComplex_id').getStore().baseParams.LpuSection_id = null;
		base_form.findField('UslugaComplex_id').getStore().baseParams.Person_id = null;
		base_form.findField('UslugaComplex_id').getStore().baseParams.UslugaComplex_Date = null;
		base_form.findField('UslugaComplex_id').getStore().baseParams.UcplDiag_id = null;
		base_form.findField('UslugaComplex_id').lastQuery = '';

		base_form.findField('UslugaComplexTariff_id').clearParams();

		base_form.findField('EvnUslugaCommon_pid').getStore().removeAll();
		base_form.findField('Org_uid').disable();
		base_form.findField('Lpu_uid').disable();
		base_form.findField('LpuSection_uid').disable();
		base_form.findField('MedStaffFact_id').disable();
		base_form.findField('Org_uid').disable();

		this.buttons[1].setDisabled(isMseDepers());

		//https://redmine.swan.perm.ru/issues/12875
		if(this.parentClass == 'EvnVizit' || this.parentClass == 'EvnPL' || this.parentClass == 'EvnPLStom')
			base_form.findField('EvnUslugaCommon_pid').setFieldLabel(langs('Посещение'));
		else
			base_form.findField('EvnUslugaCommon_pid').setFieldLabel(langs('Движение'));

		if ( arguments[0].parentEvnComboData ) {
			base_form.findField('EvnUslugaCommon_pid').getStore().loadData(arguments[0].parentEvnComboData);
		}

        this.doSave = arguments[0].doSave || this.doSave_default;
        this.FormPanel.getForm().url = arguments[0].formUrl || this.FormPanel.getForm().default_url;
        this.FormPanel.getForm().formLoadUrl = arguments[0].formLoadUrl || this.FormPanel.getForm().default_formLoadUrl;
        this.EvnClass_SysNick = arguments[0].EvnClass_SysNick || 'EvnUslugaCommon';

        this.findById('EUComEF_EvnAggPanel').isLoaded = ( this.action == 'add' );
        this.findById('EUComEF_AttributeValuePanel').isLoaded = ( this.action == 'add' );

		base_form.setValues(arguments[0].formParams);

		var evn_combo = base_form.findField('EvnUslugaCommon_pid');
		var lpu_combo = base_form.findField('Lpu_uid');
		var lpu_section_combo = base_form.findField('LpuSection_uid');
		var med_personal_combo = base_form.findField('MedStaffFact_id');
		var org_combo = base_form.findField('Org_uid');
		var usluga_place_combo = base_form.findField('UslugaPlace_id');
		var usluga_combo = base_form.findField('UslugaComplex_id');
        var prescr_combo = base_form.findField('EvnPrescr_id');
        var pay_type_combo = base_form.findField('PayType_id');
        var diag_set_class_combo = base_form.findField('DiagSetClass_id');
        var diag_combo = base_form.findField('Diag_id');

		var PersonAge = swGetPersonAge(arguments[0].Person_Birthday, new Date()) || null;
		if (PersonAge != -1){
			usluga_combo.getStore().baseParams.PersonAge = PersonAge;
		}

		if (getRegionNick() == 'ekb' && this.parentClass.inlist(['EvnVizit','EvnSection','EvnPL','EvnPS'])) {
			diag_set_class_combo.setAllowBlank(false);
			diag_set_class_combo.setContainerVisible(true);
			diag_combo.setAllowBlank(false);
			diag_combo.setContainerVisible(true);
		} else {
			diag_set_class_combo.setAllowBlank(true);
			diag_set_class_combo.setContainerVisible(false);
			diag_combo.setAllowBlank(true);
			diag_combo.setContainerVisible(false);
		}

		this.PersonInfo.load({
			Person_id: (arguments[0].Person_id ? arguments[0].Person_id : ''),
			Person_Birthday: (arguments[0].Person_Birthday ? arguments[0].Person_Birthday : ''),
			Person_Firname: (arguments[0].Person_Firname ? arguments[0].Person_Firname : ''),
			Person_Secname: (arguments[0].Person_Secname ? arguments[0].Person_Secname : ''),
			Person_Surname: (arguments[0].Person_Surname ? arguments[0].Person_Surname : ''),
			callback: function() {
				var field = base_form.findField('EvnUslugaCommon_setDate');
				if (Ext.isEmpty(PersonAge) || PersonAge == -1) {
					PersonAge = swGetPersonAge(wnd.PersonInfo.getFieldValue('Person_Birthday'), new Date());
					usluga_combo.getStore().baseParams.PersonAge = PersonAge;
				}

				clearDateAfterPersonDeath('personpanelid', 'EUComEF_PersonInformationFrame', field);
			}.createDelegate(this)
		});

        prescr_combo.clearBaseParams();
        prescr_combo.getStore().removeAll();
        prescr_combo.uslugaCombo = usluga_combo;
        prescr_combo.uslugaCatCombo = base_form.findField('UslugaCategory_id');
        prescr_combo.hasLoaded = false;

        this.uslugaPanel.doReset();
        this.uslugaPanel.setVisible(false);

		this.EvnXmlPanel.setReadOnly(false);
        this.EvnXmlPanel.doReset();
        this.EvnXmlPanel.collapse();
        this.EvnXmlPanel.LpuSectionField = lpu_section_combo;
        this.EvnXmlPanel.MedStaffFactField = med_personal_combo;

		this.FileUploadPanel.reset();
		this.FileUploadPanel.setDisabled(false);

		if ( getRegionNick() == 'ufa' && !Ext.isEmpty(this.parentClass) && this.parentClass.inlist([ 'EvnPS', 'EvnSection']) ) {
			lpu_section_combo.disableLinkedElements();
			med_personal_combo.disableParentElement();
		}
		else {
			lpu_section_combo.enableLinkedElements();
			med_personal_combo.enableParentElement();
		}

		base_form.findField('MedSpecOms_id').onShowWindow(this);
		base_form.findField('LpuSectionProfile_id').onShowWindow(this);

		var loadMask = new Ext.LoadMask(this.getEl(), {msg: LOAD_WAIT});
		loadMask.show();

		this.EvnUslugaAttributeValueGrid.getGrid().getStore().removeAll();
		this.EvnUslugaAttributeValueGrid.setReadOnly(false);
		this.AttributeValuePanel.hide();

		this.findById('EUComEF_EvnAggGrid').getStore().removeAll();
		this.findById('EUComEF_EvnAggGrid').getTopToolbar().items.items[0].enable();
		this.findById('EUComEF_EvnAggGrid').getTopToolbar().items.items[1].disable();
		this.findById('EUComEF_EvnAggGrid').getTopToolbar().items.items[2].disable();
		this.findById('EUComEF_EvnAggGrid').getTopToolbar().items.items[3].disable();

		var evn_usluga_common_pid = null;
		
		base_form.findField('EvnClass_SysNick').setValue(this.EvnClass_SysNick);

		if ( !getRegionNick().inlist(['ekb']) ) {
			// https://redmine.swan.perm.ru/issues/16610
			// https://redmine.swan.perm.ru/issues/18276
			// https://redmine.swan.perm.ru/issues/43012
			if ( getRegionNick().inlist([ 'kareliya', 'adygeya' ]) ) {
				usluga_combo.setDisallowedUslugaComplexAttributeList([ 'oper', 'vizit' ]);
			}
			else if ( getRegionNick().inlist([ 'perm', 'pskov' ]) ) {
				// оказалось такие услуги выбирать можно refs #76264
				// usluga_combo.setDisallowedUslugaComplexAttributeList([ 'vizit' ]);
			}
			else {
				// для стомат услуг эта форма тоже используется при добавлении услуг в ТАП
				usluga_combo.setDisallowedUslugaComplexAttributeList([ 'oper', 'stom', 'vizit' ]);
			}

			if ( getRegionNick().inlist([ 'kareliya' ]) ) {
				base_form.findField('UslugaCategory_id').lastQuery = '';
				base_form.findField('UslugaCategory_id').getStore().filterBy(function(rec) {
					return !(rec.get('UslugaCategory_SysNick').inlist([ 'stomoms', 'stomklass' ]));
				});
			}
		} else if ( getRegionNick().inlist(['ekb']) ) {
			this.setUslugaComplexPartitionCodeList(pay_type_combo.getFieldValue('PayType_SysNick'));

            usluga_combo.getStore().baseParams.notFilterByEvnVizitMes = this.notFilterByEvnVizitMes;
            usluga_combo.getStore().baseParams.MesOldVizit_id = this.MesOldVizit_id;
		}
		if (getRegionNick() == 'buryatiya') {
			usluga_combo.setPersonId(base_form.findField('Person_id').getValue());
		}
		
		if ( arguments[0] && arguments[0].allowDispSomeAdultLabOnly ) {
            usluga_combo.getStore().baseParams.allowDispSomeAdultLabOnly = 1;
		}
		
		if ( arguments[0] && arguments[0].Sex_Code ) {
            usluga_combo.getStore().baseParams.Sex_Code = arguments[0].Sex_Code;
		}
		
		// Для Консультанта убираем все фильтры, кроме consul
		if (
			( arguments[0].formParams.VizitType_SysNick && arguments[0].formParams.VizitType_SysNick == 'consul') 
			|| this.PrescriptionType_Code == '13'
		) {
			usluga_combo.setDisallowedUslugaComplexAttributeList();
			usluga_combo.setAllowedUslugaComplexAttributeList([ 'consul' ]);
		}
		
        if (!prescr_combo.getValue() && 'add' == this.action && evn_combo.getStore().getCount() > 0) {
            /*
             При создании событий оказания услуги,
             у которых родительским событием будет движение или посещение,
             можно выбрать пакет услуг
             */
            usluga_combo.getStore().baseParams.withoutPackage = 0;
        } else {
            // во всех остальных случаях НЕЛЬЗЯ
            usluga_combo.getStore().baseParams.withoutPackage = 1;
            this.findById('EUComEF_EvnAggPanel').setDisabled(false);
            this.findById('EUComEF_AttributeValuePanel').setDisabled(false);
        }
        usluga_combo.setUslugaComplex2011Id(null);
        usluga_combo.setPrescriptionTypeCode(null);

        wnd.withoutEvnDirection = 1;


		base_form.findField('UslugaMedType_id').setContainerVisible(getRegionNick() === 'kz');


		switch ( this.action ) {
			case 'add':
				this.setTitle(WND_POL_EUCOMADD);
				this.enableEdit(true);

				usluga_combo.clearValue();
				usluga_combo.getStore().removeAll();
				
				base_form.findField('EvnUslugaCommon_Kolvo').setValue(1);

				base_form.findField('UslugaComplex_id').getStore().baseParams.Person_id = base_form.findField('Person_id').getValue();

				var PayType_SysNick = getRegionNick() != 'kz' ? getPayTypeSysNickOms() : '';
				if (pay_type_combo.getFieldValue('PayType_SysNick')) {
					PayType_SysNick = pay_type_combo.getFieldValue('PayType_SysNick');
				}

				pay_type_combo.setFieldValue('PayType_SysNick', PayType_SysNick);
				pay_type_combo.fireEvent('select', pay_type_combo, pay_type_combo.getStore().getAt(pay_type_combo.getStore().findBy(function(rec) {
					return (rec.get('PayType_SysNick') == PayType_SysNick);
				})));
				pay_type_combo.setDisabled(getRegionNick().inlist(['ekb']) && 'bud' == pay_type_combo.getFieldValue('PayType_SysNick'));
				
				LoadEmptyRow(this.findById('EUComEF_EvnAggGrid'));

				var lpu_section_id = lpu_section_combo.getValue();
                var msf_id = med_personal_combo.getValue();
				var set_date = false;

				if ( null != this.parentClass && this.parentClass.inlist([ 'EvnPLStom', 'EvnVizit', 'EvnPrescr', 'EvnSection' ]) ) {
					evn_combo.disable();
				}

				var index;
				var ucat_cmb = base_form.findField('UslugaCategory_id');
				var ucat_rec;

				if ( ucat_cmb.getStore().getCount() == 1 ) {
					ucat_cmb.disable();
					ucat_rec = ucat_cmb.getStore().getAt(0);
					ucat_cmb.setValue(ucat_rec.get('UslugaCategory_id'));
				}
				else {
					// Для Перми по умолчанию подставляем услуги ГОСТ-2011
					// https://redmine.swan.perm.ru/issues/53028
					if ( getRegionNick().inlist([ 'perm', 'pskov', 'adygeya' ]) ) {
						index = ucat_cmb.getStore().findBy(function(rec) {
							return (rec.get('UslugaCategory_SysNick') == 'gost2011');
						});
						ucat_rec = ucat_cmb.getStore().getAt(index);
					}
					// Закомментил для Калуги http://redmine.swan.perm.ru/issues/95142
					/*else if ( getRegionNick().inlist([ 'kaluga' ]) ) {
						index = ucat_cmb.getStore().findBy(function(rec) {
							return (rec.get('UslugaCategory_SysNick') == 'lpusectiontree');
						});
						ucat_rec = ucat_cmb.getStore().getAt(index);
					}*/
					else if ( getRegionNick().inlist([ 'ekb' ]) ) {
						index = ucat_cmb.getStore().findBy(function(rec) {
							return (rec.get('UslugaCategory_SysNick') == 'tfoms');
						});
						ucat_rec = ucat_cmb.getStore().getAt(index);
					}
					else if ( this.parentClass == 'PersonDisp' && getGlobalOptions().region && getGlobalOptions().region.nick == 'kareliya' ) {
						index = ucat_cmb.getStore().findBy(function(rec) {
							return (rec.get('UslugaCategory_SysNick') == 'gost2011');
						});
						ucat_rec = ucat_cmb.getStore().getAt(index);
						ucat_cmb.disable();
					} else {
						ucat_rec = ucat_cmb.getStore().getById(ucat_cmb.getValue());
					}
				}

				if ( ucat_rec ) {
					ucat_cmb.fireEvent('select', ucat_cmb, ucat_rec);
				}

				var diag_set_class_id = diag_set_class_combo.getValue();
				if ( !Ext.isEmpty(diag_combo.getValue()) ) {
					diag_combo.getStore().load({
						callback: function() {
							diag_combo.setValue(diag_combo.getValue());
							diag_combo.onChange(diag_combo, diag_combo.getValue());

							if (diag_set_class_combo.getStore().findBy(function(rec) { return rec.get('DiagSetClass_id') == diag_set_class_id; }) >= 0) {
								diag_set_class_combo.setValue(diag_set_class_id);
							}
						},
						params: {where: "where DiagLevel_id = 4 and Diag_id = " + diag_combo.getValue()}
					});
				}

				base_form.findField('LpuSectionProfile_id').disableLoad = true;
				setCurrentDateTime({
					callback: function() {
                        loadMask.hide();
                        base_form.findField('EvnUslugaCommon_setDate').fireEvent('change', base_form.findField('EvnUslugaCommon_setDate'), base_form.findField('EvnUslugaCommon_setDate').getValue());
                        usluga_place_combo.fireEvent('change', usluga_place_combo, null);

						if ( evn_combo.getStore().getCount() > 0 /*&& (this.parentClass == 'EvnPLStom' || this.parentClass == 'EvnVizit')*/ ) {
							evn_combo.setValue(evn_combo.getStore().getAt(0).get('Evn_id'));
							prescr_combo.setPrescriptionTypeCode(wnd.PrescriptionType_Code);
							prescr_combo.getStore().baseParams.withoutEvnDirection = wnd.withoutEvnDirection;
							prescr_combo.getStore().baseParams.EvnPrescr_pid = evn_combo.getStore().getAt(0).get('Evn_id');
							usluga_combo.getStore().baseParams.LpuSection_pid = lpu_section_id;
							prescr_combo.enable();
						} else {
							usluga_combo.getStore().baseParams.LpuSection_pid = null;
							set_date = true;
							prescr_combo.disable();
						}

                        if ( prescr_combo.getValue() ) {
                            prescr_combo.getStore().baseParams.newEvnPrescr_id = prescr_combo.getValue();
                            // при выполнении назначения с оказанием услуги
                            // нужно автоматически подставлять совпадающую по эталонным полям услугу,
                            // на комбо услуг накладывать дополнительный фильтр по атрибуту услуги соответственно типу назначения
                            prescr_combo.getStore().load({
                                callback: function(){
                                    // чтобы НЕ дать возможность выбрать другое назначение
                                    prescr_combo.hasLoaded = true;
                                    prescr_combo.setValue(prescr_combo.getValue());
                                    index = prescr_combo.getStore().findBy(function(rec) {
                                        return (rec.get(prescr_combo.valueField) == prescr_combo.getValue());
                                    });
                                    var rec = prescr_combo.getStore().getAt(index);
                                    if (rec) {
                                        /*if (rec.get('EvnPrescr_setDate')) {
                                            base_form.findField('EvnUslugaCommon_setDate').setValue(rec.get('EvnPrescr_setDate'));
                                        } else {
                                            base_form.findField('EvnUslugaCommon_setDate').setValue(getGlobalOptions().date);
                                        }*/
										base_form.findField('EvnUslugaCommon_setDate').setValue(getGlobalOptions().date);
                                        base_form.findField('EvnUslugaCommon_setDate').fireEvent('change', base_form.findField('EvnUslugaCommon_setDate'), base_form.findField('EvnUslugaCommon_setDate').getValue());

                                        //если услуга добавляется по назначению, то
                                        //если ЛПУ назначения и места выполнения равны
                                        if ( rec.get('Lpu_id') == getGlobalOptions().lpu_id) {
                                            //указываем место выполнение Отделение ЛПУ
                                            usluga_place_combo.setValue(1);
                                            usluga_place_combo.fireEvent('change', usluga_place_combo, 1);

                                            index = lpu_section_combo.getStore().findBy(function(rec) {
                                                return ( rec.get('LpuSection_id') == lpu_section_id );
                                            });

                                            if ( index >= 0 ) {
                                                lpu_section_combo.setValue(lpu_section_id);
												this.isPriem = (lpu_section_combo.getFieldValue('LpuSectionProfile_SysNick') == 'priem');
                                                lpu_section_combo.fireEvent('change', lpu_section_combo, lpu_section_id);
                                            }
                                        } else {
                                            //указываем место выполнение Другое ЛПУ
                                            usluga_place_combo.setValue(2);
                                            lpu_section_combo.setValue(null);
                                            usluga_place_combo.fireEvent('change', usluga_place_combo, 2);
                                            lpu_combo.getStore().load({
                                                callback: function(records, options, success) {
                                                    if (success && records.length>0) {
                                                        lpu_combo.setValue(records[0].get(lpu_combo.valueField));
                                                    } else {
                                                        lpu_combo.setValue(null);
                                                    }
                                                },
                                                params: {
                                                    Lpu_oid: getGlobalOptions().lpu_id,
                                                    OrgType: 'lpu'
                                                }
                                            });
                                        }
                                    }
                                    prescr_combo.fireEvent('change', prescr_combo, prescr_combo.getValue());
                                },
                                params: {
                                    EvnPrescr_id: prescr_combo.getValue()
                                }
                            });
                        }
                        else {
                            if ( this.parentClass != 'EvnPLStom' )
                            {
                                if (evn_combo.getStore().getAt(0)) {
                                    evn_combo.fireEvent('change', evn_combo, evn_combo.getStore().getAt(0).get('Evn_id'), 0);
                                }
                            }
                        }

                        if ( !evn_combo.disabled ) {
                            evn_combo.focus(true, 250);
                        }
                        else if ( !base_form.findField('EvnUslugaCommon_setDate').disabled ) {
                            base_form.findField('EvnUslugaCommon_setDate').focus(true, 250);
                        }
                        else {
                            base_form.findField('EvnUslugaCommon_setTime').focus(true, 250);
                        }

						if ( Ext.isEmpty(med_personal_combo.getValue()) && !med_personal_combo.disabled ) {
							// https://redmine.swan.perm.ru/issues/33271
							// разбил фильтр на 2
							// сначала ищем запись по MedStaffFact_id
							index = med_personal_combo.getStore().findBy(function(rec) {
								return (rec.get(med_personal_combo.valueField) == msf_id);
							});

							// затем первую по отделению
							if ( index == -1 ) {
								index = med_personal_combo.getStore().findBy(function(rec) {
									return (rec.get('LpuSection_id') == lpu_section_id);
								});
							}

							if ( index >= 0 ) {
								med_personal_combo.setValue(med_personal_combo.getStore().getAt(index).get(med_personal_combo.valueField));
							}
						}

						base_form.findField('LpuSectionProfile_id').disableLoad = false;
						base_form.findField('LpuSectionProfile_id').loadStore();

                        //base_form.clearInvalid();
                        base_form.items.each(function(f) {
                            f.validate();
                        });
					},
					dateField: base_form.findField('EvnUslugaCommon_setDate'),
					loadMask: false,
					setDate: set_date,
					setDateMaxValue: true,
					setDateMinValue: false,
					setTime: true,
					timeField: base_form.findField('EvnUslugaCommon_setTime'),
					windowId: this.id
				});
				
				if (!getRegionNick().inlist(['astra', 'ekb']) && arguments[0].parentEvnComboData && arguments[0].parentEvnComboData[0] && arguments[0].parentEvnComboData[0].LpuSectionProfile_id) {
					var lpu_section_profile_id = arguments[0].parentEvnComboData[0].LpuSectionProfile_id;
					setTimeout(function(){
						base_form.findField('LpuSectionProfile_id').setValue(lpu_section_profile_id);
						base_form.findField('UslugaComplex_id').getStore().baseParams.LpuSectionProfile_id = lpu_section_profile_id;
					}, 250);
				}

				if (getRegionNick() === 'kz') {
					base_form.findField('UslugaMedType_id').setFieldValue('UslugaMedType_Code', '1400');
					pay_type_combo.disable();
				}
			break;

			case 'edit':
			case 'view':
				var EvnClass = 'EvnUslugaCommon';
				var evn_usluga_common_id = base_form.findField('EvnUslugaCommon_id').getValue();

				if ( !evn_usluga_common_id ) {
					loadMask.hide();
					this.hide();
					return false;
				}

				wnd.loadingInProgress = true;
				base_form.findField('LpuSectionProfile_id').disableLoad = true;
				base_form.load({
					failure: function() {
						loadMask.hide();

						sw.swMsg.alert(langs('Ошибка'), langs('Ошибка при загрузке данных формы'), function() {
                            wnd.hide();
						});
					},
					params: {
						'class': EvnClass,
						'id': evn_usluga_common_id,
						archiveRecord: wnd.archiveRecord
					},
					success: function(form, response) {
						// В зависимости от accessType переопределяем this.action
						if ( base_form.findField('accessType').getValue() == 'view' ) {
                            wnd.action = 'view';
						}

						var diag_set_class_id = diag_set_class_combo.getValue();
						if ( !Ext.isEmpty(diag_combo.getValue()) ) {
							diag_combo.getStore().load({
								callback: function() {
									diag_combo.setValue(diag_combo.getValue());
									diag_combo.onChange(diag_combo, diag_combo.getValue());

									if (diag_set_class_combo.getStore().findBy(function(rec) { return rec.get('DiagSetClass_id') == diag_set_class_id; }) >= 0) {
										diag_set_class_combo.setValue(diag_set_class_id);
									}
								},
								params: {where: "where DiagLevel_id = 4 and Diag_id = " + diag_combo.getValue()}
							});
						}

						var kolvo = base_form.findField('EvnUslugaCommon_Kolvo').getValue();

						if ( wnd.action == 'edit' ) {
                            wnd.setTitle(WND_POL_EUCOMEDIT);
                            wnd.enableEdit(true);
						}
						else {
                            wnd.setTitle(WND_POL_EUCOMVIEW);
                            wnd.enableEdit(false);
						}

						if ( wnd.action == 'edit' ) {
							setCurrentDateTime({
								dateField: base_form.findField('EvnUslugaCommon_setDate'),
								loadMask: false,
								setDate: false,
								setDateMaxValue: true,
								windowId: wnd.id
							});
						}
						else {
                            wnd.findById('EUComEF_EvnAggGrid').getTopToolbar().items.items[0].disable();
							wnd.EvnUslugaAttributeValueGrid.setReadOnly(true);
						}

						base_form.findField('UslugaComplex_id').getStore().baseParams.Person_id = base_form.findField('Person_id').getValue();

						var setDate = base_form.findField('EvnUslugaCommon_setDate').getValue();
						var setTime = base_form.findField('EvnUslugaCommon_setTime').getValue();
						var disDate = base_form.findField('EvnUslugaCommon_disDate').getValue();
						var disTime = base_form.findField('EvnUslugaCommon_disTime').getValue();

						if ((!Ext.isEmpty(disDate) || !Ext.isEmpty(disTime)) && (disDate-setDate != 0 || setTime != disTime)) {
							this.toggleVisibleDisDTPanel('show');
						}

						if ( base_form.findField('EvnUslugaCommon_id').getValue() > 0) {
							wnd.EvnXmlPanel.setReadOnly('view' == wnd.action);
							wnd.EvnXmlPanel.setBaseParams({
								userMedStaffFact: sw.Promed.MedStaffFactByUser.last,
								UslugaComplex_id: usluga_combo.getValue(),
								Server_id: base_form.findField('Server_id').getValue(),
								Evn_id: base_form.findField('EvnUslugaCommon_id').getValue()
							});
							wnd.EvnXmlPanel.doLoadData();

							//загружаем файлы
							wnd.FileUploadPanel.listParams = {
								Evn_id: base_form.findField('EvnUslugaCommon_id').getValue()
							};
							wnd.FileUploadPanel.loadData({
								Evn_id: base_form.findField('EvnUslugaCommon_id').getValue(),
								callback: function() {
									wnd.FileUploadPanel.setDisabled('view' == wnd.action);
								}
							});
							wnd.FileUploadPanel.setDisabled('view' == wnd.action);
						}

						var evn_usluga_common_pid = evn_combo.getValue();
						var index;
						var lpu_uid = lpu_combo.getValue();
						var lpu_section_uid = lpu_section_combo.getValue();
						var lpu_section_profile_id = base_form.findField('LpuSectionProfile_id').getValue();
						var med_personal_id = base_form.findField('MedPersonal_id').getValue();
						var med_staff_fact_id = base_form.findField('MedStaffFact_id').getValue();
						var org_uid = org_combo.getValue();
						var record;
						var usluga_complex_id = usluga_combo.getValue();
						var usluga_place_id = usluga_place_combo.getValue();
						var UslugaComplexTariff_id = base_form.findField('UslugaComplexTariff_id').getValue();
						var UslugaComplexTariff_UED = base_form.findField('UslugaComplexTariff_UED').getValue();
						var isMinusUsluga = base_form.findField('EvnUslugaCommon_IsMinusUsluga').getValue();

						var ucat_cmb = base_form.findField('UslugaCategory_id');
						var ucat_rec;
						if ( ucat_cmb.getStore().getCount() == 1 ) {
							ucat_cmb.disable();
							ucat_rec = ucat_cmb.getStore().getAt(0);
							ucat_cmb.setValue(ucat_rec.get('UslugaCategory_id'));
						} else if ( wnd.parentClass == 'PersonDisp' && getGlobalOptions().region && getGlobalOptions().region.nick == 'kareliya' ) {
							index = ucat_cmb.getStore().findBy(function(rec) {
								return (rec.get('UslugaCategory_SysNick') == 'gost2011');
							});
							ucat_rec = ucat_cmb.getStore().getAt(index);
							ucat_cmb.disable();
						} else {
							ucat_rec = ucat_cmb.getStore().getById(ucat_cmb.getValue());
						}

						if ( ucat_rec ) {
							ucat_cmb.fireEvent('select', ucat_cmb, ucat_rec);
						}
				
						index = evn_combo.getStore().findBy(function(rec) {
                            return ( rec.get('Evn_id') == evn_usluga_common_pid ) ;
						});
						record = evn_combo.getStore().getAt(index);

						if ( record ) {
							evn_combo.setValue(evn_usluga_common_pid);
                            usluga_combo.getStore().baseParams.EvnUsluga_pid = evn_usluga_common_pid;
							var thas = this;
                            prescr_combo.setPrescriptionTypeCode(thas.PrescriptionType_Code || null);
                            prescr_combo.getStore().baseParams.withoutEvnDirection = thas.withoutEvnDirection;
                            prescr_combo.getStore().baseParams.EvnPrescr_pid = evn_usluga_common_pid;
                            if ( prescr_combo.getValue() ) {
                                prescr_combo.getStore().baseParams.savedEvnPrescr_id = prescr_combo.getValue();
                                prescr_combo.getStore().load({
                                    callback: function(){
                                        // чтобы дать возможность выбрать другое назначение
                                        prescr_combo.hasLoaded = false;
                                        var rec = prescr_combo.getStore().getById(prescr_combo.getValue());
                                        if (rec && rec.get('PrescriptionType_Code')) {
                                            thas.PrescriptionType_Code = rec.get('PrescriptionType_Code');
                                            prescr_combo.setPrescriptionTypeCode(thas.PrescriptionType_Code);
                                        }
                                        prescr_combo.setValue(prescr_combo.getValue());
                                    },
                                    params: {
                                        EvnPrescr_id: prescr_combo.getValue()
                                    }
                                });
                            }
							usluga_combo.getStore().baseParams.LpuSection_pid = record.get('LpuSection_id') || null;
                        } else {

							//Если услуга добавлена из приёмного - подставляем приёмное
							!Ext.isEmpty(response.result.data.EvnUslugaCommon_pid_Name)?evn_combo.setValue(response.result.data.EvnUslugaCommon_pid_Name):evn_combo.clearValue();
                            usluga_combo.getStore().baseParams.EvnUsluga_pid = null;
							usluga_combo.getStore().baseParams.LpuSection_pid = null;
						}
                        // Если есть права на изменение услуги, то назначение должно быть редактируемо
                        prescr_combo.setDisabled(prescr_combo.uslugaCombo.disabled);


						if ( usluga_place_id ) {
							if ( wnd.action == 'edit' ) {
								usluga_place_combo.fireEvent('change', usluga_place_combo, usluga_place_id, -1);
							}

							index = usluga_place_combo.getStore().findBy(function(rec, id) {
                                return ( rec.get('UslugaPlace_id') == usluga_place_id );
							});
							record = usluga_place_combo.getStore().getAt(index);

							if ( !record ) {
								loadMask.hide();
								return false;
							}
							switch ( Number(record.get('UslugaPlace_Code')) ) {
								case 1:
									if ( wnd.action == 'edit' ) {
										base_form.findField('EvnUslugaCommon_setDate').fireEvent('change', base_form.findField('EvnUslugaCommon_setDate'), base_form.findField('EvnUslugaCommon_setDate').getValue());

										if ( !Ext.isEmpty(lpu_section_uid) ) {
											index = base_form.findField('LpuSection_uid').getStore().findBy(function(rec) {
												return (rec.get('LpuSection_id') == lpu_section_uid);
											});
											base_form.findField('LpuSection_uid').fireEvent('beforeselect', base_form.findField('LpuSection_uid'), base_form.findField('LpuSection_uid').getStore().getAt(index), index);
											base_form.findField('LpuSection_uid').setValue(lpu_section_uid);
											this.isPriem = (base_form.findField('LpuSection_uid').getFieldValue('LpuSectionProfile_SysNick') == 'priem');
										}

										if (!Ext.isEmpty(med_staff_fact_id)) {
											base_form.findField('MedStaffFact_id').setValue(med_staff_fact_id);
										} else {

											index = base_form.findField('MedStaffFact_id').getStore().findBy(function(rec, id) {
												return ( rec.get('LpuSection_id') == lpu_section_uid && rec.get('MedPersonal_id') == med_personal_id );
											});
											if(index != -1) {
												record = base_form.findField('MedStaffFact_id').getStore().getAt(index);

												if (record) {
													base_form.findField('MedStaffFact_id').setValue(record.get('MedStaffFact_id'));
												}
											}
										}
									}
									else {
										base_form.findField('MedSpecOms_id').onChangeDateField(base_form.findField('EvnUslugaCommon_setDate'), base_form.findField('EvnUslugaCommon_setDate').getValue());
										base_form.findField('LpuSectionProfile_id').onChangeDateField(base_form.findField('EvnUslugaCommon_setDate'), base_form.findField('EvnUslugaCommon_setDate').getValue());

										base_form.findField('LpuSection_uid').getStore().load({
											callback: function() {
												if ( base_form.findField('LpuSection_uid').getStore().getCount() > 0 ) {
													base_form.findField('LpuSection_uid').setValue(lpu_section_uid);
													this.isPriem = (base_form.findField('LpuSection_uid').getFieldValue('LpuSectionProfile_SysNick') == 'priem');
												}
											}.createDelegate(this),
											params: {
												LpuSection_id: lpu_section_uid
											}
										});

										var params = {};
										if (!Ext.isEmpty(med_staff_fact_id)) {
											params.MedStaffFact_id = med_staff_fact_id;
										} else {
											params.LpuSection_id = lpu_section_uid;
											params.MedPersonal_id = med_personal_id;
										}
										base_form.findField('MedStaffFact_id').getStore().load({
											callback: function() {
												if (!Ext.isEmpty(med_staff_fact_id)) {
													base_form.findField('MedStaffFact_id').setValue(med_staff_fact_id);
												} else {
													index = base_form.findField('MedStaffFact_id').getStore().findBy(function(rec) {
														if ( rec.get('LpuSection_id').toString() == lpu_section_uid.toString() && rec.get('MedPersonal_id').toString() == med_personal_id.toString() ) {
															return true;
														}
														else {
															return false;
														}
													});
													if (index != -1) {
														record = base_form.findField('MedStaffFact_id').getStore().getAt(index);

														if (record) {

															base_form.findField('MedStaffFact_id').setValue(record.get('MedStaffFact_id'));
														}
													}

												}
												
												// Временно: Если оказалось, что врач не указан, а место выполнения = отделение ЛПУ (https://redmine.swan.perm.ru/issues/29599)
												// todo: убрать после мая 2014 года
												if (!med_personal_id) { 
													// то даем возможность выбрать врача
													base_form.findField('MedStaffFact_id').enable();
                                                    wnd.buttons[0].show();
												}
												
											}.createDelegate(this),
											params: params
										});

									}
								break;

								case 2:
									
									// Другое ЛПУ
									lpu_combo.getStore().load({
										callback: function(records, options, success) {
											if (success && records.length>0) {
												lpu_combo.setValue(records[0].get(lpu_combo.valueField));
												base_form.findField('EvnUslugaCommon_IsMinusUsluga').setValue(isMinusUsluga);
												base_form.findField('LpuSection_uid').setValue(lpu_section_uid);
												base_form.findField('MedStaffFact_id').setValue(med_staff_fact_id);
												base_form.findField('LpuSectionProfile_id').setValue(lpu_section_profile_id);
												wnd.setLpuSectionAndMedStaffFactFilter(true);
											} else {
                                                lpu_combo.setValue(null);
                                            }
										},
										params: {
                                            Lpu_oid: lpu_uid,
											OrgType: 'lpu'
										}
									});
								break;

								case 3:
									// Другая организация
									org_combo.getStore().load({
										callback: function(records, options, success) {
											if (success) {
												org_combo.setValue(org_uid);
											}
										},
										params: {
											Org_id: org_uid,
											OrgType: 'org'
										}
									});
								break;

								default:
									loadMask.hide();
									return false;
								break;
							}
							base_form.findField('MedSpecOms_id').onChangeUslugaPlaceField(usluga_place_combo, record.get('UslugaPlace_Code'));
							base_form.findField('LpuSectionProfile_id').onChangeUslugaPlaceField(usluga_place_combo, record.get('UslugaPlace_Code'));
						} else {
							base_form.findField('MedSpecOms_id').onChangeUslugaPlaceField(usluga_place_combo, null);
							base_form.findField('LpuSectionProfile_id').onChangeUslugaPlaceField(usluga_place_combo, null);
						}
						
						setTimeout(function(){
							base_form.findField('LpuSectionProfile_id').setValue(lpu_section_profile_id);
							base_form.findField('UslugaComplex_id').getStore().baseParams.LpuSectionProfile_id = lpu_section_profile_id;
						}, 250);
						
						if ( getRegionNick().inlist(['ekb']) ) {
							this.setUslugaComplexPartitionCodeList(pay_type_combo.getFieldValue('PayType_SysNick'));
						}
						if (changeUslugaComplexPlaceFilter && !Ext.isEmpty(lpu_section_uid) ) {
                            usluga_combo.getStore().baseParams.LpuSection_id = lpu_section_uid;
						}
						usluga_combo.getStore().baseParams.Person_id = base_form.findField('Person_id').getValue();

                        usluga_combo.getStore().load({
							callback: function() {
								if ( usluga_combo.getStore().getCount() > 0 ) {
                                    usluga_combo.setValue(usluga_complex_id);
									usluga_combo.fireEvent('select', usluga_combo, usluga_combo.getStore().getById(usluga_complex_id));
									if (!wnd.checkAttributeValuePanelHidden()) {
										wnd.findById('EUComEF_AttributeValuePanel').expand();
									}
                                    var usluga_category_id = usluga_combo.getStore().getAt(0).get('UslugaCategory_id'),
                                        ucat_cmb = base_form.findField('UslugaCategory_id'),
                                        ucat_rec;
                                    index = ucat_cmb.getStore().findBy(function(rec) {
                                        return (rec.get('UslugaCategory_id') == usluga_category_id);
                                    });
                                    if ( index >= 0 ) {
                                        ucat_rec = ucat_cmb.getStore().getAt(index);
                                        ucat_cmb.setValue(usluga_category_id);
										if (getRegionNick() != 'ekb') {
											// не влияет на выбор услуг для Екб
											usluga_combo.setUslugaCategoryList([ucat_rec.get('UslugaCategory_SysNick')]);
										}
                                    }
								} else {
                                    usluga_combo.clearValue();
								}

								this.toggleVisibleExecutionBtnPanel();
							}.createDelegate(this),
							params: {
								UslugaComplex_id: usluga_complex_id,
								PayType_id: base_form.findField('PayType_id').getValue()
							}
						});

						base_form.findField('UslugaComplexTariff_id').setParams({
							 LpuSection_id: lpu_section_uid
							,PayType_id: base_form.findField('PayType_id').getValue()
							,Person_id: base_form.findField('Person_id').getValue()
							,UslugaComplex_id: usluga_complex_id
							,UslugaComplexTariff_Date: base_form.findField('EvnUslugaCommon_setDate').getValue()
						});

						if ( !Ext.isEmpty(UslugaComplexTariff_id) ) {
							base_form.findField('UslugaComplexTariff_id').getStore().load({
								callback: function() {
									if ( base_form.findField('UslugaComplexTariff_id').getStore().getCount() > 0 ) {
										base_form.findField('UslugaComplexTariff_id').setValue(UslugaComplexTariff_id);
										base_form.findField('UslugaComplexTariff_id').fireEvent('change', base_form.findField('UslugaComplexTariff_id'), UslugaComplexTariff_id);
										base_form.findField('UslugaComplexTariff_UED').setValue(UslugaComplexTariff_UED);
									}
									else {
										base_form.findField('UslugaComplexTariff_id').clearValue();
									}
								}.createDelegate(this),
								params: {
									UslugaComplexTariff_id: UslugaComplexTariff_id
								}
							});
						}

						loadMask.hide();

						//base_form.clearInvalid();
						base_form.items.each(function(f) {
							f.validate();
						});

						if ( wnd.action == 'edit' ) {
							pay_type_combo.setDisabled(getRegionNick().inlist(['ekb']) && 'bud' == pay_type_combo.getFieldValue('PayType_SysNick'));
							if (wnd.parentClass != null || (evn_usluga_common_pid != null && evn_usluga_common_pid.toString().length > 0)) {
								evn_combo.disable();

								base_form.findField('EvnUslugaCommon_setDate').fireEvent('change', base_form.findField('EvnUslugaCommon_setDate'), base_form.findField('EvnUslugaCommon_setDate').getValue());
								base_form.findField('EvnUslugaCommon_setDate').focus(true, 250);
							}
							else {
								evn_combo.focus(true, 250);
							}
						}
						else {
							wnd.buttons[wnd.buttons.length - 1].focus();
						}

						base_form.findField('EvnUslugaCommon_Kolvo').setValue(kolvo);
						if(!Ext.isEmpty(response.result.data.UslugaExecutionType_id)){
							if(response.result.data.UslugaExecutionType_id != 1){
								wnd.toggleVisibleExecutionPanel('show');
							}

							base_form.findField('UslugaExecutionType_id').setValue(response.result.data.UslugaExecutionType_id);
							base_form.findField('UslugaExecutionReason_id').setValue(response.result.data.UslugaExecutionReason_id);
						}

						wnd.checkIsEco();
						wnd.checkAttributeValuePanelHidden();

						base_form.findField('LpuSectionProfile_id').disableLoad = false;
						base_form.findField('LpuSectionProfile_id').loadStore();
						wnd.loadingInProgress = false;
						
						if (getRegionNick() == 'kz') {
							pay_type_combo.disable();
						}

                        return true;
					}.createDelegate(this),
					url: this.FormPanel.getForm().formLoadUrl
				});
			break;

			default:
				loadMask.hide();
				this.hide();
			break;
		}
	},
	width: 700,
	toggleVisibleExecutionBtnPanel: function() {
		var
			base_form = this.FormPanel.getForm(),
			win = this;

		if ( getRegionNick() == 'perm' ) {
			var UslugaComplex_AttributeList = base_form.findField('UslugaComplex_id').getFieldValue('UslugaComplex_AttributeList');

			if (
				win.parentClass.inlist(['EvnPS','EvnSection'])
				|| (
					!Ext.isEmpty(UslugaComplex_AttributeList)
					&& UslugaComplex_AttributeList.indexOf('kab_early_zno') != -1
				)
			) {
				win.findById('EUComEF_ToggleVisibleExecutionPanel').show();
			}
			else {
				win.findById('EUComEF_ToggleVisibleExecutionPanel').hide();
				win.toggleVisibleExecutionPanel('hide');
			}
		}
		else {
			win.findById('EUComEF_ToggleVisibleExecutionPanel').hide();
			win.toggleVisibleExecutionPanel('hide');
		}
	},
	toggleVisibleExecutionPanel: function(action){

		var win = this,
			base_form = win.FormPanel.getForm();

		if (action == 'show') {
			win.isVisibleExecutionPanel = false;
		} else if (action == 'hide') {
			win.isVisibleExecutionPanel = true;
		}
		if(!win.findById('EUComEF_EvnUslugaExecutionPanel').isVisible() && !action){
			win.isVisibleExecutionPanel = false;
		}

		if (win.isVisibleExecutionPanel) {
			win.findById('EUComEF_EvnUslugaExecutionPanel').hide();
			win.findById('EUComEF_ToggleVisibleExecutionPanelBtn').setText(langs('Уточнить объём выполнения'));
			base_form.findField('UslugaExecutionReason_id').setAllowBlank(true);
			if(win.action === 'add'){
				base_form.findField('UslugaExecutionReason_id').clearValue();
				base_form.findField('UslugaExecutionType_id').reset();
			}

			win.isVisibleExecutionPanel = false;
		} else {
			win.findById('EUComEF_EvnUslugaExecutionPanel').show();
			win.findById('EUComEF_ToggleVisibleExecutionPanelBtn').setText(langs('Скрыть объём выполнения'));
			win.isVisibleExecutionPanel = true;

			if ( win.parentClass.inlist(['EvnPS','EvnSection']) ) {
				base_form.findField('UslugaExecutionType_id').items.items[1].show();
			}
			else {
				base_form.findField('UslugaExecutionType_id').items.items[1].hide();
			}

			base_form.findField('UslugaExecutionReason_id').getStore().clearFilter();

			if ( !win.parentClass.inlist(['EvnPS','EvnSection']) ) {
				base_form.findField('UslugaExecutionReason_id').lastQuery = '';
				base_form.findField('UslugaExecutionReason_id').getStore().filterBy(function(rec) {
					return (rec.get('UslugaExecutionReason_id') == 1 || rec.get('UslugaExecutionReason_id') == 2);
				});
			}
		}
	}
});

