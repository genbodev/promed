/**
* swEvnPrescrEditWindow - окно редактирования/добавления назначения.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Prescription
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Stas Bykov aka Savage (savage@swan.perm.ru)
* @version      0.001-05.10.2011
* @comment      Префикс для id компонентов EPREF (EvnPrescrEditForm)
*/
/*NO PARSE JSON*/

sw.Promed.swEvnPrescrEditWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swEvnPrescrEditWindow',
	objectSrc: '/jscore/Forms/Prescription/swEvnPrescrEditWindow.js',

	addTime: function() {
		var base_form = this.FormPanel.getForm();
		var time_grid = this.findById('EPREF_TimeGrid').getGrid();

		if ( !base_form.findField('addTime').getValue() ) {
			return false;
		}

		var addTime = base_form.findField('addTime').getValue();

		var index = time_grid.getStore().findBy(function(rec) {
			if ( rec.get('addTime') == addTime ) {
				return true;
			}
			else {
				return false;
			}
		});

		if ( index == -1 ) {
			// Добавляем время
			time_grid.getStore().loadData([{ id: swGenTempId(time_grid.getStore()), addTime: addTime }], true);
			time_grid.getStore().sort('addTime', 'ASC');
		}
	},
	autoHeight: true,
	begDate: null,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	collapsible: false,
	deleteDrug: function() {
		var drug_grid = this.findById('EPREF_DrugGrid').getGrid();

		if ( !drug_grid.getSelectionModel().getSelected() || !drug_grid.getSelectionModel().getSelected().get('EvnPrescrTreatDrug_id') ) {
			return false;
		}

		drug_grid.getStore().remove(drug_grid.getSelectionModel().getSelected());

		if ( drug_grid.getStore().getCount() > 0 ) {
			drug_grid.getSelectionModel().selectFirstRow();
		}
	},
	deleteTime: function() {
		var time_grid = this.findById('EPREF_TimeGrid').getGrid();

		if ( !time_grid.getSelectionModel().getSelected() || !time_grid.getSelectionModel().getSelected().get('id') ) {
			return false;
		}

		time_grid.getStore().remove(time_grid.getSelectionModel().getSelected());

		if ( time_grid.getStore().getCount() > 0 ) {
			time_grid.getSelectionModel().selectFirstRow();
		}
	},
	doSave: function(options) {
		// options @Object

		if ( this.formStatus == 'save' ) {
			return false;
		}

		if ( typeof options != 'object' ) {
			options = new Object();
		}

		this.formStatus = 'save';

		var base_form = this.FormPanel.getForm();

		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					this.formStatus = 'edit';
					this.FormPanel.getFirstInvalidEl().focus(true);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		var observParamTypeList = new Array();
		var observTimeTypeList = new Array();
		var params = new Object();
		// var params = base_form.getValues();

		var drugList = getStoreRecords(this.findById('EPREF_DrugGrid').getGrid().getStore(), {
			exceptionFields: [ 'Drug_Name', 'EvnPrescrTreatDrug_Kolvo_Show' ]
		});

		var timeList = getStoreRecords(this.findById('EPREF_TimeGrid').getGrid().getStore(), {
			exceptionFields: [ 'id' ]
		});

		if ( base_form.findField('PrescriptionType_id').getValue().toString() == '10' ) {
			this.findById('EPREF_ObservParamTypeGrid').getGrid().getStore().each(function(rec) {
				if ( rec.get('ObservParamType_IsSelected') == true ) {
					observParamTypeList.push(rec.get('ObservParamType_id'));
				}
			});

			if ( base_form.findField('ObservTimeType_Morning').getValue() == true ) {
				observTimeTypeList.push(1);
			}

			if ( base_form.findField('ObservTimeType_Day').getValue() == true ) {
				observTimeTypeList.push(2);
			}

			if ( base_form.findField('ObservTimeType_Evening').getValue() == true ) {
				observTimeTypeList.push(3);
			}

			if ( observParamTypeList.length == 0 ) {
				sw.swMsg.alert(lang['oshibka'], lang['ne_vyibran_ni_odin_parametr_nablyudeniya'], function() {
					this.formStatus = 'edit';
				}.createDelegate(this));
				return false;
			}

			if ( observTimeTypeList.length == 0 ) {
				sw.swMsg.alert(lang['oshibka'], lang['ne_vyibrano_vremya_nablyudeniy'], function() {
					this.formStatus = 'edit';
				}.createDelegate(this));
				return false;
			}
		}

		params.drugList = Ext.util.JSON.encode(drugList);
		params.observParamTypeList = Ext.util.JSON.encode(observParamTypeList);
		params.observTimeTypeList = Ext.util.JSON.encode(observTimeTypeList);
		params.timeList = Ext.util.JSON.encode(timeList);

		if( base_form.findField('PrescriptionType_id').disabled ) {
			params.PrescriptionType_id = base_form.findField('PrescriptionType_id').getValue();
		}

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT_SAVE });
		loadMask.show();

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
			}.createDelegate(this),
			params: params,
			success: function(result_form, action) {
				this.formStatus = 'edit';
				loadMask.hide();

				if ( action.result ) {
					var data = new Object();
					var evnPrescrData = new Object();

					evnPrescrData.EvnPrescr_id = action.result.EvnPrescr_id;

					data.evnPrescrData = evnPrescrData;

					this.callback(data);
					// this.hide();

					this.findById('EPREF_DrugGrid').getGrid().getStore().removeAll();

					if ( base_form.findField('PrescriptionType_id').getValue().toString().inlist([ '1', '2' ]) && !base_form.findField('PrescriptionType_id').getValue().toString().inlist(this.exceptionTypes) ) {
						this.exceptionTypes.push(base_form.findField('PrescriptionType_id').getValue().toString());
					}

					base_form.findField('PrescriptionType_id').getStore().clearFilter();

					base_form.findField('PrescriptionType_id').getStore().filterBy(function(rec) {
						if ( rec.get('PrescriptionType_id').toString().inlist(this.exceptionTypes) ) {
							return false;
						}
						else {
							return true;
						}
					}.createDelegate(this));

					base_form.findField('PrescriptionType_id').clearValue();
					base_form.findField('PrescriptionType_id').fireEvent('change', base_form.findField('PrescriptionType_id'));
					base_form.clearInvalid();

					base_form.findField('PrescriptionType_id').focus();
				}
				else {
					sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_2]']);
				}
			}.createDelegate(this)
		});
	},
	draggable: true,
	formStatus: 'edit',
	// height: 650,
	id: 'EvnPrescrEditWindow',
	initComponent: function() {
		this.FormPanel = new Ext.form.FormPanel({
			autoScroll: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			border: false,
			frame: false,
			id: 'EvnPrescrEditForm',
			labelAlign: 'right',
			labelWidth: 160,
			region: 'center',
			url: '/?c=EvnPrescr&m=saveEvnPrescr',

			items: [{
				name: 'EvnPrescr_id', // Идентификатор назначения
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'EvnPrescr_pid', // Идентификатор родительского события
				value: -1,
				xtype: 'hidden'
			}, {
				name: 'PersonEvn_id', // Идентификатор состояния человека
				value: -1,
				xtype: 'hidden'
			}, {
				name: 'Server_id', // Идентификатор сервера
				value: -1,
				xtype: 'hidden'
			}, {
				allowBlank: false,
				autoLoad: false,
				typeCode: 'int',
				comboSubject: 'PrescriptionType',
				fieldLabel: lang['tip_naznacheniya'],
				hiddenName: 'PrescriptionType_id',
				lastQuery: '',
				listeners: {
					'change': function(combo, newValue, oldValue) {
						combo.fireEvent('select', combo, combo.getStore().getById(newValue));
					}.createDelegate(this),
					'select': function(combo, record) {
						var base_form = this.FormPanel.getForm();

						var formValues = base_form.getValues();

						this.findById('btnAddTime').hide();
						this.findById('EPREF_DrugGridPanel').hide();
						this.findById('EPREF_ObservParamTypeGridPanel').hide();
						this.findById('EPREF_ObservTimeTypeGridPanel').hide();
						this.findById('EPREF_TimeGridPanel').hide();
						base_form.findField('addTime').setContainerVisible(false);
						base_form.findField('EvnPrescr_setDate').setAllowBlank(true);
						base_form.findField('EvnPrescr_setDate').setContainerVisible(false);
						base_form.findField('EvnPrescr_setDate_Range').setAllowBlank(true);
						base_form.findField('EvnPrescr_setDate_Range').setContainerVisible(false);
						base_form.findField('EvnPrescr_setTime').setAllowBlank(true);
						base_form.findField('EvnPrescr_setTime').setContainerVisible(false);
						base_form.findField('EvnPrescrTreat_CountInDay').setAllowBlank(true);
						base_form.findField('EvnPrescrTreat_CountInDay').setRawValue('');
						base_form.findField('EvnPrescrTreat_CountInDay').setContainerVisible(false);
						base_form.findField('PrescriptionDietType_id').clearValue();
						base_form.findField('PrescriptionDietType_id').setContainerVisible(false);
						base_form.findField('PrescriptionIntroType_id').setAllowBlank(true);
						base_form.findField('PrescriptionIntroType_id').clearValue();
						base_form.findField('PrescriptionIntroType_id').setContainerVisible(false);
						base_form.findField('PrescriptionRegimeType_id').clearValue();
						base_form.findField('PrescriptionRegimeType_id').setContainerVisible(false);
						base_form.findField('PrescriptionTreatType_id').setAllowBlank(true);
						base_form.findField('PrescriptionTreatType_id').clearValue();
						base_form.findField('PrescriptionTreatType_id').setContainerVisible(false);

						if ( !record || !record.get('PrescriptionType_id') ) {
							base_form.findField('EvnPrescr_IsCito').setAllowBlank(true);
							base_form.findField('EvnPrescr_IsCito').clearValue();
							base_form.findField('EvnPrescr_IsCito').setContainerVisible(false);
							base_form.findField('LpuSectionProfile_id').setAllowBlank(true);
							base_form.findField('LpuSectionProfile_id').clearValue();
							base_form.findField('LpuSectionProfile_id').setContainerVisible(false);
							base_form.findField('Usluga_id').setAllowBlank(true);
							base_form.findField('Usluga_id').clearValue();
							base_form.findField('Usluga_id').setContainerVisible(false);

							base_form.findField('EvnPrescr_IsCito').fireEvent('change', base_form.findField('EvnPrescr_IsCito'), null);

							this.syncSize();
							this.center();

							return false;
						}

						if ( record.get('PrescriptionType_Code').toString().inlist([ '3', '4', '5', '6', '7' ]) ) {
							base_form.findField('EvnPrescr_IsCito').setAllowBlank(false);
							base_form.findField('EvnPrescr_IsCito').setContainerVisible(true);
						}
						else {
							base_form.findField('EvnPrescr_IsCito').setAllowBlank(true);
							base_form.findField('EvnPrescr_IsCito').clearValue();
							base_form.findField('EvnPrescr_IsCito').setContainerVisible(false);
						}

						if ( record.get('PrescriptionType_Code').toString().inlist([ '4' ]) ) {
							base_form.findField('LpuSectionProfile_id').setAllowBlank(false);
							base_form.findField('LpuSectionProfile_id').setContainerVisible(true);
						}
						else {
							base_form.findField('LpuSectionProfile_id').setAllowBlank(true);
							base_form.findField('LpuSectionProfile_id').clearValue();
							base_form.findField('LpuSectionProfile_id').setContainerVisible(false);
						}

						if ( record.get('PrescriptionType_Code').toString().inlist([ '3', '6', '7' ]) ) {
							base_form.findField('Usluga_id').setAllowBlank(false);
							base_form.findField('Usluga_id').setContainerVisible(true);
						}
						else {
							base_form.findField('Usluga_id').setAllowBlank(true);
							base_form.findField('Usluga_id').clearValue();
							base_form.findField('Usluga_id').setContainerVisible(false);
						}

						switch ( Number(record.get('PrescriptionType_Code')) ) {
							case 1:
								base_form.findField('EvnPrescr_setDate_Range').setContainerVisible(true);
								base_form.findField('PrescriptionRegimeType_id').setContainerVisible(true);

								if ( formValues.PrescriptionRegimeType_id ) {
									base_form.findField('PrescriptionRegimeType_id').setValue(formValues.PrescriptionRegimeType_id);
								}
							break;

							case 2:
								base_form.findField('PrescriptionDietType_id').setContainerVisible(true);
								base_form.findField('EvnPrescr_setDate_Range').setContainerVisible(true);

								if ( formValues.PrescriptionDietType_id ) {
									base_form.findField('PrescriptionDietType_id').setValue(formValues.PrescriptionDietType_id);
								}
							break;

							case 3:
							case 4:
							case 6:
							case 7:
								if ( record.get('PrescriptionType_Code') == 4 ) {
									if ( formValues.LpuSectionProfile_id ) {
										base_form.findField('LpuSectionProfile_id').setValue(formValues.LpuSectionProfile_id);
									}
								}

								if ( formValues.EvnPrescr_IsCito ) {
									base_form.findField('EvnPrescr_IsCito').setValue(formValues.EvnPrescr_IsCito);
								}
								else {
									base_form.findField('EvnPrescr_IsCito').setValue(1);
								}

								base_form.findField('EvnPrescr_IsCito').fireEvent('change', base_form.findField('EvnPrescr_IsCito'), base_form.findField('EvnPrescr_IsCito').getValue());
							break;

							case 5:
								this.findById('EPREF_DrugGridPanel').show();

								base_form.findField('PrescriptionIntroType_id').setAllowBlank(false);
								base_form.findField('PrescriptionIntroType_id').setContainerVisible(true);

								if ( formValues.EvnPrescr_IsCito ) {
									base_form.findField('EvnPrescr_IsCito').setValue(formValues.EvnPrescr_IsCito);
								}
								else {
									base_form.findField('EvnPrescr_IsCito').setValue(1);
								}

								if ( formValues.PrescriptionIntroType_id ) {
									base_form.findField('PrescriptionIntroType_id').setValue(formValues.PrescriptionIntroType_id);
								}

								base_form.findField('EvnPrescr_IsCito').fireEvent('change', base_form.findField('EvnPrescr_IsCito'), base_form.findField('EvnPrescr_IsCito').getValue());
							break;

							case 10:
								this.findById('EPREF_ObservParamTypeGridPanel').show();
								this.findById('EPREF_ObservTimeTypeGridPanel').show();
								// Открыть филдсет со списком времени измерения параметров
								base_form.findField('EvnPrescr_setDate_Range').setAllowBlank(false);
								base_form.findField('EvnPrescr_setDate_Range').setContainerVisible(true);
							break;
						}

						this.syncSize();
						this.center();
					}.createDelegate(this),
					'render': function(combo) {
						combo.getStore().load({
							params: {
								where: 'where PrescriptionType_id in (1, 2, 3, 4, 5, 6, 7, 10)'
							}
						});
					}.createDelegate(this)
				},
				// tabIndex: TABINDEX_EPREF + 1,
				width: 300,
				xtype: 'swcommonsprcombo'
			}, {
				allowBlank: true,
				comboSubject: 'LpuSectionProfile',
				fieldLabel: lang['profil'],
				hiddenName: 'LpuSectionProfile_id',
				listeners: {
					'change': function(combo, newValue, oldValue) {
						combo.fireEvent('select', combo, combo.getStore().getById(newValue));
					}.createDelegate(this),
					'select': function(combo, record) {
						var base_form = this.FormPanel.getForm();
					}.createDelegate(this),
					'render': function(combo) {
						combo.getStore().load();
					}.createDelegate(this)
				},
				listWidth: 600,
				// tabIndex: TABINDEX_EPREF + 1,
				width: 300,
				xtype: 'swcommonsprcombo'
			}, {
				fieldLabel: lang['usluga'],
				hiddenName: 'Usluga_id',
				listWidth: 600,
				// tabIndex: TABINDEX_EUCOMEF + 9,
				width: 500,
				xtype: 'swuslugacombo'
			}, {
				comboSubject: 'YesNo',
				fieldLabel: lang['srochnost_cito'],
				hiddenName: 'EvnPrescr_IsCito',
				listeners: {
					'change': function(combo, newValue, oldValue) {
						combo.fireEvent('select', combo, combo.getStore().getById(newValue));
					}.createDelegate(this),
					'select': function(combo, record) {
						var base_form = this.FormPanel.getForm();

						var formValues = base_form.getValues();

						this.findById('btnAddTime').hide();
						this.findById('EPREF_TimeGridPanel').hide();
						base_form.findField('addTime').setContainerVisible(false);
						base_form.findField('EvnPrescr_setDate').setAllowBlank(true);
						base_form.findField('EvnPrescr_setDate').setRawValue('');
						base_form.findField('EvnPrescr_setDate').setContainerVisible(false);
						base_form.findField('EvnPrescr_setDate_Range').setAllowBlank(true);
						base_form.findField('EvnPrescr_setDate_Range').setRawValue('');
						base_form.findField('EvnPrescr_setDate_Range').setContainerVisible(false);
						base_form.findField('EvnPrescr_setTime').setAllowBlank(true);
						base_form.findField('EvnPrescr_setTime').setRawValue('');
						base_form.findField('EvnPrescr_setTime').setContainerVisible(false);
						base_form.findField('EvnPrescrTreat_CountInDay').setAllowBlank(true);
						base_form.findField('EvnPrescrTreat_CountInDay').setRawValue('');
						base_form.findField('EvnPrescrTreat_CountInDay').setContainerVisible(false);
						base_form.findField('PrescriptionTreatType_id').clearValue();
						base_form.findField('PrescriptionTreatType_id').setAllowBlank(true);
						base_form.findField('PrescriptionTreatType_id').setContainerVisible(false);

						if ( !record || !record.get('YesNo_id') ) {
							this.syncSize();
							this.center();
							return false;
						}

						if ( Number(record.get('YesNo_Code')) == 1 ) {
							this.syncSize();
							this.center();
							return false;
						}

						var PrescriptionType_Code = 0;
						var PrescriptionType_id = base_form.findField('PrescriptionType_id').getValue();

						if ( !PrescriptionType_id ) {
							this.syncSize();
							this.center();
							return false;
						}

						var index = base_form.findField('PrescriptionType_id').getStore().findBy(function(rec) {
							if ( rec.get('PrescriptionType_id') == PrescriptionType_id ) {
								return true;
							}
							else {
								return false;
							}
						});

						if ( index >= 0 ) {
							PrescriptionType_Code = Number(base_form.findField('PrescriptionType_id').getStore().getAt(index).get('PrescriptionType_Code'));
						}

						switch ( PrescriptionType_Code ) {
							case 1:
							case 2:
								base_form.findField('EvnPrescr_setDate_Range').setContainerVisible(true);
							break;

							case 3:
							case 4:
							case 7:
								base_form.findField('EvnPrescr_setDate').setAllowBlank(false);
								base_form.findField('EvnPrescr_setDate').setContainerVisible(true);
								base_form.findField('EvnPrescr_setTime').setAllowBlank(false);
								base_form.findField('EvnPrescr_setTime').setContainerVisible(true);

								if ( formValues.EvnPrescr_setDate ) {
									base_form.findField('EvnPrescr_setDate').setValue(formValues.EvnPrescr_setDate);
								}

								if ( formValues.EvnPrescr_setTime ) {
									base_form.findField('EvnPrescr_setTime').setValue(formValues.EvnPrescr_setTime);
								}
							break;

							case 5:
								base_form.findField('EvnPrescr_setDate_Range').setContainerVisible(true);

								base_form.findField('PrescriptionTreatType_id').setAllowBlank(false);
								base_form.findField('PrescriptionTreatType_id').setContainerVisible(true);

								if ( formValues.PrescriptionTreatType_id ) {
									base_form.findField('PrescriptionTreatType_id').setValue(formValues.PrescriptionTreatType_id);
								}
							break;

							case 6:
								this.findById('btnAddTime').show();
								this.findById('EPREF_TimeGridPanel').show();
								base_form.findField('addTime').setContainerVisible(true);

								base_form.findField('EvnPrescr_setDate_Range').setContainerVisible(true);
							break;
						}
					}.createDelegate(this),
					'render': function(combo) {
						combo.getStore().load();
					}.createDelegate(this)
				},
				// tabIndex: TABINDEX_EPREF + 2,
				width: 100,
				xtype: 'swcommonsprcombo'
			}, {
				border: false,
				layout: 'column',
				items: [{
					border: false,
					layout: 'form',
					items: [{
						fieldLabel: lang['data'],
						format: 'd.m.Y',
						listeners: {
							'change': function(field, newValue, oldValue) {
								//
							}.createDelegate(this)
						},
						name: 'EvnPrescr_setDate',
						plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
						selectOnFocus: true,
						// tabIndex: TABINDEX_EPSEF + 4,
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
						name: 'EvnPrescr_setTime',
						onTriggerClick: function() {
							var base_form = this.FormPanel.getForm();
							var time_field = base_form.findField('EvnPrescr_setTime');

							if ( time_field.disabled ) {
								return false;
							}

							setCurrentDateTime({
								dateField: base_form.findField('EvnPrescr_setDate'),
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
						// tabIndex: TABINDEX_EPSEF + 5,
						validateOnBlur: false,
						width: 60,
						xtype: 'swtimefield'
					}]
				}]
			}, {
				allowBlank: true,
				comboSubject: 'PrescriptionRegimeType',
				fieldLabel: lang['tip_rejima'],
				hiddenName: 'PrescriptionRegimeType_id',
				listeners: {
					'change': function(combo, newValue, oldValue) {
						combo.fireEvent('select', combo, combo.getStore().getById(newValue));
					}.createDelegate(this),
					'select': function(combo, record) {
						var base_form = this.FormPanel.getForm();
					}.createDelegate(this),
					'render': function(combo) {
						combo.getStore().load();
					}.createDelegate(this)
				},
				// tabIndex: TABINDEX_EPREF + 1,
				width: 300,
				xtype: 'swcommonsprcombo'
			}, {
				allowBlank: true,
				comboSubject: 'PrescriptionIntroType',
				fieldLabel: lang['metod_vvedeniya'],
				hiddenName: 'PrescriptionIntroType_id',
				listeners: {
					'render': function(combo) {
						combo.getStore().load();
					}.createDelegate(this)
				},
				// tabIndex: TABINDEX_EPREF + 1,
				width: 300,
				xtype: 'swcommonsprcombo'
			}, {
				allowBlank: true,
				autoLoad: false,
				comboSubject: 'PrescriptionTreatType',
				fieldLabel: lang['vid_naznacheniya'],
				hiddenName: 'PrescriptionTreatType_id',
				listeners: {
					'change': function(combo, newValue, oldValue) {
						combo.fireEvent('select', combo, combo.getStore().getById(newValue));
					}.createDelegate(this),
					'select': function(combo, record) {
						var base_form = this.FormPanel.getForm();

						var formValues = base_form.getValues();

						this.findById('btnAddTime').hide();
						this.findById('EPREF_TimeGridPanel').hide();
						base_form.findField('addTime').setContainerVisible(false);
						base_form.findField('EvnPrescrTreat_CountInDay').setAllowBlank(true);
						base_form.findField('EvnPrescrTreat_CountInDay').setRawValue('');
						base_form.findField('EvnPrescrTreat_CountInDay').setContainerVisible(false);

						if ( !record || !record.get('PrescriptionTreatType_id') ) {
							return false;
						}

						switch ( Number(record.get('PrescriptionTreatType_Code')) ) {
/*
							case 1:
								this.findById('btnAddTime').show();
								this.findById('EPREF_TimeGridPanel').show();
								base_form.findField('addTime').setContainerVisible(true);
							break;
*/
							case 2:
								base_form.findField('EvnPrescrTreat_CountInDay').setAllowBlank(false);
								base_form.findField('EvnPrescrTreat_CountInDay').setContainerVisible(true);

								if ( formValues.EvnPrescrTreat_CountInDay ) {
									base_form.findField('EvnPrescrTreat_CountInDay').setValue(formValues.EvnPrescrTreat_CountInDay);
								}
							break;
						}
					}.createDelegate(this),
					'render': function(combo) {
						combo.getStore().load({
							params: {
								where: "where PrescriptionTreatType_Code in (2, 3)"
							}
						});
					}.createDelegate(this)
				},
				// tabIndex: TABINDEX_EPREF + 1,
				width: 300,
				xtype: 'swcommonsprcombo'
			}, {
				allowBlank: false,
				allowDecimals: false,
				allowNegative: false,
				fieldLabel: lang['kolichestvo_v_den'],
				minValue: 1,
				name: 'EvnPrescrTreat_CountInDay',
				// tabIndex: TABINDEX_EDEW + 12,
				width: 100,
				xtype: 'numberfield'
			}, {
				allowBlank: true,
				comboSubject: 'PrescriptionDietType',
				fieldLabel: lang['tip_dietyi'],
				hiddenName: 'PrescriptionDietType_id',
				listeners: {
					'change': function(combo, newValue, oldValue) {
						combo.fireEvent('select', combo, combo.getStore().getById(newValue));
					}.createDelegate(this),
					'select': function(combo, record) {
						var base_form = this.FormPanel.getForm();
					}.createDelegate(this),
					'render': function(combo) {
						combo.getStore().load();
					}.createDelegate(this)
				},
				listWidth: 600,
				// tabIndex: TABINDEX_EPREF + 1,
				width: 300,
				xtype: 'swcommonsprcombo'
			}, {
				fieldLabel: lang['prodoljitelnost_kursa'],
				name: 'EvnPrescr_setDate_Range',
				plugins: [
					new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)
				],
				// tabIndex: TABINDEX_EPSSW + 73,
				width: 170,
				xtype: 'daterangefield'
			}, {
				border: false,
				layout: 'column',

				items: [{
					border: false,
					layout: 'form',
					width: 220,

					items: [{
						fieldLabel: lang['vremya'],
						name: 'addTime',
						plugins: [ new Ext.ux.InputTextMask('99:99', true) ],
						// tabIndex: TABINDEX_EPSEF + 5,
						validateOnBlur: false,
						width: 50,
						xtype: 'textfield'
					}]
				}, {
					border: false,
					layout: 'form',

					items: [{
						handler: function() {
							this.addTime();
						}.createDelegate(this),
						iconCls: 'add16',
						id: 'btnAddTime',
						text: '',
						xtype: 'button'
					}]
				}]
			}, {
				border: false,
				height: 150,
				id: 'EPREF_TimeGridPanel',
				layout: 'border',
				style: 'margin-left: 165px; margin-right: 0.5em; padding-bottom: 4px;',
				width: 200,

				items: [ new sw.Promed.ViewFrame({
					actions: [
						{ name: 'action_add', disabled: true, hidden: true },
						{ name: 'action_edit', disabled: true, hidden: true },
						{ name: 'action_view', disabled: true, hidden: true },
						{ name: 'action_delete', handler: function() { this.deleteTime(); }.createDelegate(this), text: lang['udalit'], tooltip: lang['udalit_vremya_iz_spiska'] },
						{ name: 'action_refresh', disabled: true, hidden: true },
						{ name: 'action_print', disabled: true, hidden: true }
					],
					autoLoadData: false,
					border: true,
					// forceFit: true,
					id: 'EPREF_TimeGrid',
					region: 'center',
					stringfields: [
						{ name: 'id', type: 'int', header: 'ID', key: true },
						{ name: 'addTime', header: lang['vremya'], type: 'string', width: 150 }
					],
					style: 'margin-bottom: 0.5em;',
					title: lang['vremya'],
					toolbar: true
				})]
			}, {
				border: false,
				height: 150,
				id: 'EPREF_DrugGridPanel',
				layout: 'border',
				style: 'margin-left: 165px; margin-right: 0.5em; padding-bottom: 4px;',
				width: 500,

				items: [ new sw.Promed.ViewFrame({
					actions: [
						{ name: 'action_add', handler: function() { this.openEvnPrescrTreatDrugEditWindow('add'); }.createDelegate(this) },
						{ name: 'action_edit', handler: function() { this.openEvnPrescrTreatDrugEditWindow('edit'); }.createDelegate(this) },
						{ name: 'action_view', disabled: true, hidden: true },
						{ name: 'action_delete', handler: function() { this.deleteDrug(); }.createDelegate(this), tooltip: lang['udalit_medikament_iz_spiska'] },
						{ name: 'action_refresh', disabled: true, hidden: true },
						{ name: 'action_print', disabled: true, hidden: true }
					],
					autoLoadData: false,
					border: true,
					id: 'EPREF_DrugGrid',
					region: 'center',
					stringfields: [
						{ name: 'EvnPrescrTreatDrug_id', type: 'int', header: 'ID', key: true },
						{ name: 'Drug_id', type: 'int', hidden: true },
						{ name: 'DrugPrepFas_id', type: 'int', hidden: true },
						{ name: 'EvnPrescrTreatDrug_KolvoEd', type: 'float', hidden: true },
						{ name: 'EvnPrescrTreatDrug_Kolvo_Show', type: 'float', hidden: true },
						{ name: 'Drug_Name', header: lang['medikament'], type: 'string', id: 'autoexpand' },
						{ name: 'EvnPrescrTreatDrug_Kolvo', header: lang['kolichestvo'], type: 'float', width: 150 }
					],
					style: 'margin-bottom: 0.5em;',
					title: lang['medikamentyi'],
					toolbar: true
				})]
			}, {
				autoHeight: true,
				id: 'EPREF_ObservTimeTypeGridPanel',
				labelWidth: 1,
				style: 'margin-left: 165px; padding: 0px;',
				title: lang['vremya_provedeniya_nablyudeniy'],
				width: 500,
				xtype: 'fieldset',

				items: [{
					boxLabel: lang['utro'],
					checked: true,
					fieldLabel: '',
					labelSeparator: '',
					// tabIndex: TABINDEX_EPSSW + 69,
					name: 'ObservTimeType_Morning',
					xtype: 'checkbox'
				}, {
					boxLabel: lang['den'],
					checked: true,
					fieldLabel: '',
					labelSeparator: '',
					// tabIndex: TABINDEX_EPSSW + 69,
					name: 'ObservTimeType_Day',
					xtype: 'checkbox'
				}, {
					boxLabel: lang['vecher'],
					checked: true,
					fieldLabel: '',
					labelSeparator: '',
					// tabIndex: TABINDEX_EPSSW + 69,
					name: 'ObservTimeType_Evening',
					xtype: 'checkbox'
				}]
			}, {
				border: false,
				height: 250,
				id: 'EPREF_ObservParamTypeGridPanel',
				layout: 'border',
				style: 'margin-left: 165px; margin-right: 0.5em; padding-bottom: 4px;',
				width: 500,

				items: [ new sw.Promed.ViewFrame({
					actions: [
						{ name: 'action_add', handler: function() { this.findById('EPREF_ObservParamTypeGrid').checkAll(true); }.createDelegate(this), text: lang['vyibrat_vse'] },
						{ name: 'action_edit', disabled: true, hidden: true },
						{ name: 'action_view', disabled: true, hidden: true },
						{ name: 'action_delete', handler: function() { this.findById('EPREF_ObservParamTypeGrid').checkAll(false); }.createDelegate(this), text: lang['ubrat_vse'] },
						{ name: 'action_print', disabled: true, hidden: true }
					],
					autoLoadData: true,
					border: true,
					checkAll: function(enable) {
						this.getGrid().getStore().each(function(rec) {
							rec.set('ObservParamType_IsSelected', enable);
							rec.commit();
						});
					},
					dataUrl: '/?c=EvnPrescr&m=getObservParamTypeList',
					editing: true,
					id: 'EPREF_ObservParamTypeGrid',
					object: 'ObservParamType',
					onAfterEditSelf: function(o) {
						o.record.commit();
					},
					onDblClick: function(grid, rowIdx, object) {
						var record = grid.getStore().getAt(rowIdx);

						if ( record ) {
							record.set('ObservParamType_IsSelected', !record.get('ObservParamType_IsSelected'));
							record.commit();
						}
					},
					onLoadData: function (result) {
						this.checkAll(true);
					},
					region: 'center',
					stringfields: [
						{ name: 'ObservParamType_id', type: 'int', header: 'ID', key: true },
						{ name: 'ObservParamType_Name', header: lang['parametr'], type: 'string', id: 'autoexpand' },
						/*{ name: 'ObservParamType_IsSelected', header: lang['vyibran'], editor: new Ext.form.Checkbox({
							listeners: {
								'check': function(checkbox, value) {
									// selected_record.commit();
								}.createDelegate(this)
							}
						}), type: 'checkcolumn' }
						*/
						{ name: 'ObservParamType_IsSelected', header: lang['vyibran'], type: 'checkcolumnedit' }
					],
					style: 'margin-bottom: 0.5em;',
					title: lang['parametryi_nablyudeniya'],
					toolbar: true
				})]
			}, {
				fieldLabel: lang['kommentariy'],
				height: 80,
				name: 'EvnPrescr_Descr',
				// tabIndex: TABINDEX_EHPEF + 14,
				width: 500,
				xtype: 'textarea'
			}]
		});
/*
		this.PersonInfo = new sw.Promed.PersonInformationPanelShort({
			id: 'EStEF_PersonInformationFrame',
			region: 'north'
		});
*/
		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.doSave();
				}.createDelegate(this),
				iconCls: 'save16',
				onShiftTabAction: function () {
					// var base_form = this.FormPanel.getForm();
				}.createDelegate(this),
				onTabAction: function () {
					// this.buttons[1].focus();
				}.createDelegate(this),
				// tabIndex: TABINDEX_EPREF + 34,
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
					// this.buttons[1].focus();
				}.createDelegate(this),
				onTabAction: function () {
					// var base_form = this.FormPanel.getForm();
				}.createDelegate(this),
				// tabIndex: TABINDEX_EPREF + 36,
				text: BTN_FRMCANCEL
			}],
			items: [
				// this.PersonInfo,
				this.FormPanel
			],
			layout: 'form'
		});

		sw.Promed.swEvnPrescrEditWindow.superclass.initComponent.apply(this, arguments);
	},
	keys: [{
		alt: true,
		fn: function(inp, e) {
			var current_window = Ext.getCmp('EvnPrescrEditWindow');

			switch ( e.getKey() ) {
				case Ext.EventObject.C:
					current_window.doSave();
				break;

				case Ext.EventObject.J:
					current_window.hide();
				break;
			}
		},
		key: [
			Ext.EventObject.C,
			Ext.EventObject.J
		],
		scope: this,
		stopEvent: false
	}],
	layout: 'form',
	listeners: {
		'beforehide': function(win) {
			//
		},
		'hide': function(win) {
			win.onHide();
		},
		'maximize': function(win) {
			//
		},
		'restore': function(win) {
			//
		}
	},
	loadMask: null,
	maximizable: false,
	maximized: false,
	minHeight: 150,
	minWidth: 750,
	modal: true,
	onHide: Ext.emptyFn,
	openEvnPrescrTreatDrugEditWindow: function(action) {
		if ( typeof action != 'string' || !(action.inlist([ 'add', 'edit' ])) ) {
			return false;
		}

		if ( getWnd('swEvnPrescrTreatDrugEditWindow').isVisible() ) {
			sw.swMsg.alert(lang['oshibka'], lang['okno_redaktirovaniya_naznachaemogo_medikamenta_uje_otkryito']);
			return false;
		}

		var base_form = this.FormPanel.getForm();
		var grid = this.findById('EPREF_DrugGrid').getGrid();
		var params = new Object();

		params.action = action;
		params.callback = function(data) {
			if ( typeof data != 'object' || typeof data.evnPrescrTreatDrugData != 'object' ) {
				return false;
			}

			var record = grid.getStore().getById(data.evnPrescrTreatDrugData.EvnPrescrTreatDrug_id);

			if ( record ) {
				var grid_fields = new Array();

				grid.getStore().fields.eachKey(function(key, item) {
					grid_fields.push(key);
				});

				for ( i = 0; i < grid_fields.length; i++ ) {
					record.set(grid_fields[i], data.evnPrescrTreatDrugData[grid_fields[i]]);
				}

				record.commit();
			}
			else {
				if ( grid.getStore().getCount() == 1 && !grid.getStore().getAt(0).get('EvnPrescrTreatDrug_id') ) {
					grid.getStore().removeAll();
				}

				data.evnPrescrTreatDrugData.EvnPrescrTreatDrug_id = -swGenTempId(grid.getStore());

				grid.getStore().loadData([ data.evnPrescrTreatDrugData ], true);
			}
		}.createDelegate(this);
		params.formParams = new Object();

		if ( action == 'add' ) {
			params.onHide = function() {
				if ( grid.getStore().getCount() > 0 ) {
					grid.getView().focusRow(0);
				}
			};
		}
		else {
			if ( !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('EvnPrescrTreatDrug_id') ) {
				return false;
			}

			var record = grid.getSelectionModel().getSelected();

			params.formParams = record.data;
			params.onHide = function() {
				grid.getView().focusRow(grid.getStore().indexOf(record));
			};
		}

		getWnd('swEvnPrescrTreatDrugEditWindow').show(params);
	},
	plain: true,
	resizable: true,
	show: function() {
		sw.Promed.swEvnPrescrEditWindow.superclass.show.apply(this, arguments);

		// this.restore();
		this.center();
		// this.maximize();

		var base_form = this.FormPanel.getForm();
		base_form.reset();

		this.findById('EPREF_DrugGrid').getGrid().getStore().removeAll();
		this.findById('EPREF_TimeGrid').getGrid().getStore().removeAll();

		this.begDate = null;
		this.callback = Ext.emptyFn;
		this.exceptionTypes = new Array();
		this.formStatus = 'edit';
		this.onHide = Ext.emptyFn;
		
		if ( !arguments[0] || !arguments[0].formParams ) {
			sw.swMsg.alert(lang['soobschenie'], lang['nevernyie_parametryi'], function() { this.hide(); }.createDelegate(this) );
			return false;
		}

		base_form.setValues(arguments[0].formParams);

		if ( arguments[0].begDate && typeof arguments[0].begDate == 'object' ) {
			this.begDate = arguments[0].begDate;
		}

		if ( arguments[0].callback && typeof arguments[0].callback == 'function' ) {
			this.callback = arguments[0].callback;
		}

		if ( arguments[0].exceptionTypes && typeof arguments[0].exceptionTypes == 'object' ) {
			this.exceptionTypes = arguments[0].exceptionTypes;
		}

		if ( arguments[0].onHide && typeof arguments[0].onHide == 'function' ) {
			this.onHide = arguments[0].onHide;
		}

		if ( typeof this.begDate == 'object' ) {
			base_form.findField('EvnPrescr_setDate').setMaxValue(this.begDate.add(Date.DAY, 30));
			base_form.findField('EvnPrescr_setDate').setMinValue(this.begDate);
			base_form.findField('EvnPrescr_setDate_Range').setMaxValue(this.begDate.add(Date.DAY, 30));
			base_form.findField('EvnPrescr_setDate_Range').setMinValue(this.begDate);
		}
		else {
			base_form.findField('EvnPrescr_setDate').setMaxValue(undefined);
			base_form.findField('EvnPrescr_setDate').setMinValue(undefined);
			base_form.findField('EvnPrescr_setDate_Range').setMaxValue(undefined);
			base_form.findField('EvnPrescr_setDate_Range').setMinValue(undefined);
		}
/*
		this.PersonInfo.load({
			Person_id: (arguments[0].Person_id ? arguments[0].Person_id : ''),
			Person_Birthday: (arguments[0].Person_Birthday ? arguments[0].Person_Birthday : ''),
			Person_Firname: (arguments[0].Person_Firname ? arguments[0].Person_Firname : ''),
			Person_Secname: (arguments[0].Person_Secname ? arguments[0].Person_Secname : ''),
			Person_Surname: (arguments[0].Person_Surname ? arguments[0].Person_Surname : ''),
			callback: function() {
				clearDateAfterPersonDeath('personpanelid', 'EStEF_PersonInformationFrame', base_form.findField('EvnStick_setDate'));
			}
		});
*/
		base_form.findField('PrescriptionType_id').getStore().filterBy(function(rec) {
			if ( rec.get('PrescriptionType_id').toString().inlist(this.exceptionTypes) ) {
				return false;
			}
			else {
				return true;
			}
		}.createDelegate(this));

		var prescriptiontype_id = base_form.findField('PrescriptionType_id').getValue();
		if(prescriptiontype_id) {
			base_form.findField('PrescriptionType_id').fireEvent('change', base_form.findField('PrescriptionType_id'), prescriptiontype_id, null);
			base_form.findField('PrescriptionType_id').setDisabled(true);
		} else {
			base_form.findField('PrescriptionType_id').fireEvent('change', base_form.findField('PrescriptionType_id'), null, null);
			base_form.findField('PrescriptionType_id').setDisabled(false);
		}

		base_form.clearInvalid();

		base_form.findField('PrescriptionType_id').focus(true, 250);
	},
	title: WND_PRESCR_EPRSTRINP,
	width: 750
});