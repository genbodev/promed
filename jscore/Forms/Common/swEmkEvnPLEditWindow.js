/**
* swEmkEvnPLEditWindow - окно редактирования/добавления талона амбулаторного пациента.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/projects/promed
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2011 Swan Ltd.
* @author       Alexander "Alf" Arefyev (avaref@gmail.com)
* @co-author    Stas "Savage" Bykov (savage@swan.perm.ru)
* @version      02.2011
* @comment      Префикс для id компонентов EEPLEF (EmkEvnPLEditForm)
*
*
* @input data: action - действие (addEvnPL, editEvnPL, viewEvnPL, addEvnVizitPL, editEvnVizitPL, viewEvnVizitPL, closeEvnPL, copyEvnVizitPL)
*              EvnPL_id - ID талона амбулаторного пациента
*              EvnVizitPL_id - ID посещения
*              Person_id - ID человека
*              PersonEvn_id - ID состояния человека
*              Server_id - ID сервера
*
*/
/*NO PARSE JSON*/

sw.Promed.swEmkEvnPLEditWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swEmkEvnPLEditWindow',
	objectSrc: '/jscore/Forms/Common/swEmkEvnPLEditWindow.js',
	action: null,
	actionList: [
		'addEvnPL',
		'addEvnVizitPL',
		'openEvnPL',
		'closeEvnPL',
		'editEvnPL',
		'editEvnVizitPL',
		'viewEvnPL',
		'viewEvnVizitPL',
		'copyEvnVizitPL'
	],
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: false,
	closeAction: 'hide',
	collapsible: false,
	isFinish: null,
	s:0,
	VizitType_SysNick: null,
	checkMesOldUslugaComplexFields: function () {
		var win = this;
		var base_form = this.FormPanel.getForm();

		// проверка связи диагноза/услуги с MesOldUslugaComplex
		if (win.checkRequestId) {
			Ext.Ajax.abort(win.checkRequestId); // прервыем предыдущий, если есть
		}
		win.checkRequestId = Ext.Ajax.request({
			callback: function (options, success, response) {
				win.checkRequestId = false;

				if (response.responseText) {
					var result = Ext.util.JSON.decode(response.responseText);
					if (result.success) {

						if (result.hasDrugTherapySchemeLinks) {
							win.DrugTherapySchemePanel.show();
							if (win.DrugTherapySchemePanel.count == 0) {
								win.DrugTherapySchemePanel.addFieldSet();
							}

							if (result.DrugTherapySchemeIds) {
								win.DrugTherapySchemePanel.setBaseFilter(function (rec) {
									return rec.get('DrugTherapyScheme_id').inlist(result.DrugTherapySchemeIds);
								});
							}
							win.hasDrugTherapySchemeLinks = true;
						} else {
							win.DrugTherapySchemePanel.resetFieldSets();
							win.DrugTherapySchemePanel.hide();
						}

					}
				}
			}.createDelegate(this),
			params: {
				EvnVizitPL_setDate: !!base_form.findField('EvnVizitPL_setDate').getValue() ? base_form.findField('EvnVizitPL_setDate').getValue().format('d.m.Y') : getGlobalOptions().date,
				LpuUnitType_id: base_form.findField('LpuSection_id').getFieldValue('LpuUnitType_id'),
				EvnVizitPL_id: base_form.findField('EvnVizitPL_id').getValue(),
				Diag_id: base_form.findField('Diag_id').getValue()
			},
			url: '/?c=EvnVizit&m=checkMesOldUslugaComplexFields'
		});
	},
	checkAndOpenRepositoryObserv: function () {
		var win = this;
		var base_form = this.FormPanel.getForm();
		var params = {
			action: 'add',
			useCase: 'evnpspriem',
			callback: function(data) {
				if (!data) return false;
				win.RepositoryObservData = data;
			},
			MedStaffFact_id: win.userMedStaffFact.MedStaffFact_id,
			Person_id: base_form.findField('Person_id').getValue()
		};
		
		Ext.Ajax.request({
			callback: function(cbOptions, success, response) {
				if (success) {
					var response_obj = Ext.util.JSON.decode(response.responseText);
					if (response_obj[0] && response_obj[0].RepositoryObserv_id) {
						params.hasPrev = true;
						params.PlaceArrival_id = response_obj[0].PlaceArrival_id;
						params.KLCountry_id = response_obj[0].KLCountry_id;
						params.Region_id = response_obj[0].Region_id;
						params.RepositoryObserv_arrivalDate = response_obj[0].RepositoryObserv_arrivalDate;
						params.TransportMeans_id = response_obj[0].TransportMeans_id;
						params.RepositoryObserv_TransportDesc = response_obj[0].RepositoryObserv_TransportDesc;
						params.RepositoryObserv_TransportPlace = response_obj[0].RepositoryObserv_TransportPlace;
						params.RepositoryObserv_TransportRoute = response_obj[0].RepositoryObserv_TransportRoute;
						params.RepositoryObserv_FlightNumber = response_obj[0].RepositoryObserv_FlightNumber;
						params.RepositoryObserv_IsCVIContact = response_obj[0].RepositoryObserv_IsCVIContact;
						params.RepositoryObesrv_contactDate = response_obj[0].RepositoryObesrv_contactDate || null;
						params.RepositoryObserv_Height = response_obj[0].RepositoryObserv_Height;
						params.RepositoryObserv_Weight = response_obj[0].RepositoryObserv_Weight;
						getWnd('swRepositoryObservEditWindow').show(params);
					} else {
						getWnd('swRepositoryObservEditWindow').show(params);
					}
				}
			},
			params: {
				Person_id: base_form.findField('Person_id').getValue()
			},
			url: '/?c=RepositoryObserv&m=findByPerson'
		});
	},

	// Ссылки на компоненты (инициализируются в initComponent).
	// 1. Раздел "3. Основной диагноз":
	// 1.1. Диагноз:
	_cmbDiag: undefined,

	// 1.2. Стадия ХСН (для диагноза) #170429:
	_cmbDiagHSNStage: undefined,

	// 1.3. Функциональный класс (для диагноза) #170429:
	_cmbDiagHSNFuncClass: undefined,

	// 1.4. Осложнение:
	_cmbComplDiag: undefined,

	// 1.5. Стадия ХСН (для осложнения) #170429:
	_cmbComplDiagHSNStage: undefined,

	// 1.6. Функциональный класс (для осложнения) #170429:
	_cmbComplDiagHSNFuncClass: undefined,

	// 2. Панель формы (id = 'EmkEvnPLEditForm'):
	_formPanel: undefined,

	// 3. Форма (FormPanel.getForm()):
	_form: undefined,

	// 4. Скрытое поле с ид. пациента:
	_fldPersonId: undefined,

	/********** _refreshHsnDetails ***************************************************************************************
	 * #170429
	 * Настроить видимость комбобоксов "Стадия ХСН" и "Функциональный класс" в зависимости от того, относится ли
	 * диагноз/осложнение к группе ХСН.
	 * При скрытии очистить комбобоксы и сделать их необязательными для заполнения.
	 * При отображении настроить обязательность в зависимости от региона и, при необходимости, подгрузить значения,
	 * установленные пациенту ранее. Если эти значения непустые, выдать уведомление.
	 *
	 * cmb - комбобокс "Диагноз" или "Осложнение"
	 * loadLastValues - загружать ли значения, установленные пациенту ранее
	 ******************************************************************************************************************/
	_refreshHsnDetails: function(cmb, loadLastValues = true)
	{
		var cmbStage,
			cmbClass,
			isHsn,
			allowBlank;

		if (cmb == this._cmbDiag)
		{
			cmbStage = this._cmbDiagHSNStage;
			cmbClass = this._cmbDiagHSNFuncClass;
		}
		else
		{
			cmbStage = this._cmbComplDiagHSNStage;
			cmbClass = this._cmbComplDiagHSNFuncClass;
		}

		isHsn = (cmb.getFieldValue('Diag_Code').inlist(['I50.0', 'I50.1', 'I50.9']));

		allowBlank = !(isHsn && (getRegionNick() == 'ufa'));
		cmbStage.setAllowBlank(allowBlank);
		cmbClass.setAllowBlank(allowBlank);

		if (isHsn)
		{
			cmbStage.showContainer();
			cmbClass.showContainer();

			if (loadLastValues)
				Ext.Ajax.request(
				{
					url: '/?c=EvnPL&m=getLastHsnDetails',

					params:
					{
						Person_id: this._fldPersonId.getValue()
					},

					callback: function(options, success, response)
					{
						var stageId = null,
							classId = null,
							alertTxt,
							res;

						if (success &&
							(res = Ext.util.JSON.decode(response.responseText)) &&
							(res = res[0]))
						{
							stageId = res.HSNStage_id || null;
							classId = res.HSNFuncClass_id || null;

							if (stageId || classId)
								alertTxt =
									'Пациенту в предыдущем случае лечения установлены стадия ХСН и функциональный ' +
									'класс. При необходимости можно изменить стадию ХСН и функциональный класс.';
						}

						if (cmbStage)
							cmbStage.setValue(stageId);

						if (cmbClass)
							cmbClass.setValue(classId);

						if (alertTxt)
							sw.swMsg.alert('', alertTxt);
					}
				});
		}
		else
		{
			cmbStage.setValue(null);
			cmbStage.hideContainer();

			cmbClass.setValue(null);
			cmbClass.hideContainer();
		}
	},

	/********** _hideHsnDetails ***************************************************************************************
	 * #170429
	 * Очистить и скрыть комбобоксы "Стадия ХСН" и "Функциональный класс", относящиеся к основному диагнозу и
	 * осложнению, и сделать их необязательными для заполнения.
	 ******************************************************************************************************************/
	_hideHsnDetails: function()
	{
		[
			this._cmbDiagHSNStage,
			this._cmbDiagHSNFuncClass,
			this._cmbComplDiagHSNStage,
			this._cmbComplDiagHSNFuncClass
		].forEach(function(item, i, arr)
					{
						item.setValue(null);
						item.setAllowBlank(true);
						item.hideContainer();
					});
	},

	setDiagSpidComboDisabled: function() {

		if (!getRegionNick().inlist(['perm', 'msk']) || this.action.inlist([ 'viewEvnPL', 'viewEvnVizitPL'])) return false;

		var base_form = this.FormPanel.getForm();
		var diag_spid_combo = base_form.findField('Diag_spid');
		var iszno_checkbox = this.findById('EEPLEF_EvnVizitPL_IsZNOCheckbox');

		if (!diag_spid_combo.getValue()) return false;

		Ext.Ajax.request({
			params: {
				Person_id: base_form.findField('Person_id').getValue(),
				Diag_id: diag_spid_combo.getValue()
			},
			success: function(response, options) {
				var response_obj = Ext.util.JSON.decode(response.responseText);
				diag_spid_combo.setDisabled(response_obj.isExists == 2);
				iszno_checkbox.setDisabled(response_obj.isExists == 2);
			},
			url: '/?c=MorbusOnkoSpecifics&m=checkMorbusExists'
		});
	},
	setDiagConcComboVisible: function() {
		var base_form = this.FormPanel.getForm();
		var diagCode = base_form.findField('Diag_id').getFieldValue('Diag_Code');
		var lastDiagCode = (!Ext.isEmpty(base_form.findField('LastEvnVizitPL_Diag_Code').getValue()) ? base_form.findField('LastEvnVizitPL_Diag_Code').getValue() : diagCode);
		var Diag_lid_Code = base_form.findField('Diag_lid').getFieldValue('Diag_Code');
		var xdate = new Date(2016, 0, 1); // Поле обязательно если дата посещения 01-01-2016 или позже
		var lastEvnVizitPLDate = this.lastEvnVizitPLData ? Date.parseDate(this.lastEvnVizitPLData, 'd.m.Y') : new Date();

		if (this.action == 'addEvnPL') {
			lastEvnVizitPLDate = base_form.findField('EvnVizitPL_setDate').getValue();
		}
	
		base_form.findField('Diag_concid').setAllowBlank(true);
		if (getRegionNick() == 'kareliya') {
			if ( !Ext.isEmpty(lastDiagCode) && lastDiagCode.toString().substr(0, 1).inlist([ 'S', 'T' ]) ) {
				base_form.findField('Diag_concid').setContainerVisible(true);
				if (lastEvnVizitPLDate >= xdate) {
					base_form.findField('Diag_concid').setAllowBlank(false);
				}
			} else {
				base_form.findField('Diag_concid').clearValue();
				base_form.findField('Diag_concid').setContainerVisible(false);
			}
		}
		else {
			if ( !Ext.isEmpty(Diag_lid_Code) && Diag_lid_Code.toString().substr(0, 1).inlist([ 'S', 'T' ]) ) {
				base_form.findField('Diag_concid').setContainerVisible(true);
				if (lastEvnVizitPLDate >= xdate) {
					base_form.findField('Diag_concid').setDisabled(false);
					base_form.findField('Diag_concid').setAllowBlank(false);
				}
			}
			else {
				base_form.findField('Diag_concid').clearValue();
				base_form.findField('Diag_concid').setDisabled(true);
				base_form.findField('Diag_concid').setContainerVisible(false);
			}
		}

	},
	filterResultClassCombo: function() {

		// Переменная для региональных настроек:
		var regNick = getRegionNick();

		var base_form = this.FormPanel.getForm();
		var ResultClass_id = base_form.findField('ResultClass_id').getValue();
		
		var lastEvnVizitPLDate = this.lastEvnVizitPLDate ? Date.parseDate(this.lastEvnVizitPLData, 'd.m.Y') : new Date();

		base_form.findField('ResultClass_id').clearValue();
		base_form.findField('ResultClass_id').getStore().clearFilter();

		var xdate = new Date(2016, 0, 1);

		if (regNick == 'astra') {
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
					&& (!rec.get('ResultClass_Code') || !rec.get('ResultClass_Code').inlist(['6','7']) || regNick != 'perm' || lastEvnVizitPLDate < xdate)
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
	refreshFieldsVisibility: function(fieldNames) {
		var win = this;
		var base_form = win.FormPanel.getForm();
		var persFrame = win.findById('EEPLEF_PersonInformationFrame');
		if (typeof fieldNames == 'string') fieldNames = [fieldNames];

		var action = win.action;
		var regNick = getRegionNick();
		var Sex_Code = persFrame.getFieldValue('Sex_Code');
		var Person_BirthDay = persFrame.getFieldValue('Person_Birthday');

		base_form.items.each(function(field){
			if (!Ext.isEmpty(fieldNames) && !field.getName().inlist(fieldNames)) return;

			var value = field.getValue();
			var allowBlank = null;
			var visible = null;
			var enable = null;
			var filter = null;

			var dateX20170901 = new Date(2017, 8, 1); // 01.09.2017
			var dateX20180601 = new Date(2018, 5, 1); // 01.06.2018
			var dateX20181101 = new Date(2018, 10, 1); // 01.11.2018
			var Diag_Code = base_form.findField('Diag_id').getFieldValue('Diag_Code');
			var DeseaseType_SysNick = base_form.findField('DeseaseType_id').getFieldValue('DeseaseType_SysNick');
			var EvnVizitPL_setDate = base_form.findField('EvnVizitPL_setDate').getValue();

			var diag_code_full = !Ext.isEmpty(Diag_Code)?String(Diag_Code).slice(0, 3):'';

			var Person_Age = null;
			if (!Ext.isEmpty(Person_BirthDay) && !Ext.isEmpty(EvnVizitPL_setDate)) {
				var Person_Age = swGetPersonAge(Person_BirthDay, EvnVizitPL_setDate);
			}

			switch(field.getName()) {
				case 'TumorStage_id':
					visible = (
						regNick.inlist(['kareliya','ekb']) && (
							(diag_code_full >= 'C00' && diag_code_full <= 'C97') ||
							(diag_code_full >= 'D00' && diag_code_full <= 'D09')
						) &&
						(!regNick.inlist(['kareliya']) || (!Ext.isEmpty(EvnVizitPL_setDate) && EvnVizitPL_setDate >= dateX20170901)) &&
						(!regNick.inlist(['ekb']) || (!Ext.isEmpty(EvnVizitPL_setDate) && EvnVizitPL_setDate < dateX20180601))
					);
					if (visible) {
						enable = regNick.inlist(['ekb']) || DeseaseType_SysNick == 'new';
						if (regNick != 'ekb') {
							filter = function (record) {
								return record.get('TumorStage_Code').inlist([0, 1, 2, 3, 4])
							};
						}
						if (!enable) value = null;
					}
					allowBlank = !enable;
					break;
				case 'PainIntensity_id':
					visible = (
						regNick.inlist(['penza'])
						&& (
							(diag_code_full >= 'C00' && diag_code_full <= 'C97')
							|| (diag_code_full >= 'D00' && diag_code_full <= 'D09')
						)
						&& !Ext.isEmpty(EvnVizitPL_setDate)
						&& EvnVizitPL_setDate >= dateX20181101
					);
					enable = visible;
					allowBlank = !visible;
					if (Ext.isEmpty(value) && visible === true) {
						value = 1;
					}
					break;
				case 'PregnancyEvnVizitPL_Period':
					visible = (Sex_Code == 2 && Person_Age >= 15 && Person_Age <= 50);
					break;
			}

			if (visible === false && win.formLoaded) {
				value = null;
			}
			if (
				(
					!(value instanceof Date)
					&& value != field.getValue()
				)
				|| (
					(value instanceof Date)
					&& value.getTime() != field.getValue().getTime()
				)
			) {
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
	filterVizitTypeCombo: function() {
		var base_form = this.FormPanel.getForm();
		var formDate = base_form.findField('EvnVizitPL_setDate').getValue();

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

		// Переменная для региональных настроек:
		var regNick = getRegionNick();

		var base_form = this.FormPanel.getForm();
		var DiagCombo = base_form.findField('Diag_id');
		var Diag_id = DiagCombo.getValue();
		var DiagCode = DiagCombo.getFieldValue('Diag_Code');
		var VizitTypeCombo = base_form.findField('VizitType_id');
		var VizitType_id = VizitTypeCombo.getValue();
		var ServiceTypeCombo = base_form.findField('ServiceType_id');
		var ServiceType_id = ServiceTypeCombo.getValue();
		var TreatmentClassCombo = base_form.findField('TreatmentClass_id');
		var TreatmentClass_id = TreatmentClassCombo.getValue();
		
		if (!DiagCode) return false;
		
		TreatmentClassCombo.getStore().filterBy(function(rec) {
			if (DiagCode == 'Z51.5') {
				return (rec.get('TreatmentClass_id').inlist([ 9 ]));
			} else if (DiagCode.substr(0,1) == 'Z' || (regNick == 'perm' && DiagCode.substr(0,3) == 'W57')) {
				return (rec.get('TreatmentClass_id').inlist([ 6, 7, 8, 9, 10, 11, 12 ]));
			} else if (regNick == 'penza') {
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

	getFinanceSource: function() {
		var win = this,
			base_form = this.FormPanel.getForm();

		if (this.IsLoading) return false;

		if (getRegionNick() != 'kz') return false;

		if (this.action.inlist(['view'])) return false;

		if (base_form.findField('isPaidVisit').getValue()) return false;

		var params = {
			EvnDirection_setDate: Ext.util.Format.date(base_form.findField('EvnVizitPL_setDate').getValue(), 'd.m.Y'),
			Person_id: base_form.findField('Person_id').getValue(),
			TreatmentClass_id: base_form.findField('TreatmentClass_id').getValue(),
			LpuSectionProfile_id: base_form.findField('LpuSectionProfile_id').getValue(),
			UslugaComplex_id: base_form.findField('UslugaComplex_uid').getValue(),
			Diag_id: base_form.findField('Diag_id').getValue()
		};

		if (!params.LpuSectionProfile_id || !params.Diag_id || !params.TreatmentClass_id) return false;

		//var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Получение источника финансирования..." });
		var loadMask = new Ext.LoadMask(Ext.getBody(), { msg: "Получение источника финансирования..." });
		loadMask.show();

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

	//VizitType после загрузки окна редактирования
	deleteEvent: function(event) {
		/*if ( this.action == 'view' ) {
			return false;
		}*/

		if ( event != 'EvnDiagPL' && event != 'EvnUsluga' ) {
			return false;
		}

		var error = '';
		var grid = null;
		var question = '';
		var params = new Object();
		var url = '';

		switch ( event ) {
			case 'EvnDiagPL':
				error = lang['pri_udalenii_soputstvuyuschego_diagnoza_voznikli_oshibki'];
				grid = this.findById('EEPLEF_EvnDiagPLGrid');
				question = lang['udalit_soputstvuyuschiy_diagnoz'];
				url = '/?c=EvnPL&m=deleteEvnDiagPL';
			break;

			case 'EvnUsluga':
				error = lang['pri_udalenii_uslugi_voznikli_oshibki'];
				grid = this.findById('EEPLEF_EvnUslugaGrid');
				question = lang['udalit_uslugu'];
				url = '/?c=EvnUsluga&m=deleteEvnUsluga';
			break;
		}

		if ( !grid || !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get(event + '_id') ) {
			return false;
		}

		var selected_record = grid.getSelectionModel().getSelected();

		switch ( event ) {
			case 'EvnDiagPL':
				params['EvnDiagPL_id'] = selected_record.get('EvnDiagPL_id');
			break;

			case 'EvnUsluga':
				params['class'] = selected_record.get('EvnClass_SysNick');
				params['id'] = selected_record.get('EvnUsluga_id');
			break;
		}

		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId, text, obj) {
				if ( buttonId == 'yes' ) {
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
								sw.swMsg.alert(lang['oshibka'], response_obj.Error_Msg ? response_obj.Error_Msg : error);
							}
							else {
								grid.getStore().remove(selected_record);

								if ( event == 'EvnUsluga' ) {
									this.EvnUslugaGridIsModified = true;
									this.uetValuesRecount();
									this.checkMesOldUslugaComplexFields();
									//this.checkAbort();
								}

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
			}.createDelegate(this),
			icon: Ext.MessageBox.QUESTION,
			msg: question,
			title: lang['vopros']
		});
	},
	EvnUslugaGridIsModified: false,
	uetValuesRecount: function() {
		var base_form = this.FormPanel.getForm();
		var grid = this.findById('EEPLEF_EvnUslugaGrid');

		var evn_usluga_stom_uet = 0;
		var evn_usluga_stom_uet_oms = 0;

		var PayType_SysNick = 'oms';
		switch ( getRegionNick() ) {
			case 'by': PayType_SysNick = 'besus'; break;
			case 'kz': PayType_SysNick = 'Resp'; break;
		}

		grid.getStore().each(function(record) {
			if ( record.get('PayType_SysNick') == PayType_SysNick ) {
				evn_usluga_stom_uet_oms = evn_usluga_stom_uet_oms + Number(record.get('EvnUsluga_Summa'));
			}

			evn_usluga_stom_uet = evn_usluga_stom_uet + Number(record.get('EvnUsluga_Summa'));
		});

		base_form.findField('EvnVizitPL_Uet').setValue(evn_usluga_stom_uet.toFixed(2));
		base_form.findField('EvnVizitPL_UetOMS').setValue(evn_usluga_stom_uet_oms.toFixed(2));
	},
	setMKB: function(){
		var parentWin =this;
		var base_form = this.FormPanel.getForm();
		var sex = parentWin.findById('EEPLEF_PersonInformationFrame').getFieldValue('Sex_Code');
		var age = swGetPersonAge(parentWin.findById('EEPLEF_PersonInformationFrame').getFieldValue('Person_Birthday'),base_form.findField('EvnVizitPL_setDate').getValue());
		base_form.findField('Diag_id').setMKBFilter(age,sex,true);
	},
	setInoterFilter: function() {
		var base_form = this.FormPanel.getForm();
		if ( getRegionNick() == 'buryatiya' ) {
			var oms_spr_terr_code = this.PersonInfo.getFieldValue('OmsSprTerr_Code');
			base_form.findField('UslugaComplex_uid').getStore().baseParams.isInoter = (Ext.isEmpty(oms_spr_terr_code) || oms_spr_terr_code == 0 || oms_spr_terr_code > 100);
		}
	},
	loadOtherVizitList: function(callback) {
		callback = callback || Ext.emptyFn;
		var base_form = this.FormPanel.getForm();
		Ext.Ajax.request({
			url: '/?c=EvnPL&m=loadEvnVizitPLGrid',
			params: {EvnPL_id: base_form.findField('EvnPL_id').getValue()},
			success: function(response) {
				var vizitList = Ext.util.JSON.decode(response.responseText);
				log(vizitList);
				log(Ext.isArray(vizitList));
				if (Ext.isArray(vizitList)) {
					this.OtherVizitList = [];
					for(var i=0; i<vizitList.length; i++) {
						if (vizitList[i].EvnVizitPL_id != base_form.findField('EvnVizitPL_id').getValue()) {
							this.OtherVizitList.push(vizitList[i]);
						}
					}
				}
				callback.call(this);
			}.createDelegate(this)
		});
	},
	hasPreviusChildVizit: function() {
		var base_form = this.FormPanel.getForm();

		var currSetDate = Ext.util.Format.date(base_form.findField('EvnVizitPL_setDate').getValue(), 'd.m.Y');
		var currSetTime = base_form.findField('EvnVizitPL_setTime').getValue();
		var currSetDT = getValidDT(currSetDate, currSetTime);

		if (!currSetDT || !Ext.isArray(this.OtherVizitList)) {
			return false;
		}

		for(var i=0; i<this.OtherVizitList.length; i++) {
			var vizit = this.OtherVizitList[i];
			var setDT = getValidDT(vizit.EvnVizitPL_setDate, vizit.EvnVizitPL_setTime);
			if (vizit.LpuSectionAge_id == 2 && setDT && setDT <= currSetDT) {
				return true
			}
		}
		return false;
	},
	isLastVizit: function() {
		if (this.action == 'addEvnPL') {
			// при добавлении ТАП посещение является первым и последним :)
			return true;
		}
		if (!Ext.isArray(this.OtherVizitList)) {
			return false;
		}
		if (this.OtherVizitList.length == 0) {
			return true;
		}

		var base_form = this.FormPanel.getForm();
		var currSetDate = Ext.util.Format.date(base_form.findField('EvnVizitPL_setDate').getValue(), 'd.m.Y');
		var currSetTime = base_form.findField('EvnVizitPL_setTime').getValue();
		var currSetDT = getValidDT(currSetDate, currSetTime);

		for(var i=0; i<this.OtherVizitList.length; i++) {
			var vizit = this.OtherVizitList[i];
			var setDT = getValidDT(vizit.EvnVizitPL_setDate, vizit.EvnVizitPL_setTime);
			if (setDT > currSetDT) {
				return false;
			}
		}
		return true;
	},
	checkVisitProfiles: function(){
		//выполняется проверка соответствия профиля отделения в первом посещении и профиля отделения во всех последующих посещениях
		var flag = false;
		var base_form = this.FormPanel.getForm();

		var controlDate = new Date(2019, 7, 1);
		var evnVizitPL_setDate = base_form.findField('EvnVizitPL_setDate').getValue();
		if(controlDate >= evnVizitPL_setDate) return flag;

		var allVizitList = [];
		if(this.OtherVizitList && this.OtherVizitList.length>0){
			// собираем другие посещения в рамках ТАП
			for(var i=0; i<this.OtherVizitList.length; i++) {
				var vizit = this.OtherVizitList[i];
				var obj = {
					EvnVizitPL_id: vizit.EvnVizitPL_id,
					setDT: getValidDT(vizit.EvnVizitPL_setDate, vizit.EvnVizitPL_setTime),
					LpuSectionProfile_id: vizit.LpuSectionProfile_id,
					LpuSectionProfile_Code: vizit.LpuSectionProfile_Code
				}
				allVizitList.push(obj);
			}

			var currSetDate = Ext.util.Format.date(base_form.findField('EvnVizitPL_setDate').getValue(), 'd.m.Y');
			var currSetTime = base_form.findField('EvnVizitPL_setTime').getValue();
			// берем текущее посещение
			var currentVizit = {
				EvnVizitPL_id:  base_form.findField('EvnVizitPL_id').getValue(),
				setDT: getValidDT(currSetDate, currSetTime),
				LpuSectionProfile_id: base_form.findField('LpuSectionProfile_id').getValue(),
				LpuSectionProfile_Code: base_form.findField('LpuSectionProfile_id').getFieldValue('LpuSectionProfile_Code')
			}
			// собираем все посещения в один массив
			allVizitList.push(currentVizit);

			// сортируем полученный массив
			allVizitList.sort(function(a,b) { 
			    return a.setDT - b.setDT;
			});
			var arrNotControlProfileCode = [];
			var arrControlProfileCode = [];
			var arrVizitsProfileCode = [];
			for(var i=0; i<allVizitList.length; i++) {
				var vizit = allVizitList[i];
				if(arrVizitsProfileCode.indexOf(vizit.LpuSectionProfile_Code)<0) arrVizitsProfileCode.push(vizit.LpuSectionProfile_Code);
				if(!vizit.LpuSectionProfile_Code.inlist(getGlobalOptions().exceptionprofiles)) {
					flag = true;
					if(arrNotControlProfileCode.indexOf(vizit.LpuSectionProfile_Code)<0) arrNotControlProfileCode.push(vizit.LpuSectionProfile_Code);
				}else{
					arrControlProfileCode.push(vizit.LpuSectionProfile_Code);
				}
			}
			if(arrVizitsProfileCode.length == 1) flag = false;
			if(flag && arrControlProfileCode.length > 0 && arrNotControlProfileCode.length == 1){
				// есть одно или более посещений, в которых указаны профили «97», «57», «58», «42», «68», «3», «136»
				// И в остальных посещениях указан одинаковый профиль отделения, отличный от профилей «97», «57», «58», «42», «68», «3», «136»
				flag = false;
			}
		}
		return flag;
	},
	doSave: function(options){
		var options = options||{};
		var base_form = this.FormPanel.getForm();
		var pid = base_form.findField('EvnVizitPL_id').getValue();
		var win = this;

		// #170429
		// Если детализация ХСН отображается и по диагнозу, и по осложнению, и эта детализация не совпадает, выдается
		// сообщение об ошибке, сохранение не выполняется.
		if (this._cmbDiagHSNStage && this._cmbDiagHSNStage.isVisible() &&
			this._cmbDiagHSNFuncClass && this._cmbDiagHSNFuncClass.isVisible() &&
			this._cmbComplDiagHSNStage && this._cmbComplDiagHSNStage.isVisible() &&
			this._cmbComplDiagHSNFuncClass && this._cmbComplDiagHSNFuncClass.isVisible() &&
			(this._cmbDiagHSNStage.getValue() != this._cmbComplDiagHSNStage.getValue() ||
			 this._cmbDiagHSNFuncClass.getValue() != this._cmbComplDiagHSNFuncClass.getValue()))
		{
			sw.swMsg.show({
								title: ERR_INVFIELDS_TIT,
								icon: Ext.Msg.WARNING,
								msg: langs('Стадия ХСН и функциональный класс, указанные по основному диагнозу и осложнению, не совпадают. Данные сохранить невозможно.'),
								buttons: Ext.Msg.OK,

								fn: function()
								{
									this.formStatus = 'edit';
									this._cmbDiagHSNStage.focus(false);
								}.createDelegate(this)
							});
			return false;
		}

		if ((getRegionNick() == 'ekb') && options.isDoSave && pid) {
			// проверка услуг
			if ( this.formStatus == 'save' || this.action.inlist([ 'viewEvnPL', 'viewEvnVizitPL' ]) ) return false;
			var fieldMes = base_form.findField('Mes_id');
			var uslugaList = function(pid, parent, callback){
				Ext.Ajax.request({
					url: '?c=EvnUsluga&m=loadEvnUslugaGrid',
					params: {
						pid: pid,
						parent: parent
					},
					callback: function(opt, success, response) {
						var response_obj = Ext.util.JSON.decode(response.responseText);
						if (success ){
							callback(response_obj.length);
						}else{
							callback(1);
						}
					},
					failure: function(response, opts){
						console.log('error loadEvnUslugaGrid');
						callback(1);
					}
				})
			}

			uslugaList(pid, 'EvnVizitPL', function(n){
				options.UslugaListLength = n;
				win.doSaveContinuation(options);
			});
		}else{
			this.doSaveContinuation(options);
		}
	},
	doSaveContinuation: function(options) {
		// options @Object
		// options.ignoreEvnUslugaCountCheck @Boolean Не проверять наличие выполненных услуг, если true
		// options.print @Boolean Вызывать печать талона, если true
		// options.openChildWindow @Function Открыть дочернее окно после сохранения
		var win = this;

		// Переменная для региональных настроек:
		var regNick = getRegionNick();

		if ( this.formStatus == 'save' || this.action.inlist([ 'viewEvnPL', 'viewEvnVizitPL' ]) ) {
			return false;
		}

		this.formStatus = 'save';

		options = options||{};
		options.ignoreErrors = options.ignoreErrors || [];
		
		var base_form = this.FormPanel.getForm();
		var diagGroup=null;
		var diagIndex = base_form.findField('Diag_id').getStore().findBy(function(rec){return (rec.get('Diag_id')==base_form.findField('Diag_id').getValue())})
		if(diagIndex>=0){
			diagGroup = base_form.findField('Diag_id').getStore().getAt(diagIndex).get('Diag_Code')[0];
		}
		var traumaField = base_form.findField('PrehospTrauma_id');
		var isBuryatiya = (regNick == 'buryatiya');
		var isEkb = (regNick == 'ekb');
		var isUfa = (regNick == 'ufa');
		var isKareliya = (regNick == 'kareliya');
		var isAstra = (regNick == 'astra');
		var is_finish = base_form.findField('EvnPL_IsFinish').getValue();
		if(!Ext.isEmpty(diagGroup) && is_finish==2 && diagGroup.inlist(['S','T']) && this.findById('EEPLEF_DirectInfoPanel').hidden && Ext.isEmpty(traumaField.getValue())){
			getWnd('swSelectFromSprWindow').show({
							comboSubject:'PrehospTrauma',
							callback: function(val){
								traumaField.setValue(val);
								win.doSave(options);
							}
						});
			this.formStatus = 'edit';
			return false;
		}
		
		// Если случай не закончен - посещения не проверяем https://redmine.swan.perm.ru/issues/86067
		var tc_allowblank = base_form.findField('TreatmentClass_id').allowBlank;
		if ((is_finish != 2 && this.action != 'editEvnVizitPL' ) || (regNick == 'kareliya' && Ext.isEmpty(base_form.findField('Diag_id').getValue()))) {
			if (regNick != 'khak') {
				base_form.findField('TreatmentClass_id').setAllowBlank(true);
			}
		}
		
		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					this.formStatus = 'edit';
					this.FormPanel.getFirstInvalidEl().focus(false);
					base_form.findField('TreatmentClass_id').setAllowBlank(tc_allowblank);
					log(["Неверно заполнено поле",this.FormPanel.getFirstInvalidEl()]);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		if ( Ext.isEmpty(win.UslugaComplex_uid_AgeGroupId) ) {
			win.UslugaComplex_uid_AgeGroupId = base_form.findField('UslugaComplex_uid').getFieldValue('UslugaComplex_AgeGroupId');
		}

		if (
			regNick == 'pskov'
			&& !Ext.isEmpty(win.UslugaComplex_uid_AgeGroupId)
			&& !Ext.isEmpty(base_form.findField('LpuSection_id').getFieldValue('LpuSectionAge_id'))
			&& !( // воз. группа отделения имеет смешанный приём или его воз. группа соответсвует воз. группе посещения
				base_form.findField('LpuSection_id').getFieldValue('LpuSectionAge_id') == 3
				|| base_form.findField('LpuSection_id').getFieldValue('LpuSectionAge_id') == base_form.findField('UslugaComplex_uid').getFieldValue('UslugaComplex_AgeGroupId')
			)
		) {
			this.formStatus = 'edit';
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: langs('Выбранный код посещения не соответствует возрастной группе отделения.'),
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		if (regNick == 'ufa') {
			if (!options.ignoreIsPaid) {
				if ( !Ext.isEmpty(base_form.findField('EvnVizitPL_IsPaid').getValue()) && base_form.findField('EvnVizitPL_IsPaid').getValue() == 2 ) {
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
		var lpusection_oid = base_form.findField('LpuSection_oid').getValue();
		var person_age = swGetPersonAge(this.findById('EEPLEF_PersonInformationFrame').getFieldValue('Person_Birthday'), base_form.findField('EvnVizitPL_setDate').getValue());
		
		if(lpusection_oid&&person_age!=-1){
			if(!options.ignoreLpuSectionoidAgeCheck&& ((base_form.findField('LpuSection_oid').getFieldValue('LpuSectionAge_id') == 1 && person_age <= 17) || (base_form.findField('LpuSection_oid').getFieldValue('LpuSectionAge_id') == 2 && person_age >= 18))) {

				this.formStatus = 'edit';
				sw.swMsg.show({
					buttons: Ext.Msg.YESNO,
					fn: function(buttonId, text, obj) {
						if ( buttonId == 'yes' ) {
							options.ignoreLpuSectionoidAgeCheck = true;
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
		var lpusection_id = base_form.findField('LpuSection_id').getValue();
		if(lpusection_id&&person_age!=-1&&!this.hasPreviusChildVizit()){
			if(!options.ignoreLpuSectionAgeCheck&& ((base_form.findField('LpuSection_id').getFieldValue('LpuSectionAge_id') == 1 && person_age <= 17) || (base_form.findField('LpuSection_id').getFieldValue('LpuSectionAge_id') == 2 && person_age >= 18))) {

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

		var EvnVizitPL_setDate = base_form.findField('EvnVizitPL_setDate').getValue();
		var evn_pl_ukl = base_form.findField('EvnPL_UKL').getValue();
		var index;
		var is_finish = base_form.findField('EvnPL_IsFinish').getValue();
		var lpu_section_profile_code = '';
		var pay_type_nick = '';
		var record;
		var diag_code = '';
		var diag_name = '';

		if (isUfa) {
			// https://redmine.swan.perm.ru/issues/15258
			// Проверяем, чтобы для профилактического посещения случай был закончен
			index = base_form.findField('UslugaComplex_uid').getStore().findBy(function(rec) {
				return (rec.get('UslugaComplex_id') == base_form.findField('UslugaComplex_uid').getValue());
			});

			if ( index >= 0 ) {
				var code = base_form.findField('UslugaComplex_uid').getStore().getAt(index).get('UslugaComplex_Code');

				if ( is_finish != 2 && !Ext.isEmpty(code) && code.toString().length == 6 && isProphylaxisVizitOnly(code) ) {
					this.formStatus = 'edit';
					sw.swMsg.alert(langs('Ошибка'), langs('Для профилактического/консультативного посещения должен быть указан признак окончания случая лечения и результат лечения'), function() {
						base_form.findField('EvnPL_IsFinish').focus(true);
					});
					return false;
				}
			}
		}

		if ( isBuryatiya || isKareliya || isAstra ) {
			var vizit_type_sys_nick = base_form.findField('VizitType_id').getFieldValue('VizitType_SysNick');
			if ( this.action == 'editEvnVizitPL' ) {
				if ( this.VizitType_SysNick != vizit_type_sys_nick && vizit_type_sys_nick != 'desease' && vizit_type_sys_nick != 'ConsulDiagn' && this.isFinish == 2 ) {
					this.formStatus = 'edit';
					sw.swMsg.alert(lang['oshibka'], lang['nevozmojno_izmenit_tsel_posescheniya_sluchay_apl_zakryit']);
					return false;
				}
				if ( this.vizitCount > 1 && vizit_type_sys_nick != 'desease' && vizit_type_sys_nick != 'ConsulDiagn' && !isKareliya) {
					this.formStatus = 'edit';
					sw.swMsg.alert(lang['oshibka'], lang['v_sluchae_apl_bolee_odnogo_posescheniya']);
					return false;
				}
				if ( this.vizitCount > 1 && vizit_type_sys_nick != 'desease' && (vizit_type_sys_nick != 'consulspec' || !EvnVizitPL_setDate || EvnVizitPL_setDate >= new Date(2019, 0, 1)) && isKareliya ) {
					this.formStatus = 'edit';
					sw.swMsg.alert(lang['oshibka'], lang['v_sluchae_apl_bolee_odnogo_posescheniya']);
					return false;
				}
				if ( this.vizitCount == 1 && vizit_type_sys_nick == 'desease' && is_finish == 2 && !isBuryatiya) {
					this.formStatus = 'edit';
					sw.swMsg.alert(lang['oshibka'], lang['sohranenie_zakryitogo_tap_po_zabolevaniyu_s_odnim_posescheniem_nevozmojno'], function() {
						base_form.findField('EvnPL_IsFinish').focus(true);
					});
					return false;
				}
				if ( this.vizitCount == 1 && vizit_type_sys_nick == 'consulspec' && is_finish == 2 && isKareliya && EvnVizitPL_setDate && EvnVizitPL_setDate < new Date(2019, 0, 1) ) {
					this.formStatus = 'edit';
					sw.swMsg.alert(lang['oshibka'], 'Сохранение закрытого ТАП по диспансерному наблюдению с одним посещением невозможно', function() {
						base_form.findField('EvnPL_IsFinish').focus(true);
					});
					return false;
				}
				if ( this.vizitCount == 1 && vizit_type_sys_nick == 'desease' && is_finish == 2 && isBuryatiya && '301' == base_form.findField('ResultClass_id').getFieldValue('ResultClass_Code')) {
					this.formStatus = 'edit';
					sw.swMsg.alert(lang['oshibka'], lang['esli_v_poseschenii_ukazana_tsel_zabolevanie_i_rezultat_obrascheniya_301_to_v_tap_doljno_byit_ne_menshe_dvuh_posescheniy'], function() {
						base_form.findField('ResultClass_id').focus(true);
					});
					return false;
				}
				
				if (this.vizitCount == 1 && vizit_type_sys_nick != 'desease' && vizit_type_sys_nick != 'ConsulDiagn' && is_finish == 1 && regNick != 'kareliya') {
					
					this.formStatus = 'edit';
					sw.swMsg.alert(lang['oshibka'], lang['sohranenie_nezakryitogo_tap_nevozmojno'], function() {
						base_form.findField('EvnPL_IsFinish').focus(true);
					});
					return false;
				}
			}
			if ( this.action == 'addEvnPL' ) {
				if ( vizit_type_sys_nick == 'desease' && is_finish == 2 && !isBuryatiya ) {
					this.formStatus = 'edit';
					sw.swMsg.alert(lang['oshibka'], lang['sohranenie_zakryitogo_tap_po_zabolevaniyu_s_odnim_posescheniem_nevozmojno'], function() {
						base_form.findField('EvnPL_IsFinish').focus(true);
					});
					return false;
				}
				if ( vizit_type_sys_nick == 'consulspec' && is_finish == 2 && isKareliya && EvnVizitPL_setDate && EvnVizitPL_setDate < new Date(2019, 0, 1) ) {
					this.formStatus = 'edit';
					sw.swMsg.alert(lang['oshibka'], 'Сохранение закрытого ТАП по диспансерному наблюдению с одним посещением невозможно', function() {
						base_form.findField('EvnPL_IsFinish').focus(true);
					});
					return false;
				}
				if ( vizit_type_sys_nick == 'desease' && is_finish == 2 && isBuryatiya && '301' == base_form.findField('ResultClass_id').getFieldValue('ResultClass_Code')) {
					this.formStatus = 'edit';
					sw.swMsg.alert(lang['oshibka'], lang['esli_v_poseschenii_ukazana_tsel_zabolevanie_i_rezultat_obrascheniya_301_to_v_tap_doljno_byit_ne_menshe_dvuh_posescheniy'], function() {
						base_form.findField('ResultClass_id').focus(true);
					});
					return false;
				}
				if ( vizit_type_sys_nick != 'desease' && vizit_type_sys_nick != 'ConsulDiagn' && is_finish == 1 && regNick != 'kareliya') {
					this.formStatus = 'edit';
					sw.swMsg.alert(lang['oshibka'], lang['sohranenie_nezakryitogo_tap_nevozmojno'], function() {
						base_form.findField('EvnPL_IsFinish').focus(true);
					});
					return false;
				}
			}
		}

		if ( isEkb ) {
			//debugger;
			var fieldPayType = base_form.findField('PayType_id'); // вид опалаты
			var fieldMes = base_form.findField('Mes_id'); // МЭС
			var fieldUslugaComplex = base_form.findField('UslugaComplex_uid'); // код посещения

			//эта проверка не должна отрабатывать "при автоматическом сохранении посещения, при вызове формы добавления услуги"
			if( fieldMes.getValue() && options.isDoSave != undefined && options.isDoSave ){
				/*
				if( !fieldMes.getFieldValue('MesOldVizit_Code').inlist([811, 812, 901, 902, 664]) && fieldPayType.getFieldValue('PayType_SysNick') == 'bud' && !fieldUslugaComplex.getValue() ){
					sw.swMsg.alert(langs('Ошибка'), 'Поле <b>&laquo; Код посещения &raquo;</b> обязательно для заполнения');
					this.formStatus = 'edit';
					return false;
				}
				*/
				if( fieldMes.getFieldValue('MesOldVizit_Code').inlist([901, 902, 664])) flagMes = true;
				if( fieldMes.getFieldValue('MesOldVizit_Code').inlist([811, 812]) && fieldPayType.getFieldValue('PayType_SysNick') == 'bud') flagMes = true;
				if(!fieldUslugaComplex.getValue() && !flagMes){
					sw.swMsg.alert(langs('Ошибка'), 'Поле <b>&laquo; Код посещения &raquo;</b> обязательно для ввода');
					this.formStatus = 'edit';
					return false;
				}
				if( !Ext.isEmpty(options.UslugaListLength) && options.UslugaListLength==0 && fieldMes.getFieldValue('MesOldVizit_Code').inlist([811, 812, 901, 902, 664]) ){
					sw.swMsg.alert(lang['oshibka'], 'Если в поле <b> &laquo; МЭС &raquo;</b> указано значение 811, 812, 901, 902, 664, то в разделе <b>&laquo; Услуги &raquo;</b> должна быть добавлена услуга');
					this.formStatus = 'edit';
					return false;
				}
			}
		}

		if ( is_finish == 2 ) {
			if (((regNick != 'kareliya' && Ext.isEmpty(evn_pl_ukl)) || evn_pl_ukl < 0 || evn_pl_ukl > 1 ) && regNick != 'ekb') {
				sw.swMsg.show({
					buttons: Ext.Msg.OK,
					fn: function() {
						this.formStatus = 'edit';
						base_form.findField('EvnPL_UKL').focus(false);
					}.createDelegate(this),
					icon: Ext.Msg.WARNING,
					msg: lang['proverte_pravilnost_zapolneniya_polya_ukl'] + (regNick != 'kareliya' ? lang['pri_zakonchennom_sluchae_ukl_doljno_byit_zapolneno'] : ''),
					title: ERR_INVFIELDS_TIT
				});
				return false;
			}
            if (!base_form.findField('Diag_id').getValue()) {
				this.formStatus = 'edit';
                sw.swMsg.alert(lang['soobschenie'], lang['sluchay_lecheniya_doljen_imet_hotya_byi_odin_osnovnoy_diagnoz']);
                return false;
            }
		}

		if (regNick == 'vologda' && is_finish == 2 && this.checkVisitProfiles()) {
			this.formStatus = 'edit';
			sw.swMsg.alert(langs('Сообщение'), langs('При закрытом случае АПЛ в рамках одного ТАП для всех посещений должен быть указан один профиль отделения'));
			return false;
		}

        //При добавлении явно сохраняем текущего врача для исправления этого https://redmine.swan.perm.ru/issues/25424
        //т.к. врач может иметь в одном отделении несколько рабочих мест, то правильнее брать то, с которым была открыта форма
		var medstafffact_id,lpu_section_id,med_personal_id, medstafffact_combo = base_form.findField('MedStaffFact_id');
		if ( this.action.inlist(['addEvnPL', 'addEvnVizitPL']))
		{
			medstafffact_id = this.userMedStaffFact.MedStaffFact_id;
			lpu_section_id = this.userMedStaffFact.LpuSection_id;
			med_personal_id = this.userMedStaffFact.MedPersonal_id;
		}
		else
		{
			medstafffact_id = medstafffact_combo.getValue();
			lpu_section_id = base_form.findField('LpuSection_id').getValue();
			med_personal_id = base_form.findField('MedPersonal_id').getValue();
		}
		index = medstafffact_combo.getStore().findBy(function(rec) {
            return (
                (medstafffact_id && rec.get('MedStaffFact_id') == medstafffact_id)
            );
		});

		if ( index >= 0 ) {
			record = medstafffact_combo.getStore().getAt(index);
		}
		//record = medstafffact_combo.getStore().getById(medstafffact_id);
        /*log([
            medstafffact_combo.getValue(),
            medstafffact_id,
            record,
            this.userMedStaffFact
        ]);*/
		if ( record ) {
			lpu_section_profile_code = record.get('LpuSectionProfile_Code');
            medstafffact_id = record.get('MedStaffFact_id');
            lpu_section_id = record.get('LpuSection_id');
            med_personal_id = record.get('MedPersonal_id');
            base_form.findField('MedPersonal_id').setValue(record.get('MedPersonal_id'));
			if (base_form.findField('MedStaffFact_id').getValue() != record.get('MedStaffFact_id')) {
				base_form.findField('MedStaffFact_id').setValue(record.get('MedStaffFact_id'));
				base_form.findField('MedStaffFact_id').fireEvent('change', base_form.findField('MedStaffFact_id'), base_form.findField('MedStaffFact_id').getValue());
			}
		}
		else if(this.action.inlist(['addEvnPL', 'addEvnVizitPL']))
		{
			this.formStatus = 'edit';
			sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_popyitke_poluchit_zapis_o_rabochem_meste_vracha_s_identifikatorom']+ medstafffact_id);
			log(medstafffact_combo.getStore());
			return false;
		}

		record = base_form.findField('MedStaffFact_sid').getStore().getById(base_form.findField('MedStaffFact_sid').getValue());
		if ( record ) {
			base_form.findField('MedPersonal_sid').setValue(record.get('MedPersonal_id'));
		}

		record = base_form.findField('PayType_id').getStore().getById(base_form.findField('PayType_id').getValue());
		if ( record ) {
			pay_type_nick = record.get('PayType_SysNick');
		}

		var omsPayTypeExists = ('oms' == pay_type_nick);

		record = base_form.findField('Diag_id').getStore().getById(base_form.findField('Diag_id').getValue());
		if ( record ) {
			diag_code = record.get('Diag_Code');
			diag_name = record.get('Diag_Name');

			// https://redmine.swan.perm.ru/issues/21764
			if ( diag_code.substr(0, 1).toUpperCase() != 'Z' && Ext.isEmpty(base_form.findField('DeseaseType_id').getValue()) ) {
				sw.swMsg.show({
					buttons: Ext.Msg.OK,
					fn: function() {
						this.formStatus = 'edit';
						base_form.findField('DeseaseType_id').markInvalid(lang['pole_obyazatelno_dlya_zapolneniya_pri_vyibrannom_diagnoze']);
						base_form.findField('DeseaseType_id').focus(false);
					}.createDelegate(this),
					icon: Ext.Msg.WARNING,
					msg: lang['ne_zadan_harakter_zabolevaniya'],
					title: ERR_INVFIELDS_TIT
				});
				return false;
			}

			if (regNick == 'ekb') {
				var sex_code = this.PersonInfo.getFieldValue('Sex_Code');
				var person_age = swGetPersonAge(this.PersonInfo.getFieldValue('Person_Birthday'), base_form.findField('EvnVizitPL_setDate').getValue());
				var person_age_month = swGetPersonAgeMonth(this.PersonInfo.getFieldValue('Person_Birthday'), base_form.findField('EvnVizitPL_setDate').getValue());
				var person_age_day = swGetPersonAgeDay(this.PersonInfo.getFieldValue('Person_Birthday'), base_form.findField('EvnVizitPL_setDate').getValue());

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
				if ( pay_type_nick.inlist(['oms']) ) {
					if ( lpu_section_profile_code.inlist([ '658', '684', '558', '584' ]) ) {
						if ( record.get('DiagFinance_IsHealthCenter') != 1 ) {
							sw.swMsg.alert(lang['oshibka'], lang['diagnoz_ne_oplachivaetsya_dlya_tsentrov_zdorovya'], function() {
								this.formStatus = 'edit';
								base_form.findField('Diag_id').markInvalid(lang['diagnoz_ne_oplachivaetsya_dlya_tsentrov_zdorovya']);
								base_form.findField('Diag_id').focus(true);
							}.createDelegate(this));
							return false;
						}
					}
				
					else if ( record.get('DiagFinance_IsOms') == 0 ) {
						var textMsg = lang['diagnoz_ne_oplachivaetsya_po_oms'];
						if(isEkb){
							textMsg=lang['dannyiy_diagnoz_ne_podlejit_oplate_v_sisteme_oms_smenite_vid_oplatyi'];
						}
						sw.swMsg.alert(lang['oshibka'], textMsg, function() {
							this.formStatus = 'edit';
							base_form.findField('Diag_id').markInvalid(textMsg);
							base_form.findField('Diag_id').focus(true);
						}.createDelegate(this));
						return false;
					}
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
			} else if (regNick == 'buryatiya') {
				if (pay_type_nick == 'oms') {
					var sex_code = this.PersonInfo.getFieldValue('Sex_Code');
					if (!sex_code || !(sex_code.toString().inlist(['1', '2']))) {
						this.formStatus = 'edit';
						sw.swMsg.alert(lang['oshibka'], lang['ne_ukazan_pol_patsienta']);
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
							msg: lang['vyibrannyiy_diagnoz_ne_sootvetstvuet_polu'],
							title: lang['oshibka']
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
								msg: lang['vyibrannyiy_diagnoz_ne_oplachivaetsya_po_oms_prodoljit_sohranenie'],
								title: lang['prodoljit_sohranenie']
							});
							return false;
						}
					}
				}
			} else if (regNick == 'astra') {
				if (pay_type_nick == 'oms') {
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
								msg: lang['vyibrannyiy_diagnoz_ne_oplachivaetsya_po_oms_prodoljit_sohranenie'],
								title: lang['prodoljit_sohranenie']
							});
							return false;
						}
					}
				}
			} else if (regNick == 'kaluga') {
				if (pay_type_nick == 'oms') {
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
								msg: lang['vyibrannyiy_diagnoz_ne_oplachivaetsya_po_oms_poetomu_sluchay_ne_budet_vklyuchen_v_reestr_prodoljit_sohranenie'],
								title: lang['prodoljit_sohranenie']
							});
							return false;
						}
					}
				}
			} else if (regNick == 'kareliya') {
				if (!options.ignoreDiagFinance && pay_type_nick == 'oms') {
					var sex_code = this.PersonInfo.getFieldValue('Sex_Code');
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
			} else {
				// https://redmine.swan.perm.ru/issues/4081
				// Проверка на финансирование по ОМС основного диагноза
				if ( (isUfa == true || isEkb == true) && pay_type_nick == 'oms' ) {
					if ( lpu_section_profile_code.inlist([ '658', '684', '558', '584' ]) ) {
						if ( record.get('DiagFinance_IsHealthCenter') != 1 ) {
							sw.swMsg.alert(lang['oshibka'], lang['diagnoz_ne_oplachivaetsya_dlya_tsentrov_zdorovya'], function() {
								this.formStatus = 'edit';
								base_form.findField('Diag_id').markInvalid(lang['diagnoz_ne_oplachivaetsya_dlya_tsentrov_zdorovya']);
								base_form.findField('Diag_id').focus(true);
							}.createDelegate(this));
							return false;
						}
					}
				
					else if ( record.get('DiagFinance_IsOms') == 0 ) {
						var textMsg = lang['diagnoz_ne_oplachivaetsya_po_oms'];
						if(isEkb){
							textMsg=lang['dannyiy_diagnoz_ne_podlejit_oplate_v_sisteme_oms_smenite_vid_oplatyi'];
						}
						sw.swMsg.alert(lang['oshibka'], textMsg, function() {
							this.formStatus = 'edit';
							base_form.findField('Diag_id').markInvalid(textMsg);
							base_form.findField('Diag_id').focus(true);
						}.createDelegate(this));
						return false;
					}
					else {
						var oms_spr_terr_code = this.PersonInfo.getFieldValue('OmsSprTerr_Code');
						var person_age = swGetPersonAge(this.PersonInfo.getFieldValue('Person_Birthday'), base_form.findField('EvnVizitPL_setDate').getValue());
						var sex_code = this.PersonInfo.getFieldValue('Sex_Code');

						if ( person_age == -1 ) {
							this.formStatus = 'edit';
							sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_opredelenii_vozrasta_patsienta']);
							return false;
						}

						if ( !sex_code || !(sex_code.toString().inlist([ '1', '2' ])) ) {
							this.formStatus = 'edit';
							sw.swMsg.alert(lang['oshibka'], lang['ne_ukazan_pol_patsienta']);
							return false;
						}

						if ( person_age >= 18 ) {
							if ( Number(record.get('PersonAgeGroup_Code')) == 2 ) {
								sw.swMsg.alert(lang['oshibka'], lang['diagnoz_ne_oplachivaetsya_dlya_vzroslyih'], function() {
									this.formStatus = 'edit';
									base_form.findField('Diag_id').markInvalid(lang['diagnoz_ne_oplachivaetsya_dlya_vzroslyih']);
									base_form.findField('Diag_id').focus(true);
								}.createDelegate(this));
								return false;
							}
						}
						else if ( Number(record.get('PersonAgeGroup_Code')) == 1 ) {
							sw.swMsg.alert(lang['oshibka'], lang['diagnoz_ne_oplachivaetsya_dlya_detey'], function() {
								this.formStatus = 'edit';
								base_form.findField('Diag_id').markInvalid(lang['diagnoz_ne_oplachivaetsya_dlya_detey']);
								base_form.findField('Diag_id').focus(true);
							}.createDelegate(this));
							return false;
						}

						if ( Number(sex_code) == 1 ) {
							if ( Number(record.get('Sex_Code')) == 2 ) {
								sw.swMsg.alert(lang['oshibka'], lang['diagnoz_ne_sootvetstvuet_polu_patsienta'], function() {
									this.formStatus = 'edit';
									base_form.findField('Diag_id').markInvalid(lang['diagnoz_ne_sootvetstvuet_polu_patsienta']);
									base_form.findField('Diag_id').focus(true);
								}.createDelegate(this));
								return false;
							}
						}
						else if ( Number(record.get('Sex_Code')) == 1 ) {
							sw.swMsg.alert(lang['oshibka'], lang['diagnoz_ne_sootvetstvuet_polu_patsienta'], function() {
								this.formStatus = 'edit';
								base_form.findField('Diag_id').markInvalid(lang['diagnoz_ne_sootvetstvuet_polu_patsienta']);
								base_form.findField('Diag_id').focus(true);
							}.createDelegate(this));
							return false;
						}

						if (regNick == 'ufa' && oms_spr_terr_code != 61 && record.get('DiagFinance_IsAlien') == '0') {
							sw.swMsg.alert(lang['oshibka'], lang['diagnoz_ne_oplachivaetsya_dlya_patsientov_zastrahovannyih_ne_v_rb'], function() {
								this.formStatus = 'edit';
								base_form.findField('Diag_id').markInvalid(lang['diagnoz_ne_oplachivaetsya_dlya_patsientov_zastrahovannyih_ne_v_rb']);
								base_form.findField('Diag_id').focus(true);
							}.createDelegate(this));
							return false;
						}

						if (regNick == 'ufa' && record.get('DiagFinance_IsFacult') == '0') {
							sw.swMsg.alert(langs('Ошибка'), langs('Данный диагноз может быть только сопутствующим. Укажите верный основной диагноз.'), function() {
								this.formStatus = 'edit';
								base_form.findField('Diag_id').markInvalid(langs('Данный диагноз может быть только сопутствующим. Укажите верный основной диагноз.'));
								base_form.findField('Diag_id').focus(true);
							}.createDelegate(this));
							return false;
						}
					}
				}
			}
		}

		var params = new Object();

		params.action = this.action;
		
		// Если копируем посещение с талоном (action = 'copyEvnVizitPL'), то всеравно используем экшн добавления action = 'addEvnPL'
		if(this.action == 'copyEvnVizitPL')
			params.action = 'addEvnPL';
		
		// Если копируем посещение с талоном (action = 'copyEvnVizitPL'), то всеравно используем экшн добавления action = 'addEvnPL'
		if(params.action.inlist([ 'addEvnPL', 'addEvnVizitPL' ]))
		{
			params.allowCreateEmptyEvnDoc = 2;
			if(base_form.findField('EvnXml_id').getValue()) {
				params.copyEvnXml_id = base_form.findField('EvnXml_id').getValue();
				params.EvnXml_id = null;
			}
		}
		
        params.VizitType_id = base_form.findField('VizitType_id').getValue();
        params.ServiceType_id = base_form.findField('ServiceType_id').getValue();
        params.HomeVisit_id = this.HomeVisit_id || null;

		if ( base_form.findField('LpuSection_id').disabled ) {
			params.LpuSection_id = base_form.findField('LpuSection_id').getValue();
		}

		if ( base_form.findField('LpuSectionProfile_id').disabled ) {
			params.LpuSectionProfile_id = base_form.findField('LpuSectionProfile_id').getValue();
		}

		if (regNick == 'ekb' && base_form.findField('Mes_id').disabled) {
			params.Mes_id = base_form.findField('Mes_id').getValue();
		}

		if ( base_form.findField('UslugaComplex_uid').disabled ) {
			params.UslugaComplex_uid = base_form.findField('UslugaComplex_uid').getValue();
		}

		if ( base_form.findField('PayType_id').disabled ) {
			params.PayType_id = base_form.findField('PayType_id').getValue();
			if (!params.PayType_id && getRegionNick() == 'kz') {
				params.PayType_id = 152;
			}
		}

		if ( base_form.findField('PayTypeKAZ_id').disabled ) {
			params.PayTypeKAZ_id = base_form.findField('PayTypeKAZ_id').getValue();
		}

		if ( base_form.findField('ScreenType_id').disabled ) {
			params.ScreenType_id = base_form.findField('ScreenType_id').getValue();
		}

		if ( base_form.findField('TreatmentClass_id').disabled ) {
			params.TreatmentClass_id = base_form.findField('TreatmentClass_id').getValue();
		}

		if ( base_form.findField('Diag_spid').disabled ) {
			params.Diag_spid = base_form.findField('Diag_spid').getValue();
		}

        if (!medstafffact_combo.getValue()) {
            medstafffact_combo.setValue(Ext.isEmpty(medstafffact_id) ? this.userMedStaffFact.MedStaffFact_id : medstafffact_id);
			//medstafffact_combo.fireEvent('change', medstafffact_combo, medstafffact_combo.getValue());
        }
        if (medstafffact_combo.disabled) {
            params.MedStaffFact_id = medstafffact_combo.getValue();
        }

		if ( options.ignoreDayProfileDuplicateVizit ) {
			params.ignoreDayProfileDuplicateVizit = 1;
		}
		/* в /jscore/Forms/Polka/swTemplatesEvnVizitPLEditWindow.js закомментировано
		// Если Уфа и ignoreEvnUslugaCountCheck = false либо не задан, то проверяем количество введенных услуг
		// Если не введено ни одно услуги, то посещение не сохраняем и выдаем сообщение
		// если введено более одной услуги, то тоже выдаем сообщение
		if ( (!options || !options.ignoreEvnUslugaCountCheck) && getGlobalOptions().region && getGlobalOptions().region.nick == 'ufa' ) {
			var evn_usluga_store = this.findById('EVPLEF_EvnUslugaGrid').getStore();

			if ( evn_usluga_store.getCount() == 0 || evn_usluga_store.getCount() > 1 || !evn_usluga_store.getAt(0).get('EvnUsluga_id') ) {
				sw.swMsg.show({
					buttons: Ext.Msg.OK,
					fn: function() {
						this.formStatus = 'edit';
						this.findById('EVPLEF_EvnUslugaGrid').getView().focusRow(0);
						this.findById('EVPLEF_EvnUslugaGrid').getSelectionModel().selectFirstRow();
					}.createDelegate(this),
					icon: Ext.Msg.WARNING,
					msg: lang['posescheniyu_doljna_sootvetstvovat_odna_usluga'],
					title: ERR_INVFIELDS_TIT
				});
				return false;
			}
		}
		if ( options.ignoreEvnUslugaCountCheck == true ) {
			params.ignoreEvnUslugaCountCheck = 1;
		}
		*/
		try {
			if (isKareliya && 'oms' == pay_type_nick && '313' == base_form.findField('ResultClass_id').getFieldValue('ResultClass_Code') ) {
				throw {warningMsg: lang['posescheniya_s_rezultatom_obrascheniya_␓_konstatatsiya_fakta_smerti_kod_-_313_oplate_za_schet_oms_ne_podlejat'], fieldName: 'ResultClass_id'};
			}
			else if (regNick == 'perm' && true == omsPayTypeExists && '313' == base_form.findField('LeaveType_fedid').getFieldValue('LeaveType_Code')) {
				throw {msg: lang['sluchai_s_ishodom_313_konstatatsiya_fakta_smerti_v_poliklinike_ne_podlejat_oplate_po_oms_dlya_sohraneniya_izmenite_vid_oplatyi'], fieldName: 'LeaveType_fedid'};
			}
		} catch(err) {
			if (err.warningMsg) {
				if (false == err.warningMsg.toString().inlist(options.ignoreErrors)) {
					sw.swMsg.show({
						buttons: Ext.Msg.YESNO,
						fn: function(buttonId, text, obj) {
							win.formStatus = 'edit';
							if ('yes' == buttonId) {
								options.ignoreErrors.push(err.warningMsg);
								win.doSave(options);
							} else if (err.fieldName && base_form.findField(err.fieldName)) {
								base_form.findField(err.fieldName).markInvalid(err.warningMsg);
								base_form.findField(err.fieldName).focus(true);
							}
						},
						icon: Ext.Msg.WARNING,
						msg: err.warningMsg + '<br>Продолжить сохранение?',
						title: lang['preduprejdenie']
					});
					return false;
				}
			} else {
				win.formStatus = 'edit';
				sw.swMsg.alert(lang['oshibka'], err.msg || err.toString());
				return false;
			}
		}

		params = this.panelEvnDirectionAll.onBeforeSubmit(this, params);
		if (!params) {
			return false;
		}

		if (!options.ignoreCheckIsNeedOnkoControl
			&& options.isDoSave
			&& this.action.inlist([ 'addEvnPL', 'addEvnVizitPL','editEvnVizitPL' ])
		) {
			sw.Promed.PersonOnkoProfile.checkIsNeedOnkoControl(
				{
					Person_id: base_form.findField('Person_id').getValue(),
					Person_Birthday: this.PersonInfo.getFieldValue('Person_Birthday'),
					EvnVizitPL_setDate: base_form.findField('EvnVizitPL_setDate').getValue(),
					MedStaffFact_id: this.userMedStaffFact.MedStaffFact_id
				},
				this,
				function(allowSave, onSuccess, onFailure){
					if (allowSave) {
						options.ignoreCheckIsNeedOnkoControl = true;
						options.onSuccessSaveEvnVizitPLOnkoControl = onSuccess;
						options.onFailureSaveEvnVizitPLOnkoControl = onFailure;
						this.formStatus = 'edit';
						this.doSave(options);
					} else {
						this.formStatus = 'edit';
					}
				}
			);
			return false;
		}

		// @task https://redmine.swan.perm.ru/issues/84712
		if (regNick == 'ekb') {
			this.setDefaultMedicalCareKind();
		}

		if (regNick == 'astra') {
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

        if ( base_form.findField('MedicalCareKind_vid').disabled ) {
            params.MedicalCareKind_vid = base_form.findField('MedicalCareKind_vid').getValue();
        }

        params.isAutoCreate = (options
            && typeof options.openChildWindow == 'function'
            && this.action.inlist([ 'addEvnPL', 'addEvnVizitPL', 'editEvnVizitPL', 'closeEvnPL' ])
            ) ? 1 : 0;

		if ( options.ignoreUslugaComplexTariffCountCheck ) {
			params.ignoreUslugaComplexTariffCountCheck = 1;
		}
		if ( options.ignoreControl59536 ) {
			params.ignoreControl59536 = 1;
		}
		if ( options.ignoreControl122430 ) {
			params.ignoreControl122430 = 1;
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

		if ( this.findById('EEPLEF_EvnVizitPL_IsZNOCheckbox').getValue() == true ) {
			base_form.findField('EvnVizitPL_IsZNO').setValue(2);
		}
		else {
			base_form.findField('EvnVizitPL_IsZNO').setValue(1);
		}

		params.vizit_kvs_control_check = (options && !Ext.isEmpty(options.vizit_kvs_control_check) && options.vizit_kvs_control_check === 1) ? 1 : 0;
		params.vizit_intersection_control_check = (options && !Ext.isEmpty(options.vizit_intersection_control_check) && options.vizit_intersection_control_check === 1) ? 1 : 0;
		params.ignoreLpuSectionProfileVolume = (options && !Ext.isEmpty(options.ignoreLpuSectionProfileVolume) && options.ignoreLpuSectionProfileVolume === 1) ? 1 : 0;
		params.ignoreMesUslugaCheck = (options && !Ext.isEmpty(options.ignoreMesUslugaCheck) && options.ignoreMesUslugaCheck === 1) ? 1 : 0;
		params.ignoreParentEvnDateCheck = (options && !Ext.isEmpty(options.ignoreParentEvnDateCheck) && options.ignoreParentEvnDateCheck === 1) ? 1 : 0;
		params.ignoreFirstDisableCheck = (options && !Ext.isEmpty(options.ignoreFirstDisableCheck) && options.ignoreFirstDisableCheck === 1) ? 1 : 0;
		params.ignoreCheckEvnUslugaChange = (options && !Ext.isEmpty(options.ignoreCheckEvnUslugaChange) && options.ignoreCheckEvnUslugaChange === 1) ? 1 : 0;
		params.ignoreCheckB04069333 = (options && !Ext.isEmpty(options.ignoreCheckB04069333) && options.ignoreCheckB04069333 === 1) ? 1 : 0;
		params.ignoreCheckTNM = (options && !Ext.isEmpty(options.ignoreCheckTNM) && options.ignoreCheckTNM === 1) ? 1 : 0;
		params.ignoreDiagDispCheck = (options && !Ext.isEmpty(options.ignoreDiagDispCheck) && options.ignoreDiagDispCheck === 1) ? 1 : 0;
		params.ignoreCheckMorbusOnko = (options && !Ext.isEmpty(options.ignoreCheckMorbusOnko) && options.ignoreCheckMorbusOnko === 1) ? 1 : 0;
		params.addB04069333 = (options && !Ext.isEmpty(options.addB04069333) && options.addB04069333 === 1) ? 1 : 0;
		params.EvnVizitPLDoublesData = (options && options.EvnVizitPLDoublesData) ? options.EvnVizitPLDoublesData : null;
		//params.ignoreNoExecPrescr = (options && options.ignoreNoExecPrescr) ? options.ignoreNoExecPrescr : null;
		params.ignoreNoExecPrescr = 1;

		if (this.DrugTherapySchemePanel.isVisible()) {
			params.DrugTherapyScheme_ids = this.DrugTherapySchemePanel.getIds();
		}

		params.RepositoryObservData = Ext.util.JSON.encode(this.RepositoryObservData);

		var loadMask = new Ext.LoadMask(this.getEl(), {
			msg: "Подождите, идет сохранение..."
		});
		loadMask.show();

		base_form.submit({
			failure: function(result_form, action) {
				this.formStatus = 'edit';
				loadMask.hide();
				
				if ( action.result ) {
					if ( action.result.Error_Msg && !action.result.Error_Msg.inlist(['YesNo', 'EvnVizitPLDouble', 'Ok'])) {
						var msg = action.result.Error_Msg;

						if (action.result.Error_Code == 112 && action.result.addMsg) {
							var headMsg = lang['informatsiya_o_peresecheniyah'];
							var addMsg = escapeHtml(action.result.addMsg);
							msg += '<br/> <a onclick="Ext.Msg.alert(\' ' + headMsg +  ' \',\' ' + addMsg +  ' \');" href=\'#\' >Подробнее</a>';
						}

						 
						sw.swMsg.alert(langs('Ошибка'), msg);
						if (options && typeof options.onFailureSaveEvnVizitPLOnkoControl == 'function') {
							options.onFailureSaveEvnVizitPLOnkoControl();
						}
						
					} else if ( action.result.Alert_Msg
						&& ('Ok' == action.result.Error_Msg || (action.result.Error_Code && action.result.Error_Code == 212)) ) {
						var params = {
							msg: action.result.Alert_Msg,
							icon: Ext.Msg.WARNING,
							title: ERR_WND_TIT,
							buttons: Ext.Msg.OK
						};
						// Если уже произошло сохранение и необходимо исполнить функцию - исполняем
						if ( options && typeof options.openChildWindow == 'function' && action.result.Error_Code == 212) {
							sw.swMsg.hide();
							options.openChildWindow();
						} else {
							// если пришел код ошибки - реагируем с сообщением и последующим сохранением если все ок
							if(action.result.Error_Code == 212){
								params.fn = function(buttonId, text, obj) {
									if(buttonId == 'ok') {
										win.doSave({
											openChildWindow:function () {
												var params = {
											action: 'edit',
													MorbusOnko_pid: base_form.findField('EvnVizitPL_id').getValue(),
													EvnVizitPL_id: base_form.findField('EvnVizitPL_id').getValue(),
											Person_id: base_form.findField('Person_id').getValue(),
											PersonEvn_id: base_form.findField('PersonEvn_id').getValue(),
													Server_id: base_form.findField('Server_id').getValue(),
													allowSpecificEdit: true
												};
												getWnd('swMorbusOnkoWindow').show(params);
											}.createDelegate(this)
										});
									}
								};
							}
							// и при любом раскладе отображаем сообщение об ошибке
							sw.swMsg.show(params);
						}
					} else if ( action.result.Alert_Msg && 'YesNo' == action.result.Error_Msg ) {
						var msg = action.result.Alert_Msg;

						if (action.result.Error_Code == 112 && action.result.addMsg) {
							var headMsg = lang['informatsiya_o_peresecheniyah'];
							var addMsg = escapeHtml(action.result.addMsg);
							msg += '<br/> <a onclick="Ext.Msg.alert(\' ' + headMsg +  ' \',\' ' + addMsg +  ' \');" href=\'#\' >Подробнее</a>';
						}

                        sw.swMsg.show({
                            buttons: Ext.Msg.YESNO,
                            fn: function(buttonId, text, obj) {
                                if ( buttonId == 'yes' ) {
									switch (true) {
										case (197641 == action.result.Error_Code):
											options.ignoreNoExecPrescr = 1;
											break;
										case (102 == action.result.Error_Code):
											options.ignoreUslugaComplexTariffCountCheck = 1;
											break;
										case (103 == action.result.Error_Code):
											options.ignoreControl59536 = 1;
											break;
										case (105 == action.result.Error_Code):
											options.ignoreControl122430 = 1;
											break;
										case (104 == action.result.Error_Code):
											options.ignoreEvnDirectionProfile = 1;
											break;
										case (106 == action.result.Error_Code):
											options.ignoreMorbusOnkoDrugCheck = 1;
											break;
										case (110 == action.result.Error_Code):
											options.ignoreKareliyaKKND = 1;
											break;
										case (111 == action.result.Error_Code):
											options.vizit_kvs_control_check = 1;
											break;
										case (112 == action.result.Error_Code):
											options.vizit_intersection_control_check = 1;
											break;
										case (113 == action.result.Error_Code):
											options.ignoreLpuSectionProfileVolume = 1;
											break;
										case (114 == action.result.Error_Code):
											options.ignoreMesUslugaCheck = 1;
											break;
										case (109 == action.result.Error_Code):
											options.ignoreParentEvnDateCheck = 1;
											break;
										case (115 == action.result.Error_Code):
											options.ignoreFirstDisableCheck = 1;
											break;
										case (130 == action.result.Error_Code):
											options.ignoreCheckEvnUslugaChange = 1;
											break;
										case (131 == action.result.Error_Code):
											options.addB04069333 = 1;
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
											formParams.Diag_id = Ext.isEmpty(base_form.findField('Diag_lid').getValue()) ? base_form.findField('Diag_id').getValue():base_form.findField('Diag_lid').getValue();

											params_disp.action = 'add';
											params_disp.callback = Ext.emptyFn;
											params_disp.formParams = formParams;
											params_disp.onHide = Ext.emptyFn;

											getWnd('swPersonDispEditWindow').show(params_disp);
											break;
										case (289 == action.result.Error_Code):
											options.ignoreCheckMorbusOnko = 1;
											break;
										default: 
											options.ignoreDayProfileDuplicateVizit = true;
											break;
									}
                                    this.doSave(options);
                                }
                                else {
                                	switch (true) {
		                                case (197641 == action.result.Error_Code):
			                                base_form.findField('EvnPL_IsFinish').setValue(1);
			                                break;
										case (131 == action.result.Error_Code):
											options.addB04069333 = 1;
											options.ignoreCheckB04069333 = 1;
											this.doSave(options);
											break;
										case (182 == action.result.Error_Code):
											options.ignoreDiagDispCheck = 1;
											this.doSave(options);
											break;
										default:
											base_form.findField('EvnVizitPL_setDate').focus(true);
											break;
									}
                                }
                            }.createDelegate(this),
                            icon: Ext.MessageBox.QUESTION,
                            msg: msg,
                            title: lang['prodoljit_sohranenie']
                        });
										
					} else if ( action.result.Alert_Msg && 'EvnVizitPLDouble' == action.result.Error_Msg ) {
						getWnd('swEvnVizitPLDoublesWindow').show({
							EvnVizitPLDoublesData: action.result.Alert_Msg,
							callback: function(data) {
								options.EvnVizitPLDoublesData = data.EvnVizitPLDoublesData;
								this.doSave(options);
							}.createDelegate(this)
						});
					} else {
						sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_1]']);
						if (options && typeof options.onFailureSaveEvnVizitPLOnkoControl == 'function') {
							options.onFailureSaveEvnVizitPLOnkoControl();
						}
					}
				}
			}.createDelegate(this),
			params: params,
			success: function(result_form, action) {
				this.formStatus = 'edit';
				loadMask.hide();
				if ( action.result ) {
					if ( action.result.EvnPL_id ) {
						base_form.findField('EvnPL_id').setValue(action.result.EvnPL_id);
					}

					if ( action.result.EvnUslugaCommon_id ) {
						base_form.findField('EvnUslugaCommon_id').setValue(action.result.EvnUslugaCommon_id);
					}

					if ( action.result.EvnVizitPL_id ) {
						base_form.findField('EvnVizitPL_id').setValue(action.result.EvnVizitPL_id);
						base_form.findField('UslugaComplex_uid').getStore().baseParams.EvnVizit_id = base_form.findField('EvnVizitPL_id').getValue();
					}
					if ( action.result.TimetableGraf_id) {
						base_form.findField('TimetableGraf_id').setValue(action.result.TimetableGraf_id);
					}
					if ( action.result.EvnXml_id) {
						base_form.findField('EvnXml_id').setValue(action.result.EvnXml_id);
					}

					if (options && typeof options.onSuccessSaveEvnVizitPLOnkoControl == 'function') {
						options.onSuccessSaveEvnVizitPLOnkoControl(base_form.findField('EvnVizitPL_id').getValue());
					}
							
					checkSuicideRegistry({
						'Evn_id': action.result.EvnPL_id,
						'EvnClass_SysNick': 'EvnPL'
					});

					if ( options && typeof options.openChildWindow == 'function' /*&& this.action.inlist([ 'addEvnPL', 'addEvnVizitPL' ])*/ ) {
						if ( action.result.Alert_Msg && action.result.Alert_Msg.toString().length > 0 ) {
							sw.swMsg.alert(lang['preduprejdenie'], action.result.Alert_Msg, function() {
								options.openChildWindow();
							});
						}
						else {
							options.openChildWindow();
						}
					}
					else {
						var data = new Object();

						data.EvnPL_id = base_form.findField('EvnPL_id').getValue();
						data.EvnUslugaCommon_id = base_form.findField('EvnUslugaCommon_id').getValue();
						data.EvnVizitPL_id = base_form.findField('EvnVizitPL_id').getValue();

						var onSave = function() {
							// #154675 ТАП. Формирование системного сообщения при сохранении ТАПа со случаем подозрения на ЗНО
							var record = base_form.findField('Diag_id').getStore().getById(base_form.findField('Diag_id').getValue());
							if (record && record.get('Diag_id')) {
								var diag_code = record.get('Diag_Code');
								var diag_name = record.get('Diag_Name');
								var diag_id = record.get('Diag_id');

								if (regNick == 'ufa' && diag_code == 'Z03.1' && this.action.inlist(['addEvnPL'])) {
									//this.formStatus = 'edit';
									//код специальности врача из справочника [dbo].[MedSpecOms], равный значениям MedSpecOms_Code = 17, 41, 73, 74, 82, 243, 265, MedSpecOms_Name = Онкология/Детская онкология.
									var MedSpecOms_Code = base_form.findField('MedStaffFact_id').getFieldValue('MedSpecOms_Code');

									var params = new Object();
									if (MedSpecOms_Code.inlist(['17', '41', '73', '74', '82', '243', '265'])) {
										params.MedSpecOms = true;
									} else {
										params.MedSpecOms = false;
									}
									params.Person_id = this.PersonInfo.getFieldValue('Person_id');
									params.Server_id = base_form.findField('Server_id').getValue();
									params.userMedStaffFact = sw.Promed.MedStaffFactByUser.last;
									params.EvnPL_id = base_form.findField('EvnPL_id').getValue();
									params.EvnUslugaCommon_id = base_form.findField('EvnUslugaCommon_id').getValue();
									params.EvnDirection_id = base_form.findField('EvnVizitPL_id').getValue();
									params.EvnVizitPL_id = base_form.findField('EvnVizitPL_id').getValue();
									//params.EvnVizitPL_id = base_form.findField('EvnVizitPL_id').getValue(); // EvnDirection_id
									params.Diag_id = diag_id;
									params.ZNOinfo = true;

									getWnd('swZNOinfoWindow').show(params);
								}
							}

							this.callback(data);
							this.hide();
							/*if ( this.action.inlist([ 'addEvnPL', 'addEvnVizitPL','editEvnVizitPL' ]) )
							{
								var mh_reg = new RegExp("^(A0[0-9]|A2[0-8]|A[3-4]|A7[5-9]|A[8-9]|B0[0-9]|B1[5-9]|B2|B3[0-4]|B[5-7]|B8[0-3]|B9[0-6]|B97.[0-8]|B99)");
								//log(["dsafsfsdfsdf",diag_code])
								if(mh_reg.test(diag_code)) {
									requestEvnInfectNotify({
										EvnInfectNotify_pid: base_form.findField('EvnVizitPL_id').getValue()
										,Diag_Name: diag_code + ' ' + diag_name
										//,Diag_id: base_form.findField('Diag_id').getValue()
										,Server_id: base_form.findField('Server_id').getValue()
										,PersonEvn_id: base_form.findField('PersonEvn_id').getValue()
										,MedPersonal_id: base_form.findField('MedPersonal_id').getValue()
										,EvnInfectNotify_FirstTreatDate: base_form.findField('EvnVizitPL_setDate').getValue()
										,EvnInfectNotify_SetDiagDate: base_form.findField('EvnVizitPL_setDate').getValue()
									});
								}
							}*/
						}.createDelegate(this);
						if ( action.result.Alert_Msg && action.result.Alert_Msg.toString().length > 0 ) {
							sw.swMsg.alert(lang['preduprejdenie'], action.result.Alert_Msg, onSave);
						}
						else {
							onSave();
						}
/*
						if ( options && options.print == true ) {
							window.open('/?c=EvnPL&m=printEvnPL&EvnPL_id=' + base_form.findField('EvnPL_id').getValue(), '_blank');

							this.action = 'edit';
							this.setTitle(WND_POL_EPLEDIT);

						}
						else {
							this.hide();
						}
*/
					}
				}
				else {
					sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_2]']);
					if (options && typeof options.onFailureSaveEvnVizitPLOnkoControl == 'function') {
						options.onFailureSaveEvnVizitPLOnkoControl();
					}
				}
			}.createDelegate(this)
		});
		return true;
	},
	draggable: false,
	setInterruptLeaveTypeVisible: function() {
		var base_form = this.FormPanel.getForm();

		var lastEvnVizitPLDate = base_form.findField('EvnVizitPL_setDate').getValue();
		if (!Ext.isEmpty(base_form.findField('LastEvnVizitPL_setDate').getValue()) && Date.parseDate(base_form.findField('LastEvnVizitPL_setDate').getValue(), 'd.m.Y') > lastEvnVizitPLDate) {
			lastEvnVizitPLDate = Date.parseDate(base_form.findField('LastEvnVizitPL_setDate').getValue(), 'd.m.Y');
		}

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
	formStatus: 'edit',
	setRiskLevelComboState: function() {
		// @task https://redmine.swan.perm.ru/issues/113607
		// Регион: Астрахань
		// Для посещений взрослых с даты >= 21.07.2017 и детей с даты >=24.07.2017 поле не отображается.
		var
			base_form = this.FormPanel.getForm(),
			dateXAdult = new Date(2017, 6, 21),
			dateXChild = new Date(2017, 6, 24),
			EvnVizitPL_setDate = base_form.findField('EvnVizitPL_setDate').getValue(),
			Person_Age = swGetPersonAge(this.findById('EEPLEF_PersonInformationFrame').getFieldValue('Person_Birthday'), EvnVizitPL_setDate),
			VizitType_SysNick = base_form.findField('VizitType_id').getFieldValue('VizitType_SysNick');

		if (
			getRegionNick() == 'astra'
			&& VizitType_SysNick == 'cz'
			&& !Ext.isEmpty(EvnVizitPL_setDate)
			&& !(Person_Age >= 18 && EvnVizitPL_setDate >= dateXAdult)
			&& !(Person_Age < 18 && EvnVizitPL_setDate >= dateXChild)
		) {
			base_form.findField('RiskLevel_id').enable();
			base_form.findField('RiskLevel_id').setAllowBlank(false);
			base_form.findField('RiskLevel_id').setContainerVisible(true);
		}
		else {
			base_form.findField('RiskLevel_id').clearValue();
			base_form.findField('RiskLevel_id').disable();
			base_form.findField('RiskLevel_id').setAllowBlank(true);
			base_form.findField('RiskLevel_id').setContainerVisible(false);
		}	
	},
	setWellnessCenterAgeGroupsComboState: function() {
		// @task https://redmine.swan.perm.ru/issues/113607
		// Регион: Астрахань
		// Поле отображается для посещений детей с даты >= 24.07.2017, если в поле «Цель посещения» указано «25. Центр здоровья».
		// Для взрослых поле не отображается.
		// Поле обязательное для заполнения.
		// При отображении значений для выбора учитывается возраст пациента (верхняя граница не включается в интервал).
		var
			base_form = this.FormPanel.getForm(),
			dateXChild = new Date(2017, 6, 24),
			EvnVizitPL_setDate = base_form.findField('EvnVizitPL_setDate').getValue(),
			index,
			Person_Age = swGetPersonAge(this.findById('EEPLEF_PersonInformationFrame').getFieldValue('Person_Birthday'), EvnVizitPL_setDate),
			VizitType_SysNick = base_form.findField('VizitType_id').getFieldValue('VizitType_SysNick'),
			WellnessCenterAgeGroups_id = base_form.findField('WellnessCenterAgeGroups_id').getValue();

		if (
			getRegionNick() == 'astra'
			&& VizitType_SysNick == 'cz'
			&& !Ext.isEmpty(EvnVizitPL_setDate)
			&& Person_Age >= 2
			&& Person_Age < 18
			&& EvnVizitPL_setDate >= dateXChild
		) {
			base_form.findField('WellnessCenterAgeGroups_id').enable();
			base_form.findField('WellnessCenterAgeGroups_id').setAllowBlank(false);
			base_form.findField('WellnessCenterAgeGroups_id').setContainerVisible(true);

			// Фильтрация
			base_form.findField('WellnessCenterAgeGroups_id').getStore().filterBy(function(rec) {
				return (
					rec.get('WellnessCenterAgeGroups_From') <= Person_Age
					&& rec.get('WellnessCenterAgeGroups_To') > Person_Age
				);
			});

			if ( !Ext.isEmpty(WellnessCenterAgeGroups_id) ) {
				index = base_form.findField('WellnessCenterAgeGroups_id').getStore().findBy(function(rec) {
					return (rec.get('WellnessCenterAgeGroups_id') == WellnessCenterAgeGroups_id);
				});

				if ( index >= 0 ) {
					base_form.findField('WellnessCenterAgeGroups_id').setValue(WellnessCenterAgeGroups_id);
				}
				else {
					base_form.findField('WellnessCenterAgeGroups_id').clearValue();
				}
			}
		}
		else {
			base_form.findField('WellnessCenterAgeGroups_id').clearValue();
			base_form.findField('WellnessCenterAgeGroups_id').disable();
			base_form.findField('WellnessCenterAgeGroups_id').setAllowBlank(true);
			base_form.findField('WellnessCenterAgeGroups_id').setContainerVisible(false);
		}	
	},
	getEvnPLNumber: function() {
		/*if ( !this.action.inlist(['addEvnPL', 'copyEvnVizitPL']) ) {
			return false;
		}*/

		var bf = this.FormPanel.getForm();
		var evnpl_num_field = bf.findField('EvnPL_NumCard');

		var params = new Object();

		if ( !Ext.isEmpty(bf.findField('EvnVizitPL_setDate').getValue()) ) {
			params.year = bf.findField('EvnVizitPL_setDate').getValue().format('Y');
		}

		var loadMask = new Ext.LoadMask(this.getEl(), {
			msg: "Получение номера талона..."
		});
		loadMask.show();

		Ext.Ajax.request({
			callback: function(options, success, response) {
				loadMask.hide();

				if ( success ) {
					var response_obj = Ext.util.JSON.decode(response.responseText);

					evnpl_num_field.setValue(response_obj.EvnPL_NumCard);
					evnpl_num_field.focus(true);
				}
				else {
					sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_opredelenii_nomera_talona']);
				}
			},
			params: params,
			url: '/?c=EvnPL&m=getEvnPLNumber'
		});
		return true;
	},
	id: 'EmkEvnPLEditWindow',
	loadLpuSectionProfileDop: function() {

		// Переменная для региональных настроек:
		var regNick = getRegionNick();

		var win = this;
		var base_form = this.FormPanel.getForm();
			var oldValue = base_form.findField('LpuSectionProfile_id').getValue();

			if (!Ext.isEmpty(base_form.findField('LpuSection_id').getValue())) {
				if (
					!base_form.findField('LpuSectionProfile_id').getStore().baseParams.LpuSection_id || 
					base_form.findField('LpuSection_id').getValue() != base_form.findField('LpuSectionProfile_id').getStore().baseParams.LpuSection_id || 
					regNick == 'ekb'
				) {
					base_form.findField('LpuSectionProfile_id').lastQuery = '';
					base_form.findField('LpuSectionProfile_id').getStore().removeAll();
					base_form.findField('LpuSectionProfile_id').getStore().baseParams.LpuSection_id = base_form.findField('LpuSection_id').getValue();
					base_form.findField('LpuSectionProfile_id').getStore().baseParams.onDate = (!Ext.isEmpty(base_form.findField('EvnVizitPL_setDate').getValue()) ? base_form.findField('EvnVizitPL_setDate').getValue().format('d.m.Y') : getGlobalOptions().date);
					base_form.findField('LpuSectionProfile_id').getStore().load({
						callback: function () {
							var index = base_form.findField('LpuSectionProfile_id').getStore().findBy(function (rec) {
								return (rec.get('LpuSectionProfile_id') == oldValue);
							});

							if (index == -1) {
								base_form.findField('LpuSectionProfile_id').clearValue();
								base_form.findField('LpuSectionProfile_id').fireEvent('change', base_form.findField('LpuSectionProfile_id'), base_form.findField('LpuSectionProfile_id').getValue());
								
								if (base_form.findField('MedStaffFact_id').getFieldValue('LpuSectionProfile_msfid') > 0 && regNick == 'ekb') {
									base_form.findField('LpuSectionProfile_id').setValue(base_form.findField('MedStaffFact_id').getFieldValue('LpuSectionProfile_msfid'));
									base_form.findField('LpuSectionProfile_id').fireEvent('change', base_form.findField('LpuSectionProfile_id'), base_form.findField('LpuSectionProfile_id').getValue());
								} 
								else if (base_form.findField('LpuSection_id').getFieldValue('LpuSectionProfile_id') > 0) {								
									base_form.findField('LpuSectionProfile_id').setValue(base_form.findField('LpuSection_id').getFieldValue('LpuSectionProfile_id'));
									base_form.findField('LpuSectionProfile_id').fireEvent('change', base_form.findField('LpuSectionProfile_id'), base_form.findField('LpuSectionProfile_id').getValue());
								} 
								else if (base_form.findField('LpuSectionProfile_id').getStore().getCount() > 0) {
									base_form.findField('LpuSectionProfile_id').setValue(base_form.findField('LpuSectionProfile_id').getStore().getAt(0).get('LpuSectionProfile_id'));
									base_form.findField('LpuSectionProfile_id').fireEvent('change', base_form.findField('LpuSectionProfile_id'), base_form.findField('LpuSectionProfile_id').getValue());
								}
							} else {
								base_form.findField('LpuSectionProfile_id').setValue(oldValue);
								base_form.findField('LpuSectionProfile_id').fireEvent('change', base_form.findField('LpuSectionProfile_id'), base_form.findField('LpuSectionProfile_id').getValue());
							}

							win.setDefaultMedicalCareKind();
						}
					});
				}
			}
		
	},
	loadMesCombo:function () {
		var win = this;
		var base_form = this.FormPanel.getForm();
		var uslugaComplex_id = base_form.findField('UslugaComplex_uid').getValue();
		base_form.findField('Mes_id').setUslugaComplex_id(uslugaComplex_id);
		if(!Ext.isEmpty(base_form.findField('EvnVizitPL_setDate').getValue()))
		{
			var evn_date = new Date(base_form.findField('EvnVizitPL_setDate').getValue());
			base_form.findField('Mes_id').setEvnDate(evn_date.format('Y-m-d'));
		}
		var mes_id = base_form.findField('Mes_id').getValue();
		base_form.findField('Mes_id').getStore().load({
			callback:function () {
				var index = base_form.findField('Mes_id').getStore().findBy(function(rec) {
							if(rec.get('MesOldVizit_id') == mes_id){
								
								return true;
							}
							else {
								return false;
							}
						});
					
				if ( index >= 0 ) {
					base_form.findField('Mes_id').setValue(mes_id);
					win.setMesInUsluga();
				}
				else {
					base_form.findField('Mes_id').clearValue();
					base_form.findField('UslugaComplex_uid').setMesOldVizit_id(null);
				}
			}
		});
		
	},
	uslugaComplexLoading: false,
	setMesInUsluga: function(){
		var win = this;
		if (getRegionNick() != 'ekb') return false;
		var base_form = win.FormPanel.getForm();
		var field = base_form.findField('UslugaComplex_uid');
		var flagMes = false;
		var fieldPayType = base_form.findField('PayType_id');
		var fieldMes = base_form.findField('Mes_id');
		if( !fieldMes.getFieldValue('MesOldVizit_Code') ){
			return false;
		}
		if( fieldMes.getFieldValue('MesOldVizit_Code').inlist([901, 902, 664])) flagMes = true;
		if( fieldMes.getFieldValue('MesOldVizit_Code').inlist([811, 812]) && fieldPayType.getFieldValue('PayType_SysNick') == 'bud') flagMes = true;
		field.setAllowBlank(flagMes);
	},
	reloadUslugaComplexField: function(needUslugaComplex_id, wantUslugaComplex_id) {

		// Переменная для региональных настроек:
		var regNick = getRegionNick();

		var win = this;
		if (win.blockUslugaComplexReload) {
			return false;
		}

		var base_form = this.FormPanel.getForm();
		if (!needUslugaComplex_id && !Ext.isEmpty(base_form.findField('UslugaComplex_uid').getValue()) && regNick == 'pskov' && parseInt(base_form.findField('EvnVizitPL_Index').getValue()) > 0) {
			// для Пскова поле задизаблено при добавлении повторного посещения, грузить его заного не нужно.
			return false;
		}

		log('reloadUslugaComplexField', needUslugaComplex_id, wantUslugaComplex_id);

		var field = base_form.findField('UslugaComplex_uid');

		if (regNick == 'vologda') {
			field.getStore().baseParams.FedMedSpec_id = base_form.findField('MedStaffFact_id').getFieldValue('FedMedSpec_id');
			field.getStore().baseParams.TreatmentClass_id = base_form.findField('TreatmentClass_id').getValue();
			field.getStore().baseParams.VizitClass_id = base_form.findField('VizitClass_id').getValue();
		}

		if (regNick == 'perm') {
			field.getStore().baseParams.VizitType_id = base_form.findField('VizitType_id').getValue();
			field.getStore().baseParams.VizitClass_id = base_form.findField('VizitClass_id').getValue();
			field.getStore().baseParams.TreatmentClass_id = base_form.findField('TreatmentClass_id').getValue();
		}

		if (regNick == 'pskov') {
			field.getStore().baseParams.LpuSectionProfile_id = base_form.findField('LpuSectionProfile_id').getValue();
			field.getStore().baseParams.MedSpecOms_id = base_form.findField('MedStaffFact_id').getFieldValue('MedSpecOms_id');
			field.getStore().baseParams.LpuSectionCode_id = base_form.findField('LpuSection_id').getFieldValue('LpuSectionCode_id');
			field.getStore().baseParams.TreatmentClass_id = base_form.findField('TreatmentClass_id').getValue();
		}

		if (regNick == 'buryatiya') {
			field.getStore().baseParams.LpuSectionProfile_id = base_form.findField('LpuSection_id').getFieldValue('LpuSectionProfile_id');
		} else if (regNick == 'perm') {
			field.getStore().baseParams.LpuSectionProfile_id = base_form.findField('LpuSectionProfile_id').getValue();
			field.getStore().baseParams.FedMedSpec_id = base_form.findField('MedStaffFact_id').getFieldValue('FedMedSpec_id');
			field.getStore().baseParams.PayType_id = base_form.findField('PayType_id').getValue();
		}

		if (regNick == 'ekb') {
			field.getStore().baseParams.MesOldVizit_id = base_form.findField('Mes_id').getValue();
			field.getStore().baseParams.MedSpecOms_id = base_form.findField('MedStaffFact_id').getFieldValue('MedSpecOms_id');
			win.setMesInUsluga();
		}

		if (regNick == 'kz') {
			field.getStore().baseParams.LpuSection_id = base_form.findField('LpuSection_id').getValue();
		}

		if (regNick.inlist(['perm','pskov'])) {
			var lastSetDate = base_form.findField('EvnVizitPL_setDate').getValue();

			if (Ext.isArray(this.OtherVizitList)) {
				this.OtherVizitList.forEach(function(vizit) {
					var setDate = getValidDT(vizit.EvnVizitPL_setDate, '00:00');
					if (setDate && setDate > lastSetDate) lastSetDate = setDate;
				});
			}
			if (Ext.isArray(this.OtherUslugaList)) {
				this.OtherUslugaList.forEach(function(usluga) {
					var setDate = getValidDT(usluga.EvnUsluga_setDate, '00:00');
					if (setDate && setDate > lastSetDate) lastSetDate = setDate;
				});
			}
			this.findById('EEPLEF_EvnUslugaGrid').getStore().each(function(record) {
				var setDate = record.get('EvnUsluga_setDate');
				if (setDate && setDate > lastSetDate) lastSetDate = setDate;
			});

			field.getStore().baseParams.UslugaComplex_Date = Ext.util.Format.date(lastSetDate, 'd.m.Y');
		}
		else if (regNick == 'vologda') {
			var lastSetDate = base_form.findField('EvnVizitPL_setDate').getValue();

			if (Ext.isArray(this.OtherVizitList)) {
				this.OtherVizitList.forEach(function(vizit) {
					var setDate = getValidDT(vizit.EvnVizitPL_setDate, '00:00');
					if (setDate && setDate > lastSetDate) lastSetDate = setDate;
				});
			}

			field.getStore().baseParams.UslugaComplex_Date = Ext.util.Format.date(lastSetDate, 'd.m.Y');
		}
		else {
			field.getStore().baseParams.UslugaComplex_Date = Ext.util.Format.date(base_form.findField('EvnVizitPL_setDate').getValue(), 'd.m.Y');
		}

		field.getStore().baseParams.EvnVizit_id = base_form.findField('EvnVizitPL_id').getValue();
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
					if (index < 0 && regNick == 'pskov' && base_form.findField('UslugaComplex_uid').getStore().getCount() == 1) {
						index = 0;
					}

					if (index >= 0) {
						var record = base_form.findField('UslugaComplex_uid').getStore().getAt(index);
						field.setValue(record.get('UslugaComplex_id'));
						field.setRawValue(record.get('UslugaComplex_Code') + '. ' + record.get('UslugaComplex_Name'));
						if (regNick == 'ekb') {
							base_form.findField('Mes_id').setUslugaComplex_id(currentUslugaComplex_id);
						}

						//записываем т.к. может быть недоступно после перезагрузки значений в справочник
						if (base_form.findField('UslugaComplex_uid').getFieldValue('UslugaComplex_AgeGroupId')) {
							win.UslugaComplex_uid_AgeGroupId = base_form.findField('UslugaComplex_uid').getFieldValue('UslugaComplex_AgeGroupId');
						}
					} else {
						field.clearValue();
						if (regNick == 'ekb') {
							base_form.findField('Mes_id').setUslugaComplex_id(null);
						}
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
				if (regNick == 'ekb') {
					base_form.findField('Mes_id').setUslugaComplex_id(UslugaComplex_id);
				}
			} else {
				field.clearValue();
				if (regNick == 'ekb') {
					base_form.findField('Mes_id').setUslugaComplex_id(null);
				}
			}
		}
	},
	checkUslugaComplexUidAllowBlank: function() {
		if (getRegionNick().inlist([ 'perm', 'ufa', 'buryatiya', 'kz', 'pskov', 'ekb', 'vologda' ])) {
			var base_form = this.FormPanel.getForm();
			var xdate = new Date(2014, 11, 1);
			base_form.findField('UslugaComplex_uid').setAllowBlank(true);
			if ( !Ext.isEmpty(base_form.findField('EvnVizitPL_setDate').getValue()) 
				&& base_form.findField('EvnVizitPL_setDate').getValue() >= xdate 
				&& base_form.findField('PayType_id').getFieldValue('PayType_SysNick') == 'oms'
				&& this.action.inlist([ 'addEvnPL', 'addEvnVizitPL', 'editEvnVizitPL','copyEvnVizitPL' ])
			) {
				base_form.findField('UslugaComplex_uid').setAllowBlank(false);
			}
		}
	},
	initComponent: function() {
		var wnd = this;

		// Переменные для региональных настроек:
		var regNick = getRegionNick(),
			regNum = getRegionNumber();

		this.LpuSectionProfileDopPanel = {
			border: false,
			hidden: false,
			layout: 'form',
			items: [{
				allowBlank: regNick.inlist(['ufa', 'ekb']),
				disabled: regNick.inlist(['ufa', 'ekb']),
				fieldLabel: lang['profil'],
				listeners: {
					'change': function(combo, newValue, oldValue) {
						if (regNick.inlist(['perm','pskov'])) {
							wnd.reloadUslugaComplexField();
						}
						var base_form = wnd.FormPanel.getForm();
						var code = combo.getFieldValue('LpuSectionProfile_Code');
						var index;

						if (regNick == 'pskov' && wnd.action != 'view') {
							if (code == '160' && (Ext.isEmpty(base_form.findField('Diag_id').getValue()) || base_form.findField('Diag_id').getFieldValue('Diag_Code').substr(0, 1) != 'Z')) {
								index = base_form.findField('TreatmentClass_id').getStore().findBy(function(rec) {
									return (rec.get('TreatmentClass_Code') == '1.1');
								});

								if ( index >= 0 ) {
									base_form.findField('TreatmentClass_id').setFieldValue('TreatmentClass_Code', '1.1');
									base_form.findField('TreatmentClass_id').disable();
									base_form.findField('TreatmentClass_id').fireEvent('change', base_form.findField('TreatmentClass_id'), base_form.findField('TreatmentClass_id').getValue());
								}
							} else {
								base_form.findField('TreatmentClass_id').enable();
							}
						}

						if (regNick == 'vologda' && oldValue && newValue && wnd.checkVisitProfiles()) {
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

						wnd.getFinanceSource();
					}
				},
				hiddenName: 'LpuSectionProfile_id',
				listWidth: 600,
				tabIndex: TABINDEX_EEPLEF + 27,
				width: 450,
				xtype: 'swlpusectionprofiledopremotecombo'
			}]
		}

		this.panelEvnDirectionAll = new sw.Promed.EvnDirectionAllPanel({
			prefix: 'EEPLEF',
			startTabIndex: TABINDEX_EEPLEF + 4,
			useCase: 'choose_for_evnpl',
			showMedStaffFactCombo: true,
			personPanelId: 'EEPLEF_PersonInformationFrame',
			personFieldName: 'Person_id',
			medStaffFactFieldName: 'MedStaffFact_id',
			fromLpuFieldName: 'Lpu_fid',
			fieldIsWithDirectionName: 'EvnPL_IsWithoutDirection',
			buttonSelectId: 'EEPLEF_EvnDirectionSelectButton',
			fieldPrehospDirectName: 'PrehospDirect_id',
			fieldLpuSectionName: 'LpuSection_did',
			fieldMedStaffFactName: 'MedStaffFact_did',
			fieldOrgName: 'Org_did',
			fieldNumName: 'EvnDirection_Num',
			fieldSetDateName: 'EvnDirection_setDate',
			fieldDiagName: 'Diag_did',
			fieldDiagPreidName: 'Diag_preid',
			fieldDiagFName: 'Diag_fid',
			//fieldTimaTableName: 'TimetableGraf_id',
			//fieldEvnPrescrName: 'EvnPrescr_id',
			fieldIdName: 'EvnDirection_id',//EvnDirection_vid
			fieldIsAutoName: 'EvnDirection_IsAuto',
			fieldIsExtName: 'EvnDirection_IsReceive',
			parentSetDateFieldName: 'EvnVizitPL_setDate',
			nextFieldName: 'PrehospTrauma_id'
		});

		this.DrugTherapySchemeBodyPanel = new Ext.Panel({
			layout: 'form',
			autoHeight: true,
			border: false,
			items: []
		});

		this.DrugTherapySchemePanel = new Ext.Panel({
			border: false,
			lastNum: -1,
			count: 0,
			limit: getRegionNick().inlist(['kareliya', 'ufa', 'astra', 'khak', 'perm']) ? null : 1,
			baseFilter: null,
			setBaseFilter: function (filterFn) {
				var base_form = this.FormPanel.getForm();
				var container = this.DrugTherapySchemePanel;
				container.baseFilter = filterFn;

				for (var num = 0; num <= container.lastNum; num++) {
					var field = base_form.findField('DrugTherapyScheme_id_' + num);
					if (field) field.setBaseFilter(container.baseFilter);
				}
			}.createDelegate(this),
			getIds: function () {
				var base_form = this.FormPanel.getForm();
				var container = this.DrugTherapySchemePanel;
				var ids = [];

				for (var num = 0; num <= container.lastNum; num++) {
					var field = base_form.findField('DrugTherapyScheme_id_' + num);
					if (field && !Ext.isEmpty(field.getValue())) {
						ids.push(field.getValue());
					}
				}

				return ids.join(',');
			}.createDelegate(this),
			setIds: function (ids) {
				var container = this.DrugTherapySchemePanel;

				container.resetFieldSets();

				var ids_arr = ids.split(',');
				for (var i = 0; i < ids_arr.length; i++) {
					container.addFieldSet({value: ids_arr[i]});
				}
			}.createDelegate(this),
			checkLimit: function (checkCount) {
				var container = this.DrugTherapySchemePanel;
				var add_button = this.findById(wnd.id + '_ButtonDrugTherapySchemePanel');

				if (Ext.isEmpty(container.limit)) {
					add_button.show();
					return true;
				}

				add_button.setVisible(container.limit > container.count);

				return (container.limit >= container.count);
			}.createDelegate(this),
			resetFieldSets: function () {
				var container = this.DrugTherapySchemePanel;
				for (var num = 0; num <= container.lastNum; num++) {
					container.deleteFieldSet(num);
				}
				container.count = 0;
				container.lastNum = -1;
			}.createDelegate(this),
			deleteFieldSet: function (num) {
				var base_form = this.FormPanel.getForm(); 
				var container = this.DrugTherapySchemePanel;
				var panel = this.DrugTherapySchemeBodyPanel;

				if (panel.findById('DrugTherapySchemeFieldSet_' + num)) {
					var field = base_form.findField('DrugTherapyScheme_id_' + num);
					base_form.items.removeKey(field.id);

					panel.remove('DrugTherapySchemeFieldSet_' + num);
					this.doLayout();
					this.syncShadow();
					this.FormPanel.initFields();

					container.count--;
					container.checkLimit();
				}
			}.createDelegate(this),
			addFieldSet: function (options) {
				var base_form = this.FormPanel.getForm();
				var container = this.DrugTherapySchemePanel;
				var panel = this.DrugTherapySchemeBodyPanel;

				container.count++;
				container.lastNum++;
				var num = container.lastNum;

				if (!container.checkLimit()) {
					container.count--;
					container.lastNum--;
					return;
				}

				var delButton = new Ext.Button({
					iconCls: 'delete16',
					text: langs('Удалить'),
					handler: function () {
						container.deleteFieldSet(num);
					}
				});

				var config = {
					layout: 'column',
					id: 'DrugTherapySchemeFieldSet_' + num,
					border: false,
					cls: 'AccessRigthsFieldSet',
					items: [{
						layout: 'form',
						border: false,
						labelWidth: 220,
						items: [{
							editable: true,
							xtype: 'swcommonsprcombo',
							ctxSerach: true,
							comboSubject: 'DrugTherapyScheme',
							codeAlthoughNotEditable: true,
							fieldLabel: 'Схема лекарственной терапии',
							hiddenName: 'DrugTherapyScheme_id_' + num,
							listWidth: 1620,
							width: 450,
							listeners: {
								'change': function(combo, newValue, oldValue) {
									if(combo.qtip) {
										combo.qtip.text = combo.getFieldValue('DrugTherapyScheme_Name');
										if(newValue && !oldValue) Ext.QuickTips.register(combo.qtip);
										else if(!newValue && oldValue) Ext.QuickTips.unregister(combo.getEl());
									}
								}
							}
						}]
					}, {
						layout: 'form',
						border: false,
						items: [delButton]
					}]
				};

				panel.add(config);
				this.doLayout();
				this.syncSize();
				this.FormPanel.initFields();

				var field = base_form.findField('DrugTherapyScheme_id_' + num);

				if (field) {
					field.setBaseFilter(container.baseFilter);
					field.qtip = new Ext.QuickTip({
						target: field.getEl(),
						text: '',
						enabled: true,
						showDelay: 60,
						trackMouse: true,
						autoShow: true
					});
					field.getStore().load({
						callback: function() {
							if(field.qtip && !Ext.isEmpty(field.getValue())) {
								field.qtip.text = field.getFieldValue('DrugTherapyScheme_Name');
							}
						}
					});
					if (options && options.value) {
						field.setValue(options.value);
						field.qtip.text = field.getFieldValue('DrugTherapyScheme_Name');
						Ext.QuickTips.register(field.qtip);
					}
				}
			}.createDelegate(this),
			items: [wnd.DrugTherapySchemeBodyPanel, {
				layout: 'column',
				id: wnd.id + '_ButtonDrugTherapySchemePanel',
				cls: 'AccessRigthsFieldSet',
				height: 25,
				style: 'margin-left: 222px;',
				border: false,
				items: [{
					layout: 'form',
					border: false,
					items: [{
						xtype: 'button',
						iconCls: 'add16',
						text: langs('Добавить схему лекарственной терапии'),
						handler: function () {
							this.DrugTherapySchemePanel.addFieldSet();
						}.createDelegate(this)
					}]
				}]
			}]
		});

		this.FormPanel = new Ext.form.FormPanel({
			autoScroll: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			border: false,
			frame: false,
			id: 'EmkEvnPLEditForm',
			labelAlign: 'right',
			labelWidth: 220,
			items: [{
				name: 'accessType',
				value: '',
				xtype: 'hidden'
			}, {
				name: 'EvnVizitPL_IsPaid',
				xtype: 'hidden'
			}, {
				name: 'EvnXml_id',
				xtype: 'hidden'
			}, {
				name: 'EvnPL_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'EvnPL_lid',
				xtype: 'hidden'
			}, {
				name: 'EvnDirection_id',
				xtype: 'hidden'
			}, {
				name: 'LastEvnVizitPL_setDate',
				xtype: 'hidden'
			}, {
				name: 'LastEvnVizitPL_Diag_id',
				xtype: 'hidden'
			}, {
				name: 'LastEvnVizitPL_Diag_Code',
				xtype: 'hidden'
			}, {
				name: 'EvnDirection_vid',
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
				name: 'EvnUslugaCommon_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'UslugaComplex_Code',
				xtype: 'hidden'
			}, {
				name: 'EvnVizitPL_Count',
				xtype: 'hidden'
			}, {
				name: 'EvnVizitPL_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'EvnVizitPL_Index',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'EvnVizitPL_Uet',
				value: '',
				xtype: 'hidden'
			}, {
				name: 'EvnVizitPL_UetOMS',
				value: '',
				xtype: 'hidden'
			}, {
				name: 'LpuBuilding_id',
				value: null,
				xtype: 'hidden'
			}, {
				name: 'LpuUnit_id',
				value: null,
				xtype: 'hidden'
			}, {
                name: 'Lpu_id',
                xtype: 'hidden'
            }, {
				name: 'LpuUnitSet_id',
				value: null,
				xtype: 'hidden'
			}, {
				name: 'MedPersonal_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'MedPersonal_sid',
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
            }, {
                name: 'TimetableGraf_id',
                value: 0,
                xtype: 'hidden'
            }, {
                name: 'EvnPrescr_id',
                value: 0,
                xtype: 'hidden'
			}, {
				name: 'EvnVizitPL_IsZNO',
				xtype: 'hidden'
			}, {
				name: 'EvnVizitPL_IsZNORemove',
				xtype: 'hidden'
			},
			new sw.Promed.Panel({
				autoHeight: true,
				bodyStyle: 'padding-top: 0.5em;',
				border: true,
				collapsible: false,
				id: 'EEPLEF_DirectInfoPanel',
				layout: 'form',
				style: 'margin-bottom: 0.5em;',
				title: lang['vhodnyie_dannyie'],
				items: [{
					border: false,
					layout: 'column',
					items: [{
						border: false,
						layout: 'form',
						items: [{
							enableKeyEvents: true,
							fieldLabel: lang['№_talona'],
							listeners: {
								'keydown': function(inp, e) {
									switch ( e.getKey() ) {
										case Ext.EventObject.F2:
										case Ext.EventObject.F4:
											e.stopEvent();
											this.getEvnPLNumber();
										break;

										case Ext.EventObject.TAB:
											if ( e.shiftKey == true ) {
												e.stopEvent();
												this.buttons[this.buttons.length - 1].focus();
											}
										break;
									}
								}.createDelegate(this)
							},
							autoCreate: { tag: "input", type: "text", maxLength: "30", autocomplete: "off" },
							name: 'EvnPL_NumCard',
							onTriggerClick: function() {
								this.getEvnPLNumber();
							}.createDelegate(this),
							tabIndex: TABINDEX_EEPLEF + 1,
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
							tabIndex: TABINDEX_EEPLEF + 2,
							xtype: 'checkbox'
						}]
					}]
				}, this.panelEvnDirectionAll, {
					border: false,
					hidden: regNick != 'kareliya',
					layout: 'form',
					xtype: 'panel',
					items: [{
						hiddenName: 'MedicalCareKind_id',
						allowBlank: regNick != 'kareliya',
						fieldLabel: lang['meditsinskaya_pomosch'],
						comboSubject: 'MedicalCareKind',
						tabIndex: TABINDEX_EEPLEF + 17,
						xtype: 'swcommonsprcombo',
						width: 300
					}]
				}]
			}),
			new sw.Promed.Panel({
				autoHeight: true,
				bodyStyle: 'padding-top: 0.5em;',
				border: true,
				collapsible: false,
				id: 'EEPLEF_EvnVizitPLPanel',
				layout: 'form',
				listeners: {
					'expand': function(panel) {
						// this.FormPanel.getForm().findField('PrehospDirect_id').focus(true);
					}.createDelegate(this)
				},
				style: 'margin-bottom: 0.5em;',
				title: lang['informatsiya_o_poseschenii_vracha'],
				items: [{
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
									this.setInterruptLeaveTypeVisible();

									if ( blockedDateAfterPersonDeath('personpanelid', 'EEPLEF_PersonInformationFrame', field, newValue, oldValue) ) {
										return;
									}

									var base_form = this.FormPanel.getForm();

									var xdate = new Date(2016, 0, 1); // Поле видимо (если дата посещения 01-01-2016 или позже)
									var mdate = new Date(Math.max(getValidDT(this.lastEvnVizitPLData, ''), base_form.findField('EvnVizitPL_setDate').getValue()));
									if (mdate >= xdate) {
										base_form.findField('TreatmentClass_id').showContainer();
										base_form.findField('TreatmentClass_id').setAllowBlank(false);
										base_form.findField('TreatmentClass_id').onLoadStore();
										if (regNick != 'kz') { // для Казахстана поле не нужно
											base_form.findField('MedicalCareKind_vid').showContainer();
											base_form.findField('MedicalCareKind_vid').setAllowBlank(false);
										} else {
											base_form.findField('MedicalCareKind_vid').hideContainer();
											base_form.findField('MedicalCareKind_vid').clearValue();
											base_form.findField('MedicalCareKind_vid').setAllowBlank(true);
										}
										this.setDefaultMedicalCareKind();
									} else {
										base_form.findField('TreatmentClass_id').hideContainer();
										base_form.findField('TreatmentClass_id').clearValue();
										base_form.findField('TreatmentClass_id').setAllowBlank(true);
										base_form.findField('MedicalCareKind_vid').hideContainer();
										base_form.findField('MedicalCareKind_vid').clearValue();
										base_form.findField('MedicalCareKind_vid').setAllowBlank(true);
									}
									
									if (regNick == 'kareliya') {
										base_form.findField('TreatmentClass_id').hideContainer();									
									}

									if (regNick == 'ekb' && base_form.findField('RankinScale_id').isVisible()) {
										var mydate = base_form.findField('EvnVizitPL_setDate').getValue();
										if(mydate >= xdate){
											base_form.findField('RankinScale_id').setAllowBlank(true);
										} else {
											base_form.findField('RankinScale_id').setAllowBlank(false);
										}
									}

									var lpu_section_combo = base_form.findField('LpuSection_id'),
										medstafffact_combo = base_form.findField('MedStaffFact_id'),
										usluga_complex_combo = base_form.findField('UslugaComplex_uid');

									if ( sw.Promed.EvnVizitPL.isSupportVizitCode() ) {
										// Устанавливаем дату для кодов посещений
										usluga_complex_combo.setUslugaComplexDate(Ext.util.Format.date(newValue, 'd.m.Y'));
									}
									var index;
									var lpu_section_id = lpu_section_combo.getValue();
									var med_personal_id = base_form.findField('MedPersonal_id').getValue();
									var med_staff_fact_id = medstafffact_combo.getValue();
									var med_staff_fact_sid = base_form.findField('MedStaffFact_sid').getValue();
									var ServiceType_id = base_form.findField('ServiceType_id').getValue();

									wnd.checkUslugaComplexUidAllowBlank();
									wnd.setRiskLevelComboState();
									wnd.setWellnessCenterAgeGroupsComboState();
									wnd.refreshFieldsVisibility(['TumorStage_id', 'PainIntensity_id', 'PregnancyEvnVizitPL_Period']);

									base_form.findField('ServiceType_id').lastQuery = '';
									base_form.findField('ServiceType_id').clearValue();
									base_form.findField('ServiceType_id').getStore().clearFilter();
									
									if ( !Ext.isEmpty(newValue) ) {
										base_form.findField('ServiceType_id').getStore().filterBy(function(rec) {
											
											return (
												(Ext.isEmpty(rec.get('ServiceType_begDate')) || rec.get('ServiceType_begDate') <= newValue)
												&& (Ext.isEmpty(rec.get('ServiceType_endDate')) || rec.get('ServiceType_endDate') > newValue)
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

									base_form.findField('Diag_id').setFilterByDate(newValue);

									this.filterVizitTypeCombo();

									if (regNick.inlist(['ufa'])) {
										// Дернуть код профиля и установить фильтр
										var index = lpu_section_combo.getStore().findBy(function(rec) {
											if ( rec.get('LpuSection_id') == lpu_section_id ) {
												return true;
											}
											else {
												return false;
											}
										});
										var record = lpu_section_combo.getStore().getAt(index);
										if ( record ) {
											usluga_complex_combo.setLpuLevelCode(record.get('LpuSectionProfile_Code'));
										}
									}

									base_form.findField('LpuSection_id').clearValue();
									medstafffact_combo.clearValue();
									medstafffact_combo.fireEvent('change', medstafffact_combo, medstafffact_combo.getValue());
									base_form.findField('MedStaffFact_sid').clearValue();

									var person_age = swGetPersonAge(this.PersonInfo.getFieldValue('Person_Birthday'), base_form.findField('EvnVizitPL_setDate').getValue());
									var WithoutChildLpuSectionAge=false;
									if (person_age >= 18 && !this.hasPreviusChildVizit() && !regNick.inlist(['ufa','astra','pskov'])) {
										WithoutChildLpuSectionAge = true;
									}

									var section_filter_params = {
										allowLowLevel: 'yes',
										isPolka: true,
										regionCode: regNum,
										WithoutChildLpuSectionAge: WithoutChildLpuSectionAge
									};

									// https://redmine.swan.perm.ru/issues/19471
									if (regNick == 'ufa') {
										section_filter_params.LpuUnitSet_id = base_form.findField('LpuUnitSet_id').getValue();
									}

									var medstafffact_filter_params = {
										allowLowLevel: 'yes',
										EvnClass_SysNick: 'EvnVizit',
										isPolka: true,
										regionCode: regNum,
										WithoutChildLpuSectionAge: WithoutChildLpuSectionAge
									};

									var mid_medstafffact_filter_params = {
										allowLowLevel: 'yes',
										isMidMedPersonalOnly: true,
										isPolka: true,
										regionCode: regNum,
										WithoutChildLpuSectionAge: WithoutChildLpuSectionAge
									};

									if ( newValue ) {
										section_filter_params.onDate = Ext.util.Format.date(newValue, 'd.m.Y');
										medstafffact_filter_params.onDate = Ext.util.Format.date(newValue, 'd.m.Y');
										mid_medstafffact_filter_params.onDate = Ext.util.Format.date(newValue, 'd.m.Y');
										wnd.setMKB();
									}

									if ( !this.action.inlist([ 'viewEvnPL', 'viewEvnVizitPL' ]) ) {
										base_form.findField('LpuSection_id').enable();
										medstafffact_combo.enable();
										base_form.findField('MedStaffFact_sid').enable();
									}

									base_form.findField('LpuSection_id').getStore().removeAll();
									medstafffact_combo.getStore().removeAll();
									base_form.findField('MedStaffFact_sid').getStore().removeAll();

									// сначала фильтруем средний медперсонал, 
									// потому что для него не нужен фильтр по месту работы текущего пользователя
									setMedStaffFactGlobalStoreFilter(mid_medstafffact_filter_params);

									base_form.findField('MedStaffFact_sid').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));

									if ( this.action.inlist([ 'addEvnPL', 'addEvnVizitPL', 'editEvnVizitPL' ]) ) {
										// фильтр или на конкретное место работы или на список мест работы
										if ( this.userMedStaffFact ) {
											lpu_section_id = lpu_section_id || this.userMedStaffFact.LpuSection_id;
											med_personal_id = med_personal_id || this.userMedStaffFact.MedPersonal_id;
											med_staff_fact_id = med_staff_fact_id || this.userMedStaffFact.MedStaffFact_id;
											section_filter_params.id = this.userMedStaffFact.LpuSection_id;
											medstafffact_filter_params.medPersonalIdList = [med_personal_id];
											medstafffact_filter_params.allowDuplacateMSF = true;
										} else if ( typeof this.UserLpuSectionList == 'object' && this.UserLpuSectionList.length > 0 && typeof this.UserMedStaffFactList == 'object' && this.UserMedStaffFactList.length > 0 ) {
											//section_filter_params.ids = this.UserLpuSectionList;
											medstafffact_filter_params.ids = this.UserMedStaffFactList;
											medstafffact_filter_params.allowDuplacateMSF = true;
										}
									}

									// https://redmine.swan.perm.ru/issues/19471
									if (regNick == 'ufa') {
										medstafffact_filter_params.LpuUnitSet_id = base_form.findField('LpuUnitSet_id').getValue(); // из одного подразделения ТФОМС
									}

									if (regNick == 'ekb' && !Ext.isEmpty(base_form.findField('EvnPL_lid').getValue())) {
										// надо разрешить выбор не только врачей из поликлиники, т.к. врач из КВС должен выбраться.
										section_filter_params.isPolka = false;
										medstafffact_filter_params.isPolka = false;
										base_form.findField('LpuSection_id').disable(); // смена отделения не доступна
									}
									log('section_filter_params', section_filter_params);
									setLpuSectionGlobalStoreFilter(section_filter_params);

									setMedStaffFactGlobalStoreFilter(medstafffact_filter_params);

									base_form.findField('LpuSection_id').getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));
									medstafffact_combo.getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));

									if ( base_form.findField('LpuSection_id').getStore().getById(lpu_section_id) ) {
										base_form.findField('LpuSection_id').setValue(lpu_section_id);
										base_form.findField('LpuSection_id').fireEvent('change', base_form.findField('LpuSection_id'), lpu_section_id);
									}

									/*if ( medstafffact_combo.getStore().getById(med_staff_fact_id) ) {
										medstafffact_combo.setValue(med_staff_fact_id);
									}*/
									//костыль для нескольких рабочих мест в одном отделении
									
									index = medstafffact_combo.getStore().findBy(function(rec) {
                                        return (
                                            (med_staff_fact_id && rec.get('MedStaffFact_id') == med_staff_fact_id)
                                                || ( !med_staff_fact_id && rec.get('MedPersonal_id') == med_personal_id && rec.get('LpuSection_id') == lpu_section_id )
                                            );
									});

									if ( index >= 0 ) {
										medstafffact_combo.setValue(medstafffact_combo.getStore().getAt(index).get('MedStaffFact_id'));
										medstafffact_combo.fireEvent('change', medstafffact_combo, medstafffact_combo.getValue());
									}

									if ( base_form.findField('MedStaffFact_sid').getStore().getById(med_staff_fact_sid) ) {
										base_form.findField('MedStaffFact_sid').setValue(med_staff_fact_sid);
									}

									/*
										если форма открыта на редактирование и задано отделение и 
										место работы или задан список мест работы, то не даем редактировать вообще
									*/
									if ( this.action.inlist([ 'editEvnVizitPL' ]) && ((this.userMedStaffFact) || (typeof this.UserLpuSectionList == 'object' && this.UserLpuSectionList.length > 0 && typeof this.UserMedStaffFactList == 'object' && this.UserMedStaffFactList.length > 0)) ) {
										base_form.findField('LpuSection_id').disable();
										//medstafffact_combo.disable();
									}

									// Если форма открыта на добавление...
									if ( this.action.inlist([ 'addEvnPL', 'addEvnVizitPL' ]) ) {
										// ... и задано отделение и место работы...
										if ( this.userMedStaffFact ) {
											// ... то устанавливаем их и не даем редактировать поля
											base_form.findField('LpuSection_id').disable();
											//medstafffact_combo.disable();

											base_form.findField('LpuSection_id').setValue(this.userMedStaffFact.LpuSection_id);
											base_form.findField('LpuSection_id').fireEvent('change', base_form.findField('LpuSection_id'), this.userMedStaffFact.LpuSection_id);
											//medstafffact_combo.setValue(this.userMedStaffFact.MedStaffFact_id);
											//костыль для нескольких рабочих мест в одном отделении
											index = medstafffact_combo.getStore().findBy(function(rec) {
                                                return (
                                                    (med_staff_fact_id && rec.get('MedStaffFact_id') == med_staff_fact_id)
                                                        || ( !med_staff_fact_id && rec.get('MedPersonal_id') == med_personal_id && rec.get('LpuSection_id') == lpu_section_id )
                                                    );
											});

											if ( index >= 0 ) {
												medstafffact_combo.setValue(medstafffact_combo.getStore().getAt(index).get('MedStaffFact_id'));
												medstafffact_combo.fireEvent('change', medstafffact_combo, medstafffact_combo.getValue());
											}
										}
										// или задан список отделений и мест работы...
										else if ( medstafffact_combo.getStore().getCount() > 0 && typeof this.UserLpuSectionList == 'object' && this.UserLpuSectionList.length > 0 && typeof this.UserMedStaffFactList == 'object' && this.UserMedStaffFactList.length > 0 ) {
											// ... выбираем первое место работы
											med_staff_fact_id = medstafffact_combo.getStore().getAt(0).get('MedStaffFact_id');

											medstafffact_combo.setValue(med_staff_fact_id);
											medstafffact_combo.fireEvent('change', medstafffact_combo, medstafffact_combo.getValue());

											// Если в списке мест работы всего одна запись...
											if ( this.UserMedStaffFactList.length == 1 ) {
												// ... закрываем поля для редактирования
												base_form.findField('LpuSection_id').disable();
												medstafffact_combo.disable();
											}
										}
									}

									if ( !regNick.inlist(['kareliya','ufa']) ) {
										var diag_id = base_form.findField('Diag_id').getValue();
										if (!Ext.isEmpty(diag_id) && this.isLastVizit()) {
											base_form.findField('Diag_lid').getStore().load({
												callback: function() {
													base_form.findField('Diag_lid').setValue(diag_id);
													this.setDiagConcComboVisible();
												}.createDelegate(this),
												params: {where: "where Diag_id = " + diag_id}
											});
										}
									}

									base_form.findField('PersonDisp_id').lastQuery = 'This query sample that is not will never appear';
									base_form.findField('PersonDisp_id').getStore().removeAll();
									base_form.findField('PersonDisp_id').clearValue();
									base_form.findField('PersonDisp_id').getStore().baseParams.onDate = Ext.util.Format.date(newValue, 'd.m.Y');

									var vizitDate = base_form.findField('EvnVizitPL_setDate').getValue();
									base_form.findField('DeseaseType_id').getStore().clearFilter();
									base_form.findField('DeseaseType_id').getStore().filterBy(function(rec) {
										return (
											(!rec.get('DeseaseType_begDT') || rec.get('DeseaseType_begDT') <= vizitDate)
											&& (!rec.get('DeseaseType_endDT') || rec.get('DeseaseType_endDT') >= vizitDate)
										)
									});
									base_form.findField('DeseaseType_id').lastQuery = '';
									
									this.reloadUslugaComplexField();

									if (!wnd.fo) {
										wnd.calcFedResultDeseaseType();
										wnd.calcFedLeaveType();
									}
									sw.Promed.EvnPL.filterFedResultDeseaseType({
										fieldFedLeaveType: base_form.findField('LeaveType_fedid'),
										fieldFedResultDeseaseType: base_form.findField('ResultDeseaseType_fedid')
									});
									sw.Promed.EvnPL.filterFedLeaveType({
										fieldFedLeaveType: base_form.findField('LeaveType_fedid'),
										fieldFedResultDeseaseType: base_form.findField('ResultDeseaseType_fedid')
									});
								}.createDelegate(this),
								'keydown': function(inp, e) {
									switch ( e.getKey() ) {
										case Ext.EventObject.TAB:
											if ( e.shiftKey == true && (this.findById('EEPLEF_DirectInfoPanel').hidden || this.findById('EEPLEF_DirectInfoPanel').collapsed) ) {
												e.stopEvent();
												this.buttons[this.buttons.length - 1].focus();
											}
										break;
									}
								}.createDelegate(this)
							},
							name: 'EvnVizitPL_setDate',
							plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
							selectOnFocus: true,
							tabIndex: TABINDEX_EEPLEF + 18,
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
							name: 'EvnVizitPL_setTime',
							onTriggerClick: function() {
								var base_form = this.FormPanel.getForm();
								var time_field = base_form.findField('EvnVizitPL_setTime');

								if ( time_field.disabled ) {
									return false;
								}

								setCurrentDateTime({
									callback: function() {
										base_form.findField('EvnVizitPL_setDate').fireEvent('change', base_form.findField('EvnVizitPL_setDate'), base_form.findField('EvnVizitPL_setDate').getValue());
									},
									dateField: base_form.findField('EvnVizitPL_setDate'),
									loadMask: true,
									setDate: true,
									setDateMaxValue: true,
									setDateMinValue: false,
									setTime: true,
									timeField: time_field,
									windowId: this.id
								});
								return true;
							}.createDelegate(this),
							plugins: [ new Ext.ux.InputTextMask('99:99', true) ],
							tabIndex: TABINDEX_EEPLEF + 19,
							validateOnBlur: false,
							width: 60,
							xtype: 'swtimefield'
						}]
					}, {
						border: false,
						labelWidth: 130,
						layout: 'form',
						items: [{
							comboSubject: 'VizitClass',
							fieldLabel: lang['pervichno_povtorno'],
							listeners: {
								'change': function(combo, newValue, oldValue) {
									wnd.reloadUslugaComplexField();
								}
							},
							hiddenName: 'VizitClass_id',
							tabIndex: TABINDEX_EEPLEF + 20,
							width: 100,
							xtype: 'swcommonsprcombo'
						}]
					}]
				}, { // Night, поле специальности врача
					border: false,
					xtype: 'panel',
					layout: 'form',
					hidden: true,
					id: 'EEPLEF_DispWowSpecComboSet',
					items: [{
						allowBlank: true,
						hiddenName: 'DispWowSpec_id',
						id: 'EEPLEF_DispWowSpecCombo',
						listWidth: 650,
						tabIndex: TABINDEX_EEPLEF + 21,
						width: 433,
						xtype: 'swdispwowspeccombo',
						listeners:
						{
							change:
							function(field, newValue, oldValue)
							{
								var far = new Array();
								far.length = 0;
								switch (newValue.toString())
								{
									case '1':
										far.push('1000', '1003', '1007', '1009', '1010');
										break;
									case '2':
										far.push('2300');
										break;
									case '3':
										far.push('2800', '2801', '2805');
										break;
									case '4':
										far.push('2600', '2601', '2610', '2620');
										break;
									case '5':
										far.push('2700');
										break;
									case '6':
										far.push('0510', '0511', '0520');
										break;
									case '7':
										far.push('2509', '2510', '2517', '2518');
										break;
									case '8':
										far.push('1500');
										break;
									case '9':
										far.push('1450');
										break;
									case '10':
										far.push('0');
										break;
									default:
										far.push('1000', '1003', '1007', '1009', '1010', '2300','2800', '2801', '2805','2600', '2601', '2610', '2620','2700','0510', '0511', '0520','2509', '2510', '2517', '2518', '1500', '1450');
										break;
								}
								Ext.getCmp('EmkEvnPLEditWindow').setFilterProfile(field, far, newValue);
							}
						}
					}]
				}, {
					hiddenName: 'LpuSection_id',
					id: 'EEPLEF_LpuSectionCombo',
					lastQuery: '',
					listWidth: 650,
					linkedElements: [
						'EEPLEF_MedPersonalCombo'
					],
					tabIndex: TABINDEX_EEPLEF + 22,
					width: 450,
					xtype: 'swlpusectionglobalcombo'
				}, {
					hiddenName: 'MedStaffFact_id',
					id: 'EEPLEF_MedPersonalCombo',
					lastQuery: '',
					listWidth: 650,
					parentElementId: 'EEPLEF_LpuSectionCombo',
					tabIndex: TABINDEX_EEPLEF + 23,
					width: 450,
					xtype: 'swmedstafffactglobalcombo'
				}, {
					fieldLabel: lang['sred_m_personal'],
					hiddenName: 'MedStaffFact_sid',
					id: 'EEPLEF_MidMedPersonalCombo',
					listWidth: 650,
					tabIndex: TABINDEX_EEPLEF + 24,
					width: 450,
					xtype: 'swmedstafffactglobalcombo'
				}, {
					allowBlank: false,
					fieldLabel: regNick == 'kz' ? 'Повод обращения' : 'Вид обращения',
					hidden: regNick == 'kareliya',
					hiddenName: 'TreatmentClass_id',
					comboSubject: 'TreatmentClass',
					xtype: 'swcommonsprcombo',
					tabIndex: TABINDEX_EEPLEF + 25,
					width: 300,
					onLoadStore: function() {
						this.getStore().clearFilter();
						this.lastQuery = '';
						var base_form = wnd.FormPanel.getForm();
						if (!Ext.isEmpty(base_form.findField('Diag_id').getFieldValue('Diag_Code'))) {
							if (regNick == 'kz') {
								return false;
							}
							if (regNick == 'kareliya') {
								wnd.setTreatmentClass();
								return false;
							}
							this.getStore().filterBy(function(rec) {
								if (base_form.findField('Diag_id').getFieldValue('Diag_Code') == 'Z51.5') {
									return (rec.get('TreatmentClass_id').inlist([ 9 ]));
								} else if (base_form.findField('Diag_id').getFieldValue('Diag_Code').substr(0,1) == 'Z' || (regNick == 'perm' && base_form.findField('Diag_id').getFieldValue('Diag_Code').substr(0,3) == 'W57')) {
									return (rec.get('TreatmentClass_id').inlist([ 6, 7, 8, 9, 10, 11, 12 ]));
								} else if ( regNick == 'penza' ) {
									return (rec.get('TreatmentClass_id').inlist([ 1, 2, 3, 4, 11, 13 ]));
								} else {
									return (rec.get('TreatmentClass_id').inlist([ 1, 2, 3, 4, 13 ]));
								}
							});							
							var index = this.getStore().findBy(function(rec) {
								return (rec.get('TreatmentClass_id') == base_form.findField('TreatmentClass_id').getValue());
							});
							if (index == -1) {
								base_form.findField('TreatmentClass_id').clearValue();
							}

							if ( regNick == 'pskov' && !Ext.isEmpty(base_form.findField('LpuSectionProfile_id').getValue()) ) {
								base_form.findField('LpuSectionProfile_id').fireEvent('change', base_form.findField('LpuSectionProfile_id'), base_form.findField('LpuSectionProfile_id').getValue());
							}
						}
						else {
							this.getStore().filterBy(function(rec) {
								return (!rec.get('TreatmentClass_Code').inlist([ 2 ]));
							});
						}
					},
					listeners: {
						'change': function (combo, newValue, oldValue) {
							if (regNick == 'kareliya') return false; // https://redmine.swan.perm.ru/issues/83930
							if (Ext.isEmpty(newValue)) return false;
							var base_form = wnd.FormPanel.getForm();
							this.reloadUslugaComplexField();
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

							base_form.findField('PersonDisp_id').setAllowBlank(!(getRegionNick().inlist(['krasnoyarsk','vologda']) && combo.getFieldValue('TreatmentClass_Code') == '1.3'));
							
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

							if (getRegionNick() == 'kz') {
								base_form.findField('ScreenType_id').setContainerVisible(newValue == 29);
								base_form.findField('ScreenType_id').setAllowBlank(newValue != 29);
								if (newValue != 29) {
									base_form.findField('ScreenType_id').setValue('');
								}
							}
							
							this.getFinanceSource();
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
					comboSubject: 'ScreenType',
					fieldLabel: 'Вид скрининга',
					tabIndex: TABINDEX_EEPLEF + 26,
					prefix: 'r101_',
					width: 300,
					listWidth: 600,
					xtype: 'swcommonsprcombo',
					listeners: {
						'change': function(field, newValue, oldValue) {
							win.getFinanceSource();
						}
					}
				},{
					hiddenName: 'ServiceType_id',
					listeners: {
						'change': function(combo, newValue, oldValue) {
							var base_form = this.FormPanel.getForm();
							var record = combo.getStore().getById(newValue);

							if ( !record ) {
								return false;
							}
						
							if (regNick == 'kareliya') {
								wnd.setTreatmentClass();
							}

							if ( record.get('ServiceType_SysNick') == 'neotl' ) {
								var PayType_SysNick = 'oms';
								switch (regNick) {
									case 'by': PayType_SysNick = 'besus'; break;
									case 'kz': PayType_SysNick = 'Resp'; break;
								}
								base_form.findField('PayType_id').setFieldValue('PayType_SysNick', PayType_SysNick);
								base_form.findField('PayType_id').fireEvent('change',base_form.findField('PayType_id'),base_form.findField('PayType_id').getValue());
							}
						}.createDelegate(this)
					},
					tabIndex: TABINDEX_EEPLEF + 26,
					width: 300,
					xtype: 'swservicetypecombo'
				},this.LpuSectionProfileDopPanel, {
					listeners: {
						'change': function(combo, newValue, oldValue) {
							if (regNick == 'kareliya') {
								wnd.setTreatmentClass();
							}
							
							wnd.reloadUslugaComplexField();

							var base_form = wnd.FormPanel.getForm();
							var
								prof_goal_combo = base_form.findField('ProfGoal_id'),
								VizitType_SysNick = combo.getFieldValue('VizitType_SysNick');

							wnd.setRiskLevelComboState();
							wnd.setWellnessCenterAgeGroupsComboState();

							if (VizitType_SysNick == 'prof' || (regNick == 'kareliya' && VizitType_SysNick == 'medosm')) {
								prof_goal_combo.enable();
							}
							else {
								prof_goal_combo.disable();
								prof_goal_combo.clearValue();
							}
						}.createDelegate(this)
					},
					tabIndex: TABINDEX_EEPLEF + 28,
					width: 300,
					EvnClass_id: 11,
					xtype: 'swvizittypecombo'
				}, { 
					fieldLabel: lang['faktor_riska'],
					hiddenName: 'RiskLevel_id',
					tabIndex: TABINDEX_EEPLEF + 29,
					width: 300,
					xtype: 'swrisklevelcombo'
				}, { 
					comboSubject: 'WellnessCenterAgeGroups',
					fieldLabel: lang['gruppa_cz'],
					hiddenName: 'WellnessCenterAgeGroups_id',
					lastQuery: '',
					moreFields: [
						{ name: 'WellnessCenterAgeGroups_From', mapping: 'WellnessCenterAgeGroups_From' },
						{ name: 'WellnessCenterAgeGroups_To', mapping: 'WellnessCenterAgeGroups_To' }
					],
					tabIndex: TABINDEX_EEPLEF + 30,
					width: 300,
					xtype: 'swcommonsprcombo'
				}, {
					layout: 'column',
					border: false,
					items:[
						{
							layout: 'form',
							border: false,
							items: [{
								tabIndex: TABINDEX_EEPLEF + 31,
								width: 300,
								listeners:
									{
										'change': function(combo, newValue, oldValue) {
											wnd.checkUslugaComplexUidAllowBlank();
											var base_form = wnd.FormPanel.getForm();
											var pay_type = combo.getStore().getById(newValue);
											var pay_type_nick = (pay_type && pay_type.get('PayType_SysNick')) || '';
											if(regNick.inlist([ 'ufa', 'ekb' ]))
											{

												var uslugacomplex_combo = base_form.findField('UslugaComplex_uid');

												if (regNick == 'ekb') {
													base_form.findField('UslugaComplex_uid').setPayType(newValue);
													wnd.setDefaultMedicalCareKind();
												}

												// для Екб код посещения необязателен
												if (regNick == 'ufa') {
													//Проверяем по SysNick
													if ( pay_type ) {
														if ((pay_type_nick!='oms')&&(pay_type_nick!='dopdisp'))
														{
															uslugacomplex_combo.setAllowBlank(true);
														}
														else
														{
															uslugacomplex_combo.setAllowBlank(false);
														}
													}
												}
											}

											wnd.filterVizitTypeCombo();

											if ( regNick.inlist([ 'ekb' ]) ) {
												var uslugacomplex_uid = uslugacomplex_combo.getValue();
												uslugacomplex_combo.lastQuery = 'This query sample that is not will never appear';
												uslugacomplex_combo.getStore().removeAll();
												uslugacomplex_combo.getStore().baseParams.query = '';
												uslugacomplex_combo.getStore().baseParams.UslugaComplexPartition_CodeList = ('bud' == pay_type_nick || 'fbud' == pay_type_nick) ? Ext.util.JSON.encode([350,351]) : Ext.util.JSON.encode([300,301]);
												wnd.reloadUslugaComplexField(uslugacomplex_uid);
												base_form.findField('Mes_id').getStore().removeAll();
												base_form.findField('Mes_id').setMesType_id(('bud' == pay_type_nick || 'fbud' == pay_type_nick) ? 8 : 'oms' == pay_type_nick ? 0 : null);
												base_form.findField('Mes_id').setUslugaComplexPartitionCodeList(('bud' == pay_type_nick || 'fbud' == pay_type_nick) ? [350,351] : null);
												wnd.loadMesCombo();
												wnd.filterLpuSectionProfile();
											}

											wnd.reloadUslugaComplexField();

											if (regNick == 'kz' && pay_type) {
												base_form.findField('isPaidVisit').setValue(pay_type.get('PayType_id')=='153');
											}
										}
									},
								useCommonFilter: true,
								fieldLabel: regNick == 'kz' ? 'Источник финансирования' : 'Вид оплаты',
								xtype: 'swpaytypecombo'
							}]
						},
						{
							layout: 'form',
							border: false,
							hidden: regNick != 'kz',
							style: 'margin: 3px 0 0 10px;',
							items: [{
								xtype: 'checkbox',
								hideLabel: true,
								boxLabel: 'Платное посещение',
								name: 'isPaidVisit',
								handler: function() {
									var base_form = wnd.FormPanel.getForm();

									if (base_form.findField('isPaidVisit').getValue()) {
										base_form.findField('PayType_id').setValue('153');
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
					disabled: regNick.inlist([ 'ufa', 'kareliya', 'ekb' ]),
					tabIndex: TABINDEX_EEPLEF + 32,
					loadParams: {
						params: (regNick == 'ekb' ? {} : {where: "where MedicalCareKind_Code in ('11','12','13','4')"})
					},
					fieldLabel: lang['vid_med_pomoschi'],
					hiddenName: 'MedicalCareKind_vid',
					width: 300,
					xtype: 'swmedicalcarekindfedcombo'
				}, {
					border: false,
					hidden: regNick != 'ekb', // Открыто для Екатеринбурга
					layout: 'form',
					items: [{
						allowBlank: true,
						fieldLabel: lang['mes'],
						hiddenName: 'Mes_id',
						listeners: {
							'change': function(combo, newValue) {
								var base_form = wnd.FormPanel.getForm();
								if (regNick == 'ekb') {
										wnd.reloadUslugaComplexField();
									}
							}
						},
						listWidth: 600,
						tabIndex: TABINDEX_EEPLEF + 33,
						width: 450,
						xtype: 'swmesoldvizitcombo'
					}]
				}, {
					border: false,
					hidden: (false == sw.Promed.EvnVizitPL.isSupportVizitCode()),
					layout: 'form',
					items: [{
						// перенес определение обязательности поля в открытие формы
						allowBlank: true,
						fieldLabel: 'Код' + (regNick == 'kz' ? ' услуги ' : ' ') + 'посещения',
						hiddenName: 'UslugaComplex_uid',
						to: 'EvnVizitPL',
						id: 'EEPLEF_UslugaComplex',
						listWidth: 600,
						listeners: {
							'change': function(combo, newValue, oldValue) {
								this.setLpuSectionProfile();
								
								var base_form = this.FormPanel.getForm();
								// https://redmine.swan.perm.ru/issues/15258
								this.UslugaComplex_uid_AgeGroupId = base_form.findField('UslugaComplex_uid').getFieldValue('UslugaComplex_AgeGroupId');

								if (regNick == 'ekb') {
									if(
										newValue == '4568436' 
										&& base_form.findField('Diag_id').getStore().getById(base_form.findField('Diag_id').getValue()).data.DiagFinance_IsOms != '1' 
										&& base_form.findField('PayType_id').getStore().getById(base_form.findField('PayType_id').getValue()).data.PayType_SysNick == 'bud'
										){
											var textMsg = lang['usluga_v01_069_998_mojet_byit_vyibrana_tolko_pri_diagnoze_oplachivaemom_po_oms'];
											sw.swMsg.alert(lang['oshibka'], textMsg, function() {
												this.formStatus = 'edit';
												base_form.findField('UslugaComplex_uid').clearValue();
												base_form.findField('UslugaComplex_uid').markInvalid(textMsg);
												base_form.findField('UslugaComplex_uid').focus(true);
											}.createDelegate(this));
											return false;
									}
									this.loadMesCombo();
								}
								if (regNick == 'ufa') {
									
                                    var PLsetDate = base_form.findField('EvnVizitPL_setDate').getValue();
									var usluga_complex_code;
                                    var usluga_complex_id = base_form.findField('UslugaComplex_uid').getValue();
                                    var index = combo.getStore().findBy(function(rec) {
										return (rec.get(combo.valueField) == newValue);
									});
                                    var index_uid_code = base_form.findField('UslugaComplex_uid').getStore().findBy(function(rec) {
                                        return (rec.get('UslugaComplex_id') == usluga_complex_id);
                                    });

									if ( index >= 0 ) {
										var code = combo.getStore().getAt(index).get('UslugaComplex_Code');

										if ( !Ext.isEmpty(code) && code.toString().length == 6 && isProphylaxisVizitOnly(code) ) {
											var is_finish_combo = base_form.findField('EvnPL_IsFinish');
											is_finish_combo.setValue(2);
											//base_form.findField('EvnPL_IsFinish').fireEvent('change', base_form.findField('EvnPL_IsFinish'), 2);
											var is_finish_index = is_finish_combo.getStore().findBy(function(rec) {
												return (rec.get(is_finish_combo.valueField) == is_finish_combo.getValue());
											});
											is_finish_combo.fireEvent('select', is_finish_combo, is_finish_combo.getStore().getAt(is_finish_index));
										}
									}

                                    if ( index_uid_code >= 0 ) {
                                        usluga_complex_code = base_form.findField('UslugaComplex_uid').getStore().getAt(index).get('UslugaComplex_Code');
                                    }

									// https://redmine.swan.perm.ru/issues/31548
									// https://redmine.swan.perm.ru/issues/32218
                                    if (
										Date.parseDate(Ext.util.Format.date(PLsetDate),'d.m.Y') >= Date.parseDate('01.07.2013','d.m.Y')
										&& !Ext.isEmpty(usluga_complex_code)
										&& (
											usluga_complex_code.substr(-5, 5).inlist([
												//'66805', '00805', '31805', '57805', '71805', '67805', '68805', '69805',
												// Добавил коды
												// @task https://redmine.swan.perm.ru/issues/65411
												'31890', '57890', '71890', '66890', '00890', '67890', '68890', '69890',
												// Добавил коды
												// @task https://redmine.swan.perm.ru/issues/83983
												'69893', '67893', '73893',
												// Заменил 573805 на %73805
												// @task https://redmine.swan.perm.ru/issues/84992
												'73805'
											]) ||
											usluga_complex_code.substr(-3, 3).inlist(['805', '893'])
										)
									) {
										if ( this.action != 'view' ) {
											base_form.findField('HealthKind_id').enable();
										}
                                    } else {
                                        base_form.findField('HealthKind_id').clearValue();
                                        base_form.findField('HealthKind_id').disable();
                                    }
									
									if ( 
										this.action != 'view' &&
										!Ext.isEmpty(usluga_complex_code) &&
										usluga_complex_code.substr(-3, 3).inlist(['805', '893']) &&
										Date.parseDate(Ext.util.Format.date(PLsetDate), 'd.m.Y') >= Date.parseDate('01.11.2016','d.m.Y')
									) {
										base_form.findField('HealthKind_id').setAllowBlank(false);
									} else {
										base_form.findField('HealthKind_id').setAllowBlank(true);
									}

									if (
										!Ext.isEmpty(usluga_complex_code) && (usluga_complex_code.substr(-3, 3).inlist(['805', '893']))
									) {
										base_form.findField('DispProfGoalType_id').setAllowBlank(false);
									}
									else {
										base_form.findField('DispProfGoalType_id').setAllowBlank(true);
									}
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
							}.createDelegate(this)
						},
						reload: function(){
							var base_form = this.FormPanel.getForm(),
								form = this,
							    combo = base_form.findField('UslugaComplex_uid'),
								params = {},
								usluga_complex_id = combo.getValue(),
								is_ufa = (regNick == 'ufa');
							if (usluga_complex_id && !combo.isload) {
								params.UslugaComplex_id = usluga_complex_id;
							}

							combo.getStore().load({
								callback: function() {
									if ( combo.getStore().getCount() > 0 ) {
										combo.setValue(usluga_complex_id);
										combo.isload = true;
										/*if ( is_ufa ) {
											var index = combo.getStore().findBy(function(rec) {
												if ( rec.get('UslugaComplex_id') == usluga_complex_id ) {
													return true;
												}
												else {
													return false;
												}
											});
											var record = combo.getStore().getAt(index);
											log(['usluga_complex_combo reload', usluga_complex_id, uslugacomplex_code, params, record, combo]);
											//логика в зависимости от кода посещения
										}*/
										if ( is_ufa && !Ext.isEmpty(base_form.findField('UslugaComplex_Code').getValue()) && form.action == 'addEvnVizitPL' ) {
											var index = combo.getStore().findBy(function(rec) {
												return (rec.get('UslugaComplex_Code') == base_form.findField('UslugaComplex_Code').getValue());
											});

											if ( index >= 0 ) {
												combo.setValue(combo.getStore().getAt(index).get('UslugaComplex_id'));
											}

											base_form.findField('UslugaComplex_Code').setValue('');
										}
									}
									else {
										combo.clearValue();
									}
								}.createDelegate(this),
								params: params
							});
						}.createDelegate(this),
						tabIndex: TABINDEX_EEPLEF + 34,
						width: 450,
						xtype: 'swuslugacomplexnewcombo'
					}]
				}, {
					comboSubject: 'UslugaMedType',
					enableKeyEvents: true,
					hidden: regNick !== 'kz',
					allowBlank: regNick !== 'kz',
					fieldLabel: langs('Вид услуги'),
					hiddenName: 'UslugaMedType_id',
					lastQuery: '',
					tabIndex: TABINDEX_EEPLEF + 35,
					typeCode: 'int',
					width: 450,
					xtype: 'swcommonsprcombo'
				}, /*this.EbkLpuSectionProfilePanel,*/ {
					allowDecimals: false,
					allowNegative: false,
					enableKeyEvents: true,
					fieldLabel: lang['vremya_priema_min'],
					name: 'EvnVizitPL_Time',
					tabIndex: TABINDEX_EEPLEF + 35,
					width: 70,
					xtype: 'numberfield'
				}, {
					autoLoad: false,
					comboSubject: 'ProfGoal',
					fieldLabel: lang['tsel_profosmotra'],
					hiddenName: 'ProfGoal_id',
					tabIndex: TABINDEX_EEPLEF + 36,
					width: 450,
					xtype: 'swcommonsprcombo'
				}, {
					comboSubject: 'DispClass',
					enableKeyEvents: true,
					fieldLabel: lang['v_ramkah_disp_med_osmotra'],
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
							var base_form = this.FormPanel.getForm();

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
						}.createDelegate(this)
					},
					onLoadStore: function() {
						this.getStore().filterBy(function(rec) {
							if (regNick == 'kareliya') {
								return (rec.get('DispClass_id').inlist([ 4, 6, 8, 9, 10, 11, 12 ]));
							}
							else if (regNick == 'krym') {
								return (rec.get('DispClass_id').inlist([ 1, 2, 3, 4, 5, 7, 8, 10, 12 ]));
							}
							else {
								return (rec.get('DispClass_id').inlist([ 4, 8, 11, 12 ]));
							}
						});
					},
					tabIndex: TABINDEX_EEPLEF + 37,
					typeCode: 'int',
					width: 450,
					xtype: 'swcommonsprcombo'
				}, {
					comboSubject: 'DispProfGoalType',
					enableKeyEvents: true,
					fieldLabel: lang['v_ramkah_disp_med_osmotra'],
					hiddenName: 'DispProfGoalType_id',
					lastQuery: '',
					moreFields: [{name: 'DispProfGoalType_IsVisible', mapping: 'DispProfGoalType_IsVisible'}],
					onLoadStore: function() {
						this.getStore().filterBy(function(rec) {
							return (rec.get('DispProfGoalType_IsVisible') == 2);
						});
					},
					tabIndex: TABINDEX_EEPLEF + 38,
					typeCode: 'int',
					width: 450,
					xtype: 'swcommonsprcombo'
				},{
					displayField: 'EvnPLDisp_Name',
					enableKeyEvents: true,
					fieldLabel: lang['karta_disp_med_osmotra'],
					hiddenName: 'EvnPLDisp_id',
					store: new Ext.data.JsonStore({
						fields: [
							{name: 'EvnPLDisp_id', type: 'int'},
							{name: 'EvnPLDisp_setDate', type: 'date', dateFormat: 'd.m.Y'},
							{name: 'EvnPLDisp_Name', type: 'string'}
						],
						key: 'EvnPLDisp_id',
						sortInfo: {
							field: 'EvnPLDisp_setDate'
						},
						url: '/?c=EvnPLDisp&m=loadEvnPLDispList'
					}),
					tabIndex: TABINDEX_EEPLEF + 39,
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
					fieldLabel: lang['karta_dis_ucheta'],
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
					tabIndex: TABINDEX_EEPLEF + 40,
					tpl:
					'<tpl for="."><div class="x-combo-list-item">'+
					'{PersonDisp_Name}&nbsp;'+
					'</div></tpl>',
					valueField: 'PersonDisp_id',
					width: 450,
					xtype: 'swbaseremotecombo'
				}, {
					allowDecimals: false,
					allowNegative: false,
					xtype: 'numberfield',
					name: 'PregnancyEvnVizitPL_Period',
					fieldLabel: 'Срок беременности, недель',
					minValue: 1,
					maxValue: 45,
					width: 100
				}, {
                    border: false,
                    hidden: (regNick != 'ufa'),
                    layout: 'form',
                    items: [{
                        allowDecimals: true,
                        allowNegative: false,
                        enableKeyEvents: true,
                        fieldLabel: lang['gruppa_zdorovya'],
                        hiddenName: 'HealthKind_id',
                        tabIndex: TABINDEX_EEPLEF + 41,
                        xtype: 'swhealthkindcombo'
                    }]
                }]
			}),
			new sw.Promed.Panel({
				autoHeight: true,
				bodyStyle: 'padding-top: 0.5em;',
				border: true,
				collapsible: false,
				id: 'EEPLEF_DiagPanel',
				layout: 'form',
				style: 'margin-bottom: 0.5em;',
				title: lang['osnovnoy_diagnoz'],

				items:
				[
					// Диагноз
					// #170429
					// При изменении значения корректируется видимость полей "Стадия ХСН" и "Функц. класс" и
					// значения в этих полях. Если значения не пустые, выдается уведомление.
					{
						xtype: 'swdiagcombo',
						id: 'EEPLEF_DiagCombo',
					hiddenName: 'Diag_id',
					tabIndex: TABINDEX_EEPLEF + 42,
						checkAccessRights: true,
					width: 450,

						onChange: function(combo, newValue, oldValue)
						{
							var base_form = this._form,
								v,
								textMsg;

							this._refreshHsnDetails(combo);

						base_form.findField('TreatmentClass_id').onLoadStore();
						this.refreshFieldsVisibility(['TumorStage_id', 'PainIntensity_id']);

							if (regNick == 'ekb')
							this.setDefaultMedicalCareKind();

							if (regNick == 'ekb' &&
								base_form.findField('UslugaComplex_uid').getValue() == '4568436' &&
								combo.getStore().getById(newValue).data.DiagFinance_IsOms != '1' &&
								(v = base_form.findField('PayType_id')) &&
								v.getStore().getById(v.getValue()).data.PayType_SysNick == 'bud')
							{
								textMsg = lang['usluga_v01_069_998_mojet_byit_vyibrana_tolko_pri_diagnoze_oplachivaemom_po_oms'];
								sw.swMsg.alert(lang['oshibka'], textMsg,
												function()
												{
									this.formStatus = 'edit';
													this._cmbDiag.clearValue();
													this._cmbDiag.markInvalid(textMsg);
													this._cmbDiag.focus(true);
								}.createDelegate(this));
								return false;
						}

							if (!Ext.isEmpty(newValue) && this.isLastVizit() && !regNick.inlist(['ufa', 'kareliya']))
								base_form.findField('Diag_lid').getStore().load(
								{
									callback: function()
									{
									base_form.findField('Diag_lid').setValue(newValue);
									this.setDiagConcComboVisible();
								}.createDelegate(this),

									params:
									{
										where: "where Diag_id = " + newValue
									}
							});

						this.setDiagFidAndLid();
						this.diagIsChanged = true;

							if (regNick == 'pskov')
							{
								v = base_form.findField('LpuSectionProfile_id');
								v.fireEvent('change', v, v.getValue());
						}
					}.createDelegate(this)
					},

					// Стадия ХСН (для диагноза)
					// #170429:
					//  1. Отображается только при выборе диагноза из группы ХСН.
					//  2. Обязательно для заполнения только для диагноза из группы ХСН и только для региона ufa.
					{
						xtype: 'combo',
						id: 'EEPLEF_cmbDiagHSNStage',
						hiddenName: 'HSNStage_id',
						fieldLabel: langs('Стадия ХСН'),
						tabIndex: TABINDEX_EVPLEF + 27.1,
						valueField: 'HSNStage_id',
						displayField: 'HSNStage_Name',
						mode: 'local',
						triggerAction: 'all',
						forceSelection: true,
						editable: false,
						width: 230,
						hidden: true,

						store: new Ext.data.JsonStore(
						{
							key: 'HSNStage_id',
							url: '/?c=EvnPL&m=getHsnStage',
							autoLoad: true,

							fields:
							[
								{ name: 'HSNStage_id', type: 'int' },
								{ name: 'HSNStage_Name', type: 'string' }
							]
						})
					},

					// Функциональный класс (для диагноза)
					// #170429:
					//  1. Отображается только при выборе диагноза из группы ХСН.
					//  2. Обязательно для заполнения только для диагноза из группы ХСН и только для региона ufa.
					{
						xtype: 'combo',
						id: 'EEPLEF_cmbDiagHSNFuncClass',
						hiddenName: 'HSNFuncClass_id',
						fieldLabel: langs('Функциональный класс'),
						tabIndex: TABINDEX_EVPLEF + 27.2,
						valueField: 'HSNFuncClass_id',
						displayField: 'HSNFuncClass_Name',
						mode:'local',
						triggerAction: 'all',
						forceSelection: true,
						editable: false,
						width: 230,
						hidden: true,

						store: new Ext.data.JsonStore(
						{
							key: 'HSNFuncClass_id',
							url: '/?c=EvnPL&m=getHSNFuncClass',
							autoLoad: true,

							fields:
							[
								{ name: 'HSNFuncClass_id', type: 'int' },
								{ name: 'HSNFuncClass_Name', type: 'string' }
							]
						})
					},

					{
					hiddenName: 'DeseaseType_id',
						listeners: {
							'change': function(combo, newValue, oldValue) {
								this.refreshFieldsVisibility(['TumorStage_id']);
							}.createDelegate(this)
						},
					tabIndex: TABINDEX_EEPLEF + 43,
					width: 450,
					xtype: 'swdeseasetypecombo'
				}, {
					fieldLabel:langs('Стадия выявленного ЗНО'),
					hiddenName:'TumorStage_id',
					xtype:'swtumorstagenewcombo',
					tabIndex: TABINDEX_EEPLEF + 43.5,
					width: 450,
					loadParams: regNum.inlist([58,66,101]) ? {mode: 1} : {mode:0} // только свой регион / + нулловый рег
				}, {
					comboSubject: 'PainIntensity',
					fieldLabel: langs('Интенсивность боли'),
					hiddenName: 'PainIntensity_id',
					tabIndex: TABINDEX_EVPLEF + 43.55,
					width: 450,
					xtype: 'swcommonsprcombo'
				},
					this.DrugTherapySchemePanel,
				{
					bodyStyle: 'padding: 0px',
					border: false,
					id: 'EEPLEF_IsZNOPanel',
					hidden: regNick == 'kz',
					layout: 'form',
					xtype: 'panel',
					items: [{
						fieldLabel: 'Подозрение на ЗНО',
						id: 'EEPLEF_EvnVizitPL_IsZNOCheckbox',
						tabIndex: TABINDEX_EEPLEF + 43.6,
						xtype: 'checkbox',
						listeners:{
							'change': function(checkbox, value) {
								if (regNick != 'ekb' || checkbox.disabled) return;
								var base_form = wnd.FormPanel.getForm(),
									DiagSpid = Ext.getCmp('EEPLEF_Diag_spid'),
									diagcode = base_form.findField('Diag_id').getFieldValue('Diag_Code'),
									personframe = wnd.findById('EEPLEF_PersonInformationFrame');
								if(!value && wnd.lastzno == 2 && (Ext.isEmpty(diagcode) || diagcode.search(new RegExp("^(C|D0)", "i"))<0)) {
									sw.swMsg.show({
										buttons: Ext.Msg.YESNO,
										fn: function (buttonId, text, obj) {
											if (buttonId == 'yes') {
												wnd.changeZNO({isZNO: false});
											} else {
												checkbox.setValue(true);
												if(!Ext.isEmpty(DiagSpid.lastvalue))
													DiagSpid.setValue(DiagSpid.lastvalue);
											}
										}.createDelegate(this),
										icon: Ext.MessageBox.QUESTION,
										msg: 'По пациенту '+
											personframe.getFieldValue('Person_Surname')+' '+
											personframe.getFieldValue('Person_Firname')+' '+
											personframe.getFieldValue('Person_Secname')+
											' ранее установлено подозрение на ЗНО. Снять признак подозрения?',
										title: 'Вопрос'
									});
								}
								if(value) {
									if(Ext.isEmpty(DiagSpid.getValue()) && !Ext.isEmpty(wnd.lastznodiag)) {
										DiagSpid.getStore().load({
											callback:function () {
												DiagSpid.getStore().each(function (rec) {
													if (rec.get('Diag_id') == wnd.lastznodiag) {
														DiagSpid.fireEvent('select', DiagSpid, rec, 0);
													}
												});
											},
											params:{where:"where DiagLevel_id = 4 and Diag_id = " + wnd.lastznodiag}
										});
									}
									wnd.changeZNO({isZNO: true});
								}
							},
							'check': function(checkbox, value) {
								var base_form = wnd.FormPanel.getForm();
								var DiagSpid = base_form.findField('Diag_spid');
								if (value == true) {
									DiagSpid.showContainer();
									DiagSpid.setAllowBlank(!regNick.inlist([ 'astra', 'perm', 'ufa', 'msk' ]));
								} else {
									DiagSpid.lastvalue = DiagSpid.getValue();
									DiagSpid.clearValue();
									DiagSpid.hideContainer();
									DiagSpid.setAllowBlank(true);
								}
							}
						}
					}, {
						fieldLabel: 'Подозрение на диагноз',
						hiddenName: 'Diag_spid',
						id: 'EEPLEF_Diag_spid',
						additQueryFilter: "(Diag_Code like 'C%' or Diag_Code like 'D0%')",
						baseFilterFn: function(rec){
							if(typeof rec.get == 'function') {
								return (rec.get('Diag_Code').substr(0,1) == 'C' || rec.get('Diag_Code').substr(0,2) == 'D0');
							} else if (rec.attributes && rec.attributes.Diag_Code) {
								return (rec.attributes.Diag_Code.substr(0,1) == 'C' || rec.attributes.Diag_Code.substr(0,2) == 'D0');
							} else {
								return true;
							}
						},
						onChange: function() {
							wnd.setDiagSpidComboDisabled();
						},
						width: 450,
						xtype: 'swdiagcombo'
					}, {
						layout: 'form',
						border: false,
						id: 'EEPLEF_BiopsyDatePanel',
						hidden: regNick != 'ekb',
						items: [{
							fieldLabel: 'Дата взятия биопсии',
							format: 'd.m.Y',
							name: 'EvnVizitPL_BiopsyDate',
							plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
							width: 100,
							xtype: 'swdatefield'
						}]
					}]
				}, {
					fieldLabel: langs('Значение по шкале Рэнкина'),
					width: 450,
					comboSubject: 'RankinScale',
					hiddenName: 'RankinScale_id',
					xtype: 'swcommonsprcombo'
				},

				// Поле "Осложнение" + доп. поля "Стадия ХСН" и "Функц. класс" (#170429).
				// Отображается только для регионов ufa, ekb.
				{
					border: false,
					hidden: !(regNick.inlist(['ufa', 'ekb'])),
					layout: 'form',
					items:
					[
						// Осложнение
						// #170429
						// При изменении значения корректируется видимость полей "Стадия ХСН" и "Функц. класс"
						// и значения в этих полях. Если значения не пустые, выдается уведомление.
						{
							xtype: 'swdiagcombo',
							id: 'EEPLEF_cmbComplDiag',
							hiddenName: 'Diag_agid',
						fieldLabel: lang['oslojnenie'],
						tabIndex: TABINDEX_EEPLEF + 44,
							checkAccessRights: true,
						width: 450,

							onChange: function(combo, newValue, oldValue)
							{
								this._refreshHsnDetails(combo);
							}.createDelegate(this)
						},

						// Стадия ХСН (для осложнения)
						// #170429:
						//  1. Отображается только при выборе осложнения из группы ХСН.
						//  2. Обязательно для заполнения только для осложнения из группы ХСН и только для региона
						//     ufa.
						{
							xtype: 'combo',
							id: 'EEPLEF_cmbComplDiagHSNStage',
							hiddenName: 'ComplDiagHSNStage_id',
							fieldLabel: 'Стадия ХСН',
							tabIndex: TABINDEX_EVPLEF + 30.1,
							valueField: 'HSNStage_id',
							displayField: 'HSNStage_Name',
							mode: 'local',
							triggerAction: 'all',
							forceSelection: true,
							editable: false,
							width: 230,
							hidden: true,

							store: new Ext.data.JsonStore(
							{
								key: 'HSNStage_id',
								url: '/?c=EvnPL&m=getHsnStage',
								autoLoad: true,

								fields:
								[
									{ name: 'HSNStage_id', type: 'int' },
									{ name: 'HSNStage_Name', type: 'string' }
								]
							}),
						},

						// "Функциональный класс" (для осложнения)
						// #170429:
						//  1. Отображается только при выборе осложнения из группы ХСН.
						//  2. Обязательно для заполнения только для осложнения из группы ХСН и только для региона
						//     ufa.
						{
							xtype: 'combo',
							id: 'EEPLEF_cmbComplDiagHSNFuncClass',
							hiddenName: 'ComplDiagHSNFuncClass_id',
							fieldLabel: 'Функциональный класс',
							tabIndex: TABINDEX_EVPLEF + 30.2,
							valueField: 'HSNFuncClass_id',
							displayField: 'HSNFuncClass_Name',
							mode: 'local',
							triggerAction: 'all',
							forceSelection: true,
							editable: false,
							width: 230,
							hidden: true,

							store: new Ext.data.JsonStore(
							{
								key: 'HSNFuncClass_id',
								url: '/?c=EvnPL&m=getHSNFuncClass',
								autoLoad: true,

								fields:
								[
									{ name: 'HSNFuncClass_id', type: 'int' },
									{ name: 'HSNFuncClass_Name', type: 'string' }
								]
							})
						}
					]
					}]
			}),
			new sw.Promed.Panel({
				border: true,
				collapsible: false,
				height: 200,
				id: 'EEPLEF_EvnDiagPLPanel',
				isLoaded: false,
				layout: 'border',
				listeners: {
					'expand': function(panel) {
						if ( panel.isLoaded === false ) {
							panel.isLoaded = true;
							panel.findById('EEPLEF_EvnDiagPLGrid').getStore().load({
								params: {
									EvnVizitPL_id: this.FormPanel.getForm().findField('EvnVizitPL_id').getValue()
								}
							});
						}
						panel.doLayout();
					}.createDelegate(this)
				},
				style: 'margin-bottom: 0.5em;',
				title: lang['soputstvuyuschie_diagnozyi'],
				items: [ new Ext.grid.GridPanel({
					autoExpandColumn: 'autoexpand_diag',
					autoExpandMin: 100,
					border: false,
					columns: [{
						dataIndex: 'EvnDiagPL_setDate',
						header: lang['data_ustanovki'],
						hidden: false,
						renderer: Ext.util.Format.dateRenderer('d.m.Y'),
						resizable: false,
						sortable: true,
						width: 130
					}, {
						dataIndex: 'Diag_Code',
						header: lang['kod'],
						hidden: false,
						resizable: false,
						sortable: true,
						width: 80
					}, {
						dataIndex: 'Diag_Name',
						header: lang['naimenovanie'],
						hidden: false,
						resizable: false,
						sortable: true,
						width: 250
					}, {
						dataIndex: 'DeseaseType_Name',
						header: lang['harakter'],
						hidden: false,
						id: 'autoexpand_diag',
						sortable: true
					}],
					frame: false,
					id: 'EEPLEF_EvnDiagPLGrid',
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

							var grid = this.findById('EEPLEF_EvnDiagPLGrid');

							switch ( e.getKey() ) {
								case Ext.EventObject.DELETE:
									this.deleteEvent('EvnDiagPL');
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

									this.openEvnDiagPLEditWindow(action);
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
							}
						}.createDelegate(this),
						scope: this,
						stopEvent: true
					}],
					layout: 'fit',
					listeners: {
						'rowdblclick': function(grid, number, obj) {
							this.openEvnDiagPLEditWindow('edit');
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
								var toolbar = this.findById('EEPLEF_EvnDiagPLGrid').getTopToolbar();

								if ( selected_record ) {
									access_type = selected_record.get('accessType');
									id = selected_record.get('EvnDiagPL_id');
								}

								toolbar.items.items[1].disable();
								toolbar.items.items[3].disable();

								if ( id ) {
									toolbar.items.items[2].enable();

									if ( !this.action.inlist([ 'viewEvnPL', 'viewEvnVizitPL' ]) && access_type == 'edit' ) {
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
									LoadEmptyRow(this.findById('EEPLEF_EvnDiagPLGrid'));
								}

								// this.findById('EEPLEF_EvnDiagPLGrid').getView().focusRow(0);
								// this.findById('EEPLEF_EvnDiagPLGrid').getSelectionModel().selectFirstRow();
							}.createDelegate(this)
						},
						reader: new Ext.data.JsonReader({
							id: 'EvnDiagPL_id'
						}, [{
							mapping: 'accessType',
							name: 'accessType',
							type: 'string'
						}, {
							mapping: 'EvnDiagPL_id',
							name: 'EvnDiagPL_id',
							type: 'int'
						}, {
							mapping: 'EvnVizitPL_id',
							name: 'EvnVizitPL_id',
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
							mapping: 'DeseaseType_id',
							name: 'DeseaseType_id',
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
                            mapping: 'HealthKind_id',
                            name: 'HealthKind_id',
                            type: 'int'
                        }, {
							mapping: 'MedPersonal_id',
							name: 'MedPersonal_id',
							type: 'int'
						}, {
							dateFormat: 'd.m.Y',
							mapping: 'EvnDiagPL_setDate',
							name: 'EvnDiagPL_setDate',
							type: 'date'
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
							mapping: 'DeseaseType_Name',
							name: 'DeseaseType_Name',
							type: 'string'
						}]),
						url: '/?c=EvnPL&m=loadEvnDiagPLGrid'
					}),
					tbar: new sw.Promed.Toolbar({
						buttons: [{
							handler: function() {
								this.openEvnDiagPLEditWindow('add');
							}.createDelegate(this),
							iconCls: 'add16',
							text: BTN_GRIDADD,
							tooltip: BTN_GRIDADD_TIP
						}, {
							handler: function() {
								this.openEvnDiagPLEditWindow('edit');
							}.createDelegate(this),
							iconCls: 'edit16',
							text: BTN_GRIDEDIT,
							tooltip: BTN_GRIDEDIT_TIP
						}, {
							handler: function() {
								this.openEvnDiagPLEditWindow('view');
							}.createDelegate(this),
							iconCls: 'view16',
							text: BTN_GRIDVIEW,
							tooltip: BTN_GRIDVIEW_TIP
						}, {
							handler: function() {
								this.deleteEvent('EvnDiagPL');
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
				collapsible: false,
				height: 200,
				id: 'EEPLEF_EvnUslugaPanel',
				isLoaded: false,
				layout: 'border',
				listeners: {
					'expand': function(panel) {
						if ( panel.hidden ) {
							return false;
						}

						if ( panel.isLoaded === false ) {
							panel.isLoaded = true;
							panel.findById('EEPLEF_EvnUslugaGrid').getStore().load({
								params: {
									pid: this.FormPanel.getForm().findField('EvnVizitPL_id').getValue()
								}
							});
						}
						panel.doLayout();
					}.createDelegate(this)
				},
				style: 'margin-bottom: 0.5em;',
				title: lang['uslugi'],
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
					id: 'EEPLEF_EvnUslugaGrid',
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

							var grid = this.findById('EEPLEF_EvnUslugaGrid');

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

									var toolbar = this.findById('EEPLEF_EvnUslugaGrid').getTopToolbar();
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

							}
						}.createDelegate(this),
						scope: this,
						stopEvent: true
					}],
					listeners: {
						'rowdblclick': function(grid, number, obj) {
							var toolbar = this.findById('EEPLEF_EvnUslugaGrid').getTopToolbar();
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
								var toolbar = this.findById('EEPLEF_EvnUslugaGrid').getTopToolbar();

								if ( selected_record ) {
									evn_class = selected_record.get('EvnClass_SysNick');
									access_type = selected_record.get('accessType');
									id = selected_record.get('EvnUsluga_id');
								}

								toolbar.items.items[1].disable();
								toolbar.items.items[3].disable();

								if ( id ) {
									if (regNick.inlist(['pskov'])) {
										toolbar.items.items[0].disable();
									}

									toolbar.items.items[2].enable();

									if ( !this.action.inlist([ 'viewEvnPL', 'viewEvnVizitPL' ]) && access_type == 'edit' && evn_class != 'EvnUslugaPar' ) {
										toolbar.items.items[1].enable();
										toolbar.items.items[3].enable();
									}
								}
								else {
									toolbar.items.items[2].disable();

									if (regNick.inlist(['pskov'])) {
										if ( this.action != 'view' && this.findById('EEPLEF_EvnUslugaGrid').getStore().getCount() == 1 ) {
											toolbar.items.items[0].enable();
										}
										else {
											toolbar.items.items[0].disable();
										}
									}
								}
							}.createDelegate(this)
						}
					}),
					stripeRows: true,
					store: new Ext.data.Store({
						autoLoad: false,
						baseParams: {
							'parent': 'EvnVizitPL'
						},
						listeners: {
							'load': function(store, records, index) {
								if ( store.getCount() == 0 ) {
									LoadEmptyRow(this.findById('EEPLEF_EvnUslugaGrid'));
								}
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
							mapping: 'PayType_SysNick',
							name: 'PayType_SysNick',
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
				autoHeight: true,
				bodyStyle: 'padding-top: 0.5em;',
				border: true,
				collapsible: false,
				id: 'EEPLEF_ResultPanel',
				layout: 'form',
				style: 'margin-bottom: 0.5em;',
				title: lang['rezultat'],
				items: [{
					comboSubject: 'YesNo',
					enableKeyEvents: true,
					fieldLabel: lang['sluchay_zakonchen'],
					hiddenName: 'EvnPL_IsFinish',
					listeners: {
						'change': function(combo, newValue, oldValue) {
							/*var index = combo.getStore().findBy(function(rec) {
								return (rec.get(combo.valueField) == newValue);
							});

							combo.fireEvent('select', combo, combo.getStore().getAt(index));*/
							if (getRegionNick() == 'kz') {
								var base_form = this.FormPanel.getForm();
								
								var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Изменение признака..."});
								loadMask.show();
								
								Ext.Ajax.request({
									params: {
										object: 'EvnPLBase'
										, id: base_form.findField('EvnPL_id').getValue()
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
																base_form.findField('EvnPL_IsFinish').setValue(1);
																combo.fireEvent('select', combo, combo.getStore().getAt(1));
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
										
										console.log(response);
									},
									url: '/?c=EvnVizit&m=setEvnVizitParameter'
								});
							}
							
							this.setDiagFidAndLid();

							return true;
						}.createDelegate(this),
						'keydown': function(inp, e) {
							switch ( e.getKey() ) {
								case Ext.EventObject.TAB:
									if ( e.shiftKey == true && (this.findById('EEPLEF_EvnVizitPLPanel').hidden || this.findById('EEPLEF_EvnVizitPLPanel').collapsed) && (this.findById('EEPLEF_DirectInfoPanel').hidden || this.findById('EEPLEF_DirectInfoPanel').collapsed) ) {
										e.stopEvent();
										this.buttons[this.buttons.length - 1].focus();
									}
								break;
							}
						}.createDelegate(this),
						'select': function(combo, record, index) {
							var base_form = this.FormPanel.getForm();

							if ( !record || (record.get('YesNo_Code') == 0 && Ext.globalOptions.polka.is_finish_result_block == '1') ) {
								base_form.findField('DirectClass_id').clearValue();
								base_form.findField('DirectClass_id').setContainerVisible(false);
								base_form.findField('DirectType_id').clearValue();
								base_form.findField('DirectType_id').setContainerVisible(false);
								base_form.findField('EvnPL_UKL').setAllowBlank(true);
								base_form.findField('EvnPL_UKL').setContainerVisible(false);
								base_form.findField('EvnPL_UKL').setRawValue('');
								base_form.findField('InterruptLeaveType_id').disable();
								base_form.findField('Diag_concid').clearValue();
								base_form.findField('Diag_concid').disable();
								base_form.findField('Lpu_oid').setContainerVisible(false);
								base_form.findField('LpuSection_oid').setContainerVisible(false);
								base_form.findField('ResultClass_id').clearValue();
								base_form.findField('ResultClass_id').setAllowBlank(true);
								base_form.findField('ResultClass_id').setContainerVisible(false);
								base_form.findField('EvnPL_IsSurveyRefuse').clearValue();
								base_form.findField('ResultDeseaseType_id').clearValue();
								base_form.findField('ResultDeseaseType_id').setAllowBlank(true);
								base_form.findField('ResultDeseaseType_id').setContainerVisible(false);
								base_form.findField('ResultDeseaseType_fedid').clearValue();
								base_form.findField('ResultDeseaseType_fedid').setAllowBlank(true);
								base_form.findField('ResultDeseaseType_fedid').setContainerVisible(false);
								base_form.findField('LeaveType_fedid').clearValue();
								base_form.findField('LeaveType_fedid').setAllowBlank(true);
								base_form.findField('LeaveType_fedid').setContainerVisible(false);
								base_form.findField('Diag_lid').setAllowBlank(true);
							}
							else {
								if ( record.get('YesNo_Code') == 0 && Ext.globalOptions.polka.is_finish_result_block != '1' ) {
									base_form.findField('ResultDeseaseType_fedid').clearValue();
									base_form.findField('ResultDeseaseType_fedid').setAllowBlank(true);
									base_form.findField('ResultDeseaseType_fedid').setContainerVisible(false);
									base_form.findField('LeaveType_fedid').clearValue();
									base_form.findField('LeaveType_fedid').setAllowBlank(true);
									base_form.findField('LeaveType_fedid').setContainerVisible(false);
								}
								else {
									var is_allow_blank = sw.Promed.EvnPL.isHiddenFedResultFields();
									base_form.findField('ResultDeseaseType_fedid').setAllowBlank(/*false*/is_allow_blank);//sw.Promed.EvnPL.isHiddenFedResultFields());
									base_form.findField('ResultDeseaseType_fedid').showContainer();
									base_form.findField('LeaveType_fedid').setAllowBlank(/*false*/is_allow_blank);//sw.Promed.EvnPL.isHiddenFedResultFields());
									base_form.findField('LeaveType_fedid').showContainer();
								}

								base_form.findField('DirectClass_id').setContainerVisible(true);
								base_form.findField('DirectType_id').setContainerVisible(true);
								base_form.findField('EvnPL_UKL').setContainerVisible(true);
								base_form.findField('ResultClass_id').setContainerVisible(true);
								base_form.findField('EvnPL_IsSurveyRefuse').setContainerVisible(true);
								base_form.findField('ResultDeseaseType_id').setContainerVisible(regNick.inlist([/*'astra',*/'adygeya', 'vologda', 'buryatiya','kaluga','kareliya','krasnoyarsk','krym','ekb','penza','pskov','yakutiya','yaroslavl']) );

								base_form.findField('Diag_lid').setAllowBlank(regNick == 'kareliya' || record.get('YesNo_Code') != 1);

								base_form.findField('InterruptLeaveType_id').enable();
                                base_form.findField('Diag_concid').enable();
								if ( record.get('YesNo_Code') == 0 ) {
									base_form.findField('InterruptLeaveType_id').clearValue();
									base_form.findField('Diag_concid').clearValue();
									base_form.findField('EvnPL_UKL').setAllowBlank(true);
									base_form.findField('ResultClass_id').setAllowBlank(true);
									base_form.findField('ResultDeseaseType_id').setAllowBlank(true);
								}
								else {
									base_form.findField('EvnPL_UKL').setAllowBlank(regNick == 'ekb');
									base_form.findField('ResultClass_id').setAllowBlank(false);
									base_form.findField('ResultDeseaseType_id').setAllowBlank( !(regNick.inlist([/*'astra',*/'adygeya', 'vologda', 'buryatiya','kaluga','kareliya','krasnoyarsk','krym','ekb','penza','pskov','yakutiya','yaroslavl'])) );

									if (!base_form.findField('EvnPL_UKL').getValue() && regNick != 'ekb') {
										base_form.findField('EvnPL_UKL').setValue(1);
									}
								}
                                if (base_form.findField('EvnPL_id').getValue()) {
                                    sw.Promed.Direction.loadDirectionDataForLeave({
                                        loadMask: this.getLoadMask(lang['podojdite_idet_poluchenie_dannyih_napravleniya']),
                                        EvnClass_SysNick: 'EvnPL',
                                        Evn_rid: base_form.findField('EvnPL_id').getValue(),
                                        callback: function(data) {
                                            if (data) {
                                                base_form.findField('DirectType_id').setValue(data.DirectType_id);
                                                base_form.findField('DirectClass_id').setValue(data.DirectClass_id);
                                                base_form.findField('LpuSection_oid').setValue(data.LpuSection_oid||null);
                                                base_form.findField('Lpu_oid').setValue(data.Lpu_oid||null);
                                            }
                                        }
                                    });
                                }
							}
							if (!wnd.fo) {
								wnd.calcFedResultDeseaseType();
                            	wnd.calcFedLeaveType();
							}
							sw.Promed.EvnPL.filterFedResultDeseaseType({
								fieldFedLeaveType: base_form.findField('LeaveType_fedid'),
								fieldFedResultDeseaseType: base_form.findField('ResultDeseaseType_fedid')
							});
							sw.Promed.EvnPL.filterFedLeaveType({
								fieldFedLeaveType: base_form.findField('LeaveType_fedid'),
								fieldFedResultDeseaseType: base_form.findField('ResultDeseaseType_fedid')
							});
							base_form.findField('DirectClass_id').fireEvent('change', base_form.findField('DirectClass_id'), base_form.findField('DirectClass_id').getValue());
						}.createDelegate(this)
					},
					tabIndex: TABINDEX_EEPLEF + 45,
					width: 70,
					xtype: 'swcommonsprcombo'
				}, {
					fieldLabel: langs('Отказ от прохождения медицинских обследований'),
					hiddenName: 'EvnPL_IsSurveyRefuse',
					lastQuery: '',
					tabIndex: TABINDEX_EEPLEF + 45,
					width: 70,
					xtype: 'swyesnocombo'
				}, {
					fieldLabel: (regNick.inlist(['buryatiya','ekb','kareliya','krym','penza','pskov'])) ? langs('Результат обращения') : langs('Результат лечения'),
					hiddenName: 'ResultClass_id',
					lastQuery: '',
					tabIndex: TABINDEX_EEPLEF + 46,
					width: 300,
					xtype: 'swresultclasscombo'
				},{
					comboSubject: 'InterruptLeaveType',
					fieldLabel: lang['sluchay_prervan'],
					hiddenName: 'InterruptLeaveType_id',
					lastQuery: '',
					tabIndex: TABINDEX_EEPLEF + 47,
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
							var base_form = this.FormPanel.getForm();
							base_form.findField('LeaveType_fedid').clearFilter();
							base_form.findField('ResultDeseaseType_fedid').clearFilter();
							if (!wnd.fo) {
								wnd.calcFedLeaveType();
								wnd.calcFedResultDeseaseType();
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
					tabIndex: TABINDEX_EEPLEF + 48,
					width: 300,
					xtype: 'swcommonsprcombo'
				}, {
					border: false,
					hidden: regNick == 'ekb', // Открыто для Екатеринбурга
					layout: 'form',
					items: [{
					allowDecimals: true,
					allowNegative: false,
					fieldLabel: lang['ukl'],
					maxValue: 1,
					name: 'EvnPL_UKL',
					tabIndex: TABINDEX_EEPLEF + 49,
					width: 70,
					value: 1,
					xtype: 'numberfield'
				}]}, {
					border: false,
					hidden: !regNick.inlist(['kareliya', 'astra', 'buryatiya','krym']),
					layout: 'form',
					items: [{
						fieldLabel: 'Впервые выявленная инвалидность',
						hiddenName: 'EvnPL_IsFirstDisable',
						tabIndex: TABINDEX_EEPLEF + 50,
						xtype: 'swyesnocombo',
						width: 70
					}, {
						fieldLabel: 'Впервые выявленная инвалидность',
						hiddenName: 'PrivilegeType_id',
						tabIndex: TABINDEX_EEPLEF + 51,
						loadParams: getRegionNick() != 'krym' ? {params: {where: ' where PrivilegeType_Code in (81,82,83,84)'}} : {params: {where: ' where ReceptFinance_id = 1 and PrivilegeType_Code in (81,82,83,84)'}},
						xtype: 'swprivilegetypecombo',
						width: 200
					}]
					}, {
					comboSubject: 'DirectType',
					fieldLabel: lang['napravlenie'],
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
								base_form = this.FormPanel.getForm(),
								index,
								lpuSectionFilter = new Object(),
								LpuSection_oid = base_form.findField('LpuSection_oid').getValue();
							if (!wnd.fo) {
								wnd.calcFedResultDeseaseType();
								wnd.calcFedLeaveType();
							}
                            sw.Promed.EvnPL.filterFedResultDeseaseType({
								fieldFedLeaveType: base_form.findField('LeaveType_fedid'),
								fieldFedResultDeseaseType: base_form.findField('ResultDeseaseType_fedid')
							});
							sw.Promed.EvnPL.filterFedLeaveType({
								fieldFedLeaveType: base_form.findField('LeaveType_fedid'),
								fieldFedResultDeseaseType: base_form.findField('ResultDeseaseType_fedid')
							});
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
						}.createDelegate(this)
					},
					tabIndex: TABINDEX_EEPLEF + 52,
					width: 300,
					xtype: 'swcommonsprcombo'
				}, {
					comboSubject: 'DirectClass',
					fieldLabel: lang['kuda_napravlen'],
					hiddenName: 'DirectClass_id',
					lastQuery: '',
					listeners: {
						'change': function(combo, newValue, oldValue) {
							var record = combo.getStore().getById(newValue);
							combo.fireEvent('select', combo, record, combo.getStore().indexOf(record));
						},
						'select': function(combo, record, index) {
							var base_form = this.FormPanel.getForm();

							var lpu_combo = base_form.findField('Lpu_oid');
							var lpu_section_combo = base_form.findField('LpuSection_oid');

							lpu_combo.setContainerVisible(false);
							lpu_section_combo.setContainerVisible(false);

							if ( !this.action.inlist([ 'viewEvnPL', 'viewEvnVizitPL' ]) ) {
								lpu_combo.clearValue();
								lpu_section_combo.clearValue();
							}

							if ( !record ) {
								return false;
							}

							switch ( Number(record.get('DirectClass_Code')) ) {
								case 1:
									lpu_section_combo.setContainerVisible(true);
								break;

								case 2:
									lpu_combo.setContainerVisible(true);
								break;
							}

							if(this.action.inlist(['openEvnPL','closeEvnPL'])) {
								if(record.get('DirectClass_Code') == 1 || record.get('DirectClass_Code') == 2) {
									this.setHeight(this.minHeight + 20);
									this.syncSize();
								} else {
									this.setHeight(this.minHeight);
									this.syncSize();
								}
							}
							if (!wnd.fo) {
								wnd.calcFedResultDeseaseType();
								wnd.calcFedLeaveType();
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
					},
					tabIndex: TABINDEX_EEPLEF + 53,
					width: 300,
					xtype: 'swcommonsprcombo'
				}, {
					hiddenName: 'LpuSection_oid',
					tabIndex: TABINDEX_EEPLEF + 54,
					width: 450,
					xtype: 'swlpusectionglobalcombo'
				}, {
					displayField: 'Org_Name',
					editable: false,
					enableKeyEvents: true,
					fieldLabel: langs('МО'),
					hiddenName: 'Lpu_oid',
					listeners: {
						'keydown': function( inp, e ) {
							if ( inp.disabled )
								return true;

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
							return true;
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
							return true;
						}
					},
					mode: 'local',
					onTrigger1Click: function() {
						var base_form = this.FormPanel.getForm();
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
							onlyFromDictionary: true,
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
						return true;

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
					tabIndex: TABINDEX_EEPLEF + 55,
					tpl: new Ext.XTemplate(
						'<tpl for="."><div class="x-combo-list-item">',
						'{Org_Name}',
						'</div></tpl>'
						),
					trigger1Class: 'x-form-search-trigger',
					triggerAction: 'none',
					valueField: 'Lpu_id',
					width: 450,
					xtype: 'swbaseremotecombo'
                }, {
					checkAccessRights: true,
					fieldLabel: lang['zaklyuch_diagnoz'],
					hiddenName: 'Diag_lid',
					tabIndex: TABINDEX_EEPLEF + 56,
					width: 450,
					xtype: 'swdiagcombo',
					onChange: function(combo, newValue, oldValue) {
						this.setDiagConcComboVisible();
						//this.checkAbort();
					}.createDelegate(this)
				}, {
					checkAccessRights: true,
					fieldLabel: lang['zaklyuch_vneshnyaya_prichina'],
					hiddenName: 'Diag_concid',
					tabIndex: TABINDEX_EEPLEF + 57,
					width: 450,
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
					comboSubject: 'PrehospTrauma',
					fieldLabel: lang['vid_travmyi_vneshnego_vozdeystviya'],
					hiddenName: 'PrehospTrauma_id',
					lastQuery: '',
					listeners: {
						'select': function(combo, record, index) {
							var base_form = this.FormPanel.getForm();

							var is_unlaw_combo = base_form.findField('EvnPL_IsUnlaw');

							if ( !record ) {
								is_unlaw_combo.clearValue();
								is_unlaw_combo.setContainerVisible(false);
							}
							else {
								is_unlaw_combo.setValue(1);
								is_unlaw_combo.setContainerVisible(true);
							}
						}.createDelegate(this)
					},
					tabIndex: TABINDEX_EEPLEF + 58,
					width: 300,
					xtype: 'swcommonsprcombo'
				}, {
					border: false,
					layout: 'column',
					items: [{
						border: false,
						layout: 'form',
						items: [{
							comboSubject: 'YesNo',
							fieldLabel: lang['protivopravnaya'],
							hiddenName: 'EvnPL_IsUnlaw',
							lastQuery: '',
							tabIndex: TABINDEX_EEPLEF + 59,
							width: 70,
							xtype: 'swcommonsprcombo'
						}]
					}, {
						border: false,
						labelWidth: 155,
						layout: 'form',
						items: [{
							comboSubject: 'YesNo',
							fieldLabel: lang['netransportabelnost'],
							hiddenName: 'EvnPL_IsUnport',
							lastQuery: '',
							tabIndex: TABINDEX_EEPLEF + 60,
							width: 70,
							xtype: 'swcommonsprcombo'
						}]
					}]
				}, {
                    border: false,
                    hidden: sw.Promed.EvnPL.isHiddenFedResultFields(),
                    layout: 'form',
                    items: [{
                        disabled: regNick == 'astra',
                        fieldLabel: lang['fed_rezultat'],
                        hiddenName: 'LeaveType_fedid',
						lastQuery:'',
						tabIndex: TABINDEX_EEPLEF + 61,
                        width: 300,
                        xtype: 'swleavetypefedcombo'
                    }, {
                        disabled: regNick == 'astra',
                        fieldLabel: lang['fed_ishod'],
						lastQuery:'',
                        hiddenName: 'ResultDeseaseType_fedid',
						tabIndex: TABINDEX_EEPLEF + 62,
                        width: 300,
                        xtype: 'swresultdeseasetypefedcombo'
                    }]
				}]
			}),
			new sw.Promed.Panel({
				autoHeight: true,
				border: true,
				collapsible: true,
				id: 'EEPLEF_SpecificsPanel',
				isLoaded: false,
				layout: 'form',
				style: 'margin-bottom: 0.5em;',
				title: 'Специфика',
				items: [{
					border: false,
					height: 150,
					isLoaded: false,
					region: 'north',
					layout: 'border',
					items: [{
						autoScroll:true,
						border:false,
						collapsible:false,
						wantToFocus:false,
						id: 'EEPLEF_SpecificsTree',
						listeners:{
							'bodyresize': function(tree) {
								
							}.createDelegate(this),
							'beforeload': function(node) {
								
							}.createDelegate(this),
							'click':function (node, e) {
								var base_form = this.FormPanel.getForm();
								var win = this;
								
								var params = {};
								params.onHide = function(isChange) {
									win.loadSpecificsTree();
									win.findById('EEPLEF_EvnUslugaGrid').getStore().load({
										params: {
											pid: win.FormPanel.getForm().findField('EvnVizitPL_id').getValue()
										}
									});
								};
								params.EvnVizitPL_id = node.attributes.value;
								params.EvnDiagPLSop_id = node.attributes.EvnDiagPLSop_id;
								params.Morbus_id = node.attributes.Morbus_id;
								params.MorbusOnko_pid = base_form.findField('EvnVizitPL_id').getValue();
								params.Person_id = base_form.findField('Person_id').getValue();
								params.PersonEvn_id = base_form.findField('PersonEvn_id').getValue();
								params.Server_id = base_form.findField('Server_id').getValue();
								params.allowSpecificEdit = true;
								params.action = (this.action != 'view') ? 'edit' : 'view';
								// всегда пересохраняем, чтобы в специфику ушли актуальные данные
								if (base_form.findField('EvnVizitPL_id').getValue() == 0 || this.diagIsChanged) {
									this.doSave({
										openChildWindow:function () {
											params.EvnVizitPL_id = base_form.findField('EvnVizitPL_id').getValue();
											params.MorbusOnko_pid = base_form.findField('EvnVizitPL_id').getValue();
											getWnd('swMorbusOnkoWindow').show(params);
										}.createDelegate(this)
									});
								} else {
									params.EvnVizitPL_id = base_form.findField('EvnVizitPL_id').getValue();
									params.MorbusOnko_pid = base_form.findField('EvnVizitPL_id').getValue();
									getWnd('swMorbusOnkoWindow').show(params);
								}
							}.createDelegate(this)
						},
						loader:new Ext.tree.TreeLoader({
							dataUrl:'/?c=Specifics&m=getStomSpecificsTree'
						}),
						region:'west',
						root:{
							draggable:false,
							id:'specifics_tree_root',
							nodeType:'async',
							text:'Специфика',
							value:'root'
						},
						rootVisible:false,
						split:true,
						useArrows:true,
						width:250,
						xtype:'treepanel'
					},
					{
						border:false,
						layout:'border',
						region:'center',
						xtype:'panel',
						items:[
							{
								autoHeight:true,
								border:false,
								labelWidth:150,
								split:true,
								items:[
								
								],
								layout:'form',
								region:'north',
								xtype:'panel'
							},
							{
								autoHeight:true,
								border:false,
								id:this.id + '_SpecificFormsPanel',
								items:[

								],
								layout:'fit',
								region:'center',
								xtype:'panel'
							}
						]
					}]
				}]
			})],
			reader: new Ext.data.JsonReader({
				success: Ext.emptyFn
			}, [
				{name: 'accessType'},
				{name: 'EvnXml_id'},
				{name: 'DeseaseType_id'},
				{name: 'Diag_did'},
				{name: 'Diag_preid'},
				{name: 'Diag_id'},
				{name: 'HSNStage_id'},
				{name: 'HSNFuncClass_id'},
				{name: 'Diag_agid'},
				{name: 'ComplDiagHSNStage_id'},
				{name: 'ComplDiagHSNFuncClass_id'},
				{name: 'DirectClass_id'},
				{name: 'EvnDirection_id'},
				{name: 'EvnDirection_vid'},
				{name: 'EvnDirection_Num'},
				{name: 'EvnDirection_setDate'},
				{name: 'EvnDirection_IsAuto'},
				{name: 'EvnDirection_IsReceive'},
				{name: 'Lpu_fid'},
				{name: 'DirectType_id'},
				{name: 'EvnPL_id'},
				{name: 'EvnPL_lid'},
				{name: 'InterruptLeaveType_id'},
				{name: 'Diag_concid'},
				{name: 'Diag_fid'},
				{name: 'Diag_lid'},
				{name: 'EvnPL_IsFinish'},
				{name: 'EvnPL_IsSurveyRefuse'},
				{name: 'EvnPL_IsUnlaw'},
				{name: 'EvnPL_IsUnport'},
				{name: 'EvnPL_NumCard'},
				{name: 'EvnPL_IsCons'},
				{name: 'EvnPL_UKL'},
				{name: 'EvnPL_IsFirstDisable'},
				{name: 'PrivilegeType_id'},
				{name: 'EvnUslugaCommon_id'},
				{name: 'EvnVizitPL_id'},
				{name: 'EvnVizitPL_Index'},
				{name: 'EvnVizitPL_IsSigned'},
				{name: 'EvnVizitPL_setDate'},
				{name: 'LastEvnVizitPL_setDate'},
				{name: 'LastEvnVizitPL_Diag_id'},
				{name: 'LastEvnVizitPL_Diag_Code'},
				{name: 'EvnVizitPL_setTime'},
				{name: 'EvnVizitPL_IsPaid'},
				{name: 'EvnVizitPL_Time'},
				{name: 'EvnVizitPL_Uet'},
				{name: 'EvnVizitPL_UetOMS'},
                {name: 'HealthKind_id'},
				{name: 'Lpu_oid'},
				{name: 'LpuBuilding_id'},
				{name: 'LpuSection_did'},
				{name: 'MedStaffFact_did'},
				{name: 'LpuSection_id'},
				{name: 'LpuSection_oid'},
				{name: 'LpuUnit_id'},
				{name: 'LpuUnitSet_id'},
				{name: 'MedPersonal_id'},
				{name: 'MedStaffFact_id'},
				{name: 'MedPersonal_sid'},
				{name: 'Org_did'},
				{name: 'PayType_id'},
				{name: 'MedicalCareKind_vid'},
				{name: 'Person_id'},
				{name: 'PersonEvn_id'},
				{name: 'PrehospDirect_id'},
				{name: 'PrehospTrauma_id'},
				{name: 'ProfGoal_id'},
				{name: 'DispClass_id'},
				{name: 'DispProfGoalType_id'},
				{name: 'EvnPLDisp_id'},
				{name: 'PersonDisp_id'},
				{name: 'RankinScale_id'},
				{name: 'ResultClass_id'},
				{name: 'ResultDeseaseType_id'},
				{name: 'TumorStage_id'},
                {name: 'LeaveType_fedid'},
                {name: 'ResultDeseaseType_fedid'},
                {name: 'Lpu_id'},
				{name: 'RiskLevel_id'},
				{name: 'WellnessCenterAgeGroups_id'},
				{name: 'Server_id'},
				{name: 'TreatmentClass_id'},
				{name: 'ServiceType_id'},
				{name: 'UslugaComplex_Code'},
				{name: 'UslugaComplex_uid'},
				{name: 'Mes_id'},
				{name: 'LpuSectionProfile_id'},
				{name: 'EvnVizitPL_Count'},
                {name: 'TimetableGraf_id'},
                {name: 'EvnPrescr_id'},
				{name: 'VizitClass_id'},
				{name: 'VizitType_id'},
				{name: 'MedicalCareKind_id'},
				{name: 'PregnancyEvnVizitPL_Period'},
				{name: 'EvnVizitPL_IsZNO'},
				{name: 'EvnVizitPL_IsZNORemove'},
				{name: 'EvnVizitPL_BiopsyDate'},
				{name: 'PainIntensity_id'},
				{name: 'Diag_spid'},
				{name: 'DrugTherapyScheme_ids'},
				{name: 'PayTypeKAZ_id'},
				{name: 'ScreenType_id'},
				{name: 'UslugaMedType_id'},
				{name: 'VizitActiveType_id'}
			]),
			region: 'center',
			url: '/?c=EvnPL&m=saveEmkEvnPL'
		});

		this.PersonInfo = new sw.Promed.PersonInfoPanel({
			button1OnHide: function() {
				this.FormPanel.getForm().findField('EvnPL_NumCard').focus(true);
			}.createDelegate(this),
			button2Callback: function(callback_data) {
				var base_form = this.FormPanel.getForm();

				base_form.findField('PersonEvn_id').setValue(callback_data.PersonEvn_id);
				base_form.findField('Server_id').setValue(callback_data.Server_id);

				var p = {
					Person_id: callback_data.Person_id,
					Server_id: callback_data.Server_id
				};

				if ( this.PersonEvn_id ) {
					p.PersonEvn_id = this.PersonEvn_id;
				}

				this.PersonInfo.load(p);
			}.createDelegate(this),
			button2OnHide: function() {
				this.PersonInfo.button1OnHide();
			}.createDelegate(this),
			button3OnHide: function() {
				this.PersonInfo.button1OnHide();
			}.createDelegate(this),
			button4OnHide: function() {
				this.PersonInfo.button1OnHide();
			}.createDelegate(this),
			button5OnHide: function() {
				this.PersonInfo.button1OnHide();
			}.createDelegate(this),
			collapsible: true,
			collapsed: true,
			floatable: false,
			id: 'EEPLEF_PersonInformationFrame',
			plugins: [ Ext.ux.PanelCollapsedTitle ],
			region: 'north',
			title: lang['zagruzka'],
			listeners:{
				'render': function(panel) {
					if (panel.header)
					{
						panel.header.on('click',panel.toggleCollapse,panel,false);
					}
				},
				'beforeexpand': function(panel, a){
					if(wnd.action && wnd.action.inlist(['openEvnPL','closeEvnPL']))
					{
						this.setHeight(this.minHeight + 120);
						this.syncSize();
					}
				}.createDelegate(this),
				'beforecollapse': function(panel, a){
					if(wnd.action && wnd.action.inlist(['openEvnPL','closeEvnPL']))
					{
						this.setHeight(this.minHeight);
						this.syncSize();
					}
				}.createDelegate(this)
			},
			titleCollapse: true
		});

		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.doSave({
						print: false,
                        isDoSave: true,
						ignoreEvnUslugaCountCheck: false
					});
				}.createDelegate(this),
				iconCls: 'save16',
				onShiftTabAction: function () {
					var base_form = this.FormPanel.getForm();

					if ( !this.action.inlist([ 'viewEvnPL', 'viewEvnVizitPL' ]) ) {
						if ( !this.findById('EEPLEF_ResultPanel').hidden && !this.findById('EEPLEF_ResultPanel').collapsed ) {
							if ( !base_form.findField('ResultDeseaseType_fedid').disabled && !base_form.findField('ResultDeseaseType_fedid').hidden ) {
								base_form.findField('ResultDeseaseType_fedid').focus(true);
							}
							else if ( !base_form.findField('Lpu_oid').disabled && !base_form.findField('Lpu_oid').hidden ) {
								base_form.findField('Lpu_oid').focus(true);
							}
							else if ( !base_form.findField('LpuSection_oid').disabled && !base_form.findField('LpuSection_oid').hidden ) {
								base_form.findField('LpuSection_oid').focus(true);
							}
							else if ( !base_form.findField('DirectClass_id').disabled && !base_form.findField('DirectClass_id').hidden ) {
								base_form.findField('DirectClass_id').focus(true);
							}
							else {
								base_form.findField('EvnPL_IsFinish').focus(true);
							}
						}
						else if ( !this.findById('EEPLEF_EvnVizitPLPanel').hidden && !this.findById('EEPLEF_EvnVizitPLPanel').collapsed ) {
							if ( !base_form.findField('PersonDisp_id').disabled && !base_form.findField('PersonDisp_id').hidden ) {
								base_form.findField('PersonDisp_id').focus(true);
							}
							else if ( !base_form.findField('DispClass_id').disabled && !base_form.findField('DispClass_id').hidden ) {
								base_form.findField('DispClass_id').focus(true);
							}
							else {
								this.buttons[this.buttons.length - 1].focus();
							}
						}
						else if ( !this.findById('EEPLEF_DirectInfoPanel').hidden && !this.findById('EEPLEF_DirectInfoPanel').collapsed ) {
							if ( !base_form.findField('EvnPL_IsUnport').disabled && !base_form.findField('EvnPL_IsUnport').hidden ) {
								base_form.findField('EvnPL_IsUnport').focus(true);
							}
							else {
								this.buttons[this.buttons.length - 1].focus();
							}
						}
						else {
							this.buttons[this.buttons.length - 1].focus();
						}
					}
					else {
						this.buttons[this.buttons.length - 1].focus();
					}
				}.createDelegate(this),
				onTabAction: function () {
					this.buttons[3].focus();
				}.createDelegate(this),
				tabIndex: TABINDEX_EEPLEF + 63,
				text: BTN_FRMSAVE
			}, /*{
				handler: function() {
					this.printEvnPL();
				}.createDelegate(this),
				iconCls: 'print16',
				onShiftTabAction: function () {
					this.buttons[0].focus();
				}.createDelegate(this),
				onTabAction: function () {
					this.buttons[this.buttons.length - 1].focus();
				}.createDelegate(this),
				tabIndex: TABINDEX_EEPLEF + 64,
				text: BTN_FRMPRINT
			},*/ '-' , HelpButton(this, -1),
			{
				handler: function() {
					this.onCancelAction();
				}.createDelegate(this),
				iconCls: 'cancel16',
				onShiftTabAction: function () {
					this.buttons[0].focus();
				}.createDelegate(this),
				onTabAction: function () {
					var base_form = this.FormPanel.getForm();

					if ( !this.action.inlist([ 'viewEvnPL', 'viewEvnVizitPL' ]) ) {
						if ( !this.findById('EEPLEF_DirectInfoPanel').hidden && !this.findById('EEPLEF_DirectInfoPanel').collapsed ) {
							base_form.findField('EvnPL_NumCard').focus(true);
						}
						else if ( !this.findById('EEPLEF_EvnVizitPLPanel').hidden && !this.findById('EEPLEF_EvnVizitPLPanel').collapsed ) {
							if ( !base_form.findField('EvnVizitPL_setDate').disabled && !base_form.findField('EvnVizitPL_setDate').hidden ) {
								base_form.findField('EvnVizitPL_setDate').focus(true);
							}
							else {
								this.buttons[0].focus();
							}
						}
						else if ( !this.findById('EEPLEF_ResultPanel').hidden && !this.findById('EEPLEF_ResultPanel').collapsed ) {
							base_form.findField('EvnPL_IsFinish').focus(true);
						}
						else {
							this.buttons[0].focus();
						}
					}
					else {
						this.buttons[1].focus();
					}
				}.createDelegate(this),
				tabIndex: TABINDEX_EEPLEF + 65,
				text: BTN_FRMCANCEL
			}],
			items: [
				this.PersonInfo,
				this.FormPanel
			]
		});

		sw.Promed.swEmkEvnPLEditWindow.superclass.initComponent.apply(this, arguments);

		// Инициализируем ссылки на компоненты:
		this._cmbDiag = this.findById('EEPLEF_DiagCombo');
		this._cmbDiagHSNStage = this.findById('EEPLEF_cmbDiagHSNStage');
		this._cmbDiagHSNFuncClass = this.findById('EEPLEF_cmbDiagHSNFuncClass');
		this._cmbComplDiag = this.findById('EEPLEF_cmbComplDiag');
		this._cmbComplDiagHSNStage = this.findById('EEPLEF_cmbComplDiagHSNStage');
		this._cmbComplDiagHSNFuncClass = this.findById('EEPLEF_cmbComplDiagHSNFuncClass');
		this._formPanel = this.findById('EmkEvnPLEditForm');

		if (this._formPanel)
			this._form = this._formPanel.getForm();

		if (this._form)
			setTimeout(
				function()
				{
					this._fldPersonId = this._form.findField('Person_id');
				}.createDelegate(this),
				1);

        this.FormPanel.on('render', function(formPanel){
            formPanel.getForm().findField('ResultClass_id').on('change', function (combo, newValue) {
                var index = combo.getStore().findBy(function (rec) {
                    return (rec.get('ResultClass_id') == newValue);
                });
                combo.fireEvent('select', combo, combo.getStore().getAt(index));
            });
            formPanel.getForm().findField('ResultClass_id').on('select', function (combo, record) {
                var base_form = formPanel.getForm();
				if (!wnd.fo) {
					wnd.calcFedResultDeseaseType();
					wnd.calcFedLeaveType();
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

		this.findById('EEPLEF_DiagCombo').addListener('change', function(combo, newValue, oldValue) {
			var base_form = this.FormPanel.getForm();
			
			var index = combo.getStore().findBy(function(rec) {
				return (rec.get('Diag_id') == newValue);
			});
			this.setDiagConcComboVisible();
			if ( index >= 0 && !Ext.isEmpty(combo.getStore().getAt(index).get('Diag_Code')) && combo.getStore().getAt(index).get('Diag_Code').substr(0, 1).toUpperCase() != 'Z' ) {
				base_form.findField('DeseaseType_id').setAllowBlank(false);
			}
			else {
				base_form.findField('DeseaseType_id').setAllowBlank(true);
			}

			if (regNick == 'ekb') {
				if (base_form.findField('Diag_id').getFieldValue('DiagFinance_IsRankin') && base_form.findField('Diag_id').getFieldValue('DiagFinance_IsRankin') == 2) {
					base_form.findField('RankinScale_id').showContainer();
					var date = base_form.findField('EvnVizitPL_setDate').getValue();
					var xdate = new Date(2016, 0, 1);
					if(!Ext.isEmpty(date) && date < xdate){
						base_form.findField('RankinScale_id').setAllowBlank(false);
					} else {
						base_form.findField('RankinScale_id').setAllowBlank(true);
					}
				} else {
					base_form.findField('RankinScale_id').hideContainer();
					base_form.findField('RankinScale_id').clearValue();
					base_form.findField('RankinScale_id').setAllowBlank(true);
				}
			}
			
			if ( regNick == 'ufa' ) {
				this.findById('EEPLEF_EvnVizitPL_IsZNOCheckbox').disable();
				this.findById('EEPLEF_EvnVizitPL_IsZNOCheckbox').setValue(combo.getFieldValue('Diag_Code') == 'Z03.1');
			}
			else {
				if (regNick != 'krym' && combo.getFieldValue('Diag_Code') && combo.getFieldValue('Diag_Code').search(new RegExp("^(C|D0)", "i")) >= 0) {
					this.findById('EEPLEF_EvnVizitPL_IsZNOCheckbox').setValue(false);
					this.findById('EEPLEF_EvnVizitPL_IsZNOCheckbox').disable();
				} else {
					this.findById('EEPLEF_EvnVizitPL_IsZNOCheckbox').enable();

					if (regNick == 'buryatiya') {
						this.findById('EEPLEF_EvnVizitPL_IsZNOCheckbox').setValue(combo.getFieldValue('Diag_Code') == 'Z03.1');
					}
				}
			}

			this.findById('EEPLEF_EvnVizitPL_IsZNOCheckbox').fireEvent('check', this.findById('EEPLEF_EvnVizitPL_IsZNOCheckbox'), this.findById('EEPLEF_EvnVizitPL_IsZNOCheckbox').getValue());
			
			this.getFinanceSource();

			this.loadSpecificsTree();
			this.checkMesOldUslugaComplexFields();
		}.createDelegate(this));

		this.findById('EEPLEF_LpuSectionCombo').addListener('change', function(combo, newValue, oldValue) {
			this.setDefaultMedicalCareKind();
			this.setLpuSectionProfile();
			this.loadLpuSectionProfileDop();

			var base_form = this.FormPanel.getForm();
			var uslugacomplex_combo = base_form.findField('UslugaComplex_uid');

			if ( regNick == 'kareliya' && Ext.isEmpty(base_form.findField('MedicalCareKind_id').getValue()) ) {
				if (combo.getFieldValue('LpuSectionProfile_Code') && combo.getFieldValue('LpuSectionProfile_Code') == '57') {
					base_form.findField('MedicalCareKind_id').setFieldValue('MedicalCareKind_Code', 8);
				} else {
					base_form.findField('MedicalCareKind_id').setFieldValue('MedicalCareKind_Code', 1);
				}
			}

			if ( regNick.inlist(['ufa','buryatiya']) ) {

				if ( regNick == 'ufa' && !newValue ) {
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

				uslugacomplex_combo.lastQuery = 'This query sample that is not will never appear';
				uslugacomplex_combo.getStore().removeAll();
				uslugacomplex_combo.getStore().baseParams.query = '';

				if ( record ) {
					if (regNick == 'ufa') {
						uslugacomplex_combo.setLpuLevelCode(record.get('LpuSectionProfile_Code'));
					} else if (regNick.inlist(['buryatiya', 'perm', 'pskov'])) {
						uslugacomplex_combo.setLpuSectionProfile_id(record.get('LpuSectionProfile_id'));
					}
					wnd.reloadUslugaComplexField();
				}
			} else if (regNick == 'ekb') {
				uslugacomplex_combo.lastQuery = 'This query sample that is not will never appear';
				uslugacomplex_combo.getStore().removeAll();
				uslugacomplex_combo.getStore().baseParams.query = '';

				uslugacomplex_combo.getStore().baseParams.LpuSection_id = newValue;
				this.loadMesCombo();
				wnd.reloadUslugaComplexField();
			} else if (regNick == 'kz') {
				uslugacomplex_combo.lastQuery = 'This query sample that is not will never appear';
				uslugacomplex_combo.getStore().removeAll();
				uslugacomplex_combo.getStore().baseParams.query = '';

				uslugacomplex_combo.getStore().baseParams.LpuSection_id = newValue;
				wnd.reloadUslugaComplexField();
			}
		}.createDelegate(this));
		
		this.findById('EEPLEF_MidMedPersonalCombo').addListener('change', function(combo, newValue, oldValue) {
			this.setDefaultMedicalCareKind();
		}.createDelegate(this));

		this.findById('EEPLEF_MedPersonalCombo').addListener('change', function(combo, newValue, oldValue) {
			this.setDefaultMedicalCareKind();
			this.loadLpuSectionProfileDop();

			if (regNick == 'ufa') {
				var base_form = this.FormPanel.getForm();

				var uslugacomplex_combo = base_form.findField('UslugaComplex_uid');

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

				uslugacomplex_combo.lastQuery = 'This query sample that is not will never appear';
				uslugacomplex_combo.getStore().removeAll();
				uslugacomplex_combo.getStore().baseParams.query = '';

				if ( record ) {
					uslugacomplex_combo.setLpuLevelCode(record.get('LpuSectionProfile_Code'));
					wnd.reloadUslugaComplexField();
				}
			} else if (regNick.inlist(['buryatiya','perm','pskov'])) {
				var base_form = this.FormPanel.getForm();

				var uslugacomplex_combo = base_form.findField('UslugaComplex_uid');

				var uslugacomplex_uid = uslugacomplex_combo.getValue();
				uslugacomplex_combo.setLpuSectionProfile_id(base_form.findField('LpuSection_id').getFieldValue('LpuSectionProfile_id'));
				wnd.reloadUslugaComplexField(uslugacomplex_uid);
			} else if (regNick == 'ekb') {
				var base_form = this.FormPanel.getForm();

				var uslugacomplex_combo = base_form.findField('UslugaComplex_uid');

				uslugacomplex_combo.lastQuery = 'This query sample that is not will never appear';
				uslugacomplex_combo.getStore().removeAll();
				uslugacomplex_combo.getStore().baseParams.query = '';

				uslugacomplex_combo.getStore().baseParams.LpuSection_id = base_form.findField('LpuSection_id').getValue();
				uslugacomplex_combo.getStore().baseParams.MedPersonal_id = combo.getFieldValue('MedPersonal_id');

				this.filterLpuSectionProfile();
				this.reloadUslugaComplexField();
			} else if (regNick == 'kz') {
				var base_form = this.FormPanel.getForm();

				var uslugacomplex_combo = base_form.findField('UslugaComplex_uid');

				uslugacomplex_combo.lastQuery = 'This query sample that is not will never appear';
				uslugacomplex_combo.getStore().removeAll();
				uslugacomplex_combo.getStore().baseParams.query = '';

				uslugacomplex_combo.getStore().baseParams.LpuSection_id = base_form.findField('LpuSection_id').getValue();
			}
		}.createDelegate(this));
	},
	filterLpuSectionProfile: function() {
		var base_form = this.FormPanel.getForm();

		/*if (getRegionNick() == 'ekb') {
			var combo = base_form.findField('MedStaffFact_id');
			base_form.findField('LpuSectionProfile_id').lastQuery = 'This query sample that is not will never appear';
			base_form.findField('LpuSectionProfile_id').getStore().removeAll();
			base_form.findField('LpuSectionProfile_id').getStore().load({
				params: {
					LpuSection_id: base_form.findField('LpuSection_id').getValue(),
					MedPersonal_id: combo.getFieldValue('MedPersonal_id'),
					onDate: Ext.util.Format.date(base_form.findField('EvnVizitPL_setDate').getValue(), 'd.m.Y'),
					LpuSectionProfileGRAPP_CodeIsNotNull: (base_form.findField('PayType_id').getFieldValue('PayType_SysNick') == 'oms' ? 1 : null)
				},
				callback: function () {
					var id = base_form.findField('LpuSectionProfile_id').getValue();
					var index = base_form.findField('LpuSectionProfile_id').getStore().find('LpuSectionProfile_id', id);
					if (index >= 0) {
						base_form.findField('LpuSectionProfile_id').setValue(id);
					} else {
						base_form.findField('LpuSectionProfile_id').clearValue();
					}
				}
			});
		}*/
	},
	setLpuSectionProfile: function() {
		/*if ( getGlobalOptions().region && getGlobalOptions().region.nick.inlist(['ekb']) ) {
			var base_form = this.FormPanel.getForm();
			
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
			var current_window = Ext.getCmp('EmkEvnPLEditWindow');

			switch ( e.getKey() ) {
				case Ext.EventObject.C:
					current_window.doSave({ignoreEvnUslugaCountCheck: false,isDoSave: true});
				break;

				case Ext.EventObject.G:
					current_window.printEvnPL();
				break;

				case Ext.EventObject.J:
					current_window.onCancelAction();
				break;
			}
		},
		key: [
			Ext.EventObject.C,
			Ext.EventObject.G,
			Ext.EventObject.J
		],
		stopEvent: true
	}, {
		alt: false,
		fn: function(inp, e) {
			var current_window = Ext.getCmp('EmkEvnPLEditWindow');

			switch ( e.getKey() ) {
				case Ext.EventObject.F6:
					current_window.PersonInfo.panelButtonClick(1);
				break;

				case Ext.EventObject.F10:
					current_window.PersonInfo.panelButtonClick(2);
				break;

				case Ext.EventObject.F11:
					current_window.PersonInfo.panelButtonClick(3);
				break;

				case Ext.EventObject.F12:
					if ( e.ctrlKey == true ) {
						current_window.PersonInfo.panelButtonClick(5);
					}
					else {
						current_window.PersonInfo.panelButtonClick(4);
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
			win.onHide({
				EvnUslugaGridIsModified: win.EvnUslugaGridIsModified
			});
		}
	},
	modal: true,
	onCancelAction: function() {
		var evn_pl_id = this.FormPanel.getForm().findField('EvnPL_id').getValue();
		var evn_vizit_pl_id = this.FormPanel.getForm().findField('EvnVizitPL_id').getValue();

		if ( evn_vizit_pl_id > 0 && evn_pl_id > 0 && this.action.inlist([ 'addEvnPL', 'addEvnVizitPL' ]) ) {
			// удалить посещение
			// закрыть окно после успешного удаления
			var deleteEvnVizitPL = function (callback) {
				var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Удаление посещения..."});
				loadMask.show();
				Ext.Ajax.request({
					callback: function(options, success, response) {
						loadMask.hide();

						if ( success ) {
							this.hide();
							callback();
						}
						else {
							sw.swMsg.alert(lang['oshibka'], lang['pri_udalenii_posescheniya_voznikli_oshibki']);
						}
					}.createDelegate(this),
					params: {
						Evn_id: evn_vizit_pl_id
					},
					url: '/?c=Evn&m=deleteEvn'
				});
			}.createDelegate(this);
			
			if(this.confirmDeleteEvnVizitPL) {
				sw.swMsg.show({
					buttons: Ext.Msg.YESNO,
					fn: function(buttonId, text, obj) {
						if ( buttonId == 'yes' ) {
							this.doSave({ignoreEvnUslugaCountCheck: false, isDoSave: true});
						}
						if ( buttonId == 'no' ) {
							deleteEvnVizitPL(function () {
								var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Удаление талона амбулаторного пациента..."});
								loadMask.show();
								Ext.Ajax.request({
									callback: function(options, success, response) {
										loadMask.hide();

										if ( success ) {
											this.hide();
										}
										else {
											sw.swMsg.alert(lang['oshibka'], lang['pri_udalenii_talona_ambulatornogo_patsienta_voznikli_oshibki']);
										}
									}.createDelegate(this),
									params: {
										Evn_id: evn_pl_id
									},
									url: '/?c=Evn&m=deleteEvn'
								});
							}.createDelegate(this));
						}
					}.createDelegate(this),
					icon: Ext.MessageBox.QUESTION,
					msg: lang['dokument_ne_budet_sohranen_sohranit'],
					title: lang['vopros']
				});
			} else {
				deleteEvnVizitPL(Ext.emptyFn);
			}
		}
		else {
			this.hide();
		}
	},
	onHide: Ext.emptyFn,
	openEvnDiagPLEditWindow: function(action) {
		if ( typeof action != 'string' || !(action.inlist([ 'add', 'edit', 'view' ])) ) {
			return false;
		}

		var base_form = this.FormPanel.getForm();
		var grid = this.findById('EEPLEF_EvnDiagPLGrid');

		if ( this.action == 'view' ) {
			if ( action == 'add' ) {
				return false;
			}
			else if ( action == 'edit' ) {
				action = 'view';
			}
		}

		if ( getWnd('swEvnDiagPLEditWindow').isVisible() ) {
			sw.swMsg.alert(lang['soobschenie'], lang['okno_redaktirovaniya_diagnoza_uje_otkryito']);
			return false;
		}

		if ( action == 'add' && base_form.findField('EvnVizitPL_id').getValue() == 0 ) {
			this.doSave({
				ignoreEvnUslugaCountCheck: true,
				openChildWindow: function() {
					this.openEvnDiagPLEditWindow(action);
				}.createDelegate(this)
			});
			return false;
		}

		var params = new Object();

		var person_id = base_form.findField('Person_id').getValue();
		var person_birthday = this.PersonInfo.getFieldValue('Person_Birthday');
		var person_firname = this.PersonInfo.getFieldValue('Person_Firname');
		var person_secname = this.PersonInfo.getFieldValue('Person_Secname');
		var person_surname = this.PersonInfo.getFieldValue('Person_Surname');

		var record;
		var vizit_combo_data = new Array();

		var evn_vizit_pl_id = base_form.findField('EvnVizitPL_id').getValue();
		var evn_vizit_pl_set_date = base_form.findField('EvnVizitPL_setDate').getValue();
		var lpu_section_name = '';
		var lpu_section_id = base_form.findField('LpuSection_id').getValue();
		var med_personal_fio = '';
		var med_personal_id = null;
		var med_staff_fact_id = base_form.findField('MedStaffFact_id').getValue();

		if ( action == 'add' ) {
			params.EvnDiagPL_id = 0;
			params.EvnVizitPL_id = evn_vizit_pl_id;
			params.Person_id = base_form.findField('Person_id').getValue();
			params.PersonEvn_id = base_form.findField('PersonEvn_id').getValue();
			params.Server_id = base_form.findField('Server_id').getValue();
		}
		else {
			var selected_record = grid.getSelectionModel().getSelected();

			if ( !selected_record || !selected_record.get('EvnDiagPL_id') ) {
				return false;
			}

			if ( selected_record.get('accessType') != 'edit' ) {
				action = 'view';
			}

			params = selected_record.data;
		}

		// Формируем vizit_combo_data
		record = base_form.findField('LpuSection_id').getStore().getById(lpu_section_id);
		if ( record ) {
			lpu_section_name = record.get('LpuSection_Name');
		}

		record = base_form.findField('MedStaffFact_id').getStore().getById(med_staff_fact_id);
		if ( record ) {
			med_personal_fio = record.get('MedPersonal_Fio');
			med_personal_id = record.get('MedPersonal_id');
		}

		if ( !evn_vizit_pl_set_date || !lpu_section_id || !med_personal_id ) {
			sw.swMsg.alert(lang['soobschenie'], lang['ne_zadanyi_obyazatelnyie_parametryi_posescheniya']);
			return false;
		}

		vizit_combo_data.push({
			EvnVizitPL_id: evn_vizit_pl_id,
			LpuSection_id: lpu_section_id,
			MedPersonal_id: med_personal_id,
			EvnVizitPL_Name: Ext.util.Format.date(evn_vizit_pl_set_date, 'd.m.Y') + ' / ' + lpu_section_name + ' / ' + med_personal_fio,
			EvnVizitPL_setDate: evn_vizit_pl_set_date
		});

		getWnd('swEvnDiagPLEditWindow').show({
			action: action,
			callback: function(data) {
				if ( !data || !data.evnDiagPLData ) {
					return false;
				}

				var record = grid.getStore().getById(data.evnDiagPLData[0].EvnDiagPL_id);

				if ( !record ) {
					if ( grid.getStore().getCount() == 1 && !grid.getStore().getAt(0).get('EvnDiagPL_id') ) {
						grid.getStore().removeAll();
					}

					grid.getStore().loadData(data.evnDiagPLData, true);
				}
				else {
					var grid_fields = new Array();
					var i = 0;

					grid.getStore().fields.eachKey(function(key, item) {
						grid_fields.push(key);
					});

					for ( i = 0; i < grid_fields.length; i++ ) {
						record.set(grid_fields[i], data.evnDiagPLData[0][grid_fields[i]]);
					}

					record.commit();
				}
				
				this.loadSpecificsTree();
			}.createDelegate(this),
			formParams: params,
			onHide: function() {
				grid.getView().focusRow(0);
				grid.getSelectionModel().selectFirstRow();
			}.createDelegate(this),
			Person_id: person_id,
			Person_Birthday: person_birthday,
			Person_Firname: person_firname,
			Person_Secname: person_secname,
			Person_Surname: person_surname,
			vizitComboData: vizit_combo_data
		});
	},
	openEvnUslugaEditWindow: function(action) {

		// Переменная для региональных настроек:
		var regNick = getRegionNick();

		if (regNick == 'pskov') {
			return false;
		}

		if ( typeof action != 'string' || !action.inlist([ 'add', 'addOper', 'edit', 'view']) ) {
			return false;
		}
		var win = this;
		var base_form = this.FormPanel.getForm();
		var grid = this.findById('EEPLEF_EvnUslugaGrid');

		if ( this.action == 'view') {
			if ( action == 'add' || action == 'addOper' ) {
				return false;
			}
			else if ( action == 'edit' ) {
				action = 'view';
			}
		}

		var params = new Object();

		params.notFilterByEvnVizitMes = 1;
		if (!Ext.isEmpty(base_form.findField('Mes_id').getValue())) {
			params.MesOldVizit_id = base_form.findField('Mes_id').getValue();
		} else {
			params.MesOldVizit_id = null;
		}
		
		params.action = action;
		params.callback = function(data) {
			if ( true || !data || !data.evnUslugaData ) {
				grid.getStore().load({
					params: {
						pid: base_form.findField('EvnVizitPL_id').getValue()
					},
					callback: function() {
						//win.checkAbort();
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
				var grid_fields = new Array();
				var i = 0;

				grid.getStore().fields.eachKey(function(key, item) {
					grid_fields.push(key);
				});

				for ( i = 0; i < grid_fields.length; i++ ) {
					record.set(grid_fields[i], data.evnUslugaData[grid_fields[i]]);
				}

				record.commit();
			}

			this.EvnUslugaGridIsModified = true;
			this.uetValuesRecount();
			this.checkMesOldUslugaComplexFields();
			
		}.createDelegate(this);
		params.onHide = function() {
			grid.getView().focusRow(0);
			grid.getSelectionModel().selectFirstRow();
		}.createDelegate(this);
		params.parentClass = 'EvnVizit';
		params.Person_id = base_form.findField('Person_id').getValue();
		params.Person_Birthday = this.findById('EEPLEF_PersonInformationFrame').getFieldValue('Person_Birthday');
		params.Person_Firname = this.findById('EEPLEF_PersonInformationFrame').getFieldValue('Person_Firname');
		params.Person_Secname = this.findById('EEPLEF_PersonInformationFrame').getFieldValue('Person_Secname');
		params.Person_Surname = this.findById('EEPLEF_PersonInformationFrame').getFieldValue('Person_Surname');

		if (regNick == 'perm') {
			var lastSetDate = getValidDT(base_form.findField('EvnVizitPL_setDate').getValue(), '00:00');

			if (Ext.isArray(this.OtherVizitList)) {
				this.OtherVizitList.forEach(function(vizit) {
					var setDate = getValidDT(vizit.EvnVizitPL_setDate, '00:00');
					if (setDate && setDate > lastSetDate) lastSetDate = setDate;
				});
			}
			if (Ext.isArray(this.OtherUslugaList)) {
				this.OtherUslugaList.forEach(function(usluga) {
					var setDate = getValidDT(usluga.EvnUsluga_setDate, '00:00');
					if (setDate && setDate > lastSetDate) lastSetDate = setDate;
				});
			}

			params.UslugaComplex_Date = Ext.util.Format.date(lastSetDate, 'd.m.Y');
		}

		var getUslugaComplexDate = function(evn_usluga_id) {
			var lastSetDate = null;
			if (!Ext.isEmpty(params.UslugaComplex_Date)) {
				lastSetDate = getValidDT(params.UslugaComplex_Date, '00:00');
			}
			grid.getStore().each(function(record){
				if (Ext.isEmpty(evn_usluga_id) || record.get('EvnUsluga_id') != evn_usluga_id) {
					var setDate = record.get('EvnUsluga_setDate');
					if (lastSetDate < setDate) lastSetDate = setDate;
				}
			});
			return lastSetDate?Ext.util.Format.date(lastSetDate, 'd.m.Y'):null;
		};

		// Собрать данные для ParentEvnCombo
		var parent_evn_combo_data = new Array();

		// Формируем parent_evn_combo_data
		var
			evn_vizit_id = base_form.findField('EvnVizitPL_id').getValue(),
			evn_vizit_set_date = base_form.findField('EvnVizitPL_setDate').getValue(),
			evn_vizit_set_time = base_form.findField('EvnVizitPL_setTime').getValue(),
			index,
			lpu_section_id = base_form.findField('LpuSection_id').getValue(),
			lpu_section_name = '',
			lpu_section_profile_id = base_form.findField('LpuSectionProfile_id').getValue(),
			med_personal_fio = '',
			med_personal_id,
			med_staff_fact_id = base_form.findField('MedStaffFact_id').getValue(),
			service_type_sysnick = base_form.findField('ServiceType_id').getFieldValue('ServiceType_SysNick'),
			vizit_type_sysnick = base_form.findField('VizitType_id').getFieldValue('VizitType_SysNick'),
			diag_id = base_form.findField('Diag_id').getValue();

		if ( action == 'add' && (!evn_vizit_set_date || !lpu_section_id || !med_staff_fact_id) ) {
			sw.swMsg.alert(lang['oshibka'], lang['ne_zapolnenyi_obyazatelnyie_polya_po_posescheniyu']);
			return false;
		}

		index = base_form.findField('LpuSection_id').getStore().findBy(function(rec) {
			return (rec.get('LpuSection_id') == lpu_section_id);
		});

		if ( index >= 0 ) {
			lpu_section_name = base_form.findField('LpuSection_id').getStore().getAt(index).get('LpuSection_Name');
		}

		index = base_form.findField('MedStaffFact_id').getStore().findBy(function(rec) {
			return (rec.get('MedStaffFact_id') == med_staff_fact_id);
		});

		if ( index >= 0 ) {
			med_personal_fio = base_form.findField('MedStaffFact_id').getStore().getAt(index).get('MedPersonal_Fio');
			med_personal_id = base_form.findField('MedStaffFact_id').getStore().getAt(index).get('MedPersonal_id');
		}

		parent_evn_combo_data.push({
			Evn_id: evn_vizit_id,
			Evn_Name: Ext.util.Format.date(evn_vizit_set_date, 'd.m.Y') + ' / ' + lpu_section_name + ' / ' + med_personal_fio,
			Evn_setDate: evn_vizit_set_date,
			Evn_setTime: evn_vizit_set_time,
			MedStaffFact_id: med_staff_fact_id,
			LpuSection_id: lpu_section_id,
			LpuSectionProfile_id: lpu_section_profile_id,
			MedPersonal_id: med_personal_id,
			ServiceType_SysNick: service_type_sysnick,
			VizitType_SysNick: vizit_type_sysnick,
			Diag_id: diag_id,
			UslugaComplex_Code: base_form.findField('UslugaComplex_uid').getFieldValue('UslugaComplex_Code')
		});

		switch ( action ) {
			case 'add':
			case 'addOper':
				if ( base_form.findField('EvnVizitPL_id').getValue() == 0 ) {
					this.doSave({
						ignoreEvnUslugaCountCheck: true,
						ignoreMesUslugaCheck: true,
						openChildWindow: function() {
							this.openEvnUslugaEditWindow(action);
						}.createDelegate(this)
					});
					return false;
				}

				if (regNick == 'perm') {
					params.UslugaComplex_Date = getUslugaComplexDate();
				}

                params.action = 'add';
				params.formParams = {
					Diag_id: base_form.findField('Diag_id').getValue(),
					PayType_id: base_form.findField('PayType_id').getValue(),
					Person_id: base_form.findField('Person_id').getValue(),
					PersonEvn_id: base_form.findField('PersonEvn_id').getValue(),
					Server_id: base_form.findField('Server_id').getValue()
				}
				params.parentEvnComboData = parent_evn_combo_data;

				if ( action == 'addOper' ){
					params.formParams.EvnUslugaOper_id = 0;
					params.formParams.EvnUslugaOper_pid = base_form.findField('EvnVizitPL_id').getValue();

					getWnd('swEvnUslugaOperEditWindow').show(params);
				}
				else {
					params.formParams.EvnUslugaCommon_id = 0;
					params.formParams.EvnUslugaCommon_pid = base_form.findField('EvnVizitPL_id').getValue();

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

				if (regNick == 'perm') {
					params.UslugaComplex_Date = getUslugaComplexDate(evn_usluga_id);
				}

				switch ( selected_record.get('EvnClass_SysNick') ) {
					case 'EvnUslugaCommon':
						params.formParams = {
							EvnUslugaCommon_id: evn_usluga_id
						}
						params.parentEvnComboData = parent_evn_combo_data;

						getWnd('swEvnUslugaEditWindow').show(params);
					break;

					case 'EvnUslugaOper':
						params.formParams = {
							EvnUslugaOper_id: evn_usluga_id
						}
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
				/*
				if ( getWnd('swEvnUslugaEditWindow').isVisible() ) {
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
	plain: false,
	printEvnPL: function() {
		sw.swMsg.alert(lang['soobschenie'], lang['pechat_vremenno_nedostupna']);
		return false;

		if ( this.action.inlist([ 'addEvnPL', 'addEvnVizitPL', 'openEvnPL', 'closeEvnPL', 'editEvnPL', 'editEvnVizitPL' ]) ) {
			this.doSave({
				print: true
			});
		}
		else {
			var id = this.FormPanel.getForm().findField('EvnPL_id').getValue();
			if (getRegionNick() == 'penza'){ //https://redmine.swan.perm.ru/issues/63097
				printBirt({
					'Report_FileName': 'EvnPLPrint.rptdesign',
					'Report_Params': '&paramEvnPL=' + id,
					'Report_Format': 'pdf'
				});
			}
			else
				window.open('/?c=EvnPL&m=printEvnPL&EvnPL_id=' + id, '_blank');
		}
	},
	resizable: false,
	checkIsAssignNasel: function() {
		if (getRegionNick() == 'astra') {
			var win = this;
			var base_form = this.FormPanel.getForm();
			var checked = base_form.findField('EvnPL_IsCons').checked;
			base_form.findField('EvnPL_IsCons').hide();
			base_form.findField('EvnPL_IsCons').setValue(false);

			// проверка имеет ли МО приписное население
			Ext.Ajax.request({
				failure: function (response, options) {
					sw.swMsg.alert(lang['oshibka'], lang['pri_poluchenii_znacheniya_priznaka_mo_imeet_pripisnoe_naselenie_voznikli_oshibki']);
				},
				params: {
					Person_id: base_form.findField('Person_id').getValue(),
					Lpu_id: getGlobalOptions().lpu_id
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
	setDefaultMedicalCareKind: function() {
		// Переменная для региональных настроек:
		var regNick = getRegionNick();

		if (regNick == 'kz') { // для Казахстана поле не нужно
			return;
		}

		var base_form = this.FormPanel.getForm();

		// устанавливаем только при добавлении
		if ((this.action == 'addEvnVizitPL' || this.action == 'addEvnPL') && !regNick.inlist([ 'ekb', 'kareliya', 'ufa' ])) {
			// Если специальность врача из случая средняя, то вид мед. помощи = 11
			if  (
				base_form.findField('MedStaffFact_id').getFieldValue('FedMedSpec_Code') == 204
				|| base_form.findField('MedStaffFact_id').getFieldValue('FedMedSpecParent_Code') == 204
			) {
				base_form.findField('MedicalCareKind_vid').setFieldValue('MedicalCareKind_Code', '11');
			}
			// Если специальность врача из случая врачебная и равна 16, 22, 27 (терапевт, педиатр или ВОП), то вид мед. помощи = 12
			else if (
				!Ext.isEmpty(base_form.findField('MedStaffFact_id').getFieldValue('FedMedSpec_Code'))
				&& base_form.findField('MedStaffFact_id').getFieldValue('FedMedSpec_Code').toString().inlist([ '16', '22', '27' ])
			) {
				base_form.findField('MedicalCareKind_vid').setFieldValue('MedicalCareKind_Code', '12');
			}
			// 13 – В остальных случаях
			else {
				base_form.findField('MedicalCareKind_vid').setFieldValue('MedicalCareKind_Code', '13');
			}
		}
		
		if (regNick == 'ufa' ) {
			var LpuSectionProfile_id = base_form.findField('LpuSectionProfile_id').getValue();
			base_form.findField('MedicalCareKind_vid').getStore().findBy(function(rec) {
				swMedicalCareKindLpuSectionProfileGlobalStore.findBy(function(r) {
					if (r.get('LpuSectionProfile_id') == LpuSectionProfile_id && r.get('MedicalCareKind_id') == rec.get('MedicalCareKind_id')) {
						base_form.findField('MedicalCareKind_vid').setValue(r.get('MedicalCareKind_id'));
					}
				});
			});
		}

		if (regNick == 'kareliya') {
			var FedMedSpec_id = base_form.findField('MedStaffFact_id').getFieldValue('FedMedSpec_id');
			base_form.findField('MedicalCareKind_vid').getStore().findBy(function(rec) {
				swMedSpecLinkGlobalStore.findBy(function(r) {
					if (r.get('MedSpec_id') == FedMedSpec_id && r.get('MedicalCareKind_id') == rec.get('MedicalCareKind_id')) {
						base_form.findField('MedicalCareKind_vid').setValue(r.get('MedicalCareKind_id'));
					}
				});
			});
		}

		// @task https://redmine.swan.perm.ru/issues/84712
		// @task https://redmine.swan.perm.ru//issues/109385
		if (regNick == 'ekb') {
			var
				Diag_Code = base_form.findField('Diag_id').getFieldValue('Diag_Code'),
				FedMedSpec_Code = base_form.findField('MedStaffFact_id').getFieldValue('FedMedSpec_Code'),
				FedMedSpecParent_Code = base_form.findField('MedStaffFact_id').getFieldValue('FedMedSpecParent_Code'),
				PayType_SysNick = base_form.findField('PayType_id').getFieldValue('PayType_SysNick'),
				MedicalCareKind_Code = base_form.findField('LpuSection_id').getFieldValue('MedicalCareKind_Code');

			if ( PayType_SysNick == 'bud' && !Ext.isEmpty(MedicalCareKind_Code) && !MedicalCareKind_Code.toString().inlist([ '4', '11', '12', '13' ]) ) {
				base_form.findField('MedicalCareKind_vid').setFieldValue('MedicalCareKind_Code', MedicalCareKind_Code);
			}
			else if ( Diag_Code == 'Z51.5' ) {
				base_form.findField('MedicalCareKind_vid').setFieldValue('MedicalCareKind_Code', '4');
			}
			else if ( !Ext.isEmpty(FedMedSpecParent_Code) ) {
				if ( FedMedSpecParent_Code.toString() == '204' ) { // если HIGH = 204;
					base_form.findField('MedicalCareKind_vid').setFieldValue('MedicalCareKind_Code', '11');
				} else { // если HIGH=0;
					if ( FedMedSpec_Code && FedMedSpec_Code.toString().inlist([ '16', '22', '27' ]) ) {
						base_form.findField('MedicalCareKind_vid').setFieldValue('MedicalCareKind_Code', '12');
					}
					else {
						base_form.findField('MedicalCareKind_vid').setFieldValue('MedicalCareKind_Code', '13');
					}
				}
			}
			else {
				base_form.findField('MedicalCareKind_vid').clearValue();
			}
		}
	},
	getLastEvnVizitPLData: function() {
		var
			base_form = this.FormPanel.getForm(),
			EvnVizitPL_setDate = base_form.findField('EvnVizitPL_setDate').getValue(),
			i,
			result;

		if ( Ext.isEmpty(this.OtherVizitList) || typeof this.OtherVizitList != 'object' || this.OtherVizitList.length == 0 ) {
			return EvnVizitPL_setDate;
		}

		result = EvnVizitPL_setDate;

		for ( i in this.OtherVizitList ) {
			if ( !Ext.isEmpty(this.OtherVizitList[i].EvnVizitPL_setDate) && Date.parseDate(this.OtherVizitList[i].EvnVizitPL_setDate, 'd.m.Y') > result ) {
				result = Date.parseDate(this.OtherVizitList[i].EvnVizitPL_setDate, 'd.m.Y');
			}
		}

		return result;
	},
	calcFedLeaveType: function() {
		var base_form = this.FormPanel.getForm();

		var lastEvnVizitPLData = this.getLastEvnVizitPLData();

		sw.Promed.EvnPL.calcFedLeaveType({
			is2016: Ext.isEmpty(lastEvnVizitPLData) || lastEvnVizitPLData >= sw.Promed.EvnPL.getDateX2016(),
			disableToogleContainer: false,
			InterruptLeaveType_id: base_form.findField('InterruptLeaveType_id').getValue(),
			LeaveType_fedid: base_form.findField('ResultClass_id').getFieldValue('LeaveType_fedid'),
			ResultClass_Code: base_form.findField('ResultClass_id').getFieldValue('ResultClass_Code'),
			DirectType_Code: base_form.findField('DirectType_id').getFieldValue('DirectType_Code'),
			DirectClass_Code: base_form.findField('DirectClass_id').getFieldValue('DirectClass_Code'),
			IsFinish: base_form.findField('EvnPL_IsFinish').getValue(),
			fieldFedLeaveType: base_form.findField('LeaveType_fedid')
		});
	},
	calcFedResultDeseaseType: function() {
		var base_form = this.FormPanel.getForm();

		var lastEvnVizitPLData = this.getLastEvnVizitPLData();

		sw.Promed.EvnPL.calcFedResultDeseaseType({
			is2016: Ext.isEmpty(lastEvnVizitPLData) || lastEvnVizitPLData >= sw.Promed.EvnPL.getDateX2016(),
			disableToogleContainer: false,
			InterruptLeaveType_id: base_form.findField('InterruptLeaveType_id').getValue(),
			DirectType_Code: base_form.findField('DirectType_id').getFieldValue('DirectType_Code'),
			ResultClass_Code: base_form.findField('ResultClass_id').getFieldValue('ResultClass_Code'),
			IsFinish: base_form.findField('EvnPL_IsFinish').getValue(),
			fieldFedResultDeseaseType: base_form.findField('ResultDeseaseType_fedid')
		});
	},
	setDiagFidAndLid: function() {
		// автоматически проставляем предварительный и заключительный диагнозы на основе первого и последнего посещения
		if (getRegionNick() != 'ufa') {
			return false;
		}

		var base_form = this.FormPanel.getForm();
		if (base_form.findField('EvnPL_IsFinish').getValue() != 2) {
			base_form.findField('Diag_fid').clearValue(); // предварительный
			base_form.findField('Diag_lid').clearValue(); // заключительный
			this.setDiagConcComboVisible();
			return true;
		}

		if (base_form.findField('Diag_id').getValue()) {
			base_form.findField('Diag_fid').getStore().load({
				callback: function () {
					base_form.findField('Diag_fid').setValue(base_form.findField('Diag_id').getValue());
				},
				params: {where: "where DiagLevel_id = 4 and Diag_id = " + base_form.findField('Diag_id').getValue()}
			});
		}

		if (!base_form.findField('Diag_lid').getValue()) {
			if (base_form.findField('LastEvnVizitPL_Diag_id').getValue()) {
				base_form.findField('Diag_lid').getStore().load({
					callback: function () {
						base_form.findField('Diag_lid').setValue(base_form.findField('LastEvnVizitPL_Diag_id').getValue());
						this.setDiagConcComboVisible();
					}.createDelegate(this),
					params: {where: "where DiagLevel_id = 4 and Diag_id = " + base_form.findField('LastEvnVizitPL_Diag_id').getValue()}
				});
			} else if (base_form.findField('Diag_id').getValue()) {
				base_form.findField('Diag_lid').getStore().load({
					callback: function () {
						base_form.findField('Diag_lid').setValue(base_form.findField('Diag_id').getValue());
						this.setDiagConcComboVisible();
					}.createDelegate(this),
					params: {where: "where DiagLevel_id = 4 and Diag_id = " + base_form.findField('Diag_id').getValue()}
				});
			}
		}
	},
	onEnableEdit: function() {
		// поля предварит. диагноз и поле заключ. диагноз для Уфы недоступны для редактирования
		var base_form = this.FormPanel.getForm();
		if (getRegionNick() == 'ufa') {
			base_form.findField('Diag_fid').disable(); // предварительный
			base_form.findField('Diag_lid').disable(); // заключительный
		}
	},
	loadSpecificsTree: function() {
		var tree = this.findById('EEPLEF_SpecificsTree');
		var root = tree.getRootNode();
		var win = this;
		
		if (win.specLoading) {
			clearTimeout(win.specLoading);
		};
		
		win.specLoading = setTimeout(function() {
			
			var base_form = this.FormPanel.getForm();
			
			var Diag_ids = [];
			if (base_form.findField('Diag_id').getValue() && base_form.findField('Diag_id').getFieldValue('Diag_Code')) {
				Diag_ids.push([base_form.findField('Diag_id').getValue(), 1, base_form.findField('Diag_id').getFieldValue('Diag_Code'), '']);
			}
			this.findById('EEPLEF_EvnDiagPLGrid').getStore().each(function(record) {
				if(record.get('Diag_id')) {
					Diag_ids.push([record.get('Diag_id'), 0, record.get('Diag_Code'), record.get('EvnDiagPL_id').toString()]);
				}
			});
			tree.getLoader().baseParams.Diag_ids = Ext.util.JSON.encode(Diag_ids);
			tree.getLoader().baseParams.Person_id = base_form.findField('Person_id').getValue();
			tree.getLoader().baseParams.EvnVizitPL_id = base_form.findField('EvnVizitPL_id').getValue();
			tree.getLoader().baseParams.allowCreateButton = (this.action != 'view');
			tree.getLoader().baseParams.allowDeleteButton = (this.action != 'view');
			
			if (!root.expanded) {
				root.expand();
			} else {
				var spLoadMask = new Ext.LoadMask(this.getEl(), { msg: "Загрузка специфик..." });
				spLoadMask.show();
				tree.getLoader().load(root, function() {
					spLoadMask.hide();
				});
			}
		}.createDelegate(this), 100);
	},
	checkZNO: function(options){
		if(getRegionNick()!='ekb') return;
		var win = this,
			base_form = win.FormPanel.getForm(),
			person_id = base_form.findField('Person_id'),
			Evn_id = base_form.findField('EvnVizitPL_id');

		var params = new Object();
		params.object = 'EvnVizitPL';

		if ( !Ext.isEmpty(person_id.getValue()) ) {
			params.Person_id = person_id.getValue();
		}
		
		if ( !Ext.isEmpty(Evn_id.getValue()) && Evn_id.getValue()!=0 ) {
			params.Evn_id = Evn_id.getValue();
		}
		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Проверка признака на подозрение ЗНО..."});
        loadMask.show();
        Ext.Ajax.request({
            callback: function(opts, success, response) {
                loadMask.hide();

                if ( success ) {
                    var data = Ext.util.JSON.decode(response.responseText);
                    win.lastzno = data.iszno;
                    win.lastznodiag = data.Diag_spid;
                    if(win.lastzno==2 && Ext.isEmpty(base_form.findField('EvnVizitPL_IsZNO').getValue())) {
						win.findById('EEPLEF_EvnVizitPL_IsZNOCheckbox').setValue(true);
						if(!Ext.isEmpty(data.Diag_spid)) {
							base_form.findField('Diag_spid').getStore().load({
								callback:function () {
									base_form.findField('Diag_spid').getStore().each(function (rec) {
										if (rec.get('Diag_id') == data.Diag_spid) {
											base_form.findField('Diag_spid').fireEvent('select', base_form.findField('Diag_spid'), rec, 0);
										}
									});
								},
								params:{where:"where DiagLevel_id = 4 and Diag_id = " + data.Diag_spid}
							});
						}
					}
                }
                else {
                    sw.swMsg.alert('Ошибка', 'Ошибка при определении признака на подозрение ЗНО');
                }
            },
			params: params,
            url: '/?c=Person&m=checkEvnZNO_last'
        });
        
        win.checkBiopsyDate(options.action);
	},
	
	checkBiopsyDate: function(formAction) {
		if(getRegionNick()!='ekb') return;

		var win = this,
			base_form = win.FormPanel.getForm(),
			person_id = base_form.findField('Person_id');
			
		if(base_form.findField('EvnVizitPL_IsZNORemove').getValue() == '2') {
			Ext.getCmp('EEPLEF_BiopsyDatePanel').show();
			if(formAction=='add' && Ext.isEmpty(base_form.findField('EvnVizitPL_BiopsyDate').getValue()) ) {
				var params = new Object();
				params.object = 'EvnVizitPL';
				params.Person_id = person_id.getValue();
				Ext.Ajax.request({
					url: '/?c=Person&m=getEvnBiopsyDate',
					params: params,
					callback:function (options, success, response) {
						if (success) {
							var response_obj = Ext.util.JSON.decode(response.responseText);
							if (response_obj.success && response_obj.data) {
								base_form.findField('EvnVizitPL_BiopsyDate').setValue(response_obj.data);
							}
						}
					}
				});
			}
		} else Ext.getCmp('EEPLEF_BiopsyDatePanel').hide();
	},
	
	changeZNO: function(options){
		if(getRegionNick()!='ekb') return;
		var win = this,
			base_form = win.FormPanel.getForm(),
			person_id = base_form.findField('Person_id'),
			Evn_id = base_form.findField('EvnVizitPL_id'),
			params = new Object();
		
		params.object = 'EvnVizitPL';
		params.Evn_id = Evn_id.getValue();
		if(Ext.isEmpty(options.isZNO)) return; else params.isZNO = options.isZNO ? 2 : 1;
		
		base_form.findField('EvnVizitPL_IsZNORemove').setValue(options.isZNO ? 1 : 2);
		
		win.checkBiopsyDate( !options.isZNO ? 'add' : '' );
		
		if(!Ext.isEmpty(params.Evn_id) && params.Evn_id>0) {
			var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Запись признака ЗНО..."});
			loadMask.show();
			Ext.Ajax.request({
				url: '/?c=Person&m=changeEvnZNO',
				params: params,
				callback:function (options, success, response) {
					loadMask.hide();

					if (success) {
						var response_obj = Ext.util.JSON.decode(response.responseText);
						if (response_obj.success) {
							
						}
					}
				}
			});
		}

		win.setDiagSpidComboDisabled();
	},
	show: function() {

		// Переменная для региональных настроек:
		var regNick = getRegionNick();

		sw.Promed.swEmkEvnPLEditWindow.superclass.show.apply(this, arguments);

		var win = this;

		this.fo = true;
		this.EvnUslugaGridIsModified = false;
		this.findById('EEPLEF_DiagPanel').expand();
		this.findById('EEPLEF_DirectInfoPanel').expand();
		this.findById('EEPLEF_EvnDiagPLPanel').collapse();
		this.findById('EEPLEF_EvnUslugaPanel').collapse();
		this.findById('EEPLEF_EvnVizitPLPanel').expand();
		this.findById('EEPLEF_ResultPanel').expand();
		this.findById('EEPLEF_SpecificsPanel').expand();

		this.findById('EEPLEF_DiagPanel').hide();
		this.findById('EEPLEF_DirectInfoPanel').hide();
		this.findById('EEPLEF_EvnDiagPLPanel').hide();
		this.findById('EEPLEF_EvnUslugaPanel').hide();
		this.findById('EEPLEF_EvnVizitPLPanel').hide();
		this.findById('EEPLEF_ResultPanel').hide();
		this.findById('EEPLEF_SpecificsPanel').hide();

		this.findById('EEPLEF_EvnDiagPLPanel').isLoaded = false;
		this.findById('EEPLEF_EvnUslugaPanel').isLoaded = false;
		
		this.diagIsChanged = false;

		var
			isUfa = (regNick == 'ufa'),
			isEkb = (regNick == 'ekb'),
			isKareliya = (regNick == 'kareliya'),
			isBur = (regNick == 'buryatiya');

		var base_form = this.FormPanel.getForm();
		base_form.reset();

		this.setDiagConcComboVisible();

		base_form.findField('PayTypeKAZ_id').setContainerVisible(getRegionNick() == 'kz');

		base_form.findField('RankinScale_id').hideContainer();
		base_form.findField('RankinScale_id').clearValue();
		base_form.findField('RankinScale_id').setAllowBlank(true);
		
		base_form.findField('VizitActiveType_id').clearValue();
		base_form.findField('VizitActiveType_id').clearFilter();
		base_form.findField('VizitActiveType_id').hideContainer();

		base_form.findField('EvnPLDisp_id').setContainerVisible(!isUfa);
		base_form.findField('DispClass_id').setContainerVisible(!isUfa);
		base_form.findField('DispProfGoalType_id').setContainerVisible(isUfa);
		base_form.findField('DispProfGoalType_id').setAllowBlank(true);

		base_form.findField('Diag_id').filterDate = null;
        base_form.findField('HealthKind_id').disable();
        base_form.findField('HealthKind_id').setAllowBlank(true);

		base_form.findField('Diag_lid').setContainerVisible(regNick != 'kareliya');
		base_form.findField('ResultDeseaseType_id').setContainerVisible( regNick.inlist([/*'astra',*/'adygeya', 'vologda', 'buryatiya','ekb','kaluga','kareliya','krasnoyarsk','krym','penza','pskov','yakutiya','yaroslavl']) );
		base_form.findField('PersonDisp_id').setAllowBlank(true);
		
		base_form.findField('ScreenType_id').hideContainer();
		base_form.findField('ScreenType_id').setAllowBlank(true);

		if ( getRegionNick().inlist(['krasnoyarsk', 'adygeya', 'yakutiya', 'yaroslavl'])) {
			base_form.findField('ResultDeseaseType_id').getStore().filterBy(function(rec) {
				return (!Ext.isEmpty(rec.get('ResultDeseaseType_Code')) && rec.get('ResultDeseaseType_Code').toString().substr(0, 1) == '3');
			});
		}

		base_form.findField('LpuSectionProfile_id').getStore().baseParams = {};
		
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

		// Убираем признак обязательности заполнения с полей
		base_form.findField('DeseaseType_id').setAllowBlank(true);
		base_form.findField('Diag_id').setAllowBlank(true);
		base_form.findField('EvnPL_NumCard').setAllowBlank(true);
		base_form.findField('EvnPL_UKL').setAllowBlank(true);
		base_form.findField('EvnVizitPL_setDate').setAllowBlank(true);
		base_form.findField('LpuSection_id').setAllowBlank(true);
		base_form.findField('MedStaffFact_id').setAllowBlank(true);
		base_form.findField('PayType_id').setAllowBlank(true);
		base_form.findField('ResultClass_id').setAllowBlank(true);
		base_form.findField('ResultDeseaseType_id').setAllowBlank(true);
		base_form.findField('ServiceType_id').setAllowBlank(true);
		base_form.findField('VizitType_id').setAllowBlank(true);
		base_form.findField('RiskLevel_id').setAllowBlank(true);
		base_form.findField('WellnessCenterAgeGroups_id').setAllowBlank(true);
		base_form.findField('UslugaComplex_uid').setAllowBlank(true);
		base_form.findField('PrehospTrauma_id').setAllowBlank(true);


		base_form.findField('TreatmentClass_id').setAllowBlank(true);

		base_form.findField('MedicalCareKind_vid').setAllowBlank(true);

		this.panelEvnDirectionAll.onReset();
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
		this.action = 'addEvnPL';
		this.callback = Ext.emptyFn;
		this.formStatus = 'edit';
		this.HomeVisit_id = null;
		this.onHide = Ext.emptyFn;
		this.PersonEvn_id = null;
		this.UserLpuSectionList = new Array();
		this.UserMedStaffFactList = new Array();
		this.TimetableGraf_id = null;
		this.OtherVizitList = null;
		this.OtherUslugaList = null;
		this.clearUslugaComplexUid = false;
		this.formLoaded = false;
		this.IsLoading = true;
		this.RepositoryObservData = {};
		
		this.DrugTherapySchemePanel.resetFieldSets();
		this.DrugTherapySchemePanel.hide();

		this.setInterruptLeaveTypeVisible();

		if ( !arguments[0] || !arguments[0].formParams || !arguments[0].action || !arguments[0].userMedStaffFact ) {
			sw.swMsg.alert(lang['soobschenie'], lang['nevernyie_parametryi']);
			return false;
		}

		this.center();
		this.restore();

		this.formParams = arguments[0].formParams;
		if ( arguments[0].action && typeof arguments[0].action == 'string' && arguments[0].action.inlist(this.actionList) ) {
			this.action = arguments[0].action;
		}

		this.allowConsulDiagnVizitOnly = false;
		this.allowMorbusVizitOnly = false;

		if ( arguments[0].allowConsulDiagnVizitOnly ) {
			this.allowConsulDiagnVizitOnly = arguments[0].allowConsulDiagnVizitOnly;
		}

		if ( arguments[0].allowMorbusVizitOnly ) {
			this.allowMorbusVizitOnly = arguments[0].allowMorbusVizitOnly;
		}

		if (this.action.inlist(['openEvnPL','closeEvnPL']))
		{
			this.minimize();
		}
		else
		{
			this.maximize();
		}

		base_form.setValues(arguments[0].formParams);

		if (Ext.isArray(arguments[0].OtherVizitList) && arguments[0].OtherVizitList.length>0) {
			this.OtherVizitList = arguments[0].OtherVizitList;
		}
		if (Ext.isArray(arguments[0].OtherUslugaList)) {
			this.OtherUslugaList = arguments[0].OtherUslugaList;
		}

		if(arguments[0].TimetableGraf_id && arguments[0].TimetableGraf_id > 0)
		{
			base_form.findField('TimetableGraf_id').setValue(arguments[0].TimetableGraf_id);
			this.TimetableGraf_id = arguments[0].TimetableGraf_id;
		}

		if(arguments[0].HomeVisit_id && arguments[0].HomeVisit_id > 0)
		{
			this.HomeVisit_id = arguments[0].HomeVisit_id;
		}

		this.callback = arguments[0].callback || Ext.emptyFn;
		this.onHide = arguments[0].onHide || Ext.emptyFn;
		this.confirmDeleteEvnVizitPL = arguments[0].confirmDeleteEvnVizitPL || false;

		if ( arguments[0].PersonEvn_id && arguments[0].usePersonEvn ) {
			this.PersonEvn_id = arguments[0].PersonEvn_id;
		}

		if ( arguments[0].vizitAction ) {
			this.vizitAction = arguments[0].vizitAction;
		}

		if ( arguments[0].vizitCount ) {
			this.vizitCount = arguments[0].vizitCount;
		}

		if ( arguments[0].clearUslugaComplexUid ) {
			this.clearUslugaComplexUid = arguments[0].clearUslugaComplexUid;
		}

		this.lastEvnVizitPLData = arguments[0].lastEvnVizitPLDate || null;

		// определенный MedStaffFact
		this.userMedStaffFact = arguments[0].userMedStaffFact;

		if ( Ext.globalOptions.globals['medstafffact'] && Ext.globalOptions.globals['medstafffact'].length > 0 ) {
			this.UserMedStaffFactList = Ext.globalOptions.globals['medstafffact'];
		}

		if ( Ext.globalOptions.globals['lpusection'] && Ext.globalOptions.globals['lpusection'].length > 0 ) {
			this.UserLpuSectionList = Ext.globalOptions.globals['lpusection'];
		}

		this.PersonInfo.setTitle('...');
		this.PersonInfo.load({
			callback: function() {
				this.PersonInfo.setPersonTitle();
				win.setMKB();
				win.setInoterFilter();
				win.refreshFieldsVisibility(['PregnancyEvnVizitPL_Period']);
				win.loadSpecificsTree();
			}.createDelegate(this),
			Person_id: base_form.findField('Person_id').getValue(),
			Server_id: base_form.findField('Server_id').getValue(),
			PersonEvn_id: this.PersonEvn_id
		});

		this.findById('EEPLEF_EvnDiagPLGrid').getStore().removeAll();
		this.findById('EEPLEF_EvnDiagPLGrid').getTopToolbar().items.items[0].enable();
		this.findById('EEPLEF_EvnDiagPLGrid').getTopToolbar().items.items[1].disable();
		this.findById('EEPLEF_EvnDiagPLGrid').getTopToolbar().items.items[2].disable();
		this.findById('EEPLEF_EvnDiagPLGrid').getTopToolbar().items.items[3].disable();
		this.findById('EEPLEF_EvnUslugaGrid').getStore().removeAll();
		this.findById('EEPLEF_EvnUslugaGrid').getTopToolbar().items.items[0].enable();
		this.findById('EEPLEF_EvnUslugaGrid').getTopToolbar().items.items[1].disable();
		this.findById('EEPLEF_EvnUslugaGrid').getTopToolbar().items.items[2].disable();
		this.findById('EEPLEF_EvnUslugaGrid').getTopToolbar().items.items[3].disable();

		base_form.findField('EvnVizitPL_setDate').setMaxValue(undefined);
		base_form.findField('EvnVizitPL_setDate').setMinValue(undefined);

		base_form.findField('Diag_id').fireEvent('change', base_form.findField('Diag_id'), null);

		base_form.findField('DispClass_id').fireEvent('change', base_form.findField('DispClass_id'), null);
		base_form.findField('EvnPLDisp_id').DispClass_id = 0;
		base_form.findField('EvnPLDisp_id').getStore().removeAll();
		base_form.findField('PersonDisp_id').getStore().removeAll();

		base_form.findField('TreatmentClass_id').onLoadStore();
		this.filterResultClassCombo();
		
		var xdate = new Date(2016, 0, 1); // Поле обязательно если дата посещения 01-01-2016 или позже
		var mdate = new Date(getValidDT(this.lastEvnVizitPLData, ''));
		base_form.findField('TreatmentClass_id').setAllowBlank(mdate < xdate);

		if ( this.action.inlist([ 'openEvnPL', 'closeEvnPL', 'editEvnPL', 'editEvnVizitPL', 'copyEvnVizitPL' ]) ) {
			var params = {
				Evn_id: arguments[0].formParams.EvnPL_id,
				MedStaffFact_id: (!Ext.isEmpty(sw.Promed.MedStaffFactByUser) && !Ext.isEmpty(sw.Promed.MedStaffFactByUser.current) && !Ext.isEmpty(sw.Promed.MedStaffFactByUser.current.MedStaffFact_id)) ? sw.Promed.MedStaffFactByUser.current.MedStaffFact_id : null,
				ArmType: !Ext.isEmpty(sw.Promed.MedStaffFactByUser) && !Ext.isEmpty(sw.Promed.MedStaffFactByUser.current) && !Ext.isEmpty(sw.Promed.MedStaffFactByUser.current.ARMType) ? sw.Promed.MedStaffFactByUser.current.ARMType : null
			};
			if ( this.action.inlist([ 'editEvnVizitPL', 'copyEvnVizitPL' ]) && regNick != 'kareliya' ) {
				params.Evn_id = arguments[0].formParams.EvnVizitPL_id;
			}
			//Проверяем доступность редактирования
			Ext.Ajax.request({
				failure: function() {
					sw.swMsg.alert(lang['oshibka'], lang['oshibka_proverki_vozmojnosti_redaktirovaniya_formyi'], function() { win.hide(); } );
				},
				params: params,
				success: function(response, options) {
					if ( !Ext.isEmpty(response.responseText) ) {
						var response_obj = Ext.util.JSON.decode(response.responseText);

						if ( response_obj.success == false ) {
							sw.swMsg.alert(lang['oshibka'], response_obj.Error_Msg ? response_obj.Error_Msg : lang['oshibka_proverki_vozmojnosti_redaktirovaniya_formyi']);

							if (win.action == 'editEvnPL') {
								win.action = 'viewEvnPL';
							} else if (win.action == 'editEvnVizitPL') {
								win.action = 'viewEvnVizitPL';
							}
						}
					}

					if (!this.OtherVizitList && this.action.inlist(['editEvnVizitPL', 'copyEvnVizitPL'])) {
						this.loadOtherVizitList(win.onShow); //Получить сперва список остальных посещений в ТАП
					} else {
						win.onShow();
					}
				}.createDelegate(this),
				url: '/?c=Evn&m=CommonChecksForEdit'
			});
		}
		else {
			win.onShow();
		}
	},
	onShow: function() {
		var win = this;

		// Переменные для региональных настроек:
		var regNick = getRegionNick(),
			isUfa = (regNick == 'ufa'),
			isEkb = (regNick == 'ekb'),
			isKareliya = (regNick == 'kareliya'),
			isBur = (regNick == 'buryatiya');

		var base_form = this._form,
			diagStore = this._cmbDiag.getStore(),
			complDiagStore = this._cmbComplDiag.getStore(),
			v;

		var loadMask = new Ext.LoadMask(this.getEl(), {
			msg: LOAD_WAIT
		});
		loadMask.show();

		// #170429
		// Очистим и скроем доп. поля, относящиеся к диагнозу и осложнению из группы ХСН:
		this._hideHsnDetails();

		this.lastUslugaComplexParams = null;
		this.blockUslugaComplexReload = false;

		let UslugaMedType_id = base_form.findField('UslugaMedType_id');
		UslugaMedType_id.setContainerVisible(regNick === 'kz');
		if (regNick === 'kz' && (this.action === 'addEvnPL' || this.action === 'addEvnVizitPL')) {
			UslugaMedType_id.setFieldValue('UslugaMedType_Code', '1400');
		}

		switch ( this.action ) {
			case 'addEvnPL':
				this.checkIsAssignNasel();
				this.fo = false;
				this.setTitle(WND_POL_EPLADD);
				this.enableEdit(true);

				this.findById('EEPLEF_DiagPanel').show();
				this.findById('EEPLEF_DirectInfoPanel').show();
				this.findById('EEPLEF_EvnDiagPLPanel').show();
				this.findById('EEPLEF_EvnUslugaPanel').show();
				this.findById('EEPLEF_EvnVizitPLPanel').show();
				this.findById('EEPLEF_ResultPanel').show();
				this.findById('EEPLEF_SpecificsPanel').show();

				this.findById('EEPLEF_EvnDiagPLPanel').isLoaded = true;
				this.findById('EEPLEF_EvnUslugaPanel').isLoaded = true;

				LoadEmptyRow(this.findById('EEPLEF_EvnDiagPLGrid'));
				LoadEmptyRow(this.findById('EEPLEF_EvnUslugaGrid'));

				base_form.findField('EvnPL_NumCard').setAllowBlank(false);
				base_form.findField('EvnVizitPL_setDate').setAllowBlank(false);
				base_form.findField('LpuSection_id').setAllowBlank(false);
				base_form.findField('MedStaffFact_id').setAllowBlank(false);
				base_form.findField('UslugaComplex_uid').clearBaseParams();
				base_form.findField('UslugaComplex_uid').getStore().baseParams.LpuSection_id = base_form.findField('LpuSection_id').getValue();
				// Устанавливаем фильтры для кодов посещений
				base_form.findField('UslugaComplex_uid').setVizitCodeFilters({
					isStac: false,
					isStom: false,
					allowNonMorbusVizitOnly: false,
					allowMorbusVizitOnly: false,
					allowMorbusVizitCodesGroup88: false
				});
                if ( sw.Promed.EvnVizitPL.isSupportVizitCode() ) {
                    base_form.findField('UslugaComplex_uid').isload = false;
                    base_form.findField('Mes_id').clearBaseParams();
                }
                base_form.findField('UslugaComplex_uid').setAllowBlank(sw.Promed.EvnVizitPL.isAllowBlankVizitCode());

				if (regNick.inlist(['buryatiya', 'ekb', 'pskov', 'ufa'])) {
					base_form.findField('UslugaComplex_uid').setPersonId(base_form.findField('Person_id').getValue());
				}

				base_form.findField('PersonDisp_id').getStore().baseParams.Person_id = base_form.findField('Person_id').getValue();

				if ( isBur ) {
					base_form.findField('UslugaComplex_uid').setUslugaCategoryList(['tfoms']);
				}

				if ( isKareliya ) {
					base_form.findField('MedicalCareKind_id').setFieldValue('MedicalCareKind_Code', 1);
					base_form.findField('EvnPL_IsFirstDisable').setValue(1);
				}

                base_form.findField('Diag_id').setAllowBlank(sw.Promed.EvnVizitPL.isAllowBlankDiag());

				base_form.findField('EvnPL_IsFinish').setValue(1);
				
				var paytype_combo = base_form.findField('PayType_id');
				var servicetype_combo = base_form.findField('ServiceType_id');
				var vizittype_combo = base_form.findField('VizitType_id');
				
				paytype_combo.setAllowBlank(false);
				servicetype_combo.setAllowBlank(false);
				vizittype_combo.setAllowBlank(false);

				if(paytype_combo.getStore().getCount() == 0)
					paytype_combo.getStore().load({
						callback: function(){
							paytype_combo.setFieldValue('PayType_SysNick', 'oms');

							if (regNick == 'ekb' && this.findById('EEPLEF_PersonInformationFrame').getFieldValue('Person_IsAnonym') == 2) {
								base_form.findField('PayType_id').setFieldValue('PayType_SysNick','bud');
								base_form.findField('PayType_id').disable();
							}
						}.createDelegate(this)
					});
				else {
					paytype_combo.setFieldValue('PayType_SysNick', 'oms');

					if (regNick == 'ekb' && this.findById('EEPLEF_PersonInformationFrame').getFieldValue('Person_IsAnonym') == 2) {
						base_form.findField('PayType_id').setFieldValue('PayType_SysNick','bud');
						base_form.findField('PayType_id').disable();
					}
				}
				
				if(servicetype_combo.getStore().getCount() == 0)
					servicetype_combo.getStore().load({
						callback: function(){
							servicetype_combo.setFieldValue('ServiceType_SysNick', 'polka');
						}.createDelegate(this)
					});
				else
					servicetype_combo.setFieldValue('ServiceType_SysNick', 'polka');

				if(this.HomeVisit_id) {
					servicetype_combo.setFieldValue('ServiceType_SysNick', 'home');
					servicetype_combo.disable();
				}
				
				if(vizittype_combo.getStore().getCount() == 0)
					vizittype_combo.getStore().load({
						callback: function(){
							if (!vizittype_combo.getValue()) {
                                vizittype_combo.setFieldValue('VizitType_SysNick', 'desease');
                            }
							vizittype_combo.fireEvent('change', vizittype_combo, vizittype_combo.getValue());
						}.createDelegate(this)
					});
				else {
                    if (!vizittype_combo.getValue()) {
                        vizittype_combo.setFieldValue('VizitType_SysNick', 'desease');
                    }
					vizittype_combo.fireEvent('change', vizittype_combo, vizittype_combo.getValue());
				}

				//base_form.findField('EvnPL_IsFinish').fireEvent('change', base_form.findField('EvnPL_IsFinish'), base_form.findField('EvnPL_IsFinish').getValue());
				var is_finish_combo = base_form.findField('EvnPL_IsFinish');
				var is_finish_index = is_finish_combo.getStore().findBy(function(rec) {
					return (rec.get(is_finish_combo.valueField) == is_finish_combo.getValue());
				});
				is_finish_combo.fireEvent('select', is_finish_combo, is_finish_combo.getStore().getAt(is_finish_index));

				

				loadMask.hide();

				setCurrentDateTime({
					callback: function() {
						base_form.findField('EvnVizitPL_setDate').fireEvent('change', base_form.findField('EvnVizitPL_setDate'), base_form.findField('EvnVizitPL_setDate').getValue());
						this.panelEvnDirectionAll.isReadOnly = false;
						// так и не понял, где оно зануляется, пока костыль
						if(this.formParams.Diag_did) { 
							base_form.findField('Diag_did').setValue(this.formParams.Diag_did);
						}
						this.panelEvnDirectionAll.onLoadForm(this);
						//base_form.findField('Diag_did').fireEvent('change', base_form.findField('Diag_did'),  base_form.findField('Diag_id').getValue());
						
					}.createDelegate(this),
					dateField: base_form.findField('EvnVizitPL_setDate'),
					loadMask: false,
					setDate: true,
					setDateMaxValue: true,
					setDateMinValue: false,
					setTime: true,
					timeField: base_form.findField('EvnVizitPL_setTime'),
					windowId: this.id
				});

				base_form.clearInvalid();

				var diag_id = this._cmbDiag.getValue();
				if (diag_id)
					diagStore.load(
					{
						callback: function()
						{
							diagStore.each(function(record)
							{
								if (record.get('Diag_id') == diag_id)
								{
									this._cmbDiag.setValue(diag_id);

									// #170429
									this._refreshHsnDetails(this._cmbDiag, false);

									this._cmbDiag.fireEvent('select', this._cmbDiag, record, 0);
									this._cmbDiag.fireEvent('change', this._cmbDiag, diag_id);

									base_form.findField('TreatmentClass_id').onLoadStore();
									win.refreshFieldsVisibility();
									win.loadSpecificsTree();
									win.formLoaded = true;
									win.diagIsChanged = false;
								}
							}.createDelegate(this));
						},

						params:
						{
							where: "where Diag_id = " + diag_id
						},

						scope: this
					});

				v = base_form.findField('Diag_preid');

				var diag_preid = v.getValue();

				if (diag_preid)
					diagStore.load(
					{
						callback: function()
						{
							v.getStore().each(function(record)
							{
								if (record.get('Diag_id') == diag_preid)
								{
									v.fireEvent('select', v, record, 0);
									v.setValue(diag_preid);
									v.fireEvent('change', v, diag_preid);
									base_form.findField('TreatmentClass_id').onLoadStore();
								}
							}.createDelegate(this));
						},

						params:
						{
							where: "where Diag_id = " + diag_preid
						},

						scope: this
					});

				v = base_form.findField('Diag_concid');

				var diag_concid = v.getValue();

				if (diag_concid)
					v.getStore().load(
					{
						callback: function()
						{
							v.getStore().each(function(record)
							{
								if (record.get('Diag_id') == diag_concid)
								{
									v.fireEvent('select', v, record, 0);
									v.setValue(diag_concid);
									v.fireEvent('change', v, diag_concid);
				}
							}.createDelegate(this));
						},

						params:
						{
							where: "where Diag_id = " + diag_concid
						},

						scope: this
					});
				
				if(!base_form.findField('EvnPL_NumCard').getValue())
					this.getEvnPLNumber();

				base_form.findField('Mes_id').fireEvent('change', base_form.findField('Mes_id'), base_form.findField('Mes_id').getValue());
				base_form.findField('EvnPL_NumCard').focus(true, 250);
				if (regNick == 'ekb') this.checkZNO({action: this.action });
				this.IsLoading = false;
				this.checkAndOpenRepositoryObserv();
			break;

			case 'addEvnVizitPL':
			case 'openEvnPL':
			case 'closeEvnPL':
			case 'editEvnPL':
			case 'editEvnVizitPL':
			case 'viewEvnPL':
			case 'viewEvnVizitPL':
			case 'copyEvnVizitPL': //
				var evndirection_vid = base_form.findField('EvnDirection_vid').getValue();
				var timetablegraf_id = base_form.findField('TimetableGraf_id').getValue();
				base_form.load({
					failure: function() {
						loadMask.hide();
						sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_zagruzke_dannyih_formyi'], function() {
							this.hide();
						}.createDelegate(this) );
					}.createDelegate(this),
					params: {
						EvnPL_id: base_form.findField('EvnPL_id').getValue(),
						EvnVizitPL_id: base_form.findField('EvnVizitPL_id').getValue(),
						loadLast: this.action.inlist([ 'addEvnVizitPL', 'openEvnPL', 'closeEvnPL', 'editEvnPL', 'copyEvnVizitPL' ]) ? 1 : 0
					},
					success: function(f, act) {
						win.blockUslugaComplexReload = true;
						if ( base_form.findField('accessType').getValue() == 'view' ) {
							this.action = 'viewEvnPL';
						}

						this.setInterruptLeaveTypeVisible();
						this.checkIsAssignNasel();
						this.setDiagFidAndLid();

						if ( base_form.findField('EvnPL_IsFinish').getValue() ) {
							win.isFinish = base_form.findField('EvnPL_IsFinish').getValue();
						}
						if ( base_form.findField('VizitType_id').getFieldValue('VizitType_SysNick') ) {
							win.VizitType_SysNick = base_form.findField('VizitType_id').getFieldValue('VizitType_SysNick');
						}
						var uslugacomplex_code = base_form.findField('UslugaComplex_Code').getValue();
						var uslugacomplex_combo = base_form.findField('UslugaComplex_uid');
						if ( isUfa && this.action == 'addEvnVizitPL' && isProphylaxisVizitOnly(uslugacomplex_code) ) {
							sw.swMsg.alert(lang['zaprescheno'], lang['v_tap_ukazan_kod_profilakticheskogo_posescheniya_zaprescheno_vvodit_bolee_odnogo_posescheniya_neobhodimo_zakryit_sluchay_lecheniya'] );
							this.action = 'closeEvnPL';
							/*
							this.hide();
							return false;
							*/
						}
						/*var diag_id = base_form.findField('Diag_id').getValue();
						if ( diag_id ) {
							base_form.findField('Diag_id').getStore().load({
								callback: function() {
									base_form.findField('Diag_id').getStore().each(function(record) {
										if ( record.get('Diag_id') == diag_id ) {
											base_form.findField('Diag_id').fireEvent('select', base_form.findField('Diag_id'), record, 0);
											base_form.findField('Diag_id').setValue(diag_id);
											base_form.findField('Diag_id').fireEvent('change', base_form.findField('Diag_id'), diag_id);
											base_form.findField('TreatmentClass_id').onLoadStore();
											win.refreshFieldsVisibility();
											win.loadSpecificsTree();
											win.formLoaded = true;
											win.diagIsChanged = false;
										}
									});
								},
								params: {where: "where Diag_id = " + diag_id}
							});
						}
						var diag_preid = base_form.findField('Diag_preid').getValue();
						if ( diag_preid ) {
							base_form.findField('Diag_preid').getStore().load({
								callback: function() {
									base_form.findField('Diag_preid').getStore().each(function(record) {
										if ( record.get('Diag_id') == diag_preid ) {
											base_form.findField('Diag_preid').fireEvent('select', base_form.findField('Diag_preid'), record, 0);
											base_form.findField('Diag_preid').setValue(diag_preid);
											base_form.findField('Diag_preid').fireEvent('change', base_form.findField('Diag_preid'), diag_preid);
										}
									});
								},
								params: {where: "where Diag_id = " + diag_preid}
							});
						}
						var diag_concid = act.result.data.Diag_concid;
						if ( diag_concid ) {
							base_form.findField('Diag_concid').getStore().load({
								callback: function() {
									base_form.findField('Diag_concid').getStore().each(function(record) {
										if ( record.get('Diag_id') == diag_concid ) {
											base_form.findField('Diag_concid').fireEvent('select', base_form.findField('Diag_concid'), record, 0);
											base_form.findField('Diag_concid').setValue(diag_concid);
											base_form.findField('Diag_concid').fireEvent('change', base_form.findField('Diag_concid'), diag_concid);
										}
									});
								},
								params: {where: "where Diag_id = " + diag_concid}
							});
						}*/

						this.findById('EEPLEF_EvnVizitPL_IsZNOCheckbox').setValue(base_form.findField('EvnVizitPL_IsZNO').getValue() == 2);
						base_form.findField('Diag_spid').setContainerVisible(base_form.findField('EvnVizitPL_IsZNO').getValue() == 2);
						//base_form.findField('Diag_spid').setAllowBlank(!getRegionNick().inlist([ 'astra', 'perm', 'ufa' ]) || base_form.findField('EvnVizitPL_IsZNO').getValue() != 2);

						v = base_form.findField('Diag_spid');

						var diag_spid = v.getValue();

						if (diag_spid)
							v.getStore().load(
							{
								callback: function()
								{
									v.getStore().each(function(rec)
									{
										if (rec.get('Diag_id') == diag_spid)
										{
											v.fireEvent('select', v, rec, 0);
											win.setDiagSpidComboDisabled();
										}
									}.createDelegate(this));
								},

								params:
								{
									where:"where DiagLevel_id = 4 and Diag_id = " + diag_spid
								},

								scope: this
							});

						if (regNick == 'ekb') {
							this.checkZNO({action: this.action });
							this.checkBiopsyDate();
						}
						if (this.clearUslugaComplexUid) {
							uslugacomplex_combo.clearValue();
						}
						uslugacomplex_combo.clearBaseParams();
						// Устанавливаем фильтры для кодов посещений
						uslugacomplex_combo.setVizitCodeFilters({
							isStac: false,
							isStom: false,
							allowNonMorbusVizitOnly: false,
							allowMorbusVizitOnly: false,
							allowMorbusVizitCodesGroup88: false
						});
                        if ( sw.Promed.EvnVizitPL.isSupportVizitCode() ) {
                            uslugacomplex_combo.isload = false;
                            base_form.findField('Mes_id').clearBaseParams();
                        }
                        uslugacomplex_combo.getStore().baseParams.EvnVizit_id = base_form.findField('EvnVizitPL_id').getValue();
                        if (regNick.inlist(['buryatiya', 'ekb', 'pskov', 'ufa'])) {
                            uslugacomplex_combo.setPersonId(base_form.findField('Person_id').getValue());
                        }
                        uslugacomplex_combo.getStore().baseParams.LpuSection_id = base_form.findField('LpuSection_id').getValue();

						base_form.findField('PersonDisp_id').getStore().baseParams.Person_id = base_form.findField('Person_id').getValue();

						if ( isUfa && this.action.inlist([ 'addEvnVizitPL', 'editEvnVizitPL' ]) ) {
							if ( isMorbusVizitOnly(uslugacomplex_code) ) {
								uslugacomplex_combo.getStore().baseParams.allowMorbusVizitOnly = 1;
								uslugacomplex_combo.getStore().baseParams.allowMorbusVizitCodesGroup88 = isMorbusGroup88VizitCode(uslugacomplex_code) ? 1 : 0;
							} else {
								uslugacomplex_combo.getStore().baseParams.allowMorbusVizitOnly = 0;
							}
							if ( !isMorbusVizitOnly(uslugacomplex_code) && !isProphylaxisVizitOnly(uslugacomplex_code) ) {
								uslugacomplex_combo.getStore().baseParams.allowNonMorbusVizitOnly = 1;
							} else {
								uslugacomplex_combo.getStore().baseParams.allowNonMorbusVizitOnly = 0;
							}
						}

						if ( isUfa && this.action =='editEvnVizitPL' && base_form.findField('EvnVizitPL_Count').getValue() == 1) {
							uslugacomplex_combo.getStore().baseParams.allowMorbusVizitOnly = 0;
							uslugacomplex_combo.getStore().baseParams.allowNonMorbusVizitOnly = 0;
						}

						if ( this.action.inlist([ 'addEvnVizitPL', 'openEvnPL','closeEvnPL', 'editEvnPL', 'editEvnVizitPL', 'copyEvnVizitPL' ]) ) {
							this.enableEdit(true);
						}
						else {
							this.enableEdit(false);
						}

						this.reloadUslugaComplexField();

						if (this.action == 'addEvnVizitPL') {
							//base_form.findField('UslugaComplex_uid').disable(); // недоступно для изменения
							base_form.findField('EvnVizitPL_Index').setValue(1); // не первое посещение
						}
						if (regNick == 'pskov' && parseInt(base_form.findField('EvnVizitPL_Index').getValue()) > 0) {
							base_form.findField('UslugaComplex_uid').disable();
						}
						
						if ( this.action.inlist([ 'editEvnPL', 'viewEvnPL', 'viewEvnVizitPL', 'copyEvnVizitPL' ]) ) {
							this.findById('EEPLEF_DirectInfoPanel').show();

							base_form.findField('EvnPL_NumCard').setAllowBlank(false);
						}

						if ( this.action.inlist([ 'addEvnVizitPL', 'editEvnVizitPL', 'viewEvnPL', 'viewEvnVizitPL', 'copyEvnVizitPL' ]) ) {
							this.findById('EEPLEF_DiagPanel').show();
							this.findById('EEPLEF_EvnDiagPLPanel').show();
							this.findById('EEPLEF_EvnUslugaPanel').show();
							this.findById('EEPLEF_EvnVizitPLPanel').show();
							this.findById('EEPLEF_SpecificsPanel').show();

							if ( this.action == 'addEvnVizitPL' ) {
								this.findById('EEPLEF_EvnDiagPLPanel').isLoaded = true;
								this.findById('EEPLEF_EvnUslugaPanel').isLoaded = true;
								LoadEmptyRow(this.findById('EEPLEF_EvnDiagPLGrid'));
								LoadEmptyRow(this.findById('EEPLEF_EvnUslugaGrid'));
							}

							base_form.findField('EvnVizitPL_setDate').setAllowBlank(false);
							base_form.findField('LpuSection_id').setAllowBlank(false);
							base_form.findField('MedStaffFact_id').setAllowBlank(false);
							base_form.findField('PayType_id').setAllowBlank(false);
							base_form.findField('ServiceType_id').setAllowBlank(false);
							base_form.findField('VizitType_id').setAllowBlank(false);
							base_form.findField('TreatmentClass_id').setAllowBlank(false);
							if (regNick != 'kz') { // для Казахстана поле не нужно
								base_form.findField('MedicalCareKind_vid').setAllowBlank(false);
							}
                            base_form.findField('UslugaComplex_uid').setAllowBlank(sw.Promed.EvnVizitPL.isAllowBlankVizitCode());
                            base_form.findField('Diag_id').setAllowBlank(sw.Promed.EvnVizitPL.isAllowBlankDiag());
						}

						if ( this.action.inlist([ 'addEvnVizitPL', 'openEvnPL','closeEvnPL', 'editEvnVizitPL', 'editEvnPL', 'copyEvnVizitPL' ]) ) {
							this.findById('EEPLEF_ResultPanel').show();
						}
						this.setRiskLevelComboState();
						this.setWellnessCenterAgeGroupsComboState();
						switch ( this.action ) {
							case 'addEvnVizitPL':
								this.setTitle(WND_POL_EVPLADD);
								base_form.findField('EvnDirection_vid').setValue(evndirection_vid || null);
								base_form.findField('TimetableGraf_id').setValue(timetablegraf_id || this.TimetableGraf_id);
								base_form.findField('EvnUslugaCommon_id').setValue(0);
								base_form.findField('EvnVizitPL_id').setValue(0);
								base_form.findField('EvnVizitPL_Time').setRawValue('');
								base_form.findField('LpuSection_id').clearValue();
								base_form.findField('MedStaffFact_id').clearValue();
								base_form.findField('MedStaffFact_sid').clearValue();
								base_form.findField('MedPersonal_id').setValue(null);
								if (regNick != 'pskov') {
									base_form.findField('UslugaComplex_uid').clearValue();
								}
								base_form.findField('VizitClass_id').setValue(2);
								this.reloadUslugaComplexField();
								this.checkAndOpenRepositoryObserv();
								
							break;

							case 'openEvnPL':
								this.setTitle(lang['otkryitie_sluchaya_lecheniya']);
							break;

							case 'closeEvnPL':
								this.setTitle(lang['zavershenie_sluchaya_lecheniya']);
							break;

							case 'editEvnPL':
								this.setTitle(WND_POL_EPLEDIT);
							break;

							case 'editEvnVizitPL':
								this.setTitle(WND_POL_EVPLEDIT);
								
							break;

							case 'viewEvnPL':
								this.setTitle(WND_POL_EPLVIEW);
							break;

							case 'viewEvnVizitPL':
								this.setTitle(WND_POL_EVPLVIEW);
								
							break;
							
							case 'copyEvnVizitPL':
								this.setTitle(lang['kopirovanie_posescheniya']);
							break;
						}

						var diag_agid = act.result.data.Diag_agid;
						var diag_id = act.result.data.Diag_id;
						var diag_concid = act.result.data.Diag_concid;
						var diag_fid = act.result.data.Diag_fid;
						var diag_lid = act.result.data.Diag_lid;
						var diag_preid = act.result.data.Diag_preid;
						var direct_class_id = base_form.findField('DirectClass_id').getValue();
						var index;
						var is_finish = base_form.findField('EvnPL_IsFinish').getValue();
						var is_unlaw = base_form.findField('EvnPL_IsUnlaw').getValue();
						var lpu_oid = base_form.findField('Lpu_oid').getValue();
						var lpu_section_id = base_form.findField('LpuSection_id').getValue() || this.userMedStaffFact.LpuSection_id;
						var lpu_section_oid = base_form.findField('LpuSection_oid').getValue();
                        var medstafffact_id = base_form.findField('MedStaffFact_id').getValue() || this.userMedStaffFact.MedStaffFact_id;
                        var med_personal_id = base_form.findField('MedPersonal_id').getValue() || this.userMedStaffFact.MedPersonal_id;
						var med_personal_sid = base_form.findField('MedPersonal_sid').getValue();
						var record;
						var service_type_id = base_form.findField('ServiceType_id').getValue();
						var usluga_complex_id = base_form.findField('UslugaComplex_uid').getValue();
						var vizit_type_id = base_form.findField('VizitType_id').getValue();
						var DispClass_id = base_form.findField('DispClass_id').getValue();
						var PersonDisp_id = base_form.findField('PersonDisp_id').getValue();

						if ( !Ext.isEmpty(DispClass_id) ) {
							base_form.findField('DispClass_id').fireEvent('change', base_form.findField('DispClass_id'), DispClass_id);
						}

						this.setRiskLevelComboState();
						this.setWellnessCenterAgeGroupsComboState();

						// Фильтр на поле ServiceType_id
						// https://redmine.swan.perm.ru/issues/17571
						base_form.findField('ServiceType_id').clearValue();
						base_form.findField('ServiceType_id').getStore().clearFilter();
						base_form.findField('ServiceType_id').lastQuery = '';

						this.panelEvnDirectionAll.onLoadForm(this); // onLoadForm надо делать всегда, иначе не прогружаются диагнозы!
						if ( !this.findById('EEPLEF_DirectInfoPanel').hidden ) {
							this.panelEvnDirectionAll.isReadOnly = this.action.inlist([ 'viewEvnPL', 'viewEvnVizitPL' ]);
						}

						if ( !this.findById('EEPLEF_EvnVizitPLPanel').hidden ) {
							var curDate = (typeof getValidDT(getGlobalOptions().date, '') == 'object' ? getValidDT(getGlobalOptions().date, '') : new Date());

							if ( typeof curDate == 'object' ) {
								base_form.findField('EvnVizitPL_setDate').setMaxValue(curDate.format('d.m.Y'));
							}

							if ( this.action.inlist([ 'editEvnVizitPL' ]) ) {
								base_form.findField('EvnVizitPL_setDate').fireEvent('change', base_form.findField('EvnVizitPL_setDate'), base_form.findField('EvnVizitPL_setDate').getValue());
								base_form.findField('VizitType_id').fireEvent('change', base_form.findField('VizitType_id'), vizit_type_id);

								index = base_form.findField('MedStaffFact_id').getStore().findBy(function(rec, id) {
									if (medstafffact_id) {
										return (rec.get('MedStaffFact_id') == medstafffact_id);
									} else {
										return (rec.get('MedPersonal_id') == med_personal_id && rec.get('LpuSection_id') == lpu_section_id);
									}
								});
								record = base_form.findField('MedStaffFact_id').getStore().getAt(index);

								if ( record ) {
									base_form.findField('MedStaffFact_id').setValue(record.get('MedStaffFact_id'));
									base_form.findField('MedStaffFact_id').fireEvent('change', base_form.findField('MedStaffFact_id'), base_form.findField('MedStaffFact_id').getValue());
								}

								index = base_form.findField('MedStaffFact_sid').getStore().findBy(function(rec, id) {
                                    return ( rec.get('MedPersonal_id') == med_personal_sid );
								});
								record = base_form.findField('MedStaffFact_sid').getStore().getAt(index);

								if ( record ) {
									base_form.findField('MedStaffFact_sid').setValue(record.get('MedStaffFact_id'));
								}
							}
							else if (this.action.inlist(['addEvnVizitPL','addEvnPL'])) {
								base_form.findField('EvnVizitPL_setDate').setValue(null);
								setCurrentDateTime({
									callback: function() {
										base_form.findField('EvnVizitPL_setDate').fireEvent('change', base_form.findField('EvnVizitPL_setDate'), base_form.findField('EvnVizitPL_setDate').getValue());
									}.createDelegate(this),
									dateField: base_form.findField('EvnVizitPL_setDate'),
									loadMask: false,
									setDate: true,
									setDateMaxValue: true,
									setDateMinValue: false,
									setTime: true,
									timeField: base_form.findField('EvnVizitPL_setTime'),
									windowId: this.id
								});
							}
							else
							{
								base_form.findField('LpuSection_id').getStore().load({
									callback: function() {
										index = base_form.findField('LpuSection_id').getStore().findBy(function(rec) {
											if ( rec.get('LpuSection_id') == lpu_section_id ) {
												return true;
											}
											else {
												return false;
											}
										});

										if ( index >= 0 ) {
											base_form.findField('LpuSection_id').setValue(base_form.findField('LpuSection_id').getStore().getAt(index).get('LpuSection_id'));
											base_form.findField('LpuSection_id').fireEvent('change', base_form.findField('LpuSection_id'), base_form.findField('LpuSection_id').getStore().getAt(index).get('LpuSection_id'));
										}
									}.createDelegate(this),
									params: {
										LpuSection_id: lpu_section_id
									}
								});
								
								base_form.findField('MedStaffFact_id').getStore().load({
									callback: function() {
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
									params: {
										LpuSection_id: lpu_section_id,
										MedPersonal_id: med_personal_id
									}
								});

								if ( med_personal_sid ) {
									base_form.findField('MedStaffFact_sid').getStore().load({
										callback: function() {
											index = base_form.findField('MedStaffFact_sid').getStore().findBy(function(rec) {
												if ( rec.get('MedPersonal_id') == med_personal_id ) {
													return true;
												}
												else {
													return false;
												}
											});

											if ( index >= 0 ) {
												base_form.findField('MedStaffFact_sid').setValue(base_form.findField('MedStaffFact_sid').getStore().getAt(index).get('MedStaffFact_id'));
											}
										}.createDelegate(this),
										params: {
											MedPersonal_id: med_personal_sid
										}
									});
								}
							}

							win.blockUslugaComplexReload = false;
							if ( sw.Promed.EvnVizitPL.isSupportVizitCode() ) {
								if (regNick == 'ufa') {
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

								if ( !Ext.isEmpty(usluga_complex_id) ) {
									win.reloadUslugaComplexField(usluga_complex_id);
								}
								// https://redmine.swan.perm.ru/issues/33884
								else if (
									this.action == 'addEvnVizitPL' && !Ext.isEmpty(uslugacomplex_code) && uslugacomplex_code.substr(-3, 3).inlist([ '865', '866' ])
									&& typeof this.userMedStaffFact == 'object' && !Ext.isEmpty(this.userMedStaffFact.LpuSectionProfile_Code)
								) {
									base_form.findField('UslugaComplex_Code').setValue(this.userMedStaffFact.LpuSectionProfile_Code.toString() + '865');
								}
								// https://redmine.swan.perm.ru/issues/53050
								else if (
									this.action == 'editEvnVizitPL' && regNick == 'pskov' && parseInt(base_form.findField('EvnVizitPL_Index').getValue()) > 0
								) {
									// Загружаем данные о первом посещении с сервера
									Ext.Ajax.request({
										callback: function(options, success, response) {
											var response_obj = Ext.util.JSON.decode(response.responseText);

											if ( typeof response_obj == 'object' && response_obj.length > 0 ) {
												usluga_complex_id = response_obj[0].UslugaComplex_uid;

												if ( !Ext.isEmpty(usluga_complex_id) ) {
													win.reloadUslugaComplexField(usluga_complex_id);
												}
											}
										}.createDelegate(this),
										params: {
											EvnVizitPL_pid: base_form.findField('EvnPL_id').getValue()
										},
										url: '/?c=EvnVizit&m=loadFirstEvnVizitPLData'
									});
								}
							}
						}
						else {
							win.blockUslugaComplexReload = false;
							win.reloadUslugaComplexField(usluga_complex_id);
							// MedStaffFact_id таки надо прогрузить

							base_form.findField('MedStaffFact_id').getStore().load({
								callback: function() {
									index = base_form.findField('MedStaffFact_id').getStore().findBy(function(rec) {
										if (medstafffact_id) {
											return (rec.get('MedStaffFact_id') == medstafffact_id);
										} else {
											return (rec.get('MedPersonal_id') == med_personal_id && rec.get('LpuSection_id') == lpu_section_id);
										}
									});

									if ( index >= 0 ) {
										base_form.findField('MedStaffFact_id').setValue(base_form.findField('MedStaffFact_id').getStore().getAt(index).get('MedStaffFact_id'));
									}
								}.createDelegate(this),
								params: {
									LpuSection_id: lpu_section_id,
									MedPersonal_id: med_personal_id
								}
							});
						}

						if (!this.findById('EEPLEF_DiagPanel').hidden)
						{
							if (diag_agid)
								complDiagStore.load(
								{
									callback: function()
									{
										complDiagStore.each(function(record)
										{
											if (record.get('Diag_id') == diag_agid)
												this._cmbComplDiag.fireEvent('select', this._cmbComplDiag, record, 0);
										}.createDelegate(this));

										// #170429
										this._refreshHsnDetails(this._cmbComplDiag, false);
									},

									params:
									{
										where: "where Diag_id = " + diag_agid
									},

									scope: this
								});


							if (diag_preid)
							{
								v = base_form.findField('Diag_preid');

								v.getStore().load(
								{
									callback: function()
									{
										v.getStore().each(function(record)
										{
											if (record.get('Diag_id') == diag_preid)
											{
												v.fireEvent('select', v, record, 0);
												v.setValue(diag_preid);
												v.fireEvent('change', v, diag_preid);
											}
										}.createDelegate(this));
									},

									params:
									{
										where: "where Diag_id = " + diag_preid
									},

									scope: this
								});
							}

							if (diag_id)
								diagStore.load(
								{
									callback: function()
									{
										diagStore.each(function(record)
										{
											if (record.get('Diag_id') == diag_id)
											{
												this._cmbDiag.setValue(diag_id);

												// #170429
												this._refreshHsnDetails(this._cmbDiag, false);

												this._cmbDiag.fireEvent('select', this._cmbDiag, record, 0);
												this._cmbDiag.fireEvent('change', this._cmbDiag, diag_id);

												base_form.findField('TreatmentClass_id').onLoadStore();
												win.refreshFieldsVisibility();
												win.loadSpecificsTree();
												win.formLoaded = true;
												win.diagIsChanged = false;
											}
										}.createDelegate(this));
									},

									params:
									{
										where: "where Diag_id = " + diag_id
									},

									scope: this
								});
							}

						if (!Ext.isEmpty(diag_fid))
						{
							v = base_form.findField('Diag_fid');

							v.getStore().load(
							{
								callback: function()
								{
									v.setValue(diag_fid);
								},

								params:
								{
									where: "where Diag_id = " + diag_fid
								},

								scope: this
							});
						}

						if (!Ext.isEmpty(diag_lid))
						{
							v = base_form.findField('Diag_lid');

							v.getStore().load(
							{
								callback: function()
								{
									var fld;

									v.setValue(diag_lid);
									this.setDiagConcComboVisible();

									if (diag_concid)
									{
										fld = base_form.findField('Diag_concid');

										fld.getStore().load(
										{
											callback: function()
											{
												fld.setValue(diag_concid);
											},

											params:
											{
												where: "where Diag_id = " + diag_concid
											},

											scope: this
										});
									}
								},

								params:
								{
									where: "where Diag_id = " + diag_lid
								},

								scope: this
							});
						}

						if ( !this.findById('EEPLEF_ResultPanel').hidden ) {
							if ( this.action == 'openEvnPL')
							{
								is_finish = 1;
							}
							if ( this.action == 'closeEvnPL' && getRegionNick() != 'kz')
							{
								is_finish = 2;
							} else {
								is_finish = 1;
							}
							//base_form.findField('EvnPL_IsFinish').fireEvent('change', base_form.findField('EvnPL_IsFinish'), is_finish);
							var is_finish_combo = base_form.findField('EvnPL_IsFinish');
							var is_finish_index = is_finish_combo.getStore().findBy(function(rec) {
								return (rec.get(is_finish_combo.valueField) == is_finish);
							});
							is_finish_combo.fireEvent('select', is_finish_combo, is_finish_combo.getStore().getAt(is_finish_index));

							record = base_form.findField('DirectClass_id').getStore().getById(direct_class_id);

							if ( record ) {
								var direct_class_code = Number(record.get('DirectClass_Code'));

								switch ( direct_class_code ) {
									case 1:
										base_form.findField('LpuSection_oid').setValue(lpu_section_oid);
									break;

									case 2:
										base_form.findField('Lpu_oid').getStore().load({
											callback: function(records, options, success) {
												if ( success ) {
													base_form.findField('Lpu_oid').setValue(lpu_oid);
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
						}

						if ( !Ext.isEmpty(base_form.findField('EvnVizitPL_setDate').getValue()) ) {
							base_form.findField('ServiceType_id').getStore().filterBy(function(rec) {
								return (
									(Ext.isEmpty(rec.get('ServiceType_begDate')) || rec.get('ServiceType_begDate') <= base_form.findField('EvnVizitPL_setDate').getValue())
									&& (Ext.isEmpty(rec.get('ServiceType_endDate')) || rec.get('ServiceType_endDate') > base_form.findField('EvnVizitPL_setDate').getValue())
								);
							});
						}

						index = base_form.findField('ServiceType_id').getStore().findBy(function(rec, id) {
							return (rec.get('ServiceType_id') == service_type_id);
						}.createDelegate(this));

						if ( index >= 0 ) {
							base_form.findField('ServiceType_id').setValue(service_type_id);
						}

						base_form.findField('DirectType_id').fireEvent('change', base_form.findField('DirectType_id'), base_form.findField('DirectType_id').getValue());

						loadMask.hide();
						
						if(this.action == 'copyEvnVizitPL'){
							this.getEvnPLNumber();
							base_form.findField('EvnPL_id').setValue(null);
							base_form.findField('EvnVizitPL_id').setValue(null);
							setCurrentDateTime({
								callback: function() {
									base_form.findField('EvnVizitPL_setDate').fireEvent('change', base_form.findField('EvnVizitPL_setDate'), base_form.findField('EvnVizitPL_setDate').getValue());
								}.createDelegate(this),
								dateField: base_form.findField('EvnVizitPL_setDate'),
								loadMask: false,
								setDate: true,
								setDateMaxValue: true,
								setDateMinValue: false,
								setTime: true,
								timeField: base_form.findField('EvnVizitPL_setTime'),
								windowId: this.id
							});

							var lps_combo = base_form.findField('LpuSection_id');
							var msf_combo = base_form.findField('MedStaffFact_id');
							lps_combo.getStore().clearFilter();
							msf_combo.getStore().clearFilter();
							lps_combo.getStore().load({
								callback: function(){
									lps_combo.getStore().filterBy(function(rec){
										var flag = false;
											if(rec.get('LpuSection_id') == getGlobalOptions().CurLpuSection_id){
												lps_combo.setValue(getGlobalOptions().CurLpuSection_id);
												flag = true;
											}
										return flag;
									});

									msf_combo.getStore().load({
										callback: function(){
											msf_combo.getStore().filterBy(function(rec, id){
												var flag = false;
												if(rec.get('MedStaffFact_id') == getGlobalOptions().CurMedStaffFact_id){
													msf_combo.setValue(getGlobalOptions().CurMedStaffFact_id);
													msf_combo.fireEvent('change',msf_combo,msf_combo.getValue());
													flag = true;
												}
												return flag;
											});
										}
									});
								}
							});
						}
						if (regNick == 'ekb' && this.findById('EEPLEF_PersonInformationFrame').getFieldValue('Person_IsAnonym') == 2) {
							base_form.findField('PayType_id').setFieldValue('PayType_SysNick','bud');
							base_form.findField('PayType_id').disable();
						}

						if (!Ext.isEmpty(act.result.data.DrugTherapyScheme_ids)) {
							this.DrugTherapySchemePanel.show();
							this.DrugTherapySchemePanel.setIds(act.result.data.DrugTherapyScheme_ids);
						}

						base_form.clearInvalid();
						this.syncSize();
						this.doLayout();

						if ( this.action.inlist([ 'viewEvnPL', 'viewEvnVizitPL' ]) ) {
							this.buttons[this.buttons.length - 1].focus();
						}
						else {
							if ( !this.findById('EEPLEF_DirectInfoPanel').hidden ) {
								base_form.findField('EvnPL_NumCard').focus(false, 250);
							}
							else if ( !this.findById('EEPLEF_EvnVizitPLPanel').hidden ) {
								base_form.findField('EvnVizitPL_setDate').focus(false, 250);
							}
							else if ( !this.findById('EEPLEF_ResultPanel').hidden ) {
								base_form.findField('EvnPL_IsFinish').focus(false, 250);
							}
							else {
								this.buttons[this.buttons.length - 1].focus();
							}
						}

						base_form.findField('PayType_id').fireEvent('change',base_form.findField('PayType_id'),base_form.findField('PayType_id').getValue());
						base_form.findField('TreatmentClass_id').fireEvent('change', base_form.findField('TreatmentClass_id'), base_form.findField('TreatmentClass_id').getValue());

						if (!Ext.isEmpty(PersonDisp_id)) {
							base_form.findField('PersonDisp_id').getStore().load({
								callback: function() {
									base_form.findField('PersonDisp_id').setValue(PersonDisp_id);
								}.createDelegate(this),
								params: {
									Person_id: base_form.findField('Person_id').getValue(),
									PersonDisp_id: PersonDisp_id
								}
							});
						}
						
						if (regNick == 'ekb') {
							if(base_form.findField('Mes_id').getValue()>0)
							base_form.findField('Mes_id').fireEvent('change', base_form.findField('Mes_id'), base_form.findField('Mes_id').getValue());
							if(base_form.findField('UslugaComplex_uid').getValue()>0)
							base_form.findField('UslugaComplex_uid').fireEvent('change', base_form.findField('UslugaComplex_uid'), base_form.findField('UslugaComplex_uid').getValue());
							
							if (base_form.findField('EvnVizitPL_Index').getValue() > 0) {
								base_form.findField('Mes_id').disable();
							}
							
							if (this.action == 'addEvnVizitPL' && !Ext.isEmpty(base_form.findField('Mes_id').getValue())) {
								base_form.findField('Mes_id').disable();
							}
						}
                        if (regNick.inlist([ 'buryatiya', 'kareliya' ]) ) {
							if ( this.allowMorbusVizitOnly == true ) {
                                base_form.findField('VizitType_id').setFieldValue('VizitType_SysNick', 'desease');
                                base_form.findField('VizitType_id').disable();
                            }
						}
						win.fo = false;
						this.findById('EEPLEF_EvnDiagPLGrid').getStore().load({
							params: {
								EvnVizitPL_id: this.FormPanel.getForm().findField('EvnVizitPL_id').getValue()
							},
							callback: function() {
								this.loadSpecificsTree();
							}.createDelegate(this)
						});
						this.IsLoading = false;
						return true;
					}.createDelegate(this),
					url: '/?c=EvnPL&m=loadEmkEvnPLEditForm'
				});
				//this.fo = false;
			break;

			default:
				loadMask.hide();
				this.hide();
			break;
		}
		if (getRegionNick() == 'kz') {
			setTimeout(function() {
				base_form.findField('PayType_id').disable();
			}, 1000);
		}
		Ext.QuickTips.register({
			target: base_form.findField('EvnVizitPL_BiopsyDate').getEl(),
			text: 'Дата взятия биопсии, по результатам которой снимается подозрение на ЗНО',
			enabled: true,
			showDelay: 5,
			trackMouse: true,
			autoShow: true
		});
	},
	maximizable: true,
	minHeight: 410,
	minWidth: 720,
	height: 410,
	width: 720
});
