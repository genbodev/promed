/**
* swEvnVizitPLStomEditWindow - окно редактирования/добавления посещения пациентом поликлиники.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Polka
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Stas Bykov aka Savage (savage@swan.perm.ru)
* @version      0.001-21.01.2010
* @comment      Префикс для id компонентов EVPLSEF (EvnVizitPLStomEditForm)
*
*
* @input data: action - действие (add, edit, view)
*
*
* Использует: окно редактирования заболевания (основного диагноза) (swEvnDiagPLStomEditWindow)
*             окно редактирования диагноза (swEvnDiagPLStomSopEditWindow)
*             окно редактирования услуги (swEvnUslugaStomEditWindow)
*/
sw.Promed.swEvnVizitPLStomEditWindow = Ext.extend(sw.Promed.BaseForm, {
	action: null,
	baseTitle: WND_POL_EVPLSTOM,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: false,
	closeAction: 'hide',
	collapsible: true,
	reloadUslugaComplexField: function(needUslugaComplex_id, wantUslugaComplex_id) {
		if (this.loadingInProgress) {
			return false;
		}

		var win = this;

		var base_form = this.findById('EvnVizitPLStomEditForm').getForm();
		var field = base_form.findField('UslugaComplex_uid');

		if (getRegionNick() == 'vologda') {
			field.getStore().baseParams.FedMedSpec_id = base_form.findField('MedStaffFact_id').getFieldValue('FedMedSpec_id');
			field.getStore().baseParams.TreatmentClass_id = base_form.findField('TreatmentClass_id').getValue();
		}

		if (getRegionNick() == 'perm') {
			field.getStore().baseParams.VizitType_id = base_form.findField('VizitType_id').getValue();
			field.getStore().baseParams.VizitClass_id = base_form.findField('VizitClass_id').getValue();
			field.getStore().baseParams.TreatmentClass_id = base_form.findField('TreatmentClass_id').getValue();
			field.getStore().baseParams.LpuSectionProfile_id = base_form.findField('LpuSectionProfile_id').getValue();
			field.getStore().baseParams.isPrimaryVizit = base_form.findField('EvnVizitPLStom_IsPrimaryVizit').getValue();
			field.getStore().baseParams.FedMedSpec_id = base_form.findField('MedStaffFact_id').getFieldValue('FedMedSpec_id');
		}

		/*if (getRegionNick() == 'ekb') {
			field.getStore().baseParams.MesOldVizit_id = base_form.findField('Mes_id').getValue();
		}

		if (getRegionNick() == 'kz') {
			field.getStore().baseParams.LpuSection_id = base_form.findField('LpuSection_id').getValue();
		}*/

		if (getRegionNick() == 'perm' || getRegionNick() == 'vologda') {
			var currSetDate = Ext.util.Format.date(base_form.findField('EvnVizitPLStom_setDate').getValue(), 'd.m.Y');
			var currSetTime = base_form.findField('EvnVizitPLStom_setTime').getValue();
			var currSetDT = getValidDT(currSetDate, currSetTime);
			var lastSetDT = currSetDT;

			if (!currSetDT || !Ext.isArray(this.OtherVizitList)) {
				return false;
			}

			for(var i=0; i<this.OtherVizitList.length; i++) {
				var vizit = this.OtherVizitList[i];
				var setDT = getValidDT(vizit.EvnVizitPLStom_setDate, '00:00');
				if (setDT && setDT > lastSetDT) {
					lastSetDT = setDT;
				}
			}

			field.getStore().baseParams.UslugaComplex_Date = Ext.util.Format.date(lastSetDT, 'd.m.Y');
		} else {
			field.getStore().baseParams.UslugaComplex_Date = Ext.util.Format.date(base_form.findField('EvnVizitPLStom_setDate').getValue(), 'd.m.Y');
		}

		field.getStore().baseParams.EvnVizit_id = base_form.findField('EvnVizitPLStom_id').getValue();
		field.getStore().baseParams.query = "";

		// повторно грузить одно и то же не нужно
		var newUslugaComplexParams = Ext.util.JSON.encode(field.getStore().baseParams);
		if (needUslugaComplex_id || newUslugaComplexParams != win.lastUslugaComplexParams) {
			win.lastUslugaComplexParams = newUslugaComplexParams;
			var currentUslugaComplex_id = base_form.findField('UslugaComplex_uid').getValue();
			field.lastQuery = 'This query sample that is not will never appear';
			field.getStore().removeAll();

			var params = {};
			if (needUslugaComplex_id) {
				params.UslugaComplex_id = needUslugaComplex_id;
				currentUslugaComplex_id = needUslugaComplex_id;
			}

			field.getStore().load({
				callback: function (rec) {
					var index = -1;
					if (wantUslugaComplex_id) {
						index = base_form.findField('UslugaComplex_uid').getStore().findBy(function (rec) {
							return (rec.get('UslugaComplex_id') == wantUslugaComplex_id);
						});
					}
					if (index < 0) {
						index = base_form.findField('UslugaComplex_uid').getStore().findBy(function (rec) {
							return (rec.get('UslugaComplex_id') == currentUslugaComplex_id);
						});
					}
					if (index < 0 && getRegionNick() == 'pskov' && base_form.findField('UslugaComplex_uid').getStore().getCount() == 1) {
						index = 0;
					}

					if (index >= 0) {
						var record = base_form.findField('UslugaComplex_uid').getStore().getAt(index);
						field.setValue(record.get('UslugaComplex_id'));
						field.setRawValue(record.get('UslugaComplex_Code') + '. ' + record.get('UslugaComplex_Name'));
						/*if (getRegionNick() == 'ekb') {
							base_form.findField('Mes_id').setUslugaComplex_id(record.get('UslugaComplex_id'));
						}*/
					} else {
						field.clearValue();
						/*if (getRegionNick() == 'ekb') {
							base_form.findField('Mes_id').setUslugaComplex_id(null);
						}*/
					}

					field.fireEvent('change', field, field.getValue());
				},
				params: params
			});
		} else if (wantUslugaComplex_id) {
			index = base_form.findField('UslugaComplex_uid').getStore().findBy(function (rec) {
				return (rec.get('UslugaComplex_id') == wantUslugaComplex_id);
			});
			if (index >= 0) {
				var record = base_form.findField('UslugaComplex_uid').getStore().getAt(index);
				field.setValue(record.get('UslugaComplex_id'));
				field.setRawValue(record.get('UslugaComplex_Code') + '. ' + record.get('UslugaComplex_Name'));
				/*if (getRegionNick() == 'ekb') {
					base_form.findField('Mes_id').setUslugaComplex_id(record.get('UslugaComplex_id'));
				}*/
			} else {
				field.clearValue();
				/*if (getRegionNick() == 'ekb') {
					base_form.findField('Mes_id').setUslugaComplex_id(null);
				}*/
			}
		}
	},
	loadUslugaComplexTariffCombo: function () {
		var base_form = this.findById('EvnVizitPLStomEditForm').getForm(),
			combo = base_form.findField('UslugaComplexTariff_id'),
			uc_combo = base_form.findField('UslugaComplex_uid'),
			params = {
				LpuSection_id: base_form.findField('LpuSection_id').getValue()
				,PayType_id: base_form.findField('PayType_id').getValue()
				,Person_id: base_form.findField('Person_id').getValue()
				,UslugaComplexTariff_Date: base_form.findField('EvnVizitPLStom_setDate').getValue()
				,UslugaComplex_id: uc_combo.getValue()
			};
		combo.setParams(params);
		combo.fireEvent('change', combo, combo.getValue());
		combo.isAllowSetFirstValue = ('add' == this.action);
		combo.isLpuFilter = true;
		if (getRegionNick() == 'perm') {
			combo.getStore().baseParams.UEDAboveZero = 1;
		}
		combo.loadUslugaComplexTariffList();
		return true;
	},
	loadLpuSectionProfileDop: function() {
		var needFireEvent = true;
		if (this.loadingInProgress) {
			needFireEvent = false;
		}

		var win = this;
		var base_form = this.findById('EvnVizitPLStomEditForm').getForm();

			var oldValue = base_form.findField('LpuSectionProfile_id').getValue();

			if (!Ext.isEmpty(base_form.findField('LpuSection_id').getValue())) {
				if (!base_form.findField('LpuSectionProfile_id').getStore().baseParams.LpuSection_id || base_form.findField('LpuSection_id').getValue() != base_form.findField('LpuSectionProfile_id').getStore().baseParams.LpuSection_id) {
					base_form.findField('LpuSectionProfile_id').lastQuery = '';
					base_form.findField('LpuSectionProfile_id').getStore().removeAll();
					base_form.findField('LpuSectionProfile_id').getStore().baseParams.LpuSection_id = base_form.findField('LpuSection_id').getValue();
					base_form.findField('LpuSectionProfile_id').getStore().baseParams.onDate = (!Ext.isEmpty(base_form.findField('EvnVizitPLStom_setDate').getValue()) ? base_form.findField('EvnVizitPLStom_setDate').getValue().format('d.m.Y') : getGlobalOptions().date);
					base_form.findField('LpuSectionProfile_id').getStore().load({
						callback: function () {
							
							var comboLpuSectionProfile = base_form.findField('LpuSectionProfile_id');
							var otherVizit = (win.OtherVizitList && Ext.isArray(win.OtherVizitList) && win.OtherVizitList.length>0) ? win.OtherVizitList[win.OtherVizitList.length-1] : null;
							var index = comboLpuSectionProfile.getStore().findBy(function (rec) {
								return (rec.get('LpuSectionProfile_id') == oldValue);
							});

							if (index == -1) {
								base_form.findField('LpuSectionProfile_id').clearValue();
								if(getRegionNick() == 'vologda' && otherVizit && comboLpuSectionProfile.findRecord('LpuSectionProfile_id', otherVizit.LpuSectionProfile_id) ){
									//Если в ТАП добавлено хотя бы одно посещение,  по умолчанию в поле устанавливается профиль отделения из ранее добавленного посещения
									comboLpuSectionProfile.setValue(otherVizit.LpuSectionProfile_id);
								}else if (base_form.findField('LpuSection_id').getFieldValue('LpuSectionProfile_id') > 0) {								
									base_form.findField('LpuSectionProfile_id').setValue(base_form.findField('LpuSection_id').getFieldValue('LpuSectionProfile_id'));
								} else if (base_form.findField('LpuSectionProfile_id').getStore().getCount() > 0) {
									base_form.findField('LpuSectionProfile_id').setValue(base_form.findField('LpuSectionProfile_id').getStore().getAt(0).get('LpuSectionProfile_id'));
								}
							} else {
								base_form.findField('LpuSectionProfile_id').setValue(oldValue);
							}

							if (needFireEvent) {
								base_form.findField('LpuSectionProfile_id').fireEvent('change', base_form.findField('LpuSectionProfile_id'), base_form.findField('LpuSectionProfile_id').getValue());
							}

							win.setDefaultMedicalCareKind();
						}
					});
				}
			}
		
	},
	deleteEvent: function(event) {
		if ( this.action == 'view' ) {
			return false;
		}

		if ( Ext.isEmpty(event) || !event.inlist([ 'EvnDiagPLStom', 'EvnDiagPLStomSop', 'EvnUsluga' ]) ) {
			return false;
		}

		var base_form = this.findById('EvnVizitPLStomEditForm').getForm();
		var error = '';
		var grid = null;
		var question = '';
		var params = new Object();
		var url = '';

		switch ( event ) {
			case 'EvnDiagPLStom':
				error = 'При удалении заболевания возникли ошибки';
				grid = this.findById('EVPLSEF_EvnDiagPLStomGrid');
				question = 'Удалить заболевание?';
				url = '/?c=Evn&m=deleteEvn';
			break;

			case 'EvnDiagPLStomSop':
				error = 'При удалении сопутствующего диагноза возникли ошибки';
				grid = this.findById('EVPLSEF_EvnDiagPLStomSopGrid');
				question = 'Удалить сопутствующий диагноз?';
				url = '/?c=EvnDiag&m=deleteEvnDiag';
			break;

			case 'EvnUsluga':
				error = 'При удалении услуги возникли ошибки';
				grid = this.findById('EVPLSEF_EvnUslugaStomGrid');
				question = 'Удалить услугу?';
				url = '/?c=EvnUsluga&m=deleteEvnUsluga';
			break;
		}

		if ( !grid || !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get(event + '_id') ) {
			return false;
		}

		var selected_record = grid.getSelectionModel().getSelected();

		switch ( event ) {
			case 'EvnDiagPLStom':
				params['Evn_id'] = selected_record.get('EvnDiagPLStom_id');
			break;

			case 'EvnDiagPLStomSop':
				params['class'] = 'EvnDiagPLStomSop';
				params['id'] = selected_record.get('EvnDiagPLStomSop_id');
			break;

			case 'EvnUsluga':
				params['class'] = 'EvnUslugaStom';
				params['id'] = selected_record.get('EvnUsluga_id');

				if (getRegionNick() == 'perm' && base_form.findField('EvnVizitPLStom_RepFlag').checked) {
					winParams.ignorePaidCheck = 1;
				}
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
							sw.swMsg.alert('Ошибка', error);
						},
						params: params,
						success: function(response, options) {
							loadMask.hide();

							var response_obj = Ext.util.JSON.decode(response.responseText);

							if ( response_obj.success == false ) {
								sw.swMsg.alert('Ошибка', response_obj.Error_Msg ? response_obj.Error_Msg : error);
							}
							else {
								grid.getStore().remove(selected_record);

								if ( event == 'EvnUsluga' ) {
									this.EvnUslugaGridIsModified = true;
									this.uetValuesRecount();
									if (this.ToothMapPanel.isLoaded) {
										this.ToothMapPanel.doReloadViewData();
									}
								}
								else if ( event == 'EvnDiagPLStom' ) {
									this.EvnDiagPLStomGridIsModified = true;
									this.loadDiagNewCombo();
								}

								if ( grid.getStore().getCount() == 0 ) {
									grid.getTopToolbar().items.items[1].disable();
									grid.getTopToolbar().items.items[2].disable();
									grid.getTopToolbar().items.items[3].disable();
									LoadEmptyRow(grid);

									if ( event == 'EvnUsluga' ) {
										base_form.findField('EvnVizitPLStom_Uet').enable();
										base_form.findField('EvnVizitPLStom_UetOMS').enable();
									}
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
			title: 'Вопрос'
		});
	},
	setMKB: function(){
		var parentWin = this;
		var base_form = this.findById('EvnVizitPLStomEditForm').getForm();
		var sex = parentWin.findById('EVPLSEF_PersonInformationFrame').getFieldValue('Sex_Code');
		var age = swGetPersonAge(parentWin.findById('EVPLSEF_PersonInformationFrame').getFieldValue('Person_Birthday'),base_form.findField('EvnVizitPLStom_setDate').getValue());
		base_form.findField('Diag_id').setMKBFilter(age,sex,true);
	},
	filterTreatmentClass: function() {
		var
			base_form = this.findById('EvnVizitPLStomEditForm').getForm(),
			TreatmentClass_id = base_form.findField('TreatmentClass_id').getValue(),
			TreatmentClassArray = [ ],
			win = this;

		if (getRegionNick() == 'kz') return false;

		if (
			getRegionNick() == 'penza' && !Ext.isEmpty(win.TreatmentClass_id)
			&& getValidDT(base_form.findField('EvnPLStom_setDate').getValue(), '') >= getValidDT('01.06.2019', '')
		) {
			switch ( win.TreatmentClass_id ) {
				case 1:
				case 3:
				case 4:
					TreatmentClassArray = [ 1, 3, 4 ];
					break;

				case 2:
					TreatmentClassArray = [ 2 ];
					break;

				case 6:
				case 7:
				case 8:
				case 9:
				case 10:
				case 11:
					TreatmentClassArray = [ 6, 7, 8, 9, 10, 11 ];
					break;
			}
		}
		else if (!Ext.isEmpty(base_form.findField('Diag_newid').getFieldValue('Diag_Code'))) {
			if (base_form.findField('Diag_newid').getFieldValue('Diag_Code') == 'Z51.5') {
				TreatmentClassArray = [ 9 ];
			}
			else if (base_form.findField('Diag_newid').getFieldValue('Diag_Code').substr(0,1) == 'Z' || (getRegionNick() == 'perm' && base_form.findField('Diag_newid').getFieldValue('Diag_Code').substr(0,3) == 'W57')) {
				TreatmentClassArray = [ 6, 7, 8, 9, 10, 11, 12 ];
			}
			else if ( getRegionNick() == 'penza' ) {
				TreatmentClassArray = [ 1, 2, 3, 4, 11, 13 ];
			}
			else {
				TreatmentClassArray = [ 1, 2, 3, 4, 13 ];
			}
		}

		base_form.findField('TreatmentClass_id').getStore().filterBy(function(rec) {
			if ( TreatmentClassArray.length > 0 ) {
				return rec.get('TreatmentClass_id').inlist(TreatmentClassArray);
			}
			else {
				return (!rec.get('TreatmentClass_Code').inlist([ 2 ]));
			}
		});

		var index = base_form.findField('TreatmentClass_id').getStore().findBy(function(rec) {
			return (rec.get('TreatmentClass_id') == TreatmentClass_id);
		});
		if (index == -1) {
			base_form.findField('TreatmentClass_id').clearValue();
		}
	},
	filterVizitTypeCombo: function() {
		var base_form = this.findById('EvnVizitPLStomEditForm').getForm();
		var formDate = base_form.findField('EvnVizitPLStom_setDate').getValue();

		if (getRegionNick() == 'kz') return false;

		base_form.findField('VizitType_id').setTreatmentClass(base_form.findField('TreatmentClass_id').getValue());

		if (getRegionNick() == 'kareliya' && !Ext.isEmpty(base_form.findField('PayType_id').getValue())) {
			var pay_type_nick = base_form.findField('PayType_id').getFieldValue('PayType_SysNick');
			if (pay_type_nick == 'oms') {
				var denied_visit_type_codes = ['41', '51', '2.4', '3.1'];

				if (formDate < new Date('2019-05-01')) {
					denied_visit_type_codes.push('1.2');
				}

				base_form.findField('VizitType_id').setFilterByDateAndCode(formDate, denied_visit_type_codes);
			} else {
				base_form.findField('VizitType_id').setFilterByDate(formDate);
			}
		} else {
			base_form.findField('VizitType_id').setFilterByDate(formDate);
		}
	},
	setTreatmentClass: function() {
		var base_form = this.findById('EvnVizitPLStomEditForm').getForm();
		var DiagCombo = base_form.findField('Diag_newid');
		var VizitTypeCombo = base_form.findField('VizitType_id');
		var VizitType_id = VizitTypeCombo.getValue();
		var ServiceTypeCombo = base_form.findField('ServiceType_id');
		var ServiceType_id = ServiceTypeCombo.getValue();
		var TreatmentClassCombo = base_form.findField('TreatmentClass_id');
		var TreatmentClass_id = TreatmentClassCombo.getValue();

		if (!DiagCombo.getFieldValue('Diag_Code')) return false;

		TreatmentClassCombo.getStore().filterBy(function(rec) {
			if (DiagCombo.getFieldValue('Diag_Code') == 'Z51.5') {
				return (rec.get('TreatmentClass_id').inlist([ 9 ]));
			} else if (base_form.findField('Diag_newid').getFieldValue('Diag_Code').substr(0,1) == 'Z' || (getRegionNick() == 'perm' && base_form.findField('Diag_newid').getFieldValue('Diag_Code').substr(0,3) == 'W57')) {
				return (rec.get('TreatmentClass_id').inlist([ 6, 7, 8, 9, 10, 11, 12 ]));
			} else if ( getRegionNick() == 'penza' ) {
				return (rec.get('TreatmentClass_id').inlist([ 1, 2, 3, 4, 11, 13 ]));
			} else {
				return (rec.get('TreatmentClass_id').inlist([ 1, 2, 3, 4, 13 ]));
			}
		});

		var aindex = TreatmentClassCombo.getStore().findBy(function(rec) {
			var bindex = swTreatmentClassServiceTypeGlobalStore.findBy(function(r) {
				var cindex = swTreatmentClassVizitTypeGlobalStore.findBy(function(r2) {
					return (
						r.get('ServiceType_id') == ServiceType_id && r2.get('VizitType_id') == VizitType_id &&
						r.get('TreatmentClass_id') == rec.get('TreatmentClass_id') && r2.get('TreatmentClass_id') == rec.get('TreatmentClass_id')
					);
				});
				return (cindex != -1);
			});
			return (bindex != -1);
		});

		if (aindex == -1) {
			aindex = 0;
		}

		TreatmentClass_id = TreatmentClassCombo.getStore().getAt(aindex) && TreatmentClassCombo.getStore().getAt(aindex).get('TreatmentClass_id');
		TreatmentClassCombo.setValue(TreatmentClass_id);
	},
	doSave: function(options) {
		// options @Object
		// options.ignoreEvnUslugaStomCountCheck @Boolean Не проверять наличие выполненных услуг, если true
		// options.openChildWindow @Function Открыть дочернее окно после сохранения

		if ( this.formStatus == 'save' || this.action == 'view' ) {
			return false;
		}

		this.formStatus = 'save';

		var base_form = this.findById('EvnVizitPLStomEditForm').getForm();
		var form = this.findById('EvnVizitPLStomEditForm');
		var isPerm = (getRegionNick() == 'perm');
		var isUfa = (getRegionNick() == 'ufa');
		var diagField = base_form.findField('Diag_id');
		var vizitCodeField = base_form.findField('UslugaComplex_uid');
		var isAllowBlankDiag = diagField.allowBlank;
		var isAllowBlankVizitCode = vizitCodeField.allowBlank;
		if (options && options.isAutoCreate) {
			diagField.setAllowBlank(true);
			vizitCodeField.setAllowBlank(true);
		}

		if ( getRegionNick() == 'perm' && this.formMode == 'morbus' ) {
			base_form.findField('Mes_id').clearValue();
			base_form.findField('DeseaseType_id').clearValue();
			base_form.findField('Diag_id').clearValue();
		}

		if ( !base_form.isValid() ) {
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
		if (options && options.isAutoCreate) {
			diagField.setAllowBlank(isAllowBlankDiag);
			vizitCodeField.setAllowBlank(isAllowBlankVizitCode);
		}

		var evn_usluga_stom_store = this.findById('EVPLSEF_EvnUslugaStomGrid').getStore();

		if (options && options.isAutoCreate) {
			options.ignoreEvnUslugaStomCountCheck = true;
		}
		// Если Уфа и ignoreEvnUslugaStomCountCheck = true либо не задан, то проверяем количество введенных услуг
		// Если не введено ни одно услуги, то посещение не сохраняем и выдаем сообщение
		if ( (!options || !options.ignoreEvnUslugaStomCountCheck) && isUfa == true ) {
			if ( evn_usluga_stom_store.getCount() == 0 || (evn_usluga_stom_store.getCount() == 1 && !evn_usluga_stom_store.getAt(0).get('EvnUsluga_id')) ) {
				sw.swMsg.show({
					buttons: Ext.Msg.OK,
					fn: function() {
						this.formStatus = 'edit';
						this.findById('EVPLSEF_EvnUslugaStomGrid').getView().focusRow(0);
						this.findById('EVPLSEF_EvnUslugaStomGrid').getSelectionModel().selectFirstRow();
					}.createDelegate(this),
					icon: Ext.Msg.WARNING,
					msg: 'Не введено ни одной услуги. Сохранение посещения невозможно',
					title: ERR_INVFIELDS_TIT
				});
				return false;
			}
		}

		if(this.errorControlCodaVisits()){
			sw.swMsg.alert(langs('Сообщение'), langs('Сохранение посещения невозможно, т.к. в рамках текущего ТАП специалистом другого профиля уже добавлено посещение. '));
			this.formStatus = 'edit';
			return false;
		}		

		var index;
		var params = new Object();
		var record;

		var diag_code = '', diag_name = '';
		var evn_vizit_pl_set_time = base_form.findField('EvnVizitPLStom_setTime').getValue();
		var lpu_section_profile_code = '';
		var med_personal_fio = '';
		var pay_type_name = '';
		var pay_type_nick = '';
		var service_type_name = '';

		// Мед. персонал
		index = base_form.findField('MedStaffFact_id').getStore().findBy(function(rec) {
			return (rec.get('MedStaffFact_id') == base_form.findField('MedStaffFact_id').getValue());
		});

		if ( index >= 0 ) {
			lpu_section_profile_code = base_form.findField('MedStaffFact_id').getStore().getAt(index).get('LpuSectionProfile_Code');
			med_personal_fio = base_form.findField('MedStaffFact_id').getStore().getAt(index).get('MedPersonal_Fio');
			base_form.findField('MedPersonal_id').setValue(base_form.findField('MedStaffFact_id').getStore().getAt(index).get('MedPersonal_id'));
		}

		// Средний мед. персонал
		index = base_form.findField('MedStaffFact_sid').getStore().findBy(function(rec) {
			return (rec.get('MedStaffFact_id') == base_form.findField('MedStaffFact_sid').getValue());
		});

		if ( index >= 0 ) {
			base_form.findField('MedPersonal_sid').setValue(base_form.findField('MedStaffFact_sid').getStore().getAt(index).get('MedPersonal_id'));
		}

		// Вид оплаты
		index = base_form.findField('PayType_id').getStore().findBy(function(rec) {
			return (rec.get('PayType_id') == base_form.findField('PayType_id').getValue());
		});

		if ( index >= 0 ) {
			pay_type_name = base_form.findField('PayType_id').getStore().getAt(index).get('PayType_Name');
			pay_type_nick = base_form.findField('PayType_id').getStore().getAt(index).get('PayType_SysNick');
		}

		// Место
		index = base_form.findField('ServiceType_id').getStore().findBy(function(rec) {
			return (rec.get('ServiceType_id') == base_form.findField('ServiceType_id').getValue());
		});

		if ( index >= 0 ) {
			record = base_form.findField('ServiceType_id').getStore().getAt(index);

			service_type_name = record.get('ServiceType_Name');

			if ( record.get('ServiceType_SysNick') == 'neotl' && evn_vizit_pl_set_time.toString().length == 0 ) {
				sw.swMsg.show({
					buttons: Ext.Msg.OK,
					fn: function() {
						this.formStatus = 'edit';
						base_form.findField('EvnVizitPLStom_setTime').focus(false);
					}.createDelegate(this),
					icon: Ext.Msg.WARNING,
					msg: 'Не указано время посещения',
					title: ERR_INVFIELDS_TIT
				});
				return false;
			}
		}

		// Диагноз
		index = base_form.findField('Diag_id').getStore().findBy(function(rec) {
			return (rec.get('Diag_id') == base_form.findField('Diag_id').getValue());
		});

		if ( index >= 0 ) {
			record = base_form.findField('Diag_id').getStore().getAt(index);

			diag_code = record.get('Diag_Code');
			diag_name = record.get('Diag_Name');

			// https://redmine.swan.perm.ru/issues/21764
			if (this.findById('EVPLSEF_DiagPanel').isVisible() && !Ext.isEmpty(diag_code) && diag_code.substr(0, 1).toUpperCase() != 'Z' && Ext.isEmpty(base_form.findField('DeseaseType_id').getValue()) ) {
				sw.swMsg.show({
					buttons: Ext.Msg.OK,
					fn: function() {
						this.formStatus = 'edit';
						base_form.findField('DeseaseType_id').markInvalid('Поле обязательно для заполнения при выбранном диагнозе');
						base_form.findField('DeseaseType_id').focus(false);
					}.createDelegate(this),
					icon: Ext.Msg.WARNING,
					msg: 'Не задан характер заболевания',
					title: ERR_INVFIELDS_TIT
				});
				return false;
			}

			if ( getRegionNick() == 'ekb' ) {
				var sex_code = this.findById('EVPLSEF_PersonInformationFrame').getFieldValue('Sex_Code');
				var person_age = swGetPersonAge(this.findById('EVPLSEF_PersonInformationFrame').getFieldValue('Person_Birthday'), base_form.findField('EvnVizitPLStom_setDate').getValue());
				var person_age_month = swGetPersonAgeMonth(this.findById('EVPLSEF_PersonInformationFrame').getFieldValue('Person_Birthday'), base_form.findField('EvnVizitPLStom_setDate').getValue());
				var person_age_day = swGetPersonAgeDay(this.findById('EVPLSEF_PersonInformationFrame').getFieldValue('Person_Birthday'), base_form.findField('EvnVizitPLStom_setDate').getValue());

				if ( person_age == -1 || person_age_month == -1 || person_age_day == -1 ) {
					this.formStatus = 'edit';
					sw.swMsg.alert('Ошибка', 'Ошибка при определении возраста пациента');
					return false;
				}
				if ( !sex_code || !(sex_code.toString().inlist([ '1', '2' ])) ) {
					this.formStatus = 'edit';
					sw.swMsg.alert('Ошибка', 'Не указан пол пациента');
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
						msg: 'Выбранный диагноз не соответствует полу пациента',
						title: 'Ошибка'
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
						msg: 'Выбранный диагноз не соответствует полу пациента',
						title: 'Ошибка'
					});
					return false;
				}
			} else if ( getRegionNick() == 'buryatiya' ) {
				if (pay_type_nick == 'oms' ) {
					var sex_code = this.findById('EVPLSEF_PersonInformationFrame').getFieldValue('Sex_Code');
					if (!sex_code || !(sex_code.toString().inlist(['1', '2']))) {
						this.formStatus = 'edit';
						sw.swMsg.alert('Ошибка', 'Не указан пол пациента');
						return false;
					}
					// если Sex_id не соответсвует полу пациента то "Выбранный диагноз не соответствует полу"
					if (!Ext.isEmpty(record.get('Sex_Code')) && Number(record.get('Sex_Code')) != Number(sex_code)) {
						this.formStatus = 'edit';
						sw.swMsg.show({
							buttons: Ext.Msg.OK,
							fn: function (buttonId, text, obj) {
								base_form.findField('Diag_id').focus(true);
							}.createDelegate(this),
							icon: Ext.Msg.WARNING,
							msg: 'Выбранный диагноз не соответствует полу',
							title: 'Ошибка'
						});
						return false;
					}
					if (!options.ignoreDiagFinance) {
						// если DiagFinance_IsOms = 0
						if (record.get('DiagFinance_IsOms') == 0) {
							this.formStatus = 'edit';
							sw.swMsg.show({
								buttons: Ext.Msg.YESNO,
								fn: function (buttonId, text, obj) {
									if (buttonId == 'yes') {
										options.ignoreDiagFinance = true;
										this.doSave(options);
									} else {
										base_form.findField('Diag_id').focus(true);
									}
								}.createDelegate(this),
								icon: Ext.MessageBox.QUESTION,
								msg: 'Выбранный диагноз не оплачивается по ОМС, продолжить сохранение?',
								title: 'Продолжить сохранение?'
							});
							return false;
						}
					}
				}
			} else if ( getRegionNick() == 'astra' ) {
				if (pay_type_nick == 'oms' ) {
					if (!options.ignoreDiagFinance) {
						// если DiagFinance_IsOms = 0
						if (record.get('DiagFinance_IsOms') == 0) {
							this.formStatus = 'edit';
							sw.swMsg.show({
								buttons: Ext.Msg.YESNO,
								fn: function (buttonId, text, obj) {
									if (buttonId == 'yes') {
										options.ignoreDiagFinance = true;
										this.doSave(options);
									} else {
										base_form.findField('Diag_id').focus(true);
									}
								}.createDelegate(this),
								icon: Ext.MessageBox.QUESTION,
								msg: 'Выбранный диагноз не оплачивается по ОМС, продолжить сохранение?',
								title: 'Продолжить сохранение?'
							});
							return false;
						}
					}
				}
			} else if ( getRegionNick() == 'kareliya' ) {
				if (!options.ignoreDiagFinance && pay_type_nick == 'oms') {
					var sex_code = this.findById('EVPLSEF_PersonInformationFrame').getFieldValue('Sex_Code');
					if ( !sex_code || !(sex_code.toString().inlist([ '1', '2' ])) ) {
						this.formStatus = 'edit';
						sw.swMsg.alert('Ошибка', 'Не указан пол пациента');
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
							msg: 'Выбранный диагноз не оплачивается по ОМС, продолжить сохранение?',
							title: 'Продолжить сохранение?'
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
							msg: 'Выбранный диагноз не оплачивается по ОМС для мужчин, продолжить сохранение?',
							title: 'Продолжить сохранение?'
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
							msg: 'Выбранный диагноз не оплачивается по ОМС для женщин, продолжить сохранение?',
							title: 'Продолжить сохранение?'
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
							title: 'Продолжить сохранение?'
						});
						return false;
					}
				}
			} else {
				// https://redmine.swan.perm.ru/issues/4081
				// Проверка на финансирование по ОМС основного диагноза
				if ( isUfa == true && pay_type_nick == 'oms' ) {
					if ( lpu_section_profile_code.inlist([ '658', '684', '558', '584' ]) ) {
						if ( record.get('DiagFinance_IsHealthCenter') != 1 ) {
							sw.swMsg.alert('Ошибка', 'Диагноз не оплачивается для Центров здоровья', function() {
								this.formStatus = 'edit';
								base_form.findField('Diag_id').focus();
							}.createDelegate(this));
							return false;
						}
					}
					else if ( record.get('DiagFinance_IsOms') == 0 ) {
						sw.swMsg.alert('Ошибка', 'Диагноз не оплачивается по ОМС', function() {
							this.formStatus = 'edit';
							base_form.findField('Diag_id').focus();
						}.createDelegate(this));
						return false;
					}
					else {
						var oms_spr_terr_code = this.findById('EVPLSEF_PersonInformationFrame').getFieldValue('OmsSprTerr_Code');
						var person_age = swGetPersonAge(this.findById('EVPLSEF_PersonInformationFrame').getFieldValue('Person_Birthday'), base_form.findField('EvnVizitPLStom_setDate').getValue());
						var sex_code = this.findById('EVPLSEF_PersonInformationFrame').getFieldValue('Sex_Code');

						if ( person_age == -1 ) {
							this.formStatus = 'edit';
							sw.swMsg.alert('Ошибка', 'Ошибка при определении возраста пациента');
							return false;
						}

						if ( !sex_code || !(sex_code.toString().inlist([ '1', '2' ])) ) {
							this.formStatus = 'edit';
							sw.swMsg.alert('Ошибка', 'Не указан пол пациента');
							return false;
						}

						if ( person_age >= 18 ) {
							if ( Number(record.get('PersonAgeGroup_Code')) == 2 ) {
								sw.swMsg.alert('Ошибка', 'Диагноз не оплачивается для взрослых', function() {
									this.formStatus = 'edit';
									base_form.findField('Diag_id').focus();
								}.createDelegate(this));
								return false;
							}
						}
						else if ( Number(record.get('PersonAgeGroup_Code')) == 1 ) {
							sw.swMsg.alert('Ошибка', 'Диагноз не оплачивается для детей', function() {
								this.formStatus = 'edit';
								base_form.findField('Diag_id').focus();
							}.createDelegate(this));
							return false;
						}

						if ( Number(sex_code) == 1 ) {
							if ( Number(record.get('Sex_Code')) == 2 ) {
								sw.swMsg.alert('Ошибка', 'Диагноз не соответствует полу пациента', function() {
									this.formStatus = 'edit';
									base_form.findField('Diag_id').focus();
								}.createDelegate(this));
								return false;
							}
						}
						else if ( Number(record.get('Sex_Code')) == 1 ) {
							sw.swMsg.alert('Ошибка', 'Диагноз не соответствует полу пациента', function() {
								this.formStatus = 'edit';
								base_form.findField('Diag_id').focus();
							}.createDelegate(this));
							return false;
						}

						if ( getRegionNick() == 'ufa' && oms_spr_terr_code != 61 && record.get('DiagFinance_IsAlien') == '0' ) {
							sw.swMsg.alert('Ошибка', 'Диагноз не оплачивается для пациентов, застрахованных не в РБ', function() {
								this.formStatus = 'edit';
								base_form.findField('Diag_id').focus();
							}.createDelegate(this));
							return false;
						}

						if ( getRegionNick() == 'ufa' && record.get('DiagFinance_IsFacult') == '0' ) {
							sw.swMsg.alert(langs('Ошибка'), langs('Данный диагноз может быть только сопутствующим. Укажите верный основной диагноз.'), function() {
								this.formStatus = 'edit';
								base_form.findField('Diag_id').focus(true);
							}.createDelegate(this));
							return false;
						}
					}
				}
			}
		}

		if ( isPerm == true && base_form.findField('Mes_id').getValue() ) {
			// Проверка что сумма УЕТ не больше допустимого МЭС
			var MES = base_form.findField('EvnVizitPLStom_UetMes').getValue();
			var OMS = base_form.findField('EvnVizitPLStom_UetOMS').getValue();

			if ( (!options.checkMSE) && (MES < OMS) ) {
				this.formStatus = 'edit';
				sw.swMsg.show({
					buttons: Ext.Msg.YESNO,
					fn: function(buttonId, text, obj) {
						if ( buttonId == 'yes' ) {
							options.checkMSE = true;
							this.doSave(options);
						}
					}.createDelegate(this),
					icon: Ext.MessageBox.QUESTION,
					msg: 'Объем помощи в услугах ОМС превышает количество, предусмотренное по МЭС. Измените вид оплаты в одной или нескольких услугах, либо выберите другой МЭС. Продолжить сохранение?',
					title: 'Продолжить сохранение?'
				});
				return false;
			}
		}

		// проверка на существование связи между специальностью врача и профилем отделения на установленную дату. Поиск происходит в глобальном сторе swLpuSectionProfileMedSpecOms
		if (getRegionNick() == 'pskov' && Ext.globalOptions.polka.evnvizitpl_profile_medspecoms_check != 0 && ! options.ignoreLpuSectionProfile_MedSpecOms)
		{
			var onDate =  Ext.util.Format.date(base_form.findField('EvnVizitPLStom_setDate').getValue(), 'd.m.Y'),
				LpuSectionProfile_id = base_form.findField('LpuSectionProfile_id').getValue(),
				MedSpecOms_id = base_form.findField('MedStaffFact_id').getFieldValue('MedSpecOms_id');

			if (checkLpuSectionProfile_MedSpecOms_Exists(MedSpecOms_id, LpuSectionProfile_id, onDate) === false)
			{
				this.formStatus = 'edit';

				if (Ext.globalOptions.polka.evnvizitpl_profile_medspecoms_check == 1)
				{
					sw.swMsg.show({
						buttons: Ext.Msg.YESNO,
						fn: function(buttonId, text, obj) {

							if ( 'yes' == buttonId ) {
								options.ignoreLpuSectionProfile_MedSpecOms = true;
								this.doSave(options);
							} else {
								this.buttons[0].focus();
							}
						}.createDelegate(this),
						icon: Ext.MessageBox.QUESTION,
						msg: 'Нарушено соответствие между профилем и специальностью. Продолжить сохранение?',
						title: 'Вопрос'
					});

				} else
				{
					sw.swMsg.alert('Ошибка', 'Нарушено соответствие между профилем и специальностью');
				}

				return false;

			}
		}

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Подождите, идет сохранение посещения..." });
		loadMask.show();

		//params.AnamnezData = Ext.util.JSON.encode(this.EvnXmlPanel.getSavingData());
		//params.XmlTemplate_id = this.EvnXmlPanel.getXmlTemplate_id();
		
		params.FormType = this.FormType;
		params.from = this.from;
		params.TimetableGraf_id = (base_form.findField('TimetableGraf_id').getValue() > 0 ? base_form.findField('TimetableGraf_id').getValue() : this.TimetableGraf_id);

		if ( base_form.findField('LpuSection_id').disabled ) {
			params.LpuSection_id = base_form.findField('LpuSection_id').getValue();
		}

		if ( base_form.findField('MedStaffFact_id').disabled ) {
			params.MedStaffFact_id = base_form.findField('MedStaffFact_id').getValue();
		}

		if ( base_form.findField('PayType_id').disabled ) {
			params.PayType_id = base_form.findField('PayType_id').getValue();
		}

		if ( base_form.findField('VizitType_id').disabled ) {
			params.VizitType_id = base_form.findField('VizitType_id').getValue();
		}

		if ( base_form.findField('MedicalCareKind_id').disabled ) {
			params.MedicalCareKind_id = base_form.findField('MedicalCareKind_id').getValue();
		}

		if ( base_form.findField('LpuSectionProfile_id').disabled ) {
			params.LpuSectionProfile_id = base_form.findField('LpuSectionProfile_id').getValue();
		}

		if ( getRegionNick() == 'ekb' ) {
			base_form.findField('Mes_id').setValue(base_form.findField('MesEkb_id').getValue());
		}

		if ( base_form.findField('PayTypeKAZ_id').disabled ) {
			params.PayTypeKAZ_id = base_form.findField('PayTypeKAZ_id').getValue();
		}

		params.ToothSurfaceType_id_list = base_form.findField('ToothSurfaceType_id_list').getValue();
		params.EvnUslugaStom_UED = base_form.findField('EvnUslugaStom_UED').getValue();
		params.UslugaComplexTariff_uid = base_form.findField('UslugaComplexTariff_id').getValue();

		if (typeof options == 'object') {
			if (options.isAutoCreate) {
				params.isAutoCreate = 1;
			}
			if ( options.ignoreEvnVizitPLSetDateCheck ) {
				params.ignoreEvnVizitPLSetDateCheck = 1;
			}
			if ( options.ignoreDayProfileDuplicateVizit ) {
				params.ignoreDayProfileDuplicateVizit = 1;
			}
		}

		params.vizit_kvs_control_check = (options && !Ext.isEmpty(options.vizit_kvs_control_check) && options.vizit_kvs_control_check === 1) ? 1 : 0;
		params.vizit_intersection_control_check = (options && !Ext.isEmpty(options.vizit_intersection_control_check) && options.vizit_intersection_control_check === 1) ? 1 : 0;
		params.ignoreMesUslugaCheck = (options && !Ext.isEmpty(options.ignoreMesUslugaCheck) && options.ignoreMesUslugaCheck === 1) ? 1 : 0;
		params.ignoreKSGCheck = (options && !Ext.isEmpty(options.ignoreKSGCheck) && options.ignoreKSGCheck === 1) ? 1 : 0;
		params.ignoreCheckEvnUslugaChange = (options && !Ext.isEmpty(options.ignoreCheckEvnUslugaChange) && options.ignoreCheckEvnUslugaChange === 1) ? 1 : 0;

		if (this.formMode == 'morbus') {
			// диагноз и характер заболевания берём из специального поля выбора диагноза
			base_form.findField('Diag_id').setValue(base_form.findField('Diag_newid').getFieldValue('Diag_id'));
			base_form.findField('DeseaseType_id').setValue(base_form.findField('Diag_newid').getFieldValue('DeseaseType_id'));
		}

		base_form.submit({
			clientValidation: false,
			failure: function(result_form, action) {
				this.formStatus = 'edit';
				loadMask.hide();

				if ( action.result ) {
					if ( action.result.Error_Msg && 'YesNo' != action.result.Error_Msg) {
						sw.swMsg.alert('Ошибка', action.result.Error_Msg);
					} else if ( action.result.Alert_Msg && 'YesNo' == action.result.Error_Msg ) {
						sw.swMsg.show({
							buttons: Ext.Msg.YESNO,
							fn: function(buttonId, text, obj) {
								if ( buttonId == 'yes' ) {
									switch (true) {
										case (111 == action.result.Error_Code):
											options.vizit_kvs_control_check = 1;
										break;
										case (112 == action.result.Error_Code):
											options.vizit_intersection_control_check = 1;
										break;
										case (114 == action.result.Error_Code):
											options.ignoreMesUslugaCheck = 1;
										break;
										case (110 == action.result.Error_Code):
											options.ignoreKSGCheck = 1;
										break;
										case (130 == action.result.Error_Code):
											options.ignoreCheckEvnUslugaChange = 1;
										break;
										default:
											options.ignoreDayProfileDuplicateVizit = true;
									}
									this.doSave(options);
								}
								else {
									base_form.findField('EvnVizitPLStom_setDate').focus(true);
								}
							}.createDelegate(this),
							icon: Ext.MessageBox.QUESTION,
							msg: action.result.Alert_Msg,
							title: 'Продолжить сохранение?'
						});
					} else {
						sw.swMsg.alert('Ошибка', 'При сохранении произошли ошибки [Тип ошибки: 1]');
					}
				}
			}.createDelegate(this),
			params: params,
			success: function(result_form, action) {
				this.formStatus = 'edit';
				loadMask.hide();

				if ( action.result ) {
					if ( action.result.EvnVizitPLStom_id ) {
						base_form.findField('EvnVizitPLStom_id').setValue(action.result.EvnVizitPLStom_id);
						base_form.findField('UslugaComplex_uid').getStore().baseParams.EvnVizit_id = base_form.findField('EvnVizitPLStom_id').getValue();
						base_form.findField('TimetableGraf_id').setValue(action.result.TimetableGraf_id);
						this.EvnXmlPanel.setBaseParams({
							userMedStaffFact: this.userMedStaffFact,
							Server_id: base_form.findField('Server_id').getValue(),
							Evn_id: base_form.findField('EvnVizitPLStom_id').getValue()
						});
						this.EvnXmlPanel.onEvnSave();

						if ( action.result.EvnUslugaStom_id ) {
							base_form.findField('EvnUslugaStom_id').setValue(action.result.EvnUslugaStom_id);
						}

						if ( options && typeof options.openChildWindow == 'function' /* не только при добавлении && this.action == 'add'*/ ) {
							if ( action.result.Alert_Msg && action.result.Alert_Msg.toString().length > 0 ) {
								sw.swMsg.alert('Предупреждение', action.result.Alert_Msg, function() {
									options.openChildWindow();
								});
							}
							else {
								options.openChildWindow();
							}
						}
						else {
							var
								data = new Object(),
								index,
								lpu_section_id = base_form.findField('LpuSection_id').getValue(),
								lpu_section_name = '',
								lpu_unit_set_code = 0,
								vizit_type_name = '',
								vizit_type_sys_nick = '',
								usluga_complex_name = '',
								usluga_complex_code = '';

							// Отделение
							index = base_form.findField('LpuSection_id').getStore().findBy(function(rec) {
								return (rec.get('LpuSection_id') == lpu_section_id);
							});

							if ( index >= 0 ) {
								record = base_form.findField('LpuSection_id').getStore().getAt(index);

								lpu_section_name = record.get('LpuSection_Code') + '. ' + record.get('LpuSection_Name');
								lpu_unit_set_code = record.get('LpuUnitSet_Code');
							}

							// Цель посещения
							index = base_form.findField('VizitType_id').getStore().findBy(function(rec) {
								return (rec.get('VizitType_id') == base_form.findField('VizitType_id').getValue());
							});

							if ( index >= 0 ) {
								vizit_type_name = base_form.findField('VizitType_id').getStore().getAt(index).get('VizitType_Name');
								vizit_type_sys_nick = base_form.findField('VizitType_id').getStore().getAt(index).get('VizitType_SysNick');
							}

							if ( sw.Promed.EvnVizitPLStom.isSupportVizitCode() || (getRegionNick() == 'perm' && this.formMode == 'morbus') ) {
								var usluga_complex_id = base_form.findField('UslugaComplex_uid').getValue();

								index = base_form.findField('UslugaComplex_uid').getStore().findBy(function(rec) {
									return (rec.get('UslugaComplex_id') == usluga_complex_id);
								});

								if ( index >= 0 ) {
									usluga_complex_name = base_form.findField('UslugaComplex_uid').getStore().getAt(index).get('UslugaComplex_Code') + '. ' + base_form.findField('UslugaComplex_uid').getStore().getAt(index).get('UslugaComplex_Name');
									usluga_complex_code = base_form.findField('UslugaComplex_uid').getStore().getAt(index).get('UslugaComplex_Code');
								}
							}

							var EvnVizitPLStom_Uet = 0;
							if (!Ext.isEmpty(base_form.findField('EvnVizitPLStom_UetOMS').getValue())) {
								EvnVizitPLStom_Uet += parseFloat(base_form.findField('EvnVizitPLStom_UetOMS').getValue());
							}
							if (!Ext.isEmpty(base_form.findField('EvnUslugaStom_UED').getValue())) {
								EvnVizitPLStom_Uet += parseFloat(base_form.findField('EvnUslugaStom_UED').getValue());
							}

							data.evnVizitPLStomData = [{
								'accessType': 'edit',
								'EvnVizitPLStom_id': base_form.findField('EvnVizitPLStom_id').getValue(),
								'EvnPLStom_id': base_form.findField('EvnPLStom_id').getValue(),
								'Person_id': base_form.findField('Person_id').getValue(),
								'PersonEvn_id': base_form.findField('PersonEvn_id').getValue(),
								'Server_id': base_form.findField('Server_id').getValue(),
								'Diag_id': base_form.findField('Diag_id').getValue(),
								'Diag_Code': (this.formMode == 'morbus' ? base_form.findField('Diag_newid').getFieldValue('Diag_Code') : base_form.findField('Diag_id').getFieldValue('Diag_Code')),
								'Diag_Name': (this.formMode == 'morbus' ? base_form.findField('Diag_newid').getFieldValue('Diag_Name') : base_form.findField('Diag_id').getFieldValue('Diag_Name')),
								'EvnVizitPLStom_setDate': base_form.findField('EvnVizitPLStom_setDate').getValue(),
								'LpuSection_Name': lpu_section_name,
								'LpuUnitSet_Code': lpu_unit_set_code,
								'MedPersonal_Fio': med_personal_fio,
								'MedPersonal_id': base_form.findField('MedPersonal_id').getValue(),
								'LpuSection_id': base_form.findField('LpuSection_id').getValue(),
								'EvnVizitPLStom_IsSigned': 1, // раз редактировали, значит не подписан
								'ServiceType_Name': service_type_name,
								'VizitType_Name': vizit_type_name,
								'VizitType_SysNick': vizit_type_sys_nick,
								'PayType_id': base_form.findField('PayType_id').getValue(),
								'PayType_Name': pay_type_name,
								'UslugaComplex_Name': usluga_complex_name,
								'UslugaComplex_Code': usluga_complex_code,
								'EvnVizitPLStom_Uet': EvnVizitPLStom_Uet,
								'LpuSectionProfile_id': base_form.findField('LpuSectionProfile_id').getValue(),
								'LpuSectionProfile_Code': base_form.findField('LpuSectionProfile_id').getFieldValue('LpuSectionProfile_Code'),
								'TreatmentClass_id': base_form.findField('TreatmentClass_id').getValue()
							}];

							if ( action.result.Alert_Msg && action.result.Alert_Msg.toString().length > 0 ) {
								sw.swMsg.alert('Предупреждение', action.result.Alert_Msg, function() {
									this.callback(data);
									this.hide();
								}.createDelegate(this) );
							}
							else {
								this.callback(data);
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
		var base_form = this.findById('EvnVizitPLStomEditForm').getForm();
		var form_fields = new Array(
			'EvnVizitPLStom_setDate',
			'EvnVizitPLStom_setTime',
			'LpuSection_id',
			'MedStaffFact_id',
			'MedStaffFact_sid',
			'ServiceType_id',
			'VizitClass_id',
			'VizitType_id',
			'PayType_id',
			'EvnVizitPLStom_Time',
			'ProfGoal_id',
			'DispClass_id',
			'DispProfGoalType_id',
			'Diag_id',
			'DeseaseType_id',
			'Tooth_Code',
			'ToothSurfaceType_id_list',
			'UslugaMedType_id',
			'isPaidVisit'
		);
		var i = 0;

		if ( !getRegionNick().inlist(['perm','pskov']) ) {
			form_fields.push('EvnUslugaStom_UED');
		}

		if ( getGlobalOptions().region ) {
			if ( sw.Promed.EvnVizitPLStom.isSupportVizitCode() || (getRegionNick() == 'perm' && this.formMode == 'morbus') ) {
				form_fields.push('UslugaComplex_uid');
			}
			if ( getGlobalOptions().region.nick.inlist([ 'perm' ]) ) {
				form_fields.push('Mes_id');
			}
			if ( getGlobalOptions().region.nick.inlist([ 'ekb' ]) ) {
				form_fields.push('MesEkb_id');
			}
		}

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
		this.EvnXmlPanel.setReadOnly(!enable);
	},
	EvnDiagPLStomGridIsModified: false,
	EvnUslugaGridIsModified: false,
	formMode: null,
	setFormMode: function() {
		var base_form = this.findById('EvnVizitPLStomEditForm').getForm();

		var
			mode,
			setDate = base_form.findField('EvnPLStom_setDate').getValue() || base_form.findField('EvnVizitPLStom_setDate').getValue(),
			xDate = sw.Promed.EvnPL.getEvnPLStomNewBegDate(),
			DateX20190601 = new Date(2019, 5, 1);

		if ( getValidDT(setDate, '') >= xDate ) {
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
			diagPLStomCM = this.findById('EVPLSEF_EvnDiagPLStomGrid').getColumnModel(),
			uslugaCM = this.findById('EVPLSEF_EvnUslugaStomGrid').getColumnModel(),
			uslugaGridToolbar = this.findById('EVPLSEF_EvnUslugaStomGrid').getTopToolbar(),
			region = getRegionNick();

		switch ( this.formMode ) {
			case 'morbus':
				// Вариант с заболеваниями
				this.findById('EVPLSEF_EvnDiagPLStomPanel').show();
				this.findById('EVPLSEF_EvnDiagPLStomPanel').expand();
				this.findById('EVPLSEF_DiagPanel').hide();
				this.findById('EVPLSEF_EvnDiagPLStomSopPanel').hide();

				diagPLStomCM.setHidden(9, region != 'penza' || getValidDT(setDate, '') >= DateX20190601);

				this.findById('EVPLSEF_ToothCardPanel').setTitle('4. Зубная карта');
				this.findById('EVPLSEF_EvnUslugaStomPanel').setTitle('5. Услуги');

				base_form.findField('Diag_id').setAllowBlank(true);
				base_form.findField('DeseaseType_id').setAllowBlank(true);
				base_form.findField('EvnVizitPLStom_UetMes').setContainerVisible(false);
				if (!getRegionNick().inlist(['perm','kareliya'])) {
					base_form.findField('EvnVizitPLStom_Uet').setContainerVisible(false);
				}
				if (!getRegionNick().inlist(['kareliya'])) {
					base_form.findField('EvnVizitPLStom_UetOMS').setContainerVisible(false);
				}
				base_form.findField('UslugaComplex_uid').setAllowBlank(getRegionNick() != 'perm' && sw.Promed.EvnVizitPLStom.isAllowBlankVizitCode());
				base_form.findField('UslugaComplex_uid').setContainerVisible(sw.Promed.EvnVizitPLStom.isSupportVizitCode() || getRegionNick() == 'perm');
				base_form.findField('Diag_newid').setContainerVisible(true);

				if ( uslugaCM ) {
					uslugaCM.setHidden(6, false);
					uslugaCM.setHidden(7, !region.inlist(['perm','vologda']));
					uslugaCM.setHidden(8, !region.inlist(['perm']));
					uslugaCM.setHidden(9, !region.inlist(['perm','vologda']));
				}

				uslugaGridToolbar.items.items[0].hide();
				uslugaGridToolbar.items.items[1].hide();
				uslugaGridToolbar.items.items[3].hide();
				uslugaGridToolbar.items.items[4].hide();
			break;

			default:
				// Вариант без заболеваний
				this.findById('EVPLSEF_EvnDiagPLStomPanel').hide();
				this.findById('EVPLSEF_DiagPanel').show();
				this.findById('EVPLSEF_EvnDiagPLStomSopPanel').show();

				this.findById('EVPLSEF_ToothCardPanel').setTitle('3. Зубная карта');
				this.findById('EVPLSEF_DiagPanel').setTitle('4. Основной диагноз');
				this.findById('EVPLSEF_EvnDiagPLStomSopPanel').setTitle('5. Сопутствующие диагнозы');
				this.findById('EVPLSEF_EvnUslugaStomPanel').setTitle('6. Услуги');

				base_form.findField('Diag_id').setAllowBlank(!getRegionNick().inlist([ 'pskov', 'ufa','kareliya','astra']));
				base_form.findField('EvnVizitPLStom_UetMes').setContainerVisible(true);
				if (!getRegionNick().inlist(['perm'])) {
					base_form.findField('EvnVizitPLStom_Uet').setContainerVisible(true);
				}
				base_form.findField('EvnVizitPLStom_UetOMS').setContainerVisible(true);
				base_form.findField('UslugaComplex_uid').setAllowBlank(getRegionNick() != 'ekb' && sw.Promed.EvnVizitPLStom.isAllowBlankVizitCode());
				base_form.findField('UslugaComplex_uid').setContainerVisible(sw.Promed.EvnVizitPLStom.isSupportVizitCode());
				base_form.findField('Diag_newid').setContainerVisible(false);
				base_form.findField('Diag_newid').setAllowBlank(true);

				if ( uslugaCM ) {
					uslugaCM.setHidden(6, true);
					uslugaCM.setHidden(7, true);
					uslugaCM.setHidden(8, true);
					uslugaCM.setHidden(9, true);
				}

				uslugaGridToolbar.items.items[0].show();
				uslugaGridToolbar.items.items[1].show();
				uslugaGridToolbar.items.items[3].show();
				uslugaGridToolbar.items.items[4].show();
			break;
		}
	},
	formStatus: 'edit',
	height: 550,
	id: 'EvnVizitPLStomEditWindow',
	initComponent: function() {
		var current_window = this;

		this.evnDirectionAllInfoPanel = new sw.Promed.EvnDirectionAllInfoPanel({
			hidden: true,
			parentClass: 'EvnVizitPLStom',
			personFieldName: 'Person_id',
			evnFieldName: 'EvnVizitPLStom_id',
			idFieldName: 'EvnDirection_id',
			fieldIsAutoName: null,
			timeTableGrafFieldName: 'TimetableGraf_id',
			medStaffFactFieldName: 'MedStaffFact_id',
			id: 'EVPLSEF_DirectInfoPanel'
		});

		this.EvnXmlPanel = new sw.Promed.EvnXmlPanel({
			autoHeight: true,
			bodyStyle: 'padding-top: 0.5em;',
			border: false,
			collapsible: true,
			id: 'EVPLSEF_AnamnezPanel',
			layout: 'form',
			style: 'margin-bottom: 0.5em;',
			title: '2. Анамнез',
			isLoaded: false,
			ownerWin: this,
			options: {
				XmlType_id: sw.Promed.EvnXml.EVN_VIZIT_PROTOCOL_TYPE_ID, // только протоколы осмотра
				EvnClass_id: 13 // документы и шаблоны только категории посещение стоматологии
			},
			// определяем метод, который должен создать посещение перед созданием документа с помощью указанного метода
			onBeforeCreate: function (panel, method, params) {
				if (!panel || !method || typeof panel[method] != 'function') {
					return false;
				}
				var base_form = this.findById('EvnVizitPLStomEditForm').getForm();
				var evn_id_field = base_form.findField('EvnVizitPLStom_id');
				var evn_id = evn_id_field.getValue();
				if (evn_id && evn_id > 0) {
					// посещение было создано ранее
					// все базовые параметры уже должно быть установлены
					panel[method](params);
				} else {
					this.doSave({
						openChildWindow: function() {
							panel.setBaseParams({
								userMedStaffFact: this.userMedStaffFact,
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

		this.ToothMapPanel = new sw.Promed.ToothMapPanel({
			id: 'EVPLSEF_ToothCardPanel',
			border: true,
			collapsible: true,
			layout: 'border',
			style: 'margin-bottom: 0.5em;',
			title: '4. Зубная карта',
			onLoad: function(panel) {
				panel.expand();
			}
		});

		var mesTemplate = new Ext.XTemplate(
			'<table cellpadding="0" cellspacing="0" style="width: 100%;"><tr style="font-family: tahoma; font-size: 10pt; font-weight: bold;">',
			'<td style="padding: 2px; width: 50%;">Код</td>',
			'<td style="padding: 2px; width: 50%;">Норматив</td></tr>',
			'<tpl for="."><tr class="x-combo-list-item" style="white-space: normal; overflow: auto; text-overflow: clip;">',
			'<td style="padding: 2px;">{Mes_Code}&nbsp;</td>',
			'<td style="padding: 2px;">{Mes_KoikoDni}&nbsp;</td>',
			'</tr></tpl>',
			'</table>'
		);
		
		if ( getGlobalOptions().region && getGlobalOptions().region.nick == 'perm' ) {
			mesTemplate = new Ext.XTemplate(
				'<table cellpadding="0" cellspacing="0" style="width: 100%;"><tr style="font-family: tahoma; font-size: 10pt; font-weight: bold;">',
				'<td style="padding: 2px; width: 30%;">Код</td>',
				'<td style="padding: 2px; width: 20%;">Возрастная группа</td>',
				'<td style="padding: 2px; width: 30%;">Порядковый № посещения</td>',
				'<td style="padding: 2px; width: 20%;">Норматив</td></tr>',
				'<tpl for="."><tr class="x-combo-list-item" style="white-space: normal; overflow: auto; text-overflow: clip;">',
				'<td style="padding: 2px;">{Mes_Code}&nbsp;</td>',
				'<td style="padding: 2px;">{MesAgeGroup_Name}&nbsp;</td>',
				'<td style="padding: 2px;">{Mes_VizitNumber}&nbsp;</td>',
				'<td style="padding: 2px;">{[twoDecimalsRenderer(values.Mes_KoikoDni)]}&nbsp;</td>',
				'</tr></tpl>',
				'</table>'
			);
		}

		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.doSave({
						ignoreEvnUslugaStomCountCheck: false
					});
				}.createDelegate(this),
				iconCls: 'save16',
				onShiftTabAction: function () {
					var base_form = this.findById('EvnVizitPLStomEditForm').getForm();

					if ( !this.findById('EVPLSEF_EvnUslugaStomPanel').collapsed && this.findById('EVPLSEF_EvnUslugaStomGrid').getStore().getCount() > 0 ) {
						this.findById('EVPLSEF_EvnUslugaStomGrid').getView().focusRow(0);
						this.findById('EVPLSEF_EvnUslugaStomGrid').getSelectionModel().selectFirstRow();
					}
					else if ( !this.findById('EVPLSEF_EvnDiagPLStomSopPanel').collapsed && this.findById('EVPLSEF_EvnDiagPLStomSopGrid').getStore().getCount() > 0 ) {
						this.findById('EVPLSEF_EvnDiagPLStomSopGrid').getView().focusRow(0);
						this.findById('EVPLSEF_EvnDiagPLStomSopGrid').getSelectionModel().selectFirstRow();
					}
					else if ( !this.findById('EVPLSEF_DiagPanel').collapsed && !base_form.findField('Mes_id').disabled ) {
						base_form.findField('Mes_id').focus(true);
					}
					else if ( this.action != 'view' ) {
						if ( !base_form.findField('EvnVizitPLStom_Uet').disabled ) {
							base_form.findField('EvnVizitPLStom_Uet').focus(true);
						}
						else if ( !base_form.findField('EvnVizitPLStom_UetOMS').disabled ) {
							base_form.findField('EvnVizitPLStom_UetOMS').focus(true);
						}
						else if ( !base_form.findField('EvnPLDisp_id').disabled ) {
							base_form.findField('EvnPLDisp_id').focus(true);
						}
						else {
							base_form.findField('DispClass_id').focus(true);
						}
					}
					else {
						this.buttons[this.buttons.length - 1].focus();
					}
				}.createDelegate(this),
				onTabAction: function () {
					this.buttons[this.buttons.length - 1].focus(true);
				}.createDelegate(this),
				tabIndex: TABINDEX_EVPLSEF + 23,
				text: BTN_FRMSAVE
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
					if ( this.action != 'view' ) {
						this.buttons[0].focus();
					}
					else {
						if ( !this.findById('EVPLSEF_EvnUslugaStomPanel').collapsed && this.findById('EVPLSEF_EvnUslugaStomGrid').getStore().getCount() > 0 ) {
							this.findById('EVPLSEF_EvnUslugaStomGrid').getView().focusRow(0);
							this.findById('EVPLSEF_EvnUslugaStomGrid').getSelectionModel().selectFirstRow();
						}
						else if ( !this.findById('EVPLSEF_EvnDiagPLStomSopPanel').collapsed && this.findById('EVPLSEF_EvnDiagPLStomSopGrid').getStore().getCount() > 0 ) {
							this.findById('EVPLSEF_EvnDiagPLStomSopGrid').getView().focusRow(0);
							this.findById('EVPLSEF_EvnDiagPLStomSopGrid').getSelectionModel().selectFirstRow();
						}
					}
				}.createDelegate(this),
				onTabAction: function () {
					if ( this.action != 'view' ) {
						this.findById('EvnVizitPLStomEditForm').getForm().findField('EvnVizitPLStom_setDate').focus(true);
					}
					else {
						if ( !this.findById('EVPLSEF_EvnDiagPLStomSopPanel').collapsed && this.findById('EVPLSEF_EvnDiagPLStomSopGrid').getStore().getCount() > 0 ) {
							this.findById('EVPLSEF_EvnDiagPLStomSopGrid').getView().focusRow(0);
							this.findById('EVPLSEF_EvnDiagPLStomSopGrid').getSelectionModel().selectFirstRow();
						}
						else if ( !this.findById('EVPLSEF_EvnUslugaStomPanel').collapsed && this.findById('EVPLSEF_EvnUslugaStomGrid').getStore().getCount() > 0 ) {
							this.findById('EVPLSEF_EvnUslugaStomGrid').getView().focusRow(0);
							this.findById('EVPLSEF_EvnUslugaStomGrid').getSelectionModel().selectFirstRow();
						}
					}
				}.createDelegate(this),
				tabIndex: TABINDEX_EVPLSEF + 24,
				text: BTN_FRMCANCEL
			}],
			items: [ new sw.Promed.PersonInformationPanelShort({
				id: 'EVPLSEF_PersonInformationFrame',
				region: 'north'
			}),
			new Ext.form.FormPanel({
				autoScroll: true,
				bodyBorder: false,
				bodyStyle: 'padding: 5px 5px 0',
				border: false,
				frame: false,
				id: 'EvnVizitPLStomEditForm',
				labelAlign: 'right',
				labelWidth: 170,
				items: [{
					name: 'accessType',
					value: '',
					xtype: 'hidden'
				}, {
					name:'EvnVizitPLStom_IsPaid',
					xtype:'hidden'
				}, {
					name:'EvnVizitPLStom_IndexRep',
					xtype:'hidden'
				}, {
					name:'EvnVizitPLStom_IndexRepInReg',
					xtype:'hidden'
				}, {
					name: 'EvnVizitPLStom_id',
					value: 0,
					xtype: 'hidden'
				}, {
					name: 'EvnUslugaStom_id',
					value: 0,
					xtype: 'hidden'
				}, {
					name: 'EvnDirection_id',
					value: null,
					xtype: 'hidden'
				}, {
					name: 'TimetableGraf_id',
					value: null,
					xtype: 'hidden'
				}, {
					name: 'EvnPLStom_id',
					value: 0,
					xtype: 'hidden'
				}, {
					name: 'EvnPLStom_setDate',
					xtype: 'hidden'
				}, {
					name: 'EvnPLStom_IsFinish',
					value: 0,
					xtype: 'hidden'
				}, {
					name: 'is_repeat_vizit',
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
				}, {
					name: 'MedPersonal_id',
					value: 0,
					xtype: 'hidden'
				}, {
					name: 'AlertReg_Msg',
					xtype: 'hidden'
				}, {
					name: 'MedPersonal_sid',
					value: 0,
					xtype: 'hidden'
				},  {
					name: 'ResultClass_id',
					value: -1,
					xtype: 'hidden'
				},  {
					fieldLabel: 'Повторная подача',
					listeners: {
						'check': function(checkbox, value) {
							if ( getRegionNick() != 'perm' ) {
								return false;
							}

							var base_form = this.findById('EvnVizitPLStomEditForm').getForm();

							var
								EvnVizitPLStom_IndexRep = parseInt(base_form.findField('EvnVizitPLStom_IndexRep').getValue()),
								EvnVizitPLStom_IndexRepInReg = parseInt(base_form.findField('EvnVizitPLStom_IndexRepInReg').getValue()),
								EvnVizitPLStom_IsPaid = parseInt(base_form.findField('EvnVizitPLStom_IsPaid').getValue());

							var diff = EvnVizitPLStom_IndexRepInReg - EvnVizitPLStom_IndexRep;

							if ( EvnVizitPLStom_IsPaid != 2 || EvnVizitPLStom_IndexRepInReg == 0 ) {
								return false;
							}

							if ( value == true ) {
								if ( diff == 1 || diff == 2 ) {
									EvnVizitPLStom_IndexRep = EvnVizitPLStom_IndexRep + 2;
								}
								else if ( diff == 3 ) {
									EvnVizitPLStom_IndexRep = EvnVizitPLStom_IndexRep + 4;
								}
							}
							else if ( value == false ) {
								if ( diff <= 0 ) {
									EvnVizitPLStom_IndexRep = EvnVizitPLStom_IndexRep - 2;
								}
							}

							base_form.findField('EvnVizitPLStom_IndexRep').setValue(EvnVizitPLStom_IndexRep);

						}.createDelegate(this)
					},
					name: 'EvnVizitPLStom_RepFlag',
					xtype: 'checkbox'
				}, {
					border: false,
					layout: 'column',
					items: [{
						border: false,
						layout: 'form',
						items: [{
							allowBlank: false,
							fieldLabel: 'Дата',
							format: 'd.m.Y',
							id: 'EVPLSEF_EvnVizitPLStom_setDate',
							listeners: {
								'change': function(field, newValue, oldValue) {
									if ( blockedDateAfterPersonDeath('personpanelid', 'EVPLSEF_PersonInformationFrame', field, newValue, oldValue) ) {
										return false;
									}

									var base_form = this.findById('EvnVizitPLStomEditForm').getForm();

									var mdate = new Date(Math.max((typeof this.lastEvnVizitPLStomDate == 'object' ? this.lastEvnVizitPLStomDate : getValidDT(this.lastEvnVizitPLStomDate, '')), newValue));

									var EvnPLStom_setDate = getValidDT(base_form.findField('EvnPLStom_setDate').getValue(), '');
									var xdate = new Date(2016, 0, 1); // для стомат. ТАП Перми поля повяляются с 1 января 2016
									if (getRegionNick() != 'perm') {
										xdate = sw.Promed.EvnPL.getEvnPLStomNewBegDate(); // для стомат ТАП зависит от региона
									}

									if (EvnPLStom_setDate >= xdate) {
										if ( getRegionNick() != 'kareliya' ) {
											base_form.findField('TreatmentClass_id').showContainer();
											base_form.findField('TreatmentClass_id').setAllowBlank(false);
											base_form.findField('TreatmentClass_id').onLoadStore();
										}
										else {
											base_form.findField('TreatmentClass_id').hideContainer();
											base_form.findField('TreatmentClass_id').clearValue();
											base_form.findField('TreatmentClass_id').setAllowBlank(true);
										}
										base_form.findField('MedicalCareKind_id').showContainer();
										base_form.findField('MedicalCareKind_id').setAllowBlank(false);
										this.setDefaultMedicalCareKind();
									} else {
										base_form.findField('TreatmentClass_id').hideContainer();
										base_form.findField('TreatmentClass_id').clearValue();
										base_form.findField('TreatmentClass_id').setAllowBlank(true);
										base_form.findField('MedicalCareKind_id').hideContainer();
										base_form.findField('MedicalCareKind_id').clearValue();
										base_form.findField('MedicalCareKind_id').setAllowBlank(true);
									}

									base_form.findField('PersonDisp_id').lastQuery = 'This query sample that is not will never appear';
									base_form.findField('PersonDisp_id').getStore().removeAll();
									base_form.findField('PersonDisp_id').clearValue();
									base_form.findField('PersonDisp_id').getStore().baseParams.onDate = Ext.util.Format.date(newValue, 'd.m.Y');

									this.loadMesCombo({
										p: 'change_EvnVizit_setDate',
										Diag_id: base_form.findField('Diag_id').getValue(),
										EvnVizit_setDate: newValue,
										LpuSection_id: base_form.findField('LpuSection_id').getValue()
									});

									if (!this.loadingInProgress && getRegionNick() == 'ekb' && !this.isRepeatVizit) {
										var mes_id = base_form.findField('MesEkb_id').getValue();
										this.loadMesEkbCombo({callback: function() {
											var index = base_form.findField('MesEkb_id').getStore().findBy(function(rec){
												return rec.get('Mes_id') == mes_id
											});
											if (index >= 0) {
												base_form.findField('MesEkb_id').setValue(mes_id);
											} else {
												base_form.findField('MesEkb_id').setValue(null);
											}
										}});
									}

									var index;
									var lpu_section_id = base_form.findField('LpuSection_id').getValue();
									var med_staff_fact_id = base_form.findField('MedStaffFact_id').getValue();
									var med_staff_fact_sid = base_form.findField('MedStaffFact_sid').getValue();
									var ServiceType_id = base_form.findField('ServiceType_id').getValue();
									var uslugacomplex_uid = base_form.findField('UslugaComplex_uid').getValue();

									this.ToothMapPanel.applyParams(
										base_form.findField('Person_id').getValue(),
										base_form.findField('EvnVizitPLStom_id').getValue(),
										field.getRawValue()
									);
									// Фильтр на поле ServiceType_id
									// https://redmine.swan.perm.ru/issues/17571
									base_form.findField('ServiceType_id').clearValue();
									base_form.findField('ServiceType_id').getStore().clearFilter();
									base_form.findField('ServiceType_id').lastQuery = '';


									if (
										!Ext.isEmpty(base_form.findField('LpuDispContract_id').getValue())
										&& !Ext.isEmpty(newValue)
										&& !(
											base_form.findField('LpuDispContract_id').getFieldValue('LpuDispContract_setDate') <= newValue
											&& Ext.isEmpty(base_form.findField('LpuDispContract_id').getFieldValue('LpuDispContract_disDate')) || base_form.findField('LpuDispContract_id').getFieldValue('LpuDispContract_disDate') >= newValue
										)
									) {
										base_form.findField('LpuDispContract_id').clearValue();
									}
									base_form.findField('LpuDispContract_id').getStore().baseParams.onDate = Ext.util.Format.date(newValue, 'd.m.Y');
									base_form.findField('LpuDispContract_id').lastQuery = 'This query sample that is not will never appear';
									base_form.findField('LpuDispContract_id').getStore().baseParams.query = '';

									if ( !Ext.isEmpty(newValue) ) {
										base_form.findField('ServiceType_id').getStore().filterBy(function(rec) {
											return (
												(Ext.isEmpty(rec.get('ServiceType_begDate')) || rec.get('ServiceType_begDate') <= newValue)
												&& (Ext.isEmpty(rec.get('ServiceType_endDate')) || rec.get('ServiceType_endDate') >= newValue)
											);
										});
									}

									index = base_form.findField('ServiceType_id').getStore().findBy(function(rec) {
										return (rec.get('ServiceType_id') == ServiceType_id);
									});

									if ( index >= 0 ) {
										base_form.findField('ServiceType_id').setValue(ServiceType_id);
									}

									base_form.findField('ServiceType_id').fireEvent('change', base_form.findField('ServiceType_id'), base_form.findField('ServiceType_id').getValue());

									this.filterVizitTypeCombo();

									// Устанавливаем дату для кодов посещений
									var uslugaComplexDateChanged = false;

									if ( base_form.findField('UslugaComplex_uid').getStore().baseParams.UslugaComplex_Date != Ext.util.Format.date(newValue, 'd.m.Y') ) {
										uslugaComplexDateChanged = true;
										base_form.findField('UslugaComplex_uid').setUslugaComplexDate(Ext.util.Format.date(newValue, 'd.m.Y'));
									}

									if ( getRegionNick().inlist([ 'pskov' ]) && uslugaComplexDateChanged == true ) {
										base_form.findField('UslugaComplex_uid').clearValue();
										base_form.findField('UslugaComplex_uid').lastQuery = 'This query sample that is not will never appear';
										base_form.findField('UslugaComplex_uid').getStore().removeAll();
										base_form.findField('UslugaComplex_uid').getStore().baseParams.query = '';
									}

									if ( getRegionNick().inlist([ 'ekb' ]) && uslugaComplexDateChanged == true ) {
										//base_form.findField('UslugaComplex_uid').clearValue();
										base_form.findField('UslugaComplex_uid').lastQuery = 'This query sample that is not will never appear';
										base_form.findField('UslugaComplex_uid').getStore().removeAll();
										base_form.findField('UslugaComplex_uid').getStore().baseParams.query = '';
										var xdate = new Date(2015,0,1);
										if (newValue >= xdate) {
											base_form.findField('UslugaComplex_uid').getStore().baseParams.UslugaComplexPartition_CodeList = Ext.util.JSON.encode([302]);
										} else {
											base_form.findField('UslugaComplex_uid').getStore().baseParams.UslugaComplexPartition_CodeList = Ext.util.JSON.encode([300,301]);
										}
									}

									if ( this.action != 'view' ) {
										base_form.findField('LpuSection_id').enable();
										base_form.findField('MedStaffFact_id').enable();
									}

									base_form.findField('LpuSection_id').clearValue();
									base_form.findField('MedStaffFact_id').clearValue();
									base_form.findField('MedStaffFact_sid').clearValue();

									var lpu_section_filter_params = {
										allowLowLevel: 'yes',
										isStom: true,
										regionCode: getGlobalOptions().region.number
									};

									var medstafffact_filter_params = {
										allowLowLevel: 'yes',
										EvnClass_SysNick: 'EvnVizit',
										//isDoctor: true, // только врачи
										isStom: true,
										regionCode: getGlobalOptions().region.number
									};

									var mid_medstafffact_filter_params = {
										allowLowLevel: 'yes',
										isMidMedPersonal: true, // Средний мед. персонал + зубные врачи
										isStom: true,
										regionCode: getGlobalOptions().region.number
									};

									if ( !Ext.isEmpty(newValue) ) {
										lpu_section_filter_params.onDate = Ext.util.Format.date(newValue, 'd.m.Y');
										medstafffact_filter_params.onDate = Ext.util.Format.date(newValue, 'd.m.Y');
										mid_medstafffact_filter_params.onDate = Ext.util.Format.date(newValue, 'd.m.Y');
										current_window.setMKB();
									}

									base_form.findField('LpuSection_id').getStore().removeAll();
									base_form.findField('MedStaffFact_id').getStore().removeAll();
									base_form.findField('MedStaffFact_sid').getStore().removeAll();

									// сначала фильтруем средний медперсонал, 
									// потому что для него не нужен фильтр по месту работы текущего пользователя
									
									setMedStaffFactGlobalStoreFilter(mid_medstafffact_filter_params);

									base_form.findField('MedStaffFact_sid').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));

									if ( this.action == 'add' ) {
										// Фильтр на конкретное место работы
										if ( !Ext.isEmpty(this.userMedStaffFact.LpuSection_id) && !Ext.isEmpty(this.userMedStaffFact.MedStaffFact_id) ) {
											lpu_section_filter_params.id = this.userMedStaffFact.LpuSection_id;
											medstafffact_filter_params.id = this.userMedStaffFact.MedStaffFact_id;
										}
									}

									setLpuSectionGlobalStoreFilter(lpu_section_filter_params);
									setMedStaffFactGlobalStoreFilter(medstafffact_filter_params);

									base_form.findField('LpuSection_id').getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));
									base_form.findField('MedStaffFact_id').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));

									if ( base_form.findField('LpuSection_id').getStore().getById(lpu_section_id) ) {
										base_form.findField('LpuSection_id').setValue(lpu_section_id);
										base_form.findField('LpuSection_id').fireEvent('change', base_form.findField('LpuSection_id'), lpu_section_id);
									}

									if ( base_form.findField('MedStaffFact_id').getStore().getById(med_staff_fact_id) ) {
										base_form.findField('MedStaffFact_id').setValue(med_staff_fact_id);
										base_form.findField('MedStaffFact_id').fireEvent('change', base_form.findField('MedStaffFact_id'), base_form.findField('MedStaffFact_id').getValue());
									}

									if ( base_form.findField('MedStaffFact_sid').getStore().getById(med_staff_fact_sid) ) {
										base_form.findField('MedStaffFact_sid').setValue(med_staff_fact_sid);
									}

									/**
									 *	если форма открыта на добавление или редактирование и задано отделение и 
									 *	место работы или задан список мест работы, то не даем редактировать вообще
									 */
									if ( this.action.inlist([ 'add', 'edit' ]) && !Ext.isEmpty(this.userMedStaffFact.LpuSection_id) && !Ext.isEmpty(this.userMedStaffFact.MedStaffFact_id) ) {
										base_form.findField('LpuSection_id').disable();
										if (this.action == 'add' || this.userMedStaffFact.MedStaffFact_id == base_form.findField('MedStaffFact_id').getValue()) {
											base_form.findField('MedStaffFact_id').disable();
										}

										// Если форма открыта на добавление...
										if ( this.action == 'add' ) {
											// ... то устанавливаем заданные значения отделения и места работы, если они есть в списке
											index = base_form.findField('LpuSection_id').getStore().findBy(function(rec) {
												return (rec.get('LpuSection_id') == this.userMedStaffFact.LpuSection_id);
											}.createDelegate(this));

											if ( index >= 0 ) {
												base_form.findField('LpuSection_id').setValue(this.userMedStaffFact.LpuSection_id);
												base_form.findField('LpuSection_id').fireEvent('change', base_form.findField('LpuSection_id'), this.userMedStaffFact.LpuSection_id);
											}

											index = base_form.findField('MedStaffFact_id').getStore().findBy(function(rec) {
												return (rec.get('MedStaffFact_id') == this.userMedStaffFact.MedStaffFact_id);
											}.createDelegate(this));

											if ( index >= 0 ) {
												base_form.findField('MedStaffFact_id').setValue(this.userMedStaffFact.MedStaffFact_id);
												base_form.findField('MedStaffFact_id').fireEvent('change', base_form.findField('MedStaffFact_id'), base_form.findField('MedStaffFact_id').getValue());
											}
										}
									}

									if (getRegionNick() == 'perm') {
										if (newValue && newValue < new Date(2015,0,1) && base_form.findField('Mes_id').getValue()) {
											Ext.getCmp('EVPLSEF_addByMesButton').enable();
										} else {
											Ext.getCmp('EVPLSEF_addByMesButton').disable();
										}
									}

									if (getRegionNick() == 'ekb') {
										this.reloadUslugaComplexField(null, uslugacomplex_uid);
									}
									else {
										this.reloadUslugaComplexField();
									}
									this.evnDirectionAllInfoPanel.onLoadForm(this);
									base_form.findField('Diag_id').setFilterByDate(newValue);
									
									if (getRegionNick() == 'perm' && mdate >= new Date(2015,10,1)) {
										base_form.findField('UslugaComplexTariff_id').showContainer();
										base_form.findField('EvnUslugaStom_UED').showContainer();
									} else {
										base_form.findField('UslugaComplexTariff_id').hideContainer();
										base_form.findField('EvnUslugaStom_UED').hideContainer();
									}
									if (getRegionNick() == 'pskov' && mdate >= new Date(2018,0,1)) {
										base_form.findField('EvnUslugaStom_UED').showContainer();
									}
								}.createDelegate(this),
								'keydown': function(inp, e) {
									if ( e.getKey() == Ext.EventObject.TAB && e.shiftKey == true ) {
										e.stopEvent();
										this.buttons[this.buttons.length - 1].focus();
									}
								}.createDelegate(this)
							},
							name: 'EvnVizitPLStom_setDate',
							plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
							selectOnFocus: true,
							tabIndex: TABINDEX_EVPLSEF + 1,
							width: 100,
							xtype: 'swdatefield'
						}]
					}, {
						border: false,
						labelWidth: 50,
						layout: 'form',
						items: [{
							fieldLabel: 'Время',
							listeners: {
								'keydown': function (inp, e) {
									if ( e.getKey() == Ext.EventObject.F4 ) {
										e.stopEvent();
										inp.onTriggerClick();
									}
								}
							},
							name: 'EvnVizitPLStom_setTime',
							onTriggerClick: function() {
								var base_form = this.findById('EvnVizitPLStomEditForm').getForm();
								var time_field = base_form.findField('EvnVizitPLStom_setTime');

								if ( time_field.disabled ) {
									return false;
								}

								setCurrentDateTime({
									callback: function() {
										base_form.findField('EvnVizitPLStom_setDate').fireEvent('change', base_form.findField('EvnVizitPLStom_setDate'), base_form.findField('EvnVizitPLStom_setDate').getValue());
									},
									dateField: base_form.findField('EvnVizitPLStom_setDate'),
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
							tabIndex: TABINDEX_EVPLSEF + 2,
							validateOnBlur: false,
							width: 60,
							xtype: 'swtimefield'
						}]
					}, {
						border: false,
						labelWidth: 165,
						layout: 'form',
						items: [{
							fieldLabel: 'Первично в текущем году',
							hiddenName: 'EvnVizitPLStom_IsPrimaryVizit',
							tabIndex: TABINDEX_EVPLSEF + 3,
							validateOnBlur: false,
							width: 65,
							listeners: {
								'change': function(combo, newValue, oldValue) {
									current_window.reloadUslugaComplexField();
								}
							},
							xtype: 'swyesnocombo'
						}]
					}]
				}, {
					allowBlank: false,
					hiddenName: 'LpuSection_id',
					id: 'EVPLSEF_LpuSectionCombo',
					lastQuery: '',
					listWidth: 650,
					linkedElements: [
						'EVPLSEF_MedPersonalCombo'
					],
					tabIndex: TABINDEX_EVPLSEF + 4,
					width: 450,
					xtype: 'swlpusectionglobalcombo'
				}, {
					allowBlank: false,
					dateFieldId: 'EVPLSEF_EvnVizitPLStom_setDate',
					enableOutOfDateValidation: true,
					hiddenName: 'MedStaffFact_id',
					id: 'EVPLSEF_MedPersonalCombo',
					lastQuery: '',
					listWidth: 650,
					parentElementId: 'EVPLSEF_LpuSectionCombo',
					tabIndex: TABINDEX_EVPLSEF + 5,
					width: 450,
					xtype: 'swmedstafffactglobalcombo'
				}, {
					allowBlank: getRegionNick().inlist([ 'ufa', 'ekb' ]),
					disabled: getRegionNick().inlist([ 'ufa', 'ekb' ]),
					fieldLabel: langs('Профиль'),
					id: 'EVPLSEF_LpuSectionProfile',
					listeners: {
						'change': function(combo, newValue, oldValue) {
							current_window.reloadUslugaComplexField();

							if(oldValue && newValue && current_window.errorControlCodaVisits()){
								sw.swMsg.show({
									buttons: Ext.Msg.YESNO,
									fn: function(buttonId, text, obj) {
										if ( buttonId == 'no' ) {
											this.setValue(oldValue);
										}
									}.createDelegate(combo),
									icon: Ext.MessageBox.QUESTION,
									msg: langs('Профиль отделения текущего посещения должен соответствовать профилю отделения других посещений в этом ТАП. Продолжить ?'),
									title: langs('Предупреждение')
								});
							}
						}
					},
					hiddenName: 'LpuSectionProfile_id',
					listWidth: 600,
					tabIndex: TABINDEX_EVPLSEF + 9,
					width: 450,
					xtype: 'swlpusectionprofiledopremotecombo'
				}, {
					fieldLabel: 'Сред. м./персонал',
					hiddenName: 'MedStaffFact_sid',
					id: 'EVPLSEF_MidMedPersonalCombo',
					listWidth: 650,
					//parentElementId: 'EVPLSEF_LpuSectionCombo',
					tabIndex: TABINDEX_EVPLSEF + 6,
					width: 450,
					xtype: 'swmedstafffactglobalcombo'
				}, {
					allowBlank: false,
					fieldLabel: getRegionNick() == 'kz' ? 'Повод обращения' : 'Вид обращения',
					hiddenName: 'TreatmentClass_id',
					comboSubject: 'TreatmentClass',
					xtype: 'swcommonsprcombo',
					tabIndex: TABINDEX_EVPLSEF + 6,
					width: 300,
					onLoadStore: function() {
						if (getRegionNick() == 'kz') return false;
						this.getStore().clearFilter();
						this.lastQuery = '';
						var base_form = current_window.findById('EvnVizitPLStomEditForm').getForm();
						if (!Ext.isEmpty(base_form.findField('Diag_newid').getFieldValue('Diag_Code'))) {
							if (getRegionNick() == 'kareliya') {
								current_window.setTreatmentClass();
								return false;
							}
						}

						current_window.filterTreatmentClass();
					},
					listeners: {
						'change': function (combo, newValue, oldValue) {
							if (getRegionNick() == 'kareliya') return false; // https://redmine.swan.perm.ru/issues/83930
							if (Ext.isEmpty(newValue)) return false;
							var base_form = this.findById('EvnVizitPLStomEditForm').getForm();
							current_window.reloadUslugaComplexField();
							// Фильтруем места
							var servicetype_combo = base_form.findField('ServiceType_id');
							var servicetype_id = servicetype_combo.getValue();
							servicetype_combo.getStore().filterBy(function(rec) {
								var index = swTreatmentClassServiceTypeGlobalStore.findBy(function(r) {
									return (r.get('TreatmentClass_id') == newValue && r.get('ServiceType_id') == rec.get('ServiceType_id'));
								});
								return (index != -1);
							});
							if (servicetype_combo.getStore().getCount() == 0) {
								servicetype_combo.getStore().clearFilter();
							}
							if (servicetype_combo.getStore().getCount() == 1) {
								var servicetype_id = servicetype_combo.getStore().getAt(0).get('ServiceType_id');
								servicetype_combo.setValue(servicetype_id);
							}
							if (servicetype_id && !servicetype_combo.findRecord('ServiceType_id', servicetype_id)) {
								servicetype_combo.clearValue();
							}
							servicetype_combo.fireEvent('change', servicetype_combo, servicetype_combo.getValue());

							this.filterVizitTypeCombo();
							
							if (getRegionNick()=='kz'){
								var index = combo.getStore().findBy(function(rec) {
									return (rec.get(combo.valueField) == newValue);
								});
								
								var treatmentClassId = combo.getStore().getAt(index).get('TreatmentClass_id');
								
								base_form.findField('VizitActiveType_id').setAllowBlank(!treatmentClassId.inlist([22,30]));
								
								if (treatmentClassId.inlist([22,30])) {
									var vizitActiveTypeId = base_form.findField('VizitActiveType_id').getValue();
									
									base_form.findField('VizitActiveType_id').getStore().filterBy(function(rec){
										return rec.get('TreatmentClass_id') == treatmentClassId;
									});
									
									index = base_form.findField('VizitActiveType_id').getStore().findBy(function(rec) {
										return rec.get('VizitActiveType_id') == vizitActiveTypeId;
									});
									
									if (index == -1) {
										vizitActiveTypeId = (treatmentClassId == 22)?3:8;
										base_form.findField('VizitActiveType_id').setValue(vizitActiveTypeId);
									}
									
									base_form.findField('VizitActiveType_id').showContainer();
								} else {
									base_form.findField('VizitActiveType_id').clearValue();
									base_form.findField('VizitActiveType_id').clearFilter();
									base_form.findField('VizitActiveType_id').hideContainer();
								}
								
								index = swTreatmentClassServiceTypeGlobalStore.findBy(function(rec) {
									return rec.get('TreatmentClass_id') == treatmentClassId;
								});
								
								var serviceTypeId = swTreatmentClassServiceTypeGlobalStore.getAt(index).get('ServiceType_id');
								
								base_form.findField('ServiceType_id').fireEvent('change',base_form.findField('ServiceType_id'),serviceTypeId);
								base_form.findField('ServiceType_id').setValue(serviceTypeId);
							}
							
							this.getFinanceSource();

							base_form.findField('PersonDisp_id').setAllowBlank(!(getRegionNick().inlist(['krasnoyarsk','vologda']) && combo.getFieldValue('TreatmentClass_Code') == '1.3'));
						}.createDelegate(this)
					}
				}, {
					border: false,
					layout: 'form',
					items: [{
						comboSubject: 'VizitActiveType',
						fieldLabel: 'Вид активного посещения',
						lastQuery: '',
						moreFields: [
							{ name: 'TreatmentClass_id', mapping: 'TreatmentClass_id' }
						],
						tabIndex: TABINDEX_EEPLEF + 30,
						width: 300,
						xtype: 'swcommonsprcombo'
					}]
				}, {
					allowBlank: false,
					hiddenName: 'ServiceType_id',
					listeners: {
						'change': function(combo, newValue, oldValue) {
							var base_form = this.findById('EvnVizitPLStomEditForm').getForm();
							var record = combo.getStore().getById(newValue);

							if ( !record ) {
								return false;
							}

							if ( record.get('ServiceType_SysNick') == 'neotl' ) {
								base_form.findField('PayType_id').setFieldValue('PayType_SysNick', getPayTypeSysNickOms());
								base_form.findField('PayType_id').fireEvent('change',base_form.findField('PayType_id'),base_form.findField('PayType_id').getValue());
							}
						}.createDelegate(this)
					},
					tabIndex: TABINDEX_EVPLSEF + 7,
					width: 300,
					xtype: 'swservicetypecombo'
				}, {
					allowBlank: false,
					EvnClass_id: 13,
					listeners: {
						'change': function(combo, newValue, oldValue) {
							var base_form = this.findById('EvnVizitPLStomEditForm').getForm();
							var prof_goal_combo = base_form.findField('ProfGoal_id');

							if ( newValue != null && newValue.toString().length > 0 ) {
								var record = combo.getStore().getById(newValue);

								if ( record ) {
									if ( record.get('VizitType_SysNick') == 'prof' ) {
										prof_goal_combo.enable();
									}
									else {
										prof_goal_combo.disable();
										prof_goal_combo.clearValue();
									}
								}
							}

							else {
								prof_goal_combo.disable();
								prof_goal_combo.clearValue();
							}
						}.createDelegate(this)
					},
					tabIndex: TABINDEX_EVPLSEF + 8,
					width: 300,
					xtype: 'swvizittypecombo'
				}, {
					layout: 'column',
					border: false,
					items:[
						{
							layout: 'form',
							border: false,
							items: [{
								allowBlank: false,
								useCommonFilter: true,
								tabIndex: TABINDEX_EVPLSEF + 9,
								width: 300,
								fieldLabel: getRegionNick() == 'kz' ? 'Источник финансирования' : 'Вид оплаты',
								xtype: 'swpaytypecombo',
								onLoadStore: function() {
									var base_form = current_window.findById('EvnVizitPLStomEditForm').getForm();
									var vizitTypeCombo = base_form.findField('VizitType_id'),
										paytypecombo = base_form.findField('PayType_id');
									
									if (getRegionNick() == 'kareliya') {
										vizitTypeCombo.onLoadStore();
										paytypecombo.fireEvent('change', paytypecombo, 51, 51);
									}
								},
								listeners: {
									'change': function(combo, newValue, oldValue) {
										var pay_type = combo.getStore().getById(newValue);
										var base_form = current_window.findById('EvnVizitPLStomEditForm').getForm();
										if ( getRegionNick() == 'ekb' ) {
											this.filterLpuSectionProfile();
											this.setDefaultMedicalCareKind();
										}
										this.filterVizitTypeCombo();
										if (getRegionNick()=='kz') {
											base_form.findField('isPaidVisit').setValue(pay_type && pay_type.get('PayType_id')=='153');
										}
										this.loadUslugaComplexTariffCombo();
									}.createDelegate(this)
								}
							}]
						},
						{
							layout: 'form',
							border: false,
							hidden: getRegionNick()!='kz',
							style: 'margin: 3px 0 0 10px;',
							items: [{
								xtype: 'checkbox',
								hideLabel: true,
								boxLabel: 'Платное посещение',
								name: 'isPaidVisit',
								handler: function() {
									var base_form = this.findById('EvnVizitPLStomEditForm').getForm();
									
									if (base_form.findField('isPaidVisit').getValue()) {
										base_form.findField('PayType_id').setValue('153');
										base_form.findField('PayType_id').fireEvent('change',base_form.findField('PayType_id'),'153');
									} else {
										this.getFinanceSource();
									}
								}.createDelegate(this)
							}]
						}
					]
				}, {
					fieldLabel: 'Тип оплаты',
					width: 300,
					comboSubject: 'PayTypeKAZ',
					disabled: true,
					prefix: 'r101_',
					xtype: 'swcommonsprcombo'
				}, {
					allowBlank: false,
					disabled: getRegionNick().inlist([ 'ufa', 'kareliya', 'ekb' ]),
					tabIndex: TABINDEX_EVPLSEF + 9,
					loadParams: {
						params: (getRegionNick() == 'ekb' ? {} : {where: "where MedicalCareKind_Code in ('11','12','13','4')"})
					},
					fieldLabel: 'Вид мед. помощи',
					hiddenName: 'MedicalCareKind_id',
					width: 300,
					xtype: 'swmedicalcarekindfedcombo'
				}, {
					allowBlank: getRegionNick() != 'ekb' && sw.Promed.EvnVizitPLStom.isAllowBlankVizitCode(),
					fieldLabel: langs('Код') + ' ' + (getRegionNick() == 'kz' ? langs('Услуги').toLowerCase() + ' ' : '') + langs('посещения'),
					hiddenName: 'UslugaComplex_uid',
					to: 'EvnVizitPLStom',
					listWidth: 600,
					listeners: {
						'change': function(combo, newValue, oldValue) {
							this.setLpuSectionProfile();
							this.loadUslugaComplexTariffCombo();

							var base_form = this.findById('EvnVizitPLStomEditForm').getForm(),
								ResultClass_id = base_form.findField('ResultClass_id').getValue(),
								EvnPLStom_IsFinish = base_form.findField('EvnPLStom_IsFinish').getValue();

							if (getRegionNick() == 'ekb') {
								var mesekb_combo = base_form.findField('MesEkb_id');
								mesekb_combo.lastQuery = 'This query sample that is not will never appear';
								mesekb_combo.getStore().removeAll();
								mesekb_combo.getStore().baseParams.UslugaComplex_id = newValue;
								mesekb_combo.getStore().baseParams.query = '';
							} else if (getRegionNick() == 'ufa' && EvnPLStom_IsFinish == 2 && ((combo.getFieldValue('UslugaComplex_Code')).substr(-3)).inlist(['865', '866', '836']) && (!ResultClass_id.inlist(['1', '2', '3', '4', '5', '6', '7', '9', '11', '16']) || Ext.isEmpty(ResultClass_id))) {
								sw.swMsg.alert('Сообщение', 'Услуга не соответствует результату посещения. Если три последние символа кода посещения равны 865, 866 или 836 то код результата лечения должен быть равен 1, 2, 3, 4, 5, 6, 7, 9, 11, 16 или отсутствовать.');
								combo.setValue('');
								return false;
							}

							var
								usluga_complex_code = combo.getFieldValue('UslugaComplex_Code'),
								EvnVizitPLStom_setDate = base_form.findField('EvnVizitPLStom_setDate').getValue(),
								DateX20180401 = new Date(2018, 3, 1);

							if (
								!Ext.isEmpty(usluga_complex_code) && (usluga_complex_code.substr(-3, 3).inlist(['805'])) && getRegionNick().inlist(['ufa'])
							) {
								base_form.findField('DispProfGoalType_id').setAllowBlank(false);
							}
							else {
								base_form.findField('DispProfGoalType_id').setAllowBlank(true);
							}

							// https://redmine.swan.perm.ru/issues/130520
							if (
								!Ext.isEmpty(usluga_complex_code)
								&& usluga_complex_code.substr(-3, 3).inlist([ '805' ])
								&& EvnVizitPLStom_setDate >= DateX20180401
							) {
								base_form.findField('HealthKind_id').setAllowBlank(false);

								if ( this.action != 'view' ) {
									base_form.findField('HealthKind_id').enable();
								}
							}
							else {
								base_form.findField('HealthKind_id').setAllowBlank(true);
								base_form.findField('HealthKind_id').clearValue();
								base_form.findField('HealthKind_id').disable();
							}

							if (getRegionNick() == 'kz') {
								var pay_type_combo = base_form.findField('PayTypeKAZ_id');
								var uslugacomplex_attributelist = combo.getFieldValue('UslugaComplex_AttributeList');

								if (uslugacomplex_attributelist && !!uslugacomplex_attributelist.split(',').find(function(el){return el == 'Kpn'})) {
									pay_type_combo.setValue(1);
								}
								else if (uslugacomplex_attributelist && uslugacomplex_attributelist.indexOf('IsNotKpn') >= 0) {
									pay_type_combo.setValue(2);
								}
								else {
									pay_type_combo.setValue('');
								}
								this.getFinanceSource();
							}
							if(getRegionNick() == 'perm' && newValue) {
								var ajax_params = {
									LpuSection_id: base_form.findField('LpuSection_id').getValue(),
									PayType_id: base_form.findField('PayType_id').getValue(),
									Person_id: base_form.findField('Person_id').getValue(),
									UslugaComplexTariff_Date: base_form.findField('EvnPLStom_setDate').getValue(),
									UslugaComplex_id: newValue
								};
								Ext.Ajax.request({
									url: '/?c=Usluga&m=loadUslugaComplexTariffList',
									params: ajax_params,
									success: function (response, options) {
										var response_obj = Ext.util.JSON.decode(response.responseText);
										if (response_obj && response_obj.length == 1) {
											base_form.findField('UslugaComplexTariff_id').setFieldValue('UslugaComplexTariff_id', response_obj[0].UslugaComplexTariff_id);
										}
									}
								});
							}
						}.createDelegate(this)
					},
					tabIndex: TABINDEX_EVPLSEF + 10,
					width: 450,
					xtype: 'swuslugacomplexnewcombo'
				}, {
					comboSubject: 'UslugaMedType',
					enableKeyEvents: true,
					hidden: getRegionNick() !== 'kz',
					fieldLabel: langs('Вид услуги'),
					hiddenName: 'UslugaMedType_id',
					allowBlank: getRegionNick() !== 'kz',
					lastQuery: '',
					tabIndex: TABINDEX_EVPLSEF + 11,
					typeCode: 'int',
					width: 450,
					xtype: 'swcommonsprcombo'
				}, {
					allowBlank: true,
					hiddenName: 'UslugaComplexTariff_id',
					isStom: true,
					listeners: {
						'change': function (combo, newValue, oldValue) {
							var index = combo.getStore().findBy(function(rec) {
								return (rec.get(combo.valueField) == newValue);
							});

							combo.fireEvent('select', combo, combo.getStore().getAt(index));

							return true;
						}.createDelegate(this),
						'select': function (combo, record) {
							var base_form = this.findById('EvnVizitPLStomEditForm').getForm();

							if ( record ) {
								if ( !Ext.isEmpty(record.get(combo.valueField)) ) {
									combo.setRawValue(record.get('UslugaComplexTariff_Code') + ". " + record.get('UslugaComplexTariff_Name'));
								}
								
								base_form.findField('EvnUslugaStom_UED').setValue(record.get('UslugaComplexTariff_UED'));
							}
							else {
								base_form.findField('EvnUslugaStom_UED').setValue('');
							}

							return true;
						}.createDelegate(this)
					},
					listWidth: 600,
					tabIndex: TABINDEX_EVPLSEF + 10,
					width: 450,
					xtype: 'swuslugacomplextariffcombo'
				}, {
					allowDecimals: true,
					allowNegative: false,
					disabled: true,
					enableKeyEvents: true,
					fieldLabel: 'УЕТ врача',
					name: 'EvnUslugaStom_UED',
					tabIndex: TABINDEX_EVPLSEF + 10,
					xtype: 'numberfield'
				}, {
					fieldLabel: 'По договору',
					width: 250,
					listWidth: 700,
					hiddenName: 'LpuDispContract_id',
					tabIndex: TABINDEX_EVPLSEF + 10,
					xtype: 'swlpudispcontractcombo'
				}, {
					border: false,
					layout: 'form',
					hidden: (getRegionNick() != 'ekb'),
					items: [{
						fieldLabel: 'МЭС',
						hiddenName: 'MesEkb_id',
						listeners: {
							'keydown': function(inp, e) {
								switch ( e.getKey() ) {
									case Ext.EventObject.TAB:
										var base_form = this.findById('EvnVizitPLStomEditForm').getForm();

										if ( e.shiftKey == false ) {
											e.stopEvent();

											if ( !this.findById('EVPLSEF_EvnDiagPLStomSopPanel').collapsed && this.findById('EVPLSEF_EvnDiagPLStomSopGrid').getStore().getCount() > 0 ) {
												this.findById('EVPLSEF_EvnDiagPLStomSopGrid').getView().focusRow(0);
												this.findById('EVPLSEF_EvnDiagPLStomSopGrid').getSelectionModel().selectFirstRow();
											}
											else if ( !this.findById('EVPLSEF_EvnUslugaStomPanel').collapsed && this.findById('EVPLSEF_EvnUslugaStomGrid').getStore().getCount() > 0 ) {
												this.findById('EVPLSEF_EvnUslugaStomGrid').getView().focusRow(0);
												this.findById('EVPLSEF_EvnUslugaStomGrid').getSelectionModel().selectFirstRow();
											}
											else if ( this.action == 'view' ) {
												this.buttons[this.buttons.length - 1].focus();
											}
											else {
												this.buttons[0].focus();
											}
										}
										break;
								}
							}.createDelegate(this),
							'change': function(combo, newValue, oldValue) {
								var base_form = this.findById('EvnVizitPLStomEditForm').getForm();

								base_form.findField('UslugaComplex_uid').lastQuery = 'This query sample that is not will never appear';
								base_form.findField('UslugaComplex_uid').getStore().removeAll();
								base_form.findField('UslugaComplex_uid').getStore().baseParams.Mes_id = newValue;
								base_form.findField('UslugaComplex_uid').getStore().baseParams.query = '';
							}.createDelegate(this),
							'select': function (combo, record) {
								var addByMesBtn = this.findById('EVPLSEF_EvnUslugaStomGrid').getTopToolbar().items.items[4];
								addByMesBtn.setDisabled(this.action == 'view' || !combo.getValue());
								return true;
							}.createDelegate(this)
						},
						tabIndex: TABINDEX_EVPLSEF + 10,
						width: 450,
						xtype: 'swmesekbcombo'
					}]
				}, {
					allowBlank: true,
					fieldLabel: 'Диагноз',
					hiddenName: 'Diag_newid',
					listeners: {
						'change': function() {
							current_window.filterTreatmentClass();
							if ( getRegionNick() == 'ekb' ) {
								current_window.setDefaultMedicalCareKind();
							}
						}
					},
					tabIndex: TABINDEX_EVPLSEF + 10,
					width: 450,
					xtype: 'swdiagdeseasecombo'
				}, {
					allowDecimals: false,
					allowNegative: false,
					enableKeyEvents: true,
					fieldLabel: 'Время приема (мин)',
					name: 'EvnVizitPLStom_Time',
					tabIndex: TABINDEX_EVPLSEF + 11,
					width: 70,
					xtype: 'numberfield'
				}, {
					comboSubject: 'VizitClass',
					fieldLabel: 'Прием',
					listeners: {
						'change': function(combo, newValue, oldValue) {
							current_window.reloadUslugaComplexField();
						}
					},
					tabIndex: TABINDEX_EVPLSEF + 12,
					width: 100,
					xtype: 'swcommonsprcombo'
				}, {
					enableKeyEvents: true,
					hiddenName: 'ProfGoal_id',
					tabIndex: TABINDEX_EVPLSEF + 13,
					width: 450,
					xtype: 'swprofgoalcombo'
				}, {
					comboSubject: 'DispClass',
					enableKeyEvents: true,
					fieldLabel: 'В рамках дисп./мед.осмотра',
					hiddenName: 'DispClass_id',
					lastQuery: '',
					listeners: {
						'change': function(combo, newValue, oldValue) {
							var index = combo.getStore().findBy(function(rec) {
								return (rec.get('DispClass_id') == newValue);
							});

							combo.fireEvent('select', combo, combo.getStore().getAt(index));
						}.createDelegate(this),
						'select': function(combo, record, idx) {
							var base_form = this.findById('EvnVizitPLStomEditForm').getForm();

							var EvnPLDisp_id = base_form.findField('EvnPLDisp_id').getValue();

							if ( typeof record == 'object' && !Ext.isEmpty(record.get('DispClass_id')) ) {
								base_form.findField('EvnPLDisp_id').enable();

								if (
									base_form.findField('EvnPLDisp_id').DispClass_id != record.get('DispClass_id')
									|| base_form.findField('EvnPLDisp_id').Person_id != base_form.findField('Person_id').getValue()
								) {
									base_form.findField('EvnPLDisp_id').clearValue();
									base_form.findField('EvnPLDisp_id').getStore().removeAll();

									base_form.findField('EvnPLDisp_id').DispClass_id = record.get('DispClass_id');
									base_form.findField('EvnPLDisp_id').Person_id = base_form.findField('Person_id').getValue();

									base_form.findField('EvnPLDisp_id').getStore().load({
										callback: function() {
											if ( !Ext.isEmpty(EvnPLDisp_id) && base_form.findField('EvnPLDisp_id').getStore().getCount() > 0 ) {
												var index = base_form.findField('EvnPLDisp_id').getStore().findBy(function(rec) {
													return (rec.get('EvnPLDisp_id') == EvnPLDisp_id);
												});

												if ( index >= 0 ) {
													base_form.findField('EvnPLDisp_id').setValue(EvnPLDisp_id);
												}
												else {
													base_form.findField('EvnPLDisp_id').clearValue();
												}
											}
										},
										params: {
											DispClass_id: record.get('DispClass_id'),
											Person_id: base_form.findField('Person_id').getValue()
										}
									})
								}
								else if ( !Ext.isEmpty(EvnPLDisp_id) && base_form.findField('EvnPLDisp_id').getStore().getCount() > 0 ) {
									var index = base_form.findField('EvnPLDisp_id').getStore().findBy(function(rec) {
										return (rec.get('EvnPLDisp_id') == EvnPLDisp_id);
									});

									if ( index >= 0 ) {
										base_form.findField('EvnPLDisp_id').setValue(EvnPLDisp_id);
									}
									else {
										base_form.findField('EvnPLDisp_id').clearValue();
									}
								}
							}
							else {
								base_form.findField('EvnPLDisp_id').clearValue();
								base_form.findField('EvnPLDisp_id').disable();
							}
						}.createDelegate(this),
						'keydown': function(inp, e) {
							switch ( e.getKey() ) {
								case Ext.EventObject.TAB:
									var base_form = this.findById('EvnVizitPLStomEditForm').getForm();
									if ( e.shiftKey == false && !this.findById('EVPLSEF_DiagPanel').collapsed && base_form.findField('EvnVizitPLStom_Uet').disabled) {
										e.stopEvent();
										if ( !this.findById('EVPLSEF_EvnDiagPLStomPanel').collapsed && this.findById('EVPLSEF_EvnDiagPLStomGrid').getStore().getCount() > 0 && this.findById('EVPLSEF_EvnDiagPLStomPanel').isVisible() ) {
											this.findById('EVPLSEF_EvnDiagPLStomGrid').getSelectionModel().clearSelections();
											this.findById('EVPLSEF_EvnDiagPLStomGrid').getView().focusRow(0);
											this.findById('EVPLSEF_EvnDiagPLStomGrid').getSelectionModel().selectFirstRow();
										}
									}
									if ( e.shiftKey == false && this.findById('EVPLSEF_DiagPanel').collapsed && base_form.findField('EvnPLDisp_id').disabled ) {
										e.stopEvent();

										if ( !this.findById('EVPLSEF_EvnDiagPLStomSopPanel').collapsed && this.findById('EVPLSEF_EvnDiagPLStomSopGrid').getStore().getCount() > 0 ) {
											this.findById('EVPLSEF_EvnDiagPLStomSopGrid').getView().focusRow(0);
											this.findById('EVPLSEF_EvnDiagPLStomSopGrid').getSelectionModel().selectFirstRow();
										}
										else if ( !this.findById('EVPLSEF_EvnUslugaStomPanel').hidden && !this.findById('EVPLSEF_EvnUslugaStomPanel').collapsed && this.findById('EVPLSEF_EvnUslugaGrid').getStore().getCount() > 0 ) {
											this.findById('EVPLSEF_EvnUslugaGrid').getView().focusRow(0);
											this.findById('EVPLSEF_EvnUslugaGrid').getSelectionModel().selectFirstRow();
										}
										else if ( !this.findById('EVPLSEF_EvnReceptPanel').collapsed && this.findById('EVPLSEF_EvnReceptGrid').getStore().getCount() > 0 ) {
											this.findById('EVPLSEF_EvnReceptGrid').getView().focusRow(0);
											this.findById('EVPLSEF_EvnReceptGrid').getSelectionModel().selectFirstRow();
										}
										else if ( this.action == 'view' ) {
											this.buttons[this.buttons.length - 1].focus();
										}
										else {
											this.buttons[0].focus();
										}
									}
								break;
							}
						}.createDelegate(this)
					},
					onLoadStore: function() {
						this.getStore().filterBy(function(rec) {
							if (getRegionNick() == 'kareliya') {
								return (rec.get('DispClass_id').inlist([ 4, 6, 8, 9, 10, 11, 12 ]));
							}
							else if (getRegionNick() == 'krym') {
								return (rec.get('DispClass_id').inlist([ 1, 2, 3, 4, 5, 7, 8, 10, 12 ]));
							}
							else {
								return (rec.get('DispClass_id').inlist([ 4, 8, 11, 12 ]));
							}
						});
					},
					tabIndex: TABINDEX_EVPLSEF + 13,
					typeCode: 'int',
					width: 450,
					xtype: 'swcommonsprcombo'
				}, {
					comboSubject: 'DispProfGoalType',
					enableKeyEvents: true,
					fieldLabel: 'В рамках дисп./мед.осмотра',
					hiddenName: 'DispProfGoalType_id',
					lastQuery: '',
					moreFields: [{name: 'DispProfGoalType_IsVisible', mapping: 'DispProfGoalType_IsVisible'}],
					onLoadStore: function() {
						this.getStore().filterBy(function(rec) {
							return (rec.get('DispProfGoalType_IsVisible') == 2);
						});
					},
					tabIndex: TABINDEX_EVPLSEF + 14,
					typeCode: 'int',
					width: 450,
					xtype: 'swcommonsprcombo'
				}, {
					displayField: 'EvnPLDisp_Name',
					enableKeyEvents: true,
					fieldLabel: 'Карта дисп./мед.осмотра',
					hiddenName: 'EvnPLDisp_id',
					listeners: {
						'keydown': function(inp, e) {
							switch ( e.getKey() ) {
								case Ext.EventObject.TAB:
									var base_form = this.findById('EvnVizitPLStomEditForm').getForm();
									if( e.shiftKey == false && !this.findById('EVPLSEF_DiagPanel').collapsed ){
										e.stopEvent();
										if ( !this.findById('EVPLSEF_EvnDiagPLStomPanel').collapsed && this.findById('EVPLSEF_EvnDiagPLStomGrid').getStore().getCount() > 0 ) {
											this.findById('EVPLSEF_EvnDiagPLStomGrid').getView().focusRow(0);
											this.findById('EVPLSEF_EvnDiagPLStomGrid').getSelectionModel().selectFirstRow();
										}
									}
									if ( e.shiftKey == false && this.findById('EVPLSEF_DiagPanel').collapsed ) {
										e.stopEvent();

										if ( !this.findById('EVPLSEF_EvnDiagPLStomSopPanel').collapsed && this.findById('EVPLSEF_EvnDiagPLStomSopGrid').getStore().getCount() > 0 && this.findById('EVPLSEF_EvnDiagPLStomSopPanel').isVisible() ) {
											this.findById('EVPLSEF_EvnDiagPLStomSopGrid').getView().focusRow(0);
											this.findById('EVPLSEF_EvnDiagPLStomSopGrid').getSelectionModel().selectFirstRow();
										} 
										else if( !this.findById('EVPLSEF_EvnDiagPLStomPanel').collapsed && this.findById('EVPLSEF_EvnDiagPLStomGrid').getStore().getCount() > 0 && this.findById('EVPLSEF_EvnDiagPLStomPanel').isVisible() ){
											this.findById('EVPLSEF_EvnDiagPLStomGrid').getView().focusRow(0);
											this.findById('EVPLSEF_EvnDiagPLStomGrid').getSelectionModel().selectFirstRow();
										}
										else if ( !this.findById('EVPLSEF_EvnUslugaStomPanel').hidden && !this.findById('EVPLSEF_EvnUslugaStomPanel').collapsed && this.findById('EVPLSEF_EvnUslugaGrid').getStore().getCount() > 0 ) {
											this.findById('EVPLSEF_EvnUslugaGrid').getView().focusRow(0);
											this.findById('EVPLSEF_EvnUslugaGrid').getSelectionModel().selectFirstRow();
										}
										else if ( !this.findById('EVPLSEF_EvnReceptPanel').collapsed && this.findById('EVPLSEF_EvnReceptGrid').getStore().getCount() > 0 ) {
											this.findById('EVPLSEF_EvnReceptGrid').getView().focusRow(0);
											this.findById('EVPLSEF_EvnReceptGrid').getSelectionModel().selectFirstRow();
										}
										else if ( this.action == 'view' ) {
											this.buttons[this.buttons.length - 1].focus();
										}
										else {
											this.buttons[0].focus();
										}
									}
								break;
							}
						}.createDelegate(this)
					},
					store: new Ext.data.JsonStore({
						fields: [
							{ name: 'EvnPLDisp_id', type: 'int' },
							{ name: 'EvnPLDisp_setDate', type: 'date', dateFormat: 'd.m.Y' },
							{ name: 'EvnPLDisp_Name', type: 'string' }
						],
						key: 'EvnPLDisp_id',
						sortInfo: {
							field: 'EvnPLDisp_setDate'
						},
						url: '/?c=EvnPLDisp&m=loadEvnPLDispList'
					}),
					tabIndex: TABINDEX_EVPLSEF + 14,
					tpl:
						'<tpl for="."><div class="x-combo-list-item">'+
						'{EvnPLDisp_Name}&nbsp;'+
						'</div></tpl>',
					valueField: 'EvnPLDisp_id',
					width: 450,
					xtype: 'swbaselocalcombo'
				}, {
					displayField: 'PersonDisp_Name',
					enableKeyEvents: true,
					fieldLabel: 'Карта дис. учета',
					editable: false,
					hiddenName: 'PersonDisp_id',
					triggerAction: 'all',
					store: new Ext.data.JsonStore({
						fields: [
							{name: 'PersonDisp_id', type: 'int'},
							{name: 'PersonDisp_setDate', type: 'date', dateFormat: 'd.m.Y'},
							{name: 'PersonDisp_Name', type: 'string'}
						],
						key: 'PersonDisp_id',
						sortInfo: {
							field: 'PersonDisp_setDate'
						},
						url: '/?c=PersonDisp&m=loadPersonDispList'
					}),
					tabIndex: TABINDEX_EVPLSEF + 15,
					tpl:
					'<tpl for="."><div class="x-combo-list-item">'+
					'{PersonDisp_Name}&nbsp;'+
					'</div></tpl>',
					valueField: 'PersonDisp_id',
					width: 450,
					xtype: 'swbaseremotecombo'
				}, {
					allowDecimals: true,
					allowNegative: false,
					disabled: true,
					enableKeyEvents: true,
					fieldLabel: 'УЕТ (факт)',
					listeners: {
						'keydown': function(inp, e) {
							switch ( e.getKey() ) {
								case Ext.EventObject.TAB:
									var base_form = this.findById('EvnVizitPLStomEditForm').getForm();

									if ( e.shiftKey == false && ! base_form.findField('EvnVizitPLStom_UetOMS').isVisible() ) {
										e.stopEvent();

										if ( !this.findById('EVPLSEF_DiagPanel').collapsed && !base_form.findField('Diag_id').disabled && this.findById('EVPLSEF_DiagPanel').isVisible() ) {
											base_form.findField('Diag_id').focus(true);
										}
										else if ( !this.findById('EVPLSEF_EvnDiagPLStomPanel').collapsed && this.findById('EVPLSEF_EvnDiagPLStomGrid').getStore().getCount() > 0 && this.findById('EVPLSEF_EvnDiagPLStomPanel').isVisible() ) {
											this.findById('EVPLSEF_EvnDiagPLStomGrid').getView().focusRow(0);
											this.findById('EVPLSEF_EvnDiagPLStomGrid').getSelectionModel().selectFirstRow();
										}
										else if ( !this.findById('EVPLSEF_EvnDiagPLStomSopPanel').collapsed && this.findById('EVPLSEF_EvnDiagPLStomSopGrid').getStore().getCount() > 0 && this.findById('EVPLSEF_EvnDiagPLStomSopPanel').isVisible() ) {
											this.findById('EVPLSEF_EvnDiagPLStomSopGrid').getView().focusRow(0);
											this.findById('EVPLSEF_EvnDiagPLStomSopGrid').getSelectionModel().selectFirstRow();
										}
										else if ( !this.findById('EVPLSEF_EvnUslugaStomPanel').collapsed && this.findById('EVPLSEF_EvnUslugaStomGrid').getStore().getCount() > 0 ) {
											this.findById('EVPLSEF_EvnUslugaStomGrid').getView().focusRow(0);
											this.findById('EVPLSEF_EvnUslugaStomGrid').getSelectionModel().selectFirstRow();
										}
										else if ( this.action == 'view' ) {
											this.buttons[this.buttons.length - 1].focus();
										}
										else {
											this.buttons[0].focus();
										}
									}
								break;
							}
						}.createDelegate(this)
					},
					name: 'EvnVizitPLStom_Uet',
					tabIndex: TABINDEX_EVPLSEF + 15,
					xtype: 'numberfield'
				}, {
					allowDecimals: true,
					allowNegative: false,
					disabled: true,
					enableKeyEvents: true,
					fieldLabel: 'УЕТ (факт по ОМС)',
					listeners: {
						'keydown': function(inp, e) {
							switch ( e.getKey() ) {
								case Ext.EventObject.TAB:
									var base_form = this.findById('EvnVizitPLStomEditForm').getForm();

									if ( e.shiftKey == false ) {
										e.stopEvent();

										if ( !this.findById('EVPLSEF_DiagPanel').collapsed && !base_form.findField('Diag_id').disabled && this.findById('EVPLSEF_DiagPanel').isVisible() ) {
											base_form.findField('Diag_id').focus(true);
										}
										else if ( !this.findById('EVPLSEF_EvnDiagPLStomSopPanel').collapsed && this.findById('EVPLSEF_EvnDiagPLStomSopGrid').getStore().getCount() > 0 ) {
											this.findById('EVPLSEF_EvnDiagPLStomSopGrid').getView().focusRow(0);
											this.findById('EVPLSEF_EvnDiagPLStomSopGrid').getSelectionModel().selectFirstRow();
										}
										else if ( !this.findById('EVPLSEF_EvnUslugaStomPanel').collapsed && this.findById('EVPLSEF_EvnUslugaStomGrid').getStore().getCount() > 0 ) {
											this.findById('EVPLSEF_EvnUslugaStomGrid').getView().focusRow(0);
											this.findById('EVPLSEF_EvnUslugaStomGrid').getSelectionModel().selectFirstRow();
										}
										else if ( this.action == 'view' ) {
											this.buttons[this.buttons.length - 1].focus();
										}
										else {
											this.buttons[0].focus();
										}
									}
								break;
							}
						}.createDelegate(this)
					},
					name: 'EvnVizitPLStom_UetOMS',
					tabIndex: TABINDEX_EVPLSEF + 16,
					xtype: 'numberfield'
				}, {
					border: false,
					layout: 'form',
					hidden: !(getRegionNick() == 'perm'),
					items: [{
						allowDecimals: true,
						allowNegative: false,
						disabled: true,
						enableKeyEvents: true,
						fieldLabel: 'УЕТ (норматив по МЭС)',
						name: 'EvnVizitPLStom_UetMes',
						tabIndex: TABINDEX_EVPLSEF + 17,
						xtype: 'numberfield'
					}]
				}, {
                    border: false,
                    hidden: !getRegionNick().inlist([ 'ufa' ]),
                    layout: 'form',
                    items: [{
                        fieldLabel: langs('Группа здоровья'),
                        hiddenName: 'HealthKind_id',
						tabIndex: TABINDEX_EVPLSEF + 18,
						width: 100,
                        xtype: 'swhealthkindcombo'
                    }]
                },
				{
					comboSubject: 'BitePersonType',
					enableKeyEvents: true,
					fieldLabel: 'Прикус',
					hiddenName: 'BitePersonType_id',
					lastQuery: '',
					/*moreFields: [{name: 'DispProfGoalType_IsVisible', mapping: 'DispProfGoalType_IsVisible'}],
					onLoadStore: function() {
						this.getStore().filterBy(function(rec) {
							return (rec.get('DispProfGoalType_IsVisible') == 2);
						});
					},*/
					tabIndex: TABINDEX_EVPLSEF + 19,
					typeCode: 'int',
					width: 450,
					xtype: 'swcommonsprcombo'
				},
				this.evnDirectionAllInfoPanel,
				/* Панель протокола осмотра */
				this.EvnXmlPanel,
				new sw.Promed.Panel({
					border: true,
					collapsible: true,
					height: 200,
					id: 'EVPLSEF_EvnDiagPLStomPanel',
					isLoaded: false,
					layout: 'border',
					listeners: {
						'expand': function(panel) {
							if ( panel.isLoaded === false ) {
								panel.isLoaded = true;
								panel.findById('EVPLSEF_EvnDiagPLStomGrid').getStore().load({
									params: {
										rid: this.findById('EvnVizitPLStomEditForm').getForm().findField('EvnPLStom_id').getValue()
									}
								});
							}

							panel.doLayout();
						}.createDelegate(this)
					},
					style: 'margin-bottom: 0.5em;',
					title: '3. Заболевания',
					items: [ new Ext.grid.GridPanel({
						autoExpandColumn: 'autoexpand_evndiagplstom_vizit',
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
							align: 'center',
							dataIndex: 'EvnDiagPLStom_IsClosed',
							renderer: function(v, p, row) {
								if (!Ext.isEmpty(v) && v == 2) {
									return "Да";
								}
								if (!Ext.isEmpty(v) && v == 1) {
									return "Нет";
								}
								return ""; // для пустых строк пусто
							},
							header: 'Заболевание закрыто',
							hidden: false,
							resizable: true,
							sortable: false,
							width: 150
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
							dataIndex: 'Mes_Code',
							header: 'Код КСГ',
							hidden: !getRegionNick().inlist([ 'perm', 'astra', 'vologda' ]),
							resizable: true,
							sortable: true,
							width: 100
						}, {
							dataIndex: 'Mes_Name',
							header: 'Наименование КСГ',
							hidden: !getRegionNick().inlist([ 'perm', 'astra', 'vologda' ]),
							id: 'autoexpand_evndiagplstom_vizit',
							resizable: true,
							sortable: true
						}, {
							align: 'center',
							dataIndex: 'EvnDiagPLStom_IsThisVizit',
							header: 'Добавлено в данном посещении',
							hidden: false,
							resizable: true,
							sortable: false,
							width: 200
						}, {
							align: 'center',
							dataIndex: 'EvnDiagPLStom_Uet',
							header: 'УЕТ',
							hidden: false,
							resizable: true,
							sortable: false,
							renderer: twoDecimalsRenderer,
							width: 200
						}, {
							dataIndex: 'EvnDiagPLStom_NumGroup',
							header: 'Группировка',
							resizable: false,
							sortable: true,
							width: 80
						}],
						frame: false,
						id: 'EVPLSEF_EvnDiagPLStomGrid',
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

								var grid = this.findById('EVPLSEF_EvnDiagPLStomGrid');

								switch ( e.getKey() ) {
									case Ext.EventObject.DELETE:
										this.deleteEvent('EvnDiagPLStomSop');
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

										this.openEvnDiagPLStomEditWindow(action);
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
										grid.getSelectionModel().clearSelections();
                                        grid.getSelectionModel().fireEvent('rowselect', grid.getSelectionModel());

                                        if ( e.shiftKey == false ) {
                                            /*if ( !this.findById('EVPLSEF_EvnUslugaStomPanel').collapsed && this.findById('EVPLSEF_EvnUslugaStomGrid').getStore().getCount() > 0 ) {
                                                this.findById('EVPLSEF_EvnUslugaStomGrid').getView().focusRow(0);
                                                this.findById('EVPLSEF_EvnUslugaStomGrid').getSelectionModel().selectFirstRow();
                                            }
                                            else if ( !this.findById('EVPLSEF_EvnDirectionPanel').collapsed && this.findById('EVPLSEF_EvnDirectionGrid').getStore().getCount() > 0 ) {
                                                this.findById('EVPLSEF_EvnDirectionGrid').getView().focusRow(0);
                                                this.findById('EVPLSEF_EvnDirectionGrid').getSelectionModel().selectFirstRow();
                                            }
                                            else*/ if ( this.action == 'view' ) {
                                                this.buttons[this.buttons.length - 1].focus();
                                            }
                                            else {
                                                this.buttons[0].focus();
                                            }
                                        }
                                        else {
                                            var base_form = this.findById('EvnVizitPLStomEditForm').getForm();

                                            if ( this.action != 'view' ) {
                                                if ( !this.findById('EVPLSEF_DiagPanel').collapsed && !base_form.findField('Mes_id').disabled ) {
                                                    base_form.findField('Mes_id').focus(true);
                                                }
                                                else if ( !base_form.findField('EvnVizitPLStom_Uet').disabled ) {
                                                    base_form.findField('EvnVizitPLStom_Uet').focus(true);
                                                }
                                                else if ( !base_form.findField('EvnVizitPLStom_UetOMS').disabled ) {
                                                    base_form.findField('EvnVizitPLStom_UetOMS').focus(true);
                                                }
                                                else if ( !base_form.findField('EvnPLDisp_id').disabled ) {
                                                    base_form.findField('EvnPLDisp_id').focus(true);
                                                }
                                                else {
                                                    base_form.findField('DispClass_id').focus(true);
                                                }
                                            }
                                            else {
                                                this.buttons[this.buttons.length - 1].focus();
                                            }
                                        }
										/*var base_form = this.findById('EvnVizitPLStomEditForm').getForm();

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
										}*/
									break;
								}
							},
							scope: this,
							stopEvent: true
						}],
						listeners: {
							'rowdblclick': function(grid, number, obj) {
								this.openEvnDiagPLStomEditWindow('edit');
							}.createDelegate(this)
						},
						loadMask: true,
						region: 'center',
						sm: new Ext.grid.RowSelectionModel({
							listeners: {
								'rowselect': function(sm, rowIndex, record) {
									var
										base_form = this.findById('EvnVizitPLStomEditForm').getForm(),
										id,
										selected_record = sm.getSelected(),
										toolbar = this.findById('EVPLSEF_EvnDiagPLStomGrid').getTopToolbar();

									if ( selected_record ) {
										id = selected_record.get('EvnDiagPLStom_id');
									}

									toolbar.items.items[1].disable();
									toolbar.items.items[3].disable();

									if ( id ) {
										toolbar.items.items[2].enable();

										if ( this.action != 'view' /*&& base_form.findField('EvnVizitPLStom_id').getValue() == selected_record.get('EvnDiagPLStom_pid')*/ ) {
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
								'parent': 'EvnVizitPLStom'
							},
							listeners: {
								'load': function(store, records, index) {
									var base_form = this.findById('EvnVizitPLStomEditForm').getForm();

									if ( store.getCount() == 0 ) {
										LoadEmptyRow(this.findById('EVPLSEF_EvnDiagPLStomGrid'));
									}
									
									if (getRegionNick() == 'buryatiya') {
										var recordCount = 0;
										
										store.each(function(rec) {//считаем непустые записи
											if ( !Ext.isEmpty(rec.get('EvnDiagPLStom_id')) ) {
												recordCount ++;
											}
										});
										if ( this.action != 'view' && recordCount == 0 ) {
											Ext.getCmp('EVPLSEF_EvnDiagPLStomGrid_action_add').enable();
										} else {
											Ext.getCmp('EVPLSEF_EvnDiagPLStomGrid_action_add').disable();
										}
									}

									// Проставить признка "Создан в этом посещении"
									store.each(function(rec) {
										if ( !Ext.isEmpty(rec.get('EvnDiagPLStom_id')) ) {
											if ( rec.get('EvnDiagPLStom_pid') == base_form.findField('EvnVizitPLStom_id').getValue() ) {
												rec.set('EvnDiagPLStom_IsThisVizit', 'X');
											}
											else {
												rec.set('EvnDiagPLStom_IsThisVizit', '');
											}

											rec.commit();
										}
									});
									
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
								mapping: 'EvnVizitPLStom_setDate',
								name: 'EvnVizitPLStom_setDate',
								type: 'date'
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
								mapping: 'EvnDiagPLStom_IsClosed',
								name: 'EvnDiagPLStom_IsClosed',
								type: 'int'
							}, {
								mapping: 'EvnDiagPLStom_IsThisVizit',
								name: 'EvnDiagPLStom_IsThisVizit',
								type: 'string'
							}, {
								mapping: 'EvnDiagPLStom_Uet',
								name: 'EvnDiagPLStom_Uet',
								type: 'string'
							}, {
								mapping: 'EvnDiagPLStom_NumGroup',
								name: 'EvnDiagPLStom_NumGroup',
								type: 'string'
							}, {
								mapping: 'Mes_Code',
								name: 'Mes_Code',
								type: 'string'
							}, {
								mapping: 'Mes_Name',
								name: 'Mes_Name',
								type: 'string'
							}, {
								mapping: 'ServiceType_SysNick',
								name: 'ServiceType_SysNick',
								type: 'string'
							}, {
								mapping: 'hasUslugaType03',
								name: 'hasUslugaType03',
								type: 'int'
							}]),
							url: '/?c=EvnDiagPLStom&m=loadEvnDiagPLStomGrid'
						}),
						tbar: new sw.Promed.Toolbar({
							buttons: [{
								handler: function() {
									this.openEvnDiagPLStomEditWindow('add');
								}.createDelegate(this),
								iconCls: 'add16',
								id: 'EVPLSEF_EvnDiagPLStomGrid_action_add',
								text: BTN_GRIDADD,
								tooltip: BTN_GRIDADD_TIP
							}, {
								handler: function() {
									this.openEvnDiagPLStomEditWindow('edit');
								}.createDelegate(this),
								iconCls: 'edit16',
								text: BTN_GRIDEDIT,
								tooltip: BTN_GRIDEDIT_TIP
							}, {
								handler: function() {
									this.openEvnDiagPLStomEditWindow('view');
								}.createDelegate(this),
								iconCls: 'view16',
								text: BTN_GRIDVIEW,
								tooltip: BTN_GRIDVIEW_TIP
							}, {
								handler: function() {
									this.deleteEvent('EvnDiagPLStom');
								}.createDelegate(this),
								iconCls: 'delete16',
								text: BTN_GRIDDEL,
								tooltip: BTN_GRIDDEL_TIP
							}, {
								menu: [
									{text: 'Печать КЛУ при ЗНО', iconCls: 'print16', handler: function () { 
										this.printControlCardZno();
									}.createDelegate(this)},
									{text: 'Печать выписки при онкологии', hidden: getRegionNick() != 'ekb', iconCls: 'print16', handler: function () {
										this.printControlCardOnko();
									}.createDelegate(this)}
								],
								iconCls: 'print16',
								hidden: getRegionNick() == 'kz',
								text: BTN_GRIDPRINT,
								tooltip: BTN_GRIDPRINT_TIP
							}]
						})
					})]
				}),
				// Зубная карта
				this.ToothMapPanel,
				new sw.Promed.Panel({
					autoHeight: true,
					bodyStyle: 'padding-top: 0.5em;',
					border: true,
					collapsible: true,
					id: 'EVPLSEF_DiagPanel',
					layout: 'form',
					style: 'margin-bottom: 0.5em;',
					title: '4. Основной диагноз',
					items: [{
						hiddenName: 'Diag_id',
						id: 'EVPLSEF_DiagCombo',
						onChange: function (combo, value) {
							var base_form = this.findById('EvnVizitPLStomEditForm').getForm();
							this.loadMesCombo({
								p: 'change_Diag_id',
								Diag_id: value,
								EvnVizit_setDate: base_form.findField('EvnVizitPLStom_setDate').getValue(),
								LpuSection_id: base_form.findField('LpuSection_id').getValue()
							});
							this.getFinanceSource();
						}.createDelegate(this),
						tabIndex: TABINDEX_EVPLSEF + 19,
						width: 450,
						xtype: 'swdiagcombo'
					}, {
						hiddenName: 'DeseaseType_id',
						tabIndex: TABINDEX_EVPLSEF + 20,
						width: 450,
						xtype: 'swdeseasetypecombo'
					}, {
						name: 'EvnDiagPLStom_id',
						xtype: 'hidden'
					}, {
						name: 'Tooth_id',
						xtype: 'hidden'
					}, {
						tabIndex: TABINDEX_EVPLSEF + 21,
						listeners: {
							change: function(field) {
								var base_form = this.findById('EvnVizitPLStomEditForm').getForm();
								field.applyChangeTo(this,
									base_form.findField('Tooth_id'),
									base_form.findField('ToothSurfaceType_id_list')
								);
							}.createDelegate(this)
						},
						name: 'Tooth_Code',
						xtype: 'swtoothfield'
					}, {
						name: 'ToothSurfaceType_id_list',
						xtype: 'swtoothsurfacetypecheckboxgroup'
					}, {
						border: false,
						layout: 'form',
						hidden: (!getRegionNick().inlist(['perm'])),
						items: [{
							// allowBlank: !(getGlobalOptions().region && getGlobalOptions().region.nick == 'perm'),
							fieldLabel: 'МЭС',
							hiddenName: 'Mes_id',
							listeners: {
								'change': function (combo, newValue, oldValue) {
									var index = combo.getStore().findBy(function(rec) {
										return (rec.get('Mes_id') == newValue);
									});

									combo.fireEvent('select', combo, combo.getStore().getAt(index));

									return true;
								}.createDelegate(this),
								'keydown': function(inp, e) {
									switch ( e.getKey() ) {
										case Ext.EventObject.TAB:
											var base_form = this.findById('EvnVizitPLStomEditForm').getForm();

											if ( e.shiftKey == false ) {
												e.stopEvent();

												if ( !this.findById('EVPLSEF_EvnDiagPLStomSopPanel').collapsed && this.findById('EVPLSEF_EvnDiagPLStomSopGrid').getStore().getCount() > 0 ) {
													this.findById('EVPLSEF_EvnDiagPLStomSopGrid').getView().focusRow(0);
													this.findById('EVPLSEF_EvnDiagPLStomSopGrid').getSelectionModel().selectFirstRow();
												}
												else if ( !this.findById('EVPLSEF_EvnUslugaStomPanel').collapsed && this.findById('EVPLSEF_EvnUslugaStomGrid').getStore().getCount() > 0 ) {
													this.findById('EVPLSEF_EvnUslugaStomGrid').getView().focusRow(0);
													this.findById('EVPLSEF_EvnUslugaStomGrid').getSelectionModel().selectFirstRow();
												}
												else if ( this.action == 'view' ) {
													this.buttons[this.buttons.length - 1].focus();
												}
												else {
													this.buttons[0].focus();
												}
											}
										break;
									}
								}.createDelegate(this),
								'select': function (combo, record) {
									var base_form = this.findById('EvnVizitPLStomEditForm').getForm();
									var addByMesBtn = this.findById('EVPLSEF_EvnUslugaStomGrid').getTopToolbar().items.items[4];

									if ( record ) {
										base_form.findField('EvnVizitPLStom_UetMes').setRawValue(record.get('Mes_KoikoDni'));
									} else {
										base_form.findField('EvnVizitPLStom_UetMes').setRawValue('');
									}
									addByMesBtn.setDisabled(this.action == 'view' || !combo.getValue());
									return true;
								}.createDelegate(this)
							},
							mode: 'local',
							tabIndex: TABINDEX_EVPLSEF + 22,
							width: 450,
							xtype: 'swmescombo'
						}]
					}]
				}),
				new sw.Promed.Panel({
					border: true,
					collapsible: true,
					height: 145,
					id: 'EVPLSEF_EvnDiagPLStomSopPanel',
					isLoaded: false,
					layout: 'border',
					listeners: {
						'expand': function(panel) {
							if ( panel.isLoaded === false ) {
								panel.isLoaded = true;
								panel.findById('EVPLSEF_EvnDiagPLStomSopGrid').getStore().load({
									params: {
										EvnDiagPLStomSop_pid: this.findById('EvnVizitPLStomEditForm').getForm().findField('EvnVizitPLStom_id').getValue()
									}
								});
							}

							panel.doLayout();
						}.createDelegate(this)
					},
					setFocusOnLoad: false,
					style: 'margin-bottom: 0.5em;',
					title: '5. Сопутствующие диагнозы',
					items: [ new Ext.grid.GridPanel({
						autoExpandColumn: 'autoexpand_diag',
						autoExpandMin: 100,
						border: false,
						columns: [{
							dataIndex: 'EvnDiagPLStomSop_setDate',
							header: 'Дата установки',
							hidden: false,
							renderer: Ext.util.Format.dateRenderer('d.m.Y'),
							resizable: false,
							sortable: true,
							width: 130
						}, {
							dataIndex: 'Diag_Code',
							header: 'Код',
							hidden: false,
							resizable: false,
							sortable: true,
							width: 80
						}, {
							dataIndex: 'Diag_Name',
							header: 'Наименование',
							hidden: false,
							resizable: false,
							sortable: true,
							width: 250
						}, {
							dataIndex: 'DeseaseType_Name',
							header: 'Характер',
							hidden: false,
							id: 'autoexpand_diag',
							sortable: true
						}],
						frame: false,
						id: 'EVPLSEF_EvnDiagPLStomSopGrid',
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

								var grid = this.findById('EVPLSEF_EvnDiagPLStomSopGrid');

								switch ( e.getKey() ) {
									case Ext.EventObject.DELETE:
										this.deleteEvent('EvnDiagPLStomSop');
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

										this.openEvnDiagPLStomSopEditWindow(action);
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
										grid.getSelectionModel().clearSelections();
										grid.getSelectionModel().fireEvent('rowselect', grid.getSelectionModel());

										if ( e.shiftKey == false ) {
											if ( !this.findById('EVPLSEF_EvnUslugaStomPanel').collapsed && this.findById('EVPLSEF_EvnUslugaStomGrid').getStore().getCount() > 0 ) {
												this.findById('EVPLSEF_EvnUslugaStomGrid').getView().focusRow(0);
												this.findById('EVPLSEF_EvnUslugaStomGrid').getSelectionModel().selectFirstRow();
											}
											else if ( this.action == 'view' ) {
												this.buttons[this.buttons.length - 1].focus();
											}
											else {
												this.buttons[0].focus();
											}
										}
										else {
											var base_form = this.findById('EvnVizitPLStomEditForm').getForm();

											if ( this.action != 'view' ) {
												if ( !this.findById('EVPLSEF_DiagPanel').collapsed && !base_form.findField('Mes_id').disabled ) {
													base_form.findField('Mes_id').focus(true);
												}
												else if ( !base_form.findField('EvnVizitPLStom_Uet').disabled ) {
													base_form.findField('EvnVizitPLStom_Uet').focus(true);
												}
												else if ( !base_form.findField('EvnVizitPLStom_UetOMS').disabled ) {
													base_form.findField('EvnVizitPLStom_UetOMS').focus(true);
												}
												else if ( !base_form.findField('EvnPLDisp_id').disabled ) {
													base_form.findField('EvnPLDisp_id').focus(true);
												}
												else {
													base_form.findField('DispClass_id').focus(true);
												}
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
						layout: 'fit',
						listeners: {
							'rowdblclick': function(grid, number, obj) {
								this.openEvnDiagPLStomSopEditWindow('edit');
							}.createDelegate(this)
						},
						loadMask: true,
						region: 'center',
						sm: new Ext.grid.RowSelectionModel({
							listeners: {
								'rowselect': function(sm, rowIndex, record) {
									var id = null;
									var selected_record = sm.getSelected();
									var toolbar = this.findById('EVPLSEF_EvnDiagPLStomSopGrid').getTopToolbar();

									if ( selected_record ) {
										id = selected_record.get('EvnDiagPLStomSop_id');
									}

									toolbar.items.items[1].disable();
									toolbar.items.items[3].disable();

									if ( id ) {
										toolbar.items.items[2].enable();

										if ( this.action != 'view' ) {
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
										LoadEmptyRow(this.findById('EVPLSEF_EvnDiagPLStomSopGrid'));
									}

									if ( this.findById('EVPLSEF_EvnDiagPLStomSopPanel').setFocusOnLoad == true ) {
										this.findById('EVPLSEF_EvnDiagPLStomSopGrid').getView().focusRow(0);
										this.findById('EVPLSEF_EvnDiagPLStomSopGrid').getSelectionModel().selectFirstRow();

										this.findById('EVPLSEF_EvnDiagPLStomSopPanel').setFocusOnLoad = false;
									}
								}.createDelegate(this)
							},
							reader: new Ext.data.JsonReader({
								id: 'EvnDiagPLStomSop_id'
							}, [{
								mapping: 'EvnDiagPLStomSop_id',
								name: 'EvnDiagPLStomSop_id',
								type: 'int'
							}, {
								mapping: 'EvnDiagPLStomSop_pid',
								name: 'EvnDiagPLStomSop_pid',
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
								dateFormat: 'd.m.Y',
								mapping: 'EvnDiagPLStomSop_setDate',
								name: 'EvnDiagPLStomSop_setDate',
								type: 'date'
							}, {
								mapping: 'Diag_Code',
								name: 'Diag_Code',
								type: 'string'
							}, {
								mapping: 'Diag_Name',
								name: 'Diag_Name',
								type: 'string'
							}, {
								mapping: 'DeseaseType_Name',
								name: 'DeseaseType_Name',
								type: 'string'
							}]),
							url: '/?c=EvnDiag&m=loadEvnDiagPLStomSopGrid'
						}),
						tbar: new sw.Promed.Toolbar({
							buttons: [{
								handler: function() {
									this.openEvnDiagPLStomSopEditWindow('add');
								}.createDelegate(this),
								iconCls: 'add16',
								text: BTN_GRIDADD,
								tooltip: BTN_GRIDADD_TIP
							}, {
								handler: function() {
									this.openEvnDiagPLStomSopEditWindow('edit');
								}.createDelegate(this),
								iconCls: 'edit16',
								text: BTN_GRIDEDIT,
								tooltip: BTN_GRIDEDIT_TIP
							}, {
								handler: function() {
									this.openEvnDiagPLStomSopEditWindow('view');
								}.createDelegate(this),
								iconCls: 'view16',
								text: BTN_GRIDVIEW,
								tooltip: BTN_GRIDVIEW_TIP
							}, {
								handler: function() {
									this.deleteEvent('EvnDiagPLStomSop');
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
					id: 'EVPLSEF_EvnUslugaStomPanel',
					isLoaded: false,
					layout: 'border',
					listeners: {
						'expand': function(panel) {
							if ( panel.isLoaded === false ) {
								panel.isLoaded = true;

								panel.findById('EVPLSEF_EvnUslugaStomGrid').getStore().load({
									params: {
										pid: this.findById('EvnVizitPLStomEditForm').getForm().findField('EvnVizitPLStom_id').getValue()
									}
								});
							}
							panel.doLayout();
						}.createDelegate(this)
					},
					style: 'margin-bottom: 0.5em;',
					title: '6. Услуги',
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
							header: 'Цена (УЕТ)',
							hidden: false,
							resizable: true,
							sortable: true,
							renderer: twoDecimalsRenderer,
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
							renderer: twoDecimalsRenderer,
							resizable: true,
							sortable: true,
							width: 100
						}, {
							dataIndex: 'EvnDiagPLStom_Title',
							header: 'Заболевание',
							hidden: false,
							resizable: true,
							sortable: true,
							width: 200
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
						id: 'EVPLSEF_EvnUslugaStomGrid',
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

								var grid = this.findById('EVPLSEF_EvnUslugaStomGrid');

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
										if ( !grid.getSelectionModel().getSelected() ) {
											return false;
										}

										var action = 'edit';

										if ( e.getKey() == Ext.EventObject.F3 ) {
											action = 'view';
										}

										this.openEvnUslugaStomEditWindow(action);
									break;

									case Ext.EventObject.HOME:
										GridHome(grid);
									break;

									case Ext.EventObject.INSERT:
										this.openEvnUslugaStomEditWindow('add');
									break;

									case Ext.EventObject.PAGE_DOWN:
										GridPageDown(grid);
									break;

									case Ext.EventObject.PAGE_UP:
										GridPageUp(grid);
									break;

									case Ext.EventObject.TAB:
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
											var base_form = this.findById('EvnVizitPLStomEditForm').getForm();

											if ( !this.findById('EVPLSEF_EvnDiagPLStomSopPanel').collapsed && this.findById('EVPLSEF_EvnDiagPLStomSopGrid').getStore().getCount() > 0 ) {
												this.findById('EVPLSEF_EvnDiagPLStomSopGrid').getView().focusRow(0);
												this.findById('EVPLSEF_EvnDiagPLStomSopGrid').getSelectionModel().selectFirstRow();
											}
											else if ( !this.findById('EVPLSEF_DiagPanel').collapsed && !base_form.findField('Mes_id').disabled ) {
												base_form.findField('Mes_id').focus(true);
											}
											else if ( this.action != 'view' ) {
												if ( !base_form.findField('EvnVizitPLStom_Uet').disabled ) {
													base_form.findField('EvnVizitPLStom_Uet').focus(true);
												}
												else if ( !base_form.findField('EvnVizitPLStom_UetOMS').disabled ) {
													base_form.findField('EvnVizitPLStom_UetOMS').focus(true);
												}
												else if ( !base_form.findField('EvnPLDisp_id').disabled ) {
													base_form.findField('EvnPLDisp_id').focus(true);
												}
												else {
													base_form.findField('DispClass_id').focus(true);
												}
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
								this.openEvnUslugaStomEditWindow('edit');
							}.createDelegate(this)
						},
						loadMask: true,
						region: 'center',
						sm: new Ext.grid.RowSelectionModel({
							listeners: {
								'rowselect': function(sm, rowIndex, record) {
									var evn_usluga_stom_id = null;
									var selected_record = sm.getSelected();
									var toolbar = this.findById('EVPLSEF_EvnUslugaStomGrid').getTopToolbar(),
										addByMesBtn = toolbar.items.items[4];

									if ( selected_record ) {
										evn_usluga_stom_id = selected_record.get('EvnUsluga_id');
									}

									if ( evn_usluga_stom_id ) {
										toolbar.items.items[2].enable();

										if ( this.action != 'view' ) {
											toolbar.items.items[1].enable();
											toolbar.items.items[3].enable();
										}
									}
									else {
										toolbar.items.items[1].disable();
										toolbar.items.items[2].disable();
										toolbar.items.items[3].disable();
									}
									var bf = this.findById('EvnVizitPLStomEditForm').getForm();
									var mes_id = bf.findField('Mes_id').getValue();
									if ( getRegionNick() == 'ekb' ) {
										mes_id = bf.findField('MesEkb_id').getValue();
									}
									addByMesBtn.setDisabled(this.action == 'view' || !mes_id);
								}.createDelegate(this)
							}
						}),
						stripeRows: true,
						store: new Ext.data.Store({
							autoLoad: false,
							baseParams: {
								'class': 'EvnUslugaStom',
								'parent': 'EvnPLStom'
							},
							listeners: {
								'load': function(store, records, index) {
									if ( store.getCount() == 0 ) {
										LoadEmptyRow(this.findById('EVPLSEF_EvnUslugaStomGrid'));
									}

									var base_form = this.findById('EvnVizitPLStomEditForm').getForm();

									if ( store.getCount() == 0 || (store.getCount() == 1 && !store.getAt(0).get('EvnUsluga_id')) ) {
										base_form.findField('EvnVizitPLStom_Uet').enable();
										base_form.findField('EvnVizitPLStom_UetOMS').enable();
									}
									else {
										base_form.findField('EvnVizitPLStom_Uet').disable();
										base_form.findField('EvnVizitPLStom_UetOMS').disable();
									}

									this.uetValuesRecount();
								}.createDelegate(this)
							},
							reader: new Ext.data.JsonReader({
								id: 'EvnUsluga_id'
							}, [{
								mapping: 'EvnUsluga_id',
								name: 'EvnUsluga_id',
								type: 'int'
							}, {
								mapping: 'EvnUsluga_pid',
								name: 'EvnUsluga_pid',
								type: 'int'
							}, {
								mapping: 'PayType_id',
								name: 'PayType_id',
								type: 'int'
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
								mapping: 'EvnUsluga_Price',
								name: 'EvnUsluga_Price',
								type: 'float'
							}, {
								mapping: 'EvnUsluga_Kolvo',
								name: 'EvnUsluga_Kolvo',
								type: 'float'
							}, {
								mapping: 'EvnUsluga_Summa',
								name: 'EvnUsluga_Summa',
								type: 'float'
							}, {
								mapping: 'EvnDiagPLStom_Title',
								name: 'EvnDiagPLStom_Title',
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
									this.openEvnUslugaStomEditWindow('add');
								}.createDelegate(this),
								iconCls: 'add16',
								text: 'Добавить'
							}, {
								handler: function() {
									this.openEvnUslugaStomEditWindow('edit');
								}.createDelegate(this),
								iconCls: 'edit16',
								text: 'Изменить'
							}, {
								handler: function() {
									this.openEvnUslugaStomEditWindow('view');
								}.createDelegate(this),
								iconCls: 'view16',
								text: 'Просмотр'
							}, {
								handler: function() {
									this.deleteEvent('EvnUsluga');
								}.createDelegate(this),
								iconCls: 'delete16',
								text: 'Удалить'
							}, {
								id: 'EVPLSEF_addByMesButton',
								handler: function() {
									this.openEvnUslugaStomEditWindow('addByMes');
								}.createDelegate(this),
								iconCls: 'add16',
								hidden: (!getRegionNick().inlist(['perm','ekb'])),
								text: 'Добавить все услуги по МЭС'
							}]
						})
					})]
				})],
				reader: new Ext.data.JsonReader({
					success: Ext.emptyFn
				}, [
					{ name: 'accessType' },
					{ name: 'EvnVizitPLStom_IsPaid' },
					{ name: 'EvnVizitPLStom_IndexRep' },
					{ name: 'EvnVizitPLStom_IndexRepInReg' },
					{ name: 'EvnVizitPLStom_id' },
					{ name: 'EvnDirection_id' },
					{ name: 'TimetableGraf_id' },
					//{ name: 'EvnVizitPLStom_hid' },
					{ name: 'EvnPLStom_id' },
					{ name: 'EvnPLStom_setDate' },
					{ name: 'Person_id' },
					{ name: 'PersonEvn_id' },
					{ name: 'Server_id' },
					{ name: 'LpuSection_id' },
					{ name: 'MedPersonal_id' },
					{ name: 'MedStaffFact_id' },
					{ name: 'MedPersonal_sid' },
					{ name: 'PayType_id' },
					{ name: 'ProfGoal_id' },
					{ name: 'DispClass_id' },
					{ name: 'DispProfGoalType_id'},
					{ name: 'EvnPLDisp_id' },
					{ name: 'ServiceType_id' },
					{ name: 'VizitClass_id' },
					{ name: 'VizitType_id' },
					{ name: 'EvnVizitPLStom_Time' },
					{ name: 'EvnVizitPLStom_Uet' },
					{ name: 'EvnVizitPLStom_UetOMS' },
					{ name: 'EvnVizitPLStom_UetMes' },
					{ name: 'EvnVizitPLStom_setDate' },
					{ name: 'EvnVizitPLStom_setTime' },
					{ name: 'EvnUslugaStom_id' },
					{ name: 'UslugaComplex_uid' },
					{ name: 'UslugaMedType_id' },
					{ name: 'UslugaComplexTariff_id' },
					{ name: 'EvnUslugaStom_UED' },
					{ name: 'LpuDispContract_id' },
					{ name: 'LpuSectionProfile_id' },
					{ name: 'HealthKind_id' },
					{ name: 'Diag_id' },
					{ name: 'DeseaseType_id' },
					{ name: 'Tooth_id' },
					{ name: 'ToothSurfaceType_id_list' },
					{ name: 'Tooth_Code' },
					{ name: 'EvnDiagPLStom_id' },
					{ name: 'Mes_id' },
					{ name: 'EvnVizitPLStom_IsPrimaryVizit' },
					{ name: 'EvnPLStom_IsFinish' },
					{ name: 'is_repeat_vizit' },
					{ name: 'TreatmentClass_id' },
					{ name: 'MedicalCareKind_id' },
					{ name: 'ResultClass_id' },
					{ name: 'AlertReg_Msg' },
					{ name: 'VizitActiveType_id' }
				]),
				region: 'center',
				url: '/?c=EvnPLStom&m=saveEvnVizitPLStom'
			})]
		});

		sw.Promed.swEvnVizitPLStomEditWindow.superclass.initComponent.apply(this, arguments);

		current_window.ToothMapPanel.addListener({
			beforeexpand: function(panel) {
				if ( !panel.isLoaded) {
					var base_form = current_window.findById('EvnVizitPLStomEditForm').getForm();
					if (base_form.findField('EvnVizitPLStom_id').getValue() > 0 ) {
						current_window.ToothMapPanel.applyParams(
							base_form.findField('Person_id').getValue(),
							base_form.findField('EvnVizitPLStom_id').getValue(),
							base_form.findField('EvnVizitPLStom_setDate').getRawValue()
						);
						current_window.ToothMapPanel.doLoad();
						return false;
					} else {
						current_window.doSave({
							isAutoCreate: true,
							openChildWindow: function() {
								current_window.ToothMapPanel.applyParams(
									base_form.findField('Person_id').getValue(),
									base_form.findField('EvnVizitPLStom_id').getValue(),
									base_form.findField('EvnVizitPLStom_setDate').getRawValue()
								);
								current_window.ToothMapPanel.doLoad();
							}
						});
					}
					return false;
				}
				return true;
			}
		});

		this.findById('EVPLSEF_DiagCombo').addListener('change', function(combo, newValue, oldValue) {
			var base_form = this.findById('EvnVizitPLStomEditForm').getForm();

			var index = combo.getStore().findBy(function(rec) {
				return (rec.get('Diag_id') == newValue);
			});

			if ( index >= 0 && combo.getStore().getAt(index).get('Diag_Code').substr(0, 1).toUpperCase() != 'Z' && this.formMode != 'morbus' ) {
				base_form.findField('DeseaseType_id').setAllowBlank(false);
			}
			else {
				base_form.findField('DeseaseType_id').setAllowBlank(true);
			}
		}.createDelegate(this));

		this.findById('EVPLSEF_LpuSectionCombo').addListener('change', function(combo, newValue, oldValue) {
			var base_form = this.findById('EvnVizitPLStomEditForm').getForm();

			if (
				!Ext.isEmpty(base_form.findField('LpuDispContract_id').getValue())
				&& (base_form.findField('LpuDispContract_id').getStore().getCount() > 0) // если комбик прогрузился (есть record)
				&& !Ext.isEmpty(base_form.findField('LpuSection_id').getFieldValue('LpuSectionProfile_id'))
				&& base_form.findField('LpuDispContract_id').getFieldValue('LpuSectionProfile_id') != base_form.findField('LpuSection_id').getFieldValue('LpuSectionProfile_id')
			) {
				base_form.findField('LpuDispContract_id').clearValue();
			}
			base_form.findField('LpuDispContract_id').getStore().baseParams.LpuSectionProfile_id = base_form.findField('LpuSection_id').getFieldValue('LpuSectionProfile_id');
			base_form.findField('LpuDispContract_id').lastQuery = 'This query sample that is not will never appear';
			base_form.findField('LpuDispContract_id').getStore().baseParams.query = '';

			this.setDefaultMedicalCareKind();
			this.loadLpuSectionProfileDop();
			this.setLpuSectionProfile();
			this.loadMesCombo({
				p: 'change_LpuSection_id',
				Diag_id: base_form.findField('Diag_id').getValue(),
				EvnVizit_setDate: base_form.findField('EvnVizitPLStom_setDate').getValue(),
				LpuSection_id: newValue
			});

			var uslugacomplex_combo = base_form.findField('UslugaComplex_uid');

			if ( getRegionNick().inlist(['ufa','buryatiya']) ) {
				var usluga_complex_id = uslugacomplex_combo.getValue();

				if ( getRegionNick() == 'ufa' && !newValue ) {
					uslugacomplex_combo.setLpuLevelCode(0);
					return false;
				}

				var index = combo.getStore().findBy(function(rec) {
					if ( rec.get('LpuSection_id') == newValue ) {
						return true;
					}
					else {
						return false;
					}
				});
				var record = combo.getStore().getAt(index);

				uslugacomplex_combo.clearValue();
				uslugacomplex_combo.lastQuery = 'This query sample that is not will never appear';
				uslugacomplex_combo.getStore().removeAll();
				uslugacomplex_combo.getStore().baseParams.query = '';

				if ( record ) {
					if ( getRegionNick() == 'ufa' ) {
						uslugacomplex_combo.setLpuLevelCode(record.get('LpuSectionProfile_Code'));
					} else if ( getRegionNick() == 'buryatiya' ) {
						uslugacomplex_combo.setLpuSectionProfile_id(record.get('LpuSectionProfile_id'));
					}
					uslugacomplex_combo.getStore().load({
						callback: function() {
							index = uslugacomplex_combo.getStore().findBy(function(rec) {
								return (rec.get('UslugaComplex_id') == usluga_complex_id);
							});

							if ( index >= 0 ) {
								uslugacomplex_combo.setValue(usluga_complex_id);
							}
						}
					});
				}
			} else if ( getRegionNick() == 'ekb' ) {
				uslugacomplex_combo.lastQuery = 'This query sample that is not will never appear';
				uslugacomplex_combo.getStore().removeAll();
				uslugacomplex_combo.getStore().baseParams.query = '';

				uslugacomplex_combo.getStore().baseParams.LpuSection_id = newValue;
			} else if ( getRegionNick() == 'kz' ) {
				uslugacomplex_combo.lastQuery = 'This query sample that is not will never appear';
				uslugacomplex_combo.getStore().removeAll();
				uslugacomplex_combo.getStore().baseParams.query = '';

				uslugacomplex_combo.getStore().baseParams.LpuSection_id = newValue;
			}
		}.createDelegate(this));

		this.findById('EVPLSEF_MedPersonalCombo').addListener('change', function(combo, newValue, oldValue) {
			var base_form = this.findById('EvnVizitPLStomEditForm').getForm();
			this.loadLpuSectionProfileDop();
			this.setDefaultMedicalCareKind();

			if (
				!Ext.isEmpty(base_form.findField('LpuDispContract_id').getValue())
				&& (base_form.findField('LpuDispContract_id').getStore().getCount() > 0) // если комбик прогрузился (есть record)
				&& !Ext.isEmpty(base_form.findField('LpuSection_id').getFieldValue('LpuSectionProfile_id'))
				&& base_form.findField('LpuDispContract_id').getFieldValue('LpuSectionProfile_id') != base_form.findField('LpuSection_id').getFieldValue('LpuSectionProfile_id')
			) {
				base_form.findField('LpuDispContract_id').clearValue();
			}
			base_form.findField('LpuDispContract_id').getStore().baseParams.LpuSectionProfile_id = base_form.findField('LpuSection_id').getFieldValue('LpuSectionProfile_id');
			base_form.findField('LpuDispContract_id').lastQuery = 'This query sample that is not will never appear';
			base_form.findField('LpuDispContract_id').getStore().baseParams.query = '';

			if ( getGlobalOptions().region && getGlobalOptions().region.nick == 'ufa' ) {
				var uslugacomplex_combo = base_form.findField('UslugaComplex_uid');

				var usluga_complex_id = uslugacomplex_combo.getValue();

				if ( !newValue ) {
					uslugacomplex_combo.setLpuLevelCode(0);
					return false;
				}

				var index = combo.getStore().findBy(function(rec) {
					if ( rec.get('MedStaffFact_id') == newValue ) {
						return true;
					}
					else {
						return false;
					}
				});
				var record = combo.getStore().getAt(index);

				uslugacomplex_combo.clearValue();
				uslugacomplex_combo.lastQuery = 'This query sample that is not will never appear';
				uslugacomplex_combo.getStore().removeAll();
				uslugacomplex_combo.getStore().baseParams.query = '';

				if ( record ) {
					uslugacomplex_combo.setLpuLevelCode(record.get('LpuSectionProfile_Code'));
					uslugacomplex_combo.getStore().load({
						callback: function() {
							index = uslugacomplex_combo.getStore().findBy(function(rec) {
								return (rec.get('UslugaComplex_id') == usluga_complex_id);
							});

							if ( index >= 0 ) {
								uslugacomplex_combo.setValue(usluga_complex_id);
							}
						}
					});
				}
			} else if ( getRegionNick().inlist(['ekb','perm']) ) {
				this.reloadUslugaComplexField();

				this.filterLpuSectionProfile();
			} else if ( getRegionNick() == 'kz' ) {
				var base_form = this.findById('EvnVizitPLStomEditForm').getForm();

				var uslugacomplex_combo = base_form.findField('UslugaComplex_uid');

				uslugacomplex_combo.lastQuery = 'This query sample that is not will never appear';
				uslugacomplex_combo.getStore().removeAll();
				uslugacomplex_combo.getStore().baseParams.query = '';

				uslugacomplex_combo.getStore().baseParams.LpuSection_id = base_form.findField('LpuSection_id').getValue();
			}
		}.createDelegate(this));

		this.findById('EVPLSEF_LpuSectionProfile').addListener('change', function(combo, newValue, oldValue) {
			if (newValue && current_window.errorControlCodaVisits()) {
				sw.swMsg.show({
					buttons: Ext.Msg.YESNO,
					fn: function (buttonId, text, obj) {
						if (buttonId == 'no') {
							this.setValue(oldValue);
						}
					}.createDelegate(combo),
					icon: Ext.MessageBox.QUESTION,
					msg: langs('Профиль отделения текущего посещения должен соответствовать профилю отделения других посещений в этом ТАП. Продолжить?'),
					title: langs('Предупреждение')
				});
			}
		}.createDelegate(this));
	},
	filterLpuSectionProfile: function() {
		/*var base_form = this.findById('EvnVizitPLStomEditForm').getForm();

		if (getRegionNick() == 'ekb') {
			var combo = base_form.findField('MedStaffFact_id');
			base_form.findField('LpuSectionProfile_id').lastQuery = 'This query sample that is not will never appear';
			base_form.findField('LpuSectionProfile_id').getStore().removeAll();
			base_form.findField('LpuSectionProfile_id').getStore().load({
				params: {
					LpuSection_id: base_form.findField('LpuSection_id').getValue(),
					MedPersonal_id: combo.getFieldValue('MedPersonal_id'),
					LpuSectionProfileGRAPP_CodeIsNotNull: (base_form.findField('PayType_id').getFieldValue('PayType_SysNick') == 'oms' ? 1 : null)
				},
				callback: function () {

				}
			});
		}*/
	},
	setLpuSectionProfile: function() {
		/*if ( getGlobalOptions().region && getGlobalOptions().region.nick.inlist(['ekb']) ) {
			var base_form = this.findById('EvnVizitPLStomEditForm').getForm();
			
			if (Ext.isEmpty(base_form.findField('LpuSectionProfile_id').getValue())) {
				// 1. ищем профиль в отделении
				var LpuSectionProfile_id = base_form.findField('LpuSection_id').getFieldValue('LpuSectionProfile_id');
				if (!Ext.isEmpty(LpuSectionProfile_id)) {
					index = base_form.findField('LpuSectionProfile_id').getStore().findBy(function(rec) {
						return (rec.get('LpuSectionProfile_id') == LpuSectionProfile_id);
					});

					if ( index >= 0 ) {
						base_form.findField('LpuSectionProfile_id').setValue(LpuSectionProfile_id);
						return true;
					}
				}
				// 2. ищем профиль в услуге
				var LpuSectionProfile_id = base_form.findField('UslugaComplex_uid').getFieldValue('LpuSectionProfile_id');
				if (!Ext.isEmpty(LpuSectionProfile_id)) {
					index = base_form.findField('LpuSectionProfile_id').getStore().findBy(function(rec) {
						return (rec.get('LpuSectionProfile_id') == LpuSectionProfile_id);
					});

					if ( index >= 0 ) {
						base_form.findField('LpuSectionProfile_id').setValue(LpuSectionProfile_id);
						return true;
					}
				}
			}
		}*/
	},
	keys: [{
		alt: true,
		fn: function(inp, e) {
			var current_window = Ext.getCmp('EvnVizitPLStomEditWindow');

			switch ( e.getKey() ) {
				case Ext.EventObject.C:
					current_window.doSave({
						ignoreEvnUslugaStomCountCheck: false
					});
				break;

				case Ext.EventObject.J:
					current_window.onCancelAction();
				break;

				case Ext.EventObject.L:
					if ( current_window.findById('EVPLSEF_EvnDiagPLStomSopPanel').isLoaded == false ) {
						current_window.findById('EVPLSEF_EvnDiagPLStomSopPanel').setFocusOnLoad = true;
						current_window.findById('EVPLSEF_EvnDiagPLStomSopPanel').toggleCollapse();
					}
					else {
						current_window.findById('EVPLSEF_EvnDiagPLStomSopPanel').expand();

						if ( current_window.findById('EVPLSEF_EvnDiagPLStomSopGrid').getStore().getCount() > 0 ) {
							current_window.findById('EVPLSEF_EvnDiagPLStomSopGrid').getView().focusRow(0);
							current_window.findById('EVPLSEF_EvnDiagPLStomSopGrid').getSelectionModel().selectFirstRow();
						}
					}
				break;

				case Ext.EventObject.NUM_ONE:
				case Ext.EventObject.ONE:
					current_window.evnDirectionAllInfoPanel.toggleCollapse();
				break;

				case Ext.EventObject.NUM_TWO:
				case Ext.EventObject.TWO:
					current_window.EvnXmlPanel.toggleCollapse();
				break;

				case Ext.EventObject.NUM_THREE:
				case Ext.EventObject.THREE:
					current_window.ToothMapPanel.toggleCollapse();
				break;

				case Ext.EventObject.FOUR:
				case Ext.EventObject.NUM_FOUR:
					current_window.findById('EVPLSEF_DiagPanel').toggleCollapse();
				break;

				case Ext.EventObject.FIVE:
				case Ext.EventObject.NUM_FIVE:
					current_window.findById('EVPLSEF_EvnDiagPLStomSopPanel').toggleCollapse();
				break;

				case Ext.EventObject.NUM_SIX:
				case Ext.EventObject.SIX:
					current_window.findById('EVPLSEF_EvnUslugaStomPanel').toggleCollapse();
				break;

				case Ext.EventObject.NUM_SEVEN:
				case Ext.EventObject.SEVEN:
					//current_window.findById('EVPLSEF_EvnDirectionPanel').toggleCollapse();
				break;
			}
		},
		key: [
			Ext.EventObject.C,
			Ext.EventObject.FIVE,
			Ext.EventObject.FOUR,
			Ext.EventObject.J,
			Ext.EventObject.L,
			Ext.EventObject.NUM_FIVE,
			Ext.EventObject.NUM_FOUR,
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
			var current_window = Ext.getCmp('EvnVizitPLStomEditWindow');

			switch ( e.getKey() ) {
				case Ext.EventObject.ESC:
					current_window.onCancelAction();
				break;
			}
		},
		key: [
			Ext.EventObject.ESC
		],
		stopEvent: true
	}],
	layout: 'border',
	listeners: {
		'hide': function(win) {
			win.onHide({
				EvnDiagPLStomGridIsModified: win.EvnDiagPLStomGridIsModified,
				EvnUslugaGridIsModified: win.EvnUslugaGridIsModified
			});
		},
		'maximize': function(win) {
			win.EvnXmlPanel.doLayout();
			win.findById('EVPLSEF_DiagPanel').doLayout();
			win.findById('EVPLSEF_EvnDiagPLStomPanel').doLayout();
			win.findById('EVPLSEF_EvnDiagPLStomSopPanel').doLayout();
			win.findById('EVPLSEF_EvnUslugaStomPanel').doLayout();
		},
		'restore': function(win) {
			win.EvnXmlPanel.doLayout();
			win.findById('EVPLSEF_DiagPanel').doLayout();
			win.findById('EVPLSEF_EvnDiagPLStomPanel').doLayout();
			win.findById('EVPLSEF_EvnDiagPLStomSopPanel').doLayout();
			win.findById('EVPLSEF_EvnUslugaStomPanel').doLayout();
		}
	},
	loadMesCombo:function (changed) {
		// МЭС - только для Перми
		//log('loadMesCombo', getRegionNick(), this.disabledLoadMesCombo, changed);
		if ( !getRegionNick().inlist([ 'perm' ]) || this.disabledLoadMesCombo || this.formMode == 'morbus' ) {
			return false;
		}
		this.disabledLoadMesCombo = true;
		var base_form = this.findById('EvnVizitPLStomEditForm').getForm();

		var Diag_id = changed.Diag_id || null;
		var EvnVizit_setDate = changed.EvnVizit_setDate || null;
		var LpuSection_id = changed.LpuSection_id || null;
		var EvnVizit_id = base_form.findField('EvnVizitPLStom_id').getValue();
		var Mes_id = base_form.findField('Mes_id').getValue();
		var Person_id = base_form.findField('Person_id').getValue();

		base_form.findField('Mes_id').disable();
		base_form.findField('Mes_id').getStore().removeAll();
		base_form.findField('Mes_id').setAllowBlank(true);

		if ( !Diag_id || !EvnVizit_setDate || !LpuSection_id || !Person_id ) {
			this.disabledLoadMesCombo = false;
			return false;
		}

		base_form.findField('Mes_id').getStore().load({
			callback: function () {
				this.disabledLoadMesCombo = false;
				var index;
				var record;

				if ( base_form.findField('Mes_id').getStore().getCount() > 0 ) {
					if ( this.action != 'view' ) {
						base_form.findField('Mes_id').enable();
					}
					base_form.findField('Mes_id').setAllowBlank(false);

					if ( base_form.findField('Mes_id').getStore().getCount() == 1 ) {
						index = 0;
					} else {
						index = base_form.findField('Mes_id').getStore().findBy(function(rec) {
							return (rec.get('Mes_id') == Mes_id);
						});
					}

					record = base_form.findField('Mes_id').getStore().getAt(index);

					if ( record ) {
						base_form.findField('Mes_id').setValue(record.get('Mes_id'));
						base_form.findField('Mes_id').fireEvent('select', base_form.findField('Mes_id'), record);
					} else {
						base_form.findField('Mes_id').clearValue();
						base_form.findField('Mes_id').fireEvent('change', base_form.findField('Mes_id'), null);
					}
				} else {
					base_form.findField('Mes_id').clearValue();
					base_form.findField('Mes_id').fireEvent('change', base_form.findField('Mes_id'), null);
				}
			}.createDelegate(this),
			params: {
				 Diag_id: Diag_id
				,EvnVizit_id: EvnVizit_id
				,EvnVizit_setDate: Ext.util.Format.date(EvnVizit_setDate, 'd.m.Y')
				,LpuSection_id: LpuSection_id
				,Person_id: Person_id
			}
		});
	},
	setBitePersonCombo: function(person_id){
		var base_form = this.findById('EvnVizitPLStomEditForm').getForm();

		Ext.Ajax.request({
			callback: function (options, success, response) {
				if (success) {
					if (response && response.responseText) {
						var response_obj = Ext.util.JSON.decode(response.responseText);
						if (response_obj && response_obj.BitePersonType_id) {
							base_form.findField('BitePersonType_id').setValue(response_obj.BitePersonType_id);
						}
					}
				} else {
					sw.swMsg.alert('Ошибка', 'При удалении посещения возникли ошибки');
				}
			},
			params: {
				Person_id: person_id
			},
			url: '/?c=EvnPLStom&m=getCurrentBitePersonData'
		});
	},
	loadMesEkbCombo: function(options) {
		if (getRegionNick() != 'ekb') { return false; }
		var base_form = this.findById('EvnVizitPLStomEditForm').getForm();
		var callback = Ext.emptyFn;
		if (options && options.callback) {
			callback = options.callback;
		}
		var mesekb = base_form.findField('MesEkb_id');

		var date = getGlobalOptions().date;
		if (!Ext.isEmpty(base_form.findField('EvnVizitPLStom_setDate').getValue())) {
			date = base_form.findField('EvnVizitPLStom_setDate').getValue();
		}

		/*var mes_codes = [];
		if (date < new Date(2015,0,1)) {
			mes_codes = ['1115', '1135', '1145'];
		} else {
			mes_codes = ['5511', '5512', '5513', '5521', '5522', '5531', '5532', '5541', '5542', '5551', '5552']
		}
		mesekb.getStore().baseParams.Mes_Codes = Ext.util.JSON.encode(mes_codes);*/
		mesekb.getStore().baseParams.MesType_id = 12;

		var usluga_complex_uid = base_form.findField('UslugaComplex_uid').getValue();
		if (usluga_complex_uid > 0) {
			mesekb.getStore().baseParams.UslugaComplex_id = usluga_complex_uid;
		} else {
			mesekb.getStore().baseParams.UslugaComplex_id = null;
		}
		mesekb.getStore().load({callback: callback});
	},
	maximizable: true,
	minHeight: 550,
	minWidth: 700,
	modal: true,
	onCancelAction: function() {
		var me = this,
			evn_vizit_pl_stom_id = this.findById('EvnVizitPLStomEditForm').getForm().findField('EvnVizitPLStom_id').getValue();

		if ( evn_vizit_pl_stom_id > 0 && this.action == 'add' ) {
			var deleteEvnVizit = function() {
				// удалить посещение
				// закрыть окно после успешного удаления
				var loadMask = new Ext.LoadMask(me.getEl(), { msg: "Удаление посещения..." });
				loadMask.show();
				Ext.Ajax.request({
					callback: function(options, success, response) {
						loadMask.hide();
						if ( success ) {
							if (response && response.responseText) {
								var response_obj = Ext.util.JSON.decode(response.responseText);
								if (response_obj && response_obj.success) {
									me.ToothMapPanel.doClear(true);
									me.hide();
								}
							}
						} else {
							sw.swMsg.alert('Ошибка', 'При удалении посещения возникли ошибки');
						}
					},
					params: {
						Evn_id: evn_vizit_pl_stom_id
					},
					url: '/?c=Evn&m=deleteEvn'
				});
			};
			deleteEvnVizit();
		} else {
			this.hide();
		}
	},
	onHide: Ext.emptyFn,
	openEvnDiagPLStomEditWindow: function(action) {
		if ( Ext.isEmpty(action) || !action.inlist([ 'add', 'edit', 'view' ]) ) {
			return false;
		}

		var base_form = this.findById('EvnVizitPLStomEditForm').getForm();
		var grid = this.findById('EVPLSEF_EvnDiagPLStomGrid');

		if ( this.action == 'view') {
			if ( action == 'add') {
				return false;
			}
			else if ( action == 'edit' ) {
				action = 'view';
			}
		}

		if ( getWnd('swEvnDiagPLStomEditWindow').isVisible() ) {
			sw.swMsg.alert('Сообщение', 'Окно редактирования заболевания уже открыто');
			return false;
		}

		if ( base_form.findField('EvnVizitPLStom_id').getValue() == 0 ) {
			this.doSave({
				ignoreEvnUslugaStomCountCheck: true,
				openChildWindow: function() {
					this.openEvnDiagPLStomEditWindow(action);
				}.createDelegate(this)
			});
			return false;
		}

		var params = new Object();
		var formParams = new Object();

		params.action = action;
		params.callback = function(data) {
			/*if ( !data || !data.evnDiagPLStomData ) {
				return false;
			}

			if ( data.evnDiagPLStomData[0].EvnDiagPLStom_pid == base_form.findField('EvnVizitPLStom_id').getValue() ) {
				data.evnDiagPLStomData[0].EvnDiagPLStom_IsThisVizit = 'X';
			}
			else {
				data.evnDiagPLStomData[0].EvnDiagPLStom_IsThisVizit = '';
			}

			var record = grid.getStore().getById(data.evnDiagPLStomData[0].EvnDiagPLStom_id);

			if ( !record ) {
				if ( grid.getStore().getCount() == 1 && !grid.getStore().getAt(0).get('EvnDiagPLStom_id') ) {
					grid.getStore().removeAll();
				}

				grid.getStore().loadData(data.evnDiagPLStomData, true);
			}
			else {
				var evn_diag_pl_stom_fields = new Array();
				var i = 0;

				grid.getStore().fields.eachKey(function(key, item) {
					evn_diag_pl_stom_fields.push(key);
				});

				for ( i = 0; i < evn_diag_pl_stom_fields.length; i++ ) {
					record.set(evn_diag_pl_stom_fields[i], data.evnDiagPLStomData[0][evn_diag_pl_stom_fields[i]]);
				}

				record.commit();
			}*/

			grid.getStore().reload();

			this.EvnDiagPLStomGridIsModified = true;
			this.loadDiagNewCombo({
				ignoreCurrentValue: true
			});
		}.createDelegate(this);
		params.formMode = this.formMode;
		params.onHide = function(options) {
			if ( this.findById('EVPLSEF_EvnUslugaStomPanel').isLoaded === true && options.EvnUslugaGridIsModified === true ) {
				this.EvnUslugaGridIsModified = true;

				this.findById('EVPLSEF_EvnUslugaStomGrid').getStore().load({
					params: {
						pid: base_form.findField('EvnVizitPLStom_id').getValue()
					}
				});
			}

			grid.getView().focusRow(0);
			grid.getSelectionModel().selectFirstRow();
		}.createDelegate(this);
		params.Person_id = this.findById('EVPLSEF_PersonInformationFrame').getFieldValue('Person_id');
		params.Person_Birthday = this.findById('EVPLSEF_PersonInformationFrame').getFieldValue('Person_Birthday');
		params.Person_Firname = this.findById('EVPLSEF_PersonInformationFrame').getFieldValue('Person_Firname');
		params.Person_Secname = this.findById('EVPLSEF_PersonInformationFrame').getFieldValue('Person_Secname');
		params.Person_Surname = this.findById('EVPLSEF_PersonInformationFrame').getFieldValue('Person_Surname');

		var
			evn_vizit_pl_stom_set_date = base_form.findField('EvnVizitPLStom_setDate').getValue(),
			lpu_section_id = base_form.findField('LpuSection_id').getValue(),
			lpu_section_profile_id = base_form.findField('LpuSectionProfile_id').getValue(),
			med_personal_id = base_form.findField('MedStaffFact_id').getFieldValue('MedPersonal_id'),
			med_staff_fact_id = base_form.findField('MedStaffFact_id').getValue(),
			pay_type_id = base_form.findField('PayType_id').getValue();

		if ( !evn_vizit_pl_stom_set_date || !lpu_section_id || !med_personal_id || !pay_type_id ) {
			sw.swMsg.alert('Сообщение', 'Не заданы обязательные параметры посещения');
			return false;
		}

		params.evnVizitData = {
			EvnVizitPLStom_id: base_form.findField('EvnVizitPLStom_id').getValue(),
			EvnVizitPLStom_setDate: evn_vizit_pl_stom_set_date,
			LpuSection_id: lpu_section_id,
			LpuSectionProfile_id: lpu_section_profile_id,
			MedStaffFact_id: med_staff_fact_id,
			MedPersonal_id: med_personal_id,
			PayType_id: pay_type_id,
			MesEkb_id: base_form.findField('MesEkb_id').getValue()
		};

		if ( action == 'add' ) {
			formParams.EvnDiagPLStom_id = 0;
			formParams.EvnDiagPLStom_rid = base_form.findField('EvnPLStom_id').getValue();
			formParams.EvnDiagPLStom_pid = base_form.findField('EvnVizitPLStom_id').getValue();
			formParams.EvnDiagPLStom_setDate = evn_vizit_pl_stom_set_date;
			formParams.Person_id = base_form.findField('Person_id').getValue();
			formParams.PersonEvn_id = base_form.findField('PersonEvn_id').getValue();
			formParams.Server_id = base_form.findField('Server_id').getValue();
		}
		else {
			var selected_record = grid.getSelectionModel().getSelected();

			if ( !selected_record || !selected_record.get('EvnDiagPLStom_id') ) {
				return false;
			}

			var DateX20190601 = new Date(2019, 5, 1);

			if (
				getRegionNick() == 'penza'
				&& action == 'edit'
				&& base_form.findField('EvnVizitPLStom_id').getValue() != selected_record.get('EvnDiagPLStom_pid')
				&& (Ext.isEmpty(base_form.findField('EvnVizitPLStom_setDate').getValue()) || base_form.findField('EvnVizitPLStom_setDate').getValue().format('d.m.Y') != selected_record.get('EvnVizitPLStom_setDate').format('d.m.Y'))
				&& (selected_record.get('ServiceType_SysNick') == 'neotl' || selected_record.get('ServiceType_SysNick') == 'polnmp')
				&& !Ext.isEmpty(selected_record.get('hasUslugaType03'))
				&& selected_record.get('EvnDiagPLStom_setDate') >= new Date(2018, 7, 1)
				// Данный контроль выполняется для случаев, созданных до 01.06.2019 (#167177 Регион: Пенза)
				&& getValidDT(base_form.findField('EvnPLStom_setDate').getValue(), '') < DateX20190601
			) {
				sw.swMsg.alert(langs('Ошибка'), langs('Выбранное заболевание содержит услуги по неотложной помощи, в этом случае для внесения изменений в заболевание дата посещения должна совпадать с датой начала заболевания'));
				return false;
			}

			formParams.EvnDiagPLStom_id = selected_record.get('EvnDiagPLStom_id');
		}

		params.formParams = formParams;
		params.archiveRecord = this.archiveRecord;

		getWnd('swEvnDiagPLStomEditWindow').show(params);
	},
	openEvnDiagPLStomSopEditWindow: function(action) {
		if ( action != 'add' && action != 'edit' && action != 'view' ) {
			return false;
		}

		var base_form = this.findById('EvnVizitPLStomEditForm').getForm();
		var grid = this.findById('EVPLSEF_EvnDiagPLStomSopGrid');

		if ( this.action == 'view') {
			if ( action == 'add') {
				return false;
			}
			else if ( action == 'edit' ) {
				action = 'view';
			}
		}

		if ( getWnd('swEvnDiagPLStomSopEditWindow').isVisible() ) {
			sw.swMsg.alert('Сообщение', 'Окно редактирования сопутствующего диагноза уже открыто');
			return false;
		}

		if ( action == 'add' && base_form.findField('EvnVizitPLStom_id').getValue() == 0 ) {
			this.doSave({
				ignoreEvnUslugaStomCountCheck: true,
				openChildWindow: function() {
					this.openEvnDiagPLStomSopEditWindow(action);
				}.createDelegate(this)
			});
			return false;
		}

		var params = new Object();
		var formParams = new Object();

		params.action = action;
		params.callback = function(data) {
			if ( !data || !data.evnDiagPLStomSopData ) {
				return false;
			}

			var record = grid.getStore().getById(data.evnDiagPLStomSopData[0].EvnDiagPLStomSop_id);

			if ( !record ) {
				if ( grid.getStore().getCount() == 1 && !grid.getStore().getAt(0).get('EvnDiagPLStomSop_id') ) {
					grid.getStore().removeAll();
				}

				grid.getStore().loadData(data.evnDiagPLStomSopData, true);
			}
			else {
				var evn_diag_pl_stom_fields = new Array();
				var i = 0;

				grid.getStore().fields.eachKey(function(key, item) {
					evn_diag_pl_stom_fields.push(key);
				});

				for ( i = 0; i < evn_diag_pl_stom_fields.length; i++ ) {
					record.set(evn_diag_pl_stom_fields[i], data.evnDiagPLStomSopData[0][evn_diag_pl_stom_fields[i]]);
				}

				record.commit();
			}
		}.createDelegate(this);
		params.onHide = function() {
			grid.getView().focusRow(0);
			grid.getSelectionModel().selectFirstRow();
		}.createDelegate(this);
		params.Person_id = this.findById('EVPLSEF_PersonInformationFrame').getFieldValue('Person_id');
		params.Person_Birthday = this.findById('EVPLSEF_PersonInformationFrame').getFieldValue('Person_Birthday');
		params.Person_Firname = this.findById('EVPLSEF_PersonInformationFrame').getFieldValue('Person_Firname');
		params.Person_Secname = this.findById('EVPLSEF_PersonInformationFrame').getFieldValue('Person_Secname');
		params.Person_Surname = this.findById('EVPLSEF_PersonInformationFrame').getFieldValue('Person_Surname');

		var evn_vizit_pl_stom_set_date = base_form.findField('EvnVizitPLStom_setDate').getValue();
		var lpu_section_id = base_form.findField('LpuSection_id').getValue();
		var med_personal_id = null;
		var med_staff_fact_id = base_form.findField('MedStaffFact_id').getValue();

		var record = base_form.findField('MedStaffFact_id').getStore().getById(med_staff_fact_id);
		if ( record ) {
			med_personal_id = record.get('MedPersonal_id');
		}

		if ( !evn_vizit_pl_stom_set_date || !lpu_section_id || !med_personal_id ) {
			sw.swMsg.alert('Сообщение', 'Не заданы обязательные параметры посещения');
			return false;
		}

		if ( action == 'add' ) {
			formParams.EvnDiagPLStomSop_id = 0;
			formParams.EvnDiagPLStomSop_pid = base_form.findField('EvnVizitPLStom_id').getValue();
			formParams.EvnDiagPLStomSop_setDate = evn_vizit_pl_stom_set_date;
			formParams.Person_id = base_form.findField('Person_id').getValue();
			formParams.PersonEvn_id = base_form.findField('PersonEvn_id').getValue();
			formParams.Server_id = base_form.findField('Server_id').getValue();
		}
		else {
			var selected_record = grid.getSelectionModel().getSelected();

			if ( !selected_record || !selected_record.get('EvnDiagPLStomSop_id') ) {
				return false;
			}

			formParams.EvnDiagPLStomSop_id = selected_record.get('EvnDiagPLStomSop_id');
		}

		params.formParams = formParams;
		params.archiveRecord = this.archiveRecord;

		getWnd('swEvnDiagPLStomSopEditWindow').show(params);
	},
	openEvnUslugaStomEditWindow: function(action) {
		if ( !action.inlist(['add','addByMes','edit','view']) ) {
			return false;
		}

		var base_form = this.findById('EvnVizitPLStomEditForm').getForm();
		var grid = this.findById('EVPLSEF_EvnUslugaStomGrid');

		if ( getRegionNick() == 'perm' && this.formMode == 'morbus' ) {
			if ( action.inlist([ 'add', 'addByMes' ]) ) {
				return false;
			}

			action = 'view';
		}

		if ( this.action == 'view') {
			if ( action == 'add' || action == 'addByMes') {
				return false;
			}
			else if ( action == 'edit' ) {
				action = 'view';
			}
		}

		if ( (action == 'add' || action == 'addByMes') && base_form.findField('EvnVizitPLStom_id').getValue() == 0 ) {
			this.doSave({
				ignoreEvnUslugaStomCountCheck: true,
				openChildWindow: function() {
					this.openEvnUslugaStomEditWindow(action);
				}.createDelegate(this)
			});
			return false;
		}

		var params = new Object();

		var person_id = this.findById('EVPLSEF_PersonInformationFrame').getFieldValue('Person_id');
		var person_birthday = this.findById('EVPLSEF_PersonInformationFrame').getFieldValue('Person_Birthday');
		var person_firname = this.findById('EVPLSEF_PersonInformationFrame').getFieldValue('Person_Firname');
		var person_secname = this.findById('EVPLSEF_PersonInformationFrame').getFieldValue('Person_Secname');
		var person_surname = this.findById('EVPLSEF_PersonInformationFrame').getFieldValue('Person_Surname');
		var mes_count = this.findById('EVPLSEF_PersonInformationFrame').getFieldValue('EvnVizitPLStom_UetOMS');

		/*var base_form = this.findById('EvnVizitPLStomEditForm').getForm();
		base_form.findField('EvnVizitPLStom_UetOMS').setValue(evn_usluga_stom_uet_oms.toFixed(2));*/

		if ( action == 'add' || action == 'addByMes' ) {
			params.EvnUslugaStom_id = 0;
			if ('ekb' == getRegionNick()) {
				params.EvnUslugaStom_rid = base_form.findField('EvnPLStom_id').getValue();
			}
			params.EvnUslugaStom_rid = base_form.findField('EvnPLStom_id').getValue();
			params.EvnUslugaStom_pid = base_form.findField('EvnVizitPLStom_id').getValue();
			params.EvnUslugaStom_setDate = base_form.findField('EvnVizitPLStom_setDate').getValue();
			params.MedStaffFact_id = base_form.findField('MedStaffFact_id').getValue();
			params.LpuSection_uid = base_form.findField('LpuSection_id').getValue();
			params.PayType_id = base_form.findField('PayType_id').getValue();
			params.Person_id = base_form.findField('Person_id').getValue();
			params.PersonEvn_id = base_form.findField('PersonEvn_id').getValue();
			params.Server_id = base_form.findField('Server_id').getValue();
		}
		else {
			var selected_record = grid.getSelectionModel().getSelected();

			if ( !selected_record || !selected_record.get('EvnUsluga_id') ) {
				return false;
			}

			params.EvnUslugaStom_id = selected_record.get('EvnUsluga_id');
		}

		var mes_id = base_form.findField('Mes_id').getValue();
		if (getRegionNick() == 'ekb') {
			mes_id = base_form.findField('MesEkb_id').getValue();
		}
		var winParams = {
			action: action,
			callback: function(data) {
				grid.getStore().load({
					params: {
						pid: base_form.findField('EvnVizitPLStom_id').getValue()
					},
					callback: function() {
						this.uetValuesRecount();
					}.createDelegate(this)
				});
				if (this.ToothMapPanel.isLoaded) {
					this.ToothMapPanel.doReloadViewData();
				}
				this.EvnUslugaGridIsModified = true;
			}.createDelegate(this),
			formMode: this.formMode,
			formParams: params,
			Mes_id: mes_id,
			Mes_Spend: (mes_count)?(mes_count):0,
			Mes_Total: base_form.findField('Mes_id').getFieldValue('Mes_KoikoDni'),
			onHide: function() {
				grid.getView().focusRow(0);
				grid.getSelectionModel().selectFirstRow();
			}.createDelegate(this),
			Person_id: person_id,
			Person_Birthday: person_birthday,
			Person_Firname: person_firname,
			Person_Secname: person_secname,
			Person_Surname: person_surname
		};

		if (getRegionNick() == 'perm' && base_form.findField('EvnVizitPLStom_RepFlag').checked) {
			winParams.ignorePaidCheck = 1;
		}

		var win = (action == 'addByMes') ? getWnd('swEvnUslugaStomByMesInputWindow') : getWnd('swEvnUslugaStomEditWindow');
		if ( win.isVisible() ) {
			sw.swMsg.alert('Сообщение', 'Окно выполнения стоматологической услуги уже открыто');
			return false;
		}
		winParams.archiveRecord = this.archiveRecord;
		win.show(winParams);
	},
	plain: true,
	resizable: true,
	setEvnVizitPLStomPeriod: function() {
		var base_form = this.findById('EvnVizitPLStomEditForm').getForm();

		var EvnVizitPLStom_maxDate = base_form.findField('EvnVizitPLStom_setDate').maxValue;

		var
			currentDate = getValidDT(getGlobalOptions().date, ''),
			EvnPLStom_setDate = getValidDT(base_form.findField('EvnPLStom_setDate').getValue(), ''),
			xDate = sw.Promed.EvnPL.getEvnPLStomNewBegDate();

		var  minValue = xDate.format('d.m.Y');
		// для первого посещения надо тоже ограничить дату посещения, иначе в ТАП уйдет не та дата, а от неё зависит тип формы.
		var maxValue = ((getRegionNick() == 'perm' || !this.isRepeatVizit) && xDate.add('D', -1) <= currentDate ? xDate.add('D', -1).format('d.m.Y') : getGlobalOptions().date);

		if ( EvnPLStom_setDate >= xDate ) {
			base_form.findField('EvnVizitPLStom_setDate').setMinValue(minValue);
		}
		else {
			base_form.findField('EvnVizitPLStom_setDate').setMaxValue(maxValue);
		}
	},
	loadDiagNewCombo: function(options) {
		if ( this.formMode != 'morbus' ) {
			return false;
		}

		if ( typeof options != 'object' ) {
			options = new Object();
		}

		var base_form = this.findById('EvnVizitPLStomEditForm').getForm();
		var currentValue = base_form.findField('Diag_newid').getValue();
		var savedValue = base_form.findField('Diag_id').getValue();

		base_form.findField('Diag_newid').setAllowBlank(false);
		base_form.findField('Diag_newid').enable();

		base_form.findField('Diag_newid').getStore().baseParams.EvnPLStom_id = base_form.findField('EvnPLStom_id').getValue();
		base_form.findField('Diag_newid').getStore().load({
			params: {
				EvnPLStom_id: base_form.findField('EvnPLStom_id').getValue(),
				EvnVizitPLStom_id: base_form.findField('EvnVizitPLStom_id').getValue()
			},
			callback: function() {
				var index = -1;
				if (!options.ignoreCurrentValue) {
					// если нашли выбранное, то выбираем его
					index = base_form.findField('Diag_newid').getStore().findBy(function (rec) {
						return (rec.get('Diag_id') == currentValue);
					});
					if (index == -1) {
						// иначе если совпадает с Diag_id сохранённым в БД
						index = base_form.findField('Diag_newid').getStore().findBy(function (rec) {
							return (rec.get('Diag_id') == savedValue);
						});
					}
				}

				if (index == -1) {
					// иначе если совпадает с Diag_id заболевания по текущему посещению
					index = base_form.findField('Diag_newid').getStore().findBy(function (rec) {
						return (rec.get('Diag_IsCurrent') == 2);
					});
				}

				if (index >= 0) {
					base_form.findField('Diag_newid').setValue(base_form.findField('Diag_newid').getStore().getAt(index).get('Diag_id'));
				} else if (base_form.findField('Diag_newid').getStore().getCount() > 0) {
					// иначе первое попавшееся
					base_form.findField('Diag_newid').setValue(base_form.findField('Diag_newid').getStore().getAt(0).get('Diag_id'));
				} else {
					// иначе очищаем
					base_form.findField('Diag_newid').clearValue();
					base_form.findField('Diag_newid').setAllowBlank(true);
					base_form.findField('Diag_newid').disable();
				}
				
				base_form.findField('Diag_newid').fireEvent('change', base_form.findField('Diag_newid'), base_form.findField('Diag_newid').getValue());
			}
		});
	},
	setDefaultMedicalCareKind: function() {
		if ( this.formMode != 'morbus' ) { // только для нового функционала
			return false;
		}

		var base_form = this.findById('EvnVizitPLStomEditForm').getForm();

		// устанавливаем только при добавлении
		if (this.action == 'add') {
			// Если должность врача посещения «Средняя» (v_medstafffact.PostKind_id = 6)
			// или выбрано значение в поле «средний мед.персонал»
			// или тип группы отделений ФАП,
			// то вид мед. помощи = 11
			if  (
				base_form.findField('MedStaffFact_id').getFieldValue('PostKind_id') == 6
				|| !Ext.isEmpty(base_form.findField('MedStaffFact_sid').getValue())
				|| base_form.findField('LpuSection_id').getFieldValue('LpuUnitType_SysNick') == 'fap'
			) {
				base_form.findField('MedicalCareKind_id').setFieldValue('MedicalCareKind_Code', '11');
			}
			// Если должность врача отмечена «Первичное звено», то вид мед. помощи = 12
			else if (
				base_form.findField('MedStaffFact_id').getFieldValue('Post_IsPrimaryHealthCare') == 2
			) {
				base_form.findField('MedicalCareKind_id').setFieldValue('MedicalCareKind_Code', '12');
			}
			// 13 – В остальных случаях
			else {
				base_form.findField('MedicalCareKind_id').setFieldValue('MedicalCareKind_Code', '13');
			}
		}

		// @task https://redmine.swan.perm.ru/issues/84712
		if ( getRegionNick() == 'ufa' ) {
			var LpuSectionProfile_id = base_form.findField('LpuSectionProfile_id').getValue();
			base_form.findField('MedicalCareKind_id').getStore().findBy(function(rec) {
				swMedicalCareKindLpuSectionProfileGlobalStore.findBy(function(r) {
					if (r.get('LpuSectionProfile_id') == LpuSectionProfile_id && r.get('MedicalCareKind_id') == rec.get('MedicalCareKind_id')) {
						base_form.findField('MedicalCareKind_id').setValue(r.get('MedicalCareKind_id'));
					}
				});
			});
		}

		// @task https://redmine.swan.perm.ru/issues/84712
		if ( getRegionNick() == 'kareliya' ) {
			var FedMedSpec_id = base_form.findField('MedStaffFact_id').getFieldValue('FedMedSpec_id');
			base_form.findField('MedicalCareKind_id').getStore().findBy(function(rec) {
				swMedSpecLinkGlobalStore.findBy(function(r) {
					if (r.get('MedSpec_id') == FedMedSpec_id && r.get('MedicalCareKind_id') == rec.get('MedicalCareKind_id')) {
						base_form.findField('MedicalCareKind_id').setValue(r.get('MedicalCareKind_id'));
					}
				});
			});
		}

		// @task https://redmine.swan.perm.ru/issues/84712
		// @task https://redmine.swan.perm.ru//issues/109385
		if ( getRegionNick() == 'ekb' ) {
			var
				Diag_Code = base_form.findField('Diag_newid').getFieldValue('Diag_Code'),
				FedMedSpec_Code = base_form.findField('MedStaffFact_id').getFieldValue('FedMedSpec_Code'),
				FedMedSpecParent_Code = base_form.findField('MedStaffFact_id').getFieldValue('FedMedSpecParent_Code'),
				PayType_SysNick = base_form.findField('PayType_id').getFieldValue('PayType_SysNick'),
				MedicalCareKind_Code = base_form.findField('LpuSection_id').getFieldValue('MedicalCareKind_Code');

			if ( PayType_SysNick == 'bud' && !Ext.isEmpty(MedicalCareKind_Code) && !MedicalCareKind_Code.toString().inlist([ '4', '11', '12', '13' ]) ) {
				base_form.findField('MedicalCareKind_id').setFieldValue('MedicalCareKind_Code', MedicalCareKind_Code);
			}
			else if ( Diag_Code == 'Z51.5' ) {
				base_form.findField('MedicalCareKind_id').setFieldValue('MedicalCareKind_Code', '4');
			}
			else if ( !Ext.isEmpty(FedMedSpecParent_Code) ) {
				if ( FedMedSpecParent_Code.toString() == '204' ) { // если HIGH = 204;
					base_form.findField('MedicalCareKind_id').setFieldValue('MedicalCareKind_Code', '11');
				} else { // если HIGH=0;
					if ( FedMedSpec_Code && FedMedSpec_Code.toString().inlist([ '16', '22', '27' ]) ) {
						base_form.findField('MedicalCareKind_id').setFieldValue('MedicalCareKind_Code', '12');
					}
					else {
						base_form.findField('MedicalCareKind_id').setFieldValue('MedicalCareKind_Code', '13');
					}
				}
			}
			else {
				base_form.findField('MedicalCareKind_id').clearValue();
			}
		}

		// @task https://redmine.swan-it.ru/issues/155514
		if ( getRegionNick() == 'vologda' && this.action == 'add') {
			var
				FedMedSpec_Code = base_form.findField('MedStaffFact_id').getFieldValue('FedMedSpec_Code'),
				FedMedSpecParent_Code = base_form.findField('MedStaffFact_id').getFieldValue('FedMedSpecParent_Code');

			if ( !Ext.isEmpty(FedMedSpecParent_Code) ) {
				if ( FedMedSpecParent_Code.toString() == '204' ) { // если HIGH = 204;
					base_form.findField('MedicalCareKind_id').setFieldValue('MedicalCareKind_Code', '11');
				}
				else { // если HIGH = 0;
					if ( FedMedSpec_Code && FedMedSpec_Code.toString().inlist([ '16', '22', '27' ]) ) {
						base_form.findField('MedicalCareKind_id').setFieldValue('MedicalCareKind_Code', '12');
					}
					else {
						base_form.findField('MedicalCareKind_id').setFieldValue('MedicalCareKind_Code', '13');
					}
				}
			}
			else if ( FedMedSpec_Code && FedMedSpec_Code.toString() == '204' ) { // если HIGH = 204;
				base_form.findField('MedicalCareKind_id').setFieldValue('MedicalCareKind_Code', '11');
			}
			else {
				base_form.findField('MedicalCareKind_id').clearValue();
			}
		}
	},
	loadingInProgress: false,
	show: function() {
		sw.Promed.swEvnVizitPLStomEditWindow.superclass.show.apply(this, arguments);

		var win = this;

		this.restore();
		this.center();
		this.maximize();

		this.EvnXmlPanel.collapse();
		this.findById('EVPLSEF_EvnDiagPLStomPanel').collapse();
		this.findById('EVPLSEF_EvnDiagPLStomSopPanel').collapse();

		this.evnDirectionAllInfoPanel.onReset(this);

		var base_form = this.findById('EvnVizitPLStomEditForm').getForm();
		base_form.reset();

		this.setEvnVizitPLStomPeriod();

		this.EvnXmlPanel.doReset();
		this.EvnXmlPanel.LpuSectionField = base_form.findField('LpuSection_id');
		this.EvnXmlPanel.MedStaffFactField = base_form.findField('MedStaffFact_id');

		var
			isEkb = (getRegionNick() == 'ekb'),
			isKareliya = (getRegionNick() == 'kareliya'),
			isPskov = (getRegionNick() == 'pskov'),
			isUfa = (getRegionNick() == 'ufa'),
			isBur = (getRegionNick() == 'buryatiya'),
			isPerm = (getRegionNick() == 'perm');

		base_form.findField('PayType_id').enable();
		base_form.findField('EvnPLDisp_id').setContainerVisible(!isUfa);
		base_form.findField('DispClass_id').setContainerVisible(!isUfa);
		base_form.findField('DispProfGoalType_id').setContainerVisible(isUfa);
		base_form.findField('DispProfGoalType_id').setAllowBlank(true);
		base_form.findField('LpuDispContract_id').setContainerVisible(!isKareliya);
        base_form.findField('HealthKind_id').disable();
		base_form.findField('PersonDisp_id').setAllowBlank(true);

		base_form.findField('EvnVizitPLStom_setDate').setMaxValue(undefined);
		base_form.findField('EvnVizitPLStom_setDate').setMinValue(undefined);

		base_form.findField('LpuSectionProfile_id').getStore().baseParams = {};
		base_form.findField('LpuSectionProfile_id').getStore().removeAll();
		
		base_form.findField('UslugaComplexTariff_id').setContainerVisible(isPerm);
		base_form.findField('EvnUslugaStom_UED').setContainerVisible(isPerm || isPskov);

		this.action = null;
		this.allowMorbusVizitOnly = 0;
		this.allowNonMorbusVizitOnly = 0;
		this.callback = Ext.emptyFn;
		this.EvnDiagPLStomGridIsModified = false;
		this.EvnUslugaGridIsModified = false;
		this.formStatus = 'edit';
		this.loadLastData = false;
		this.onHide = Ext.emptyFn;
		this.streamInput = false;
		this.OtherVizitList = null;
		this.TreatmentClass_id = null;

		this.FormType = null;
		this.from = null;
		this.TimetableGraf_id = null;
		base_form.findField('Diag_id').filterDate = null;
		this.findById('EVPLSEF_EvnDiagPLStomSopPanel').setFocusOnLoad = false;

		if ( !arguments[0] || !arguments[0].formParams ) {
			sw.swMsg.alert('Сообщение', 'Неверные параметры', function() { this.hide(); }.createDelegate(this) );
			return false;
		}

		if(arguments && arguments[0]){
			var formParams = arguments[0];
		}

		if (Ext.isArray(arguments[0].OtherVizitList) && arguments[0].OtherVizitList.length>0) {
			this.OtherVizitList = arguments[0].OtherVizitList;
		}else if(getRegionNick() == 'vologda'){
			//если параметров нет, то попробуем их получить
			this.loadEvnVizitPLStomNodeList(arguments[0].formParams);
		}
		
		if ( arguments[0].action ) {
			this.action = arguments[0].action;
			if ( this.action == 'add' ) {
			//http://redmine.swan.perm.ru/issues/20417
				if ( arguments[0].allowMorbusVizitOnly == true ) {
					this.allowMorbusVizitOnly = 1;
				}

				if ( arguments[0].allowNonMorbusVizitOnly == true ) {
					this.allowNonMorbusVizitOnly = 1;
				}
			}
		}

		base_form.findField('UslugaComplex_uid').clearBaseParams();
		// Устанавливаем фильтры для кодов посещений
		base_form.findField('UslugaComplex_uid').setVizitCodeFilters({
			isStom: true,
			allowNonMorbusVizitOnly: 1==this.allowNonMorbusVizitOnly,
			allowMorbusVizitOnly: 1==this.allowMorbusVizitOnly
		});
		base_form.findField('UslugaComplex_uid').setUslugaComplexDate(null);

		if ( arguments[0].callback ) {
			this.callback = arguments[0].callback;
		}

		if ( arguments[0].loadLastData ) {
			this.loadLastData = arguments[0].loadLastData;
		}
		this.isRepeatVizit = arguments[0].isRepeatVizit || false;

		if ( arguments[0].onHide ) {
			this.onHide = arguments[0].onHide;
		}
				
		if ( arguments[0].FormType ) {
			this.from = arguments[0].FormType;
		}
		
		if ( arguments[0].from ) {
			this.from = arguments[0].from;
		}
				
		if ( arguments[0].TimetableGraf_id ) {
			this.TimetableGraf_id = arguments[0].TimetableGraf_id;
		}

		if ( arguments[0].streamInput ) {
			this.streamInput = arguments[0].streamInput;
		}

		if ( arguments[0].TreatmentClass_id ) {
			this.TreatmentClass_id = arguments[0].TreatmentClass_id;
		}

		this.lastEvnVizitPLStomDate = arguments[0].lastEvnVizitPLStomDate || null;
		this.userMedStaffFact = (this.streamInput == true || Ext.isEmpty(sw.Promed.MedStaffFactByUser.current) || sw.Promed.MedStaffFactByUser.current.ARMType == 'mstat' ? new Object() : sw.Promed.MedStaffFactByUser.current);

		// - Загрузка - если данные переданы в ограниченном объеме
		this.findById('EVPLSEF_PersonInformationFrame').load({
			Person_id: (arguments[0].Person_id ? arguments[0].Person_id : ''),
			Person_Birthday: (arguments[0].Person_Birthday ? arguments[0].Person_Birthday : ''),
			Person_Firname: (arguments[0].Person_Firname ? arguments[0].Person_Firname : ''),
			Person_Secname: (arguments[0].Person_Secname ? arguments[0].Person_Secname : ''),
			Person_Surname: (arguments[0].Person_Surname ? arguments[0].Person_Surname : ''),
			OmsSprTerr_Code: (arguments[0].OmsSprTerr_Code ? arguments[0].OmsSprTerr_Code : ''),
			Sex_Code: (arguments[0].Sex_Code ? arguments[0].Sex_Code : ''),
			callback: function() {
				var field = base_form.findField('EvnVizitPLStom_setDate');
				clearDateAfterPersonDeath('personpanelid', 'EVPLSEF_PersonInformationFrame', field);
				win.setMKB();
				if (isEkb && win.findById('EVPLSEF_PersonInformationFrame').getFieldValue('Person_IsAnonym') == 2) {
					base_form.findField('PayType_id').setFieldValue('PayType_SysNick','bud');
					base_form.findField('PayType_id').disable();
				}
			}
		});

		this.findById('EVPLSEF_EvnDiagPLStomPanel').isLoaded = false;

		if ( this.action == 'add' ) {
			this.findById('EVPLSEF_EvnDiagPLStomSopPanel').isLoaded = true;
			this.findById('EVPLSEF_EvnUslugaStomPanel').isLoaded = true;
		}
		else {
			this.findById('EVPLSEF_EvnDiagPLStomSopPanel').isLoaded = false;
			this.findById('EVPLSEF_EvnUslugaStomPanel').isLoaded = false;
		}

		this.findById('EVPLSEF_EvnDiagPLStomGrid').getStore().removeAll();
		this.findById('EVPLSEF_EvnDiagPLStomGrid').getTopToolbar().items.items[0].enable();
		this.findById('EVPLSEF_EvnDiagPLStomGrid').getTopToolbar().items.items[1].disable();
		this.findById('EVPLSEF_EvnDiagPLStomGrid').getTopToolbar().items.items[2].disable();
		this.findById('EVPLSEF_EvnDiagPLStomGrid').getTopToolbar().items.items[3].disable();

		this.findById('EVPLSEF_EvnDiagPLStomSopGrid').getStore().removeAll();
		this.findById('EVPLSEF_EvnDiagPLStomSopGrid').getTopToolbar().items.items[0].enable();
		this.findById('EVPLSEF_EvnDiagPLStomSopGrid').getTopToolbar().items.items[1].disable();
		this.findById('EVPLSEF_EvnDiagPLStomSopGrid').getTopToolbar().items.items[2].disable();
		this.findById('EVPLSEF_EvnDiagPLStomSopGrid').getTopToolbar().items.items[3].disable();

		this.findById('EVPLSEF_EvnUslugaStomGrid').getStore().removeAll();
		this.findById('EVPLSEF_EvnUslugaStomGrid').getTopToolbar().items.items[0].enable();
		this.findById('EVPLSEF_EvnUslugaStomGrid').getTopToolbar().items.items[1].disable();
		this.findById('EVPLSEF_EvnUslugaStomGrid').getTopToolbar().items.items[2].disable();
		this.findById('EVPLSEF_EvnUslugaStomGrid').getTopToolbar().items.items[3].disable();
		this.findById('EVPLSEF_EvnUslugaStomGrid').getTopToolbar().items.items[4].disable();

		base_form.setValues(arguments[0].formParams);
		base_form.findField('MedicalCareKind_id').clearValue(); // т.к. из ТАП приходит значение из другого справочника
		if (getRegionNick() == 'kz') {
			base_form.findField('PayType_id').clearValue();
		}
		
		this.formParams = arguments[0].formParams;

		this.ToothMapPanel.collapse();
		this.ToothMapPanel.doReset();
		this.ToothMapPanel.isLoaded = false;
		this.ToothMapPanel.setReadOnly(this.action == 'view');
		
		base_form.findField('EvnVizitPLStom_setTime').setRawValue(arguments[0].formParams.EvnVizitPLStom_setTime);
		base_form.findField('EvnVizitPLStom_RepFlag').hideContainer();

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT });
		loadMask.show();

		LoadEmptyRow(this.findById('EVPLSEF_EvnDiagPLStomGrid'));
		LoadEmptyRow(this.findById('EVPLSEF_EvnDiagPLStomSopGrid'));
		LoadEmptyRow(this.findById('EVPLSEF_EvnUslugaStomGrid'));

		base_form.findField('Diag_id').setBaseFilter(function(rec, id){
			return (rec.get('Diag_Code') && -1 == rec.get('Diag_Code').indexOf('F'));
		});

		base_form.findField('Diag_id').fireEvent('change', base_form.findField('Diag_id'), null);

		base_form.findField('DispClass_id').fireEvent('change', base_form.findField('DispClass_id'), null);
		base_form.findField('UslugaMedType_id').fireEvent('change', base_form.findField('UslugaMedType_id'), null);
		base_form.findField('EvnPLDisp_id').DispClass_id = 0;
		base_form.findField('EvnPLDisp_id').getStore().removeAll();
		base_form.findField('PersonDisp_id').getStore().removeAll();
		base_form.findField('TreatmentClass_id').onLoadStore();

		base_form.findField('MesEkb_id').setAllowBlank(getRegionNick() != 'ekb');

		var tooth_field = base_form.findField('Tooth_Code'),
			ToothSurface_group = base_form.findField('ToothSurfaceType_id_list');

		var addByMesBtn = win.findById('EVPLSEF_EvnUslugaStomGrid').getTopToolbar().items.items[4];

		base_form.findField('UslugaMedType_id').setContainerVisible(getRegionNick() === 'kz');
		
		base_form.findField('VizitActiveType_id').clearValue();
		base_form.findField('VizitActiveType_id').clearFilter();
		base_form.findField('VizitActiveType_id').hideContainer();
		
		switch ( this.action ) {
			case 'add':
				this.setTitle(this.baseTitle + ': ' + FRM_ACTION_ADD);
				this.enableEdit(true);

				this.setFormMode();

				this.loadDiagNewCombo();

				// Панели с основным диагнозом и списком услуг по умолчанию развернуты
				this.findById('EVPLSEF_DiagPanel').expand();
				this.findById('EVPLSEF_EvnDiagPLStomPanel').expand();
				this.findById('EVPLSEF_EvnUslugaStomPanel').expand();

				if ( this.formMode == 'morbus' ) {
					this.findById('EVPLSEF_EvnDiagPLStomPanel').fireEvent('expand', this.findById('EVPLSEF_EvnDiagPLStomPanel'));
				}
				else {
					this.findById('EVPLSEF_EvnDiagPLStomSopPanel').fireEvent('expand', this.findById('EVPLSEF_EvnDiagPLStomSopPanel'));
				}

				var set_date_flag = Ext.isEmpty(base_form.findField('EvnVizitPLStom_setDate').getValue());
				var time_field = base_form.findField('EvnVizitPLStom_setTime');

				if ( !Ext.isEmpty(this.userMedStaffFact.MedStaffFact_id) ) {
					// устанавливаем дату/время, если пришли из рабочего места врача
					time_field.setAllowBlank(false);
				} 
				else {
					time_field.setAllowBlank(true);
				}

				if ( isUfa || isEkb || isBur ) {
					base_form.findField('UslugaComplex_uid').setPersonId(base_form.findField('Person_id').getValue());
				}

				base_form.findField('PersonDisp_id').getStore().baseParams.Person_id = base_form.findField('Person_id').getValue();

				var index;
				var LpuSection_id = base_form.findField('LpuSection_id').getValue();
				var MedPersonal_id = base_form.findField('MedPersonal_id').getValue();
				var medstafffact_id = base_form.findField('MedStaffFact_id').getValue();
				var MedPersonal_sid = base_form.findField('MedPersonal_sid').getValue();

				tooth_field.setValue(null);
				tooth_field.fireEvent('change', tooth_field, null);
				ToothSurface_group.setValue('');

				if (getRegionNick() == 'ekb') {
					if (win.isRepeatVizit && !win.loadLastData) {
						win.hide();
						return false;
					}
					if (!win.loadLastData) {
						win.loadMesEkbCombo();
					}
				}

				setCurrentDateTime({
					callback: function() {
						win.setEvnVizitPLStomPeriod();

						if ( win.loadLastData === true ) {
							// Загружаем данные о последнем посещении с сервера
							Ext.Ajax.request({
								callback: function(options, success, response) {
									var response_obj = Ext.util.JSON.decode(response.responseText);

									if ( typeof response_obj == 'object' && response_obj.length > 0 ) {
										base_form.findField('DeseaseType_id').setValue(response_obj[0].DeseaseType_id);
										base_form.findField('BitePersonType_id').setValue(response_obj[0].BitePersonType_id);

										if (response_obj[0].Tooth_Code) {
											tooth_field.setValue(response_obj[0].Tooth_Code);
										} else {
											tooth_field.setToothId(response_obj[0].Tooth_id || null);
										}
										var tooth_code = tooth_field.getValue();
										if (!tooth_field.hasCode(tooth_code)) {
											tooth_code = null;
											tooth_field.setValue(tooth_code);
										}
										tooth_field.fireEvent('change', tooth_field, tooth_code);
										ToothSurface_group.setValue(response_obj[0].ToothSurfaceType_id_list);

										base_form.findField('PayType_id').setValue(response_obj[0].PayType_id);
										if (isEkb && win.findById('EVPLSEF_PersonInformationFrame').getFieldValue('Person_IsAnonym') == 2) {
											base_form.findField('PayType_id').setFieldValue('PayType_SysNick','bud');
											base_form.findField('PayType_id').disable();
										}
										base_form.findField('PayType_id').fireEvent('change',base_form.findField('PayType_id'),base_form.findField('PayType_id').getValue());
										base_form.findField('ServiceType_id').setValue(response_obj[0].ServiceType_id);
										base_form.findField('VizitType_id').setValue(response_obj[0].VizitType_id);
										base_form.findField('EvnPLStom_IsFinish').setValue(response_obj[0].EvnPLStom_IsFinish);
										
										/*if (isKareliya
											&& 2 == base_form.findField('EvnPLStom_IsFinish').getValue() 
											&& base_form.findField('VizitType_id').getValue()
										) {
											base_form.findField('VizitType_id').disable();
										}*/

										if (getRegionNick() == 'ekb') {
											if (!response_obj[0].Mes_id) {
												win.hide();
												return false;
											}
											base_form.findField('Mes_id').setValue(response_obj[0].Mes_id);
											var mes_id = base_form.findField('Mes_id').getValue();
											base_form.findField('UslugaComplex_uid').getStore().baseParams.Mes_id = mes_id;
											win.loadMesEkbCombo({callback: function(a,b,c) {
												base_form.findField('MesEkb_id').setValue(mes_id);
												base_form.findField('MesEkb_id').disable();
												addByMesBtn.setDisabled(false);
											}});
										}
										
										base_form.findField('Diag_id').setValue(response_obj[0].Diag_id);
										if ( Ext.isEmpty(this.userMedStaffFact.LpuSection_id) && Ext.isEmpty(this.userMedStaffFact.MedStaffFact_id) ) {
											base_form.findField('LpuSection_id').setValue(response_obj[0].LpuSection_id);
										}
										base_form.findField('EvnVizitPLStom_setDate').fireEvent('change', base_form.findField('EvnVizitPLStom_setDate'), base_form.findField('EvnVizitPLStom_setDate').getValue());

										if ( sw.Promed.EvnVizitPLStom.isSupportVizitCode() || (getRegionNick() == 'perm' && win.formMode == 'morbus') ) {
											var usluga_complex_id = response_obj[0].UslugaComplex_uid;

											if ( isUfa ) {
												// Дернуть код профиля и установить фильтр
												index = base_form.findField('LpuSection_id').getStore().findBy(function(rec) {
													if ( rec.get('LpuSection_id') == response_obj[0].LpuSection_id ) {
														return true;
													}
													else {
														return false;
													}
												});
												record = base_form.findField('LpuSection_id').getStore().getAt(index);

												if ( record ) {
													base_form.findField('UslugaComplex_uid').setLpuLevelCode(record.get('LpuSectionProfile_Code'));
												}
											}

											if ( usluga_complex_id ) {
												base_form.findField('UslugaComplex_uid').getStore().load({
													callback: function() {
														index = base_form.findField('UslugaComplex_uid').getStore().findBy(function(rec) {
															if ( rec.get('UslugaComplex_id') == usluga_complex_id ) {
																return true;
															}
															else {
																return false;
															}
														});

														if ( index >= 0 ) {
															base_form.findField('UslugaComplex_uid').setValue(usluga_complex_id);
														}
														else {
															base_form.findField('UslugaComplex_uid').clearValue();
														}
														base_form.findField('UslugaComplex_uid').fireEvent('change', base_form.findField('UslugaComplex_uid'), base_form.findField('UslugaComplex_uid').getValue());
													}.createDelegate(this),
													params: {
														// UslugaComplex_id: usluga_complex_id
													}
												});
											}
										}

										if ( base_form.findField('EvnVizitPLStom_setDate').getValue() && Ext.isEmpty(this.userMedStaffFact.LpuSection_id) && Ext.isEmpty(this.userMedStaffFact.MedStaffFact_id) ) {
											var lpu_section_id = response_obj[0].LpuSection_id;
											
											base_form.findField('LpuSection_id').setValue(lpu_section_id);
											base_form.findField('LpuSection_id').fireEvent('change', base_form.findField('LpuSection_id'), lpu_section_id);
											
											// врач и младший мед. персонал
											index = base_form.findField('MedStaffFact_id').getStore().findBy(function(record, id) {
												var is_found = false;
												if (response_obj[0].MedStaffFact_id) {
													if (record.get('MedStaffFact_id') == response_obj[0].MedStaffFact_id) {
														is_found = true;
													}
												} else if ( record.get('LpuSection_id') == lpu_section_id && record.get('MedPersonal_id') == response_obj[0].MedPersonal_id ) {
													is_found = true;
												}
												if ( is_found ) {
													base_form.findField('MedStaffFact_id').setValue(record.get('MedStaffFact_id'));
													base_form.findField('MedStaffFact_id').fireEvent('change', base_form.findField('MedStaffFact_id'), base_form.findField('MedStaffFact_id').getValue());
												}
												return is_found;
											});
											
											index = base_form.findField('MedStaffFact_sid').getStore().findBy(function(record, id) {
												if ( record.get('MedPersonal_id') == response_obj[0].MedPersonal_sid ) {
													base_form.findField('MedStaffFact_sid').setValue(record.get('MedStaffFact_id'));
													return true;
												}
												else {
													return false;
												}
											});
										}

										base_form.findField('VizitType_id').fireEvent('change', base_form.findField('VizitType_id'), base_form.findField('VizitType_id').getValue());

										if ( !Ext.isEmpty(response_obj[0].Diag_id) ) {
											base_form.findField('Diag_id').getStore().load({
												callback: function() {
													base_form.findField('Diag_id').getStore().each(function(record) {
														if ( record.get('Diag_id') == response_obj[0].Diag_id ) {
															base_form.findField('Diag_id').setValue(response_obj[0].Diag_id);
															base_form.findField('Diag_id').fireEvent('select', base_form.findField('Diag_id'), record, 0);
															base_form.findField('Diag_id').fireEvent('change', base_form.findField('Diag_id'), response_obj[0].Diag_id);
															base_form.findField('TreatmentClass_id').onLoadStore();
														}
													});
												},
												params: { where: "where DiagLevel_id = 4 and Diag_id = " + response_obj[0].Diag_id }
											});
										}
									} else {
										base_form.findField('EvnVizitPLStom_setDate').fireEvent('change', base_form.findField('EvnVizitPLStom_setDate'), base_form.findField('EvnVizitPLStom_setDate').getValue());
									}

									loadMask.hide();

									base_form.findField('EvnVizitPLStom_setDate').focus(true, 250);
								}.createDelegate(this),
								params: {
									EvnVizitPLStom_pid: base_form.findField('EvnPLStom_id').getValue()
								},
								url: '/?c=EvnVizit&m=loadLastEvnVizitPLStomData'
							});
							this.EvnXmlPanel.loadLastEvnProtocolData(base_form.findField('EvnPLStom_id').getValue());
							this.EvnXmlPanel.expand();
						}
						else {
							base_form.findField('EvnVizitPLStom_setDate').fireEvent('change', base_form.findField('EvnVizitPLStom_setDate'), base_form.findField('EvnVizitPLStom_setDate').getValue());
							// Night, если посещение создается из места работы врача (то есть под врачом), то заполняем по умолчанию 
							// Место - скорее всего поликлиника
							// Цель посещения - скорее всего лечебно-диагностическая 
							// Вид оплаты - скорее всего ОМС 
							if ( !Ext.isEmpty(this.userMedStaffFact.MedStaffFact_id) ) {
								if(getRegionNick() != 'kz') {
									base_form.findField('PayType_id').setFieldValue('PayType_SysNick', getPayTypeSysNickOms());
									base_form.findField('PayType_id').fireEvent('change',base_form.findField('PayType_id'),base_form.findField('PayType_id').getValue());
								}
								base_form.findField('ServiceType_id').setFieldValue('ServiceType_SysNick', 'polka');
								base_form.findField('VizitType_id').setFieldValue('VizitType_SysNick', 'desease');
							}

							if (isEkb && win.findById('EVPLSEF_PersonInformationFrame').getFieldValue('Person_IsAnonym') == 2) {
								base_form.findField('PayType_id').setFieldValue('PayType_SysNick','bud');
								base_form.findField('PayType_id').disable();
								base_form.findField('PayType_id').fireEvent('change',base_form.findField('PayType_id'),base_form.findField('PayType_id').getValue());
							}

							if ( isKareliya == true ) {
								if ( Ext.isEmpty(base_form.findField('PayType_id').getValue()) ) {
									base_form.findField('PayType_id').setFieldValue('PayType_SysNick', 'oms');
									base_form.findField('PayType_id').fireEvent('change',base_form.findField('PayType_id'),base_form.findField('PayType_id').getValue());
								}

								if ( Ext.isEmpty(base_form.findField('ServiceType_id').getValue()) ) {
									base_form.findField('ServiceType_id').setFieldValue('ServiceType_SysNick', 'polka');
								}

								if ( Ext.isEmpty(base_form.findField('VizitType_id').getValue()) ) {
									base_form.findField('VizitType_id').setFieldValue('VizitType_SysNick', 'desease');
									base_form.findField('VizitType_id').fireEvent('change', base_form.findField('VizitType_id'), base_form.findField('VizitType_id').getValue());
								}
							}

							// Параметры поточного ввода
							if ( !Ext.isEmpty(LpuSection_id) ) {
								index = base_form.findField('LpuSection_id').getStore().findBy(function(rec) {
									return (rec.get('LpuSection_id') == LpuSection_id);
								});

								if ( index >= 0 ) {
									base_form.findField('LpuSection_id').setValue(LpuSection_id);
									var record = base_form.findField('LpuSection_id').getStore().getAt(index);

									if ( record ) {
										base_form.findField('UslugaComplex_uid').setLpuSectionProfile_id(record.get('LpuSectionProfile_id'));
									}
								}
							}

							if ( !Ext.isEmpty(medstafffact_id) ) {
								index = base_form.findField('MedStaffFact_id').getStore().findBy(function(rec) {
									return (rec.get('MedStaffFact_id') == medstafffact_id);
								});
								if ( index >= 0 ) {
									base_form.findField('MedStaffFact_id').setValue(base_form.findField('MedStaffFact_id').getStore().getAt(index).get('MedStaffFact_id'));
									base_form.findField('MedStaffFact_id').fireEvent('change', base_form.findField('MedStaffFact_id'), base_form.findField('MedStaffFact_id').getValue());
								}
							} else if ( !Ext.isEmpty(MedPersonal_id) ) {
								index = base_form.findField('MedStaffFact_id').getStore().findBy(function(rec) {
									return (rec.get('LpuSection_id') == LpuSection_id && rec.get('MedPersonal_id') == MedPersonal_id);
								});

								if ( index >= 0 ) {
									base_form.findField('MedStaffFact_id').setValue(base_form.findField('MedStaffFact_id').getStore().getAt(index).get('MedStaffFact_id'));
									base_form.findField('MedStaffFact_id').fireEvent('change', base_form.findField('MedStaffFact_id'), base_form.findField('MedStaffFact_id').getValue());
								}
							}

							if ( !Ext.isEmpty(MedPersonal_sid) ) {
								index = base_form.findField('MedStaffFact_sid').getStore().findBy(function(rec) {
									return (rec.get('MedPersonal_id') == MedPersonal_sid);
								});

								if ( index >= 0 ) {
									base_form.findField('MedStaffFact_sid').setValue(base_form.findField('MedStaffFact_sid').getStore().getAt(index).get('MedStaffFact_id'));
								}
							}

							if(formParams.Person_id)
								win.setBitePersonCombo(formParams.Person_id);

							loadMask.hide();

							base_form.findField('EvnVizitPLStom_setDate').focus(true, 250);
							if(win.streamInput && isUfa){
								base_form.findField('UslugaComplex_uid').getStore().load({
									callback: function() {
										index = base_form.findField('UslugaComplex_uid').getStore().findBy(function(rec) {
											if ( rec.get('UslugaComplex_id') == win.formParams.UslugaComplex_uid ) {
												return true;
											}
											else {
												return false;
											}
										});

										if ( index >= 0 ) {
											base_form.findField('UslugaComplex_uid').setValue(win.formParams.UslugaComplex_uid);
										}
										else {
											base_form.findField('UslugaComplex_uid').clearValue();
										}
										base_form.findField('UslugaComplex_uid').fireEvent('change', base_form.findField('UslugaComplex_uid'), base_form.findField('UslugaComplex_uid').getValue());
									}.createDelegate(this),
									params: {
										//UslugaComplex_id: usluga_complex_id
									}
								});
							}
						}
					}.createDelegate(this),
					dateField: base_form.findField('EvnVizitPLStom_setDate'),
					loadMask: false,
					setDate: set_date_flag,
					setDateMaxValue: true,
					setDateMinValue: false,
					setTime: set_date_flag,
					timeField: base_form.findField('EvnVizitPLStom_setTime'),
					windowId: this.id
				});

				if (getRegionNick() === 'kz') {
					base_form.findField('UslugaMedType_id').setFieldValue('UslugaMedType_Code', '1400');
					base_form.findField('PayType_id').disable();
				}
			break;

			case 'edit':
			case 'view':
				// Делаем загрузку данных с сервера
				this.loadingInProgress = true;
				base_form.load({
					failure: function() {
						loadMask.hide();
						sw.swMsg.alert('Ошибка', 'Ошибка при загрузке данных формы', function() { this.hide(); }.createDelegate(this) );
					}.createDelegate(this),
					params: {
						EvnVizitPLStom_id: base_form.findField('EvnVizitPLStom_id').getValue(),
						archiveRecord: win.archiveRecord,
						fromMZ: getWnd('swWorkPlaceMZSpecWindow').isVisible()?'2':'1'
					},
					success: function(form, result) {
						if (!Ext.isEmpty(base_form.findField('AlertReg_Msg').getValue())) {
							sw.swMsg.alert('Внимание', base_form.findField('AlertReg_Msg').getValue());
						}

						var response_obj = Ext.util.JSON.decode(result.response.responseText);

						// В зависимости от accessType переопределяем this.action
						if ( base_form.findField('accessType').getValue() == 'view' ) {
							this.action = 'view';
						}
						this.isRepeatVizit = (1 == base_form.findField('is_repeat_vizit').getValue());

						this.setFormMode();

						this.loadDiagNewCombo();

						if ( this.action == 'view' ) {
							this.setTitle(this.baseTitle + ': ' + FRM_ACTION_VIEW);
							this.enableEdit(false);
						}
						else {
							this.setTitle(this.baseTitle + ': ' + FRM_ACTION_EDIT);
							this.enableEdit(true);
						}

						if(response_obj[0].BitePersonType_id){
							base_form.findField('BitePersonType_id').setValue(response_obj[0].BitePersonType_id);
						}else{
							if(formParams.Person_id)
								win.setBitePersonCombo(formParams.Person_id);
						}

						if (!Ext.isEmpty(base_form.findField('LpuDispContract_id').getValue())) {
							// надо загрузить
							base_form.findField('LpuDispContract_id').getStore().load({
								params: {
									LpuDispContract_id: base_form.findField('LpuDispContract_id').getValue()
								},
								callback: function() {
									base_form.findField('LpuDispContract_id').setValue(base_form.findField('LpuDispContract_id').getValue());
								}
							});
						}

						if (isEkb && win.findById('EVPLSEF_PersonInformationFrame').getFieldValue('Person_IsAnonym') == 2) {
							base_form.findField('PayType_id').setFieldValue('PayType_SysNick','bud');
							base_form.findField('PayType_id').disable();
						}

						var xdate = sw.Promed.EvnPL.getEvnPLStomNewBegDate();
						var EvnPLStom_setDate = getValidDT(base_form.findField('EvnPLStom_setDate').getValue(), '');
						if ( getRegionNick() == 'perm' && base_form.findField('EvnVizitPLStom_IsPaid').getValue() == 2 && parseInt(base_form.findField('EvnVizitPLStom_IndexRepInReg').getValue()) > 0 && !Ext.isEmpty(EvnPLStom_setDate) && EvnPLStom_setDate < xdate ) {
							base_form.findField('EvnVizitPLStom_RepFlag').showContainer();

							if ( parseInt(base_form.findField('EvnVizitPLStom_IndexRep').getValue()) >= parseInt(base_form.findField('EvnVizitPLStom_IndexRepInReg').getValue()) ) {
								base_form.findField('EvnVizitPLStom_RepFlag').setValue(true);
							}
							else {
								base_form.findField('EvnVizitPLStom_RepFlag').setValue(false);
							}
						}
						
						base_form.findField('UslugaComplex_uid').getStore().baseParams.EvnVizit_id = base_form.findField('EvnVizitPLStom_id').getValue();

						var curDate = (typeof getValidDT(getGlobalOptions().date, '') == 'object' ? getValidDT(getGlobalOptions().date, '') : new Date());

						if ( typeof curDate == 'object' ) {
							base_form.findField('EvnVizitPLStom_setDate').setMaxValue(curDate.format('d.m.Y'));
						}

						win.setEvnVizitPLStomPeriod();

						this.ToothMapPanel.applyParams(
							base_form.findField('Person_id').getValue(),
							base_form.findField('EvnVizitPLStom_id').getValue(),
							base_form.findField('EvnVizitPLStom_setDate').getRawValue()
						);

						if ( isUfa || isEkb || isBur ) {
							base_form.findField('UslugaComplex_uid').setPersonId(base_form.findField('Person_id').getValue());
						}

						base_form.findField('PersonDisp_id').getStore().baseParams.Person_id = base_form.findField('Person_id').getValue();

						if ( this.formMode == 'morbus' ) {
							this.findById('EVPLSEF_EvnDiagPLStomPanel').fireEvent('expand', this.findById('EVPLSEF_EvnDiagPLStomPanel'));
						}
						else {
							this.findById('EVPLSEF_EvnDiagPLStomSopPanel').fireEvent('expand', this.findById('EVPLSEF_EvnDiagPLStomSopPanel'));
						}

						this.findById('EVPLSEF_EvnUslugaStomPanel').fireEvent('expand', this.findById('EVPLSEF_EvnUslugaStomPanel'));

						var diag_id = base_form.findField('Diag_id').getValue();
						var evn_vizit_pl_stom_set_date = base_form.findField('EvnVizitPLStom_setDate').getValue();
						var index;
						var lpu_section_id = base_form.findField('LpuSection_id').getValue();
						var med_personal_id = base_form.findField('MedPersonal_id').getValue();
						var medstafffact_id = base_form.findField('MedStaffFact_id').getValue();
						var med_personal_sid = base_form.findField('MedPersonal_sid').getValue();
						var record;
						var service_type_id = base_form.findField('ServiceType_id').getValue();
						var DispClass_id = base_form.findField('DispClass_id').getValue();
						var UslugaMedType_id = base_form.findField('UslugaMedType_id').getValue();

						if ( !Ext.isEmpty(DispClass_id) ) {
							base_form.findField('DispClass_id').fireEvent('change', base_form.findField('DispClass_id'), DispClass_id);
						}
						if ( !Ext.isEmpty(UslugaMedType_id) ) {
							base_form.findField('UslugaMedType_id').fireEvent('change', base_form.findField('UslugaMedType_id'), UslugaMedType_id);
						}

						if ( sw.Promed.EvnVizitPLStom.isSupportVizitCode() || (getRegionNick() == 'perm' && win.formMode == 'morbus') ) {
							var usluga_complex_id = base_form.findField('UslugaComplex_uid').getValue();
						}
						if ( isEkb == true ) {
							var mes_id = base_form.findField('Mes_id').getValue();
						}

						// Фильтр на поле ServiceType_id
						// https://redmine.swan.perm.ru/issues/17571
						base_form.findField('ServiceType_id').clearValue();
						base_form.findField('ServiceType_id').getStore().clearFilter();
						base_form.findField('ServiceType_id').lastQuery = '';

						if ( !Ext.isEmpty(evn_vizit_pl_stom_set_date) ) {
							base_form.findField('ServiceType_id').getStore().filterBy(function(rec) {	
								return (
									(Ext.isEmpty(rec.get('ServiceType_begDate')) || rec.get('ServiceType_begDate') <= evn_vizit_pl_stom_set_date)
									&& (Ext.isEmpty(rec.get('ServiceType_endDate')) || rec.get('ServiceType_endDate') >= evn_vizit_pl_stom_set_date)
								);
							});
						}

						index = base_form.findField('ServiceType_id').getStore().findBy(function(rec, id) {
							return (rec.get('ServiceType_id') == service_type_id);
						}.createDelegate(this));

						if ( index >= 0 ) {
							base_form.findField('ServiceType_id').setValue(service_type_id);
						}

						base_form.findField('LpuSection_id').clearValue();
						base_form.findField('MedStaffFact_id').clearValue();
						base_form.findField('MedStaffFact_sid').clearValue();

						var lpuSectionFilter = {
							allowLowLevel: 'yes',
							isStom: true,
							onDate: Ext.util.Format.date(evn_vizit_pl_stom_set_date, 'd.m.Y')
						};

						var medStaffFactFilter = {
							allowLowLevel: 'yes',
							EvnClass_SysNick: 'EvnVizit',
							//isDoctor: true,
							isStom: true,
							onDate: Ext.util.Format.date(evn_vizit_pl_stom_set_date, 'd.m.Y')
						};

						if ( !Ext.isEmpty(this.userMedStaffFact.LpuSection_id) && !Ext.isEmpty(this.userMedStaffFact.MedStaffFact_id) ) {
							if ( this.action == 'edit' ) {
								base_form.findField('LpuSection_id').disable();
								if (this.userMedStaffFact.MedStaffFact_id == base_form.findField('MedStaffFact_id').getValue()) {
									base_form.findField('MedStaffFact_id').disable();
								}
							}
						}

						setLpuSectionGlobalStoreFilter(lpuSectionFilter);
						setMedStaffFactGlobalStoreFilter(medStaffFactFilter);

						base_form.findField('LpuSection_id').getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));
						base_form.findField('MedStaffFact_id').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));

						setMedStaffFactGlobalStoreFilter({
							allowLowLevel: 'yes',
							isMidMedPersonal: true, // Средний мед. персонал + зубные врачи
							isStom: true,
							onDate: Ext.util.Format.date(evn_vizit_pl_stom_set_date, 'd.m.Y')
						});

						base_form.findField('MedStaffFact_sid').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));

						index = base_form.findField('LpuSection_id').getStore().findBy(function (rec, id) {
							return (rec.get('LpuSection_id') == lpu_section_id);
						});

						if ( index >= 0 ) {
							base_form.findField('LpuSection_id').setValue(lpu_section_id);
						}
						else {
							Ext.Ajax.request({
								failure: function(response, options) {
									//
								},
								params: {
									LpuSection_id: lpu_section_id
								},
								success: function(response, options) {
									base_form.findField('LpuSection_id').getStore().loadData(Ext.util.JSON.decode(response.responseText), true);

									index = base_form.findField('LpuSection_id').getStore().findBy(function (rec, id) {
										return (rec.get('LpuSection_id') == lpu_section_id);
									});

									if ( index >= 0 ) {
										base_form.findField('LpuSection_id').setValue(lpu_section_id);
									}
								}.createDelegate(this),
								url: C_LPUSECTION_LIST
							});
						}

						index = base_form.findField('MedStaffFact_id').getStore().findBy(function(rec, id) {
							if (medstafffact_id) {
								return (rec.get('MedStaffFact_id') == medstafffact_id);
							} else {
								return (rec.get('MedPersonal_id') == med_personal_id && rec.get('LpuSection_id') == lpu_section_id);
							}
						});

						if ( index >= 0 ) {
							base_form.findField('MedStaffFact_id').setValue(base_form.findField('MedStaffFact_id').getStore().getAt(index).get('MedStaffFact_id'));
							base_form.findField('MedStaffFact_id').fireEvent('change', base_form.findField('MedStaffFact_id'), base_form.findField('MedStaffFact_id').getValue());
						}
						else {
							Ext.Ajax.request({
								failure: function(response, options) {
									loadMask.hide();
								},
								params: {
									LpuSection_id: lpu_section_id,
									MedPersonal_id: med_personal_id,
									ignoreDisableInDocParam: 1
								},
								success: function(response, options) {
									base_form.findField('MedStaffFact_id').ignoreDisableInDoc = true;
									base_form.findField('MedStaffFact_id').getStore().loadData(Ext.util.JSON.decode(response.responseText), true);
									base_form.findField('MedStaffFact_id').ignoreDisableInDoc = false;

									index = base_form.findField('MedStaffFact_id').getStore().findBy(function(rec) {
										if (medstafffact_id) {
											return (rec.get('MedStaffFact_id') == medstafffact_id);
										} else {
											return (rec.get('MedPersonal_id') == med_personal_id && rec.get('LpuSection_id') == lpu_section_id);
										}
									});

									if ( index >= 0 ) {
										base_form.findField('MedStaffFact_id').setValue(base_form.findField('MedStaffFact_id').getStore().getAt(index).get('MedStaffFact_id'));
										base_form.findField('MedStaffFact_id').fireEvent('change', base_form.findField('MedStaffFact_id'), base_form.findField('MedStaffFact_id').getValue());
									}
								}.createDelegate(this),
								url: C_MEDPERSONAL_LIST
							});
						}

						if ( !Ext.isEmpty(med_personal_sid) ) {
							index = base_form.findField('MedStaffFact_sid').getStore().findBy(function(rec, id) {
								return (rec.get('MedPersonal_id') == med_personal_sid);
							})

							if ( index >= 0 ) {
								base_form.findField('MedStaffFact_sid').setValue(base_form.findField('MedStaffFact_sid').getStore().getAt(index).get('MedStaffFact_id'));
							}
							else {
								Ext.Ajax.request({
									failure: function(response, options) {
										loadMask.hide();
									},
									params: {
										MedPersonal_id: med_personal_sid
									},
									success: function(response, options) {
										base_form.findField('MedStaffFact_sid').getStore().loadData(Ext.util.JSON.decode(response.responseText), true);

										index = base_form.findField('MedStaffFact_sid').getStore().findBy(function(rec) {
											return (rec.get('MedPersonal_id') == med_personal_sid);
										});

										if ( index >= 0 ) {
											base_form.findField('MedStaffFact_sid').setValue(base_form.findField('MedStaffFact_sid').getStore().getAt(index).get('MedStaffFact_id'));
										}
									}.createDelegate(this),
									url: C_MEDPERSONAL_LIST
								});
							}
						}

						if ( this.action == 'edit' ) {
							setCurrentDateTime({
								dateField: base_form.findField('EvnVizitPLStom_setDate'),
								loadMask: false,
								setDate: false,
								setDateMaxValue: true,
								setDateMinValue: false,
								setTime: false,
								timeField: base_form.findField('EvnVizitPLStom_setTime'),
								windowId: this.id
							});

							base_form.findField('EvnVizitPLStom_setDate').fireEvent('change', base_form.findField('EvnVizitPLStom_setDate'), base_form.findField('EvnVizitPLStom_setDate').getValue());
							base_form.findField('VizitType_id').fireEvent('change', base_form.findField('VizitType_id'), base_form.findField('VizitType_id').getValue());
						}
						else {
							base_form.findField('EvnVizitPLStom_setDate').fireEvent('change', base_form.findField('EvnVizitPLStom_setDate'), base_form.findField('EvnVizitPLStom_setDate').getValue());
							base_form.findField('VizitType_id').fireEvent('change', base_form.findField('VizitType_id'), base_form.findField('VizitType_id').getValue());

							this.findById('EVPLSEF_EvnDiagPLStomGrid').getTopToolbar().items.items[0].disable();
							this.findById('EVPLSEF_EvnDiagPLStomSopGrid').getTopToolbar().items.items[0].disable();
							this.findById('EVPLSEF_EvnUslugaStomGrid').getTopToolbar().items.items[0].disable();
						}
						
						if ( sw.Promed.EvnVizitPLStom.isSupportVizitCode() || (getRegionNick() == 'perm' && win.formMode == 'morbus') ) {
							if ( isUfa == true ) {
								// Дернуть код профиля и установить фильтр
								index = base_form.findField('LpuSection_id').getStore().findBy(function(rec) {
									if ( rec.get('LpuSection_id') == lpu_section_id ) {
										return true;
									}
									else {
										return false;
									}
								});
								record = base_form.findField('LpuSection_id').getStore().getAt(index);

								if ( record ) {
									base_form.findField('UslugaComplex_uid').setLpuLevelCode(record.get('LpuSectionProfile_Code'));
								}
							}

							if ( usluga_complex_id ) {
								if ( isEkb == true) {
									base_form.findField('UslugaComplex_uid').getStore().baseParams.Mes_id = mes_id;
								}
								base_form.findField('UslugaComplex_uid').getStore().load({
									callback: function() {
										index = base_form.findField('UslugaComplex_uid').getStore().findBy(function(rec) {
											if ( rec.get('UslugaComplex_id') == usluga_complex_id ) {
												return true;
											}
											else {
												return false;
											}
										});

										if ( index >= 0 ) {
											base_form.findField('UslugaComplex_uid').setValue(usluga_complex_id);
										}
										else {
											base_form.findField('UslugaComplex_uid').clearValue();
										}
										base_form.findField('UslugaComplex_uid').fireEvent('change', base_form.findField('UslugaComplex_uid'), base_form.findField('UslugaComplex_uid').getValue());

										if ( isEkb == true ) {
											win.loadMesEkbCombo({callback: function(a,b,c) {
												base_form.findField('MesEkb_id').setValue(mes_id);
												if (win.action == 'edit') {
													base_form.findField('MesEkb_id').setDisabled(win.isRepeatVizit);
												}
												addByMesBtn.setDisabled(win.action == 'view' || !mes_id);
											}});
										}
									}.createDelegate(this),
									params: {
										UslugaComplex_id: usluga_complex_id
									}
								});
							} else if ( isEkb == true ) {
								win.loadMesEkbCombo({callback: function(a,b,c) {
									base_form.findField('MesEkb_id').setValue(mes_id);
									if (win.action == 'edit') {
										base_form.findField('MesEkb_id').setDisabled(win.isRepeatVizit);
									}
									addByMesBtn.setDisabled(win.action == 'view' || !mes_id);
								}});
							}
						}

						base_form.findField('Diag_id').setValue(null);
						if ( diag_id ) {
							base_form.findField('Diag_id').getStore().load({
								callback: function() {
									base_form.findField('Diag_id').getStore().each(function(record) {
										if ( record.get('Diag_id') == diag_id ) {
											base_form.findField('Diag_id').setValue(diag_id);
											base_form.findField('Diag_id').fireEvent('select', base_form.findField('Diag_id'), record, 0);
											base_form.findField('Diag_id').fireEvent('change', base_form.findField('Diag_id'), diag_id);
											base_form.findField('TreatmentClass_id').onLoadStore();
										}
									}.createDelegate(this));
								}.createDelegate(this),
								params: { where: "where DiagLevel_id = 4 and Diag_id = " + diag_id }
							});
						}

						this.evnDirectionAllInfoPanel.onLoadForm(this);

						var tooth_code = tooth_field.getValue(),
							ToothSurfaceType_id_list = ToothSurface_group.getValue();
						if (!tooth_field.hasCode(tooth_code)) {
							tooth_code = null;
							tooth_field.setValue(tooth_code);
						}
						tooth_field.fireEvent('change', tooth_field, tooth_code);
						ToothSurface_group.setValue(ToothSurfaceType_id_list);

						loadMask.hide();

						this.EvnXmlPanel.setBaseParams({
							userMedStaffFact: this.userMedStaffFact,
							Server_id: base_form.findField('Server_id').getValue(),
							Evn_id: base_form.findField('EvnVizitPLStom_id').getValue()
						});
						this.EvnXmlPanel.doLoadData();
						this.EvnXmlPanel.expand();

						if ( this.action == 'edit' ) {
							base_form.findField('EvnVizitPLStom_setDate').focus(true, 250);
						}
						else {
							this.buttons[this.buttons.length - 1].focus();
						}
						
						var ued_value = base_form.findField('EvnUslugaStom_UED').getValue();
						if ( ued_value ) {
							setTimeout(function () {
								base_form.findField('EvnUslugaStom_UED').setValue(ued_value);
							}, 1500);
						}
						/*setTimeout(function () {
							base_form.findField('EvnUslugaStom_UED').setValue(ued_value);
						}, 1500);*/
						
						base_form.findField('PayType_id').fireEvent('change',base_form.findField('PayType_id'),base_form.findField('PayType_id').getValue());
						this.findById('EVPLSEF_EvnDiagPLStomPanel').expand();

						this.loadingInProgress = false;

						if (getRegionNick() == 'perm') {
							this.reloadUslugaComplexField();
						}

						if (getRegionNick() == 'kz') {
							base_form.findField('PayType_id').disable();
							base_form.findField('TreatmentClass_id').fireEvent('change', base_form.findField('TreatmentClass_id'), base_form.findField('TreatmentClass_id').getValue());
						}
					}.createDelegate(this),
					url: '/?c=EvnVizit&m=loadEvnVizitPLStomEditForm'
				});
			break;

			default:
				loadMask.hide();
				this.hide();
			break;
		}
	},
	uetValuesRecount: function() {
		var base_form = this.findById('EvnVizitPLStomEditForm').getForm();
		var grid = this.findById('EVPLSEF_EvnUslugaStomGrid');

		var
			evn_usluga_stom_uet = 0,
			evn_usluga_stom_uet_oms = 0,
			PayType_id;

		var index = base_form.findField('PayType_id').getStore().findBy(function(rec) {
			return (rec.get('PayType_SysNick') == getPayTypeSysNickOms());
		});

		if ( index >= 0 ) {
			PayType_id = base_form.findField('PayType_id').getStore().getAt(index).get('PayType_id');
		}

		grid.getStore().each(function(record) {
			if ( getRegionNick() != 'kareliya' || record.get('EvnUsluga_pid') == base_form.findField('EvnVizitPLStom_id').getValue() ) {
				if ( record.get('PayType_id') == PayType_id ) {
					evn_usluga_stom_uet_oms = evn_usluga_stom_uet_oms + Number(record.get('EvnUsluga_Summa'));
				}

				evn_usluga_stom_uet = evn_usluga_stom_uet + Number(record.get('EvnUsluga_Summa'));
			}
		});

		base_form.findField('EvnVizitPLStom_Uet').setValue(evn_usluga_stom_uet.toFixed(2));
		base_form.findField('EvnVizitPLStom_UetOMS').setValue(evn_usluga_stom_uet_oms.toFixed(2));
	},
	printControlCardZno: function() {
		var grid = this.findById('EVPLSEF_EvnDiagPLStomGrid');
			rec = grid.getSelectionModel().getSelected();

		if (rec.get('EvnDiagPLStom_id')) {
			printControlCardZno(rec.get('EvnDiagPLStom_id'));
		}
	},
	printControlCardOnko: function() {
		var grid = this.findById('EVPLSEF_EvnDiagPLStomGrid');
		rec = grid.getSelectionModel().getSelected();

		if (rec.get('EvnDiagPLStom_id')) {
			printControlCardOnko(rec.get('EvnDiagPLStom_id'));
		}
	},
	loadEvnVizitPLStomNodeList: function(data){
		if(!data.Person_id || !data.EvnPLStom_id || !data.EvnVizitPLStom_id) return false;
		
		var EvnVizitPLStom_id = data.EvnVizitPLStom_id;
		var params = {
			Object: 'EvnVizitPLStom',
			Object_id: data.EvnPLStom_id,
			Person_id: data.Person_id
		}
		Ext.Ajax.request({
			failure: function(response, options) {
				//debugger;
			},
			params: params,
			success: function(response, options) {
				var result = Ext.util.JSON.decode(response.responseText);
				if(result && Ext.isArray(result) && result.length>0){
					var OtherVizitList = [];
					result.forEach(function(vizit) {
						if (vizit.EvnVizitPLStom_id != EvnVizitPLStom_id) {
							OtherVizitList.push(vizit);
						}
					});
					this.OtherVizitList = OtherVizitList;
				}
			}.createDelegate(this),
			url: '/?c=EMK&m=getEvnVizitPLStomNodeList'
		});
	},
	errorControlCodaVisits: function(){		
		var flagProfile = false;
		if ( getRegionNick() == 'vologda' && this.OtherVizitList && Ext.isArray(this.OtherVizitList) && this.OtherVizitList.length>0 && this.OtherVizitList[0].EvnVizitPLStom_id) {			
			var base_form = this.findById('EvnVizitPLStomEditForm').getForm();
			var controlDate = new Date(2019, 7, 1);
			var evnVizitPLStom_setDate = base_form.findField('EvnVizitPLStom_setDate').getValue();
			if(evnVizitPLStom_setDate >= controlDate && this.OtherVizitList.length>0){
				var comboLpuSectionProfile = base_form.findField('LpuSectionProfile_id');
				var flagProfile = false;
				var arrNotControlProfileCode = [];
				var arrControlProfileCode = [];
				var arrVizitsProfileCode = [];
				for(var i=0; i<this.OtherVizitList.length; i++) {
					var vizit = this.OtherVizitList[i];
					
					if(arrVizitsProfileCode.indexOf(vizit.LpuSectionProfile_Code)<0) 
						arrVizitsProfileCode.push(vizit.LpuSectionProfile_Code);
					
					if(!vizit.LpuSectionProfile_Code.inlist(getGlobalOptions().exceptionprofiles)) {
						flagProfile = true;
						if(arrNotControlProfileCode.indexOf(vizit.LpuSectionProfile_Code)<0) 
							arrNotControlProfileCode.push(vizit.LpuSectionProfile_Code);
					}else{
						arrControlProfileCode.push(vizit.LpuSectionProfile_Code);
					}
				}
				
				if(arrVizitsProfileCode.indexOf(comboLpuSectionProfile.getFieldValue('LpuSectionProfile_Code'))<0) 
					arrVizitsProfileCode.push(comboLpuSectionProfile.getFieldValue('LpuSectionProfile_Code'));
				
				if(!comboLpuSectionProfile.getFieldValue('LpuSectionProfile_Code').inlist(getGlobalOptions().exceptionprofiles) ){
					flagProfile = true;
					if(arrNotControlProfileCode.indexOf(comboLpuSectionProfile.getFieldValue('LpuSectionProfile_Code'))<0) 
						arrNotControlProfileCode.push(comboLpuSectionProfile.getFieldValue('LpuSectionProfile_Code'));
				}else{
					arrControlProfileCode.push(comboLpuSectionProfile.getFieldValue('LpuSectionProfile_Code'));
				}
				
				if(arrVizitsProfileCode.length == 1) 
					flagProfile = false;
				
				if(flagProfile && arrControlProfileCode.length > 0 && arrNotControlProfileCode.length == 1){
					// есть одно или более посещений, в которых указаны профили «97», «57», «58», «42», «68», «3», «136», «85», «89»
					// И в остальных посещениях указан одинаковый профиль отделения, отличный от профилей «97», «57», «58», «42», «68», «3», «136», «85», «89»
					flagProfile = false;
				}
			}
		}
		return flagProfile;
	},
	getFinanceSource: function() {
		var win = this,
			base_form = this.findById('EvnVizitPLStomEditForm').getForm();
		
		//if (this.IsLoading) return false;
		//if (this.IsProfLoading) return false;

		if (getRegionNick() != 'kz') return false;

		if (this.action.inlist(['view'])) return false;
		
		if (base_form.findField('isPaidVisit').getValue()) return false;
		var params = {
			EvnDirection_setDate: Ext.util.Format.date(base_form.findField('EvnVizitPLStom_setDate').getValue(), 'd.m.Y'),
			Person_id: base_form.findField('Person_id').getValue(),
			TreatmentClass_id: base_form.findField('TreatmentClass_id').getValue(),
			LpuSectionProfile_id: base_form.findField('LpuSectionProfile_id').getValue(),
			UslugaComplex_id: base_form.findField('UslugaComplex_uid').getValue(),
			Diag_id: base_form.findField('Diag_id').getValue()
		};

		params.Diag_id = ( params.TreatmentClass_id.inlist(['24','37']) )?'5944':params.Diag_id;
		
		if (!params.LpuSectionProfile_id || !params.Diag_id || !params.TreatmentClass_id || !params.UslugaComplex_id) return false;
		//var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Получение источника финансирования..." });
		var loadMask = new Ext.LoadMask(Ext.getBody(), { msg: "Получение источника финансирования..." });
		loadMask.show();
		
		//Периодически при запуске данного метода (сразу после открытия формы) бывает активна некая маска без текста. Которая делает невидимой маску применененную к э-лту а не к телу
		//И позволяет нажимать на кнопки, что приводит к плохим результатам. Это костыль. На время работы править нельзя, маска привязана к телу.
		this.enableEdit(false);

		Ext.Ajax.request({
			callback: function (options, success, response) {
				loadMask.hide();
				this.enableEdit(true);
				base_form.findField('PayType_id').disable();

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
	width: 700
});