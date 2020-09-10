/**
* swEvnPLEditWindow - окно редактирования/добавления талона амбулаторного пациента.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Polka
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Stas Bykov aka Savage (savage@swan.perm.ru)
* @version      0.002-16.11.2009
* @comment      Префикс для id компонентов EPLEF (EvnPLEditForm)
*
*
* @input data: action - действие (add, edit, view)
*              EvnPL_id - ID талона для редактирования или просмотра
*              Person_id - ID человека
*              PersonEvn_id - ID состояния человека
*              Server_id - ID сервера
*
*
* Использует: окно выписки листа нетрудоспособности (swEvnStickEditWindow)
*             окно выписки справки учащегося (swEvnStickStudentEditWindow)
*             окно редактирования посещения (swEvnVizitPLEditWindow)
*             окно редактирования общей услуги (swEvnUslugaCommonEditWindow)
*             окно добавления комплексной услуги (swEvnUslugaComplexEditWindow)
*             окно поиска организации (swOrgSearchWindowWindow)
*/

sw.Promed.swEvnPLEditWindow = Ext.extend(sw.Promed.BaseForm, {
	action: null,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: false,
	closeAction: 'hide',
	collapsible: true,
	undoDeleteEvnStick: function() {
		var win = this;
		var grid = this.findById('EPLEF_EvnStickGrid');

		if ( !grid || !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('EvnStick_id') ) {
			return false;
		}

		var selected_record = grid.getSelectionModel().getSelected();

		win.getLoadMask('Отмена удаления ЛВН').show();
		Ext.Ajax.request({
			params: {
				EvnStick_id: selected_record.get('EvnStick_id')
			},
			callback: function(options, success, response) {
				win.getLoadMask().hide();

				if (success) {
					var response_obj = Ext.util.JSON.decode(response.responseText);

					if (response_obj.success) {
						sw.swMsg.alert('Внимание', 'ЛВН успешно восстановлен');

						grid.getStore().load({
							params: {
								EvnStick_pid: selected_record.get('EvnStick_pid')
							}
						});
					}
				}
			}.createDelegate(this),
			url: '/?c=Stick&m=undoDeleteEvnStick'
		});
	},
	deleteEvent: function(event, options) {
		options = options || {};

		if ( this.action == 'view' && !(event == 'EvnStick' && this.evnStickAction != 'view')) {
			return false;
		}

		if ( !event.inlist([ 'EvnDrug', 'EvnStick', 'EvnUsluga', 'EvnVizitPL' ]) ) {
			return false;
		}
/*
		if ( event == 'EvnUsluga' && getGlobalOptions().region && getGlobalOptions().region.nick == 'ufa' ) {
			return false;
		}
*/
		var base_form = this.findById('EvnPLEditForm').getForm(),
		    error = '',
            _this = this,
		    grid = null,
		    question = '',
		    params = {},
		    url = '',
		    lastEvnDeleted = false;

		if (options.params) {
			params = options.params;
		}

		switch ( event ) {
			case 'EvnDrug':
				grid = this.findById('EPLEF_EvnDrugGrid');
			break;

			case 'EvnStick':
				grid = this.findById('EPLEF_EvnStickGrid');
			break;

			case 'EvnUsluga':
				grid = this.findById('EPLEF_EvnUslugaGrid');
			break;

			case 'EvnVizitPL':
				grid = this.findById('EPLEF_EvnVizitPLGrid');
			break;
		}


		if ( !grid || !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get(event + '_id') ) {
			return false;
		}

		var selected_record = grid.getSelectionModel().getSelected();

		switch ( event ) {
			case 'EvnDrug':
				error = lang['pri_udalenii_sluchaya_ispolzovaniya_medikamentov_voznikli_oshibki'];
				question = lang['udalit_sluchay_ispolzovaniya_medikamentov'];
				url = '/?c=EvnDrug&m=deleteEvnDrug';

				params['EvnDrug_id'] = selected_record.get('EvnDrug_id');
			break;

			case 'EvnStick':
				var evn_pl_id = base_form.findField('EvnPL_id').getValue();
				var evn_stick_mid = selected_record.get('EvnStick_mid');

				if ( selected_record.get('evnStickType') == 3 ) {
					if ( evn_pl_id == evn_stick_mid ) {
						error = lang['pri_udalenii_spravki_uchaschegosya_voznikli_oshibki'];
						question = lang['udalit_spravku_uchaschegosya'];
					}
					else {
						error = lang['pri_udalenii_svyazi_spravki_uchaschegosya_s_tekuschim_dokumentom_voznikli_oshibki'];
						question = lang['udalit_svyaz_spravki_uchaschegosya_s_tekuschim_dokumentom'];
					}

					url = '/?c=Stick&m=deleteEvnStickStudent';

					params['EvnStickStudent_id'] = selected_record.get('EvnStick_id');
					params['EvnStickStudent_mid'] = evn_pl_id;
				}
				else {
					error = lang['pri_udalenii_lvn_voznikli_oshibki'];
					question = lang['udalit_lvn'];

					url = '/?c=Stick&m=deleteEvnStick';

					params['EvnStick_id'] = selected_record.get('EvnStick_id');
					params['EvnStick_mid'] = evn_pl_id;
				}
			break;

			case 'EvnUsluga':
				error = lang['pri_udalenii_uslugi_voznikli_oshibki'];
				question = lang['udalit_uslugu'];
				url = '/?c=EvnUsluga&m=deleteEvnUsluga';

				params['class'] = selected_record.get('EvnClass_SysNick');
				params['id'] = selected_record.get('EvnUsluga_id');

				if (getRegionNick() == 'perm' && base_form.findField('EvnPL_RepFlag').checked) {
					params.ignorePaidCheck = 1;
				}
			break;

			case 'EvnVizitPL':
				if ( this.action == 'view' || selected_record.get('accessType') != 'edit'
					|| (
						!Ext.isEmpty(getGlobalOptions().medpersonal_id)
						&& !Ext.isEmpty(selected_record.get('MedPersonal_id'))
						&& userHasWorkPlaceAtLpuSection(selected_record.get('LpuSection_id')) == false
						&& getGlobalOptions().isMedStatUser != true
						&& isSuperAdmin() != true
					)
					|| selected_record.get('EvnVizitPL_IsSigned') != 1
				) {
					return false;
				}

				error = lang['pri_udalenii_posescheniya_voznikli_oshibki'];
				question = lang['udalit_poseschenie'];
				params['Evn_id'] = selected_record.get('EvnVizitPL_id');
				var count = grid.getStore().getCount();
				if (count == 1) {
					params['Evn_id'] = base_form.findField('EvnPL_id').getValue();
					question += lang['budet_udalen_ves_talon_ambulatornogo_patsienta'];
					lastEvnDeleted = true;
				}
				url = '/?c=Evn&m=deleteEvn';

				if ( getRegionNick() == 'ufa' ) {
					if ( selected_record.get('EvnVizitPL_IsPaid') == 2 ) {
						question = lang['dannyiy_sluchay_oplachen_vyi_deystvitelno_hotite_udalit_dannoe_poseschenie'];
					}
				}
			break;
		}

		var alert = {
			EvnVizitPL: {
				'701': {
					buttons: Ext.Msg.YESNO,
					fn: function(buttonId, scope, params) {
						if (buttonId == 'yes') {
							options.ignoreDoc = true;
							scope.deleteEvent(event, options);
						}
					}
				},
				'703': {
					buttons: Ext.Msg.YESNO,
					fn: function(buttonId, scope, params) {
						if (buttonId == 'yes') {
							options.ignoreCheckEvnUslugaChange = true;
							scope.deleteEvent(event, options);
						}
					}
				},
				'808': {
					buttons: Ext.Msg.YESNO,
					fn: function(buttonId, scope, params) {
						if (buttonId == 'yes') {
							Ext.Ajax.request({
								params: params,
								url: '/?c=HomeVisit&m=RevertHomeVizitStatus',
								success: function(response) {
									var resp = Ext.util.JSON.decode(response.responseText);
									if (Ext.isEmpty(resp.Error_Msg)) {
										options.ignoreHomeVizit = true;
										scope.deleteEvent(event, options);
									} else {
										sw.swMsg.alert(langs('Ошибка'), resp.Error_Msg);
									}
								}
							});
						}
					}
				},
				'809': {
					buttons: Ext.Msg.YESNO,
					fn: function(buttonId, scope, params) {
						if (buttonId == 'yes') {
							Ext.Ajax.request({
								params: params,
								url: '/?c=HomeVisit&m=RevertHomeVizitStatusesTAP',
								callback: function(opts, success, response) {
									if (success) {
										var resp = Ext.util.JSON.decode(response.responseText);
										if (Ext.isEmpty(resp.Error_Msg)) {
											options.ignoreHomeVizit = true;
											scope.deleteEvent(event, options);
										} else {
											sw.swMsg.alert(langs('Ошибка'), resp.Error_Msg);
										}
									} else {
										sw.swMsg.alert(langs('Ошибка'), 'При измененении статусов вызовов на дом возникли ошибки');
									}
								}
							});
						}
					}
				}
			}
		};

		alert['EvnStick'] = sw.Promed.EvnStick.getDeleteAlertCodes({
			callback: function(options) {
				_this.deleteEvent(event, options);
			},
			options: options
		});

		if (options.ignoreDoc) {
			params['ignoreDoc'] = options.ignoreDoc;
		}

		if (options.ignoreCheckEvnUslugaChange) {
			params.ignoreCheckEvnUslugaChange = options.ignoreCheckEvnUslugaChange;
		}

		if (options.ignoreHomeVizit) {
			params.ignoreHomeVizit = options.ignoreHomeVizit;
		}

		if (options.StickCauseDel_id) {
			params.StickCauseDel_id = options.StickCauseDel_id;
		}

		var doDelete = function() {
			var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Удаление записи..."});
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
						if (response_obj.Alert_Msg) {
							if (response_obj.Alert_Code == 705) {
								getWnd('swStickCauseDelSelectWindow').show({
									countNotPaid: response_obj.countNotPaid,
									existDuplicate: response_obj.existDuplicate,
									callback: function(StickCauseDel_id) {
										if (StickCauseDel_id) {
											options.ignoreQuestion = true;
											options.StickCauseDel_id = StickCauseDel_id;
											this.deleteEvent(event, options);
										}
									}.createDelegate(this)
								});
							} else {
							var a_params = alert[event][response_obj.Alert_Code];
							sw.swMsg.show({
								buttons: a_params.buttons,
								fn: function(buttonId) {
										a_params.fn(buttonId, this, params);
								}.createDelegate(this),
								msg: response_obj.Alert_Msg,
								icon: Ext.MessageBox.QUESTION,
								title: lang['vopros']
							});
							}
						} else {
							sw.swMsg.alert(lang['oshibka'], response_obj.Error_Msg ? response_obj.Error_Msg : error);
						}
					}
					else {
						if (response_obj.IsDelQueue) {
							sw.swMsg.alert('Внимание', 'ЛВН добавлен в очередь на удаление');
							selected_record.set('EvnStick_IsDelQueue', 2);
							selected_record.set('accessType', 'view');
							selected_record.commit();
						} else {
							grid.getStore().remove(selected_record);
						}

						if ( grid.getStore().getCount() == 0 ) {
							grid.getTopToolbar().items.items[1].disable();
							grid.getTopToolbar().items.items[2].disable();
							grid.getTopToolbar().items.items[3].disable();
							LoadEmptyRow(grid);
						}

						if ( event == 'EvnVizitPL' ) {
							if (lastEvnDeleted == false) {

								// Перезагрузить грид с услугами
								if ( this.findById('EPLEF_EvnUslugaPanel').isLoaded === true ) {
									this.findById('EPLEF_EvnUslugaGrid').getStore().load({
										params: {
											pid: this.findById('EvnPLEditForm').getForm().findField('EvnPL_id').getValue()
										}
									});
								}

								// обновляем MedicalCareKind
								// если профиль последнего посещения - "57. Общая врачебная практика (семейная медицина)", проставлять значение 8 - общеврачебная практика, Для остальных 1 - амбулаторный прием
								if (getRegionNick() == 'kareliya') {
									var lastEvnVizitPLData = null;
									this.findById('EPLEF_EvnVizitPLGrid').getStore().each(function(record) {
										if ( Ext.isEmpty(lastEvnVizitPLData) || (!Ext.isEmpty(record.get('EvnVizitPL_setDate')) && record.get('EvnVizitPL_setDate') >= lastEvnVizitPLData.EvnVizitPL_setDate) ) {
											lastEvnVizitPLData = record.data;
										}
									});

									if (Ext.isEmpty(base_form.findField('MedicalCareKind_id').getValue())) {
										if (lastEvnVizitPLData && lastEvnVizitPLData.LpuSectionProfile_Code == '57') {
											base_form.findField('MedicalCareKind_id').setFieldValue('MedicalCareKind_Code', 8);
										} else {
											base_form.findField('MedicalCareKind_id').setFieldValue('MedicalCareKind_Code', 1);
										}
									}
								}
							} else {
								this.callback({
									evnPLData: {
										lastEvnDeleted: true,
										EvnPL_id: base_form.findField('EvnPL_id').getValue()
									}
								});
								this.hide();
							}
							this.refreshFieldsVisibility(['MedicalCareBudgType_id']);
						}

						grid.getView().focusRow(0);
						grid.getSelectionModel().selectFirstRow();
					}
				}.createDelegate(this),
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
	openStickFSSDataEditWindow: function () {
		var win = this;
		var base_form = this.findById('EvnPLEditForm').getForm();

		var rec = this.findById('EPLEF_EvnStickGrid').getSelectionModel().getSelected();

		var options = {
			action: 'add',
			ignoreCheckExist: true
		}
		var rec = win.findById('EPLEF_EvnStickGrid').getSelectionModel().getSelected();
		if (rec) {
			options.Person_id = rec.get('Person_id');
			options.StickFSSData_StickNum = rec.get('EvnStick_Num');
		}
		options.callback = function () {
			win.findById('EPLEF_EvnStickGrid').getStore().load({
				params: {
					EvnStick_pid: base_form.findField('EvnPL_id').getValue()
				}
			});
		}
		getWnd('swStickFSSDataEditWindow').show(options);
	},
	getLastEvnVizitPL: function() {
		var last = null;
		this.findById('EPLEF_EvnVizitPLGrid').getStore().each(function(record) {
			if ( Ext.isEmpty(last) || (!Ext.isEmpty(record.get('EvnVizitPL_setDate')) && record.get('EvnVizitPL_setDate') >= last.EvnVizitPL_setDate) ) {
				last = record.data;
			}
		});
		return last;
	},
	refreshFieldsVisibility: function(fieldNames) {
		var win = this;
		var base_form = win.findById('EvnPLEditForm').getForm();
		if (typeof fieldNames == 'string') fieldNames = [fieldNames];

		var action = win.action;
		var Region_Nick = getRegionNick();

		var lastEvnVizitPL = win.getLastEvnVizitPL();

		base_form.items.each(function(field){
			if (!Ext.isEmpty(fieldNames) && !field.getName().inlist(fieldNames)) return;

			var value = field.getValue();
			var allowBlank = null;
			var visible = null;
			var enable = null;
			var filter = null;

			var EvnPL_IsFinish = base_form.findField('EvnPL_IsFinish').getValue();

			switch(field.getName()) {
				case 'MedicalCareBudgType_id':
					visible = (
						Region_Nick.inlist(['perm', 'astra', 'ufa', 'kareliya', 'krym', 'pskov']) &&
						EvnPL_IsFinish == 2 &&
						lastEvnVizitPL && lastEvnVizitPL.PayType_SysNick.inlist(['bud','fbud','subrf','mbudtrans_mbud'])
					);
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
	doSave: function(options) {
		// options @Object
		// options.ignoreEvnVizitPLCountCheck @Boolean Не проверять наличие посещений, если true
		// options.ignoreDiagCountCheck @Boolean Не проверять наличие основного диагноза, если true
		// options.print @Boolean Вызывать печать рецепта, если true
		// options.openChildWindow @Function Открыть дочернее окно после сохранения

		if ( this.formStatus == 'save' || (this.action == 'view' && !this.canCreateVizit)) {
			return false;
		}
		var wnd = this;
		this.formStatus = 'save';
		options = options||{};
		options.ignoreErrors = options.ignoreErrors || [];

		var base_form = this.findById('EvnPLEditForm').getForm();
		if(!this.checkTrauma()){
			var trField =base_form.findField('PrehospTrauma_id');
			if(Ext.isEmpty(trField.getValue())){
			sw.swMsg.show({
					buttons: Ext.Msg.OK,
					fn: function() {
						wnd.formStatus = 'edit';
						trField.focus(false);
					},
					icon: Ext.Msg.WARNING,
					msg: lang['pri_diagnozah_kategoriy_s_i_t_pole_vid_travmyi_vneshnego_vozdeystviya_doljno_byit_zapolneno'],
					title: ERR_INVFIELDS_TIT
				});
				return false;
			}
		}
		if ( !options.ignoreFormValidation && !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					this.formStatus = 'edit';
					this.findById('EvnPLEditForm').getFirstInvalidEl().focus(false);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		var evn_vizit_pl_store = this.findById('EPLEF_EvnVizitPLGrid').getStore();

		if ( !options || !options.ignoreEvnVizitPLCountCheck ) {
			if ( evn_vizit_pl_store.getCount() == 0 || (evn_vizit_pl_store.getCount() == 1 && !evn_vizit_pl_store.getAt(0).get('EvnVizitPL_id')) ) {
				sw.swMsg.show({
					buttons: Ext.Msg.OK,
					fn: function() {
						this.formStatus = 'edit';
						this.findById('EPLEF_EvnVizitPLGrid').getView().focusRow(0);
						this.findById('EPLEF_EvnVizitPLGrid').getSelectionModel().selectFirstRow();
					}.createDelegate(this),
					icon: Ext.Msg.WARNING,
					msg: lang['ne_vvedeno_ni_odnogo_posescheniya_sohranenie_talona_nevozmojno'],
					title: ERR_INVFIELDS_TIT
				});
				return false;
			}
		}

		var omsPayTypeExists = false;
		var pay_type_nick = '';
		evn_vizit_pl_store.each(function(rec) {
			if ( rec.get('PayType_SysNick') && !pay_type_nick ) {
				pay_type_nick = rec.get('PayType_SysNick');
			}
			if ( rec.get('PayType_SysNick') == 'oms' ) {
				omsPayTypeExists = true;
			}
		});

		// проверки по контролю дат госпитализации и направления согласно #110233
		// проверка нужна для всех, #137188
		var firstEvnVizitPLData;
		this.findById('EPLEF_EvnVizitPLGrid').getStore().each(function(record) {
			if ( Ext.isEmpty(firstEvnVizitPLData) || (!Ext.isEmpty(record.get('EvnVizitPL_setDate')) && record.get('EvnVizitPL_setDate') <= firstEvnVizitPLData.EvnVizitPL_setDate) ) {
				firstEvnVizitPLData = record.data;
			}
		});

		var EvnDirection_setDate = base_form.findField('EvnDirection_setDate').getValue(),
			EvnPL_setDate = firstEvnVizitPLData.EvnVizitPL_setDate;

		// Если дата направления больше даты первого случая
		if ( getRegionNick() === 'ekb' && getOthersOptions().checkEvnDirectionDate && EvnPL_setDate instanceof Date &&  EvnDirection_setDate instanceof Date && EvnDirection_setDate.getTime() > EvnPL_setDate.getTime() ) {
			this.formStatus = 'edit';
			Ext.Msg.alert(langs('Ошибка'), langs('Дата выписки направления позже даты начала случая. Дата направления должна быть раньше или совпадать с датой начала случая. Проверьте дату выписки направления'));
			return false;
		}

		if (getRegionNick() == 'kareliya'){
			var needToBeOnlyOneVizit = false;
			evn_vizit_pl_store.each(function(rec) {
				if ( rec.get('VizitType_SysNick') ) {
					if ( rec.get('VizitType_SysNick').inlist(['kompdiagslkontr', 'kompdiagvou', 'kompdiagdo40vsbor', 'kompdiagst40vsbor'])) {
						needToBeOnlyOneVizit = true;
					}
				}
			});

			if (evn_vizit_pl_store.getCount() > 1 && needToBeOnlyOneVizit){
				sw.swMsg.show({
					buttons: Ext.Msg.OK,
					fn: function() {
						this.formStatus = 'edit';
						this.findById('EPLEF_EvnVizitPLGrid').getView().focusRow(0);
						this.findById('EPLEF_EvnVizitPLGrid').getSelectionModel().selectFirstRow();
					}.createDelegate(this),
					icon: Ext.Msg.WARNING,
					msg: lang['esli_v_poseschenii_ukazana_tsel_s_kodom_37_-_40_to_doljno_byit_tolko_odno_poseschenie'],
					title: ERR_INVFIELDS_TIT
				});
				return false;
			}
		}

		var evn_pl_ukl = base_form.findField('EvnPL_UKL').getValue();
		var is_finish = base_form.findField('EvnPL_IsFinish').getValue();
		var result_class_id = base_form.findField('ResultClass_id').getValue();
		var result_desease_type_id = base_form.findField('ResultDeseaseType_id').getValue();
		var setDate =null;
		evn_vizit_pl_store.each(function(record) {
								if ( typeof setDate != 'object' || record.get('EvnVizitPL_setDate') <= setDate ) {
									setDate = record.get('EvnVizitPL_setDate');
								}
							});
		var lpusection_oid = base_form.findField('LpuSection_oid').getValue();
		var person_age = swGetPersonAge(this.findById('EPLEF_PersonInformationFrame').getFieldValue('Person_Birthday'), setDate);

		if(lpusection_oid&&person_age!=-1){
			if(!options.ignoreLpuSectionAgeCheck&& ((base_form.findField('LpuSection_oid').getFieldValue('LpuSectionAge_id') == 1 && person_age <= 17) || (base_form.findField('LpuSection_oid').getFieldValue('LpuSectionAge_id') == 2 && person_age >= 18))) {

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

		if ( !options || !options.ignoreDiagCountCheck ) {
			var diag_exists = false;

			evn_vizit_pl_store.each(function(record) {
				if ( record.get('Diag_id') > 0 ) {
					diag_exists = true;
				}
			});

			if ( !diag_exists ) {
				sw.swMsg.show({
					buttons: Ext.Msg.OK,
					fn: function() {
						this.formStatus = 'edit';
						this.findById('EPLEF_EvnVizitPLGrid').getView().focusRow(0);
						this.findById('EPLEF_EvnVizitPLGrid').getSelectionModel().selectFirstRow();
					}.createDelegate(this),
					icon: Ext.Msg.WARNING,
					msg: lang['sluchay_lecheniya_doljen_imet_hotya_byi_odin_osnovnoy_diagnoz'],
					title: ERR_INVFIELDS_TIT
				});
				return false;
			}
		}

		var diag_fid = base_form.findField('Diag_fid').getValue();
		var diag_did = base_form.findField('Diag_did').getValue();
		if (!Ext.isEmpty(diag_fid) && (!options || !options.ignoreDiagFCheck)) {
			var is_check = false;
			evn_vizit_pl_store.each(function(vizit) {
				if (vizit.get('Diag_id') == diag_fid) {
					is_check = true;
					//return false;
				}
			});
			if (is_check == false && diag_fid == diag_did) {
				is_check = true;
			}
			if (!is_check) {
				this.formStatus = 'edit';
				sw.swMsg.show({
					buttons: Ext.Msg.YESNO,
					fn: function(buttonId) {
						if (buttonId == 'yes') {
							options.ignoreDiagFCheck = true;
							this.doSave(options);
						}
					}.createDelegate(this),
					icon: Ext.Msg.QUESTION,
					msg: 'Предварительный диагноз не совпадает ни с одним из диагнозов, установленных в посещениях. Продолжить сохранение?',
					title: 'Вопрос'
				});
				return false;
			}
		}

		var diag_lid = base_form.findField('Diag_lid').getValue();
		if (!Ext.isEmpty(diag_fid) && (!options || !options.ignoreDiagLCheck) && getRegionNick() != 'kareliya') {
			var is_check = false;
			evn_vizit_pl_store.each(function(vizit) {
				if (vizit.get('Diag_id') == diag_lid) {
					is_check = true;
					//return false;
				}
			});
			if (base_form.findField('Diag_did') == diag_lid) {
				is_check = true;
			}
			if (!is_check) {
				this.formStatus = 'edit';
				sw.swMsg.show({
					buttons: Ext.Msg.YESNO,
					fn: function(buttonId) {
						if (buttonId == 'yes') {
							options.ignoreDiagLCheck = true;
							this.doSave(options);
						}/* else {
							return true;
						}*/
					}.createDelegate(this),
					icon: Ext.Msg.QUESTION,
					msg: 'Заключительный диагноз не совпадает ни с одним из диагнозов, установленных в посещениях. Продолжить сохранение?',
					title: 'Вопрос'
				});
				return false;
			}
		}

		if ( getGlobalOptions().region && getGlobalOptions().region.nick == 'ufa' ) {
			// https://redmine.swan.perm.ru/issues/15258
			// Проверяем, чтобы для посещения по заболеванию случай был незакончен, а для профилактического - закончен

			// https://redmine.swan.perm.ru/issues/17388
			// Для некоторых отделений допускается сохранение законченного случая с одним посещением по заболеванию

			var isProfVizit = false;
			var isSpecialCase = false;
			var morbusVizitCnt = 0;

			this.findById('EPLEF_EvnVizitPLGrid').getStore().each(function(rec) {
				if ( rec.get('LpuUnitSet_Code').toString().inlist([ '22112', '22105', '22119', '5058', '140', '114' ]) ) {
					isSpecialCase = true;
				}

				if ( rec.get('UslugaComplex_Code') && rec.get('UslugaComplex_Code').length == 6 ) {
					if ( isProphylaxisVizitOnly(rec.get('UslugaComplex_Code')) ) {
						isProfVizit = true;
					} else if ( isMorbusVizitOnly(rec.get('UslugaComplex_Code')) ) {
						morbusVizitCnt = morbusVizitCnt + 1;
					}

					// https://redmine.swan.perm.ru/issues/18168
					if ( /*!Ext.isEmpty(getGlobalOptions().lpu_id)
						&& getGlobalOptions().lpu_id.toString().inlist([ '77', '78', '79', '80', '85', '87', '88' ])
						&&*/ rec.get('UslugaComplex_Code').substr(rec.get('UslugaComplex_Code').length - 3, 3) == '871'
					) {
						isSpecialCase = true;
					}
				}
			});

			if ( isProfVizit == true && is_finish != 2 ) {
				this.formStatus = 'edit';
				sw.swMsg.alert(langs('Ошибка'), langs('Для профилактического/консультативного посещения должен быть указан признак окончания случая лечения и результат лечения'), function() {
					base_form.findField('EvnPL_IsFinish').focus(true);
				});
				return false;
			}
			else if ( morbusVizitCnt == 1 && is_finish == 2 && isSpecialCase == false ) {
				this.formStatus = 'edit';
				sw.swMsg.alert(lang['oshibka'], lang['sohranenie_zakryitogo_tap_po_zabolevaniyu_s_odnim_posescheniem_nevozmojno']);
				return false;
			}
		}

        if ( getRegionNick().inlist(['buryatiya','kareliya','astra']) ) {
			var consulDiagnVizitCnt = 0;
			var deseaseVizitCnt = 0;
			var otherVizitCnt = 0;
			var totalVizitCnt = 0;

            this.findById('EPLEF_EvnVizitPLGrid').getStore().each(function(rec) {
                if ( rec.get('VizitType_SysNick') ) {
                    totalVizitCnt++;
                    if ( rec.get('VizitType_SysNick') == 'desease' ) {
						deseaseVizitCnt++;
                    }
					else if ( rec.get('VizitType_SysNick') == 'ConsulDiagn' ) {
						consulDiagnVizitCnt++;
                    }
					else {
						otherVizitCnt++;
					}
                }
            });

			if ( totalVizitCnt > 0 && deseaseVizitCnt > 0 && otherVizitCnt > 0 && getRegionNick() != 'kareliya') {
				this.formStatus = 'edit';
				sw.swMsg.alert(lang['oshibka'], lang['v_tap_bolee_odnogo_posescheniya_i_prisutstvuyut_posescheniya_s_tselyu_otlichnoy_ot_obraschenie_po_povodu_zabolevaniya'], function() {
					base_form.findField('EvnPL_IsFinish').focus(true);
				});
				return false;
			}
			if ( otherVizitCnt > 1 && getRegionNick() != 'kareliya') {
				this.formStatus = 'edit';
				sw.swMsg.alert(langs('Ошибка'), langs('В ТАП более одного посещения с целью отличной от "Обращение по поводу заболевания"!'), function() {
					base_form.findField('EvnPL_IsFinish').focus(true);
				});
				return false;
			}
            if ( deseaseVizitCnt == 1 && is_finish == 2 && 'buryatiya' != getRegionNick()) {
                this.formStatus = 'edit';
                sw.swMsg.alert(lang['oshibka'], lang['sohranenie_zakryitogo_tap_po_zabolevaniyu_s_odnim_posescheniem_nevozmojno'], function() {
                    base_form.findField('EvnPL_IsFinish').focus(true);
                });
                return false;
            }
            /*if ( deseaseVizitCnt == 1 && is_finish == 2 && 'buryatiya' == getRegionNick() && '301' == base_form.findField('ResultClass_id').getFieldValue('ResultClass_Code')) {
                this.formStatus = 'edit';
                sw.swMsg.alert(lang['oshibka'], lang['esli_v_poseschenii_ukazana_tsel_zabolevanie_i_rezultat_obrascheniya_301_to_v_tap_doljno_byit_ne_menshe_dvuh_posescheniy'], function() {
                    base_form.findField('ResultClass_id').focus(true);
                });
                return false;
            }*/
			if ( otherVizitCnt == 1 && is_finish == 1 ) {
                this.formStatus = 'edit';
                sw.swMsg.alert(lang['oshibka'], lang['sohranenie_nezakryitogo_tap_nevozmojno'], function() {
                    base_form.findField('EvnPL_IsFinish').focus(true);
                });
                return false;
            }
        }

		if ( is_finish == 2 ) {
			if  ( ((getRegionNick() != 'kareliya' && Ext.isEmpty(evn_pl_ukl)) || evn_pl_ukl < 0 || evn_pl_ukl > 1 )&&getRegionNick() != 'ekb') {
				sw.swMsg.show({
					buttons: Ext.Msg.OK,
					fn: function() {
						this.formStatus = 'edit';
						base_form.findField('EvnPL_UKL').focus(false);
					}.createDelegate(this),
					icon: Ext.Msg.WARNING,
					msg: lang['proverte_pravilnost_zapolneniya_polya_ukl'] + (getRegionNick() != 'kareliya' ? lang['pri_zakonchennom_sluchae_ukl_doljno_byit_zapolneno'] : ''),
					title: ERR_INVFIELDS_TIT
				});
				return false;
			}

			if ( !options || !options.ignoreDiagCountCheck ) {
				if ( !result_class_id ) {
					sw.swMsg.show({
						buttons: Ext.Msg.OK,
						fn: function() {
							this.formStatus = 'edit';
							base_form.findField('ResultClass_id').markInvalid();
							base_form.findField('ResultClass_id').focus(false);
						}.createDelegate(this),
						icon: Ext.Msg.WARNING,
						msg: 'При законченном случае поле "'+ ( getRegionNick().inlist(['buryatiya','kareliya','ekb','krym']) ) ? 'Результат обращения' : 'Результат лечения' + '" должно быть заполнено',
						title: ERR_INVFIELDS_TIT
					});
					return false;
				}

				if ( !result_desease_type_id && getRegionNick().inlist([/*'astra',*/'adygeya', 'vologda', 'buryatiya','kareliya','krasnoyarsk','krym','ekb','penza','yakutiya','yaroslavl']) ) {
					sw.swMsg.show({
						buttons: Ext.Msg.OK,
						fn: function() {
							this.formStatus = 'edit';
							base_form.findField('ResultDeseaseType_id').markInvalid();
							base_form.findField('ResultDeseaseType_id').focus(false);
						}.createDelegate(this),
						icon: Ext.Msg.WARNING,
						msg: lang['pri_zakonchennom_sluchae_pole_ishod_doljno_byit_zapolneno'],
						title: ERR_INVFIELDS_TIT
					});
					return false;
				}
			}
		}

		if( 'ekb' == getRegionNick()&&pay_type_nick == 'oms'&&base_form.findField('Diag_did').getValue()) {
			var DiagFinance_IsOms = base_form.findField('Diag_did').getFieldValue('DiagFinance_IsOms');
			if(DiagFinance_IsOms == 0){
				var textMsg = lang['dannyiy_diagnoz_ne_podlejit_oplate_v_sisteme_oms_smenite_vid_oplatyi'];
				sw.swMsg.alert(lang['oshibka'], textMsg, function() {
					this.formStatus = 'edit';
					base_form.findField('Diag_did').markInvalid(textMsg);
					base_form.findField('Diag_did').focus(true);
				}.createDelegate(this));
				return false;
			}
		}

		try {
			if ( 'kareliya' == getRegionNick() && 'oms' == pay_type_nick && '313' == base_form.findField('ResultClass_id').getFieldValue('ResultClass_Code') ) {
				throw {warningMsg: lang['posescheniya_s_rezultatom_obrascheniya_␓_konstatatsiya_fakta_smerti_kod_-_313_oplate_za_schet_oms_ne_podlejat'], fieldName: 'ResultClass_id'};
			}
			else if ( 'perm' == getRegionNick() && true == omsPayTypeExists && '313' == base_form.findField('LeaveType_fedid').getFieldValue('LeaveType_Code') ) {
				throw {msg: lang['sluchai_s_ishodom_313_konstatatsiya_fakta_smerti_v_poliklinike_ne_podlejat_oplate_po_oms_dlya_sohraneniya_izmenite_vid_oplatyi'], fieldName: 'LeaveType_fedid'};
			}
		} catch(err) {
			if (err.warningMsg) {
				if (false == err.warningMsg.toString().inlist(options.ignoreErrors)) {
					sw.swMsg.show({
						buttons: Ext.Msg.YESNO,
						fn: function(buttonId, text, obj) {
							wnd.formStatus = 'edit';
							if ('yes' == buttonId) {
								options.ignoreErrors.push(err.warningMsg);
								wnd.doSave(options);
							} else if (err.fieldName && base_form.findField(err.fieldName)) {
								base_form.findField(err.fieldName).markInvalid(err.warningMsg);
								base_form.findField(err.fieldName).focus(true);
							}
						},
						icon: Ext.Msg.WARNING,
						msg: err.warningMsg + lang['prodoljit_sohranenie'],
						title: lang['preduprejdenie']
					});
					return false;
				}
			} else {
				wnd.formStatus = 'edit';
				sw.swMsg.alert(lang['oshibka'], err.msg || err.toString());
				return false;
			}
		}

		if (getRegionNick() == 'kz'){
			var need_mother_check = false;
			this.findById('EPLEF_EvnVizitPLGrid').getStore().each(function(rec) {
				if (!Ext.isEmpty(rec.get('ServiceType_SysNick')) && rec.get('ServiceType_SysNick').inlist(['home', 'ahome', 'neotl'])) {
					need_mother_check = true;
				}
			});
			var firstvizittype_sysnick = null;
			if(this.findById('EPLEF_EvnVizitPLGrid').getStore().getAt(0) && this.findById('EPLEF_EvnVizitPLGrid').getStore().getAt(0).get('VizitType_SysNick')) {
				firstvizittype_sysnick = this.findById('EPLEF_EvnVizitPLGrid').getStore().getAt(0).get('VizitType_SysNick')
			}
			if (is_finish == 2 && person_age < 1 && need_mother_check && firstvizittype_sysnick == 'prof' && !wnd.findById('EPLEF_PersonInformationFrame').getFieldValue('DeputyPerson_id') && !wnd.ignoreMotherCheck) {
				sw.swMsg.show({
					buttons: Ext.Msg.OKCANCEL,
					fn: function(buttonId, text, obj) {
						wnd.formStatus = 'edit';
						wnd.ignoreMotherCheck = true; // проверяем только один раз вне зависимости от результата
						if ('ok' == buttonId) {
							ShowWindow('swPersonEditWindow', {
								Person_id: base_form.findField('Person_id').getValue(),
								addMother: true
							});
						} else {
							wnd.doSave(options);
						}
					},
					icon: Ext.Msg.WARNING,
					msg: 'Проверьте информацию о матери для пациента младше 1 года. Для корректной передачи данных в сервис должны быть заполнены поля «Представитель», «Статус представителя» на вкладке «2. Дополнительно» формы  «Человек» если пациент гражданин Казахстана и не является сиротой.',
					title: langs('Предупреждение')
				});
				return false;
			}
		}

		if (getRegionNick() == 'vologda' && is_finish == 2){
			var grid = this.findById('EPLEF_EvnVizitPLGrid');

			var controlDate = new Date(2019, 7, 1);
			var evnPL_disDate = base_form.findField('EvnPL_disDate').getValue();
			evnPL_disDate = Date.parseDate(evnPL_disDate, 'd.m.Y');
			if(evnPL_disDate >= controlDate) {
				var gridStore = grid.getStore();
				var recFirst = gridStore.getAt(0);
				var flagProfile = false;
				if(grid.getStore().getCount() > 1 && recFirst.get('LpuSectionProfile_id')){
					var firstLpuSectionProfile_id = recFirst.get('LpuSectionProfile_id');

					var arrNotControlProfileCode = [];
					var arrControlProfileCode = [];
					var arrVizitsProfileCode = [];
					grid.getStore().each(function(rec) {
						if(arrVizitsProfileCode.indexOf(rec.get('LpuSectionProfile_Code'))<0) arrVizitsProfileCode.push(rec.get('LpuSectionProfile_Code'));
						if(!rec.get('LpuSectionProfile_Code').inlist(getGlobalOptions().exceptionprofiles)) {
							flagProfile = true;
							if(arrNotControlProfileCode.indexOf(rec.get('LpuSectionProfile_Code'))<0) arrNotControlProfileCode.push(rec.get('LpuSectionProfile_Code'));
						}else{
							arrControlProfileCode.push(rec.get('LpuSectionProfile_Code'));
						}
					});
					if(arrVizitsProfileCode.length == 1) flagProfile = false;
					if(flagProfile && arrControlProfileCode.length > 0 && arrNotControlProfileCode.length == 1){
						// есть одно или более посещений, в которых указаны профили «97», «57», «58», «42», «68», «3», «136»
						// И в остальных посещениях указан одинаковый профиль отделения, отличный от профилей «97», «57», «58», «42», «68», «3», «136»
						flagProfile = false;
					}
				}
				if(flagProfile){
					sw.swMsg.alert(langs('Сообщение'), langs('Закрытие случая АПЛ невозможно, т.к. в рамках одного ТАП для всех посещений должен быть указан один профиль отделения.'));
					wnd.formStatus = 'edit';
					return false;
				}
			}
		}

		var result_desease_type_code=base_form.findField('ResultDeseaseType_id').getFieldValue('ResultDeseaseType_Code'),
			result_class_code=base_form.findField('ResultClass_id').getFieldValue('ResultClass_Code'),
			diag_lid_code = base_form.findField('Diag_lid').getFieldValue('Diag_Code'),
			result_class_code_check="";
		
		if ( !getRegionNick().inlist(['astra','ufa','kz','msk','perm','khak']) && !Ext.isEmpty(result_class_id) && !Ext.isEmpty(result_desease_type_id)) {

			switch (getRegionNick()) {
				case 'ekb':
					result_class_code_check=[301, 302, 303, 304, 305, 306, 307, 308, 309, 310, 311];
					break;
				case 'adygeya':
					result_class_code_check=[302, 303, 304, 306, 307, 309, 310, 311, 312, 313];
					break;
				default:
					result_class_code_check=[301, 302, 303, 304, 306, 307, 309, 310, 311, 312, 313];
					break;
			}

			if(
				(
					result_class_code.toString().inlist(result_class_code_check)
					&& result_desease_type_code==306
				)
				||
				(result_class_code==304 && result_desease_type_code==301)
				||
				(
					result_class_code==313
					&& result_desease_type_code.toString().inlist([301, 302, 303, 304, 306])
				)
			) {
				this.formStatus = 'edit';
				if (getRegionNick().inlist(['vologda', 'adygeya'])) {
					sw.swMsg.alert(langs('Ошибка'), langs('Выбранный исход не соответствует результату лечения. Укажите корректный исход'));
				}else {
					sw.swMsg.alert(langs('Ошибка'), langs('Выбранный исход не соответствует результату обращения. Укажите корректный исход'));
				}
				return false;
			}
		}

		if (getRegionNick() == 'buryatiya' && !Ext.isEmpty(result_class_id) && !Ext.isEmpty(diag_lid)){
			if(
				result_class_code==313
				&& diag_lid_code.toString().substr(0, 1).inlist(['Z'])
			){
				sw.swMsg.alert(langs('Ошибка'), langs('При диагнозе Z нельзя указать результат обращения "Констатация факта смерти". Укажите корректный диагноз.'));
				return false;
			}
		}
		
		var params = {};

		if (this.action == 'add') {
			params.action = 'addEvnPL';
		} else if (this.action == 'edit') {
			params.action = 'editEvnPL';
		}

		params = this.panelEvnDirectionAll.onBeforeSubmit(this, params);
		if (!params) {
			return false;
		}
		params.Diag_id=this.DiagPreg;

		if ( getRegionNick() == 'astra' ) {
			params.LeaveType_fedid = base_form.findField('ResultClass_id').getFieldValue('LeaveType_fedid');
		}
		else if ( base_form.findField('LeaveType_fedid').disabled ) {
			params.LeaveType_fedid = base_form.findField('LeaveType_fedid').getValue();
		}

		/*if ( getRegionNick() == 'astra' ) {
			params.ResultDeseaseType_fedid = base_form.findField('ResultDeseaseType_id').getFieldValue('ResultDeseaseType_fedid');
		}
		else*/ if ( base_form.findField('ResultDeseaseType_fedid').disabled ) {
			params.ResultDeseaseType_fedid = base_form.findField('ResultDeseaseType_fedid').getValue();
		}

		if ( base_form.findField('Diag_fid').disabled ) {
			params.Diag_fid = base_form.findField('Diag_fid').getValue();
		}

		if ( base_form.findField('Diag_lid').disabled ) {
			params.Diag_lid = base_form.findField('Diag_lid').getValue();
		}
		if ( options.ignoreEvnDirectionProfile ) {
			params.ignoreEvnDirectionProfile = 1;
		}
		if ( options.ignoreMorbusOnkoDrugCheck ) {
			params.ignoreMorbusOnkoDrugCheck = 1;
		}
		if ( options.ignoreKareliyaKKND ) {
			params.ignoreKareliyaKKND = 1;
		}

        params.isAutoCreate = (options && typeof options.openChildWindow == 'function' && this.action == 'add') ? 1 : 0;
		params.ignoreParentEvnDateCheck = (!Ext.isEmpty(options.ignoreParentEvnDateCheck) && options.ignoreParentEvnDateCheck === 1) ? 1 : 0;
		params.ignoreMesUslugaCheck = (!Ext.isEmpty(options.ignoreMesUslugaCheck) && options.ignoreMesUslugaCheck === 1) ? 1 : 0;
		params.ignoreFirstDisableCheck = (!Ext.isEmpty(options.ignoreFirstDisableCheck) && options.ignoreFirstDisableCheck === 1) ? 1 : 0;
		params.ignoreCheckEvnUslugaChange = (!Ext.isEmpty(options.ignoreCheckEvnUslugaChange) && options.ignoreCheckEvnUslugaChange === 1) ? 1 : 0;
		params.ignoreCheckB04069333 = (!Ext.isEmpty(options.ignoreCheckB04069333) && options.ignoreCheckB04069333 === 1) ? 1 : 0;
		params.ignoreCheckTNM = (!Ext.isEmpty(options.ignoreCheckTNM) && options.ignoreCheckTNM === 1) ? 1 : 0;
		params.ignoreDiagDispCheck = (!Ext.isEmpty(options.ignoreDiagDispCheck) && options.ignoreDiagDispCheck === 1) ? 1 : 0;
		params.vizit_intersection_control_check = (options && !Ext.isEmpty(options.vizit_intersection_control_check) && options.vizit_intersection_control_check === 1) ? 1 : 0;
        params.streamInput = (this.streamInput === true ? 1 : 0);
		//params.ignoreNoExecPrescr = (options && options.ignoreNoExecPrescr) ? options.ignoreNoExecPrescr : null;
		params.ignoreNoExecPrescr = 1;
		
		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение талона..."});
		loadMask.show();

		base_form.submit({
			clientValidation: !options.ignoreFormValidation,
			failure: function(result_form, action) {
				this.formStatus = 'edit';
				loadMask.hide();

				if ( action.result ) {
					if (action.result.Alert_Msg) {
						var msg = action.result.Alert_Msg;

						if (action.result.Error_Code == 112 && action.result.addMsg) {
							var headMsg = lang['informatsiya_o_peresecheniyah'];
							var addMsg = escapeHtml(action.result.addMsg);
							msg += '<br/> <a onclick="Ext.Msg.alert(\' ' + headMsg +  ' \',\' ' + addMsg +  ' \');" href=\'#\' >Подробнее</a>';
						}

						if ( action.result.Error_Code == 212) {
							sw.swMsg.show({
								buttons: Ext.Msg.OK,
								fn: function(buttonId, text, obj) {
									if(buttonId == 'ok') {
										base_form.findField('Person_id');
										getWnd('swMorbusOnkoWindow').show({
											action: 'edit',
											MorbusOnko_pid: action.result.EvnVizitPL_id,
											EvnVizitPL_id: action.result.EvnVizitPL_id,
											Person_id: base_form.findField('Person_id').getValue(),
											PersonEvn_id: base_form.findField('PersonEvn_id').getValue(),
											Server_id: base_form.findField('Server_id').getValue(),
											allowSpecificEdit: true
										});
									}
								},
								msg: msg,
								icon: Ext.Msg.WARNING,
								title: ERR_WND_TIT
							});
						} else {
							sw.swMsg.show({
								buttons: Ext.Msg.YESNO,
								fn: function(buttonId, text, obj) {
									if ( buttonId == 'yes' ) {
										switch (true) {
											case (197641 == action.result.Error_Code):
												options.ignoreNoExecPrescr = 1;
												break;
											case (104 == action.result.Error_Code):
												options.ignoreEvnDirectionProfile = 1;
												break;
											case (106 == action.result.Error_Code):
												options.ignoreMorbusOnkoDrugCheck = 1;
												break;
											case (109 == action.result.Error_Code):
												options.ignoreParentEvnDateCheck = 1;
												break;
											case (110 == action.result.Error_Code):
												options.ignoreKareliyaKKND = 1;
												break;
											case (112 == action.result.Error_Code):
												options.vizit_intersection_control_check = 1;
												break;
											case (114 == action.result.Error_Code):
												options.ignoreMesUslugaCheck = 1;
												break;
											case (115 == action.result.Error_Code):
												options.ignoreFirstDisableCheck = 1;
												break;
											case (130 == action.result.Error_Code):
												options.ignoreCheckEvnUslugaChange = 1;
												break;
											case (131 == action.result.Error_Code):
												options.ignoreCheckB04069333 = 1;
												break;
											case (181 == action.result.Error_Code):
												options.ignoreCheckTNM = 1;
												break;
											case (182 == action.result.Error_Code):
												options.ignoreDiagDispCheck = 1;
												var formParams = new Object();
												var params_disp = new Object();

												formParams.Person_id = base_form.findField('Person_id').getValue();
												formParams.Server_id = base_form.findField('Server_id').getValue();
												formParams.PersonDisp_begDate = getGlobalOptions().date;
												formParams.PersonDisp_DiagDate = getGlobalOptions().date;
												formParams.Diag_id = base_form.findField('Diag_lid').getValue();

												params_disp.action = 'add';
												params_disp.callback = Ext.emptyFn;
												params_disp.formParams = formParams;
												params_disp.onHide = Ext.emptyFn;

												getWnd('swPersonDispEditWindow').show(params_disp);
												break;
										}

										this.doSave(options);
									}
									else {
										switch (true) {
											case (197641 == action.result.Error_Code):
												base_form.findField('EvnPL_IsFinish').setValue(1);
												break;
											case (182 == action.result.Error_Code):
												options.ignoreDiagDispCheck = 1;
												this.doSave(options);
												break;
										}
									}
								}.createDelegate(this),
								icon: Ext.MessageBox.QUESTION,
								msg: msg,
								title: langs('Продолжить сохранение?')
							});
						}

					} else if ( action.result.Error_Msg ) {
						var msg = action.result.Error_Msg;



						if (action.result.Error_Code == 112 && action.result.addMsg) {
							var headMsg = lang['informatsiya_o_peresecheniyah'];
							var addMsg = escapeHtml(action.result.addMsg);
							msg += '<br/> <a onclick="Ext.Msg.alert(\' ' + headMsg +  ' \',\' ' + addMsg +  ' \');" href=\'#\' >Подробнее</a>';
						}


							sw.swMsg.alert(langs('Ошибка'), msg);
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
					if ( action.result.EvnPL_id ) {
						var evn_pl_id = action.result.EvnPL_id;

						base_form.findField('EvnPL_id').setValue(evn_pl_id);

						checkSuicideRegistry({
							'Evn_id': evn_pl_id,
							'EvnClass_SysNick': 'EvnPL'
						});

						if ( options && typeof options.openChildWindow == 'function' && this.action == 'add' ) {
							options.openChildWindow();
						}
						else {
							var setDate, disDate;
							var person_information = this.findById('EPLEF_PersonInformationFrame');
							var response = {};
							var diag_code = '', diag_name = '', lpusection_name = '', medpersonal_fio = '';

							evn_vizit_pl_store.each(function(record) {
								if ( typeof setDate != 'object' || record.get('EvnVizitPL_setDate') <= setDate ) {
									setDate = record.get('EvnVizitPL_setDate');
								}

								if ( typeof disDate != 'object' || record.get('EvnVizitPL_setDate') >= disDate ) {
									disDate = record.get('EvnVizitPL_setDate');
									lpusection_name = record.get('LpuSection_Name');
									medpersonal_fio = record.get('MedPersonal_Fio');

									if ( !Ext.isEmpty(record.get('Diag_Code')) ) {
										diag_code = record.get('Diag_Code');
										diag_name = record.get('Diag_Name');
									}
								}
							});

							response.accessType = 'edit';
							response.Diag_Name = (!Ext.isEmpty(diag_code) ? diag_code + '. ' + diag_name : '');
							response.EvnPL_disDate = disDate;
							response.EvnPL_id = evn_pl_id;
							response.EvnPL_IsFinish = base_form.findField('EvnPL_IsFinish').getStore().getById(is_finish).get('YesNo_Name');
							response.EvnPL_NumCard = base_form.findField('EvnPL_NumCard').getValue();
							response.EvnPL_setDate = setDate;
							response.EvnPL_VizitCount = (!Ext.isEmpty(evn_vizit_pl_store.getAt(0).get('EvnVizitPL_id')) ? evn_vizit_pl_store.getCount() : 0);
							response.LpuSection_Name = lpusection_name;
							response.MedPersonal_Fio = medpersonal_fio;
							response.Person_Birthday = person_information.getFieldValue('Person_Birthday');
							response.Person_Firname = person_information.getFieldValue('Person_Firname');
							response.Person_id = base_form.findField('Person_id').getValue();
							response.Person_Secname = person_information.getFieldValue('Person_Secname');
							response.Person_Surname = person_information.getFieldValue('Person_Surname');
							response.PersonEvn_id = base_form.findField('PersonEvn_id').getValue();
							response.Server_id = base_form.findField('Server_id').getValue();
							if (base_form.findField('EvnCostPrint_IsNoPrint').getValue() == 2) {
								response.EvnCostPrint_IsNoPrintText = lang['otkaz_ot_spravki'];
							} else if (base_form.findField('EvnCostPrint_IsNoPrint').getValue() == 1) {
								response.EvnCostPrint_IsNoPrintText = lang['spravka_vyidana'];
							} else {
								response.EvnCostPrint_IsNoPrintText = '';
							}
							response.EvnCostPrint_setDT = base_form.findField('EvnCostPrint_setDT').getValue();

							this.callback({evnPLData: response});

							if ( options && options.print == true ) {

								printEvnPL({
									type: 'EvnPL',
									EvnPL_id: evn_pl_id
								});

								this.action = 'edit';
								this.setTitle(WND_POL_EPLEDIT);

							}
							else {
								// this.createEvnDirection(); // что то не понятное по рефакторингу направлений, на рабочей пока точно не нужно
								if ( action.result.Alert_Msg && action.result.Alert_Msg.toString().length > 0 ) {
									sw.swMsg.alert(lang['preduprejdenie'], action.result.Alert_Msg, function() {
										this.hide();
									}.createDelegate(this));
								}
								else {
									this.hide(); // а вот закрыться всё равно нужно :)
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
	draggable: true,
	// тест, переход на универсальную функцию из BaseForm.
	/*enableEdit: function(enable) {
		var base_form = this.findById('EvnPLEditForm').getForm();
		var form_fields = new Array(
			'Diag_did',
			'DirectClass_id',
			'DirectType_id',
			'EvnPL_Complexity',
			'EvnPL_IsFirstTime',
			'EvnPL_NumCard',
			'EvnPL_UKL',
			'EvnPL_IsFinish',
			'EvnPL_IsUnlaw',
			'EvnPL_IsUnport',
			'PrehospDirect_id',
			'EvnPL_IsWithoutDirection',
			'PrehospTrauma_id',
			'ResultClass_id',
			'ResultDeseaseType_id'
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
	},*/

	createEvnDirection:function(){
		var win = this;
		var base_form = win.findById('EvnPLEditForm').getForm();
		var DirectType_id = base_form.findField('DirectType_id').getValue();
		var DirectClass_id = base_form.findField('DirectClass_id').getValue();
		var LpuSection_did = base_form.findField('LpuSection_oid').getValue();
		var Lpu_did = base_form.findField('Lpu_oid').getValue();
		var Person_id = base_form.findField('Person_id').getValue();
		var PersonEvn_id =base_form.findField('PersonEvn_id').getValue();
		var Server_id =base_form.findField('Server_id').getValue();
		var params={};
		if(!DirectType_id&&!DirectClass_id&&!LpuSection_did&&!Lpu_did){
			win.hide();
		}else{
			/*params = {
						Person_FirName:result[0].Person_FirName,
						Person_SecName:result[0].Person_SecName,
						Person_SurName:result[0].Person_SurName,
						Person_id:result[0].Person_id,
						PersonEvn_id:result[0].PersonEvn_id,
						Server_id:result[0].Server_id,
						userMedStaffFact: userMedStaffFact,
						directionData: result[0],
						personData:{
							Person_FirName:result[0].Person_FirName,
							Person_SecName:result[0].Person_SecName,
							Person_SurName:result[0].Person_SurName,
							Person_id:result[0].Person_id,
							PersonEvn_id:result[0].PersonEvn_id,
							Server_id:result[0].Server_id
						}
					}*/
			params={
				action:'add',
				type:'recwp',
				Person_id:Person_id,
				onHide:function(){win.hide()},
				formParams:{
					DirectType_id:DirectType_id,
					DirectClass_id:DirectClass_id,
					LpuSection_did:LpuSection_did,
					Lpu_did:Lpu_did,
					Server_id:Server_id,
					PersonEvn_id:PersonEvn_id,
					Person_id:Person_id,
					MedStaffFact_id:getGlobalOptions().CurMedStaffFact_id,
					Diag_id:win.params.Diag_id
				}
			};
			if(DirectClass_id==1){
				params.formParams.Lpu_did=getGlobalOptions().lpu_id;
			}
			getWnd('swEvnDirectionEditWindow').show(params);
		}

	},
	checkTrauma: function() {

        var base_form = this.findById('EvnPLEditForm').getForm();
        var grid = this.findById('EPLEF_EvnVizitPLGrid');
		var traumaField = base_form.findField('PrehospTrauma_id');
		var is_finish = base_form.findField('EvnPL_IsFinish').getValue();
		var checkTr = true;
        if ( is_finish==2 && grid.getStore().getCount() > 0 && grid.getStore().getAt(0).get('EvnPL_id') ) {
            grid.getStore().each(function(rec) {
			var diagGroup = rec.get('Diag_Code')[0];
			if(diagGroup=="S"||diagGroup=="T"){
				checkTr = false;
				return false;
			}
            });
        }
		traumaField.setAllowBlank(checkTr);
		return checkTr;
    },



	filterResultClassCombo: function() {
		var base_form = this.findById('EvnPLEditForm').getForm();

		// фильтрация комбо ResultClass в зависимости от даты последнего посещения, либо текущей даты, если нет посещений.
		var lastEvnVizitPLDate;
		var ResultClass_id = base_form.findField('ResultClass_id').getValue();

		base_form.findField('ResultClass_id').clearValue();
		base_form.findField('ResultClass_id').getStore().clearFilter();

		this.findById('EPLEF_EvnVizitPLGrid').getStore().each(function(record) {
			if ( Ext.isEmpty(lastEvnVizitPLDate) || (!Ext.isEmpty(record.get('EvnVizitPL_setDate')) && record.get('EvnVizitPL_setDate') >= lastEvnVizitPLDate) ) {
				lastEvnVizitPLDate = record.get('EvnVizitPL_setDate');
			}
		});

		if ( Ext.isEmpty(lastEvnVizitPLDate) ) {
			lastEvnVizitPLDate = new Date();
		}

		var xdate = new Date(2016, 0, 1);

		if ( getRegionNick() == 'astra' ) {
			base_form.findField('ResultClass_id').getStore().filterBy(function(rec) {
				return (
					(Ext.isEmpty(rec.get('ResultClass_begDT')) || rec.get('ResultClass_begDT') <= lastEvnVizitPLDate)
					&& (Ext.isEmpty(rec.get('ResultClass_endDT')) || rec.get('ResultClass_endDT') >= lastEvnVizitPLDate)
					&& (Ext.isEmpty(rec.get('ResultClass_Code')) || rec.get('ResultClass_Code').inlist(['1','2','3','4','5']))
				);
			});
		}
		else {
			base_form.findField('ResultClass_id').getStore().filterBy(function(rec) {
				return (
					(Ext.isEmpty(rec.get('ResultClass_begDT')) || rec.get('ResultClass_begDT') <= lastEvnVizitPLDate)
					&& (Ext.isEmpty(rec.get('ResultClass_endDT')) || rec.get('ResultClass_endDT') >= lastEvnVizitPLDate)
					&& (!rec.get('ResultClass_Code') || !rec.get('ResultClass_Code').inlist(['6','7']) || getRegionNick() != 'perm' || lastEvnVizitPLDate < xdate)
				);
			});
		}

		if ( !Ext.isEmpty(ResultClass_id) ) {
			index = base_form.findField('ResultClass_id').getStore().findBy(function(rec) {
				return (rec.get('ResultClass_id') == ResultClass_id);
			});

			if ( index >= 0 ) {
				base_form.findField('ResultClass_id').setValue(ResultClass_id);
			}
		}
	},
	setMedicalStatusComboVisible: function() {
		if (getRegionNick() != 'kz') {
			return false;
		}

		var base_form = this.findById('EvnPLEditForm').getForm();
		this.findById('EPLEF_EvnVizitPLGrid').getStore().each(function(rec) {
			base_form.findField('MedicalStatus_id').hideContainer();
			if ( rec.get('VizitType_SysNick') && rec.get('VizitType_SysNick') == 'disp' ) {
				base_form.findField('MedicalStatus_id').showContainer();
			}
		});
	},
	setDiagConcComboVisible: function() {
		var base_form = this.findById('EvnPLEditForm').getForm();

		// фильтрация комбо ResultClass в зависимости от даты последнего посещения, либо текущей даты, если нет посещений.
		var lastEvnVizitPLDate, lastDiagCode;
		var Diag_lid_Code = base_form.findField('Diag_lid').getFieldValue('Diag_Code');
		var xdate = new Date(2016, 0, 1); // Поле обязательно если дата посещения 01-01-2016 или позже

		this.findById('EPLEF_EvnVizitPLGrid').getStore().each(function(record) {
			if ( Ext.isEmpty(lastEvnVizitPLDate) || (!Ext.isEmpty(record.get('EvnVizitPL_setDate')) && record.get('EvnVizitPL_setDate') >= lastEvnVizitPLDate) ) {
				lastDiagCode = record.get('Diag_Code');
				lastEvnVizitPLDate = record.get('EvnVizitPL_setDate');
			}
		});

		base_form.findField('Diag_concid').setAllowBlank(true);
		if (getRegionNick() == 'kareliya') {
			if ( !Ext.isEmpty(lastDiagCode) && lastDiagCode.toString().substr(0, 1).inlist([ 'S', 'T' ]) ) {
				base_form.findField('Diag_concid').setContainerVisible(true);
				if (lastEvnVizitPLDate >= xdate) {
					base_form.findField('Diag_concid').setAllowBlank(false);
				}
			}
			else {
				base_form.findField('Diag_concid').clearValue();
				base_form.findField('Diag_concid').setContainerVisible(false);
			}
		}
		else {
			if ( !Ext.isEmpty(Diag_lid_Code) && Diag_lid_Code.toString().substr(0, 1).inlist([ 'S', 'T' ]) ) {
				base_form.findField('Diag_concid').setContainerVisible(true);
				if (lastEvnVizitPLDate >= xdate) {
					base_form.findField('Diag_concid').setAllowBlank(false);
				}
			}
			else {
				base_form.findField('Diag_concid').clearValue();
				base_form.findField('Diag_concid').setContainerVisible(false);
			}
		}

	},
	setInterruptLeaveTypeVisible: function() {
		var base_form = this.findById('EvnPLEditForm').getForm();

		var lastEvnVizitPLDate;

		this.findById('EPLEF_EvnVizitPLGrid').getStore().each(function(record) {
			if ( Ext.isEmpty(lastEvnVizitPLDate) || (!Ext.isEmpty(record.get('EvnVizitPL_setDate')) && record.get('EvnVizitPL_setDate') >= lastEvnVizitPLDate) ) {
				lastEvnVizitPLDate = record.get('EvnVizitPL_setDate');
			}
		});

		var xdate = new Date(2016, 0, 1); // Поле видимо (если дата посещения 01-01-2016 или позже)
		if ( !Ext.isEmpty(lastEvnVizitPLDate) && lastEvnVizitPLDate >= xdate) {
			base_form.findField('InterruptLeaveType_id').showContainer();
		} else {
			base_form.findField('InterruptLeaveType_id').hideContainer();
			base_form.findField('InterruptLeaveType_id').clearValue();
		}

		var xdate = new Date(2016, 10, 1); // Поле видимо (если дата посещения 01-11-2016 или позже)
		if ( Ext.isEmpty(lastEvnVizitPLDate) || lastEvnVizitPLDate >= xdate) {
			base_form.findField('PrivilegeType_id').showContainer();
			base_form.findField('EvnPL_IsFirstDisable').hideContainer();
			base_form.findField('EvnPL_IsFirstDisable').clearValue();
		} else {
			base_form.findField('EvnPL_IsFirstDisable').showContainer();
			base_form.findField('PrivilegeType_id').hideContainer();
			base_form.findField('PrivilegeType_id').clearValue();
		}
	},
	firstRun: true,
	formStatus: 'edit',
	getEvnPLNumber: function() {
		if ( this.action == 'view' ) {
			return false;
		}
        var that = this;
		var evnpl_num_field = this.findById('EvnPLEditForm').getForm().findField('EvnPL_NumCard');
		var grid = that.findById('EPLEF_EvnVizitPLGrid');

		var params = {};

		if ( grid.getStore().getCount() > 0 ) {
			grid.getStore().each(function(rec) {
				if (
					!Ext.isEmpty(rec.get('EvnVizitPL_id')) && typeof rec.get('EvnVizitPL_setDate') == 'object'
					&& (Ext.isEmpty(params.year) || params.year > rec.get('EvnVizitPL_setDate').format('Y'))
				) {
					params.year = rec.get('EvnVizitPL_setDate').format('Y');
				}
			});
		}

		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Получение номера талона..."});
		loadMask.show();

		Ext.Ajax.request({
			callback: function(options, success, response) {
				loadMask.hide();

				if ( success ) {
					var response_obj = Ext.util.JSON.decode(response.responseText);
					evnpl_num_field.setValue(response_obj.EvnPL_NumCard);
					evnpl_num_field.focus(true);

                    grid.getSelectionModel().selectFirstRow();
                    grid.getView().focusRow(0);
				}
				else {
					sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_opredelenii_nomera_talona']);
				}
			},
			params: params,
			url: '/?c=EvnPL&m=getEvnPLNumber'
		});
	},
	height: 550,
	id: 'EvnPLEditWindow',
	initComponent: function() {
		var win = this;

		this.panelEvnDirectionAll = new sw.Promed.EvnDirectionAllPanel({
			prefix: 'EPLEF',
			startTabIndex: TABINDEX_EPLEF + 8,
			checkOtherLpuDirection: function() {
				var base_form = win.findById('EvnPLEditForm').getForm();

				if (getRegionNick() == 'perm') {
					var org_id = base_form.findField('Org_did').getValue();
					var date = Ext.util.Format.date(base_form.findField('EvnDirection_setDate').getValue(), 'd.m.Y');

					if (base_form.findField('PrehospDirect_id').getFieldValue('PrehospDirect_Code') == 2) {
						if (Ext.isEmpty(org_id)) {
							base_form.findField('Diag_did').setAllowBlank(true);
							if (getRegionNick() != 'ekb') {
								base_form.findField('EvnDirection_Num').setAllowBlank(true);
								base_form.findField('EvnDirection_setDate').setAllowBlank(true);
							}
						} else {
							win.checkLpuPeriodOMS(org_id, date, function (hasLpuPeriodOMS) {
								base_form.findField('Diag_did').setAllowBlank(!hasLpuPeriodOMS);
								base_form.findField('EvnDirection_Num').setAllowBlank(!hasLpuPeriodOMS);
								base_form.findField('EvnDirection_setDate').setAllowBlank(!hasLpuPeriodOMS);
							});
						}
					}
				}
			},
			useCase: 'choose_for_evnpl',
			showMedStaffFactCombo: true,
			personPanelId: 'EPLEF_PersonInformationFrame',
			personFieldName: 'Person_id',
			medStaffFactFieldName: null,
			fromLpuFieldName: 'Lpu_fid',
			fieldIsWithDirectionName: 'EvnPL_IsWithoutDirection',
			buttonSelectId: 'EPLEF_EvnDirectionSelectButton',
			fieldPrehospDirectName: 'PrehospDirect_id',
			fieldLpuSectionName: 'LpuSection_did',
			fieldMedStaffFactName: 'MedStaffFact_did',
			fieldOrgName: 'Org_did',
			fieldDoctorCode: 'EvnPL_MedPersonalCode',
			fieldNumName: 'EvnDirection_Num',
			fieldSetDateName: 'EvnDirection_setDate',
			fieldDiagName: 'Diag_did',
			fieldDiagFName: 'Diag_fid',
			fieldDiagPredName: 'Diag_preid',
			//fieldTimaTableName: 'TimetableGraf_id',
			//fieldEvnPrescrName: 'EvnPrescr_id',
			fieldIdName: 'EvnDirection_id',
			fieldIsAutoName: 'EvnDirection_IsAuto',
			fieldIsExtName: 'EvnDirection_IsReceive',
			parentSetDateFieldName: 'EvnPL_setDate',
			nextFieldName: 'EvnPL_IsFinish',
			openEvnDirectionSelectWindow: function()
			{
				if ( this.isDisabledChooseDirection ) {
					return false;
				}
				var me = this,
					base_form = me.getBaseForm(),
					person_info = Ext.getCmp(me.personPanelId);
				// По кнопке “Выбор направления” всегда вызывать форму выбора со скрытым нижним гридом “Записи”
				if ( getWnd('swEvnDirectionSelectWindow').isVisible() ) {
					getWnd('swEvnDirectionSelectWindow').hide();
				}
				getWnd('swEvnDirectionSelectWindow').show({
					callback: function(evnDirectionData) {
						if (evnDirectionData && evnDirectionData.EvnDirection_id){
							// создавать случай со связью с направлением
							me._applyEvnDirectionData(evnDirectionData, true);
						} else {
							// создать случай без связи с направлением
							me._applyEvnDirectionData(null);
						}
					},
					onDate: me.parentSetDateFieldName ? base_form.findField(me.parentSetDateFieldName).getValue() : getGlobalOptions().date,
					onHide: function() {
						if ( !Ext.getCmp('EPLEF_EvnVizitPLPanel').collapsed ) {
                            Ext.getCmp('EPLEF_EvnVizitPLGrid').getView().focusRow(0);
                            Ext.getCmp('EPLEF_EvnVizitPLGrid').getSelectionModel().selectFirstRow();
                        } else {
							base_form.findField(me.nextFieldName).focus(true);
						}
					},
					useCase: me.useCase,
					MedStaffFact_id: me.medStaffFactFieldName ? base_form.findField(me.medStaffFactFieldName).getValue() : getGlobalOptions().CurMedStaffFact_id,
					Person_Birthday: person_info.getFieldValue('Person_Birthday'),
					Person_Firname: person_info.getFieldValue('Person_Firname'),
					Person_id: base_form.findField(me.personFieldName).getValue(),
					Person_Secname: person_info.getFieldValue('Person_Secname'),
					Person_Surname: person_info.getFieldValue('Person_Surname')
				});
				return true;
			}
		});

		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.doSave({
						ignoreDiagCountCheck: false,
						ignoreEvnVizitPLCountCheck: false,
						print: false
					});
				}.createDelegate(this),
				iconCls: 'save16',
				onShiftTabAction: function () {
					var base_form = this.findById('EvnPLEditForm').getForm();

					/*if ( !this.findById('EPLEF_DirectPanel').collapsed && this.action != 'view' ) {
						if ( !base_form.findField('Lpu_oid').disabled ) {
							base_form.findField('Lpu_oid').focus(true);
						}
						else if ( !base_form.findField('LpuSection_oid').disabled ) {
							base_form.findField('LpuSection_oid').focus(true);
						}
						else {
							base_form.findField('DirectClass_id').focus(true);
						}
					}
					else*/ if ( !this.findById('EPLEF_ResultPanel').collapsed && this.action != 'view' ) {
						if(base_form.findField('ResultDeseaseType_fedid').isVisible()){
							base_form.findField('ResultDeseaseType_fedid').focus();
						} else {
							base_form.findField('EvnPL_UKL').focus(true);
						}
					}
					else if ( !this.findById('EPLEF_EvnStickPanel').collapsed ) {
						this.findById('EPLEF_EvnStickGrid').getView().focusRow(0);
						this.findById('EPLEF_EvnStickGrid').getSelectionModel().selectFirstRow();
					}
					else if ( !this.findById('EPLEF_EvnUslugaPanel').collapsed ) {
						this.findById('EPLEF_EvnUslugaGrid').getView().focusRow(0);
						this.findById('EPLEF_EvnUslugaGrid').getSelectionModel().selectFirstRow();
					}
					else if ( !this.findById('EPLEF_EvnVizitPLPanel').collapsed ) {
						this.findById('EPLEF_EvnVizitPLGrid').getView().focusRow(0);
						this.findById('EPLEF_EvnVizitPLGrid').getSelectionModel().selectFirstRow();
					}
					else if ( !this.findById('EPLEF_DirectInfoPanel').collapsed && this.action != 'view' ) {
						base_form.findField('EvnPL_IsUnport').focus(true);
					}
					else if ( this.action == 'view' ) {
						this.buttons[this.buttons.length - 1].focus();
					}
					else {
						base_form.findField('EvnPL_Complexity').focus(true);
					}
				}.createDelegate(this),
				onTabAction: function () {
					if ( this.action == 'view' ) {
						this.buttons[this.buttons.length - 1].focus();
					}
					else {
						this.buttons[1].focus();
					}
				}.createDelegate(this),
				tabIndex: TABINDEX_EPLEF + 41,
				text: BTN_FRMSAVE
			}, {
				handler: function() {
					this.printEvnPL();
				}.createDelegate(this),
				iconCls: 'print16',
				onShiftTabAction: function () {
					if ( this.action == 'view' ) {
						this.buttons[0].onShiftTabAction();
					}
					else {
						this.buttons[0].focus();
					}
				}.createDelegate(this),
				onTabAction: function () {
					this.buttons[this.buttons.length - 1].focus();
				}.createDelegate(this),
				tabIndex: TABINDEX_EPLEF + 42,
				text: BTN_FRMPRINT
			},{
                    handler: function() {
                        var evn_pl_id = this.findById('EvnPLEditForm').getForm().findField('EvnPL_id').getValue();
						printBirt({
							'Report_FileName': 'FormaKBK_EvnPL.rptdesign',
							'Report_Params': '&paramEvnPL=' + evn_pl_id,
							'Report_Format': 'doc'
						});
                    }.createDelegate(this),
                    hidden: !(getGlobalOptions().region.nick == 'kareliya'),
                    iconCls: 'print16',
                    text: lang['pechat_kvk']
            }
                , {
				text: '-'
			},
			HelpButton(this, -1),
			{
				handler: function() {
					this.onCancelAction();
				}.createDelegate(this),
				iconCls: 'cancel16',
				onShiftTabAction: function () {
					this.buttons[1].focus();
				}.createDelegate(this),
				onTabAction: function () {
					if ( this.action != 'view' ) {
						this.findById('EvnPLEditForm').getForm().findField('EvnPL_NumCard').focus(true)
					}
					else {
						if ( !this.findById('EPLEF_EvnVizitPLPanel').collapsed ) {
							this.findById('EPLEF_EvnVizitPLGrid').getView().focusRow(0);
							this.findById('EPLEF_EvnVizitPLGrid').getSelectionModel().selectFirstRow();
						}
						else if ( !this.findById('EPLEF_EvnUslugaPanel').collapsed ) {
							this.findById('EPLEF_EvnUslugaGrid').getView().focusRow(0);
							this.findById('EPLEF_EvnUslugaGrid').getSelectionModel().selectFirstRow();
						}
						else if ( !this.findById('EPLEF_EvnStickPanel').collapsed ) {
							this.findById('EPLEF_EvnStickGrid').getView().focusRow(0);
							this.findById('EPLEF_EvnStickGrid').getSelectionModel().selectFirstRow();
						}
						else {
							this.buttons[1].focus();
						}
					}
				}.createDelegate(this),
				tabIndex: TABINDEX_EPLEF + 43,
				text: BTN_FRMCANCEL
			}],
			items: [ new sw.Promed.PersonInfoPanel({
				button1OnHide: function() {
					if ( this.action == 'view' ) {
						this.buttons[this.buttons.length - 1].focus();
					}
					else {
						this.findById('EvnPLEditForm').getForm().findField('EvnPL_NumCard').focus(true);
					}
				}.createDelegate(this),
				button2Callback: function(callback_data) {
					var form = this.findById('EvnPLEditForm');
					var evn_pl_id = form.getForm().findField('EvnPL_id').getValue();
					var p = {};
					if(evn_pl_id > 0 && form.getForm().findField('Person_id').getValue()==callback_data.Person_id) {
                        Ext.Ajax.request({
                             failure: function(response, options) {
                                sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_zagruzke_dannyih_formyi'], function() {this.hide();}.createDelegate(this) );
                            },
                            success: function(response, options) {
                                if (!Ext.isEmpty(response.responseText)) {
                                    var response_obj = Ext.util.JSON.decode(response.responseText);
                                    if ( response_obj.success == false ) {
                                        form.getForm().findField('PersonEvn_id').setValue(callback_data.PersonEvn_id);
                                        form.getForm().findField('Server_id').setValue(callback_data.Server_id);
                                        p = {Person_id: callback_data.Person_id, Server_id: callback_data.Server_id};
                                        if (callback_data.PersonEvn_id>0)
                                            p.PersonEvn_id = callback_data.PersonEvn_id;
                                        if (callback_data.Server_id>=0)
                                            p.Server_id =callback_data.Server_id;
                                        this.findById('EPLEF_PersonInformationFrame').load(p);
                                    } else if (response_obj[0].PersonEvn_id > 0) {
                                        form.getForm().findField('PersonEvn_id').setValue(response_obj[0].PersonEvn_id);
                                        form.getForm().findField('Server_id').setValue(response_obj[0].Server_id);
                                        p = {
                                            Person_id: callback_data.Person_id,
                                            Server_id: response_obj[0].Server_id,
                                            PersonEvn_id:response_obj[0].PersonEvn_id,
                                            Evn_setDT:form.getForm().findField('EvnPL_setDate').getValue()
                                        };
                                        this.findById('EPLEF_PersonInformationFrame').load(p);
                                    }
                                }
                            }.createDelegate(this),
                            params: {
                                Evn_id: evn_pl_id
                            },
                            url: '/?c=Person&m=getPersonEvnIdByEvnId'
                        });
                    } else {
                        p.Person_id = form.getForm().findField('Person_id').getValue();
                        this.findById('EPLEF_PersonInformationFrame').load(p);
                    }
					 //или прямо form.PersonEvn_id
				}.createDelegate(this),
				button2OnHide: function() {
					this.findById('EPLEF_PersonInformationFrame').button1OnHide();
				}.createDelegate(this),
				button3OnHide: function() {
					this.findById('EPLEF_PersonInformationFrame').button1OnHide();
				}.createDelegate(this),
				button4OnHide: function() {
					this.findById('EPLEF_PersonInformationFrame').button1OnHide();
				}.createDelegate(this),
				button5OnHide: function() {
					this.findById('EPLEF_PersonInformationFrame').button1OnHide();
				}.createDelegate(this),
				collapsible: true,
				collapsed: true,
				collectAdditionalParams: function(winType) {
					var params = {};

					switch ( winType ) {
						case 5:
							params.Diag_id = null;
							params.LpuSection_id = null;
							params.MedPersonal_id = null;

							var evn_vizit_pl_set_date = null;

							this.findById('EPLEF_EvnVizitPLGrid').getStore().each(function(rec) {
								if ( evn_vizit_pl_set_date == null || evn_vizit_pl_set_date < getValidDT(Ext.util.Format.date(rec.get('EvnVizitPL_setDate'), 'd.m.Y'), rec.get('EvnVizitPL_setTime')) ) {
									evn_vizit_pl_set_date = getValidDT(Ext.util.Format.date(rec.get('EvnVizitPL_setDate'), 'd.m.Y'), rec.get('EvnVizitPL_setTime'));

									params.Diag_id = rec.get('Diag_id');
									params.LpuSection_id = rec.get('LpuSection_id');
									params.MedPersonal_id = rec.get('MedPersonal_id');
								}
							}.createDelegate(this));
						break;
					}

					return params;
				}.createDelegate(this),
				floatable: false,
				id: 'EPLEF_PersonInformationFrame',
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
				id: 'EvnPLEditForm',
				labelAlign: 'right',
				labelWidth: 220,
				items: [{
					name: 'accessType',
					value: '',
					xtype: 'hidden'
				},{
					name: 'canCreateVizit',
					value: '',
					xtype: 'hidden'
				}, {
					name:'EvnPL_IsPaid',
					xtype:'hidden'
				}, {
					name:'EvnPL_IndexRep',
					xtype:'hidden'
				}, {
					name:'EvnPL_IndexRepInReg',
					xtype:'hidden'
				}, {
					name: 'EvnPL_id',
					value: 0,
					xtype: 'hidden'
				}, {
					name: 'EvnPL_lid',
					value: 0,
					xtype: 'hidden'
				}, {
					name: 'EvnPL_IsTransit',
					value: 0,
					xtype: 'hidden'
				},{
                    name: 'Lpu_id',
                    value: 0,
                    xtype: 'hidden'
                },{
					name: 'EvnDirection_id',
					xtype: 'hidden'
				}, {
					name: 'EvnDirection_IsAuto',
					xtype: 'hidden'
				}, {
					name: 'Lpu_fid',
					xtype: 'hidden'
				}, {
					name: 'EvnDirection_IsReceive',
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
					name: 'EvnPL_setDate',
					xtype: 'hidden'
				}, {
					name: 'EvnPL_disDate',
					xtype: 'hidden'
				},{
					name: 'Server_id',
					value: -1,
					xtype: 'hidden'
				}, {
					name: 'CmpCallCard_id',
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
							enableKeyEvents: true,
							fieldLabel: lang['№_talona'],
							listeners: {
								'keydown': function (inp, e) {
									switch (e.getKey()) {
										case Ext.EventObject.F2:
											e.stopEvent();
											this.getEvnPLNumber();
											break;

										case Ext.EventObject.TAB:
											if (e.shiftKey == true) {
												e.stopEvent();
												this.buttons[this.buttons.length - 1].focus();
											}
											break;

									}
								}.createDelegate(this)
							},
							autoCreate: { tag: "input", type: "text", maxLength: "30", autocomplete: "off" },
							name: 'EvnPL_NumCard',
							onTriggerClick: function () {
								this.getEvnPLNumber();
							}.createDelegate(this),
							tabIndex: TABINDEX_EPLEF + 1,
							triggerClass: 'x-form-plus-trigger',
							validateOnBlur: false,
							width: 150,
							xtype: 'trigger'
						}]
					}, {
						border: false,
						style: 'padding: 0px 0px 0px 4px;',
						layout: 'form',
						items: [{
							name: 'EvnPL_IsCons',
							hideLabel: true,
							boxLabel: langs('Консультативный приём'),
							hidden: true,
							tabIndex: TABINDEX_EPLEF + 2,
							xtype: 'checkbox'
						}]
					}]
				}, {
					border: false,
					id: 'EPLEF_KDKBFields',
					layout: 'column',

					items: [{
						border: false,
						layout: 'form',
						items: [{
							comboSubject: 'YesNo',
							fieldLabel: lang['vpervyie_v_dannoy_lpu'],
							hiddenName: 'EvnPL_IsFirstTime',
							tabIndex: TABINDEX_EPLEF + 3,
							width: 150,
							xtype: 'swcommonsprcombo'
						}]
					}, {
						border: false,
						layout: 'form',
						items: [{
							allowDecimals: false,
							allowNegative: false,
							enableKeyEvents: true,
							fieldLabel: lang['kategoriya_slojnosti'],
							listeners: {
								'keydown': function(inp, e) {
									switch ( e.getKey() ) {
										case Ext.EventObject.TAB:
											if ( e.shiftKey == false ) {
												e.stopEvent();

												var base_form = this.findById('EvnPLEditForm').getForm();

												if ( !this.findById('EPLEF_DirectInfoPanel').collapsed && this.action != 'view' ) {
													base_form.findField('PrehospDirect_id').focus(true);
												}
												else if ( !this.findById('EPLEF_EvnVizitPLPanel').collapsed ) {
													this.findById('EPLEF_EvnVizitPLGrid').getView().focusRow(0);
													this.findById('EPLEF_EvnVizitPLGrid').getSelectionModel().selectFirstRow();
												}
												else if ( !this.findById('EPLEF_EvnUslugaPanel').collapsed ) {
													this.findById('EPLEF_EvnUslugaGrid').getView().focusRow(0);
													this.findById('EPLEF_EvnUslugaGrid').getSelectionModel().selectFirstRow();
												}
												else if ( !this.findById('EPLEF_EvnStickPanel').collapsed ) {
													this.findById('EPLEF_EvnStickGrid').getView().focusRow(0);
													this.findById('EPLEF_EvnStickGrid').getSelectionModel().selectFirstRow();
												}
												else if ( !this.findById('EPLEF_ResultPanel').collapsed && this.action != 'view' ) {
													base_form.findField('EvnPL_IsFinish').focus(true);
												}
												/*else if ( !this.findById('EPLEF_DirectPanel').collapsed && this.action != 'view' ) {
													base_form.findField('DirectType_id').focus(true);
												}*/
												else if ( this.action == 'view' ) {
													this.buttons[1].focus();
												}
												else {
													this.buttons[0].focus();
												}
											}
										break;
									}
								}.createDelegate(this)
							},
							maxValue: 5,
							minValue: 1,
							name: 'EvnPL_Complexity',
							tabIndex: TABINDEX_EPLEF + 4,
							width: 150,
							xtype: 'numberfield'
						}]
					}]
				}, {
					border: false,
					hidden: getRegionNick() != 'kareliya',
					layout: 'form',
					xtype: 'panel',
					items: [{
						hiddenName: 'MedicalCareKind_id',
						allowBlank: getRegionNick() != 'kareliya',
						fieldLabel: lang['meditsinskaya_pomosch'],
						comboSubject: 'MedicalCareKind',
						xtype: 'swcommonsprcombo',
						width: 300
					}]
				},
				new sw.Promed.Panel({
					autoHeight: true,
					bodyStyle: 'padding-top: 0.5em;',
					border: true,
					collapsible: true,
					id: 'EPLEF_DirectInfoPanel',
					layout: 'form',
					listeners: {
						'expand': function(panel) {
							// this.findById('EvnPLEditForm').getForm().findField('PrehospDirect_id').focus(true);
						}.createDelegate(this)
					},
					style: 'margin-bottom: 0.5em;',
					title: lang['1_dannyie_o_napravlenii'],
					items: [
					    this.panelEvnDirectionAll
                    ]
				}),

				new sw.Promed.Panel({
					border: true,
					collapsible: true,
					height: 140,
					id: 'EPLEF_EvnVizitPLPanel',
					isLoaded: false,
					layout: 'border',
					listeners: {
						'expand': function(panel) {
							if ( panel.isLoaded === false ) {
								panel.isLoaded = true;
								panel.findById('EPLEF_EvnVizitPLGrid').getStore().load({
									params: {
										EvnPL_id: this.findById('EvnPLEditForm').getForm().findField('EvnPL_id').getValue()
									},
									callback: function(records, options, success) {
										if ( success ) {
											//win.checkAbort();
											win.refreshFieldsVisibility(['MedicalCareBudgType_id']);
										}
									}
								});
							}
							panel.doLayout();
						}.createDelegate(this)
					},
					style: 'margin-bottom: 0.5em;',
					title: lang['2_posescheniya'],
					//frame: true,
					//split:true,
					items: [ new Ext.grid.GridPanel({
						autoExpandColumn: 'autoexpand_vizit',
						autoExpandMin: 100,
						border: false,
						columns: [{
							dataIndex: 'EvnVizitPL_setDate',
							header: lang['data_posescheniya'],
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
							id: 'autoexpand_vizit',
							resizable: true,
							sortable: true
						}, {
							dataIndex: 'UslugaComplex_Name',
							header: lang['kod_posescheniya'],
							hidden: !(getGlobalOptions().region && getGlobalOptions().region.nick.inlist([ 'ufa' ])),
							resizable: true,
							sortable: true,
							width: 300
						}, {
							dataIndex: 'Diag_Name',
							header: lang['osnovnoy_diagnoz'],
							hidden: false,
							resizable: true,
							sortable: true,
							width: 130
						}, {
							dataIndex: 'Diag_Code',
							header: lang['kod'],
							hidden: false,
							resizable: true,
							sortable: true,
							width: 80
						}, {
							dataIndex: 'ServiceType_Name',
							header: lang['mesto_obslujivaniya'],
							hidden: false,
							resizable: true,
							sortable: true,
							width: 130
						}, {
							dataIndex: 'VizitType_Name',
							header: lang['tsel_posescheniya'],
							hidden: false,
							resizable: true,
							sortable: true,
							width: 130
						}, {
							dataIndex: 'VizitType_SysNick',
							hidden: true
						}, {
							dataIndex: 'PayType_Name',
							header: lang['vid_oplatyi'],
							hidden: false,
							resizable: true,
							sortable: true,
							width: 130
						}, {
							dataIndex: 'EvnVizitPL_NumGroup',
							header: langs('Группировка'),
							hidden: getRegionNick() != 'vologda',
							sortable: true,
							width: 100
						}, {
							dataIndex: 'PayType_SysNick',
							hidden: true
						}, {
							dataIndex: 'TreatmentClass_id',
							hidden: true,
							header: 'Вид обращения'
						}],
						frame: false,
						id: 'EPLEF_EvnVizitPLGrid',
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

								var grid = Ext.getCmp('EPLEF_EvnVizitPLGrid');

								switch ( e.getKey() ) {
									case Ext.EventObject.DELETE:
										Ext.getCmp('EvnPLEditWindow').deleteEvent('EvnVizitPL');
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

											Ext.getCmp('EvnPLEditWindow').openEvnVizitPLEditWindow(action);
										} else {
											var params = {};
											params['key_id'] = grid.getSelectionModel().getSelected().data.EvnVizitPL_id;
											params['key_field'] = 'EvnVizitPL_id';
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

										Ext.getCmp('EvnPLEditWindow').openEvnVizitPLEditWindow(action);
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
										var base_form = this.findById('EvnPLEditForm').getForm();

										grid.getSelectionModel().clearSelections();
										grid.getSelectionModel().fireEvent('rowselect', grid.getSelectionModel());

										if ( e.shiftKey == false ) {
											if ( !this.findById('EPLEF_EvnUslugaPanel').collapsed ) {
												this.findById('EPLEF_EvnUslugaGrid').getView().focusRow(0);
												this.findById('EPLEF_EvnUslugaGrid').getSelectionModel().selectFirstRow();
											}
											else if ( !this.findById('EPLEF_EvnStickPanel').collapsed ) {
												this.findById('EPLEF_EvnStickGrid').getView().focusRow(0);
												this.findById('EPLEF_EvnStickGrid').getSelectionModel().selectFirstRow();
											}
											else if ( !this.findById('EPLEF_ResultPanel').collapsed && this.action != 'view' ) {
												base_form.findField('EvnPL_IsFinish').focus(true);
											}
											/*else if ( !this.findById('EPLEF_DirectPanel').collapsed && this.action != 'view' ) {
												base_form.findField('DirectType_id').focus(true);
											}*/
											else if ( this.action == 'view' ) {
												this.buttons[1].focus();
											}
											else {
												this.buttons[0].focus();
											}
										}
										else {
											if ( !this.findById('EPLEF_DirectInfoPanel').collapsed && this.action != 'view' ) {
												base_form.findField('EvnPL_IsUnport').focus(true);
											}
											else if ( this.action == 'view' ) {
												this.buttons[this.buttons.length - 1].focus();
											}
											else {
												base_form.findField('EvnPL_Complexity').focus(true);
											}
										}
									break;
								}
							},
							scope: this,
							stopEvent: true
						}],
						layout: 'fit',
						listeners: {
							'rowdblclick': function(grid, number, obj) {

                                var access_type = 'view',
                                    action = 'view',
                                    id = null,
                                    selected_record = grid.getSelectionModel().getSelected();

                                if ( selected_record ) {
                                    access_type = selected_record.get('accessType');
                                    id = selected_record.get('EvnVizitPL_id');
                                }

								if (
									!Ext.isEmpty(id)
									&& (win.action != 'view' || win.gridAccess != 'view')
									&& access_type == 'edit'
									&& selected_record.get('EvnVizitPL_IsSigned') == 1
									&& (
										Ext.isEmpty(getGlobalOptions().medpersonal_id)
										|| Ext.isEmpty(selected_record.get('MedPersonal_id'))
										|| userHasWorkPlaceAtLpuSection(selected_record.get('LpuSection_id')) == true
										|| getGlobalOptions().isMedStatUser == true
										|| isSuperAdmin() == true
									)
								) {
									action = 'edit';
								}

								this.openEvnVizitPLEditWindow(action);
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
									var toolbar = this.findById('EPLEF_EvnVizitPLGrid').getTopToolbar();

									if ( selected_record ) {
										access_type = selected_record.get('accessType');
										id = selected_record.get('EvnVizitPL_id');
									}

									toolbar.items.items[1].disable();
									toolbar.items.items[3].disable();

									if ( id ) {
										toolbar.items.items[2].enable();

										if (
											(win.action != 'view' || win.gridAccess != 'view')
											&& access_type == 'edit'
											&& selected_record.get('EvnVizitPL_IsSigned') == 1
											&& (
												Ext.isEmpty(getGlobalOptions().medpersonal_id)
												|| Ext.isEmpty(selected_record.get('MedPersonal_id'))
												|| userHasWorkPlaceAtLpuSection(selected_record.get('LpuSection_id')) == true
												|| getGlobalOptions().isMedStatUser == true
												|| isSuperAdmin() == true
											)
										) {
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
										LoadEmptyRow(this.findById('EPLEF_EvnVizitPLGrid'));
									}
									// узкое место, конечно получилось, по идее надо брать высоту шапки и высоту тулбара, а не 78
									if ( store.getCount() < 3 ) {
										this.findById('EPLEF_EvnVizitPLPanel').setHeight(95+store.getCount()*21);
									}
									else
									{
										var height = 140;
										if ( getGlobalOptions().region && getGlobalOptions().region.nick == 'ufa' ) {
											if(this.width<this.findById('EPLEF_EvnVizitPLPanel').getSize().width){
												height = 195
											}
											height = 185;
										}
										this.findById('EPLEF_EvnVizitPLPanel').setHeight(height);
									}

									this.filterResultClassCombo();
									this.setMedicalStatusComboVisible();
									this.setDiagConcComboVisible();
									this.setInterruptLeaveTypeVisible();
									this.setDiagFidAndLid();

									var base_form = this.findById('EvnPLEditForm').getForm();

									if ( getRegionNick() == 'khak' ) {
										if(!win.fo){
											win.calcFedLeaveType();
											win.calcFedResultDeseaseType();
										}
										sw.Promed.EvnPL.filterFedResultDeseaseType({
											fieldFedLeaveType: base_form.findField('LeaveType_fedid'),
											fieldFedResultDeseaseType: base_form.findField('ResultDeseaseType_fedid')
										});
										sw.Promed.EvnPL.filterFedLeaveType({
											fieldFedLeaveType: base_form.findField('LeaveType_fedid'),
											fieldFedResultDeseaseType: base_form.findField('ResultDeseaseType_fedid')
										});
									}
								}.createDelegate(this)
							},
							reader: new Ext.data.JsonReader({
								id: 'EvnVizitPL_id'
							}, [{
								mapping: 'accessType',
								name: 'accessType',
								type: 'string'
							}, {
								mapping: 'EvnVizitPL_id',
								name: 'EvnVizitPL_id',
								type: 'int'
							}, {
								mapping: 'EvnVizitPL_IsSigned',
								name: 'EvnVizitPL_IsSigned',
								type: 'int'
							}, {
								mapping: 'EvnPL_id',
								name: 'EvnPL_id',
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
								mapping: 'LpuSection_id',
								name: 'LpuSection_id',
								type: 'int'
							}, {
								mapping: 'LpuSectionProfile_id',
								name: 'LpuSectionProfile_id',
								type: 'int'
							}, {
								mapping: 'LpuSectionProfile_Code',
								name: 'LpuSectionProfile_Code',
								type: 'string'
							}, {
								mapping: 'LpuSectionAge_id',
								name: 'LpuSectionAge_id',
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
								mapping: 'LpuUnitSet_Code',
								name: 'LpuUnitSet_Code',
								type: 'int'
							}, {
								mapping: 'UslugaComplex_Code',
								name: 'UslugaComplex_Code',
								type: 'string'
							}, {
								dateFormat: 'd.m.Y',
								mapping: 'EvnVizitPL_setDate',
								name: 'EvnVizitPL_setDate',
								type: 'date'
							}, {
								mapping: 'EvnVizitPL_setTime',
								name: 'EvnVizitPL_setTime',
								type: 'string'
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
								mapping: 'ServiceType_SysNick',
								name: 'ServiceType_SysNick',
								type: 'string'
							}, {
								mapping: 'ServiceType_Name',
								name: 'ServiceType_Name',
								type: 'string'
							}, {
								mapping: 'VizitType_Name',
								name: 'VizitType_Name',
								type: 'string'
							}, {
								mapping: 'VizitType_SysNick',
								name: 'VizitType_SysNick',
								type: 'string'
							}, {
								mapping: 'PayType_Name',
								name: 'PayType_Name',
								type: 'string'
							}, {
								mapping: 'PayType_SysNick',
								name: 'PayType_SysNick',
								type: 'string'
							}, {
								mapping: 'UslugaComplex_Name',
								name: 'UslugaComplex_Name',
								type: 'string'
							}, {
								mapping: 'EvnVizitPL_IsPaid',
								name: 'EvnVizitPL_IsPaid',
								type: 'int'
							}, {
								mapping: 'EvnVizitPL_NumGroup',
								name: 'EvnVizitPL_NumGroup',
								type: 'int'
							}, {
								mapping: 'TreatmentClass_id',
								name: 'TreatmentClass_id',
								type: 'int'
							}]),
							url: '/?c=EvnPL&m=loadEvnVizitPLGrid'
						}),
						tbar: new sw.Promed.Toolbar({
							buttons: [{
								handler: function() {
									this.openEvnVizitPLEditWindow('add');
								}.createDelegate(this),
								iconCls: 'add16',
								text: BTN_GRIDADD,
								tooltip: BTN_GRIDADD_TIP
							}, {
								handler: function() {
									this.openEvnVizitPLEditWindow('edit');
								}.createDelegate(this),
								iconCls: 'edit16',
								text: BTN_GRIDEDIT,
								tooltip: BTN_GRIDEDIT_TIP
							}, {
								handler: function() {
									this.openEvnVizitPLEditWindow('view');
								}.createDelegate(this),
								iconCls: 'view16',
								text: BTN_GRIDVIEW,
								tooltip: BTN_GRIDVIEW_TIP
							}, {
								handler: function() {
									this.deleteEvent('EvnVizitPL');
								}.createDelegate(this),
								iconCls: 'delete16',
								text: BTN_GRIDDEL,
								tooltip: BTN_GRIDDEL_TIP
							}, {
								menu: [
									{text: 'Печать КЛУ при ЗНО', iconCls: 'print16', hidden: getRegionNick() == 'kz', handler: function () { win.printControlCardZno() }},
									{text: 'Печать выписки при онкологии', iconCls: 'print16', hidden: getRegionNick() != 'ekb', handler: function () { win.printControlCardOnko() }}
								],
								iconCls: 'print16',
								text: BTN_GRIDPRINT,
								tooltip: BTN_GRIDPRINT_TIP
							}]
						})
					})//new Ext.SplitBar(win.findById('EPLEF_EvnVizitPLPanel'))
				]
				}),
				new sw.Promed.Panel({
					border: true,
					collapsible: true,
					height: 200,
					id: 'EPLEF_EvnUslugaPanel',
					isLoaded: false,
					layout: 'border',
					listeners: {
						'expand': function(panel) {
							if ( panel.isLoaded === false ) {
								panel.isLoaded = true;
								panel.findById('EPLEF_EvnUslugaGrid').getStore().load({
									params: {
										'class': 'EvnUslugaCommon',
										'pid': this.findById('EvnPLEditForm').getForm().findField('EvnPL_id').getValue()
									},
									callback: function(records, options, success) {
										if ( success ) {
											//win.checkAbort();
										}
									}
								});
							}

							panel.doLayout();
						}.createDelegate(this)
					},
					style: 'margin-bottom: 0.5em;',
					title: lang['3_uslugi'],
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
						id: 'EPLEF_EvnUslugaGrid',
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

								var grid = Ext.getCmp('EPLEF_EvnUslugaGrid');

								switch ( e.getKey() ) {
									case Ext.EventObject.DELETE:
										Ext.getCmp('EvnPLEditWindow').deleteEvent('EvnUsluga');
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

										var toolbar = this.findById('EPLEF_EvnUslugaGrid').getTopToolbar();
										var action = 'add';

										if ( e.getKey() == Ext.EventObject.F3 ) {
											action = 'view';
										}
										else if ( e.getKey() == Ext.EventObject.F4 || e.getKey() == Ext.EventObject.ENTER ) {
											if (toolbar.items.items[1].disabled) {
												action = 'view';
											} else {
												action = 'edit';
											}
										}

										Ext.getCmp('EvnPLEditWindow').openEvnUslugaEditWindow(action);
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
										var base_form = this.findById('EvnPLEditForm').getForm();

										grid.getSelectionModel().clearSelections();
										grid.getSelectionModel().fireEvent('rowselect', grid.getSelectionModel());

										if ( e.shiftKey == false ) {
											if ( !this.findById('EPLEF_EvnStickPanel').collapsed ) {
												this.findById('EPLEF_EvnStickGrid').getView().focusRow(0);
												this.findById('EPLEF_EvnStickGrid').getSelectionModel().selectFirstRow();
											}
											else if ( !this.findById('EPLEF_ResultPanel').collapsed && this.action != 'view' ) {
												base_form.findField('EvnPL_IsFinish').focus(true);
											}
											/*else if ( !this.findById('EPLEF_DirectPanel').collapsed && this.action != 'view' ) {
												base_form.findField('DirectType_id').focus(true);
											}*/
											else if ( this.action == 'view' ) {
												this.buttons[1].focus();
											}
											else {
												this.buttons[0].focus();
											}
										}
										else {
											if ( !this.findById('EPLEF_EvnVizitPLPanel').collapsed ) {
												this.findById('EPLEF_EvnVizitPLGrid').getView().focusRow(0);
												this.findById('EPLEF_EvnVizitPLGrid').getSelectionModel().selectFirstRow();
											}
											else if ( !this.findById('EPLEF_DirectInfoPanel').collapsed && this.action != 'view' ) {
												base_form.findField('EvnPL_IsUnport').focus(true);
											}
											else if ( this.action == 'view' ) {
												this.buttons[this.buttons.length - 1].focus();
											}
											else {
												base_form.findField('EvnPL_Complexity').focus(true);
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
								var toolbar = this.findById('EPLEF_EvnUslugaGrid').getTopToolbar();
								var action = 'edit';

								if (toolbar.items.items[1].disabled) {
									action = 'view';
								}

								this.openEvnUslugaEditWindow(action);
							}.createDelegate(this)
						},
						loadMask: true,
						region: 'center',
						sm: new Ext.grid.RowSelectionModel({
							listeners: {
								'rowselect': function(sm, rowIndex, record) {
									var access_type = 'view';
									var id = null;
									var evn_class = '';
									var selected_record = sm.getSelected();
									var toolbar = this.findById('EPLEF_EvnUslugaGrid').getTopToolbar();

									if ( selected_record ) {
										evn_class = selected_record.get('EvnClass_SysNick');
										access_type = selected_record.get('accessType');
										id = selected_record.get('EvnUsluga_id');
									}

									toolbar.items.items[1].disable();
									toolbar.items.items[3].disable();

									if ( id ) {
										toolbar.items.items[2].enable();

										if ( this.action != 'view' && access_type == 'edit' && evn_class != 'EvnUslugaPar' ) {
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
								'parent': 'EvnPL'
							},
							listeners: {
								'load': function(store, records, index) {
									if ( store.getCount() == 0 ) {
										LoadEmptyRow(this.findById('EPLEF_EvnUslugaGrid'));
									}
									// узкое место, конечно получилось, по идее надо брать высоту шапки и высоту тулбара, а не 78
									if ( store.getCount() < 3 ) {
										this.findById('EPLEF_EvnUslugaPanel').setHeight(95+store.getCount()*21);
									}
									else
									{
										this.findById('EPLEF_EvnUslugaPanel').setHeight(140);
									}

									// this.findById('EPLEF_EvnUslugaGrid').getView().focusRow(0);
									// this.findById('EPLEF_EvnUslugaGrid').getSelectionModel().selectFirstRow();
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
								mapping: 'EvnUsluga_pid',
								name: 'EvnUsluga_pid',
								type: 'int'
							}, {
								mapping: 'EvnClass_SysNick',
								name: 'EvnClass_SysNick',
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
								iconCls: 'add16',
								text: lang['dobavit'],
								menu: {
									xtype: 'menu',
									plain: true,
									items: [{
										handler: function() {
											this.openEvnUslugaEditWindow('add');
										}.createDelegate(this),
										text: lang['dobavit_obschuyu_uslugu']
									}, {
										handler: function() {
											this.openEvnUslugaEditWindow('addOper');
										}.createDelegate(this),
										text: lang['dobavit_operatsiyu']
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
					id: 'EPLEF_EvnStickPanel',
					isLoaded: false,
					layout: 'border',
					listeners: {
						'expand': function(panel) {
							if ( panel.isLoaded === false ) {
								panel.isLoaded = true;
								panel.findById('EPLEF_EvnStickGrid').getStore().load({
									params: {
										EvnStick_pid: this.findById('EvnPLEditForm').getForm().findField('EvnPL_id').getValue()
									}
								});
							}

							panel.doLayout();
						}.createDelegate(this)
					},
					style: 'margin-bottom: 0.5em;',
					title: lang['4_netrudosposobnost'],
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
							dataIndex: 'Lpu_Nick',
							header: lang['mo_vyidavshaya'],
							hidden: false,
							resizable: true,
							sortable: true,
							width: 100
						}, {
							dataIndex: 'EvnStatus_Name',
							header: lang['tip_lvn'],
							hidden: false,
							resizable: true,
							sortable: true,
							width: 100
						}, {
							dataIndex: 'StickFSSType_Name',
							header: 'Состояние ЛВН в ФСС',
							hidden: getRegionNick() == 'kz',
							resizable: true,
							sortable: true,
							width: 100
						}],
						frame: false,
						id: 'EPLEF_EvnStickGrid',
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

								var grid = this.findById('EPLEF_EvnStickGrid');

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
										var base_form = this.findById('EvnPLEditForm').getForm();

										grid.getSelectionModel().clearSelections();
										grid.getSelectionModel().fireEvent('rowselect', grid.getSelectionModel());

										if ( e.shiftKey == false ) {
											if ( !this.findById('EPLEF_ResultPanel').collapsed && this.action != 'view' ) {
												base_form.findField('EvnPL_IsFinish').focus(true);
											}
											/*else if ( !this.findById('EPLEF_DirectPanel').collapsed && this.action != 'view' ) {
												base_form.findField('DirectType_id').focus(true);
											}*/
											else if ( this.action == 'view' ) {
												this.buttons[1].focus();
											}
											else {
												this.buttons[0].focus();
											}
										}
										else {
											if ( !this.findById('EPLEF_EvnUslugaPanel').collapsed ) {
												this.findById('EPLEF_EvnUslugaGrid').getView().focusRow(0);
												this.findById('EPLEF_EvnUslugaGrid').getSelectionModel().selectFirstRow();
											}
											else if ( !this.findById('EPLEF_EvnVizitPLPanel').collapsed ) {
												this.findById('EPLEF_EvnVizitPLGrid').getView().focusRow(0);
												this.findById('EPLEF_EvnVizitPLGrid').getSelectionModel().selectFirstRow();
											}
											else if ( !this.findById('EPLEF_DirectInfoPanel').collapsed && this.action != 'view' ) {
												base_form.findField('EvnPL_IsUnport').focus(true);
											}
											else if ( this.action == 'view' ) {
												this.buttons[this.buttons.length - 1].focus();
											}
											else {
												base_form.findField('EvnPL_Complexity').focus(true);
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
									var del_access_type = 'view';
									var cancel_access_type = 'view';
									var id;
									var selected_record = sm.getSelected();
									var toolbar = this.findById('EPLEF_EvnStickGrid').getTopToolbar();

									if ( selected_record ) {
										access_type = selected_record.get('accessType');
										del_access_type = selected_record.get('delAccessType');
										cancel_access_type = selected_record.get('cancelAccessType');
										id = selected_record.get('EvnStick_id');
									}

									toolbar.items.items[1].disable();
									toolbar.items.items[3].disable();
									toolbar.items.items[4].disable();
									toolbar.items.items[5].disable();
									toolbar.items.items[6].disable();

									if ( id ) {
										toolbar.items.items[2].enable();

										if ( this.evnStickAction != 'view' && access_type == 'edit' ) {
											toolbar.items.items[1].enable();
										} else if (this.evnStickAction == 'view'){
											toolbar.items.items[0].disable();
										}

										if ( (this.action != 'view' || isRegLvn() || this.gridAccess != 'view') && cancel_access_type == 'edit' ) {
											if (selected_record.get('EvnStick_IsDelQueue') == 2) {
												toolbar.items.items[5].enable();
											} else {
												toolbar.items.items[4].enable();
											}
										}

										if( this.action != 'view' && del_access_type != 'view' ) {
											toolbar.items.items[3].enable();
										}

										if ( selected_record.get('EvnStick_isELN') && (!selected_record.get('requestExist')) ) {
											toolbar.items.items[6].enable();
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
										LoadEmptyRow(this.findById('EPLEF_EvnStickGrid'));
									}

									// this.findById('EPLEF_EvnStickGrid').getView().focusRow(0);
									// this.findById('EPLEF_EvnStickGrid').getSelectionModel().selectFirstRow();
								}.createDelegate(this)
							},
							reader: new Ext.data.JsonReader({
								id: 'EvnStick_id'
							}, [{
								mapping: 'accessType',
								name: 'accessType',
								type: 'string'
							}, {
								mapping: 'delAccessType',
								name: 'delAccessType',
								type: 'string'
							}, {
								mapping: 'cancelAccessType',
								name: 'cancelAccessType',
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
								mapping: 'Lpu_Nick',
								name: 'Lpu_Nick',
								type: 'string'
							}, {
								mapping: 'EvnStatus_Name',
								name: 'EvnStatus_Name',
								type: 'string'
							}, {
								mapping: 'StickFSSType_Name',
								name: 'StickFSSType_Name',
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
							}, {
								mapping: 'EvnStick_ParentTypeName',
								name: 'EvnStick_ParentTypeName',
								type: 'string'
							}, {
								mapping: 'EvnStick_ParentNum',
								name: 'EvnStick_ParentNum',
								type: 'string'
							}, {
								mapping: 'EvnStick_IsOriginal',
								name: 'EvnStick_IsOriginal',
								type: 'string'
							}, {
								mapping: 'EvnStick_IsDelQueue',
								name: 'EvnStick_IsDelQueue',
								type: 'int'
							}, {
								mapping: 'EvnStick_isELN',
								name: 'EvnStick_isELN',
								type: 'int'
							}, {
								mapping: 'requestExist',
								name: 'requestExist',
								type: 'int'
							}]),
							url: '/?c=Stick&m=loadEvnStickGrid'
						}),
						view: new Ext.grid.GridView({
							getRowClass: function (row, index) {
								var cls = '';
								if (row.get('EvnStick_IsDelQueue') == 2) {
									cls = cls + 'x-grid-rowbackgray ';
								}
								if (cls.length == 0)
									cls = 'x-grid-panel';
								return cls;
							}
						}),
						tbar: new sw.Promed.Toolbar({
							buttons: [{
								handler: function() {
									this.openEvnStickEditWindow('add');
								}.createDelegate(this),
								iconCls: 'add16',
								text: lang['dobavit']
							}, {
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
							}, {
								handler: function() {
									this.deleteEvent('EvnStick', { ignoreQuestion: true });
								}.createDelegate(this),
								hidden: getRegionNick() == 'kz',
								text: langs('Аннулировать')
							}, {
								handler: function() {
									this.undoDeleteEvnStick();
								}.createDelegate(this),
								hidden: getRegionNick() == 'kz',
								text: 'Восстановить'
							}, {
								handler: function() {
									this.openStickFSSDataEditWindow();
								}.createDelegate(this),
								hidden: getRegionNick() == 'kz',
								disabled: true,
								text: 'Создать запрос в ФСС'
							}]
						})
					})]
				}),
				new sw.Promed.Panel({
					autoHeight: true,
					bodyStyle: 'padding-top: 0.5em;',
					border: true,
					collapsible: true,
					id: 'EPLEF_ResultPanel',
					layout: 'form',
						listeners: {
						'expand': function(panel) {
							// this.findById('EvnPLEditForm').getForm().findField('EvnPL_IsFinish').focus(true);
						}.createDelegate(this)
					},
					style: 'margin-bottom: 0.5em;',
					title: lang['5_rezultat'],
					items: [{
						fieldLabel: lang['povtornaya_podacha'],
						listeners: {
							'check': function(checkbox, value) {
								if ( getRegionNick() != 'perm' ) {
									return false;
								}

								var base_form = this.findById('EvnPLEditForm').getForm();

								var
									EvnPL_IndexRep = parseInt(base_form.findField('EvnPL_IndexRep').getValue()),
									EvnPL_IndexRepInReg = parseInt(base_form.findField('EvnPL_IndexRepInReg').getValue()),
									EvnPL_IsPaid = parseInt(base_form.findField('EvnPL_IsPaid').getValue());

								var diff = EvnPL_IndexRepInReg - EvnPL_IndexRep;

								if ( EvnPL_IsPaid != 2 || EvnPL_IndexRepInReg == 0 ) {
									return false;
								}

								if ( value == true ) {
									if ( diff == 1 || diff == 2 ) {
										EvnPL_IndexRep = EvnPL_IndexRep + 2;
									}
									else if ( diff == 3 ) {
										EvnPL_IndexRep = EvnPL_IndexRep + 4;
									}
								}
								else if ( value == false ) {
									if ( diff <= 0 ) {
										EvnPL_IndexRep = EvnPL_IndexRep - 2;
									}
								}

								base_form.findField('EvnPL_IndexRep').setValue(EvnPL_IndexRep);

							}.createDelegate(this)
						},
						name: 'EvnPL_RepFlag',
						xtype: 'checkbox'
					}, {
						allowBlank: false,
						enableKeyEvents: true,
						fieldLabel: lang['sluchay_zakonchen'],
						hiddenName: 'EvnPL_IsFinish',
						listeners: {
							'change': function(combo, newValue, oldValue) {
								var index,
									base_form,
									loadMask,
									evnPL_Id;
								
								index = combo.getStore().findBy(function(rec) {
									return (rec.get(combo.valueField) == newValue);
								});

								combo.fireEvent('select', combo, combo.getStore().getAt(index));
								
								if (getRegionNick() == 'kz') {
									base_form = this.findById('EvnPLEditForm').getForm();
									
									if ((evnPL_Id = base_form.findField('EvnPL_id')) &&
										(evnPL_Id = evnPL_Id.getValue()) && Number(evnPL_Id))
									{
										loadMask = new Ext.LoadMask(this.getEl(), {msg: "Изменение признака..."});
										loadMask.show();

										Ext.Ajax.request({
											params: {
												object: 'EvnPLBase'
												, id: evnPL_Id
												, param_name: 'EvnPLBase_IsFinish'
												, param_value: newValue
											},
											callback: function (opt, success, response) {
												loadMask.hide();

												if (!Ext.isEmpty(response.responseText)) {
													var response_obj = Ext.util.JSON.decode(response.responseText);

													if (response_obj.success == false) {
														if (response_obj.Alert_Msg) {
															sw.swMsg.show({
																buttons: Ext.Msg.YESNO,
																fn: function (buttonId, text, obj) {
																	if (buttonId == 'no' && response_obj.Error_Code == 197641) {
																		base_form.findField('EvnPL_IsFinish').setValue(0);
																		combo.fireEvent('select', combo, combo.getStore().getAt(0));
																	}
																}.createDelegate(this),
																icon: Ext.MessageBox.QUESTION,
																msg: response_obj.Alert_Msg,
																title: 'Продолжить сохранение?'
															});
														} else if (response_obj.Error_Msg) {
															sw.swMsg.alert('Ошибка', response_obj.Error_Msg);
														} else {
															sw.swMsg.alert('Ошибка', 'При изменении признака окончания случая возникли ошибки');
														}
													}
												} else {
													sw.swMsg.alert('Ошибка', 'При изменении признака окончания случая возникли ошибки');
												}
											},
											url: '/?c=EvnVizit&m=setEvnVizitParameter'
										});
									}
								}

								this.checkForCostPrintPanel();
								this.setDiagFidAndLid();
								this.refreshFieldsVisibility(['MedicalCareBudgType_id']);

								return true;
							}.createDelegate(this),
							'select': function(combo, record, id) {
								var base_form = this.findById('EvnPLEditForm').getForm();

								if ( !record || record.get('YesNo_Code') == 0 ) {
									base_form.findField('ResultClass_id').clearValue();
									base_form.findField('EvnPL_IsSurveyRefuse').clearValue();
									base_form.findField('InterruptLeaveType_id').clearValue();
									base_form.findField('ResultDeseaseType_id').clearValue();
									base_form.findField('Diag_concid').clearValue();
									base_form.findField('ResultClass_id').setAllowBlank(true);
									base_form.findField('ResultDeseaseType_id').setAllowBlank(true);
                                    base_form.findField('EvnPL_UKL').setAllowBlank(true);
                                    base_form.findField('DirectClass_id').clearValue();
                                    base_form.findField('DirectType_id').clearValue();
                                    base_form.findField('Lpu_oid').clearValue();
                                    base_form.findField('LpuSection_oid').clearValue();

									base_form.findField('ResultDeseaseType_fedid').hideContainer();
									base_form.findField('ResultDeseaseType_fedid').setAllowBlank(true);
									base_form.findField('LeaveType_fedid').hideContainer();
									base_form.findField('LeaveType_fedid').setAllowBlank(true);
									base_form.findField('Diag_lid').setAllowBlank(true);

									base_form.findField('ResultDeseaseType_fedid').clearValue();
									base_form.findField('LeaveType_fedid').clearValue();

									if ( Ext.globalOptions.polka.is_finish_result_block == '1' ) {
                                        //Запрет ввода результата лечения для незаконченного случая
                                        base_form.findField('EvnPL_UKL').disable();
                                        base_form.findField('EvnPL_UKL').setValue('');
										base_form.findField('ResultClass_id').disable();
										base_form.findField('EvnPL_IsSurveyRefuse').disable();
										base_form.findField('InterruptLeaveType_id').disable();
										base_form.findField('Diag_concid').disable();
										base_form.findField('ResultDeseaseType_id').disable();

                                        base_form.findField('DirectClass_id').disable();
                                        base_form.findField('DirectType_id').disable();
                                        base_form.findField('Lpu_oid').disable();
                                        base_form.findField('LpuSection_oid').disable();
									} else {
                                        base_form.findField('EvnPL_UKL').enable();
                                        base_form.findField('ResultClass_id').enable();
                                        base_form.findField('EvnPL_IsSurveyRefuse').enable();
                                        base_form.findField('InterruptLeaveType_id').enable();
										base_form.findField('ResultDeseaseType_id').enable();
                                        base_form.findField('Diag_concid').enable();
                                        base_form.findField('DirectClass_id').enable();
                                        base_form.findField('DirectType_id').enable();
                                        base_form.findField('Lpu_oid').enable();
                                        base_form.findField('LpuSection_oid').enable();
									}
								} else {
									base_form.findField('ResultDeseaseType_fedid').setAllowBlank(sw.Promed.EvnPL.isHiddenFedResultFields());
									base_form.findField('ResultDeseaseType_fedid').showContainer();
									base_form.findField('LeaveType_fedid').setAllowBlank(sw.Promed.EvnPL.isHiddenFedResultFields());
									base_form.findField('LeaveType_fedid').showContainer();
                                    base_form.findField('DirectClass_id').enable();
                                    base_form.findField('DirectType_id').enable();
                                    base_form.findField('Lpu_oid').enable();
                                    base_form.findField('LpuSection_oid').enable();
                                    base_form.findField('EvnPL_UKL').enable();
									base_form.findField('ResultClass_id').enable();
									base_form.findField('EvnPL_IsSurveyRefuse').enable();
									base_form.findField('InterruptLeaveType_id').enable();
									base_form.findField('ResultDeseaseType_id').enable();
									base_form.findField('Diag_concid').enable();
                                    base_form.findField('EvnPL_UKL').setAllowBlank(getRegionNick() == 'ekb');
									base_form.findField('ResultClass_id').setAllowBlank(false);
									base_form.findField('ResultDeseaseType_id').setAllowBlank( !(getRegionNick().inlist([/*'astra',*/'adygeya', 'vologda','buryatiya','kaluga','kareliya','krasnoyarsk','krym','ekb','penza','pskov','yakutiya','yaroslavl'])) );
                                    if ( !base_form.findField('EvnPL_UKL').getValue() && getRegionNick() != 'ekb' ) {
                                        base_form.findField('EvnPL_UKL').setValue(1);
                                    }
									base_form.findField('Diag_lid').setAllowBlank(getRegionNick() == 'kareliya' || record.get('YesNo_Code') != 1);
								}
								if(!win.fo || getRegionNick() == 'khak'){
									win.calcFedLeaveType();
									win.calcFedResultDeseaseType();
								}
								sw.Promed.EvnPL.filterFedResultDeseaseType({
									fieldFedLeaveType: base_form.findField('LeaveType_fedid'),
									fieldFedResultDeseaseType: base_form.findField('ResultDeseaseType_fedid')
								});
								sw.Promed.EvnPL.filterFedLeaveType({
									fieldFedLeaveType: base_form.findField('LeaveType_fedid'),
									fieldFedResultDeseaseType: base_form.findField('ResultDeseaseType_fedid')
								});
								if(getRegionNick() == 'kz'){
									base_form.findField('MedicalStatus_id').setAllowBlank(!base_form.findField('MedicalStatus_id').isVisible() || record.get('YesNo_Code') != 1);
								}
								if (win.action != 'view' && base_form.findField('EvnPL_IsFinish').getValue() != 2) {
									this.findById('EPLEF_EvnVizitPLGrid').getTopToolbar().items.items[0].enable();
								} else {
									this.findById('EPLEF_EvnVizitPLGrid').getTopToolbar().items.items[0].disable();
								}
                                base_form.findField('DirectClass_id').fireEvent('change', base_form.findField('DirectClass_id'), base_form.findField('DirectClass_id').getValue());
							}.createDelegate(this),
							'keydown': function(inp, e) {
								switch ( e.getKey() ) {
									case Ext.EventObject.TAB:
										if ( e.shiftKey == true ) {
											e.stopEvent();

											if ( !this.findById('EPLEF_EvnStickPanel').collapsed ) {
												this.findById('EPLEF_EvnStickGrid').getView().focusRow(0);
												this.findById('EPLEF_EvnStickGrid').getSelectionModel().selectFirstRow();
											}
											else if ( !this.findById('EPLEF_EvnUslugaPanel').collapsed ) {
												this.findById('EPLEF_EvnUslugaGrid').getView().focusRow(0);
												this.findById('EPLEF_EvnUslugaGrid').getSelectionModel().selectFirstRow();
											}
											else if ( !this.findById('EPLEF_EvnVizitPLPanel').collapsed ) {
												this.findById('EPLEF_EvnVizitPLGrid').getView().focusRow(0);
												this.findById('EPLEF_EvnVizitPLGrid').getSelectionModel().selectFirstRow();
											}
											else if ( !this.findById('EPLEF_DirectInfoPanel').collapsed && this.action != 'view' ) {
												base_form.findField('EvnPL_IsUnport').focus(true);
											}
											else if ( this.action == 'view' ) {
												this.buttons[this.buttons.length - 1].focus();
											}
											else {
												base_form.findField('EvnPL_Complexity').focus(true);
											}
										}
									break;
								}
							}.createDelegate(this)
						},
						tabIndex: TABINDEX_EPLEF + 20,
						width: 70,
						xtype: 'swyesnocombo'
					}, {
						fieldLabel: langs('Отказ от прохождения медицинских обследований'),
						hiddenName: 'EvnPL_IsSurveyRefuse',
						lastQuery: '',
						tabIndex: TABINDEX_EPLEF + 20,
						width: 70,
						xtype: 'swyesnocombo'
					}, {
						hiddenName: 'ResultClass_id',
						fieldLabel: ( getRegionNick().inlist(['buryatiya','kareliya','krym','ekb','penza','pskov']) ) ? lang['rezultat_obrascheniya'] : lang['rezultat_lecheniya'],
						lastQuery: '',
						tabIndex: TABINDEX_EPLEF + 21,
						width: 300,
						xtype: 'swresultclasscombo',
						listeners: {
							'change': function(combo, newValue, oldValue) {
								var index = combo.getStore().findBy(function(rec) {
									return (rec.get(combo.valueField) == newValue);
								});
								combo.fireEvent('select', combo, combo.getStore().getAt(index));
							}.createDelegate(this),
							'select': function(combo, record, id) {
								var base_form = this.findById('EvnPLEditForm').getForm();
								if (getRegionNick() == 'kz') {
									switch (record.get('ResultClass_Code')) {
										case 2: // Улучшение
											base_form.findField('MedicalStatus_id').setValue(8);
											break;
										case 4: // Смерть
											base_form.findField('MedicalStatus_id').setValue(3);
											break;
										case 3: // Динамическое наблюдение
										case 5: // Без перемен
											base_form.findField('MedicalStatus_id').setValue(2);
											break;
									}
								}
							}.createDelegate(this)
						}
					}, {
						comboSubject: 'MedicalStatus',
						hiddenName: 'MedicalStatus_id',
						fieldLabel: langs('Состояние здоровья'),
						hidden: getRegionNick() != 'kz',
						hideLabel: getRegionNick() != 'kz',
						lastQuery: '',
						tabIndex: TABINDEX_EPLEF + 21,
						width: 300,
						xtype: 'swcommonsprcombo'
					}, {
						comboSubject: 'InterruptLeaveType',
						fieldLabel: lang['sluchay_prervan'],
						hiddenName: 'InterruptLeaveType_id',
						lastQuery: '',
						tabIndex: TABINDEX_EPLEF + 22,
						width: 300,
						xtype: 'swcommonsprcombo',
						listeners: {
							'change': function(combo, newValue, oldValue) {
								var index = combo.getStore().findBy(function(rec) {
									return (rec.get(combo.valueField) == newValue);
								});
								combo.fireEvent('select', combo, combo.getStore().getAt(index));
								return true;
							}.createDelegate(this),
							'select': function(combo, record, id) {
								var base_form = this.findById('EvnPLEditForm').getForm();
								base_form.findField('LeaveType_fedid').clearFilter();
								base_form.findField('ResultDeseaseType_fedid').clearFilter();
								if(!win.fo){
									win.calcFedLeaveType();
									win.calcFedResultDeseaseType();
								}
								sw.Promed.EvnPL.filterFedResultDeseaseType({
									fieldFedLeaveType: base_form.findField('LeaveType_fedid'),
									fieldFedResultDeseaseType: base_form.findField('ResultDeseaseType_fedid')
								});
								sw.Promed.EvnPL.filterFedLeaveType({
									fieldFedLeaveType: base_form.findField('LeaveType_fedid'),
									fieldFedResultDeseaseType: base_form.findField('ResultDeseaseType_fedid')
								});
							}.createDelegate(this)
						}
					}, {
						comboSubject: 'ResultDeseaseType',
						fieldLabel: lang['ishod'],
						hiddenName: 'ResultDeseaseType_id',
						lastQuery: '',
						moreFields: [
							{ name: 'ResultDeseaseType_fedid', type: 'int' }
						],
						tabIndex: TABINDEX_EPLEF + 23,
						width: 300,
						xtype: 'swcommonsprcombo'
					}, {
                        border: false,
                        hidden: (getGlobalOptions().region && getGlobalOptions().region.nick.inlist([ 'ekb' ])), // Открыто для Екатеринбурга
                        layout: 'form',
                        items: [{
                            allowDecimals: true,
                            allowNegative: false,
                            fieldLabel: lang['ukl'],
                            maxValue: 1,
                            name: 'EvnPL_UKL',
                            tabIndex: TABINDEX_EPLEF + 24,
                            width: 70,
                            value: 1,
                            xtype: 'numberfield'
                        }]
                    }, {
                        border: false,
						hidden: !getRegionNick().inlist(['kareliya', 'astra', 'buryatiya','krym']),
                        layout: 'form',
                        items: [{
							fieldLabel: 'Впервые выявленная инвалидность',
							hiddenName: 'EvnPL_IsFirstDisable',
							tabIndex: TABINDEX_EPLEF + 24,
							xtype: 'swyesnocombo',
							width: 70
                        }, {
							fieldLabel: 'Впервые выявленная инвалидность',
							hiddenName: 'PrivilegeType_id',
							tabIndex: TABINDEX_EPLEF + 24,
							loadParams: getRegionNick() != 'krym' ? {params: {where: ' where PrivilegeType_Code in (81,82,83,84)'}} : {params: {where: ' where ReceptFinance_id = 1 and PrivilegeType_Code in (81,82,83,84)'}},
							xtype: 'swprivilegetypecombo',
							width: 200
                        }]
                    }, {
                        id:'EPLEF_DirectFieldset',
                        xtype: 'fieldset',
                        title: lang['napravlenie'],
                        labelWidth: 165,
                        style: 'margin: 3px;',
                        autoHeight: true,
                        items:[{
                            enableKeyEvents: true,
                            hiddenName: 'DirectType_id',
                            lastQuery: '',
                            listeners: {
								'change': function(combo, newValue, oldValue) {
									var index = combo.getStore().findBy(function(rec) {
										return (rec.get(combo.valueField) == newValue);
									});
									combo.fireEvent('select', combo, combo.getStore().getAt(index), index);
								}.createDelegate(this),
								'select': function(combo, record, idx) {
									var
										base_form = this.findById('EvnPLEditForm').getForm(),
										index,
										lpuSectionFilter = {},
										LpuSection_oid = base_form.findField('LpuSection_oid').getValue();
									if(!win.fo){
										win.calcFedLeaveType();
										win.calcFedResultDeseaseType();
									}
									/*sw.Promed.EvnPL.filterFedResultDeseaseType({
										fieldFedLeaveType: base_form.findField('LeaveType_fedid'),
										fieldFedResultDeseaseType: base_form.findField('ResultDeseaseType_fedid')
									})*/
									if ( typeof record == 'object' && !Ext.isEmpty(record.get(combo.valueField)) ) {
										switch ( Number(record.get('DirectType_Code')) ) {
											// В круглосуточный стационар
											case 1:
												lpuSectionFilter.arrayLpuUnitType = [ 2 ];
											break;

											// В стационар дневного пребывания
											case 3:
												lpuSectionFilter.arrayLpuUnitType = [ 3 ];
											break;

											// В дневной стационар при поликлинике
											case 4:
												lpuSectionFilter.arrayLpuUnitType = [ 5 ];
											break;

											// В стационар на дому
											case 5:
												lpuSectionFilter.arrayLpuUnitType = [ 4 ];
											break;

											// На консультацию
											case 6:
												lpuSectionFilter.isPolka = true;
											break;
										}
									}

									var setDate =null;
									var evn_vizit_pl_store = this.findById('EPLEF_EvnVizitPLGrid').getStore();
									var WithoutChildLpuSectionAge =false;
									evn_vizit_pl_store.each(function(record) {
															if ( typeof setDate != 'object' || record.get('EvnVizitPL_setDate') <= setDate ) {
																setDate = record.get('EvnVizitPL_setDate');
															}
														});

									var person_age = swGetPersonAge(this.findById('EPLEF_PersonInformationFrame').getFieldValue('Person_Birthday'), setDate);

									if (person_age >= 18&&getRegionNick()!='ufa') {
										WithoutChildLpuSectionAge = true;
									}
									lpuSectionFilter.WithoutChildLpuSectionAge = WithoutChildLpuSectionAge;
									setLpuSectionGlobalStoreFilter(lpuSectionFilter);
									base_form.findField('LpuSection_oid').getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));

									if ( !Ext.isEmpty(LpuSection_oid) ) {
										index = base_form.findField('LpuSection_oid').getStore().findBy(function(rec) {
											return (rec.get(base_form.findField('LpuSection_oid').valueField) == LpuSection_oid);
										});
									}

									if ( index >= 0 ) {
										base_form.findField('LpuSection_oid').setValue(LpuSection_oid);
									}
									else {
										base_form.findField('LpuSection_oid').clearValue();
									}
								}.createDelegate(this),
                                'keydown': function(inp, e) {
                                    switch ( e.getKey() ) {
                                        case Ext.EventObject.TAB:
                                            if ( e.shiftKey == true ) {
                                                e.stopEvent();

                                                var base_form = this.findById('EvnPLEditForm').getForm();

                                                if ( !this.findById('EPLEF_ResultPanel').collapsed ) {
                                                    base_form.findField('EvnPL_UKL').focus(true);
                                                }
                                                else if ( !this.findById('EPLEF_EvnStickPanel').collapsed ) {
                                                    this.findById('EPLEF_EvnStickGrid').getView().focusRow(0);
                                                    this.findById('EPLEF_EvnStickGrid').getSelectionModel().selectFirstRow();
                                                }
                                                else if ( !this.findById('EPLEF_EvnUslugaPanel').collapsed ) {
                                                    this.findById('EPLEF_EvnUslugaGrid').getView().focusRow(0);
                                                    this.findById('EPLEF_EvnUslugaGrid').getSelectionModel().selectFirstRow();
                                                }
                                                else if ( !this.findById('EPLEF_EvnVizitPLPanel').collapsed ) {
                                                    this.findById('EPLEF_EvnVizitPLGrid').getView().focusRow(0);
                                                    this.findById('EPLEF_EvnVizitPLGrid').getSelectionModel().selectFirstRow();
                                                }
                                                else if ( !this.findById('EPLEF_DirectInfoPanel').collapsed && this.action != 'view' ) {
                                                    base_form.findField('EvnPL_IsUnport').focus(true);
                                                }
                                                else if ( this.action == 'view' ) {
                                                    this.buttons[this.buttons.length - 1].focus();
                                                }
                                                else {
                                                    base_form.findField('EvnPL_Complexity').focus(true);
                                                }
                                            }
                                            break;
                                    }
                                }.createDelegate(this)
                            },
                            tabIndex: TABINDEX_EPLEF + 25,
                            width: 300,
                            xtype: 'swdirecttypecombo'
                        }, {
                            hiddenName: 'DirectClass_id',
                            lastQuery: '',
                            listeners: {
								'change': function(combo, newValue, oldValue) {
									var index = combo.getStore().findBy(function(rec) {
										return (rec.get(combo.valueField) == newValue);
									});
									combo.fireEvent('select', combo, combo.getStore().getAt(index), index);
								}.createDelegate(this),
								'select': function(combo, record, idx) {
                                    var base_form = this.findById('EvnPLEditForm').getForm();

                                    var lpu_combo = base_form.findField('Lpu_oid');
                                    var lpu_section_combo = base_form.findField('LpuSection_oid');

                                    lpu_combo.clearValue();
                                    lpu_section_combo.clearValue();

									if ( typeof record == 'object' && !Ext.isEmpty(record.get(combo.valueField)) ) {
										if ( record.get('DirectClass_Code') == 1 ) {
											lpu_section_combo.enable();
											lpu_combo.disable();
										}
										else if ( record.get('DirectClass_Code') == 2 ) {
											lpu_section_combo.disable();
											lpu_combo.enable();
										}
									}
									else {
										lpu_section_combo.disable();
										lpu_combo.disable();
									}
									if(!win.fo){
										win.calcFedLeaveType();
										win.calcFedResultDeseaseType();
									}
									/*sw.Promed.EvnPL.filterFedResultDeseaseType({
									fieldFedLeaveType: base_form.findField('LeaveType_fedid'),
									fieldFedResultDeseaseType: base_form.findField('ResultDeseaseType_fedid')
								})*/
                                }.createDelegate(this)
                            },
                            tabIndex: TABINDEX_EPLEF + 26,
                            width: 300,
                            xtype: 'swdirectclasscombo'
                        }, {
                            hiddenName: 'LpuSection_oid',
                            tabIndex: TABINDEX_EPLEF + 27,
                            width: 500,
                            xtype: 'swlpusectionglobalcombo'
                        }, {
                            displayField: 'Org_Name',
                            editable: false,
                            enableKeyEvents: true,
                            fieldLabel: lang['lpu'],
                            hiddenName: 'Lpu_oid',
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
                                var base_form = this.findById('EvnPLEditForm').getForm();
                                var combo = base_form.findField('Lpu_oid');

                                if ( combo.disabled ) {
                                    return false;
                                }

                                var current_window = this;
                                var direct_class_combo = base_form.findField('DirectClass_id');
                                var direct_class_id = direct_class_combo.getValue();
                                var record = direct_class_combo.getStore().getById(direct_class_id);

                                if ( !record ) {
                                    return false;
                                }

                                var direct_class_code = record.get('DirectClass_Code');
                                var org_type = 'lpu';

                                getWnd('swOrgSearchWindow').show({
                                    object: org_type,
                                    onClose: function() {
                                        combo.focus(true, 200)
                                    },
									//onlyFromDictionary: true,
                                    onSelect: function(org_data) {
                                        if ( org_data.Lpu_id > 0 ) {
                                            combo.getStore().loadData([{
                                                Lpu_id: org_data.Lpu_id,
                                                Org_Name: org_data.Org_Name
                                            }]);
                                            combo.setValue(org_data.Lpu_id);
                                            getWnd('swOrgSearchWindow').hide();
                                        }/* else {
											if (IS_DEBUG){
												sw.swMsg.alert(lang['oshibka'], lang['peredana_mo_s_lpu_id_<_0']);
												return false;
											}
										}*/
                                    }
                                });
                            }.createDelegate(this),
                            store: new Ext.data.JsonStore({
                                autoLoad: false,
                                fields: [
                                    {name: 'Lpu_id', type: 'int'},
                                    {name: 'Org_Name', type: 'string'}
                                ],
                                key: 'Lpu_id',
                                sortInfo: {
                                    field: 'Org_Name'
                                },
                                url: C_ORG_LIST
                            }),
                            tabIndex: TABINDEX_EPLEF + 28,
                            tpl: new Ext.XTemplate(
                                '<tpl for="."><div class="x-combo-list-item">',
                                '{Org_Name}',
                                '</div></tpl>'
                            ),
                            trigger1Class: 'x-form-search-trigger',
                            triggerAction: 'none',
                            valueField: 'Lpu_id',
                            width: 500,
                            xtype: 'swbaseremotecombo'
                        }, {
							xtype: 'swcheckbox',
							name: 'EvnPL_isMseDirected',
							fieldLabel: 'Пациент направлен на МСЭ',
							hideLabel: ! getRegionNick().inlist(['astra']),
							hidden: ! getRegionNick().inlist(['astra'])
						}]
                    }, {
						checkAccessRights: true,
						fieldLabel: lang['zaklyuch_diagnoz'],
						hiddenName: 'Diag_lid',
						tabIndex: TABINDEX_EPLEF + 29,
						width: 460,
						xtype: 'swdiagcombo',
						onChange: function(combo, newValue, oldValue) {
							this.setDiagConcComboVisible();
							//this.checkAbort();
						}.createDelegate(this)
					}, {
						checkAccessRights: true,
						fieldLabel: lang['zaklyuch_vneshnyaya_prichina'],
						hiddenName: 'Diag_concid',
						tabIndex: TABINDEX_EPLEF + 30,
						width: 460,
						xtype: 'swdiagcombo',
						baseFilterFn: function(rec){
							if(typeof rec.get == 'function') {
								return (rec.get('Diag_Code').substr(0,3) >= 'V01' && rec.get('Diag_Code').substr(0,3) <= 'Y98');
							} else if (rec.attributes && rec.attributes.Diag_Code) {
								return (rec.attributes.Diag_Code.substr(0,3) >= 'V01' && rec.attributes.Diag_Code.substr(0,3) <= 'Y98');
							} else {
								return true;
							}
						},
						onChange: function(combo, newValue, oldValue) {
							//this.checkAbort();
						}.createDelegate(this)
					}, {
						disabled: true,
						hideTrigger: true,
						xtype: 'swmedicalcarebudgtypecombo',
						hiddenName: 'MedicalCareBudgType_id',
						fieldLabel: 'Тип мед. помощи (бюджет)',
						width: 460
					}, new sw.Promed.SwPrehospTraumaCombo({
						hiddenName: 'PrehospTrauma_id',
						fieldLabel: lang['vid_travmyi_vneshnego_vozdeystviya'],
						lastQuery: '',
						listeners: {
							'change': function(combo, newValue, oldValue) {
								var base_form = this.findById('EvnPLEditForm').getForm();

								var is_unlaw_combo = base_form.findField('EvnPL_IsUnlaw');
								var record = combo.getStore().getById(newValue);

								if ( !record ) {
									is_unlaw_combo.clearValue();
									is_unlaw_combo.disable();
								}
								else {
									is_unlaw_combo.setValue(1);
									is_unlaw_combo.enable();
								}
							}.createDelegate(this)
						},
						tabIndex: TABINDEX_EPLEF + 31,
						width: 300
					}), {
						border: false,
						layout: 'column',
						items: [{
							border: false,
							layout: 'form',
							items: [ new sw.Promed.SwYesNoCombo({
								fieldLabel: lang['protivopravnaya'],
								hiddenName: 'EvnPL_IsUnlaw',
								lastQuery: '',
								tabIndex: TABINDEX_EPLEF + 32,
								width: 70
							})]
						}, {
							border: false,
							labelWidth: 200,
							layout: 'form',
							items: [ new sw.Promed.SwYesNoCombo({
								fieldLabel: lang['netransportabelnost'],
								hiddenName: 'EvnPL_IsUnport',
								lastQuery: '',
								listeners: {
									'keydown': function(inp, e) {
										switch ( e.getKey() ) {
											case Ext.EventObject.TAB:
												var base_form = this.findById('EvnPLEditForm').getForm();

												if ( e.shiftKey == false ) {
													e.stopEvent();
													this.buttons[0].focus();
													/*if(base_form.findField('EvnPL_IsWithoutDirection').isVisible() && !base_form.findField('EvnPL_IsWithoutDirection').disabled){
														base_form.findField('EvnPL_IsWithoutDirection').focus();
													} else if ( !this.findById('EPLEF_EvnVizitPLPanel').collapsed ) {
														this.findById('EPLEF_EvnVizitPLGrid').getView().focusRow(0);
														this.findById('EPLEF_EvnVizitPLGrid').getSelectionModel().selectFirstRow();
													}
													else if ( !this.findById('EPLEF_EvnUslugaPanel').collapsed ) {
														this.findById('EPLEF_EvnUslugaGrid').getView().focusRow(0);
														this.findById('EPLEF_EvnUslugaGrid').getSelectionModel().selectFirstRow();
													}
													else if ( !this.findById('EPLEF_EvnStickPanel').collapsed ) {
														this.findById('EPLEF_EvnStickGrid').getView().focusRow(0);
														this.findById('EPLEF_EvnStickGrid').getSelectionModel().selectFirstRow();
													}
													else if ( !this.findById('EPLEF_ResultPanel').collapsed && this.action != 'view' ) {
														base_form.findField('EvnPL_IsFinish').focus(true);
													}*/
													/*else if ( !this.findById('EPLEF_DirectPanel').collapsed && this.action != 'view' ) {
													 base_form.findField('DirectType_id').focus(true);
													 }*/
													//else
													if (base_form.findField('LeaveType_fedid').isVisible()){
														base_form.findField('LeaveType_fedid').focus();
													} else if ( this.action == 'view' ) {
														this.buttons[1].focus();
													}
													else {
														this.buttons[0].focus();
													}
												}
												else if ( this.action == 'view' ) {
													e.stopEvent();
													this.buttons[this.buttons.length - 1].focus();
												}
												break;
										}
									}.createDelegate(this)
								},
								tabIndex: TABINDEX_EPLEF + 33,
								width: 70
							})]
						}]
					}, {
                        border: false,
                        hidden: sw.Promed.EvnPL.isHiddenFedResultFields(),
                        layout: 'form',
                        items: [{
                            disabled: getRegionNick() == 'astra',
                            fieldLabel: lang['fed_rezultat'],
                            hiddenName: 'LeaveType_fedid',
							USLOV:3,
							lastQuery: '',
                            width: 500,
							tabIndex: TABINDEX_EPLEF + 34,
                            xtype: 'swleavetypefedcombo'
                        }, {
                            disabled: getRegionNick() == 'astra',
                            fieldLabel: lang['fed_ishod'],
                            hiddenName: 'ResultDeseaseType_fedid',
                            width: 500,
							lastQuery: '',
							tabIndex: TABINDEX_EPLEF + 35,
                            xtype: 'swresultdeseasetypefedcombo'
                        }]
                    }
				]
				}),

				new sw.Promed.Panel({
                            border: true,
                            collapsible: true,
                            height: 200,
                            id: 'EPLEF_EvnDrugPanel',
                            isLoaded: false,
                            layout: 'border',
                            listeners: {
                                'expand': function(panel) {
                                    if ( panel.isLoaded === false ) {
                                        panel.isLoaded = true;
                                        panel.findById('EPLEF_EvnDrugGrid').getStore().load({
                                            params: {
                                                EvnDrug_pid: this.findById('EvnPLEditForm').getForm().findField('EvnPL_id').getValue()
                                            }
                                        });
                                    }

                                    panel.doLayout();
                                }.createDelegate(this)
                            },
                            style: 'margin-bottom: 0.5em;',
                            title: lang['6_ispolzovanie_medikamentov'],
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
                                id: 'EPLEF_EvnDrugGrid',
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

                                        var grid = this.findById('EPLEF_EvnDrugGrid');

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
                                                var base_form = this.findById('EvnPLEditForm').getForm();

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
                                            var toolbar = this.findById('EPLEF_EvnDrugGrid').getTopToolbar();

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
                                                LoadEmptyRow(this.findById('EPLEF_EvnDrugGrid'));
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
                                            var grid = this.findById('EPLEF_EvnDrugGrid');
                                            Ext.ux.GridPrinter.print(grid);
                                        }.createDelegate(this)
                                    }]
                                })
                            })]
                        }),
						new sw.Promed.Panel({
							border: true,
							collapsible: true,
							height: 100,
							id: 'EPLEF_CostPrintPanel',
							layout: 'border',
							listeners: {
								'expand': function(panel) {
									panel.doLayout();
								}.createDelegate(this)
							},
							style: 'margin-bottom: 0.5em;',
							title: lang['7_spravka_o_stoimosti_lecheniya'],
							hidden: ! getRegionNick().inlist(['perm', 'kz', 'ufa']),
							items: [{
								bodyStyle: 'padding-top: 0.5em;',
								border: false,
								height: 90,
								layout: 'form',
								region: 'center',
								items: [{
									fieldLabel: lang['data_vyidachi_spravki_otkaza'],
									tabIndex: this.tabindex + 51,
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
									tabIndex: this.tabindex + 52,
									hiddenName: 'EvnCostPrint_IsNoPrint',
									width: 60,
									xtype: 'swyesnocombo'
								}]
							}]
						})],
				reader: new Ext.data.JsonReader({
					success: Ext.emptyFn
				}, [
					{name: 'accessType'},
					{name: 'canCreateVizit'},
					{name: 'EvnPL_IsPaid'},
					{name: 'EvnPL_IsTransit'},
					{name: 'EvnPL_IndexRep'},
					{name: 'EvnPL_IndexRepInReg'},
					{name: 'DirectClass_id'},
					{name: 'EvnDirection_id'},
					{name: 'EvnDirection_IsAuto'},
					{name: 'EvnDirection_IsReceive'},
					{name: 'Lpu_fid'},
					{name: 'Org_did'},
					{name: 'LpuSection_did'},
					{name: 'MedStaffFact_did'},
					{name: 'Diag_did'},
					{name: 'Diag_fid'},
					{name: 'Diag_preid'},
					{name: 'EvnDirection_Num'},
					{name: 'EvnDirection_setDate'},
					{name: 'DirectType_id'},
					{name: 'EvnPL_Complexity'},
					{name: 'EvnPL_id'},
					{name: 'EvnPL_lid'},
					{name: 'EvnPL_IsFinish'},
					{name: 'EvnPL_IsSurveyRefuse'},
					{name: 'EvnPL_isMseDirected'},
					{name: 'EvnPL_IsFirstTime'},
					{name: 'EvnPL_IsUnlaw'},
					{name: 'EvnPL_IsUnport'},
					{name: 'EvnPL_NumCard'},
					{name: 'EvnPL_IsCons'},
					{name: 'EvnPL_UKL'},
					{name: 'EvnPL_IsFirstDisable'},
					{name: 'PrivilegeType_id'},
					{name: 'Lpu_oid'},
					{name: 'LpuSection_oid'},
                    {name: 'Lpu_id'},
					{name: 'Person_id'},
					{name: 'PersonEvn_id'},
					{name: 'PrehospDirect_id'},
					{name: 'PrehospTrauma_id'},
					{name: 'ResultClass_id'},
					{name: 'InterruptLeaveType_id'},
					{name: 'Diag_lid'},
					{name: 'Diag_concid'},
					{name: 'ResultDeseaseType_id'},
                    {name: 'LeaveType_fedid'},
                    {name: 'ResultDeseaseType_fedid'},
					{name: 'MedicalCareKind_id'},
					{name: 'MedicalStatus_id'},
					{name: 'Server_id'},
					{name: 'CmpCallCard_id'},
					{name: 'EvnPL_setDate'},
					{name: 'EvnPL_disDate'},
					{name: 'EvnCostPrint_setDT'},
					{name: 'EvnCostPrint_Number'},
					{name: 'EvnCostPrint_IsNoPrint'},
					{name: 'MedicalCareBudgType_id'},
					{name: 'EvnPL_MedPersonalCode'}
				]),
				region: 'center',
				url: '/?c=EvnPL&m=saveEvnPL'
			})]
		});

		sw.Promed.swEvnPLEditWindow.superclass.initComponent.apply(this, arguments);

        this.findById('EvnPLEditForm').on('render', function(formPanel){
            formPanel.getForm().findField('ResultClass_id').on('change', function (combo, newValue) {
                var index = combo.getStore().findBy(function (rec) {
                    return (rec.get('ResultClass_id') == newValue);
                });
                combo.fireEvent('select', combo, combo.getStore().getAt(index));
            });
            formPanel.getForm().findField('ResultClass_id').on('select', function (combo, record) {
                var base_form = formPanel.getForm();
                if (getRegionNick() == 'khak') {
					base_form.findField('LeaveType_fedid').clearValue();
					base_form.findField('ResultDeseaseType_fedid').clearValue();
					return false;
				}
				if(!win.fo){
					win.calcFedLeaveType();
					win.calcFedResultDeseaseType();
				}
				sw.Promed.EvnPL.filterFedResultDeseaseType({
					fieldFedLeaveType: base_form.findField('LeaveType_fedid'),
					fieldFedResultDeseaseType: base_form.findField('ResultDeseaseType_fedid')
				});
            });
			formPanel.getForm().findField('LeaveType_fedid').on('change', function (combo, newValue) {
				 var base_form = formPanel.getForm();
                sw.Promed.EvnPL.filterFedResultDeseaseType({
					fieldFedLeaveType: base_form.findField('LeaveType_fedid'),
					fieldFedResultDeseaseType: base_form.findField('ResultDeseaseType_fedid')
				})
            });
			formPanel.getForm().findField('ResultDeseaseType_fedid').on('change', function (combo, newValue) {
				 var base_form = formPanel.getForm();
                sw.Promed.EvnPL.filterFedLeaveType({
					fieldFedLeaveType: base_form.findField('LeaveType_fedid'),
					fieldFedResultDeseaseType: base_form.findField('ResultDeseaseType_fedid')
				});
            });

            formPanel.getForm().findField('Lpu_oid').on('change', function (combo, newValue) {
                var index = combo.getStore().findBy(function (rec) {
                    return (rec.get('Lpu_oid') == newValue);
                });
                combo.fireEvent('select', combo, combo.getStore().getAt(index));
            });
            formPanel.getForm().findField('LpuSection_oid').on('change', function (combo, newValue) {
                var index = combo.getStore().findBy(function (rec) {
                    return (rec.get('LpuSection_oid') == newValue);
                });
                combo.fireEvent('select', combo, combo.getStore().getAt(index));
            });
        });
	},
	keys: [{
		alt: true,
		fn: function(inp, e) {
			var current_window = Ext.getCmp('EvnPLEditWindow');

			switch ( e.getKey() ) {
				case Ext.EventObject.C:
					current_window.doSave({
						ignoreDiagCountCheck: false,
						ignoreEvnVizitPLCountCheck: false
					});
				break;

				case Ext.EventObject.G:
					current_window.printEvnPL();
				break;

				case Ext.EventObject.J:
					current_window.onCancelAction();
				break;

				case Ext.EventObject.NUM_ONE:
				case Ext.EventObject.ONE:
					current_window.findById('EPLEF_DirectInfoPanel').toggleCollapse();
				break;

				case Ext.EventObject.NUM_TWO:
				case Ext.EventObject.TWO:
					current_window.findById('EPLEF_EvnVizitPLPanel').toggleCollapse();
				break;

				case Ext.EventObject.NUM_THREE:
				case Ext.EventObject.THREE:
					current_window.findById('EPLEF_EvnUslugaPanel').toggleCollapse();
				break;

				case Ext.EventObject.FOUR:
				case Ext.EventObject.NUM_FOUR:
					current_window.findById('EPLEF_EvnStickPanel').toggleCollapse();
				break;

				case Ext.EventObject.FIVE:
				case Ext.EventObject.NUM_FIVE:
					current_window.findById('EPLEF_ResultPanel').toggleCollapse();
				break;

				case Ext.EventObject.NUM_SIX:
				case Ext.EventObject.SIX:
					current_window.findById('EPLEF_EvnDrugPanel').toggleCollapse();
				break;

			}
		},
		key: [
			Ext.EventObject.C,
			Ext.EventObject.G,
			Ext.EventObject.FOUR,
			Ext.EventObject.FIVE,
			Ext.EventObject.J,
			Ext.EventObject.NUM_FOUR,
			Ext.EventObject.NUM_FIVE,
			Ext.EventObject.NUM_ONE,
			Ext.EventObject.NUM_SIX,
			Ext.EventObject.NUM_TWO,
			Ext.EventObject.NUM_THREE,
			Ext.EventObject.ONE,
			Ext.EventObject.SIX,
			Ext.EventObject.TWO,
			Ext.EventObject.THREE
		],
		stopEvent: true
	}, {
		alt: false,
		fn: function(inp, e) {
			var current_window = Ext.getCmp('EvnPLEditWindow');

			switch ( e.getKey() ) {
				case Ext.EventObject.F6:
					current_window.findById('EPLEF_PersonInformationFrame').panelButtonClick(1);
				break;

				case Ext.EventObject.F10:
					current_window.findById('EPLEF_PersonInformationFrame').panelButtonClick(2);
				break;

				case Ext.EventObject.F11:
					current_window.findById('EPLEF_PersonInformationFrame').panelButtonClick(3);
				break;

				case Ext.EventObject.F12:
					if ( e.ctrlKey == true ) {
						current_window.findById('EPLEF_PersonInformationFrame').panelButtonClick(5);
					}
					else {
						current_window.findById('EPLEF_PersonInformationFrame').panelButtonClick(4);
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
	layout: 'border',
	listeners: {
		'hide': function(win) {
			win.onHide();
		},
		'maximize': function(win) {
			win.findById('EPLEF_DirectInfoPanel').doLayout();
			//win.findById('EPLEF_DirectPanel').doLayout();
			win.findById('EPLEF_EvnDrugPanel').doLayout();
			win.findById('EPLEF_CostPrintPanel').doLayout();
			win.findById('EPLEF_EvnStickPanel').doLayout();
			win.findById('EPLEF_EvnUslugaPanel').doLayout();
			win.findById('EPLEF_EvnVizitPLPanel').doLayout();
			win.findById('EPLEF_ResultPanel').doLayout();
		},
		'restore': function(win) {
			win.findById('EPLEF_DirectInfoPanel').doLayout();
			//win.findById('EPLEF_DirectPanel').doLayout();
			win.findById('EPLEF_EvnDrugPanel').doLayout();
			win.findById('EPLEF_CostPrintPanel').doLayout();
			win.findById('EPLEF_EvnStickPanel').doLayout();
			win.findById('EPLEF_EvnUslugaPanel').doLayout();
			win.findById('EPLEF_EvnVizitPLPanel').doLayout();
			win.findById('EPLEF_ResultPanel').doLayout();
		}
	},
	maximizable: true,
	minHeight: 550,
	minWidth: 800,
	modal: true,
	onCancelAction: function() {
		var base_form = this.findById('EvnPLEditForm').getForm();
		var evn_pl_id = base_form.findField('EvnPL_id').getValue();

		if ( evn_pl_id > 0 && this.action == 'add') {
			// удалить талон
			// закрыть окно после успешного удаления
			var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Удаление талона..."});
			loadMask.show();

			Ext.Ajax.request({
				callback: function(options, success, response) {
					loadMask.hide();

					if ( success ) {
						this.hide();
					}
					else {
						sw.swMsg.alert(lang['oshibka'], lang['pri_udalenii_talona_voznikli_oshibki']);
						return false;
					}
				}.createDelegate(this),
				params: {
					Evn_id: evn_pl_id
				},
				url: '/?c=Evn&m=deleteEvn'
			});
		}
		else {
			this.hide();
		}
	},
	onHide: Ext.emptyFn,
	// @todo: action implementation :)
	/*
	*
	* @changes:
	* 	- Нам нет необходимости в evnStickType, так как, данный функционал мы переносим в вышележащую форму.
	* 	- Удаляем соответсвующие проверки из функции.
	*
	* */
	openEvnStickEditWindow: function(action) {
		if ( action != 'add' && action != 'edit' && action != 'view' ) {
			return false;
		}

		var base_form = this.findById('EvnPLEditForm').getForm();
		var grid = this.findById('EPLEF_EvnStickGrid');

		// if ( this.action == 'view') {
		// 	if ( action == 'add') {
		// 		return false;
		// 	}
		// 	else if ( action == 'edit' ) {
		// 		action = 'view';
		// 	}
		// }

		if ( action == 'add' && base_form.findField('EvnPL_id').getValue() == 0 ) {
			this.doSave({
				ignoreDiagCountCheck: true,
				ignoreEvnVizitPLCountCheck: false,
				openChildWindow: function() {
					this.openEvnStickEditWindow(action);
				}.createDelegate(this),
				print: false
			});
			return false;
		}

		var formParams = {};
		var joborg_id = this.findById('EPLEF_PersonInformationFrame').getFieldValue('JobOrg_id');
		var params = {};
		var person_id = this.findById('EPLEF_PersonInformationFrame').getFieldValue('Person_id');
		var person_birthday = this.findById('EPLEF_PersonInformationFrame').getFieldValue('Person_Birthday');
		var person_firname = this.findById('EPLEF_PersonInformationFrame').getFieldValue('Person_Firname');
		var person_post = this.findById('EPLEF_PersonInformationFrame').getFieldValue('Person_Post');
		var person_secname = this.findById('EPLEF_PersonInformationFrame').getFieldValue('Person_Secname');
		var person_surname = this.findById('EPLEF_PersonInformationFrame').getFieldValue('Person_Surname');

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
				var grid_fields = [];
				var i = 0;

				grid.getStore().fields.eachKey(function(key, item) {
					grid_fields.push(key);
				});

				for ( i = 0; i < grid_fields.length; i++ ) {
					record.set(grid_fields[i], data.evnStickData[0][grid_fields[i]]);
				}

				record.commit();
			}
		}.createDelegate(this);
		params.JobOrg_id = joborg_id;
		params.parentClass = 'EvnPL';
		params.Person_id = base_form.findField('Person_id').getValue();
		params.PersonEvn_id = base_form.findField('PersonEvn_id').getValue();
		params.Person_Birthday = person_birthday;
		params.Person_Firname = person_firname;
		params.Person_Post = person_post;
		params.Person_Secname = person_secname;
		params.Person_Surname = person_surname;
		params.Server_id = base_form.findField('Server_id').getValue();
		params.UserMedStaffFact_id = this.workplace_params.UserMedStaffFact_id;
		params.UserLpuSection_id = this.workplace_params.UserLpuSection_id;

		formParams.EvnStick_mid = base_form.findField('EvnPL_id').getValue();

		if ( action == 'add' ) {
			var diag_code = '';
			var evn_vizit_pl_set_date = null;
			var evn_vizit_pl_dis_date = null;
			var evn_vizit_pl_store = this.findById('EPLEF_EvnVizitPLGrid').getStore();

			evn_vizit_pl_store.each(function(record) {
				if ( evn_vizit_pl_set_date == null || record.get('EvnVizitPL_setDate') <= evn_vizit_pl_set_date ) {
					evn_vizit_pl_set_date = record.get('EvnVizitPL_setDate');
				}
				if (evn_vizit_pl_dis_date == null ||  record.get('EvnVizitPL_disDate') >= evn_vizit_pl_dis_date) {
					evn_vizit_pl_dis_date = record.get('EvnVizitPL_disDate');
					if (!Ext.isEmpty(record.get('Diag_Code'))) {
						diag_code = record.get('Diag_Code');
					}
				}
			});

			if (getRegionNick() == 'kz' && (diag_code >= 'A15.0' && diag_code <= 'A19.9' || diag_code >= 'A30.1' && diag_code <= 'A30.2')) {
				params.isTubDiag = true;
			}

			formParams.EvnStick_pid = base_form.findField('EvnPL_id').getValue();
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
			params.archiveRecord = this.archiveRecord;

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
		if ( typeof action != 'string' || !action.inlist([ 'add', 'addOper', 'edit', 'view']) ) {
			return false;
		}
/*
		// Если Уфа, то добавление услуги с формы редактирования талона недоступно
		if ( action == 'add' && getGlobalOptions().region && getGlobalOptions().region.nick == 'ufa' ) {
			return false;
		}
*/
		var base_form = this.findById('EvnPLEditForm').getForm();
		var grid = this.findById('EPLEF_EvnUslugaGrid');

		if ( this.action == 'view' && !this.canCreateVizit) {
			if ( action == 'add' || action == 'addOper' ) {
				return false;
			}
			else if ( action == 'edit' ) {
				action = 'view';
			}
		}

		var params = {};

		params.action = action;
		params.callback = function(data) {
			if ( true || !data || !data.evnUslugaData ) {
				grid.getStore().load({
					params: {
						pid: base_form.findField('EvnPL_id').getValue()
					}
				});
				return false;
			}
            // логика ниже не годится, если создается пакет услуг

			var record = grid.getStore().getById(data.evnUslugaData.EvnUsluga_id);

			if ( !record ) {
				if ( grid.getStore().getCount() == 1 && !grid.getStore().getAt(0).get('EvnUsluga_id') ) {
					grid.getStore().removeAll();
				}

				grid.getStore().loadData([ data.evnUslugaData ], true);
			}
			else {
				var grid_fields = [];
				var i = 0;

				grid.getStore().fields.eachKey(function(key, item) {
					grid_fields.push(key);
				});

				for ( i = 0; i < grid_fields.length; i++ ) {
					record.set(grid_fields[i], data.evnUslugaData[grid_fields[i]]);
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
		params.parentClass = 'EvnPL';
		params.Person_id = base_form.findField('Person_id').getValue();
		params.Person_Birthday = this.findById('EPLEF_PersonInformationFrame').getFieldValue('Person_Birthday');
		params.Person_Firname = this.findById('EPLEF_PersonInformationFrame').getFieldValue('Person_Firname');
		params.Person_Secname = this.findById('EPLEF_PersonInformationFrame').getFieldValue('Person_Secname');
		params.Person_Surname = this.findById('EPLEF_PersonInformationFrame').getFieldValue('Person_Surname');

		// Собрать данные для ParentEvnCombo
		var parent_evn_combo_data = [];

		// Формируем parent_evn_combo_data
		var evn_vizit_pl_grid = this.findById('EPLEF_EvnVizitPLGrid');

		evn_vizit_pl_grid.getStore().each(function(record) {
			var temp_record = {};

			temp_record.Evn_id = record.get('EvnVizitPL_id');
			temp_record.Evn_Name = Ext.util.Format.date(record.get('EvnVizitPL_setDate'), 'd.m.Y') + ' / ' + record.get('LpuSection_Name') + ' / ' + record.get('MedPersonal_Fio');
			temp_record.Evn_setDate = record.get('EvnVizitPL_setDate');
			temp_record.Evn_setTime = record.get('EvnVizitPL_setTime');
			temp_record.MedStaffFact_id = record.get('MedStaffFact_id');
			temp_record.LpuSection_id = record.get('LpuSection_id');
			temp_record.LpuSectionProfile_id = record.get('LpuSectionProfile_id');
			temp_record.MedPersonal_id = record.get('MedPersonal_id');
			temp_record.ServiceType_SysNick = record.get('ServiceType_SysNick');
			temp_record.VizitType_SysNick = record.get('VizitType_SysNick');
			temp_record.Diag_id = record.get('Diag_id');
			temp_record.UslugaComplex_Code = record.get('UslugaComplex_Code');

			parent_evn_combo_data.push(temp_record);
		});

		if (getRegionNick() == 'perm' && base_form.findField('EvnPL_RepFlag').checked) {
			params.ignorePaidCheck = 1;
		}

		var getUslugaComplexDate = function(evn_usluga_id) {
			var lastSetDate = null;
			evn_vizit_pl_grid.getStore().each(function(record) {
				var setDate = record.get('EvnVizitPL_setDate');
				if (!lastSetDate || lastSetDate < setDate) {
					lastSetDate = setDate;
				}
			});
			grid.getStore().each(function(record) {
				if (Ext.isEmpty(evn_usluga_id) || record.get('EvnUsluga_id') != evn_usluga_id) {
					var setDate = record.get('EvnUsluga_setDate');
					if (!lastSetDate || lastSetDate < setDate) {
						lastSetDate = setDate;
					}
				}
			});
			return lastSetDate?Ext.util.Format.date(lastSetDate, 'd.m.Y'):null;
		};

		switch ( action ) {
			case 'add':
			case 'addOper':
				if ( base_form.findField('EvnPL_id').getValue() == 0 ) {
					this.doSave({
						ignoreDiagCountCheck: true,
						ignoreEvnVizitPLCountCheck: false,
						openChildWindow: function() {
							this.openEvnUslugaEditWindow(action);
						}.createDelegate(this),
						print: false
					});
					return false;
				}

				if (getRegionNick() == 'perm') {
					params.UslugaComplex_Date = getUslugaComplexDate();
				}

                params.action = 'add';
				params.formParams = {
					Person_id: base_form.findField('Person_id').getValue(),
					PersonEvn_id: base_form.findField('PersonEvn_id').getValue(),
					Server_id: base_form.findField('Server_id').getValue()
				};
				params.parentEvnComboData = parent_evn_combo_data;

				if ( action == 'addOper' ){
					getWnd('swEvnUslugaOperEditWindow').show(params);
				}
				else {
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

				if ( selected_record.get('accessType') != 'edit' ) {
					params.action = 'view';
				}

				params.archiveRecord = this.archiveRecord;

				var evn_usluga_id = selected_record.get('EvnUsluga_id');

				if (getRegionNick() == 'perm') {
					params.UslugaComplex_Date = getUslugaComplexDate(evn_usluga_id);
				}

				switch ( selected_record.get('EvnClass_SysNick') ) {
					case 'EvnUslugaCommon':
						params.formParams = {
							EvnUslugaCommon_id: evn_usluga_id
						};
						params.parentEvnComboData = parent_evn_combo_data;

						getWnd('swEvnUslugaEditWindow').show(params);
					break;

					case 'EvnUslugaOper':
						params.formParams = {
							EvnUslugaOper_id: evn_usluga_id
						};
						params.parentEvnComboData = parent_evn_combo_data;

						getWnd('swEvnUslugaOperEditWindow').show(params);
					break;

					case 'EvnUslugaPar':
						params.EvnUslugaPar_id = evn_usluga_id;

						getWnd('swEvnUslugaParEditWindow').show(params);
					break;

					default:
						return false;
					break;
				}
			break;
		}
	},
	openEvnVizitPLEditWindow: function(action) {
		if ( action != 'add' && action != 'edit' && action != 'view' ) {
			return false;
		}

		var win = this;
		var base_form = this.findById('EvnPLEditForm').getForm();
		var grid = this.findById('EPLEF_EvnVizitPLGrid');
		var usluga_grid = this.findById('EPLEF_EvnUslugaGrid');

		if ( this.action == 'view') {
			if ( action == 'add' && this.gridAccess == 'view' ) {
				sw.swMsg.alert(lang['soobschenie'], 'Добавление посещения невозможно.');
				return false;
			}
			else if ( action == 'edit' && this.gridAccess == 'view' ) {
				action = 'view';
			}
		}

		if ( action == 'add' && base_form.findField('EvnPL_IsFinish').getValue() == 2 && this.streamInput === false) {
			sw.swMsg.alert(lang['soobschenie'], lang['talon_zakryit_-_dobavlenie_posescheniya_nevozmojno']);
			return false;
		}

		if ( getWnd('swEvnVizitPLEditWindow').isVisible() ) {
			sw.swMsg.alert(lang['soobschenie'], lang['okno_redaktirovaniya_posescheniya_patsientom_polikliniki_uje_otkryito']);
			return false;
		}

		//Проверка на второе посещение НМП
		if ( action == 'add' && getGlobalOptions().region && getGlobalOptions().region.nick == 'buryatiya' ) {
			var allowAdd = true;

			grid.getStore().each(function(rec) {
				if ( rec.get('TreatmentClass_id') == 2 ) {
					allowAdd = false;
				}
			});

			if ( allowAdd == false ) {
				sw.swMsg.alert(langs('Сообщение'), langs('В рамках текущего ТАП есть посещение с видом обращения в неотложной форме по заболеванию. Добавление еще одного посещения невозможно.'));
				return false;
			}
		}

		// https://redmine.swan.perm.ru/issues/15258
		// Для Уфы проверяем наличие посещений с кодом профилактического посещения. Если такие посещения уже есть, то больше добавлять нельзя
		// Если имеются посещения по заболеванию, то добавлять можно только посещения по заболеваниям

		// Признак возможности добавлять только посещения по заболеваниям
		var allowConsulDiagnVizitOnly = false;
		var allowMorbusVizitCodesGroup88 = false;
		var allowMorbusVizitOnly = false;
		var allowNonMorbusVizitOnly = false;

		if ( action == 'add' && getGlobalOptions().region && getGlobalOptions().region.nick == 'ufa' ) {
			var allowAdd = true;
			var is871 = false;

			grid.getStore().each(function(rec) {
				if ( rec.get('UslugaComplex_Code') && rec.get('UslugaComplex_Code').length == 6 ) {
					if ( isOneVizitCode(rec.get('UslugaComplex_Code')) ) {
						allowAdd = false;
						is871 = isMorbusOneVizitCode(rec.get('UslugaComplex_Code'));
					} else if ( isMorbusMultyVizitCode(rec.get('UslugaComplex_Code')) ) {
						allowMorbusVizitOnly = true;
						allowMorbusVizitCodesGroup88 = isMorbusGroup88VizitCode(rec.get('UslugaComplex_Code'));
					} else {
						allowNonMorbusVizitOnly = true;
					}
				}
			});

			if ( allowAdd == false ) {
				sw.swMsg.alert(langs('Сообщение'), langs('Добавление посещения невозможно, т.к. в рамках текущего ТАП уже есть посещение с кодом ') + (is871 ? langs('однократного посещения по заболеванию') : langs('профилактического/консультативного посещения')));
				return false;
			}
		}

		// https://redmine.swan.perm.ru/issues/53050
		if (
			action == 'add' && getRegionNick() == 'pskov' && grid.getStore().getCount() > 0
			&& !Ext.isEmpty(grid.getStore().getAt(0).get('EvnVizitPL_id')) && Ext.isEmpty(grid.getStore().getAt(0).get('UslugaComplex_Code'))
		) {
			sw.swMsg.alert(lang['soobschenie'], lang['dobavlenie_posescheniya_nevozmojno_t_k_ne_ukazan_kod_posescheniya_v_pervom_poseschenii']);
			return false;
		}

        if ( action == 'add' && getRegionNick().inlist(['astra']) ) {
            var allowAdd = true;

            grid.getStore().each(function(rec) {
                if ( rec.get('VizitType_SysNick') ) {
                    if ( rec.get('VizitType_SysNick') == 'desease' ) {
                        allowMorbusVizitOnly = true;
					} else if ( rec.get('VizitType_SysNick') == 'ConsulDiagn' ) {
						allowConsulDiagnVizitOnly = true;
                    } else {
                        allowAdd = false;
                    }
                }
            });

            if ( allowAdd == false ) {
                sw.swMsg.alert(lang['soobschenie'], lang['dobavlenie_posescheniya_nevozmojno_t_k_v_ramkah_tekuschego_tap_uje_est_poseschenie']);
                return false;
            }
        }

        if ( action == 'add' && getRegionNick().inlist(['kareliya']) ) {
            var allowAdd = true;

            grid.getStore().each(function(rec) {
                if ( rec.get('VizitType_SysNick') ) {
                    if ( rec.get('VizitType_SysNick') == 'desease' ) {
                        allowMorbusVizitOnly = true;
                    } else if(rec.get('VizitType_SysNick') != 'consulspec'){
                        allowAdd = false;
                    }
                }
            });

            if ( allowAdd == false ) {
                sw.swMsg.alert(lang['soobschenie'], lang['dobavlenie_posescheniya_nevozmojno_t_k_v_ramkah_tekuschego_tap_uje_est_poseschenie']);
                return false;
            }
        }

		if ( action == 'add' && base_form.findField('EvnPL_id').getValue() == 0 ) {
			this.doSave({
				ignoreFormValidation: true,
				ignoreDiagCountCheck: true,
				ignoreEvnVizitPLCountCheck: true,
				openChildWindow: function() {
					this.openEvnVizitPLEditWindow(action);
				}.createDelegate(this),
				print: false
			});
			return false;
		}

		var formParams = {};
		var params = {};

		params.action = action;
		params.allowConsulDiagnVizitOnly = allowConsulDiagnVizitOnly;
		params.allowMorbusVizitCodesGroup88 = allowMorbusVizitCodesGroup88;
		params.allowMorbusVizitOnly = allowMorbusVizitOnly;
		params.allowNonMorbusVizitOnly = allowNonMorbusVizitOnly;
		params.callback = function(data) {
			if ( !data || !data.evnVizitPLData ) {
				return false;
			}

			if ( getGlobalOptions().region && getGlobalOptions().region.nick == 'ufa' ) {
				// https://redmine.swan.perm.ru/issues/15258
				// профилактических посещений автоматически устанавливаем признак законченности случая лечения "Да"
				if ( !Ext.isEmpty(data.evnVizitPLData[0].UslugaComplex_Code)
					&& isProphylaxisVizitOnly(data.evnVizitPLData[0].UslugaComplex_Code.toString())
				) {
					base_form.findField('EvnPL_IsFinish').setValue(2);
					base_form.findField('EvnPL_IsFinish').fireEvent('change', base_form.findField('EvnPL_IsFinish'), 2);
				}
			}

            grid.getStore().load({
                params: {
                    EvnPL_id: win.findById('EvnPLEditForm').getForm().findField('EvnPL_id').getValue()
                },
                callback: function(records, options, success) {
                    if ( success ) {
                        grid.getView().focusRow(0);
                        grid.getSelectionModel().selectFirstRow();

						var firstEvnVizitPLData = null;
						var lastEvnVizitPLData = null;
						this.findById('EPLEF_EvnVizitPLGrid').getStore().each(function(record) {
							if ( Ext.isEmpty(firstEvnVizitPLData) || (!Ext.isEmpty(record.get('EvnVizitPL_setDate')) && record.get('EvnVizitPL_setDate') <= firstEvnVizitPLData.EvnVizitPL_setDate) ) {
								firstEvnVizitPLData = record.data;
							}

							if ( Ext.isEmpty(lastEvnVizitPLData) || (!Ext.isEmpty(record.get('EvnVizitPL_setDate')) && record.get('EvnVizitPL_setDate') >= lastEvnVizitPLData.EvnVizitPL_setDate) ) {
								lastEvnVizitPLData = record.data;
							}
						});

						if (firstEvnVizitPLData.EvnVizitPL_setDate == data.evnVizitPLData[0].EvnVizitPL_setDate && Ext.util.Format.date(firstEvnVizitPLData.EvnVizitPL_setDate,'d.m.Y') != base_form.findField('EvnPL_setDate').getValue()) {
							base_form.findField('EvnPL_setDate').setValue(Ext.util.Format.date(firstEvnVizitPLData.EvnVizitPL_setDate,'d.m.Y'));
							// Перезагружаем информационный фрейм
							this.findById('EPLEF_PersonInformationFrame').load({
								callback: function() {
									this.findById('EPLEF_PersonInformationFrame').setPersonTitle();
								}.createDelegate(this),
								// loadFromDB: true,
								Person_id: base_form.findField('Person_id').getValue(),
								Server_id: base_form.findField('Server_id').getValue(),
								Evn_setDT: base_form.findField('EvnPL_setDate').getValue()
							});
						}

						if (!Ext.isEmpty(data.evnVizitPLData[0].EvnVizitPL_id)) {
							this.findById('EPLEF_EvnUslugaGrid').getStore().load({
								params: {
									//pid: data.evnVizitPLData[0].EvnVizitPL_id
									pid: this.findById('EvnPLEditForm').getForm().findField('EvnPL_id').getValue()
								}
							});
						}

						if ( !getRegionNick().inlist(['kareliya', 'ufa']) ) {
							var diag_l_combo = base_form.findField('Diag_lid');
							if (/*Ext.isEmpty(diag_l_combo.getValue()) &&*/ lastEvnVizitPLData && !Ext.isEmpty(lastEvnVizitPLData.Diag_id)) {
								diag_l_combo.getStore().load({
									params: {
										where: "where DiagLevel_id = 4 and Diag_id = " + lastEvnVizitPLData.Diag_id
									},
									callback: function() {
										diag_l_combo.setValue(lastEvnVizitPLData.Diag_id);
										this.setDiagConcComboVisible();
									}.createDelegate(this)
								});
							}
						}

						// если профиль последнего посещения - "57. Общая врачебная практика (семейная медицина)", проставлять значение 8 - общеврачебная практика, Для остальных 1 - амбулаторный прием
						if (getRegionNick() == 'kareliya' && Ext.isEmpty(base_form.findField('MedicalCareKind_id').getValue())) {
							if (lastEvnVizitPLData && lastEvnVizitPLData.LpuSectionProfile_Code == '57') {
								base_form.findField('MedicalCareKind_id').setFieldValue('MedicalCareKind_Code', 8);
							} else {
								base_form.findField('MedicalCareKind_id').setFieldValue('MedicalCareKind_Code', 1);
							}
						}

						this.checkIsAssignNasel();
						this.refreshFieldsVisibility(['MedicalCareBudgType_id']);
                    }
				}.createDelegate(this)
            });

			/*var index = grid.getStore().findBy(function(rec) {
				return (rec.get('EvnVizitPL_id') == data.evnVizitPLData[0].EvnVizitPL_id);
			});
			var record = grid.getStore().getAt(index);

			if ( !record ) {
				if ( grid.getStore().getCount() == 1 && !grid.getStore().getAt(0).get('EvnVizitPL_id') ) {
					grid.getStore().removeAll();
				}

				grid.getStore().loadData(data.evnVizitPLData, true);
			}
			else {
				var grid_fields = new Array();

				grid.getStore().reload();
				grid.getStore().fields.eachKey(function(key, item) {
					grid_fields.push(key);
				});

				for ( var i = 0; i < grid_fields.length; i++ ) {
					record.set(grid_fields[i], data.evnVizitPLData[0][grid_fields[i]]);
				}

				record.commit();
			}*/
		}.createDelegate(this);
		params.from = this.params.from;
		params.onHide = function(options) {
			if ( this.findById('EPLEF_EvnUslugaPanel').isLoaded === true && (options.EvnUslugaGridIsModified === true||getRegionNick()=='perm') ) {
				this.findById('EPLEF_EvnUslugaGrid').getStore().load({
					params: {
						pid: base_form.findField('EvnPL_id').getValue()
					},
					callback: function(records, options, success) {
						if ( success ) {
							//win.checkAbort()
						}
					}
				});
			}

			grid.getView().focusRow(0);
			grid.getSelectionModel().selectFirstRow();
		}.createDelegate(this);
		params.Person_id = base_form.findField('Person_id').getValue();
		params.Person_Birthday = this.findById('EPLEF_PersonInformationFrame').getFieldValue('Person_Birthday');
		params.Person_Firname = this.findById('EPLEF_PersonInformationFrame').getFieldValue('Person_Firname');
		params.Person_Secname = this.findById('EPLEF_PersonInformationFrame').getFieldValue('Person_Secname');
		params.Person_Surname = this.findById('EPLEF_PersonInformationFrame').getFieldValue('Person_Surname');
		params.TimetableGraf_id = this.params.TimetableGraf_id;
		params.OmsSprTerr_Code = this.findById('EPLEF_PersonInformationFrame').getFieldValue('OmsSprTerr_Code');
		params.Sex_Code = this.findById('EPLEF_PersonInformationFrame').getFieldValue('Sex_Code');
		params.UserMedStaffFact_id = this.workplace_params.UserMedStaffFact_id;
		params.UserLpuSection_id = this.workplace_params.UserLpuSection_id;
		params.streamInput = this.streamInput; // Признак добавления посещения с формы поточного ввода
		var lastEvnVizitPLData = null;
		grid.getStore().each(function(record) {
			if ( typeof setDate != 'object' || record.get('EvnVizitPL_setDate') > setDate ) {
				lastEvnVizitPLData = record.get('EvnVizitPL_setDate');
			}
		});
		params.lastEvnVizitPLData = lastEvnVizitPLData;

		if ( action == 'add' ) {
			if ( grid.getStore().getCount() == 0 || !grid.getStore().getAt(0).get('EvnVizitPL_id') ) {
				formParams = this.params;
				params.loadLastData = false;

				// Если заполнен диагноз направившего учреждения...
				if ( base_form.findField('Diag_did').getValue() ) {
					formParams.Diag_id = base_form.findField('Diag_did').getValue();
				}
			}
			else if ( this.streamInput == true ) {
				formParams = this.params;
				params.loadLastData = true;
			}
			else {
				params.loadLastData = true;
			}

			formParams.EvnPL_id = base_form.findField('EvnPL_id').getValue();
			formParams.EvnVizitPL_id = 0;
			formParams.Person_id = base_form.findField('Person_id').getValue();
			formParams.PersonEvn_id = base_form.findField('PersonEvn_id').getValue();
			formParams.Server_id = base_form.findField('Server_id').getValue();

		}
		else {
			var selected_record = grid.getSelectionModel().getSelected();

			if ( !selected_record || !selected_record.get('EvnVizitPL_id') ) {
				return false;
			}

			if (
				selected_record.get('accessType') != 'edit'
				|| (
					!Ext.isEmpty(getGlobalOptions().medpersonal_id)
					&& !Ext.isEmpty(selected_record.get('MedPersonal_id'))
					&& userHasWorkPlaceAtLpuSection(selected_record.get('LpuSection_id')) == false
					&& getGlobalOptions().isMedStatUser != true
					&& isSuperAdmin() != true
				)
				|| selected_record.get('EvnVizitPL_IsSigned') != 1
			) {
				params.action = 'view';
			}

			formParams.EvnVizitPL_id = selected_record.get('EvnVizitPL_id');
			formParams.Person_id = selected_record.get('Person_id');
			formParams.Server_id = selected_record.get('Server_id');
		}

		formParams.ResultClass_id = base_form.findField('ResultClass_id').getValue();
		formParams.EvnPL_IsFinish = base_form.findField('EvnPL_IsFinish').getValue();

		params.OtherVizitList = getStoreRecords(grid.getStore(), {
			convertDateFields: true,
			exceptionRecordIds: [formParams.EvnVizitPL_id]
		});
		params.OtherUslugaList = getStoreRecords(usluga_grid.getStore(), {
			convertDateFields: true
		}).filter(function(item) {
			return item.EvnUsluga_pid != formParams.EvnVizitPL_id;
		});

		formParams.EvnPL_lid = base_form.findField('EvnPL_lid').getValue();

		params.formParams = formParams;
		params.ServiceType_SysNick = this.params.ServiceType_SysNick;
		params.VizitType_SysNick = this.params.VizitType_SysNick;
		params.archiveRecord = this.archiveRecord;
		params.EvnPSInfo = this.EvnPSInfo;
		params.UslugaComplex_uid = this.params.UslugaComplex_uid;

		getWnd('swEvnVizitPLEditWindow').show(params);
	},
	openEvnDrugEditWindow: function(action) {
        if ( this.findById('EPLEF_EvnDrugPanel').hidden || this.findById('EPLEF_EvnDrugPanel').collapsed ) {
            return false;
        }

        if ( action != 'add' && action != 'edit' && action != 'view' ) {
            return false;
        }

        var base_form = this.findById('EvnPLEditForm').getForm();
        var grid = this.findById('EPLEF_EvnDrugGrid');

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

        if ( action == 'add' && base_form.findField('EvnPL_id').getValue() == 0 ) {
            this.doSave({
                openChildWindow: function() {
                    this.openEvnDrugEditWindow(action);
                }.createDelegate(this),
                print: false
            });
            return false;
        }

        var parent_evn_combo_data = [];


		this.findById('EPLEF_EvnVizitPLGrid').getStore().each(function(record) {
			parent_evn_combo_data.push({
				Evn_id : record.get('EvnVizitPL_id'),
				Evn_Name : Ext.util.Format.date(record.get('EvnVizitPL_setDate'), 'd.m.Y') + ' / ' + record.get('LpuSection_Name') + ' / ' + record.get('MedPersonal_Fio'),
				Evn_setDate : record.get('EvnVizitPL_setDate'),
				Evn_setTime : record.get('EvnVizitPL_setTime'),
				MedStaffFact_id : record.get('MedStaffFact_id'),
				Lpu_id: record.get('Lpu_id'),
				LpuSection_id : record.get('LpuSection_id'),
				MedPersonal_id : record.get('MedPersonal_id')
			})
		});

        var formParams = {};
        var params = {};
        var person_id = this.findById('EPLEF_PersonInformationFrame').getFieldValue('Person_id');
        var person_birthday = this.findById('EPLEF_PersonInformationFrame').getFieldValue('Person_Birthday');
        var person_firname = this.findById('EPLEF_PersonInformationFrame').getFieldValue('Person_Firname');
        var person_secname = this.findById('EPLEF_PersonInformationFrame').getFieldValue('Person_Secname');
        var person_surname = this.findById('EPLEF_PersonInformationFrame').getFieldValue('Person_Surname');

		params.type = 'PL';
        params.action = action;
        params.parentEvnComboData = parent_evn_combo_data;
        params.callback = function(data) {
            if ( !data || !data.evnDrugData ) {
                return false;
            }
            var grid = this.findById('EPLEF_EvnDrugGrid');
            var record = grid.getStore().getById(data.evnDrugData.EvnDrug_id);

            if ( !record ) {
                if ( grid.getStore().getCount() == 1 && !grid.getStore().getAt(0).get('EvnDrug_id') ) {
                    grid.getStore().removeAll();
                }
                grid.getStore().loadData([data.evnDrugData], true);
            }
            else {
                //
                var grid_fields = [];
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
            formParams.EvnDrug_pid = base_form.findField('EvnPL_id').getValue();

        }
        else {
            var selected_record = grid.getSelectionModel().getSelected();

            if ( !selected_record || !selected_record.get('EvnDrug_id') ) {
                return false;
            }

            formParams.EvnDrug_id = selected_record.get('EvnDrug_id');
			formParams.EvnDrug_rid = base_form.findField('EvnPL_id').getValue();
        }

        params.formParams = formParams;
		params.archiveRecord = this.archiveRecord;

        getWnd(getEvnDrugEditWindowName()).show(params);
    },
	openSpecificEditWindow: function(action) {
		var base_form = this.findById('EvnPLEditForm').getForm();

		if ( this.action == 'view') {
			if ( action == 'add') {
				return false;
			}
			else if ( action == 'edit' ) {
				action = 'view';
			}
		}

		if ( action == 'add' && base_form.findField('EvnPL_id').getValue() == 0 ) {
			this.doSave({
				ignoreDiagCountCheck: true,
				ignoreEvnVizitPLCountCheck: true,
				openChildWindow: function() {
					this.openSpecificEditWindow(action);
				}.createDelegate(this)
			});
			return false;
		}

		var params = {};

		var person_id = base_form.findField('Person_id').getValue();
		var person_birthday = this.findById('EPLEF_PersonInformationFrame').getFieldValue('Person_Birthday');
		var person_firname = this.findById('EPLEF_PersonInformationFrame').getFieldValue('Person_Firname');
		var person_secname = this.findById('EPLEF_PersonInformationFrame').getFieldValue('Person_Secname');
		var person_surname = this.findById('EPLEF_PersonInformationFrame').getFieldValue('Person_Surname');

		params.action = action;
		params.callback = function(data) {
			if ( !data || !data.EvnPLAbortData ) {
				return false;
			}
		}.createDelegate(this);
		params.onHide = Ext.emptyFn;
		params.Person_id = person_id;
		params.Person_Birthday = person_birthday;
		params.Person_Firname = person_firname;
		params.Person_Secname = person_secname;
		params.Person_Surname = person_surname;

		if ( action == 'add' ) {
			var sex_id = this.findById('EPLEF_PersonInformationFrame').getFieldValue('Sex_id');
			var specificList = [];
			var is_z303_diag = false;

			params.formParams = {
				EvnPL_id: base_form.findField('EvnPL_id').getValue(),
				Person_id: base_form.findField('Person_id').getValue(),
				PersonEvn_id: base_form.findField('PersonEvn_id').getValue(),
				Server_id: base_form.findField('Server_id').getValue()
			};

			// Ищем диагноз Z30.3 в списке посещений
			this.findById('EPLEF_EvnVizitPLGrid').getStore().each(function(record) {
				if ( record.get('Diag_id') == 11034 ) {
					is_z303_diag = true;
				}
			});

			if ( sex_id == 2 && Ext.globalOptions.specifics.abort_data === true && is_z303_diag === true ) {
				specificList.push(1);
			}

			if ( specificList.length == 0 ) {
				sw.swMsg.alert(lang['soobschenie'], lang['vvod_spetsifiki_nedostupen']);
				return false;
			}

			getWnd('swSpecificSetWindow').show({
				params: params,
				specificList: specificList
			});
		}
	},
	params: {
		EvnVizitPL_setDate: null,
		LpuSection_id: null,
		MedPersonal_id: null,
		MedPersonal_sid: null,
		PayType_id: null,
		ServiceType_id: null,
		VizitType_id: null,
		UslugaComplex_uid: null,
		RiskLevel_id:null
	},
	plain: true,
	printEvnPL: function() {
		if ( 'add' == this.action || 'edit' == this.action ) {
			this.doSave({
				ignoreDiagCountCheck: false,
				ignoreEvnVizitPLCountCheck: false,
				print: true
			});
		}
		else if ( 'view' == this.action ) {
			var evn_pl_id = this.findById('EvnPLEditForm').getForm().findField('EvnPL_id').getValue();
            printEvnPL({
                type: 'EvnPL',
                EvnPL_id: evn_pl_id
            });
        }
	},
	resizable: true,
	checkIsAssignNasel: function() {
		if (getRegionNick() == 'astra') {
			var win = this;
			var base_form = this.findById('EvnPLEditForm').getForm();
			var checked = base_form.findField('EvnPL_IsCons').checked;
			base_form.findField('EvnPL_IsCons').hide();
			base_form.findField('EvnPL_IsCons').setValue(false);

			var firstEvnVizit = null;
			this.findById('EPLEF_EvnVizitPLGrid').getStore().each(function (record) {
				if (Ext.isEmpty(firstEvnVizit) || (!Ext.isEmpty(record.get('EvnVizitPL_setDate')) && record.get('EvnVizitPL_setDate') <= firstEvnVizit.get('EvnVizitPL_setDate'))) {
					firstEvnVizit = record;
				}
			});

			// проверка имеет ли МО приписное население
			Ext.Ajax.request({
				failure: function (response, options) {
					sw.swMsg.alert(lang['oshibka'], lang['pri_poluchenii_znacheniya_priznaka_mo_imeet_pripisnoe_naselenie_voznikli_oshibki']);
				},
				params: {
					Person_id: base_form.findField('Person_id').getValue(),
					Lpu_id: getGlobalOptions().lpu_id,
					setDate: firstEvnVizit?firstEvnVizit.get('EvnVizitPL_setDate').format('d.m.Y'):null
				},
				success: function (response, options) {
					var response_obj = Ext.util.JSON.decode(response.responseText);
					if (response_obj.PasportMO_IsAssignNasel == 2 || response_obj.hasConsPriemVolume == 2) {
						base_form.findField('EvnPL_IsCons').show();
						if (checked) {
							base_form.findField('EvnPL_IsCons').setValue(true);
						}
					}
				},
				url: '/?c=EvnPL&m=checkIsAssignNasel'
			});
		}
	},
	checkForCostPrintPanel: function() {
		var base_form = this.findById('EvnPLEditForm').getForm();

		this.findById('EPLEF_CostPrintPanel').hide();
		base_form.findField('EvnCostPrint_Number').setContainerVisible(getRegionNick() == 'khak');
		base_form.findField('EvnCostPrint_setDT').setAllowBlank(true);
		base_form.findField('EvnCostPrint_IsNoPrint').setAllowBlank(true);

		// если справка уже печаталась и случай закрыт, отображаем раздел с данными справки
		if (base_form.findField('EvnPL_IsFinish').getValue() == 2 && !Ext.isEmpty(base_form.findField('EvnCostPrint_setDT').getValue()) && getRegionNick().inlist(['perm', 'kz', 'ufa'])) {
			this.findById('EPLEF_CostPrintPanel').show();
			// поля обязтаельные
			base_form.findField('EvnCostPrint_setDT').setAllowBlank(false);
			base_form.findField('EvnCostPrint_IsNoPrint').setAllowBlank(false);
		}
	},
	calcFedLeaveType: function() {
		var base_form = this.findById('EvnPLEditForm').getForm();
		var LeaveType_fedid = base_form.findField('LeaveType_fedid');
		if (getRegionNick() == 'khak') LeaveType_fedid = null;
		var lastEvnVizitPLDate;
		this.findById('EPLEF_EvnVizitPLGrid').getStore().each(function(record) {
			if ( Ext.isEmpty(lastEvnVizitPLDate) || (!Ext.isEmpty(record.get('EvnVizitPL_setDate')) && record.get('EvnVizitPL_setDate') >= lastEvnVizitPLDate) ) {
				lastEvnVizitPLDate = record.get('EvnVizitPL_setDate');
			}
		});
		sw.Promed.EvnPL.calcFedLeaveType({
			is2016: Ext.isEmpty(lastEvnVizitPLDate) || lastEvnVizitPLDate >= sw.Promed.EvnPL.getDateX2016(),
			disableToogleContainer: false,
			InterruptLeaveType_id: base_form.findField('InterruptLeaveType_id').getValue(),
			ResultClass_id: base_form.findField('ResultClass_id').getValue(),
			LeaveType_fedid: base_form.findField('ResultClass_id').getFieldValue('LeaveType_fedid'),
			ResultClass_Code: base_form.findField('ResultClass_id').getFieldValue('ResultClass_Code'),
			DirectType_Code: base_form.findField('DirectType_id').getFieldValue('DirectType_Code'),
			DirectClass_Code: base_form.findField('DirectClass_id').getFieldValue('DirectClass_Code'),
			IsFinish: base_form.findField('EvnPL_IsFinish').getValue(),
			fieldFedLeaveType: LeaveType_fedid
		});
	},
	calcFedResultDeseaseType: function() {
		var base_form = this.findById('EvnPLEditForm').getForm();
		var FedResultDeseaseType = base_form.findField('ResultDeseaseType_fedid');
		if (getRegionNick() == 'khak') FedResultDeseaseType = null;
		var lastEvnVizitPLDate;
		this.findById('EPLEF_EvnVizitPLGrid').getStore().each(function(record) {
			if ( Ext.isEmpty(lastEvnVizitPLDate) || (!Ext.isEmpty(record.get('EvnVizitPL_setDate')) && record.get('EvnVizitPL_setDate') >= lastEvnVizitPLDate) ) {
				lastEvnVizitPLDate = record.get('EvnVizitPL_setDate');
			}
		});
		sw.Promed.EvnPL.calcFedResultDeseaseType({
			is2016: (Ext.isEmpty(lastEvnVizitPLDate) || lastEvnVizitPLDate >= sw.Promed.EvnPL.getDateX2016()) && (getRegionNick() != 'khak' || base_form.findField('EvnPL_IsFinish').getValue() == 2),
			disableToogleContainer: false,
			InterruptLeaveType_id: base_form.findField('InterruptLeaveType_id').getValue(),
			DirectType_Code: base_form.findField('DirectType_id').getFieldValue('DirectType_Code'),
			ResultClass_Code: base_form.findField('ResultClass_id').getFieldValue('ResultClass_Code') || null,
			IsFinish: base_form.findField('EvnPL_IsFinish').getValue(),
			fieldFedResultDeseaseType: FedResultDeseaseType
		});
	},
	setDiagFidAndLid: function() {
		// автоматически проставляем предварительный и заключительный диагнозы на основе первого и последнего посещения
		if ( !getRegionNick().inlist(['perm', 'astra', 'ufa', 'krym', 'pskov', 'kareliya']) ) {
			return false;
		}

		var base_form = this.findById('EvnPLEditForm').getForm();
		if (base_form.findField('EvnPL_IsFinish').getValue() != 2) {
			base_form.findField('Diag_fid').clearValue(); // предварительный
			base_form.findField('Diag_lid').clearValue(); // заключительный
			this.setDiagConcComboVisible();
			return true;
		}

		var firstEvnVizit = null;
		var lastEvnVizit = null;
		this.findById('EPLEF_EvnVizitPLGrid').getStore().each(function (record) {
			if (Ext.isEmpty(firstEvnVizit) || (!Ext.isEmpty(record.get('EvnVizitPL_setDate')) && record.get('EvnVizitPL_setDate') <= firstEvnVizit.get('EvnVizitPL_setDate'))) {
				firstEvnVizit = record;
			}
			if (Ext.isEmpty(lastEvnVizit) || (!Ext.isEmpty(record.get('EvnVizitPL_setDate')) && record.get('EvnVizitPL_setDate') >= lastEvnVizit.get('EvnVizitPL_setDate'))) {
				lastEvnVizit = record;
			}
		});

		if (!Ext.isEmpty(firstEvnVizit) && !Ext.isEmpty(lastEvnVizit)) {
			if (firstEvnVizit.get('Diag_id') && Ext.isEmpty(base_form.findField('Diag_fid').getValue())) {
				base_form.findField('Diag_fid').getStore().load({
					callback: function () {
						base_form.findField('Diag_fid').setValue(firstEvnVizit.get('Diag_id'));
					},
					params: {where: "where DiagLevel_id = 4 and Diag_id = " + firstEvnVizit.get('Diag_id')}
				});
			}

			if (lastEvnVizit.get('Diag_id') && Ext.isEmpty(base_form.findField('Diag_lid').getValue())) {
				base_form.findField('Diag_lid').getStore().load({
					callback: function () {
						base_form.findField('Diag_lid').setValue(lastEvnVizit.get('Diag_id'));
						this.setDiagConcComboVisible();
					}.createDelegate(this),
					params: {where: "where DiagLevel_id = 4 and Diag_id = " + lastEvnVizit.get('Diag_id')}
				});
			}
		}
	},
	onEnableEdit: function() {
		// поля предварит. диагноз и поле заключ. диагноз для Уфы недоступны для редактирования
		var base_form = this.findById('EvnPLEditForm').getForm();
		if (getRegionNick() == 'ufa') {
			base_form.findField('Diag_fid').disable(); // предварительный
			base_form.findField('Diag_lid').disable(); // заключительный
		}
	},
	checkLpuPeriodOMS: function(org_id, date, callback) {
		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Проверка периода ОМС..."});
		loadMask.show();

		Ext.Ajax.request({
			url: '/?c=LpuPassport&m=hasLpuPeriodOMS',
			params: {Org_oid: org_id, Date: date},
			success: function(response) {
				loadMask.hide();
				var response_obj = Ext.util.JSON.decode(response.responseText);

				if (response_obj && response_obj.success) {
					callback(response_obj.hasLpuPeriodOMS);
				}
			},
			failure: function() {
				loadMask.hide();
			}
		});
	},
	delDocsView: false,
	show: function() {
		sw.Promed.swEvnPLEditWindow.superclass.show.apply(this, arguments);

		if (getGlobalOptions().lpu_id == 10011165) {
			this.findById('EPLEF_KDKBFields').setVisible(true);
		}
		else {
			this.findById('EPLEF_KDKBFields').setVisible(false);
		}

		if (this.firstRun == true) {
			this.findById('EPLEF_EvnStickPanel').collapse();
			this.findById('EPLEF_EvnUslugaPanel').collapse();
			this.findById('EPLEF_EvnDrugPanel').collapse();
			this.findById('EPLEF_CostPrintPanel').collapse();
			this.firstRun = false;
		}
		/*if(getRegionNick()=='perm'){
		 this.findById('EPLEF_EvnUslugaPanel').expand();
		 }*/
		this.panelEvnDirectionAll.onReset();
		this.panelEvnDirectionAll.useCase = 'choose_for_evnpl';
		this.evnStickAction = null;

		this.restore();
		this.center();
		this.maximize();

		var base_form = this.findById('EvnPLEditForm').getForm(),
			_this = this;
		base_form.reset();

		if (getRegionNick() == 'ekb') {
			base_form.findField('PrehospDirect_id').addListener('select', function(combo, record, index) {
				base_form.findField('MedStaffFact_did').setAllowBlank(index != 1);
				base_form.findField('EvnPL_MedPersonalCode').setAllowBlank(index != 1 && index != 2);
				base_form.findField('EvnDirection_Num').setAllowBlank(index != 1 && index != 2);
				base_form.findField('EvnDirection_setDate').setAllowBlank(index != 1 && index != 2);
				base_form.findField('Org_did').setAllowBlank(index != 2);
				console.trace();
				console.log(base_form.findField('EvnDirection_Num'))
			});

			base_form.findField('MedStaffFact_did').addListener('select', function(combo) {
				var MedPCode = base_form.findField('EvnPL_MedPersonalCode');
				if(combo.getValue()) {
					MedPCode.setValue(combo.getFieldValue('MedPersonal_DloCode'));
				} else MedPCode.setValue('');
			})
		}

		this.checkForCostPrintPanel();

		base_form.findField('Diag_lid').setContainerVisible(getRegionNick() != 'kareliya');
		base_form.findField('ResultDeseaseType_id').setContainerVisible(getRegionNick().inlist([/*'astra',*/'adygeya', 'vologda', 'buryatiya', 'kaluga', 'kareliya', 'krasnoyarsk', 'krym', 'ekb', 'penza', 'pskov', 'yakutiya', 'yaroslavl']));
		base_form.findField('EvnPL_MedPersonalCode').setContainerVisible(getRegionNick() == 'ekb');

		if ( getRegionNick().inlist(['krasnoyarsk','adygeya','yakutiya','yaroslavl']) ) {
			base_form.findField('ResultDeseaseType_id').getStore().filterBy(function(rec) {
				return (!Ext.isEmpty(rec.get('ResultDeseaseType_Code')) && rec.get('ResultDeseaseType_Code').toString().substr(0, 1) == '3');
			});
		}

		//base_form.findField('ResultDeseaseType_fedid').getStore().lastQuery = '';
		this.action = 'add';
		this.onPersonChange = Ext.emptyFn;
		this.callback = Ext.emptyFn;
		this.gridAccess = 'view';
		this.formStatus = 'edit';
		this.onHide = Ext.emptyFn;
		this.PersonEvn_id = null;
		this.streamInput = false;
		this.ignoreMotherCheck = false;
		this.DiagPreg = null;
		this.filterResultClassCombo();
		this.setMedicalStatusComboVisible();
		this.setDiagConcComboVisible();
		this.setInterruptLeaveTypeVisible();

		this.params.Diag_id = null;
		this.params.EvnVizitPL_setDate = null;
		this.params.EvnVizitPL_setTime = null;
		this.params.LpuSection_id = null;
		this.params.MedStaffFact_id = null;
		this.params.MedStaffFact_sid = null;
		this.params.MedPersonal_id = null;
		this.params.MedPersonal_sid = null;
		this.params.PayType_id = null;
		this.params.ServiceType_id = null;
		this.params.ServiceType_SysNick = null;
		this.params.VizitType_id = null;
		this.params.UslugaComplex_uid = null;
		this.params.RiskLevel_id = null;
		this.params.VizitType_SysNick = null;
		this.params.MedicalCareKind_id = null;
		this.fo = true;
		this.params.from = null;
		this.params.TimetableGraf_id = null;
		this.EvnPSInfo = null;

		this.workplace_params = {};
		this.workplace_params.UserMedStaffFact_id = getGlobalOptions().CurMedStaffFact_id || null;
		this.workplace_params.UserLpuSection_id = getGlobalOptions().CurLpuSection_id || null;

		base_form.findField('EvnDirection_Num').disable();
		base_form.findField('EvnDirection_setDate').disable();
		base_form.findField('EvnPL_IsUnlaw').disable();
		base_form.findField('Lpu_oid').disable();
		base_form.findField('LpuSection_did').disable();
		base_form.findField('MedStaffFact_did').disable();
		base_form.findField('LpuSection_oid').disable();
		base_form.findField('Org_did').disable();

		if (!arguments[0]) {
			sw.swMsg.alert(lang['soobschenie'], lang['nevernyie_parametryi']);
			return false;
		}

		if (arguments[0].onPersonChange) {
			this.onPersonChange = arguments[0].onPersonChange;
		}

		if (arguments[0].delDocsView) {
			this.delDocsView = arguments[0].delDocsView;
		}

		base_form.setValues(arguments[0]);

		if (arguments[0].action) {
			this.action = arguments[0].action;
			this.evnStickAction = arguments[0].action;
		}

		if (arguments[0].callback && typeof arguments[0].callback == 'function') {
			this.callback = arguments[0].callback;
		}

		if (!Ext.isEmpty(arguments[0].Diag_id)) {
			this.params.Diag_id = arguments[0].Diag_id;
		}

		if (!Ext.isEmpty(arguments[0].EvnVizitPL_setDate)) {
			this.params.EvnVizitPL_setDate = arguments[0].EvnVizitPL_setDate;
		}

		if (!Ext.isEmpty(arguments[0].EvnVizitPL_setTime)) {
			this.params.EvnVizitPL_setTime = arguments[0].EvnVizitPL_setTime;
		}

		if (!Ext.isEmpty(arguments[0].LpuSection_id)) {
			this.params.LpuSection_id = arguments[0].LpuSection_id;
		}

		if (!Ext.isEmpty(arguments[0].MedStaffFact_id)) {
			this.params.MedStaffFact_id = arguments[0].MedStaffFact_id;
		}

		if (!Ext.isEmpty(arguments[0].MedStaffFact_sid)) {
			this.params.MedStaffFact_sid = arguments[0].MedStaffFact_sid;
		}

		if (!Ext.isEmpty(arguments[0].MedPersonal_id)) {
			this.params.MedPersonal_id = arguments[0].MedPersonal_id;
		}

		if (!Ext.isEmpty(arguments[0].MedPersonal_sid)) {
			this.params.MedPersonal_sid = arguments[0].MedPersonal_sid;
		}

		if (arguments[0].onHide && typeof arguments[0].onHide == 'function') {
			this.onHide = arguments[0].onHide;
		}

		if (!Ext.isEmpty(arguments[0].PayType_id)) {
			this.params.PayType_id = arguments[0].PayType_id;
		}

		if (!Ext.isEmpty(arguments[0].ServiceType_id)) {
			this.params.ServiceType_id = arguments[0].ServiceType_id;
		}

		if (!Ext.isEmpty(arguments[0].ServiceType_SysNick)) {
			this.params.ServiceType_SysNick = arguments[0].ServiceType_SysNick;
		}

		if (!Ext.isEmpty(arguments[0].VizitType_id)) {
			this.params.VizitType_id = arguments[0].VizitType_id;
		}
		if (!Ext.isEmpty(arguments[0].UslugaComplex_uid)) {
			this.params.UslugaComplex_uid = arguments[0].UslugaComplex_uid;
		}

		if (!Ext.isEmpty(arguments[0].RiskLevel_id)) {
			this.params.RiskLevel_id = arguments[0].RiskLevel_id;
		}

		if (!Ext.isEmpty(arguments[0].VizitType_SysNick)) {
			this.params.VizitType_SysNick = arguments[0].VizitType_SysNick;
		}

		if (!Ext.isEmpty(arguments[0].MedicalCareKind_id)) {
			this.params.MedicalCareKind_id = arguments[0].MedicalCareKind_id;
		}

		if (arguments[0].from) {
			this.params.from = arguments[0].from;
		}

		if (arguments[0].streamInput) {
			this.streamInput = arguments[0].streamInput;
			this.panelEvnDirectionAll.useCase = 'choose_for_evnpl_stream_input';
		}

		if (arguments[0].PersonEvn_id && arguments[0].usePersonEvn) {
			this.PersonEvn_id = arguments[0].PersonEvn_id;
		}

		if (arguments[0].Server_id && arguments[0].usePersonEvn) {
			this.Server_id = arguments[0].Server_id;
		} else {
			this.Server_id = null;
		}

		if (!Ext.isEmpty(arguments[0].TimetableGraf_id)) {
			this.params.TimetableGraf_id = arguments[0].TimetableGraf_id;
		}

		if (!Ext.isEmpty(arguments[0].UserMedStaffFact_id)) {
			this.workplace_params.UserMedStaffFact_id = arguments[0].UserMedStaffFact_id;
		}

		if (!Ext.isEmpty(arguments[0].UserLpuSection_id)) {
			this.workplace_params.UserLpuSection_id = arguments[0].UserLpuSection_id;
		}


		if (this.action == 'add') {
			this.findById('EPLEF_EvnStickPanel').isLoaded = true;
			this.findById('EPLEF_EvnUslugaPanel').isLoaded = true;
			this.findById('EPLEF_EvnVizitPLPanel').isLoaded = true;
			this.findById('EPLEF_EvnDrugPanel').isLoaded = true;
		}
		else {
			this.findById('EPLEF_EvnStickPanel').isLoaded = false;
			this.findById('EPLEF_EvnUslugaPanel').isLoaded = false;
			this.findById('EPLEF_EvnVizitPLPanel').isLoaded = false;
			this.findById('EPLEF_EvnDrugPanel').isLoaded = false;
		}

		var evn_pl_id = base_form.findField('EvnPL_id').getValue();

		base_form.findField('EvnPL_RepFlag').hideContainer();
		base_form.findField('Diag_did').on('change', function (combo, newValue) {
			var diag = combo.getStore().getById(newValue);
			if(diag!=undefined){
				var diagGroup = diag.get('Diag_Code')[0];
				if(diagGroup=="S"||diagGroup=="T"){
					base_form.findField('Diag_preid').setDisabled(false);
					base_form.findField('Diag_preid').setContainerVisible(true);
				} else {
					base_form.findField('Diag_preid').setDisabled(true);
					base_form.findField('Diag_preid').setContainerVisible(false);
						base_form.findField('Diag_preid').clearValue();
				}
			}
		});
		base_form.findField('PrehospDirect_id').on('keydown', function(inp, e) {
            switch ( e.getKey() ) {
                case Ext.EventObject.TAB:
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
						if(!base_form.findField('Diag_fid').disabled){
							base_form.findField('Diag_fid').focus();
						} else {
							this.findById('EPLEF_EvnVizitPLGrid').getSelectionModel().clearSelections();
	                        this.findById('EPLEF_EvnVizitPLGrid').getView().focusRow(0);
	                        this.findById('EPLEF_EvnVizitPLGrid').getSelectionModel().selectFirstRow();
						}

                    break;
            }
        }.createDelegate(this));
		this.loadMask = new Ext.LoadMask(this.getEl(), {msg: LOAD_WAIT});
		this.loadMask.show();

		//Проверяем возможность редактирования документа
		if (this.action === 'edit') {
			Ext.Ajax.request({
				failure: function (response, options) {
					sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_zagruzke_dannyih_formyi'], function () {
						this.hide();
					}.createDelegate(this));
				},
				params: {
					Evn_id: evn_pl_id,
					MedStaffFact_id: (!Ext.isEmpty(sw.Promed.MedStaffFactByUser) && !Ext.isEmpty(sw.Promed.MedStaffFactByUser.current) && !Ext.isEmpty(sw.Promed.MedStaffFactByUser.current.MedStaffFact_id)) ? sw.Promed.MedStaffFactByUser.current.MedStaffFact_id : null,
					ArmType: (!Ext.isEmpty(sw.Promed.MedStaffFactByUser) && !Ext.isEmpty(sw.Promed.MedStaffFactByUser.current) && !Ext.isEmpty(sw.Promed.MedStaffFactByUser.current.ARMType)) ? sw.Promed.MedStaffFactByUser.current.ARMType : null
				},
				success: function (response, options) {
					if (!Ext.isEmpty(response.responseText)) {
						var response_obj = Ext.util.JSON.decode(response.responseText);

						if (response_obj.success == false) {
							sw.swMsg.alert(lang['oshibka'], response_obj.Error_Msg ? response_obj.Error_Msg : lang['oshibka_pri_zagruzke_dannyih_formyi']);
							_this.action = 'view';
							if(getRegionNick() == 'vologda') {
								_this.gridAccess = 'full';
							}
						}

						if (response_obj.Alert_Msg) {
							sw.swMsg.alert(langs('Внимание'), response_obj.Alert_Msg);
						}
					}
					else {
						_this.gridAccess = 'full';
					}

					//вынес продолжение show в отдельную функцию, т.к. иногда callback приходит после выполнения логики
					_this.onShow();
				}.createDelegate(this),
				url: '/?c=Evn&m=CommonChecksForEdit'
			});
		} else {
			if ( this.action == 'add' ) {
				this.gridAccess = 'full';
			}

			_this.onShow();
		}
	},
	onShow: function(){

		var base_form = this.findById('EvnPLEditForm').getForm(),
			_this = this;

		var direct_class_combo = base_form.findField('DirectClass_id');
		var is_finish_combo = base_form.findField('EvnPL_IsFinish');
		var is_unlaw_combo = base_form.findField('EvnPL_IsUnlaw');
		var is_unport_combo = base_form.findField('EvnPL_IsUnport');
		var lpu_section_dir_combo = base_form.findField('LpuSection_oid');
		var org_dir_combo = base_form.findField('Lpu_oid');
		var prehosp_trauma_combo = base_form.findField('PrehospTrauma_id');
		var result_class_combo = base_form.findField('ResultClass_id');
		var medical_care_kind_combo = base_form.findField('MedicalCareKind_id');

		var evn_pl_id = base_form.findField('EvnPL_id').getValue();
		var person_id = base_form.findField('Person_id').getValue();

		this.findById('EPLEF_EvnVizitPLGrid').getStore().removeAll();
		this.findById('EPLEF_EvnVizitPLGrid').getTopToolbar().items.items[0].enable();
		this.findById('EPLEF_EvnVizitPLGrid').getTopToolbar().items.items[1].disable();
		this.findById('EPLEF_EvnVizitPLGrid').getTopToolbar().items.items[2].disable();
		this.findById('EPLEF_EvnVizitPLGrid').getTopToolbar().items.items[3].disable();

		this.findById('EPLEF_EvnUslugaGrid').getStore().removeAll();
		this.findById('EPLEF_EvnUslugaGrid').getTopToolbar().items.items[0].enable();
		this.findById('EPLEF_EvnUslugaGrid').getTopToolbar().items.items[1].disable();
		this.findById('EPLEF_EvnUslugaGrid').getTopToolbar().items.items[2].disable();
		this.findById('EPLEF_EvnUslugaGrid').getTopToolbar().items.items[3].disable();

		this.findById('EPLEF_EvnStickGrid').getStore().removeAll();
		this.findById('EPLEF_EvnStickGrid').getTopToolbar().items.items[0].enable();
		this.findById('EPLEF_EvnStickGrid').getTopToolbar().items.items[1].disable();
		this.findById('EPLEF_EvnStickGrid').getTopToolbar().items.items[2].disable();
		this.findById('EPLEF_EvnStickGrid').getTopToolbar().items.items[3].disable();
		this.findById('EPLEF_EvnStickGrid').getTopToolbar().items.items[4].disable();
		this.findById('EPLEF_EvnStickGrid').getTopToolbar().items.items[5].disable();

		this.findById('EPLEF_EvnDrugGrid').getStore().removeAll();
		this.findById('EPLEF_EvnDrugGrid').getTopToolbar().items.items[0].enable();
		this.findById('EPLEF_EvnDrugGrid').getTopToolbar().items.items[1].disable();
		this.findById('EPLEF_EvnDrugGrid').getTopToolbar().items.items[2].disable();
		this.findById('EPLEF_EvnDrugGrid').getTopToolbar().items.items[3].disable();

		setLpuSectionGlobalStoreFilter();

		var isKareliya = (getRegionNick() == 'kareliya');
		switch ( this.action ) {
			case 'add':
				this.checkIsAssignNasel();
				this.fo = false;
				this.setTitle(WND_POL_EPLADD);
				this.enableEdit(true);

				LoadEmptyRow(this.findById('EPLEF_EvnStickGrid'));
				LoadEmptyRow(this.findById('EPLEF_EvnUslugaGrid'));
				LoadEmptyRow(this.findById('EPLEF_EvnVizitPLGrid'));
				LoadEmptyRow(this.findById('EPLEF_EvnDrugGrid'));
				var direct_class_id = direct_class_combo.getValue();
				var is_finish = is_finish_combo.getValue();

				//Проверяем возможность пользователя редактировать ЛВН
				checkEvnStickEditable('EPLEF_EvnStickGrid', _this);

				if ( is_finish == null || is_finish.toString().length == 0 ) {
					is_finish = 1;
				}

				this.findById('EPLEF_PersonInformationFrame').setTitle('...');
				this.findById('EPLEF_PersonInformationFrame').clearPersonChangeParams();
				this.findById('EPLEF_PersonInformationFrame').load({
					callback: function() {
						this.findById('EPLEF_PersonInformationFrame').setPersonTitle();
					}.createDelegate(this),
					onExpand: true,
					Person_id: person_id,
					Server_id: this.Server_id
				});
				direct_class_combo.setValue(direct_class_id);
				is_finish_combo.setValue(is_finish);
				is_unport_combo.setValue(1);

				direct_class_combo.fireEvent('change', direct_class_combo, direct_class_id, direct_class_id + 1);
				is_finish_combo.fireEvent('change', is_finish_combo, is_finish, is_finish + 1);
				prehosp_trauma_combo.fireEvent('change', prehosp_trauma_combo, null, 1);

				if (isKareliya) {
					base_form.findField('EvnPL_IsFirstDisable').setValue(1);
					if (this.params.MedicalCareKind_id) {
						medical_care_kind_combo.setValue(this.params.MedicalCareKind_id);
					} else {
						medical_care_kind_combo.setFieldValue('MedicalCareKind_Code', 1);
					}
				}

				this.loadMask.hide();

				this.getEvnPLNumber();

				this.panelEvnDirectionAll.isReadOnly = false;
				this.panelEvnDirectionAll.onLoadForm(this);
				base_form.findField('EvnPL_IsWithoutDirection').focus(false, 50);
				// base_form.findField('EvnPL_NumCard').focus(false, 250);

				if (getRegionNick().inlist(['ekb','vologda','krasnoyarsk','pskov', 'msk', 'khak']) && !Ext.isEmpty(base_form.findField('EvnPL_lid').getValue()) && base_form.findField('EvnPL_lid').getValue() != 0) {
					// в разделе «Результат» для следующих полей устанавливаются значения по умолчанию
					base_form.findField('EvnPL_IsFinish').setValue(2); // o Случай закончен. Значение по умолчанию: «Да»
					base_form.findField('EvnPL_IsFinish').fireEvent('change', base_form.findField('EvnPL_IsFinish'), 2);
					base_form.findField('ResultClass_id').setFieldValue('ResultClass_Code', 304); // o Результат обращения. Значение по умолчанию: «Лечение продолжено»
					base_form.findField('ResultDeseaseType_id').setFieldValue('ResultDeseaseType_Code', 304); // o Исход. Значение по умолчанию: «Без перемен»

					// в разделе «Результат» автоматически заполняются следующие поля данными из исходной формы «Поступление пациента в приемное отделение»:
					_this.getLoadMask('Получение данных КВС').show();
					Ext.Ajax.request({
						url: '/?c=EvnPS&m=getEvnPSInfoForEvnPL',
						params: {
							EvnPS_id: base_form.findField('EvnPL_lid').getValue()
						},
						callback: function(options, success, response) {
							if ( success ) {
								var response_obj = Ext.util.JSON.decode(response.responseText);
								if (response_obj.EvnPS_id) {
									_this.EvnPSInfo = response_obj;
									// o Заключ. диагноз. Значение поля «Диагноз прием. отд-я» исходной формы.
									if (!Ext.isEmpty(response_obj.Diag_pid)) {
										base_form.findField('Diag_lid').getStore().load({
											callback: function() {
												base_form.findField('Diag_lid').setValue(response_obj.Diag_pid);
												_this.setDiagConcComboVisible();
											}.createDelegate(this),
											params: {where: "where DiagLevel_id = 4 and Diag_id = " + response_obj.Diag_pid}
										});
									}
									// o Вид травмы (внешнего воздействия). Значение одноименного поля исходной формы.
									base_form.findField('PrehospTrauma_id').setValue(response_obj.PrehospTrauma_id);
									// o Заключ. внешняя причина. Значение поля «Внешняя причина» исходной формы.
									if (!Ext.isEmpty(response_obj.Diag_eid)) {
										base_form.findField('Diag_concid').getStore().load({
											callback: function () {
												base_form.findField('Diag_concid').setValue(response_obj.Diag_eid);
											},
											params: {where: "where DiagLevel_id = 4 and Diag_id = " + response_obj.Diag_eid}
										});
									}
									// o Противоправная. Значение одноименного поля исходной формы.
									base_form.findField('EvnPL_IsUnlaw').setValue(response_obj.EvnPS_IsUnlaw);
									// o Нетранспортабельность. Значение одноименного поля исходной формы
									base_form.findField('EvnPL_IsUnport').setValue(response_obj.EvnPS_IsUnport);
								}
							}
						}
					});
				}
			break;

			case 'edit':
			case 'view':
				base_form.load({
					failure: function() {
						this.loadMask.hide();
						sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_zagruzke_dannyih_formyi'], function() {this.hide();}.createDelegate(this) );
					}.createDelegate(this),
					params: {
						EvnPL_id: evn_pl_id,
						archiveRecord: _this.archiveRecord,
						delDocsView: _this.delDocsView ? 1 : 0
					},
					success: function() {
						// В зависимости от accessType переопределяем this.action
						if ( base_form.findField('accessType').getValue() == 'view' ) {
							this.action = 'view';
						}

						if ( base_form.findField('canCreateVizit').getValue() == "true") {
							this.canCreateVizit = true;
						} else {
							this.canCreateVizit = false;
						}

						this.checkForCostPrintPanel();

						if (getRegionNick() == 'vologda') {
							if (this.action == 'view' && this.gridAccess == 'full') {
								setTimeout(function(){
									_this.findById('EPLEF_EvnVizitPLGrid').getTopToolbar().items.items[0].enable();
								}, 50);
							}
						}

						this.checkIsAssignNasel();

						if ( this.action == 'view' && !this.canCreateVizit) {
							this.setTitle(WND_POL_EPLVIEW);
							this.enableEdit(false);

							this.findById('EPLEF_PersonInformationFrame').clearPersonChangeParams();
						}
						else {
							this.setTitle(WND_POL_EPLEDIT);
							this.enableEdit(true);

							this.findById('EPLEF_PersonInformationFrame').setPersonChangeParams({
								 callback: function(data) {
									// если открыли из ЭМК, то надо ЭМК перекотрыть, делаем это в onPersonChange
									this.onPersonChange(data);
									this.hide();
								 }.createDelegate(this)
								,Evn_id: evn_pl_id
							});
						}

						if ( getRegionNick() == 'perm' && base_form.findField('EvnPL_IsPaid').getValue() == 2 && parseInt(base_form.findField('EvnPL_IndexRepInReg').getValue()) > 0 ) {
							base_form.findField('EvnPL_RepFlag').showContainer();

							if ( parseInt(base_form.findField('EvnPL_IndexRep').getValue()) >= parseInt(base_form.findField('EvnPL_IndexRepInReg').getValue()) ) {
								base_form.findField('EvnPL_RepFlag').setValue(true);
							}
							else {
								base_form.findField('EvnPL_RepFlag').setValue(false);
							}
						}

						this.findById('EPLEF_PersonInformationFrame').setTitle('...');
						this.findById('EPLEF_PersonInformationFrame').load({
							callback: function() {
								this.findById('EPLEF_PersonInformationFrame').setPersonTitle();
							}.createDelegate(this),
							onExpand: true,
							Person_id: base_form.findField('Person_id').getValue(),
							Server_id: (this.Server_id ? this.Server_id : base_form.findField('Server_id').getValue()),
							//PersonEvn_id: (this.PersonEvn_id ? this.PersonEvn_id : base_form.findField('PersonEvn_id').getValue()),
							Evn_setDT:base_form.findField('EvnPL_setDate').getValue()
						});

						// Посещения прогружаем в любом случае
						this.findById('EPLEF_EvnVizitPLPanel').fireEvent('expand', this.findById('EPLEF_EvnVizitPLPanel'));
						//this.checkAbort();
						// Остальные гриды - только если развернуты панельки
						if ( !this.findById('EPLEF_EvnStickPanel').collapsed ) {
							this.findById('EPLEF_EvnStickPanel').fireEvent('expand', this.findById('EPLEF_EvnStickPanel'));
						}

						if ( !this.findById('EPLEF_EvnUslugaPanel').collapsed || getRegionNick()=='perm') {
							this.findById('EPLEF_EvnUslugaPanel').fireEvent('expand', this.findById('EPLEF_EvnUslugaPanel'));
						}
						if(!this.findById('EPLEF_EvnDrugPanel').collapsed){
							this.findById('EPLEF_EvnDrugPanel').fireEvent('expand', this.findById('EPLEF_EvnDrugPanel'));
						}
						if(!this.findById('EPLEF_CostPrintPanel').collapsed){
							this.findById('EPLEF_CostPrintPanel').fireEvent('expand', this.findById('EPLEF_CostPrintPanel'));
						}
						/*
						if ( !this.findById('EPLEF_SpecificPanel').collapsed ) {
							this.findById('EPLEF_SpecificPanel').fireEvent('expand', this.findById('EPLEF_SpecificPanel'));
						}*/

						var direct_class_id = direct_class_combo.getValue();
						var evnpl_isfinish = is_finish_combo.getValue();
						var evnpl_isunlaw = is_unlaw_combo.getValue();
						var lpu_section_oid = lpu_section_dir_combo.getValue();
						var lpu_oid = org_dir_combo.getValue();
						var prehosp_trauma_id = prehosp_trauma_combo.getValue();
						var record;
						var result_class_id = result_class_combo.getValue();
						var diag_concid = base_form.findField('Diag_concid').getValue();
						var diag_lid = base_form.findField('Diag_lid').getValue();
						var diag_fid = base_form.findField('Diag_fid').getValue();

						if ( this.action == 'edit' ) {
							prehosp_trauma_combo.fireEvent('change', prehosp_trauma_combo, prehosp_trauma_id, -1);
							is_unlaw_combo.setValue(evnpl_isunlaw);
							direct_class_combo.fireEvent('change', direct_class_combo, direct_class_id, direct_class_id + 1);
							is_finish_combo.fireEvent('change', is_finish_combo, evnpl_isfinish, -1);
							result_class_combo.setValue(result_class_id);
							if (evnpl_isfinish == 2) {
								this.findById('EPLEF_EvnVizitPLGrid').getTopToolbar().items.items[0].disable();
							}
						}
						else {
							if (this.canCreateVizit) {
								this.findById('EPLEF_EvnVizitPLGrid').getTopToolbar().items.items[0].enable();
								this.findById('EPLEF_EvnUslugaGrid').getTopToolbar().items.items[0].enable();
								this.findById('EPLEF_EvnStickGrid').getTopToolbar().items.items[0].enable();
							} else {
								this.findById('EPLEF_EvnVizitPLGrid').getTopToolbar().items.items[0].disable();
								this.findById('EPLEF_EvnUslugaGrid').getTopToolbar().items.items[0].disable();
								this.findById('EPLEF_EvnStickGrid').getTopToolbar().items.items[0].disable();
							}
							base_form.findField('EvnPL_NumCard').disable();
							base_form.findField('Diag_fid').disable();
							this.findById('EPLEF_EvnDrugGrid').getTopToolbar().items.items[0].disable();
						}

						//Проверяем возможность пользователя редактировать ЛВН
						checkEvnStickEditable('EPLEF_EvnStickGrid', _this);

						record = direct_class_combo.getStore().getById(direct_class_id);

						if ( record ) {
							var direct_class_code = record.get('DirectClass_Code');

							switch ( direct_class_code ) {
								case 1:
									lpu_section_dir_combo.setValue(lpu_section_oid);
								break;

								case 2:
									org_dir_combo.getStore().load({
										callback: function(records, options, success) {
											if ( success ) {
												org_dir_combo.setValue(lpu_oid);
											}
										},
										params: {
											Lpu_oid: lpu_oid,
											OrgType: 'lpu'
										}
									});
								break;

								default:
									return false;
								break;
							}
						}

						if ( diag_concid != null && diag_concid.toString().length > 0 ) {
							base_form.findField('Diag_concid').getStore().load({
								callback: function() {
									base_form.findField('Diag_concid').getStore().each(function(record) {
										if ( record.get('Diag_id') == diag_concid ) {
											base_form.findField('Diag_concid').fireEvent('select', base_form.findField('Diag_concid'), record, 0);
										}
									});
									//base_form.findField('Diag_concid').setFilterByDate(base_form.findField('EvnVizitPL_setDate').getValue());
								},
								params: {where: "where DiagLevel_id = 4 and Diag_id = " + diag_concid}
							});
						}

						if (!Ext.isEmpty(diag_lid)) {
							base_form.findField('Diag_lid').getStore().load({
								callback: function() {
									base_form.findField('Diag_lid').setValue(diag_lid);
									this.setDiagConcComboVisible();
								}.createDelegate(this),
								params: {where: "where DiagLevel_id = 4 and Diag_id = " + diag_lid}
							});
						}

						base_form.findField('DirectType_id').fireEvent('change', base_form.findField('DirectType_id'), base_form.findField('DirectType_id').getValue());

						this.loadMask.hide();

						this.panelEvnDirectionAll.isReadOnly = (this.action == 'view');
						this.panelEvnDirectionAll.onLoadForm(this);
						if (!Ext.isEmpty(diag_fid)) {
							base_form.findField('Diag_fid').getStore().load({
								callback: function() {
									base_form.findField('Diag_fid').setValue(diag_fid);
								},
								params: {where: "where DiagLevel_id = 4 and Diag_id = " + diag_fid}
							});
						}

						if ( this.action == 'edit' ) {
							base_form.findField('EvnPL_NumCard').focus(false, 250);
						}
						else {
							this.buttons[this.buttons.length - 1].focus();
						}
						this.fo = false;
					}.createDelegate(this),
					url: '/?c=EvnPL&m=loadEvnPLEditForm'
				});
			break;

			default:
				this.loadMask.hide();
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
		var params = {};

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

				var grid_fields = [];

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
		};
		params.formMode = 'local';
		params.formParams = {};

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
	width: 800,
	printControlCardZno: function()
	{
		var grid = Ext.getCmp('EPLEF_EvnVizitPLGrid'),
			rec = grid.getSelectionModel().getSelected();

		if (rec.get('EvnVizitPL_id'))
		{
			printControlCardZno(rec.get('EvnVizitPL_id'));
		}
	},
	printControlCardOnko: function()
	{
		var grid = Ext.getCmp('EPLEF_EvnVizitPLGrid'),
			rec = grid.getSelectionModel().getSelected();

		if (rec.get('EvnVizitPL_id'))
		{
			printControlCardOnko(rec.get('EvnVizitPL_id'));
		}
	}
});
