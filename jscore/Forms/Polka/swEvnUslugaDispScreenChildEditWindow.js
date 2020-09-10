/**
* swEvnUslugaDispScreenChildEditWindow - окно редактирования/добавления выполнения осмотра/исследования по скрининговому исследованию
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package			Polka
* @access			public
* @copyright		Copyright (c) 2015 Swan Ltd.
* @author			Dmitry Vlasenko
* @comment			Префикс для id компонентов EUDSCEW (swEvnUslugaDispScreenChildEditWindow)
*
*
* Использует: окно редактирования талона по доп. диспансеризации (swEvnPLDispScreenChildEditWindow)
*/

sw.Promed.swEvnUslugaDispScreenChildEditWindow = Ext.extend(sw.Promed.BaseForm, {
	action: null,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	collapsible: true,
	formStatus: 'edit',
	doSave: function(callback, nothide, options) {
		var win = this;
		if ( win.formStatus == 'save' || win.action == 'view' ) {
			return false;
		}
		win.formStatus = 'save';

		if ( typeof options != 'object' ) {
			options = new Object();
		}
		
		// проверяем заполненность, отправляем на сервер
		var base_form = this.findById('EUDSCEW_EvnUslugaDispDopEditForm').getForm();
		
		if ( !base_form.isValid() )
		{
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					win.formStatus = 'edit';
					win.findById('EUDSCEW_EvnUslugaDispDopEditForm').getFirstInvalidEl().focus(false);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		if (getRegionNick() == 'pskov' && !options.ignoreCheckPersonAge && this.SurveyType_Code == 27) {
			// Реализовать контроль при сохранении осмотра врача-педиатра (ВОП).
			// Если на дату осмотра врача-педиатра возраст пациента не соответствует значению, указанному в поле «Возрастная группа», то выводить
			// предупреждение: «Возраст пациента на дату осмотра врача-педиатра не соответствует указанной возрастной группе. (Сохранить / Отмена)».
			// При нажатии «Сохранить» - сохранять осмотр, При нажатии «Отмена» - сохранение отменить, возврат на форму редактирования осмотра.
			var newSetDate = base_form.findField('EvnUslugaDispDop_setDate').getValue();
			var age_start = -1;
			var month_start = -1;
			var age_end = -1;
			if ( !Ext.isEmpty(newSetDate) && win.AgeGroupDispRecord ) {
				age_start = swGetPersonAge(win.findById('EUDSCEW_PersonInformationFrame').getFieldValue('Person_Birthday'), newSetDate);
				var year = newSetDate.getFullYear();
				var endYearDate = new Date(year, 11, 31);
				age_end = swGetPersonAge(win.findById('EUDSCEW_PersonInformationFrame').getFieldValue('Person_Birthday'), endYearDate);
				month_start = swGetPersonAgeMonth(win.findById('EUDSCEW_PersonInformationFrame').getFieldValue('Person_Birthday'), newSetDate);

				if (!((
					win.AgeGroupDispRecord.get('AgeGroupDisp_From') <= age_end && win.AgeGroupDispRecord.get('AgeGroupDisp_To') >= age_end && age_end >= 4 // если на конец года не менее 4-ёх лет
					) || (
					win.AgeGroupDispRecord.get('AgeGroupDisp_From') <= age_start && win.AgeGroupDispRecord.get('AgeGroupDisp_To') >= age_start &&
					win.AgeGroupDispRecord.get('AgeGroupDisp_monthFrom') <= month_start && win.AgeGroupDispRecord.get('AgeGroupDisp_monthTo') >= month_start && age_end <= 3 // если на конец года не более 3 лет
				))) {
					sw.swMsg.show({
						buttons: Ext.Msg.YESNO,
						fn: function (buttonId) {
							if (buttonId == 'yes') {
								options.ignoreCheckPersonAge = true;
								win.doSave(callback, nothide, options);
							}
						},
						msg: lang['vozrast_patsienta_na_datu_osmotra_vracha-pediatra_ne_sootvetstvuet_ukazannoy_vozrastnoy_gruppe_sohranit'],
						title: lang['podtverjdenie_sohraneniya']
					});

					win.formStatus = 'edit';
					return false;
				}
			}
		}

		if ( Ext.isEmpty(base_form.findField('UslugaComplex_id').getValue()) ) {
			sw.swMsg.alert(lang['oshibka'], lang['usluga_doljna_byit_zapolnena'], function() {
				win.formStatus = 'edit';
				if (!base_form.findField('UslugaComplex_id').disabled) {
					base_form.findField('UslugaComplex_id').focus(true);
				}
			}.createDelegate(this));
			return false;
		}

		// https://redmine.swan.perm.ru/issues/44519
		if (
			options.ignoreEmptyDidDate != true
			&& Ext.isEmpty(base_form.findField('EvnUslugaDispDop_setDate').getValue())
		) {
			win.formStatus = 'edit';

			sw.swMsg.show({
				buttons: Ext.Msg.YESNO,
				fn: function ( buttonId ) {
					if ( buttonId == 'yes' ) {
						options.ignoreEmptyDidDate = true;
						win.doSave(callback, nothide, options);
					}
					else {
						base_form.findField('EvnUslugaDispDop_setDate').focus(true);
					}
				},
				msg: lang['data_vyipolneniya_osmotra_issledovaniya_ne_zapolnena_prodoljit_sohranenie'],
				title: lang['podtverjdenie_sohraneniya']
			});

			win.formStatus = 'edit';
			return false;
		}
		
		var diag_code = '';
		var index;
		var lpu_section_profile_code = '';
		var params = {};
		var record;

		if (base_form.findField('EvnUslugaDispDop_setDate').disabled) {
			params.EvnUslugaDispDop_setDate = Ext.util.Format.date(base_form.findField('EvnUslugaDispDop_setDate').getValue(), 'd.m.Y');
		}
		
		params.UslugaComplex_id = base_form.findField('UslugaComplex_id').getValue();
		params.DispClass_id = this.DispClass_id;

		index = base_form.findField('MedStaffFact_id').getStore().findBy(function(rec) {
			return (rec.get('MedStaffFact_id') == base_form.findField('MedStaffFact_id').getValue());
		});

		if ( index >= 0 ) {
			lpu_section_profile_code = base_form.findField('MedStaffFact_id').getStore().getAt(index).get('LpuSectionProfile_Code');
			base_form.findField('MedPersonal_id').setValue(base_form.findField('MedStaffFact_id').getStore().getAt(index).get('MedPersonal_id'));
		}

		index = base_form.findField('Diag_id').getStore().findBy(function(rec) {
			return (rec.get('Diag_id') == base_form.findField('Diag_id').getValue());
		});

		if ( index >= 0 ) {
			record = base_form.findField('Diag_id').getStore().getAt(index);

			diag_code = record.get('Diag_Code');

			if ( !Ext.isEmpty(diag_code) && diag_code.substr(0, 1).toUpperCase() != 'Z' && !base_form.findField('DeseaseType_id').getValue() ) {
				sw.swMsg.show({
					buttons: Ext.Msg.OK,
					fn: function() {
						win.formStatus = 'edit';
						base_form.findField('DeseaseType_id').markInvalid(lang['pole_obyazatelno_dlya_zapolneniya_pri_vyibrannom_diagnoze']);
						base_form.findField('DeseaseType_id').focus(false);
					}.createDelegate(this),
					icon: Ext.Msg.WARNING,
					msg: lang['ne_zadan_harakter_zabolevaniya'],
					title: ERR_INVFIELDS_TIT
				});
				return false;
			}
		}
		
		var checkResults = false;
		
		this.results.forEach(function(item,index) {
			if (Ext.isEmpty(item.ScreenValue_id)) checkResults = true;
		});
		
		if (checkResults) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					win.formStatus = 'edit';
				},
				icon: Ext.Msg.WARNING,
				msg: 'Необходимо заполнить результаты.',
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
		
		params.results = Ext.util.JSON.encode(this.results);

		win.getLoadMask("Подождите, идет сохранение...").show();
		base_form.submit({
			url: '/?c=EvnPLDispScreenChild&m=saveEvnUslugaDispDop',
			failure: function(result_form, action) {
				win.formStatus = 'edit';
				win.getLoadMask().hide();

				if ( action.result ) {
					if ( action.result.Error_Msg ) {
						sw.swMsg.alert(lang['oshibka'], action.result.Error_Msg);
					}
					else {
						sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_1]']);
					}
				}
			},
			params: params,
			success: function(result_form, action) {
				win.formStatus = 'edit';
				win.getLoadMask().hide();
				if ( action.result ) {
					if ( action.result.EvnUslugaDispDop_id ) {
						base_form.findField('EvnUslugaDispDop_id').setValue(action.result.EvnUslugaDispDop_id)
						var params = {};
						
						var items = win.findById('EUDSCEW_ResultsPanel').items.items;
						for (var key in items) {
							var obj = items[key];
							if (obj.name) {
								if (obj.name.inlist(win.inresults)) {
									params[obj.name] = obj.getValue();
								}
							}
							if (obj.hiddenName) {
								if (obj.hiddenName.inlist(win.inresults)) {
									params[obj.hiddenName] = obj.getValue();
								}
							}
						}
						
						win.callback(params);
						if (typeof callback == 'function') {
							callback();
						}
						if (!nothide) {
							win.hide();
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
			}
		});
    },
	draggable: true,
    height: 400,
	id: 'EvnUslugaDispScreenChildEditWindow',
    initComponent: function() {
		var win = this;
		
        Ext.apply(this, {
			buttons: [{
				handler: function() {
					win.doSave();
				},
				iconCls: 'save16',
				id: 'EUDSCEW_SaveButton',
				tabIndex: TABINDEX_EUDSCEW + 80,
				text: BTN_FRMSAVE
			},
			'-',
			HelpButton(win, TABINDEX_EUDSCEW + 81),
			{
				handler: function() {
					win.hide();
				},
				iconCls: 'cancel16',
				id: 'EUDSCEW_CancelButton',
				onTabAction: function() {
					Ext.getCmp('EUDSCEW_EvnUslugaDispDop_setDate').focus(true, 200);
				},
				onShiftTabAction: function() {
					Ext.getCmp('EUDSCEW_SaveButton').focus(true, 200);
				},
				tabIndex: TABINDEX_EUDSCEW + 82,
				text: BTN_FRMCANCEL
			}],
			items: [
				new	sw.Promed.PersonInformationPanelShort({
					id: 'EUDSCEW_PersonInformationFrame',
					region: 'north'
				}),
				new Ext.form.FormPanel({
					autoScroll: true,
					bodyBorder: false,
					border: false,
					frame: false,
					id: 'EUDSCEW_EvnUslugaDispDopEditForm',
					labelAlign: 'right',
					labelWidth: 150,
					items: [{
						id: 'EUDSCEW_EvnUslugaDispDop_id',
						name: 'EvnUslugaDispDop_id',
						value: 0,
						xtype: 'hidden'
					}, {
						name: 'EvnUslugaDispDop_pid',
						xtype: 'hidden'
					}, {
						name: 'PersonEvn_id',
						xtype: 'hidden'
					}, {
						name: 'MedPersonal_id',
						xtype: 'hidden'
					}, {
						name: 'EvnDirection_id',
						xtype: 'hidden'
					}, {
						name: 'Server_id',
						xtype: 'hidden'
					}, {
						name: 'SurveyType_id',
						xtype: 'hidden'
					}, {
						name: 'Lpu_id',
						xtype: 'hidden'
					}, {
						title: lang['vyipolnenie'],
						bodyStyle: 'padding: 5px',
						layout: 'form',
						items: [
							{
								allowBlank: false,
								id: 'EUDSCEWUslugaComplexCombo',
								fieldLabel: lang['usluga'],
								hiddenName: 'UslugaComplex_id',
								listWidth: 590,
								showUslugaComplexEndDate: true,
								tabIndex: TABINDEX_EUDSCEW + 1,
								width: 400,
								nonDispOnly: false,
								xtype: 'swuslugacomplexnewcombo',
								listeners: {
									'change': function(combo, newValue, oldValue) {
										var base_form = win.findById('EUDSCEW_EvnUslugaDispDopEditForm').getForm();
										
										Ext.Ajax.request({
											params: {
												UslugaComplex_id: newValue,
												ScreenType_id: base_form.findField('ScreenType_id').getValue(),
												EvnUslugaDispDop_pid: base_form.findField('EvnUslugaDispDop_pid').getValue()
											},
											url: '/?c=EvnPLDispScreen&m=getEvnUslugaDispDopResult',
											success: function (response, action) {
												var resp = Ext.util.JSON.decode(response.responseText);
												
												var items = [];
												
												resp.forEach(function (item) {
													var fieldLabel;
													var store = [];
													var id = '';
													var currentValue;
													item.forEach(function(itm) {
														fieldLabel = itm.ScreenCheckList_PunktName;
														store.push([itm.ScreenValue_id, itm.ScreenValue_Name]);
														id = 'result_'+itm.ScreenCheckList_id;
														currentValue = itm.currentValue;
														
														var isInserted = false;
														
														win.results.forEach(function (res, index) {
															if (res.ScreenCheckList_id == itm.ScreenCheckList_id) {
																win.results[index].ScreenValue_id = itm.currentValue;
																isInserted = true;
															}
														});
														
														if (!isInserted) win.results.push({
															ScreenCheckList_id: itm.ScreenCheckList_id,
															ScreenValue_id: itm.currentValue
														});
													});
													
													items.push({
														value: currentValue,
														xtype: 'combo',
														id: id,
														width: 400,
														name: id,
														fieldLabel: fieldLabel,
														triggerAction: 'all',
														editable: false,
														store: store,
														allowBlank: false,
														disabled: win.EvnUsluga_IsAPP == 2,
														listeners: {
															'change': function(combo, newValue, oldValue) {
																if (newValue == oldValue) return false;
																var screen_check_list_id = combo.getId().split('_')[1];
																
																win.results.forEach(function (res, index) {
																	if (res.ScreenCheckList_id == screen_check_list_id)
																		win.results[index].ScreenValue_id = newValue;
																});
															}
														},
														tabIndex: TABINDEX_EUDSEW + 8,
													});
												});
												
												win.findById('EUDSCEW_ResultsPanel').add(
													{
														layout: 'form',
														border: false,
														labelWidth: 200,
														items: items
													}
												);
												
												win.findById('EUDSCEW_ResultsPanel').doLayout();
											}
										});
									}
								}
							}, {
								comboSubject: 'VizitKind',
								fieldLabel: lang['vid_posescheniya'],
								value: 1,
								hiddenName: 'VizitKind_id',
								lastQuery: '',
								tabIndex: TABINDEX_EUDSCEW + 1,
								width: 450,
								xtype: 'swcommonsprcombo'
							}, {
								border: false,
								layout: 'column',
								items: [{
									border: false,
									layout: 'form',
									items: [{
										fieldLabel: lang['data_vyipolneniya'],
										format: 'd.m.Y',
										allowBlank: false,
										id: 'EUDSCEW_EvnUslugaDispDop_setDate',
										listeners: {
											'change': function(field, newValue, oldValue) {
												if ( blockedDateAfterPersonDeath('personpanelid', 'EUDSCEW_PersonInformationFrame', field, newValue, oldValue) ) {
													return false;
												}

												var base_form = this.findById('EUDSCEW_EvnUslugaDispDopEditForm').getForm();

												if (getRegionNick() == 'ufa' && !Ext.isEmpty(win.DispClass_id) && win.DispClass_id.inlist([1,2]) && win.SurveyType_Code == 19) {
													// услуга терапевта зависи от даты выполнения, а не согласия
													win.UslugaComplex_Date = newValue;
													base_form.findField('UslugaComplex_id').getStore().baseParams.UslugaComplex_Date = (typeof win.UslugaComplex_Date == 'object' ? Ext.util.Format.date(win.UslugaComplex_Date, 'd.m.Y') : win.UslugaComplex_Date);
													win.loadUslugaComplexCombo();
												}

												if ( Ext.isEmpty(newValue) ) {
													base_form.findField('Diag_id').setAllowBlank(true);
												}
												else {
													base_form.findField('Diag_id').setAllowBlank(false);

													if ( Ext.isEmpty(oldValue) && Ext.isEmpty(base_form.findField('Diag_id').getValue()) ) {
														base_form.findField('Diag_id').getStore().load({
															params: { where: "where Diag_Code = 'Z10.8'" },
															callback: function() {
																var diag_id = base_form.findField('Diag_id').getStore().getAt(0).get('Diag_id');
																base_form.findField('Diag_id').setValue(diag_id);
																base_form.findField('Diag_id').fireEvent('select', base_form.findField('Diag_id'), base_form.findField('Diag_id').getStore().getAt(0), 0);
																base_form.findField('Diag_id').onChange();
															}
														});
													}
												}
												base_form.findField('Diag_id').setFilterByDate(newValue);
												
												if (Ext.isEmpty(base_form.findField('Lpu_uid').getValue()) && (Ext.isEmpty(base_form.findField('Lpu_id').getValue()) || base_form.findField('Lpu_id').getValue() == getGlobalOptions().lpu_id)) {
													this.setLpuSectionAndMedStaffFactFilter();
													base_form.findField('PlaceWork_id').fireEvent('change', base_form.findField('PlaceWork_id'), 1);
													base_form.findField('PlaceWork_id').setValue(1);
												} else {
													base_form.findField('PlaceWork_id').fireEvent('change', base_form.findField('PlaceWork_id'), 2);
													base_form.findField('PlaceWork_id').setValue(2);
												}
											}.createDelegate(this)
										},
										name: 'EvnUslugaDispDop_setDate',
										//maxValue: Date.parseDate(getGlobalOptions().date, 'd.m.Y'),
										plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
										tabIndex: TABINDEX_EUDSCEW + 2,
										width: 100,
										xtype: 'swdatefield'
									}]
								}, {
									border: false,
									layout: 'form',
									labelWidth: 50,
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
										name: 'EvnUslugaDispDop_setTime',
										onTriggerClick: function() {
											var base_form = this.findById('EUDSCEW_EvnUslugaDispDopEditForm').getForm();

											var time_field = base_form.findField('EvnUslugaDispDop_setTime');

											if ( time_field.disabled ) {
												return false;
											}

											setCurrentDateTime({
												callback: function() {
													base_form.findField('EvnUslugaDispDop_setDate').fireEvent('change', base_form.findField('EvnUslugaDispDop_setDate'), base_form.findField('EvnUslugaDispDop_setDate').getValue());
												},
												dateField: base_form.findField('EvnUslugaDispDop_setDate'),
												loadMask: true,
												setDate: true,
												//setDateMaxValue: true,
												setDateMinValue: false,
												setTime: true,
												timeField: time_field,
												windowId: this.id
											});
										}.createDelegate(this),
										plugins: [ new Ext.ux.InputTextMask('99:99', true) ],
										tabIndex: TABINDEX_EUDSCEW + 3,
										validateOnBlur: false,
										width: 60,
										xtype: 'swtimefield'
									}]
								}]
							}, {
								width: 450,
								allowBlank: false,
								xtype: 'combo',
								hiddenName: 'PlaceWork_id',
								fieldLabel: 'Место выполнения',
								editable: false,
								triggerAction: 'all',
								mode: 'local',
								displayField: 'name',
								valueField: 'id',
								value: 1,
								listeners: {
									'change': function(field, newValue, oldValue) {
										var base_form = win.findById('EUDSCEW_EvnUslugaDispDopEditForm').getForm();
										
										if (newValue == 2) {
											base_form.findField('Lpu_uid').showContainer();
											base_form.findField('Lpu_uid').setAllowBlank(false);
											
											if (Ext.isEmpty(base_form.findField('Lpu_uid').getValue()) && base_form.findField('Lpu_id').getValue() != getGlobalOptions().lpu_id) {
												base_form.findField('Lpu_uid').setValue(base_form.findField('Lpu_id').getValue());
											}
											
											base_form.findField('LpuSection_id').clearValue();
											base_form.findField('LpuSection_id').setAllowBlank(true);
											base_form.findField('LpuSection_id').getStore().removeAll();
											
											base_form.findField('MedStaffFact_id').disableParentElement();
										} else {
											base_form.findField('Lpu_uid').hideContainer();
											base_form.findField('Lpu_uid').setAllowBlank(true);
											base_form.findField('Lpu_uid').clearValue();
											
											base_form.findField('LpuSection_id').setAllowBlank(false);
											
											base_form.findField('MedStaffFact_id').enableParentElement();
											win.setLpuSectionAndMedStaffFactFilter();
										}
									}
								},
								store: new Ext.data.SimpleStore({
									fields: [
										{name: 'id', type: 'int'},
										{name: 'name', type: 'string'}
									],
									data: [
										[1, 'Отделение ЛПУ'],
										[2, 'Другая МО']
									]
								}),
							}, {
								fieldLabel: 'Медицинская организация',
								allowBlank: true,
								hiddenName: 'Lpu_uid',
								listeners: {
									'change': function(field, newValue, oldValue) {
										var base_form = win.findById('EUDSCEW_EvnUslugaDispDopEditForm').getForm();
										
										base_form.findField('MedStaffFact_id').clearValue();
										
										base_form.findField('MedStaffFact_id').getStore().load({
											callback: function() {
												//console.log(arguments);
											},
											params: {
												mode: 'combo',
												Lpu_id: newValue
											}
										});
									}
								},
								width: 450,
								xtype: 'swlpulocalcombo'
							}, {
								hiddenName: 'LpuSection_id',
								id: 'EUDSCEW_LpuSectionCombo',
								lastQuery: '',
								listWidth: 650,
								linkedElements: [
									'EUDSCEW_MedPersonalCombo'
								],
								listeners: {
									'select': function(combo, record, index) {
										combo.setValue(record.get('LpuSection_id'));
										combo.fireEvent('change', combo, combo.getValue());
									},
									'change': function (field, newValue, oldValue) {
										var base_form = win.findById('EUDSCEW_EvnUslugaDispDopEditForm').getForm();
										if (getRegionNick() == 'ufa' && !Ext.isEmpty(win.DispClass_id) && win.DispClass_id.inlist([1,2])) {
											// услуга зависит от выбранного отделения
											base_form.findField('UslugaComplex_id').getStore().baseParams.LpuSection_id = base_form.findField('LpuSection_id').getValue();
											win.loadUslugaComplexCombo();
										}
										
										if (getRegionNick() == 'kz') {
											var params = {
												allowLowLevel: 'yes'
												, allowDuplacateMSF: true
												, LpuSection_id: newValue
											}
											
											base_form.findField('MedStaffFact_id').clearValue();
											
											setMedStaffFactGlobalStoreFilter(params);
											
											base_form.findField('MedStaffFact_id').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));
										}
									}
								},
								tabIndex: TABINDEX_EUDSCEW + 4,
								width: 450,
								xtype: 'swlpusectionglobalcombo'
							}, {
								hiddenName: 'MedStaffFact_id',
								id: 'EUDSCEW_MedPersonalCombo',
								lastQuery: '',
								listWidth: 650,
								parentElementId: 'EUDSCEW_LpuSectionCombo',
                                listeners: {
									'select': function(combo, record, index) {
										combo.setValue(record.get('MedStaffFact_id'));
										combo.fireEvent('change', combo, combo.getValue());
									},
                                    'change': function(field, newValue, oldValue) {
										var base_form = win.findById('EUDSCEW_EvnUslugaDispDopEditForm').getForm();
                                    }
                                },
								tabIndex: TABINDEX_EUDSCEW + 5,
								width: 450,
								xtype: 'swmedstafffactglobalcombo'
							}, {
								fieldLabel: lang['diagnoz'],
								hiddenName: 'Diag_id',
								onChange: function() {
									var diag_code = this.getFieldValue('Diag_Code');
									if (diag_code) {
										var base_form = win.findById('EUDSCEW_EvnUslugaDispDopEditForm').getForm();
										if ( !Ext.isEmpty(diag_code) && diag_code.substr(0, 1).toUpperCase() == 'Z') {
											base_form.findField('DeseaseType_id').clearValue();
											base_form.findField('DeseaseType_id').disable();
											base_form.findField('DeseaseType_id').setAllowBlank(true);
										} else {
											base_form.findField('DeseaseType_id').enable();
											base_form.findField('DeseaseType_id').setAllowBlank(false);
										}
									}
								},
								listWidth: 600,
								tabIndex: TABINDEX_EUDSCEW + 6,
								width: 450,
								xtype: 'swdiagcombo'
							}, {
								comboSubject: 'DeseaseType',
								disabled: true,
								fieldLabel: lang['harakter_zabolevaniya'],
								hiddenName: 'DeseaseType_id',
								lastQuery: '',
								tabIndex: TABINDEX_EUDSCEW + 7,
								width: 450,
								xtype: 'swcommonsprcombo'
							}, {
								name: 'ScreenType_id',
								xtype: 'hidden'
							}
						]
					}, {
						title: lang['rezultat'],
						bodyStyle: 'padding: 5px',
						defaults: {
							decimalPrecision: 2
						},
						labelWidth: 210,
						id: 'EUDSCEW_ResultsPanel',
						layout: 'form',
						items: []
					}],
					layout: 'form',
					reader: new Ext.data.JsonReader({
						success: function() { }
					}, [
						{ name: 'EvnUslugaDispDop_id' },
						{ name: 'EvnUslugaDispDop_pid' },
						{ name: 'PersonEvn_id' },
						{ name: 'Server_id' },
						{ name: 'SurveyType_id' },
						{ name: 'Lpu_uid' },
						{ name: 'VizitKind_id' },
						{ name: 'UslugaComplex_id' },
						{ name: 'MedStaffFact_id' },
						{ name: 'Lpu_id' },
						{ name: 'EvnUslugaDispDop_setDate' },
						{ name: 'EvnUslugaDispDop_setTime' },
						{ name: 'LpuSection_id' },
						{ name: 'MedPersonal_id' },
						{ name: 'Diag_id' },
						{ name: 'DeseaseType_id' },
						{ name: 'electro_cardio_gramm' },
						{ name: 'gemokult_test' },
						{ name: 'total_cholesterol' },
						{ name: 'bio_blood_triglycerid' },
						{ name: 'glucose' },
						{ name: 'pap_test' },
						{ name: 'res_mammo_graph' },
						{ name: 'pressure_measure' },
						{ name: 'eye_pressure_left' },
						{ name: 'eye_pressure_right' },
						{ name: 'survey_result' }
					]),
					region: 'center'
				})
			]
		});
		sw.Promed.swEvnUslugaDispScreenChildEditWindow.superclass.initComponent.apply(this, arguments);
    },
	loadFirstMedPersonal: true,
    keys: [{
    	alt: true,
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

            e.browserEvent.returnValue = false;
            e.returnValue = false;

            if (Ext.isIE)
            {
            	e.browserEvent.keyCode = 0;
            	e.browserEvent.which = 0;
            }

        	var current_window = Ext.getCmp('EvnUslugaDispScreenChildEditWindow');

            if (e.getKey() == Ext.EventObject.J)
            {
            	current_window.hide();
            }
			else if (e.getKey() == Ext.EventObject.C)
			{
	        	if ('view' != current_window.action)
	        	{
	            	current_window.doSave();
	            }
			}
        },
        key: [ Ext.EventObject.C, Ext.EventObject.J ],
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
    minHeight: 370,
    minWidth: 700,
    modal: true,
    onHide: Ext.emptyFn,
	plain: true,
    resizable: true,
	setLpuSectionAndMedStaffFactFilter: function() {
		var win = this;
		var base_form = this.findById('EUDSCEW_EvnUslugaDispDopEditForm').getForm();

		// Учитываем дату и место выполнения
		var EvnUslugaDispDop_setDate = base_form.findField('EvnUslugaDispDop_setDate').getValue();

		base_form.findField('LpuSection_id').disableLinkedElements();
		base_form.findField('MedStaffFact_id').disableParentElement();

		// Убрал условие "не Бурятия", ибо https://redmine.swan.perm.ru/issues/51414
		base_form.findField('LpuSection_id').enableLinkedElements();
		base_form.findField('MedStaffFact_id').enableParentElement();

		if (Ext.isEmpty(base_form.findField('EvnUslugaDispDop_setDate').getValue())) {
			base_form.findField('LpuSection_id').setAllowBlank(true);
			base_form.findField('MedStaffFact_id').setAllowBlank(true);
		} else {
			var UslugaCategory_SysNick = '';
			var UslugaComplex_id = base_form.findField('UslugaComplex_id').getValue();

			if ( !Ext.isEmpty(UslugaComplex_id) ) {
				var index = base_form.findField('UslugaComplex_id').getStore().findBy(function(rec) {
					return (rec.get('UslugaComplex_id') == UslugaComplex_id);
				});

				if ( index >= 0 ) {
					UslugaCategory_SysNick = base_form.findField('UslugaComplex_id').getStore().getAt(index).get('UslugaCategory_SysNick');
				}
			}
			// https://redmine.swan.perm.ru/issues/20625
			if ( getRegionNick().inlist([ 'pskov', 'ufa', 'buryatiya' ]) && UslugaCategory_SysNick != 'lpusection' ) {
				base_form.findField('LpuSection_id').setAllowBlank(true);
				base_form.findField('MedStaffFact_id').setAllowBlank(true);
			}
			else {
				base_form.findField('LpuSection_id').setAllowBlank(false);
				base_form.findField('MedStaffFact_id').setAllowBlank(false);
			}
		}

		var index;
		var params = {
			 allowLowLevel: 'yes'
			,allowDuplacateMSF: true
		}

		if (!getRegionNick().inlist(['ekb'])) {
			params.isNotStac = true;
		}

		if ( !Ext.isEmpty(EvnUslugaDispDop_setDate) ) {
			params.onDate = Ext.util.Format.date(EvnUslugaDispDop_setDate, 'd.m.Y');
		}

		if ( getRegionNick().inlist(['perm']) ) {
			if (!Ext.isEmpty(this.OrpDispSpec_Code)) {
				switch (this.OrpDispSpec_Code)
				{
					case 1:
						params.arrayLpuSectionProfile = ['900', '0900', '1003', '0905', '1011', '57', '68', '151'];
						switch (this.type)
						{
							case 'DispTeenInspectionPeriod':
								params.arrayLpuSectionProfile = ['900', '0900', '1003', '922', '923', '924', '0905', '1011', '48', '57', '68', '151'];
							break;

							case 'DispTeenInspectionProf':
								params.arrayLpuSectionProfile = ['900', '0900', '1003', '918', '0905', '1011', '57', '68', '151'];
							break;

							case 'DispTeenInspectionPred':
								params.arrayLpuSectionProfile = ['900', '0900', '1003', '919', '920', '921', '929', '930', '931', '939', '940', '941', '0905', '1011', '48', '57', '68', '151'];
							break;
						}
					break;
					case 2:
						params.arrayLpuSectionProfile = ['2800', '53'];
					break;
					case 3:
						params.arrayLpuSectionProfile = ['2700', '65'];
					break;
					case 4:
						params.arrayLpuSectionProfile = ['2300', '2350', '20', '112'];
					break;
					case 5:
						params.arrayLpuSectionProfile = ['2600', '162'];
					break;
					case 6:
						params.arrayLpuSectionProfile = ['2517', '2519', '136'];
					break;
					case 7:
						params.arrayLpuSectionProfile = ['1830', '1800', '85', '86'];
						if (this.type.inlist(['DispTeenInspectionPeriod', 'DispTeenInspectionProf', 'DispTeenInspectionPred'])) {
							params.arrayLpuSectionProfile = ['1830', '1800', '1802', '1810', '85', '86', '89'];
						}
					break;
					case 8:
						params.arrayLpuSectionProfile = ['1450', '100'];
						if (this.type.inlist(['DispTeenInspectionPeriod', 'DispTeenInspectionProf', 'DispTeenInspectionPred'])) {
							params.arrayLpuSectionProfile = ['1450', '2300', '2350', '20', '100', '112'];
						}
					break;
					case 9:
					case 12:
					case 13:
						params.arrayLpuSectionProfile = ['3710', '72'];
					break;
					case 10:
						params.arrayLpuSectionProfile = ['1530', '1500', '2350', '19', '20', '108'];
						if (this.type.inlist(['DispTeenInspectionPeriod', 'DispTeenInspectionProf', 'DispTeenInspectionPred'])) {
							params.arrayLpuSectionProfile = ['1530', '1500', '2300', '2350', '19', '20', '108', '112'];
						}
					break;
					case 11:
						params.arrayLpuSectionProfile = ['0530', '0510', '21', '122'];
					break;
				}
			} else if (this.SurveyType_Code == 27) {
				// фильтр по профилю отделения 0900, 1003
				params.arrayLpuSectionProfile = ['0900', '1003', '57', '68'];
			}
		} else if (getRegionNick().inlist(['pskov'])) {
			if (!Ext.isEmpty(this.OrpDispSpec_Code)) {
				switch (this.OrpDispSpec_Code)
				{
					case 1: // Педиатрия
						params.arrayLpuSectionProfile = ['68'];
						break;
					case 2: // Неврология
						params.arrayLpuSectionProfile = ['53'];
						break;
					case 3: // Офтальмология
						params.arrayLpuSectionProfile = ['65'];
						break;
					case 4: // Детская хирургия
						params.arrayLpuSectionProfile = ['112','20'];
						break;
					case 5: // Отоларингология
						params.arrayLpuSectionProfile = ['162'];
						break;
					case 6: // Гинекология
						params.arrayLpuSectionProfile = ['136'];
						break;
					case 7: // Стоматология детская
						params.arrayLpuSectionProfile = ['85','86','63'];
						break;
					case 8: // Ортопедия-травматология
						params.arrayLpuSectionProfile = ['100','112','20'];
						break;
					case 10: // Детская урология-андрология
						params.arrayLpuSectionProfile = ['19','108','112','20'];
						break;
					case 11: // Детская эндокринология
						params.arrayLpuSectionProfile = ['122','21'];
						break;
					case 9: // Психиатрия
					case 12: // Детская психиатрия
					case 13: // Подростковая психиатрия
						params.arrayLpuSectionProfile = ['72','74'];
						break;
				}
			}
		}

		var
			LpuSection_id,
			MedPersonal_id,
			MedStaffFact_id;

		// Сохраняем текущие значения
		if ( typeof this.loadedParams == 'object' && (!Ext.isEmpty(this.loadedParams.LpuSection_id) || !Ext.isEmpty(this.loadedParams.MedPersonal_id)) ) {
			LpuSection_id = this.loadedParams.LpuSection_id || null;
			MedPersonal_id = this.loadedParams.MedPersonal_id || null;

			this.loadedParams = new Object();
		}
		else {
			LpuSection_id = base_form.findField('LpuSection_id').getValue();
			MedStaffFact_id = base_form.findField('MedStaffFact_id').getValue();
		}

		base_form.findField('LpuSection_id').clearValue();
		base_form.findField('MedStaffFact_id').clearValue();

		setLpuSectionGlobalStoreFilter(params);
		setMedStaffFactGlobalStoreFilter(params);

		base_form.findField('LpuSection_id').getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));
		base_form.findField('MedStaffFact_id').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));

		if ( !Ext.isEmpty(LpuSection_id) ) {
			index = base_form.findField('LpuSection_id').getStore().findBy(function(rec) {
				return (rec.get('LpuSection_id') == LpuSection_id);
			});

			if ( index >= 0 ) {
				base_form.findField('LpuSection_id').setValue(LpuSection_id);
			}
		}

		if ( !Ext.isEmpty(MedStaffFact_id) ) {
			index = base_form.findField('MedStaffFact_id').getStore().findBy(function(rec) {
				return (rec.get('MedStaffFact_id') == MedStaffFact_id);
			});

			if ( index >= 0 ) {
				base_form.findField('MedStaffFact_id').setValue(MedStaffFact_id);
			}
		}
		else if ( !Ext.isEmpty(MedPersonal_id) ) {
			index = base_form.findField('MedStaffFact_id').getStore().findBy(function(rec) {
				return (rec.get('LpuSection_id') == LpuSection_id && rec.get('MedPersonal_id') == MedPersonal_id);
			});

			if ( index == -1 ) {
				index = base_form.findField('MedStaffFact_id').getStore().findBy(function(rec) {
					return (rec.get('MedPersonal_id') == MedPersonal_id);
				});
			}

			if ( index >= 0 ) {
				base_form.findField('MedStaffFact_id').setValue(base_form.findField('MedStaffFact_id').getStore().getAt(index).get('MedStaffFact_id'));
			}
		}
	},
	loadUslugaComplexCombo: function() {
		var win = this;
		var base_form = win.findById('EUDSCEW_EvnUslugaDispDopEditForm').getForm();

		// повторно грузить одно и то же не нужно
		var newUslugaComplexParams = Ext.util.JSON.encode(base_form.findField('UslugaComplex_id').getStore().baseParams);
		if (newUslugaComplexParams != win.lastUslugaComplexParams) {
			var currentUslugaComplex_id = base_form.findField('UslugaComplex_id').getValue();
			win.getLoadMask(lang['zagruzka_spiska_vozmojnyih_uslug_pojaluysta_podojdite']).show();
			base_form.findField('UslugaComplex_id').clearValue();
			base_form.findField('UslugaComplex_id').getStore().removeAll();
			win.lastUslugaComplexParams = newUslugaComplexParams;
			base_form.findField('UslugaComplex_id').getStore().load({
				callback: function () {
					win.getLoadMask().hide();
					index = base_form.findField('UslugaComplex_id').getStore().findBy(function (rec) {
						return (rec.get('UslugaComplex_id') == currentUslugaComplex_id);
					});

					if (base_form.findField('UslugaComplex_id').getStore().getCount() == 1) {
						ucid = base_form.findField('UslugaComplex_id').getStore().getAt(0).get('UslugaComplex_id');
						base_form.findField('UslugaComplex_id').setValue(ucid);
						base_form.findField('UslugaComplex_id').disable();
					} else if (base_form.findField('UslugaComplex_id').getStore().getCount() > 1) {
						if (index >= 0) {
							ucid = base_form.findField('UslugaComplex_id').getStore().getAt(index).get('UslugaComplex_id');
						} else {
							// по умолчанию подставляем эти услуги
							index = base_form.findField('UslugaComplex_id').getStore().findBy(function (rec) {
								switch (win.SurveyType_Code) {
									case 103:
										return (rec.get('UslugaComplex_Code') == 'A01.01.000');
										break;
									case 111:
										return (rec.get('UslugaComplex_Code') == 'D89.111.331');
										break;
									case 132:
										return (rec.get('UslugaComplex_Code') == 'B03.335.003');
										break;
									case 34:
								}
								return false;
							});
							if (index >= 0) {
								ucid = base_form.findField('UslugaComplex_id').getStore().getAt(index).get('UslugaComplex_id');
							} else {
								ucid = base_form.findField('UslugaComplex_id').getStore().getAt(0).get('UslugaComplex_id');
							}
						}
						base_form.findField('UslugaComplex_id').setValue(ucid);
						if (win.action != 'view') {
							base_form.findField('UslugaComplex_id').enable();
						}

						if (win.SurveyType_Code == '20' && getRegionNick() == 'ekb') {
							base_form.findField('UslugaComplex_id').disable();
						}
					}

					base_form.findField('UslugaComplex_id').fireEvent('change', base_form.findField('UslugaComplex_id'), base_form.findField('UslugaComplex_id').getValue());
				}
			});
		}
	},
	show: function() {
		sw.Promed.swEvnUslugaDispScreenChildEditWindow.superclass.show.apply(this, arguments);
		this.formStatus = 'edit';
		var current_window = this;

		current_window.restore();
		current_window.center();

        var form = current_window.findById('EUDSCEW_EvnUslugaDispDopEditForm');
		var base_form = form.getForm();
		base_form.reset();

		base_form.findField('Diag_id').lastQuery = lang['stroka_kotoruyu_nikto_ne_dodumaetsya_vvodit_v_kachestve_filtra_ibo_eto_bred_iskat_diagnoz_po_takoy_stroke'];
		
		base_form.findField('Lpu_uid').hideContainer();
		
		current_window.callback = Ext.emptyFn;
       	current_window.OmsSprTerr_Code = null;
       	current_window.onHide = Ext.emptyFn;
       	current_window.Sex_Code = null;

		base_form.findField('Diag_id').filterDate = null;

		if (!arguments[0] || !arguments[0].formParams || !arguments[0].SurveyTypeLink_id || !arguments[0].SurveyType_Code || !arguments[0].DispClass_id)
        {
        	Ext.Msg.alert(lang['soobschenie'], lang['nevernyie_parametryi'], function() { current_window.hide(); } );
        	return false;
        }
		
		current_window.object = 'EvnPLDispScreenChild';
        if (arguments[0].object)
        {
        	current_window.object = arguments[0].object;
        }

		current_window.AgeGroupDisp_id = null;
        if (arguments[0].AgeGroupDisp_id)
        {
        	current_window.AgeGroupDisp_id = arguments[0].AgeGroupDisp_id;
        }

		current_window.SurveyTypeLink_IsLowWeight = null;
        if (arguments[0].SurveyTypeLink_IsLowWeight)
        {
        	current_window.SurveyTypeLink_IsLowWeight = arguments[0].SurveyTypeLink_IsLowWeight;
        }

		current_window.AgeGroupDispRecord = false;
		if (arguments[0].AgeGroupDispRecord)
        {
        	current_window.AgeGroupDispRecord = arguments[0].AgeGroupDispRecord;
        }
		
		current_window.type = '';
		if (arguments[0].type)
        {
        	current_window.type = arguments[0].type;
        }
		
		base_form.setValues(arguments[0].formParams);
		
		this.SurveyType_Code = arguments[0].SurveyType_Code;
		this.SurveyTypeLink_id = arguments[0].SurveyTypeLink_id;
		this.DispClass_id = arguments[0].DispClass_id;
		
		this.loadFirstMedPersonal = true;

		this.OrpDispSpec_Code = 0;
		if (arguments[0].OrpDispSpec_Code) {
			this.OrpDispSpec_Code = arguments[0].OrpDispSpec_Code;
		}

		if (this.SurveyType_Code && this.SurveyType_Code.inlist(['118','119','120','121','122','123','124','125','126'])) {
			base_form.findField('VizitKind_id').showContainer();
		} else {
			base_form.findField('VizitKind_id').clearValue();
			base_form.findField('VizitKind_id').hideContainer();
		}
		
		this.disableDidDate = false;
		if (arguments[0].disableDidDate)
        {
			this.disableDidDate = true;
        }

		if (arguments[0].minDate)
		{
			base_form.findField('EvnUslugaDispDop_setDate').setMinValue(arguments[0].minDate);
		}

		if (arguments[0].maxDate)
		{
			base_form.findField('EvnUslugaDispDop_setDate').setMaxValue(arguments[0].maxDate);
		}
		
		if (arguments[0].EvnDirection_id)
		{
			base_form.findField('EvnDirection_id').setValue(arguments[0].EvnDirection_id);
		}
		
		this.UslugaComplex_Date = null;
		if (arguments[0].UslugaComplex_Date)
        {
			this.UslugaComplex_Date = arguments[0].UslugaComplex_Date;
        }

		this.results = [];
		
		if (arguments[0].ScreenType_id) base_form.findField('ScreenType_id').setValue(arguments[0].ScreenType_id);
		
		this.findById('EUDSCEW_ResultsPanel').removeAll();
		
		// Фильтрация подразумевающая наличие для 1 SurveyType_id нескольких услуг в SurveyTypeLink
		base_form.findField('UslugaComplex_id').getStore().baseParams.SurveyTypeLink_lid = current_window.SurveyTypeLink_id;
		base_form.findField('UslugaComplex_id').getStore().baseParams.EvnPLDisp_id = base_form.findField('EvnUslugaDispDop_pid').getValue();
		base_form.findField('UslugaComplex_id').getStore().baseParams.SurveyTypeLink_id = null;
		base_form.findField('UslugaComplex_id').getStore().baseParams.UslugaComplex_Date = (typeof this.UslugaComplex_Date == 'object' ? Ext.util.Format.date(this.UslugaComplex_Date, 'd.m.Y') : this.UslugaComplex_Date);
		base_form.findField('UslugaComplex_id').getStore().baseParams.LpuSection_id = null;
		base_form.findField('UslugaComplex_id').getStore().baseParams.AgeGroupDisp_id = this.AgeGroupDisp_id;
		base_form.findField('UslugaComplex_id').getStore().baseParams.SurveyTypeLink_IsLowWeight = this.SurveyTypeLink_IsLowWeight;

		// загрузить услуги в комбо, задисаблить комбо, если одна услуга
		base_form.findField('UslugaComplex_id').clearValue();
		base_form.findField('UslugaComplex_id').getStore().removeAll();
		this.lastUslugaComplexParams = null;

        if (arguments[0].action)
        {
        	current_window.action = arguments[0].action;
        }
		
		if (arguments[0].set_date)
        {
        	current_window.set_date = arguments[0].set_date;
        }

        if (arguments[0].callback)
        {
            current_window.callback = arguments[0].callback;
        }

        if (arguments[0].onHide)
        {
        	current_window.onHide = arguments[0].onHide;
        }

        if ( !Ext.isEmpty(arguments[0].Sex_Code) ) {
        	current_window.Sex_Code = arguments[0].Sex_Code;
        }

        if ( !Ext.isEmpty(arguments[0].OmsSprTerr_Code) ) {
        	current_window.OmsSprTerr_Code = arguments[0].OmsSprTerr_Code;
        }

		current_window.findById('EUDSCEW_PersonInformationFrame').load({
			Person_id: (arguments[0].Person_id ? arguments[0].Person_id : ''),
			Person_Birthday: (arguments[0].Person_Birthday ? arguments[0].Person_Birthday : ''),
			Person_Firname: (arguments[0].Person_Firname ? arguments[0].Person_Firname : ''),
			Person_Secname: (arguments[0].Person_Secname ? arguments[0].Person_Secname : ''),
			Person_Surname: (arguments[0].Person_Surname ? arguments[0].Person_Surname : ''),
			callback: function() {
				var field = base_form.findField('EvnUslugaDispDop_setDate');
				clearDateAfterPersonDeath('personpanelid', 'EUDSCEW_PersonInformationFrame', field);
			}
		});

  		var loadMask = new Ext.LoadMask(Ext.get('EvnUslugaDispScreenChildEditWindow'), { msg: LOAD_WAIT });
		loadMask.show();

		var sex_id = arguments[0].Sex_id;
		var age = arguments[0].Person_Age;
		
		var med_personal_id = 0; //arguments[0].formParams.MedPersonal_id;
		
		this.age = arguments[0].Person_Age;
		this.loadedParams = new Object();
		this.Sex_id = arguments[0].Sex_id;
		this.Person_Birthday = arguments[0].Person_Birthday;
		
		this.wintitle = lang['osmotr_issledovanie'];
		if (arguments[0].SurveyType_Name) {
			this.wintitle = arguments[0].SurveyType_Name;
		}
		
		switch (current_window.action)
		{
        	case 'edit':
			case 'view':
				if (current_window.action == 'edit') {
					current_window.setTitle(this.wintitle + lang['_redaktirovanie']);
					current_window.enableEdit(true);
				} else {
					current_window.setTitle(this.wintitle + lang['_prosmotr']);
					current_window.enableEdit(false);
				}
				
				if (current_window.disableDidDate) {
					base_form.findField('EvnUslugaDispDop_setDate').disable();
				}
				
				base_form.findField('UslugaComplex_id').disable();
				if (base_form.findField('UslugaComplex_id').getStore().getCount() > 1) {
					if (current_window.action != 'view') {
						base_form.findField('UslugaComplex_id').enable();
					}
				}
				// устанавливаем врача
				current_window.findById('EUDSCEW_MedPersonalCombo').getStore().findBy(function(record) {
					if ( record.get('MedPersonal_id') == med_personal_id )
					{
						current_window.findById('EUDSCEW_MedPersonalCombo').setValue(record.get('MedStaffFact_id'));
						return true;
					}
				});
				
				loadMask.hide();
				
				// если уже было сохранено надо грузить с сервера
				if (!Ext.isEmpty(base_form.findField('EvnUslugaDispDop_id').getValue())) {
					loadMask.show();
					base_form.load({
						failure: function() {
							loadMask.hide();
							sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_zagruzke_dannyih_formyi'], function() { current_window.hide(); } );
						}.createDelegate(this),
						params: {
							EvnUslugaDispDop_id: base_form.findField('EvnUslugaDispDop_id').getValue(),
							archiveRecord: current_window.archiveRecord
						},
						success: function(result_form, action) {
							loadMask.hide();
							current_window.loadUslugaComplexCombo();

							var responseObj = new Object();

							if ( action && action.response && action.response.responseText ) {
								responseObj = Ext.util.JSON.decode(action.response.responseText);

								if ( responseObj.length > 0 ) {
									responseObj = responseObj[0];
								}
							}
							
							current_window.enableEdit(responseObj.EvnUsluga_IsAPP == 1);
							current_window.EvnUsluga_IsAPP = responseObj.EvnUsluga_IsAPP;

							var
								Diag_id = responseObj.Diag_id,
								MedPersonal_id = responseObj.MedPersonal_id,
								LpuSection_id = responseObj.LpuSection_id;

							var didDate = base_form.findField('EvnUslugaDispDop_setDate').getValue();
							var didTime = base_form.findField('EvnUslugaDispDop_setTime').getValue();
							
							if (!Ext.isEmpty(base_form.findField('Lpu_uid').getValue()) || base_form.findField('Lpu_id').getValue() != getGlobalOptions().lpu_id) {
								base_form.findField('MedStaffFact_id').getStore().load({
									callback: function() {
										if (!Ext.isEmpty(base_form.findField('MedPersonal_id').getValue())) {
											var index = base_form.findField('MedStaffFact_id').getStore().findBy(function(rec) {
												return (rec.get('MedStaffFact_id') == base_form.findField('MedStaffFact_id').getValue());
											});
											
											if ( index == -1 ) {
												index = base_form.findField('MedStaffFact_id').getStore().findBy(function(rec) {
													return (rec.get('MedPersonal_id') == MedPersonal_id);
												});
											}
											
											if ( index >= 0 ) {
												base_form.findField('MedStaffFact_id').setValue(base_form.findField('MedStaffFact_id').getStore().getAt(index).get('MedStaffFact_id'));
											}
										}
									},
									params: {
										mode: 'combo',
										Lpu_id: !Ext.isEmpty(base_form.findField('Lpu_uid').getValue())?base_form.findField('Lpu_uid').getValue():base_form.findField('Lpu_id').getValue()
									}
								});
							}

							var diag_combo = base_form.findField('Diag_id');
							if ( !Ext.isEmpty(Diag_id) ) {
								diag_combo.getStore().load({
									callback: function() {
										diag_combo.getStore().each(function(record) {
											if ( record.get('Diag_id') == Diag_id ) {
												diag_combo.fireEvent('select', diag_combo, record, 0);
												diag_combo.onChange();
											}
										});
									},
									params: { where: "where DiagLevel_id = 4 and Diag_id = " + Diag_id }
								});
							}

							this.loadedParams = responseObj;
							base_form.findField('EvnUslugaDispDop_setDate').fireEvent('change', base_form.findField('EvnUslugaDispDop_setDate'), base_form.findField('EvnUslugaDispDop_setDate').getValue());
						}.createDelegate(this),
						url: '/?c=EvnPLDispScreenChild&m=loadEvnUslugaDispDop'
					});
				}
				else {
					current_window.EvnUsluga_IsAPP = 1;
					current_window.loadUslugaComplexCombo();
					setCurrentDateTime({
						callback: function() {
							base_form.findField('EvnUslugaDispDop_setDate').fireEvent('change', base_form.findField('EvnUslugaDispDop_setDate'), base_form.findField('EvnUslugaDispDop_setDate').getValue());
						},
						dateField: base_form.findField('EvnUslugaDispDop_setDate'),
						loadMask: false,
						setDate: true,
						//setDateMaxValue: true,
						setDateMinValue: false,
						setTime: true,
						timeField: base_form.findField('EvnUslugaDispDop_setTime'),
						windowId: this.id
					});
				}
			break;
			
			default:
				current_window.hide();
        }
/*
		//В рамках задачи https://redmine.swan.perm.ru/issues/23822 определим последний компонент в подпункте "Результат", чтобы с него осуществить переход на кнопку "Сохранить"
		var items = this.findById('EUDSCEW_ResultsPanel').items.items;
		for (var key in items) {
			var obj = items[key];
			if(!obj.hidden && obj.xtype){
				var last_obj = obj;
			}
		}
		if(last_obj)
			last_obj.addListener('keydown',function(inp,e){
				if(e.getKey() == Ext.EventObject.TAB){
					e.stopEvent();
					Ext.getCmp('EUDSCEW_SaveButton').focus(true, 200);
				}
			});
*/
    },
    width: 700
});