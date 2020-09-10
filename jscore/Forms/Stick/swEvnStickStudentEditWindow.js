/**
* swEvnStickStudentEditWindow - окно редактирования/добавления справки учащегося.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Stick
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Stas Bykov aka Savage (savage@swan.perm.ru)
* @version      02.02.2011
* @comment      Префикс для id компонентов EStSEF (EvnStickStudentEditForm)
*
*
* @input data: action - действие (add, edit, view)
*/
/*NO PARSE JSON*/

sw.Promed.swEvnStickStudentEditWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swEvnStickStudentEditWindow',
	objectSrc: '/jscore/Forms/Stick/swEvnStickStudentEditWindow.js',

	action: null,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	collapsible: false,
	onFizraRelease: function(combo, newVal, oldVal) {
		var win = this,
			base_form = win.FormPanel.getForm(),
			disable = !newVal || win.action=='view' || getRegionNick()!='perm',
			grid = this.findById('EStSEF_EvnStickWorkReleaseGrid');
		base_form.findField('EvnStickStudent_begDT').setDisabled(disable);
		base_form.findField('EvnStickStudent_Days').setDisabled(disable);
		base_form.findField('Okei_id').setDisabled(disable);
		if(!newVal) {//при снятом флаге нужно затирать значения в БД
			base_form.findField('EvnStickStudent_begDT').reset();
			base_form.findField('EvnStickStudent_Days').reset();
			base_form.findField('Okei_id').reset();
		} else if(getRegionNick()!='perm') {
			//в дату начала нужно поставить день после последнего в таблице освобождений
			var cnt = grid.getStore().getCount();
			if(cnt>0) {
				var dt = grid.getStore().getAt(cnt-1);
				if(!Ext.isEmpty(dt.get('EvnStickWorkRelease_endDate'))) {
					dt = dt.get('EvnStickWorkRelease_endDate').addDays(1);
					base_form.findField('EvnStickStudent_begDT').setValue(dt);
				}
			}
		}
	},
	deleteEvnStickWorkRelease: function() {
		if ( this.action == 'view' ) {
			return false;
		}

		var base_form = this.FormPanel.getForm();
		var grid = this.findById('EStSEF_EvnStickWorkReleaseGrid');

		if ( !grid ) {
			sw.swMsg.alert(lang['oshibka'], lang['pri_udalenii_osvobojdeniya_ot_zanyatiy_posescheniy_voznikli_oshibki_[tip_oshibki_1]']);
			return false;
		}
		else if ( !grid.getSelectionModel().getSelected() ) {
			sw.swMsg.alert(lang['oshibka'], lang['ne_vyibrano_osvobojdenie_ot_zanyatiy_posescheniy_iz_spiska']);
			return false;
		}

		var selected_record = grid.getSelectionModel().getSelected();

		if ( selected_record.get('accessType') == 'view' ) {
			return false;
		}

		var id = selected_record.get('EvnStickWorkRelease_id');

		if ( id == null ) {
			return false;
		}

		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId, text, obj) {
				if ( 'yes' == buttonId ) {
					Ext.Ajax.request({
						failure: function(response, options) {
							sw.swMsg.alert(lang['oshibka'], lang['pri_udalenii_osvobojdeniya_ot_zanyatiy_posescheniy_voznikli_oshibki_[tip_oshibki_2]']);
						},
						params: {
							EvnStickWorkRelease_id: id
						},
						success: function(response, options) {
							var response_obj = Ext.util.JSON.decode(response.responseText);

							if ( response_obj.success == false ) {
								sw.swMsg.alert(lang['oshibka'], response_obj.Error_Msg ? response_obj.Error_Msg : lang['pri_udalenii_osvobojdeniya_ot_zanyatiy_posescheniy_voznikli_oshibki_[tip_oshibki_3]']);
							}
							else {
								grid.getStore().remove(selected_record);

								if ( grid.getStore().getCount() == 0 ) {
									LoadEmptyRow(grid);
								}
							}

							grid.getView().focusRow(0);
							grid.getSelectionModel().selectFirstRow();
						},
						url: '/?c=Stick&m=deleteEvnStickWorkRelease'
					});
				}
			},
			icon: Ext.MessageBox.QUESTION,
			msg: lang['udalit_osvobojdenie_ot_zanyatiy_posescheniy'],
			title: lang['vopros']
		});
	},
	doSave: function(options) {
		// options @Object
		// options.ignoreWorkReleaseCount @Boolean Игнорировать проверку количества введенных освобождений от работы
		// options.openChildWindow @Function Открыть дочернее окно после сохранения
		// options.print @Boolean Вызывать печать ЛВН, если true

		var me = this;
		if ( this.formStatus == 'save' || this.action == 'view' ) {
			return false;
		}

		if ( typeof options != 'object' ) {
			options = new Object();
		}

		this.formStatus = 'save';

		var base_form = this.FormPanel.getForm();
		var form = this.FormPanel;
		var grid = this.findById('EStSEF_EvnStickWorkReleaseGrid');

		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					this.formStatus = 'edit';
					form.getFirstInvalidEl().focus(true);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		// Проверка на заполнение хотя бы одной записи в таблице "Освобождение от занятий/посещений"
		if ( !options.ignoreWorkReleaseCount && (grid.getStore().getCount() == 0 || !grid.getStore().getAt(0).get('EvnStickWorkRelease_id')) ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					this.formStatus = 'edit';

					if ( this.findById('EStSEF_EvnStickWorkReleasePanel').collapsed ) {
						this.findById('EStSEF_EvnStickWorkReleasePanel').expand();
					}

					grid.getView().focusRow(0);
					grid.getSelectionModel().selectFirstRow();
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: lang['doljno_byit_zapolneno_hotya_byi_odno_osvobojdenie_ot_zanyatiy_posescheniy'],
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT_SAVE });
		loadMask.show();

		var params = new Object();

		if ( base_form.findField('EvnStick_Num').disabled ) {
			params.EvnStick_Num = base_form.findField('EvnStick_Num').getValue();
		}

		base_form.findField('MedPersonal_id').setValue(base_form.findField('MedStaffFact_id').getFieldValue('MedPersonal_id'));

		if ( base_form.findField('MedStaffFact_id').disabled ) {
			params.MedStaffFact_id = base_form.findField('MedStaffFact_id').getValue();
		}


		var Org_idPersonInfo = this.PersonInfo.getFieldValue('JobOrg_id'),	//получаем значение поля JobOrg_id из панели данных о человеке PersonInfo
			doUpdateJobInfo = Ext.isEmpty(Org_idPersonInfo);				//doUpdateJobInfo - флаг установливаем, если в нашей панели данных о человеке нет данных о его месте учебы

		//объявление функции сохранения данных формы
		var saveFn = function (doUpdateJobInfo) {
			//если флаг установлен, добавляем его в параметры, которые будут отправлены на сервер
			//тогда сервер должен будет сохранить место учебы из формы "Справка учащегося" в поле "Место учебы/работы" формы "Человек"
			if (doUpdateJobInfo) {
				params.doUpdateJobInfo = 1;
			}

		base_form.submit({
			failure: function(result_form, action) {
				this.formStatus = 'edit';
				loadMask.hide();

				if ( action.result ) {
					if ( action.result.Error_Msg ) {
						sw.swMsg.alert(lang['oshibka'], action.result.Error_Msg);
					}
					else {
						sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_1]']);
					}
				}
				}.createDelegate(me),
			params: params,
			success: function(result_form, action) {
				this.formStatus = 'edit';
				loadMask.hide();

				if ( action.result ) {
					var evn_stick_id = action.result.EvnStick_id;

					base_form.findField('EvnStick_id').setValue(evn_stick_id);
						if (options && typeof options.openChildWindow == 'function' && me.action == 'add') {
						options.openChildWindow();
					}
					else {
						var data = new Object();
						var evn_stick_beg_date;
						var evn_stick_end_date;
							//var grid = this.findById('EStSEF_EvnStickWorkReleaseGrid');

						grid.getStore().each(function(rec) {
							if ( typeof rec.get('EvnStickWorkRelease_begDate') == 'object' && (!evn_stick_beg_date || evn_stick_beg_date > rec.get('EvnStickWorkRelease_begDate')) ) {
								evn_stick_beg_date = rec.get('EvnStickWorkRelease_begDate');
							}

							if ( typeof rec.get('EvnStickWorkRelease_endDate') == 'object' && (!evn_stick_end_date || evn_stick_end_date < rec.get('EvnStickWorkRelease_endDate')) ) {
								evn_stick_end_date = rec.get('EvnStickWorkRelease_endDate');
							}
						});

						data.evnStickData = [{
							'accessType': 'edit',
							'evnStickType': 3,
							'EvnStick_disDate': evn_stick_end_date,
							'EvnStick_id': evn_stick_id,
							'EvnStick_mid': base_form.findField('EvnStick_pid').getValue(),
							'EvnStick_Num': base_form.findField('EvnStick_Num').getValue(),
							'EvnStick_ParentNum': '',
							'EvnStick_ParentTypeName': lang['tekuschiy'],
							'EvnStick_pid': base_form.findField('EvnStick_pid').getValue(),
							'EvnStick_Ser': '',
							'EvnStick_setDate': base_form.findField('EvnStick_setDate').getValue(),
							'EvnStickWorkRelease_begDate': evn_stick_beg_date,
							'EvnStickWorkRelease_endDate': evn_stick_end_date,
							'parentClass': this.parentClass,
							'Person_id': base_form.findField('Person_id').getValue(),
							'PersonEvn_id': base_form.findField('PersonEvn_id').getValue(),
							'Server_id': base_form.findField('Server_id').getValue(),
							'StickOrder_Name': '',
							'StickType_Name': langs('Справка учащегося'),
							'StickWorkType_Name': '',
							'EvnStickStudent_begDT': base_form.findField('EvnStickStudent_begDT'),
							'EvnStickStudent_Days': base_form.findField('EvnStickStudent_Days'),
							'Okei_id': base_form.findField('Okei_id')
						}];
							me.callback(data);

						if ( options && options.print ) {
                            if((getRegionNick()=='kz'))
                                printBirt({
                                    'Report_FileName': 'f095u.rptdesign',
                                    'Report_Params': '&paramEvnStickStudent=' + evn_stick_id,
                                    'Report_Format': 'pdf'
                                });
								else {
							    var url = '/?c=Stick&m=printEvnStickStudent&EvnStickStudent_id=' + evn_stick_id;
							    window.open(url, '_blank');
                            }
							this.buttons[1].focus();
						}
						else {
								me.hide();
						}
					}
				}
				else {
					sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_2]']);
				}
				}.createDelegate(me)
		});
		};

		var Org_idStudentCombo = this.FormPanel.getForm().findField('Org_id'),
			Org_idStudent = Org_idStudentCombo.getValue();

		//если требуется сохранение места учебы из формы "Спр. уч-ся" в форму "Человек", выполняем первую ветку условия, иначе просто сохраняем данные во второй ветке
		if (doUpdateJobInfo && !Ext.isEmpty(Org_idStudent) && !(getRegionNick() == 'kz') && Ext.isEmpty(options.ignoreWorkReleaseCount)) { //параметр options.ignoreWorkReleaseCount используем, чтобы не открывалось дважды предложение сохранить данные о месте учебы и работы
			//сохранение места учебы при отсутствии места учебы в форме "Человек"
			//перед сохранением выводим окошко с вопросом о подтверждении сохранения данных о месте работы учебы
			sw.swMsg.show({
				buttons: Ext.Msg.YESNO,
				fn: function (buttonId, text, obj) {
					me.formStatus = 'edit';

					if (buttonId == 'yes') {
						saveFn(true);
					}
					if (buttonId == 'no') {
						loadMask.hide();
					}
	},
				icon: Ext.MessageBox.QUESTION,
				msg: langs(' Вы указали новое место учебы пациента. Обновить данные формы «Человек»?'),
				title: langs(' Продолжить сохранение?')
			});
		} else { //сохранение места учебы не требуется, т.к. оно уазано в форме "Человек"
			saveFn();
		}
	},
	draggable: true,
	enableEdit: function(enable) {
		var base_form = this.FormPanel.getForm();
		var form_fields = new Array(
			'EvnStick_ContactDescr',
			'EvnStick_IsContact',
			'EvnStick_Num',
			'EvnStick_setDate',
			'MedStaffFact_id',
			'Org_id',
			'StickCause_id',
			'StickRecipient_id',
			'isFizraRelease',
			'EvnStickStudent_begDT',
			'EvnStickStudent_Days',
			'Okei_id'
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
	filterStickCause: function() {
		var wnd  = this;
		var base_form = wnd.FormPanel.getForm();
		var stick_cause_combo = base_form.findField('StickCause_id');

		var set_date = base_form.findField('EvnStick_setDate').getValue();
		if (Ext.isEmpty(set_date)) {
			set_date = new Date(new Date().format('Y-m-d'));
		}

		stick_cause_combo.getStore().clearFilter();
		stick_cause_combo.lastQuery = '';

		stick_cause_combo.getStore().filterBy(function(rec){
			var flag = true;

			if (
				(!Ext.isEmpty(rec.get('StickCause_begDate')) && rec.get('StickCause_begDate') > set_date) ||
				(!Ext.isEmpty(rec.get('StickCause_endDate')) && rec.get('StickCause_endDate') < set_date)
			) {
				flag = false;
			}

			return flag;
		});
	},
	formStatus: 'edit',
	getLoadMask: function() {
		if ( !this.loadMask ) {
			this.loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT });
		}

		return this.loadMask;
	},
	getEvnStickStudentNumber: function() {
		var num_field = this.FormPanel.getForm().findField('EvnStick_Num');

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Получение номера справки учащегося..." });
		loadMask.show();

		Ext.Ajax.request({
			callback: function(options, success, response) {
				loadMask.hide();

				if ( success ) {
					var response_obj = Ext.util.JSON.decode(response.responseText);

					num_field.setValue(response_obj.EvnStickStudent_Num);
					num_field.focus(true);
				}
				else {
					sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_opredelenii_nomera_spravki_uchaschegosya']);
				}
			},
			url: '/?c=Stick&m=getEvnStickStudentNumber'
		});
	},
	height: 550,
	id: 'EvnStickStudentEditWindow',
	initComponent: function() {
		var win = this;
		this.FormPanel = new Ext.form.FormPanel({
			autoScroll: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			border: false,
			frame: false,
			id: 'EvnStickStudentEditForm',
			labelAlign: 'right',
			labelWidth: 200,
			reader: new Ext.data.JsonReader({
				success: Ext.emptyFn
			},  [
				{ name: 'accessType' },
				{ name: 'isParentOwner' },
				{ name: 'EvnStick_ContactDescr' },
				{ name: 'EvnStick_id' },
				{ name: 'EvnStick_IsContact' },
				{ name: 'EvnStick_Num' },
				{ name: 'EvnStick_pid' },
				{ name: 'EvnStick_setDate' },
				{ name: 'MedStaffFact_id' },
				{ name: 'MedPersonal_id' },
				{ name: 'Org_id' },
				{ name: 'Person_id' },
				{ name: 'PersonEvn_id' },
				{ name: 'Server_id' },
				{ name: 'StickCause_id' },
				{ name: 'StickRecipient_id' },
				{ name: 'EvnStickStudent_begDT' },
				{ name: 'EvnStickStudent_Days' },
				{ name: 'Okei_id' }
			]),
			region: 'center',
			url: '/?c=Stick&m=saveEvnStickStudent',

			items: [{
				name: 'accessType',
				value: '',
				xtype: 'hidden'
			}, {
				name: 'isParentOwner',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'EvnStick_id', // Идентификатор справки учащегося
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'EvnStick_mid', // Идентификатор учетного документа (ТАП, КВС)
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'EvnStick_pid', // Идентификатор родительского события для справки учащегося
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'MedPersonal_id',
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
				value: 0,
				xtype: 'hidden'
			}, {
				allowBlank: false,
				disabled: false,
				fieldLabel: lang['nomer'],
				name: 'EvnStick_Num',
				maskRe: /\d/,
				tabIndex: TABINDEX_ESTSEF + 1,
				width: 100,
				xtype: 'textfield'
			}, {
				allowBlank: false,
				fieldLabel: lang['data_vyidachi'],
				format: 'd.m.Y',
				listeners: {
					'change': function(field, newValue, oldValue) {
						this.filterStickCause();

						if ( blockedDateAfterPersonDeath('personpanelid', 'EStSEF_PersonInformationFrame', field, newValue, oldValue) ) {
							return;
						}

						var base_form = this.FormPanel.getForm();

						var MedStaffFact_id = base_form.findField('MedStaffFact_id').getValue();
						var isParentOwner = base_form.findField('isParentOwner').getValue();

						base_form.findField('MedStaffFact_id').clearValue();

						if ( !newValue ) {
							base_form.findField('MedStaffFact_id').disable();
							return false;
						}

						var med_staff_fact_filter_params = {
							// Пока закрыл, согласно (http://redmine.swan.perm.ru/issues/3592)
							// TO-DO: надо сделать передачу полка это или стац, с родительской формы, и в зависимости от этого фильтровать
							//isPolka: true, 
							onDate: Ext.util.Format.date(newValue, 'd.m.Y'),
							regionCode: getGlobalOptions().region.number
						};

						if ( this.action != 'view' ) {
							base_form.findField('MedStaffFact_id').enable();
						}

						base_form.findField('MedStaffFact_id').getStore().removeAll();

						if ( this.action == 'add' && this.userMedStaffFactId ) {
							med_staff_fact_filter_params.id = this.userMedStaffFactId;
						}

						setMedStaffFactGlobalStoreFilter(med_staff_fact_filter_params);

						base_form.findField('MedStaffFact_id').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));

						if (getRegionNick() != 'kz') {
						var index = base_form.findField('MedStaffFact_id').getStore().findBy(function(rec, id) {
							return (rec.get('MedStaffFact_id') == MedStaffFact_id);
						});

						if ( index >= 0 ) {
							base_form.findField('MedStaffFact_id').setValue(MedStaffFact_id);
						}
						else if ( base_form.findField('MedStaffFact_id').getStore().getCount() == 1 ) {
							base_form.findField('MedStaffFact_id').setValue(base_form.findField('MedStaffFact_id').getStore().getAt(0).get('MedStaffFact_id'));
						}

						if ( this.action == 'edit' && this.userMedStaffFactId && !isParentOwner ) {
							base_form.findField('MedStaffFact_id').disable();
						}
						}
					}.createDelegate(this)
				},
				name: 'EvnStick_setDate',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				selectOnFocus: true,
				tabIndex: TABINDEX_ESTSEF + 2,
				width: 100,
				xtype: 'swdatefield'
			}, {
				allowBlank: false,
				displayField: 'Org_Name',
				editable: false,
				enableKeyEvents: true,
				fieldLabel: lang['vyidana_dlya'],
				hiddenName: 'Org_id',
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
					},
					'select': function(combo, record, index){
						var OrgTypeInListeners = record.get('OrgType_id');
					},
					'change': function (combo, newOrg_id, oldOrg_id) {
						if (getRegionNick() != 'kz' && !Ext.isEmpty(newOrg_id)) {
							if (typeof newOrg_id == "object") {		//При вызове события из onTrigger1Click newOrg_id приходит в виде объекта, поэтому здесь получаем числовое значения newOrg_id
								newOrg_id = newOrg_id.value;
					}
							var store = combo.getStore(),			//получаем стор комбобокса
								recordIndex = store.find('Org_id', newOrg_id);		//ищем индекс записи в сторе, другой смысл: получаем индекс стора, за которым закреплена запись
								if (recordIndex >= 0) {
									var record = store.getAt(recordIndex),
										OrgType_id = record.get('OrgType_id'),		//извлекаем из записи значение поля OrgType_id
										base_form = win.FormPanel.getForm(),
										StickRecipientCombo = base_form.findField('StickRecipient_id'),	//получаем ссылку на комбобокс поля "Выдана для"
										StickRecipient_id;
									switch (OrgType_id) {
										case 7:								//дошкольник
											StickRecipient_id = 5;			//5 - идентификатор записи "5. Ребенок, посещающий ДДУ"
											break;
										case 8:								//школьник обычной школы
										case 20:							//из коррекционной школы
										case 21:							//из учебно-воспитательной школы
											StickRecipient_id = 4;			//4 - идентификатор записи "4. Учащийся школы"
											break;
										case 9:								//учащийся техникума(колледжа)
											StickRecipient_id = 2;			//2 - идентификатор записи "2. Учащийся колледжа"
											break;
										case 10:							//студент
											StickRecipient_id = 1;			//1 - идентификатор записи "1. Студент ВУЗа"
											break;
										default:

											break;
									}
								}
							if (!Ext.isEmpty(StickRecipient_id)) {
								StickRecipientCombo.setValue(StickRecipient_id);        //меняем запись в комбобоксе "Получатель справки"
							}
						}
					}
				},
				mode: 'local',
				onTrigger1Click: function() {
					var base_form = this.FormPanel.getForm();
					var combo = base_form.findField('Org_id');

					if ( combo.disabled ) {
						return false;
					}

					getWnd('swOrgSearchWindow').show({
						object: 'org',
						onClose: function() {
							combo.focus(true, 200)
						},
						onSelect: function(org_data) {
							if ( org_data.Org_id > 0 ) {
								combo.getStore().loadData([{
									Org_id: org_data.Org_id,
									Org_Name: org_data.Org_Name,
									OrgType_id: org_data.OrgType_id
								}]);
								combo.setValue(org_data.Org_id);
								getWnd('swOrgSearchWindow').hide();
								combo.collapse();
								combo.fireEvent('change', combo, org_data.Org_id);
							}
						}
					});
				}.createDelegate(this),
				store: new Ext.data.JsonStore({
					autoLoad: false,
					fields: [
						{ name: 'Org_id', type: 'int' },
						{ name: 'Org_Name', type: 'string' },
						{ name: 'OrgType_id', type: 'int' }
					],
					key: 'Org_id',
					sortInfo: {
						field: 'Org_Name'
					},
					url: C_ORG_LIST
				}),
				tabIndex: TABINDEX_ESTSEF + 3,
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
				allowBlank: false,
				comboSubject: 'StickRecipient',
				fieldLabel: lang['poluchatel_spravki'],
				hiddenName: 'StickRecipient_id',
				tabIndex: TABINDEX_ESTSEF + 4,
				width: 400,
				listeners: {
					'change': function(combo, newOrg_id, oldOrg_id){
						if (getRegionNick() != 'kz') {
							var base_form = win.FormPanel.getForm();
							var Org_idCombo = base_form.findField('Org_id');
							Org_idCombo.reset();
						}
					}
				},
				xtype: 'swcommonsprcombo'
			}, {
				allowBlank: false,
				allowSysNick: true,
				fieldLabel: lang['prichina_netrudosposobnosti'],
				hiddenName: 'StickCause_id',
				tabIndex: TABINDEX_ESTSEF + 5,
				width: 400,
				xtype: 'swstickcausecombo'
			},
			{
				allowBlank: false,
				comboSubject: 'YesNo',
				fieldLabel: lang['nalichie_kontakta_s_infektsionnyimi_bolnyimi'],
				hiddenName: 'EvnStick_IsContact',
				listeners: {
					'change': function(combo, newValue, oldValue) {
						var base_form = this.FormPanel.getForm();

						var record = combo.getStore().getById(newValue);

						if ( record && record.get('YesNo_Code') == 1 ) {
							base_form.findField('EvnStick_ContactDescr').enable();
						}
						else {
							base_form.findField('EvnStick_ContactDescr').disable();
							base_form.findField('EvnStick_ContactDescr').setRawValue('');
						}
					}.createDelegate(this)
				},
				value: 1,
				tabIndex: TABINDEX_ESTSEF + 6,
				width: 100,
				xtype: 'swcommonsprcombo'
			}, {
				allowBlank: true,
				fieldLabel: lang['opisanie_kontakta'],
				height: 100,
				name: 'EvnStick_ContactDescr',
				tabIndex: TABINDEX_ESTSEF + 7,
				width: 500,
				xtype: 'textarea'
			},
			new sw.Promed.Panel({
				border: true,
				collapsible: true,
				height: 175,
				id: 'EStSEF_EvnStickWorkReleasePanel',
				isLoaded: false,
				layout: 'border',
				listeners: {
					'expand': function(panel) {
						if ( panel.isLoaded === false ) {
							panel.isLoaded = true;
							panel.findById('EStSEF_EvnStickWorkReleaseGrid').getStore().load({
								params: {
									EvnStick_id: this.FormPanel.getForm().findField('EvnStick_id').getValue()
								}
							});
						}

						panel.doLayout();
					}.createDelegate(this)
				},
				style: 'margin-bottom: 0.5em;',
				title: lang['1_osvobojdenie_ot_zanyatiy_posescheniy'],
				items: [ new Ext.grid.GridPanel({
					autoExpandColumn: 'autoexpand_workrelease',
					autoExpandMin: 100,
					border: false,
					columns: [{
						dataIndex: 'EvnStickWorkRelease_begDate',
						header: lang['s_kakogo_chisla'],
						hidden: false,
						renderer: Ext.util.Format.dateRenderer('d.m.Y'),
						resizable: false,
						sortable: true,
						width: 100
					}, {
						dataIndex: 'EvnStickWorkRelease_endDate',
						header: lang['po_kakoe_chislo'],
						hidden: false,
						renderer: Ext.util.Format.dateRenderer('d.m.Y'),
						resizable: false,
						sortable: true,
						width: 100
					}, {
						dataIndex: 'MedPersonal_Fio',
						header: lang['vrach'],
						hidden: false,
						id: 'autoexpand_workrelease',
						resizable: true,
						sortable: true
					}],
					frame: false,
					id: 'EStSEF_EvnStickWorkReleaseGrid',
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

							var grid = this.findById('EStSEF_EvnStickWorkReleaseGrid');

							switch ( e.getKey() ) {
								case Ext.EventObject.DELETE:
									this.deleteEvnStickWorkRelease();
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

									this.openEvnStickWorkReleaseEditWindow(action);
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
										if ( !base_form.findField('MedStaffFact_id').disabled ) {
											base_form.findField('MedStaffFact_id').focus();
										}
										else if ( this.action != 'view' ) {
											this.buttons[0].focus();
										}
										else {
											this.buttons[1].focus();
										}
									}
									else {
										if ( !base_form.findField('EvnStick_ContactDescr').disabled ) {
											base_form.findField('EvnStick_ContactDescr').focus();
										}
										else if ( !base_form.findField('EvnStick_IsContact').disabled ) {
											base_form.findField('EvnStick_IsContact').focus();
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
					layout: 'fit',
					listeners: {
						'rowdblclick': function(grid, number, obj) {
							this.openEvnStickWorkReleaseEditWindow('edit');
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
								var toolbar = this.findById('EStSEF_EvnStickWorkReleaseGrid').getTopToolbar();

								if ( selected_record ) {
									access_type = selected_record.get('accessType');
									id = selected_record.get('EvnStickWorkRelease_id');
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
									LoadEmptyRow(this.findById('EStSEF_EvnStickWorkReleaseGrid'));
								}
							}.createDelegate(this)
						},
						reader: new Ext.data.JsonReader({
							id: 'EvnStickWorkRelease_id'
						}, [{
							mapping: 'accessType',
							name: 'accessType',
							type: 'string'
						}, {
							mapping: 'EvnStickWorkRelease_id',
							name: 'EvnStickWorkRelease_id',
							type: 'int'
						}, {
							mapping: 'EvnStickBase_id',
							name: 'EvnStickBase_id',
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
							mapping: 'MedPersonal2_id',
							name: 'MedPersonal2_id',
							type: 'int'
						}, {
							mapping: 'MedPersonal3_id',
							name: 'MedPersonal3_id',
							type: 'int'
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
							mapping: 'MedPersonal_Fio',
							name: 'MedPersonal_Fio',
							type: 'string'
						}]),
						url: '/?c=Stick&m=loadEvnStickStudentWorkReleaseGrid'
					}),
					tbar: new sw.Promed.Toolbar({
						buttons: [{
							handler: function() {
								this.openEvnStickWorkReleaseEditWindow('add');
							}.createDelegate(this),
							iconCls: 'add16',
							text: BTN_GRIDADD,
							tooltip: BTN_GRIDADD_TIP
						}, {
							handler: function() {
								this.openEvnStickWorkReleaseEditWindow('edit');
							}.createDelegate(this),
							iconCls: 'edit16',
							text: BTN_GRIDEDIT,
							tooltip: BTN_GRIDEDIT_TIP
						}, {
							handler: function() {
								this.openEvnStickWorkReleaseEditWindow('view');
							}.createDelegate(this),
							iconCls: 'view16',
							text: BTN_GRIDVIEW,
							tooltip: BTN_GRIDVIEW_TIP
						}, {
							handler: function() {
								this.deleteEvnStickWorkRelease();
							}.createDelegate(this),
							iconCls: 'delete16',
							text: BTN_GRIDDEL,
							tooltip: BTN_GRIDDEL_TIP
						}]
					})
				})]
			}), {
				allowBlank: false,
				fieldLabel: lang['vrach'],
				hiddenName: 'MedStaffFact_id',
				id: 'EStEF_MedStaffFactCombo',
				lastQuery: '',
				listWidth: 670,
				tabIndex: TABINDEX_ESTSEF + 8,
				width: 500,
				xtype: 'swmedstafffactglobalcombo'
			}, 
			new sw.Promed.Panel({
				bodyStyle: 'padding-top: 0.5em;',
				style: 'padding-top: 0.5em;',
				hidden: getRegionNick()!='perm',
				border: true,
				collapsible: true,
				height: 150,
				id: 'EStSEF_EvnStickWorkReleaseFizPanel',
				title: langs('2. Освобождение от занятий физкультуры'),
				layout: 'form',
				items: [
				{
					fieldLabel: langs('Освободить от занятий физкультуры'),
					labelWidth: 300,
					width: 400,
					labelSeparator: '',
					xtype: 'checkbox',
					name: 'isFizraRelease',
					checked: false,
					listeners: {
						change: function(a,b,c) {
							win.onFizraRelease(a,b,c);
						}
					}
				}, {
					fieldLabel: langs('Дата начала освобождения от физкультуры'),
					labelWidth: 300,
					xtype: 'swdatefield',
					name: 'EvnStickStudent_begDT',
					disabled: true,
					allowBlank: false
				}, {
					layout: 'column',
					border: false,
					items: [{
						layout: 'form',
						border: false,
						items: [{
							xtype: 'numberfield',
							fieldLabel: 'Длительность освобождения',
							name: 'EvnStickStudent_Days',//на самом деле не дни, а кол-во ед.измерения
							width: 105,
							disabled: true,
							allowBlank: false
						}]
					}, {
						layout: 'form',
						border: false,
						style: "padding-left: 10px",
						items: [{
							//Единицы измерения
							xtype: 'swokeicombo',
							name: 'Okei_id',
							hiddenName: 'Okei_id',
							hideLabel: true,
							displayField: 'Okei_Name',
							width: 80,
							tpl: new Ext.XTemplate(
									'<tpl for="."><div class="x-combo-list-item">',
									'{Okei_Name}',
									'</div></tpl>'
								),
							loadParams: {params: {where: ' where Okei_id in (101, 102, 104, 107)'}},
							disabled: true,
							allowBlank: false
						}]
					}]
				}]
			})]
		});

		this.PersonInfo = new sw.Promed.PersonInformationPanelShort({
			id: 'EStSEF_PersonInformationFrame',
			region: 'north'
		});

		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.doSave();
				}.createDelegate(this),
				iconCls: 'save16',
				onShiftTabAction: function () {
					var base_form = this.FormPanel.getForm();

					if ( !base_form.findField('MedStaffFact_id').disabled ) {
						base_form.findField('MedStaffFact_id').focus(true);
					}
					else if ( !this.findById('EStSEF_EvnStickWorkReleasePanel').collapsed && this.findById('EStSEF_EvnStickWorkReleaseGrid').getStore().getCount() > 0 ) {
						this.findById('EStSEF_EvnStickWorkReleaseGrid').getView().focusRow(0);
						this.findById('EStSEF_EvnStickWorkReleaseGrid').getSelectionModel().selectFirstRow();
					}
					else if ( !base_form.findField('EvnStick_ContactDescr').disabled ) {
						base_form.findField('EvnStick_ContactDescr').focus(true);
					}
					else if ( !base_form.findField('EvnStick_IsContact').disabled ) {
						base_form.findField('EvnStick_IsContact').focus(true);
					}
					else {
						this.buttons[this.buttons.length - 1].focus();
					}
				}.createDelegate(this),
				onTabAction: function () {
					this.buttons[1].focus();
				}.createDelegate(this),
				tabIndex: TABINDEX_ESTSEF + 9,
				text: BTN_FRMSAVE
			}, {
				handler: function() {
					this.printEvnStickStudent();
				}.createDelegate(this),
				iconCls: 'print16',
				onShiftTabAction: function () {
					var base_form = this.FormPanel.getForm();

					if ( this.action != 'view' ) {
						this.buttons[0].focus();
					}
					else if ( !this.findById('EStSEF_EvnStickWorkReleasePanel').collapsed && this.findById('EStSEF_EvnStickWorkReleaseGrid').getStore().getCount() > 0 ) {
						this.findById('EStSEF_EvnStickWorkReleaseGrid').getView().focusRow(0);
						this.findById('EStSEF_EvnStickWorkReleaseGrid').getSelectionModel().selectFirstRow();
					}
					else {
						this.buttons[this.buttons.length - 1].focus();
					}
				}.createDelegate(this),
				onTabAction: function () {
					this.buttons[this.buttons.length - 1].focus();
				}.createDelegate(this),
				tabIndex: TABINDEX_ESTSEF + 10,
				text: BTN_FRMPRINT
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
					this.buttons[1].focus();
				}.createDelegate(this),
				onTabAction: function () {
					var base_form = this.FormPanel.getForm();

					if ( !base_form.findField('EvnStick_Num').disabled ) {
						base_form.findField('EvnStick_Num').focus(true);
					}
					else if ( !this.findById('EStSEF_EvnStickWorkReleasePanel').collapsed && this.findById('EStSEF_EvnStickWorkReleaseGrid').getStore().getCount() > 0 ) {
						this.findById('EStSEF_EvnStickWorkReleaseGrid').getView().focusRow(0);
						this.findById('EStSEF_EvnStickWorkReleaseGrid').getSelectionModel().selectFirstRow();
					}
					else if ( this.action != 'view' ) {
						this.buttons[0].focus();
					}
					else {
						this.buttons[1].focus();
					}
				}.createDelegate(this),
				tabIndex: TABINDEX_ESTSEF + 11,
				text: BTN_FRMCANCEL
			}],
			items: [
				this.PersonInfo,
				this.FormPanel
			],
			layout: 'border'
		});

		sw.Promed.swEvnStickStudentEditWindow.superclass.initComponent.apply(this, arguments);
	},
	keys: [{
		alt: true,
		fn: function(inp, e) {
			var current_window = Ext.getCmp('EvnStickStudentEditWindow');

			switch ( e.getKey() ) {
				case Ext.EventObject.C:
					current_window.doSave();
				break;

				case Ext.EventObject.G:
					current_window.printEvnStickStudent();
				break;

				case Ext.EventObject.J:
					current_window.hide();
				break;

				case Ext.EventObject.NUM_ONE:
				case Ext.EventObject.ONE:
					current_window.findById('EStSEF_EvnStickWorkReleasePanel').toggleCollapse();
				break;
			}
		},
		key: [
			Ext.EventObject.C,
			Ext.EventObject.G,
			Ext.EventObject.J,
			Ext.EventObject.NUM_ONE,
			Ext.EventObject.ONE
		],
		scope: this,
		stopEvent: false
	}],
	layout: 'border',
	listeners: {
		'beforehide': function(win) {
			 //win.onCancelAction();
		},
		'hide': function(win) {
			win.onHide();
		},
		'maximize': function(win) {
			win.findById('EStSEF_EvnStickWorkReleasePanel').doLayout();
		},
		'restore': function(win) {
			win.findById('EStSEF_EvnStickWorkReleasePanel').doLayout();
		}
	},
	loadMask: null,
	maximizable: true,
	maximized: false,
	minHeight: 550,
	minWidth: 750,
	modal: true,
	onCancelAction: function() {
		var base_form = this.FormPanel.getForm();
		var id = base_form.findField('EvnStick_id').getValue();
        var mid = base_form.findField('EvnStick_pid').getValue();
		if ( id > 0 && this.action == 'add') {
			// удалить справку учащегося
			// закрыть окно после успешного удаления
			var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Удаление справки учащегося..." });
			loadMask.show();

			Ext.Ajax.request({
				failure: function(response, options) {
					loadMask.hide();
					sw.swMsg.alert(lang['oshibka'], lang['pri_udalenii_spravki_uchaschegosya_voznikli_oshibki_[tip_oshibki_2]']);
					return false;
				},
				params: {
					//EvnStick_id: id,
                    //EvnStick_mid: mid,
                    //Evn_id: mid,
                    EvnStickStudent_mid: mid,
                    EvnStickStudent_id: id
				},
				success: function(response, options) {
					loadMask.hide();

					var response_obj = Ext.util.JSON.decode(response.responseText);

					if ( response_obj.success == false ) {
						sw.swMsg.alert(lang['oshibka'], response_obj.Error_Msg ? response_obj.Error_Msg : lang['pri_udalenii_spravki_uchaschegosya_voznikli_oshibki_[tip_oshibki_3]']);
						return false;
					}
				},
				url: '/?c=Stick&m=deleteEvnStickStudent'
			});
		}
	},
	onHide: Ext.emptyFn,
	openEvnStickWorkReleaseEditWindow: function(action) {
		if ( action != 'add' && action != 'edit' && action != 'view' ) {
			return false;
		}

		if ( getWnd('swEvnStickWorkReleaseEditWindow').isVisible() ) {
			sw.swMsg.alert(lang['oshibka'], lang['okno_redaktirovaniya_osvobojdeniya_ot_zanyatiy_posescheniy_uje_otkryito']);
			return false;
		}

		if ( this.action == 'view' ) {
			if ( action == 'add') {
				return false;
			}
			else if ( action == 'edit' ) {
				action = 'view';
			}
		}

		var base_form = this.FormPanel.getForm();
		var grid = this.findById('EStSEF_EvnStickWorkReleaseGrid');
		var maxDate = null;
		var params = new Object();
		params.StickReg = this.StickReg;
        params.CurLpuSection_id = this.CurLpuSection_id;
        params.CurLpuUnit_id = this.CurLpuUnit_id;
        params.CurLpuBuilding_id = this.CurLpuBuilding_id;
        params.IngoreMSFFilter = this.IngoreMSFFilter;

		if ( action == 'add' ) {
			if ( base_form.findField('EvnStick_id').getValue() == 0 ) {
				this.doSave({
					ignoreWorkReleaseCount: true,
					openChildWindow: function() {
						this.openEvnStickWorkReleaseEditWindow(action);
					}.createDelegate(this)
				});
				return false;
			}

			if ( grid.getStore().getCount() >= 2 ) {
				sw.swMsg.alert(lang['oshibka'], lang['razresheno_dobavlenie_tolko_2-h_zapisey_ob_osvobojdenii_ot_zanyatiy_posescheniy']);
				return false;
			}

			grid.getStore().each(function(record) {
				if ( record && (!maxDate || record.get('EvnStickWorkRelease_endDate') > maxDate) ) {
					maxDate = record.get('EvnStickWorkRelease_endDate');
				}
			});
		}

		params.action = action;
		params.callback = function(data) {
			if ( !data || !data.evnStickWorkReleaseData ) {
				return false;
			}

			// Обновить запись в grid
			var record = grid.getStore().getById(data.evnStickWorkReleaseData.EvnStickWorkRelease_id);

			if ( record ) {
				var grid_fields = new Array();

				grid.getStore().fields.eachKey(function(key, item) {
					grid_fields.push(key);
				});

				for ( i = 0; i < grid_fields.length; i++ ) {
					record.set(grid_fields[i], data.evnStickWorkReleaseData[grid_fields[i]]);
				}

				record.commit();
			}
			else {
				if ( grid.getStore().getCount() == 1 && !grid.getStore().getAt(0).get('EvnStickWorkRelease_id') ) {
					grid.getStore().removeAll();
				}

				grid.getStore().loadData([ data.evnStickWorkReleaseData ], true);
			}
		}
		params.evnStickType = 3;
		params.formMode = 'remote';
		params.formParams = new Object();
		params.hideEvnStickWorkReleaseIsDraft = true;
		params.maxDate = maxDate;
		params.onHide = function() {
			grid.getView().focusRow(0);
			grid.getSelectionModel().selectFirstRow();
		}.createDelegate(this);
		params.parentClass = this.parentClass;
		params.Person_id = this.PersonInfo.getFieldValue('Person_id');
		params.Person_Birthday = this.PersonInfo.getFieldValue('Person_Birthday');
		params.Person_Firname = this.PersonInfo.getFieldValue('Person_Firname');
		params.Person_Secname = this.PersonInfo.getFieldValue('Person_Secname');
		params.Person_Surname = this.PersonInfo.getFieldValue('Person_Surname');

		if ( this.userMedStaffFactId ) {
			params.UserMedStaffFact_id = this.userMedStaffFactId;
		}
		
		var recordsc = base_form.findField('StickCause_id').getStore().getById(base_form.findField('StickCause_id').getValue());
		if ( recordsc ) {
			params.StickCause_SysNick = recordsc.get('StickCause_SysNick');
		}

		if ( action == 'add' ) {
			params.formParams.EvnStickBase_id = base_form.findField('EvnStick_id').getValue();
			params.formParams.EvnStickWorkRelease_id = 0;
		}
		else {
			var selected_record = grid.getSelectionModel().getSelected();

			if ( !selected_record || !selected_record.get('EvnStickWorkRelease_id') ) {
				return false;
			}

			if ( selected_record.get('accessType') != 'edit' ) {
				params.action = 'view';
			}

			params.formParams = selected_record.data;
		}

		getWnd('swEvnStickWorkReleaseEditWindow').show(params);
	},
	parentClass: null,
	plain: true,
	printEvnStickStudent: function() {
		switch ( this.action ) {
			case 'add':
			case 'edit':
				this.doSave({
					print: true
				});
			break;

			case 'view':
                if((getRegionNick()=='kz'))
                    printBirt({
                        'Report_FileName': 'f095u.rptdesign',
                        'Report_Params': '&paramEvnStickStudent=' + this.FormPanel.getForm().findField('EvnStick_id').getValue(),
                        'Report_Format': 'pdf'
                    });
                else
                {
                    var url = '/?c=Stick&m=printEvnStickStudent&EvnStickStudent_id=' + this.FormPanel.getForm().findField('EvnStick_id').getValue();
				    window.open(url, '_blank');
                }
			break;
		}
	},
	resizable: true,
	show: function() {
		sw.Promed.swEvnStickStudentEditWindow.superclass.show.apply(this, arguments);

		var win = this;

		this.findById('EStSEF_EvnStickWorkReleasePanel').expand();

		this.restore();
		this.center();
		this.maximize();

		var base_form = this.FormPanel.getForm();
		base_form.reset();

		this.action = null;
		this.callback = Ext.emptyFn;
		this.formStatus = 'edit';
		this.onHide = Ext.emptyFn;
		this.parentClass = null;
		this.userMedStaffFactId = null;
        this.CurLpuSection_id = 0;
        this.CurLpuUnit_id = 0;
        this.CurLpuBuilding_id = 0;
        this.IngoreMSFFilter = 0;
		this.StickReg = 0;
		if ( !arguments[0] || !arguments[0].formParams ) {
			sw.swMsg.alert(lang['soobschenie'], lang['nevernyie_parametryi'], function() { this.hide(); }.createDelegate(this) );
			return false;
		}
		if(arguments[0].formParams.StickReg) {
			this.StickReg = arguments[0].formParams.StickReg;
		}
        if(arguments[0].formParams.CurLpuSection_id) {
            this.CurLpuSection_id = arguments[0].formParams.CurLpuSection_id;
        }
        if(arguments[0].formParams.CurLpuUnit_id) {
            this.CurLpuUnit_id = arguments[0].formParams.CurLpuUnit_id;
        }
        if(arguments[0].formParams.CurLpuBuilding_id) {
            this.CurLpuBuilding_id = arguments[0].formParams.CurLpuBuilding_id;
        }

        if(arguments[0].formParams.IngoreMSFFilter) {
            this.IngoreMSFFilter = arguments[0].formParams.IngoreMSFFilter;
        }

		base_form.setValues(arguments[0].formParams);

		if ( arguments[0].action && typeof arguments[0].action == 'string' ) {
			this.action = arguments[0].action;
		}

		if ( arguments[0].callback && typeof arguments[0].callback == 'function' ) {
			this.callback = arguments[0].callback;
		}

		if ( arguments[0].onHide && typeof arguments[0].onHide == 'function' ) {
			this.onHide = arguments[0].onHide;
		}

		if ( arguments[0].parentClass ) {
			this.parentClass = arguments[0].parentClass;
		}

		if ( arguments[0].UserMedStaffFact_id ) {
			this.userMedStaffFactId = arguments[0].UserMedStaffFact_id;
		}

		this.findById('EStSEF_EvnStickWorkReleasePanel').isLoaded = false;

		this.PersonInfo.load({
			Person_id: (arguments[0].Person_id ? arguments[0].Person_id : ''),
			Person_Birthday: (arguments[0].Person_Birthday ? arguments[0].Person_Birthday : ''),
			Person_Firname: (arguments[0].Person_Firname ? arguments[0].Person_Firname : ''),
			Person_Secname: (arguments[0].Person_Secname ? arguments[0].Person_Secname : ''),
			Person_Surname: (arguments[0].Person_Surname ? arguments[0].Person_Surname : ''),
			callback: function() {
				var field = base_form.findField('EvnStick_setDate');
				clearDateAfterPersonDeath('personpanelid', 'EStSEF_PersonInformationFrame', field);
				if (getRegionNick() != 'kz') {
					var OrgCombo = base_form.findField('Org_id'),				//получаем ссылку на комбобокс c лейблом "Выдано для"
						Org_id = win.PersonInfo.getFieldValue('JobOrg_id');		//получаем значение поля JobOrg_id из ответа сервера через стор PersonInfo
					if (!Ext.isEmpty(Org_id)) {
						OrgCombo.setValue(Org_id);
						var OrgStore = OrgCombo.getStore();		//получаем стор поля "Выдано для"
						OrgStore.load({							//получаем с сервера данные организации по ее идентификатору
							params: {
								Org_id: Org_id
							},
							callback: function () {
								OrgCombo.setValue(Org_id);		//вставляем в комбобокс идентификатор, который автоматически заменится наименованием организации, т.к. оно появилось в сторе этого комбобокса
								OrgCombo.fireEvent('change', OrgCombo, Org_id);
			}
		});
					}
				}
			}
		});
		
		this.findById('EStSEF_EvnStickWorkReleaseGrid').getStore().removeAll();
		this.findById('EStSEF_EvnStickWorkReleaseGrid').getTopToolbar().items.items[0].enable();
		this.findById('EStSEF_EvnStickWorkReleaseGrid').getTopToolbar().items.items[1].disable();
		this.findById('EStSEF_EvnStickWorkReleaseGrid').getTopToolbar().items.items[2].disable();
		this.findById('EStSEF_EvnStickWorkReleaseGrid').getTopToolbar().items.items[3].disable();

		base_form.findField('MedStaffFact_id').getStore().removeAll();

		this.filterStickCause();

		this.getLoadMask().show();

		switch ( this.action ) {
			case 'add':
				this.setTitle(WND_STICK_ESTSADD);
				this.enableEdit(true);

				LoadEmptyRow(this.findById('EStSEF_EvnStickWorkReleaseGrid'));

				this.findById('EStSEF_EvnStickWorkReleasePanel').isLoaded = true;

				if (getRegionNick() != 'kz') {
				var index = base_form.findField('StickCause_id').getStore().findBy(function(rec) {
					return (rec.get('StickCause_SysNick') == 'desease');
				});

				if ( index >= 0 ) {
					base_form.findField('StickCause_id').setValue(base_form.findField('StickCause_id').getStore().getAt(index).get('StickCause_id'));
				}
				}

				base_form.findField('EvnStick_IsContact').setValue(1);

				base_form.findField('EvnStick_IsContact').fireEvent('change', base_form.findField('EvnStick_IsContact'), 1);

				setCurrentDateTime({
					callback: function() {
						base_form.findField('EvnStick_setDate').fireEvent('change', base_form.findField('EvnStick_setDate'), base_form.findField('EvnStick_setDate').getValue());
					}.createDelegate(this),
					dateField: base_form.findField('EvnStick_setDate'),
					loadMask: false,
					setDate: true,
					setDateMaxValue: true,
					windowId: this.id
				});
				
				base_form.findField('isFizraRelease').fireEvent('change', base_form.findField('isFizraRelease'), false);

				this.getLoadMask().hide();

				// this.getEvnStickStudentNumber();

				//base_form.clearInvalid();

				base_form.findField('EvnStick_Num').focus(true, 250);
			break;

			case 'edit':
			case 'view':
				base_form.load({
					failure: function() {
						this.getLoadMask().hide();
						sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_zagruzke_dannyih_formyi'], function() { this.hide(); }.createDelegate(this) );
					}.createDelegate(this),
					params: {
						'EvnStickStudent_id': base_form.findField('EvnStick_id').getValue(),
						archiveRecord: win.archiveRecord
					},
					success: function() {
						// В зависимости от accessType переопределяем this.action
						if ( base_form.findField('accessType').getValue() == 'view' ) {
							this.action = 'view';
						}

						if ( this.action == 'edit' ) {
							this.setTitle(WND_STICK_ESTSEDIT);
							this.enableEdit(true);

							setCurrentDateTime({
								callback: function() {
									this.filterStickCause();
								}.createDelegate(this),
								dateField: base_form.findField('EvnStick_setDate'),
								loadMask: false,
								setDate: false,
								setDateMaxValue: true,
								windowId: this.id
							});
						}
						else {
							this.setTitle(WND_STICK_ESTSVIEW);
							this.enableEdit(false);

							this.findById('EStSEF_EvnStickWorkReleaseGrid').getTopToolbar().items.items[0].disable();
						}

						if ( this.userMedStaffFactId && !base_form.findField('isParentOwner').getValue() ) {
							base_form.findField('MedStaffFact_id').disable();
						}

						this.findById('EStSEF_EvnStickWorkReleasePanel').fireEvent('expand', this.findById('EStSEF_EvnStickWorkReleasePanel'));

						var
							index,
							evn_stick_is_contact = base_form.findField('EvnStick_IsContact').getValue(),
							MedPersonal_id = base_form.findField('MedPersonal_id').getValue(),
							MedStaffFact_id = base_form.findField('MedStaffFact_id').getValue(),
							org_id = base_form.findField('Org_id').getValue(),
							record;

						if ( this.action == 'edit' ) {
							base_form.findField('EvnStick_setDate').fireEvent('change', base_form.findField('EvnStick_setDate'), base_form.findField('EvnStick_setDate').getValue());
							base_form.findField('EvnStick_IsContact').fireEvent('change', base_form.findField('EvnStick_IsContact'), base_form.findField('EvnStick_IsContact').getValue());
	
							if ( !Ext.isEmpty(MedStaffFact_id) ) {
								index = base_form.findField('MedStaffFact_id').getStore().findBy(function(record, id) {
									return (record.get('MedStaffFact_id') == MedStaffFact_id);
								});
							}

							if ( index == -1 && !Ext.isEmpty(MedPersonal_id) ) {
								index = base_form.findField('MedStaffFact_id').getStore().findBy(function(record, id) {
									return (record.get('MedPersonal_id') == MedPersonal_id);
								});
							}

							if ( index >= 0 ) {
								base_form.findField('MedStaffFact_id').setValue(base_form.findField('MedStaffFact_id').getStore().getAt(index).get('MedStaffFact_id'));
							}
						}
						else {
							base_form.findField('MedStaffFact_id').getStore().load({
								callback: function() {
									index = base_form.findField('MedStaffFact_id').getStore().findBy(function(rec) {
										return (rec.get('MedStaffFact_id') == MedStaffFact_id);
									});

									if ( index >= 0 ) {
										base_form.findField('MedStaffFact_id').setValue(MedStaffFact_id);
									}
								}.createDelegate(this),
								params: {
									MedStaffFact_id: MedStaffFact_id
								}
							});
						}

						if ( org_id != null && Number(org_id) > 0 ) {
							base_form.findField('Org_id').getStore().load({
								callback: function(records, options, success) {
									if ( success ) {
										base_form.findField('Org_id').setValue(org_id);
									}
								},
								params: {
									Org_id: org_id,
									OrgType: 'org'
								}
							});
						}
						else {
							base_form.findField('Org_id').clearValue();
						}
						
						var isFizraRelease = !Ext.isEmpty(base_form.findField('EvnStickStudent_begDT').getValue());
						base_form.findField('isFizraRelease').setValue(isFizraRelease);
						base_form.findField('isFizraRelease').fireEvent('change', base_form.findField('isFizraRelease'), isFizraRelease);
						
						this.getLoadMask().hide();

						//base_form.clearInvalid();

						if ( this.action == 'edit' ) {
							base_form.findField('EvnStick_Num').focus(true, 250);
						}
						else {
							this.buttons[this.buttons.length - 1].focus();
						}
					}.createDelegate(this),
					url: '/?c=Stick&m=loadEvnStickStudentEditForm'
				});
			break;

			default:
				this.getLoadMask().hide();
				this.hide();
			break;
		}
	},
	width: 750
});