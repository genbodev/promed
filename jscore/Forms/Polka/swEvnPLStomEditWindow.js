/**
* swEvnPLStomEditWindow - окно редактирования/добавления ТАП в стоматологии.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Polka
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Stas Bykov aka Savage (savage@swan.perm.ru)
* @version      0.001-14.01.2010
* @comment      Префикс для id компонентов EPLStomEF (EvnPLStomEditForm)
*
*
* @input data: action - действие (add, edit, view)
*              EvnPLStom_id - ID талона для редактирования или просмотра
*              Person_id - ID человека
*              PersonEvn_id - ID состояния человека
*              Server_id - ID сервера
*/

sw.Promed.swEvnPLStomEditWindow = Ext.extend(sw.Promed.BaseForm, {
	action: null,
	baseTitle: WND_POL_EPLSTOM,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: false,
	closeAction: 'hide',
	checkOnEvnPLStomIsFinish: function() {
		if ( this.action == 'view' ) {
			return '';
		}

		var base_form = this.findById('EvnPLStomEditForm').getForm();

		if ( Ext.isEmpty(base_form.findField('EvnPLStom_id').getValue()) || base_form.findField('EvnPLStom_IsFinish').getValue() != 2 ) {
			return '';
		}

		if ( this.formMode == 'morbus' ) {
			var
				morbusCountAll = 0,
				morbusCountClosed = 0;

			this.findById('EPLStomEF_EvnDiagPLStomGrid').getStore().each(function(rec) {
				if ( !Ext.isEmpty(rec.get('EvnDiagPLStom_id')) ) {
					morbusCountAll++;

					if ( !Ext.isEmpty(rec.get('EvnDiagPLStom_IsClosed')) && rec.get('EvnDiagPLStom_IsClosed') == 2 ) {
						morbusCountClosed++;
					}
				}
			});

			if ( morbusCountAll == 0 ) {
				return 'Случай не может быть закончен, т.к. не заведено ни одного заболевания';
			}
			else if ( morbusCountAll != morbusCountClosed ) {
				return 'Случай не может быть закончен, пока есть незакрытые заболевания';
			}
		}
	},
	checkLpuHasConsPriemVolume: function() {
		if (getRegionNick() == 'astra') {
			var win = this;
			var base_form = win.findById('EvnPLStomEditForm').getForm();
			var checked = base_form.findField('EvnPLStom_IsCons').getValue();

			base_form.findField('EvnPLStom_IsCons').hide();
			base_form.findField('EvnPLStom_IsCons').setValue(false);

			// проверка наличия действующей записи в объеме «Консультативный прием»
			Ext.Ajax.request({
				failure: function (response, options) {
					sw.swMsg.alert(langs('Ошибка'), langs('При проверке наличия действующей записи в объеме "Консультативный прием" возникли ошибки'));
				},
				params: {
					Evn_id: base_form.findField('EvnPLStom_id').getValue(),
					setDate: !Ext.isEmpty(base_form.findField('EvnPLStom_setDate').getValue()) ? base_form.findField('EvnPLStom_setDate').getValue().format('d.m.Y') : null
				},
				success: function (response, options) {
					var response_obj = Ext.util.JSON.decode(response.responseText);

					if ( typeof response_obj == 'object' && response_obj.length > 0 && !Ext.isEmpty(response_obj[0].AttributeValue_id) ) {
						base_form.findField('EvnPLStom_IsCons').show();
						if ( checked || (win.action == 'add' && Ext.isEmpty(response_obj[0].AttributeValue_ValueFloat)) ) {
							base_form.findField('EvnPLStom_IsCons').setValue(true);
						}
					}
				},
				url: '/?c=EvnPL&m=checkLpuHasConsPriemVolume'
			});
		}
	},
	collapsible: true,
	undoDeleteEvnStick: function() {
		var win = this;
		var grid = this.findById('EPLStomEF_EvnStickGrid');

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
		if ( this.action == 'view' ) {
			return false;
		}
		else if ( Ext.isEmpty(event) || !event.inlist([ 'EvnDrug', 'EvnStick', 'EvnUsluga', 'EvnVizitPLStom' ]) ) {
			return false;
		}
		else if ( this.formMode == 'morbus' && event.inlist([ 'EvnUsluga' ]) ) {
			return false;
		}

		options = options || {};

		var
			base_form = this.findById('EvnPLStomEditForm').getForm(),
			error = '',
			_this = this,
			grid = null,
			lastEvnDeleted = false,
			params = {},
			question = '',
			url = '';

		if (options.params) {
			params = options.params;
		}

		switch ( event ) {
			case 'EvnDrug':
				grid = this.findById('EPLStomEF_EvnDrugGrid');
			break;

			case 'EvnStick':
				grid = this.findById('EPLStomEF_EvnStickGrid');
			break;

			case 'EvnUsluga':
				grid = this.findById('EPLStomEF_EvnUslugaGrid');
			break;

			case 'EvnVizitPLStom':
				grid = this.findById('EPLStomEF_EvnVizitPLStomGrid');
			break;
		}

		if ( !grid || !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get(event + '_id') ) {
			return false;
		}

		var selected_record = grid.getSelectionModel().getSelected();

		switch ( event ) {
			case 'EvnDrug':
				error = langs('При удалении случая использования медикаментов возникли ошибки');
				question = langs('Удалить случай использования медикаментов?');
				url = '/?c=EvnDrug&m=deleteEvnDrug';

				params['EvnDrug_id'] = selected_record.get('EvnDrug_id');
			break;

			case 'EvnStick':
				var evn_pl_stom_id = base_form.findField('EvnPLStom_id').getValue();
				var evn_stick_mid = selected_record.get('EvnStick_mid');

				if ( selected_record.get('evnStickType') == 3 ) {
					if ( evn_pl_stom_id == evn_stick_mid ) {
						error = 'При удалении справки учащегося возникли ошибки';
						question = 'Удалить справку учащегося?';
					}
					else {
						error = 'При удалении связи справки учащегося с текущим документом возникли ошибки';
						question = 'Удалить связь справки учащегося с текущим документом?';
					}

					url = '/?c=Stick&m=deleteEvnStickStudent';

					params['EvnStickStudent_id'] = selected_record.get('EvnStick_id');
					params['EvnStickStudent_mid'] = evn_pl_stom_id;
				}
				else {
					error = 'При удалении ЛВН возникли ошибки';
					question = 'Удалить ЛВН?';

					url = '/?c=Stick&m=deleteEvnStick';

					params['EvnStick_id'] = selected_record.get('EvnStick_id');
					params['EvnStick_mid'] = evn_pl_stom_id;
				}
			break;

			case 'EvnUsluga':
				error = 'При удалении услуги возникли ошибки';
				question = 'Удалить услугу?';
				url = '/?c=EvnUsluga&m=deleteEvnUsluga';

				params['class'] = selected_record.get('EvnClass_SysNick');
				params['id'] = selected_record.get('EvnUsluga_id');
			break;

			case 'EvnVizitPLStom':
				if ( this.action == 'view' || selected_record.get('accessType') != 'edit'
					|| (
						!Ext.isEmpty(getGlobalOptions().medpersonal_id)
						&& !Ext.isEmpty(selected_record.get('MedPersonal_id'))
						&& userHasWorkPlaceAtLpuSection(selected_record.get('LpuSection_id')) == false
						&& getGlobalOptions().isMedStatUser != true
						&& isSuperAdmin() != true
					)
					|| selected_record.get('EvnVizitPLStom_IsSigned') != 1
				) {
					return false;
				}

				error = 'При удалении посещения возникли ошибки';
				question = 'Удалить посещение?';
				url = '/?c=Evn&m=deleteEvn';
				params['Evn_id'] = selected_record.get('EvnVizitPLStom_id');

				var count = grid.getStore().getCount();
				if ( count == 1 ) {
					params['Evn_id'] = base_form.findField('EvnPLStom_id').getValue();
					question += ' Будет удален весь талон амбулаторного пациента.';
					lastEvnDeleted = true;
				}
			break;
		}

		var alert = {
			EvnVizitPLStom: {
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
				'808' : {
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
				}
			}
		};

		alert['EvnStick'] = sw.Promed.EvnStick.getDeleteAlertCodes({
			callback: function(options) {
				_this.deleteEvent(event, options);
			},
			options: options
		});

		if ( options.ignoreDoc ) {
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
			var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Удаление записи..." });
			loadMask.show();

			Ext.Ajax.request({
				failure: function(response, options) {
					loadMask.hide();
					sw.swMsg.alert('Ошибка', error);
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
									title: 'Вопрос'
								});
							}
						} else {
							sw.swMsg.alert('Ошибка', response_obj.Error_Msg ? response_obj.Error_Msg : error);
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

						if ( event == 'EvnVizitPLStom' ) {
							var setDate;

							grid.getStore().each(function(record) {
								if ( typeof setDate != 'object' || record.get('EvnVizitPLStom_setDate') <= setDate ) {
									setDate = record.get('EvnVizitPLStom_setDate');
								}
							});

							if ( !Ext.isEmpty(setDate) ) {
								base_form.findField('EvnPLStom_setDate').setValue(setDate);
							}

							if ( lastEvnDeleted == false ) {
								// Перезагрузить грид с заболеваниями
								if ( this.findById('EPLStomEF_EvnDiagPLStomPanel').isLoaded === true ) {
									this.findById('EPLStomEF_EvnDiagPLStomGrid').getStore().load({
										params: {
											rid: this.findById('EvnPLStomEditForm').getForm().findField('EvnPLStom_id').getValue()
										}
									});
								}

								// Перезагрузить грид с услугами
								if ( this.findById('EPLStomEF_EvnUslugaPanel').isLoaded === true ) {
									this.findById('EPLStomEF_EvnUslugaGrid').getStore().load({
										params: {
											pid: this.findById('EvnPLStomEditForm').getForm().findField('EvnPLStom_id').getValue(),
											rid: this.findById('EvnPLStomEditForm').getForm().findField('EvnPLStom_id').getValue()
										}
									});
								}

								this.checkTrauma();
							}
							else {
								this.callback({
									evnPLStomData: {
										lastEvnDeleted: true,
										EvnPLStom_id: base_form.findField('EvnPLStom_id').getValue()
									}
								});
								this.hide();
							}
						}

						grid.getView().focusRow(0);
						grid.getSelectionModel().selectFirstRow();
					}
				}.createDelegate(this),
				url: url
			});
		}.createDelegate(this);

		if ( options.ignoreQuestion ) {
			doDelete();
		}
		else {
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
				},
				icon: Ext.MessageBox.QUESTION,
				msg: question,
				title: 'Вопрос'
			});
		}
	},
	setMKB: function(){
		var parentWin =this;
		var base_form = this.findById('EvnPLStomEditForm').getForm();
		var sex = parentWin.findById('EPLStomEF_PersonInformationFrame').getFieldValue('Sex_Code');
		var age = swGetPersonAge(parentWin.findById('EPLStomEF_PersonInformationFrame').getFieldValue('Person_Birthday'),base_form.findField('EvnDirection_setDate').getValue());
		base_form.findField('Diag_did').setMKBFilter(age,sex,true);
	},
	checkTrauma: function() {
		var base_form = this.findById('EvnPLStomEditForm').getForm();
		var is_finish = base_form.findField('EvnPLStom_IsFinish').getValue();
		var grid = this.findById('EPLStomEF_EvnVizitPLStomGrid');
		var traumaField = base_form.findField('PrehospTrauma_id');
		var checkTr = true;

		if (is_finish==2 && grid.getStore().getCount() > 0 && grid.getStore().getAt(0).get('EvnPLStom_id') ) {
			grid.getStore().each(function(rec) {
				if (!Ext.isEmpty(rec.get('Diag_Code')) && rec.get('Diag_Code')[0].inlist(['T', 'S'])) {
					checkTr = false;
					return false;
				}
			});
		}
		traumaField.setAllowBlank(checkTr);
		return checkTr;
	},

	doSave: function(options) {
		// options @Object
		// options.ignoreEvnVizitPLCountCheck @Boolean Не проверять наличие посещений, если true
		// options.ignoreDiagCountCheck @Boolean Не проверять наличие основного диагноза, если true
		// options.print @Boolean Вызывать печать рецепта, если true
		// options.openChildWindow @Function Открыть дочернее окно после сохранения
		var wnd = this;

		if ( this.formStatus == 'save' || (this.action == 'view' && !this.canCreateVizit)) {
			return false;
		}

		this.formStatus = 'save';

		var base_form = this.findById('EvnPLStomEditForm').getForm();
		var form = this.findById('EvnPLStomEditForm');

		if ( !this.checkTrauma() ) {
			var trField = base_form.findField('PrehospTrauma_id');
			if ( Ext.isEmpty(trField.getValue()) ) {
				sw.swMsg.show({
					buttons: Ext.Msg.OK,
					fn: function() {
						wnd.formStatus = 'edit';
						trField.focus(false);
					},
					icon: Ext.Msg.WARNING,
					msg: 'При диагнозах категорий "S" и "T", поле "Вид травмы (внешнего воздействия)" должно быть заполнено.',
					title: ERR_INVFIELDS_TIT
				});
				return false;
			}
		}

		var evn_pl_stom_form = this.findById('EvnPLStomEditForm').getForm();

		if ( !evn_pl_stom_form.isValid() ) {
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

		var evn_vizit_pl_stom_store = this.findById('EPLStomEF_EvnVizitPLStomGrid').getStore();

		if ( !options || !options.ignoreEvnVizitPLCountCheck ) {
			if ( evn_vizit_pl_stom_store.getCount() == 0 || (evn_vizit_pl_stom_store.getCount() == 1 && !evn_vizit_pl_stom_store.getAt(0).get('EvnVizitPLStom_id')) ) {
				sw.swMsg.show({
					buttons: Ext.Msg.OK,
					fn: function() {
						this.formStatus = 'edit';
						this.findById('EPLStomEF_EvnVizitPLStomGrid').getView().focusRow(0);
						this.findById('EPLStomEF_EvnVizitPLStomGrid').getSelectionModel().selectFirstRow();
					}.createDelegate(this),
					icon: Ext.Msg.WARNING,
					msg: 'Не введено ни одного посещения. Сохранение талона невозможно',
					title: ERR_INVFIELDS_TIT
				});
				return false;
			}
		}

		var evn_pl_stom_ukl = evn_pl_stom_form.findField('EvnPLStom_UKL').getValue();
		var is_finish = evn_pl_stom_form.findField('EvnPLStom_IsFinish').getValue();
		var result_class_id = evn_pl_stom_form.findField('ResultClass_id').getValue();
		var result_desease_type_id = evn_pl_stom_form.findField('ResultDeseaseType_id').getValue();

		if ( getRegionNick().inlist(['kareliya']) ) {
			var deseaseVizitCnt = 0;
			var otherVizitCnt = 0;
			var totalVizitCnt = 0;

			this.findById('EPLStomEF_EvnVizitPLStomGrid').getStore().each(function(rec) {
				if ( rec.get('VizitType_SysNick') ) {
					totalVizitCnt++;
					if ( rec.get('VizitType_SysNick') == 'desease' ) {
						deseaseVizitCnt++;
					} else {
						otherVizitCnt++;
					}
				}
			});

			if ( totalVizitCnt > 0 && deseaseVizitCnt > 0 && otherVizitCnt > 0 && getRegionNick() != 'kareliya') {
				this.formStatus = 'edit';
				sw.swMsg.alert('Ошибка', 'В ТАП более одного посещения и присутствуют посещения с целью отличной от "Обращение по поводу заболевания"!', function() {
					base_form.findField('EvnPLStom_IsFinish').focus(true);
				});
				return false;
			}
			if ( otherVizitCnt > 1 && getRegionNick() != 'kareliya') {
				this.formStatus = 'edit';
				sw.swMsg.alert('Ошибка', 'В ТАП более одного с целью отличной от "Обращение по поводу заболевания"!', function() {
					base_form.findField('EvnPLStom_IsFinish').focus(true);
				});
				return false;
			}
			if ( deseaseVizitCnt == 1 && is_finish == 2 ) {
				this.formStatus = 'edit';
				sw.swMsg.alert('Ошибка', 'Сохранение закрытого ТАП по заболеванию с одним посещением невозможно', function() {
					base_form.findField('EvnPLStom_IsFinish').focus(true);
				});
				return false;
			}
			if ( otherVizitCnt == 1 && is_finish == 1 ) {
				this.formStatus = 'edit';
				sw.swMsg.alert('Ошибка', 'Сохранение незакрытого ТАП невозможно', function() {
					base_form.findField('EvnPLStom_IsFinish').focus(true);
				});
				return false;
			}
		}

		var checkOnEvnPLStomIsFinish = this.checkOnEvnPLStomIsFinish();

		if ( !Ext.isEmpty(checkOnEvnPLStomIsFinish) ) {
			this.formStatus = 'edit';
			sw.swMsg.alert('Ошибка', checkOnEvnPLStomIsFinish, function() {
				base_form.findField('EvnPLStom_IsFinish').focus(true);
			});
			return false;
		}

		if ( is_finish == 2 ) {
			if ( (getRegionNick() != 'kareliya' && Ext.isEmpty(evn_pl_stom_ukl)) || evn_pl_stom_ukl < 0 || evn_pl_stom_ukl > 1 ) {
				sw.swMsg.show({
					buttons: Ext.Msg.OK,
					fn: function() {
						this.formStatus = 'edit';
						evn_pl_stom_form.findField('EvnPLStom_UKL').focus(false);
					}.createDelegate(this),
					icon: Ext.Msg.WARNING,
					msg: 'Проверьте правильность заполнения поля УКЛ.' + (getRegionNick() != 'kareliya' ? ' При законченном случае УКЛ должно быть заполнено' : ''),
					title: ERR_INVFIELDS_TIT
				});
				return false;
			}

			if ( !result_class_id ) {
				sw.swMsg.show({
					buttons: Ext.Msg.OK,
					fn: function() {
						this.formStatus = 'edit';
						evn_pl_stom_form.findField('ResultClass_id').markInvalid();
						evn_pl_stom_form.findField('ResultClass_id').focus(false);
					}.createDelegate(this),
					icon: Ext.Msg.WARNING,
					msg: 'При законченном случае поле "Результат лечения" должно быть заполнено',
					title: ERR_INVFIELDS_TIT
				});
				return false;
			}

			if ( !result_desease_type_id && getRegionNick().inlist(['adygeya', 'vologda','buryatiya','kareliya','krasnoyarsk','ekb','penza','yakutiya','yaroslavl']) ) {
				sw.swMsg.show({
					buttons: Ext.Msg.OK,
					fn: function() {
						this.formStatus = 'edit';
						evn_pl_stom_form.findField('ResultDeseaseType_id').markInvalid();
						evn_pl_stom_form.findField('ResultDeseaseType_id').focus(false);
					}.createDelegate(this),
					icon: Ext.Msg.WARNING,
					msg: 'При законченном случае поле "Исход" должно быть заполнено',
					title: ERR_INVFIELDS_TIT
				});
				return false;
			}

			if ( this.formMode == 'morbus' && getRegionNick() == 'perm' ) {
				// Проверки для случая по заболеванию
			}
			else {
				if ( !options || !options.ignoreDiagCountCheck ) {
					var diag_exists = false;

					evn_vizit_pl_stom_store.each(function(record) {
						if ( !Ext.isEmpty(record.get('Diag_id')) ) {
							diag_exists = true;
						}
					});

					if ( !diag_exists ) {
						sw.swMsg.show({
							buttons: Ext.Msg.OK,
							fn: function() {
								this.formStatus = 'edit';
								this.findById('EPLStomEF_EvnVizitPLStomGrid').getView().focusRow(0);
								this.findById('EPLStomEF_EvnVizitPLStomGrid').getSelectionModel().selectFirstRow();
							}.createDelegate(this),
							icon: Ext.Msg.WARNING,
							msg: 'Законченный случай лечения должен иметь хотя бы один диагноз',
							title: ERR_INVFIELDS_TIT
						});
						return false;
					}
				}
			}
		}

		var diag_fid = base_form.findField('Diag_fid').getValue();
		if (this.formMode == 'morbus' && !Ext.isEmpty(diag_fid) && (!options || !options.ignoreDiagFCheck)) {
			var is_check = false;

			if (!Ext.isEmpty(base_form.findField('Diag_did').getValue()) && base_form.findField('Diag_did').getValue() == diag_fid) {
				is_check = true;
			}

			if (!is_check) {
				var evn_diag_pl_stom_store = this.findById('EPLStomEF_EvnDiagPLStomGrid').getStore();
				evn_diag_pl_stom_store.each(function (evndiag) {
					log('azaza', evndiag.get('Diag_id'), diag_fid);
					if (evndiag.get('Diag_id') == diag_fid) {
						is_check = true;
						return false;
					}
				});
			}

			if (!is_check) {
				sw.swMsg.show({
					buttons: Ext.Msg.YESNO,
					fn: function (buttonId) {
						this.formStatus = 'edit';
						if (buttonId == 'yes') {
							options.ignoreDiagFCheck = true;
							this.doSave(options);
						}
					}.createDelegate(this),
					icon: Ext.Msg.QUESTION,
					msg: 'Предварительный диагноз не совпадает ни с одним из диагнозов, установленных в заболеваниях или с диагнозом направившего учреждения. Продолжить сохранение?',
					title: 'Вопрос'
				});
				return false;
			}
		}

		var diag_lid = base_form.findField('Diag_lid').getValue();
		if (this.formMode == 'morbus' && !Ext.isEmpty(diag_lid) && (!options || !options.ignoreDiagLCheck)) {
			var is_check = false;
			evn_vizit_pl_stom_store.each(function(vizit) {
				if (vizit.get('Diag_id') == diag_lid) {
					is_check = true;
					return false;
				}
			});
			if (base_form.findField('Diag_did') == diag_lid) {
				is_check = true;
			}
			if (!is_check) {
				sw.swMsg.show({
					buttons: Ext.Msg.YESNO,
					fn: function(buttonId) {
						this.formStatus = 'edit';
						if (buttonId == 'yes') {
							options.ignoreDiagLCheck = true;
							this.doSave(options);
						}
					}.createDelegate(this),
				icon: Ext.Msg.QUESTION,
					msg: 'Заключительный диагноз не совпадает ни с одним из диагнозов, установленных в посещениях. Продолжить сохранение?',
					title: 'Вопрос'
				});
				return false;
			}
		}

		//http://redmine.swan.perm.ru/issues/20417
		// Отключил, ибо только для НЕ стоматологического https://redmine.swan.perm.ru/issues/65430
		if ( false && getGlobalOptions().region && getGlobalOptions().region.nick == 'ufa' ) {
			// https://redmine.swan.perm.ru/issues/15258
			// Проверяем, чтобы для посещения по заболеванию случай был незакончен, а для профилактического - закончен

			// https://redmine.swan.perm.ru/issues/17388
			// Для некоторых отделений допускается сохранение законченного случая с одним посещением по заболеванию

			var isProfVizit = false;
			var isSpecialCase = false;
			var morbusVizitCnt = 0;


			this.findById('EPLStomEF_EvnVizitPLStomGrid').getStore().each(function(rec) {
				/***/
				/*if ( rec.get('LpuUnitSet_Code').toString().inlist([ '22112', '22105', '22119', '5058', '140', '114' ]) ) {
					isSpecialCase = true;
				}*/

				if ( rec.get('UslugaComplex_Code') && rec.get('UslugaComplex_Code').length == 6 ) {
					if ( isProphylaxisVizitOnly(rec.get('UslugaComplex_Code')) ) {
						isProfVizit = true;
					} else if ( isMorbusVizitOnly(rec.get('UslugaComplex_Code')) ) {
						morbusVizitCnt = morbusVizitCnt + 1;
					}

					// https://redmine.swan.perm.ru/issues/18168
					if ( /*!Ext.isEmpty(getGlobalOptions().lpu_id)
						&& getGlobalOptions().lpu_id.toString().inlist([ '77', '78', '79', '80', '85', '87' ])
						&&*/ rec.get('UslugaComplex_Code').substr(rec.get('UslugaComplex_Code').length - 3, 3) == '871'
					) {
						isSpecialCase = true;
					}
				}
			});

			if ( isProfVizit == true && is_finish != 2 ) {
				this.formStatus = 'edit';
				sw.swMsg.alert('Ошибка', 'Для профилактического/консультативного посещения должен быть указан признак окончания случая лечения и результат лечения', function() {
					base_form.findField('EvnPLStom_IsFinish').focus(true);
				});
				return false;
			}
			/*else if ( morbusVizitCnt == 1 && is_finish == 2 && isSpecialCase == false ) {
				this.formStatus = 'edit';
				sw.swMsg.alert('Ошибка', 'Сохранение закрытого ТАП по заболеванию с одним посещением невозможно');
				return false;
			}*/

		}

		if (getRegionNick() == 'vologda' && is_finish == 2){
			var grid = this.findById('EPLStomEF_EvnVizitPLStomGrid');

			var controlDate = new Date(2019, 7, 1);
			var evnPLStom_disDate = base_form.findField('EvnPLStom_disDate').getValue();
			evnPLStom_disDate = Date.parseDate(evnPLStom_disDate, 'd.m.Y');

			if(evnPLStom_disDate >= controlDate) {
				var gridStore = grid.getStore();
				var recFirst = gridStore.getAt(0);
				var flagProfile = false;
				if(grid.getStore().getCount() > 1 && recFirst.get('LpuSectionProfile_id')){
					var firstLpuSectionProfile_id = recFirst.get('LpuSectionProfile_id');
					var arrNotControlProfileCode = [];
					var arrControlProfileCode = [];

					grid.getStore().each(function(rec) {
						if(!rec.get('LpuSectionProfile_Code').inlist(getGlobalOptions().exceptionprofiles)) {
							flagProfile = true;
							if(arrNotControlProfileCode.indexOf(rec.get('LpuSectionProfile_Code'))<0) arrNotControlProfileCode.push(rec.get('LpuSectionProfile_Code'));
						}else{
							arrControlProfileCode.push(rec.get('LpuSectionProfile_Code'));
						}
					});
					if(flagProfile && arrControlProfileCode.length > 0 && arrNotControlProfileCode.length == 1){
						// есть одно или более посещений, в которых указаны профили «97», «57», «58», «42», «68», «3», «136»
						// И в остальных посещениях указан одинаковый профиль отделения, отличный от профилей «97», «57», «58», «42», «68», «3», «136»
						flagProfile = false;
					}

					if(flagProfile){
						flagProfile = false;
						grid.getStore().each(function(rec) {
							if(firstLpuSectionProfile_id != rec.get('LpuSectionProfile_id')) flagProfile = true;
						});
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

		params = this.panelEvnDirectionAll.onBeforeSubmit(this, params);
		if (!params) {
			return false;
		}

		if ( base_form.findField('LeaveType_fedid').disabled ) {
			params.LeaveType_fedid = base_form.findField('LeaveType_fedid').getValue();
		}

		if ( base_form.findField('ResultDeseaseType_fedid').disabled ) {
			params.ResultDeseaseType_fedid = base_form.findField('ResultDeseaseType_fedid').getValue();
		}

		if ( base_form.findField('Diag_fid').disabled ) {
			params.Diag_fid = base_form.findField('Diag_fid').getValue();
		}

		if ( base_form.findField('Diag_lid').disabled ) {
			params.Diag_lid = base_form.findField('Diag_lid').getValue();
		}

		params.isAutoCreate = (options && typeof options.openChildWindow == 'function' && this.action == 'add') ? 1 : 0;
		params.ignoreParentEvnDateCheck = (!Ext.isEmpty(options.ignoreParentEvnDateCheck) && options.ignoreParentEvnDateCheck === 1) ? 1 : 0;
		params.ignoreMesUslugaCheck = (!Ext.isEmpty(options.ignoreMesUslugaCheck) && options.ignoreMesUslugaCheck === 1) ? 1 : 0;
		params.ignoreKsgInMorbusCheck = (!Ext.isEmpty(options.ignoreKsgInMorbusCheck) && options.ignoreKsgInMorbusCheck === 1) ? 1 : 0;
		params.ignoreUetSumInNonMorbusCheck = (!Ext.isEmpty(options.ignoreUetSumInNonMorbusCheck) && options.ignoreUetSumInNonMorbusCheck === 1) ? 1 : 0;
		params.ignoreCheckEvnUslugaChange = (!Ext.isEmpty(options.ignoreCheckEvnUslugaChange) && options.ignoreCheckEvnUslugaChange === 1) ? 1 : 0;
		//params.ignoreNoExecPrescr = (options && options.ignoreNoExecPrescr) ? options.ignoreNoExecPrescr : null;
		params.ignoreNoExecPrescr = 1;
		
		var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Подождите, идет сохранение талона..." });
		loadMask.show();

		evn_pl_stom_form.submit({
			params: params,
			failure: function(result_form, action) {
				this.formStatus = 'edit';
				loadMask.hide();

				if ( action.result ) {
					if (action.result.Alert_Msg) {
						sw.swMsg.show({
							buttons: Ext.Msg.YESNO,
							fn: function(buttonId, text, obj) {
								if ( buttonId == 'yes' ) {
									switch (true) {
										case (197641 == action.result.Error_Code):
											options.ignoreNoExecPrescr = 1;
											break;
										case (109 == action.result.Error_Code):
											options.ignoreParentEvnDateCheck = 1;
											break;
										case (114 == action.result.Error_Code):
											options.ignoreMesUslugaCheck = 1;
											break;
										case (119 == action.result.Error_Code):
											options.ignoreKsgInMorbusCheck = 1;
											break;
										case (129 == action.result.Error_Code):
											options.ignoreUetSumInNonMorbusCheck = 1;
											break;
										case (130 == action.result.Error_Code):
											options.ignoreCheckEvnUslugaChange = 1;
											break;
									}

									this.doSave(options);
								} else {
									switch (true) {
										case (197641 == action.result.Error_Code):
											base_form.findField('EvnPLStom_IsFinish').setValue(1);
											break;
									}
								}
							}.createDelegate(this),
							icon: Ext.MessageBox.QUESTION,
							msg: action.result.Alert_Msg,
							title: 'Продолжить сохранение?'
						});
					} else if ( action.result.Error_Msg ) {
						sw.swMsg.alert('Ошибка', action.result.Error_Msg);

						if ( 191 == action.result.Error_Code ) {
							base_form.findField('EvnPLStom_IsFinish').setValue(1);
							base_form.findField('EvnPLStom_IsFinish').fireEvent('change', base_form.findField('EvnPLStom_IsFinish'), base_form.findField('EvnPLStom_IsFinish').getValue());
						}
					}
					else {
						sw.swMsg.alert('Ошибка', 'При сохранении произошли ошибки [Тип ошибки: 1]');
					}
				}
			}.createDelegate(this),
			success: function(result_form, action) {
				this.formStatus = 'edit';
				loadMask.hide();

				if ( action.result ) {
					if ( action.result.EvnPLStom_id ) {
						var evn_pl_stom_id = action.result.EvnPLStom_id;

						evn_pl_stom_form.findField('EvnPLStom_id').setValue(evn_pl_stom_id);

						if ( options && typeof options.openChildWindow == 'function' && this.action == 'add' ) {
							options.openChildWindow();
						}
						else {
							var setDate, disDate;
							var person_information = this.findById('EPLStomEF_PersonInformationFrame');
							var response = {};
							var diag_code = '', diag_name = '', lpusection_name = '', medpersonal_fio = '';

							evn_vizit_pl_stom_store.each(function(record) {
								if ( typeof setDate != 'object' || record.get('EvnVizitPLStom_setDate') <= setDate ) {
									setDate = record.get('EvnVizitPLStom_setDate');
								}

								if ( typeof disDate != 'object' || record.get('EvnVizitPLStom_setDate') >= disDate ) {
									disDate = record.get('EvnVizitPLStom_setDate');
									lpusection_name = record.get('LpuSection_Name');
									medpersonal_fio = record.get('MedPersonal_Fio');

									if ( !Ext.isEmpty(record.get('Diag_Code')) ) {
										diag_code = record.get('Diag_Code');
										diag_name = record.get('Diag_Name');
									}
								}
							});

							response.Diag_Name = (!Ext.isEmpty(diag_code) ? diag_code + '. ' + diag_name : '');
							response.EvnPLStom_disDate = disDate;
							response.EvnPLStom_id = evn_pl_stom_id;
							response.EvnPLStom_IsFinish = evn_pl_stom_form.findField('EvnPLStom_IsFinish').getFieldValue('YesNo_Name');
							response.EvnPLStom_NumCard = evn_pl_stom_form.findField('EvnPLStom_NumCard').getValue();
							response.EvnPLStom_setDate = setDate;
							response.EvnPLStom_VizitCount = evn_vizit_pl_stom_store.getCount();
							response.LpuSection_Name = lpusection_name;
							response.MedPersonal_Fio = medpersonal_fio;
							response.Person_Birthday = person_information.getFieldValue('Person_Birthday');
							response.Person_Firname = person_information.getFieldValue('Person_Firname');
							response.Person_id = evn_pl_stom_form.findField('Person_id').getValue();
							response.Person_Secname = person_information.getFieldValue('Person_Secname');
							response.Person_Surname = person_information.getFieldValue('Person_Surname');
							response.PersonEvn_id = evn_pl_stom_form.findField('PersonEvn_id').getValue();
							response.Server_id = evn_pl_stom_form.findField('Server_id').getValue();

							if ( base_form.findField('EvnCostPrint_IsNoPrint').getValue() == 2 ) {
								response.EvnCostPrint_IsNoPrintText = 'Отказ от справки';
							}
							else if ( base_form.findField('EvnCostPrint_IsNoPrint').getValue() == 1 ) {
								response.EvnCostPrint_IsNoPrintText = 'Справка выдана';
							}
							else {
								response.EvnCostPrint_IsNoPrintText = '';
							}

							response.EvnCostPrint_setDT = base_form.findField('EvnCostPrint_setDT').getValue();

							this.callback({ evnPLStomData: response });

							if ( options && options.print == true ) {
								printEvnPL({
									type: 'EvnPLStom',
									EvnPL_id: evn_pl_stom_id
								});

								this.action = 'edit';
								this.setTitle(this.baseTitle + ': ' + FRM_ACTION_EDIT);
							}
							else {
								this.hide();
							}
						}
					}
					else {
						if ( action.result.Error_Msg ) {
							sw.swMsg.alert('Ошибка', action.result.Error_Msg);
						}
						else {
							sw.swMsg.alert('Ошибка', 'При сохранении произошли ошибки [Тип ошибки: 3]');
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
		var base_form = this.findById('EvnPLStomEditForm').getForm();
		var form_fields = ['Diag_did',
			'DirectClass_id',
			'DirectType_id',
			'EvnPLStom_IsFinish',
			'EvnPLStom_IsSan',
			'EvnPLStom_IsUnlaw',
			'EvnPLStom_IsUnport',
			'EvnPLStom_NumCard',
			'EvnPLStom_UKL',
			'PrehospDirect_id',
			'PrehospTrauma_id',
			'ResultClass_id',
			'ResultDeseaseType_id',
			'SanationStatus_id'];
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

		if (this.onEnableEdit && typeof this.onEnableEdit == 'function') {
			this.onEnableEdit(enable);
		}
	},
	filterResultClassCombo: function() {
		var base_form = this.findById('EvnPLStomEditForm').getForm();

		// фильтрация комбо ResultClass в зависимости от даты последнего посещения, либо текущей даты, если нет посещений.
		var lastEvnVizitPLStomDate;
		var ResultClass_id = base_form.findField('ResultClass_id').getValue();

		base_form.findField('ResultClass_id').clearValue();
		base_form.findField('ResultClass_id').getStore().clearFilter();

		this.findById('EPLStomEF_EvnVizitPLStomGrid').getStore().each(function(record) {
			if ( Ext.isEmpty(lastEvnVizitPLStomDate) || (!Ext.isEmpty(record.get('EvnVizitPLStom_setDate')) && record.get('EvnVizitPLStom_setDate') <= lastEvnVizitPLStomDate) ) {
				lastEvnVizitPLStomDate = record.get('EvnVizitPLStom_setDate');
			}
		});

		if ( Ext.isEmpty(lastEvnVizitPLStomDate) ) {
			lastEvnVizitPLStomDate = new Date();
		}

		var xdate = new Date(2016, 0, 1);

		base_form.findField('ResultClass_id').getStore().filterBy(function(rec) {
			return (
				rec.get('ResultClass_id') == ResultClass_id
				|| (
					(Ext.isEmpty(rec.get('ResultClass_begDT')) || rec.get('ResultClass_begDT') <= lastEvnVizitPLStomDate)
					&& (Ext.isEmpty(rec.get('ResultClass_endDT')) || rec.get('ResultClass_endDT') >= lastEvnVizitPLStomDate)
					&& (
						!rec.get('ResultClass_Code')
						|| (getRegionNick() == 'penza' && rec.get('ResultClass_Code').inlist(['301','302','303','305']))
						|| (
							getRegionNick() != 'penza'
							&& (
								!rec.get('ResultClass_Code').inlist(['6','7'])
								|| getRegionNick() != 'perm'
								|| lastEvnVizitPLStomDate < xdate
							)
						)
					)
				)
			);
		});

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
		
		var base_form = this.findById('EvnPLStomEditForm').getForm();
		this.findById('EPLStomEF_EvnVizitPLStomGrid').getStore().each(function(rec) {
			base_form.findField('MedicalStatus_id').hideContainer();
			if ( rec.get('VizitType_SysNick') && rec.get('VizitType_SysNick') == 'disp' ) {
				base_form.findField('MedicalStatus_id').showContainer();
			}
		});
	},
	setDiagConcComboVisible: function() {
		var base_form = this.findById('EvnPLStomEditForm').getForm();
		var lastEvnVizitPLStomDate, lastDiagCode;
		var Diag_lid_Code = base_form.findField('Diag_lid').getFieldValue('Diag_Code');
		var isFinish = base_form.findField('EvnPLStom_IsFinish').getValue();

		this.findById('EPLStomEF_EvnVizitPLStomGrid').getStore().each(function(record) {
			if ( Ext.isEmpty(lastEvnVizitPLStomDate) || (!Ext.isEmpty(record.get('EvnVizitPLStom_setDate')) && record.get('EvnVizitPLStom_setDate') >= lastEvnVizitPLStomDate) ) {
				lastDiagCode = record.get('Diag_Code');
				lastEvnVizitPLStomDate = record.get('EvnVizitPLStom_setDate');
			}
		});

	/*	if ( this.formMode == 'morbus' && base_form.findField('EvnPLStom_IsFinish').getValue() == 2 && !Ext.isEmpty(lastDiagCode) && lastDiagCode.toString().substr(0, 1).inlist([ 'S', 'T' ]) ) {
			base_form.findField('Diag_concid').setContainerVisible(true);
		}
		else {
			base_form.findField('Diag_concid').clearValue();
			base_form.findField('Diag_concid').setContainerVisible(false);
		}*/

		base_form.findField('Diag_concid').setAllowBlank(true);
		if ( this.formMode == 'morbus' && !Ext.isEmpty(Diag_lid_Code) && isFinish==2 && Diag_lid_Code.toString().substr(0, 1).inlist([ 'S', 'T' ]) ) {
			base_form.findField('Diag_concid').setContainerVisible(true);
			base_form.findField('Diag_concid').setAllowBlank(false);
		}
		else {
			base_form.findField('Diag_concid').clearValue();
			base_form.findField('Diag_concid').setContainerVisible(false);
		}

	},
	setInterruptLeaveTypeVisible: function() {
		var base_form = this.findById('EvnPLStomEditForm').getForm();

		var lastEvnVizitPLStomDate;

		this.findById('EPLStomEF_EvnVizitPLStomGrid').getStore().each(function(record) {
			if ( Ext.isEmpty(lastEvnVizitPLStomDate) || (!Ext.isEmpty(record.get('EvnVizitPLStom_setDate')) && record.get('EvnVizitPLStom_setDate') >= lastEvnVizitPLStomDate) ) {
				lastEvnVizitPLStomDate = record.get('EvnVizitPLStom_setDate');
			}
		});

		var xdate = new Date(2016, 0, 1); // Поле видимо (если дата посещения 01-01-2016 или позже)
		if ( !Ext.isEmpty(lastEvnVizitPLStomDate) && lastEvnVizitPLStomDate >= xdate) {
			base_form.findField('InterruptLeaveType_id').showContainer();
		} else {
			base_form.findField('InterruptLeaveType_id').hideContainer();
			base_form.findField('InterruptLeaveType_id').clearValue();
		}
	},
	formMode: null,
	setFormMode: function() {
		var base_form = this.findById('EvnPLStomEditForm').getForm();

		var
			mode,
			setDate = base_form.findField('EvnPLStom_setDate').getValue() || getValidDT(getGlobalOptions().date, ''),
			xDate = sw.Promed.EvnPL.getEvnPLStomNewBegDate(),
			DateX20190601 = new Date(2019, 5, 1);

		if ( setDate >= xDate ) {
			mode = 'morbus';
		}
		else {
			mode = 'classic';
		}

		/*if ( this.formMode == mode ) {
			return false;
		}*/

		this.formMode = mode;

		var
			diagPLStomCM = this.findById('EPLStomEF_EvnDiagPLStomGrid').getColumnModel(),
			uslugaCM = this.findById('EPLStomEF_EvnUslugaGrid').getColumnModel(),
			uslugaGridToolbar = this.findById('EPLStomEF_EvnUslugaGrid').getTopToolbar(),
			vizitCM = this.findById('EPLStomEF_EvnVizitPLStomGrid').getColumnModel(),
			region = getRegionNick();

		switch ( this.formMode ) {
			case 'morbus':
				// Вариант с заболеваниями
				this.findById('EPLStomEF_EvnDiagPLStomPanel').show();

				diagPLStomCM.setHidden(4, region != 'penza' || setDate >= DateX20190601);

				this.findById('EPLStomEF_EvnUslugaPanel').setTitle('4. Услуги');
				this.findById('EPLStomEF_EvnStickPanel').setTitle('5. Нетрудоспособность');
				this.findById('EPLStomEF_ResultPanel').setTitle('6. Результат');
				this.findById('EPLStomEF_EvnDrugPanel').setTitle('7. Использование медикаментов');

				this.findById('EPLStomEF_EvnUslugaGrid').getStore().baseParams.allowVizitCode = 1;

				if ( uslugaCM ) {
					uslugaCM.setHidden(6, !region.inlist(['perm']) && !sw.Promed.EvnVizitPLStom.isSupportVizitCode());
					uslugaCM.setHidden(7, !region.inlist(['perm','vologda']));
					uslugaCM.setHidden(8, !region.inlist(['perm']));
					uslugaCM.setHidden(9, !region.inlist(['perm','vologda']));
				}

				if ( vizitCM ) {
					vizitCM.setHidden(8, true);
				}

				uslugaGridToolbar.items.items[1].hide();
				uslugaGridToolbar.items.items[3].hide();

				base_form.findField('Diag_fid').showContainer();
				base_form.findField('Diag_lid').showContainer();
				//base_form.findField('PrehospTrauma_id').showContainer();
				//base_form.findField('EvnPLStom_IsUnlaw').showContainer();
				//base_form.findField('EvnPLStom_IsUnport').showContainer();
			break;

			default:
				// Вариант без заболеваний
				this.findById('EPLStomEF_EvnDiagPLStomPanel').hide();


				this.findById('EPLStomEF_EvnUslugaPanel').setTitle('3. Услуги');
				this.findById('EPLStomEF_EvnStickPanel').setTitle('4. Нетрудоспособность');
				this.findById('EPLStomEF_ResultPanel').setTitle('5. Результат');
				this.findById('EPLStomEF_EvnDrugPanel').setTitle('6. Использование медикаментов');

				this.findById('EPLStomEF_EvnUslugaGrid').getStore().baseParams.allowVizitCode = 0;

				if ( uslugaCM ) {
					uslugaCM.setHidden(6, true);
					uslugaCM.setHidden(7, true);
					uslugaCM.setHidden(8, true);
					uslugaCM.setHidden(9, true);
				}

				if ( vizitCM ) {
					vizitCM.setHidden(8, false);
				}

				if ( getRegionNick() == 'ufa' ) {
					uslugaGridToolbar.items.items[0].show();
				}

				uslugaGridToolbar.items.items[1].show();
				uslugaGridToolbar.items.items[3].show();

				base_form.findField('Diag_fid').hideContainer();
				base_form.findField('Diag_lid').hideContainer();
				//base_form.findField('PrehospTrauma_id').hideContainer();
				//base_form.findField('EvnPLStom_IsUnlaw').hideContainer();
				//base_form.findField('EvnPLStom_IsUnport').hideContainer();
			break;
		}
	},
	formStatus: 'edit',
	getEvnPLStomNumber: function() {
		var evnpl_stom_num_field = this.findById('EvnPLStomEditForm').getForm().findField('EvnPLStom_NumCard');
		var grid = this.findById('EPLStomEF_EvnVizitPLStomGrid');

		var params = {};

		if ( grid.getStore().getCount() > 0 ) {
			grid.getStore().each(function(rec) {
				if (
					!Ext.isEmpty(rec.get('EvnVizitPLStom_id')) && typeof rec.get('EvnVizitPLStom_setDate') == 'object'
					&& (Ext.isEmpty(params.year) || params.year > rec.get('EvnVizitPLStom_setDate').format('Y'))
				) {
					params.year = rec.get('EvnVizitPLStom_setDate').format('Y');
				}
			});
		}

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Получение номера талона..." });
		loadMask.show();

		Ext.Ajax.request({
			callback: function(options, success, response) {
				loadMask.hide();

				if ( success ) {
					var response_obj = Ext.util.JSON.decode(response.responseText);
					evnpl_stom_num_field.setValue(response_obj.EvnPLStom_NumCard);
					//evnpl_stom_num_field.focus(true);
				}
				else {
					sw.swMsg.alert('Ошибка', 'Ошибка при определении номера талона');
				}
			},
			params: params,
			url: '/?c=EvnPLStom&m=getEvnPLStomNumber'
		});
	},
	height: 550,
	id: 'EvnPLStomEditWindow',
	initComponent: function() {
		var current_window = this;
		var win = this;

		this.panelEvnDirectionAll = new sw.Promed.EvnDirectionAllPanel({
			startTabIndex: TABINDEX_EPLSTOMEF + 2,
			checkOtherLpuDirection: function() {
				var base_form = win.findById('EvnPLStomEditForm').getForm();

				if (getRegionNick() == 'perm') {
					var org_id = base_form.findField('Org_did').getValue();
					var date = Ext.util.Format.date(base_form.findField('EvnDirection_setDate').getValue(), 'd.m.Y');

					if (base_form.findField('PrehospDirect_id').getFieldValue('PrehospDirect_Code') == 2) {
						if (Ext.isEmpty(org_id)) {
							base_form.findField('Diag_did').setAllowBlank(true);
							base_form.findField('EvnDirection_Num').setAllowBlank(true);
							base_form.findField('EvnDirection_setDate').setAllowBlank(true);
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
			useCase: 'choose_for_evnplstom',
			personPanelId: 'EPLStomEF_PersonInformationFrame',
			personFieldName: 'Person_id',
			medStaffFactFieldName: null,
			fromLpuFieldName: 'Lpu_fid',
			fieldIsWithDirectionName: 'EvnPLStom_IsWithoutDirection',
			buttonSelectId: 'EPLStomEF_EvnDirectionSelectButton',
			fieldPrehospDirectName: 'PrehospDirect_id',
			fieldLpuSectionName: 'LpuSection_did',
			fieldOrgName: 'Org_did',
			fieldNumName: 'EvnDirection_Num',
			fieldSetDateName: 'EvnDirection_setDate',
			fieldDiagName: 'Diag_did',
			fieldDiagPreidName:'Diag_preid',
			fieldDiagFName: 'Diag_fid',
			//fieldTimaTableName: 'TimetableGraf_id',
			//fieldEvnPrescrName: 'EvnPrescr_id',
			fieldIdName: 'EvnDirection_id',
			fieldIsAutoName: 'EvnDirection_IsAuto',
			fieldIsExtName: 'EvnDirection_IsReceive',
			parentSetDateFieldName: null,
			nextFieldName: 'PrehospTrauma_id'
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
					var base_form = this.findById('EvnPLStomEditForm').getForm();

					/*if ( !this.findById('EPLStomEF_DirectPanel').collapsed && this.action != 'view' ) {
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
					else*/ if ( !this.findById('EPLStomEF_ResultPanel').collapsed && this.action != 'view' ) {
						if(base_form.findField('ResultDeseaseType_fedid').isVisible()){
							base_form.findField('ResultDeseaseType_fedid').focus();
						} else {
							base_form.findField('EvnPLStom_UKL').focus(true);
						}
					}
					else if ( !this.findById('EPLStomEF_EvnStickPanel').collapsed ) {
						this.findById('EPLStomEF_EvnStickGrid').getView().focusRow(0);
						this.findById('EPLStomEF_EvnStickGrid').getSelectionModel().selectFirstRow();
					}
					else if ( !this.findById('EPLStomEF_EvnUslugaPanel').collapsed ) {
						this.findById('EPLStomEF_EvnUslugaGrid').getView().focusRow(0);
						this.findById('EPLStomEF_EvnUslugaGrid').getSelectionModel().selectFirstRow();
					}
					else if ( !this.findById('EPLStomEF_EvnVizitPLStomPanel').collapsed ) {
						this.findById('EPLStomEF_EvnVizitPLStomGrid').getView().focusRow(0);
						this.findById('EPLStomEF_EvnVizitPLStomGrid').getSelectionModel().selectFirstRow();
					}
					else if ( !this.findById('EPLStomEF_DirectInfoPanel').collapsed && this.action != 'view' ) {
						base_form.findField('EvnPLStom_IsUnport').focus(true);
					}
					else if ( this.action == 'view' ) {
						this.buttons[this.buttons.length - 1].focus();
					}
					else {
						base_form.findField('EvnPLStom_NumCard').focus(true);
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
				tabIndex: TABINDEX_EPLSTOMEF + 24,
				text: BTN_FRMSAVE
			}, {
				handler: function() {
					this.printEvnPLStom();
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
				tabIndex: TABINDEX_EPLSTOMEF + 25,
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
					this.buttons[1].focus();
				}.createDelegate(this),
				onTabAction: function () {
					if ( this.action != 'view' ) {
						this.findById('EvnPLStomEditForm').getForm().findField('EvnPLStom_NumCard').focus(true)
					}
					else {
						if ( !this.findById('EPLStomEF_EvnVizitPLStomPanel').collapsed ) {
							this.findById('EPLStomEF_EvnVizitPLStomGrid').getView().focusRow(0);
							this.findById('EPLStomEF_EvnVizitPLStomGrid').getSelectionModel().selectFirstRow();
						}
						else if ( !this.findById('EPLStomEF_EvnUslugaPanel').collapsed ) {
							this.findById('EPLStomEF_EvnUslugaGrid').getView().focusRow(0);
							this.findById('EPLStomEF_EvnUslugaGrid').getSelectionModel().selectFirstRow();
						}
						else if ( !this.findById('EPLStomEF_EvnStickPanel').collapsed ) {
							this.findById('EPLStomEF_EvnStickGrid').getView().focusRow(0);
							this.findById('EPLStomEF_EvnStickGrid').getSelectionModel().selectFirstRow();
						}
						else {
							this.buttons[1].focus();
						}
					}
				}.createDelegate(this),
				tabIndex: TABINDEX_EPLSTOMEF + 26,
				text: BTN_FRMCANCEL
			}],
			items: [ new sw.Promed.PersonInfoPanel({
				button1OnHide: function() {
					if ( this.action == 'view' ) {
						this.buttons[this.buttons.length - 1].focus();
					}
					else {
						this.findById('EvnPLStomEditForm').getForm().findField('EvnPLStom_NumCard').focus(true);
					}
				}.createDelegate(this),
				button2Callback: function(callback_data) {
					var form = this.findById('EvnPLStomEditForm');

					form.getForm().findField('PersonEvn_id').setValue(callback_data.PersonEvn_id);
					form.getForm().findField('Server_id').setValue(callback_data.Server_id);
					var p = { Person_id: callback_data.Person_id, Server_id: callback_data.Server_id };
					if (form.PersonEvn_id)
						p.PersonEvn_id = form.PersonEvn_id;
					if (form.Server_id)
						p.Server_id = form.Server_id;

					this.findById('EPLStomEF_PersonInformationFrame').load({ Person_id: callback_data.Person_id, Server_id: callback_data.Server_id });
				}.createDelegate(this),
				button2OnHide: function() {
					this.findById('EPLStomEF_PersonInformationFrame').button1OnHide();
				}.createDelegate(this),
				button3OnHide: function() {
					this.findById('EPLStomEF_PersonInformationFrame').button1OnHide();
				}.createDelegate(this),
				button4OnHide: function() {
					this.findById('EPLStomEF_PersonInformationFrame').button1OnHide();
				}.createDelegate(this),
				button5OnHide: function() {
					this.findById('EPLStomEF_PersonInformationFrame').button1OnHide();
				}.createDelegate(this),
				collapsible: true,
				collapsed: true,
				floatable: false,
				id: 'EPLStomEF_PersonInformationFrame',
				plugins: [ Ext.ux.PanelCollapsedTitle ],
				region: 'north',
				title: '<div>Загрузка...</div>',
				titleCollapse: true
			}),
			new Ext.form.FormPanel({
				autoScroll: true,
				bodyBorder: false,
				bodyStyle: 'padding: 5px 5px 0',
				border: false,
				frame: false,
				id: 'EvnPLStomEditForm',
				labelAlign: 'right',
				labelWidth: 220,
				items: [{
					name: 'accessType',
					xtype: 'hidden'
				},{
					name: 'canCreateVizit',
					value: '',
					xtype: 'hidden'
				}, {
					name:'EvnPLStom_IsPaid',
					xtype:'hidden'
				}, {
					name:'EvnPLStom_IndexRep',
					xtype:'hidden'
				}, {
					name:'EvnPLStom_IndexRepInReg',
					xtype:'hidden'
				}, {
					name: 'EvnPLStom_id',
					xtype: 'hidden'
				}, {
					name: 'EvnPLStom_IsTransit',
					xtype: 'hidden'
				}, {
					name: 'Person_id',
					xtype: 'hidden'
				}, {
					name: 'PersonEvn_id',
					xtype: 'hidden'
				}, {
					name: 'Server_id',
					value: -1,
					xtype: 'hidden'
				}, {
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
					name: 'EvnPLStom_disDate',
					xtype: 'hidden'
				},{
					allowBlank: false,
					fieldLabel: 'Дата начала случая',
					listeners: {
						'change': function(field, newValue, oldValue) {
							this.setFormMode();
							this.checkLpuHasConsPriemVolume();
						}.createDelegate(this)
					},
					name: 'EvnPLStom_setDate',
					plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
					tabIndex: this.tabindex + 0,
					width: 100,
					xtype: 'swdatefield'
				}, {
					border: false,
					layout: 'column',
					items: [{
						border: false,
						layout: 'form',
						items: [{
							allowBlank: false,
							enableKeyEvents: true,
							fieldLabel: '№ талона',
							listeners: {
								'keydown': function(inp, e) {
									switch ( e.getKey() ) {
										case Ext.EventObject.F2:
											e.stopEvent();
											this.getEvnPLStomNumber();
										break;

										case Ext.EventObject.TAB:
											e.stopEvent();

											if ( e.shiftKey == false ) {
												var base_form = this.findById('EvnPLStomEditForm').getForm();

												if ( !this.findById('EPLStomEF_DirectInfoPanel').collapsed && this.action != 'view' ) {
													base_form.findField('PrehospDirect_id').focus(true);
												}
												else if ( !this.findById('EPLStomEF_EvnVizitPLStomPanel').collapsed ) {
													this.findById('EPLStomEF_EvnVizitPLStomGrid').getView().focusRow(0);
													this.findById('EPLStomEF_EvnVizitPLStomGrid').getSelectionModel().selectFirstRow();
												}
												else if ( !this.findById('EPLStomEF_EvnUslugaPanel').collapsed ) {
													this.findById('EPLStomEF_EvnUslugaGrid').getView().focusRow(0);
													this.findById('EPLStomEF_EvnUslugaGrid').getSelectionModel().selectFirstRow();
												}
												else if ( !this.findById('EPLStomEF_EvnStickPanel').collapsed ) {
													this.findById('EPLStomEF_EvnStickGrid').getView().focusRow(0);
													this.findById('EPLStomEF_EvnStickGrid').getSelectionModel().selectFirstRow();
												}
												else if ( !this.findById('EPLStomEF_ResultPanel').collapsed && this.action != 'view' ) {
													base_form.findField('EvnPLStom_IsFinish').focus(true);
												}
												/*else if ( !this.findById('EPLStomEF_DirectPanel').collapsed && this.action != 'view' ) {
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
												this.buttons[this.buttons.length - 1].focus();
											}
										break;
									}
								}.createDelegate(this)
							},
							autoCreate: { tag: "input", type: "text", maxLength: "30", autocomplete: "off" },
							name: 'EvnPLStom_NumCard',
							onTriggerClick: function() {
								this.getEvnPLStomNumber();
							}.createDelegate(this),
							tabIndex: TABINDEX_EPLSTOMEF + 1,
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
							name: 'EvnPLStom_IsCons',
							hideLabel: true,
							boxLabel: langs('Консультативный приём'),
							hidden: true,
							tabIndex: TABINDEX_EPLSTOMEF + 1.5,
							xtype: 'checkbox'
						}]
					}]
				},
				new sw.Promed.SwPrehospTraumaCombo({
					hiddenName: 'PrehospTrauma_id',
					fieldLabel: 'Вид травмы (внешнего воздействия)',
					lastQuery: '',
					listeners: {
						'change': function(combo, newValue, oldValue) {
							var base_form = this.findById('EvnPLStomEditForm').getForm();

							var is_unlaw_combo = base_form.findField('EvnPLStom_IsUnlaw');
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
					tabIndex: TABINDEX_EPLSTOMEF + 8,
					width: 300
				}), {
					border: false,
					layout: 'column',
					items: [{
						border: false,
						layout: 'form',
						width: 300,
						items: [ new sw.Promed.SwYesNoCombo({
							fieldLabel: 'Противоправная',
							hiddenName: 'EvnPLStom_IsUnlaw',
							lastQuery: '',
							tabIndex: TABINDEX_EPLSTOMEF + 9,
							width: 70
						})]
					}, {
						border: false,
						labelWidth: 150,
						layout: 'form',
						items: [ new sw.Promed.SwYesNoCombo({
							fieldLabel: 'Нетранспортабельность',
							hiddenName: 'EvnPLStom_IsUnport',
							lastQuery: '',
							listeners: {
								'keydown': function(inp, e) {
									switch ( e.getKey() ) {
										case Ext.EventObject.TAB:
											var base_form = this.findById('EvnPLStomEditForm').getForm();

											if ( e.shiftKey == false ) {
												e.stopEvent();

												if ( !this.findById('EPLStomEF_EvnVizitPLStomPanel').collapsed ) {
													this.findById('EPLStomEF_EvnVizitPLStomGrid').getView().focusRow(0);
													this.findById('EPLStomEF_EvnVizitPLStomGrid').getSelectionModel().selectFirstRow();
												}
												else if ( !this.findById('EPLStomEF_EvnUslugaPanel').collapsed ) {
													this.findById('EPLStomEF_EvnUslugaGrid').getView().focusRow(0);
													this.findById('EPLStomEF_EvnUslugaGrid').getSelectionModel().selectFirstRow();
												}
												else if ( !this.findById('EPLStomEF_EvnStickPanel').collapsed ) {
													this.findById('EPLStomEF_EvnStickGrid').getView().focusRow(0);
													this.findById('EPLStomEF_EvnStickGrid').getSelectionModel().selectFirstRow();
												}
												else if ( !this.findById('EPLStomEF_ResultPanel').collapsed && this.action != 'view' ) {
													base_form.findField('EvnPLStom_IsFinish').focus(true);
												}
												/*else if ( !this.findById('EPLStomEF_DirectPanel').collapsed && this.action != 'view' ) {
												 base_form.findField('DirectType_id').focus(true);
												 }*/
												else if ( this.action == 'view' ) {
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
							tabIndex: TABINDEX_EPLSTOMEF + 10,
							width: 70
						})]
					}]
				},
				new sw.Promed.Panel({
					autoHeight: true,
					bodyStyle: 'padding-top: 0.5em;',
					border: true,
					collapsible: true,
					id: 'EPLStomEF_DirectInfoPanel',
					layout: 'form',
					listeners: {
						'expand': function(panel) {
							// this.findById('EvnPLStomEditForm').getForm().findField('PrehospDirect_id').focus(true);
						}.createDelegate(this)
					},
					style: 'margin-bottom: 0.5em;',
					title: '1. Данные о направлении',
					items: [
					this.panelEvnDirectionAll, {
						border: false,
						hidden: getRegionNick() != 'kareliya',
						layout: 'form',
						xtype: 'panel',
						items: [{
							hiddenName: 'MedicalCareKind_id',
							allowBlank: getRegionNick() != 'kareliya',
							fieldLabel: 'Медицинская помощь',
							comboSubject: 'MedicalCareKind',
							xtype: 'swcommonsprcombo',
							width: 300
						}]
					}]
				}),
				new sw.Promed.Panel({
					border: true,
					collapsible: true,
					height: 200,
					id: 'EPLStomEF_EvnVizitPLStomPanel',
					layout: 'border',
					listeners: {
						'expand': function(panel) {
							panel.doLayout();
						}.createDelegate(this)
					},
					style: 'margin-bottom: 0.5em;',
					title: '2. Посещения',
					items: [ new Ext.grid.GridPanel({
						autoExpandColumn: 'autoexpand_vizit',
						autoExpandMin: 100,
						border: false,
						columns: [{
							dataIndex: 'EvnVizitPLStom_setDate',
							header: 'Дата посещения',
							hidden: false,
							renderer: Ext.util.Format.dateRenderer('d.m.Y'),
							resizable: false,
							sortable: true,
							width: 100
						}, {
							dataIndex: 'LpuSection_Name',
							header: 'Отделение',
							hidden: false,
							resizable: true,
							sortable: true,
							width: 130
						}, {
							dataIndex: 'MedPersonal_Fio',
							header: 'Врач',
							hidden: false,
							id: 'autoexpand_vizit',
							resizable: true,
							sortable: true
						}, {
							dataIndex: 'UslugaComplex_Name',
							header: 'Код посещения',
							hidden: !(getGlobalOptions().region && getGlobalOptions().region.nick.inlist([ 'ufa' ])),
							resizable: true,
							sortable: true,
							width: 300
						}, {
							dataIndex: 'ServiceType_Name',
							header: 'Место обслуживания',
							hidden: false,
							resizable: true,
							sortable: true,
							width: 130
						}, {
							dataIndex: 'VizitType_Name',
							header: 'Цель посещения',
							hidden: false,
							resizable: true,
							sortable: true,
							width: 130
						},{
							dataIndex: 'VizitType_SysNick',
							hideable: false,
							hidden: true
						}, {
							dataIndex: 'PayType_Name',
							header: 'Вид оплаты',
							hidden: false,
							resizable: true,
							sortable: true,
							width: 130
						}, {
							dataIndex: 'Diag_Code',
							header: 'Диагноз',
							hidden: false,
							resizable: true,
							sortable: true,
							width: 130
						}, {
							dataIndex: 'EvnVizitPLStom_NumGroup',
							header: langs('Группировка'),
							hidden: getRegionNick() != 'vologda',
							sortable: true,
							width: 100
						}, {
							dataIndex: 'TreatmentClass_id',
							hidden: true,
							header: 'Вид обращения'
						}],
						frame: false,
						id: 'EPLStomEF_EvnVizitPLStomGrid',
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

								var grid = this.findById('EPLStomEF_EvnVizitPLStomGrid');

								switch ( e.getKey() ) {
									case Ext.EventObject.DELETE:
										this.deleteEvent('EvnVizitPLStom');
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

											this.openEvnVizitPLStomEditWindow(action);
										} else {
											var params = {};
											params['key_id'] = grid.getSelectionModel().getSelected().data.EvnVizitPLStom_id;
											params['key_field'] = 'EvnVizitPLStom_id';
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

										this.openEvnVizitPLStomEditWindow(action);
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
										var base_form = this.findById('EvnPLStomEditForm').getForm();

										grid.getSelectionModel().clearSelections();
										grid.getSelectionModel().fireEvent('rowselect', grid.getSelectionModel());

										if ( e.shiftKey == false ) {
											if ( !this.findById('EPLStomEF_EvnUslugaPanel').collapsed ) {
												this.findById('EPLStomEF_EvnUslugaGrid').getView().focusRow(0);
												this.findById('EPLStomEF_EvnUslugaGrid').getSelectionModel().selectFirstRow();
											}
											else if ( !this.findById('EPLStomEF_EvnStickPanel').collapsed ) {
												this.findById('EPLStomEF_EvnStickGrid').getView().focusRow(0);
												this.findById('EPLStomEF_EvnStickGrid').getSelectionModel().selectFirstRow();
											}
											else if ( !this.findById('EPLStomEF_ResultPanel').collapsed && this.action != 'view' ) {
												base_form.findField('EvnPLStom_IsFinish').focus(true);
											}
											/*else if ( !this.findById('EPLStomEF_DirectPanel').collapsed && this.action != 'view' ) {
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
											if ( !this.findById('EPLStomEF_DirectInfoPanel').collapsed && this.action != 'view' ) {
												base_form.findField('EvnPLStom_IsUnport').focus(true);
											}
											else if ( this.action == 'view' ) {
												this.buttons[this.buttons.length - 1].focus();
											}
											else {
												base_form.findField('EvnPLStom_NumCard').focus(true);
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
								var
									access_type = 'view',
									action = 'view',
									id = null,
									selected_record = grid.getSelectionModel().getSelected();

								if ( selected_record ) {
									access_type = selected_record.get('accessType');
									id = selected_record.get('EvnVizitPLStom_id');
								}

								if (
									!Ext.isEmpty(id)
									&& (current_window.action != 'view' || current_window.gridAccess != 'view')
									&& access_type == 'edit'
									&& selected_record.get('EvnVizitPLStom_IsSigned') == 1
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

								this.openEvnVizitPLStomEditWindow('edit');
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
									var toolbar = this.findById('EPLStomEF_EvnVizitPLStomGrid').getTopToolbar();

									if ( selected_record ) {
										access_type = selected_record.get('accessType');
										id = selected_record.get('EvnVizitPLStom_id');
									}

									toolbar.items.items[1].disable();
									toolbar.items.items[3].disable();

									if ( id ) {
										toolbar.items.items[2].enable();

										if (
											(current_window.action != 'view' || current_window.gridAccess != 'view')
											&& access_type == 'edit'
											&& selected_record.get('EvnVizitPLStom_IsSigned') == 1
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
										LoadEmptyRow(this.findById('EPLStomEF_EvnVizitPLStomGrid'));
									}

									this.filterResultClassCombo();
									this.setMedicalStatusComboVisible();
									this.setInterruptLeaveTypeVisible();
									this.setDiagFidAndLid();

									this.uetValuesRecount();
								}.createDelegate(this)
							},
							reader: new Ext.data.JsonReader({
								id: 'EvnVizitPLStom_id'
							}, [{
								mapping: 'accessType',
								name: 'accessType',
								type: 'string'
							}, {
								mapping: 'EvnVizitPLStom_id',
								name: 'EvnVizitPLStom_id',
								type: 'int'
							}, {
								mapping: 'EvnVizitPLStom_IsSigned',
								name: 'EvnVizitPLStom_IsSigned',
								type: 'int'
							}, {
								mapping: 'EvnPLStom_id',
								name: 'EvnPLStom_id',
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
								mapping: 'Diag_Code',
								name: 'Diag_Code',
								type: 'string'
							}, {
								mapping: 'Diag_Name',
								name: 'Diag_Name',
								type: 'string'
							},{
								mapping: 'UslugaComplex_Code',
								name: 'UslugaComplex_Code',
								type: 'string'
							},{
								dateFormat: 'd.m.Y',
								mapping: 'EvnVizitPLStom_setDate',
								name: 'EvnVizitPLStom_setDate',
								type: 'date'
							}, {
								mapping: 'MedStaffFact_id',
								name: 'MedStaffFact_id',
								type: 'int'
							}, {
								mapping: 'LpuSection_id',
								name: 'LpuSection_id',
								type: 'int'
							}, , {
								mapping: 'LpuSectionProfile_id',
								name: 'LpuSectionProfile_id',
								type: 'int'
							}, {
								mapping: 'LpuSectionProfile_Code',
								name: 'LpuSectionProfile_Code',
								type: 'string'
							}, {
								mapping: 'MedPersonal_id',
								name: 'MedPersonal_id',
								type: 'int'
							}, {
								mapping: 'PayType_id',
								name: 'PayType_id',
								type: 'int'
							}, {
								mapping: 'LpuSection_Name',
								name: 'LpuSection_Name',
								type: 'string'
							},{
								mapping: 'LpuUnitSet_Code',
								name: 'LpuUnitSet_Code',
								type: 'int'
							},{
								mapping: 'MedPersonal_Fio',
								name: 'MedPersonal_Fio',
								type: 'string'
							}, {
								mapping: 'ServiceType_Name',
								name: 'ServiceType_Name',
								type: 'string'
							},{
								mapping: 'VizitType_SysNick',
								name: 'VizitType_SysNick',
								type: 'string'
							},{
								mapping: 'VizitType_Name',
								name: 'VizitType_Name',
								type: 'string'
							}, {
								mapping: 'PayType_Name',
								name: 'PayType_Name',
								type: 'string'
							}, {
								mapping: 'UslugaComplex_Name',
								name: 'UslugaComplex_Name',
								type: 'string'
							}, {
								mapping: 'EvnVizitPLStom_Uet',
								name: 'EvnVizitPLStom_Uet',
								type: 'float'
							}, {
								mapping: 'EvnVizitPLStom_NumGroup',
								name: 'EvnVizitPLStom_NumGroup',
								type: 'int'
							}, {
								mapping: 'TreatmentClass_id',
								name: 'TreatmentClass_id',
								type: 'int'
							}]),
							url: '/?c=EvnVizit&m=loadEvnVizitPLStomGrid'
						}),
						tbar: new sw.Promed.Toolbar({
							buttons: [{
								handler: function() {
									this.openEvnVizitPLStomEditWindow('add');
								}.createDelegate(this),
								iconCls: 'add16',
								text: BTN_GRIDADD,
								tooltip: BTN_GRIDADD_TIP
							}, {
								handler: function() {
									this.openEvnVizitPLStomEditWindow('edit');
								}.createDelegate(this),
								iconCls: 'edit16',
								text: BTN_GRIDEDIT,
								tooltip: BTN_GRIDEDIT_TIP
							}, {
								handler: function() {
									this.openEvnVizitPLStomEditWindow('view');
								}.createDelegate(this),
								iconCls: 'view16',
								text: BTN_GRIDVIEW,
								tooltip: BTN_GRIDVIEW_TIP
							}, {
								handler: function() {
									this.deleteEvent('EvnVizitPLStom');
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
					id: 'EPLStomEF_EvnDiagPLStomPanel',
					isLoaded: false,
					layout: 'border',
					listeners: {
						'expand': function(panel) {
							if ( panel.isLoaded === false ) {
								panel.isLoaded = true;
								panel.findById('EPLStomEF_EvnDiagPLStomGrid').getStore().load({
									params: {
										rid: this.findById('EvnPLStomEditForm').getForm().findField('EvnPLStom_id').getValue()
									}
								});
							}

							panel.doLayout();
						}.createDelegate(this)
					},
					style: 'margin-bottom: 0.5em;',
					title: '3. Заболевания',
					items: [ new Ext.grid.GridPanel({
						autoExpandColumn: 'autoexpand_evndiagplstom',
						autoExpandMin: 100,
						border: false,
						columns: [{
							dataIndex: 'EvnDiagPLStom_setDate',
							header: 'Дата начала',
							renderer: Ext.util.Format.dateRenderer('d.m.Y'),
							resizable: false,
							sortable: true,
							width: 100
						}, {
							dataIndex: 'EvnDiagPLStom_disDate',
							header: 'Дата окончания',
							renderer: Ext.util.Format.dateRenderer('d.m.Y'),
							resizable: false,
							sortable: true,
							width: 100
						}, {
							dataIndex: 'Diag_Code',
							header: 'Диагноз',
							resizable: false,
							sortable: true,
							width: 100
						}, {
							dataIndex: 'Tooth_Code',
							header: 'Номер зуба',
							resizable: false,
							sortable: true,
							width: 100
						}, {
							dataIndex: 'EvnDiagPLStom_NumGroup',
							header: 'Группировка',
							resizable: false,
							sortable: true,
							width: 80
						}, {
							dataIndex: 'Mes_Code',
							header: 'Код КСГ',
							hidden: getRegionNick() != 'perm',
							resizable: true,
							sortable: true,
							width: 100
						}, {
							dataIndex: 'Mes_Name',
							header: 'Наименование КСГ',
							hidden: getRegionNick() != 'perm',
							id: 'autoexpand_evndiagplstom',
							resizable: true,
							sortable: true
						}],
						frame: false,
						id: 'EPLStomEF_EvnDiagPLStomGrid',
						keys: [{
							key: [
								Ext.EventObject.END,
								Ext.EventObject.ENTER,
								Ext.EventObject.F3,
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

								var grid = Ext.getCmp('EPLStomEF_EvnDiagPLStomGrid');

								switch ( e.getKey() ) {
									case Ext.EventObject.END:
										GridEnd(grid);
									break;

									case Ext.EventObject.ENTER:
									case Ext.EventObject.F3:
										if ( !grid.getSelectionModel().getSelected() ) {
											return false;
										}

										Ext.getCmp('EvnPLStomEditWindow').openEvnDiagPLStomEditWindow('view');
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
										var base_form = this.findById('EvnPLStomEditForm').getForm();

										grid.getSelectionModel().clearSelections();
										grid.getSelectionModel().fireEvent('rowselect', grid.getSelectionModel());

										if ( e.shiftKey == false ) {
											if ( !this.findById('EPLStomEF_EvnStickPanel').collapsed ) {
												this.findById('EPLStomEF_EvnStickGrid').getView().focusRow(0);
												this.findById('EPLStomEF_EvnStickGrid').getSelectionModel().selectFirstRow();
											}
											else if ( !this.findById('EPLStomEF_ResultPanel').collapsed && this.action != 'view' ) {
												base_form.findField('EvnPLStom_IsFinish').focus(true);
											}
											else if ( this.action == 'view' ) {
												this.buttons[1].focus();
											}
											else {
												this.buttons[0].focus();
											}
										}
										else {
											if ( !this.findById('EPLStomEF_EvnVizitPLStomPanel').collapsed ) {
												this.findById('EPLStomEF_EvnVizitPLStomGrid').getView().focusRow(0);
												this.findById('EPLStomEF_EvnVizitPLStomGrid').getSelectionModel().selectFirstRow();
											}
											else if ( !this.findById('EPLStomEF_DirectInfoPanel').collapsed && this.action != 'view' ) {
												base_form.findField('EvnPLStom_IsUnport').focus(true);
											}
											else if ( this.action == 'view' ) {
												this.buttons[this.buttons.length - 1].focus();
											}
											else {
												base_form.findField('EvnPLStom_NumCard').focus(true);
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
								this.openEvnDiagPLStomEditWindow('view');
							}.createDelegate(this)
						},
						loadMask: true,
						region: 'center',
						sm: new Ext.grid.RowSelectionModel({
							listeners: {
								'rowselect': function(sm, rowIndex, record) {
									var
										id,
										selected_record = sm.getSelected(),
										toolbar = this.findById('EPLStomEF_EvnDiagPLStomGrid').getTopToolbar();

									if ( selected_record ) {
										id = selected_record.get('EvnDiagPLStom_id');
									}

									if ( id ) {
										toolbar.items.items[0].enable();
									}
									else {
										toolbar.items.items[0].disable();
									}
								}.createDelegate(this)
							}
						}),
						stripeRows: true,
						store: new Ext.data.Store({
							autoLoad: false,
							baseParams: {
								'parent': 'EvnPLStom'
							},
							listeners: {
								'load': function(store, records, index) {
									if ( store.getCount() == 0 ) {
										LoadEmptyRow(this.findById('EPLStomEF_EvnDiagPLStomGrid'));
									}
								}.createDelegate(this)
							},
							reader: new Ext.data.JsonReader({
								id: 'EvnDiagPLStom_id'
							}, [{
								mapping: 'EvnDiagPLStom_id',
								name: 'EvnDiagPLStom_id',
								type: 'int'
							}, {
								mapping: 'EvnDiagPLStom_pid',
								name: 'EvnDiagPLStom_pid',
								type: 'int'
							}, {
								dateFormat: 'd.m.Y',
								mapping: 'EvnDiagPLStom_setDate',
								name: 'EvnDiagPLStom_setDate',
								type: 'date'
							}, {
								dateFormat: 'd.m.Y',
								mapping: 'EvnDiagPLStom_disDate',
								name: 'EvnDiagPLStom_disDate',
								type: 'date'
							}, {
								mapping: 'Diag_id',
								name: 'Diag_id',
								type: 'int'
							}, {
								mapping: 'Diag_Code',
								name: 'Diag_Code',
								type: 'string'
							}, {
								mapping: 'Diag_Name',
								name: 'Diag_Name',
								type: 'string'
							}, {
								mapping: 'Tooth_Code',
								name: 'Tooth_Code',
								type: 'int'
							}, {
								mapping: 'Mes_Code',
								name: 'Mes_Code',
								type: 'string'
							}, {
								mapping: 'Mes_Name',
								name: 'Mes_Name',
								type: 'string'
							}, {
								mapping: 'EvnDiagPLStom_IsClosed',
								name: 'EvnDiagPLStom_IsClosed',
								type: 'int'
							}, {
								mapping: 'EvnDiagPLStom_NumGroup',
								name: 'EvnDiagPLStom_NumGroup',
								type: 'int'
							}]),
							url: '/?c=EvnDiagPLStom&m=loadEvnDiagPLStomGrid'
						}),
						tbar: new sw.Promed.Toolbar({
							buttons: [{
								handler: function() {
									this.openEvnDiagPLStomEditWindow('view');
								}.createDelegate(this),
								iconCls: 'view16',
								text: 'Просмотр'
							}]
						})
					})]
				}),
				new sw.Promed.Panel({
					border: true,
					collapsible: true,
					height: 200,
					id: 'EPLStomEF_EvnUslugaPanel',
					isLoaded: false,
					layout: 'border',
					listeners: {
						'expand': function(panel) {
							if ( panel.isLoaded === false ) {
								panel.isLoaded = true;
								panel.findById('EPLStomEF_EvnUslugaGrid').getStore().load({
									params: {
										pid: this.findById('EvnPLStomEditForm').getForm().findField('EvnPLStom_id').getValue(),
										rid: this.findById('EvnPLStomEditForm').getForm().findField('EvnPLStom_id').getValue()
									}
								});
							}

							panel.doLayout();
						}.createDelegate(this)
					},
					style: 'margin-bottom: 0.5em;',
					title: '3. Услуги',
					items: [ new Ext.grid.GridPanel({
						autoExpandColumn: 'autoexpand_usluga',
						autoExpandMin: 100,
						border: false,
						columns: [{
							dataIndex: 'EvnUsluga_setDate',
							header: 'Дата',
							hidden: false,
							renderer: Ext.util.Format.dateRenderer('d.m.Y'),
							resizable: false,
							sortable: true,
							width: 100
						}, {
							dataIndex: 'Usluga_Code',
							header: 'Код',
							hidden: false,
							resizable: false,
							sortable: true,
							width: 100
						}, {
							dataIndex: 'Usluga_Name',
							header: 'Наименование',
							hidden: false,
							id: 'autoexpand_usluga',
							resizable: true,
							sortable: true
						}, {
							dataIndex: 'EvnUsluga_Price',
							header: 'УЕТ',
							hidden: false,
							resizable: true,
							sortable: true,
							width: 100
						}, {
							dataIndex: 'EvnUsluga_Kolvo',
							header: 'Количество',
							hidden: false,
							resizable: true,
							sortable: true,
							width: 100
						}, {
							dataIndex: 'EvnUsluga_Summa',
							header: 'Сумма (УЕТ)',
							hidden: false,
							resizable: true,
							sortable: true,
							width: 100
						}, {
							align: 'center',
							dataIndex: 'EvnUsluga_IsVizitCode',
							header: 'Услуга посещения',
							hidden: false,
							resizable: true,
							sortable: true,
							width: 120
						}, {
							align: 'center',
							dataIndex: 'EvnUsluga_IsMes',
							header: 'По КСГ',
							hidden: false,
							resizable: true,
							sortable: true,
							width: 75
						}, {
							align: 'center',
							dataIndex: 'EvnUsluga_IsAllMorbus',
							header: 'Для всех заболеваний',
							hidden: false,
							resizable: true,
							sortable: true,
							width: 75
						}, {
							align: 'center',
							dataIndex: 'EvnUsluga_IsRequired',
							header: 'Обязательная',
							hidden: false,
							resizable: true,
							sortable: true,
							width: 75
						}],
						frame: false,
						id: 'EPLStomEF_EvnUslugaGrid',
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

								var grid = Ext.getCmp('EPLStomEF_EvnUslugaGrid');

								switch ( e.getKey() ) {
									case Ext.EventObject.DELETE:
										Ext.getCmp('EvnPLStomEditWindow').deleteEvent('EvnUsluga');
									break;

									case Ext.EventObject.END:
										GridEnd(grid);
									break;

									case Ext.EventObject.ENTER:
									case Ext.EventObject.F3:
									case Ext.EventObject.F4:
									//case Ext.EventObject.INSERT:
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

										if (current_window.formMode == 'morbus' && action == 'edit') {
											action = 'view';
										}

										Ext.getCmp('EvnPLStomEditWindow').openEvnUslugaEditWindow(action);
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
										var base_form = this.findById('EvnPLStomEditForm').getForm();

										grid.getSelectionModel().clearSelections();
										grid.getSelectionModel().fireEvent('rowselect', grid.getSelectionModel());

										if ( e.shiftKey == false ) {
											if ( !this.findById('EPLStomEF_EvnStickPanel').collapsed ) {
												this.findById('EPLStomEF_EvnStickGrid').getView().focusRow(0);
												this.findById('EPLStomEF_EvnStickGrid').getSelectionModel().selectFirstRow();
											}
											else if ( !this.findById('EPLStomEF_ResultPanel').collapsed && this.action != 'view' ) {
												base_form.findField('EvnPLStom_IsFinish').focus(true);
											}
											/*else if ( !this.findById('EPLStomEF_DirectPanel').collapsed && this.action != 'view' ) {
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
											if ( !this.findById('EPLStomEF_EvnVizitPLStomPanel').collapsed ) {
												this.findById('EPLStomEF_EvnVizitPLStomGrid').getView().focusRow(0);
												this.findById('EPLStomEF_EvnVizitPLStomGrid').getSelectionModel().selectFirstRow();
											}
											else if ( !this.findById('EPLStomEF_DirectInfoPanel').collapsed && this.action != 'view' ) {
												base_form.findField('EvnPLStom_IsUnport').focus(true);
											}
											else if ( this.action == 'view' ) {
												this.buttons[this.buttons.length - 1].focus();
											}
											else {
												base_form.findField('EvnPLStom_NumCard').focus(true);
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
								if (current_window.formMode == 'morbus') {
									this.openEvnUslugaEditWindow('view');
								} else {
									this.openEvnUslugaEditWindow('edit');
								}
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
									var toolbar = this.findById('EPLStomEF_EvnUslugaGrid').getTopToolbar();

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

											if ( selected_record.get('EvnClass_SysNick') != 'EvnUslugaPar' ) {
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
								'parent': 'EvnPLStom'
							},
							listeners: {
								'load': function(store, records, index) {
									if ( store.getCount() == 0 ) {
										LoadEmptyRow(this.findById('EPLStomEF_EvnUslugaGrid'));
									}

									// this.findById('EPLStomEF_EvnUslugaGrid').getView().focusRow(0);
									// this.findById('EPLStomEF_EvnUslugaGrid').getSelectionModel().selectFirstRow();
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
								mapping: 'EvnUsluga_Summa',
								name: 'EvnUsluga_Summa',
								type: 'float'
							}, {
								mapping: 'EvnUsluga_Price',
								name: 'EvnUsluga_Price',
								type: 'float'
							}, {
								mapping: 'EvnUsluga_IsVizitCode',
								name: 'EvnUsluga_IsVizitCode',
								type: 'string'
							}, {
								mapping: 'EvnUsluga_IsMes',
								name: 'EvnUsluga_IsMes',
								type: 'string'
							}, {
								mapping: 'EvnUsluga_IsRequired',
								name: 'EvnUsluga_IsRequired',
								type: 'string'
							}, {
								mapping: 'EvnUsluga_IsAllMorbus',
								name: 'EvnUsluga_IsAllMorbus',
								type: 'string'
							}]),
							url: '/?c=EvnUsluga&m=loadEvnUslugaGrid'
						}),
						tbar: new sw.Promed.Toolbar({
							buttons: [{
								handler: function() {
									this.openEvnUslugaEditWindow('add');
								}.createDelegate(this),
								iconCls: 'add16',
								disabled: true,
								hidden: true,
								text: 'Добавить'
							}, {
								handler: function() {
									this.openEvnUslugaEditWindow('edit');
								}.createDelegate(this),
								iconCls: 'edit16',
								text: 'Изменить'
							}, {
								handler: function() {
									this.openEvnUslugaEditWindow('view');
								}.createDelegate(this),
								iconCls: 'view16',
								text: 'Просмотр'
							}, {
								handler: function() {
									this.deleteEvent('EvnUsluga');
								}.createDelegate(this),
								iconCls: 'delete16',
								text: 'Удалить'
							}]
						})
					})]
				}),
				new sw.Promed.Panel({
					border: true,
					collapsible: true,
					height: 200,
					id: 'EPLStomEF_EvnStickPanel',
					isLoaded: false,
					layout: 'border',
					listeners: {
						'expand': function(panel) {
							if ( panel.isLoaded === false ) {
								panel.isLoaded = true;
								panel.findById('EPLStomEF_EvnStickGrid').getStore().load({
									params: {
										EvnStick_pid: this.findById('EvnPLStomEditForm').getForm().findField('EvnPLStom_id').getValue()
									}
								});
							}

							panel.doLayout();
						}.createDelegate(this)
					},
					style: 'margin-bottom: 0.5em;',
					title: '4. Нетрудоспособность',
					items: [ new Ext.grid.GridPanel({
						autoExpandColumn: 'autoexpand_stick',
						autoExpandMin: 100,
						border: false,
						columns: [{
							dataIndex: 'EvnStick_ParentTypeName',
							header: 'ТАП/КВС',
							hidden: false,
							resizable: true,
							sortable: true,
							width: 100
						}, {
							dataIndex: 'EvnStick_ParentNum',
							header: 'Номер ТАП/КВС',
							hidden: false,
							resizable: true,
							sortable: true,
							width: 120
						}, {
							dataIndex: 'StickType_Name',
							header: 'Вид документа',
							hidden: false,
							resizable: true,
							sortable: true,
							width: 150
						}, {
							dataIndex: 'EvnStick_IsOriginal',
							header: 'Оригинальность',
							hidden: false,
							resizable: true,
							sortable: true,
							width: 150
						}, {
							dataIndex: 'StickWorkType_Name',
							header: 'Тип занятости',
							hidden: false,
							resizable: true,
							sortable: true,
							width: 150
						}, {
							dataIndex: 'EvnStick_setDate',
							header: 'Дата выдачи',
							hidden: false,
							renderer: Ext.util.Format.dateRenderer('d.m.Y'),
							resizable: false,
							sortable: true,
							width: 100
						}, {
							dataIndex: 'EvnStickWorkRelease_begDate',
							header: 'Освобожден с',
							hidden: false,
							renderer: Ext.util.Format.dateRenderer('d.m.Y'),
							resizable: false,
							sortable: true,
							width: 100
						}, {
							dataIndex: 'EvnStickWorkRelease_endDate',
							header: 'Освобожден по',
							hidden: false,
							renderer: Ext.util.Format.dateRenderer('d.m.Y'),
							resizable: false,
							sortable: true,
							width: 100
						}, {
							dataIndex: 'EvnStick_disDate',
							header: 'Дата закрытия',
							hidden: false,
							renderer: Ext.util.Format.dateRenderer('d.m.Y'),
							resizable: false,
							sortable: true,
							width: 100
						}, {
							dataIndex: 'EvnStick_Ser',
							header: 'Серия',
							hidden: false,
							resizable: true,
							sortable: true,
							width: 100
						}, {
							dataIndex: 'EvnStick_Num',
							header: 'Номер',
							hidden: false,
							resizable: true,
							sortable: true,
							width: 100
						}, {
							dataIndex: 'StickOrder_Name',
							header: 'Порядок выписки',
							hidden: false,
							id: 'autoexpand_stick',
							resizable: true,
							sortable: true
						}],
						frame: false,
						id: 'EPLStomEF_EvnStickGrid',
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

								var grid = this.findById('EPLStomEF_EvnStickGrid');

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
										var base_form = this.findById('EvnPLStomEditForm').getForm();

										grid.getSelectionModel().clearSelections();
										grid.getSelectionModel().fireEvent('rowselect', grid.getSelectionModel());

										if ( e.shiftKey == false ) {
											if ( !this.findById('EPLStomEF_ResultPanel').collapsed && this.action != 'view' ) {
												base_form.findField('EvnPLStom_IsFinish').focus(true);
											}
											/*else if ( !this.findById('EPLStomEF_DirectPanel').collapsed && this.action != 'view' ) {
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
											if ( !this.findById('EPLStomEF_EvnUslugaPanel').collapsed ) {
												this.findById('EPLStomEF_EvnUslugaGrid').getView().focusRow(0);
												this.findById('EPLStomEF_EvnUslugaGrid').getSelectionModel().selectFirstRow();
											}
											else if ( !this.findById('EPLStomEF_EvnVizitPLStomPanel').collapsed ) {
												this.findById('EPLStomEF_EvnVizitPLStomGrid').getView().focusRow(0);
												this.findById('EPLStomEF_EvnVizitPLStomGrid').getSelectionModel().selectFirstRow();
											}
											else if ( !this.findById('EPLStomEF_DirectInfoPanel').collapsed && this.action != 'view' ) {
												base_form.findField('EvnPLStom_IsUnport').focus(true);
											}
											else if ( this.action == 'view' ) {
												this.buttons[this.buttons.length - 1].focus();
											}
											else {
												base_form.findField('EvnPLStom_NumCard').focus(true);
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
									var toolbar = this.findById('EPLStomEF_EvnStickGrid').getTopToolbar();

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

									if ( id ) {
										toolbar.items.items[2].enable();

										if ( this.action != 'view' && access_type == 'edit' ) {
											toolbar.items.items[1].enable();
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
										LoadEmptyRow(this.findById('EPLStomEF_EvnStickGrid'));
									}

									// this.findById('EPLStomEF_EvnStickGrid').getView().focusRow(0);
									// this.findById('EPLStomEF_EvnStickGrid').getSelectionModel().selectFirstRow();
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
							}, {
								mapping: 'EvnStick_IsDelQueue',
								name: 'EvnStick_IsDelQueue',
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
								text: 'Добавить'
							},{
								handler: function() {
									this.openEvnStickEditWindow('edit');
								}.createDelegate(this),
								iconCls: 'edit16',
								text: 'Изменить'
							},{
								handler: function() {
									this.openEvnStickEditWindow('view');
								}.createDelegate(this),
								iconCls: 'view16',
								text: 'Просмотр'
							}, {
								handler: function() {
									this.deleteEvent('EvnStick');
								}.createDelegate(this),
								iconCls: 'delete16',
								text: 'Удалить'
							}, {
								handler: function() {
									this.deleteEvent('EvnStick', { ignoreQuestion: true });
								}.createDelegate(this),
								hidden: getRegionNick() == 'kz',
								text: 'Аннулировать'
							}, {
								handler: function() {
									this.undoDeleteEvnStick();
								}.createDelegate(this),
								hidden: getRegionNick() == 'kz',
								text: 'Восстановить'
							}]
						})
					})]
				}),
				new sw.Promed.Panel({
					autoHeight: true,
					bodyStyle: 'padding-top: 0.5em;',
					border: true,
					collapsible: true,
					id: 'EPLStomEF_ResultPanel',
					layout: 'form',
						listeners: {
						'expand': function(panel) {
							// this.findById('EvnPLStomEditForm').getForm().findField('EvnPLStom_IsFinish').focus(true);
						}.createDelegate(this)
					},
					style: 'margin-bottom: 0.5em;',
					title: '5. Результат',
					items: [{
						fieldLabel: 'Повторная подача',
						listeners: {
							'check': function(checkbox, value) {
								if ( getRegionNick() != 'perm' ) {
									return false;
								}

								var base_form = this.findById('EvnPLStomEditForm').getForm();

								var
									EvnPLStom_IndexRep = parseInt(base_form.findField('EvnPLStom_IndexRep').getValue()),
									EvnPLStom_IndexRepInReg = parseInt(base_form.findField('EvnPLStom_IndexRepInReg').getValue()),
									EvnPLStom_IsPaid = parseInt(base_form.findField('EvnPLStom_IsPaid').getValue());

								var diff = EvnPLStom_IndexRepInReg - EvnPLStom_IndexRep;

								if ( EvnPLStom_IsPaid != 2 || EvnPLStom_IndexRepInReg == 0 ) {
									return false;
								}

								if ( value == true ) {
									if ( diff == 1 || diff == 2 ) {
										EvnPLStom_IndexRep = EvnPLStom_IndexRep + 2;
									}
									else if ( diff == 3 ) {
										EvnPLStom_IndexRep = EvnPLStom_IndexRep + 4;
									}
								}
								else if ( value == false ) {
									if ( diff <= 0 ) {
										EvnPLStom_IndexRep = EvnPLStom_IndexRep - 2;
									}
								}

								base_form.findField('EvnPLStom_IndexRep').setValue(EvnPLStom_IndexRep);

							}.createDelegate(this)
						},
						name: 'EvnPLStom_RepFlag',
						xtype: 'checkbox'
					}, {
						allowBlank: false,
						enableKeyEvents: true,
						fieldLabel: 'Случай закончен',
						hiddenName: 'EvnPLStom_IsFinish',
						listeners: {
							'change': function(combo, newValue, oldValue) {
								var win = this;
								var base_form = this.findById('EvnPLStomEditForm').getForm();
								var index = combo.getStore().findBy(function(rec) {
									return (rec.get(combo.valueField) == newValue);
								});
								combo.fireEvent('select', combo, combo.getStore().getAt(index));

								this.checkForCostPrintPanel();
								this.setDiagFidAndLid();

								if ( Ext.isEmpty(base_form.findField('EvnPLStom_id').getValue()) || oldValue == -1 ) {
									return true;
								}

								var checkOnEvnPLStomIsFinish = this.checkOnEvnPLStomIsFinish();

								if ( !Ext.isEmpty(checkOnEvnPLStomIsFinish) ) {
									sw.swMsg.alert('Ошибка', checkOnEvnPLStomIsFinish, function() {
										combo.setValue(1);
										var index = combo.getStore().findBy(function(rec) {
											return (rec.get(combo.valueField) == 1);
										});
										combo.fireEvent('select', combo, combo.getStore().getAt(index));
										win.checkForCostPrintPanel();
										win.setDiagFidAndLid();
										combo.focus(true);
									});
									return false;
								}

								var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Изменение признака..." });
								loadMask.show();

								var setEvnVizitParameter = function(options) {
									var params = {
										 object: 'EvnPLBase'
										,id: base_form.findField('EvnPLStom_id').getValue()
										,param_name: 'EvnPLBase_IsFinish'
										,param_value: newValue
									};

									if ( typeof options != 'object' ) {
										options = {};
									}

									options.ignoreKsgInMorbusCheck = 1; // игнорируем эту проверку, проверим при сохранении всего ТАП refs #160848

									params.options = Ext.util.JSON.encode(options);

									Ext.Ajax.request({
										callback: function(opt, success, response) {
											loadMask.hide();

											if ( !Ext.isEmpty(response.responseText) ) {
												var response_obj = Ext.util.JSON.decode(response.responseText);

												if ( response_obj.success == false )  {
													if ( response_obj.Alert_Msg ) {
														sw.swMsg.show({
															buttons: Ext.Msg.YESNO,
															fn: function(buttonId, text, obj) {
																if ( buttonId == 'yes' ) {
																	if (response_obj.Error_Code == 197641) {
																		options.ignoreNoExecPrescr = 1;
																	}
																	if (response_obj.Error_Code == 109) {
																		options.ignoreParentEvnDateCheck = 1;
																	}
																	else if (response_obj.Error_Code == 119) {
																		options.ignoreKsgInMorbusCheck = 1;
																	}
																	else if (response_obj.Error_Code == 129) {
																		options.ignoreUetSumInNonMorbusCheck = 1;
																	}

																	setEvnVizitParameter(options);
																} else {
																	if (response_obj.Error_Code == 197641) {
																		base_form.findField('EvnPLStom_IsFinish').setValue(1);
																	}
																}
															}.createDelegate(this),
															icon: Ext.MessageBox.QUESTION,
															msg: response_obj.Alert_Msg,
															title: 'Продолжить сохранение?'
														});
													} else if ( response_obj.Error_Msg ) {
														sw.swMsg.alert('Ошибка', response_obj.Error_Msg);
													}
													else {
														sw.swMsg.alert('Ошибка', 'При изменении признака окончания случая возникли ошибки');
													}
												}
											}
											else {
												sw.swMsg.alert('Ошибка', 'При изменении признака окончания случая возникли ошибки');
											}
										}.createDelegate(this),
										params: params,
										url: '/?c=EvnVizit&m=setEvnVizitParameter'
									});
								};

								setEvnVizitParameter();

								return true;
							}.createDelegate(this),
							'select': function(combo, record, id) {
								var base_form = this.findById('EvnPLStomEditForm').getForm();
								this.setDiagConcComboVisible();

								if ( !record || record.get('YesNo_Code') == 0 ) {
									base_form.findField('ResultClass_id').clearValue();
									base_form.findField('EvnPLStom_IsSurveyRefuse').clearValue();
									base_form.findField('ResultDeseaseType_id').clearValue();
									base_form.findField('ResultClass_id').setAllowBlank(true);
									base_form.findField('ResultDeseaseType_id').setAllowBlank(true);
									base_form.findField('InterruptLeaveType_id').clearValue();
									base_form.findField('Diag_concid').clearValue();
									base_form.findField('EvnPLStom_IsSan').clearValue();
									base_form.findField('EvnPLStom_IsSan').setAllowBlank(true);
									base_form.findField('SanationStatus_id').clearValue();
									base_form.findField('SanationStatus_id').setAllowBlank(true);
									base_form.findField('EvnPLStom_UKL').setAllowBlank(true);
									base_form.findField('DirectClass_id').clearValue();
									base_form.findField('DirectType_id').clearValue();
									base_form.findField('Lpu_oid').clearValue();
									base_form.findField('LpuSection_oid').clearValue();
									base_form.findField('Diag_lid').setAllowBlank(true);

									if ( Ext.globalOptions.polka.is_finish_result_block == '1' ) {
										//Запрет ввода результата лечения для незаконченного случая
										base_form.findField('ResultClass_id').disable();
										base_form.findField('EvnPLStom_IsSurveyRefuse').disable();
										base_form.findField('ResultDeseaseType_id').disable();
										base_form.findField('InterruptLeaveType_id').disable();
										base_form.findField('Diag_concid').disable();
										base_form.findField('EvnPLStom_IsSan').disable();
										base_form.findField('SanationStatus_id').disable();
										base_form.findField('EvnPLStom_UKL').disable();
										base_form.findField('EvnPLStom_UKL').setValue('');
										base_form.findField('DirectClass_id').disable();
										base_form.findField('DirectType_id').disable();
										base_form.findField('Lpu_oid').disable();
										base_form.findField('LpuSection_oid').disable();
										base_form.findField('LeaveType_fedid').disable();
										base_form.findField('ResultDeseaseType_fedid').disable();
									}
									else {
										base_form.findField('LeaveType_fedid').enable();
										base_form.findField('ResultDeseaseType_fedid').enable();
										base_form.findField('ResultClass_id').enable();
										base_form.findField('EvnPLStom_IsSurveyRefuse').enable();
										base_form.findField('ResultDeseaseType_id').enable();
                                        base_form.findField('InterruptLeaveType_id').enable();
										base_form.findField('Diag_concid').enable();
										base_form.findField('EvnPLStom_IsSan').enable();
										base_form.findField('SanationStatus_id').enable();
										base_form.findField('EvnPLStom_UKL').enable();
										base_form.findField('DirectClass_id').enable();
										base_form.findField('DirectType_id').enable();
										base_form.findField('Lpu_oid').enable();
										base_form.findField('LpuSection_oid').enable();
									}
								} else {
									base_form.findField('LeaveType_fedid').enable();
									base_form.findField('ResultDeseaseType_fedid').enable();
									base_form.findField('DirectClass_id').enable();
									base_form.findField('DirectType_id').enable();
									base_form.findField('Lpu_oid').enable();
									base_form.findField('LpuSection_oid').enable();
									base_form.findField('ResultClass_id').enable();
									base_form.findField('EvnPLStom_IsSurveyRefuse').enable();
									base_form.findField('ResultDeseaseType_id').enable();
									base_form.findField('InterruptLeaveType_id').enable();
									base_form.findField('Diag_concid').enable();
									base_form.findField('ResultClass_id').setAllowBlank(false);
									base_form.findField('ResultDeseaseType_id').setAllowBlank( !getRegionNick().inlist(['adygeya', 'vologda','buryatiya','kareliya','krasnoyarsk','ekb','pskov','penza','krym','yakutiya','yaroslavl']) );
									base_form.findField('EvnPLStom_IsSan').enable();
									base_form.findField('SanationStatus_id').enable();
									base_form.findField('EvnPLStom_UKL').enable();
									base_form.findField('EvnPLStom_UKL').setAllowBlank(getRegionNick() == 'kareliya');
									if (current_window.formMode == 'morbus') {
										base_form.findField('Diag_lid').setAllowBlank(false);
									}

									if ( Ext.isEmpty(base_form.findField('EvnPLStom_UKL').getValue()) ) {
										base_form.findField('EvnPLStom_UKL').setValue(1);
									}
								}

								var firstVizitData = null;
								var lastVizitData = null;
								this.findById('EPLStomEF_EvnVizitPLStomGrid').getStore().each(function(record) {
									if ( Ext.isEmpty(firstVizitData) || (!Ext.isEmpty(record.get('EvnVizitPLStom_setDate')) && record.get('EvnVizitPLStom_setDate') <= firstVizitData.EvnVizitPLStom_setDate) ) {
										firstVizitData = record.data;
									}
									if ( Ext.isEmpty(lastVizitData) || (!Ext.isEmpty(record.get('EvnVizitPLStom_setDate')) && record.get('EvnVizitPLStom_setDate') >= lastVizitData.EvnVizitPLStom_setDate) ) {
										lastVizitData = record.data;
									}
								});

								var diag_l_combo = base_form.findField('Diag_lid');
								if (Ext.isEmpty(diag_l_combo.getValue()) && lastVizitData && !Ext.isEmpty(lastVizitData.Diag_id)) {
									diag_l_combo.getStore().load({
										params: {where: "where DiagLevel_id = 4 and Diag_id = " + lastVizitData.Diag_id},
										callback: function() {
											diag_l_combo.setValue(lastVizitData.Diag_id);
											win.setDiagConcComboVisible();
											win.checkTrauma();
										}
									});
								}
								else {
									win.setDiagConcComboVisible();
									win.checkTrauma();
								}

								if(!win.fo){
									win.calcFedResultDeseaseType();
									win.calcFedLeaveType();
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

								base_form.findField('DirectClass_id').fireEvent('change', base_form.findField('DirectClass_id'), base_form.findField('DirectClass_id').getValue());
							}.createDelegate(this),
							'keydown': function(inp, e) {
								switch ( e.getKey() ) {
									case Ext.EventObject.TAB:
										if ( e.shiftKey == true ) {
											e.stopEvent();

											if ( !this.findById('EPLStomEF_EvnStickPanel').collapsed ) {
												this.findById('EPLStomEF_EvnStickGrid').getView().focusRow(0);
												this.findById('EPLStomEF_EvnStickGrid').getSelectionModel().selectFirstRow();
											}
											else if ( !this.findById('EPLStomEF_EvnUslugaPanel').collapsed ) {
												this.findById('EPLStomEF_EvnUslugaGrid').getView().focusRow(0);
												this.findById('EPLStomEF_EvnUslugaGrid').getSelectionModel().selectFirstRow();
											}
											else if ( !this.findById('EPLStomEF_EvnVizitPLStomPanel').collapsed ) {
												this.findById('EPLStomEF_EvnVizitPLStomGrid').getView().focusRow(0);
												this.findById('EPLStomEF_EvnVizitPLStomGrid').getSelectionModel().selectFirstRow();
											}
											else if ( !this.findById('EPLStomEF_DirectInfoPanel').collapsed && this.action != 'view' ) {
												this.findField('EvnPLStom_IsUnport').focus(true);
											}
											else if ( this.action == 'view' ) {
												this.buttons[this.buttons.length - 1].focus();
											}
											else {
												this.findField('EvnPLStom_NumCard').focus(true);
											}
										}
									break;
								}
							}.createDelegate(this)
						},
						tabIndex: TABINDEX_EPLSTOMEF + 11,
						width: 70,
						xtype: 'swyesnocombo'
					}, {
						fieldLabel: langs('Отказ от прохождения медицинских обследований'),
						hiddenName: 'EvnPLStom_IsSurveyRefuse',
						lastQuery: '',
						tabIndex: TABINDEX_EPLSTOMEF + 11,
						width: 70,
						xtype: 'swyesnocombo'
					}, {
						hiddenName: 'ResultClass_id',
						lastQuery: '',
						tabIndex: TABINDEX_EPLSTOMEF + 12,
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
								var base_form = this.findById('EvnPLStomEditForm').getForm();
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
						fieldLabel: langs('Случай прерван'),
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
								var base_form = this.findById('EvnPLStomEditForm').getForm();
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
						fieldLabel: 'Исход',
						hiddenName: 'ResultDeseaseType_id',
						lastQuery: '',
						tabIndex: TABINDEX_EPLSTOMEF + 13,
						width: 300,
						xtype: 'swcommonsprcombo'
					}, {
						fieldLabel: 'Санирован',
						hiddenName: 'EvnPLStom_IsSan',
						tabIndex: TABINDEX_EPLSTOMEF + 13,
						width: 70,
						xtype: 'swyesnocombo'
					}, {
						fieldLabel: 'Санация',
						hiddenName: 'SanationStatus_id',
						tabIndex: TABINDEX_EPLSTOMEF + 14,
						width: 300,
						xtype: 'swsanationstatuscombo'
					}, {
						allowDecimals: true,
						allowNegative: false,
						fieldLabel: 'УКЛ',
						maxValue: 1,
						name: 'EvnPLStom_UKL',
						tabIndex: TABINDEX_EPLSTOMEF + 15,
						width: 70,
						value: 1,
						xtype: 'numberfield'
					}, {
						allowDecimals: true,
						allowNegative: false,
						disabled: true,
						fieldLabel: 'Общее количество УЕТ',
						name: 'EvnPLStom_UET',
						tabIndex: TABINDEX_EPLSTOMEF + 15,
						width: 70,
						xtype: 'numberfield'
					}, {
						id:'EPLStomEF_DirectFieldset',
						xtype: 'fieldset',
						title: 'Направление',
						labelWidth: 165,
						style: 'margin: 3px;',
						autoHeight: true,
						items:[{
							enableKeyEvents: true,
							hiddenName: 'DirectType_id',
							lastQuery: '',
							listeners: {
								change: function(combo, newValue, oldValue) {
									var index = combo.getStore().findBy(function(rec) {
										return (rec.get(combo.valueField) == newValue);
									});
									combo.fireEvent('select', combo, combo.getStore().getAt(index), index);
								},
								select: function(combo, record, idx) {
									var base_form = combo.findParentByType('form').getForm();
									if(!win.fo){
										win.calcFedResultDeseaseType();
										win.calcFedLeaveType();
									}
									sw.Promed.EvnPL.filterFedResultDeseaseType({
									fieldFedLeaveType: base_form.findField('LeaveType_fedid'),
									fieldFedResultDeseaseType: base_form.findField('ResultDeseaseType_fedid')
								});
								sw.Promed.EvnPL.filterFedLeaveType({
									fieldFedLeaveType: base_form.findField('LeaveType_fedid'),
									fieldFedResultDeseaseType: base_form.findField('ResultDeseaseType_fedid')
								});
								},
								'keydown': function(inp, e) {
									switch ( e.getKey() ) {
										case Ext.EventObject.TAB:
											if ( e.shiftKey == true ) {
												e.stopEvent();

												var base_form = this.findById('EvnPLStomEditForm').getForm();

												if ( !this.findById('EPLStomEF_ResultPanel').collapsed ) {
													base_form.findField('EvnPLStom_UKL').focus(true);
												}
												else if ( !this.findById('EPLStomEF_EvnStickPanel').collapsed ) {
													this.findById('EPLStomEF_EvnStickGrid').getView().focusRow(0);
													this.findById('EPLStomEF_EvnStickGrid').getSelectionModel().selectFirstRow();
												}
												else if ( !this.findById('EPLStomEF_EvnUslugaPanel').collapsed ) {
													this.findById('EPLStomEF_EvnUslugaGrid').getView().focusRow(0);
													this.findById('EPLStomEF_EvnUslugaGrid').getSelectionModel().selectFirstRow();
												}
												else if ( !this.findById('EPLStomEF_EvnVizitPLStomPanel').collapsed ) {
													this.findById('EPLStomEF_EvnVizitPLStomGrid').getView().focusRow(0);
													this.findById('EPLStomEF_EvnVizitPLStomGrid').getSelectionModel().selectFirstRow();
												}
												else if ( !this.findById('EPLStomEF_DirectInfoPanel').collapsed && this.action != 'view' ) {
													base_form.findField('EvnPLStom_IsUnport').focus(true);
												}
												else if ( this.action == 'view' ) {
													this.buttons[this.buttons.length - 1].focus();
												}
												else {
													base_form.findField('EvnPLStom_NumCard').focus(true);
												}
											}
											break;
									}
								}.createDelegate(this)
							},
							tabIndex: TABINDEX_EPLSTOMEF + 16,
							width: 300,
							xtype: 'swdirecttypecombo'
						}, {
							hiddenName: 'DirectClass_id',
							lastQuery: '',
							listeners: {
								'change': function(combo, newValue, oldValue) {
									var base_form = this.findById('EvnPLStomEditForm').getForm();
									var record = combo.getStore().getById(newValue);

									var lpu_combo = base_form.findField('Lpu_oid');
									var lpu_section_combo = base_form.findField('LpuSection_oid');

									lpu_combo.clearValue();
									lpu_section_combo.clearValue();

									if ( !record ) {
										lpu_section_combo.disable();
										lpu_combo.disable();
									}
									else if ( record.get('DirectClass_Code') == 1 ) {
										lpu_section_combo.enable();
										lpu_combo.disable();
									}
									else if ( record.get('DirectClass_Code') == 2 ) {
										lpu_section_combo.disable();
										lpu_combo.enable();
									}
									else {
										lpu_section_combo.disable();
										lpu_combo.disable();
									}
									if(!win.fo){
										win.calcFedResultDeseaseType();
										win.calcFedLeaveType();
									}
								}.createDelegate(this)
							},
							tabIndex: TABINDEX_EPLSTOMEF + 17,
							width: 300,
							xtype: 'swdirectclasscombo'
						}, {
							hiddenName: 'LpuSection_oid',
							tabIndex: TABINDEX_EPLSTOMEF + 18,
							width: 500,
							xtype: 'swlpusectionglobalcombo'
						}, {
							displayField: 'Org_Name',
							editable: false,
							enableKeyEvents: true,
							fieldLabel: 'ЛПУ',
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
								var base_form = this.findById('EvnPLStomEditForm').getForm();
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
									onSelect: function(org_data) {
										if ( org_data.Lpu_id > 0 ) {
											combo.getStore().loadData([{
												Lpu_id: org_data.Lpu_id,
												Org_Name: org_data.Org_Name
											}]);
											combo.setValue(org_data.Lpu_id);
											getWnd('swOrgSearchWindow').hide();
										}
									}
								});
							}.createDelegate(this),
							store: new Ext.data.JsonStore({
								autoLoad: false,
								fields: [
									{ name: 'Lpu_id', type: 'int' },
									{ name: 'Org_Name', type: 'string' }
								],
								key: 'Lpu_id',
								sortInfo: {
									field: 'Org_Name'
								},
								url: C_ORG_LIST
							}),
							tabIndex: TABINDEX_EPLSTOMEF + 19,
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
						}]
					}, {
						checkAccessRights: true,
						fieldLabel: langs('Заключ. диагноз'),
						hiddenName: 'Diag_lid',
						tabIndex: TABINDEX_EPLSTOMEF + 20,
						width: 500,
						xtype: 'swdiagcombo',
						onChange: function(combo, newValue, oldValue) {
							this.setDiagConcComboVisible();
							//this.checkAbort();
						}.createDelegate(this)
					}, {
						checkAccessRights: true,
						fieldLabel: langs('Заключ. внешняя причина'),
						hiddenName: 'Diag_concid',
						tabIndex: TABINDEX_EPLSTOMEF + 21,
						width: 500,
						xtype: 'swdiagcombo',
						baseFilterFn: function (rec) {
							if(typeof rec.get == 'function') {
								return (rec.get('Diag_Code').substr(0,3) >= 'V01' && rec.get('Diag_Code').substr(0,3) <= 'Y98');
							} else if (rec.attributes && rec.attributes.Diag_Code) {
								return (rec.attributes.Diag_Code.substr(0,3) >= 'V01' && rec.attributes.Diag_Code.substr(0,3) <= 'Y98');
							} else {
								return true;
							}
						},
						onChange: function (combo, newValue, oldValue) {
							//this.checkAbort();
						}.createDelegate(this)
					}, {
						border: false,
						hidden: sw.Promed.EvnPL.isHiddenFedResultFields(),
						layout: 'form',
						items: [{
							fieldLabel: 'Фед. результат',
							hiddenName: 'LeaveType_fedid',
							tabIndex: TABINDEX_EPLSTOMEF + 22,
							lastQuery:'',
							width: 500,
							xtype: 'swleavetypefedcombo'
						}, {
							fieldLabel: 'Фед. исход',
							hiddenName: 'ResultDeseaseType_fedid',
							tabIndex: TABINDEX_EPLSTOMEF + 23,
							lastQuery:'',
							width: 500,
							xtype: 'swresultdeseasetypefedcombo'
						}]
					}]
				}),
				new sw.Promed.Panel({
					border: true,
					collapsible: true,
					height: 200,
					id: 'EPLStomEF_EvnDrugPanel',
					isLoaded: false,
					layout: 'border',
					listeners: {
						'expand': function(panel) {
							if ( panel.isLoaded === false ) {
								panel.isLoaded = true;
								panel.findById('EPLStomEF_EvnDrugGrid').getStore().load({
									params: {
										EvnDrug_pid: this.findById('EvnPLStomEditForm').getForm().findField('EvnPLStom_id').getValue()
									}
								});
							}

							panel.doLayout();
						}.createDelegate(this)
					},
					style: 'margin-bottom: 0.5em;',
					title: '6. Использование медикаментов',
					items: [ new Ext.grid.GridPanel({
						autoExpandColumn: 'autoexpand_drug',
						autoExpandMin: 100,
						border: false,
						columns: [{
							dataIndex: 'EvnDrug_setDate',
							header: 'Дата',
							hidden: false,
							renderer: Ext.util.Format.dateRenderer('d.m.Y'),
							resizable: false,
							sortable: true,
							width: 100
						}, {
							dataIndex: 'Drug_Code',
							header: 'Код',
							hidden: false,
							resizable: false,
							sortable: true,
							width: 100
						}, {
							dataIndex: 'EvnDrug_Kolvo',
							header: 'Количество',
							hidden: false,
							resizable: true,
							sortable: true,
							width: 100
						}, {
							dataIndex: 'Drug_Name',
							header: 'Наименование',
							hidden: false,
							id: 'autoexpand_drug',
							resizable: true,
							sortable: true
						}],
						frame: false,
						id: 'EPLStomEF_EvnDrugGrid',
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

								var grid = this.findById('EPLStomEF_EvnDrugGrid');

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
										var base_form = this.findById('EvnPLStomEditForm').getForm();

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
									var toolbar = this.findById('EPLStomEF_EvnDrugGrid').getTopToolbar();

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
										LoadEmptyRow(this.findById('EPLStomEF_EvnDrugGrid'));
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
								text: 'Добавить'
							}, {
								handler: function() {
									this.openEvnDrugEditWindow('edit');
								}.createDelegate(this),
								iconCls: 'edit16',
								text: 'Изменить'
							}, {
								handler: function() {
									this.openEvnDrugEditWindow('view');
								}.createDelegate(this),
								iconCls: 'view16',
								text: 'Просмотр'
							}, {
								handler: function() {
									this.deleteEvent('EvnDrug');
								}.createDelegate(this),
								iconCls: 'delete16',
								text: 'Удалить'
							}, {
								iconCls: 'print16',
								text: 'Печать',
								handler: function() {
									var grid = this.findById('EPLStomEF_EvnDrugGrid');
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
					id: 'EPLStomEF_CostPrintPanel',
					layout: 'border',
					listeners: {
						'expand': function(panel) {
							panel.doLayout();
						}.createDelegate(this)
					},
					style: 'margin-bottom: 0.5em;',
					title: '8. Справка о стоимости лечения',
					hidden: ! getRegionNick().inlist(['perm', 'kz', 'ufa']),
					items: [{
						bodyStyle: 'padding-top: 0.5em;',
						border: false,
						height: 90,
						layout: 'form',
						region: 'center',
						items: [{
							fieldLabel: 'Дата выдачи справки/отказа',
							tabIndex: this.tabindex + 51,
							width: 100,
							plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
							name: 'EvnCostPrint_setDT',
							xtype: 'swdatefield'
						},{
							fieldLabel: 'Отказ',
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
					{ name: 'accessType' },
					{ name: 'canCreateVizit' },
					{ name: 'EvnPLStom_IsPaid' },
					{ name: 'EvnPLStom_IndexRep' },
					{ name: 'EvnPLStom_IndexRepInReg' },
					{ name: 'DirectClass_id' },
					{ name: 'EvnDirection_id' },
					{ name: 'EvnDirection_IsAuto' },
					{ name: 'EvnDirection_IsReceive' },
					{ name: 'Lpu_fid' },
					{ name: 'Org_did' },
					{ name: 'LpuSection_did' },
					{ name: 'Diag_did' },
					{ name: 'Diag_fid' },
					{ name: 'Diag_lid' },
					{ name: 'Diag_preid' },
					{ name: 'EvnDirection_Num' },
					{ name: 'EvnDirection_setDate' },
					{ name: 'DirectType_id' },
					{ name: 'EvnPLStom_id' },
					{ name: 'EvnPLStom_IsTransit' },
					{ name: 'EvnPLStom_IsFinish' },
					{ name: 'EvnPLStom_IsSurveyRefuse' },
					{ name: 'EvnPLStom_IsSan' },
					{ name: 'EvnPLStom_IsUnlaw' },
					{ name: 'EvnPLStom_IsUnport' },
					{ name: 'EvnPLStom_NumCard' },
					{ name: 'EvnPLStom_IsCons' },
					{ name: 'EvnPLStom_setDate' },
					{ name: 'EvnPLStom_disDate' },
					{ name: 'EvnPLStom_UKL' },
					{ name: 'Lpu_oid' },
					{ name: 'LpuSection_oid' },
					{ name: 'Person_id' },
					{ name: 'PersonEvn_id' },
					{ name: 'PrehospDirect_id' },
					{ name: 'PrehospTrauma_id' },
					{ name: 'ResultClass_id' },
					{ name: 'InterruptLeaveType_id' },
					{ name: 'ResultDeseaseType_id' },
					{ name: 'LeaveType_fedid' },
					{ name: 'ResultDeseaseType_fedid' },
					{ name: 'SanationStatus_id' },
					{ name: 'MedicalCareKind_id' },
					{ name: 'MedicalStatus_id' },
					{ name: 'Server_id' },
					{name: 'EvnCostPrint_setDT'},
					{name: 'EvnCostPrint_IsNoPrint'},
					{name: 'Diag_concid'}
				]),
				region: 'center',
				url: '/?c=EvnPLStom&m=saveEvnPLStom'
			})]
		});
		sw.Promed.swEvnPLStomEditWindow.superclass.initComponent.apply(this, arguments);

		this.findById('EvnPLStomEditForm').on('render', function(formPanel){
			formPanel.getForm().findField('ResultClass_id').on('change', function (combo, newValue) {
				var index = combo.getStore().findBy(function (rec) {
					return (rec.get('ResultClass_id') == newValue);
				});
				combo.fireEvent('select', combo, combo.getStore().getAt(index));
			});
			formPanel.getForm().findField('ResultClass_id').on('select', function (combo, record) {
				var lastEvnVizitPLStomDate;
				win.findById('EPLStomEF_EvnVizitPLStomGrid').getStore().each(function(record) {
					if ( Ext.isEmpty(lastEvnVizitPLStomDate) || (!Ext.isEmpty(record.get('EvnVizitPLStom_setDate')) && record.get('EvnVizitPLStom_setDate') <= lastEvnVizitPLStomDate) ) {
						lastEvnVizitPLStomDate = record.get('EvnVizitPLStom_setDate');
					}
				});
				if ( Ext.isEmpty(lastEvnVizitPLStomDate) ) {
					lastEvnVizitPLStomDate = new Date();
				}
				var base_form = formPanel.getForm();
				if(!win.fo){
					win.calcFedResultDeseaseType();
					win.calcFedLeaveType();
				}
				sw.Promed.EvnPL.filterFedResultDeseaseType({
					fieldFedLeaveType: base_form.findField('LeaveType_fedid'),
					fieldFedResultDeseaseType: base_form.findField('ResultDeseaseType_fedid')
				});
				sw.Promed.EvnPL.filterFedLeaveType({
					fieldFedLeaveType: base_form.findField('LeaveType_fedid'),
					fieldFedResultDeseaseType: base_form.findField('ResultDeseaseType_fedid')
				});
			});
		});
	},
	keys: [{
		alt: true,
		fn: function(inp, e) {
			var current_window = Ext.getCmp('EvnPLStomEditWindow');

			switch ( e.getKey() ) {
				case Ext.EventObject.C:
					current_window.doSave({
						ignoreDiagCountCheck: false,
						ignoreEvnVizitPLCountCheck: false
					});
				break;

				case Ext.EventObject.G:
					current_window.printEvnPLStom();
				break;

				case Ext.EventObject.J:
					current_window.onCancelAction();
				break;

				case Ext.EventObject.NUM_ONE:
				case Ext.EventObject.ONE:
					current_window.findById('EPLStomEF_DirectInfoPanel').toggleCollapse();
				break;

				case Ext.EventObject.NUM_TWO:
				case Ext.EventObject.TWO:
					current_window.findById('EPLStomEF_EvnVizitPLStomPanel').toggleCollapse();
				break;

				case Ext.EventObject.NUM_THREE:
				case Ext.EventObject.THREE:
					if ( current_window.formMode == 'morbus' ) {
						current_window.findById('EPLStomEF_EvnDiagPLStomPanel').toggleCollapse();
					}
					else {
						current_window.findById('EPLStomEF_EvnUslugaPanel').toggleCollapse();
					}
				break;

				case Ext.EventObject.FOUR:
				case Ext.EventObject.NUM_FOUR:
					if ( current_window.formMode == 'morbus' ) {
						current_window.findById('EPLStomEF_EvnUslugaPanel').toggleCollapse();
					}
					else {
						current_window.findById('EPLStomEF_EvnStickPanel').toggleCollapse();
					}
				break;

				case Ext.EventObject.FIVE:
				case Ext.EventObject.NUM_FIVE:
					if ( current_window.formMode == 'morbus' ) {
						current_window.findById('EPLStomEF_EvnStickPanel').toggleCollapse();
					}
					else {
						current_window.findById('EPLStomEF_ResultPanel').toggleCollapse();
					}
				break;

				case Ext.EventObject.NUM_SIX:
				case Ext.EventObject.SIX:
					if ( current_window.formMode == 'morbus' ) {
						current_window.findById('EPLStomEF_ResultPanel').toggleCollapse();
					}
					else {
						current_window.findById('EPLStomEF_EvnDrugPanel').toggleCollapse();
					}
				break;

				case Ext.EventObject.NUM_SEVEN:
				case Ext.EventObject.SEVEN:
					if ( current_window.formMode == 'morbus' ) {
						current_window.findById('EPLStomEF_EvnDrugPanel').toggleCollapse();
					}
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
			var current_window = Ext.getCmp('EvnPLStomEditWindow');

			switch ( e.getKey() ) {
				case Ext.EventObject.ESC:
					current_window.onCancelAction();
				break;

				case Ext.EventObject.F6:
					current_window.findById('EPLStomEF_PersonInformationFrame').panelButtonClick(1);
				break;

				case Ext.EventObject.F10:
					current_window.findById('EPLStomEF_PersonInformationFrame').panelButtonClick(2);
				break;

				case Ext.EventObject.F11:
					current_window.findById('EPLStomEF_PersonInformationFrame').panelButtonClick(3);
				break;

				case Ext.EventObject.F12:
					if ( e.ctrlKey == true ) {
						current_window.findById('EPLStomEF_PersonInformationFrame').panelButtonClick(5);
					}
					else {
						current_window.findById('EPLStomEF_PersonInformationFrame').panelButtonClick(4);
					}
				break;
			}
		},
		key: [
			Ext.EventObject.ESC,
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
			win.findById('EPLStomEF_CostPrintPanel').doLayout();
			win.findById('EPLStomEF_DirectInfoPanel').doLayout();
			win.findById('EPLStomEF_EvnDiagPLStomPanel').doLayout();
			win.findById('EPLStomEF_EvnDrugPanel').doLayout();
			win.findById('EPLStomEF_EvnStickPanel').doLayout();
			win.findById('EPLStomEF_EvnUslugaPanel').doLayout();
			win.findById('EPLStomEF_EvnVizitPLStomPanel').doLayout();
			win.findById('EPLStomEF_ResultPanel').doLayout();
		},
		'restore': function(win) {
			win.findById('EPLStomEF_CostPrintPanel').doLayout();
			win.findById('EPLStomEF_DirectInfoPanel').doLayout();
			win.findById('EPLStomEF_EvnDiagPLStomPanel').doLayout();
			win.findById('EPLStomEF_EvnDrugPanel').doLayout();
			win.findById('EPLStomEF_EvnStickPanel').doLayout();
			win.findById('EPLStomEF_EvnUslugaPanel').doLayout();
			win.findById('EPLStomEF_EvnVizitPLStomPanel').doLayout();
			win.findById('EPLStomEF_ResultPanel').doLayout();
		}
	},
	maximizable: true,
	minHeight: 550,
	minWidth: 800,
	modal: true,
	onCancelAction: function() {
		var base_form = this.findById('EvnPLStomEditForm').getForm();
		var evn_pl_stom_id = base_form.findField('EvnPLStom_id').getValue();

		if ( evn_pl_stom_id > 0 && this.action == 'add') {
			// удалить талон
			// закрыть окно после успешного удаления
			var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Удаление талона..." });
			loadMask.show();

			Ext.Ajax.request({
				callback: function(options, success, response) {
					loadMask.hide();

					if ( success ) {
						this.callback();
						this.hide();
					}
					else {
						sw.swMsg.alert('Ошибка', 'При удалении талона возникли ошибки');
						return false;
					}
				}.createDelegate(this),
				params: {
					Evn_id: evn_pl_stom_id
				},
				url: '/?c=Evn&m=deleteEvn'
			});
		}
		else {
			this.callback();
			this.hide();
		}
	},
	onHide: Ext.emptyFn,
	openEvnDiagPLStomEditWindow: function(action) {
		if ( Ext.isEmpty(action) || !action.inlist([ 'view' ]) ) {
			return false;
		}

		var base_form = this.findById('EvnPLStomEditForm').getForm();
		var grid = this.findById('EPLStomEF_EvnDiagPLStomGrid');

		if ( getWnd('swEvnDiagPLStomEditWindow').isVisible() ) {
			sw.swMsg.alert('Сообщение', 'Окно редактирования заболевания уже открыто');
			return false;
		}

		var params = {};
		var formParams = {};

		params.action = action;
		params.formMode = this.formMode;
		params.onHide = function() {
			grid.getView().focusRow(0);
			grid.getSelectionModel().selectFirstRow();
		}.createDelegate(this);
		params.Person_id = this.findById('EPLStomEF_PersonInformationFrame').getFieldValue('Person_id');
		params.Person_Birthday = this.findById('EPLStomEF_PersonInformationFrame').getFieldValue('Person_Birthday');
		params.Person_Firname = this.findById('EPLStomEF_PersonInformationFrame').getFieldValue('Person_Firname');
		params.Person_Secname = this.findById('EPLStomEF_PersonInformationFrame').getFieldValue('Person_Secname');
		params.Person_Surname = this.findById('EPLStomEF_PersonInformationFrame').getFieldValue('Person_Surname');

		var selected_record = grid.getSelectionModel().getSelected();

		if ( !selected_record || !selected_record.get('EvnDiagPLStom_id') ) {
			return false;
		}

		formParams.EvnDiagPLStom_id = selected_record.get('EvnDiagPLStom_id');

		var index = this.findById('EPLStomEF_EvnVizitPLStomGrid').getStore().findBy(function(rec) {
			return (rec.get('EvnVizitPLStom_id') == selected_record.get('EvnDiagPLStom_pid'));
		});

		if ( index >= 0 ) {
			var vizitRecord = this.findById('EPLStomEF_EvnVizitPLStomGrid').getStore().getAt(index);

			params.evnVizitData = {
				EvnVizitPLStom_setDate: vizitRecord.get('EvnVizitPLStom_setDate'),
				LpuSection_id: vizitRecord.get('LpuSection_id'),
				MedStaffFact_id: vizitRecord.get('MedStaffFact_id'),
				MedPersonal_id: vizitRecord.get('MedPersonal_id'),
				PayType_id: vizitRecord.get('PayType_id')
			}
		}

		params.formParams = formParams;
		params.archiveRecord = this.archiveRecord;

		getWnd('swEvnDiagPLStomEditWindow').show(params);
	},
	openEvnDrugEditWindow: function(action) {
		var wndName = getEvnDrugEditWindowName();
		if ( this.findById('EPLStomEF_EvnDrugPanel').hidden || this.findById('EPLStomEF_EvnDrugPanel').collapsed ) {
			return false;
		}

		if ( action != 'add' && action != 'edit' && action != 'view' ) {
			return false;
		}

		var base_form = this.findById('EvnPLStomEditForm').getForm();
		var grid = this.findById('EPLStomEF_EvnDrugGrid');

		if ( this.action == 'view') {
			if ( action == 'add') {
				return false;
			}
			else if ( action == 'edit' ) {
				action = 'view';
			}
		}

		if ( getWnd(wndName).isVisible() ) {
			sw.swMsg.alert('Сообщение', 'Окно добавления случая использования медикаментов уже открыто');
			return false;
		}

		if ( action == 'add' && Ext.isEmpty(base_form.findField('EvnPLStom_id').getValue()) ) {
			this.doSave({
				openChildWindow: function() {
					this.openEvnDrugEditWindow(action);
				}.createDelegate(this),
				print: false
			});
			return false;
		}

		var parent_evn_combo_data = [];

		this.findById('EPLStomEF_EvnVizitPLStomGrid').getStore().each(function(record) {
			parent_evn_combo_data.push({
				Evn_id: record.get('EvnVizitPLStom_id'),
				Evn_Name: Ext.util.Format.date(record.get('EvnVizitPLStom_setDate'), 'd.m.Y') + ' / ' + record.get('LpuSection_Name') + ' / ' + record.get('MedPersonal_Fio'),
				Evn_setDate: record.get('EvnVizitPLStom_setDate'),
				Evn_setTime: record.get('EvnVizitPLStom_setTime'),
				MedStaffFact_id: record.get('MedStaffFact_id'),
				Lpu_id: record.get('Lpu_id'),
				LpuSection_id: record.get('LpuSection_id'),
				MedPersonal_id: record.get('MedPersonal_id')
			});
		});

		var formParams = {};
		var params = {};
		var person_id = this.findById('EPLStomEF_PersonInformationFrame').getFieldValue('Person_id');
		var person_birthday = this.findById('EPLStomEF_PersonInformationFrame').getFieldValue('Person_Birthday');
		var person_firname = this.findById('EPLStomEF_PersonInformationFrame').getFieldValue('Person_Firname');
		var person_secname = this.findById('EPLStomEF_PersonInformationFrame').getFieldValue('Person_Secname');
		var person_surname = this.findById('EPLStomEF_PersonInformationFrame').getFieldValue('Person_Surname');

		params.type = 'PLStom';
		params.action = action;
		params.parentEvnComboData = parent_evn_combo_data;
		params.callback = function(data) {
			if ( !data || !data.evnDrugData ) {
				return false;
			}
			var grid = this.findById('EPLStomEF_EvnDrugGrid');
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
			formParams.EvnDrug_pid = base_form.findField('EvnPLStom_id').getValue();
		}
		else {
			var selected_record = grid.getSelectionModel().getSelected();

			if ( !selected_record || !selected_record.get('EvnDrug_id') ) {
				return false;
			}

			formParams.EvnDrug_id = selected_record.get('EvnDrug_id');
			formParams.EvnDrug_rid = base_form.findField('EvnPLStom_id').getValue();
		}

		params.formParams = formParams;
		params.archiveRecord = this.archiveRecord;

		getWnd(wndName).show(params);
	},
	openEvnStickEditWindow: function(action) {
		if ( action != 'add' && action != 'edit' && action != 'view' ) {
			return false;
		}

		var base_form = this.findById('EvnPLStomEditForm').getForm();
		var grid = this.findById('EPLStomEF_EvnStickGrid');

		if ( this.action == 'view') {
			if ( action == 'add') {
				return false;
			}
			else if ( action == 'edit' ) {
				action = 'view';
			}
		}

		if ( action == 'add' && base_form.findField('EvnPLStom_id').getValue() == 0 ) {
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
		var joborg_id = this.findById('EPLStomEF_PersonInformationFrame').getFieldValue('JobOrg_id');
		var params = {};
		var person_id = this.findById('EPLStomEF_PersonInformationFrame').getFieldValue('Person_id');
		var person_birthday = this.findById('EPLStomEF_PersonInformationFrame').getFieldValue('Person_Birthday');
		var person_firname = this.findById('EPLStomEF_PersonInformationFrame').getFieldValue('Person_Firname');
		var person_post = this.findById('EPLStomEF_PersonInformationFrame').getFieldValue('Person_Post');
		var person_secname = this.findById('EPLStomEF_PersonInformationFrame').getFieldValue('Person_Secname');
		var person_surname = this.findById('EPLStomEF_PersonInformationFrame').getFieldValue('Person_Surname');

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
		params.parentClass = 'EvnPLStom';
		params.Person_id = base_form.findField('Person_id').getValue();
		params.PersonEvn_id = base_form.findField('PersonEvn_id').getValue();
		params.Person_Birthday = person_birthday;
		params.Person_Firname = person_firname;
		params.Person_Post = person_post;
		params.Person_Secname = person_secname;
		params.Person_Surname = person_surname;
		params.Server_id = base_form.findField('Server_id').getValue();

		formParams.EvnStick_mid = base_form.findField('EvnPLStom_id').getValue();

		if ( action == 'add' ) {
			var evn_stick_beg_date = null;
			var evn_vizit_pl_stom_store = this.findById('EPLStomEF_EvnVizitPLStomGrid').getStore();

			evn_vizit_pl_stom_store.each(function(record) {
				if ( evn_stick_beg_date == null || record.get('EvnVizitPLStom_setDate') <= evn_stick_beg_date ) {
					evn_stick_beg_date = record.get('EvnVizitPLStom_setDate');
				}
			});

			formParams.EvnStick_begDate = evn_stick_beg_date;
			formParams.EvnStick_pid = base_form.findField('EvnPLStom_id').getValue();
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
		if ( Ext.isEmpty(action) || !action.inlist([ 'add', 'edit', 'view' ]) ) {
			return false;
		}

		// Если Уфа, то добавление услуги с формы редактирования талона недоступно
		if ( action == 'add' && getRegionNick() == 'ufa' ) {
			return false;
		}
		else if ( getRegionNick() == 'perm' && this.formMode == 'morbus' ) {
			if ( action == 'add' ) {
				return false;
			}

			action = 'view';
		}

		if ( this.action == 'view' && !this.canCreateVizit) {
			if ( action == 'add') {
				return false;
			}
			else if ( action == 'edit' ) {
				action = 'view';
			}
		}

		var base_form = this.findById('EvnPLStomEditForm').getForm();
		var grid = this.findById('EPLStomEF_EvnUslugaGrid');
		var params = {};

		// Собрать данные для ParentEvnCombo
		var parent_evn_combo_data = [];

		// Формируем parent_evn_combo_data
		var evn_vizit_pl_stom_grid = this.findById('EPLStomEF_EvnVizitPLStomGrid');

		evn_vizit_pl_stom_grid.getStore().each(function(record) {
			var temp_record = {};

			temp_record.Evn_id = record.get('EvnVizitPLStom_id');
			temp_record.Evn_Name = Ext.util.Format.date(record.get('EvnVizitPLStom_setDate'), 'd.m.Y') + ' / ' + record.get('LpuSection_Name') + ' / ' + record.get('MedPersonal_Fio');
			temp_record.Evn_setDate = record.get('EvnVizitPLStom_setDate');
			temp_record.Evn_setTime = record.get('EvnVizitPLStom_setTime');
			temp_record.MedStaffFact_id = record.get('MedStaffFact_id');
			temp_record.LpuSection_id = record.get('LpuSection_id');
			temp_record.MedPersonal_id = record.get('MedPersonal_id');
			temp_record.ServiceType_SysNick = record.get('ServiceType_SysNick');
			temp_record.VizitType_SysNick = record.get('VizitType_SysNick');
			temp_record.Diag_id = record.get('Diag_id');

			parent_evn_combo_data.push(temp_record);
		});

		params.action = action;
		params.callback = function(data) {
			if ( true || !data || !data.evnUslugaData ) {
				grid.getStore().load({
					params: {
						pid: base_form.findField('EvnPLStom_id').getValue(),
						rid: base_form.findField('EvnPLStom_id').getValue()
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
		params.formMode = this.formMode;
		params.onHide = function() {
			grid.getView().focusRow(0);
			grid.getSelectionModel().selectFirstRow();
		}.createDelegate(this);
		params.parentClass = 'EvnPLStom';
		params.parentEvnComboData = parent_evn_combo_data;
		params.Person_id = this.findById('EPLStomEF_PersonInformationFrame').getFieldValue('Person_id');
		params.Person_Birthday = this.findById('EPLStomEF_PersonInformationFrame').getFieldValue('Person_Birthday');
		params.Person_Firname = this.findById('EPLStomEF_PersonInformationFrame').getFieldValue('Person_Firname');
		params.Person_Secname = this.findById('EPLStomEF_PersonInformationFrame').getFieldValue('Person_Secname');
		params.Person_Surname = this.findById('EPLStomEF_PersonInformationFrame').getFieldValue('Person_Surname');

		// Собрать данные для ParentEvnCombo

		switch ( action ) {
			case 'add':
				if ( base_form.findField('EvnPLStom_id').getValue() == 0 ) {
					this.doSave({
						openChildWindow: function() {
							this.openEvnUslugaEditWindow(action);
						}.createDelegate(this)
					});
					return false;
				}

				// Открываем форму выбора класса услуги
				if ( getWnd('swEvnUslugaSetWindow').isVisible() ) {
					sw.swMsg.alert('Сообщение', 'Окно выбора типа услуги уже открыто', function() {
						grid.getSelectionModel().selectFirstRow();
						grid.getView().focusRow(0);
					});
					return false;
				}

				params.formParams = {
					Person_id: base_form.findField('Person_id').getValue(),
					PersonEvn_id: base_form.findField('PersonEvn_id').getValue(),
					Server_id: base_form.findField('Server_id').getValue()
				};

				getWnd('swEvnUslugaSetWindow').show({
					EvnUsluga_rid: base_form.findField('EvnPLStom_id').getValue(),
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
					parentEvent: 'EvnPLStom'
				});
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

				switch ( selected_record.get('EvnClass_SysNick') ) {
					case 'EvnUslugaCommon':
						params.formParams = {
							EvnUslugaCommon_id: evn_usluga_id
						};
						getWnd('swEvnUslugaEditWindow').show(params);
					break;

					case 'EvnUslugaStom':
						params.formParams = {
							EvnUslugaStom_id: evn_usluga_id
						};
						getWnd('swEvnUslugaStomEditWindow').show(params);
					break;
					case 'EvnUslugaOper':
						params.formParams = {
							EvnUslugaOper_id: evn_usluga_id
						};
						getWnd('swEvnUslugaOperEditWindow').show(params);
					break;

					default:
						return false;
					break;
				}
			break;
		}
	},
	openEvnVizitPLStomEditWindow: function(action) {
		if ( action != 'add' && action != 'edit' && action != 'view' ) {
			return false;
		}

		var base_form = this.findById('EvnPLStomEditForm').getForm();
		var grid = this.findById('EPLStomEF_EvnVizitPLStomGrid');

		if ( this.action == 'view') {
			if ( action == 'add' && this.gridAccess == 'view' ) {
				return false;
			}
			else if ( action == 'edit' && this.gridAccess == 'view' ) {
				action = 'view';
			}
		}

		if ( action == 'add' && Ext.isEmpty(base_form.findField('EvnPLStom_setDate').getValue()) ) {
			sw.swMsg.alert('Сообщение', 'Укажите дату начала случая', function() { base_form.findField('EvnPLStom_setDate').focus(true); });
			return false;
		}

		if ( getWnd('swEvnVizitPLStomEditWindow').isVisible() ) {
			sw.swMsg.alert('Сообщение', 'Окно редактирования посещения пациентом поликлиники уже открыто');
			return false;
		}

		/*http://redmine.swan.perm.ru/issues/20417*/
		var allowMorbusVizitOnly = false;
		var allowNonMorbusVizitOnly = false;
		var firstVizitData = null;

		grid.getStore().each(function(record) {
			if ( Ext.isEmpty(firstVizitData) || (!Ext.isEmpty(record.get('EvnVizitPLStom_setDate')) && record.get('EvnVizitPLStom_setDate') <= firstVizitData.EvnVizitPLStom_setDate) ) {
				firstVizitData = record;
			}
		});

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
				sw.swMsg.alert('Сообщение', 'Добавление посещения невозможно, т.к. в рамках текущего ТАП уже есть посещение');
				return false;
			}
		}
		//Проверка на второе посещение НМП
		if ( action == 'add' && getRegionNick().inlist(['buryatiya']) ) {
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
		if ( action == 'add' && base_form.findField('EvnPLStom_id').getValue() == 0 ) {
			this.doSave({
				ignoreDiagCountCheck: true,
				ignoreEvnVizitPLCountCheck: true,
				openChildWindow: function() {
					this.openEvnVizitPLStomEditWindow(action);
				}.createDelegate(this),
				print: false
			});
			return false;
		}

		var formParams = {};
		var params = {};

		params.action = action;
		params.allowMorbusVizitOnly = allowMorbusVizitOnly;
		params.allowNonMorbusVizitOnly = allowNonMorbusVizitOnly;
		params.TreatmentClass_id = (!Ext.isEmpty(firstVizitData) && (grid.getStore().getCount() > 1 || action == 'add') ? firstVizitData.get('TreatmentClass_id') : null);
		params.callback = function(data) {
			if ( action == 'add') {
				// т.к. было обслужено
				this.params.TimetableGraf_id = null;
				this.params.EvnDirection_id = null;
			}
			if ( !data || !data.evnVizitPLStomData ) {
				return false;
			}

			grid.getStore().load({
				params: {
					EvnVizitPLStom_pid: base_form.findField('EvnPLStom_id').getValue()
				},
				callback: function (records, options, success) {
					if ( success ) {
						grid.getView().focusRow(0);
						grid.getSelectionModel().selectFirstRow();

						var firstVizitData = null;
						var lastVizitData = null;

						grid.getStore().each(function (record) {
							if (Ext.isEmpty(firstVizitData) || (!Ext.isEmpty(record.get('EvnVizitPLStom_setDate')) && record.get('EvnVizitPLStom_setDate') <= firstVizitData.EvnVizitPLStom_setDate)) {
								firstVizitData = record.data;
							}
							if (Ext.isEmpty(lastVizitData) || (!Ext.isEmpty(record.get('EvnVizitPLStom_setDate')) && record.get('EvnVizitPLStom_setDate') >= lastVizitData.EvnVizitPLStom_setDate)) {
								lastVizitData = record.data;
							}
						});

						if (!getRegionNick().inlist(['ufa'])) {
							var diag_f_combo = base_form.findField('Diag_fid');
							if (Ext.isEmpty(diag_f_combo.getValue()) && firstVizitData && !Ext.isEmpty(firstVizitData.Diag_id)) {
								diag_f_combo.getStore().load({
									params: {
										where: "where DiagLevel_id = 4 and Diag_id = " + firstVizitData.Diag_id
									},
									callback: function () {
										diag_f_combo.setValue(firstVizitData.Diag_id);
									}
								});
							}


							var diag_l_combo = base_form.findField('Diag_lid');
							if (/*Ext.isEmpty(diag_l_combo.getValue()) &&*/ lastVizitData && !Ext.isEmpty(lastVizitData.Diag_id)) {
								diag_l_combo.getStore().load({
									params: {
										where: "where DiagLevel_id = 4 and Diag_id = " + lastVizitData.Diag_id
									},
									callback: function () {
										diag_l_combo.setValue(lastVizitData.Diag_id);
									}
								});
							}
						}

						if (firstVizitData && !Ext.isEmpty(firstVizitData.EvnVizitPLStom_setDate)) {
							base_form.findField('EvnPLStom_setDate').disable();
							base_form.findField('EvnPLStom_setDate').setValue(firstVizitData.EvnVizitPLStom_setDate);
							base_form.findField('EvnPLStom_setDate').fireEvent('change', base_form.findField('EvnPLStom_setDate'), base_form.findField('EvnPLStom_setDate').getValue());
						}

						this.checkTrauma();
						this.uetValuesRecount();
					}
				}.createDelegate(this)
			});
		}.createDelegate(this);
		params.from = this.params.from;
		params.onHide = function(options) {
			if ( this.findById('EPLStomEF_EvnDiagPLStomPanel').isLoaded === true ) {
				this.findById('EPLStomEF_EvnDiagPLStomGrid').getStore().load({
					params: {
						rid: this.findById('EvnPLStomEditForm').getForm().findField('EvnPLStom_id').getValue()
					},
					callback: function() {
						this.setDiagConcComboVisible();
					}.createDelegate(this)
				});
			}

			if ( this.findById('EPLStomEF_EvnUslugaPanel').isLoaded === true ) {
				this.findById('EPLStomEF_EvnUslugaGrid').getStore().load({
					params: {
						pid: base_form.findField('EvnPLStom_id').getValue(),
						rid: base_form.findField('EvnPLStom_id').getValue()
					}
				});
			}

			grid.getView().focusRow(0);
			grid.getSelectionModel().selectFirstRow();
		}.createDelegate(this);
		params.Person_id = this.findById('EPLStomEF_PersonInformationFrame').getFieldValue('Person_id');
		params.Person_Birthday = this.findById('EPLStomEF_PersonInformationFrame').getFieldValue('Person_Birthday');
		params.Person_Firname = this.findById('EPLStomEF_PersonInformationFrame').getFieldValue('Person_Firname');
		params.Person_Secname = this.findById('EPLStomEF_PersonInformationFrame').getFieldValue('Person_Secname');
		params.Person_Surname = this.findById('EPLStomEF_PersonInformationFrame').getFieldValue('Person_Surname');
		params.TimetableGraf_id = this.params.TimetableGraf_id;
		params.streamInput = this.streamInput; // Признак добавления посещения с формы поточного ввода
		var lastEvnVizitPLStomDate = null;
		grid.getStore().each(function(record) {
			if ( Ext.isEmpty(lastEvnVizitPLStomDate) || record.get('EvnVizitPLStom_setDate') > lastEvnVizitPLStomDate ) {
				lastEvnVizitPLStomDate = record.get('EvnVizitPLStom_setDate');
			}
		});
		params.lastEvnVizitPLStomDate = lastEvnVizitPLStomDate;

		if ( action == 'add' ) {
			formParams = this.params;
			formParams.EvnPLStom_id = base_form.findField('EvnPLStom_id').getValue();
			formParams.EvnVizitPLStom_id = 0;
			formParams.Person_id = base_form.findField('Person_id').getValue();
			formParams.PersonEvn_id = base_form.findField('PersonEvn_id').getValue();
			formParams.Server_id = base_form.findField('Server_id').getValue();
			if ( grid.getStore().getCount() == 0 || !grid.getStore().getAt(0).get('EvnVizitPLStom_id') ) {
				params.loadLastData = false;
				params.isRepeatVizit = false;
				// только для первого
				formParams.EvnDirection_id = base_form.findField('EvnDirection_id').getValue();

				if ( this.formMode == 'morbus' && Ext.isEmpty(formParams.EvnVizitPLStom_setDate) ) {
					formParams.EvnVizitPLStom_setDate = base_form.findField('EvnPLStom_setDate').getValue();
				}
			} else {
				/*if (this.action == 'add' ) {
					params.TimetableGraf_id = null;
					formParams.TimetableGraf_id = null;
					formParams.EvnDirection_id = null;
				}*/
				params.loadLastData = true;
				params.isRepeatVizit = true;
				formParams.EvnVizitPLStom_setDate = null; // для второго посещения дата должна быть текущая.
			}
		}
		else {
			var selected_record = grid.getSelectionModel().getSelected();

			if ( !selected_record || !selected_record.get('EvnVizitPLStom_id') ) {
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
				|| selected_record.get('EvnVizitPLStom_IsSigned') != 1
			) {
				params.action = 'view';
			}

			formParams.EvnVizitPLStom_id = selected_record.get('EvnVizitPLStom_id');
			formParams.Person_id = selected_record.get('Person_id');
			formParams.Server_id = selected_record.get('Server_id');
		}

		formParams.ResultClass_id = base_form.findField('ResultClass_id').getValue();
		formParams.EvnPLStom_IsFinish = base_form.findField('EvnPLStom_IsFinish').getValue();
		formParams.EvnPLStom_setDate = Ext.util.Format.date(base_form.findField('EvnPLStom_setDate').getValue(), 'd.m.Y');
		params.formParams = formParams;
		params.archiveRecord = this.archiveRecord;

		params.OtherVizitList = getStoreRecords(grid.getStore(), {
			convertDateFields: true,
			exceptionRecordIds: [formParams.EvnVizitPLStom_id]
		});

		getWnd('swEvnVizitPLStomEditWindow').show(params);
	},
	params: {
		EvnVizitPLStom_setDate: null,
		LpuSection_id: null,
		MedPersonal_id: null,
		MedPersonal_sid: null,
		PayType_id: null,
		ServiceType_id: null,
		UslugaComplex_uid: null,
		VizitType_id: null
	},
	plain: true,
	printEvnPLStom: function() {
		if ( 'add' == this.action || 'edit' == this.action ) {
			this.doSave({
				ignoreDiagCountCheck: false,
				ignoreEvnVizitPLCountCheck: false,
				print: true
			});
		}
		else if ( 'view' == this.action ) {
			var evn_pl_stom_id = this.findById('EvnPLStomEditForm').getForm().findField('EvnPLStom_id').getValue();
			printEvnPL({
				type: 'EvnPLStom',
				EvnPL_id: evn_pl_stom_id
			});
		}
	},
	resizable: true,
	checkForCostPrintPanel: function() {
		var base_form = this.findById('EvnPLStomEditForm').getForm();

		this.findById('EPLStomEF_CostPrintPanel').hide();
		base_form.findField('EvnCostPrint_setDT').setAllowBlank(true);
		base_form.findField('EvnCostPrint_IsNoPrint').setAllowBlank(true);

		// если справка уже печаталась и случай закрыт, отображаем раздел с данными справки
		if (base_form.findField('EvnPLStom_IsFinish').getValue() == 2 && !Ext.isEmpty(base_form.findField('EvnCostPrint_setDT').getValue()) && getRegionNick().inlist(['perm', 'kz', 'ufa'])) {
			this.findById('EPLStomEF_CostPrintPanel').show();
			// поля обязтаельные
			base_form.findField('EvnCostPrint_setDT').setAllowBlank(false);
			base_form.findField('EvnCostPrint_IsNoPrint').setAllowBlank(false);
		}
	},
	calcFedLeaveType: function() {
		var base_form = this.findById('EvnPLStomEditForm').getForm();
		var lastEvnVizitPLStomDate;
		this.findById('EPLStomEF_EvnVizitPLStomGrid').getStore().each(function(record) {
			if ( Ext.isEmpty(lastEvnVizitPLStomDate) || (!Ext.isEmpty(record.get('EvnVizitPLStom_setDate')) && record.get('EvnVizitPLStom_setDate') <= lastEvnVizitPLStomDate) ) {
				lastEvnVizitPLStomDate = record.get('EvnVizitPLStom_setDate');
			}
		});
		sw.Promed.EvnPL.calcFedLeaveType({
			is2016: Ext.isEmpty(lastEvnVizitPLStomDate) || lastEvnVizitPLStomDate >= sw.Promed.EvnPL.getDateX2016(),
			disableToogleContainer: true,
			InterruptLeaveType_id: base_form.findField('InterruptLeaveType_id').getValue(),
			LeaveType_fedid: base_form.findField('ResultClass_id').getFieldValue('LeaveType_fedid'),
			ResultClass_Code: base_form.findField('ResultClass_id').getFieldValue('ResultClass_Code'),
			DirectType_Code: base_form.findField('DirectType_id').getFieldValue('DirectType_Code'),
			DirectClass_Code: base_form.findField('DirectClass_id').getFieldValue('DirectClass_Code'),
			fieldFedLeaveType: base_form.findField('LeaveType_fedid')
		});
	},
	calcFedResultDeseaseType: function() {
		var base_form = this.findById('EvnPLStomEditForm').getForm();
		var lastEvnVizitPLStomDate;
		this.findById('EPLStomEF_EvnVizitPLStomGrid').getStore().each(function(record) {
			if ( Ext.isEmpty(lastEvnVizitPLStomDate) || (!Ext.isEmpty(record.get('EvnVizitPLStom_setDate')) && record.get('EvnVizitPLStom_setDate') <= lastEvnVizitPLStomDate) ) {
				lastEvnVizitPLStomDate = record.get('EvnVizitPLStom_setDate');
			}
		});
		sw.Promed.EvnPL.calcFedResultDeseaseType({
			is2016: Ext.isEmpty(lastEvnVizitPLStomDate) || lastEvnVizitPLStomDate >= sw.Promed.EvnPL.getDateX2016(),
			disableToogleContainer: true,
			InterruptLeaveType_id: base_form.findField('InterruptLeaveType_id').getValue(),
			DirectType_Code: base_form.findField('DirectType_id').getFieldValue('DirectType_Code'),
			ResultClass_Code: base_form.findField('ResultClass_id').getFieldValue('ResultClass_Code'),
			fieldFedResultDeseaseType: base_form.findField('ResultDeseaseType_fedid')
		});
	},
	setDiagFidAndLid: function() {
		// автоматически проставляем предварительный и заключительный диагнозы на основе первого и последнего посещения
		if (getRegionNick() != 'ufa') {
			return false;
		}

		var base_form = this.findById('EvnPLStomEditForm').getForm(), win = this;
		if (base_form.findField('EvnPLStom_IsFinish').getValue() != 2) {
			base_form.findField('Diag_fid').clearValue(); // предварительный
			base_form.findField('Diag_lid').clearValue(); // заключительный
			win.setDiagConcComboVisible();
			return true;
		}

		var firstEvnVizit = null;
		var lastEvnVizit = null;
		this.findById('EPLStomEF_EvnVizitPLStomGrid').getStore().each(function (record) {
			if (Ext.isEmpty(firstEvnVizit) || (!Ext.isEmpty(record.get('EvnVizitPLStom_setDate')) && record.get('EvnVizitPLStom_setDate') <= firstEvnVizit.get('EvnVizitPLStom_setDate'))) {
				firstEvnVizit = record;
			}
			if (Ext.isEmpty(lastEvnVizit) || (!Ext.isEmpty(record.get('EvnVizitPLStom_setDate')) && record.get('EvnVizitPLStom_setDate') >= lastEvnVizit.get('EvnVizitPLStom_setDate'))) {
				lastEvnVizit = record;
			}
		});

		if (!Ext.isEmpty(firstEvnVizit) && !Ext.isEmpty(lastEvnVizit)) {
			if (firstEvnVizit.get('Diag_id')) {
				base_form.findField('Diag_fid').getStore().load({
					callback: function () {
						base_form.findField('Diag_fid').setValue(firstEvnVizit.get('Diag_id'));
					},
					params: {where: "where DiagLevel_id = 4 and Diag_id = " + firstEvnVizit.get('Diag_id')}
				});
			}

			if (lastEvnVizit.get('Diag_id')) {
				base_form.findField('Diag_lid').getStore().load({
					callback: function () {
						base_form.findField('Diag_lid').setValue(lastEvnVizit.get('Diag_id'));
						win.setDiagConcComboVisible();
					},
					params: {where: "where DiagLevel_id = 4 and Diag_id = " + lastEvnVizit.get('Diag_id')}
				});
			}
		}
	},
	onEnableEdit: function() {
		// поля предварит. диагноз и поле заключ. диагноз для Уфы недоступны для редактирования
		var base_form = this.findById('EvnPLStomEditForm').getForm();
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
	uetValuesRecount: function() {
		var base_form = this.findById('EvnPLStomEditForm').getForm();
		var grid = this.findById('EPLStomEF_EvnVizitPLStomGrid');

		var evn_usluga_stom_uet_oms = 0;

		grid.getStore().each(function(record) {
			evn_usluga_stom_uet_oms = evn_usluga_stom_uet_oms + Number(record.get('EvnVizitPLStom_Uet'));
		});

		base_form.findField('EvnPLStom_UET').setValue(evn_usluga_stom_uet_oms.toFixed(2));
	},
	delDocsView: false,
	show: function() {
		sw.Promed.swEvnPLStomEditWindow.superclass.show.apply(this, arguments);
		var win = this;
		
		this.findById('EPLStomEF_CostPrintPanel').collapse();
		this.findById('EPLStomEF_DirectInfoPanel').collapse();
		this.findById('EPLStomEF_EvnDiagPLStomPanel').collapse();
		this.findById('EPLStomEF_EvnDrugPanel').collapse();
		this.findById('EPLStomEF_EvnStickPanel').collapse();
		this.findById('EPLStomEF_EvnUslugaPanel').collapse();
		this.findById('EPLStomEF_EvnVizitPLStomPanel').expand();
		this.findById('EPLStomEF_ResultPanel').expand();

		this.panelEvnDirectionAll.onReset();
		this.panelEvnDirectionAll.useCase = 'choose_for_evnplstom';

		this.restore();
		this.center();
		this.maximize();

		var base_form = this.findById('EvnPLStomEditForm').getForm();
		base_form.reset();

		this.checkForCostPrintPanel();

		base_form.findField('ResultDeseaseType_id').setContainerVisible( getRegionNick().inlist(['adygeya', 'vologda','buryatiya','kareliya','krasnoyarsk','ekb','pskov','penza','krym','yakutiya','yaroslavl']) );
		base_form.findField('LeaveType_fedid').on('change', function (combo, newValue) {
			sw.Promed.EvnPL.filterFedResultDeseaseType({
				fieldFedLeaveType: base_form.findField('LeaveType_fedid'),
				fieldFedResultDeseaseType: base_form.findField('ResultDeseaseType_fedid')
			})
		});
		base_form.findField('ResultDeseaseType_fedid').on('change', function (combo, newValue) {
			sw.Promed.EvnPL.filterFedLeaveType({
				fieldFedLeaveType: base_form.findField('LeaveType_fedid'),
				fieldFedResultDeseaseType: base_form.findField('ResultDeseaseType_fedid')
			});
		});

		if ( getRegionNick().inlist(['krasnoyarsk','adygeya','yakutiya','yaroslavl']) ) {
			base_form.findField('ResultDeseaseType_id').getStore().filterBy(function(rec) {
				return (!Ext.isEmpty(rec.get('ResultDeseaseType_Code')) && rec.get('ResultDeseaseType_Code').toString().substr(0, 1) == '3');
			});
		}

		this.action = 'add';
		this.callback = Ext.emptyFn;
		this.formStatus = 'edit';
		this.gridAccess = 'view';
		this.onHide = Ext.emptyFn;
		this.PersonEvn_id = null;
		this.streamInput = false;

		this.filterResultClassCombo();
		this.setMedicalStatusComboVisible();
		this.setDiagConcComboVisible();
		this.setInterruptLeaveTypeVisible();
		this.fo = true;
		this.params.EvnVizitPLStom_setDate = null;
		this.params.EvnVizitPLStom_setTime = null;
		this.params.LpuSection_id = null;
		this.params.MedPersonal_id = null;
		this.params.MedPersonal_sid = null;
		this.params.PayType_id = null;
		this.params.ServiceType_id = null;
		this.params.VizitType_id = null;
		this.params.UslugaComplex_uid = null;
		this.params.MedicalCareKind_id = null;
		this.params.from = null;
		this.params.TimetableGraf_id = null;
		base_form.findField('Diag_did').filterDate = null;

		base_form.findField('EvnDirection_Num').disable();
		base_form.findField('EvnDirection_setDate').disable();
		base_form.findField('EvnPLStom_IsUnlaw').disable();
		base_form.findField('Lpu_oid').disable();
		base_form.findField('LpuSection_did').disable();
		base_form.findField('LpuSection_oid').disable();
		base_form.findField('Org_did').disable();

		if ( !arguments[0] ) {
			sw.swMsg.alert('Сообщение', 'Неверные параметры');
			return false;
		}

		if (arguments[0].delDocsView) {
			this.delDocsView = arguments[0].delDocsView;
		}

		base_form.setValues(arguments[0]);

		if ( arguments[0].action ) {
			this.action = arguments[0].action;
		}

		if ( arguments[0].callback ) {
			this.callback = arguments[0].callback;
		}

		if ( arguments[0].EvnVizitPLStom_setDate ) {
			this.params.EvnVizitPLStom_setDate = arguments[0].EvnVizitPLStom_setDate;
		}

		if ( arguments[0].EvnVizitPLStom_setTime ) {
			this.params.EvnVizitPLStom_setTime = arguments[0].EvnVizitPLStom_setTime;
		}

		if ( arguments[0].LpuSection_id ) {
			this.params.LpuSection_id = arguments[0].LpuSection_id;
		}

		if ( arguments[0].MedPersonal_id ) {
			this.params.MedPersonal_id = arguments[0].MedPersonal_id;
		}

		if ( arguments[0].MedPersonal_sid ) {
			this.params.MedPersonal_sid = arguments[0].MedPersonal_sid;
		}

		if ( arguments[0].onHide ) {
			this.onHide = arguments[0].onHide;
		}

		if ( arguments[0].PayType_id ) {
			this.params.PayType_id = arguments[0].PayType_id;
		}

		if ( arguments[0].from ) {
			this.params.from = arguments[0].from;
		}

		if ( arguments[0].PersonEvn_id && arguments[0].usePersonEvn ) {
			this.PersonEvn_id = arguments[0].PersonEvn_id;
		}

		if ( arguments[0].Server_id && arguments[0].usePersonEvn ) {
			this.Server_id = arguments[0].Server_id;
		} else {
			this.Server_id = null;
		}

		if ( arguments[0].ServiceType_id ) {
			this.params.ServiceType_id = arguments[0].ServiceType_id;
		}

		if ( arguments[0].MedicalCareKind_id ) {
			this.params.MedicalCareKind_id = arguments[0].MedicalCareKind_id;
		}

		if ( arguments[0].streamInput ) {
			this.streamInput = arguments[0].streamInput;
			this.panelEvnDirectionAll.useCase = 'choose_for_evnplstom_stream_input';
		}

		if ( arguments[0].VizitType_id ) {
			this.params.VizitType_id = arguments[0].VizitType_id;
		}

		if ( arguments[0].UslugaComplex_uid ) {
			this.params.UslugaComplex_uid = arguments[0].UslugaComplex_uid;
		}

		if ( arguments[0].TimetableGraf_id ) {
			this.params.TimetableGraf_id = arguments[0].TimetableGraf_id;
		}

		if ( this.action == 'add' ) {
			this.findById('EPLStomEF_EvnDiagPLStomPanel').isLoaded = true;
			this.findById('EPLStomEF_EvnDrugPanel').isLoaded = true;
			this.findById('EPLStomEF_EvnStickPanel').isLoaded = true;
			this.findById('EPLStomEF_EvnUslugaPanel').isLoaded = true;
		}
		else {
			this.findById('EPLStomEF_EvnDiagPLStomPanel').isLoaded = false;
			this.findById('EPLStomEF_EvnDrugPanel').isLoaded = false;
			this.findById('EPLStomEF_EvnStickPanel').isLoaded = false;
			this.findById('EPLStomEF_EvnUslugaPanel').isLoaded = false;
		}

		var evn_pl_stom_id = base_form.findField('EvnPLStom_id').getValue();

		base_form.findField('EvnPLStom_RepFlag').hideContainer();
		this.loadMask = new Ext.LoadMask(this.getEl(), {msg: LOAD_WAIT});
		this.loadMask.show();
		
		base_form.findField('Diag_did').on('change', function (combo, newValue) {
			if (win.formMode == 'morbus') {
				var diag = combo.getStore().getById(newValue);
				if (diag != undefined) {
					var diagGroup = diag.get('Diag_Code')[0];
					if (diagGroup == "S" || diagGroup == "T") {
						base_form.findField('Diag_preid').setDisabled(false);
						base_form.findField('Diag_preid').setContainerVisible(true);
					} else {
						base_form.findField('Diag_preid').setDisabled(true);
						base_form.findField('Diag_preid').setContainerVisible(false);
						base_form.findField('Diag_preid').clearValue();
					}
				}
			}
		});

		//Проверяем возможность редактирования документа
		if ( this.action == 'edit' ) {
			Ext.Ajax.request({
				failure: function(response, options) {
					sw.swMsg.alert('Ошибка', 'Ошибка при загрузке данных формы', function() {this.hide();}.createDelegate(this) );
				},
				params: {
					Evn_id: evn_pl_stom_id,
					MedStaffFact_id: (!Ext.isEmpty(sw.Promed.MedStaffFactByUser) && !Ext.isEmpty(sw.Promed.MedStaffFactByUser.current) && !Ext.isEmpty(sw.Promed.MedStaffFactByUser.current.MedStaffFact_id)) ? sw.Promed.MedStaffFactByUser.current.MedStaffFact_id : null,
					ArmType: (!Ext.isEmpty(sw.Promed.MedStaffFactByUser) && !Ext.isEmpty(sw.Promed.MedStaffFactByUser.current) && !Ext.isEmpty(sw.Promed.MedStaffFactByUser.current.ARMType)) ? sw.Promed.MedStaffFactByUser.current.ARMType : null
				},
				success: function(response, options) {
					if (!Ext.isEmpty(response.responseText)) {
						var response_obj = Ext.util.JSON.decode(response.responseText);

						if ( response_obj.success == false ) {
							sw.swMsg.alert('Ошибка', response_obj.Error_Msg ? response_obj.Error_Msg : 'Ошибка при проверке возможности редактировать документ');
							win.action = 'view';
							if(getRegionNick() == 'vologda') {
								win.gridAccess = 'full';
							}
						}

						if (response_obj.Alert_Msg) {
							sw.swMsg.alert(langs('Внимание'), response_obj.Alert_Msg);
						}
					}
					else {
						win.gridAccess = 'full';
					}

					win.onShow();
				}.createDelegate(this),
				url: '/?c=Evn&m=CommonChecksForEdit'
			});
		}
		else {
			if ( win.action == 'add' ) {
				win.gridAccess = 'full';
			}

			win.onShow();
		}
	},
	onShow: function() {
		var win = this;
		var base_form = this.findById('EvnPLStomEditForm').getForm();

		var direct_class_combo = base_form.findField('DirectClass_id');
		var is_finish_combo = base_form.findField('EvnPLStom_IsFinish');
		var is_unlaw_combo = base_form.findField('EvnPLStom_IsUnlaw');
		var is_unport_combo = base_form.findField('EvnPLStom_IsUnport');
		var lpu_section_dir_combo = base_form.findField('LpuSection_oid');
		var org_dir_combo = base_form.findField('Lpu_oid');
		var prehosp_trauma_combo = base_form.findField('PrehospTrauma_id');
		var result_class_combo = base_form.findField('ResultClass_id');
		var medical_care_kind_combo = base_form.findField('MedicalCareKind_id');

		var evn_pl_stom_id = base_form.findField('EvnPLStom_id').getValue();
		var person_id = base_form.findField('Person_id').getValue();
		//var server_id = base_form.findField('Server_id').getValue();

		this.findById('EPLStomEF_EvnVizitPLStomGrid').getStore().removeAll();
		this.findById('EPLStomEF_EvnVizitPLStomGrid').getTopToolbar().items.items[0].enable();
		this.findById('EPLStomEF_EvnVizitPLStomGrid').getTopToolbar().items.items[1].disable();
		this.findById('EPLStomEF_EvnVizitPLStomGrid').getTopToolbar().items.items[2].disable();
		this.findById('EPLStomEF_EvnVizitPLStomGrid').getTopToolbar().items.items[3].disable();

		this.findById('EPLStomEF_EvnDiagPLStomGrid').getStore().removeAll();
		this.findById('EPLStomEF_EvnDiagPLStomGrid').getTopToolbar().items.items[0].disable();

		this.findById('EPLStomEF_EvnUslugaGrid').getStore().removeAll();
		this.findById('EPLStomEF_EvnUslugaGrid').getTopToolbar().items.items[0].enable();
		this.findById('EPLStomEF_EvnUslugaGrid').getTopToolbar().items.items[1].disable();
		this.findById('EPLStomEF_EvnUslugaGrid').getTopToolbar().items.items[2].disable();
		this.findById('EPLStomEF_EvnUslugaGrid').getTopToolbar().items.items[3].disable();

		this.findById('EPLStomEF_EvnStickGrid').getStore().removeAll();
		this.findById('EPLStomEF_EvnStickGrid').getTopToolbar().items.items[0].enable();
		this.findById('EPLStomEF_EvnStickGrid').getTopToolbar().items.items[1].enable();
		this.findById('EPLStomEF_EvnStickGrid').getTopToolbar().items.items[2].disable();
		this.findById('EPLStomEF_EvnStickGrid').getTopToolbar().items.items[3].disable();
		this.findById('EPLStomEF_EvnStickGrid').getTopToolbar().items.items[4].disable();
		this.findById('EPLStomEF_EvnStickGrid').getTopToolbar().items.items[5].disable();


		if ( getRegionNick() == 'ufa' ) {
			this.findById('EPLStomEF_EvnUslugaGrid').getTopToolbar().items.items[0].disable();
		}

		base_form.findField('EvnPLStom_setDate').disable();

		setLpuSectionGlobalStoreFilter();

		lpu_section_dir_combo.getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));

		var isKareliya = (getRegionNick() == 'kareliya');

		switch ( this.action ) {
			case 'add':
				this.setTitle(this.baseTitle + ': ' + FRM_ACTION_ADD);
				this.enableEdit(true);
				this.fo = false;

				base_form.findField('EvnPLStom_setDate').enable();

				if ( !Ext.isEmpty(this.params.EvnVizitPLStom_setDate) ) {
					base_form.findField('EvnPLStom_setDate').setValue(this.params.EvnVizitPLStom_setDate);
				}

				setCurrentDateTime({
					dateField: base_form.findField('EvnPLStom_setDate'),
					loadMask: false,
					setDate: Ext.isEmpty(this.params.EvnVizitPLStom_setDate),
					setDateMaxValue: true,
					setDateMinValue: false,
					setTime: false,
					windowId: this.id,
					callback: function() {
						win.checkLpuHasConsPriemVolume();
					}
				});

				this.setFormMode();

				LoadEmptyRow(this.findById('EPLStomEF_EvnDiagPLStomGrid'));
				LoadEmptyRow(this.findById('EPLStomEF_EvnDrugGrid'));
				LoadEmptyRow(this.findById('EPLStomEF_EvnStickGrid'));
				LoadEmptyRow(this.findById('EPLStomEF_EvnUslugaGrid'));
				LoadEmptyRow(this.findById('EPLStomEF_EvnVizitPLStomGrid'));

				var direct_class_id = direct_class_combo.getValue();
				var is_finish = is_finish_combo.getValue();

				if ( is_finish == null || is_finish.toString().length == 0 ) {
					is_finish = 1;
				}

				this.findById('EPLStomEF_PersonInformationFrame').setTitle('...');
				this.findById('EPLStomEF_PersonInformationFrame').clearPersonChangeParams();

				this.findById('EPLStomEF_PersonInformationFrame').load({
					callback: function() {
						this.findById('EPLStomEF_PersonInformationFrame').setPersonTitle();
						win.setMKB();
					}.createDelegate(this),
					Person_id: person_id,
					Server_id: this.Server_id,
					PersonEvn_id: this.PersonEvn_id
				});

				direct_class_combo.setValue(direct_class_id);
				is_finish_combo.setValue(is_finish);
				is_unport_combo.setValue(1);

				direct_class_combo.fireEvent('change', direct_class_combo, direct_class_id, direct_class_id + 1);
				is_finish_combo.fireEvent('change', is_finish_combo, is_finish, -1);
				prehosp_trauma_combo.fireEvent('change', prehosp_trauma_combo, null, 1);

				if ( isKareliya ) {
					if ( this.params.MedicalCareKind_id ) {
						medical_care_kind_combo.setValue(this.params.MedicalCareKind_id);
					}
					else {
						medical_care_kind_combo.setFieldValue('MedicalCareKind_Code', 9);
					}
				}

				win.loadMask.hide();

				this.getEvnPLStomNumber();
				this.checkTrauma();

				this.panelEvnDirectionAll.isReadOnly = false;
				this.panelEvnDirectionAll.onLoadForm(this);

				base_form.findField('EvnPLStom_setDate').focus(false, 250);
			break;

			case 'edit':
			case 'view':
				base_form.load({
					failure: function() {
						win.loadMask.hide();
						sw.swMsg.alert('Ошибка', 'Ошибка при загрузке данных формы', function() { this.hide(); }.createDelegate(this) );
					}.createDelegate(this),
					params: {
						EvnPLStom_id: evn_pl_stom_id,
						archiveRecord: win.archiveRecord,
						delDocsView: win.delDocsView ? 1 : 0
					},
					success: function(f, act) {
						// В зависимости от accessType переопределяем this.action
						if ( base_form.findField('accessType').getValue() == 'view' ) {
							this.action = 'view';
						}

						if ( base_form.findField('canCreateVizit').getValue() == "true") {
							this.canCreateVizit = true;
						} else {
							this.canCreateVizit = false;
						}

						this.setFormMode();
						this.checkForCostPrintPanel();
						this.checkLpuHasConsPriemVolume();

						if (getRegionNick() == 'vologda') {
							if (this.action == 'view' && this.gridAccess == 'full') {
								setTimeout(function(){
									win.findById('EPLStomEF_EvnVizitPLStomGrid').getTopToolbar().items.items[0].enable();
								}, 50);
							}
						}

						if ( this.action == 'view' && !this.canCreateVizit) {
							this.setTitle(this.baseTitle + ': ' + FRM_ACTION_VIEW);
							this.enableEdit(false);

							this.findById('EPLStomEF_PersonInformationFrame').clearPersonChangeParams();
						}
						else {
							this.setTitle(this.baseTitle + ': ' + FRM_ACTION_EDIT);
							this.enableEdit(true);

							this.findById('EPLStomEF_PersonInformationFrame').setPersonChangeParams({
								callback: function(data) {
									this.hide();
								}.createDelegate(this)
								,Evn_id: evn_pl_stom_id
							});
						}

						var xdate = sw.Promed.EvnPL.getEvnPLStomNewBegDate();
						var EvnPLStom_setDate = base_form.findField('EvnPLStom_setDate').getValue();
						if ( getRegionNick() == 'perm' && base_form.findField('EvnPLStom_IsPaid').getValue() == 2 && parseInt(base_form.findField('EvnPLStom_IndexRepInReg').getValue()) > 0 && !Ext.isEmpty(EvnPLStom_setDate) && EvnPLStom_setDate >= xdate ) {
							base_form.findField('EvnPLStom_RepFlag').showContainer();

							if ( parseInt(base_form.findField('EvnPLStom_IndexRep').getValue()) >= parseInt(base_form.findField('EvnPLStom_IndexRepInReg').getValue()) ) {
								base_form.findField('EvnPLStom_RepFlag').setValue(true);
							}
							else {
								base_form.findField('EvnPLStom_RepFlag').setValue(false);
							}
						}

						this.findById('EPLStomEF_PersonInformationFrame').setTitle('...');
						this.findById('EPLStomEF_PersonInformationFrame').load({
							callback: function() {
								this.findById('EPLStomEF_PersonInformationFrame').setPersonTitle();
								win.setMKB();
							}.createDelegate(this),
							onExpand: true,
							Person_id: base_form.findField('Person_id').getValue(),
							Server_id: (this.Server_id ? this.Server_id : base_form.findField('Server_id').getValue()),
							PersonEvn_id: (this.PersonEvn_id ? this.PersonEvn_id : base_form.findField('PersonEvn_id').getValue())
						});

						this.findById('EPLStomEF_EvnVizitPLStomGrid').getStore().load({
							callback: function() {
								win.checkTrauma();
							},
							params: {
								EvnVizitPLStom_pid: evn_pl_stom_id
							}
						});

						if ( this.formMode == 'morbus' ) {
							this.findById('EPLStomEF_EvnDiagPLStomPanel').fireEvent('expand', this.findById('EPLStomEF_EvnDiagPLStomPanel'));
						}

						var direct_class_id = direct_class_combo.getValue();
						var evnpl_stom_isfinish = is_finish_combo.getValue();
						var evnpl_stom_isunlaw = is_unlaw_combo.getValue();
						var lpu_oid = org_dir_combo.getValue();
						var lpu_section_oid = lpu_section_dir_combo.getValue();
						var prehosp_trauma_id = prehosp_trauma_combo.getValue();
						var record;
						var result_class_id = result_class_combo.getValue();
						var diag_preid = base_form.findField('Diag_preid').getValue();
						var diag_concid = base_form.findField('Diag_concid').getValue();
						var diag_fid = base_form.findField('Diag_fid').getValue();
						var diag_lid = act.result.data.Diag_lid;

						if ( this.action == 'edit' ) {
							prehosp_trauma_combo.fireEvent('change', prehosp_trauma_combo, prehosp_trauma_id, -1);
							is_unlaw_combo.setValue(evnpl_stom_isunlaw);
							direct_class_combo.fireEvent('change', direct_class_combo, direct_class_id, direct_class_id + 1);
							is_finish_combo.fireEvent('change', is_finish_combo, evnpl_stom_isfinish, -1);
							result_class_combo.setValue(result_class_id);
							if (evnpl_stom_isfinish == 2) {
								this.findById('EPLStomEF_EvnVizitPLStomGrid').getTopToolbar().items.items[0].disable();
							}
						}
						else {
							if (this.canCreateVizit) {
								this.findById('EPLStomEF_EvnVizitPLStomGrid').getTopToolbar().items.items[0].enable();
								this.findById('EPLStomEF_EvnUslugaGrid').getTopToolbar().items.items[0].enable();
								this.findById('EPLStomEF_EvnStickGrid').getTopToolbar().items.items[0].enable();
								this.findById('EPLStomEF_EvnStickGrid').getTopToolbar().items.items[1].enable();
							} else {
								this.findById('EPLStomEF_EvnVizitPLStomGrid').getTopToolbar().items.items[0].disable();
								this.findById('EPLStomEF_EvnUslugaGrid').getTopToolbar().items.items[0].disable();
								this.findById('EPLStomEF_EvnStickGrid').getTopToolbar().items.items[0].disable();
								this.findById('EPLStomEF_EvnStickGrid').getTopToolbar().items.items[1].disable();
							}
							base_form.findField('EvnPLStom_NumCard').disable();
							base_form.findField('Diag_fid').disable();
						}

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

						if ( diag_preid != null && diag_preid.toString().length > 0 ) {
							base_form.findField('Diag_preid').getStore().load({
								callback: function() {
									base_form.findField('Diag_preid').getStore().each(function(record) {
										if ( record.get('Diag_id') == diag_preid ) {
											base_form.findField('Diag_preid').fireEvent('select', base_form.findField('Diag_preid'), record, 0);
										}
									});
								},
								params: {where: "where DiagLevel_id = 4 and Diag_id = " + diag_preid}
							});
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

						if (!Ext.isEmpty(diag_fid)) {
							base_form.findField('Diag_fid').getStore().load({
								callback: function() {
									base_form.findField('Diag_fid').setValue(diag_fid);
								},
								params: {where: "where DiagLevel_id = 4 and Diag_id = " + diag_fid}
							});
						}

						if (!Ext.isEmpty(diag_lid)) {
							base_form.findField('Diag_lid').getStore().load({
								callback: function() {
									base_form.findField('Diag_lid').setValue(diag_lid);
								},
								params: {where: "where DiagLevel_id = 4 and Diag_id = " + diag_lid}
							});
						}

						win.loadMask.hide();

						this.panelEvnDirectionAll.isReadOnly = (this.action == 'view');
						this.panelEvnDirectionAll.onLoadForm(this);

						if ( !base_form.findField('EvnPLStom_setDate').disabled ) {
							base_form.findField('EvnPLStom_setDate').focus(false, 250);
						}
						else {
							base_form.findField('EvnPLStom_NumCard').focus(false, 250);
						}

						this.fo = false;
					}.createDelegate(this),
					url: '/?c=EvnPLStom&m=loadEvnPLStomEditForm'
				});
			break;

			default:
				win.loadMask.hide();
				this.hide();
			break;
		}
	},
	width: 800
});