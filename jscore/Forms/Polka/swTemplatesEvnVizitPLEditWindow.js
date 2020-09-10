/**
* swEvnVizitPLEditWindow - окно редактирования/добавления посещения пациентом поликлиники.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Polka
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Stas Bykov aka Savage (savage@swan.perm.ru)
* @version      0.001-23.07.2009
* @comment      Префикс для id компонентов EVPLEF (EvnVizitPLEditForm)
*
*
* @input data: action - действие (add, edit, view)
*
*
* Использует: окно редактирования диагноза (swEvnDiagPLEditWindow)
*             окно редактирования рецепта (swEvnReceptEditWindow)
*             окно выбора типа услуги (swEvnUslugaSetWindow)
*             окно редактирования услуги (swEvnUslugaEditWindow)
*/
/*NO PARSE JSON*/

sw.Promed.swEvnVizitPLEditWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swEvnVizitPLEditWindow',
	objectSrc: '/jscore/Forms/Polka/swTemplatesEvnVizitPLEditWindow.js',
	action: null,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: false,
	closeAction: 'hide',
	collapsible: true,

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

	// 2. Панель формы (id = 'EvnVizitPLEditForm'):
	_formPanel: undefined,

	// 3. Форма (_formPanel.getForm()):
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

	loadLpuSectionProfileDop: function() {

		// Переменная для региональных настроек:
		var regNick = getRegionNick();

		var win = this;
		var base_form = this.findById('EvnVizitPLEditForm').getForm();

		
			var oldValue = base_form.findField('LpuSectionProfile_id').getValue();

			if (!Ext.isEmpty(base_form.findField('LpuSection_id').getValue())) {
				if (
					!base_form.findField('LpuSectionProfile_id').getStore().baseParams.LpuSection_id || 
					base_form.findField('LpuSection_id').getValue() != base_form.findField('LpuSectionProfile_id').getStore().baseParams.LpuSection_id || 
					regNick == 'ekb'
				) {
					this.IsProfLoading = true;
					base_form.findField('LpuSectionProfile_id').lastQuery = '';
					base_form.findField('LpuSectionProfile_id').getStore().removeAll();
					base_form.findField('LpuSectionProfile_id').getStore().baseParams.LpuSection_id = base_form.findField('LpuSection_id').getValue();
					base_form.findField('LpuSectionProfile_id').getStore().baseParams.onDate = (!Ext.isEmpty(base_form.findField('EvnVizitPL_setDate').getValue()) ? base_form.findField('EvnVizitPL_setDate').getValue().format('d.m.Y') : getGlobalOptions().date);
					base_form.findField('LpuSectionProfile_id').getStore().load({
						callback: function () {
							var comboLpuSectionProfile = base_form.findField('LpuSectionProfile_id');
							var index = comboLpuSectionProfile.getStore().findBy(function (rec) {
								return (rec.get('LpuSectionProfile_id') == oldValue);
							});
							var otherVizit = (win.OtherVizitList && Ext.isArray(win.OtherVizitList) && win.OtherVizitList.length>0) ? win.OtherVizitList[win.OtherVizitList.length-1] : null;

							if (index == -1) {
								base_form.findField('LpuSectionProfile_id').clearValue();
								base_form.findField('LpuSectionProfile_id').fireEvent('change', base_form.findField('LpuSectionProfile_id'), base_form.findField('LpuSectionProfile_id').getValue());
								
								if (!oldValue || win.LpuSection_id != base_form.findField('LpuSection_id').getValue()) {
									if (base_form.findField('MedStaffFact_id').getFieldValue('LpuSectionProfile_msfid') > 0 && regNick == 'ekb') {
										base_form.findField('LpuSectionProfile_id').setValue(base_form.findField('MedStaffFact_id').getFieldValue('LpuSectionProfile_msfid'));
										base_form.findField('LpuSectionProfile_id').fireEvent('change', base_form.findField('LpuSectionProfile_id'), base_form.findField('LpuSectionProfile_id').getValue());
									} 
									else if (regNick == 'vologda' && otherVizit && comboLpuSectionProfile.findRecord('LpuSectionProfile_id', otherVizit.LpuSectionProfile_id)){
										//Если в ТАП добавлено хотя бы одно посещение,  по умолчанию в поле устанавливается профиль отделения из ранее добавленного посещения
										comboLpuSectionProfile.setValue(otherVizit.LpuSectionProfile_id);
										comboLpuSectionProfile.fireEvent('change', comboLpuSectionProfile, comboLpuSectionProfile.getValue());
									}
									else if (base_form.findField('LpuSection_id').getFieldValue('LpuSectionProfile_id') > 0) {
										base_form.findField('LpuSectionProfile_id').setValue(base_form.findField('LpuSection_id').getFieldValue('LpuSectionProfile_id'));
										base_form.findField('LpuSectionProfile_id').fireEvent('change', base_form.findField('LpuSectionProfile_id'), base_form.findField('LpuSectionProfile_id').getValue());
									} 
									else if (base_form.findField('LpuSectionProfile_id').getStore().getCount() > 0) {
										base_form.findField('LpuSectionProfile_id').setValue(base_form.findField('LpuSectionProfile_id').getStore().getAt(0).get('LpuSectionProfile_id'));
										base_form.findField('LpuSectionProfile_id').fireEvent('change', base_form.findField('LpuSectionProfile_id'), base_form.findField('LpuSectionProfile_id').getValue());
									}
								}
							} else {
								base_form.findField('LpuSectionProfile_id').setValue(oldValue);
								base_form.findField('LpuSectionProfile_id').fireEvent('change', base_form.findField('LpuSectionProfile_id'), base_form.findField('LpuSectionProfile_id').getValue());
							}

							win.LpuSection_id = base_form.findField('LpuSection_id').getValue();
							win.setDefaultMedicalCareKind();
							win.IsProfLoading = false;
						}
					});
				}
			}
		
	},
	loadMesCombo:function () {
		var win = this;
		var base_form = this.findById('EvnVizitPLEditForm').getForm();
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
					base_form.findField('UslugaComplex_uid').setMesOldVizit_id(null)
				}
			}
		});
		
	},
	checkAndOpenRepositoryObserv: function () {
		var win = this;
		var base_form = this.findById('EvnVizitPLEditForm').getForm();
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
	setDiagSpidComboDisabled: function() {

		if (!getRegionNick().inlist(['perm', 'msk']) || this.action == 'view') return false;

		var base_form = this.findById('EvnVizitPLEditForm').getForm();
		var diag_spid_combo = base_form.findField('Diag_spid');
		var iszno_checkbox = this.findById('EVPLEF_EvnVizitPL_IsZNOCheckbox');

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
	//добавление карты первичного онкоскрининга
	addNewEvnPLDispScreenOnko: function() {
		var me = this;
		var base_form = this.findById('EvnVizitPLEditForm').getForm();
		var loadMask = new Ext.LoadMask(this.getEl(), {msg: LOAD_WAIT});
		loadMask.show();

		Ext.Ajax.request({
			url: '/?c=EvnPLDispScreenOnko&m=checkEvnPLDispScreenOnkoExists',
			params: {
				Person_id: base_form.findField('Person_id').getValue()
			},
			callback: function(options, success, response) {
				loadMask.hide();

				var response_obj = Ext.util.JSON.decode(response.responseText);

				if (response_obj.length) {
					sw.swMsg.show({
						buttons: sw.swMsg.YESNO,
						fn: function(buttonId, text, obj) {
							if (buttonId == 'yes') {
								var params = {
									Person_id: base_form.findField('Person_id').getValue(),
									PersonEvn_id: base_form.findField('PersonEvn_id').getValue(),
									Server_id: base_form.findField('Server_id').getValue(),
									UserMedStaffFact_id: me.userMedStaffFact.MedStaffFact_id,
									userMedStaffFact: me.userMedStaffFact,
									LpuSection_id: base_form.findField('LpuSection_id').getValue(),
									MedStaffFact_id: base_form.findField('MedStaffFact_id').getValue(),
									Person_Firname: me.findById('EVPLEF_PersonInformationFrame').getFieldValue('Person_Firname'),
									Person_Surname: me.findById('EVPLEF_PersonInformationFrame').getFieldValue('Person_Surname'),
									Person_Secname: me.findById('EVPLEF_PersonInformationFrame').getFieldValue('Person_Secname'),
									Person_Birthday: me.findById('EVPLEF_PersonInformationFrame').getFieldValue('Person_Birthday'),
									EvnPLDispScreenOnko_id: response_obj[0],
									callback: function() {
										me.EvnPLDispScreenOnkoGrid.loadData({globalFilters: {EvnSection_id: base_form.findField('EvnVizitPL_id').getValue()}});
									}
								}
								getWnd('swEvnPLDispScreenOnkoWindow').show(params);
							}
						}.createDelegate(this),
						icon: Ext.MessageBox.QUESTION,
						msg: langs('У пациента есть пройдённый осмотр по онкологии. Открыть?'),
						title: langs('Вопрос')
					});
				} else {
					me._addNewEvnPLDispScreenOnko();
				}
			}
		});

	},
	_addNewEvnPLDispScreenOnko: function() {
		var me = this;
		var base_form = this.findById('EvnVizitPLEditForm').getForm();

		var Evn_pid = base_form.findField('EvnVizitPL_id').getValue();
		if (Ext.isEmpty(Evn_pid) || Evn_pid == 0) {
			this.doSave({
				openChildWindow: function () {
					mw._addNewEvnPLDispScreenOnko();
				}
			});
			return;
		}

		var loadMask = new Ext.LoadMask(this.getEl(), {msg: 'Создание нового первичного онкоскрининга...'});
		loadMask.show();

		Ext.Ajax.request({
			url: '/?c=EvnPLDispScreenOnko&m=addEvnPLDispScreenOnko',
			params: {
				PersonEvn_id: base_form.findField('PersonEvn_id').getValue(),
				Server_id: base_form.findField('Server_id').getValue(),
				Evn_pid: Evn_pid,
				Lpu_id: getGlobalOptions().lpu_id
			},
			callback: function(options, success, response) {
				loadMask.hide();
				var response_obj = Ext.util.JSON.decode(response.responseText);
				if (response_obj.success) {
					var params = {
						Person_id: base_form.findField('Person_id').getValue(),
						PersonEvn_id: base_form.findField('PersonEvn_id').getValue(),
						Server_id: base_form.findField('Server_id').getValue(),
						UserMedStaffFact_id: me.userMedStaffFact.MedStaffFact_id,
						userMedStaffFact: me.userMedStaffFact,
						LpuSection_id: base_form.findField('LpuSection_id').getValue(),
						MedStaffFact_id: base_form.findField('MedStaffFact_id').getValue(),
						Person_Firname: me.findById('EVPLEF_PersonInformationFrame').getFieldValue('Person_Firname'),
						Person_Surname: me.findById('EVPLEF_PersonInformationFrame').getFieldValue('Person_Surname'),
						Person_Secname: me.findById('EVPLEF_PersonInformationFrame').getFieldValue('Person_Secname'),
						Person_Birthday: me.findById('EVPLEF_PersonInformationFrame').getFieldValue('Person_Birthday'),
						EvnPLDispScreenOnko_id: response_obj['EvnPLDispScreenOnko_id'],
						callback: function() {
							me.EvnPLDispScreenOnkoGrid.loadData({globalFilters: {EvnSection_id: base_form.findField('EvnVizitPL_id').getValue()}});
						}
					}
					getWnd('swEvnPLDispScreenOnkoWindow').show(params);
				}
			}
		});

	},
	openEvnPLDispScreenOnko: function(){
		var me = this;
		var base_form = this.findById('EvnVizitPLEditForm').getForm();
		var grid = this.EvnPLDispScreenOnkoGrid.getGrid();
		var rec = grid.getSelectionModel().getSelected();

		if (!rec || !rec.get('EvnPLDispScreenOnko_id')) {
			return false;
		}

		var params = {
			Person_id: base_form.findField('Person_id').getValue(),
			PersonEvn_id: base_form.findField('PersonEvn_id').getValue(),
			Server_id: base_form.findField('Server_id').getValue(),
			UserMedStaffFact_id: me.userMedStaffFact.MedStaffFact_id,
			userMedStaffFact: me.userMedStaffFact,
			LpuSection_id: base_form.findField('LpuSection_id').getValue(),
			MedStaffFact_id: base_form.findField('MedStaffFact_id').getValue(),
			Person_Firname: me.findById('EVPLEF_PersonInformationFrame').getFieldValue('Person_Firname'),
			Person_Surname: me.findById('EVPLEF_PersonInformationFrame').getFieldValue('Person_Surname'),
			Person_Secname: me.findById('EVPLEF_PersonInformationFrame').getFieldValue('Person_Secname'),
			Person_Birthday: me.findById('EVPLEF_PersonInformationFrame').getFieldValue('Person_Birthday'),
			EvnPLDispScreenOnko_id: rec.get('EvnPLDispScreenOnko_id'),
			callback: function() {
				me.EvnPLDispScreenOnkoGrid.loadData({globalFilters: {EvnSection_id: base_form.findField('EvnVizitPL_id').getValue()}});
			}
		}
		getWnd('swEvnPLDispScreenOnkoWindow').show(params);
	},
	deleteEvnPLDispScreenOnko: function(){
		var me = this;
		var base_form = this.findById('EvnVizitPLEditForm').getForm();
		var grid = this.EvnPLDispScreenOnkoGrid.getGrid();
		var rec = grid.getSelectionModel().getSelected();

		if (!rec || !rec.get('EvnPLDispScreenOnko_id')) {
			return false;
		}

		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Удаление..."});
		loadMask.show();

		Ext.Ajax.request({
			callback: function (options, success, response) {
				loadMask.hide();
				if (success) {
					me.EvnPLDispScreenOnkoGrid.loadData({globalFilters: {EvnSection_id: base_form.findField('EvnVizitPL_id').getValue()}});
				} else {
					sw.swMsg.alert('Ошибка', 'При удалении возникли ошибки');
					return false;
				}
			}.createDelegate(this),
			params: {
				Evn_id: rec.get('EvnPLDispScreenOnko_id')
			},
			url: '/?c=Evn&m=deleteEvn'
		});
	},
	deleteEvent: function(event) {
		if ( this.action == 'view' ) {
			return false;
		}

		if ( event != 'EvnDiagPL' && event != 'EvnDirection' && event != 'EvnRecept' && event != 'EvnUsluga' && event != 'EvnDirection' ) {
			return false;
		}

		var base_form = this.findById('EvnVizitPLEditForm').getForm();
		var error = '';
		var grid = null;
		var question = '';
		var params = new Object();
		var url = '';

		switch ( event ) {
			case 'EvnDiagPL':
				error = lang['pri_udalenii_soputstvuyuschego_diagnoza_voznikli_oshibki'];
				grid = this.findById('EVPLEF_EvnDiagPLGrid');
				question = lang['udalit_soputstvuyuschiy_diagnoz'];
				url = '/?c=EvnPL&m=deleteEvnDiagPL';
			break;

			case 'EvnRecept':
				grid = this.findById('EVPLEF_EvnReceptGrid');
			break;

			case 'EvnUsluga':
				error = lang['pri_udalenii_uslugi_voznikli_oshibki'];
				grid = this.findById('EVPLEF_EvnUslugaGrid');
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

			case 'EvnRecept':
				params['EvnRecept_id'] = selected_record.get('EvnRecept_id');
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
					if ( event == 'EvnRecept' ) {
						getWnd('swEvnReceptDeleteWindow').show({
							callback: function() {
								grid.getStore().reload();
							},
							EvnRecept_id: params['EvnRecept_id'],
							onHide: function() {
								
							}
						});
					}
					else {
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
										this.checkMesOldUslugaComplexFields();
										this.uetValuesRecount();
										this.checkAbort();
									}

									if ( grid.getStore().getCount() == 0 ) {
										grid.getTopToolbar().items.items[1].disable();
										grid.getTopToolbar().items.items[2].disable();
										grid.getTopToolbar().items.items[3].disable();
										LoadEmptyRow(grid);
/*
										if ( event == 'EvnUsluga' ) {
											base_form.findField('EvnVizitPL_Uet').enable();
											base_form.findField('EvnVizitPL_UetOMS').enable();
										}
*/
									}

									if ( event == 'EvnDiagPL' ) {
										this.loadSpecificsTree();
									}
								}
								
								grid.getView().focusRow(0);
								grid.getSelectionModel().selectFirstRow();
							}.createDelegate(this),
							url: url
						});
					}
				}
			}.createDelegate(this),
			icon: Ext.MessageBox.QUESTION,
			msg: question,
			title: lang['vopros']
		});
	},
	setMKB: function(){
		var parentWin =this
		var base_form = this.findById('EvnVizitPLEditForm').getForm();
		var sex = parentWin.findById('EVPLEF_PersonInformationFrame').getFieldValue('Sex_Code');
		var age = swGetPersonAge(parentWin.findById('EVPLEF_PersonInformationFrame').getFieldValue('Person_Birthday'),base_form.findField('EvnVizitPL_setDate').getValue());
		base_form.findField('Diag_id').setMKBFilter(age,sex,true);
	},
	setInoterFilter: function() {
		var base_form = this.findById('EvnVizitPLEditForm').getForm();
		if ( getRegionNick() == 'buryatiya' ) {
			var oms_spr_terr_code = this.findById('EVPLEF_PersonInformationFrame').getFieldValue('OmsSprTerr_Code');
			base_form.findField('UslugaComplex_uid').getStore().baseParams.isInoter = (Ext.isEmpty(oms_spr_terr_code) || oms_spr_terr_code == 0 || oms_spr_terr_code > 100);
		}
	},
	getTemplateFavorites: function() {
		var cur_wnd = this;
		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Загрузка списка часто используемых шаблонов..."});
		loadMask.show();
		Ext.Ajax.request({
			failure: function(response, options) {
				loadMask.hide();
				sw.swMsg.alert(lang['oshibka'], lang['pri_zagruzke_spiska_chasto_ispolzuemyih_shablonov_voznikli_oshibki']);
			},
			params: {EvnClass_id: cur_wnd.EvnXmlPanel.getOption('EvnClass_id')},
			success: function(response, options) {
				loadMask.hide();
				//cur_wnd.EvnXmlPanel.restoreEditing();
				if ( response.responseText )
				{
					var result = {
                        data: Ext.util.JSON.decode(response.responseText)
                    };
					if ( Ext.isArray(result.data) && result.data.length > 0 )
					{
						var TemplateFavoritesContextMenu = new Ext.menu.Menu();
						for (i=0; i < result.data.length; i++)
						{
							TemplateFavoritesContextMenu.add(new Ext.Action({
								name: result.data[i].XmlTemplateFavorites_id,
								//text: result.data[i].XmlTemplate_Caption + ' (' + result.data[i].XmlTemplateFavorites_CountLoad + ')',
								text: '<B>' + result.data[i].XmlTemplate_Caption + '</B>',
								tooltip: result.data[i].XmlTemplate_Caption,
								template_id: result.data[i].XmlTemplate_id,
								iconCls : 'template16',
								handler: function() {
                                    cur_wnd.EvnXmlPanel.onBeforeCreate(cur_wnd.EvnXmlPanel, 'onSelectXmlTemplate', this.template_id);
								}
							}));
						}
						//TemplateFavoritesContextMenu.show(Ext.getCmp('EVPLEF_TemplateFavorites_btn').getEl());
						TemplateFavoritesContextMenu.showAt(Ext.getCmp('EVPLEF_TemplateFavorites_btn').getEl().getXY());
					} else {
                        var msg = result.data.Error_Msg || lang['spisok_nedavnih_shablonov_pust'];
                        sw.swMsg.alert(lang['uvedomlenie'], msg);
                    }
				}
			},
			url: '/?c=XmlTemplate&m=getFavorites'
		});
	},
	filterVizitTypeCombo: function() {
		var regNick = getRegionNick();
		var base_form = this.findById('EvnVizitPLEditForm').getForm();
		var formDate = base_form.findField('EvnVizitPL_setDate').getValue();

		if (regNick == 'kz') return false;

		base_form.findField('VizitType_id').setTreatmentClass(base_form.findField('TreatmentClass_id').getValue());

		if (regNick == 'kareliya' && !Ext.isEmpty(base_form.findField('PayType_id').getValue())) {
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

		var base_form = this.findById('EvnVizitPLEditForm').getForm();
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
			var tcId = rec.get('TreatmentClass_id');

			if (DiagCode == 'Z51.5') {
				return (tcId.inlist([ 9 ]));
			} else if (DiagCode.substr(0,1) == 'Z' || (regNick == 'perm' && DiagCode.substr(0,3) == 'W57')) {
				return (tcId.inlist([ 6, 7, 8, 9, 10, 11, 12 ]));
			} else if (regNick == 'penza') {
				return (tcId.inlist([ 1, 2, 3, 4, 11, 13 ]));
			} else {
				return (tcId.inlist([ 1, 2, 3, 4, 13 ]));
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
			base_form = this.findById('EvnVizitPLEditForm').getForm();

		if (this.IsLoading) return false;
		if (this.IsProfLoading) return false;

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
		var base_form = this.findById('EvnVizitPLEditForm').getForm();
		var flag = false;

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
				flag = true;
				break;
			}
		}
		return flag;
	},

	getLastHsnData: function(diag_id, person_id) {
		var regNick = getRegionNick();
		var base_form = this.findById('EvnVizitPLEditForm').getForm();
		var params = {};
		if (regNick == 'ufa' || regNick == 'ekb') {
			params = {Diag_agid: diag_id, Person_id: person_id}
		} else {
			params = {Diag_id: diag_id, Person_id: person_id}
		}
		var win = this;
		var action = win.action;
		Ext.Ajax.request({
			url: '/?c=EvnPL&m=getLastHsnData',
			params: params,
			callback: function(options, success, response) {
				if ( success ) {

					var result = Ext.util.JSON.decode(response.responseText);
					console.log('result',result);
					if((result.HSNFuncClass_id || result.HSNStage_id) && action != 'view') {
						sw.swMsg.alert('', 'Пациенту в предыдущем случае лечения установлены стадия ХСН и функциональный класс. При необходимости можно изменить стадию ХСН и функциональный класс. ');
					}
					base_form.findField('HSNStage_id').setValue(result.HSNStage_id ? result.HSNStage_id : null);
					base_form.findField('HSNFuncClass_id').setValue(result.HSNFuncClass_id ? result.HSNFuncClass_id : null);
				} else {
					base_form.findField('HSNStage_id').setValue(null);
					base_form.findField('HSNFuncClass_id').setValue(null);
				}
			}
		});
	},

	refreshFieldsVisibility: function(fieldNames) {
		var allowedFields = [
			'TumorStage_id', 'PregnancyEvnVizitPL_Period', 'PainIntensity_id'
		];
		var win = this;
		var base_form = win.findById('EvnVizitPLEditForm').getForm();
		var persFrame = win.findById('EVPLEF_PersonInformationFrame');
		if (typeof fieldNames == 'string') fieldNames = [fieldNames];

		var action = win.action;
		var regNick = getRegionNick();
		var Sex_Code = persFrame.getFieldValue('Sex_Code');
		var Person_BirthDay = persFrame.getFieldValue('Person_Birthday');

		base_form.items.each(function(field){
			if (!Ext.isEmpty(fieldNames) && !field.getName().inlist(fieldNames)) return;
			if (!field.getName().inlist(allowedFields)) return;

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
		// options.ignoreEvnUslugaCountCheck @Boolean Не проверять наличие выполненных услуг, если true
		// options.ignoreEvnVizitPLSetDateCheck @Boolean Не проверять дату посещения, если true
		// options.openChildWindow @Function Открыть дочернее окно после сохранения

		// Переменная для региональных настроек:
		var regNick = getRegionNick();

		if ( this.formStatus == 'save' ) {
			return false;
		}

		if ( typeof options != 'object' ) {
			options = new Object();
		}

		this.formStatus = 'save';

		var win = this;
		var base_form = this._form;

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

		if (regNick.inlist(['khak','buryatiya'])) {
			if(base_form.findField('LpuSectionProfile_id').isVisible() === false)
				base_form.findField('LpuSectionProfile_id').setAllowBlank(true);
		}

		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					this.formStatus = 'edit';
					base_form.getFirstInvalidEl().focus(false);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		if (regNick == 'ekb') {
			var fieldPayType = base_form.findField('PayType_id'); // вид опалаты
			var fieldMes = base_form.findField('Mes_id'); // МЭС
			var fieldUslugaComplex = base_form.findField('UslugaComplex_uid'); // код посещения

			if (Ext.isEmpty(base_form.findField('Mes_id').getValue()) && Ext.isEmpty(base_form.findField('UslugaComplex_uid').getValue()) && base_form.findField('PayType_id').getFieldValue('PayType_SysNick') != 'bud' && base_form.findField('PayType_id').getFieldValue('PayType_SysNick') != 'dms') {
				sw.swMsg.alert(lang['oshibka'], lang['neobhodimo_ukazat_hotya_byi_odno_iz_poley_mes_ili_kod_posescheniya']);
				this.formStatus = 'edit';
				return false;
			}

			//эта проверка не должна отрабатывать "при автоматическом сохранении посещения, при вызове формы добавления услуги" #109640
			if( fieldMes.getValue() && options.isDoSave != undefined && options.isDoSave) {
				var flagMes = false;
				/*
				if( !fieldMes.getFieldValue('MesOldVizit_Code').inlist([811, 812]) && fieldPayType.getFieldValue('PayType_SysNick') == 'bud' && !fieldUslugaComplex.getValue() ){
					sw.swMsg.alert(lang['oshibka'], 'Поле <b>&laquo; Код посещения &raquo;</b> обязательно для ввода');
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
				
				if( fieldMes.getFieldValue('MesOldVizit_Code').inlist([811, 812, 901, 902, 664]) ){
					var evn_usluga_store = this.findById('EVPLEF_EvnUslugaGrid').getStore();
					if( evn_usluga_store.getCount() == 0 || (evn_usluga_store.getCount() == 1 && !evn_usluga_store.getAt(0).get('EvnUsluga_id')) ){
						sw.swMsg.alert(langs('Ошибка'), 'Если в поле <b> &laquo; МЭС &raquo;</b> указано значение 811, 812, 901, 902, 664, то в разделе <b>&laquo; 5.Услуги &raquo;</b> должна быть добавлена услуга');
						this.formStatus = 'edit';
						return false;
					}
				}
			}
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
		
/*
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
*/
		// Если ignoreEvnVizitPLSetDateCheck = false либо не задан и посещение добавляется, то проверяем дату посещения
		if ( !options.ignoreEvnVizitPLSetDateCheck && this.action == 'add' ) {
			var evn_vizit_pl_set_dt = getValidDT(Ext.util.Format.date(base_form.findField('EvnVizitPL_setDate').getValue(), 'd.m.Y'), base_form.findField('EvnVizitPL_setTime').getValue());
			var min_available_date = new Date().add(Date.MONTH, -3);

			if ( evn_vizit_pl_set_dt < min_available_date ) {
				sw.swMsg.show({
					buttons: Ext.Msg.YESNO,
					fn: function(buttonId, text, obj) {
						this.formStatus = 'edit';

						if ( buttonId == 'yes' ) {
							options.ignoreEvnVizitPLSetDateCheck = true;
							this.doSave(options);
						}
						else {
							base_form.findField('EvnVizitPL_setDate').focus(true);
						}
					}.createDelegate(this),
					icon: Ext.MessageBox.QUESTION,
					msg: lang['data_posescheniya_otlichaetsya_ot_tekuschey_bolee_chem_na_3_mesyatsa_sohranit_poseschenie'],
					title: lang['vopros']
				});
				return false;
			}
		}
			
		if(this.errorControlCodaVisits()){
			sw.swMsg.alert(langs('Сообщение'), langs('Сохранение посещения невозможно, т.к. в рамках текущего ТАП специалистом другого профиля уже добавлено посещение. '));
			this.formStatus = 'edit';
			return false;
		}		

		var params = new Object();
		var record = null;

		if ( typeof options == 'object' ) {
/*
			if ( options.ignoreEvnUslugaCountCheck == true ) {
				params.ignoreEvnUslugaCountCheck = 1;
			}
*/
            if ( options.ignoreEvnVizitPLSetDateCheck == true ) {
                params.ignoreEvnVizitPLSetDateCheck = 1;
            }
            if ( options.ignoreDayProfileDuplicateVizit == true ) {
                params.ignoreDayProfileDuplicateVizit = 1;
            }

		}

		var diag_code = '';
		var diag_name = '';
		var lpu_section_profile_code = '';
		var med_personal_fio = '';
		var pay_type_nick = '';
		var service_type_name = '';
		var service_type_sysnick = '';

		record = base_form.findField('MedStaffFact_id').getStore().getById(base_form.findField('MedStaffFact_id').getValue());
		if ( record ) {
			lpu_section_profile_code = record.get('LpuSectionProfile_Code');
			med_personal_fio = record.get('MedPersonal_Fio');
			base_form.findField('MedPersonal_id').setValue(record.get('MedPersonal_id'));
		}

		record = base_form.findField('MedStaffFact_sid').getStore().getById(base_form.findField('MedStaffFact_sid').getValue());
		if ( record ) {
			base_form.findField('MedPersonal_sid').setValue(record.get('MedPersonal_id'));
		}

		record = base_form.findField('PayType_id').getStore().getById(base_form.findField('PayType_id').getValue());
		if ( record ) {
			pay_type_nick = record.get('PayType_SysNick');
		}

		record = base_form.findField('Diag_id').getStore().getById(base_form.findField('Diag_id').getValue());
/*
		// Диагноз для Уфы - обязательное поле
		if ( !record && getGlobalOptions().region && getGlobalOptions().region.nick == 'ufa' ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					this.formStatus = 'edit';
					base_form.findField('Diag_id').focus(true);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: lang['pole_diagnoz_obyazatelno_dlya_zapolneniya'],
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
*/
		var person_age = swGetPersonAge(this.findById('EVPLEF_PersonInformationFrame').getFieldValue('Person_Birthday'), base_form.findField('EvnVizitPL_setDate').getValue());

		if(!this.hasPreviusChildVizit()&&person_age!=-1&&!options.ignoreLpuSectionAgeCheck&& ((base_form.findField('LpuSection_id').getFieldValue('LpuSectionAge_id') == 1 && person_age <= 17) || (base_form.findField('LpuSection_id').getFieldValue('LpuSectionAge_id') == 2 && person_age >= 18))) {

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
		if ( record ) {
			diag_code = record.get('Diag_Code');
			diag_name = record.get('Diag_Name');

			if ( diag_code.substr(0, 1).toUpperCase() != 'Z' && !base_form.findField('DeseaseType_id').getValue() ) {
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
				var sex_code = this.findById('EVPLEF_PersonInformationFrame').getFieldValue('Sex_Code');
				
				var person_age_month = swGetPersonAgeMonth(this.findById('EVPLEF_PersonInformationFrame').getFieldValue('Person_Birthday'), base_form.findField('EvnVizitPL_setDate').getValue());
				var person_age_day = swGetPersonAgeDay(this.findById('EVPLEF_PersonInformationFrame').getFieldValue('Person_Birthday'), base_form.findField('EvnVizitPL_setDate').getValue());

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
				if ( pay_type_nick == 'oms' ) {
					if ( lpu_section_profile_code.inlist([ '658', '684', '558', '584' ])&&pay_type_nick == 'oms' ) {
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
						if (regNick == 'ekb') {
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
				if (pay_type_nick=='bud' && base_form.findField('UslugaComplex_uid').getValue()== 4568436) {
					if ( record.get('DiagFinance_IsOms') == 0 ) {
						var textMsg = 'Услуга В01.069.998 может быть выбрана только при диагнозе, оплачиваемом по ОМС';
						sw.swMsg.alert('Ошибка', textMsg, function() {
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
			} else if (regNick == 'buryatiya' ) {
				if (pay_type_nick == 'oms' ) {
					var sex_code = this.findById('EVPLEF_PersonInformationFrame').getFieldValue('Sex_Code');
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
			} else if (regNick == 'astra' ) {
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
								msg: lang['vyibrannyiy_diagnoz_ne_oplachivaetsya_po_oms_prodoljit_sohranenie'],
								title: lang['prodoljit_sohranenie']
							});
							return false;
						}
					}
				}
			} else if (regNick == 'kaluga' ) {
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
								msg: lang['vyibrannyiy_diagnoz_ne_oplachivaetsya_po_oms_poetomu_sluchay_ne_budet_vklyuchen_v_reestr_prodoljit_sohranenie'],
								title: lang['prodoljit_sohranenie']
							});
							return false;
						}
					}
				}
			} else if (regNick == 'kareliya' ) {
				if (!options.ignoreDiagFinance && pay_type_nick == 'oms') {
					var sex_code = this.findById('EVPLEF_PersonInformationFrame').getFieldValue('Sex_Code');
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
				if (regNick != 'perm' && (pay_type_nick == 'oms' || (regNick == 'ekb' && pay_type_nick == 'bud'))) {
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
						if (regNick == 'ekb') {
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
						var oms_spr_terr_code = this.findById('EVPLEF_PersonInformationFrame').getFieldValue('OmsSprTerr_Code');
						var person_age = swGetPersonAge(this.findById('EVPLEF_PersonInformationFrame').getFieldValue('Person_Birthday'), base_form.findField('EvnVizitPL_setDate').getValue());
						var sex_code = this.findById('EVPLEF_PersonInformationFrame').getFieldValue('Sex_Code');
						var DiagOMS = new RegExp("^Z80.[0-9]");
						
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
						else if (Number(record.get('PersonAgeGroup_Code')) == 1 && !(regNick == 'ufa' && DiagOMS.test(record.get('Diag_Code')))) {
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

		record = base_form.findField('ServiceType_id').getStore().getById(base_form.findField('ServiceType_id').getValue());
		if ( record ) {
			service_type_name = record.get('ServiceType_Name');
			service_type_sysnick = record.get('ServiceType_SysNick');

			if ( record.get('ServiceType_SysNick') == 'neotl' && base_form.findField('EvnVizitPL_setTime').getValue().toString().length == 0 ) {
				sw.swMsg.show({
					buttons: Ext.Msg.OK,
					fn: function() {
						this.formStatus = 'edit';
						base_form.findField('EvnVizitPL_setTime').focus(false);
					}.createDelegate(this),
					icon: Ext.Msg.WARNING,
					msg: lang['ne_ukazano_vremya_posescheniya'],
					title: ERR_INVFIELDS_TIT
				});
				return false;
			}
		}

		if (!options.ignoreCheckIsNeedOnkoControl
			&& options.isDoSave
			&& this.action.inlist(['add','edit'])
			&& this.FormType != 'EvnVizitPLWow'
		) {
			sw.Promed.PersonOnkoProfile.checkIsNeedOnkoControl(
				{
					Person_id: base_form.findField('Person_id').getValue(),
					Person_Birthday: this.findById('EVPLEF_PersonInformationFrame').getFieldValue('Person_Birthday'),
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

		// проверка на существование связи между специальностью врача и профилем отделения на установленную дату. Поиск происходит в глобальном сторе swLpuSectionProfileMedSpecOms
		if (regNick == 'pskov' && Ext.globalOptions.polka.evnvizitpl_profile_medspecoms_check != 0 && ! options.ignoreLpuSectionProfile_MedSpecOms)
		{
			var onDate =  Ext.util.Format.date(base_form.findField('EvnVizitPL_setDate').getValue(), 'd.m.Y'),
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

		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение посещения..."});
		loadMask.show();

		// @task https://redmine.swan.perm.ru/issues/84712
		if (regNick == 'ekb') {
			this.setDefaultMedicalCareKind();
		}

        params.VizitType_id = base_form.findField('VizitType_id').getValue();

		//params.AnamnezData = Ext.util.JSON.encode(this.EvnXmlPanel.getSavingData());
		//params.XmlTemplate_id = this.EvnXmlPanel.getXmlTemplateId();
		
		params.FormType = this.FormType;
		params.action = this.action;
		params.from = this.from;
		params.TimetableGraf_id = (base_form.findField('TimetableGraf_id').getValue() > 0 ? base_form.findField('TimetableGraf_id').getValue() : this.TimetableGraf_id);

		// Гриды специфики
		/*
		params.MorbusHepatitisDiag = this.collectGridData('MorbusHepatitisDiag');		
		params.MorbusHepatitisDiagSop = this.collectGridData('MorbusHepatitisDiagSop');		
		params.MorbusHepatitisLabConfirm = this.collectGridData('MorbusHepatitisLabConfirm');		
		params.MorbusHepatitisFuncConfirm = this.collectGridData('MorbusHepatitisFuncConfirm');		
		params.MorbusHepatitisCure = this.collectGridData('MorbusHepatitisCure');		
		params.MorbusHepatitisCureEffMonitoring = this.collectGridData('MorbusHepatitisCureEffMonitoring');		
		params.MorbusHepatitisVaccination = this.collectGridData('MorbusHepatitisVaccination');		
		params.MorbusHepatitisQueue = this.collectGridData('MorbusHepatitisQueue');	
		*/
		if ( base_form.findField('LpuSection_id').disabled ) {
			params.LpuSection_id = base_form.findField('LpuSection_id').getValue();
		}

		if ( base_form.findField('MedStaffFact_id').disabled ) {
			params.MedStaffFact_id = base_form.findField('MedStaffFact_id').getValue();
		}

		if ( base_form.findField('PayType_id').disabled ) {
			params.PayType_id = base_form.findField('PayType_id').getValue();
			if (!params.PayType_id && regNick == 'kz') {
				params.PayType_id = 152;
			}
		}

		if ( base_form.findField('PayTypeKAZ_id').disabled ) {
			params.PayTypeKAZ_id = base_form.findField('PayTypeKAZ_id').getValue();
		}

		if ( base_form.findField('ScreenType_id').disabled ) {
			params.ScreenType_id = base_form.findField('ScreenType_id').getValue();
		}

		if ( base_form.findField('EvnVizitPL_Uet').disabled ) {
			params.EvnVizitPL_Uet = base_form.findField('EvnVizitPL_Uet').getValue();
		}

		if ( base_form.findField('EvnVizitPL_UetOMS').disabled ) {
			params.EvnVizitPL_UetOMS = base_form.findField('EvnVizitPL_UetOMS').getValue();
		}

		if (regNick == 'ekb' && base_form.findField('Mes_id').disabled) {
			params.Mes_id = base_form.findField('Mes_id').getValue();
		}

		if ( base_form.findField('UslugaComplex_uid').disabled ) {
			params.UslugaComplex_uid = base_form.findField('UslugaComplex_uid').getValue();
		}

        if ( base_form.findField('MedicalCareKind_id').disabled ) {
            params.MedicalCareKind_id = base_form.findField('MedicalCareKind_id').getValue();
        }

        if ( base_form.findField('TreatmentClass_id').disabled ) {
            params.TreatmentClass_id = base_form.findField('TreatmentClass_id').getValue();
        }

		if ( this.findById('EVPLEF_EvnVizitPL_IsZNOCheckbox').getValue() == true ) {
			base_form.findField('EvnVizitPL_IsZNO').setValue(2);
		}
		else {
			base_form.findField('EvnVizitPL_IsZNO').setValue(1);
		}

		if ( base_form.findField('Diag_spid').disabled ) {
			params.Diag_spid = base_form.findField('Diag_spid').getValue();
		}

        params.isAutoCreate = (options && typeof options.openChildWindow == 'function' && this.action == 'add') ? 1 : 0;
        params.vizit_kvs_control_check = (options && !Ext.isEmpty(options.vizit_kvs_control_check) && options.vizit_kvs_control_check === 1) ? 1 : 0;
        params.vizit_intersection_control_check = (options && !Ext.isEmpty(options.vizit_intersection_control_check) && options.vizit_intersection_control_check === 1) ? 1 : 0;
        params.ignoreLpuSectionProfileVolume = (options && !Ext.isEmpty(options.ignoreLpuSectionProfileVolume) && options.ignoreLpuSectionProfileVolume === 1) ? 1 : 0;
        params.ignoreMesUslugaCheck = (options && !Ext.isEmpty(options.ignoreMesUslugaCheck) && options.ignoreMesUslugaCheck === 1) ? 1 : 0;
        params.ignoreControl59536 = (options && !Ext.isEmpty(options.ignoreControl59536) && options.ignoreControl59536 === 1) ? 1 : 0;
        params.ignoreControl122430 = (options && !Ext.isEmpty(options.ignoreControl122430) && options.ignoreControl122430 === 1) ? 1 : 0;
        params.ignoreEvnDirectionProfile = (options && !Ext.isEmpty(options.ignoreEvnDirectionProfile) && options.ignoreEvnDirectionProfile === 1) ? 1 : 0;
		params.ignoreCheckEvnUslugaChange = (options && !Ext.isEmpty(options.ignoreCheckEvnUslugaChange) && options.ignoreCheckEvnUslugaChange === 1) ? 1 : 0;
		params.ignoreDiagDispCheck = (options && !Ext.isEmpty(options.ignoreDiagDispCheck) && options.ignoreDiagDispCheck === 1) ? 1 : 0;
		params.ignoreCheckB04069333 = (options && !Ext.isEmpty(options.ignoreCheckB04069333) && options.ignoreCheckB04069333 === 1) ? 1 : 0;
        params.ignoreCheckMorbusOnko = (options && !Ext.isEmpty(options.ignoreCheckMorbusOnko) && options.ignoreCheckMorbusOnko === 1) ? 1 : 0;
		params.addB04069333 = (options && !Ext.isEmpty(options.addB04069333) && options.addB04069333 === 1) ? 1 : 0;
		params.EvnVizitPLDoublesData = (options && options.EvnVizitPLDoublesData) ? options.EvnVizitPLDoublesData : null;
        params.streamInput = (this.streamInput === true ? 1 : 0);

		if (this.DrugTherapySchemePanel.isVisible()) {
			params.DrugTherapyScheme_ids = this.DrugTherapySchemePanel.getIds();
		}

		params.RepositoryObservData = Ext.util.JSON.encode(this.RepositoryObservData);

		base_form.submit({
			failure: function(result_form, action) {
				this.formStatus = 'edit';
				loadMask.hide();

                if ( action.result ) {
                    if ( action.result.Error_Msg && !action.result.Error_Msg.inlist(['YesNo', 'EvnVizitPLDouble'])) {
						var msg = action.result.Error_Msg;

						if (action.result.Error_Code == 112 && action.result.addMsg) {
							var headMsg = lang['informatsiya_o_peresecheniyah'];
							var addMsg = escapeHtml(action.result.addMsg);
							msg += '<br/> <a onclick="Ext.Msg.alert(\' ' + headMsg +  ' \',\' ' + addMsg +  ' \');" href=\'#\' >Подробнее</a>';
						}

                        sw.swMsg.alert(lang['oshibka'], msg);
						if (options && typeof options.onFailureSaveEvnVizitPLOnkoControl == 'function') {
							options.onFailureSaveEvnVizitPLOnkoControl();
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
										case (103 == action.result.Error_Code):
											options.ignoreControl59536 = 1;
											break;
										case (105 == action.result.Error_Code):
											options.ignoreControl122430 = 1;
											break;
										case (104 == action.result.Error_Code):
											options.ignoreEvnDirectionProfile = 1;
											break;
										case (130 == action.result.Error_Code):
											options.ignoreCheckEvnUslugaChange = 1;
											break;
										case (131 == action.result.Error_Code):
											options.addB04069333 = 1;
											options.ignoreCheckB04069333 = 1;
											break;
										case (289 == action.result.Error_Code):
											options.ignoreCheckMorbusOnko = 1;
											break;
										case (182 == action.result.Error_Code):
											options.ignoreDiagDispCheck = 1;
											var formParams = new Object();
											var params_disp = new Object();

											formParams.Person_id = base_form.findField('Person_id').getValue();
											formParams.Server_id = base_form.findField('Server_id').getValue();
											formParams.PersonDisp_begDate = getGlobalOptions().date;
											formParams.PersonDisp_DiagDate = getGlobalOptions().date;
											formParams.Diag_id = base_form.findField('Diag_id').getValue();

											params_disp.action = 'add';
											params_disp.callback = Ext.emptyFn;
											params_disp.formParams = formParams;
											params_disp.onHide = Ext.emptyFn;

											getWnd('swPersonDispEditWindow').show(params_disp);
											break;
										default:
											options.ignoreDayProfileDuplicateVizit = true;
											break;
									}
                                    this.doSave(options);
                                }
                                else {
									switch (true) {
										case (182 == action.result.Error_Code):
											options.ignoreDiagDispCheck = 1;
											this.doSave(options);
											break;
										case (131 == action.result.Error_Code):
											options.addB04069333 = 0;
											options.ignoreCheckB04069333 = 1;
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
					if ( action.result.EvnVizitPL_id ) {
						base_form.findField('EvnVizitPL_id').setValue(action.result.EvnVizitPL_id);
						base_form.findField('UslugaComplex_uid').getStore().baseParams.EvnVizit_id = base_form.findField('EvnVizitPL_id').getValue();
						base_form.findField('TimetableGraf_id').setValue(action.result.TimetableGraf_id);
                        this.EvnXmlPanel.setBaseParams({
                            userMedStaffFact: this.userMedStaffFact,
                            Server_id: base_form.findField('Server_id').getValue(),
                            Evn_id: base_form.findField('EvnVizitPL_id').getValue()
                        });
                        this.EvnXmlPanel.onEvnSave();
						this.diagIsChanged = false;

						if ( action.result.EvnUslugaCommon_id ) {
							base_form.findField('EvnUslugaCommon_id').setValue(action.result.EvnUslugaCommon_id);
						}

						if ( options && typeof options.openChildWindow == 'function' /*&& this.action == 'add'*/ ) {
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

							var lpu_section_name = '';
							var lpu_unit_set_code = 0;
							var pay_type_name = '';
							var usluga_complex_code = '';
							var usluga_complex_name = '';
							var vizit_type_name = '';
							var vizit_type_sys_nick = '';

							record = base_form.findField('LpuSection_id').getStore().getById(base_form.findField('LpuSection_id').getValue());
							if ( record ) {
								lpu_section_name = record.get('LpuSection_Code') + '. ' + record.get('LpuSection_Name');
								lpu_unit_set_code = record.get('LpuUnitSet_Code');
							}

							record = base_form.findField('PayType_id').getStore().getById(base_form.findField('PayType_id').getValue());
							if ( record ) {
								pay_type_name = record.get('PayType_Name');
							}

							record = base_form.findField('VizitType_id').getStore().getById(base_form.findField('VizitType_id').getValue());
							if ( record ) {
								vizit_type_name = record.get('VizitType_Name');
								vizit_type_sys_nick = record.get('VizitType_SysNick');
							}

							/*var mh_reg = new RegExp("^(A0[0-9]|A2[0-8]|A[3-4]|A7[5-9]|A[8-9]|B0[0-9]|B1[5-9]|B2|B3[0-4]|B[5-7]|B8[0-3]|B9[0-6]|B97.[0-8]|B99)");
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
							}*/

							if ( sw.Promed.EvnVizitPL.isSupportVizitCode()) {
								var usluga_complex_id = base_form.findField('UslugaComplex_uid').getValue();
								var index = base_form.findField('UslugaComplex_uid').getStore().findBy(function(rec) {
									return (rec.get('UslugaComplex_id') == usluga_complex_id);
								});

								if ( index >= 0 ) {
									usluga_complex_code = base_form.findField('UslugaComplex_uid').getStore().getAt(index).get('UslugaComplex_Code');
									usluga_complex_name = base_form.findField('UslugaComplex_uid').getStore().getAt(index).get('UslugaComplex_Code') + '. ' + base_form.findField('UslugaComplex_uid').getStore().getAt(index).get('UslugaComplex_Name');
								}
							}
							
							data.evnVizitPLData = [{
								'accessType': 'edit',
								'EvnVizitPL_id': base_form.findField('EvnVizitPL_id').getValue(),
								'EvnPL_id': base_form.findField('EvnPL_id').getValue(),
								'Person_id': base_form.findField('Person_id').getValue(),
								'PersonEvn_id': base_form.findField('PersonEvn_id').getValue(),
								'Server_id': base_form.findField('Server_id').getValue(),
								'Diag_id': base_form.findField('Diag_id').getValue(),
								'EvnVizitPL_setDate': base_form.findField('EvnVizitPL_setDate').getValue(),
								'Diag_Code': diag_code,
								'Diag_Name': diag_name,
								'LpuSection_Name': lpu_section_name,
								'LpuUnitSet_Code': lpu_unit_set_code,
								'MedPersonal_Fio': med_personal_fio,
								'MedPersonal_id': base_form.findField('MedPersonal_id').getValue(),
								'LpuSection_id': base_form.findField('LpuSection_id').getValue(),
								'LpuSectionProfile_Code': base_form.findField('LpuSection_id').getFieldValue('LpuSectionProfile_Code'),
								'EvnVizitPL_IsSigned': 1, // раз редактировали, значит не подписан
								'ServiceType_SysNick': service_type_sysnick,
								'ServiceType_Name': service_type_name,
								'VizitType_Name': vizit_type_name,
								'VizitType_SysNick': vizit_type_sys_nick,
								'PayType_Name': pay_type_name,
								'UslugaComplex_Code': usluga_complex_code,
								'UslugaComplex_Name': usluga_complex_name
							}];

							// Для ВОВ еще одно поле 
							if ( this.FormType == 'EvnVizitPLWow' ) {
								data.evnVizitPLData[0].DispWOWSpec_id = base_form.findField('DispWowSpec_id').getValue();
								data.evnVizitPLData[0].DispWOWSpec_Name = base_form.findField('DispWowSpec_id').getRawValue();
							}

							if (options && typeof options.onSuccessSaveEvnVizitPLOnkoControl == 'function') {
								options.onSuccessSaveEvnVizitPLOnkoControl(base_form.findField('EvnVizitPL_id').getValue());
							}
							
							if ( action.result.Alert_Msg && action.result.Alert_Msg.toString().length > 0 ) {
								sw.swMsg.alert(lang['preduprejdenie'], action.result.Alert_Msg, function() {
									this.callback(data);
									this.hide();
								}.createDelegate(this) );
							}
							else {
								// #154675 ТАП. Формирование системного сообщения при сохранении ТАПа со случаем подозрения на ЗНО
								var record = base_form.findField('Diag_id').getStore().getById(base_form.findField('Diag_id').getValue());
								if (record && record.get('Diag_id')) {
									var diag_code = record.get('Diag_Code');
									var diag_name = record.get('Diag_Name');
									var diag_id = record.get('Diag_id');

									if (regNick == 'ufa' && diag_code == 'Z03.1' && this.action.inlist(['add'])) {
										//код специальности врача из справочника [dbo].[MedSpecOms], равный значениям MedSpecOms_Code = 17, 41, 73, 74, 82, 243, 265, MedSpecOms_Name = Онкология/Детская онкология.
										var MedSpecOms_Code = base_form.findField('MedStaffFact_id').getFieldValue('MedSpecOms_Code');

										var params = new Object();
										if (MedSpecOms_Code.inlist(['17', '41', '73', '74', '82', '243', '265'])) {
											params.MedSpecOms = true;
										} else {
											params.MedSpecOms = false;
										}
										params.Person_id = base_form.findField('Person_id').getValue();
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
						if (options && typeof options.onFailureSaveEvnVizitPLOnkoControl == 'function') {
							options.onFailureSaveEvnVizitPLOnkoControl();
						}
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
	},
	draggable: true,
	enableEdit: function(enable) {

		// Переменная для региональных настроек:
		var regNick = getRegionNick();

		var base_form = this.findById('EvnVizitPLEditForm').getForm();
		var form_fields = new Array(
			'EvnVizitPL_setDate',
			'EvnVizitPL_setTime',
			'LpuSection_id',
			'MedStaffFact_id',
			'MedStaffFact_sid',
			'ServiceType_id',
			'VizitClass_id',
			'VizitType_id',
			'PayType_id',
			'EvnVizitPL_Time',
			'RiskLevel_id',
			'ProfGoal_id',
			'DispClass_id',
			'DispProfGoalType_id',
			'Diag_id',
			'HSNStage_id',
			'HSNFuncClass_id',
			'Diag_agid',
			'ComplDiagHSNStage_id',
			'ComplDiagHSNFuncClass_id',
			'TreatmentClass_id',
			'LpuSectionProfile_id',
			'MedicalCareKind_id',
			'PersonDisp_id',
			'DeseaseType_id',
			//'EvnVizitPL_Uet',
			//'EvnVizitPL_UetOMS'
			'UslugaMedType_id',
			'isPaidVisit'
		);
		var i = 0;

		if ( sw.Promed.EvnVizitPL.isSupportVizitCode() ) {
			form_fields.push('UslugaComplex_uid');
		}

		if (regNick == 'ekb') {
			form_fields.push('Mes_id');
			//form_fields.push('LpuSectionProfile_id');
		}

		if ( this.FormType == 'EvnVizitPLWow' ) {
			form_fields.push('DispWowSpec_id');
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
			if (regNick == 'ufa') {
				this.findById('EVPLEF_EvnVizitPL_IsZNOCheckbox').disable();
			}
			else {
				this.findById('EVPLEF_EvnVizitPL_IsZNOCheckbox').enable();
			}

			this.buttons[0].show();
		}
		else {
			this.findById('EVPLEF_EvnVizitPL_IsZNOCheckbox').disable();
			this.buttons[0].hide();
		}
        this.EvnXmlPanel.setReadOnly(!enable);
	},
	loadSpecificsTree: function() {
		var tree = this.findById('EVPLEF_SpecificsTree');
		var root = tree.getRootNode();
		var win = this;
		
		if (win.specLoading) {
			if (win.specLoading.timeoutId) {
				clearTimeout(win.specLoading.timeoutId);
			}
			if (win.specLoading.transId) {
				Ext.Ajax.abort(win.specLoading.transId);
			}
		}
		
		win.specLoading = {timeoutId: null, transId: null};

		win.specLoading.timeoutId = setTimeout(function() {
			
			var base_form = this.findById('EvnVizitPLEditForm').getForm();
			
			var Diag_ids = [];
			if (base_form.findField('Diag_id').getValue() && base_form.findField('Diag_id').getFieldValue('Diag_Code')) {
				Diag_ids.push([base_form.findField('Diag_id').getValue(), 1, base_form.findField('Diag_id').getFieldValue('Diag_Code'), '']);
			}
			this.findById('EVPLEF_EvnDiagPLGrid').getStore().each(function(record) {
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

				win.specLoading.transId = tree.getLoader().transId;
			}
			
			if (this.findById('EVPLEF_SpecificsPanel').collapsed) {
				this.findById('EVPLEF_SpecificsPanel').expand();
				this.findById('EVPLEF_SpecificsPanel').collapse();
			}
		}.createDelegate(this), 100);
	},
	firstRun: true,
	getListDispWowSpec: function (gridpanel, value)
	{
		var list = Array();
		if (gridpanel.getCount()>0)
		{
			gridpanel.getGrid().getStore().each(function(rec) 
			{
				if ((rec.get('DispWOWSpec_id')!=value) && (rec.get('DispWOWSpec_id')!=10))
				{
					list.push(rec.get('DispWOWSpec_id'));
				}
			});
		}
		return list;
	},
	setMesInUsluga: function(){
		var win = this;
		if (getRegionNick() != 'ekb') return false;
		var base_form = win.findById('EvnVizitPLEditForm').getForm();
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
	reloadUslugaComplexField: function(needUslugaComplex_id, wantUslugaComplex_id, from_usluga_add) {//yl:from_usluga_add - признак вызова после добавления услуги

		// Переменная для региональных настроек:
		var regNick = getRegionNick();

		var win = this;
		if (win.blockUslugaComplexReload) {
			return false;
		}

		var base_form = this.findById('EvnVizitPLEditForm').getForm();
		var field = base_form.findField('UslugaComplex_uid');

		if(win.action == 'add' && win.UslugaComplex_uid && !field.getValue()) {
			field.setValue(win.UslugaComplex_uid);
		}

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

		// Пока добавил для всех регионов #179764
		field.getStore().baseParams.LpuSection_id = base_form.findField('LpuSection_id').getValue();

		if (regNick.inlist(['perm','pskov'])) {
			var currSetDate = Ext.util.Format.date(base_form.findField('EvnVizitPL_setDate').getValue(), 'd.m.Y');
			var currSetTime = base_form.findField('EvnVizitPL_setTime').getValue();
			var currSetDT = getValidDT(currSetDate, currSetTime);
			var lastSetDT = currSetDT;

			if (currSetDT && Ext.isArray(this.OtherVizitList)) {
				for(var i=0; i<this.OtherVizitList.length; i++) {
					var vizit = this.OtherVizitList[i];
					var setDT = getValidDT(vizit.EvnVizitPL_setDate, vizit.EvnVizitPL_setTime);
					if (setDT && setDT > lastSetDT) {
						lastSetDT = setDT;
					}
				}
			}
			if (Ext.isArray(this.OtherUslugaList)) {
				for(var i=0; i<this.OtherUslugaList.length; i++) {
					var usluga = this.OtherUslugaList[i];
					var setDT = getValidDT(usluga.EvnUsluga_setDate, '00:00');
					if (setDT && setDT > lastSetDT) {
						lastSetDT = setDT;
					}
				}
			}
			this.findById('EVPLEF_EvnUslugaGrid').getStore().each(function(record) {
				var setDT = record.get('EvnUsluga_setDate');
				if (!lastSetDT || lastSetDT < setDT) {
					lastSetDT = setDT;
				}
			});

			field.getStore().baseParams.UslugaComplex_Date = Ext.util.Format.date(lastSetDT, 'd.m.Y');
		}
		else if (regNick == 'vologda') {
			var currSetDate = Ext.util.Format.date(base_form.findField('EvnVizitPL_setDate').getValue(), 'd.m.Y');
			var currSetTime = base_form.findField('EvnVizitPL_setTime').getValue();
			var currSetDT = getValidDT(currSetDate, currSetTime);
			var lastSetDT = currSetDT;

			if (currSetDT && Ext.isArray(this.OtherVizitList)) {
				for(var i=0; i<this.OtherVizitList.length; i++) {
					var vizit = this.OtherVizitList[i];
					var setDT = getValidDT(vizit.EvnVizitPL_setDate, vizit.EvnVizitPL_setTime);
					if (setDT && setDT > lastSetDT) {
						lastSetDT = setDT;
					}
				}
			}

			field.getStore().baseParams.UslugaComplex_Date = Ext.util.Format.date(lastSetDT, 'd.m.Y');
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

					if ((!!!from_usluga_add) && regNick.inlist(['pskov', 'vologda'])) {//yl:15500 после добавления услуги не трогаем, а то оно может очистить
						if(base_form.findField('UslugaComplex_uid').getStore().getCount() == 1) {
							index = 0;
						}else if(base_form.findField('UslugaComplex_uid').getStore().getCount() > 1){
							index = -1;
						}
					}

					if (index >= 0) {
						var record = base_form.findField('UslugaComplex_uid').getStore().getAt(index);
						field.setValue(record.get('UslugaComplex_id'));
						field.setRawValue(record.get('UslugaComplex_Code') + '. ' + record.get('UslugaComplex_Name'));
						if (regNick == 'ekb') {
							base_form.findField('Mes_id').setUslugaComplex_id(record.get('UslugaComplex_id'));
						}

						if ( !Ext.isEmpty(base_form.findField('UslugaComplex_uid').getFieldValue('UslugaComplex_AgeGroupId')) ) {
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
					base_form.findField('Mes_id').setUslugaComplex_id(record.get('UslugaComplex_id'));
				}
			} else {
				field.clearValue();
				if (regNick == 'ekb') {
					base_form.findField('Mes_id').setUslugaComplex_id(null);
				}
			}
		}
	},
	checkAbortFields:function(flag){
		var win = this;
		var base_form = win.findById('EvnVizitPLEditForm').getForm();
		base_form.findField('MorbusPregnancy_IsMedicalAbort').setAllowBlank(flag);
		base_form.findField('MorbusPregnancy_OutcomPeriod').setAllowBlank(flag);
		base_form.findField('MorbusPregnancy_OutcomT').setAllowBlank(flag);
		base_form.findField('MorbusPregnancy_OutcomD').setAllowBlank(flag);
		base_form.findField('MorbusPregnancy_CountPreg').setAllowBlank(flag);
	},
	checkAbort:function(){
		var win = this;
		var isSpecific = false;
		var DiagSpec = new RegExp('^O0[2-7]');
		var isAbort=false;
		var base_form = win.findById('EvnVizitPLEditForm').getForm();
		var Diag = base_form.findField('Diag_id');

		win.findById('EVPLEF_EvnUslugaGrid').getStore().each(function(rec){
			if(rec.get('Usluga_Code')=='04250403')isAbort=true;
			
		});
		
		Diag.getStore().each(function(rec) {
			if(Diag.getValue()==rec.get('Diag_id')&&DiagSpec.test(rec.get('Diag_Code'))){
				isSpecific = true;
				
			}
			
		});

		if(isAbort&&isSpecific&&getRegionNick()=='perm'){
			win.findById('EVPLEF_EvnBirthForm').setVisible(true);

		}else{
			win.findById('EVPLEF_EvnBirthForm').setVisible(false);
		}

	},
	EvnUslugaGridIsModified: false,
	formStatus: 'edit',
	height: 550,
	id: 'EvnVizitPLEditWindow',
	checkUslugaComplexUidAllowBlank: function() {
		if (getRegionNick().inlist([ 'perm', 'ufa', 'buryatiya', 'kz', 'pskov', 'ekb' ])) {
			var base_form = this.findById('EvnVizitPLEditForm').getForm();
			var xdate = new Date(2014, 11, 1);
			base_form.findField('UslugaComplex_uid').setAllowBlank(true);
			if ( !Ext.isEmpty(base_form.findField('EvnVizitPL_setDate').getValue()) && base_form.findField('EvnVizitPL_setDate').getValue() >= xdate && base_form.findField('PayType_id').getFieldValue('PayType_SysNick') == 'oms' ) {
				base_form.findField('UslugaComplex_uid').setAllowBlank(false);
			}
		}
	},
	checkZNO: function(options){
		if(getRegionNick()!='ekb') return;
		var win = this,
			base_form = win.findById('EvnVizitPLEditForm').getForm(),
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
						win.findById('EVPLEF_EvnVizitPL_IsZNOCheckbox').setValue(true);
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
			base_form = win.findById('EvnVizitPLEditForm').getForm(),
			person_id = base_form.findField('Person_id');
			
		if(base_form.findField('EvnVizitPL_IsZNORemove').getValue() == '2') {
			Ext.getCmp('EVPLEF_BiopsyDatePanel').show();
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
		} else Ext.getCmp('EVPLEF_BiopsyDatePanel').hide();
	},
	
	changeZNO: function(options){
		if(getRegionNick()!='ekb') return;
		var win = this,
			base_form = win.findById('EvnVizitPLEditForm').getForm(),
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
	},
	
	initComponent: function() {
		var current_window = this;
		var win = this; //!!! нужно убрать !!!

		// Переменная для региональных настроек:
		var regNick = getRegionNick();

		this.evnDirectionAllInfoPanel = new sw.Promed.EvnDirectionAllInfoPanel({
			hidden: true,
			parentClass: 'EvnVizitPL',
			personFieldName: 'Person_id',
			evnFieldName: 'EvnVizitPL_id',
			idFieldName: 'EvnDirection_id',
			fieldIsAutoName: null,
			timeTableGrafFieldName: 'TimetableGraf_id',
			medStaffFactFieldName: 'MedStaffFact_id',
			id: 'EVPLEF_DirectInfoPanel'
		});

        this.EvnXmlPanel = new sw.Promed.EvnXmlPanel({
            autoHeight: true,
            bodyStyle: 'padding-top: 0.5em;',
            border: false,
            collapsible: true,
            id: 'EVPLEF_AnamnezPanel',
            layout: 'form',
            style: 'margin-bottom: 0.5em;',
            title: lang['2_osmotr'],
            isLoaded: false,
            ownerWin: this,
            options: {
                XmlType_id: sw.Promed.EvnXml.EVN_VIZIT_PROTOCOL_TYPE_ID, // только протоколы осмотра
                EvnClass_id: 11 // документы и шаблоны только категории посещение поликлиники
            },
            // определяем метод, который должен создать посещение перед созданием документа с помощью указанного метода
            onBeforeCreate: function (panel, method, params) {
                if (!panel || !method || typeof panel[method] != 'function') {
                    return false;
                }
                var base_form = this.findById('EvnVizitPLEditForm').getForm();
                var evn_id_field = base_form.findField('EvnVizitPL_id');
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

		this.EvnPLDispScreenOnkoGrid = new sw.Promed.ViewFrame({
			actions: [
				{name: 'action_add', handler: function() {win.addNewEvnPLDispScreenOnko()}},
				{name: 'action_edit', handler: function() {win.openEvnPLDispScreenOnko('edit')}},
				{name: 'action_view', handler: function() {win.openEvnPLDispScreenOnko('view')}},
				{name: 'action_delete', handler: function() {win.deleteEvnPLDispScreenOnko()}},
				{name: 'action_refresh', hidden: true},
				{name: 'action_print', hidden: true}
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			autoLoadData: false,
			border: false,
			dataUrl: '/?c=EvnSection&m=loadScreenList',
			height: 200,
			paging: false,
			stringfields: [
				{name: 'EvnPLDispScreenOnko_id', type: 'int', header: 'ID', key: true},
				{name: 'EvnPLDispScreenOnko_Name', type: 'string', header: 'Наименование скрининга', width: 200, id: 'autoexpand'},
				{name: 'EvnPLDispScreenOnko_setDate', type: 'date', header: 'Дата', width: 120},
			],
			toolbar: true
		});

		var form = this;
		// Формирование списка экшенов левой панели
		var configActions = 
		{
			action_Direction: 
			{
				nn: 'action_Direction',
				tooltip: lang['zapisat_patsienta_k_vrachu'],
				text: lang['napravit_patsienta'],
				iconCls : 'eph-record16',
				disabled: false, 
				handler: function() 
				{
					var base_form = form.findById('EvnVizitPLEditForm').getForm();
					var pif = form.findById('EVPLEF_PersonInformationFrame');
					
					var openMPRecordWindow = function(base_form,pif){
						var params = 
						{
							EvnDirection_pid: base_form.findField('EvnVizitPL_id').getValue(),
							PersonEvn_id: base_form.findField('PersonEvn_id').getValue(),
							Server_id: base_form.findField('Server_id').getValue(),
							UserMedStaffFact_id: base_form.findField('MedStaffFact_id').getValue(),
							Person_id: base_form.findField('Person_id').getValue(),
							Person_Birthday: pif.getFieldValue('Person_Birthday'),
							Person_Firname: pif.getFieldValue('Person_Firname'),
							Person_Secname: pif.getFieldValue('Person_Secname'),
							Person_Surname: pif.getFieldValue('Person_Surname'),
							formMode:'vizit_PL'
						}
						if ( getWnd('swMPRecordWindow').isVisible() ) {
							sw.swMsg.show({
								buttons: Ext.Msg.OK,
								fn: Ext.emptyFn,
								icon: Ext.Msg.WARNING,
								msg: lang['okno_uje_otkryito'],
								title: ERR_WND_TIT
							});
							return false;
						}
						getWnd('swMPRecordWindow').show(params);
					};
					
					//проверяем жив ли человек
					if (pif && pif.getFieldValue('Person_deadDT') != '') {
						sw.swMsg.alert(lang['oshibka'], lang['zapis_nevozmojna_v_svyazi_so_smertyu_patsienta']);
					} else {
						if ( base_form.findField('EvnVizitPL_id').getValue() < 1) {
							sw.swMsg.show(
							{
								icon: Ext.MessageBox.QUESTION,
								msg: lang['poseschenie_ne_sohraneno_poetomu_elektronnoe_napravlenie_mojet_byit_vyipisano_s_oshibkami_vyi_hotite_sohranit_poseschenie_i_zapisat_patsienta_bez_oshibok'],
								title: lang['vopros'],
								buttons: Ext.Msg.YESNO,
								fn: function(buttonId, text, obj)
								{
									//если пользователь отказал, то вернуть в форму посещения
									if ('yes' != buttonId)
									{
										return false;
									}
									// если пользователь подтвердил, сохранить и перейти к требуемой форме,
									form.doSave({
										//ignoreEvnUslugaCountCheck: true,
										openChildWindow: function() {
											openMPRecordWindow(base_form,pif);
										}.createDelegate(this)
									});
								}
							});
						} else {
							openMPRecordWindow(base_form,pif);
						}
					}
				}
			},
			action_JournalDirections: 
			{
				nn: 'action_JournalDirections',
				tooltip: lang['otkryit_jurnal_napravleniy'],
				text: lang['jurnal_napravleniy'],
				iconCls : 'mp-directions32',
				disabled: false, 
				handler: function() 
				{
					var base_form = form.findById('EvnVizitPLEditForm').getForm();
					var pif = form.findById('EVPLEF_PersonInformationFrame');
					var openJournalDirectionsWindow = function(base_form,pif){
						var params = 
						{
							Form_data: {
								EvnDirection_pid: base_form.findField('EvnVizitPL_id').getValue(),
								Person_id: base_form.findField('Person_id').getValue(),
								PersonEvn_id: base_form.findField('PersonEvn_id').getValue(),
								Server_id: base_form.findField('Server_id').getValue(),
								UserMedStaffFact_id: base_form.findField('MedStaffFact_id').getValue()
							},
							PersonInformationFrame_data: {
								person_id: base_form.findField('Person_id').getValue(),
								person_birthday: pif.getFieldValue('Person_Birthday'),
								person_firname: pif.getFieldValue('Person_Firname'),
								person_secname: pif.getFieldValue('Person_Secname'),
								person_surname: pif.getFieldValue('Person_Surname')
							}
						}
						if ( getWnd('swJournalDirectionsWindow').isVisible() ) {
							sw.swMsg.show({
								buttons: Ext.Msg.OK,
								fn: Ext.emptyFn,
								icon: Ext.Msg.WARNING,
								msg: lang['okno_uje_otkryito'],
								title: ERR_WND_TIT
							});
							return false;
						}
						getWnd('swJournalDirectionsWindow').show(params);
					};
					if ( base_form.findField('EvnVizitPL_id').getValue() < 1)
					{
						sw.swMsg.show(
						{
							icon: Ext.MessageBox.QUESTION,
							msg: lang['poseschenie_ne_sohraneno_poetomu_elektronnyie_napravleniya_mogut_byit_vyipisanyi_s_oshibkami_iz_jurnala_napravleniy_vyi_hotite_sohranit_poseschenie_i_vyipisyivat_napravleniya_patsienta_bez_oshibok'],
							title: lang['vopros'],
							buttons: Ext.Msg.YESNO,
							fn: function(buttonId, text, obj)
							{
								//если пользователь отказал, то вернуть в форму посещения
								if ('yes' != buttonId)
								{
									return false;
								}
								// если пользователь подтвердил, сохранить и перейти к требуемой форме,
								form.doSave({
									//ignoreEvnUslugaCountCheck: true,
									openChildWindow: function() {
										openJournalDirectionsWindow(base_form,pif);
									}.createDelegate(this)
								});
							}
						});
					}
					else
					{
						openJournalDirectionsWindow(base_form,pif);
					}
				}
			},
			action_Disp: 
			{
				nn: 'action_Disp',
				tooltip: lang['otkryit_istoriyu_dispanserizatsii_patsienta'],
				text: lang['dispanserizatsiya'],
				iconCls : 'mp-disp32',
				disabled: false, 
				handler: function() 
				{
					var base_form = form.findById('EvnVizitPLEditForm').getForm();
					var pif = form.findById('EVPLEF_PersonInformationFrame');
					var params = 
					{
						PersonEvn_id: base_form.findField('PersonEvn_id').getValue(),
						Server_id: base_form.findField('Server_id').getValue(),
						Person_id: base_form.findField('Person_id').getValue(),
						Person_Birthday: pif.getFieldValue('Person_Birthday'),
						Person_Firname: pif.getFieldValue('Person_Firname'),
						Person_Secname: pif.getFieldValue('Person_Secname'),
						Person_Surname: pif.getFieldValue('Person_Surname')
					}
					if ( getWnd('swPersonDispHistoryWindow').isVisible() ) {
						sw.swMsg.show({
							buttons: Ext.Msg.OK,
							fn: Ext.emptyFn,
							icon: Ext.Msg.WARNING,
							msg: lang['okno_uje_otkryito'],
							title: ERR_WND_TIT
						});
						return false;
					}
					getWnd('swPersonDispHistoryWindow').show(params);
				}
			},
			action_TemplateFavorites: 
			{
				nn: 'action_TemplateFavorites',
				tooltip: lang['nedavnie_shablonyi_osmotrov'],
				text: lang['nedavnie_shablonyi'],
				id: 'EVPLEF_TemplateFavorites_btn',
				iconCls : 'template-fav32',
				disabled: false, 
				handler: function() 
				{
					current_window.getTemplateFavorites();
				}
			}
		}
		// Копируем все действия для создания панели кнопок
		form.PanelActions = {};
		for(var key in configActions)
		{
			var iconCls = configActions[key].iconCls.replace(/16/g, '32');
			var z = Ext.applyIf({cls: 'x-btn-large', iconCls: iconCls, text: ''}, configActions[key]);
			this.PanelActions[key] = new Ext.Action(z);
		}
		var actions_list = ['action_Direction','action_JournalDirections','action_Disp','action_TemplateFavorites'];
		// Создание кнопок для панели
		form.BtnActions = new Array();
		var i = 0;
		for( key in form.PanelActions)
		{
			if (key.inlist(actions_list))
			{
				form.BtnActions.push(new Ext.Button(form.PanelActions[key]));
				i++;
			}
		}
		this.leftMenu = new Ext.Panel(
		{
			region: 'west',
			border: false,
			layout:'form',
			layoutConfig:
			{
				titleCollapse: true,
				animate: true,
				activeOnTop: false
			},
			items: form.BtnActions
		});

		
		this.LpuSectionProfileDopPanel = {
			border: false,
			hidden: false, // Открыто для Казахстана
			layout: 'form',
			items: [{
				allowBlank: getRegionNick().inlist(['ufa', 'ekb']),
				disabled: getRegionNick().inlist(['ufa', 'ekb']),
				id: 'EVPLEF_LpuSectionProfile',
				fieldLabel: lang['profil'],
				listeners: {
					'change': function (combo, newValue, oldValue) {
						var base_form,
							cmbDiag,
							cmbTrClass,
							code,
							index;

						if (regNick.inlist(['perm', 'pskov'])) {
							current_window.reloadUslugaComplexField();
						}

						if (regNick == 'pskov' && current_window.action != 'view') {
							base_form = current_window.findById('EvnVizitPLEditForm').getForm();
							cmbDiag = base_form.findField('Diag_id');
							cmbTrClass = base_form.findField('TreatmentClass_id');
							code = combo.getFieldValue('LpuSectionProfile_Code');

							if (code == '160' && (Ext.isEmpty(cmbDiag.getValue()) || cmbDiag.getFieldValue('Diag_Code').substr(0, 1) != 'Z')) {
								index = cmbTrClass.getStore().findBy(function(rec) {
									return (rec.get('TreatmentClass_Code') == '1.1');
								});

								if ( index >= 0 ) {
									cmbTrClass.setFieldValue('TreatmentClass_Code', '1.1');
									cmbTrClass.disable();
									cmbTrClass.fireEvent('change', cmbTrClass, cmbTrClass.getValue());
								}
							} else {
								cmbTrClass.enable();
							}
						}

						if (oldValue && newValue && current_window.errorControlCodaVisits()) {
							sw.swMsg.show({
								buttons: Ext.Msg.YESNO,
								fn: function (buttonId, text, obj) {
									if (buttonId == 'no') {
										this.setValue(oldValue);
									}
								}.createDelegate(combo),
								icon: Ext.MessageBox.QUESTION,
								msg: langs('Профиль отделения текущего посещения должен соответствовать профилю отделения других посещений в этом ТАП. Продолжить ?'),
								title: langs('Предупреждение')
							});
						}

						current_window.getFinanceSource();
					}
				},
				hiddenName: 'LpuSectionProfile_id',
				listWidth: 600,
				onTrigger2Click: function () {
					if (!this.disabled) {
						this.clearValue();
						this.fireEvent('change', this);
					}
				},
				tabIndex: TABINDEX_EVPLEF + 10,
				width: 450,
				xtype: 'swlpusectionprofiledopremotecombo'
			}]
		}

		this.EvnDirectionGrid = new sw.Promed.ViewFrame({
			uniqueId: true,
			actions: [
				{ name: 'action_add', text: 'Выбрать направление', handler: function() {
					// Выбрать направление
					current_window.openEvnDirectionSelectWindow();
				}},
				{ name: 'action_edit', disabled: true, hidden: true },
				{ name: 'action_view', handler: function() {
					// Просмотр
					current_window.openEvnDirectionEditWindow('view');
				}},
				{ name: 'action_delete', handler: function() {
					// Удалить
					sw.swMsg.show({
						buttons: Ext.Msg.YESNO,
						fn: function(buttonId, text, obj) {
							if ( buttonId == 'yes' ) {
								current_window.deleteEvnDirectionLink();
							}
						}.createDelegate(this),
						icon: Ext.MessageBox.QUESTION,
						msg: "Удалить связь случая с выбранным направлением?",
						title: lang['vopros']
					});
				}},
				{ name: 'action_refresh', disabled: true, hidden: true },
				{ name: 'action_print', disabled: true, hidden: true }
			],
			auditOptions: {
				maskRe: new RegExp('^([a-z]+)_(\\d+)$', 'i'),
				maskParams: ['key_field', 'key_id'],
				needIdSuffix: true
			},
			autoLoadData: false,
			filterByFieldEnabled: true,
			border: false,
			dataUrl: '/?c=EvnDirection&m=loadEvnDirectionList',
			paging: false,
			region: 'center',
			stringfields: [
				{ name: 'EvnDirection_id', type: 'string', header: 'ID', key: true },
				{ name: 'EvnDirection_Num', type: 'string', header: 'Номер', width: 100 },
				{ name: 'EvnDirection_setDate', type: 'date', header: 'Дата', width: 120 },
				{ name: 'Timetable_begTime', type: 'date', header: 'Время записи', width: 150, renderer: function(value, cellEl, rec){
					if (!rec.get('EvnDirection_id')) return '';
					if (!rec.get('Timetable_begTime')) return lang['ochered'];
					return rec.get('Timetable_begTime');
				}},
				{ name: 'DirType_Name', type: 'string', header: 'Тип направления', width: 200 },
				{ name: 'Lpu_dNick', type: 'string', id: 'autoexpand', header: 'Куда направлен', width: 200 },
				{ name: 'LpuSectionProfile_Name', type: 'string', header: 'Профиль', width: 200 },
				{ name: 'Diag_Name', type: 'string', header: 'Диагноз', width: 200 }
			],
			toolbar: true
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
			limit: regNick.inlist(['kareliya', 'ufa', 'astra', 'khak', 'perm']) ? null : 1,
			baseFilter: null,
			setBaseFilter: function (filterFn) {
				var base_form = this.findById('EvnVizitPLEditForm').getForm();
				var container = this.DrugTherapySchemePanel;
				container.baseFilter = filterFn;

				for (var num = 0; num <= container.lastNum; num++) {
					var field = base_form.findField('DrugTherapyScheme_id_' + num);
					if (field) field.setBaseFilter(container.baseFilter);
				}
			}.createDelegate(this),
			getIds: function () {
				var base_form = this.findById('EvnVizitPLEditForm').getForm();
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
				var add_button = this.findById(form.id + '_ButtonDrugTherapySchemePanel');

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
				var base_form = this.findById('EvnVizitPLEditForm').getForm();
				var container = this.DrugTherapySchemePanel;
				var panel = this.DrugTherapySchemeBodyPanel;

				if (panel.findById('DrugTherapySchemeFieldSet_' + num)) {
					var field = base_form.findField('DrugTherapyScheme_id_' + num);
					base_form.items.removeKey(field.id);

					panel.remove('DrugTherapySchemeFieldSet_' + num);
					this.doLayout();
					this.syncShadow();
					this.findById('EvnVizitPLEditForm').initFields();

					container.count--;
					container.checkLimit();
				}
			}.createDelegate(this),
			addFieldSet: function (options) {
				var base_form = this.findById('EvnVizitPLEditForm').getForm();
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
						labelWidth: 180,
						items: [{
							editable: true,
							xtype: 'swcommonsprcombo',
							ctxSerach: true,
							comboSubject: 'DrugTherapyScheme',
							codeAlthoughNotEditable: true,
							fieldLabel: 'Схема лекарственной терапии',
							hiddenName: 'DrugTherapyScheme_id_' + num,
							listWidth: 1620,
							width: 430,
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
				this.findById('EvnVizitPLEditForm').initFields();

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
			items: [form.DrugTherapySchemeBodyPanel, {
				layout: 'column',
				id: form.id + '_ButtonDrugTherapySchemePanel',
				cls: 'AccessRigthsFieldSet',
				height: 25,
				style: 'margin-left: 182px;',
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

		Ext.apply(this, {
			layout: 'border',
			buttons: [{
				handler: function() {
					this.doSave({
                        isDoSave: true,
						ignoreEvnUslugaCountCheck: false
					});
				}.createDelegate(this),
				iconCls: 'save16',
				onShiftTabAction: function () {
					var base_form = this.findById('EvnVizitPLEditForm').getForm();

					if ( !this.findById('EVPLEF_EvnReceptPanel').collapsed && this.findById('EVPLEF_EvnReceptGrid').getStore().getCount() > 0 ) {
						this.findById('EVPLEF_EvnReceptGrid').getView().focusRow(0);
						this.findById('EVPLEF_EvnReceptGrid').getSelectionModel().selectFirstRow();
					}
					else if ( !this.findById('EVPLEF_EvnUslugaPanel').hidden && !this.findById('EVPLEF_EvnUslugaPanel').collapsed && this.findById('EVPLEF_EvnUslugaGrid').getStore().getCount() > 0 ) {
						this.findById('EVPLEF_EvnUslugaGrid').getView().focusRow(0);
						this.findById('EVPLEF_EvnUslugaGrid').getSelectionModel().selectFirstRow();
					}
					else if ( !this.findById('EVPLEF_EvnDiagPLPanel').collapsed && this.findById('EVPLEF_EvnDiagPLGrid').getStore().getCount() > 0 ) {
						this.findById('EVPLEF_EvnDiagPLGrid').getView().focusRow(0);
						this.findById('EVPLEF_EvnDiagPLGrid').getSelectionModel().selectFirstRow();
					}
					else if ( this.action != 'view' ) {
						if ( !this.findById('EVPLEF_DiagPanel').collapsed ) {
							base_form.findField('DeseaseType_id').focus(true);
						}
						else {
							base_form.findField('PersonDisp_id').focus(true);
						}
					}
					else {
						this.buttons[this.buttons.length - 1].focus();
					}
				}.createDelegate(this),
				onTabAction: function () {
					this.buttons[this.buttons.length - 1].focus(true);
				}.createDelegate(this),
				tabIndex: TABINDEX_EVPLEF + 31,
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
						if ( !this.findById('EVPLEF_EvnReceptPanel').collapsed && this.findById('EVPLEF_EvnReceptGrid').getStore().getCount() > 0 ) {
							this.findById('EVPLEF_EvnReceptGrid').getView().focusRow(0);
							this.findById('EVPLEF_EvnReceptGrid').getSelectionModel().selectFirstRow();
						}
						else if ( !this.findById('EVPLEF_EvnUslugaPanel').hidden && !this.findById('EVPLEF_EvnUslugaPanel').collapsed && this.findById('EVPLEF_EvnUslugaGrid').getStore().getCount() > 0 ) {
							this.findById('EVPLEF_EvnUslugaGrid').getView().focusRow(0);
							this.findById('EVPLEF_EvnUslugaGrid').getSelectionModel().selectFirstRow();
						}
						else if ( !this.findById('EVPLEF_EvnDiagPLPanel').collapsed && this.findById('EVPLEF_EvnDiagPLGrid').getStore().getCount() > 0 ) {
							this.findById('EVPLEF_EvnDiagPLGrid').getView().focusRow(0);
							this.findById('EVPLEF_EvnDiagPLGrid').getSelectionModel().selectFirstRow();
						}
					}
				}.createDelegate(this),
				onTabAction: function () {
					if ( this.action != 'view' ) {
						this.findById('EvnVizitPLEditForm').getForm().findField('EvnVizitPL_setDate').focus(true);
					}
					else {
						if ( !this.findById('EVPLEF_EvnDiagPLPanel').collapsed && this.findById('EVPLEF_EvnDiagPLGrid').getStore().getCount() > 0 ) {
							this.findById('EVPLEF_EvnDiagPLGrid').getView().focusRow(0);
							this.findById('EVPLEF_EvnDiagPLGrid').getSelectionModel().selectFirstRow();
						}
						else if ( !this.findById('EVPLEF_EvnUslugaPanel').hidden && !this.findById('EVPLEF_EvnUslugaPanel').collapsed && this.findById('EVPLEF_EvnUslugaGrid').getStore().getCount() > 0 ) {
							this.findById('EVPLEF_EvnUslugaGrid').getView().focusRow(0);
							this.findById('EVPLEF_EvnUslugaGrid').getSelectionModel().selectFirstRow();
						}
						else if ( !this.findById('EVPLEF_EvnReceptPanel').collapsed && this.findById('EVPLEF_EvnReceptGrid').getStore().getCount() > 0 ) {
							this.findById('EVPLEF_EvnReceptGrid').getView().focusRow(0);
							this.findById('EVPLEF_EvnReceptGrid').getSelectionModel().selectFirstRow();
						}
					}
				}.createDelegate(this),
				tabIndex: TABINDEX_EVPLEF + 32,
				text: BTN_FRMCANCEL
			}],
			items: [ new sw.Promed.PersonInformationPanelShort({
				id: 'EVPLEF_PersonInformationFrame',
				region: 'north'
			}),
			new Ext.form.FormPanel({
				autoScroll: true,
				bodyBorder: false,
				bodyStyle: 'padding: 5px 5px 0',
				border: false,
				frame: false,
				id: 'EvnVizitPLEditForm',
				labelAlign: 'right',
				labelWidth: 170,
				items: [{
					name: 'accessType',
					value: '',
					xtype: 'hidden'
				}, {
					name: 'EvnVizitPL_IsPaid',
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
					name:'MorbusPregnancy_id',
					xtype:'hidden',
					value:0
				}, {
					name: 'EvnPL_id',
					value: 0,
					xtype: 'hidden'
				}, {
					name: 'EvnPL_lid',
					xtype: 'hidden'
				}, {
					name: 'EvnUslugaCommon_id',
					value: 0,
					xtype: 'hidden'
				}, {
					name: 'EvnDirection_id',
					value: null,
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
					name: 'LpuUnitSet_id',
					value: null,
					xtype: 'hidden'
				}, {
                    name: 'TimetableGraf_id',
                    value: null,
                    xtype: 'hidden'
                }, {
                    name: 'EvnPrescr_id',
                    value: null,
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
				}, {
                    name: 'ResultClass_id',
                    value: -1,
                    xtype: 'hidden'
                }, {
                    name: 'EvnPL_IsFinish',
                    value: 2,
                    xtype: 'hidden'
                },{
                    name: 'EvnVizitPL_IsZNO',
                    xtype: 'hidden'
                }, {
					name: 'EvnVizitPL_IsZNORemove',
					xtype: 'hidden'
				}, {
					border: false,
					layout: 'column',
					items: [{
						border: false,
						layout: 'form',
						items: [{
							allowBlank: false,
							fieldLabel: lang['data'],
							format: 'd.m.Y',
							id: 'EVPLEF_EvnVizitPL_setDate',
							listeners: {
								'change': function(field, newValue, oldValue) {
									if ( blockedDateAfterPersonDeath('personpanelid', 'EVPLEF_PersonInformationFrame', field, newValue, oldValue) ) {
										return;
									}

									var base_form = this.findById('EvnVizitPLEditForm').getForm();

									this.setRiskLevelComboState();
									this.setWellnessCenterAgeGroupsComboState();
									this.refreshFieldsVisibility(['TumorStage_id', 'PainIntensity_id', 'PregnancyEvnVizitPL_Period']);

									var lastEvnVizitPLDate = (typeof this.lastEvnVizitPLData == 'object')?this.lastEvnVizitPLData:getValidDT(this.lastEvnVizitPLData, '');
									var xdate = new Date(2016, 0, 1); // Поле видимо (если дата посещения 01-01-2016 или позже)
									var mdate = new Date(Math.max(lastEvnVizitPLDate, base_form.findField('EvnVizitPL_setDate').getValue()));
									if (mdate >= xdate) {
										base_form.findField('TreatmentClass_id').showContainer();
										base_form.findField('TreatmentClass_id').setAllowBlank(false);
										base_form.findField('TreatmentClass_id').onLoadStore();
										if (regNick != 'kz') { // для Казахстана поле не нужно
											base_form.findField('MedicalCareKind_id').showContainer();
											base_form.findField('MedicalCareKind_id').setAllowBlank(false);
										} else {
											base_form.findField('MedicalCareKind_id').hideContainer();
											base_form.findField('MedicalCareKind_id').clearValue();
											base_form.findField('MedicalCareKind_id').setAllowBlank(true);
										}
										this.setDefaultMedicalCareKind();
									} else {
										base_form.findField('TreatmentClass_id').hideContainer();
										base_form.findField('TreatmentClass_id').clearValue();
										base_form.findField('TreatmentClass_id').setAllowBlank(true);
										base_form.findField('MedicalCareKind_id').hideContainer();
										base_form.findField('MedicalCareKind_id').clearValue();
										base_form.findField('MedicalCareKind_id').setAllowBlank(true);
									}
									
									if (regNick == 'kareliya') {
										base_form.findField('TreatmentClass_id').hideContainer();									
									}

									var index;
									var lpu_section_id = base_form.findField('LpuSection_id').getValue();
									var med_staff_fact_id = base_form.findField('MedStaffFact_id').getValue();
									var med_staff_fact_sid = base_form.findField('MedStaffFact_sid').getValue();
									var ServiceType_id = base_form.findField('ServiceType_id').getValue();

									// Фильтр на поле ServiceType_id
									// https://redmine.swan.perm.ru/issues/17571
									base_form.findField('ServiceType_id').clearValue();
									base_form.findField('ServiceType_id').getStore().clearFilter();
									base_form.findField('ServiceType_id').lastQuery = '';

									current_window.checkUslugaComplexUidAllowBlank();

									if ( !Ext.isEmpty(newValue) ) {
										base_form.findField('ServiceType_id').getStore().filterBy(function(rec) {
											return (
												(Ext.isEmpty(rec.get('ServiceType_begDate')) || rec.get('ServiceType_begDate') <= newValue)
												&& (Ext.isEmpty(rec.get('ServiceType_endDate')) || rec.get('ServiceType_endDate') >= newValue)
											);
										});
										current_window.setMKB();
									}

									index = base_form.findField('ServiceType_id').getStore().findBy(function(rec) {
										return (rec.get('ServiceType_id') == ServiceType_id);
									});

									if ( index >= 0 ) {
										base_form.findField('ServiceType_id').setValue(ServiceType_id);
									}

									base_form.findField('ServiceType_id').fireEvent('change', base_form.findField('ServiceType_id'), base_form.findField('ServiceType_id').getValue());

									base_form.findField('Diag_id').setFilterByDate(newValue);
									base_form.findField('Diag_agid').setFilterByDate(newValue);
									///base_form.findField('Diag_id').filterDate = Ext.util.Format.date(newValue, 'd.m.Y');

									this.filterVizitTypeCombo();

									if (regNick == 'kareliya') {
										var VizitTypeCombo = base_form.findField('VizitType_id');
										var index = -1;
										if(
											!Ext.isEmpty(current_window.previousVizitDate)
											&& current_window.previousVizitDate.getFullYear() == '2018'
											&& base_form.findField('EvnVizitPL_setDate').getValue().getFullYear() == '2019'
											&& current_window.previousVizitType_Code == 11
										) {
											
											index = VizitTypeCombo.getStore().findBy(function(rec) {
												return rec.get('VizitType_Code') == '3.0';
											});
											
										} else if(!Ext.isEmpty(current_window.previousVizitType_Code)) {
											index = VizitTypeCombo.getStore().findBy(function(rec) {
												return rec.get('VizitType_Code') == current_window.previousVizitType_Code;
											});
										}
										if(index != -1) {
											VizitTypeCombo.setValue(VizitTypeCombo.getStore().getAt(index).get('VizitType_id'));
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
										isPolka: true,
										onDate: Ext.util.Format.date(newValue, 'd.m.Y')
									};

									// https://redmine.swan.perm.ru/issues/19471
									if (regNick.inlist(['ufa', 'ekb'])) {
										lpu_section_filter_params.LpuUnitSet_id = base_form.findField('LpuUnitSet_id').getValue(); // отделения одного подразделения ТФОМС
									}

									var isUfa = (regNick == 'ufa');
									var isAstra = (regNick == 'astra');
									var person_age = swGetPersonAge(this.findById('EVPLEF_PersonInformationFrame').getFieldValue('Person_Birthday'),  base_form.findField('EvnVizitPL_setDate').getValue());
									var WithoutChildLpuSectionAge=false;
									if (person_age >= 18 && !this.hasPreviusChildVizit() && !isUfa && !isAstra) {
										WithoutChildLpuSectionAge = true;
									}
									lpu_section_filter_params.WithoutChildLpuSectionAge = WithoutChildLpuSectionAge;;
									var medstafffact_filter_params = {
										allowLowLevel: 'yes',
										isPolka: true,
										onDate: Ext.util.Format.date(newValue, 'd.m.Y'),
										WithoutChildLpuSectionAge: WithoutChildLpuSectionAge
									};
									var mid_medstafffact_filter_params = {
										allowLowLevel: 'yes',
										isMidMedPersonalOnly: true,
										isPolka: true,
										onDate: Ext.util.Format.date(newValue, 'd.m.Y'),
										WithoutChildLpuSectionAge: WithoutChildLpuSectionAge
									};

									if ( !Ext.isEmpty(newValue) ) {
										lpu_section_filter_params.onDate = Ext.util.Format.date(newValue, 'd.m.Y');
										medstafffact_filter_params.onDate = Ext.util.Format.date(newValue, 'd.m.Y');
										mid_medstafffact_filter_params.onDate = Ext.util.Format.date(newValue, 'd.m.Y');
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
											if (this.userMedStaffFact.MedStaffFactCache_IsDisableInDoc == 2) {
												sw.swMsg.alert(lang['soobschenie'], lang['tekuschee_rabochee_mesto_zaprescheno_dlya_vyibora_v_dokumentah'], function() {this.hide();}.createDelegate(this) );
												return false;
											}
											lpu_section_filter_params.id = this.userMedStaffFact.LpuSection_id;
											medstafffact_filter_params.id = this.userMedStaffFact.MedStaffFact_id;
										}
									}

									medstafffact_filter_params.allowDuplacateMSF = true;
									medstafffact_filter_params.EvnClass_SysNick = 'EvnVizit';

									// https://redmine.swan.perm.ru/issues/19471
									if (regNick.inlist(['ufa', 'ekb'])) {
										medstafffact_filter_params.LpuUnitSet_id = base_form.findField('LpuUnitSet_id').getValue(); // из одного подразделения ТФОМС
									}

									if (regNick.inlist(['ekb','vologda','pskov','msk', 'khak']) && base_form.findField('EvnPL_lid').getValue() > 0) {
										// надо разрешить выбор не только врачей из поликлиники, т.к. врач из КВС должен выбраться.
										lpu_section_filter_params.isPolka = false;
										medstafffact_filter_params.isPolka = false;
										base_form.findField('LpuSection_id').disable(); // смена отделения не доступна
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
									this.evnDirectionAllInfoPanel.onLoadForm(this);
								}.createDelegate(this),
								'keydown': function(inp, e) {
									if ( e.getKey() == Ext.EventObject.TAB && e.shiftKey == true ) {
										e.stopEvent();
										this.buttons[this.buttons.length - 1].focus();
									}
								}.createDelegate(this)
							},
							name: 'EvnVizitPL_setDate',
							plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
							selectOnFocus: true,
							tabIndex: TABINDEX_EVPLEF + 1,
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
								var base_form = this.findById('EvnVizitPLEditForm').getForm();
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
							}.createDelegate(this),	
							plugins: [ new Ext.ux.InputTextMask('99:99', true) ],
							tabIndex: TABINDEX_EVPLEF + 2,
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
							fieldLabel: lang['vid_posescheniya'],
							listeners: {
								'change': function(combo, newValue, oldValue) {
									current_window.reloadUslugaComplexField();
								}
							},
							tabIndex: TABINDEX_EVPLEF + 3,
							width: 100,
							xtype: 'swcommonsprcombo'
						}]
					}]
				}, { // Night, поле специальности врача
					border: false,
					xtype: 'panel',
					layout: 'form',
					hidden: true,
					id: 'EVPLEF_DispWowSpecComboSet',
					items: [{
						allowBlank: true,
						hiddenName: 'DispWowSpec_id',
						id: 'EVPLEF_DispWowSpecCombo',
						listWidth: 650,
						tabIndex: TABINDEX_EVPLEF + 4,
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
										case '1':far.push('1000', '1003', '1007', '1009', '1010');break; 
										case '2':far.push('2300');break; 
										case '3':far.push('2800', '2801', '2805');break; 
										case '4':far.push('2600', '2601', '2610', '2620');break; 
										case '5':far.push('2700');break; 
										case '6':far.push('0510', '0511', '0520');break; 
										case '7':far.push('2509', '2510', '2517', '2518');break; 
										case '8':far.push('1500');break; 
										case '9':far.push('1450');break; 
										case '10':
												far.push('1000','1007','1008','7108','7100','1001','1015','1016','1014','1002','9100','1006',
													'2300','2330','7233','2350','7230','2301','8231','7231','7232','2320','8232','9230',
													'2810','2801','2800','2805','8280','7280','9280',
													'2601','8260','7260',
													'7271','2712','2700','2713','2710','8271','7270','2711','9270',
													'0510','0530','8051','7051','9051','0520','0511',
													'9521','2519','2518','7259','7257','2517',
													'1500','8150','7150','9150',
													'7145','1450');
												break;
										default:far.push('1000', '1003', '1007', '1009', '1010', '2300','2800', '2801', '2805','2600', '2601', '2610', '2620','2700','0510', '0511', '0520','2509', '2510', '2517', '2518', '1500', '1450');break; 
									}

									var grid_Vizit = getWnd('EvnPLWOWEditWindow').findById('EvnPLWOWVizitGrid');
									var mass_Vizit = newValue;
									grid_Vizit.getGrid().getStore().each(function(record)
									{
										if ((typeof record == 'object') && (record.get('DispWOWSpec_id').inlist(mass_Vizit)))
										{
											alert(lang['osmotr_etogo_vracha-spetsialista_uje_zaveden']);
										}
									});
									Ext.getCmp('EvnVizitPLEditWindow').setFilterProfile(field, far, newValue);
								}
						}
					}]
				}, {
					allowBlank: false,
					hiddenName: 'LpuSection_id',
					id: 'EVPLEF_LpuSectionCombo',
					lastQuery: '',
					listWidth: 650,
					linkedElements: [
						'EVPLEF_MedPersonalCombo'/*,
						'EVPLEF_MidMedPersonalCombo'*/
					],
					tabIndex: TABINDEX_EVPLEF + 5,
					width: 450,
					xtype: 'swlpusectionglobalcombo'
				},  {
					allowBlank: false,
					dateFieldId: 'EVPLEF_EvnVizitPL_setDate',
					enableOutOfDateValidation: true,
					hiddenName: 'MedStaffFact_id',
					id: 'EVPLEF_MedPersonalCombo',
					lastQuery: '',
					listWidth: 670,
					parentElementId: 'EVPLEF_LpuSectionCombo',
					tabIndex: TABINDEX_EVPLEF + 6,
					width: 450,
					xtype: 'swmedstafffactglobalcombo'
				}, {
					fieldLabel: lang['sred_m_personal'],
					hiddenName: 'MedStaffFact_sid',
					id: 'EVPLEF_MidMedPersonalCombo',
					listWidth: 670,
					// parentElementId: 'EVPLEF_LpuSectionCombo',
					tabIndex: TABINDEX_EVPLEF + 7,
					width: 450,
					xtype: 'swmedstafffactglobalcombo'
				}, {
					allowBlank: false,
					hidden: regNick == 'kareliya',
					fieldLabel: regNick == 'kz' ? 'Повод обращения' : 'Вид обращения',
					hiddenName: 'TreatmentClass_id',
					comboSubject: 'TreatmentClass',
					xtype: 'swcommonsprcombo',
					tabIndex: TABINDEX_EVPLEF + 8,
					width: 300,
					onLoadStore: function() {
						this.getStore().clearFilter();
						this.lastQuery = '';
						var base_form = form.findById('EvnVizitPLEditForm').getForm(),
							cmbDiag = base_form.findField('Diag_id');

						if (!Ext.isEmpty(cmbDiag.getFieldValue('Diag_Code'))) {
							if (regNick == 'kz') {
								return false;
							}
							if (regNick == 'kareliya') {
								current_window.setTreatmentClass();
								return false;
							}
							this.getStore().filterBy(function(rec) {
								if (cmbDiag.getFieldValue('Diag_Code') == 'Z51.5') {
									return (rec.get('TreatmentClass_id').inlist([ 9 ]));
								} else if (cmbDiag.getFieldValue('Diag_Code').substr(0,1) == 'Z' || (regNick == 'perm' && cmbDiag.getFieldValue('Diag_Code').substr(0,3) == 'W57')) {
									return (rec.get('TreatmentClass_id').inlist([ 6, 7, 8, 9, 10, 11, 12 ]));
								} else if (regNick == 'penza') {
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

							if (regNick == 'pskov' && !Ext.isEmpty(base_form.findField('LpuSectionProfile_id').getValue())) {
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
							var base_form = this.findById('EvnVizitPLEditForm').getForm();
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
					tabIndex: TABINDEX_EVPLEF + 9,
					prefix: 'r101_',
					width: 300,
					listWidth: 600,
					xtype: 'swcommonsprcombo',
					listeners: {
						'change': function(field, newValue, oldValue) {
							current_window.getFinanceSource();
						}
					}
				}, {
					allowBlank: false,
					hiddenName: 'ServiceType_id',
					listeners: {
						'change': function(combo, newValue, oldValue) {
							var base_form = this.findById('EvnVizitPLEditForm').getForm();
							var record = combo.getStore().getById(newValue);

							if ( !record ) {
								return false;
							}

							if (regNick == 'kareliya') {
								current_window.setTreatmentClass();
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
					tabIndex: TABINDEX_EVPLEF + 9,
					width: 300,
					xtype: 'swservicetypecombo'
				},this.LpuSectionProfileDopPanel, {
					allowBlank: false,
					listeners: {
						'change': function(combo, newValue, oldValue) {
							if (regNick == 'kareliya') {
								current_window.setTreatmentClass();
							}

							current_window.reloadUslugaComplexField();

							var base_form = this.findById('EvnVizitPLEditForm').getForm();
							var
								prof_goal_combo = base_form.findField('ProfGoal_id'),
								VizitType_SysNick = combo.getFieldValue('VizitType_SysNick');

							this.setRiskLevelComboState();
							this.setWellnessCenterAgeGroupsComboState();

							if (VizitType_SysNick == 'prof' || (regNick == 'kareliya' && VizitType_SysNick == 'medosm')) {
								prof_goal_combo.enable();
							}
							else {
								prof_goal_combo.disable();
								prof_goal_combo.clearValue();
							}
						}.createDelegate(this)
					},
					tabIndex: TABINDEX_EVPLEF + 11,
					width: 300,
					EvnClass_id: 11,
					xtype: 'swvizittypecombo'
				}, {
					fieldLabel: lang['faktor_riska'],
					hiddenName: 'RiskLevel_id',
					tabIndex: TABINDEX_EVPLEF + 12,
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
					tabIndex: TABINDEX_EVPLEF + 13,
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
								allowBlank: false,
								tabIndex: TABINDEX_EVPLEF + 14,
								width: 300,
								listeners:
									{
										'change': function(combo, newValue, oldValue) {
											current_window.checkUslugaComplexUidAllowBlank();

											var base_form = this.findById('EvnVizitPLEditForm').getForm();
											var uslugacomplex_combo = base_form.findField('UslugaComplex_uid');
											var pay_type = combo.getStore().getById(newValue);
											var pay_type_nick = (pay_type && pay_type.get('PayType_SysNick')) || '';
											if(getRegionNick().inlist([ 'ufa', 'ekb' ]))
											{
												base_form.findField('UslugaComplex_uid').setPayType(newValue);

												if (getRegionNick() == 'ekb') {
													this.setDefaultMedicalCareKind();
												}

												// для Екб код посещения необязателен
												if (getRegionNick() != 'ekb') {
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
											current_window.filterVizitTypeCombo();

											if ( getRegionNick().inlist([ 'ekb' ]) && this.FormType != 'EvnVizitPLWow' ) {
												var uslugacomplex_uid = uslugacomplex_combo.getValue();
												uslugacomplex_combo.lastQuery = 'This query sample that is not will never appear';
												uslugacomplex_combo.getStore().removeAll();
												uslugacomplex_combo.getStore().baseParams.query = '';
												uslugacomplex_combo.getStore().baseParams.UslugaComplexPartition_CodeList = ('bud' == pay_type_nick || 'fbud' == pay_type_nick) ? Ext.util.JSON.encode([350,351]) : Ext.util.JSON.encode([300,301]);
												this.reloadUslugaComplexField(uslugacomplex_uid);
												base_form.findField('Mes_id').getStore().removeAll();
												base_form.findField('Mes_id').setMesType_id(('bud' == pay_type_nick || 'fbud' == pay_type_nick) ? 8 : 'oms' == pay_type_nick ? 0 : null);
												base_form.findField('Mes_id').setUslugaComplexPartitionCodeList(('bud' == pay_type_nick || 'fbud' == pay_type_nick) ? [350,351] : null);
												current_window.loadMesCombo();
											}
											else if ( getRegionNick().inlist([ 'ufa' ]) ) {
												var uslugacomplex_uid = uslugacomplex_combo.getValue();
												uslugacomplex_combo.lastQuery = 'This query sample that is not will never appear';
												uslugacomplex_combo.getStore().removeAll();
												uslugacomplex_combo.getStore().baseParams.query = '';
												uslugacomplex_combo.clearValue();
												this.reloadUslugaComplexField(uslugacomplex_uid);
											}
											else if ( getRegionNick().inlist([ 'perm' ]) ) {
												this.reloadUslugaComplexField(uslugacomplex_uid);
											}
											if (getRegionNick()=='kz') {
												base_form.findField('isPaidVisit').setValue(pay_type && pay_type.get('PayType_id')=='153');
											}

											current_window.filterLpuSectionProfile();
										}.createDelegate(this)
									},

								useCommonFilter: true,
								fieldLabel: getRegionNick() == 'kz' ? 'Источник финансирования' : 'Вид оплаты',
								xtype: 'swpaytypecombo'
							}]
						},
						{
							layout: 'form',
							border: false,
							hidden: getRegionNick() != 'kz',
							style: 'margin: 3px 0 0 10px;',
							items: [{
								xtype: 'checkbox',
								hideLabel: true,
								boxLabel: 'Платное посещение',
								name: 'isPaidVisit',
								handler: function() {
									var base_form = this.findById('EvnVizitPLEditForm').getForm();

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
					tabIndex: TABINDEX_EVPLEF + 15,
					loadParams: {
						params: (regNick == 'ekb' ? {} : {where: "where MedicalCareKind_Code in ('11','12','13','4')"})
					},
					fieldLabel: 'Вид мед. помощи',
					hiddenName: 'MedicalCareKind_id',
					lastQuery: '',
					width: 300,
					xtype: 'swmedicalcarekindfedcombo'
				}, {
					border: false,
					hidden: (regNick != 'ekb'), // Открыто для Екатеринбурга
					layout: 'form',
					items: [{
						allowBlank: true,
						fieldLabel: lang['mes'],
						comboSubject: 'MesOldVizit',
						hiddenName: 'Mes_id',
						listeners: {
							'change': function(combo, newValue) {
								if (regNick == 'ekb') {
									var base_form = current_window.findById('EvnVizitPLEditForm').getForm();
									current_window.reloadUslugaComplexField();
									if (!Ext.isEmpty(newValue)) {
										
									} else {
										if (current_window.action != 'view') {
											base_form.findField('UslugaComplex_uid').enable();
										}
									}
								}
							}
						},
						listWidth: 600,
						tabIndex: TABINDEX_EVPLEF + 16,
						width: 450,
						xtype: 'swmesoldvizitcombo'
					}]
				}, {
					border: false,
					hidden: (false == sw.Promed.EvnVizitPL.isSupportVizitCode()),
					layout: 'form',
					items: [{
						allowBlank: sw.Promed.EvnVizitPL.isAllowBlankVizitCode(),
						fieldLabel: lang['kod'] + ' ' + (regNick == 'kz' ? lang['uslugi'].toLowerCase() + ' ' : '') + lang['posescheniya'],
						hiddenName: 'UslugaComplex_uid',
						to: 'EvnVizitPL',
						id: 'EVPLEF_UslugaComplex',
                        listeners: {
                            'change': function(combo, newValue, oldValue) {
								this.setLpuSectionProfile();
                                var base_form = this.findById('EvnVizitPLEditForm').getForm(),
                                    ResultClass_id = base_form.findField('ResultClass_id').getValue(),
                                    EvnPL_IsFinish = base_form.findField('EvnPL_IsFinish').getValue();
								if (regNick == 'ekb') {
									this.loadMesCombo();
								}
								var usluga_complex_code;
                                var usluga_complex_id = base_form.findField('UslugaComplex_uid').getValue();
                                var PLsetDate = base_form.findField('EvnVizitPL_setDate').getValue();
                                var index = base_form.findField('UslugaComplex_uid').getStore().findBy(function(rec) {
                                    return (rec.get('UslugaComplex_id') == usluga_complex_id);
                                });

                                if ( index >= 0 ) {
                                    usluga_complex_code = base_form.findField('UslugaComplex_uid').getStore().getAt(index).get('UslugaComplex_Code');
                                }

                                this.UslugaComplex_uid_AgeGroupId = base_form.findField('UslugaComplex_uid').getFieldValue('UslugaComplex_AgeGroupId');

								// https://redmine.swan.perm.ru/issues/31548
								// https://redmine.swan.perm.ru/issues/32218
                                if (
									!Ext.isEmpty(usluga_complex_code)
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
                                }
								else {
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
									!Ext.isEmpty(usluga_complex_code) && (usluga_complex_code.substr(-3, 3).inlist(['805', '893'])) && regNick.inlist(['ufa'])
								) {
									base_form.findField('DispProfGoalType_id').setAllowBlank(false);
								}
								else {
									base_form.findField('DispProfGoalType_id').setAllowBlank(true);
								}

                                if (regNick == 'ufa' && EvnPL_IsFinish == 2
                                    && !Ext.isEmpty(combo.getFieldValue('UslugaComplex_Code'))
                                    && ((combo.getFieldValue('UslugaComplex_Code')).substr(-3)).inlist(['865', '866', '836'])
                                    && (!ResultClass_id.inlist(['1', '2', '3', '4', '5', '6', '7', '9', '11', '16']) || Ext.isEmpty(ResultClass_id))
                                ) {
                                    sw.swMsg.alert(lang['soobschenie'], lang['usluga_ne_sootvetstvuet_rezultatu_posescheniya_esli_tri_poslednie_simvola_koda_posescheniya_ravnyi_865_866_ili_836_to_kod_rezultata_lecheniya_doljen_byit_raven_1_2_3_4_5_6_7_9_11_16_ili_otsutstvovat']);
                                    combo.setValue('');
                                    return false;
                                }

								if (regNick == 'kz') {
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
						listWidth: 600,
						tabIndex: TABINDEX_EVPLEF + 17,
						width: 450,
						xtype: 'swuslugacomplexnewcombo'
					}]
				}, {
					comboSubject: 'UslugaMedType',
					enableKeyEvents: true,
					hidden: regNick !== 'kz',
					fieldLabel: langs('Вид услуги'),
					hiddenName: 'UslugaMedType_id',
					allowBlank: regNick !== 'kz',
					lastQuery: '',
					tabIndex: TABINDEX_EVPLEF + 18,
					typeCode: 'int',
					width: 450,
					xtype: 'swcommonsprcombo'
				}, /*this.EbkLpuSectionProfilePanel,*/ {
					allowDecimals: false,
					allowNegative: false,
					enableKeyEvents: true,
					fieldLabel: lang['vremya_priema_min'],
					name: 'EvnVizitPL_Time',
					tabIndex: TABINDEX_EVPLEF + 19,
					width: 70,
					xtype: 'numberfield'
				}, {
					enableKeyEvents: true,
					hiddenName: 'ProfGoal_id',
					tabIndex: TABINDEX_EVPLEF + 20,
					width: 450,
					xtype: 'swprofgoalcombo'
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
							var base_form = this.findById('EvnVizitPLEditForm').getForm();

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
					tabIndex: TABINDEX_EVPLEF + 21,
					typeCode: 'int',
					width: 450,
					xtype: 'swcommonsprcombo'
				},{
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
					tabIndex: TABINDEX_EVPLEF + 22,
					typeCode: 'int',
					width: 450,
					xtype: 'swcommonsprcombo'
				}, {
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
					tabIndex: TABINDEX_EVPLEF + 23,
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
					listeners: {
						'keydown': function(inp, e) {
							switch ( e.getKey() ) {
								case Ext.EventObject.TAB:
									var base_form = this.findById('EvnVizitPLEditForm').getForm();

									if ( e.shiftKey == false && this.findById('EVPLEF_DiagPanel').collapsed ) {
										e.stopEvent();

										if ( !this.findById('EVPLEF_EvnDiagPLPanel').collapsed && this.findById('EVPLEF_EvnDiagPLGrid').getStore().getCount() > 0 ) {
											this.findById('EVPLEF_EvnDiagPLGrid').getView().focusRow(0);
											this.findById('EVPLEF_EvnDiagPLGrid').getSelectionModel().selectFirstRow();
										}
										else if ( !this.findById('EVPLEF_EvnUslugaPanel').hidden && !this.findById('EVPLEF_EvnUslugaPanel').collapsed && this.findById('EVPLEF_EvnUslugaGrid').getStore().getCount() > 0 ) {
											this.findById('EVPLEF_EvnUslugaGrid').getView().focusRow(0);
											this.findById('EVPLEF_EvnUslugaGrid').getSelectionModel().selectFirstRow();
										}
										else if ( !this.findById('EVPLEF_EvnReceptPanel').collapsed && this.findById('EVPLEF_EvnReceptGrid').getStore().getCount() > 0 ) {
											this.findById('EVPLEF_EvnReceptGrid').getView().focusRow(0);
											this.findById('EVPLEF_EvnReceptGrid').getSelectionModel().selectFirstRow();
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
					tabIndex: TABINDEX_EVPLEF + 24,
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
					fieldLabel: lang['uet_fakt'],
					name: 'EvnVizitPL_Uet',
					tabIndex: TABINDEX_EVPLEF + 25,
					width: 100,
					xtype: 'numberfield'
				}, {
					allowDecimals: true,
					allowNegative: false,
					disabled: true,
					enableKeyEvents: true,
					fieldLabel: lang['uet_fakt_po_oms'],
					name: 'EvnVizitPL_UetOMS',
					tabIndex: TABINDEX_EVPLEF + 26,
					width: 100,
					xtype: 'numberfield'
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
                    hidden: !(regNick.inlist(['ufa', 'ekb'])),
                    layout: 'form',
                    items: [{
                        allowDecimals: true,
                        allowNegative: false,
                        enableKeyEvents: true,
                        fieldLabel: lang['gruppa_zdorovya'],
                        hiddenName: 'HealthKind_id',
                        tabIndex: TABINDEX_EVPLEF + 26,
						width: 100,
                        xtype: 'swhealthkindcombo'
                    }]
                },
				/* Панель входящего направления (по направлению) */
				this.evnDirectionAllInfoPanel,
                /* Панель протокола осмотра */
                this.EvnXmlPanel,

				// 3. Основной диагноз.
				new sw.Promed.Panel({
					autoHeight: true,
					bodyStyle: 'padding-top: 0.5em;',
					border: true,
					collapsible: true,
					id: 'EVPLEF_DiagPanel',
					layout: 'form',
					style: 'margin-bottom: 0.5em;',
					title: lang['3_osnovnoy_diagnoz'],
					items:
					[
						// Диагноз
						// 1. Обязательно для заполнения для регионов pskov, ufa, ekb (по ТЗ необязательно для всех)
						// 2. #170429
						//    При изменении значения корректируется видимость полей "Стадия ХСН" и "Функц. класс" и
						//    значения в этих полях. Если значения не пустые, выдается уведомление.
						{
							xtype: 'swdiagcombo',
							id: 'EVPLEF_DiagCombo',
							hiddenName: 'Diag_id',
							tabIndex: TABINDEX_EVPLEF + 27,
							checkAccessRights: true,

							allowBlank: !(regNick.inlist(['pskov', 'ufa', 'ekb'])),
							width: 450,

							onChange: function(combo, newValue, oldValue)
							{
								var v;

								this._refreshHsnDetails(combo);

								this.checkAbort();

								if (regNick == 'ekb')
									this.setDefaultMedicalCareKind();

								this._form.findField('TreatmentClass_id').onLoadStore();
								this.refreshFieldsVisibility(['TumorStage_id', 'PainIntensity_id']);
								this.loadSpecificsTree();
								this.checkMesOldUslugaComplexFields();
								this.diagIsChanged = true;

								if (regNick == 'pskov')
								{
									v = this._form.findField('LpuSectionProfile_id');
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
							id: 'EVPLEF_cmbDiagHSNStage',
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
							id: 'EVPLEF_cmbDiagHSNFuncClass',
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
							'keydown': function(inp, e) {
								switch ( e.getKey() ) {
									case Ext.EventObject.TAB:
										var base_form = this.findById('EvnVizitPLEditForm').getForm();

										if ( e.shiftKey == false ) {
											e.stopEvent();

											if ( !this.findById('EVPLEF_EvnDiagPLPanel').collapsed && this.findById('EVPLEF_EvnDiagPLGrid').getStore().getCount() > 0 ) {
												this.findById('EVPLEF_EvnDiagPLGrid').getView().focusRow(0);
												this.findById('EVPLEF_EvnDiagPLGrid').getSelectionModel().selectFirstRow();
											}
											else if ( !this.findById('EVPLEF_EvnUslugaPanel').hidden && !this.findById('EVPLEF_EvnUslugaPanel').collapsed && this.findById('EVPLEF_EvnUslugaGrid').getStore().getCount() > 0 ) {
												this.findById('EVPLEF_EvnUslugaGrid').getView().focusRow(0);
												this.findById('EVPLEF_EvnUslugaGrid').getSelectionModel().selectFirstRow();
											}
											else if ( !this.findById('EVPLEF_EvnReceptPanel').collapsed && this.findById('EVPLEF_EvnReceptGrid').getStore().getCount() > 0 ) {
												this.findById('EVPLEF_EvnReceptGrid').getView().focusRow(0);
												this.findById('EVPLEF_EvnReceptGrid').getSelectionModel().selectFirstRow();
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
								this.refreshFieldsVisibility(['TumorStage_id']);
							}.createDelegate(this)
						},
						tabIndex: TABINDEX_EVPLEF + 28,
						width: 450,
						xtype: 'swdeseasetypecombo'
					}, {
						fieldLabel:langs('Стадия выявленного ЗНО'),
						hiddenName:'TumorStage_id',
						xtype:'swtumorstagenewcombo',
						tabIndex: TABINDEX_EVPLEF + 29,
						width: 450,
						loadParams: getRegionNumber().inlist([58,66,101]) ? {mode: 1} : {mode:0} // только свой регион / + нулловый рег
					}, {
						comboSubject: 'PainIntensity',
						fieldLabel: langs('Интенсивность боли'),
						hiddenName: 'PainIntensity_id',
						tabIndex: TABINDEX_EVPLEF + 29.5,
						width: 450,
						xtype: 'swcommonsprcombo'
					},
						this.DrugTherapySchemePanel
					,{
						bodyStyle: 'padding: 0px',
						border: false,
						id: 'EVPLEF_IsZNOPanel',
						hidden: (regNick == 'kz'),
						layout: 'form',
						xtype: 'panel',
						items: [{
							fieldLabel: langs('Подозрение на ЗНО'),
							id: 'EVPLEF_EvnVizitPL_IsZNOCheckbox',
							tabIndex: TABINDEX_EVPLEF + 30,
							xtype: 'checkbox',
							listeners:{
								'change': function(checkbox, value) {
									if (regNick != 'ekb' || checkbox.disabled) return;
									var base_form = current_window.findById('EvnVizitPLEditForm').getForm(),
										DiagSpid = Ext.getCmp('EVPLEF_Diag_spid'),
										diagcode = base_form.findField('Diag_id').getFieldValue('Diag_Code'),
										personframe = current_window.findById('EVPLEF_PersonInformationFrame');
									if(!value && current_window.lastzno == 2 && (Ext.isEmpty(diagcode) || diagcode.search(new RegExp("^(C|D0)", "i"))<0)) {
										sw.swMsg.show({
											buttons: Ext.Msg.YESNO,
											fn: function (buttonId, text, obj) {
												if (buttonId == 'yes') {
													current_window.changeZNO({isZNO: false});
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
										if(Ext.isEmpty(DiagSpid.getValue()) && !Ext.isEmpty(current_window.lastznodiag)) {
											DiagSpid.getStore().load({
												callback:function () {
													DiagSpid.getStore().each(function (rec) {
														if (rec.get('Diag_id') == current_window.lastznodiag) {
															DiagSpid.fireEvent('select', DiagSpid, rec, 0);
														}
													});
												},
												params:{where:"where DiagLevel_id = 4 and Diag_id = " + current_window.lastznodiag}
											});
										}
										current_window.changeZNO({isZNO: true});
									}
								},
								'check': function(checkbox, value) {
									var DiagSpid = Ext.getCmp('EVPLEF_Diag_spid');
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
							id: 'EVPLEF_Diag_spid',
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
								current_window.setDiagSpidComboDisabled();
							},
							width: 450,
							xtype: 'swdiagcombo'
						}, {
							layout: 'form',
							border: false,
							id: 'EVPLEF_BiopsyDatePanel',
							hidden: (regNick != 'ekb'),
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
					// Отображаются только для регионов ufa, ekb.
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
								id: 'EVPLEF_cmbComplDiag',
								hiddenName: 'Diag_agid',
								fieldLabel: lang['oslojnenie'],
								tabIndex: TABINDEX_EVPLEF + 30,
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
								id: 'EVPLEF_cmbComplDiagHSNStage',
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
								id: 'EVPLEF_cmbComplDiagHSNFuncClass',
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
					},
					]
				}),

				new sw.Promed.Panel({
					border: true,
					collapsible: true,
					height: 200,
					id: 'EVPLEF_EvnDiagPLPanel',
					isLoaded: false,
					layout: 'border',
					listeners: {
						'expand': function(panel) {
							if ( panel.isLoaded === false ) {
								panel.isLoaded = true;
								panel.findById('EVPLEF_EvnDiagPLGrid').getStore().load({
									params: {
										EvnVizitPL_id: this.findById('EvnVizitPLEditForm').getForm().findField('EvnVizitPL_id').getValue()
									}
								});
							}
							panel.doLayout();
						}.createDelegate(this)
					},
					style: 'margin-bottom: 0.5em;',
					title: lang['4_soputstvuyuschie_diagnozyi'],
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
						id: 'EVPLEF_EvnDiagPLGrid',
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

								var grid = this.findById('EVPLEF_EvnDiagPLGrid');

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

									case Ext.EventObject.TAB:
										grid.getSelectionModel().clearSelections();
										grid.getSelectionModel().fireEvent('rowselect', grid.getSelectionModel());

										if ( e.shiftKey == false ) {
											if ( !this.findById('EVPLEF_EvnUslugaPanel').hidden && !this.findById('EVPLEF_EvnUslugaPanel').collapsed && this.findById('EVPLEF_EvnUslugaGrid').getStore().getCount() > 0 ) {
												this.findById('EVPLEF_EvnUslugaGrid').getView().focusRow(0);
												this.findById('EVPLEF_EvnUslugaGrid').getSelectionModel().selectFirstRow();
											}
											else if ( !this.findById('EVPLEF_EvnReceptPanel').collapsed && this.findById('EVPLEF_EvnReceptGrid').getStore().getCount() > 0 ) {
												this.findById('EVPLEF_EvnReceptGrid').getView().focusRow(0);
												this.findById('EVPLEF_EvnReceptGrid').getSelectionModel().selectFirstRow();
											}
											else if ( this.action == 'view' ) {
												this.buttons[this.buttons.length - 1].focus();
											}
											else {
												this.buttons[0].focus();
											}
										}
										else {
											var base_form = this.findById('EvnVizitPLEditForm').getForm();

											if ( this.action != 'view' ) {
												if ( !this.findById('EVPLEF_DiagPanel').collapsed ) {
													base_form.findField('DeseaseType_id').focus(true);
												}
												else {
													base_form.findField('PersonDisp_id').focus(true);
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
									var toolbar = this.findById('EVPLEF_EvnDiagPLGrid').getTopToolbar();

									if ( selected_record ) {
										access_type = selected_record.get('accessType');
										id = selected_record.get('EvnDiagPL_id');
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
										LoadEmptyRow(this.findById('EVPLEF_EvnDiagPLGrid'));
									}

									// this.findById('EVPLEF_EvnDiagPLGrid').getView().focusRow(0);
									// this.findById('EVPLEF_EvnDiagPLGrid').getSelectionModel().selectFirstRow();
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
					collapsible: true,
					height: 200,
					// Открываем для Уфы, ибо https://redmine.swan.perm.ru/issues/19517
					hidden: (regNick == 'pskov'),
					id: 'EVPLEF_EvnUslugaPanel',
					isLoaded: false,
					layout: 'border',
					listeners: {
						'expand': function(panel) {
							if ( panel.hidden ) {
								return false;
							}

							if ( panel.isLoaded === false ) {
								panel.isLoaded = true;
								panel.findById('EVPLEF_EvnUslugaGrid').getStore().load({
									params: {
										pid: this.findById('EvnVizitPLEditForm').getForm().findField('EvnVizitPL_id').getValue()
									}
								});
							}
							panel.doLayout();
						}.createDelegate(this)
					},
					style: 'margin-bottom: 0.5em;',
					title: lang['5_uslugi'],
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
						id: 'EVPLEF_EvnUslugaGrid',
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

								var grid = this.findById('EVPLEF_EvnUslugaGrid');

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

										var toolbar = this.findById('EVPLEF_EvnUslugaGrid').getTopToolbar();
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

									case Ext.EventObject.TAB:
										grid.getSelectionModel().clearSelections();
										grid.getSelectionModel().fireEvent('rowselect', grid.getSelectionModel());

										if ( e.shiftKey == false ) {
											if ( !this.findById('EVPLEF_EvnReceptPanel').collapsed && this.findById('EVPLEF_EvnReceptGrid').getStore().getCount() > 0 ) {
												this.findById('EVPLEF_EvnReceptGrid').getView().focusRow(0);
												this.findById('EVPLEF_EvnReceptGrid').getSelectionModel().selectFirstRow();
											}
											else if ( this.action == 'view' ) {
												this.buttons[this.buttons.length - 1].focus();
											}
											else {
												this.buttons[0].focus();
											}
										}
										else {
											var base_form = this.findById('EvnVizitPLEditForm').getForm();

											if ( !this.findById('EVPLEF_EvnDiagPLPanel').collapsed && this.findById('EVPLEF_EvnDiagPLGrid').getStore().getCount() > 0 ) {
												this.findById('EVPLEF_EvnDiagPLGrid').getView().focusRow(0);
												this.findById('EVPLEF_EvnDiagPLGrid').getSelectionModel().selectFirstRow();
											}
											else if ( this.action != 'view' ) {
												if ( !this.findById('EVPLEF_DiagPanel').collapsed ) {
													base_form.findField('DeseaseType_id').focus(true);
												}
												else {
													base_form.findField('PersonDisp_id').focus(true);
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
								var toolbar = this.findById('EVPLEF_EvnUslugaGrid').getTopToolbar();
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
									var toolbar = this.findById('EVPLEF_EvnUslugaGrid').getTopToolbar();

									if ( selected_record ) {
										evn_class = selected_record.get('EvnClass_SysNick');
										access_type = selected_record.get('accessType');
										id = selected_record.get('EvnUsluga_id');
									}

									toolbar.items.items[1].disable();
									toolbar.items.items[3].disable();

									if ( id ) {
										if (regNick == 'pskov') {
											toolbar.items.items[0].disable();
										}

										toolbar.items.items[2].enable();

										if ( this.action != 'view' && access_type == 'edit' && evn_class != 'EvnUslugaPar' ) {
											toolbar.items.items[1].enable();
											toolbar.items.items[3].enable();
										}
									}
									else {
										toolbar.items.items[2].disable();

										if (regNick == 'pskov') {
											if ( this.action != 'view' && this.findById('EVPLEF_EvnUslugaGrid').getStore().getCount() == 1 ) {
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
										LoadEmptyRow(this.findById('EVPLEF_EvnUslugaGrid'));
									}
									var from_usluga_add=store.from_usluga_add;delete store.from_usluga_add;//yl:признак вызова после добавления услуги
									this.reloadUslugaComplexField(null,null,from_usluga_add);
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
					bodyStyle: 'padding-top: 0.5em;',
					border: true,
					collapsible: true,
					height: 200,
					id: 'EVPLEF_EvnReceptPanel',
					layout: 'border',
					listeners: {
						'expand': function(panel) {
							if ( panel.isLoaded === false ) {
								panel.isLoaded = true;
								panel.findById('EVPLEF_EvnReceptGrid').getStore().load({
									params: {
										EvnRecept_pid: this.findById('EvnVizitPLEditForm').getForm().findField('EvnVizitPL_id').getValue()
									}
								});
							}
							panel.doLayout();
						}.createDelegate(this)
					},
					style: 'margin-bottom: 0.5em;',
					title: lang['6_retseptyi'],
					items: [ new Ext.grid.GridPanel({
						autoExpandColumn: 'autoexpand_recept',
						autoExpandMin: 100,
						border: false,
						columns: [{
							dataIndex: 'EvnRecept_setDate',
							header: lang['data'],
							hidden: false,
							renderer: Ext.util.Format.dateRenderer('d.m.Y'),
							resizable: false,
							sortable: true,
							width: 80
						}, {
							dataIndex: 'MedPersonal_Fio',
							header: lang['vrach'],
							hidden: false,
							resizable: false,
							sortable: true,
							width: 250
						}, {
							dataIndex: 'Drug_Name',
							header: lang['medikament'],
							hidden: false,
							id: 'autoexpand_recept',
							sortable: true
						}, {
							dataIndex: 'EvnRecept_Ser',
							header: lang['seriya'],
							hidden: false,
							resizable: false,
							sortable: true,
							width: 100
						}, {
							dataIndex: 'EvnRecept_Num',
							header: lang['nomer'],
							hidden: false,
							resizable: false,
							sortable: true,
							width: 100
						}],
						frame: false,
						id: 'EVPLEF_EvnReceptGrid',
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

								var grid = this.findById('EVPLEF_EvnReceptGrid');

								switch ( e.getKey() ) {
									case Ext.EventObject.DELETE:
										this.deleteEvent('EvnRecept');
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
										else if ( e.getKey() == Ext.EventObject.ENTER || e.getKey() == Ext.EventObject.F4 ) {
											action = 'view';
										}

										this.openEvnReceptEditWindow(action);
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
											if ( this.action == 'view' ) {
												this.buttons[this.buttons.length - 1].focus();
											}
											else {
												this.buttons[0].focus();
											}
										}
										else {
											var base_form = this.findById('EvnVizitPLEditForm').getForm();

											if ( !this.findById('EVPLEF_EvnUslugaPanel').hidden && !this.findById('EVPLEF_EvnUslugaPanel').collapsed && this.findById('EVPLEF_EvnUslugaGrid').getStore().getCount() > 0 ) {
												this.findById('EVPLEF_EvnUslugaGrid').getView().focusRow(0);
												this.findById('EVPLEF_EvnUslugaGrid').getSelectionModel().selectFirstRow();
											}
											else if ( !this.findById('EVPLEF_EvnDiagPLPanel').collapsed && this.findById('EVPLEF_EvnDiagPLGrid').getStore().getCount() > 0 ) {
												this.findById('EVPLEF_EvnDiagPLGrid').getView().focusRow(0);
												this.findById('EVPLEF_EvnDiagPLGrid').getSelectionModel().selectFirstRow();
											}
											else if ( this.action != 'view' ) {
												if ( !this.findById('EVPLEF_DiagPanel').collapsed ) {
													base_form.findField('DeseaseType_id').focus(true);
												}
												else {
													base_form.findField('PersonDisp_id').focus(true);
												}
											}
											else {
												this.buttons[this.buttons.length - 1].focus();
											}
										}
									break;
								}
							}.createDelegate(this),
							stopEvent: true
						}],
						layout: 'fit',
						listeners: {
							'rowdblclick': function(grid, number, obj) {
								this.openEvnReceptEditWindow('view');
							}.createDelegate(this)
						},
						loadMask: true,
						region: 'center',
						sm: new Ext.grid.RowSelectionModel({
							listeners: {
								'rowselect': function(sm, rowIndex, record) {
									var id = null;
									var selected_record = sm.getSelected();
									var toolbar = this.findById('EVPLEF_EvnReceptGrid').getTopToolbar();

									if ( selected_record ) {
										id = selected_record.get('EvnRecept_id');
									}

									toolbar.items.items[1].disable();
									toolbar.items.items[3].disable();

									if ( id ) {
										toolbar.items.items[2].enable();

										if ( this.action != 'view' ) {
											//toolbar.items.items[1].enable();
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
										LoadEmptyRow(this.findById('EVPLEF_EvnReceptGrid'));
									}

									// this.findById('EVPLEF_EvnReceptGrid').getView().focusRow(0);
									// this.findById('EVPLEF_EvnReceptGrid').getSelectionModel().selectFirstRow();
								}.createDelegate(this)
							},
							reader: new Ext.data.JsonReader({
								id: 'EvnRecept_id'
							}, [{
								mapping: 'EvnRecept_id',
								name: 'EvnRecept_id',
								type: 'int'
							}, {
								mapping: 'EvnRecept_pid',
								name: 'EvnRecept_pid',
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
								mapping: 'ReceptRemoveCauseType_id',
								name: 'ReceptRemoveCauseType_id',
								type: 'int'
							}, {
								mapping: 'Server_id',
								name: 'Server_id',
								type: 'int'
							}, {
								dateFormat: 'd.m.Y',
								mapping: 'EvnRecept_setDate',
								name: 'EvnRecept_setDate',
								type: 'date'
							}, {
								mapping: 'MedPersonal_Fio',
								name: 'MedPersonal_Fio',
								type: 'string'
							}, {
								mapping: 'Drug_Name',
								name: 'Drug_Name',
								type: 'string'
							}, {
								mapping: 'EvnRecept_Ser',
								name: 'EvnRecept_Ser',
								type: 'string'
							}, {
								mapping: 'EvnRecept_Num',
								name: 'EvnRecept_Num',
								type: 'string'
							}]),
							url: C_EVNREC_LIST
						}),
						tbar: new sw.Promed.Toolbar({
							buttons: [{
								handler: function() {
									this.openEvnReceptEditWindow('add');
								}.createDelegate(this),
								iconCls: 'add16',
								text: BTN_GRIDADD,
								tooltip: BTN_GRIDADD_TIP
							}, {
								handler: function() {
									this.openEvnReceptEditWindow('edit');
								}.createDelegate(this),
								iconCls: 'edit16',
								text: BTN_GRIDEDIT,
								tooltip: BTN_GRIDEDIT_TIP
							}, {
								handler: function() {
									this.openEvnReceptEditWindow('view');
								}.createDelegate(this),
								iconCls: 'view16',
								text: BTN_GRIDVIEW,
								tooltip: BTN_GRIDVIEW_TIP
							}, {
								handler: function() {
									this.deleteEvent('EvnRecept');
								}.createDelegate(this),
								iconCls: 'delete16',
								text: BTN_GRIDDEL,
								tooltip: BTN_GRIDDEL_TIP
							}]
						}),
						view: new Ext.grid.GridView({
							getRowClass: function (row, index) {
								var cls = '';

								if ( parseInt(row.get('ReceptRemoveCauseType_id')) > 0 ) {
									cls = cls + 'x-grid-rowgray';
								}
								else {
									cls = 'x-grid-panel'; 
								}

								return cls;
							}
						})
					})]
				}),
				new sw.Promed.Panel({
					bodyStyle: 'padding-top: 0.5em;',
					hidden: !regNick.inlist(['astra', 'kareliya']),
					border: true,
					collapsible: true,
					height: 200,
					id: 'EVPLEF_EvnDirectionPanel',
					layout: 'border',
					listeners: {
						'expand': function(panel) {
							var base_form = this.findById('EvnVizitPLEditForm').getForm()

							if ( panel.isLoaded === false ) {
								panel.isLoaded = true;
								current_window.EvnDirectionGrid.loadData({
									params: {
										EvnDirection_pid: this.findById('EvnVizitPLEditForm').getForm().findField('EvnVizitPL_id').getValue(),
										Person_id: base_form.findField('Person_id').getValue()
									},
									globalFilters: {
										EvnDirection_pid: this.findById('EvnVizitPLEditForm').getForm().findField('EvnVizitPL_id').getValue(),
										Person_id: base_form.findField('Person_id').getValue()
									}
								});
							}
							panel.doLayout();
						}.createDelegate(this)
					},
					style: 'margin-bottom: 0.5em;',
					title: '7. Направления',
					items: [
						current_window.EvnDirectionGrid
					]
				}),
				new sw.Promed.Panel({
					autoHeight: true,
					border: true,
					collapsible: true,
					id: 'EVPLEF_SpecificPanel',
					isLoaded: false,
					layout: 'form',
					listeners: {
						'expand': function(panel) {
							if ( panel.isLoaded === false && this.findById('EVPLEF_EvnBirthForm').isVisible() && this.findById('EvnVizitPLEditForm').getForm().findField('EvnVizitPL_id').getValue() > 0 ) {
								panel.isLoaded = true;
								var base_form = this.findById('EvnVizitPLEditForm').getForm()
								var Morbuspreg = base_form.findField('MorbusPregnancy_id').getValue();
								if(this.action!='add'){
									Ext.Ajax.request({
										callback:function (options, success, response) {
											if(Morbuspreg>0){
												base_form.findField('MorbusPregnancyPresent').setValue(2);
												current_window.findById('birthDataFieldset').enable();
												current_window.checkAbortFields(false)
											}
											var response_obj = Ext.util.JSON.decode(response.responseText);

											for(var x in response_obj){
												if(x!='success'){
													base_form.findField(x).setValue(response_obj[x])
												}
											}
										},
										params:{
											MorbusPregnancy_id:Morbuspreg
										},
										url:'/?c=MorbusPregnancy&m=load',
										method:'POST'
									})
								}
							}

							panel.doLayout();
						}.createDelegate(this)
					},
					style: 'margin-bottom: 0.5em;',
					title: (regNick.inlist(['astra', 'kareliya']) ? '8' : '7') + '. Специфика',
					items: [{
						border: true,
						height: 200,
						id: 'EVPLEF_SpecificsPanel',
						isLoaded: false,
						region: 'north',
						layout: 'border',
						style: 'margin-bottom: 0.5em;',
						items: [
							{
								autoScroll:true,
								border:false,
								collapsible:false,
								wantToFocus:false,
								id: 'EVPLEF_SpecificsTree',
								listeners:{
									'bodyresize': function(tree) {
										
									}.createDelegate(this),
									'beforeload': function(node) {
										
									}.createDelegate(this),
									'click':function (node, e) {
										var base_form = this.findById('EvnVizitPLEditForm').getForm();
										var win = this;
										
										var params = {};
										params.onHide = function(isChange) {
											win.loadSpecificsTree();
											win.findById('EVPLEF_EvnUslugaGrid').getStore().load({
												params: {
													pid: win.findById('EvnVizitPLEditForm').getForm().findField('EvnVizitPL_id').getValue()
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
									}.createDelegate(this),
									contextmenu: function(node, e) {
										if (!!node.leaf) {
											var c = new Ext.menu.Menu({
											items: [{
												id: 'print',
												text: langs('Печать КЛУ при ЗНО'),
												disabled: !node.attributes.Morbus_id,
												icon: 'img/icons/print16.png',
												iconCls : 'x-btn-text'
											},{
												id: 'printOnko',
												text: langs('Печать выписки по онкологии'),
												disabled: !(node.attributes.Morbus_id && regNick == 'ekb'),
												hidden: regNick != 'ekb',
												icon: 'img/icons/print16.png',
												iconCls : 'x-btn-text'
											}],
											listeners: {
												itemclick: function(item) {
													switch (item.id) {
														case 'print': 
															var n = item.parentMenu.contextNode;
															printControlCardZno(n.attributes.value, n.attributes.EvnDiagPLSop_id);
															break;
														case 'printOnko':
															var n = item.parentMenu.contextNode;
															printOnko(n.attributes.value, n.attributes.EvnDiagPLSop_id);
															break;
													}
												}
											}
											});
											c.contextNode = node;
											c.showAt(e.getXY());
										}
									}
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
							}
						]
					}, {
						autoHeight:true,
						border:false,
						frame:false,
						id:'EVPLEF_EvnBirthForm',
						isLoaded:false,
						labelWidth:290,
						layout:'form',
						xtype:'panel',
						items:[
							{
								name:'Morbus_id',
								xtype:'hidden',
								value:0
							},
							{
								fieldLabel:lang['zapolnit_dannyie_po_beremennosti_i_rodam'],
								comboSubject:'YesNo',
								hiddenName:'MorbusPregnancyPresent',
								name:'MorbusPregnancyPresent',
								width:70,
								value:1,
								xtype:'swcommonsprcombo',
								listeners:{
									select:function (i,d,f) {
										if (2 == f) {
											current_window.findById('birthDataFieldset').enable();
											current_window.checkAbortFields(false);
											}
										 else {
											current_window.findById('birthDataFieldset').disable();
											current_window.checkAbortFields(true);
										}
									}
								}
							},
							{
								id:'birthDataFieldset',
								xtype:'fieldset',
								disabled:true,
								labelWidth:180,
								height:240,
								items:[
									{
										allowDecimals:false,
										allowNegative:false,
										fieldLabel:lang['kotoraya_beremennost'],
										minValue:1,
										maxValue:99,
										autoCreate: {tag: "input", maxLength: "2", autocomplete: "off"},
										name:'MorbusPregnancy_CountPreg',
										hiddenName:'MorbusPregnancy_CountPreg',
										width:100,
										xtype:'numberfield'
									},
									{
										layout:'column',
										border:false,
										autoHeight:true,
										items:[
											{
												layout:'form',
												border:false,
												labelWidth:180,
												items:[
													{
														allowBlank:true,
														fieldLabel:lang['data_ishoda_beremennosti'],
														format:'d.m.Y',
														listeners:{
															'change':function (field, newValue, oldValue) {
																this.calculateOutcomPeriod();
															}.createDelegate(this)
														},
														name:'MorbusPregnancy_OutcomD',
														plugins:[ new Ext.ux.InputTextMask('99.99.9999', false) ],
														selectOnFocus:true,
														width:100,
														xtype:'swdatefield'
													}
												]
											},
											{
												layout:'form',
												border:false,
												labelWidth:50,
												items:[
													{
														fieldLabel:lang['vremya'],
														listeners:{
															'keydown':function (inp, e) {
																if (e.getKey() == Ext.EventObject.F4) {
																	e.stopEvent();
																	inp.onTriggerClick();
																}
															}
														},
														name:'MorbusPregnancy_OutcomT',
														onTriggerClick:function () {
															var base_form = this.findById('EvnVizitPLEditForm').getForm();
															var time_field = base_form.findField('MorbusPregnancy_OutcomT');

															if (time_field.disabled) {
																return false;
															}

															setCurrentDateTime({
																dateField:base_form.findField('MorbusPregnancy_OutcomD'),
																loadMask:true,
																setDate:true,
																setDateMaxValue:true,
																setDateMinValue:false,
																setTime:true,
																timeField:time_field,
																windowId:this.id
															});
														}.createDelegate(this),
														plugins:[ new Ext.ux.InputTextMask('99:99', true) ],
														validateOnBlur:false,
														width:60,
														xtype:'swtimefield'
													}
												]
											}
										]
									},
									{
										comboSubject:'BirthResult',
										fieldLabel:lang['ishod_beremennosti'],
										hiddenName:'BirthResult_id',
										name:'BirthResult_id',
										width:160,
										xtype:'swcommonsprcombo',
										value:3,
										disabled:true
									},
									{
										allowDecimals:false,
										allowNegative:false,
										fieldLabel:lang['srok_nedel'],
										maxValue:50,
										minValue:1,
										name:'MorbusPregnancy_OutcomPeriod',
										autoCreate: {tag: "input", maxLength: "2", autocomplete: "off"},
										width:100,
										xtype:'numberfield',
										tooltip:'tooltip',
										hint:'hint',
										label:'label',
										title:'title'
									},
									{
										comboSubject:'AbortType',
										fieldLabel:lang['tip_aborta'],
										hiddenName:'AbortType_id',
										width:200,
										xtype:'swcommonsprcombo'
									},
									{
										comboSubject:'YesNo',
										fieldLabel:lang['medikamentoznyiy'],
										hiddenName:'MorbusPregnancy_IsMedicalAbort',
										width:100,
										xtype:'swcommonsprcombo'
									},
									{
										allowDecimals:false,
										allowNegative:false,
										fieldLabel:lang['krovopoteri_ml'],
										name:'MorbusPregnancy_BloodLoss',
										hiddenName:'MorbusPregnancy_BloodLoss',
										width:100,
										maxValue:9999,
										autoCreate: {maskRe:new RegExp('^[0-9]$'),decimalPrecision:0,tag: "input", maxLength: "4", autocomplete: "off"},
										xtype:'numberfield'
									},
									{
										comboSubject:'YesNo',
										fieldLabel:lang['obsledovana_na_vich'],
										hiddenName:'MorbusPregnancy_IsHIVtest',
										width:100,
										xtype:'swcommonsprcombo'
									},
									{
										comboSubject:'YesNo',
										fieldLabel:lang['nalichie_vich-infektsii'],
										hiddenName:'MorbusPregnancy_IsHIV',
										width:100,
										xtype:'swcommonsprcombo'
									}
								]
							}
						]
					}]
				}),
				new sw.Promed.Panel({
					hidden: getRegionNick() == 'kz',
					autoHeight: true,
					border: true,
					collapsible: true,
					isLoaded: false,
					layout: 'form',
					listeners: {
						'expand': function (panel) {
							panel.doLayout();
						}.createDelegate(this)
					},
					style: 'margin-bottom: 0.5em;',
					title: (getRegionNick().inlist(['astra', 'kareliya']) ? '9' : '8') + '. Скрининговые обследования',
					items: [this.EvnPLDispScreenOnkoGrid]
				})
				],
				reader: new Ext.data.JsonReader(
				{
					success: function() 
					{ 
						//
					}
				}, 
				[
					{name: 'accessType'},
					{name: 'EvnVizitPL_id'},
					{name: 'EvnVizitPL_Index'},
					{name: 'MorbusPregnancy_id'},
					{name: 'EvnPL_id'},
					{name: 'EvnPL_lid'},
					{name: 'EvnUslugaCommon_id'},
					{name: 'HealthKind_id'},
					{name: 'LpuBuilding_id'},
					{name: 'LpuUnit_id'},
					{name: 'LpuUnitSet_id'},
					{name: 'Person_id'},
					{name: 'PersonEvn_id'},
					{name: 'Server_id'},
					{name: 'TimetableGraf_id'},
					{name: 'EvnPrescr_id'},
					{name: 'EvnDirection_id'},
					{name: 'EvnVizitPL_setDate'},
					{name: 'EvnVizitPL_setTime'},
					{name: 'EvnVizitPL_IsPaid'},
					{name: 'EvnVizitPL_Uet'},
					{name: 'EvnVizitPL_UetOMS'},
					{name: 'VizitClass_id'},
					{name: 'DispWowSpec_id'},
					{name: 'LpuSection_id'},
					{name: 'MedPersonal_id'},
					{name: 'MedStaffFact_id'},
					{name: 'MedPersonal_sid'},
					{name: 'TreatmentClass_id'},
					{name: 'ScreenType_id'},
					{name: 'ServiceType_id'},
					{name: 'VizitType_id'},
					{name: 'EvnVizitPL_Time'},
					{name: 'RiskLevel_id'},
					{name: 'WellnessCenterAgeGroups_id'},
					{name: 'PayType_id'},
					{name: 'MedicalCareKind_id'},
					{name: 'ProfGoal_id'},
					{name: 'Diag_id'},
					{name: 'HSNStage_id'},
					{name: 'HSNFuncClass_id'},
					{name: 'Diag_agid'},
					{name: 'ComplDiagHSNStage_id'},
					{name: 'ComplDiagHSNFuncClass_id'},
					{name: 'DeseaseType_id'},
					{name: 'TumorStage_id'},
					{name: 'UslugaComplex_uid'},
					{name: 'Mes_id'},
					{name: 'LpuSectionProfile_id'},
					{name: 'DispClass_id'},
					{name: 'DispProfGoalType_id'},
					{name: 'EvnPLDisp_id'},
					{name: 'PersonDisp_id'},
					{name: 'RankinScale_id'},
					{name: 'PregnancyEvnVizitPL_Period'},
					{name: 'EvnVizitPL_IsZNO'},
					{name: 'EvnVizitPL_IsZNORemove'},
					{name: 'EvnVizitPL_BiopsyDate'},
					{name: 'PainIntensity_id'},
					{name: 'Diag_spid'},
					{name: 'DrugTherapyScheme_ids'},
					{name: 'AlertReg_Msg'},
					{name: 'UslugaMedType_id'},
					{name: 'PayTypeKAZ_id'},
					{name: 'VizitActiveType_id'},
					{name: 'action'}
				]),
				region: 'center',
				timeout: 300,
				url: '/?c=EvnPL&m=saveEvnVizitPL'
			})]
		});
		
		sw.Promed.swEvnVizitPLEditWindow.superclass.initComponent.apply(this, arguments);

		// Инициализируем ссылки на компоненты:
		this._cmbDiag = this.findById('EVPLEF_DiagCombo');
		this._cmbDiagHSNStage = this.findById('EVPLEF_cmbDiagHSNStage');
		this._cmbDiagHSNFuncClass = this.findById('EVPLEF_cmbDiagHSNFuncClass');
		this._cmbComplDiag = this.findById('EVPLEF_cmbComplDiag');
		this._cmbComplDiagHSNStage = this.findById('EVPLEF_cmbComplDiagHSNStage');
		this._cmbComplDiagHSNFuncClass = this.findById('EVPLEF_cmbComplDiagHSNFuncClass');
		this._formPanel = this.findById('EvnVizitPLEditForm');

		if (this._formPanel)
			this._form = this._formPanel.getForm();

		if (this._form)
			setTimeout(
				function()
				{
					this._fldPersonId = this._form.findField('Person_id');
				}.createDelegate(this),
				1);

		this._cmbDiag.addListener('change', function(combo, newValue, oldValue) {
			var base_form = this._form;

			var index = combo.getStore().findBy(function(rec) {
				return (rec.get('Diag_id') == newValue);
			});

			if ( index >= 0 && !Ext.isEmpty(combo.getStore().getAt(index).get('Diag_Code')) && combo.getStore().getAt(index).get('Diag_Code').substr(0, 1).toUpperCase() != 'Z' ) {
				base_form.findField('DeseaseType_id').setAllowBlank(false);
			}
			else {
				base_form.findField('DeseaseType_id').setAllowBlank(true);
			}

			if (regNick == 'ekb') {
				if (this._cmbDiag.getFieldValue('DiagFinance_IsRankin') && this._cmbDiag.getFieldValue('DiagFinance_IsRankin') == 2) {
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

			if (regNick == 'ufa') {
				this.findById('EVPLEF_EvnVizitPL_IsZNOCheckbox').setValue(combo.getFieldValue('Diag_Code') == 'Z03.1');
				this.findById('EVPLEF_EvnVizitPL_IsZNOCheckbox').disable();
			}
			else {
				if (regNick != 'krym' && combo.getFieldValue('Diag_Code') && combo.getFieldValue('Diag_Code').search(new RegExp("^(C|D0)", "i")) >= 0) {
					this.findById('EVPLEF_EvnVizitPL_IsZNOCheckbox').setValue(false);
					this.findById('EVPLEF_EvnVizitPL_IsZNOCheckbox').disable();
				} else {
					this.findById('EVPLEF_EvnVizitPL_IsZNOCheckbox').enable();

					if (regNick == 'buryatiya') {
						this.findById('EVPLEF_EvnVizitPL_IsZNOCheckbox').setValue(combo.getFieldValue('Diag_Code') == 'Z03.1');
					}
				}
			}

			this.findById('EVPLEF_EvnVizitPL_IsZNOCheckbox').fireEvent('check', this.findById('EVPLEF_EvnVizitPL_IsZNOCheckbox'), this.findById('EVPLEF_EvnVizitPL_IsZNOCheckbox').getValue());
			
			this.getFinanceSource();

			this.loadSpecificsTree();
		}.createDelegate(this));

		this.findById('EVPLEF_LpuSectionCombo').addListener('change', function(combo, newValue, oldValue) {
			this.setDefaultMedicalCareKind();
			this.setLpuSectionProfile();
			this.loadLpuSectionProfileDop();

			var base_form = this.findById('EvnVizitPLEditForm').getForm();
			var uslugacomplex_combo = base_form.findField('UslugaComplex_uid');

			uslugacomplex_combo.getStore().baseParams.LpuSection_id = newValue;

			if (regNick.inlist(['ufa','buryatiya','perm','pskov'])) {
				if (regNick == 'ufa' && !newValue) {
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


				if ( record ) {
					if (regNick == 'ufa') {
						uslugacomplex_combo.setLpuLevelCode(record.get('LpuSectionProfile_Code'));
						} else if (regNick.inlist(['buryatiya', 'perm', 'pskov'])) {
						uslugacomplex_combo.setLpuSectionProfile_id(record.get('LpuSectionProfile_id'));
					}

					this.reloadUslugaComplexField();
					//uslugacomplex_combo.getStore().load();
				}
			} else if (regNick == 'ekb') {
				uslugacomplex_combo.lastQuery = 'This query sample that is not will never appear';
				uslugacomplex_combo.getStore().removeAll();
				uslugacomplex_combo.getStore().baseParams.query = '';

				uslugacomplex_combo.getStore().baseParams.LpuSection_id = newValue;
				this.loadMesCombo();
				this.reloadUslugaComplexField();
			} else if (regNick == 'kz') {
				uslugacomplex_combo.lastQuery = 'This query sample that is not will never appear';
				uslugacomplex_combo.getStore().removeAll();
				uslugacomplex_combo.getStore().baseParams.query = '';

				uslugacomplex_combo.getStore().baseParams.LpuSection_id = newValue;
				this.reloadUslugaComplexField();
			}
			this.checkMesOldUslugaComplexFields();
		}.createDelegate(this));

		this.findById('EVPLEF_MidMedPersonalCombo').addListener('change', function(combo, newValue, oldValue) {
			this.setDefaultMedicalCareKind()
		}.createDelegate(this));

		this.findById('EVPLEF_MedPersonalCombo').addListener('change', function(combo, newValue, oldValue) {
			this.setDefaultMedicalCareKind();
			this.loadLpuSectionProfileDop();

			if (regNick.inlist(['ufa'])) {
				var base_form = this.findById('EvnVizitPLEditForm').getForm();

				var uslugacomplex_combo = base_form.findField('UslugaComplex_uid'),
					uslugacomplex_combo_value = base_form.findField('UslugaComplex_uid').getValue();

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

				this.reloadUslugaComplexField();

				if ( record ) {
					uslugacomplex_combo.setLpuLevelCode(record.get('LpuSectionProfile_Code'));
					//uslugacomplex_combo.getStore().load();
				}

			} else if (regNick.inlist(['buryatiya','perm','pskov'])) {
				var base_form = this.findById('EvnVizitPLEditForm').getForm();

				var uslugacomplex_combo = base_form.findField('UslugaComplex_uid');
				uslugacomplex_combo.setLpuSectionProfile_id(base_form.findField('LpuSection_id').getFieldValue('LpuSectionProfile_id'));
				this.reloadUslugaComplexField();

			} else if (regNick == 'ekb') {
				var base_form = this.findById('EvnVizitPLEditForm').getForm();

				var uslugacomplex_combo = base_form.findField('UslugaComplex_uid');

				uslugacomplex_combo.lastQuery = 'This query sample that is not will never appear';
				uslugacomplex_combo.getStore().removeAll();
				uslugacomplex_combo.getStore().baseParams.query = '';

				uslugacomplex_combo.getStore().baseParams.LpuSection_id = base_form.findField('LpuSection_id').getValue();
				uslugacomplex_combo.getStore().baseParams.MedPersonal_id = combo.getFieldValue('MedPersonal_id');

				this.filterLpuSectionProfile();
				this.reloadUslugaComplexField();
			} else if (regNick == 'kz') {
				var base_form = this.findById('EvnVizitPLEditForm').getForm();

				var uslugacomplex_combo = base_form.findField('UslugaComplex_uid');

				uslugacomplex_combo.lastQuery = 'This query sample that is not will never appear';
				uslugacomplex_combo.getStore().removeAll();
				uslugacomplex_combo.getStore().baseParams.query = '';

				uslugacomplex_combo.getStore().baseParams.LpuSection_id = base_form.findField('LpuSection_id').getValue();
			}
		}.createDelegate(this));

		this.findById('EVPLEF_LpuSectionProfile').addListener('change', function(combo, newValue, oldValue) {
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
		/*var base_form = this.findById('EvnVizitPLEditForm').getForm();

		if (getRegionNick() == 'ekb') {
			var combo = base_form.findField('MedStaffFact_id');
			base_form.findField('LpuSectionProfile_id').lastQuery = 'This query sample that is not will never appear';
			base_form.findField('LpuSectionProfile_id').getStore().removeAll();
			base_form.findField('LpuSectionProfile_id').getStore().load({
				params: {
					LpuSection_id: base_form.findField('LpuSection_id').getValue(),
					MedPersonal_id: combo.getFieldValue('MedPersonal_id'),
					MedSpecOms_id: combo.getFieldValue('MedSpecOms_id'),
					onDate: Ext.util.Format.date(base_form.findField('EvnVizitPL_setDate').getValue(), 'd.m.Y'),
					LpuSectionProfileGRAPP_CodeIsNotNull: (base_form.findField('PayType_id').getFieldValue('PayType_SysNick') == 'oms' ? 1 : null)
				},
				callback: function() {
					var id = base_form.findField('LpuSectionProfile_id').getValue();
					var index = base_form.findField('LpuSectionProfile_id').getStore().findBy(function(rec) { return rec.get('LpuSectionProfile_id') == id; });
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
			var base_form = this.findById('EvnVizitPLEditForm').getForm();
			
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
			var current_window = Ext.getCmp('EvnVizitPLEditWindow');

			switch ( e.getKey() ) {
				case Ext.EventObject.C:
					current_window.doSave({
                        isDoSave: true,
						ignoreEvnUslugaCountCheck: false
					});
				break;

				case Ext.EventObject.J:
					current_window.onCancelAction();
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
					current_window.findById('EVPLEF_DiagPanel').toggleCollapse();
				break;

				case Ext.EventObject.NUM_FOUR:
				case Ext.EventObject.FOUR:
					current_window.findById('EVPLEF_EvnDiagPLPanel').toggleCollapse();
				break;

				case Ext.EventObject.NUM_FIVE:
				case Ext.EventObject.FIVE:
					if  ( !current_window.findById('EVPLEF_EvnUslugaPanel').hidden ) {
						current_window.findById('EVPLEF_EvnUslugaPanel').toggleCollapse();
					}
				break;

				case Ext.EventObject.NUM_SIX:
				case Ext.EventObject.SIX:
					current_window.findById('EVPLEF_EvnReceptPanel').toggleCollapse();
				break;
				/*
				case Ext.EventObject.NUM_SEVEN:
				case Ext.EventObject.SEVEN:
					current_window.findById('EVPLEF_EvnDirectionPanel').toggleCollapse();
				break;

				case Ext.EventObject.NUM_EIGHT:
				case Ext.EventObject.EIGHT:
					
				break;
				*/
			}
		},
		key: [
			Ext.EventObject.C,
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
			Ext.EventObject.THREE,
			Ext.EventObject.EIGHT,
			Ext.EventObject.NUM_EIGHT
		],
		stopEvent: true
	}],
	layout: 'border',
	listeners: {
		'hide': function(win) {
			win.onHide({
				EvnUslugaGridIsModified: win.EvnUslugaGridIsModified
			});
		},
		'maximize': function(win) {
			win.EvnXmlPanel.doLayout();
			win.findById('EVPLEF_DiagPanel').doLayout();
			win.findById('EVPLEF_EvnDiagPLPanel').doLayout();
			win.findById('EVPLEF_EvnReceptPanel').doLayout();
			win.findById('EVPLEF_EvnDirectionPanel').doLayout();
			win.findById('EVPLEF_EvnUslugaPanel').doLayout();
		},
		'restore': function(win) {
			win.EvnXmlPanel.doLayout();
			win.findById('EVPLEF_DiagPanel').doLayout();
			win.findById('EVPLEF_EvnDiagPLPanel').doLayout();
			win.findById('EVPLEF_EvnReceptPanel').doLayout();
			win.findById('EVPLEF_EvnDirectionPanel').doLayout();
			win.findById('EVPLEF_EvnUslugaPanel').doLayout();
		}
	},
	maximizable: true,
	minHeight: 550,
	minWidth: 700,
	modal: true,
	onCancelAction: function() {
		var evn_vizit_pl_id = this.findById('EvnVizitPLEditForm').getForm().findField('EvnVizitPL_id').getValue();

		if ( evn_vizit_pl_id > 0 ) {
			switch ( this.action ) {
				case 'add':
					// удалить посещение
					// закрыть окно после успешного удаления
					var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Удаление посещения..."});
					loadMask.show();

					Ext.Ajax.request({
						callback: function(options, success, response) {
							loadMask.hide();

							if ( success ) {
								this.hide();
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
				break;

				case 'edit':
				case 'view':
					this.hide();
				break;
			}
		}
		else {
			this.hide();
		}
	},
	onHide: Ext.emptyFn,
	openEvnDiagPLEditWindow: function(action) {
		if ( action != 'add' && action != 'edit' && action != 'view' ) {
			return false;
		}

		var base_form = this.findById('EvnVizitPLEditForm').getForm();
		var grid = this.findById('EVPLEF_EvnDiagPLGrid');

		if ( this.action == 'view') {
			if ( action == 'add') {
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
		var person_birthday = this.findById('EVPLEF_PersonInformationFrame').getFieldValue('Person_Birthday');
		var person_firname = this.findById('EVPLEF_PersonInformationFrame').getFieldValue('Person_Firname');
		var person_secname = this.findById('EVPLEF_PersonInformationFrame').getFieldValue('Person_Secname');
		var person_surname = this.findById('EVPLEF_PersonInformationFrame').getFieldValue('Person_Surname');

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
			//console.log('selected_record.get(\'accessType\')',selected_record.get('accessType'));
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

		var params = {
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
		};

		params.archiveRecord = this.archiveRecord;

		getWnd('swEvnDiagPLEditWindow').show(params);
	},
	openEvnReceptEditWindow: function(action) {

		// Переменная для региональных настроек:
		var regNick = getRegionNick();

		if ( action != 'add' && action != 'edit' && action != 'view' ) {
			return false;
		}
		var ReceptWnd = regNick.inlist([ 'khak', 'krym', 'pskov', 'saratov' ])?'swEvnReceptRlsEditWindow':'swEvnReceptEditWindow';//https://redmine.swan.perm.ru/issues/60206
		var base_form = this.findById('EvnVizitPLEditForm').getForm();
		var grid = this.findById('EVPLEF_EvnReceptGrid');

		if ( this.action == 'view') {
			if ( action == 'add') {
				return false;
			}
			else if ( action == 'edit' ) {
				action = 'view';
			}
		}

		if (
			action == 'add' && regNick == 'msk'
			&& (
				!isDLOUser(Ext.util.Format.date(base_form.findField('EvnVizitPL_setDate').getValue(), 'd.m.Y'))
				|| Ext.util.Format.date(base_form.findField('EvnVizitPL_setDate').getValue(), 'd.m.Y') != getGlobalOptions().date
			)
		) {
			return false;
		}

		if ( getWnd(ReceptWnd).isVisible() ) { //https://redmine.swan.perm.ru/issues/60206
			sw.swMsg.alert(lang['soobschenie'], lang['okno_redaktirovaniya_retsepta_uje_otkryito']);
			return false;
		}

		if ( action == 'add' && base_form.findField('EvnVizitPL_id').getValue() == 0 ) {
			this.doSave({
				ignoreEvnUslugaCountCheck: true,
				openChildWindow: function() {
					this.openEvnReceptEditWindow(action);
				}.createDelegate(this),
				print: false
			});
			return false;
		}

		var params = new Object();

		if ( action == 'add' ) {
			var evn_vizit_pl_set_date = base_form.findField('EvnVizitPL_setDate').getValue();
			var lpu_section_id = base_form.findField('LpuSection_id').getValue();
			var med_staff_fact_id = base_form.findField('MedStaffFact_id').getValue();

			if ( !evn_vizit_pl_set_date || !lpu_section_id || !med_staff_fact_id ) {
				sw.swMsg.alert(lang['soobschenie'], lang['ne_zadanyi_obyazatelnyie_parametryi_posescheniya']);
				return false;
			}

			var record = base_form.findField('MedStaffFact_id').getStore().getById(med_staff_fact_id);
			if ( !record ) {
				return false;
			}

			params.Diag_id = base_form.findField('Diag_id').getValue();
			params.EvnRecept_id = 0;
			params.EvnRecept_pid = base_form.findField('EvnVizitPL_id').getValue();
			params.EvnRecept_setDate = evn_vizit_pl_set_date;
			params.LpuSection_id = lpu_section_id;
			params.MedPersonal_id = med_staff_fact_id; // record.get('MedPersonal_id');
			params.Person_id = base_form.findField('Person_id').getValue();
			params.PersonEvn_id = base_form.findField('PersonEvn_id').getValue();
			params.Server_id = base_form.findField('Server_id').getValue();
		}
		else {
			var selected_record = grid.getSelectionModel().getSelected();

			if ( !selected_record || !selected_record.get('EvnRecept_id') ) {
				return false;
			}

			params.EvnRecept_id = selected_record.get('EvnRecept_id');
			params.Person_id = selected_record.get('Person_id');
			params.PersonEvn_id = selected_record.get('PersonEvn_id');
			params.Server_id = selected_record.get('Server_id');
		}

		params.action = action;
		params.callback = function(data) {
			if ( !data || !data.EvnReceptData ) {
				return false;
			}

			var record = grid.getStore().getById(data.EvnReceptData.EvnRecept_id);

			if ( !record ) {
				if ( grid.getStore().getCount() == 1 && !grid.getStore().getAt(0).get('EvnRecept_id') ) {
					grid.getStore().removeAll();
				}

				grid.getStore().loadData([ data.EvnReceptData ], true);
			}
			else {
				var grid_fields = new Array();
				var i = 0;

				grid.getStore().fields.eachKey(function(key, item) {
					grid_fields.push(key);
				});

				for ( i = 0; i < grid_fields.length; i++ ) {
					record.set(grid_fields[i], data.EvnReceptData[grid_fields[i]]);
				}

				record.commit();
			}
		}.createDelegate(this);
		params.onHide = function() {
			grid.getView().focusRow(0);
			grid.getSelectionModel().selectFirstRow();
		}.createDelegate(this);

		params.archiveRecord = this.archiveRecord;

		getWnd(ReceptWnd).show(params); //https://redmine.swan.perm.ru/issues/60206
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
		var base_form = this.findById('EvnVizitPLEditForm').getForm();
		var grid = this.findById('EVPLEF_EvnUslugaGrid');

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
				grid.getStore().from_usluga_add="from_usluga_add";//yl:признак вызова из добавления услуги
				grid.getStore().load({
					params: {
						pid: base_form.findField('EvnVizitPL_id').getValue()
					},
					callback: function() {
						win.checkAbort();
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
			this.reloadUslugaComplexField();
			this.checkMesOldUslugaComplexFields();
		}.createDelegate(this);
		params.onHide = function() {
			grid.getView().focusRow(0);
			grid.getSelectionModel().selectFirstRow();
		}.createDelegate(this);
		params.parentClass = 'EvnVizit';
		params.Person_id = base_form.findField('Person_id').getValue();
		params.Person_Birthday = this.findById('EVPLEF_PersonInformationFrame').getFieldValue('Person_Birthday');
		params.Person_Firname = this.findById('EVPLEF_PersonInformationFrame').getFieldValue('Person_Firname');
		params.Person_Secname = this.findById('EVPLEF_PersonInformationFrame').getFieldValue('Person_Secname');
		params.Person_Surname = this.findById('EVPLEF_PersonInformationFrame').getFieldValue('Person_Surname');

		if (regNick == 'perm') {
			var currSetDate = Ext.util.Format.date(base_form.findField('EvnVizitPL_setDate').getValue(), 'd.m.Y');
			var currSetTime = base_form.findField('EvnVizitPL_setTime').getValue();
			var currSetDT = getValidDT(currSetDate, currSetTime);
			var lastSetDT = currSetDT;

			if (currSetDT && Ext.isArray(this.OtherVizitList)) {
				for(var i=0; i<this.OtherVizitList.length; i++) {
					var vizit = this.OtherVizitList[i];
					var setDT = getValidDT(vizit.EvnVizitPL_setDate, vizit.EvnVizitPL_setTime);
					if (setDT && setDT > lastSetDT) {
						lastSetDT = setDT;
					}
				}
			}
			if (Ext.isArray(this.OtherUslugaList)) {
				for(var i=0; i<this.OtherUslugaList.length; i++) {
					var usluga = this.OtherUslugaList[i];
					var setDT = getValidDT(usluga.EvnUsluga_setDate, '00:00');
					if (setDT && setDT > lastSetDT) {
						lastSetDT = setDT;
					}
				}
			}

			params.UslugaComplex_Date = Ext.util.Format.date(lastSetDT, 'd.m.Y');
		}

		var getUslugaComplexDate = function(evn_usluga_id) {
			var lastSetDate = null;
			if (!Ext.isEmpty(params.UslugaComplex_Date)) {
				lastSetDate = getValidDT(params.UslugaComplex_Date, '00:00');
			}
			grid.getStore().each(function(record){
				if (Ext.isEmpty(evn_usluga_id) || record.get('EvnUsluga_id') != evn_usluga_id) {
					var setDate = record.get('EvnUsluga_setDate');
					if (!lastSetDate || lastSetDate < setDate) {
						lastSetDate = setDate;
					}
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
			VizitType_SysNick = base_form.findField('VizitType_id').getFieldValue('VizitType_SysNick'),
			diag_id = this._cmbDiag.getValue();

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
			VizitType_SysNick: VizitType_SysNick,
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
				//console.log('selected_record.get(\'accessType\')',selected_record.get('accessType'));
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
	plain: true,
	resizable: true,
	setFilter: function(value)
	{
		form = this;
		var mass = form.getListDispWowSpec(form.owner,value);
		var combo = form.findById('EVPLEF_DispWowSpecCombo');
		combo.getStore().clearFilter();
		combo.lastQuery = '';
		combo.getStore().filterBy(function(record) 
		{
			if (value==record.get('DispWowSpec_id'))
			{
				combo.fireEvent('select', combo, record, 0);
			}
			return (!(record.get('DispWowSpec_id').inlist(mass))) && (!((form.Sex_id == 1) && (record.get('DispWowSpec_id').inlist([7]))));
		});
		if (value==0)
		{
			combo.fireEvent('change', combo, '', '');
		}
	},
	setFilterValue: function(combo, field_name)
	{
		var id = combo.getValue();
		var fs = false;
		combo.getStore().each(function(record) 
		{
			if (record.get(field_name) == id)
			{
				combo.fireEvent('select', combo, record, 0);
				fs = true;
			}
		});
		if (!fs) 
		{
			combo.setValue('');
		}
	},
	setFilterProfile: function(field, far, type)
	{

		var form = this;
		var bf = this.findById('EvnVizitPLEditForm').getForm();
		var dateValue = bf.findField('EvnVizitPL_setDate').getValue();
		var params = new Object();
		var regNick = getRegionNick();
		var isUfa = (regNick == 'ufa');
		var isAstra = (regNick == 'astra');
		var person_age = swGetPersonAge(this.findById('EVPLEF_PersonInformationFrame').getFieldValue('Person_Birthday'), dateValue);
		var WithoutChildLpuSectionAge=false;
		if (person_age >= 18 && !isUfa && !isAstra) {
			WithoutChildLpuSectionAge = true;
		}
		params.WithoutChildLpuSectionAge = WithoutChildLpuSectionAge;
		params.isPolka = true;

		if (type==10)
		{
			params.arrayLpuSectionProfileNot = far;
		}
		if ( !dateValue ) 
		{
			params.onDate = Ext.util.Format.date(dateValue, 'd.m.Y');
		}

		setLpuSectionGlobalStoreFilter(params);

		params.EvnClass_SysNick = 'EvnVizit';
		setMedStaffFactGlobalStoreFilter(params);
		bf.findField('LpuSection_id').getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));
		bf.findField('MedStaffFact_id').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));
		
		params.EvnClass_SysNick = null;
		params.isMidMedPersonalOnly = true;
		setMedStaffFactGlobalStoreFilter(params);
		bf.findField('MedStaffFact_sid').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));
		form.setFilterValue(bf.findField('LpuSection_id'), 'LpuSection_id');
		form.setFilterValue(bf.findField('MedStaffFact_id'), 'MedStaffFact_id');
		form.setFilterValue(bf.findField('MedStaffFact_sid'), 'MedStaffFact_id');
	},
	setDefaultMedicalCareKind: function() {

		// Переменная для региональных настроек:
		var regNick = getRegionNick();

		if (regNick == 'kz') { // для Казахстана поле не нужно
			return;
		}

		var base_form = this.findById('EvnVizitPLEditForm').getForm();

		// устанавливаем только при добавлении
		if (this.action == 'add' && !regNick.inlist(['ekb', 'kareliya', 'ufa'])) {
			// Если специальность врача из случая средняя, то вид мед. помощи = 11
			if  (
				base_form.findField('MedStaffFact_id').getFieldValue('FedMedSpec_Code') == 204
				|| base_form.findField('MedStaffFact_id').getFieldValue('FedMedSpecParent_Code') == 204
			) {
				base_form.findField('MedicalCareKind_id').setFieldValue('MedicalCareKind_Code', '11');
			}
			// Если специальность врача из случая врачебная и равна 16, 22, 27 (терапевт, педиатр или ВОП), то вид мед. помощи = 12
			else if (
				!Ext.isEmpty(base_form.findField('MedStaffFact_id').getFieldValue('FedMedSpec_Code'))
				&& base_form.findField('MedStaffFact_id').getFieldValue('FedMedSpec_Code').toString().inlist([ '16', '22', '27' ])
			) {
				base_form.findField('MedicalCareKind_id').setFieldValue('MedicalCareKind_Code', '12');
			}
			// 13 – В остальных случаях
			else {
				base_form.findField('MedicalCareKind_id').setFieldValue('MedicalCareKind_Code', '13');
			}
		}

		if (regNick == 'ufa') {
			var LpuSectionProfile_id = base_form.findField('LpuSectionProfile_id').getValue();
			base_form.findField('MedicalCareKind_id').getStore().findBy(function(rec) {
				swMedicalCareKindLpuSectionProfileGlobalStore.findBy(function(r) {
					if (r.get('LpuSectionProfile_id') == LpuSectionProfile_id && r.get('MedicalCareKind_id') == rec.get('MedicalCareKind_id')) {
						base_form.findField('MedicalCareKind_id').setValue(r.get('MedicalCareKind_id'));
					}
				});
			});
		}

		if (regNick == 'kareliya') {
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
		if (regNick == 'ekb') {
			var
				Diag_Code = base_form.findField('Diag_id').getFieldValue('Diag_Code'),
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
	},
	deleteEvnDirectionLink: function() {
		if ( this.action == 'view') {
			return false;
		}

		var win = this;
		var record = this.EvnDirectionGrid.getGrid().getSelectionModel().getSelected();

		if (record && record.get('EvnDirection_id')) {
			win.getLoadMask('Удаление связи направления со случаем').show();
			Ext.Ajax.request({
				url: '/?c=EvnDirection&m=saveEvnDirectionPid',
				params: {
					EvnDirection_id: record.get('EvnDirection_id'),
					EvnDirection_pid: null
				},
				callback: function() {
					win.getLoadMask().hide();
					win.EvnDirectionGrid.getGrid().getStore().reload();
				}
			});
		}
	},
	openEvnDirectionEditWindow: function() {
		var record = this.EvnDirectionGrid.getGrid().getSelectionModel().getSelected();

		if (record && record.get('EvnDirection_id')) {
			var base_form = this.findById('EvnVizitPLEditForm').getForm();

			getWnd('swEvnDirectionEditWindow').show({
				Person_id: base_form.findField('Person_id').getValue(),
				EvnDirection_id: record.get('EvnDirection_id'),
				action: 'view',
				formParams: {}
			});
		}
	},
	openEvnDirectionSelectWindow: function() {
		if ( this.action == 'view') {
			return false;
		}

		var win = this;
		var base_form = this.findById('EvnVizitPLEditForm').getForm();

		// если форма не сохранена, надо сохранить
		if ( base_form.findField('EvnVizitPL_id').getValue() == 0 ) {
			this.doSave({
				ignoreEvnUslugaCountCheck: true,
				openChildWindow: function() {
					this.openEvnDirectionSelectWindow();
				}.createDelegate(this)
			});
			return false;
		}

		if ( getWnd('swEvnDirectionSelectWindow').isVisible() ) {
			sw.swMsg.alert('Сообщение', 'Окно выбора направления уже открыто');
			return false;
		}

		getWnd('swEvnDirectionSelectWindow').show({
			useCase: 'choose_for_evnvizitpl_link',
			callback: function(data) {
				if (data.EvnDirection_id) {
					// сохраняем связь
					win.getLoadMask('Сохранение связи направления со случаем').show();
					Ext.Ajax.request({
						url: '/?c=EvnDirection&m=saveEvnDirectionPid',
						params: {
							EvnDirection_id: data.EvnDirection_id,
							EvnDirection_pid: base_form.findField('EvnVizitPL_id').getValue()
						},
						callback: function() {
							win.getLoadMask().hide();

							win.EvnDirectionGrid.loadData({
								params: {
									EvnDirection_pid: base_form.findField('EvnVizitPL_id').getValue(),
									Person_id: base_form.findField('Person_id').getValue()
								},
								globalFilters: {
									EvnDirection_pid: base_form.findField('EvnVizitPL_id').getValue(),
									Person_id: base_form.findField('Person_id').getValue()
								}
							});
						}
					});
				}
			},
			Person_Birthday: this.findById('EVPLEF_PersonInformationFrame').getFieldValue('Person_Birthday'),
			Person_Firname: this.findById('EVPLEF_PersonInformationFrame').getFieldValue('Person_Firname'),
			Person_id: base_form.findField('Person_id').getValue(),
			Person_Secname: this.findById('EVPLEF_PersonInformationFrame').getFieldValue('Person_Secname'),
			Person_Surname: this.findById('EVPLEF_PersonInformationFrame').getFieldValue('Person_Surname')
		});
	},
	show: function() {
		var
			// Переменная для региональных настроек:
			regNick = getRegionNick(),

			v,
			diagStore = this._cmbDiag.getStore(),
			complDiagStore = this._cmbComplDiag.getStore();

		sw.Promed.swEvnVizitPLEditWindow.superclass.show.apply(this, arguments);
		var win = this;
		this.restore();
		this.center();
		this.maximize();
        var base_form = this._form;

		base_form.findField('RankinScale_id').hideContainer();
		base_form.findField('RankinScale_id').clearValue();
		base_form.findField('RankinScale_id').setAllowBlank(true);
        base_form.findField('HealthKind_id').disable();
		base_form.findField('Diag_id').filterDate = null;

		if ( this.firstRun == true ) {
			this.EvnXmlPanel.collapse();
            this.EvnXmlPanel.LpuSectionField = base_form.findField('LpuSection_id');
            this.EvnXmlPanel.MedStaffFactField = base_form.findField('MedStaffFact_id');

            this.findById('EVPLEF_EvnDiagPLPanel').collapse();
			this.findById('EVPLEF_EvnReceptPanel').collapse();
			this.findById('EVPLEF_EvnDirectionPanel').collapse();
			this.findById('EVPLEF_SpecificPanel').collapse();
			this.firstRun = false;
		}
		this.checkAbortFields(true);
		this.evnDirectionAllInfoPanel.onReset(this);

		base_form.findField('PayTypeKAZ_id').setContainerVisible(getRegionNick() == 'kz');

		base_form.reset();
        this.EvnXmlPanel.doReset();
        this.doLayout();
/*
		if (!this.MorbusHepatitisSpec.hidden) {
			this.MorbusHepatitisSpec.doLayout();
		}
		this.MorbusHepatitisSpec.hide();
*/
		var enable_usluga_section_load_filter = getUslugaOptions().enable_usluga_section_load_filter;
		var index;
		var isBuryatiya = (regNick == 'buryatiya');
		var isEkb = (regNick == 'ekb');
		var isPskov = (regNick == 'pskov');
		var isUfa = (regNick == 'ufa');
		var isKareliya = (regNick == 'kareliya');
		var isAstra = (regNick == 'astra');
		var record;

		base_form.findField('EvnPLDisp_id').setContainerVisible(!isUfa);

		base_form.findField('DispClass_id').setContainerVisible(!isUfa);
		base_form.findField('DispProfGoalType_id').setContainerVisible(isUfa);
		base_form.findField('DispProfGoalType_id').setAllowBlank(true);
		base_form.findField('PersonDisp_id').setAllowBlank(true);
		
		base_form.findField('VizitActiveType_id').clearValue();
		base_form.findField('VizitActiveType_id').clearFilter();
		base_form.findField('VizitActiveType_id').hideContainer();

		base_form.findField('ScreenType_id').hideContainer();
		base_form.findField('ScreenType_id').setAllowBlank(true);

		base_form.findField('LpuSectionProfile_id').getStore().baseParams = {};

        this.EvnPLDispScreenOnkoGrid.doLayout();
        this.EvnPLDispScreenOnkoGrid.getGrid().getStore().removeAll();

		this.action = null;
		this.allowConsulDiagnVizitOnly = 0;
		this.allowMorbusVizitCodesGroup88 = 0;
		this.allowMorbusVizitOnly = 0;
		this.allowNonMorbusVizitOnly = 0;
		this.callback = Ext.emptyFn;
		this.EvnUslugaGridIsModified = false;
		this.formStatus = 'edit';
		this.FormType = null;
		this.from = null;
		this.loadLastData = false;
		this.onHide = Ext.emptyFn;
		this.owner = null;
		this.Sex_id = null;
		this.streamInput = false;
		this.TimetableGraf_id = null;
		this.ServiceType_SysNick = null;
		this.VizitType_SysNick = null;
		this.RiskLevel_id = null;
		this.WellnessCenterAgeGroups_id = null;
		this.OtherVizitList = null;
		this.OtherUslugaList = null;
		this.LpuSection_id = null;
		this.previousVizitDate = null;
		this.previousVizitType_Code = null;
		this.diagIsChanged = false;
		this.IsLoading = true;
		this.IsProfLoading = false;
		this.RepositoryObservData = {};

		this.EvnPSInfo = null;
		this.DrugTherapySchemePanel.resetFieldSets();
		this.DrugTherapySchemePanel.hide();

		if ( !arguments[0] || (!arguments[0].formParams && !arguments[0].FormType) ) { // http://172.19.61.24:85/issues/show/2428 открывается и из ВОВ (осмотр ВОВ - обычное посещение, а formParams там нету)
			sw.swMsg.alert(lang['soobschenie'], lang['nevernyie_parametryi'], function() {this.hide();}.createDelegate(this) );
			return false;
		}

		if (Ext.isArray(arguments[0].OtherVizitList)) {
			this.OtherVizitList = arguments[0].OtherVizitList;
		}
		if (Ext.isArray(arguments[0].OtherUslugaList)) {
			this.OtherUslugaList = arguments[0].OtherUslugaList;
		}

		// For EvnVizitPLWow, Night
		// Если поле FormType есть и равно EvnVizitPLWow, то это посещение ВОВ, в данном случае переданные поля преобразуем в необходимые дальше
		if ( arguments[0].FormType && arguments[0].FormType == 'EvnVizitPLWow' ) 
		{
			this.FormType = arguments[0].FormType;

			arguments[0].formParams = new Object();

			if ( arguments[0].action == 'add' )
			{
				arguments[0].formParams.EvnVizitPL_id = 0;
			}
			else
			{
				arguments[0].formParams.EvnVizitPL_id = arguments[0].EvnVizitPLWOW_id;
			}

			arguments[0].formParams.EvnPL_id = arguments[0].EvnPLWOW_id;
			arguments[0].formParams.Person_id = arguments[0].Person_id;
			arguments[0].formParams.PersonEvn_id = arguments[0].PersonEvn_id;
			arguments[0].formParams.Server_id = arguments[0].Server_id;
				
			if ( arguments[0].Sex_id ) {
				this.Sex_id = arguments[0].Sex_id;
			}
			base_form.findField('Diag_id').setAllowBlank(false);
			this.findById('EVPLEF_DispWowSpecComboSet').setVisible(true);
			this.findById('EVPLEF_DispWowSpecCombo').setAllowBlank(false);
			this.findById('EVPLEF_EvnUslugaPanel').setVisible(false);

			var PayType_SysNick = 'oms';
			switch (regNick) {
				case 'by': PayType_SysNick = 'besus'; break;
				case 'kz': PayType_SysNick = 'Resp'; break;
			}

			index = base_form.findField('PayType_id').getStore().findBy(function(rec) {
				return (rec.get('PayType_SysNick') == PayType_SysNick);
			});

			if ( index >= 0 ) {
				base_form.findField('PayType_id').setValue(base_form.findField('PayType_id').getStore().getAt(index).get('PayType_id'));
				base_form.findField('PayType_id').fireEvent('change',base_form.findField('PayType_id'),base_form.findField('PayType_id').getValue());
			}
		}
		else 
		{
			this.findById('EVPLEF_DispWowSpecComboSet').setVisible(false);
			this.findById('EVPLEF_DispWowSpecCombo').setAllowBlank(true);
			// this.findById('EVPLEF_EvnUslugaPanel').setVisible(true);
			if (arguments[0].action == 'add') {
				var PayType_SysNick = 'oms';
				index = base_form.findField('PayType_id').getStore().findBy(function(rec) {
					return (rec.get('PayType_SysNick') == PayType_SysNick);
				});

				if ( index >= 0 ) {
					base_form.findField('PayType_id').setValue(base_form.findField('PayType_id').getStore().getAt(index).get('PayType_id'));
					base_form.findField('PayType_id').fireEvent('change',base_form.findField('PayType_id'),base_form.findField('PayType_id').getValue());
				}
			}
		}
		
		if ( arguments[0].action ) {
			this.action = arguments[0].action;

			if ( this.action == 'add' ) {
				if ( arguments[0].allowConsulDiagnVizitOnly == true ) {
					this.allowConsulDiagnVizitOnly = 1;
				}

				if ( arguments[0].allowMorbusVizitOnly == true ) {
					this.allowMorbusVizitOnly = 1;
					this.allowMorbusVizitCodesGroup88 = arguments[0].allowMorbusVizitCodesGroup88 || 0;
				}

				if ( arguments[0].allowNonMorbusVizitOnly == true ) {
					this.allowNonMorbusVizitOnly = 1;
				}
				
				this.UslugaComplex_uid = arguments[0].UslugaComplex_uid;
			}
		}

		if ( arguments[0].callback ) {
			this.callback = arguments[0].callback;
		}
		
		if ( arguments[0].from ) {
			this.from = arguments[0].from;
		}

		if ( arguments[0].loadLastData ) {
			this.loadLastData = arguments[0].loadLastData;
		}

		if ( arguments[0].owner ) {
			this.owner = arguments[0].owner;
		}

		if ( arguments[0].ServiceType_SysNick ) {
			this.ServiceType_SysNick = arguments[0].ServiceType_SysNick;
		}
		
		if ( arguments[0].onHide ) {
			this.onHide = arguments[0].onHide;
		}

		if ( arguments[0].streamInput ) {
			this.streamInput = arguments[0].streamInput;
		}

		if ( arguments[0].TimetableGraf_id ) {
			this.TimetableGraf_id = arguments[0].TimetableGraf_id;
		}

		if ( arguments[0].EvnPSInfo ) {
			this.EvnPSInfo = arguments[0].EvnPSInfo;
		}

		this.lastEvnVizitPLData = arguments[0].lastEvnVizitPLData || null;
		this.userMedStaffFact = (this.streamInput == true || Ext.isEmpty(sw.Promed.MedStaffFactByUser.current) || sw.Promed.MedStaffFactByUser.current.ARMType == 'mstat' ? new Object() : sw.Promed.MedStaffFactByUser.current);

		base_form.findField('UslugaComplex_uid').clearBaseParams();
		// base_form.findField('UslugaComplex_uid').clearValue();
		base_form.findField('UslugaComplex_uid').getStore().removeAll();
		this.lastUslugaComplexParams = null;
		this.blockUslugaComplexReload = false;
		if ( sw.Promed.EvnVizitPL.isSupportVizitCode() ) {
			base_form.findField('Mes_id').clearBaseParams();
		}
		win.findById('EVPLEF_EvnBirthForm').setVisible(false);
		this.findById('EVPLEF_PersonInformationFrame').load({
			Person_id: (arguments[0].Person_id ? arguments[0].Person_id : ''),
			Person_Birthday: (arguments[0].Person_Birthday ? arguments[0].Person_Birthday : ''),
			Person_Firname: (arguments[0].Person_Firname ? arguments[0].Person_Firname : ''),
			Person_Secname: (arguments[0].Person_Secname ? arguments[0].Person_Secname : ''),
			Person_Surname: (arguments[0].Person_Surname ? arguments[0].Person_Surname : ''),
			Person_closeDT: (arguments[0].Person_closeDT ? arguments[0].Person_closeDT : ''),
			Person_deadDT: (arguments[0].Person_deadDT ? arguments[0].Person_deadDT : ''),
			OmsSprTerr_Code: (arguments[0].OmsSprTerr_Code ? arguments[0].OmsSprTerr_Code : ''),
			Sex_Code: (arguments[0].Sex_Code ? arguments[0].Sex_Code : ''),
			callback: function() {
				var field = base_form.findField('EvnVizitPL_setDate');
				clearDateAfterPersonDeath('personpanelid', 'EVPLEF_PersonInformationFrame', field);
				win.setMKB();
				// иначе полис не успевает прогружаться
				win.setInoterFilter(); 
				// Устанавливаем фильтры для кодов посещений
				base_form.findField('UslugaComplex_uid').setVizitCodeFilters({
					allowNonMorbusVizitOnly: 1==this.allowNonMorbusVizitOnly,
					allowMorbusVizitOnly: 1==this.allowMorbusVizitOnly,
					allowMorbusVizitCodesGroup88: 1==this.allowMorbusVizitCodesGroup88
				});
				win.refreshFieldsVisibility(['PregnancyEvnVizitPL_Period']);
			}
		});
		//base_form.findField('Diag_id').on('change',win.checkAbort(win))
		//console.log('this.action',this.action);
		if ( this.action == 'add' ) {
			this.findById('EVPLEF_EvnDiagPLPanel').isLoaded = true;
			this.findById('EVPLEF_SpecificPanel').isLoaded = true;
			//this.findById('EVPLEF_EvnDirectionPanel').isLoaded = true;
			this.findById('EVPLEF_EvnReceptPanel').isLoaded = true;
			this.findById('EVPLEF_EvnDirectionPanel').isLoaded = true;
			this.findById('EVPLEF_EvnUslugaPanel').isLoaded = true;
			//this.findById('EVPLEF_PersonDispPanel').isLoaded = true;
		}
		else {
			this.findById('EVPLEF_EvnDiagPLPanel').isLoaded = false;
			this.findById('EVPLEF_SpecificPanel').isLoaded = false;
			//this.findById('EVPLEF_EvnDirectionPanel').isLoaded = false;
			this.findById('EVPLEF_EvnReceptPanel').isLoaded = false;
			this.findById('EVPLEF_EvnDirectionPanel').isLoaded = false;
			this.findById('EVPLEF_EvnUslugaPanel').isLoaded = false;
			//this.findById('EVPLEF_PersonDispPanel').isLoaded = false;
		}

		this.findById('EVPLEF_EvnDiagPLGrid').getStore().removeAll();
		this.findById('EVPLEF_EvnDiagPLGrid').getTopToolbar().items.items[0].disable();
		if (this.action != 'view') {
			this.findById('EVPLEF_EvnDiagPLGrid').getTopToolbar().items.items[0].enable();
		}
		this.findById('EVPLEF_EvnDiagPLGrid').getTopToolbar().items.items[1].disable();
		this.findById('EVPLEF_EvnDiagPLGrid').getTopToolbar().items.items[2].disable();
		this.findById('EVPLEF_EvnDiagPLGrid').getTopToolbar().items.items[3].disable();

		this.findById('EVPLEF_EvnReceptGrid').getStore().removeAll();
		this.findById('EVPLEF_EvnReceptGrid').getTopToolbar().items.items[0].disable();
		if (this.action != 'view') {
			this.findById('EVPLEF_EvnReceptGrid').getTopToolbar().items.items[0].enable();
		}
		this.findById('EVPLEF_EvnReceptGrid').getTopToolbar().items.items[1].disable();
		this.findById('EVPLEF_EvnReceptGrid').getTopToolbar().items.items[2].disable();
		this.findById('EVPLEF_EvnReceptGrid').getTopToolbar().items.items[3].disable();

		this.EvnDirectionGrid.removeAll({ clearAll: true });

		this.findById('EVPLEF_EvnUslugaGrid').getStore().removeAll();
		this.findById('EVPLEF_EvnUslugaGrid').getTopToolbar().items.items[0].disable();
		if (this.action != 'view') {
			this.findById('EVPLEF_EvnUslugaGrid').getTopToolbar().items.items[0].enable();
		}
		this.findById('EVPLEF_EvnUslugaGrid').getTopToolbar().items.items[1].disable();
		this.findById('EVPLEF_EvnUslugaGrid').getTopToolbar().items.items[2].disable();
		this.findById('EVPLEF_EvnUslugaGrid').getTopToolbar().items.items[3].disable();

		base_form.findField('EvnVizitPL_setDate').setMaxValue(undefined);

		base_form.findField('Diag_id').fireEvent('change', base_form.findField('Diag_id'), null);

		base_form.findField('DispClass_id').fireEvent('change', base_form.findField('DispClass_id'), null);
		base_form.findField('UslugaMedType_id').fireEvent('change', base_form.findField('UslugaMedType_id'), null);
		base_form.findField('EvnPLDisp_id').DispClass_id = 0;
		base_form.findField('EvnPLDisp_id').getStore().removeAll();
		base_form.findField('PersonDisp_id').getStore().removeAll();

		base_form.findField('TreatmentClass_id').onLoadStore();

		this.formParams = arguments[0].formParams;
		
		base_form.setValues(this.formParams);
		base_form.findField('MedicalCareKind_id').clearValue(); // т.к. из ТАП приходит значение из другого справочника
		base_form.findField('VizitType_id').getStore().clearFilter();

		this.setRiskLevelComboState();
		this.setWellnessCenterAgeGroupsComboState();

		base_form.findField('UslugaMedType_id').setContainerVisible(regNick === 'kz');

		// врач и младший мед. персонал
		// base_form.findField('MedStaffFact_id').setFieldValue('MedPersonal_id', this.formParams.MedPersonal_id);
		// base_form.findField('MedStaffFact_sid').setFieldValue('MedPersonal_id', this.formParams.MedPersonal_sid);

		var loadMask = new Ext.LoadMask(this.getEl(), {msg: LOAD_WAIT});
		loadMask.show();

		// #170429
		// Очистим и скроем доп. поля, относящиеся к диагнозу и осложнению из группы ХСН:
		this._hideHsnDetails();

		switch ( this.action ) {
			case 'add':
				// Варианты:
				// 1 - загружаем данные с сервера
				// 2 - устанавливаем параметры с формы поточного ввода
				win.findById('EVPLEF_EvnBirthForm').setVisible(false);
				this.setTitle(WND_POL_EVPLADD);
				this.enableEdit(true);

				if (regNick.inlist(['ufa', 'kareliya', 'ekb'])) {
					base_form.findField('MedicalCareKind_id').disable();
				}

				LoadEmptyRow(this.findById('EVPLEF_EvnDiagPLGrid'));
				LoadEmptyRow(this.findById('EVPLEF_EvnUslugaGrid'));
				LoadEmptyRow(this.findById('EVPLEF_EvnReceptGrid'));

				var set_date_flag = Ext.isEmpty(base_form.findField('EvnVizitPL_setDate').getValue());
				
				var time_field = base_form.findField('EvnVizitPL_setTime');

				if ( !Ext.isEmpty(this.userMedStaffFact.MedStaffFact_id) ) {
					// устанавливаем дату/время, если пришли из рабочего места врача
					time_field.setAllowBlank(false);
				} 
				else {
					time_field.setAllowBlank(true);
				}

				if (regNick.inlist(['buryatiya', 'ekb', 'pskov', 'ufa'])) {
					base_form.findField('UslugaComplex_uid').setPersonId(base_form.findField('Person_id').getValue());
				}

				base_form.findField('PersonDisp_id').getStore().baseParams.Person_id = base_form.findField('Person_id').getValue();


				setCurrentDateTime({
					callback: function() {
						if ( this.loadLastData === true ) {
							// Загружаем данные о последнем посещении с сервера
							Ext.Ajax.request({
								callback: function(options, success, response) {
									var response_obj = Ext.util.JSON.decode(response.responseText);

									if ( typeof response_obj == 'object' && response_obj.length > 0 ) {
										if (isPskov) {
											var UslugaComplex_uid = !Ext.isEmpty(response_obj[0].UslugaComplex_uid)?response_obj[0].UslugaComplex_uid:'';
											if (UslugaComplex_uid) {
												win.UslugaComplex_uid = UslugaComplex_uid;
											}
											win.reloadUslugaComplexField(UslugaComplex_uid);
											base_form.findField('UslugaComplex_uid').disable(); // недоступно для изменения
											base_form.findField('EvnVizitPL_Index').setValue(1); // не первое посещение
										}
										base_form.findField('DeseaseType_id').setValue(response_obj[0].DeseaseType_id);
										base_form.findField('LpuBuilding_id').setValue(response_obj[0].LpuBuilding_id);
										base_form.findField('LpuUnit_id').setValue(response_obj[0].LpuUnit_id);
										base_form.findField('LpuUnitSet_id').setValue(response_obj[0].LpuUnitSet_id);
										base_form.findField('PayType_id').setValue(response_obj[0].PayType_id);
										base_form.findField('PayType_id').fireEvent('change',base_form.findField('PayType_id'),base_form.findField('PayType_id').getValue());
										base_form.findField('ServiceType_id').setValue(response_obj[0].ServiceType_id);
										base_form.findField('Mes_id').setValue(response_obj[0].Mes_id);

										
										win.previousVizitDate = new Date.parse(response_obj[0].EvnVizitPL_setDate);
										win.previousVizitType_Code = response_obj[0].VizitType_Code;
										

										base_form.findField('EvnVizitPL_setDate').fireEvent('change', base_form.findField('EvnVizitPL_setDate'), base_form.findField('EvnVizitPL_setDate').getValue());

										if ( typeof base_form.findField('EvnVizitPL_setDate').getValue() == 'object' && Ext.isEmpty(this.userMedStaffFact.LpuSection_id) && Ext.isEmpty(this.userMedStaffFact.MedStaffFact_id) ) {
											var lpu_section_id = response_obj[0].LpuSection_id;
											win.LpuSection_id = lpu_section_id;

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
												return (record.get('MedPersonal_id') == response_obj[0].MedPersonal_sid);
											});

											if ( index >= 0 ) {
												base_form.findField('MedStaffFact_sid').setValue(base_form.findField('MedStaffFact_id').getStore().getAt(index).get('MedStaffFact_id'));
											}
										}

										base_form.findField('VizitType_id').fireEvent('change', base_form.findField('VizitType_id'), base_form.findField('VizitType_id').getValue());

										if (!Ext.isEmpty(v = response_obj[0].Diag_id))
											diagStore.load(
											{
												callback: function()
												{
													diagStore.each(function(record)
													{
														if (record.get('Diag_id') == v)
														{
															this._cmbDiag.setValue(v);

															// #170429
															this._refreshHsnDetails(this._cmbDiag, false);

															this._cmbDiag.fireEvent('select', this._cmbDiag, record, 0);
															this._cmbDiag.fireEvent('change', this._cmbDiag, v);

															base_form.findField('TreatmentClass_id').onLoadStore();
															win.refreshFieldsVisibility();
															win.diagIsChanged = false;
														}
													}.createDelegate(this));
												},

												params:
												{
													where: "where DiagLevel_id = 4 and Diag_id = " + v
												},

												scope: this
											});

										if ( sw.Promed.EvnVizitPL.isSupportVizitCode() ) {
											var usluga_complex_id = response_obj[0].UslugaComplex_uid;

											if (isUfa || isBuryatiya) {
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
												win.reloadUslugaComplexField(null, usluga_complex_id);
											}
										}
									}

									if (isEkb && win.findById('EVPLEF_PersonInformationFrame').getFieldValue('Person_IsAnonym') == 2) {
										base_form.findField('PayType_id').setFieldValue('PayType_SysNick','bud');
										base_form.findField('PayType_id').disable();
									}

									if (isUfa || isEkb) {
										base_form.findField('PayType_id').fireEvent('change', base_form.findField('PayType_id'), base_form.findField('PayType_id').getValue());
									}
									
									if (isEkb) {
										if(base_form.findField('Mes_id').getValue()>0)
										base_form.findField('Mes_id').fireEvent('change', base_form.findField('Mes_id'), base_form.findField('Mes_id').getValue());
										
										if (!Ext.isEmpty(base_form.findField('Mes_id').getValue())) {
											base_form.findField('Mes_id').disable();
										}
									}

									var vizitDate = base_form.findField('EvnVizitPL_setDate').getValue();
									base_form.findField('DeseaseType_id').getStore().clearFilter();
									base_form.findField('DeseaseType_id').getStore().filterBy(function(rec) {										
										return (
											(!rec.get('DeseaseType_begDT') || rec.get('DeseaseType_begDT') <= vizitDate)
											&& (!rec.get('DeseaseType_endDT') || rec.get('DeseaseType_endDT') >= vizitDate)
										)
									});
									base_form.findField('DeseaseType_id').lastQuery = '';
									

									this.refreshFieldsVisibility();

									loadMask.hide();

									//base_form.clearInvalid();

									base_form.findField('EvnVizitPL_setDate').focus(true, 250);
								}.createDelegate(this),
								params: {
									EvnVizitPL_pid: base_form.findField('EvnPL_id').getValue()
								},
								url: '/?c=EvnVizit&m=loadLastEvnVizitPLData'
							});
                            this.EvnXmlPanel.loadLastEvnProtocolData(base_form.findField('EvnPL_id').getValue());
                            // this.EvnXmlPanel.expand();
						}
						else {
							base_form.findField('EvnVizitPL_setDate').fireEvent('change', base_form.findField('EvnVizitPL_setDate'), base_form.findField('EvnVizitPL_setDate').getValue());

							// Night, если посещение создается из места работы врача (то есть под врачом), то заполняем по умолчанию 
							// Место - скорее всего поликлиника
							// Цель посещения - скорее всего лечебно-диагностическая 
							// Вид оплаты - скорее всего ОМС 
							if ( !Ext.isEmpty(this.userMedStaffFact.MedStaffFact_id) ) {
								var PayType_SysNick = 'oms';
								switch (regNick) {
									case 'by': PayType_SysNick = 'besus'; break;
									case 'kz': PayType_SysNick = 'Resp'; break;
								}
								base_form.findField('PayType_id').setFieldValue('PayType_SysNick', PayType_SysNick);
								base_form.findField('PayType_id').fireEvent('change',base_form.findField('PayType_id'),base_form.findField('PayType_id').getValue());
								base_form.findField('ServiceType_id').setFieldValue('ServiceType_SysNick', 'polka');
								base_form.findField('VizitType_id').setFieldValue('VizitType_SysNick', 'desease');
							}
							
							// Если задан ServiceType то проставляем его
							if (this.ServiceType_SysNick && this.ServiceType_SysNick != null) {
								base_form.findField('ServiceType_id').setFieldValue('ServiceType_SysNick', this.ServiceType_SysNick);
							}

                            if (this.VizitType_SysNick && this.VizitType_SysNick != null) {
								base_form.findField('ServiceType_id').setFieldValue('VizitType_SysNick', this.VizitType_SysNick);
							}

							// Night: если для ВОВ то автоматически ставил тип оплаты - ОМС и не даем изменять
							if ( this.FormType == 'EvnVizitPLWow' && base_form.findField('PayType_id').getValue() > 0 ) {
								base_form.findField('PayType_id').setDisabled(true);
							}
							if (isEkb && win.findById('EVPLEF_PersonInformationFrame').getFieldValue('Person_IsAnonym') == 2) {
								base_form.findField('PayType_id').setFieldValue('PayType_SysNick','bud');
								base_form.findField('PayType_id').disable();
							}

							if (regNick.inlist(['ekb','vologda','krasnoyarsk','pskov', 'msk', 'khak']) && base_form.findField('EvnPL_lid').getValue() > 0) {
								if (win.EvnPSInfo && win.EvnPSInfo.EvnPS_id) {
									// o Отделение. Значение поля «Приемное отделение» формы «Поступление пациента в приемное отделение». Недоступное для редактирования поле.
									base_form.findField('LpuSection_id').setValue(win.EvnPSInfo.LpuSection_pid);
									base_form.findField('LpuSection_id').fireEvent('change', base_form.findField('LpuSection_id'), win.EvnPSInfo.LpuSection_pid);
									base_form.findField('LpuSection_id').disable();
									// o Врач. Значение одноименного поля формы «Поступление пациента в приемное отделение».
									base_form.findField('MedStaffFact_id').setValue(win.EvnPSInfo.MedStaffFact_pid);
									base_form.findField('MedStaffFact_id').fireEvent('change', base_form.findField('MedStaffFact_id'), win.EvnPSInfo.MedStaffFact_pid);
									// o Вид обращения. Значение по умолчанию: «В неотложной форме (Заболевание)»
									base_form.findField('TreatmentClass_id').setFieldValue('TreatmentClass_Code', '1.1');
									// o Место. Значение по умолчанию: «Другое»
									base_form.findField('ServiceType_id').setFieldValue('ServiceType_SysNick', 'other');
									// o Код посещения. Значение одноименного поля формы «Поступление пациента в приемное отделение».
									if (!Ext.isEmpty(win.EvnPSInfo.UslugaComplex_id)) {
										base_form.findField('UslugaComplex_uid').setValue(win.EvnPSInfo.UslugaComplex_id);
										win.reloadUslugaComplexField(win.EvnPSInfo.UslugaComplex_id);
									}
									// o Диагноз. Значение поля «Диагноз прием. отд-я» формы «Поступление пациента в приемное отделение».
									if (!Ext.isEmpty(v = win.EvnPSInfo.Diag_pid))
										diagStore.load(
										{
											callback: function()
											{
												diagStore.each(function(record)
												{
													if (record.get('Diag_id') == v)
														this._cmbDiag.fireEvent('select',
																				this._cmbDiag,
																				record,
																				0);
												}.createDelegate(this));

												this._cmbDiag.fireEvent('change',
																		this._cmbDiag,
																		this._cmbDiag.getValue());
											},

											params:
											{
												where: "where DiagLevel_id = 4 and Diag_id = " + v
											},

											scope: this
										});

									if (regNick.inlist(['msk','krasnoyarsk','vologda','pskov'])) {
										// o «Дата», «Время» – дата и время исхода из приёмного отделения в КВС;
										base_form.findField('EvnVizitPL_setDate').setValue(win.EvnPSInfo.EvnPS_OutcomeDate);
										base_form.findField('EvnVizitPL_setTime').setValue(win.EvnPSInfo.EvnPS_OutcomeTime);
										// o «Профиль» – основной профиль отделения в поле «Приёмное отделение» в КВС;
										base_form.findField('LpuSectionProfile_id').getValue(win.EvnPSInfo.LpuSectionProfile_id);
										// o «Вид оплаты» – вид оплаты в поле «Вид оплаты» в КВС;
										base_form.findField('PayType_id').setValue(win.EvnPSInfo.PayType_id);
										// o «Подозрение на ЗНО» – состояние флага «Подозрение на ЗНО» в КВС;
										this.findById('EVPLEF_EvnVizitPL_IsZNOCheckbox').setValue(win.EvnPSInfo.EvnPS_IsZNO == '2');
										// o «Подозрение на диагноз» – диагноз в поле «Подозрение на диагноз» в КВС;
										if(!Ext.isEmpty(win.EvnPSInfo.Diag_spid)) {
											base_form.findField('Diag_spid').getStore().load({
												callback:function () {
													base_form.findField('Diag_spid').getStore().each(function (rec) {
														if (rec.get('Diag_id') == win.EvnPSInfo.Diag_spid) {
															base_form.findField('Diag_spid').fireEvent('select', base_form.findField('Diag_spid'), rec, 0);
															win.setDiagSpidComboDisabled();
														}
													});
												},
												params:{where:"where DiagLevel_id = 4 and Diag_id = " + win.EvnPSInfo.Diag_spid}
											});
										}
										// o «Характер» – значение в поле «Характер» в КВС;
										base_form.findField('DeseaseType_id').setValue(win.EvnPSInfo.DeseaseType_id);

									}else{
										// o Вид оплаты. Значение по умолчанию: «ОМС».
										base_form.findField('PayType_id').setFieldValue('PayType_SysNick', 'oms');
										// o Характер. Значение по умолчанию: «Острое».
										base_form.findField('DeseaseType_id').setFieldValue('DeseaseType_Code', 1);
									}

								}
							}

							if (isUfa || isEkb) {
								base_form.findField('PayType_id').fireEvent('change', base_form.findField('PayType_id'), base_form.findField('PayType_id').getValue());
							}

							this.refreshFieldsVisibility();

							loadMask.hide();

							//base_form.clearInvalid();

							base_form.findField('EvnVizitPL_setDate').focus(true, 250);
						}

						var index;
						var lpu_section_id = base_form.findField('LpuSection_id').getValue();
						var lpu_section_pid;
						var med_personal_id = base_form.findField('MedPersonal_id').getValue();
						var medstafffact_id = base_form.findField('MedStaffFact_id').getValue();
						var med_personal_sid = base_form.findField('MedPersonal_sid').getValue();
						var medstafffact_sid = base_form.findField('MedStaffFact_sid').getValue();
						var record;

						index = base_form.findField('LpuSection_id').getStore().findBy(function(rec, id) {
							return (rec.get('LpuSection_id') == lpu_section_id);
						}.createDelegate(this));
						record = base_form.findField('LpuSection_id').getStore().getAt(index);

						if ( record ) {
							lpu_section_pid = record.get('LpuSection_pid');
							base_form.findField('UslugaComplex_uid').setLpuSectionProfile_id(record.get('LpuSectionProfile_id'));
						}

						// врач
						index = base_form.findField('MedStaffFact_id').getStore().findBy(function(rec, id) {
							if (medstafffact_id) {
								return (rec.get('MedStaffFact_id') == medstafffact_id);
							} else {
								return (rec.get('LpuSection_id').inlist([ lpu_section_id, lpu_section_pid ]) && rec.get('MedPersonal_id') == med_personal_id);
							}
						});
						record = base_form.findField('MedStaffFact_id').getStore().getAt(index);

						if ( record ) {
							base_form.findField('MedStaffFact_id').setValue(record.get('MedStaffFact_id'));
							base_form.findField('MedStaffFact_id').fireEvent('change', base_form.findField('MedStaffFact_id'), base_form.findField('MedStaffFact_id').getValue());
						}

						// средний мед. персонал
						index = base_form.findField('MedStaffFact_sid').getStore().findBy(function(rec, id) {
							if (medstafffact_sid) {
								return (rec.get('MedStaffFact_id') == medstafffact_sid);
							} else {
								return (rec.get('MedPersonal_id') == med_personal_sid);
							}
						}.createDelegate(this));
						record = base_form.findField('MedStaffFact_sid').getStore().getAt(index);

						if ( record ) {
							base_form.findField('MedStaffFact_sid').setValue(record.get('MedStaffFact_id'));
						}
						
						var diag_id = this.formParams.Diag_id;
						if (diag_id != null && diag_id.toString().length > 0)
							diagStore.load(
							{
								callback: function()
								{
									diagStore.each(function(record)
									{
										if (record.get('Diag_id') == diag_id)
											this._cmbDiag.fireEvent('select', this._cmbDiag, record, 0);
									}.createDelegate(this));

									this._cmbDiag.fireEvent('change', this._cmbDiag, this._cmbDiag.getValue());
								},

								params:
								{
									where: "where DiagLevel_id = 4 and Diag_id = " + diag_id
								},

								scope: this
							});

						if(win.streamInput && (isUfa || isBuryatiya)){
							if (win.formParams && win.formParams.UslugaComplex_uid) {
								win.reloadUslugaComplexField(null, win.formParams.UslugaComplex_uid);
							}
						}
					}.createDelegate(this),
					dateField: base_form.findField('EvnVizitPL_setDate'),
					loadMask: false,
					setDate: set_date_flag,
					setDateMaxValue: true,
					setDateMinValue: false,
					setTime: set_date_flag,
					timeField: base_form.findField('EvnVizitPL_setTime'),
					windowId: this.id
				});
                if (isBuryatiya || isKareliya || isAstra) {
                    if ( this.allowMorbusVizitOnly == true )
                    {
                        base_form.findField('VizitType_id').setFieldValue('VizitType_SysNick', 'desease');
                        base_form.findField('VizitType_id').disable();
                    }
                    else if (isAstra && this.allowConsulDiagnVizitOnly == true)
                    {
                        base_form.findField('VizitType_id').setFieldValue('VizitType_SysNick', 'ConsulDiagn');
                        base_form.findField('VizitType_id').disable();
                    }
                }
				if (isEkb) {
					base_form.findField('Mes_id').fireEvent('change', base_form.findField('Mes_id'), base_form.findField('Mes_id').getValue());
				}
				if (isEkb) this.checkZNO({action: this.action });

				if (regNick === 'kz') {
					base_form.findField('UslugaMedType_id').setFieldValue('UslugaMedType_Code', '1400');
					base_form.findField('PayType_id').disable();
					base_form.findField('PayType_id').setValue('');
				}
				
				this.checkAndOpenRepositoryObserv();

				this.IsLoading = false;
			break;

			case 'edit':
			case 'view':
				// this.MorbusHepatitisSpec.collapse();
				// Делаем загрузку данных с сервера
				base_form.load({
					failure: function() {
						loadMask.hide();
						sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_zagruzke_dannyih_formyi'], function() {this.hide();}.createDelegate(this) );
					}.createDelegate(this),
					params: {
						EvnVizitPL_id: base_form.findField('EvnVizitPL_id').getValue(),
						FormType: this.FormType,
						archiveRecord: win.archiveRecord
					},
					success: function(f, act) {
						var v;

						// В зависимости от accessType переопределяем this.action
						if ( base_form.findField('accessType').getValue() == 'view' ) {
							this.action = 'view';
						}

						if (!Ext.isEmpty(base_form.findField('AlertReg_Msg').getValue())) {
							sw.swMsg.alert('Внимание', base_form.findField('AlertReg_Msg').getValue());
						}

						win.blockUslugaComplexReload = true;

						if ( this.action == 'view' ) {
							this.setTitle(WND_POL_EVPLVIEW);
							this.enableEdit(false);
						}
						else {
							this.setTitle(WND_POL_EVPLEDIT);
							this.enableEdit(true);

							if (regNick.inlist(['ufa', 'kareliya', 'ekb'])) {
								base_form.findField('MedicalCareKind_id').disable();
							}

							if ( this.FormType == 'EvnVizitPLWow' && base_form.findField('PayType_id').getValue() > 0 ) {
								base_form.findField('PayType_id').setDisabled(true);
							}

							if (isUfa || isEkb) {
								base_form.findField('PayType_id').fireEvent('change', base_form.findField('PayType_id'), base_form.findField('PayType_id').getValue());
							}
						}

						var
							diag_agid = act.result.data.Diag_agid,
							diag_id = act.result.data.Diag_id,
							diag_spid = act.result.data.Diag_spid,
							index,
							lpu_section_id = act.result.data.LpuSection_id,
							lpu_section_pid,
							med_personal_id = act.result.data.MedPersonal_id,
							medstafffact_id = act.result.data.MedStaffFact_id,
							med_personal_sid = act.result.data.MedPersonal_sid,
							record,
							service_type_id = act.result.data.ServiceType_id,
							DispClass_id = act.result.data.DispClass_id,
							PayType_id = act.result.data.PayType_id,
							PersonDisp_id = act.result.data.PersonDisp_id,
							UslugaMedType_id = act.result.data.UslugaMedType_id
						;

						base_form.findField('EvnVizitPL_setDate').fireEvent('change', base_form.findField('EvnVizitPL_setDate'), base_form.findField('EvnVizitPL_setDate').getValue());
						
						var vizitDate = base_form.findField('EvnVizitPL_setDate').getValue();
						base_form.findField('DeseaseType_id').getStore().clearFilter();
						base_form.findField('DeseaseType_id').getStore().filterBy(function(rec) {
							return (
								(!rec.get('DeseaseType_begDT') || rec.get('DeseaseType_begDT') <= vizitDate)
								&& (!rec.get('DeseaseType_endDT') || rec.get('DeseaseType_endDT') >= vizitDate)
							)
						});
						base_form.findField('DeseaseType_id').lastQuery = '';

						if (isPskov && parseInt(base_form.findField('EvnVizitPL_Index').getValue()) > 0) {
							base_form.findField('UslugaComplex_uid').disable();
						}

						var curDate = (typeof getValidDT(getGlobalOptions().date, '') == 'object' ? getValidDT(getGlobalOptions().date, '') : new Date());

						if ( typeof curDate == 'object' ) {
							base_form.findField('EvnVizitPL_setDate').setMaxValue(curDate.format('d.m.Y'));
						}

						if (!isPskov) {
							this.findById('EVPLEF_EvnUslugaPanel').fireEvent('expand', this.findById('EVPLEF_EvnUslugaPanel'));
						}

						if (regNick.inlist(['buryatiya', 'ekb', 'pskov', 'ufa'])) {
							base_form.findField('UslugaComplex_uid').setPersonId(base_form.findField('Person_id').getValue());
						}

						base_form.findField('PersonDisp_id').getStore().baseParams.Person_id = base_form.findField('Person_id').getValue();

						// Остальные гриды - только если развернуты панельки
						if ( !this.findById('EVPLEF_EvnDiagPLPanel').collapsed ) {
							this.findById('EVPLEF_EvnDiagPLPanel').fireEvent('expand', this.findById('EVPLEF_EvnDiagPLPanel'));
						}
						if ( !this.findById('EVPLEF_SpecificPanel').collapsed ) {
							this.findById('EVPLEF_SpecificPanel').fireEvent('expand', this.findById('EVPLEF_SpecificPanel'));
						}
						if ( !this.findById('EVPLEF_EvnReceptPanel').collapsed ) {
							this.findById('EVPLEF_EvnReceptPanel').fireEvent('expand', this.findById('EVPLEF_EvnReceptPanel'));
						}
						if ( !this.findById('EVPLEF_EvnDirectionPanel').collapsed ) {
							this.findById('EVPLEF_EvnDirectionPanel').fireEvent('expand', this.findById('EVPLEF_EvnDirectionPanel'));
						}

						win.LpuSection_id = lpu_section_id;

						if ( !Ext.isEmpty(DispClass_id) ) {
							base_form.findField('DispClass_id').fireEvent('change', base_form.findField('DispClass_id'), DispClass_id);
						}

						if (!Ext.isEmpty(UslugaMedType_id)) {
							base_form.findField('UslugaMedType_id').fireEvent('change', base_form.findField('UslugaMedType_id'), UslugaMedType_id);
						}

						if ( sw.Promed.EvnVizitPL.isSupportVizitCode() ) {
							var usluga_complex_id = act.result.data.UslugaComplex_uid;
						}

						base_form.findField('ServiceType_id').clearValue();
						base_form.findField('ServiceType_id').getStore().clearFilter();
						base_form.findField('ServiceType_id').lastQuery = '';
						
						// Фильтр на поле ServiceType_id
						// https://redmine.swan.perm.ru/issues/17571
						if ( !Ext.isEmpty(base_form.findField('EvnVizitPL_setDate').getValue()) ) {
							base_form.findField('ServiceType_id').getStore().filterBy(function(rec) {	
								return (
									(Ext.isEmpty(rec.get('ServiceType_begDate')) || rec.get('ServiceType_begDate') <= base_form.findField('EvnVizitPL_setDate').getValue())
									&& (Ext.isEmpty(rec.get('ServiceType_endDate')) || rec.get('ServiceType_endDate') >= base_form.findField('EvnVizitPL_setDate').getValue())
								);
							});
						}

						index = base_form.findField('ServiceType_id').getStore().findBy(function(rec, id) {
							return (rec.get('ServiceType_id') == service_type_id);
						}.createDelegate(this));

						if ( index >= 0 ) {
							base_form.findField('ServiceType_id').setValue(service_type_id);
						}

						if ( !Ext.isEmpty(this.userMedStaffFact.LpuSection_id) && !Ext.isEmpty(this.userMedStaffFact.MedStaffFact_id) ) {
							if ( this.action == 'edit' ) {
								base_form.findField('LpuSection_id').disable();
								if (this.userMedStaffFact.MedStaffFact_id == base_form.findField('MedStaffFact_id').getValue()) {
									base_form.findField('MedStaffFact_id').disable();
								}
							}
						}

						index = base_form.findField('LpuSection_id').getStore().findBy(function(rec, id) {
							if ( rec.get('LpuSection_id') == lpu_section_id ) {
								return true;
							}
							else {
								return false;
							}
						}.createDelegate(this));
						record = base_form.findField('LpuSection_id').getStore().getAt(index);

						if ( record ) {
							lpu_section_pid = record.get('LpuSection_pid');
						}

						if ( this.action == 'edit' ) {
							if (!Ext.isEmpty(base_form.findField('EvnVizitPL_setDate').getValue())) {
								this.filterVizitTypeCombo();
							}

							index = base_form.findField('MedStaffFact_id').getStore().findBy(function(rec, id) {
								if (medstafffact_id) {
									return (rec.get('MedStaffFact_id') == medstafffact_id);
								} else {
									return (rec.get('LpuSection_id').inlist([ lpu_section_id, lpu_section_pid ]) && rec.get('MedPersonal_id') == med_personal_id);
								}
							});
							record = base_form.findField('MedStaffFact_id').getStore().getAt(index);

							if ( record ) {
								base_form.findField('MedStaffFact_id').setValue(record.get('MedStaffFact_id'));
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
										loadMask.hide();

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
											base_form.findField('MedStaffFact_id').validate();
										}
									}.createDelegate(this),
									url: C_MEDPERSONAL_LIST
								});
							}

							index = base_form.findField('MedStaffFact_sid').getStore().findBy(function(rec, id) {
								return (rec.get('MedPersonal_id') == med_personal_sid);
							});
							record = base_form.findField('MedStaffFact_sid').getStore().getAt(index);

							if ( record ) {
								base_form.findField('MedStaffFact_sid').setValue(record.get('MedStaffFact_id'));
							}
						}
						else {
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
										//base_form.findField('LpuSection_id').fireEvent('change', base_form.findField('LpuSection_id'), base_form.findField('LpuSection_id').getStore().getAt(index).get('LpuSection_id'));
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
										base_form.findField('MedStaffFact_id').validate();
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
											if ( rec.get('MedPersonal_id') == med_personal_sid && rec.get('LpuSection_id') == lpu_section_id ) {
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
										LpuSection_id: lpu_section_id,
										MedPersonal_id: med_personal_sid
									}
								});
							}
						}

						win.blockUslugaComplexReload = false;
						if ( sw.Promed.EvnVizitPL.isSupportVizitCode() ) {
							if (isUfa || isBuryatiya) {
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
								win.reloadUslugaComplexField(usluga_complex_id);
							}
							else if (isPskov && parseInt(base_form.findField('EvnVizitPL_Index').getValue()) > 0) {
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

						if (diag_agid != null && diag_agid.toString().length > 0)
							complDiagStore.load(
							{
								callback: function()
								{
									complDiagStore.each(function(record)
									{
										if (record.get('Diag_id') == diag_agid)
										{
											this._cmbComplDiag.fireEvent('select', this._cmbComplDiag, record, 0);
										}
									}.createDelegate(this));

									this._cmbComplDiag.setFilterByDate(base_form.findField('EvnVizitPL_setDate').getValue());

									// #170429
									this._refreshHsnDetails(this._cmbComplDiag, false);
								},

								params:
								{
									where: "where DiagLevel_id = 4 and Diag_id = " + diag_agid
								},

								scope: this
							});

						this.checkAbort();

						if (!Ext.isEmpty(diag_id))
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
											this._cmbDiag.setFilterByDate(base_form.findField('EvnVizitPL_setDate').getValue());

											base_form.findField('TreatmentClass_id').onLoadStore();
											this.checkAbort();
											this.refreshFieldsVisibility();
										}
									}.createDelegate(this));
								},

								params:
								{
									where: "where DiagLevel_id = 4 and Diag_id = " + diag_id
								},

								scope: this
							});

						win.findById('EVPLEF_EvnVizitPL_IsZNOCheckbox').setValue(base_form.findField('EvnVizitPL_IsZNO').getValue() == 2);
						base_form.findField('Diag_spid').setContainerVisible(base_form.findField('EvnVizitPL_IsZNO').getValue() == 2);
						//base_form.findField('Diag_spid').setAllowBlank(getRegionNick() != 'perm' || base_form.findField('EvnVizitPL_IsZNO').getValue() != 2);

						if (diag_spid) {
							base_form.findField('Diag_spid').getStore().load({
								callback:function () {
									base_form.findField('Diag_spid').getStore().each(function (rec) {
										if (rec.get('Diag_id') == diag_spid) {
											base_form.findField('Diag_spid').fireEvent('select', base_form.findField('Diag_spid'), rec, 0);
											win.setDiagSpidComboDisabled();
										}
									});
								},
								params:{where:"where DiagLevel_id = 4 and Diag_id = " + diag_spid}
							});
						}

						if ( this.FormType == 'EvnVizitPLWow' ) {
							this.setFilter(base_form.findField('DispWowSpec_id').getValue());
						}

						if (!Ext.isEmpty(act.result.data.DrugTherapyScheme_ids)) {
							this.DrugTherapySchemePanel.show();
							this.DrugTherapySchemePanel.setIds(act.result.data.DrugTherapyScheme_ids);
						}

						loadMask.hide();
						this.evnDirectionAllInfoPanel.onLoadForm(this);

						//base_form.clearInvalid();

                        this.EvnXmlPanel.setBaseParams({
                            userMedStaffFact: this.userMedStaffFact,
                            Server_id: base_form.findField('Server_id').getValue(),
                            Evn_id: base_form.findField('EvnVizitPL_id').getValue()
                        });
                        this.EvnXmlPanel.doLoadData();
                        // this.EvnXmlPanel.expand();
   						if ( this.action == 'edit' ) {
							base_form.findField('EvnVizitPL_setDate').focus(true, 250);
						}
						else {
							this.buttons[this.buttons.length - 1].focus();
						}
						if (isEkb) {
							if(base_form.findField('Mes_id').getValue()>0)
							base_form.findField('Mes_id').fireEvent('change', base_form.findField('Mes_id'), base_form.findField('Mes_id').getValue());
							if(base_form.findField('UslugaComplex_uid').getValue()>0)
							base_form.findField('UslugaComplex_uid').fireEvent('change', base_form.findField('UslugaComplex_uid'), base_form.findField('UslugaComplex_uid').getValue());
							
							if (base_form.findField('EvnVizitPL_Index').getValue() > 0) {
								base_form.findField('Mes_id').disable();
							}
						}
						base_form.findField('PayType_id').setValue(PayType_id);
						if (isEkb && win.findById('EVPLEF_PersonInformationFrame').getFieldValue('Person_IsAnonym') == 2) {
							base_form.findField('PayType_id').setFieldValue('PayType_SysNick','bud');
							base_form.findField('PayType_id').disable();
						}
						base_form.findField('PayType_id').fireEvent('change',base_form.findField('PayType_id'),base_form.findField('PayType_id').getValue());
						base_form.findField('Mes_id').fireEvent('change', base_form.findField('Mes_id'), base_form.findField('Mes_id').getValue());
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
						if (isEkb) {
							this.checkZNO({action: this.action });
							this.checkBiopsyDate();
						}
						this.findById('EVPLEF_EvnDiagPLGrid').getStore().load({
							params: {
								EvnVizitPL_id: this.findById('EvnVizitPLEditForm').getForm().findField('EvnVizitPL_id').getValue()
							},
							callback: function() {
								this.loadSpecificsTree();
							}.createDelegate(this)
						});

						if (regNick == 'kz') {
							base_form.findField('PayType_id').disable();
						}

						this.IsLoading = false;

						if (regNick != 'kz') {
							this.EvnPLDispScreenOnkoGrid.loadData({globalFilters: {EvnSection_id: base_form.findField('EvnVizitPL_id').getValue()}});
						}
					}.createDelegate(this),
					url: '/?c=EvnVizit&m=loadEvnVizitPLEditForm'
				});
			break;

			default:
				loadMask.hide();
				this.hide();
			break;
		}
		if (isEkb) {
			Ext.QuickTips.register({
				target: base_form.findField('EvnVizitPL_BiopsyDate').getEl(),
				text: 'Дата взятия биопсии, по результатам которой снимается подозрение на ЗНО',
				enabled: true,
				showDelay: 5,
				trackMouse: true,
				autoShow: true
			});
		}
	},
	setRiskLevelComboState: function() {
		// @task https://redmine.swan.perm.ru/issues/113607
		// Регион: Астрахань
		// Для посещений взрослых с даты >= 21.07.2017 и детей с даты >=24.07.2017 поле не отображается.
		var
			base_form = this.findById('EvnVizitPLEditForm').getForm(),
			dateXAdult = new Date(2017, 6, 21),
			dateXChild = new Date(2017, 6, 24),
			EvnVizitPL_setDate = base_form.findField('EvnVizitPL_setDate').getValue(),
			Person_Age = swGetPersonAge(this.findById('EVPLEF_PersonInformationFrame').getFieldValue('Person_Birthday'), EvnVizitPL_setDate),
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
			base_form = this.findById('EvnVizitPLEditForm').getForm(),
			dateXChild = new Date(2017, 6, 24),
			EvnVizitPL_setDate = base_form.findField('EvnVizitPL_setDate').getValue(),
			index,
			Person_Age = swGetPersonAge(this.findById('EVPLEF_PersonInformationFrame').getFieldValue('Person_Birthday'), EvnVizitPL_setDate),
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
		var params = new Object();

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

				var grid_fields = new Array();

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
		}
		params.formMode = 'local';
		params.formParams = new Object();

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
	uetValuesRecount: function() {
		var base_form = this.findById('EvnVizitPLEditForm').getForm();
		var grid = this.findById('EVPLEF_EvnUslugaGrid');

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
	checkMesOldUslugaComplexFields: function () {
		var win = this;
		var base_form = this.findById('EvnVizitPLEditForm').getForm();

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
	errorControlCodaVisits: function(){
		var flagProfile = false;
		if ( getRegionNick() == 'vologda' && this.OtherVizitList && Ext.isArray(this.OtherVizitList) && this.OtherVizitList.length>0 && this.OtherVizitList[0].EvnVizitPL_id) {
			var base_form = this.findById('EvnVizitPLEditForm').getForm();
			var controlDate = new Date(2019, 7, 1);
			var evnVizitPL_setDate = base_form.findField('EvnVizitPL_setDate').getValue();
			if(evnVizitPL_setDate >= controlDate && this.OtherVizitList.length>0){
				var comboLpuSectionProfile = base_form.findField('LpuSectionProfile_id');
				var flagProfile = false;
				var arrNotControlProfileCode = [];
				var arrControlProfileCode = [];
				var arrVizitsProfileCode = [];
				for(var i=0; i<this.OtherVizitList.length; i++) {
					var vizit = this.OtherVizitList[i];
					if(arrVizitsProfileCode.indexOf(vizit.LpuSectionProfile_Code)<0) arrVizitsProfileCode.push(vizit.LpuSectionProfile_Code);
					if(!vizit.LpuSectionProfile_Code.inlist(getGlobalOptions().exceptionprofiles)) {
						flagProfile = true;
						if(arrNotControlProfileCode.indexOf(vizit.LpuSectionProfile_Code)<0) arrNotControlProfileCode.push(vizit.LpuSectionProfile_Code);
					}else{
						arrControlProfileCode.push(vizit.LpuSectionProfile_Code);
					}
				}
				
				if(arrVizitsProfileCode.indexOf(comboLpuSectionProfile.getFieldValue('LpuSectionProfile_Code'))<0) arrVizitsProfileCode.push(comboLpuSectionProfile.getFieldValue('LpuSectionProfile_Code'));
				if(!comboLpuSectionProfile.getFieldValue('LpuSectionProfile_Code').inlist(getGlobalOptions().exceptionprofiles) ){
					flagProfile = true;
					if(arrNotControlProfileCode.indexOf(comboLpuSectionProfile.getFieldValue('LpuSectionProfile_Code'))<0) arrNotControlProfileCode.push(comboLpuSectionProfile.getFieldValue('LpuSectionProfile_Code'));
				}else{
					arrControlProfileCode.push(comboLpuSectionProfile.getFieldValue('LpuSectionProfile_Code'));
				}
				
				if(arrVizitsProfileCode.length == 1) flagProfile = false;
				if(flagProfile && arrControlProfileCode.length > 0 && arrNotControlProfileCode.length == 1){
					// есть одно или более посещений, в которых указаны профили «97», «57», «58», «42», «68», «3», «136»
					// И в остальных посещениях указан одинаковый профиль отделения, отличный от профилей «97», «57», «58», «42», «68», «3», «136»
					flagProfile = false;
				}
			}
		}
		return flagProfile;
	},
	width: 700
});
