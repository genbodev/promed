/**
* swEvnPSPriemEditWindow - Поступление пациента в приемное отделение.
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
* @comment      Префикс для id компонентов EPSPEF (EvnPSPriemEditForm)
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
*             окно редактирования осмотров беспризорных (swPrehospWaifInspectionEditWindow)
*             окно редактирования общей услуги (swEvnUslugaCommonEditWindow)
*             окно добавления оперативной услуги (swEvnUslugaOperEditWindow)
*/
sw.Promed.swEvnPSPriemEditWindow = Ext.extend(sw.Promed.BaseForm, {
	title: lang['postuplenie_patsienta_v_priemnoe_otdelenie'],
	buttonAlign: 'left',
	closable: false,
	closeAction: 'hide',
	collapsible: true,
	plain: true,
	draggable: true,
	height: 550,
	id: 'EvnPSPriemEditWindow',
	width: 800,
	resizable: true,
	maximizable: true,
	minHeight: 550,
	minWidth: 800,
	modal: true,
	layout: 'border',
	
	action: null,
	callback: Ext.emptyFn,
	onHide: Ext.emptyFn,
	onCancelAction: Ext.emptyFn,
	formStatus: 'edit',
	evnPSAbortStore: null,
	firstRun: true,
	isCopy: false,
	form_panels: [
		'EPSPEF_HospitalisationPanel',
		'EPSPEF_DirectDiagPanel',
		'EPSPEF_AdmitDepartPanel',
		'EPSPEF_AdmitDiagPanel',
		'EPSPEF_PriemLeavePanel',
		'EPSPEF_EvnUslugaPanel',
		'EPSPEF_EvnDrugPanel',
		'EPSPEF_PrehospWaifPanel'
	],
	listeners: {
		'hide': function(win) {
			win.onHide();
		},
		'maximize': function(win) {
			for(var i = 0; i < win.form_panels.length; i++)
			{
				if(!win.findById(win.form_panels[i]).hidden)
				{
					win.findById(win.form_panels[i]).doLayout();
				}
			}
		},
		'restore': function(win) {
			win.fireEvent('maximize', win);
		}
	},
	keys: [{
		alt: true,
		fn: function(inp, e) {
			var current_window = Ext.getCmp('EvnPSPriemEditWindow');

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
					if ( !current_window.findById('EPSPEF_HospitalisationPanel').hidden ) {
						current_window.findById('EPSPEF_HospitalisationPanel').toggleCollapse();
					}
				break;

				case Ext.EventObject.NUM_TWO:
				case Ext.EventObject.TWO:
					if ( !current_window.findById('EPSPEF_DirectDiagPanel').hidden ) {
						current_window.findById('EPSPEF_DirectDiagPanel').toggleCollapse();
					}
				break;

				case Ext.EventObject.NUM_THREE:
				case Ext.EventObject.THREE:
					if ( !current_window.findById('EPSPEF_AdmitDepartPanel').hidden ) {
						current_window.findById('EPSPEF_AdmitDepartPanel').toggleCollapse();
					}
				break;

				case Ext.EventObject.FOUR:
				case Ext.EventObject.NUM_FOUR:
					if ( !current_window.findById('EPSPEF_AdmitDiagPanel').hidden ) {
						current_window.findById('EPSPEF_AdmitDiagPanel').toggleCollapse();
					}
				break;

				case Ext.EventObject.FIVE:
				case Ext.EventObject.NUM_FIVE:
					if ( !current_window.findById('EPSPEF_PriemLeavePanel').hidden ) {
						current_window.findById('EPSPEF_PriemLeavePanel').toggleCollapse();
					}
				break;

				case Ext.EventObject.NUM_SIX:
				case Ext.EventObject.SIX:
					if ( !current_window.findById('EPSPEF_EvnUslugaPanel').hidden ) {
						current_window.findById('EPSPEF_EvnUslugaPanel').toggleCollapse();
					}
				break;

				case Ext.EventObject.NUM_SEVEN:
				case Ext.EventObject.SEVEN:
					if ( !current_window.findById('EPSPEF_EvnDrugPanel').hidden ) {
						current_window.findById('EPSPEF_EvnDrugPanel').toggleCollapse();
					}
				break;

				case Ext.EventObject.EIGHT:
				case Ext.EventObject.NUM_EIGHT:
					if ( !current_window.findById('EPSPEF_PrehospWaifPanel').hidden ) {
						current_window.findById('EPSPEF_PrehospWaifPanel').toggleCollapse();
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
		stopEvent: true
	}, {
		alt: false,
		fn: function(inp, e) {
			var current_window = Ext.getCmp('EvnPSPriemEditWindow');

			switch ( e.getKey() ) {
				case Ext.EventObject.F6:
					current_window.findById('EPSPEF_PersonInformationFrame').panelButtonClick(1);
				break;

				case Ext.EventObject.F10:
					current_window.findById('EPSPEF_PersonInformationFrame').panelButtonClick(2);
				break;

				case Ext.EventObject.F11:
					current_window.findById('EPSPEF_PersonInformationFrame').panelButtonClick(3);
				break;

				case Ext.EventObject.F12:
					if ( e.ctrlKey == true ) {
						current_window.findById('EPSPEF_PersonInformationFrame').panelButtonClick(5);
					}
					else {
						current_window.findById('EPSPEF_PersonInformationFrame').panelButtonClick(4);
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
		stopEvent: true
	}],
	deleteEvent: function(event) {
		if ( this.action == 'view' ) {
			return false;
		}

		if ( !event.inlist(['EvnUsluga', 'EvnDiagPSHosp', 'EvnDiagPSRecep', 'EvnDrug']) ) {
			return false;
		}

		var base_form = this.findById('EvnPSPriemEditForm').getForm();
		var error = '';
		var grid = null;
		var question = '';
		var params = new Object();
		var url = '';

		switch ( event ) {
			case 'EvnDrug':
				grid = this.findById('EPSPEF_EvnDrugGrid');
			break;

			case 'EvnUsluga':
				grid = this.findById('EPSPEF_EvnUslugaGrid');
			break;

			case 'EvnDiagPSHosp':
				grid = this.findById('EPSPEF_EvnDiagPSHospGrid');
			break;

			case 'EvnDiagPSRecep':
				grid = this.findById('EPSPEF_EvnDiagPSRecepGrid');
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

		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId, text, obj) {
				if ( buttonId == 'yes' ) {
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

								if ( grid.getStore().getCount() == 0 ) {
									grid.getTopToolbar().items.items[1].disable();
									grid.getTopToolbar().items.items[2].disable();
									grid.getTopToolbar().items.items[3].disable();
									LoadEmptyRow(grid);
								}
							}

							grid.getView().focusRow(0);
							grid.getSelectionModel().selectFirstRow();
						}.createDelegate(this),
						url: url
					});
				}
				else {
					grid.getView().focusRow(0);
					grid.getSelectionModel().selectFirstRow();
				}
			}.createDelegate(this),
			icon: Ext.MessageBox.QUESTION,
			msg: question,
			title: lang['vopros']
		});
	},

	doSave: function(options) {
		// options @Object
		// options.print @Boolean Вызывать печать КВС, если true
		// options.openChildWindow @Function Открыть дочернее окно после сохранения
		if ( this.formStatus == 'save' || this.action == 'view' ) {
			return false;
		}
		
		if ( typeof options != 'object' ) {
			options = new Object();
		}

		this.formStatus = 'save';

		var base_form = this.findById('EvnPSPriemEditForm').getForm();

		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					this.formStatus = 'edit';
					this.findById('EvnPSPriemEditForm').getFirstInvalidEl().focus(false);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		var tmp_bool = (/*base_form.findField('PrehospDirect_id').getValue() == 2 &&*/ base_form.findField('EvnDirection_id').getValue() > 0 && !base_form.findField('Diag_did').getValue());

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

		var priemDiag = (getGlobalOptions().check_priemdiag_allow && getGlobalOptions().check_priemdiag_allow=='1')
		tmp_bool = (priemDiag && !base_form.findField('LpuSection_pid').getValue() > 0 && !base_form.findField('Diag_pid').getValue() && !Ext.isEmpty(base_form.findField('EvnPS_OutcomeDate').getValue()));
		if ( tmp_bool ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					this.formStatus = 'edit';
					base_form.findField('Diag_pid').focus(false);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: lang['pri_vyibrannom_priemnom_otdelenii_pole_osnovnoy_diagnoz_priemnogo_otdeleniya_obyazatelno_dlya_zapolneniya'],
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
		
		var evnps_setdate = base_form.findField('EvnPS_setDate').getValue();
		var evnps_settime = base_form.findField('EvnPS_setTime').getValue();
		var evnps_outcomedate = base_form.findField('EvnPS_OutcomeDate').getValue();
		var evnps_outcometime = base_form.findField('EvnPS_OutcomeTime').getValue();

		if ( evnps_setdate && evnps_outcomedate ){
			if (!evnps_settime) evnps_settime = '00:00';
			if (!evnps_outcometime) evnps_outcometime = '00:00';

			var setDT = Date.parseDate(Ext.util.Format.date(evnps_setdate) + ' ' + evnps_settime, 'd.m.Y H:i');
			var outDT = Date.parseDate(Ext.util.Format.date(evnps_outcomedate) + ' ' + evnps_outcometime, 'd.m.Y H:i');

			if ( outDT < setDT ) {
				this.formStatus = 'edit';
				sw.swMsg.alert(
					langs('Ошибка'),
					langs('Дата и время поступления в стационар') + ' ' + setDT.format('d.m.Y H:i') + ' ' + langs('позже даты и времени исхода пребывания в приемном отделении') + ' ' + outDT.format('d.m.Y H:i'),
					function() {
						base_form.findField('EvnPS_OutcomeDate').focus(false);
					}
				);
				return false;
			}

			if ( !Ext.isEmpty(outDT) && (outDT.getTime() - setDT.getTime()) > 86400000 ) {
				this.formStatus = 'edit';
				sw.swMsg.alert(
					langs('Ошибка'),
					langs('Дата и время поступления в стационар') +' ' + setDT.format('d.m.Y H:i') + ' ' + langs('раньше даты исхода из приемного отделения') + ' ' + outDT.format('d.m.Y H:i') + ' ' + langs('больше, чем на сутки') + '.',
					function() {
						base_form.findField('EvnPS_OutcomeDate').focus(false);
					}
				);
				return false;
			}
		}

		var Person_Birthday = this.findById('EPSPEF_PersonInformationFrame').getFieldValue('Person_Birthday');
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

		var params = new Object();
		var record;
		var med_staff_fact_pid = base_form.findField('MedStaffFact_pid').getValue();
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

		if ( base_form.findField('Diag_did').disabled ) {
			params.Diag_did = base_form.findField('Diag_did').getValue();
		}

		if ( base_form.findField('EvnPS_IsPLAmbulance').disabled ) {
			params.EvnPS_IsPLAmbulance = base_form.findField('EvnPS_IsPLAmbulance').getValue();
		}

		if ( base_form.findField('EvnPS_IsWithoutDirection').disabled ) {
			params.EvnPS_IsWithoutDirection = base_form.findField('EvnPS_IsWithoutDirection').getValue();
		}
		
		params.LpuSection_pid = base_form.findField('LpuSection_pid').getValue();
		params.EvnDirection_setDate = Ext.util.Format.date(base_form.findField('EvnDirection_setDate').getValue(), 'd.m.Y');

		params.EvnPS_disDate = null;
		params.EvnPS_disTime = null;
		
		params.TimetableStac_id = this.TimetableStac_id;
		
		var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Подождите, идет сохранение поступления в приемное отделение..." });
		loadMask.show();

        if ( base_form.findField('LpuSection_eid').disabled ) {
            params.LpuSection_eid = base_form.findField('LpuSection_eid').getValue();
        }
        if ( options && typeof options.openChildWindow == 'function' && this.action == 'add') {
            params.isAutoCreate = 1;
        }

		params.PayType_id = base_form.findField('PayType_id').getValue();

		params.vizit_direction_control_check = (options && !Ext.isEmpty(options.vizit_direction_control_check) && options.vizit_direction_control_check === 1) ? 1 : 0;
		params.ignoreEvnPSDoublesCheck = (options && !Ext.isEmpty(options.ignoreEvnPSDoublesCheck) && options.ignoreEvnPSDoublesCheck === 1) ? 1 : 0;
		params.ignoreEvnPSTimeDeseaseCheck = (!Ext.isEmpty(options.ignoreEvnPSTimeDeseaseCheck) && options.ignoreEvnPSTimeDeseaseCheck === 1) ? 1 : 0;

		base_form.submit({
			params: params,
			failure: function(result_form, action) {
				this.formStatus = 'edit';
				loadMask.hide();

				if ( action.result ) {
					if ( action.result.Alert_Msg ) {
						if ( 'YesNo' == action.result.Error_Msg ) {
							var msg = getMsgForCheckDoubles(action.result);

							sw.swMsg.show({
								buttons: Ext.Msg.YESNO,
								fn: function(buttonId, text, obj) {
									if ( buttonId == 'yes' ) {
										if (action.result.Error_Code == 112) {
											options.vizit_direction_control_check = 1;
										}
										if (action.result.Error_Code == 113) {
											options.ignoreEvnPSDoublesCheck = 1;
										}
										if (action.result.Error_Code == 114) {
											options.ignoreEvnPSTimeDeseaseCheck = 1;
										}
										this.doSave(options);
									}
									else {
										base_form.findField('EvnSection_setDate').focus(true);
									}
								}.createDelegate(this),
								icon: Ext.MessageBox.QUESTION,
								msg: msg,
								title: lang['prodoljit_sohranenie']
							});
						} else {
							sw.swMsg.show({
								buttons: Ext.Msg.YESNO,
								fn: function(buttonId, text, obj) {
									if ( buttonId == 'yes' ) {
										if (action.result.Error_Code == 102) {
											//options.ignoreUslugaComplexTariffCountCheck = 1;
										}

										this.doSave(options);
									}
								}.createDelegate(this),
								icon: Ext.MessageBox.QUESTION,
								msg: action.result.Alert_Msg,
								title: lang['prodoljit_sohranenie']
							});
						}
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
			success: function(result_form, action) {
				this.formStatus = 'edit';
				loadMask.hide();

				if ( action.result ) {
					if ( action.result.EvnPS_id ) {
						var evn_ps_id = action.result.EvnPS_id;

						base_form.findField('EvnPS_id').setValue(evn_ps_id);

						if ( options && typeof options.openChildWindow == 'function' && this.action == 'add' ) {
							options.openChildWindow();
						}
						else {
							var date = null;
							var person_information = this.findById('EPSPEF_PersonInformationFrame');
							var response = new Object();

							response.EvnPS_id = evn_ps_id;
							/*
							response.LpuSection_Name = lpu_section_name;
							response.EvnPS_disDate = Date.parseDate(evn_ps_dis_date, 'd.m.Y');
							
							response.Diag_Name = base_form.findField('Diag_pid').getRawValue();
							response.EvnPS_NumCard = base_form.findField('EvnPS_NumCard').getValue();
							response.EvnPS_setDate = base_form.findField('EvnPS_setDate').getValue();
							response.Person_Birthday = person_information.getFieldValue('Person_Birthday');
							response.Person_Firname = person_information.getFieldValue('Person_Firname');
							response.Person_id = base_form.findField('Person_id').getValue();
							response.Person_Secname = person_information.getFieldValue('Person_Secname');
							response.Person_Surname = person_information.getFieldValue('Person_Surname');
							response.PersonEvn_id = base_form.findField('PersonEvn_id').getValue();
							response.Server_id = base_form.findField('Server_id').getValue();
							*/

							this.callback({ evnPSData: response });

							if ( action.result.Alert_Msg ) {
								sw.swMsg.alert(lang['preduprejdenie'], action.result.Alert_Msg);
							}

							if ( options && options.print == true ) {
								// Определяем тип стационара
								var LpuUnitType_id = base_form.findField('LpuSection_pid').getFieldValue('LpuUnitType_id');

								if ( Ext.isEmpty(LpuUnitType_id) || LpuUnitType_id == 1 ) {
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
							}
							else {
								//this.hide();
								if(options.printRefuse != true)
									this.hide();
								else{
									window.open('/?c=EvnPS&m=printEvnPSPrehospWaifRefuseCause&EvnPS_id=' + evn_ps_id, '_blank');
									window.open('/?c=EvnPS&m=printPatientRefuse&EvnPS_id=' + evn_ps_id, '_blank');
								}
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
	getEvnPSNumber: function() {
		var evn_ps_num_field = this.findById('EvnPSPriemEditForm').getForm().findField('EvnPS_NumCard');

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Получение номера карты выбывшего из стационара..." });
		loadMask.show();

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
			url: '/?c=EvnPS&m=getEvnPSNumber'
		});
	},

	setDiagPAllowBlank: function() {
		var base_form = this.findById('EvnPSPriemEditForm').getForm();
		var date = base_form.findField('EvnPS_OutcomeDate').getValue();
		var priemDiag = (getGlobalOptions().check_priemdiag_allow && getGlobalOptions().check_priemdiag_allow=='1');

		base_form.findField('Diag_pid').setAllowBlank(Ext.isEmpty(date) || !priemDiag);
	},

	getFinanceSource: function() {
		var win = this,
			base_form = this.findById('EvnPSPriemEditForm').getForm();

		if (base_form.findField('EvnPS_IsWithoutDirection').getValue() == 2) return false;

		if (this.action.inlist(['view'])) return false;

		var params = {
			DirType_id: 1,
			EvnPS_id: base_form.findField('EvnPS_id').getValue(),
			EvnDirection_setDate: Ext.util.Format.date(base_form.findField('EvnPS_setDate').getValue(), 'd.m.Y'),
			LpuUnitType_id: base_form.findField('LpuSection_pid').getFieldValue('LpuUnitType_id'),
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

	initComponent: function() {
		var curwin = this;
		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.doSave();
				}.createDelegate(this),
				iconCls: 'save16',
				onShiftTabAction: function() {
					var base_form = this.findById('EvnPSPriemEditForm').getForm();

					if ( !this.findById('EPSPEF_EvnUslugaPanel').collapsed && this.findById('EPSPEF_EvnUslugaGrid').getStore().getCount() > 0 ) {
						this.findById('EPSPEF_EvnUslugaGrid').getView().focusRow(0);
						this.findById('EPSPEF_EvnUslugaGrid').getSelectionModel().selectFirstRow();
					}
					else if ( !this.findById('EPSPEF_AdmitDiagPanel').collapsed && this.findById('EPSPEF_EvnDiagPSRecepGrid').getStore().getCount() > 0 ) {
						this.findById('EPSPEF_EvnDiagPSRecepGrid').getView().focusRow(0);
						this.findById('EPSPEF_EvnDiagPSRecepGrid').getSelectionModel().selectFirstRow();
					}
					else if ( !this.findById('EPSPEF_AdmitDepartPanel').collapsed && this.action != 'view' ) {
						if ( !base_form.findField('Diag_pid').disabled ) {
							base_form.findField('Diag_pid').focus(true);
						}
						else {
							base_form.findField('MedStaffFact_pid').focus(true);
						}
					}
					else if ( !this.findById('EPSPEF_DirectDiagPanel').collapsed && this.findById('EPSPEF_EvnDiagPSHospGrid').getStore().getCount() > 0 ) {
						this.findById('EPSPEF_EvnDiagPSHospGrid').getView().focusRow(0);
						this.findById('EPSPEF_EvnDiagPSHospGrid').getSelectionModel().selectFirstRow();
					}
					else if ( !this.findById('EPSPEF_HospitalisationPanel').collapsed && this.action != 'view' ) {
						base_form.findField('EvnPS_IsDiagMismatch').focus(true);
					}
					else {
						this.buttons[this.buttons.length - 1].focus();
					}
					/*
					else if ( !this.findById('EPSPEF_PriemLeavePanel').collapsed ) {
						
					}
					*/
				}.createDelegate(this),
				tabIndex: TABINDEX_EPSPEF + 81,
				text: BTN_FRMSAVE
			}, new Ext.Button({ // Petrov
				onShiftTabAction: function () {
					var base_form = this.findById('EvnPSPriemEditForm').getForm();

					if (this.action != 'view') {
						this.buttons[0].focus();
					}
					else if (!this.findById('EPSPEF_EvnUslugaPanel').collapsed && this.findById('EPSPEF_EvnUslugaGrid').getStore().getCount() > 0) {
						this.findById('EPSPEF_EvnUslugaGrid').getView().focusRow(0);
						this.findById('EPSPEF_EvnUslugaGrid').getSelectionModel().selectFirstRow();
					}
					else if (!this.findById('EPSPEF_AdmitDiagPanel').collapsed && this.findById('EPSPEF_EvnDiagPSRecepGrid').getStore().getCount() > 0) {
						this.findById('EPSPEF_EvnDiagPSRecepGrid').getView().focusRow(0);
						this.findById('EPSPEF_EvnDiagPSRecepGrid').getSelectionModel().selectFirstRow();
					}
					else if (!this.findById('EPSPEF_DirectDiagPanel').collapsed && this.findById('EPSPEF_EvnDiagPSHospGrid').getStore().getCount() > 0) {
						this.findById('EPSPEF_EvnDiagPSHospGrid').getView().focusRow(0);
						this.findById('EPSPEF_EvnDiagPSHospGrid').getSelectionModel().selectFirstRow();
					}
					else {
						this.buttons[this.buttons.length - 1].focus();
					}
				}.createDelegate(this),
				tabIndex: TABINDEX_EPSPEF + 82,
				text: lang['pechat'],
				menu: {
					plain: true,
					items: [{
						text: lang['forma_066_u-02'],
						handler: function () {
							this.printEvnPS();
						}.createDelegate(this)
					}]
				}
			}), {
				text: '-'
			},
			HelpButton(this, -1),
			{
				handler: function() {
					this.hide();
					this.onCancelAction();
				}.createDelegate(this),
				iconCls: 'cancel16',
				onTabAction: function() {
					if ( this.action != 'view' ) {
						this.buttons[0].focus();
					}
					else {
						this.buttons[1].focus();
					}
				}.createDelegate(this),
				tabIndex: TABINDEX_EPSPEF + 83,
				text: BTN_FRMCANCEL
			}],
			items: [ 
			new sw.Promed.PersonInfoPanel({
				button1OnHide: function() {
					if ( this.action == 'view' ) {
						this.buttons[this.buttons.length - 1].focus();
					}
					else {
						this.findById('EvnPSPriemEditForm').getForm().findField('EvnPS_NumCard').focus(true);
					}
				}.createDelegate(this),
				button2Callback: function(callback_data) {
					var form = this.findById('EvnPSPriemEditForm');

					form.getForm().findField('PersonEvn_id').setValue(callback_data.PersonEvn_id);
					form.getForm().findField('Server_id').setValue(callback_data.Server_id);

					this.findById('EPSPEF_PersonInformationFrame').load({ Person_id: callback_data.Person_id, Server_id: callback_data.Server_id });
				}.createDelegate(this),
				button2OnHide: function() {
					this.findById('EPSPEF_PersonInformationFrame').button1OnHide();
				}.createDelegate(this),
				button3OnHide: function() {
					this.findById('EPSPEF_PersonInformationFrame').button1OnHide();
				}.createDelegate(this),
				button4OnHide: function() {
					this.findById('EPSPEF_PersonInformationFrame').button1OnHide();
				}.createDelegate(this),
				button5OnHide: function() {
					this.findById('EPSPEF_PersonInformationFrame').button1OnHide();
				}.createDelegate(this),
				collapsible: true,
				collapsed: true,
				floatable: false,
				id: 'EPSPEF_PersonInformationFrame',
				region: 'north',
				plugins: [ Ext.ux.PanelCollapsedTitle ],
				title: '<div>Загрузка...</div>',
				listeners:{
					'render': function(panel) {
						if (panel.header)
						{
							panel.header.on('click',panel.toggleCollapse,panel,false);
						}
					}.createDelegate(this)
				},
				titleCollapse: true
			}),
			new Ext.form.FormPanel({
				autoScroll: true,
				bodyBorder: false,
				bodyStyle: 'padding: 5px 5px 0',
				border: false,
				frame: false,
				id: 'EvnPSPriemEditForm',
				labelAlign: 'right',
				labelWidth: 180,
				items: [{
					name: 'EvnPS_id',
					value: 0,
					xtype: 'hidden'
				}, {
					name: 'EvnDie_id',
					value: 0,
					xtype: 'hidden'
				}, {
					name: 'from',
					value: 'workplacepriem',
					xtype: 'hidden'
				}, {
					name: 'EvnDirection_id',
					value: 0,
					xtype: 'hidden'
				}, {
					name: 'EvnQueue_id',
					value: 0,
					xtype: 'hidden'
				}, {
					name: 'EvnLeave_id',
					value: 0,
					xtype: 'hidden'
				}, {
					name: 'LeaveType_id',
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
					name: 'PrehospStatus_id',
					value: 0,
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
				},
				new sw.Promed.Panel({
					autoHeight: true,
					bodyStyle: 'padding-top: 0.5em;',
					border: true,
					collapsible: true,
					id: 'EPSPEF_HospitalisationPanel',
					layout: 'form',
					listeners: {
						'expand': function(panel) {
							// this.findById('EvnPSPriemEditForm').getForm().findField('EvnPS_IsCont').focus(true);
						}.createDelegate(this)
					},
					style: 'margin-bottom: 0.5em;',
					title: lang['1_gospitalizatsiya'],
					items: [{
						allowBlank: false,
						fieldLabel: lang['pereveden'],
						hiddenName: 'EvnPS_IsCont',
						listeners: {
							'change': function(combo, newValue, oldValue) {
								var base_form = this.findById('EvnPSPriemEditForm').getForm();

								base_form.findField('EvnPS_setDate').fireEvent('change', base_form.findField('EvnPS_setDate'), base_form.findField('EvnPS_setDate').getValue());
							}.createDelegate(this),
							'keydown': function(inp, e) {
								if ( e.getKey() == Ext.EventObject.TAB && e.shiftKey == true ) {
									e.stopEvent();
									this.buttons[this.buttons.length - 1].focus();
								}
							}.createDelegate(this)
						},
						tabIndex: TABINDEX_EPSPEF + 1,
						value: 1,
						width: 70,
						xtype: 'swyesnocombo'
					}, {
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
						tabIndex: TABINDEX_EPSPEF + 2,
						triggerClass: 'x-form-plus-trigger',
						validateOnBlur: false,
						width: 300,
						xtype: 'trigger'
					}, {
						allowBlank: true,
						useCommonFilter: true,
						//disabled: true,
						fieldLabel: 'Источник финансирования',
						tabIndex: TABINDEX_EPSPEF + 3,
						width: 300,
						xtype: 'swpaytypecombo'
					}, {
						border: false,
						layout: 'column',
						items: [{
							border: false,
							layout: 'form',
							items: [{
								allowBlank: false,
								fieldLabel: lang['data_postupleniya'],
								format: 'd.m.Y',
								listeners: {
									'change': function(field, newValue, oldValue) {
										if (blockedDateAfterPersonDeath('personpanelid', 'EPSPEF_PersonInformationFrame', field, newValue, oldValue)) return;
										var base_form = this.findById('EvnPSPriemEditForm').getForm();

										var lpu_section_did = base_form.findField('LpuSection_did').getValue();
										var lpu_section_pid = base_form.findField('LpuSection_pid').getValue();
										var med_staff_fact_pid = base_form.findField('MedStaffFact_pid').getValue();

										base_form.findField('LpuSection_did').clearValue();
										base_form.findField('LpuSection_pid').clearValue();
										base_form.findField('MedStaffFact_pid').clearValue();

										var WithoutChildLpuSectionAge = false;
										var Person_Birthday = this.findById('EPSPEF_PersonInformationFrame').getFieldValue('Person_Birthday');
												
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
										}

										if ( base_form.findField('LpuSection_did').getStore().getById(lpu_section_did) ) {
											base_form.findField('LpuSection_did').setValue(lpu_section_did);
										}

										if ( base_form.findField('LpuSection_pid').getStore().getById(lpu_section_pid) ) {
											base_form.findField('LpuSection_pid').setValue(lpu_section_pid);
											base_form.findField('LpuSection_pid').fireEvent('change', base_form.findField('LpuSection_pid'), lpu_section_pid);
										}

										if ( base_form.findField('MedStaffFact_pid').getStore().getById(med_staff_fact_pid) ) {
											base_form.findField('MedStaffFact_pid').setValue(med_staff_fact_pid);
											base_form.findField('MedStaffFact_pid').fireEvent('change', base_form.findField('MedStaffFact_pid'), base_form.findField('MedStaffFact_pid').getValue());
										}
										curwin.setMKB();
									}.createDelegate(this)
								},
								name: 'EvnPS_setDate',
								plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
								selectOnFocus: true,
								tabIndex: TABINDEX_EPSPEF + 4,
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
								name: 'EvnPS_setTime',
								onTriggerClick: function() {
									var base_form = this.findById('EvnPSPriemEditForm').getForm();
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
										windowId: 'EvnPSPriemEditWindow'
									});
								}.createDelegate(this),
								plugins: [ new Ext.ux.InputTextMask('99:99', true) ],
								tabIndex: TABINDEX_EPSPEF + 5,
								validateOnBlur: false,
								width: 60,
								xtype: 'swtimefield'
							}]
						}]
					}, {
						autoHeight: true,
						style: 'padding: 0px;',
						title: lang['kem_napravlen'],
						width: 730,
						xtype: 'fieldset',

						items: [{
							hiddenName: 'PrehospDirect_id',
							lastQuery: '',
							listeners: {
								'change': function(combo, newValue, oldValue) {
									var base_form = this.findById('EvnPSPriemEditForm').getForm();
									var priemDiag = (getGlobalOptions().check_priemdiag_allow && getGlobalOptions().check_priemdiag_allow=='1')
									var evn_direction_set_date_field = base_form.findField('EvnDirection_setDate');
									var evn_direction_num_field = base_form.findField('EvnDirection_Num');
									var lpu_section_combo = base_form.findField('LpuSection_did');
									var org_combo = base_form.findField('Org_did');
									var iswd_combo = base_form.findField('EvnPS_IsWithoutDirection');

									base_form.findField('Diag_did').setAllowBlank(true);
									base_form.findField('LpuSection_pid').setAllowBlank(false);
									base_form.findField('MedStaffFact_pid').setAllowBlank(false);

									base_form.findField('EvnDirection_id').setValue(0);
									evn_direction_set_date_field.setValue(null);
									evn_direction_num_field.setValue(null);
									lpu_section_combo.clearValue();
									org_combo.clearValue();
									
									var record = base_form.findField('EvnPS_IsCont').getStore().getById(base_form.findField('EvnPS_IsCont').getValue());
									var evn_ps_is_cont = false;

									if ( record && record.get('YesNo_Code') == 1 ) {
										evn_ps_is_cont = true;
									}

									record = combo.getStore().getById(newValue);

									evn_direction_set_date_field.disable();
									evn_direction_num_field.disable()
									lpu_section_combo.disable();
									org_combo.disable();

									if ( typeof record != 'object' || Ext.isEmpty(record.get('PrehospDirect_Code')) ) {
										return false;
									}

									// https://redmine.swan.perm.ru/issues/4549
									switch ( Number(record.get('PrehospDirect_Code')) ) {
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

											// http://redmine.swan.perm.ru/issues/22684
											// Перенес выше тут https://redmine.swan.perm.ru/issues/77114
											if ( Number(record.get('PrehospDirect_Code')) == 3 ) {
												base_form.findField('PrehospArrive_id').setFieldValue('PrehospArrive_Code', 2);
												base_form.findField('PrehospArrive_id').fireEvent('change', base_form.findField('PrehospArrive_id'), base_form.findField('PrehospArrive_id').getValue());
											}
										break;

										default:
											evn_direction_set_date_field.disable();
											evn_direction_num_field.disable()
											org_combo.disable();
											org_combo.setAllowBlank(true);
										break;
									}

									if(iswd_combo.getValue() == 2) {
										evn_direction_set_date_field.disable();
										evn_direction_num_field.disable()
										lpu_section_combo.disable();
										org_combo.disable();
									}
								}.createDelegate(this),
								'select': function(combo, record, index) {
									combo.fireEvent('change', combo, record.get(combo.valueField));
								}.createDelegate(this)
							},
							tabIndex: TABINDEX_EPSPEF + 6,
							width: 300,
							xtype: 'swprehospdirectcombo'
						},{
							border: false,
							layout: 'column',
							items: [{
								border: false,
								layout: 'form',
								width: 500,
								items: [
								new sw.Promed.SwYesNoCombo({
									fieldLabel: lang['s_elektronnyim_napravleniem'],
									hiddenName: 'EvnPS_IsWithoutDirection',
									value: 2,
									allowBlank: false,
									tabIndex: TABINDEX_EPSPEF + 7,
									width: 60,
									listeners: 
									{
										'change': function (iswd_combo, newValue, oldValue) 
										{
											if ( this.action == 'view' ) {
												return false;
											}
											var base_form = this.findById('EvnPSPriemEditForm').getForm();
											if ( newValue == 2 ) {
												// поля заполняются из эл.направления
												base_form.findField('PrehospDirect_id').disable();
												base_form.findField('Diag_did').disable();
												base_form.findField('PayType_id').disable();
												base_form.findField('PrehospDirect_id').fireEvent('change', base_form.findField('PrehospDirect_id'), base_form.findField('PrehospDirect_id').getValue());
											}
											else {
												base_form.findField('EvnDirection_Num').enable();
												base_form.findField('EvnDirection_setDate').enable();
												base_form.findField('Org_did').enable();
												base_form.findField('Diag_did').enable();
												base_form.findField('PrehospDirect_id').enable();
												base_form.findField('PayType_id').enable();
												base_form.findField('PrehospDirect_id').fireEvent('change', base_form.findField('PrehospDirect_id'), base_form.findField('PrehospDirect_id').getValue());
											}
										}.createDelegate(this)
									}
								})]
							}]
						}, {
							hiddenName: 'LpuSection_did',
							tabIndex: TABINDEX_EPSPEF + 8,
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
								var base_form = this.findById('EvnPSPriemEditForm').getForm();
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
									{ name: 'Org_id', type: 'int' },
									{ name: 'Org_Name', type: 'string' }
								],
								key: 'Org_id',
								sortInfo: {
									field: 'Org_Name'
								},
								url: C_ORG_LIST
							}),
							tabIndex: TABINDEX_EPSPEF + 9,
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
									tabIndex: TABINDEX_EPSPEF + 10,
									autoCreate: {tag: "input", type: "text", maxLength: "6", autocomplete: "off"},
									width: 150,
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
									tabIndex: TABINDEX_EPSPEF + 11,
									width: 100,
									xtype: 'swdatefield'
								}]
							}]
						}]
					}, {
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
									var index = combo.getStore().findBy(function(rec) {
										return (rec.get(combo.valueField) == newValue);
									});
									combo.fireEvent('select', combo, combo.getStore().getAt(index), index);
								},
								'select': function(combo, record, index) {
									var base_form = this.findById('EvnPSPriemEditForm').getForm();

									base_form.findField('EvnPS_CodeConv').setValue('');
									base_form.findField('EvnPS_NumConv').setValue('');
									if ( this.action == 'add' )
										base_form.findField('EvnPS_IsPLAmbulance').setValue(1);

									if ( typeof record != 'object' || Ext.isEmpty(record.get('PrehospArrive_Code')) || record.get('PrehospArrive_Code') == 1 ) {
										base_form.findField('EvnPS_CodeConv').disable();
										base_form.findField('EvnPS_NumConv').disable();
										base_form.findField('EvnPS_IsPLAmbulance').disable();
									}
									else if ( typeof record == 'object' && record.get('PrehospArrive_Code') == 2 ) {
										base_form.findField('EvnPS_CodeConv').enable();
										base_form.findField('EvnPS_NumConv').enable();
										base_form.findField('EvnPS_IsPLAmbulance').enable();
										if ( this.action == 'add' && base_form.findField('PrehospDirect_id').getFieldValue('PrehospDirect_Code') == 3 )
											base_form.findField('EvnPS_IsPLAmbulance').setValue(2);
									}
									else {
										base_form.findField('EvnPS_CodeConv').enable();
										base_form.findField('EvnPS_NumConv').enable();
										base_form.findField('EvnPS_IsPLAmbulance').disable();
									}
								}.createDelegate(this),
								'render': function(combo) {
									combo.getStore().load();
								}
							},
							tabIndex: TABINDEX_EPSPEF + 12,
							width: 300,
							xtype: 'swprehosparrivecombo'
						}, {
							fieldLabel: lang['kod'],
							maxLength: 10,
							name: 'EvnPS_CodeConv',
							tabIndex: TABINDEX_EPSPEF + 13,
							width: 150,
							xtype: 'textfield'
						}, {
							fieldLabel: lang['nomer_naryada'],
							maxLength: 10,
							name: 'EvnPS_NumConv',
							tabIndex: TABINDEX_EPSPEF + 14,
							width: 150,
							xtype: 'textfield'
						},{
							id: 'EPSPEF_EvnPS_IsPLAmbulance',
							comboSubject: 'YesNo',
							disabled: true,
							fieldLabel: lang['talon_peredan_na_ssmp'],
							hiddenName: 'EvnPS_IsPLAmbulance',
							tabIndex: TABINDEX_EPSPEF + 15,
							width: 150,
							value: 1,
							xtype: 'swcommonsprcombo'
						}]
					}, new sw.Promed.swDiagPanel({
						labelWidth: 180,
						phaseDescrName: 'EvnPS_PhaseDescr_did',
						diagSetPhaseName: 'DiagSetPhase_did',
						diagField: {
							MKB:null,
							// allowBlank: false,
							fieldLabel: lang['diagnoz_napr_uchr-ya'],
							hiddenName: 'Diag_did',
							id: 'EPSPEF_DiagHospCombo',
							onChange: function(combo, newValue) {
								var base_form = this.findById('EvnPSPriemEditForm').getForm();

								if ( !newValue ) {
									return true;
								}

								base_form.findField('LpuSection_pid').fireEvent('change', base_form.findField('LpuSection_pid'), base_form.findField('LpuSection_pid').getValue());
							}.createDelegate(this),
							tabIndex: TABINDEX_EPSPEF + 16,
							width: 500,
							xtype: 'swdiagcombo'
						}
					}), {
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
							tabIndex: TABINDEX_EPSPEF + 17,
							value: 1,
							width: 100,
							xtype: 'swyesnocombo'
						}, {
							allowBlank: false,
							fieldLabel: lang['nedost_obyem_kliniko-diag_obsledovaniya'],
							hiddenName: 'EvnPS_IsShortVolume',
							tabIndex: TABINDEX_EPSPEF + 18,
							value: 1,
							width: 100,
							xtype: 'swyesnocombo'
						}, {
							allowBlank: false,
							fieldLabel: lang['nepravilnaya_taktika_lecheniya'],
							hiddenName: 'EvnPS_IsWrongCure',
							tabIndex: TABINDEX_EPSPEF + 19,
							value: 1,
							width: 100,
							xtype: 'swyesnocombo'
						}, {
							allowBlank: false,
							fieldLabel: lang['nesovpadenie_diagnoza'],
							hiddenName: 'EvnPS_IsDiagMismatch',
							listeners: {
								'keydown': function(inp, e) {
									var base_form = this.findById('EvnPSPriemEditForm').getForm();

									if ( e.getKey() == Ext.EventObject.TAB && e.shiftKey == false ) {
										e.stopEvent();

										if ( !this.findById('EPSPEF_DirectDiagPanel').collapsed && this.findById('EPSPEF_EvnDiagPSHospGrid').getStore().getCount() > 0 ) {
											this.findById('EPSPEF_EvnDiagPSHospGrid').getView().focusRow(0);
											this.findById('EPSPEF_EvnDiagPSHospGrid').getSelectionModel().selectFirstRow();
										}
										else if ( !this.findById('EPSPEF_AdmitDepartPanel').collapsed && this.action != 'view' ) {
											base_form.findField('PrehospToxic_id').focus(true);
										}
										else if ( !this.findById('EPSPEF_AdmitDiagPanel').collapsed && this.findById('EPSPEF_EvnDiagPSRecepGrid').getStore().getCount() > 0 ) {
											this.findById('EPSPEF_EvnDiagPSRecepGrid').getView().focusRow(0);
											this.findById('EPSPEF_EvnDiagPSRecepGrid').getSelectionModel().selectFirstRow();
										}
										else if ( !this.findById('EPSPEF_PriemLeavePanel').collapsed ) {

										}
										else if ( !this.findById('EPSPEF_EvnUslugaPanel').collapsed && this.findById('EPSPEF_EvnUslugaGrid').getStore().getCount() > 0 ) {
											this.findById('EPSPEF_EvnUslugaGrid').getView().focusRow(0);
											this.findById('EPSPEF_EvnUslugaGrid').getSelectionModel().selectFirstRow();
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
							tabIndex: TABINDEX_EPSPEF + 20,
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
					id: 'EPSPEF_DirectDiagPanel',
					isLoaded: false,
					layout: 'border',
					listeners: {
						'expand': function(panel) {
							if ( panel.isLoaded === false ) {
								panel.isLoaded = true;
								panel.findById('EPSPEF_EvnDiagPSHospGrid').getStore().load({
									params: {
										'class': 'EvnDiagPSHosp',
										EvnDiagPS_pid: this.findById('EvnPSPriemEditForm').getForm().findField('EvnPS_id').getValue()
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
						id: 'EPSPEF_EvnDiagPSHospGrid',
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

								var grid = Ext.getCmp('EPSPEF_EvnDiagPSHospGrid');

								switch ( e.getKey() ) {
									case Ext.EventObject.DELETE:
										Ext.getCmp('EvnPSPriemEditWindow').deleteEvent('EvnDiagPSHosp');
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

										Ext.getCmp('EvnPSPriemEditWindow').openEvnDiagPSEditWindow(action, 'hosp');
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
										var base_form = this.findById('EvnPSPriemEditForm').getForm();

										grid.getSelectionModel().clearSelections();
										grid.getSelectionModel().fireEvent('rowselect', grid.getSelectionModel());

										if ( e.shiftKey == false ) {
											if ( !this.findById('EPSPEF_AdmitDepartPanel').collapsed && this.action != 'view' ) {
												base_form.findField('PrehospToxic_id').focus(true);
											}
											else if ( !this.findById('EPSPEF_AdmitDiagPanel').collapsed && this.findById('EPSPEF_EvnDiagPSRecepGrid').getStore().getCount() > 0 ) {
												this.findById('EPSPEF_EvnDiagPSRecepGrid').getView().focusRow(0);
												this.findById('EPSPEF_EvnDiagPSRecepGrid').getSelectionModel().selectFirstRow();
											}
											else if ( !this.findById('EPSPEF_PriemLeavePanel').collapsed ) {

											}
											else if ( !this.findById('EPSPEF_EvnUslugaPanel').collapsed && this.findById('EPSPEF_EvnUslugaGrid').getStore().getCount() > 0 ) {
												this.findById('EPSPEF_EvnUslugaGrid').getView().focusRow(0);
												this.findById('EPSPEF_EvnUslugaGrid').getSelectionModel().selectFirstRow();
											}
											else if ( this.action != 'view' ) {
												this.buttons[0].focus();
											}
											else {
												this.buttons[1].focus();
											}
										}
										else {
											if ( !this.findById('EPSPEF_HospitalisationPanel').collapsed && this.action != 'view' ) {
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
									var evn_diag_ps_id = null;
									var selected_record = sm.getSelected();
									var toolbar = this.grid.getTopToolbar();

									if ( selected_record ) {
										evn_diag_ps_id = selected_record.get('EvnDiagPS_id');
									}

									if ( evn_diag_ps_id ) {
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
							baseParams: {
								'class': 'EvnDiagPSHosp'
							},
							listeners: {
								'load': function(store, records, index) {
									if ( store.getCount() == 0 ) {
										LoadEmptyRow(this.findById('EPSPEF_EvnDiagPSHospGrid'));
									}

									// this.findById('EPSPEF_EvnDiagPSHospGrid').getView().focusRow(0);
									// this.findById('EPSPEF_EvnDiagPSHospGrid').getSelectionModel().selectFirstRow();
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
					id: 'EPSPEF_AdmitDepartPanel',
					layout: 'form',
					listeners: {
						'expand': function(panel) {
							this.findById('EvnPSPriemEditForm').getForm().findField('PrehospToxic_id').focus(true);
						}.createDelegate(this)
					},
					style: 'margin-bottom: 0.5em;',
					title: lang['3_pervichnyiy_osmotr'],
					labelWidth: 200,
					//Width: 250,
					items: [{
						fieldLabel: lang['sostoyanie_opyaneniya'],
						hiddenName: 'PrehospToxic_id',
						listeners: {
							'keydown': function(inp, e) {
								if ( e.getKey() == Ext.EventObject.TAB && e.shiftKey == true ) {
									e.stopEvent();
									var base_form = this.findById('EvnPSPriemEditForm').getForm();

									if ( !this.findById('EPSPEF_DirectDiagPanel').collapsed && this.findById('EPSPEF_EvnDiagPSHospGrid').getStore().getCount() > 0 ) {
										this.findById('EPSPEF_EvnDiagPSHospGrid').getView().focusRow(0);
										this.findById('EPSPEF_EvnDiagPSHospGrid').getSelectionModel().selectFirstRow();
									}
									else if ( !this.findById('EPSPEF_HospitalisationPanel').collapsed && this.action != 'view' ) {
										base_form.findField('EvnPS_IsDiagMismatch').focus(true);
									}
									else {
										this.buttons[this.buttons.length - 1].focus();
									}
								}
							}.createDelegate(this)
						},
						tabIndex: TABINDEX_EPSPEF + 31,
						width: 300,
						xtype: 'swprehosptoxiccombo'
					}, {
						allowBlank: false,
						fieldLabel: lang['tip_gospitalizatsii'],
						hiddenName: 'PrehospType_id',
						tabIndex: TABINDEX_EPSPEF + 32,
						width: 300,
						xtype: 'swprehosptypecombo'
					}, {
						fieldLabel: 'Цель госпитализации',
						comboSubject: 'PurposeHospital',
						width: 300,
						prefix: 'r101_',
						xtype: 'swcommonsprcombo',
					}, {
						allowDecimals: false,
						allowNegative: false,
						fieldLabel: lang['kolichestvo_gospitalizatsiy'],
						minValue: 0,
						maxValue: 99,
						name: 'EvnPS_HospCount',
						tabIndex: TABINDEX_EPSPEF + 33,
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
								tabIndex: this.tabindex + 34,
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
								tabIndex: this.tabindex + 34,
								width: 100,
								xtype: 'numberfield'
							}]
						}]
					}, {
						allowBlank: true,
						fieldLabel: lang['sluchay_zapuschen'],
						hiddenName: 'EvnPS_IsNeglectedCase',
						tabIndex: TABINDEX_EPSPEF + 35,
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
									var base_form = this.findById('EvnPSPriemEditForm').getForm();

									var is_unlaw_combo = base_form.findField('EvnPS_IsUnlaw');
									var record = combo.getStore().getById(newValue);

									if ( !record ) {
										is_unlaw_combo.clearValue();
										is_unlaw_combo.disable();
										is_unlaw_combo.setAllowBlank(true);
									}
									else {
										is_unlaw_combo.setValue(1);
										is_unlaw_combo.enable();
										is_unlaw_combo.setAllowBlank(false);
									}
									
									is_unlaw_combo.fireEvent('change', is_unlaw_combo, is_unlaw_combo.getValue());
								}.createDelegate(this)
							},
							tabIndex: TABINDEX_EPSPEF + 36,
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
									tabIndex: TABINDEX_EPSPEF + 37,
									width: 70,
									listeners: {
										'change': function(combo, newValue, oldValue) {
											var base_form = this.findById('EvnPSPriemEditForm').getForm();

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
							}, {
								border: false,
								labelWidth: 200,
								layout: 'form',
								items: [ new sw.Promed.SwYesNoCombo({
									fieldLabel: lang['netransportabelnost'],
									hiddenName: 'EvnPS_IsUnport',
									lastQuery: '',
									tabIndex: TABINDEX_EPSPEF + 38,
									width: 70
								})]
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
										var base_form = this.findById('EvnPSPriemEditForm').getForm(), 
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
								tabIndex: TABINDEX_EPSPEF + 39,
								width: 300,
								xtype: 'swcommonsprcombo'
							}]
						}]
					}, {
						fieldLabel: lang['priemnoe_otdelenie'],
						hiddenName: 'LpuSection_pid',
						bodyStyle: 'padding-top: 0.5em;',
						style: 'margin-bottom: 0.5em;',
						id: 'EPSPEF_LpuSectionRecCombo',
						disabled: true,
						listeners: {
							'change': function(field, newValue, oldValue) {
								
								var base_form = this.findById('EvnPSPriemEditForm').getForm();
								var lpu_section_id = base_form.findField('LpuSection_eid').getValue();
								base_form.findField('LpuSection_eid').clearValue();

								if ( newValue ) {
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
											if ( base_form.findField('LpuSection_eid').getStore().getById(lpu_section_id) ) {
												base_form.findField('LpuSection_eid').setValue(lpu_section_id);
											}
										}
									});
								}
								
							}.createDelegate(this)
						},
/*
						linkedElements: [
							'EPSPEF_MedStaffFactRecCombo'
						],
*/
						listWidth: 650,
						tabIndex: TABINDEX_EPSPEF + 40,
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
						fieldLabel: lang['vrach'],
						hiddenName: 'MedStaffFact_pid',
						id: 'EPSPEF_MedStaffFactRecCombo',
						listWidth: 650,
						// parentElementId: 'EPSPEF_LpuSectionRecCombo',
						tabIndex: TABINDEX_EPSPEF + 41,
						width: 500,
						xtype: 'swmedstafffactglobalcombo'
					}, new sw.Promed.swDiagPanel({
						labelWidth: 200,
						bodyStyle: 'padding-top: 0.5em;',
						style: 'margin-bottom: 0.5em;',
						phaseDescrName: 'EvnPS_PhaseDescr_pid',
						diagSetPhaseName: 'DiagSetPhase_pid',
						diagField: {
							MKB:null,
							// allowBlank: false,
							fieldLabel: lang['diagnoz_priem_otd-ya'],
							hiddenName: 'Diag_pid',
							id: 'EPSPEF_DiagRecepCombo',
							tabIndex: TABINDEX_EPSPEF + 42,
							width: 500,
							xtype: 'swdiagcombo'
						}
					}), {
						fieldLabel: 'Уточняющий диагноз прием. отд-я',
						hiddenName: 'Diag_cid',
						id: 'EPSPEF_DiagRecepComboC',
						tabIndex: TABINDEX_EPSPEF + 42,
						width: 500,
						xtype: 'swdiagcombo',
					}]
				}),
				new sw.Promed.Panel({
					border: true,
					collapsible: true,
					height: 125,
					id: 'EPSPEF_AdmitDiagPanel',
					isLoaded: false,
					layout: 'border',
					listeners: {
						'expand': function(panel) {
							if ( panel.isLoaded === false ) {
								panel.isLoaded = true;
								panel.findById('EPSPEF_EvnDiagPSRecepGrid').getStore().load({
									params: {
										'class': 'EvnDiagPSRecep',
										EvnDiagPS_pid: this.findById('EvnPSPriemEditForm').getForm().findField('EvnPS_id').getValue()
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
						id: 'EPSPEF_EvnDiagPSRecepGrid',
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

								var grid = Ext.getCmp('EPSPEF_EvnDiagPSRecepGrid');

								switch ( e.getKey() ) {
									case Ext.EventObject.DELETE:
										Ext.getCmp('EvnPSPriemEditWindow').deleteEvent('EvnDiagPSRecep');
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

										Ext.getCmp('EvnPSPriemEditWindow').openEvnDiagPSEditWindow(action, 'recep');
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
										var base_form = this.findById('EvnPSPriemEditForm').getForm();

										grid.getSelectionModel().clearSelections();
										grid.getSelectionModel().fireEvent('rowselect', grid.getSelectionModel());

										if ( e.shiftKey == false ) {
											if ( !this.findById('EPSPEF_PriemLeavePanel').collapsed ) {

											}
											else if ( !this.findById('EPSPEF_EvnUslugaPanel').collapsed && this.findById('EPSPEF_EvnUslugaGrid').getStore().getCount() > 0 ) {
												this.findById('EPSPEF_EvnUslugaGrid').getView().focusRow(0);
												this.findById('EPSPEF_EvnUslugaGrid').getSelectionModel().selectFirstRow();
											}
											else if ( this.action != 'view' ) {
												this.buttons[0].focus();
											}
											else {
												this.buttons[1].focus();
											}
										}
										else {
											if ( !this.findById('EPSPEF_AdmitDepartPanel').collapsed && this.action != 'view' ) {
												if ( !base_form.findField('Diag_pid').disabled ) {
													base_form.findField('Diag_pid').focus(true);
												}
												else {
													base_form.findField('MedStaffFact_pid').focus(true);
												}
											}
											else if ( !this.findById('EPSPEF_DirectDiagPanel').collapsed && this.findById('EPSPEF_EvnDiagPSHospGrid').getStore().getCount() > 0 ) {
												this.findById('EPSPEF_EvnDiagPSHospGrid').getView().focusRow(0);
												this.findById('EPSPEF_EvnDiagPSHospGrid').getSelectionModel().selectFirstRow();
											}
											else if ( !this.findById('EPSPEF_HospitalisationPanel').collapsed && this.action != 'view' ) {
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
									var evn_diag_ps_id = null;
									var selected_record = sm.getSelected();
									var toolbar = this.grid.getTopToolbar();

									if ( selected_record ) {
										evn_diag_ps_id = selected_record.get('EvnDiagPS_id');
									}

									if ( evn_diag_ps_id ) {
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
										LoadEmptyRow(this.findById('EPSPEF_EvnDiagPSRecepGrid'));
									}

									// this.findById('EPSPEF_EvnDiagPSRecepGrid').getView().focusRow(0);
									// this.findById('EPSPEF_EvnDiagPSRecepGrid').getSelectionModel().selectFirstRow();
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
					id: 'EPSPEF_PriemLeavePanel',
					layout: 'form',
					listeners: {
						'expand': function(panel) {
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
								tabIndex: TABINDEX_EPSPEF + 43,
								width: 100,
								xtype: 'swdatefield',
								listeners: {
									'change': function() {
										this.setDiagPAllowBlank();
									}.createDelegate(this)
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
									var base_form = this.findById('EvnPSPriemEditForm').getForm();
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
								tabIndex: TABINDEX_EPSPEF + 44,
								validateOnBlur: false,
								width: 60,
								xtype: 'swtimefield'
							}]
						}]
					},{
						hiddenName: 'LpuSection_eid',
						fieldLabel: lang['gospitalizirovan_v'],
						id: 'EPSPEF_LpuSectionCombo',
						tabIndex: TABINDEX_EPSPEF + 45,
						width: 500,
						xtype: 'swlpusectionglobalcombo', 
						listeners: 
						{
							'select': function (combo,record,index) 
							{
								if ( typeof record == 'object' && !Ext.isEmpty(record.get('LpuSection_id')) )
								{
									var base_form = this.findById('EvnPSPriemEditForm').getForm();
									if (!this.isProcessLoadForm && Ext.isEmpty(base_form.findField('EvnPS_OutcomeDate').getValue())) {
										base_form.findField('EvnPS_OutcomeTime').onTriggerClick();
									}
									var rc_combo = this.findById('EPSPEF_PrehospWaifRefuseCause_id');
									var oldValue = rc_combo.getValue();
									rc_combo.clearValue();
									rc_combo.fireEvent('change',rc_combo,'',oldValue);
								}
							}.createDelegate(this)
						}
					},{
						hiddenName: 'PrehospWaifRefuseCause_id',
						id: 'EPSPEF_PrehospWaifRefuseCause_id',
						fieldLabel: lang['otkaz'],
						tabIndex: TABINDEX_EPSPEF + 46,
						width: 500,
						comboSubject: 'PrehospWaifRefuseCause',
						autoLoad: true,
						xtype: 'swcommonsprcombo', 
						listeners: 
						{
							'change': function (combo,newValue,oldValue)
							{
								var base_form = this.findById('EvnPSPriemEditForm').getForm();
								var is_transf_call_combo = base_form.findField('EvnPS_IsTransfCall');
								if(Ext.isEmpty(newValue))
								{
									is_transf_call_combo.disable();
									this.findById('EPSPEF_PrehospWaifRefuseCauseButton').disable();
								}
								else
								{
									if (!this.isProcessLoadForm && Ext.isEmpty(base_form.findField('EvnPS_OutcomeDate').getValue())) {
										base_form.findField('EvnPS_OutcomeTime').onTriggerClick();
									}
									
									is_transf_call_combo.enable();
									this.findById('EPSPEF_PrehospWaifRefuseCauseButton').enable();
									this.findById('EPSPEF_LpuSectionCombo').clearValue();
								}
							}.createDelegate(this)
						}
					},{
						border: false,
						layout: 'column',
						items: [{
							border: false,
							layout: 'form',
							width: 300,
							items: [{
								allowBlank: false,
								id: 'EPSPEF_EvnPS_IsTransfCall',
								tabIndex: TABINDEX_EPSPEF + 47,
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
										window.open('/?c=EvnPS&m=printEvnPSPrehospWaifRefuseCause&EvnPS_id='+this.findById('EvnPSPriemEditForm').getForm().findField('EvnPS_id').getValue(), '_blank');
										window.open('/?c=EvnPS&m=printPatientRefuse&EvnPS_id=' + this.findById('EvnPSPriemEditForm').getForm().findField('EvnPS_id').getValue(), '_blank');
									}
								}.createDelegate(this),
								iconCls: 'print16',
								id: 'EPSPEF_PrehospWaifRefuseCauseButton',
								tabIndex: TABINDEX_EPSPEF + 48,
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
					height: 200,
					id: 'EPSPEF_EvnUslugaPanel',
					isLoaded: false,
					layout: 'border',
					listeners: {
						'expand': function(panel) {
							if ( panel.isLoaded === false ) {
								panel.isLoaded = true;
								panel.findById('EPSPEF_EvnUslugaGrid').getStore().load({
									params: {
										pid: this.findById('EvnPSPriemEditForm').getForm().findField('EvnPS_id').getValue()
									}
								});
							}

							panel.doLayout();
						}.createDelegate(this)
					},
					style: 'margin-bottom: 0.5em;',
					title: lang['6_uslugi'],
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
						id: 'EPSPEF_EvnUslugaGrid',
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

								var grid = Ext.getCmp('EPSPEF_EvnUslugaGrid');

								switch ( e.getKey() ) {
									case Ext.EventObject.DELETE:
										Ext.getCmp('EvnPSPriemEditWindow').deleteEvent('EvnUsluga');
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

										Ext.getCmp('EvnPSPriemEditWindow').openEvnUslugaEditWindow(action);
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
										var base_form = this.findById('EvnPSPriemEditForm').getForm();

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
											if ( !this.findById('EPSPEF_PriemLeavePanel').collapsed ) {
												
											}
											else if ( !this.findById('EPSPEF_AdmitDiagPanel').collapsed && this.findById('EPSPEF_EvnDiagPSRecepGrid').getStore().getCount() > 0 ) {
												this.findById('EPSPEF_EvnDiagPSRecepGrid').getView().focusRow(0);
												this.findById('EPSPEF_EvnDiagPSRecepGrid').getSelectionModel().selectFirstRow();
											}
											else if ( !this.findById('EPSPEF_AdmitDepartPanel').collapsed && this.action != 'view' ) {
												if ( !base_form.findField('Diag_pid').disabled ) {
													base_form.findField('Diag_pid').focus(true);
												}
												else {
													base_form.findField('MedStaffFact_pid').focus(true);
												}
											}
											else if ( !this.findById('EPSPEF_DirectDiagPanel').collapsed && this.findById('EPSPEF_EvnDiagPSHospGrid').getStore().getCount() > 0 ) {
												this.findById('EPSPEF_EvnDiagPSHospGrid').getView().focusRow(0);
												this.findById('EPSPEF_EvnDiagPSHospGrid').getSelectionModel().selectFirstRow();
											}
											else if ( !this.findById('EPSPEF_HospitalisationPanel').collapsed && this.action != 'view' ) {
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
									var evn_usluga_id = null;
									var evnclass_sysnick = null;
									var selected_record = sm.getSelected();
									var toolbar = this.grid.getTopToolbar();

									if ( selected_record ) {
										evn_usluga_id = selected_record.get('EvnUsluga_id');
										evnclass_sysnick = selected_record.get('EvnClass_SysNick');
									}

									toolbar.items.items[1].disable();
									toolbar.items.items[2].disable();
									toolbar.items.items[3].disable();
										
									if ( evn_usluga_id ) {
										toolbar.items.items[1].enable();
										toolbar.items.items[2].enable();
										if (evnclass_sysnick != 'EvnUslugaPar') {
											toolbar.items.items[3].enable();
										}
									}
								}
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
										LoadEmptyRow(this.findById('EPSPEF_EvnUslugaGrid'));
									}

									// this.findById('EPSPEF_EvnUslugaGrid').getView().focusRow(0);
									// this.findById('EPSPEF_EvnUslugaGrid').getSelectionModel().selectFirstRow();
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
					border: true,
					collapsible: true,
					height: 200,
					id: 'EPSPEF_EvnDrugPanel',
					isLoaded: false,
					layout: 'border',
					listeners: {
						'expand': function(panel) {
							if ( panel.isLoaded === false ) {
								panel.isLoaded = true;
								panel.findById('EPSPEF_EvnDrugGrid').getStore().load({
									params: {
										EvnDrug_pid: this.findById('EvnPSPriemEditForm').getForm().findField('EvnPS_id').getValue()
									}
								});
							}

							panel.doLayout();
						}.createDelegate(this)
					},
					style: 'margin-bottom: 0.5em;',
					title: lang['7_ispolzovanie_medikamentov'],
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
						id: 'EPSPEF_EvnDrugGrid',
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

								var grid = this.findById('EPSPEF_EvnDrugGrid');

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
										var base_form = this.findById('EvnPSPriemEditForm').getForm();

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
											if ( !this.findById('EPSPEF_PriemLeavePanel').collapsed ) {
												
											}
											else if ( !this.findById('EPSPEF_AdmitDiagPanel').collapsed && this.findById('EPSPEF_EvnDiagPSRecepGrid').getStore().getCount() > 0 ) {
												this.findById('EPSPEF_EvnDiagPSRecepGrid').getView().focusRow(0);
												this.findById('EPSPEF_EvnDiagPSRecepGrid').getSelectionModel().selectFirstRow();
											}
											else if ( !this.findById('EPSPEF_AdmitDepartPanel').collapsed && this.action != 'view' ) {
												if ( !base_form.findField('Diag_pid').disabled ) {
													base_form.findField('Diag_pid').focus(true);
												}
												else {
													base_form.findField('MedStaffFact_pid').focus(true);
												}
											}
											else if ( !this.findById('EPSPEF_DirectDiagPanel').collapsed && this.findById('EPSPEF_EvnDiagPSHospGrid').getStore().getCount() > 0 ) {
												this.findById('EPSPEF_EvnDiagPSHospGrid').getView().focusRow(0);
												this.findById('EPSPEF_EvnDiagPSHospGrid').getSelectionModel().selectFirstRow();
											}
											else if ( !this.findById('EPSPEF_HospitalisationPanel').collapsed && this.action != 'view' ) {
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
									var evn_drug_id = null;
									var selected_record = sm.getSelected();
									var toolbar = this.findById('EPSPEF_EvnDrugGrid').getTopToolbar();

									if ( selected_record ) {
										evn_drug_id = selected_record.get('EvnDrug_id');
									}

									toolbar.items.items[1].disable();
									toolbar.items.items[2].disable();
									toolbar.items.items[3].disable();

									if ( evn_drug_id ) {
										toolbar.items.items[2].enable();

										if ( this.action != 'view' ) {
											toolbar.items.items[1].enable();
											toolbar.items.items[3].enable();
										}
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
										LoadEmptyRow(this.findById('EPSPEF_EvnDrugGrid'));
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
							}]
						})
					})]
				}),
				new sw.Promed.Panel({
					border: true,
					collapsible: true,
					height: 290,
					id: 'EPSPEF_PrehospWaifPanel',
					layout: 'border',
					listeners: {
						'expand': function(panel) {
							//to-do не загружать грид, если он загружен
							//log(this.findById('EPSPEF_PrehospWaifInspection'));
							this.PrehospWaifInspectionRefreshGrid();
							panel.doLayout();
						}.createDelegate(this)
					},
					style: 'margin-bottom: 0.5em;',
					title: lang['8_besprizornyiy'],
					items: [{
						bodyStyle: 'padding-top: 0.5em;',
						border: false,
						height: 90,
						layout: 'form',
						region: 'north',
						items: [{
							id: 'EPSPEF_EvnPS_IsWaif',
							comboSubject: 'YesNo',
							fieldLabel: lang['besprizornyiy'],
							hiddenName: 'EvnPS_IsWaif',
							tabIndex: TABINDEX_EPSPEF + 50,
							width: 100,
							value: 1,
							xtype: 'swcommonsprcombo', 
							listeners: 
							{
								'change': function (combo,newValue,oldValue) 
								{
									var base_form = this.findById('EvnPSPriemEditForm').getForm();
									var pw_arrive_combo = base_form.findField('PrehospWaifArrive_id');
									var pw_reason_combo = base_form.findField('PrehospWaifReason_id');
									var view_frame = this.findById('EPSPEF_PrehospWaifInspection');
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
										pw_arrive_combo.enable();
										pw_arrive_combo.setAllowBlank(false);
										// Обратился самостоятельно ставить автоматически и поле не доступно, если Беспризорный = Да и в разделе КВС Госпитализация поле Кем доставлен = Самостоятельно
										/*if (base_form.findField('PrehospArrive_id').getValue() == 1)
										{
											pw_arrive_combo.setValue(3);
											pw_arrive_combo.disable();
										}*/
										// Причина помещения в ЛПУ: доступно и обязательное если Беспризорный = Да.
										pw_reason_combo.enable();
										pw_reason_combo.setAllowBlank(false);
										view_frame.setReadOnly(false);
									}
								}.createDelegate(this)
							}
						},{
							fieldLabel: lang['kem_dostavlen'],
							tabIndex: TABINDEX_EPSPEF + 51,
							width: 500,
							comboSubject: 'PrehospWaifArrive',
							hiddenName: 'PrehospWaifArrive_id',
							autoLoad: true,
							xtype: 'swcommonsprcombo'
						},{
							id: 'EPSPEF_PrehospWaifReason_id',
							fieldLabel: lang['prichina_pomescheniya_v_lpu'],
							tabIndex: TABINDEX_EPSPEF + 52,
							width: 500,
							comboSubject: 'PrehospWaifReason',
							autoLoad: true,
							xtype: 'swcommonsprcombo'
						}]
					},
					new sw.Promed.ViewFrame({
						id: 'EPSPEF_PrehospWaifInspection',
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
							{name:'action_add', handler: function() { this.openPrehospWaifInspectionEditWindow('add'); }.createDelegate(this)},
							{name:'action_edit', handler: function() { this.openPrehospWaifInspectionEditWindow('edit'); }.createDelegate(this)},
							{name:'action_view', handler: function() { this.openPrehospWaifInspectionEditWindow('view'); }.createDelegate(this)},
							{name:'action_delete'},
							{name:'action_refresh', handler: function() { this.PrehospWaifInspectionRefreshGrid(); }.createDelegate(this)},
							{name:'action_print'}
						],
						paging: false,
						root: 'data',
						totalProperty: 'totalCount',
						focusOn: {name:'EPSPEF_PrintBtn',type:'button'},
						focusPrev: {name:'EPSPEF_PrehospWaifReason_id',type:'field'},
						focusOnFirstLoad: false
					})
					]
				})],
				reader: new Ext.data.JsonReader({
					success: Ext.emptyFn
				}, [
					{ name: 'EvnPS_OutcomeDate' },
					{ name: 'EvnPS_OutcomeTime' },
					{ name: 'EvnPS_IsPLAmbulance' },
					{ name: 'EvnPS_IsTransfCall' },
					{ name: 'EvnPS_IsWaif' },
					{ name: 'LpuSection_eid' },
					{ name: 'PrehospWaifArrive_id' },
					{ name: 'PrehospWaifReason_id' },
					{ name: 'PrehospWaifRefuseCause_id' },
					{ name: 'LpuSectionProfile_id' },
					{ name: 'Diag_did' },
					{ name: 'DiagSetPhase_did' },
					{ name: 'EvnPS_PhaseDescr_did' },
					{ name: 'Diag_pid' },
					{ name: 'DiagSetPhase_pid' },
					{ name: 'EvnPS_PhaseDescr_pid' },
					{ name: 'EvnDie_id' },
					{ name: 'EvnQueue_id' },
					{ name: 'EvnDirection_id' },
					{ name: 'EvnDirection_Num' },
					{ name: 'EvnDirection_setDate' },
					{ name: 'EvnLeave_id' },
					{ name: 'EvnOtherLpu_id' },
					{ name: 'EvnOtherSection_id' },
					{ name: 'EvnOtherSectionBedProfile_id' },
					{ name: 'EvnOtherStac_id' },
					{ name: 'EvnPS_CodeConv' },
					{ name: 'EvnPS_HospCount' },
					{ name: 'EvnPS_id' },
					{ name: 'EvnPS_IsCont' },
					{ name: 'EvnPS_IsDiagMismatch' },
					{ name: 'EvnPS_IsImperHosp' },
					{ name: 'EvnPS_IsNeglectedCase' },
					{ name: 'EvnPS_IsWrongCure' },
					{ name: 'EvnPS_IsUnlaw' },
					{ name: 'EvnPS_IsUnport' },
					{ name: 'EvnPS_NotificationDate' },
					{ name: 'EvnPS_NotificationTime' },
					{ name: 'MedStaffFact_id' },
					{ name: 'EvnPS_Policeman' },
					{ name: 'EvnPS_IsShortVolume' },
					{ name: 'EvnPS_IsWithoutDirection' },
					{ name: 'EvnPS_NumCard' },
					{ name: 'EvnPS_NumConv' },
					{ name: 'EvnPS_setDate' },
					{ name: 'EvnPS_setTime' },
					{ name: 'EvnPS_TimeDesease' },
					{ name: 'Okei_id' },
					{ name: 'LeaveType_id' },
					{ name: 'LpuSection_did' },
					{ name: 'LpuSection_pid' },
					{ name: 'GetBed_id' },
					{ name: 'MedStaffFact_pid' },
					{ name: 'Org_did' },
					{ name: 'PayType_id' },
					{ name: 'Person_id' },
					{ name: 'PersonEvn_id' },
					{ name: 'PrehospArrive_id' },
					{ name: 'PrehospDirect_id' },
					{ name: 'PrehospStatus_id' },
					{ name: 'PrehospToxic_id' },
					{ name: 'PrehospTrauma_id' },
					{ name: 'PrehospType_id' },
					{ name: 'PurposeHospital_id' },
					{ name: 'Diag_cid' },
					{ name: 'EntranceModeType_id' },
					{ name: 'Server_id' }
				]),
				region: 'center',
				url: '/?c=EvnPS&m=saveEvnPS'
			})]
		});
		sw.Promed.swEvnPSPriemEditWindow.superclass.initComponent.apply(this, arguments);

		this.findById('EPSPEF_LpuSectionRecCombo').addListener('change', function(combo, newValue, oldValue) {
			var base_form = this.findById('EvnPSPriemEditForm').getForm();

			var diag_d_combo = base_form.findField('Diag_did');
			var diag_p_combo = base_form.findField('Diag_pid');
			if ( Ext.isEmpty(newValue) ) {
				diag_p_combo.clearValue();
				diag_p_combo.disable();
				this.setDiagPAllowBlank();
				return false;
			}

			diag_p_combo.enable();
			this.setDiagPAllowBlank();

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

		this.findById('EPSPEF_MedStaffFactRecCombo').addListener('keydown', function(inp, e) {
			var base_form = this.findById('EvnPSPriemEditForm').getForm();

			if ( e.getKey() == Ext.EventObject.TAB && e.shiftKey == false && base_form.findField('Diag_pid').disabled ) {
				e.stopEvent();

				if ( !this.findById('EPSPEF_AdmitDiagPanel').collapsed && this.findById('EPSPEF_EvnDiagPSRecepGrid').getStore().getCount() > 0 ) {
					this.findById('EPSPEF_EvnDiagPSRecepGrid').getView().focusRow(0);
					this.findById('EPSPEF_EvnDiagPSRecepGrid').getSelectionModel().selectFirstRow();
				}
				else if ( !this.findById('EPSPEF_PriemLeavePanel').collapsed ) {
					
				}
				else if ( !this.findById('EPSPEF_EvnUslugaPanel').collapsed && this.findById('EPSPEF_EvnUslugaGrid').getStore().getCount() > 0 ) {
					this.findById('EPSPEF_EvnUslugaGrid').getView().focusRow(0);
					this.findById('EPSPEF_EvnUslugaGrid').getSelectionModel().selectFirstRow();
				}
				else if ( this.action != 'view' ) {
					this.buttons[0].focus();
				}
				else {
					this.buttons[1].focus();
				}
			}
		}.createDelegate(this));

		this.findById('EPSPEF_DiagRecepCombo').addListener('keydown', function(inp, e) {
			var base_form = this.findById('EvnPSPriemEditForm').getForm();

			if ( e.getKey() == Ext.EventObject.TAB && e.shiftKey == false ) {
				e.stopEvent();

				if ( !this.findById('EPSPEF_AdmitDiagPanel').collapsed && this.findById('EPSPEF_EvnDiagPSRecepGrid').getStore().getCount() > 0 ) {
					this.findById('EPSPEF_EvnDiagPSRecepGrid').getView().focusRow(0);
					this.findById('EPSPEF_EvnDiagPSRecepGrid').getSelectionModel().selectFirstRow();
				}
				else if ( !this.findById('EPSPEF_PriemLeavePanel').collapsed ) {

				}
				else if ( !this.findById('EPSPEF_EvnUslugaPanel').collapsed && this.findById('EPSPEF_EvnUslugaGrid').getStore().getCount() > 0 ) {
					this.findById('EPSPEF_EvnUslugaGrid').getView().focusRow(0);
					this.findById('EPSPEF_EvnUslugaGrid').getSelectionModel().selectFirstRow();
				}
				else if ( this.action != 'view' ) {
					this.buttons[0].focus();
				}
				else {
					this.buttons[1].focus();
				}
			}
		}.createDelegate(this));
	},
	openEvnDiagPSEditWindow: function(action, type) {
		if ( action != 'add' && action != 'edit' && action != 'view' ) {
			return false;
		}

		var base_form = this.findById('EvnPSPriemEditForm').getForm();
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
				openChildWindow: function() {
					this.openEvnDiagPSEditWindow(action, type);
				}.createDelegate(this),
				print: false
			});
			return false;
		}

		switch ( type ) {
			case 'hosp':
				if ( this.findById('EPSPEF_HospitalisationPanel').hidden ) {
					return false;
				}

				if ( !base_form.findField('Diag_did').getValue() ) {
					sw.swMsg.alert(lang['oshibka'], lang['ne_zapolnen_osnovnoy_diagnoz_napravivshego_uchrejdeniya'], function() { base_form.findField('Diag_did').focus(true); });
					return false;
				}

				grid = this.findById('EPSPEF_EvnDiagPSHospGrid');
			break;

			case 'recep':
				if ( this.findById('EPSPEF_AdmitDepartPanel').hidden ) {
					return false;
				}

				if ( !base_form.findField('Diag_pid').getValue() ) {
					sw.swMsg.alert(lang['oshibka'], lang['ne_zapolnen_osnovnoy_diagnoz_v_priemnom_otdelenii'], function() { base_form.findField('Diag_pid').focus(true); });
					return false;
				}

				grid = this.findById('EPSPEF_EvnDiagPSRecepGrid');
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
						if ( !this.findById('EPSPEF_AdmitDepartPanel').hidden ) {
							this.findById('EPSPEF_EvnDiagPSRecepGrid').getStore().load({
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
			Person_Birthday: this.findById('EPSPEF_PersonInformationFrame').getFieldValue('Person_Birthday'),
			Person_Firname: this.findById('EPSPEF_PersonInformationFrame').getFieldValue('Person_Firname'),
			Person_id: base_form.findField('Person_id').getValue(),
			Person_Secname: this.findById('EPSPEF_PersonInformationFrame').getFieldValue('Person_Secname'),
			Person_Surname: this.findById('EPSPEF_PersonInformationFrame').getFieldValue('Person_Surname'),
			type: type
		});
	},
	openEvnDrugEditWindow: function(action) {
		if ( this.findById('EPSPEF_EvnDrugPanel').hidden || this.findById('EPSPEF_EvnDrugPanel').collapsed ) {
			return false;
		}

		if ( action != 'add' && action != 'edit' && action != 'view' ) {
			return false;
		}

		var base_form = this.findById('EvnPSPriemEditForm').getForm();
		var grid = this.findById('EPSPEF_EvnDrugGrid');

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
				openChildWindow: function() {
					this.openEvnDrugEditWindow(action);
				}.createDelegate(this),
				print: false
			});
			return false;
		}

		// данные для ParentEvnCombo (КВС как движение в приемном)
		var parent_evn_combo_data = new Array({
			Evn_id: base_form.findField('EvnPS_id').getValue(),
			Evn_Name: Ext.util.Format.date(base_form.findField('EvnPS_setDate').getValue(), 'd.m.Y') + ' / ' + this.userMedStaffFact.LpuSection_Name + ' / ' + this.userMedStaffFact.MedPersonal_FIO,
			Evn_setDate: base_form.findField('EvnPS_setDate').getValue(),
			MedStaffFact_id: this.userMedStaffFact.MedStaffFact_id,
			Lpu_id: this.userMedStaffFact.Lpu_id,
			LpuSection_id: this.userMedStaffFact.LpuSection_id,
			MedPersonal_id: this.userMedStaffFact.MedPersonal_id
		});

		var formParams = new Object();
		var params = new Object();
		var person_id = this.findById('EPSPEF_PersonInformationFrame').getFieldValue('Person_id');
		var person_birthday = this.findById('EPSPEF_PersonInformationFrame').getFieldValue('Person_Birthday');
		var person_firname = this.findById('EPSPEF_PersonInformationFrame').getFieldValue('Person_Firname');
		var person_secname = this.findById('EPSPEF_PersonInformationFrame').getFieldValue('Person_Secname');
		var person_surname = this.findById('EPSPEF_PersonInformationFrame').getFieldValue('Person_Surname');

		params.action = action;
		params.parentEvnComboData = parent_evn_combo_data;
		params.callback = function(data) {
			if ( !data || !data.evnDrugData ) {
				return false;
			}
			var grid = this.findById('EPSPEF_EvnDrugGrid');
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
		}

		params.formParams = formParams;

		getWnd(getEvnDrugEditWindowName()).show(params);
	},
	openEvnUslugaEditWindow: function(action) {
		if ( this.findById('EPSPEF_EvnUslugaPanel').hidden ) {
			return false;
		}

		if ( action != 'add' && action != 'edit' && action != 'view' ) {
			return false;
		}

		var base_form = this.findById('EvnPSPriemEditForm').getForm();
		var grid = this.findById('EPSPEF_EvnUslugaGrid');

		if ( this.action == 'view' ) {
			if ( action == 'add' ) {
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
		params.Person_id = this.findById('EPSPEF_PersonInformationFrame').getFieldValue('Person_id');
		params.Person_Birthday = this.findById('EPSPEF_PersonInformationFrame').getFieldValue('Person_Birthday');
		params.Person_Firname = this.findById('EPSPEF_PersonInformationFrame').getFieldValue('Person_Firname');
		params.Person_Secname = this.findById('EPSPEF_PersonInformationFrame').getFieldValue('Person_Secname');
		params.Person_Surname = this.findById('EPSPEF_PersonInformationFrame').getFieldValue('Person_Surname');

		// данные для ParentEvnCombo (КВС как движение в приемном)
		var parent_evn_combo_data = new Array({
			Evn_id: base_form.findField('EvnPS_id').getValue(),
			Evn_Name: Ext.util.Format.date(base_form.findField('EvnPS_setDate').getValue(), 'd.m.Y') + ' / ' + this.userMedStaffFact.LpuSection_Name + ' / ' + this.userMedStaffFact.MedPersonal_FIO,
			Evn_setDate: base_form.findField('EvnPS_setDate').getValue(),
			MedStaffFact_id: this.userMedStaffFact.MedStaffFact_id,
			LpuSection_id: this.userMedStaffFact.LpuSection_id,
			MedPersonal_id: this.userMedStaffFact.MedPersonal_id
		});

		switch ( action ) {
			case 'add':
				if ( base_form.findField('EvnPS_id').getValue() == 0 ) {
					this.doSave({
						openChildWindow: function() {
							this.openEvnUslugaEditWindow(action);
						}.createDelegate(this),
						print: false
					});
					return false;
				}

				// Открываем форму выбора класса услуги
				if ( getWnd('swEvnUslugaSetWindow').isVisible() ) {
					sw.swMsg.alert(lang['soobschenie'], lang['okno_vyibora_tipa_uslugi_uje_otkryito'], function() {
						grid.getSelectionModel().selectFirstRow();
						grid.getView().focusRow(0);
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

				getWnd('swEvnUslugaSetWindow').show({
					EvnUsluga_rid: base_form.findField('EvnPS_id').getValue(),
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
					parentEvent: 'EvnPS'
				});
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
					case 'EvnUslugaCommon':
						params.formParams = {
							EvnUslugaCommon_id: evn_usluga_id
						}
						params.parentEvnComboData = parent_evn_combo_data;
						getWnd('swEvnUslugaCommonEditWindow').show(params);
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

			break;
		}
	},
	PrehospWaifInspectionRefreshGrid: function() 
	{
		if ( Ext.getCmp('EPSPEF_PrehospWaifPanel').hidden ) {
			return false;
		}

		var base_form = Ext.getCmp('EvnPSPriemEditForm').getForm();
		if ( this.action == 'add' && base_form.findField('EvnPS_id').getValue() == 0 ) {
			this.doSave({
				openChildWindow: function() {
					this.PrehospWaifInspectionRefreshGrid();
				}.createDelegate(this),
				print: false
			});
			return false;
		}
		var view_frame = Ext.getCmp('EPSPEF_PrehospWaifInspection');
		view_frame.removeAll(true);
		var params = { EvnPS_id: base_form.findField('EvnPS_id').getValue() };
		params.start = 0; 
		params.limit = 100;
		view_frame.loadData({globalFilters:params});
	},
	openPrehospWaifInspectionEditWindow: function(action) {
		if ( this.findById('EPSPEF_PrehospWaifPanel').hidden ) {
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
		
		var base_form = this.findById('EvnPSPriemEditForm').getForm();
		var view_frame = this.findById('EPSPEF_PrehospWaifInspection');
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
		params.MedStaffFact_id = this.userMedStaffFact.MedStaffFact_id;
		params.LpuSection_id  = this.userMedStaffFact.LpuSection_id;
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
						openChildWindow: function() {
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
	_onSelectEvnDirection: function(data) {
		var base_form = this.findById('EvnPSPriemEditForm').getForm();
		var prehosp_arrive_combo = base_form.findField('PrehospArrive_id');
		var prehosp_direct_combo = base_form.findField('PrehospDirect_id');
		var lpu_section_dir_combo = base_form.findField('LpuSection_did');

		prehosp_direct_combo.setValue(data.PrehospDirect_id || ((data.Org_did != getGlobalOptions().org_id)?16:15));
		prehosp_arrive_combo.setValue((data.PrehospArrive_id || 1));
		prehosp_arrive_combo.fireEvent('change', prehosp_arrive_combo, prehosp_arrive_combo.getValue());

		var iswd_combo = base_form.findField('EvnPS_IsWithoutDirection');
		iswd_combo.setValue(2);
		iswd_combo.disable();
		this.disableFields.push('EvnPS_IsWithoutDirection');
		iswd_combo.fireEvent('change', iswd_combo, 2);

		base_form.findField('EvnDirection_id').setValue(data.EvnDirection_id);

		if ( !Ext.isEmpty(data.Org_did) ) {
			base_form.findField('Org_did').getStore().load({
				callback: function(records, options, success) {
					if ( success ) {
						base_form.findField('Org_did').setValue(data.Org_did);
					}
				},
				params: {
					Org_id: data.Org_did,
					OrgType: 'org'
				}
			});
		}

		base_form.findField('EvnDirection_Num').setValue(data.EvnDirection_Num);
		base_form.findField('EvnDirection_setDate').setValue(data.EvnDirection_setDate);

		if ( data.Diag_did ) {
			base_form.findField('Diag_did').getStore().load({
				callback: function() {
					base_form.findField('Diag_did').getStore().each(function(record) {
						if ( record.get('Diag_id') == data.Diag_did ) {
							base_form.findField('Diag_did').setValue(data.Diag_did);
							base_form.findField('Diag_did').disable();
							base_form.findField('Diag_did').fireEvent('select', base_form.findField('Diag_did'), record, 0);
						}
					});
				},
				params: { where: "where DiagLevel_id = 4 and Diag_id = " + data.Diag_did }
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
			var evn_ps_id = this.findById('EvnPSPriemEditForm').getForm().findField('EvnPS_id').getValue();
			window.open('/?c=EvnPS&m=printEvnPS&EvnPS_id=' + evn_ps_id, '_blank');
		}
	},
	setDisableFields: function() {
		var bf = this.findById('EvnPSPriemEditForm').getForm();
		for ( var i = 0; i < this.disableFields.length; i++ ) {
			bf.findField(this.disableFields[i]).disable();
		}
	},
	setEnableField: function(f) {
		var bf = this.findById('EvnPSPriemEditForm').getForm();
		if(this.disableFields.indexOf(f) < 0)
			bf.findField(f).enable();
	},
	setMKB: function(){
		var parentWin =this
		var base_form = this.findById('EvnPSPriemEditForm').getForm();
		var sex = parentWin.findById('EPSPEF_PersonInformationFrame').getFieldValue('Sex_Code');
		var age = swGetPersonAge(parentWin.findById('EPSPEF_PersonInformationFrame').getFieldValue('Person_Birthday'),base_form.findField('EvnPS_setDate').getValue());
		base_form.findField('Diag_did').setMKBFilter(age,sex,true);
		base_form.findField('Diag_pid').setMKBFilter(age,sex,true);
	},
	show: function() {
		sw.Promed.swEvnPSPriemEditWindow.superclass.show.apply(this, arguments);

		this.restore();
		this.center();
		this.maximize();

		var base_form = this.findById('EvnPSPriemEditForm').getForm();
		base_form.reset();
		
		if ( this.firstRun == true ) {
			this.findById('EPSPEF_HospitalisationPanel').collapse();
			this.findById('EPSPEF_DirectDiagPanel').collapse();
			this.findById('EPSPEF_AdmitDepartPanel').collapse();
			this.findById('EPSPEF_AdmitDiagPanel').collapse();
			this.findById('EPSPEF_PriemLeavePanel').collapse();
			this.findById('EPSPEF_EvnUslugaPanel').collapse();
			this.findById('EPSPEF_EvnDrugPanel').collapse();
			this.findById('EPSPEF_PrehospWaifPanel').collapse();
		}
		
		this.formStatus = 'edit';
		this.isCopy = false;
		
		if ( !arguments[0] ) {
			sw.swMsg.alert(lang['soobschenie'], lang['nevernyie_parametryi']);
			return false;
		}
		
		base_form.setValues(arguments[0]);
		this.action = arguments[0].action || null;
		this.callback = arguments[0].callback || Ext.emptyFn;
		this.onHide = arguments[0].onHide || Ext.emptyFn;
		this.disableFields = arguments[0].disableFields || [];
		this.TimetableStac_id = arguments[0].TimetableStac_id || null;
		this.userMedStaffFact = arguments[0].userMedStaffFact || {};

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT });
		loadMask.show();

		this.findById('EPSPEF_HospitalisationPanel').hide();
		this.findById('EPSPEF_DirectDiagPanel').hide();
		this.findById('EPSPEF_AdmitDepartPanel').hide();
		this.findById('EPSPEF_AdmitDiagPanel').hide();
		this.findById('EPSPEF_PriemLeavePanel').hide();
		this.findById('EPSPEF_EvnUslugaPanel').hide();
		this.findById('EPSPEF_EvnDrugPanel').hide();
		this.findById('EPSPEF_PrehospWaifPanel').hide();

		base_form.findField('EvnDirection_Num').disable();
		base_form.findField('EvnDirection_setDate').disable();
		base_form.findField('LpuSection_did').disable();
		base_form.findField('Org_did').disable();

		if ( this.action == 'add' ) {
			this.findById('EPSPEF_DirectDiagPanel').isLoaded = true;
			this.findById('EPSPEF_AdmitDiagPanel').isLoaded = true;
			this.findById('EPSPEF_EvnUslugaPanel').isLoaded = true;
			this.findById('EPSPEF_EvnDrugPanel').isLoaded = true;
		}
		else {
			this.findById('EPSPEF_DirectDiagPanel').isLoaded = false;
			this.findById('EPSPEF_AdmitDiagPanel').isLoaded = false;
			this.findById('EPSPEF_EvnUslugaPanel').isLoaded = false;
			this.findById('EPSPEF_EvnDrugPanel').isLoaded = false;
		}

		this.findById('EPSPEF_EvnDiagPSHospGrid').getStore().removeAll();
		this.findById('EPSPEF_EvnDiagPSHospGrid').getTopToolbar().items.items[0].enable();
		this.findById('EPSPEF_EvnDiagPSHospGrid').getTopToolbar().items.items[1].disable();
		this.findById('EPSPEF_EvnDiagPSHospGrid').getTopToolbar().items.items[2].disable();
		this.findById('EPSPEF_EvnDiagPSHospGrid').getTopToolbar().items.items[3].disable();

		this.findById('EPSPEF_EvnDiagPSRecepGrid').getStore().removeAll();
		this.findById('EPSPEF_EvnDiagPSRecepGrid').getTopToolbar().items.items[0].enable();
		this.findById('EPSPEF_EvnDiagPSRecepGrid').getTopToolbar().items.items[1].disable();
		this.findById('EPSPEF_EvnDiagPSRecepGrid').getTopToolbar().items.items[2].disable();
		this.findById('EPSPEF_EvnDiagPSRecepGrid').getTopToolbar().items.items[3].disable();

		this.findById('EPSPEF_EvnUslugaGrid').getStore().removeAll();
		this.findById('EPSPEF_EvnUslugaGrid').getTopToolbar().items.items[0].enable();
		this.findById('EPSPEF_EvnUslugaGrid').getTopToolbar().items.items[1].disable();
		this.findById('EPSPEF_EvnUslugaGrid').getTopToolbar().items.items[2].disable();
		this.findById('EPSPEF_EvnUslugaGrid').getTopToolbar().items.items[3].disable();

		this.findById('EPSPEF_EvnDrugGrid').getStore().removeAll();
		this.findById('EPSPEF_EvnDrugGrid').getTopToolbar().items.items[0].enable();
		this.findById('EPSPEF_EvnDrugGrid').getTopToolbar().items.items[1].disable();
		this.findById('EPSPEF_EvnDrugGrid').getTopToolbar().items.items[2].disable();
		this.findById('EPSPEF_EvnDrugGrid').getTopToolbar().items.items[3].disable();
/*
		setLpuSectionGlobalStoreFilter();

		prehosp_direct_combo.fireEvent('change', prehosp_direct_combo, null);
*/
		var evn_ps_id = base_form.findField('EvnPS_id').getValue();
		var person_id = base_form.findField('Person_id').getValue();
		var server_id = base_form.findField('Server_id').getValue();

		var set_date_field = base_form.findField('EvnPS_setDate');
		var iswd_combo = base_form.findField('EvnPS_IsWithoutDirection');
		var diag_d_combo = base_form.findField('Diag_did');
		var diag_p_combo = base_form.findField('Diag_pid');
		var lpu_section_dir_combo = base_form.findField('LpuSection_did');
		var lpu_section_rec_combo = base_form.findField('LpuSection_pid');
		var med_staff_fact_rec_combo = base_form.findField('MedStaffFact_pid');
		var org_combo = base_form.findField('Org_did');
		var prehosp_arrive_combo = base_form.findField('PrehospArrive_id');
		var prehosp_direct_combo = base_form.findField('PrehospDirect_id');
		var prehosp_trauma_combo = base_form.findField('PrehospTrauma_id');
		var prehosp_type_combo = base_form.findField('PrehospType_id');
		var lpu_section_hosp_combo = base_form.findField('LpuSection_eid');
		var refuse_cause_combo = base_form.findField('PrehospWaifRefuseCause_id');
		var okei_combo = base_form.findField('Okei_id');
		this.findById('EPSPEF_PrehospWaifRefuseCauseButton').disable();
		var is_waif_combo = base_form.findField('EvnPS_IsWaif');
		var priemDiag = (getGlobalOptions().check_priemdiag_allow && getGlobalOptions().check_priemdiag_allow=='1');
		is_waif_combo.setAllowBlank(true);
		var person_info = this.findById('EPSPEF_PersonInformationFrame');

		okei_combo.setValue(100); // По умолчанию: час

		this.setDiagPAllowBlank();
		
		prehosp_direct_combo.getStore().clearFilter();

		lpu_section_dir_combo.getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));
		
		setMedStaffFactGlobalStoreFilter({
			EvnClass_SysNick: 'EvnPS',
			isStac:true
		});
		base_form.findField('MedStaffFact_id').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));
		
		set_date_field.setMinValue(undefined);

		switch ( this.action ) {
			case 'add':
				this.enableEdit(true);

				if (false && set_date_field.getValue()) {
					set_date_field.fireEvent('change', set_date_field, set_date_field.getValue());
				} else {
					setCurrentDateTime({
						callback: function() {
							set_date_field.fireEvent('change', set_date_field, set_date_field.getValue());
						},
						dateField: set_date_field,
						loadMask: false,
						setDate: true,
						setDateMaxValue: true,
						setTime: true,
						timeField: base_form.findField('EvnPS_setTime'),
						windowId: this.id
					});
				}
				
				base_form.findField('PrehospWaifRefuseCause_id').fireEvent('change', base_form.findField('PrehospWaifRefuseCause_id'), base_form.findField('PrehospWaifRefuseCause_id').getValue());

				person_info.load({
					callback: function() {
						loadMask.hide();
						person_info.setPersonTitle();
						base_form.findField('EvnPS_setDate').setMinValue(person_info.getFieldValue('Person_Birthday'));
						if(person_info.getFieldValue('Person_Age') < 18)
						{
							this.findById('EPSPEF_PrehospWaifPanel').show();
							is_waif_combo.setAllowBlank(false);
							is_waif_combo.fireEvent('change', is_waif_combo,1, null);
						}
						this.setDisableFields();
						this.setMKB();
					}.createDelegate(this),
					Person_id: person_id,
					Server_id: server_id
				});

				if ( this.firstRun == true ) {
					this.findById('EPSPEF_HospitalisationPanel').expand();
					this.findById('EPSPEF_AdmitDepartPanel').expand();

					this.firstRun = false;
				}

				// base_form.clearInvalid();

				lpu_section_rec_combo.fireEvent('change', lpu_section_rec_combo, lpu_section_rec_combo.getValue());
				prehosp_trauma_combo.fireEvent('change', prehosp_trauma_combo, prehosp_trauma_combo.getValue());
				base_form.findField('EvnPS_IsUnlaw').fireEvent('change', base_form.findField('EvnPS_IsUnlaw'), base_form.findField('EvnPS_IsUnlaw').getValue());

				this.getEvnPSNumber();
				this.loadBedList();
				this.setBedListAllowBlank();

				this.findById('EPSPEF_HospitalisationPanel').show();
				this.findById('EPSPEF_DirectDiagPanel').show();
				this.findById('EPSPEF_AdmitDepartPanel').show();
				this.findById('EPSPEF_AdmitDiagPanel').show();
				this.findById('EPSPEF_PriemLeavePanel').show();
				this.findById('EPSPEF_EvnUslugaPanel').show();
				this.findById('EPSPEF_EvnDrugPanel').show();

				LoadEmptyRow(this.findById('EPSPEF_EvnDiagPSHospGrid'));
				LoadEmptyRow(this.findById('EPSPEF_EvnDiagPSRecepGrid'));
				LoadEmptyRow(this.findById('EPSPEF_EvnUslugaGrid'));
				LoadEmptyRow(this.findById('EPSPEF_EvnDrugGrid'));

				if ( !prehosp_type_combo.getValue() ) {
					prehosp_type_combo.setValue(2);
				}

				prehosp_type_combo.getStore().on('load', function(store, records, index) {
					prehosp_type_combo.setValue(prehosp_type_combo.getValue());
				});

				prehosp_arrive_combo.getStore().on('load', function(store, records, index) {
					prehosp_arrive_combo.setValue(prehosp_arrive_combo.getValue());
				});

				if ( arguments[0].EvnDirection_id ) {
					this._onSelectEvnDirection(arguments[0]);
				} else {
					var org_did = org_combo.getValue();
					iswd_combo.setValue(1);
					prehosp_arrive_combo.fireEvent('change', prehosp_arrive_combo, prehosp_arrive_combo.getValue());
					iswd_combo.fireEvent('change', iswd_combo, iswd_combo.getValue());
					prehosp_direct_combo.getStore().on('load', function(store, records, index) {
						prehosp_direct_combo.setValue(prehosp_direct_combo.getValue());
						var record = prehosp_direct_combo.getStore().getById(prehosp_direct_combo.getValue());
						if ( !record ) {
							return;
						}
						var org_type = '';
						switch ( record.get('PrehospDirect_Code') ) {
							case 4:
								org_type = 'lpu';
							break;

							case 1:
							case 2:
							case 3:
							case 5:
							case 6:
								org_type = 'org';
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
						else {
							org_combo.clearValue();
						}
					});
				}

			break;

			case 'edit':
			case 'view':
				if ( 'edit' == this.action ) {
					this.enableEdit(true);

				}
				else {
					this.enableEdit(false);
					this.buttons[this.buttons.length - 1].focus();
				}

				this.findById('EPSPEF_HospitalisationPanel').show();
				this.findById('EPSPEF_DirectDiagPanel').show();
				this.findById('EPSPEF_AdmitDepartPanel').show();
				this.findById('EPSPEF_AdmitDiagPanel').show();
				this.findById('EPSPEF_PriemLeavePanel').show();
				this.findById('EPSPEF_PriemLeavePanel').expand();
				this.findById('EPSPEF_EvnUslugaPanel').show();
				this.findById('EPSPEF_EvnDrugPanel').show();

				this.isProcessLoadForm = true;
				base_form.load({
					failure: function() {
						loadMask.hide();
						sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_zagruzke_dannyih_formyi'], function() { this.hide(); }.createDelegate(this) );
					}.createDelegate(this),
					params: {
						EvnPS_id: evn_ps_id
					},
					success: function() {
						loadMask.hide();

						var evn_direction_id = base_form.findField('EvnDirection_id').getValue();
						var evn_direction_num = base_form.findField('EvnDirection_Num').getValue();
						var evn_direction_set_date = base_form.findField('EvnDirection_setDate').getValue();
						var evn_ps_code_conv = base_form.findField('EvnPS_CodeConv').getValue();
						var evn_ps_is_cont = base_form.findField('EvnPS_IsCont').getValue();
						var evn_ps_is_unlaw = base_form.findField('EvnPS_IsUnlaw').getValue();
						var evn_ps_num_conv = base_form.findField('EvnPS_NumConv').getValue();
						var lpu_section_did = lpu_section_dir_combo.getValue();
						var med_staff_fact_pid = med_staff_fact_rec_combo.getValue();
						var org_did = org_combo.getValue();
						var prehosp_arrive_id = prehosp_arrive_combo.getValue();
						var prehosp_direct_id = prehosp_direct_combo.getValue();
						var prehosp_trauma_id = prehosp_trauma_combo.getValue();
						var diag_did = diag_d_combo.getValue();
						var lpu_section_pid = lpu_section_rec_combo.getValue();
						var diag_pid = diag_p_combo.getValue();
						
						var lpu_section_id = lpu_section_hosp_combo.getValue();
						var prehospwaif_refuse_cause_id = refuse_cause_combo.getValue();
						var is_waif_yesno = is_waif_combo.getValue();

						if ( !lpu_section_pid && 'edit' == this.action ) {
							lpu_section_pid = this.userMedStaffFact.LpuSection_id;
							lpu_section_rec_combo.setValue(lpu_section_pid);
						}
						
						if ( !diag_pid && 'edit' == this.action ) {
							diag_p_combo.enable();
						}
						
						base_form.findField('PrehospWaifRefuseCause_id').fireEvent('change', base_form.findField('PrehospWaifRefuseCause_id'), base_form.findField('PrehospWaifRefuseCause_id').getValue());
						
						person_info.load({
							callback: function() {
								person_info.setPersonTitle();
								base_form.findField('EvnPS_setDate').setMinValue(person_info.getFieldValue('Person_Birthday'));
								if(person_info.getFieldValue('Person_Age') < 18)
								{
									this.findById('EPSPEF_PrehospWaifPanel').show();
									is_waif_combo.setAllowBlank(false);
									is_waif_combo.fireEvent('change', is_waif_combo, is_waif_yesno, null);
								}
								lpu_section_rec_combo.fireEvent('change', lpu_section_rec_combo, lpu_section_pid);
								if('edit' == this.action)
								{
									var record;

									base_form.findField('EvnPS_IsCont').fireEvent('change', base_form.findField('EvnPS_IsCont'), evn_ps_is_cont);

									if ( lpu_section_pid ) {
										diag_p_combo.enable();
									}

									if ( evn_direction_id ) {
										iswd_combo.setValue(2);
										iswd_combo.disable();
										this.disableFields.push('EvnPS_IsWithoutDirection');
									} else {
										iswd_combo.setValue(1);
									}
									iswd_combo.fireEvent('change', iswd_combo, iswd_combo.getValue());
									
									if ( prehosp_direct_id != null && prehosp_direct_id.toString().length > 0 ) {
										// prehosp_direct_combo.fireEvent('change', prehosp_direct_combo, prehosp_direct_id, -1);
										// prehosp_direct_combo.disable();


										record = prehosp_direct_combo.getStore().getById(prehosp_direct_id);

										if ( !record ) {
											loadMask.hide();
											return false;
										}

										var prehosp_direct_code = record.get('PrehospDirect_Code');
										var org_type = '';

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

									base_form.findField('EvnDirection_id').setValue(evn_direction_id);
									base_form.findField('EvnDirection_Num').setValue(evn_direction_num);
									base_form.findField('EvnDirection_setDate').setValue(evn_direction_set_date);

									prehosp_arrive_combo.fireEvent('change', prehosp_arrive_combo, prehosp_arrive_id, -1);
									base_form.findField('EvnPS_CodeConv').setValue(evn_ps_code_conv);
									base_form.findField('EvnPS_NumConv').setValue(evn_ps_num_conv);

									base_form.findField('EvnPS_IsUnlaw').setValue(evn_ps_is_unlaw);
									prehosp_trauma_combo.fireEvent('change', prehosp_trauma_combo, prehosp_trauma_id, -1);
									base_form.findField('EvnPS_IsUnlaw').setValue(evn_ps_is_unlaw);
									base_form.findField('EvnPS_IsUnlaw').fireEvent('change', base_form.findField('EvnPS_IsUnlaw'), base_form.findField('EvnPS_IsUnlaw').getValue());

									// base_form.clearInvalid();

									this.setDisableFields();
									
									base_form.findField('EvnPS_IsCont').focus(true, 250);
								}
							}.createDelegate(this),
							Person_id: base_form.findField('Person_id').getValue(),
							PersonEvn_id: base_form.findField('PersonEvn_id').getValue(),
							Server_id: base_form.findField('Server_id').getValue()
						});
						
						// Остальные гриды - только если развернуты панельки
						if ( !this.findById('EPSPEF_DirectDiagPanel').collapsed ) {
							this.findById('EPSPEF_DirectDiagPanel').fireEvent('expand', this.findById('EPSPEF_DirectDiagPanel'));
						}

						if ( !this.findById('EPSPEF_AdmitDiagPanel').collapsed ) {
							this.findById('EPSPEF_AdmitDiagPanel').fireEvent('expand', this.findById('EPSPEF_AdmitDiagPanel'));
						}


						if ( !this.findById('EPSPEF_EvnUslugaPanel').collapsed ) {
							this.findById('EPSPEF_EvnUslugaPanel').fireEvent('expand', this.findById('EPSPEF_EvnUslugaPanel'));
						}

						if ( !this.findById('EPSPEF_EvnDrugPanel').collapsed ) {
							this.findById('EPSPEF_EvnDrugPanel').fireEvent('expand', this.findById('EPSPEF_EvnDrugPanel'));
						}
						
						set_date_field.fireEvent('change', set_date_field, set_date_field.getValue());

						var index = med_staff_fact_rec_combo.getStore().findBy(function(record, id) {
							if ( record.get('MedStaffFact_id') == med_staff_fact_pid )
								return true;
							else
								return false;
						})

						if ( index >= 0 ) {
							med_staff_fact_rec_combo.setValue(med_staff_fact_rec_combo.getStore().getAt(index).get('MedStaffFact_id'));
							med_staff_fact_rec_combo.fireEvent('change', med_staff_fact_rec_combo, med_staff_fact_rec_combo.getValue());
						}

						if ( diag_did )
						{
							diag_d_combo.getStore().load({
								callback: function() {
									diag_d_combo.setValue(diag_did);
									diag_d_combo.fireEvent('select', diag_d_combo, diag_d_combo.getStore().getAt(0), 0);
									diag_d_combo.disable();
								},
								params: {
									where: "where DiagLevel_id = 4 and Diag_id = " + diag_did
								}
							});
						}

						if ( diag_pid )
						{
							diag_p_combo.getStore().load({
								callback: function() {
									diag_p_combo.fireEvent('select', diag_p_combo, diag_p_combo.getStore().getAt(0), 0);
								},
								params: {
									where: "where DiagLevel_id = 4 and Diag_id = " + diag_pid
								}
							});
						}

						if ( lpu_section_id )
						{
							index = lpu_section_hosp_combo.getStore().findBy(function(rec, id) {
								return (rec.get('LpuSection_id') == lpu_section_id);
							});

							lpu_section_hosp_combo.fireEvent('select', lpu_section_hosp_combo, lpu_section_hosp_combo.getStore().getAt(index), index);
						}

						refuse_cause_combo.fireEvent('change', refuse_cause_combo, prehospwaif_refuse_cause_id, null);
						this.isProcessLoadForm = false;
						this.loadBedList();
						this.setBedListAllowBlank();
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
			base_form = this.findById('EvnPSPriemEditForm').getForm(),
			getbed_field = base_form.findField('GetBed_id'),
			allowBlank = !base_form.findField('LpuSection_pid').getValue() || !base_form.findField('PayType_id').getFieldValue('PayType_SysNick').inlist(['bud', 'Resp']);

		getbed_field.setAllowBlank(allowBlank);
	},
	loadBedList: function() {
		var win = this,
			base_form = this.findById('EvnPSPriemEditForm').getForm(),
			getbed_field = base_form.findField('GetBed_id');

		getbed_field.lastQuery = '';
		getbed_field.getStore().load({
			params: {
				Lpu_id: getGlobalOptions().lpu_id,
				LpuSection_id: base_form.findField('LpuSection_pid').getValue(),
				Person_id: base_form.findField('Person_id').getValue(),
				GetBed_id: win.action == 'view' ? getbed_field.getValue() : null
			},
			callback: function() {
				getbed_field.setValue(getbed_field.getValue());
			}
		});
	}
});